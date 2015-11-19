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
 * @filesource   $HeadURL: http://192.168.2.52/svn/berlussimo_1/tags/02.11.2010 - Downloadversion 0.27/classes/class_details.php $
 * @version      $Revision: 15 $
 * @modifiedby   $LastChangedBy: sivac $
 * @lastmodified $Date: 2011-07-07 10:41:33 +0200 (Do, 07 Jul 2011) $
 * 
 */

/*Modul DETAILS für Anzeigen/erfassung aller DETAILS bezogen auf OBJEKTE, HÄUSER, MIETER, EINHEITEN USW*/


/*Allgemeine Funktionsdatei laden*/
if(file_exists("includes/allgemeine_funktionen.php")){
include_once("includes/allgemeine_funktionen.php");
}

/*Klasse "formular" für Formularerstellung laden*/
if(file_exists("classes/class_formular.php")){
include_once("classes/class_formular.php");
}

/*Berlussimo Hauptklasse laden*/
if(file_exists("classes/berlussimo_class.php")){
include_once("classes/berlussimo_class.php");	
}


class detail{
	

function detailsanzeigen($detail_tabelle, $detail_id){
$f = new formular;
$f->fieldset("Details menü", 'details_menue');
$link = "?daten=details&option=details_hinzu&detail_tabelle=$detail_tabelle&detail_id=$detail_id";
echo "<a href=\"$link\">Neues Detail hinzufügen</a>&nbsp;";
$f->fieldset_ende();

$db_abfrage = "SELECT DETAIL_DAT, DETAIL_ID, DETAIL_NAME, DETAIL_INHALT, DETAIL_BEMERKUNG FROM DETAIL WHERE DETAIL_AKTUELL='1' && DETAIL_ZUORDNUNG_TABELLE = '$detail_tabelle' && DETAIL_ZUORDNUNG_ID = '$detail_id' ORDER BY DETAIL_NAME ASC";
$resultat = mysql_query($db_abfrage) or
           die(mysql_error());

$numrows = mysql_numrows($resultat);

if($numrows){
echo "<table>\n";
$kurzinfo = $this->get_info_detail($detail_tabelle, $detail_id);
echo "<tr class=\"feldernamen\"><td colspan=4>Details über $kurzinfo</td></tr>\n";		
echo "<tr class=\"feldernamen\"><td>Beschreibung</td><td>Inhalt</td><td>Bemerkung</td><td>Optionen</td></tr>\n";
 	
$counter = 0;
while (list ($DETAIL_DAT, $DETAIL_ID, $DETAIL_NAME, $DETAIL_INHALT, $DETAIL_BEMERKUNG) = mysql_fetch_row($resultat)){
$counter++;
$loeschen_link = "<a href=\"?daten=details&option=detail_loeschen&detail_dat=$DETAIL_DAT\">Löschen</a>";

if($counter == 1){
#echo "$DETAIL_NAME $DETAIL_INHALT<br>\n";
echo "<tr class=\"zeile1\"><td>$DETAIL_NAME</td><td>$DETAIL_INHALT</td><td>$DETAIL_BEMERKUNG</td><td>$loeschen_link</td></tr>\n";
}
if($counter == 2){
echo "<tr class=\"zeile2\"><td>$DETAIL_NAME</td><td>$DETAIL_INHALT</td><td>$DETAIL_BEMERKUNG</td><td>$loeschen_link</td></tr>\n";
$counter = 0;
}
}
echo "<tr><td colspan=2>";
echo "</td></tr>";
echo "</table>";
}
else{
echo "Keine Details vorhanden";	
}
}



function get_info_detail($tab,$id){
if($tab == "OBJEKT"){
$db_abfrage = "SELECT OBJEKT_KURZNAME FROM OBJEKT WHERE OBJEKT_AKTUELL='1' && OBJEKT_ID = '$id' order by OBJEKT_DAT DESC limit 0,1";
$resultat = mysql_query($db_abfrage) or
           die(mysql_error());
while (list ($OBJEKT_KURZNAME) = mysql_fetch_row($resultat))
return $OBJEKT_KURZNAME;			
}
if($tab == "HAUS"){
$db_abfrage = "SELECT HAUS_STRASSE, HAUS_NUMMER FROM HAUS WHERE HAUS_AKTUELL='1' && HAUS_ID = '$id' order by HAUS_DAT DESC limit 0,1";
$resultat = mysql_query($db_abfrage) or
           die(mysql_error());
while (list ($HAUS_STRASSE, $HAUS_NUMMER) = mysql_fetch_row($resultat))
$akt_haus = "$HAUS_STRASSE $HAUS_NUMMER";
return $akt_haus;			
}
if($tab == "EINHEIT"){
$db_abfrage = "SELECT EINHEIT_KURZNAME FROM EINHEIT WHERE EINHEIT_AKTUELL='1' && EINHEIT_ID = '$id' order by EINHEIT_DAT DESC limit 0,1";
$resultat = mysql_query($db_abfrage) or
           die(mysql_error());
while (list ($EINHEIT_KURZNAME) = mysql_fetch_row($resultat))
return $EINHEIT_KURZNAME;			
}
if($tab == "MIETVERTRAG"){
$mieternamen = mieternamen_als_string($id);
$db_abfrage = "SELECT EINHEIT_ID, MIETVERTRAG_VON, MIETVERTRAG_BIS FROM MIETVERTRAG WHERE MIETVERTRAG_AKTUELL='1' && MIETVERTRAG_ID = '$id' order by MIETVERTRAG_DAT DESC limit 0,1";
$resultat = mysql_query($db_abfrage) or
           die(mysql_error());
while (list ($EINHEIT_ID, $MIETVERTRAG_VON, $MIETVERTRAG_BIS) = mysql_fetch_row($resultat)){
$einheit_name = einheit_name($EINHEIT_ID);
$anzahl_mieter = anzahl_mieter_im_vertrag($id);
$ausgabe = "$einheit_name vermietet an $anzahl_mieter Personen ($mieternamen) am $MIETVERTRAG_VON bis $MIETVERTRAG_BIS";
return $ausgabe;			
	}
 }
if($tab == "PERSON"){
$p = new person;
$p->get_person_infos($id);
$kurzinfo = "$p->person_nachname $p->person_vorname";
return $kurzinfo;			
}
}


function get_detail_kat_arr($tabelle){
$result = mysql_query ("SELECT DETAIL_KAT_ID, DETAIL_KAT_NAME FROM `DETAIL_KATEGORIEN` WHERE `DETAIL_KAT_KATEGORIE` = '$tabelle'
AND `DETAIL_KAT_AKTUELL` = '1' ORDER BY DETAIL_KAT_NAME ASC");
	
	
	while ($row = mysql_fetch_assoc($result)){
	$my_arr[] = $row;	
	}
return $my_arr;
}

function get_detail_ukat_arr($kat_id){

$result = mysql_query ("SELECT UNTERKATEGORIE_NAME FROM `DETAIL_UNTERKATEGORIEN` WHERE `KATEGORIE_ID` = '$kat_id' AND `AKTUELL` = '1' ORDER BY UNTERKATEGORIE_NAME ASC");
	
	
	while ($row = mysql_fetch_assoc($result)){
	$my_arr[] = $row;	
	}
return $my_arr;
}

function detail_optionen_arr($kat_bez){
$db_abfrage = "SELECT KATEGORIE_ID, UNTERKATEGORIE_NAME FROM `DETAIL_KATEGORIEN` JOIN DETAIL_UNTERKATEGORIEN ON (`DETAIL_KAT_ID`=KATEGORIE_ID) WHERE `DETAIL_KAT_NAME`='$kat_bez'";
$result = mysql_query($db_abfrage) or
           die(mysql_error());	
$numrows = mysql_numrows($result);
	if($numrows){
	while ($row = mysql_fetch_assoc($result)) $my_arr[] = $row;
	return $my_arr;
	}
}

function dropdown_optionen($label, $name, $id, $kat_bez, $vorgabe, $js=null){
	
	$arr = $this->detail_optionen_arr($kat_bez);
	if(is_array($arr)){
	/*echo '<pre>';
		print_r($arr);*/
	echo "<label for=\"$id\">$label</label><select name=\"$name\" id=\"$id\" size=1 $js>\n";
	echo "<option value=\"0\">Bitte wählen</option>\n";
	$anz = count($arr);
	
		for($a=0;$a<$anz;$a++){
			$u_name = ltrim(rtrim($arr[$a]['UNTERKATEGORIE_NAME']));
			if(ltrim(rtrim($vorgabe)) == $u_name){
			echo "<option value=\"$u_name\" selected>$u_name</option>\n";
			}else{
				echo "<option value=\"$u_name\">$u_name</option>\n";	
			}
		}
	echo "</select>\n";
	}else{
	echo "<label for=\"$name\">$beschreibung</label>\n";
	echo "<input type=\"text\" id=\"$id\" name=\"$name\" value=\"$wert\" size=\"$size\" $js_action >\n";	
	}
	
}

function select_hauptkats_arr($beschreibung, $name, $id, $js, $selected_value, $arr){

	if(is_array($arr)){
	echo "<label for=\"$id\">$beschreibung</label>\n";
	echo "<select name=\"$name\" id=\"$id\" $js>\n";
	$anzahl = count($arr);
		echo "<option value=\"nooption\">Bitte wählen</option>\n";
		for($a = 0; $a < $anzahl;$a++){
		$kat_id = $arr[$a]['DETAIL_KAT_ID'];
		$kat_name =	$arr[$a]['DETAIL_KAT_NAME'];
			
			if($kat_name == $selected_value){
			echo "<option value=\"$kat_id\" selected>$kat_name</option>\n";
			}else{
			echo "<option value=\"$kat_id\">$kat_name</option>\n";		
				}
		}
echo "</select>\n";	
}else{
	echo "Fehler beim Lesen aus der DB / Error:D123";
}
}

function select_unterkats_arr($beschreibung, $name, $id, $js, $selected_value, $arr){

	if(is_array($arr)){
	echo "<label for=\"$id\" >$beschreibung</label>\n";
	echo "<select name=\"$name\" id=\"$id\" $js>\n";
	$anzahl = count($arr);
		for($a = 0; $a < $anzahl;$a++){
		$kat_name =	$arr[$a]['UNTERKATEGORIE_NAME'];
		if($kat_name == $selected_value){
			echo "<option value=\"$kat_id\" selected>$kat_name</option>\n";
			}else{
			echo "<option value=\"$kat_id\">$kat_name</option>\n";		
				}
		}
echo "</select>\n";	
}else{
	echo "Fehler beim Lesen aus der DB / Error:D123";
}
}

function select_unterkats($beschreibung, $name, $id, $js){

	echo "<label for=\"$id\">$beschreibung</label>\n";
	echo "<select name=\"$name\" id=\"$id\" $js>\n";
	echo "<option value=\"nooption\">Manuell eintragen</option>";	 
	echo "</select>\n";	
}



function form_detail_hinzu($tab, $id, $vorauswahl=null){
	$kurzinfo = $this->get_info_detail($tab,$id);
	$form = new formular;
    $link ='';
	if($tab=='EINHEIT'){
	$link = "<a href=\"index.php?daten=uebersicht&anzeigen=einheit&einheit_id=$id\">Zurück zu Einheit</a>";
    }
	$form->erstelle_formular('Detail hinzufügen', '');
	echo "$link<br>";
	$form->hidden_feld("tabelle", "$tab");
    $form->hidden_feld("id", "$id");
    $det_kat_arr = $this->get_detail_kat_arr($tab);
    #$det_ukat_arr = $this->get_detail_ukat_arr(1);
    $js="onchange=\"get_detail_ukats(this.value)\" onload=\"get_detail_ukats(this.value)\"";
    $this->select_hauptkats_arr("Detail auswählen zu $kurzinfo", 'detail_kat', 'detail_kat', $js, $vorauswahl, $det_kat_arr);
    
    $this->select_unterkats('Detailoption auswählen', 'detail_ukat', 'detail_ukat', '');
    $hinw =  ' Text als Warnung eingeben: <p class="warnung"> INHALT </p>';
        
    $form->text_bereich('Detail Inhalt', 'inhalt', '', 20, 10, 'inhalt');
    echo htmlentities($hinw);
    $form->text_bereich('Bemerkung', 'bemerkung', '', 20, 10, 'bemerkung');
    echo "<br>";
    $form->hidden_feld("option", "detail_gesendet"); 
    $form->send_button("submit_detail", "Eintragen");
    $form->ende_formular();
    $this->detailsanzeigen($tab, $id);	
}

function get_katname($kat_id){
$this->detail_name = '';
$db_abfrage = "SELECT DETAIL_KAT_NAME FROM DETAIL_KATEGORIEN WHERE DETAIL_KAT_AKTUELL='1' && DETAIL_KAT_ID = '$kat_id' limit 0,1";
$resultat = mysql_query($db_abfrage) or
           die(mysql_error());
while (list ($DETAIL_KAT_NAME) = mysql_fetch_row($resultat))
$this->detail_name = $DETAIL_KAT_NAME;				
}


function detail_speichern($tabelle, $id, $det_name, $det_inhalt, $det_bemerkung){
$this->letzte_detail_id();
#$det_inhalt = str_replace("\n", '<br />', $det_inhalt);
$db_abfrage = "INSERT INTO DETAIL VALUES (NULL, '$this->last_detail_id', '$det_name','$det_inhalt', '$det_bemerkung', '1','$tabelle','$id')";
$resultat = mysql_query($db_abfrage) or
           die(mysql_error());
	if($resultat){
	echo "<br>Detail wurde gespeichert";
	}else{
	echo "<br>FEHLER: Detail wurde NICHT gespeichert";
	}
weiterleiten_in_sec("?daten=details&option=details_anzeigen&detail_tabelle=$tabelle&detail_id=$id", 2);
}

function detail_speichern_2($tabelle, $id, $det_name, $det_inhalt, $det_bemerkung){
$this->letzte_detail_id();
#$det_inhalt = str_replace("\n", '<br />', $det_inhalt);
if($det_bemerkung==''){
	$det_bemerkung = $_SESSION['username'].'-'.date("d.m.Y H:i");
}
$db_abfrage = "INSERT INTO DETAIL VALUES (NULL, '$this->last_detail_id', '$det_name','$det_inhalt', '$det_bemerkung', '1','$tabelle','$id')";
$resultat = mysql_query($db_abfrage) or
           die(mysql_error());
	

}

function check_detail_exist($tab, $tab_id, $det_name){
$db_abfrage = "SELECT DETAIL_INHALT FROM DETAIL WHERE DETAIL_AKTUELL='1' && DETAIL_NAME='$det_name' && DETAIL_ZUORDNUNG_TABELLE='$tab' && DETAIL_ZUORDNUNG_ID='$tab_id'";
$result = mysql_query($db_abfrage) or
           die(mysql_error());		
$numrows = mysql_numrows($result);
		if($numrows){
			return true;
		}else{
			return false;
		}	
}

function detail_aktualisieren($tab, $tab_id, $det_name, $det_inhalt, $det_bemerkung){
	if($this->check_detail_exist($tab, $tab_id, $det_name)){
		$this->details_deaktivieren($tab, $tab_id, $det_name);	
	}
	$this->detail_speichern_2($tab, $tab_id, $det_name, $det_inhalt, $det_bemerkung);
}

function details_deaktivieren($tab, $tab_id, $det_name){
$db_abfrage = "UPDATE DETAIL SET DETAIL_AKTUELL='0' WHERE DETAIL_NAME='$det_name' && DETAIL_ZUORDNUNG_TABELLE='$tab' && DETAIL_ZUORDNUNG_ID='$tab_id'";
$result = mysql_query($db_abfrage) or
           die(mysql_error());		
}



function letzte_detail_id(){
$this->last_detail_id = '';
$db_abfrage = "SELECT DETAIL_ID FROM DETAIL ORDER BY DETAIL_ID DESC LIMIT 0,1";
$resultat = mysql_query($db_abfrage) or
           die(mysql_error());
while (list ($DETAIL_ID) = mysql_fetch_row($resultat))
$this->last_detail_id = $DETAIL_ID +1;
}

function detail_loeschen($detail_dat){
$db_abfrage = "UPDATE DETAIL SET DETAIL_AKTUELL='0' WHERE DETAIL_DAT='$detail_dat'";
$resultat = mysql_query($db_abfrage) or
           die(mysql_error());
	if($resultat){
	echo "<br>Detail wurde gelöscht";
	}else{
	echo "<br>FEHLER: Detail wurde NICHT gelöscht";
	}
$this->finde_tab_id($detail_dat);	
$link = "?daten=details&option=details_anzeigen&detail_tabelle=$this->dat_tabelle&detail_id=$this->dat_id";
#echo $link;
weiterleiten_in_sec("$link", 2);
}



function get_detail_info($detail_dat){
	$db_abfrage = "SELECT * FROM DETAIL WHERE DETAIL_DAT='$detail_dat'";
	$resultat = mysql_query($db_abfrage) or
	die(mysql_error());
	$row = mysql_fetch_assoc($resultat);
	return $row;
	
}

function finde_tab_id($detail_dat){
$this->det_tabelle = '';
$this->det_id = '';
$db_abfrage = "SELECT DETAIL_ZUORDNUNG_TABELLE, DETAIL_ZUORDNUNG_ID FROM DETAIL WHERE DETAIL_DAT='$detail_dat'";
$resultat = mysql_query($db_abfrage) or
           die(mysql_error());
while ($row = mysql_fetch_assoc($resultat)){
$this->dat_tabelle = $row['DETAIL_ZUORDNUNG_TABELLE'];
$this->dat_id = $row['DETAIL_ZUORDNUNG_ID'];
}
}

/*mandantennr finden falls exisitiert*/
function finde_mandanten_nr($partner_id){
$db_abfrage = "SELECT DETAIL_INHALT FROM DETAIL WHERE DETAIL_ZUORDNUNG_TABELLE='PARTNER_LIEFERANT' && DETAIL_NAME='Mandanten-Nr' && DETAIL_ZUORDNUNG_ID='$partner_id' && DETAIL_AKTUELL='1' ORDER BY DETAIL_DAT DESC limit 0,1";
$resultat = mysql_query($db_abfrage) or
           die(mysql_error());
while (list ($DETAIL_INHALT) = mysql_fetch_row($resultat))
return $DETAIL_INHALT;	
}

/*anrede finden falls exisitiert*/
function finde_person_anrede($person_id){
$db_abfrage = " SELECT DETAIL_INHALT FROM DETAIL WHERE DETAIL_ZUORDNUNG_TABELLE = 'PERSON' && DETAIL_NAME = 'Anrede' && DETAIL_ZUORDNUNG_ID = '$person_id' && DETAIL_AKTUELL = '1' ORDER BY DETAIL_DAT DESC LIMIT 0 , 1 ";
$resultat = mysql_query($db_abfrage) or
           die(mysql_error());
while (list ($DETAIL_INHALT) = mysql_fetch_row($resultat))
return $DETAIL_INHALT;	
}

/*geschlecht finden falls exisitiert*/
function finde_person_geschlecht($person_id){
$db_abfrage = " SELECT DETAIL_INHALT FROM DETAIL WHERE DETAIL_ZUORDNUNG_TABELLE = 'PERSON' && DETAIL_NAME = 'Geschlecht' && DETAIL_ZUORDNUNG_ID = '$person_id' && DETAIL_AKTUELL = '1' ORDER BY DETAIL_DAT DESC LIMIT 0 , 1 ";
$resultat = mysql_query($db_abfrage) or
           die(mysql_error());
while (list ($DETAIL_INHALT) = mysql_fetch_row($resultat))
return ltrim(rtrim($DETAIL_INHALT));	
}

/*Funktion um alle Details zu finden anhand des Detailnamens, werden die Detailinhalte angezeigt*/

function finde_detail_inhalt($tab, $id, $detail_name){
$db_abfrage = "SELECT DETAIL_INHALT FROM DETAIL WHERE DETAIL_ZUORDNUNG_TABELLE = '$tab' && DETAIL_NAME = '$detail_name' && DETAIL_ZUORDNUNG_ID = '$id' && DETAIL_AKTUELL = '1' ORDER BY DETAIL_DAT DESC LIMIT 0 , 1";
#if($detail_name=='WEG-KaltmieteINS'){
#echo $db_abfrage;
#die();
#}
$resultat = mysql_query($db_abfrage) or
           die(mysql_error());
while (list ($DETAIL_INHALT) = mysql_fetch_row($resultat))
return ltrim(rtrim($DETAIL_INHALT));
}


function finde_alle_details_grup($tab, $id,  $detail_name){
$db_abfrage = " SELECT DETAIL_INHALT FROM DETAIL WHERE DETAIL_ZUORDNUNG_TABELLE = '$tab' && DETAIL_ZUORDNUNG_ID = '$id' && DETAIL_NAME = '$detail_name'  && DETAIL_AKTUELL = '1' ORDER BY DETAIL_INHALT DESC";
$result = mysql_query($db_abfrage) or
           die(mysql_error());

while ($row = mysql_fetch_assoc($result)){
	$my_arr[] = $row;	
	}
	
	if($my_arr){
		return $my_arr;
	}else{
		return false;
	}

}


function finde_detail_inhalt_arr($detail_name){
	
	$db_abfrage = " SELECT * FROM DETAIL WHERE DETAIL_NAME = '$detail_name'  && DETAIL_AKTUELL = '1' ORDER BY DETAIL_DAT DESC";
	$result = mysql_query($db_abfrage) or
           die(mysql_error());
	
while ($row = mysql_fetch_assoc($result)){
	$my_arr[] = $row;	
	}
	
	if(isset($my_arr)){
		return $my_arr;
	}else{
		return false;
	}

}

function finde_detail_inhalt_last_arr($tab, $id,  $detail_name){
	$db_abfrage = " SELECT * FROM DETAIL WHERE DETAIL_ZUORDNUNG_TABELLE = '$tab' && DETAIL_ZUORDNUNG_ID = '$id' && DETAIL_NAME = '$detail_name'  && DETAIL_AKTUELL = '1' ORDER BY DETAIL_INHALT DESC LIMIT 0,1";
	#echo "<h1>$db_abfrage</h1>";
	
	$result = mysql_query($db_abfrage) or
	die(mysql_error());

	while ($row = mysql_fetch_assoc($result)){
		$my_arr[] = $row;
	}

	if(isset($my_arr)){
		
		return $my_arr;
	}else{
		return false;
	}

}



function finde_alle_details_arr($tab,  $tab_id){
$db_abfrage = "SELECT DETAIL_NAME, DETAIL_INHALT,  DETAIL_BEMERKUNG FROM DETAIL WHERE DETAIL_ZUORDNUNG_TABELLE = '$tab' && DETAIL_ZUORDNUNG_ID = '$tab_id' && DETAIL_AKTUELL = '1' ORDER BY DETAIL_NAME ASC";
#echo $db_abfrage;
$result = mysql_query($db_abfrage) or
           die(mysql_error());

while ($row = mysql_fetch_assoc($result)){
	$my_arr[] = $row;	
	}
	
	if(isset($my_arr)){
		return $my_arr;
	}else{
		return false;
	}

}




function get_det_arr(){
$db_abfrage = "SELECT  `DETAIL_NAME` FROM  `DETAIL` WHERE  `DETAIL_AKTUELL` =  '1' GROUP BY DETAIL_NAME";
#echo $db_abfrage;
$result = mysql_query($db_abfrage) or
           die(mysql_error());

while ($row = mysql_fetch_assoc($result)){
	$my_arr[] = $row;	
	}
	
	if(isset($my_arr)){
		return $my_arr;
	}else{
		return false;
	}
}


function dropdown_details($label, $name, $id){
	
	$arr = $this->get_det_arr();
	
	echo "<label for=\"$id\">$label</label><select name=\"$name\" id=\"$id\" size=1 >\n";
	echo "<option value=\"\">Bitte wählen</option>\n";
	if(is_array($arr)){
	$anz = count($arr);
		
		for($a=0;$a<$anz;$a++){
			$det_name = $arr[$a]['DETAIL_NAME'];
			echo "<option value=\"$det_name\">$det_name</option>\n";	
			}
	}
	echo "</select>\n";
	
	
}


function finde_detail($suchtext, $det_name=null){
	if($det_name==null){
	$db_abfrage = "SELECT * FROM  `DETAIL` WHERE  `DETAIL_INHALT` LIKE  '%$suchtext%' AND  `DETAIL_AKTUELL` =  '1' ORDER BY DETAIL_NAME ASC";
	
	}else{
	$db_abfrage = "SELECT * FROM  `DETAIL` WHERE  `DETAIL_NAME`='$det_name' && `DETAIL_INHALT` LIKE  '%$suchtext%' AND  `DETAIL_AKTUELL` =  '1'";
	}
	$result = mysql_query($db_abfrage) or
           die(mysql_error());
          while ($row = mysql_fetch_assoc($result)){
			$my_arr[] = $row;	
		  }	
	if(is_array($my_arr)){
	$anz = count($my_arr);
	#print_r($my_arr);	
	echo "<table>";
		echo "<tr><th>DETNAME</th><th>INHALT</th><th>BEZ</th></tr>";
		for($a=0;$a<$anz;$a++){
			$det_name = $my_arr[$a]['DETAIL_NAME'];
			$det_inhalt = $my_arr[$a]['DETAIL_INHALT'];
			$det_tab = ucfirst(strtolower($my_arr[$a]['DETAIL_ZUORDNUNG_TABELLE']));
			$det_tab_id = $my_arr[$a]['DETAIL_ZUORDNUNG_ID'];
			#$r = new rechnung();
			#$bez = $r->kostentraeger_ermitteln($det_tab, $det_tab_id);
			if(strtolower($my_arr[$a]['DETAIL_ZUORDNUNG_TABELLE'])=='objekt'){
				$o = new objekt;
				$o->get_objekt_infos($det_tab_id);
				$link_e = "<a href=\"?daten=details&option=details_anzeigen&detail_tabelle=OBJEKT&detail_id=$det_tab_id\">Objekt: $o->objekt_kurzname</a>";
			}
			if(strtolower($my_arr[$a]['DETAIL_ZUORDNUNG_TABELLE'])=='einheit'){
				$e = new einheit;
				$e->get_einheit_info($det_tab_id);
			$link_e = "<a href=\"?daten=uebersicht&anzeigen=einheit&einheit_id=$det_tab_id\">Einheit: $e->einheit_kurzname</a>";
			}
			
			if(strtolower($my_arr[$a]['DETAIL_ZUORDNUNG_TABELLE'])=='mietvertrag'){
				$mvs = new mietvertraege;
				$mvs->get_mietvertrag_infos_aktuell($det_tab_id);
				
				$link_e = "<a href=\"?daten=uebersicht&anzeigen=einheit&einheit_id=$mvs->einheit_id&mietvertrag_id=$det_tab_id\">Mieter: $mvs->einheit_kurzname $mvs->personen_name_string</a>";
			}
			
			if(strtolower($my_arr[$a]['DETAIL_ZUORDNUNG_TABELLE'])=='person'){
				$pp = new personen;
				$pp->get_person_infos($det_tab_id);
				if($pp->person_anzahl_mietvertraege>0){
					$link_e ='';
					for($pm=0;$pm<$pp->person_anzahl_mietvertraege;$pm++){
					$mv_id = $pp->p_mv_ids[$pm];
					$mvs = new mietvertraege;
					$mvs->get_mietvertrag_infos_aktuell($mv_id);
					$link_e .= "Mieter: $mvs->einheit_kurzname $pp->person_nachname $pp->person_vorname<br>";
					}	
				}else{
				$link_e = "Kein Mieter: $pp->person_nachname $pp->person_vorname";
				}
			}
			
			
			if(!isset($link_e)){
				$link_e = "$det_tab $det_tab_id";
			}
			
			
			
			
			
			echo "<tr><td>$det_name</td><td>$det_inhalt</td><td>$link_e</td></tr>";
			
		}
		echo "</table>";	
	
	}else{
		echo "NOT FOUND!!!";
	}
}

}//class details ende


?>
