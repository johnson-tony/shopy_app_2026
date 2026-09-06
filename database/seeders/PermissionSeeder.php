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
            // Dashboard
            ['name' => 'View Dashboard', 'slug' => 'dashboard.view', 'module' => 'dashboard', 'description' => 'View the admin control panel dashboard.'],
            ['name' => 'View Dashboard (Legacy)', 'slug' => 'view-dashboard', 'module' => 'dashboard', 'description' => 'Legacy permission for admin dashboard.'],

            // Shopping Modes
            ['name' => 'View Modes', 'slug' => 'modes.view', 'module' => 'modes', 'description' => 'Browse and view shopping modes.'],
            ['name' => 'Create Modes', 'slug' => 'modes.create', 'module' => 'modes', 'description' => 'Add new shopping modes.'],
            ['name' => 'Edit Modes', 'slug' => 'modes.edit', 'module' => 'modes', 'description' => 'Modify existing shopping modes.'],
            ['name' => 'Delete Modes', 'slug' => 'modes.delete', 'module' => 'modes', 'description' => 'Delete unused shopping modes.'],

            // Categories
            ['name' => 'View Categories', 'slug' => 'categories.view', 'module' => 'categories', 'description' => 'Browse and filter categories.'],
            ['name' => 'Create Categories', 'slug' => 'categories.create', 'module' => 'categories', 'description' => 'Create new categories.'],
            ['name' => 'Edit Categories', 'slug' => 'categories.edit', 'module' => 'categories', 'description' => 'Update categories.'],
            ['name' => 'Delete Categories', 'slug' => 'categories.delete', 'module' => 'categories', 'description' => 'Remove categories.'],

            // Products
            ['name' => 'View Products', 'slug' => 'products.view', 'module' => 'products', 'description' => 'Browse and view products in catalog.'],
            ['name' => 'Create Products', 'slug' => 'products.create', 'module' => 'products', 'description' => 'Add new products to catalog.'],
            ['name' => 'Edit Products', 'slug' => 'products.edit', 'module' => 'products', 'description' => 'Modify existing products.'],
            ['name' => 'Delete Products', 'slug' => 'products.delete', 'module' => 'products', 'description' => 'Remove products from catalog.'],
            ['name' => 'Manage Products (Legacy)', 'slug' => 'manage-products', 'module' => 'products', 'description' => 'Legacy permission for product catalog.'],

            // Restaurants (Food Delivery)
            ['name' => 'View Restaurants', 'slug' => 'restaurants.view', 'module' => 'restaurants', 'description' => 'Browse and view restaurants in food delivery mode.'],
            ['name' => 'Create Restaurants', 'slug' => 'restaurants.create', 'module' => 'restaurants', 'description' => 'Register and onboard new restaurants.'],
            ['name' => 'Edit Restaurants', 'slug' => 'restaurants.edit', 'module' => 'restaurants', 'description' => 'Modify restaurant profile, timing, and menus.'],
            ['name' => 'Delete Restaurants', 'slug' => 'restaurants.delete', 'module' => 'restaurants', 'description' => 'Remove restaurants from food delivery.'],

            // Users / Customers
            ['name' => 'View Users', 'slug' => 'users.view', 'module' => 'users', 'description' => 'Browse registered customer accounts.'],
            ['name' => 'Manage Users', 'slug' => 'users.manage', 'module' => 'users', 'description' => 'Activate, deactivate, and edit users.'],
            ['name' => 'Impersonate Users', 'slug' => 'users.impersonate', 'module' => 'users', 'description' => 'Sign in as a customer for support purposes.'],
            ['name' => 'Manage Customers (Legacy)', 'slug' => 'manage-customers', 'module' => 'users', 'description' => 'Legacy permission for customer accounts.'],

            // Administrators
            ['name' => 'View Admins', 'slug' => 'admins.view', 'module' => 'admins', 'description' => 'View staff and administrator accounts.'],
            ['name' => 'Create Admins', 'slug' => 'admins.create', 'module' => 'admins', 'description' => 'Invite new sub-administrators.'],
            ['name' => 'Edit Admins', 'slug' => 'admins.edit', 'module' => 'admins', 'description' => 'Modify staff roles and status.'],
            ['name' => 'Delete Admins', 'slug' => 'admins.delete', 'module' => 'admins', 'description' => 'Remove administrator accounts.'],
            ['name' => 'Manage Admins (Legacy)', 'slug' => 'manage-admins', 'module' => 'admins', 'description' => 'Legacy permission for staff management.'],

            // Roles & Permissions
            ['name' => 'View Roles', 'slug' => 'roles.view', 'module' => 'roles', 'description' => 'View roles and their permission matrices.'],
            ['name' => 'Create Roles', 'slug' => 'roles.create', 'module' => 'roles', 'description' => 'Create new administrator roles.'],
            ['name' => 'Edit Roles', 'slug' => 'roles.edit', 'module' => 'roles', 'description' => 'Modify role permissions and mode access.'],
            ['name' => 'Delete Roles', 'slug' => 'roles.delete', 'module' => 'roles', 'description' => 'Delete custom roles.'],
            ['name' => 'Manage Roles (Legacy)', 'slug' => 'manage-roles', 'module' => 'roles', 'description' => 'Legacy permission for roles.'],

            // Settings & System
            ['name' => 'View Settings', 'slug' => 'settings.view', 'module' => 'settings', 'description' => 'View system configuration.'],
            ['name' => 'Edit Settings', 'slug' => 'settings.edit', 'module' => 'settings', 'description' => 'Change site configuration and theme.'],

            // Coupons & Promotions
            ['name' => 'View Coupons', 'slug' => 'coupons.view', 'module' => 'coupons', 'description' => 'Browse and view discount coupons.'],
            ['name' => 'Create Coupons', 'slug' => 'coupons.create', 'module' => 'coupons', 'description' => 'Create promotional coupons and vouchers.'],
            ['name' => 'Edit Coupons', 'slug' => 'coupons.edit', 'module' => 'coupons', 'description' => 'Modify existing coupons and toggle status.'],
            ['name' => 'Delete Coupons', 'slug' => 'coupons.delete', 'module' => 'coupons', 'description' => 'Remove discount coupons.'],

            // Orders (placeholder for future orders module)
            ['name' => 'Manage Orders (Legacy)', 'slug' => 'manage-orders', 'module' => 'orders', 'description' => 'Legacy permission for orders.'],
            ['name' => 'View Orders', 'slug' => 'orders.view', 'module' => 'orders', 'description' => 'View customer orders.'],
        ];

        foreach ($permissions as $permData) {
            Permission::updateOrCreate(
                ['slug' => $permData['slug']],
                $permData
            );
        }
    }
}
