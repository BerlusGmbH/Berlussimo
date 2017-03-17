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
            <i class="mdi mdi-alert tooltipped red-text" data-position="bottom" data-delay="50"
               data-tooltip="{{$tooltip}}"></i><i
        @else
            <i
        @endif
        @if($entity->vermietet())
            class="mdi mdi-cube tooltipped" data-position="bottom" data-delay="50" data-tooltip="Einheit (Vermietet)"></i><i
        @else
            class="mdi mdi-cube-outline tooltipped" data-position="bottom" data-delay="50" data-tooltip="Einheit (Leer)"></i><i
        @endif
        @if($entity->TYP == 'Wohnraum')
            class="mdi mdi-home tooltipped" data-position="bottom" data-delay="50" data-tooltip="Wohnraum"></i>
        @elseif($entity->TYP == 'Gewerbe')
            class="mdi mdi-store tooltipped" data-position="bottom" data-delay="50" data-tooltip="Gewerbe"></i>
        @elseif($entity->TYP == 'Stellplatz')
            class="mdi mdi-car tooltipped" data-position="bottom" data-delay="50" data-tooltip="Stellplatz"></i>
        @elseif($entity->TYP == 'Garage')
            class="mdi mdi-garage tooltipped" data-position="bottom" data-delay="50" data-tooltip="Garage"></i>
        @elseif($entity->TYP == 'Keller')
            class="mdi mdi-ghost tooltipped" data-position="bottom" data-delay="50" data-tooltip="Keller"></i>
        @elseif($entity->TYP == 'Freiflaeche')
            class="mdi mdi-nature-people tooltipped" data-position="bottom" data-delay="50" data-tooltip="Freifläche"></i>
        @elseif($entity->TYP == 'Wohneigentum')
            class="mdi mdi-home-variant tooltipped" data-position="bottom" data-delay="50" data-tooltip="Wohneigentum"></i>
        @elseif($entity->TYP == 'Werbeflaeche')
            class="mdi mdi-newspaper tooltipped" data-position="bottom" data-delay="50" data-tooltip="Werbefläche"></i>
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