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
 * @filesource   $HeadURL: http://192.168.2.52/svn/berlussimo_1/tags/02.11.2010 - Downloadversion 0.27/options/modules/wartung.php $
 * @version      $Revision: 6 $
 * @modifiedby   $LastChangedBy: sivac $
 * @lastmodified $Date: 2010-09-21 10:34:50 +0200 (Di, 21 Sep 2010) $
 * 
 */

/*Allgemeine Funktionsdatei laden*/
include_once("includes/allgemeine_funktionen.php");

/*Überprüfen ob Benutzer Zugriff auf das Modul hat*/
if(!check_user_mod($_SESSION[benutzer_id], 'wartung')){
	echo '<script type="text/javascript">';
	echo "alert('Keine Berechtigung')";
	echo '</script>';	
	die();
}

/*Klasse "formular" für Formularerstellung laden*/
include_once("classes/class_formular.php");

/*Modulabhängige Dateien d.h. Links und eigene Klasse*/
/*Modulabhängige Dateien d.h. Links und eigene Klasse*/
include_once("options/links/links.wartung.php");
include_once("classes/class_wartungen.php");
if(!isset($_SESSION[plan_id])){
$w = new wartung;
$w->wartungsplan_auswahl();
}else{
echo "<a href=\"?daten=wartung&option=plan_auswahl\">Wartungsplan ändern</a><br><br>";	

}

$option = $_REQUEST["option"];

/*Optionsschalter*/
switch($option) {


default:
if(isset($_SESSION[plan_id])){
$w = new wartung;
$plan_id = $_SESSION[plan_id];
if($plan_id){
$w->test($plan_id);
}
}else{
hinweis_ausgeben("Wartungsplan auswählen");	
}
break;

case "geraet_hinzu":
$w = new wartung;
$w->form_geraete_erfassen();
break;


case "geraet_gesendet":
$bezeichnung = $_POST[bezeichnung];
$hersteller = $_POST[hersteller];
$baujahr = $_POST[baujahr];
$eingebaut = $_POST[eingebaut];
$kostentraeger_typ = $_POST[kostentraeger_typ];
$kostentraeger_bez = $_POST[kostentraeger_id];
$plan_id = $_POST[plan_id];
/*letzte wartung*/
$wartungstermin = $_POST[wartungstermin];
if(!empty($bezeichnung) && !empty($hersteller) && !empty($baujahr) && !empty($eingebaut) && !empty($kostentraeger_typ) && !empty($kostentraeger_bez) && !empty($plan_id)){
$w = new wartung;
$w->geraet_speichern($bezeichnung, $hersteller, $baujahr, $eingebaut, $kostentraeger_typ, $kostentraeger_bez, $plan_id);
weiterleiten_in_sec("?daten=wartung&option=geraet_hinzu", 1);
}else{
	echo "unvollständig, zurück";
}
break;

case "wplan_gesendet":
if(!empty($_POST[plan_id])){
$_SESSION[plan_id] = $_POST[plan_id];	
}
weiterleiten_in_sec("?daten=wartung", 0);
break;

case "plan_auswahl":
unset($_SESSION['plan_id']);

break;

case "geraeteliste":
if(isset($_SESSION[plan_id])){
$w = new wartung;
$w->geraete_uebersicht_alle($_SESSION[plan_id]);
}else{
hinweis_ausgeben("Wartungsplan auswählen");	
}
break;


case "wplan":
$w=new wartung;
$w-> list_plan(4);
#$w-> list_plan(2);
#$w->termine_anzeigen(1, 2, '2007-07-10', '2012-07-15');
#$w->termine_anzeigen_pdf(1, 2, '2007-07-10', '2012-07-15');
#$w->termine_anzeigen_pdf(1, 3, '2007-07-10', '2012-07-15');
break;


case "termin_neu":
if(!empty($_REQUEST[geraete_id]) && !empty($_REQUEST[plan_id])){
$geraete_id = $_REQUEST[geraete_id];
$plan_id =$_REQUEST[plan_id];
$w=new wartung;
$w->form_termin($geraete_id, $plan_id);
}else{
	echo "Gerät und Wartungsplan auswählen";
}
break;

case "wartungstermin":
print_req();
if(!empty($_REQUEST[geraete_id]) && !empty($_REQUEST[plan_id]) &&  !empty($_REQUEST[datum]) &&  !empty($_REQUEST[benutzer_id]) && !empty($_REQUEST[uhrzeit]) && !empty($_REQUEST[dauer])){
$geraete_id = $_REQUEST[geraete_id];
$plan_id =$_REQUEST[plan_id];
$datum =date_german2mysql($_REQUEST[datum]);
$zeit =$_REQUEST[uhrzeit];
$benutzer_id =$_REQUEST[benutzer_id];
$dauer =$_REQUEST[dauer];
weiterleiten("?daten=wartung&option=wplan");
	$w = new wartung;
	$w-> termin_speichern($benutzer_id, $plan_id, $datum, $zeit, $geraete_id, $dauer);
}else{
echo "Eingabe unvollständig";	
}
break;

case "ue":
	$w = new wartung;
	$w->wartungen_anstehend(1);
	$w->wartungen_anstehend(2);
	$w->wartungen_anstehend(3);
	$w->wartungen_anstehend(4);
break;


case "akkus":
	#echo "LALALA";
	$w = new wartung;
	$w->ausgabe(1,3,'PDF');
	break;


}//ende switch


?>
