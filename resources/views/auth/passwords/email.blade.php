@extends('layouts.app')

<!-- Main Content -->
@section('app-content')
    <div class="container">
        <div class="row">
            <div class="col s12 m10 l8 offset-m1 offset-l2">
                <div class="card">
                    <div class="card-content">
                        <div class="card-title">Reset Password</div>
                        @if (session('status'))
                            <div class="alert alert-success">
                                {{ session('status') }}
                            </div>
                        @endif

                        <form role="form" method="POST" action="{{ url('/password/email') }}">
                            {!! csrf_field() !!}
                            <div class="input-field">
                                <i class="material-icons prefix">email</i>
                                <input type="email" id="email" name="email"
                                       class="validate {{ $errors->has('email') ? 'invalid' : '' }}"
                                       value="{{ old('email') }}">
                                <span class="error-block">{{ $errors->has('email') ? $errors->first('email') : '' }}</span>
                                <label for="email">E-Mail Address</label>
                            </div>

                            <div class="input-field">
                                <button class="btn waves-effect waves-light" type="submit">Send Reset Link
                                    <i class="material-icons right">send</i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
