<?php
/**
 * BERLUSSIMO
 *
 * Hausverwaltungssoftware
 *
 *
 * @copyright    Copyright (c) 2010, Berlus GmbH, Fontanestr. 1, 14193 Berlin
 * @link         http://www.berlus.de
 * @author       Sanel Sivac & Wolfgang Wehrheim
 * @contact		 software(@)berlus.de
 * @license     http://www.gnu.org/licenses/agpl.html AGPL Version 3
 * 
 * @filesource   $HeadURL: http://192.168.2.52/svn/berlussimo_1/tags/02.11.2010 - Downloadversion 0.27/options/links/links.statisch.php $
 * @version      $Revision: 15 $
 * @modifiedby   $LastChangedBy: sivac $
 * @lastmodified $Date: 2011-07-07 10:41:33 +0200 (Do, 07 Jul 2011) $
 * 
 */
include_once ("includes/allgemeine_funktionen.php");
$mieten = new mietkonto ();

echo "<div class=\"navi_leiste1\">";
erstelle_abschnitt( "Hauptmenü");

if (check_user_links ( $_SESSION ['benutzer_id'], 'partner' )) {
	//echo "<b>| </b>&nbsp;<a href=\"?daten=partner\"></a>&nbsp;<b>| </b>&nbsp;";
    echo "<ul class=\"nav list-group\">\n";
	echo "<li class='list-group-item'><a class=\"tree-toggler\" data-toggle='collapse' data-target='#partner'>Partner</a>\n"; echo"</li>\n";
    echo "<ul class=\"nav list-group\">\n";
    include_once ("options/links/links.partner.php");
    echo "</ul>\n";
    echo "</ul>\n";
}

if (check_user_links ( $_SESSION ['benutzer_id'], 'objekte_raus' )) {
	//echo "<a href=\"?daten=objekte_raus&objekte_raus=objekte_kurz\"></a>&nbsp;<b>| </b>&nbsp;";
    echo "<ul class=\"nav list-group\">\n";
    echo "<li class='list-group-item'><a class=\"tree-toggler\">Objekte</a></li>\n";
    echo "</ul>\n";
}

if (check_user_links ( $_SESSION ['benutzer_id'], 'haus_raus' )) {
	//echo "<a href=\"?daten=haus_raus&haus_raus=haus_kurz\"></a>&nbsp;<b>| </b>&nbsp;";
    echo "<ul class=\"nav list-group\">\n";
    echo "<li class='list-group-item'><a class=\"tree-toggler\">Häuser</a></li>\n";
    echo "</ul>\n";
}

if (check_user_links ( $_SESSION ['benutzer_id'], 'einheit_raus' )) {
	//echo "<a href=\"?daten=einheit_raus\">Einheiten</a>&nbsp;<b>| </b>&nbsp;";
    echo "<ul class=\"nav list-group\">\n";
    echo "<li class='list-group-item'><a class=\"tree-toggler\">Einheiten</a></li>\n";
    echo "</ul>\n";
}

if (check_user_links ( $_SESSION ['benutzer_id'], 'mietvertrag_raus' )) {
	//echo "<a href=\"?daten=mietvertrag_raus\">Mietverträge</a>&nbsp;<b>| </b>&nbsp;";
    echo "<ul class=\"nav list-group\">\n";
    echo "<li class='list-group-item'><a class=\"tree-toggler\">Mietverträge</a></li>\n";
    echo "</ul>\n";
}

if (check_user_links ( $_SESSION ['benutzer_id'], 'person' )) {
	//echo "<a href=\"?daten=person&anzeigen=alle_personen\">Personen</a>&nbsp;<b>| </b>&nbsp;";
    echo "<ul class=\"nav list-group\">\n";
    echo "<li class='list-group-item'><a class=\"tree-toggler\">Personen</a></li>\n";
    echo "</ul>\n";
}

if (check_user_links ( $_SESSION ['benutzer_id'], 'details' )) {
	//echo "<a href=\"?daten=details&option=detail_suche\">Details suchen</a>&nbsp;<b>| </b>&nbsp;";
    echo "<ul class=\"nav list-group\">\n";
    echo "<li class='list-group-item'><a class=\"tree-toggler\">Details suchen</a></li>\n";
    echo "</ul>\n";
}

