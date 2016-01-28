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
 * @filesource   $HeadURL: http://192.168.2.52/svn/berlussimo_1/tags/02.11.2010 - Downloadversion 0.27/options/formulare/form_einheit.php $
 * @version      $Revision: 6 $
 * @modifiedby   $LastChangedBy: sivac $
 * @lastmodified $Date: 2010-09-21 10:34:50 +0200 (Di, 21 Sep 2010) $
 * 
 */
 
include_once("includes/formular_funktionen.php");
include_once("includes/allgemeine_funktionen.php");
$objekt_id = $_REQUEST["objekt_id"];
$haus_id = $_REQUEST["haus_id"];
$einheit_id = $_REQUEST["einheit_id"];
$objekt_kurzname = objekt_kurzname($objekt_id);
$haus_kurzname = haus_strasse_nr($haus_id);
$einheit_update = $_REQUEST["einheit_update"];
if(isset($_REQUEST["daten_rein"])){
	switch($_REQUEST["daten_rein"]) {

    case "anlegen":
    $form = new mietkonto;
    $form->erstelle_formular("Einheit anlegen", NULL);
    iframe_start();
    echo "<h1>Einheit anlegen</h1>";
	if(!isset($objekt_id)){        
    objekt_links();
	}
	if(isset($objekt_id) && !isset($haus_id)){        
    $objekt_kurzname = objekt_kurzname($objekt_id);
    hinweis_ausgeben("Objekt: $objekt_kurzname");
	haeuser_links($objekt_id);
	}
	if(isset($objekt_id) && isset($haus_id)){        
    $objekt_kurzname = objekt_kurzname($objekt_id);
    $haus_kurzname = haus_strasse_nr($haus_id);
    hinweis_ausgeben("Objekt: $objekt_kurzname");
    hinweis_ausgeben("Haus: $haus_kurzname");
    if(!isset($_POST[submit_einheit])){
    einheit_eingabe_form($haus_id);
    einheiten_liste($haus_id);
    }
	}
    
    if(isset($_POST[submit_einheit])) {
    foreach($_POST as $key => $value){
    		if(empty($value)){
    		fehlermeldung_ausgeben ("FEHLER: Alle Felder müssen ausgefüllt werden!");
    		backlink();
    	 	$error = 1;
    	 	break;
    	 	}
        }
    if(!isset($error)){
    #	print_r($_POST);
    	erstelle_formular(einheit_in_db, NULL); //name, action
    	echo "<tr><td><h1>Folgende Daten wurden übermittelt:\n</h1></td></tr>\n";
    	echo "<tr><td><h2>Objektkurzname: $objekt_kurzname</h2></td></tr>\n";
    	echo "<tr><td><h2>Haus: $haus_kurzname</h2></td></tr>\n";
    	echo "<tr><td><h2>Einheit: $_POST[einheit_kurzname] - $_POST[einheit_qm]m² - Lage: $_POST[einheit_lage]</h2></td></tr>\n";
    	echo "<tr><td>";
    	#print_r($_POST);
    	warnung_ausgeben("Sind Sie sicher, daß Sie die neue Einheit $_POST[einheit_kurzname] ($_POST[einheit_qm]m²) im Objekt $objekt_kurzname, $haus_kurzname anlegen wollen?");
    	echo "</td></tr>";
    	erstelle_hiddenfeld("haus_id", "$haus_id");
    	erstelle_hiddenfeld("einheit_kurzname", "$_POST[einheit_kurzname]");
    	erstelle_hiddenfeld("einheit_qm", "$_POST[einheit_qm]");
    	erstelle_hiddenfeld("einheit_lage", "$_POST[einheit_lage]");
    	erstelle_hiddenfeld("daten_rein", "speichern");
    	erstelle_submit_button("einheit_speichern", "Speichern"); //name, wert
    	ende_formular();
        }
    }
    iframe_end();
    $form->ende_formular();
    break;

	case "speichern":
	$form = new mietkonto;
    $form->erstelle_formular("Einheit speichern", NULL);
	iframe_start();
	hinweis_ausgeben("Objekt: $objekt_kurzname");
    hinweis_ausgeben("Haus: $haus_kurzname");
	neue_einheit_in_db($_POST[haus_id], $_POST[einheit_kurzname], $_POST[einheit_lage], $_POST[einheit_qm]);
	weiterleiten("?daten=einheit_raus&einheit_raus=einheit_kurz&haus_id=$_POST[haus_id]");
	iframe_end();
	$form->ende_formular();
	break;

	case "aendern":
	$form = new mietkonto;
    $form->erstelle_formular("Einheit ändern", NULL);
	iframe_start();
	echo "<h1>Einheit ändern</h1>";
	if(!isset($objekt_id)){        
    objekt_links();
		}
	if(isset($objekt_id) && !isset($haus_id)){        
    $objekt_kurzname = objekt_kurzname($objekt_id);
    hinweis_ausgeben("Objekt: $objekt_kurzname");
	haeuser_links($objekt_id);
	}
	if(isset($objekt_id) && isset($haus_id) && !isset($einheit_id)){        
    $objekt_id = objekt_id_of_haus($haus_id);
    $objekt_kurzname = objekt_kurzname($objekt_id);
    $haus_kurzname = haus_strasse_nr($haus_id);
    hinweis_ausgeben("Objekt: $objekt_kurzname");
    hinweis_ausgeben("Haus: $haus_kurzname");
    einheiten_links($objekt_id, $haus_id);
    
    }
	if(isset($objekt_id) && isset($haus_id) && isset($einheit_id) && !isset($_POST[aendern_einheit])){
	$objekt_id = objekt_id_of_haus($haus_id);
	$haus_id = haus_id_of_einheit($einheit_id);
	$objekt_kurzname = objekt_kurzname($objekt_id);
    $haus_kurzname = haus_strasse_nr($haus_id);
	$einheit_kurzname = einheit_kurzname($einheit_id);
	hinweis_ausgeben("Objekt: $objekt_kurzname");
    hinweis_ausgeben("Haus: $haus_kurzname");
    hinweis_ausgeben("Einheit: $einheit_kurzname");
	einheit_aendern_form($einheit_id);	
	einheiten_liste($haus_id);
	}
	
	if(isset($_POST[aendern_einheit])) {
     foreach($_POST as $key => $value){
    		if(empty($value)){
    		fehlermeldung_ausgeben ("FEHLER: Alle Felder müssen ausgefüllt werden!");
    		$error = 1;
    	 	break;
    	 	}
        echo "$key $value";
        }
    if(!isset($error)){
    	erstelle_formular(einheit_in_db, NULL); //name, action
    	echo "<tr><td><h1>Folgende Daten wurden übermittelt:\n</h1></td></tr>\n";
    	echo "<tr><td><h2>Objektkurzname: $objekt_kurzname</h2></td></tr>\n";
    	echo "<tr><td><h2>Haus: $haus_kurzname</h2></td></tr>\n";
    	echo "<tr><td><h2>Einheit: $_POST[einheit_kurzname] - $_POST[einheit_qm]m² - Lage: $_POST[einheit_lage]</h2></td></tr>\n";
    	echo "<tr><td>";
    	#print_r($_POST);
    	warnung_ausgeben("Sind Sie sicher, daß Sie die neue Einheit $_POST[einheit_kurzname] ($_POST[einheit_qm]m²) im Objekt $objekt_kurzname, $haus_kurzname anlegen wollen?");
    	echo "</td></tr>";
    	erstelle_hiddenfeld("haus_id", "$haus_id");
    	erstelle_hiddenfeld("einheit_kurzname", "$_POST[einheit_kurzname]");
    	erstelle_hiddenfeld("einheit_id", "$_POST[einheit_id]");
    	erstelle_hiddenfeld("einheit_dat", "$_POST[einheit_dat]");
    	erstelle_hiddenfeld("einheit_qm", "$_POST[einheit_qm]");
    	erstelle_hiddenfeld("einheit_lage", "$_POST[einheit_lage]");
    	erstelle_hiddenfeld("daten_rein", "einheit_update");
    	erstelle_submit_button("einheit_update", "Speichern"); //name, wert
    	ende_formular();
    #neue_einheit_in_db($_POST[haus_id], $_POST[einheit_kurzname], $_POST[einheit_lage], $_POST[einheit_qm], $_POST[einheit_ausstattung]);
    #einheit_deaktivieren($_POST[einheit_dat]);
    #einheit_geandert_in_db($_POST[einheit_dat], $_POST[einheit_id], $_POST[haus_id], $_POST[einheit_kurzname], $_POST[einheit_lage], $_POST[einheit_qm]);
    
        }
    } 
	
	iframe_end();
	$form->ende_formular();
	break;

	case "einheit_update":
	iframe_start();
	hinweis_ausgeben("Objekt: $objekt_kurzname");
    hinweis_ausgeben("Haus: $haus_kurzname");
	einheit_deaktivieren($_POST[einheit_dat]);
	hinweis_ausgeben("DAT $_POST[einheit_dat] inaktiv");
	einheit_geandert_in_db($_POST[einheit_dat], $_POST[einheit_id], $_POST[haus_id], $_POST[einheit_kurzname], $_POST[einheit_lage], $_POST[einheit_qm]);
	hinweis_ausgeben("EINHEIT $_POST[einheit_kurzname] WURDE GEÄNDERT!");
	einheiten_liste($haus_id);
	iframe_end();
	break;
	
	}
}

