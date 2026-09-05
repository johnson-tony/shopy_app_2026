<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Mode;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductReviewTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $otherUser;
    protected Mode $shopyMode;
    protected Category $category;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->shopyMode = Mode::firstOrCreate(
            ['slug' => 'shopy'],
            ['name' => 'Shopy', 'status' => true, 'icon' => 'fa-solid fa-bag-shopping']
        );

        $this->category = Category::create([
            'mode_id'    => $this->shopyMode->id,
            'name'       => 'Fashion',
            'slug'       => 'fashion',
            'status'     => true,
            'sort_order' => 1,
        ]);

        $this->product = Product::create([
            'mode_id'     => $this->shopyMode->id,
            'category_id' => $this->category->id,
            'name'        => 'Classic Denim Jacket',
            'slug'        => 'classic-denim-jacket',
            'sku'         => 'CDJ-01',
            'price'       => 2499.00,
            'sale_price'  => 1999.00,
            'stock'       => 15,
            'status'      => true,
        ]);

        $this->user = User::create([
            'name'              => 'Peter Parker',
            'email'             => 'peter@spidey.com',
            'password'          => bcrypt('password'),
            'status'            => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);

        $this->otherUser = User::create([
            'name'              => 'Ned Leeds',
            'email'             => 'ned@guyinachair.com',
            'password'          => bcrypt('password'),
            'status'            => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);
    }

    public function test_guest_cannot_submit_review(): void
    {
        $response = $this->post(route('reviews.store'), [
            'product_id' => $this->product->id,
            'rating'     => 5,
            'comment'    => 'Great jacket, love the fabric!',
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_user_who_purchased_product_gets_verified_buyer_badge(): void
    {
        // Create confirmed order containing this product for $this->user
        $order = Order::create([
            'order_number'          => Order::generateOrderNumber(),
            'user_id'               => $this->user->id,
            'mode_id'               => $this->shopyMode->id,
            'shipping_name'         => $this->user->name,
            'shipping_phone'        => '9876543210',
            'shipping_address_line1'=> '20 Ingram Street, Forest Hills',
            'shipping_city'         => 'New York',
            'shipping_state'        => 'NY',
            'shipping_postal_code'  => '11375',
            'status'                => Order::STATUS_DELIVERED,
            'payment_method'        => 'pay_on_delivery',
            'payment_status'        => 'paid',
            'subtotal'              => 1999.00,
            'grand_total'           => 1999.00,
        ]);

        OrderItem::create([
            'order_id'     => $order->id,
            'product_id'   => $this->product->id,
            'product_name' => $this->product->name,
            'product_slug' => $this->product->slug,
            'quantity'     => 1,
            'unit_price'   => 1999.00,
            'subtotal'     => 1999.00,
        ]);

        // Submit review
        $response = $this->actingAs($this->user)->post(route('reviews.store'), [
            'product_id' => $this->product->id,
            'order_id'   => $order->id,
            'rating'     => 5,
            'title'      => 'Outstanding quality!',
            'comment'    => 'Fits true to size and the denim is heavy and durable.',
        ]);

        $response->assertSessionHas('success');

        $review = ProductReview::where('product_id', $this->product->id)->where('user_id', $this->user->id)->first();
        $this->assertNotNull($review);
        $this->assertEquals(5, $review->rating);
        $this->assertEquals('Outstanding quality!', $review->title);
        $this->assertTrue($review->is_verified_buyer);
        $this->assertEquals($order->id, $review->order_id);
    }

    public function test_user_who_did_not_purchase_does_not_get_verified_buyer_badge(): void
    {
        $response = $this->actingAs($this->otherUser)->post(route('reviews.store'), [
            'product_id' => $this->product->id,
            'rating'     => 4,
            'title'      => 'Good look',
            'comment'    => 'Looks nice from friends recommendation.',
        ]);

        $response->assertSessionHas('success');

        $review = ProductReview::where('product_id', $this->product->id)->where('user_id', $this->otherUser->id)->first();
        $this->assertNotNull($review);
        $this->assertEquals(4, $review->rating);
        $this->assertFalse($review->is_verified_buyer);
    }

    public function test_user_can_upload_multiple_photos_with_review(): void
    {
        Storage::fake('public');

        $file1 = UploadedFile::fake()->image('jacket_front.jpg', 600, 600);
        $file2 = UploadedFile::fake()->image('jacket_back.jpg', 600, 600);

        $response = $this->actingAs($this->user)->post(route('reviews.store'), [
            'product_id' => $this->product->id,
            'rating'     => 5,
            'title'      => 'With photos',
            'comment'    => 'Attaching photos so people can see the real texture and color.',
            'images'     => [$file1, $file2],
        ]);

        $response->assertSessionHas('success');

        $review = ProductReview::where('product_id', $this->product->id)->where('user_id', $this->user->id)->first();
        $this->assertNotNull($review);
        $this->assertIsArray($review->images);
        $this->assertCount(2, $review->images);

        // Check storage has saved files
        foreach ($review->images as $path) {
            Storage::disk('public')->assertExists($path);
        }
    }

    public function test_product_detail_page_displays_live_ratings_and_breakdown(): void
    {
        ProductReview::create([
            'product_id'        => $this->product->id,
            'user_id'           => $this->user->id,
            'rating'            => 5,
            'title'             => 'Best Jacket Ever',
            'comment'           => 'Amazing fit, great stitching, high quality denim.',
            'is_verified_buyer' => true,
            'status'            => true,
        ]);

        ProductReview::create([
            'product_id'        => $this->product->id,
            'user_id'           => $this->otherUser->id,
            'rating'            => 4,
            'title'             => 'Pretty Solid',
            'comment'           => 'Good fit, would buy again in another color.',
            'is_verified_buyer' => false,
            'status'            => true,
        ]);

        $response = $this->get(route('product.show', $this->product->slug));
        $response->assertOk();
        $response->assertSee('4.5'); // Average of 5 and 4 = 4.5
        $response->assertSee('Ratings &amp; Customer Reviews', false);
        $response->assertSee('Best Jacket Ever');
        $response->assertSee('Certified Buyer');
    }
}
