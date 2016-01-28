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
 * @filesource   $HeadURL: http://192.168.2.52/svn/berlussimo_1/tags/02.11.2010 - Downloadversion 0.27/options/modules/dtaus_daten.php $
 * @version      $Revision: 6 $
 * @modifiedby   $LastChangedBy: sivac $
 * @lastmodified $Date: 2010-09-21 10:34:50 +0200 (Di, 21 Sep 2010) $
 * 
 */
 
include_once("includes/allgemeine_funktionen.php");
include_once("classes/class_dtaus_berlussimo.php");
/*Überprüfen ob Benutzer Zugriff auf das Modul hat*/
if(!check_user_mod($_SESSION['benutzer_id'], 'dt_aus')){
	echo '<script type="text/javascript">';
	echo "alert('Keine Berechtigung')";
	echo '</script>';	
	die();
}

include_once("options/links/links.dtaus_daten.php");
include_once("classes/mietkonto_class.php");
include_once("classes/berlussimo_class.php");
include_once("classes/kasse_class.php");
include_once("classes/class.dtaus.php");
if(isset($_REQUEST["option"])){
$option = $_REQUEST["option"];
}else{
	$option = 'default';
}
switch($option) {
   
    default:
   # unset($_SESSION['objekt_id']);
    break;
    
    case "dtaus_erstellen_alt":
   	#unset($_SESSION['objekt_id']);
   	$form = new mietkonto;
   	$form->erstelle_formular("DT-AUS Erstellung", NULL);
   	#$dtaus_info = new dtaus_vorbereitung;
   	$link="?daten=dt_aus&option=dtaus_erstellen";
   	objekt_auswahl_liste($link);
   	if(isset($_SESSION['objekt_id'])){
   	$dtaus = new dtaus_vorbereitung($_SESSION['objekt_id']); // 'objekt_id'
   	$form->erstelle_formular("DT-AUS Zusammenstellung für $dtaus->objekt_name", NULL);
   	
	#$dtaus = new dtaus_vorbereitung(2); // 'objekt_id'
	#echo "<hr>";
	if($dtaus->fehler){
		echo $dtaus->objekt_name;
		echo $dtaus->fehler; 
	}
	if($dtaus->objekt_anzahl_geldkonten == 1){
	$dtaus->sortierung_nach_kontonummern($dtaus->objekt_geld_konten[0]['KONTO_ID']);
	
	/*echo "<pre>";
	print_r($dtaus);
	echo "</pre>";
	*/
	// DTAUS KLASSE -> Neues Objekt erzeugen
    if(count($dtaus->sortiert)>0){
    $dt = new dtaus;
    // Wo soll die Kohle hin ? (Name, BLZ, Kontonummer)
    $dt->meineDaten($dtaus->objekt_geld_konten[0][empfaenger_name],$dtaus->objekt_geld_konten[0][empfaenger_blz],$dtaus->objekt_geld_konten[0][empfaenger_kontonr]);
	
 	 #$dt->meineDaten('Berlus Hausverwaltung','10080000','0580400007');
		// DTAUS Klasse ansteuern
            $jahr=date("Y");
            $monat=date("m");
            echo "<table><tr class=\"feldernamen\">";
            echo "<td colspan=2><b>Empfänger:<br>".$dtaus->objekt_geld_konten[0][empfaenger_name]."</b></td>";
            echo "<td><b>Kontonr:<br> ".$dtaus->objekt_geld_konten[0][empfaenger_kontonr]."</b></td>";
            echo "<td><b>BLZ:<br> ".$dtaus->objekt_geld_konten[0][empfaenger_blz]."</b></td>";
            echo "<td>vzweck</td><td>MV-Nr</td><td>Einheit</td>";
            echo "</tr>";
            $import_string = "";
            $gesamt_summe_rechnen = 0;
            for($a=0;$a<count($dtaus->sortiert);$a++){
             
            $vzweck1 = "LASTSCHRIFT Miete $monat$jahr"; // zu langes wird abgeschnitten
            $vzweck2 = $dtaus->sortiert[$a]['MIETVERTRAG_NR']; // zu langes wird abgeschnitten
            $vzweck3 = $dtaus->sortiert[$a]['EINHEIT_ID']; // zu langes wird abgeschnitten
            $vzweck3 .= " "; // zu langes wird abgeschnitten
            $vzweck3 .= $dtaus->sortiert[$a]['EINHEIT_NAME']; // zu langes wird abgeschnitten
			$betrag_in_cent = $dtaus->sortiert[$a]['MIETSUMME'];
			$betrag_import = $dtaus->sortiert[$a]['MIETSUMME'];
			
			//EINHEIT_NAME, MIETVERTRAG_ID, EINHEIT_ID, VZWECK, BETRAG
			$import_string .= "".$dtaus->sortiert[$a]['EINHEIT_NAME'].";$vzweck2;".$dtaus->sortiert[$a]['EINHEIT_ID'].";$vzweck1;$betrag_import\n";
            // daten kommen in diesem beispiel aus der datenbankabfrage
            // verwendungszweck ist statisch
           
            $zeilen_zaehler = $a+1;           
            echo "<tr><td>$zeilen_zaehler. ".$dtaus->sortiert[$a]['KONTOINHABER']."</td><td>".$dtaus->sortiert[$a]['KONTONUMMER']."</td><td>".$dtaus->sortiert[$a]['BLZ']."</td><td>".$dtaus->sortiert[$a]['MIETSUMME']." €-Cent</td><td>$vzweck1</td><td>$vzweck2</td><td>$vzweck3</td>";
            $gesamt_summe_rechnen = $gesamt_summe_rechnen +  $dtaus->sortiert[$a]['MIETSUMME'];
            $dt->lastschrift($dtaus->sortiert[$a]['KONTOINHABER'], $dtaus->sortiert[$a]['BLZ'], $dtaus->sortiert[$a]['KONTONUMMER'], $betrag_in_cent, $vzweck1, $vzweck2, $vzweck3);
	// ausgabe zum beispiel in einem textfeld für copy & paste
            }//end for
             #echo "$import_string";
            echo "<tr><td>$gesamt_summe_rechnen €</td></tr>";
            echo "</table>";
    /* echo '<textarea class="content" style="width:900; height:1200;">' . $dt->doSome() . '</textarea><br><br>';*/
        // fertig
	// Dateityp
$string = $dt->doSome();
$filename = "DTAUS0_".$dtaus->objekt_geld_konten[0][empfaenger_kontonr]."_".$dtaus->objekt_geld_konten[0][empfaenger_blz]."_".$dtaus->objekt_geld_konten[0]['KONTO_ID'].".DTA";
$dtaus->dtaus_datei_speichern($filename, $string); 
$import_filename = "DTAUS0_".$dtaus->objekt_geld_konten[0][empfaenger_kontonr]."_".$dtaus->objekt_geld_konten[0][empfaenger_blz]."_".$dtaus->objekt_geld_konten[0]['KONTO_ID'].".import";
$dtaus->dtaus_importdatei_speichern($import_filename, $import_string);
#echo $string;
    }//end if dtaus sortiert nicht leer bzw >0
    else{
    	echo "Keine Teilnehmer am Einzugsverfahren für dieses Geldkonto";
    }
	}
	
	$form->ende_formular();
   	}
   	$form->ende_formular();
    break; 



	case "dtaus_buchen":
	if(isset($_REQUEST['objekt_id'])){
		$_SESSION['objekt_id'] = $_REQUEST['objekt_id'];
	}
	$form = new mietkonto;
   	$form->erstelle_formular("DT-AUS Buchen", NULL);
   	$link="?daten=dt_aus&option=dtaus_buchen";
   	objekt_auswahl_liste($link);
   	if(isset($_SESSION['objekt_id'])){
   	#$dtaus = new dtaus_vorbereitung($_SESSION['objekt_id']); // 'objekt_id'
   	$form->erstelle_formular("DT-AUS Zusammenstellung", NULL);
   	
   	$o = new objekt;
	$o->objekt_informationen($_SESSION['objekt_id']);
		if($o->anzahl_geld_konten>0){
		$geldkonto_id = $o->geld_konten_arr[0]['KONTO_ID'];
		$beguenstgter =  $o->geld_konten_arr[0]['BEGUENSTIGTER'];
		$kontonr = $o->geld_konten_arr[0]['KONTONUMMER'];
		$blz = $o->geld_konten_arr[0]['BLZ'];
		$institut = $o->geld_konten_arr[0][INSTITUT];
   	
		
	$import_filename = "DTAUS0_".$kontonr."_".$blz."_".$geldkonto_id.".import";
	$geld_konto_id_f = $geldkonto_id;
	$dtaus_ordner = 'dtaus';
	$jahr = date("Y");
	$monat = date("m");
	#echo $dtaus_ordner;
	$jahres_ordner = "".$dtaus_ordner."/".$jahr."";
	$monats_ordner = "".$jahres_ordner."/".$monat."";
	if(file_exists("$monats_ordner/$import_filename")){
	$import_eingelesen = file("$monats_ordner/$import_filename"); 
	if(!isset($_POST[send_btn])){
	$form->erstelle_formular("Lastschriften für Import auswählen", NULL);
	#print_r($_SESSION);
	if(isset($_SESSION['temp_datum'])){
	$datum	= $_SESSION['temp_datum'];
	}else{
	$datum = date("d.m.Y");	
	}
	
	if(isset($_SESSION['temp_kontoauszugsnummer'])){
	$auszugnr= $_SESSION['temp_kontoauszugsnummer'];
	}else{
	$auszugnr = '';	
	}
	
		$form->text_feld_id("Datum", "datum", $datum, 10, "datum_feld");
		$form->text_feld_id("Kontoauszugsnummer", "kontoauszugsnummer", "$auszugnr", 10, "kontoauszugsnummer_feld");
		$form->hidden_feld('geld_konto_id', "$geld_konto_id_f");
		echo "<table>";
		echo "<tr class=\"feldernamen\"><td>EINHEIT</td><td>MIETVERTRAG</td><td>EINHEIT_ID</td><td>VERWENDUNGSZWECK</td><td>BETRAG</td></tr>";
		$gesamtbetrag = 0;
		for($a=0;$a<count($import_eingelesen);$a++){
			$zeile = explode(";", $import_eingelesen[$a]);
			$gesamtbetrag = $gesamtbetrag + $zeile['4'];
			echo "<tr>";
			for($b=0;$b<count($zeile);$b++){
			echo "<td>$zeile[$b]</td>";	
			}
		echo "</tr>";
		}
	echo "<tr><td><b>Gesamtbetrag: $gesamtbetrag €</b></td></tr>";
	echo "</table>";	
	$form->send_button("send_btn", "IMPORTIEREN");
	}
	$form->ende_formular(); //lastschriften
	}else{
		fehlermeldung_ausgeben('Importdatei nicht erstellt, bitte DTAUS-Erstellen');
	}
	if(isset($_POST[send_btn])){
		$buchungsdatum = date_german2mysql($_POST[datum]);
		$kontoauszugsnummer = $_POST[kontoauszugsnummer];
		
		for($a=0;$a<count($import_eingelesen);$a++){
			$zeile = explode(";", $import_eingelesen[$a]);
			$mietvertrag_id = $zeile[1];
			$betrag = $zeile[4];
			$bemerkung = $zeile[3];
			$geldkonto_id = $_POST[geld_konto_id];
	#echo "DDDD $kontoauszugsnummer $mietvertrag_id $buchungsdatum $betrag $bemerkung $geld_konto_id";
					
			#$form->import_miete_zahlbetrag_buchen($kontoauszugsnummer, 'Mietvertrag', $mietvertrag_id, $buchungsdatum, $betrag, $bemerkung, $geld_konto_id, '80001');				
			
			$form->insert_geldbuchung($geldkonto_id, '80001', $kontoauszugsnummer, 'DTAUS', $bemerkung, $buchungsdatum, 'Mietvertrag', $mietvertrag_id, $betrag);
			
	}//end for
	}//send btn	
   	}//konto 1
   	}//if set objekt
	$form->ende_formular();
	break;
	
	/*Neue Version der DTAUS-Erstellung objektbezogen*/
	case "dtaus_erstellen";
	$f = new formular;
	$f->fieldset("DTAUS-Erstellen", 'dtaus_erstellen');
	$dt = new dtaus_berlussimo;
	$dt->ls_akt_teilnehmer();
	$f->fieldset_ende();
	break;
	
	
	case "pdf_protokoll":
	error_reporting(E_ALL|E_STRICT);
	ini_set('display_errors','On'); 

	#if(!empty($_REQUEST['objekt_id'])){
	$dt = new dtaus_berlussimo;
	$tab_arr = $dt->ls_akt_teilnehmer();
	$dtb = new dtaus_berlus();
	$dtb->pdf_dtaus_inhalt_obj($tab_arr);	
	#}
	break;	
	
	
}//end switch