function objekt_links(){
$daten_rein = $_REQUEST["daten_rein"];
$db_abfrage = "SELECT OBJEKT_DAT, OBJEKT_ID, OBJEKT_KURZNAME FROM OBJEKT WHERE OBJEKT_AKTUELL='1' ORDER BY OBJEKT_KURZNAME ASC ";
$resultat = mysql_query($db_abfrage) or
           die(mysql_error());
echo "<b>Objekt auswählen:</b><br>\n ";
while (list ($OBJEKT_DAT, $OBJEKT_ID, $OBJEKT_KURZNAME) = mysql_fetch_row($resultat)){
echo "<a class=\"objekt_links\" href=\"?formular=einheit&daten_rein=$daten_rein&objekt_id=$OBJEKT_ID\">$OBJEKT_KURZNAME</a><br>\n";
 }
}

function haeuser_links($obj_id){
$daten_rein = $_REQUEST["daten_rein"];
$db_abfrage = "SELECT HAUS_DAT, HAUS_ID, HAUS_STRASSE, HAUS_NUMMER FROM HAUS WHERE OBJEKT_ID='$obj_id' && HAUS_AKTUELL='1' ORDER BY HAUS_STRASSE, HAUS_NUMMER ASC";
$resultat = mysql_query($db_abfrage) or
           die(mysql_error());
$numrows = mysql_numrows($resultat);
if($numrows < 1) {
	echo "<h2 class=\"fehler\">Keine Häuser im ausgewählten Objekt</h2><br>\n";
	echo "Erst Haus im Objekt anlegen - <a href=\"?formular=haus&daten_rein=anlegen\">Hauseningabe hier&nbsp;</a>\n<br>\n";
}else{
  while (list ($HAUS_DAT, $HAUS_ID, $HAUS_STRASSE, $HAUS_NUMMER) = mysql_fetch_row($resultat)){
  #echo "$HAUS_DAT, $HAUS_ID, $HAUS_STRASSE, $HAUS_NUMMER<br>";
  echo "<a class=\"objekt_links\" href=\"?formular=einheit&daten_rein=$daten_rein&objekt_id=$obj_id&haus_id=$HAUS_ID\">$HAUS_STRASSE $HAUS_NUMMER</a><br>\n";
#  echo "<option value=\"$HAUS_ID\">$HAUS_STRASSE $HAUS_NUMMER</option>\n";
  }
}
}



