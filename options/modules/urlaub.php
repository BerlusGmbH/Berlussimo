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
 * @filesource   $HeadURL: http://192.168.2.52/svn/berlussimo_1/tags/02.11.2010 - Downloadversion 0.27/options/modules/urlaub.php $
 * @version      $Revision: 6 $
 * @modifiedby   $LastChangedBy: sivac $
 * @lastmodified $Date: 2010-09-21 10:34:50 +0200 (Di, 21 Sep 2010) $
 * 
 */

/*Allgemeine Funktionsdatei laden*/
include_once("includes/allgemeine_funktionen.php");

/*Überprüfen ob Benutzer Zugriff auf das Modul hat*/
if(!isset($_SESSION['benutzer_id']) OR !check_user_mod($_SESSION['benutzer_id'], 'urlaub')){
	echo '<script type="text/javascript">';
	echo "alert('Keine Berechtigung')";
	echo '</script>';	
	die();
}

/*Klasse "formular" für Formularerstellung laden*/
include_once("classes/class_formular.php");

/*Modulabhängige Dateien d.h. Links und eigene Klasse*/
include_once("options/links/links.urlaub.php");
include_once("classes/class_urlaub.php");

if(!empty($_REQUEST["option"])){
$option = $_REQUEST["option"];
}else{
$option = 'default';
}

