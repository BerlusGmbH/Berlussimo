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
 * @filesource   $HeadURL: http://192.168.2.52/svn/berlussimo_1/tags/02.11.2010 - Downloadversion 0.27/options/modules/buchungsmaske.php $
 * @version      $Revision: 6 $
 * @modifiedby   $LastChangedBy: sivac $
 * @lastmodified $Date: 2010-09-21 10:34:50 +0200 (Di, 21 Sep 2010) $
 * 
 */

include_once("includes/allgemeine_funktionen.php");

/*Überprüfen ob Benutzer Zugriff auf das Modul hat*/
if(!check_user_mod($_SESSION['benutzer_id'], 'miete_buchen')){
	echo '<script type="text/javascript">';
	echo "alert('Keine Berechtigung')";
	echo '</script>';	
	die();
}

include_once("includes/formular_funktionen.php");
include_once("classes/mietkonto_class.php");
include_once("classes/berlussimo_class.php");
include_once("options/links/links.mietkonten_blatt.php");
include_once("classes/mietzeit_class.php");
include_once("classes/class_mietvertrag.php");
if(isset($_REQUEST["daten"])){
$daten = $_REQUEST["daten"];
}
if(isset($_REQUEST["schritt"])){
$schritt = $_REQUEST["schritt"];
}else{
$schritt = '';	
}
/*Mieterinformationen über die Buchungsformulare anzeigen*/
if(isset($_REQUEST['mietvertrag_id']) OR !empty($_REQUEST['mietvertrag_id'])) {
$mieter_info = new mietkonto;
$mieter_info->erstelle_formular("Mieterinformationen", NULL);
$mieter_info->mieter_informationen_anzeigen($_REQUEST['mietvertrag_id']);
$mieter_info->ende_formular();
}


