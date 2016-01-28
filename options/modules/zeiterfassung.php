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
 * @filesource   $HeadURL: http://192.168.2.52/svn/berlussimo_1/tags/02.11.2010 - Downloadversion 0.27/options/modules/zeiterfassung.php $
 * @version      $Revision: 15 $
 * @modifiedby   $LastChangedBy: sivac $
 * @lastmodified $Date: 2011-07-07 10:41:33 +0200 (Do, 07 Jul 2011) $
 * 
 */

/*Allgemeine Funktionsdatei laden*/
include_once("includes/allgemeine_funktionen.php");

/*Überprüfen ob Benutzer Zugriff auf das Modul hat*/
if(!check_user_mod($_SESSION['benutzer_id'], 'zeiterfassung')){
	echo '<script type="text/javascript">';
	echo "alert('Keine Berechtigung')";
	echo '</script>';	
	die();
}

/*Klasse "formular" für Formularerstellung laden*/
include_once("classes/class_formular.php");

/*Modulabhängige Dateien d.h. Links und eigene Klasse*/
include_once("options/links/links.zeiterfassung.php");
include_once("classes/class_zeiterfassung.php");



$option = $_REQUEST["option"];

/*Optionsschalter*/
switch($option) {

default:
echo "HAUPTSEITE ZEITERFASSUNG<br>";
#$benutzer_id = $_SESSION['benutzer_id'];
#echo "IHRE BENUTZER_ID lautet $benutzer_id"; 
break;


case "eigene_zettel":
$benutzer_id = $_SESSION['benutzer_id'];
$benutzer_name = $_SESSION['username'];
#echo "Sie sind $benutzer_name<br>";
$ze = new zeiterfassung;
$ze->eigene_stundenzettel_anzeigen();
break;

case "zettel_ansehen":
$benutzer_id = $_SESSION['benutzer_id'];
$benutzer_name = $_SESSION['username'];
$zettel_id = $_REQUEST['zettel_id'];
$ze = new zeiterfassung;
$ze->stundenzettel_anzeigen($zettel_id);
break;

case "neuer_zettel":
$benutzer_id = $_SESSION['benutzer_id'];
$benutzer_name = $_SESSION['username'];
$ze = new zeiterfassung;
$ze->stundenzettel_anlegen($benutzer_id);
break;


case "zettel_anlegen":
$benutzer_id = $_POST['benutzer_id'];
$beschreibung = $_POST['beschreibung'];
$ze = new zeiterfassung;
if(isset($benutzer_id) && isset($beschreibung)){
$ze->stundenzettel_speichern($benutzer_id, $beschreibung);
}else{
fehlermeldung_ausgeben("Bitte füllen Sie alle Felder aus");	
}
break;


case "zettel_eingabe":
$zettel_id = $_REQUEST['zettel_id'];
$ze = new zeiterfassung;
$ze->stundenzettel_erfassen($zettel_id);
break;

case "zettel_eingabe1":
$ze = new zeiterfassung;
$datum =$_POST['datum'];
$zettel_id = $_POST['zettel_id'];
$benutzer_id =$_POST['benutzer_id'];
$leistung_id = $_POST['leistung_id'];
$kostentraeger_typ = $_POST['kostentraeger_typ'];
$kostentraeger_bez = $_POST['kostentraeger_id'];
$dauer_min = $_POST['dauer_min'];
$leistungs_beschreibung = $_POST['leistungs_beschreibung'];
$hinweis = $_POST['hinweis'];
$beginn = $_POST['beginn'];
$ende = $_POST['ende'];


if(!empty($datum) && !empty($zettel_id) && !empty($benutzer_id) && !empty($kostentraeger_typ) && !empty($kostentraeger_bez) && !empty($beginn) && !empty($ende)){
	$_SESSION['beginn'] = $beginn;
	$_SESSION['ende'] = $ende;
	if($ende=='15:15'){
	unset($_SESSION['beginn']);
	unset($_SESSION['ende']);
	}
	$d= check_datum ($datum);
	if(!$d){
	fehlermeldung_ausgeben("DATUMSEINGABE FEHLERHAFT");
	die();
	}
	
	if(empty($leistung_id) && empty($leistungs_beschreibung)){
	echo "Wählen Sie bitte ein Leistung aus, oder geben Sie manuell Ihre Leistungsbeschreibung ein";	
	die();
	}
	if(empty($leistungs_beschreibung) && !empty($leistung_id)){
		$ze->zettel_pos_speichern($datum, $benutzer_id, $leistung_id, $zettel_id, $dauer_min, $kostentraeger_typ, $kostentraeger_bez, $hinweis, $beginn,$ende);
	}
	if(!empty($leistungs_beschreibung) && empty($leistung_id)){
	$ze->leistung_in_katalog($datum, $benutzer_id, $leistungs_beschreibung, $zettel_id, $dauer_min, $kostentraeger_typ, $kostentraeger_bez, $hinweis, $beginn,$ende);	
	}
	
	if(!empty($leistungs_beschreibung) && !empty($leistung_id)){
	echo "Entweder Leistung aussuchen oder Leistungsbeschreibung eintragen";
	}
}else{
	echo "EINGABE UNVOLLSTÄNDIG";
	print_req($_POST);
}
break;

case "loeschen":
$zettel_id = $_REQUEST[zettel_id];
$pos_id = $_REQUEST[pos_id];
if(!empty($zettel_id) && !empty($pos_id)){
$ze = new zeiterfassung;
$ze->pos_loeschen($zettel_id, $pos_id);
}else{
	hinweis_ausgeben("FEHLER BEIM LÖSCHEN");
	weiterleiten_in_sec("?daten=zeiterfassung&option=zettel_eingabe&zettel_id=$zettel_id", 2);
}
break;


case "aendern":
$zettel_id = $_REQUEST['zettel_id'];
$pos_id = $_REQUEST['pos_id'];
if(!empty($zettel_id) && !empty($pos_id)){
$ze = new zeiterfassung;
$ze->form_zeile_aendern($zettel_id, $pos_id);
}else{
	hinweis_ausgeben("FEHLER BEIM ÄNDERN");
	weiterleiten_in_sec("?daten=zeiterfassung&option=zettel_eingabe&zettel_id=$zettel_id", 2);
}
break;

case "zettel_zeile_aendern":
#print_req();
#die();
$ze = new zeiterfassung;
$datum =$_POST[datum];
$zettel_id = $_POST[zettel_id];
$pos_dat = $_POST[pos_dat];
$benutzer_id =$_POST[benutzer_id];
$leistung_id = $_POST[leistung_id];
$kostentraeger_typ = $_POST[kostentraeger_typ];
$kostentraeger_bez = $_POST[kostentraeger_id];
$dauer_min = $_POST[dauer_min];
$leistungs_beschreibung = $_POST[leistungs_beschreibung];
$hinweis = $_POST[hinweis];
$beginn = $_POST[beginn];
$ende = $_POST[ende];

if(!empty($datum) && !empty($zettel_id) && !empty($benutzer_id) && !empty($kostentraeger_typ) && !empty($kostentraeger_bez) && !empty($beginn) && !empty($ende)){
	
	$d= check_datum ($datum);
	if(!$d){
	fehlermeldung_ausgeben("DATUMSEINGABE FEHLERHAFT");
	die();
	}
	
	if(empty($leistung_id) && empty($leistungs_beschreibung)){
	echo "Wählen Sie bitte ein Leistung aus, oder geben Sie manuell Ihre Leistungsbeschreibung ein";	
	die();
	}
	if(empty($leistungs_beschreibung) && !empty($leistung_id)){
		$ze->pos_deaktivieren($zettel_id, $pos_dat);
		$ze->zettel_pos_speichern($datum, $benutzer_id, $leistung_id, $zettel_id, $dauer_min, $kostentraeger_typ, $kostentraeger_bez, $hinweis, $beginn,$ende);
	}
	if(!empty($leistungs_beschreibung) && empty($leistung_id)){
	$ze->pos_deaktivieren($zettel_id, $pos_dat);
	$ze->leistung_in_katalog($datum, $benutzer_id, $leistungs_beschreibung, $zettel_id, $dauer_min, $kostentraeger_typ, $kostentraeger_bez, $hinweis, $beginn,$ende);	
	}
	
	if(!empty($leistungs_beschreibung) && !empty($leistung_id)){
	echo "Entweder Leistung aussuchen oder Leistungsbeschreibung eintragen";
	}
}else{
	echo "EINGABE UNVOLLSTÄNDIG";
}


break;

case "zettel_zu_beleg":
$zettel_id = $_REQUEST[zettel_id];
$ze = new zeiterfassung;
$ze->zettel2beleg($zettel_id);
break;

case "zettel2pdf":
$zettel_id = $_REQUEST[zettel_id];
$ze = new zeiterfassung;
$ze->zettel2pdf($zettel_id);
break;

case "stundennachweise":
$ze = new zeiterfassung;
$ze->mitarbeiter_auswahl();
break;

case "stundennachweise_ex":
$ze = new zeiterfassung;
$ze->mitarbeiter_auswahl(1);
break;

case "nachweisliste":
$m_id = $_REQUEST['mitarbeiter_id'];
$ze = new zeiterfassung;
$ze->nachweisliste($m_id);
break;

case "einheitenliste":
$bg = new berlussimo_global;
$link = "?daten=zeiterfassung&option=einheitenliste";
#$bg->objekt_auswahl_liste($link);
#$ze = new zeiterfassung;
#if(!empty($_SESSION[objekt_id])){
#$ze->einheit_kurz_objekt($_SESSION[objekt_id]);

#}
break;

case "zettel_loeschen":
if(!empty($_REQUEST[zettel_id])){
$z = new zeiterfassung;
$zettel_id = $_REQUEST[zettel_id];
$benutzer_id = $z->get_userid($zettel_id);
if($benutzer_id == $_SESSION[benutzer_id] or check_user_mod($_SESSION[benutzer_id], '*')){
$z->zettel_loeschen_voll($zettel_id);	
weiterleiten("?daten=zeiterfassung&option=nachweisliste&mitarbeiter_id=$benutzer_id");
}else{
	die("Sie haben keine Berechtigung fremde Stundennachweise zu löschen, da sie keine Vollrechte haben.");
}
	
}else{
	die("Zettel auswählen");
}
break;



case "stunden":
$z = new zeiterfassung();
$z->form_stunden_anzeigen();
break;


case "suchen_std":
#print_req();
if(empty($_REQUEST['kostentraeger_typ']) or empty($_REQUEST['kostentraeger_id'])){
#	die('Kostentraeger wählen!');
}

if(empty($_REQUEST['adatum'])){
	die('Anfangsdatum notwendig!!!');
}

if(empty($_REQUEST['edatum'])){
	$edatum = date("d.m.Y");
}else{
	$edatum = $_REQUEST['edatum'];
}

$z = new zeiterfassung();
$adatum = $_REQUEST['adatum'];
$benutzer_id = $_REQUEST['benutzer_id'];
$gewerk_id = $_REQUEST['g_id'];
$kos_typ = $_REQUEST['kostentraeger_typ'];
$kos_bez = $_REQUEST['kostentraeger_id']; //bez später zu id machen nicht vergessen! 

$z->stunden_suchen($benutzer_id, $gewerk_id, $kos_typ, $kos_bez, $adatum, $edatum);
break;




}//end switch



?>