if (check_user_links ( $_SESSION ['benutzer_id'], 'mietkonten_blatt' )) {
	//echo "<a href=\"?daten=mietkonten_blatt\">Miete</a>&nbsp;<b>| </b>&nbsp;";
    echo "<ul class=\"nav list-group\">\n";
    echo "<li class='list-group-item'><a class=\"tree-toggler\">Miete</a></li>\n";
    echo "</ul>\n";
}

if (check_user_links ( $_SESSION ['benutzer_id'], 'mietanpassung' )) {
	//echo "<a href=\"?daten=mietanpassung\">Mietanpassung MS</a>&nbsp;<b>| </b>&nbsp;";
    echo "<ul class=\"nav list-group\">\n";
    echo "<li class='list-group-item'><a class=\"tree-toggler\">Mietanpassung MS</a></li>\n";
    echo "</ul>\n";
}

if (check_user_links ( $_SESSION ['benutzer_id'], 'rechnungen' )) {
	//echo "<a href=\"?daten=rechnungen&option=erfasste_rechnungen\"><b>Rechnungen</b> </a>&nbsp;<b>| </b>&nbsp;";
    echo "<ul class=\"nav list-group\">\n";
    echo "<li class='list-group-item'><a class=\"tree-toggler\">Rechnungen</a></li>\n";
    echo "</ul>\n";
}

if (check_user_links ( $_SESSION ['benutzer_id'], 'katalog' )) {
	//echo "<a href=\"?daten=katalog\">Katalog</a>&nbsp;<b>| </b>&nbsp;";
    echo "<ul class=\"nav list-group\">\n";
    echo "<li class='list-group-item'><a class=\"tree-toggler\">Katalog</a></li>\n";
    echo "</ul>\n";
}

if (check_user_links ( $_SESSION ['benutzer_id'], 'kontenrahmen' )) {
	//echo "<a href=\"?daten=kontenrahmen\">Kontenrahmen </a>&nbsp;";
    echo "<ul class=\"nav list-group\">\n";
    echo "<li class='list-group-item'><a class=\"tree-toggler\">Kontenrahmen</a></li>\n";
    echo "</ul>\n";
}

if (check_user_links ( $_SESSION ['benutzer_id'], 'geldkonten' )) {
	//echo "<b>| </b>&nbsp;<a href=\"?daten=geldkonten\">Geldkonten </a>&nbsp;<b>| </b>&nbsp;";
    echo "<ul class=\"nav list-group\">\n";
    echo "<li class='list-group-item'><a class=\"tree-toggler\">Geldkonten</a></li>\n";
    echo "</ul>\n";
}

if (check_user_links ( $_SESSION ['benutzer_id'], 'kasse' )) {
	//echo "<a href=\"?daten=kasse\">Kassen </a>&nbsp;<b>| </b>&nbsp;";
    echo "<ul class=\"nav list-group\">\n";
    echo "<li class='list-group-item'><a class=\"tree-toggler\">Kassen</a></li>\n";
    echo "</ul>\n";
}

if (check_user_links ( $_SESSION ['benutzer_id'], 'lager' )) {
	//echo "<a href=\"?daten=lager\">Lager </a>&nbsp;<b>| </b>&nbsp;";
    echo "<ul class=\"nav list-group\">\n";
    echo "<li class='list-group-item'><a class=\"tree-toggler\">Lager</a></li>\n";
    echo "</ul>\n";
}

if (check_user_links ( $_SESSION ['benutzer_id'], 'buchen' )) {
	//echo "<a href=\"?daten=buchen\"><b>Buchen</b> </a>&nbsp;<b>| </b>&nbsp;";
    echo "<ul class=\"nav list-group\">\n";
    echo "<li class='list-group-item'><a class=\"tree-toggler\">Buchen</a></li>\n";
    echo "</ul>\n";
}

