<div class="detail">
    @if(!empty(trim($entity->DETAIL_INHALT)))
        <i class="mdi mdi-fax"></i>
    @endif
    <span>
        @if(!empty(trim($entity->DETAIL_INHALT)))
            <a href="fax:{{trim($entity->DETAIL_INHALT)}}">{{$entity->DETAIL_INHALT}}</a>
        @endif
        @if(!empty(trim($entity->DETAIL_BEMERKUNG)))
            <i class="mdi mdi-note"></i> {{trim($entity->DETAIL_BEMERKUNG)}}
        @endif
    </span>
</div>