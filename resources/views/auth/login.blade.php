@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-xs-12 col-md-10 col-lg-8 col-md-offset-1 col-lg-offset-2">
            <div class="card">
                <div class="card-content">
                    <span class="card-title">Login</span>
                    <form role="form" method="POST" action="{{ url('/login') }}">
                        {!! csrf_field() !!}
                        <div class="input-field">
                            <i class="mdi mdi-email prefix"></i>
                            <input type="email" id="email" name="email"
                                   class="validate {{ $errors->has('email') ? 'invalid' : '' }}"
                                   value="{{ old('email') }}">
                            <span class="error-block">{{ $errors->has('email') ? $errors->first('email') : '' }}</span>
                            <label for="email">E-Mail Adresse</label>
                        </div>

                        <div class="input-field">
                            <i class="mdi mdi-lock prefix"></i>
                            <input type="password" id="password" name="password">
                            <label for="password">Passwort</label>
                        </div>

                        <div class="row">
                            <div class="col-xs-6">
                                <input type="checkbox" class='filled-in' name="remember" id="remember">
                                <label for="remember">Angemeldet bleiben</label>
                            </div>
                            <div class="col-xs-6">
                                <a class="right" href="{{ url('/password/reset') }}">Password vergessen</a>
                            </div>
                            <div class="col-xs-12">
                                <button type="submit" class="btn right">
                                    <i class="fa fa-btn fa-sign-in"></i>Anmelden
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
