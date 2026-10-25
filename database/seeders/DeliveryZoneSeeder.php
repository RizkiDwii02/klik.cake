<?php

namespace Database\Seeders;

use App\Models\DeliveryZone;
use Illuminate\Database\Seeder;

class DeliveryZoneSeeder extends Seeder
{
    public function run(): void
    {
        $zones = [
            [
                'name' => 'Makassar Kota & Panakkukang',
                'fee' => 15000,
                'estimated_delivery_time' => '30 – 45 menit',
                'is_active' => true,
            ],
            [
                'name' => 'Tamalanrea & Biringkanaya',
                'fee' => 25000,
                'estimated_delivery_time' => '45 – 60 menit',
                'is_active' => true,
            ],
            [
                'name' => 'Somba Opu & Gowa Perbatasan',
                'fee' => 30000,
                'estimated_delivery_time' => '60 – 90 menit',
                'is_active' => true,
            ],
        ];

        foreach ($zones as $zone) {
            DeliveryZone::updateOrCreate(
                ['name' => $zone['name']],
                $zone
            );
        }
    }
}
