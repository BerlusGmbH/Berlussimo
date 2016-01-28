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
 * @filesource   $HeadURL: http://192.168.2.52/svn/berlussimo_1/tags/02.11.2010 - Downloadversion 0.27/options/links/links.geldkonten.php $
 * @version      $Revision: 6 $
 * @modifiedby   $LastChangedBy: sivac $
 * @lastmodified $Date: 2010-09-21 10:34:50 +0200 (Di, 21 Sep 2010) $
 * 
 */

$mieten = new mietkonto;
echo "<div class=\"navi_leiste2\">"; 
$mieten->erstelle_formular("Hauptmenü -> GELDKONTEN...", NULL);

echo "<a href=\"?daten=geldkonten\">Kontostände</a>&nbsp;";
echo "<a href=\"?daten=geldkonten&option=uebersicht_ea\">Übersicht E/A</a>&nbsp;";
echo "<a href=\"?daten=geldkonten&option=gk_neu\">GK erstellen</a>&nbsp;";
echo "<a href=\"?daten=geldkonten&option=gk_zuweisen\">GK zuweisen</a>&nbsp;";
#echo "<a href=\"?daten=dt_aus&option=dtaus_buchen\">DTAUS Buchen</a>&nbsp;";
echo "<a href=\"?daten=geldkonten&option=uebersicht_zuweisung\">Übersicht Zuweisung</a>&nbsp;";

$mieten->ende_formular();

echo "</div>";
?>
