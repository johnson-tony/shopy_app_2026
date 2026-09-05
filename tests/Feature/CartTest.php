<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Mode;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Mode $shopyMode;
    protected Mode $minutesMode;
    protected Category $category;
    protected Product $product;
    protected Product $outOfStockProduct;

    protected function setUp(): void
    {
        parent::setUp();

        $this->shopyMode = Mode::firstOrCreate(
            ['slug' => 'shopy'],
            ['name' => 'Shopy', 'status' => true, 'icon' => 'fa-solid fa-bag-shopping']
        );

        $this->minutesMode = Mode::firstOrCreate(
            ['slug' => 'minutes'],
            ['name' => 'Minutes', 'status' => true, 'icon' => 'fa-solid fa-bolt']
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
            'price'       => 2000.00,
            'sale_price'  => 1500.00,
            'stock'       => 10,
            'status'      => true,
        ]);

        $this->outOfStockProduct = Product::create([
            'mode_id'     => $this->shopyMode->id,
            'category_id' => $this->category->id,
            'name'        => 'Sold Out Phone',
            'slug'        => 'sold-out-phone',
            'sku'         => 'SOLD-2026',
            'price'       => 10000.00,
            'stock'       => 0,
            'status'      => true,
        ]);

        $this->user = User::factory()->create([
            'status'            => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);
    }

    public function test_guest_can_add_product_to_cart_via_ajax(): void
    {
        $response = $this->postJson(route('cart.add'), [
            'product_id' => $this->product->id,
            'quantity'   => 2,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success'       => true,
                'action'        => 'added',
                'item_quantity' => 2,
                'cart_count'    => 2,
            ]);

        $this->assertDatabaseHas('cart_items', [
            'product_id' => $this->product->id,
            'quantity'   => 2,
            'unit_price' => 1500.00, // Used sale_price
        ]);
    }

    public function test_authenticated_user_can_add_product_to_cart_via_ajax(): void
    {
        $response = $this->actingAs($this->user)->postJson(route('cart.add'), [
            'product_id' => $this->product->id,
            'quantity'   => 1,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success'       => true,
                'item_quantity' => 1,
                'cart_count'    => 1,
            ]);

        $this->assertDatabaseHas('carts', [
            'user_id' => $this->user->id,
            'mode_id' => $this->shopyMode->id,
        ]);

        $this->assertEquals(1, $this->user->cartCount('shopy'));
    }

    public function test_cannot_add_out_of_stock_product_to_cart(): void
    {
        $response = $this->postJson(route('cart.add'), [
            'product_id' => $this->outOfStockProduct->id,
            'quantity'   => 1,
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);

        $this->assertDatabaseMissing('cart_items', [
            'product_id' => $this->outOfStockProduct->id,
        ]);
    }

    public function test_adding_existing_product_increments_quantity(): void
    {
        $cart = Cart::getOrCreate($this->user, 'test-sess', 'shopy');
        $cart->addItem($this->product, 2);

        $response = $this->actingAs($this->user)->postJson(route('cart.add'), [
            'product_id' => $this->product->id,
            'quantity'   => 3,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success'       => true,
                'item_quantity' => 5,
            ]);

        $this->assertDatabaseHas('cart_items', [
            'cart_id'    => $cart->id,
            'product_id' => $this->product->id,
            'quantity'   => 5,
        ]);
    }

    public function test_cannot_increment_quantity_beyond_available_stock(): void
    {
        // Stock is 10
        $cart = Cart::getOrCreate($this->user, 'test-sess', 'shopy');
        $cart->addItem($this->product, 8);

        // Try adding 5 more (8 + 5 = 13 > 10)
        $this->actingAs($this->user)->postJson(route('cart.add'), [
            'product_id' => $this->product->id,
            'quantity'   => 5,
        ]);

        // Clamped to 10
        $this->assertDatabaseHas('cart_items', [
            'cart_id'    => $cart->id,
            'product_id' => $this->product->id,
            'quantity'   => 10,
        ]);
    }

    public function test_user_can_update_item_quantity(): void
    {
        $cart = Cart::getOrCreate($this->user, 'test-sess', 'shopy');
        $cart->addItem($this->product, 2);

        $response = $this->actingAs($this->user)->postJson(route('cart.update'), [
            'product_id' => $this->product->id,
            'quantity'   => 4,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success'       => true,
                'item_quantity' => 4,
            ]);

        $this->assertDatabaseHas('cart_items', [
            'cart_id'    => $cart->id,
            'product_id' => $this->product->id,
            'quantity'   => 4,
        ]);
    }

    public function test_setting_quantity_to_zero_removes_item(): void
    {
        $cart = Cart::getOrCreate($this->user, 'test-sess', 'shopy');
        $cart->addItem($this->product, 2);

        $response = $this->actingAs($this->user)->postJson(route('cart.update'), [
            'product_id' => $this->product->id,
            'quantity'   => 0,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success'      => true,
                'item_removed' => true,
            ]);

        $this->assertDatabaseMissing('cart_items', [
            'cart_id'    => $cart->id,
            'product_id' => $this->product->id,
        ]);
    }

    public function test_user_can_remove_item_from_cart(): void
    {
        $cart = Cart::getOrCreate($this->user, 'test-sess', 'shopy');
        $cart->addItem($this->product, 2);

        $response = $this->actingAs($this->user)->deleteJson(route('cart.remove', $this->product->id));

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseMissing('cart_items', [
            'cart_id'    => $cart->id,
            'product_id' => $this->product->id,
        ]);
    }

    public function test_user_can_clear_cart(): void
    {
        $cart = Cart::getOrCreate($this->user, 'test-sess', 'shopy');
        $cart->addItem($this->product, 2);

        $response = $this->actingAs($this->user)->postJson(route('cart.clear'));

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertEquals(0, $cart->items()->count());
    }

    public function test_cart_calculations_subtotal_tax_delivery_grand_total(): void
    {
        // 1 item @ ₹1500
        $cart = Cart::getOrCreate($this->user, 'test-sess', 'shopy');
        $cart->addItem($this->product, 1);

        // Subtotal = 1500.00
        $this->assertEquals(1500.00, $cart->subtotal());

        // Subtotal >= 499.00 -> Free delivery
        $this->assertEquals(0.00, $cart->deliveryFee());

        // 5% Tax on 1500 = 75.00
        $this->assertEquals(75.00, $cart->taxAmount());

        // Grand Total = 1500 + 0 + 75 = 1575.00
        $this->assertEquals(1575.00, $cart->grandTotal());
    }

    public function test_delivery_fee_charged_when_subtotal_below_threshold(): void
    {
        $cheapProduct = Product::create([
            'mode_id'     => $this->shopyMode->id,
            'category_id' => $this->category->id,
            'name'        => 'Notebook',
            'slug'        => 'notebook',
            'price'       => 100.00,
            'stock'       => 50,
            'status'      => true,
        ]);

        $cart = Cart::getOrCreate($this->user, 'test-sess', 'shopy');
        $cart->addItem($cheapProduct, 1);

        // Subtotal = 100.00 < 499.00 -> Shopy base delivery fee is 50.00
        $this->assertEquals(100.00, $cart->subtotal());
        $this->assertEquals(50.00, $cart->deliveryFee());
        $this->assertEquals(399.00, $cart->amountNeededForFreeDelivery());
    }

    public function test_apply_and_remove_coupon_code(): void
    {
        $cart = Cart::getOrCreate($this->user, 'test-sess', 'shopy');
        $cart->addItem($this->product, 1); // 1500

        // Apply SAVE10 (10% discount on 1500 = 150)
        $response = $this->actingAs($this->user)->post(route('cart.coupon.apply'), [
            'coupon_code' => 'SAVE10',
            'mode'        => 'shopy',
        ]);

        $response->assertRedirect();
        $cart->refresh();
        $this->assertEquals('SAVE10', $cart->coupon_code);
        $this->assertEquals(150.00, $cart->discount());

        // Remove coupon
        $removeResponse = $this->actingAs($this->user)->post(route('cart.coupon.remove'), [
            'mode' => 'shopy',
        ]);

        $removeResponse->assertRedirect();
        $cart->refresh();
        $this->assertNull($cart->coupon_code);
        $this->assertEquals(0.00, $cart->discount());
    }

    public function test_guest_cart_is_merged_into_user_account_on_login(): void
    {
        // 1. Guest adds product
        $session = 'guest-session-12345';
        $guestCart = Cart::getOrCreate(null, $session, 'shopy');
        $guestCart->addItem($this->product, 3);

        $this->assertDatabaseHas('carts', [
            'session_id' => $session,
            'user_id'    => null,
        ]);

        // 2. Perform merge
        Cart::mergeGuestCart($session, $this->user->id);

        // 3. User now has the items
        $userCart = Cart::where('user_id', $this->user->id)->first();
        $this->assertNotNull($userCart);
        $this->assertEquals(3, $userCart->totalQuantity());

        // 4. Temporary guest cart is cleaned up
        $this->assertDatabaseMissing('carts', [
            'session_id' => $session,
            'user_id'    => null,
        ]);
    }

    public function test_cart_page_renders_with_items_and_calculations(): void
    {
        $cart = Cart::getOrCreate($this->user, 'test-sess', 'shopy');
        $cart->addItem($this->product, 2);

        $response = $this->actingAs($this->user)->get(route('cart.index', ['mode' => 'shopy']));

        $response->assertStatus(200);
        $response->assertSee('Shopy Cart');
        $response->assertSee('Wireless Headphones 2026');
        $response->assertSee('Order Summary');
        $response->assertSee('Proceed to Checkout');
        $response->assertSee('3,000.00'); // 2 * 1500
    }

    public function test_empty_cart_page_renders_friendly_message(): void
    {
        $response = $this->actingAs($this->user)->get(route('cart.index'));

        $response->assertStatus(200);
        $response->assertSee('Your Cart is Empty');
        $response->assertSee('Start Shopping');
    }

    public function test_footer_fetch_counts_returns_accurate_cart_count(): void
    {
        $cart = Cart::getOrCreate($this->user, 'test-sess', 'shopy');
        $cart->addItem($this->product, 4);

        $response = $this->actingAs($this->user)->getJson(route('footer.fetch-counts', ['mode' => 'shopy']));

        $response->assertStatus(200)
            ->assertJson([
                'cart_count'  => 4,
                'active_mode' => 'shopy',
            ]);
    }
}
