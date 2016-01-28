<?php
/* Created on / Erstellt am : 13.01.2011
*  Author: Sivac
*/

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
 * @filesource   $HeadURL$
 * @version      $Revision$
 * @modifiedby   $LastChangedBy$
 * @lastmodified $Date$
 * 
 */

$f = new formular;
echo "<div class=\"navi_leiste2\">"; 
$f->erstelle_formular("Hauptmen� -> Aufgaben und Projekte...", NULL);
echo "<a href=\"?daten=todo&option=offene_auftraege\"><b>Alle offenen Auftr�ge</b></a>|&nbsp;";
echo "<a href=\"?daten=todo&option=neue_auftraege\"><b>Alle Auftr�ge</b></a>|&nbsp;";
echo "<a href=\"?daten=todo&option=erledigte_auftraege\"><b>Alle erledigten Auftr�ge</b></a>|&nbsp;";
echo "<a href=\"?daten=todo\">Meine Projekte</a>|&nbsp;";
echo "<a href=\"?daten=todo&option=neues_projekt&typ=Benutzer\">Neuer Auftrag INT</a>|&nbsp;";
echo "<a href=\"?daten=todo&option=neues_projekt&typ=Partner\">Neuer Auftrag EXT</a>|&nbsp;";
echo "<a href=\"?daten=todo&option=erledigte_projekte\">Erledigt</a>|&nbsp;";
echo "<a href=\"?daten=todo&option=form_neue_baustelle\">Neue Baustelle</a>|&nbsp;";
echo "<a href=\"?daten=todo&option=baustellen_liste\">Baustellenliste</a>|&nbsp;";
echo "<a href=\"?daten=todo&option=baustellen_liste_inaktiv\">Baustellenliste inaktiv</a>|&nbsp;";
echo "<a href=\"../../../workspace/Mobile_ZE\">Mobile ZE</a>&nbsp;";
$f->ende_formular();
echo "</div>";


?>