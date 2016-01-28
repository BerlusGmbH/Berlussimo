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
 * @filesource   $HeadURL: http://192.168.2.52/svn/berlussimo_1/tags/02.11.2010 - Downloadversion 0.27/options/modules/geldkonten.php $
 * @version      $Revision: 6 $
 * @modifiedby   $LastChangedBy: sivac $
 * @lastmodified $Date: 2010-09-21 10:34:50 +0200 (Di, 21 Sep 2010) $
 * 
 */
 
include_once("includes/allgemeine_funktionen.php");
include_once("classes/class_geldkonten.php");

/*Überprüfen ob Benutzer Zugriff auf das Modul hat*/
if(!check_user_mod($_SESSION['benutzer_id'], 'geldkonten')){
	echo '<script type="text/javascript">';
	echo "alert('Keine Berechtigung')";
	echo '</script>';	
	die();
}

include_once("options/links/links.geldkonten.php");
#include_once("classes/mietkonto_class.php");
include_once("classes/berlussimo_class.php");
if(!empty($_REQUEST["option"])){
$option = $_REQUEST["option"];
}else{
	$option = 'default';
}
switch($option) {

case "uebersicht_ea":
$form = new mietkonto;
$form->erstelle_formular("Geldkontenübersicht", NULL);
$geldkonten = new geldkonto_info;
$geldkonten->alle_geldkonten_tabelle();
$form->ende_formular();
break;


case "gk_neu":
$gk = new gk;
$gk->form_geldkonto_neu();
break;

case "new_gk":
#print_req();
if(!empty($_REQUEST['g_bez']) && !empty($_REQUEST['beguenstigter']) && !empty($_REQUEST['kontonummer']) && !empty($_REQUEST['blz']) && !empty($_REQUEST['institut']) && !empty($_REQUEST['kostentraeger_typ']) && !empty($_REQUEST['kostentraeger_id'])){
$gk = new gk;
$b = new buchen;
$g_bez = $_REQUEST['g_bez'];
$beguenstigter = $_REQUEST['beguenstigter'];
$kontonummer = $_REQUEST['kontonummer'];
$blz = $_REQUEST['blz'];
$institut = $_REQUEST['institut'];
$iban = $_REQUEST['iban'];
$bic = $_REQUEST['bic'];
$sep = new sepa();
$iban_mysql = $sep->iban_convert($iban,1);
$kostentraeger_typ = $_REQUEST['kostentraeger_typ'];
$kostentraeger_bez = $_REQUEST['kostentraeger_id'];
$kos_id = $b->kostentraeger_id_ermitteln($kostentraeger_typ, $kostentraeger_bez);
#print_req();
#echo "IBAN NEW $iban $iban_mysql";
#die();
$gk->geldkonto_speichern($kostentraeger_typ,$kos_id,$g_bez, $beguenstigter, $kontonummer, $blz, $institut, $iban_mysql, $bic);
weiterleiten('?daten=geldkonten&option=uebersicht_zuweisung');

}else{
	echo "Eingabe unvollständig Error: 621ghp";
}
break;

case "gk_aendern":
if(isset($_REQUEST['gk_id']) && !empty($_REQUEST['gk_id'])){
	$gk_id = ($_REQUEST['gk_id']);
	$gk = new gk;
	$gk->form_geldkonto_edit($gk_id);
}else{
	fehlermeldung_ausgeben("Geldkonto wählen");
}
break;

case "gk_update":
print_req();
if(!empty($_REQUEST['gk_id']) && !empty($_REQUEST['g_bez']) && !empty($_REQUEST['beguenstigter']) && !empty($_REQUEST['kontonummer']) && !empty($_REQUEST['blz']) && !empty($_REQUEST['institut']) && !empty($_REQUEST['iban']) && !empty($_REQUEST['bic'])){
$gk = new gk;
$b = new buchen;
$gk_id = $_REQUEST['gk_id'];
$g_bez = $_REQUEST['g_bez'];
$beguenstigter = $_REQUEST['beguenstigter'];
$kontonummer = $_REQUEST['kontonummer'];
$blz = $_REQUEST['blz'];
$institut = $_REQUEST['institut'];
$iban = $_REQUEST['iban'];
$bic = $_REQUEST['bic'];
$sep = new sepa();
$iban_mysql = $sep->iban_convert($iban,1);
#print_req();
#echo "IBAN NEW $iban $iban_mysql";
#die();
$gk->geldkonto_update($gk_id, $g_bez, $beguenstigter, $kontonummer, $blz, $institut, $iban_mysql, $bic);
weiterleiten('?daten=geldkonten');
}else{
	echo "Eingabe unvollständig Error: Modul GK 115";
}

break;

case "update_iban_bic":
$gk = new gk;
$gk->update_iban_bic_alle();
break;

case "gk_zuweisen":
$gk = new gk;
$gk->form_geldkonto_zuweisen();
break;

case "uebersicht_zuweisung":
$gk = new gk;
$gk->uebersicht_zuweisung();
break;

case "zuweisen_gk":
if(!empty($_REQUEST[geldkonto_id]) && !empty($_REQUEST[kostentraeger_typ]) && !empty($_REQUEST[kostentraeger_id])){
$gk = new gk;
$b = new buchen;
$geldkonto_id = $_REQUEST[geldkonto_id];
$kostentraeger_typ = $_REQUEST[kostentraeger_typ];
$kostentraeger_bez = $_REQUEST[kostentraeger_id];
$kos_id = $b->kostentraeger_id_ermitteln($kostentraeger_typ, $kostentraeger_bez);
if($gk->check_zuweisung_kos($geldkonto_id, $kostentraeger_typ, $kos_id)){
	echo "Zuweisung existiert bereits.";
}else{
	$gk->zuweisung_speichern($kostentraeger_typ,$kos_id,$geldkonto_id);
	weiterleiten('?daten=geldkonten&option=uebersicht_zuweisung');
}
}else{
	echo "Eingabe unvollständig Error: 623gd";
}
break;



case "zuweisung_loeschen":
if(!empty($_REQUEST['geldkonto_id']) && !empty($_REQUEST['kos_typ']) && !empty($_REQUEST['kos_id'])){
$gk = new gk;
$geldkonto_id = $_REQUEST['geldkonto_id'];
$kos_typ = $_REQUEST['kos_typ'];
$kos_id = $_REQUEST['kos_id'];
$gk-> zuweisung_aufheben($kos_typ,$kos_id,$geldkonto_id);
weiterleiten('?daten=geldkonten&option=uebersicht_zuweisung');
}else{
	echo "Eingabe unvollständig Error: 623gf1";
}

default:
$form = new mietkonto;
$form->erstelle_formular("Geldkontostände AKTUELL", NULL);
$geldkonten = new geldkonto_info;
$geldkonten->alle_geldkonten_tabelle_kontostand();
$form->ende_formular();
break;
}

?>
