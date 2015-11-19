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
 * @filesource   $HeadURL: http://192.168.2.52/svn/berlussimo_1/tags/02.11.2010 - Downloadversion 0.27/options/modules/rechnungen.php $
 * @version      $Revision: 15 $
 * @modifiedby   $LastChangedBy: sivac $
 * @lastmodified $Date: 2011-07-07 10:41:33 +0200 (Do, 07 Jul 2011) $
 * 
 */

include_once("includes/allgemeine_funktionen.php");

/*�berpr�fen ob Benutzer Zugriff auf das Modul hat*/
if(!isset($_SESSION['benutzer_id']) OR !check_user_mod($_SESSION['benutzer_id'], 'rechnungen')){
	echo '<script type="text/javascript">';
	echo "alert('Keine Berechtigung')";
	echo '</script>';	
	die();
}

include_once("options/links/links.rechnungen.php");
include_once("classes/mietkonto_class.php");
include_once("classes/berlussimo_class.php");
include_once("classes/class_partners.php");
include_once("classes/class_rechnungen.php");
include_once("classes/class_buchen.php");

if(!empty($_REQUEST["option"])){
$option = $_REQUEST["option"];
}else{
$option = 'default';
}
switch($option) {
   
    case "rechnung_erfassen":
   	$form = new mietkonto;
   	$rechnungsformular = new rechnung;
    $rechnungsformular->form_rechnung_erfassen();
   	$form->ende_formular();
    break;
    
    case "gutschrift_erfassen":
   	$form = new mietkonto;
   	$rechnungsformular = new rechnung;
    $rechnungsformular->form_gutschrift_erfassen();
   	$form->ende_formular();
    break;
    
    case "rechnung_an_kasse_erfassen":
   	$form = new mietkonto;
   	$rechnungsformular = new rechnung;
    $rechnungsformular->form_rechnung_erfassen_an_kasse();
   	$form->ende_formular();
    break;
    
    
    case "rechnung_erfassen1":
   	$form = new mietkonto;
   	$form->erstelle_formular("Rechnungsdaten �berpr�fen", NULL);
   	echo "<p><b>Eingegebene Rechnungsdaten:</b></p>";
   	$clean_arr = post_array_bereinigen();
   	#$form->array_anzeigen($clean_arr);
   	foreach ($clean_arr as $key => $value) {
    	
    	if(($key != 'submit_rechnung1') AND ($key != 'option')){
    	#echo "$key " . $value . "<br>";
  		$form->hidden_feld($key, $value);
  		}
   	}
   	if($clean_arr['aussteller_id'] == $clean_arr['empfaenger_id'] ){
   		#$fehler = true;
   		fehlermeldung_ausgeben("Rechnungsaussteller- und Empf�nger sind identisch.<br>");
   	}
   	
   	if(!isset($fehler)){
   		if($clean_arr['empfaenger_typ'] == 'Kasse'){
   		$kassen_info = new kasse;
   		$kassen_info->get_kassen_info($clean_arr['Empfaenger']);
   		$partner_info = new partner;
   		$aussteller = $partner_info->get_partner_name($clean_arr['Aussteller']);
   		$empfaenger = "".$kassen_info->kassen_name." - ".$kassen_info->kassen_verwalter."";	
   		}
   		if($clean_arr['empfaenger_typ'] == 'Partner'){
   		$partner_info = new partner;
   		$aussteller = $partner_info->get_partner_name($clean_arr['aussteller_id']);
   		$empfaenger = $partner_info->get_partner_name($clean_arr['empfaenger_id']);
   		}
   		
   		echo "Rechnungsnummer: $clean_arr[rechnungsnummer]<br>";
   		echo "Eingangsdatum: $clean_arr[eingangsdatum]<br>";
   		if (preg_match("/,/i", $clean_arr['nettobetrag'])) {
   		$clean_arr['nettobetrag'] = nummer_komma2punkt($clean_arr['nettobetrag']);
   		}
   		if (preg_match("/,/i", $clean_arr['bruttobetrag'])) {
   		$clean_arr['bruttobetrag'] = nummer_komma2punkt($clean_arr['bruttobetrag']);
   		}
   		if (preg_match("/,/i", $clean_arr['skontobetrag'])) {
   		$clean_arr['skontobetrag'] = nummer_komma2punkt($clean_arr['skontobetrag']);
   		}
   		
   		$netto_betrag_komma =   nummer_punkt2komma($clean_arr['nettobetrag']); 		
   		$brutto_betrag_komma =   nummer_punkt2komma($clean_arr['bruttobetrag']);
   		
   		#echo "Nettobetrag: $netto_betrag_komma �<br>";
   		#echo "Bruttobetrag: $brutto_betrag_komma �<br>";
   		echo "F�llig am: $clean_arr[faellig_am] <br>";
   		echo "Kurzbeschreibung: $clean_arr[kurzbeschreibung] <br>";
   		$geld_konto_info = new geldkonto_info;
   		$geld_konto_info->dropdown_geldkonten('Partner', $clean_arr['aussteller_id']);
   		
   		$form->hidden_feld("option", "rechnung_erfassen2");
   		$form->send_button("submit_rechnung2", "Rechnung speichern");
   		
   		}else{
   			backlink();	
   	}
   	$form->ende_formular();
    break;
    
      case "rechnung_erfassen2":
   	$form = new mietkonto;
   	$form->erstelle_formular("Rechnungsdaten werden gespeichert", NULL);
   	echo "<p><b>Gespeicherte Rechnungsdaten:</b></p>";
   	$clean_arr = post_array_bereinigen();
   	#$form->array_anzeigen($clean_arr);
   	$rechnung = new rechnung;
   	$rechnung->rechnung_speichern($clean_arr);
   	$form->ende_formular();
    break;
    
    
    
    
    case "erfasste_rechnungen":
   	$form = new mietkonto;
    $form->erstelle_formular("Erfasste Rechnungen", NULL);
   	$rechnung = new rechnung;
    $rechnung->erfasste_rechungen_anzeigen(); //LIMIT 10,10
    $form->ende_formular();
    break;
    
    
    case "vollstaendige_rechnungen":
   	$form = new mietkonto;
    $form->erstelle_formular("Vollst�ndig erfasste Rechnungen", NULL);
   	$rechnung = new rechnung;
    $rechnung->vollstaendig_erfasste_rechungen_anzeigen();
    $form->ende_formular();
    break;
    
    case "unvollstaendige_rechnungen":
   	$form = new mietkonto;
    $form->erstelle_formular("Unvollst�ndig erfasste Rechnungen", NULL);
   	$rechnung = new rechnung;
    $rechnung->unvollstaendig_erfasste_rechungen_anzeigen();
    $form->ende_formular();
    break;
    
    case "kontierte_rechnungen":
   	$form = new mietkonto;
    $form->erstelle_formular("Vollst�ndig kontierte Rechnungen", NULL);
   	$rechnung = new rechnung;
    $rechnung->vollstaendig_kontierte_rechungen_anzeigen();
    $form->ende_formular();
    break;
    
    case "nicht_kontierte_rechnungen":
   	$form = new mietkonto;
    $form->erstelle_formular("Unvollst�ndig oder nocht nicht kontierte Rechnungen", NULL);
   	$rechnung = new rechnung;
    $rechnung->unvollstaendig_kontierte_rechungen_anzeigen();
    $form->ende_formular();
    break;
    
    
    case "bezahlte_rechnungen":
   	$form = new formular;
    $form->fieldset("Bezahlte Rechnungen", 'bezalte_rechnungen');
   	$r = new rechnungen;
    $r->bezahlte_rechnungen_anzeigen();
    $form->fieldset_ende();
    break;
    
    
    case "unbezahlte_rechnungen":
   	$form = new formular;
    $form->fieldset("Unbezahlte Rechnungen", 'unbezahlte_rechnungen');
   	$r = new rechnungen;
    $r->unbezahlte_rechnungen_anzeigen();
    $form->fieldset_ende();
    break;
    
    
    case "bestaetigte_rechnungen":
   	$form = new formular;
    $form->fieldset("Bezahlte Rechnungen", 'bezalte_rechnungen');
   	$r = new rechnungen;
    $r->bestaetigte_rechnungen_anzeigen();
    $form->fieldset_ende();
    break;
    
    
   case "unbestaetigte_rechnungen":
   	$form = new formular;
    $form->fieldset("Unbezahlte Rechnungen", 'unbezahlte_rechnungen');
   	$r = new rechnungen;
    $r->unbestaetigte_rechnungen_anzeigen();
    $form->fieldset_ende();
    break;
    
    
    case "positions_pool":
   	$form = new mietkonto;
    $form->erstelle_formular("Rechnung erstellen - Positionspool", NULL);
   	$rechnung = new rechnung;
    #$rechnung->positions_pool_anzeigen();
    #$rechnung->objekt_kosten_positionen(4);
    #$rechnung->haus_kosten_positionen(4);
    #$rechnung->einheit_kosten_positionen(4);
    $objekte_ids = $rechnung->objekte_im_pool();
    for($k=0;$k<count($objekte_ids);$k++){
    $objekt_id = $objekte_ids[$k];
    echo "$objekt_id<hr>";
    $haeuser_ids = $rechnung->haeuser_vom_objekt_im_pool($objekt_id);
    echo "<br>";
    $einheiten_ids = $rechnung->einheiten_vom_objekt_im_pool($objekt_id);
    echo "<hr>";
    }
    $form->ende_formular();
    break;
    
    case "pool_rechnungen":
   	$form = new mietkonto;
    $form->erstelle_formular("Rechnung erstellen - aus dem Positionspool", NULL);
   	$rechnung = new rechnung;
    /*$elemente_aus_pool[OBJEKTE] o. [HAUS] o. [EINHEITEN]*/
    $elemente_aus_pool = $rechnung->elemente_im_pool_baum();
    #print_r($elemente_aus_pool);
    $objekte_ids = $elemente_aus_pool['OBJEKTE'];
    $objekt_info = new objekt;
    $haus_info = new haus;
    $einheit_info = new einheit;
    $lager_info = new lager;
    if(is_array($elemente_aus_pool)){
    for($k=0;$k<count($objekte_ids);$k++){
    $objekt_id = $objekte_ids[$k];
    $objekt_info->get_objekt_name($objekt_id);
    $objekt_name = $objekt_info->objekt_name;
    $kontierung_id_arr = $rechnung->rechnung_aus_pool_zusammenstellen('Objekt', $objekt_id);
    $objekt_link_rechnung = "<a href=\"?daten=rechnungen&option=rechnung_an_objekt&objekt_id=$objekt_id\">Rechnung erstellen</a>";
   	
    $rrg = new rechnungen();
   	$summe_pool=$rrg->get_summe_kosten_pool('Objekt',$objekt_id);
    if($summe_pool>0){
    $objektkosten_link = "<a href=\"?daten=rechnungen&option=objektkosten_in_rechnung&objekt_id=$objekt_id\" style='color:blue;'>Rechnung erstellen ($summe_pool)</a>";
    }else{
    $objektkosten_link ='';	
    }
    echo "<hr><h3>$objekt_name</h3>";
	echo "<b>Objektbezogene Kosten vom $objekt_name</b><br>";
	echo "<b>|-</b>Gesamtrechnung f�r Objekt $objekt_name (inkl H�user / Einheiten) $objekt_link_rechnung<br>";
    echo "<b>|-</b>Objektkostenrechnung f�r Objekt $objekt_name $objektkosten_link<br>";
    $haeuser_ids = $elemente_aus_pool['HAUS'];
    
    if(is_array($haeuser_ids)){
      
    	echo "<b>&nbsp;&nbsp;&nbsp;H�userbezogene Kosten vom $objekt_name</b><br>";
    	#echo "<b>&nbsp;&nbsp;&nbsp;|-</b>Gesamtrechnung f�r alle H�user vom $objekt_name</b> <br>";
    	echo "<b>&nbsp;&nbsp;&nbsp;Rechnungen pro Haus - Haus w�hlen bitte</b><br>";
    	for($g=0;$g<count($haeuser_ids);$g++){
    	$haus_id = $haeuser_ids[$g];
    	
    	$rrg = new rechnungen();
   		$summe_pool=$rrg->get_summe_kosten_pool('Haus',$haus_id);
    	    	
    	$haus_info->get_haus_info($haus_id);
    	$haus_objekt_id = $haus_info->objekt_id;
    	$haus_link_rechnung = "<a href=\"?daten=rechnungen&option=rechnung_an_haus&haus_id=$haus_id\">Rechnung inkl. Einheiten</a>";
    	if($summe_pool>0){
    	$hauskosten_link = "<a href=\"?daten=rechnungen&option=hauskosten_in_rechnung&haus_id=$haus_id\" style='color:red;'>Nur Hauskosten ($summe_pool)</a>";
    	}else{
    	$hauskosten_link ='';	
    	}
    	if($objekt_id == $haus_objekt_id){
    	echo "<b>&nbsp;&nbsp;&nbsp;|-</b> Haus ".$haus_info->haus_strasse.$haus_info->haus_nummer." $haus_link_rechnung $hauskosten_link<br>";
    	}
    	}
    }//end if is_array $hauser_ids
    $einheiten_ids = $elemente_aus_pool['EINHEITEN'];
    if(is_array($einheiten_ids)){
    	echo "<b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;| Einheitsbezogene Kosten vom $objekt_name</b><br>";
    	#echo "<b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;|-</b>Gesamtrechnung f�r alle Einheiten vom $objekt_name</b> <br>";
    	echo "<b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;| Rechnungen pro Einheit - Einheit w�hlen bitte</b><br>";
    	for($e=0;$e<count($einheiten_ids);$e++){
    		$einheit_id = $einheiten_ids[$e];
    		$einheit_info->get_einheit_haus($einheit_id);
			$einheit_objekt_id = $einheit_info->objekt_id;
			
			$rrg = new rechnungen();
   			$summe_pool=$rrg->get_summe_kosten_pool('Einheit',$einheit_id);
    		
			
			if($einheit_objekt_id == $objekt_id){
			if($summe_pool>0){
			$einheit_link_rechnung = "<a href=\"?daten=rechnungen&option=rechnung_an_einheit&einheit_id=$einheit_id\" style='color:green;'>Rechnung erstellen ($summe_pool)</a>";
			}else{
			$einheit_link_rechnung ='';	
			}
				echo "<b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;|-</b> Einheit ".$einheit_info->einheit_kurzname."&nbsp;".$einheit_info->haus_strasse.$einheit_info->haus_nummer.$einheit_info->einheit_lage." $einheit_link_rechnung<br>";
			}
    	}
    }
    
    
    
    
      
    }//end for first
 
 
    }//end if is_array elemente
    echo "<hr>";
    $lager_ids = $elemente_aus_pool['LAGER'];
    if(is_array($lager_ids)){
    	echo "<b>| Lagerbezogene Kosten</b><br>";
    	echo "<b>&nbsp;&nbsp;&nbsp;| Rechnungen pro Lager - Lager w�hlen bitte</b><br>";
    	for($f=0;$f<count($lager_ids);$f++){
    		$lager_id = $lager_ids[$f];
    		$lager_bezeichnung = $lager_info->lager_bezeichnung($lager_id);
			#$lager_objekt_id = $lager_info->lager_objekt_id($lager_id);
			$rrg = new rechnungen();
   			$summe_pool=$rrg->get_summe_kosten_pool('Lager',$lager_id);
   			if($summe_pool>0){    		
    		$lager_link_rechnung = "<a href=\"?daten=rechnungen&option=rechnung_an_lager&lager_id=$lager_id\" style='color:white;'>Rechnung erstellen ($summe_pool)</a>";
			#$lager_link_csv = "<a href=\"?daten=rechnungen&option=pool_csv&kos_typ=Lager&kos_id=$lager_id\" style='color:brown;'>CSV</a>";
   			}else{
   			$lager_link_csv ='';
   			#$lager_link_rechnung ='';	
   			}
			echo "<b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;|-</b> Lager ".$lager_bezeichnung."&nbsp;".$einheit_info->haus_strasse.$einheit_info->haus_nummer.$einheit_info->einheit_lage." $lager_link_rechnung<br>";
			
    	}
    }else{
    	echo "Keine lagerbezogenen Daten im Pool";
    }
    
    
    echo "<hr>";
       $partner_ids = $elemente_aus_pool['PARTNER'];
    #print_r($partner_ids);
    if(is_array($partner_ids)){
    	echo "<b>| Partnerbezogene Kosten</b><br>";
    	echo "<b>&nbsp;&nbsp;&nbsp;| Rechnungen an Partner - Partner w�hlen bitte</b><br>";
    	$r = new rechnung;
    	for($f=0;$f<count($partner_ids);$f++){
    		$partner_id = $partner_ids[$f];
    		$rechnungs_empfaenger_name  =  $r->get_partner_name($partner_id);
    		
    		$rrg = new rechnungen();
   			$summe_pool=$rrg->get_summe_kosten_pool('Partner',$partner_id);
    		if($summe_pool>0){
    		$partner_link_rechnung = "<a href=\"?daten=rechnungen&option=rechnung_an_partner&partner_id=$partner_id\" style='color:green;'>Rechnung erstellen ($summe_pool)</a>";	
    		}else{
    		$partner_link_rechnung ='';	
    		}
    		
			echo "<b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;|-</b> Partner ".$rechnungs_empfaenger_name."&nbsp;$partner_link_rechnung<br>";
			
    	}
    }else{
    	echo "Keine Partner Daten im Pool";
    }
    
    
    $form->ende_formular();
    break;
    
    
    
     case "pool_csv":
     print_req();
     break;	
    
    
    ########################################
    case "rechnung_an_objekt":
   	$form = new mietkonto;
    $form->erstelle_formular("Rechnung an Objekt aus Pool", NULL);
   	$rechnung = new rechnung;
    if(isset($_REQUEST['objekt_id']) && !empty($_REQUEST['objekt_id']) && empty($_REQUEST['aussteller_id'])){
    $kontierung_id_arr = $rechnung->rechnung_an_objekt_zusammenstellen($_REQUEST['objekt_id']);
    #print_r($kontierung_id_arr);
    /*Feldernamen definieren - �berschrift Tabelle*/
	if(is_array($kontierung_id_arr)){
	foreach($kontierung_id_arr[0] as $key => $value){
		$ueberschrift_felder_arr[] = $key;
	}
	#array_als_tabelle_anzeigen($kontierung_id_arr, $ueberschrift_felder_arr);
    }
    /*Rausfinden von wem die Rechnungen ausm Pool geschrieben werden*/
    for($a=0;$a<count($kontierung_id_arr);$a++){
    $beleg_nr = $kontierung_id_arr[$a]['BELEG_NR'];
	$rechnung->rechnung_grunddaten_holen($beleg_nr);	
    /*Empf�nger der Rechnung wird zum Austeller der Auto...Rechnung*/ 
    	
    	if($rechnung->rechnungs_empfaenger_typ != 'Kasse'){
    	#echo "PARTNER $a<br>";
    	$aussteller_arr[] = $rechnung->rechnungs_empfaenger_id ;
    	}else{
    	#echo "KASSE $a<br>";
    	$kassen_arr[] = $rechnung->rechnungs_empfaenger_id ;	
    	}
    }
    		if(isset($aussteller_arr) && is_array($aussteller_arr)){
    		$aussteller_arr = array_unique($aussteller_arr);
				foreach($aussteller_arr as $key=>$value)
    			{
    			$aussteller_arr_sortiert[]= $value;
    			}
    		}
    		
    		if(isset($kassen_arr) && is_array($kassen_arr)){
    		$kassen_arr = array_unique($kassen_arr);
				foreach($kassen_arr as $key=>$value)
    			{
    			$kassen_arr_sortiert[]= $value;
    			}
    		}
    		
   /*Ausgabe der Links mit Rechnungsausteller namen*/
   #print_r($aussteller_arr);
   echo "<table>";
   echo "<tr><td>W�hlen Sie bitte den Rechnungsaussteller aus!</td></tr>";
   if(isset($aussteller_arr_sortiert) && is_array($aussteller_arr_sortiert)){
   #print_r($aussteller_arr_sortiert);
   for($a=0;$a<count($aussteller_arr_sortiert);$a++){ 		
   $partner_info = new partner;
   $partner_info->get_aussteller_info($aussteller_arr_sortiert[$a]);
   $aussteller_id = $aussteller_arr_sortiert[$a];
   $rechnung_von_link = "<a href=\"?daten=rechnungen&option=rechnung_an_objekt&objekt_id=$_REQUEST[objekt_id]&aussteller_typ=$rechnung->rechnungs_empfaenger_typ&aussteller_id=$aussteller_id\">$rechnung->rechnungs_empfaenger_name</a>" ;
      
   echo "<tr><td>$rechnung_von_link</td></tr>";
   }
}
   if(isset($kassen_arr_sortiert) && is_array($kassen_arr_sortiert)){
   for($a=0;$a<count($kassen_arr_sortiert);$a++){ 		
   $kassen_info = new kasse;
   $kassen_info->get_kassen_info($kassen_arr_sortiert[$a]);
   $aussteller_id = $kassen_arr_sortiert[$a];
   $rechnung_von_link = "<a href=\"?daten=rechnungen&option=rechnung_an_objekt&objekt_id=$_REQUEST[objekt_id]&aussteller_typ=Kasse&aussteller_id=$aussteller_id\">".$kassen_info->kassen_name." - ".$kassen_info->kassen_verwalter."</a>" ;
   echo "<tr><td>$rechnung_von_link</td></tr>";
   }
   }
   echo "</table>";
    /*Ende der Ausgabe der Links mit Rechnungsausteller namen*/ 		    
    }//end if
    
    if(isset($_REQUEST['objekt_id']) && !empty($_REQUEST['objekt_id']) && isset($_REQUEST['aussteller_id']) && !empty($_REQUEST['aussteller_id']) && !empty($_REQUEST['aussteller_typ'])){
    $kontierung_id_arr = $rechnung->rechnung_an_objekt_zusammenstellen($_REQUEST['objekt_id']);	
    $kontierung_id_arr_gefiltert = $rechnung->filtern_nach_austeller($kontierung_id_arr, $_REQUEST['aussteller_typ'], $_REQUEST['aussteller_id']);
   		/*echo "<pre>";
		print_r($kontierung_id_arr_gefiltert);
		echo "</pre>";
		*/	
    $rechnung->rechnung_schreiben_positionen_wahl('Objekt', $_REQUEST['objekt_id'], $kontierung_id_arr_gefiltert, $_REQUEST['aussteller_typ'],$_REQUEST['aussteller_id']);		
    }
        
    $form->ende_formular();
    break;
    
        
    ########################################
    case "objektkosten_in_rechnung":
   	$form = new mietkonto;
    $form->erstelle_formular("Objektkosten in Rechnung stellen", NULL);
   	$rechnung = new rechnung;
    if(isset($_REQUEST['objekt_id']) && !empty($_REQUEST['objekt_id'])){
    $kontierung_id_arr = $rechnung->rechnung_aus_pool_zusammenstellen('Objekt', $_REQUEST['objekt_id']);    
    if($kontierung_id_arr == false){
    	echo "Keine Objektkosten";
    }
    /*Feldernamen definieren - �berschrift Tabelle*/
	if(is_array($kontierung_id_arr)){
	foreach($kontierung_id_arr[0] as $key => $value){
		$ueberschrift_felder_arr[] = $key;
	}
	}
	#array_als_tabelle_anzeigen($kontierung_id_arr, $ueberschrift_felder_arr);
    #$rechnung->rechnung_schreiben_positionen_wahl('Objekt', $_REQUEST[objekt_id], $kontierung_id_arr);
    /*Rausfinden von wem die Rechnungen ausm Pool geschrieben werden*/
    for($a=0;$a<count($kontierung_id_arr);$a++){
    $beleg_nr = $kontierung_id_arr[$a][BELEG_NR];
	$rechnung->rechnung_grunddaten_holen($beleg_nr);	
    /*Empf�nger der Rechnung wird zum Austeller der Auto...Rechnung*/ 
    	
    	if($rechnung->rechnungs_empfaenger_typ != 'Kasse'){
    	#echo "PARTNER $a<br>";
    	$aussteller_arr[] = $rechnung->rechnungs_empfaenger_id ;
    	}else{
    	#echo "KASSE $a<br>";
    	$kassen_arr[] = $rechnung->rechnungs_empfaenger_id ;	
    	}
    }
    		if(is_array($aussteller_arr)){
    		$aussteller_arr = array_unique($aussteller_arr);
				foreach($aussteller_arr as $key=>$value)
    			{
    			$aussteller_arr_sortiert[]= $value;
    			}
    		}
    		
    		if(is_array($kassen_arr)){
    		$kassen_arr = array_unique($kassen_arr);
				foreach($kassen_arr as $key=>$value)
    			{
    			$kassen_arr_sortiert[]= $value;
    			}
    		}
    		
   /*Ausgabe der Links mit Rechnungsausteller namen*/
   #print_r($aussteller_arr);
   echo "<table>";
   echo "<tr><td>W�hlen Sie bitte den Rechnungsaussteller aus!</td></tr>";
   if(is_array($aussteller_arr_sortiert)){
   #print_r($aussteller_arr_sortiert);
   for($a=0;$a<count($aussteller_arr_sortiert);$a++){ 		
   $partner_info = new partner;
   $partner_info->get_aussteller_info($aussteller_arr_sortiert[$a]);
   $aussteller_id = $aussteller_arr_sortiert[$a];
   $rechnung_von_link = "<a href=\"?daten=rechnungen&option=objektkosten_in_rechnung&objekt_id=$_REQUEST[objekt_id]&aussteller_typ=$rechnung->rechnungs_empfaenger_typ&aussteller_id=$aussteller_id\">$rechnung->rechnungs_empfaenger_name</a>" ;
      
   echo "<tr><td>$rechnung_von_link</td></tr>";
   }
}
   if(is_array($kassen_arr_sortiert)){
   for($a=0;$a<count($kassen_arr_sortiert);$a++){ 		
   $kassen_info = new kasse;
   $kassen_info->get_kassen_info($kassen_arr_sortiert[$a]);
   $aussteller_id = $kassen_arr_sortiert[$a];
   $rechnung_von_link = "<a href=\"?daten=rechnungen&option=objektkosten_in_rechnung&objekt_id=$_REQUEST[objekt_id]&aussteller_typ=Kasse&aussteller_id=$aussteller_id\">".$kassen_info->kassen_name." - ".$kassen_info->kassen_verwalter."</a>" ;
   echo "<tr><td>$rechnung_von_link</td></tr>";
   }
   }
   echo "</table>";
    /*Ende der Ausgabe der Links mit Rechnungsausteller namen*/ 		    
    }
    if(isset($_REQUEST[objekt_id]) && !empty($_REQUEST[objekt_id]) && isset($_REQUEST[aussteller_id]) && !empty($_REQUEST[aussteller_id]) && !empty($_REQUEST[aussteller_typ])){
    $kontierung_id_arr = $rechnung->rechnung_aus_pool_zusammenstellen('Objekt',$_REQUEST[objekt_id]);	
    $kontierung_id_arr_gefiltert = $rechnung->filtern_nach_austeller($kontierung_id_arr, $_REQUEST[aussteller_typ], $_REQUEST[aussteller_id]);
   		/*echo "<pre>";
		print_r($kontierung_id_arr_gefiltert);
		echo "</pre>";
		*/	
    $rechnung->rechnung_schreiben_positionen_wahl('Objekt', $_REQUEST[objekt_id], $kontierung_id_arr_gefiltert, $_REQUEST[aussteller_typ],$_REQUEST[aussteller_id]);		
    }
    $form->ende_formular();
    break;
    
    
    case "rechnung_an_einheit":
   	$form = new mietkonto;
    $form->erstelle_formular("Rechnung f�r eine Einheit erstellen", NULL);
   	$rechnung = new rechnung;
    if(isset($_REQUEST['einheit_id']) && !empty($_REQUEST['einheit_id'])){
    $kontierung_id_arr = $rechnung->rechnung_aus_pool_zusammenstellen('Einheit', $_REQUEST['einheit_id']);    
    if($kontierung_id_arr == false){
    	echo "Keine einheitsbezogene Kosten";
    }
    /*Feldernamen definieren - �berschrift Tabelle*/
	if(is_array($kontierung_id_arr)){
	foreach($kontierung_id_arr[0] as $key => $value){
		$ueberschrift_felder_arr[] = $key;
	}
	}
	#array_als_tabelle_anzeigen($kontierung_id_arr, $ueberschrift_felder_arr);
    #$rechnung->rechnung_schreiben_positionen_wahl('Objekt', $_REQUEST[objekt_id], $kontierung_id_arr);
    /*Rausfinden von wem die Rechnungen ausm Pool geschrieben werden*/
    for($a=0;$a<count($kontierung_id_arr);$a++){
    $beleg_nr = $kontierung_id_arr[$a]['BELEG_NR'];
	$rechnung->rechnung_grunddaten_holen($beleg_nr);	
    /*Empf�nger der Rechnung wird zum Austeller der Auto...Rechnung*/ 
    	
    	if($rechnung->rechnungs_empfaenger_typ != 'Kasse'){
    	#echo "PARTNER $a<br>";
    	$aussteller_arr[] = $rechnung->rechnungs_empfaenger_id ;
    	}else{
    	#echo "KASSE $a<br>";
    	$kassen_arr[] = $rechnung->rechnungs_empfaenger_id ;	
    	}
    }
    		if(is_array($aussteller_arr)){
    		$aussteller_arr = array_unique($aussteller_arr);
				foreach($aussteller_arr as $key=>$value)
    			{
    			$aussteller_arr_sortiert[]= $value;
    			}
    		}
    		
    		if(isset($kassen_arr) && is_array($kassen_arr)){
    		$kassen_arr = array_unique($kassen_arr);
			#$kassen_arr_sortiert = Array();
    		foreach($kassen_arr as $key=>$value)
    			{
    			$kassen_arr_sortiert[]= $value;
    			}
    		}
    		
   /*Ausgabe der Links mit Rechnungsausteller namen*/
   #print_r($aussteller_arr);
   echo "<table>";
   echo "<tr><td>W�hlen Sie bitte den Rechnungsaussteller aus!</td></tr>";
   if(is_array($aussteller_arr_sortiert)){
   #print_r($aussteller_arr_sortiert);
   for($a=0;$a<count($aussteller_arr_sortiert);$a++){ 		
   $partner_info = new partner;
   $partner_info->get_aussteller_info($aussteller_arr_sortiert[$a]);
   $aussteller_id = $aussteller_arr_sortiert[$a];
   $rechnung_von_link = "<a href=\"?daten=rechnungen&option=rechnung_an_einheit&einheit_id=$_REQUEST[einheit_id]&aussteller_typ=$rechnung->rechnungs_empfaenger_typ&aussteller_id=$aussteller_id\">$rechnung->rechnungs_empfaenger_name</a>" ;
      
   echo "<tr><td>$rechnung_von_link</td></tr>";
   }
}
   if(isset($kassen_arr_sortiert) && is_array($kassen_arr_sortiert)){
   for($a=0;$a<count($kassen_arr_sortiert);$a++){ 		
   $kassen_info = new kasse;
   $kassen_info->get_kassen_info($kassen_arr_sortiert[$a]);
   $aussteller_id = $kassen_arr_sortiert[$a];
   $rechnung_von_link = "<a href=\"?daten=rechnungen&option=rechnung_an_einheit&einheit_id=$_REQUEST[einheit_id]&aussteller_typ=Kasse&aussteller_id=$aussteller_id\">".$kassen_info->kassen_name." - ".$kassen_info->kassen_verwalter."</a>" ;
   echo "<tr><td>$rechnung_von_link</td></tr>";
   }
   }
   echo "</table>";
    /*Ende der Ausgabe der Links mit Rechnungsausteller namen*/ 		    
    }
    if(isset($_REQUEST['einheit_id']) && !empty($_REQUEST['einheit_id']) && isset($_REQUEST['aussteller_id']) && !empty($_REQUEST['aussteller_id']) && !empty($_REQUEST['aussteller_typ'])){
    $kontierung_id_arr = $rechnung->rechnung_aus_pool_zusammenstellen('Einheit',$_REQUEST['einheit_id']);	
    $kontierung_id_arr_gefiltert = $rechnung->filtern_nach_austeller($kontierung_id_arr, $_REQUEST['aussteller_typ'], $_REQUEST['aussteller_id']);
   		/*echo "<pre>";
		print_r($kontierung_id_arr_gefiltert);
		echo "</pre>";
		*/	
    $rechnung->rechnung_schreiben_positionen_wahl('Einheit', $_REQUEST['einheit_id'], $kontierung_id_arr_gefiltert, $_REQUEST['aussteller_typ'],$_REQUEST['aussteller_id']);		
    }
    $form->ende_formular();
    break;
    
    
    
       
    case "rechnung_an_haus":
   	$form = new mietkonto;
    $form->erstelle_formular("Rechnung an Haus aus Pool", NULL);
   	$rechnung = new rechnung;
   	
   	 if(isset($_REQUEST[haus_id]) && !empty($_REQUEST[haus_id])){
    $kontierung_id_arr = $rechnung->rechnung_aus_pool_zusammenstellen('Haus', $_REQUEST[haus_id]);    
    if($kontierung_id_arr == false){
    	echo "Keine hausbezogenen Kosten";
    }
    
    /*Feldernamen definieren - �berschrift Tabelle*/
	if(is_array($kontierung_id_arr)){
	foreach($kontierung_id_arr[0] as $key => $value){
		$ueberschrift_felder_arr[] = $key;
	}
	}
   	
   	
   	/*Rausfinden von wem die Rechnungen ausm Pool geschrieben werden*/
    for($a=0;$a<count($kontierung_id_arr);$a++){
    $beleg_nr = $kontierung_id_arr[$a][BELEG_NR];
	$rechnung->rechnung_grunddaten_holen($beleg_nr);	
    /*Empf�nger der Rechnung wird zum Austeller der Auto...Rechnung*/ 
    	
    	if($rechnung->rechnungs_empfaenger_typ != 'Kasse'){
    	#echo "PARTNER $a<br>";
    	$aussteller_arr[] = $rechnung->rechnungs_empfaenger_id ;
    	}else{
    	#echo "KASSE $a<br>";
    	$kassen_arr[] = $rechnung->rechnungs_empfaenger_id ;	
    	}
    }
    		if(is_array($aussteller_arr)){
    		$aussteller_arr = array_unique($aussteller_arr);
				foreach($aussteller_arr as $key=>$value)
    			{
    			$aussteller_arr_sortiert[]= $value;
    			}
    		}
    		
    		if(is_array($kassen_arr)){
    		$kassen_arr = array_unique($kassen_arr);
				foreach($kassen_arr as $key=>$value)
    			{
    			$kassen_arr_sortiert[]= $value;
    			}
    		}
   	 }		
   	
   	 /*Ausgabe der Links mit Rechnungsausteller namen*/
   #print_r($aussteller_arr);
   echo "<table>";
   echo "<tr><td>W�hlen Sie bitte den Rechnungsaussteller aus!</td></tr>";
   if(is_array($aussteller_arr_sortiert)){
   #print_r($aussteller_arr_sortiert);
   for($a=0;$a<count($aussteller_arr_sortiert);$a++){ 		
   $partner_info = new partner;
   $partner_info->get_aussteller_info($aussteller_arr_sortiert[$a]);
   $aussteller_id = $aussteller_arr_sortiert[$a];
   $rechnung_von_link = "<a href=\"?daten=rechnungen&option=rechnung_an_haus&haus_id=$_REQUEST[haus_id]&aussteller_typ=$rechnung->rechnungs_empfaenger_typ&aussteller_id=$aussteller_id\">$rechnung->rechnungs_empfaenger_name</a>" ;
      
   echo "<tr><td>$rechnung_von_link</td></tr>";
   }
}
   if(is_array($kassen_arr_sortiert)){
   for($a=0;$a<count($kassen_arr_sortiert);$a++){ 		
   $kassen_info = new kasse;
   $kassen_info->get_kassen_info($kassen_arr_sortiert[$a]);
   $aussteller_id = $kassen_arr_sortiert[$a];
   $rechnung_von_link = "<a href=\"?daten=rechnungen&option=rechnung_an_haus&haus_id=$_REQUEST[haus_id]&aussteller_typ=Kasse&aussteller_id=$aussteller_id\">".$kassen_info->kassen_name." - ".$kassen_info->kassen_verwalter."</a>" ;
   echo "<tr><td>$rechnung_von_link</td></tr>";
   }
   }
   echo "</table>";
    /*Ende der Ausgabe der Links mit Rechnungsausteller namen*/ 		    
    	
    	if(isset($_REQUEST[haus_id]) && !empty($_REQUEST[haus_id]) && isset($_REQUEST[aussteller_id]) && !empty($_REQUEST[aussteller_id]) && !empty($_REQUEST[aussteller_typ])){
    	$kontierung_id_arr = $rechnung->rechnung_aus_pool_zusammenstellen('Haus',$_REQUEST[haus_id]);	
    	$kontierung_id_arr_gefiltert = $rechnung->filtern_nach_austeller($kontierung_id_arr, $_REQUEST[aussteller_typ], $_REQUEST[aussteller_id]);
   	 	$rechnung->rechnung_schreiben_positionen_wahl('Haus', $_REQUEST[haus_id], $kontierung_id_arr_gefiltert, $_REQUEST[aussteller_typ],$_REQUEST[aussteller_id]);
    	}	
    
   
    $form->ende_formular();
    break;
    
    
    case "hauskosten_in_rechnung":
   	$form = new mietkonto;
    $form->erstelle_formular("Hauskosten in Rechnung stellen", NULL);
   	$rechnung = new rechnung;
    if(isset($_REQUEST[haus_id]) && !empty($_REQUEST[haus_id])){
    $kontierung_id_arr = $rechnung->rechnung_aus_pool_zusammenstellen('Haus', $_REQUEST[haus_id]);    
    if($kontierung_id_arr == false){
    	echo "Keine Hauskosten";
    }
    /*Feldernamen definieren - �berschrift Tabelle*/
	if(is_array($kontierung_id_arr)){
	foreach($kontierung_id_arr[0] as $key => $value){
		$ueberschrift_felder_arr[] = $key;
	}
	#array_als_tabelle_anzeigen($kontierung_id_arr, $ueberschrift_felder_arr);
    $rechnung->rechnung_schreiben_positionen_wahl('Haus', $_REQUEST[haus_id], $kontierung_id_arr);
    }
    }
    $form->ende_formular();
    break;
    
    
    case "rechnung_an_einheit_ALT":
   	$form = new mietkonto;
    $form->erstelle_formular("Rechnung an Einheit aus Pool", NULL);
   	$rechnung = new rechnung;
    if(isset($_REQUEST['einheit_id']) && !empty($_REQUEST['einheit_id'])){
    $kontierung_id_arr = $rechnung->rechnung_aus_pool_zusammenstellen('Einheit', $_REQUEST[einheit_id]);    
    
    /*Feldernamen definieren - �berschrift Tabelle*/
	if(is_array($kontierung_id_arr)){
	foreach($kontierung_id_arr[0] as $key => $value){
		$ueberschrift_felder_arr[] = $key;
		}
	#array_als_tabelle_anzeigen($kontierung_id_arr, $ueberschrift_felder_arr);
	 $rechnung->rechnung_schreiben_positionen_wahl('Einheit', $_REQUEST[einheit_id], $kontierung_id_arr);
	    }
    }
    $form->ende_formular();
    break;
    
    case "rechnung_an_lager":
   	$form = new mietkonto;
    $form->erstelle_formular("Rechnung an Lager aus Pool", NULL);
   	$rechnung = new rechnung;
    if(isset($_REQUEST['lager_id']) && !empty($_REQUEST['lager_id'])){
    $kontierung_id_arr = $rechnung->rechnung_aus_pool_zusammenstellen('Lager', $_REQUEST['lager_id']);    
    
    /*Feldernamen definieren - �berschrift Tabelle*/
	if(is_array($kontierung_id_arr)){
	foreach($kontierung_id_arr[0] as $key => $value){
		$ueberschrift_felder_arr[] = $key;
		}
	#array_als_tabelle_anzeigen($kontierung_id_arr, $ueberschrift_felder_arr);
	 #$rechnung->rechnung_schreiben_positionen_wahl('Lager', $_REQUEST['lager_id'], $kontierung_id_arr);
	    }
    
    /*Rausfinden von wem die Rechnungen ausm Pool geschrieben werden*/
    for($a=0;$a<count($kontierung_id_arr);$a++){
    $beleg_nr = $kontierung_id_arr[$a]['BELEG_NR'];
	$rechnung->rechnung_grunddaten_holen($beleg_nr);	
    /*Empf�nger der Rechnung wird zum Austeller der Auto...Rechnung*/ 
    	
    	if($rechnung->rechnungs_empfaenger_typ != 'Kasse'){
    	#echo "PARTNER $a<br>";
    	$aussteller_arr[] = $rechnung->rechnungs_empfaenger_id ;
    	}else{
    	#echo "KASSE $a<br>";
    	$kassen_arr[] = $rechnung->rechnungs_empfaenger_id ;	
    	}
    }
    		if(is_array($aussteller_arr)){
    		$aussteller_arr = array_unique($aussteller_arr);
				foreach($aussteller_arr as $key=>$value)
    			{
    			$aussteller_arr_sortiert[]= $value;
    			}
    		}
    		
    		if(is_array($kassen_arr)){
    		$kassen_arr = array_unique($kassen_arr);
				foreach($kassen_arr as $key=>$value)
    			{
    			$kassen_arr_sortiert[]= $value;
    			}
    		}
    		
   /*Ausgabe der Links mit Rechnungsausteller namen*/
   echo "<table>";
   echo "<tr><td>W�hlen Sie bitte den Rechnungsaussteller aus!</td></tr>";
   #print_r($aussteller_arr);
   if(is_array($aussteller_arr_sortiert)){
   print_r($aussteller_arr_sortiert);
   for($a=0;$a<count($aussteller_arr_sortiert);$a++){ 		
   $partner_info = new partner;
   $partner_info->get_aussteller_info($aussteller_arr_sortiert[$a]);
   $aussteller_id = $aussteller_arr_sortiert[$a];
   
   
   $rechnung_von_link = "<a href=\"?daten=rechnungen&option=rechnung_an_lager&lager_id=$_REQUEST[lager_id]&aussteller_typ=$rechnung->rechnungs_empfaenger_typ&aussteller_id=$aussteller_id\">".$rechnung->rechnungs_empfaenger_name."</a>" ;
   echo "<tr><td>$rechnung_von_link</td></tr>";
   }
}
   if(is_array($kassen_arr_sortiert)){
   for($a=0;$a<count($kassen_arr_sortiert);$a++){ 		
   $kassen_info = new kasse;
   $kassen_info->get_kassen_info($kassen_arr_sortiert[$a]);
   $aussteller_id = $kassen_arr_sortiert[$a];
   $rechnung_von_link = "<a href=\"?daten=rechnungen&option=rechnung_an_lager&lager_id=$_REQUEST[lager_id]&aussteller_typ=Kasse&aussteller_id=$aussteller_id\">".$kassen_info->kassen_name." - ".$kassen_info->kassen_verwalter."</a>" ;
   echo "<tr><td>$rechnung_von_link</td></tr>";
   }
   }
   echo "</table>";
    /*Ende der Ausgabe der Links mit Rechnungsausteller namen*/ 		    
    }//end if
    
    if(isset($_REQUEST['lager_id']) && !empty($_REQUEST['lager_id']) && isset($_REQUEST[aussteller_id]) && !empty($_REQUEST[aussteller_id]) && !empty($_REQUEST[aussteller_typ])){
   $kontierung_id_arr = $rechnung->rechnung_aus_pool_zusammenstellen('Lager', $_REQUEST['lager_id']);    
   $kontierung_id_arr_gefiltert = $rechnung->filtern_nach_austeller($kontierung_id_arr, $_REQUEST[aussteller_typ], $_REQUEST[aussteller_id]);
   		/*if(isset($_REQUEST['csv'])){
   		echo "<pre>";
		print_r($kontierung_id_arr_gefiltert);
		echo "</pre>";
		DIE('SIVAC');	
   		}else{*/
    $rechnung->rechnung_schreiben_positionen_wahl('Lager', $_REQUEST['lager_id'], $kontierung_id_arr_gefiltert, $_REQUEST[aussteller_typ],$_REQUEST[aussteller_id]);
   		#}		
    }
    
    
    $form->ende_formular();
    break;
    
    
    
        case "rechnung_an_partner":
   	$form = new mietkonto;
    $form->erstelle_formular("Rechnung an Partner aus Pool", NULL);
   	$rechnung = new rechnung;
    if(isset($_REQUEST[partner_id]) && !empty($_REQUEST[partner_id])){
    $kontierung_id_arr = $rechnung->rechnung_aus_pool_zusammenstellen('Partner', $_REQUEST[partner_id]);    
    
    /*Feldernamen definieren - �berschrift Tabelle*/
	if(is_array($kontierung_id_arr)){
	foreach($kontierung_id_arr[0] as $key => $value){
		$ueberschrift_felder_arr[] = $key;
		}
	#array_als_tabelle_anzeigen($kontierung_id_arr, $ueberschrift_felder_arr);
	 #$rechnung->rechnung_schreiben_positionen_wahl('Lager', $_REQUEST['lager_id'], $kontierung_id_arr);
	    }
    
    /*Rausfinden von wem die Rechnungen ausm Pool geschrieben werden*/
    for($a=0;$a<count($kontierung_id_arr);$a++){
    $beleg_nr = $kontierung_id_arr[$a][BELEG_NR];
	$rechnung->rechnung_grunddaten_holen($beleg_nr);	
    /*Empf�nger der Rechnung wird zum Austeller der Auto...Rechnung*/ 
    	
    	if($rechnung->rechnungs_empfaenger_typ != 'Kasse'){
    	#echo "PARTNER $a<br>";
    	$aussteller_arr[] = $rechnung->rechnungs_empfaenger_id ;
    	}else{
    	#echo "KASSE $a<br>";
    	$kassen_arr[] = $rechnung->rechnungs_empfaenger_id ;	
    	}
    }
    		if(is_array($aussteller_arr)){
    		$aussteller_arr = array_unique($aussteller_arr);
				foreach($aussteller_arr as $key=>$value)
    			{
    			$aussteller_arr_sortiert[]= $value;
    			}
    		}
    		
    		if(is_array($kassen_arr)){
    		$kassen_arr = array_unique($kassen_arr);
				foreach($kassen_arr as $key=>$value)
    			{
    			$kassen_arr_sortiert[]= $value;
    			}
    		}
    		
   /*Ausgabe der Links mit Rechnungsausteller namen*/
   echo "<table>";
   echo "<tr><td>W�hlen Sie bitte den Rechnungsaussteller aus!</td></tr>";
   #print_r($aussteller_arr);
   if(is_array($aussteller_arr_sortiert)){
   #print_r($aussteller_arr_sortiert);
   for($a=0;$a<count($aussteller_arr_sortiert);$a++){ 		
   $partner_info = new partner;
   $partner_info->get_aussteller_info($aussteller_arr_sortiert[$a]);
   $aussteller_id = $aussteller_arr_sortiert[$a];
   
   
   $rechnung_von_link = "<a href=\"?daten=rechnungen&option=rechnung_an_partner&partner_id=$_REQUEST[partner_id]&aussteller_typ=$rechnung->rechnungs_empfaenger_typ&aussteller_id=$aussteller_id\">".$rechnung->rechnungs_empfaenger_name."</a>" ;
   echo "<tr><td>$rechnung_von_link</td></tr>";
   }
}
   if(is_array($kassen_arr_sortiert)){
   for($a=0;$a<count($kassen_arr_sortiert);$a++){ 		
   $kassen_info = new kasse;
   $kassen_info->get_kassen_info($kassen_arr_sortiert[$a]);
   $aussteller_id = $kassen_arr_sortiert[$a];
   $rechnung_von_link = "<a href=\"?daten=rechnungen&option=rechnung_an_partner&partner_id=$_REQUEST[partner_id]&aussteller_typ=Kasse&aussteller_id=$aussteller_id\">".$kassen_info->kassen_name." - ".$kassen_info->kassen_verwalter."</a>" ;
   echo "<tr><td>$rechnung_von_link</td></tr>";
   }
   }
   echo "</table>";
    /*Ende der Ausgabe der Links mit Rechnungsausteller namen*/ 		    
    }//end if
    
    if(isset($_REQUEST[partner_id]) && !empty($_REQUEST[partner_id]) && isset($_REQUEST[aussteller_id]) && !empty($_REQUEST[aussteller_id]) && !empty($_REQUEST[aussteller_typ])){
   $kontierung_id_arr = $rechnung->rechnung_aus_pool_zusammenstellen('Partner', $_REQUEST[partner_id]);    
   $kontierung_id_arr_gefiltert = $rechnung->filtern_nach_austeller($kontierung_id_arr, $_REQUEST[aussteller_typ], $_REQUEST[aussteller_id]);
   		#echo "<pre>";
		#print_r($kontierung_id_arr_gefiltert);
		#echo "</pre>";
			
   #die(); 
   $rechnung->rechnung_schreiben_positionen_wahl('Partner', $_REQUEST[partner_id], $kontierung_id_arr_gefiltert, $_REQUEST[aussteller_typ],$_REQUEST[aussteller_id]);		
    }
    
    
    $form->ende_formular();
    break;
    
    
    /*UPDATE KONTIERUNGSPOS VON RECH_POS SKONTO*/
    /*UPDATE `KONTIERUNG_POSITIONEN` AS t1 LEFT JOIN `RECHNUNGEN_POSITIONEN` AS t2 ON( t1.`BELEG_NR` = t2.`BELEG_NR` && t1.`POSITION` = t2.`POSITION`) SET t1.`SKONTO`=t2.`SKONTO` 
     * */
     /*UPDATE KONTIERUNG_POSITIONEN SET GESAMT_SUMME = (MENGE*EINZEL_PREIS/100)*(100-RABATT_SATZ)
      * 
      */
    
    
    
    case "rechnungs_uebersicht":
   	$form = new mietkonto;
    $form->erstelle_formular("Rechnungs�bersicht", NULL);
   	$rechnung = new rechnung;
        
    if(isset($_REQUEST['belegnr']) && !empty($_REQUEST['belegnr'])){
    $rechnung->rechnung_grunddaten_holen($_REQUEST['belegnr']);
   	if(file_exists("print_css/$rechnung->rechnungs_aussteller_typ/$rechnung->rechnungs_aussteller_id.css")){
   	echo "<header><link rel=\"stylesheet\" type=\"text/css\"  href=\"print_css/$rechnung->rechnungs_aussteller_typ/$rechnung->rechnungs_aussteller_id.css\" media=\"print\"></header>";	
   	}else{
   	echo "<header><link rel=\"stylesheet\" type=\"text/css\"  href=\"css/print_rechnungen.css\" media=\"print\"></header>";	
   	}
   /*	echo "<pre>";
   	print_r($rechnung);
   	echo "</pre>";
    */
    $rechnung->rechnung_inkl_positionen_anzeigen($_REQUEST['belegnr']);
    }
    $form->ende_formular();
    break;
    
    
    case "partner_erfassen":
   	$partner_form = new partner;
    $partner_form->partner_rechts_anzeigen();
    $partner_form->form_partner_erfassen();
    break;
    
    case "partner_gesendet":
   	$partner_form = new partner;
   	$partner_form->partner_rechts_anzeigen();
   	$form = new mietkonto;
   	$form->erstelle_formular("Partnerdaten �berpr�fen", NULL);
   	echo "<p><b>�bermittelte Partnerdaten:</b></p>";
   	#$form->array_anzeigen($_POST);
   	$clean_arr = post_array_bereinigen();
    #$form->array_anzeigen($clean_arr);
   	foreach ($clean_arr as $key => $value) {
    	if(empty($value)){
    	echo "<b>$key wurde nicht eingegeben</b>";
    	$fehler = true;
    	}
    	if(($key != 'submit_partner') AND ($key != 'option')){
    	echo "" . $value . "<br>";
  		$form->hidden_feld($key, $value);
  		}
   	}
   		if(!$fehler){
   		$form->hidden_feld("option", "partner_gesendet1");
   		$form->send_button("submit_partner1", "Speichern");
   		}else{
   			backlink();	
   	}
   	$form->ende_formular();
   	break;
   	
   	case "partner_gesendet1":
   	$clean_arr = post_array_bereinigen();
   	$form = new mietkonto;
   	$form->erstelle_formular("Partnerdaten speichern", NULL);
   	#$form->array_anzeigen($clean_arr);
   	$partner = new partner;
   	$partner->partner_speichern($clean_arr);
   	$form->ende_formular();
   	break;


	case "send_positionen":
   	$clean_arr = post_array_bereinigen();
   	$rechnung = new rechnung;
   	
   	$form = new mietkonto;
   	$form->erstelle_formular("Rechnung vervollst�ndigen", NULL);
   	if(isset($_REQUEST[belegnr]) && !empty($_REQUEST[belegnr])){
    #$rechnung->rechnung_grunddaten_holen($_REQUEST[rechnung_id]);
  	$rechnung->rechnungs_kopf($_REQUEST[belegnr]);
  	#$rechnung->rechnungsdaten_anzeigen($_REQUEST[belegnr]);
  	
  	/*Block mit Artikeln und Leistungen des Rechnungsaustellers*/
  	$rechnung->artikel_leistungen_block($rechnung->rechnungs_aussteller_id);
  	
  	$form->erstelle_formular("Positionen eingeben", NULL);
  	echo "<table>";
  	for($a=1;$a<=$clean_arr[anzahl_positionen];$a++){
 	echo "<tr>";
 	echo "<td>";
 	$form->text_feld_inaktiv("Position $a", "positionen[$a][position]", "$a", "1");
 	echo "</td><td>";
 	$form->text_feld("Artikel/Leistung", "positionen[$a][artikel_nr]", "", "15");
 	echo "</td><td>";
 	$form->text_feld("Bezeichnung", "positionen[$a][bezeichnung]", "", "50");
 	echo "</td><td>";
 	$form->text_feld("Listenpreis", "positionen[$a][preis]", "", "10"); 
 	echo "</td><td>";
 	$form->text_feld("Rabatt %", "positionen[$a][rabatt_satz]", "", "10");  		
 		 		
 	#echo "<label name=\"'inaktiv.'positionen[$a][artikel_nr]\">ss</label>
 	echo "</td><td>";
 	
 	$form->text_feld("Menge:", "positionen[$a][menge]", "", "3");
  	echo "</td></tr>";
  	}
  	echo "<tr><td colspan=3>";
  	$form->hidden_feld("option", "send_positionen2");
  	$form->hidden_feld("belegnr", "".$rechnung->belegnr."");
  	$form->hidden_feld("rechnungsnummer", "".$rechnung->rechnungsnummer."");
  	$form->hidden_feld("partner_id", "".$rechnung->rechnungs_aussteller_id."");
  	$form->send_button("senden_art_pos", "Weiter");
  echo "<td></tr>";
  echo "</table>";
  #echo "<pre>";
  
  #print_r($_POST);
  #echo "</pre>";
    /*Anzeigen von Netto/Brutto werten der aktuellen Rechnung*/
    $rechnung->rechnung_footer_tabelle_anzeigen();
    $form->ende_formular();
    }else{
    fehlermeldung_ausgeben("Bitte Rechnung ausw�hlen!");
    weiterleiten_in_sec("?daten=rechnungen&option=erfasste_rechnungen", 2);
    }
   	break;
   	
   	case "send_positionen2":
   	
   	$clean_arr = post_array_bereinigen();
   	#$clean_positionen_arr = post_unterarray_bereinigen('positionen');
   	$rechnung = new rechnung;
   	$form = new mietkonto;
   	$form->erstelle_formular("Rechnung vervollst�ndigen", NULL);
   	if(isset($_REQUEST[belegnr]) && !empty($_REQUEST[belegnr])){
    $rechnung->rechnungs_kopf($_REQUEST[belegnr]);
    #$rechnung->rechnungsdaten_anzeigen($_REQUEST[belegnr]);
  	#print_r($clean_positionen_arr);
  	
  	/*Pr�fen ob Bezeichnung, Preis, Menge eingetragen worden sind*/
  	 for($b=1;$b<=count($_POST[positionen]);$b++){
    	foreach ($_POST[positionen][$b] as $key1 => $value1) {
    	/*if($key1=='bezeichnung' && empty($value1)){
  		backlink();
  		die("<b>Position $b. Die Bezeichnung der Position muss eingetragen werden!</b>\n");
  	  	}*/
  	if($key1=='menge' && empty($value1)){
  		backlink();
  		die("<b>Position $b. Die Mengenangabe fehlt</b>\n");
  	  	}
  	else{
  	$fehler = false;
  	}
  }
}
  	
  	
  	
  	/*Block mit Artikeln und Leistungen des Rechnungsaustellers*/
  	$rechnung->artikel_leistungen_block($rechnung->rechnungs_aussteller_id);
  	if(!$fehler){
  	$form->erstelle_formular("Zusammenfassung", NULL);
  	#print_r($rechnung);
  	echo "<table>";
  	echo "<tr><td colspan=8>";
			$geld_konto_info = new geldkonto_info;
			
			if($rechnung->rechnungs_empfaenger_typ != 'Kasse'){
			echo "<b>Diese Rechnung wird/wurde �berwiesen an $rechnung->rechnungs_aussteller_name .</b>";
			$geld_konto_info->dropdown_geldkonten($rechnung->rechnungs_aussteller_typ, $rechnung->rechnungs_aussteller_id);
			}else{
				echo "<b>Diese Rechnung wird/wurde in BAR an $rechnung->rechnungs_aussteller_name gezaht.</b>";
			}
			echo "</td></tr>";
  	echo "<tr class=felder_namen>";
  	echo "<td>Pos</td><td>Artikel</td><td>Bezeichnung</td><td>EPreis</td><td>Menge</td><td><input type=\"button\" onclick=\"wert_uebertragen(this.form.mwst_feld)\" value=\"Alle\">
</td><td><input type=\"button\" onclick=\"wert_uebertragen(this.form.rabatt_feld)\" value=\"Alle\"></td><td></td></tr>";
  			
  	
  	for($a=1;$a<=count($_POST[positionen]);$a++){
 	echo "<tr>";
 	echo "<td>";
 	$form->text_feld("Pos.", "positionen[$a]", "$a", "1");
 	echo "</td><td>";
 	/*Artikelinfos als Array verf�gbar machen*/
 	$artikel_info_arr = $rechnung->artikel_info($_POST[partner_id], "".$_POST[positionen][$a]['artikel_nr']."");
 	
 	
 	/*Pr�fen ob Artikelinfos als Array verf�gbar sind*/
 	if(is_array($artikel_info_arr)){
 	$bezeichnung = $artikel_info_arr[0]['BEZEICHNUNG'];
 	$listenpreis = $artikel_info_arr[0]['LISTENPREIS'];
 	$rabatt_satz = $artikel_info_arr[0]['RABATT_SATZ'];
 	#$gpreis = $_POST[positionen][$a]['menge'] * $_POST[positionen][$a]['preis'];
 	$gpreis = (($_POST[positionen][$a]['menge'] * $listenpreis) / 100) * (100-$rabatt_satz);
 	$artikel_nr = $artikel_info_arr[0]['ARTIKEL_NR'];
 	}else{
 	
 	/*Artikel nicht in db vorhanden z.B. neues Artikel / Leistung*/
 	if(!empty($_POST[positionen][$a]['bezeichnung'])){
 		if(!empty($_POST[positionen][$a]['artikel_nr'])){
 		$listenpreis_neuer_artikel = nummer_komma2punkt($_POST[positionen][$a]['preis']);
 		$art_nr = $rechnung->artikel_leistung_mit_artikelnr_speichern($_POST[partner_id], $_POST[positionen][$a]['bezeichnung'], $listenpreis_neuer_artikel, $_POST[positionen][$a]['artikel_nr'], $_POST[positionen][$a]['rabatt_satz']);
 		}else{
 		$listenpreis_neuer_artikel = nummer_komma2punkt($_POST[positionen][$a]['preis']);
 		$art_nr = $rechnung->artikel_leistung_speichern($_POST[partner_id], $_POST[positionen][$a]['bezeichnung'], $listenpreis_neuer_artikel, $_POST[positionen][$a]['rabatt_satz']);
 		}
 	}
 	/*Artikelinfos als Array verf�gbar machen*/
 	$artikel_info_arr = $rechnung->artikel_info($_POST[partner_id], $art_nr);
 	$bezeichnung = $artikel_info_arr[0]['BEZEICHNUNG'];
 	$listenpreis = $artikel_info_arr[0]['LISTENPREIS'];
 	$rabatt_satz = $artikel_info_arr[0]['RABATT_SATZ'];
 	$artikel_nr = $artikel_info_arr[0]['ARTIKEL_NR'];
 	$gpreis = ($_POST[positionen][$a]['menge'] * $listenpreis) / (100-$rabatt_satz);
 	}
 	
 	
 	$form->text_feld("Artikel/Leistung", "positionen[$a][artikel_nr]", "$artikel_nr", "15"); 		
 		echo "</td><td>";
 	$form->text_feld("Bezeichnung:", "positionen[$a][bezeichnung]", "$bezeichnung", "40");
 	
 		echo "</td><td>";
		$listenpreis = nummer_punkt2komma($listenpreis);
		$form->text_feld_id("Epreis:", "positionen[$a][preis]", "$listenpreis", "5", "epreis_feld");
		echo "</td><td>";
 	
 	 	
 	 	 	 	
 	 	$form->text_feld_id("Menge:", "positionen[$a][menge]", "".$_POST[positionen][$a]['menge']."", "2", "mengen_feld");
 	 	
 	 	echo "</td><td>";
 	$form->text_feld_id("MWST %:", "positionen[$a][pos_mwst_satz]", "19", "5", "mwst_feld");
  	echo "</td><td>";
  	$form->text_feld_id("Rabatt %:", "positionen[$a][pos_rabatt]", "$rabatt_satz", "5", "rabatt_feld");
  	echo "</td><td>";
  	$gpreis = nummer_punkt2komma($gpreis); 	 	 	
 	$form->text_feld_id("Netto:", "positionen[$a][gpreis]", "$gpreis", "8", "netto_feld");
  	echo "</td></tr>";
  	
  	}//ende for
  	echo "<tr><td colspan=7 align=right>";
  	  	
  	echo "</td></tr>";
  	echo "<tr><td colspan=8><hr></td></tr>";
  	echo "<tr><td colspan=7 align=right>Netto errechnet</td><td id=\"g_netto_errechnet\"></td></tr>";
	echo "<tr><td colspan=7 align=right>Durchschnittsrabatt</td><td id=\"durchschnitt_rabatt\"></td></tr>";
      	
  	echo "<tr><td colspan=3>";
  $form->hidden_feld("option", "send_positionen3");
  $form->send_button_disabled("senden_pos", "Speichern deaktiviert", "speichern_button1");
  $form->hidden_feld("belegnr", "".$rechnung->belegnr."");
  $form->hidden_feld("rechnungsnummer", "".$rechnung->rechnungsnummer."");
  	} //end if !fehler
  echo "<td></tr>";
  echo "</table>";
  
    
    /*Anzeigen von Netto/Brutto werten der aktuellen Rechnung*/
    $rechnung->rechnung_footer_tabelle_anzeigen();
    
    $form->ende_formular();
    }else{
    fehlermeldung_ausgeben("Bitte Rechnung ausw�hlen!");
    weiterleiten_in_sec("?daten=rechnungen&option=erfasste_rechnungen", 2);
    }
   	/*Block mit Artikeln und Leistungen des Rechnungsaustellers*/
   	$rechnung->artikel_leistungen_block($rechnung->rechnungs_aussteller_id);
   	break;
    
    case "send_positionen3":
    $form = new mietkonto;
   	$form->erstelle_formular("Rechnungspositionen speichern", NULL);
    $clean_arr = post_array_bereinigen();
    $rechnung = new rechnung;
    $rechnung->positionen_speichern($clean_arr[belegnr]);
    echo "<pre>";
    #print_r($clean_arr);
    #print_r($_POST[positionen]);
    echo "</pre>";
    $form->ende_formular();
    break;
    
    case "rechnung_suchen":
   	$clean_arr = post_array_bereinigen();
   	$form = new mietkonto;
   	$form->erstelle_formular("Rechnung suchen", NULL);
   	$form->array_anzeigen($clean_arr);
   	$rechnung = new rechnung;
   	$rechnung->suche_rechnung_form();
   	$form->ende_formular();
   	break;
    
    case "rechnung_suchen1":
   	$rechnung = new rechnung;
   	$rechnung->suche_rechnung_form();
   	$clean_arr = post_array_bereinigen();
   	$form = new mietkonto;
   	$form->erstelle_formular("Ergebnis", NULL);
   	#$form->array_anzeigen($clean_arr);
   	$suchart = $clean_arr['suchart'];
   	if($suchart == 'beleg_nr'){
   		#echo "suche nach belegnr $clean_arr[beleg_nr_txt]";
   	$ergebnis = $rechnung->rechnung_finden_nach_beleg($clean_arr[beleg_nr_txt]);
   		if(count($ergebnis)>0){
   		$rechnung->rechnungen_aus_arr_anzeigen($ergebnis);
   		}else{
   			echo "Keine Rechnung mit dieser Belegnummer ($clean_arr[beleg_nr_txt])";
   		}
   	}
   	if($suchart == 'lieferschein'){
   		#echo "suche nach belegnr $clean_arr[beleg_nr_txt]";
   	$ergebnis = $rechnung->rechnung_finden_nach_lieferschein($clean_arr[lieferschein_nr_txt]);
   		if(is_array($ergebnis)){
   		$anzahl_rechnungen = count($ergebnis);
   			for($a=0;$a<$anzahl_rechnungen;$a++){
   			$beleg_nr = $ergebnis[$a]['DETAIL_ZUORDNUNG_ID'];
   			$r = new rechnungen;
   			$r-> rechnung_grunddaten_holen($beleg_nr);
   			$link_rechnung = "<a href=\"?daten=rechnungen&option=rechnungs_uebersicht&belegnr=$beleg_nr\">$r->rechnungsdatum $r->rechnungs_aussteller_name Rechnungsnr: $r->rechnungsnummer WE: $r->empfaenger_eingangs_rnr WA: $r->aussteller_ausgangs_rnr</a>";
   			echo "$link_rechnung<br>";
   			}
   		
   		}else{
   			echo "Keine Rechnung mit dieser Lieferscheinnummer ($clean_arr[lieferschein_nr_txt])";
   		}
   	}
   	
   	if($suchart == 'rechnungsnr'){
   		$ergebnis = $rechnung->rechnung_finden_nach_rnr($clean_arr['rechnungsnr_txt']);
   		if(count($ergebnis)>0){
   		$rechnung->rechnungen_aus_arr_anzeigen($ergebnis);
   		}else{
   		echo "Keine Rechnung mit der Rechnungsnummer $clean_arr[rechnungsnr_txt] gefunden!";
   		}
   		
   	}
   	if($suchart == 'aussteller'){
   	#	echo "suche nach aussteller $clean_arr[Aussteller]";
   	$ergebnis = $rechnung->rechnung_finden_nach_aussteller($clean_arr[aussteller]);
   		if(count($ergebnis)>0){
   		$rechnung->rechnungen_aus_arr_anzeigen($ergebnis);
   		}else{
   			echo "Keine Rechnung von Austeller ($clean_arr[aussteller])";
   		}
   	}
   	if($suchart == 'empfaenger'){
   		#echo "suche nach empfaenger $clean_arr[empfaenger]";
   	$ergebnis = $rechnung->rechnung_finden_nach_empfaenger('Partner', $clean_arr[empfaenger]);
   		if(count($ergebnis)>0){
   		$rechnung->rechnungen_aus_arr_anzeigen($ergebnis);
   		}else{
   			echo "Keine Rechnungen f�r den Empf�nger ($clean_arr[empfaenger])";
   		}
   	
   	}
   	if($suchart == 'partner_paar'){
   		#echo "suche nach partner_paar $clean_arr[partner_paar1] $clean_arr[partner_paar2]";
   	$ergebnis = $rechnung->rechnung_finden_nach_paar($clean_arr[partner_paar1],$clean_arr[partner_paar2]);
   		if(count($ergebnis)>0){
   		$rechnung->rechnungen_aus_arr_anzeigen($ergebnis);
   		}else{
   			echo "Keine Rechnungen f�r das Partnerpaar";
   		}
   	}
   	$form->ende_formular();
   	break;
    
    case "rechnung_kontieren":
   	#echo "<header><link rel=\"stylesheet\" type=\"text/css\"  href=\"css/print_rechnungen.css\" media=\"print\"></header>";
   	$form = new mietkonto;
    $form->erstelle_formular("Rechnungs�bersicht", NULL);
   	if(!isset($_POST['positionen_list'])){
   	   	$rechnung = new rechnung;
        if(isset($_REQUEST['belegnr']) && !empty($_REQUEST['belegnr'])){
        $rechnung->rechnung_zum_kontieren_anzeigen($_REQUEST['belegnr']);
    }    	
    }else{
    #print_r($_POST);	
	$rechnung = new rechnung;
	$rechnung->kontierungstabelle_anzeigen($_REQUEST['belegnr'], $_POST['positionen_list'], $_POST['kosten_traeger_typ']);
    }
    $form->ende_formular();
    break;
    
    
    case "rechnung_kontierung_aufheben":
    if(isset($_REQUEST['belegnr']) && !empty($_REQUEST['belegnr'])){
        $r = new rechnung;
    	if($r->rechnung_kontierung_aufheben($_REQUEST['belegnr'])==true){
    		$belegnr = $_REQUEST['belegnr'];
    		weiterleiten("?daten=rechnungen&option=rechnung_kontieren&belegnr=$belegnr");
    	}
    }else{
    	fehlermeldung_ausgeben("Rechnunt w�hlen x777");
    }    	
    break;
    
    
    
    
    case "KONTIERUNG_SENDEN":
    $rechnung = new rechnung;
    $error = $rechnung->kontierung_pruefen();
    if($error){
    	fehlermeldung_ausgeben("KONTIERUNGSSUMME > URSPRUNGSSUMME");
    }else{
    $rechnung->kontierung_speichern();
    }
    /*echo "<pre>";
    print_r($_POST);
    echo "</pre>";
    */
    break;
    
    case "AUTO_RECHNUNG_VORSCHAU_ALT":
    #ob_clean();
    # phpinfo();
    $form = new mietkonto;
    $form->erstelle_formular("Rechnungsvorschau", NULL);
   echo "<pre>";
   print_r($_POST);
   echo "</pre>";
   
    if(!empty($_POST)){
    for($a=0;$a<count($_POST[uebernehmen]);$a++){
    	
    	$zeile_uebernehmen = $_POST[uebernehmen][$a];
    	$menge= $_POST[positionen][$zeile_uebernehmen][menge];
    	$preis =$_POST[positionen][$zeile_uebernehmen][preis];	
    	#echo "$zeile_uebernehmen $menge $preis<br>";
    	$uebernahme_arr[positionen][] = $_POST[positionen][$zeile_uebernehmen];
    	}
    	$uebernahme_arr[RECHNUNG_KOSTENTRAEGER_TYP] = $_POST[RECHNUNG_KOSTENTRAEGER_TYP];
    	$uebernahme_arr[RECHNUNG_KOSTENTRAEGER_ID] = $_POST[RECHNUNG_KOSTENTRAEGER_ID];
    	$uebernahme_arr[RECHNUNG_AUSSTELLER_TYP] = $_POST[RECHNUNG_AUSSTELLER_TYP];
    	$uebernahme_arr[RECHNUNG_AUSSTELLER_ID] = $_POST[RECHNUNG_AUSSTELLER_ID];
		$uebernahme_arr[RECHNUNG_EMPFAENGER_TYP] = $_POST[RECHNUNG_EMPFAENGER_TYP];
    	$uebernahme_arr[RECHNUNG_EMPFAENGER_ID] = $_POST[RECHNUNG_EMPFAENGER_ID];
    	$uebernahme_arr[RECHNUNG_SKONTO] = $_POST[skonto];
    	$uebernahme_arr[RECHNUNG_FAELLIG_AM] = $_POST[faellig_am];
     	$uebernahme_arr[RECHNUNG_NETTO_BETRAG]= $_POST[RECHNUNG_NETTO_BETRAG];
   		$uebernahme_arr[RECHNUNG_BRUTTO_BETRAG]= $_POST[RECHNUNG_BRUTTO_BETRAG];
   		$uebernahme_arr[RECHNUNG_SKONTO_BETRAG]= $_POST[RECHNUNG_SKONTO_BETRAG];
   		$uebernahme_arr[EMPFANGS_GELD_KONTO]= $_POST[geld_konto];
   		$uebernahme_arr[RECHNUNGSDATUM]= $_POST[rechnungsdatum];
   		
     	
     	 $objekt_info = new objekt;
		 $objekt_info->get_objekt_name($_POST[RECHNUNG_KOSTENTRAEGER_ID]);
		 $objekt_info->get_objekt_eigentuemer_partner($_POST[RECHNUNG_KOSTENTRAEGER_ID]);
		 $partner_info = new partner;
		 $rechnung_an =$partner_info->get_partner_name($objekt_info->objekt_eigentuemer_partner_id);
		 if($uebernahme_arr[RECHNUNG_AUSSTELLER_TYP] == 'Partner'){
		 $rechnung_von =$partner_info->get_partner_name($uebernahme_arr[RECHNUNG_AUSSTELLER_ID]);
		 }
		 if($uebernahme_arr[RECHNUNG_AUSSTELLER_TYP] == 'Kasse'){
		 $kassen_info = new kasse;
		 $kassen_info->get_kassen_info($uebernahme_arr[RECHNUNG_AUSSTELLER_ID]);
		 $rechnung_von = $kassen_info->kassen_name;
		 }
   echo "<table><tr><td>Rechnung von <b>$rechnung_von</b> an $rechnung_an<br> f�r das Objekt ".$objekt_info->objekt_name."</td></tr></table>";
   /*echo "<pre>";
   print_r($uebernahme_arr);
   echo "</pre>"; 
   */
   #$rechnungsdatum = date("Y-m-d");
   #$rechnungsdatum = date_mysql2german($rechnungsdatum);
   $clean_arr[rechnungsdatum] = $uebernahme_arr[RECHNUNGSDATUM];
   $clean_arr[rechnungsnummer] = '';
   $clean_arr[Aussteller_typ]= $uebernahme_arr[RECHNUNG_AUSSTELLER_TYP];
   $clean_arr[Aussteller_id]= $uebernahme_arr[RECHNUNG_AUSSTELLER_ID];
   $clean_arr[Empfaenger_typ]= $uebernahme_arr[RECHNUNG_EMPFAENGER_TYP];
   $clean_arr[Empfaenger_id]= $uebernahme_arr[RECHNUNG_EMPFAENGER_ID];
   $clean_arr[eingangsdatum]= $rechnungsdatum; 
   #$faellig_am = date("Y-m-t");
   $faellig_am = $uebernahme_arr[RECHNUNG_FAELLIG_AM];
   $clean_arr[faellig_am]= date_mysql2german($faellig_am);
   $kurzbeschreibung = $_POST[kurzbeschreibung];
   
   
   
   
   if($clean_arr[Empfaenger_typ] == 'Objekt'){
   $clean_arr[kurzbeschreibung]= "Rechnung f�r $objekt_info->objekt_name<br>$kurzbeschreibung";
   }
   if($clean_arr[Empfaenger_typ] == 'Haus'){
   $clean_arr[kurzbeschreibung]= "Rechnung f�r Haus im $objekt_info->objekt_name<br>$kurzbeschreibung";
   }
   if($clean_arr[Empfaenger_typ] == 'Einheit'){
   
   $r = new rechnung;
   $einheit = $r->kostentraeger_ermitteln('Einheit', $uebernahme_arr[RECHNUNG_KOSTENTRAEGER_ID]);
   $clean_arr[kurzbeschreibung]= "Rechnung f�r Einheit $einheit<br>$kurzbeschreibung";
   }
   if($clean_arr[Empfaenger_typ] == 'Lager'){
   $lager_info = new lager;
   $lager_info->lager_name = $lager_info->lager_bezeichnung($clean_arr['Empfaenger_id']);
   $clean_arr[kurzbeschreibung]= "Rechnung an Lager $lager_info->lager_name<br>$kurzbeschreibung";
   }
   if($clean_arr[Empfaenger_typ] == 'Partner'){
   $clean_arr[kurzbeschreibung]= "Rechnung an Partner<br>$kurzbeschreibung";
   }
   
   $netto_betrag = 0;
   for($b=0;$b<count($uebernahme_arr[positionen]);$b++){
   	$netto_betrag = $netto_betrag + ($uebernahme_arr[positionen][$b][menge] * $uebernahme_arr[positionen][$b][preis]);
   	$netto1 = $uebernahme_arr[positionen][$b][menge] * $uebernahme_arr[positionen][$b][preis];
   	$uebernahme_arr[positionen][$b][mwst_betrag] = $mwst_betrag;
    }
   $clean_arr[nettobetrag]= $netto_betrag;
   $clean_arr[skonto]= $uebernahme_arr[RECHNUNG_SKONTO];
   $clean_arr[faellig_am]= $uebernahme_arr[RECHNUNG_FAELLIG_AM];
   $clean_arr[netto_betrag]= $uebernahme_arr[RECHNUNG_NETTO_BETRAG];
   $clean_arr[brutto_betrag]= $uebernahme_arr[RECHNUNG_BRUTTO_BETRAG];
   $clean_arr[skonto_betrag]= $uebernahme_arr[RECHNUNG_SKONTO_BETRAG];
   $clean_arr[empfangs_geld_konto]= $uebernahme_arr[EMPFANGS_GELD_KONTO];
   $rechnung = new rechnung;
  echo "<pre>";
  #print_r($clean_arr);
  echo "<hr>";
  #print_r($uebernahme_arr);
   #$gespeicherte_belegnr =$rechnung->auto_rechnung_speichern($clean_arr);
   
   #$rechnung->auto_positionen_speichern($gespeicherte_belegnr , $uebernahme_arr[positionen]);
  
   $form->ende_formular();
    }
    break;
  
  
  case "AUTO_RECHNUNG_VORSCHAU":
   $f = new formular;
   $f->fieldset("Rechnung speichern", 'rechnung_speichern');
  /* echo "<pre>";
   #print_r($_POST);
   echo "</pre>";
   */
   $r = new rechnung;
    if(!empty($_POST)){
    for($a=0;$a<count($_POST[uebernehmen]);$a++){
    	
    	$zeile_uebernehmen = $_POST[uebernehmen][$a];
    	$menge= $_POST[positionen][$zeile_uebernehmen][menge];
    	$preis =$_POST[positionen][$zeile_uebernehmen][preis];	
    	
    	$uebernahme_arr[positionen][] = $_POST[positionen][$zeile_uebernehmen];
    	}
    	$uebernahme_arr[RECHNUNG_AUSSTELLER_TYP] = $_POST[RECHNUNG_AUSSTELLER_TYP];
    	$uebernahme_arr[RECHNUNG_AUSSTELLER_ID] = $_POST[RECHNUNG_AUSSTELLER_ID];
		$uebernahme_arr[RECHNUNG_EMPFAENGER_TYP] = $_POST[RECHNUNG_KOSTENTRAEGER_TYP];//objekt, Haus, Einheit, Partner, Lager
    	$uebernahme_arr[RECHNUNG_EMPFAENGER_ID] = $_POST[RECHNUNG_KOSTENTRAEGER_ID];
    	#$uebernahme_arr[RECHNUNG_SKONTO] = $_POST[skonto];
    	$uebernahme_arr[RECHNUNG_FAELLIG_AM] = $_POST[faellig_am];
     	$uebernahme_arr[EMPFANGS_GELD_KONTO]= $_POST[geld_konto];
   		$uebernahme_arr[RECHNUNGSDATUM]= $_POST[rechnungsdatum];
   		
     	
     	 $partner_info = new partner; 
		 if($uebernahme_arr[RECHNUNG_AUSSTELLER_TYP] == 'Partner'){
		 $rechnung_von =$partner_info->get_partner_name($uebernahme_arr[RECHNUNG_AUSSTELLER_ID]);
		 }
		 if($uebernahme_arr[RECHNUNG_AUSSTELLER_TYP] == 'Kasse'){
		 $kassen_info = new kasse;
		 $kassen_info->get_kassen_info($uebernahme_arr[RECHNUNG_AUSSTELLER_ID]);
		 $rechnung_von = $kassen_info->kassen_name;
		 }
   
   $clean_arr[RECHNUNGSDATUM] = $uebernahme_arr[RECHNUNGSDATUM];
   $clean_arr[RECHNUNG_AUSSTELLER_TYP]= $uebernahme_arr[RECHNUNG_AUSSTELLER_TYP];
   $clean_arr[RECHNUNG_AUSSTELLER_ID]= $uebernahme_arr[RECHNUNG_AUSSTELLER_ID];
   $clean_arr[RECHNUNG_EMPFAENGER_TYP]= $uebernahme_arr[RECHNUNG_EMPFAENGER_TYP];
   $clean_arr[RECHNUNG_EMPFAENGER_ID]= $uebernahme_arr[RECHNUNG_EMPFAENGER_ID];
   $clean_arr[RECHNUNG_FAELLIG_AM] = $uebernahme_arr[RECHNUNG_FAELLIG_AM];
   $clean_arr[EMPFANGS_GELD_KONTO]= $uebernahme_arr[EMPFANGS_GELD_KONTO];
   
   $kurzbeschreibung = $_POST[kurzbeschreibung];  
   
   
   $objekt_info = new objekt;
   if($clean_arr[RECHNUNG_EMPFAENGER_TYP] == 'Objekt'){
   $objekt_info->get_objekt_name($clean_arr[RECHNUNG_EMPFAENGER_ID]);
   $objekt_info->get_objekt_eigentuemer_partner($clean_arr[RECHNUNG_EMPFAENGER_ID]);
   $clean_arr[kurzbeschreibung]= "Rechnung f�r $objekt_info->objekt_name<br>$kurzbeschreibung";
   }
   if($clean_arr[RECHNUNG_EMPFAENGER_TYP] == 'Haus'){
   $haus_info = $r->kostentraeger_ermitteln('Haus', $clean_arr['RECHNUNG_EMPFAENGER_ID']);
   $clean_arr[kurzbeschreibung]= "Rechnung f�r Haus $haus_info<br>$kurzbeschreibung";
   }
   if($clean_arr[RECHNUNG_EMPFAENGER_TYP] == 'Einheit'){
   $einheit = $r->kostentraeger_ermitteln('Einheit', $clean_arr['RECHNUNG_EMPFAENGER_ID']);
   $clean_arr[kurzbeschreibung]= "Rechnung f�r Einheit $einheit<br>$kurzbeschreibung";
   }
   if($clean_arr[RECHNUNG_EMPFAENGER_TYP] == 'Lager'){
   $lager_info = new lager;
   $lager_info->lager_name = $lager_info->lager_bezeichnung($clean_arr['RECHNUNG_EMPFAENGER_ID']);
   $clean_arr[kurzbeschreibung]= "Rechnung an Lager $lager_info->lager_name<br>$kurzbeschreibung";
   }
   if($clean_arr[RECHNUNG_EMPFAENGER_TYP] == 'Partner'){
   $clean_arr[kurzbeschreibung]= "Rechnung an Partner<br>$kurzbeschreibung";
   }
   
   $netto_betrag = 0;
   $brutto_betrag = 0;
   /*Position Einzelnettopreis berechnen und Gesamtnetto bilden*/
   for($b=0;$b<count($uebernahme_arr[positionen]);$b++){
   	$preis = number_format($uebernahme_arr[positionen][$b][preis], 2 ,'.','');
   	#($zahl,2, ",", ".");
   	$netto_pos = (($uebernahme_arr[positionen][$b][menge] * $preis)/100) * (100 -$uebernahme_arr[positionen][$b][rabatt_satz]);
   	$netto_betrag = $netto_betrag + $netto_pos;
   	$beleg_nr = $uebernahme_arr[positionen][$b][beleg_nr];
   	$position = $uebernahme_arr[positionen][$b][position];
   	$mwst_satz = $r->mwst_satz_der_position($beleg_nr, $position);
   	$pos_mwst = $uebernahme_arr[positionen][$b][skonto];
   	
   	echo "Bel$beleg_nr POS$position MWST$mwst_satz SKONTO $skonto<br>";
   	$brutto_betrag = $brutto_betrag + ($netto_pos + ($netto_pos/100)*($mwst_satz));
   	}
   
   $clean_arr[nettobetrag]= number_format($netto_betrag, 2,'.','');
   $clean_arr[bruttobetrag]= number_format($brutto_betrag, 2,'.','');
   #$clean_arr[skonto]= $uebernahme_arr[RECHNUNG_SKONTO]; //prozent
   
   $rechnung = new rechnung;
  /*echo "<pre>";
  print_r($clean_arr);
  echo "<hr>";
  print_r($uebernahme_arr);
  */ 
  $gespeicherte_belegnr =$rechnung->auto_rechnung_speichern($clean_arr);
   
  $rechnung->auto_positionen_speichern($gespeicherte_belegnr , $uebernahme_arr[positionen]);
  $rechnung->rechnung_als_vollstaendig($gespeicherte_belegnr);
  hinweis_ausgeben("Rechnung wurde erstellt.<br>Sie werden gleich zur neuen Rechnung weitergeleitet.");
  #weiterleiten_in_sec("?daten=rechnungen&option=rechnungs_uebersicht&belegnr=$gespeicherte_belegnr", 3);
   $rr = new rechnungen;
   $rr->update_skontobetrag($gespeicherte_belegnr);
   $f->fieldset_ende();
    }
    break;
  
  
    
    case "zahlung_freigeben":
   	$r = new rechnung;
    $belegnr =$_REQUEST['belegnr']; 
    $r->rechnung_als_freigegeben($belegnr);
   	hinweis_ausgeben("Rechnung wurde zur Zahlung freigegeben!");
   	weiterleiten_in_sec("?daten=rechnungen&option=rechnungs_uebersicht&belegnr=$belegnr", 2);
   	break;
   	
   	case "als_bezahlt_markieren":
   	$r = new rechnung;
    $belegnr =$_REQUEST['belegnr']; 
    $r->rechnung_als_freigegeben($belegnr);
   	hinweis_ausgeben("Rechnung wurde zur Zahlung freigegeben!");
   	weiterleiten_in_sec("?daten=rechnungen&option=rechnungs_uebersicht&belegnr=$belegnr", 2);
   	break;
   	
   	
   	
  
    
    case "eingangsbuch":
   	$p = new partner;
   	$link = "?daten=rechnungen&option=eingangsbuch";
   	if(isset($_REQUEST['partner_wechseln'])){
  	unset($_SESSION['partner_id']);
   	$p->partner_auswahl($link);
   	}
   	if(isset($_REQUEST['partner_id'])){
  $_SESSION['partner_id'] = $_REQUEST['partner_id'];
   	}
   	$r = new rechnung;
    
    
    $partner_id = $_SESSION['partner_id'];
    
    if(isset($_REQUEST['monat']) && isset($_REQUEST['jahr'])){
    	if($_REQUEST['monat']!= 'alle'){
    	$_SESSION['monat'] = sprintf('%02d',$_REQUEST['monat']);
    	}else{
    	$_SESSION['monat'] = $_REQUEST['monat']; 
    	}
 	$_SESSION['jahr'] = $_REQUEST['jahr'];    	
    }
    
     if(empty($partner_id) && empty($_SESSION['lager_id'])){
    $p->partner_auswahl($link);  
    }
    else{
    #$p->partner_auswahl($link);
    if(isset($_SESSION['monat'])){
    $monat = $_SESSION['monat'];
    }
    if(isset($_SESSION['jahr'])){
    $jahr = $_SESSION['jahr'];
    }
    if(empty($monat)) {
    $monat = date("m");
    }
    
    if(empty($jahr)){
    $jahr = date("Y");	
    }
    $rechnung = new rechnung();
    
    if(file_exists("print_css/$rechnung->rechnungs_aussteller_typ/$rechnung->rechnungs_aussteller_id.css")){
   	echo "<header><link rel=\"stylesheet\" type=\"text/css\"  href=\"print_css/$rechnung->rechnungs_aussteller_typ/$rechnung->rechnungs_aussteller_id.css\" media=\"print\"></header>";	
   	}else{
   	echo "<header><link rel=\"stylesheet\" type=\"text/css\"  href=\"css/print_rechnungen.css\" media=\"print\"></header>";	
   	}
  #  $r->rechnungseingangsbuch('Partner', $partner_id, $monat, $jahr, 'Rechnung');
  	if(!empty($_SESSION['partner_id']) && empty($_SESSION['lager_id'])){
    $r->rechnungseingangsbuch('Partner', $partner_id, $monat, $jahr, 'Rechnung');	
    }
    if(!empty($_SESSION['partner_id']) && !empty($_SESSION['lager_id'])){
    $r->rechnungseingangsbuch('Partner', $partner_id, $monat, $jahr, 'Rechnung');	
    }
    if(empty($_SESSION['partner_id']) && !empty($_SESSION['lager_id'])){
    $r->rechnungseingangsbuch('Lager', $_SESSION['lager_id'], $monat, $jahr, 'Rechnung');	
    }
    if(empty($_SESSION['partner_id']) && empty($_SESSION['lager_id'])){
    echo "F�r Eingangsrechungen einen Partner oder ein Lager w�hlen";	
    }
  
    }
    
   	break; 
   	
   	
   	
   	case "ausgangsbuch":
   	$p = new partner;
    $link = "?daten=rechnungen&option=ausgangsbuch";
   	if(isset($_REQUEST['partner_wechseln'])){
  	unset($_SESSION['partner_id']);
   	$p->partner_auswahl($link);
   	}
   	
   	if(isset($_REQUEST['partner_id'])){
  $_SESSION['partner_id'] = $_REQUEST['partner_id'];
   	}
   	$r = new rechnung;
    
    $partner_id = $_SESSION['partner_id'];
    
    if(isset($_REQUEST['monat']) && isset($_REQUEST['jahr'])){
    	if($_REQUEST['monat']!= 'alle'){
    	$_SESSION['monat'] = sprintf('%02d',$_REQUEST['monat']);
    	}else{
    	$_SESSION['monat'] = $_REQUEST['monat']; 
    	}
 	$_SESSION['jahr'] = $_REQUEST['jahr'];    	
    }
    
    if(empty($partner_id) && empty($_SESSION['lager_id'])){
    $p->partner_auswahl($link); 
    }
    else{
    #$p->partner_auswahl($link);
    $monat = $_SESSION['monat'];
    $jahr = $_SESSION['jahr'];
    
    if(empty($monat) OR empty($jahr)){
    $monat = date("m");
    $jahr = date("Y");	
    }
    $rechnung = new rechnung();
     if(file_exists("print_css/$rechnung->rechnungs_aussteller_typ/$rechnung->rechnungs_aussteller_id.css")){
   	echo "<header><link rel=\"stylesheet\" type=\"text/css\"  href=\"print_css/$rechnung->rechnungs_aussteller_typ/$rechnung->rechnungs_aussteller_id.css\" media=\"print\"></header>";	
   	}else{
   	echo "<header><link rel=\"stylesheet\" type=\"text/css\"  href=\"css/print_rechnungen.css\" media=\"print\"></header>";	
   	}
    if(!empty($_SESSION['partner_id']) && empty($_SESSION['lager_id'])){
    $r->rechnungsausgangsbuch('Partner', $partner_id, $monat, $jahr, 'Rechnung');	
    }
    if(!empty($_SESSION['partner_id']) && !empty($_SESSION['lager_id'])){
    $r->rechnungsausgangsbuch('Partner', $partner_id, $monat, $jahr, 'Rechnung');	
    }
    if(empty($_SESSION['partner_id']) && !empty($_SESSION['lager_id'])){
    $r->rechnungsausgangsbuch('Lager', $_SESSION['lager_id'], $monat, $jahr, 'Rechnung');	
    }
    if(empty($_SESSION['partner_id']) && empty($_SESSION['lager_id'])){
    echo "F�r Ausgangsrechungen einen Partner oder ein Lager w�hlen";	
    }
    
    }
    
   	break; 
   	
   	/*Rechnungspositionen erfassen Version 2*/
   	case "positionen_erfassen_alt":
   	$r = new rechnungen;
    $belegnr =$_REQUEST['belegnr']; 
    if(!empty($belegnr)){
    $r->form_positionen_erfassen($belegnr);
    }
   	break;
   	
   	/*Rechnungspositionen erfassen Version 2 mit Autovervollst�ndigen*/
   	case "positionen_erfassen":
   	$r = new rechnungen;
    $belegnr =$_REQUEST['belegnr']; 
    if(!empty($belegnr)){
    $r->form_positionen_erfassen2($belegnr);
    }
   	break;
   	
   	
   	/*Rechnungsposition �ndern */
   	case "position_aendern":
   	$r = new rechnungen;
    $pos = $_REQUEST['pos'];
    $belegnr = $_REQUEST['belegnr']; 
    if(!empty($belegnr) && !empty($pos)){
    $r->form_positionen_aendern($pos, $belegnr);
    }
   	break;
   	
   	/*Rechnungsposition �ndern */
   	case "position_loeschen":
   	$r = new rechnung;
    $pos = $_REQUEST['pos'];
    $belegnr = $_REQUEST['belegnr']; 
    if(!empty($belegnr) && !empty($pos)){
    $r->position_deaktivieren($pos,$belegnr);
    echo "POSITION GEL�SCHT";
    weiterleiten_in_sec("?daten=rechnungen&option=positionen_erfassen&belegnr=$belegnr", 1);
    }
    break;
   	
   	
   	/*Rechnung buchen ALT KOMBI-Chronologisch*/
   	case "rechnung_buchen":
   	$r = new rechnungen;
    $belegnr =$_REQUEST['belegnr']; 
    if(!empty($belegnr)){
    $r->form_rechnung_buchen($belegnr);
    }else{
    hinweis_ausgeben('Keine Rechung gew�hlt!');	
    }
   	break;
   	
   	
   	/*Rechnung Zahlung buchen*/
   	case "rechnung_zahlung_buchen":
   	$belegnr =$_REQUEST['belegnr']; 
   	
   	$r = new rechnungen;
    if(!empty($belegnr)){
    $r1 = new rechnung;
   	$r1->rechnung_als_freigegeben($belegnr);
    $r->form_rechnung_zahlung_buchen($belegnr);
    }else{
    hinweis_ausgeben('Keine Rechung gew�hlt!');	
    }
   	break;
   	
   	
   	/*Rechnung durch Kontoauszug best�tigen und buchen*/
   	case "rechnung_empfang_buchen":
   	$r = new rechnungen;
    $belegnr =$_REQUEST['belegnr']; 
    
    if(!empty($belegnr)){
    $r1 = new rechnung;
    $r1->rechnung_als_freigegeben($belegnr);
   # print_r($_SESSION);
    $r->form_rechnung_empfang_buchen($belegnr);
    }else{
    hinweis_ausgeben('Keine Rechung gew�hlt!');	
    }
   	break;
   	
   	
   	/*Rechnung buchen, daten gesendet*/
   	case "rechnung_buchen_gesendet":
   	$r = new rechnungen;
    $b = new buchen;
    $buchungsbetrag = $_POST['buchungsbetrag'];
    $buchungs_art = $_POST['buchungsart'];
    $belegnr = $_POST['belegnr'];
    $r->rechnung_grunddaten_holen($belegnr);
    echo "<pre>";
    #print_r($r);
    print_r($_POST);
    $datum = date_german2mysql($_POST['datum']);
    $kto_auszugsnr = $_POST['kontoauszugsnr'];
    $vzweck = $_POST['vzweck'];
    $geldkonto_id = $_POST['geld_konto'];
    $kostentraeger_typ = $_POST['kostentraeger_typ'];
    $kostentraeger_id = $_POST['kostentraeger_id'];
    $kostenkonto = $_POST['kostenkonto'];
    $_SESSION['geldkonto_id'] = $geldkonto_id;
    $_SESSION['temp_kontoauszugsnummer'] = $kto_auszugsnr;
    	
    	/*Entscheidung ob Rechnung oder Gutschrift gebucht wird, daher + o. - als vorzeichen*/
    	if($r->rechnungstyp == 'Rechnung' OR $r->rechnungstyp == 'Buchungsbeleg'){
    	/*Zahlung*/
    	if($r->empfangs_geld_konto != $geldkonto_id){
    	$vorzeichen = '-';	
    	}else{
    	/*Empfang*/
    	$vorzeichen = '';	
    	}
    	}
    	
    	if($r->rechnungstyp == 'Gutschrift' OR $r->rechnungstyp == 'Stornorechnung'){
    	/*Zahlung*/
    	if($r->empfangs_geld_konto != $geldkonto_id){
    	$vorzeichen = '';	
    	}else{
    	$vorzeichen = '-';	
    	}
    	}
    	
    /*Falls nur ein Betrag zu buchen ist*/
    if($buchungs_art == 'Gesamtbetrag'){
    if($buchungsbetrag == 'Skontobetrag'){
 	$proz = $r->rechnungs_mwst/($r->rechnungs_brutto/100);
 	$skontiert_mwst = ($r->rechnungs_skontobetrag*$proz)/100; 
    $b->geldbuchung_speichern_rechnung($datum, $kto_auszugsnr, $belegnr, $vorzeichen.$r->rechnungs_skontobetrag, $vzweck, $geldkonto_id, $kostentraeger_typ, $kostentraeger_id, $kostenkonto, $vorzeichen.$skontiert_mwst);	
    }
    if($buchungsbetrag == 'Bruttobetrag'){
    $b->geldbuchung_speichern_rechnung($datum, $kto_auszugsnr, $belegnr, $vorzeichen.$r->rechnungs_brutto, $vzweck, $geldkonto_id, $kostentraeger_typ, $kostentraeger_id, $kostenkonto, $vorzeichen.$r->rechnungs_mwst);	
    }
    if($buchungsbetrag == 'Nettobetrag'){
    $b->geldbuchung_speichern_rechnung($datum, $kto_auszugsnr, $belegnr, $vorzeichen.$r->rechnungs_netto, $vzweck, $geldkonto_id, $kostentraeger_typ, $kostentraeger_id, $kostenkonto);	
    }
    }    
    
    /*Falls mehrere Betr�ge zu buchen sind, d.h wie kontiert*/
    if($buchungs_art == 'Teilbetraege'){
    $r->beleg_kontierungs_arr($datum, $kto_auszugsnr, $belegnr, $vorzeichen, $buchungsbetrag, $vzweck, $geldkonto_id);
    }	
	
   	
   	
   	if($r->empfangs_geld_konto == $geldkonto_id){
   		if($r->rechnungstyp == 'Gutschrift'){
   		$r->rechnung_als_gezahlt($belegnr, $datum);
   		}else{
   		$r->rechnung_als_bestaetigt($belegnr, $datum);	
   		}
   		}else{
   		if($r->rechnungstyp == 'Gutschrift'){
   		$r->rechnung_als_bestaetigt($belegnr, $datum);
   		}else{
   		$r->rechnung_als_gezahlt($belegnr, $datum);
   		}
   	}
   		/*if($r->status_bezahlt == '0'){
    	$r->rechnung_als_gezahlt($belegnr, $datum);
    	}
    	if($r->status_bezahlt == '1'){
    	$r->rechnung_als_bestaetigt($belegnr, $datum);
    	}
    	*/
    
    
    #if($vorzeichen==''){
   	weiterleiten_in_sec($_SESSION['last_url'], 1);
   # }else{
    #	weiterleiten_in_sec('?daten=buchen&option=eingangsbuch_kurz', 1);
    #}
    break;
   	
   	case "pos_kontierung_aufheben";	
   	$dat = $_REQUEST['dat'];
   	$id = $_REQUEST['id'];
   	$belegnr = $_REQUEST['belegnr'];
   	if(!empty($dat) && !empty($id) && !empty($belegnr)){
   	$r = new rechnung;
   	$r->pos_kontierung_aufheben($dat, $id);
   	hinweis_ausgeben("Kontierung wurde aufgehoben");
   	weiterleiten_in_sec("?daten=rechnungen&option=rechnung_kontieren&belegnr=$belegnr", 2);
   	}
   	break;
   	
   	case "rechnungsgrunddaten_aendern":
   	$belegnr = $_REQUEST[belegnr];
   	if(!empty($belegnr)){
   	$r = new rechnungen;
   	$r->form_rechnungsgrunddaten_aendern($belegnr);
   	}else{
   	back();	
   	}
   	break;
   	
   	
   	#rechnung_gd_gesendet
   	case "rechnung_gd_gesendet":
   	$form = new formular;
    $form->fieldset("Grunddaten speichern", 'grunddaten speichern');
   	#print('<pre>');
   	#print_r($_POST);
   	$rechnung_dat = $_POST['rechnung_dat']; 
   	$belegnr = $_POST['belegnr'];
   	$rechnungsnummer = $_POST['rechnungsnummer'];
   	$a_ausnr = $_POST['a_ausnr'];
   	$e_einnr = $_POST['e_einnr'];
   	$r_datum = $_POST['rechnungsdatum'];
   	$ein_datum  = $_POST['eingangsdatum'];
   	$netto =  $_POST['netto'];
   	$brutto =  $_POST['brutto'];
   	$skontobetrag  = nummer_komma2punkt($_POST['skontobetrag']);
   	$aussteller_typ = $_POST['aus_typ'];
   	$aussteller_id = $_POST['aus_id'];
   	$empfaenger_typ = $_POST['ein_typ'];
   	$empfaenger_id = $_POST['ein_id'];
   	$stat_erfasst = $_POST['status_erfasst'];
   	$stat_voll = $_POST['status_voll'];
   	$stat_zugew = $_POST['status_zugew'];
   	$stat_z_frei = $_POST['status_z_frei'];
   	$stat_bezahlt = $_POST['status_bezahlt'];
   	$faellig_am = $_POST['faellig_am'];
   	$bezahlt_am = $_POST[bezahlt_am];
   	$kurzb = $_POST[kurzbeschreibung];
   	$empfangs_gkonto = $_POST[empfangs_geldkonto];
   	$rechnungs_typ = $_POST[rechnungstyp];
   	
   	$r = new rechnungen;
   	$r->rechnung_deaktivieren($rechnung_dat);
   	$r->rechnungs_aenderungen_speichern($rechnung_dat, $belegnr, $rechnungsnummer, $a_ausnr, $e_einnr, $rechnungs_typ, $r_datum, $ein_datum, $netto, $brutto, $skontobetrag, $aussteller_typ, $aussteller_id, $empfaenger_typ, $empfaenger_id, $stat_erfasst, $stat_voll, $stat_zugew, $stat_z_frei, $stat_bezahlt, $faellig_am, $bezahlt_am, $kurzb, $empfangs_gkonto);
   	#echo "$rechnung_dat, $belegnr, $rechnungsnummer, $a_ausnr, $e_einnr, $rechnungs_typ, $r_datum, $ein_datum, $netto, $brutto, $skonto_pro, $aussteller_typ, $aussteller_id, $empfaenger_typ, $empfaenger_id, $stat_erfasst, $stat_voll, $stat_zugew, $stat_z_frei, $stat_bezahlt, $faellig_am, $bezahlt_am, $kurzb, $empfangs_gkonto";
   	weiterleiten_in_sec("?daten=rechnungen&option=rechnungs_uebersicht&belegnr=$belegnr", 2);   	
   	$form->fieldset_ende();
    
   	break;
   	
   	/*case "submit_rbb":
   	echo "<pre>";
   	print_r($_POST);
   	break;
   	*/
   	
   	case "testr":
   	$form = new formular;
    $form->fieldset("TEST", 'test');
   	$r = new rechnungen;
   	$r->ursprungs_rechnungs_nr_arr(1667);
   	echo "<pre>";
   	
	$r->ursprungs_array_a=array_reverse($r->ursprungs_array, TRUE);
	#$r->ursprungs_array = array_reverse($input, TRUE);
   	#print_r($r->ursprungs_array);
   	
   	$anzahl_el = count($r->ursprungs_array_a);
   	print_r($r->ursprungs_array_a);
   	
	$rechnungsnrs_arr = array_keys($r->ursprungs_array_a);
   		$erste_rechnungsnr = $rechnungsnrs_arr[0];
   		#echo "start bei $erste_rechnungsnr<hr>";
   		#print_r($rechnungsnrs_arr);
   		for($a=0;$a<$anzahl_el;$a++){
   		$erste_rechnungsnr = $rechnungsnrs_arr[0];	
   		$akt_rech_nr = $rechnungsnrs_arr[$a];
   		
   			$anzahl_vor_rechnungen = count($r->ursprungs_array_a[$akt_rech_nr]);
   			$brojac = '-';
   			for($i=0;$i<$anzahl_vor_rechnungen;$i++){
   			$brojac .='-';
   			$vorrechnung = $r->ursprungs_array_a[$akt_rech_nr][$i]['U_BELEG_NR'];
   			if($vorrechnung != $akt_rech_nr){
   			echo $brojac.$vorrechnung;
   			}else{
   				echo "<br>";
   			}
   			}
   		echo "<b>|-$akt_rech_nr</b>";
   		}
   	$form->fieldset_ende();
   	break;
   	
   	case "lieferschein_erfassen":
   	$beleg_nr=$_REQUEST['beleg_nr'];
   	if(!empty($beleg_nr)){
   	$r = new rechnungen;
   	$r->form_lieferschein_erfassen($beleg_nr);
   	}else{
   		echo "Belegnr fehlt";
   	}
   	break;
   	
   	case "buchungsbelege":
   	$monat = $_REQUEST['monat'];
   	$jahr = $_REQUEST['jahr'];
   	if(empty($monat)){
   		$monat = date("m");
   	}
   	if(empty($jahr)){
   		$jahr = date("Y");
   	}
   	$r = new rechnungen;
   	$buchungsbelege_arr = $r->buchungsbelege_arr($monat, $jahr);
   	#$r->form_lieferschein_erfassen($beleg_nr);
   	$bg = new berlussimo_global;
   	$link ='?daten=rechnungen&option=buchungsbelege';
   	$bg->monate_jahres_links($jahr, $link);
   	#rechnungsbuch_anzeigen_ein_kurz
   	$r->rechnungsbuch_anzeigen_ein_kurz($buchungsbelege_arr);
   	break;
   	
   	
 	default:
 	echo "Rechnungen hauptseite";
 	
 	break;

	case "artikel_bau":
	$r = new rechnungen;
	$r->artikel_pro_kos_anzeigen('3', 'Einheit', '213', '1023');
	break;	
   	
   	
   	
   	case "anzeigen_pdf":
   	if(!empty($_REQUEST['belegnr']) && is_numeric($_REQUEST['belegnr'])){
   	$r = new rechnungen;
	$r->rechnung_anzeigen($_REQUEST['belegnr']);
   	}else{
   		echo "Rechnung w�hlen $_REQUEST[belegnr]";
   	}
   	break;   	

	case "rechnungsbuch_ausgang":
	$r = new rechnungen;
	#rechnungsbuch_pdf($typ, $von_typ, $von_id, $monat, $jahr, $rechnungstyp, $sort)
	if(!empty($_SESSION[partner_id])){
	$r->rechnungsausgangsbuch_pdf('Partner',$_SESSION[partner_id], $_REQUEST['monat'], $_REQUEST['jahr'], $_REQUEST[r_typ],$_REQUEST[sort]);
	}else{
		if(!empty($_SESSION['lager_id'])){
		$r->rechnungsausgangsbuch_pdf('Lager',$_SESSION['lager_id'], $_REQUEST['monat'], $_REQUEST['jahr'], $_REQUEST[r_typ],$_REQUEST[sort]);
		}else{
			echo "F�r Lagerrechnungen Lager w�hlen und f�r Partnerrechnungen den Partner";
		}
	}
	break;
	
		
	case "rechnungsbuch_eingang":
	$r = new rechnungen;
	#rechnungsbuch_pdf($typ, $von_typ, $von_id, $monat, $jahr, $rechnungstyp, $sort)
	if(!empty($_SESSION[partner_id])){
	$r->rechnungseingangsbuch_pdf('Partner',$_SESSION[partner_id], $_REQUEST['monat'], $_REQUEST['jahr'], $_REQUEST[r_typ],$_REQUEST[sort]);
	}else{
		if(!empty($_SESSION['lager_id'])){
		$r->rechnungseingangsbuch_pdf('Lager',$_SESSION['lager_id'], $_REQUEST['monat'], $_REQUEST['jahr'], $_REQUEST[r_typ],$_REQUEST[sort]);
		}else{
			echo "F�r Lagerrechnungen Lager w�hlen und f�r Partnerrechnungen den Partner";
		}
	}
	
	break;

case "rechnungsbuch_suche":
$r = new rechnungen;
$r->form_rbuecher_suchen();
break;

case "rechnungsbuch_suche1":
if(!empty($_REQUEST[buchart]) && !empty($_REQUEST[r_inhaber_t]) && !empty($_REQUEST[r_inhaber]) && !empty($_REQUEST['r_art']) && !empty($_REQUEST['monat']) && !empty($_REQUEST['jahr'])){
	$r = new rechnungen;
	$buchart = $_REQUEST[buchart];
	$r_inhaber_t = $_REQUEST[r_inhaber_t];
	$r_inhaber_bez = $_REQUEST[r_inhaber];
	if($r_inhaber_t == 'Partner'){
	$p = new partners;
	$p->get_partner_id($r_inhaber_bez);
	$r_inhaber_id = $p->partner_id;	
	}else{
	$l = new lager;
	$r_inhaber_id = $l->get_lager_id($r_inhaber_bez);	
	}
	
	if(empty($r_inhaber_id)){
		die("Datenfehler - Rechnungsinhaber $r_inhaber_t unbekannt");
	}
	
	$r_art = $_REQUEST['r_art'];
	$monat = $_REQUEST['monat'];
	if(is_numeric($monat)){
	$monat = sprintf('%02d',$monat);
	}
	$jahr = $_REQUEST['jahr'];
	
	if($buchart=='ausgangsbuch'){
	#echo "$r_inhaber_t,$r_inhaber_id, $monat, $jahr, $r_art, $_REQUEST[sort]";
	$r->rechnungsausgangsbuch_pdf($r_inhaber_t,$r_inhaber_id, $monat, $jahr, $r_art, 'ASC');	
	}
	if($buchart=='eingangsbuch'){
	echo "$r_inhaber_t,$r_inhaber_id, $monat, $jahr, $r_art, $_REQUEST[sort]";
	$r->rechnungseingangsbuch_pdf($r_inhaber_t,$r_inhaber_id, $monat, $jahr, $r_art, 'ASC');	
	}
	
}else{
	echo "Eingabe unvollst�ndig";
print_req();
}
break;


/*Angebote*/
case "angebot_erfassen": // Angebot anlegen maske
$r = new rechnungen();
$r->form_angebot_erfassen();
break;

case "angebot_erfassen1": // Angebot anlegen/speichern
$r = new rechnungen();
if(!empty($_REQUEST['aussteller_typ']) && !empty($_REQUEST['aussteller_id']) && !empty($_REQUEST['empfaenger_typ']) && !empty($_REQUEST['empfaenger_id'])){
$r->angebot_speichern($_REQUEST['aussteller_typ'],$_REQUEST['aussteller_id'],$_REQUEST['empfaenger_typ'], $_REQUEST['empfaenger_id'], $_REQUEST['kurzbeschreibung']);
#weiterleiten('?daten=rechnungen&option=meine_angebote');
}else{
	fehlermeldung_ausgeben("Daten unvollst�ndig");
}
	break;

	
	
case "meine_angebote":
	$r = new rechnungen();
	$r->meine_angebote_anzeigen();
	break;

case "ang_bearbeiten":
if(!empty($_REQUEST['ang_id'])){
$r = new rechnungen();
$r->form_angebot_bearbeiten($_REQUEST['ang_id']);
}else{
	echo "Angebot w�hlen";
	}

break;		

	case "ang2beleg":
	if(!empty($_REQUEST['belegnr'])){
		$r = new rechnungen();
		$r->angebot2beleg($_REQUEST['belegnr']);
	}else{
		fehlermeldung_ausgeben("Angebot w�hlen!");
	}
	break;


	/*Aus noch unbekanntem Grund, tauchen 99.99% Rabatt oder 9.99% Skonti in neuen
	 * Rechnungen auf. Beim �ffnen der Rechnung wird es erkannt und eine Option f�r die Autokorrektur angeboten
	 *Bei der Korrektur wird aus der Ursprungsrechnung der Rabatt und Skonti �bernommen 
	 */
	case "autokorrektur_pos":
	if(!empty($_REQUEST['belegnr'])){
	$r = new rechnungen;
	$r->autokorrektur_pos($_REQUEST['belegnr']);
	}else{
		fehlermeldung_ausgeben('Bitte Rechnung w�hlen!');
	}
	break;
	
	case "edisnp":
	if(!empty($_REQUEST['belegnr'])){
	$r = new rechnungen;+
	$r->edisp($_REQUEST['belegnr']);
	}
	break;
	
	case "reg_pool":
	$_SESSION['pool_id'] = $_REQUEST['pool_id'];	
	break;
	
	case "u_pool_liste":
	$f = new formular();
	$f->fieldset('Kostentraeger w�hlen', 'pool_tab2');
		$r = new rechnungen();
	$r->pool_liste_wahl();
	$f->fieldset_ende();	
	break;
	
	case "u_pool_edit":
	$f = new formular();
	$f->fieldset('POOL', 'pool_tab');	
	$r = new rechnungen();
	if(!empty($_REQUEST['kos_typ']) && !empty($_REQUEST['kos_id'])){
	$r->u_pool_edit($_REQUEST['kos_typ'],$_REQUEST['kos_id'], $_REQUEST['aussteller_typ'],$_REQUEST['aussteller_id']);
	}else{
	echo "Rechnungsempf�nger w�hlen";	
	}	
	$f->fieldset_ende();
	break;
	
	case "u_pool_erstellen":
	$f = new formular();
	$f->fieldset('Unterpools erstellen', 'u_pool_tab');
	$r = new rechnungen();
	$r->u_pools_erstellen();
	$f->fieldset_ende();	
	break;
	
	/*Verbindlichkeiten*/
	case "verbindlichkeiten":
	$r = new rechnungen();
	if(empty($_REQUEST['jahr'])){
	$jahr = date("Y");
	}else{
		$jahr = $_REQUEST['jahr'];
	}
	$r->verbindlichkeiten($jahr);
	break;
	
	/*Forderungen*/
	case "forderungen":
	$r = new rechnungen();
	$jahr = date("Y");
	$r->forderungen($jahr);
	break;
	
	
	/*Importfunktion HWPWIN Projekttransfer XML*/
	case "import_hwpwin_xml_rechnung":
	$r = new rechnungen();
	$r->test_xml();	
	break;	
	
	case "import_ugl":
	$r = new rechnungen();
	$r->test_ugl();	
	break;	
	
	case "form_ugl":
	$r = new rechnungen();
	$r->form_import_ugl();
	break;

	case "ugl_sent":
	#print_req();
	#	echo '<pre>';
	#print_r($_FILES);
	$r = new rechnungen();
	$tmp_datei = $_FILES['Datei']['tmp_name'];
	$arr = $r->get_ugl_arr($tmp_datei);
	echo '<pre>';
	print_r($arr);
	
	if(is_array($arr)){
	@unlink($tmp_datei);
	#print_r($arr);
	$aussteller_typ = 'Partner';
	$aussteller_id  = $_POST['aussteller_id'];
	$empfaenger_typ  = 'Partner';
	$empfaenger_id = $_POST['empfaenger_id'];
	$rnr = $_POST['rnr'];
	$r_datum = $_POST['r_datum'];
	$faellig = $_POST['faellig'];
	$eingangsdatum = $_POST['eingangsdatum'];
	$kurzinfo = $_POST['kurzbeschreibung'];
	$skonto = $_POST['skonto'];
	$kurzinfo_ugl = $r->ibm850_encode($arr['a_nr_hw'].' '.$arr['kundentext'].' '.$arr['vorgangsnr_gh'].' '.$arr['datum_d']);
	#$kurzinfo_ugl = $arr['a_nr_hw'].' '.$arr['kundentext'].' '.$arr['vorgangsnr_gh'].' '.$arr['datum_d'];
	$kurzinfo .= '\n '.$kurzinfo_ugl;
	
	echo "<b>$kurzinfo</b>";
	if($arr['a_art'] != 'PA' && $arr['a_art'] != 'AB' && $arr['a_art'] != 'RG'){
	$aart= $arr['a_art'];
		die("Abbruch!<br>Die Datei ist kein Angebot, sowie keine Rechnung!!! <b>TYP:$aart</b>");
	}
	if($arr['a_art']=='PA'){//Preisangebot
	$r_typ = 'Angebot'; 
	}
	if($arr['a_art']=='AB' or $arr['a_art']=='RG'){
	$r_typ = 'Rechnung'; // Auftragsbest�tigung
	}

	$beleg_nr = $r->rechnung_erstellen_ugl($rnr, $r_typ, $r_datum, $eingangsdatum, $aussteller_typ, $aussteller_id, $empfaenger_typ, $empfaenger_id, $faellig, $kurzinfo,0,0,0);
	$anz = count($arr['positionen_arr']);
	for($a=1;$a<=$anz;$a++){
	/*$arr['POA'] = substr($pos,0,3);
	$arr['POSNRHW'] = substr($pos,3,10);
	$arr['POSNRGH'] = substr($pos,13,10);
	$arr['ARTIKELNR'] = substr($pos,23,15);
	$arr['MENGE'] = substr($pos,38,11);
	$arr['ARTBEZ1'] = substr($pos,49,40);
	$arr['ARTBEZ2'] = substr($pos,89,40);
	$arr['POS_BRUTTO'] = substr($pos,129,11);//2 nachkommastellen
	$arr['PE'] = substr($pos,140,1); //null leer Stk, 2=10stk, 3=100, 4=1000
	$arr['POS_NETTO'] = substr($pos,141,11); //2 nachkommastellen
	$arr['RABATT1'] = substr($pos,152,5); //Info
	$arr['RABATT2'] = substr($pos,157,5); //Info
	$arr['LV_NR'] = substr($pos,162,18); //LV-Nummer
	$arr['POS_ART'] = substr($pos,180,1); //Alternativpos leer=iriginal, A=Alternativ
	$arr['POS_TYP'] = substr($pos,181,1); //Positionstyp = J=Jumbo, U=Jumbounterpos, H=regular Art Pos
	*/
	$pos_typ = $arr['positionen_arr'][$a]['POS_TYP'];
	$artikel_nr = ltrim(rtrim($arr['positionen_arr'][$a]['ARTIKELNR']));
	$menge = $arr['positionen_arr'][$a]['MENGE']/1000;
	$pos_netto = $arr['positionen_arr'][$a]['POS_NETTO']/100;
	$e_preis = $pos_netto/$menge;
	$rabatt1 = $arr['positionen_arr'][$a]['RABATT1']/100;
	$rabatt2 = $arr['positionen_arr'][$a]['RABATT2']/100;
	$listenpreis = ($e_preis/(100-$rabatt1))*100;
	$bezeichnung = $r->ibm850_encode($arr['positionen_arr'][$a]['ARTBEZ1'].' '.$arr['positionen_arr'][$a]['ARTBEZ2']);
	$mwst = '19';
	
	
	
	$vpe = $arr['positionen_arr'][$a]['PE'];
	if($vpe =='0'){
	$vpe = 'Stk';	
	}
	if($vpe =='2'){
	$vpe = 'Stk';
	#$vpe = '10Stk';	
	}
	if($vpe =='3'){
	$vpe = 'Stk';
	#$vpe = '100Stk';	
	}
	if($vpe =='4'){
	$vpe = 'Stk';
	#$vpe = '1000Stk';	
	}
	
	$r1 = new rechnung;
	if(!is_array($r1->artikel_info($aussteller_id, $artikel_nr))){
		$r1->artikel_leistung_mit_artikelnr_speichern($aussteller_id, $bezeichnung, $listenpreis, $artikel_nr, $rabatt1, $vpe, $mwst, $skonto);
	}
	echo "$a. $bezeichnung<br>";
	
	$r->position_speichern($beleg_nr, $beleg_nr, $aussteller_id, $artikel_nr, $menge, $listenpreis, $mwst, $skonto,$rabatt1, $pos_netto);
	#return $arr;
	}
	weiterleiten_in_sec("?daten=rechnungen&option=rechnungs_uebersicht&belegnr=$beleg_nr", 3);
	}
		
	break;
	
	
	case "import_csv":
	echo "CSV";
	$r = new rechnungen();
	$r->form_import_csv();
	break;
	
	
	case "csv_sent":
	$r = new rechnungen();
	$tmp_datei = $_FILES['Datei']['tmp_name'];
	if($handle = fopen($tmp_datei, "r")){
      $zeilen=file($tmp_datei);
	}else{
		die('Datei konnte nicht gelesen werden!');
	}
	echo '<pre>';
	#print_r($zeilen);
	$arr = $zeilen;
	#die();
	if(is_array($arr)){
	@unlink($tmp_datei);
	#print_r($arr);
	$aussteller_typ = 'Partner';
	$aussteller_id  = $_POST['aussteller_id'];
	$empfaenger_typ  = 'Partner';
	$empfaenger_id = $_POST['empfaenger_id'];
	$rnr = $_POST['rnr'];
	$r_datum = $_POST['r_datum'];
	$faellig = $_POST['faellig'];
	$eingangsdatum = $_POST['eingangsdatum'];
	$kurzinfo = $_POST['kurzbeschreibung'];
	$skonto = $_POST['skonto'];
	$beleg_typ = $_POST['beleg_typ'];
		

	$beleg_nr = $r->rechnung_erstellen_csv($beleg_typ, $r_datum, $eingangsdatum, $aussteller_typ, $aussteller_id, $empfaenger_typ, $empfaenger_id, $faellig, $kurzinfo,0,0,0);
	$anz = count($arr);
	$b_pos = 1;
	for($a=1;$a<$anz;$a++){
	$zeile = explode(';',$arr[$a]);
	$pos_typ = $zeile[2];//Einheit LV LG
	if($pos_typ == 'Position'){
	$artikel_nr = ltrim(rtrim($zeile[0])).ltrim(rtrim($zeile[16]));
	$menge = nummer_komma2punkt($zeile[3]);
	$vpe = $zeile[4];
	$pos_netto = nummer_komma2punkt($zeile[10]);
	$e_preis = $pos_netto/$menge;
	$rabatt1 = $zeile[6];
	#$listenpreis = ($e_preis/(100-$rabatt1))*100;
	$listenpreis = ($pos_netto/(100-$rabatt1)*100)/$menge;
	$bezeichnung = $zeile[1];
	$mwst = $zeile[7];
		
	
	
	$r1 = new rechnung;
	if(!is_array($r1->artikel_info($aussteller_id, $artikel_nr))){
		$r1->artikel_leistung_mit_artikelnr_speichern($aussteller_id, $bezeichnung, $listenpreis, $artikel_nr, $rabatt1, $vpe, $mwst, $skonto);
	}
	echo "$a. $bezeichnung<br>";
	
	$r->position_speichern($beleg_nr, $beleg_nr, $aussteller_id, $artikel_nr, $menge, $listenpreis, $mwst, $skonto,$rabatt1, $pos_netto);
	#return $arr;
	$b_pos++;
	}
	
	if($pos_typ=='LG'){
	$pool_bez = $zeile[0].' '.$zeile[1];
	$rr = new rechnungen();	
	$rr->insert_pool_bez_in_gruppe($pool_bez, $beleg_nr, $b_pos);
	}
	
	}//end for
	weiterleiten_in_sec("?daten=rechnungen&option=rechnungs_uebersicht&belegnr=$beleg_nr", 3);
	}
		
	break;
	
	case "kosten_einkauf":
		$r = new rechnungen();
		$r->form_kosten_einkauf();
	break;

	
	case "kosten_einkauf_send":
	if(!empty($_REQUEST['kostentraeger_typ']) && !empty($_REQUEST['kostentraeger_id']) && !empty($_REQUEST['empf_typ']) && !empty($_REQUEST['empf_id'])){
		$r = new rechnungen();
		$kos_typ = $_REQUEST['kostentraeger_typ'];
		$kos_bez = $_REQUEST['kostentraeger_id'];
		$b = new buchen();
		$kos_id = $b->kostentraeger_id_ermitteln($kos_typ, $kos_bez);
		$empf_typ = $_REQUEST['empf_typ'];
		$empf_id = $_REQUEST['empf_id'];
	
		$r->kosten_einkauf($kos_typ, $kos_id, $empf_typ, $empf_id);
		#print_req();
		#echo "$kos_typ, $kos_id, $empf_typ, $empf_id";
		
	}else{
	#	print_req();
		echo "Kostentraeger Koniertung w�hlen";
	}
	break;
	
	case "teil_rg_hinzu":
	if(!empty($_REQUEST['beleg_id'])){
	$r = new rechnungen();
	$beleg_id = $_REQUEST['beleg_id'];
	$r->form_teil_rg_hinzu($beleg_id);
	}else{
		echo "Schlussrechnung w�hlen";
	}
	break;
	
	case "send_teil_rg":
	if(!empty($_POST['beleg_id']) && is_array($_POST['tr_ids'])){
	$r = new rechnungen();
	$r->teilrechnungen_hinzu($_POST['beleg_id'], $_POST['tr_ids']);	
	$beleg_id = $_POST['beleg_id'];
	weiterleiten("?daten=rechnungen&option=teil_rg_hinzu&beleg_id=$beleg_id");
	}else{
	echo "Auswahl unvollst�ndig err:RGSJH2000";
	}	
	break;
	
	case "teil_rg_loeschen":
	if(!empty($_REQUEST['beleg_id']) && !empty($_REQUEST['t_beleg_id'])){
	$r = new rechnungen();
	$r->teilrechnungen_loeschen($_REQUEST['beleg_id'], $_REQUEST['t_beleg_id']);	
	$beleg_id = $_REQUEST['beleg_id'];
	weiterleiten("?daten=rechnungen&option=teil_rg_hinzu&beleg_id=$beleg_id");
	}else{
	echo "Auswahl unvollst�ndig err:RGSJH3000";
	}	
	break;
	
	
	case "seb":
	$rr = new rechnungen();
	$rr->seb_rgs_anzeigen();
	break;	
	
	case "vg_rechnungen":
	if(!isset($_SESSION['objekt_id']) or !isset($_SESSION['partner_id'])){
		die(fehlermeldung_ausgeben("Partner (Hausverwalter) und Objekt w�hlen"));
	}
	$rr = new rechnungen();
	/*echo $_SESSION['objekt_id'];
	echo $_SESSION['partner_id'];*/
	$rr->form_vg_rechnungen($_SESSION['objekt_id'], $_SESSION['partner_id']);
	break;	
	
	case "rgg":
		if(!isset($_POST['check'])){
			die(fehlermeldung_ausgeben("Einheiten w�hlen!!!"));
		}
		$einheiten = $_POST['check'];
		
		if(!empty($_POST['kostenkonto'])){
		$kostenkonto = $_POST['kostenkonto'];
		}else{
			die(fehlermeldung_ausgeben("Kostenkonto w�hlen"));
		}
		
		$anz_e = count($einheiten);
		$brutto_betrag = nummer_komma2punkt($_POST['brutto']);
		$kurztext = $_POST['kurztext'];
		for($a=0;$a<$anz_e;$a++){
			$id = $einheiten[$a];
			$einheit_id = $_POST['EINHEITEN'][$id];
			$empf_typ = $_POST['EMPF_TYP'][$id];
			$empf_id = $_POST['EMPF_ID'][$id];
			$e = new einheit();
			$e->get_einheit_info($einheit_id);
			$kurztext_neu = "$kurztext\nEinheit:$e->einheit_kurzname $e->einheit_lage, $e->haus_strasse $e->haus_nummer, $e->haus_plz $e->haus_stadt";
			echo "$einheit_id $empf_typ $empf_id $kurztext_neu<br>";
			$r = new rechnung;
			$letzte_belegnr = $r->letzte_beleg_nr()+1;

			$jahr = date("Y");
			$datum = date("Y-m-d");
			$letzte_aussteller_rnr =	$r->letzte_aussteller_ausgangs_nr($_SESSION['partner_id'], 'Partner', $jahr, 'Rechnung')+1;
			$letzte_aussteller_rnr = sprintf('%03d',$letzte_aussteller_rnr);
			$r->rechnungs_kuerzel = $r->rechnungs_kuerzel_ermitteln('Partner', $_SESSION['partner_id'], $datum);
			$rechnungsnummer = $r->rechnungs_kuerzel.' '.$letzte_aussteller_rnr.'-'.$jahr;
			
			
			$letzte_empfaenger_rnr = $r->letzte_empfaenger_eingangs_nr($empf_id, $empf_typ,$jahr, 'Rechnung')+1;
		
			
			
			$netto_betrag = $brutto_betrag/1.19;
			$gk = new geldkonto_info();
			$gk->geld_konto_ermitteln('Partner', $_SESSION['partner_id']);
			$faellig_am = tage_plus($datum, 10);
			$db_abfrage = "INSERT INTO RECHNUNGEN VALUES (NULL, '$letzte_belegnr', '$rechnungsnummer', '$letzte_aussteller_rnr', '$letzte_empfaenger_rnr', 'Rechnung', '$datum','$datum', '$netto_betrag','$brutto_betrag','0.00', 'Partner', '$_SESSION[partner_id]','$empf_typ', '$empf_id','1', '1', '0', '0', '0', '0', '0', '$faellig_am', '0000-00-00', '$kurztext_neu', '$gk->geldkonto_id')";
			$resultat = mysql_query($db_abfrage) or
            die(mysql_error());
            /*Protokollieren*/
		$last_dat = mysql_insert_id();
		protokollieren('RECHNUNGEN', $last_dat, '0');
           
/*Positionen erfassen*/
            $art_nr = "VG-".$einheit_id;
            $r->artikel_leistung_mit_artikelnr_speichern($_SESSION['partner_id'], "Verwaltergeb�hr $e->einheit_kurzname", '14.99', "$art_nr", '0', 'Stk', '19', '0');
			$letzte_rech_pos_id = $r->get_last_rechnung_pos_id()+1;
			$p_id = $_SESSION['partner_id'];
			$db_abfrage = "INSERT INTO RECHNUNGEN_POSITIONEN VALUES (NULL, '$letzte_rech_pos_id', '1', '$letzte_belegnr', '$letzte_belegnr','$p_id', '$art_nr', '1','$netto_betrag','19', '0','0', '$netto_betrag','1')";
			$resultat = mysql_query($db_abfrage) or
           die(mysql_error());	           
/*Protokollieren*/
		$last_dat = mysql_insert_id();
		protokollieren('RECHNUNGEN_POSITIONEN', $last_dat, '0');
           
/*Kontieren*/
		$kontierung_id = $r->get_last_kontierung_id()+1;          
		
		
		$db_abfrage = "INSERT INTO KONTIERUNG_POSITIONEN VALUES (NULL, '$kontierung_id','$letzte_belegnr', '1','1', '$netto_betrag', '$netto_betrag', '19', '0', '0', '$kostenkonto', '$empf_typ', '$empf_id', '$datum', '$jahr', '0', '1')";	
		$resultat = mysql_query($db_abfrage) or
         die(mysql_error());	 	
		
		/*Protokollieren*/
		$last_dat = mysql_insert_id();
		protokollieren('KONTIERUNG_POSITIONEN', $last_dat, '0');
		
		
		/*In SEPA �BERWEISUNGEN bei H�ckchen*/
		if(isset($_POST['sepa'])){
		$r->rechnung_grunddaten_holen($letzte_belegnr);
		$vzweck = "$r->rechnungs_aussteller_name, Rg. $r->rechnungsnummer ".bereinige_string($kurztext);	
			
		$sep = new sepa();
		if($sep->sepa_ueberweisung_speichern($_SESSION['geldkonto_id'], $gk->geldkonto_id, $vzweck, 'Verwaltergebuehr', $empf_typ, $empf_id, $kostenkonto, $brutto_betrag)==false){
    		fehlermeldung_ausgeben("�BERWEISUNG KONNTE NICHT GESPEICHERT WERDEN!");
    	}
		}else{
			fehlermeldung_ausgeben("KEINE SEPA-�BERWEISUNG GEW�NSCHT!");
		}
		}//END FOR
		#print_req($_POST);
		
		break;
		
	case "rgg_ob":
	#print_req();
	if(!empty($_POST['kostenkonto'])){
		$kostenkonto = $_POST['kostenkonto'];
		}else{
			die(fehlermeldung_ausgeben("Kostenkonto w�hlen"));
		}
		
		$empf_typ = $_POST['empf_typ'];
		$empf_id = $_POST['empf_id'];
		$kurztext = $_POST['kurztext'];
		$typ_arr = $_POST['typ'];
		$brutto_arr = $_POST['brutto'];
		$mengen_arr = $_POST['mengen'];
		
		$o = new objekt();
		$o->get_objekt_infos($_SESSION['objekt_id']);
	#echo $o->objekt_kurzname;
		
		$kurztext_neu = "$kurztext\n<b>Objektname: $o->objekt_kurzname</b>";
			$r = new rechnung;
			$letzte_belegnr = $r->letzte_beleg_nr()+1;

			$jahr = date("Y");
			$datum = date("Y-m-d");
			$letzte_aussteller_rnr =	$r->letzte_aussteller_ausgangs_nr($_SESSION['partner_id'], 'Partner', $jahr, 'Rechnung')+1;
			$letzte_aussteller_rnr = sprintf('%03d',$letzte_aussteller_rnr);
			$r->rechnungs_kuerzel = $r->rechnungs_kuerzel_ermitteln('Partner', $_SESSION['partner_id'], $datum);
			$rechnungsnummer = $r->rechnungs_kuerzel.' '.$letzte_aussteller_rnr.'-'.$jahr;
			
			
			$letzte_empfaenger_rnr = $r->letzte_empfaenger_eingangs_nr($empf_id, $empf_typ,$jahr, 'Rechnung')+1;
		
			
			
			$netto_betrag = 0.00;
			$gk = new geldkonto_info();
			$gk->geld_konto_ermitteln('Partner', $_SESSION['partner_id']);
			$faellig_am = tage_plus($datum, 10);
			$db_abfrage = "INSERT INTO RECHNUNGEN VALUES (NULL, '$letzte_belegnr', '$rechnungsnummer', '$letzte_aussteller_rnr', '$letzte_empfaenger_rnr', 'Rechnung', '$datum','$datum', '$netto_betrag','0.00','0.00', 'Partner', '$_SESSION[partner_id]','$empf_typ', '$empf_id','1', '1', '0', '0', '0', '0', '0', '$faellig_am', '0000-00-00', '$kurztext_neu', '$gk->geldkonto_id')";
			$resultat = mysql_query($db_abfrage) or
            die(mysql_error());
            /*Protokollieren*/
		$last_dat = mysql_insert_id();
		protokollieren('RECHNUNGEN', $last_dat, '0');
		
		$pos=0;
		$g_sum = 0;
		for($a=0;$a<count($typ_arr);$a++){
		$pos++;
		$brutto_bet = $brutto_arr[$a];
		
		$netto_betrag = nummer_komma2punkt($brutto_bet)/1.19;
		$typ_bez = $typ_arr[$a];
		$menge = $mengen_arr[$a];
		$g_sum += nummer_komma2punkt($brutto_bet) * $menge;
		$g_netto = $netto_betrag*$menge;
		/*Positionen erfassen*/
            $art_nr = "$o->objekt_kurzname-$typ_bez";
            $r->artikel_leistung_mit_artikelnr_speichern($_SESSION['partner_id'], "Verwaltergeb�hr $typ_bez", $brutto_bet, "$art_nr", '0', 'Stk', '19', '0');
			$letzte_rech_pos_id = $r->get_last_rechnung_pos_id()+1;
			$p_id = $_SESSION['partner_id'];
			$db_abfrage = "INSERT INTO RECHNUNGEN_POSITIONEN VALUES (NULL, '$letzte_rech_pos_id', '$pos', '$letzte_belegnr', '$letzte_belegnr','$p_id', '$art_nr', $menge,'$netto_betrag','19', '0','0', '$g_netto','1')";
			$resultat = mysql_query($db_abfrage) or
           die(mysql_error());	           
/*Protokollieren*/
		$last_dat = mysql_insert_id();
		protokollieren('RECHNUNGEN_POSITIONEN', $last_dat, '0');
           
/*Kontieren*/
		$kontierung_id = $r->get_last_kontierung_id()+1; 
		$db_abfrage = "INSERT INTO KONTIERUNG_POSITIONEN VALUES (NULL, '$kontierung_id','$letzte_belegnr', '$pos','$menge', '$netto_betrag', '$g_netto', '19', '0', '0', '$kostenkonto', 'Objekt', '$_SESSION[objekt_id]', '$datum', '$jahr', '0', '1')";	
		$resultat = mysql_query($db_abfrage) or
         die(mysql_error());	 	
		
		/*Protokollieren*/
		$last_dat = mysql_insert_id();
		protokollieren('KONTIERUNG_POSITIONEN', $last_dat, '0');
		
		}
		
		/*In SEPA �BERWEISUNGEN bei H�ckchen*/
		if(isset($_POST['sepa'])){
		$r->rechnung_grunddaten_holen($letzte_belegnr);
		$vzweck = "$r->rechnungs_aussteller_name, Rg. $r->rechnungsnummer ".bereinige_string($kurztext_neu);	
		$sep = new sepa();
		if($sep->sepa_ueberweisung_speichern($_SESSION['geldkonto_id'], $gk->geldkonto_id, $vzweck, 'Verwaltergebuehr', $empf_typ, $empf_id, $kostenkonto, $g_sum)==false){
    		fehlermeldung_ausgeben("�BERWEISUNG KONNTE NICHT GESPEICHERT WERDEN!");
    	}
		}else{
			fehlermeldung_ausgeben("KEINE SEPA-�BERWEISUNG GEW�NSCHT!");
		}
		
		
	break;
	
	case "rg_aus_beleg":
	if(!isset($_SESSION['partner_id'])){
		die(fehlermeldung_ausgeben("Partner (Rechnungssteller) w�hlen!"));
	}
		echo "<hr>";
		$link_add = "<a href=\"?daten=rechnungen&option=beleg2pool\">Beleg hinzuf�gen</a>";
		echo $link_add;
		echo "<hr>";
		$r = new rechnungen();
		$r->liste_beleg2rg();
		

	break;
	
	case "beleg2pool":
	$r = new rechnungen();
	$r->form_beleg2pool();
	break;

	case "beleg_sent":
	$r = new rechnungen();
	if(!empty($_POST['beleg_nr']) && !empty($_POST['empf_p_id'])){
	$r->beleg2rg_db($_POST['empf_p_id'],$_POST['beleg_nr']);	
	}
	break;
	
	case "neue_rg":
	if(!empty($_REQUEST['belegnr']) && !empty($_REQUEST['empf_p_id']) && !empty($_SESSION['partner_id'])){
	$r = new rechnungen();
	$r->rechnung_aus_beleg($_SESSION['partner_id'], $_REQUEST['belegnr'], $_REQUEST['empf_p_id']);	
	}else{
		fehlermeldung_ausgeben("FEHLER xo");
	}
	print_req();
	break;
	
	case "pdf_druckpool":
	if(!isset($_SESSION['partner_id'])){
		fehlermeldung_ausgeben("Partner f�r das RA-Buch w�hlen!!!");
	die();
	}
	$re = new rechnungen();
	if(!isset($_REQUEST['monat']) or empty($_REQUEST['monat'])){
	$monat = date("m");
	}else{
		$monat = $_REQUEST['monat'];
	}

	if(!isset($_REQUEST['jahr']) or empty($_REQUEST['jahr'])){
		$jahr = date("Y");
	}else{
		$jahr = $_REQUEST['jahr'];
	}
	
	
	
	$arr = $re->ausgangsrechnungen_arr_sort('Partner', $_SESSION['partner_id'], $monat, $jahr, 'Rechnung', 'ASC');
	#echo '<pre>';
	#print_r($arr);
	if(!is_array($arr)){
		fehlermeldung_ausgeben("Keine Ausgangsrechnungen $monat / $jahr");
	}else{
		$anz = count($arr);
		$f = new formular;
		$f->erstelle_formular("Sammeldruck als PDF", null);
		echo "<table>";
		echo "<tr><td>";
		$f->check_box_js_alle('uebernahme_alle[]', 'ue', '', 'Alle', '', '', 'uebernahme');
		echo "</td><td colspan=\"30\">RECHNUNGEN $monat/$jahr</td></tr>";
		$spalte = 0;
		echo "<tr>";
		for($a=0;$a<$anz;$a++){
			$spalte++;
			$id=$arr[$a]['BELEG_NR'];
			$rnr=$arr[$a]['RECHNUNGSNUMMER'];
			$a_nr = $arr[$a]['AUSTELLER_AUSGANGS_RNR'];
			echo "<td>";
			$f->check_box_js('uebernahme[]', $id, $rnr, '', 'checked');
			echo "</td>";
				if($spalte==30){
				echo "</tr><tr>";
				$spalte=0;
				}
				
			#f->check_box_js($name, $wert, $label, $js, $checked){
					
			
			
		}
		echo "</tr>";
		echo "</table>";
		$f->hidden_feld('option', 'rg2pdf');
		$f->send_button('RG2PDF', 'PDF-Erstellen');
		$f->ende_formular();
	}
	
	break;
	
	
	case "sepa_druckpool":
		if(!isset($_SESSION['partner_id'])){
			fehlermeldung_ausgeben("Partner f�r das RE-Buch w�hlen!!!");
			die();
		}
		
		if(!isset($_SESSION['geldkonto_id'])){
			fehlermeldung_ausgeben("Abgangsgeldkonto f�r SEPA Zahlungen w�hlen!!!");
			die();
		}
		
		$re = new rechnungen();
		if(!isset($_REQUEST['monat']) or empty($_REQUEST['monat'])){
			$monat = date("m");
		}else{
			$monat = $_REQUEST['monat'];
		}
	
		if(!isset($_REQUEST['jahr']) or empty($_REQUEST['jahr'])){
			$jahr = date("Y");
		}else{
			$jahr = $_REQUEST['jahr'];
		}
	
	
	
		$arr = $re->eingangsrechnungen_arr_sort('Partner', $_SESSION['partner_id'], $monat, $jahr, 'Rechnung', 'ASC');
		#echo '<pre>';
		#print_r($arr);
		if(!is_array($arr)){
			fehlermeldung_ausgeben("Keine Eingangsrechnungen $monat / $jahr");
		}else{
			$anz = count($arr);
			$f = new formular;
			$f->erstelle_formular("Rg zahlen �ber SEPA $monat/$jahr", null);
			echo "<table>";
			echo "<tr><td>";
			$f->check_box_js_alle('uebernahme_alle[]', 'ue', '', 'Alle', '', '', 'uebernahme');
			$vormonat = sprintf('%02d',$monat-1);
			$nachmonat = sprintf('%02d',$monat+1);
			$link_vormonat = "<a href=\"?daten=rechnungen&option=sepa_druckpool&monat=$vormonat\">Rechnungen $vormonat/$jahr</a>";
			$link_nachmonat = "<a href=\"?daten=rechnungen&option=sepa_druckpool&monat=$nachmonat\">Rechnungen $nachmonat/$jahr</a>";
			echo "</td><td colspan=\"30\">$link_vormonat<br><b>RECHNUNGEN $monat/$jahr</b><br>$link_nachmonat</td></tr>";
			$spalte = 0;
			echo "<tr>";
			for($a=0;$a<$anz;$a++){
				$spalte++;
				$id=$arr[$a]['BELEG_NR'];
				$rnr=$arr[$a]['RECHNUNGSNUMMER'];
				$e_nr = $arr[$a]['WE_NR'];
				echo "<td>";
				$f->check_box_js('uebernahme[]', $id, "$e_nr:$rnr", '', 'checked');
				echo "</td>";
				if($spalte==30){
					echo "</tr><tr>";
					$spalte=0;
				}
	
				#f->check_box_js($name, $wert, $label, $js, $checked){
					
					
					
			}
			echo "</tr>";
					echo "</table>";
					$f->hidden_feld('option', 'rg2sep');
					$f->send_button('RG2SEP', 'Rechnungen in SEPA-Sammler �bernehmen');
					$f->ende_formular();
		}
	
		break;
		
		
		case "rg2sep":
		
			if(!is_array($_POST['uebernahme'])){
				fehlermeldung_ausgeben("rechnungen w�hlen!");
				die();
			}else{
				#echo '<pre>';
				#print_r($_POST);
				$anz = count($_POST['uebernahme']);
				
				for($a=0;$a<$anz;$a++){
					
					$belegnr=$_POST['uebernahme'][$a];
					$re = new rechnungen;
					$re->rechnung_grunddaten_holen($belegnr);
					/*$re->empfangs_geld_konto;
					$re->kurzbeschreibung;
					$re->rechnungs_skontobetrag;
					$re->rechnungs_aussteller_name;
					$re->rechnungsnummer;*/
					$sep = new sepa;
					if (preg_match("/$re->rechnungs_aussteller_name/i", "$re->kurzbeschreibung")) {
					$vzweck = "$re->rechnungs_aussteller_name, Rg. $re->rechnungsnummer, $re->kurzbeschreibung";
					}else{
						$vzweck = "Rg. $re->rechnungsnummer, $re->kurzbeschreibung";
					}
					 
					$sep->sepa_ueberweisung_speichern($_SESSION['geldkonto_id'],$re->empfangs_geld_konto, "$vzweck", 'RECHNUNGP', $re->rechnungs_aussteller_typ, $re->rechnungs_aussteller_id, '0', $re->rechnungs_skontobetrag);
					#print_r($re);
				}
		
		
		
		
					
			weiterleiten("?daten=sepa&option=sammler_anzeigen");		
			}
			break;
		
	
	
	
	case "rg2pdf":
	
	if(!is_array($_POST['uebernahme'])){
		fehlermeldung_ausgeben("rechnungen w�hlen!");
		die();
	}else{
		#echo '<pre>';
		#print_r($_POST);
		$anz = count($_POST['uebernahme']);
		/*ezPDF-Klasse laden*/
		include_once('pdfclass/class.ezpdf.php');
		/*Eigene PDF-Klasse laden*/
		include_once('classes/class_bpdf.php');
		/*Neues PDF-Objekt erstellen*/
		$pdf = new Cezpdf('a4', 'portrait');
		/*Neue Instanz von b_pdf*/
		$bpdf = new b_pdf;
		/*Header und Footer des Rechnungsaustellers in alle PDF-Seiten laden*/
		#die("hallo $this->rechnung_aussteller_partner_id");
		$bpdf->b_header($pdf, 'Partner', $_SESSION['partner_id'], 'portrait', 'pdfclass/fonts/Helvetica.afm', 6);
		
		$pdf->ezStopPageNumbers();
		
		
		for($a=0;$a<$anz;$a++){
			$i = $pdf->ezStartPageNumbers(545,715,6,'','Seite {PAGENUM} von {TOTALPAGENUM}',1);
			$id=$_POST['uebernahme'][$a];
			$re = new rechnungen();
			$re->rechnung_2_pdf($pdf,$id);
			$pdf->ezStopPageNumbers(1,1,$i);
			$pdf->ezNewPage();
		}
		



		ob_clean();
		/*PDF-Ausgabe*/
		$pdf->ezStream();
			
			
	}
	break;
	
	
}

?>
