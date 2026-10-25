<x-shop.layout title="Keranjang Belanja — Klik.Cake Makassar">
<div class="bg-cream min-h-screen py-8 md:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        {{-- Breadcrumb --}}
        <nav class="flex items-center gap-2 text-xs md:text-sm text-cocoa-muted mb-6">
            <a href="{{ route('home') }}" class="hover:text-strawberry transition-colors">Beranda</a>
            <span>/</span>
            <span class="text-cocoa font-semibold">Keranjang Belanja</span>
        </nav>

        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="mb-6 p-4 rounded-2xl bg-blush border border-strawberry/20 text-strawberry-deep font-bold flex items-center justify-between shadow-sm animate-fade-in">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-strawberry flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
        @endif

        <div class="flex flex-col md:flex-row md:items-end justify-between mb-8 gap-4 border-b border-blush pb-4">
            <div>
                <span class="text-xs uppercase tracking-wider font-bold text-strawberry">Pesanan Kamu</span>
                <h1 class="text-3xl md:text-4xl font-serif font-black text-cocoa mt-1">Keranjang Belanja</h1>
            </div>
            <p class="text-sm text-cocoa-muted font-medium">
                {{ count($cartItems) }} jenis produk di keranjang
            </p>
        </div>

        @if(empty($cartItems))
            {{-- Empty Cart State --}}
            <div class="bg-white rounded-3xl p-10 md:p-16 text-center shadow-card border border-blush max-w-2xl mx-auto my-12">
                <div class="w-24 h-24 mx-auto mb-6 rounded-full bg-blush flex items-center justify-center text-strawberry">
                    <svg class="w-12 h-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                </div>
                <h2 class="text-2xl md:text-3xl font-serif font-bold text-cocoa mb-3">Keranjangmu Masih Kosong</h2>
                <p class="text-cocoa-muted text-base max-w-md mx-auto mb-8">
                    Yuk pilih kue atau roti favoritmu yang baru dipanggang hangat untuk dinikmati bersama keluarga tercinta.
                </p>
                <a href="{{ route('catalog.index') }}" class="inline-flex items-center justify-center gap-2 px-8 py-3.5 rounded-full bg-strawberry-deep text-white font-bold hover:bg-strawberry transition-all shadow-md transform hover:-translate-y-0.5">
                    <span>Mulai Belanja</span>
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </a>
            </div>
        @else
            {{-- Cart Grid --}}
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                
                {{-- Left Column: Items List & Notes --}}
                <div class="lg:col-span-8 space-y-6">
                    <div class="bg-white rounded-3xl p-6 md:p-8 shadow-card border border-blush space-y-6">
                        @foreach($cartItems as $key => $item)
                            <div class="flex flex-col sm:flex-row gap-4 sm:gap-6 pb-6 {{ ! $loop->last ? 'border-b border-blush' : '' }}">
                                {{-- Thumbnail --}}
                                <div class="w-24 h-24 sm:w-28 sm:h-28 rounded-2xl overflow-hidden bg-blush/40 flex-shrink-0 border border-blush">
                                    @if(! empty($item['image_url']))
                                        <img src="{{ $item['image_url'] }}" alt="{{ $item['product_name'] }}" class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-strawberry/40">
                                            <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                    @endif
                                </div>

                                {{-- Details --}}
                                <div class="flex-1 flex flex-col justify-between">
                                    <div>
                                        <div class="flex items-start justify-between gap-2">
                                            <div>
                                                <a href="{{ route('product.show', $item['product_slug'] ?? 'cake') }}" class="font-serif font-bold text-lg md:text-xl text-cocoa hover:text-strawberry transition-colors">
                                                    {{ $item['product_name'] }}
                                                </a>
                                                <p class="text-sm font-semibold text-cocoa-muted mt-0.5">
                                                    Varian: <span class="text-cocoa">{{ $item['variant_name'] }}</span>
                                                </p>
                                            </div>
                                            
                                            {{-- Delete Button Form --}}
                                            <form action="{{ route('cart.remove', $key) }}" method="POST" onsubmit="return confirm('Hapus produk ini dari keranjang?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-2 text-cocoa-muted/60 hover:text-strawberry transition-colors rounded-full hover:bg-blush" title="Hapus item">
                                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>

                                        {{-- Schedule & Slot Badge --}}
                                        <div class="mt-2.5 flex flex-wrap items-center gap-2 text-xs">
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-blush text-strawberry-deep font-bold">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                                {{ \Carbon\Carbon::parse($item['fulfillment_date'])->locale('id')->translatedFormat('d M Y') }}
                                            </span>
                                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-cream border border-blush text-cocoa font-semibold">
                                                Slot {{ $item['fulfillment_slot'] }} WITA
                                            </span>
                                        </div>

                                        {{-- Greeting Card Note --}}
                                        @if(! empty($item['greeting_card']))
                                            <div class="mt-2.5 text-xs text-cocoa-muted bg-cream/70 border border-blush rounded-xl px-3 py-1.5 flex items-center gap-2">
                                                <svg class="w-4 h-4 text-strawberry flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                                </svg>
                                                <span>Ucapan: <strong class="text-cocoa italic">"{{ $item['greeting_card'] }}"</strong></span>
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Price and Qty Stepper --}}
                                    <div class="mt-4 pt-3 flex flex-wrap items-center justify-between gap-4">
                                        <div class="text-cocoa">
                                            <span class="text-xs text-cocoa-muted block">Harga Satuan</span>
                                            <span class="font-bold text-base text-strawberry-deep">Rp{{ number_format($item['price'], 0, ',', '.') }}</span>
                                        </div>

                                        {{-- Stepper Form --}}
                                        <form action="{{ route('cart.update', $key) }}" method="POST" class="flex items-center gap-2" x-data="{ qty: {{ $item['quantity'] }} }">
                                            @csrf
                                            @method('PATCH')
                                            <div class="flex items-center border border-blush-dark rounded-full bg-cream overflow-hidden">
                                                <button type="button" @click="if (qty > 1) { qty--; $el.closest('form').submit(); }" class="w-8 h-8 flex items-center justify-center text-cocoa hover:bg-blush font-bold transition-colors">
                                                    -
                                                </button>
                                                <input type="number" name="quantity" x-model="qty" readonly class="w-10 text-center font-bold text-sm bg-transparent border-none p-0 focus:ring-0 text-cocoa">
                                                <button type="button" @click="qty++; $el.closest('form').submit();" class="w-8 h-8 flex items-center justify-center text-cocoa hover:bg-blush font-bold transition-colors">
                                                    +
                                                </button>
                                            </div>
                                            <div class="text-right ml-4">
                                                <span class="text-xs text-cocoa-muted block">Subtotal</span>
                                                <span class="font-extrabold text-base md:text-lg text-cocoa">Rp{{ number_format($item['line_total'], 0, ',', '.') }}</span>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Order Notes Card --}}
                    <div class="bg-white rounded-3xl p-6 md:p-8 shadow-card border border-blush">
                        <h3 class="font-serif font-bold text-lg text-cocoa mb-2 flex items-center gap-2">
                            <svg class="w-5 h-5 text-strawberry" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            Catatan Pesanan
                        </h3>
                        <p class="text-xs text-cocoa-muted mb-4">Tambahkan instruksi khusus untuk kitchen atau pengemasan (opsional, maks 500 karakter).</p>
                        <form action="{{ route('cart.notes') }}" method="POST">
                            @csrf
                            <textarea name="notes" rows="3" maxlength="500" placeholder="Contoh: Tolong sertakan lilin angka 25, dan jangan menggunakan pisau plastik." class="w-full rounded-2xl border-blush focus:border-strawberry focus:ring-strawberry text-sm text-cocoa bg-cream/40 p-3.5">{{ old('notes', $cartNotes) }}</textarea>
                            <div class="mt-3 flex justify-end">
                                <button type="submit" class="px-5 py-2 rounded-full border border-strawberry text-strawberry-deep font-bold text-xs hover:bg-strawberry hover:text-white transition-colors">
                                    Simpan Catatan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Right Column: Summary & Checkout Button --}}
                <div class="lg:col-span-4 sticky top-28 space-y-6">
                    <div class="bg-white rounded-3xl p-6 md:p-8 shadow-card border border-blush">
                        <h3 class="font-serif font-bold text-xl text-cocoa border-b border-blush pb-4 mb-4">
                            Ringkasan Belanja
                        </h3>

                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between items-center text-cocoa-muted">
                                <span>Subtotal Produk</span>
                                <span class="font-bold text-cocoa">Rp{{ number_format($subtotal, 0, ',', '.') }}</span>
                            </div>

                            <div class="flex justify-between items-center text-cocoa-muted">
                                <span>Ongkos Kirim</span>
                                <span class="text-xs italic text-strawberry-deep font-semibold">Dihitung di checkout</span>
                            </div>

                            <div class="border-t border-blush pt-3 mt-3 flex justify-between items-baseline">
                                <span class="font-serif font-bold text-base text-cocoa">Perkiraan Total</span>
                                <span class="font-serif font-black text-2xl text-strawberry-deep">Rp{{ number_format($subtotal, 0, ',', '.') }}</span>
                            </div>
                        </div>

                        <div class="mt-6 pt-2">
                            <a href="{{ route('checkout.index') }}" class="w-full flex items-center justify-center gap-2 px-6 py-4 rounded-full bg-strawberry-deep text-white font-bold hover:bg-strawberry transition-all shadow-md transform hover:-translate-y-0.5 text-center">
                                <span>Lanjut ke Checkout</span>
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                            </a>
                        </div>

                        <div class="mt-6 pt-6 border-t border-blush space-y-3 text-xs text-cocoa-muted">
                            <div class="flex items-center gap-2.5">
                                <svg class="w-4 h-4 text-butter flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                <span>Dipanggang segar sesuai tanggal & slot pilihanmu</span>
                            </div>
                            <div class="flex items-center gap-2.5">
                                <svg class="w-4 h-4 text-butter flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                <span>Bahan premium 100% tanpa pengawet</span>
                            </div>
                            <div class="flex items-center gap-2.5">
                                <svg class="w-4 h-4 text-butter flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                <span>Pembayaran QRIS & Transfer instan terverifikasi</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        @endif

    </div>
</div>
</x-shop.layout>
