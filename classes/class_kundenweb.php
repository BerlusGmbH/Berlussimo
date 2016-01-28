<?php

class kundenweb{

	function get_kundendaten_arr($kunden_id=0){
		if($kunden_id==0){
		$db_abfrage = "SELECT * FROM  `KUNDEN_LOGIN` WHERE AKTUELL='1'";
		}else{
			$db_abfrage = "SELECT * FROM  `KUNDEN_LOGIN` WHERE ID='$kunden_id' && AKTUELL='1'";
		}
		$result = mysql_query($db_abfrage) or
		die(mysql_error());
		$numrows = mysql_numrows($result);
		if($numrows){
			while ($row = mysql_fetch_assoc($result)){
				$arr[] = $row;
			}
			return $arr;
		}else{
			return false;
		}
	}
	
	
		
	
	function kundendaten_anzeigen_alle(){
		$arr = $this-> get_kundendaten_arr();
		if(!is_array($arr)){
			fehlermeldung_ausgeben("Keine Kundweb benutzer vorhanden");
		}else{
			//echo '<pre>';
			//print_r($arr);
			$f = new formular;
			$f->fieldset("Kunden mit Zugriff auf Kundenweb", 'kp');
			$anz = count($arr);
			echo "<table class=\"sortable\"><tr><th>PERSON</th><th>FIRMA</th><th>BENUTZERNAME</th><th>EMAIL</th><th>BERECHTIGUNG</th><th>LÖSCHEN</th></tr>";
			for($a=0;$a<$anz;$a++){
				$kunden_id = $arr[$a]['ID'];
				$username = $arr[$a]['USERNAME'];
				$passwd = $arr[$a]['PASSWORD'];
				$email =  $arr[$a]['EMAIL'];
				$person_id  = $arr[$a]['PERSON_ID'];
				$partner_id  = $arr[$a]['PDF_PARTNER_ID'];
				
				$p = new person;
				$p->get_person_infos($person_id);
				//$p->person_nachname
				//$p->person_vorname
				$pa = new partners;
				$pa->get_partner_info($partner_id);
				/*$this->partner_dat = $row['PARTNER_DAT'];
				$this->partner_name = $row['PARTNER_NAME'];
				$this->partner_strasse = $row['STRASSE'];
				$this->partner_hausnr = $row['NUMMER'];
				$this->partner_plz = $row['PLZ'];
				$this->partner_ort = $row['ORT'];
				$this->partner_land = $row['LAND'];*/
				$link_ber = "<a href=\"?daten=kundenweb&option=berechtigung&kunden_id=$kunden_id\">Berechtigung</a>";
				$link_deakt = "<a href=\"?daten=kundenweb&option=deaktivieren&kunden_id=$kunden_id\">Deaktivieren/Löschen</a>";
				echo "<tr><td>$p->person_nachname $p->person_vorname</td><td>$pa->partner_name</td><td>$username</td><td>$email</td><td>$link_ber</td><td>$link_deakt</td></tr>";
			}
			echo "</table>";
			$f->fieldset_ende();
		}
	}
	
	
	function kundendaten_anzeigen($kunden_id){
		$arr = $this-> get_kundendaten_arr($kunden_id);
		if(!is_array($arr)){
			fehlermeldung_ausgeben("Keine Kundweb benutzer vorhanden");
		}else{
			$anz = count($arr);
			$f = new formular;
			$f->fieldset("Kundenprofil", 'kp');
			echo "<table class=\"sortable\"><tr><th>PERSON</th><th>FIRMA</th><th>BENUTZERNAME</th><th>EMAIL</th><th>PASSWORT</th></tr>";
			for($a=0;$a<$anz;$a++){
				$kunden_id = $arr[$a]['ID'];
				$username = $arr[$a]['USERNAME'];
				$passwd = $arr[$a]['PASSWORD'];
				$email =  $arr[$a]['EMAIL'];
				$person_id  = $arr[$a]['PERSON_ID'];
				$partner_id  = $arr[$a]['PDF_PARTNER_ID'];
				
				$p = new person;
				$p->get_person_infos($person_id);
				//$p->person_nachname
				//$p->person_vorname
				$pa = new partners;
				$pa->get_partner_info($partner_id);
				/*$this->partner_dat = $row['PARTNER_DAT'];
				$this->partner_name = $row['PARTNER_NAME'];
				$this->partner_strasse = $row['STRASSE'];
				$this->partner_hausnr = $row['NUMMER'];
				$this->partner_plz = $row['PLZ'];
				$this->partner_ort = $row['ORT'];
				$this->partner_land = $row['LAND'];*/
				//$link_ber = "<a href=\"?daten=kundenweb&option=berechtigung&kunden_id=$kunden_id\">Berechtigung</a>";
				echo "<tr><td>$p->person_nachname $p->person_vorname</td><td>$pa->partner_name</td><td>$username</td><td>$email</td><td>$passwd</td></tr>";
			}
			echo "</table>";
			
			
			/*Formular für Neue berechtigungen*/
			$this->form_berechtigung($kunden_id);
			
			
			
			/*Berechtigungen*/
			$arr_ber = $this-> kunden_berr_arr($kunden_id);
			if(!is_array($arr_ber)){
				die('Keine Berechtigung für den Kunden');
			}else{
			#echo '<pre>';
			#print_r($arr_ber);
			$anz = count($arr_ber);
			echo "<table class=\"sortable\"><tr><th>NR</th><th>TYP</th><th>BEZEICHNUNG</th><th>OPTIONEN</th></tr>";
			$z = 0;
			for($a=0;$a<$anz;$a++){
				$z++;
				$ber_obj = $arr_ber[$a]['ZUGRIFF_OBJ'];
				$ber_id = $arr_ber[$a]['ZUGRIFF_ID'];
				$r = new rechnung;
				$kos_bez = $r->kostentraeger_ermitteln($ber_obj, $ber_id);
				$link_loeschen = "<a href=\"?daten=kundenweb&option=berechtigung_del&kunden_id=$kunden_id&ber_obj=$ber_obj&ber_id=$ber_id\">Löschen</a>";
				echo "<tr><td>$z</td><td>$ber_obj</td><td>$kos_bez</td><td>$link_loeschen</td></tr>";
			}
			echo "</table>";
			
			
			$f->fieldset_ende();
		}			
	}
}	
	
