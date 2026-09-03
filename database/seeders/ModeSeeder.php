<?php

namespace Database\Seeders;

use App\Models\Mode;
use Illuminate\Database\Seeder;

class ModeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $modes = [
            [
                'name' => 'Shopy',
                'slug' => 'shopy',
                'description' => 'General shopping',
                'icon' => 'fa-solid fa-bag-shopping',
                'status' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Food',
                'slug' => 'food',
                'description' => 'Food ordering',
                'icon' => 'fa-solid fa-utensils',
                'status' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Minutes',
                'slug' => 'minutes',
                'description' => 'Grocery and quick commerce',
                'icon' => 'fa-solid fa-bolt',
                'status' => true,
                'sort_order' => 3,
            ],
        ];

        foreach ($modes as $data) {
            Mode::updateOrCreate(
                ['slug' => $data['slug']],
                $data
            );
        }
    }
}
