@extends('layouts.main')

@section('breadcrumbs')
    <i class="mdi mdi-subdirectory-arrow-right"></i>
    @if(starts_with(URL::previous(), route('web::personen::index')))
        <a href="{{ URL::previous() }}">Personen</a>
    @else
        <a href="{{ route('web::personen::index') }}">Personen</a>
    @endif
    <span class="breadcrumb">@include('shared.entities.person', ['entity' => $person, 'icons' => false])</span>
@endsection

@section('content')
    <app-person-show :person-id="{{$person->id}}"></app-person-show>
    <person-merge-dialog id="merge" :person="{{$person}}"></person-merge-dialog>
    @if(is_null($person->credential))
        @include('modules.personen.credentials.create', ['person' => $person, 'id' => 'credentials'])
    @else
        @include('modules.personen.credentials.edit', ['person' => $person, 'id' => 'credentials'])
    @endif
    @include('modules.personen.edit', ['person' => $person, 'id' => 'edit'])
    @include('modules.personen.jobs.create', ['person' => $person, 'id' => 'jobs'])
@endsection