	function get_person_of_kunde($kunden_id){
		$db_abfrage = "SELECT * FROM  `KUNDEN_LOGIN` WHERE ID='$kunden_id' && AKTUELL='1' ORDER BY DAT DESC LIMIT 0,1";
		$result = mysql_query($db_abfrage) or
		die(mysql_error());
		$numrows = mysql_numrows($result);
		if($numrows){
			$row = mysql_fetch_assoc($result);
			return $row['PERSON_ID'];
		}
	}
	
	function get_kunden_id_of_person($person_id){
		$db_abfrage = "SELECT * FROM  `KUNDEN_LOGIN` WHERE PERSON_ID='$person_id' && AKTUELL='1' ORDER BY DAT DESC LIMIT 0,1";
		$result = mysql_query($db_abfrage) or
		die(mysql_error());
		$numrows = mysql_numrows($result);
		if($numrows){
			$row = mysql_fetch_assoc($result);
			return $row['ID'];
		}
	}
	
	
	function kunden_berr_arr($kunden_id=0){
		if($kunden_id==0){
			fehlermeldung_ausgeben("Kundennr 0 unbekannt!");
			die();
		}else{
			$person_id = $this->get_person_of_kunde($kunden_id);
			if(empty($person_id)){
				die("KEINE PERSON DER KUNDENNR ZUGEWIESEN");
			}
			$db_abfrage = "SELECT * FROM  `KUNDEN_LOG_BER` WHERE PERSON_ID='$person_id' && AKTUELL='1'";
			$result = mysql_query($db_abfrage) or
			die(mysql_error());
			$numrows = mysql_numrows($result);
			if($numrows){
				while ($row = mysql_fetch_assoc($result)){
					$arr[] = $row;
				}
				return $arr;
			}else{
				return false;
			}
		}
	}
	
	
	function berechtigung_del($kunden_id, $ber_obj, $ber_id){
		$person_id = $this->get_person_of_kunde($kunden_id);
		
		if(!empty($person_id)){
			$db_abfrage = "UPDATE `KUNDEN_LOG_BER` SET AKTUELL='0' WHERE PERSON_ID='$person_id' &&  ZUGRIFF_OBJ='$ber_obj' && ZUGRIFF_ID='$ber_id' && AKTUELL='1'";
			$result = mysql_query($db_abfrage) or
			die(mysql_error());
		}
		return true;
	}
	
	
	function form_neuer_benutzer(){
		$f = new formular;
		$f->erstelle_formular("Neuen Kundenweb-Benutzer hinzufügen", null);
		$p = new personen;
		$p->dropdown_personen("Person wählen", 'person_id', 'person_id', null);
		$pa = new partners;
		$pa->partner_dropdown("Partner der Person wählen - Logo im PDF", 'partner_id', 'partner_id', null);
		$f->text_feld("Benutzername (20 Zeichen)", "username", "", 40, 'username', null);
		$f->text_feld("Passwort (20 Zeichen)", "password", "", 40, 'password', null);
		$f->text_feld("Email (50 Zeichen)", "email", "", 40, 'email', null);
		$f->hidden_feld('option','benutzer_hinzu');
		$f->send_button('BTN_ben', 'Benutzer hinzufügen');
		$f->ende_formular();
	}
	
