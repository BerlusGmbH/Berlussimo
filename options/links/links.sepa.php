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


$mieten = new mietkonto;
echo "<div class=\"navi_leiste2\">"; 
$mieten->erstelle_formular("Hauptmenü -> SEPA...", NULL);
#echo "<a href=\"?daten=sepa\">Alle Mandate</a>&nbsp;";
echo "<a href=\"?daten=sepa&option=mandat_mieter_neu\">Neues Mietermandat</a>&nbsp;|";
echo "<a href=\"?daten=sepa&option=mandate_mieter_kurz\">Mieter Stammdaten</a>&nbsp;";
echo "<a href=\"?daten=sepa&option=mandate_mieter\">Mieter SEPA-LS</a>&nbsp;";
echo "<a href=\"?daten=sepa&option=ls_auto_buchen\">LS-Autobuchen</a>&nbsp;";
echo "<a href=\"?daten=sepa&option=mandate_rechnungen\">Rechnungen Mandate</a>&nbsp;|";
echo "<a href=\"?daten=sepa&option=mandate_hausgeld\">Hausgeld Mandate</a>&nbsp;";
echo "<a href=\"?daten=sepa&option=mandat_hausgeld_neu\">Neues Hausgeldmandat</a>&nbsp;|";
#echo "<a href=\"?daten=sepa&option=test_ls\">Test LS</a>&nbsp;";
echo "<a href=\"?daten=sepa&option=re_zahlen\">RE zahlen</a>&nbsp;";
echo "<a href=\"?daten=sepa&option=ra_zahlen\">RA zahlen</a>&nbsp;";
echo "<a href=\"?daten=sepa&option=sammler_anzeigen\">SEPA-Ü-Sammler</a>&nbsp;";
echo "<a href=\"?daten=sepa&option=sammel_ue\">Sammelüberweisung</a>&nbsp;";
echo "<a href=\"?daten=sepa&option=sammel_ue_IBAN\">Sammelüberweisung IBAN</a>&nbsp;";
echo "<a href=\"?daten=sepa&option=sepa_files\">SEPA-DATEIEN</a>&nbsp;";
echo "<a href=\"?daten=sepa&option=sepa_files_fremd\">SEPA-DATEIEN FREMDKONTO</a>&nbsp;";
#echo "<a href=\"?daten=sepa&option=sepa\">SEPA TEST</a>&nbsp;";
#echo "<a href=\"?daten=sepa&option=mandate\">Mandate</a>&nbsp;";
$mieten->ende_formular();

echo "</div>";

?>