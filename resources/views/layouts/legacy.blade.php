<!DOCTYPE html>
<html>
<head>
    <link href='https://fonts.googleapis.com/css?family=Roboto:300,400,500,700|Material+Icons' rel="stylesheet">
    <link href='{{mix('css/vendor.css')}}' rel='stylesheet' type='text/css'>
    @stack('head')
    <link href='{{mix('css/main.css')}}' rel='stylesheet' type='text/css'>
    <link href='{{mix('css/berlussimo.css')}}' rel='stylesheet' type='text/css'>
    <link href='{{mix('css/materialize-css.css')}}' rel='stylesheet' type='text/css'>
    <style type="text/css">
        .application--wrap {
            min-height: auto !important;
        }
    </style>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body style="display: flex; min-height: 100vh; flex-direction: column;">

<div id="top" style="position: sticky; top: 0; z-index: 2">
    <v-app dark>
        <b-state-loader></b-state-loader>
        <app-toolbar></app-toolbar>
        <app-menu v-cloak>
            <div slot="mainmenu">@include('shared.menus.main')</div>
            <div v-cloak slot="submenu">
                <?php include(base_path($submenu)); ?>
            </div>
        </app-menu>
        <div>
            @include("shared.messages")
        </div>
    </v-app>
</div>

<div id="berlussimo-content" class="application theme--dark content"
     style="flex: 1 0 auto; flex-direction: column; display: none">
    @if($content != "")
        <div class="berlussimo-materialize container fluid" style="flex: 1 0 auto">
            {!!$content!!}
        </div>
    @else
        <div class="berlussimo-materialize container fluid" style="flex: 1 0 auto">
            @yield('content')
        </div>
    @endif
    @if(Auth::check())
        <div id="notification">
            <v-app>
                <app-notifications style="z-index: 1000"></app-notifications>
                <app-snackbar style="z-index: 1010"></app-snackbar>
            </v-app>
        </div>
    @endif
</div>

<div id="bottom">
    <v-app dark>
        <app-footer></app-footer>
    </v-app>
</div>

<script type='text/javascript' src='{{mix('js/manifest.js')}}'></script>
<script type='text/javascript' src='{{mix('js/vendor.js')}}'></script>
<script type='text/javascript' src='{{mix('js/app-materialize.js')}}'></script>
<script type='text/javascript' src='{{mix('js/materialize.js')}}'></script>
<script type='text/javascript' src='{{mix('js/legacy.js')}}'></script>
@stack('scripts')

</body>
</html>
