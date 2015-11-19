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
 * @filesource   $HeadURL: http://192.168.2.52/svn/berlussimo_1/tags/02.11.2010 - Downloadversion 0.27/classes/class_urlaub.php $
 * @version      $Revision: 15 $
 * @modifiedby   $LastChangedBy: sivac $
 * @lastmodified $Date: 2011-07-07 10:41:33 +0200 (Do, 07 Jul 2011) $
 * 
 */

/*Allgemeine Funktionsdatei laden*/
include_once("includes/allgemeine_funktionen.php");

/*Klasse "formular" für Formularerstellung laden*/
include_once("classes/class_formular.php");
include_once("classes/class_zeiterfassung.php");



class urlaub{
	

function mitarbeiter_arr($jahr){
	$datum_h = date("y-m-d");
	$result = mysql_query ("SELECT benutzer_id, benutzername, URLAUB, EINTRITT, AUSTRITT FROM BENUTZER WHERE DATE_FORMAT(EINTRITT, '%Y') <= '$jahr' &&  (DATE_FORMAT(AUSTRITT, '%Y') >= '$jahr' OR AUSTRITT='0000-00-00') && DATE_FORMAT(AUSTRITT, '%Y-%m-%d') < '$datum_h' ORDER BY benutzername ASC ");
	$numrows = mysql_numrows($result);
		if($numrows>0){
		while ($row = mysql_fetch_assoc($result)) $my_array[] = $row;	
		return $my_array;
		}else{
		return FALSE;	
		}
	 
}





function anzahl_geplanter_tage($jahr, $benutzer_id, $art='Urlaub'){
	$result = mysql_query ("SELECT benutzername, URLAUB AS ANSPRUCH, SUM( ANTEIL )  AS GEPLANT , URLAUB - SUM( ANTEIL ) AS REST
FROM `URLAUB` , BENUTZER
WHERE URLAUB.ART = '$art' && URLAUB.BENUTZER_ID = BENUTZER.benutzer_id && URLAUB.BENUTZER_ID='$benutzer_id' && DATE_FORMAT( DATUM, '%Y' ) = '$jahr' && DATUM> CURDATE() && AKTUELL='1'  GROUP BY URLAUB.`BENUTZER_ID` LIMIT 0 , 1 ");
	
	$numrows = mysql_numrows($result);
		if($numrows){
		while ($row = mysql_fetch_assoc($result)) $my_array[] = $row;	
		return $my_array;
		}

}

function anzahl_genommene_tage($jahr, $benutzer_id, $art='Urlaub'){
	$result = mysql_query ("SELECT benutzername, URLAUB AS ANSPRUCH, SUM( ANTEIL )  AS GENOMMEN , URLAUB - SUM( ANTEIL ) AS REST
FROM `URLAUB` , BENUTZER
WHERE URLAUB.ART = '$art' && URLAUB.BENUTZER_ID = BENUTZER.benutzer_id && URLAUB.BENUTZER_ID='$benutzer_id' && DATE_FORMAT( DATUM, '%Y' ) = '$jahr' && DATUM<= CURDATE() && AKTUELL='1' GROUP BY URLAUB.`BENUTZER_ID` LIMIT 0 , 1 ");
	
	$numrows = mysql_numrows($result);
		if($numrows){
		while ($row = mysql_fetch_assoc($result)) $my_array[] = $row;	
		return $my_array;
		}

}

function mitarbeiter_info($benutzer_id){
$result = mysql_query ("SELECT * FROM BENUTZER WHERE benutzer_id='$benutzer_id'  LIMIT 0,1");
	$numrows = mysql_numrows($result);
		if($numrows>0){
		while ($row = mysql_fetch_assoc($result)) $my_array[] = $row;	
		return $my_array;
		}else{
		return FALSE;	
		}	
}


function mitarbeiter_details($benutzer_id){
$result = mysql_query ("SELECT * FROM BENUTZER WHERE benutzer_id='$benutzer_id'  LIMIT 0,1");
$numrows = mysql_numrows($result);
		if($numrows){
		$row = mysql_fetch_assoc($result);
		$this->benutzername = $row['benutzername'];
		$this->benutzer_id = $benutzer_id;
		$this->gewerk_id = $row['GEWERK_ID'];
		$this->eintritt = $row['EINTRITT'];
		$this->austritt = $row['AUSTRITT'];
		$this->urlaub = $row['URLAUB'];
		$this->stunden_pw =$row['STUNDEN_PW'];
		$this->stundensatz =$row['STUNDENSATZ']; 
		}else{
			return false;
		}	
}



function rest_tage($jahr, $benutzer_id){
			$mitarbeiter_arr = $this->mitarbeiter_info($benutzer_id);
			$eintritt = $mitarbeiter_arr[0]['EINTRITT'];
			$eintritt_arr = explode("-", $eintritt);
			$eintritt_jahr = $eintritt_arr[0];
			$eintritt_monat = $eintritt_arr[1];
			
			$austritt = $mitarbeiter_arr[0]['AUSTRITT'];
			$austritt_arr = explode("-", $austritt);
			$austritt_jahr = $austritt_arr[0];
			$austritt_monat = $austritt_arr[1];
			
			
			$anspruch =  $mitarbeiter_arr[0]['URLAUB'];
			
			$anspruch_pro_m = $anspruch/12;
			$anspruch_pro_tag = $anspruch/365;
			
			
			/*Erstes Jahr in der Firma*/
			if($eintritt_jahr == $jahr){
				/*Mitarbeiter noch beschäftigt*/
				
				if($austritt_jahr == '0000'){
				$bis="$jahr-12-31";
				$tage = $this->tage_zwischen($eintritt, $bis);
				$monate = $tage/30;
				}else{
				
				
					$jahre = $austritt_jahr - $eintritt_jahr;
					/*Mitarbeiter nicht mehr beschäftigt, gleiches Jahr ausgetreten*/
					if($jahre==0){
					$tage = $this->tage_zwischen($eintritt, $austritt);
					$monate = $tage/30;
					}
					/*Mitarbeiter nicht mehr beschäftigt, Jahre danach ausgetreten*/
					if($jahre>0){
					$bis="$jahr-12-31";
					$tage = $this->tage_zwischen($eintritt, $bis);
					$monate = $tage/30;
					}
						
				}
				
			}
			
			/*Jahre danach in der Firma*/
			if($eintritt_jahr < $jahr){
				/*Mitarbeiter noch beschäftigt*/
				
				if($austritt_jahr == '0000'){
				$tage = 365;
				$monate = 12;
				}else{
				
				
					$jahre = $austritt_jahr - $eintritt_jahr;
					
					/*Mitarbeiter nicht mehr beschäftigt, 1 Jahr danach ausgetreten*/
					if($jahre==1){
					$von= "$jahr-01-01";
					$tage = $this->tage_zwischen($von, $austritt);
					$monate = $tage/30;	
					}
					/*Mitarbeiter nicht mehr beschäftigt, Jahre danach ausgetreten*/
					if($jahre>1){
						if($jahr != $austritt_jahr){
						$tage = 365;
						$monate = 12;	
						}else{
						$von= "$jahr-01-01";
						$tage = $this->tage_zwischen($von, $austritt);
						$monate = $tage/30;	
						}
					}
						
				}
				
			}
			
			
			
			
			#$monate = floor($monate);
			$anspruch = $monate*$anspruch_pro_m;
			$anspruch1 = $tage*$anspruch_pro_tag;
			/*Jahr vor Eintritt in die Firma*/
			if($eintritt_jahr > $jahr){
			$anspruch =  '0.0';
			}
			
			$genommen_arr =  $this->anzahl_genommene_tage($jahr, $benutzer_id);
			$geplant_arr = $this->anzahl_geplanter_tage($jahr, $benutzer_id);
			if(is_array($geplant_arr)){
			$geplant = $geplant_arr[0]['GEPLANT'];
			}else{
				$geplant = '0.0';
			}
			if(is_array($genommen_arr)){
			$genommen = $genommen_arr[0]['GENOMMEN'];
			}else{
				$genommen = '0.0';
			}
			$rest_aktuell = $anspruch - $genommen;
			$rest_jahr = $rest_aktuell - $geplant;
			$r1 = $anspruch - $genommen - $geplant;	
			#echo "<br>BEN$benutzer_id:$jahr $r1 = $anspruch - $genommen - $geplant<br>";
			return $r1;
}


function rest_aus_vorjahren($jahr, $benutzer_id){
	        $mitarbeiter_arr = $this->mitarbeiter_info($benutzer_id);
			$eintritt = $mitarbeiter_arr[0]['EINTRITT'];
			$eintritt_arr = explode("-", $eintritt);
			$eintritt_jahr = $eintritt_arr[0];
			$eintritt_monat = $eintritt_arr[1];
			
			$austritt = $mitarbeiter_arr[0]['AUSTRITT'];
			$austritt_arr = explode("-", $austritt);
			$austritt_jahr = $austritt_arr[0];
			$austritt_monat = $austritt_arr[1];
						
			$anspruch =  $mitarbeiter_arr[0]['URLAUB'];
												
			$vorjahr = $jahr -1;
			
			$rest_tage = 0;
			# $rest_tage = $rest_tage + $this->rest_tage($vorjahr, $benutzer_id);
			for($a=$eintritt_jahr;$a<=$vorjahr;$a++){
				#echo "$benutzer_id $a $rest_tage<br>";
				 $rest_tage = $rest_tage + $this->rest_tage($a, $benutzer_id);
				
			}
			
			return $rest_tage;
			
}


function jahresuebersicht_alle_pdf($jahr){
	$mitarbeiter_arr = $this->mitarbeiter_arr($jahr);
	if(!is_array($mitarbeiter_arr)){
		echo "Keine Mitarbeiter vorhanden, Eintrittsdaten erst nach $jahr";
	}else{
		$anzahl_daten = count($mitarbeiter_arr);
		$zaehler = 0;
		
		 ob_clean(); //ausgabepuffer leeren
		include_once('pdfclass/class.ezpdf.php');
		include_once('classes/class_bpdf.php');
		$pdf = new Cezpdf('a4', 'portrait');
		$bpdf = new b_pdf;
		$bpdf->b_header($pdf, 'Partner', $_SESSION[partner_id], 'portrait', 'pdfclass/fonts/Helvetica.afm', 6);
		$cols = array('MITARBEITER'=>"Mitarbeiter", 'MONATE'=>"Monate", 'ANSPRUCH'=>"Anspruch $jahr", 'REST_VORJAHR'=>"Rest Vorjahr",'G_ANSPRUCH'=>"Anspruch gesamt", 'GENOMMEN'=>"Genommen",  'GEPLANT'=>"Geplant",'REST_AKT'=>"Rest aktuell", 'REST_J'=>"Rest Jahr");
		
		
		for($a=0;$a<$anzahl_daten;$a++){
			$zaehler++;
			$benutzer_id = $mitarbeiter_arr[$a]['benutzer_id'];	
			$this->jahresuebersicht_mitarbeiter_kurz($benutzer_id, $jahr);
			#echo "$this->benutzername $this->rest_jahr<br>";
			
		/*	$this->anspruch_jahr = $anspruch;
		$this->anspruch_monate = $monate;
		$this->anspruch_gesamt = $g_anspruch;
		$this->anspruch_vorjahre = $rest_aus_vorjahren;
		$this->genommen = $genommen;
		$this->geplant = $geplant;
		$this->rest_aktuell = $rest_aktuell;
		$this->rest_jahr = $rest_jahr;
		*/
			$table_arr[$zaehler][MITARBEITER] = "$this->benutzername";
			$table_arr[$zaehler][MONATE] = "$this->anspruch_monate";
			$table_arr[$zaehler][ANSPRUCH] = "$this->anspruch_jahr";
			$table_arr[$zaehler][REST_VORJAHR] = "$this->anspruch_vorjahre";
			$table_arr[$zaehler][G_ANSPRUCH] = "$this->anspruch_gesamt";
			$table_arr[$zaehler][GENOMMEN] = "$this->genommen";
			$table_arr[$zaehler][GEPLANT] = "$this->geplant";
			$table_arr[$zaehler][REST_AKT] = "$this->rest_aktuell";
			$table_arr[$zaehler][REST_J] = "$this->rest_jahr";
			
			
			
	}//end for

$pdf->ezTable($table_arr,$cols,"Jahresübersicht $jahr",
array('showHeadings'=>1,'showLines'=>'1', 'shaded'=>1,'shadeCol'=>array(0.78, 0.95,1), 'shadeCol2'=>array(0.1, 0.5, 1), 'titleFontSize' => 10, 'fontSize' => 7, 'xPos'=>50,'xOrientation'=>'right',  'width'=>500,'cols'=>array('MITARBEITER'=>array('justification'=>'right', 'width'=>80))));
	$datum_uhrzeit = date("d.m.Y      H:i");
	$pdf->ezSetDy(-20); //abstand
	$pdf->ezText("Übersicht vom $datum_uhrzeit Uhr",7, array('left'=>'0'));
		
		#header("Content-type: application/pdf");  // wird von MSIE ignoriert
		
		$pdf->ezStream();

}
}


function jahresuebersicht_anzeigen($jahr){
	$rest_aktuell = 0;
	$mitarbeiter_arr = $this->mitarbeiter_arr($jahr);
	#print_r($mitarbeiter_arr);
	if(!is_array($mitarbeiter_arr)){
		echo "Keine Mitarbeiter vorhanden, Eintrittsdaten erst nach $jahr";
	}else{
echo "<table>";
$vorjahr = $jahr -1 ;
$pdf_link = "<a href=\"?daten=urlaub&option=uebersicht_pdf&jahr=$jahr\">PDF</a>";
echo "<tr class=\"feldernamen\"><td colspan =\"9\">$pdf_link</td></tr>";
#echo "<tr class=\"feldernamen\"><td>MITARBEITER</td><td>OPTIONEN</td><td>MONATE $jahr</td><td>TAGE $jahr</td><td>REST $vorjahr</td><td>G. ANSPRUCH</td><td>GENOMMEN</td><td>GEPLANT</td><td>RESTURLAUB</td></tr>";
echo "</table>";
echo "<table class=\"sortable\">";
echo "<tr><th>MITARBEITER</th><th>OPTIONEN</th><th>MONATE $jahr</th><th>TAGE $jahr</th><th>REST $vorjahr</th><th>ANSPRUCH GESAMT</th><th>GENOMMEN</th><th>GEPLANT</th><th>RESTURLAUB</th></tr>";

		$anzahl_daten = count($mitarbeiter_arr);
		$zaehler = 0;
		for($a=0;$a<$anzahl_daten;$a++){
			$zaehler++;
			$mitarbeiter = $mitarbeiter_arr[$a]['benutzername'];
			$benutzer_id = $mitarbeiter_arr[$a]['benutzer_id'];
			
			$eintritt = $mitarbeiter_arr[$a]['EINTRITT'];
			$eintritt_arr = explode("-", $eintritt);
			$eintritt_jahr = $eintritt_arr[0];
			$eintritt_monat = $eintritt_arr[1];
			$eintritt_tag = $eintritt_arr[2];
			
			$austritt = $mitarbeiter_arr[$a]['AUSTRITT'];
			$austritt_arr = explode("-", $austritt);
			$austritt_jahr = $austritt_arr[0];
			$austritt_monat = $austritt_arr[1];
			$austritt_tag = $austritt_arr[2];
			
			
			$anspruch =  $mitarbeiter_arr[$a]['URLAUB'];
			$anspruch_pro_m = $anspruch/12;
			$anspruch_pro_tag = $anspruch/365;
			
			
			/*Erstes Jahr in der Firma*/
			if($eintritt_jahr == $jahr){
				/*Mitarbeiter noch beschäftigt*/
				
				if($austritt_jahr == '0000'){
				$bis="$jahr-12-31";
				$tage = $this->tage_zwischen($eintritt, $bis);
				$monate = $tage/30;
				}else{
				
				
					$jahre = $austritt_jahr - $eintritt_jahr;
					/*Mitarbeiter nicht mehr beschäftigt, gleiches Jahr ausgetreten*/
					if($jahre==0){
					$tage = $this->tage_zwischen($eintritt, $austritt);
					$monate = $tage/30;
					}
					/*Mitarbeiter nicht mehr beschäftigt, Jahre danach ausgetreten*/
					if($jahre>0){
					$bis="$jahr-12-31";
					$tage = $this->tage_zwischen($eintritt, $bis);
					$monate = $tage/30;
					}
						
				}
				
			}
			
			/*Jahre danach in der Firma*/
			if($eintritt_jahr < $jahr){
				/*Mitarbeiter noch beschäftigt*/
				
				if($austritt_jahr == '0000'){
				$tage = 365;
				$monate = 12;
				}else{
				
				
					$jahre = $austritt_jahr - $eintritt_jahr;
					
					/*Mitarbeiter nicht mehr beschäftigt, 1 Jahr danach ausgetreten*/
					if($jahre==1){
					$von= "$jahr-01-01";
					$tage = $this->tage_zwischen($von, $austritt);
					$monate = $tage/30;	
					}
					/*Mitarbeiter nicht mehr beschäftigt, Jahre danach ausgetreten*/
					if($jahre>1){
						if($jahr != $austritt_jahr){
						$tage = 365;
						$monate = 12;	
						}else{
						$von= "$jahr-01-01";
						$tage = $this->tage_zwischen($von, $austritt);
						$monate = $tage/30;	
						}
					}
						
				}
				
			}
			
			
			
			
			#$monate = floor($monate);
			$anspruch = $monate*$anspruch_pro_m;
			$anspruch1 = $tage*$anspruch_pro_tag;
			/*Jahr vor Eintritt in die Firma*/
			if($eintritt_jahr > $jahr){
			$anspruch =  '0.0';
			}
					
			
						
			$genommen_arr =  $this->anzahl_genommene_tage($jahr, $benutzer_id);
			#$ausgezahlt_arr = $this->anzahl_genommene_tage($jahr, $benutzer_id, 'Auszahlung');
			#$genommen_arr = array_merge($genommen_arr_temp, $ausgezahlt_arr);
			#echo 'GENOMMEN URLAUB<pre>';
			#print_r($genommen_arr_temp);
			#echo "$mitarbeiter AUSGEZAHLT<hr>";
			#print_r($ausgezahlt_arr);
			#echo "NEU<hr>";
			#print_r($genommen_arr);
			$geplant_arr = $this->anzahl_geplanter_tage($jahr, $benutzer_id);
			#$geplant_arr_ausz = $this->anzahl_geplanter_tage($jahr, $benutzer_id, 'Auszahlung');
			#$geplant_arr = array_merge($geplant_arr_u, $geplant_arr_ausz);
			
			#die();
			if(is_array($geplant_arr)){
			$geplant = $geplant_arr[0]['GEPLANT'];
			}else{
				$geplant = '0';
			}
			if(is_array($genommen_arr)){
			$genommen = $genommen_arr[0]['GENOMMEN'];
			}else{
				$genommen = '0';
			}
			$rest_aus_vorjahren = $this->rest_aus_vorjahren($jahr, $benutzer_id);
			#$anspruch = $anspruch + $rest_aus_vorjahren;
			#$rest_aktuell = $anspruch - $genommen;
			$rest_jahr = $rest_aktuell - $geplant;
						
			#echo '<pre>';
			#print_r($geplant_arr);
			#print_r($genommen_arr);
		$link_urlaubsantrag = "<a href=\"?daten=urlaub&option=urlaubsantrag&benutzer_id=$benutzer_id\">Abwesenheit eintragen</a>&nbsp;";
		$link_jahresansicht = "<a href=\"?daten=urlaub&option=jahresansicht&jahr=$jahr&benutzer_id=$benutzer_id\">Jahresansicht</a>&nbsp;";
		#$link_monatsansicht = "<a href=\"?daten=urlaub&option=monatsansicht&jahr=$jahr\">Monatssansicht</a>&nbsp;";
			
		if($austritt !='0000-00-00'){
			$mitarbeiter = "<b>$mitarbeiter</b>";
		}
		$g_anspruch = $anspruch + $rest_aus_vorjahren;
		$g_anspruch = $this->runden($g_anspruch);
		$rest_aktuell = $g_anspruch - $genommen;
		$rest_jahr = $g_anspruch - $genommen - $geplant;
		
			
		$g_anspruch = nummer_punkt2komma($g_anspruch);
				
		$rest_aus_vorjahren = nummer_punkt2komma($rest_aus_vorjahren);
		$anspruch = nummer_punkt2komma($anspruch);
		$geplant = nummer_punkt2komma($geplant);
		$genommen = nummer_punkt2komma($genommen);
		$rest_aktuell = nummer_punkt2komma($rest_aktuell);
		$rest_jahr = nummer_punkt2komma($rest_jahr);
		$anspruch = nummer_punkt2komma($anspruch);
		$monate = nummer_punkt2komma($monate);
		if($zaehler == 1){
		echo "<tr class=\"zeile1\"><td>$mitarbeiter</td><td>$link_urlaubsantrag $link_jahresansicht </td><td align=\"right\">$monate</td><td align=\"right\">$anspruch</td><td align=\"right\"> $rest_aus_vorjahren</td><td align=\"right\"><b>$g_anspruch</b></td><td align=\"right\">$genommen</td><td align=\"right\">$geplant</td><td align=\"right\">$rest_aktuell <b>($rest_jahr)</b></td></tr>";
		}
		if($zaehler == 2){
		echo "<tr class=\"zeile2\"><td>$mitarbeiter</td><td>$link_urlaubsantrag $link_jahresansicht </td><td align=\"right\">$monate</td><td align=\"right\">$anspruch</td><td align=\"right\"> $rest_aus_vorjahren</td><td align=\"right\"><b>$g_anspruch</b></td><td align=\"right\">$genommen</td><td align=\"right\">$geplant</td><td align=\"right\">$rest_aktuell <b>($rest_jahr)</b></td></tr>";
		$zaehler = 0;
		}
		unset($genommen_arr);
		unset($geplant_arr);
		}
	echo "</TABLE>"; 
	}

#$this->jahresuebersicht_mitarbeiter_kurz(1, 2010);
}


function jahresuebersicht_mitarbeiter_kurz($benutzer_id, $jahr){
	
$vorjahr = $jahr -1 ;
#echo "<table><tr class=\"feldernamen\"><td>ddddddddMITARBEITER</td><td>OPTIONEN</td><td>MONATE $jahr</td><td>TAGE $jahr</td><td>REST $vorjahr</td><td>G. ANSPRUCH</td><td>GENOMMEN</td><td>GEPLANT</td><td>RESTURLAUB</td></tr>";

	
	
	
			$this->mitarbeiter_details($benutzer_id);
			$mitarbeiter = $this->benutzername;
			
			$eintritt = $this->eintritt;
			$eintritt_arr = explode("-", $eintritt);
			$eintritt_jahr = $eintritt_arr[0];
			$eintritt_monat = $eintritt_arr[1];
			$eintritt_tag = $eintritt_arr[2];
			
			$austritt = $this->austritt;
			$austritt_arr = explode("-", $austritt);
			$austritt_jahr = $austritt_arr[0];
			$austritt_monat = $austritt_arr[1];
			$austritt_tag = $austritt_arr[2];
			
			
			$anspruch =  $this->urlaub;
			$anspruch_pro_m = $anspruch/12;
			$anspruch_pro_tag = $anspruch/365;
			
			
			/*Erstes Jahr in der Firma*/
			if($eintritt_jahr == $jahr){
				/*Mitarbeiter noch beschäftigt*/
				
				if($austritt_jahr == '0000'){
				$bis="$jahr-12-31";
				$tage = $this->tage_zwischen($eintritt, $bis);
				$monate = $tage/30;
				}else{
				
				
					$jahre = $austritt_jahr - $eintritt_jahr;
					/*Mitarbeiter nicht mehr beschäftigt, gleiches Jahr ausgetreten*/
					if($jahre==0){
					$tage = $this->tage_zwischen($eintritt, $austritt);
					$monate = $tage/30;
					}
					/*Mitarbeiter nicht mehr beschäftigt, Jahre danach ausgetreten*/
					if($jahre>0){
					$bis="$jahr-12-31";
					$tage = $this->tage_zwischen($eintritt, $bis);
					$monate = $tage/30;
					}
						
				}
				
			}
			
			/*Jahre danach in der Firma*/
			if($eintritt_jahr < $jahr){
				/*Mitarbeiter noch beschäftigt*/
				
				if($austritt_jahr == '0000'){
				$tage = 365;
				$monate = 12;
				}else{
				
				
					$jahre = $austritt_jahr - $eintritt_jahr;
					
					/*Mitarbeiter nicht mehr beschäftigt, 1 Jahr danach ausgetreten*/
					if($jahre==1){
					$von= "$jahr-01-01";
					$tage = $this->tage_zwischen($von, $austritt);
					$monate = $tage/30;	
					}
					/*Mitarbeiter nicht mehr beschäftigt, Jahre danach ausgetreten*/
					if($jahre>1){
						if($jahr != $austritt_jahr){
						$tage = 365;
						$monate = 12;	
						}else{
						$von= "$jahr-01-01";
						$tage = $this->tage_zwischen($von, $austritt);
						$monate = $tage/30;	
						}
					}
						
				}
				
			}
			
			
			
			
			#$monate = floor($monate);
			$anspruch = $monate*$anspruch_pro_m;
			$anspruch1 = $tage*$anspruch_pro_tag;
			/*Jahr vor Eintritt in die Firma*/
			if($eintritt_jahr > $jahr){
			$anspruch =  '0.0';
			}
					
			
						
			$genommen_arr =  $this->anzahl_genommene_tage($jahr, $benutzer_id);
			$geplant_arr = $this->anzahl_geplanter_tage($jahr, $benutzer_id);
			if(is_array($geplant_arr)){
			$geplant = $geplant_arr[0]['GEPLANT'];
			}else{
				$geplant = '0';
			}
			if(is_array($genommen_arr)){
			$genommen = $genommen_arr[0]['GENOMMEN'];
			}else{
				$genommen = '0';
			}
			$rest_aus_vorjahren = $this->rest_aus_vorjahren($jahr, $benutzer_id);
			#$anspruch = $anspruch + $rest_aus_vorjahren;
			#$rest_aktuell = $anspruch - $genommen;
			#$rest_jahr = $rest_aktuell - $geplant;
						
		
			
		if($austritt !='0000-00-00'){
			$mitarbeiter = "<b>$mitarbeiter</b>";
		}
		$g_anspruch = $anspruch + $rest_aus_vorjahren;
		$g_anspruch = $this->runden($g_anspruch);
		$rest_aktuell = $g_anspruch - $genommen;
		$rest_jahr = $g_anspruch - $genommen - $geplant;
		
			
		$g_anspruch = nummer_punkt2komma($g_anspruch);
				
		$rest_aus_vorjahren = nummer_punkt2komma($rest_aus_vorjahren);
		$anspruch = nummer_punkt2komma($anspruch);
		$geplant = nummer_punkt2komma($geplant);
		$genommen = nummer_punkt2komma($genommen);
		$rest_aktuell = nummer_punkt2komma($rest_aktuell);
		$rest_jahr = nummer_punkt2komma($rest_jahr);
		$anspruch = nummer_punkt2komma($anspruch);
		$monate = nummer_punkt2komma($monate);
		#echo "<tr class=\"zeile1\"><td>$mitarbeiter</td><td>$link_urlaubsantrag $link_jahresansicht </td><td>$monate</td><td>$anspruch</td><td> $rest_aus_vorjahren</td><td><b>$g_anspruch</b></td><td>$genommen</td><td>$geplant</td><td>$rest_aktuell <b>($rest_jahr)</b></td></tr>";
		
		$this->anspruch_jahr = $anspruch;
		$this->anspruch_monate = $monate;
		$this->anspruch_gesamt = $g_anspruch;
		$this->anspruch_vorjahre = $rest_aus_vorjahren;
		$this->genommen = $genommen;
		$this->geplant = $geplant;
		$this->rest_aktuell = $rest_aktuell;
		$this->rest_jahr = $rest_jahr;
		
		unset($genommen_arr);
		unset($geplant_arr);

	#echo "</TABLE>"; 
	
}





function runden($zahl){
$zahl = sprintf('%01.2f',$zahl);
$zahl_arr = explode(".", $zahl);
$vorkomma = $zahl_arr[0];
$nachkomma = $zahl_arr[1];
if($nachkomma == '50'){
	$neue_zahl = $zahl;
}

if($nachkomma > '50'){
	$neue_zahl = round($zahl);
}
if($nachkomma < '50'){
	$neue_zahl = floor($zahl);
}
return $neue_zahl;
}


function jahres_ansicht($benutzer_id, $jahr){
$result = mysql_query ("SELECT U_DAT, benutzername, ANTRAG_D, DATUM, ANTEIL, ART from BENUTZER JOIN URLAUB ON (BENUTZER.benutzer_id = URLAUB.BENUTZER_ID) WHERE BENUTZER.benutzer_id='$benutzer_id' && DATE_FORMAT(URLAUB.DATUM, '%Y') = '$jahr' && AKTUELL='1' ORDER BY  DATUM ASC ");
	
	$numrows = mysql_numrows($result);
		if($numrows){
		$link_benutzer_jahr_pdf = "<a href=\"?daten=urlaub&option=jahresansicht_pdf&jahr=$jahr&benutzer_id=$benutzer_id\">PDF-Ansicht</a>";
		 echo "<table><tr class=\"feldernamen\"><td colspan=\"6\">$link_benutzer_jahr_pdf</td></tr>";
		 echo "<tr class=\"feldernamen\"><td>Zeile</td><td>Antrag vom</td><td>Art</td><td>Datum, Wochentag</td><td>Anteil</td><td>Option</td></tr>";
		 $summe_tage = 0;
		 $zeile = 0;
		 while ($row = mysql_fetch_assoc($result)){	
		 $zeile++;
		 $benutzername = $row['benutzername'];
		 $antrag_vom = $row['ANTRAG_D'];
		 $urlaubstag = $row['DATUM'];
		 $anteil = $row['ANTEIL'];
		 $art = $row['ART'];
		 $summe_tage = $summe_tage + $anteil;
		 #echo "$zeile. $antrag_vom $urlaubstag $anteil Tag(-e)<br>";
		 $antrag_vom = date_mysql2german($antrag_vom);
		 $urlaubstag = date_mysql2german($urlaubstag);
		 $wochentag = $this->tagesname($urlaubstag);
		 $u_dat = $row['U_DAT'];
		 $link_loeschen = "<a href=\"?daten=urlaub&option=urlaubstag_loeschen&u_dat=$u_dat&benutzer_id=$benutzer_id&jahr=$jahr\">Urlaubstag löschen</a>";
		 echo "<tr class=\"zeile1\"><td>$zeile</td><td>$antrag_vom</td><td>$art</td><td>$urlaubstag, $wochentag</td><td>$anteil</td><td>$link_loeschen</td></tr>";
		 }
		echo "$benutzername Gesamt: $summe_tage Tage";
		echo "</TABLE>";
		}else{
			echo "KEINE URLAUBSDATEN VORHANDEN";
		}
		#echo '<pre>';
		#print_r($my_array);
		
}

function jahres_ansicht_pdf($benutzer_id, $jahr){
$this->jahresuebersicht_mitarbeiter_kurz($benutzer_id, $jahr);



$result = mysql_query ("SELECT U_DAT, benutzername, ANTRAG_D, DATUM, ANTEIL, ART from BENUTZER JOIN URLAUB ON (BENUTZER.benutzer_id = URLAUB.BENUTZER_ID) WHERE BENUTZER.benutzer_id='$benutzer_id' && DATE_FORMAT(URLAUB.DATUM, '%Y') = '$jahr' && AKTUELL='1' ORDER BY DATUM ASC ");
	
	$numrows = mysql_numrows($result);
		if($numrows){
		 ob_clean(); //ausgabepuffer leeren
	include_once('pdfclass/class.ezpdf.php');
	include_once('classes/class_bpdf.php');
		$pdf = new Cezpdf('a4', 'portrait');
		$bpdf = new b_pdf;
		$bpdf->b_header($pdf, 'Partner', $_SESSION[partner_id], 'portrait', 'pdfclass/fonts/Helvetica.afm', 6);
		
		 
		 $summe_tage = 0;
		 $summe_krank = 0;
		 $summe_ausgezahlt = 0;
		 $summe_unbezahlt = 0; 
		$zeile = 0;
		 $cols = array('ZEILE'=>"Tag", 'ART'=>"Grund",'URLAUBSTAG'=>"Urlaubstag", 'ANTEIL'=>"Anteil");
		 while ($row = mysql_fetch_assoc($result)){	
		 $zeile++;
		 $benutzername = $row[benutzername];
		 $antrag_vom = $row['ANTRAG_D'];
		 $urlaubstag = $row['DATUM'];
		 $anteil = $row['ANTEIL'];
		 $art = $row['ART'];
		 if($art=='Urlaub'){
		 $summe_tage += $anteil;
		 }
		 if($art=='Krank'){
		 $summe_krank += $anteil;
		 }
		 
		 if($art=='Auszahlung'){
		 $summe_ausgezahlt += $anteil;
		 }
		 
		 if($art=='Unbezahlt'){
		 $summe_unbezahlt += $anteil;
		 }
		 
		 #echo "$zeile. $antrag_vom $urlaubstag $anteil Tag(-e)<br>";
		 $antrag_vom = date_mysql2german($antrag_vom);
		 $urlaubstag = date_mysql2german($urlaubstag);
		 $wochentag = $this->tagesname($urlaubstag);
		 $u_dat = $row['U_DAT'];
		 $table_arr[$zeile]['URLAUBSTAG'] = "$urlaubstag, $wochentag";
		 $table_arr[$zeile]['ANTEIL'] = "$anteil";
		 $table_arr[$zeile]['ART'] = "$art";
		 #echo "<tr class=\"zeile1\"><td>$zeile</td><td>$antrag_vom</td><td>$urlaubstag, $wochentag</td><td>$anteil</td><td>$link_loeschen</td></tr>";
		$table_arr[$zeile]['ZEILE'] = "$zeile";
		 }
	#	echo "$benutzername Gesamt: $summe_tage Tage";
	
		
		$zz = $zeile+1;
		$table_arr[$zz]['URLAUBSTAG'] = "Genommene Urlaubstage";
		$table_arr[$zz]['ANTEIL'] = "$summe_tage Tage";
		$zz++;
		$table_arr[$zz]['URLAUBSTAG'] = "Ausgezahlter Urlaub";
		$table_arr[$zz]['ANTEIL'] = "$summe_ausgezahlt Tage";
		$zz++;
		$table_arr[$zz]['URLAUBSTAG'] = "----------------------";
		$table_arr[$zz]['ANTEIL'] = "-----";
		$zz++;
		$table_arr[$zz]['URLAUBSTAG'] = "<b>Genommen";
		$su_g = $summe_tage+$summe_ausgezahlt;
		$table_arr[$zz]['ANTEIL'] = "$su_g Tage</b>";
		$zz++;
		$table_arr[$zz]['URLAUBSTAG'] = "Anspruch Jahr  ($this->anspruch_monate Monate)";
		$table_arr[$zz]['ANTEIL'] = "$this->anspruch_jahr Tage</b>";
		$zz++;
		$r_urlaub_jahr = $this->anspruch_jahr - $su_g;
		$table_arr[$zz]['URLAUBSTAG'] = "Resturlaub Jahr";
		$table_arr[$zz]['ANTEIL'] = "$r_urlaub_jahr Tage</b>";
		$zz++;
		$table_arr[$zz]['URLAUBSTAG'] = "Rest aus Vorjahren $this->anspruch_vorjahre";
		$table_arr[$zz]['ANTEIL'] = $this->runden($this->anspruch_vorjahre)." Tage";
		$zz++;
		$su_noch = $r_urlaub_jahr+$this->anspruch_vorjahre;
		$table_arr[$zz]['URLAUBSTAG'] = "Rest Vorjahre + Aktuell";
		$table_arr[$zz]['ANTEIL'] = "$su_noch Tage</i></b>";
		$zz++;
		$table_arr[$zz]['URLAUBSTAG'] = "<b><i>Unbezahlter Urlaub";
		$table_arr[$zz]['ANTEIL'] = "$summe_unbezahlt Tage</i></b>";
		$zz++;
		$table_arr[$zz]['URLAUBSTAG'] = "Krank im Jahr $jahr";
		$table_arr[$zz]['ANTEIL'] = "$summe_krank Tage";
		$zz++;
		/*$table_arr[$zeile+6][URLAUBSTAG] = "Geplant: $this->geplant Tage";
		$table_arr[$zeile+8][URLAUBSTAG] = "Resttage aktuell: $this->rest_aktuell Tage";
		$table_arr[$zeile+9][URLAUBSTAG] = "<b>Resttage Jahr: $this->rest_jahr Tage</b>";
		$datum_heute = date("d.m.Y     H:i");
		$table_arr[$zeile+10][URLAUBSTAG] = "Druck erfolgte am $datum_heute Uhr </b>";
		*/
		/*
		$this->anspruch_jahr = $anspruch;
		$this->anspruch_monate = $monate;
		$this->anspruch_gesamt = $g_anspruch;
		$this->anspruch_vorjahre = $rest_aus_vorjahren;
		$this->genommen = $genommen;
		$this->geplant = $geplant;
		$this->rest_aktuell = $rest_aktuell;
		$this->rest_jahr = $rest_jahr;
		*/
		
	$pdf->ezTable($table_arr,$cols,"Abwesenheit $benutzername  $jahr",
array('showHeadings'=>1,'showLines'=>'1', 'shaded'=>1,'shadeCol'=>array(0.78, 0.95,1), 'shadeCol2'=>array(0.1, 0.5, 1), 'titleFontSize' => 10, 'fontSize' => 7, 'xPos'=>50,'xOrientation'=>'right',  'width'=>300,'cols'=>array('ZEILE'=>array('justification'=>'right', 'width'=>30),'ART'=>array('justification'=>'right', 'width'=>60), 'ANTEIL'=>array('justification'=>'right', 'width'=>40))));
		$pdf->ezStream();
		}else{
			echo "KEINE URLAUBSDATEN VORHANDEN";
		}
		#echo '<pre>';
		#print_r($my_array);
		
}


function form_urlaubsantrag($benutzer_id){
	$f = new formular;
	$z = new zeiterfassung;
	$mitarbeiter_name = $z->get_benutzer_name($benutzer_id);
    $f->erstelle_formular("Urlaubsplanung und Abwesenheit für $mitarbeiter_name", NULL);
    $f->datum_feld('Abwesend vom', 'u_vom', "", 'u_vom');
    $f->datum_feld('Abwesend bis', 'u_bis', "", 'u_bis');
	#$f->radio_button('art', 'krank', 'als Krank eintragen');
	$this->dropdown_art('Abwesenheitsgrund', 'art', 'art', '');
    $f->hidden_feld("benutzer_id", "$benutzer_id");
	$f->hidden_feld("option", "urlaubsantrag_check");
	$f->send_button("submit", "Eintragen");
	$f->ende_formular();
#$this->tag_danach("2009-12-12");
#$this->tage_arr("2009-12-10", "2009-12-20");
}

function dropdown_art($beschreibung, $name, $id, $js){
	echo "<label for=\"$name\">$beschreibung</label><select name=\"$name\" id=\"$id\" $js> \n";
	echo "<option value=\"Urlaub\" selected>Urlaub</option>\n";	
	echo "<option value=\"Krank\">Krank</option>\n";	
	echo "<option value=\"Auszahlung\">Auszahlung</option>\n";
	echo "<option value=\"Unbezahlt\">Unbezahlt</option>\n";
	echo "</select>";
}


function tage_arr($benutzer_id,$datum_a, $datum_e, $art='Urlaub'){
	
	
	if($datum_a == $datum_e){
		if($this->feiertag ($datum_a) == 'Arbeitstag'){
		$anteil = $this->anteil_datum($datum_a);
		$this->urlaubstag_speichern($benutzer_id, $datum_a, $anteil,$art);
		}else{
			echo "Der gewünschte Tag ist ein Feiertag oder Wochenende";
		}
	}else{
	/*ersten Tag eingeben*/
	if($this->feiertag ($datum_a) == 'Arbeitstag'){
		$anteil = $this->anteil_datum($datum_a);
		$this->urlaubstag_speichern($benutzer_id, $datum_a, $anteil, $art);
		}else{
			echo "Der gewünschte Tag ist ein Feiertag oder Wochenende";
		}
	
	$zeile = 0;
	while($datum_a!=$datum_e){
		
		$datum_a = $this->tag_danach($datum_a);
		echo $datum_a.'   ';	
		$tag_name = $this->tagesname($datum_a);
			if($this->feiertag ($datum_a) == 'Arbeitstag'){
			$anteil = $this->anteil_datum($datum_a);
			$this->urlaubstag_speichern($benutzer_id, $datum_a, $anteil, $art);
			echo "$tag_name $datum_a wurde als Urlaubstag eingegeben<br>";
			}else{
				echo "<b>$tag_name $datum_a ist  ".$this->feiertag ($datum_a).'</b><br>';
			}
		
			
	unset($anteil);
	$zeile++;
	
	}
	}
}

function anteil_datum($datum){
	$datum_arr = explode("-", $datum);
	$monat = $datum_arr[1];
	$tag = $datum_arr[2];
	$result = mysql_query ("SELECT ANTEIL FROM URLAUB_EINST WHERE DATE_FORMAT(DATUM, '%m-%d') ='$monat-$tag' LIMIT 0,1 ");
	
	$numrows = mysql_numrows($result);
		if($numrows){
		$row = mysql_fetch_assoc($result);	
		return $row['ANTEIL'];
		}else{
			return '1.0';
		}
}


function urlaubstag_speichern($benutzer_id, $datum, $anteil, $art='Urlaub'){
	$datum_heute = date("Y-m-d");
	
	if($this->urlaubstag_eingetragen($datum, $benutzer_id)){
	$d_a = date_mysql2german($datum);
	echo "$d_a wurde schon als Urlaubstag eingetragen<br>";	
	}else{
	
	$db_abfrage = "INSERT INTO URLAUB VALUES (NULL, '$benutzer_id','$datum_heute','$datum', '$anteil', '1', '$art')";

$resultat = mysql_query($db_abfrage) or
           die(mysql_error());
	}
}

function urlaubstag_eingetragen($datum, $benutzer_id){
	$result = mysql_query ("SELECT * FROM URLAUB WHERE DATUM='$datum' && BENUTZER_ID='$benutzer_id' && AKTUELL='1'");
	$numrows = mysql_numrows($result);
	if($numrows){
	return true;
	}else{
		return false;
	}
}


function tag_danach($datum){
$datum_arr = explode("-",$datum);
$jahr = $datum_arr[0];
$monat =   $datum_arr[1];
$tag =   $datum_arr[2];
$morgen = date('Y-m-d', mktime(0, 0, 0, $monat , $tag + 1, $jahr));
return $morgen;
}

function wochentag($datum){
$datum_arr = explode("-",$datum);
$jahr = $datum_arr[0];
$monat =   $datum_arr[1];
$tag =   $datum_arr[2];
$wochentag = date('w', mktime(0, 0, 0, $monat , $tag, $jahr));
return $wochentag;
}

function date2name($datum){
$datum = date_german2mysql($datum);
$datum_arr = explode("-",$datum);
$jahr = $datum_arr[0];
$monat =   $datum_arr[1];
$tag =   $datum_arr[2];
#$wochentag_name = date('l', mktime(0, 0, 0, $monat , $tag, $jahr));

$datum_arr1 = getdate(mktime(0,0,0,$monat,$tag,$jahr));
$wochentag_name = $datum_arr1['weekday'];
#echo '<pre>';
#print_r($datum_arr1);
return $wochentag_name;
}


function anzahl_tage_monat($datum){
$datum_arr = explode("-",$datum);
$jahr = $datum_arr[0];
$monat =   $datum_arr[1];
$tag =   $datum_arr[2];

$tage = date("t",mktime(0, 0, 0, $monat, 1, $jahr));

return $tage;
}


function tagesname($datum){
$tagesname = $this->date2name($datum);
if($tagesname == 'Monday'){
	return 'Montag';
}
if($tagesname == 'Tuesday'){
	return 'Dienstag';
}
if($tagesname == 'Wednesday'){
	return 'Mittwoch';
}
if($tagesname == 'Thursday'){
	return 'Donnerstag';
}
if($tagesname == 'Friday'){
	return 'Freitag';
}
if($tagesname == 'Saturday'){
	return 'Samstag';
}
if($tagesname == 'Sunday'){
	return 'Sonntag';
}	
}

function urlaubstag_loeschen($dat){
$db_abfrage = "UPDATE URLAUB SET AKTUELL='0' WHERE U_DAT='$dat'";
$resultat = mysql_query($db_abfrage) or
           die(mysql_error());
	echo "gelöscht";
}

function urlaubstag_loeschen_datum($benutzer_id,$datum){
$db_abfrage = "UPDATE URLAUB SET AKTUELL='0' WHERE DATUM='$datum' && BENUTZER_ID='$benutzer_id'";
$resultat = mysql_query($db_abfrage) or
           die(mysql_error());
	echo "gelöscht";
	
}




function feiertag ($datum) {

    $datum = explode("-", $datum);

    $datum[1] = str_pad($datum[1], 2, "0", STR_PAD_LEFT);
    $datum[2] = str_pad($datum[2], 2, "0", STR_PAD_LEFT);

    if (!checkdate($datum[1], $datum[2], $datum[0])) return false;

    $datum_arr = getdate(mktime(0,0,0,$datum[1],$datum[2],$datum[0]));

    $easter_d = date("d", easter_date($datum[0]));
    $easter_m = date("m", easter_date($datum[0]));

    $status = 'Arbeitstag';
    if ($datum_arr['wday'] == 0 || $datum_arr['wday'] == 6) $status = 'Wochenende';

    if ($datum[1].$datum[2] == '0101') {
        return 'Neujahr';
    }
      elseif ($datum[1].$datum[2] == date("md",mktime(0,0,0,$easter_m,$easter_d-2,$datum[0]))) {
        return 'Karfreitag';
    
    } elseif ($datum[1].$datum[2] == date("md",mktime(0,0,0,$easter_m,$easter_d+1,$datum[0]))) {
        return 'Ostermontag';
    }  elseif ($datum[1].$datum[2] == '0501') {
        return 'Erster Mai';
    } elseif ($datum[1].$datum[2] == date("md",mktime(0,0,0,$easter_m,$easter_d+39,$datum[0]))) {
        return 'Christi Himmelfahrt';
     } elseif ($datum[1].$datum[2] == date("md",mktime(0,0,0,$easter_m,$easter_d+50,$datum[0]))) {
        return 'Pfingstmontag';
    }  elseif ($datum[1].$datum[2] == '1003') {
        return 'Tag der deutschen Einheit';
    } elseif ($datum[1].$datum[2] == '1225') {
        return '1. Weihnachtstag';
      }elseif ($datum[1].$datum[2] == '1226') {
        return '2. Weihnachtstag';
        } 
         
     else {
        return $status;
    }

}






function monatsansicht($monat, $jahr){
	$mitarbeiter_arr = $this->mitarbeiter_arr($jahr);
	if(!is_array($mitarbeiter_arr)){
		echo "Keine Mitarbeiter vorhanden, Eintrittsdaten erst nach $jahr";
	}else{

$datum = "$jahr-$monat-01";
$anzahl_t = $this->anzahl_tage_monat($datum);

if($monat>1 && $monat<12){
	$vormonat = $monat-1;
	$vormonatname = monat2name($vormonat);
	$nachmonat = $monat+1;
	$nachmonatname = monat2name($nachmonat);
	$v_jahr = $jahr;
	$n_jahr = $jahr;
}
if($monat == 1){
	$vormonat = 12;
	$vormonatname = monat2name($vormonat);
	$nachmonat = $monat+1;
	$nachmonatname = monat2name($nachmonat);
	$v_jahr = $jahr-1;
	$n_jahr = $jahr;
}
if($monat == 12){
	$vormonat = $monat-1;
	$vormonatname = monat2name($vormonat);
	$nachmonat = 1;
	$nachmonatname = monat2name($nachmonat);
	$v_jahr = $jahr;
	$n_jahr = $jahr+1;
	}

$monatsname = monat2name($monat);
$link_vormonat = "<a href=\"?daten=urlaub&option=monatsansicht&jahr=$v_jahr&monat=$vormonat\">$vormonatname $v_jahr</a>";
$link_nachmonat = "<a href=\"?daten=urlaub&option=monatsansicht&jahr=$n_jahr&monat=$nachmonat\">$nachmonatname $n_jahr</a>";
$link_pdf = "<a href=\"?daten=urlaub&option=monatsansicht_pdf&jahr=$n_jahr&monat=$monat\">$monatsname PDF</a>";





/*Ausgabe der Tage*/
echo "<table class=\"sortable\">";

echo "<thead><tr><th colspan=\"33\">$monatsname $jahr $link_vormonat $link_nachmonat $link_pdf</th></tr>";
echo "<tr class=\"rot\">";
echo "<td class=\"rot\">MITARBEITER</td><td>REST</td>";
	for($a=1;$a<=$anzahl_t;$a++){
	echo "<td>$a</td>";
	}
echo "</tr></thead>";


		$anzahl_daten = count($mitarbeiter_arr);
		$zaehler = 0;
		$rest_tage = 0;
		for($i=0;$i<$anzahl_daten;$i++){
			$zaehler++;
			$mitarbeiter = $mitarbeiter_arr[$i]['benutzername'];
			$benutzer_id = $mitarbeiter_arr[$i]['benutzer_id'];
			
			
			$this->jahresuebersicht_mitarbeiter_kurz($benutzer_id, $jahr);
			/*$this->anspruch_jahr = $anspruch;
		$this->anspruch_monate = $monate;
		$this->anspruch_gesamt = $g_anspruch;
		$this->anspruch_vorjahre = $rest_aus_vorjahren;
		$this->genommen = $genommen;
		$this->geplant = $geplant;
		$this->rest_aktuell = $rest_aktuell;
		$this->rest_jahr = $rest_jahr;*/
			
			if($zaehler ==1){
			echo "<tr class=\"zeile1\"><td>$mitarbeiter</td><td><b>$this->rest_jahr</b></td>";	
			}
			if($zaehler ==2){
			echo "<tr class=\"zeile2\"><td>$mitarbeiter</td><td><b>$this->rest_jahr</b></td>";	
			$zaehler = 0;
			}
			for($a=1;$a<=$anzahl_t;$a++){
	    		if($a<10){
	    		$tag = '0'.$a;
	    		}else{
	    			$tag = $a;
	    		}
	    		$datum = "$jahr-$monat-$tag";
	    		$status = $this->feiertag ($datum);
	    		if($status == 'Wochenende'){
	    		$zeichen = "W";
	    		}
	    		if($status != 'Wochenende' & $status !='Arbeitstag'){
	    		$zeichen = "F";
	    		}
	    		if($status =='Arbeitstag'){
	    		$zeichen = "";
	    		}
	    		$status = $this->check_anwesenheit($benutzer_id, $datum);
	    		if($status!=''){
	    		$zeichen = $status; 
	    		}
	    		
	    	$feld_id = $datum.$benutzer_id;	
	    	$datum_j = date_mysql2german($datum);	
	    	$zeichen_k = substr($zeichen,0,2);
			if($zeichen!=''){
	    	echo "<td id=\"$feld_id\" class=\"$zeichen\" onclick=\"urlaub_del_button('$feld_id', '$benutzer_id', '$datum_j')\"><b>$zeichen_k</b></td>";
			}else{
			
			
			echo "<td id=\"$feld_id\" class=\"gruen\" onclick=\"urlaub_buttons('$feld_id', '$benutzer_id', '$datum_j')\">";
			$f = new formular();
			#$f->button_js('urlaub', 'Urlaub', '');
			#$f->button_js('urlaub', 'Krank', '');
			echo "</td>";	
			}	    	
			
			}
			echo "</tr>";	
		$zeichen = '';
		}
echo "</TABLE>";		
		
}
}



function monatsansicht_pdf($monat, $jahr){
	ob_clean(); //ausgabepuffer leeren
	include_once('pdfclass/class.ezpdf.php');
	include_once('classes/class_bpdf.php');
		$pdf = new Cezpdf('a4', 'portrait');
		$bpdf = new b_pdf;
		$bpdf->b_header($pdf, 'Partner', $_SESSION[partner_id], 'portrait', 'pdfclass/fonts/Helvetica.afm', 6);
		$pdf->addText(43,710,6,"U -> Urlaub");
		$pdf->addText(43,704,6,"W -> Wochenende");
		$pdf->addText(43,698,6,"F -> Feiertag");
		$pdf->addText(43,692,6,"*G -> Geburtstag");
		/*Tage*/
		$monat = sprintf('%02d',$monat);	
		$monatsname = monat2name($monat);
		$datum = "$jahr-$monat-01";
		$anzahl_t = $this->anzahl_tage_monat($datum);
		$cols = array('MITARBEITER'=>"Mitarbeiter");
		
		$mitarbeiter_arr = $this->mitarbeiter_arr($jahr);
		$anzahl_mitarbeiter = count($mitarbeiter_arr);

		for($c=0;$c<$anzahl_mitarbeiter;$c++){
		$mitarbeiter = $mitarbeiter_arr[$c]['benutzername'];
		$benutzer_id = $mitarbeiter_arr[$c]['benutzer_id'];
		
		
		
		
		
		for($b=1;$b<=$anzahl_t;$b++){
		$tag = sprintf('%02d',$b);	
		$cols["$tag"] = "$b";
		
		$datum_a = "$jahr-$monat-$tag";
		
		$status = $this->feiertag ($datum_a);
	    		if($status == 'Wochenende'){
	    		$zeichen = "W";
	    		}
	    		if($status != 'Wochenende' && $status !='Arbeitstag'){
	    		$zeichen = "F";
	    		}
	    		if($status =='Arbeitstag'){
	    		$zeichen = "";
	    		}
	    				
				$geburtstag = $this->check_geburtstag($benutzer_id, $datum_a);
				if($geburtstag){
				$zeichen .= "*G";
				}
		
				$status = $this->check_anwesenheit($benutzer_id, $datum_a);
	    		if($status!=''){
	    		$zeichen = $status; 
	    		}
	    		
	    		
	    		
	    	$zeichen_k = substr($zeichen,0,2);
			#echo "<td class=\"$zeichen\"><b>$zeichen_k</b></td>";
			
		
		$table_arr[$c]['MITARBEITER'] = "$mitarbeiter";
		$table_arr[$c]["$tag"] = "$zeichen_k";
		$zeichen = '';
		unset($geburtstag);
	}	//end for 2
		
}//end for 1
		#print_r($cols);
		
		$pdf->ezTable($table_arr,$cols,"Monatsansicht $monatsname $jahr",
array('showHeadings'=>1,'showLines'=>'1', 'shaded'=>1,'shadeCol'=>array(0.78, 0.95,1), 'shadeCol2'=>array(0.1, 0.5, 1), 'titleFontSize' => 10, 'fontSize' => 5, 'xPos'=>50,'xOrientation'=>'right',  'width'=>500,'cols'=>array('MITARBEITER'=>array('justification'=>'right', 'width'=>35))));
	
		
		#header("Content-type: application/pdf");  // wird von MSIE ignoriert
		
		$pdf->ezStream();
		
		
}


function monatsansicht_pdf_mehrere($monat_a, $monat_e, $jahr){
	ob_clean(); //ausgabepuffer leeren
	include_once('pdfclass/class.ezpdf.php');
	include_once('classes/class_bpdf.php');
		$pdf = new Cezpdf('a4', 'portrait');
		$bpdf = new b_pdf;
		$bpdf->b_header($pdf, 'Partner', $_SESSION[partner_id], 'portrait', 'pdfclass/fonts/Helvetica.afm', 6);
		
		$pdf->addText(43,710,6,"U -> Urlaub");
		$pdf->addText(43,704,6,"W -> Wochenende");
		$pdf->addText(43,698,6,"F -> Feiertag");
		$pdf->addText(43,692,6,"*G -> Geburtstag");
		
		for($f=$monat_a;$f<=$monat_e;$f++){
		$monat = $f;
		$monat = sprintf('%02d',$monat);	
		/*Tage*/
		$monatsname = monat2name($monat);
		$datum = "$jahr-$monat-01";
		$anzahl_t = $this->anzahl_tage_monat($datum);
		$cols = array('MITARBEITER'=>"Mitarbeiter");
		
		$mitarbeiter_arr = $this->mitarbeiter_arr($jahr);
		$anzahl_mitarbeiter = count($mitarbeiter_arr);

		for($c=0;$c<$anzahl_mitarbeiter;$c++){
		$mitarbeiter = $mitarbeiter_arr[$c]['benutzername'];
		$benutzer_id = $mitarbeiter_arr[$c]['benutzer_id'];
		
		
		
		
		
		for($b=1;$b<=$anzahl_t;$b++){
		$tag = sprintf('%02d',$b);	
		$cols["$tag"] = "$b";
		
		$datum_a = "$jahr-$monat-$b";
		
		$status = $this->feiertag ($datum_a);
	    		if($status == 'Wochenende'){
	    		$zeichen = "W";
	    		}
	    		if($status != 'Wochenende' && $status !='Arbeitstag'){
	    		$zeichen = "F";
	    		}
	    		if($status =='Arbeitstag'){
	    		$zeichen = "";
	    		}
	    		if(!$this->check_anwesenheit($benutzer_id, $datum_a)){
	    		$zeichen = 'U'; 
	    		}
		
			$geburtstag = $this->check_geburtstag($benutzer_id, $datum_a);
		if($geburtstag){
			$zeichen .= "<b>*G</b>";
		}
		
		$table_arr[$c][MITARBEITER] = "$mitarbeiter</b>";
		$table_arr[$c]["$tag"] = "$zeichen";
		$zeichen = '';
	}	//end for 3
		
}//end for 2
$pdf->ezTable($table_arr,$cols,"Monatsansicht $monatsname $jahr",
array('showHeadings'=>1,'showLines'=>'1', 'shaded'=>1,'shadeCol'=>array(0.78, 0.95,1), 'shadeCol2'=>array(0.1, 0.5, 1), 'titleFontSize' => 10, 'fontSize' => 5, 'xPos'=>50,'xOrientation'=>'right',  'width'=>500,'cols'=>array('MITARBEITER'=>array('justification'=>'right', 'width'=>35))));
} //end for 1
		
		
	
		
		header("Content-type: application/pdf");  // wird von MSIE ignoriert
		
		$pdf->ezStream();
		
		
}






function check_anwesenheit($benutzer_id, $datum){
$result = mysql_query ("SELECT * FROM URLAUB WHERE AKTUELL='1' && BENUTZER_ID='$benutzer_id' && DATUM='$datum'");
	
	$numrows = mysql_numrows($result);
		if(!$numrows){
		
		}else{
			$row = mysql_fetch_assoc($result);
			return $row['ART'];
		}	
}


function check_geburtstag($benutzer_id, $datum){
$datum_arr = explode("-", $datum);
$monat = $datum_arr[1];
$tag = $datum_arr[2];

$result = mysql_query ("SELECT *  FROM BENUTZER WHERE BENUTZER_ID='$benutzer_id' && DATE_FORMAT(GEB_DAT, '%d-%m') ='$tag-$monat' ");
	
	$numrows = mysql_numrows($result);
		if($numrows){
		return true;
		}else{
			return false;
		}	
}



function tage_zwischen($von, $bis){
	$von = strtotime("$von") ;
	$bis = strtotime("$bis") ;
	$differenz = $bis - $von;
	#$differenz = floor($differenz / (3600*24));
	$differenz = floor($differenz / 86400);
	return $differenz;
}





function zinsen($kaution, $zins_pj){
#$kaution = '589.27';
#$zins_pj ='6'; //%

$alt = strtotime("2009-01-01") ;
echo "<br>";
$aktuell = strtotime("2009-12-31") ;
#$aktuell = strtotime(date("Y-m-d")) ;

$differenz = $aktuell - $alt;
$differenz = $differenz / 86400;

echo "$differenz";



$anlege_jahre =1;
$anlege_monate = 0;
$anlege_tage =10;

$kap_prozent = 25;
$soli_prozent = 5.5;

#$gesamt_tage = ($anlege_jahre * 360) + ($anlege_monate * 30) + $anlege_tage;
$gesamt_tage = $differenz;
$berechnungs_monate = $gesamt_tage/30;
$berechnungs_monate_voll = intval($berechnungs_monate);
$rest_tage = $gesamt_tage - ($berechnungs_monate_voll*30);


echo "<b>$berechnungs_monate_voll $rest_tage</b><br>";
#=SUMME(C11*0,005*30)/360+C11



echo "<h1>$gesamt_tage $rest_tage</h1>";

$betrag_vormonat = $kaution;
for($a=1;$a<=$berechnungs_monate_voll;$a++){
	/*=(C11*0,005*30)/360+C11*/
	$betrag_ende_monat = ($betrag_vormonat * $zins_pj*30)  / 360 + $betrag_vormonat ;
	$kap = ($betrag_ende_monat - $betrag_vormonat)*$kap_prozent/$kap_prozent;
	$soli = $kap*$soli_prozent/100;
	
	#$betrag_ende_monat = $betrag_ende_monat + $kap + $soli;
	
	$kap_a = nummer_punkt2komma($kap);
	$soli_a = nummer_punkt2komma($soli); 
	$b_vm = nummer_punkt2komma($betrag_vormonat);
	$b_em = nummer_punkt2komma($betrag_ende_monat);
	#echo "$a. $betrag_vormonat $betrag_ende_monat<br>";
	echo "$a.           $b_vm      $kap_a      $soli_a   $b_em<br>";
	#echo "$a. $betrag_vormonat $betrag_ende_monat<br>";
	$betrag_vormonat = $betrag_ende_monat;
}


if($rest_tage>0){
	
	$betrag_ende_monat = ($betrag_vormonat * $zins_pj*$rest_tage)  / 360 + $betrag_vormonat ;
	$kap = ($betrag_ende_monat - $betrag_vormonat)*$kap_prozent/$kap_prozent;
	$soli = $kap*$soli_prozent/100;
	
	#$betrag_ende_monat = $betrag_ende_monat + $kap + $soli;
	
	$kap_a = nummer_punkt2komma($kap);
	$soli_a = nummer_punkt2komma($soli); 
	$b_vm = nummer_punkt2komma($betrag_vormonat);
	$b_em = nummer_punkt2komma($betrag_ende_monat);
	#echo "$a. $betrag_vormonat $betrag_ende_monat<br>";
	echo "<b> REST $rest_tage tage           $b_vm      $kap_a      $soli_a   $b_em</b><br>";
	#echo "$a. $betrag_vormonat $betrag_ende_monat<br>";
	$betrag_vormonat = $betrag_ende_monat;	
	
}


/*Beispiel: Kaution Euro 1000 bei Mietdauer 1 Jahr und 14 Tagen: 1000 : 100 : 1 Jahr x Zins 3 x 1 Jahr = Euro 30. Folgejahr: 1030 : 100 : 360 x 3 x 14 Tage = 0,40 Euro - Der Mieter erhält mit Zinseszinses vom Vermieter Euro 1.030,40 zurück!*/
}	


}//end class urlaub



?>
