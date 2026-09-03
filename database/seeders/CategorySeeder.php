<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Mode;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $catalog = [
            [
                'name' => 'Fashion',
                'slug' => 'fashion',
                'description' => 'Trending apparel, clothing, footwear, and accessories.',
                'icon' => 'fas fa-shirt',
                'sort_order' => 1,
                'status' => true,
                'is_featured' => true,
                'children' => [
                    ['name' => 'Men', 'slug' => 'fashion-men', 'icon' => 'fas fa-user-tie', 'sort_order' => 1, 'status' => true, 'is_featured' => true],
                    ['name' => 'Women', 'slug' => 'fashion-women', 'icon' => 'fas fa-person-dress', 'sort_order' => 2, 'status' => true, 'is_featured' => true],
                    ['name' => 'Kids', 'slug' => 'fashion-kids', 'icon' => 'fas fa-child', 'sort_order' => 3, 'status' => true, 'is_featured' => false],
                    ['name' => 'Footwear', 'slug' => 'fashion-footwear', 'icon' => 'fas fa-shoe-prints', 'sort_order' => 4, 'status' => true, 'is_featured' => false],
                ],
            ],
            [
                'name' => 'Electronics',
                'slug' => 'electronics',
                'description' => 'Laptops, computers, TVs, cameras, audio, and home tech.',
                'icon' => 'fas fa-laptop',
                'sort_order' => 2,
                'status' => true,
                'is_featured' => true,
                'children' => [
                    ['name' => 'Laptops', 'slug' => 'electronics-laptops', 'icon' => 'fas fa-laptop-code', 'sort_order' => 1, 'status' => true, 'is_featured' => true],
                    ['name' => 'TVs', 'slug' => 'electronics-tvs', 'icon' => 'fas fa-tv', 'sort_order' => 2, 'status' => true, 'is_featured' => false],
                    ['name' => 'Headphones', 'slug' => 'electronics-headphones', 'icon' => 'fas fa-headphones', 'sort_order' => 3, 'status' => true, 'is_featured' => true],
                    ['name' => 'Cameras', 'slug' => 'electronics-cameras', 'icon' => 'fas fa-camera', 'sort_order' => 4, 'status' => true, 'is_featured' => false],
                ],
            ],
            [
                'name' => 'Mobiles',
                'slug' => 'mobiles',
                'description' => 'Smartphones, feature phones, tablets, cases, and charging gear.',
                'icon' => 'fas fa-mobile-screen-button',
                'sort_order' => 3,
                'status' => true,
                'is_featured' => true,
                'children' => [
                    ['name' => 'Smartphones', 'slug' => 'mobiles-smartphones', 'icon' => 'fas fa-mobile-screen', 'sort_order' => 1, 'status' => true, 'is_featured' => true],
                    ['name' => 'Tablets', 'slug' => 'mobiles-tablets', 'icon' => 'fas fa-tablet-screen-button', 'sort_order' => 2, 'status' => true, 'is_featured' => false],
                    ['name' => 'Accessories', 'slug' => 'mobiles-accessories', 'icon' => 'fas fa-charging-station', 'sort_order' => 3, 'status' => true, 'is_featured' => false],
                ],
            ],
            [
                'name' => 'Home & Kitchen',
                'slug' => 'home-kitchen',
                'description' => 'Kitchen appliances, cookware, furniture, home decor, and lighting.',
                'icon' => 'fas fa-couch',
                'sort_order' => 4,
                'status' => true,
                'is_featured' => true,
                'children' => [
                    ['name' => 'Kitchen Appliances', 'slug' => 'kitchen-appliances', 'icon' => 'fas fa-blender', 'sort_order' => 1, 'status' => true, 'is_featured' => false],
                    ['name' => 'Cookware & Dining', 'slug' => 'cookware-dining', 'icon' => 'fas fa-utensils', 'sort_order' => 2, 'status' => true, 'is_featured' => false],
                    ['name' => 'Home Decor', 'slug' => 'home-decor', 'icon' => 'fas fa-lightbulb', 'sort_order' => 3, 'status' => true, 'is_featured' => false],
                ],
            ],
            [
                'name' => 'Beauty & Personal Care',
                'slug' => 'beauty-personal-care',
                'description' => 'Skincare, makeup, hair care, grooming, and luxury perfumes.',
                'icon' => 'fas fa-spa',
                'sort_order' => 5,
                'status' => true,
                'is_featured' => false,
                'children' => [
                    ['name' => 'Skincare', 'slug' => 'beauty-skincare', 'icon' => 'fas fa-pump-soap', 'sort_order' => 1, 'status' => true, 'is_featured' => false],
                    ['name' => 'Fragrances', 'slug' => 'beauty-fragrances', 'icon' => 'fas fa-spray-can', 'sort_order' => 2, 'status' => true, 'is_featured' => false],
                ],
            ],
            [
                'name' => 'Grocery',
                'slug' => 'grocery',
                'description' => 'Daily staples, fruits, fresh vegetables, snacks, and beverages.',
                'icon' => 'fas fa-basket-shopping',
                'sort_order' => 6,
                'status' => true,
                'is_featured' => true,
                'children' => [
                    ['name' => 'Fruits & Vegetables', 'slug' => 'grocery-fruits-vegetables', 'icon' => 'fas fa-apple-whole', 'sort_order' => 1, 'status' => true, 'is_featured' => true],
                    ['name' => 'Snacks', 'slug' => 'grocery-snacks', 'icon' => 'fas fa-cookie', 'sort_order' => 2, 'status' => true, 'is_featured' => false],
                    ['name' => 'Beverages', 'slug' => 'grocery-beverages', 'icon' => 'fas fa-mug-hot', 'sort_order' => 3, 'status' => true, 'is_featured' => false],
                    ['name' => 'Dairy & Breakfast', 'slug' => 'grocery-dairy', 'icon' => 'fas fa-cheese', 'sort_order' => 4, 'status' => true, 'is_featured' => false],
                ],
            ],
            [
                'name' => 'Sports',
                'slug' => 'sports',
                'description' => 'Sporting equipment, fitness gym gear, and outdoor adventures.',
                'icon' => 'fas fa-dumbbell',
                'sort_order' => 7,
                'status' => true,
                'is_featured' => false,
            ],
            [
                'name' => 'Books',
                'slug' => 'books',
                'description' => 'Bestsellers, fiction, non-fiction, academic, and kids books.',
                'icon' => 'fas fa-book',
                'sort_order' => 8,
                'status' => true,
                'is_featured' => false,
            ],
            [
                'name' => 'Toys & Baby',
                'slug' => 'toys-baby',
                'description' => 'Baby care products, toys, games, and nursery essentials.',
                'icon' => 'fas fa-baby',
                'sort_order' => 9,
                'status' => true,
                'is_featured' => false,
            ],
            [
                'name' => 'Automotive',
                'slug' => 'automotive',
                'description' => 'Car & bike accessories, vehicle electronics, and maintenance tools.',
                'icon' => 'fas fa-car',
                'sort_order' => 10,
                'status' => true,
                'is_featured' => false,
            ],
        ];

        $shopyMode = Mode::where('slug', 'shopy')->first();
        $minutesMode = Mode::where('slug', 'minutes')->first();

        foreach ($catalog as $rootData) {
            $children = $rootData['children'] ?? [];
            unset($rootData['children']);

            $modeId = ($rootData['slug'] === 'grocery')
                ? ($minutesMode?->id ?? $shopyMode?->id)
                : ($shopyMode?->id);

            $rootData['mode_id'] = $modeId;

            $root = Category::updateOrCreate(
                ['slug' => $rootData['slug']],
                $rootData
            );

            foreach ($children as $childData) {
                $childData['parent_id'] = $root->id;
                $childData['mode_id'] = $modeId;
                Category::updateOrCreate(
                    ['slug' => $childData['slug']],
                    $childData
                );
            }
        }
    }
}