if (check_user_links ( $_SESSION ['benutzer_id'], 'leerstand' )) {
	//echo "<a href=\"?daten=leerstand\">Leerstände </a>&nbsp;<b>| </b>&nbsp;";
    echo "<ul class=\"nav list-group\">\n";
    echo "<li class='list-group-item'><a class=\"tree-toggler\">Leerstände</a></li>\n";
    echo "</ul>\n";
}

if (check_user_links ( $_SESSION ['benutzer_id'], 'statistik' )) {
	//echo "<a href=\"?daten=statistik\">Statistik </a>&nbsp;<b>| </b>&nbsp;";
    echo "<ul class=\"nav list-group\">\n";
    echo "<li class='list-group-item'><a class=\"tree-toggler\">Statistik</a></li>\n";
    echo "</ul>\n";
}

if (check_user_links ( $_SESSION ['benutzer_id'], 'zeiterfassung' )) {
	//echo "<a href=\"?daten=zeiterfassung\">Zeiterfassung </a>&nbsp;<b>| </b>&nbsp;";
    echo "<ul class=\"nav list-group\">\n";
    echo "<li class='list-group-item'><a class=\"tree-toggler\">Zeiterfassung</a></li>\n";
    echo "</ul>\n";
}

if (check_user_links ( $_SESSION ['benutzer_id'], 'urlaub' )) {
	//echo "<a href=\"?daten=urlaub\">Urlaub </a>&nbsp;<b>| </b>&nbsp;";
    echo "<ul class=\"nav list-group\">\n";
    echo "<li class='list-group-item'><a class=\"tree-toggler\">Urlaub</a></li>\n";
    echo "</ul>\n";
}

if (check_user_links ( $_SESSION ['benutzer_id'], 'kautionen' )) {
	//echo "<a href=\"?daten=kautionen\">Kautionen </a>&nbsp;<b>| </b>&nbsp;";
    echo "<ul class=\"nav list-group\">\n";
    echo "<li class='list-group-item'><a class=\"tree-toggler\">Kautionen</a></li>\n";
    echo "</ul>\n";
}

if (check_user_links ( $_SESSION ['benutzer_id'], 'bk' )) {
	//echo "<a href=\"?daten=bk\">BK&NK </a>&nbsp;<b>| </b>&nbsp;";
    echo "<ul class=\"nav list-group\">\n";
    echo "<li class='list-group-item'><a class=\"tree-toggler\">BK&NK</a></li>\n";
    echo "</ul>\n";
}

if (check_user_links ( $_SESSION ['benutzer_id'], 'wartung' )) {
	//echo "<a href=\"?daten=wartung\">Wartung </a>&nbsp;<b>| </b>&nbsp;";
    echo "<ul class=\"nav list-group\">\n";
    echo "<li class='list-group-item'><a class=\"tree-toggler\">Wartung</a></li>\n";
    echo "</ul>\n";
}

if (check_user_links ( $_SESSION ['benutzer_id'], 'sepa' )) {
	//echo "<a href=\"?daten=sepa\"><b>SEPA</b></a>&nbsp;<b>| </b>&nbsp;";
    echo "<ul class=\"nav list-group\">\n";
    echo "<li class='list-group-item'><a class=\"tree-toggler\">SEPA</a></li>\n";
    echo "</ul>\n";
}

if (check_user_links ( $_SESSION ['benutzer_id'], 'benutzer' )) {
	//echo "<b>| </b>&nbsp;<a href=\"?daten=benutzer\"><b>Benutzer</b> </a>&nbsp;<b>| </b>&nbsp;";
    echo "<ul class=\"nav list-group\">\n";
    echo "<li class='list-group-item'><a class=\"tree-toggler\">Benutzer</a></li>\n";
    echo "</ul>\n";
}

if (check_user_links ( $_SESSION ['benutzer_id'], 'weg' )) {
	//echo "<b>| </b>&nbsp;<a class=\"WEG\" href=\"?daten=weg\"><b>WEG</b> </a>&nbsp;<b>| </b>&nbsp;";
    echo "<ul class=\"nav list-group\">\n";
    echo "<li class='list-group-item'><a class=\"tree-toggler\">WEG</a></li>\n";
    echo "</ul>\n";
}

