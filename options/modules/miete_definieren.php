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
 * @filesource   $HeadURL: http://192.168.2.52/svn/berlussimo_1/tags/02.11.2010 - Downloadversion 0.27/options/modules/miete_definieren.php $
 * @version      $Revision: 6 $
 * @modifiedby   $LastChangedBy: sivac $
 * @lastmodified $Date: 2010-09-21 10:34:50 +0200 (Di, 21 Sep 2010) $
 * 
 */
 
include_once("includes/allgemeine_funktionen.php");

/*Überprüfen ob Benutzer Zugriff auf das Modul hat*/
if(!check_user_mod($_SESSION['benutzer_id'], 'miete_definieren')){
	echo '<script type="text/javascript">';
	echo "alert('Keine Berechtigung')";
	echo '</script>';	
	die();
}

include_once("includes/formular_funktionen.php");
include_once("classes/mietkonto_class.php");
include_once("classes/class_mietentwicklung.php");
include_once("classes/berlussimo_class.php");
include_once("options/links/links.miete_definieren.php");
include_once("classes/class_mietvertrag.php");

$daten = $_REQUEST["daten"];
if(isset($_REQUEST['option']) && !empty($_REQUEST['option'])){
$schritt = $_REQUEST["option"];
}else{
	$schritt = 'default';
}

/*Mietdefinition beginn (in MIETENTWICKLUNG) 
 * Datum  auf 1.3.2008 oder 1.1.2007 setzen*/
#$me = new mietentwicklung;
#$me->set_datum_block_e();
#$me->set_datum_andere(); 

#$me->mietdefinition_zu_details();

/*Mieterinformationen über die Buchungsformulare anzeigen*/
if(isset($_REQUEST['mietvertrag_id']) && !empty($_REQUEST['mietvertrag_id'])) {
$mieter_info = new mietkonto;
$mieter_info->erstelle_formular("Mieterinformationen", NULL);
$mieter_info->mieter_informationen_anzeigen($_REQUEST['mietvertrag_id']);
$mieter_info->ende_formular();
}


