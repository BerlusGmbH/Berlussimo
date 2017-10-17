@extends('layouts.app')

@section('breadcrumbs')
    <i class="mdi mdi-subdirectory-arrow-right" style="margin-right: -2px"></i>
    <v-breadcrumbs style="display: inline-flex" icons divider="chevron_right" class="pl-0">
        <v-breadcrumbs-item>
            Personen
        </v-breadcrumbs-item>
    </v-breadcrumbs>
@endsection

@section('content')
    <app-person-list-view></app-person-list-view>
@endsection