switch($schritt) {

    #################
    case "buchungsauswahl":
	$form = new mietkonto;
    $form->erstelle_formular("Buchungsart auswählen", NULL);
	if(isset($_REQUEST['mietvertrag_id']) && !empty($_REQUEST['mietvertrag_id'])){
	/*MAHNSPERRE*/
		$dd = new detail();
		$mahnsperre = $dd->finde_detail_inhalt('MIETVERTRAG', $_REQUEST['mietvertrag_id'], 'Mahnsperre');
		if(!empty($mahnsperre)){
		hinweis_ausgeben("<h1>Mahnsperre: Grund: $mahnsperre Bitte unbedingt die Mahnungsabteilung über Zahlung mündlich informieren</h1>");	
		}
		
	$mietvertrag_id = $_REQUEST['mietvertrag_id'];	
	$buchung = new mietkonto;
    /*$geldkonto_info = new geldkonto_info;
    $geldkonto_info->geld_konten_ermitteln('Mietvertrag', $mietvertrag_id);*/
    $geld = new geldkonto_info;
	$kontostand_aktuell =nummer_punkt2komma($geld->geld_konto_stand($_SESSION['geldkonto_id']));
	if(isset($_SESSION['temp_kontostand']) && isset($_SESSION['temp_kontoauszugsnummer'])){
	$kontostand_temp = nummer_punkt2komma($_SESSION['temp_kontostand']);
	echo "<h3>Kontostand am $_SESSION[temp_datum] laut Kontoauszug $_SESSION[temp_kontoauszugsnummer] war $kontostand_temp €</h3>";	
	}else{
	echo "<h3 style=\"color:red\">Kontrolldaten zum Kontoauszug fehlen</h3>";
	echo "<h3 style=\"color:red\">Weiterleitung erfolgt</h3>";
	weiterleiten_in_sec("?daten=buchen&option=kontoauszug_form", 1);	
	}
	if($kontostand_aktuell == $kontostand_temp){
	echo "<h3>Kontostand aktuell: $kontostand_aktuell €</h3>";
	}else{
		echo "<h3 style=\"color:red\">Kontostand aktuell: $kontostand_aktuell €</h3>";
	}
	
	$buchung->buchungsauswahl($mietvertrag_id);
	}else{
	//fals keine MV_ID eingegeben wurde, weiterleiten
	warnung_ausgeben("Fehler : Bitte eine Einheit auswählen!");
		weiterleiten("?daten=miete_buchen");
	}
    $form->ende_formular();
    break;
    
    #################
    case "auto_buchung":
	/*Automatisches Buchen der Miete wird
	 * durch klicken auf Button suhbmit_buchen1 ausgelöst*/
	
	if(check_datum ($_POST[buchungsdatum])){
	$buchungsdatum = date_german2mysql($_POST[buchungsdatum]);
		if("".$_POST[ZAHLBETRAG]."" == 0){
		warnung_ausgeben("Die Miete ist in der Mietentwicklung nicht definiert!");
		warnung_ausgeben("Sie werden um einen Schritt zurückversetzt!");
		weiterleiten_in_sec('javascript:history.back();', 5);	
		die();
	}else{
	
	/*Buchungsprozedur*/
	$buchen = new mietkonto;
	$mwst_anteil = $_POST['MWST_ANTEIL'];
	$buchen->miete_zahlbetrag_buchen($_POST[kontoauszugsnr], $_POST[MIETVERTRAG_ID], $buchungsdatum, $_POST[ZAHLBETRAG], $_POST[bemerkung], $_POST[geld_konto], $mwst_anteil);
	}
	
		}	else{
			warnung_ausgeben("Datumsformat nicht korrekt!");
			warnung_ausgeben("Sie werden um einen Schritt zurückversetzt!");
			weiterleiten_in_sec('javascript:history.back();', 3);
			}
	break;
    /*Ende Case*/
    
    
    /*Case für die manuelle Buchung bzw. Buchung eines anderen Betrages */
    case "manuelle_buchung":
	$mietvertrag_id = $_POST[MIETVERTRAG_ID];
    $buchung = new mietkonto;
    $zahlbetrag = $buchung->nummer_komma2punkt($_POST[ZAHLBETRAG]);
    $geld_konto_id = $_POST[geld_konto];
    $zahlbetrag = number_format($zahlbetrag, 2, ".", "");
    $summe_forderung_monatlich = $buchung->summe_forderung_monatlich($mietvertrag_id, $buchung->monat_heute, $buchung->jahr_heute);	
    
    if(empty($_POST[ZAHLBETRAG])){
    	warnung_ausgeben("Bitte geben Sie einen Betrag bzw. Zahl ein!");
		warnung_ausgeben("Sie werden um einen Schritt zurückversetzt!");
		weiterleiten_in_sec('javascript:history.back();', 5);	
	    }
	
    elseif(!is_numeric($zahlbetrag)){
    	warnung_ausgeben("Bitte geben Sie eine Zahl als Betrag ein!");
		warnung_ausgeben("Sie werden um einen Schritt zurückversetzt!");
		weiterleiten_in_sec('javascript:history.back();', 5);	
	    }
    
    else{
    
    	$zahlbetrag = $buchung->nummer_komma2punkt($_POST[ZAHLBETRAG]);
	
/*Den Zahlbetrag und die Summe der Forderungen auf zwei Nachkommastellen formatieren*/
	$zahlbetrag = number_format($zahlbetrag, 2, ".", "");	
	$summe_forderung_monatlich = number_format($summe_forderung_monatlich, 2, ".", "");
    if($summe_forderung_monatlich == 0){
		$summe_forderung_monatlich = $buchung->summe_forderung_aus_vertrag($mietvertrag_id);
	}
/*Regelung für die Funktionsaufrufe abhängig vom eingegebenen Zahlbetrag*/
		if($zahlbetrag == $summe_forderung_monatlich){
    	$buchung->buchungsmaske_manuell_gleicher_betrag($mietvertrag_id, $geld_konto_id);	
    	}
		if($zahlbetrag > $summe_forderung_monatlich){
		$buchung->buchungsmaske_manuell_gross_betrag($mietvertrag_id, $geld_konto_id);
    	}
    	if($zahlbetrag < $summe_forderung_monatlich && $zahlbetrag>0){
    	$buchung->buchungsmaske_manuell_kleiner_betrag($mietvertrag_id, $geld_konto_id);	
    	}
    	/*Negativ buchung*/
    	if($zahlbetrag < $summe_forderung_monatlich && $zahlbetrag<0){
    	$buchung->buchungsmaske_manuell_negativ_betrag($mietvertrag_id, $geld_konto_id);	
    	}
    }
    break;
    /*Ende Case*/
    
    #################
    case "manuelle_buchung3":
	/*Buchen der Miete wird durch klicken auf Button submit_buchen3 ausgelöst*/
	if(check_datum ($_POST[buchungsdatum])){
	$buchungsdatum = date_german2mysql($_POST[buchungsdatum]);
	$buchen = new mietkonto;
	/*Buchungsprozedur*/
	$buchen = new mietkonto;
	$buchen->miete_zahlbetrag_buchen($_POST[kontoauszugsnr], $_POST[MIETVERTRAG_ID], $buchungsdatum, $_POST[ZAHLBETRAG], $_POST[bemerkung], $_POST[geld_konto]);
	}	else{
			warnung_ausgeben("Datumsformat nicht korrekt!");
			warnung_ausgeben("Sie werden um einen Schritt zurückversetzt!");
			weiterleiten_in_sec('javascript:history.back();', 5);
			}
	break;	
    
    #################
    case "manuelle_buchung4":
	/*Kontonummer des Objektes finden, soll optimiert werden,  da die MV_ids in der Adresse geändert werden können, und die Kontonummer bleibt die gleiche, obwohl der MV vielleicht einem anderen Objekt gehört, erledigt, testen*/
	
	$mietvertrag_id = $_POST[MIETVERTRAG_ID];
    $buchung = new mietkonto;
    $buchungsdatum = $buchung->date_german2mysql($_POST[buchungsdatum]);
    $summe_forderung_monatlich = $buchung->summe_forderung_monatlich($mietvertrag_id, $buchung->monat_heute, $buchung->jahr_heute);	
    $zahlbetrag = $buchung->nummer_komma2punkt($_REQUEST[ZAHLBETRAG]);
	/*Den Zahlbetrag und die Summe der Forderungen auf zwei Nachkommastellen formatieren*/
	$zahlbetrag = number_format($zahlbetrag, 2, ".", "");	
	$summe_forderung_monatlich = number_format($summe_forderung_monatlich, 2, ".", "");
	#echo "ZB: $zahlbetrag SUMME-F:$summe_forderung_monatlich";
	
		
	/*Buchungsprozedur inkl. interne Buchung*/
	$buchen = new mietkonto;
	$buchen->miete_zahlbetrag_buchen($_POST[kontoauszugsnr], $_POST[MIETVERTRAG_ID], $buchungsdatum, $_POST[ZAHLBETRAG], $_POST[bemerkung], $_POST[geld_konto]);
		/*$buchung->miete_zahlbetrag_buchen($_POST[kontoauszugsnr], $_POST[MIETVERTRAG_ID], $buchungsdatum, $zahlbetrag, $objekt_kontonummer, $_POST[bemerkung]);
		$buchungsnummer = $buchung->letzte_buchungsnummer($_POST[MIETVERTRAG_ID]);
		$buchung->intern_buchen($_POST[MIETVERTRAG_ID], $buchungsnummer);
		$betrag = $buchung->nummer_punkt2komma($_POST[ZAHLBETRAG]);
		hinweis_ausgeben("Zahlbetrag von $betrag € wurde wie erwartet verbucht.");
		weiterleiten_in_sec('?daten=miete_buchen', 3);
	*/
	
	break;	
    
    
    
    
    case "datum_aendern":
    unset($_SESSION[buchungsdatum]);
    unset($_SESSION[temp_kontoauszugsnummer]);
    weiterleiten("?daten=miete_buchen");
    break;
    
    
    default:
 	if(isset($_REQUEST['objekt_id'])){
 		$_SESSION['objekt_id'] = $_REQUEST['objekt_id'];
 	#unset($_SESSION[temp_kontoauszugsnummer]);
 	#unset($_SESSION[buchungsdatum]);
 	#weiterleiten("?daten=miete_buchen");
 	}
 	//$_SESSION[buchungsanzahl] = 5;  Anzahl letzter Buchungen die angezeigt werden
 	$info = new mietkonto;
 	#$info->letzte_buchungen_anzeigen();
 	$info->letzte_buchungen_anzeigen_vormonat_monat();
 	if(!isset($_SESSION['objekt_id'])){
 	echo "<div class=\"info_feld_oben\">Objekt auswählen</div>";
 	}
 	
 	if(isset($_POST['datum_setzen'])){
 		$_SESSION['buchungsdatum'] = $_POST['tag'].".".$_POST['monat'].".".$_POST['jahr']."";
 		$_SESSION['temp_kontoauszugsnummer'] = $_POST['KONTOAUSZUGSNR'];
 	#weiterleiten("?daten=miete_buchen");
 	}
 	echo "<div class=\"datum_zeile\">";
 	$datum_form = new mietkonto;
 	$datum_form->erstelle_formular("Buchungsdatum", NULL);
 	if(!isset($_SESSION['buchungsdatum']) && !isset($_SESSION['kontoauszugsnr'])) {
 	$datum_form->datum_form();
 	}else{
 	
 	if(!empty($_SESSION['kontoauszugsnr'])){
 	echo "<b>Kontoauszugsnummer:</b> ".$_SESSION[kontoauszugsnr]." ";	
 	}else{
 	echo "<b>Kontoauszugsnummer eingeben!</b>&nbsp;&nbsp;";	
 	}
 	if(isset($_SESSION['buchungsdatum'])){
 		
 	echo "<b>Buchungsdatum:</b> ".$_SESSION['buchungsdatum']."";
 	echo "&nbsp;<a href=\"?daten=miete_buchen&schritt=datum_aendern\">Datum ändern</a>&nbsp;";
 	}else{
 	echo "<b>Datum eingeben !</b>";	
 	}
 	#print_r($_SESSION);
 	
 	$geld = new geldkonto_info;
 	$kontostand_aktuell =nummer_punkt2komma($geld->geld_konto_stand($_SESSION['geldkonto_id']));
 	if(isset($_SESSION['temp_kontostand']) && isset($_SESSION['temp_kontoauszugsnummer'])){
 		$kontostand_temp = nummer_punkt2komma($_SESSION['temp_kontostand']);
 		echo "<h3>Kontostand am $_SESSION[temp_datum] laut Kontoauszug $_SESSION[temp_kontoauszugsnummer] war $kontostand_temp €</h3>";
 	}else{
 		echo "<h3 style=\"color:red\">Kontrolldaten zum Kontoauszug fehlen</h3>";
 		echo "<h3 style=\"color:red\">Weiterleitung erfolgt</h3>";
 		weiterleiten_in_sec("?daten=buchen&option=kontoauszug_form", 1);
 	}
 	if($kontostand_aktuell == $kontostand_temp){
 		echo "<h3>Kontostand aktuell: $kontostand_aktuell €</h3>";
 	}else{
 		echo "<h3 style=\"color:red\">Kontostand aktuell: $kontostand_aktuell €</h3>";
 	}
 	
 	
 	$datum_form->ende_formular();
 	}
 	echo "</div>";
 	
 	
 	objekt_auswahl();
 	#include("options/formulare/einheit_suche.php");
 	if(isset($_SESSION[objekt_id])){
 	einheiten_liste();
 	}
 	#if(isset($_POST[einheit_finden])){
 	#echo "$_POST[suchfeld]";
 	#$_SESSION[EINHEIT] = $_POST[suchfeld];
 	#print_r($_SESSION); 
 	break;


	case "stornieren":
    $form = new mietkonto;
    $form->erstelle_formular("Buchung stornieren", NULL);
	$buchungs_info = new mietkonto;
    $buchungs_info->buchungsnummer_infos($_REQUEST[bnr]);
    $form->ende_formular();
    break;

	case "stornierung_in_db":
    $form = new mietkonto;
    $form->erstelle_formular("Sicherheitsabfrage", NULL);
	/*Falls NEIN gedrückt*/
    if(isset($_POST[submit_storno_nein])){
       	weiterleiten("?daten=miete_buchen", 2);
    warnung_ausgeben(("Der Vorgang wurde vom Benutzer abgebrochen. <br> Die Buchung wurde nicht storniert. <br>Bitte warten, Sie werden weitergeleitet."));
    }
    /*Sicherheitsabfrage vor dem Absenden oder Abbrechen*/
    if(!isset($_POST[submit_storno_ja]) && !isset($_POST[submit_storno_nein]) ){
    warnung_ausgeben(("Sind Sie sicher, daß Sie die Buchungsnummer $_POST[BUCHUNGSNUMMER] stornieren möchten?"));
	$form->hidden_feld("BUCHUNGSNUMMER", "".$_POST[BUCHUNGSNUMMER]."");
	for($a=0;$a<count($_POST[MIETBUCHUNGEN]);$a++){
	$form->hidden_feld("MIETBUCHUNGEN[]", "".$_POST[MIETBUCHUNGEN][$a]."");	
	}
	$form->hidden_feld("schritt", "stornierung_in_db");
	$form->send_button("submit_storno_ja", "JA");
    $form->send_button("submit_storno_nein", "NEIN");
    
    }
    /*Falls JA gedrückt*/
    if(isset($_POST[submit_storno_ja])){
    $form->miete_zahlbetrag_stornieren($_POST[BUCHUNGSNUMMER]);
    for($a=0;$a<count($_POST[MIETBUCHUNGEN]);$a++){
    $form->mietbuchung_stornieren_intern("".$_POST[MIETBUCHUNGEN][$a]."");
    }
    /*Nach dem Stornieren weiterleiten*/
    weiterleiten("?daten=miete_buchen", 3);
    }
    $form->ende_formular();
    break;

	case "monatsabschluss":
    $mietkonto = new mietkonto;
		    
	if(isset($_SESSION[objekt_id])){
	$objekt_id = $_SESSION[objekt_id];
	$mein_objekt = new objekt;
	$liste_haeuser = $mein_objekt->haeuser_objekt_in_arr($objekt_id);

	for($i=0;$i<count($liste_haeuser);$i++){
$result = mysql_query ("SELECT * FROM EINHEIT WHERE EINHEIT_AKTUELL='1' && HAUS_ID='".$liste_haeuser[$i][HAUS_ID]."' ORDER BY EINHEIT_KURZNAME ASC");
	while ($row = mysql_fetch_assoc($result)) $einheiten_array[] = $row;
	}
	
	$einheit_info = new einheit;
	#ob_start(); //Ausgabepuffer Starten
	
	$zeile = 0;
	for($i=0;$i<=count($einheiten_array);$i++){
	$einheit_info->get_mietvertrag_id("".$einheiten_array[$i][EINHEIT_ID]."");
	$einheit_vermietet = $einheit_info->get_einheit_status("".$einheiten_array[$i][EINHEIT_ID]."");
	if($einheit_vermietet){
$miete = new miete;
		$miete->mietkonto_berechnung($einheit_info->mietvertrag_id);
		$zeile = $zeile +1;	
		echo "$zeile . $mietkonto->datum_heute Mietvertrag: $einheit_info->mietvertrag_id Saldo: $miete->erg €<br>";
		$mietkonto->monatsabschluesse_speichern($einheit_info->mietvertrag_id, $miete->erg);
	$miete->erg = '0.00';
	}
	}
    }
    break;


 	}//end switch
 	
 	/*User Funktionen */


