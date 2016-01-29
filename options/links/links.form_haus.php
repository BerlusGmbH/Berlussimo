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
 * @filesource   $HeadURL: http://192.168.2.52/svn/berlussimo_1/tags/02.11.2010 - Downloadversion 0.27/options/links/links.form_haus.php $
 * @version      $Revision: 6 $
 * @modifiedby   $LastChangedBy: sivac $
 * @lastmodified $Date: 2010-09-21 10:34:50 +0200 (Di, 21 Sep 2010) $
 * 
 */
$mieten = new mietkonto ();
echo "<div class=\"navi_leiste2\">";
$mieten->erstelle_formular ( "Hauptmen� -> H�user...", NULL );
echo "<a href=\"?daten=haus_raus&haus_raus=haus_kurz\">H�userliste</a>&nbsp; ";
echo "<a href=\"?formular=haus&daten_rein=haus_neu\">Haus anlegen</a>&nbsp; ";
echo "<a href=\"?daten=haus_raus&haus_raus=haus_aendern\">Haus �ndern</a>&nbsp; ";
$mieten->ende_formular ();
echo "</div>";
?>
