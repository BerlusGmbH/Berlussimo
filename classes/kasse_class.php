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
 * @filesource   $HeadURL: http://192.168.2.52/svn/berlussimo_1/tags/02.11.2010 - Downloadversion 0.27/classes/kasse_class.php $
 * @version      $Revision: 6 $
 * @modifiedby   $LastChangedBy: sivac $
 * @lastmodified $Date: 2010-09-21 10:34:50 +0200 (Di, 21 Sep 2010) $
 * 
 */

/*
 * Das ist eine Test bzw. Spieledatei, aus der kann man viel lernen,
 * leider die Funktionen und vars nicht in Deutsch
 */

/*Klasse "formular" für Formularerstellung laden*/
#include_once("classes/class_formular.php");

#include_once("config.inc.php");
include_once(HAUPT_PATH.'/'.BERLUS_PATH."/classes/config.inc.php");
include_once(HAUPT_PATH.'/'.BERLUS_PATH."/includes/config.php");
include_once(HAUPT_PATH.'/'.BERLUS_PATH."/classes/berlussimo_class.php");


class kasse extends rechnung{
var $kassen_name;
var $kassen_verwalter;
var $kassen_id;
var $kasse_in_rechnung_gestellt;
var $kasse_aus_rechnung_erhalten;
var $kasse_direkt_gezahlt;
var $kassen_stand;
var $kassen_forderung_offen;
var $kassen_ausgaben_offen;
var $kassen_summe_ein_auszahlung;		

function dropdown_kassen($label, $name, $id){
$result = mysql_query ("SELECT KASSEN_ID, KASSEN_NAME, KASSEN_VERWALTER FROM `KASSEN` WHERE AKTUELL = '1'");
	    $numrows = mysql_numrows($result);
		if($numrows){
		echo "<input type=\"hidden\" name=\"empfaenger_typ\" value=\"Kasse\">";
		echo "<label for=\"$id\">$label</label>";
		echo "<select name=\"$name\" id=\"$id\">";
		while($row = mysql_fetch_assoc($result)){
		if(isset($_SESSION[kasse]) && $_SESSION[kasse] == $row[KASSEN_ID]){
		echo "<option value=\"$row[KASSEN_ID]\" selected>$row[KASSEN_NAME] - $row[KASSEN_VERWALTER]</option>";
		}else{
		echo "<option value=\"$row[KASSEN_ID]\">$row[KASSEN_NAME] - $row[KASSEN_VERWALTER]</option>";	
		}
		}
		echo "</select>";
		}else{
			return FALSE;
		}	
}
	
function get_kassen_info($kassen_id){
#$result = mysql_query ("SELECT KASSEN_NAME, KASSEN_VERWALTER FROM `KASSEN` WHERE AKTUELL = '1' && KASSEN_ID='$kassen_id' ORDER BY KASSEN_DAT DESC LIMIT 0,1");
$result = mysql_query ("SELECT KASSEN_NAME, KASSEN_VERWALTER, PARTNER_ID FROM `KASSEN` RIGHT JOIN (KASSEN_PARTNER) ON (KASSEN.KASSEN_ID = KASSEN_PARTNER.KASSEN_ID) WHERE KASSEN.AKTUELL = '1' && KASSEN_PARTNER.AKTUELL = '1' && KASSEN.KASSEN_ID='1' ORDER BY KASSEN_DAT DESC LIMIT 0,1");

	    $numrows = mysql_numrows($result);
		if($numrows){
		$row = mysql_fetch_assoc($result);
		$this->kassen_name = $row[KASSEN_NAME];
		$this->kassen_verwalter = $row[KASSEN_VERWALTER];
		$this->kassen_id = $kassen_id;
		$this->summe_ein_ausgaben($kassen_id);
		$this->kasse_in_rechnung_gestellt($kassen_id);
		$this->kasse_einnahme_durch_zahlung($kassen_id);
		$this->ausgabe_durch_barzahlung($kassen_id);
		$this->kasse_offen_zu_zahlen($kassen_id);
		$this->kassen_partner_id = $row[PARTNER_ID];
		
		}else{
			return FALSE;
		}	
}

function summe_ein_ausgaben($kassen_id){
	/*Gesamtsumme aller Kasseneinzahlungen aus dem Kassenbuch*/
	$result = mysql_query ("SELECT SUM(BETRAG) AS EIN_AUSGABEN FROM KASSEN_BUCH WHERE KASSEN_ID='$kassen_id' && AKTUELL = '1'");
	    $numrows = mysql_numrows($result);
		if($numrows>0){
		$row = mysql_fetch_assoc($result);
		}else{
			return FALSE;
		}	
}



