<span style="white-space: nowrap">
    @if($entity->isActive())
        <i class="mdi mdi-checkbox-blank-circle tooltipped" data-position="bottom" data-delay="50"
           data-tooltip="Mietvertrag (Aktiv)"></i>
    @else
        <i class="mdi mdi-checkbox-blank-circle-outline tooltipped" data-position="bottom" data-delay="50"
           data-tooltip="Mietvertrag (Inaktiv)"></i>
    @endif
    <a href="{{ route('web::uebersicht::legacy', ['anzeigen' => 'einheit', 'einheit_id' => $entity->EINHEIT_ID, 'mietvertrag_id' => $entity->MIETVERTRAG_ID]) }}">MV-{{ $entity->MIETVERTRAG_ID }}</a>
</span>