<?php

namespace Tests\Feature;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Storefront home page is accessible and displays modes, categories, and products.
     */
    public function test_storefront_home_displays_categories_and_products_and_login_link(): void
    {
        $this->seed(DatabaseSeeder::class);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Shop by Category');
        $response->assertSee('Best Sellers &amp; New Arrivals', false);
        $response->assertSee('Sign In / Login');
        $response->assertSee('Shopy');
        $response->assertSee('Food');
        $response->assertSee('Minutes');
    }
}
