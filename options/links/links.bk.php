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
 * @filesource   $HeadURL: http://192.168.2.52/svn/berlussimo_1/tags/02.11.2010 - Downloadversion 0.27/options/links/links.bk.php $
 * @version      $Revision: 6 $
 * @modifiedby   $LastChangedBy: sivac $
 * @lastmodified $Date: 2010-09-21 10:34:50 +0200 (Di, 21 Sep 2010) $
 * 
 */

$f = new formular;
echo "<div class=\"navi_leiste2\">"; 
$f->erstelle_formular("Hauptmenü -> Betriebskosten & Nebenkostenabrechnung...", NULL);
echo "<a href=\"?daten=bk&option=wirtschaftseinheiten\">Alle Wirtschaftseinheiten</a>&nbsp;";
echo "<a href=\"?daten=bk&option=wirtschaftseinheit_neu\">Neue Wirtschaftseinheit</a>&nbsp;";
echo "<a href=\"?daten=bk&option=profile\">Alle Profile</a>&nbsp;";
echo "<a href=\"?daten=bk&option=assistent\">Assistent</a>&nbsp;";
echo "<a href=\"?daten=bk&option=profil_reset\">Profil reset</a>&nbsp;";
echo "<a href=\"?daten=bk&option=zusammenfassung\">Zusammenfassung</a>&nbsp;";
echo "<a href=\"?daten=bk&option=pdf_ausgabe\">PDF-Ausgabe</a>&nbsp;";
echo "<a href=\"?daten=bk&option=anpassung_bk_hk\">BK/HK Anpassung</a>&nbsp;";
echo "<a href=\"?daten=bk&option=energie\"><b>Energiewerte</b></a>&nbsp;";
echo "<a href=\"?daten=bk&option=anpassung_bk_nk\"><b>HK-BK eingeben</b></a>&nbsp;";
echo "<a href=\"?daten=bk&option=serienbrief\">Serienbrief</a>&nbsp;";
echo "<a href=\"?daten=bk&option=serienbrief_vorlage_neu\">Neue Serienbriefvorlage</a>&nbsp;";
echo "<a href=\"?daten=bk&option=form_profil_kopieren\">Profile kopieren</a>&nbsp;";
$f->ende_formular();

echo "</div>";

?>
