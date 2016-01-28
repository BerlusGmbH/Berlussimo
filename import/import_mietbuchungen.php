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
 * @filesource   $HeadURL: http://192.168.2.52/svn/berlussimo_1/tags/02.11.2010 - Downloadversion 0.27/import/import_mietbuchungen.php $
 * @version      $Revision: 6 $
 * @modifiedby   $LastChangedBy: sivac $
 * @lastmodified $Date: 2010-09-21 10:34:50 +0200 (Di, 21 Sep 2010) $
 * 
 */

/*
 * Das ist eine Test bzw. Spieledatei, aus der kann man viel lernen kann
 * 
 */

 ob_start();

/*SCHAUEN DAS IN DER TRANSFER_TAB ALLE MV_IDS VORHANDEN SIND*/

include("config.php");
include_once("../classes/mietkonto_class.php");
include_once("../classes/berlussimo_class.php");
include_once("../includes/allgemeine_funktionen.php");
import_me('miete_214');
#import_me('mieten3012');
#import_me('buchungen2012_10000');
#import_me('buchungen2012_20000');
#import_me('buchungen2012_30000');
#kontrolle_zb_mb();
function import_me($tabelle){
$tabelle_in_gross = strtoupper($tabelle); // Tabelle in GRO�BUCHSTABEN
$datei = "$tabelle.csv"; //DATEINAME
$array = file($datei); //DATEI IN ARRAY EINLESEN




echo $array[0]; //ZEILE 0 mit �berschriften
$feldernamen[] = explode(":" , $array[0]); //FELDNAMEN AUS ZEILE 0 IN ARRAY EINLESEN
$anzahl_felder = count($feldernamen[0]); // ANZAHL DER IMPORT FELDER
$feld1 = $feldernamen[0][0]; //FELD1 - IMPORT nur zur info
echo "<h1>$feld1</h1>"; 
#for($bb=17670;$bb<17750;$bb++){
	#$mzeile = $array[$bb];
	#$mzeile = str_replace(array("\r", "\n"), '', $mzeile); 
	#$mzeile = preg_replace("/[\s][\s]*/"," ",$mzeile); 
	#echo $bb.$mzeile.'<br>';
#}
/*MIT EDITOR csv korrigieren oder \n als Reg. Ausdruck in Calc entfernen*/
echo "<b>Importiere daten aus $datei nach MYSQL $tabelle_in_gross:</b><br><br>";
	#for ($i = 10000; $i < 30163; $i++) //Datei ab Zeile1 einlesen, weil Zeile 0 
	for ($i = 20000; $i < 30163; $i++) //Datei ab Zeile1 einlesen, weil Zeile 0
	{
	#echo "<b>$i</b> ";
	#$file_zeile = str_replace(array("\r", "\n"), '', $array[$i]); 
	#$file_zeile = preg_replace("/[\s][\s]*/"," ",$file_zeile); 
	$zeile[$i] = explode(":" , $array[$i]); // Zeile in Array einlesen
	
	$zeile[$i][0] = textrep($zeile[$i][0]);
	$zeile[$i][1] = textrep($zeile[$i][1]);
	$zeile[$i][2] = textrep($zeile[$i][2]);
	$zeile[$i][3] = textrep($zeile[$i][3]);
	$zeile[$i][4] = textrep($zeile[$i][4]);
	$zeile[$i][5] = textrep($zeile[$i][5]);
	$zeile[$i][6] = textrep($zeile[$i][6]);
	$zeile[$i][7] = textrep($zeile[$i][7]);
	$zeile[$i][8] = textrep($zeile[$i][8]);
	$zeile[$i][9] = textrep($zeile[$i][9]);
	
	#if(count($zeile[$i]) < 8){
	#	echo "<pre>";
	#print_r($zeile[$i]);
	#echo "</pre>";
	
	
	/*MV begin*/
	$form = new mietkonto;
	
	
	$FMeinheit_name = rtrim(ltrim($zeile[$i][0]));
	if(!empty($FMeinheit_name)){
	$datum = rtrim(ltrim($zeile[$i][1]));
	$betrag = rtrim(ltrim($zeile[$i][2]));
	$mv_id = mv_id_aus_transtab($FMeinheit_name);
	#echo "$betrag $FMeinheit_name $mv_id<br>";
	$betrag = explode(",", $betrag);
	$vorkomma = $betrag[0];
	$nachkomma = $betrag[1];
	$betrag = "$vorkomma.$nachkomma";
	$bemerkung = rtrim(ltrim($zeile[$i][3]));
	//abs von negativ auf positiv -2+
	$bemerkung = mysql_escape_string($bemerkung);
	
	if(!isset($mv_id) or $mv_id==0){
	#echo "FEHLT $FMeinheit_name<br>";
	$einheit_id = einheit_id_aus_transtab($FMeinheit_name);
	$kostentraeger_typ = 'Einheit';
	#echo "mv fehlt $FMeinheit_name $kostentraeger_typ $einheit_id $geld_konto_id<br>";
	if(!empty($einheit_id)){
		echo "Einheit $einheit_id in me<br>";
	if(!preg_match("/Miete Sollstellung/i", $bemerkung)){
	
	if (preg_match("/Betriebskostenabrechnung/i", $bemerkung) OR preg_match("/Heizkostenabrechnung/i", $bemerkung) OR preg_match("/Saldo Vortrag Vorverwaltung/", $bemerkung)) {
  # echo "<b>$i Es wurde eine �bereinstimmung gefunden.<br></b>";
	$form = new mietkonto;
	$datum_arr = explode(".", $datum);
	$tag =  $datum_arr[0];
	$monat = $datum_arr[1];
	$jahr = $datum_arr[2];
	#$betrag = substr($betrag, 1);
	$lastday = date('d', mktime(0, 0, -1, $monat, 1, $jahr));
	#$a_datum = "$jahr-$monat-01";
	$a_datum = "$jahr-$monat-$tag";
	$e_datum = "$jahr-$monat-$tag";
	#$e_datum = "$jahr-$monat-$lastday";
	/*if($betrag<0.00 && (preg_match("/Betriebskostenabrechnung/i", $bemerkung) OR preg_match("/Heizkostenabrechnung/i", $bemerkung)) ){
		#$betrag_status = "<b>NACHZAHLUNG</b>";
		$betrag = abs($betrag);
	}
	if($betrag>0.00 && (preg_match("/Betriebskostenabrechnung/i", $bemerkung) OR preg_match("/Heizkostenabrechnung/i", $bemerkung)) ){
		#$betrag_status = "<b>NACHZAHLUNG</b>";
		$betrag = "-".$betrag;
	}
	if(preg_match("/Saldo Vortrag Vorverwaltung/", $bemerkung)){
		#$betrag_status = "<b>NACHZAHLUNG</b>";
		$betrag = $betrag;
	}
	*/
	#echo "$betrag_status $i $mv_id $datum $betrag $bemerkung<br>";		
	#echo "<h1>$lastday</h1>";
	$form->mietentwicklung_speichern('Einheit', $einheit_id, $bemerkung, $betrag, $a_datum, $e_datum);
	}else{
	$kostentraeger_typ = 'Einheit';
	$kostentraeger_id = $einheit_id;
	$geldkonto_einheit = new geld_konten_id_ermitteln;
	$geldkonto_einheit->geld_konten_id_ermitteln_f('Einheit', $einheit_id);	
	if(!empty($geldkonto_einheit->konto_id)){
	$form->import_miete_zahlbetrag_buchen('999999', 'Einheit', $einheit_id, $datum, $betrag, $bemerkung, $geldkonto_einheit->konto_id, '80001');
	echo "$i e_id->zb gespeichert<br>";
	}
}
}else{
	echo "$i - sollst<br>";
}//end if sollstellung

}//ENDE IF EINHEIT
}//!mv_idend if
	
	if(isset($mv_id) && $mv_id!=0){
	if(!preg_match("/Miete Sollstellung/", $bemerkung)){
	$kostentraeger_typ = 'Mietvertrag';
	#echo "$i <b>$mv_id</b> $FMeinheit_name $betrag $datum $bemerkung<br>";
	if (preg_match("/Betriebskostenabrechnung/i", $bemerkung) OR preg_match("/Heizkostenabrechnung/i", $bemerkung) OR preg_match("/Saldo Vortrag Vorverwaltung/", $bemerkung)) {
  # echo "<b>$i Es wurde eine �bereinstimmung gefunden.<br></b>";
	$form = new mietkonto;
	$datum_arr = explode(".", $datum);
	$tag =  $datum_arr[0];
	$monat = $datum_arr[1];
	$jahr = $datum_arr[2];
	#$betrag = substr($betrag, 1);
	$lastday = date('d', mktime(0, 0, -1, $monat, 1, $jahr));
	#$a_datum = "$jahr-$monat-01";
	$a_datum = "$jahr-$monat-$tag";
	$e_datum = "$jahr-$monat-$tag";
	#$e_datum = "$jahr-$monat-$lastday";
	/*
	if($betrag<0.00 && (preg_match("/Betriebskostenabrechnung/i", $bemerkung) OR preg_match("/Heizkostenabrechnung/i", $bemerkung)) ){
		#$betrag_status = "<b>NACHZAHLUNG</b>";
		$betrag = abs($betrag);
	}
	if($betrag>0.00 && (preg_match("/Betriebskostenabrechnung/i", $bemerkung) OR preg_match("/Heizkostenabrechnung/i", $bemerkung)) ){
		#$betrag_status = "<b>NACHZAHLUNG</b>";
		$betrag = "-$betrag";
	}
	if(preg_match("/Saldo Vortrag Vorverwaltung/", $bemerkung)){
		#$betrag_status = "<b>NACHZAHLUNG</b>";
		$betrag = $betrag;
	}
	*/
	#echo "$betrag_status $i $mv_id $datum $betrag $bemerkung<br>";		
	#echo "<h1>$lastday</h1>";
	echo "$i mv->me gespeichert<br>";
	$form->mietentwicklung_speichern('Mietvertrag', $mv_id, $bemerkung, $betrag, $a_datum, $e_datum);
	}else{
	#echo "$i $mv_id $datum $betrag $bemerkung<br>";	
	$kostentraeger_typ = 'Mietvertrag';
	$kostentraeger_id = $mv_id;
	$geldkonto_ins = new geld_konten_id_ermitteln;
	$geldkonto_ins->geld_konten_id_ermitteln_f('Mietvertrag', $mv_id);
	if(!empty($geldkonto_ins->konto_id)){
	$form->import_miete_zahlbetrag_buchen('999999', 'MIETVERTRAG', $mv_id, $datum, $betrag, $bemerkung, $geldkonto_ins->konto_id, '80001');
	echo "$i mv->zb gespeichert<br>";
			}else{
				echo "$i mv->me nicht gespeichert, kein gk<br>";
			}
		}
	}else{
		echo "$i mv soll<br>";
	} // sollmiete
	
	} // kein mv_id
	}//kein einheitname
	
	$zb_exists = $form->check_zahlbetrag('999999', $kostentraeger_typ, $kostentraeger_id, $datum, $betrag, $bemerkung, $geldkonto_ins->konto_id, '80001');
	if(!$zb_exists){
		echo "Nicht importiert Zeile $i +1:<br><br>";
		print_r($zeile[$i]);
	}
	
	
	}//end for
	}//end function
