<span style="white-space: nowrap">
    @php
        if(!isset($icons))
        {
            $icons = true;
        };
    @endphp
    @if($icons)
        <i class="mdi mdi-cube-outline tooltipped" data-position="bottom" data-delay="50" data-tooltip="Einheit"></i>
    @endif
    <a href="{{ route('web::einheiten::show', ['id' => $entity->EINHEIT_ID]) }}">{{ $entity->EINHEIT_KURZNAME }}</a>
    @if($icons)
        <a href="{{ route('web::personen::index', ['q' => '!person(mietvertrag(einheit(id=' . $entity->EINHEIT_ID . ')))']) }}"><i
                    class="mdi mdi-view-list"></i></a>
    @endif
</span>