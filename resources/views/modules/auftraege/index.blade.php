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
@endsection