<?php

namespace Database\Seeders;

use App\Models\Mode;
use App\Models\Permission;
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
                'status' => true,
            ],
            [
                'name' => 'Administrator',
                'slug' => 'admin',
                'description' => 'System administrator managing operations, products, and staff.',
                'status' => true,
            ],
            [
                'name' => 'Food Manager',
                'slug' => 'food-manager',
                'description' => 'Sub administrator responsible exclusively for the Food Ordering business mode.',
                'status' => true,
            ],
            [
                'name' => 'Minutes Manager',
                'slug' => 'minutes-manager',
                'description' => 'Sub administrator responsible exclusively for the Minutes / Quick Commerce mode.',
                'status' => true,
            ],
            [
                'name' => 'Order Manager',
                'slug' => 'order-manager',
                'description' => 'Administrative staff responsible for order fulfillment and catalog.',
                'status' => true,
            ],
        ];

        foreach ($roles as $roleData) {
            Role::updateOrCreate(
                ['slug' => $roleData['slug']],
                $roleData
            );
        }

        // Attach mode access to roles
        $foodMode = Mode::where('slug', 'food')->first();
        $minutesMode = Mode::where('slug', 'minutes')->first();
        $shopyMode = Mode::where('slug', 'shopy')->first();

        $adminRole = Role::where('slug', 'admin')->first();
        if ($adminRole && $shopyMode && $foodMode && $minutesMode) {
            $adminRole->modes()->syncWithoutDetaching([$shopyMode->id, $foodMode->id, $minutesMode->id]);
            // Admin gets all current permissions
            $allPerms = Permission::all();
            $adminRole->permissions()->sync($allPerms->pluck('id'));
        }

        $foodManagerRole = Role::where('slug', 'food-manager')->first();
        if ($foodManagerRole && $foodMode) {
            $foodManagerRole->modes()->syncWithoutDetaching([$foodMode->id]);
            $foodPerms = Permission::whereIn('slug', [
                'dashboard.view', 'view-dashboard',
                'categories.view', 'categories.create', 'categories.edit',
                'products.view', 'products.create', 'products.edit', 'manage-products',
            ])->pluck('id');
            $foodManagerRole->permissions()->sync($foodPerms);
        }

        $minutesManagerRole = Role::where('slug', 'minutes-manager')->first();
        if ($minutesManagerRole && $minutesMode) {
            $minutesManagerRole->modes()->syncWithoutDetaching([$minutesMode->id]);
            $minutesPerms = Permission::whereIn('slug', [
                'dashboard.view', 'view-dashboard',
                'categories.view', 'categories.create', 'categories.edit',
                'products.view', 'products.create', 'products.edit', 'manage-products',
            ])->pluck('id');
            $minutesManagerRole->permissions()->sync($minutesPerms);
        }

        $orderManagerRole = Role::where('slug', 'order-manager')->first();
        if ($orderManagerRole) {
            $orderPerms = Permission::whereIn('slug', [
                'dashboard.view', 'view-dashboard',
                'manage-orders', 'orders.view',
                'manage-products', 'products.view',
            ])->pluck('id');
            $orderManagerRole->permissions()->sync($orderPerms);
        }
    }
}
