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
 * @filesource   $HeadURL: http://192.168.2.52/svn/berlussimo_1/tags/02.11.2010 - Downloadversion 0.27/options/formulare/form_haus.php $
 * @version      $Revision: 15 $
 * @modifiedby   $LastChangedBy: sivac $
 * @lastmodified $Date: 2011-07-07 10:41:33 +0200 (Do, 07 Jul 2011) $
 * 
 */
	 
include_once("includes/haus_form_funktionen.php");
include_once("includes/formular_funktionen.php");
include_once("options/links/links.form_haus.php");
include_once("includes/allgemeine_funktionen.php");

if(isset($_REQUEST["daten_rein"])){
	switch($_REQUEST["daten_rein"]) {

    case "anlegen":
    $form = new mietkonto;
    $form->erstelle_formular("Haus anlegen", NULL);
     
        if(empty($_REQUEST["haus_objekt"])){
    iframe_start();
    #haus_eingabe_formular();
     objekt_liste_links();
        }else{
        haeuser_liste_tabelle($_REQUEST[haus_objekt]); // rechts die liste der häuser
        iframe_start();	
        #echo "$_REQUEST[haus_objekt]";
        haus_eingabe_formular($_REQUEST[haus_objekt]);
        
        }
        
        if(isset($_POST["submit_haus"])){
    	foreach($_POST as $key => $value){
    		if(empty($value)){
    		fehlermeldung_ausgeben("Alle felder müssen ausgefüllt werden");
    		weiterleiten ("javascript:history.back()");
    		#echo "<a href=\"javascript:history.back()\">Zurück</a>\n";
    	 	$error = 1;
    	 	iframe_end();
    	 	break;
    	 	}
    	}
    	if($error != 1){
    		#echo "KEIN FEHLER"; //Dateineingabe
    		#foreach($_POST as $key => $value){
    			#echo "$key - $value<br>";
    		#}
    	$letzte_haus_id = letzte_haus_id();
    	haus_in_db_eintragen($_POST['haus_strasse'], $_POST['haus_nummer'], $_POST['haus_stadt'], $_POST['haus_plz'], $_POST['haus_qm'], $_REQUEST['haus_objekt']);
    	   	
    	}
        }
    iframe_end();
    $form->ende_formular();
    break;

    
    case "haus_neu":
    $h = new haus;
    if(!empty($_REQUEST['objekt_id'])){
    $h->form_haus_neu($_REQUEST['objekt_id']);
    }else{
    	$h->form_haus_neu('');
    }
    break;
    
     case "haus_speichern":
    #echo "HAUSSPEICHERN";
    #print_req();
    if($_POST){
    	if(!empty($_POST['strasse']) && !empty($_POST['haus_nr']) && !empty($_POST['ort']) && !empty($_POST['plz']) && !empty($_POST['qm']) && !empty($_POST['objekt_id'])){
    		echo "alles ok";
    	$h = new haus;
    	$h->haus_speichern($_POST['strasse'],$_POST['haus_nr'],$_POST['ort'],$_POST['plz'],$_POST['qm'],$_POST['objekt_id']);
    	weiterleiten("?daten=haus_raus&haus_raus=haus_kurz&objekt_id=$_POST[objekt_id]");
    	}else{
    		echo "Daten unvollständig";
    	}
    }else{
    	echo "Daten unvollständig";
    }
    break;
    
    
    case "aendern_liste":
    $form = new mietkonto;
    $form->erstelle_formular("Haus ändern", NULL);
    iframe_start();
    echo "<h1>Haus ändern</h1>";
    if(!isset($_REQUEST["objekt_id"])){
    objekt_liste_links_aenderung();
    }
    if(!isset($_REQUEST["haus_id"]) && isset($_REQUEST["objekt_id"])){
    $objekt_kurzname = objekt_kurzname($_REQUEST["objekt_id"]);
    hinweis_ausgeben("Objekt: $objekt_kurzname");
    haus_liste_links_aenderung($_REQUEST["objekt_id"]);
    }
    if(isset($_REQUEST["haus_id"]) && isset($_REQUEST["objekt_id"])){
    $objekt_kurzname = objekt_kurzname($_REQUEST["objekt_id"]);
    $haus_kurzname = haus_strasse_nr($_REQUEST["haus_id"]);
    hinweis_ausgeben("Objekt: $objekt_kurzname");
    hinweis_ausgeben("Haus: $haus_kurzname");
    haus_aendern_formular($_REQUEST["haus_id"]);
    haeuser_liste_tabelle($_REQUEST["objekt_id"]); // rechts die liste der häuser
    }
        
    iframe_end();
    $form->ende_formular();
    break;
    
    case "aendern":
    $form = new mietkonto;
    $form->erstelle_formular("Haus ändern", NULL);
    iframe_start();
    echo "<h1>Haus ändern - Prozedur</h1>";
    foreach($_POST as $key => $value){
    		if(!isset($value)){
    		fehlermeldung_ausgeben("FEHLER: Alle Felder müssen ausgefüllt werden!");
    		echo "<a href=\"javascript:history.back()\">Zurück</a>\n";
    	 	$error = 1;
    	 	#echo "ERROR $key $value<br>";
    	 	break;
    	 	}
    	#echo "$key $value<br>";
     }
		if(!isset($error)){
		if(!isset($_REQUEST[einheit_update])){
		erstelle_formular(haus_in_db, NULL); //name, action
    	$objekt_kurzname = objekt_kurzname($_POST["objekt_id"]);
    	echo "<tr><td><h1>Folgende Daten wurden übermittelt:\n</h1></td></tr>\n";
    	echo "<tr><td><h2>Objektkurzname: $objekt_kurzname</h2></td></tr>\n";
    	echo "<tr><td><h2>Haus: $_POST[haus_strasse] $_POST[haus_nummer] in $_POST[haus_plz] $_POST[haus_stadt]</h2></td></tr>\n";
    	echo "<tr><td>";
    	#print_r($_POST);
    	warnung_ausgeben("Sind Sie sicher, daß Sie das Haus $_POST[haus_strasse] $_POST[haus_nummer] im Objekt $objekt_kurzname  ändern wollen?");
    	echo "</td></tr>";
    	erstelle_hiddenfeld("haus_dat", "$_POST[haus_dat]");
    	erstelle_hiddenfeld("haus_id", "$_POST[haus_id]");
    	erstelle_hiddenfeld("objekt_id", "$_POST[objekt_id]");
    	erstelle_hiddenfeld("haus_strasse", "$_POST[haus_strasse]");
    	erstelle_hiddenfeld("haus_nummer", "$_POST[haus_nummer]");
    	erstelle_hiddenfeld("haus_plz", "$_POST[haus_plz]");
    	erstelle_hiddenfeld("haus_stadt", "$_POST[haus_stadt]");
    	erstelle_hiddenfeld("haus_qm", "$_POST[haus_qm]");
    	erstelle_submit_button("einheit_update", "Speichern"); //name, wert
    	ende_formular(); 
		}
		if(isset($_REQUEST[einheit_update])){
		$haus_dat = $_POST[haus_dat];
		deaktiviere_haus_dat($haus_dat);
    	haus_geaendert_eintragen($_POST['haus_dat'], $_POST['haus_id'], $_POST['haus_strasse'], $_POST['haus_nummer'], $_POST['haus_stadt'], $_POST['haus_plz'], $_POST['haus_qm'], $_POST['objekt_id']);
    	    //hausdat deaktivieren
		    #deaktiviere_haus_dat($_POST[haus_dat]);
    		#haus_geaendert_eintragen($_POST['haus_dat'], $_POST['haus_id'], $_POST['haus_strasse'], $_POST['haus_nummer'], $_POST['haus_stadt'], $_POST['haus_plz'], $_POST['haus_qm'], $_POST['objekt_id']);
		}
				}
				echo $error;   
	iframe_end();
    $form->ende_formular();
    break;
    
    case "loeschen":
    echo "<h1>Haus löschen</h1>";
    break;
    }
}
function haus_liste_links_aenderung($objekt_id){
$daten_rein = $_REQUEST["daten_rein"];
$db_abfrage = "SELECT HAUS_DAT, HAUS_ID, HAUS_STRASSE, HAUS_NUMMER FROM HAUS WHERE OBJEKT_ID='$objekt_id' && HAUS_AKTUELL='1' ORDER BY HAUS_STRASSE, HAUS_NUMMER ASC";
$resultat = mysql_query($db_abfrage) or
           die(mysql_error());
$numrows = mysql_numrows($resultat);
if($numrows < 1) {
	echo "<h2 class=\"fehler\">Keine Häuser im ausgewählten Objekt</h2><br>\n";
	echo "Erst Haus im Objekt anlegen - <a href=\"?formular=haus&daten_rein=anlegen\">Hauseningabe hier&nbsp;</a>\n<br>\n";
}else{
  while (list ($HAUS_DAT, $HAUS_ID, $HAUS_STRASSE, $HAUS_NUMMER) = mysql_fetch_row($resultat)){
  echo "<a class=\"objekt_links\" href=\"?formular=haus&daten_rein=$daten_rein&objekt_id=$objekt_id&haus_id=$HAUS_ID\">$HAUS_STRASSE $HAUS_NUMMER</a><br>\n";
  }
}
}


?>
