@extends('layouts.app')

@section('breadcrumbs')
    <i class="mdi mdi-subdirectory-arrow-right" style="margin-right: -2px"></i>
    <v-breadcrumbs style="display: inline-flex" icons divider="chevron_right" class="pl-0">
        <v-breadcrumbs-item>
            Objekte
        </v-breadcrumbs-item>
    </v-breadcrumbs>
@endsection

@section('content')
    <app-object-list-view></app-object-list-view>
    <!--
    <div class="card-panel white">
        <form id="filter-form" method="get">
            <div class="row">
                <div class="input-field col-xs-12 col-md-3">
                    <a class="btn waves-effect waves-light tooltipped" data-position="bottom" data-delay="50" data-tooltip="Neues Objekt"
                       href="{{ route('web::objekte::legacy', ['objekte_raus' => 'objekt_anlegen']) }}"><i
                                class="mdi mdi-plus"></i><i class="mdi mdi-city"></i></a>
                    <a class="btn waves-effect waves-light tooltipped" data-position="bottom" data-delay="50" data-tooltip="Objekt kopieren"
                       href="{{ route('web::objekte::legacy', ['objekte_raus' => 'objekt_kopieren']) }}"><i
                                class="mdi mdi-content-copy"></i><i class="mdi mdi-city"></i></a>
                </div>
                <div class="input-field col-xs-12 col-md-6">
                    <i class="mdi mdi-filter-variant prefix"></i>
                    <input id="filter" name="q" value="{{ request()->input('q') }}" type="text"
                           autocomplete="off">
                    <label for="filter">Filter</label>
                </div>
                <div class="input-field col-xs-12 col-md-2">
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
        @include('shared.tables.entities-with-paginator', ['parameters' => $listViews->getParameters('q') ,'columns' => $columns, 'entities' => $entities, 'class' => \App\Models\Objekte::class])
    </div>
    -->
@endsection