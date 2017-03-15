<div class="detail">
    @if(!empty(trim($entity->DETAIL_INHALT)))
        <i class="mdi mdi-email"></i>
    @endif
    <span>
        @if(!empty(trim($entity->DETAIL_INHALT)))
            {!!trim($entity->detail_inhalt_with_br)!!}
        @endif
        @if(!empty(trim($entity->DETAIL_BEMERKUNG)))
            <br><i class="mdi mdi-note"></i> {{trim($entity->DETAIL_BEMERKUNG)}}
        @endif
    </span>
</div>