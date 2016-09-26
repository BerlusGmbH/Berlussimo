@extends('layouts.app')

@section('app-content')
    <div class="container">
        <div class="row">
            <div class="col s12 m10 l8 offset-m1 offset-l2">
                <div class="card">
                    <div class="card-title">Reset Password</div>

                    <div class="card-content">
                        <form class="form-horizontal" role="form" method="POST" action="{{ url('/password/reset') }}">
                            {!! csrf_field() !!}
                            <input type="hidden" name="token" value="{{ $token }}">
                            <div class="row">
                                <div class="input-field col s12">
                                    <i class="material-icons prefix">email</i>
                                    <input type="email" id="email" name="email"
                                           class="validate {{ $errors->has('email') ? 'invalid' : '' }}"
                                           value="{{ $email || old('email') }}">
                                    <span class="error-block">{{ $errors->has('email') ? $errors->first('email') : '' }}</span>
                                    <label for="email">E-Mail Address</label>
                                </div>

                                <div class="input-field col s12">
                                    <i class="material-icons prefix">lock</i>
                                    <input type="email" id="password" name="password"
                                           class="validate {{ $errors->has('password') ? 'invalid' : '' }}">
                                    <span class="error-block">{{ $errors->has('password') ? $errors->first('password') : '' }}</span>
                                    <label for="password">Password</label>
                                </div>

                                <div class="input-field col s12">
                                    <i class="material-icons prefix">lock</i>
                                    <input type="email" id="password_confirmation" name="password_confirmation"
                                           class="validate {{ $errors->has('password_confirmation') ? 'invalid' : '' }}">
                                    <span class="error-block">{{ $errors->has('password_confirmation') ? $errors->first('password_confirmation') : '' }}</span>
                                    <label for="password_confirmation">Confirm Password</label>
                                </div>

                                <div class="input-field col s12">
                                    <button class="btn waves-effect waves-light" type="submit">Reset Password
                                        <i class="material-icons right">send</i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
