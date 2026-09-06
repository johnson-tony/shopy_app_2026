<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Mode;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Models\UserAddress;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminOrderManagementTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;
    protected Admin $superAdmin;
    protected User $customer;
    protected Mode $shopyMode;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();

        $this->admin = Admin::where('email', 'admin@shopy.test')->first();
        $this->superAdmin = Admin::where('email', 'superadmin@shopy.test')->first();
        $this->customer = User::where('email', 'customer@shopy.test')->first();
        $this->shopyMode = Mode::where('slug', 'shopy')->first();
    }

    protected function makeOrder(): Order
    {
        $product = Product::first() ?? Product::create([
            'mode_id'     => $this->shopyMode->id,
            'category_id' => $this->shopyMode->categories()->first()?->id,
            'name'        => 'Test Product',
            'slug'        => 'test-product-' . uniqid(),
            'sku'         => 'TP-' . uniqid(),
            'price'       => 100.00,
            'stock'       => 10,
            'status'      => true,
        ]);

        $address = UserAddress::create([
            'user_id'       => $this->customer->id,
            'full_name'     => $this->customer->name,
            'address_line1' => 'Test Address',
            'city'          => 'Bengaluru',
            'state'         => 'Karnataka',
            'postal_code'   => '560001',
            'address_type'  => 'home',
            'is_default'    => true,
        ]);

        $order = Order::create([
            'order_number'          => Order::generateOrderNumber(),
            'user_id'               => $this->customer->id,
            'mode_id'               => $this->shopyMode->id,
            'address_id'            => $address->id,
            'status'                => Order::STATUS_CONFIRMED,
            'payment_method'        => 'pay_on_delivery',
            'payment_status'        => 'pending',
            'subtotal'              => 100.00,
            'delivery_fee'          => 0.00,
            'tax_amount'            => 5.00,
            'discount_amount'       => 0.00,
            'grand_total'           => 105.00,
        ]);

        OrderItem::create([
            'order_id'     => $order->id,
            'product_id'   => $product->id,
            'product_name' => $product->name,
            'product_slug' => $product->slug,
            'quantity'     => 1,
            'unit_price'   => 100.00,
            'subtotal'     => 100.00,
        ]);

        return $order;
    }

    public function test_guest_cannot_access_admin_orders(): void
    {
        $response = $this->get(route('admin.orders.index'));
        $response->assertRedirect(route('admin.login'));
    }

    public function test_customer_cannot_access_admin_orders(): void
    {
        $response = $this->actingAs($this->customer, 'web')
            ->get(route('admin.orders.index'));

        $response->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_view_orders_index(): void
    {
        $order = $this->makeOrder();

        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.orders.index'));

        $response->assertStatus(200);
        $response->assertSee('Order Management');
        $response->assertSee($order->order_number);
        $response->assertSee($this->customer->name);
    }

    public function test_admin_can_filter_orders_by_mode_tab(): void
    {
        $this->makeOrder();

        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.orders.index', ['mode' => $this->shopyMode->id]));

        $response->assertStatus(200);
        $response->assertSee('Shopy');
    }

    public function test_admin_can_search_orders_by_customer(): void
    {
        $this->makeOrder();

        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.orders.index', ['search' => $this->customer->name]));

        $response->assertStatus(200);
        $response->assertSee($this->customer->name);
    }

    public function test_admin_can_view_order_detail(): void
    {
        $order = $this->makeOrder();

        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.orders.show', $order));

        $response->assertStatus(200);
        $response->assertSee($order->order_number);
        $response->assertSee($this->customer->name);
        $response->assertSee('Update Fulfillment');
    }

    public function test_admin_can_update_order_status_and_payment(): void
    {
        $order = $this->makeOrder();

        $response = $this->actingAs($this->admin, 'admin')
            ->put(route('admin.orders.update', $order), [
                'status'         => Order::STATUS_SHIPPED,
                'payment_status' => 'paid',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('orders', [
            'id'             => $order->id,
            'status'         => Order::STATUS_SHIPPED,
            'payment_status' => 'paid',
        ]);
    }

    public function test_invalid_order_status_is_rejected(): void
    {
        $order = $this->makeOrder();

        $response = $this->actingAs($this->admin, 'admin')
            ->put(route('admin.orders.update', $order), [
                'status' => 'not-a-real-status',
            ]);

        $response->assertSessionHasErrors(['status']);
    }

    public function test_admin_can_cancel_order_with_reason(): void
    {
        $order = $this->makeOrder();

        $response = $this->actingAs($this->admin, 'admin')
            ->put(route('admin.orders.update', $order), [
                'status'              => Order::STATUS_CANCELLED,
                'payment_status'      => 'refunded',
                'cancellation_reason' => 'Out of stock',
            ]);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('orders', [
            'id'                  => $order->id,
            'status'              => Order::STATUS_CANCELLED,
            'payment_status'      => 'refunded',
            'cancellation_reason' => 'Out of stock',
        ]);
    }
}
