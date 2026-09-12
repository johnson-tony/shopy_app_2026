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
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OrderReturnTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;
    protected User $customer;
    protected Mode $shopyMode;
    protected Mode $groceryMode;
    protected Product $product;
    protected UserAddress $address;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();

        $this->admin = Admin::where('email', 'admin@shopy.test')->first();
        $this->customer = User::where('email', 'customer@shopy.test')->first();
        $this->shopyMode = Mode::where('slug', 'shopy')->first();
        $this->groceryMode = Mode::where('slug', 'minutes')->first();

        $this->product = Product::create([
            'mode_id'     => $this->shopyMode->id,
            'category_id' => $this->shopyMode->categories()->first()?->id,
            'name'        => 'Classic Denim Jacket',
            'slug'        => 'classic-denim-jacket-' . uniqid(),
            'sku'         => 'CDJ-' . uniqid(),
            'price'       => 1499.00,
            'stock'       => 15,
            'status'      => true,
        ]);

        $this->address = UserAddress::create([
            'user_id'       => $this->customer->id,
            'full_name'     => $this->customer->name,
            'phone'         => '9876543210',
            'address_line1' => '221B Baker Street',
            'city'          => 'Bengaluru',
            'state'         => 'Karnataka',
            'postal_code'   => '560001',
            'address_type'  => 'home',
            'is_default'    => true,
        ]);
    }

    protected function createDeliveredOrder(?Mode $mode = null, ?\DateTimeInterface $deliveredAt = null): Order
    {
        $mode = $mode ?? $this->shopyMode;

        $order = Order::create([
            'order_number'          => Order::generateOrderNumber(),
            'user_id'               => $this->customer->id,
            'mode_id'               => $mode->id,
            'address_id'            => $this->address->id,
            'shipping_name'         => $this->address->full_name,
            'shipping_phone'        => $this->address->phone,
            'shipping_address_line1'=> $this->address->address_line1,
            'shipping_city'         => $this->address->city,
            'shipping_state'        => $this->address->state,
            'shipping_postal_code'  => $this->address->postal_code,
            'status'                => Order::STATUS_DELIVERED,
            'payment_method'        => 'pay_on_delivery',
            'payment_status'        => 'paid',
            'subtotal'              => 1499.00,
            'delivery_fee'          => 0.00,
            'tax_amount'            => 74.95,
            'discount_amount'       => 0.00,
            'grand_total'           => 1573.95,
            'delivered_at'          => $deliveredAt ?? now()->subHours(12),
        ]);

        OrderItem::create([
            'order_id'     => $order->id,
            'product_id'   => $this->product->id,
            'product_name' => $this->product->name,
            'product_slug' => $this->product->slug,
            'quantity'     => 2,
            'unit_price'   => 1499.00,
            'subtotal'     => 2998.00,
        ]);

        return $order;
    }

    public function test_customer_cannot_request_return_on_non_delivered_order(): void
    {
        $order = $this->createDeliveredOrder();
        $order->update(['status' => Order::STATUS_SHIPPED, 'delivered_at' => null]);

        $response = $this->actingAs($this->customer)->post(route('orders.return', $order->order_number), [
            'return_reason' => 'Size or fit issue (Need exchange/return)',
            'return_note'   => 'Sleeve is too long',
        ]);

        $response->assertSessionHas('error');
        $this->assertEquals(Order::STATUS_SHIPPED, $order->fresh()->status);
    }

    public function test_customer_cannot_request_return_after_policy_window_expires(): void
    {
        // 8 days ago (Shopy window is 7 days)
        $order = $this->createDeliveredOrder($this->shopyMode, now()->subDays(8));

        $this->assertFalse($order->isReturnEligible());

        $response = $this->actingAs($this->customer)->post(route('orders.return', $order->order_number), [
            'return_reason' => 'Changed mind / No longer needed',
        ]);

        $response->assertSessionHas('error');
        $this->assertEquals(Order::STATUS_DELIVERED, $order->fresh()->status);
    }

    public function test_grocery_order_has_48_hour_freshness_window(): void
    {
        // Grocery 20 hours ago is eligible
        $orderWithin = $this->createDeliveredOrder($this->groceryMode, now()->subHours(20));
        $this->assertTrue($orderWithin->isReturnEligible());
        $this->assertStringContainsString('48-Hour Grocery Quality', $orderWithin->return_window_text);

        // Grocery 50 hours ago is expired
        $orderExpired = $this->createDeliveredOrder($this->groceryMode, now()->subHours(50));
        $this->assertFalse($orderExpired->isReturnEligible());
    }

    public function test_customer_can_request_return_with_reason_note_and_image(): void
    {
        Storage::fake('public');
        $order = $this->createDeliveredOrder($this->shopyMode, now()->subDay());

        $fakeImage = UploadedFile::fake()->image('defective_item.jpg');

        $response = $this->actingAs($this->customer)->post(route('orders.return', $order->order_number), [
            'return_reason' => 'Defective or damaged product received',
            'return_note'   => 'Zipper is jammed and torn at bottom edge',
            'return_image'  => $fakeImage,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $order->refresh();
        $this->assertEquals(Order::STATUS_RETURN_REQUESTED, $order->status);
        $this->assertEquals('requested', $order->return_status);
        $this->assertEquals('Defective or damaged product received', $order->return_reason);
        $this->assertEquals('Zipper is jammed and torn at bottom edge', $order->return_note);
        $this->assertNotNull($order->return_image);
        $this->assertNotNull($order->return_requested_at);
        Storage::disk('public')->assertExists($order->return_image);
    }

    public function test_admin_can_approve_return_request(): void
    {
        $order = $this->createDeliveredOrder();
        $order->update([
            'status'              => Order::STATUS_RETURN_REQUESTED,
            'return_status'       => 'requested',
            'return_reason'       => 'Size or fit issue (Need exchange/return)',
            'return_requested_at' => now(),
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('admin.orders.approve_return', $order));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $order->refresh();
        $this->assertEquals(Order::STATUS_RETURN_APPROVED, $order->status);
        $this->assertEquals('approved', $order->return_status);
        $this->assertNotNull($order->return_resolved_at);
    }

    public function test_admin_can_complete_return_and_inventory_is_restocked(): void
    {
        $order = $this->createDeliveredOrder();
        $order->update([
            'status'              => Order::STATUS_RETURN_APPROVED,
            'return_status'       => 'approved',
            'return_reason'       => 'Quality not as expected',
            'return_requested_at' => now(),
            'return_resolved_at'  => now(),
        ]);

        $initialStock = $this->product->fresh()->stock; // 15
        $returnedQuantity = $order->items->sum('quantity'); // 2

        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('admin.orders.complete_return', $order));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $order->refresh();
        $this->assertEquals(Order::STATUS_RETURNED, $order->status);
        $this->assertEquals('completed', $order->return_status);

        // Inventory must be restocked by +2
        $this->assertEquals($initialStock + $returnedQuantity, $this->product->fresh()->stock);
    }

    public function test_admin_can_reject_return_request_with_reason(): void
    {
        $order = $this->createDeliveredOrder();
        $order->update([
            'status'              => Order::STATUS_RETURN_REQUESTED,
            'return_status'       => 'requested',
            'return_reason'       => 'Changed mind / No longer needed',
            'return_requested_at' => now(),
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('admin.orders.reject_return', $order), [
                'rejection_reason' => 'Item tags have been removed according to inspection photos.',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $order->refresh();
        $this->assertEquals(Order::STATUS_DELIVERED, $order->status);
        $this->assertEquals('rejected', $order->return_status);
        $this->assertEquals('Item tags have been removed according to inspection photos.', $order->return_rejection_reason);
        $this->assertNotNull($order->return_resolved_at);
    }
}
