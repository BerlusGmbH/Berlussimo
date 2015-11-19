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
 * @filesource   $HeadURL: http://192.168.2.52/svn/berlussimo_1/tags/02.11.2010 - Downloadversion 0.27/options/modules/ueberweisung.php $
 * @version      $Revision: 15 $
 * @modifiedby   $LastChangedBy: sivac $
 * @lastmodified $Date: 2011-07-07 10:41:33 +0200 (Do, 07 Jul 2011) $
 * 
 */

/*Allgemeine Funktionsdatei laden*/
include_once("includes/allgemeine_funktionen.php");

/*Überprüfen ob Benutzer Zugriff auf das Modul hat*/
if(!check_user_mod($_SESSION['benutzer_id'], 'ueberweisung')){
	echo '<script type="text/javascript">';
	echo "alert('Keine Berechtigung')";
	echo '</script>';	
	die();
}

/*Klasse "formular" für Formularerstellung laden*/
include_once("classes/class_formular.php");

/*Modulabhängige Dateien d.h. Links und eigene Klasse*/
include_once("options/links/links.ueberweisung.php");
include_once("classes/class_ueberweisung.php");


$option = $_REQUEST["option"];

/*Optionsschalter*/
switch($option) {

default:
$u = new ueberweisung;
$u->dtaus_uebersicht();

/*UPDATE RECHNUNGEN_POSITIONEN AS t1
JOIN RECHNUNGEN_POSITIONEN AS t2 ON t1.`RECHNUNGEN_POS_DAT` = t2.`RECHNUNGEN_POS_DAT`
SET t1.RABATT_SATZ = t2.RABATT_SATZ_NEU WHERE t1.RABATT_SATZ != t2.RABATT_SATZ_NEU*/
#$u->testdtaus();
#sel_dec();

break;

case "manuell":
echo "MANUELL MASKE";
break;


case "re_zahlen":
if(empty($_SESSION['geldkonto_id'])){
	hinweis_ausgeben("Bitte Geldkonto auswählen!");
}else{
	$g = new geldkonto_info;
	$g->geld_konto_details($_SESSION['geldkonto_id']);
	echo "<b>Ausgewähltes Konto $g->geldkonto_bezeichnung_kurz</b><br>";
}

	if(isset($_REQUEST['partner_wechseln'])){
  	unset($_SESSION['partner_id']);
   	}
   	if(isset($_REQUEST['partner_id'])){
  $_SESSION['partner_id'] = $_REQUEST['partner_id'];
   	}
   	$r = new rechnungen;
    $p = new partner;
    $link = "?daten=ueberweisung&option=re_zahlen";
    $partner_id = $_SESSION['partner_id'];
    
    if(isset($_REQUEST['monat']) && isset($_REQUEST['jahr'])){
    	if($_REQUEST['monat']!= 'alle'){
    	$_SESSION['monat'] = sprintf('%02d',$_REQUEST['monat']);
    	}else{
    	$_SESSION['monat'] = $_REQUEST['monat']; 
    	}
 	$_SESSION['jahr'] = $_REQUEST['jahr'];    	
    }
    
    if(empty($partner_id)){
    $p->partner_auswahl($link); 
    }
    else{
    #$p->partner_auswahl($link);
    $monat = $_SESSION['monat'];
    $jahr = $_SESSION['jahr'];
    
    if(empty($monat) OR empty($jahr)){
    $monat = date("m");
    $jahr = date("Y");	
    }
    #$belegnr = $_REQUEST['belegnr'];
    if(isset($_REQUEST['belegnr'])){
    $belegnr = $_REQUEST['belegnr'];
    }
    if(!isset($belegnr)){
     $r->rechnungseingangsbuch_kurz_zahlung('Partner', $partner_id, $monat, $jahr, 'Rechnung');
    }else{
    	$u = new ueberweisung;
    	$u-> form_rechnung_dtaus($belegnr);
    }
    }
break;


case "ueberweisung_dtaus":
$a_konto_id = $_POST['a_konto_id'];
$e_konto_id = $_POST['e_konto_id'];
$betrag =  $_POST['betrag'];
$betrags_art =  $_POST['betrags_art'];
$vzweck1 = $_POST['vzweck1'];
$vzweck2 = $_POST['vzweck2'];
$vzweck3 = $_POST['vzweck3'];
$bezugstab =  $_POST['bezugstab'];
$bezugsid =  $_POST['bezugsid'];
$buchungstext = $_POST['buchungstext'];

if(!$betrags_art){
	echo "Zu zahlenden Betrag wählen";
}else{

#echo "$a_konto_id $e_konto_id $betrag $betrags_art $vzweck1 $vzweck2 $vzweck3 $bezugstab $bezugsid $buchungstext";
$u = new ueberweisung;
$u->zahlung2dtaus($a_konto_id, $e_konto_id, $betrag, $betrags_art, $vzweck1, $vzweck2, $vzweck3, $bezugstab, $bezugsid, $buchungstext, 'VOLL');
hinweis_ausgeben("Sie werden in 1 Sekunde weitergeleitet");
weiterleiten_in_sec("?daten=ueberweisung&option=re_zahlen", 1);
}
break;

case "u_dtaus_erstellen":
$g_konto_id = $_REQUEST[konto_id];
if(!$g_konto_id){
	fehlermeldung_ausgeben("KEIN KONTO AUSGEWÄHLT");
}else{
$u = new ueberweisung;
$u->dtaus_von_konto_erstellen($g_konto_id);
	
}
break;


case "dtaus_dateien":
$u = new ueberweisung;
if(empty($_SESSION['geldkonto_id'])){
	echo "GELDKONTO AUSWÄHLEN";
}else{
	$g = new geldkonto_info;
	$g->geld_konto_details($_SESSION['geldkonto_id']);
	echo "<b>Ausgewähltes Konto $g->geldkonto_bezeichnung_kurz</b><br>";
}
$u->dtaus_dateien_anzeigen($_SESSION['geldkonto_id']);
break;



case "dtaus_ansicht":
$dtaus_id=$_REQUEST['dtaus_id'];
if(!$dtaus_id){
	echo "DTAUS DATEI AUSWÄHLEN";
}else{
	$u = new ueberweisung;
	$u->dtaus_datei_uebersicht($dtaus_id);
}
break;

case "dtaus_ansicht_pdf":
$dtaus_id=$_REQUEST['dtaus_id'];
if(!$dtaus_id){
	echo "DTAUS DATEI AUSWÄHLEN";
}else{
	$u = new ueberweisung;
	$u->pdf_dtaus_datei_uebersicht($dtaus_id);
}
break;


case "dtaus_buchen":
$dtaus_id=$_REQUEST[dtaus_id];
if(!$dtaus_id){
	echo "DTAUS DATEI AUSWÄHLEN";
}else{
	$u = new ueberweisung;
	$u->form_dtaus_datei_buchen($dtaus_id);
}
break;

case "autobuchen":
if(!empty($_REQUEST['dtaus_id']) && !empty($_REQUEST['datum']) && !empty($_REQUEST['kontoauszugsnr'])){
	echo '<pre>';
	print_r($_REQUEST);
	$dtaus_id = $_REQUEST['dtaus_id'];
	$datum = date_german2mysql($_REQUEST['datum']);
	$kto_auszugsnr = $_REQUEST['kontoauszugsnr'];
	$u = new ueberweisung;
	$u->autobuchen_zahlung($dtaus_id, $datum, $kto_auszugsnr);
}	else{
	echo "DTAUS UNVOLLSTÄNDIG error 5875858";
}
break;


case "manuelle_ueberweisung":
$u = new ueberweisung;
$u->form_ueberweisung_manuell();
break;


case "ue_send":
echo '<pre>';
print_r($_POST);
print_r($_SESSION);
if(empty($_SESSION[geldkonto_id]) or empty($_SESSION[partner_id])){
	die('ABBRUCH - Geldkonto und Partner wählen / Fehler 34234xx');
}else{
	if(!empty($_POST[geld_konto]) && !empty($_POST[betrag]) && !empty($_POST[vzweck1])){
		$u = new ueberweisung;
		$e_konto_id = $_POST[geld_konto];
		$betrag = $_POST[betrag];
		$vzweck1 = $_POST[vzweck1];
		$vzweck2 = $_POST[vzweck2];
		$vzweck3 = $_POST[vzweck3];
		$u->zahlung2dtaus($_SESSION[geldkonto_id], $e_konto_id, $betrag, '', $vzweck1, $vzweck2, $vzweck3, 'MANUELL', null, "$vzweck1, $vzweck2, $vzweck3", '');	
		weiterleiten_in_sec('?daten=ueberweisung&option=manuelle_ueberweisung',2);
	}else{
		"Daten unvollständig, Empfängerkontonummer, BLZ, VZWECK UND BETRAG prüfen";
	}
}


break;


}//end switch





		
?>
