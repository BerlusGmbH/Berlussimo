@extends('layouts.app')

<!-- Main Content -->
@section('content')
    <div class="container">
        <div class="row">
            <div class="col-xs-12 col-md-10 col-lg-8 col-md-offset-1 col-lg-offset-2">
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
                                <i class="mdi mdi-email prefix"></i>
                                <input type="email" id="email" name="email"
                                       class="validate {{ $errors->has('email') ? 'invalid' : '' }}"
                                       value="{{ old('email') }}">
                                <span class="error-block">{{ $errors->has('email') ? $errors->first('email') : '' }}</span>
                                <label for="email">E-Mail Address</label>
                            </div>

                            <div class="input-field">
                                <button class="btn waves-effect waves-light" type="submit">Send Reset Link
                                    <i class="mdi mdi-send right"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
