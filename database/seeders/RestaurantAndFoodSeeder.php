<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Mode;
use App\Models\Product;
use App\Models\Restaurant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RestaurantAndFoodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $foodMode = Mode::where('slug', 'food')->first();
        if (!$foodMode) {
            return;
        }

        // Categories under food mode
        $biryaniCat = Category::firstOrCreate(
            ['slug' => 'food-biryani'],
            [
                'mode_id'    => $foodMode->id,
                'name'       => 'Biryani & Rice Bowls',
                'status'     => true,
                'sort_order' => 1,
            ]
        );

        $startersCat = Category::firstOrCreate(
            ['slug' => 'food-starters'],
            [
                'mode_id'    => $foodMode->id,
                'name'       => 'Starters & Appetizers',
                'status'     => true,
                'sort_order' => 2,
            ]
        );

        $pizzaCat = Category::firstOrCreate(
            ['slug' => 'food-pizzas'],
            [
                'mode_id'    => $foodMode->id,
                'name'       => 'Pizzas & Fast Food',
                'status'     => true,
                'sort_order' => 3,
            ]
        );

        // 1. Restaurant: Thalappakatti Biryani
        $thalappakatti = Restaurant::updateOrCreate(
            ['slug' => 'thalappakatti-biryani'],
            [
                'name'          => 'Dindigul Thalappakatti Biryani',
                'image'         => 'https://images.unsplash.com/photo-1563379091339-03b21ab4a4f8?auto=format&fit=crop&w=600&q=80',
                'banner_image'  => 'https://images.unsplash.com/photo-1589302168068-964664d93dc0?auto=format&fit=crop&w=1200&q=80',
                'cuisine'       => 'Biryani, South Indian, Chettinad, Tandoori',
                'rating'        => 4.7,
                'ratings_count' => 1420,
                'delivery_time' => '30-35 mins',
                'cost_for_two'  => 500,
                'address'       => '12/4 GST Road, Guindy',
                'city'          => 'Chennai',
                'is_pure_veg'   => false,
                'is_featured'   => true,
                'status'        => true,
            ]
        );

        // 2. Restaurant: Buhari Hotel
        $buhari = Restaurant::updateOrCreate(
            ['slug' => 'buhari-hotel'],
            [
                'name'          => 'Buhari Hotel (Est. 1951)',
                'image'         => 'https://images.unsplash.com/photo-1552611052-33e04de081de?auto=format&fit=crop&w=600&q=80',
                'banner_image'  => 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&w=1200&q=80',
                'cuisine'       => 'Mughlai, Biryani, North Indian, Chinese',
                'rating'        => 4.5,
                'ratings_count' => 2150,
                'delivery_time' => '25-30 mins',
                'cost_for_two'  => 600,
                'address'       => '83 Mount Road, Anna Salai',
                'city'          => 'Chennai',
                'is_pure_veg'   => false,
                'is_featured'   => true,
                'status'        => true,
            ]
        );

        // 3. Restaurant: Domino's Pizza
        $dominos = Restaurant::updateOrCreate(
            ['slug' => 'dominos-pizza'],
            [
                'name'          => "Domino's Pizza",
                'image'         => 'https://images.unsplash.com/photo-1513104890138-7c749659a591?auto=format&fit=crop&w=600&q=80',
                'banner_image'  => 'https://images.unsplash.com/photo-1590947132387-155cc02f3212?auto=format&fit=crop&w=1200&q=80',
                'cuisine'       => 'Pizzas, Pastas, Garlic Breads, Fast Food',
                'rating'        => 4.4,
                'ratings_count' => 3800,
                'delivery_time' => '20-25 mins',
                'cost_for_two'  => 400,
                'address'       => 'Phoenix Marketcity Mall, Velachery',
                'city'          => 'Chennai',
                'is_pure_veg'   => false,
                'is_featured'   => true,
                'status'        => true,
            ]
        );

        // Standard Add-on packages for Biryani
        $biryaniAddons = [
            [
                'group'   => 'Gravy & Accompaniments',
                'type'    => 'multiple',
                'options' => [
                    ['name' => 'Extra Salna / Chalna Gravy (250ml)', 'price' => 25.00],
                    ['name' => 'Boiled Egg (1 pc)',                   'price' => 15.00],
                    ['name' => 'Special Onion Raita (200ml)',          'price' => 15.00],
                    ['name' => 'Brinjal Gravy (Ennai Kathirikai)',    'price' => 20.00],
                ],
            ],
            [
                'group'   => 'Beverages & Soft Drinks',
                'type'    => 'multiple',
                'options' => [
                    ['name' => 'Coca-Cola Can (300ml)',  'price' => 40.00],
                    ['name' => 'Thums Up Can (300ml)',   'price' => 40.00],
                    ['name' => 'Rose Milk Chilled',      'price' => 45.00],
                ],
            ],
        ];

        // Seed Thalappakatti Dishes
        Product::updateOrCreate(
            ['slug' => 'thalappakatti-chicken-dum-biryani'],
            [
                'mode_id'        => $foodMode->id,
                'restaurant_id'  => $thalappakatti->id,
                'category_id'    => $biryaniCat->id,
                'name'           => 'Thalappakatti Chicken Dum Biryani',
                'sku'            => 'THAL-BIRY-01',
                'short_description' => 'Authentic Seeraga Samba rice biryani slow cooked with succulent tender chicken.',
                'description'    => 'Signature Dindigul Thalappakatti biryani made with fragrant Seeraga Samba short-grain rice, fresh ground spices, ghee, and succulent farm-fresh chicken pieces.',
                'price'          => 299.00,
                'sale_price'     => 269.00,
                'stock'          => 50,
                'delivery_time'  => '30-35 mins',
                'return_policy'  => 'non_returnable',
                'is_veg'         => false,
                'addons'         => $biryaniAddons,
                'image'          => 'https://images.unsplash.com/photo-1563379091339-03b21ab4a4f8?auto=format&fit=crop&w=600&q=80',
                'status'         => true,
                'featured'       => true,
            ]
        );

        Product::updateOrCreate(
            ['slug' => 'thalappakatti-mutton-biryani'],
            [
                'mode_id'        => $foodMode->id,
                'restaurant_id'  => $thalappakatti->id,
                'category_id'    => $biryaniCat->id,
                'name'           => 'Thalappakatti Special Mutton Biryani',
                'sku'            => 'THAL-BIRY-02',
                'short_description' => 'Tender grass-fed young mutton slow-cooked with pure ghee and samba rice.',
                'description'    => 'Our royal recipe of tender young mutton and small Seeraga Samba rice cooked in copper handi on firewood coals.',
                'price'          => 399.00,
                'sale_price'     => 369.00,
                'stock'          => 40,
                'delivery_time'  => '30-35 mins',
                'return_policy'  => 'non_returnable',
                'is_veg'         => false,
                'addons'         => $biryaniAddons,
                'image'          => 'https://images.unsplash.com/photo-1633945274405-b6c8069047b0?auto=format&fit=crop&w=600&q=80',
                'status'         => true,
                'featured'       => true,
            ]
        );

        Product::updateOrCreate(
            ['slug' => 'thalappakatti-chicken-65-boneless'],
            [
                'mode_id'        => $foodMode->id,
                'restaurant_id'  => $thalappakatti->id,
                'category_id'    => $startersCat->id,
                'name'           => 'Crispy Chicken 65 (Boneless)',
                'sku'            => 'THAL-STR-01',
                'short_description' => 'Crispy fried boneless chicken bites tossed with fresh curry leaves.',
                'description'    => 'Juicy boneless chicken marinated in our secret southern spice blend, deep-fried to golden perfection and garnished with fried curry leaves and sliced lemon.',
                'price'          => 220.00,
                'sale_price'     => 199.00,
                'stock'          => 35,
                'delivery_time'  => '25-30 mins',
                'return_policy'  => 'non_returnable',
                'is_veg'         => false,
                'addons'         => [
                    [
                        'group'   => 'Dips & Drinks',
                        'type'    => 'multiple',
                        'options' => [
                            ['name' => 'Extra Green Mint Chutney', 'price' => 15.00],
                            ['name' => 'Garlic Mayonnaise Dip',   'price' => 20.00],
                            ['name' => 'Coca-Cola Can (300ml)',   'price' => 40.00],
                        ],
                    ],
                ],
                'image'          => 'https://images.unsplash.com/photo-1610057099443-fde8c4d50f91?auto=format&fit=crop&w=600&q=80',
                'status'         => true,
                'featured'       => false,
            ]
        );

        // Seed Buhari Dishes
        Product::updateOrCreate(
            ['slug' => 'buhari-authentic-chicken-biryani'],
            [
                'mode_id'        => $foodMode->id,
                'restaurant_id'  => $buhari->id,
                'category_id'    => $biryaniCat->id,
                'name'           => 'Buhari Authentic Chicken Biryani',
                'sku'            => 'BUH-BIRY-01',
                'short_description' => 'The legendary recipe invented at Buhari Chennai, served with egg and brinjal dalcha.',
                'description'    => 'A historic culinary marvel crafted with fine long-grain Basmati rice, slow-cooked in rich clarified butter, aromatic whole spices, and tender chicken.',
                'price'          => 285.00,
                'sale_price'     => 255.00,
                'stock'          => 60,
                'delivery_time'  => '25-30 mins',
                'return_policy'  => 'non_returnable',
                'is_veg'         => false,
                'addons'         => $biryaniAddons,
                'image'          => 'https://images.unsplash.com/photo-1589302168068-964664d93dc0?auto=format&fit=crop&w=600&q=80',
                'status'         => true,
                'featured'       => true,
            ]
        );

        // Seed Domino's Pizza
        Product::updateOrCreate(
            ['slug' => 'dominos-peppy-paneer-cheese-burst'],
            [
                'mode_id'        => $foodMode->id,
                'restaurant_id'  => $dominos->id,
                'category_id'    => $pizzaCat->id,
                'name'           => 'Peppy Paneer Cheese Burst Pizza',
                'sku'            => 'DOM-PIZ-01',
                'short_description' => 'Spiced paneer chunks, crisp capsicum, and spicy red paprika with molten cheese.',
                'description'    => 'Delicious soft paneer cubes marinated in fiery peri-peri spices, combined with fresh green bell peppers and red paprika on our famous liquid mozzarella cheese burst crust.',
                'price'          => 419.00,
                'sale_price'     => 379.00,
                'stock'          => 50,
                'delivery_time'  => '20-25 mins',
                'return_policy'  => 'non_returnable',
                'is_veg'         => true,
                'addons'         => [
                    [
                        'group'   => 'Crust & Cheese Upgrades',
                        'type'    => 'multiple',
                        'options' => [
                            ['name' => 'Extra Mozzarella Cheese Layer', 'price' => 75.00],
                            ['name' => 'Cheesy Jalapeno Dip (25g)',     'price' => 30.00],
                            ['name' => 'Garlic Breadsticks (4 pcs)',    'price' => 99.00],
                        ],
                    ],
                    [
                        'group'   => 'Cold Beverages',
                        'type'    => 'multiple',
                        'options' => [
                            ['name' => 'Coca-Cola (500ml Bottle)',      'price' => 50.00],
                            ['name' => 'Fanta Orange (500ml Bottle)',   'price' => 50.00],
                        ],
                    ],
                ],
                'image'          => 'https://images.unsplash.com/photo-1513104890138-7c749659a591?auto=format&fit=crop&w=600&q=80',
                'status'         => true,
                'featured'       => true,
            ]
        );
    }
}
