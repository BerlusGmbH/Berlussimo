@extends('layouts.app')

@section('navbar')
    <a href='' data-activates="berlussimo-sidenav" class="button-collapse right" xmlns="http://www.w3.org/1999/html"><i
                class="mdi mdi-menu"></i></a>
    <ul class="right hide-on-med-and-down">
        <li style="height: 64px">
            @include('shared.searchbar')
        </li>
        <li><a class="dropdown-button" data-activates="user-dropdown"><i
                        class="mdi mdi-account left"></i>{{Auth::user()->pretty_name}}<i class="material-icons right">arrow_drop_down</i></a>
        </li>
    </ul>
@endsection

@section('nav-content')
    <ul class="side-nav" id="berlussimo-sidenav">
        <li>
            <div class="userView primary-color">
                <a href="#!user"><img class="circle" src="/images/berlus_logo.svg"></a>
                <a href="#!name"><span class="white-text name">{{Auth::user()->pretty_name}}</span></a>
                <a href="#!email"><span class="white-text email">{{Auth::user()->email}}</span></a>
            </div>
        </li>
        <li>
            <a href="/logout"><i class="mdi mdi-exit-to-app left"></i>Abmelden</a>
        </li>
    </ul>

    <ul id="user-dropdown" class="dropdown-content">
        <li><a href="/logout"><i class="mdi mdi-exit-to-app left"></i>Abmelden</a></li>
    </ul>
@endsection