/*for($a=0;$a<count($nicht_vorhanden);$a++){
	$einheit= $nicht_vorhanden[$a];
	echo "$einheit<br>";
}*/
	


function kontrolle_zb_mb(){
$result = mysql_query ("SELECT BUCHUNGSNUMMER, BETRAG FROM MIETE_ZAHLBETRAG");

while ($row = mysql_fetch_assoc($result)){
	$bnr = $row['BUCHUNGSNUMMER'];
	$zb_betrag = $row['BETRAG'];
	$intern = summe_mb($bnr);
#echo $bnr;
	if($zb_betrag != $intern){
	echo "NICHT OK $bnr<br>";
}
}
}

function summe_mb($bnr){
$result = mysql_query ("SELECT SUM(BETRAG) AS INTERN FROM MIETBUCHUNGEN WHERE BUCHUNGSNUMMER='$bnr'");
$row = mysql_fetch_assoc($result);
$intern_betrag = $row['INTERN'];
return $intern_betrag;
}





function mv_id_aus_transtab($fm_einheitenname){
$db_abfrage = "SELECT MIETVERTRAG_ID FROM TRANSFER_TAB WHERE RTRIM( LTRIM( FM_Einheitenname ) ) ='$fm_einheitenname'  && MIETVERTRAG_ID!='0'order by MIETVERTRAG_ID DESC limit 0,1";	
$resultat = mysql_query($db_abfrage) or
           die(mysql_error());

		while (list ($MIETVERTRAG_ID) = mysql_fetch_row($resultat))
		{
		return $MIETVERTRAG_ID;
		} 
}

