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
 * @filesource   $HeadURL: http://192.168.2.52/svn/berlussimo_1/tags/02.11.2010 - Downloadversion 0.27/options/formulare/person_suche.php $
 * @version      $Revision: 6 $
 * @modifiedby   $LastChangedBy: sivac $
 * @lastmodified $Date: 2010-09-21 10:34:50 +0200 (Di, 21 Sep 2010) $
 * 
 */
#include("includes/formular_funktionen.php");

function person_suchform(){

if(isset($_REQUEST['suchfeld'])){
	$suchbegriff = $_REQUEST['suchfeld'];
#print_r($_POST);
#echo $suchbegriff;
}else{
	$suchbegriff ='';
}
echo "<table class=\"formular_tabelle\">";
#$act = $_SERVER['SCRIPT_URI'];
echo "<form method=\"POST\" >";
echo "<tr>";
echo "<td  width=50% align=left>Suchbegriff: <input type=\"text\" name=\"suchfeld\" size=\"50\" value=\"$suchbegriff\"></td>";
echo "<td width=30% align=left>suchen in:  <select name=\"suche_nach\">";
echo "<option value=\"Nachname\">Nachname</option>";
echo "<option value=\"Vorname\">Vorname</option>";
echo "</select></td>";
#echo "<td>Suchbegriff: <input type=\"text\" name=\"suchfeld\" size=\"50\" value=\"$suchbegriff\"></td><td>";
echo "<td width=20% align=left><input type=\"submit\" name=\"person_finden\" value=\"Finden\" class=\"buttons\"></td></tr>";
#erstelle_submit_button("suche_person", "Finden");
ende_formular();
echo "</table>";
}
person_suchform();

?>
