<?php

namespace App\Console\Commands;

use App\Enums\InternalOrderStatus;
use App\Models\Order;
use App\Services\OrderService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ExpireOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Batalkan pesanan yang belum dibayar setelah melewati batas waktu 1 jam dan lepaskan kuota slot serta stok.';

    public function handle(OrderService $orderService): int
    {
        $now = Carbon::now('Asia/Makassar');

        $expiredOrders = Order::where('internal_status', InternalOrderStatus::ORDER_PLACED->value)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', $now)
            ->get();

        $count = $expiredOrders->count();

        if ($count === 0) {
            $this->info('Tidak ada pesanan kedaluwarsa.');
            return Command::SUCCESS;
        }

        $this->info("Menemukan {$count} pesanan kedaluwarsa. Memproses pembatalan...");

        foreach ($expiredOrders as $order) {
            try {
                $orderService->cancelAndRelease(
                    $order,
                    'Batas waktu pembayaran 1 jam telah berakhir (kedaluwarsa otomatis scheduler).'
                );
                // Safe logging: ONLY order id and status, never secrets or sensitive data
                Log::info("Order #{$order->order_number} expired and cancelled via orders:expire scheduler.");
                $this->line("Pesanan {$order->order_number} berhasil dibatalkan dan kuota dikembalikan.");
            } catch (\Throwable $e) {
                Log::error("Gagal membatalkan order #{$order->order_number}: " . $e->getMessage());
                $this->error("Gagal membatalkan {$order->order_number}: " . $e->getMessage());
            }
        }

        $this->info("Selesai memproses {$count} pesanan kedaluwarsa.");
        return Command::SUCCESS;
    }
}