/*Optionsschalter*/
switch($option) {

#case "uebersicht":
default:
if(!empty($_REQUEST['jahr'])){
$jahr = $_REQUEST['jahr'];
}
if(!isset($jahr)){
$jahr = date("Y");
}
$vorjahr = $jahr -1;
$nachjahr=$jahr+1;
$link_vorjahr = "<a href=\"?daten=urlaub&option=uebersicht&jahr=$vorjahr\">Übersicht $vorjahr</a>";
$link_nachjahr = "<a href=\"?daten=urlaub&option=uebersicht&jahr=$nachjahr\">Übersicht $nachjahr</a>";
$u = new urlaub;
echo "$link_vorjahr $link_nachjahr<br>";
$u->jahresuebersicht_anzeigen($jahr);
break;


case "uebersicht_pdf":
$u = new urlaub;
$jahr = $_REQUEST['jahr'];
if(!$jahr){
	$jahr = date("Y");
}
$u->jahresuebersicht_alle_pdf($jahr);
break;



case "urlaubsantrag":
$u = new urlaub;
$benutzer_id = $_REQUEST['benutzer_id'];
if(empty($benutzer_id)){
	$benutzer_id = $_SESSION[benutzer_id];
}
$u->form_urlaubsantrag($benutzer_id);
break;

case "urlaubsantrag_check":

$u = new urlaub;
$benutzer_id = $_REQUEST[benutzer_id];
$datum_a = date_german2mysql($_REQUEST[u_vom]);
$datum_e = date_german2mysql($_REQUEST[u_bis]);
$datum_a_arr = explode("-", $datum_a);
$datum_e_arr = explode("-", $datum_e);
$a_jahr = $datum_a_arr[0];
$e_jahr = $datum_e_arr[0];
##echo "$a_jahr $e_jahr";
if($e_jahr < $a_jahr){
	fehlermeldung_ausgeben("Enddatum kleiner als Anfangsdatum, bitte neu eingeben!");
	die();
}
if($e_jahr>$a_jahr){
	fehlermeldung_ausgeben("Urlaub erstreckt sich über ein Jahr, bitte nur Urlaub innerhalb eines Kalenderjahres eingeben.");
	die();
}
else{
$art = $_REQUEST['art'];
$u->tage_arr($benutzer_id, $datum_a, $datum_e, $art);
}
weiterleiten_in_sec("?daten=urlaub&option=urlaubsantrag&benutzer_id=$benutzer_id", 1);
break;

case "jahresansicht":
$u = new urlaub;
$benutzer_id = $_REQUEST['benutzer_id'];
$jahr = $_REQUEST['jahr'];
if(!empty($benutzer_id) && !empty($jahr)){
$u->jahres_ansicht($benutzer_id,$jahr);
}
break;

case "jahresansicht_pdf":
$u = new urlaub;
$benutzer_id = $_REQUEST['benutzer_id'];
$jahr = $_REQUEST['jahr'];
if(!empty($benutzer_id) && !empty($jahr)){
$u->jahres_ansicht_pdf($benutzer_id,$jahr);
}
break;


case "urlaubstag_loeschen":
$u = new urlaub;
$dat = $_REQUEST[u_dat];
$benutzer_id = $_REQUEST['benutzer_id'];
$jahr = $_REQUEST['jahr'];
if(!empty($dat)){
$u->urlaubstag_loeschen($dat);
weiterleiten_in_sec("?daten=urlaub&option=jahresansicht&jahr=2009&benutzer_id=$benutzer_id&jahr=$jahr",1);
}else{
	echo "Urlaubstag auswählen";
}
break;

case "urlaubstag_loeschen_js":
$u = new urlaub;
$benutzer_id = $_REQUEST['benutzer_id'];
$datum = date_german2mysql($_REQUEST['datum']);
$u->urlaubstag_loeschen_datum($benutzer_id,$datum);
break;


case "monatsansicht":
$u = new urlaub;
if(!empty($_REQUEST['jahr'])){
$jahr = $_REQUEST['jahr'];
}
if(!empty($_REQUEST['monat'])){
$monat = $_REQUEST['monat'];
}
if(!isset($monat)){
	$monat=date("m");
}
if(!isset($jahr)){
	$jahr=date("Y");
}
$u->monatsansicht($monat, $jahr);
break;


case "monatsansicht_pdf":
$u = new urlaub;
$jahr = $_REQUEST['jahr'];
$monat = $_REQUEST['monat'];
if(empty($monat)){
	$monat=date("m");
}
if(empty($jahr)){
	$jahr=date("Y");
}
$u->monatsansicht_pdf($monat, $jahr);
break;


case "monatsansicht_pdf_mehrere":
$u = new urlaub;
$u->monatsansicht_pdf_mehrere(1,12, 2010);
break;



case "monatsansicht_jahr":
$u = new urlaub;
$jahr = $_REQUEST['jahr'];


if(empty($jahr)){
	$jahr=date("Y");
}
$vorjahr = $jahr-1;
$nachjahr = $jahr+1;
echo "<a href=\"?daten=urlaub&option=monatsansicht_jahr&jahr=$vorjahr\"> Übersicht $vorjahr </a> |  ";
echo "<a href=\"?daten=urlaub&option=monatsansicht_jahr&jahr=$nachjahr\"> Übersicht $nachjahr </a> ";
for($a=1;$a<=12;$a++){
$u->monatsansicht($a, $jahr);
}
break;


case "urlaubsplan_jahr":
$u = new urlaub;
$jahr = $_REQUEST['jahr'];
if(empty($jahr)){
	$jahr=date("Y");
}
$u->monatsansicht_pdf_mehrere(1,12, $jahr);
break;



case "test":
include_once('classes/class_wartungen.php');
$w = new wartung;
$w->test(1);

#$u = new urlaub;
 #$u->rest_tage(2007, 1);
 #$u->rest_tage(2008, 1);
 # $u->rest_tage(2009, 1);
#$u->zinsen(954.14,1.8);
#$u->monatsansicht(12, 2009);
#$u->zinsen(893.90,4);
#$u->zinsen(372.70,0.005);
#echo "Anzahl der Tage bis zum Monatsende: ".(date("t") - date("j"));
#include_once('classes/class_kautionen.php');
#$k = new kautionen;
 #datum_bis = '2010-04-31';
 
 
 #$k->kautionsberechnung('Mietvertrag', '220', '2010-12-31', 0.005,25,5.5);
 #echo "<br>";
 #$k->kautionsberechnung('Mietvertrag', '221', '2010-01-31', 0.005,25,5.5);
 #echo "<br>";
 #$k->kautionsberechnung('Mietvertrag', '221', '2010-02-28', 0.005,25,5.5);
 
 
 #$k->form_hochrechnung_mv(221);

 #kautionsberechnung($kostentraeger_typ, $kostentraeger_id, $datum_bis, $zins_pj, $kap_prozent, $soli_prozent)
#$k->zinstage();

break;

case "hochrechnung_mv":
include_once('classes/class_kautionen.php');
$k = new kautionen;
$datum_bis = date_german2mysql($_POST[datum_bis]);
$mietvertrag_id = $_POST[mietvertrag_id];
$k->kautionsberechnung('Mietvertrag', $mietvertrag_id, $datum_bis, 0.005,25,5.5);

break;


}//end switch
?>
