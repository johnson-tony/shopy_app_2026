<?php

namespace Database\Seeders;

use App\Models\DeliveryPartner;
use App\Models\Mode;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DeliveryPartnerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $partners = [
            [
                'name' => 'Ravi Kumar',
                'email' => 'rider@shopy.test',
                'phone' => '+1000000101',
                'password' => Hash::make('password'),
                'vehicle_type' => 'bike',
                'status' => DeliveryPartner::STATUS_ACTIVE,
                'is_available' => true,
                'latitude' => 13.082680,
                'longitude' => 80.270718,
                'location_source' => DeliveryPartner::LOCATION_SOURCE_STATIC,
                'modes' => ['shopy', 'minutes', 'food'],
            ],
            [
                'name' => 'Priya Sharma',
                'email' => 'priya@shopy.test',
                'phone' => '+1000000102',
                'password' => Hash::make('password'),
                'vehicle_type' => 'scooter',
                'status' => DeliveryPartner::STATUS_ACTIVE,
                'is_available' => true,
                'latitude' => 13.035467,
                'longitude' => 80.159287,
                'location_source' => DeliveryPartner::LOCATION_SOURCE_STATIC,
                'modes' => ['minutes', 'food'],
            ],
        ];

        foreach ($partners as $partnerData) {
            $modeSlugs = $partnerData['modes'] ?? [];
            unset($partnerData['modes']);

            $partner = DeliveryPartner::updateOrCreate(
                ['email' => $partnerData['email']],
                $partnerData
            );

            $modeIds = Mode::whereIn('slug', $modeSlugs)->pluck('id');
            $partner->modes()->sync($modeIds);
        }
    }
}