<?php

namespace App\Services;

use App\Contracts\PaymentGateway;
use App\Models\Order;

class FakeGateway implements PaymentGateway
{
    /**
     * @var array<string, array<string, mixed>>
     */
    protected array $transactions = [];

    public function createCharge(Order $order, string $paymentMethod): array
    {
        $txId = 'FAKE-TX-' . $order->order_number . '-' . time();
        $response = [
            'transaction_id' => $txId,
            'status' => 'pending',
            'gross_amount' => $order->total_amount,
            'payment_method' => $paymentMethod,
            'redirect_url' => 'http://localhost/fake-payment/' . $txId,
            'qr_string' => '00020101021226680016ID.CO.MIDTRANS.WWW01189360050300000876645204481453033605802ID5910KLIK CAKE6008MAKASSAR62070703A0163041234',
            'va_number' => '87654' . substr((string) $order->id, -5),
            'expires_at' => now()->addHour()->toIso8601String(),
        ];

        $this->transactions[$txId] = $response;

        return $response;
    }

    public function getStatus(string $transactionId): string
    {
        return $this->transactions[$transactionId]['status'] ?? 'settlement';
    }

    public function verifyNotification(array $payload): array
    {
        return [
            'order_id' => (string) ($payload['order_id'] ?? 'KC-TEST'),
            'transaction_id' => (string) ($payload['transaction_id'] ?? 'FAKE-TX-001'),
            'status' => (string) ($payload['transaction_status'] ?? 'settlement'),
            'gross_amount' => (int) ($payload['gross_amount'] ?? 100000),
            'payment_method' => (string) ($payload['payment_type'] ?? 'qris'),
            'fraud_status' => (string) ($payload['fraud_status'] ?? 'accept'),
        ];
    }
}
