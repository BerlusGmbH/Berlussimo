@extends('layouts.app')

@section('app-content')
    <div class="row">
        <div class="card col s12 m10 l8 offset-m1 offset-l2">
            <div class="card-content">
                <span class="card-title">Login</span>
                <form role="form" method="POST" action="{{ url('/login') }}">
                    {!! csrf_field() !!}
                    <div class="input-field">
                        <i class="material-icons prefix">email</i>
                        <input type="email" id="email" name="email"
                               class="validate {{ $errors->has('email') ? 'invalid' : '' }}"
                               value="{{ old('email') }}">
                        <span class="error-block">{{ $errors->has('email') ? $errors->first('email') : '' }}</span>
                        <label for="email">E-Mail Adresse</label>
                    </div>

                    <div class="input-field">
                        <i class="material-icons prefix">lock</i>
                        <input type="password" id="password" name="password">
                        <label for="password">Passwort</label>
                    </div>

                    <div class="row">
                        <div class="col s6">
                            <input type="checkbox" name="remember" id="remember">
                            <label for="remember">Angemeldet bleiben</label>
                        </div>
                        <div class="col s6">
                            <a class="right" href="{{ url('/password/reset') }}">Password vergessen</a>
                        </div>
                        <div class="col s12">
                            <button type="submit" class="btn right">
                                <i class="fa fa-btn fa-sign-in"></i>Anmelden
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
