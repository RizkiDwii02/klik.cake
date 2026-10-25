@props([
    'product',
    'href' => '#',
])

@php
$name = is_object($product) ? $product->name : ($product['name'] ?? 'Nama Produk');
$formattedPrice = is_object($product)
    ? (method_exists($product, 'formattedPrice') ? $product->formattedPrice() : 'Rp' . number_format($product->minPrice(), 0, ',', '.'))
    : ($product['formatted_price'] ?? 'Rp' . number_format($product['price'] ?? 0, 0, ',', '.'));
$isFreshBake = is_object($product) ? ($product->is_fresh_bake ?? true) : ($product['is_fresh_bake'] ?? true);
$imageUrl = is_object($product) ? ($product->image_url ?? null) : ($product['image_url'] ?? null);
@endphp

<article {{ $attributes->merge(['class' => 'group flex flex-col bg-white rounded-2xl overflow-hidden p-3 transition-all duration-200 hover:-translate-y-1 hover:shadow-soft border border-blush/60']) }}>
    <a href="{{ $href }}" class="block relative w-full overflow-hidden rounded-xl">
        <x-product-image :src="$imageUrl" :alt="$name" aspect="4/5" />

        @if($isFreshBake)
            <div class="absolute top-2.5 left-2.5 z-10">
                <x-badge variant="butter" size="xs">
                    Fresh Bake
                </x-badge>
            </div>
        @endif
    </a>

    <div class="mt-3 flex flex-col flex-1 justify-between">
        <a href="{{ $href }}" class="group-hover:text-strawberry-deep transition-colors">
            <h3 class="font-serif font-bold text-sm md:text-base text-cocoa leading-snug line-clamp-2">
                {{ $name }}
            </h3>
        </a>

        <div class="mt-2 flex items-center justify-between pt-1">
            <p class="font-sans font-extrabold text-sm md:text-base text-cocoa">
                {{ $formattedPrice }}
            </p>

            <span class="text-xs font-semibold text-strawberry-deep opacity-0 group-hover:opacity-100 transition-opacity">
                Pesan &rarr;
            </span>
        </div>
    </div>
</article>
