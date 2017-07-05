@extends('layouts.page')
@section('page-content')
    <div class="navbar-fixed">
        <nav class="nav-extended">
            <div class="nav-wrapper">
                <a class="brand-logo primary-color-dark text-variation-2"
                   href='/'>
                    <img style="padding: 10px; position: absolute"
                         src="/images/berlus_logo.svg">
                    <span style="margin-left: 65px">berlussimo</span>
                </a>
                @yield('navbar')
            </div>
            <div class="nav-content">
                @if(!Auth::guest())
                    @include('shared.menus.main')
                @endif
            </div>
        </nav>
    </div>

    @yield('sidenav')

    <main>
        <div style="margin-top: 10px" class="center-align">
            @include("shared.messages")
        </div>
        @yield("content")
    </main>

    <footer class="page-footer">
        <div class="footer-copyright">
            <div class="container center">
                <b>Berlussimo</b> wird von der <a target='_new' class="primary-color text-variation-3"
                                                  href='http://www.berlus.de'>Berlus GmbH</a> -
                Hausverwaltung zur Verfügung gestellt.
            </div>
        </div>
    </footer>
@endsection