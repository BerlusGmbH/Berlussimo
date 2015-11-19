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
 * @filesource   $HeadURL: http://192.168.2.52/svn/berlussimo_1/tags/02.11.2010 - Downloadversion 0.27/options/modules/kasse.php $
 * @version      $Revision: 6 $
 * @modifiedby   $LastChangedBy: sivac $
 * @lastmodified $Date: 2010-09-21 10:34:50 +0200 (Di, 21 Sep 2010) $
 * 
 */
 
include_once("includes/allgemeine_funktionen.php");
/*Überprüfen ob Benutzer Zugriff auf das Modul hat*/
if(!check_user_mod($_SESSION[benutzer_id], 'kasse')){
	echo '<script type="text/javascript">';
	echo "alert('Keine Berechtigung')";
	echo '</script>';	
	die();
}


include_once("options/links/links.kasse.php");
include_once("classes/mietkonto_class.php");
include_once("classes/berlussimo_class.php");
include_once("classes/kasse_class.php");
include_once("classes/class_buchen.php");
$kassen_info = new kasse;
$kassen_info->kassen_auswahl();



$option = $_REQUEST["option"];
switch($option) {
   
    
	case "rechnung_an_kasse_erfassen":
   	$form = new mietkonto;
   	$rechnungsformular = new rechnung;
    $rechnungsformular->form_rechnung_erfassen_an_kasse();
   	$form->ende_formular();
    break;
    
    
    case "rechnung_erfassen1":
   	$form = new mietkonto;
   	$form->erstelle_formular("Rechnungsdaten überprüfen", NULL);
   	echo "<p><b>Eingegebene Rechnungsdaten:</b></p>";
   	$clean_arr = post_array_bereinigen();
   	#$form->array_anzeigen($clean_arr);
   	foreach ($clean_arr as $key => $value) {
    	if(($key != 'submit_rechnung1') AND ($key != 'option')){
    	#echo "$key " . $value . "<br>";
  		$form->hidden_feld($key, $value);
  		}
   	}
   	if($clean_arr[Aussteller_typ] == $clean_arr[Empfaenger_typ] && $clean_arr[Aussteller] == $clean_arr[Empfaenger] ){
   		$fehler = true;
   		fehlermeldung_ausgeben("Rechnungsaussteller- und Empfänger sind identisch.<br>");
   	}
   	
   	if(!$fehler){
   		if($clean_arr[Empfaenger_typ] == 'Kasse'){
   		$kassen_info = new kasse;
   		$kassen_info->get_kassen_info($clean_arr[Empfaenger]);
   		$partner_info = new partner;
   		$aussteller = $partner_info->get_partner_name($clean_arr[Aussteller]);
   		$empfaenger = "".$kassen_info->kassen_name." - ".$kassen_info->kassen_verwalter."";	
   		}
   		if($clean_arr[Empfaenger_typ] == 'Partner'){
   		$partner_info = new partner;
   		$aussteller = $partner_info->get_partner_name($clean_arr[Aussteller]);
   		$empfaenger = $partner_info->get_partner_name($clean_arr[Empfaenger]);
   		}
   		echo "Rechnung von: <b>$aussteller</b> an <b>$empfaenger</b> vom $clean_arr[rechnungsdatum]<br>";
   		echo "Rechnungsnummer: $clean_arr[rechnungsnummer]<br>";
   		echo "Eingangsdatum: $clean_arr[eingangsdatum]<br>";
   		if (preg_match("/,/i", $clean_arr['nettobetrag'])) {
   		$clean_arr[nettobetrag] = nummer_komma2punkt($clean_arr[nettobetrag]);
   		}
   		if (preg_match("/,/i", $clean_arr['bruttobetrag'])) {
   		$clean_arr[bruttobetrag] = nummer_komma2punkt($clean_arr[bruttobetrag]);
   		}
   		if (preg_match("/,/i", $clean_arr['skontobetrag'])) {
   		$clean_arr[skontobetrag] = nummer_komma2punkt($clean_arr[skontobetrag]);
   		}
   		
   		$netto_betrag_komma =   nummer_punkt2komma($clean_arr[nettobetrag]); 		
   		$brutto_betrag_komma =   nummer_punkt2komma($clean_arr[bruttobetrag]);
   		$skonto_betrag_komma =   nummer_punkt2komma($clean_arr[skontobetrag]);
   		echo "Nettobetrag: $netto_betrag_komma €<br>";
   		echo "Bruttobetrag: $brutto_betrag_komma €<br>";
   		echo "Skontobetrag: $skonto_betrag_komma €<br>";
   		echo "Skonto in %: $clean_arr[skonto] %<br>";
   		$skonto_satz = $clean_arr[skonto];
   		$ein_prozent = ($clean_arr[bruttobetrag] / 100);
   		$skonto_in_eur = $ein_prozent * $skonto_satz;
   		$skonto_in_eur_komma = nummer_punkt2komma($skonto_in_eur);
   		$skontobetrag_errechnet = $clean_arr[bruttobetrag] - $skonto_in_eur;
   		$skontobetrag_errechnet_komma = nummer_punkt2komma($skontobetrag_errechnet); 
   		echo "Fällig am: $clean_arr[faellig_am] <br>";
   		echo "Kurzbeschreibung: $clean_arr[kurzbeschreibung] <br>";
   		echo "<hr><b>Errechnete Daten:</b><br>Skonto in €: $skonto_in_eur_komma  €<br>";
   		echo "Skontobetrag errechnet: $skontobetrag_errechnet_komma €<br>";
   		
   		$form->hidden_feld("option", "rechnung_erfassen2");
   		$form->send_button("submit_rechnung2", "Rechnung speichern");
   		echo "<br>";
   		backlink();	
   		}else{
   			backlink();	
   	}
   	$form->ende_formular();
    break;
    
    case "rechnung_erfassen2":
   	$form = new mietkonto;
   	$form->erstelle_formular("Rechnungsdaten werden gespeichert", NULL);
   	echo "<p><b>Gespeicherte Rechnungsdaten:</b></p>";
   	$clean_arr = post_array_bereinigen();
   	#$form->array_anzeigen($clean_arr);
   	$rechnung = new rechnung;
   	$rechnung->rechnung_speichern($clean_arr);
   	$form->ende_formular();
    break;

   

	case "buchungsmaske_kasse":
   	$form = new mietkonto;
   	$form->erstelle_formular("Buchungsformular Kasse $_SESSION[kasse]", NULL);
   	$kasse =  new kasse;
   	$kasse->buchungsmaske_kasse($_SESSION[kasse]);
   	$form->ende_formular();
    break;

	case "kassendaten_gesendet":
   	$form = new mietkonto;
   	$form->erstelle_formular("Buchungsdaten überprüfen $_SESSION[kasse]", NULL);
   	$kasse =  new kasse;
   	#print_r($_POST);
   	echo "<b>Gesendete Daten:</b><br>";
   	echo "Kasse: $_POST[kassen_id]<br>";
   	echo "Datum: $_POST[datum]<br>";
   	echo "Zahlungstyp: $_POST[zahlungstyp]<br>";
   	echo "Betrag: $_POST[betrag]<br>";
   	echo "Beleg/Text: $_POST[beleg_text]<br>";
   	$form->hidden_feld("kassen_id", $_POST[kassen_id]);
   	$form->hidden_feld("datum", $_POST[datum]);
   	$form->hidden_feld("zahlungstyp", $_POST[zahlungstyp]);
   	$form->hidden_feld("betrag", $_POST[betrag]);
   	$form->hidden_feld("beleg_text", $_POST[beleg_text]);
   	$form->hidden_feld("kostentraeger_typ", $_POST[kostentraeger_typ]);
   	$form->hidden_feld("kostentraeger_id", $_POST[kostentraeger_id]);
   	$form->hidden_feld("beleg_text", $_POST[beleg_text]);
   	$form->hidden_feld("option", "kassendaten_speichern");   	
   	$form->send_button("submit", "Speichern");
   	$form->ende_formular();
    break;

	case "kassendaten_speichern":
   	$form = new mietkonto;
   	$form->erstelle_formular("Buchungsdaten speichern $_SESSION[kasse]", NULL);
   	$kasse =  new kasse;
	$kasse->speichern_in_kassenbuch($_POST[kassen_id], $_POST[betrag], $_POST[datum], $_POST[zahlungstyp], $_POST[beleg_text],$_POST[kostentraeger_typ],$_POST[kostentraeger_id]);
	$form->ende_formular();
	break;

	case "kassenbuch":
   	$form = new mietkonto;
   	if(empty($_REQUEST[jahr])){
   	$jahr = date("Y");
   	}else{
   	$jahr = $_REQUEST[jahr];
   	}
   	$form->erstelle_formular("Kassenbuch der Kasse $_SESSION[kasse] für das Jahr $jahr", NULL);
   	$vorjahr = $jahr - 1;
   	$jahr_aktuell =date("Y");
   	$kassen_id = $_SESSION[kasse];
   	echo "<a href=\"?daten=kasse&kasse=$kassen_id&option=kassenbuch&jahr=$jahr_aktuell\">Kassenbuch aktuell</a>&nbsp;";
   	echo "<a href=\"?daten=kasse&kasse=$kassen_id&option=kassenbuch&jahr=$vorjahr\">Kassenbuch $vorjahr</a>&nbsp;";
   	echo "<a href=\"?daten=kasse&option=kassenbuch_xls&jahr=$jahr\">Exceldatei</a>&nbsp;<hr>";
   	$g = new berlussimo_global;
   	$link = "?daten=kasse&kasse=$kassen_id&option=kassenbuch";
   	$g->monate_jahres_links($jahr, $link);
   	$kasse =  new kasse;
	$monat = $_REQUEST[monat];
	if(!$monat){
		#$monat = date("m");
	$kasse->kassenbuch_anzeigen($jahr, $_SESSION[kasse]);
	}
	#$kasse->kassenbuch_anzeigen($jahr, $_SESSION[kasse]);
	$kasse->monatskassenbuch_anzeigen($monat, $jahr, $_SESSION[kasse]);
	$form->ende_formular();
	break;
	
	case "kassenbuch_xls":
   	$form = new mietkonto;
   	if(empty($_REQUEST[jahr])){
   	$jahr = date("Y");
   	}else{
   	$jahr = $_REQUEST[jahr];
   	}
   	$vorjahr = $jahr - 1;
   	$jahr_aktuell =date("Y");
   	$kasse =  new kasse;
	$kasse->kassenbuch_als_excel($jahr, $_SESSION[kasse]);
	$form->ende_formular();
	break;
	

	case "kasseneintrag_aendern":
	$form = new mietkonto;
   	$jahr = date("Y");
   	$form->erstelle_formular("Kassenbuch der Kasse $_SESSION[kasse] für das Jahr $jahr", NULL);
	$kasse =  new kasse;
	$kasse->buchungsmaske_kasse_aendern($_REQUEST[eintrag_dat]);
	$form->ende_formular();
	break;

	case "kassendaten_aendern":
	$k = new kasse;
	$k->kassenbuch_dat_deaktivieren($_POST[kassen_dat_alt]);
	$k->speichern_in_kassenbuch_id($_POST[kassen_id], $_POST[betrag], $_POST[datum], $_POST[zahlungstyp], $_POST[beleg_text],$_POST[kostentraeger_typ],$_POST[kostentraeger_id],$_POST[kassen_buch_id]);
	break;
	
	case "kasseneintrag_loeschen":
	$k = new kasse;
	$k->kassenbuch_dat_deaktivieren($_REQUEST[eintrag_dat]);
	weiterleiten_in_sec('?daten=kasse&kasse=1&option=kassenbuch','1');
	break;
}


?>
