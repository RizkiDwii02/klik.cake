@props([
    'variant' => 'primary', // primary, secondary, outline, ghost
    'size' => 'md',        // sm, md, lg
    'as' => 'button',      // button, a
    'href' => null,
])

@php
$baseClasses = 'inline-flex items-center justify-center font-bold font-sans rounded-full transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:pointer-events-none cursor-pointer';

$sizeClasses = match($size) {
    'sm' => 'px-4 py-2 text-xs gap-1.5 shadow-sm',
    'lg' => 'px-8 py-3.5 text-base gap-2.5 shadow-pill',
    default => 'px-6 py-2.5 text-sm gap-2 shadow-pill',
};

$variantClasses = match($variant) {
    'primary' => 'bg-strawberry-deep text-white hover:bg-strawberry-hover focus:ring-strawberry-deep active:scale-[0.98]',
    'secondary' => 'bg-blush text-cocoa hover:bg-blush-dark focus:ring-blush active:scale-[0.98] shadow-none',
    'butter' => 'bg-butter text-cocoa font-extrabold hover:bg-butter-dark focus:ring-butter active:scale-[0.98] shadow-soft',
    'outline' => 'bg-transparent border-2 border-strawberry-deep text-strawberry-deep hover:bg-strawberry-light focus:ring-strawberry-deep active:scale-[0.98] shadow-none',
    'ghost' => 'bg-transparent text-cocoa hover:bg-blush focus:ring-blush shadow-none',
    default => 'bg-strawberry-deep text-white hover:bg-strawberry-hover focus:ring-strawberry-deep shadow-pill',
};

$classes = "{$baseClasses} {$sizeClasses} {$variantClasses}";
@endphp

@if($as === 'a' || $href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button {{ $attributes->merge(['type' => 'button', 'class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif
