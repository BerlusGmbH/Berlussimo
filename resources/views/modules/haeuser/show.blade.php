@extends('layouts.app')

@section('breadcrumbs')
    <i class="mdi mdi-subdirectory-arrow-right" style="margin-right: -2px"></i>
    <v-breadcrumbs style="display: inline-flex" class="pl-0">
        <v-icon slot="divider">chevron_right</v-icon>
        <v-breadcrumbs-item>
            @if(parse_url(URL::previous())['path'] === parse_url(route('web::haeuser.index'))['path'])
                <a href="{{ URL::previous() }}">Häuser</a>
            @else
                <a href="{{ route('web::haeuser.index') }}">Häuser</a>
            @endif
        </v-breadcrumbs-item>
        <v-breadcrumbs-item>
            @include('shared.entities.haus', ['entity' => $haus, 'icons' => false])
        </v-breadcrumbs-item>
    </v-breadcrumbs>
@endsection

@section('content')
    <b-house-detail-view :house-id="{{$haus->HAUS_ID}}"></b-house-detail-view>
@endsection