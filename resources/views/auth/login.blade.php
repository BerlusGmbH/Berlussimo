@extends('layouts.app')

@section('main')
    <div class="row">
        <div class="card col s12 m10 l8 offset-m1 offset-l2">
            <div class="card-content">
                <span class="card-title">Login</span>
                <form role="form" method="POST" action="{{ url('/login') }}">
                    {!! csrf_field() !!}

                    <div class="row">
                        <div class="{{ $errors->has('email') ? 'invalid' : '' }}">
                            <div class="input-field col s12">
                                <label for="email">E-Mail Address</label>
                                <input type="email" name="email" id="email" value="{{ old('email') }}">

                                @if ($errors->has('email'))
                                    <span class="help-block">
                                <strong>{{ $errors->first('email') }}</strong>
                            </span>
                                @endif
                            </div>
                        </div>

                        <div class="{{ $errors->has('password') ? ' has-error' : '' }}">
                            <div class="input-field col s12">
                                <label for="password">Password</label>
                                <input type="password" name="password" id="password">

                                @if ($errors->has('password'))
                                    <span class="error">
                                <strong>{{ $errors->first('password') }}</strong>
                            </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col s6">
                            <input type="checkbox" name="remember" id="remember">
                            <label for="remember">Remember Me</label>
                        </div>
                        <div class="col s6">
                            <a class="right" href="{{ url('/password/reset') }}">Forgot Your
                                Password?</a>
                        </div>
                        <div class="col s12">
                            <button type="submit" class="btn right">
                                <i class="fa fa-btn fa-sign-in"></i>Login
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
