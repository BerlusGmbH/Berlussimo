@extends('layouts.page')
@section('page-content')
    <div class="navbar-fixed">
        <nav>
            <div class="nav-wrapper">
                <a href='/'><img class="left" style="height:100%; padding: 5px" src="/images/berlus_logo.svg"></a>
                <div class="left">
                    @yield('breadcrumbs')
                </div>
                @yield('navbar')
            </div>
        </nav>
    </div>

    <main>
        @yield('app-content')
    </main>

    <footer class="page-footer">
        <div class="footer-copyright center">
            <b>Berlussimo</b> wird von der <a target='_new' href='http://www.berlus.de'>Berlus GmbH</a> -
            Hausverwaltung zur Verfügung gestellt.
        </div>
    </footer>
@endsection