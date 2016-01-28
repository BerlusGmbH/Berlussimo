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
 * @filesource   $HeadURL: http://192.168.2.52/svn/berlussimo_1/tags/02.11.2010 - Downloadversion 0.27/includes/haus_form_funktionen.php $
 * @version      $Revision: 6 $
 * @modifiedby   $LastChangedBy: sivac $
 * @lastmodified $Date: 2010-09-21 10:34:50 +0200 (Di, 21 Sep 2010) $
 * 
 */
 

function objekt_liste_links(){
$db_abfrage = "SELECT OBJEKT_DAT, OBJEKT_ID, OBJEKT_KURZNAME FROM OBJEKT WHERE OBJEKT_AKTUELL='1' ORDER BY OBJEKT_KURZNAME ASC ";
$resultat = mysql_query($db_abfrage) or
           die(mysql_error());
echo "<b>Objekt auswählen:</b><br>\n ";
while (list ($OBJEKT_DAT, $OBJEKT_ID, $OBJEKT_KURZNAME) = mysql_fetch_row($resultat)){
echo "<a class=\"objekt_links\" href=\"?formular=haus&daten_rein=anlegen&haus_objekt=$OBJEKT_ID\">$OBJEKT_KURZNAME</a><br>\n";
 }
}
function objekt_liste_links_aenderung(){
$db_abfrage = "SELECT OBJEKT_DAT, OBJEKT_ID, OBJEKT_KURZNAME FROM OBJEKT WHERE OBJEKT_AKTUELL='1' ORDER BY OBJEKT_KURZNAME ASC ";
$resultat = mysql_query($db_abfrage) or
           die(mysql_error());
echo "<b>Objekt auswählen:</b><br>\n ";
while (list ($OBJEKT_DAT, $OBJEKT_ID, $OBJEKT_KURZNAME) = mysql_fetch_row($resultat)){
echo "<a class=\"objekt_links\" href=\"?formular=haus&daten_rein=aendern_liste&objekt_id=$OBJEKT_ID\">$OBJEKT_KURZNAME</a><br>\n";
 }
}

function haus_eingabe_formular($objekt_id){
$objekt_kurzname = objekt_kurzname($objekt_id);
$anzahl_haeuser = anzahl_haeuser_im_objekt($objekt_id);
echo "<p class=\"form_ausgewaehlt\">Ausgewähltes Objekt: $objekt_kurzname (Häuser: $anzahl_haeuser)</p>";
erstelle_formular("haus_eingabe_form", NULL);
erstelle_hiddenfeld("objekt_id", $objekt_id);
erstelle_eingabefeld("Strasse", "haus_strasse", "", "50");
erstelle_eingabefeld("Hausnummer", "haus_nummer", "", "5");
erstelle_eingabefeld("Ort/Stadt", "haus_stadt", "", "50");
erstelle_eingabefeld("PLZ", "haus_plz", "", "50");
erstelle_eingabefeld("Haus in m²", "haus_qm", "", "50");
erstelle_submit_button("submit_haus", "Senden");
ende_formular();
}

function haus_aendern_formular($haus_id){
erstelle_formular("haus_aendern_form", "?formular=haus&daten_rein=aendern");
$db_abfrage = "SELECT HAUS_DAT, HAUS_STRASSE, HAUS_NUMMER, HAUS_STADT, HAUS_PLZ, HAUS_QM, OBJEKT_ID FROM HAUS WHERE HAUS_ID='$haus_id' && HAUS_AKTUELL='1' ORDER BY HAUS_DAT DESC LIMIT 0,1";
$resultat = mysql_query($db_abfrage) or
           die(mysql_error());

while (list ($HAUS_DAT, $HAUS_STRASSE, $HAUS_NUMMER, $HAUS_STADT, $HAUS_PLZ, $HAUS_QM, $OBJEKT_ID) = mysql_fetch_row($resultat)){
erstelle_hiddenfeld("haus_dat", $HAUS_DAT);
erstelle_hiddenfeld("haus_id", $haus_id);
erstelle_hiddenfeld("objekt_id", $OBJEKT_ID);
erstelle_eingabefeld("Strasse", "haus_strasse", "$HAUS_STRASSE", "50");
erstelle_eingabefeld("Hausnummer", "haus_nummer", "$HAUS_NUMMER", "5");
erstelle_eingabefeld("Ort/Stadt", "haus_stadt", "$HAUS_STADT", "50");
erstelle_eingabefeld("PLZ", "haus_plz", "$HAUS_PLZ", "50");
erstelle_eingabefeld("Haus in m²", "haus_qm", "$HAUS_QM", "50");
}
erstelle_submit_button("submit_haus", "Senden");
ende_formular();

}

