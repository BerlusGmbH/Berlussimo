<div class="mainmenu">
    <div class="row">
        <div class="col s12">
            <div class="card grey">
                <div class="card-content">
                    <div class="row">
                        @if(check_user_links(Auth::user()->id, 'partner'))
                            <div class="col s4 m3 l1">
                                <a class="white-text" href='{{route('web::partner::legacy')}}'>Partner</a>
                            </div>
                        @endif

                        @if(check_user_links(Auth::user()->id, 'objekte_raus'))
                            <div class="col s4 m3 l1">
                                <a href='{{route('web::objekte::legacy', ['objekte_raus' => 'objekte_kurz'])}}'>Objekte</a>
                            </div>
                        @endif

                        @if(check_user_links(Auth::user()->id, 'haus_raus'))
                            <div class="col s4 m3 l1">
                                <a href='{{route('web::haeuser::legacy', ['haus_raus' => 'haus_kurz'])}}'>Häuser</a>
                            </div>
                        @endif

                        @if(check_user_links(Auth::user()->id, 'einheit_raus'))
                            <div class="col s4 m3 l1">
                                <a href='{{route('web::einheiten::legacy')}}'>Einheiten</a>
                            </div>
                        @endif

                        @if(check_user_links(Auth::user()->id, 'mietvertrag_raus'))
                            <div class="col s4 m3 l1">
                                <a href='{{route('web::mietvertraege::legacy')}}'>Mietverträge</a>
                            </div>
                        @endif

                        @if(check_user_links(Auth::user()->id, 'person' ))
                            <div class="col s4 m3 l1">
                                <a href='{{route('web::personen::legacy', ['anzeigen' => 'alle_personen'])}}'>Personen</a>
                            </div>
                        @endif

                        @if(check_user_links(Auth::user()->id, 'personal' ))
                            <div class="col s4 m3 l1">
                                <a href='{{route('web::personal::legacy')}}'>Personal</a>
                            </div>
                        @endif

                        @if(check_user_links(Auth::user()->id, 'details' ))
                            <div class="col s4 m3 l1">
                                <a href='{{route('web::details::legacy', ['option' => 'detail_suche'])}}'>Details</a>
                            </div>
                        @endif

                        @if(check_user_links(Auth::user()->id, 'mietkonten_blatt' ))
                            <div class="col s4 m3 l1">
                                <a href='{{route('web::mietkontenblatt::legacy')}}'>Miete</a>
                            </div>
                        @endif

                        @if(check_user_links(Auth::user()->id, 'rechnungen'))
                            <div class="col s4 m3 l1">
                                <a href='{{route('web::rechnungen::legacy', ['option' => 'erfasste_rechnungen'])}}'>Rechnungen</a>
                            </div>
                        @endif

                        @if(check_user_links (Auth::user()->id, 'katalog'))
                            <div class="col s4 m3 l1">
                                <a href='{{route('web::katalog::legacy')}}'>Katalog</a>
                            </div>
                        @endif

                        @if(check_user_links( Auth::user()->id, 'kontenrahmen'))
                            <div class="col s4 m3 l1">
                                <a href='{{route('web::kontenrahmen::legacy')}}'>Kontenrahmen</a>
                            </div>
                        @endif

                        @if(check_user_links (Auth::user()->id, 'geldkonten'))
                            <div class="col s4 m3 l1">
                                <a href='{{route('web::geldkonten::legacy')}}'>Geldkonten</a>
                            </div>
                        @endif

                        @if(check_user_links(Auth::user()->id, 'kasse'))
                            <div class="col s4 m3 l1">
                                <a href='{{route('web::kassen::legacy')}}'>Kassen</a>
                            </div>
                        @endif

                        @if(check_user_links(Auth::user()->id, 'lager'))
                            <div class="col s4 m3 l1">
                                <a href='{{route('web::lager::legacy')}}'>Lager</a>
                            </div>
                        @endif

                        @if(check_user_links(Auth::user()->id, 'buchen'))
                            <div class="col s4 m3 l1">
                                <a href='{{route('web::buchen::legacy')}}'>Buchen</a>
                            </div>
                        @endif

                        @if(check_user_links( Auth::user()->id, 'leerstand'))
                            <div class="col s4 m3 l1">
                                <a href='{{route('web::leerstand::legacy')}}'>Leerstände</a>
                            </div>
                        @endif

                        @if(check_user_links (Auth::user()->id, 'statistik' ))
                            <div class="col s4 m3 l1">
                                <a href='{{route('web::statistik::legacy')}}'>Statistik</a>
                            </div>
                        @endif

                        @if(check_user_links (Auth::user()->id, 'zeiterfassung' ))
                            <div class="col s4 m3 l1">
                                <a href='{{route('web::zeiterfassung::legacy')}}'>Zeiterfassung</a>
                            </div>
                        @endif

                        @if(check_user_links(Auth::user()->id, 'urlaub'))
                            <div class="col s4 m3 l1">
                                <a href='{{route('web::urlaub::legacy')}}'>Urlaub</a>
                            </div>
                        @endif

                        @if(check_user_links(Auth::user()->id, 'kautionen'))
                            <div class="col s4 m3 l1">
                                <a href='{{route('web::kautionen::legacy')}}'>Kautionen</a>
                            </div>
                        @endif

                        @if(check_user_links(Auth::user()->id, 'bk'))
                            <div class="col s4 m3 l1">
                                <a href='{{route('web::bk::legacy')}}'>BK & NK</a>
                            </div>
                        @endif

                        @if(check_user_links( Auth::user()->id, 'sepa'))
                            <div class="col s4 m3 l1">
                                <a href='{{route('web::sepa::legacy')}}'>SEPA</a>
                            </div>
                        @endif

                        @if (check_user_links(Auth::user()->id, 'benutzer'))
                            <div class="col s4 m3 l1">
                                <a href='{{route('web::benutzer::legacy')}}'>Benutzer</a>
                            </div>
                        @endif

                        @if (check_user_links(Auth::user()->id, 'weg'))
                            <div class="col s4 m3 l1">
                                <a class='WEG' href='{{route('web::weg::legacy')}}'>WEG</a>
                            </div>
                        @endif

                        @if(check_user_links(Auth::user()->id, 'todo' ))
                            <div class="col s4 m3 l1">
                                <a href='{{route('web::todo::legacy')}}'>Aufträge</a>
                            </div>
                        @endif

                        @if(check_user_links(Auth::user()->id, 'Wartung'))
                            <div class="col s4 m3 l1">
                                <a href='/wartungsplaner/' target='new'>Wartungsplaner</a>
                            </div>
                        @endif

                        @if(check_user_links(Auth::user()->id, 'admin_panel'))
                            <div class="col s4 m3 l1">
                                <a href='{{route('web::admin::legacy', ['admin_panel' => 'menu'])}}'>Administration</a>
                            </div>
                        @endif

                        @if(check_user_links(Auth::user()->id, 'listen'))
                            <div class="col s4 m3 l1">
                                <a class='WEG' href='{{route('web::listen::legacy')}}'>Listen</a>
                            </div>
                        @endif

                        @if(check_user_links(Auth::user()->id, 'mietspiegel'))
                            <div class="col s4 m3 l1">
                                <a class='WEG' href='{{route('web::mietspiegel::legacy')}}'>Mietspiegel</a>
                            </div>
                        @endif

                        <div class="col s4 m3 l1">
                            <a target='_new'
                               href='http://www.hausverwaltung.de/software/schnelleinstieg.html'>Handbuch</a>
                        </div>
                    </div>
                    @yield('submenu')
                </div>
            </div>
        </div>
    </div>
</div>