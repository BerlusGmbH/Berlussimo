<span>
    <i class="mdi mdi-checkbox-blank-circle-outline"></i>
        <a href="{{ route('web::uebersicht::legacy', ['anzeigen' => 'einheit', 'einheit_id' => $entity->EINHEIT_ID, 'mietvertrag_id' => $entity->MIETVERTRAG_ID]) }}">MV-{{ $entity->MIETVERTRAG_ID }}</a>
        <i class="mdi mdi-view-list"></i>
</span>