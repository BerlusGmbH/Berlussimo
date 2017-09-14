<!DOCTYPE html>
<html>
<head>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href='{{mix('css/vendor.css')}}' rel='stylesheet' type='text/css'>
    @stack('head')
    <link href='{{mix('css/main.css')}}' rel='stylesheet' type='text/css'>
    <link href='{{mix('css/berlussimo.css')}}' rel='stylesheet' type='text/css'>
    <link href='{{mix('css/materialize-css.css')}}' rel='stylesheet' type='text/css'>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body style="background: #303030; display: flex; min-height: 100vh; flex-direction: column;">

<div id="top" style="position: sticky; top: 0; z-index: 1">
    <v-app dark style="min-height: auto">
        @if(Auth::check())
            <app-user-loader :user="{{Auth::user()}}"></app-user-loader>
            <app-global-select-loader
                    :partner="{{session()->has('partner_id') ? \App\Models\Partner::find(session()->get('partner_id')) : 'null'}}"
                    :objekt="{{ session()->has('objekt_id') ? \App\Models\Objekte::find(session()->get('objekt_id')) : 'null'}}"
                    :bankkonto="{{ session()->has('geldkonto_id') ? \App\Models\Bankkonten::find(session()->get('geldkonto_id')) : 'null'}}">
            </app-global-select-loader>
            <app-legacy-loader :is-legacy="true"></app-legacy-loader>
        @endif
        <app-toolbar></app-toolbar>
        <app-menu>
            <div slot="mainmenu">@include('shared.menus.main')</div>
            <div slot="submenu">
                <?php include(base_path($submenu)); ?>
            </div>
        </app-menu>
        <div>
            @include("shared.messages")
        </div>
    </v-app>
</div>

<main class="application--dark" style="flex: 1 0 auto;">
    @if($content != "")
        <div class="berlussimo-materialize container-fluid">
            {!!$content!!}
        </div>
    @endif
    @if(Auth::check())
        <app-notifications id="notifications" style="z-index: 1"></app-notifications>
        <app-snackbar id="snackbar" style="z-index: 1"></app-snackbar>
    @endif
</main>

<div id="bottom" class="application--dark">
    <app-footer dark></app-footer>
</div>

<script type='text/javascript' src='{{mix('js/manifest.js')}}'></script>
<script type='text/javascript' src='{{mix('js/vendor.js')}}'></script>
<script type='text/javascript' src='{{mix('js/app-materialize.js')}}'></script>
<script type='text/javascript' src='{{mix('js/materialize.js')}}'></script>
<script type='text/javascript' src='{{mix('js/legacy.js')}}'></script>
@stack('scripts')

</body>
</html>