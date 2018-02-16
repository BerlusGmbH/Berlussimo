<?php
echo "<div class=\"navi_leiste1\">";
erstelle_abschnitt( "Hauptmenü");


if (check_user_links ( Auth::user()->id, 'partner' )) {
	echo "<b>| </b>&nbsp;<a href='" . route('web::partner::legacy') . "'>Partner/Lieferant</a>&nbsp;<b>| </b>&nbsp;";
}

if (check_user_links ( Auth::user()->id, 'objekte_raus' )) {
	echo "<a href='" . route('web::objekte::legacy', ['objekte_raus' => 'objekt_kurz']) . "'>Objekte</a>&nbsp;<b>| </b>&nbsp;";
}

if (check_user_links ( Auth::user()->id, 'haus_raus' )) {
	echo "<a href='" . route('web::haeuser::legacy', ['haus_raus' => 'haus_kurz']) . "'>Häuser</a>&nbsp;<b>| </b>&nbsp;";
}

if (check_user_links ( Auth::user()->id, 'einheit_raus' )) {
	echo "<a href='" . route('web::einheiten::legacy') . "'>Einheiten</a>&nbsp;<b>| </b>&nbsp;";
}

if (check_user_links ( Auth::user()->id, 'mietvertrag_raus' )) {
	echo "<a href='" . route('web::mietvertraege::legacy') . "'>Mietverträge</a>&nbsp;<b>| </b>&nbsp;";
}

if (check_user_links ( Auth::user()->id, 'person' )) {
    echo "<a href='" . route('web::personen.index') . "'>Personen</a>&nbsp;<b>| </b>&nbsp;";
}

if (check_user_links ( Auth::user()->id, 'details' )) {
	echo "<a href='" . route('web::details::legacy', ['option' => 'detail_suche']) . "'>Details suchen</a>&nbsp;<b>| </b>&nbsp;";
}

if (check_user_links ( Auth::user()->id, 'mietkonten_blatt' )) {
	echo "<a href='" . route('web::mietkontenblatt::legacy') . "'>Miete</a>&nbsp;<b>| </b>&nbsp;";
}

if (check_user_links ( Auth::user()->id, 'rechnungen' )) {
	echo "<a href='" . route('web::rechnungen::legacy', ['option' => 'erfasste_rechnungen']). "'><b>Rechnungen</b> </a>&nbsp;<b>| </b>&nbsp;";
}

if (check_user_links ( Auth::user()->id, 'katalog' )) {
	echo "<a href='" . route('web::katalog::legacy') . "'>Katalog</a>&nbsp;<b>| </b>&nbsp;";
}

if (check_user_links ( Auth::user()->id, 'kontenrahmen' )) {
	echo "<a href='" . route('web::kontenrahmen::legacy') . "'>Kontenrahmen </a>&nbsp;";
}

if (check_user_links ( Auth::user()->id, 'geldkonten' )) {
	echo "<b>| </b>&nbsp;<a href='" . route('web::geldkonten::legacy') . "'>Geldkonten </a>&nbsp;<b>| </b>&nbsp;";
}

if (check_user_links ( Auth::user()->id, 'kasse' )) {
	echo "<a href='" . route('web::kassen::legacy') . "'>Kassen </a>&nbsp;<b>| </b>&nbsp;";
}

if (check_user_links ( Auth::user()->id, 'lager' )) {
	echo "<a href='" . route('web::lager::legacy') . "'>Lager </a>&nbsp;<b>| </b>&nbsp;";
}

if (check_user_links ( Auth::user()->id, 'buchen' )) {
	echo "<a href='" . route('web::buchen::legacy') . "'><b>Buchen</b> </a>&nbsp;<b>| </b>&nbsp;";
}

if (check_user_links ( Auth::user()->id, 'leerstand' )) {
	echo "<a href='" . route('web::leerstand::legacy') . "'>Leerstände </a>&nbsp;<b>| </b>&nbsp;";
}

