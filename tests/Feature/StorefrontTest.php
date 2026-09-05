<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Mode;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_guest_can_access_storefront_without_being_redirected_to_login(): void
    {
        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee('Shop by Category');
        $response->assertSee('Best Sellers &amp; New Arrivals', false);
        // Header contains Login link for guests
        $response->assertSee('Sign In / Login');
    }

    public function test_storefront_displays_shopping_modes_and_categories(): void
    {
        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee('All Stores');
        $response->assertSee('Shopy');
        $response->assertSee('Food');
        $response->assertSee('Minutes');

        // Check categories in DOM
        $response->assertSee('featured-categories-scroll', false);
        $response->assertSee('category-card', false);
        $response->assertSee('category-icon-bg', false);
    }

    public function test_storefront_displays_product_cards_with_prices_and_discounts(): void
    {
        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee('product-card', false);
        $response->assertSee('product-image-wrapper', false);
        $response->assertSee('current-price', false);
        $response->assertSee('Add to Cart');
    }

    public function test_user_can_filter_storefront_by_mode(): void
    {
        $foodMode = Mode::where('slug', 'food')->firstOrFail();
        $foodProduct = Product::where('mode_id', $foodMode->id)->firstOrFail();
        $minutesMode = Mode::where('slug', 'minutes')->firstOrFail();
        $minutesProduct = Product::where('mode_id', $minutesMode->id)->firstOrFail();

        $response = $this->get(route('home', ['mode' => 'food']));

        $response->assertStatus(200);
        $response->assertSee('Food Channel Active');
        $response->assertSee($foodProduct->name);
        // Minutes product is not in the filtered grid
        $response->assertDontSee($minutesProduct->name);
    }

    public function test_user_can_filter_storefront_by_category(): void
    {
        $category = Category::whereHas('products')->firstOrFail();
        $productInCategory = $category->products()->firstOrFail();

        $response = $this->get(route('home', ['category' => $category->slug]));

        $response->assertStatus(200);
        $response->assertSee($productInCategory->name);
    }

    public function test_authenticated_user_sees_account_in_header_on_storefront(): void
    {
        $user = User::first();

        $response = $this->actingAs($user)->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee($user->name);
        $response->assertSee('My Profile');
        $response->assertDontSee('Sign In / Login');
    }

    public function test_ajax_category_products_endpoint(): void
    {
        $category = Category::whereHas('products')->firstOrFail();

        $response = $this->getJson(route('category.products.ajax', ['slug' => $category->slug]));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'count',
            'html',
        ]);
        $this->assertTrue($response->json('success'));
    }

    public function test_clicking_login_opens_login_page(): void
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
        $response->assertSee('Sign in to your customer account');
    }
}
