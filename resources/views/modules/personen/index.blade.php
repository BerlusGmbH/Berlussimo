@extends('layouts.main')

@section('submenu')
    <?php include(base_path('legacy/options/links/links.person.php')); ?>
@endsection

@section('content')
    <div class="card-panel white">
        <div class="row">
            <div class="input-field col-xs-4 col-md-4">
                <a class="btn waves-effect waves-light"
                   href="{{ route('web::personen::legacy', ['anzeigen' => 'person_erfassen']) }}"><i
                            class="material-icons left">add</i>Neu</a>
            </div>
            <div class="input-field col-xs-12 col-md-offset-2 col-md-6 col-lg-offset-4 col-lg-4">
                <form method="get">
                    <i class="material-icons prefix">filter_list</i>
                    <input id="filter" name="q" value="{{ request()->input('q') }}" type="text"
                           class="validate" autocomplete="off">
                    <label for="filter">Filter</label>

                </form>
            </div>
        </div>
        <div class="row">
            <div class="col col-xs-12">
                <table class="striped">
                    <thead>
                    <th>Name</th>
                    <th>Einheiten</th>
                    <th>Häuser</th>
                    <th>Objekte</th>
                    </thead>
                    <tbody>
                    @foreach( $personen as $person )
                        <tr>
                            <td>
                                <a href="{{ route('web::personen::show', ['id' => $person->PERSON_ID]) }}">{{ $person->PERSON_NACHNAME }}
                                    , {{ $person->PERSON_VORNAME }}</a></td>
                            <td>
                                @php($firstMietvertrag = true)
                                @foreach($person->mietvertraege as $mietvertrag)
                                    @if(isset($mietvertrag->einheit))
                                        @if($firstMietvertrag)
                                            @php($firstMietvertrag = false)
                                            <b>Mietverträge</b><br>
                                        @endif
                                        @include('shared.namedentity', [ 'entity' => $mietvertrag->einheit]) <br>
                                    @endif
                                @endforeach
                                @php($firstKaufvertrag = true)
                                @foreach($person->kaufvertraege as $kaufvertrag)
                                    @if(isset($kaufvertrag->einheit))
                                        @if($firstKaufvertrag)
                                            @php($firstKaufvertrag = false)
                                            <b>Wohneigentum</b><br>
                                        @endif
                                        @include('shared.namedentity', [ 'entity' => $kaufvertrag->einheit]) <br>
                                    @endif
                                @endforeach
                            </td>
                            <td>
                                @if(!$person->mietvertraege->isEmpty())
                                    <br>
                                @endif
                                @foreach($person->mietvertraege as $mietvertrag)
                                    @if(isset($mietvertrag->einheit))
                                        @include('shared.namedentity', [ 'entity' => $mietvertrag->einheit->haus])
                                        <br>
                                    @endif
                                @endforeach
                                @if(!$person->kaufvertraege->isEmpty())
                                    <br>
                                @endif
                                @foreach($person->kaufvertraege as $kaufvertrag)
                                    @if(isset($kaufvertrag->einheit))
                                        @include('shared.namedentity', [ 'entity' => $kaufvertrag->einheit->haus])
                                        <br>
                                    @endif
                                @endforeach
                            </td>
                            <td>
                                @if(!$person->mietvertraege->isEmpty())
                                    <br>
                                @endif
                                @foreach($person->mietvertraege as $mietvertrag)
                                    @if(isset($mietvertrag->einheit))
                                        @include('shared.namedentity', [ 'entity' => $mietvertrag->einheit->haus->objekt])
                                        <br>
                                    @endif
                                @endforeach
                                @if(!$person->kaufvertraege->isEmpty())
                                    <br>
                                @endif
                                @foreach($person->kaufvertraege as $kaufvertrag)
                                    @if(isset($kaufvertrag->einheit))
                                        @include('shared.namedentity', [ 'entity' => $kaufvertrag->einheit->haus->objekt])
                                        <br>
                                    @endif
                                @endforeach
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="row center-xs">
            @if(request()->has('q'))
                {!! $personen->addQuery('q',request()->input('q'))->render() !!}
            @else
                {!! $personen->render() !!}
            @endif
        </div>
    </div>
@endsection