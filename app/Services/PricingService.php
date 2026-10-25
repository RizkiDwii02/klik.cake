<?php

namespace App\Services;

use App\Models\DeliveryZone;
use App\Models\PromoCode;

class PricingService
{
    /**
     * Calculate subtotal from an array of order items or carts.
     * Each item is expected to have 'price' and 'quantity'.
     *
     * @param  array<int, array{price: int, quantity: int}>  $items
     */
    public function calculateSubtotal(array $items): int
    {
        $subtotal = 0;
        foreach ($items as $item) {
            $price = (int) ($item['price'] ?? 0);
            $qty = (int) ($item['quantity'] ?? 1);
            $subtotal += ($price * $qty);
        }

        return $subtotal;
    }

    /**
     * Calculate shipping fee based on delivery type and selected DeliveryZone.
     */
    public function calculateShippingFee(string $deliveryType, ?DeliveryZone $zone = null): int
    {
        if ($deliveryType === 'pickup') {
            return 0;
        }

        if (! $zone || ! $zone->is_active) {
            return 0;
        }

        return (int) $zone->fee;
    }

    /**
     * Calculate discount amount using PromoCode and subtotal.
     */
    public function calculateDiscount(?PromoCode $promoCode, int $subtotal): int
    {
        if (! $promoCode) {
            return 0;
        }

        return $promoCode->calculateDiscount($subtotal);
    }

    /**
     * Calculate entire order pricing summary.
     *
     * @param  array<int, array{price: int, quantity: int}>  $items
     * @return array{
     *     subtotal: int,
     *     shipping_fee: int,
     *     discount_amount: int,
     *     total: int,
     *     formatted_subtotal: string,
     *     formatted_shipping_fee: string,
     *     formatted_discount: string,
     *     formatted_total: string
     * }
     */
    public function calculateOrderSummary(
        array $items,
        string $deliveryType = 'delivery',
        ?DeliveryZone $zone = null,
        ?PromoCode $promoCode = null
    ): array {
        $subtotal = $this->calculateSubtotal($items);
        $shippingFee = $this->calculateShippingFee($deliveryType, $zone);
        $discountAmount = $this->calculateDiscount($promoCode, $subtotal);
        $total = max(0, ($subtotal + $shippingFee) - $discountAmount);

        return [
            'subtotal' => $subtotal,
            'shipping_fee' => $shippingFee,
            'discount_amount' => $discountAmount,
            'total' => $total,
            'formatted_subtotal' => 'Rp' . number_format($subtotal, 0, ',', '.'),
            'formatted_shipping_fee' => 'Rp' . number_format($shippingFee, 0, ',', '.'),
            'formatted_discount' => 'Rp' . number_format($discountAmount, 0, ',', '.'),
            'formatted_total' => 'Rp' . number_format($total, 0, ',', '.'),
        ];
    }
}
