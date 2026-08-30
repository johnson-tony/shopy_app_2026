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
            [
                'name' => 'View Dashboard',
                'slug' => 'view-dashboard',
                'description' => 'Permission to view the administration dashboard.',
            ],
            [
                'name' => 'Manage Admins',
                'slug' => 'manage-admins',
                'description' => 'Permission to create, update, and manage administrator staff.',
            ],
            [
                'name' => 'Manage Roles',
                'slug' => 'manage-roles',
                'description' => 'Permission to modify access control and role assignments.',
            ],
            [
                'name' => 'Manage Customers',
                'slug' => 'manage-customers',
                'description' => 'Permission to view and manage registered customer accounts.',
            ],
            [
                'name' => 'Manage Products',
                'slug' => 'manage-products',
                'description' => 'Permission to view, create, edit, and delete products and inventory.',
            ],
            [
                'name' => 'Manage Orders',
                'slug' => 'manage-orders',
                'description' => 'Permission to manage orders, process fulfillments, and handle refunds.',
            ],
        ];

        foreach ($permissions as $permData) {
            Permission::firstOrCreate(
                ['slug' => $permData['slug']],
                $permData
            );
        }

        // Assign permissions to admin roles
        $superAdmin = Role::where('slug', 'super-admin')->first();
        $admin = Role::where('slug', 'admin')->first();
        $orderManager = Role::where('slug', 'order-manager')->first();

        $allPermissions = Permission::all();

        if ($superAdmin) {
            $superAdmin->permissions()->sync($allPermissions->pluck('id'));
        }

        if ($admin) {
            $admin->permissions()->sync($allPermissions->pluck('id'));
        }

        if ($orderManager) {
            $managerPermissions = Permission::whereIn('slug', ['view-dashboard', 'manage-products', 'manage-orders'])->get();
            $orderManager->permissions()->sync($managerPermissions->pluck('id'));
        }
    }
}
