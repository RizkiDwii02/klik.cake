@props([
    'currentStep' => 1, // 1: Diterima, 2: Dipanggang, 3: Siap, 4: Selesai
])

@php
$steps = [
    1 => 'Diterima',
    2 => 'Dipanggang',
    3 => 'Siap',
    4 => 'Selesai',
];
@endphp

<div {{ $attributes->merge(['class' => 'w-full py-4']) }}>
    <div class="relative flex items-center justify-between">
        {{-- Connecting background line --}}
        <div class="absolute left-6 right-6 top-1/2 -translate-y-1/2 h-1 bg-blush-dark rounded-full -z-0"></div>

        {{-- Connecting active line --}}
        @php
            $activePercent = match($currentStep) {
                1 => '0%',
                2 => '33%',
                3 => '66%',
                4 => '100%',
                default => '0%',
            };
        @endphp
        <div
            class="absolute left-6 top-1/2 -translate-y-1/2 h-1 bg-strawberry-deep rounded-full transition-all duration-500 -z-0"
            style="width: calc({{ $activePercent }} - 12px);"
        ></div>

        {{-- Step circles and labels --}}
        @foreach($steps as $number => $label)
            @php
                $isCompleted = $number < $currentStep;
                $isCurrent = $number == $currentStep;
            @endphp

            <div class="flex flex-col items-center relative z-10">
                <div class="w-10 h-10 md:w-11 md:h-11 rounded-full flex items-center justify-center font-bold text-sm transition-all duration-300 shadow-sm
                    @if($isCompleted)
                        bg-strawberry-deep text-white
                    @elseif($isCurrent)
                        bg-strawberry-deep text-white ring-4 ring-strawberry-light scale-110
                    @else
                        bg-white border-2 border-blush-dark text-cocoa-muted
                    @endif
                ">
                    @if($isCompleted)
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                    @else
                        {{ $number }}
                    @endif
                </div>

                <span class="mt-2 text-xs md:text-sm font-bold tracking-tight text-center
                    @if($isCurrent)
                        text-strawberry-deep
                    @elseif($isCompleted)
                        text-cocoa
                    @else
                        text-cocoa-muted/70
                    @endif
                ">
                    {{ $label }}
                </span>
            </div>
        @endforeach
    </div>
</div>
