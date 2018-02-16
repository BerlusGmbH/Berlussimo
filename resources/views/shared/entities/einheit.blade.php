<span style="white-space: nowrap">
    @php
        if(!isset($icons))
        {
            $icons = true;
        };
    @endphp
    @if($icons)
        @if($entity->hasHinweis())
            @php($tooltip = $entity->hinweise->implode('DETAIL_INHALT', '<hr>'))
            <i class="mdi mdi-alert tooltipped red-text" data-tooltip="{{$tooltip}}"></i><i
        @else
            <i
        @endif
        @if($entity->vermietet)
            class="mdi mdi-cube tooltipped" data-tooltip="Einheit (Vermietet)"></i><i
        @else
            class="mdi mdi-cube-outline tooltipped" data-tooltip="Einheit (Leer)"></i><i
        @endif
        @if($entity->TYP == 'Wohnraum')
            class="mdi mdi-home tooltipped" data-tooltip="Wohnraum"></i>
        @elseif($entity->TYP == 'Gewerbe')
            class="mdi mdi-store tooltipped" data-tooltip="Gewerbe"></i>
        @elseif($entity->TYP == 'Stellplatz')
            class="mdi mdi-car tooltipped" data-tooltip="Stellplatz"></i>
        @elseif($entity->TYP == 'Garage')
            class="mdi mdi-garage tooltipped" data-tooltip="Garage"></i>
        @elseif($entity->TYP == 'Keller')
            class="mdi mdi-ghost tooltipped" data-tooltip="Keller"></i>
        @elseif($entity->TYP == 'Freiflaeche')
            class="mdi mdi-nature-people tooltipped" data-tooltip="Freifläche"></i>
        @elseif($entity->TYP == 'Wohneigentum')
            class="mdi mdi-home-variant tooltipped" data-tooltip="Wohneigentum"></i>
        @elseif($entity->TYP == 'Werbeflaeche')
            class="mdi mdi-newspaper tooltipped" data-tooltip="Werbefläche"></i>
        @else
            ></i>
        @endif
    @endif
        <a href="{{ route('web::einheiten::show', ['id' => $entity->EINHEIT_ID]) }}">{{ $entity->EINHEIT_KURZNAME }}</>
    @if($icons)
        <a href="{{ route('web::personen::index', ['q' => '!person(mietvertrag(einheit(id=' . $entity->EINHEIT_ID . ')))']) }}"><i
                    class="mdi mdi-view-list"></i></a>
    @endif
</span>