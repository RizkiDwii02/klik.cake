@props([
    'active' => false,
    'disabled' => false,
    'value' => null,
])

@php
$baseClasses = 'inline-flex items-center justify-center px-4 py-2 rounded-full text-xs md:text-sm font-semibold transition-all duration-150 border cursor-pointer select-none';

if ($disabled) {
    $stateClasses = 'bg-gray-100 text-gray-400 border-gray-200 cursor-not-allowed opacity-60';
} elseif ($active) {
    $stateClasses = 'bg-blush border-strawberry-deep text-strawberry-deep font-bold shadow-sm ring-1 ring-strawberry-deep';
} else {
    $stateClasses = 'bg-white border-blush-dark text-cocoa hover:border-strawberry-deep/60 hover:bg-blush/30';
}

$classes = "{$baseClasses} {$stateClasses}";
@endphp

<button
    type="button"
    {{ $disabled ? 'disabled' : '' }}
    {{ $attributes->merge(['class' => $classes]) }}
>
    {{ $slot }}
</button>
