@extends('layouts.app')

@section('content')
    <app-login>
        <input slot="csrf_token" type="hidden" name="_token" value="{{csrf_token()}}">
    </app-login>
@endsection
