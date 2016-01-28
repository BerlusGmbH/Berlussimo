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
 * @filesource   $HeadURL: http://192.168.2.52/svn/berlussimo_1/tags/02.11.2010 - Downloadversion 0.27/options/modules/buchen.php $
 * @version      $Revision: 15 $
 * @modifiedby   $LastChangedBy: sivac $
 * @lastmodified $Date: 2011-07-07 10:41:33 +0200 (Do, 07 Jul 2011) $
 * 
 */

/*Allgemeine Funktionsdatei laden*/
include_once("includes/allgemeine_funktionen.php");

/*Überprüfen ob Benutzer Zugriff auf das Modul hat*/
if(!isset($_SESSION['benutzer_id']) or !check_user_mod($_SESSION['benutzer_id'], 'buchen')){
	echo '<script type="text/javascript">';
	echo "alert('Keine Berechtigung')";
	echo '</script>';	
	die();
}




/*Klasse "formular" für Formularerstellung laden*/
include_once("classes/class_formular.php");

/*Modulabhängige Dateien d.h. Links und eigene Klasse*/
include_once("options/links/links.buchen.php");
include_once("classes/class_buchen.php");
include_once("classes/class_rechnungen.php");

/*Prüfen ob es Buchungen vor einem Jahr gibt und ausgeben*/
$bu = new buchen();
if($bu->get_buchungen_vor(2005)!=false){
fehlermeldung_ausgeben("Buchungen vor 2005 gefunden (DATUM FALSCH?!?: ANZAHL: ".$bu->get_buchungen_vor(2005));	
}

if(!empty($_REQUEST["option"])){
$option = $_REQUEST["option"];
}else{
$option = 'default';
}