class dtaus_berlussimo{
/*SALDO ANZEIGEN, WAS ZU ZIEHEN, SUMME AUS MV*/

/*Ausgabe der LS-Teilnehmer*/
function ls_akt_teilnehmer(){

include_once('classes/class_mietvertrag.php');
$link="?daten=dt_aus&option=dtaus_erstellen";
$bg = new berlussimo_global;
$bg->objekt_auswahl_liste($link);
$form = new formular;
$mv = new mietvertraege;
$form->erstelle_formular("Aktuelle Teilnehmer am Lastschriftverfahren", NULL);

$teilnehmer_arr = $mv->ls_akt_teilnehmer_arr();
$anzahl_tln = count($teilnehmer_arr);
#echo '<pre>';
#print_r($teilnehmer_arr);

/*Wenn Teilnehmer vorhanden*/
if($anzahl_tln){

$monat = date("m");
#$monat = "10";
$jahr = date("Y");
#$jahr=2010;
$monatsname = monat2name($monat);
echo "<table>";
echo "<tr class=\"feldernamen\"><td colspan=6>Objekt $teilnehmer_arr[OBJEKT_KURZNAME] $monatsname $jahr</td></tr>";
echo "<tr class=\"feldernamen\"><td>EINHEIT</td><td>MIETER</td><td>SALDO</td><td>SOLL AUS MV</td><td>ZIEHEN</td><td>EINZUGSART</td></tr>";

$mz = new miete;
$mk = new mietkonto;
 

$zaehler=0;
for($a=0;$a<$anzahl_tln-2;$a++){
$zeile = $a+1;
$zaehler++;
$mv_id =$teilnehmer_arr[$a]['MV_ID'];
#$saldo = $mz->saldo_berechnen($mv_id);
#$saldo = $mz->saldo_berechnen_monatsgenau($mv_id, $monat, $jahr);
$mz->mietkonto_berechnung($mv_id);
$saldo = $mz->erg;
$saldo_a = nummer_punkt2komma($saldo);
$soll_aus_mv = $mk->summe_forderung_monatlich($mv_id, $monat, $jahr);
$soll_aus_mv_a = nummer_punkt2komma($soll_aus_mv);
$einheit_id =$teilnehmer_arr[$a]['EINHEIT_ID'];
$einheit_kurzname =$teilnehmer_arr[$a]['EINHEIT_KURZNAME'];
$anzahl_mieter =$teilnehmer_arr[$a]['MIETER_ANZAHL'];
$erster_mieter = $teilnehmer_arr[$a]['MIETER'][0]['VORNAME'];
#echo '<pre>';
#print_r($teilnehmer_arr);
/*Einzugsart aus Detailsermitteln*/
$einzugsart = $this->einzugsart_ermitteln($mv_id);

	if($anzahl_mieter==1){
	#echo "<tr class=\"zeile$zaehler\"><td>$zeile. $einheit_kurzname</td><td>$erster_mieter</td><td>$saldo_a</td><td>$soll_aus_mv_a</td><td>$einzugsart</td></tr>";	
	}
	if($anzahl_mieter>1){
	#echo "<tr class=\"zeile$zaehler\"><td>$zeile. $einheit_kurzname</td><td>";
	
		for($b=0;$b<$anzahl_mieter;$b++){
		#echo $teilnehmer_arr[$a]['MIETER'][$b]['NACHNAME'].' '.$teilnehmer_arr[$a]['MIETER'][$b][VORNAME].'<br>';	
		}
		#echo "</td><td>$saldo_a</td><td>$soll_aus_mv_a</td><td>$einzugsart</td></tr>";	
		}
		if($zaehler==2){
		$zaehler=0;	
		}

#echo "$erster_mieter $saldo_a, $soll_aus_mv_a<br>";

/*DETAILDATEN Holen*/

/*Kontoinhaber ermitteln*/
		$result = mysql_query ("SELECT DETAIL_INHALT AS KONTO_INHABER
FROM `DETAIL`
WHERE DETAIL_NAME = 'Kontoinhaber-AutoEinzug' && DETAIL_ZUORDNUNG_TABELLE = 'MIETVERTRAG' && DETAIL_AKTUELL = '1' && DETAIL_ZUORDNUNG_ID = '$mv_id'");
		$row = mysql_fetch_assoc($result);
		$this->alle_teilnehmer[$a]['KONTOINHABER'] = $row['KONTO_INHABER'];
		
		/*Kontonummer für das Einzugsverfahren ermitteln*/
		$result = mysql_query ("SELECT DETAIL_INHALT AS KONTONUMMER
FROM `DETAIL`
WHERE DETAIL_NAME = 'Kontonummer-AutoEinzug' && DETAIL_ZUORDNUNG_TABELLE = 'MIETVERTRAG' && DETAIL_AKTUELL = '1' && DETAIL_ZUORDNUNG_ID = '$mv_id'");
		$row = mysql_fetch_assoc($result);
		$this->alle_teilnehmer[$a]['KONTONUMMER'] = $row['KONTONUMMER'];
		
		/*BLZ ermitteln*/
		$result = mysql_query ("SELECT DETAIL_INHALT AS BLZ FROM `DETAIL` WHERE DETAIL_NAME = 'BLZ-AutoEinzug' && DETAIL_ZUORDNUNG_TABELLE = 'MIETVERTRAG' && DETAIL_AKTUELL = '1' && DETAIL_ZUORDNUNG_ID = '$mv_id'");
		$row = mysql_fetch_assoc($result);
		$this->alle_teilnehmer[$a]['BLZ'] = $row['BLZ'];
		



/*Wenn keine Einzugsart bestimmt, Einzugsart auf Aktuelles Saldo komplett stellen*/
		if(!$einzugsart){
		$this->alle_teilnehmer[$a]['Autoeinzugsart'] ="Aktuelles Saldo komplett";
		$einzugsart == "Aktuelles Saldo komplett";
		}		
		if($einzugsart == "Aktuelles Saldo komplett"){
		$mietk = new miete;
		#$mietsumme = $mietk->saldo_berechnen_monatsgenau($mv_id, $monat, $jahr);
		 
		 #$mietsumme = $mietk->saldo_berechnen_monatsgenau($mv_id, date("m"), date("Y"));
		 #$mietsumme = $mietk->saldo_berechnen_tagesgenau($mv_id, date("m"), date("Y"), date("d"));
		 $mietk->mietkonto_berechnung($mv_id);
		 $mietsumme = $mietk->erg;		
			/* FALL 1. Wenn Mieter im PLUS d.h. keine Schulden, $mietsumme auf 0, da nicht gezogen wird.*/
			if($mietsumme >= '0.00'){
			$mietsumme = '0.00';
			}
		}
		/* FALL 2. Unabhängig vom Saldo, nur die im Mietvertrag vereinbarte Summe ziehen*/
		if($einzugsart == "Nur die Summe aus Vertrag"){
		$mietsumme = $mk->summe_forderung_monatlich($mv_id, $monat, $jahr);
		$this->alle_teilnehmer[$a]['Autoeinzugsart'] = $einzugsart;	
		}
		
		/*FALL 3. Falls Ratenzahlung vereinbart, Summe aus Vertrag + Rate*/
		if($einzugsart == "Ratenzahlung"){
		$summe_raten = $mk->summe_rate_monatlich($mv_id, $monat, $jahr);	
		$forderung_monatlich = $mk->summe_forderung_monatlich($mv_id, $monat, $jahr);
		$mietsumme = $summe_raten + $forderung_monatlich;
		$this->alle_teilnehmer[$a]['Autoeinzugsart'] = $einzugsart;
		}
		
		
		#$summe_raten = $mk->summe_rate_monatlich($mv_id, $monat, $jahr);	
		#$mietsumme = $mietsumme + $summe_raten;
		
		
		$ziehen = nummer_punkt2komma($mietsumme);
		echo "<tr class=\"zeile$zaehler\"><td>$zeile. $einheit_kurzname</td><td>$erster_mieter</td><td>$saldo_a</td><td>$soll_aus_mv_a</td><td><b>$ziehen</b></td><td>$einzugsart</td></tr>";
				
		if($mietsumme < 0){
		#echo "K: $mietvertrag_id $mietsumme<br>";
		$mietsumme = substr($mietsumme, 1); //Minusvorzeichen entfernen
		}		 
		$mietsumme = number_format($mietsumme, 2, ".", "");
		if($mietsumme>'0.00'){
		#echo "K: $mietvertrag_id $mietsumme<br>";
		$this->alle_teilnehmer[$a]['MV_ID'] = $mv_id;
		$this->alle_teilnehmer[$a]['E_ID'] = $einheit_id;
		$this->alle_teilnehmer[$a]['EINHEIT_NAME'] = $einheit_kurzname;
		$this->alle_teilnehmer[$a]['Autoeinzugsart'] = $einzugsart;
		$this->alle_teilnehmer[$a]['ZIEHEN'] = $mietsumme;
		$this->alle_teilnehmer = array_values($this->alle_teilnehmer);
		
		}else{
		
		unset($this->alle_teilnehmer[$a]);
		}
		
		$mietsumme = 0;
		
		
		

}//end for
echo "</table>";
/*Ende der ersten Tabelle mit allen Teilnehmern und Infos*/

$anzahl_teilnehmer = count($this->alle_teilnehmer);
if($anzahl_teilnehmer>0){
$this->alle_teilnehmer['objekt_id'] = $teilnehmer_arr['objekt_id'];
$this->alle_teilnehmer['OBJEKT_KURZNAME'] = $teilnehmer_arr['OBJEKT_KURZNAME'];
	$o = new objekt;
	#print_r($teilnehmer_arr);
	$o->objekt_informationen($teilnehmer_arr['objekt_id']);
		if($o->anzahl_geld_konten>0){
		$geldkonto_id = $o->geld_konten_arr[0]['KONTO_ID'];
		$beguenstgter =  $o->geld_konten_arr[0]['BEGUENSTIGTER'];
		$kontonr = $o->geld_konten_arr[0]['KONTONUMMER'];
		$blz = $o->geld_konten_arr[0]['BLZ'];
		$institut = $o->geld_konten_arr[0]['INSTITUT'];
		
		
		/*ALLE DATEN VORHANDEN, DTAUS DATEI ERSTELLEN UND SPEICHERN*/
		$dt = new dtaus;
    	// Wo soll die Kohle hin ? (Name, BLZ, Kontonummer)
    	$dt->meineDaten($beguenstgter,$blz,$kontonr);
		
		
		// DTAUS Klasse ansteuern
        #echo '<pre>';    
    	#print_r($this->alle_teilnehmer);
         #   die();
            
            $import_string = "";
            $gesamt_summe_rechnen = 0;
            $a = 0;
            echo "<br><hr><br>";
            echo "<table>";
            $pdf_link = "<a href=\"?daten=dt_aus&option=pdf_protokoll\"><img src=\"css/pdf.png\"></a>";
            $pdf_link1 = "<a href=\"?daten=dt_aus&option=pdf_protokoll&no_logo\"><img src=\"css/pdf2.png\"></a>";
            echo "<tr class=\"feldernamen\"><td colspan=8>DTAUS TEILNEHMER IN DTAUS DATEI $pdf_link $pdf_link1</td></tr>";
            echo "<tr class=\"feldernamen\"><td colspan=8>$beguenstgter $kontonr $blz $institut</td></tr>";
            for($a=0;$a<$anzahl_teilnehmer;$a++){
             
            $monats_name = monat2name($monat);
            $vzweck1 = "LASTSCHRIFT Miete $monats_name $jahr"; // zu langes wird abgeschnitten
            $this->alle_teilnehmer[$a]['vztext'] = $vzweck1; 
            $vzweck2 = $this->alle_teilnehmer[$a]['MV_ID']; // zu langes wird abgeschnitten
            $vzweck3 = $this->alle_teilnehmer[$a]['E_ID']; // zu langes wird abgeschnitten
            $vzweck3 .= " "; // zu langes wird abgeschnitten
            $vzweck3 .= $this->alle_teilnehmer[$a]['EINHEIT_NAME']; // zu langes wird abgeschnitten
			$betrag_in_cent = $this->alle_teilnehmer[$a]['ZIEHEN'];
			$this->alle_teilnehmer[$a]['BETRAG'] = nummer_punkt2komma($this->alle_teilnehmer[$a]['ZIEHEN']);
			$betrag_import = $this->alle_teilnehmer[$a]['ZIEHEN'];
			
			//EINHEIT_NAME, MIETVERTRAG_ID, EINHEIT_ID, VZWECK, BETRAG
			$import_string .= "".$this->alle_teilnehmer[$a]['EINHEIT_NAME'].";$vzweck2;".$this->alle_teilnehmer[$a]['E_ID'].";$vzweck1;$betrag_import\n";
            // daten kommen in diesem beispiel aus der datenbankabfrage
            // verwendungszweck ist statisch
           
            $zeilen_zaehler = $a+1;           
            echo "<tr><td>$zeilen_zaehler. ".$this->alle_teilnehmer[$a]['KONTOINHABER']."</td><td>".$this->alle_teilnehmer[$a]['KONTONUMMER']."</td><td>".$this->alle_teilnehmer[$a]['BLZ']."</td><td>".$this->alle_teilnehmer[$a]['ZIEHEN']." €-Cent</td><td>$vzweck1</td><td>$vzweck2</td><td>$vzweck3</td>";
            $gesamt_summe_rechnen = $gesamt_summe_rechnen +  $this->alle_teilnehmer[$a]['ZIEHEN'];
            $dt->lastschrift($this->alle_teilnehmer[$a]['KONTOINHABER'], $this->alle_teilnehmer[$a]['BLZ'], $this->alle_teilnehmer[$a]['KONTONUMMER'], $betrag_in_cent, $vzweck1, $vzweck2, $vzweck3);
	// ausgabe zum beispiel in einem textfeld für copy & paste
            }//end for
            #echo "$import_string";
            $gesamt_summe_rechnen = nummer_punkt2komma($gesamt_summe_rechnen);
            echo "<tr><td colspan=3><b>GESAMTSUMME ALLER MIETEN</b></td><td><b>$gesamt_summe_rechnen €</b></td></tr>";
            echo "</table>";
     #echo '<textarea class="content" style="width:900; height:1200;">' . $dt->doSome() . '</textarea><br><br>';
        // fertig
	// Dateityp
$string = $dt->doSome();
$filename = "DTAUS0_".$kontonr."_".$blz."_".$geldkonto_id.".DTA";
$this->dtaus_datei_speichern($filename, $string); 
$import_filename = "DTAUS0_".$kontonr."_".$blz."_".$geldkonto_id.".import";
$this->dtaus_importdatei_speichern($import_filename, $import_string);
#echo $string;
		
		
		
		
		}else{
		fehlermeldung_ausgeben('Kein Geldkonto zum Objekt hinterlegt');	
		}
		
		
		}else{
fehlermeldung_ausgeben("Keine Daten für DTAUS, siehe Soll");
}		

echo "</table>";
#echo "<pre>";
#print_r($this->alle_teilnehmer);
/*Wenn keine Teilnehmer vorhanden*/
}else{
	echo "Keine Teilnehmer am Einzugsverfahren";
}
$form->ende_formular();

if(isset($this->alle_teilnehmer)){
return $this->alle_teilnehmer;
}
}//end function

	
/*Ermittlung der Einzugsart*/
	function einzugsart_ermitteln($mietvertrag_id){
	$result = mysql_query ("SELECT DETAIL_INHALT AS EINZUGSART FROM `DETAIL`
WHERE DETAIL_NAME = 'Autoeinzugsart' && DETAIL_ZUORDNUNG_TABELLE = 'MIETVERTRAG' && DETAIL_AKTUELL = '1' && DETAIL_ZUORDNUNG_ID='$mietvertrag_id' ORDER BY DETAIL_DAT DESC LIMIT 0,1");
	
	$numrows = mysql_numrows($result);
		if($numrows<1){
		return false;
		}else{
		$row = mysql_fetch_assoc($result);
		$einzugsart = $row['EINZUGSART'];	
		
		return $einzugsart;
			
	}
}	

function dtaus_datei_speichern($filename, $string){
	$doc_root = $_SERVER['DOCUMENT_ROOT'];
	$dtaus_ordner = 'dtaus';
	$jahr = date("Y");
	$monat = date("m");
	#echo $dtaus_ordner;
	$jahres_ordner = "$dtaus_ordner/$jahr";
	$monats_ordner = "$jahres_ordner/$monat";
	if (!file_exists($dtaus_ordner)){
	mkdir ($dtaus_ordner,0777);
	}
	if (!file_exists($jahres_ordner)){
	mkdir ($jahres_ordner,0777);
	}
	if (!file_exists($monats_ordner)){
	mkdir ($monats_ordner,0777);
	}
		
$filename_neu = "$monats_ordner/$filename";
	/*wenn datei existiert löschen*/
	if (file_exists($filename_neu)){
	unlink($filename_neu); // Datei löschen	
	}
	if (!file_exists($filename_neu)){
	$fhandle = fopen($filename_neu,"w");
	fwrite($fhandle,$string);
	fclose($fhandle);
		echo "<br>$filename_neu erstellt";
	chmod($filename_neu, 0644);
	#system("rm $dir -R");  //ordner abrechnungsjahr löschen
	echo "<table  border=3>";
	echo "<tr class=\"feldernamen\"><td><a href=\"$_SERVER[SCRIPT_ROOT]$filename_neu\"><b>DOWNLOAD ALS DTA-DATEI</a></b></td></tr>";
	echo "<tr><td>$string</td></tr>";
	echo "</table>";
	}
}

function dtaus_importdatei_speichern($filename, $string){
	$doc_root = $_SERVER['DOCUMENT_ROOT'];
	$dtaus_ordner = 'dtaus';
	$jahr = date("Y");
	$monat = date("m");
	#echo $dtaus_ordner;
	$jahres_ordner = "$dtaus_ordner/$jahr";
	$monats_ordner = "$jahres_ordner/$monat";
	if (!file_exists($dtaus_ordner)){
	mkdir ($dtaus_ordner,0777);
	}
	if (!file_exists($jahres_ordner)){
	mkdir ($jahres_ordner,0777);
	}
	if (!file_exists($monats_ordner)){
	mkdir ($monats_ordner,0777);
	}
		
	$filename_neu = "$monats_ordner/$filename";
	/*wenn datei existiert löschen*/
	if (file_exists($filename_neu)){
	unlink($filename_neu); // Datei löschen	
	}
	if (!file_exists($filename_neu)){
	$fhandle = fopen($filename_neu,"w");
	fwrite($fhandle,$string);
	fclose($fhandle);
	echo "<br>$filename_neu erstellt";
	chmod($filename_neu, 0644);
	#system("rm $dir -R");  //ordner abrechnungsjahr löschen
	echo "<table  border=3>";
	echo "<tr class=\"feldernamen\"><td>IMPORTDATEI</td></tr>";
	echo "<tr><td>$string</td></tr>";
	echo "</table>";
	}
}

	

}//end class





  class dtaus_vorbereitung{
  /*Berlussimo DATEN*/
  var $fehler;
  var $objekt_id;
  var $objekt_name;
  var $objekt_anzahl_geldkonten;
  var $objekt_geld_konten;
  var $temp_konto_id;
  
  /*Array mit Daten der Teilnehmer am Einzugsverfahren*/
  var $alle_teilnehmer = array();
  var $sortiert = array();


  function dtaus_vorbereitung($objekt_id){
  $this->objekt_id = $objekt_id;
  $objekt_info = new objekt;
  $objekt_info->get_objekt_name($objekt_id);
  $this->objekt_name = $objekt_info->objekt_name;
  $this->objekt_geld_konten_ermitteln();	
  $this->einzugsverfahren_teilnehmer();
  
  } 
  
  function objekt_geld_konten_ermitteln(){
  $konto_arr = $this->geldkonten_arr('Objekt', $this->objekt_id);
  if($konto_arr == false){
  $this->fehler = "Kein Geldkonto";	
  }else{
  $this->objekt_anzahl_geldkonten = count($konto_arr);
  	for($a=0;$a<$this->objekt_anzahl_geldkonten;$a++){
  	$this->objekt_geld_konten[$a]['KONTO_ID'] = $konto_arr[$a]['KONTO_ID'];
  	$this->objekt_geld_konten[$a][empfaenger_name] = $konto_arr[$a]['BEGUENSTIGTER'];
  	$this->objekt_geld_konten[$a][empfaenger_kontonr] = $konto_arr[$a]['KONTONUMMER'];
  	$this->objekt_geld_konten[$a][empfaenger_blz] = $konto_arr[$a]['BLZ'];
  	}
  	
  }
}//end function
  
 function sortierung_nach_kontonummern($konto_id){
 	/*$data[inhaber]=$data[0];
	$data['BLZ']=$data[1];
	$data['KONTONUMMER']=$data[2];
	$data[3]=$data[3]/100;echo $data[3];
	$data[betrag]=$data[3];*/
	for($a=0;$a<count($this->alle_teilnehmer);$a++){
		if($this->alle_teilnehmer[$a]['KONTO_ID'] == $konto_id){
		$this->sortiert[] = $this->alle_teilnehmer[$a];	
		}	
	}
 }
  
  	/*Funktion zur Ermittlung der Anzahl der Geldkonten*/
	function geldkonten_arr($kostentraeger_typ, $kostentraeger_id){
		$result = mysql_query ("SELECT GELD_KONTEN.KONTO_ID, GELD_KONTEN.BEGUENSTIGTER, GELD_KONTEN.KONTONUMMER, GELD_KONTEN.BLZ FROM GELD_KONTEN_ZUWEISUNG, GELD_KONTEN WHERE KOSTENTRAEGER_TYP = '$kostentraeger_typ' && KOSTENTRAEGER_ID = '$kostentraeger_id' && GELD_KONTEN.KONTO_ID = GELD_KONTEN_ZUWEISUNG.KONTO_ID && GELD_KONTEN_ZUWEISUNG.AKTUELL = '1' && GELD_KONTEN.AKTUELL = '1' ORDER BY GELD_KONTEN.KONTO_ID ASC");
	
	$numrows = mysql_numrows($result);
		if($numrows>0){
		while ($row = mysql_fetch_assoc($result)) $my_array[] = $row;	
		return $my_array;
		}else{
		return FALSE;	
		}
	
	}
  
 /*Funktion zur Ermittlung der Mietverträge die am Einzugsverfahren teilnehmen*/
	function mitvertrag_einzugsverfahren_arr(){
	#	$result = mysql_query ("SELECT DETAIL_ZUORDNUNG_ID AS MIETVERTRAG_NR
#FROM `DETAIL`
#WHERE DETAIL_NAME = 'Einzugsermächtigung' && DETAIL_INHALT='JA' && DETAIL_ZUORDNUNG_TABELLE = 'MIETVERTRAG' && DETAIL_AKTUELL = '1'");

$result = mysql_query ("SELECT DETAIL_ZUORDNUNG_ID AS MIETVERTRAG_NR FROM `DETAIL` LEFT JOIN(MIETVERTRAG) ON (DETAIL_ZUORDNUNG_ID=MIETVERTRAG_ID) 
WHERE DETAIL_NAME = 'Einzugsermächtigung' && DETAIL_INHALT='JA' && DETAIL_ZUORDNUNG_TABELLE = 'MIETVERTRAG' && DETAIL_AKTUELL = '1' && MIETVERTRAG_AKTUELL = '1' && (MIETVERTRAG_BIS='0000-00-00' OR MIETVERTRAG_BIS>=CURDATE())");
	
	$numrows = mysql_numrows($result);
		if($numrows>0){
		while ($row = mysql_fetch_assoc($result)) $my_array[] = $row;	
		
		return $my_array;
		}else{
		return FALSE;	
		}
	
	}
	
/*Funktion zur Ermittlung der Kontodaten für das Einzugsverfahren*/
	function einzugsverfahren_teilnehmer(){
		$monat = date("m");
		#$monat = '09';
		$jahr = date("Y");
		
		
		$this->alle_teilnehmer = $this->mitvertrag_einzugsverfahren_arr();
		#print_r($this->alle_teilnehmer);
		$anzahl_teilnehmer= count($this->alle_teilnehmer);
		for($a=0;$a<$anzahl_teilnehmer;$a++){
		$mietvertrag_id = $this->alle_teilnehmer[$a]['MIETVERTRAG_NR'];
		/*Kontoinhaber ermitteln*/
		$result = mysql_query ("SELECT DETAIL_INHALT AS KONTO_INHABER
FROM `DETAIL`
WHERE DETAIL_NAME = 'Kontoinhaber-AutoEinzug' && DETAIL_ZUORDNUNG_TABELLE = 'MIETVERTRAG' && DETAIL_AKTUELL = '1' && DETAIL_ZUORDNUNG_ID = '$mietvertrag_id'");
		$row = mysql_fetch_assoc($result);
		$this->alle_teilnehmer[$a]['KONTOINHABER'] = $row['KONTO_INHABER'];
		
		/*Kontonummer für das Einzugsverfahren ermitteln*/
		$result = mysql_query ("SELECT DETAIL_INHALT AS KONTONUMMER
FROM `DETAIL`
WHERE DETAIL_NAME = 'Kontonummer-AutoEinzug' && DETAIL_ZUORDNUNG_TABELLE = 'MIETVERTRAG' && DETAIL_AKTUELL = '1' && DETAIL_ZUORDNUNG_ID = '$mietvertrag_id'");
		$row = mysql_fetch_assoc($result);
		$this->alle_teilnehmer[$a]['KONTONUMMER'] = $row['KONTONUMMER'];
		
		/*BLZ ermitteln*/
		$result = mysql_query ("SELECT DETAIL_INHALT AS BLZ FROM `DETAIL` WHERE DETAIL_NAME = 'BLZ-AutoEinzug' && DETAIL_ZUORDNUNG_TABELLE = 'MIETVERTRAG' && DETAIL_AKTUELL = '1' && DETAIL_ZUORDNUNG_ID = '$mietvertrag_id'");
		$row = mysql_fetch_assoc($result);
		$this->alle_teilnehmer[$a]['BLZ'] = $row['BLZ'];
		
		/*EINHEIT_ID ermitteln*/
		$result = mysql_query ("SELECT EINHEIT_ID FROM MIETVERTRAG WHERE MIETVERTRAG_AKTUELL='1' && MIETVERTRAG_ID='$mietvertrag_id' ORDER BY MIETVERTRAG_DAT DESC LIMIT 0,1");
		$row = mysql_fetch_assoc($result);
		$this->alle_teilnehmer[$a]['EINHEIT_ID'] = $row['EINHEIT_ID'];
		$einheit_id= $row['EINHEIT_ID'];
		/*EINHEIT_KURZNAME ermitteln*/
		
		$result = mysql_query ("SELECT EINHEIT_KURZNAME FROM EINHEIT WHERE EINHEIT_AKTUELL='1' && EINHEIT_ID='$einheit_id' ORDER BY EINHEIT_DAT DESC LIMIT 0,1");
		$row = mysql_fetch_assoc($result);
		$this->alle_teilnehmer[$a]['EINHEIT_NAME'] = $row['EINHEIT_KURZNAME'];
		$this->geld_konten_id('Mietvertrag', $mietvertrag_id);
		$this->alle_teilnehmer[$a]['KONTO_ID'] = $this->temp_konto_id;
		
		/*Einzugsart aus Detailsermitteln*/
		$einzugsart = $this->einzugsart_ermitteln($mietvertrag_id);
		/*Wenn nicht definiert dann Summe aus Saldo*/
		$mietkonto = new mietkonto;
				
		/*if(!$einzugsart){
		$mietsumme = $mietkonto->mietkontostand_ausrechnen($mietvertrag_id);	
		$this->alle_teilnehmer[$a]['Autoeinzugsart'] ="Aktuelles Saldo komplett";
			/*Falls das Mietkonto in den positiven Bereich wandert, nur reguläre Miete ziehen*/
		/*	$mietsumme = number_format($mietsumme, 2, ".", "");
			if($mietsumme >= "0.00"){
			$mietsumme = $mietkonto->summe_forderung_monatlich($mietvertrag_id, $monat, $jahr);
			}
		}*/
		
		/*Wenn keine Einzugsart bestimmt, Einzugsart auf Aktuelles Saldo komplett stellen*/
		if(!$einzugsart){
		$this->alle_teilnehmer[$a]['Autoeinzugsart'] ="Aktuelles Saldo komplett";
		$einzugsart == "Aktuelles Saldo komplett";
		}		
		if($einzugsart == "Aktuelles Saldo komplett"){
		#$mietsumme = $mietkonto->mietkontostand_ausrechnen($mietvertrag_id);
		$mietk = new miete;
		#$mietsumme = $mietk->saldo_berechnen($mietvertrag_id);
		$mietsumme = $mietk->saldo_berechnen_monatsgenau($mietvertrag_id, $monat, $jahr);
		echo "$monat $jahr MV $mietvertrag_id = $mietsumme<br>";
		$this->alle_teilnehmer[$a]['Autoeinzugsart'] = $einzugsart;
		$mietsumme = number_format($mietsumme, 2, ".", "");
			#echo "$mietvertrag_id $mietsumme<br>";
			if($mietsumme >= '0.00'){
			
			#$mietsumme_ford = $mietkonto->summe_forderung_monatlich($mietvertrag_id, $monat, $jahr);
			#$ziehen = $mietsumme_ford - $mietsumme;
			#echo "F: $mietvertrag_id $mietsumme_ford $mietsumme $ziehen<br>";
			#$mietsumme = $mietsumme_mm - $mietsumme;
			$mietsumme = '0.00';
			}
		}
		if($einzugsart == "Nur die Summe aus Vertrag"){
		$mietsumme = $mietkonto->summe_forderung_monatlich($mietvertrag_id, $monat, $jahr);
		$this->alle_teilnehmer[$a]['Autoeinzugsart'] = $einzugsart;	
		#echo "<h1>".$this->alle_teilnehmer[$a]['KONTOINHABER']." $mietsumme</h1>";
		}
		if($einzugsart == "Ratenzahlung"){
		$summe_raten = $mietkonto->summe_rate_monatlich($mietvertrag_id, $monat, $jahr);	
		$forderung_monatlich = $mietkonto->summe_forderung_monatlich($mietvertrag_id, $monat, $jahr);
		$mietsumme = $summe_raten + $forderung_monatlich;
		$this->alle_teilnehmer[$a]['Autoeinzugsart'] = $einzugsart;
		}
		if($mietsumme < 0){
		#echo "K: $mietvertrag_id $mietsumme<br>";
			$mietsumme = substr($mietsumme, 1); //Minusvorzeichen entfernen
		}		 
		$mietsumme = number_format($mietsumme, 2, ".", "");
		if($mietsumme>'0.00'){
		#echo "K: $mietvertrag_id $mietsumme<br>";
		$this->alle_teilnehmer[$a]['MIETSUMME'] = $mietsumme;
		}else{
		
		#unset($this->alle_teilnehmer[$a]);
		}
		$mietsumme = 0;
		}//end for
  		#return $mietvertraege;
	/*echo "<pre>";
	print_r($this);
	echo "</pre>";
	*/
	}//end function



	/*Ermittlung der Einzugsart*/
	function einzugsart_ermitteln($mietvertrag_id){
	$result = mysql_query ("SELECT DETAIL_INHALT AS EINZUGSART FROM `DETAIL`
WHERE DETAIL_NAME = 'Autoeinzugsart' && DETAIL_ZUORDNUNG_TABELLE = 'MIETVERTRAG' && DETAIL_AKTUELL = '1' && DETAIL_ZUORDNUNG_ID='$mietvertrag_id' ORDER BY DETAIL_DAT DESC LIMIT 0,1");
	
	$numrows = mysql_numrows($result);
		if($numrows<1){
		return false;
		}else{
		$row = mysql_fetch_assoc($result);
		$einzugsart = $row['EINZUGSART'];	
		
		return $einzugsart;
			
	}
}	
	
/*Diese Funktion ermittelt die hinterlegten vererbbaren Geldkontonummern aus Berlussimo*/
	function geld_konten_id($kostentraeger_typ, $kostentraeger_id){
		$geldkonten_anzahl =$this->geldkonten_anzahl($kostentraeger_typ, $kostentraeger_id);
		
		if($geldkonten_anzahl>0){
		$konten_arr = $this->geldkonten_arr($kostentraeger_typ, $kostentraeger_id);	
		#echo "<b>$kostentraeger_typ - $geldkonten_anzahl<br></b>";
		$this->temp_konto_id = $konten_arr['0']['KONTO_ID'];
		}
		else{
			$error=true;
			if($kostentraeger_typ == 'Mietvertrag'){
			$mietvertrag_info = new mietvertrag;
			$einheit_id =$mietvertrag_info->get_einheit_id_von_mietvertrag($kostentraeger_id);
			$this->geld_konten_id('Einheit', $einheit_id);
			#echo "<h3>Mietvertrag $kostentraeger_id Einheit: $einheit_id  </h3>";
			}
			
			if($kostentraeger_typ == 'Einheit'){
			$einheit_info = new einheit;
			$einheit_info->get_einheit_info($kostentraeger_id);
			$this->geld_konten_id('Haus', $einheit_info->haus_id);
			#echo "<h3>Einheit $kostentraeger_id Haus: ".$einheit_info->haus_id." </h3>";
			}
			
			if($kostentraeger_typ == 'Haus'){
			$haus_info = new haus;
			$haus_info->get_haus_info($kostentraeger_id);
			$this->geld_konten_id('Objekt', $haus_info->objekt_id);
			#echo "<h3>Haus $kostentraeger_id Objekt: ".$haus_info->objekt_id." </h3>";
			}
			
		}
	
	}		
		
	/*Funktion zur Ermittlung der Anzahl der Geldkonten*/
	function geldkonten_anzahl($kostentraeger_typ, $kostentraeger_id){
		$result = mysql_query ("SELECT GELD_KONTEN.KONTO_ID, GELD_KONTEN.BEGUENSTIGTER, GELD_KONTEN.KONTONUMMER, GELD_KONTEN.BLZ FROM GELD_KONTEN_ZUWEISUNG, GELD_KONTEN WHERE KOSTENTRAEGER_TYP = '$kostentraeger_typ' && KOSTENTRAEGER_ID = '$kostentraeger_id' && GELD_KONTEN.KONTO_ID = GELD_KONTEN_ZUWEISUNG.KONTO_ID && GELD_KONTEN_ZUWEISUNG.AKTUELL = '1' && GELD_KONTEN.AKTUELL = '1' ORDER BY GELD_KONTEN.KONTO_ID ASC");
	
	$numrows = mysql_numrows($result);
	return $numrows;
	}
	
	function dtaus_datei_speichern($filename, $string){
	$doc_root = $_SERVER['DOCUMENT_ROOT'];
	$dtaus_ordner = 'dtaus';
	$jahr = date("Y");
	$monat = date("m");
	#echo $dtaus_ordner;
	$jahres_ordner = "$dtaus_ordner/$jahr";
	$monats_ordner = "$jahres_ordner/$monat";
	if (!file_exists($dtaus_ordner)){
	mkdir ($dtaus_ordner,0777);
	}
	if (!file_exists($jahres_ordner)){
	mkdir ($jahres_ordner,0777);
	}
	if (!file_exists($monats_ordner)){
	mkdir ($monats_ordner,0777);
	}
		
$filename_neu = "$monats_ordner/$filename";
	if (!file_exists($filename_neu)){
	$fhandle = fopen($filename_neu,"w");
	fwrite($fhandle,$string);
	fclose($fhandle);
		echo "<br>$filename_neu erstellt";
	chmod($filename_neu, 0644);
	#system("rm $dir -R");  //ordner abrechnungsjahr löschen
	echo "<table  border=3>";
	echo "<tr class=\"feldernamen\"><td><a href=\"$_SERVER[SCRIPT_ROOT]$filename_neu\"><b>DOWNLOAD ALS DTA-DATEI</a></b></td></tr>";
	echo "<tr><td>$string</td></tr>";
	echo "</table>";
	
}else{
	#unlink($filename_neu); // Datei löschen
	#exec("rm $monats_ordner/*.*"); 'Monatsordner aufräumen'
	#exec("rm $dtaus_ordner -R"); '/dtaus komplett löschen'
	echo "<b>Kein Schreiben möglich<br>";
	echo "Die Datei existiert bereits als $filename_neu<br></b>";
	echo "<tr><td><a href=\"$_SERVER[SCRIPT_ROOT]$filename_neu\"><b>DOWNLOAD ALS DTA-DATEI</a></b></td></tr>";
	}
}

function dtaus_importdatei_speichern($filename, $string){
	$doc_root = $_SERVER['DOCUMENT_ROOT'];
	$dtaus_ordner = 'dtaus';
	$jahr = date("Y");
	$monat = date("m");
	#echo $dtaus_ordner;
	$jahres_ordner = "$dtaus_ordner/$jahr";
	$monats_ordner = "$jahres_ordner/$monat";
	if (!file_exists($dtaus_ordner)){
	mkdir ($dtaus_ordner,0777);
	}
	if (!file_exists($jahres_ordner)){
	mkdir ($jahres_ordner,0777);
	}
	if (!file_exists($monats_ordner)){
	mkdir ($monats_ordner,0777);
	}
		
	$filename_neu = "$monats_ordner/$filename";
	if (!file_exists($filename_neu)){
	$fhandle = fopen($filename_neu,"w");
	fwrite($fhandle,$string);
	fclose($fhandle);
		echo "<br>$filename_neu erstellt";
	chmod($filename_neu, 0644);
	#system("rm $dir -R");  //ordner abrechnungsjahr löschen
	echo "<table  border=3>";
	echo "<tr class=\"feldernamen\"><td>IMPORTDATEI</td></tr>";
	echo "<tr><td>$string</td></tr>";
	echo "</table>";
	
}else{
	unlink($filename_neu); // Datei löschen
	#exec("rm $monats_ordner/*.*"); 'Monatsordner aufräumen'
	#exec("rm $dtaus_ordner -R"); '/dtaus komplett löschen'
	echo "<br><b>Kein Schreiben möglich<br>";
	echo "Die Datei existiert bereits als $filename_neu<br></b>";
	}
}

	


}//end class

function objekt_auswahl_liste($link){
	if(isset($_REQUEST['objekt_id']) && !empty($_REQUEST['objekt_id'])){
	$_SESSION['objekt_id'] = $_REQUEST['objekt_id'];	
	}
	
	echo "<div class=\"objekt_auswahl\">";
	$mieten = new mietkonto;
	$mieten->erstelle_formular("Objekt auswählen...", NULL);
		
	if(isset($_SESSION['objekt_id'])){
 	$objekt_kurzname = new objekt;
 	$objekt_kurzname->get_objekt_name($_SESSION['objekt_id']);
 	echo "<p>&nbsp;<b>Ausgewähltes Objekt</b> -> $objekt_kurzname->objekt_name ->";
	}else{
 	echo "<p>&nbsp;<b>Objekt auswählen</b>";
	}
	
	$objekte = new objekt;
	$objekte_arr = $objekte->liste_aller_objekte();
	$anzahl_objekte = count($objekte_arr);
	
	for($i=0;$i<=$anzahl_objekte;$i++){
	$obj_id = $objekte_arr[$i]['OBJEKT_ID'];
	$objekt_name = $objekte_arr[$i]['OBJEKT_KURZNAME'];
	echo "<a class=\"objekt_auswahl_buchung\" href=\"$link&objekt_id=$obj_id\">$objekt_name</a>&nbsp;";	
#if($i==7){
#	echo "<br>";
#}
	}
$mieten->ende_formular();
echo "</div>";
}
	




?>
