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
                'name' => 'Super Admin',
                'slug' => 'super-admin',
                'description' => 'Full access to system and administration settings.',
            ],
            [
                'name' => 'Admin',
                'slug' => 'admin',
                'description' => 'Standard administrator with management access.',
            ],
            [
                'name' => 'Order Manager',
                'slug' => 'order-manager',
                'description' => 'Staff member managing orders and shipments.',
            ],
            [
                'name' => 'Customer',
                'slug' => 'customer',
                'description' => 'Default customer role for registered store shoppers.',
            ],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['slug' => $role['slug']], $role);
        }
    }
}