/*Optionsschalter*/
switch($option) {

	/*Aufruf des Formulars für die 
	* Buchung der Zahlbeträge*/
	case "zahlbetrag_buchen":
   	$form = new formular;
    $form->erstelle_formular("Buchungsmaske für Zahlbeträge", NULL);
   	$buchung = new buchen;
   	$link="?daten=buchen&option=zahlbetrag_buchen";
   	$buchung->geldkonto_auswahl($link);
   	
   	if(isset($_SESSION['geldkonto_id']) && isset($_SESSION['temp_datum']) && isset($_SESSION['temp_kontoauszugsnummer']) && isset($_SESSION['temp_kontostand']) ){
   	$buchung->zb_buchen_form($_SESSION['geldkonto_id']);
   	}
   	$form->ende_formular();
    break;
    
    case "geldkonto_aendern":
   	$form = new formular;
    $form->erstelle_formular("Geldkonto ändern", NULL);
   	$buchung = new buchen;
   	unset($_SESSION['geldkonto_id']);
   	unset($_SESSION['temp_datum']);
   	unset($_SESSION['temp_kontoauszugsnummer']);
   	unset($_SESSION['temp_kontostand']);
   	unset($_SESSION['kos_typ']);
   	unset($_SESSION['kos_id']);
   	
   	$link="?daten=buchen&option=geldkonto_aendern";
   	   
   	$buchung->geldkonto_auswahl($link);
   	$form->ende_formular();
    break;

	 case "buchung_gesendet":
   	
	$link_kontoauszug = "<a href=\"?daten=buchen&option=kontoauszug_form\">Kontrolldaten zum Kontoauszug eingeben</a>";
   	$form = new formular;
    $form->erstelle_formular("Buchungsinformationen prüfen", NULL);
   	$kostentraeger_typ = $_POST['kostentraeger_typ'];
   	$kostentraeger_id = $_POST['kostentraeger_id'];
	if(empty($kostentraeger_typ) OR empty($kostentraeger_id)){
   	$error = "Fehler - Kostenträgertyp und Kostenträger wählen";
   	}else{
   		echo "if(empty($kostentraeger_typ) OR empty($kostentraeger_id)){";
   	}
   	$kto_auszugsnr = $_POST['kontoauszugsnummer'];
   	if(empty($kto_auszugsnr)){
   	$error = "Fehler - Kontoauszugsnummer";	
   	}
   	if($kto_auszugsnr != $_SESSION['temp_kontoauszugsnummer']){
   	
   	$error = "Sie beginnen mit einem neuen Kontoauszug.<br>";
   	$error .= "Bitte die Kontrolldaten zur Kontoauszugsnummer $_SESSION[temp_kontoauszugsnummer] eingeben";	
   	$error .= "<br>$link_kontoauszug";
   	}
   	if(!is_numeric($kto_auszugsnr)){
   	$error = "Fehler - Kontoauszugsnummer - NUR ZAHLEN";
   	}
   	$datum = $_POST['datum'];
   	if(empty($datum)){
   	$error = "Fehler - Datum fehlt";
   	}
   	$rechnungsnr = $_POST['rechnungsnr'];
   	if(empty($rechnungsnr)){
   	$error = "Fehler - Rechnungsnummer";	
   	}
   	if($datum != $_SESSION['temp_datum']){
   	$link_kontoauszug = "<a href=\"?daten=buchen&option=kontoauszug_form\">Kontrolldaten zum Kontoauszug eingeben</a>";
   	$error = "Sie haben das Buchungsdatum verändert.<br>";
   	$error .= "Bitte die Kontrolldaten zur Kontoauszugsnummer $_SESSION[temp_kontoauszugsnummer] verändern.";	
   	$error .= "<br>$link_kontoauszug";
   	}
   	if(!check_datum ($datum)){
   	$error = "Fehler - Datumsformat überprüfen";	
   	}
   	$betrag = $_POST['betrag'];
   	if(empty($betrag)){
   	$error = "Fehler - Betrag";	
   	}
   	   	
   	$kostenkonto = $_POST['kostenkonto'];
   	$vzweck = $_POST['vzweck'];
   	if(empty($vzweck)){
   	$error = "Fehler - Buchungstext fehlt";	
   	}
   	
   	$geldkonto_id = $_POST['geldkonto_id'];
   	if(empty($geldkonto_id)){
   	$error = "Fehler - Kein Geldkonto wurde gewählt";
   	}
   	if(!isset($error)){
   	echo "$kostentraeger_typ - $kostentraeger_id";
   /*	print_r($_SESSION);
   	$_SESSION['kos_typ'] = $kostentraeger_typ;
   	$_SESSION['kos_bez'] = $kostentraeger_id;
   	die();*/
   	echo "<h3>Datum: $datum<br>";
   	echo "Kontoauszugsnr: $kto_auszugsnr<br>";
   	echo "Betrag: $betrag<br>";
   	echo "Kostenkonto: $kostenkonto<br>";
   	echo "Kostenträgertyp $kostentraeger_typ<br>";
   	echo "Kostenträger $kostentraeger_id<br>";
   	echo "Buchungstext $vzweck<br></h3>";
   	/*$form->hidden_feld("geldkonto_id", "$geldkonto_id");
   	$form->hidden_feld("kostentraeger_typ", "$kostentraeger_typ");
   	$form->hidden_feld("kostentraeger_id", "$kostentraeger_id");
   	$form->hidden_feld("kontoauszugsnummer", "$kto_auszugsnr");
   	$form->hidden_feld("rechnungsnr", "$rechnungsnr");
   	$form->hidden_feld("datum", "$datum");
   	$form->hidden_feld("betrag", "$betrag");
   	$form->hidden_feld("kostenkonto", "$kostenkonto");
   	$form->hidden_feld("vzweck", "$vzweck");
    $form->hidden_feld("option", "buchung_speichern");
    $form->send_button("submit_zb_speichern", "Speichern");
   */
   $datum = date_german2mysql($datum);
   $betrag = nummer_komma2punkt($betrag);
   $buchung = new buchen;
   	 if($_POST['mwst']){
   	 $mwst = nummer_komma2punkt($_POST['mwst']);
   	 }else{
   	 	$mwst = '0.00';
   	 }
   	 #if(is_numeric($kostentraeger_id)){
   	 #	die('KOSTENTRÄGER WÄHLEN');
   	 #}
   	 $buchung->geldbuchung_speichern($datum, $kto_auszugsnr, $rechnungsnr, $betrag, $vzweck, $geldkonto_id, $kostentraeger_typ, $kostentraeger_id, $kostenkonto, $mwst);
     }else{
   	 echo $error;
   	 }
   	$form->ende_formular();
    break;

case "buchung_speichern":
	$kostentraeger_typ = $_POST[kostentraeger_typ];
   	$kostentraeger_id = $_POST[kostentraeger_id];
   	$kto_auszugsnr = $_POST[kontoauszugsnummer];
   	$datum = $_POST[datum];
   	$datum = date_german2mysql($datum);
   	$betrag = $_POST[betrag];
   	$betrag = nummer_komma2punkt($betrag);
   	$kostenkonto = $_POST[kostenkonto];
   	$vzweck = $_POST[vzweck];
   	$geldkonto_id = $_POST[geldkonto_id];
   	$rechnungsnr = $_POST[rechnungsnr];
   	$buchung = new buchen;
   	$buchung->geldbuchung_speichern($datum, $kto_auszugsnr, $rechnungsnr, $betrag, $vzweck, $geldkonto_id, $kostentraeger_typ, $kostentraeger_id, $kostenkonto);

break;

case "geldbuchung_aendern":
   	$form = new formular;
    $form->erstelle_formular("Geldbuchung ändern", NULL);
   	$buchung = new buchen;
   	$geldbuchung_dat = $_REQUEST['geldbuchung_dat'];
   	$buchung->buchungsmaske_buchung_aendern($geldbuchung_dat);
   	$form->ende_formular();
    break;

case "geldbuchung_aendern1":
   	$form = new formular;
    $form->erstelle_formular("Geldbuchung ändern", NULL);
   	$buchung = new buchen;
   	$geldbuchung_dat_alt = $_POST['buch_dat_alt'];
   	$geldbuchung_id = $_POST['akt_buch_id'];
   	$g_buchungsnummer = $_POST['g_buchungsnummer'];
   	$betrag =$_POST['betrag'];
   	$datum = $_POST['datum'];
   	$kostentraeger_typ = $_POST['kostentraeger_typ'];
   	$kostentraeger_bez = $_POST['kostentraeger_id'];
   	$vzweck = $_POST['vzweck'];
   	$kostenkonto = $_POST['kostenkonto'];
   	$geldkonto_id = $_POST['geldkonto_id'];
   	$kontoauszugsnr = $_POST['kontoauszugsnr'];
   	$erfass_nr = $_POST['erfassungsnr'];
   	$mwst_anteil = $_POST['mwst'];
   	$buchung->geldbuchungs_dat_deaktivieren($geldbuchung_dat_alt);
   	$buchung->speichern_in_geldbuchungen($geldbuchung_id, $g_buchungsnummer, $betrag, $datum, $kostentraeger_typ, $kostentraeger_bez, $vzweck, $kostenkonto, $geldkonto_id, $kontoauszugsnr, $erfass_nr, $mwst_anteil, $geldbuchung_dat_alt);
   	/*echo '<pre>';
   	print_r($_POST);
   	*/
   	$form->ende_formular();
    break;

case "kontoauszug_form":
   	$form = new formular;
    $form->erstelle_formular("Kontoauszug bearbeiten", NULL);
   	$buchung = new buchen;
   	$buchung->kontoauszug_form();
   	$form->ende_formular();
    break;
    
case "kontoauszug_gesendet":
   	$form = new formular;
    $form->erstelle_formular("Kontoauszug temporär gespeichert", NULL);
   	$_SESSION['temp_kontoauszugsnummer'] = $_POST['kontoauszugsnummer'];
   	$_SESSION['temp_datum'] = $_POST['datum'];
   	$_SESSION['buchungsdatum'] = $_POST['datum'];
   	$kontostand_punkt = nummer_komma2punkt($_POST['kontostand']);
   	$_SESSION['temp_kontostand'] = $kontostand_punkt;
   	echo "Kontoauszugsdaten wurden temporär gespeichert.<br>";
   	echo "Sie werden weitergeleitet.";
   	weiterleiten_in_sec("?daten=buchen&option=zahlbetrag_buchen", 1);
   	$form->ende_formular();
    break;


 case "buchungs_journal":
   	$form = new formular;
    echo "<body onload=\"JavaScript:seite_aktualisieren(10000);\">";
    if(!empty($_SESSION['temp_kontoauszugsnummer']) && !empty($_SESSION['geldkonto_id'])){
    $buchung = new buchen;
    $buchung->akt_konto_bezeichnung = $buchung->geld_konto_bezeichung($_SESSION['geldkonto_id']);
    $form->erstelle_formular("$buchung->akt_konto_bezeichnung -> Buchungsjournal vom Kontoauszug $_SESSION[temp_kontoauszugsnummer]", NULL);
   	$buchung->buchungsjournal_auszug($_SESSION['geldkonto_id'], $_SESSION['temp_kontoauszugsnummer']);
   	$form->ende_formular();
    }
    if(empty($_SESSION['temp_kontoauszugsnummer'])){
    $buchung = new buchen;
    $buchung->akt_konto_bezeichnung = $buchung->geld_konto_bezeichung($_SESSION[geldkonto_id]);
    if(!empty($_REQUEST['jahr']) && !empty($_REQUEST['monat'])){
    $jahr = $_REQUEST['jahr']; 
    $monat = sprintf("%02d",$_REQUEST['monat']);
    $datum = "$jahr-$monat-01";
    }else{
    $jahr = date("Y"); 
    $monat = sprintf("%02d",date("m"));
    $datum = "$jahr-$monat-01";	
    }
    $form->erstelle_formular("$buchung->akt_konto_bezeichnung -> Buchungsjournal seit $datum", NULL);
   	$bg = new berlussimo_global;
   	$link = "?daten=buchen&option=buchungs_journal";
   	$bg->monate_jahres_links($jahr, $link);
   	echo "<a href=\"?daten=buchen&option=buchungs_journal_druckansicht\">Druckansicht</a>&nbsp;";
   		if(!empty($_REQUEST['monat'])){
   		$aktueller_monat = $_REQUEST['monat'];
   		}else{
   			$aktueller_monat = date("m");
   		}
   	echo "<a href=\"?daten=buchen&option=buchungs_journal_pdf&monat=$aktueller_monat&jahr=$jahr\">PDF-Ansicht</a>&nbsp;";
   	$buchung->buchungsjournal_startzeit($_SESSION['geldkonto_id'], $datum);
   	$form->ende_formular();	
    }
    break;
    
    
    case "buchungs_journal_druckansicht":
   	if(file_exists("print_css/print_buchungsjournal.css")){
   	echo "<header><link rel=\"stylesheet\" type=\"text/css\"  href=\"print_css/print_buchungsjournal.css\" media=\"print\"></header>";	
   	}
   	$form = new formular;
    if(!empty($_SESSION[temp_kontoauszugsnummer]) && !empty($_SESSION[geldkonto_id])){
    $buchung = new buchen;
    $buchung->akt_konto_bezeichnung = $buchung->geld_konto_bezeichung($_SESSION[geldkonto_id]);
    $form->erstelle_formular("$buchung->akt_konto_bezeichnung -> Buchungsjournal vom Kontoauszug $_SESSION[temp_kontoauszugsnummer]", NULL);
   	$buchung->buchungsjournal_auszug($_SESSION[geldkonto_id], $_SESSION[temp_kontoauszugsnummer]);
   	$form->ende_formular();
    }
    if(empty($_SESSION[temp_kontoauszugsnummer])){
    $buchung = new buchen;
    $buchung->akt_konto_bezeichnung = $buchung->geld_konto_bezeichung($_SESSION[geldkonto_id]);
    if(!empty($_REQUEST['jahr']) && !empty($_REQUEST['monat'])){
    $jahr = $_REQUEST['jahr']; 
    $monat = sprintf("%02d",$_REQUEST['monat']);
    $datum = "$jahr-$monat-01";
    }else{
    $jahr = date("Y"); 
    $monat = sprintf("%02d",date("m"));
    $datum = "$jahr-$monat-01";	
    }
    $form->erstelle_formular("$buchung->akt_konto_bezeichnung -> Buchungsjournal seit $datum", NULL);
   	$bg = new berlussimo_global;
   	$link = "?daten=buchen&option=buchungs_journal_druckansicht";
   	$bg->monate_jahres_links($jahr, $link);
   	$buchung->buchungsjournal_startzeit_druck($_SESSION['geldkonto_id'], $datum);
   	$form->ende_formular();	
    }
    break;
    
    case "buchungs_journal_pdf":
   	if(!empty($_REQUEST[jahr]) && !empty($_REQUEST[monat])){
    $jahr = $_REQUEST[jahr]; 
    $monat = sprintf("%02d",$_REQUEST[monat]);
    $datum = "$jahr-$monat-01";
    }else{
    $jahr = date("Y"); 
    $monat = sprintf("%02d",date("m"));
    $datum = "$jahr-$monat-01";	
    }
    if(!empty($_SESSION[geldkonto_id])){
    $b = new buchen;
    $b->buchungsjournal_startzeit_pdf($_SESSION[geldkonto_id], $datum);
    }else{
    	echo "Geldkonto auswählen";
    }
   	
    break;
    
    
    case "buchungs_journal_jahr_pdf":
   	if(!empty($_REQUEST[jahr])){
    $jahr = $_REQUEST[jahr]; 
    }else{
    $jahr = date("Y"); 
    }
    if(!empty($_SESSION[geldkonto_id])){
    $b = new buchen;
    $b->buchungsjournal_jahr_pdf($_SESSION[geldkonto_id], $jahr);
    }else{
    	echo "Geldkonto auswählen";
    }
   	
    break;
    
    
    case "reset_kontoauszug":
   	unset($_SESSION[temp_kontoauszugsnummer]);
   	echo "Temporäre Kontoauszugsnummer wurde gelöscht.<br>";
   	echo "Sie werden weitergeleitet.";
   	weiterleiten_in_sec('?daten=buchen&option=buchungs_journal', 1);
   	break;
   	
   	case "buchungsbeleg_ansicht":
   	$buchungsnr =$_REQUEST['buchungsnr']; 
   	$form = new formular;
   	$form->erstelle_formular("Ansicht Buchungsbeleg für Buchungsnummer $buchungsnr", NULL);
   	$b = new buchen;
   	$b->buchungsbeleg_ansicht($buchungsnr);
   	$form->ende_formular();
   	break;
   	
   	case "konten_uebersicht":
   	$b = new buchen;
   	$link="?daten=buchen&option=konten_uebersicht";
   	$form = new formular;
    $form->fieldset("Buchungen -> Kostenkontenübersicht", 'kostenkonten');
   	$b->geldkonto_auswahl_menu($link);
   	$geldkonto_id = $_SESSION['geldkonto_id'];
   	if(!empty($geldkonto_id)){
   	$b->buchungskonten_uebersicht($geldkonto_id);
   	}else{
   		echo "Geldkonto auswählen";
   	}
   	$form->fieldset_ende();
   	break;
   	
   	
   	case "konten_uebersicht_pdf":
   	$b = new buchen;
   	$link="?daten=buchen&option=konten_uebersicht";
   	$form = new formular;
    $form->fieldset("Buchungen -> Kostenkontenübersicht als PDF", 'kostenkonten');
   	$b->geldkonto_auswahl_menu($link);
   	$geldkonto_id = $_SESSION[geldkonto_id];
   	if(!empty($geldkonto_id)){
   	$b->buchungskonten_uebersicht_pdf($geldkonto_id);
   	}else{
   		echo "Geldkonto auswählen";
   	}
   	$form->fieldset_ende();
   	break;
   	
   	
   	case "buchungskonto_summiert_xls":
   		if(isset($_SESSION['geldkonto_id']) && !empty($_SESSION['geldkonto_id'])){
   			if(isset($_REQUEST['jahr']) && !empty($_REQUEST['jahr'])){
   				$jahr = $_REQUEST['jahr'];
   			}else{
   				$jahr = date("Y");
   			}
   			
   			$weg = new weg();
   			#$weg->form_hausgeldzahlungen_xls($_SESSION['objekt_id']);
   			
   			$weg->kontobuchungen_anzeigen_jahr_xls($_SESSION['geldkonto_id'], $jahr);
   		}else{
   			fehlermeldung_ausgeben("Geldkonto wählen!!!");
   		}
   	break;
   	
   	
   	case "konto_uebersicht":
   	$b = new buchen;
   	$link="?daten=buchen&option=konto";
   	$form = new formular;
    $form->erstelle_formular('Buchungen -> Kostenkontenübersicht dynamisch', null);
   	$b->geldkonto_auswahl_menu($link);
   	$geldkonto_id = $_SESSION[geldkonto_id];
   	if(!empty($geldkonto_id)){
   	$b->form_kontouebersicht();
   	}else{
   		echo "Geldkonto auswählen";
   	}
   	$form->fieldset_ende();
   	break;
   	
   	case "kostenkonto_suchen":
   	$b = new buchen;
   	$link="?daten=buchen&option=konto";
   	$form = new formular;
    $form->fieldset("Buchungen -> Kostenkontenübersicht dynamisch", 'kostenkonten_dyn');
   	$kostentraeger_typ = $_POST[kostentraeger_typ];
   	$kostentraeger_id = $_POST[kostentraeger_id];
   	$kostenkonto = $_POST[kostenkonto];
   	$geldkonto_id = $_POST[geldkonto_id];
   	$anfangsdatum = $_POST[anfangsdatum];
   	$enddatum = $_POST[enddatum];
   	$b->kontobuchungen_anzeigen_dyn($geldkonto_id, $kostenkonto, $anfangsdatum, $enddatum, $kostentraeger_typ, $kostentraeger_id);
   	#print_r($_POST);
   	$form->fieldset_ende();
   	break;
   
   
   /*Einsicht aller Buchungen zu einem Kostenkonto unabhängig vom Geldkonto*/
   case "buchungen_zu_kostenkonto":
   	$b = new buchen;
   	$link="?daten=buchen&option=konto";
   	$f = new formular;
    $f->fieldset("Buchungen -> Buchungen zu einem Kostenkonto finden", 'kostenkonten_buchungen');
   	if(empty($_POST)){
   	$b->form_buchungen_zu_kostenkonto();
   	$f->fieldset_ende();
   	}else{
   	$kostenkonto = $_POST['kostenkonto'];
   	$anfang = date_german2mysql($_POST['anfangsdatum']);
   	$ende = date_german2mysql($_POST['enddatum']);
   	$b->finde_buchungen_zu_kostenkonto($kostenkonto, $anfang, $ende);
   	}
   break;
   
   
   
   case "eingangsbuch_kurz":
   	if(isset($_REQUEST['partner_wechseln'])){
  	unset($_SESSION['partner_id']);
   	}
   	if(isset($_REQUEST['partner_id'])){
  $_SESSION[partner_id] = $_REQUEST[partner_id];
   	}
   	$r = new rechnungen;
    $p = new partner;
    $link = "?daten=buchen&option=eingangsbuch_kurz";
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
    if(file_exists("print_css/$rechnung->rechnungs_aussteller_typ/$rechnung->rechnungs_aussteller_id.css")){
   	echo "<header><link rel=\"stylesheet\" type=\"text/css\"  href=\"print_css/$rechnung->rechnungs_aussteller_typ/$rechnung->rechnungs_aussteller_id.css\" media=\"print\"></header>";	
   	}else{
   	echo "<header><link rel=\"stylesheet\" type=\"text/css\"  href=\"css/print_rechnungen.css\" media=\"print\"></header>";	
   	}
    $r->rechnungseingangsbuch_kurz('Partner', $partner_id, $monat, $jahr, 'Rechnung');
    }
    #echo '<pre>';
    #print_r($_SERVER);
    $fragez = strpos($_SERVER['REQUEST_URI'], '?');
    #echo "FFF $fragez";
    $last_url = substr($_SERVER['REQUEST_URI'],$fragez);
    #echo $last_url;
    $_SESSION['last_url'] = $last_url;
   	break; 
   	
   	
   	
   	
   	case "ausgangsbuch_kurz":
   	if(isset($_REQUEST[partner_wechseln])){
  	unset($_SESSION[partner_id]);
   	}
   	if(isset($_REQUEST[partner_id])){
  $_SESSION[partner_id] = $_REQUEST[partner_id];
   	}
   	$r = new rechnungen;
    $p = new partner;
    $link = "?daten=buchen&option=ausgangsbuch_kurz";
    $partner_id = $_SESSION[partner_id];
    
    if(isset($_REQUEST[monat]) && isset($_REQUEST[jahr])){
    	if($_REQUEST['monat']!= 'alle'){
    	$_SESSION[monat] = sprintf('%02d',$_REQUEST[monat]);
    	}else{
    	$_SESSION[monat] = $_REQUEST[monat]; 
    	}
 	$_SESSION[jahr] = $_REQUEST[jahr];    	
    }
    
    if(empty($partner_id)){
    $p->partner_auswahl($link); 
    }
    else{
    #$p->partner_auswahl($link);
    $monat = $_SESSION[monat];
    $jahr = $_SESSION[jahr];
    
    if(empty($monat) OR empty($jahr)){
    $monat = date("m");
    $jahr = date("Y");	
    }
    if(file_exists("print_css/$rechnung->rechnungs_aussteller_typ/$rechnung->rechnungs_aussteller_id.css")){
   	echo "<header><link rel=\"stylesheet\" type=\"text/css\"  href=\"print_css/$rechnung->rechnungs_aussteller_typ/$rechnung->rechnungs_aussteller_id.css\" media=\"print\"></header>";	
   	}else{
   	echo "<header><link rel=\"stylesheet\" type=\"text/css\"  href=\"css/print_rechnungen.css\" media=\"print\"></header>";	
   	}
    $r->rechnungsausgangsbuch_kurz('Partner', $partner_id, $monat, $jahr, 'Rechnung');
    }
    
    $fragez = strpos($_SERVER['REQUEST_URI'], '?');
    #echo "FFF $fragez";
    $last_url = substr($_SERVER['REQUEST_URI'],$fragez);
    #echo $last_url;
    $_SESSION['last_url'] = $last_url;
   	break; 
   	
   
   
   
   	/*Monatsbericht ohne ausgezogene Mietern*/
   	case "monatsbericht_o_a":
   	$b = new buchen;
   	$link="?daten=buchen&option=konto";
   	$form = new formular;
    $form->fieldset("Monatsbericht", 'monatsbericht');
   	$b->monatsbericht_ohne_ausgezogene();
   	$form->fieldset_ende();
   	break;
   	/*Monatsbericht mit ausgezogenen Mietern*/
   	case "monatsbericht_m_a":
   	$b = new buchen;
   	$link="?daten=buchen&option=konto";
   	$form = new formular;
    $form->fieldset("Monatsbericht", 'monatsbericht');
   	$b->monatsbericht_mit_ausgezogenen();
   	$form->fieldset_ende();
   	break;
   	
   	case"test":
   		ob_clean(); //ausgabepuffer leeren
   		include_once('pdfclass/class.ezpdf.php');
		$pdf = new Cezpdf('a4', 'portrait');
		$pdf->ezSetCmMargins(4.5,2.5,2.5,2.5);
		$berlus_schrift = 'pdfclass/fonts/Times-Roman.afm';
		$text_schrift = 'pdfclass/fonts/Arial.afm';
		$pdf->addJpegFromFile('pdfclass/hv_logo198_80.jpg', 450, 780, 100, 42);
		$pdf->setLineStyle(0.5);
		$pdf->selectFont($berlus_schrift);
		$pdf->addText(42,743,6,"BERLUS HAUSVERWALTUNG - Fontanestr. 1 - 14193 Berlin");
		$pdf->line(42,750,550,750);
		$seite = $pdf->ezGetCurrentPageNumber();
		$alle_seiten = $pdf->ezPageCount;