function objekt_auswahl(){
	echo "<div class=\"objekt_auswahl\">";
	$mieten = new mietkonto;
	$mieten->erstelle_formular("Objekt auswählen...", NULL);
	
	
	if(isset($_SESSION['objekt_id'])){
 	$objekt_kurzname = new objekt;
 	$objekt_kurzname->get_objekt_name($_SESSION['objekt_id']);
 	echo "<p>&nbsp;<b>Ausgewähltes Objekt</b> -> $objekt_kurzname->objekt_name ->";
 	
 	#$mieten->geldkonto_stand_anzeigen($_SESSION[objekt_id]);
 	echo "->&nbsp;<a href=\"?daten=miete_buchen&schritt=monatsabschluss\">Monatsabschluss</a>";
 	echo " </p>";
 	echo "<div class=\"info_feld_oben\">Ausgewähltes Objekt ".$objekt_kurzname->objekt_name."<br><b>Einheit auswählen</b><br>WEISS: keine Zahlung im aktuellen Monat.<br>GRAU: Zahlungen wurden gebucht.</div>";
 	}
	
	
	
	$objekte = new objekt;
	$objekte_arr = $objekte->liste_aller_objekte();
	
	$anzahl_objekte = count($objekte_arr);
	#print_r($objekte_arr);
	$c=0;
	for($i=0;$i<$anzahl_objekte;$i++){
	echo "<a class=\"objekt_auswahl_buchung\" href=\"?daten=miete_buchen&objekt_id=".$objekte_arr[$i]['OBJEKT_ID']."\">".$objekte_arr[$i]['OBJEKT_KURZNAME']."</a>&nbsp;<b>|</b>&nbsp;";	
	$c++;
	if($c==10){
	echo "<br>";
	$c=0;
	}
	}
$mieten->ende_formular();
echo "</div>";
}

