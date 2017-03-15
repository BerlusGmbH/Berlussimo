<div class="detail">
    @if(!empty(trim($entity->DETAIL_INHALT)))
        <i class="mdi mdi-mail-ru"></i>
    @endif
    <span>
        @if(!empty(trim($entity->DETAIL_INHALT)))
            @if($entity->from instanceof \App\Models\Personen)
                <a href="mailto:{{trim($entity->from->PERSON_VORNAME)}} {{trim($entity->from->PERSON_NACHNAME)}} <{{trim($entity->DETAIL_INHALT)}}>">{{trim($entity->DETAIL_INHALT)}}</a>
            @else
                <a href="mailto:{{trim($entity->DETAIL_INHALT)}}>">{{trim($entity->DETAIL_INHALT)}}</a>
            @endif
        @endif
        @if(!empty(trim($entity->DETAIL_BEMERKUNG)))
            <i class="mdi mdi-note"></i> {{trim($entity->DETAIL_BEMERKUNG)}}
        @endif
    </span>
</div>