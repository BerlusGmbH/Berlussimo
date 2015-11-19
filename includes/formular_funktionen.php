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
 * @filesource   $HeadURL: http://192.168.2.52/svn/berlussimo_1/tags/02.11.2010 - Downloadversion 0.27/includes/formular_funktionen.php $
 * @version      $Revision: 15 $
 * @modifiedby   $LastChangedBy: sivac $
 * @lastmodified $Date: 2011-07-07 10:41:33 +0200 (Do, 07 Jul 2011) $
 * 
 */



function erstelle_formular($name, $action){
echo "<table class=\"formular_tabelle\">\n<tr><td>";
#$self = $_SERVER['PHP_SELF'];
$scriptname = $_SERVER['REQUEST_URI'];
$servername = $_SERVER['SERVER_NAME'];
$serverport = $_SERVER['SERVER_PORT'];

$self = "http://$servername:$serverport$scriptname";

#$self = "http://$servername$scriptname";

	if(!isset($action)){
	echo "<form name=\"$name\" action=\"$self\"  method=\"post\">\n";
	}else{
	echo "<form name=\"$name\" action=\"$action\" method=\"post\">\n";
	}
echo "</td></tr>\n";
}

function ende_formular(){
	echo "</form></table>\n";
}
function erstelle_hiddenfeld($name, $wert){
	echo "<input type=\"hidden\" name=\"$name\" value=\"$wert\">\n";
}
function erstelle_button($name, $wert, $onclick){
echo "<input type=\"button\" name=\"$name\" value=\"$wert\" onclick=\"\">";
}

function erstelle_back_button(){
echo "<input type=\"button\" name=\"zurueck\" value=\"Abbrechen und Zurück\" onclick=\"javascript:history.back()\" class=\"buttons\">";
}


function erstelle_eingabefeld($beschreibung, $name, $wert, $size){
	echo "<tr><td>$beschreibung:</td><td><input type=\"text\" name=\"$name\" value=\"$wert\" size=\"$size\"></td></tr>\n";
}
function erstelle_submit_button($name, $wert){
	echo "<tr><td colspan=2><input type=\"submit\" name=\"$name\" value=\"$wert\" class=\"buttons\">";
	erstelle_back_button();
	echo "</td></tr>\n";
}

function erstelle_submit_button_nur($name, $wert){
	echo "<tr><td colspan=2><input type=\"submit\" name=\"$name\" value=\"$wert\" class=\"buttons\">";
	echo "</td></tr>\n";
}


function objekt_kurzname_anzahl($kurzname){
$db_abfrage = "SELECT OBJEKT_KURZNAME FROM OBJEKT WHERE OBJEKT_KURZNAME LIKE '$kurzname' && OBJEKT_AKTUELL='1'";
$resultat = mysql_query($db_abfrage) or
           die(mysql_error());

$numrows = mysql_numrows($resultat);
return $numrows;
}

function letzte_obj_id(){
$db_abfrage = "SELECT OBJEKT_ID FROM OBJEKT ORDER BY OBJEKT_ID DESC LIMIT 0,1";
$resultat = mysql_query($db_abfrage) or
           die(mysql_error());
while (list ($OBJEKT_ID) = mysql_fetch_row($resultat))
return $OBJEKT_ID;	
}

function check_objekt_kurzname($kurzname){
$db_abfrage = "SELECT OBJEKT_KURZNAME FROM OBJEKT ORDER BY OBJEKT_ID WHERE OBJEKT_KURZNAME='$kurzname' && OBJEKT_AKTUELL=1";
$resultat = mysql_query($db_abfrage) or
           die(mysql_error());
$numrows = mysql_numrows($resultat);
return $numrows;	
}


function neues_objekt_anlegen($objekt_kurzname, $eigentuemer){
$letzte_obj_id = letzte_obj_id();
$letzte_obj_id = $letzte_obj_id + 1;

$db_abfrage="INSERT INTO OBJEKT (OBJEKT_DAT, OBJEKT_ID, OBJEKT_AKTUELL, OBJEKT_KURZNAME, EIGENTUEMER_PARTNER) VALUES (NULL,'$letzte_obj_id','1', '$objekt_kurzname', '$eigentuemer')";
$resultat = mysql_query($db_abfrage) or
           die(mysql_error());

$obj_dat_neu = letzte_objekt_dat_kurzname($objekt_kurzname);
protokollieren("OBJEKT", $obj_dat_neu, 0);
}

