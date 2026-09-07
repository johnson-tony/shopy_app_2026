<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Category;
use App\Models\Mode;
use App\Models\Permission;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminReviewTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;
    protected User $customer;
    protected Product $product;
    protected ProductReview $review;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        $this->admin = Admin::where('email', 'superadmin@shopy.test')->first()
            ?? Admin::where('email', 'admin@shopy.test')->first();

        $this->customer = User::create([
            'name'              => 'John Doe',
            'email'             => 'john@example.com',
            'password'          => bcrypt('password'),
            'status'            => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);

        $mode = Mode::firstOrCreate(['slug' => 'shopy'], ['name' => 'Shopy', 'status' => true]);
        $category = Category::create(['mode_id' => $mode->id, 'name' => 'Tech', 'slug' => 'tech', 'status' => true]);
        $this->product = Product::create([
            'mode_id'     => $mode->id,
            'category_id' => $category->id,
            'name'        => 'Wireless Headphones',
            'slug'        => 'wireless-headphones',
            'sku'         => 'W-HEAD-01',
            'price'       => 999.00,
            'stock'       => 10,
            'status'      => true,
        ]);

        $this->review = ProductReview::create([
            'product_id'        => $this->product->id,
            'user_id'           => $this->customer->id,
            'rating'            => 5,
            'title'             => 'Awesome sound quality',
            'comment'           => 'Really crisp bass and crystal clear trebles.',
            'is_verified_buyer' => true,
            'status'            => true,
        ]);
    }

    public function test_guest_cannot_access_admin_reviews(): void
    {
        $response = $this->get(route('admin.reviews.index'));
        $response->assertRedirect(route('admin.login'));
    }

    public function test_normal_customer_cannot_access_admin_reviews(): void
    {
        $response = $this->actingAs($this->customer)->get(route('admin.reviews.index'));
        $response->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_view_reviews_index(): void
    {
        $response = $this->actingAs($this->admin, 'admin')->get(route('admin.reviews.index'));
        $response->assertOk();
        $response->assertSee('Customer Reviews');
        $response->assertSee('Awesome sound quality');
        $response->assertSee('Wireless Headphones');
        $response->assertSee('John Doe');
    }

    public function test_admin_can_filter_reviews_by_rating_and_search(): void
    {
        $response = $this->actingAs($this->admin, 'admin')->get(route('admin.reviews.index', [
            'rating' => 5,
            'search' => 'sound quality',
        ]));
        $response->assertOk();
        $response->assertSee('Awesome sound quality');
    }

    public function test_admin_can_toggle_review_status(): void
    {
        $this->assertTrue($this->review->status);

        $response = $this->actingAs($this->admin, 'admin')->patch(route('admin.reviews.toggleStatus', $this->review));
        $response->assertRedirect();

        $this->assertFalse($this->review->fresh()->status);
    }

    public function test_admin_can_delete_review(): void
    {
        $response = $this->actingAs($this->admin, 'admin')->delete(route('admin.reviews.destroy', $this->review));
        $response->assertRedirect();

        $this->assertDatabaseMissing('product_reviews', ['id' => $this->review->id]);
    }
}
