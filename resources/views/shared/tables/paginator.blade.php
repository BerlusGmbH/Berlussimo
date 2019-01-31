@if($entities instanceof \Illuminate\Pagination\AbstractPaginator)
    @foreach($parameters as $parameter)
        @if(request()->filled($parameter))
            @php($paginator->appends([$parameter => request()->input($parameter)]))
        @endif
    @endforeach
    {!! $paginator->render() !!}
@endif