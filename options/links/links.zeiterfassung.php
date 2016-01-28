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
 * @filesource   $HeadURL: http://192.168.2.52/svn/berlussimo_1/tags/02.11.2010 - Downloadversion 0.27/options/links/links.zeiterfassung.php $
 * @version      $Revision: 15 $
 * @modifiedby   $LastChangedBy: sivac $
 * @lastmodified $Date: 2011-07-07 10:41:33 +0200 (Do, 07 Jul 2011) $
 * 
 */

$f = new formular;
echo "<div class=\"navi_leiste2\">"; 
$f->erstelle_formular("Hauptmenü -> Zeiterfassung...", NULL);

echo "<a href=\"?daten=zeiterfassung&option=eigene_zettel\">Eigene Zettel</a>&nbsp;";
echo "<a href=\"?daten=zeiterfassung&option=neuer_zettel\">Neuer Zettel</a>&nbsp;";
#echo "<a href=\"?daten=zeiterfassung&option=stunden\">Stunden</a>&nbsp;";
#echo "<a href=\"?daten=zeiterfassung&option=einheitenliste\">Einheitenliste</a>";
$f->ende_formular();

echo "</div>";

?>
