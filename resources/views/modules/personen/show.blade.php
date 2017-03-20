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
            <div class="card">
                <div class="card-content">
                    <div class="card-title">
                        @if($person->PERSON_GEBURTSTAG->year > 1902)
                            @php($margin_bot = '0px')
                        @else
                            @php($margin_bot = '12px')
                        @endif
                        <div class="row" style="line-height: 24px; margin-bottom: {{ $margin_bot }}; margin-top: 12px">
                            <div class="col-xs-12 col-sm-8">
                                @include('shared.entities.person', ['entity' => $person])
                                @if($person->PERSON_GEBURTSTAG->year > 1902)
                                    <div style="font-size: small; line-height: 24px; margin-left: 6px">
                                        <i class="mdi mdi-star"></i> {{ $person->PERSON_GEBURTSTAG->formatLocalized("%d.%m.%Y") }}
                                    </div>
                                @endif
                            </div>
                            <div class="col-xs-12 col-sm-4 end-xs">
                                <a href="{{ route('web::personen::legacy', ['anzeigen' => 'person_aendern', 'person_id' => $person->PERSON_ID]) }}"><i
                                            class="mdi mdi-pencil"></i></a>
                                <a href="{{ route('web::details::legacy', ['option' => 'details_hinzu', 'detail_tabelle' => 'PERSON', 'detail_id' => $person->PERSON_ID]) }}"><i
                                            class="mdi mdi-table-edit"></i></a>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        @inject('phonelocator', 'App\Services\PhoneLocator')
                        @foreach($person->phones as $phone)
                            <div class="col-xs-12 col-sm-6">
                                @include('shared.entities.details.telefon', ['entity' => $phone])
                            </div>
                        @endforeach
                        @foreach($person->emails as $email)
                            <div class="col-xs-12 col-sm-6">
                                @include('shared.entities.details.email', ['entity' => $email])
                            </div>
                        @endforeach
                        @foreach($person->faxs as $fax)
                            <div class="col-xs-12 col-sm-6">
                                @include('shared.entities.details.fax', ['entity' => $fax])
                            </div>
                        @endforeach
                        @foreach($person->adressen as $anschrift)
                            <div class="col-xs-12 col-sm-6">
                                @include('shared.entities.details.adresse', ['entity' => $anschrift])
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
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
    </div>
@endsection