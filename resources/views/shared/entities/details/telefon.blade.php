<span>
    @if(!empty(trim($entity->DETAIL_INHALT)))
        <i class="mdi mdi-phone"></i> {{ trim($entity->DETAIL_INHALT) }}
    @endif
    @if(!empty(trim($entity->DETAIL_BEMERKUNG)))
        <i class="mdi mdi-note"></i> {{ trim($entity->DETAIL_BEMERKUNG) }}
    @endif
</span>