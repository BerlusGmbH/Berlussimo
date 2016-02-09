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
 * @filesource   $HeadURL: http://192.168.2.52/svn/berlussimo_1/tags/02.11.2010 - Downloadversion 0.27/options/links/links.statistik.php $
 * @version      $Revision: 15 $
 * @modifiedby   $LastChangedBy: sivac $
 * @lastmodified $Date: 2011-07-07 10:41:33 +0200 (Do, 07 Jul 2011) $
 * 
 */
$form = new formular ();

$form->fieldset ( "Statistiken", 'statistiken_links' );
echo "<div class=\"navi_leiste2\">&nbsp;&nbsp;";
echo "<a href=\"?daten=statistik&option=bau_stat_menu\">BAU STAT</a>&nbsp;";
echo "<a href=\"?daten=statistik&option=leer_vermietet_jahr\">LEERSTAND/VERMIETET</a>&nbsp;";
echo "<a href=\"?daten=statistik&option=stellplaetze\">Stellplaetze (E)</a>&nbsp;";
echo "<a href=\"?daten=statistik&option=garage\">Garage (GBN)</a>&nbsp;";
echo "<a href=\"?daten=statistik&option=sollmieten_aktuell\">Sollmieten aktuell inkl. Leerstand</a>&nbsp;";
echo "<a href=\"?daten=statistik&option=sollmieten_haeuser&pdf\">Sollmieten Häusergruppen</a>&nbsp;";
echo "<a href=\"?daten=statistik&option=me_k\">Kosten/Einnahmen Diag.</a>&nbsp;";
echo "<a href=\"?daten=statistik&option=baustelle\">Baustellen</a>&nbsp;";
echo "<a href=\"?daten=zeiterfassung&option=stunden\">Stundenübersicht</a>&nbsp;";
echo "<a href=\"?daten=statistik&option=fenster\">Fensterübersicht</a>&nbsp;";
echo "<a href=\"?daten=statistik&option=leer_haus_stat\">Statistik im Haus 5J</a>&nbsp;";
echo "<a href=\"?daten=leerstand&option=kontrolle_preise\">Vermietungspreise</a>&nbsp;";
echo "</div>";
$form->fieldset_ende ();

?>
