<span style="white-space: nowrap">
    @php
        if(!isset($icons))
        {
            $icons = true;
        };
    @endphp
    @if($icons)
        <i class="mdi mdi-worker tooltipped" data-position="bottom" data-delay="50" data-tooltip="Mitarbeiter"></i>
    @endif
    @if(check_user_links(Auth::user()->id, 'benutzer'))
        <a href="{{route('web::benutzer::legacy', ['option' => 'aendern', 'b_id' => $entity->id])}}">{{$entity->name}}</a><a href="{{route('web::benutzer::legacy', ['option' => 'berechtigungen', 'b_id' => $entity->id])}}"> <i class="mdi mdi-flash tooltipped" data-position="bottom" data-delay="50" data-tooltip="Berechtigungen"></i></a>
    @else
        {{$entity->name}}
    @endif
</span>