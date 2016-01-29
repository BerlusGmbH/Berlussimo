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
 * @filesource   $HeadURL: http://192.168.2.52/svn/berlussimo_1/tags/02.11.2010 - Downloadversion 0.27/options/links/links.mietkonten_blatt.php $
 * @version      $Revision: 6 $
 * @modifiedby   $LastChangedBy: sivac $
 * @lastmodified $Date: 2010-09-21 10:34:50 +0200 (Di, 21 Sep 2010) $
 * 
 */
$mieten = new mietkonto ();
echo "<div class=\"navi_leiste2\">";
$mieten->erstelle_formular ( "Hauptmen� -> Miete...", NULL );
// echo "<a href=\"?daten=mietkonten_blatt&anzeigen=mietkonto_uebersicht_detailiert&mietvertrag_id=528\">MIETKONTENBLATT DETAILIERT</a>&nbsp;";
// echo "<a href=\"?daten=mietkonten_blatt&anzeigen=buchung_aktuell&mietvertrag_id=528\">Aktuelle Buchungen / Forderungen</a>&nbsp;";
// echo "<a href=\"?daten=mietkonten_blatt&anzeigen=aufteilung_buchung_zeitraum&mietvertrag_id=528\">Aktuelle Aufteilung</a>&nbsp;";
// echo "<a href=\"?daten=mietkonten_blatt&anzeigen=buchung_zeitraum&mietvertrag_id=528\">Jahres�bersicht Buchung</a>&nbsp;";

// echo "<a href=\"?daten=mietkonten_blatt&anzeigen=liste\">MIETEN MANUEL BUCHEN</a>&nbsp;";
echo "<a href=\"?daten=miete_definieren\">Mieth�he definieren</a>&nbsp;";
echo "<a href=\"?daten=miete_definieren&option=mieterlisten_kostenkat&kostenkat=MOD\">Mieterliste MOD</a>&nbsp;";
echo "<a href=\"?daten=miete_definieren&option=mieterlisten_kostenkat&kostenkat=Untermieter Zuschlag\">Mieterliste Untermieterz.</a>&nbsp;";
echo "<a href=\"?daten=sepa&option=ls_auto_buchen\">SEPA LS-Autobuchen</a>&nbsp;";

// echo "<a href=\"?daten=mietkonten_blatt&anzeigen=alle_buchungen&mietvertrag_id=528\">�BERSICHT</a>&nbsp;";
// echo "<a href=\"?daten=mietkonten_blatt&anzeigen=forderung_seit_einzug&mietvertrag_id=528\">SEIT EINZUG</a>&nbsp;";

// echo "<a href=\"?daten=mietkonten_blatt&anzeigen=mietkonto_auszug&mietvertrag_id=528\">�BERSICHTSTABELLEN MIETKONTO</a>&nbsp;";
// echo "<a href=\"?daten=miete_buchen\">Letzte </a>&nbsp;";

$mieten->ende_formular ();

echo "</div>";
?>
