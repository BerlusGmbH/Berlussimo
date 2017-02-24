<span style="white-space: nowrap">
    @php
        if(!isset($icons))
        {
            $icons = true;
        };
    @endphp
    @if($icons)
        <i class="mdi mdi-worker tooltipped" data-position="bottom" data-delay="50" data-tooltip="Mitarbeiter"></i>
    @endif
    {{ $entity->name }}
</span>