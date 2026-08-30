<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admins = [
            [
                'name' => 'Root Super Admin',
                'email' => 'superadmin@shopy.test',
                'phone' => '+1000000001',
                'password' => Hash::make('password'),
                'status' => Admin::STATUS_ACTIVE,
                'role' => 'super-admin',
            ],
            [
                'name' => 'Store Administrator',
                'email' => 'admin@shopy.test',
                'phone' => '+1000000002',
                'password' => Hash::make('password'),
                'status' => Admin::STATUS_ACTIVE,
                'role' => 'admin',
            ],
            [
                'name' => 'Order Manager Staff',
                'email' => 'manager@shopy.test',
                'phone' => '+1000000003',
                'password' => Hash::make('password'),
                'status' => Admin::STATUS_ACTIVE,
                'role' => 'order-manager',
            ],
        ];

        foreach ($admins as $adminData) {
            $roleSlug = $adminData['role'];
            unset($adminData['role']);

            $admin = Admin::updateOrCreate(
                ['email' => $adminData['email']],
                $adminData
            );

            $role = Role::where('slug', $roleSlug)->first();
            if ($role && !$admin->roles()->where('roles.id', $role->id)->exists()) {
                $admin->roles()->attach($role->id);
            }
        }
    }
}
