<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HeaderFooterTest extends TestCase
{
    use RefreshDatabase;

    public function test_storefront_home_renders_organized_header_and_footer(): void
    {
        Category::create([
            'name' => 'Fashion',
            'slug' => 'fashion',
            'status' => true,
            'sort_order' => 1,
            'parent_id' => null,
        ]);

        $response = $this->get(route('home'));

        $response->assertStatus(200);

        // Header assets and structure
        $response->assertSee('announcement-bar', false);
        $response->assertSee('navbar', false);
        $response->assertSee('header-top', false);
        $response->assertSee('search-input', false);
        $response->assertSee('animated-placeholder', false);
        $response->assertSee('mobile-search-wrapper', false);
        $response->assertSee('mobile-category-chips', false);
        $response->assertSee('mobileToggle', false);
        $response->assertSee('mobileMenu', false);
        $response->assertSee('mobileMenuOverlay', false);
        $response->assertSee('mobileBottomNav', false);
        $response->assertSee('user-theme.css', false);

        // Brand logo
        $response->assertSee('images/logo/logo.png', false);

        // Navbar Icons for Guest (Flipkart-Style: Only Login & Cart)
        $response->assertSee('images/navbar/cart.svg', false);
        $response->assertSee('images/navbar/profile.svg', false);
        $response->assertDontSee('id="wishlistBtn"', false);
        $response->assertDontSee('id="notificationBtn"', false);
        $response->assertDontSee('My Profile', false);
        $response->assertDontSee('My Orders', false);
        $response->assertDontSee('My Cart', false);
        $response->assertDontSee('Track Order', false);
        $response->assertSee('Sign In / Login', false);

        // Category mega menu and mobile menu category
        $response->assertSee('Fashion');

        // Footer elements
        $response->assertDontSee('trust-badges-section', false);
        $response->assertSee('site-footer', false);
        $response->assertSee('footer-top', false);
        $response->assertSee('data-accordion', false);
        $response->assertSee('Subscribe to our Newsletter', false);
        $response->assertSee('newsletterForm', false);
        $response->assertSee('payment-methods-row', false);
        $response->assertSee('Have Questions?', false);
        $response->assertSee('All Rights Reserved', false);
    }

    public function test_guest_does_not_see_my_cart_and_orders_in_navigation(): void
    {
        $response = $this->get(route('home'));

        $response->assertStatus(200);
        // Header cart icon remains visible for guest shopping
        $response->assertSee('id="cartBtn"', false);
        // Navigation links for personal account are hidden for guest
        $response->assertDontSee('My Cart', false);
        $response->assertDontSee('Track Order', false);
    }

    public function test_authenticated_user_sees_my_cart_and_orders_in_navigation(): void
    {
        $user = User::factory()->create([
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee('My Cart', false);
        $response->assertSee('Orders', false);
        $response->assertSee('Track Order', false);
    }

    public function test_auth_pages_do_not_render_storefront_header_and_footer(): void
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
        $response->assertDontSee('id="siteHeader"', false);
        $response->assertDontSee('site-footer', false);
        $response->assertSee('Welcome Back');
    }

    public function test_authenticated_user_sees_account_in_header(): void
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('John');
        $response->assertSee('images/navbar/wishlist.svg', false);
        $response->assertSee('images/navbar/notification.svg', false);
        $response->assertSee('images/navbar/cart.svg', false);
        $response->assertSee('My Profile', false);
        $response->assertSee('My Orders', false);
        $response->assertSee(route('logout'));
    }

    public function test_footer_fetch_counts_endpoint_returns_json(): void
    {
        $response = $this->getJson(route('footer.fetch-counts'));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'wishlist_count',
                'cart_count',
                'notification_count',
            ]);
    }

    public function test_newsletter_subscription_endpoint_validates_and_subscribes(): void
    {
        $response = $this->postJson(route('newsletter.subscribe'), [
            'email' => 'user@example.com',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Thank you for subscribing to our newsletter!',
            ]);
    }

    public function test_newsletter_subscription_requires_valid_email(): void
    {
        $response = $this->postJson(route('newsletter.subscribe'), [
            'email' => 'invalid-email',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_user_storefront_allows_changing_both_themes_when_admin_enables_dark_mode(): void
    {
        \App\Models\AdminSetting::setDarkMode(true);

        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee('data-theme="light"', false);
        $response->assertSee('userThemeToggle', false);
    }

    public function test_user_storefront_forces_only_light_theme_when_admin_disables_dark_mode(): void
    {
        \App\Models\AdminSetting::setDarkMode(false);

        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee('data-theme="light"', false);
        $response->assertDontSee('userThemeToggle', false);
    }
}
