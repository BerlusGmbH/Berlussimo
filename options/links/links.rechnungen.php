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
 * @filesource   $HeadURL: http://192.168.2.52/svn/berlussimo_1/tags/02.11.2010 - Downloadversion 0.27/options/links/links.rechnungen.php $
 * @version      $Revision: 15 $
 * @modifiedby   $LastChangedBy: sivac $
 * @lastmodified $Date: 2011-07-07 10:41:33 +0200 (Do, 07 Jul 2011) $
 * 
 */
$mieten = new mietkonto ();
echo "<div class=\"navi_leiste2\">";
$mieten->erstelle_formular ( "Hauptmen� -> Rechnungen...", NULL );

echo "<a href=\"?daten=rechnungen&option=erfasste_rechnungen\">Rechnungsliste</a>&nbsp;";
echo "<a href=\"?daten=rechnungen&option=eingangsbuch\">Eingangsbuch</a>&nbsp;";
echo "<a href=\"?daten=rechnungen&option=ausgangsbuch\">Ausgangsbuch</a>&nbsp;";
echo "<a href=\"?daten=rechnungen&option=rechnung_erfassen\">Bargeldlose Rechnung erfassen</a>&nbsp;";
echo "<a href=\"?daten=rechnungen&option=gutschrift_erfassen\">Gutschrift</a>&nbsp;";
echo "<a href=\"?daten=rechnungen&option=pool_rechnungen\">Rechnung aus Pool erstellen</a>&nbsp;";
echo "<a href=\"?daten=rechnungen&option=rechnung_suchen\"><b>Rechnung suchen</b></a>&nbsp;";
echo "<hr>";
echo "<a href=\"?daten=rechnungen&option=vollstaendige_rechnungen\">Vollst�ndige Rechnungen</a>&nbsp;";
echo "<a href=\"?daten=rechnungen&option=unvollstaendige_rechnungen\">Unvollst�ndige Rechnungen</a>&nbsp;";
// echo "<a href=\"?daten=rechnungen&option=gutschrift_erfassen\">Gutschrift schreiben</a>&nbsp;";
echo "<a href=\"?daten=rechnungen&option=kontierte_rechnungen\">Kontierte Rechnungen</a>&nbsp;";
echo "<a href=\"?daten=rechnungen&option=nicht_kontierte_rechnungen\">Nicht kontierte Rechnungen</a>&nbsp;";
echo "<a href=\"?daten=zeiterfassung&option=stundennachweise\"><b>Stundennachweise</b></a>&nbsp;";
echo "<a href=\"?daten=rechnungen&option=buchungsbelege\">Buchungsbelege</a>&nbsp;";
echo "<a href=\"?daten=rechnungen&option=rechnungsbuch_suche\">Rechnungsb�cher PDF</a>&nbsp;";
echo "<hr><a href=\"?daten=rechnungen&option=meine_angebote\">Angebote</a>&nbsp;";
echo "<a href=\"?daten=rechnungen&option=angebot_erfassen\">Angebot erfassen</a>&nbsp;";
echo "<a href=\"?daten=rechnungen&option=u_pool_erstellen\">Unterpool erstellen</a>&nbsp;";
echo "<a href=\"?daten=rechnungen&option=u_pool_liste\">Rechnungen im Unterpool</a>&nbsp;";

echo "<a href=\"?daten=rechnungen&option=verbindlichkeiten\">Verbindlichkeiten</a>&nbsp;";
echo "<a href=\"?daten=rechnungen&option=forderungen\">Forderungen</a>&nbsp;";
echo "<a href=\"?daten=rechnungen&option=form_ugl\">UGL-Import</a>&nbsp;";
echo "<a href=\"?daten=rechnungen&option=import_csv\">CSV-Import</a>&nbsp;";
echo "<a href=\"?daten=rechnungen&option=kosten_einkauf\">Kosten Einkauf</a>&nbsp;";
echo "<a href=\"?daten=rechnungen&option=seb\">SEB</a>&nbsp;";
echo "<a href=\"?daten=rechnungen&option=vg_rechnungen\">Verwaltergeb�hren</a>&nbsp;";
echo "<a href=\"?daten=rechnungen&option=rg_aus_beleg\">RG aus Beleg</a>&nbsp;";
echo "<a href=\"?daten=rechnungen&option=pdf_druckpool&no_logo\">PDF-Druckpool</a>&nbsp;";
echo "<a href=\"?daten=rechnungen&option=sepa_druckpool\">SEPA RG-Pool</a>&nbsp;";

// echo "<a href=\"?daten=rechnungen&option=bezahlte_rechnungen\">Bezahlte Rechnungen</a>&nbsp;";
// echo "<a href=\"?daten=rechnungen&option=unbezahlte_rechnungen\">Unbezahlte Rechnungen</a>&nbsp;";
// echo "<a href=\"?daten=rechnungen&option=bestaetigte_rechnungen\">Best�tigte Rechnungen</a>&nbsp;";
// echo "<a href=\"?daten=rechnungen&option=unbestaetigte_rechnungen\">Unbest�tigte Rechnungen</a>&nbsp;";

$mieten->ende_formular ();

echo "</div>";
?>
