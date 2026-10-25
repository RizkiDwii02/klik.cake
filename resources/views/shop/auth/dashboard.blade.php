<x-shop.layout title="Akun Saya — Klik.Cake Makassar">
    <div class="bg-cream min-h-screen py-8 md:py-12">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            
            {{-- Breadcrumb --}}
            <nav class="flex items-center gap-2 text-xs md:text-sm text-cocoa-muted mb-6">
                <a href="{{ route('home') }}" class="hover:text-strawberry-deep transition-colors">Beranda</a>
                <span>/</span>
                <span class="text-cocoa font-semibold">Akun Saya</span>
            </nav>

            {{-- Admin Banner if Admin --}}
            @if($user->isAdmin())
                <div class="mb-8 p-6 rounded-3xl bg-gradient-to-r from-strawberry-deep to-strawberry text-white shadow-card flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-white/20 backdrop-blur-sm flex items-center justify-center text-2xl flex-shrink-0">
                            🛡️
                        </div>
                        <div>
                            <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-butter text-cocoa text-[10px] font-black uppercase tracking-wider mb-1">
                                Hak Akses Administrator
                            </div>
                            <h2 class="font-serif font-black text-xl md:text-2xl">
                                Terhubung ke Panel Admin
                            </h2>
                            <p class="text-xs text-white/90 mt-0.5 max-w-xl">
                                Akun Anda memiliki izin penuh untuk mengelola pesanan masuk, kuota kalender Fresh Bake, verifikasi pembayaran, dan katalog produk.
                            </p>
                        </div>
                    </div>
                    <a
                        href="{{ url('/admin') }}"
                        class="px-6 py-3 rounded-full bg-white text-strawberry-deep font-extrabold text-xs shadow-md hover:bg-cream transition-all flex items-center gap-2 flex-shrink-0"
                    >
                        <span>Buka Dashboard Admin</span>
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </a>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 items-start">
                
                {{-- Profile Summary Card --}}
                <div class="bg-white rounded-3xl p-6 shadow-card border border-blush space-y-6">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 rounded-2xl bg-blush text-strawberry-deep flex items-center justify-center font-serif font-black text-2xl uppercase">
                            {{ substr($user->name, 0, 1) }}
                        </div>
                        <div>
                            <h1 class="font-serif font-bold text-lg text-cocoa leading-tight">
                                {{ $user->name }}
                            </h1>
                            <p class="text-xs text-cocoa-muted truncate max-w-[180px]">
                                {{ $user->email }}
                            </p>
                            <span class="inline-block mt-1 px-2 py-0.5 rounded-full text-[10px] font-extrabold {{ $user->isAdmin() ? 'bg-butter text-cocoa' : 'bg-blush text-strawberry-deep' }}">
                                {{ $user->isAdmin() ? 'Administrator' : 'Pelanggan' }}
                            </span>
                        </div>
                    </div>

                    <div class="border-t border-blush pt-4 space-y-2">
                        <a href="{{ route('catalog.index') }}" class="w-full flex items-center justify-between p-3 rounded-2xl hover:bg-cream/60 text-xs font-bold text-cocoa transition-colors">
                            <span class="flex items-center gap-2.5">
                                <svg class="w-4 h-4 text-strawberry-deep" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                </svg>
                                <span>Pesan Roti &amp; Kue</span>
                            </span>
                            <span class="text-cocoa-muted">&rarr;</span>
                        </a>

                        <a href="{{ route('track.index') }}" class="w-full flex items-center justify-between p-3 rounded-2xl hover:bg-cream/60 text-xs font-bold text-cocoa transition-colors">
                            <span class="flex items-center gap-2.5">
                                <svg class="w-4 h-4 text-strawberry-deep" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                </svg>
                                <span>Lacak Pesanan Tertentu</span>
                            </span>
                            <span class="text-cocoa-muted">&rarr;</span>
                        </a>

                        @if($user->isAdmin())
                            <a href="{{ url('/admin') }}" class="w-full flex items-center justify-between p-3 rounded-2xl bg-blush/30 hover:bg-blush/60 text-xs font-bold text-strawberry-deep transition-colors">
                                <span class="flex items-center gap-2.5">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <span>Panel Admin Toko</span>
                                </span>
                                <span class="text-strawberry-deep font-black">&rarr;</span>
                            </a>
                        @endif
                    </div>

                    {{-- Logout Form --}}
                    <div class="border-t border-blush pt-4">
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button
                                type="submit"
                                class="w-full py-2.5 px-4 rounded-xl border border-red-200 text-red-600 hover:bg-red-50 text-xs font-bold transition-all flex items-center justify-center gap-2 cursor-pointer"
                            >
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                                <span>Keluar dari Akun</span>
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Orders List --}}
                <div class="md:col-span-2 space-y-6">
                    <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-card border border-blush">
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <h3 class="font-serif font-black text-xl text-cocoa">
                                    Riwayat Pesanan Anda
                                </h3>
                                <p class="text-xs text-cocoa-muted mt-0.5">
                                    Daftar pesanan kue &amp; roti berdasarkan email akun Anda.
                                </p>
                            </div>
                            <span class="px-3 py-1 rounded-full bg-cream text-cocoa text-xs font-bold border border-blush">
                                {{ $orders->count() }} Pesanan
                            </span>
                        </div>

                        @if($orders->isEmpty())
                            <div class="text-center py-10 px-4">
                                <div class="w-16 h-16 rounded-full bg-blush mx-auto flex items-center justify-center text-2xl text-strawberry-deep mb-3">
                                    🎂
                                </div>
                                <h4 class="font-serif font-bold text-base text-cocoa">
                                    Belum Ada Pesanan
                                </h4>
                                <p class="text-xs text-cocoa-muted max-w-sm mx-auto mt-1 leading-relaxed">
                                    Anda belum memiliki riwayat pemesanan kue segar dengan email ini. Yuk mulai pesan untuk momen spesial Anda!
                                </p>
                                <div class="mt-5">
                                    <a
                                        href="{{ route('catalog.index') }}"
                                        class="inline-flex items-center gap-2 px-6 py-2.5 rounded-full bg-strawberry-deep hover:bg-strawberry text-white font-extrabold text-xs shadow-sm transition-all"
                                    >
                                        <span>Lihat Menu Katalog</span>
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        @else
                            <div class="space-y-4">
                                @foreach($orders as $order)
                                    <div class="p-5 rounded-2xl border border-blush bg-cream/20 hover:bg-cream/50 transition-all flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                        <div class="space-y-1">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <span class="font-mono font-bold text-sm text-cocoa">
                                                    #{{ $order->order_number }}
                                                </span>
                                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider
                                                    @if($order->customer_status === \App\Enums\CustomerOrderStatus::RECEIVED->value) bg-blue-100 text-blue-700
                                                    @elseif($order->customer_status === \App\Enums\CustomerOrderStatus::BAKING->value) bg-amber-100 text-amber-800
                                                    @elseif($order->customer_status === \App\Enums\CustomerOrderStatus::READY->value) bg-purple-100 text-purple-700
                                                    @elseif($order->customer_status === \App\Enums\CustomerOrderStatus::COMPLETED->value) bg-emerald-100 text-emerald-700
                                                    @else bg-gray-100 text-gray-700 @endif
                                                ">
                                                    {{ $order->customer_status_label }}
                                                </span>
                                            </div>
                                            <p class="text-xs text-cocoa-muted">
                                                Jadwal: <strong class="text-cocoa">{{ $order->fulfillment_date?->translatedFormat('d M Y') }}</strong> ({{ $order->fulfillment_slot }})
                                            </p>
                                            <p class="text-xs text-cocoa-muted">
                                                Total: <strong class="text-strawberry-deep font-bold">Rp{{ number_format($order->total_amount, 0, ',', '.') }}</strong>
                                                &middot; {{ $order->items->count() }} Produk
                                            </p>
                                        </div>

                                        <div class="flex items-center gap-2 self-start sm:self-center">
                                            @if($order->internal_status === \App\Enums\InternalOrderStatus::ORDER_PLACED->value && !$order->isExpired())
                                                <a
                                                    href="{{ route('payment.show', $order->order_number) }}"
                                                    class="px-4 py-2 rounded-full bg-strawberry-deep text-white text-xs font-bold hover:bg-strawberry transition-colors shadow-sm"
                                                >
                                                    Bayar
                                                </a>
                                            @endif
                                            <a
                                                href="{{ route('track.show', $order->order_number) }}"
                                                class="px-4 py-2 rounded-full border border-blush bg-white text-cocoa hover:text-strawberry-deep text-xs font-bold hover:border-strawberry-deep transition-colors shadow-sm flex items-center gap-1.5"
                                            >
                                                <span>Lacak</span>
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                                </svg>
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-shop.layout>
