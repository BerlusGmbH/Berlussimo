<span>
    <i class="mdi mdi-account"></i>
        <a href="{{ route('web::personen::show', ['id' => $entity->PERSON_ID]) }}">{{ $entity->PERSON_NACHNAME }}
            @if(trim($entity->PERSON_VORNAME) != '')
                , {{ $entity->PERSON_VORNAME }}
            @endif
        </a>
    @if($entity->sex[0]->DETAIL_INHALT == 'männlich')
        <i class="mdi mdi-gender-male"></i>
    @elseif($entity->sex[0]->DETAIL_INHALT == 'weiblich')
        <i class="mdi mdi-gender-female"></i>
    @endif
</span>