<span style="white-space: nowrap">
    @if($entity->isActive())
        <i class="mdi mdi-checkbox-blank tooltipped" data-position="bottom" data-delay="50"
           data-tooltip="Kaufvertrag (Aktiv)"></i>
    @else
        <i class="mdi mdi-checkbox-blank-outline tooltipped" data-position="bottom" data-delay="50"
           data-tooltip="Kaufvertrag (Inaktiv)"></i>
    @endif
    <a href="{{ route('web::weg::legacy', ['option' => 'einheit_uebersicht', 'einheit_id' => $entity->EINHEIT_ID]) }}">KV-{{ $entity->ID }}</a>
</span>