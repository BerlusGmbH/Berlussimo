<div class="mainmenu">
    <ul class="collapsible" style="margin: 0" data-collapsible="accordion">
        <li>
            <div class="collapsible-header primary-color text-variation-3">@yield('breadcrumbs')</div>
            <div class="collapsible-body primary-color text-variation-2">
                <div class="row">
                    @can(\App\Libraries\Permission::PERMISSION_MODUL_PARTNER)
                        <div class="col-xs-4 col-sm-3 col-md-2 col-lg-1">
                            <a href='{{route('web::partner::legacy')}}'>Partner</a>
                        </div>
                    @endcan

                    @can(\App\Libraries\Permission::PERMISSION_MODUL_OBJEKT)
                        <div class="col-xs-4 col-sm-3 col-md-2 col-lg-1">
                            <a href='{{route('web::objekte::index')}}'>Objekte</a>
                        </div>
                    @endcan

                    @can(\App\Libraries\Permission::PERMISSION_MODUL_HAUS)
                        <div class="col-xs-4 col-sm-3 col-md-2 col-lg-1">
                            <a href='{{route('web::haeuser::index')}}'>Häuser</a>
                        </div>
                    @endcan

                    @can(\App\Libraries\Permission::PERMISSION_MODUL_EINHEIT)
                        <div class="col-xs-4 col-sm-3 col-md-2 col-lg-1">
                            <a href='{{route('web::einheiten::index')}}'>Einheiten</a>
                        </div>
                    @endcan

                    @can(\App\Libraries\Permission::PERMISSION_MODUL_MIETVERTRAG)
                        <div class="col-xs-4 col-sm-3 col-md-2 col-lg-1">
                            <a href='{{route('web::mietvertraege::legacy')}}'>Mietverträge</a>
                        </div>
                    @endcan

                    @can(\App\Libraries\Permission::PERMISSION_MODUL_PERSON)
                        <div class="col-xs-4 col-sm-3 col-md-2 col-lg-1">
                            <a href='{{route('web::personen::index')}}'>Personen</a>
                        </div>
                    @endcan

                    @can(\App\Libraries\Permission::PERMISSION_MODUL_PERSONAL)
                        <div class="col-xs-4 col-sm-3 col-md-2 col-lg-1">
                            <a href='{{route('web::personal::legacy')}}'>Personal</a>
                        </div>
                    @endcan

                    @can(\App\Libraries\Permission::PERMISSION_MODUL_DETAIL)
                        <div class="col-xs-4 col-sm-3 col-md-2 col-lg-1">
                            <a href='{{route('web::details::legacy', ['option' => 'detail_suche'])}}'>Details</a>
                        </div>
                    @endcan

                    @can(\App\Libraries\Permission::PERMISSION_MODUL_MIETVERTRAG)
                        <div class="col-xs-4 col-sm-3 col-md-2 col-lg-1">
                            <a href='{{route('web::mietkontenblatt::legacy')}}'>Miete</a>
                        </div>
                    @endcan

                    @can(\App\Libraries\Permission::PERMISSION_MODUL_RECHNUNG)
                        <div class="col-xs-4 col-sm-3 col-md-2 col-lg-1">
                            <a href='{{route('web::rechnungen::legacy', ['option' => 'erfasste_rechnungen'])}}'>Rechnungen</a>
                        </div>
                    @endcan

                    @can(\App\Libraries\Permission::PERMISSION_MODUL_KATALOG)
                        <div class="col-xs-4 col-sm-3 col-md-2 col-lg-1">
                            <a href='{{route('web::katalog::legacy')}}'>Katalog</a>
                        </div>
                    @endcan

                    @can(\App\Libraries\Permission::PERMISSION_MODUL_KONTENRAHMEN)
                        <div class="col-xs-4 col-sm-3 col-md-2 col-lg-1">
                            <a href='{{route('web::kontenrahmen::legacy')}}'>Kontenrahmen</a>
                        </div>
                    @endcan

                    @can(\App\Libraries\Permission::PERMISSION_MODUL_BANKKONTO)
                        <div class="col-xs-4 col-sm-3 col-md-2 col-lg-1">
                            <a href='{{route('web::geldkonten::legacy')}}'>Geldkonten</a>
                        </div>
                    @endcan

                    @can(\App\Libraries\Permission::PERMISSION_MODUL_KASSE)
                        <div class="col-xs-4 col-sm-3 col-md-2 col-lg-1">
                            <a href='{{route('web::kassen::legacy')}}'>Kassen</a>
                        </div>
                    @endcan

                    @can(\App\Libraries\Permission::PERMISSION_MODUL_LAGER)
                        <div class="col-xs-4 col-sm-3 col-md-2 col-lg-1">
                            <a href='{{route('web::lager::legacy')}}'>Lager</a>
                        </div>
                    @endcan

                    @can(\App\Libraries\Permission::PERMISSION_MODUL_BUCHEN)
                        <div class="col-xs-4 col-sm-3 col-md-2 col-lg-1">
                            <a href='{{route('web::buchen::legacy')}}'>Buchen</a>
                        </div>
                    @endcan

                    @can(\App\Libraries\Permission::PERMISSION_MODUL_LEERSTAND)
                        <div class="col-xs-4 col-sm-3 col-md-2 col-lg-1">
                            <a href='{{route('web::leerstand::legacy')}}'>Leerstände</a>
                        </div>
                    @endcan

                    @can(\App\Libraries\Permission::PERMISSION_MODUL_STATISTIK)
                        <div class="col-xs-4 col-sm-3 col-md-2 col-lg-1">
                            <a href='{{route('web::statistik::legacy')}}'>Statistik</a>
                        </div>
                    @endcan

                    @can(\App\Libraries\Permission::PERMISSION_MODUL_ZEITERFASSUNG)
                        <div class="col-xs-4 col-sm-3 col-md-2 col-lg-1">
                            <a href='{{route('web::zeiterfassung::legacy')}}'>Zeiterfassung</a>
                        </div>
                    @endcan

                    @can(\App\Libraries\Permission::PERMISSION_MODUL_URLAUB)
                        <div class="col-xs-4 col-sm-3 col-md-2 col-lg-1">
                            <a href='{{route('web::urlaub::legacy')}}'>Urlaub</a>
                        </div>
                    @endcan

                    @can(\App\Libraries\Permission::PERMISSION_MODUL_KAUTION)
                        <div class="col-xs-4 col-sm-3 col-md-2 col-lg-1">
                            <a href='{{route('web::kautionen::legacy')}}'>Kautionen</a>
                        </div>
                    @endcan

                    @can(\App\Libraries\Permission::PERMISSION_MODUL_BETRIEBSKOSTEN)
                        <div class="col-xs-4 col-sm-3 col-md-2 col-lg-1">
                            <a href='{{route('web::bk::legacy')}}'>BK & NK</a>
                        </div>
                    @endcan

                    @can(\App\Libraries\Permission::PERMISSION_MODUL_SEPA)
                        <div class="col-xs-4 col-sm-3 col-md-2 col-lg-1">
                            <a href='{{route('web::sepa::legacy')}}'>SEPA</a>
                        </div>
                    @endcan

                    @can(\App\Libraries\Permission::PERMISSION_MODUL_BENUTZER)
                        <div class="col-xs-4 col-sm-3 col-md-2 col-lg-1">
                            <a href='{{route('web::benutzer::index')}}'>Mitarbeiter</a>
                        </div>
                    @endcan

                    @can(\App\Libraries\Permission::PERMISSION_MODUL_BENUTZER)
                        <div class="col-xs-4 col-sm-3 col-md-2 col-lg-1">
                            <a href='{{route('web::benutzer::legacy', ['option' => 'werkzeuge'])}}'>Werkzeuge</a>
                        </div>
                    @endcan

                    @can(\App\Libraries\Permission::PERMISSION_MODUL_WEG)
                        <div class="col-xs-4 col-sm-3 col-md-2 col-lg-1">
                            <a class='WEG' href='{{route('web::weg::legacy')}}'>WEG</a>
                        </div>
                    @endcan

                    @can(\App\Libraries\Permission::PERMISSION_MODUL_AUFTRAEGE)
                        <div class="col-xs-4 col-sm-3 col-md-2 col-lg-1">
                            <a href='{{route('web::todo::legacy')}}'>Aufträge</a>
                        </div>
                    @endcan

                @can(\App\Libraries\Permission::PERMISSION_MODUL_WARTUNG)
                    <div class="col-xs-4 col-sm-3 col-md-2 col-lg-1">
                        <a href='/wartungsplaner/' target='new'>Wartungsplaner</a>
                    </div>
                @endcan
                </div>
            </div>
        </li>
        <li>
            <div class="collapsible-header secondary-color text-variation-1">
                <div class="row">
                    <div style="padding: 0" class="col col-xs-4">
                        <i class="mdi mdi-settings"></i>Tools
                    </div>
                    <div style="padding: 0" class="col col-xs-8 end-xs hide-on-med-and-down">
                        @include('shared.menus.partner_select')
                        @include('shared.menus.account_select')
                    </div>
                </div>
            </div>
            <div class="collapsible-body secondary-color">
                <div class="row">
                    <div style="padding: 3px" class="col col-xs-12 col-sm-7 end-xs hide-on-large-only">
                        @include('shared.menus.partner_select')
                    </div>
                    <div style="padding: 3px" class="col col-xs-12 col-sm-5 end-xs hide-on-large-only">
                        @include('shared.menus.account_select')
                    </div>
                </div>
                <div class="secondary-color text-variation-2">
                    @yield('submenu')
                </div>
            </div>
        </li>
    </ul>
</div>