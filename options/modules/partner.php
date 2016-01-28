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
 * @filesource   $HeadURL: http://192.168.2.52/svn/berlussimo_1/tags/02.11.2010 - Downloadversion 0.27/options/modules/partner.php $
 * @version      $Revision: 15 $
 * @modifiedby   $LastChangedBy: sivac $
 * @lastmodified $Date: 2011-07-07 10:41:33 +0200 (Do, 07 Jul 2011) $
 * 
 */

/*Allgemeine Funktionsdatei laden*/
include_once("includes/allgemeine_funktionen.php");

/*Überprüfen ob Benutzer Zugriff auf das Modul hat*/
if(!check_user_mod($_SESSION['benutzer_id'], 'partner')){
	echo '<script type="text/javascript">';
	echo "alert('Keine Berechtigung')";
	echo '</script>';	
	die();
}

/*Klasse "formular" für Formularerstellung laden*/
include_once("classes/class_formular.php");

/*Modulabhängige Dateien d.h. Links und eigene Klasse*/
include_once("options/links/links.partner.php");
include_once("classes/class_partners.php");
	if(isset($_REQUEST["option"])){
	$option = $_REQUEST["option"];
	}else{
		$option = 'default';
	}

/*Optionsschalter*/
switch($option) {

	default:
	$p = new partners;
	$p->form_such_partner();
	break;
	
	
	case "partner_suchen1":
	#print_req();
	$p = new partners();
	$partner_arr = $p->suche_partner_in_array($_REQUEST['suchtext']);
	if(is_array($partner_arr)){
		$p->partner_liste_filter($partner_arr);
	}else{
		fehlermeldung_ausgeben("Keine Partner gefunden!");
	}
	break;
	
	/*Aufruf des Formulars für die 
	* Partner/Lieferantenerfassung*/
	case "partner_erfassen":
   	$form = new formular;
    $form->erstelle_formular("Partner / Lieferanten / Eigentümer anlegen", NULL);
   	$partners = new partners;
    #$partners->partner_rechts_anzeigen();
    $partners->form_partner_erfassen();
    $form->ende_formular();
    break;

	case "partner_gesendet":
   	$partners = new partners;
   	$partners->partner_rechts_anzeigen();
   	$form = new formular;
   	$form->erstelle_formular("Partnerdaten überprüfen", NULL);
   	echo "<p><b>Übermittelte Partnerdaten:</b></p>";
   	#$form->array_anzeigen($_POST);
   	$clean_arr = $form->post_array_bereinigen();
    #$form->array_anzeigen($clean_arr);
   	foreach ($clean_arr as $key => $value) {
    	if(($key != 'submit_partner') AND ($key != 'option')){
    	echo "" . $value . "<br>";
  		$form->hidden_feld($key, $value);
  		}
   	}
   		if(!$fehler){
   		$form->hidden_feld("option", "partner_gesendet1");
   		$form->send_button("submit_partner1", "Speichern");
   		}else{
   		echo "Daten unvollständig";	
   	}
   	$form->ende_formular();
   	break;
   	
   	case "partner_gesendet1":
   	$form = new formular;
   	$clean_arr = $form->post_array_bereinigen();
   	$form->erstelle_formular("Partnerdaten speichern", NULL);
   	#print_r($clean_arr);
   	$partners = new partners;
   	$partners->partner_speichern($clean_arr);
   	$form->ende_formular();
   	break;
   	
   	case "partner_liste":
   	$form = new formular;
   	$form->erstelle_formular("Partnerliste", NULL);
   	$partner = new partners;
   	$partner->partner_liste();
   	$form->ende_formular();
   	break;
   	
   	
   	case "partner_stichwort":
   	if(isset($_REQUEST['partner_id']) && !empty($_REQUEST['partner_id'])){
   	$pp = new partners;
   	$pp->form_partner_stichwort_neu($_REQUEST['partner_id']);
   	$pp->form_partner_stichwort($_REQUEST['partner_id']);
	
   	}
   	break;
   	
   	case "partner_stich_sent":
   	if(isset($_POST['stichworte'])){
   		#print_req();
   	$anz_stich = count($_POST['stichworte']);
   #	echo "ANZ $anz_stich";
   	$partner_id = $_POST['partner_id'];
   	$pp = new partners;
   	$pp->stichworte_speichern($partner_id, $_POST['stichworte']);
   	weiterleiten("?daten=partner&option=partner_stichwort&partner_id=$partner_id");
   	}
   	break;
   	
   	case "partner_stich_sent_neu":
   		#print_req();
   		if(isset($_POST['partner_id']) && isset($_POST['stichwort']) && !empty($_POST['stichwort'])){
   			$stichwort = $_POST['stichwort'];
   			$partner_id = $_POST['partner_id'];
   			$pp = new partners;
   			$pp->stichwort_speichern($partner_id, $stichwort);
   			weiterleiten("?daten=partner&option=partner_stichwort&partner_id=$partner_id");
   		}
   		break;
   	
   	
   	case "partner_umsatz":
   	$form = new formular;
   	$form->erstelle_formular("Partnerliste nach Umsatz", NULL);
   	$partner = new partners;
   	$arr = $partner->partner_nach_umsatz();
   	echo "<pre>";
   	#print_r($arr);
   	$anz = count($arr);
   	if($anz){
   		echo "<table class=\"sortable\">";
   		echo "<tr><th>PARTNER</th><th>NETTO</th><th>BRUTTO</th></tr>";
   		for($a=0;$a<$anz;$a++){
   			$p_name = $arr[$a]['PARTNER_NAME'];
   			$netto = nummer_punkt2komma_t($arr[$a]['NETTO']);
   			$brutto = nummer_punkt2komma_t($arr[$a]['BRUTTO']);
   		echo "<tr><td>$p_name</td><td>$netto</td><td>$brutto</td>";
   		}
   		echo "</table>";
   	}
   	$form->ende_formular();
   	break;
   	
   	case "partner_im_detail":
   	$form = new formular;
   	$form->erstelle_formular("Partnerdetails", NULL);
   	$partner = new partner;
   	$partner_id= $_REQUEST[partner_id];
   	$partner->partnerdaten_anzeigen($partner_id);
   	$d = new detail();
   	$d->detailsanzeigen('PARTNER_LIEFERANT', $partner_id);
   	$form->ende_formular();
   	
   	break;
	
	case "partner_aendern":
   	if(!empty($_REQUEST[partner_id])){
   	$partner = new partners;
   	$partner->form_partner_aendern($_REQUEST[partner_id]);
   	}else{
   		echo "Bitte den Partner zum Ändern wählen.";
   	}
   	break;
	

	case "partner_aendern_send":
	if($_POST){
		if(!empty($_POST[partner_dat]) && !empty($_POST[partner_id]) && !empty($_POST[partnername]) && !empty($_POST[strasse]) && !empty($_POST[hausnummer]) && !empty($_POST[plz]) && !empty($_POST[ort]) && !empty($_POST[land])){
			echo "alles OK";
			$p = new partners;
			$p->partner_aendern($_POST[partner_dat],$_POST[partner_id], $_POST[partnername],$_POST[strasse],$_POST[hausnummer],$_POST[plz],$_POST[ort],$_POST[land]);
			weiterleiten("?daten=partner&option=partner_im_detail&partner_id=$_POST[partner_id]");
			}
			else{
			echo "DATEN UNVOLLSTÄNDIG";
			}
		
	}else{
		echo "Daten unvollständig";
	}
	break;
	
	/*Auswahlmaske Empfänger*/
	case "serienbrief":
	#echo "Serienbriefe an Partner";
	$pp = new partners();
	$pp->form_partner_serienbrief();
	break;
	
	
	case "serien_brief_vorlagenwahl":
		#	p($_POST);
		#	print_req();
		if(isset($_REQUEST['delete'])){
			unset($_SESSION['p_ids']);
			$_SESSION['p_ids'] = array();
			echo "Alle gelöscht!";
			break;
			#weiterleiten_in_sec("?daten=weg&option=serienbrief", 2);
		}
		if(!isset($_SESSION['p_ids'])){
			$_SESSION['p_ids'] = array();
		}
		if(isset($_POST['p_ids']) && is_array($_POST['p_ids'])){
			#p($_POST['eig_ids']);
			$_SESSION['p_ids'] = array_merge($_SESSION['p_ids'], $_POST['p_ids']);
			$_SESSION['p_ids'] = array_unique($_SESSION['p_ids']);
			#p($_SESSION);
			$s = new serienbrief();
			if(isset($_REQUEST['kat'])){
				$s->vorlage_waehlen('Partner', $_REQUEST['kat']);
			}else{
				$s->vorlage_waehlen('Partner');
			}
		}else{
			fehlermeldung_ausgeben("Bitte Partner aus Liste wählen!");
		}
		break;
	
		case "serienbrief_pdf":
			#p($_SESSION);
			#unset($_SESSION['eig_ids']);
			print_req();
			$bpdf = new b_pdf;
			$s = new serienbrief();
			$s->erstelle_brief_vorlage($_REQUEST['vorlagen_dat'], 'Partner', $_SESSION['p_ids'], $option='0');
			#$bpdf->form_mieter2sess();
			break;
	
	

}
?>
