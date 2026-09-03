<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Category;
use App\Models\Mode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AdminCategoryTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;
    protected Admin $superAdmin;
    protected User $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();

        $this->admin = Admin::where('email', 'admin@shopy.test')->first();
        $this->superAdmin = Admin::where('email', 'superadmin@shopy.test')->first();
        $this->customer = User::where('email', 'customer@shopy.test')->first();
    }

    public function test_guest_cannot_access_admin_categories(): void
    {
        $response = $this->get(route('admin.categories.index'));
        $response->assertRedirect(route('admin.login'));
    }

    public function test_normal_customer_cannot_access_admin_categories(): void
    {
        $response = $this->actingAs($this->customer, 'web')
            ->get(route('admin.categories.index'));

        $response->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_view_categories_index(): void
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.categories.index'));

        $response->assertStatus(200);
        $response->assertSee('Category Management');
        $response->assertSee('Fashion');
        $response->assertSee('Electronics');
    }

    public function test_admin_can_view_create_category_form(): void
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.categories.create'));

        $response->assertStatus(200);
        $response->assertSee('Create New Category');
    }

    public function test_admin_can_store_root_category(): void
    {
        $payload = [
            'name' => 'Sports & Fitness',
            'slug' => 'sports-fitness',
            'description' => 'Sporting goods, fitness equipment, and camping essentials.',
            'icon' => 'fas fa-dumbbell',
            'sort_order' => 10,
            'status' => '1',
            'is_featured' => '1',
            'meta_title' => 'Sports Gear Online',
            'meta_description' => 'High quality fitness and sports equipment.',
        ];

        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('admin.categories.store'), $payload);

        $response->assertRedirect(route('admin.categories.index'));
        $this->assertDatabaseHas('categories', [
            'name' => 'Sports & Fitness',
            'slug' => 'sports-fitness',
            'parent_id' => null,
            'status' => true,
            'is_featured' => true,
        ]);
    }

    public function test_admin_can_store_subcategory(): void
    {
        $parent = Category::where('slug', 'fashion')->first();
        $this->assertNotNull($parent);

        $payload = [
            'parent_id' => $parent->id,
            'name' => 'Shirts',
            'slug' => 'fashion-shirts',
            'description' => 'Formal, casual and party wear shirts.',
            'icon' => 'fas fa-shirt',
            'sort_order' => 5,
            'status' => '1',
            'is_featured' => '0',
        ];

        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('admin.categories.store'), $payload);

        $response->assertRedirect(route('admin.categories.index'));
        $this->assertDatabaseHas('categories', [
            'name' => 'Shirts',
            'slug' => 'fashion-shirts',
            'parent_id' => $parent->id,
            'status' => true,
            'is_featured' => false,
        ]);
    }

    public function test_admin_can_upload_category_image_to_cloudinary(): void
    {
        $mockCloudinaryUrl = 'https://res.cloudinary.com/dudhfvy5f/image/upload/v1725041234/shopy_so/category/mock_sample.jpg';

        Http::fake([
            'https://api.cloudinary.com/v1_1/*/image/upload' => Http::response([
                'secure_url' => $mockCloudinaryUrl,
                'public_id' => 'shopy_so/category/mock_sample',
                'format' => 'jpg',
                'width' => 800,
                'height' => 600,
            ], 200),
        ]);

        $file = UploadedFile::fake()->image('banner.jpg', 800, 600);

        $payload = [
            'name' => 'Automotive Accessories',
            'slug' => 'automotive-accessories',
            'image' => $file,
            'status' => '1',
        ];

        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('admin.categories.store'), $payload);

        $response->assertRedirect(route('admin.categories.index'));

        $category = Category::where('slug', 'automotive-accessories')->first();
        $this->assertNotNull($category);
        $this->assertEquals($mockCloudinaryUrl, $category->image);
        $this->assertEquals($mockCloudinaryUrl, $category->image_url);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'image/upload')
                && str_contains($request->body(), 'shopy_so/category');
        });
    }

    public function test_category_slug_is_auto_generated_when_empty(): void
    {
        $payload = [
            'name' => 'Smart Home Gadgets',
            'slug' => '',
            'status' => '1',
        ];

        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('admin.categories.store'), $payload);

        $response->assertRedirect(route('admin.categories.index'));
        $this->assertDatabaseHas('categories', [
            'name' => 'Smart Home Gadgets',
            'slug' => 'smart-home-gadgets',
        ]);
    }

    public function test_admin_cannot_set_category_as_its_own_parent(): void
    {
        $category = Category::where('slug', 'electronics')->first();
        $this->assertNotNull($category);

        $payload = [
            'name' => 'Electronics Updated',
            'slug' => 'electronics',
            'parent_id' => $category->id,
            'status' => '1',
        ];

        $response = $this->actingAs($this->admin, 'admin')
            ->put(route('admin.categories.update', $category), $payload);

        $response->assertSessionHasErrors('parent_id');
    }

    public function test_admin_can_edit_and_update_category(): void
    {
        $category = Category::where('slug', 'electronics')->first();
        $this->assertNotNull($category);

        $payload = [
            'name' => 'Electronics & Gadgets',
            'slug' => 'electronics-gadgets',
            'description' => 'Updated description for electronic gadgets.',
            'sort_order' => 99,
            'status' => '1',
            'is_featured' => '1',
        ];

        $response = $this->actingAs($this->admin, 'admin')
            ->put(route('admin.categories.update', $category), $payload);

        $response->assertRedirect(route('admin.categories.index'));
        $category->refresh();

        $this->assertEquals('Electronics & Gadgets', $category->name);
        $this->assertEquals('electronics-gadgets', $category->slug);
        $this->assertEquals(99, $category->sort_order);
    }

    public function test_admin_can_replace_category_image_and_cleanup_old_cloudinary_image(): void
    {
        $oldCloudinaryUrl = 'https://res.cloudinary.com/dudhfvy5f/image/upload/v1725041234/shopy_so/category/old_sample.jpg';
        $newCloudinaryUrl = 'https://res.cloudinary.com/dudhfvy5f/image/upload/v1725045678/shopy_so/category/new_sample.jpg';

        $category = Category::create([
            'name' => 'Tablets & E-Readers',
            'slug' => 'tablets-e-readers',
            'image' => $oldCloudinaryUrl,
            'status' => true,
        ]);

        Http::fake([
            'https://api.cloudinary.com/v1_1/*/image/upload' => Http::response([
                'secure_url' => $newCloudinaryUrl,
                'public_id' => 'shopy_so/category/new_sample',
            ], 200),
            'https://api.cloudinary.com/v1_1/*/image/destroy' => Http::response([
                'result' => 'ok',
            ], 200),
        ]);

        $file = UploadedFile::fake()->image('new_banner.jpg', 800, 600);

        $payload = [
            'name' => 'Tablets & E-Readers',
            'slug' => 'tablets-e-readers',
            'image' => $file,
            'status' => '1',
        ];

        $response = $this->actingAs($this->admin, 'admin')
            ->put(route('admin.categories.update', $category), $payload);

        $response->assertRedirect(route('admin.categories.index'));
        $category->refresh();

        $this->assertEquals($newCloudinaryUrl, $category->image);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'image/destroy')
                && $request['public_id'] === 'shopy_so/category/old_sample';
        });
    }

    public function test_admin_can_toggle_category_status(): void
    {
        $category = Category::where('slug', 'electronics')->first();
        $this->assertTrue($category->status);

        $response = $this->actingAs($this->admin, 'admin')
            ->patch(route('admin.categories.toggleStatus', $category));

        $response->assertRedirect();
        $category->refresh();
        $this->assertFalse($category->status);

        // Toggle back via AJAX
        $ajaxResponse = $this->actingAs($this->admin, 'admin')
            ->patchJson(route('admin.categories.toggleStatus', $category));

        $ajaxResponse->assertStatus(200);
        $ajaxResponse->assertJson(['success' => true, 'status' => true]);
        $category->refresh();
        $this->assertTrue($category->status);
    }

    public function test_admin_can_toggle_featured_status(): void
    {
        $category = Category::where('slug', 'sports')->first();
        $this->assertFalse($category->is_featured);

        $response = $this->actingAs($this->admin, 'admin')
            ->patch(route('admin.categories.toggleFeatured', $category));

        $response->assertRedirect();
        $category->refresh();
        $this->assertTrue($category->is_featured);
    }

    public function test_admin_can_delete_category_and_cleanup_cloudinary_image(): void
    {
        $cloudinaryUrl = 'https://res.cloudinary.com/dudhfvy5f/image/upload/v1725041234/shopy_so/category/to_delete.jpg';

        $category = Category::create([
            'name' => 'Temporary Category',
            'slug' => 'temp-cat-delete',
            'image' => $cloudinaryUrl,
            'status' => true,
        ]);

        Http::fake([
            'https://api.cloudinary.com/v1_1/*/image/destroy' => Http::response([
                'result' => 'ok',
            ], 200),
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->delete(route('admin.categories.destroy', $category));

        $response->assertRedirect(route('admin.categories.index'));
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'image/destroy')
                && $request['public_id'] === 'shopy_so/category/to_delete';
        });
    }

    public function test_category_create_form_renders_shopping_mode_options(): void
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.categories.create'));

        $response->assertStatus(200);
        $response->assertSee('Shopping Mode');
        $response->assertSee('Shopy');
        $response->assertSee('Food');
        $response->assertSee('Minutes');
    }

    public function test_admin_can_store_category_with_selected_mode(): void
    {
        $foodMode = Mode::where('slug', 'food')->firstOrFail();

        $payload = [
            'mode_id' => $foodMode->id,
            'name' => 'Fast Food & Burgers',
            'slug' => 'fast-food-burgers',
            'description' => 'Delicious burgers, wraps, and meals.',
            'status' => '1',
        ];

        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('admin.categories.store'), $payload);

        $response->assertRedirect(route('admin.categories.index'));

        $category = Category::where('slug', 'fast-food-burgers')->firstOrFail();
        $this->assertEquals($foodMode->id, $category->mode_id);
        $this->assertEquals('Food', $category->mode->name);
    }

    public function test_subcategory_inherits_parent_mode_if_omitted(): void
    {
        $minutesMode = Mode::where('slug', 'minutes')->firstOrFail();
        $parent = Category::where('slug', 'grocery')->firstOrFail();
        $this->assertEquals($minutesMode->id, $parent->mode_id);

        $payload = [
            'parent_id' => $parent->id,
            'name' => 'Organic Vegetables',
            'slug' => 'organic-vegetables',
            'status' => '1',
            // mode_id omitted intentionally
        ];

        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('admin.categories.store'), $payload);

        $response->assertRedirect(route('admin.categories.index'));

        $child = Category::where('slug', 'organic-vegetables')->firstOrFail();
        $this->assertEquals($minutesMode->id, $child->mode_id);
    }

    public function test_admin_can_filter_categories_by_mode(): void
    {
        $minutesMode = Mode::where('slug', 'minutes')->firstOrFail();

        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.categories.index', ['mode' => $minutesMode->id]));

        $response->assertStatus(200);
        $categories = $response->viewData('categories');
        $this->assertTrue($categories->contains('slug', 'grocery'));
        $this->assertFalse($categories->contains('slug', 'fashion'));
    }

    public function test_mode_with_categories_cannot_be_deleted(): void
    {
        $shopyMode = Mode::where('slug', 'shopy')->firstOrFail();

        $this->assertTrue($shopyMode->hasRelatedRecords());

        $response = $this->actingAs($this->admin, 'admin')
            ->delete(route('admin.modes.destroy', $shopyMode));

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('modes', ['slug' => 'shopy']);
    }
}
