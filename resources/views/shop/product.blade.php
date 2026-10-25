<x-shop.layout :title="$product->name . ' — Klik.Cake Makassar'">
    <div
        class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4 sm:pt-6 pb-16 space-y-10"
        x-data="{
            variants: {{ Js::from($product->variants->map(fn($v) => [
                'id' => $v->id,
                'name' => $v->name,
                'price' => $v->price,
                'formatted' => $v->formattedPrice(),
                'stock' => $v->stock,
            ])) }},
            activeVariantId: {{ $product->variants->first()->id ?? 0 }},
            activeVariantName: '{{ $product->variants->first()->name ?? '' }}',
            activePriceFormatted: '{{ $product->variants->first() ? $product->variants->first()->formattedPrice() : $product->formattedPrice() }}',
            greeting: '',
            selectedDate: '{{ $initialDateString }}',
            selectedSlot: '{{ array_key_first(array_filter($initialSlots, fn($s) => $s['is_available'])) ?? '10:00' }}',
            slots: {{ Js::from(array_values($initialSlots)) }},
            isLoadingSlots: false,
            activeThumb: 0,
            qty: 1,

            selectVariant(v) {
                this.activeVariantId = v.id;
                this.activeVariantName = v.name;
                this.activePriceFormatted = v.formatted;
            },

            async updateDate(newDate) {
                this.selectedDate = newDate;
                this.isLoadingSlots = true;
                try {
                    const res = await fetch(`/api/slots?tanggal=${newDate}&lead_time={{ $product->lead_time_days }}&qty=${this.qty}`);
                    const data = await res.json();
                    this.slots = data.slots;
                    const currentSlotData = this.slots.find(s => s.time_slot === this.selectedSlot);
                    if (!currentSlotData || !currentSlotData.is_available) {
                        const firstAvailable = this.slots.find(s => s.is_available);
                        this.selectedSlot = firstAvailable ? firstAvailable.time_slot : '';
                    }
                } catch (e) {
                    console.error('Error fetching slots', e);
                } finally {
                    this.isLoadingSlots = false;
                }
            }
        }"
    >
        {{-- Breadcrumb (Screen 03) --}}
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

        {{-- Main Detail Grid: Galeri Kiri + Info & Form Kanan (Screen 03) --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-start">

            {{-- Kolom Kiri: Galeri Foto + 4 Thumbnail (Screen 03) --}}
            <div class="lg:col-span-6 space-y-4">
                {{-- Foto Utama --}}
                <div class="relative w-full rounded-3xl overflow-hidden bg-blush border border-blush-dark/60 shadow-soft">
                    <x-product-image :src="$product->image_url ?? null" :alt="$product->name" aspect="4/5" />

                    @if($product->is_fresh_bake)
                        <div class="absolute top-4 left-4 z-10">
                            <x-badge variant="butter" size="sm">
                                Fresh Bake
                            </x-badge>
                        </div>
                    @endif
                </div>

                {{-- 4 Thumbnail Interaktif [2] [3] [4] [5] --}}
                <div class="grid grid-cols-4 gap-3">
                    @for($i = 0; $i < 4; $i++)
                        <button
                            type="button"
                            @click="activeThumb = {{ $i }}"
                            :class="activeThumb === {{ $i }} ? 'border-strawberry-deep ring-2 ring-strawberry-deep' : 'border-blush-dark opacity-70 hover:opacity-100'"
                            class="aspect-square rounded-2xl bg-blush border transition-all overflow-hidden p-1 flex items-center justify-center cursor-pointer shadow-sm"
                        >
                            <span class="font-serif font-black text-xs text-strawberry-deep">
                                [{{ $i + 1 }}]
                            </span>
                        </button>
                    @endfor
                </div>
            </div>

            {{-- Kolom Kanan: Detail Produk & Form Pesan (Screen 03) --}}
            <div class="lg:col-span-6 space-y-6">

                {{-- Header Produk --}}
                <div class="space-y-2">
                    @if($product->is_fresh_bake)
                        <x-badge variant="butter" size="xs">Fresh Bake</x-badge>
                    @else
                        <x-badge variant="blush" size="xs">Pre-Order</x-badge>
                    @endif

                    <h1 class="font-serif font-black text-3xl sm:text-4xl text-cocoa tracking-tight">
                        {{ $product->name }}
                    </h1>

                    {{-- Harga Dinamis & Rating --}}
                    <div class="flex items-center gap-3 pt-1">
                        <span class="font-sans font-black text-2xl sm:text-3xl text-strawberry-deep" x-text="activePriceFormatted">
                            {{ $product->formattedPrice() }}
                        </span>

                        <span class="text-xs sm:text-sm font-bold text-butter flex items-center gap-1 bg-white px-2.5 py-1 rounded-full border border-blush shadow-sm">
                            <span>&#9733;</span>
                            <span class="text-cocoa">{{ number_format($product->rating, 1) }}</span>
                        </span>
                    </div>

                    @if($product->description)
                        <p class="font-sans text-sm text-cocoa-muted leading-relaxed pt-1">
                            {{ $product->description }}
                        </p>
                    @endif
                </div>

                {{-- Form Pemesanan / Keranjang --}}
                <form action="{{ route('cart.add') }}" method="POST" class="space-y-6 pt-2">
                    @csrf
                    <input type="hidden" name="variant_id" :value="activeVariantId">

                    {{-- 1. Pilihan Ukuran / Varian --}}
                    <div class="space-y-2.5">
                        <label class="block font-serif font-bold text-sm text-cocoa">
                            Ukuran
                        </label>
                        <div class="flex flex-wrap gap-2.5">
                            <template x-for="v in variants" :key="v.id">
                                <button
                                    type="button"
                                    @click="selectVariant(v)"
                                    :class="activeVariantId === v.id ? 'bg-blush border-strawberry-deep text-strawberry-deep font-black ring-2 ring-strawberry-deep shadow-sm' : 'bg-white border-blush-dark text-cocoa hover:bg-blush/30'"
                                    class="px-4 py-2.5 rounded-full text-xs sm:text-sm border transition-all cursor-pointer font-sans"
                                >
                                    <span x-text="v.name + ' · ' + v.formatted"></span>
                                </button>
                            </template>
                        </div>
                    </div>

                    {{-- 2. Ucapan Kartu (Maks 40 Karakter) --}}
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <label for="greeting_card" class="font-serif font-bold text-sm text-cocoa">
                                Ucapan kartu (maks 40 karakter)
                            </label>
                            <span class="text-[11px] font-bold" :class="greeting.length >= 40 ? 'text-strawberry-deep' : 'text-cocoa-muted'">
                                <span x-text="greeting.length">0</span>/40
                            </span>
                        </div>

                        <input
                            type="text"
                            id="greeting_card"
                            name="greeting_card"
                            x-model="greeting"
                            maxlength="40"
                            placeholder="Selamat ulang tahun..."
                            class="w-full px-4 py-3 rounded-2xl bg-white border border-blush-dark text-sm text-cocoa placeholder:text-cocoa-muted/50 focus:outline-none focus:ring-2 focus:ring-strawberry-deep shadow-sm"
                        />
                    </div>

                    {{-- 3. Tanggal & Slot Pemesanan --}}
                    <div class="space-y-3">
                        <label class="block font-serif font-bold text-sm text-cocoa">
                            Tanggal &amp; slot
                        </label>

                        {{-- Tanggal Selection Chips --}}
                        <div class="flex items-center gap-2 overflow-x-auto pb-1.5 [scrollbar-width:none] [-ms-overflow-style:none] [&::-webkit-scrollbar]:hidden">
                            @foreach($dateOptions as $opt)
                                <button
                                    type="button"
                                    @click="updateDate('{{ $opt['value'] }}')"
                                    :class="selectedDate === '{{ $opt['value'] }}' ? 'bg-strawberry-deep text-white font-black shadow-pill' : 'bg-white border border-blush-dark text-cocoa hover:bg-blush/30'"
                                    class="px-3 py-2 rounded-2xl text-xs flex-shrink-0 transition-all cursor-pointer text-center"
                                >
                                    <span class="block text-[10px] uppercase font-bold opacity-80">{{ $opt['day_name'] }}</span>
                                    <span class="font-black text-xs sm:text-sm">{{ $opt['formatted'] }}</span>
                                </button>
                            @endforeach
                        </div>
                        <input type="hidden" name="fulfillment_date" :value="selectedDate">

                        {{-- Slot Jam Chips (10.00, 13.00, 16.00) --}}
                        <div class="flex flex-wrap gap-2 pt-1">
                            <template x-for="s in slots" :key="s.time_slot">
                                <button
                                    type="button"
                                    @click="if (s.is_available) selectedSlot = s.time_slot"
                                    :disabled="!s.is_available"
                                    :class="{
                                        'bg-blush border-strawberry-deep text-strawberry-deep font-black ring-2 ring-strawberry-deep shadow-sm': selectedSlot === s.time_slot && s.is_available,
                                        'bg-white border-blush-dark text-cocoa hover:bg-blush/30 cursor-pointer': selectedSlot !== s.time_slot && s.is_available,
                                        'bg-gray-100 border-gray-200 text-gray-400 cursor-not-allowed opacity-50 line-through': !s.is_available
                                    }"
                                    class="px-3.5 sm:px-4 py-2 sm:py-2.5 rounded-full text-xs sm:text-sm border transition-all font-sans font-bold flex items-center gap-1.5"
                                >
                                    <span x-text="s.time_slot.replace(':', '.')"></span>
                                    <span x-show="!s.is_available" class="text-[10px] font-normal no-underline">(Penuh)</span>
                                </button>
                            </template>
                        </div>
                        <input type="hidden" name="fulfillment_slot" :value="selectedSlot">

                        {{-- Slot Availability Feedback --}}
                        <p class="text-xs text-cocoa-muted flex items-center gap-1.5 pt-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            <span>Slot tersedia &middot; lead time minimal H-{{ $product->lead_time_days }}</span>
                        </p>
                    </div>

                    {{-- 4. Quantity Stepper + CTA Tambah ke Keranjang --}}
                    <div class="flex items-center gap-2.5 sm:gap-4 pt-2">
                        <div class="flex-shrink-0">
                            <x-qty-stepper :initial="1" :min="1" :max="10" name="quantity" />
                        </div>

                        <div class="flex-1">
                            <button
                                type="submit"
                                :disabled="!selectedSlot"
                                class="w-full bg-strawberry-deep text-white hover:bg-strawberry-hover active:scale-[0.98] py-3 sm:py-3.5 px-3 sm:px-6 rounded-full font-bold text-xs sm:text-base transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-strawberry-deep shadow-pill flex items-center justify-center gap-1.5 sm:gap-2 cursor-pointer disabled:opacity-50 disabled:pointer-events-none whitespace-nowrap"
                            >
                                <svg class="w-4 h-4 sm:w-5 sm:h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                </svg>
                                <span>Tambah ke Keranjang</span>
                            </button>
                        </div>
                    </div>
                </form>

                {{-- 5. Detail Info Box (Porsi, Alergen, Daya Tahan, Bahan) --}}
                <div class="bg-white rounded-2xl p-5 border border-blush shadow-soft space-y-3 text-xs sm:text-sm">
                    @if($product->portion_info)
                        <div class="font-bold text-cocoa">
                            {{ $product->portion_info }}
                        </div>
                    @endif

                    <div class="text-cocoa-muted space-y-1.5 leading-relaxed pt-1 border-t border-blush/80">
                        @if($product->allergens)
                            <p><strong>Alergen:</strong> {{ $product->allergens }}</p>
                        @endif

                        @if($product->shelf_life)
                            <p><strong>Daya tahan:</strong> {{ $product->shelf_life }}</p>
                        @endif

                        @if($product->ingredients)
                            <p><strong>Bahan:</strong> {{ $product->ingredients }}</p>
                        @endif
                    </div>
                </div>

            </div>
        </div>

        {{-- 6. Produk Terkait --}}
        @if($relatedProducts->isNotEmpty())
            <section class="space-y-6 pt-10 border-t border-blush">
                <div class="flex items-baseline justify-between">
                    <h2 class="font-serif font-bold text-2xl text-cocoa">
                        Produk Terkait
                    </h2>
                    <a href="{{ route('catalog.index') }}" class="text-xs sm:text-sm font-bold text-strawberry-deep hover:underline">
                        Lihat Menu Lain &rarr;
                    </a>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6">
                    @foreach($relatedProducts as $rel)
                        <x-product-card
                            :product="$rel"
                            :href="route('product.show', $rel->slug)"
                        />
                    @endforeach
                </div>
            </section>
        @endif

    </div>
</x-shop.layout>
