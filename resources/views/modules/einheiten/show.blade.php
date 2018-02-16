@extends('layouts.main')

@section('breadcrumbs')
    <i class="mdi mdi-subdirectory-arrow-right"></i>
    @if(starts_with(URL::previous(), route('web::einheiten::index')))
        <a href="{{ URL::previous() }}">Einheiten</a>
    @else
        <a href="{{ route('web::einheiten::index') }}">Einheiten</a>
    @endif
    <span class="breadcrumb">@include('shared.entities.einheit', ['entity' => $einheit, 'icons' => false])</span>
@endsection

@section('content')
    <div class="row">
        <div class="col-xs-12 col-sm-6">
            <div class="card">
                <div class="card-content">
                    <div class="card-title">
                        <div class="row" style="line-height: 24px; margin-bottom: 0; margin-top: 12px">
                            <div class="col-xs-12 col-sm-8">
                                @include('shared.entities.einheit', ['entity' => $einheit])
                                <div style="font-size: small; line-height: 24px; margin-left: 6px">
                                    <span style="margin-right: 8px">@include('shared.entities.haus', ['entity' => $einheit->haus])</span> @include('shared.entities.objekt', ['entity' => $einheit->haus->objekt])
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-4 end-xs">
                                <a href="{{ route('web::einheiten::legacy', ['einheit_raus' => 'einheit_aendern', 'einheit_id' => $einheit->EINHEIT_ID]) }}"><i
                                            class="mdi mdi-pencil"></i></a>
                                <a href="{{ route('web::details::legacy', ['option' => 'details_anzeigen', 'detail_tabelle' => 'EINHEIT', 'detail_id' => $einheit->EINHEIT_ID]) }}"><i
                                            class="mdi mdi-table-edit"></i></a>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-12 col-sm-6 detail">
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
                        <div class="col-xs-12 col-sm-6 detail">
                            <i class="mdi mdi-compass tooltipped" data-position="bottom" data-delay="50" data-tooltip="Lage"></i> {{ $einheit->EINHEIT_LAGE }}
                        </div>
                        <div class="col-xs-12 col-sm-6 detail">
                            <i class="mdi mdi-arrow-expand-all tooltipped" data-position="bottom" data-delay="50" data-tooltip="Fläche"></i> {{ $einheit->EINHEIT_QM }} m²
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @if(!$einheit->hinweise->isEmpty())
            <div class="col-xs-12 col-sm-6">
                @include('shared.cards.hinweise', ['hinweise' => $einheit->hinweise()->defaultOrder()->get(), 'title' => 'Hinweise'])
            </div>
        @endif
        @if(!$einheit->commonDetails->isEmpty())
            <div class="col-xs-12 col-sm-6">
                @include('shared.cards.details', ['details' => $einheit->commonDetails()->defaultOrder()->get(), 'title' => 'Allgemeine Details'])
            </div>
        @endif
        @if(!$einheit->mieter()->get()->isEmpty())
            <div class="col-xs-12 col-sm-3">
                @include('shared.cards.mieter', ['mieter' => $einheit->mieter()->defaultOrder()->with(['sex', 'details'])->get(), 'title' => 'Mieter', 'href' => route('web::personen::index', ['q' => '!person(mietvertrag(einheit(id=' . $einheit->EINHEIT_ID . ') aktiv))'])])
            </div>
        @endif
        @if(!$einheit->WEGEigentuemer()->get()->isEmpty())
            <div class="col-xs-12 col-sm-3">
                @include('shared.cards.eigentuemer', ['eigentuemer' => $einheit->WEGEigentuemer()->defaultOrder()->with(['sex', 'details'])->get(), 'title' => 'WEG-Eigentümer', 'href' => route('web::personen::index', ['q' => '!person(kaufvertrag(einheit(id=' . $einheit->EINHEIT_ID . ') aktiv))'])])
            </div>
        @endif
        @if(!$einheit->mietvertraege()->get()->isEmpty())
            <div class="col-xs-12 col-sm-3">
                @include('shared.cards.mietvertraege', ['mietvertraege' => $einheit->mietvertraege()->defaultOrder()->get()])
            </div>
        @endif
        @if(!$einheit->kaufvertraege()->get()->isEmpty())
            <div class="col-xs-12 col-sm-3">
                @include('shared.cards.kaufvertraege', ['kaufvertraege' => $einheit->kaufvertraege()->defaultOrder()->get()])
            </div>
        @endif
        <div class="col-xs-12">
            @include('shared.cards.auftraege', [
                'auftraege' => $einheit->auftraege()->orderBy('ERSTELLT', 'desc')->get(),
                'title' => 'Aufträge',
                'type' => 'Einheit',
                'id' => $einheit->EINHEIT_ID,
                'href' => route('web::todo::index', ['q' => '!auftrag(kostenträger(einheit(id=' . $einheit->EINHEIT_ID . ')))']),
                'hasHinweis' => $einheit->hasHinweis()
            ])
        </div>
    </div>
@endsection