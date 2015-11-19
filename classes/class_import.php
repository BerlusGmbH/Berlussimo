<?php

class import{
	
	

function form_import_gfad($file=null){
	
	if(!isset($_SESSION['objekt_id'])){
		fehlermeldung_ausgeben("Objekt wählen");
		$bg = new berlussimo_global();
		$bg->objekt_auswahl_liste('?daten=objekte_raus&objekte_raus=import');
	}else{
	if(!file_exists(HAUPT_PATH.'/'.BERLUS_PATH.'/'.$file)){
		$ff = HAUPT_PATH.'/'.BERLUS_PATH.'/'.$file;
		die(fehlermeldung_ausgeben("Datei: <u>$ff</u> existiert nicht"));
	}
	$arr = file(HAUPT_PATH.'/'.BERLUS_PATH.'/'.$file);
	$o = new objekt();
	$o->get_objekt_infos($_SESSION['objekt_id']);
	echo '<pre>';
	print_r($arr);
	
	$anz = count($arr);
	#$this->akt_zeile = 1;
	$z = 0;
	$z_et = 201;
	$z_mi = 201;
	$z_ga = 601;
	for($a=1;$a<$anz;$a++){
	$zeile = explode(';', $arr[$a]);
	$we = $zeile[2];
	$lage = $zeile[14];
	$datum_von = date_german2mysql(substr($zeile[15],0,10));
	$datum_bis = date_german2mysql(substr($zeile[16],0,10));
	$qm = nummer_punkt2komma(nummer_komma2punkt($zeile[42]));
	$typ = $zeile[1];
	$anrede = $zeile[3];
	$email = $zeile[24];
	$new_arr[$we]['EINHEIT_BEZ']= $we;
	$tel = $zeile[9];
	$strasse = $zeile[7];
	$ort_plz = $zeile[8];
	
	$wm = $zeile[17];
	$mwst = $zeile[18]; // J/N
	
	$km = $zeile[17];//miete vor 3 Jahren
	$km_3 = $zeile[17];//miete vor 3 Jahren
	$nk = $zeile[17];//miete vor 3 Jahren
	$hk = $zeile[17];//miete vor 3 Jahren
	
	 
	$kto = $zeile[37];
	$blz = $zeile[35];
	$sep = new sepa();
	$sep->get_iban_bic($kto, $blz);
		
	
	
	if($typ=='E'){
	$new_arr[$we]['ET']['BEZ_NEW'] = "WEG-$o->objekt_kurzname-$z_et";
	$new_arr[$we]['ET']['TYP']= 'Wohneigentum';
	$z_et++;
	$new_arr[$we]['TYP'][] = "ET";
	$et1 =$zeile[4];
	$et2 =$zeile[6];
	
	
	
	$new_arr[$we]['ET']['NAMEN'][] = "$et1";	
		if(!empty($et2)){
		$new_arr[$we]['ET']['NAMEN'][] = $et2;
		$zustell_ans = "$anrede\n$et1 $et2\n $strasse $ort_plz";
		}else{
		$zustell_ans = "$anrede\n$et1\n $strasse $ort_plz";	
		}
		$new_arr[$we]['ET']['ZANSCHRIFT']= $zustell_ans;
		$new_arr[$we]['ET']['VON']= $datum_von;
		$new_arr[$we]['ET']['BIS']= $datum_bis;
		$new_arr[$we]['ET']['LAGE']= $lage;
		$new_arr[$we]['ET']['QM']= $qm;
		$new_arr[$we]['ET']['EMAIL']= $email;
		$new_arr[$we]['ET']['TEL']= $tel;
		$new_arr[$we]['ET']['KTO']= $kto;
		$new_arr[$we]['ET']['BLZ']= $blz;
		$new_arr[$we]['ET']['IBAN']= $sep->IBAN1;
		$new_arr[$we]['ET']['BIC']= $sep->BIC;
		
		
		if($anrede=='Herr' or $anrede=='Herrn'){
		$new_arr[$we]['ET']['GES'][] = 'männlich';
		}
		if($anrede=='Herren'){
		$new_arr[$we]['ET']['GES'][] = 'männlich';	
		$new_arr[$we]['ET']['GES'][] = 'männlich';
		}	
		
		if($anrede=='Frau'){
		$new_arr[$we]['ET']['GES'][] = 'weiblich';	
		}
		if($anrede=='Herr und Frau'){
		$new_arr[$we]['ET']['GES'][] = 'männlich';	
		$new_arr[$we]['ET']['GES'][] = 'weiblich';	
		}
		if(empty($anrede)){
		$anz_m = count($new_arr[$we]['ET']['NAMEN']);
		$new_arr[$we]['ET']['GES'][] = 'unbekannt';	
		$new_arr[$we]['ET']['GES'][] = 'unbekannt';	
		}
	}
	
	if($typ=='M'){
	$new_arr[$we]['MIETER']['BEZ_NEW'] = "$o->objekt_kurzname-$z_mi";
	$new_arr[$we]['MIETER']['TYP']= 'Wohnraum';
	$z_mi++;
	$new_arr[$we]['TYP'][] = "MIETER";
	$mi1 =$zeile[4];
	$mi2 =$zeile[6];
		$new_arr[$we]['MIETER']['NAMEN'][] = "$mi1";	
		if(!empty($mi2)){
		$new_arr[$we]['MIETER']['NAMEN'][] = $mi2;
		$zustell_ans = "$anrede\n$mi1 $mi2\n $strasse $ort_plz";
		}else{
		$zustell_ans = "$anrede\n$mi1\n $strasse $ort_plz";	
		}
		$new_arr[$we]['MIETER']['ZANSCHRIFT']= $zustell_ans;
		
		$new_arr[$we]['MIETER']['VON']= $datum_von;
		$new_arr[$we]['MIETER']['BIS']= $datum_bis;
		$new_arr[$we]['MIETER']['LAGE']= $lage;
		$new_arr[$we]['MIETER']['QM']= $qm;
		$new_arr[$we]['MIETER']['EMAIL']= $email;
		$new_arr[$we]['MIETER']['TEL']= $tel;
		$new_arr[$we]['MIETER']['KTO']= $kto;
		$new_arr[$we]['MIETER']['BLZ']= $blz;
		$new_arr[$we]['MIETER']['IBAN']= $sep->IBAN1;
		$new_arr[$we]['MIETER']['BIC']= $sep->BIC;
			
		
		if($anrede=='Herr' or $anrede=='Herrn'){
		$new_arr[$we]['MIETER']['GES'][] = 'männlich';	
		}
		if($anrede=='Herren'){
		$new_arr[$we]['MIETER']['GES'][] = 'männlich';	
		$new_arr[$we]['MIETER']['GES'][] = 'männlich';
		}
		if($anrede=='Frau'){
		$new_arr[$we]['MIETER']['GES'][] = 'weiblich';	
		}
		if($anrede=='Herr und Frau'){
		$new_arr[$we]['MIETER']['GES'][] = 'männlich';	
		$new_arr[$we]['MIETER']['GES'][] = 'weiblich';	
		}
		if(empty($anrede)){
		$anz_m = count($new_arr[$we]['MIETER']['NAMEN']);
		$new_arr[$we]['MIETER']['GES'][] = 'unbekannt';	
		$new_arr[$we]['MIETER']['GES'][] = 'unbekannt';	
		}
	}
	
	if($typ=='S'){
	$new_arr[$we]['SMIETER']['BEZ_NEW'] = "$o->objekt_kurzname-$z_ga";
	$new_arr[$we]['SMIETER']['TYP']= 'Stellplatz';
	$z_ga++;
	$new_arr[$we]['TYP'][] = "SMIETER";
	$mi1 =$zeile[4];
	$mi2 =$zeile[6];
		$new_arr[$we]['SMIETER']['NAMEN'][] = "$mi1";	
		if(!empty($mi2)){
		$new_arr[$we]['SMIETER']['NAMEN'][] = $mi2;
		$zustell_ans = "$anrede\n$mi1 $mi2\n $strasse $ort_plz";
		}else{
		$zustell_ans = "$anrede\n$mi1\n $strasse $ort_plz";	
		}
		$new_arr[$we]['SMIETER']['ZANSCHRIFT']= $zustell_ans;
		
		$new_arr[$we]['SMIETER']['VON']= $datum_von;
		$new_arr[$we]['SMIETER']['BIS']= $datum_bis;
		$new_arr[$we]['SMIETER']['LAGE'] = $lage;
		$new_arr[$we]['SMIETER']['QM']= $qm;
		$new_arr[$we]['SMIETER']['EMAIL']= $email;
		$new_arr[$we]['SMIETER']['TEL']= $tel;
		$new_arr[$we]['SMIETER']['KTO']= $kto;
		$new_arr[$we]['SMIETER']['BLZ']= $blz;
		$new_arr[$we]['SMIETER']['IBAN']= $sep->IBAN1;
		$new_arr[$we]['SMIETER']['BIC']= $sep->BIC;

		if($anrede=='Herr' or $anrede=='Herrn'){
		$new_arr[$we]['SMIETER']['GES'][] = 'männlich';	
		}
		if($anrede=='Herren'){
		$new_arr[$we]['SMIETER']['GES'][] = 'männlich';	
		$new_arr[$we]['SMIETER']['GES'][] = 'männlich';
		}
		if($anrede=='Frau'){
		$new_arr[$we]['SMIETER']['GES'][] = 'weiblich';	
		}
		if($anrede=='Herr und Frau'){
		$new_arr[$we]['SMIETER']['GES'][] = 'männlich';	
		$new_arr[$we]['SMIETER']['GES'][] = 'weiblich';	
		}
		if(empty($anrede)){
		$anz_m = count($new_arr[$we]['SMIETER']['NAMEN']);
		$new_arr[$we]['SMIETER']['GES'][] = 'unbekannt';	
		$new_arr[$we]['SMIETER']['GES'][] = 'unbekannt';	
		}
	}
	
	
	#print_r($zeile);
	/*$new_arr[$z][$we]['QM'] = $qm;
	$new_arr[$z][$we]['LAGE'] = $lage;
	$new_arr[$z][$we]['TYP'] = $typ;
	*/
	$z++;
	}
	#print_r($zeile);
	
	
	#print_r($new_arr);
	
	$anz_a = count($new_arr);
	$keys = array_keys($new_arr);
	#print_r($keys);
	for($a=0;$a<$anz_a;$a++){
		$key = $keys[$a];
		$arr_n[] = $new_arr[$key];
	}
	#print_r($arr_n);
	
	if(!isset($_SESSION['akt_z'])){
		$_SESSION['akt_z'] = 0;
	}
	
	if(isset($_REQUEST['next'])){
	$_SESSION['akt_z']++;	
	}
	
	if(isset($_REQUEST['vor'])){
	$_SESSION['akt_z']--;	
	}
	
	if($_SESSION['akt_z']<0){
		$_SESSION['akt_z'] = 0;
	}
	
	if($_SESSION['akt_z']>=$anz_a){
		$_SESSION['akt_z'] = $anz_a-1;
	}
	
	$this->akt_z = $_SESSION['akt_z'];
	#print_r($_SESSION);
	echo "<h1>$this->akt_z</h1>";
	#print_r($arr_n[$this->akt_z]['MIETER']);
	
	
	
	
	$alt_bez = $arr_n[$this->akt_z]['EINHEIT_BEZ'];
	if(in_array('MIETER', $arr_n[$this->akt_z]['TYP'])){
	$bez_new = $arr_n[$this->akt_z]['MIETER']['BEZ_NEW'];
	$lage = $arr_n[$this->akt_z]['MIETER']['LAGE'];
	$typ = $arr_n[$this->akt_z]['MIETER']['TYP'];
	$qm = $arr_n[$this->akt_z]['MIETER']['QM'];
	
	$telefon_m = $arr_n[$this->akt_z]['MIETER']['TEL'];
	$handy_m = $arr_n[$this->akt_z]['MIETER']['TEL'];
	$email_m = $arr_n[$this->akt_z]['MIETER']['EMAIL'];
	
	$einzug_m = date_mysql2german($arr_n[$this->akt_z]['MIETER']['VON']);
	$auszug_m = date_mysql2german($arr_n[$this->akt_z]['MIETER']['BIS']);
	
	$saldo_vv = $arr_n[$this->akt_z]['MIETER']['SALDO_VV'];
	$km_3 = $arr_n[$this->akt_z]['MIETER']['KM_3'];
	$km = $arr_n[$this->akt_z]['MIETER']['KM'];
	$nk = $arr_n[$this->akt_z]['MIETER']['NK'];
	$hk = $arr_n[$this->akt_z]['MIETER']['HK'];
	
		echo "<table>";
		$ee = new einheit();
		$ee->get_einheit_id($bez_new);
		if(!isset($ee->einheit_id)){
		echo "<tr>";
		echo "<td>";
		$f = new formular();
		$f->erstelle_formular("Import MIETER aus GFAD ins Objekt $o->objekt_kurzname", null);	
				
			
		$f->text_feld("Kurzname (Alt:$alt_bez)", "kurzname", "$bez_new", "50", 'kurzname','');
		echo "</td>";
		echo "<td>";
			$f->text_feld("Lage $lage", "lage", "$lage", "10", 'lage','');
		echo "</td>";
		echo "<td>";
			$f->text_feld("qm", "qm", "$qm", "10", 'qm','');
		echo "</td>";
			$h = new haus;
		echo "<td>";			
    		$o->dropdown_haeuser_objekt($o->objekt_id, 'Haus', 'haus_id', 'haus_id', '');
		echo "</td>";
		echo "<td>";
    		$e = new einheit;
    		$e->dropdown_einheit_typen("Typ $lage $typ", 'typ', 'typ', $typ);
		echo "</td>";

    		$f->hidden_feld("objekte_raus", "einheit_speichern");
		echo "<td>";    		
    		$f->send_button("submit_einheit", "Einheit erstellen");
		echo "</td>";

    		$f->ende_formular();	
		echo "</tr>";
			}else{
				echo "<tr><td>";
				fehlermeldung_ausgeben("Einheit $bez_new existiert");
				echo "</td></tr>";
			}
	
	
			
$anz_namen = count($arr_n[$this->akt_z]['MIETER']['NAMEN']);
for($n=0;$n<$anz_namen;$n++){
	echo "<tr>";
	echo "<td>";
	$f = new formular();
	$f->erstelle_formular("Import Namen aus GFAD ", null);
	
	
	$arr_n[$this->akt_z]['MIETER']['NAMEN'][$n] = ltrim(rtrim(str_replace('Frau', '', $arr_n[$this->akt_z]['MIETER']['NAMEN'][$n])));
	$arr_n[$this->akt_z]['MIETER']['NAMEN'][$n] = ltrim(rtrim(str_replace('Herr', '', $arr_n[$this->akt_z]['MIETER']['NAMEN'][$n])));

	$name_arr = explode(' ', $arr_n[$this->akt_z]['MIETER']['NAMEN'][$n]);
	$vorname = $name_arr[0];
	$nachname = $name_arr[1];
	
	$f->text_feld("Nachname", "nachname", "$nachname", "50", 'nachname','');
	$f->text_feld("Vorname", "vorname", "$vorname", "50", 'vorname','');
	$pp = new personen();
	$pp->dropdown_geschlecht('Geschlecht', 'geschlecht', 'geschlecht');
	$f->text_feld("Telefon", "telefon", "$telefon_m", "50", 'telefon','');
	$f->text_feld("Handy", "handy", "$handy_m", "50", 'handy','');
	$f->text_feld("Email", "email", "$email_m", "50", 'email','');
	#$f->text_feld("Zustellanschrift", "email", "$email_m", "50", 'email','');
	$f->send_button("submit_pers", "Person speichern");
	echo "</td>";
	echo "<td>";
	$this->dropdown_personen_liste_filter('Name gefunden', 'name_g', 'name_g', null, $nachname, $vorname);
	echo "</td>";
	echo "</tr>";
	$f->hidden_feld("geburtsdatum", "01.01.1900");
	$f->hidden_feld("objekte_raus", "person_speichern");
	$f->ende_formular();	
}

	$f = new formular();
	if(!$ee->get_einheit_status($ee->einheit_id)){
	echo "<tr><td>";
	$f->erstelle_formular("Mietvertrag erstellen", null);
	$f->hidden_feld("einheit_id", "$ee->einheit_id");
	$f->text_feld_inaktiv('Einheit', 'ein', $bez_new, 50, 'ein');
	$f->datum_feld('Einzug', 'einzug', $einzug_m, 'einzug');
	$f->datum_feld('Auszug', 'auszug', $auszug_m, 'auszug');
	#$f->datum_feld('Auszug', 'auszug', $auszug_m, 'auszug');
	$f->text_feld("Saldo VV", "saldo_vv", "$saldo_vv", "10", 'saldo_vv','');
	$f->text_feld("Kaltmiete vor 3 Jahren", "km_3", "$km_3", "10", 'km_3','');
	$f->text_feld("Kaltmiete", "km", "$km", "10", 'km','');
	$f->text_feld("Nebenkosten", "nk", "$nk", "10", 'nk','');
	$f->text_feld("Heizkosten", "hk", "$hk", "10", 'hk','');
	$f->hidden_feld("objekte_raus", "mv_speichern");
		
	
	$anz_namen = count($arr_n[$this->akt_z]['MIETER']['NAMEN']);
for($n=0;$n<$anz_namen;$n++){
	$name_arr = explode(' ', $arr_n[$this->akt_z]['MIETER']['NAMEN'][$n]);
	$vorname = $name_arr[0];
	$nachname = $name_arr[1];
	$this->dropdown_personen_liste_filter('Name gefunden', 'person_ids[]', 'person_ids', null, $nachname, $vorname);
	
}
	
	
	$f->send_button("submit_mv", "MV anlegen");
	$f->ende_formular();	
	echo "</td></tr>";
	}else{
		echo "<tr><td>Mietvertrag bereits angelegt</td></tr>";
	}
	echo "</table>";
	}
	
	
	
	
	
	
	#echo '<pre><br><br><br><br><br><br><br><br>';
	#print_r($o);
	/*$f = new formular();
	$f->erstelle_formular("Import aus GFAD ins Objekt $o->objekt_kurzname", null);
	$f->text_feld("Kurzname", "kurzname", "", "50", 'kurzname','');
			$f->text_feld("Lage", "lage", "", "10", 'lage','');
			$f->text_feld("qm", "qm", "", "10", 'qm','');
			$h = new haus;
    		echo "<br>";
    		
    		$o->dropdown_haeuser_objekt($o->objekt_id, 'Haus', 'haus_id', 'haus_id', '');
    		$e = new einheit;
    		$e->dropdown_einheit_typen('Typ', 'typ', 'typ', 'Wohnraum');
			$f->hidden_feld("einheit_raus", "einheit_speichern");
    		$f->send_button("submit_einheit", "Einheit erstellen");
	$f->ende_formular();*/
	
	}
}	



function import_arr($file=null){
	if(!isset($_SESSION['objekt_id'])){
		fehlermeldung_ausgeben("Objekt wählen");
		$bg = new berlussimo_global();
		$bg->objekt_auswahl_liste('?daten=objekte_raus&objekte_raus=import');
	}else{
	
	
	$arr = $this->get_import_arr($file);
	#echo '<pre>';
	#print_r($arr);
	$anz_a = count($arr);
	
	if(!isset($_SESSION['akt_z'])){
		$_SESSION['akt_z'] = 1;
	}
	
	if(isset($_REQUEST['next'])){
	$_SESSION['akt_z']++;	
	}
	
	if(isset($_REQUEST['vor'])){
	$_SESSION['akt_z']--;	
	}
	
	if($_SESSION['akt_z']<1){
		$_SESSION['akt_z'] = 1;
	}
	
	if($_SESSION['akt_z']>=$anz_a){
		$_SESSION['akt_z'] = $anz_a;
	}
	
	$this->akt_z = $_SESSION['akt_z'];
	#print_r($_SESSION);
	echo "<br><br><br><br><br><h1>$this->akt_z/$anz_a</h1>";
	}
	#echo '<pre>';
	#print_r($arr[$this->akt_z]);
	
	/*Prüfen ob Einheit angelegt*/
	$bez_new = $arr[$this->akt_z]['EINHEIT']['BEZ_NEW'];
	$ee = new einheit();
	$ee->get_einheit_id("$bez_new (Ap$this->akt_z)");
	if(!isset($ee->einheit_id)){
	$this->step1($arr[$this->akt_z]);
	}else{
		echo "EINHEIT $bez_new $ee->einheit_id";
		#$this->step2($arr[$this->akt_z], $ee->einheit_id);
		$this->step1_1($arr[$this->akt_z], $ee->einheit_id);
	}
	/*elseif($bez_new=='SANEL'){
		echo "SANEL";
	}*/
}

function step1($arr){
	#echo '<pre>';
	#print_r($arr);
	$o = new objekt();
	$o->get_objekt_infos($_SESSION['objekt_id']);	
		$wtyp = $arr['EINHEIT']['WTYP'];
		$alt_bez = $arr['EINHEIT']['BEZ_ALT'];
		$bez_new = $arr['EINHEIT']['BEZ_NEW'];
		$lage = $arr['EINHEIT']['LAGE'];
		$anz_mvs = count($arr['MVZ']);
		if(!$anz_mvs){
			die('Keine MVS oder Leer');
		}else{
			if($wtyp=='Gewerbe'){
			$qm = $arr['MVS'][$anz_mvs-1][32];
			}else{
			$qm = $arr['MVS'][$anz_mvs-1][31];	
			}
		}
		$f = new formular();
		$f->erstelle_formular("Import MIETER aus GFAD ins Objekt $o->objekt_kurzname", null);	
		$f->text_feld("Kurzname (Alt:$alt_bez)", "kurzname", "$bez_new (Ap$this->akt_z)", "50", 'kurzname','');
		$f->text_feld("Lage $lage", "lage", "$lage", "10", 'lage','');
		$f->text_feld("qm", "qm", "$qm", "10", 'qm','');
		$f->text_feld("Eigentümer QM", "weg_qm", "", "10", 'weg_qm','');
		$f->text_feld("WEG MAE", "weg_mea", "", "10", 'weg_mae','');
		$h = new haus;
 		$o->dropdown_haeuser_objekt($o->objekt_id, 'Haus', 'haus_id', 'haus_id', '');
  		$e = new einheit;
   		$e->dropdown_einheit_typen("Typ $lage $wtyp", 'typ', 'typ', $wtyp);
 		$f->hidden_feld("objekte_raus", "einheit_speichern");
  		$f->send_button("submit_einheit", "Einheit erstellen");
		$f->ende_formular();
		
		$f->erstelle_formular('Weiter', '?daten=objekte_raus&objekte_raus=import&vor');
		$f->send_button("sbmW", "Zurück");
		$f->ende_formular();
	#print_r($arr);
}


function step1_1($arr,$einheit_id){
	echo "<h2>STEP 2 - PERSONEN MIETER UND EIGENTÜMER</h2>";
#echo '<pre>';
#	print_r($arr);
	$anz_mvs = count($arr['MVZ']);
	if(!$anz_mvs){
		die('Keine MVS oder Leer');
	}else{
		/*Alle MVS durchlaufen ALTE UND NEUE*/
		for($a=0;$a<$anz_mvs;$a++){
		$anrede = ltrim(rtrim($arr['MVS'][$a][3]));
		
		$strasse =  ltrim(rtrim($arr['MVS'][$a][7]));
		$ort_plz= ltrim(rtrim($arr['MVS'][$a][8]));
		$mi1 = ltrim(rtrim($arr['MVS'][$a][4]));
		$mi2 = ltrim(rtrim($arr['MVS'][$a][6]));
		$new_arr[$a]['MIETER']['NAMEN'][] = "$mi1";	
		if(!empty($mi2)){
		$new_arr[$a]['MIETER']['NAMEN'][] = $mi2;
		$zustell_ans = "$anrede\n$mi1 $mi2\n $strasse $ort_plz";
		}else{
		$zustell_ans = "$anrede\n$mi1\n $strasse $ort_plz";	
		}
		$new_arr[$a]['MIETER']['ZUSTELL'] = $zustell_ans;
		
		
		if($anrede=='Herr' or $anrede=='Herrn'){
		$new_arr[$a]['MIETER']['GES'][] = 'männlich';	
		#echo "$anrede<br>";
		}
		if($anrede=='Herren'){
		$new_arr[$a]['MIETER']['GES'][] = 'männlich';	
		$new_arr[$a]['MIETER']['GES'][] = 'männlich';
		}
		if($anrede=='Frau'){
		$new_arr[$a]['MIETER']['GES'][] = 'weiblich';	
		}
		if($anrede=='Herr und Frau' or $anrede=='Familie'){
		$new_arr[$a]['MIETER']['GES'][] = 'männlich';	
		$new_arr[$a]['MIETER']['GES'][] = 'weiblich';	
		}
		if(!is_array($new_arr[$a]['MIETER']['GES'])){
		$new_arr[$a]['MIETER']['GES'][] = 'unbekannt';	
		$new_arr[$a]['MIETER']['GES'][] = 'unbekannt';	
		}
		
		$handy= ltrim(rtrim($arr['MVS'][$a][9]));
		$new_arr[$a]['MIETER']['TEL'][] = $handy;
		$new_arr[$a]['MIETER']['HANDY'][] = $handy;
		
		$email= ltrim(rtrim($arr['MVS'][$a][19]));
		$new_arr[$a]['MIETER']['EMAIL'][] = $email;
		
	
	}//end for
	
	/*ET*/
	$anrede = ltrim(rtrim($arr['ET']['ZEILE1'][3]));
		
		$strasse =  ltrim(rtrim($arr['ET']['ZEILE1'][7]));
		$ort_plz= ltrim(rtrim($arr['ET']['ZEILE1'][8]));
		$mi1 = ltrim(rtrim($arr['ET']['ZEILE1'][4]));
		$mi2 = ltrim(rtrim($arr['ET']['ZEILE1'][6]));
		$new_arr[$a]['MIETER']['NAMEN'][] = "$mi1";	
		if(!empty($mi2)){
		$new_arr[$a]['MIETER']['NAMEN'][] = $mi2;
		$zustell_ans = "$anrede\n$mi1 $mi2\n $strasse $ort_plz";
		}else{
		$zustell_ans = "$anrede\n$mi1\n $strasse $ort_plz";	
		}
		$new_arr[$a]['MIETER']['ZUSTELL'] = $zustell_ans;
		
		
		if($anrede=='Herr' or $anrede=='Herrn'){
		$new_arr[$a]['MIETER']['GES'][] = 'männlich';	
		#echo "$anrede<br>";
		}
		if($anrede=='Herren'){
		$new_arr[$a]['MIETER']['GES'][] = 'männlich';	
		$new_arr[$a]['MIETER']['GES'][] = 'männlich';
		}
		if($anrede=='Frau'){
		$new_arr[$a]['MIETER']['GES'][] = 'weiblich';	
		}
		if($anrede=='Herr und Frau' or $anrede=='Familie'){
		$new_arr[$a]['MIETER']['GES'][] = 'männlich';	
		$new_arr[$a]['MIETER']['GES'][] = 'weiblich';	
		}
		if(!is_array($new_arr[$a]['MIETER']['GES'])){
		$new_arr[$a]['MIETER']['GES'][] = 'unbekannt';	
		$new_arr[$a]['MIETER']['GES'][] = 'unbekannt';	
		}
		
		$handy= ltrim(rtrim($arr['ET']['ZEILE1'][9]));
		$new_arr[$a]['MIETER']['TEL'][] = $handy;
		$new_arr[$a]['MIETER']['HANDY'][] = $handy;
		
		$email= ltrim(rtrim($arr['ET']['ZEILE1'][19]));
		$new_arr[$a]['MIETER']['EMAIL'][] = $email;
	
	#print_r($new_arr);
}
$anz_mvs++;
for($a=0;$a<$anz_mvs;$a++){
$anz_namen = count($new_arr[$a]['MIETER']['NAMEN']);
if($a==$anz_mvs-1){
	$f = new formular();
	#$f->fieldset_ende();
	$f->fieldset('EIGENTÜMER', 'ett');
}else{
	$f = new formular();
	$f->fieldset('MIETER', 'miet');
}
for($n=0;$n<$anz_namen;$n++){
	
		
	$new_arr[$a]['MIETER']['NAMEN'][$n] = ltrim(rtrim(str_replace('Frau', '', $new_arr[$a]['MIETER']['NAMEN'][$n])));
	$new_arr[$a]['MIETER']['NAMEN'][$n] = ltrim(rtrim(str_replace('Herr', '', $new_arr[$a]['MIETER']['NAMEN'][$n])));

	
	$name_full =$new_arr[$a]['MIETER']['NAMEN'][$n];
	$name_arr = explode(' ', $new_arr[$a]['MIETER']['NAMEN'][$n]);
	$vorname = $name_arr[0];
	$nachname = $name_arr[1];
	
	$geschlecht = $new_arr[$a]['MIETER']['GES'][$n];
	$telefon_m = $new_arr[$a]['MIETER']['TEL'][$n];
	if(isset($new_arr[$a]['MIETER']['HANDY'][$n])){
	$handy_m = $new_arr[$a]['MIETER']['HANDY'][$n];
	}
	$email_m = $new_arr[$a]['MIETER']['EMAIL'][$n];
	
	if($vorname!='LEER'){
	$f = new formular();
	$f->erstelle_formular("Import Namen aus GFAD $name_full", null);
	$person_id = $this->dropdown_personen_liste_filter('Name gefunden', 'name_g', 'name_g', null, $nachname, $vorname);
	if(empty($person_id)){
	$error = true;
	$f->text_feld("Nachname", "nachname", "$nachname", "50", 'nachname','');
	$f->text_feld("Vorname", "vorname", "$vorname", "50", 'vorname','');
	$pp = new personen();
	$pp->dropdown_geschlecht('Geschlecht', 'geschlecht', 'geschlecht', $geschlecht);
	$f->text_feld("Telefon", "telefon", "$telefon_m", "50", 'telefon','');
	$f->text_feld("Handy", "handy", "$handy_m", "50", 'handy','');
	$f->text_feld("Email", "email", "$email_m", "50", 'email','');
	if($a==$anz_mvs-1){
	#$f->text_feld("TYP", "p_typ", "ET", "50", 'p_typ','');
	$f->datum_feld('Eigentümer seit', 'et_seit', '01.01.2014', 'et_seit');
		$f->hidden_feld("p_typ", "ET");	
	}else{
	$f->hidden_feld("p_typ", "MIETER");	
	}
	#$f->text_feld("Zustellanschrift", "email", "$email_m", "50", 'email','');
	$f->hidden_feld("einheit_id", "$einheit_id");	
	$f->send_button("submit_pers", "Person speichern");
	
	
	
	$f->hidden_feld("geburtsdatum", "01.01.1900");
	$f->hidden_feld("objekte_raus", "person_speichern");
	}else{
		
		#echo "$name_full gespeichert";
		$error = false;
	}
	if($a==$anz_mvs-1 && $this->check_person_et($person_id, $einheit_id)==false){
	$f->hidden_feld("einheit_id", "$einheit_id");	
	$f->datum_feld('Eigentümer seit', 'et_seit', '01.01.2014', 'et_seit');
	$f->hidden_feld("p_typ", "ET");	
	$f->hidden_feld("objekte_raus", "person_et");
	$f->send_button("submit_pers", "ET übernehmen");
	}
	
	
	$f->ende_formular();
	}
}
}
if($error==false){
	$this->step3($arr, $einheit_id);
}
}

function step2($arr,$einheit_id){
	echo "<h2>STEP 2 - PERSONEN AUS MVS</h2>";
#echo '<pre>';
	print_r($arr);
	$anz_mvs = count($arr['MVZ']);
	if(!$anz_mvs){
		die('Keine MVS oder Leer');
	}else{
		/*Alle MVS durchlaufen ALTE UND NEUE*/
		for($a=0;$a<$anz_mvs;$a++){
		$anrede = ltrim(rtrim($arr['MVS'][$a][3]));
		
		$strasse =  ltrim(rtrim($arr['MVS'][$a][7]));
		$ort_plz= ltrim(rtrim($arr['MVS'][$a][8]));
		$mi1 = ltrim(rtrim($arr['MVS'][$a][4]));
		$mi2 = ltrim(rtrim($arr['MVS'][$a][6]));
		$new_arr[$a]['MIETER']['NAMEN'][] = "$mi1";	
		if(!empty($mi2)){
		$new_arr[$a]['MIETER']['NAMEN'][] = $mi2;
		$zustell_ans = "$anrede\n$mi1 $mi2\n $strasse $ort_plz";
		}else{
		$zustell_ans = "$anrede\n$mi1\n $strasse $ort_plz";	
		}
		$new_arr[$a]['MIETER']['ZUSTELL'] = $zustell_ans;
		
		
		if($anrede=='Herr' or $anrede=='Herrn'){
		$new_arr[$a]['MIETER']['GES'][] = 'männlich';	
		#echo "$anrede<br>";
		}
		if($anrede=='Herren'){
		$new_arr[$a]['MIETER']['GES'][] = 'männlich';	
		$new_arr[$a]['MIETER']['GES'][] = 'männlich';
		}
		if($anrede=='Frau'){
		$new_arr[$a]['MIETER']['GES'][] = 'weiblich';	
		}
		if($anrede=='Herr und Frau' or $anrede=='Familie'){
		$new_arr[$a]['MIETER']['GES'][] = 'männlich';	
		$new_arr[$a]['MIETER']['GES'][] = 'weiblich';	
		}
		if(!is_array($new_arr[$a]['MIETER']['GES'])){
		$new_arr[$a]['MIETER']['GES'][] = 'unbekannt';	
		$new_arr[$a]['MIETER']['GES'][] = 'unbekannt';	
		}
		
		$handy= ltrim(rtrim($arr['MVS'][$a][9]));
		$new_arr[$a]['MIETER']['TEL'][] = $handy;
		$new_arr[$a]['MIETER']['HANDY'][] = $handy;
		
		$email= ltrim(rtrim($arr['MVS'][$a][19]));
		$new_arr[$a]['MIETER']['EMAIL'][] = $email;
		
	
	}//end for
	#print_r($new_arr);
}

for($a=0;$a<$anz_mvs;$a++){
$anz_namen = count($new_arr[$a]['MIETER']['NAMEN']);

for($n=0;$n<$anz_namen;$n++){
	
		
	$new_arr[$a]['MIETER']['NAMEN'][$n] = ltrim(rtrim(str_replace('Frau', '', $new_arr[$a]['MIETER']['NAMEN'][$n])));
	$new_arr[$a]['MIETER']['NAMEN'][$n] = ltrim(rtrim(str_replace('Herr', '', $new_arr[$a]['MIETER']['NAMEN'][$n])));

	
	$name_full =$new_arr[$a]['MIETER']['NAMEN'][$n];
	$name_arr = explode(' ', $new_arr[$a]['MIETER']['NAMEN'][$n]);
	$vorname = $name_arr[0];
	$nachname = $name_arr[1];
	
	$geschlecht = $new_arr[$a]['MIETER']['GES'][$n];
	$telefon_m = $new_arr[$a]['MIETER']['TEL'][$n];
	if(isset($new_arr[$a]['MIETER']['HANDY'][$n])){
	$handy_m = $new_arr[$a]['MIETER']['HANDY'][$n];
	}
	$email_m = $new_arr[$a]['MIETER']['EMAIL'][$n];
	
	if($vorname!='LEER'){
	$f = new formular();
	$f->erstelle_formular("Import Namen aus GFAD $name_full", null);
	if(!$this->dropdown_personen_liste_filter('Name gefunden', 'name_g', 'name_g', null, $nachname, $vorname)){
	$error = true;
	$f->text_feld("Nachname", "nachname", "$nachname", "50", 'nachname','');
	$f->text_feld("Vorname", "vorname", "$vorname", "50", 'vorname','');
	$pp = new personen();
	$pp->dropdown_geschlecht('Geschlecht', 'geschlecht', 'geschlecht', $geschlecht);
	$f->text_feld("Telefon", "telefon", "$telefon_m", "50", 'telefon','');
	$f->text_feld("Handy", "handy", "$handy_m", "50", 'handy','');
	$f->text_feld("Email", "email", "$email_m", "50", 'email','');
	#$f->text_feld("Zustellanschrift", "email", "$email_m", "50", 'email','');
	$f->send_button("submit_pers", "Person speichern");
	
	
	
	$f->hidden_feld("geburtsdatum", "01.01.1900");
	$f->hidden_feld("objekte_raus", "person_speichern");
	}else{
		#echo "$name_full gespeichert";
		$error = false;
	}
	$f->ende_formular();
	}
}
}
if($error==false){
	$this->step3($arr, $einheit_id);
}
}


function step3($arr, $einheit_id){
echo "<h2>STEP 3 - MIETVERTRAG ERSTELLEN</h2>";
	$anz_mvs = count($arr['MVZ']);
	if(!$anz_mvs){
		die('Keine MVS oder Leer');
	}else{
		/*Alle MVS durchlaufen ALTE UND NEUE*/
		for($a=0;$a<$anz_mvs;$a++){
		$anrede = ltrim(rtrim($arr['MVS'][$a][3]));
		
		$strasse =  ltrim(rtrim($arr['MVS'][$a][7]));
		$ort_plz= ltrim(rtrim($arr['MVS'][$a][8]));
		$mi1 = ltrim(rtrim($arr['MVS'][$a][4]));
		$mi2 = ltrim(rtrim($arr['MVS'][$a][6]));
		$new_arr[$a]['MIETER']['NAMEN'][] = "$mi1";	
		if(!empty($mi2)){
		$new_arr[$a]['MIETER']['NAMEN'][] = $mi2;
		$zustell_ans = "$anrede\n$mi1 $mi2\n $strasse $ort_plz";
		}else{
		$zustell_ans = "$anrede\n$mi1\n $strasse $ort_plz";	
		}
		$new_arr[$a]['MIETER']['ZUSTELL'] = $zustell_ans;
		
		
		if($anrede=='Herr' or $anrede=='Herrn'){
		$new_arr[$a]['MIETER']['GES'][] = 'männlich';	
		#echo "$anrede<br>";
		}
		if($anrede=='Herren'){
		$new_arr[$a]['MIETER']['GES'][] = 'männlich';	
		$new_arr[$a]['MIETER']['GES'][] = 'männlich';
		}
		if($anrede=='Frau'){
		$new_arr[$a]['MIETER']['GES'][] = 'weiblich';	
		}
		if($anrede=='Herr und Frau' or $anrede=='Familie'){
		$new_arr[$a]['MIETER']['GES'][] = 'männlich';	
		$new_arr[$a]['MIETER']['GES'][] = 'weiblich';	
		}
		if(!is_array($new_arr[$a]['MIETER']['GES'])){
		$new_arr[$a]['MIETER']['GES'][] = 'unbekannt';	
		$new_arr[$a]['MIETER']['GES'][] = 'unbekannt';	
		}
		
		$handy= ltrim(rtrim($arr['MVS'][$a][9]));
		$new_arr[$a]['MIETER']['TEL'][] = $handy;
		$new_arr[$a]['MIETER']['HANDY'][] = $handy;
		
		$email= ltrim(rtrim($arr['MVS'][$a][19]));
		$new_arr[$a]['MIETER']['EMAIL'][] = $email;
		
	
	}//end for
	#print_r($new_arr);
}
#echo '<pre>';
#print_r($arr);

$f = new formular();
#$f->erstelle_formular("Import MVS aus GFAD", null);
$error = false;
for($a=0;$a<$anz_mvs;$a++){

$einzug_m = substr($arr['MVS'][$a][15],0,10);
$auszug_m =  substr($arr['MVS'][$a][16],0,10);
$von_mv = date_german2mysql($einzug_m);
$bis_mv = date_german2mysql($auszug_m);
$bez_alt = $arr['MVS'][$a][2];
/*Wenn kein MV angelegt*/
if(!$this->check_mv($von_mv, $bis_mv, $einheit_id)){
$error = true;
	$anz_namen = count($new_arr[$a]['MIETER']['NAMEN']);

if($arr['MVS'][$a][4]!='LEER'){//Wenn nicht leer
	if($anz_mvs>1 && $a<$anz_mvs-1){
	$f->erstelle_formular("Alten Mietvertrag importieren", null);
	
	}else{
		$f->erstelle_formular("Aktuellen Mietvertrag importieren", null);
	}
	$zustell_ans = $new_arr[$a]['MIETER']['ZUSTELL'];
	$f->text_feld("Zustellanschrift", "zustell_ans", "$zustell_ans", "100", 'zustell','');
	$f->hidden_feld("einheit_id", "$einheit_id");
	$ee = new einheit();
	$ee->get_einheit_info($einheit_id);
	$f->text_feld_inaktiv('Einheit', 'ein', "$ee->einheit_kurzname ALT: $bez_alt", 50, 'ein');
	
	$f->datum_feld('Einzug', 'einzug', $einzug_m, 'einzug');
	$f->datum_feld('Auszug', 'auszug', $auszug_m, 'auszug');
	#$f->text_feld("Saldo VV", "saldo_vv", "$saldo_vv", "10", 'saldo_vv','');
	$km_3 = $this->euro_entferen($arr['MVS'][$a][35]);
	$f->text_feld("Kaltmiete vor 3 Jahren", "km_3", "$km_3", "10", 'km_3','');
	$km = $this->euro_entferen($arr['MVS'][$a][27]);
	$f->text_feld("Kaltmiete", "km", "$km", "10", 'km','');
	$nk = $this->euro_entferen($arr['MVS'][$a][28]);//nebenkosten ohne hk
	$kab = $this->euro_entferen($arr['MVS'][$a][30]); //Plus Kabel
	
	$f->text_feld("Nebenkosten", "nk", "$nk", "10", 'nk','');
	$f->text_feld("Kabel TV", "kabel_tv", "$kab", "10", 'kabel_tv','');
	$hk = $this->euro_entferen($arr['MVS'][$a][29]);//nebenkosten ohne hk
	$f->text_feld("Heizkosten", "hk", "$hk", "10", 'hk','');
	$miete_gesamt_import = $this->euro_entferen($arr['MVS'][$a][17]);
	$miete_gesamt = nummer_punkt2komma(nummer_komma2punkt($km) + nummer_komma2punkt($nk) + nummer_komma2punkt($kab) + nummer_komma2punkt($hk));
	if(nummer_komma2punkt($miete_gesamt)!= nummer_komma2punkt($miete_gesamt_import)){
	$f->fieldset('Differenz in der Gesamtmiete', 'te');
	$f->text_feld_inaktiv('Gesamtmiete errechnet', 'gm', "$miete_gesamt", 10, 'gm');
	$f->text_feld_inaktiv('Gesamtmiete import', 'gm', "$miete_gesamt_import", 10, 'gm');
	$f->fieldset_ende();
	}else{
	$f->text_feld_inaktiv('Gesamtmiete import', 'gm', "$miete_gesamt_import", 10, 'gm');	
	}
	$kaution =  $arr['MVS'][$a][33];
	$f->text_feld("Kautionshinweis", "kaution", "$kaution", "100", 'kaution','');
	
	$klein_rep =  $arr['MVS'][$a][34];
	$f->text_feld("Kleinreparaturen", "klein_rep", "$klein_rep", "100", 'klein_rep','');
	
	$zusatzinfo =  $arr['MVS'][$a][36];
	$f->text_feld("Zusatzinfo", "zusatzinfo", "$zusatzinfo", "100", 'zusatzinfo','');
	
	
	for($n=0;$n<$anz_namen;$n++){
	$new_arr[$a]['MIETER']['NAMEN'][$n] = ltrim(rtrim(str_replace('Frau', '', $new_arr[$a]['MIETER']['NAMEN'][$n])));
	$new_arr[$a]['MIETER']['NAMEN'][$n] = ltrim(rtrim(str_replace('Herr', '', $new_arr[$a]['MIETER']['NAMEN'][$n])));

	$name_full =$new_arr[$a]['MIETER']['NAMEN'][$n];
	$name_arr = explode(' ', $new_arr[$a]['MIETER']['NAMEN'][$n]);
	$vorname = $name_arr[0];
	$nachname = $name_arr[1];
	
		
	$this->dropdown_personen_liste_filter('Name gefunden', 'person_ids[]', 'person_ids', null, $nachname, $vorname);
	
	}
	$f->hidden_feld("objekte_raus", "mv_speichern");
	$f->send_button("submit_mv", "MV importieren");
	$f->ende_formular();
	}
}else{
	$anz_m = $a+1;
	echo "<br><b>Mietvertrag $anz_m zu Einheit $bez_alt wurde bereits importiert</b><br>";
}

if($error==false){
	#echo "WEITER";
	$f->erstelle_formular('Weiter', '?daten=objekte_raus&objekte_raus=import&next');
	$f->send_button("sbmW", "Weiter");
	$f->ende_formular();
}
}


#if($error==false){
#	$this->step3($arr, $einheit_id);
#}
	
	
	/*
	
	
	$f = new formular();
	$ee = new einheit();
	if(!$ee->get_einheit_status($einheit_id)){
	$f->erstelle_formular("Mietvertrag erstellen", null);
	$f->hidden_feld("einheit_id", "$ee->einheit_id");
	$f->text_feld_inaktiv('Einheit', 'ein', $bez_new, 50, 'ein');
	$f->datum_feld('Einzug', 'einzug', $einzug_m, 'einzug');
	$f->datum_feld('Auszug', 'auszug', $auszug_m, 'auszug');
	#$f->datum_feld('Auszug', 'auszug', $auszug_m, 'auszug');
	$f->text_feld("Saldo VV", "saldo_vv", "$saldo_vv", "10", 'saldo_vv','');
	$f->text_feld("Kaltmiete vor 3 Jahren", "km_3", "$km_3", "10", 'km_3','');
	$f->text_feld("Kaltmiete", "km", "$km", "10", 'km','');
	$f->text_feld("Nebenkosten", "nk", "$nk", "10", 'nk','');
	$f->text_feld("Heizkosten", "hk", "$hk", "10", 'hk','');
	$f->hidden_feld("objekte_raus", "mv_speichern");
		
	
	$anz_namen = count($arr_n[$this->akt_z]['MIETER']['NAMEN']);
for($n=0;$n<$anz_namen;$n++){
	$name_arr = explode(' ', $arr_n[$this->akt_z]['MIETER']['NAMEN'][$n]);
	$vorname = $name_arr[0];
	$nachname = $name_arr[1];
	$this->dropdown_personen_liste_filter('Name gefunden', 'person_ids[]', 'person_ids', null, $nachname, $vorname);
	
}
	
	
	$f->send_button("submit_mv", "MV anlegen");
	$f->ende_formular();	
}else{
	echo "Einheit vermietet";
}
*/
}

function euro_entferen($string){
	return str_replace('€', '', $string);
}



function get_import_arr($file=null){

	if(!file_exists(HAUPT_PATH.'/'.BERLUS_PATH.'/'.$file)){
		$ff = HAUPT_PATH.'/'.BERLUS_PATH.'/'.$file;
		die(fehlermeldung_ausgeben("Datei: <u>$ff</u> existiert nicht"));
	}
	$arr = file(HAUPT_PATH.'/'.BERLUS_PATH.'/'.$file);
	$o = new objekt();
	$o->get_objekt_infos($_SESSION['objekt_id']);
	#echo '<pre>';
	#print_r($arr);
	
	/*Überschriften*/
	#$ue_arr = explode(';', $arr[0]);
	#print_r($ue_arr);
	$anz_a = count($arr);
	
	
	#print_r(explode('*', $arr[1]));
	/*Zeilenbearbeitung*/
	
	$gew_z = 101;
	$woh_z = 201;
	$pp_z =  601;
	for($a=2;$a<$anz_a;$a++){
	$z_arr = explode('*', $arr[$a]);
	/*Debug*/
	#echo '<pre>';
	#print_r($z_arr);
	
	$etyp = $z_arr[1];
	$wtyp = ltrim(rtrim($z_arr[37]));
	$we_bez_alt = $z_arr[2];
	$name1 = ltrim(rtrim($z_arr[4]));
	$name2 = ltrim(rtrim($z_arr[5]));
	$lage = ltrim(rtrim($z_arr[14]));
	
	$we_nr = substr($we_bez_alt, 0,-2);
	
	#echo "$we_nr $etyp $wtyp<br>";
	$ein_arr[$we_nr]['EINHEIT']['WTYP'] = $wtyp;
	$ein_arr[$we_nr]['EINHEIT']['BEZ_ALT'] = $we_bez_alt;
	$ein_arr[$we_nr]['EINHEIT']['WE_NR'] = $we_nr;
	$ein_arr[$we_nr]['EINHEIT']['LAGE'] = $lage;
	
	
	if($etyp == 'E'){
		/*Nur bei ET, sonst doppelt*/
		if($wtyp=='Gewerbe'){
		$ein_arr[$we_nr]['EINHEIT']['BEZ_NEW'] = "$o->objekt_kurzname-$gew_z";
		$gew_z++;	
		}
		if($wtyp=='Wohnung'){
		$ein_arr[$we_nr]['EINHEIT']['BEZ_NEW'] = "$o->objekt_kurzname-$woh_z";
		$woh_z++;	
		}
		
		
		$ein_arr[$we_nr]['ET']['ZEILE'] = $a;
		$ein_arr[$we_nr]['ET']['ZEILE1'] = explode('*', $arr[$a]);
		/*$ein_arr[$we_nr]['ET']['NAME1'] = $name1;
		$ein_arr[$we_nr]['ET']['NAME2'] = $name2;*/
	}
	
	#if($etyp=='S'  or $etyp=='G' or $etyp=='FS'){
		if($etyp=='M' or $etyp=='S' or $etyp=='G' or $etyp=='FS'){
		$ein_arr[$we_nr]['MVZ'][] = $a;
		$ein_arr[$we_nr]['MVS'][] = explode('*', $arr[$a]);
	}
	}
	
	$anz = count($ein_arr);
	#$ein_arr1 = array_unique($ein_arr);
	$iZero = array_values($ein_arr);
	$ein_arr1 = array_combine(range(1, count($ein_arr)), array_values($ein_arr));
	unset($ein_arr);
	unset($iZero);
	return $ein_arr1;
	#print_r($ein_arr);
	#return $ein_arr;
		
	
	

}

function dropdown_personen_liste_filter($label, $name, $id, $javaaction, $name_v, $vorname_v, $et=0){
$db_abfrage = "SELECT PERSON_ID, PERSON_NACHNAME, PERSON_VORNAME, PERSON_GEBURTSTAG FROM PERSON WHERE PERSON_AKTUELL='1' && PERSON_VORNAME='$vorname_v' && PERSON_NACHNAME='$name_v' ORDER BY PERSON_NACHNAME, PERSON_VORNAME ASC";
$result = mysql_query($db_abfrage) or
           die(mysql_error());
$numrows = mysql_numrows($result);
if($numrows){
	while ($row = mysql_fetch_assoc($result)) $personen[] = $row;
	echo "<label for=\"$id\">$label</label><select name=\"$name\" id=\"$id\" $javaaction>";
	for($a=0;$a<count($personen);$a++){
	$person_id = $personen[$a]['PERSON_ID'];
	if($et==1){
		return $person_id;
	}
	$vorname = $personen[$a]['PERSON_VORNAME'];
	$nachname = $personen[$a]['PERSON_NACHNAME'];
	if($name_v==$nachname or $vorname==$vorname_v){
		$pp = new personen();
		$pp->get_person_infos($person_id);
		$anz_mv = count($pp->p_mv_ids);
			for($m=0;$m<$anz_mv;$m++){
				$mv = new mietvertraege();
				$mv_id = $pp->p_mv_ids[$m];
				$mv->get_mietvertrag_infos_aktuell($mv_id);
				$mv_str .= "$mv->einheit_kurzname ";
			}
		
	echo "<option value=\"$person_id\" selected>$nachname $vorname MV:$anz_mv $mv_str</option>";
	}else{
		echo "<option value=\"$person_id\">$nachname $vorname</option>";
	}
	}
	echo "</select>";
return $person_id;
}
}


function check_mv($von, $bis, $einheit_id){
		$result = mysql_query ("SELECT * FROM MIETVERTRAG WHERE EINHEIT_ID = '$einheit_id' && MIETVERTRAG_AKTUELL = '1' &&  MIETVERTRAG_VON = '$von'  && MIETVERTRAG_BIS = '$bis'  LIMIT 0 , 1 ");
		#echo "SELECT * FROM MIETVERTRAG WHERE EINHEIT_ID = '$einheit_id' && MIETVERTRAG_AKTUELL = '1' &&  MIETVERTRAG_VON = '$von'  && MIETVERTRAG_BIS = '$bis'  LIMIT 0 , 1 ";
		#echo "SELECT * FROM MIETVERTRAG WHERE EINHEIT_ID = '$einheit_id' && MIETVERTRAG_AKTUELL = '1' && ( MIETVERTRAG_BIS >= '$this->datum_heute' OR MIETVERTRAG_BIS = '0000-00-00' ) LIMIT 0 , 1 ";
		$numrows = mysql_numrows($result);
		if($numrows){
			return true;
		}else{
			return false;
		}
		
}

function et_erstellen($einheit_id, $von, $bis='0000-00-00'){
/*Neue Eigentümer eintragen*/	
$id = last_id2('WEG_MITEIGENTUEMER', 'ID') +1 ;
$db_abfrage = "INSERT INTO WEG_MITEIGENTUEMER VALUES (NULL, '$id', '$einheit_id', '$von', '$bis', '1')";	
$resultat = mysql_query($db_abfrage) or
           die(mysql_error());
/*Zugewiesene MIETBUCHUNG_DAT auslesen*/
$last_dat = mysql_insert_id();
protokollieren('WEG_MITEIGENTUEMER', '0', $last_dat);
return $id;	
}

function et_person_hinzu($et_id, $person_id){
	/*Personen zu ID eintragen*/
	$p_id = last_id2('WEG_EIGENTUEMER_PERSON', 'ID') +1 ;
	$db_abfrage = "INSERT INTO WEG_EIGENTUEMER_PERSON VALUES (NULL, '$p_id', '$et_id', '$person_id', '1')";	
	$resultat = mysql_query($db_abfrage) or
           die(mysql_error());
	/*Zugewiesene DAT auslesen*/
	$last_dat = mysql_insert_id();
	protokollieren('WEG_EIGENTUEMER_PERSON', '0', $last_dat);
	
}

function get_last_eigentuemer_id($einheit_id){
	$result = mysql_query ("SELECT ID FROM WEG_MITEIGENTUEMER WHERE EINHEIT_ID='$einheit_id' && AKTUELL='1' ORDER BY VON DESC LIMIT 0,1");
	$numrows = mysql_numrows($result);
		if($numrows){
			$row = mysql_fetch_assoc($result);
			return $row['ID'];
		}else{
			return false;
		}		
	}

function check_person_et($person_id, $einheit_id){
#	SELECT `WEG_EIG_ID` FROM `WEG_EIGENTUEMER_PERSON` WHERE `PERSON_ID` = 1386 AND `AKTUELL` = '1' && `WEG_EIG_ID` IN (SELECT ID 
#FROM  `WEG_MITEIGENTUEMER` 
#WHERE  `EINHEIT_ID` =1139
#AND  `AKTUELL` =  '1')

$result = mysql_query ("SELECT `WEG_EIG_ID` FROM `WEG_EIGENTUEMER_PERSON` WHERE `PERSON_ID` = '$person_id' AND `AKTUELL` = '1' && `WEG_EIG_ID` IN (SELECT ID FROM  `WEG_MITEIGENTUEMER` WHERE  `EINHEIT_ID` ='$einheit_id' AND  `AKTUELL` =  '1')");
	$numrows = mysql_numrows($result);
		if($numrows){
			return true;
		}else{
			return false;
		}		
}

}//end class




?>