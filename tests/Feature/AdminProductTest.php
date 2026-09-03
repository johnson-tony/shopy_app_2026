<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Category;
use App\Models\Mode;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Database\Seeders\ModeSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AdminProductTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        $this->admin = Admin::where('email', 'superadmin@shopy.test')->first()
            ?? Admin::where('email', 'admin@shopy.test')->first();
    }

    public function test_guest_cannot_access_admin_products(): void
    {
        $response = $this->get(route('admin.products.index'));
        $response->assertRedirect(route('admin.login'));
    }

    public function test_normal_customer_cannot_access_admin_products(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('admin.products.index'));
        $response->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_view_products_index(): void
    {
        $mode = Mode::where('slug', 'shopy')->firstOrFail();
        $category = Category::where('mode_id', $mode->id)->firstOrFail();

        Product::create([
            'mode_id' => $mode->id,
            'category_id' => $category->id,
            'name' => 'Test Gaming Mouse',
            'slug' => 'test-gaming-mouse',
            'sku' => 'TEST-MOU-01',
            'price' => 59.99,
            'sale_price' => 49.99,
            'stock' => 25,
            'status' => true,
            'featured' => true,
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.products.index'));

        $response->assertStatus(200);
        $response->assertSee('Product Management');
        $response->assertSee('Test Gaming Mouse');
        $response->assertSee('TEST-MOU-01');
        $response->assertSee('$59.99');
        $response->assertSee('$49.99');
        $response->assertSee('Total Products');
    }

    public function test_admin_can_search_products(): void
    {
        $mode = Mode::where('slug', 'shopy')->firstOrFail();
        $category = Category::where('mode_id', $mode->id)->firstOrFail();

        Product::create([
            'mode_id' => $mode->id,
            'category_id' => $category->id,
            'name' => 'Specific Unique Product Alpha',
            'slug' => 'unique-alpha',
            'sku' => 'UNI-ALPHA',
            'price' => 100.00,
            'stock' => 10,
        ]);

        Product::create([
            'mode_id' => $mode->id,
            'category_id' => $category->id,
            'name' => 'Unrelated Product Beta',
            'slug' => 'unrelated-beta',
            'sku' => 'UNR-BETA',
            'price' => 200.00,
            'stock' => 10,
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.products.index', ['search' => 'Alpha']));

        $response->assertStatus(200);
        $response->assertSee('Specific Unique Product Alpha');
        $response->assertDontSee('Unrelated Product Beta');
    }

    public function test_admin_can_filter_products_by_mode(): void
    {
        $shopyMode = Mode::where('slug', 'shopy')->firstOrFail();
        $minutesMode = Mode::where('slug', 'minutes')->firstOrFail();

        $shopyCat = Category::where('mode_id', $shopyMode->id)->firstOrFail();
        $minutesCat = Category::where('mode_id', $minutesMode->id)->firstOrFail();

        Product::create([
            'mode_id' => $shopyMode->id,
            'category_id' => $shopyCat->id,
            'name' => 'Fashion Silk Scarf',
            'slug' => 'silk-scarf',
            'price' => 25.00,
            'stock' => 15,
        ]);

        Product::create([
            'mode_id' => $minutesMode->id,
            'category_id' => $minutesCat->id,
            'name' => 'Whole Wheat Bread 400g',
            'slug' => 'whole-wheat-bread',
            'price' => 2.50,
            'stock' => 40,
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.products.index', ['mode' => $minutesMode->id]));

        $response->assertStatus(200);
        $response->assertSee('Whole Wheat Bread 400g');
        $response->assertDontSee('Fashion Silk Scarf');
    }

    public function test_admin_can_filter_products_by_category(): void
    {
        $shopyMode = Mode::where('slug', 'shopy')->firstOrFail();
        $categories = Category::where('mode_id', $shopyMode->id)->take(2)->get();
        $cat1 = $categories[0];
        $cat2 = $categories[1];

        Product::create([
            'mode_id' => $shopyMode->id,
            'category_id' => $cat1->id,
            'name' => 'Category One Product',
            'slug' => 'cat-one-prod',
            'price' => 10.00,
            'stock' => 5,
        ]);

        Product::create([
            'mode_id' => $shopyMode->id,
            'category_id' => $cat2->id,
            'name' => 'Category Two Product',
            'slug' => 'cat-two-prod',
            'price' => 20.00,
            'stock' => 5,
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.products.index', ['category' => $cat1->id]));

        $response->assertStatus(200);
        $response->assertSee('Category One Product');
        $response->assertDontSee('Category Two Product');
    }

    public function test_admin_can_view_create_product_form(): void
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.products.create'));

        $response->assertStatus(200);
        $response->assertSee('Create New Product');
        $response->assertSee('Shopping Mode');
        $response->assertSee('Category');
        $response->assertSee('Regular Price');
        $response->assertSee('Cloudinary: shopy_so/products');
    }

    public function test_admin_can_store_product_with_valid_data(): void
    {
        $foodMode = Mode::where('slug', 'food')->firstOrFail();
        $foodCat = Category::where('mode_id', $foodMode->id)->firstOrFail();

        $payload = [
            'mode_id' => $foodMode->id,
            'category_id' => $foodCat->id,
            'name' => 'BBQ Chicken Wings',
            'sku' => 'FOD-WNG-01',
            'short_description' => 'Crispy wings tossed in smoky BBQ sauce.',
            'description' => 'Freshly baked and fried chicken wings coated with sweet and tangy homemade BBQ sauce.',
            'price' => '13.50',
            'sale_price' => '10.99',
            'stock' => '50',
            'status' => '1',
            'featured' => '1',
            'sort_order' => '5',
        ];

        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('admin.products.store'), $payload);

        $response->assertRedirect(route('admin.products.index'));

        $product = Product::where('sku', 'FOD-WNG-01')->firstOrFail();
        $this->assertEquals('BBQ Chicken Wings', $product->name);
        $this->assertEquals('bbq-chicken-wings', $product->slug);
        $this->assertEquals('13.50', $product->price);
        $this->assertEquals('10.99', $product->sale_price);
        $this->assertEquals(50, $product->stock);
        $this->assertTrue($product->status);
        $this->assertTrue($product->featured);
    }

    public function test_product_store_rejects_category_mismatched_with_mode(): void
    {
        $shopyMode = Mode::where('slug', 'shopy')->firstOrFail();
        $minutesMode = Mode::where('slug', 'minutes')->firstOrFail();
        $minutesCat = Category::where('mode_id', $minutesMode->id)->firstOrFail();

        $payload = [
            'mode_id' => $shopyMode->id, // Shopy
            'category_id' => $minutesCat->id, // Belongs to Minutes, not Shopy!
            'name' => 'Mismatched Item',
            'price' => '10.00',
            'stock' => '10',
        ];

        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('admin.products.store'), $payload);

        $response->assertSessionHasErrors(['category_id']);
        $this->assertDatabaseMissing('products', ['name' => 'Mismatched Item']);
    }

    public function test_product_store_validates_pricing_rules(): void
    {
        $mode = Mode::firstOrFail();
        $category = Category::where('mode_id', $mode->id)->firstOrFail();

        // Sale price higher than regular price
        $payload = [
            'mode_id' => $mode->id,
            'category_id' => $category->id,
            'name' => 'Invalid Price Product',
            'price' => '20.00',
            'sale_price' => '25.00', // Invalid: sale_price > price
            'stock' => '5',
        ];

        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('admin.products.store'), $payload);

        $response->assertSessionHasErrors(['sale_price']);
    }

    public function test_product_store_validates_unique_slug_and_sku(): void
    {
        $mode = Mode::firstOrFail();
        $category = Category::where('mode_id', $mode->id)->firstOrFail();

        Product::create([
            'mode_id' => $mode->id,
            'category_id' => $category->id,
            'name' => 'Original Item',
            'slug' => 'original-item',
            'sku' => 'ORIG-001',
            'price' => 10.00,
            'stock' => 5,
        ]);

        $payload = [
            'mode_id' => $mode->id,
            'category_id' => $category->id,
            'name' => 'Duplicate Attempt',
            'slug' => 'original-item',
            'sku' => 'ORIG-001',
            'price' => 15.00,
            'stock' => 5,
        ];

        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('admin.products.store'), $payload);

        $response->assertSessionHasErrors(['slug', 'sku']);
    }

    public function test_admin_can_upload_product_image_to_cloudinary(): void
    {
        $mode = Mode::firstOrFail();
        $category = Category::where('mode_id', $mode->id)->firstOrFail();

        $fakeImageUrl = 'https://res.cloudinary.com/dudhfvy5f/image/upload/v1725041234/shopy_so/products/sample_product.jpg';

        Http::fake([
            'https://api.cloudinary.com/v1_1/*/image/upload' => Http::response([
                'secure_url' => $fakeImageUrl,
                'public_id' => 'shopy_so/products/sample_product',
            ], 200),
        ]);

        $file = UploadedFile::fake()->image('headset.jpg', 600, 600);

        $payload = [
            'mode_id' => $mode->id,
            'category_id' => $category->id,
            'name' => 'Wireless Gaming Headset',
            'price' => '79.99',
            'stock' => '20',
            'image' => $file,
        ];

        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('admin.products.store'), $payload);

        $response->assertRedirect(route('admin.products.index'));

        $product = Product::where('name', 'Wireless Gaming Headset')->firstOrFail();
        $this->assertEquals($fakeImageUrl, $product->image);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'image/upload')
                && str_contains($request->body(), 'shopy_so/products');
        });
    }

    public function test_admin_can_view_product_details(): void
    {
        $mode = Mode::firstOrFail();
        $category = Category::where('mode_id', $mode->id)->firstOrFail();

        $product = Product::create([
            'mode_id' => $mode->id,
            'category_id' => $category->id,
            'name' => 'Detail View Item',
            'slug' => 'detail-view-item',
            'sku' => 'DET-001',
            'price' => 88.00,
            'stock' => 12,
            'short_description' => 'Test summary',
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.products.show', $product));

        $response->assertStatus(200);
        $response->assertSee('Detail View Item');
        $response->assertSee('DET-001');
        $response->assertSee('$88.00');
    }

    public function test_admin_can_view_edit_product_form(): void
    {
        $mode = Mode::firstOrFail();
        $category = Category::where('mode_id', $mode->id)->firstOrFail();

        $product = Product::create([
            'mode_id' => $mode->id,
            'category_id' => $category->id,
            'name' => 'Editable Item',
            'slug' => 'editable-item',
            'price' => 50.00,
            'stock' => 8,
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.products.edit', $product));

        $response->assertStatus(200);
        $response->assertSee('Edit: Editable Item');
        $response->assertSee('editable-item');
    }

    public function test_admin_can_update_product(): void
    {
        $mode = Mode::firstOrFail();
        $category = Category::where('mode_id', $mode->id)->firstOrFail();

        $product = Product::create([
            'mode_id' => $mode->id,
            'category_id' => $category->id,
            'name' => 'Old Product Name',
            'slug' => 'old-product-name',
            'price' => 50.00,
            'stock' => 8,
        ]);

        $updatePayload = [
            'mode_id' => $mode->id,
            'category_id' => $category->id,
            'name' => 'Updated Product Name',
            'slug' => 'old-product-name', // keeping same slug
            'price' => '65.00',
            'sale_price' => '55.00',
            'stock' => '25',
            'status' => '1',
            'featured' => '1',
            'sort_order' => '2',
        ];

        $response = $this->actingAs($this->admin, 'admin')
            ->put(route('admin.products.update', $product), $updatePayload);

        $response->assertRedirect(route('admin.products.index'));

        $product->refresh();
        $this->assertEquals('Updated Product Name', $product->name);
        $this->assertEquals('65.00', $product->price);
        $this->assertEquals('55.00', $product->sale_price);
        $this->assertEquals(25, $product->stock);
        $this->assertTrue($product->featured);
    }

    public function test_admin_can_toggle_product_status(): void
    {
        $mode = Mode::firstOrFail();
        $category = Category::where('mode_id', $mode->id)->firstOrFail();

        $product = Product::create([
            'mode_id' => $mode->id,
            'category_id' => $category->id,
            'name' => 'Status Toggle Product',
            'slug' => 'status-toggle-product',
            'price' => 30.00,
            'stock' => 10,
            'status' => true,
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->patch(route('admin.products.toggleStatus', $product));

        $response->assertSessionHas('success');
        $product->refresh();
        $this->assertFalse($product->status);

        // Toggle back
        $this->actingAs($this->admin, 'admin')
            ->patch(route('admin.products.toggleStatus', $product));

        $product->refresh();
        $this->assertTrue($product->status);
    }

    public function test_admin_can_toggle_product_featured(): void
    {
        $mode = Mode::firstOrFail();
        $category = Category::where('mode_id', $mode->id)->firstOrFail();

        $product = Product::create([
            'mode_id' => $mode->id,
            'category_id' => $category->id,
            'name' => 'Featured Toggle Product',
            'slug' => 'featured-toggle-product',
            'price' => 30.00,
            'stock' => 10,
            'featured' => false,
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->patch(route('admin.products.toggleFeatured', $product));

        $response->assertSessionHas('success');
        $product->refresh();
        $this->assertTrue($product->featured);
    }

    public function test_admin_can_delete_product_and_cleanup_cloudinary(): void
    {
        $mode = Mode::firstOrFail();
        $category = Category::where('mode_id', $mode->id)->firstOrFail();
        $cloudinaryUrl = 'https://res.cloudinary.com/dudhfvy5f/image/upload/v1725041234/shopy_so/products/to_delete.jpg';

        $product = Product::create([
            'mode_id' => $mode->id,
            'category_id' => $category->id,
            'name' => 'Temporary Product',
            'slug' => 'temp-product-delete',
            'image' => $cloudinaryUrl,
            'price' => 19.99,
            'stock' => 5,
        ]);

        Http::fake([
            'https://api.cloudinary.com/v1_1/*/image/destroy' => Http::response([
                'result' => 'ok',
            ], 200),
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->delete(route('admin.products.destroy', $product));

        $response->assertRedirect(route('admin.products.index'));
        $this->assertDatabaseMissing('products', ['id' => $product->id]);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'image/destroy')
                && $request['public_id'] === 'shopy_so/products/to_delete';
        });
    }

    public function test_categories_by_mode_api_endpoint(): void
    {
        $minutesMode = Mode::where('slug', 'minutes')->firstOrFail();

        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.products.categoriesByMode', ['mode_id' => $minutesMode->id]));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $data = $response->json('categories');
        $this->assertNotEmpty($data);
        foreach ($data as $cat) {
            $catRecord = Category::find($cat['id']);
            $this->assertEquals($minutesMode->id, $catRecord->mode_id);
        }
    }
}
