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
 * @filesource   $HeadURL: http://192.168.2.52/svn/berlussimo_1/tags/02.11.2010 - Downloadversion 0.27/options/links/links.mietkonten_blatt_uebersicht.php $
 * @version      $Revision: 6 $
 * @modifiedby   $LastChangedBy: sivac $
 * @lastmodified $Date: 2010-09-21 10:34:50 +0200 (Di, 21 Sep 2010) $
 * 
 */
echo "<div class=\"navi_leiste2\">";
erstelle_abschnitt("Mietkontenübersicht -> Darstelltungsoptionen...");
echo "<a href=\"?daten=mietkonten_blatt&anzeigen=mietkonto_uebersicht_detailiert&mietvertrag_id=$_REQUEST[mietvertrag_id]\">Seit Einzug</a>&nbsp;";
echo "<a href=\"?daten=mietkonten_blatt&anzeigen=mietkonto_detailiert_seit_1zahlung&mietvertrag_id=$_REQUEST[mietvertrag_id]\">Seit 1. Zahlung</a>&nbsp;";
echo "<a href=\"?daten=miete_buchen\">Zeitraum eingrenzen</a>&nbsp;";
ende_abschnitt();
echo "</div>";
