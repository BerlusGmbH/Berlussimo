@if ($paginator->hasPages())
    <ul class="pagination">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <li class="disabled"><a><i class="mdi mdi-chevron-left"></i></a></li>
        @else
            <li><a class="waves-effect" href="{{ $paginator->previousPageUrl() }}" rel="prev"><i class="mdi mdi-chevron-left"></i></a></li>
        @endif

        {{-- Pagination Elements --}}
        @foreach ($elements as $element)
            {{-- "Three Dots" Separator --}}
            @if (is_string($element))
                <li class="disabled"><span>{{ $element }}</span></li>
            @endif

            {{-- Array Of Links --}}
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <li class="active"><a>{{ $page }}</a></li>
                    @else
                        <li><a class="waves-effect" href="{{ $url }}">{{ $page }}</a></li>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <li><a class="waves-effect" href="{{ $paginator->nextPageUrl() }}" rel="next"><i class="mdi mdi-chevron-right"></i></a></li>
        @else
            <li class="disabled"><a><i class="mdi mdi-chevron-right"></i></a></li>
        @endif
    </ul>
@endif
