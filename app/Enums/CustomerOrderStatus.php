<?php

namespace App\Enums;

enum CustomerOrderStatus: string
{
    case RECEIVED = 'Diterima';
    case BAKING = 'Dipanggang';
    case READY = 'Siap';
    case COMPLETED = 'Selesai';
    case CANCELLED = 'Dibatalkan';

    /**
     * Return 1-based step index matching customer tracking screen 07.
     */
    public function stepIndex(): int
    {
        return match ($this) {
            self::RECEIVED => 1,
            self::BAKING => 2,
            self::READY => 3,
            self::COMPLETED => 4,
            self::CANCELLED => 0,
        };
    }

    /**
     * All 4 standard customer progress steps.
     *
     * @return array<int, string>
     */
    public static function trackingSteps(): array
    {
        return [
            1 => self::RECEIVED->value,
            2 => self::BAKING->value,
            3 => self::READY->value,
            4 => self::COMPLETED->value,
        ];
    }
}
