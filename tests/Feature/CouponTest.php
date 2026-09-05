<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Mode;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CouponTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Mode $shopyMode;
    protected Mode $minutesMode;
    protected Category $category;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->shopyMode = Mode::firstOrCreate(
            ['slug' => 'shopy'],
            ['name' => 'Shopy', 'status' => true]
        );

        $this->minutesMode = Mode::firstOrCreate(
            ['slug' => 'minutes'],
            ['name' => 'Minutes', 'status' => true]
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
            'name'        => 'Wireless Headphones 2026',
            'slug'        => 'wireless-headphones-2026',
            'sku'         => 'WH-2026',
            'price'       => 1000.00,
            'stock'       => 10,
            'status'      => true,
        ]);

        $this->user = User::factory()->create([
            'status'            => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);
    }

    public function test_coupons_directory_page_renders(): void
    {
        Coupon::create([
            'code'             => 'SAVE20',
            'name'             => '20% Off Super Sale',
            'description'      => 'Save 20% on all orders',
            'type'             => Coupon::TYPE_PERCENTAGE,
            'value'            => 20.00,
            'min_order_amount' => 100.00,
            'status'           => true,
        ]);

        $response = $this->get(route('coupons.index'));

        $response->assertStatus(200);
        $response->assertSee('Coupons & Offers');
        $response->assertSee('SAVE20');
        $response->assertSee('20% Off Super Sale');
    }

    public function test_coupons_directory_can_filter_by_mode(): void
    {
        Coupon::create([
            'code'             => 'MINUTES50',
            'name'             => 'Minutes Only Deal',
            'type'             => Coupon::TYPE_FIXED,
            'value'            => 50.00,
            'mode_id'          => $this->minutesMode->id,
            'status'           => true,
        ]);

        $response = $this->get(route('coupons.index', ['mode' => 'minutes']));

        $response->assertStatus(200);
        $response->assertSee('MINUTES50');
    }

    public function test_user_can_apply_percentage_coupon_with_max_cap(): void
    {
        // 20% off with max cap of ₹150
        $coupon = Coupon::create([
            'code'                => 'MEGA20',
            'name'                => 'Mega Discount',
            'type'                => Coupon::TYPE_PERCENTAGE,
            'value'               => 20.00,
            'max_discount_amount' => 150.00,
            'min_order_amount'    => 200.00,
            'status'              => true,
        ]);

        // Cart subtotal: 1000. 20% of 1000 is 200, but capped at 150
        $cart = Cart::getOrCreate($this->user, 'sess-1', 'shopy');
        $cart->addItem($this->product, 1);

        $response = $this->actingAs($this->user)->post(route('cart.coupon.apply'), [
            'coupon_code' => 'MEGA20',
            'mode'        => 'shopy',
        ]);

        $response->assertRedirect();
        $cart->refresh();

        $this->assertEquals('MEGA20', $cart->coupon_code);
        $this->assertEquals(150.00, $cart->discount());
    }

    public function test_user_can_apply_fixed_amount_coupon(): void
    {
        Coupon::create([
            'code'             => 'FLAT75',
            'name'             => 'Flat 75 Off',
            'type'             => Coupon::TYPE_FIXED,
            'value'            => 75.00,
            'min_order_amount' => 200.00,
            'status'           => true,
        ]);

        $cart = Cart::getOrCreate($this->user, 'sess-1', 'shopy');
        $cart->addItem($this->product, 1);

        $response = $this->actingAs($this->user)->post(route('cart.coupon.apply'), [
            'coupon_code' => 'FLAT75',
            'mode'        => 'shopy',
        ]);

        $response->assertRedirect();
        $cart->refresh();

        $this->assertEquals('FLAT75', $cart->coupon_code);
        $this->assertEquals(75.00, $cart->discount());
    }

    public function test_user_can_apply_free_delivery_coupon(): void
    {
        Coupon::create([
            'code'             => 'FREESHIP',
            'name'             => 'Free Shipping',
            'type'             => Coupon::TYPE_FREE_DELIVERY,
            'value'            => 0.00,
            'min_order_amount' => 50.00,
            'status'           => true,
        ]);

        // Cheap product below free delivery threshold
        $cheapProduct = Product::create([
            'mode_id'     => $this->shopyMode->id,
            'category_id' => $this->category->id,
            'name'        => 'Notebook',
            'slug'        => 'notebook',
            'price'       => 100.00,
            'stock'       => 10,
            'status'      => true,
        ]);

        $cart = Cart::getOrCreate($this->user, 'sess-1', 'shopy');
        $cart->addItem($cheapProduct, 1);

        $this->assertEquals(50.00, $cart->deliveryFee());

        $response = $this->actingAs($this->user)->post(route('cart.coupon.apply'), [
            'coupon_code' => 'FREESHIP',
            'mode'        => 'shopy',
        ]);

        $response->assertRedirect();
        $cart->refresh();

        $this->assertEquals('FREESHIP', $cart->coupon_code);
        $this->assertEquals(50.00, $cart->discount());
    }

    public function test_coupon_cannot_be_applied_if_subtotal_below_minimum(): void
    {
        Coupon::create([
            'code'             => 'MIN2000',
            'name'             => 'Big Spender',
            'type'             => Coupon::TYPE_FIXED,
            'value'            => 200.00,
            'min_order_amount' => 2000.00,
            'status'           => true,
        ]);

        $cart = Cart::getOrCreate($this->user, 'sess-1', 'shopy');
        $cart->addItem($this->product, 1); // 1000 < 2000

        $response = $this->actingAs($this->user)->post(route('cart.coupon.apply'), [
            'coupon_code' => 'MIN2000',
            'mode'        => 'shopy',
        ]);

        $response->assertSessionHas('error');
        $cart->refresh();
        $this->assertNull($cart->coupon_code);
    }

    public function test_coupon_cannot_be_applied_to_unauthorized_store_mode(): void
    {
        Coupon::create([
            'code'             => 'MINUTESONLY',
            'name'             => 'Minutes Only',
            'type'             => Coupon::TYPE_FIXED,
            'value'            => 50.00,
            'mode_id'          => $this->minutesMode->id, // Restrict to Minutes
            'status'           => true,
        ]);

        // Cart is in Shopy store mode
        $cart = Cart::getOrCreate($this->user, 'sess-1', 'shopy');
        $cart->addItem($this->product, 1);

        $response = $this->actingAs($this->user)->post(route('cart.coupon.apply'), [
            'coupon_code' => 'MINUTESONLY',
            'mode'        => 'shopy',
        ]);

        $response->assertSessionHas('error');
        $cart->refresh();
        $this->assertNull($cart->coupon_code);
    }

    public function test_expired_coupon_cannot_be_applied(): void
    {
        Coupon::create([
            'code'        => 'EXPIRED',
            'name'        => 'Expired Deal',
            'type'        => Coupon::TYPE_FIXED,
            'value'       => 50.00,
            'expires_at'  => now()->subDay(), // Expired yesterday
            'status'      => true,
        ]);

        $cart = Cart::getOrCreate($this->user, 'sess-1', 'shopy');
        $cart->addItem($this->product, 1);

        $response = $this->actingAs($this->user)->post(route('cart.coupon.apply'), [
            'coupon_code' => 'EXPIRED',
            'mode'        => 'shopy',
        ]);

        $response->assertSessionHas('error');
        $cart->refresh();
        $this->assertNull($cart->coupon_code);
    }

    public function test_inactive_coupon_cannot_be_applied(): void
    {
        Coupon::create([
            'code'   => 'INACTIVE',
            'name'   => 'Disabled Deal',
            'type'   => Coupon::TYPE_FIXED,
            'value'  => 50.00,
            'status' => false,
        ]);

        $cart = Cart::getOrCreate($this->user, 'sess-1', 'shopy');
        $cart->addItem($this->product, 1);

        $response = $this->actingAs($this->user)->post(route('cart.coupon.apply'), [
            'coupon_code' => 'INACTIVE',
            'mode'        => 'shopy',
        ]);

        $response->assertSessionHas('error');
        $cart->refresh();
        $this->assertNull($cart->coupon_code);
    }

    public function test_coupon_cannot_be_used_exceeding_per_user_limit(): void
    {
        $coupon = Coupon::create([
            'code'                 => 'ONCEONLY',
            'name'                 => 'One Time Deal',
            'type'                 => Coupon::TYPE_FIXED,
            'value'                => 50.00,
            'usage_limit_per_user' => 1,
            'status'               => true,
        ]);

        // User already used it once
        CouponUsage::create([
            'coupon_id'       => $coupon->id,
            'user_id'         => $this->user->id,
            'discount_amount' => 50.00,
            'used_at'         => now(),
        ]);

        $cart = Cart::getOrCreate($this->user, 'sess-1', 'shopy');
        $cart->addItem($this->product, 1);

        $response = $this->actingAs($this->user)->post(route('cart.coupon.apply'), [
            'coupon_code' => 'ONCEONLY',
            'mode'        => 'shopy',
        ]);

        $response->assertSessionHas('error');
        $cart->refresh();
        $this->assertNull($cart->coupon_code);
    }

    public function test_user_can_remove_applied_coupon(): void
    {
        $coupon = Coupon::create([
            'code'   => 'TESTREMOVE',
            'name'   => 'Removable',
            'type'   => Coupon::TYPE_FIXED,
            'value'  => 50.00,
            'status' => true,
        ]);

        $cart = Cart::getOrCreate($this->user, 'sess-1', 'shopy');
        $cart->addItem($this->product, 1);
        $cart->coupon_code = 'TESTREMOVE';
        $cart->discount_amount = 50.00;
        $cart->save();

        $response = $this->actingAs($this->user)->post(route('cart.coupon.remove'), [
            'mode' => 'shopy',
        ]);

        $response->assertRedirect();
        $cart->refresh();

        $this->assertNull($cart->coupon_code);
        $this->assertEquals(0.00, $cart->discount());
    }
}
