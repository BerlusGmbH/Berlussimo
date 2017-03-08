@extends('layouts.main-without-menu')

@section('breadcrumbs')
    <a href="{{ route('web::haeuser::index') }}" class="breadcrumb">Häuser</a>
@endsection

@section('content')
    <div class="card-panel white">
        <form id="filter-form" method="get">
            <div class="row">
                <div class="input-field col-xs-6 col-md-2">
                    <a class="btn waves-effect waves-light tooltipped" data-position="bottom" data-delay="50" data-tooltip="Neues Haus"
                       href="{{ route('web::haeuserform::legacy', ['daten_rein' => 'haus_neu']) }}"><i class="mdi mdi-plus"></i><i class="mdi mdi-domain"></i></a>
                </div>
                <div class="input-field col-xs-12 col-md-6">
                    <i class="mdi mdi-filter-variant prefix"></i>
                    <input id="filter" name="q" value="{{ request()->input('q') }}" type="text"
                           autocomplete="off">
                    <label for="filter">Filter</label>
                </div>
                <div class="input-field col-xs-12 col-md-3">
                    @php($options = ['(ohne)' => '', 'Listenansicht' => 'haus !haus[str:asc nr:asc] haus[plz] haus[ort] detail[count] einheit[count] objekt'])
                    @include('shared.listview.views', ['id' => 'view', 'name' => 'v', 'label' => 'Ansicht', 'options' => $options])
                </div>
                <div class="input-field col-xs-6 col-md-1">
                    @include('shared.listview.resultsize', ['name' => 's', 'id' => 'size', 'label' => 'Anzahl'])
                </div>
            </div>
        </form>
        @include('shared.tables.entities-with-paginator', ['parameters' => ['q', 's', 'v', 'f'] ,'columns' => $columns, 'entities' => $entities, 'class' => \App\Models\Haeuser::class])
    </div>
@endsection

@push('scripts')
<script type="text/javascript">
    $(document).ready(function () {

        var submit = function(target) {
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