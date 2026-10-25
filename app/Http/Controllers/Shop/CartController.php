<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Variant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    /**
     * Display the shopping cart (Screen 04).
     */
    public function index(Request $request): View
    {
        $cart = session()->get('cart', []);
        $cartNotes = session()->get('cart_notes', '');

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

        // Recalculate prices and verify items from DB
        $subtotal = 0;
        $formattedItems = [];

        foreach ($cart as $key => $item) {
            $variant = Variant::with('product')->find($item['variant_id']);
            if (! $variant) {
                // Stale item, remove
                unset($cart[$key]);
                continue;
            }

            $currentPrice = $variant->price;
            $qty = (int) $item['quantity'];
            $lineTotal = $currentPrice * $qty;
            $subtotal += $lineTotal;

            $formattedItems[$key] = array_merge($item, [
                'price' => $currentPrice,
                'line_total' => $lineTotal,
                'product_slug' => $variant->product->slug,
                'image_url' => $variant->product->primary_image_url ?? null,
                'stock' => $variant->stock,
            ]);
        }

        // Keep session cart synchronized with any purged stale items
        session()->put('cart', $cart);
        session()->put('cart_count', array_sum(array_column($cart, 'quantity')));

        return view('shop.cart', [
            'cartItems' => $formattedItems,
            'subtotal' => $subtotal,
            'cartNotes' => $cartNotes,
        ]);
    }

    /**
     * Add item to session cart.
     */
    public function add(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'variant_id' => 'required|exists:variants,id',
            'quantity' => 'required|integer|min:1|max:99',
            'greeting_card' => 'nullable|string|max:40',
            'fulfillment_date' => 'required|date',
            'fulfillment_slot' => 'required|string',
        ]);

        $variant = Variant::with('product')->findOrFail($validated['variant_id']);

        $cart = session()->get('cart', []);
        $cartKey = $variant->id . '_' . $validated['fulfillment_date'] . '_' . $validated['fulfillment_slot'];

        if (isset($cart[$cartKey])) {
            $cart[$cartKey]['quantity'] += (int) $validated['quantity'];
            if (! empty($validated['greeting_card'])) {
                $cart[$cartKey]['greeting_card'] = $validated['greeting_card'];
            }
        } else {
            $cart[$cartKey] = [
                'variant_id' => $variant->id,
                'product_id' => $variant->product_id,
                'product_name' => $variant->product->name,
                'variant_name' => $variant->name,
                'price' => $variant->price,
                'quantity' => (int) $validated['quantity'],
                'greeting_card' => ! empty($validated['greeting_card']) ? substr(trim($validated['greeting_card']), 0, 40) : null,
                'fulfillment_date' => $validated['fulfillment_date'],
                'fulfillment_slot' => $validated['fulfillment_slot'],
            ];
        }

        session()->put('cart', $cart);

        $totalItems = array_sum(array_column($cart, 'quantity'));
        session()->put('cart_count', $totalItems);

        return redirect()->route('cart.index')->with('success', "{$variant->product->name} ({$variant->name}) berhasil ditambahkan ke keranjang!");
    }

    /**
     * Update quantity of an item in the cart.
     */
    public function update(Request $request, string $key): RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1|max:99',
        ]);

        $cart = session()->get('cart', []);

        if (isset($cart[$key])) {
            $cart[$key]['quantity'] = (int) $validated['quantity'];
            session()->put('cart', $cart);
            session()->put('cart_count', array_sum(array_column($cart, 'quantity')));
        }

        return redirect()->route('cart.index')->with('success', 'Jumlah pesanan berhasil diperbarui.');
    }

    /**
     * Remove an item from the cart.
     */
    public function remove(string $key): RedirectResponse
    {
        $cart = session()->get('cart', []);

        if (isset($cart[$key])) {
            $name = $cart[$key]['product_name'] ?? 'Item';
            unset($cart[$key]);
            session()->put('cart', $cart);
            session()->put('cart_count', array_sum(array_column($cart, 'quantity')));

            return redirect()->route('cart.index')->with('success', "{$name} telah dihapus dari keranjang.");
        }

        return redirect()->route('cart.index');
    }

    /**
     * Save customer order notes to session.
     */
    public function updateNotes(Request $request): RedirectResponse
    {
        $notes = $request->input('notes', '');
        session()->put('cart_notes', substr(trim($notes), 0, 500));

        return redirect()->route('cart.index')->with('success', 'Catatan pesanan berhasil disimpan.');
    }
}
