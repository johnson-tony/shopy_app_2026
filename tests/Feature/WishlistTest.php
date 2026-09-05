<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Mode;
use App\Models\Product;
use App\Models\User;
use App\Models\Wishlist;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WishlistTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        (new DatabaseSeeder())->run();

        $this->user = User::factory()->create([
            'status' => User::STATUS_ACTIVE,
        ]);

        $mode = Mode::first() ?? Mode::create(['name' => 'Shopy', 'slug' => 'shopy', 'status' => true]);
        $category = Category::first() ?? Category::create(['name' => 'Fashion', 'slug' => 'fashion', 'status' => true]);

        $this->product = Product::create([
            'mode_id'     => $mode->id,
            'category_id' => $category->id,
            'name'        => 'Wireless Headphones 2026',
            'slug'        => 'wireless-headphones-2026',
            'sku'         => 'WHP-2026-001',
            'price'       => 2499.00,
            'sale_price'  => 1999.00,
            'stock'       => 15,
            'status'      => true,
            'featured'    => true,
        ]);
    }

    public function test_guest_cannot_toggle_wishlist_and_receives_401(): void
    {
        $response = $this->postJson(route('wishlist.toggle'), [
            'product_id' => $this->product->id,
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success'         => false,
                'unauthenticated' => true,
            ]);

        $this->assertDatabaseMissing('wishlists', [
            'product_id' => $this->product->id,
        ]);
    }

    public function test_guest_accessing_wishlist_page_is_redirected_to_login(): void
    {
        $response = $this->get(route('wishlist.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_add_product_to_wishlist(): void
    {
        $response = $this->actingAs($this->user)->postJson(route('wishlist.toggle'), [
            'product_id' => $this->product->id,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success'     => true,
                'action'      => 'added',
                'in_wishlist' => true,
                'count'       => 1,
            ]);

        $this->assertDatabaseHas('wishlists', [
            'user_id'    => $this->user->id,
            'product_id' => $this->product->id,
        ]);

        $this->assertEquals(1, $this->user->wishlistCount());
    }

    public function test_authenticated_user_can_remove_product_by_toggling(): void
    {
        // Add to wishlist first
        Wishlist::create([
            'user_id'    => $this->user->id,
            'product_id' => $this->product->id,
        ]);

        // Toggle again to remove
        $response = $this->actingAs($this->user)->postJson(route('wishlist.toggle'), [
            'product_id' => $this->product->id,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success'     => true,
                'action'      => 'removed',
                'in_wishlist' => false,
                'count'       => 0,
            ]);

        $this->assertDatabaseMissing('wishlists', [
            'user_id'    => $this->user->id,
            'product_id' => $this->product->id,
        ]);

        $this->assertEquals(0, $this->user->wishlistCount());
    }

    public function test_authenticated_user_can_view_their_wishlist_page(): void
    {
        Wishlist::create([
            'user_id'    => $this->user->id,
            'product_id' => $this->product->id,
        ]);

        $response = $this->actingAs($this->user)->get(route('wishlist.index'));

        $response->assertStatus(200);
        $response->assertSee('My Wishlist');
        $response->assertSee('Wireless Headphones 2026');
        $response->assertSee('1,999.00');
        $response->assertSee('In Stock');
        $response->assertSee('Clear Wishlist');
    }

    public function test_empty_wishlist_displays_friendly_empty_state(): void
    {
        $response = $this->actingAs($this->user)->get(route('wishlist.index'));

        $response->assertStatus(200);
        $response->assertSee('Your Wishlist is Empty');
        $response->assertSee('Start Shopping');
    }

    public function test_authenticated_user_can_remove_item_via_destroy(): void
    {
        Wishlist::create([
            'user_id'    => $this->user->id,
            'product_id' => $this->product->id,
        ]);

        $response = $this->actingAs($this->user)->deleteJson(route('wishlist.destroy', $this->product->id));

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'count'   => 0,
            ]);

        $this->assertDatabaseMissing('wishlists', [
            'user_id'    => $this->user->id,
            'product_id' => $this->product->id,
        ]);
    }

    public function test_authenticated_user_can_clear_entire_wishlist(): void
    {
        $product2 = Product::create([
            'mode_id'     => $this->product->mode_id,
            'category_id' => $this->product->category_id,
            'name'        => 'Second Product 2026',
            'slug'        => 'second-product-2026',
            'sku'         => 'SEC-2026',
            'price'       => 999.00,
            'stock'       => 5,
            'status'      => true,
        ]);

        Wishlist::create(['user_id' => $this->user->id, 'product_id' => $this->product->id]);
        Wishlist::create(['user_id' => $this->user->id, 'product_id' => $product2->id]);

        $this->assertEquals(2, $this->user->wishlistCount());

        $response = $this->actingAs($this->user)->post(route('wishlist.clear'));

        $response->assertRedirect(route('wishlist.index'));
        $this->assertEquals(0, $this->user->wishlistCount());
    }

    public function test_user_cannot_delete_another_users_wishlist_item(): void
    {
        $otherUser = User::factory()->create(['status' => User::STATUS_ACTIVE]);

        Wishlist::create([
            'user_id'    => $otherUser->id,
            'product_id' => $this->product->id,
        ]);

        // Current user tries to delete the product
        $this->actingAs($this->user)->delete(route('wishlist.destroy', $this->product->id));

        // Other user's wishlist item still exists
        $this->assertDatabaseHas('wishlists', [
            'user_id'    => $otherUser->id,
            'product_id' => $this->product->id,
        ]);
    }

    public function test_footer_fetch_counts_returns_accurate_wishlist_count(): void
    {
        // As guest: count is 0
        $guestResponse = $this->getJson(route('footer.fetch-counts'));
        $guestResponse->assertJson(['wishlist_count' => 0]);

        // As authenticated user with 1 wishlist item
        Wishlist::create([
            'user_id'    => $this->user->id,
            'product_id' => $this->product->id,
        ]);

        $authResponse = $this->actingAs($this->user)->getJson(route('footer.fetch-counts'));
        $authResponse->assertJson(['wishlist_count' => 1]);
    }

    public function test_deleting_product_cascades_and_removes_from_wishlists(): void
    {
        Wishlist::create([
            'user_id'    => $this->user->id,
            'product_id' => $this->product->id,
        ]);

        $this->assertDatabaseHas('wishlists', [
            'product_id' => $this->product->id,
        ]);

        $this->product->delete();

        $this->assertDatabaseMissing('wishlists', [
            'product_id' => $this->product->id,
        ]);
    }

    public function test_authenticated_user_can_filter_wishlist_by_shopping_mode(): void
    {
        $minutesMode = Mode::where('slug', 'minutes')->first() ?? Mode::create(['name' => 'Minutes', 'slug' => 'minutes', 'status' => true]);
        $category = Category::first();

        $minutesProduct = Product::create([
            'mode_id'     => $minutesMode->id,
            'category_id' => $category->id,
            'name'        => 'Instant Fresh Apples',
            'slug'        => 'instant-fresh-apples',
            'sku'         => 'MIN-APPLES',
            'price'       => 120.00,
            'stock'       => 20,
            'status'      => true,
        ]);

        // Add 1 Shopy product and 1 Minutes product to wishlist
        Wishlist::create(['user_id' => $this->user->id, 'product_id' => $this->product->id]);
        Wishlist::create(['user_id' => $this->user->id, 'product_id' => $minutesProduct->id]);

        // Filter by Minutes
        $minutesResponse = $this->actingAs($this->user)->get(route('wishlist.index', ['mode' => 'minutes']));
        $minutesResponse->assertStatus(200);
        $minutesResponse->assertSee('Instant Fresh Apples');
        $minutesResponse->assertDontSee('Wireless Headphones 2026');

        // Filter by Shopy
        $shopyResponse = $this->actingAs($this->user)->get(route('wishlist.index', ['mode' => 'shopy']));
        $shopyResponse->assertStatus(200);
        $shopyResponse->assertSee('Wireless Headphones 2026');
        $shopyResponse->assertDontSee('Instant Fresh Apples');
    }
}
