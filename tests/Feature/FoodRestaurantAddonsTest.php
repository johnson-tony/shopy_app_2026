<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Mode;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Restaurant;
use App\Models\User;
use App\Models\UserAddress;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FoodRestaurantAddonsTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Mode $foodMode;
    protected Restaurant $restaurantA;
    protected Restaurant $restaurantB;
    protected Category $category;
    protected Product $biryaniProduct;
    protected Product $friedRiceProduct;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'phone'  => '9876543210',
            'status' => User::STATUS_ACTIVE,
        ]);

        $this->foodMode = Mode::firstOrCreate(
            ['slug' => 'food'],
            ['name' => 'Food Delivery', 'status' => true, 'icon' => 'fa-solid fa-utensils']
        );

        $this->restaurantA = Restaurant::create([
            'name'          => 'Thalappakatti Biryani',
            'slug'          => 'thalappakatti-biryani',
            'cuisine'       => 'South Indian, Biryani',
            'rating'        => 4.6,
            'ratings_count' => 1250,
            'delivery_time' => 30,
            'cost_for_two'  => 500,
            'address'       => '12 Anna Salai',
            'city'          => 'Chennai',
            'is_pure_veg'   => false,
            'status'        => true,
        ]);

        $this->restaurantB = Restaurant::create([
            'name'          => 'Buhari Hotel',
            'slug'          => 'buhari-hotel',
            'cuisine'       => 'Mughlai, Chinese',
            'rating'        => 4.4,
            'ratings_count' => 840,
            'delivery_time' => 35,
            'cost_for_two'  => 450,
            'address'       => '45 Mount Road',
            'city'          => 'Chennai',
            'is_pure_veg'   => false,
            'status'        => true,
        ]);

        $this->category = Category::create([
            'mode_id'    => $this->foodMode->id,
            'name'       => 'Biryani Specials',
            'slug'       => 'biryani-specials',
            'status'     => true,
            'sort_order' => 1,
        ]);

        $this->biryaniProduct = Product::create([
            'mode_id'       => $this->foodMode->id,
            'category_id'   => $this->category->id,
            'restaurant_id' => $this->restaurantA->id,
            'name'          => 'Dindigul Mutton Biryani',
            'slug'          => 'dindigul-mutton-biryani',
            'sku'           => 'THAL-MB-01',
            'price'         => 350.00,
            'stock'         => 25,
            'is_veg'        => false,
            'status'        => true,
            'addons'        => [
                [
                    'group_name' => 'Gravies & Sides',
                    'items' => [
                        ['name' => 'Extra Salna Gravy', 'price' => 25.00],
                        ['name' => 'Boiled Egg', 'price' => 15.00],
                    ],
                ],
                [
                    'group_name' => 'Beverages',
                    'items' => [
                        ['name' => 'Coca-Cola 330ml', 'price' => 40.00],
                    ],
                ],
            ],
        ]);

        $this->friedRiceProduct = Product::create([
            'mode_id'       => $this->foodMode->id,
            'category_id'   => $this->category->id,
            'restaurant_id' => $this->restaurantB->id,
            'name'          => 'Chicken Fried Rice',
            'slug'          => 'chicken-fried-rice',
            'sku'           => 'BUH-CFR-01',
            'price'         => 260.00,
            'stock'         => 20,
            'is_veg'        => false,
            'status'        => true,
        ]);
    }

    public function test_add_to_cart_with_addons_calculates_correct_unit_price(): void
    {
        $selectedAddons = [
            ['name' => 'Extra Salna Gravy', 'price' => 25.00, 'group' => 'Gravies & Sides'],
            ['name' => 'Coca-Cola 330ml', 'price' => 40.00, 'group' => 'Beverages'],
        ];

        $response = $this->actingAs($this->user)->postJson(route('cart.add'), [
            'product_id' => $this->biryaniProduct->id,
            'quantity'   => 2,
            'addons'     => $selectedAddons,
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        $cart = Cart::where('user_id', $this->user->id)->first();
        $this->assertNotNull($cart);
        $this->assertCount(1, $cart->items);

        $item = $cart->items->first();
        // Base price 350 + addons (25 + 40 = 65) = 415.00 per unit
        $this->assertEquals(415.00, (float)$item->unit_price);
        $this->assertEquals(2, $item->quantity);
        $this->assertEquals(830.00, (float)$item->lineTotal());

        $this->assertTrue($item->hasAddons());
        $this->assertCount(2, $item->addonsList());
        $this->assertStringContainsString('Extra Salna Gravy', $item->formattedAddons());
    }

    public function test_different_addons_create_separate_cart_items(): void
    {
        // 1. Biryani with Salna
        $this->actingAs($this->user)->postJson(route('cart.add'), [
            'product_id' => $this->biryaniProduct->id,
            'quantity'   => 1,
            'addons'     => [['name' => 'Extra Salna Gravy', 'price' => 25.00]],
        ])->assertOk();

        // 2. Biryani with Coke
        $this->actingAs($this->user)->postJson(route('cart.add'), [
            'product_id' => $this->biryaniProduct->id,
            'quantity'   => 1,
            'addons'     => [['name' => 'Coca-Cola 330ml', 'price' => 40.00]],
        ])->assertOk();

        // 3. Biryani with no add-ons
        $this->actingAs($this->user)->postJson(route('cart.add'), [
            'product_id' => $this->biryaniProduct->id,
            'quantity'   => 1,
        ])->assertOk();

        $cart = Cart::where('user_id', $this->user->id)->first();
        $this->assertCount(3, $cart->items);

        // Prices: 375 (350+25), 390 (350+40), 350 (no addons)
        $prices = $cart->items->pluck('unit_price')->map(fn($p) => (float)$p)->sort()->values()->toArray();
        $this->assertEquals([350.0, 375.0, 390.0], $prices);
    }

    public function test_same_addons_increments_existing_cart_item_quantity(): void
    {
        $addons = [['name' => 'Extra Salna Gravy', 'price' => 25.00]];

        // Add 1st time
        $this->actingAs($this->user)->postJson(route('cart.add'), [
            'product_id' => $this->biryaniProduct->id,
            'quantity'   => 1,
            'addons'     => $addons,
        ])->assertOk();

        // Add 2nd time with exact same add-ons
        $this->actingAs($this->user)->postJson(route('cart.add'), [
            'product_id' => $this->biryaniProduct->id,
            'quantity'   => 2,
            'addons'     => $addons,
        ])->assertOk();

        $cart = Cart::where('user_id', $this->user->id)->first();
        $this->assertCount(1, $cart->items);
        $this->assertEquals(3, $cart->items->first()->quantity);
        $this->assertEquals(375.00, (float)$cart->items->first()->unit_price);
    }

    public function test_single_restaurant_cart_conflict_returns_409(): void
    {
        // 1. Add item from Restaurant A
        $this->actingAs($this->user)->postJson(route('cart.add'), [
            'product_id' => $this->biryaniProduct->id,
            'quantity'   => 1,
        ])->assertOk();

        // 2. Attempt to add item from Restaurant B
        $response = $this->actingAs($this->user)->postJson(route('cart.add'), [
            'product_id' => $this->friedRiceProduct->id,
            'quantity'   => 1,
        ]);

        $response->assertStatus(409)
            ->assertJson([
                'success'             => false,
                'restaurant_conflict' => true,
                'current_restaurant'  => 'Thalappakatti Biryani',
                'new_restaurant'      => 'Buhari Hotel',
            ]);

        // Cart should still only contain Restaurant A item
        $cart = Cart::where('user_id', $this->user->id)->first();
        $this->assertCount(1, $cart->items);
        $this->assertEquals($this->biryaniProduct->id, $cart->items->first()->product_id);
    }

    public function test_replace_cart_clears_previous_restaurant_items_and_adds_new(): void
    {
        // 1. Add item from Restaurant A
        $this->actingAs($this->user)->postJson(route('cart.add'), [
            'product_id' => $this->biryaniProduct->id,
            'quantity'   => 1,
        ])->assertOk();

        // 2. Replace cart with item from Restaurant B
        $response = $this->actingAs($this->user)->postJson(route('cart.add'), [
            'product_id'   => $this->friedRiceProduct->id,
            'quantity'     => 2,
            'replace_cart' => true,
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        $cart = Cart::where('user_id', $this->user->id)->first();
        $this->assertCount(1, $cart->items);
        $this->assertEquals($this->friedRiceProduct->id, $cart->items->first()->product_id);
        $this->assertEquals(2, $cart->items->first()->quantity);
        $this->assertEquals('Buhari Hotel', $cart->currentRestaurant()->name);
    }

    public function test_checkout_saves_restaurant_name_and_addons_to_order_items(): void
    {
        // Add item with add-ons
        $selectedAddons = [
            ['name' => 'Extra Salna Gravy', 'price' => 25.00],
            ['name' => 'Boiled Egg', 'price' => 15.00],
        ];

        $this->actingAs($this->user)->postJson(route('cart.add'), [
            'product_id' => $this->biryaniProduct->id,
            'quantity'   => 2,
            'addons'     => $selectedAddons,
        ])->assertOk();

        // Create user address
        $address = UserAddress::create([
            'user_id'       => $this->user->id,
            'full_name'     => 'Tony Stark',
            'phone'         => '9876543210',
            'address_line1' => '10880 Malibu Point',
            'city'          => 'Chennai',
            'state'         => 'Tamil Nadu',
            'postal_code'   => '600001',
            'is_default'    => true,
            'address_type'  => 'home',
        ]);

        // Place order
        $response = $this->actingAs($this->user)
            ->withSession(['active_shopping_mode' => 'food'])
            ->post(route('checkout.place_order'), [
                'address_source' => 'existing',
                'address_id'     => $address->id,
                'payment_method' => 'pay_on_delivery',
                'notes'          => 'Please include extra spoons',
            ]);

        $order = Order::where('user_id', $this->user->id)->latest()->first();
        $this->assertNotNull($order);
        $this->assertCount(1, $order->items);

        $orderItem = $order->items->first();
        $this->assertEquals('Thalappakatti Biryani', $orderItem->restaurant_name);
        $this->assertTrue($orderItem->hasAddons());
        $this->assertCount(2, $orderItem->addonsList());
        $this->assertEquals(390.00, (float)$orderItem->unit_price);
        $this->assertEquals(780.00, (float)$orderItem->subtotal);
        $this->assertStringContainsString('Extra Salna Gravy', $orderItem->formattedAddons());
        $this->assertStringContainsString('Boiled Egg', $orderItem->formattedAddons());
    }

    public function test_product_detail_page_displays_restaurant_and_addons(): void
    {
        $response = $this->get(route('product.show', $this->biryaniProduct->slug));

        $response->assertOk();
        $response->assertSee('Thalappakatti Biryani');
        $response->assertSee('Extra Salna Gravy');
        $response->assertSee('Boiled Egg');
        $response->assertSee('Coca-Cola 330ml');
        $response->assertSee('Customise Your Food');
    }
}
