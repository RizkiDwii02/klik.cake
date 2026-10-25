@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Navigasi Halaman" class="flex items-center gap-2">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <span class="w-9 h-9 rounded-full flex items-center justify-center text-gray-300 border border-gray-200 cursor-not-allowed select-none text-xs">
                &lsaquo;
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="w-9 h-9 rounded-full flex items-center justify-center text-cocoa bg-white border border-blush-dark hover:border-strawberry-deep hover:text-strawberry-deep transition-colors text-xs font-bold shadow-sm">
                &lsaquo;
            </a>
        @endif

        {{-- Pagination Elements --}}
        @foreach ($elements as $element)
            {{-- "Three Dots" Separator --}}
            @if (is_string($element))
                <span class="w-9 h-9 rounded-full flex items-center justify-center text-cocoa-muted text-xs select-none">
                    {{ $element }}
                </span>
            @endif

            {{-- Array Of Links --}}
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span aria-current="page" class="w-9 h-9 rounded-full flex items-center justify-center font-bold text-xs bg-strawberry-deep text-white shadow-pill select-none">
                            {{ $page }}
                        </span>
                    @else
                        <a href="{{ $url }}" class="w-9 h-9 rounded-full flex items-center justify-center font-bold text-xs text-cocoa bg-white border border-blush-dark hover:border-strawberry-deep hover:text-strawberry-deep transition-colors shadow-sm">
                            {{ $page }}
                        </a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="w-9 h-9 rounded-full flex items-center justify-center text-cocoa bg-white border border-blush-dark hover:border-strawberry-deep hover:text-strawberry-deep transition-colors text-xs font-bold shadow-sm">
                &rsaquo;
            </a>
        @else
            <span class="w-9 h-9 rounded-full flex items-center justify-center text-gray-300 border border-gray-200 cursor-not-allowed select-none text-xs">
                &rsaquo;
            </span>
        @endif
    </nav>
@endif
