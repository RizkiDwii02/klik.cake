<?php

namespace App\Services;

use App\Contracts\PaymentGateway;
use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Midtrans\Config;
use Midtrans\CoreApi;
use Midtrans\Snap;
use Midtrans\Transaction;

class MidtransGateway implements PaymentGateway
{
    public function __construct()
    {
        $this->configure();
    }

    protected function configure(): void
    {
        Config::$serverKey = config('shop.midtrans.server_key', env('MIDTRANS_SERVER_KEY', ''));
        Config::$clientKey = config('shop.midtrans.client_key', env('MIDTRANS_CLIENT_KEY', ''));
        Config::$isProduction = (bool) config('shop.midtrans.is_production', false);
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    /**
     * Charge payment using Midtrans Core API (with Snap fallback if channel inactive).
     */
    public function createCharge(Order $order, string $paymentMethod): array
    {
        $this->configure();

        // If no server key is configured in env, throw or handle safely
        if (empty(Config::$serverKey)) {
            Log::warning("Midtrans server key is empty. Using simulated charge for order #{$order->order_number}.");
            return [
                'transaction_id' => 'SANDBOX-SIM-' . $order->order_number,
                'status' => 'pending',
                'gross_amount' => $order->total_amount,
                'payment_method' => $paymentMethod,
                'qr_string' => '00020101021226680016ID.CO.MIDTRANS.WWW01189360050300000876645204481453033605802ID5910KLIK CAKE6008MAKASSAR62070703A0163041234',
                'va_number' => '70012' . substr((string) $order->id, -5),
                'expires_at' => $order->expires_at?->toIso8601String() ?? now()->addHour()->toIso8601String(),
            ];
        }

        try {
            return $this->chargeViaCoreApi($order, $paymentMethod);
        } catch (\Throwable $e) {
            // Channel not active in sandbox account: log warning and use Snap as specified
            Log::warning("Midtrans Core API channel [{$paymentMethod}] unavailable for order #{$order->order_number}: {$e->getMessage()}. Falling back to Snap.");

            return $this->chargeViaSnapFallback($order, $paymentMethod);
        }
    }

    protected function chargeViaCoreApi(Order $order, string $paymentMethod): array
    {
        $baseParams = [
            'transaction_details' => [
                'order_id' => $order->order_number,
                'gross_amount' => $order->total_amount,
            ],
            'customer_details' => [
                'first_name' => $order->customer_name,
                'phone' => $order->customer_phone,
                'email' => $order->customer_email,
            ],
            'custom_expiry' => [
                'order_time' => now()->format('Y-m-d H:i:s O'),
                'expiry_duration' => 60,
                'unit' => 'minute',
            ],
        ];

        switch ($paymentMethod) {
            case 'qris':
                $params = array_merge($baseParams, [
                    'payment_type' => 'qris',
                    'qris' => ['acquirer' => 'gopay'],
                ]);
                break;

            case 'gopay':
                $params = array_merge($baseParams, [
                    'payment_type' => 'gopay',
                    'gopay' => [
                        'enable_callback' => true,
                        'callback_url' => route('payment.show', $order->order_number),
                    ],
                ]);
                break;

            case 'shopeepay':
                $params = array_merge($baseParams, [
                    'payment_type' => 'shopeepay',
                    'shopeepay' => [
                        'callback_url' => route('payment.show', $order->order_number),
                    ],
                ]);
                break;

            case 'bank_transfer':
            case 'transfer':
            default:
                $params = array_merge($baseParams, [
                    'payment_type' => 'bank_transfer',
                    'bank_transfer' => ['bank' => 'bca'],
                ]);
                break;
        }

        $res = CoreApi::charge($params);
        $resObj = is_object($res) ? $res : (object) $res;

        $qrString = null;
        $qrUrl = null;
        $vaNumber = null;

        // Extract QR string or QR URL if available
        if (isset($resObj->qr_string)) {
            $qrString = $resObj->qr_string;
        }

        if (isset($resObj->actions) && is_array($resObj->actions)) {
            foreach ($resObj->actions as $action) {
                $actionObj = is_object($action) ? $action : (object) $action;
                if (($actionObj->name ?? '') === 'generate-qr-code') {
                    $qrUrl = $actionObj->url ?? null;
                }
            }
        }

        // Extract VA numbers
        if (isset($resObj->va_numbers) && is_array($resObj->va_numbers) && ! empty($resObj->va_numbers)) {
            $vaObj = is_object($resObj->va_numbers[0]) ? $resObj->va_numbers[0] : (object) $resObj->va_numbers[0];
            $vaNumber = $vaObj->va_number ?? null;
        } elseif (isset($resObj->permata_va_number)) {
            $vaNumber = $resObj->permata_va_number;
        }

        return [
            'transaction_id' => (string) ($resObj->transaction_id ?? $order->order_number),
            'status' => (string) ($resObj->transaction_status ?? 'pending'),
            'gross_amount' => (int) ($resObj->gross_amount ?? $order->total_amount),
            'payment_method' => $paymentMethod,
            'qr_string' => $qrString,
            'qr_url' => $qrUrl,
            'va_number' => $vaNumber,
            'expires_at' => (string) ($resObj->expiry_time ?? $order->expires_at?->toIso8601String() ?? now()->addHour()->toIso8601String()),
        ];
    }

    protected function chargeViaSnapFallback(Order $order, string $paymentMethod): array
    {
        $params = [
            'transaction_details' => [
                'order_id' => $order->order_number,
                'gross_amount' => $order->total_amount,
            ],
            'customer_details' => [
                'first_name' => $order->customer_name,
                'phone' => $order->customer_phone,
                'email' => $order->customer_email,
            ],
            'expiry' => [
                'start_time' => now()->format('Y-m-d H:i:s O'),
                'unit' => 'minute',
                'duration' => 60,
            ],
        ];

        $snapResponse = Snap::createTransaction($params);

        return [
            'transaction_id' => (string) ($snapResponse->token ?? $order->order_number),
            'status' => 'pending',
            'gross_amount' => $order->total_amount,
            'payment_method' => $paymentMethod,
            'redirect_url' => $snapResponse->redirect_url ?? '',
            'snap_token' => $snapResponse->token ?? '',
            'expires_at' => $order->expires_at?->toIso8601String() ?? now()->addHour()->toIso8601String(),
        ];
    }

    public function getStatus(string $transactionId): string
    {
        $this->configure();

        if (empty(Config::$serverKey) || str_starts_with($transactionId, 'FAKE-') || str_starts_with($transactionId, 'SANDBOX-SIM-')) {
            return 'pending';
        }

        try {
            $status = Transaction::status($transactionId);
            $statusObj = is_object($status) ? $status : (object) $status;

            return (string) ($statusObj->transaction_status ?? 'pending');
        } catch (\Throwable $e) {
            Log::warning("Midtrans getStatus error for #{$transactionId}: {$e->getMessage()}");
            return 'pending';
        }
    }

    public function verifyNotification(array $payload): array
    {
        $this->configure();

        return [
            'order_id' => (string) ($payload['order_id'] ?? ''),
            'transaction_id' => (string) ($payload['transaction_id'] ?? ''),
            'status' => (string) ($payload['transaction_status'] ?? 'pending'),
            'gross_amount' => (int) ($payload['gross_amount'] ?? 0),
            'payment_method' => (string) ($payload['payment_type'] ?? 'unknown'),
            'signature_key' => (string) ($payload['signature_key'] ?? ''),
            'status_code' => (string) ($payload['status_code'] ?? ''),
            'fraud_status' => (string) ($payload['fraud_status'] ?? 'accept'),
        ];
    }
}
