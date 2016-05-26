<?php
/*
 * Created on / Erstellt am : 16.11.2010
 * Author: Sivac
 */
/**
 * BERLUSSIMO
 *
 * Hausverwaltungssoftware
 *
 *
 * @copyright Copyright (c) 2010, Berlus GmbH, Fontanestr. 1, 14193 Berlin
 * @link http://www.berlus.de
 * @author Sanel Sivac & Wolfgang Wehrheim
 *         @contact software(@)berlus.de
 * @license http://www.gnu.org/licenses/agpl.html AGPL Version 3
 *         
 * @filesource $HeadURL$
 * @version $Revision$
 *          @modifiedby $LastChangedBy$
 *          @lastmodified $Date$
 *         
 */
echo "<div class=\"navi_leiste2\">";
if (empty ( $_SESSION ['objekt_id'] )) {
	erstelle_abschnitt("WEG");
} else {
	$o = new objekt ();
	$o->get_objekt_infos ( $_SESSION ['objekt_id'] );
	erstelle_abschnitt("Hauptmenü -> WEG -> $o->objekt_kurzname");
}

echo "<a class=\"WEG\" href=\"?daten=weg\">WEG</a>&nbsp;";
echo "<a class=\"WEG\" href=\"?daten=weg&option=objekt_auswahl\">Objektliste</a>&nbsp;";
echo "<a class=\"WEG\" href=\"?daten=weg&option=stammdaten_weg&lang=en\">STAMM-PDF</a>&nbsp;";
echo "<a class=\"WEG\" href=\"?daten=weg&option=pdf_et_liste_alle_kurz\">ET-Liste kurz</a>&nbsp;";
echo "<a class=\"WEG\" href=\"?daten=weg&option=wohngeld_buchen_auswahl_e\">Hausgeld buchen</a>&nbsp;";
echo "<a class=\"WEG\" href=\"?daten=buchen&option=zahlbetrag_buchen\">Kosten buchen</a>&nbsp;";
echo "<a class=\"WEG\" href=\"?daten=weg&option=einheiten\">Einheiten</a>&nbsp;";
echo "<a class=\"WEG\" href=\"?daten=weg&option=eigentuemer_wechsel\">Eigentümerwechsel</a>&nbsp;";
echo "<a class=\"WEG\" href=\"?daten=weg&option=mahnliste\">Mahnliste</a>&nbsp;";
echo "<a class=\"WEG\" href=\"?daten=weg&option=wpliste\">Wirtschaftspläne</a>&nbsp;";
echo "<a class=\"WEG\" href=\"?daten=weg&option=wp_neu\">WP-Neu</a>&nbsp;";
echo "<a class=\"WEG\" href=\"?daten=weg&option=assistent\">HGA-Assistent</a>&nbsp;<hr>";
echo "<a class=\"WEG\" href=\"?daten=weg&option=hga_profile\">HGA-Profile</a>&nbsp;";
echo "<a class=\"WEG\" href=\"?daten=weg&option=hk_verbrauch_tab\">HK-Verbrauch</a>&nbsp;";
echo "<a class=\"WEG\" href=\"?daten=weg&option=kontostand_erfassen\">Kontostand erfassen</a>&nbsp;";
echo "<a class=\"WEG\" href=\"?daten=weg&option=hga_gesamt_pdf\">PDF-HGA-Gesamtabrechnung</a>&nbsp;";
echo "<a class=\"WEG\" href=\"?daten=weg&option=ihr\">IHR</a>&nbsp;";
echo "<a class=\"WEG\" href=\"?daten=weg&option=pdf_ihr\">PDF-IHR</a>&nbsp;";
echo "<a class=\"WEG\" href=\"?daten=weg&option=hga_einzeln\">PDF-HGA-Einzelabrechnung</a>&nbsp;";
echo "<a class=\"WEG\" href=\"?daten=weg&option=serienbrief\">Serienbrief</a>&nbsp;";
$jahr = date ( "Y" );
$vorjahr = date ( "Y" ) - 1;
echo "<a class=\"WEG\" href=\"?daten=weg&option=hausgeld_zahlungen&jahr=$jahr\">Kontenübersicht $jahr</a>&nbsp;";
echo "<a class=\"WEG\" href=\"?daten=weg&option=hausgeld_zahlungen_xls&jahr=$vorjahr\">Kontenübersicht XLS $vorjahr</a>&nbsp;";
echo "<a class=\"WEG\" href=\"?daten=weg&option=pdf_hausgelder\">Hausgelder</a>&nbsp;";
ende_abschnitt();
echo "</div>";