function objekt_id_of_haus($haus_id){
$db_abfrage = "SELECT OBJEKT_ID FROM HAUS WHERE HAUS_ID='$haus_id' && HAUS_AKTUELL='1' ORDER BY HAUS_DAT DESC LIMIT 0,1";
$resultat = mysql_query($db_abfrage) or
           die(mysql_error());
$numrows = mysql_numrows($resultat);
if($numrows > 0) {
  while (list ($OBJEKT_ID) = mysql_fetch_row($resultat)){
   return $OBJEKT_ID;
  }
}
}
function haus_id_of_einheit($einheit_id){
$db_abfrage = "SELECT HAUS_ID FROM EINHEIT WHERE EINHEIT_ID='$einheit_id' && EINHEIT_AKTUELL='1' ORDER BY EINHEIT_DAT DESC LIMIT 0,1";
$resultat = mysql_query($db_abfrage) or
           die(mysql_error());
$numrows = mysql_numrows($resultat);
if($numrows > 0) {
  while (list ($HAUS_ID) = mysql_fetch_row($resultat)){
   return $HAUS_ID;
  }
}
}



function einheiten_links($objekt_id, $haus_id){
$daten_rein = $_REQUEST["daten_rein"];
$db_abfrage = "SELECT EINHEIT_ID, EINHEIT_KURZNAME, EINHEIT_LAGE FROM EINHEIT WHERE HAUS_ID='$haus_id' && EINHEIT_AKTUELL='1' ORDER BY EINHEIT_KURZNAME ASC ";
$resultat = mysql_query($db_abfrage) or
           die(mysql_error());
$numrows = mysql_numrows($resultat);
if($numrows < 1){
	echo "<h2 class=\"fehler\">Keine Einheiten im ausgewählten Haus</h2>";
	echo "<p class=\"hinweis\">Bitte zuerst Einheit im Haus anlegen - <a href=\"?formular=einheit&daten_rein=anlegen\">Einheit anlegen HIER&nbsp;</a></p><br>";
	
}else{
while (list ($EINHEIT_ID, $EINHEIT_KURZNAME, $EINHEIT_LAGE) = mysql_fetch_row($resultat)){
echo "<a class=\"objekt_links\" href=\"?formular=einheit&daten_rein=$daten_rein&objekt_id=$objekt_id&haus_id=$haus_id&einheit_id=$EINHEIT_ID\">$EINHEIT_KURZNAME - $EINHEIT_LAGE</a><br>\n";
#echo "<option value=\"$EINHEIT_ID\">$EINHEIT_KURZNAME</option>\n";	
}
}
}

