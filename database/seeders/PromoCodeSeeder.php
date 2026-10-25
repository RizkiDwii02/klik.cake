<?php

namespace Database\Seeders;

use App\Enums\PromoCodeType;
use App\Models\PromoCode;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PromoCodeSeeder extends Seeder
{
    public function run(): void
    {
        $promos = [
            [
                'code' => 'KLIKMANIS',
                'type' => PromoCodeType::PERCENTAGE->value,
                'value' => 10, // 10%
                'min_order_amount' => 100000,
                'max_discount' => 25000, // maks Rp25.000
                'expires_at' => Carbon::now('Asia/Makassar')->addMonths(3),
                'is_active' => true,
            ],
            [
                'code' => 'FRESHBAKE',
                'type' => PromoCodeType::FIXED->value,
                'value' => 20000, // Rp20.000
                'min_order_amount' => 150000,
                'max_discount' => null,
                'expires_at' => Carbon::now('Asia/Makassar')->addMonths(3),
                'is_active' => true,
            ],
        ];

        foreach ($promos as $promo) {
            PromoCode::updateOrCreate(
                ['code' => $promo['code']],
                $promo
            );
        }
    }
}
