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
 * @filesource   $HeadURL: http://192.168.2.52/svn/berlussimo_1/tags/02.11.2010 - Downloadversion 0.27/classes/haus.php $
 * @version      $Revision: 6 $
 * @modifiedby   $LastChangedBy: sivac $
 * @lastmodified $Date: 2010-09-21 10:34:50 +0200 (Di, 21 Sep 2010) $
 * 
 */
include_once("berlussimo_class.php");

$objekt_id = $_REQUEST[objekt_id];

if(isset($objekt_id)){
haeuser_auflisten($objekt_id);
}

function haeuser_auflisten($objekt_id){
	$objekt_instanz = new objekt;
	$alle_haeuser_arr = $objekt_instanz->liste_haeuser_objekt($objekt_id);	
	$anzahl_haeuser = $objekt_instanz->anzahl_haeuser_objekt($objekt_id);
	$anzahl_haeuser =$objekt_instanz->anzahl_haeuser;
	$seiten_anzahl = $objekt_instanz->seiten_anzahl;
	$zeilen_pro_seite = $objekt_instanz->zeilen_pro_seite;
	echo "Seiten $seiten_anzahl";	
	$objekt_name = $objekt_instanz->get_objekt_name($objekt_id);
	$objekt_name = $objekt_instanz->objekt_name;	
	echo $objekt_name;
	if(is_array($alle_haeuser_arr)){
	echo "<table>";
	echo "<tr><td>Nr.</td><td><a href=\"".$_SERVER[PHP_SELF]."?sortby=HAUS_ID\">HAUS ID</td><td>STRASSE NR</td><td>PLZ</td></tr>";
	for($a=0;$a<$zeilen_pro_seite;$a++){
	$reihe = $a+1;
	echo "<tr><td>".$reihe."</td<td>".$alle_haeuser_arr[$a][HAUS_ID]."</td><td>".$alle_haeuser_arr[$a][HAUS_STRASSE]."".$alle_haeuser_arr[$a][HAUS_NUMMER]."</td><td>".$alle_haeuser_arr[$a][HAUS_PLZ]."</td></tr>";
	
	}
	echo "</table>";
	}
	echo "<pre>";
	print_r($alle_haeuser_arr);
	echo "</pre>";
	echo  $objekt_instanz->navi_links();
	#echo $links;
}

?>