@extends('layouts.main')

@section('submenu')
    <?php include(base_path('legacy/options/links/links.person.php')); ?>
@endsection

@section('content')
    <div class="row">
        <div class="col s12">
            <div class="card-panel white">
                <div class="row">
                    <div class="input-field col s4 m4">
                        <a class="btn waves-effect waves-light" href="{{ route('web::personen::legacy', ['anzeigen' => 'person_erfassen']) }}"><i class="material-icons left">add_circle_outline</i>Neu</a>
                    </div>
                    <form method="get">
                        <div class="input-field col s12 offset-m2 m6 offset-l4 l4">
                            <i class="material-icons prefix">filter_list</i>
                            <input id="filter" name="q" value="{{ request()->input('q') }}" type="text"
                                   class="validate">
                            <label for="filter">Filter</label>
                        </div>
                    </form>
                </div>
                <div class="row">
                    <div class="col s12">
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
                                    <td>{{ $person->PERSON_NACHNAME }}, {{ $person->PERSON_VORNAME }}</td>
                                    <td>
                                        @if(!$person->mietvertraege->isEmpty())
                                            <b>Mietverträge</b><br>
                                        @endif
                                        @foreach($person->mietvertraege as $mietvertrag)
                                            @include('shared.namedlink', [ 'entity' => $mietvertrag->einheit]) <br>
                                        @endforeach
                                        @if(!$person->eigentuemer->isEmpty())
                                            <b>Wohneigentum</b><br>
                                        @endif
                                        @foreach($person->eigentuemer as $eigentuemer)
                                            @include('shared.namedlink', [ 'entity' => $eigentuemer->einheit]) <br>
                                        @endforeach
                                    </td>
                                    <td>
                                        @if(!$person->mietvertraege->isEmpty())
                                            <br>
                                        @endif
                                        @foreach($person->mietvertraege as $mietvertrag)
                                            @include('shared.namedlink', [ 'entity' => $mietvertrag->einheit->haus])
                                            <br>
                                        @endforeach
                                        @if(!$person->eigentuemer->isEmpty())
                                            <br>
                                        @endif
                                        @foreach($person->eigentuemer as $eigentuemer)
                                            @include('shared.namedlink', [ 'entity' => $eigentuemer->einheit->haus])
                                            <br>
                                        @endforeach
                                    </td>
                                    <td>
                                        @if(!$person->mietvertraege->isEmpty())
                                            <br>
                                        @endif
                                        @foreach($person->mietvertraege as $mietvertrag)
                                            @include('shared.namedlink', [ 'entity' => $mietvertrag->einheit->haus->objekt])
                                            <br>
                                        @endforeach
                                        @if(!$person->eigentuemer->isEmpty())
                                            <br>
                                        @endif
                                        @foreach($person->eigentuemer as $eigentuemer)
                                            @include('shared.namedlink', [ 'entity' => $eigentuemer->einheit->haus->objekt])
                                            <br>
                                        @endforeach
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="row">
                    <span class="center-align">
                        @if(request()->has('q'))
                            {!! $personen->addQuery('q',request()->input('q'))->render() !!}
                        @else
                            {!! $personen->render() !!}
                        @endif
                    </span>
                </div>
            </div>
        </div>
    </div>
@endsection