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
 * @filesource   $HeadURL: http://192.168.2.52/svn/berlussimo_1/tags/02.11.2010 - Downloadversion 0.27/options/links/links.kontenrahmen.php $
 * @version      $Revision: 6 $
 * @modifiedby   $LastChangedBy: sivac $
 * @lastmodified $Date: 2010-09-21 10:34:50 +0200 (Di, 21 Sep 2010) $
 * 
 */
 
$mieten = new mietkonto;
echo "<div class=\"navi_leiste2\">"; 
$mieten->erstelle_formular("Hauptmenü -> Kontenrahmen...", NULL);


echo "<a href=\"?daten=kontenrahmen&option=kontenrahmen_uebersicht\">Kontenrahmenübersicht</a>&nbsp;";
echo "<a href=\"?daten=kontenrahmen&option=kontenrahmen_neu\">Kontenrahmen erstellen</a>&nbsp;";
echo "<a href=\"?daten=kontenrahmen&option=kostenkonto_neu\">Buchungskonto erstellen</a>&nbsp;";
echo "<a href=\"?daten=kontenrahmen&option=gruppen\">Gruppen anzeigen</a>&nbsp;";
echo "<a href=\"?daten=kontenrahmen&option=gruppe_neu\">Gruppen erstellen</a>&nbsp;";
echo "<a href=\"?daten=kontenrahmen&option=kontoarten\">Kontoarten anzeigen</a>&nbsp;";
echo "<a href=\"?daten=kontenrahmen&option=kontoart_neu\">Kontoart erstellen</a>&nbsp;";
echo "<a href=\"?daten=kontenrahmen&option=kontenrahmen_zuweisen\">Kontenrahmen zuweisen</a>&nbsp;";


$mieten->ende_formular();

echo "</div>";
?>
