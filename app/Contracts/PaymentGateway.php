<?php

namespace App\Contracts;

use App\Models\Order;

interface PaymentGateway
{
    /**
     * Create a payment charge/transaction for an order.
     *
     * @return array{
     *     transaction_id: string,
     *     status: string,
     *     gross_amount: int,
     *     payment_method: string,
     *     redirect_url?: string,
     *     qr_string?: string,
     *     va_number?: string,
     *     expires_at?: string
     * }
     */
    public function createCharge(Order $order, string $paymentMethod): array;

    /**
     * Get transaction status by ID.
     */
    public function getStatus(string $transactionId): string;

    /**
     * Verify and parse webhook/HTTP notification payload.
     *
     * @param  array<string, mixed>  $payload
     * @return array{
     *     order_id: string,
     *     transaction_id: string,
     *     status: string,
     *     gross_amount: int,
     *     payment_method: string,
     *     fraud_status?: string
     * }
     */
    public function verifyNotification(array $payload): array;
}
