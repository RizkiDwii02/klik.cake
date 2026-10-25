<x-shop.layout :title="($currentCollection ? $currentCollection->name . ' — ' : '') . 'Katalog Klik.Cake Makassar'">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4 sm:pt-6 pb-12 space-y-6">

        {{-- Breadcrumb (Screen 02) --}}
        <nav class="flex items-center text-xs text-cocoa-muted font-medium gap-2">
            @foreach($breadcrumb as $index => $item)
                @if($loop->last)
                    <span class="text-cocoa font-bold">{{ $item['title'] }}</span>
                @else
                    <a href="{{ $item['url'] }}" class="hover:text-strawberry-deep transition-colors">{{ $item['title'] }}</a>
                    <span>&rsaquo;</span>
                @endif
            @endforeach
        </nav>

        {{-- Page Title --}}
        <div>
            <h1 class="font-serif font-black text-3xl sm:text-4xl text-cocoa">
                {{ $currentCollection ? $currentCollection->name : 'Semua Menu' }}
            </h1>
            <p class="text-xs sm:text-sm text-cocoa-muted mt-1 max-w-xl">
                {{ $currentCollection && $currentCollection->description ? $currentCollection->description : 'Pilihan roti, cake, pastry, dan snackbox hangat dipanggang sesuai pesanan di Makassar.' }}
            </p>
        </div>

        {{-- Main Layout: Sidebar Filters + Products Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start" x-data="{ mobileFilterOpen: false }">

            {{-- Mobile Filter Toggle Button --}}
            <div class="lg:hidden flex items-center justify-between">
                <button
                    type="button"
                    @click="mobileFilterOpen = !mobileFilterOpen"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white border border-blush-dark font-bold text-xs text-cocoa shadow-sm"
                >
                    <svg class="w-4 h-4 text-strawberry-deep" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    <span>Filter Produk</span>
                </button>

                <span class="text-xs font-bold text-cocoa-muted">
                    {{ $products->total() }} produk ditemukan
                </span>
            </div>

            {{-- Sidebar Filter (Desktop & Mobile Dropdown) --}}
            <aside
                :class="mobileFilterOpen ? 'block' : 'hidden lg:block'"
                class="lg:col-span-3 bg-white lg:bg-transparent rounded-2xl p-5 lg:p-0 border border-blush lg:border-none shadow-soft lg:shadow-none space-y-6"
            >
                <form action="{{ route('catalog.index') }}" method="GET" id="filterForm" class="space-y-6">
                    @if($sort)
                        <input type="hidden" name="sort" value="{{ $sort }}">
                    @endif

                    {{-- 1. Kategori --}}
                    <div class="space-y-3">
                        <h3 class="font-serif font-bold text-base text-cocoa">Kategori</h3>
                        <div class="space-y-1.5 text-sm">
                            <a
                                href="{{ route('catalog.index', array_filter(['harga' => $selectedPriceRange, 'acara' => $selectedOccasion, 'tanggal' => $selectedDate, 'sort' => $sort])) }}"
                                class="flex items-center justify-between px-3.5 py-2 rounded-xl transition-colors {{ empty($selectedCategorySlug) ? 'bg-blush text-strawberry-deep font-bold border border-strawberry-deep/30' : 'text-cocoa hover:bg-blush/40' }}"
                            >
                                <span>Semua Kategori</span>
                            </a>

                            @foreach($allCategories as $cat)
                                <div class="space-y-1">
                                    <a
                                        href="{{ route('catalog.category', array_merge(['slug' => $cat->slug], array_filter(['harga' => $selectedPriceRange, 'acara' => $selectedOccasion, 'tanggal' => $selectedDate, 'sort' => $sort]))) }}"
                                        class="flex items-center justify-between px-3.5 py-2 rounded-xl transition-colors {{ $selectedCategorySlug === $cat->slug ? 'bg-blush text-strawberry-deep font-bold border border-strawberry-deep/30' : 'text-cocoa hover:bg-blush/40' }}"
                                    >
                                        <span>{{ $cat->name }}</span>
                                    </a>

                                    @if($cat->children->isNotEmpty())
                                        <div class="pl-4 space-y-1">
                                            @foreach($cat->children as $child)
                                                <a
                                                    href="{{ route('catalog.category', array_merge(['slug' => $child->slug], array_filter(['harga' => $selectedPriceRange, 'acara' => $selectedOccasion, 'tanggal' => $selectedDate, 'sort' => $sort]))) }}"
                                                    class="flex items-center justify-between px-3 py-1.5 rounded-lg text-xs transition-colors {{ $selectedCategorySlug === $child->slug ? 'bg-blush text-strawberry-deep font-bold border border-strawberry-deep/30' : 'text-cocoa-muted hover:text-cocoa hover:bg-blush/20' }}"
                                                >
                                                    <span>{{ $child->name }}</span>
                                                </a>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- 2. Rentang Harga --}}
                    <div class="space-y-3 pt-4 border-t border-blush/80">
                        <h3 class="font-serif font-bold text-base text-cocoa">Harga</h3>
                        <div class="space-y-1.5 text-xs sm:text-sm">
                            @php
                                $priceOptions = [
                                    '' => 'Semua Harga',
                                    'under_50k' => 'Di bawah Rp50.000',
                                    '50k_150k' => 'Rp50.000 – Rp150.000',
                                    'over_150k' => 'Di atas Rp150.000',
                                ];
                            @endphp
                            @foreach($priceOptions as $val => $label)
                                <label class="flex items-center gap-2.5 px-3 py-1.5 rounded-xl hover:bg-blush/30 cursor-pointer select-none">
                                    <input
                                        type="radio"
                                        name="harga"
                                        value="{{ $val }}"
                                        {{ $selectedPriceRange === $val ? 'checked' : '' }}
                                        onchange="document.getElementById('filterForm').submit()"
                                        class="text-strawberry-deep focus:ring-strawberry-deep"
                                    />
                                    <span class="{{ $selectedPriceRange === $val ? 'font-bold text-strawberry-deep' : 'text-cocoa' }}">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- 3. Acara --}}
                    <div class="space-y-3 pt-4 border-t border-blush/80">
                        <h3 class="font-serif font-bold text-base text-cocoa">Acara</h3>
                        <div class="space-y-1.5 text-xs sm:text-sm">
                            @php
                                $occasionOptions = [
                                    '' => 'Semua Acara',
                                    'ulang-tahun' => 'Ulang Tahun & Pesta',
                                    'kantor-acara' => 'Rapat Kantor & Acara',
                                    'sarapan-harian' => 'Sarapan & Santap Harian',
                                ];
                            @endphp
                            @foreach($occasionOptions as $val => $label)
                                <label class="flex items-center gap-2.5 px-3 py-1.5 rounded-xl hover:bg-blush/30 cursor-pointer select-none">
                                    <input
                                        type="radio"
                                        name="acara"
                                        value="{{ $val }}"
                                        {{ $selectedOccasion === $val ? 'checked' : '' }}
                                        onchange="document.getElementById('filterForm').submit()"
                                        class="text-strawberry-deep focus:ring-strawberry-deep"
                                    />
                                    <span class="{{ $selectedOccasion === $val ? 'font-bold text-strawberry-deep' : 'text-cocoa' }}">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- 4. Tersedia Tanggal --}}
                    <div class="space-y-3 pt-4 border-t border-blush/80">
                        <h3 class="font-serif font-bold text-base text-cocoa">Tersedia tanggal</h3>
                        <div class="space-y-2">
                            <input
                                type="date"
                                name="tanggal"
                                value="{{ $selectedDate }}"
                                min="{{ date('Y-m-d') }}"
                                onchange="document.getElementById('filterForm').submit()"
                                class="w-full px-3.5 py-2 rounded-xl border border-blush-dark text-xs sm:text-sm text-cocoa bg-white focus:outline-none focus:ring-2 focus:ring-strawberry-deep"
                            />
                            @if($selectedDate)
                                <button
                                    type="button"
                                    onclick="document.querySelector('input[name=tanggal]').value = ''; document.getElementById('filterForm').submit();"
                                    class="text-[11px] font-bold text-strawberry-deep hover:underline block"
                                >
                                    &times; Hapus filter tanggal
                                </button>
                            @endif
                        </div>
                    </div>

                    {{-- Reset Button --}}
                    @if($selectedPriceRange || $selectedOccasion || $selectedDate || $selectedCategorySlug)
                        <div class="pt-2">
                            <a
                                href="{{ route('catalog.index') }}"
                                class="w-full text-center block text-xs font-bold text-cocoa-muted hover:text-strawberry-deep py-2 border border-blush-dark rounded-full bg-white hover:bg-blush/40 transition-colors"
                            >
                                Reset Semua Filter
                            </a>
                        </div>
                    @endif
                </form>
            </aside>

            {{-- Main Products Grid Area (Screen 02) --}}
            <main class="lg:col-span-9 space-y-6">

                {{-- Header Bar: Product Count & Sort Dropdown (Screen 02) --}}
                <div class="flex items-center justify-between pb-3 border-b border-blush/80">
                    <p class="text-xs sm:text-sm font-bold text-cocoa-muted">
                        <span class="text-cocoa font-black">{{ $products->total() }}</span> produk ditemukan
                    </p>

                    {{-- Sort Dropdown --}}
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-cocoa-muted hidden sm:inline">Urutkan:</span>
                        <div class="relative inline-block text-left">
                            <select
                                onchange="location = this.value;"
                                class="appearance-none bg-white border border-blush-dark text-cocoa font-bold text-xs sm:text-sm rounded-full pl-4 pr-8 py-2 focus:outline-none focus:ring-2 focus:ring-strawberry-deep cursor-pointer shadow-sm"
                            >
                                <option value="{{ request()->fullUrlWithQuery(['sort' => 'terbaru']) }}" {{ $sort === 'terbaru' ? 'selected' : '' }}>Terbaru</option>
                                <option value="{{ request()->fullUrlWithQuery(['sort' => 'terpopuler']) }}" {{ $sort === 'terpopuler' ? 'selected' : '' }}>Terpopuler</option>
                                <option value="{{ request()->fullUrlWithQuery(['sort' => 'harga_asc']) }}" {{ $sort === 'harga_asc' ? 'selected' : '' }}>Harga Terendah</option>
                                <option value="{{ request()->fullUrlWithQuery(['sort' => 'harga_desc']) }}" {{ $sort === 'harga_desc' ? 'selected' : '' }}>Harga Tertinggi</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-cocoa">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Products Grid: 3 Kolom Desktop / 2 Kolom Mobile (Screen 02) --}}
                @if($products->isNotEmpty())
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-3 sm:gap-6">
                        @foreach($products as $product)
                            <x-product-card
                                :product="$product"
                                :href="route('product.show', $product->slug)"
                            />
                        @endforeach
                    </div>

                    {{-- Pagination (Screen 02: 1 2 3 Circular Pills) --}}
                    <div class="pt-8 flex justify-center">
                        {{ $products->links('shop.pagination') }}
                    </div>
                @else
                    {{-- Empty State --}}
                    <div class="bg-white rounded-3xl p-12 text-center border border-blush shadow-soft space-y-4">
                        <div class="w-16 h-16 mx-auto rounded-full bg-blush flex items-center justify-center text-strawberry-deep">
                            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="font-serif font-bold text-xl text-cocoa">Belum ada menu yang cocok</h3>
                        <p class="text-sm text-cocoa-muted max-w-sm mx-auto">
                            Coba ubah atau hilangkan beberapa filter untuk melihat menu roti dan kue lezat kami lainnya.
                        </p>
                        <div class="pt-2">
                            <x-button as="a" href="{{ route('catalog.index') }}" variant="primary" size="sm">
                                Lihat Semua Menu
                            </x-button>
                        </div>
                    </div>
                @endif

            </main>
        </div>

    </div>
</x-shop.layout>
