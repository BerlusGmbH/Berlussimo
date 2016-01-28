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
 * @filesource   $HeadURL: http://192.168.2.52/svn/berlussimo_1/tags/02.11.2010 - Downloadversion 0.27/options/links/links.person.php $
 * @version      $Revision: 6 $
 * @modifiedby   $LastChangedBy: sivac $
 * @lastmodified $Date: 2010-09-21 10:34:50 +0200 (Di, 21 Sep 2010) $
 * 
 */
 

$mieten = new mietkonto;
echo "<div class=\"navi_leiste2\">"; 
$mieten->erstelle_formular("Hauptmenü -> Personen...", NULL);
echo "<a href=\"?daten=person&anzeigen=alle_personen\">Personenliste</a>&nbsp;";
echo "<a href=\"?daten=person&anzeigen=person_erfassen\">Neue Person</a>&nbsp;";
echo "<a href=\"?daten=person&anzeigen=person_hinweis\">Personen mit Hinweisen</a>&nbsp;";
echo "<a href=\"?daten=person&anzeigen=person_anschrift\">Zustell- und Verzugsanschriften</a>&nbsp;";
#echo "<a href=\"?daten=person&anzeigen=alle_mieter\">Mieterliste</a>&nbsp;";
$mieten->ende_formular();

echo "</div>";
?>
