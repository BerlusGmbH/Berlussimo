@extends('layouts.main')

@section('breadcrumbs')
    <a href="{{ route('web::personen::index') }}" class="breadcrumb">Personen</a>
    <a href="" class="breadcrumb">Neu</a>
@endsection

@section('content')
    <div class="row">
        <div class="col-xs-12">
            <div class="card-panel">
                <form action="{{route('web::personen::store')}}" method="post">
                    <div class="row">
                        <div class="input-field col-xs-12 col-md-6">
                            <i class="mdi mdi-alphabetical prefix"></i>
                            <input type="text" minlength="1" maxlength="255" id="name" name="name"
                                   class="validate {{$errors->has('name') ? 'invalid' : ''}}"
                                   value="{{old('name')}}">
                            <label for="name">Nachname</label>
                            <span class="error-block">{{$errors->has('name') ? $errors->first('name') : ''}}</span>
                        </div>
                        <div class="input-field col-xs-12 col-md-6">
                            <i class="mdi mdi-alphabetical prefix"></i>
                            <input type="text" minlength="1" maxlength="255" id="first_name" name="first_name"
                                   class="validate {{$errors->has('first_name') ? 'invalid' : ''}}"
                                   value="{{old('first_name')}}">
                            <label for="first_name">Vorname</label>
                            <span class="error-block">{{$errors->has('first_name') ? $errors->first('first_name') : ''}}</span>
                        </div>
                        <div class="input-field col-xs-12 col-md-6">
                            <i class="mdi mdi-numeric prefix"></i>
                            <select id="sex" name="sex">
                                <option value="">unbekannt</option>
                                <option value="weiblich" {{(old('sex') == 'weiblich') ? 'selected' : ''}}>weiblich
                                </option>
                                <option value="männlich" {{(old('sex') == 'männlich') ? 'selected' : ''}}>männlich
                                </option>
                            </select>
                            <label>Geschlecht</label>
                        </div>
                        <div class="input-field col-xs-12 col-md-6">
                            <i class="mdi mdi-cake prefix"></i>
                            <input type="date" id="birthday" name="birthday"
                                   class="validate {{$errors->has('birthday') ? 'invalid' : ''}}"
                                   value="{{ old('birthday') }}">
                            <span class="error-block">{{$errors->has('birthday') ? $errors->first('birthday') : ''}}</span>
                            <label class="active" for="birthday"
                                   data-error="{{$errors->has('birthday') ? $errors->first('birthday') : ''}}">Geburtstag</label>
                        </div>
                        <div class="input-field col-xs-12 col-md-6">
                            <i class="mdi mdi-mail-ru prefix"></i>
                            <input type="email" maxlength="255" id="email" name="email"
                                   class="validate {{$errors->has('email') ? 'invalid' : ''}}"
                                   value="{{old('email')}}">
                            <label for="first_name">E-Mail</label>
                            <span class="error-block">{{$errors->has('email') ? $errors->first('email') : ''}}</span>
                        </div>
                        <div class="input-field col-xs-12 col-md-6">
                            <i class="mdi mdi-phone prefix"></i>
                            <input type="tel" maxlength="255" id="phone" name="phone"
                                   class="validate {{$errors->has('phone') ? 'invalid' : ''}}"
                                   value="{{old('phone')}}">
                            <label for="first_name">Telefon</label>
                            <span class="error-block">{{$errors->has('phone') ? $errors->first('phone') : ''}}</span>
                        </div>
                        <div class="input-field col-xs-12 col-sm-3 col-sm-offset-9 col-md-2 col-md-offset-10 end-xs">
                            <button class="btn waves-effect waves-light" type="submit">Erfassen
                                <i class="mdi mdi-plus left"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection