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
 * @filesource   $HeadURL: http://192.168.2.52/svn/berlussimo_1/tags/02.11.2010 - Downloadversion 0.27/options/formulare/form_objekte.php $
 * @version      $Revision: 6 $
 * @modifiedby   $LastChangedBy: sivac $
 * @lastmodified $Date: 2010-09-21 10:34:50 +0200 (Di, 21 Sep 2010) $
 * 
 */
include_once("includes/formular_funktionen.php");
include_once("includes/allgemeine_funktionen.php");
include_once("options/links/links.form_objekte.php");
#echo $_POST["objekt_kurzname"];
$objekt_kurzname = $_REQUEST["objekt_kurzname"];
$eigentuemer = $_REQUEST["eigentuemer"];
$submit_obj_erstellen = $_REQUEST["obj_erstellen"];
$daten_rein = $_REQUEST["daten_rein"];
$objekt_in_db = $_REQUEST["objekt_in_db"];
$aendern_in_db = $_REQUEST["aendern_in_db"];
$obj_update = $_REQUEST["obj_update"];
switch($daten_rein) {

    case "anlegen":
    $form = new mietkonto;
    $form->erstelle_formular("Neues Wohnobjekt anlegen", NULL);
    iframe_start();
    echo "<h1>Objekt anlegen</h1>";
    if(!isset($submit_obj_erstellen)){
    #$form->erstelle_formular("Neues Wohnobjekt anlegen", NULL);
    erstelle_eingabefeld('Objekt Kurzname', 'objekt_kurzname', NULL, 20); //name, wert, size
    $partner = new partner;
    
    $partner_arr=$partner->partner_dropdown('Eigent�mer', 'eigentuemer', 'eigentuemer');
    erstelle_submit_button(obj_erstellen, Erstellen); //name, wert
    ende_formular();
    objekte_liste();
    }
        if(isset($submit_obj_erstellen)){
    	$objekt_kurzname = trim($objekt_kurzname);    	
    	if($objekt_kurzname != ''){
    	#erstelle_formular('objekt_in_db', '?formular=objekte&daten_rein=objekt_in_db'); //name, action
    	$form->erstelle_formular("Wohnobjekt anlegen", '?formular=objekte&daten_rein=objekt_in_db');
    	echo "<tr><td><h1>Folgende Daten wurden �bermittelt:\n</h1></td></tr>\n";
    	echo "<tr><td><h2>Objektkurzname: $objekt_kurzname</h2></td></tr>\n";
    	echo "<tr><td>";
    	warnung_ausgeben("Sind Sie sicher, da� Sie das Objekt $objekt_kurzname anlegen wollen? $eigentuemer");
    	echo "</td></tr>";
    	erstelle_hiddenfeld("daten_rein", "objekt_in_db");
    	erstelle_hiddenfeld("objekt_kurzname", "$objekt_kurzname");
    	erstelle_hiddenfeld("eigentuemer", "$eigentuemer");
    	erstelle_submit_button("obj_eintragen", "Speichern"); //name, wert
    	ende_formular();
    	
    	}
    	else{
    		fehlermeldung_ausgeben("Bitte geben Sie dem Objekt einen Kurznamen!");
    	backlink();
    	break;
    	}
    }
    iframe_end();
    $form->ende_formular();
    break;
    
    
    case "objekt_in_db":
    iframe_start();
    $objekt_kurzname = trim($objekt_kurzname);
    	if($objekt_kurzname != ''){
    	$kurzname_existiert = objekt_kurzname_anzahl($objekt_kurzname);
    		if($kurzname_existiert < 1){
        	neues_objekt_anlegen($objekt_kurzname, $eigentuemer); //obj_id, kurzname - id mu� eingegeben werden
    		hinweis_ausgeben("$objekt_kurzname wurde als Verwaltungsobjekt angelegt.");
    		weiterleiten_in_sec("?daten=objekte_raus&objekte_raus=objekte_kurz", 2);
    		}else{
    		fehlermeldung_ausgeben("Objekt $objekt_kurzname existiert schon!!!");
    		}
    	}else{
    		fehlermeldung_ausgeben("Fehler beim speichern des Objekts $objekt_kurzname");
    	}
    iframe_end();
    break;
    
    
    case "aendern_liste":
    $form = new mietkonto;
    $form->erstelle_formular("Wohnobjekt �ndern", NULL);
    iframe_start();
    echo "<h1>Objekte �ndern</h1>";
    liste_aktueller_objekte_edit();
    iframe_end();
    $form->ende_formular();
    break;
    
    case "aendern":
    $form = new mietkonto;
    $form->erstelle_formular("Wohnobjekt �ndern", NULL);
    
    iframe_start();
    $obj_id = $_REQUEST["obj_id"];
    $obj_id = trim($obj_id);
    $submit_update_objekt = $_REQUEST["submit_update_objekt"];
    $neu_objekt_kurzname = $_REQUEST["objekt_kurzname"];
    $neu_objekt_kurzname = trim($neu_objekt_kurzname);
    $objekt_dat = $_REQUEST["objekt_dat"];
    if(isset($submit_update_objekt)){
    	if($neu_objekt_kurzname != ''){
    	echo "$neu_objekt_kurzname $objekt_dat";
    	#objekt_update_kurzname($objekt_dat, $obj_id, $neu_objekt_kurzname);	
    	erstelle_formular(objekt_in_db, '?formular=objekte&daten_rein=aendern_in_db'); //name, action
    	echo "<tr><td><h1>Folgende Daten wurden �bermittelt:\n</h1></td></tr>\n";
    	echo "<tr><td><h2>Objektkurzname: $objekt_kurzname</h2></td></tr>\n";
    	echo "<tr><td>";
    	warnung_ausgeben("Sind Sie sicher, da� Sie das Objekt in $objekt_kurzname �ndern wollen?");
    	echo "</td></tr>";
    	erstelle_hiddenfeld("obj_id", "$obj_id");
    	erstelle_hiddenfeld("objekt_dat", "$objekt_dat");
    	erstelle_hiddenfeld("objekt_kurzname", "$objekt_kurzname");
    	erstelle_submit_button("obj_update", "�ndern"); //name, wert
    	ende_formular();
    	   	
    	}	else{
    		fehlermeldung_ausgeben("Bitte geben Sie dem Objekt einen Kurznamen!");
    			}
    }
    if(!isset($submit_update_objekt)){
    objekt_zum_aendern_holen($obj_id);
    }
    iframe_end();
    break;
    
    case "aendern_in_db":
    iframe_start();
    if(isset($obj_update)){
    $obj_id = $_REQUEST["obj_id"];
    $obj_id = trim($obj_id);
    $neu_objekt_kurzname = $_REQUEST["objekt_kurzname"];
    $neu_objekt_kurzname = trim($neu_objekt_kurzname);
    $objekt_dat = $_REQUEST["objekt_dat"];
    #echo "$objekt_dat, $obj_id, $neu_objekt_kurzname";
    		$kurzname_existiert = objekt_kurzname_anzahl($objekt_kurzname);
    		if($kurzname_existiert < 1){
    		objekt_update_kurzname($objekt_dat, $obj_id, $neu_objekt_kurzname);
    		hinweis_ausgeben("objekt_kurzname wurde in $neu_objekt_kurzname umbenannt.");
    		weiterleiten("?daten=objekte_raus&objekte_raus=objekte_kurz");
    		}else{
    			fehlermeldung_ausgeben("Objekt $objekt_kurzname existiert schon!!!");
    			hinweis_ausgeben("Keine �nderungen wurden vorgenommen!!!");
    			
    		}
    }
    iframe_end();
    $form->ende_formular();
    break;
    
    
    
    case "loeschen":
    iframe_start();
    $obj_dat = $_REQUEST["obj_dat"];
    $objekt_kurzname = objekt_kurzname_finden($obj_dat);
    
    if(!isset($_REQUEST[obj_loeschen])){
    erstelle_formular(NULL,NULL); //name, action
    echo "<tr><td><h1>Objektkurzname: $objekt_kurzname</h2></td></tr>\n";
    echo "<tr><td>";
    warnung_ausgeben("Sind Sie sicher, da� Sie das Objekt $objekt_kurzname l�schen wollen?");
    echo "</td></tr>";
    erstelle_hiddenfeld("obj_dat", "$obj_dat");
    erstelle_submit_button("obj_loeschen", "L�schen"); //name, wert
    ende_formular();
    }
    if(isset($_REQUEST[obj_loeschen])){
    objekt_loeschen($obj_dat);
    hinweis_ausgeben("$objekt_kurzname wurde gel�scht!");
    weiterleiten("?daten=objekte_raus&objekte_raus=objekte_kurz");
    }
    iframe_end();
    break;
     
    }

function objekte_liste(){
$db_abfrage = "SELECT OBJEKT_KURZNAME FROM OBJEKT WHERE OBJEKT_AKTUELL='1' ORDER BY OBJEKT_KURZNAME ASC ";
$resultat = mysql_query($db_abfrage) or
           die(mysql_error());
echo "<div class=\"tabelle_objekte\"><table>\n";
echo "<tr class=\"feldernamen\"><td>Objektliste</td></tr>\n";
echo "<tr class=\"feldernamen\"><td>Objektkurznamen</td></tr>\n";
$counter = 0;
while (list ($OBJEKT_KURZNAME) = mysql_fetch_row($resultat)){
$counter++;
if($counter == 1){
echo "<tr class=\"zeile1\"><td>$OBJEKT_KURZNAME</td></tr>\n";  
}
if($counter == 2){
echo "<tr class=\"zeile2\"><td>$OBJEKT_KURZNAME</td></tr>\n";
$counter = 0;
}
} 
echo "</table></div>";
}

?>
