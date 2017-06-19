@extends('layouts.main')

@section('breadcrumbs')
    <i class="mdi mdi-subdirectory-arrow-right"></i>Häuser
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
                    @inject('listViews', "App\Services\ListViewsService")
                    @php($options = $listViews->getViewNames('v'))
                    @include('shared.listview.dropdown', ['id' => 'view', 'name' => 'v', 'label' => 'Ansicht', 'options' => $options])
                </div>
                <div class="input-field col-xs-6 col-md-1">
                    @php($options = $listViews->getViewNames('s'))
                    @include('shared.listview.dropdown', ['name' => 's', 'id' => 'size', 'label' => 'Anzahl', 'options' => $options])
                </div>
            </div>
        </form>
        @include('shared.tables.entities-with-paginator', ['parameters' => $listViews->getParameters('q') ,'columns' => $columns, 'entities' => $entities, 'class' => \App\Models\Haeuser::class])
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