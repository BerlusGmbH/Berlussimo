<span style="white-space: nowrap">
    @php
        if(!isset($icons))
        {
            $icons = true;
        };
    @endphp
    @if($icons)
        <i class="mdi mdi-package tooltipped" data-position="bottom" data-delay="50" data-tooltip="Lager"></i>
    @endif
    {{ $entity->LAGER_NAME }}
</span>