function liste_aktueller_objekte_edit(){
$db_abfrage = "SELECT OBJEKT_DAT, OBJEKT_ID, OBJEKT_KURZNAME FROM OBJEKT WHERE OBJEKT_AKTUELL='1' ORDER BY OBJEKT_KURZNAME ASC ";
$resultat = mysql_query($db_abfrage) or
           die(mysql_error());
while (list ($OBJEKT_DAT, $OBJEKT_ID, $OBJEKT_KURZNAME) = mysql_fetch_row($resultat))
echo "$OBJEKT_KURZNAME - <a href=?formular=objekte&daten_rein=aendern&obj_id=$OBJEKT_ID>Edit </a> - <a href=?formular=objekte&daten_rein=loeschen&obj_dat=$OBJEKT_DAT>Löschen</a><br>\n";	
}

function objekt_zum_aendern_holen($obj_id){
$db_abfrage = "SELECT OBJEKT_DAT, OBJEKT_ID, OBJEKT_AKTUELL, OBJEKT_KURZNAME FROM OBJEKT WHERE OBJEKT_ID='$obj_id' ORDER BY OBJEKT_DAT DESC LIMIT 0,1";
$resultat = mysql_query($db_abfrage) or
           die(mysql_error());
erstelle_formular(NULL, NULL);
while (list ($OBJEKT_DAT, $OBJEKT_ID, $OBJEKT_AKTUELL, $OBJEKT_KURZNAME) = mysql_fetch_row($resultat)){
#echo "$OBJEKT_KURZNAME $OBJEKT_ID, $OBJEKT_AKTUELL, $OBJEKT_KURZNAME<br>\n";	
echo "<input type=\"hidden\" name=\"objekt_dat\" value=\"$OBJEKT_DAT\"><br>\n";
echo "<input type=\"text\" name=\"objekt_kurzname\" value=\"$OBJEKT_KURZNAME\" size=\"20\"><br>\n";
}
erstelle_submit_button("submit_update_objekt", "Ändern");
ende_formular();
}


function objekt_update_kurzname($obj_dat, $obj_id, $obj_kurzname){
$obj_kurzname = trim($obj_kurzname);
if($obj_kurzname != ''){
$db_abfrage = "UPDATE OBJEKT SET OBJEKT_AKTUELL='0' WHERE OBJEKT_DAT='$obj_dat'";
$resultat = mysql_query($db_abfrage) or
           die(mysql_error());	
$db_abfrage="INSERT INTO OBJEKT (OBJEKT_DAT, OBJEKT_ID, OBJEKT_AKTUELL, OBJEKT_KURZNAME) VALUES (NULL,'$obj_id','1', '$obj_kurzname')";
$resultat = mysql_query($db_abfrage) or
           die(mysql_error());

$dat_dat_alt = $obj_dat;
$dat_dat_neu = letzte_objekt_dat();
protokollieren("OBJEKT", $dat_dat_neu, $dat_dat_alt);
	} 		else {
			fehlermeldung_ausgeben("Bitte tragen Sie einen Objektnamen ein, Objekte ohne Namen sind nicht erlaubt.");
			 }
}
function objekt_loeschen($obj_dat){
$db_abfrage = "UPDATE OBJEKT SET OBJEKT_AKTUELL='0' WHERE OBJEKT_DAT='$obj_dat'";
$resultat = mysql_query($db_abfrage) or
           die(mysql_error());	
protokollieren("OBJECT", $obj_dat, $obj_dat);
}

function objekt_liste_dropdown(){
$db_abfrage = "SELECT OBJEKT_DAT, OBJEKT_ID, OBJEKT_KURZNAME FROM OBJEKT WHERE OBJEKT_AKTUELL='1' ORDER BY OBJEKT_KURZNAME ASC ";
$resultat = mysql_query($db_abfrage) or
           die(mysql_error());
echo "<b>Objekt auswählen:</b><br>\n ";
echo "<select name=\"haus_objekt\" size=\"1\">\n";
while (list ($OBJEKT_DAT, $OBJEKT_ID, $OBJEKT_KURZNAME) = mysql_fetch_row($resultat)){
echo "<option value=\"$OBJEKT_ID\">$OBJEKT_KURZNAME</option>\n";	
}
echo "</select><br>";
}	


