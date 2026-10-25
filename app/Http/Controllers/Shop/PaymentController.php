<?php

namespace App\Http\Controllers\Shop;

use App\Contracts\PaymentGateway;
use App\Enums\InternalOrderStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Services\OrderService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class PaymentController extends Controller
{
    /**
     * Display Payment Page (Screen 06).
     */
    public function show(
        string $orderNumber,
        Request $request,
        OrderService $orderService,
        PaymentGateway $gateway
    ): View|RedirectResponse {
        $order = Order::with(['items', 'latestPayment'])
            ->where('order_number', $orderNumber)
            ->firstOrFail();

        // 1. Lazy check for order expiration
        $order = $orderService->checkAndExpireIfNeeded($order);

        // 2. If already paid, redirect straight to tracking
        if ($order->internal_status !== InternalOrderStatus::ORDER_PLACED->value &&
            $order->internal_status !== InternalOrderStatus::CANCELLED->value) {
            return redirect()->route('track.show', ['orderNumber' => $order->order_number])
                ->with('success', 'Pembayaran telah terverifikasi!');
        }

        $selectedMethod = $request->query('method', 'qris');
        $payment = $order->latestPayment;
        $charge = [];

        // 3. Create charge if no payment exists or if customer switched payment method
        if (! $order->isExpired() && ($payment === null || $request->has('method') || empty($payment->raw_notification))) {
            try {
                $charge = $gateway->createCharge($order, $selectedMethod);

                $payment = Payment::updateOrCreate(
                    [
                        'provider_transaction_id' => $charge['transaction_id'] ?? ('PAY-' . $order->order_number . '-' . $selectedMethod),
                    ],
                    [
                        'order_id' => $order->id,
                        'provider' => 'midtrans',
                        'method' => $selectedMethod,
                        'gross_amount' => $order->total_amount,
                        'status' => PaymentStatus::PENDING->value,
                        'raw_notification' => $charge,
                        'expires_at' => isset($charge['expires_at']) ? Carbon::parse($charge['expires_at']) : $order->expires_at,
                    ]
                );
            } catch (\Throwable $e) {
                Log::warning("Charge creation error for order #{$order->order_number}: {$e->getMessage()}");
            }
        } elseif ($payment) {
            $charge = $payment->raw_notification ?? [];
            $selectedMethod = $payment->method ?? $selectedMethod;
        }

        return view('shop.payment', [
            'order' => $order,
            'payment' => $payment,
            'charge' => $charge,
            'selectedMethod' => $selectedMethod,
        ]);
    }

    /**
     * Status inquiry endpoint invoked by "Saya Sudah Bayar" button.
     * Verifies server-side with Midtrans and returns current status.
     */
    public function checkStatus(
        string $orderNumber,
        OrderService $orderService,
        PaymentGateway $gateway
    ): JsonResponse {
        $order = Order::with('latestPayment')
            ->where('order_number', $orderNumber)
            ->firstOrFail();

        // 1. Lazy check expiration
        $order = $orderService->checkAndExpireIfNeeded($order);

        if ($order->internal_status === InternalOrderStatus::CANCELLED->value) {
            return response()->json([
                'status' => 'expired',
                'message' => 'Pesanan telah kedaluwarsa atau dibatalkan karena melewati batas waktu 1 jam.',
            ]);
        }

        if ($order->internal_status === InternalOrderStatus::PAYMENT_VERIFIED->value ||
            $order->internal_status !== InternalOrderStatus::ORDER_PLACED->value) {
            return response()->json([
                'status' => 'paid',
                'redirect_url' => route('track.show', $order->order_number),
            ]);
        }

        // 2. Query status from gateway
        $payment = $order->latestPayment;
        $txId = $payment?->provider_transaction_id ?? $order->order_number;
        $remoteStatus = $gateway->getStatus($txId);

        if (in_array($remoteStatus, ['settlement', 'capture'])) {
            DB::transaction(function () use ($order, $payment) {
                if ($payment) {
                    $payment->update([
                        'status' => PaymentStatus::PAID->value,
                        'paid_at' => now(),
                    ]);
                }

                $order->updateInternalStatus(
                    InternalOrderStatus::PAYMENT_VERIFIED,
                    'Pembayaran terverifikasi melalui pengecekan status server.',
                    'server_verification'
                );
            });

            Log::info("Order #{$order->order_number} verified as PAID via server getStatus check.");

            return response()->json([
                'status' => 'paid',
                'redirect_url' => route('track.show', $order->order_number),
            ]);
        }

        if (in_array($remoteStatus, ['expire', 'cancel', 'deny'])) {
            $orderService->cancelAndRelease($order, "Pembayaran {$remoteStatus} di Midtrans.");

            return response()->json([
                'status' => 'expired',
                'message' => 'Pesanan telah kedaluwarsa atau pembayaran dibatalkan.',
            ]);
        }

        return response()->json([
            'status' => 'pending',
            'message' => 'Pembayaran belum terdeteksi. Silakan selesaikan pembayaran di aplikasi Anda lalu klik tombol ini lagi.',
        ]);
    }
}
