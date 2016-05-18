<?php
echo "<div class=\"navi_leiste1\">";
erstelle_abschnitt( "Hauptmenü");


if (check_user_links ( Auth::user()->id, 'partner' )) {
	echo "<b>| </b>&nbsp;<a href='" . route('legacy::partner::index') . "'>Partner/Lieferant</a>&nbsp;<b>| </b>&nbsp;";
}

if (check_user_links ( Auth::user()->id, 'objekte_raus' )) {
	echo "<a href='" . route('legacy::objekte::index', ['objekte_raus' => 'objekt_kurz']) . "'>Objekte</a>&nbsp;<b>| </b>&nbsp;";
}

if (check_user_links ( Auth::user()->id, 'haus_raus' )) {
	echo "<a href='" . route('legacy::haeuser::index', ['haus_raus' => 'haus_kurz']) . "'>Häuser</a>&nbsp;<b>| </b>&nbsp;";
}

if (check_user_links ( Auth::user()->id, 'einheit_raus' )) {
	echo "<a href='" . route('legacy::einheiten::index') . "'>Einheiten</a>&nbsp;<b>| </b>&nbsp;";
}

if (check_user_links ( Auth::user()->id, 'mietvertrag_raus' )) {
	echo "<a href='" . route('legacy::mietvertraege::index') . "'>Mietverträge</a>&nbsp;<b>| </b>&nbsp;";
}

if (check_user_links ( Auth::user()->id, 'person' )) {
	echo "<a href='" . route('legacy::personen::index', ['anzeigen' => 'alle_personen']) . "'>Personen</a>&nbsp;<b>| </b>&nbsp;";
}

if (check_user_links ( Auth::user()->id, 'details' )) {
	echo "<a href='" . route('legacy::details::index', ['option' => 'detail_suche']) . "'>Details suchen</a>&nbsp;<b>| </b>&nbsp;";
}

if (check_user_links ( Auth::user()->id, 'mietkonten_blatt' )) {
	echo "<a href='" . route('legacy::mietkontenblatt::index') . "'>Miete</a>&nbsp;<b>| </b>&nbsp;";
}

if (check_user_links ( Auth::user()->id, 'rechnungen' )) {
	echo "<a href='" . route('legacy::rechnungen::index', ['option' => 'erfasste_rechnungen']). "'><b>Rechnungen</b> </a>&nbsp;<b>| </b>&nbsp;";
}

if (check_user_links ( Auth::user()->id, 'katalog' )) {
	echo "<a href='" . route('legacy::katalog::index') . "'>Katalog</a>&nbsp;<b>| </b>&nbsp;";
}

if (check_user_links ( Auth::user()->id, 'kontenrahmen' )) {
	echo "<a href='" . route('legacy::kontenrahmen::index') . "'>Kontenrahmen </a>&nbsp;";
}

if (check_user_links ( Auth::user()->id, 'geldkonten' )) {
	echo "<b>| </b>&nbsp;<a href='" . route('legacy::geldkonten::index') . "'>Geldkonten </a>&nbsp;<b>| </b>&nbsp;";
}

if (check_user_links ( Auth::user()->id, 'kasse' )) {
	echo "<a href='" . route('legacy::kassen::index') . "'>Kassen </a>&nbsp;<b>| </b>&nbsp;";
}

if (check_user_links ( Auth::user()->id, 'lager' )) {
	echo "<a href='" . route('legacy::lager::index') . "'>Lager </a>&nbsp;<b>| </b>&nbsp;";
}

if (check_user_links ( Auth::user()->id, 'buchen' )) {
	echo "<a href='" . route('legacy::buchen::index') . "'><b>Buchen</b> </a>&nbsp;<b>| </b>&nbsp;";
}

if (check_user_links ( Auth::user()->id, 'leerstand' )) {
	echo "<a href='" . route('legacy::leerstand::index') . "'>Leerstände </a>&nbsp;<b>| </b>&nbsp;";
}

