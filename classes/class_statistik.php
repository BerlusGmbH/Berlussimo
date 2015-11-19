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
 * @filesource   $HeadURL: http://192.168.2.52/svn/berlussimo_1/tags/02.11.2010 - Downloadversion 0.27/classes/class_statistik.php $
 * @version      $Revision: 15 $
 * @modifiedby   $LastChangedBy: sivac $
 * @lastmodified $Date: 2011-07-07 10:41:33 +0200 (Do, 07 Jul 2011) $
 * 
 */
 
include_once("config.inc.php");
include_once("berlussimo_class.php");
if(file_exists("classes/class_details.php")){
include_once('classes/class_details.php');
}
class statistik{
	


function get_stat($jahr,$objekt_id, $typ_lage){
$b = new objekt;
$this->objekt_name = $b->get_objekt_name($objekt_id);
	echo "OBJEKT $this->objekt_name im Jahr $jahr<hr>";
	$this->akt_jahr = date("Y");
	if($jahr == $this->akt_jahr){
	$a_bis = date("m");	
	}else{
	$a_bis = 12;	
	}
	for($a=1;$a<=$a_bis;$a++){
	if($a<10){
	$monat = "0"."$a";	
	}else{
		$monat = $a;
	}
	$jahr_monat = "$jahr-$monat";
	$this->leerstaende_monat_jahr($jahr_monat,$objekt_id,$typ_lage);
	$this->vermietete_monat_jahr($jahr_monat,$objekt_id,$typ_lage);
$this->gesamt = $this->vermietete + $this->leer;
$this->gesamt_leer = $this->gesamt_leer + $this->leer;
$this->pro_v = $this->vermietete / ($this->gesamt/100);
$this->pro_v = sprintf("%01.2f", $this->pro_v);
$this->pro_l = $this->leer / ($this->gesamt/100);
$this->pro_l = sprintf("%01.2f", $this->pro_l);	
#echo "$monat $jahr ----> VERMIETET $this->vermietete ($this->pro_v %) LEER:$this->leer ($this->pro_l %) <br>";
}
$this->durchschnitt_leer_jahr = $this->gesamt_leer / ($a_bis*$this->gesamt/100);
$this->durchschnitt_leer_jahr = sprintf("%01.2f", $this->durchschnitt_leer_jahr);
echo "<b>DURCHSCHNITT LEERSTAND IM $this->objekt_name IM JAHR $jahr $this->durchschnitt_leer_jahr %</b>";
$vermietet = 100 - $this->durchschnitt_leer_jahr;
$vermietet = sprintf("%01.2f", $vermietet);	
$vermietet = round($vermietet,0);
$this->durchschnitt_leer_jahr = round($this->durchschnitt_leer_jahr,0);
echo "<p><iframe src=\"graph/examples/myPieGraph.php?leerstand=$this->durchschnitt_leer_jahr&vermietet=$vermietet&objekt=$this->objekt_name&jahr=$jahr\" width=\"50%\" height=\"300\" ></iframe></p>";
$this->gesamt_leer = 0;
$this->durchschnitt_leer_jahr = 0;
$vermietet = 0;
echo "<hr>";
}


function form_haus_leer_stat(){
	$f = new formular;
	$f->erstelle_formular('Leerstand Statistik HAUS', null);
	$h = new haus;
	$h->dropdown_haeuser_2('Haus wählen', 'haus_id', 'haus_id');
	$f->hidden_feld('option', 'leer_haus_stat1');
	$f->send_button('BtnSnd', 'anzeigen');
	$f->ende_formular();
}


function stat_haus_anzeigen($haus_id, $jahr){
	$verm_qm = 0;
	$leer_qm = 0;
	echo "<table>";
	for($a=1;$a<13;$a++){
		$monat = $mo = sprintf("%02d",$a);
		$jahr_monat = "$jahr-$monat";
		#$leer += $this->leerstaende_monat_jahr_haus($jahr_monat, $haus_id, '');
		#$verm += $this->vermietet_monat_jahr_haus($jahr_monat, $haus_id, '');
		$leer = $this->leer_monat_jahr_haus_m2($jahr_monat, $haus_id, '');
		$verm = $this->vermietet_monat_jahr_haus_m2($jahr_monat, $haus_id, '');
		
		$leer_qm += $leer;
		$verm_qm += $verm;
		echo "<tr><td>$a.</td><td>$leer</td><td>$verm</td></tr>";
		#echo "$a. $leer_qm $verm_qm<br>";
	}
	echo "</table>";
	#$this->vermietet = $verm/12;
	#$this->leer = $leer/12;
	
	$lj = $leer_qm/12;
	$vj = $verm_qm/12;
	
	$ges = $leer_qm + $verm_qm;
	
	$leer_pro = nummer_punkt2komma($leer_qm/($ges/100));
	$verm_pro = nummer_punkt2komma($verm_qm/($ges/100));
	
	echo "<h1>$jahr</h1>";
	echo "<h2>LEER: $leer_pro %</h2>";
	echo "<h2>VERMIETET: $verm_pro %</h2>";
	
}


function leerstaende_monat_jahr_haus($jahr_monat, $haus_id, $typ_lage){
	if($typ_lage == ''){
		$m_lage ="(TYP LIKE 'Wohnraum' OR TYP LIKE 'Gewerbe') &&";
	}else{
		$m_lage = "TYP LIKE '".$typ_lage."%' &&";
	}
	$db_abfrage = "SELECT OBJEKT_KURZNAME, EINHEIT.HAUS_ID, EINHEIT_ID, EINHEIT_KURZNAME, HAUS_STRASSE, HAUS_NUMMER FROM `EINHEIT` RIGHT JOIN ( HAUS, OBJEKT ) ON ( EINHEIT.HAUS_ID = HAUS.HAUS_ID && HAUS.OBJEKT_ID = OBJEKT.OBJEKT_ID) WHERE EINHEIT.EINHEIT_AKTUELL='1' && HAUS.HAUS_AKTUELL='1' && EINHEIT.HAUS_ID = '$haus_id' && (TYP='Wohnraum' OR TYP='Gewerbe') && EINHEIT_ID NOT IN ( SELECT EINHEIT_ID FROM MIETVERTRAG WHERE MIETVERTRAG_AKTUELL = '1' && DATE_FORMAT( MIETVERTRAG_VON, '%Y-%m' ) <= '$jahr_monat' && ( DATE_FORMAT( MIETVERTRAG_BIS, '%Y-%m' ) >= '$jahr_monat' OR MIETVERTRAG_BIS = '0000-00-00' ) ) ORDER BY EINHEIT_KURZNAME ASC";

	#echo $db_abfrage;
	#echo "<hr>";
	
			$resultat = mysql_query($db_abfrage) or   die(mysql_error());
			$numrows = mysql_numrows($resultat);
	
			return  $numrows;
#return $my_arr;

}

function vermietet_monat_jahr_haus($jahr_monat, $haus_id, $typ_lage){
	if($typ_lage == ''){
		$m_lage ="TYP LIKE 'Wohnraum' OR TYP LIKE 'Gewerbe' && ";
	}else{
		$m_lage = "TYP LIKE '".$typ_lage."%' &&";
	}
	$db_abfrage = "SELECT OBJEKT_KURZNAME, EINHEIT.HAUS_ID, EINHEIT_ID, EINHEIT_KURZNAME, HAUS_STRASSE, HAUS_NUMMER FROM `EINHEIT` RIGHT JOIN ( HAUS, OBJEKT ) ON ( EINHEIT.HAUS_ID = HAUS.HAUS_ID && HAUS.OBJEKT_ID = OBJEKT.OBJEKT_ID) WHERE EINHEIT.EINHEIT_AKTUELL='1' && HAUS.HAUS_AKTUELL='1' && EINHEIT.HAUS_ID = '$haus_id' && (TYP='Wohnraum' OR TYP='Gewerbe') && EINHEIT_ID IN ( SELECT EINHEIT_ID FROM MIETVERTRAG WHERE MIETVERTRAG_AKTUELL = '1' && DATE_FORMAT( MIETVERTRAG_VON, '%Y-%m' ) <= '$jahr_monat' && ( DATE_FORMAT( MIETVERTRAG_BIS, '%Y-%m' ) >= '$jahr_monat' OR MIETVERTRAG_BIS = '0000-00-00' ) ) ORDER BY EINHEIT_KURZNAME ASC";

	#echo $db_abfrage;
	#echo "<hr>";
	
	#while ($row = mysql_fetch_assoc($result)) $my_arr[] = $row;
	$resultat = mysql_query($db_abfrage) or   die(mysql_error());
	$numrows = mysql_numrows($resultat);
	
	return $numrows;
	
	#return $my_arr;

}


function vermietet_monat_jahr_haus_m2($jahr_monat, $haus_id, $typ_lage){
	if($typ_lage == ''){
		$m_lage ="TYP LIKE 'Wohnraum' OR TYP LIKE 'Gewerbe' && ";
	}else{
		$m_lage = "TYP LIKE '".$typ_lage."%' &&";
	}
	$db_abfrage = "SELECT SUM(EINHEIT_QM) AS QM FROM `EINHEIT` RIGHT JOIN ( HAUS, OBJEKT ) ON ( EINHEIT.HAUS_ID = HAUS.HAUS_ID && HAUS.OBJEKT_ID = OBJEKT.OBJEKT_ID) WHERE EINHEIT.EINHEIT_AKTUELL='1' && HAUS.HAUS_AKTUELL='1' && EINHEIT.HAUS_ID = '$haus_id' && (TYP='Wohnraum' OR TYP='Gewerbe') && EINHEIT_ID IN ( SELECT EINHEIT_ID FROM MIETVERTRAG WHERE MIETVERTRAG_AKTUELL = '1' && DATE_FORMAT( MIETVERTRAG_VON, '%Y-%m' ) <= '$jahr_monat' && ( DATE_FORMAT( MIETVERTRAG_BIS, '%Y-%m' ) >= '$jahr_monat' OR MIETVERTRAG_BIS = '0000-00-00' ) ) ORDER BY EINHEIT_KURZNAME ASC";


	$resultat = mysql_query($db_abfrage) or   die(mysql_error());
	$numrows = mysql_numrows($resultat);
	if($numrows){
		$row = mysql_fetch_assoc($resultat);
		return $row['QM'];
	}else{
		return '0.00';
	}
	

}

function leer_monat_jahr_haus_m2($jahr_monat, $haus_id, $typ_lage){
	if($typ_lage == ''){
		$m_lage ="TYP LIKE 'Wohnraum' OR TYP LIKE 'Gewerbe' && ";
	}else{
		$m_lage = "TYP LIKE '".$typ_lage."%' &&";
	}
	$db_abfrage = "SELECT SUM(EINHEIT_QM) AS QM FROM `EINHEIT` RIGHT JOIN ( HAUS, OBJEKT ) ON ( EINHEIT.HAUS_ID = HAUS.HAUS_ID && HAUS.OBJEKT_ID = OBJEKT.OBJEKT_ID) WHERE EINHEIT.EINHEIT_AKTUELL='1' && HAUS.HAUS_AKTUELL='1' && EINHEIT.HAUS_ID = '$haus_id' && (TYP='Wohnraum' OR TYP='Gewerbe') && EINHEIT_ID NOT IN ( SELECT EINHEIT_ID FROM MIETVERTRAG WHERE MIETVERTRAG_AKTUELL = '1' && DATE_FORMAT( MIETVERTRAG_VON, '%Y-%m' ) <= '$jahr_monat' && ( DATE_FORMAT( MIETVERTRAG_BIS, '%Y-%m' ) >= '$jahr_monat' OR MIETVERTRAG_BIS = '0000-00-00' ) ) ORDER BY EINHEIT_KURZNAME ASC";

	#echo $db_abfrage;
	
	$resultat = mysql_query($db_abfrage) or   die(mysql_error());
	$numrows = mysql_numrows($resultat);
	if($numrows){
		$row = mysql_fetch_assoc($resultat);
		return $row['QM'];
	}else{
		return '0.00';
	}
	
}





function leerstaende_monat_jahr($jahr_monat, $objekt_id, $typ_lage){
if($typ_lage == ''){
	$m_lage ='';
}else{
$m_lage = "EINHEIT_LAGE LIKE '".$typ_lage."%' &&";
}
$result = mysql_query ("SELECT OBJEKT_KURZNAME, EINHEIT_ID, EINHEIT_KURZNAME, HAUS_STRASSE, HAUS_NUMMER

FROM `EINHEIT`
RIGHT JOIN (
HAUS, OBJEKT
) ON ( EINHEIT.HAUS_ID = HAUS.HAUS_ID && HAUS.OBJEKT_ID = OBJEKT.OBJEKT_ID && OBJEKT.OBJEKT_ID = '$objekt_id' )
WHERE $m_lage  EINHEIT_AKTUELL='1' && EINHEIT_ID NOT
IN (

SELECT EINHEIT_ID
FROM MIETVERTRAG
WHERE MIETVERTRAG_AKTUELL = '1' && DATE_FORMAT( MIETVERTRAG_VON, '%Y-%m' ) <= '$jahr_monat' && ( DATE_FORMAT( MIETVERTRAG_BIS, '%Y-%m' ) >= '$jahr_monat'
OR MIETVERTRAG_BIS = '0000-00-00' )
)
ORDER BY EINHEIT_KURZNAME ASC");

while ($row = mysql_fetch_assoc($result)) $my_arr[] = $row;
$this->leer = 0;
$this->leer = count($my_arr);
return $my_arr;

}


function vermietete_monat_jahr($jahr_monat,$objekt_id, $typ_lage){
if($typ_lage != ''){
$m_lage = "EINHEIT_LAGE LIKE '".$typ_lage."%' &&";
}else{
	$m_lage ='';
}
$result = mysql_query ("SELECT EINHEIT_ID, EINHEIT_KURZNAME, HAUS_STRASSE, HAUS_NUMMER

FROM `EINHEIT`
RIGHT JOIN (
HAUS, OBJEKT
) ON ( EINHEIT.HAUS_ID = HAUS.HAUS_ID && HAUS.OBJEKT_ID = OBJEKT.OBJEKT_ID && OBJEKT.OBJEKT_ID = '$objekt_id' )
WHERE $m_lage  EINHEIT_AKTUELL='1' && EINHEIT_ID IN (

SELECT EINHEIT_ID
FROM MIETVERTRAG
WHERE MIETVERTRAG_AKTUELL = '1' && DATE_FORMAT( MIETVERTRAG_VON, '%Y-%m' ) <= '$jahr_monat' && ( DATE_FORMAT( MIETVERTRAG_BIS, '%Y-%m' ) >= '$jahr_monat'
OR MIETVERTRAG_BIS = '0000-00-00' )
)
GROUP BY EINHEIT_ID ORDER BY EINHEIT_KURZNAME ASC");


while ($row = mysql_fetch_assoc($result)) $my_arr[] = $row;
$this->vermietete = 0;
$this->vermietete = count($my_arr);
return $my_arr;

}

function vermietete_monat_jahr_haus($jahr_monat,$haus_id, $typ_lage){
if($typ_lage != ''){
$m_lage = "EINHEIT_LAGE LIKE '".$typ_lage."%' &&";
}else{
	$m_lage ='';
}
$result = mysql_query (" SELECT EINHEIT_ID, EINHEIT_KURZNAME, HAUS_STRASSE, HAUS_NUMMER
FROM `EINHEIT`
RIGHT JOIN (
HAUS
) ON ( EINHEIT.HAUS_ID = HAUS.HAUS_ID && EINHEIT.HAUS_ID = '$haus_id' )
WHERE EINHEIT_AKTUELL = '1' && EINHEIT_ID
IN (

SELECT EINHEIT_ID
FROM MIETVERTRAG
WHERE MIETVERTRAG_AKTUELL = '1' && DATE_FORMAT( MIETVERTRAG_VON, '%Y-%m' ) <= '$jahr_monat' && ( DATE_FORMAT( MIETVERTRAG_BIS, '%Y-%m' ) >= '$jahr_monat'
OR MIETVERTRAG_BIS = '0000-00-00' )
)
ORDER BY EINHEIT_KURZNAME ASC
LIMIT 0 , 30 ");


while ($row = mysql_fetch_assoc($result)) $my_arr[] = $row;
$this->vermietete = 0;
$this->vermietete = count($my_arr);
return $my_arr;

}


function leerstand_monat_jahr_haus($jahr_monat,$haus_id, $typ_lage){
if($typ_lage != ''){
$m_lage = "EINHEIT_LAGE LIKE '".$typ_lage."%' &&";
}else{
	$m_lage ='';
}
$result = mysql_query (" SELECT EINHEIT_ID, EINHEIT_KURZNAME, HAUS_STRASSE, HAUS_NUMMER
FROM `EINHEIT`
RIGHT JOIN (
HAUS
) ON ( EINHEIT.HAUS_ID = HAUS.HAUS_ID && EINHEIT.HAUS_ID = '$haus_id' )
WHERE EINHEIT_AKTUELL = '1' && EINHEIT_ID NOT
IN (

SELECT EINHEIT_ID
FROM MIETVERTRAG
WHERE MIETVERTRAG_AKTUELL = '1' && DATE_FORMAT( MIETVERTRAG_VON, '%Y-%m' ) <= '$jahr_monat' && ( DATE_FORMAT( MIETVERTRAG_BIS, '%Y-%m' ) >= '$jahr_monat'
OR MIETVERTRAG_BIS = '0000-00-00' )
)
ORDER BY EINHEIT_KURZNAME ASC
LIMIT 0 , 30 ");


while ($row = mysql_fetch_assoc($result)) $my_arr[] = $row;
$this->vermietete = 0;
$this->vermietete = count($my_arr);
return $my_arr;

}



function summe_sollmiete_alle(){
$o = new objekt;
$objekt_arr = 	$o->liste_aller_objekte();
$anzahl_objekte = count($objekt_arr);

for($a=0;$a<$anzahl_objekte;$a++){
$objekt_id = $objekt_arr[$a]['OBJEKT_ID'];
$this->summe_sollmieten($objekt_id);
	
}
}

function summe_sollmieten($objekt_id){
$o1=new objekt;
$objekt_name = $o1->get_objekt_name($objekt_id);

if(empty($_REQUEST['monat']) && empty($_REQUEST['jahr'])){
$datum = date("Y-m");
$monat = date("m");
$jahr = date("Y");
}else{
$j =$_REQUEST[jahr];
$m = $_REQUEST[monat];
$datum = "$j-$m";
$monat = $m;
$jahr = $j;
	}

$bg = new berlussimo_global;
$link = "?daten=statistik&option=sollmieten_aktuell";
$bg->monate_jahres_links($jahr, $link);


echo "<a href=\"?daten=statistik&option=verwaltergebuehr_objekt_pdf&objekt_id=$objekt_id\">Berechnung für $objekt_name als PDF</a><hr>";
echo "<b>OBJEKT $objekt_name $monat/$jahr</b><br>";
$typ_lage ='';
$vermietete_arr = $this->vermietete_monat_jahr($datum,$objekt_id, $typ_lage);
$anzahl_vermietete = count($vermietete_arr);
$mv = new mietvertrag;
$m = new mietkonto;

	$gsollmiete_vermietet = 0;
	for($a=0;$a<$anzahl_vermietete;$a++){
	$einheit_id = $vermietete_arr[$a]['EINHEIT_ID'];
	$mv->get_mietvertrag_infos_aktuell($einheit_id);
	$summe_f_monatlich = $m->summe_forderung_monatlich($mv->mietvertrag_id, $monat, $jahr);	
	$gsollmiete_vermietet = $gsollmiete_vermietet + $summe_f_monatlich;
	}

$leerstand_arr = $this->leerstaende_monat_jahr($datum,$objekt_id, $typ_lage);
$anzahl_leer = count($leerstand_arr);

$gsollmiete_leer = 0;

	for($b=0;$b<$anzahl_leer;$b++){
	$einheit_id = $leerstand_arr[$b]['EINHEIT_ID'];
		
	$sollmiete_leer = $this->get_sollmiete_leerstand($einheit_id);
	
	$gsollmiete_leer = $gsollmiete_leer + $sollmiete_leer;
	}

$g_summe = $gsollmiete_vermietet + $gsollmiete_leer;
$g_summe_a = nummer_punkt2komma($g_summe);
$gsollmiete_vermietet_a = nummer_punkt2komma($gsollmiete_vermietet);
$gsollmiete_leer_a = nummer_punkt2komma($gsollmiete_leer);
echo "$gsollmiete_vermietet_a €   GESAMT SOLL VERMIETET<br>";
echo "$gsollmiete_leer_a €   GESAMT SOLL LEER<br>";
$v_geb = ($g_summe/100)*5;
$brutto_vgeb = $v_geb*1.19;
$brutto_vgeb_a = nummer_punkt2komma($brutto_vgeb);
$v_geb_a = nummer_punkt2komma($v_geb);
echo " $g_summe_a €   GESAMT SOLL<br>";
echo " $v_geb_a €   NETTO VERWALTERGEBÜHR 5%<br>";
echo " <b>$brutto_vgeb_a €   INKL. 19% MWST VERWALTERGEBÜHR 5%</b><hr>";

}

function summe_sollmieten_pdf($objekt_id){
$o1=new objekt;
$objekt_name = $o1->get_objekt_name($objekt_id);

$datum = date("Y-m");
$monat = date("m");
$jahr = date("Y");

/*echo "<a href=\"?daten=statistik&option=verwaltergebuehr_objekt_pdf&objekt_id=$objekt_id\">Berechnung für $objekt_name als PDF</a><hr>";

echo "<b>OBJEKT $objekt_name $monat/$jahr</b><br>";
*/
$typ_lage ='';
$vermietete_arr = $this->vermietete_monat_jahr($datum,$objekt_id, $typ_lage);
$anzahl_vermietete = count($vermietete_arr);
$mv = new mietvertrag;
$m = new mietkonto;

	$gsollmiete_vermietet = 0;
	for($a=0;$a<$anzahl_vermietete;$a++){
	$einheit_id = $vermietete_arr[$a]['EINHEIT_ID'];
	$mv->get_mietvertrag_infos_aktuell($einheit_id);
	$summe_f_monatlich = $m->summe_forderung_monatlich($mv->mietvertrag_id, $monat, $jahr);	
	$gsollmiete_vermietet = $gsollmiete_vermietet + $summe_f_monatlich;
	}

$leerstand_arr = $this->leerstaende_monat_jahr($datum,$objekt_id, $typ_lage);
$anzahl_leer = count($leerstand_arr);

$gsollmiete_leer = 0;

	for($b=0;$b<$anzahl_leer;$b++){
	$einheit_id = $leerstand_arr[$b]['EINHEIT_ID'];
		
	$sollmiete_leer = $this->get_sollmiete_leerstand($einheit_id);
	
	$gsollmiete_leer = $gsollmiete_leer + $sollmiete_leer;
	}

$g_summe = $gsollmiete_vermietet + $gsollmiete_leer;
$g_summe_a = nummer_punkt2komma($g_summe);
$gsollmiete_vermietet_a = nummer_punkt2komma($gsollmiete_vermietet);
$gsollmiete_leer_a = nummer_punkt2komma($gsollmiete_leer);
/*echo "$gsollmiete_vermietet_a €   GESAMT SOLL VERMIETET<br>";
echo "$gsollmiete_leer_a €   GESAMT SOLL LEER<br>";
*/
$v_geb = ($g_summe/100)*5;
$v_geb = sprintf("%01.2f",$v_geb);
$mwst_eur = ($v_geb/100)*19;
$mwst_eur = sprintf("%01.2f", $mwst_eur);
$brutto_vgeb = $v_geb+$mwst_eur;
$mwst_eur =  nummer_punkt2komma($mwst_eur);
$brutto_vgeb_a = nummer_punkt2komma($brutto_vgeb);
$v_geb_a = nummer_punkt2komma($v_geb);
/*echo " $g_summe_a €   GESAMT SOLL<br>";
echo " $v_geb_a €   NETTO VERWALTERGEBÜHR 5%<br>";
echo " <b>$brutto_vgeb_a €   INKL. 19% MWST VERWALTERGEBÜHR 5%</b><hr>";
*/
ob_clean(); //ausgabepuffer leeren
header("Content-type: application/pdf");  // wird von MSIE ignoriert
	include_once('pdfclass/class.ezpdf.php');
		$pdf = new Cezpdf('a4', 'portrait');
		$pdf->ezSetCmMargins(4.5,1,1,1);
		
		
		#include_once('pdfclass/class.ezpdf.php');
		include_once('classes/class_bpdf.php');
		$pdf = new Cezpdf('a4', 'portrait');
		$bpdf = new b_pdf;
		$bpdf->b_header($pdf, 'Partner', $_SESSION[partner_id], 'portrait', 'pdfclass/fonts/Helvetica.afm', 6);
		$this->footer_zahlungshinweis = $bpdf->zahlungshinweis;
		$pdf->ezStopPageNumbers(); //seitennummerirung beenden
		
		
		
		
		
		$berlus_schrift = 'pdfclass/fonts/Times-Roman.afm';
		$text_schrift = 'pdfclass/fonts/Arial.afm';
		#$pdf->addJpegFromFile('pdfclass/logo_hv_sw.jpg', 220, 750, 175, 100);
		#$pdf->addJpgFromFile('pdfclass/logo_262_150_sw1.jpg', 300, 500, 250, 150);
		#$pdf->setLineStyle(0.5);
		$pdf->selectFont($berlus_schrift);
		#$pdf->addText(42,743,6,"BERLUS HAUSVERWALTUNG - Fontanestr. 1 - 14193 Berlin");
		#$pdf->line(42,750,550,750);
		$monatsname = monat2name($monat);
		$pdf->addText(42, 700,12,"Berechnungsbogen für die Verwaltergebühr <b>$objekt_name $monatsname $jahr</b>");
		
		$pdf->addText(42, 650,10,"Gesamtsoll aus vermieteten Einheiten");
		$pdf->addText(300, 650,10,"$gsollmiete_vermietet_a €");
		$pdf->addText(42, 635,10,"Gesamtsoll aus leerstehenden Einheiten");
		$pdf->addText(300, 635,10,"$gsollmiete_leer_a €");
		$pdf->setLineStyle(0.5);
		$pdf->line(42,630,350,630);
		$pdf->addText(42, 620,10,"<b>Gesamtsoll");
		$pdf->addText(300, 620,10,"$g_summe_a €</b>");
		$pdf->addText(42, 595,10,"5% Verwaltergebühr");
		$pdf->addText(300, 595,10,"$v_geb_a €");
		$pdf->addText(42, 585,10,"+ 19% MWSt");
		$pdf->addText(300, 585,10,"$mwst_eur €");
		$pdf->setLineStyle(0.5);
		$pdf->line(42,580,350,580);
		$pdf->addText(42, 570,10,"<b>Verwaltergebühr brutto");
		$pdf->addText(300, 570,10,"$brutto_vgeb_a €</b>");
		
		$pdf->ezStream();
}

function get_sollmiete_leerstand($einheit_id){
$result = mysql_query ("SELECT SUM( DETAIL_INHALT ) AS SUMME FROM DETAIL WHERE DETAIL_AKTUELL = '1' && DETAIL_ZUORDNUNG_TABELLE = 'EINHEIT' && DETAIL_ZUORDNUNG_ID = '$einheit_id' && (DETAIL_NAME = 'Miete kalt' OR DETAIL_NAME = 'Nebenkosten Vorauszahlung' OR DETAIL_NAME = 'Heizkosten Vorauszahlung')  ");
		
		$row = mysql_fetch_assoc($result);
		return $row['SUMME'];				
}

function form_haeuser_auswahl(){
$f = new formular;
$f->fieldset("Verwaltergebühr für Häusergruppen", 'v_geb_haeuser');
if(!empty($_POST)){
	#echo '<pre>';
	#print_r($_POST);
	$anzahl_h = count($_POST[haus]);
		if(!isset($_REQUEST[monat]) && !isset($_REQUEST[jahr])){
		$jahr_monat = date("Y-m");
		$jahr = date("Y");
		$monat = date("m");
		}else{
		$monat =$_REQUEST[monat];
		$jahr = $_REQUEST[jahr];
		$jahr_monat = $jahr.'-'.$monat;
		}
	$vermietete_arr = array();
	$leerstand_arr = array();
	for($a=0;$a<$anzahl_h;$a++){
	$haus_id = $_POST[haus][$a];
	$vermietete = $this->vermietete_monat_jahr_haus($jahr_monat,$haus_id, '');
	$leerstand = $this->leerstand_monat_jahr_haus($jahr_monat,$haus_id, '');
		if(is_array($vermietete)){
		$vermietete_arr = array_merge($vermietete_arr, $vermietete);
		}
		if(is_array($leerstand)){
		$leerstand_arr = array_merge($leerstand_arr, $leerstand);
		}
	
	unset($leerstand);
	unset($vermietete);
	}
	
	#print_r($vermietete_arr);
	#print_r($leerstand_arr);
$this-> berechnung_anzeigen($leerstand_arr, $vermietete_arr, $monat, $jahr);

}else{
$h = new haus;
$haus_arr = $h->liste_aller_haeuser();
$anzahl_haeuser = count($haus_arr);
if(is_array($haus_arr)){
$f->erstelle_formular("Häuser auswählen", NULL);
	for($a=0;$a<$anzahl_haeuser;$a++){
	$objekt_id = $haus_arr[$a]['OBJEKT_ID'];
	$haus_id = $haus_arr[$a]['HAUS_ID'];
	$haus_id = $haus_arr[$a]['HAUS_ID'];
	$haus_str = $haus_arr[$a]['HAUS_STRASSE'];
	$haus_nr = $haus_arr[$a]['HAUS_NUMMER'];
	if(isset($_SESSION['objekt_id']) && $objekt_id==$_SESSION['objekt_id']){
	$f->check_box_js('haus[]', $haus_id, $haus_str.' '.$haus_nr, '', 'checked');
	}
	#echo "$haus_id $haus_str $haus_nr<br>";
	
	}
	$f->send_button('btn_send', 'Berechnen');
$f->ende_formular();	
}else{
	echo "Keine Häuser";
}
}


$f->fieldset_ende();	
}

function berechnung_anzeigen($leerstand_arr, $vermietete_arr, $monat, $jahr){
echo '<pre>';
#print_r($vermietete_arr);
$anzahl_vermietete = count($vermietete_arr);
$mv = new mietvertrag;
$m = new mietkonto;

$haeuser = array();
	$gsollmiete_vermietet = 0;
	for($a=0;$a<$anzahl_vermietete;$a++){
	$einheit_id = $vermietete_arr[$a]['EINHEIT_ID'];
	
	$haus_str = $vermietete_arr[$a]['HAUS_STRASSE'];
	$haus_nr = $vermietete_arr[$a]['HAUS_NUMMER'];
	$haus_str_nr = $haus_str.' '.$haus_nr;
	if(!in_array($haus_str_nr, $haeuser)){
	$haeuser[] = $haus_str_nr;
	}
	
	$mv->get_mietvertrag_infos_aktuell($einheit_id);
	$summe_f_monatlich = $m->summe_forderung_monatlich($mv->mietvertrag_id, $monat, $jahr);	
	
	$gsollmiete_vermietet = $gsollmiete_vermietet + $summe_f_monatlich;
	
	}

$anzahl_leer = count($leerstand_arr);

$gsollmiete_leer = 0;

	for($b=0;$b<$anzahl_leer;$b++){
	$einheit_id = $leerstand_arr[$b]['EINHEIT_ID'];
	
	$haus_str = $leerstand_arr[$b]['HAUS_STRASSE'];
	$haus_nr = $leerstand_arr[$b]['HAUS_NUMMER'];
	$haus_str_nr = $haus_str.' '.$haus_nr;
	if(!in_array($haus_str_nr, $haeuser)){
	$haeuser[] = $haus_str_nr;
	}	
	
	$sollmiete_leer = $this->get_sollmiete_leerstand($einheit_id);
	$gsollmiete_leer = $gsollmiete_leer + $sollmiete_leer;
	
	}
	

#print_r($haeuser);

$g_summe = $gsollmiete_vermietet + $gsollmiete_leer;
$g_summe_a = nummer_punkt2komma($g_summe);
$gsollmiete_vermietet_a = nummer_punkt2komma($gsollmiete_vermietet);
$gsollmiete_leer_a = nummer_punkt2komma($gsollmiete_leer);
$v_geb = ($g_summe/100)*5;
$brutto_vgeb = $v_geb*1.19;
$mwst_eur = $v_geb/100*19;
$mwst_eur =  nummer_punkt2komma($mwst_eur);
$brutto_vgeb_a = nummer_punkt2komma($brutto_vgeb);
$v_geb_a = nummer_punkt2komma($v_geb);
if(!isset($_REQUEST[pdf])){
echo "$gsollmiete_vermietet_a €   GESAMT SOLL VERMIETET<br>";
echo "$gsollmiete_leer_a €   GESAMT SOLL LEER<br>";
echo " $g_summe_a €   GESAMT SOLL<br>";
echo " $v_geb_a €   NETTO VERWALTERGEBÜHR 5%<br>";
echo " <b>$brutto_vgeb_a €   INKL. 19% MWST VERWALTERGEBÜHR 5%</b><hr>";
}else{
/*PDF AUSGABE*/	
ob_clean(); //ausgabepuffer leeren
header("Content-type: application/pdf");  // wird von MSIE ignoriert
	include_once('pdfclass/class.ezpdf.php');
		$pdf = new Cezpdf('a4', 'portrait');
		$pdf->ezSetCmMargins(4.5,1,1,1);
		$berlus_schrift = 'pdfclass/fonts/Times-Roman.afm';
		$text_schrift = 'pdfclass/fonts/Arial.afm';
		$pdf->addJpegFromFile('pdfclass/logo_hv_sw.jpg', 220, 750, 175, 100);
		#$pdf->addJpgFromFile('pdfclass/logo_262_150_sw1.jpg', 300, 500, 250, 150);
		$pdf->setLineStyle(0.5);
		$pdf->selectFont($berlus_schrift);
		$pdf->addText(42,743,6,"BERLUS HAUSVERWALTUNG - Fontanestr. 1 - 14193 Berlin");
		$pdf->line(42,750,550,750);
		$monatsname = monat2name($monat);
		$pdf->addText(42, 720,12,"Berechnungsbogen für die Verwaltergebühr $monatsname $jahr");
		
			
		
		
		$pdf->addText(42, 650,10,"Gesamtsoll aus vermieteten Einheiten");
		$pdf->addText(300, 650,10,"$gsollmiete_vermietet_a €");
		$pdf->addText(42, 635,10,"Gesamtsoll aus leerstehenden Einheiten");
		$pdf->addText(300, 635,10,"$gsollmiete_leer_a €");
		$pdf->setLineStyle(0.5);
		$pdf->line(42,630,350,630);
		$pdf->addText(42, 620,10,"<b>Gesamtsoll");
		$pdf->addText(300, 620,10,"$g_summe_a €</b>");
		$pdf->addText(42, 595,10,"5% Verwaltergebühr");
		$pdf->addText(300, 595,10,"$v_geb_a €");
		$pdf->addText(42, 585,10,"+ 19% MWSt");
		$pdf->addText(300, 585,10,"$mwst_eur €");
		$pdf->setLineStyle(0.5);
		$pdf->line(42,580,350,580);
		$pdf->addText(42, 570,10,"<b>Verwaltergebühr brutto");
		$pdf->addText(300, 570,10,"$brutto_vgeb_a €</b>");
		
		/*Häuser*/
		$pdf->addText(42, 480,10,"In diese Berechnung wurden folgende Häuser einbezogen:");
		$text_xpos = 460;
		for($c=0;$c<count($haeuser);$c++){
		$haus = $haeuser[$c];
		$pdf->addText(42, $text_xpos,10,"<b>$haus</b>");
		$text_xpos = $text_xpos -10;
		if($text_xpos==100){
		$pdf->ezNewPage();	
		$text_xpos = 650;
		$pdf->ezSetCmMargins(4.5,1,1,1);
		$berlus_schrift = 'pdfclass/fonts/Times-Roman.afm';
		$text_schrift = 'pdfclass/fonts/Arial.afm';
		$pdf->addJpegFromFile('pdfclass/logo_hv_sw.jpg', 220, 750, 175, 100);
		#$pdf->addJpgFromFile('pdfclass/logo_262_150_sw.jpg', 450, 780, 100, 42);
		$pdf->setLineStyle(0.5);
		$pdf->selectFont($berlus_schrift);
		$pdf->addText(42,743,6,"BERLUS HAUSVERWALTUNG - Fontanestr. 1 - 14193 Berlin");
		$pdf->line(42,750,550,750);
		$pdf->addText(42, 720,12,"Berechnungsbogen für die Verwaltergebühr $monat/$jahr");
		}
		}	
		
		$pdf->ezStream();
}
}
	
/*Vermietete und Leerstand ab 1.6.09 bezogen auf alle Einheiten in allen Objekten*/


function leerstand_alle($jahr_monat, $typ_lage){
if($typ_lage == ''){
	$m_lage ='';
}else{
$m_lage = "EINHEIT_LAGE LIKE '".$typ_lage."%' &&";
}
$result = mysql_query ("SELECT EINHEIT_ID FROM `EINHEIT` WHERE $m_lage  EINHEIT_AKTUELL='1' && EINHEIT_ID NOT
IN (
SELECT EINHEIT_ID
FROM MIETVERTRAG
WHERE MIETVERTRAG_AKTUELL = '1' && DATE_FORMAT( MIETVERTRAG_VON, '%Y-%m' ) <= '$jahr_monat' && ( DATE_FORMAT( MIETVERTRAG_BIS, '%Y-%m' ) >= '$jahr_monat'
OR MIETVERTRAG_BIS = '0000-00-00' )
)
ORDER BY EINHEIT_KURZNAME ASC");

while ($row = mysql_fetch_assoc($result)) $my_arr[] = $row;

return count($my_arr); //liefert anzahl leerstände
}


function stat_kosten_me_jahr($geldkonto_id, $jahr){
$b = new buchen;


$datum_jahresanfang = "01.01.$jahr";
$b->kontostand_tagesgenau_bis($geldkonto_id, $datum_jahresanfang);
$kontostand_jahresanfang = $this->summe_konto_buchungen;


/*Alle Monate durchlaufen*/
#$daten_arr = array();

for($a=1;$a<=12;$a++){
$monat = $a;	
if(strlen($monat)==1){
	$monat = '0'.$monat;
}

$b->summe_kontobuchungen_jahr_monat($geldkonto_id, '80001', $jahr, $monat);
$summe_mieteinnahmen_monat = $b->summe_konto_buchungen;
$daten_arr[me_monat][] = $b->summe_konto_buchungen;

$b->summe_miete_jahr($geldkonto_id, '80001', $jahr, $monat);
$summe_mieteinnahmen_jahr = $b->summe_konto_buchungen;
$daten_arr[me_jahr][] = $summe_mieteinnahmen_jahr;


$b->summe_kosten_jahr_monat($geldkonto_id, '80001', $jahr, $monat);
$summe_kosten_monat = abs($b->summe_konto_buchungen);
$daten_arr[kosten_monat][] = $summe_kosten_monat;


$b->summe_kosten_jahr($geldkonto_id, '80001', $jahr, $monat);
$summe_kosten_jahr = abs($b->summe_konto_buchungen);
$daten_arr[kosten_jahr][]= $summe_kosten_jahr;


$monatname = monat2name($monat);
$daten_arr[monate][] = $monatname;
	
}//end for
#echo '<pre>';
#print_r($daten_arr);
return $daten_arr;
}

function kosten_einnahmen_k($geldkonto_id, $jahr, $desc, $y_label){
$daten_arr = $this->stat_kosten_me_jahr($geldkonto_id, $jahr);
$_SESSION['daten_arr'][$geldkonto_id] = $daten_arr;
echo "<p><iframe src=\"graph/examples/myLineGraph.php?desc=$desc&y_label=$x_label&x_label=$jahr&geldkonto_id=$geldkonto_id\" width=\"85%\" height=\"550\" ></iframe></p>";
#unset($daten_arr);
#unset($_SESSION[daten_arr]);
}










function vermietete_monat_jahr_neu($jahr, $monat){
$monatname = monat2name($monat);
echo "<h1>$monatname $jahr</h1>";
$e = new einheit;
$anzahl_alle_einheiten = count($e->liste_aller_einheiten());
echo "Gesamt Einheiten: $anzahl_alle_einheiten<br>";


$result = mysql_query ("SELECT EINHEIT_ID, MIETVERTRAG_ID FROM MIETVERTRAG WHERE MIETVERTRAG_AKTUELL = '1' && DATE_FORMAT( MIETVERTRAG_VON, '%Y-%m' ) <= '$jahr-$monat' && ( DATE_FORMAT( MIETVERTRAG_BIS, '%Y-%m' ) >= '$jahr-$monat' OR MIETVERTRAG_BIS = '0000-00-00' )");

while ($row = mysql_fetch_assoc($result)) $my_arr[] = $row;

$anzahl_gesamt_mvs = count($my_arr);

$prozent_vermietet = nummer_punkt2komma($anzahl_gesamt_mvs/($anzahl_alle_einheiten/100));
echo "Gesamt vermietet: $anzahl_gesamt_mvs --- Vermietet:$prozent_vermietet %<br>";

$nicht_vermietet = $anzahl_alle_einheiten-$anzahl_gesamt_mvs;
$prozent_n_vermietet = nummer_punkt2komma($nicht_vermietet/($anzahl_alle_einheiten/100));
echo "Gesamt leer: $nicht_vermietet --- Leer:$prozent_n_vermietet %<br>";




for($a=0;$a<$anzahl_gesamt_mvs;$a++){
	$d = new detail;
	$id = $my_arr[$a][MIETVERTRAG_ID];
	$nutzungsart = $d->finde_detail_inhalt('MIETVERTRAG', $id, 'Nutzungsart');
	$nutzungs_stat[] = $nutzungsart;
	
}
#echo "<pre>";
$nutzungs_arr = array_count_values($nutzungs_stat);
$anzahl_zeilen = count($nutzungs_arr);

$values = array_values($nutzungs_arr);
$keys = array_keys($nutzungs_arr);

for($a=0;$a<$anzahl_zeilen;$a++){

$prozent =	$values[$a]/($anzahl_gesamt_mvs/100);
$stat_arr[$a][NUTZUNGSART] = $keys[$a];
$stat_arr[$a][ANZAHL] = $values[$a];
$stat_arr[$a][PROZENT] = nummer_punkt2komma($prozent);		
}

#print_r($stat_arr);


echo "<table>";
echo "<tr class=\"feldernamen\"><td colspan=\"3\">Vermietete</td></tr>";
echo "<tr class=\"feldernamen\"><td>Nutzungsart</td><td>Anzahl</td><td>Prozent</td></tr>";
for($a=0;$a<$anzahl_zeilen;$a++){
	$nutzungsart = $stat_arr[$a][NUTZUNGSART];
	$anzahl= $stat_arr[$a][ANZAHL];
	$prozent = $stat_arr[$a][PROZENT];
	echo "<tr><td>$nutzungsart</td><td>$anzahl</td><td>$prozent %</td></tr>";
}
echo "</table>";

}


function stunden_gesamt_mitarbeiter($mitarbeiter_id, $typ,$id){
/*stundenzettel*/
$db_abfrage = "SELECT SUM(DAUER_MIN)/60 AS STUNDEN  FROM `STUNDENZETTEL_POS` JOIN STUNDENZETTEL ON (STUNDENZETTEL.ZETTEL_ID=STUNDENZETTEL_POS.ZETTEL_ID) WHERE `BENUTZER_ID` = '$mitarbeiter_id' AND STUNDENZETTEL.AKTUELL = '1' && STUNDENZETTEL_POS.AKTUELL = '1' && KOSTENTRAEGER_TYP='$typ' && KOSTENTRAEGER_ID='$id'";
	$resultat = mysql_query($db_abfrage) or   die(mysql_error());
	$numrows = mysql_numrows($resultat);
	
	if($numrows){
	$row = mysql_fetch_assoc($resultat);
	return $row['STUNDEN'];
	}else{
	return 0;
	}
}


function stunden_gesamt_kostentraeger($typ,$id, $datum_a, $datum_e){
/*stundenzettel*/
$db_abfrage = "SELECT SUM(DAUER_MIN)/60 AS STUNDEN  FROM `STUNDENZETTEL_POS`  WHERE STUNDENZETTEL_POS.DATUM BETWEEN '$datum_a' AND '$datum_e' && STUNDENZETTEL_POS.AKTUELL = '1' && KOSTENTRAEGER_TYP='$typ' && KOSTENTRAEGER_ID='$id'";
	$resultat = mysql_query($db_abfrage) or   die(mysql_error());
	$numrows = mysql_numrows($resultat);
	
	if($numrows){
	$row = mysql_fetch_assoc($resultat);
	return $row['STUNDEN'];
	}else{
	return 0;
	}
}

function gesamt_stunden($kos_typ, $kos_id, $datum_a, $datum_e){
$db_abfrage = "SELECT SUM(DAUER_MIN)/60 AS G_STD FROM `STUNDENZETTEL_POS` JOIN STUNDENZETTEL ON (STUNDENZETTEL.ZETTEL_ID=STUNDENZETTEL_POS.ZETTEL_ID) JOIN BENUTZER ON(STUNDENZETTEL.BENUTZER_ID=BENUTZER.benutzer_id) WHERE  STUNDENZETTEL_POS.DATUM BETWEEN '$datum_a' AND '$datum_e' && STUNDENZETTEL.AKTUELL = '1' && STUNDENZETTEL_POS.AKTUELL = '1' && KOSTENTRAEGER_TYP='$kos_typ' && KOSTENTRAEGER_ID='$kos_id'";	
#die();
$resultat = mysql_query($db_abfrage) or   die(mysql_error());
	$numrows = mysql_numrows($resultat);
	if($numrows){
		$row = mysql_fetch_assoc($resultat);
		return $row[G_STD];
	}
}




function baustellen_leistung($kos_typ, $kos_id, $preis, $datum_a, $datum_e, $beschreibung=''){
$db_abfrage = "SELECT STUNDENZETTEL.BENUTZER_ID, benutzername, STUNDENSATZ, SUM(DAUER_MIN)/60 AS STD, $preis*(SUM(DAUER_MIN)/60) AS LEISTUNG_EUR FROM `STUNDENZETTEL_POS` JOIN STUNDENZETTEL ON (STUNDENZETTEL.ZETTEL_ID=STUNDENZETTEL_POS.ZETTEL_ID) JOIN BENUTZER ON(STUNDENZETTEL.BENUTZER_ID=BENUTZER.benutzer_id) WHERE  STUNDENZETTEL_POS.DATUM BETWEEN '$datum_a' AND '$datum_e' && STUNDENZETTEL.AKTUELL = '1' && STUNDENZETTEL_POS.AKTUELL = '1' && KOSTENTRAEGER_TYP='$kos_typ' && KOSTENTRAEGER_ID='$kos_id' GROUP BY STUNDENZETTEL.BENUTZER_ID ORDER BY STD DESC";
#echo $db_abfrage.'<br>';
$resultat = mysql_query($db_abfrage) or   die(mysql_error());
	$numrows = mysql_numrows($resultat);
	
	$r = new rechnung;
	$kos_bez = $r->kostentraeger_ermitteln($kos_typ,$kos_id);
	$datum1 = date_mysql2german($datum_a);
	$datum2 = date_mysql2german($datum_e);
	
	
	if($numrows){
	echo "<table>";
	echo "<tr><th colspan=\"3\">Baustelle $kos_bez $beschreibung Vom:$datum1 Bis: $datum2</th></tr>";
	echo "<tr><th>Mitarbeiter</th><th>Stunden</th><th>Gesamtkosten</th></tr>";
	$gesamt_std = $this-> stunden_gesamt_kostentraeger($kos_typ,$kos_id,$datum_a,$datum_e);
	$gesamt_eur = nummer_punkt2komma($gesamt_std * $preis);
	
	while($row = mysql_fetch_assoc($resultat)){
	$benutzname=$row[benutzername];
	$std=nummer_punkt2komma($row[STD]);
	$leistung_eur=nummer_punkt2komma($row[LEISTUNG_EUR]);
	#echo "$benutzname $std Stunden $leistung_eur €<br>";
	echo "<tr><td>$benutzname</td><td>$std</td><td>$leistung_eur</td></tr>";
	}
	#echo "<hr><b>Stunden gesamt: $gesamt_std | Preis Leistung: $gesamt_eur €</b><hr><br>";
	$gesamt_std_a = nummer_punkt2komma($gesamt_std);
	$gesamt_eur_a = nummer_punkt2komma($gesamt_eur);
	echo "<tr><th>Gesamt</th><th>$gesamt_std_a Std.</th><th>$gesamt_eur_a €</th></tr>";
	
	}else{
	echo "Keine Stunden eingetragen<hr><br>";
	}	
}


function baustellen_uebersicht(){
$db_abfrage = "SELECT * FROM BAUSTELLEN ORDER BY DAT DESC";
	$resultat = mysql_query($db_abfrage) or   die(mysql_error());
	$numrows = mysql_numrows($resultat);	
		if($numrows){
		while($row = mysql_fetch_assoc($resultat)){
		$kos_typ = $row[KOSTENTRAEGER_TYP];
		$kos_id = $row[KOSTENTRAEGER_ID];
		$datum_a = $row[A_DATUM];
		$datum_e = $row[E_DATUM];
		$beschreibung = 	$row[BESCHREIBUNG];
		$this->baustellen_leistung($kos_typ, $kos_id, 25, $datum_a, $datum_e,$beschreibung);
		}	
		}	
}

function baustellen_uebersicht2($soll_std){
$db_abfrage = "SELECT * FROM BAUSTELLEN ORDER BY DAT DESC";
	$resultat = mysql_query($db_abfrage) or   die(mysql_error());
	$numrows = mysql_numrows($resultat);	
		if($numrows){
		echo "<table class=\"sortable\">";
		echo "<tr><th>Baustelle</th><th>SOLL</th><th>GESAMT</th><th>DIFF</th><th>STATUS</th></tr>";
		while($row = mysql_fetch_assoc($resultat)){
		$kos_typ = $row[KOSTENTRAEGER_TYP];
		$kos_id = $row[KOSTENTRAEGER_ID];
		$datum_a = $row[A_DATUM];
		$datum_e = $row[E_DATUM];
		$beschreibung = 	$row[BESCHREIBUNG];
		$g_stunden = 0;
		$g_stunden = $this->gesamt_stunden($kos_typ, $kos_id, $datum_a, $datum_e);
		#echo "Einheit $kos_id  $g_stunden".'<br>';
		$rest_std = $soll_std - $g_stunden;
		
				
		if($soll_std>$g_stunden){
			$status = "erfolgreich";
		}else{
			$status = "nicht erfolgreich";
		}
		
		$r = new rechnung;
		$kos_bez = $r->kostentraeger_ermitteln($kos_typ,$kos_id);
		if($kos_typ == 'Einheit'){
			$e = new einheit;
			$e->get_einheit_info($kos_id);
			$kos_bez = "$e->einheit_kurzname $e->haus_strasse $e->haus_nummer $e->einheit_lage $e->einheit_qm m²";
		}
		$soll_std_a = nummer_punkt2komma($soll_std);
		$g_stunden_a = nummer_punkt2komma($g_stunden);
		$rest_std_a = nummer_punkt2komma($rest_std);
		
		echo "<tr><td>$kos_bez</td><td>$soll_std_a STD</td><td>$g_stunden_a STD</td><td>$rest_std_a STD</td><td>$status</td></tr>";
		}
		echo "</table>";
		
		}	
}

function get_baustelle_ext_id($bau_bez){
$result = mysql_query ("SELECT ID FROM BAUSTELLEN_EXT WHERE BEZ='$bau_bez' && AKTUELL = '1' ORDER BY ID DESC LIMIT 0,1");
	$row = mysql_fetch_assoc($result);
 	return $row['ID'];		
}

function get_baustelle_ext_infos($bau_id){
$result = mysql_query ("SELECT * FROM BAUSTELLEN_EXT WHERE ID='$bau_id' && AKTUELL = '1' ORDER BY ID DESC LIMIT 0,1");
	$row = mysql_fetch_assoc($result);
 	$this->bez = $row['BEZ'];		
}

function fenster_uebersicht($lieferant_id=null, $objekt_id=null){
	
	$this->lieferungen_anzeigen();
	
}

function form_lieferung_eingeben(){
	$f = new formular();
	$f->erstelle_formular('Lieferung eingeben', null);
	$f->text_feld('BelegID', 'beleg_id_l', '', 10, 'beleg_id_l', '');
	$f->text_feld('Position', 'pos_l', '', 5, 'pos_l', '');
	$f->hidden_feld('option', 'lieferung_eingeben');
	$f->send_button('lsndBtn', 'Eingeben');
	$f->ende_formular();
}


function form_einheit_suche(){
	$f = new formular();
	$f->erstelle_formular('Einheit', null);
	$f->text_feld('Einheit - Bezeichnung', 'einheit_bez', '', 10, 'einheit_bez', '');
	$f->hidden_feld('option', 'einheit_suche_bau');
	$f->send_button('lsndBtn', 'Suche');
	$f->ende_formular();
}


function kontrolle_bau_tab($kos_typ, $kos_id){
	$f = new formular();
	$f->fieldset('BAU', 'bauid');
	$r = new rechnung();
	$kos_bez = $r->kostentraeger_ermitteln($kos_typ, $kos_id);
	echo "<h1>$kos_bez</h1>";
	#echo "EDIS";
	$b_arr = $this->get_bau_beleg_arr();
	if(!is_array($b_arr)){
		fehlermeldung_ausgeben("Keine Belege in BAU_BELEG DB hinterlegt");
	}else{
		$anz = count($b_arr);
		for($a=0;$a<$anz;$a++){
			$beleg_nr = $b_arr[$a]['BELEG_NR'];
			#echo "$beleg_nr<br>";
			$r->rechnung_grunddaten_holen($beleg_nr);
			echo "<hr><b>$r->kurzbeschreibung</b><hr>";
			$pos_arr = $r->rechnungs_positionen_arr($beleg_nr);
			#echo '<pre>';	
			#print_r($pos_arr);
				if(is_array($pos_arr)){
				$anz_p = count($pos_arr);
					for($p=0;$p<$anz_p;$p++){
					$art_nr = $pos_arr[$p]['ARTIKEL_NR'];	
					$menge = $pos_arr[$p]['MENGE'];
					$this->get_kontierung($art_nr, $menge, $kos_typ, $kos_id);
					}
				
				}
		}
	
	}
	$f->fieldset_ende();
}


function get_kontierung($art_nr, $menge, $kos_typ, $kos_id){
	echo "<table class=\"sortable\">";
	#echo "<tr><th>BELEG</th><th>ARTIKEL</th><th>AUSSTELLER</th><th>EMPFÄNGER</th><th>MENGE SOLL</th><th>MENGE IST</th><th>BEZ</th><th>RG KURZBESCHRIBUNG</th></tr>";
	echo "<tr><th>BELEG</th><th>ARTIKEL</th><th>BEZ</th><th>AUSSTELLER</th><th>EMPFÄNGER</th><th>MENGE SOLL</th><th>MENGE IST</th></tr>";
		
	
	$result = mysql_query ("SELECT BELEG_NR, POSITION, MENGE FROM  `KONTIERUNG_POSITIONEN`	WHERE  `KOSTENTRAEGER_TYP` LIKE  '$kos_typ'	AND  `KOSTENTRAEGER_ID` ='$kos_id' AND  `AKTUELL` =  '1'");
	while ($row = mysql_fetch_assoc($result)){
	$beleg_nr = $row['BELEG_NR'];
	$position = $row['POSITION'];
	$menge_kont = $row['MENGE'];
	
	$r = new rechnung;
	$art_nr_kont = $r->art_nr_from_beleg($beleg_nr, $position);
		if($art_nr_kont==$art_nr){
		
			
			
		$lieferant_id = $r->art_lieferant_from_beleg($beleg_nr, $position);
		$a_arr = $r->artikel_info($lieferant_id, $art_nr);
		$bez = $a_arr[0]['BEZEICHNUNG'];
		$r->rechnung_grunddaten_holen($beleg_nr);
		$link_beleg = "<a href=\"?daten=rechnungen&option=rechnung_kontieren&belegnr=$beleg_nr\">$r->rechnungsnummer</a>";
		$link_katalog = "<a href=\"?daten=katalog&option=artikel_suche&artikel_nr=$art_nr\">$art_nr</a>";
		#$link_katalog1 = "<a href=\"?daten=katalog&option=artikel_suche_freitext&artikel_nr=$art_nr\">FREITEXT</a>";
		
		#echo "<tr><td>$link_beleg</td><td>$link_katalog</td><td>$r->rechnungs_aussteller_name</td><td>$r->rechnungs_empfaenger_name</td><td>$menge</td><td>$menge_kont</td><td>$bez</td><td>$r->kurzbeschreibung</td></tr>";
		echo "<tr><td>$link_beleg</td><td>$link_katalog</td><td>$bez</td><td>$r->rechnungs_aussteller_name</td><td>$r->rechnungs_empfaenger_name</td><td>$menge</td><td>$menge_kont</td></tr>";
		}	
		
	}
	echo "</table>";
	
	#echo "</table>";
		
			
			
}


function get_bau_beleg_arr(){
	$result = mysql_query ("SELECT * FROM  `BAU_BELEG`");
	
	while ($row = mysql_fetch_assoc($result)) $my_arr[] = $row;
	if(isset($my_arr)){
		return $my_arr;
	}
}

function check_lieferung($beleg_id, $pos){
$result = mysql_query ("SELECT * FROM `FENSTER_LIEFERUNG` WHERE `R_BELEG_ID` ='$beleg_id' AND `POS` ='$pos' LIMIT 0 , 1");
	$numrows = mysql_numrows($result);
	if($numrows){
	return true;
	}else{
	return false;
	}
}


function lieferung_speichern($beleg_id, $pos){
	if(!$this->check_lieferung($beleg_id, $pos)){
	$db_abfrage = "INSERT INTO FENSTER_LIEFERUNG VALUES (NULL, '$beleg_id', '$pos')";
	$resultat = mysql_query($db_abfrage) or
           die(mysql_error());
           return true;
	}else{
		fehlermeldung_ausgeben("Lieferung exisitiert schon!");
		return false;
	}       
}

function lieferung_loeschen($beleg_id, $pos){

	if($this->check_lieferung($beleg_id, $pos)){
	$db_abfrage = "DELETE FROM FENSTER_LIEFERUNG WHERE R_BELEG_ID='$beleg_id' && POS='$pos'";
	$resultat = mysql_query($db_abfrage) or
           die(mysql_error());
           
    $db_abfrage = "DELETE FROM FENSTER_EINGEBAUT WHERE R_BELEG_ID='$beleg_id' && POS='$pos'";
	$resultat = mysql_query($db_abfrage) or
           die(mysql_error());
           
           return true;
	}else{
		fehlermeldung_ausgeben("Lieferung exisitiert nicht!");
		return false;
	}       
}


function zuweisung_loeschen($beleg_id, $pos, $einheit_id){

	if($this->check_lieferung($beleg_id, $pos)){
	           
    $db_abfrage = "DELETE FROM FENSTER_EINGEBAUT WHERE R_BELEG_ID='$beleg_id' && POS='$pos' && EINHEIT_ID='$einheit_id'";
	$resultat = mysql_query($db_abfrage) or
           die(mysql_error());
           
           return true;
	}else{
		fehlermeldung_ausgeben("Zuweisung exisitiert nicht!");
		return false;
	}       
}

function fenster_zuweisen($beleg_id, $pos, $anz_fenster=1, $einheit_id){
	for($a=0;$a<$anz_fenster;$a++){
	$db_abfrage = "INSERT INTO FENSTER_EINGEBAUT VALUES (NULL, '$beleg_id', '$pos', '$einheit_id')";
	$resultat = mysql_query($db_abfrage) or
           die(mysql_error());
	}
	
	return true;
}

function soll_fenster($einheit_id){
	return 5;
}

function ist_fenster($einheit_id){
	return  4;
}

function get_fenster_stat($einheit_id){
	return $this->ist_fenster($einheit_id) - $this->soll_fenster($einheit_id);
	
}

function get_lieferungen_arr(){
$result = mysql_query ("SELECT R_BELEG_ID, POS, ARTIKEL_NR, PREIS, MENGE, ART_LIEFERANT FROM `FENSTER_LIEFERUNG`, RECHNUNGEN_POSITIONEN
WHERE R_BELEG_ID=BELEG_NR && POS=POSITION");
		
		while ($row = mysql_fetch_assoc($result)) $my_arr[] = $row;
	if(isset($my_arr)){
		return $my_arr;
	}			
}
	
function lieferungen_anzeigen(){
	$this->form_lieferung_eingeben();
	$arr = $this->get_lieferungen_arr();
	#p($arr);
	$anz = count($arr);
	$gesamt = Array();
	echo "<table class=\"sortable\">";	
	#echo "<thead><tr><th>BELEG</th><th>POS</th><th>ART_NR</th><th>PREIS</th><th>GELIEFERT</th><th>EINGEBAUT</th><th>REST</th><th>LIEF</th></tr></thead>";
	for($a=0;$a<$anz;$a++){
			$beleg_id = $arr[$a]['R_BELEG_ID'];
			$r = new rechnungen();
			$rnr = $r->get_rechnungsnummer($beleg_id);
			$pos = $arr[$a]['POS'];
			$art_nr = $arr[$a]['ARTIKEL_NR'];
			$img = "grafiken/del.png";
			$link_rnr = "<a href=\"?daten=rechnungen&option=rechnung_kontieren&belegnr=$beleg_id\">$rnr</a>";
			$link_del = "<a href=\"?daten=statistik&option=lieferung_loeschen&beleg_id=$beleg_id&pos=$pos\"><img src=\"$img\"></a>";
			$preis = $arr[$a]['PREIS'];
			$menge = $arr[$a]['MENGE'];
			$lieferant_id = $arr[$a]['ART_LIEFERANT'];
						
			$eingebaut = $this->get_eingebaut($beleg_id, $pos);
			$rest = $menge - $eingebaut;
				ini_set('display_errors','Off'); 
				error_reporting(0);
				$gesamt[$art_nr]['GELIEFERT'] += $menge;
				$gesamt[$art_nr]['EINGEBAUT'] += $eingebaut;
				$gesamt[$art_nr]['REST'] += $rest;
				$gesamt[$art_nr]['LIEFERANT_ID'] = $lieferant_id;
				
				
				$pp = new partners();
				$pp->get_partner_info($lieferant_id);
				
			
			echo "<thead><tr><th>BELEG</th><th>POS</th><th>ART_NR</th><th>PREIS</th><th>GELIEFERT</th><th>EINGEBAUT</th><th>REST</th><th>LIEF</th></tr></thead>";
			echo "<tr><td>$link_del $link_rnr</td><td>$pos</td><td>$art_nr</td><td>$preis</td><td>$menge</td><td>$eingebaut</td><td>$rest</td><td>$pp->partner_name</td></tr>";
			echo "<tr><td colspan=\"4\">";
			if($rest>0){
			$f = new formular();
			$f->erstelle_formular("Zuweisen $a", null);
			$f->text_feld("Anzahl Fenster", "anz_fenster", 1, 10, 'anz_fenster', '');
			$e = new einheit();
			$e->dropdown_einheiten('Einheit', 'einheit');
			$f->hidden_feld('rest', "$rest");
			$f->hidden_feld('beleg_id', "$beleg_id");
			$f->hidden_feld('pos', $pos);
			$f->hidden_feld('option', 'fenster_zuweisen');
			$f->send_button('sndBtn', 'Zuweisen');
			$f->ende_formular();
			}
			echo "</td><td colspan=\"4\">";
			$this->wo_eingebaut($beleg_id, $pos);
			echo "</td></tr>";			
			#echo $einheit_kn . $this->get_fenster_stat($einheit_id) .'<br>';
			$eingebaut = 0;
			$rest = 0;
		}
	echo "</table>";
	#echo '<pre>';
	#print_r($gesamt);
	$arr_keys = array_keys($gesamt);
	#print_r($arr_keys);
	$anz = count($arr_keys);
	if(is_array($arr_keys)){
	echo "<table class=\"sortable\">";
	echo "<tr><th>ART_NR</th><th>BEZEICHNUNG</th><th>GELIEFERT</th><th>EINGEBAUT</th><th>REST</th></tr>";	
	for($a=0;$a<$anz;$a++){
		$art_nr = $arr_keys[$a];
		$lieferant_id = $gesamt[$art_nr]['LIEFERANT_ID'];
		$lieferant = $gesamt[$art_nr]['LIEFERANT'];
		$r = new rechnungen();
		$art_info = $r->artikel_info($lieferant_id, $art_nr);
		#print_r($art_info);
		#die("$art_nr $lieferant_id");
		$art_bez = $art_info[0]['BEZEICHNUNG'];
		unset($art_info);
		$geliefert = $gesamt[$art_nr]['GELIEFERT'];
		$eingebaut = $gesamt[$art_nr]['EINGEBAUT'];
		$rest = $gesamt[$art_nr]['REST'];
		echo "<tr><td>$art_nr</td><td>$art_bez</td><td>$geliefert</td><td>$eingebaut</td><td>$rest</td></tr>";
		}
	echo "</table>";
	}	
}

function get_eingebaut($beleg_nr, $pos){
	$result = mysql_query ("SELECT COUNT(*) AS EINGEBAUT  FROM `FENSTER_EINGEBAUT` WHERE `R_BELEG_ID` = '$beleg_nr' AND `POS` ='$pos' ");
	$row = mysql_fetch_assoc($result);
 	return $row['EINGEBAUT'];		
	
}

function get_wo_eingebaut_arr($beleg_nr, $pos){
	$result = mysql_query ("SELECT COUNT(EINHEIT_ID) AS ANZ, EINHEIT_ID  FROM `FENSTER_EINGEBAUT` WHERE `R_BELEG_ID` = '$beleg_nr' AND `POS` ='$pos' GROUP BY EINHEIT_ID");
		
	while ($row = mysql_fetch_assoc($result)) $my_arr[] = $row;
	if(isset($my_arr)){
	return $my_arr;
	}	
}

function wo_eingebaut($beleg_nr, $pos){
	$arr = $this->get_wo_eingebaut_arr($beleg_nr, $pos);
	if(is_array($arr)){
	$anz = count($arr);
	
	$img = "grafiken/del.png";
			

	echo "<table class=\"sortable\">";	
	echo "<thead><tr><th>EINHEIT</th><th>EINHEIT_INFO</th><th>MENGE</th></tr></thead>";
	$sum = 0;
	for($a=0;$a<$anz;$a++){
	$menge = $arr[$a]['ANZ'];
	$sum += $menge;
	$einheit_id = $arr[$a]['EINHEIT_ID'];
	$e = new einheit();
	$e->get_einheit_info($einheit_id);
	$link_del = "<a href=\"?daten=statistik&option=zuweisung_loeschen&beleg_id=$beleg_nr&pos=$pos&einheit_id=$einheit_id\"><img src=\"$img\"></a>";
	echo "<tr><td>$link_del $e->einheit_kurzname</td><td>$e->haus_strasse $e->haus_nummer <br>$e->einheit_lage</td><td>$menge</td></tr>";	
	}
	echo "<tfoot><tr><th colspan=\"2\">Summe eingebaut</th><th>$sum</th></tr></tfoot>";
	echo "</table>";
	}else{
		echo "Keine eingebaut!";
	}
}

/*Alle übersicht
SELECT STUNDENZETTEL.BENUTZER_ID, benutzername, SUM(DAUER_MIN)/60 AS STD FROM `STUNDENZETTEL_POS` JOIN STUNDENZETTEL ON (STUNDENZETTEL.ZETTEL_ID=STUNDENZETTEL_POS.ZETTEL_ID) JOIN BENUTZER ON(STUNDENZETTEL.BENUTZER_ID=BENUTZER.benutzer_id) WHERE  STUNDENZETTEL.AKTUELL = '1' && STUNDENZETTEL_POS.AKTUELL = '1' && KOSTENTRAEGER_TYP='Einheit' && KOSTENTRAEGER_ID='166' GROUP BY STUNDENZETTEL.BENUTZER_ID ORDER BY STD DESC

*
*SELECT STUNDENZETTEL.BENUTZER_ID, benutzername, STUNDENSATZ, SUM(DAUER_MIN)/60 AS STD, 25*(SUM(DAUER_MIN)/60) AS LEISTUNG_EUR FROM `STUNDENZETTEL_POS` JOIN STUNDENZETTEL ON (STUNDENZETTEL.ZETTEL_ID=STUNDENZETTEL_POS.ZETTEL_ID) JOIN BENUTZER ON(STUNDENZETTEL.BENUTZER_ID=BENUTZER.benutzer_id) WHERE  STUNDENZETTEL.AKTUELL = '1' && STUNDENZETTEL_POS.AKTUELL = '1' && KOSTENTRAEGER_TYP='Einheit' && KOSTENTRAEGER_ID='168' GROUP BY STUNDENZETTEL.BENUTZER_ID ORDER BY STD DESC
*
**/




}//end class


/*MIETVERTRÄGE OHNE MIETDEFINITION
 *  SELECT MIETVERTRAG_ID
FROM MIETVERTRAG
WHERE MIETVERTRAG_AKTUELL = '1' && MIETVERTRAG_ID NOT
IN (

SELECT KOSTENTRAEGER_ID AS MIETVERTRAG_ID
FROM MIETENTWICKLUNG
WHERE KOSTENTRAEGER_TYP = 'MIETVERTRAG'
)
LIMIT 0 , 30 
 */



#}//end classs
/*SET @num =5300;# MySQL lieferte ein leeres Resultat zurück (d.&nbsp;h. null Zeilen).
# MySQL lieferte ein leeres Resultat zurück (d.&nbsp;h. null Zeilen).
SELECT @num := @num +1 AS ZEILE, GELD_KONTO_BUCHUNGEN_DAT
FROM `GELD_KONTO_BUCHUNGEN`
WHERE `GELDKONTO_ID` =1

*
* SET @num =0;# MySQL lieferte ein leeres Resultat zurück (d.&nbsp;h. null Zeilen).
# MySQL lieferte ein leeres Resultat zurück (d.&nbsp;h. null Zeilen).
SELECT @num := @num +1 AS ZEILE, BETRAG
FROM `GELD_KONTO_BUCHUNGEN`
WHERE `GELDKONTO_ID` =1
AND `AKTUELL` = '1'
AND DATE_FORMAT( DATUM, '%Y' ) = '2009'
ORDER BY GELD_KONTO_BUCHUNGEN_DAT ASC 
*



SET @num =0;# MySQL lieferte ein leeres Resultat zurück (d.&nbsp;h. null Zeilen).
UPDATE GELD_KONTO_BUCHUNGEN SET G_BUCHUNGSNUMMER = @num:= @num +1 WHERE GELD_KONTO_BUCHUNGEN_DAT IN (
SELECT GELD_KONTO_BUCHUNGEN_DAT
FROM GELD_KONTO_BUCHUNGEN_OK37
WHERE GELDKONTO_ID =1
AND AKTUELL = '1'
AND DATE_FORMAT( DATUM, '%Y' ) = '2009'
ORDER BY GELD_KONTO_BUCHUNGEN_DAT ASC
)#  Betroffene Datensätze:  468
#  Betroffene Datensätze:  467


UPDATE KONTIERUNG_POSITIONEN AS t1
JOIN KONTIERUNG_POSITIONEN AS t2 ON t1.KONTIERUNG_ID = t2.KONTIERUNG_ID
SET t1.GESAMT_SUMME = t2.MENGE*t2.EINZEL_PREIS
*/
?>
