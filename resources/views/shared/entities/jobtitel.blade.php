<span style="white-space: nowrap">
    @php
        if(!isset($icons))
        {
            $icons = true;
        };
    @endphp
    @if($icons)
        <i class="mdi mdi-library tooltipped" data-position="bottom" data-delay="50" data-tooltip="Titel"></i>
    @endif
    @if(isset($entity))
        {{ $entity->title }}
    @else
        Mitarbeiter
    @endif
</span>