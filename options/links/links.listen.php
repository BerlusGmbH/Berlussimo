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
 * @filesource   $HeadURL$
 * @version      $Revision$
 * @modifiedby   $LastChangedBy$
 * @lastmodified $Date$
 * 
 */
echo "<div class=\"navi_leiste2\">";
erstelle_abschnitt("Listen");
echo "<a href=\"?daten=listen&option=mieterliste_aktuell&objekt_id=1\">Mieterliste Block II</a>&nbsp;";
echo "<a href=\"?daten=listen&option=mieterliste_aktuell&objekt_id=2\">Mieterliste Block III</a>&nbsp;";
echo "<a href=\"?daten=listen&option=mieterliste_aktuell&objekt_id=3\">Mieterliste Block V</a>&nbsp;";
echo "<a href=\"?daten=listen&option=mieterliste_aktuell&objekt_id=40\">Mieterliste DW (Block E)</a>&nbsp;";
echo "<a href=\"?daten=listen&option=income_report\">Income report</a>&nbsp;";
echo "<a href=\"?daten=listen&option=inspiration_sepa\">Inspiration SEPA</a>&nbsp;";
echo "<a href=\"?daten=listen&option=sammler_anzeigen\">SEPA-Sammler</a>&nbsp;";
echo "<a href=\"?daten=listen&option=profil_neu\">Profil NEU</a>&nbsp;";
echo "<a href=\"?daten=listen&option=profil_liste\">Profil wählen</a>&nbsp;";
ende_abschnitt();
echo "</div>";