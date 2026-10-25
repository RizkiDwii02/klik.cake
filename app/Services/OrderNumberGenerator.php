<?php

namespace App\Services;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class OrderNumberGenerator
{
    /**
     * Generate a unique sequential order number formatted as KC-YYYYMMDD-XXXX.
     */
    public function generate(?Carbon $date = null): string
    {
        $currentDate = $date ?? Carbon::now('Asia/Makassar');
        $datePrefix = 'KC-' . $currentDate->format('Ymd') . '-';

        return DB::transaction(function () use ($datePrefix) {
            // Find highest sequence for today
            $latest = Order::where('order_number', 'LIKE', $datePrefix . '%')
                ->lockForUpdate()
                ->orderBy('order_number', 'desc')
                ->first();

            $sequence = 1;
            if ($latest) {
                $parts = explode('-', $latest->order_number);
                if (isset($parts[2]) && is_numeric($parts[2])) {
                    $sequence = ((int) $parts[2]) + 1;
                }
            }

            return $datePrefix . str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
        });
    }
}
