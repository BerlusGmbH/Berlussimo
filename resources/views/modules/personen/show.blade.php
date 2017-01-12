@extends('layouts.main')

@section('submenu')
    @php(include(base_path('legacy/options/links/links.person.php')))
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
                            <div class="col-xs-10">
                                {{ $person->PERSON_NACHNAME }},
                                {{ $person->PERSON_VORNAME }}
                                @if($person->sex[0]->DETAIL_INHALT == 'männlich')
                                    <i class="mdi mdi-gender-male"></i>
                                @elseif($person->sex[0]->DETAIL_INHALT == 'weiblich')
                                    <i class="mdi mdi-gender-female"></i>
                                @endif
                            </div>
                            <div class="col-xs-1 end-xs">
                                <a href="{{ route('web::personen::legacy', ['anzeigen' => 'person_aendern', 'person_id' => $person->PERSON_ID]) }}"><i
                                            class="mdi mdi-pencil"></i></a>
                            </div>
                            <div class="col-xs-1 end-xs">
                                <a href="{{ route('web::details::legacy', ['option' => 'details_hinzu', 'detail_tabelle' => 'PERSON', 'detail_id' => $person->PERSON_ID]) }}"><i
                                            class="mdi mdi-table-edit"></i></a>
                            </div>
                        </div>
                        @if($person->PERSON_GEBURTSTAG->year > 1902)
                            <div style="font-size: small; line-height: 24px; margin-bottom:12px;">
                                <i class="mdi mdi-star"></i> {{ $person->PERSON_GEBURTSTAG->formatLocalized("%d.%m.%Y") }}
                            </div>
                        @endif
                    </div>
                    <div class="row">
                        @inject('phonelocator', 'App\Services\PhoneLocator')
                        @foreach($person->phones as $phone)
                            <div class="col-xs-6 detail">
                                <i class="mdi mdi-phone"></i>
                                {!! $phonelocator->url(e($phone->DETAIL_INHALT), e($phone->DETAIL_BEMERKUNG)) !!}
                            </div>
                        @endforeach
                        @foreach($person->faxs as $fax)
                            <div class="col-xs-6 detail">
                                <i class="mdi mdi-fax"></i>
                                <a href="fax:{{ $fax->DETAIL_INHALT }}">{{ $fax->DETAIL_INHALT }}{{ $fax->DETAIL_BEMERKUNG !== '' ? ', ' . $fax->DETAIL_BEMERKUNG : '' }}</a>
                            </div>
                        @endforeach
                        @foreach($person->emails as $email)
                            <div class="col-xs-6 detail">
                                <i class="mdi mdi-mail-ru"></i>
                                <a href="mailto:{{ $person->PERSON_VORNAME }} {{ $person->PERSON_NACHNAME }} <{{ $email->DETAIL_INHALT }}>">{{ $email->DETAIL_INHALT }}{{ $email->DETAIL_BEMERKUNG !== '' ? ', ' . $email->DETAIL_BEMERKUNG : '' }}</a>
                            </div>
                        @endforeach
                        @foreach($person->adressen as $anschrift)
                            <div class="col-xs-6 detail">
                                <i class="mdi mdi-email"></i>
                                {{ $anschrift->detail_inhalt_with_br }}{{ $anschrift->DETAIL_BEMERKUNG !== '' ? ', ' . $anschrift->DETAIL_BEMERKUNG : '' }}
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        @if(!$person->commonDetails->isEmpty())
            <div class="col-xs-12 col-sm-6">
                <div class="card">
                    <div class="card-content">
                        <div class="card-title">Allgemeine Details ({{ $person->commonDetails->count() }})</div>
                        <table class="striped">
                            <thead>
                            <th>Typ</th>
                            <th>Wert</th>
                            <th>Bemerkung</th>
                            </thead>
                            <tbody>
                            @foreach( $person->commonDetails as $detail )
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
        @if(!$person->mietvertraege->isEmpty())
            <div class="col-xs-12 col-sm-6">
                <div class="card">
                    <div class="card-content">
                        <span class="card-title">Mietverträge ({{ $person->mietvertraege->count() }})</span>
                        <table class="striped">
                            <thead>
                            <th>Mietvertrag</th>
                            <th>Einheit</th>
                            <th>Haus</th>
                            <th>Objekt</th>
                            </thead>
                            <tbody>
                            @foreach( $person->mietvertraege as $mietvertrag )
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
                <div class="card">
                    <div class="card-content">
                        <span class="card-title">Eigentum ({{ $person->kaufvertraege->count() }})</span>
                        <table class="striped">
                            <thead>
                            <th>Kaufvertrag</th>
                            <th>Einheit</th>
                            <th>Haus</th>
                            <th>Objekt</th>
                            </thead>
                            <tbody>
                            @foreach( $person->kaufvertraege as $kaufvertrag )
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