function detail_drop_down_kategorie(){
echo "<tr><td>Detailzugehörigkeit:</td><td> <select name=\"bereich_kategorie\" size=\"1\">\n";	
echo "<option value=\"OBJEKT\">OBJEKT</option>\n";
echo "<option value=\"HAUS\">HAUS</option>\n";
echo "<option value=\"EINHEIT\">EINHEIT</option>\n";
echo "<option value=\"PERSON\">PERSON</option>\n";
echo "<option value=\"MIETVERTRAG\">MIETVERTRAG</option>\n";
echo "<option value=\"PARTNER_LIEFERANT\">PARTNER/LIEFERANT</option>\n";
echo "<option value=\"SEPA_UEBERWEISUNG\">SEPA_UEBERWEISUNG</option>\n";
echo "</td></tr></select>";
}


function detail_drop_down_kategorie_db(){
$db_abfrage = "SELECT DETAIL_KAT_ID, DETAIL_KAT_NAME FROM DETAIL_KATEGORIEN WHERE DETAIL_KAT_AKTUELL='1' ORDER BY DETAIL_KAT_NAME ASC";
$resultat = mysql_query($db_abfrage) or
           die(mysql_error());
$numrows = mysql_numrows($resultat);
if($numrows < 1) {
fehlermeldung_ausgeben("Keine Hauptkategorien");
erstelle_back_button;
}else{
echo "<tr><td>Detailzugehörigkeit:</td><td> <select name=\"bereich_kategorie\" size=\"1\">\n";
  while (list ($DETAIL_KAT_ID, $DETAIL_KAT_NAME) = mysql_fetch_row($resultat)){
echo "<option value=\"$DETAIL_KAT_ID\">$DETAIL_KAT_NAME</option>\n";
  }
echo "</td></tr></select>";
}
}



function objekt_auswahl_form(){
	erstelle_formular(NULL, NULL);
	objekt_liste_dropdown();
	erstelle_submit_button("submit_objekt_auswahl", "Auswählen");
	ende_formular();
}

function haeuser_liste_dropdown($obj_id){
erstelle_formular("haus_auswahl", NULL);

$db_abfrage = "SELECT HAUS_DAT, HAUS_ID, HAUS_STRASSE, HAUS_NUMMER FROM HAUS WHERE OBJEKT_ID='$obj_id' && HAUS_AKTUELL='1' ORDER BY HAUS_NUMMER ASC";
$resultat = mysql_query($db_abfrage) or
           die(mysql_error());
$numrows = mysql_numrows($resultat);
if($numrows < 1) {
	echo "<h2 class=\"fehler\">Keine Häuser im ausgewählten Objekt</h2><br>\n";
	echo "Erst Haus im Objekt anlegen - <a href=\"?formular=haus&daten_rein=anlegen\">Hauseningabe hier&nbsp;</a>\n<br>\n";
}else{
echo "<select name=\"haeuser\" size=\"1\">\n";
  while (list ($HAUS_DAT, $HAUS_ID, $HAUS_STRASSE, $HAUS_NUMMER) = mysql_fetch_row($resultat)){
  #echo "$HAUS_DAT, $HAUS_ID, $HAUS_STRASSE, $HAUS_NUMMER<br>";
  echo "<option value=\"$HAUS_ID\">$HAUS_STRASSE $HAUS_NUMMER</option>\n";
  }
echo "</select>\n";

erstelle_submit_button("haus_auswahl", "Senden");
ende_formular();
}
}



function einheit_eingabe_form($haus_id){
erstelle_formular(NULL, NULL);
erstelle_hiddenfeld("haus_id", "$haus_id");
erstelle_eingabefeld("Kurzname", "einheit_kurzname", "", "50");
erstelle_eingabefeld("Lage (V1L)", "einheit_lage", "", "50");
erstelle_eingabefeld("m²", "einheit_qm", "", "5");
erstelle_submit_button("submit_einheit", "Senden");
ende_formular();	
} 

