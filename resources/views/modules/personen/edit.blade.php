@extends('layouts.main-without-menu')

@section('breadcrumbs')
    <a href="{{route('web::personen::index')}}" class="breadcrumb">Personen</a>
    <a href="{{route('web::personen::show',['id' => $person->id])}}" class="breadcrumb">{{$person->full_name}}</a>
    <a href="{{route('web::personen::edit',['id' => $person->id])}}" class="breadcrumb">Bearbeiten</a>
@endsection

@section('content')
    <div class="row">
        <div class="col-xs-12">
            <div class="card-panel">
                <form action="{{route('web::personen::update', ['id' => $person->id])}}" method="post">
                    {{method_field('PATCH')}}
                    <div class="row">
                        <div class="input-field col-xs-12 col-md-6">
                            <i class="mdi mdi-alphabetical prefix"></i>
                            <input type="text" minlength="1" maxlength="255" id="name" name="name"
                                   class="validate {{$errors->has('name') ? 'invalid' : ''}}"
                                   value="{{$person->name}}">
                            <label for="name">Nachname</label>
                            <span class="error-block">{{$errors->has('name') ? $errors->first('name') : ''}}</span>
                        </div>
                        <div class="input-field col-xs-12 col-md-6">
                            <i class="mdi mdi-alphabetical prefix"></i>
                            <input type="text" minlength="1" maxlength="255" id="first_name" name="first_name"
                                   class="validate {{$errors->has('first_name') ? 'invalid' : ''}}"
                                   value="{{$person->first_name}}">
                            <label for="first_name">Vorname</label>
                            <span class="error-block">{{$errors->has('first_name') ? $errors->first('first_name') : ''}}</span>
                        </div>
                        <div class="input-field col-xs-12 col-md-6">
                            <i class="mdi mdi-cake prefix"></i>
                            <input type="date" id="birthday" name="birthday"
                                   class="validate {{$errors->has('birthday') ? 'invalid' : ''}}"
                                   value="{{(!is_null($person->birthday)) ? $person->birthday->toDateString() : ''}}">
                            <span class="error-block">{{$errors->has('birthday') ? $errors->first('birthday') : ''}}</span>
                            <label class="active" for="birthday"
                                   data-error="{{$errors->has('birthday') ? $errors->first('birthday') : ''}}">Geburtstag</label>
                        </div>
                        <div class="input-field col-xs-12 end-xs">
                            <button class="btn waves-effect waves-light red" type="submit">Übernehmen
                                <i class="mdi mdi-pencil left"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection