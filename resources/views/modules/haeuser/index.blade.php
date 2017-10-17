@extends('layouts.app')

@section('breadcrumbs')
    <i class="mdi mdi-subdirectory-arrow-right" style="margin-right: -2px"></i>
    <v-breadcrumbs style="display: inline-flex" icons divider="chevron_right" class="pl-0">
        <v-breadcrumbs-item>
            Häuser
        </v-breadcrumbs-item>
    </v-breadcrumbs>
@endsection

@section('content')
    <app-house-list-view></app-house-list-view>
    <!--
                    <a class="btn waves-effect waves-light tooltipped" data-position="bottom" data-delay="50" data-tooltip="Neues Haus"
                       href="{{ route('web::haeuserform::legacy', ['daten_rein' => 'haus_neu']) }}"><i class="mdi mdi-plus"></i><i class="mdi mdi-domain"></i></a>
    -->
@endsection