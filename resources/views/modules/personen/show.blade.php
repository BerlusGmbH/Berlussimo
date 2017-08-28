@extends('layouts.app')

@section('breadcrumbs')
    <i class="mdi mdi-subdirectory-arrow-right" style="margin-right: -2px"></i>
    <v-breadcrumbs style="display: inline-flex" icons divider="chevron_right" class="pl-0">
        <v-breadcrumbs-item>
            @if(starts_with(URL::previous(), route('web::personen::index')))
                <a href="{{ URL::previous() }}">Personen</a>
            @else
                <a href="{{ route('web::personen::index') }}">Personen</a>
            @endif
        </v-breadcrumbs-item>
        <v-breadcrumbs-item>
            @include('shared.entities.person', ['entity' => $person, 'icons' => false])
        </v-breadcrumbs-item>
    </v-breadcrumbs>
@endsection

@section('content')
    <app-person-show :person-id="{{$person->id}}"></app-person-show>
@endsection