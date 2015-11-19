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
 * @filesource   $HeadURL: http://192.168.2.52/svn/berlussimo_1/tags/02.11.2010 - Downloadversion 0.27/options/modules/kautionen.php $
 * @version      $Revision: 15 $
 * @modifiedby   $LastChangedBy: sivac $
 * @lastmodified $Date: 2011-07-07 10:41:33 +0200 (Do, 07 Jul 2011) $
 * 
 */

/*Allgemeine Funktionsdatei laden*/
include_once("includes/allgemeine_funktionen.php");

/*Überprüfen ob Benutzer Zugriff auf das Modul hat*/
if(!check_user_mod($_SESSION[benutzer_id], 'kautionen')){
	echo '<script type="text/javascript">';
	echo "alert('Keine Berechtigung')";
	echo '</script>';	
	die();
}


/*Klasse "formular" für Formularerstellung laden*/
include_once("classes/class_formular.php");

/*Modulabhängige Dateien d.h. Links und eigene Klasse*/
include_once("options/links/links.kautionen.php");
include_once("classes/class_kautionen.php");

$option = $_REQUEST["option"];

/*Optionsschalter*/
switch($option) {


/*Kautionseinzahlung*/
case "kautionen_buchen":
	if(!empty($_REQUEST[mietvertrag_id])){
	$mv_id = $_REQUEST[mietvertrag_id];
	$k = new kautionen;
	$k->form_kautionsbuchung_mieter($mv_id);
	}else{
		echo "Mietvertrag auswählen";
	}
break;


case "kaution_gesendet":
	if(!empty($_REQUEST[mietvertrag_id]) && !empty($_REQUEST[datum]) && !empty($_REQUEST[betrag])  && !empty($_REQUEST[text])  ){
	$mv_id = $_REQUEST[mietvertrag_id];
	$betrag = nummer_komma2punkt($_REQUEST[betrag]);
	$datum = $_REQUEST['datum'];
	$datum = date_german2mysql($datum);
	$text = $_REQUEST[text];
	$k = new kautionen;
	$k->kaution_speichern($datum, 'MIETVERTRAG', $mv_id, $betrag, $text, '1000');
	}else{
		echo "Mietvertrag auswählen";
	}
break;


case "hochrechner":
$k = new kautionen;
	if(!empty($_REQUEST[mietvertrag_id])){
	$mv_id = $_REQUEST[mietvertrag_id];
	$k->form_hochrechnung_mv($mv_id);
	}else{
		echo "Mietvertrag auswählen";
	}
break;

case "hochrechnung_mv":
$k = new kautionen;
$datum_bis = date_german2mysql($_POST['datum_bis']);
$mietvertrag_id = $_POST['mietvertrag_id'];
$k->kautionsberechnung('Mietvertrag', $mietvertrag_id, $datum_bis, 0.0025,25,5.5);
$k->kautionsberechnung_2('250', '2014-01-06', '2014-04-30', 0.0025,25,5.5);
$k->kautionsberechnung_2('250', '2014-01-21', '2014-04-30', 0.0025,25,5.5);
$k->kautionsberechnung_2('500', '2014-02-17', '2014-04-30', 0.0025,25,5.5);
$k->kautionsberechnung_2('250.15', '2014-04-30', '2014-07-31', 0.0015,25,5.5);
$k->kautionsberechnung_2('250.13', '2014-04-30', '2014-07-31', 0.0015,25,5.5);
$k->kautionsberechnung_2('500.19', '2014-04-30', '2014-07-31', 0.0015,25,5.5);
$k->kautionsberechnung_2('1000.46', '2014-04-30', '2014-07-31', 0.0015,25,5.5);

#$k->kautionsberechnung_2('536.929', '2014-05-01', '2014-08-30', 0.0015,25,5.5);
break;


case "hochrechner_pdf":
$k = new kautionen;
if(!empty($_REQUEST[datum_bis]) && !empty( $_REQUEST[mietvertrag_id])){
$datum_bis = date_german2mysql($_REQUEST[datum_bis]);
$mietvertrag_id = $_REQUEST[mietvertrag_id];
$k->kautionsberechnung_pdf('Mietvertrag', $mietvertrag_id, $datum_bis, 0.0025,25,5.5);
}else{
echo "Mietvertrag und Auszahlungsdatum eingeben";
}
break;


case "kontohochrechnung":
$k = new kautionen;

if(empty($_REQUEST[datum_bis])){
	$datum_bis = date("Y")."-12-31";
}else{
	$datum_bis = date_german2mysql($_REQUEST[datum_bis]);
}

if(!empty($_REQUEST[tag]) && !empty($_REQUEST[monat]) && !empty($_REQUEST[jahr])){
	
}

$k->kontohochrechnung($datum_bis, 0.0025,25,5.5);
#$k->kautionsberechnung('Mietvertrag', $mietvertrag_id, $datum_bis, 0.005,25,5.5);
break;

/*Mieter ohne Kautionen*/
case "mv_ohne_k":
$k = new kautionen;
if(!empty($_SESSION[geldkonto_id])){
	$k->mieter_ohne_kaution_anzeigen($_SESSION[geldkonto_id], '1000');
}else{
hinweis_ausgeben('Kautionskonto wählen');	
}
break;

	
}


?>
