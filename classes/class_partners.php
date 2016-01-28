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
 * @filesource   $HeadURL: http://192.168.2.52/svn/berlussimo_1/tags/02.11.2010 - Downloadversion 0.27/classes/class_partners.php $
 * @version      $Revision: 15 $
 * @modifiedby   $LastChangedBy: sivac $
 * @lastmodified $Date: 2011-07-07 10:41:33 +0200 (Do, 07 Jul 2011) $
 * 
 */
 
include_once("config.inc.php");
/*
 * Klasse partners
 * Diese Klasse wird von /options/modules/partner genutzt
 * Beinhaltet wichtige Funktionen wie Formular, speichern von Partnern
 */

class partners{
	
	/*Name eines Partner/Lieferand/Eigentümer*/
	function get_partner_name($partner_id){
	if(isset($this->partner_name)){
	unset($this->partner_name);	
	}
	$result = mysql_query ("SELECT PARTNER_NAME FROM PARTNER_LIEFERANT WHERE PARTNER_ID='$partner_id' && AKTUELL = '1'");	
	$row = mysql_fetch_assoc($result);
	if($row){
	$this->partner_name = $row['PARTNER_NAME'];
	}else{
	$this->partner_name = '<b>unbekannt</b>';	
	}
	}
	
	
	function suche_partner_in_array($suchtext){
		$result = mysql_query ("SELECT * FROM  `PARTNER_LIEFERANT` WHERE  `AKTUELL` =  '1' AND  `PARTNER_NAME` LIKE  '%$suchtext%'
OR  `STRASSE` LIKE  '%$suchtext%'
OR  `PLZ` LIKE  '%$suchtext%'
OR  `ORT` LIKE  '%$suchtext%'
OR  `LAND` LIKE  '%$suchtext%'
 GROUP BY PARTNER_ID ORDER BY PARTNER_NAME ASC");

	
		$numrows = mysql_numrows($result);
		if($numrows>0){
			while ($row = mysql_fetch_assoc($result)) $my_array[] = $row;
			
			
			/*Zusätzlich Stichwortsuche*/
			$my_array_stich = $this->suche_partner_stichwort_arr($suchtext);
			#echo '<pre>';
			#print_r($my_array);
			#print_r($my_array_stich);
			if(is_array($my_array_stich)){
			$anz_stich = count($my_array_stich);
			for($p=0;$p<$anz_stich;$p++){
				$partner_id = $my_array_stich[$p]['PARTNER_ID'];
				$this->get_partner_info($partner_id);
				#print_r($this);
				#die();
				$anz = count($my_array);
				$my_array[$anz]['PARTNER_ID'] = $partner_id;
				$my_array[$anz]['PARTNER_NAME'] = "<b>$this->partner_name</b>";
				$my_array[$anz]['STRASSE'] = $this->partner_strasse;
				$my_array[$anz]['NUMMER'] = $this->partner_hausnr;
				$my_array[$anz]['PLZ'] = $this->partner_plz;
				$my_array[$anz]['ORT'] = $this->partner_ort;
				$my_array[$anz]['LAND'] = $this->partner_land;
					
				
			}
				
			}
			
			
			return $my_array;
		}else{
			$my_array_stich = $this->suche_partner_stichwort_arr($suchtext);
			if(is_array($my_array_stich)){
				
				$anz_stich = count($my_array_stich);
				for($p=0;$p<$anz_stich;$p++){
					$partner_id = $my_array_stich[$p]['PARTNER_ID'];
					$this->get_partner_info($partner_id);
					#print_r($this);
					#die();
					if(isset($my_array)){
					$anz = count($my_array);
					}else{
						$anz = 0;
					}
					$my_array[$anz]['PARTNER_ID'] = $partner_id;
					$my_array[$anz]['PARTNER_NAME'] = "<b>$this->partner_name</b>";
					$my_array[$anz]['STRASSE'] = $this->partner_strasse;
					$my_array[$anz]['NUMMER'] = $this->partner_hausnr;
					$my_array[$anz]['PLZ'] = $this->partner_plz;
					$my_array[$anz]['ORT'] = $this->partner_ort;
					$my_array[$anz]['LAND'] = $this->partner_land;
						
				
				}
				
				
				return $my_array;
			}else{
			
			return false;
			}
		}
	}
	
	
	
	function stichworte_speichern($partner_id, $arr){
		
		if(is_array($arr)){
			$anz = count($arr);
			$this->stichworte_loeschen($partner_id);
			for($a=0;$a<$anz;$a++){
				$stichwort = $arr[$a];
				
				
				$id = last_id2('PARTNER_STICHWORT', 'ID') +1 ;
				$db_abfrage = "INSERT INTO PARTNER_STICHWORT VALUES (NULL, '$id', '$partner_id', '$stichwort',  '1')";
				$resultat = mysql_query($db_abfrage) or
				die(mysql_error());
								
				/*Protokollieren*/
				#$last_dat = mysql_insert_id();
				#protokollieren('PARTNER_STICHWORT', $last_dat, '0');
						
			}
		}
	}
	
	function stichwort_speichern($partner_id, $stichwort){
	
				$id = last_id2('PARTNER_STICHWORT', 'ID') +1 ;
				$db_abfrage = "INSERT INTO PARTNER_STICHWORT VALUES (NULL, '$id', '$partner_id', '$stichwort',  '1')";
				$resultat = mysql_query($db_abfrage) or
				die(mysql_error());
	
				/*Protokollieren*/
	#			$last_dat = mysql_insert_id();
	#			protokollieren('PARTNER_STICHWORT', $last_dat, '0');
	
			
		
	}
	
	
	
	function stichworte_loeschen($partner_id){
		$db_abfrage = "UPDATE PARTNER_STICHWORT SET AKTUELL='0' WHERE PARTNER_ID='$partner_id'";
		$resultat = mysql_query($db_abfrage) or
		die(mysql_error());
	}
	
	
	
	function partner_liste_filter($partner_arr){
		echo "<table class=\"sortable\">";
		#echo "<tr class=\"feldernamen\"><td width=\"200px\">Name</td><td>Anschrift</td><td>Details</td></tr>";
		echo "<tr><th>Partner</th><th>Anschrift</th><th>GEWERK / Stichwort</th><th>Details</th></tr>";
		$zaehler=0;
		for($a=0;$a<count($partner_arr);$a++){
			$zaehler++;
			$partner_id = $partner_arr[$a]['PARTNER_ID'];
			$partner_name = $partner_arr[$a]['PARTNER_NAME'];
			$partner_link_detail = "<a href=\"?daten=partner&option=partner_im_detail&partner_id=$partner_id\">$partner_name</a>";
			$link_detail_hinzu = "<a href=\"?daten=details&option=details_hinzu&detail_tabelle=PARTNER_LIEFERANT&detail_id=$partner_id\">Details</a>";
			$partner_strasse = $partner_arr[$a]['STRASSE'];
			$partner_nr = $partner_arr[$a]['NUMMER'];
			$partner_plz = $partner_arr[$a]['PLZ'];
			$partner_ort = $partner_arr[$a]['ORT'];
			$anschrift = "$partner_strasse $partner_nr, $partner_plz $partner_ort";
			
			$pp = new partners;
			$stich_arr = $pp->get_partner_stichwort_arr($partner_id);
			
			$link_stich_hinzu = "<a href=\"?daten=partner&option=partner_stichwort&partner_id=$partner_id\"><b>Stichwort eingeben</b></a>";
			
			
			if($zaehler==1){
				echo "<tr valign=\"top\" class=\"zeile1\"><td>$partner_link_detail</td><td>$anschrift</td><td>";
				
				if(is_array($stich_arr)){
					$anz_s = count($stich_arr);
					for($s=0;$s<$anz_s;$s++){
						echo $stich_arr[$s]['STICHWORT'].", ";
					}
				}
					echo $link_stich_hinzu;
				
				echo "</td><td>$link_detail_hinzu</td></tr>";
		}
		if($zaehler==2){
			echo "<tr valign=\"top\" class=\"zeile2\"><td>$partner_link_detail</td><td>$anschrift</td><td>";
			if(is_array($stich_arr)){
				$anz_s = count($stich_arr);
				for($s=0;$s<$anz_s;$s++){
					echo $stich_arr[$s]['STICHWORT'].", ";
				}
			}
				echo $link_stich_hinzu;
			
			echo "</td><td>$link_detail_hinzu</td></tr>";
			$zaehler=0;
		}
	}
	echo "</table><br>\n";
	}
	
	
	/*Name eines Partner/Lieferand/Eigentümer*/
	function get_partner_id($partner_name){
	#echo "$result = mysql_query (\"SELECT PARTNER_ID FROM PARTNER_LIEFERANT WHERE PARTNER_NAME='$partner_name' && AKTUELL = '1\");	";
	$result = mysql_query ("SELECT PARTNER_ID FROM PARTNER_LIEFERANT WHERE PARTNER_NAME='$partner_name' && AKTUELL = '1'");
	$row = mysql_fetch_assoc($result);
	$this->partner_id = $row['PARTNER_ID'];
	}
	
	
	
	function form_partner_stichwort($partner_id){
		$this->get_partner_info($partner_id);
		
		$f = new formular;
		$f->erstelle_formular("Partner $this->partner_name Gewerke oder Stichwort eingeben", NULL);
		
		$stich_arr = $this->get_stichwort_arr();
		if(is_array($stich_arr)){
			$anz = count($stich_arr);
			for($a=0;$a<$anz;$a++){
			$stich = $stich_arr[$a]['STICHWORT'];
				if($this->check_stichwort($partner_id, $stich)==false){
				$f->check_box_js('stichworte[]', $stich, $stich, '', '');
				}else{
				$f->check_box_js('stichworte[]', $stich, $stich, '', 'checked');
				}	
			
			}
			#echo '<pre>';
			#print_r($stich_arr);
			
		}
		
		$f->hidden_feld("partner_id", "$partner_id");
		$f->hidden_feld("option", "partner_stich_sent");
		$f->send_button("submit", "Stichworte aktualisieren");
		$f->ende_formular();
	}
	
	
	function form_partner_stichwort_neu($partner_id){
		$this->get_partner_info($partner_id);
	
		$f = new formular;
		$f->erstelle_formular("Neues Gewerk / Stichwort eingeben", NULL);
		$f->text_feld("Stichwort", "stichwort","", "30", 'stichwort_neu', '');
		$f->hidden_feld("partner_id", "$partner_id");
		$f->hidden_feld("option", "partner_stich_sent_neu");
		$f->send_button("submit", "Stichwort hinzufügen");
		$f->ende_formular();
		}
	
	
	function check_stichwort($partner_id, $stichwort){
		$result = mysql_query ("SELECT STICHWORT FROM  `PARTNER_STICHWORT` WHERE  `AKTUELL` =  '1'  && PARTNER_ID='$partner_id' && STICHWORT='$stichwort' LIMIT 0,1");
		
		
		$numrows = mysql_numrows($result);
		if($numrows>0){
		return true;
		}else{
				return false;
		}
	}
	
	
	/*Grundinformationen über einen Partner/Lieferand/Eigentümer*/
	function get_partner_info($partner_id){
	$result = mysql_query ("SELECT *  FROM PARTNER_LIEFERANT WHERE PARTNER_ID='$partner_id' && AKTUELL = '1'");	
	$row = mysql_fetch_assoc($result);
	if($row){
	$this->partner_dat = $row['PARTNER_DAT'];
	$this->partner_name = $row['PARTNER_NAME'];
	$this->partner_strasse = $row['STRASSE'];
	$this->partner_hausnr = $row['NUMMER'];
	$this->partner_plz = $row['PLZ'];
	$this->partner_ort = $row['ORT'];
	$this->partner_land = $row['LAND'];
	
	}
	
	}
		
	/*Partner erfassen Formular*/
	function form_partner_erfassen(){
	$form = new mietkonto;
    $form->erstelle_formular("Partner erfassen", NULL);
    #$form->text_feld("Partnername:", "partnername", "", "10");
   # $js = "onkeyup=\"alert('SANEL');\"";
    $js = "onkeyup=\"daj3('ajax/ajax_info.php?option=finde_partner&suchstring='+this.value, 'p_fund');\"";
    
    $f = new formular();
    
    #$f->text_feld_inaktiv('Partner gefunden', 'p_fund', '', 70, 'p_fund');
    #echo "<div id=\"p_fund\" style=\"color=#ff0000;\"></div>";#
    $f->text_bereich_js('Partnername', 'partnername', '', '20', '3', 'partner_name', $js);
    echo "<div id=\"p_fund\" style=\"color:#ff0000;border:3px;border-color=#ff0000;\"></div>";#
    #$f->text_bereich_js("Partnername", "partnername", '', "20", "3", $js);
    $form->text_feld("Strasse:", "strasse",'', "50");
    $form->text_feld("Hausnummer:", "hausnummer", '', "10");
    $form->text_feld("Postleitzahl:", "plz", '', "10");
    $form->text_feld("Ort:", "ort", '', "25");
    $form->text_feld("Land:", "land", '', "25");
    #$form->text_feld("Kreditinstitut:", "kreditinstitut", "", "10");
    #$form->text_feld("Kontonummer:", "kontonummer", "", "10");
    #$form->text_feld("Bankleitzahl:", "blz", "", "10");
    $form->text_feld("Telefon:", "tel", "", "25");
    $form->text_feld("Fax:", "fax", "", "25");
    $form->text_feld("Email:", "email", "", "30");
    $form->send_button("submit_partner", "Partner speichern");
    $form->hidden_feld("option", "partner_gesendet");
    $form->ende_formular();
	
	}
	
	
	
	/*Partner suchen Formular*/
	function form_such_partner(){
		$form = new mietkonto;
		$form->erstelle_formular("Partner suchen", NULL);
		$form->text_feld("Suchtext:", "suchtext", "", "50");
		$form->send_button("sBtN_such", "Partner suchen");
		$form->hidden_feld("option", "partner_suchen1");
		$form->ende_formular();
	}
	
	
	/*Partner in Datenbank speichern*/
	function partner_speichern($clean_arr){
	foreach ($clean_arr as $key => $value) {	
		$partnername = $clean_arr[partnername];
		$str = $clean_arr[strasse];
		$hausnr = $clean_arr[hausnummer];
		$plz = $clean_arr[plz];
		$ort = $clean_arr[ort];
		$land = $clean_arr[land];
		$tel = $clean_arr['tel'];
		$fax = $clean_arr['fax'];
		$email = $clean_arr['email'];
		#$kreditinstitut = $clean_arr[kreditinstitut];
		#$kontonummer = $clean_arr[kontonummer];
		#$blz = $clean_arr[blz];
		
		#print_r($clean_arr);
		if(empty($partnername) OR empty($str) OR empty($hausnr)OR empty($plz) OR empty($ort) OR empty($land)){
		fehlermeldung_ausgeben("Dateneingabe unvollständig!!!<br>Sie werden weitergeleitet.");
		$_SESSION[partnername] = $partnername;
		$_SESSION[strasse] = $str;
		$_SESSION[hausnummer] = $hausnr;
		$_SESSION[plz] = $plz;
		$_SESSION[ort] = $ort;
		$_SESSION[land] = $land;
		
		#$_SESSION[kreditinstitut] = $kreditinstitut;
		#$_SESSION[kontonummer] = $kontonummer;
		#$_SESSION[blz] = $blz;
		
		$fehler = true;
		weiterleiten_in_sec("?daten=partner&option=partner_erfassen", 3);
		die();
		}
		}//Ende foreach
		
/*Prüfen ob Partner/Liefernat vorhanden*/
		$result_3 = mysql_query ("SELECT * FROM PARTNER_LIEFERANT WHERE PARTNER_NAME = '$clean_arr[partnername]' && STRASSE='$clean_arr[strasse]' && NUMMER='$clean_arr[hausnummer]' && PLZ='$clean_arr[plz]' && AKTUELL = '1' ORDER BY PARTNER_NAME");	
$numrows_3 = mysql_numrows($result_3);

/*Wenn kein Fehler durch eingabe oder partner in db nicht vorhanden wird neuer datensatz gespeichert*/

		if(!$fehler && $numrows_3<1){
/*Partnerdaten ohne Kontoverbindung*/
$partner_id = $this->letzte_partner_id();
$partner_id = $partner_id + 1;
$db_abfrage = "INSERT INTO PARTNER_LIEFERANT VALUES (NULL, $partner_id, '$clean_arr[partnername]','$clean_arr[strasse]', '$clean_arr[hausnummer]','$clean_arr[plz]','$clean_arr[ort]','$clean_arr[land]','1')";
$resultat = mysql_query($db_abfrage) or
           die(mysql_error());
/*Protokollieren*/
		$last_dat = mysql_insert_id();
		protokollieren('PARTNER_LIEFERANT', $last_dat, '0');

/*if(!empty($kreditinstitut) OR !empty($kontonummer) OR !empty($blz)){
/*Kontodaten speichern*/
/*$konto_id= $this->letzte_geldkonto_id();
$konto_id = $konto_id + 1;
$db_abfrage = "INSERT INTO GELD_KONTEN VALUES (NULL, '$konto_id','$clean_arr[partnername] - Konto','$clean_arr[partnername]', '$clean_arr[kontonummer]','$clean_arr[blz]', '$clean_arr[kreditinstitut]','1')";
$resultat = mysql_query($db_abfrage) or
           die(mysql_error());	*/           
/*Protokollieren*/
	/*	$last_dat = mysql_insert_id();
		protokollieren('GELD_KONTEN', $last_dat, '0');*/
/*Geldkonto dem Partner zuweisen*/
/*$letzte_zuweisung_id = $this->letzte_zuweisung_geldkonto_id();
$letzte_zuweisung_id = $letzte_zuweisung_id +1;
$db_abfrage = "INSERT INTO GELD_KONTEN_ZUWEISUNG VALUES (NULL, '$letzte_zuweisung_id','$konto_id', 'Partner','$partner_id', '1')";
$resultat = mysql_query($db_abfrage) or
           die(mysql_error());	
} */  		
		if(isset($resultat)){
			hinweis_ausgeben("Partner $clean_arr[partnername] wurde gespeichert.");
			weiterleiten_in_sec("?daten=partner&option=partner_erfassen", 2);
			}
		}//ende fehler
		if($numrows_3>0){
		fehlermeldung_ausgeben("Partner $clean_arr[partnername] exisitiert bereits.");
		weiterleiten_in_sec("?daten=partner&option=partner_erfassen", 2);
		}
		unset($_SESSION[partnername]);
		unset($_SESSION[strasse]);
		unset($_SESSION[hausnummer]);
		unset($_SESSION[plz]);
		unset($_SESSION[ort]);
		unset($_SESSION[land]);
		#unset($_SESSION[kreditinstitut]);
		#unset($_SESSION[kontonummer]);
		#unset($_SESSION[blz]);
		
		$dd = new detail();
		if(!empty($tel)){
		$dd->detail_speichern_2('PARTNER_LIEFERANT', $partner_id, 'Telefon', $tel, $_SESSION['username']." ".date("d.m.Y H:i:s"));
		}		
		if(!empty($fax)){
			$dd->detail_speichern_2('PARTNER_LIEFERANT', $partner_id, 'Fax', $fax, $_SESSION['username']." ".date("d.m.Y H:i:s"));
		}
		if(!empty($email)){
			$dd->detail_speichern_2('PARTNER_LIEFERANT', $partner_id, 'Email', $email, $_SESSION['username']." ".date("d.m.Y H:i:s"));
		}
		
		}//Ende funktion

/*Letzte Partner ID*/
function letzte_partner_id(){
$result = mysql_query ("SELECT PARTNER_ID FROM PARTNER_LIEFERANT  ORDER BY PARTNER_ID DESC LIMIT 0,1");
		$row = mysql_fetch_assoc($result);
		return $row['PARTNER_ID'];		
}
/*Letzte Partnergeldkonto ID*/
function letzte_geldkonto_id(){
$result = mysql_query ("SELECT KONTO_ID FROM GELD_KONTEN ORDER BY KONTO_ID DESC LIMIT 0,1");
		$row = mysql_fetch_assoc($result);
		return $row['KONTO_ID'];		
}

/*Letzte Zuweisunggeldkonto ID*/
function letzte_zuweisung_geldkonto_id(){
$result = mysql_query ("SELECT ZUWEISUNG_ID FROM GELD_KONTEN_ZUWEISUNG ORDER BY ZUWEISUNG_ID DESC LIMIT 0,1");
		$row = mysql_fetch_assoc($result);
		return $row['ZUWEISUNG_ID'];		
}


/*Letzte Parner Zuweisunggeldkonto ID*/
function letzte_konto_geldkonto_id_p($partner_id){
$result = mysql_query ("SELECT KONTO_ID FROM GELD_KONTEN_ZUWEISUNG WHERE KOSTENTRAEGER_TYP='Partner' && KOSTENTRAEGER_ID='$partner_id' ORDER BY ZUWEISUNG_ID DESC LIMIT 0,1");
		$row = mysql_fetch_assoc($result);
		return $row['KONTO_ID'];		
}

/*Anzeige der Partnerliste rechts senkrecht*/
function partner_rechts_anzeigen(){
$result = mysql_query ("SELECT * FROM PARTNER_LIEFERANT WHERE AKTUELL = '1' ORDER BY PARTNER_NAME");	
$numrows = mysql_numrows($result);
	if($numrows>0){
	$form = new mietkonto;
	$form->erstelle_formular("Partner", NULL);
	echo "<div class=\"tabelle\">\n";
	while ($row = mysql_fetch_assoc($result)) $my_array[] = $row;
	echo "<table class=\"sortable\">\n";
	echo "<tr class=\"feldernamen\"><td>Partner</td></tr>\n";
	echo "<tr><th>Partner</th></tr>";
		for($i=0;$i<count($my_array);$i++){
		echo "<tr><td>".$my_array[$i][PARTNER_NAME]."</td></tr>\n";
		}		
	echo "</table></div>\n";
	$form->ende_formular();
}else{
	echo "Keine Partner";
}
}
/*Alle Partner in ein array laden*/
function partner_in_array(){
$result = mysql_query ("SELECT * FROM PARTNER_LIEFERANT WHERE AKTUELL = '1' ORDER BY PARTNER_NAME ASC");	
$numrows = mysql_numrows($result);
	if($numrows>0){
	while ($row = mysql_fetch_assoc($result)) $my_array[] = $row;
	return $my_array;
	}else{
		return false;
	}
}
/*Dropdownfeld mit Partnern/Lieferanten/Eigentümern*/
function partner_dropdown($label, $name, $id, $vorwahl=null){
$partner_arr = $this->partner_in_array();
echo "<label for=\"$id\">$label</label><select name=\"$name\" size=\"1\" id=\"$id\">";
	for($a=0;$a<count($partner_arr);$a++){
	$partner_id = $partner_arr[$a][PARTNER_ID];
	$partner_name = $partner_arr[$a][PARTNER_NAME];
	if($vorwahl==$partner_id){
	echo "<option value=\"$partner_id\" selected>$partner_name</OPTION>\n";
	}else{
	echo "<option value=\"$partner_id\">$partner_name</OPTION>\n";	
	}
	}
	echo "</select><br>\n";
}

/*Alle Gewerke in ein array laden*/
function gewerke_in_array(){
$result = mysql_query ("SELECT * FROM GEWERKE WHERE AKTUELL = '1' ORDER BY BEZEICHNUNG ASC");	
$numrows = mysql_numrows($result);
	if($numrows>0){
	while ($row = mysql_fetch_assoc($result)) $my_array[] = $row;
	return $my_array;
	}else{
		return false;
	}
}


/*Dropdownfeld mit Gewerken*/
function gewerke_dropdown($label, $name, $id, $vorwahl=null){
$gewerk_arr = $this->gewerke_in_array();
echo "<label for=\"$id\">$label</label><select name=\"$name\" size=\"1\" id=\"$id\">";
	for($a=0;$a<count($gewerk_arr);$a++){
	$gewerk_id = $gewerk_arr[$a]['G_ID'];
	$bezeichnung = $gewerk_arr[$a]['BEZEICHNUNG'];
		if($vorwahl==$gewerk_id){
		echo "<option value=\"$gewerk_id\" selected>$bezeichnung</OPTION>\n";
		}else{
			echo "<option value=\"$gewerk_id\">$bezeichnung</OPTION>\n";
		}
	}
	echo "</select><br>\n";
}


function partner_liste(){
$partner_arr = $this->partner_in_array();
echo "<table class=\"sortable\">";
#echo "<tr class=\"feldernamen\"><td width=\"200px\">Name</td><td>Anschrift</td><td>Details</td></tr>";
echo "<tr><th>Partner</th><th>Anschrift</th><th>Gewerk / Stichwort</th><th>Details</th></tr>";
	$zaehler=0;
	for($a=0;$a<count($partner_arr);$a++){
	$zaehler++;
	$partner_id = $partner_arr[$a]['PARTNER_ID'];
	$partner_name = $partner_arr[$a]['PARTNER_NAME'];
	$partner_link_detail = "<a href=\"?daten=partner&option=partner_im_detail&partner_id=$partner_id\">$partner_name</a>";
	$link_detail_hinzu = "<a href=\"?daten=details&option=details_hinzu&detail_tabelle=PARTNER_LIEFERANT&detail_id=$partner_id\">Details</a>";
	$link_aendern = "<a href=\"?daten=partner&option=partner_aendern&partner_id=$partner_id\">Ändern</a>";
	$partner_strasse = $partner_arr[$a]['STRASSE'];
	$partner_nr = $partner_arr[$a]['NUMMER'];
	$partner_plz = $partner_arr[$a]['PLZ'];
	$partner_ort = $partner_arr[$a]['ORT'];
	$anschrift = "$partner_strasse $partner_nr, $partner_plz $partner_ort";
	
	echo "<tr valign=\"top\" class=\"zeile$zaehler\"><td>$partner_link_detail</td><td>$anschrift</td><td>";
	$pp = new partners;
	$stich_arr = $pp->get_partner_stichwort_arr($partner_id);
		
	$link_stich_hinzu = "<a href=\"?daten=partner&option=partner_stichwort&partner_id=$partner_id\"><b>Stichwort eingeben</b></a>";
		
		
		if(is_array($stich_arr)){
		$anz_s = count($stich_arr);
		for($s=0;$s<$anz_s;$s++){
			echo $stich_arr[$s]['STICHWORT'].", ";
		}
		
		}
		echo $link_stich_hinzu;
	echo "</td><td>$link_detail_hinzu $link_aendern</td></tr>";
	
	if($zaehler==2){
	$zaehler=0;
	}
	}
	echo "</table><br>\n";
}


function get_partner_stichwort_arr($partner_id){
		$result = mysql_query ("SELECT * FROM  `PARTNER_STICHWORT` WHERE  `AKTUELL` =  '1' AND  `PARTNER_ID` =  '$partner_id'
				 ORDER BY STICHWORT ASC");
	
	
		$numrows = mysql_numrows($result);
		if($numrows>0){
			while ($row = mysql_fetch_assoc($result)) $my_array[] = $row;
			return $my_array;
		}else{
			return false;
		}
	
}


function get_stichwort_arr(){
	$result = mysql_query ("SELECT STICHWORT FROM  `PARTNER_STICHWORT` WHERE  `AKTUELL` =  '1'  GROUP BY STICHWORT	ORDER BY STICHWORT ASC");


			$numrows = mysql_numrows($result);
			if($numrows>0){
			while ($row = mysql_fetch_assoc($result)) $my_array[] = $row;
			return $my_array;
			}else{
			return false;
			}

}







function suche_partner_stichwort_arr($stichwort){
	$result = mysql_query ("SELECT * FROM  `PARTNER_STICHWORT` WHERE  `AKTUELL` =  '1' AND  `STICHWORT` LIKE  '%$stichwort%'
			ORDER BY STICHWORT ASC");


			$numrows = mysql_numrows($result);
			if($numrows>0){
			while ($row = mysql_fetch_assoc($result)) $my_array[] = $row;
			return $my_array;
			}else{
			return false;
			}

}

function form_partner_aendern($partner_id){
	$this->get_partner_info($partner_id);
	if($this->partner_name){
	$f = new formular;
	$f->erstelle_formular("Partner $this->partner_name ändern", NULL);
  	$f->text_bereich("Partnername", "partnername", $this->partner_name, "20", "3", 'partnername');
    $f->text_feld("Strasse:", "strasse",$this->partner_strasse, "30", 'strasse', '');
    $f->text_feld("Nummer:", "hausnummer",$this->partner_hausnr, "10", 'hausnummer', '');
    $f->text_feld("PLZ:", "plz",$this->partner_plz, "10", 'plz', '');
    $f->text_feld("Ort:", "ort",$this->partner_ort, "30", 'ort', '');
    $f->text_feld("Land:", "land",$this->partner_land, "30", 'land', '');
    #$f->text_feld("Kreditinstitut:", "kreditinstitut", "", "10");
    #$f->text_feld("Kontonummer:", "kontonummer", "", "10");
    #$f->text_feld("Bankleitzahl:", "blz", "", "10");
    $f->hidden_feld("partner_dat", "$this->partner_dat");
    $f->hidden_feld("partner_id", "$partner_id"); 
    $f->hidden_feld("option", "partner_aendern_send");
	$f->send_button("submit", "Änderung speichern");
	$f->ende_formular();
	}else{
		die("Partner $partner_id unbekannt");
	}
}

function partner_aendern($partner_dat,$partner_id, $partnername,$strasse,$hausnummer,$plz,$ort,$land){
	/*Deaktivieren*/
	$db_abfrage ="UPDATE PARTNER_LIEFERANT SET AKTUELL='0' WHERE PARTNER_DAT='$partner_dat'";
	$resultat = mysql_query($db_abfrage) or
           die(mysql_error());
	
	/*Änderung Speichern*/
	$db_abfrage ="INSERT INTO PARTNER_LIEFERANT VALUES(NULL, '$partner_id', '$partnername', '$strasse', '$hausnummer', '$plz', '$ort', '$land', '1')";
	$resultat = mysql_query($db_abfrage) or
           die(mysql_error());
	
	/*Protokollieren*/
	$last_dat = mysql_insert_id();
	protokollieren('PARTNER_LIEFERANT', $last_dat, $partner_dat);
	
	
}


function partner_nach_umsatz(){
$result = mysql_query ("SELECT  `AUSSTELLER_TYP` , AUSSTELLER_ID, SUM( NETTO ) AS NETTO, SUM( BRUTTO ) AS BRUTTO
FROM  `RECHNUNGEN` 
WHERE  `RECHNUNGSTYP` =  'RECHNUNG'
GROUP BY  `AUSSTELLER_TYP` ,  `AUSSTELLER_ID` 
ORDER BY SUM( BRUTTO ) DESC 
LIMIT 0 , 80");	
$numrows = mysql_numrows($result);
	if($numrows>0){
	$z=0;
		while ($row = mysql_fetch_assoc($result)){
		$this->get_partner_name($row['AUSSTELLER_ID']);
		$row['PARTNER_NAME'] = $this->partner_name;	
		$my_array[] = $row;
		
		}
	
	return $my_array;
	}else{
		return false;
	}	
}



function form_partner_serienbrief(){
	$partner_arr = $this->partner_in_array();
	if(!is_array($partner_arr)){
		die(fehlermeldung_ausgeben("Keine Partner gefunden!"));
	}
	
	$f = new formular();
	$f->erstelle_formular('Serienbrief an Partner', null);
	$js = "onclick=\"activate(this.form.elements['p_ids[]']);\"";
	$f->check_box_js_alle('c_alle', 'c_alle', 1, 'Alle', '', '', 'p_ids');
	$f->send_button('Button', 'Vorlage wählen');
	$f->send_button("delete", "Alle Löschen");
	
	$anz_p = count($partner_arr);
	for($a=0;$a<$anz_p;$a++){
		$p_id = $partner_arr[$a]['PARTNER_ID'];
		$p_name =  $partner_arr[$a]['PARTNER_NAME'];
		#p($this);
		#echo '<hr>';
		#print_r($_SESSION['eig_ids']);
	
		if(isset($_SESSION['p_ids']) && in_array($p_id, $_SESSION['p_ids'])){
			$f->check_box_js1('p_ids[]', 'p_ids', $p_id, "$p_name", '', 'checked');
		}else{
			$f->check_box_js1('p_ids[]', 'p_ids', $p_id, "$p_name", '', '');
		}
	
	}
	$f->hidden_feld('option', 'serien_brief_vorlagenwahl');
	$f->send_button('Button', 'Vorlage wählen');
	$f->ende_formular();
	
}

}//Ende Klasse Partner

?>