function einheit_id_aus_transtab($fm_einheitenname){
$db_abfrage = "SELECT EINHEIT_ID FROM TRANSFER_TAB WHERE RTRIM( LTRIM( FM_Einheitenname ) ) ='$fm_einheitenname'  order by MIETVERTRAG_ID DESC limit 0,1";	
$resultat = mysql_query($db_abfrage) or
           die(mysql_error());

		while (list ($EINHEIT_ID) = mysql_fetch_row($resultat))
		{
		return $EINHEIT_ID;
		} 
}


class geld_konten_id_ermitteln{
  /*Berlussimo DATEN*/
  
  var $konto_id;
  
 
  
  /*Diese Funktion ermittelt die hinterlegten vererbbaren Geldkontonummern aus Berlussimo*/
	function geld_konten_id_ermitteln_f($kostentraeger_typ, $kostentraeger_id){
		$geldkonten_anzahl =$this->geldkonten_anzahl($kostentraeger_typ, $kostentraeger_id);
		#echo "$kostentraeger_typ $kostentraeger_id $geldkonten_anzahl<br>";
		if($geldkonten_anzahl>0){
		$konten_arr = $this->geldkonten_arr($kostentraeger_typ, $kostentraeger_id);	
		#echo "<b>$kostentraeger_typ - $geldkonten_anzahl<br></b>";
		$this->konto_id = $konten_arr['0']['KONTO_ID'];
		#return $konten_arr['0']['KONTO_ID'];
		#echo "<br><b>$this->konto_id</b><br>";
		}
		else{
			$error=true;
			if($kostentraeger_typ == 'Mietvertrag'){
			$mietvertrag_info = new mietvertrag;
			$einheit_id =$mietvertrag_info->get_einheit_id_von_mietvertrag($kostentraeger_id);
			$this->geld_konten_id_ermitteln_f('Einheit', $einheit_id);
			#echo "<h3>Mietvertrag $kostentraeger_id Einheit: $einheit_id  </h3>";
			}
			
			if($kostentraeger_typ == 'Einheit'){
			$einheit_info = new einheit;
			$einheit_info->get_einheit_info($kostentraeger_id);
			$this->geld_konten_id_ermitteln_f('Haus', $einheit_info->haus_id);
			#echo "<h3>Einheit $kostentraeger_id Haus: ".$einheit_info->haus_id." </h3>";
			}
			
			if($kostentraeger_typ == 'Haus'){
			$haus_info = new haus;
			$haus_info->get_haus_info($kostentraeger_id);
			$this->geld_konten_id_ermitteln_f('Objekt', $haus_info->objekt_id);
			#echo "<h3>Haus $kostentraeger_id Objekt: ".$haus_info->objekt_id." </h3>";
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
  

	/*Funktion zur Ermittlung der Anzahl der Geldkonten*/
	function geldkonten_anzahl($kostentraeger_typ, $kostentraeger_id){
		$result = mysql_query ("SELECT GELD_KONTEN.KONTO_ID, GELD_KONTEN.BEGUENSTIGTER, GELD_KONTEN.KONTONUMMER, GELD_KONTEN.BLZ FROM GELD_KONTEN_ZUWEISUNG, GELD_KONTEN WHERE KOSTENTRAEGER_TYP = '$kostentraeger_typ' && KOSTENTRAEGER_ID = '$kostentraeger_id' && GELD_KONTEN.KONTO_ID = GELD_KONTEN_ZUWEISUNG.KONTO_ID && GELD_KONTEN_ZUWEISUNG.AKTUELL = '1' && GELD_KONTEN.AKTUELL = '1' ORDER BY GELD_KONTEN.KONTO_ID ASC");
	
	$numrows = mysql_numrows($result);
	return $numrows;
	}
	
	

	
	

}//end class


function textrep($text) {
return str_replace("\r\n",'',$text);
}

 

?>
