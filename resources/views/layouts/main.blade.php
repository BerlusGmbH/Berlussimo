@extends('layouts.app')

@section('navbar')
    <a href='' data-activates="berlussimo-sidenav" class="button-collapse right" xmlns="http://www.w3.org/1999/html"><i
                class="material-icons">menu</i></a>
    <ul class="right hide-on-med-and-down">
        @if(check_user_links(Auth::user()->id, 'rechnungen'))
            <li>
                @if(session()->has('partner_id'))
                    <?php $p = new partners (); $p->get_partner_name(session()->get('partner_id')); ?>
                    <a href='{{route('legacy::rechnungen::index', ['option' => 'partner_wechseln'])}}'>
                        <i class="material-icons left">perm_identity</i>
                        Partner: <b>{{str_limit($p->partner_name, 20)}}</b></a>
                @else
                    <a href='{{route('legacy::rechnungen::index', ['option' => 'partner_wechseln'])}}'>
                        <i class="material-icons left">perm_identity</i>
                        Partner wählen</a>
                @endif
            </li>
        @endif
        @if(check_user_links(Auth::user()->id, 'buchen' ))
            <li>
                @if(session()->has('geldkonto_id'))
                    <?php $g = new geldkonto_info(); $g->geld_konto_details(session()->get('geldkonto_id')); ?>
                    <a href='{{route('legacy::buchen::index', ['option' => 'geldkonto_aendern'])}}'>
                        <i class="material-icons left">euro_symbol</i>
                        Geldkonto: <b>{{$g->geldkonto_bezeichnung_kurz}}</b></a>
                @else
                    <a href='{{route('legacy::buchen::index', ['option' => 'geldkonto_aendern'])}}'>
                        <i class="material-icons left">euro_symbol</i>
                        Geldkonto wählen</a>
                @endif
            </li>
        @endif
        <li><a href="/logout"><i class="material-icons left">exit_to_app</i>Abmelden</a></li>
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
        @if(check_user_links(Auth::user()->id, 'rechnungen'))
            <li>
                @if(session()->has('partner_id'))
                    <?php $p = new partners (); $p->get_partner_name(session()->get('partner_id')); ?>
                    <a href='{{route('legacy::rechnungen::index', ['option' => 'partner_wechseln'])}}'>
                        <i class="material-icons left">perm_identity</i>
                        Partner: <b>{{str_limit($p->partner_name, 20)}}</b></a>
                @else
                    <a href='{{route('legacy::rechnungen::index', ['option' => 'partner_wechseln'])}}'>
                        <i class="material-icons left">perm_identity</i>
                        Partner wählen</a>
                @endif
            </li>
        @endif
        @if(check_user_links(Auth::user()->id, 'buchen' ))
            <li>
                @if(session()->has('geldkonto_id'))
                    <?php $g = new geldkonto_info(); $g->geld_konto_details(session()->get('geldkonto_id')); ?>
                    <a href='{{route('legacy::buchen::index', ['option' => 'geldkonto_aendern'])}}'><i
                                class="material-icons left">euro_symbol</i>Geldkonto:
                        <b>{{$g->geldkonto_bezeichnung_kurz}}</b></a>
                @else
                    <a href='{{route('legacy::buchen::index', ['option' => 'geldkonto_aendern'])}}'><i
                                class="material-icons left small">euro_symbol</i>Geldkonto wählen</a>
                @endif
            </li>
        @endif
        <li>
            <div class="divider"></div>
        </li>
        <li>
            <a href="/logout"><i class="material-icons left">exit_to_app</i>Abmelden</a>
        </li>
    </ul>
    @include("shared.mainmenu")

    <div class="center-align">
        @include("shared.messages")
    </div>

    @yield("content")
@endsection