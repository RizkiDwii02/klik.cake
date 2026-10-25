<?php

namespace Database\Seeders;

use App\Models\SlotQuota;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class SlotQuotaSeeder extends Seeder
{
    public function run(): void
    {
        $today = Carbon::now('Asia/Makassar')->startOfDay();
        $slots = ['10:00', '13:00', '16:00'];
        $defaultCapacity = (int) config('shop.schedule.default_slot_capacity', 5);

        for ($i = 0; $i < 14; $i++) {
            $date = $today->copy()->addDays($i)->toDateString();

            foreach ($slots as $slot) {
                SlotQuota::updateOrCreate(
                    [
                        'date' => $date,
                        'time_slot' => $slot,
                    ],
                    [
                        'capacity' => $defaultCapacity,
                        'booked' => 0,
                    ]
                );
            }
        }
    }
}
