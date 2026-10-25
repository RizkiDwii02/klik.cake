<x-shop.layout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 sm:pt-10 space-y-16 sm:space-y-24">

        {{-- 1. Hero Section (Screen 01) --}}
        <section class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
            <div class="lg:col-span-7 space-y-5 sm:space-y-6">
                <div>
                    <x-badge variant="butter" size="sm">
                        Fresh Bake hari ini
                    </x-badge>
                </div>

                <h1 class="font-serif font-black text-4xl sm:text-5xl lg:text-6xl text-cocoa leading-[1.15] tracking-tight">
                    Klik, dipanggang,<br class="hidden sm:inline"> sampai.
                </h1>

                <p class="font-sans text-base sm:text-lg text-cocoa-muted max-w-xl leading-relaxed">
                    Pesan roti &amp; kue segar dari Makassar, pilih jadwal, bayar, pantau sampai matang. Dibuat fresh setiap hari langsung dari dapur kami.
                </p>

                <div class="flex flex-wrap items-center gap-3 pt-2">
                    <x-button as="a" href="{{ route('catalog.index') }}" variant="primary" size="lg">
                        Pesan Sekarang
                    </x-button>

                    <x-button as="a" href="#kategori" variant="secondary" size="lg">
                        Lihat Menu
                    </x-button>
                </div>

                <div class="pt-4 flex items-center gap-6 text-xs text-cocoa-muted font-semibold">
                    <div class="flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        <span>Dipanggang Segar H-1</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-butter"></span>
                        <span>Kirim &amp; Ambil Makassar</span>
                    </div>
                </div>
            </div>

            {{-- Hero Cake Card / Visual --}}
            <div class="lg:col-span-5 relative">
                <div class="relative bg-blush rounded-3xl p-6 sm:p-8 border border-blush-dark/60 shadow-soft overflow-hidden group">
                    <div class="absolute -right-12 -top-12 w-48 h-48 bg-strawberry-light/50 rounded-full blur-2xl pointer-events-none"></div>

                    <div class="relative z-10 flex flex-col items-center">
                        <div class="w-full max-w-xs mx-auto">
                            <x-product-image :src="$heroProduct->image_url ?? null" :alt="$heroProduct->name ?? 'Strawberry Cream Cake'" aspect="1/1" />
                        </div>

                        <div class="mt-6 text-center">
                            <x-badge variant="butter" size="xs">Unggulan Makassar</x-badge>
                            <h3 class="font-serif font-black text-xl sm:text-2xl text-cocoa mt-2">
                                {{ $heroProduct->name ?? 'Strawberry Cream Cake' }}
                            </h3>
                            <p class="font-sans font-black text-lg text-strawberry-deep mt-1">
                                {{ $heroProduct->formattedPrice() ?? 'Rp185.000' }}
                            </p>
                            <p class="text-xs text-cocoa-muted mt-1 max-w-xs">
                                {{ $heroProduct->portion_info ?? 'Porsi 6–8 orang · Lead time H-1' }}
                            </p>

                            <div class="mt-4">
                                <x-button as="a" href="{{ route('product.show', $heroProduct->slug) }}" variant="primary" size="sm">
                                    Pesan Kue Ini &rarr;
                                </x-button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- 2. Kategori Section (Screen 01) --}}
        <section id="kategori" class="space-y-6">
            <div class="flex items-baseline justify-between border-b border-blush/80 pb-3">
                <h2 class="font-serif font-bold text-2xl sm:text-3xl text-cocoa">
                    Kategori
                </h2>
                <a href="{{ route('catalog.index') }}" class="text-xs sm:text-sm font-bold text-strawberry-deep hover:underline">
                    Semua Menu &rarr;
                </a>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
                @foreach($categories as $cat)
                    <a
                        href="{{ route('catalog.category', $cat->slug) }}"
                        class="flex items-center justify-center p-4 sm:p-5 rounded-2xl bg-white border border-blush-dark/60 text-cocoa font-bold text-base sm:text-lg hover:border-strawberry-deep hover:bg-blush/30 hover:text-strawberry-deep hover:-translate-y-0.5 transition-all shadow-sm select-none"
                    >
                        <span>{{ $cat->name }}</span>
                    </a>
                @endforeach
            </div>
        </section>

        {{-- 3. Best Seller Section (Screen 01: 4 Kolom Desktop, 2 Kolom Mobile) --}}
        <section class="space-y-6">
            <div class="flex items-baseline justify-between border-b border-blush/80 pb-3">
                <div>
                    <h2 class="font-serif font-bold text-2xl sm:text-3xl text-cocoa">
                        Best Seller
                    </h2>
                    <p class="text-xs sm:text-sm text-cocoa-muted mt-1">
                        Paling diminati pelanggan setia Pinky Cake di Makassar
                    </p>
                </div>

                <a href="{{ route('catalog.index', ['sort' => 'terpopuler']) }}" class="text-xs sm:text-sm font-bold text-strawberry-deep hover:underline">
                    Lihat Semua &rarr;
                </a>
            </div>

            {{-- 4 Columns Desktop, 2 Columns Mobile --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-6">
                @foreach($bestSellers->take(4) as $product)
                    <x-product-card
                        :product="$product"
                        :href="route('product.show', $product->slug)"
                    />
                @endforeach
            </div>
        </section>

        {{-- Fresh Bake Banner --}}
        <section id="fresh-bake" class="bg-gradient-to-r from-blush via-blush-light to-blush rounded-3xl p-6 sm:p-10 border border-blush-dark/60 flex flex-col sm:flex-row items-center justify-between gap-6 shadow-soft">
            <div class="space-y-2 text-center sm:text-left">
                <x-badge variant="butter">Jadwal Fresh Bake</x-badge>
                <h3 class="font-serif font-bold text-2xl sm:text-3xl text-cocoa">
                    Dipanggang Pas Jadwal, Tiba Masih Wangi
                </h3>
                <p class="text-sm text-cocoa-muted max-w-xl">
                    Tiga slot pemanggangan setiap hari: <strong>10.00</strong>, <strong>13.00</strong>, dan <strong>16.00</strong>. Kuota terbatas 5 cake per slot agar kualitas tetap terjaga maksimal.
                </p>
            </div>

            <x-button as="a" href="{{ route('catalog.index') }}" variant="primary" size="md">
                Pilih Jadwal Pemanggangan
            </x-button>
        </section>

        {{-- 4. Cara Pesan 3 Langkah (Screen 01) --}}
        <section id="lacak" class="space-y-8">
            <div class="text-center max-w-xl mx-auto space-y-2">
                <x-badge variant="blush">Mudah &amp; Transparan</x-badge>
                <h2 class="font-serif font-bold text-2xl sm:text-3xl text-cocoa">
                    Cara pesan
                </h2>
                <p class="text-xs sm:text-sm text-cocoa-muted">
                    Hanya 3 menit dari memilih kue sampai pesanan masuk antrean panggang
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                {{-- Step 1 --}}
                <div class="bg-white rounded-2xl p-6 border border-blush shadow-soft text-center space-y-3 relative">
                    <div class="w-12 h-12 mx-auto rounded-full bg-strawberry-deep text-white font-serif font-black text-xl flex items-center justify-center shadow-pill">
                        1
                    </div>
                    <h3 class="font-serif font-bold text-lg text-cocoa">Pilih Menu &amp; Jadwal</h3>
                    <p class="text-xs sm:text-sm text-cocoa-muted leading-relaxed">
                        Tentukan varian ukuran/rasa, tuliskan ucapan kartu, lalu pilih tanggal dan jam slot ambil atau kirim.
                    </p>
                </div>

                {{-- Step 2 --}}
                <div class="bg-white rounded-2xl p-6 border border-blush shadow-soft text-center space-y-3 relative">
                    <div class="w-12 h-12 mx-auto rounded-full bg-strawberry-deep text-white font-serif font-black text-xl flex items-center justify-center shadow-pill">
                        2
                    </div>
                    <h3 class="font-serif font-bold text-lg text-cocoa">Bayar Tanpa Ribet</h3>
                    <p class="text-xs sm:text-sm text-cocoa-muted leading-relaxed">
                        Konfirmasi pembayaran otomatis via QRIS, GoPay, ShopeePay, atau transfer bank. Tanpa perlu kirim bukti struk.
                    </p>
                </div>

                {{-- Step 3 --}}
                <div class="bg-white rounded-2xl p-6 border border-blush shadow-soft text-center space-y-3 relative">
                    <div class="w-12 h-12 mx-auto rounded-full bg-strawberry-deep text-white font-serif font-black text-xl flex items-center justify-center shadow-pill">
                        3
                    </div>
                    <h3 class="font-serif font-bold text-lg text-cocoa">Pantau Sampai Matang</h3>
                    <p class="text-xs sm:text-sm text-cocoa-muted leading-relaxed">
                        Cek status transparan: Diterima &rarr; Dipanggang &rarr; Siap &rarr; Selesai. Notifikasi pesanan terhubung langsung ke WhatsApp.
                    </p>
                </div>
            </div>
        </section>

        {{-- 5. Testimoni Pelanggan (Screen 01) --}}
        <section class="space-y-6">
            <div class="text-center max-w-xl mx-auto space-y-2">
                <x-badge variant="butter">Cerita Pembeli</x-badge>
                <h2 class="font-serif font-bold text-2xl sm:text-3xl text-cocoa">
                    Kata Mereka Tentang Pinky Cake
                </h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white rounded-2xl p-6 border border-blush shadow-soft space-y-3">
                    <div class="flex text-butter text-sm">&#9733;&#9733;&#9733;&#9733;&#9733;</div>
                    <p class="text-xs sm:text-sm text-cocoa italic leading-relaxed">
                        &ldquo;Strawberry Cream Cake-nya juara! Bolunya lembut dan krimnya ringan tidak bikin enek. Stroberinya fresh manis asem segar.&rdquo;
                    </p>
                    <div class="pt-2 border-t border-blush">
                        <p class="font-bold text-xs text-cocoa">Andi Nurul</p>
                        <p class="text-[10px] text-cocoa-muted">Panakkukang, Makassar</p>
                    </div>
                </div>

                <div class="bg-white rounded-2xl p-6 border border-blush shadow-soft space-y-3">
                    <div class="flex text-butter text-sm">&#9733;&#9733;&#9733;&#9733;&#9733;</div>
                    <p class="text-xs sm:text-sm text-cocoa italic leading-relaxed">
                        &ldquo;Sangat terbantu ada pilihan slot jam. Pesan H-1 sore, besok jam 1 siang diambil di toko kuenya baru saja selesai dikemas.&rdquo;
                    </p>
                    <div class="pt-2 border-t border-blush">
                        <p class="font-bold text-xs text-cocoa">Reza Pratama</p>
                        <p class="text-[10px] text-cocoa-muted">Tamalanrea, Makassar</p>
                    </div>
                </div>

                <div class="bg-white rounded-2xl p-6 border border-blush shadow-soft space-y-3">
                    <div class="flex text-butter text-sm">&#9733;&#9733;&#9733;&#9733;&#9733;</div>
                    <p class="text-xs sm:text-sm text-cocoa italic leading-relaxed">
                        &ldquo;Snackbox kantor untuk rapat 40 orang rapi dan kuenya enak semua. Pesan lewat web praktis langsung dapat rincian lengkap.&rdquo;
                    </p>
                    <div class="pt-2 border-t border-blush">
                        <p class="font-bold text-xs text-cocoa">Ibu Fatimah</p>
                        <p class="text-[10px] text-cocoa-muted">Biringkanaya, Makassar</p>
                    </div>
                </div>
            </div>
        </section>

    </div>
</x-shop.layout>
