@extends('layouts.main')

@section('breadcrumbs')
    <i class="mdi mdi-subdirectory-arrow-right"></i>Personen
@endsection

@section('content')
    <div class="card-panel white">
        <form id="filter-form" method="get">
            <div class="row">
                <div class="input-field col-xs-6 col-md-2">
                    <a class="btn waves-effect waves-light tooltipped" data-tooltip="Neue Person"
                       href="{{ route('web::personen::create') }}">
                        <i class="mdi mdi-plus"></i><i class="mdi mdi-account"></i></a>
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
                    @include('shared.listview.dropdown', ['id' => 'view', 'name' => 'v', 'label' => 'Ansicht', 'multiple' => false, 'options' => $options])
                </div>
                <div class="input-field col-xs-6 col-md-1">
                    @php($options = $listViews->getViewNames('s'))
                    @include('shared.listview.dropdown', ['name' => 's', 'id' => 'size', 'label' => 'Anzahl', 'multiple' => false, 'options' => $options])
                </div>
            </div>
            <div class="row end-xs">
                <div class="input-field col-md-2">
                    @php($options = $listViews->getViewNames('c'))
                    @include('shared.listview.dropdown', ['name' => 'c', 'id' => 'class', 'label' => 'Klasse', 'multiple' => true, 'options' => $options])
                </div>
                @if(request()->has('c'))
                    @if(in_array('Arbeitnehmer', request()->input('c')))
                        <div class="input-field col-md-2">
                            @php($options = $listViews->getViewNames('f1'))
                            @include('shared.listview.dropdown', ['name' => 'f1', 'id' => 'filter1', 'label' => 'Arbeitsverhältnis-Aktiv', 'multiple' => false, 'options' => $options])
                        </div>
                        <div class="input-field col-md-2">
                            @php($options = $listViews->getViewNames('f2'))
                            @include('shared.listview.dropdown', ['name' => 'f2', 'id' => 'filter2', 'label' => 'Arbeitgeber', 'multiple' => false, 'options' => $options])
                        </div>
                    @endif
                    @if(in_array('Mieter', request()->input('c')))
                        <div class="input-field col-md-2">
                            @php($options = $listViews->getViewNames('f3'))
                            @include('shared.listview.dropdown', ['name' => 'f3', 'id' => 'filter3', 'label' => 'Mietvertrag-Aktiv', 'multiple' => false, 'options' => $options])
                        </div>
                    @endif
                @endif
            </div>
        </form>
        @include('shared.tables.entities-with-paginator', ['parameters' => $listViews->getParameters('q') ,'columns' => $columns, 'entities' => $entities, 'class' => \App\Models\Person::class])
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
        var $filter = $('select.listview-filter');
        $filter.on('change', function () {
            submit(this);
        });
        $filter.siblings('input.select-dropdown').first().on('close', function () {
            if (hasChanged)
                submit(this);
        });
        $filter = $('select.listview-filter-multiple');
        var hasChanged = false;
        $filter.on('change', function () {
            hasChanged = true;
        });
        $filter.siblings('input.select-dropdown').first().on('close', function () {
            if (hasChanged)
                submit(this);
        });
    });
</script>
@endpush