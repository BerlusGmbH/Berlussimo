@extends('layouts.app')

@section('breadcrumbs')
    <i class="mdi mdi-subdirectory-arrow-right" style="margin-right: -2px"></i>
    <v-breadcrumbs style="display: inline-flex" icons divider="chevron_right" class="pl-0">
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
    <app-house-detail-view :house-id="{{$haus->HAUS_ID}}"></app-house-detail-view>
    <!--
                        <div class="col-xs-12 col-sm-6 detail">
                            <i class="mdi mdi-home tooltipped" data-tooltip="Wohnfläche"></i>
                            <a href="{{ route('web::einheiten.index', ['q' => '!einheit(haus(id=' . $haus->HAUS_ID . ') (typ=Wohnraum or typ=Wohneigentum))']) }}">{{$haus->wohnflaeche}}
                                m²</a>
                        </div>
                        <div class="col-xs-12 col-sm-6 detail">
                            <i class="mdi mdi-store tooltipped" data-tooltip="Gewerbefläche"></i>
                            <a href="{{ route('web::einheiten.index', ['q' => '!einheit(haus(id=' . $haus->HAUS_ID . ') typ=Gewerbe)']) }}">{{$haus->gewerbeflaeche}}
                                m²</a>
                        </div>
    -->
@endsection