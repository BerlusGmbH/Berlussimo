@extends('layouts.main-without-menu')

@section('breadcrumbs')
    @if(starts_with(URL::previous(), route('web::einheiten::index')))
        <a href="{{ URL::previous() }}" class="breadcrumb">Einheiten</a>
    @else
        <a href="{{ route('web::einheiten::index') }}" class="breadcrumb">Einheiten</a>
    @endif
    <span class="breadcrumb">@include('shared.entities.einheit', ['entity' => $einheit, 'icons' => false])</span>
@endsection

@section('content')
    <div class="row">
        <div class="col-xs-12">
            <div class="card">
                <div class="card-content">
                    <div class="card-title">
                        <div class="row" style="line-height: 24px; margin-bottom: 0; margin-top: 12px">
                            <div class="col-xs-10">
                                @include('shared.entities.einheit', ['entity' => $einheit])
                            </div>
                            <div class="col-xs-2 end-xs">
                                <a href="{{ route('web::haeuser::legacy', ['haus_raus' => 'haus_aendern', 'haus_id' => $einheit->HAUS_ID]) }}"><i
                                            class="mdi mdi-pencil"></i></a>
                                <a href="{{ route('web::details::legacy', ['option' => 'details_anzeigen', 'detail_tabelle' => 'HAUS', 'detail_id' => $einheit->HAUS_ID]) }}"><i
                                            class="mdi mdi-table-edit"></i></a>
                            </div>
                        </div>
                        <div style="font-size: small; line-height: 24px; margin-bottom:12px; margin-left: 6px">
                            <span style="margin-right: 8px">@include('shared.entities.haus', ['entity' => $einheit->haus])</span> @include('shared.entities.objekt', ['entity' => $einheit->haus->objekt])
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-6 detail">
                            <i class="mdi mdi-mail-ru"></i>
                            @php
                                $emails = collect();
                                foreach($einheit->mieter()->with('emails')->get() as $mieter) {
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
                    </div>
                </div>
            </div>
        </div>
        @if(!$einheit->commonDetails->isEmpty())
            <div class="col-xs-12 col-sm-6">
                <div class="card card-expandable">
                    <div class="card-content">
                        <div class="card-title">Allgemeine Details ({{ $einheit->commonDetails->count() }})</div>
                        <table class="striped">
                            <thead>
                            <th>Typ</th>
                            <th>Wert</th>
                            <th>Bemerkung</th>
                            </thead>
                            <tbody>
                            @foreach( $einheit->commonDetails as $detail )
                                <tr>
                                    <td>
                                        {{ $detail->DETAIL_NAME }}
                                    </td>
                                    <td>
                                        {{ $detail->DETAIL_INHALT }}
                                    </td>
                                    <td>
                                        {{ $detail->DETAIL_BEMERKUNG }}
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
        @if(!$einheit->mieter()->get()->isEmpty())
            <div class="col-xs-12 col-sm-3">
                <div class="card card-expandable">
                    <div class="card-content">
                        <span class="card-title"><a href="{{ route('web::personen::index', ['q' => '!person(mietvertrag(einheit(id=' . $einheit->EINHEIT_ID . ') laufzeit=' . \Carbon\Carbon::today()->toDateString() . '))']) }}">Mieter ({{ $einheit->mieter()->get()->count() }})</a></span>
                        <table class="striped">
                            <thead>
                            <th>Mieter</th>
                            </thead>
                            <tbody>
                            @foreach( $einheit->mieter()->defaultOrder()->with('sex')->get() as $mieter )
                                <tr>
                                    <td>
                                        @include('shared.entities.person', [ 'entity' => $mieter ])
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
        @if(!$einheit->auftraege->isEmpty())
            <div class="col-xs-12">
                <div class="card card-expandable">
                    <div class="card-content">
                        <span class="card-title">Aufträge ({{ $einheit->auftraege->count() }})</span>
                        <table class="striped">
                            <thead>
                            <th>Auftrag</th>
                            <th>Erstellt</th>
                            <th>Von</th>
                            <th>An</th>
                            <th>Text</th>
                            </thead>
                            <tbody>
                            @foreach( $einheit->auftraege()->defaultOrder()->get() as $auftrag )
                                <tr>
                                    <td>
                                        @include('shared.entities.auftrag', [ 'entity' => $auftrag ])
                                    </td>
                                    <td>
                                        {{  $auftrag->ERSTELLT }}
                                    </td>
                                    <td>
                                        @include('shared.entity', [ 'entity' => $auftrag->von ])
                                    </td>
                                    <td>
                                        @include('shared.entity', [ 'entity' => $auftrag->an ])
                                    </td>
                                    <td>
                                        {{  $auftrag->TEXT }}
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection