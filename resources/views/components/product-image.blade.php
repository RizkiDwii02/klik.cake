@props([
    'src' => null,
    'alt' => 'Foto produk',
    'aspect' => '4/5', // 4/5 default as per PDF specification
])

<div {{ $attributes->merge(['class' => 'relative w-full overflow-hidden rounded-2xl bg-blush flex items-center justify-center']) }} style="aspect-ratio: {{ $aspect }};">
    @if($src && file_exists(public_path($src)))
        <img
            src="{{ asset($src) }}"
            alt="{{ $alt }}"
            class="w-full h-full object-cover transition-transform duration-300 hover:scale-105"
            loading="lazy"
        />
    @else
        {{-- Stylized Blush Bakery Illustration Placeholder (Non-stock) --}}
        <div class="w-full h-full flex flex-col items-center justify-center p-6 text-center select-none bg-gradient-to-b from-blush to-blush-dark/40">
            <svg class="w-20 h-20 text-strawberry-deep/70 drop-shadow-sm mb-2" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                <!-- Cake Stand -->
                <path d="M22 54H42" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
                <path d="M32 46V54" stroke="currentColor" stroke-width="2.5"/>
                <path d="M12 46H52" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>

                <!-- Bottom Layer -->
                <path d="M16 46V36C16 34.8954 16.8954 34 18 34H46C47.1046 34 48 34.8954 48 36V46" fill="#F8C8D5" stroke="currentColor" stroke-width="2.5"/>

                <!-- Cream Frosting Drips -->
                <path d="M16 38C18 41 20 41 22 38C24 41 26 41 28 38C30 41 32 41 34 38C36 41 38 41 40 38C42 41 44 41 46 38C47 39 48 39 48 38" fill="white" stroke="currentColor" stroke-width="1.5"/>

                <!-- Top Layer -->
                <path d="M21 34V26C21 24.8954 21.8954 24 23 24H41C42.1046 24 43 24.8954 43 26V34" fill="#FFF5F7" stroke="currentColor" stroke-width="2.5"/>

                <!-- Strawberry on top -->
                <path d="M32 15C30 15 28 17 28 19C28 22 32 25 32 25C32 25 36 22 36 19C36 17 34 15 32 15Z" fill="#B82850"/>
                <path d="M30 14C31 15 33 15 34 14" stroke="#488B49" stroke-width="1.5" stroke-linecap="round"/>
                <circle cx="31" cy="18" r="0.5" fill="#F4B942"/>
                <circle cx="33" cy="19" r="0.5" fill="#F4B942"/>
            </svg>
            <span class="text-xs font-serif font-bold text-cocoa/80 tracking-wide">{{ $alt }}</span>
            <span class="text-[10px] font-sans text-cocoa-muted mt-0.5">Dipanggang Segar Makassar</span>
        </div>
    @endif
</div>