function letzte_einheit_id(){
$db_abfrage = "SELECT EINHEIT_ID FROM EINHEIT ORDER BY EINHEIT_ID DESC LIMIT 0,1";
$resultat = mysql_query($db_abfrage) or
           die(mysql_error());
while (list ($EINHEIT_ID) = mysql_fetch_row($resultat))
return $EINHEIT_ID;		
}

function kurzname_exist($einheit_kurzname){
$db_abfrage = "SELECT EINHEIT_KURZNAME FROM EINHEIT WHERE EINHEIT_KURZNAME LIKE '$einheit_kurzname' LIMIT 0,1";
$resultat = mysql_query($db_abfrage) or
           die(mysql_error());
$numrows = mysql_numrows($resultat);
return $numrows;
}

function neue_einheit_in_db($haus_id, $einheit_kurzname, $einheit_lage, $einheit_qm){
#echo "eingabe: $haus_id, $einheit_kurzname, $einheit_lage, $einheit_qm, $einheit_ausstattung";	
$kurzname = kurzname_exist($einheit_kurzname);
if($kurzname > 0){
	echo "Einheit mit dem selben Kurznamen existiert!!!<br>";
	backlink();
	
}else{

$einheit_id = letzte_einheit_id();
$einheit_id = $einheit_id+1;
$dat_alt = letzte_einheit_dat_of_einheit_id($einheit_id);
$db_abfrage="INSERT INTO EINHEIT (EINHEIT_DAT, EINHEIT_ID, EINHEIT_QM, EINHEIT_LAGE, HAUS_ID, EINHEIT_AKTUELL, EINHEIT_KURZNAME) VALUES (NULL,'$einheit_id','$einheit_qm', '$einheit_lage', '$haus_id', '1', '$einheit_kurzname')";
$resultat = mysql_query($db_abfrage) or
           die(mysql_error());
$dat_neu = letzte_einheit_dat_of_einheit_id($einheit_id);
hinweis_ausgeben("Einheit $_POST[einheit_kurzname] mit der Lage $_POST[einheit_lage] und Größe von $_POST[einheit_qm]m² wurde angelegt.");
protokollieren('EINHEIT', $dat_neu, $dat_alt);
}
}
function einheit_liste_dropdown($haus_id){
if(isset($haus_id)){
$db_abfrage = "SELECT EINHEIT_ID, EINHEIT_KURZNAME FROM EINHEIT WHERE HAUS_ID='$haus_id' && EINHEIT_AKTUELL='1' ORDER BY EINHEIT_KURZNAME ASC ";
}else{
$db_abfrage = "SELECT EINHEIT_ID, EINHEIT_KURZNAME FROM EINHEIT WHERE EINHEIT_AKTUELL='1' ORDER BY EINHEIT_KURZNAME ASC ";
}
$resultat = mysql_query($db_abfrage) or
           die(mysql_error());
$numrows = mysql_numrows($resultat);
if($numrows < 1){
	echo "<h2 class=\"fehler\">Keine Einheiten im ausgewählten Haus</h2>";
	echo "<p class=\"hinweis\">Bitte zuerst Einheit im Haus anlegen - <a href=\"?formular=einheit&daten_rein=anlegen\">Einheit anlegen HIER&nbsp;</a></p><br>";
	
}else{
echo "<b>Einheit auswählen:</b><br>\n ";
echo "<select name=\"einheiten\" size=\"1\">\n";
while (list ($EINHEIT_ID, $EINHEIT_KURZNAME) = mysql_fetch_row($resultat)){
echo "<option value=\"$EINHEIT_ID\">$EINHEIT_KURZNAME</option>\n";	
}
echo "</select><br>";
}
}	

function einheit_auswahl_form($haus_id){
erstelle_formular(NULL, NULL);
einheit_liste_dropdown($haus_id);	
erstelle_submit_button("submit_einheit", "Bearbeiten");
ende_formular();
}

function einheit_auswaehlen($haus_id){
erstelle_formular(NULL, NULL);
einheit_liste_dropdown($haus_id);	
erstelle_submit_button("submit_einheit", "Auswählen");
ende_formular();
}


