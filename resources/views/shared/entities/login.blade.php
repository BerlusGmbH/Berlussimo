<span style="white-space: nowrap">
    @php
        if(!isset($icons))
        {
            $icons = true;
        };
    @endphp
    @if($icons)
        <i class="mdi mdi-key tooltipped" data-position="bottom" data-delay="50" data-tooltip="Login"></i>
    @endif
    Aktiv: {{$entity->trashed() ? 'Nein' : 'Ja'}}
</span>