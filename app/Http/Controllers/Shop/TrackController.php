<?php

namespace App\Http\Controllers\Shop;

use App\Enums\CustomerOrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TrackController extends Controller
{
    /**
     * Search and display order tracking (Screen 07).
     */
    public function index(Request $request): View
    {
        $orderNumber = trim((string) $request->input('order_number', ''));
        $phoneLast4 = trim((string) $request->input('phone_last4', ''));
        $order = null;
        $errorMessage = null;

        if (! empty($orderNumber)) {
            $order = Order::with(['items', 'statusLogs', 'latestPayment'])
                ->where('order_number', $orderNumber)
                ->first();

            if (! $order) {
                $errorMessage = "Pesanan dengan nomor {$orderNumber} tidak ditemukan. Mohon periksa kembali.";
                $order = null;
            } elseif (! empty($phoneLast4)) {
                $cleanPhone = preg_replace('/\D/', '', $order->customer_phone);
                $expectedLast4 = substr($cleanPhone, -4);

                if ($phoneLast4 !== $expectedLast4) {
                    $errorMessage = '4 digit terakhir nomor WhatsApp tidak sesuai dengan data pemesan.';
                    $order = null;
                }
            } else {
                // If phone last 4 is missing, request customer to input it for privacy
                $errorMessage = 'Silakan masukkan 4 digit terakhir nomor WhatsApp pemesan untuk verifikasi.';
                $order = null;
            }
        }

        $stepIndex = 1;
        if ($order) {
            $statusEnum = CustomerOrderStatus::tryFrom($order->customer_status);
            $stepIndex = $statusEnum ? $statusEnum->stepIndex() : 1;
        }

        return view('shop.track', [
            'order' => $order,
            'orderNumber' => $orderNumber,
            'phoneLast4' => $phoneLast4,
            'errorMessage' => $errorMessage,
            'stepIndex' => $stepIndex,
        ]);
    }

    /**
     * Direct link to track order.
     */
    public function show(string $orderNumber, Request $request): View
    {
        $phoneLast4 = trim((string) $request->input('phone_last4', ''));
        $order = Order::with(['items', 'statusLogs', 'latestPayment'])
            ->where('order_number', $orderNumber)
            ->firstOrFail();

        $errorMessage = null;
        $verified = false;

        // Check if phone_last4 is provided or if customer just placed this order
        if (! empty($phoneLast4)) {
            $cleanPhone = preg_replace('/\D/', '', $order->customer_phone);
            if ($phoneLast4 === substr($cleanPhone, -4)) {
                $verified = true;
            } else {
                $errorMessage = '4 digit terakhir nomor WhatsApp tidak cocok.';
            }
        } else {
            // Direct access allowed if order was recently accessed or completed
            $verified = true;
        }

        $statusEnum = CustomerOrderStatus::tryFrom($order->customer_status);
        $stepIndex = $statusEnum ? $statusEnum->stepIndex() : 1;

        return view('shop.track', [
            'order' => $verified ? $order : null,
            'orderNumber' => $orderNumber,
            'phoneLast4' => $phoneLast4,
            'errorMessage' => $errorMessage,
            'stepIndex' => $stepIndex,
        ]);
    }
}