function einheit_aendern_form($einheit_id){
erstelle_formular(NULL, NULL);
erstelle_hiddenfeld("einheit_id", "$einheit_id");
$db_abfrage = "SELECT EINHEIT_DAT, EINHEIT_ID, EINHEIT_QM, EINHEIT_LAGE, HAUS_ID, EINHEIT_KURZNAME FROM EINHEIT WHERE EINHEIT_ID='$einheit_id' && EINHEIT_AKTUELL='1' ORDER BY EINHEIT_DAT DESC LIMIT 0,1";
$resultat = mysql_query($db_abfrage) or
           die(mysql_error());
while (list ($EINHEIT_DAT, $EINHEIT_ID, $EINHEIT_QM, $EINHEIT_LAGE, $HAUS_ID, $EINHEIT_KURZNAME) = mysql_fetch_row($resultat)){
erstelle_hiddenfeld("einheit_dat", "$EINHEIT_DAT");
erstelle_hiddenfeld("haus_id", "$HAUS_ID");
erstelle_eingabefeld("Kurzname", "einheit_kurzname", "$EINHEIT_KURZNAME", "50");
erstelle_eingabefeld("Lage (V1L)", "einheit_lage", "$EINHEIT_LAGE", "50");
erstelle_eingabefeld("m²", "einheit_qm", "$EINHEIT_QM", "5");
}
erstelle_submit_button("aendern_einheit", "Ändern");
ende_formular();	
}  
function einheit_deaktivieren($einheit_dat){
$db_abfrage = "UPDATE EINHEIT SET EINHEIT_AKTUELL='0' WHERE EINHEIT_DAT='$einheit_dat'";
$resultat = mysql_query($db_abfrage) or
           die(mysql_error());	
}

function letzte_einheit_dat(){
$db_abfrage = "SELECT EINHEIT_DAT FROM EINHEIT WHERE EINHEIT_AKTUELL='1' ORDER BY EINHEIT_ID DESC LIMIT 0,1";
$resultat = mysql_query($db_abfrage) or
           die(mysql_error());
while (list ($EINHEIT_DAT) = mysql_fetch_row($resultat))
return $EINHEIT_DAT;			
}

function einheit_geandert_in_db($einheit_dat, $einheit_id, $haus_id, $einheit_kurzname, $einheit_lage, $einheit_qm){
$db_abfrage="INSERT INTO EINHEIT (EINHEIT_DAT, EINHEIT_ID, EINHEIT_QM, EINHEIT_LAGE, HAUS_ID, EINHEIT_AKTUELL, EINHEIT_KURZNAME) VALUES (NULL,'$einheit_id','$einheit_qm', '$einheit_lage', '$haus_id', '1', '$einheit_kurzname')";
$resultat = mysql_query($db_abfrage) or
           die(mysql_error());
$akt_einheit_dat = letzte_einheit_dat();
protokollieren("EINHEIT", $akt_einheit_dat, $einheit_dat);
}

function detail_kategorien_form($kategorie){
if(empty($_REQUEST[submit_kat]) && empty($_REQUEST[submit_ukat])){
$db_abfrage = "SELECT DETAIL_KAT_ID, DETAIL_KAT_NAME FROM DETAIL_KATEGORIEN WHERE DETAIL_KAT_KATEGORIE='$kategorie' && DETAIL_KAT_AKTUELL='1' ORDER BY DETAIL_KAT_NAME ASC";
$resultat = mysql_query($db_abfrage) or
           die(mysql_error());
$numrows = mysql_numrows($resultat);
if($numrows > 0){
erstelle_formular(NULL, NULL);
echo "<tr><td>";
echo "<select name=\"detail_kat_id\" size=1>";
while (list ($DETAIL_KAT_ID, $DETAIL_KAT_NAME) = mysql_fetch_row($resultat)){
echo "<option value=\"$DETAIL_KAT_ID\" >$DETAIL_KAT_NAME</option>";	
 }
echo "</select>";
echo "</td></tr>";
erstelle_submit_button("submit_kat", "Weiter");
ende_formular();
 }
 }
###############step 2############
if(isset($_REQUEST[submit_kat])){
#print_r($_REQUEST);
$kat_id = $_REQUEST["detail_kat_id"];
$kat_name = get_kategorie_name($_REQUEST[detail_kat_id]);
$anzahl_ukat = check_unterkategorie($kat_id); 
if($anzahl_ukat>0){
unterkategorien_form($kat_id);
  }
if($anzahl_ukat<1){
haupt_kategorie_form($kat_id);
  }

} 
if(isset($_REQUEST[submit_hauptkat])){
	#print_r($_REQUEST);
	#
detail_in_db_eintragen($_REQUEST[kat_name], $_REQUEST[kat_wert], $_REQUEST[Bemerkung], $_REQUEST[detail_tabelle], $_REQUEST[detail_id]);
}


if(isset($_REQUEST[submit_ukat])){
	
	#print_r($_REQUEST);
	
detail_in_db_eintragen($_REQUEST[kat_name], $_REQUEST[detail_ukat_name], $_REQUEST[Bemerkung], $_REQUEST[detail_tabelle], $_REQUEST[detail_id]);
}
}



