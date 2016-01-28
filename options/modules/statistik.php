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
 * @filesource   $HeadURL: http://192.168.2.52/svn/berlussimo_1/tags/02.11.2010 - Downloadversion 0.27/options/modules/statistik.php $
 * @version      $Revision: 15 $
 * @modifiedby   $LastChangedBy: sivac $
 * @lastmodified $Date: 2011-07-07 10:41:33 +0200 (Do, 07 Jul 2011) $
 * 
 */

/*Allgemeine Funktionsdatei laden*/
include_once("includes/allgemeine_funktionen.php");

/*Überprüfen ob Benutzer Zugriff auf das Modul hat*/
if(!check_user_mod($_SESSION['benutzer_id'], 'statistik')){
	echo '<script type="text/javascript">';
	echo "alert('Keine Berechtigung')";
	echo '</script>';	
	die();
}

/*Klasse "formular" für Formularerstellung laden*/
include_once("classes/class_statistik.php");

/*Modulabhängige Dateien d.h. Links und eigene Klasse*/
include_once("options/links/links.statistik.php");

$option = $_REQUEST["option"];
switch($option) {
	
	case "leer_vermietet_jahr":
   	$s = new statistik;
   	#$s->create_moths('2009');
   	$s->get_stat('2010','1', '');
   	$s->get_stat('2011','1', '');
   	$s->get_stat('2012','1', '');
   	$s->get_stat('2013','1', '');
   	$s->get_stat('2014','1', '');
   	$s->get_stat('2015','1', '');
	
   	$s->get_stat('2010','2', '');
  	$s->get_stat('2011','2', '');
  	$s->get_stat('2012','2', '');
  	$s->get_stat('2013','2', '');
   	$s->get_stat('2014','2', '');
	$s->get_stat('2015','2', '');
	
	$s->get_stat('2010','3', '');
	$s->get_stat('2011','3', '');
	$s->get_stat('2012','3', '');
	$s->get_stat('2013','3', '');
	$s->get_stat('2014','3', '');
	$s->get_stat('2015','3', '');
	
   	$s->get_stat('2010','13', '');
   	$s->get_stat('2011','13', '');
   	$s->get_stat('2012','13', '');
   	$s->get_stat('2013','13', '');
   	$s->get_stat('2014','13', '');
   	$s->get_stat('2015','13', '');
	
   	$s->get_stat('2010','14', '');
   	$s->get_stat('2011','14', '');
   	$s->get_stat('2012','14', '');
   	$s->get_stat('2013','14', '');
   	$s->get_stat('2014','14', '');
   	$s->get_stat('2015','14', '');

   	
   	
   	
   	
   	break;
	
	
   	case "leer_haus_stat":
   	$s = new statistik;
   	$s->form_haus_leer_stat();
   
   	break;
   	
   	
   	case "leer_haus_stat1":
   		#print_req();
   		$s = new statistik;
   		$akt_jahr = Date("Y");
   		$start_j = $akt_jahr-4;
   		
   		$h = new haus;
   		$h->get_haus_info($_POST['haus_id']);
   		echo "<h1>$h->haus_strasse $h->haus_nummer</h1>";
   		for($a=$start_j;$a<=$akt_jahr;$a++){
   		$s->stat_haus_anzeigen($_POST['haus_id'], $a);
   		}
   		break;
   	
   	
	case "stellplaetze":
   	$objekt_id = $_REQUEST[objekt_id];
   	if(empty($objekt_id)){
   		$objekt_id = 4;//Block E
   	}
   	$s = new statistik;
   	$s->get_stat('2008',$objekt_id,'Stellplatz');//Block E
   	$s->get_stat('2009',$objekt_id,'Stellplatz');//Block E
   	$s->get_stat('2010',$objekt_id,'Stellplatz');//Block E
   	break;
   	
   	case "garage":
   	$objekt_id = $_REQUEST[objekt_id];
   	if(empty($objekt_id)){
   		$objekt_id = 13;//GBN
   	}
   	$s = new statistik;
   	$s->get_stat('2008',$objekt_id,'Garage');//GBN
   	$s->get_stat('2009',$objekt_id,'Garage');//GBN
   	$s->get_stat('2010',$objekt_id,'Garage');//GBN
   	break;
	
	case "keller":
   	$objekt_id = $_REQUEST[objekt_id];
   	if(empty($objekt_id)){
   		$objekt_id = 1;//II
   	}
   	$s = new statistik;
   	$s->get_stat('2008',$objekt_id,'Keller');//II
   	$s->get_stat('2009',$objekt_id,'Keller');//II
   	$s->get_stat('2010',$objekt_id,'Keller');//II
   	break;
	
   	
	case "leer_gesamt":
   	
   	for($a=2007;$a<=date("Y");$a++){
    	
    	$jahr = $a;
    	for($b=1;$b<13;$b++){
    		$monat = $b;
    		if(strlen($monat) <2){
			$monat = '0'.$monat;
			}
			$monatsname = monat2name($monat);
			echo "Leerstand gesamt $monatsname $jahr<br>";
   	$s = new statistik;
   	echo $s->leerstand_alle("$jahr-$monat",'');
   	echo "<hr>";
    	}
    	
    	
    }
   	break;
	   	
   	
	case "sollmieten_aktuell":
   	$objekt_id = $_SESSION['objekt_id'];
   	if(empty($objekt_id)){
   		$objekt_id = 4;//Block E
   	}
   	$s = new statistik;
   	$s->summe_sollmiete_alle();
   	break;
   	
   	
   	case "verwaltergebuehr_objekt":
   	$objekt_id = $_REQUEST[objekt_id];
   	if(empty($objekt_id)){
   		$objekt_id = 4;//Block E
   	}
   	$s = new statistik;
   	$s->summe_sollmieten($objekt_id);
   	break;
   	
   	
   	case "verwaltergebuehr_objekt_pdf":
   	$objekt_id = $_REQUEST[objekt_id];
   	if(empty($objekt_id)){
   		$objekt_id = 4;//Block E
   	}
   	$s = new statistik;
   	$s->summe_sollmieten_pdf($objekt_id);
   	break;
   	
   	
   	
   	case "sollmieten_haeuser":
   	$s = new statistik;
   	$s->form_haeuser_auswahl();
   	break;
	
	
	default:
 	echo "STATISTIKEN HAUPTSEITE";
 	break;  
   	
   	case "me_k":
   	$s = new statistik;
   	$jahr = 2010;
   	unset($_SESSION[daten_arr]);
   	#$s->stat_kosten_me_jahr($geldkonto_id, $jahr);
   	$s->kosten_einnahmen_k('4', $jahr,'II', 'Euro');
   	$s->kosten_einnahmen_k('5', $jahr,'III', 'Euro');
   	$s->kosten_einnahmen_k('6', $jahr,'V', 'Euro');
   	$s->kosten_einnahmen_k('11', $jahr,'Block E', 'Euro');
	$s->kosten_einnahmen_k('7', $jahr,'GBN', 'Euro');
	$s->kosten_einnahmen_k('8', $jahr,'HW', 'Euro');
	$s->kosten_einnahmen_k('10', $jahr,'FON', 'Euro');
	break;
   	
   	
   	case "testen":
   	$s = new statistik;
   	#$s->alle_mvs_einheit_arr();
   	$bg = new berlussimo_global;
   	$link = "?daten=statistik&option=testen";
   	if(!empty($_REQUEST[jahr])){
   	$jahr = $_REQUEST[jahr];
   	}else{
   		$jahr = date("Y");
   	}
   	if(!empty($_REQUEST[monat])){
   	$monat = $_REQUEST[monat];
   	}else{
   		$monat = date("m");
   	}
   	$bg->monate_jahres_links($jahr, $link);
   	$s->vermietete_monat_jahr_neu($jahr, $monat);
   	break;
   	
   	case "baustelle_manuell";
   	$s = new statistik;
   	$f = new formular;
   	$f->erstelle_formular("Baustelenübersicht", NULL);
   	$s->baustellen_leistung('Einheit', '166', 25, '2009-11-01', '2009-11-31','Plätzer Sansi 53');
   	#$s->baustellen_leistung('Einheit', '168', 25);
   	#$s->baustellen_leistung('Einheit', '304', 25);
   	#$s->baustellen_leistung('Einheit', '32', 25);
   	#$s->baustellen_leistung('Einheit', '368', 25);
   	$s->baustellen_leistung('Einheit', '368', 25, '2010-04-12', '2010-05-30','Laßnack Badsanierung MODs');
   	$f->ende_formular();	
   	
   	case "baustelle";
   	$s = new statistik;
   	$f = new formular;
   	$f->erstelle_formular("Baustelenübersicht", NULL);
   	$s->baustellen_uebersicht2(600);
   	$s->baustellen_uebersicht();
   	$f->ende_formular();	
   	break;
   	
   	case "fenster";
   	$s = new statistik;
   	$f = new formular;
   	$f->erstelle_formular("Fensterübersicht", NULL);
   	$s->fenster_uebersicht();
   	$f->ende_formular();	
   	break;
   	
   	case "fenster_zuweisen";
   	#print_req();
   	#die();
   	if(isset($_REQUEST['sndBtn'])){
   		if($_REQUEST['rest'] < $_REQUEST['anz_fenster']){
   			fehlermeldung_ausgeben("Eingegebene Menge größer als Restmenge!");
   			die();
   		}else{
   			$s = new statistik();
   			if($s->fenster_zuweisen($_REQUEST['beleg_id'], $_REQUEST['pos'], $_REQUEST['anz_fenster'], $_REQUEST['Einheit'])){
   				weiterleiten("?daten=statistik&option=fenster");
   			}
   		}
   	}	
   	break;
   	
   	case "lieferung_eingeben":
   	#print_req();
   	if(isset($_REQUEST['lsndBtn'])){
   		if(!empty($_REQUEST['beleg_id_l']) && !empty($_REQUEST['pos_l'])){
   		$s = new statistik();
   		if($s->lieferung_speichern($_REQUEST['beleg_id_l'], $_REQUEST['pos_l'])){
   			weiterleiten("?daten=statistik&option=fenster");
   		}else{
   			weiterleiten_in_sec("?daten=statistik&option=fenster", 3);
   		}
   		}else{
   			fehlermeldung_ausgeben("BelegID und Position eingeben");
   		}
   	}
   	break;
   	
   	case "lieferung_loeschen":
   		if(!empty($_REQUEST['beleg_id']) && !empty($_REQUEST['pos'])){
   		$s = new statistik();
   		if($s->lieferung_loeschen($_REQUEST['beleg_id'], $_REQUEST['pos'])){
   			weiterleiten("?daten=statistik&option=fenster");
   		}else{
   			weiterleiten_in_sec("?daten=statistik&option=fenster", 3);
   		}
   	}else{
   		fehlermeldung_ausgeben("Eingabe unvollständig Z261");
   		weiterleiten_in_sec("?daten=statistik&option=fenster", 3);
   	}
   	break;
   	
   	case "zuweisung_loeschen":
	if(!empty($_REQUEST['beleg_id']) && !empty($_REQUEST['pos']) &&  !empty($_REQUEST['einheit_id'])){
   		$s = new statistik();
   		if($s->zuweisung_loeschen($_REQUEST['beleg_id'], $_REQUEST['pos'], $_REQUEST['einheit_id'])){
   			weiterleiten("?daten=statistik&option=fenster");
   		}else{
   			weiterleiten_in_sec("?daten=statistik&option=fenster", 3);
   		}
   	}else{
   		fehlermeldung_ausgeben("Eingabe unvollständig Z262");
   		weiterleiten_in_sec("?daten=statistik&option=fenster", 3);
   	}
   	break;
   	
   	
   	case "bau_stat_menu":
   	$s = new statistik;
   	$s->form_einheit_suche();
   	break;
   	
   	case "einheit_suche_bau":
   	
   	#print_req();
   	if(isset($_POST['einheit_bez']) && !empty($_POST['einheit_bez'])){
   	$e = new einheit;
   	$e->get_einheit_id($_POST['einheit_bez']);
   	if(isset($e->einheit_id)){
   		#echo "$e->einheit_id gefunden";
   		$s = new statistik;
   		$s->kontrolle_bau_tab('Einheit', $e->einheit_id);
   		
   	}else{
   		echo "nicht gefunden";
   	}
   
   	}
   	break;
   	
   	
   	   	
}

?>
