@extends('layouts.main-without-menu')

@section('breadcrumbs')
    @if(starts_with(URL::previous(), route('web::personen::index')))
        <a href="{{ URL::previous() }}" class="breadcrumb">Personen</a>
    @else
        <a href="{{ route('web::personen::index') }}" class="breadcrumb">Personen</a>
    @endif
    <span class="breadcrumb">@include('shared.entities.person', ['entity' => $person, 'icons' => false])</span>
@endsection

@section('content')
    <div class="row">
        <div class="col-xs-12 col-sm-6">
            @include('shared.cards.person', ['person' => $person])
        </div>
        @if(!$person->hinweise->isEmpty())
            <div class="col-xs-12 col-sm-6">
                @include('shared.cards.hinweise', ['hinweise' => $person->hinweise()->defaultOrder()->get(), 'title' => 'Hinweise'])
            </div>
        @endif
        @if(!$person->commonDetails->isEmpty())
            <div class="col-xs-12 col-sm-6">
                @include('shared.cards.details', ['details' => $person->commonDetails()->defaultOrder()->get(), 'title' => 'Allgemeine Details'])
            </div>
        @endif
        @if(!$person->mietvertraege->isEmpty())
            <div class="col-xs-12 col-sm-6">
                <div class="card card-expandable">
                    <div class="card-content">
                        <span class="card-title">Mietverträge ({{$person->mietvertraege->count()}})</span>
                        <table class="striped responsive-table">
                            <thead>
                            <th>Mietvertrag</th>
                            <th>Einheit</th>
                            <th>Haus</th>
                            <th>Objekt</th>
                            </thead>
                            <tbody>
                            @foreach( $person->mietvertraege()->defaultOrder()->with('einheit.haus.objekt')->get() as $mietvertrag )
                                <tr>
                                    <td>
                                        @include('shared.entity', [ 'entity' => $mietvertrag])
                                    </td>
                                    <td>
                                        @include('shared.entity', [ 'entity' => $mietvertrag->einheit])
                                    </td>
                                    <td>
                                        @include('shared.entity', [ 'entity' => $mietvertrag->einheit->haus])
                                    </td>
                                    <td>
                                        @include('shared.entity', [ 'entity' => $mietvertrag->einheit->haus->objekt])
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
        @if(!$person->kaufvertraege->isEmpty())
            <div class="col-xs-12 col-sm-6">
                <div class="card card-expandable">
                    <div class="card-content">
                        <span class="card-title">Eigentum ({{$person->kaufvertraege->count()}})</span>
                        <table class="striped responsive-table">
                            <thead>
                            <th>Kaufvertrag</th>
                            <th>Einheit</th>
                            <th>Haus</th>
                            <th>Objekt</th>
                            </thead>
                            <tbody>
                            @foreach( $person->kaufvertraege()->defaultOrder()->with('einheit.haus.objekt')->get() as $kaufvertrag )
                                <tr>
                                    <td>
                                        @include('shared.entity', [ 'entity' => $kaufvertrag])
                                    </td>
                                    <td>
                                        @include('shared.entity', [ 'entity' => $kaufvertrag->einheit])
                                    </td>
                                    <td>
                                        @include('shared.entity', [ 'entity' => $kaufvertrag->einheit->haus])
                                    </td>
                                    <td>
                                        @include('shared.entity', [ 'entity' => $kaufvertrag->einheit->haus->objekt])
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
        @if(!$person->audits->isEmpty())
            <div class="col-xs-12 col-sm-6">
                @include('shared.cards.audits', ['audits' => $person->audits, 'title' => 'Historie'])
            </div>
        @endif
    </div>
@endsection