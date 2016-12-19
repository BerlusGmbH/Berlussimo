@extends('layouts.main')

@section('submenu')
    <?php include(base_path('legacy/options/links/links.person.php')); ?>
@endsection

@section('content')
    <div class="row">
        <div class="col col-xs-12">
            <div class="card-panel white">
                <div class="row">
                    <div class="input-field col-xs-4 col-md-4">
                        <a class="btn waves-effect waves-light"
                           href="{{ route('web::personen::legacy', ['anzeigen' => 'person_erfassen']) }}"><i
                                    class="material-icons left">add_circle_outline</i>Neu</a>
                    </div>
                    <div class="input-field col-xs-12 col-md-offset-2 col-md-6 col-lg-offset-4 col-lg-4">
                        <form method="get">
                            <i class="material-icons prefix">filter_list</i>
                            <input id="filter" name="q" value="{{ request()->input('q') }}" type="text"
                                   class="validate">
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
                                    <td><a href="{{ route('web::personen::show', ['id' => $person->PERSON_ID]) }}">{{ $person->PERSON_NACHNAME }}, {{ $person->PERSON_VORNAME }}</a></td>
                                    <td>
                                        @if(!$person->mietvertraege->isEmpty())
                                            <b>Mietverträge</b><br>
                                        @endif
                                        @foreach($person->mietvertraege as $mietvertrag)
                                            @include('shared.namedentity', [ 'entity' => $mietvertrag->einheit]) <br>
                                        @endforeach
                                        @if(!$person->kaufvertraege->isEmpty())
                                            <b>Wohneigentum</b><br>
                                        @endif
                                        @foreach($person->kaufvertraege as $kaufvertrag)
                                            @include('shared.namedentity', [ 'entity' => $kaufvertrag->einheit]) <br>
                                        @endforeach
                                    </td>
                                    <td>
                                        @if(!$person->mietvertraege->isEmpty())
                                            <br>
                                        @endif
                                        @foreach($person->mietvertraege as $mietvertrag)
                                            @include('shared.namedentity', [ 'entity' => $mietvertrag->einheit->haus])
                                            <br>
                                        @endforeach
                                        @if(!$person->kaufvertraege->isEmpty())
                                            <br>
                                        @endif
                                        @foreach($person->kaufvertraege as $kaufvertrag)
                                            @include('shared.namedentity', [ 'entity' => $kaufvertrag->einheit->haus])
                                            <br>
                                        @endforeach
                                    </td>
                                    <td>
                                        @if(!$person->mietvertraege->isEmpty())
                                            <br>
                                        @endif
                                        @foreach($person->mietvertraege as $mietvertrag)
                                            @include('shared.namedentity', [ 'entity' => $mietvertrag->einheit->haus->objekt])
                                            <br>
                                        @endforeach
                                        @if(!$person->kaufvertraege->isEmpty())
                                            <br>
                                        @endif
                                        @foreach($person->kaufvertraege as $kaufvertrag)
                                            @include('shared.namedentity', [ 'entity' => $kaufvertrag->einheit->haus->objekt])
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