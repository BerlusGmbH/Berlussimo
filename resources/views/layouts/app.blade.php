@inject('locator', App\Services\PhoneLocator')
        <!DOCTYPE html>
<html>
<head>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href='{{mix('css/vendor.css')}}' rel='stylesheet' type='text/css'>
    @stack('head')
    <link href='{{mix('css/main.css')}}' rel='stylesheet' type='text/css'>
    <link href='{{mix('css/berlussimo.css')}}' rel='stylesheet' type='text/css'>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body>

<div id="app">
    <v-app dark>
        @if(Auth::check())
            <app-user-loader :user="{{Auth::user()}}"></app-user-loader>
            <app-global-select-loader
                    :partner="{{json_encode(\App\Models\Partner::find(session()->get('partner_id')))}}"
                    :objekt="{{json_encode(\App\Models\Objekte::find(session()->get('objekt_id')))}}"
                    :bankkonto="{{json_encode(\App\Models\Bankkonten::find(session()->get('geldkonto_id')))}}">
            </app-global-select-loader>
            <app-workplace-loader :has-phone="{{json_encode($locator->workplaceHasPhone())}}"></app-workplace-loader>
        @endif
        <div style="position: sticky; top: 0; z-index: 1">
            <app-toolbar></app-toolbar>
            <app-menu>
                <template slot="breadcrumbs">@yield('breadcrumbs')</template>
                <template slot="mainmenu">@include('shared.menus.main')</template>
                <template slot="submenu">@yield('submenu')</template>
            </app-menu>
            <div>
                @include("shared.messages")
            </div>
        </div>
        <main style="z-index: 0">
            <v-content>
            @yield("content")
            @if(Auth::check())
                <app-notifications id="notifications"></app-notifications>
                <app-snackbar id="snackbar"></app-snackbar>
            @endif
            </v-content>
        </main>

        <app-footer></app-footer>
    </v-app>
</div>

<script type='text/javascript' src='{{mix('js/manifest.js')}}'></script>
<script type='text/javascript' src='{{mix('js/vendor.js')}}'></script>
<script type='text/javascript' src='{{mix('js/app.js')}}'></script>
@stack('scripts')

</body>
</html>