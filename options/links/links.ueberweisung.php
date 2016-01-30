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
 * @filesource   $HeadURL: http://192.168.2.52/svn/berlussimo_1/tags/02.11.2010 - Downloadversion 0.27/options/links/links.ueberweisung.php $
 * @version      $Revision: 15 $
 * @modifiedby   $LastChangedBy: sivac $
 * @lastmodified $Date: 2011-07-07 10:41:33 +0200 (Do, 07 Jul 2011) $
 * 
 */
$f = new formular ();
echo "<div class=\"navi_leiste2\">";
$f->erstelle_formular ( "Hauptmenü -> Überweisung DTAUS...", NULL );
echo "<a href=\"?daten=ueberweisung\">DTAUS Pool</a>&nbsp;";
echo "<a href=\"?daten=ueberweisung&option=re_zahlen\">RE Zahlen</a>&nbsp;";
echo "<a href=\"?daten=ueberweisung&option=dtaus_dateien\">Erstellte Dateien</a>&nbsp;";
echo "<a href=\"?daten=ueberweisung&option=manuelle_ueberweisung\">Sammelüberweisung</a>&nbsp;";
$f->ende_formular ();

echo "</div>";

?>
