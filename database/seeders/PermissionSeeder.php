<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // User management
            ['name' => 'View Users', 'slug' => 'users.view', 'description' => 'Permission to view users list and details.'],
            ['name' => 'Create Users', 'slug' => 'users.create', 'description' => 'Permission to create new users.'],
            ['name' => 'Update Users', 'slug' => 'users.update', 'description' => 'Permission to update user records.'],
            ['name' => 'Delete Users', 'slug' => 'users.delete', 'description' => 'Permission to delete user records.'],

            // Products (foundation placeholders)
            ['name' => 'View Products', 'slug' => 'products.view', 'description' => 'Permission to view products in admin.'],
            ['name' => 'Create Products', 'slug' => 'products.create', 'description' => 'Permission to add new products.'],
            ['name' => 'Update Products', 'slug' => 'products.update', 'description' => 'Permission to edit products.'],
            ['name' => 'Delete Products', 'slug' => 'products.delete', 'description' => 'Permission to delete products.'],

            // Orders (foundation placeholders)
            ['name' => 'View Orders', 'slug' => 'orders.view', 'description' => 'Permission to view customer orders.'],
            ['name' => 'Update Orders', 'slug' => 'orders.update', 'description' => 'Permission to update order status.'],

            // Admin Dashboard
            ['name' => 'Access Admin Dashboard', 'slug' => 'dashboard.view', 'description' => 'Permission to view the admin dashboard.'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['slug' => $permission['slug']], $permission);
        }

        // Attach permissions to Admin
        $adminRole = Role::where('slug', 'admin')->first();
        if ($adminRole) {
            $adminPermissions = Permission::whereIn('slug', [
                'users.view',
                'users.create',
                'users.update',
                'products.view',
                'products.create',
                'products.update',
                'orders.view',
                'orders.update',
                'dashboard.view',
            ])->pluck('id')->toArray();

            $adminRole->permissions()->sync($adminPermissions);
        }

        // Attach permissions to Order Manager
        $orderManagerRole = Role::where('slug', 'order-manager')->first();
        if ($orderManagerRole) {
            $orderManagerPermissions = Permission::whereIn('slug', [
                'orders.view',
                'orders.update',
                'products.view',
                'dashboard.view',
            ])->pluck('id')->toArray();

            $orderManagerRole->permissions()->sync($orderManagerPermissions);
        }

        // Super Admin gets all permissions
        $superAdminRole = Role::where('slug', 'super-admin')->first();
        if ($superAdminRole) {
            $allPermissionIds = Permission::pluck('id')->toArray();
            $superAdminRole->permissions()->sync($allPermissionIds);
        }
    }
}
