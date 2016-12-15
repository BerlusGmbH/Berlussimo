@if($entity instanceof \App\Models\Einheiten)
    <span>
    <i class="material-icons" style="font-size: 1rem">crop_free</i>
    <a href="{{ route('web::uebersicht::legacy', ['anzeigen' => 'einheit', 'einheit_id' => $entity->EINHEIT_ID]) }}">{{ $entity->EINHEIT_KURZNAME }}</a>
    <a><i class="material-icons" style="font-size: 1rem">list</i></a>
    </span>
@elseif($entity instanceof \App\Models\Haeuser)
    <span>
    <i class="material-icons" style="font-size: 1rem">home</i>
        {{ $entity->HAUS_STRASSE }} {{ $entity->HAUS_NUMMER }}
        <a href="{{ route('web::einheiten::legacy', ['einheit_raus' => 'einhiet_kurz', 'haus_id' => $entity->HAUS_ID]) }}"><i
                    class="material-icons" style="font-size: 1rem">list</i></a>
    </span>
@elseif($entity instanceof \App\Models\Objekte)
    <span>
    <i class="material-icons" style="font-size: 1rem">location_city</i>
        {{ $entity->OBJEKT_KURZNAME }}
        <a href="{{ route('web::haeuser::legacy', ['haus_raus' => 'haus_kurz', 'objekt_id' => $entity->OBJEKT_ID]) }}"><i
                    class="material-icons" style="font-size: 1rem">list</i></a>
    </span>
@endif