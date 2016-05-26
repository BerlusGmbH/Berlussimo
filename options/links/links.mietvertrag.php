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
 * @filesource   $HeadURL: http://192.168.2.52/svn/berlussimo_1/tags/02.11.2010 - Downloadversion 0.27/options/links/links.mietvertrag.php $
 * @version      $Revision: 6 $
 * @modifiedby   $LastChangedBy: sivac $
 * @lastmodified $Date: 2010-09-21 10:34:50 +0200 (Di, 21 Sep 2010) $
 * 
 */
echo "<div class=\"navi_leiste2\">";
erstelle_abschnitt("Mietverträge");
$monat = sprintf ( '%02d', date ( "m" ) );
$jahr = date ( "Y" );
echo "<a href=\"?daten=mietvertrag_raus&mietvertrag_raus=mietvertrag_kurz\">Alle Mietverträge</a>&nbsp;";
echo "<a href=\"?daten=mietvertrag_raus&mietvertrag_raus=mietvertrag_aktuelle\">Aktuelle</a>&nbsp;";
echo "<a href=\"?daten=mietvertrag_raus&mietvertrag_raus=mietvertrag_abgelaufen\">Abgelaufene</a>&nbsp;";
echo "<a href=\"?daten=mietvertrag_raus&mietvertrag_raus=mietvertrag_neu\">Neuer Mietvertrag</a>&nbsp;";
echo "<a href=\"?daten=mietvertrag_raus&mietvertrag_raus=ls_teilnehmer_neu\">LS Teilnehmer neu</a>&nbsp;";
echo "<a href=\"?daten=mietvertrag_raus&mietvertrag_raus=ls_teilnehmer\">LS Teilnehmer</a>&nbsp;";
echo "<a href=\"?daten=mietvertrag_raus&mietvertrag_raus=ls_teilnehmer_inaktiv\">LS Teilnehmer inaktiv</a>&nbsp;";
echo "<a href=\"?daten=mietvertrag_raus&mietvertrag_raus=letzte_auszuege\">Letzte Auszüge Objekt</a>&nbsp;<br>";
echo "<a href=\"?daten=mietvertrag_raus&mietvertrag_raus=letzte_einzuege\">Letzte Einzüge Objekt</a>&nbsp;";
echo "<a href=\"?daten=mietvertrag_raus&mietvertrag_raus=alle_letzten_auszuege&monat=$monat&jahr=$jahr\">Alle Auszüge </a>&nbsp;";
echo "<a href=\"?daten=mietvertrag_raus&mietvertrag_raus=alle_letzten_einzuege&monat=$monat&jahr=$jahr\">Alle Einzüge </a>&nbsp;";
echo "<a href=\"?daten=mietvertrag_raus&mietvertrag_raus=mahnliste_alle\"><b>Mahnliste alle</b></a>&nbsp;";
echo "<a href=\"?daten=mietvertrag_raus&mietvertrag_raus=mahnliste\"><b>Mahnliste akt.</b></a>&nbsp;";
echo "<a href=\"?daten=mietvertrag_raus&mietvertrag_raus=mahnliste_ausgezogene\"><b>Mahnliste ex. Mieter</b></a>&nbsp;";
echo "<a href=\"?daten=mietvertrag_raus&mietvertrag_raus=guthaben_liste\"><b>Guthaben</b></a>&nbsp;";
echo "<a href=\"?daten=mietvertrag_raus&mietvertrag_raus=saldenliste\"><b>Saldenlisten</b></a>&nbsp;";
echo "<a href=\"?daten=mietvertrag_raus&mietvertrag_raus=nebenkosten\"><b>Nebenkosten/Jahr</b></a>&nbsp;";
$vorjahr = date ( "Y" ) - 1;
echo "<a href=\"?daten=mietvertrag_raus&mietvertrag_raus=nebenkosten_pdf_zs&jahr=$vorjahr\"><b>NK/KM/Jahr mit ZS</b></a>&nbsp;";
echo "<a href=\"?daten=mietvertrag_raus&mietvertrag_raus=nebenkosten_pdf_zs&jahr=$vorjahr&xls\"><b>NK/KM/Jahr mit ZS als XLS</b></a>&nbsp;";
ende_abschnitt();
echo "</div>";


