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
 * @filesource   $HeadURL: http://192.168.2.52/svn/berlussimo_1/tags/02.11.2010 - Downloadversion 0.27/options/links/links.partner.php $
 * @version      $Revision: 6 $
 * @modifiedby   $LastChangedBy: sivac $
 * @lastmodified $Date: 2010-09-21 10:34:50 +0200 (Di, 21 Sep 2010) $
 * 
 */
echo "<div class=\"navi_leiste2\">";
erstelle_abschnitt("Partner");
echo "<div class=\"navi_leiste2\">\n";
echo "<a href=\"?daten=partner&option=partner_liste\">Alle Partner</a>&nbsp;";
echo "<a href=\"?daten=partner&option=partner_erfassen\">Neuer Partner</a>&nbsp;";
echo "<a href=\"?daten=partner&option=partner_umsatz\">Umsatz Partner</a>&nbsp;";
echo "<a href=\"?daten=partner&option=serienbrief\">Serienbrief</a>&nbsp;";
ende_abschnitt();
echo "</div>\n";

?>
