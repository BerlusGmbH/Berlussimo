<span style="white-space: nowrap">
    @php
        if(!isset($icons))
        {
            $icons = true;
        };
    @endphp
    @if($icons)
        @if($entity->hasHinweis())
            @php($tooltip = $entity->hinweise->implode('DETAIL_INHALT', '<hr>'))
            <i class="mdi mdi-alert tooltipped red-text" data-position="bottom" data-delay="50"
               data-tooltip="{{$tooltip}}"></i><i
        @else
            <i
        @endif
        class="mdi mdi-account tooltipped" data-position="bottom"
        data-delay="50"
        data-tooltip="Person"></i>
    @endif
        <a href="{{ route('web::personen.show', ['id' => $entity->id]) }}">{{$entity->full_name}}</a>
    @if($icons)
            @if($entity->sex == 'männlich')
            <i class="mdi mdi-gender-male"></i>
            @elseif($entity->sex == 'weiblich')
            <i class="mdi mdi-gender-female"></i>
        @endif
    @endif
</span>