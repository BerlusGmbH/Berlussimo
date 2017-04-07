@extends('layouts.main-without-menu')

@section('breadcrumbs')
    <a href="{{ route('web::personen::index') }}" class="breadcrumb">Personen</a>
    <a href="" class="breadcrumb">Neu</a>
@endsection

@section('content')
    <div class="row">
        <div class="col-xs-12">
            <div class="card-panel">
                <form action="{{ route('web::personen::store') }}" method="post" autocomplete="off">
                    <div class="row">
                        <div class="input-field col-xs-12 col-md-6">
                            <i class="mdi mdi-alphabetical prefix"></i>
                            <input type="text" id="name" name="name"
                                   value="{{old('name')}}" readonly>
                            <label for="name" class="active">Nachname</label>
                        </div>
                        <div class="input-field col-xs-12 col-md-6">
                            <i class="mdi mdi-alphabetical prefix"></i>
                            <input type="text" id="first_name" name="first_name"
                                   value="{{old('first_name')}}" readonly>
                            <label for="first_name" class="active">Vorname</label>
                        </div>
                        <div class="input-field col-xs-12 col-md-6">
                            <i class="mdi mdi-numeric prefix"></i>
                            <select id="sex" name="sex" disabled>
                                <option value="">unbekannt</option>
                                <option value="weiblich" {{(old('sex') == 'weiblich') ? 'selected' : ''}}>weiblich
                                </option>
                                <option value="männlich" {{(old('sex') == 'männlich') ? 'selected' : ''}}>männlich
                                </option>
                            </select>
                            <label for="sex">Geschlecht</label>
                            <input type="hidden" name="sex" value="{{old('sex')}}">
                        </div>
                        <div class="input-field col-xs-12 col-md-6">
                            <i class="mdi mdi-cake prefix"></i>
                            <input type="date" id="birthday" name="birthday"
                                   value="{{old('birthday')}}" readonly>
                            <label class="active" for="birthday">Geburtstag</label>
                        </div>
                        <div class="input-field col-xs-12 col-md-6">
                            <i class="mdi mdi-mail-ru prefix"></i>
                            <input type="email" id="email" name="email"
                                   value="{{old('email')}}" readonly>
                            <label for="email" class="active">E-Mail</label>
                        </div>
                        <div class="input-field col-xs-12 col-md-6">
                            <i class="mdi mdi-phone prefix"></i>
                            <input type="tel" id="phone" name="phone"
                                   value="{{old('phone')}}" readonly>
                            <label for="phone" class="active">Telefon</label>
                        </div>
                        <div class="input-field col-xs-12 col-sm-8 col-md-9">
                            <i class="mdi mdi-alert left red-text prefix"></i>
                            <input type="hidden" name="accept_dublicates" value="on">
                            <label class="red-text">Der Name
                                "<strong>{{old('first_name')}}{{(!empty(old('first_name')) ? ' ' : '')}}{{old('name')}}</strong>"
                                existiert bereits. Dennoch hinzufügen?</label>
                        </div>
                        <div class="input-field col-xs-12 col-sm-4 col-md-3 end-xs">
                            <a class="btn waves-effect waves-light" onclick="window.history.back()">Nein
                                <i class="mdi mdi-close left"></i>
                            </a>
                            <button class="btn waves-effect waves-light red" type="submit">Ja
                                <i class="mdi mdi-check left"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        @foreach($dublicates as $dublicate)
            <div class="col-xs-12 col-md-6 col-lg-4">
                @include('shared.cards.person', ['person' => $dublicate])
            </div>
        @endforeach
    </div>
@endsection