function einheiten_liste(){
$mieten = new mietkonto;
#$mieten->letzte_buchungen_anzeigen();
echo "<div class=\"einheit_auswahl\">";
$mieten->erstelle_formular("Einheit auswählen...", NULL);

/*Liste der Einheiten falls Objekt ausgewählt wurde*/
if(isset($_SESSION['objekt_id'])){
$objekt_id = $_SESSION['objekt_id'];
$mein_objekt = new objekt;
$liste_haeuser = $mein_objekt->haeuser_objekt_in_arr($objekt_id);

	for($i=0;$i<count($liste_haeuser);$i++){
	$hh_id = $liste_haeuser[$i]['HAUS_ID'];
	$result = mysql_query ("SELECT * FROM EINHEIT WHERE EINHEIT_AKTUELL='1' && HAUS_ID='$hh_id' ORDER BY EINHEIT_KURZNAME ASC");
	while ($row = mysql_fetch_assoc($result)) $einheiten_array[] = $row;
	}
}else{
/*Liste aller Einheiten da kein Objekt ausgewählt wurde*/
$meine_einheiten = new einheit;
$einheiten_array = $meine_einheiten->liste_aller_einheiten();
}	
// Beispiel für ein Array $sx mit den Spalten $sx['dat'], $sx['name'], $sx['id'].

$einheiten_array = array_sortByIndex($einheiten_array,'EINHEIT_KURZNAME');
#echo "<pre>";
#print_r($einheiten_array);
#echo "</pre>";
$counter = 0;
$spaltencounter = 0;
echo "<table>";
echo "<tr><td valign=\"top\">";
$einheit_info = new einheit;
#$mietkonto2 = new mietkonto;
#$zeitraum = new zeitraum;
#foreach ( $[ 'element' ] as $value ) {
for($i=0;$i<count($einheiten_array);$i++){
	
	$ee_id = $einheiten_array[$i]['EINHEIT_ID']; 
	$einheit_vermietet = $einheit_info->get_einheit_status($ee_id);
	if($einheit_vermietet){
	$einheit_info->get_mietvertrag_id($ee_id);
	/*
	$mi = new miete;
	$saldo = $mi->saldo_berechnen($einheit_info->mietvertrag_id);
	
	if($saldo==0){
	$mietkonto_status = "<font id=\"status_neutral\">(0)</font>";
	}
	if($saldo>0){
	$mietkonto_status = "<font id=\"status_positiv\">(+)</font>";
	}
	if($saldo<0){
	$mietkonto_status = "<font id=\"status_negativ\">(-)</font>";
	}
	*/
	$mietkonto_status ='';
	#if(isset($einheit_info->mietvertrag_id)){
		$anzahl_zahlungsvorgaenge = $mieten->anzahl_zahlungsvorgaenge($einheit_info->mietvertrag_id);
		$ekn = $einheiten_array[$i]['EINHEIT_KURZNAME'];	
		if($anzahl_zahlungsvorgaenge<1){
			
				echo "<a href=\"?daten=miete_buchen&schritt=buchungsauswahl&mietvertrag_id=".$einheit_info->mietvertrag_id."\" class=\"nicht_gebucht_links\">$ekn</a> $mietkonto_status&nbsp;";
			}else{
			echo "<a href=\"?daten=miete_buchen&schritt=buchungsauswahl&mietvertrag_id=".$einheit_info->mietvertrag_id."\" class=\"gebucht_links\">$ekn</a> $mietkonto_status&nbsp;";
			}
	echo "<br>"; // Nach jeder Einheit Neuzeile
	$m = new mietvertrag; //class mietvertrag aus berlussimo_class.php;
	$m1 = new mietvertraege; //class mietvertraege NEUE KLASSE;
	$mv_ids_arr = $m->get_personen_ids_mietvertrag($einheit_info->mietvertrag_id);
	#$m1->mv_personen_anzeigen($mv_ids_arr); //$mv_ids_arr Array mit personan Ids
	$mieternamen_str = $m1->mv_personen_als_string($mv_ids_arr);
	echo $mieternamen_str.'<br>';
	#echo "<br>"; // Nach jeder Einheit Neuzeile
		
	
	
	
	
	#echo "$mietkonto_status";
	#######mietkonto status ende
	
$counter++;
}
if($counter==10){
	echo "</td><td valign=\"top\">";
$counter = 0;
$spaltencounter++;
}

if($spaltencounter==5){
	echo "</td></tr>";
	echo "<tr><td colspan=\"$spaltencounter\"><hr></td></tr>";
	echo "<tr><td valign=\"top\">";
	
$spaltencounter = 0;
}

}
echo "</td></tr></table>";
#echo "<pre>";
		#print_r($einheiten_array);	
#echo "</pre>";
$mieten->ende_formular();
echo "</div>";	

}


?>
