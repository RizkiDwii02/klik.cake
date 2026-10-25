<x-shop.layout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-16">

        {{-- Hero Header --}}
        <section class="border-b border-blush pb-8">
            <x-badge variant="butter" size="sm">Styleguide &amp; Design System</x-badge>
            <h1 class="font-serif font-black text-3xl sm:text-4xl lg:text-5xl text-cocoa mt-3">
                Klik.Cake Design Tokens &amp; Components
            </h1>
            <p class="font-sans text-base sm:text-lg text-cocoa-muted mt-2 max-w-2xl">
                Identitas visual hangat, pastel, dan menggoda untuk toko pre-order kue &amp; roti segar Makassar by Pinky Cake &amp; Bakery.
            </p>
        </section>

        {{-- 1. Design Tokens: Color Palette --}}
        <section class="space-y-6">
            <div>
                <h2 class="font-serif font-bold text-2xl text-cocoa">1. Design Tokens &mdash; Color Palette</h2>
                <p class="text-sm text-cocoa-muted">Kombinasi warna hangat &amp; pastel sesuai acuan PDF dan prinsip WCAG AA.</p>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
                {{-- Cream --}}
                <div class="bg-white rounded-2xl p-3 border border-blush shadow-soft">
                    <div class="h-20 rounded-xl bg-cream border border-blush-dark/50 mb-2"></div>
                    <p class="font-bold text-sm text-cocoa">Cream</p>
                    <p class="text-xs text-cocoa-muted font-mono">#FFF8F0</p>
                    <span class="text-[10px] text-cocoa-light">Latar belakang utama</span>
                </div>

                {{-- Strawberry --}}
                <div class="bg-white rounded-2xl p-3 border border-blush shadow-soft">
                    <div class="h-20 rounded-xl bg-strawberry mb-2"></div>
                    <p class="font-bold text-sm text-cocoa">Strawberry</p>
                    <p class="text-xs text-cocoa-muted font-mono">#E5527A</p>
                    <span class="text-[10px] text-cocoa-light">Aksen brand utama</span>
                </div>

                {{-- Strawberry Deep --}}
                <div class="bg-white rounded-2xl p-3 border border-blush shadow-soft">
                    <div class="h-20 rounded-xl bg-strawberry-deep mb-2"></div>
                    <p class="font-bold text-sm text-strawberry-deep">Strawberry Deep</p>
                    <p class="text-xs text-cocoa-muted font-mono">#B82850</p>
                    <span class="text-[10px] font-bold text-emerald-600">CTA (WCAG AA 5.6:1)</span>
                </div>

                {{-- Blush --}}
                <div class="bg-white rounded-2xl p-3 border border-blush shadow-soft">
                    <div class="h-20 rounded-xl bg-blush mb-2"></div>
                    <p class="font-bold text-sm text-cocoa">Blush</p>
                    <p class="text-xs text-cocoa-muted font-mono">#FDE3EA</p>
                    <span class="text-[10px] text-cocoa-light">Latar kartu / section</span>
                </div>

                {{-- Butter --}}
                <div class="bg-white rounded-2xl p-3 border border-blush shadow-soft">
                    <div class="h-20 rounded-xl bg-butter mb-2"></div>
                    <p class="font-bold text-sm text-cocoa">Butter</p>
                    <p class="text-xs text-cocoa-muted font-mono">#F4B942</p>
                    <span class="text-[10px] text-cocoa-light">Badge Fresh Bake / promo</span>
                </div>

                {{-- Cocoa --}}
                <div class="bg-white rounded-2xl p-3 border border-blush shadow-soft">
                    <div class="h-20 rounded-xl bg-cocoa mb-2"></div>
                    <p class="font-bold text-sm text-white bg-cocoa px-1.5 py-0.5 rounded inline-block">Cocoa</p>
                    <p class="text-xs text-cocoa-muted font-mono">#3B2320</p>
                    <span class="text-[10px] text-cocoa-light">Teks judul &amp; paragraf</span>
                </div>
            </div>
        </section>

        {{-- 2. Typography --}}
        <section class="space-y-6">
            <div>
                <h2 class="font-serif font-bold text-2xl text-cocoa">2. Typography (Fraunces &amp; Nunito)</h2>
                <p class="text-sm text-cocoa-muted">Fraunces untuk judul &amp; nama produk; Nunito untuk teks body, harga, dan tombol.</p>
            </div>

            <div class="bg-white rounded-2xl p-6 border border-blush shadow-soft space-y-6">
                <div>
                    <span class="text-xs uppercase tracking-wider font-bold text-strawberry-deep">Fraunces (Serif Hangat)</span>
                    <h3 class="font-serif font-black text-3xl sm:text-4xl text-cocoa mt-1">
                        Klik, dipanggang, sampai.
                    </h3>
                    <h4 class="font-serif font-bold text-2xl text-cocoa mt-2">
                        Strawberry Cream Cake &mdash; Rp185.000
                    </h4>
                </div>

                <hr class="border-blush" />

                <div>
                    <span class="text-xs uppercase tracking-wider font-bold text-strawberry-deep">Nunito (Sans Ramah)</span>
                    <p class="font-sans text-base text-cocoa mt-1 leading-relaxed max-w-3xl">
                        Pesan roti &amp; kue segar dari Makassar, pilih jadwal, bayar, pantau sampai matang. Setiap sajian dibuat dari mentega wisman pilihan dan buah stroberi segar.
                    </p>
                    <p class="font-sans font-extrabold text-xl text-cocoa mt-3">
                        Rp185.000 <span class="text-sm font-semibold text-cocoa-muted">/ 16 cm (Porsi 6&ndash;8 orang)</span>
                    </p>
                </div>
            </div>
        </section>

        {{-- 3. Blade Component: Buttons --}}
        <section class="space-y-6">
            <div>
                <h2 class="font-serif font-bold text-2xl text-cocoa">3. Blade Component: Button Pill (`x-button`)</h2>
                <p class="text-sm text-cocoa-muted">Tombol berbentuk pil hangat dengan varian strawberry deep, blush, butter, dan outline.</p>
            </div>

            <div class="bg-white rounded-2xl p-6 border border-blush shadow-soft space-y-4">
                <div class="flex flex-wrap items-center gap-3">
                    <x-button variant="primary" size="lg">Pesan Sekarang (Large)</x-button>
                    <x-button variant="primary" size="md">Tambah ke Keranjang (Default)</x-button>
                    <x-button variant="primary" size="sm">Beli &middot; Small</x-button>
                </div>

                <div class="flex flex-wrap items-center gap-3 pt-2">
                    <x-button variant="secondary">Lihat Menu (Blush)</x-button>
                    <x-button variant="butter">Promo Hari Ini (Butter)</x-button>
                    <x-button variant="outline">Detail Produk (Outline)</x-button>
                    <x-button variant="ghost">Kembali (Ghost)</x-button>
                    <x-button variant="primary" disabled>Tombol Disabled</x-button>
                </div>
            </div>
        </section>

        {{-- 4. Blade Component: Chips & Badges --}}
        <section class="space-y-6">
            <div>
                <h2 class="font-serif font-bold text-2xl text-cocoa">4. Blade Component: Chips (`x-chip`) &amp; Badges (`x-badge`)</h2>
                <p class="text-sm text-cocoa-muted">Digunakan untuk pemilihan ukuran cake, waktu slot, dan label kategori.</p>
            </div>

            <div class="bg-white rounded-2xl p-6 border border-blush shadow-soft space-y-6">
                <div>
                    <p class="text-xs font-bold text-cocoa-muted mb-2 uppercase">Variant Chips (Active, Inactive, Disabled):</p>
                    <div class="flex flex-wrap gap-2">
                        <x-chip :active="true">16 cm &middot; Rp185.000</x-chip>
                        <x-chip :active="false">20 cm &middot; Rp245.000</x-chip>
                        <x-chip :active="false" :disabled="true">24 cm &middot; Stok Habis</x-chip>
                    </div>
                </div>

                <div>
                    <p class="text-xs font-bold text-cocoa-muted mb-2 uppercase">Slot Time Chips:</p>
                    <div class="flex flex-wrap gap-2">
                        <x-chip :active="false">10.00 &ndash; 12.00</x-chip>
                        <x-chip :active="true">13.00 &ndash; 15.00 (Dipilih)</x-chip>
                        <x-chip :active="false">16.00 &ndash; 18.00</x-chip>
                    </div>
                </div>

                <div>
                    <p class="text-xs font-bold text-cocoa-muted mb-2 uppercase">Badges:</p>
                    <div class="flex flex-wrap items-center gap-2">
                        <x-badge variant="butter">Fresh Bake hari ini</x-badge>
                        <x-badge variant="butter" size="xs">Fresh Bake</x-badge>
                        <x-badge variant="blush">Pre-Order H-1</x-badge>
                        <x-badge variant="strawberry">Best Seller</x-badge>
                    </div>
                </div>
            </div>
        </section>

        {{-- 5. Blade Component: Interactive Qty Stepper --}}
        <section class="space-y-6">
            <div>
                <h2 class="font-serif font-bold text-2xl text-cocoa">5. Blade Component: Qty Stepper (`x-qty-stepper`)</h2>
                <p class="text-sm text-cocoa-muted">Stepper jumlah kue berbasis Alpine.js dengan batasan min/max dinamis.</p>
            </div>

            <div class="bg-white rounded-2xl p-6 border border-blush shadow-soft flex items-center gap-6">
                <x-qty-stepper :initial="1" :min="1" :max="10" />
                <span class="text-sm text-cocoa-muted">Coba klik tombol &minus; dan &plus; di sebelah kiri.</span>
            </div>
        </section>

        {{-- 6. Blade Component: Status Progress Bar --}}
        <section class="space-y-6">
            <div>
                <h2 class="font-serif font-bold text-2xl text-cocoa">6. Blade Component: Customer Status Progress (`x-status-progress`)</h2>
                <p class="text-sm text-cocoa-muted">Pelacak 4 langkah status pelanggan sesuai PDF Screen 07 (Diterima &rarr; Dipanggang &rarr; Siap &rarr; Selesai).</p>
            </div>

            <div class="bg-white rounded-2xl p-6 border border-blush shadow-soft space-y-8" x-data="{ currentStep: 2 }">
                <div class="flex items-center justify-between border-b border-blush pb-4">
                    <span class="font-bold text-sm text-cocoa">Simulasi Langkah Status:</span>
                    <div class="flex gap-2">
                        <template x-for="step in [1, 2, 3, 4]" :key="step">
                            <button
                                type="button"
                                @click="currentStep = step"
                                :class="currentStep === step ? 'bg-strawberry-deep text-white' : 'bg-blush text-cocoa'"
                                class="px-3 py-1 rounded-full text-xs font-bold cursor-pointer transition-colors"
                                x-text="'Langkah ' + step"
                            ></button>
                        </template>
                    </div>
                </div>

                {{-- Interactive rendering --}}
                <div x-show="currentStep === 1">
                    <p class="text-xs font-semibold text-cocoa-muted mb-1">Status: Langkah 1 &mdash; Diterima</p>
                    <x-status-progress :currentStep="1" />
                </div>
                <div x-show="currentStep === 2">
                    <p class="text-xs font-semibold text-cocoa-muted mb-1">Status: Langkah 2 &mdash; Dipanggang</p>
                    <x-status-progress :currentStep="2" />
                </div>
                <div x-show="currentStep === 3">
                    <p class="text-xs font-semibold text-cocoa-muted mb-1">Status: Langkah 3 &mdash; Siap</p>
                    <x-status-progress :currentStep="3" />
                </div>
                <div x-show="currentStep === 4">
                    <p class="text-xs font-semibold text-cocoa-muted mb-1">Status: Langkah 4 &mdash; Selesai</p>
                    <x-status-progress :currentStep="4" />
                </div>
            </div>
        </section>

        {{-- 7. Blade Component: Product Card --}}
        <section class="space-y-6">
            <div>
                <h2 class="font-serif font-bold text-2xl text-cocoa">7. Blade Component: Product Card (`x-product-card`)</h2>
                <p class="text-sm text-cocoa-muted">Kartu produk radius 16px, rasio foto 4:5, badge Fresh Bake, dan tipografi Fraunces.</p>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
                {{-- Exact Strawberry Cream Cake from DB --}}
                @if($strawberryCake)
                    <x-product-card :product="$strawberryCake" />
                @endif

                {{-- Sample Products from DB --}}
                @foreach($sampleProducts as $sample)
                    <x-product-card :product="$sample" />
                @endforeach
            </div>
        </section>

        {{-- 8. Fallback Stylized Product Image --}}
        <section class="space-y-6">
            <div>
                <h2 class="font-serif font-bold text-2xl text-cocoa">8. Stylized Placeholder Image (`x-product-image`)</h2>
                <p class="text-sm text-cocoa-muted">Bila file foto asli belum diunggah, komponen menampilkan ilustrasi kue pastel blush berkarakter (tanpa foto stok eksternal).</p>
            </div>

            <div class="max-w-xs">
                <x-product-image :src="null" alt="Strawberry Cream Cake" aspect="4/5" />
            </div>
        </section>

    </div>
</x-shop.layout>