if (check_user_links ( Auth::user()->id, 'statistik' )) {
	echo "<a href='" . route('legacy::statistik::index') . "'>Statistik </a>&nbsp;<b>| </b>&nbsp;";
}

if (check_user_links ( Auth::user()->id, 'zeiterfassung' )) {
	echo "<a href='" . route('legacy::zeiterfassung::index') . "'>Zeiterfassung </a>&nbsp;<b>| </b>&nbsp;";
}

if (check_user_links ( Auth::user()->id, 'urlaub' )) {
	echo "<a href='" . route('legacy::urlaub::index') . "'>Urlaub </a>&nbsp;<b>| </b>&nbsp;";
}

if (check_user_links ( Auth::user()->id, 'kautionen' )) {
	echo "<a href='" . route('legacy::kautionen::index') . "'>Kautionen </a>&nbsp;<b>| </b>&nbsp;";
}

if (check_user_links ( Auth::user()->id, 'bk' )) {
	echo "<a href='" . route('legacy::bk::index') . "'>BK&NK </a>&nbsp;<b>| </b>&nbsp;";
}

if (check_user_links ( Auth::user()->id, 'sepa' )) {
	echo "<a href='" . route('legacy::sepa::index') . "'><b>SEPA</b></a>&nbsp;<b>| </b>&nbsp;";
}

if (check_user_links ( Auth::user()->id, 'benutzer' )) {
	echo "<b>| </b>&nbsp;<a href='" . route('legacy::benutzer::index') . "'><b>Benutzer</b> </a>&nbsp;<b>| </b>&nbsp;";
}

if (check_user_links ( Auth::user()->id, 'weg' )) {
	echo "<b>| </b>&nbsp;<a class=\"WEG\" href='" . route('legacy::weg::index') . "'><b>WEG</b> </a>&nbsp;<b>| </b>&nbsp;";
}

if (check_user_links ( Auth::user()->id, 'todo' )) {
	echo "&nbsp;<a href='" . route('legacy::todo::index') . "'>Projekte und Aufgaben </a>&nbsp;<b>| </b>&nbsp;";
}

if (check_user_links ( Auth::user()->id, 'Wartung' )) {
	echo "&nbsp;<a href=\"/wartungsplaner/\" target=\"new\"><b>Wartungsplaner </b></a>&nbsp;<b>| </b>&nbsp;";
}

if (check_user_links ( Auth::user()->id, 'admin_panel' )) {
	echo "<a href='" . route('legacy::admin::index', ['admin_panel' => 'menu']) . "'>Administration </a>&nbsp;<b>| </b>";
}

if (check_user_links ( Auth::user()->id, 'listen' )) {
	echo "&nbsp;<a class=\"WEG\" href='" . route('legacy::listen::index') . "'>Listen</a>&nbsp;<b>| </b>&nbsp;";
}

if (check_user_links ( Auth::user()->id, 'tickets' )) {
	echo "&nbsp;<a class=\"WEG\" href='" . route('legacy::tickets::index') . "'>Tickets</a>&nbsp;<b>| </b>&nbsp;";
}

if (check_user_links ( Auth::user()->id, 'kundenweb' )) {
	echo "&nbsp;<a class=\"WEG\" href='" . route('legacy::kundenweb::index') . "'>Kundenweb</a>&nbsp;<b>| </b>&nbsp;";
}

if (check_user_links ( Auth::user()->id, 'mietspiegel' )) {
	echo "&nbsp;<a class=\"WEG\" href='" . route('legacy::mietspiegel::index') . "'>Mietspiegel</a>&nbsp;<b>| </b>&nbsp;";
}

echo "<a target=\"_new\" href=\"http://www.hausverwaltung.de/software/schnelleinstieg.html\">Handbuch</a>&nbsp;<b>| </b>&nbsp;";
if (check_user_links ( Auth::user()->id, 'buchen' )) {
	echo "<a href='" . route('legacy::dbbackup::index') . "'>DB sichern</a>&nbsp;";
}
ende_abschnitt();
echo "</div>";