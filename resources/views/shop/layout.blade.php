<!DOCTYPE html>
<html lang="id" class="h-full scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Klik.Cake by Pinky Cake & Bakery — Pre-order Roti & Kue Segar Makassar' }}</title>
    <meta name="description" content="Pesan roti & kue segar dari Makassar, pilih jadwal, bayar, pantau sampai matang. Klik, dipanggang, sampai.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400..700;1,9..144,400..700&family=Nunito:ital,wght@0,400..800;1,400..800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-cream text-cocoa font-sans min-h-full flex flex-col selection:bg-blush selection:text-strawberry-deep overflow-x-hidden">

    {{-- Top Desktop & Mobile Header (Screen 01–07) --}}
    <header class="sticky top-0 z-30 bg-cream/95 backdrop-blur-md border-b border-blush/80 transition-all">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-18 flex items-center justify-between">
            {{-- Logo --}}
            <a href="{{ route('home') }}" class="flex items-baseline gap-1.5 group">
                <span class="font-serif font-black text-2xl md:text-3xl text-strawberry-deep tracking-tight group-hover:opacity-90 transition-opacity">
                    Klik.Cake
                </span>
                <span class="hidden sm:inline-block text-[11px] font-bold text-cocoa-muted tracking-wider uppercase">
                    by Pinky Cake
                </span>
            </a>

            {{-- Desktop Navigation Links --}}
            <nav class="hidden md:flex items-center gap-8 font-bold text-sm text-cocoa">
                <a href="{{ route('catalog.index') }}" class="hover:text-strawberry-deep transition-colors {{ request()->routeIs('catalog.*') ? 'text-strawberry-deep' : '' }}">
                    Menu
                </a>
                <a href="{{ route('home') }}#fresh-bake" class="hover:text-strawberry-deep transition-colors flex items-center gap-1.5">
                    <span>Fresh Bake</span>
                    <span class="w-2 h-2 rounded-full bg-butter animate-pulse"></span>
                </a>
                <a href="{{ route('track.index') }}" class="hover:text-strawberry-deep transition-colors {{ request()->routeIs('track.*') ? 'text-strawberry-deep' : '' }}">
                    Lacak Pesanan
                </a>
            </nav>

            {{-- Cart Button Header --}}
            @php
                $cartCount = session('cart_count', 0);
            @endphp
            <div class="flex items-center gap-3">
                <x-button as="a" href="{{ route('cart.index') }}" variant="primary" size="sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                    <span>Keranjang &middot; {{ $cartCount }}</span>
                </x-button>
            </div>
        </div>
    </header>

    {{-- Flash Notifications --}}
    @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" class="bg-blush border-b border-blush-dark px-4 py-3 text-center text-sm font-bold text-strawberry-deep transition-all flex items-center justify-center gap-2">
            <svg class="w-5 h-5 text-strawberry-deep flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
            </svg>
            <span>{{ session('success') }}</span>
            <button @click="show = false" class="ml-2 text-cocoa-muted hover:text-cocoa">&times;</button>
        </div>
    @endif

    {{-- Main Content --}}
    <main class="flex-1 pb-24 md:pb-12">
        {{ $slot }}
    </main>

    {{-- Floating WhatsApp Customer Support Button --}}
    @php
        $waNumber = config('shop.wa_number', '6281234567890');
        $waMessage = rawurlencode('Halo Pinky Cake & Bakery, saya ingin bertanya tentang pesanan roti dan kue di Klik.Cake.');
        $waUrl = "https://wa.me/{$waNumber}?text={$waMessage}";
    @endphp
    <aside class="fixed bottom-20 md:bottom-8 right-5 z-40">
        <a
            href="{{ $waUrl }}"
            target="_blank"
            rel="noopener noreferrer"
            class="flex items-center justify-center w-13 h-13 md:w-14 md:h-14 rounded-full bg-[#25D366] text-white shadow-lg hover:scale-110 active:scale-95 transition-all duration-200 group focus:outline-none focus:ring-4 focus:ring-[#25D366]/40"
            aria-label="Chat WhatsApp CS"
            title="Chat WhatsApp Pinky Cake"
        >
            <span class="font-extrabold text-sm md:text-base tracking-tighter">WA</span>
        </a>
    </aside>

    {{-- Mobile Bottom Bar Navigation (Screen 01–07) --}}
    <nav class="md:hidden fixed bottom-0 left-0 right-0 z-30 bg-white/95 backdrop-blur-md border-t border-blush shadow-lg px-4 py-2 flex items-center justify-around">
        <a href="{{ route('home') }}" class="flex flex-col items-center gap-1 text-cocoa hover:text-strawberry-deep transition-colors py-1 {{ request()->routeIs('home') ? 'text-strawberry-deep font-black' : '' }}">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            <span class="text-[10px] font-bold">Beranda</span>
        </a>

        <a href="{{ route('catalog.index') }}" class="flex flex-col items-center gap-1 text-cocoa hover:text-strawberry-deep transition-colors py-1 {{ request()->routeIs('catalog.*') ? 'text-strawberry-deep font-black' : '' }}">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>
            </svg>
            <span class="text-[10px] font-bold">Menu</span>
        </a>

        <a href="{{ route('track.index') }}" class="flex flex-col items-center gap-1 text-cocoa hover:text-strawberry-deep transition-colors py-1 {{ request()->routeIs('track.*') ? 'text-strawberry-deep font-black' : '' }}">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
            </svg>
            <span class="text-[10px] font-bold">Lacak</span>
        </a>

        <a href="{{ route('cart.index') }}" class="flex flex-col items-center gap-1 text-cocoa hover:text-strawberry-deep transition-colors py-1 relative {{ request()->routeIs('cart.*') ? 'text-strawberry-deep font-black' : '' }}">
            <div class="relative">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                </svg>
                <span class="absolute -top-1 -right-2 bg-strawberry-deep text-white text-[9px] font-black w-4 h-4 rounded-full flex items-center justify-center">
                    {{ $cartCount }}
                </span>
            </div>
            <span class="text-[10px] font-bold">Keranjang</span>
        </a>
    </nav>

    {{-- Footer & Kontak (Screen 01 Footer) --}}
    <footer class="bg-blush/40 border-t border-blush text-cocoa py-12 px-4 sm:px-6 lg:px-8 mt-12">
        <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-4 gap-8">
            <div class="md:col-span-2">
                <span class="font-serif font-black text-2xl text-strawberry-deep tracking-tight">
                    Klik.Cake
                </span>
                <span class="text-xs font-bold text-cocoa-muted tracking-wider uppercase block mt-1">
                    by Pinky Cake and Bakery Makassar
                </span>
                <p class="font-sans text-sm text-cocoa-muted mt-3 max-w-sm leading-relaxed">
                    Pesan roti &amp; kue segar dari Makassar, pilih jadwal, bayar, pantau sampai matang. Dibuat langsung di dapur kami dengan mentega berkualitas dan bahan pilihan.
                </p>
                <div class="mt-4 flex items-center gap-3">
                    <a href="{{ $waUrl }}" target="_blank" class="inline-flex items-center gap-2 text-xs font-bold text-[#25D366] bg-white px-3 py-1.5 rounded-full border border-blush shadow-sm hover:scale-105 transition-transform">
                        <span>WhatsApp CS: +{{ $waNumber }}</span>
                    </a>
                </div>
            </div>

            <div>
                <h4 class="font-serif font-bold text-base text-cocoa mb-3">Menu Koleksi</h4>
                <ul class="space-y-2 text-sm text-cocoa-muted font-medium">
                    <li><a href="{{ route('catalog.category', 'cake') }}" class="hover:text-strawberry-deep transition-colors">Cake &amp; Tart Ulang Tahun</a></li>
                    <li><a href="{{ route('catalog.category', 'roti') }}" class="hover:text-strawberry-deep transition-colors">Roti Manis &amp; Gurih</a></li>
                    <li><a href="{{ route('catalog.category', 'pastry') }}" class="hover:text-strawberry-deep transition-colors">Croissant &amp; Pastry</a></li>
                    <li><a href="{{ route('catalog.category', 'snackbox') }}" class="hover:text-strawberry-deep transition-colors">Snackbox Acara &amp; Kantor</a></li>
                </ul>
            </div>

            <div>
                <h4 class="font-serif font-bold text-base text-cocoa mb-3">Dapur Makassar</h4>
                <p class="text-sm text-cocoa-muted leading-relaxed">
                    Jl. Boulevard / Panakkukang<br>
                    Makassar, Sulawesi Selatan<br>
                    Buka setiap hari: 08.00 &ndash; 20.00 WITA
                </p>
                <p class="text-xs text-cocoa-muted/80 mt-4">
                    &copy; {{ date('Y') }} Klik.Cake by Pinky Cake. All rights reserved.
                </p>
            </div>
        </div>
    </footer>

</body>
</html>
