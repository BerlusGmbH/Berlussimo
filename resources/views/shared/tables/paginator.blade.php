@if($entities instanceof \Illuminate\Pagination\AbstractPaginator)
    @foreach($parameters as $parameter)
        @if(request()->has($parameter))
            @php($paginator->appends([$parameter => request()->input($parameter)]))
        @endif
    @endforeach
    {!! $paginator->render() !!}
@endif