if (check_user_links ( $_SESSION ['benutzer_id'], 'todo' )) {
	//echo "&nbsp;<a href=\"?daten=todo\">Projekte und Aufgaben </a>&nbsp;<b>| </b>&nbsp;";
    echo "<ul class=\"nav list-group\">\n";
    echo "<li class='list-group-item'><a class=\"tree-toggler\">Projekte und Aufgaben</a></li>\n";
    echo "</ul>\n";
}

if (check_user_links ( $_SESSION ['benutzer_id'], 'Wartung' )) {
	//echo "&nbsp;<a href=\"/wartungsplaner/\" target=\"new\"><b>Wartungsplaner </b></a>&nbsp;<b>| </b>&nbsp;";
    echo "<ul class=\"nav list-group\">\n";
    echo "<li class='list-group-item'><a class=\"tree-toggler\">Wartungsplaner</a></li>\n";
    echo "</ul>\n";
}

if (check_user_links ( $_SESSION ['benutzer_id'], 'admin_panel' )) {
	//echo "<a href=\"?optionen=admin_panel&admin_panel=menu\">Administration </a>&nbsp;<b>| </b>";
    echo "<ul class=\"nav list-group\">\n";
    echo "<li class='list-group-item'><a class=\"tree-toggler\">Administration</a></li>\n";
    echo "</ul>\n";
}

if (check_user_links ( $_SESSION ['benutzer_id'], 'listen' )) {
	//echo "&nbsp;<a class=\"WEG\" href=\"?daten=listen\">Listen</a>&nbsp;<b>| </b>&nbsp;";
    echo "<ul class=\"nav list-group\">\n";
    echo "<li class='list-group-item'><a class=\"tree-toggler\">Listen</a></li>\n";
    echo "</ul>\n";
}

if (check_user_links ( $_SESSION ['benutzer_id'], 'tickets' )) {
	//echo "&nbsp;<a class=\"WEG\" href=\"?daten=tickets\">Tickets</a>&nbsp;<b>| </b>&nbsp;";
    echo "<ul class=\"nav list-group\">\n";
    echo "<li class='list-group-item'><a class=\"tree-toggler\">Tickets</a></li>\n";
    echo "</ul>\n";
}

if (check_user_links ( $_SESSION ['benutzer_id'], 'kundenweb' )) {
	//echo "&nbsp;<a class=\"WEG\" href=\"?daten=kundenweb\">Kundenweb</a>&nbsp;<b>| </b>&nbsp;";
    echo "<ul class=\"nav list-group\">\n";
    echo "<li class='list-group-item'><a class=\"tree-toggler\">Kundenweb</a></li>\n";
    echo "</ul>\n";
}

if (check_user_links ( $_SESSION ['benutzer_id'], 'mietspiegel' )) {
	//echo "&nbsp;<a class=\"WEG\" href=\"?daten=mietspiegel\">Mietspiegel</a>&nbsp;<b>| </b>&nbsp;";
    echo "<ul class=\"nav list-group\">\n";
    echo "<li class='list-group-item'><a class=\"tree-toggler\">Mietspiegel</a></li>\n";
    echo "</ul>\n";
}

//echo "<a href=\"?logout\">Abmelden</a>&nbsp;<b>| </b>&nbsp;";
echo "<ul class=\"nav list-group\">\n";
echo "<li class='list-group-item'><a class=\"tree-toggler\">Abmelden</a></li>\n";
echo "</ul>\n";
//echo "<a target=\"_new\" href=\"http://www.hausverwaltung.de/software/schnelleinstieg.html\">Handbuch</a>&nbsp;<b>| </b>&nbsp;";
echo "<ul class=\"nav list-group\">\n";
echo "<li class='list-group-item'><a class=\"tree-toggler\">Handbuch</a></li>\n";
echo "</ul>\n";
if (check_user_links ( $_SESSION ['benutzer_id'], 'buchen' )) {
	//echo "<a href=\"?daten=dbbackup\">DB sichern </a>&nbsp;";
    echo "<ul class=\"nav list-group\">\n";
    echo "<li class='list-group-item'><a class=\"tree-toggler\">DB sichern</a></li>\n";
    echo "</ul>\n";
}
ende_abschnitt();
echo "</div>";