function get_kategorie_id($kategorie_name){
$db_abfrage = "SELECT DETAIL_KAT_ID FROM DETAIL_KATEGORIEN WHERE DETAIL_KAT_NAME='$kategorie_name' && DETAIL_KAT_AKTUELL='1' ORDER BY DETAIL_KAT_ID DESC limit 0,1";
$resultat = mysql_query($db_abfrage) or
           die(mysql_error());
while (list ($DETAIL_KAT_ID) = mysql_fetch_row($resultat)){
return $DETAIL_KAT_ID;	
 }	
}


###neu	
function get_kategorie_name($kategorie_id){
$db_abfrage = "SELECT DETAIL_KAT_NAME FROM DETAIL_KATEGORIEN WHERE DETAIL_KAT_ID='$kategorie_id' && DETAIL_KAT_AKTUELL='1' ORDER BY DETAIL_KAT_ID DESC limit 0,1";
$resultat = mysql_query($db_abfrage) or
           die(mysql_error());
while (list ($DETAIL_KAT_NAME) = mysql_fetch_row($resultat)){
return $DETAIL_KAT_NAME;	
 }	
}

function check_unterkategorie($kategorie_id){
$db_abfrage = "SELECT UNTERKATEGORIE_NAME FROM DETAIL_UNTERKATEGORIEN WHERE KATEGORIE_ID='$kategorie_id' ORDER BY UNTERKATEGORIE_NAME ASC";
$resultat = mysql_query($db_abfrage) or
           die(mysql_error());
$numrows = mysql_numrows($resultat);
return $numrows;
}

function unterkategorien_form($kat_id){
$kat_name = get_kategorie_name($kat_id);
$db_abfrage = "SELECT UNTERKATEGORIE_NAME FROM DETAIL_UNTERKATEGORIEN WHERE KATEGORIE_ID='$kat_id' ORDER BY UNTERKATEGORIE_NAME ASC";
$resultat = mysql_query($db_abfrage) or
           die(mysql_error());
erstelle_formular(NULL, NULL);
erstelle_hiddenfeld("kat_name", "$kat_name");
echo "<tr><td>";
echo "<select name=\"detail_ukat_name\" size=1>\n";
while (list ($UNTERKATEGORIE_NAME) = mysql_fetch_row($resultat)){
echo "<option value=\"$UNTERKATEGORIE_NAME\">$UNTERKATEGORIE_NAME</option>\n";	
 }
echo "</select>\n";
echo "</td></tr>";
echo "<tr><td>";
text_area("Bemerkung", "30", "6");
echo "</td></tr>";
echo "<tr><td>";
erstelle_submit_button("submit_ukat", "Eintragen");
echo "</td></tr>";
ende_formular();
}


function detail_in_db_eintragen($kat_name, $kat_uname, $bemerkung, $table, $id){
if(isset($kat_name) && isset($kat_uname) && isset($table) && isset($id))

#$dat_alt  = letzte_detail_dat($table, $id);
$dat_alt = "0"; //weil, neues detail hinzugefügt wurde  
$db_abfrage = "INSERT INTO DETAIL (`DETAIL_DAT`, `DETAIL_ID`, `DETAIL_NAME`, `DETAIL_INHALT`, `DETAIL_BEMERKUNG`, `DETAIL_AKTUELL`, `DETAIL_ZUORDNUNG_TABELLE`, `DETAIL_ZUORDNUNG_ID`) VALUES (NULL, '', '$kat_name', '$kat_uname', '$bemerkung', '1', '$table', '$id')";
$resultat = mysql_query($db_abfrage) or
           die(mysql_error());
$dat_neu  = letzte_detail_dat($table, $id);
protokollieren('DETAIL', $dat_neu, $dat_alt);
}

