<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = [
            [
                'name' => 'Alice Customer',
                'email' => 'customer@shopy.test',
                'phone' => '+1000000004',
                'password' => Hash::make('password'),
                'status' => User::STATUS_ACTIVE,
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Inactive Customer',
                'email' => 'inactive@shopy.test',
                'phone' => '+1000000005',
                'password' => Hash::make('password'),
                'status' => User::STATUS_INACTIVE,
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Blocked Customer',
                'email' => 'blocked@shopy.test',
                'phone' => '+1000000006',
                'password' => Hash::make('password'),
                'status' => User::STATUS_BLOCKED,
                'email_verified_at' => now(),
            ],
        ];

        foreach ($customers as $customer) {
            User::updateOrCreate(
                ['email' => $customer['email']],
                $customer
            );
        }
    }
}
