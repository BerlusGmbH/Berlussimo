@extends('layouts.main-without-menu')
@section('app-content')
    @if($content != "")
        {!! $content !!}
    @endif
@endsection