<?php

namespace App\Http\Controllers\Shop;

use App\Enums\InternalOrderStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Services\OrderService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    /**
     * Handle incoming notifications from Midtrans.
     * Verified with SHA512 signature, gross_amount match, idempotent,
     * and processed atomically within DB::transaction().
     */
    public function handleMidtrans(Request $request, OrderService $orderService): JsonResponse
    {
        $payload = $request->all();

        $orderId = (string) ($payload['order_id'] ?? '');
        $statusCode = (string) ($payload['status_code'] ?? '');
        $grossAmount = (string) ($payload['gross_amount'] ?? '');
        $signatureKey = (string) ($payload['signature_key'] ?? '');
        $transactionStatus = (string) ($payload['transaction_status'] ?? '');
        $paymentType = (string) ($payload['payment_type'] ?? 'midtrans');
        $fraudStatus = (string) ($payload['fraud_status'] ?? 'accept');
        $transactionId = (string) ($payload['transaction_id'] ?? '');

        $serverKey = (string) config('shop.midtrans.server_key', env('MIDTRANS_SERVER_KEY', ''));

        // 1. Signature Verification: SHA512(order_id + status_code + gross_amount + server_key)
        $expectedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

        if (empty($signatureKey) || ! hash_equals($expectedSignature, $signatureKey)) {
            Log::warning("Midtrans webhook: invalid signature for order #{$orderId}");

            return response()->json(['message' => 'Invalid signature.'], 401);
        }

        // 2. Order Lookup
        $order = Order::where('order_number', $orderId)->first();
        if (! $order) {
            Log::warning("Midtrans webhook: order not found #{$orderId}");

            return response()->json(['message' => 'Order not found.'], 404);
        }

        // 3. Amount Matching: reject with 401 if gross_amount doesn't match total_amount
        $parsedGross = (int) round((float) $grossAmount);
        if ($parsedGross !== (int) $order->total_amount) {
            Log::warning("Midtrans webhook: gross amount mismatch for order #{$orderId}. Expected {$order->total_amount}, got {$parsedGross}");

            return response()->json(['message' => 'Gross amount mismatch.'], 401);
        }

        // 4. Idempotency Check
        $isPaidNotification = ($transactionStatus === 'settlement' || ($transactionStatus === 'capture' && $fraudStatus === 'accept'));

        if ($isPaidNotification && $order->internal_status === InternalOrderStatus::PAYMENT_VERIFIED->value) {
            return response()->json(['message' => 'Notification already processed.'], 200);
        }

        if (in_array($transactionStatus, ['expire', 'cancel', 'deny']) && $order->internal_status === InternalOrderStatus::CANCELLED->value) {
            return response()->json(['message' => 'Notification already processed.'], 200);
        }

        // 5. Atomic Update inside DB Transaction
        DB::transaction(function () use (
            $order,
            $orderService,
            $payload,
            $transactionStatus,
            $paymentType,
            $transactionId,
            $parsedGross,
            $fraudStatus,
            $isPaidNotification
        ) {
            // Determine payment status
            $paymentStatus = match ($transactionStatus) {
                'capture' => ($fraudStatus === 'accept') ? PaymentStatus::PAID : PaymentStatus::PENDING,
                'settlement' => PaymentStatus::PAID,
                'pending' => PaymentStatus::PENDING,
                'deny', 'cancel' => PaymentStatus::CANCELLED,
                'expire' => PaymentStatus::EXPIRED,
                default => PaymentStatus::FAILED,
            };

            // Update or create Payment record
            Payment::updateOrCreate(
                [
                    'provider_transaction_id' => $transactionId ?: $order->order_number,
                ],
                [
                    'order_id' => $order->id,
                    'provider' => 'midtrans',
                    'method' => $paymentType,
                    'gross_amount' => $parsedGross,
                    'status' => $paymentStatus->value,
                    'raw_notification' => $payload,
                    'paid_at' => $isPaidNotification ? now() : null,
                    'expires_at' => isset($payload['expiry_time']) ? Carbon::parse($payload['expiry_time']) : null,
                ]
            );

            // Transition Order Status
            if ($isPaidNotification) {
                $order->updateInternalStatus(
                    InternalOrderStatus::PAYMENT_VERIFIED,
                    "Pembayaran terverifikasi via {$paymentType} (Midtrans).",
                    'midtrans_webhook'
                );
            } elseif ($paymentStatus === PaymentStatus::EXPIRED) {
                $orderService->cancelAndRelease(
                    $order,
                    'Pembayaran kedaluwarsa dari penyedia pembayaran.'
                );
            } elseif (in_array($paymentStatus, [PaymentStatus::CANCELLED, PaymentStatus::FAILED])) {
                $orderService->cancelAndRelease(
                    $order,
                    "Pembayaran {$transactionStatus} dari penyedia pembayaran."
                );
            }
        });

        // Safe logging: log ONLY order id and status, NOT secrets or customer sensitive data
        Log::info("Midtrans webhook processed for order #{$orderId} with status: {$transactionStatus}");

        return response()->json(['status' => 'success'], 200);
    }
}
