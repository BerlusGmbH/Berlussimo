<span style="white-space: nowrap">
    @php
        if(!isset($icons))
        {
            $icons = true;
        };
    @endphp
    @if($icons)
        <i class="mdi mdi-cube tooltipped" data-position="bottom" data-delay="50" data-tooltip="Wirtschaftseinheit"></i>
    @endif
    {{ $entity->W_NAME }}
</span>