<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderAndReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::where('email', 'customer@shopy.test')->first() ?? User::first();
        if (!$user) {
            return;
        }

        $products = Product::where('status', true)->take(4)->get();
        if ($products->isEmpty()) {
            return;
        }

        $firstProduct = $products->first();

        // 1. Create a sample delivered order
        $order = Order::firstOrCreate(
            ['order_number' => 'SHP-202609-DEMO01'],
            [
                'user_id'                => $user->id,
                'mode_id'                => $firstProduct->mode_id,
                'shipping_name'          => $user->name,
                'shipping_phone'         => $user->phone ?? '+91 98765 43210',
                'shipping_address_line1' => 'Flat 402, Sunshine Heights',
                'shipping_address_line2' => 'MG Road, Indiranagar',
                'shipping_landmark'      => 'Near Metro Station',
                'shipping_city'          => 'Bengaluru',
                'shipping_state'         => 'Karnataka',
                'shipping_postal_code'   => '560038',
                'shipping_country'       => 'India',
                'shipping_address_type'  => 'home',
                'status'                 => Order::STATUS_DELIVERED,
                'payment_method'         => Order::PAYMENT_METHOD_COD,
                'payment_status'         => Order::PAYMENT_STATUS_PAID,
                'subtotal'               => $firstProduct->price,
                'delivery_fee'           => 0.00,
                'tax_amount'             => round($firstProduct->price * 0.05, 2),
                'discount_amount'        => 0.00,
                'grand_total'            => round($firstProduct->price * 1.05, 2),
                'delivered_at'           => now()->subDays(2),
            ]
        );

        OrderItem::firstOrCreate(
            [
                'order_id'   => $order->id,
                'product_id' => $firstProduct->id,
            ],
            [
                'product_name'  => $firstProduct->name,
                'product_slug'  => $firstProduct->slug,
                'product_image' => $firstProduct->image,
                'color'         => $firstProduct->colorOptions()[0]['name'] ?? null,
                'size'          => $firstProduct->sizeOptions()[0] ?? null,
                'quantity'      => 1,
                'unit_price'    => $firstProduct->price,
                'subtotal'      => $firstProduct->price,
            ]
        );

        // 2. Create rich sample reviews with verified badge and customer photos
        ProductReview::updateOrCreate(
            [
                'user_id'    => $user->id,
                'product_id' => $firstProduct->id,
            ],
            [
                'order_id'          => $order->id,
                'rating'            => 5,
                'title'             => 'Exceptional quality, looks exactly like the photos!',
                'comment'           => 'I was pleasantly surprised by the build quality and packaging. Arrived well within the delivery time frame. The fit and finish are top-notch. Highly recommended to anyone looking for premium durability.',
                'images'            => [
                    'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=400&q=80',
                    'https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=400&q=80',
                ],
                'is_verified_buyer' => true,
                'status'            => true,
                'helpful_count'     => 14,
            ]
        );
    }
}
