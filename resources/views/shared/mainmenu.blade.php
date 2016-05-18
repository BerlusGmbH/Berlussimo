<div class="mainmenu">
    <div class="row">
        <div class="col s12">
            <div class="card grey">
                <div class="card-content white-text">
                    <div class="row">
                        @if(check_user_links(Auth::user()->id, 'partner'))
                            <div class="col s4 m3 l1">
                                <a href='{{route('legacy::partner::index')}}'>Partner</a>
                            </div>
                        @endif

                        @if(check_user_links(Auth::user()->id, 'objekte_raus'))
                            <div class="col s4 m3 l1">
                                <a href='{{route('legacy::objekte::index', ['objekte_raus' => 'objekte_kurz'])}}'>Objekte</a>
                            </div>
                        @endif

                        @if(check_user_links(Auth::user()->id, 'haus_raus'))
                            <div class="col s4 m3 l1">
                                <a href='{{route('legacy::haeuser::index', ['haus_raus' => 'haus_kurz'])}}'>Häuser</a>
                            </div>
                        @endif

                        @if(check_user_links(Auth::user()->id, 'einheit_raus'))
                            <div class="col s4 m3 l1">
                                <a href='{{route('legacy::einheiten::index')}}'>Einheiten</a>
                            </div>
                        @endif

                        @if(check_user_links(Auth::user()->id, 'mietvertrag_raus'))
                            <div class="col s4 m3 l1">
                                <a href='{{route('legacy::mietvertraege::index')}}'>Mietverträge</a>
                            </div>
                        @endif

                        @if(check_user_links(Auth::user()->id, 'person' ))
                            <div class="col s4 m3 l1">
                                <a href='{{route('legacy::personen::index', ['anzeigen' => 'alle_personen'])}}'>Personen</a>
                            </div>
                        @endif

                        @if(check_user_links(Auth::user()->id, 'personal' ))
                            <div class="col s4 m3 l1">
                                <a href='{{route('legacy::personal::index')}}'>Personal</a>
                            </div>
                        @endif

                        @if(check_user_links(Auth::user()->id, 'details' ))
                            <div class="col s4 m3 l1">
                                <a href='{{route('legacy::details::index', ['option' => 'detail_suche'])}}'>Details</a>
                            </div>
                        @endif

                        @if(check_user_links(Auth::user()->id, 'mietkonten_blatt' ))
                            <div class="col s4 m3 l1">
                                <a href='{{route('legacy::mietkontenblatt::index')}}'>Miete</a>
                            </div>
                        @endif

                        @if(check_user_links(Auth::user()->id, 'rechnungen'))
                            <div class="col s4 m3 l1">
                                <a href='{{route('legacy::rechnungen::index', ['option' => 'erfasste_rechnungen'])}}'><b>Rechnungen</b></a>
                            </div>
                        @endif

                        @if(check_user_links (Auth::user()->id, 'katalog'))
                            <div class="col s4 m3 l1">
                                <a href='{{route('legacy::katalog::index')}}'>Katalog</a>
                            </div>
                        @endif

                        @if(check_user_links( Auth::user()->id, 'kontenrahmen'))
                            <div class="col s4 m3 l1">
                                <a href='{{route('legacy::kontenrahmen::index')}}'>Kontenrahmen</a>
                            </div>
                        @endif

                        @if(check_user_links (Auth::user()->id, 'geldkonten'))
                            <div class="col s4 m3 l1">
                                <a href='{{route('legacy::geldkonten::index')}}'>Geldkonten</a>
                            </div>
                        @endif

                        @if(check_user_links(Auth::user()->id, 'kasse'))
                            <div class="col s4 m3 l1">
                                <a href='{{route('legacy::kassen::index')}}'>Kassen</a>
                            </div>
                        @endif

                        @if(check_user_links(Auth::user()->id, 'lager'))
                            <div class="col s4 m3 l1">
                                <a href='{{route('legacy::lager::index')}}'>Lager</a>
                            </div>
                        @endif

                        @if(check_user_links(Auth::user()->id, 'buchen'))
                            <div class="col s4 m3 l1">
                                <a href='{{route('legacy::buchen::index')}}'><b>Buchen</b></a>
                            </div>
                        @endif

                        @if(check_user_links( Auth::user()->id, 'leerstand'))
                            <div class="col s4 m3 l1">
                                <a href='{{route('legacy::leerstand::index')}}'>Leerstände</a>
                            </div>
                        @endif

                        @if(check_user_links (Auth::user()->id, 'statistik' ))
                            <div class="col s4 m3 l1">
                                <a href='{{route('legacy::statistik::index')}}'>Statistik</a>
                            </div>
                        @endif

                        @if(check_user_links (Auth::user()->id, 'zeiterfassung' ))
                            <div class="col s4 m3 l1">
                                <a href='{{route('legacy::zeiterfassung::index')}}'>Zeiterfassung</a>
                            </div>
                        @endif

                        @if(check_user_links(Auth::user()->id, 'urlaub'))
                            <div class="col s4 m3 l1">
                                <a href='{{route('legacy::urlaub::index')}}'>Urlaub</a>
                            </div>
                        @endif

                        @if(check_user_links(Auth::user()->id, 'kautionen'))
                            <div class="col s4 m3 l1">
                                <a href='{{route('legacy::kautionen::index')}}'>Kautionen</a>
                            </div>
                        @endif

                        @if(check_user_links(Auth::user()->id, 'bk'))
                            <div class="col s4 m3 l1">
                                <a href='{{route('legacy::bk::index')}}'>BK & NK</a>
                            </div>
                        @endif

                        @if(check_user_links( Auth::user()->id, 'sepa'))
                            <div class="col s4 m3 l1">
                                <a href='{{route('legacy::sepa::index')}}'><b>SEPA</b></a>
                            </div>
                        @endif

                        @if (check_user_links(Auth::user()->id, 'benutzer'))
                            <div class="col s4 m3 l1">
                                <a href='{{route('legacy::benutzer::index')}}'><b>Benutzer</b></a>
                            </div>
                        @endif

                        @if (check_user_links(Auth::user()->id, 'weg'))
                            <div class="col s4 m3 l1">
                                <a class='WEG' href='{{route('legacy::weg::index')}}'><b>WEG</b></a>
                            </div>
                        @endif

                        @if(check_user_links(Auth::user()->id, 'todo' ))
                            <div class="col s4 m3 l1">
                                <a href='{{route('legacy::todo::index')}}'>P & A</a>
                            </div>
                        @endif

                        @if(check_user_links(Auth::user()->id, 'Wartung'))
                            <div class="col s4 m3 l1">
                                <a href='/wartungsplaner/' target='new'><b>Wartungsplaner</b></a>
                            </div>
                        @endif

                        @if(check_user_links(Auth::user()->id, 'admin_panel'))
                            <div class="col s4 m3 l1">
                                <a href='{{route('legacy::admin::index', ['admin_panel' => 'menu'])}}'>Administration</a>
                            </div>
                        @endif

                        @if(check_user_links(Auth::user()->id, 'listen'))
                            <div class="col s4 m3 l1">
                                <a class='WEG' href='{{route('legacy::listen::index')}}'>Listen</a>
                            </div>
                        @endif

                        @if(check_user_links(Auth::user()->id, 'mietspiegel'))
                            <div class="col s4 m3 l1">
                                <a class='WEG' href='{{route('legacy::mietspiegel::index')}}'>Mietspiegel</a>
                            </div>
                        @endif

                        <div class="col s4 m3 l1">
                            <a target='_new'
                               href='http://www.hausverwaltung.de/software/schnelleinstieg.html'>Handbuch</a>
                        </div>
                        @if(check_user_links(Auth::user()->id, 'buchen'))
                            <div class="col s4 m3 l1">
                                <a href='{{route('legacy::dbbackup::index')}}'>DB sichern </a>&nbsp;
                            </div>
                        @endif
                    </div>
                    @yield('submenu')
                </div>
            </div>
        </div>
    </div>
</div>