	function form_berechtigung($kunden_id){
		$person_id = $this->get_person_of_kunde($kunden_id);
		
		if(!empty($person_id)){
			$p = new person;
			$p->get_person_infos($person_id);
			$f = new formular;
			$f->erstelle_formular("Berechtigungen für $p->person_nachname $p->person_vorname hinzufügen", null);
			$bu = new buchen;
			$js_typ = "onchange=\"list_kostentraeger('list_kostentraeger', this.value)\"";
			$bu->dropdown_kostentreager_typen('Kostenträgertyp', 'kostentraeger_typ', 'kostentraeger_typ', $js_typ);
			$js_id = "";
			$bu->dropdown_kostentreager_ids('Kostenträger', 'kostentraeger_id', 'dd_kostentraeger_id', $js_id);
			$f->hidden_feld('option','ber_hinzu');
			$f->hidden_feld('person_id',$person_id);
			$f->send_button('BTN_ber', 'Berechtigung hinzufügen');
			$f->ende_formular();
		}
	}
	
	function check_ber($person_id, $ber_obj, $ber_id){
		$db_abfrage = "SELECT * FROM  `KUNDEN_LOG_BER` WHERE PERSON_ID='$person_id' && ZUGRIFF_OBJ='$ber_obj' && ZUGRIFF_ID='$ber_id' && AKTUELL='1' ORDER BY DAT DESC LIMIT 0,1";
		$result = mysql_query($db_abfrage) or
		die(mysql_error());
		$numrows = mysql_numrows($result);
		if($numrows){
			return true;
		}else{
			return false;
		}
	}
	
	function berechtigung_speichern($person_id, $ber_obj, $ber_id){
		/*Prüfen ob Berechtigung bereits vorhanden, wenn nicht, dann speichen*/
		if($this->check_ber($person_id, $ber_obj, $ber_id)==false){
		$last_id = last_id2('KUNDEN_LOG_BER', 'ID')+1;
		$sql = "INSERT INTO `KUNDEN_LOG_BER` VALUES (NULL, '$last_id', '$person_id', '$ber_obj', '$ber_id', '1');";
		$result = mysql_query($sql) or die(mysql_error());
		/*Protokollieren*/
		$last_dat = mysql_insert_id();
		protokollieren('KUNDEN_LOG_BER', $last_dat, '0');
		return true;
		}else{
		return true;
		}
	}
	
	function benutzer_speichern($person_id, $partner_id, $username, $passwd, $email){
		$last_id = last_id2('KUNDEN_LOGIN', 'ID')+1;
		$sql = "INSERT INTO `KUNDEN_LOGIN` VALUES (NULL, '$last_id', '$username', '$passwd', '$email', '$person_id', '$partner_id','1');";
		$result = mysql_query($sql) or die(mysql_error());
		
		/*Protokollieren*/
		$last_dat = mysql_insert_id();
		protokollieren('KUNDEN_LOGIN', $last_dat, '0');
		
		return true;
	}
	
	/*Kundenlogin deaktivieren und Berechtigungen entziehen*/
	function kunden_deaktivieren($kunden_id){
		$person_id = $this->get_person_of_kunde($kunden_id);
		
		if(!empty($person_id)){
			/*Logindaten deaktivieren*/
			$db_abfrage = "UPDATE `KUNDEN_LOGIN` SET AKTUELL='0' WHERE ID='$kunden_id' && AKTUELL='1'";
			$result = mysql_query($db_abfrage) or
			die(mysql_error());
				
						
			/*Berechtigungen deaktivieren*/
			$db_abfrage = "UPDATE `KUNDEN_LOG_BER` SET AKTUELL='0' WHERE PERSON_ID='$person_id' && AKTUELL='1'";
			$result = mysql_query($db_abfrage) or
			die(mysql_error());
		
		
		
		}
	}
	
	
}
?>