$data55 = array(
array('num'=>1,'name'=>'gandalf','type'=>'wizard')
,array('num'=>2,'name'=>'bilbo','type'=>'hobbit','url'=>'http://www.ros.co.
nz/pdf/')
,array('num'=>3,'name'=>'frodo','type'=>'hobbit')
,array('num'=>4,'name'=>'saruman','type'=>'bad
dude','url'=>'http://sourceforge.net/projects/pdf-php')
,array('num'=>5,'name'=>'sauron','type'=>'really bad dude')
);
$pdf->ezTable($data55);
#header('Content-type: application/pdf');
#header('Content-Disposition: attachment; filename="downloaded.pdf"');


#$output = $pdf->Output();
#$len = strlen($output);

  #header("Content-type: application/pdf");  // wird von MSIE ignoriert
  #header("content-length: $len");
  #header("Content-Disposition: inline; filename=test.pdf"); //im fenster
  #header("Content-Disposition: attachment; filename=test.pdf");
  #echo $output;          // jetzt ausgeben
$pdf->ezStream();
   	break;
   	
   	default:
   	if(!empty($_REQUEST['geldkonto_id'])){
   	$_SESSION['geldkonto_id'] = $_REQUEST['geldkonto_id'];	
   	}
   	
   	break;


	case "kosten_einnahmen":
	$f = new formular;
    $b = new buchen;
    $f->fieldset("Kosten & Einnahmen", 'kosten_einnahmen');
   	$b->form_kosten_einnahmen();
   	$f->fieldset_ende();
	break;
	
	case "kosten_einnahmen_pdf":
		
	$f = new formular;
    $b = new buchen;
    $f->fieldset("Kosten & Einnahmen", 'kosten_einnahmen');
   	$arr[0][GELDKONTO_ID] = '4';
   	$arr[0][OBJEKT_NAME] = 'II';
   	$arr[1][GELDKONTO_ID] = '5';
   	$arr[1][OBJEKT_NAME] = 'III';
   	$arr[2][GELDKONTO_ID] = '6';
   	$arr[2][OBJEKT_NAME] = 'V';
   	$arr[3][GELDKONTO_ID] = '11';
   	$arr[3][OBJEKT_NAME] = 'E';
   	$arr[4][GELDKONTO_ID] = '8';
   	$arr[4][OBJEKT_NAME] = 'GBN';
   	$arr[5][GELDKONTO_ID] = '7';
   	$arr[5][OBJEKT_NAME] = 'HW';
   	$arr[6][GELDKONTO_ID] = '10';
   	$arr[6][OBJEKT_NAME] = 'FON';
   	$arr[7][GELDKONTO_ID] = '12';
   	$arr[7][OBJEKT_NAME] = 'LAGER';
   	
   	 if(isset($_REQUEST[monat]) && isset($_REQUEST[jahr])){
    	if($_REQUEST['monat']!= 'alle'){
    	$_SESSION[monat] = sprintf('%02d',$_REQUEST[monat]);
    	}else{
    	$_SESSION[monat] = $_REQUEST[monat]; 
    	}
 	$_SESSION[jahr] = $_REQUEST[jahr];    	
    $jahr=$_SESSION[jahr];
    $monat = $_SESSION[monat];
    }
   	if(empty($monat) OR empty($jahr)){
    $monat = date("m");
    $jahr = date("Y");	
    }
   	$b->kosten_einnahmen_pdf($arr, $monat, $jahr);
   	$f->fieldset_ende();
	break;
	
	case "buchung_suchen":
	$f = new formular;
    $b = new buchen;
    $f->fieldset("Buchung suchen", 'buchung_suchen');
   	$b->form_buchung_suchen();
   	$f->fieldset_ende();
	break;
	
	case "buchung_suchen_1":
	$f = new formular;
    $b = new buchen;
    $b->form_buchung_suchen();
    $f->fieldset("Suchergebnis", 'buchung_suchen');
   	#echo '<pre>';
   	#print_r($_POST);
   	$geld_konto_id = $_POST['geld_konto'];
   	$betrag = $_POST['betrag'];
   	$ausdruck = $_POST['ausdruck'];
   	$anfangsdatum = $_POST['anfangsdatum'];
   	$enddatum = $_POST['enddatum'];
   	$kontoauszug = $_POST['kontoauszug'];
   	$kostenkonto = $_POST['kostenkonto'];
   	$kostentraeger_typ = $_POST['kostentraeger_typ'];
   	$kostentraeger_bez = $_POST['kostentraeger_id'];
   	
   	$kostenkonto = $_POST['kostenkonto'];
   	
   	
   	#echo "$geld_konto_id, $betrag, $ausdruck, $anfangsdatum, $enddatum, $kostenkonto";
   		
   		
   		
   	###########
		if($geld_konto_id != 'alle'){
		$where[] = " GELDKONTO_ID='$geld_konto_id' ";
		} 	
   		
   		if($betrag) {
   		$betrag = nummer_komma2punkt($betrag);
   		$where[] = " BETRAG='$betrag' "; 
   		}
		   	
   		if($ausdruck) {
   		$ausdruck_arr = explode('|', $ausdruck);
   		$anz_aus = count($ausdruck_arr);
   		for($ss=0;$ss<$anz_aus;$ss++){
   			$ausdruck_n = $ausdruck_arr[$ss];
   			if($anz_aus==1){
   			$where_or = " VERWENDUNGSZWECK LIKE '%$ausdruck_n%' ";
   			}else{
   				if($ss<$anz_aus-1){
   				$where_or .= " VERWENDUNGSZWECK LIKE '%$ausdruck_n%' OR ";
   				}else{
   				$where_or .= " VERWENDUNGSZWECK LIKE '%$ausdruck_n%' ";	
   				}
   			}
   		}
   		$where[] = $where_or;
   		}
   		
   		if($anfangsdatum) {
   		$anfangsdatum = date_german2mysql($anfangsdatum);
   		}
   		
   		if($enddatum) {
   		$enddatum = date_german2mysql($enddatum);
   		}
   		
   		   		
   		if($anfangsdatum && $enddatum){
   		$where[] = " DATUM BETWEEN '$anfangsdatum' AND '$enddatum' ";
   		}
   		
   		if($anfangsdatum && !$enddatum){
   		$where[] = " DATUM = '$anfangsdatum'";
   		}
   		
   		if($enddatum && !$anfangsdatum){
   		$where[] = " DATUM = '$enddatum'";
   		}
   		
   		if($kontoauszug) {
   		$where[] = " KONTO_AUSZUGSNUMMER='$kontoauszug' ";
   		}
   		
   		if(!empty($kostenkonto)) {
   		$where[] = " KONTENRAHMEN_KONTO='$kostenkonto' ";
   		}
   		
   		if($kostentraeger_typ) {
   		$where[] = " KOSTENTRAEGER_TYP='$kostentraeger_typ' ";
   		}
   		
   		if($kostentraeger_bez){
   		$kostentraeger_id = $b->kostentraeger_id_ermitteln($kostentraeger_typ, $kostentraeger_bez);
   		$where[] = " KOSTENTRAEGER_ID='$kostentraeger_id' ";
   		}
   		
   		if(!$betrag && !$ausdruck && !$anfangsdatum && !$enddatum && !$kontoauszug && !$kostenkonto && !$kostentraeger_typ){
   		echo "FEHLER KEINE AUSWAHL GETROFFEN";	
   		}else{
   		echo '<pre>';
   		$anzahl_kriterien = count($where);
   		$abfrage = "SELECT * FROM GELD_KONTO_BUCHUNGEN WHERE";
   			for($a=0;$a<$anzahl_kriterien;$a++){
   			if($a == 0){
   			$abfrage .= $where[$a];
   			}else{
   				#$teil = $where[$a];
   				#if(strstr($teil, ' OR ', true)){
   				#$abfrage .= $where[$a];
   				#}else{
   				$abfrage .= '&&'.$where[$a];	
   				#}
   			}
   			}
   		$abfrage .= " && AKTUELL='1' ORDER BY DATUM ASC, KOSTENTRAEGER_TYP, KOSTENTRAEGER_ID";
   		#echo $abfrage;
   		#die();
   		
   		/*SELECT * FROM articles WHERE MATCH (title,body)
    -> AGAINST ('+MySQL -YourSQL' IN BOOLEAN MODE);
   		
   		#print_r($where);
   		#echo $abfrage;	
   		#die();
   		/*Monitorausgabe*/
   		if(isset($_POST[submit_php])){
   			if($ausdruck!='' OR $betrag!='' OR $kostenkonto!=''){
   			$b->finde_buchungen($abfrage);
   			}else{
   				echo "Bitte geben Sie den gesuchten Betrag, Ausdruck oder ein Kostenkonto ein.";
   			}
   		}
   		/*PDF-Ausgabe*/
   		if(isset($_POST[submit_pdf])){
   			if($ausdruck!='' OR $betrag!='' OR $kostenkonto!=''){
   		$b->finde_buchungen_pdf($abfrage);
   		}else{
   				echo "Bitte geben Sie den gesuchten Betrag, Ausdruck oder ein Kostenkonto ein.";
   		}
   		}
   		}
   		
   		
   	$f->fieldset_ende();
	break;

	case "kostenkonto_pdf":
	$b = new buchen;
	$b->form_kostenkonto_pdf();
	break;	
  
	/*mt940 Kontoauszug test*/
	case "mt940":
	include('classes/mt940_class.php');
	$mt = new mt940;
	#$mt->import('mt940.txt');
	#$mt->daten_anzeigen();
	$mt->feld_definition();
	break;
	
	/*CSV Kontoauszug*/
	case "kontoauszug_csv":
	$datei = "temp/berlussimo_auszug.csv.csv"; //DATEINAME
	$tabelle_in_gross = strtoupper($datei); // Tabelle in GROßBUCHSTABEN
	$array = file($datei); //DATEI IN ARRAY EINLESEN
	#echo $array[0]; //ZEILE 0 mit Überschriften
	$anz_zeilen = count($array);
	/*Ausgabe ab 1. zeile*/
	echo "<table class=\"sortable\">";
	echo "<tr><th>Datum</th><th>VZweck</th><th>Auftraggeber</th><th>Betrag</th><th>KONTO</th><th>BELEG</th><th>OPTION</th></tr>";
	$bb = new buchen();
	#print_r($_SESSION);
	if(isset($_SESSION['geldkonto_id'])){
	$geldkonto_id = $_SESSION['geldkonto_id'];
	}
	if(isset($_SESSION['partner_id'])){
	$partner_id = $_SESSION['partner_id'];
	}
	
	if(empty($geldkonto_id) or empty($partner_id)){
		fehlermeldung_ausgeben('Geldkonto und Partner wählen');
		die();
	}
	if(isset($_REQUEST['start'])){
		$start = $_REQUEST['start'];
	}else{
	$start = 1;	
	}
	echo '<pre>';
	for($a=$start;$a<$anz_zeilen;$a++){
		$feld = explode(';', $array[$a]);
		$auszugsnr = $feld[0];
		$buch_datum = $feld[1];
		$val_datum = $feld[2];
		$v_zweck = $feld[3].' '.$feld[4].' '.$feld[5];
		
		
		
		
		
		$btext = $feld[6];
		$betrag = nummer_komma2punkt($feld[7]);
		
		$auftraggeber_text = $feld[8].' '.$feld[9];
		$auftraggeber_ktonr = $feld[10];
		$auftraggeber_blz = $feld[11];
		
		
		#echo $array[$a].'<br>';
		#echo "<tr><td>$auszugsnr</td><td>$buch_datum</td><td>$val_datum</td><td>$v_zweck</td><td>$btext</td><td>$betrag</td></tr>";
		echo "<tr><td>$val_datum</td><td>$v_zweck</td><td>$auftraggeber_text<br>$auftraggeber_ktonr<br>$auftraggeber_blz<td>$betrag</td><td>";
		$bb->dropdown_kostenrahmen_nr('Kostenkonto', 'kostenkonto', 'GELDKONTO', $geldkonto_id, '');
		echo "</td>";
		if($a==$start){
		
		$_zahlen_arr = gib_zahlen($v_zweck);
		if(is_array($_zahlen_arr)){
			$anz_m = count($_zahlen_arr);
			for($m=0;$m<$anz_m;$m++){
				$re = new rechnung();
				if(is_array($re->rechnung_finden_nach_rnr($_zahlen_arr[$m]))){
				$rnr_kurz = $_zahlen_arr[$m];
				}
			}
		}	
			#echo $rnr_kurz;
		if(empty($rnr_kurz)){
			$rnr_kurz = null;
		}	
		echo "<td>";
		if($betrag>0){
		$bb->dropdown_ra_buch('Partner',$partner_id,200, $rnr_kurz);
		}else{
		$bb->dropdown_re_buch('Partner',$partner_id,200, $rnr_kurz);	
		}
		echo "</td><td>";
		$f = new formular();
		$f->button_js('snd_buchen', 'buchen', null);
		echo "</td>";
		}
		
		echo "</tr>";
	}
	break;
	
	case "einnahmen_ausgaben":
		$b = new buchen;
		$b->einnahmen_ausgaben(555, 2013);
	
	break;
	
	
	case "excel_buchen":
		$_SESSION['umsatz_id_temp'] = 0;
		
		#echo '<pre>';
		#print_r($_SESSION);
		
		$sep = new sepa;

		
		
		/*if(isset($_SESSION['umsaetze_ok']) or isset($_SESSION['umsaetze_nok'])){
			#darstellung session
		echo "SESSION-VORHANDEN";
		break;//abbruch solange daten in session
		}*/
				
						
		if(isset($_REQUEST['upload'])){
		unset($_SESSION['umsaetze_nok']);
		unset($_SESSION['umsaetze_ok']);
		unset($_SESSION['umsatz_konten']);
		unset($_SESSION['umsatz_stat']);
		unset($_SESSION['umsatz_konten_start']);
		unset($_SESSION['umsatz_id_temp']);
		unset($_SESSION['temp_kontostand']);
		unset($_SESSION['temp_datum']);
		unset($_SESSION['geldkonto_id']);
		unset($_SESSION['temp_kontoauszugsnummer']);
		unset($_SESSION['kos_typ']);
		unset($_SESSION['kos_id']);
				
		
		$sep->form_upload_excel_ktoauszug();
		#echo '<pre>';
		#print_r($_SESSION);
		}

	
		if($_FILES){
		require_once "classes/simplexlsx.class.php";
		$xlsx = new SimpleXLSX( $_FILES['file']['tmp_name'] );
		
		
		/*Kontostände abrufen*/
		$arr_konten = $xlsx->rows(4);
		#echo '<pre>';
		#print_r($arr_konten);
		$anz_konten = count($arr_konten);
		for($a=2;$a<$anz_konten;$a++){
			$kto_auszug_1 = $arr_konten[$a][3];
			if(!empty($kto_auszug_1)){
			$kto_nr = $arr_konten[$a][1];
			$ksa = str_replace('.','',$arr_konten[$a][7]);
			$kse = str_replace('.','',$arr_konten[$a][9]);
			#echo "$a. $kto_nr $kto_auszug_1 $ksa  $kse<br>";
			
			
			$ktnr_arr = explode('/', $arr_konten[$a][1]); //KTO BLZ
			$blz =  $ktnr_arr[0];
			$kto_full = $ktnr_arr[1];
					
			
			
			if(strpos($kto_full, 'EUR')){
				$kto_arr = explode('EUR', $kto_full);
				$kto = $kto_arr[0];
			}else{
				$kto = substr($kto_full,0,-3);
			}
			
			/*Suche nach KTO und BLZ*/
			$gk = new gk();
			$gk_id = $gk->get_geldkonto_id2($kto, $blz);
			/*Suche nach generierte IBAN */
			if(!$gk_id){
				$sep = new sepa();
				$IBAN = $sep->get_iban_bic($kto, $blz);
				$gk_id = $gk->get_geldkonto_id2($kto, $blz, $IBAN);
			}
			/*Nach Bezeichnung*/
			if(!$gk_id){
				$gk_id = $gk->get_geldkonto_id($arr_konten[$a][0]);
			}
			
			if($gk_id){
				$_SESSION['umsatz_stat'][$gk_id]['kontonr'] = $kto_nr;
				$_SESSION['umsatz_stat'][$gk_id]['auszug'] = $kto_auszug_1;
				$_SESSION['umsatz_stat'][$gk_id]['ksa'] = $ksa;
				$_SESSION['umsatz_stat'][$gk_id]['kse'] = $kse;
			
				
			##########
			
			
			
				if(!isset($_SESSION['umsatz_konten'])){
				$_SESSION['umsatz_konten'][] = $gk_id;
				}else{
					if(!in_array($gk_id, $_SESSION['umsatz_konten'])){
					$_SESSION['umsatz_konten'][] = $gk_id;
					}
				}
			}else{
				$bez = $arr_konten[$a][0];
				die(fehlermeldung_ausgeben("$bez $kto $blz $IBAN nicht gefunden!!!<br>Schreibweise prüfen!!!"));
			}	
			}
			
		}
		
		
		
		#print_r($_SESSION);
		#die();
		$arr = $xlsx->rows(5);
		if(is_array($arr)){
		$anz = count($arr);
		
		$tmp_konto_nr = '';
		for($a=2;$a<$anz;$a++){
			if(!empty($arr[$a][3])){//Kontoauszug
				
				$ktnr_arr = explode('/', $arr[$a][1]); //KTO BLZ
				$blz =  $ktnr_arr[0];
				
				$kto_full = $ktnr_arr[1];
				
								
				if(strpos($kto_full, 'EUR')){
					$kto_arr = explode('EUR', $kto_full);
					$kto = $kto_arr[0];
				}else{
					$kto = substr($kto_full,0,-3);
				}
				
				/*Suche nach KTO und BLZ*/
				$gk = new gk();
				$gk_id = $gk->get_geldkonto_id2($kto, $blz);
				/*Suche nach generierte IBAN */
				if(!$gk_id){
					$sep = new sepa();
					$IBAN = $sep->get_iban_bic($kto, $blz);
					$gk_id = $gk->get_geldkonto_id2($kto, $blz, $IBAN);
				}
				/*Nach Bezeichnung*/
				if(!$gk_id){
					$gk_id = $gk->get_geldkonto_id($arr[$a][0]);
				}
				
					if($gk_id){
					$arr[$a]['GK_ID']=$gk_id;
					$_SESSION['umsaetze_ok'][] = $arr[$a];
					
					/*Startdatensätze*/
					if($arr[$a][1]!=$tmp_konto_nr){
						$tmp_konto_nr = $arr[$a][1];
						$_SESSION['umsatz_konten_start'][$gk_id] = count($_SESSION['umsaetze_ok']);
					}
						
					}else{
						$_SESSION['umsaetze_nok'][] = $arr[$a];
					}
					
				
			}
		}
		if(is_array($_SESSION['umsaetze_nok']) or is_array($_SESSION['umsaetze_ok'])){
			weiterleiten('?daten=buchen&option=excel_buchen_session');
		}else{
			fehlermeldung_ausgeben("Keine Daten aus der Importdatei übernommen!");
		}
	}else{
		fehlermeldung_ausgeben("Keine Daten in der Importdatei");
	}
	#darstellung session
	}
	
	
	break;
	
	
	case "uebersicht_excel_konten":
	$sep = new sepa();
	$sep->uebersicht_excel_konten();
	break;
	
	case "excel_buchen_session":
#echo '<pre>';
#print_r($_SESSION);

		if(isset($_REQUEST['next'])){
			$_SESSION['umsatz_id_temp']++;
		}

		if(isset($_REQUEST['vor'])){
			$_SESSION['umsatz_id_temp']--;
		}
		
		$anz_ok = count($_SESSION['umsaetze_ok']);
		if($_SESSION['umsatz_id_temp']>=$anz_ok or $_SESSION['umsatz_id_temp']<0){
			$_SESSION['umsatz_id_temp'] = 0;
		}
		
		
	if(isset($_REQUEST['ds_id']) && is_numeric($_REQUEST['ds_id'])){
		if($_REQUEST['ds_id']>0 && $_REQUEST['ds_id']<=count($_SESSION['umsaetze_ok'])){
		$_SESSION['umsatz_id_temp'] = $_REQUEST['ds_id']-1;
		}else{
			$_SESSION['umsatz_id_temp'] = 0;
		}
		ob_clean();
		weiterleiten('?daten=buchen&option=excel_buchen_session');
	}
	
		
	if(!isset($_SESSION['umsatz_id_temp'])){
		$_SESSION['umsatz_id_temp'] = '0';
	}
	
	/*if(isset($_REQUEST['next'])){
		$next = $_SESSION['umsatz_id_temp']+1;
		$anz_ok = count($_SESSION['umsaetze_ok']);
		if($next<$anz_ok){
		$_SESSION['umsatz_id_temp'] = $next;
		}
	}
	
	if(isset($_REQUEST['vor'])){
		$vor = $_SESSION['umsatz_id_temp']-1;
		$anz_ok = count($_SESSION['umsaetze_ok']);
		if($vor<$anz_ok && $vor>-1){
			$_SESSION['umsatz_id_temp'] = $vor;
		}
	}*/
	
	$sep = new sepa();
	$sep->status_excelsession();
	$sep->form_excel_ds($_SESSION['umsatz_id_temp']);
	$bu = new buchen;
	$bu->buchungsjournal_auszug($_SESSION['geldkonto_id'], $_SESSION['temp_kontoauszugsnummer']);
	
	
	break;
	
	case "excel_buchen_ALT":
		#print_r($_SESSION);
		if(!$_FILES){
			echo '<h1>Upload</h1>
		<form method="post" enctype="multipart/form-data">
		*.XLSX <input type="file" name="file"  />&nbsp;&nbsp;<input type="submit" value="Parse" />
		</form>';
		}else{
				
		require_once "classes/simplexlsx.class.php";
		$xlsx = new SimpleXLSX( $_FILES['file']['tmp_name'] );
		
		echo '<pre>';
		#print_r($xlsx->rows(5));
		
		
		echo '<h1>Parsing Result</h1>';
		
		echo "<table border=\"1\" cellpadding=\"3\" style=\"border-collapse: collapse\">";
		$arr = $xlsx->rows(5);
		$anz = count($arr);
		for($a=2;$a<$anz;$a++){
			if(empty($arr[$a][3])){//Kontoauszug
				#echo $arr[$a][0]."<br>";
			}else{
				echo "<tr><td>";
				echo $arr[$a][0]."<br>";//Kontobez
				$gk = new gk();
				#$gk_id = $gk->get_geldkonto_id($arr[$a][0]);
				#echo "<b>$gk_id</b>";
				echo "</td><td>";
				$ktnr_arr = explode('/', $arr[$a][1]); //KTO BLZ
				$blz =  $ktnr_arr[0];
				
				$kto_full = $ktnr_arr[1];
				if(strpos($kto_full, 'EUR')){
					$kto_arr = explode('EUR', $kto_full);
					$kto = $kto_arr[0]; 
				}else{
					$kto = substr($kto_full,0,-3);
				}
				
				
				
				$gk_id = $gk->get_geldkonto_id2($kto, $blz);
				if(!$gk_id){
					$sep = new sepa();
					$IBAN = $sep->get_iban_bic($kto, $blz);
					#$kto = substr($ktnr_arr[1],0,-3);
					$gk_id = $gk->get_geldkonto_id2($kto, $blz, $IBAN);
				}
				
				if(!$gk_id){
					$gk_id = $gk->get_geldkonto_id($arr[$a][0]);
					if(!$gk_id){
						echo "Kein Konto mit BEZ ".$arr[$a][0]."<br>"; 
						echo "$kto $blz ".$arr[$a][0]." prüfen!!!";
					}
				}
				if($gk_id){
					echo $gk_id;
				}
				
				
				
				#echo "</td><td>";
				#echo "$kto $blz<br>";
				echo "</td><td>";
				echo $arr[$a][6]."<br>";//DatumVALUTE
				$datum = $arr[$a][6];
				echo "</td><td>";
				$auszug = sprintf('%01d',$arr[$a][3]);
				echo "$auszug<br>";//Auszug
				echo "</td><td>";
		
		
		
				$betrag = str_replace('.','', $arr[$a][7]);
				echo $betrag."<br>";//BETRAG
				echo "</td><td>";
				#echo $arr[$a][13]."<br>";//Buchungstext
				echo "</td><td>";
				#echo $arr[$a][14]."<br>";//vZweck###############################################
				
				$pos_svwz = strpos(strtoupper($arr[$a][14]), 'SVWZ+');
				if($pos_svwz==true){
					
					#echo $arr[$a][14]."<br>";//vZweck###############################################
					#$arr[$a][14] = substr($arr[$a][14],$pos_svwz+5); 
				}
				
				echo $arr[$a][14]."<br>";//vZweck###############################################
				#SVWZ+
				
				
				/*Suche nach SEPA-Dateien*/
				
				/*LASTSCHRIFTEN MIETEN UND HAUSGELD*/
				if($gk_id){
				
				if($arr[$a][13]=='SEPA-LS SAMMLER-HABEN'){
					echo "<b>SEPA-LASTSCHRIFT-SAMMLER</b>";
					$sep = new sepa();
					$ls_arr = $sep->get_sepa_lsfiles_arr_gk($gk_id);
					if(is_array($ls_arr)){
						$anz_ls = count($ls_arr);
						$z = 0;
						for($ls=0;$ls<$anz_ls;$ls++){
							$summe = nummer_punkt2komma($ls_arr[$ls]['SUMME']);
							$datei = $ls_arr[$ls]['DATEI'];
							if($summe==$betrag){
							$z++;
								echo "<hr>$z. <a href=\"index.php?daten=sepa&option=ls_auto_buchen_file&datei=$datei\">AUTOBUCHEN $datei $summe</a>";
							}
						}
					}
				}
				/*ÜBERWEISUNGSAMMLER SEPA*/
				if($arr[$a][13]=='SEPA-UEBERWEIS.SAMMLER-SOLL'){
					echo "<b>SEPA-ÜBERWEISUNG SAMMLER</b>";
					$sep = new sepa();
					$ue_arr = $sep->sepa_files_arr($gk_id);
					if(is_array($ue_arr)){
						#print_r($ue_arr);
						$anz_ue = count($ue_arr);
						$z = 0;
						for($ls=0;$ls<$anz_ue;$ls++){
							$summe = nummer_punkt2komma($ue_arr[$ls]['SUMME']);
							$datei = $ue_arr[$ls]['FILE'];
							if($summe==($betrag*-1)){
								$z++;
								echo "<hr>$z. <a href=\"index.php?daten=sepa&option=excel_ue_autobuchen&datei=$datei&auszug=$auszug&gk_id=$gk_id&datum=$datum\">AUTOBUCHEN $datei $summe</a>";
								#excel_ue_autobuchen
							}
							}
							}
				
				}
				
				/*EINZELÜBERWEISUNGEN HABEN*/
				if($arr[$a][13]=='SEPA-UEBERWEIS.HABEN EINZEL'){
					echo "<b>EINNAHME EINZELBUCHUNG</b><br>";
					/*Mietzahlungen*/
					if(strpos(strtolower($arr[$a][14]), 'miete')){
						echo "--MIETE--";
					
					}
					
					
					if(strpos(strtolower($arr[$a][14]), 'hausgeld') or strpos(strtolower($arr[$a][14]), 'wohngeld')){
						echo "--HAUSGELD--";
					}
					
					$pos_svwz = strpos(strtoupper($arr[$a][14]), 'SVWZ+');
					if($pos_svwz==true){
							
						echo $arr[$a][14]."<br>";//vZweck###############################################
						$arr[$a][14] = substr($arr[$a][14],$pos_svwz+5);
					}
					echo $arr[$a][14]."<br>";//vZweck###############################################
				}
				
				/*EINZELÜBERWEISUNGEN DAUERAUFTRAG HABEN*/
				if($arr[$a][13]=='SEPA Dauerauftragsgutschrift'){
					echo "<b>DAUERAUFTRAG EINNAHME EINZELBUCHUNG</b>";
						
				}
				
				/*EINZELABBUCHUNG LASTSCHRIFFT SOLL*/
				if($arr[$a][13]=='SEPA DIRECT DEBIT (EINZELBUCHUNG-SOLL, B2B)'){
					echo "<b>B2B ABBUCHUNG EINZELN</b>";
				
				}
				
				/*EINZELABBUCHUNG LASTSCHRIFFT SOLL*/
				if($arr[$a][13]=='SEPA-LS EINZELBUCHUNG SOLL'){
					echo "<b>SEPA-LS ABBUCHUNG EINZELN</b>";
				
				}
				
				
				
				
				
				
				
				
				
				
				
				}//ENDE IF GK VORHANDEN
				
				echo "</td><td>";
				echo $arr[$a][25]."<br>";//Ktoinh
				echo "</td><td>";
				echo $arr[$a][26]."<br>";//IBAN
				echo $arr[$a][27]."<br>";//BIC
				echo "</td><td>";
		
				echo "</td><tr>";
			}
		
		
		}
		echo "</table>";
		}
		
		
		
		
		
	break;
	
	case "excel_einzelbuchung":
	#echo '<pre>';
	#print_req();
	#print_r($_SESSION);
	$kostentraeger_typ = $_POST['kostentraeger_typ'];
	$kostentraeger_id = $_POST['kostentraeger_id'];
	$kto_auszugsnr = $_SESSION['temp_kontoauszugsnummer'];
	$datum = date_german2mysql($_SESSION['temp_datum']);
	$betrag = nummer_komma2punkt($_POST['betrag']);
	$kostenkonto = $_POST['kostenkonto'];
	$vzweck = mysql_real_escape_string($_POST['text']);
	$geldkonto_id = $_SESSION['geldkonto_id'];
	$rechnungsnr = $kto_auszugsnr;
	
	if($_POST['mwst']){
		$mwst = $betrag/119*19;
	}else{
		$mwst = '0.00';
	}
	
	#die();
	$bu = new buchen;
	$bu->geldbuchung_speichern($datum, $kto_auszugsnr, $rechnungsnr, $betrag, $vzweck, $geldkonto_id, $kostentraeger_typ, $kostentraeger_id, $kostenkonto, $mwst);
	
	#weiterleiten_in_sec('?daten=buchen&option=excel_buchen_session', 1);
	weiterleiten('?daten=buchen&option=excel_buchen_session');
	break;
	
	
	case "sepa_ue_autobuchen":
		if(isset($_POST)){
			if(!isset($_SESSION['geldkonto_id'])){
				fehlermeldung_ausgeben("Geldkonto wählen");
				die();
			}
				
			if(!isset($_SESSION['temp_kontoauszugsnummer'])){
				fehlermeldung_ausgeben("Kontrolldatein eingeben Kontoauszugsnummer!");
				die();
			}
			if(!isset($_SESSION['temp_datum'])){
				fehlermeldung_ausgeben("Kontrolldatein eingeben Buchungsdatum!");
				die();
			}
			if(isset($_POST['mwst'])){
				$mwst = 1;
			}else{
				$mwst = '0';
			}
			$file = $_POST['file'];
			$sep = new sepa();
			$sep->sepa_file_autobuchen($file, $_SESSION['temp_datum'], $_SESSION['geldkonto_id'], $_SESSION['temp_kontoauszugsnummer'], $mwst);
			weiterleiten('?daten=buchen&option=excel_buchen_session');
		}else{
			fehlermeldung_ausgeben("Fehler beim Verbuchen EC232");
		}
	break;
	
	
	case "excel_ls_sammler_buchung":
		#echo '<pre>';
		#print_req();
		hinweis_ausgeben("Bitte warten....3..2...1.");
		$ls_file = $_REQUEST['ls_file'];
		$s = new sepa();
		$s->form_ls_datei_ab($ls_file);
		
		weiterleiten_in_sec('?daten=buchen&option=excel_buchen_session',3);
	break;
	
	case "excel_nok":
		$gesamt = count($_SESSION['umsaetze_nok']);
		for($a=0;$a<$gesamt;$a++){
			$kto_bez = $_SESSION['umsaetze_nok'][$a][0];
			$kto = $_SESSION['umsaetze_nok'][$a][1];
			echo "$kto_bez - $kto<br>";
		}
		break;
	
	case "objekte_anz_einh":
	$o = new objekt();
	$o_arr = $o->liste_aller_objekte_kurz();
	$anz = count($o_arr);
	echo "<table class=\"sortable\">";
	echo "<tr><td>OBJEKT</td><td>ANZAHL EINHEITEN</td></tr>";
	for($a=0;$a<$anz;$a++){
		$objekt_id = $o_arr[$a]['OBJEKT_ID'];
		$objekt_kn = $o_arr[$a]['OBJEKT_KURZNAME'];
		$anz_einheiten = $o->anzahl_einheiten_objekt($objekt_id);
		
		echo "<tr><td>$objekt_kn</td><td>$anz_einheiten</td></tr>";
	}
	echo "</table>";
	break;

}//end switch

/*
 * // Wir werden eine PDF Datei ausgeben
header('Content-type: application/pdf');

// Es wird downloaded.pdf benannt
header('Content-Disposition: attachment; filename="downloaded.pdf"');

// Die originale PDF Datei heißt original.pdf
readfile('original.pdf');
 */
?>
