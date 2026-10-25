<x-shop.layout title="Checkout — Klik.Cake Makassar">
<div class="bg-cream min-h-screen py-8 md:py-12" x-data="{
    deliveryType: '{{ old('delivery_type', 'delivery') }}',
    selectedZoneId: '{{ old('delivery_zone_id', $deliveryZones->first()->id ?? '') }}',
    zones: @js($deliveryZones->keyBy('id')),
    subtotal: {{ $subtotal }},
    promoCode: '{{ old('promo_code', '') }}',
    promoDiscount: 0,
    promoMessage: '',
    
    get shippingFee() {
        if (this.deliveryType === 'pickup') return 0;
        if (!this.selectedZoneId || !this.zones[this.selectedZoneId]) return 0;
        return parseInt(this.zones[this.selectedZoneId].fee);
    },
    
    get totalAmount() {
        return Math.max(0, (this.subtotal + this.shippingFee) - this.promoDiscount);
    },
    
    formatRupiah(num) {
        return 'Rp' + new Intl.NumberFormat('id-ID').format(num);
    },

    applyPromo() {
        const code = this.promoCode.trim().toUpperCase();
        if (code === 'KLIKMANIS') {
            this.promoDiscount = Math.round(this.subtotal * 0.10);
            this.promoMessage = 'Kode promo KLIKMANIS (Diskon 10%) diterapkan!';
        } else if (code === 'FRESHBAKE') {
            this.promoDiscount = 20000;
            this.promoMessage = 'Kode promo FRESHBAKE (Potongan Rp20.000) diterapkan!';
        } else if (code === '') {
            this.promoDiscount = 0;
            this.promoMessage = '';
        } else {
            this.promoDiscount = 0;
            this.promoMessage = 'Kode promo tidak valid atau telah berakhir.';
        }
    }
}">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        {{-- Breadcrumb --}}
        <nav class="flex items-center gap-2 text-xs md:text-sm text-cocoa-muted mb-6">
            <a href="{{ route('home') }}" class="hover:text-strawberry transition-colors">Beranda</a>
            <span>/</span>
            <a href="{{ route('cart.index') }}" class="hover:text-strawberry transition-colors">Keranjang</a>
            <span>/</span>
            <span class="text-cocoa font-semibold">Checkout</span>
        </nav>

        {{-- Validation Errors --}}
        @if($errors->any())
            <div class="mb-6 p-4 rounded-2xl bg-red-50 border border-red-200 text-red-700 shadow-sm">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-red-500 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div>
                        <h4 class="font-bold text-sm">Mohon periksa kembali formulir Anda:</h4>
                        <ul class="list-disc list-inside text-xs mt-1 space-y-0.5">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <div class="border-b border-blush pb-4 mb-8">
            <span class="text-xs uppercase tracking-wider font-bold text-strawberry">Langkah Terakhir</span>
            <h1 class="text-3xl md:text-4xl font-serif font-black text-cocoa mt-1">Konfirmasi & Checkout</h1>
        </div>

        <form action="{{ route('checkout.store') }}" method="POST">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                
                {{-- Left Column: Form Details --}}
                <div class="lg:col-span-8 space-y-8">
                    
                    {{-- 1. Contact Information --}}
                    <div class="bg-white rounded-3xl p-6 md:p-8 shadow-card border border-blush">
                        <div class="flex items-center gap-3 mb-6">
                            <span class="w-8 h-8 rounded-full bg-blush text-strawberry-deep font-bold flex items-center justify-center text-sm">1</span>
                            <h2 class="text-xl font-serif font-bold text-cocoa">Informasi Pemesan</h2>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="customer_name" class="block text-xs font-bold text-cocoa mb-1.5">Nama Lengkap <span class="text-strawberry">*</span></label>
                                <input type="text" id="customer_name" name="customer_name" value="{{ old('customer_name') }}" required placeholder="Contoh: Nurul Aisyah" class="w-full rounded-2xl border-blush focus:border-strawberry focus:ring-strawberry text-sm text-cocoa bg-cream/30 p-3">
                                @error('customer_name')
                                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="customer_phone" class="block text-xs font-bold text-cocoa mb-1.5">Nomor WhatsApp Aktif <span class="text-strawberry">*</span></label>
                                <input type="tel" id="customer_phone" name="customer_phone" value="{{ old('customer_phone') }}" required placeholder="08xxxxxxxxxx" class="w-full rounded-2xl border-blush focus:border-strawberry focus:ring-strawberry text-sm text-cocoa bg-cream/30 p-3">
                                <p class="text-[11px] text-cocoa-muted mt-1">Untuk konfirmasi pesanan & tracking via WhatsApp.</p>
                                @error('customer_phone')
                                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="md:col-span-2">
                                <label for="customer_email" class="block text-xs font-bold text-cocoa mb-1.5">Alamat Email (Opsional)</label>
                                <input type="email" id="customer_email" name="customer_email" value="{{ old('customer_email') }}" placeholder="email@contoh.com" class="w-full rounded-2xl border-blush focus:border-strawberry focus:ring-strawberry text-sm text-cocoa bg-cream/30 p-3">
                                @error('customer_email')
                                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- 2. Delivery Method --}}
                    <div class="bg-white rounded-3xl p-6 md:p-8 shadow-card border border-blush">
                        <div class="flex items-center gap-3 mb-6">
                            <span class="w-8 h-8 rounded-full bg-blush text-strawberry-deep font-bold flex items-center justify-center text-sm">2</span>
                            <h2 class="text-xl font-serif font-bold text-cocoa">Metode Pengantaran</h2>
                        </div>

                        {{-- Toggle Pills --}}
                        <div class="grid grid-cols-2 gap-3 p-1.5 bg-cream rounded-2xl border border-blush mb-6">
                            <button type="button" @click="deliveryType = 'delivery'" :class="deliveryType === 'delivery' ? 'bg-strawberry-deep text-white shadow-sm font-bold' : 'text-cocoa font-semibold hover:text-strawberry'" class="py-3 px-4 rounded-xl text-sm transition-all flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                                </svg>
                                <span>Delivery (Kurir)</span>
                            </button>
                            
                            <button type="button" @click="deliveryType = 'pickup'" :class="deliveryType === 'pickup' ? 'bg-strawberry-deep text-white shadow-sm font-bold' : 'text-cocoa font-semibold hover:text-strawberry'" class="py-3 px-4 rounded-xl text-sm transition-all flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                                <span>Pick-up (Ambil Sendiri)</span>
                            </button>
                        </div>

                        <input type="hidden" name="delivery_type" :value="deliveryType">

                        {{-- Delivery Fields --}}
                        <div x-show="deliveryType === 'delivery'" class="space-y-4">
                            <div>
                                <label for="delivery_zone_id" class="block text-xs font-bold text-cocoa mb-1.5">Wilayah / Zona Pengantaran <span class="text-strawberry">*</span></label>
                                <select id="delivery_zone_id" name="delivery_zone_id" x-model="selectedZoneId" class="w-full rounded-2xl border-blush focus:border-strawberry focus:ring-strawberry text-sm text-cocoa bg-cream/30 p-3">
                                    <option value="">-- Pilih Zona Pengantaran Makassar --</option>
                                    @foreach($deliveryZones as $zone)
                                        <option value="{{ $zone->id }}" {{ old('delivery_zone_id', $deliveryZones->first()->id ?? '') == $zone->id ? 'selected' : '' }}>
                                            {{ $zone->name }} (Rp{{ number_format($zone->fee, 0, ',', '.') }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('delivery_zone_id')
                                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="delivery_address" class="block text-xs font-bold text-cocoa mb-1.5">Alamat Pengantaran Lengkap <span class="text-strawberry">*</span></label>
                                <textarea id="delivery_address" name="delivery_address" rows="3" placeholder="Nama Jalan, Nomor Rumah, RT/RW, Patokan atau Titik Antar" class="w-full rounded-2xl border-blush focus:border-strawberry focus:ring-strawberry text-sm text-cocoa bg-cream/30 p-3">{{ old('delivery_address') }}</textarea>
                                @error('delivery_address')
                                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        {{-- Pick-up Info --}}
                        <div x-show="deliveryType === 'pickup'" x-cloak class="p-4 rounded-2xl bg-cream border border-blush flex items-start gap-3">
                            <div class="w-10 h-10 rounded-xl bg-blush flex items-center justify-center text-strawberry flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                            <div class="text-xs text-cocoa">
                                <h4 class="font-bold text-sm text-cocoa mb-0.5">Ambil Langsung di Outlet Pinky Cake</h4>
                                <p class="text-cocoa-muted leading-relaxed">
                                    Jl. Botolempangan No. 42, Makassar.<br>
                                    Pesanan akan disiapkan hangat sesuai slot waktu yang Anda tentukan di bawah. Bebas biaya antar.
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- 3. Fulfillment Schedule --}}
                    <div class="bg-white rounded-3xl p-6 md:p-8 shadow-card border border-blush">
                        <div class="flex items-center gap-3 mb-6">
                            <span class="w-8 h-8 rounded-full bg-blush text-strawberry-deep font-bold flex items-center justify-center text-sm">3</span>
                            <h2 class="text-xl font-serif font-bold text-cocoa">Jadwal Fresh Bake</h2>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="fulfillment_date" class="block text-xs font-bold text-cocoa mb-1.5">
                                    Tanggal Panggang & Kirim <span class="text-strawberry">*</span>
                                </label>
                                <input type="date" id="fulfillment_date" name="fulfillment_date" value="{{ old('fulfillment_date', $defaultDate) }}" min="{{ $earliestDate }}" required class="w-full rounded-2xl border-blush focus:border-strawberry focus:ring-strawberry text-sm text-cocoa bg-cream/30 p-3">
                                <p class="text-[11px] text-cocoa-muted mt-1">Lead time minimal H-{{ $maxLeadTimeDays }} (cut-off 18.00 WITA).</p>
                                @error('fulfillment_date')
                                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-cocoa mb-1.5">
                                    Slot Waktu <span class="text-strawberry">*</span>
                                </label>
                                <div class="grid grid-cols-3 gap-2">
                                    @foreach(['10:00' => '10.00', '13:00' => '13.00', '16:00' => '16.00'] as $timeVal => $timeLabel)
                                        <label class="cursor-pointer">
                                            <input type="radio" name="fulfillment_slot" value="{{ $timeVal }}" {{ old('fulfillment_slot', $defaultSlot) == $timeVal ? 'checked' : '' }} class="peer sr-only">
                                            <div class="p-2.5 rounded-xl border border-blush text-center text-xs font-bold text-cocoa bg-cream/40 peer-checked:bg-strawberry-deep peer-checked:text-white peer-checked:border-strawberry-deep transition-all">
                                                {{ $timeLabel }} WITA
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                                @error('fulfillment_slot')
                                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- 4. Voucher & Catatan --}}
                    <div class="bg-white rounded-3xl p-6 md:p-8 shadow-card border border-blush space-y-4">
                        <div>
                            <label for="promo_code" class="block text-xs font-bold text-cocoa mb-1.5">Voucher / Kode Promo</label>
                            <div class="flex gap-2">
                                <input type="text" id="promo_code" name="promo_code" x-model="promoCode" placeholder="Contoh: KLIKMANIS" class="uppercase flex-1 rounded-2xl border-blush focus:border-strawberry focus:ring-strawberry text-sm text-cocoa bg-cream/30 p-3">
                                <button type="button" @click="applyPromo()" class="px-5 py-3 rounded-2xl bg-cocoa text-white font-bold text-xs hover:bg-cocoa-light transition-colors">
                                    Pakai
                                </button>
                            </div>
                            <p x-show="promoMessage" x-text="promoMessage" :class="promoDiscount > 0 ? 'text-green-600 font-bold' : 'text-red-500'" class="text-xs mt-1.5"></p>
                        </div>

                        <div>
                            <label for="customer_notes" class="block text-xs font-bold text-cocoa mb-1.5">Catatan Tambahan (Opsional)</label>
                            <textarea id="customer_notes" name="customer_notes" rows="2" placeholder="Instruksi pengantaran atau preferensi pesanan..." class="w-full rounded-2xl border-blush focus:border-strawberry focus:ring-strawberry text-sm text-cocoa bg-cream/30 p-3">{{ old('customer_notes', $cartNotes) }}</textarea>
                        </div>
                    </div>

                </div>

                {{-- Right Column: Order Summary --}}
                <div class="lg:col-span-4 sticky top-28 space-y-6">
                    <div class="bg-white rounded-3xl p-6 md:p-8 shadow-card border border-blush">
                        <h3 class="font-serif font-bold text-xl text-cocoa border-b border-blush pb-4 mb-4">
                            Ringkasan Pesanan
                        </h3>

                        {{-- Items Preview --}}
                        <div class="space-y-3 max-h-56 overflow-y-auto pr-1 border-b border-blush pb-4 mb-4">
                            @foreach($cartItems as $item)
                                <div class="flex items-center justify-between text-xs">
                                    <div class="pr-2">
                                        <div class="font-bold text-cocoa line-clamp-1">{{ $item['product_name'] }}</div>
                                        <div class="text-cocoa-muted text-[11px]">{{ $item['variant_name'] }} &times; {{ $item['quantity'] }}</div>
                                        @if(!empty($item['greeting_card']))
                                            <div class="text-[10px] text-strawberry italic">"{{ $item['greeting_card'] }}"</div>
                                        @endif
                                    </div>
                                    <span class="font-bold text-cocoa whitespace-nowrap">Rp{{ number_format($item['line_total'], 0, ',', '.') }}</span>
                                </div>
                            @endforeach
                        </div>

                        {{-- Price Calculation --}}
                        <div class="space-y-2.5 text-sm">
                            <div class="flex justify-between items-center text-cocoa-muted">
                                <span>Subtotal</span>
                                <span class="font-bold text-cocoa">Rp{{ number_format($subtotal, 0, ',', '.') }}</span>
                            </div>

                            <div class="flex justify-between items-center text-cocoa-muted">
                                <span>Ongkos Kirim</span>
                                <span class="font-bold text-cocoa" x-text="formatRupiah(shippingFee)"></span>
                            </div>

                            <div x-show="promoDiscount > 0" class="flex justify-between items-center text-green-600">
                                <span>Diskon Promo</span>
                                <span class="font-bold" x-text="'-' + formatRupiah(promoDiscount)"></span>
                            </div>

                            <div class="border-t border-blush pt-3 mt-3 flex justify-between items-baseline">
                                <span class="font-serif font-bold text-base text-cocoa">Total Bayar</span>
                                <span class="font-serif font-black text-2xl text-strawberry-deep" x-text="formatRupiah(totalAmount)"></span>
                            </div>
                        </div>

                        {{-- Submit Button --}}
                        <div class="mt-6 pt-2">
                            <button type="submit" class="w-full flex items-center justify-center gap-2 px-6 py-4 rounded-full bg-strawberry-deep text-white font-bold hover:bg-strawberry transition-all shadow-md transform hover:-translate-y-0.5 text-center text-base">
                                <span>Lanjut ke Pembayaran</span>
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                            </button>
                        </div>

                        <div class="mt-4 text-center">
                            <span class="text-[11px] text-cocoa-muted">
                                Dengan menekan tombol di atas, pesanan Anda akan dibuat dan kuota slot akan ditahan selama 1 jam.
                            </span>
                        </div>
                    </div>
                </div>

            </div>
        </form>

    </div>
</div>
</x-shop.layout>
