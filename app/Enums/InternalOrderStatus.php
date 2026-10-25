<?php

namespace App\Enums;

enum InternalOrderStatus: string
{
    case ORDER_PLACED = 'Pesanan masuk';
    case PAYMENT_VERIFIED = 'Pembayaran terverifikasi';
    case SCHEDULED = 'Masuk jadwal';
    case BAKING = 'Dipanggang';
    case PACKED = 'Dikemas';
    case SHIPPED_OR_READY = 'Dikirim / Siap diambil';
    case COMPLETED = 'Selesai';
    case CANCELLED = 'Dibatalkan';

    /**
     * Map internal status to 4-step customer tracking status (PDF Screen 07).
     */
    public function toCustomerStatus(): CustomerOrderStatus
    {
        return match ($this) {
            self::ORDER_PLACED,
            self::PAYMENT_VERIFIED,
            self::SCHEDULED => CustomerOrderStatus::RECEIVED,
            self::BAKING => CustomerOrderStatus::BAKING,
            self::PACKED,
            self::SHIPPED_OR_READY => CustomerOrderStatus::READY,
            self::COMPLETED => CustomerOrderStatus::COMPLETED,
            self::CANCELLED => CustomerOrderStatus::CANCELLED,
        };
    }
}