function einheiten_liste($haus_id){
$daten_rein = $_REQUEST["daten_rein"];
$db_abfrage = "SELECT EINHEIT_ID, EINHEIT_KURZNAME, EINHEIT_LAGE, EINHEIT_QM FROM EINHEIT WHERE HAUS_ID='$haus_id' && EINHEIT_AKTUELL='1' ORDER BY EINHEIT_KURZNAME ASC ";
$resultat = mysql_query($db_abfrage) or
           die(mysql_error());
$numrows = mysql_numrows($resultat);
if($numrows < 1){
fehlermeldung_ausgeben("<h2 class=\"fehler\">Keine Einheiten im ausgewählten Haus</h2>");
hinweis_ausgeben("Bitte zuerst hier Einheit im Haus anlegen</p>");
	
}else{
echo "<div class=\"tabelle\">";
#iframe_start();
#echo "<div class=\"tabelle\"><table>";
echo "<table>";
echo "<tr class=\"feldernamen\"><td>EINHEIT KURZNAME</td><td>EINHEIT LAGE</td><td>FLÄCHE</td></tr>\n";
$counter = 0;
while (list ($EINHEIT_ID, $EINHEIT_KURZNAME, $EINHEIT_LAGE, $EINHEIT_QM) = mysql_fetch_row($resultat))
{
  $counter++;
if($counter == 1){
echo "<tr class=\"zeile1\"><td>$EINHEIT_KURZNAME</td><td>$EINHEIT_LAGE</td><td>$EINHEIT_QM m²</td></tr>\n";  
}
if($counter == 2){
echo "<tr class=\"zeile2\"><td>$EINHEIT_KURZNAME</td><td>$EINHEIT_LAGE</td><td>$EINHEIT_QM m²</td></tr>\n";
$counter = 0;
  }
}

echo "</table>";
#iframe_end();
echo "</div>";
}
}

?>
