<?php

namespace App\Filament\Widgets;

use App\Enums\InternalOrderStatus;
use App\Models\Order;
use App\Models\Variant;
use Carbon\Carbon;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected static bool $isLazy = false;

    protected function getStats(): array
    {
        $today = Carbon::today('Asia/Makassar');

        // 1. Pesanan Baru (Menunggu Pembayaran / Verifikasi)
        $newOrdersCount = Order::where('internal_status', InternalOrderStatus::ORDER_PLACED->value)->count();

        // 2. Pesanan Hari Ini (Jadwal Fresh Bake Hari Ini)
        $todayOrdersCount = Order::whereDate('fulfillment_date', $today)
            ->where('internal_status', '!=', InternalOrderStatus::CANCELLED->value)
            ->count();

        // 3. Stok Menipis (Varian dengan stok <= 3)
        $lowStockCount = Variant::where('stock', '<=', 3)->count();

        // 4. Total Omset Penjualan Terverifikasi (Laporan Penjualan)
        $totalRevenue = Order::whereNotIn('internal_status', [
            InternalOrderStatus::ORDER_PLACED->value,
            InternalOrderStatus::CANCELLED->value,
        ])->sum('total_amount');

        return [
            Stat::make('Pesanan Baru', $newOrdersCount)
                ->description('Menunggu konfirmasi / pembayaran')
                ->descriptionIcon(Heroicon::OutlinedClock)
                ->color($newOrdersCount > 0 ? 'warning' : 'gray'),

            Stat::make('Pesanan Hari Ini', $todayOrdersCount)
                ->description('Jadwal bake & kirim hari ini')
                ->descriptionIcon(Heroicon::OutlinedCalendar)
                ->color('primary'),

            Stat::make('Stok Menipis', $lowStockCount)
                ->description('Varian stok ≤ 3 atau habis')
                ->descriptionIcon(Heroicon::OutlinedExclamationTriangle)
                ->color($lowStockCount > 0 ? 'danger' : 'success'),

            Stat::make('Total Omset Penjualan', 'Rp' . number_format($totalRevenue, 0, ',', '.'))
                ->description('Pesanan lunas & diproses')
                ->descriptionIcon(Heroicon::OutlinedBanknotes)
                ->color('success'),
        ];
    }
}
