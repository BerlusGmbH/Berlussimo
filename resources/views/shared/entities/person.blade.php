<span style="white-space: nowrap">
    @php
        if(!isset($icons))
        {
            $icons = true;
        };
    @endphp
    @if($icons)
        <i class="mdi mdi-account tooltipped" data-position="bottom" data-delay="50" data-tooltip="Person"></i>
    @endif
    <a href="{{ route('web::personen::show', ['id' => $entity->PERSON_ID]) }}">{{ $entity->PERSON_NACHNAME }}
        @if(trim($entity->PERSON_VORNAME) != '')
            , {{ $entity->PERSON_VORNAME }}
        @endif
        </a>
    @if($icons)
        @if($entity->sex[0]->DETAIL_INHALT == 'männlich')
            <i class="mdi mdi-gender-male"></i>
        @elseif($entity->sex[0]->DETAIL_INHALT == 'weiblich')
            <i class="mdi mdi-gender-female"></i>
        @endif
    @endif
</span>