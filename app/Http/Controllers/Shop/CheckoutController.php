<?php

namespace App\Http\Controllers\Shop;

use App\Exceptions\SlotFullException;
use App\Exceptions\StockUnavailableException;
use App\Http\Controllers\Controller;
use App\Http\Requests\CheckoutRequest;
use App\Models\DeliveryZone;
use App\Models\Variant;
use App\Services\OrderService;
use App\Services\SlotAvailabilityService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    /**
     * Display Checkout page (Screen 05).
     */
    public function index(Request $request, SlotAvailabilityService $slotService): View|RedirectResponse
    {
        $cart = session()->get('cart', []);

        // Demo seed for visual verification in local development
        if ($request->query('demo') == '1' && app()->isLocal() && empty($cart)) {
            $scVariant = Variant::with('product')->whereHas('product', fn($q) => $q->where('slug', 'strawberry-cream-cake'))->first();
            if ($scVariant) {
                $cartKey = $scVariant->id . '_' . now()->addDays(2)->format('Y-m-d') . '_10:00';
                $cart[$cartKey] = [
                    'variant_id' => $scVariant->id,
                    'product_id' => $scVariant->product_id,
                    'product_name' => $scVariant->product->name,
                    'variant_name' => $scVariant->name,
                    'price' => $scVariant->price,
                    'quantity' => 1,
                    'greeting_card' => 'Selamat Ulang Tahun!',
                    'fulfillment_date' => now()->addDays(2)->format('Y-m-d'),
                    'fulfillment_slot' => '10:00',
                ];
                session()->put('cart', $cart);
                session()->put('cart_count', 1);
            }
        }

        if (empty($cart)) {
            return redirect()->route('cart.index')->with('warning', 'Keranjang belanja Anda masih kosong.');
        }

        // Fresh DB lookup for items & calculate subtotal strictly from DB
        $formattedItems = [];
        $subtotal = 0;
        $maxLeadTimeDays = 1;

        foreach ($cart as $key => $item) {
            $variant = Variant::with('product')->find($item['variant_id']);
            if (! $variant) {
                unset($cart[$key]);
                continue;
            }

            $currentPrice = $variant->price;
            $qty = (int) $item['quantity'];
            $subtotal += ($currentPrice * $qty);

            if ($variant->product->lead_time_days > $maxLeadTimeDays) {
                $maxLeadTimeDays = $variant->product->lead_time_days;
            }

            $formattedItems[$key] = array_merge($item, [
                'price' => $currentPrice,
                'line_total' => $currentPrice * $qty,
                'image_url' => $variant->product->primary_image_url ?? null,
            ]);
        }

        if (empty($formattedItems)) {
            session()->forget('cart');
            session()->forget('cart_count');

            return redirect()->route('cart.index')->with('warning', 'Item dalam keranjang Anda tidak lagi tersedia.');
        }

        // Active delivery zones
        $deliveryZones = DeliveryZone::where('is_active', true)->orderBy('fee')->get();

        // Prefill default date & slot from the first cart item or earliest possible
        $firstItem = reset($formattedItems);
        $defaultDate = $firstItem['fulfillment_date'] ?? $slotService->getEarliestAvailableDate($maxLeadTimeDays)->format('Y-m-d');
        $defaultSlot = $firstItem['fulfillment_slot'] ?? '10:00';
        $cartNotes = session()->get('cart_notes', '');

        // Earliest date string for min date picker
        $earliestDate = $slotService->getEarliestAvailableDate($maxLeadTimeDays)->format('Y-m-d');

        return view('shop.checkout', [
            'cartItems' => $formattedItems,
            'subtotal' => $subtotal,
            'deliveryZones' => $deliveryZones,
            'defaultDate' => $defaultDate,
            'defaultSlot' => $defaultSlot,
            'earliestDate' => $earliestDate,
            'maxLeadTimeDays' => $maxLeadTimeDays,
            'cartNotes' => $cartNotes,
        ]);
    }

    /**
     * Process checkout submission and create real Order in DB.
     */
    public function store(CheckoutRequest $request, OrderService $orderService): RedirectResponse
    {
        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return redirect()->route('cart.index')->with('warning', 'Keranjang belanja Anda kosong.');
        }

        $cartItems = array_map(function ($item) {
            return [
                'variant_id' => (int) $item['variant_id'],
                'quantity' => (int) $item['quantity'],
                'greeting_card' => $item['greeting_card'] ?? null,
                'fulfillment_date' => $item['fulfillment_date'] ?? null,
                'fulfillment_slot' => $item['fulfillment_slot'] ?? null,
            ];
        }, array_values($cart));

        try {
            $order = $orderService->place(
                customerData: [
                    'name' => (string) $request->validated('customer_name'),
                    'phone' => (string) $request->validated('customer_phone'),
                    'email' => $request->validated('customer_email'),
                ],
                deliveryType: (string) $request->validated('delivery_type'),
                deliveryZoneId: $request->validated('delivery_zone_id') ? (int) $request->validated('delivery_zone_id') : null,
                deliveryAddress: $request->validated('delivery_address'),
                fulfillmentDate: (string) $request->validated('fulfillment_date'),
                fulfillmentSlot: (string) $request->validated('fulfillment_slot'),
                promoCodeString: $request->validated('promo_code'),
                customerNotes: $request->validated('customer_notes') ?? session()->get('cart_notes'),
                cartItems: $cartItems
            );

            // Clear session cart on successful order placement
            session()->forget('cart');
            session()->forget('cart_count');
            session()->forget('cart_notes');

            return redirect()->route('payment.show', ['orderNumber' => $order->order_number]);
        } catch (StockUnavailableException|SlotFullException $e) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['fulfillment' => $e->getMessage()]);
        } catch (\Throwable $e) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['checkout' => 'Gagal memproses pesanan: ' . $e->getMessage()]);
        }
    }
}
