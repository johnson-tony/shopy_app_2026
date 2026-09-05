<?php

namespace Database\Seeders;

use App\Models\Coupon;
use App\Models\Mode;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $minutesMode = Mode::where('slug', 'minutes')->first();
        $shopyMode = Mode::where('slug', 'shopy')->first();

        $coupons = [
            [
                'code'                => 'WELCOME50',
                'name'                => 'Flat ₹50 Off Welcome Discount',
                'description'         => 'Enjoy ₹50 flat savings on your order. Valid on minimum cart of ₹199.',
                'type'                => Coupon::TYPE_FIXED,
                'value'               => 50.00,
                'min_order_amount'    => 199.00,
                'max_discount_amount' => null,
                'mode_id'             => null, // All modes
                'usage_limit'         => 1000,
                'usage_limit_per_user'=> 2,
                'starts_at'           => now()->subDay(),
                'expires_at'          => now()->addMonths(3),
                'status'              => true,
            ],
            [
                'code'                => 'SAVE10',
                'name'                => '10% Off Super Savings',
                'description'         => 'Get 10% instant discount up to ₹200 on shopping orders above ₹499.',
                'type'                => Coupon::TYPE_PERCENTAGE,
                'value'               => 10.00,
                'min_order_amount'    => 499.00,
                'max_discount_amount' => 200.00,
                'mode_id'             => $shopyMode?->id, // Shopy
                'usage_limit'         => 500,
                'usage_limit_per_user'=> 3,
                'starts_at'           => now()->subDay(),
                'expires_at'          => now()->addMonths(6),
                'status'              => true,
            ],
            [
                'code'                => 'MINUTES20',
                'name'                => '20% Off 10-Minute Groceries',
                'description'         => 'Get 20% discount on quick-commerce essentials delivered in 10 minutes. Max ₹80 off.',
                'type'                => Coupon::TYPE_PERCENTAGE,
                'value'               => 20.00,
                'min_order_amount'    => 149.00,
                'max_discount_amount' => 80.00,
                'mode_id'             => $minutesMode?->id, // Minutes only
                'usage_limit'         => 2000,
                'usage_limit_per_user'=> 5,
                'starts_at'           => now()->subDay(),
                'expires_at'          => now()->addMonths(2),
                'status'              => true,
            ],
            [
                'code'                => 'FREESHIP',
                'name'                => 'Zero Delivery Fee',
                'description'         => '100% off delivery fee on your cart when subtotal is ₹150 or more.',
                'type'                => Coupon::TYPE_FREE_DELIVERY,
                'value'               => 0.00,
                'min_order_amount'    => 150.00,
                'max_discount_amount' => null,
                'mode_id'             => null, // All modes
                'usage_limit'         => 1000,
                'usage_limit_per_user'=> 1,
                'starts_at'           => now()->subDay(),
                'expires_at'          => now()->addMonths(3),
                'status'              => true,
            ],
        ];

        foreach ($coupons as $coupon) {
            Coupon::updateOrCreate(['code' => $coupon['code']], $coupon);
        }
    }
}
