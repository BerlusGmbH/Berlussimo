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
 * @filesource   $HeadURL: http://192.168.2.52/svn/berlussimo_1/tags/02.11.2010 - Downloadversion 0.27/classes/class_wirtschafts_e.php $
 * @version      $Revision: 15 $
 * @modifiedby   $LastChangedBy: sivac $
 * @lastmodified $Date: 2011-07-07 10:41:33 +0200 (Do, 07 Jul 2011) $
 * 
 */

class wirt_e{

function get_wirt_e_arr(){
$db_abfrage="SELECT W_ID, W_NAME FROM `WIRT_EINHEITEN` WHERE `AKTUELL`='1' ORDER BY W_NAME ASC";
$result = mysql_query($db_abfrage) or
           die(mysql_error());	
$numrows = mysql_numrows($result);
	if($numrows){
	while ($row = mysql_fetch_assoc($result)) $my_arr[] = $row;
	return $my_arr;
	}
}

function check_einheit_in_we($einheit_id, $w_id){
#$db_abfrage ="SELECT EINHEIT.*, HAUS.* FROM `WIRT_EINHEITEN` JOIN WIRT_EIN_TAB ON (WIRT_EINHEITEN.W_ID=WIRT_EIN_TAB.W_ID) JOIN EINHEIT ON (EINHEIT.EINHEIT_ID=WIRT_EIN_TAB.EINHEIT_ID) JOIN HAUS ON (HAUS.HAUS_ID=EINHEIT.HAUS_ID) WHERE  WIRT_EINHEITEN.AKTUELL='1' && WIRT_EIN_TAB.AKTUELL='1' && WIRT_EINHEITEN.W_ID='$w_id' && EINHEIT.EINHEIT_AKTUELL='1' && HAUS_AKTUELL='1' && EINHEIT.EINHEIT_ID='$einheit_id'";	
$db_abfrage ="SELECT * FROM `WIRT_EIN_TAB` WHERE `W_ID` ='$w_id' AND `EINHEIT_ID` ='$einheit_id' AND `AKTUELL` = '1' LIMIT 0 , 1";
$result = mysql_query($db_abfrage) or
           die(mysql_error());	
$numrows = mysql_numrows($result);
	if($numrows){
		return true;
	}
}


function get_einheiten_from_wirte($w_id){
$db_abfrage ="SELECT EINHEIT.*, HAUS.* FROM `WIRT_EINHEITEN` JOIN WIRT_EIN_TAB ON (WIRT_EINHEITEN.W_ID=WIRT_EIN_TAB.W_ID) JOIN EINHEIT ON (EINHEIT.EINHEIT_ID=WIRT_EIN_TAB.EINHEIT_ID) JOIN HAUS ON (HAUS.HAUS_ID=EINHEIT.HAUS_ID) WHERE  WIRT_EINHEITEN.AKTUELL='1' && WIRT_EIN_TAB.AKTUELL='1' && WIRT_EINHEITEN.W_ID='$w_id' && EINHEIT.EINHEIT_AKTUELL='1' && HAUS_AKTUELL='1' ORDER BY LENGTH(EINHEIT.EINHEIT_KURZNAME) ASC, EINHEIT.EINHEIT_KURZNAME ASC";	
$result = mysql_query($db_abfrage) or
           die(mysql_error());	
$numrows = mysql_numrows($result);
	if($numrows){
	while ($row = mysql_fetch_assoc($result)) $my_arr[] = $row;
	return $my_arr;
	}
}


function get_anzahl_einheiten_from_wirte($w_id,$typ){
$db_abfrage ="SELECT * FROM `WIRT_EINHEITEN` JOIN WIRT_EIN_TAB ON (WIRT_EINHEITEN.W_ID=WIRT_EIN_TAB.W_ID) JOIN EINHEIT ON (EINHEIT.EINHEIT_ID=WIRT_EIN_TAB.EINHEIT_ID) JOIN HAUS ON (HAUS.HAUS_ID=EINHEIT.HAUS_ID) WHERE  WIRT_EINHEITEN.AKTUELL='1' && WIRT_EIN_TAB.AKTUELL='1' && WIRT_EINHEITEN.W_ID='$w_id' && EINHEIT.EINHEIT_AKTUELL='1' && HAUS_AKTUELL='1' && EINHEIT.TYP='$typ' ORDER BY EINHEIT.EINHEIT_KURZNAME ASC";	
$result = mysql_query($db_abfrage) or
           die(mysql_error());	
$numrows = mysql_numrows($result);
	if($numrows){
	return $numrows;
	}else{
		return 0.00;
	}
}


function get_qm_from_wirte($w_id){
$db_abfrage ="SELECT SUM(EINHEIT_QM) AS G_QM  FROM `WIRT_EINHEITEN` JOIN WIRT_EIN_TAB ON (WIRT_EINHEITEN.W_ID=WIRT_EIN_TAB.W_ID) JOIN EINHEIT ON (EINHEIT.EINHEIT_ID=WIRT_EIN_TAB.EINHEIT_ID) JOIN HAUS ON (HAUS.HAUS_ID=EINHEIT.HAUS_ID) WHERE  WIRT_EINHEITEN.AKTUELL='1' && WIRT_EIN_TAB.AKTUELL='1' && WIRT_EINHEITEN.W_ID='$w_id' && EINHEIT.EINHEIT_AKTUELL='1' && HAUS_AKTUELL='1' ";	
$result = mysql_query($db_abfrage) or
           die(mysql_error());	
$numrows = mysql_numrows($result);
	if($numrows){
	$row = mysql_fetch_assoc($result);
	return $row['G_QM'];
	}
}



function get_id_from_wirte($w_name){
$db_abfrage ="SELECT W_ID  FROM `WIRT_EINHEITEN` WHERE  WIRT_EINHEITEN.AKTUELL='1' &&  WIRT_EINHEITEN.W_NAME='$w_name' ORDER BY W_DAT DESC LIMIT 0,1 ";	
$result = mysql_query($db_abfrage) or
           die(mysql_error());	
$numrows = mysql_numrows($result);
	if($numrows){
	$row = mysql_fetch_assoc($result);
	return $row['W_ID'];
	}
}

function get_qm_gewerb_from_wirte($w_id){
$db_abfrage ="SELECT SUM(EINHEIT_QM) AS G_QM  FROM `WIRT_EINHEITEN` JOIN WIRT_EIN_TAB ON (WIRT_EINHEITEN.W_ID=WIRT_EIN_TAB.W_ID) JOIN EINHEIT ON (EINHEIT.EINHEIT_ID=WIRT_EIN_TAB.EINHEIT_ID) JOIN HAUS ON (HAUS.HAUS_ID=EINHEIT.HAUS_ID) WHERE  WIRT_EINHEITEN.AKTUELL='1' && EINHEIT.TYP='Gewerbe' && WIRT_EIN_TAB.AKTUELL='1' && WIRT_EINHEITEN.W_ID='$w_id' && EINHEIT.EINHEIT_AKTUELL='1' && HAUS_AKTUELL='1'";	
$result = mysql_query($db_abfrage) or
           die(mysql_error());	
$numrows = mysql_numrows($result);
	if($numrows){
	$row = mysql_fetch_assoc($result);
	#return nummer_komma2punkt($row['G_QM']);
	return $row['G_QM'];
	}
}


function dropdown_we($label, $name, $id, $js_action, $vorwahl=null){
	echo "<label for=\"$id\">$label</label><select name=\"$name\" id=\"$id\" size=1 $js_action>\n";
	echo "<option value=\"\">Bitte wählen</option>\n";
	
	$wirt_e_arr =$this->get_wirt_e_arr();
	$anzahl = count($wirt_e_arr);
	for($a=0;$a<$anzahl;$a++){
	$w_id = $wirt_e_arr[$a]['W_ID'];
	$w_name = $wirt_e_arr[$a]['W_NAME'];
	if($vorwahl==null){
	echo "<option value=\"$w_id\">$w_name</option>\n";
	}else{
		if($vorwahl==$w_id){
			echo "<option value=\"$w_id\" selected>$w_name</option>\n";
		}else{
			echo "<option value=\"$w_id\">$w_name</option>\n";
		}
	}
	}

	echo "</select>\n";
}



function get_wirt_e_infos($w_id){
$this->w_name = '';
$this->g_qm = '0.00';
$this->g_qm_gewerbe = '0.00';
$db_abfrage="SELECT  W_NAME FROM `WIRT_EINHEITEN`  WHERE  WIRT_EINHEITEN.AKTUELL='1' && WIRT_EINHEITEN.W_ID='$w_id' LIMIT 0,1";
$result = mysql_query($db_abfrage) or
           die(mysql_error());	
$numrows = mysql_numrows($result);
	if($numrows){
	$row = mysql_fetch_assoc($result);
	$this->w_name = $row['W_NAME'];
	$this->g_qm = $this->get_qm_from_wirte($w_id);
	$this->g_qm_gewerbe = $this->get_qm_gewerb_from_wirte($w_id);
	#echo  "SSSS $w_id $this->g_qm_gewerbe";
	$this->anzahl_e = $this->get_anzahl_e($w_id);
	$this->anzahl_ge = $this->get_anzahl_einheiten_from_wirte($w_id,'Gewerbe');
	$this->anzahl_wo = $this->anzahl_e - $this->anzahl_ge; 
	
	/*NEU aus class  WEG Function ->key_daten_formel*/
	$d = new detail;
	#echo "$anteile_g = $d->finde_detail_inhalt('WIRT_EINHEITEN', $w_id, 'Gesamtanteile')";
	#die();
	$anteile_g = $d->finde_detail_inhalt('WIRT_EINHEITEN', $w_id, 'Gesamtanteile');
	if(empty($anteile_g)){
		$anteile_g = 0.00;
	}
	$this->g_mea = $anteile_g;
	$this->g_einheit_qm = $this->g_qm;
	$this->g_anzahl_einheiten = $this->anzahl_e;
	$this->g_verbrauch = '0.00';
	}	
#echo '<pre>';
#	print_r($this);
#	die();
}


function get_wirt_e_infos_ALT($w_id){
$this->w_name = '';
$this->g_qm = '0.00';
$this->g_qm_gewerbe = '0.00';
$db_abfrage="SELECT  W_NAME FROM `WIRT_EINHEITEN` JOIN WIRT_EIN_TAB ON (WIRT_EINHEITEN.W_ID=WIRT_EIN_TAB.W_ID) JOIN EINHEIT ON (EINHEIT.EINHEIT_ID=WIRT_EIN_TAB.EINHEIT_ID) JOIN HAUS ON (HAUS.HAUS_ID=EINHEIT.HAUS_ID) WHERE  WIRT_EINHEITEN.AKTUELL='1' && WIRT_EIN_TAB.AKTUELL='1' && WIRT_EINHEITEN.W_ID='$w_id' && EINHEIT.EINHEIT_AKTUELL='1' && HAUS_AKTUELL='1'";
$result = mysql_query($db_abfrage) or
           die(mysql_error());	
$numrows = mysql_numrows($result);
	if($numrows){
	$row = mysql_fetch_assoc($result);
	$this->w_name = $row['W_NAME'];
	$this->g_qm = $this->get_qm_from_wirte($w_id);
	$this->g_qm_gewerbe = $this->get_qm_gewerb_from_wirte($w_id);
	$this->anzahl_e = $this->get_anzahl_e($w_id);
	$this->anzahl_ge = $this->get_anzahl_einheiten_from_wirte($w_id,'Gewerbe');
	$this->anzahl_wo = $this->anzahl_e - $this->anzahl_ge; 
	
	}	

}


function get_anzahl_e($w_id){
$db_abfrage="SELECT  WZ_DAT FROM WIRT_EIN_TAB WHERE AKTUELL='1' && W_ID='$w_id'";
$result = mysql_query($db_abfrage) or
           die(mysql_error());	
$numrows = mysql_numrows($result);
return $numrows;	
}

function form_new_we(){
	$f = new formular;
	$f->erstelle_formular("Neue Wirtschaftseinheit erstellen", NULL);
    $f->text_feld("Name der Wirtschafseinheit", "w_name", "", "50", 'w_name','');  
	$f->hidden_feld("option", "new_we");
	$f->send_button("submit_we", "Erstellen");
	$f->ende_formular();
}


function neue_we_speichern($w_name){
$bk = new bk;
$last_w_id = $bk->last_id('WIRT_EINHEITEN', 'W_ID') +1;

$db_abfrage = "INSERT INTO WIRT_EINHEITEN VALUES (NULL, '$last_w_id', '$w_name',  '1')";
$resultat = mysql_query($db_abfrage) or
           die(mysql_error());
/*Protokollieren*/
$last_dat = mysql_insert_id();
#protokollieren('BK_PROFILE', $last_dat, '0');
#$this->last_bk_id = $last_bk_id;
#$_SESSION[profil_id] = $last_bk_id;
}

function liste_einh_in($w_id){
	$f = new formular;
	$this->get_wirt_e_infos($w_id);
	$f->erstelle_formular("Liste der Einheiten in $this->w_name", NULL);
	$einheiten_arr = $this->get_einheiten_from_wirte($w_id);
	$anzahl = count($einheiten_arr);
	if($anzahl){
		echo "<SELECT SIZE=\"10\" NAME=\"IMPORT_AUS\">";
		for($a=0;$a<$anzahl;$a++){
		$e_id = $einheiten_arr[$a][EINHEIT_ID];
		$e_name = $einheiten_arr[$a][EINHEIT_KURZNAME];
		$haus_info = $einheiten_arr[$a][HAUS_STRASSE].$einheiten_arr[$a][HAUS_NUMMER];;
		echo "<OPTION value=\"$e_id\">$e_name</OPTION>";
		}
		echo "</SELECT>";
		echo "<p>Einheiten: $this->anzahl_e</p>";
		echo "<p>QM: $this->g_qm m²</p>";
		echo "<p>Gew.: $this->g_qm_gewerbe m²</p>";
		$f->send_button("submit_del", "Löschen");
		$f->send_button("submit_del_all", "Alle löschen");
	}else{
		echo "Keine Einheiten in der Wirtschafseinheit $this->w_name";
	}
	$f->hidden_feld("option", "wirt_delete");
	$f->ende_formular();
	}

	

function form_einheit_hinzu($w_id){
		
			
	echo "<table><tr valign=\"top\" border=\"0\"><td>";
	$this->liste_einh_in($w_id);
	echo "</td><td>";
	$f = new formular;
	$f->erstelle_formular("Vorauswahl / Einheiten aus ...", NULL);
    $self = "?daten=bk&option=wirt_einheiten_hinzu&w_id=$w_id";
    $link_o="<a href=\"$self&anzeigen=objekt\">Objekt</a>";
    $link_h="<a href=\"$self&anzeigen=haus\">Häuser</a>";
    $link_e="<a href=\"$self&anzeigen=einheit\">Einheiten</a>";
    echo "$link_o<br>";
    echo "$link_h<br>";
    echo "$link_e<br>";
    $f->ende_formular();
	echo "</td><td>";
	$f = new formular;
	$f->erstelle_formular("Bitte wählen", NULL);
    $anzeigen = $_REQUEST['anzeigen'];
    #echo $anzeigen;
     if($anzeigen == 'objekt'){
    $o = new objekt;
    $o_array = $o->liste_aller_objekte();
    #echo '<pre>';
    #print_r($o_array);
    $anzahl = count($o_array);
    echo "<SELECT SIZE=\"10\" NAME=\"IMPORT_AUS\">";
    for($a=0;$a<$anzahl;$a++){
    $objekt_n = $o_array[$a][OBJEKT_KURZNAME];
    $objekt_id = $o_array[$a][OBJEKT_ID];
    echo "<OPTION  value=\"$objekt_id\">$objekt_n</OPTION>";	
    }
	echo "</SELECT";    	
    $f->hidden_feld("anzeigen", "$anzeigen");
    $f->send_button("submit_we", "Übernehmen");
    }
    if($anzeigen == 'haus'){
    $h = new haus;
    $h_array =  $h->liste_aller_haeuser();
   # echo '<pre>';
    #print_r($h_array);
    $anzahl = count($h_array);
    echo "<SELECT SIZE=\"10\" NAME=\"IMPORT_AUS\">";
    for($a=0;$a<$anzahl;$a++){
    $haus_n = $h_array[$a][HAUS_STRASSE].$h_array[$a][HAUS_NUMMER];
    $haus_id = $h_array[$a][HAUS_ID];
    echo "<OPTION  value=\"$haus_id\">$haus_n</OPTION>";	
    }
	echo "</SELECT";    	
    $f->hidden_feld("anzeigen", "$anzeigen");
    $f->send_button("submit_we", "Übernehmen");
    }
    if($anzeigen == 'einheit'){
    $e_array =  $this->liste_aller_einheiten($w_id);
    $anzahl = count($e_array);
    echo "<SELECT SIZE=\"10\" NAME=\"IMPORT_AUS\">";
    for($a=0;$a<$anzahl;$a++){
    $ein_id = $e_array[$a][EINHEIT_ID];
    $ein_n = $e_array[$a][EINHEIT_KURZNAME];
    
    echo "<OPTION value=\"$ein_id\">$ein_n</OPTION>";	
    }
	echo "</SELECT";    	
    $f->hidden_feld("anzeigen", "$anzeigen");
    
    $f->send_button("submit_we", "Übernehmen");
	    	
    }
    $f->hidden_feld("anzeigen", "$anzeigen");
    $f->hidden_feld("option", "wirt_hinzu");
    $f->ende_formular();
	echo "</td></tr>";
	echo "</table>";
	#}
}

function einheit2_wirt($w_id,$import_aus, $anzeigen){
	echo $import_aus.$anzeigen;
$bk = new bk;


if($anzeigen == 'einheit'){
$last_wtab_id = $bk->last_id('WIRT_EIN_TAB', 'WZ_ID') +1;
$db_abfrage = "INSERT INTO WIRT_EIN_TAB VALUES (NULL, '$last_wtab_id', '$w_id', '$import_aus', '1')";
$resultat = mysql_query($db_abfrage) or
           die(mysql_error());	
}

if($anzeigen == 'haus'){
$einheiten_arr = $this->liste_aller_einheiten_haus($w_id,$import_aus);
$anzahl = count($einheiten_arr);
for($a=0;$a<$anzahl;$a++){
    $ein_id = $einheiten_arr[$a][EINHEIT_ID];
 $last_wtab_id = $bk->last_id('WIRT_EIN_TAB', 'WZ_ID') +1;
$db_abfrage = "INSERT INTO WIRT_EIN_TAB VALUES (NULL, '$last_wtab_id', '$w_id', '$ein_id', '1')";
$resultat = mysql_query($db_abfrage) or
           die(mysql_error());	
}
}

if($anzeigen == 'objekt'){
$einheiten_arr = $this->liste_aller_einheiten_objekt($w_id,$import_aus);
#echo '<pre>';
#print_r($einheiten_arr);
#die();
$anzahl = count($einheiten_arr);
for($a=0;$a<$anzahl;$a++){
$ein_id = $einheiten_arr[$a][EINHEIT_ID];
$last_wtab_id = $bk->last_id('WIRT_EIN_TAB', 'WZ_ID') +1;
$db_abfrage = "INSERT INTO WIRT_EIN_TAB VALUES (NULL, '$last_wtab_id', '$w_id', '$ein_id', '1')";
$resultat = mysql_query($db_abfrage) or
           die(mysql_error());	
}
}



header("Location: ?daten=bk&option=wirt_einheiten_hinzu&anzeigen=$anzeigen&w_id=$w_id");
}


function liste_aller_einheiten($w_id){
		$result = mysql_query ("SELECT EINHEIT_ID, EINHEIT_KURZNAME  FROM EINHEIT WHERE EINHEIT_AKTUELL='1' && EINHEIT_ID NOT IN (SELECT EINHEIT_ID FROM WIRT_EIN_TAB WHERE AKTUELL='1' && W_ID='$w_id') GROUP BY EINHEIT_ID ORDER BY EINHEIT_KURZNAME ASC");
		while ($row = mysql_fetch_assoc($result)) $einheiten_array[] = $row;
		$this->anzahl_einheiten = count($einheiten_array);
		return $einheiten_array;		
		}
		
function liste_aller_einheiten_haus($w_id,$haus_id){
		$result = mysql_query ("SELECT EINHEIT_ID, EINHEIT_KURZNAME  FROM EINHEIT WHERE HAUS_ID='$haus_id' && EINHEIT_AKTUELL='1' && EINHEIT_ID NOT IN (SELECT EINHEIT_ID FROM WIRT_EIN_TAB WHERE AKTUELL='1' && W_ID='$w_id') GROUP BY EINHEIT_ID ORDER BY EINHEIT_KURZNAME ASC");
		while ($row = mysql_fetch_assoc($result)) $einheiten_array[] = $row;
		$this->anzahl_einheiten = count($einheiten_array);
		return $einheiten_array;		
		}		


function liste_aller_einheiten_objekt($w_id,$objekt_id){
		$result = mysql_query ("SELECT EINHEIT_ID, EINHEIT_KURZNAME  FROM EINHEIT JOIN HAUS ON(HAUS.HAUS_ID=EINHEIT.HAUS_ID) JOIN OBJEKT ON(HAUS.OBJEKT_ID=OBJEKT.OBJEKT_ID) WHERE OBJEKT.OBJEKT_ID='$objekt_id' && EINHEIT_AKTUELL='1' && EINHEIT_ID NOT IN (SELECT EINHEIT_ID FROM WIRT_EIN_TAB WHERE AKTUELL='1' && W_ID='$w_id') GROUP BY EINHEIT_ID ORDER BY EINHEIT_KURZNAME ASC");
		while ($row = mysql_fetch_assoc($result)) $einheiten_array[] = $row;
		$this->anzahl_einheiten = count($einheiten_array);
		return $einheiten_array;		
		}		


function del_all($w_id){
$db_abfrage = "DELETE FROM WIRT_EIN_TAB WHERE W_ID='$w_id'";
$resultat = mysql_query($db_abfrage) or
           die(mysql_error());		
header("Location: ?daten=bk&option=wirt_einheiten_hinzu&w_id=$w_id");
}

function del_eine($w_id,$e_id){
$db_abfrage = "DELETE FROM WIRT_EIN_TAB WHERE W_ID='$w_id' && EINHEIT_ID='$e_id'";
$resultat = mysql_query($db_abfrage) or
           die(mysql_error());		
header("Location: ?daten=bk&option=wirt_einheiten_hinzu&w_id=$w_id");
}



}//end class wirt_e

?>