function letzte_haus_id(){
$db_abfrage = "SELECT HAUS_ID FROM HAUS ORDER BY HAUS_ID DESC LIMIT 0,1";
$resultat = mysql_query($db_abfrage) or
           die(mysql_error());

while (list ($HAUS_ID) = mysql_fetch_row($resultat))
return $HAUS_ID;
}

function haus_in_db_eintragen($strasse, $nummer, $stadt, $plz, $qm, $objekt_id){
$haus_existiert = haus_exists($strasse, $nummer, $stadt, $plz);
if($haus_existiert < 1){
$haus_id = letzte_haus_id();
$haus_id = $haus_id+1;
$db_abfrage="INSERT INTO HAUS (HAUS_DAT, HAUS_ID, HAUS_STRASSE, HAUS_NUMMER, HAUS_STADT, HAUS_PLZ, HAUS_QM, HAUS_AKTUELL, OBJEKT_ID) VALUES (NULL,'$haus_id','$strasse', '$nummer', '$stadt', '$plz', '$qm', '1', '$objekt_id')";
$resultat = mysql_query($db_abfrage) or
           die(mysql_error());

$aktuelle_haus_dat = zugeteilte_haus_dat($strasse, $nummer, $stadt, $plz);
protokollieren("HAUS", $aktuelle_haus_dat, 0);
hinweis_ausgeben ("Haus $haus_id wurde eingetragen");	
weiterleiten ("?daten=haus_raus&haus_raus=haus_kurz&objekt_id=$objekt_id");
}else{
fehlermeldung_ausgeben("Haus in der $strasse $nummer in $stadt $plz existiert bereits.");	
weiterleiten ("javascript:history.back()");
}
}

function haus_geaendert_eintragen($haus_dat, $haus_id, $strasse, $nummer, $stadt, $plz, $qm, $objekt_id){
$db_abfrage="INSERT INTO HAUS (HAUS_DAT, HAUS_ID, HAUS_STRASSE, HAUS_NUMMER, HAUS_STADT, HAUS_PLZ, HAUS_QM, HAUS_AKTUELL, OBJEKT_ID) VALUES (NULL, '$haus_id', '$strasse', '$nummer', '$stadt', '$plz', '$qm', '1', '$objekt_id')";
$resultat = mysql_query($db_abfrage) or
           die(mysql_error());

$aktuelle_haus_dat = zugeteilte_haus_dat($strasse, $nummer, $stadt, $plz);
protokollieren("HAUS", $aktuelle_haus_dat, $haus_dat);
hinweis_ausgeben("Haus wurde geändert");
weiterleiten ("?daten=haus_raus&haus_raus=haus_kurz");	
}

function zugeteilte_haus_dat($strasse, $nummer, $stadt, $plz){
$db_abfrage = "SELECT HAUS_DAT FROM HAUS WHERE HAUS_STRASSE='$strasse' && HAUS_NUMMER='$nummer' && HAUS_STADT='$stadt' && HAUS_PLZ='$plz' ORDER BY HAUS_DAT DESC LIMIT 0,1";
$resultat = mysql_query($db_abfrage) or
           die(mysql_error());

while (list ($HAUS_DAT) = mysql_fetch_row($resultat))
return $HAUS_DAT;
}

function haus_exists($strasse, $nummer, $stadt, $plz){
$db_abfrage = "SELECT HAUS_DAT FROM HAUS WHERE HAUS_STRASSE='$strasse' && HAUS_NUMMER='$nummer' && HAUS_STADT='$stadt' && HAUS_PLZ='$plz' ORDER BY HAUS_DAT DESC LIMIT 0,1";
$resultat = mysql_query($db_abfrage) or
           die(mysql_error());

$numrows = mysql_numrows($resultat);
return $numrows;
}


function objekt_auswahl_form1(){
erstelle_formular("objekt_auswahl", NULL);
objekt_liste_dropdown();
erstelle_submit_button("submit_objekt", "Senden");
ende_formular();	
}
function haeuser_liste_dropdown1($obj_id){
erstelle_formular("haus_auswahl", NULL);

$db_abfrage = "SELECT HAUS_DAT, HAUS_ID, HAUS_STRASSE, HAUS_NUMMER FROM HAUS WHERE OBJEKT_ID='$obj_id' && HAUS_AKTUELL='1' ORDER BY HAUS_NUMMER ASC";
$resultat = mysql_query($db_abfrage) or
           die(mysql_error());
$numrows = mysql_numrows($resultat);
if($numrows < 1) {
	echo "<h1>Keine Häuser im ausgewählten Objekt</h1><br>";
	echo "Erst Haus im Objekt anlegen - <a href=\"?formular=haus&daten_rein=anlegen\">Hauseningabe hier&nbsp;</a>";
}else{
echo "<select name=\"haeuser\" size=\"1\">";
  while (list ($HAUS_DAT, $HAUS_ID, $HAUS_STRASSE, $HAUS_NUMMER) = mysql_fetch_row($resultat)){
  #echo "$HAUS_DAT, $HAUS_ID, $HAUS_STRASSE, $HAUS_NUMMER<br>";
  echo "<option value=\"$HAUS_ID\">$HAUS_STRASSE $HAUS_NUMMER</option>\n";
  }
echo "</select>";

erstelle_submit_button("haus_auswahl", "Senden");
ende_formular();
}
} 

