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
 * @filesource   $HeadURL: http://192.168.2.52/svn/berlussimo_1/tags/02.11.2010 - Downloadversion 0.27/options/links/links.katalog.php $
 * @version      $Revision: 6 $
 * @modifiedby   $LastChangedBy: sivac $
 * @lastmodified $Date: 2010-09-21 10:34:50 +0200 (Di, 21 Sep 2010) $
 * 
 */
$f = new formular ();
echo "<div class=\"navi_leiste2\">";
$f->erstelle_formular ( "Hauptmenü -> Artikel- und Leistungskatalog...", NULL );

echo "<a href=\"?daten=katalog&option=katalog_anzeigen\">Artikel & Leistungen</a>&nbsp;";
echo "<a href=\"?daten=katalog&option=preisentwicklung\">Preisentwicklung</a>&nbsp;";
// echo "<a href=\"?daten=buchen&option=zahlbetrag_buchen\">Kosten buchen</a>&nbsp;";
echo "<a href=\"?daten=katalog&option=artikelsuche\">Artikelsuche</a>&nbsp;<b>|</b>&nbsp;";
echo "<a href=\"?daten=katalog&option=artikelsuche_freitext\">Artikelsuche Freitext</a>&nbsp;<b>|</b>&nbsp;";
echo "<a href=\"?daten=katalog&option=meist_gekauft\">Meistgekauft</a>&nbsp;<b>|</b>&nbsp;";
echo "<a href=\"?daten=katalog&option=zuletzt_gekauft\">Zuletzt gekauft</a>&nbsp;<b>|</b>&nbsp;";
$f->ende_formular ();

echo "</div>";

?>
