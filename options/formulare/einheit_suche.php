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
 * @filesource   $HeadURL: http://192.168.2.52/svn/berlussimo_1/tags/02.11.2010 - Downloadversion 0.27/options/formulare/einheit_suche.php $
 * @version      $Revision: 6 $
 * @modifiedby   $LastChangedBy: sivac $
 * @lastmodified $Date: 2010-09-21 10:34:50 +0200 (Di, 21 Sep 2010) $
 * 
 */
 

function einheit_suchform(){
if(isset($_REQUEST[suchfeld])){
	$suchbegriff = $_REQUEST[suchfeld];
#print_r($_POST);
#echo $suchbegriff;
}
echo "<table class=\"formular_tabelle\" width=100%>";
echo "<form method=POST action=\"$_SERVER[SCRIPT_URI]\">";
echo "<tr>";
echo "<td>Einheit: <input type=\"text\" name=\"suchfeld\" size=\"50\" value=\"$suchbegriff\"></td>";
echo "<td><input type=\"submit\" name=\"einheit_finden\" value=\"Finden\" class=\"buttons\"></td></tr>";
ende_formular();
echo "</table>";
}

einheit_suchform();

?>