function haeuser_liste_tabellealt($objekt_id){
$objekt_kurzname = objekt_kurzname($objekt_id);
$db_abfrage = "SELECT HAUS_DAT, HAUS_ID, HAUS_STRASSE, HAUS_NUMMER FROM HAUS WHERE OBJEKT_ID='$objekt_id' && HAUS_AKTUELL='1' ORDER BY HAUS_NUMMER ASC";
$resultat = mysql_query($db_abfrage) or
           die(mysql_error());
$numrows = mysql_numrows($resultat);
if($numrows > 0) {
	
echo "<table width=100%>\n";
echo "<tr class=\"feldernamen\"><td colspan=7>Objekt: $objekt_kurzname</td></tr>\n";
echo "<tr class=\"feldernamen\"><td>Straße</td><td>Nummer</td><td colspan=6></td></tr>\n";
$counter = 0;
  while (list ($HAUS_DAT, $HAUS_ID, $HAUS_STRASSE, $HAUS_NUMMER) = mysql_fetch_row($resultat)){
  #echo "$HAUS_DAT, $HAUS_ID, $HAUS_STRASSE, $HAUS_NUMMER<br>";
  $counter++;
if($counter == 1){
echo "<tr class=\"zeile1\"><td>$HAUS_STRASSE</td><td>$HAUS_NUMMER</td><td>Details</td><td><a href=\"?daten=einheit_raus&einheit_raus=einheit_kurz&haus_id=$HAUS_ID\">Einheiten</a></td><td>Mieter</td><td>Ändern</td><td><a href=\"?formular=haus&daten_rein=aendern_liste\">Löschen</a></td></tr>\n";  
}
if($counter == 2){
echo "<tr class=\"zeile2\"><td>$HAUS_STRASSE</td><td>$HAUS_NUMMER</td><td>Details</td><td><a href=\"?daten=einheit_raus&einheit_raus=einheit_kurz&haus_id=$HAUS_ID\">Einheiten</a></td><td>Mieter</td><td>Ändern</td><td><a href=\"?formular=haus&daten_rein=aendern_liste\">Löschen</a></td></tr>\n";
$counter = 0;
  }
}
echo "</table>";
} 
}
function haeuser_liste_tabelle($objekt_id){
$objekt_kurzname = objekt_kurzname($objekt_id);
$db_abfrage = "SELECT HAUS_STRASSE, HAUS_NUMMER FROM HAUS WHERE OBJEKT_ID='$objekt_id' && HAUS_AKTUELL='1' ORDER BY HAUS_NUMMER ASC";
$resultat = mysql_query($db_abfrage) or
           die(mysql_error());
$numrows = mysql_numrows($resultat);
if($numrows > 0) {
	
echo "<div class=\"tabelle_haus\"><table>\n";
echo "<tr class=\"feldernamen\"><td colspan=2>Objekt: $objekt_kurzname</td></tr>\n";
echo "<tr class=\"feldernamen\"><td>Straße</td><td>Nummer</td></tr>\n";
$counter = 0;
  while (list ($HAUS_STRASSE, $HAUS_NUMMER) = mysql_fetch_row($resultat)){
  #echo "$HAUS_DAT, $HAUS_ID, $HAUS_STRASSE, $HAUS_NUMMER<br>";
  $counter++;
if($counter == 1){
echo "<tr class=\"zeile1\"><td>$HAUS_STRASSE</td><td>$HAUS_NUMMER</td></tr>\n";  
}
if($counter == 2){
echo "<tr class=\"zeile2\"><td>$HAUS_STRASSE</td><td>$HAUS_NUMMER</td></tr>\n";
$counter = 0;
  }
}
echo "</table></div>";
} 
}

function deaktiviere_haus_dat($haus_dat){
$db_abfrage = "UPDATE HAUS SET HAUS_AKTUELL='0' WHERE HAUS_DAT='$haus_dat'";
$resultat = mysql_query($db_abfrage) or
           die(mysql_error());		
}

?>
