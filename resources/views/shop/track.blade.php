<x-shop.layout :title="$order ? 'Lacak Pesanan #' . $order->order_number . ' — Klik.Cake Makassar' : 'Lacak Pesanan — Klik.Cake Makassar'">
<div class="bg-cream min-h-screen py-8 md:py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6">

        {{-- Breadcrumb --}}
        <nav class="flex items-center gap-2 text-xs md:text-sm text-cocoa-muted mb-6">
            <a href="{{ route('home') }}" class="hover:text-strawberry transition-colors">Beranda</a>
            <span>/</span>
            <span class="text-cocoa font-semibold">Lacak Pesanan</span>
        </nav>

        {{-- Search Form Box --}}
        <div class="bg-white rounded-3xl p-6 md:p-8 shadow-card border border-blush mb-8">
            <div class="text-center max-w-lg mx-auto mb-6">
                <span class="text-xs uppercase tracking-wider font-bold text-strawberry">Status Terkini</span>
                <h1 class="text-2xl md:text-3xl font-serif font-black text-cocoa mt-1">Lacak Pesanan Kamu</h1>
                <p class="text-xs md:text-sm text-cocoa-muted mt-2">
                    Masukkan nomor pesanan dan 4 digit terakhir nomor WhatsApp yang Anda gunakan saat pemesanan.
                </p>
            </div>

            <form action="{{ route('track.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-12 gap-3 max-w-2xl mx-auto">
                <div class="sm:col-span-6">
                    <input type="text" name="order_number" value="{{ old('order_number', $orderNumber) }}" placeholder="Nomor Pesanan (mis. KC-20261023-XXXX)" required class="w-full uppercase rounded-2xl border-blush focus:border-strawberry focus:ring-strawberry text-sm text-cocoa bg-cream/30 p-3">
                </div>
                <div class="sm:col-span-3">
                    <input type="text" name="phone_last4" value="{{ old('phone_last4', $phoneLast4) }}" maxlength="4" placeholder="4 digit WA" required class="w-full rounded-2xl border-blush focus:border-strawberry focus:ring-strawberry text-sm text-cocoa bg-cream/30 p-3 text-center">
                </div>
                <div class="sm:col-span-3">
                    <button type="submit" class="w-full h-full py-3 px-6 rounded-2xl bg-strawberry-deep text-white font-bold text-sm hover:bg-strawberry transition-all shadow-sm">
                        Lacak
                    </button>
                </div>
            </form>

            @if($errorMessage)
                <div class="mt-4 p-3.5 rounded-2xl bg-red-50 border border-red-200 text-red-700 text-xs text-center max-w-2xl mx-auto">
                    {{ $errorMessage }}
                </div>
            @endif
        </div>

        {{-- Order Details if Found --}}
        @if($order)
            <div class="space-y-6">
                
                {{-- Progress Bar Card --}}
                <div class="bg-white rounded-3xl p-6 md:p-8 shadow-card border border-blush">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-6 border-b border-blush gap-4">
                        <div>
                            <span class="text-xs uppercase tracking-wider font-bold text-strawberry">Nomor Pesanan</span>
                            <h2 class="text-2xl md:text-3xl font-serif font-black text-cocoa">#{{ $order->order_number }}</h2>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="px-4 py-1.5 rounded-full text-xs font-bold
                                @if($order->internal_status === \App\Enums\InternalOrderStatus::COMPLETED->value)
                                    bg-green-100 text-green-800
                                @elseif($order->internal_status === \App\Enums\InternalOrderStatus::CANCELLED->value)
                                    bg-red-100 text-red-800
                                @else
                                    bg-blush text-strawberry-deep
                                @endif
                            ">
                                Status: {{ $order->customer_status }}
                            </span>
                        </div>
                    </div>

                    {{-- 4-Step Progress Bar --}}
                    @if($order->internal_status !== \App\Enums\InternalOrderStatus::CANCELLED->value)
                        <div class="py-6">
                            <x-status-progress :currentStep="$stepIndex" />
                        </div>
                    @else
                        <div class="py-6 text-center text-red-600 font-bold text-sm">
                            Pesanan ini telah dibatalkan atau kedaluwarsa.
                        </div>
                    @endif
                </div>

                {{-- Schedule & Location Box --}}
                <div class="bg-white rounded-3xl p-6 md:p-8 shadow-card border border-blush">
                    <h3 class="font-serif font-bold text-lg text-cocoa mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-strawberry" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Jadwal & Lokasi
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
                        <div class="p-4 rounded-2xl bg-cream/50 border border-blush">
                            <span class="text-xs text-cocoa-muted font-semibold block mb-1">Tanggal & Waktu Fresh Bake</span>
                            <p class="font-serif font-bold text-cocoa text-base">
                                {{ \Carbon\Carbon::parse($order->fulfillment_date)->locale('id')->translatedFormat('l, d F Y') }}
                            </p>
                            <p class="text-xs text-strawberry-deep font-bold mt-1">
                                Slot {{ $order->fulfillment_slot }} WITA
                            </p>
                        </div>

                        <div class="p-4 rounded-2xl bg-cream/50 border border-blush">
                            <span class="text-xs text-cocoa-muted font-semibold block mb-1">Metode & Alamat</span>
                            <p class="font-bold text-cocoa">
                                {{ $order->delivery_type === 'delivery' ? 'Pengantaran (Delivery)' : 'Ambil di Toko (Pick-up)' }}
                            </p>
                            <p class="text-xs text-cocoa-muted mt-1 leading-relaxed">
                                {{ $order->delivery_address }}
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Order Summary Box --}}
                <div class="bg-white rounded-3xl p-6 md:p-8 shadow-card border border-blush">
                    <h3 class="font-serif font-bold text-lg text-cocoa mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-strawberry" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                        Ringkasan Item Pesanan
                    </h3>

                    <div class="divide-y divide-blush mb-6">
                        @foreach($order->items as $item)
                            <div class="py-3 flex items-start justify-between gap-4 text-sm">
                                <div>
                                    <h4 class="font-bold text-cocoa">{{ $item->product_name }}</h4>
                                    <p class="text-xs text-cocoa-muted">Varian: {{ $item->variant_name }} &times; {{ $item->quantity }}</p>
                                    @if(!empty($item->greeting_card))
                                        <div class="text-xs text-strawberry italic mt-0.5">
                                            Ucapan: "{{ $item->greeting_card }}"
                                        </div>
                                    @endif
                                </div>
                                <span class="font-bold text-cocoa whitespace-nowrap">
                                    Rp{{ number_format($item->price * $item->quantity, 0, ',', '.') }}
                                </span>
                            </div>
                        @endforeach
                    </div>

                    <div class="bg-cream/50 rounded-2xl p-4 border border-blush space-y-2 text-xs">
                        <div class="flex justify-between text-cocoa-muted">
                            <span>Subtotal</span>
                            <span class="font-bold text-cocoa">Rp{{ number_format($order->subtotal, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-cocoa-muted">
                            <span>Ongkos Kirim</span>
                            <span class="font-bold text-cocoa">Rp{{ number_format($order->shipping_fee, 0, ',', '.') }}</span>
                        </div>
                        @if($order->discount_amount > 0)
                            <div class="flex justify-between text-green-600">
                                <span>Potongan Diskon</span>
                                <span class="font-bold">-Rp{{ number_format($order->discount_amount, 0, ',', '.') }}</span>
                            </div>
                        @endif
                        <div class="border-t border-blush pt-2 flex justify-between text-sm font-bold text-cocoa">
                            <span class="font-serif">Total Pembayaran</span>
                            <span class="font-serif font-black text-strawberry-deep text-lg">Rp{{ number_format($order->total_amount, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>

                {{-- Action / Support CTA --}}
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4 bg-blush/60 rounded-3xl p-6 border border-blush">
                    <div>
                        <h4 class="font-serif font-bold text-base text-cocoa">Ada Pertanyaan Mengenai Pesananmu?</h4>
                        <p class="text-xs text-cocoa-muted mt-0.5">Tim kami siap melayani dan memberikan update proses memanggang langsung via WhatsApp.</p>
                    </div>
                    
                    @php
                        $waText = urlencode("Halo Pinky Cake, saya mau tanya tentang pesanan #{$order->order_number} atas nama {$order->customer_name}.");
                    @endphp
                    <a href="https://wa.me/{{ config('shop.wa_number') }}?text={{ $waText }}" target="_blank" class="inline-flex items-center gap-2 px-6 py-3.5 rounded-full bg-strawberry-deep text-white font-bold text-xs hover:bg-strawberry transition-all shadow-sm whitespace-nowrap">
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                            <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/>
                        </svg>
                        <span>Chat WhatsApp Toko</span>
                    </a>
                </div>

            </div>
        @endif

    </div>
</div>
</x-shop.layout>
