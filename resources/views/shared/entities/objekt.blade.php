<span style="white-space: nowrap">
    @php
        if(!isset($icons))
        {
            $icons = true;
        };
    @endphp
    @if($icons)
        <i class="mdi mdi-city tooltipped" data-position="bottom" data-delay="50" data-tooltip="Objekt"></i>
    @endif
    <a href="{{ route('web::objekte::show', ['id' => $entity->OBJEKT_ID]) }}">{{ $entity->OBJEKT_KURZNAME }}</a>
    @if($icons)
        <a href="{{ route('web::haeuser::index', ['q' => '!haus(objekt(id=' . $entity->OBJEKT_ID . '))']) }}">
        <i class="mdi mdi-view-list"></i></a>
    @endif
</span>