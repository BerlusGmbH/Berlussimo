@extends('layouts.page')
@section('page-content')
    <div class="navbar-fixed">
        <nav>
            <div class="nav-wrapper">
                <a href='http://www.berlus.de'><img style="height:100%; padding: 5px" src="/images/berlus_logo.svg"></a>
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