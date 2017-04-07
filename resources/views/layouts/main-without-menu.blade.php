@extends('layouts.app')

@section('navbar')
    <a href='' data-activates="berlussimo-sidenav" class="button-collapse right" xmlns="http://www.w3.org/1999/html"><i
                class="mdi mdi-menu"></i></a>
    <ul class="right hide-on-med-and-down">
        <li style="height: 64px">
            @include('shared.searchbar')
        </li>
        @can(\App\Libraries\Permission::PERMISSION_MODUL_RECHNUNG)
            <li>
                @if(session()->has('partner_id'))
                    <?php $p = new partners (); $p->get_partner_name(session()->get('partner_id')); ?>
                    <a href='{{route('web::rechnungen::legacy', ['option' => 'partner_wechseln'])}}'>
                        <i class="mdi mdi-account-multiple left"></i>
                        Partner: <b>{{str_limit($p->partner_name, 20)}}</b></a>
                @else
                    <a href='{{route('web::rechnungen::legacy', ['option' => 'partner_wechseln'])}}'>
                        <i class="mdi mdi-account-multiple left"></i>
                        Partner wählen</a>
                @endif
            </li>
        @endcan
        @can(\App\Libraries\Permission::PERMISSION_MODUL_BUCHEN)
            <li>
                @if(session()->has('geldkonto_id'))
                    <?php $g = new geldkonto_info(); $g->geld_konto_details(session()->get('geldkonto_id')); ?>
                    <a href='{{route('web::buchen::legacy', ['option' => 'geldkonto_aendern'])}}'>
                        <i class="mdi mdi-currency-eur left"></i>
                        Geldkonto: <b>{{$g->geldkonto_bezeichnung_kurz}}</b></a>
                @else
                    <a href='{{route('web::buchen::legacy', ['option' => 'geldkonto_aendern'])}}'>
                        <i class="mdi mdi-currency-eur left"></i>
                        Geldkonto wählen</a>
                @endif
            </li>
        @endcan
        <li><a href="/logout"><i class="mdi mdi-exit-to-app left"></i>Abmelden</a></li>
    </ul>
@endsection

@section('app-content')
    <ul class="side-nav" id="berlussimo-sidenav">
        <li>
            <div class="userView grey">
                <a href="#!user"><img class="circle" src="/images/berlus_logo.svg"></a>
                <a href="#!name"><span class="white-text name">{{Auth::user()->name}}</span></a>
                <a href="#!email"><span class="white-text email">{{Auth::user()->email}}</span></a>
            </div>
        </li>
        @can(\App\Libraries\Permission::PERMISSION_MODUL_RECHNUNG)
            <li>
                @if(session()->has('partner_id'))
                    <?php $p = new partners (); $p->get_partner_name(session()->get('partner_id')); ?>
                    <a href='{{route('web::rechnungen::legacy', ['option' => 'partner_wechseln'])}}'>
                        <i class="mdi mdi-account-multiple left"></i>
                        Partner: <b>{{str_limit($p->partner_name, 20)}}</b></a>
                @else
                    <a href='{{route('web::rechnungen::legacy', ['option' => 'partner_wechseln'])}}'>
                        <i class="mdi mdi-account-multiple left"></i>
                        Partner wählen</a>
                @endif
            </li>
        @endcan
        @can(\App\Libraries\Permission::PERMISSION_MODUL_BUCHEN)
            <li>
                @if(session()->has('geldkonto_id'))
                    <?php $g = new geldkonto_info(); $g->geld_konto_details(session()->get('geldkonto_id')); ?>
                    <a href='{{route('web::buchen::legacy', ['option' => 'geldkonto_aendern'])}}'><i
                                class="mdi mdi-currency-eur left"></i>Geldkonto:
                        <b>{{$g->geldkonto_bezeichnung_kurz}}</b></a>
                @else
                    <a href='{{route('web::buchen::legacy', ['option' => 'geldkonto_aendern'])}}'><i
                                class="mdi mdi-currency-eur left small"></i>Geldkonto wählen</a>
                @endif
            </li>
        @endcan
        <li>
            <div class="divider"></div>
        </li>
        <li>
            <a href="/logout"><i class="mdi mdi-exit-to-app left"></i>Abmelden</a>
        </li>
    </ul>
    @yield('mainmenu')

    <div style="margin-top: 10px" class="center-align">
        @include("shared.messages")
    </div>

    @yield("content")
@endsection