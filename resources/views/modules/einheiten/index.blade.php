@extends('layouts.main-without-menu')

@section('breadcrumbs')
    <a href="{{ route('web::einheiten::index') }}" class="breadcrumb">Einheiten</a>
@endsection

@section('content')
    <div class="card-panel white">
        <form id="filter-form" method="get">
            <div class="row">
                <div class="input-field col-xs-6 col-md-2">
                    <a class="btn waves-effect waves-light"
                       href="{{ route('web::einheiten::legacy', ['einheit_raus' => 'einheit_neu']) }}"><i
                                class="mdi mdi-plus left"></i>Neu</a>
                </div>
                <div class="input-field col-xs-12 col-md-6">
                    <i class="mdi mdi-filter-variant prefix"></i>
                    <input id="filter" name="q" value="{{ request()->input('q') }}" type="text"
                           autocomplete="off">
                    <label for="filter">Filter</label>
                </div>
                <div class="input-field col-xs-12 col-md-3">
                    <select id="view" name="v">
                        <option value="" {{ !request()->has('v') ? 'selected' : '' }}>(ohne)
                        </option>
                        <option value="einheit !einheit[name] mietvertrag person[mietvertrag] einheit[typ] einheit[qm] einheit[lage] haus detail[count]" {{ request()->input('v') == 'einheit !einheit[name] mietvertrag person[mietvertrag] einheit[typ] einheit[qm] einheit[lage] haus detail[count]' ? 'selected' : '' }}>Listenansicht
                        </option>
                    </select>
                    <label>Ansicht</label>
                </div>
                <div class="input-field col-xs-6 col-md-1">
                    <select id="size" name="s">
                        <option value="5" {{ request()->input('s') == 5 ? 'selected' : '' }}>5
                        </option>
                        <option value="10" {{ request()->input('s') == 10 ? 'selected' : '' }}>10</option>
                        <option value="20" {{ (request()->input('s') == 20 | !request()->has('s')) ? 'selected' : '' }}>20
                        </option>
                        <option value="50" {{ request()->input('s') == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request()->input('s') == 100 ? 'selected' : '' }}>100</option>
                        <option value="all" {{ request()->input('s') == 'all' ? 'selected' : '' }}>Alle</option>
                    </select>
                    <label>Anzahl</label>
                </div>
            </div>
        </form>
        <div class="row center-xs">
            @if(!request()->has('s') || (request()->has('s') && request()->input('s') != 'all'))
                @php
                    if(request()->has('q'))
                        $entities->appends(['q' => request()->input('q')]);
                    if(request()->has('s'))
                        $entities->appends(['s' => request()->input('s')]);
                    if(request()->has('v'))
                        $entities->appends(['v' => request()->input('v')]);
                @endphp
                {!! $entities->render() !!}
            @endif
        </div>
        <div class="row">
            <div class="col col-xs-12">
                @include('shared.entitytable', ['columns' => $columns, 'entities' => $entities, 'class' => \App\Models\Einheiten::class])
            </div>
        </div>
        <div class="row center-xs">
            @if(!request()->has('s') || (request()->has('s') && request()->input('s') != 'all'))
                @php
                    if(request()->has('q'))
                        $entities->appends(['q' => request()->input('q')]);
                    if(request()->has('s'))
                        $entities->appends(['s' => request()->input('s')]);
                    if(request()->has('v'))
                        $entities->appends(['v' => request()->input('v')]);
                @endphp
                {!! $entities->render() !!}
            @endif
        </div>
    </div>
@endsection

@push('scripts')
<script type="text/javascript">
    $(document).ready(function () {

        var submit = function (target) {
            target.form.submit();
        };

        $('#filter').keypress(function (e) {
            if (e.which == KeyCode.KEY_ENTER || e.which == KeyCode.KEY_RETURN) {
                submit(this);
            }
        });
        $('#size').on('change', function (e) {
            submit(this);
        });
        $('#view').on('change', function (e) {
            submit(this);
        });
    });
</script>
@endpush