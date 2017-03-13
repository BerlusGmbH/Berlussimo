<span style="white-space: nowrap">
    @php
        if(!isset($icons))
        {
            $icons = true;
        };
    @endphp
    @if($icons)
        <i class="mdi mdi-domain tooltipped" data-position="bottom" data-delay="50" data-tooltip="Haus"></i>
    @endif
    <a href="{{ route('web::haeuser::show', ['id' => $entity->HAUS_ID]) }}">{{ $entity->HAUS_STRASSE }} {{ $entity->HAUS_NUMMER }}</a>
    @if($icons)
        <a href="{{ route('web::einheiten::index', ['q' => '!einheit(haus(id=' . $entity->HAUS_ID . '))']) }}"><i
                    class="mdi mdi-view-list"></i></a>
    @endif
</span>