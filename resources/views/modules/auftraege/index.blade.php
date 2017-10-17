@extends('layouts.app')

@section('breadcrumbs')
    <i class="mdi mdi-subdirectory-arrow-right" style="margin-right: -2px"></i>
    <v-breadcrumbs style="display: inline-flex" icons divider="chevron_right" class="pl-0">
        <v-breadcrumbs-item>
            Aufträge
        </v-breadcrumbs-item>
    </v-breadcrumbs>
@endsection

@section('content')
    <app-assignment-list-view></app-assignment-list-view>
    <!--
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
                    @include('shared.listview.dropdown', ['id' => 'view', 'name' => 'v', 'label' => 'Ansicht', 'options' => $options])
                </div>
                <div class="input-field col-md-3">
                    @php($options = $listViews->getViewNames('f'))
                    @include('shared.listview.dropdown', ['name' => 'f', 'id' => 'filter', 'label' => 'Filter', 'multiple' => true, 'options' => $options])
                </div>
                <div class="input-field col-xs-6 col-md-1">
                    @php($options = $listViews->getViewNames('s'))
                    @include('shared.listview.dropdown', ['name' => 's', 'id' => 'size', 'label' => 'Anzahl', 'options' => $options])
                </div>
            </div>
        </form>
        @include('shared.tables.entities-with-paginator', ['parameters' => $listViews->getParameters('q') ,'columns' => $columns, 'entities' => $entities, 'class' => \App\Models\Auftraege::class])
    </div>
    -->
@endsection