switch($schritt) {

    
    
    case "miethoehe":
	$form = new mietkonto;
    $form->erstelle_formular("Miethöhe definieren", NULL);
	if(isset($_REQUEST['mietvertrag_id']) && !empty($_REQUEST['mietvertrag_id'])){
	$mietvertrag_id = $_REQUEST['mietvertrag_id'];	
	$me = new mietentwicklung;
	$jahr = date("Y");
	$monat = date("m");
	$me->get_mietentwicklung_infos($mietvertrag_id, $jahr, $monat);
	#echo "<pre>";
	#print_r($me);
	#echo "</pre>";
	#$mv = new mietvertraege();
	#$mv->get_mietvertrag_infos_aktuell($mietvertrag_id);
	#$einheit_id = $mv->einheit_id+1;
	#$mv1 = new mietvertrag();
	#$mv1->get_mietvertrag_infos_aktuell($einheit_id);
	
	
	#print_r($mv1);
	#$link_nach = "";
	
	
	if(count($me->kostenkategorien) > 0){
	$form->erstelle_formular("Aktuelle Mietdefinition", NULL);
	$me->me_dat_neu_form($mietvertrag_id);
	$me->mietentwicklung_anzeigen($mietvertrag_id);
	}//end if
	else{
	/*$form->hidden_feld('mietvertrag_id', $mietvertrag_id);
		$form->text_feld_id('Miete kalt', "kostenkategorie[][Miete kalt]", '', '10', 'Miete kalt');	
$form->text_feld_id('Nebenkosten Vorauszahlung', "kostenkategorie[][Nebenkosten Vorauszahlung]", '', '10', 'Nebenkosten Vorauszahlung');
$form->text_feld_id('Heizkosten Vorauszahlung', "kostenkategorie[][Heizkosten Vorauszahlung]", '', '10', 'Heizkosten Vorauszahlung');
	$form->hidden_feld('option', 'miete_eingeben');
	$form->send_button("btn_miete_senden", 'Miete eingeben');
	*/
	$me->me_dat_neu_form($mietvertrag_id);
	}//end else
	$form->ende_formular();
	}else{
	//fals keine MV_ID eingegeben wurde, weiterleiten
	warnung_ausgeben("Fehler : Bitte eine Einheit auswählen!");
		weiterleiten("?daten=miete_definieren");
	}
    $form->ende_formular();
    break;

	default:
	$form = new mietkonto;
    $form->erstelle_formular("Objekte & Einheiten", NULL);
	if(isset($_REQUEST['objekt_id'])){
 		$_SESSION['objekt_id'] = "".$_REQUEST['objekt_id']."";
 	}
 	
 	if(!isset($_SESSION['objekt_id'])){
 	echo "<div class=\"info_feld_oben\">Objekt auswählen</div>";
 		objekt_auswahl();
 	}
 	
 	if(isset($_SESSION['objekt_id'])){
 	echo "<div class=\"info_feld_oben\">Einheit auswählen</div>";
 		objekt_auswahl();
 		einheiten_liste();
 	}


 	$form->ende_formular();
 	break;
 	
 	case "aendern":
	$form = new mietkonto;
    $form->erstelle_formular("Aktuelle Mietdefinition ändern", NULL);
	$me = new mietentwicklung;
	$me->me_dat_aendern_form($_REQUEST['aendern_dat']);
	$me->mietentwicklung_anzeigen($_REQUEST['mietvertrag_id']);
	$form->ende_formular();
	break;
	
	case "andern_dat_speichern":
	$form = new mietkonto;
    $form->erstelle_formular("Aktuelle Mietdefinition wird gespeichert", NULL);
	$me = new mietentwicklung;
	$me->me_dat_aendern();
	weiterleiten_in_sec("?daten=miete_definieren&option=miethoehe&mietvertrag_id=$_POST[mv_id]", 1);
	$form->ende_formular();
	break;
	
	case "me_neu_speichern":
	$form = new mietkonto;
    $form->erstelle_formular("Neue Mietdefinition wird gespeichert", NULL);
	$me = new mietentwicklung;
	#print_r($_POST);
	$_SESSION['a_datum'] = $_POST['anfang'];
	$_SESSION['e_datum'] = $_POST['ende'];
	$_SESSION['me_kostenkat'] =$_POST['kostenkategorie'];
	#die();
	$me->me_dat_neu_speichern();
	weiterleiten_in_sec("?daten=miete_definieren&option=miethoehe&mietvertrag_id=$_POST[mv_id]", 1);
	$form->ende_formular();
	break;


	case "me_loeschen":
	$me = new mietentwicklung;
	$me_dat=$_REQUEST['me_dat'];
	if($me_dat){
	$me->me_dat_loeschen($me_dat);
	$mv_id = $_REQUEST['mietvertrag_id'];
	weiterleiten_in_sec("?daten=miete_definieren&option=miethoehe&mietvertrag_id=$mv_id", 1);
	}
	break;
		
 	
 	
case "miete_eingeben":
$form = new mietkonto;
$form->erstelle_formular("Miethöhe eingeben", NULL);

for($a=0;$a<count($_POST[kostenkategorie]);$a++){
$uarr = $_POST['kostenkategorie'][$a];
	foreach ($uarr as $key=>$value){
	#echo $key.$value;
		if(!empty($value)){
		$form->mietentwicklung_speichern($_POST['mietvertrag_id'], $key, $value, $_POST[anfangs_datum], '0000-00-00');
		}	
	}

}
	hinweis_ausgeben("Miete wurde definiert, Sie werden gleich zur Übersicht weitergeleit!");
	$mv_info = new mietvertrag;
	$einheit_id=$mv_info->get_einheit_id_von_mietvertrag($_POST['mietvertrag_id']);
	weiterleiten_in_sec("?daten=uebersicht&anzeigen=einheit&einheit_id=$einheit_id", "2");
	$form->ende_formular();
	break;


case "mieterlisten_kostenkat":
if(!empty($_REQUEST['kostenkat'])){
$me = new mietentwicklung;
$me->mieterlisten_kostenkat($_REQUEST['kostenkat']);	
}else{
	echo "Kostenkat eingeben";
}
break;

}//end switch


