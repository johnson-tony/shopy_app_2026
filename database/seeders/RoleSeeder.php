<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'Super Administrator',
                'slug' => 'super-admin',
                'description' => 'Unrestricted root administrator with complete system access.',
            ],
            [
                'name' => 'Administrator',
                'slug' => 'admin',
                'description' => 'System administrator managing operations, products, and staff.',
            ],
            [
                'name' => 'Order Manager',
                'slug' => 'order-manager',
                'description' => 'Administrative staff responsible for order fulfillment and catalog.',
            ],
        ];

        foreach ($roles as $roleData) {
            Role::firstOrCreate(
                ['slug' => $roleData['slug']],
                $roleData
            );
        }
    }
}
