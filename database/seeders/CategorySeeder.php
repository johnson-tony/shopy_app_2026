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
            [
                'name' => 'Pizza & Fast Food',
                'slug' => 'pizza-fast-food',
                'description' => 'Cheesy pizzas, gourmet burgers, sides, and snacks.',
                'icon' => 'fas fa-pizza-slice',
                'sort_order' => 11,
                'status' => true,
                'is_featured' => true,
                'children' => [
                    ['name' => 'Pizzas', 'slug' => 'food-pizzas', 'icon' => 'fas fa-pizza-slice', 'sort_order' => 1, 'status' => true, 'is_featured' => true],
                    ['name' => 'Burgers', 'slug' => 'food-burgers', 'icon' => 'fas fa-burger', 'sort_order' => 2, 'status' => true, 'is_featured' => true],
                ],
            ],
            [
                'name' => 'Biryani & Rice Bowls',
                'slug' => 'biryani-rice-bowls',
                'description' => 'Authentic dum biryani, fried rice, and hearty combo meals.',
                'icon' => 'fas fa-bowl-rice',
                'sort_order' => 12,
                'status' => true,
                'is_featured' => true,
                'children' => [
                    ['name' => 'Biryani', 'slug' => 'food-biryani', 'icon' => 'fas fa-bowl-rice', 'sort_order' => 1, 'status' => true, 'is_featured' => true],
                ],
            ],
        ];

        $shopyMode = Mode::where('slug', 'shopy')->first();
        $minutesMode = Mode::where('slug', 'minutes')->first();
        $foodMode = Mode::where('slug', 'food')->first();

        $categoryImages = [
            'fashion'                    => 'https://images.unsplash.com/photo-1445205170230-053b83016050?auto=format&fit=crop&w=400&q=80',
            'fashion-men'                => 'https://images.unsplash.com/photo-1617137984095-74e4e5e3613f?auto=format&fit=crop&w=400&q=80',
            'fashion-women'              => 'https://images.unsplash.com/photo-1483985988355-763728e1935b?auto=format&fit=crop&w=400&q=80',
            'fashion-kids'               => 'https://images.unsplash.com/photo-1503454537195-1dcabb73ffb9?auto=format&fit=crop&w=400&q=80',
            'fashion-footwear'           => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=400&q=80',
            'electronics'                => 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?auto=format&fit=crop&w=400&q=80',
            'electronics-laptops'        => 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?auto=format&fit=crop&w=400&q=80',
            'electronics-tvs'            => 'https://images.unsplash.com/photo-1593359677879-a4bb92f829d1?auto=format&fit=crop&w=400&q=80',
            'electronics-headphones'     => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&fit=crop&w=400&q=80',
            'electronics-cameras'        => 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?auto=format&fit=crop&w=400&q=80',
            'mobiles'                    => 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?auto=format&fit=crop&w=400&q=80',
            'mobiles-smartphones'        => 'https://images.unsplash.com/photo-1592750475338-74b7b21085ab?auto=format&fit=crop&w=400&q=80',
            'mobiles-tablets'            => 'https://images.unsplash.com/photo-1544244015-0df4b3ffc6b0?auto=format&fit=crop&w=400&q=80',
            'mobiles-accessories'        => 'https://images.unsplash.com/photo-1583394838336-acd977736f90?auto=format&fit=crop&w=400&q=80',
            'home-kitchen'               => 'https://images.unsplash.com/photo-1556911220-e15b29be8c8f?auto=format&fit=crop&w=400&q=80',
            'beauty-personal-care'       => 'https://images.unsplash.com/photo-1522337360788-8b13dee7a37e?auto=format&fit=crop&w=400&q=80',
            'sports-fitness'             => 'https://images.unsplash.com/photo-1517838277536-f5f99be501cd?auto=format&fit=crop&w=400&q=80',
            'books-media'                => 'https://images.unsplash.com/photo-1512820790803-83ca734da794?auto=format&fit=crop&w=400&q=80',
            'toys-baby'                  => 'https://images.unsplash.com/photo-1566576912321-d58ddd7a6088?auto=format&fit=crop&w=400&q=80',
            'grocery'                    => 'https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&w=400&q=80',
            'grocery-fruits-vegetables'  => 'https://images.unsplash.com/photo-1610832958506-aa56368176cf?auto=format&fit=crop&w=400&q=80',
            'grocery-dairy'              => 'https://images.unsplash.com/photo-1550583724-b2692b85b150?auto=format&fit=crop&w=400&q=80',
            'grocery-snacks'             => 'https://images.unsplash.com/photo-1566478989037-eec170784d0b?auto=format&fit=crop&w=400&q=80',
            'grocery-cold-drinks'        => 'https://images.unsplash.com/photo-1527661591475-527312dd65f5?auto=format&fit=crop&w=400&q=80',
            'pizza-fast-food'            => 'https://images.unsplash.com/photo-1513104890138-7c749659a591?auto=format&fit=crop&w=400&q=80',
            'biryani-rice-bowls'         => 'https://images.unsplash.com/photo-1563379091339-03b21ab4a4f8?auto=format&fit=crop&w=400&q=80',
            'food-biryani'               => 'https://images.unsplash.com/photo-1563379091339-03b21ab4a4f8?auto=format&fit=crop&w=400&q=80',
            'food-pizzas'                => 'https://images.unsplash.com/photo-1513104890138-7c749659a591?auto=format&fit=crop&w=400&q=80',
            'food-starters'              => 'https://images.unsplash.com/photo-1626777552726-4a6b54c97e46?auto=format&fit=crop&w=400&q=80',
        ];

        foreach ($catalog as $rootData) {
            $children = $rootData['children'] ?? [];
            unset($rootData['children']);

            if ($rootData['slug'] === 'grocery') {
                $modeId = $minutesMode?->id ?? $shopyMode?->id;
            } elseif (in_array($rootData['slug'], ['pizza-fast-food', 'biryani-rice-bowls'], true)) {
                $modeId = $foodMode?->id ?? $shopyMode?->id;
            } else {
                $modeId = $shopyMode?->id;
            }

            $rootData['mode_id'] = $modeId;
            $rootData['image'] = $categoryImages[$rootData['slug']] ?? null;

            $root = Category::updateOrCreate(
                ['slug' => $rootData['slug']],
                $rootData
            );

            foreach ($children as $childData) {
                $childData['parent_id'] = $root->id;
                $childData['mode_id'] = $modeId;
                $childData['image'] = $categoryImages[$childData['slug']] ?? $rootData['image'];

                Category::updateOrCreate(
                    ['slug' => $childData['slug']],
                    $childData
                );
            }
        }
    }
}
