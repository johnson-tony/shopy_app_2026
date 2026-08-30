<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Super Administrator',
                'email' => 'superadmin@shopy.test',
                'phone' => '+1000000001',
                'password' => 'password',
                'status' => User::STATUS_ACTIVE,
                'email_verified_at' => now(),
                'role' => 'super-admin',
            ],
            [
                'name' => 'Store Admin',
                'email' => 'admin@shopy.test',
                'phone' => '+1000000002',
                'password' => 'password',
                'status' => User::STATUS_ACTIVE,
                'email_verified_at' => now(),
                'role' => 'admin',
            ],
            [
                'name' => 'Order Manager',
                'email' => 'manager@shopy.test',
                'phone' => '+1000000003',
                'password' => 'password',
                'status' => User::STATUS_ACTIVE,
                'email_verified_at' => now(),
                'role' => 'order-manager',
            ],
            [
                'name' => 'Alice Customer',
                'email' => 'customer@shopy.test',
                'phone' => '+1234567890',
                'password' => 'password',
                'status' => User::STATUS_ACTIVE,
                'email_verified_at' => now(),
                'role' => 'customer',
            ],
            [
                'name' => 'John Doe',
                'email' => 'john@shopy.test',
                'phone' => '+1987654321',
                'password' => 'password',
                'status' => User::STATUS_ACTIVE,
                'email_verified_at' => now(),
                'role' => 'customer',
            ],
            [
                'name' => 'Inactive User',
                'email' => 'inactive@shopy.test',
                'phone' => '+1111111111',
                'password' => 'password',
                'status' => User::STATUS_INACTIVE,
                'email_verified_at' => now(),
                'role' => 'customer',
            ],
            [
                'name' => 'Blocked User',
                'email' => 'blocked@shopy.test',
                'phone' => '+1222222222',
                'password' => 'password',
                'status' => User::STATUS_BLOCKED,
                'email_verified_at' => now(),
                'role' => 'customer',
            ],
            [
                'name' => 'Pending User',
                'email' => 'pending@shopy.test',
                'phone' => '+1333333333',
                'password' => 'password',
                'status' => User::STATUS_PENDING,
                'email_verified_at' => null,
                'role' => 'customer',
            ],
        ];

        foreach ($users as $userData) {
            $roleSlug = $userData['role'];
            unset($userData['role']);

            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                $userData
            );

            $role = Role::where('slug', $roleSlug)->first();
            if ($role && !$user->roles()->where('roles.id', $role->id)->exists()) {
                $user->roles()->attach($role->id);
            }
        }
    }
}
