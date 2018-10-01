@extends('layouts.app')

@section('breadcrumbs')
    <i class="mdi mdi-subdirectory-arrow-right" style="margin-right: -2px"></i>
    <v-breadcrumbs style="display: inline-flex" class="pl-0">
        <v-icon slot="divider">chevron_right</v-icon>
        <v-breadcrumbs-item>
            @if(parse_url(URL::previous())['path'] === parse_url(route('web::objekte.index'))['path'])
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
    <b-object-detail-view :object-id="{{$objekt->OBJEKT_ID}}"></b-object-detail-view>
@endsection