	function kasse_in_rechnung_gestellt($kassen_id){	
	/*Gesamtsumme aller in Rechnung gstellten Summen*/
	$result = mysql_query ("SELECT SUM(SKONTOBETRAG) AS kasse_in_rechnung_gestellt FROM RECHNUNGEN WHERE AUSSTELLER_TYP='Kasse' && AUSSTELLER_ID='$kassen_id' && AKTUELL = '1'");
	    $numrows = mysql_numrows($result);
		if($numrows){
		$row = mysql_fetch_assoc($result);
		$this->kasse_in_rechnung_gestellt = $row[kasse_in_rechnung_gestellt];
		}else{
			return FALSE;
		}
}	
function kasse_einnahme_durch_zahlung($kassen_id){
/*Gesamtsumme aller in Rechnung gestellten Summen und bezahlten*/
	$result = mysql_query ("SELECT SUM(SKONTOBETRAG) AS kasse_aus_rechnung_erhalten FROM RECHNUNGEN WHERE AUSSTELLER_TYP='Kasse' && AUSSTELLER_ID='$kassen_id' && STATUS_BEZAHLT='1' && AKTUELL = '1'");
	    $numrows = mysql_numrows($result);
		if($numrows){
		$row = mysql_fetch_assoc($result);
		$this->kasse_aus_rechnung_erhalten = $row[kasse_aus_rechnung_erhalten];
		}else{
			return FALSE;
		}
}
function ausgabe_durch_barzahlung($kassen_id){
/*Gesamtsumme aller aus der Kasse  bezahlten Rechnungssummen*/
	$result = mysql_query ("SELECT SUM(SKONTOBETRAG) AS kasse_direkt_gezahlt FROM RECHNUNGEN WHERE EMPFAENGER_TYP='Kasse' && EMPFAENGER_ID='$kassen_id' && STATUS_BEZAHLT='1' && AKTUELL = '1'");
	    $numrows = mysql_numrows($result);
		if($numrows){
		$row = mysql_fetch_assoc($result);
		$this->kasse_direkt_gezahlt = $row[kasse_direkt_gezahlt];
		}else{
			return FALSE;
		}
}		
/* *Gesamtsumme aller aus der Kasse noch zu bezahlenden Rechnungssummen*/
function kasse_offen_zu_zahlen($kassen_id){
	$result = mysql_query ("SELECT SUM(SKONTOBETRAG) AS kassen_ausgaben_offen FROM RECHNUNGEN WHERE EMPFAENGER_TYP='Kasse' && EMPFAENGER_ID='$kassen_id' && STATUS_BEZAHLT='0' && AKTUELL = '1'");
	    $numrows = mysql_numrows($result);
		if($numrows){
		$row = mysql_fetch_assoc($result);
		$this->kassen_ausgaben_offen = $row[kassen_ausgaben_offen];
		}else{
			return FALSE;
		}		
}

		
	function kassen_ueberblick(){
		$result = mysql_query ("SELECT KASSEN_ID FROM `KASSEN` WHERE AKTUELL = '1'");
	    $numrows = mysql_numrows($result);
		if($numrows){
			
			echo "<table>";
			echo "<tr><td>Kasse</td><td>Verwalter</td><td></td><td></td><td>Kassenstand</td><td>Ausgaben</td><td>Einnahmen</td><td>I.R. gestellt</td><td>Einnahmen Offen</td><td>Ausgaben Offen</td></tr>";
			while($row = mysql_fetch_assoc($result)){
			$this->kassen_id = $row[KASSEN_ID];
			$this->get_kassen_info($this->kassen_id);
			$link_eingang="<a href=\"?daten=kasse&option=kassenbeleg_eingang&kasse=".$this->kassen_id."\">Ausgaben</a>";
			$link_ausgang="<a href=\"?daten=kasse&option=kassenbeleg_ausgang&kasse=".$this->kassen_id."\">Einnahmen</a>"; 
			echo "<tr><td><a href=\"?daten=kasse&option=kassen_uebersicht&kasse=$this->kassen_id\">$this->kassen_name</a></td><td>$this->kassen_verwalter</td><td>$link_eingang</td><td>$link_ausgang</td><td>$this->kassen_stand</td><td>$this->kasse_direkt_gezahlt</td><td>$this->kasse_aus_rechnung_erhalten</td><td>$this->kasse_in_rechnung_gestellt</td><td>$this->kassen_forderung_offen </td><td>$this->kassen_ausgaben_offen</td></tr>";
			} 	
			echo "</table>";
				}else{
			return FALSE;
		}
	}


function einnahmen($kassen_id){
$result = mysql_query ("SELECT BEZAHLT_AM, BELEG_NR,  EMPFAENGER_TYP, EMPFAENGER_ID, NETTO, BRUTTO, SKONTOBETRAG FROM `RECHNUNGEN` WHERE AKTUELL = '1' && AUSSTELLER_TYP='Kasse' && AUSSTELLER_ID='$kassen_id' && STATUS_BEZAHLT='1' ORDER BY BEZAHLT_AM ASC");
	    $numrows = mysql_numrows($result);
		if($numrows){
			echo "<table>";
echo "<table>";
echo "<tr><td colspan=6><b>BISHERIGE EINNAHMEN</b></td></tr>";			
echo "<tr><td>$this->kassen_name</td><td>$this->kassen_verwalter</td><td>Stand: $this->kassen_stand</td><td>Einnahmen offen: $this->kassen_forderung_offen</td><td></td><td></td></tr>";
			echo "<tr><td>BEZAHLT AM</td><td>BELEG</td><td>VON</td><td>NETTO</td><td>BRUTTO</td><td>SKONTO</td></tr>";
			while($row = mysql_fetch_assoc($result)){
			$rechnung = new rechnung;
			$rechnung->rechnung_grunddaten_holen($row[BELEG_NR]);
			$beleg_link = "<a href=\"?daten=rechnungen&option=rechnungs_uebersicht&belegnr=$row[BELEG_NR]\">Ansehen</a>";
			echo "<tr><td>$row[BEZAHLT_AM]</td><td>$beleg_link</td><td>$rechnung->rechnungs_empfaenger_name</td><td>$row[NETTO]</td><td>$row[BRUTTO]</td><td>$row[SKONTOBETRAG]</td>" ;
			} 	
			echo "</table>";
				}else{
			return FALSE;
		}	
} 

function offene_einnahmen($kassen_id){

$result = mysql_query ("SELECT FAELLIG_AM, BELEG_NR,  EMPFAENGER_TYP, EMPFAENGER_ID, NETTO, BRUTTO, SKONTOBETRAG FROM `RECHNUNGEN` WHERE AKTUELL = '1' && AUSSTELLER_TYP='Kasse' && AUSSTELLER_ID='$kassen_id' && STATUS_BEZAHLT='0' ORDER BY FAELLIG_AM ASC");
	    $numrows = mysql_numrows($result);
		if($numrows){
			echo "<table>";
echo "<table>";
echo "<tr><td colspan=6><b>EINNAHMEN</b></td></tr>";			

			echo "<tr><td>FÄLLIG AM</td><td>BELEG</td><td>VON</td><td>NETTO</td><td>BRUTTO</td><td align=right>SKONTO</td></tr>";
			while($row = mysql_fetch_assoc($result)){
			echo "<tr><td>$row[FAELLIG_AM]</td><td>$row[BELEG_NR]</td><td>$row[EMPFAENGER_TYP] $row[EMPFAENGER_ID]</td><td>$row[NETTO]</td><td>$row[BRUTTO]</td><td align=right>$row[SKONTOBETRAG]</td>" ;
			} 	
			echo "<tr><td colspan=5 align=right><hr>Stand: $this->kassen_stand</td><td align=right><hr>Ausgaben offen: $this->kassen_forderung_offen</td></tr>";
			echo "</table>";
				}else{
			return FALSE;
		}	
} 

function ausgaben($kassen_id){

$result = mysql_query ("SELECT BEZAHLT_AM, BELEG_NR,  AUSSTELLER_TYP, AUSSTELLER_ID, NETTO, BRUTTO, SKONTOBETRAG FROM `RECHNUNGEN` WHERE AKTUELL = '1' && EMPFAENGER_TYP='Kasse' && EMPFAENGER_ID='$kassen_id' && STATUS_BEZAHLT='1' ORDER BY BEZAHLT_AM ASC");
	    $numrows = mysql_numrows($result);
		if($numrows){
			echo "<table>";
echo "<tr><td colspan=6><b>BISHERIGE AUSGABEN</b></td></tr>";			
echo "<tr><td>$this->kassen_name</td><td>$this->kassen_verwalter</td><td>Stand: $this->kassen_stand</td><td>Ausgaben offen: $this->kassen_ausgaben_offen</td><td></td><td></td></tr>";
			echo "<tr><td>BEZAHLT AM</td><td>BELEG</td><td>VON</td><td>NETTO</td><td>BRUTTO</td><td>SKONTO</td></tr>";
			while($row = mysql_fetch_assoc($result)){
			$rechnung = new rechnung;
			$rechnung->rechnung_grunddaten_holen($row[BELEG_NR]);
			$beleg_link = "<a href=\"?daten=rechnungen&option=rechnungs_uebersicht&belegnr=$row[BELEG_NR]\">Ansehen</a>";
			echo "<tr><td>$row[BEZAHLT_AM]</td><td>$beleg_link</td><td>$rechnung->rechnungs_aussteller_name</td><td>$row[NETTO]</td><td>$row[BRUTTO]</td><td>$row[SKONTOBETRAG]</td>" ;
			} 	
			echo "</table>";
				}else{
			return FALSE;
		}	
} 


function offene_ausgaben($kassen_id){

$result = mysql_query ("SELECT FAELLIG_AM, BELEG_NR,  AUSSTELLER_TYP, AUSSTELLER_ID, NETTO, BRUTTO, SKONTOBETRAG FROM `RECHNUNGEN` WHERE AKTUELL = '1' && EMPFAENGER_TYP='Kasse' && EMPFAENGER_ID='$kassen_id' && STATUS_BEZAHLT='0' ORDER BY FAELLIG_AM ASC");
	    $numrows = mysql_numrows($result);
		if($numrows){
			echo "<table>";
echo "<tr><td colspan=6><b>AUSGABEN</b></td></tr>";			

			echo "<tr><td>FÄLLIG AM</td><td>BELEG</td><td>VON</td><td>NETTO</td><td>BRUTTO</td><td align=right>SKONTO</td></tr>";
			while($row = mysql_fetch_assoc($result)){
			echo "<tr><td>$row[FAELLIG_AM]</td><td>$row[BELEG_NR]</td><td>$row[AUSSTELLER_TYP] $row[AUSSTELLER_ID]</td><td>$row[NETTO]</td><td>$row[BRUTTO]</td><td align=right>$row[SKONTOBETRAG]</td>" ;
			} 	
			echo "<tr><td colspan=5 align=right><hr>Stand: $this->kassen_stand</td><td align=right><hr>Ausgaben offen: $this->kassen_ausgaben_offen</td></tr>";
			echo "</table>";
				}else{
			return FALSE;
		}	
} 

/*Alle Eingangskassenbelege werden angezeigt*/
		function kassenbelege_anzeigen_eingang($kassen_id){
		/*Zählen aller Zeilen*/
		$result = mysql_query ("SELECT * FROM RECHNUNGEN WHERE AKTUELL = '1' AND RECHNUNGSTYP='Kassenbeleg' AND EMPFAENGER_TYP='Kasse' AND EMPFAENGER_ID='$kassen_id' ORDER BY BELEG_NR DESC");
		 /*$result = mysql_query ("SELECT RECHNUNGEN. * , COUNT( RECHNUNGEN_POSITIONEN.POSITION ) AS ANZAHL_POSITIONEN
FROM RECHNUNGEN, RECHNUNGEN_POSITIONEN
WHERE RECHNUNGEN.BELEG_NR = '1' && RECHNUNGEN_POSITIONEN.BELEG_NR = '1'
GROUP BY RECHNUNGEN.BELEG_NR DESC"); 
		*/	
		$numrows1 = mysql_numrows($result);
		/*Seitennavigation mit  Limit erstellen*/
		echo "<table><tr><td>Anzahl aller Belege: $numrows1</td></tr><tr><td>\n";
		$navi = new blaettern(0,$numrows1,14, '?daten=kasse&option=erfasste_belege');
		echo "</td></tr></table>\n";
		$result = mysql_query ("SELECT * FROM RECHNUNGEN WHERE AKTUELL = '1' AND RECHNUNGSTYP='Kassenbeleg' AND EMPFAENGER_TYP='Kasse' AND EMPFAENGER_ID='$kassen_id' ORDER BY BELEG_NR DESC ".$navi->limit."");	
		$numrows = mysql_numrows($result);
		
		if($numrows>0){
		while ($row = mysql_fetch_assoc($result)) $my_array[] = $row;
		echo "<table class=rechnungen>\n";
		echo "<tr class=feldernamen><td>BNr</td><td>TYP</td><td>Beleg.Nr</td><td>Fälig</td><td>Von</td><td>An</td><td width=80>Netto</td><td width=80>Brutto</td><td width=80>Skonto</td></tr>\n";
		for($a=0;$a<count($my_array);$a++){
		$belegnr = $my_array[$a][BELEG_NR];
		$this->rechnung_grunddaten_holen($belegnr);
		
		
		$e_datum = date_mysql2german($my_array[$a][EINGANGSDATUM]);
		$r_datum = date_mysql2german($my_array[$a][RECHNUNGSDATUM]);
		$faellig_am = date_mysql2german($my_array[$a][FAELLIG_AM]);
		$beleg_link = "<a href=\"?daten=rechnungen&option=rechnungs_uebersicht&belegnr=".$my_array[$a]['BELEG_NR']."\">".$my_array[$a]['BELEG_NR']."</>\n";
		$netto = nummer_punkt2komma($my_array[$a][NETTO]);
		$brutto = nummer_punkt2komma($my_array[$a][BRUTTO]);
		$skonto_betrag = nummer_punkt2komma($my_array[$a][SKONTOBETRAG]);
		$rechnungsnummer = $my_array[$a][RECHNUNGSNUMMER];
		$rechnungstyp = $my_array[$a][RECHNUNGSTYP];
		$kassenbeleg_eingangsnr = $my_array[$a][EMPFAENGER_EINGANGS_RNR];
		echo "<tr><td>$beleg_link</td><td>$rechnungstyp</td><td>$kassenbeleg_eingangsnr</td><td><b>$faellig_am</b></td><td>".$this->rechnungs_aussteller_name."</td><td>".$this->rechnungs_empfaenger_name."</td><td align=right>$netto €</td><td align=right>$brutto €</td><td align=right>$skonto_betrag €</td></tr>\n";
		}
		
		echo "</table>\n";
		}
		}
		
/*Alle Eingangskassenbelege werden angezeigt*/
		function kassenbelege_anzeigen_ausgang($kassen_id){
		/*Zählen aller Zeilen*/
		$result = mysql_query ("SELECT * FROM RECHNUNGEN WHERE AKTUELL = '1' AND RECHNUNGSTYP='Kassenbeleg' AND AUSSTELLER_TYP='Kasse' AND AUSSTELLER_ID='$kassen_id' ORDER BY BELEG_NR DESC");
		 /*$result = mysql_query ("SELECT RECHNUNGEN. * , COUNT( RECHNUNGEN_POSITIONEN.POSITION ) AS ANZAHL_POSITIONEN
FROM RECHNUNGEN, RECHNUNGEN_POSITIONEN
WHERE RECHNUNGEN.BELEG_NR = '1' && RECHNUNGEN_POSITIONEN.BELEG_NR = '1'
GROUP BY RECHNUNGEN.BELEG_NR DESC"); 
		*/	
		$numrows1 = mysql_numrows($result);
		/*Seitennavigation mit  Limit erstellen*/
		echo "<table><tr><td>Anzahl aller Belege: $numrows1</td></tr><tr><td>\n";
		$navi = new blaettern(0,$numrows1,14, '?daten=kasse&option=erfasste_belege');
		echo "</td></tr></table>\n";
		$result = mysql_query ("SELECT * FROM RECHNUNGEN WHERE AKTUELL = '1' AND RECHNUNGSTYP='Kassenbeleg' AND AUSSTELLER_TYP='Kasse' AND AUSSTELLER_ID='$kassen_id' ORDER BY BELEG_NR DESC ".$navi->limit."");	
		$numrows = mysql_numrows($result);
		
		if($numrows>0){
		while ($row = mysql_fetch_assoc($result)) $my_array[] = $row;
		echo "<table class=rechnungen>\n";
		echo "<tr class=feldernamen><td>BNr</td><td>TYP</td><td>Beleg.Nr</td><td>Fälig</td><td>Von</td><td>An</td><td width=80>Netto</td><td width=80>Brutto</td><td width=80>Skonto</td></tr>\n";
		for($a=0;$a<count($my_array);$a++){
		$belegnr = $my_array[$a][BELEG_NR];
		$this->rechnung_grunddaten_holen($belegnr);
		
		
		$e_datum = date_mysql2german($my_array[$a][EINGANGSDATUM]);
		$r_datum = date_mysql2german($my_array[$a][RECHNUNGSDATUM]);
		$faellig_am = date_mysql2german($my_array[$a][FAELLIG_AM]);
		$beleg_link = "<a href=\"?daten=rechnungen&option=rechnungs_uebersicht&belegnr=".$my_array[$a]['BELEG_NR']."\">".$my_array[$a]['BELEG_NR']."</>\n";
		$netto = nummer_punkt2komma($my_array[$a][NETTO]);
		$brutto = nummer_punkt2komma($my_array[$a][BRUTTO]);
		$skonto_betrag = nummer_punkt2komma($my_array[$a][SKONTOBETRAG]);
		$rechnungsnummer = $my_array[$a][RECHNUNGSNUMMER];
		$rechnungstyp = $my_array[$a][RECHNUNGSTYP];
		echo "<tr><td>$beleg_link</td><td>$rechnungstyp</td><td>$rechnungsnummer</td><td><b>$faellig_am</b></td><td>".$this->rechnungs_aussteller_name."</td><td>".$this->rechnungs_empfaenger_name."</td><td align=right>$netto €</td><td align=right>$brutto €</td><td align=right>$skonto_betrag €</td></tr>\n";
		}
		
		echo "</table>\n";
		}
		}
		

function buchungsmaske_kasse($kassen_id){
$form = new formular;
$form->erstelle_formular("Buchungsmaske Kasseneinnahmen und Ausgaben", NULL);
$form->hidden_feld('kassen_id', $kassen_id);
$datum_feld =  'document.getElementById("datum").value';
$js_datum = "onchange='check_datum($datum_feld)'";
$form->text_feld('Datum', 'datum', '', '10', 'datum', $js_datum);
$this->dropdown_einausgaben('Zahlungstyp', 'zahlungstyp', 'zahlungstyp');
$form->text_feld('Betrag', 'betrag', '', '10', 'betrag', '');
$form->text_bereich('Beleg/Text', 'beleg_text', '', 10, 5, 'beleg_text');
$buchung = new buchen;
$js_typ = "onchange=\"list_kostentraeger('list_kostentraeger', this.value)\"";
$buchung->dropdown_kostentreager_typen('Kostenträgertyp', 'kostentraeger_typ', 'kostentraeger_typ', $js_typ);
$js_id = "";
$buchung->dropdown_kostentreager_ids('Kostenträger', 'kostentraeger_id', 'dd_kostentraeger_id', $js_id);
$form->hidden_feld("option", "kassendaten_gesendet");
$form->send_button("submit", "Speichern");
$form->ende_formular();	
}


function buchungsmaske_kasse_aendern($buchungs_dat){
$form = new formular;
$form->erstelle_formular("Buchungsmaske Kasseneinnahmen und Ausgaben", NULL);
$this->kassenbuch_dat_infos($buchungs_dat);
#print_r($this);

$form->hidden_feld("kassen_dat_alt", $buchungs_dat);
$form->hidden_feld("kassen_buch_id", $this->akt_kassenbuch_id);
if(!empty($this->kostentraeger_typ) && $this->kostentraeger_typ=='Rechnung'){
$form->hidden_feld("kostentraeger_typ", $this->kostentraeger_typ);
$form->hidden_feld("kostentraeger_id", $this->kostentraeger_id);
}

$form->hidden_feld('kassen_id', $this->akt_kassen_id);

$form->text_feld('Datum', 'datum', $this->akt_datum, '10', 'datum', '');
$this->dropdown_einausgaben_markiert('Zahlungstyp', 'zahlungstyp', 'zahlungstyp', $this->akt_zahlungstyp);
$form->text_feld('Betrag', 'betrag', $this->akt_betrag_komma, '10', 'betrag', '');
$form->text_bereich('Beleg/Text', 'beleg_text', $this->akt_beleg_text, 10, 5, 'beleg_text');
$akt_kostentraeger_bez = $this->kostentraeger_beschreibung($this->kostentraeger_typ, $this->kostentraeger_id);
$akt_kostentraeger_bez = str_replace("<b>","",$akt_kostentraeger_bez);
$akt_kostentraeger_bez = str_replace("</b>","",$akt_kostentraeger_bez);
if(empty($this->kostentraeger_typ) OR $this->kostentraeger_typ!='Rechnung'){
$form->text_feld_inaktiv('Kostenträger aktuell', 'kostentraeger', $akt_kostentraeger_bez, '30', 'kostentraeger');
$buchung = new buchen;
$js_typ = "onchange=\"list_kostentraeger('list_kostentraeger', this.value)\"";
$buchung->dropdown_kostentreager_typen('Kostenträgertyp wählen', 'kostentraeger_typ', 'kostentraeger_typ', $js_typ);
$js_id = "";
$buchung->dropdown_kostentreager_ids('Kostenträger', 'kostentraeger_id', 'dd_kostentraeger_id', $js_id);
}
$form->hidden_feld("option", "kassendaten_aendern");
$form->send_button("submit", "Änderungen speichern");
$form->ende_formular();	
}





function kassenbuch_dat_infos($buchungs_dat){
$result = mysql_query ("SELECT * FROM KASSEN_BUCH WHERE KASSEN_BUCH_DAT='$buchungs_dat' && AKTUELL='1' LIMIT 0,1");
		$row = mysql_fetch_assoc($result);
		$this->akt_kassenbuch_dat = $row[KASSEN_BUCH_DAT];
		$this->akt_kassenbuch_id = $row[KASSEN_BUCH_ID];
		$this->akt_kassen_id = $row[KASSEN_ID];
		$this->akt_zahlungstyp = $row[ZAHLUNGSTYP];
		$this->akt_betrag_punkt = $row[BETRAG];
		$this->akt_betrag_komma = nummer_punkt2komma($row[BETRAG]);
		$this->akt_datum = date_mysql2german($row[DATUM]);
		$this->akt_beleg_text = $row[BELEG_TEXT];
		$this->kostentraeger_typ = $row[KOSTENTRAEGER_TYP];
		$this->kostentraeger_id = $row[KOSTENTRAEGER_ID];
}



function dropdown_einausgaben($beschreibung, $name, $id){
echo "<label for=\"$id\">$beschreibung</label>";
		echo "<select name=\"$name\" id=\"$id\">";
		echo "<option value=\"Einnahmen\">Einnahmen</option>";
		echo "<option value=\"Ausgaben\">Ausgaben</option>";
		echo "</select>";	
}

function dropdown_einausgaben_markiert($beschreibung, $name, $id, $zahlungstyp){
echo "<label for=\"$id\">$beschreibung</label>";
		echo "<select name=\"$name\" id=\"$id\">";
		if($zahlungstyp=='Einnahmen'){
		echo "<option value=\"Einnahmen\">Einnahmen</option>";
		echo "<option value=\"Ausgaben\">Ausgaben</option>";
		}
		if($zahlungstyp=='Ausgaben'){
		echo "<option value=\"Ausgaben\">Ausgaben</option>";
		echo "<option value=\"Einnahmen\">Einnahmen</option>";
		}
		echo "</select>";	
}

function speichern_in_kassenbuch($kassen_id, $betrag, $datum, $zahlungstyp, $beleg_text, $kostentraeger_typ, $kostentraeger_bez)
{
	
$buchung = new buchen;
if($kostentraeger_typ !=='Rechnung'){
$kostentraeger_id = $buchung->kostentraeger_id_ermitteln($kostentraeger_typ, $kostentraeger_bez);	
}else{
$kostentraeger_id = $kostentraeger_bez;	
}

$letzte_kb_id = $this->letzte_kassenbuch_id($kassen_id);
$letzte_kb_id = $letzte_kb_id+1;

$datum = date_german2mysql($datum);
$betrag1 = nummer_komma2punkt($betrag);
$db_abfrage = "INSERT INTO KASSEN_BUCH VALUES (NULL, '$letzte_kb_id','$kassen_id', '$zahlungstyp','$betrag1', '$datum', '$beleg_text', '1', '$kostentraeger_typ', '$kostentraeger_id')";
$resultat = mysql_query($db_abfrage) or
           die(mysql_error());	    
echo "Betrag von $betrag € wurde ins Kassenbuch eingetragen!<br>";
echo "Sie werden zum Kassenbuch weitergeleitet!";
weiterleiten_in_sec('?daten=kasse&option=kassenbuch', 2);
}


function speichern_in_kassenbuch_id($kassen_id, $betrag, $datum, $zahlungstyp, $beleg_text, $kostentraeger_typ, $kostentraeger_bez, $letzte_kb_id)
{
	
$buchung = new buchen;
if($kostentraeger_typ !=='Rechnung'){
$kostentraeger_id = $buchung->kostentraeger_id_ermitteln($kostentraeger_typ, $kostentraeger_bez);	
}else{
$kostentraeger_id = $kostentraeger_bez;	
}

$datum = date_german2mysql($datum);
$betrag1 = nummer_komma2punkt($betrag);
$db_abfrage = "INSERT INTO KASSEN_BUCH VALUES (NULL, '$letzte_kb_id','$kassen_id', '$zahlungstyp','$betrag1', '$datum', '$beleg_text', '1', '$kostentraeger_typ', '$kostentraeger_id')";
$resultat = mysql_query($db_abfrage) or
           die(mysql_error());	    
echo "Betrag von $betrag € wurde ins Kassenbuch eingetragen!<br>";
echo "Sie werden zum Kassenbuch weitergeleitet!";
weiterleiten_in_sec('?daten=kasse&option=kassenbuch', 2);
}


function kassenbuch_dat_deaktivieren($buchungs_dat){
$db_abfrage = "UPDATE KASSEN_BUCH SET AKTUELL='0' WHERE KASSEN_BUCH_DAT='$buchungs_dat'";
$resultat = mysql_query($db_abfrage) or
           die(mysql_error());
protokollieren('KASSEN_BUCH', $buchungs_dat, $buchungs_dat);           		
echo "Alter Eintrag deaktiviert<br>";
}


function rechnung_in_kassenbuch($kassen_id, $betrag, $datum, $zahlungstyp, $beleg_text, $kostentraeger_typ, $kostentraeger_bez)
{
	
$buchung = new buchen;
if($kostentraeger_typ !=='Rechnung'){
$kostentraeger_id = $buchung->kostentraeger_id_ermitteln($kostentraeger_typ, $kostentraeger_bez);	
}else{
$kostentraeger_id = $kostentraeger_bez;	
}

$letzte_kb_id = $this->letzte_kassenbuch_id($kassen_id);
$letzte_kb_id = $letzte_kb_id+1;

$datum = date_german2mysql($datum);
$db_abfrage = "INSERT INTO KASSEN_BUCH VALUES (NULL, '$letzte_kb_id','$kassen_id', '$zahlungstyp','$betrag', '$datum', '$beleg_text', '1', '$kostentraeger_typ', '$kostentraeger_id')";
$resultat = mysql_query($db_abfrage) or
           die(mysql_error());	    
echo "Betrag von $betrag € wurde ins Kassenbuch eingetragen!<br>";
#echo "Sie werden zum Kassenbuch weitergeleitet!";
#weiterleiten_in_sec('?daten=kasse&option=kassenbuch', 2);
}

function letzte_kassenbuch_id($kassen_id){
$result = mysql_query ("SELECT KASSEN_BUCH_ID FROM KASSEN_BUCH WHERE KASSEN_ID='$kassen_id' && AKTUELL='1' ORDER BY KASSEN_BUCH_ID DESC LIMIT 0,1");
		$row = mysql_fetch_assoc($result);
		return $row[KASSEN_BUCH_ID];	
}

function kassenbuch_anzeigen($jahr, $kassen_id){

$vorjahr = $jahr - 1;

$result = mysql_query ("SELECT * FROM KASSEN_BUCH WHERE KASSEN_ID = '$kassen_id' && AKTUELL='1' && DATUM BETWEEN '$jahr-01-01' AND '$jahr-12-31' ORDER BY DATUM, KASSEN_BUCH_ID ASC");
		$numrows = mysql_numrows($result);
		if($numrows>0){
		while ($row = mysql_fetch_assoc($result)) $my_array[] = $row;
		$anzahl = count($my_array);
		echo "<table>";
		echo "<tr class=feldernamen><td>Nr.</td><td>Datum</td><td>Einnahmen</td><td>Ausgaben</td><td>Beleg / Text</td><td>Info</td><td>Optionen</td></tr>";
		$vorjahr = $jahr - 1;
		$kassenstand_vorjahr = $this->kassenstand_vorjahr($vorjahr,$kassen_id);
		$kassenstand_vorjahr_komma = nummer_punkt2komma($kassenstand_vorjahr);
		echo "<tr><td></td><td>01.01.$jahr</td>";
		echo "<td>$kassenstand_vorjahr_komma</td><td></td>";
		echo "<td><b>Kassenstand Vorjahr</b></td><td></td></tr>";
		$zaehler=0;		
		for($a=0;$a<$anzahl;$a++){
		$zaehler++;
		$zeile = $a+1;
		$dat = $my_array[$a][KASSEN_BUCH_DAT 	];
		$datum = $my_array[$a][DATUM];
		$datum = date_mysql2german($datum);
		$betrag = $my_array[$a][BETRAG];
		$betrag = nummer_punkt2komma($betrag);
		$zahlungstyp = $my_array[$a][ZAHLUNGSTYP];
		$beleg_text = $my_array[$a][BELEG_TEXT];
		$kostentraeger_typ = $my_array[$a][KOSTENTRAEGER_TYP];
		$kostentraeger_id = $my_array[$a][KOSTENTRAEGER_ID];
		if($kostentraeger_typ == 'Rechnung'){
		$info_link = "<a href=\"?daten=rechnungen&option=rechnungs_uebersicht&belegnr=$kostentraeger_id\">$kostentraeger_typ</a>";	
		}else{
		$info_link = $this->kostentraeger_beschreibung($kostentraeger_typ, $kostentraeger_id);
		}
		$aendern_link = "<a href=\"?daten=kasse&option=kasseneintrag_aendern&eintrag_dat=$dat\">Ändern</a>";
		$loeschen_link = "<a href=\"?daten=kasse&option=kasseneintrag_loeschen&eintrag_dat=$dat\">Löschen</a>";
		if($zaehler == 1){
		echo "<tr class=\"zeile1\"><td>$zeile</td><td>$datum</td>";
		}
		if($zaehler == 2){
		echo "<tr class=\"zeile2\"><td>$zeile</td><td>$datum</td>";
		$zaehler=0;
		}
		if($zahlungstyp=='Einnahmen'){
		echo "<td>$betrag</td><td></td><td>$beleg_text</td><td>$info_link</td><td>$aendern_link $loeschen_link</td></tr>";	
		}
		if($zahlungstyp=='Ausgaben'){
		echo "<td></td><td>$betrag</td><td>$beleg_text</td><td>$info_link</td><td>$aendern_link $loeschen_link</td></tr>";
		}
		
			
		}
		$summe_einnahmen = $this->summe_einnahmen($jahr, $kassen_id);
		$summe_einnahmen = $summe_einnahmen + $kassenstand_vorjahr;
		$summe_ausgaben = $this->summe_ausgaben($jahr, $kassen_id);
		$kassenstand = $summe_einnahmen - $summe_ausgaben;
		#$kassenstand = number_format($kassenstand, ',' , '2', '');
		#$summe_einnahmen = nummer_punkt2komma($summe_einnahmen);
		#$summe_ausgaben = nummer_punkt2komma($summe_ausgaben);
		#$kassenstand = sprintf("%01.2f", $kassenstand);
		$kassenstand = nummer_punkt2komma($kassenstand);
		echo "<tr class=feldernamen><td></td><td></td><td>$summe_einnahmen €</td><td>$summe_ausgaben €</td><td>Kassenstand: $kassenstand €</td><td></td><td></td></tr>";
		echo "</table>";
		}
		else{
		echo "kassenbuch leer";
		}
	
}

function anzahl_buchungen_bis_monat($monat, $jahr, $kassen_id){
$result = mysql_query ("SELECT * FROM KASSEN_BUCH WHERE KASSEN_ID = '$kassen_id' && AKTUELL='1' && DATE_FORMAT(DATUM, '%Y-%m') < '$jahr-$monat' && DATE_FORMAT(DATUM, '%Y') = '$jahr'  ORDER BY DATUM, KASSEN_BUCH_ID ASC");	
$numrows = mysql_numrows($result);
return $numrows;
}


function monatskassenbuch_anzeigen($monat, $jahr, $kassen_id){
$vorjahr = $jahr - 1;
$zeile = $this->anzahl_buchungen_bis_monat($monat, $jahr, $kassen_id);
$zeile = $zeile+1;

$result = mysql_query ("SELECT * FROM KASSEN_BUCH WHERE KASSEN_ID = '$kassen_id' && AKTUELL='1' && DATE_FORMAT(DATUM, '%Y-%m') = '$jahr-$monat' ORDER BY DATUM, KASSEN_BUCH_ID ASC");
		$numrows = mysql_numrows($result);
		if($numrows){
		while ($row = mysql_fetch_assoc($result)) $my_array[] = $row;
		$anzahl = count($my_array);
		echo "<table>";
		echo "<tr class=feldernamen><td>Nr.</td><td>Datum</td><td>Einnahmen</td><td>Ausgaben</td><td>Beleg / Text</td><td>Info</td><td>Optionen</td></tr>";
		$vorjahr = $jahr - 1;
		$kassenstand_vorjahr = $this->kassenstand_vorjahr($vorjahr,$kassen_id);
		$kassenstand_vorjahr_komma = nummer_punkt2komma($kassenstand_vorjahr);
		$kassenstand_vormonat = $this->kassenstand_vormonat($monat, $jahr, $kassen_id);
		echo "<tr><td></td><td>01.01.$jahr</td>";
		echo "<td>$kassenstand_vorjahr_komma</td><td></td>";
		#echo "<td>$kassenstand_vormonat</td><td></td>";
		echo "<td><b>Kassenstand Vorjahr</b></td><td></td></tr>";
		$zaehler=0;		
		for($a=0;$a<$anzahl;$a++){
		$zaehler++;
		#$zeile = $a+1;
		
		$dat = $my_array[$a][KASSEN_BUCH_DAT 	];
		$datum = $my_array[$a][DATUM];
		$datum = date_mysql2german($datum);
		$betrag = $my_array[$a][BETRAG];
		$betrag = nummer_punkt2komma($betrag);
		$zahlungstyp = $my_array[$a][ZAHLUNGSTYP];
		$beleg_text = $my_array[$a][BELEG_TEXT];
		$kostentraeger_typ = $my_array[$a][KOSTENTRAEGER_TYP];
		$kostentraeger_id = $my_array[$a][KOSTENTRAEGER_ID];
		if($kostentraeger_typ == 'Rechnung'){
		$info_link = "<a href=\"?daten=rechnungen&option=rechnungs_uebersicht&belegnr=$kostentraeger_id\">$kostentraeger_typ</a>";	
		}else{
		$info_link = $this->kostentraeger_beschreibung($kostentraeger_typ, $kostentraeger_id);
		}
		$aendern_link = "<a href=\"?daten=kasse&option=kasseneintrag_aendern&eintrag_dat=$dat\">Ändern</a>";
		$loeschen_link = "<a href=\"?daten=kasse&option=kasseneintrag_loeschen&eintrag_dat=$dat\">Löschen</a>";
		if($zaehler == 1){
		echo "<tr class=\"zeile1\"><td>$zeile</td><td>$datum</td>";
		}
		if($zaehler == 2){
		echo "<tr class=\"zeile2\"><td>$zeile</td><td>$datum</td>";
		$zaehler=0;
		}
		if($zahlungstyp=='Einnahmen'){
		echo "<td>$betrag</td><td></td><td>$beleg_text</td><td>$info_link</td><td>$aendern_link $loeschen_link</td></tr>";	
		}
		if($zahlungstyp=='Ausgaben'){
		echo "<td></td><td>$betrag</td><td>$beleg_text</td><td>$info_link</td><td>$aendern_link $loeschen_link</td></tr>";
		}
		
		$zeile = $zeile+1;	
		}
		$summe_einnahmen = $this->summe_einnahmen($jahr, $kassen_id);
		$summe_einnahmen = $summe_einnahmen + $kassenstand_vorjahr;
		$summe_ausgaben = $this->summe_ausgaben($jahr, $kassen_id);
		$kassenstand = $summe_einnahmen - $summe_ausgaben;
		#$kassenstand = number_format($kassenstand, ',' , '2', '');
		#$summe_einnahmen = nummer_punkt2komma($summe_einnahmen);
		#$summe_ausgaben = nummer_punkt2komma($summe_ausgaben);
		#$kassenstand = sprintf("%01.2f", $kassenstand);
		$kassenstand = nummer_punkt2komma($kassenstand);
		echo "<tr class=feldernamen><td></td><td></td><td>$summe_einnahmen €</td><td>$summe_ausgaben €</td><td>Kassenstand: $kassenstand €</td><td></td><td></td></tr>";
		echo "</table>";
		}
		else{
		echo "kassenbuch leer";
		}
	
}


function kassenbuch_als_excel($jahr, $kassen_id){
$fileName = 'kasse.xls';
ob_clean(); //ausgabepuffer leeren
header("Content-type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=$fileName");
$vorjahr = $jahr - 1;
$result = mysql_query ("SELECT * FROM KASSEN_BUCH WHERE KASSEN_ID = '$kassen_id' && AKTUELL='1' && DATUM BETWEEN '$jahr-01-01' AND '$jahr-12-31' ORDER BY DATUM, KASSEN_BUCH_ID ASC");
		$numrows = mysql_numrows($result);
		if($numrows>0){
		while ($row = mysql_fetch_assoc($result)) $my_array[] = $row;
		$anzahl = count($my_array);
		echo "<table>";
		echo "<tr class=feldernamen><td>Nr.</td><td>Datum</td><td>Einnahmen</td><td>Ausgaben</td><td>Beleg / Text</td><td>Info</td><td>Optionen</td></tr>";
		$vorjahr = $jahr - 1;
		$kassenstand_vorjahr = $this->kassenstand_vorjahr($vorjahr,$kassen_id);
		echo "<tr><td></td><td>01.01.$jahr</td>";
		echo "<td>$kassenstand_vorjahr</td><td></td>";
		echo "<td><b>Kassenstand Vorjahr</b></td><td></td></tr>";
		$zaehler=0;		
		for($a=0;$a<$anzahl;$a++){
		$zaehler++;
		$zeile = $a+1;
		$dat = $my_array[$a][KASSEN_BUCH_DAT 	];
		$datum = $my_array[$a][DATUM];
		$datum = date_mysql2german($datum);
		$betrag = $my_array[$a][BETRAG];
		$betrag = nummer_punkt2komma($betrag);
		$zahlungstyp = $my_array[$a][ZAHLUNGSTYP];
		$beleg_text = $my_array[$a][BELEG_TEXT];
		$kostentraeger_typ = $my_array[$a][KOSTENTRAEGER_TYP];
		$kostentraeger_id = $my_array[$a][KOSTENTRAEGER_ID];
		if($kostentraeger_typ == 'Rechnung'){
		$info_link = "<a href=\"?daten=rechnungen&option=rechnungs_uebersicht&belegnr=$kostentraeger_id\">$kostentraeger_typ</a>";	
		}else{
		$info_link = $this->kostentraeger_beschreibung($kostentraeger_typ, $kostentraeger_id);
		}
		$aendern_link = "<a href=\"?daten=kasse&option=kasseneintrag_aendern&eintrag_dat=$dat\">Ändern</a>";
		$loeschen_link = "<a href=\"?daten=kasse&option=kasseneintrag_loeschen&eintrag_dat=$dat\">Löschen</a>";
		if($zaehler == 1){
		echo "<tr class=\"zeile1\"><td>$zeile</td><td>$datum</td>";
		}
		if($zaehler == 2){
		echo "<tr class=\"zeile2\"><td>$zeile</td><td>$datum</td>";
		$zaehler=0;
		}
		if($zahlungstyp=='Einnahmen'){
		echo "<td>$betrag</td><td></td><td>$beleg_text</td><td>$info_link</td><td>$aendern_link $loeschen_link</td></tr>";	
		}
		if($zahlungstyp=='Ausgaben'){
		echo "<td></td><td>$betrag</td><td>$beleg_text</td><td>$info_link</td><td>$aendern_link $loeschen_link</td></tr>";
		}
		
			
		}
		$summe_einnahmen = $this->summe_einnahmen($jahr, $kassen_id);
		$summe_einnahmen = $summe_einnahmen + $kassenstand_vorjahr;
		$summe_ausgaben = $this->summe_ausgaben($jahr, $kassen_id);
		$kassenstand = $summe_einnahmen - $summe_ausgaben;
		#$kassenstand = number_format($kassenstand, ',' , '2', '');
		$summe_einnahmen = nummer_punkt2komma($summe_einnahmen);
		$summe_ausgaben = nummer_punkt2komma($summe_ausgaben);
		$kassenstand = sprintf("%01.2f", $kassenstand);
		$kassenstand = nummer_punkt2komma($kassenstand);
		echo "<tr class=feldernamen><td></td><td></td><td>$summe_einnahmen €</td><td>$summe_ausgaben €</td><td>Kassenstand: $kassenstand €</td><td></td><td></td></tr>";
		echo "</table>";
		}
		else{
		echo "kassenbuch leer";
		}
	
}

function summe_einnahmen($jahr,$kassen_id){
$result = mysql_query ("SELECT SUM(BETRAG) AS BETRAG FROM KASSEN_BUCH WHERE KASSEN_ID='$kassen_id' && ZAHLUNGSTYP='Einnahmen' && AKTUELL='1' && DATUM BETWEEN '$jahr-01-01' AND '$jahr-12-31' ORDER BY KASSEN_BUCH_ID DESC LIMIT 0,1");
		$row = mysql_fetch_assoc($result);
		return $row[BETRAG];		
}

function summe_ausgaben($jahr, $kassen_id){
$result = mysql_query ("SELECT SUM(BETRAG) AS BETRAG FROM KASSEN_BUCH WHERE KASSEN_ID='$kassen_id' && ZAHLUNGSTYP='Ausgaben' && AKTUELL='1' && DATUM BETWEEN '$jahr-01-01' AND '$jahr-12-31' ORDER BY KASSEN_BUCH_ID DESC LIMIT 0,1");
		$row = mysql_fetch_assoc($result);
		return $row[BETRAG];		
}

function summe_einnahmen_bis_vorjahr($vorjahr,$kassen_id){
$result = mysql_query ("SELECT SUM(BETRAG) AS BETRAG FROM KASSEN_BUCH WHERE KASSEN_ID='$kassen_id' && ZAHLUNGSTYP='Einnahmen' && AKTUELL='1' && DATUM <='$vorjahr-12-31'");
		$row = mysql_fetch_assoc($result);
		return $row[BETRAG];		
}

function summe_ausgaben_bis_vorjahr($vorjahr,$kassen_id){
$result = mysql_query ("SELECT SUM(BETRAG) AS BETRAG FROM KASSEN_BUCH WHERE KASSEN_ID='$kassen_id' && ZAHLUNGSTYP='Ausgaben' && AKTUELL='1' && DATUM <='$vorjahr-12-31'");
		$row = mysql_fetch_assoc($result);
		return $row[BETRAG];		
}

function summe_ausgaben_bis_monat($jahr,$monat, $kassen_id){
$result = mysql_query ("SELECT SUM(BETRAG) AS BETRAG FROM KASSEN_BUCH WHERE KASSEN_ID='$kassen_id' && ZAHLUNGSTYP='Ausgaben' && AKTUELL='1' && DATE_FORMAT(DATUM, '%Y-%m') < '$jahr-$monat'");
		$row = mysql_fetch_assoc($result);
		return $row[BETRAG];		
}

function summe_einnahmen_bis_monat($jahr,$monat,$kassen_id){
$result = mysql_query ("SELECT SUM(BETRAG) AS BETRAG FROM KASSEN_BUCH WHERE KASSEN_ID='$kassen_id' && ZAHLUNGSTYP='Einnahmen' && AKTUELL='1' && DATE_FORMAT(DATUM, '%y-%m') < '$jahr-$monat'");
		$row = mysql_fetch_assoc($result);
		return $row[BETRAG];		
}

function kassenstand_vormonat($aktmonat, $jahr, $kassen_id){
if($aktmonat<2){
	$vormonat = '12';
	$jahr = $jahr -1;
}else{
	$vormonat = $aktmonat -1;
}

$einnahmen_bis_vormonat = $this->summe_einnahmen_bis_monat($jahr, $vormonat, $kassen_id);
$ausgaben_bis_vormonat = $this->summe_ausgaben_bis_monat($jahr,$vormonat, $kassen_id);
$kassenstand_monat = $einnahmen_bis_vormonat - $ausgaben_bis_vormonat;
return $kassenstand_monat;		
}


function kassenstand_vorjahr($vorjahr,$kassen_id){
$einnahmen_vorjahr = $this->summe_einnahmen_bis_vorjahr($vorjahr,$kassen_id);
$ausgaben_vorjahr = $this->summe_ausgaben_bis_vorjahr($vorjahr,$kassen_id);
$kassenstand_vorjahr = $einnahmen_vorjahr - $ausgaben_vorjahr;
return $kassenstand_vorjahr;		
}

function kassen_auswahl(){
if(isset($_REQUEST[kasse]) && !empty($_REQUEST[kasse])){
   		$_SESSION[kasse] = $_REQUEST[kasse];
   	}

$form = new formular;
if(!isset($_SESSION[kasse])){
$form->erstelle_formular("Kasse wählen", NULL);
}else{
$form->erstelle_formular("Kassenauswahl - Aktuell: Kasse $_SESSION[kasse]", NULL);	
}
$result = mysql_query ("SELECT KASSEN_ID, KASSEN_NAME, KASSEN_VERWALTER FROM `KASSEN` WHERE AKTUELL = '1'");
	    $numrows = mysql_numrows($result);
		echo "<p class=\"objekt_auswahl\">";
		if($numrows){
		while($row = mysql_fetch_assoc($result)){
		$kassen_link = "<a class=\"objekt_auswahl_buchung\" href=\"?daten=kasse&kasse=$row[KASSEN_ID]&option=kassenbuch\">$row[KASSEN_NAME] - $row[KASSEN_VERWALTER]</a>";
		echo "| $kassen_link ";
		 }
		echo "</p>";
		}else{
		echo "Keine Kasse vorhanden";
			return FALSE;
		}		
$form->ende_formular();
}

function kostentraeger_beschreibung($kostentraeger_typ, $kostentraeger_id){
	if($kostentraeger_typ=='Objekt'){
	$a = new objekt;
	$k_bezeichnung = $a->get_objekt_name($kostentraeger_id);
	return "<b>$k_bezeichnung</b>";
	}
	if($kostentraeger_typ=='Haus'){
	$a = new haus;
	$a->get_haus_info($kostentraeger_id);
	$k_bezeichnung = "<b>$a->haus_strasse $a->haus_nummer $a->haus_stadt</b>";
	return $k_bezeichnung;
	}
	if($kostentraeger_typ=='Einheit'){
	$a = new einheit;
	$a->get_einheit_info($kostentraeger_id);
	$k_bezeichnung = "<b>$a->einheit_kurzname</b>";
	return $k_bezeichnung;
	}
	
}

}//end class kasse


?>
