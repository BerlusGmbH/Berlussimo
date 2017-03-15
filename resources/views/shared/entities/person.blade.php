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
    @php
        $name = trim($entity->PERSON_NACHNAME);
        if(trim($entity->PERSON_VORNAME) != '') {
            $name .= ', ' . trim($entity->PERSON_VORNAME);
        }
    @endphp
    <a href="{{ route('web::personen::show', ['id' => $entity->PERSON_ID]) }}">{{$name}}</a>
    @if($icons)
        @if($entity->sex[0]->DETAIL_INHALT == 'männlich')
            <i class="mdi mdi-gender-male"></i>
        @elseif($entity->sex[0]->DETAIL_INHALT == 'weiblich')
            <i class="mdi mdi-gender-female"></i>
        @endif
    @endif
</span>