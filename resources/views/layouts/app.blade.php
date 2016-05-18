<!DOCTYPE html>
<html>
<head>
    <link href="http://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href='{{ elixir('css/vendor.css') }}' rel='stylesheet' type='text/css'>
    <link href='{{ elixir('css/berlussimo.css') }}' rel='stylesheet' type='text/css'>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
</head>

<body>

<div class="navbar-fixed">
    <nav>
        <div class="nav-wrapper">
            <a href='http://www.berlus.de'><img style="height:100%; padding: 5px" src="/images/berlus_logo.svg"></a>
            @yield('navbar')
        </div>
    </nav>
</div>

<main>
    @yield('main')
</main>

<footer class="page-footer">
    <div class="footer-copyright center">
        <b>Berlussimo</b> wird von der <a target='_new' href='http://www.berlus.de'>Berlus GmbH</a> -
        Hausverwaltung zur Verfügung gestellt.
    </div>
</footer>

<script type='text/javascript' src={{ elixir('js/vendor.js') }}></script>
<script type='text/javascript' src={{ elixir('js/legacy.js') }}></script>
<script type='text/javascript' src={{ elixir('js/berlussimo.js') }}></script>
@stack('scripts')

</body>
</html>