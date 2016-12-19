@if($entity instanceof \App\Models\Einheiten)
    <span>
    <i class="mdi mdi-crop-square"></i>
    <a href="{{ route('web::uebersicht::legacy', ['anzeigen' => 'einheit', 'einheit_id' => $entity->EINHEIT_ID]) }}">{{ $entity->EINHEIT_KURZNAME }}</a>
    <a><i class="mdi mdi-view-list"></i></a>
    </span>
@elseif($entity instanceof \App\Models\Haeuser)
    <span>
    <i class="mdi mdi-home"></i>
        {{ $entity->HAUS_STRASSE }} {{ $entity->HAUS_NUMMER }}
        <a href="{{ route('web::einheiten::legacy', ['einheit_raus' => 'einheit_kurz', 'haus_id' => $entity->HAUS_ID]) }}"><i
                    class="mdi mdi-view-list"></i></a>
    </span>
@elseif($entity instanceof \App\Models\Objekte)
    <span>
    <i class="mdi mdi-city"></i>
        {{ $entity->OBJEKT_KURZNAME }}
        <a href="{{ route('web::haeuser::legacy', ['haus_raus' => 'haus_kurz', 'objekt_id' => $entity->OBJEKT_ID]) }}"><i
                    class="mdi mdi-view-list"></i></a>
    </span>
@elseif($entity instanceof \App\Models\Kaufvertraege)
    <span>
    <i class="mdi mdi-checkbox-blank-circle"></i>
        KV-{{ $entity->ID }}
        <i class="mdi mdi-view-list"></i>
    </span>
@elseif($entity instanceof \App\Models\Mietvertraege)
    <span>
    <i class="mdi mdi-checkbox-blank-circle-outline"></i>
        MV-{{ $entity->MIETVERTRAG_ID }}
        <i class="mdi mdi-view-list"></i>
    </span>
@endif