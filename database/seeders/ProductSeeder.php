<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Mode;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $shopyMode = Mode::where('slug', 'shopy')->first();
        $foodMode = Mode::where('slug', 'food')->first();
        $minutesMode = Mode::where('slug', 'minutes')->first();

        // Shopy Categories
        $smartphones = Category::where('slug', 'mobiles-smartphones')->first()
            ?? Category::where('slug', 'mobiles')->first();
        $laptops = Category::where('slug', 'electronics-laptops')->first()
            ?? Category::where('slug', 'electronics')->first();
        $mensFashion = Category::where('slug', 'fashion-men')->first()
            ?? Category::where('slug', 'fashion')->first();
        $headphones = Category::where('slug', 'electronics-headphones')->first()
            ?? Category::where('slug', 'electronics')->first();

        // Food Categories
        $pizzas = Category::where('slug', 'food-pizzas')->first()
            ?? Category::where('slug', 'pizza-fast-food')->first();
        $burgers = Category::where('slug', 'food-burgers')->first()
            ?? Category::where('slug', 'pizza-fast-food')->first();
        $biryani = Category::where('slug', 'food-biryani')->first()
            ?? Category::where('slug', 'biryani-rice-bowls')->first();

        // Minutes Categories
        $dairy = Category::where('slug', 'grocery-dairy')->first()
            ?? Category::where('slug', 'grocery')->first();
        $fruits = Category::where('slug', 'grocery-fruits-vegetables')->first()
            ?? Category::where('slug', 'grocery')->first();
        $snacks = Category::where('slug', 'grocery-snacks')->first()
            ?? Category::where('slug', 'grocery')->first();

        $products = [
            // Shopy Products
            [
                'mode_id' => $shopyMode?->id,
                'category_id' => $smartphones?->id,
                'name' => 'Apple iPhone 16 Pro Max 256GB',
                'slug' => 'apple-iphone-16-pro-max-256gb',
                'sku' => 'SHP-MOB-001',
                'short_description' => 'Flagship smartphone with titanium design and A18 Pro chip.',
                'description' => 'The iPhone 16 Pro Max features a strong and light titanium design, Action button, 48MP Fusion camera system, and industry-leading battery life.',
                'price' => 1199.00,
                'sale_price' => 1099.00,
                'stock' => 45,
                'images' => [
                    'https://images.unsplash.com/photo-1592750475338-74b7b21085ab?auto=format&fit=crop&w=600&q=80',
                    'https://images.unsplash.com/photo-1510557880182-3d4d3cba35a5?auto=format&fit=crop&w=600&q=80',
                    'https://images.unsplash.com/photo-1511707171634-5f897ff025a5?auto=format&fit=crop&w=600&q=80',
                ],
                'colors' => [
                    ['name' => 'Desert Titanium', 'hex' => '#c2a382'],
                    ['name' => 'Natural Titanium', 'hex' => '#9e9e9e'],
                    ['name' => 'White Titanium', 'hex' => '#f5f5f7'],
                    ['name' => 'Black Titanium', 'hex' => '#212121'],
                ],
                'sizes' => ['128GB', '256GB', '512GB', '1TB'],
                'status' => true,
                'featured' => true,
                'sort_order' => 1,
            ],
            [
                'mode_id' => $shopyMode?->id,
                'category_id' => $laptops?->id,
                'name' => 'Apple MacBook Pro 16 M3 Max',
                'slug' => 'apple-macbook-pro-16-m3-max',
                'sku' => 'SHP-ELE-002',
                'short_description' => 'Ultimate workstation laptop for pro developers and creators.',
                'description' => '16-inch Liquid Retina XDR display, M3 Max chip with 16-core CPU and 40-core GPU, 48GB unified memory, and up to 22 hours of battery life.',
                'price' => 2499.00,
                'sale_price' => null,
                'stock' => 18,
                'images' => [
                    'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?auto=format&fit=crop&w=600&q=80',
                    'https://images.unsplash.com/photo-1611186871348-b1ce696e52c9?auto=format&fit=crop&w=600&q=80',
                    'https://images.unsplash.com/photo-1541807084-5c52b6b3adef?auto=format&fit=crop&w=600&q=80',
                ],
                'colors' => [
                    ['name' => 'Space Black', 'hex' => '#1d1d1f'],
                    ['name' => 'Silver', 'hex' => '#e3e4e5'],
                ],
                'sizes' => ['512GB SSD', '1TB SSD', '2TB SSD'],
                'status' => true,
                'featured' => true,
                'sort_order' => 2,
            ],
            [
                'mode_id' => $shopyMode?->id,
                'category_id' => $mensFashion?->id,
                'name' => 'Men Slim Fit Oxford Cotton Shirt',
                'slug' => 'men-slim-fit-oxford-cotton-shirt',
                'sku' => 'SHP-FAS-003',
                'short_description' => '100% breathable combed cotton formal & casual shirt.',
                'description' => 'Tailored slim fit crafted from premium Oxford weave cotton. Features button-down collar, single chest pocket, and mother-of-pearl buttons.',
                'price' => 49.99,
                'sale_price' => 34.99,
                'stock' => 120,
                'images' => [
                    'https://images.unsplash.com/photo-1596755094514-f87e34085b2c?auto=format&fit=crop&w=600&q=80',
                    'https://images.unsplash.com/photo-1602810318383-e386cc2a3ccf?auto=format&fit=crop&w=600&q=80',
                ],
                'colors' => [
                    ['name' => 'Sky Blue', 'hex' => '#38bdf8'],
                    ['name' => 'Navy Blue', 'hex' => '#1e3a8a'],
                    ['name' => 'White', 'hex' => '#ffffff'],
                    ['name' => 'Pastel Pink', 'hex' => '#f472b6'],
                ],
                'sizes' => ['S', 'M', 'L', 'XL', 'XXL'],
                'status' => true,
                'featured' => false,
                'sort_order' => 3,
            ],
            [
                'mode_id' => $shopyMode?->id,
                'category_id' => $headphones?->id,
                'name' => 'Sony WH-1000XM5 Wireless Headphones',
                'slug' => 'sony-wh-1000xm5-wireless-headphones',
                'sku' => 'SHP-ELE-004',
                'short_description' => 'Industry-leading noise canceling over-ear headphones.',
                'description' => 'Two processors control 8 microphones for unprecedented noise cancellation. With Auto NC Optimizer, noise canceling is automatically optimized based on your wearing conditions.',
                'price' => 399.00,
                'sale_price' => 329.00,
                'stock' => 35,
                'images' => [
                    'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&fit=crop&w=600&q=80',
                    'https://images.unsplash.com/photo-1583394838336-acd977736f90?auto=format&fit=crop&w=600&q=80',
                ],
                'colors' => [
                    ['name' => 'Midnight Black', 'hex' => '#111827'],
                    ['name' => 'Platinum Silver', 'hex' => '#cbd5e1'],
                    ['name' => 'Midnight Blue', 'hex' => '#1e3a8a'],
                ],
                'status' => true,
                'featured' => true,
                'sort_order' => 4,
            ],

            // Food Products
            [
                'mode_id' => $foodMode?->id,
                'category_id' => $pizzas?->id,
                'name' => 'Classic Margherita Cheese Burst Pizza',
                'slug' => 'classic-margherita-cheese-burst-pizza',
                'sku' => 'FOD-PIZ-001',
                'short_description' => 'Hand-tossed crust filled with liquid mozzarella and fresh basil.',
                'description' => 'Authentic Italian pizza sauce made from San Marzano tomatoes, topped with fresh mozzarella, basil leaves, extra virgin olive oil, and parmesan cheese.',
                'price' => 14.99,
                'sale_price' => 11.99,
                'stock' => 85,
                'images' => [
                    'https://images.unsplash.com/photo-1604382354936-07c5d9983bd3?auto=format&fit=crop&w=600&q=80',
                    'https://images.unsplash.com/photo-1513104890138-7c749659a591?auto=format&fit=crop&w=600&q=80',
                ],
                'sizes' => ['Regular 7"', 'Medium 10"', 'Large 12"'],
                'status' => true,
                'featured' => true,
                'sort_order' => 1,
            ],
            [
                'mode_id' => $foodMode?->id,
                'category_id' => $burgers?->id,
                'name' => 'Gourmet Double Smash Cheeseburger',
                'slug' => 'gourmet-double-smash-cheeseburger',
                'sku' => 'FOD-BGR-002',
                'short_description' => 'Two crispy-edged beef patties with melted cheddar in brioche.',
                'description' => 'Double smashed seasoned beef patties, double aged cheddar, caramelized onions, house pickles, and secret burger sauce on a toasted buttered brioche bun.',
                'price' => 12.50,
                'sale_price' => 9.99,
                'stock' => 60,
                'images' => [
                    'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?auto=format&fit=crop&w=600&q=80',
                    'https://images.unsplash.com/photo-1550547660-d9450f859349?auto=format&fit=crop&w=600&q=80',
                ],
                'sizes' => ['Single Patty', 'Double Patty'],
                'status' => true,
                'featured' => true,
                'sort_order' => 2,
            ],
            [
                'mode_id' => $foodMode?->id,
                'category_id' => $biryani?->id,
                'name' => 'Royal Hyderabadi Dum Chicken Biryani',
                'slug' => 'royal-hyderabadi-dum-chicken-biryani',
                'sku' => 'FOD-BIR-003',
                'short_description' => 'Slow-cooked aromatic basmati rice with marinated chicken and saffron.',
                'description' => 'Prepared in the traditional clay pot dum style with long-grain basmati rice, tender farm chicken marinated overnight in yogurt and secret Nizami spices, served with Mirchi ka Salan and Raita.',
                'price' => 18.00,
                'sale_price' => 15.00,
                'stock' => 40,
                'images' => [
                    'https://images.unsplash.com/photo-1563379091339-03b21ab4a4f8?auto=format&fit=crop&w=600&q=80',
                    'https://images.unsplash.com/photo-1633945274405-b6c8069047b0?auto=format&fit=crop&w=600&q=80',
                ],
                'sizes' => ['Regular (Serves 1)', 'Family Pack (Serves 3)', 'Jumbo Handi (Serves 5)'],
                'status' => true,
                'featured' => true,
                'sort_order' => 3,
            ],

            // Minutes (Quick Commerce) Products
            [
                'mode_id' => $minutesMode?->id,
                'category_id' => $dairy?->id,
                'name' => 'Amul Taaza Homogenised Toned Milk 1L',
                'slug' => 'amul-taaza-homogenised-toned-milk-1l',
                'sku' => 'MIN-DAR-001',
                'short_description' => 'Fresh pasteurized toned milk in sterile tetra pack.',
                'description' => 'Fortified with Vitamin A and D. Excellent source of natural calcium and protein for the entire family. 100% pure vegetarian.',
                'price' => 2.20,
                'sale_price' => 1.99,
                'stock' => 450,
                'images' => [
                    'https://images.unsplash.com/photo-1550583724-b2692b85b150?auto=format&fit=crop&w=600&q=80',
                ],
                'status' => true,
                'featured' => true,
                'sort_order' => 1,
            ],
            [
                'mode_id' => $minutesMode?->id,
                'category_id' => $dairy?->id,
                'name' => 'Farm Fresh Brown Eggs (Pack of 6)',
                'slug' => 'farm-fresh-brown-eggs-pack-of-6',
                'sku' => 'MIN-DAR-002',
                'short_description' => 'Rich in protein, omega-3 brown eggs from free-range hens.',
                'description' => 'Antibiotic-free and hygienically packed brown eggs. Perfect for a nutritious breakfast, baking, or high-protein meals.',
                'price' => 3.50,
                'sale_price' => null,
                'stock' => 280,
                'images' => [
                    'https://images.unsplash.com/photo-1582722872445-44dc5f7e3c8f?auto=format&fit=crop&w=600&q=80',
                ],
                'status' => true,
                'featured' => false,
                'sort_order' => 2,
            ],
            [
                'mode_id' => $minutesMode?->id,
                'category_id' => $fruits?->id,
                'name' => 'Fresh Organic Cavendish Bananas 1kg',
                'slug' => 'fresh-organic-cavendish-bananas-1kg',
                'sku' => 'MIN-FRU-003',
                'short_description' => 'Naturally ripened, sweet and energy-rich bananas.',
                'description' => 'Farm-picked Cavendish bananas free from artificial ripening chemicals. Rich in potassium, dietary fiber, and essential minerals.',
                'price' => 1.80,
                'sale_price' => 1.49,
                'stock' => 160,
                'images' => [
                    'https://images.unsplash.com/photo-1571771894821-ce9b6c11b08e?auto=format&fit=crop&w=600&q=80',
                ],
                'status' => true,
                'featured' => true,
                'sort_order' => 3,
            ],
            [
                'mode_id' => $minutesMode?->id,
                'category_id' => $snacks?->id,
                'name' => 'Lays Classic Salted Potato Chips 150g',
                'slug' => 'lays-classic-salted-potato-chips-150g',
                'sku' => 'MIN-SNK-004',
                'short_description' => 'Crispy golden potato chips with pure rock salt seasoning.',
                'description' => 'Made from selected quality potatoes, thinly sliced and kettle-fried to perfection. The timeless classic snack for parties and quick cravings.',
                'price' => 2.50,
                'sale_price' => 2.00,
                'stock' => 0, // Intentionally 0 to verify out-of-stock badge
                'images' => [
                    'https://images.unsplash.com/photo-1566478989037-eec170784d0b?auto=format&fit=crop&w=600&q=80',
                ],
                'status' => true,
                'featured' => false,
                'sort_order' => 4,
            ],
        ];

        foreach ($products as $item) {
            if (!empty($item['mode_id']) && !empty($item['category_id'])) {
                if (empty($item['image']) && !empty($item['images'][0])) {
                    $item['image'] = $item['images'][0];
                }
                Product::updateOrCreate(
                    ['slug' => $item['slug']],
                    $item
                );
            }
        }
    }
}
