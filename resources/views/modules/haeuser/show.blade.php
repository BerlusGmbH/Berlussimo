@extends('layouts.main-without-menu')

@section('breadcrumbs')
    @if(starts_with(URL::previous(), route('web::haeuser::index')))
        <a href="{{ URL::previous() }}" class="breadcrumb">Häuser</a>
    @else
        <a href="{{ route('web::haeuser::index') }}" class="breadcrumb">Häuser</a>
    @endif
    <span class="breadcrumb">@include('shared.entities.haus', ['entity' => $haus, 'icons' => false])</span>
@endsection

@section('content')
    <div class="row">
        <div class="col-xs-12">
            <div class="card">
                <div class="card-content">
                    <div class="card-title">
                        <div class="row" style="line-height: 24px; margin-bottom: 0px; margin-top: 12px">
                            <div class="col-xs-10">
                                @include('shared.entities.haus', ['entity' => $haus])
                            </div>
                            <div class="col-xs-2 end-xs">
                                <a href="{{ route('web::haeuser::legacy', ['haus_raus' => 'haus_aendern', 'haus_id' => $haus->HAUS_ID]) }}"><i
                                            class="mdi mdi-pencil"></i></a>
                                <a href="{{ route('web::details::legacy', ['option' => 'details_anzeigen', 'detail_tabelle' => 'HAUS', 'detail_id' => $haus->HAUS_ID]) }}"><i
                                            class="mdi mdi-table-edit"></i></a>
                            </div>
                        </div>
                        <div style="font-size: small; line-height: 24px; margin-bottom:12px; margin-left: 6px">
                            @include('shared.entities.objekt', ['entity' => $haus->objekt])
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-6 col-sm-3 detail">
                            <i class="mdi mdi-mail-ru tooltipped" data-position="bottom" data-delay="50" data-tooltip="E-Mail"></i>
                            @php
                                $emails = collect();
                                foreach($haus->mieter()->with('emails')->get() as $mieter) {
                                    if(!$mieter->emails->isEmpty()) {
                                        foreach ($mieter->emails as $email) {
                                            if($email->DETAIL_INHALT != '') {
                                                $emails->push(trim($email->DETAIL_INHALT));
                                            }
                                        }
                                    }
                                }
                                $href = "mailto:?bcc=";
                                foreach ($emails as $email) {
                                    $href .= $email . ', ';
                                }
                            @endphp
                            <a href="{{ $href }}">E-Mail an Mieter ({{ $emails->count() }})</a>
                        </div>
                        <div class="col-xs-6 col-sm-3 detail">
                            <i class="mdi mdi-email tooltipped" data-position="bottom" data-delay="50" data-tooltip="Postleitzahl und Ort"></i>
                            {{$haus->HAUS_PLZ}} {{$haus->HAUS_STADT}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @if(!$haus->commonDetails->isEmpty())
            <div class="col-xs-12 col-sm-6">
                @include('shared.cards.details', ['details' => $haus->commonDetails()->defaultOrder()->get(), 'title' => 'Allgemeine Details'])
            </div>
        @endif
        @if(!$haus->einheiten->isEmpty())
            <div class="col-xs-12 col-sm-3">
                @include('shared.cards.einheiten', [ 'einheiten' => $haus->einheiten()->defaultOrder()->get(), 'title' => 'Einheiten', 'href' => route('web::einheiten::index', ['q' => '!einheit(haus(id=' . $haus->HAUS_ID . '))'])])
            </div>
        @endif
        @if(!$haus->mieter()->get()->isEmpty())
            <div class="col-xs-12 col-sm-3">
                @include('shared.cards.mieter', [ 'mieter' => $haus->mieter()->defaultOrder()->with('sex')->get(), 'title' => 'Mieter', 'href' => route('web::personen::index', ['q' => '!person(mietvertrag(haus(id=' . $haus->HAUS_ID . ') laufzeit=' . \Carbon\Carbon::today()->toDateString() . '))'])])
            </div>
        @endif
        <div class="col-xs-12">
            @include('shared.cards.auftraege', ['auftraege' => $haus->auftraege()->defaultOrder()->get(), 'type' => 'Haus'])
        </div>
    </div>
@endsection