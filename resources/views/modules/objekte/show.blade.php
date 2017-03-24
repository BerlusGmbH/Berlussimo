@extends('layouts.main-without-menu')

@section('breadcrumbs')
    @if(starts_with(URL::previous(), route('web::objekte::index')))
        <a href="{{ URL::previous() }}" class="breadcrumb">Objekte</a>
    @else
        <a href="{{ route('web::objekte::index') }}" class="breadcrumb">Objekte</a>
    @endif
    <span class="breadcrumb">@include('shared.entities.objekt', ['entity' => $objekt, 'icons' => false])</span>
@endsection

@section('content')
    <div class="row">
        <div class="col-xs-12 col-sm-6">
            <div class="card">
                <div class="card-content">
                    <div class="card-title">
                        <div class="row" style="line-height: 24px; margin-bottom: 12px; margin-top: 12px">
                            <div class="col-xs-8">
                                @include('shared.entities.objekt', ['entity' => $objekt])
                            </div>
                            <div class="col-xs-4 end-xs">
                                <a href="{{ route('web::objekte::legacy', ['objekte_raus' => 'objekt_aendern', 'objekt_id' => $objekt->OBJEKT_ID]) }}"><i
                                            class="mdi mdi-pencil"></i></a>
                                <a href="{{ route('web::details::legacy', ['option' => 'details_hinzu', 'detail_tabelle' => 'OBJEKT', 'detail_id' => $objekt->OBJEKT_ID]) }}"><i
                                            class="mdi mdi-table-edit"></i></a>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-12 col-sm-6 detail">
                            <i class="mdi mdi-mail-ru"></i>
                            @php
                                $emails = collect();
                                foreach($objekt->mieter()->with('emails')->get() as $mieter) {
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
                            <i class="mdi mdi-key tooltipped" data-position="bottom" data-delay="50" data-tooltip="Eigentümer"></i>
                            @include('shared.entities.partner', ['entity' => $objekt->eigentuemer])
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @if(!$objekt->hinweise->isEmpty())
            <div class="col-xs-12 col-sm-6">
                @include('shared.cards.hinweise', ['hinweise' => $objekt->hinweise, 'title' => 'Hinweise'])
            </div>
        @endif
        @if(!$objekt->commonDetails->isEmpty())
            <div class="col-xs-12 col-sm-6">
                @include('shared.cards.details', ['details' => $objekt->commonDetails, 'title' => 'Allgemeine Details'])
            </div>
        @endif
        @if(!$objekt->haeuser->isEmpty())
            <div class="col-xs-12 col-sm-3">
                @include('shared.cards.haeuser', [ 'haeuser' => $objekt->haeuser, 'title' => 'Häuser', 'href' => route('web::haeuser::index', ['q' => '!haus(objekt(id=' . $objekt->OBJEKT_ID . '))'])])
            </div>
        @endif
        @if(!$objekt->einheiten->isEmpty())
            <div class="col-xs-12 col-sm-3">
                @include('shared.cards.einheiten', [ 'einheiten' => $objekt->einheiten, 'title' => 'Einheiten', 'href' => route('web::einheiten::index', ['q' => '!einheit(objekt(id=' . $objekt->OBJEKT_ID . '))'])])
            </div>
        @endif
        @if(!$objekt->mieter()->get()->isEmpty())
            <div class="col-xs-12 col-sm-3">
                @include('shared.cards.mieter', [ 'mieter' => $objekt->mieter()->defaultOrder()->with('sex', 'hinweise')->get(), 'title' => 'Mieter', 'href' => route('web::personen::index', ['q' => '!person(mietvertrag(objekt(id=' . $objekt->OBJEKT_ID . ') aktiv))'])])
            </div>
        @endif
        <div class="col-xs-12 col-sm-6">
            <div class="card card-expandable">
                <div class="card-content">
                    <span class="card-title">Berichte</span>
                    <table class="striped">
                        <thead>
                        <th>Bericht</th>
                        <th>Beschreibung</th>
                        </thead>
                        <tbody>
                        <tr>
                            <td>
                                <a target="_blank"
                                   href="{{ route('web::objekte::legacy', ['objekte_raus' => 'checkliste', 'objekt_id' => $objekt->OBJEKT_ID]) }}">Hauswart
                                    Checkliste <i class="mdi mdi-file-pdf"></i></a>
                            </td>
                            <td>
                                Checkliste für Rundgang
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <a target="_blank"
                                   href="{{ route('web::objekte::legacy', ['objekte_raus' => 'mietaufstellung', 'objekt_id' => $objekt->OBJEKT_ID]) }}">Mietaufstellung
                                    <i class="mdi mdi-file-pdf"></i></a>
                            </td>
                            <td>
                                Mietaufstellung des aktuellen Monats
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <a target="_blank"
                                   href="{{ route('web::objekte::legacy', ['objekte_raus' => 'mietaufstellung_m_j', 'objekt_id' => $objekt->OBJEKT_ID, 'monat' => \Carbon\Carbon::now()->month, 'jahr' => \Carbon\Carbon::now()->year]) }}">Mietaufstellung
                                    Monatsjournal<i class="mdi mdi-file-pdf"></i></a>
                                <a target="_blank"
                                   href="{{ route('web::objekte::legacy', ['objekte_raus' => 'mietaufstellung_m_j', 'objekt_id' => $objekt->OBJEKT_ID, 'monat' => \Carbon\Carbon::now()->month, 'jahr' => \Carbon\Carbon::now()->year, 'XLS']) }}"><i
                                            class="mdi mdi-file-excel"></i></a>
                            </td>
                            <td>
                                Mietaufstellung des aktuellen Monats in Journalansicht
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <a target="_blank"
                                   href="{{ route('web::mietkontenblatt::legacy', ['anzeigen' => 'alle_mkb', 'objekt_id' => $objekt->OBJEKT_ID]) }}">Alle
                                    Mietkontenblätter <i class="mdi mdi-file-pdf"></i></a>
                            </td>
                            <td>
                                Mietkontenblätter aller Mieter
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <a target="_blank"
                                   href="{{ route('web::einheiten::legacy', ['einheit_raus' => 'mieterliste_aktuell', 'objekt_id' => $objekt->OBJEKT_ID]) }}">Mieterkontakte
                                    <i class="mdi mdi-file-pdf"></i></a>
                            </td>
                            <td>
                                Kontaktliste aller Mieter
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <a target="_blank"
                                   href="{{ route('web::objekte::legacy', ['objekte_raus' => 'mietaufstellung_j', 'objekt_id' => $objekt->OBJEKT_ID, 'jahr' => \Carbon\Carbon::parse('last year')->year]) }}">SOLL/IST
                                    <i class="mdi mdi-file-pdf"></i></a>
                            </td>
                            <td>
                                Mieten SOLL/IST kumuliert über das vorherige Jahr
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <a target="_blank"
                                   href="{{ route('web::objekte::legacy', ['objekte_raus' => 'stammdaten_pdf', 'objekt_id' => $objekt->OBJEKT_ID]) }}">Stammdaten
                                    <i class="mdi mdi-file-pdf"></i></a>
                            </td>
                            <td>
                                Stammdaten des Objektes
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-xs-12">
            @include('shared.cards.auftraege', [
                'auftraege' => $objekt->auftraege()->orderBy('ERSTELLT', 'desc')->get(),
                'title' => 'Aufträge',
                'type' => 'Objekt',
                'id' => $objekt->OBJEKT_ID,
                'href' => route('web::todo::index', ['q' => '!auftrag(kostenträger(objekt(id=' . $objekt->OBJEKT_ID . ')))']),
                'hasHinweis' => $objekt->hasHinweis()]
            )
        </div>
    </div>
@endsection