if (check_user_links ( Auth::user()->id, 'statistik' )) {
	echo "<a href='" . route('web::statistik::legacy') . "'>Statistik </a>&nbsp;<b>| </b>&nbsp;";
}

if (check_user_links ( Auth::user()->id, 'zeiterfassung' )) {
	echo "<a href='" . route('web::zeiterfassung::legacy') . "'>Zeiterfassung </a>&nbsp;<b>| </b>&nbsp;";
}

if (check_user_links ( Auth::user()->id, 'urlaub' )) {
	echo "<a href='" . route('web::urlaub::legacy') . "'>Urlaub </a>&nbsp;<b>| </b>&nbsp;";
}

if (check_user_links ( Auth::user()->id, 'kautionen' )) {
	echo "<a href='" . route('web::kautionen::legacy') . "'>Kautionen </a>&nbsp;<b>| </b>&nbsp;";
}

if (check_user_links ( Auth::user()->id, 'bk' )) {
	echo "<a href='" . route('web::bk::legacy') . "'>BK&NK </a>&nbsp;<b>| </b>&nbsp;";
}

if (check_user_links ( Auth::user()->id, 'sepa' )) {
	echo "<a href='" . route('web::sepa::legacy') . "'><b>SEPA</b></a>&nbsp;<b>| </b>&nbsp;";
}

if (check_user_links ( Auth::user()->id, 'benutzer' )) {
	echo "<b>| </b>&nbsp;<a href='" . route('web::benutzer::legacy') . "'><b>Benutzer</b> </a>&nbsp;<b>| </b>&nbsp;";
}

if (check_user_links ( Auth::user()->id, 'weg' )) {
	echo "<b>| </b>&nbsp;<a class=\"WEG\" href='" . route('web::weg::legacy') . "'><b>WEG</b> </a>&nbsp;<b>| </b>&nbsp;";
}

if (check_user_links ( Auth::user()->id, 'todo' )) {
    echo "&nbsp;<a href='" . route('web::construction::legacy') . "'>Projekte und Aufgaben </a>&nbsp;<b>| </b>&nbsp;";
}

if (check_user_links ( Auth::user()->id, 'Wartung' )) {
	echo "&nbsp;<a href=\"/wartungsplaner/\" target=\"new\"><b>Wartungsplaner </b></a>&nbsp;<b>| </b>&nbsp;";
}

if (check_user_links ( Auth::user()->id, 'admin_panel' )) {
	echo "<a href='" . route('web::admin::legacy', ['admin_panel' => 'menu']) . "'>Administration </a>&nbsp;<b>| </b>";
}

if (check_user_links ( Auth::user()->id, 'listen' )) {
	echo "&nbsp;<a class=\"WEG\" href='" . route('web::listen::legacy') . "'>Listen</a>&nbsp;<b>| </b>&nbsp;";
}

if (check_user_links ( Auth::user()->id, 'tickets' )) {
	echo "&nbsp;<a class=\"WEG\" href='" . route('web::tickets::legacy') . "'>Tickets</a>&nbsp;<b>| </b>&nbsp;";
}

if (check_user_links ( Auth::user()->id, 'kundenweb' )) {
	echo "&nbsp;<a class=\"WEG\" href='" . route('web::kundenweb::legacy') . "'>Kundenweb</a>&nbsp;<b>| </b>&nbsp;";
}

if (check_user_links ( Auth::user()->id, 'mietspiegel' )) {
	echo "&nbsp;<a class=\"WEG\" href='" . route('web::mietspiegel::legacy') . "'>Mietspiegel</a>&nbsp;<b>| </b>&nbsp;";
}

echo "<a target=\"_new\" href=\"http://www.hausverwaltung.de/software/schnelleinstieg.html\">Handbuch</a>&nbsp;<b>| </b>&nbsp;";
if (check_user_links ( Auth::user()->id, 'buchen' )) {
	echo "<a href='" . route('web::dbbackup::legacy') . "'>DB sichern</a>&nbsp;";
}
ende_abschnitt();
echo "</div>";