function text_area($name, $breite, $hoehe){
echo "<br>$name:<br> <textarea name=\"$name\" cols=\"$breite\" rows=\"$hoehe\"></textarea><br>\n";	
}

function haupt_kategorie_form($kat_id){
erstelle_formular(NULL, NULL);
$kat_name = get_kategorie_name($kat_id);
erstelle_hiddenfeld("kat_name", "$kat_name");
echo "<tr><td>";
echo "<br>$kat_name:<br> <textarea name=\"kat_wert\" cols=\"30\" rows=\"7\"></textarea><br>\n";	
echo "</td></tr>";
echo "<tr><td>";
echo "<br>Bemerkung:<br> <textarea name=\"Bemerkung\" cols=\"30\" rows=\"7\"></textarea><br>\n";
echo "</td></tr>";
erstelle_submit_button("submit_hauptkat", "Eintragen");
ende_formular();
}

##### person
function person_erfassen_form(){
erstelle_formular(NULL, NULL);	
erstelle_eingabefeld("Nachname", "person_nachname", "", "50");
erstelle_eingabefeld("Vorname", "person_vorname", "", "50");
erstelle_eingabefeld("Geburtstag (dd.mm.jjjj)", "person_geburtstag", "", "10");
#erstelle_eingabefeld("Ausweisart", "person_ausweisart", "", "50");
#erstelle_eingabefeld("Ausweisnummer", "person_ausweisnummer", "", "50");
erstelle_submit_button("submit_person", "Eintragen");
ende_formular();
}

function person_hidden_form($nachname, $vorname, $geburtstag){
erstelle_formular(NULL, NULL);
erstelle_hiddenfeld("person_nachname", "$nachname");
erstelle_hiddenfeld("person_vorname", "$vorname");
erstelle_hiddenfeld("person_geburtstag", "$geburtstag");
#erstelle_hiddenfeld("person_ausweisart", "$ausweisart");
#erstelle_hiddenfeld("person_ausweisnummer", "$ausweisnummer");
erstelle_submit_button("submit_person_direkt", "Trotzdem eintragen");
ende_formular();
}

function person_aendern_from($person_id){
$db_abfrage = "SELECT PERSON_ID, PERSON_NACHNAME, PERSON_VORNAME, PERSON_GEBURTSTAG FROM PERSON WHERE PERSON_ID='$person_id' && PERSON_AKTUELL='1'";
$resultat = mysql_query($db_abfrage) or
           die(mysql_error());
$numrows = mysql_numrows($resultat);
if($numrows>0){
erstelle_formular(NULL, NULL);
while (list ($PERSON_ID, $PERSON_NACHNAME, $PERSON_VORNAME, $PERSON_GEBURTSTAG) = mysql_fetch_row($resultat))
	{
$PERSON_GEBURTSTAG = date_mysql2german($PERSON_GEBURTSTAG);
erstelle_hiddenfeld("person_id", "$PERSON_ID");
erstelle_eingabefeld("Nachname", "person_nachname", "$PERSON_NACHNAME", "50");
erstelle_eingabefeld("Vorname", "person_vorname", "$PERSON_VORNAME", "50");
erstelle_eingabefeld("Geburtstag (dd.mm.jjjj)", "person_geburtstag", "$PERSON_GEBURTSTAG", "10");
#erstelle_eingabefeld("Ausweisart", "person_ausweisart", "$PERSON_AUSWEISART", "50");
#erstelle_eingabefeld("Ausweisnummer", "person_ausweisnummer", "$PERSON_AUSWEISNUMMER", "50");
	}
erstelle_submit_button("submit_person_aendern", "Aendern");
ende_formular();
}else{
	hinweis_ausgeben("Person mit der Person ID $person_id existiert nicht!");
	}
}

