@extends('layouts.main')

@section('breadcrumbs')
    <i class="mdi mdi-subdirectory-arrow-right"></i>Aufträge
@endsection

@section('content')
    <div class="card-panel white">
        <form id="filter-form" method="get">
            <div class="row">
                <div class="input-field col-xs-6 col-md-3">
                    <a class='waves-effect waves-light btn tooltipped' data-position="bottom" data-delay="50" data-tooltip="Auftrag an Mitarbeiter"
                           href='{{ route('web::todo::legacy', ['option' => 'neues_projekt', 'typ' => 'Benutzer']) }}'>
                        <i class="mdi mdi-plus"></i><i class="mdi mdi-clipboard"></i><i class="mdi mdi-worker"></i></a>
                    <a class='waves-effect waves-light btn tooltipped' data-position="bottom" data-delay="50" data-tooltip="Auftrag an Partner"
                           href='{{ route('web::todo::legacy', ['option' => 'neues_projekt', 'typ' => 'Partner']) }}'>
                        <i class="mdi mdi-plus"></i><i class="mdi mdi-clipboard"></i><i class="mdi mdi-account-multiple"></i></a>
                </div>
                <div class="input-field col-xs-12 col-md-9">
                    <i class="mdi mdi-filter-variant prefix"></i>
                    <input id="query" name="q" value="{{ request()->input('q') }}" type="text"
                           autocomplete="off">
                    <label for="query">Abfrage</label>
                </div>
                <div class="input-field col-xs-12 col-md-offset-5 col-md-3">
                    @inject('listViews', "App\Services\ListViewsService")
                    @php($options = $listViews->getViewNames('v'))
                    @include('shared.listview.views', ['id' => 'view', 'name' => 'v', 'label' => 'Ansicht', 'options' => $options])
                </div>
                <div class="input-field col-md-3">
                    @php($options = $listViews->getViewNames('f'))
                    @include('shared.listview.filters', ['name' => 'f', 'id' => 'filter', 'label' => 'Filter', 'multiple' => true, 'options' => $options])
                </div>
                <div class="input-field col-xs-6 col-md-1">
                    @include('shared.listview.resultsize', ['name' => 's', 'id' => 'size', 'label' => 'Anzahl'])
                </div>
            </div>
        </form>
        @include('shared.tables.entities-with-paginator', ['parameters' => ['q', 's', 'v', 'f'] ,'columns' => $columns, 'entities' => $entities, 'class' => \App\Models\Auftraege::class])
    </div>
@endsection

@push('scripts')
<script type="text/javascript">
    $(document).ready(function () {

        var submit = function (target) {
            target.form.submit();
        };

        var hasChanged = false;

        $('#query').keypress(function (e) {
            if (e.which == KeyCode.KEY_ENTER || e.which == KeyCode.KEY_RETURN) {
                submit(this);
            }
        });
        $('#size').on('change', function () {
            submit(this);
        });
        $('#view').on('change', function () {
            submit(this);
        });
        var $filter = $('#filter');
        $filter.on('change', function () {
           hasChanged = true;
        });
        $filter.siblings('input.select-dropdown').first().on('close', function () {
            if(hasChanged)
                submit(this);
        });
    });
</script>
@endpush