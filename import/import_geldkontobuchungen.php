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
 * @filesource   $HeadURL: http://192.168.2.52/svn/berlussimo_1/tags/02.11.2010 - Downloadversion 0.27/import/import_geldkontobuchungen.php $
 * @version      $Revision: 6 $
 * @modifiedby   $LastChangedBy: sivac $
 * @lastmodified $Date: 2010-09-21 10:34:50 +0200 (Di, 21 Sep 2010) $
 * 
 */

/*
 * Das ist eine Test bzw. Spieledatei, aus der kann man viel lernen kann
 * 
 */

 
include("config.php");
include_once("../classes/mietkonto_class.php");
include_once("../classes/berlussimo_class.php");
include_once("../includes/allgemeine_funktionen.php");
ob_start();
import_me('geld_214');
#import_me('geld3012');
#import_me('buchungen2012_10000');
#import_me('buchungen2012_20000');
#import_me('buchungen2012_30000');
#kontrolle_zb_mb();
function import_me($tabelle){
$tabelle_in_gross = strtoupper($tabelle); // Tabelle in GRO�BUCHSTABEN
$datei = "$tabelle.csv"; //DATEINAME
#$CSVarray = get_csv($file); 
$array = get_csv($datei); //DATEI IN ARRAY EINLESEN
#echo "<pre>";
#print_r($array);
echo $array[0]; //ZEILE 0 mit �berschriften
$feldernamen[] = explode(":" , $array[0]); //FELDNAMEN AUS ZEILE 0 IN ARRAY EINLESEN
$anzahl_felder = count($feldernamen[0]); // ANZAHL DER IMPORT FELDER
$feld1 = $feldernamen[0][0]; //FELD1 - IMPORT nur zur info
echo "<h1>$feld1</h1>"; 


echo "<b>Importiere daten aus $datei nach MYSQL $tabelle_in_gross:</b><br><br>";

	#echo "<pre>";
	#print_r($zeile);
	#echo "</pre>";
	
	
$anzahl_zeilen = count($array);
	
for ($i = 5000; $i < 9175; $i++) //Datei ab Zeile1 einlesen, weil Zeile 0 �berschrift ist 
#	for ($i = 2460; $i < 9175; $i++) //Datei ab Zeile1 einlesen, weil Zeile 0 �berschrift ist
	{
	
	
	$zeile[$i] = $array[$i]; // Zeile in Array einlesen
	$red = $zeile[$i];
	
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
	
	
	/*MV begin*/
	$form = new mietkonto;
	$einheit_name = rtrim(ltrim($zeile[$i][8]));
	#echo "$einheit_name<br>";
	$geldkonto_ins = new geld_konten_id_ermitteln;
	if(!empty($einheit_name)){
	/*Einheit*/
	$einheit_kostentraeger_id = einheit_id_aus_transtab($einheit_name);
	$geldkonto_ins->geld_konten_id_ermitteln_f('Einheit', $einheit_kostentraeger_id);
	$einheit_geldkonto_id= $geldkonto_ins->konto_id;
	
	/*$objekt_kostentraeger_name = rtrim(ltrim($zeile[$i][7]));
	if($objekt_kostentraeger_name == 'II-2007'){
	$objekt_kostentraeger_id = '1';	
	}
	$objekt_kostentraeger_name = rtrim(ltrim($zeile[$i][7]));
	if($objekt_kostentraeger_name == 'III-2007'){
	$objekt_kostentraeger_id = '2';	
	}
	
	$objekt_kostentraeger_name = rtrim(ltrim($zeile[$i][7]));
	if($objekt_kostentraeger_name == 'V-2007'){
	$objekt_kostentraeger_id = '3';	
	}
	
	$objekt_kostentraeger_name = rtrim(ltrim($zeile[$i][7]));
	if($objekt_kostentraeger_name == 'E-2007'){
	$objekt_kostentraeger_id = '4';	
	}
	$objekt_kostentraeger_name = rtrim(ltrim($zeile[$i][7]));
	if($objekt_kostentraeger_name == 'FON-2007'){
	$objekt_kostentraeger_id = '12';	
	}
	
	if($objekt_kostentraeger_name == 'GBN-8-2007' OR $objekt_kostentraeger_name == 'GBN-7-2007' OR $objekt_kostentraeger_name == 'GBN-5-2007' OR $objekt_kostentraeger_name == 'GBN-G-2007'){
	$objekt_kostentraeger_id = '13';
}

if($objekt_kostentraeger_name == 'HW-35-2007' OR $objekt_kostentraeger_name == 'HW-37-39-2007' OR $objekt_kostentraeger_name == 'HW-41-2007' OR $objekt_kostentraeger_name == 'GBN-G-2007'){
	$objekt_kostentraeger_id = '14';
}

$objekt_kostentraeger_name = rtrim(ltrim($zeile[$i][7]));
	if($objekt_kostentraeger_name == 'L�-2007'){
	$objekt_kostentraeger_id = '15';	
	}*/
	$objekt_kostentraeger_id = rtrim(ltrim($zeile[$i][7]));

	$geldkonto_ins->geld_konten_id_ermitteln_f('Objekt', $objekt_kostentraeger_id);
	$objekt_geldkonto_id = $geldkonto_ins->konto_id;
		/*�berpr�fen ob Einheit Geldkonto = Objektgeldkonto und falls unterschiedlich buchen auf Objektgeldkonto weil FM das Objektgeldkonto unabh�ngig von Einheit belastet. Nur so stimmt die Kontobelastung und -buchung*/
		if($einheit_geldkonto_id!=$objekt_geldkonto_id){
		#echo "<h1> E_KONTO: $einheit_geldkonto_id != OBJEKT_KONTO:$objekt_geldkonto_id</h1>";
		$geldkonto_ins->konto_id = $objekt_geldkonto_id;
		$kostentraeger_typ = 'Objekt';
		$kostentraeger_id = $objekt_kostentraeger_id;
		}else{
		$kostentraeger_typ = 'Einheit';
		$geldkonto_ins->konto_id = $einheit_geldkonto_id;	
		$kostentraeger_id = $einheit_kostentraeger_id;
		}
	#echo "NACH �NDERUNG KT:$kostentraeger_typ KID:$kostentraeger_id KONTO:$geldkonto_ins->konto_id<br>";
	}else{
		$kostentraeger_typ = 'Objekt';
		$kostentraeger_id = rtrim(ltrim($zeile[$i][7]));
		$geldkonto_ins->geld_konten_id_ermitteln_f('Objekt', $kostentraeger_id);
	}
	#print_r($geldkonto_ins);
	$v_zweck =  rtrim(ltrim($zeile[$i][3])); 
	if(!preg_match("/Miete Sollstellung/i", $v_zweck)){
	if(!empty($geldkonto_ins->konto_id) && !empty($kostentraeger_typ) && !empty($kostentraeger_id)){
	#if(!empty($kostentraeger_typ) && !empty($kostentraeger_id)){
	
	$datum = $zeile[$i][1];
	#echo "$i. $datum<br>";
	$datum_arr = explode(".", $datum);
	$tag =  $datum_arr[0];
	$monat = $datum_arr[1];
	$jahr = $datum_arr[2];
	$datum_sql = "$jahr-$monat-$tag";
	$buchungskonto = rtrim(ltrim($zeile[$i][9]));
	$buchungskonto = str_replace("'", "", $buchungskonto);
	
	$buchungskonto = substr($buchungskonto, 0, 4);
	#Beispiel 5: substr ( '1234567', -2, 3 )
	$v_zweck =  rtrim(ltrim($zeile[$i][3])); 
	#$v_zweck = str_replace("'", "", $v_zweck);
	#$v_zweck = str_replace("\n", " ", $v_zweck);
	#$v_zweck = str_replace("\r", " ", $v_zweck);
		
	$betrag = rtrim(ltrim($zeile[$i][2]));
	$betrag = $form->nummer_komma2punkt($betrag);
	#$zb_exists = $form->check_zahlbetrag('888888', $kostentraeger_typ, $kostentraeger_id, $datum_sql, $betrag, $v_zweck, $geldkonto_ins->konto_id, $buchungskonto);
	#if(!$zb_exists){
		#echo "Existiert nicht, wird hinzugef�gt:<br>$zeile[$i]<br>";
	
	
	
		insert_geldbuchung($geldkonto_ins->konto_id, $buchungskonto, '888888', 'IMPORT', $v_zweck, $datum_sql, $kostentraeger_typ, $kostentraeger_id, $betrag);
	#}
	}else{
	#	echo "<h1>$i KTYP:$kostentraeger_typ, K_ID: $kostentraeger_id GELDKONTO: $geldkonto_ins->konto_id</h1>";
	echo "<pre>";
	print_r($red);
	echo "</pre>";
	}
	}else{
	#	echo "$i - sollstellung<br>";
	}
	
		
	
	$zb_exists = $form->check_zahlbetrag('888888', $kostentraeger_typ, $kostentraeger_id, $datum_sql, $betrag, $v_zweck, $geldkonto_ins->konto_id, $buchungskonto);
	if(!$zb_exists && !preg_match("/Miete Sollstellung/i", $v_zweck)){
		echo "Nicht importiert Zeile $i +1:<br><br>";
		print_r($zeile[$i]);
	}
	
	unset($geldkonto_ins->konto_id);
	unset($kostentraeger_id);
	unset($kostentraeger_typ);
	unset($einheit_name);
	unset($geldkonto_ins);
	}//END FOR
} 



