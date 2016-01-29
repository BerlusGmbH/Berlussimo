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
 * @filesource   $HeadURL: http://192.168.2.52/svn/berlussimo_1/tags/02.11.2010 - Downloadversion 0.27/options/links/links.lager.php $
 * @version      $Revision: 15 $
 * @modifiedby   $LastChangedBy: sivac $
 * @lastmodified $Date: 2011-07-07 10:41:33 +0200 (Do, 07 Jul 2011) $
 * 
 */
$mieten = new mietkonto ();
echo "<div class=\"navi_leiste2\">";
$mieten->erstelle_formular ( "Hauptmen� -> Lager...", NULL );

// echo "<a href=\"?daten=lager\">Lager�bersicht</a>&nbsp;<b>|</b>&nbsp;";
echo "<a href=\"?daten=lager&option=lagerbestand\">Lagerbestand</a>&nbsp;<b>|</b>&nbsp;";
echo "<a href=\"?daten=lager&option=lagerbestand_bis_form\">Lagerbestand bis...</a>&nbsp;<b>|</b>&nbsp;";
echo "<a href=\"?daten=lager&option=re\">RE</a>&nbsp;<b>|</b>&nbsp;";
echo "<a href=\"?daten=lager&option=ra\">RA</a>&nbsp;<b>|</b>&nbsp;";
echo "<a href=\"?daten=lager&option=artikelsuche\">Artikelsuche</a>&nbsp;<b>|</b>&nbsp;";
$mieten->ende_formular ();

echo "</div>";
?>
