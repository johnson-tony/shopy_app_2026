<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Category;
use App\Models\Mode;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductDetailTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_guest_can_view_product_detail_page_by_slug(): void
    {
        $product = Product::where('status', true)->firstOrFail();

        $response = $this->get(route('product.show', $product->slug));

        $response->assertStatus(200);
        $response->assertSee($product->name);
        $response->assertSee($product->returnPolicyText());
        $response->assertSee('Fast Doorstep Delivery');
        $response->assertSee('Free Delivery');
        $response->assertSee('Pay on Delivery');
        $response->assertSee('Add to Cart');
        $response->assertSee('Buy Now');
    }

    public function test_product_detail_page_syncs_session_mode(): void
    {
        $minutesMode = Mode::where('slug', 'minutes')->firstOrFail();
        $minutesProduct = Product::where('mode_id', $minutesMode->id)->where('status', true)->firstOrFail();

        $response = $this->get(route('product.show', $minutesProduct->slug));

        $response->assertStatus(200);
        $response->assertSessionHas('active_shopping_mode', 'minutes');
    }

    public function test_product_detail_displays_return_policies_accurately(): void
    {
        $shopyMode = Mode::where('slug', 'shopy')->firstOrFail();
        $category = Category::where('mode_id', $shopyMode->id)->firstOrFail();

        // 1. Non-returnable
        $nonReturnable = Product::create([
            'mode_id' => $shopyMode->id,
            'category_id' => $category->id,
            'name' => 'Non Returnable Item XYZ',
            'slug' => 'non-returnable-item-xyz',
            'sku' => 'NR-XYZ-01',
            'price' => 299,
            'stock' => 10,
            'status' => true,
            'return_policy' => 'non_returnable',
        ]);

        $res1 = $this->get(route('product.show', $nonReturnable->slug));
        $res1->assertStatus(200);
        $res1->assertSee('Non-Returnable');

        // 2. 7 Days Replacement
        $replacementItem = Product::create([
            'mode_id' => $shopyMode->id,
            'category_id' => $category->id,
            'name' => 'Replacement Electronic Item',
            'slug' => 'replacement-electronic-item',
            'sku' => 'REP-ELEC-02',
            'price' => 1499,
            'stock' => 5,
            'status' => true,
            'return_policy' => '7_days_replacement',
        ]);

        $res2 = $this->get(route('product.show', $replacementItem->slug));
        $res2->assertStatus(200);
        $res2->assertSee('7 Days Replacement');

        // 3. 10 Days Return & Exchange
        $fashionItem = Product::create([
            'mode_id' => $shopyMode->id,
            'category_id' => $category->id,
            'name' => 'Cotton Shirt Fashion Item',
            'slug' => 'cotton-shirt-fashion-item',
            'sku' => 'FASH-SHT-03',
            'price' => 799,
            'stock' => 8,
            'status' => true,
            'return_policy' => '10_days_return',
        ]);

        $res3 = $this->get(route('product.show', $fashionItem->slug));
        $res3->assertStatus(200);
        $res3->assertSee('10 Days Return &amp; Exchange', false);
    }

    public function test_food_mode_product_defaults_to_non_returnable(): void
    {
        $foodMode = Mode::where('slug', 'food')->firstOrFail();
        $foodCategory = Category::where('mode_id', $foodMode->id)->firstOrFail();

        $foodItem = Product::create([
            'mode_id' => $foodMode->id,
            'category_id' => $foodCategory->id,
            'name' => 'Fresh Butter Chicken Curry',
            'slug' => 'fresh-butter-chicken-curry',
            'sku' => 'FOOD-BC-99',
            'price' => 320,
            'stock' => 15,
            'status' => true,
            'return_policy' => null,
        ]);

        $response = $this->get(route('product.show', $foodItem->slug));
        $response->assertStatus(200);
        $response->assertSee('Non-Returnable');
    }

    public function test_custom_and_fallback_delivery_estimates(): void
    {
        $minutesMode = Mode::where('slug', 'minutes')->firstOrFail();
        $minutesCategory = Category::where('mode_id', $minutesMode->id)->firstOrFail();

        // Product with custom delivery time
        $customItem = Product::create([
            'mode_id' => $minutesMode->id,
            'category_id' => $minutesCategory->id,
            'name' => 'Ultra Fast Grocery Item',
            'slug' => 'ultra-fast-grocery-item',
            'sku' => 'MIN-FAST-01',
            'price' => 99,
            'stock' => 20,
            'status' => true,
            'delivery_time' => '8-12 mins',
        ]);

        $resCustom = $this->get(route('product.show', $customItem->slug));
        $resCustom->assertStatus(200);
        $resCustom->assertSee('8-12 mins');

        // Product with fallback delivery time in Minutes channel
        $fallbackItem = Product::create([
            'mode_id' => $minutesMode->id,
            'category_id' => $minutesCategory->id,
            'name' => 'Standard Grocery Item',
            'slug' => 'standard-grocery-item',
            'sku' => 'MIN-STD-02',
            'price' => 49,
            'stock' => 20,
            'status' => true,
            'delivery_time' => null,
        ]);

        $resFallback = $this->get(route('product.show', $fallbackItem->slug));
        $resFallback->assertStatus(200);
        $resFallback->assertSee('10-15 mins');
    }

    public function test_invalid_slug_or_inactive_product_returns_404(): void
    {
        // 1. Non-existent slug
        $response = $this->get(route('product.show', 'non-existent-slug-xyz-12345'));
        $response->assertStatus(404);

        // 2. Inactive product
        $inactiveProduct = Product::firstOrFail();
        $inactiveProduct->update(['status' => false]);

        $resInactive = $this->get(route('product.show', $inactiveProduct->slug));
        $resInactive->assertStatus(404);
    }

    public function test_out_of_stock_product_disables_add_to_cart(): void
    {
        $product = Product::where('status', true)->firstOrFail();
        $product->update(['stock' => 0]);

        $response = $this->get(route('product.show', $product->slug));

        $response->assertStatus(200);
        $response->assertSee('Currently Out of Stock');
        $response->assertSee('disabled');
    }

    public function test_channel_free_delivery_thresholds(): void
    {
        $minutesMode = Mode::where('slug', 'minutes')->firstOrFail();
        $minutesCategory = Category::where('mode_id', $minutesMode->id)->firstOrFail();
        $minutesProduct = Product::create([
            'mode_id' => $minutesMode->id,
            'category_id' => $minutesCategory->id,
            'name' => 'Minutes Threshold Test',
            'slug' => 'minutes-threshold-test',
            'sku' => 'MIN-THR-01',
            'price' => 150,
            'stock' => 10,
            'status' => true,
        ]);

        $response = $this->get(route('product.show', $minutesProduct->slug));
        $response->assertStatus(200);
        $response->assertSee('Orders above ₹199');
    }

    public function test_admin_can_set_return_policy_and_delivery_time_on_product(): void
    {
        $admin = Admin::firstOrFail();
        $shopyMode = Mode::where('slug', 'shopy')->firstOrFail();
        $category = Category::where('mode_id', $shopyMode->id)->firstOrFail();

        $productData = [
            'mode_id' => $shopyMode->id,
            'category_id' => $category->id,
            'name' => 'Smart Watch Pro Max',
            'slug' => 'smart-watch-pro-max',
            'sku' => 'SW-PRO-MAX',
            'price' => 2999,
            'stock' => 25,
            'status' => 1,
            'return_policy' => '7_days_replacement',
            'delivery_time' => '2-3 days',
        ];

        $response = $this->actingAs($admin, 'admin')->post(route('admin.products.store'), $productData);
        $response->assertRedirect(route('admin.products.index'));

        $this->assertDatabaseHas('products', [
            'slug' => 'smart-watch-pro-max',
            'return_policy' => '7_days_replacement',
            'delivery_time' => '2-3 days',
        ]);
    }

    public function test_user_can_add_product_to_cart_with_quantity(): void
    {
        $user = User::firstOrFail();
        $product = Product::where('status', true)->where('stock', '>=', 5)->firstOrFail();

        $response = $this->actingAs($user)->postJson(route('cart.add'), [
            'product_id' => $product->id,
            'quantity' => 3,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product->id,
            'quantity' => 3,
        ]);
    }

    public function test_quantity_stepper_is_only_visible_for_minutes_and_food_modes(): void
    {
        $shopyMode = Mode::where('slug', 'shopy')->firstOrFail();
        $minutesMode = Mode::where('slug', 'minutes')->firstOrFail();
        $foodMode = Mode::where('slug', 'food')->firstOrFail();

        $shopyProduct = Product::where('mode_id', $shopyMode->id)->where('status', true)->firstOrFail();
        $minutesProduct = Product::where('mode_id', $minutesMode->id)->where('status', true)->firstOrFail();
        $foodProduct = Product::where('mode_id', $foodMode->id)->where('status', true)->firstOrFail();

        // 1. Shopy mode: NO quantity stepper buttons, fixed hidden input 1
        $resShopy = $this->get(route('product.show', $shopyProduct->slug));
        $resShopy->assertStatus(200);
        $resShopy->assertSee('type="hidden" id="pdpQuantity" value="1"', false);
        $resShopy->assertDontSee('fa-minus', false);
        $resShopy->assertDontSee('fa-plus', false);

        // 2. Minutes mode: Quantity stepper buttons present
        $resMinutes = $this->get(route('product.show', $minutesProduct->slug));
        $resMinutes->assertStatus(200);
        $resMinutes->assertSee('fa-minus', false);
        $resMinutes->assertSee('fa-plus', false);

        // 3. Food mode: Quantity stepper buttons present
        $resFood = $this->get(route('product.show', $foodProduct->slug));
        $resFood->assertStatus(200);
        $resFood->assertSee('fa-minus', false);
        $resFood->assertSee('fa-plus', false);
    }

    public function test_product_detail_displays_gallery_thumbnails_and_variants(): void
    {
        $shopyMode = Mode::where('slug', 'shopy')->firstOrFail();
        $category = Category::where('mode_id', $shopyMode->id)->firstOrFail();

        $variantProduct = Product::create([
            'mode_id' => $shopyMode->id,
            'category_id' => $category->id,
            'name' => 'Premium Titanium Gadget',
            'slug' => 'premium-titanium-gadget',
            'sku' => 'VAR-GAD-01',
            'price' => 999.00,
            'stock' => 25,
            'status' => true,
            'images' => [
                'https://placehold.co/400x400/e2e8f0/475569?text=Gadget+Angle1',
                'https://placehold.co/400x400/334155/f8fafc?text=Gadget+Angle2',
            ],
            'colors' => [
                ['name' => 'Midnight', 'hex' => '#0f172a'],
                ['name' => 'Silver', 'hex' => '#cbd5e1'],
            ],
            'sizes' => ['128GB', '256GB', '512GB'],
        ]);

        $response = $this->get(route('product.show', $variantProduct->slug));
        $response->assertStatus(200);

        // Gallery thumbnails rendered
        $response->assertSee('gallery-thumb-btn', false);
        $response->assertSee('Gadget+Angle1', false);
        $response->assertSee('Gadget+Angle2', false);

        // Color swatches rendered
        $response->assertSee('color-swatch-btn', false);
        $response->assertSee('Midnight', false);
        $response->assertSee('Silver', false);

        // Size pills rendered
        $response->assertSee('size-pill-btn', false);
        $response->assertSee('128GB', false);
        $response->assertSee('256GB', false);
        $response->assertSee('512GB', false);
    }

    public function test_user_can_add_product_to_cart_with_color_and_size_variants(): void
    {
        $user = User::factory()->create();
        $product = Product::where('status', true)->firstOrFail();

        $response = $this->actingAs($user)->postJson(route('cart.add'), [
            'product_id' => $product->id,
            'quantity' => 2,
            'color' => 'Space Black',
            'size' => '256GB',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product->id,
            'quantity' => 2,
            'color' => 'Space Black',
            'size' => '256GB',
        ]);

        // Cart page displays selected variant badges
        $cartPage = $this->actingAs($user)->get(route('cart.index'));
        $cartPage->assertStatus(200);
        $cartPage->assertSee('Space Black');
        $cartPage->assertSee('256GB');
    }
}
