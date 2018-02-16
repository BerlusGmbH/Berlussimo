<span style="white-space: nowrap">
    @php
        if(!isset($icons))
        {
            $icons = true;
        };
    @endphp
    @if($icons)
        <i class="mdi mdi-shovel tooltipped" data-position="bottom" data-delay="50" data-tooltip="Baustelle"></i>
    @endif
    {{ $entity->BEZ }}
</span>