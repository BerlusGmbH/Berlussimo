@extends('layouts.page')
@section('page-content')
    <v-app dark style="display: flex; min-height: 100vh; flex-direction: column;">
        @if(Auth::check())
            <app-user-loader :user="{{Auth::user()}}"></app-user-loader>
        @endif
        <app-toolbar></app-toolbar>
        <app-menu>
            <div slot="breadcrumbs">@yield('breadcrumbs')</div>
            <div slot="mainmenu">@include('shared.menus.main')</div>
            <div slot="submenu">@yield('submenu')</div>
        </app-menu>

        <main style="flex: 1 0 auto;">
            <div style="margin-top: 10px" class="center-align">
                @include("shared.messages")
            </div>
        @yield("content")
        <!--
            @if(Auth::check())
            <notifications id="notifications" :user="{{Auth::id()}}"
                               :init-notifications="{{Auth::user()->notifications->toJson()}}"></notifications>
            @endif
                -->
        </main>

        <app-footer></app-footer>
    </v-app>
@endsection

@if(Auth::check())
    @push('scripts')
    <script>
        Echo.private('test').listen('TestEvent', function (e) {
            Materialize.toast(JSON.stringify(e), 5000);
        });
    </script>
    @endpush
@endif