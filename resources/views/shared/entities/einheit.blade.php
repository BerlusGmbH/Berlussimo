<span style="white-space: nowrap">
    @php
        if(!isset($icons))
        {
            $icons = true;
        };
    @endphp
    @if($icons)
        <i class="mdi mdi-cube-outline tooltipped" data-position="bottom" data-delay="50" data-tooltip="Einheit"></i>
        @if($entity->TYP == 'Wohnraum')
            <i class="mdi mdi-home tooltipped" data-position="bottom" data-delay="50" data-tooltip="Wohnraum"></i>
        @elseif($entity->TYP == 'Gewerbe')
            <i class="mdi mdi-store tooltipped" data-position="bottom" data-delay="50" data-tooltip="Gewerbe"></i>
        @elseif($entity->TYP == 'Stellplatz')
            <i class="mdi mdi-car tooltipped" data-position="bottom" data-delay="50" data-tooltip="Stellplatz"></i>
        @elseif($entity->TYP == 'Garage')
            <i class="mdi mdi-garage tooltipped" data-position="bottom" data-delay="50" data-tooltip="Garage"></i>
        @elseif($entity->TYP == 'Keller')
            <i class="mdi mdi-ghost tooltipped" data-position="bottom" data-delay="50" data-tooltip="Keller"></i>
        @elseif($entity->TYP == 'Freiflaeche')
            <i class="mdi mdi-nature-people tooltipped" data-position="bottom" data-delay="50" data-tooltip="Freifläche"></i>
        @elseif($entity->TYP == 'Wohneigentum')
            <i class="mdi mdi-home-variant tooltipped" data-position="bottom" data-delay="50" data-tooltip="Wohneigentum"></i>
        @elseif($entity->TYP == 'Werbeflaeche')
            <i class="mdi mdi-newspaper tooltipped" data-position="bottom" data-delay="50" data-tooltip="Werbefläche"></i>
        @endif
    @endif
    <a href="{{ route('web::einheiten::show', ['id' => $entity->EINHEIT_ID]) }}">{{ $entity->EINHEIT_KURZNAME }}</a>
    @if($icons)
        <a href="{{ route('web::personen::index', ['q' => '!person(mietvertrag(einheit(id=' . $entity->EINHEIT_ID . ')))']) }}"><i
                    class="mdi mdi-view-list"></i></a>
    @endif
</span>