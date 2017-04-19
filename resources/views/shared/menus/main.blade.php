<div class="mainmenu">
    <div class="card grey darken-3">
        <div class="card-content">
            <div class="row">
                @if(check_user_links(Auth::user()->id, 'partner'))
                    <div class="col-xs-4 col-sm-3 col-md-2 col-lg-1">
                        <a class="white-text" href='{{route('web::partner::legacy')}}'>Partner</a>
                    </div>
                @endif

                @if(check_user_links(Auth::user()->id, 'objekte_raus'))
                    <div class="col-xs-4 col-sm-3 col-md-2 col-lg-1">
                        <a href='{{route('web::objekte::index')}}'>Objekte</a>
                    </div>
                @endif

                @if(check_user_links(Auth::user()->id, 'haus_raus'))
                    <div class="col-xs-4 col-sm-3 col-md-2 col-lg-1">
                        <a href='{{route('web::haeuser::index')}}'>Häuser</a>
                    </div>
                @endif

                @if(check_user_links(Auth::user()->id, 'einheit_raus'))
                    <div class="col-xs-4 col-sm-3 col-md-2 col-lg-1">
                        <a href='{{route('web::einheiten::index')}}'>Einheiten</a>
                    </div>
                @endif

                @if(check_user_links(Auth::user()->id, 'mietvertrag_raus'))
                    <div class="col-xs-4 col-sm-3 col-md-2 col-lg-1">
                        <a href='{{route('web::mietvertraege::legacy')}}'>Mietverträge</a>
                    </div>
                @endif

                @if(check_user_links(Auth::user()->id, 'person' ))
                    <div class="col-xs-4 col-sm-3 col-md-2 col-lg-1">
                        <a href='{{route('web::personen::index')}}'>Personen</a>
                    </div>
                @endif

                @if(check_user_links(Auth::user()->id, 'personal' ))
                    <div class="col-xs-4 col-sm-3 col-md-2 col-lg-1">
                        <a href='{{route('web::personal::legacy')}}'>Personal</a>
                    </div>
                @endif

                @if(check_user_links(Auth::user()->id, 'details' ))
                    <div class="col-xs-4 col-sm-3 col-md-2 col-lg-1">
                        <a href='{{route('web::details::legacy', ['option' => 'detail_suche'])}}'>Details</a>
                    </div>
                @endif

                @if(check_user_links(Auth::user()->id, 'mietkonten_blatt' ))
                    <div class="col-xs-4 col-sm-3 col-md-2 col-lg-1">
                        <a href='{{route('web::mietkontenblatt::legacy')}}'>Miete</a>
                    </div>
                @endif

                @if(check_user_links(Auth::user()->id, 'rechnungen'))
                    <div class="col-xs-4 col-sm-3 col-md-2 col-lg-1">
                        <a href='{{route('web::rechnungen::legacy', ['option' => 'erfasste_rechnungen'])}}'>Rechnungen</a>
                    </div>
                @endif

                @if(check_user_links (Auth::user()->id, 'katalog'))
                    <div class="col-xs-4 col-sm-3 col-md-2 col-lg-1">
                        <a href='{{route('web::katalog::legacy')}}'>Katalog</a>
                    </div>
                @endif

                @if(check_user_links( Auth::user()->id, 'kontenrahmen'))
                    <div class="col-xs-4 col-sm-3 col-md-2 col-lg-1">
                        <a href='{{route('web::kontenrahmen::legacy')}}'>Kontenrahmen</a>
                    </div>
                @endif

                @if(check_user_links (Auth::user()->id, 'geldkonten'))
                    <div class="col-xs-4 col-sm-3 col-md-2 col-lg-1">
                        <a href='{{route('web::geldkonten::legacy')}}'>Geldkonten</a>
                    </div>
                @endif

                @if(check_user_links(Auth::user()->id, 'kasse'))
                    <div class="col-xs-4 col-sm-3 col-md-2 col-lg-1">
                        <a href='{{route('web::kassen::legacy')}}'>Kassen</a>
                    </div>
                @endif

                @if(check_user_links(Auth::user()->id, 'lager'))
                    <div class="col-xs-4 col-sm-3 col-md-2 col-lg-1">
                        <a href='{{route('web::lager::legacy')}}'>Lager</a>
                    </div>
                @endif

                @if(check_user_links(Auth::user()->id, 'buchen'))
                    <div class="col-xs-4 col-sm-3 col-md-2 col-lg-1">
                        <a href='{{route('web::buchen::legacy')}}'>Buchen</a>
                    </div>
                @endif

                @if(check_user_links( Auth::user()->id, 'leerstand'))
                    <div class="col-xs-4 col-sm-3 col-md-2 col-lg-1">
                        <a href='{{route('web::leerstand::legacy')}}'>Leerstände</a>
                    </div>
                @endif

                @if(check_user_links (Auth::user()->id, 'statistik' ))
                    <div class="col-xs-4 col-sm-3 col-md-2 col-lg-1">
                        <a href='{{route('web::statistik::legacy')}}'>Statistik</a>
                    </div>
                @endif

                @if(check_user_links (Auth::user()->id, 'zeiterfassung' ))
                    <div class="col-xs-4 col-sm-3 col-md-2 col-lg-1">
                        <a href='{{route('web::zeiterfassung::legacy')}}'>Zeiterfassung</a>
                    </div>
                @endif

                @if(check_user_links(Auth::user()->id, 'urlaub'))
                    <div class="col-xs-4 col-sm-3 col-md-2 col-lg-1">
                        <a href='{{route('web::urlaub::legacy')}}'>Urlaub</a>
                    </div>
                @endif

                @if(check_user_links(Auth::user()->id, 'kautionen'))
                    <div class="col-xs-4 col-sm-3 col-md-2 col-lg-1">
                        <a href='{{route('web::kautionen::legacy')}}'>Kautionen</a>
                    </div>
                @endif

                @if(check_user_links(Auth::user()->id, 'bk'))
                    <div class="col-xs-4 col-sm-3 col-md-2 col-lg-1">
                        <a href='{{route('web::bk::legacy')}}'>BK & NK</a>
                    </div>
                @endif

                @if(check_user_links( Auth::user()->id, 'sepa'))
                    <div class="col-xs-4 col-sm-3 col-md-2 col-lg-1">
                        <a href='{{route('web::sepa::legacy')}}'>SEPA</a>
                    </div>
                @endif

                @if (check_user_links(Auth::user()->id, 'benutzer'))
                    <div class="col-xs-4 col-sm-3 col-md-2 col-lg-1">
                        <a href='{{route('web::benutzer::index')}}'>Mitarbeiter</a>
                    </div>
                @endif

                @if (check_user_links(Auth::user()->id, 'benutzer'))
                    <div class="col-xs-4 col-sm-3 col-md-2 col-lg-1">
                        <a href='{{route('web::benutzer::legacy', ['option' => 'werkzeuge'])}}'>Werkzeuge</a>
                    </div>
                @endif

                @if (check_user_links(Auth::user()->id, 'weg'))
                    <div class="col-xs-4 col-sm-3 col-md-2 col-lg-1">
                        <a class='WEG' href='{{route('web::weg::legacy')}}'>WEG</a>
                    </div>
                @endif

                @if(check_user_links(Auth::user()->id, 'todo' ))
                    <div class="col-xs-4 col-sm-3 col-md-2 col-lg-1">
                        <a href='{{route('web::todo::legacy')}}'>Aufträge</a>
                    </div>
                @endif

                @if(check_user_links(Auth::user()->id, 'Wartung'))
                    <div class="col-xs-4 col-sm-3 col-md-2 col-lg-1">
                        <a href='/wartungsplaner/' target='new'>Wartungsplaner</a>
                    </div>
                @endif

                @if(check_user_links(Auth::user()->id, 'listen'))
                    <div class="col-xs-4 col-sm-3 col-md-2 col-lg-1">
                        <a class='WEG' href='{{route('web::listen::legacy')}}'>Listen</a>
                    </div>
                @endif

                @if(check_user_links(Auth::user()->id, 'mietspiegel'))
                    <div class="col-xs-4 col-sm-3 col-md-2 col-lg-1">
                        <a class='WEG' href='{{route('web::mietspiegel::legacy')}}'>Mietspiegel</a>
                    </div>
                @endif
            </div>
            @yield('submenu')
        </div>
    </div>
</div>