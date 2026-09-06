<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Mode;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\UserAddress;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderCheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Mode $shopyMode;
    protected Category $category;
    protected Product $product;
    protected UserAddress $address;

    protected function setUp(): void
    {
        parent::setUp();

        $this->shopyMode = Mode::firstOrCreate(
            ['slug' => 'shopy'],
            ['name' => 'Shopy', 'status' => true, 'icon' => 'fa-solid fa-bag-shopping']
        );

        $this->category = Category::create([
            'mode_id'    => $this->shopyMode->id,
            'name'       => 'Electronics',
            'slug'       => 'electronics',
            'status'     => true,
            'sort_order' => 1,
        ]);

        $this->product = Product::create([
            'mode_id'     => $this->shopyMode->id,
            'category_id' => $this->category->id,
            'name'        => 'Gaming Laptop Pro',
            'slug'        => 'gaming-laptop-pro',
            'sku'         => 'GL-2026',
            'price'       => 50000.00,
            'sale_price'  => 45000.00,
            'stock'       => 5,
            'status'      => true,
        ]);

        $this->user = User::create([
            'name'              => 'Tony Stark',
            'email'             => 'tony@stark.com',
            'password'          => bcrypt('password'),
            'status'            => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);

        $this->address = UserAddress::create([
            'user_id'       => $this->user->id,
            'full_name'     => 'Tony Stark',
            'phone'         => '9876543210',
            'address_line1' => '10880 Malibu Point',
            'city'          => 'Malibu',
            'state'         => 'California',
            'postal_code'   => '90265',
            'address_type'  => 'home',
            'is_default'    => true,
        ]);
    }

    public function test_guest_cannot_access_checkout_and_is_redirected_to_login(): void
    {
        $response = $this->get(route('checkout'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_with_empty_cart_is_redirected_to_cart_page(): void
    {
        $response = $this->actingAs($this->user)->get(route('checkout'));
        $response->assertRedirect(route('cart.index'));
        $response->assertSessionHas('warning');
    }

    public function test_authenticated_user_with_items_can_view_checkout_page(): void
    {
        $cart = Cart::getOrCreate($this->user, 'session-123', 'shopy');
        $cart->addItem($this->product, 1, 'Black', '16GB');

        $response = $this->actingAs($this->user)->get(route('checkout'));
        $response->assertOk();
        $response->assertSee('Secure Checkout');
        $response->assertSee('Gaming Laptop Pro');
        $response->assertSee('Pay on Delivery');
    }

    public function test_user_can_place_order_with_existing_address_and_cash_on_delivery(): void
    {
        $cart = Cart::getOrCreate($this->user, 'session-123', 'shopy');
        $cart->addItem($this->product, 2, 'Black', '16GB');

        $initialStock = $this->product->fresh()->stock;

        $response = $this->actingAs($this->user)->post(route('checkout.place_order'), [
            'address_source' => 'existing',
            'address_id'     => $this->address->id,
            'payment_method' => 'pay_on_delivery',
            'notes'          => 'Leave at the front gate',
        ]);

        $order = Order::where('user_id', $this->user->id)->latest()->first();
        $this->assertNotNull($order);
        $this->assertEquals(Order::STATUS_CONFIRMED, $order->status);
        $this->assertEquals('pay_on_delivery', $order->payment_method);
        $this->assertEquals(Order::PAYMENT_STATUS_PENDING, $order->payment_status);
        $this->assertEquals($this->address->id, $order->address_id);
        $this->assertEquals('10880 Malibu Point', $order->userAddress->address_line1);
        $this->assertEquals('Leave at the front gate', $order->notes);

        // Check stock decrement
        $this->assertEquals($initialStock - 2, $this->product->fresh()->stock);

        // Check order item created with variants
        $this->assertCount(1, $order->items);
        $item = $order->items->first();
        $this->assertEquals('Gaming Laptop Pro', $item->product_name);
        $this->assertEquals('Black', $item->color);
        $this->assertEquals('16GB', $item->size);
        $this->assertEquals(2, $item->quantity);

        // Check cart emptied
        $this->assertCount(0, $cart->fresh()->items);

        // Check redirect to order success page
        $response->assertRedirect(route('orders.success', $order->order_number));
    }

    public function test_user_can_place_order_with_new_address_and_mock_upi_payment(): void
    {
        $cart = Cart::getOrCreate($this->user, 'session-123', 'shopy');
        $cart->addItem($this->product, 1);

        $response = $this->actingAs($this->user)->post(route('checkout.place_order'), [
            'address_source' => 'new',
            'full_name'      => 'Pepper Potts',
            'phone'          => '9123456780',
            'address_line1'  => 'Stark Tower, 5th Ave',
            'city'           => 'New York',
            'state'          => 'NY',
            'postal_code'    => '10001',
            'address_type'   => 'work',
            'save_address'   => '1',
            'payment_method' => 'mock_upi',
        ]);

        $order = Order::where('user_id', $this->user->id)->latest()->first();
        $this->assertNotNull($order);
        $this->assertEquals('Pepper Potts', $order->userAddress->full_name);
        $this->assertEquals('Stark Tower, 5th Ave', $order->userAddress->address_line1);
        $this->assertEquals('mock_upi', $order->payment_method);
        $this->assertEquals(Order::PAYMENT_STATUS_PAID, $order->payment_status);

        // Check new address saved to user addresses
        $this->assertTrue(UserAddress::where('user_id', $this->user->id)->where('address_line1', 'Stark Tower, 5th Ave')->exists());

        $response->assertRedirect(route('orders.success', $order->order_number));
    }

    public function test_user_can_view_order_history_and_order_tracking_details(): void
    {
        $order = Order::create([
            'order_number'          => Order::generateOrderNumber(),
            'user_id'               => $this->user->id,
            'mode_id'               => $this->shopyMode->id,
            'address_id'            => $this->address->id,
            'status'                => Order::STATUS_CONFIRMED,
            'payment_method'        => 'pay_on_delivery',
            'payment_status'        => 'pending',
            'subtotal'              => 45000.00,
            'delivery_fee'          => 0.00,
            'tax_amount'            => 2250.00,
            'discount_amount'       => 0.00,
            'grand_total'           => 47250.00,
        ]);

        $order->items()->create([
            'product_id'   => $this->product->id,
            'product_name' => $this->product->name,
            'product_slug' => $this->product->slug,
            'quantity'     => 1,
            'unit_price'   => 45000.00,
            'subtotal'     => 45000.00,
        ]);

        // View Order History
        $historyRes = $this->actingAs($this->user)->get(route('orders.index'));
        $historyRes->assertOk();
        $historyRes->assertSee($order->order_number);

        // View Order Details
        $showRes = $this->actingAs($this->user)->get(route('orders.show', $order->order_number));
        $showRes->assertOk();
        $showRes->assertSee($order->order_number);
        $showRes->assertSee('Rate &amp; Review Product', false);
    }

    public function test_user_can_cancel_eligible_order_restoring_stock(): void
    {
        $cart = Cart::getOrCreate($this->user, 'session-123', 'shopy');
        $cart->addItem($this->product, 2);

        $initialStock = $this->product->fresh()->stock;

        $this->actingAs($this->user)->post(route('checkout.place_order'), [
            'address_source' => 'existing',
            'address_id'     => $this->address->id,
            'payment_method' => 'pay_on_delivery',
        ]);

        $order = Order::where('user_id', $this->user->id)->latest()->first();
        $this->assertEquals($initialStock - 2, $this->product->fresh()->stock);

        // Cancel the order
        $cancelRes = $this->actingAs($this->user)->post(route('orders.cancel', $order->order_number), [
            'cancellation_reason' => 'Changed my mind',
        ]);

        $cancelRes->assertSessionHas('success');
        $this->assertEquals(Order::STATUS_CANCELLED, $order->fresh()->status);
        $this->assertEquals('Changed my mind', $order->fresh()->cancellation_reason);

        // Stock must be restored
        $this->assertEquals($initialStock, $this->product->fresh()->stock);
    }
}