function get_csv($filename, $delim =","){

    $row = 0;
    $dump = array();
   
    $f = fopen ($filename,"r");
    $size = filesize($filename)+1;
    while ($data = fgetcsv($f, $size, $delim)) {
        $dump[$row] = $data;
        //echo $data[1]."<br>";
        $row++;
    }
    fclose ($f);
   
    return $dump;
} 

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


function insert_geldbuchung($geldkonto_id, $buchungskonto, $auszugsnr, $rechnungsnr, $v_zweck, $datum, $kostentraeger_typ, $kostentraeger_id, $betrag){
$last_id = last_id('GELD_KONTO_BUCHUNGEN');
$last_id = $last_id + 1;	
mysql_query("INSERT INTO GELD_KONTO_BUCHUNGEN VALUES(NULL, '$last_id', '$auszugsnr', '$rechnungsnr', '$betrag', '$v_zweck', '$geldkonto_id', '$buchungskonto', '$datum', '$kostentraeger_typ', '$kostentraeger_id', '1')");
#echo "'$last_id', '$auszugsnr', '$betrag', '$v_zweck', '$geldkonto_id', '$buchungskonto', '$datum', '$kostentraeger_typ', '$kostentraeger_id' <b>1</b><br>";
}


function mv_id_aus_transtab($fm_einheitenname){
$db_abfrage = "SELECT MIETVERTRAG_ID FROM TRANSFER_TAB WHERE RTRIM( LTRIM( FM_Einheitenname ) ) ='$fm_einheitenname'  order by MIETVERTRAG_ID DESC limit 0,1";	
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
