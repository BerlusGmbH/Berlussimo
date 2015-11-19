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
 * @filesource   $HeadURL: http://192.168.2.52/svn/berlussimo_1/tags/02.11.2010 - Downloadversion 0.27/options/links/links.buchen.php $
 * @version      $Revision: 15 $
 * @modifiedby   $LastChangedBy: sivac $
 * @lastmodified $Date: 2011-07-07 10:41:33 +0200 (Do, 07 Jul 2011) $
 * 
 */

$mieten = new mietkonto;
echo "<div class=\"navi_leiste2\">"; 
$mieten->erstelle_formular("Hauptmenü -> Buchen...", NULL);

echo "<a href=\"?daten=miete_buchen\">Miete Buchen</a>&nbsp;";
echo "<a href=\"?daten=buchen&option=zahlbetrag_buchen\">Kosten buchen</a>&nbsp;";
echo "<a href=\"?daten=buchen&option=ausgangsbuch_kurz\">RA buchen</a>&nbsp;";
#echo "<a href=\"?daten=buchen&option=eingangsbuch_kurz\">RE buchen</a>&nbsp;";
echo "<a href=\"?daten=buchen&option=eingangsbuch_kurz&anzeige=empfaenger_eingangs_rnr\">RE buchen</a>&nbsp;";

echo "<a href=\"?daten=buchen&option=buchungs_journal\">Buchungsjournal</a>&nbsp;";
$jahr = date("Y");
$vorjahr = date("Y")-1;
echo "<a href=\"?daten=buchen&option=buchungs_journal_jahr_pdf&jahr=$jahr\">Buchungsjournal $jahr PDF</a>&nbsp;";
echo "<a href=\"?daten=buchen&option=buchungs_journal_jahr_pdf&jahr=$vorjahr&xls\">Buchungsjournal $vorjahr XLS</a>&nbsp;";
echo "<a href=\"?daten=buchen&option=konten_uebersicht\">Kontenübersicht</a>&nbsp;";
echo "<a href=\"?daten=buchen&option=konto_uebersicht\">Kontoübersicht</a>&nbsp;";
echo "<a href=\"?daten=buchen&option=buchungen_zu_kostenkonto\">Buchungen zu Kostenkonto</a>&nbsp;";
echo "<a href=\"?daten=buchen&option=monatsbericht_o_a\">Monatsbericht o. Auszug</a>&nbsp;";
echo "<a href=\"?daten=buchen&option=monatsbericht_m_a\">Monatsbericht m. Auszug</a>&nbsp;";
echo "<a href=\"?daten=buchen&option=kosten_einnahmen\">Kosten&Einnahmen</a>&nbsp;";
echo "<a href=\"?daten=buchen&option=buchung_suchen\">Buchung suchen</a>&nbsp;";
echo "<a href=\"?daten=buchen&option=kostenkonto_pdf\">Kostenkonto PDF</a>&nbsp;";
echo "<a href=\"?daten=buchen&option=excel_buchen&upload\">ExcelUPLOAD</a>&nbsp;";
echo "<a href=\"?daten=buchen&option=excel_buchen_session\">Exceldaten verbuchen</a>&nbsp;";
echo "<a href=\"?daten=buchen&option=uebersicht_excel_konten\">Excel Konten</a>&nbsp;";
echo "<a href=\"?daten=buchen&option=buchungskonto_summiert_xls&jahr=$vorjahr\">Buchungskonten summiert XLS</a>&nbsp;";

#echo "<a href=\"?daten=buchen&option=kontoauszug_csv\">kONTOAUSZUG CSV</a>&nbsp;";
$mieten->ende_formular();

echo "</div>";


?>
