<?php
/*
 * Created on / Erstellt am : 18.02.2014
 * Author: Sivac
 */
/**
 * BERLUSSIMO
 *
 * Hausverwaltungssoftware
 *
 *
 * @copyright Copyright (c) 2010, Berlus GmbH, Fontanestr. 1, 14193 Berlin
 * @link http://www.berlus.de
 * @author Sanel Sivac & Wolfgang Wehrheim
 *         @contact software(@)berlus.de
 * @license http://www.gnu.org/licenses/agpl.html AGPL Version 3
 *         
 * @filesource $HeadURL$
 * @version $Revision$
 *          @modifiedby $LastChangedBy$
 *          @lastmodified $Date$
 *         
 */
echo "<div class=\"navi_leiste2\">";
erstelle_abschnitt("Personalwesen");
echo "<a href=\"?daten=personal&option=lohn_gehalt_sepa\">Lohn- und Gehalt SEPA</a>&nbsp;";
echo "<a href=\"?daten=personal&option=kk\">KK</a>&nbsp;";
echo "<a href=\"?daten=personal&option=steuern\">Steuern</a>&nbsp;";
ende_formular();
echo "</div>";
