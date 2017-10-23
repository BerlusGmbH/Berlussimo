@extends('layouts.app')

@section('breadcrumbs')
    <i class="mdi mdi-subdirectory-arrow-right" style="margin-right: -2px"></i>
    <v-breadcrumbs style="display: inline-flex" icons divider="chevron_right" class="pl-0">
        <v-breadcrumbs-item>
            @if(parse_url(URL::previous())['path'] === parse_url(route('web::einheiten.index'))['path'])
                <a href="{{ URL::previous() }}">Einheiten</a>
            @else
                <a href="{{ route('web::einheiten.index') }}">Einheiten</a>
            @endif
        </v-breadcrumbs-item>
        <v-breadcrumbs-item>
            @include('shared.entities.einheit', ['entity' => $einheit, 'icons' => false])
        </v-breadcrumbs-item>
    </v-breadcrumbs>
@endsection

@section('content')
    <app-unit-detail-view :unit-id="{{$einheit->EINHEIT_ID}}"></app-unit-detail-view>
@endsection