@if(isset($entities))
    @inject('relations', '\App\Services\RelationsService')
    @php
        foreach ($entities as $e) {
            if(isset($e)) {
                $entity = $e;
                break;
            }
        };
        $name = $relations->classToColumn(get_class($entity));
        $query = '!' . $name . '((';
        $id = $relations->classFieldToField(get_class($entity), 'id');
        $first = true;
        foreach ($entities as $e) {
            if(isset($e)) {
                if($first) {
                    $first = false;
                    $query .= 'id=' . $e->{$id};
                } else {
                    $query .= ' or id=' . $e->{$id};
                }
            }
        }
        $query .= '))';
    @endphp
    @if($entity instanceof \App\Models\Einheiten)
        @php($href = route('web::einheiten::index', ['q' => $query], false))
    @elseif($entity instanceof \App\Models\Haeuser)
        @php($href = route('web::haeuser::index', ['q' => $query], false))
    @elseif($entity instanceof \App\Models\Objekte)
        @php($href = route('web::objekte::index', ['q' => $query], false))
    @elseif($entity instanceof \App\Models\Kaufvertraege)
        @include('shared.entities.count.kaufvertrag')
    @elseif($entity instanceof \App\Models\Mietvertraege)
        @include('shared.entities.count.mietvertrag')
    @elseif($entity instanceof \App\Models\Person)
        @php($href = route('web::personen::index', ['q' => $query], false))
    @elseif($entity instanceof \App\Models\Details)
        @php($href = route('web::details::legacy', ['q' => $query], false))
    @endif
    <a href="{!! $href !!}">{{ucfirst($aggregate)}} ({{$entities->count()}})</a>
@endif