<?php

namespace App\Services;

use App\Enums\CustomerOrderStatus;
use App\Enums\InternalOrderStatus;
use App\Exceptions\SlotFullException;
use App\Exceptions\StockUnavailableException;
use App\Models\DeliveryZone;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PromoCode;
use App\Models\Variant;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function __construct(
        protected SlotAvailabilityService $slotService,
        protected PricingService $pricingService,
        protected OrderNumberGenerator $orderNumberGenerator
    ) {}

    /**
     * Place order in a single DB transaction:
     * - Re-calculate prices, stock, shipping, and discount purely from DB
     * - Lock & deduct variant stock with lockForUpdate()
     * - Atomically reserve slot quota with conditional UPDATE
     * - Create Order with 1-hour expiration
     *
     * @param  array{name: string, phone: string, email?: ?string}  $customerData
     * @param  array<int, array{variant_id: int, quantity: int, greeting_card?: ?string, fulfillment_date?: ?string, fulfillment_slot?: ?string}>  $cartItems
     *
     * @throws StockUnavailableException|SlotFullException
     */
    public function place(
        array $customerData,
        string $deliveryType,
        ?int $deliveryZoneId,
        ?string $deliveryAddress,
        string $fulfillmentDate,
        string $fulfillmentSlot,
        ?string $promoCodeString,
        ?string $customerNotes,
        array $cartItems
    ): Order {
        if (empty($cartItems)) {
            throw new \InvalidArgumentException('Keranjang belanja kosong.');
        }

        return DB::transaction(function () use (
            $customerData,
            $deliveryType,
            $deliveryZoneId,
            $deliveryAddress,
            $fulfillmentDate,
            $fulfillmentSlot,
            $promoCodeString,
            $customerNotes,
            $cartItems
        ) {
            $date = Carbon::parse($fulfillmentDate)->startOfDay();
            $now = Carbon::now('Asia/Makassar');

            // 1. Lock and validate variant stock & recompute items subtotal from DB
            $preparedItems = [];
            $maxLeadTimeDays = 1;
            $totalQuantity = 0;
            $subtotal = 0;

            foreach ($cartItems as $item) {
                $qty = max(1, (int) ($item['quantity'] ?? 1));
                $variantId = (int) $item['variant_id'];

                $variant = Variant::with('product')
                    ->where('id', $variantId)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($variant->stock < $qty) {
                    throw new StockUnavailableException(
                        "Stok untuk {$variant->product->name} ({$variant->name}) tidak mencukupi (sisa: {$variant->stock})."
                    );
                }

                // Deduct stock
                $variant->decrement('stock', $qty);

                $lineTotal = $variant->price * $qty;
                $subtotal += $lineTotal;
                $totalQuantity += $qty;

                if ($variant->product->lead_time_days > $maxLeadTimeDays) {
                    $maxLeadTimeDays = $variant->product->lead_time_days;
                }

                $preparedItems[] = [
                    'variant' => $variant,
                    'quantity' => $qty,
                    'price' => $variant->price,
                    'greeting_card' => ! empty($item['greeting_card']) ? substr(trim($item['greeting_card']), 0, 40) : null,
                ];
            }

            // 2. Validate lead time & cut-off for the fulfillment date
            if (! $this->slotService->isDateAllowed($date, $maxLeadTimeDays, $now)) {
                $suggested = $this->slotService->suggestNearestAvailableSlot($date, $fulfillmentSlot, $maxLeadTimeDays, $totalQuantity, $now);
                $earliest = $this->slotService->getEarliestAvailableDate($maxLeadTimeDays, $now);

                throw new SlotFullException(
                    "Tanggal {$fulfillmentDate} tidak memenuhi lead time H-{$maxLeadTimeDays} atau telah melewati batas cut-off 18.00. Jadwal paling awal adalah {$earliest->format('d/m/Y')}.",
                    422,
                    $suggested
                );
            }

            // 3. Atomically reserve slot quota with conditional UPDATE
            try {
                $this->slotService->reserveSlot($date, $fulfillmentSlot, $totalQuantity, $maxLeadTimeDays, $now);
            } catch (SlotFullException $e) {
                $suggested = $this->slotService->suggestNearestAvailableSlot($date, $fulfillmentSlot, $maxLeadTimeDays, $totalQuantity, $now);

                $msg = "Slot jam {$fulfillmentSlot} pada tanggal {$fulfillmentDate} sudah penuh.";
                if ($suggested) {
                    $msg .= " Slot terdekat yang tersedia adalah tanggal {$suggested['date']} jam {$suggested['time_slot']}.";
                }

                throw new SlotFullException($msg, 422, $suggested);
            }

            // 4. Calculate shipping fee purely from DeliveryZone in DB
            $zone = null;
            if ($deliveryType === 'delivery' && $deliveryZoneId) {
                $zone = DeliveryZone::where('is_active', true)->find($deliveryZoneId);
            }
            $shippingFee = $this->pricingService->calculateShippingFee($deliveryType, $zone);

            // 5. Calculate discount purely from PromoCode in DB
            $promo = null;
            if ($promoCodeString) {
                $promo = PromoCode::where('code', strtoupper(trim($promoCodeString)))
                    ->where('is_active', true)
                    ->first();
            }
            $discountAmount = $this->pricingService->calculateDiscount($promo, $subtotal);

            // 6. Final Clean Total
            $totalAmount = max(0, ($subtotal + $shippingFee) - $discountAmount);

            // 7. Order Number & Expiration (1 hour)
            $orderNumber = $this->orderNumberGenerator->generate($now);
            $expiresAt = $now->copy()->addHour();

            // 8. Create Order Record
            $order = Order::create([
                'order_number' => $orderNumber,
                'customer_name' => $customerData['name'],
                'customer_phone' => $customerData['phone'],
                'customer_email' => $customerData['email'] ?? null,
                'delivery_type' => $deliveryType,
                'delivery_address' => $deliveryType === 'delivery' ? $deliveryAddress : 'Ambil di Toko (Pinky Cake Makassar)',
                'delivery_zone_id' => $zone?->id,
                'subtotal' => $subtotal,
                'shipping_fee' => $shippingFee,
                'discount_amount' => $discountAmount,
                'total_amount' => $totalAmount,
                'internal_status' => InternalOrderStatus::ORDER_PLACED->value,
                'customer_status' => CustomerOrderStatus::RECEIVED->value,
                'fulfillment_date' => $fulfillmentDate,
                'fulfillment_slot' => $fulfillmentSlot,
                'customer_notes' => $customerNotes,
                'promo_code_id' => $promo?->id,
                'expires_at' => $expiresAt,
            ]);

            // 9. Create Order Items
            foreach ($preparedItems as $prep) {
                $order->items()->create([
                    'variant_id' => $prep['variant']->id,
                    'product_name' => $prep['variant']->product->name,
                    'variant_name' => $prep['variant']->name,
                    'price' => $prep['price'],
                    'quantity' => $prep['quantity'],
                    'greeting_card' => $prep['greeting_card'],
                    'fulfillment_date' => $fulfillmentDate,
                    'fulfillment_slot' => $fulfillmentSlot,
                ]);
            }

            // 10. Status Log
            $order->statusLogs()->create([
                'from_status' => null,
                'to_status' => InternalOrderStatus::ORDER_PLACED->value,
                'notes' => 'Pesanan berhasil dibuat, menunggu pembayaran (batas waktu 1 jam).',
                'changed_by' => 'customer',
            ]);

            return $order;
        });
    }

    /**
     * Cancel an order and release reserved stock and slot quota.
     */
    public function cancelAndRelease(Order $order, string $reason = 'Pesanan dibatalkan / kedaluwarsa'): void
    {
        if ($order->internal_status === InternalOrderStatus::CANCELLED->value) {
            return;
        }

        DB::transaction(function () use ($order, $reason) {
            // Lock order for update
            $order = Order::with('items.variant')->lockForUpdate()->find($order->id);

            // Re-increment stock
            $totalQty = 0;
            foreach ($order->items as $item) {
                if ($item->variant_id) {
                    Variant::where('id', $item->variant_id)->increment('stock', $item->quantity);
                }
                $totalQty += $item->quantity;
            }

            // Release slot quota
            $this->slotService->releaseSlot($order->fulfillment_date, $order->fulfillment_slot, $totalQty);

            // Update order status
            $order->updateInternalStatus(InternalOrderStatus::CANCELLED, $reason, 'system');
        });
    }

    /**
     * Lazy check: if order is expired and still in ORDER_PLACED status, cancel and release immediately.
     */
    public function checkAndExpireIfNeeded(Order $order): Order
    {
        if ($order->internal_status === InternalOrderStatus::ORDER_PLACED->value && $order->isExpired()) {
            $this->cancelAndRelease($order, 'Batas waktu pembayaran 1 jam terlewati (kedaluwarsa).');
            return $order->fresh();
        }

        return $order;
    }
}