##### mietvertrag
function mietvertrag_form_neu(){
if(!isset($_REQUEST[objekt_id]) && !isset($_REQUEST[einheit_id])){
mietvertrag_objekt_links();
}
if(isset($_REQUEST[objekt_id])){
einheiten_ids_by_objekt($_REQUEST[objekt_id]);
}
if(isset($_REQUEST[einheit_id]) && !isset($_REQUEST[submit_vertragspartner]) && !isset($_REQUEST[mietvertrag_speichern])){
erstelle_formular(NULL, NULL);
erstelle_hiddenfeld("einheit_id", "$_REQUEST[einheit_id]");
personen_liste_multi();
erstelle_eingabefeld("Vertragsbeginn)", "mietvertrag_von", "", "10");
erstelle_eingabefeld("Vertragsende", "mietvertrag_bis", "", "10");
erstelle_submit_button("submit_vertragspartner", "Vertrag abschließen!");
ende_formular();
}
if(isset($_REQUEST[submit_vertragspartner])){
$anzahl_partner = count($_REQUEST[PERSON_ID]);
if($anzahl_partner <1){
	fehlermeldung_ausgeben ("Wählen Sie Vertragsparteien aus");
	$error = true;
}
elseif(empty($_REQUEST[mietvertrag_von])){
	fehlermeldung_ausgeben ("Vertragsbeginn eintragen");
	$error = true;
}
echo $error;
if($error != true){
erstelle_formular(NULL, NULL); //name, action
    	$anzahl_partner = count($_REQUEST[PERSON_ID]);
    	$einheit_kurzname = einheit_kurzname($_REQUEST[einheit_id]);
    	echo "<tr><td><h1>Folgende Daten wurden übermittelt:\n</h1></td></tr>\n";
    	echo "<tr><td><h2>Einheitkurzname: $einheit_kurzname</h2></td></tr>\n";
    	echo "<tr><td>Vertragsparteien: ";
    	for($a=0;$a<$anzahl_partner;$a++){
		mieternamen($_REQUEST[PERSON_ID][$a]);
		    	}
    	echo "</td></tr>";
    	echo "<tr><td>Von: $_REQUEST[mietvertrag_von]</td></tr>";
    	if(empty($_REQUEST[mietvertrag_bis])){
    		$vertrag_bis = "unbefristet";
    	}else{
    		$vertrag_bis = $_REQUEST[mietvertrag_bis];
    	}
    	echo "<tr><td>Bis: $vertrag_bis</td></tr>";
    	echo "<tr><td>";
    	#print_r($_POST);
    	warnung_ausgeben("Sind Sie sicher, daß Sie diesen Mietvertrag abschließen möchten?");
    	echo "</td></tr>";
    	erstelle_hiddenfeld("einheit_id", "".$_REQUEST[einheit_id]."");
    	erstelle_hiddenfeld("mietvertrag_von", "".$_REQUEST[mietvertrag_von]."");
    	erstelle_hiddenfeld("mietvertrag_bis", "".$_REQUEST[mietvertrag_bis]."");
    	for($a=0;$a<$anzahl_partner;$a++){
		erstelle_hiddenfeld("PERSON_ID[]", "".$_REQUEST[PERSON_ID][$a]."");		
		    	}
    	erstelle_submit_button("mietvertrag_speichern", "Speichern"); //name, wert
    	ende_formular(); 
}
}
###vertrag eintragen
if(isset($_REQUEST[mietvertrag_speichern])){
mietvertrag_anlegen($_REQUEST[mietvertrag_von], $_REQUEST[mietvertrag_bis], $_REQUEST[einheit_id]);
$zugewiesene_vetrags_id = mietvertrag_by_einheit($_REQUEST[einheit_id]);
#echo "VERTRAG $zugewiesene_vetrags_id angelegt";
$anzahl_partner = count($_REQUEST[PERSON_ID]);
for($a=0;$a<$anzahl_partner;$a++){
#echo "".$_REQUEST[PERSON_ID][$a]." <br>";
person_zu_mietvertrag($_REQUEST[PERSON_ID][$a], $zugewiesene_vetrags_id);
	}
hinweis_ausgeben("Mietvertrag wurde erstellt!");
hinweis_ausgeben("Sie werden zur Mietdefinition weitergeleitet!");
weiterleiten_in_sec("?daten=miete_definieren&option=miethoehe&mietvertrag_id=$zugewiesene_vetrags_id", "2");
  }
 }


?>
