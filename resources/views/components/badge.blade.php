@props([
    'variant' => 'butter', // butter, blush, strawberry
    'size' => 'sm',        // xs, sm, md
])

@php
$sizeClasses = match($size) {
    'xs' => 'px-2 py-0.5 text-[10px]',
    'md' => 'px-3 py-1.5 text-xs tracking-wide',
    default => 'px-2.5 py-1 text-[11px] font-bold tracking-tight',
};

$variantClasses = match($variant) {
    'butter' => 'bg-butter text-cocoa font-bold shadow-sm',
    'blush' => 'bg-blush text-strawberry-deep font-semibold',
    'strawberry' => 'bg-strawberry-deep text-white font-bold',
    default => 'bg-butter text-cocoa font-bold',
};
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1 rounded-full uppercase {$sizeClasses} {$variantClasses}"]) }}>
    {{ $slot }}
</span>
