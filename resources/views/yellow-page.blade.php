@extends('layouts.app')
@section('app-content')
    @if($content != "")
        {!! $content !!}
    @endif
@endsection