function objekt_auswahl(){
	echo "<div class=\"objekt_auswahl\">";
	$mieten = new mietkonto;
	$mieten->erstelle_formular("Objekt auswählen...", NULL);
	
	
	if(isset($_SESSION['objekt_id'])){
 	$objekt_kurzname = new objekt;
 	$objekt_kurzname->get_objekt_name($_SESSION['objekt_id']);
 	echo "<p>&nbsp;<b>Ausgewähltes Objekt</b> -> $objekt_kurzname->objekt_name ->";
 	echo "<div class=\"info_feld_oben\">Ausgewähltes Objekt ".$objekt_kurzname->objekt_name."<br><b>Einheit auswählen</b><br>WEISS: keine Zahlung im aktuellen Monat.<br>GRAU: Zahlungen wurden gebucht.</div>";
 	}
	
	$objekte = new objekt;
	$objekte_arr = $objekte->liste_aller_objekte();
	
	$anzahl_objekte = count($objekte_arr);
	#print_r($objekte_arr);
	$c=0;
	for($i=0;$i<$anzahl_objekte;$i++){
	echo "<a class=\"objekt_auswahl_buchung\" href=\"?daten=miete_definieren&objekt_id=".$objekte_arr[$i]['OBJEKT_ID']."\">".$objekte_arr[$i]['OBJEKT_KURZNAME']."</a>&nbsp;";	
	$c++;
	if($c==10){
	echo "<br>";
	$c=0;
	}
	}
$mieten->ende_formular();
echo "</div>";
}

function einheiten_liste(){
$mieten = new mietkonto;
echo "<div class=\"einheit_auswahl\">";
$mieten->erstelle_formular("Vermietete Einheit auswählen...", NULL);

/*Liste der Einheiten falls Objekt ausgewählt wurde*/
if(isset($_SESSION['objekt_id'])){
$objekt_id = $_SESSION['objekt_id'];
$mein_objekt = new objekt;
$liste_haeuser = $mein_objekt->haeuser_objekt_in_arr($objekt_id);

	for($i=0;$i<count($liste_haeuser);$i++){
$result = mysql_query ("SELECT * FROM EINHEIT WHERE EINHEIT_AKTUELL='1' && HAUS_ID='".$liste_haeuser[$i]['HAUS_ID']."' ORDER BY EINHEIT_KURZNAME ASC");
	while ($row = mysql_fetch_assoc($result)) $einheiten_array[] = $row;
	}
}else{
/*Liste aller Einheiten da kein Objekt ausgewählt wurde*/
$meine_einheiten = new einheit;
$einheiten_array = $meine_einheiten->liste_aller_einheiten();
}	
// Beispiel für ein Array $sx mit den Spalten $sx['dat'], $sx['name'], $sx['id'].

$einheiten_array = array_sortByIndex($einheiten_array,'EINHEIT_KURZNAME');
#echo "<pre>";
#print_r($einheiten_array);
#echo "</pre>";
$counter = 0;
$spaltencounter = 0;
echo "<table>";
echo "<tr><td valign=\"top\">";
$einheit_info = new einheit;
#$mietkonto2 = new mietkonto;
#$zeitraum = new zeitraum;
for($i=0;$i<count($einheiten_array);$i++){
	
	
	
	
	$einheit_info->get_mietvertrag_id("".$einheiten_array[$i]['EINHEIT_ID']."");
	$einheit_vermietet = $einheit_info->get_einheit_status("".$einheiten_array[$i]['EINHEIT_ID']."");
	if($einheit_vermietet){
	#if(isset($einheit_info->mietvertrag_id)){
	
		
		echo "<a href=\"?daten=miete_definieren&option=miethoehe&mietvertrag_id=".$einheit_info->mietvertrag_id."\" class=\"nicht_gebucht_links\">".$einheiten_array[$i]['EINHEIT_KURZNAME']."</a>&nbsp;";

	echo "<br>"; // Nach jeder Einheit Neuzeile
	$m = new mietvertrag; //class mietvertrag aus berlussimo_class.php;
	$m1 = new mietvertraege; //class mietvertraege NEUE KLASSE;
	$mv_ids_arr = $m->get_personen_ids_mietvertrag($einheit_info->mietvertrag_id);
	#$m1->mv_personen_anzeigen($mv_ids_arr); //$mv_ids_arr Array mit personan Ids
	$mieternamen_str = $m1->mv_personen_als_string($mv_ids_arr);
	echo $mieternamen_str.'<br>';
	#echo "<br>"; // Nach jeder Einheit Neuzeile
	
	
	
$counter++;
}
if($counter==10){
	echo "</td><td valign=\"top\">";
$counter = 0;
$spaltencounter++;
}

if($spaltencounter==4){
	echo "</td></tr>";
	echo "<tr><td colspan=\"$spaltencounter\"><hr></td></tr>";
	echo "<tr><td valign=\"top\">";
	
$spaltencounter = 0;
}

}
echo "</td></tr></table>";
#echo "<pre>";
		#print_r($einheiten_array);	
#echo "</pre>";
$mieten->ende_formular();
echo "</div>";	

} 
?>
