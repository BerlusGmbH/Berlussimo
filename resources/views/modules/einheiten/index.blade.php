@extends('layouts.app')

@section('breadcrumbs')
    <i class="mdi mdi-subdirectory-arrow-right" style="margin-right: -2px"></i>
    <v-breadcrumbs style="display: inline-flex" class="pl-0">
        <v-icon slot="divider">chevron_right</v-icon>
        <v-breadcrumbs-item>
            Einheiten
        </v-breadcrumbs-item>
    </v-breadcrumbs>
@endsection

@section('content')
    <app-unit-list-view></app-unit-list-view>
@endsection