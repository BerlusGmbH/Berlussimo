@extends('layouts.app')

@section('breadcrumbs')
    <i class="mdi mdi-subdirectory-arrow-right" style="margin-right: -2px"></i>
    <v-breadcrumbs style="display: inline-flex" icons divider="chevron_right" class="pl-0">
        <v-breadcrumbs-item>
            @if(starts_with(URL::previous(), route('web::objekte.index')))
                <a href="{{ URL::previous() }}">Objekte</a>
            @else
                <a href="{{ route('web::objekte.index') }}">Objekte</a>
            @endif
        </v-breadcrumbs-item>
        <v-breadcrumbs-item>
            @include('shared.entities.objekt', ['entity' => $objekt, 'icons' => false])
        </v-breadcrumbs-item>
    </v-breadcrumbs>
@endsection

@section('content')
    <app-object-detail-view :object-id="{{$objekt->OBJEKT_ID}}"></app-object-detail-view>
    <!--
                        <div class="col-xs-12 col-sm-6 detail">
                            <i class="mdi mdi-home tooltipped" data-tooltip="Wohnfläche"></i>
                            <a href="{{ route('web::einheiten.index', ['q' => '!einheit(objekt(id=' . $objekt->OBJEKT_ID . ') (typ=Wohnraum or typ=Wohneigentum))']) }}">{{$objekt->wohnflaeche}}
                                m²</a>
                        </div>
                        <div class="col-xs-12 col-sm-6 detail">
                            <i class="mdi mdi-store tooltipped" data-tooltip="Gewerbefläche"></i>
                            <a href="{{ route('web::einheiten.index', ['q' => '!einheit(objekt(id=' . $objekt->OBJEKT_ID . ') typ=Gewerbe)']) }}">{{$objekt->gewerbeflaeche}}
                                m²</a>
                        </div>
    -->
@endsection