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
 * @filesource   $HeadURL$
 * @version      $Revision$
 * @modifiedby   $LastChangedBy$
 * @lastmodified $Date$
 * 
 */
include_once ('class_sepa_fremd.php');

// include_once('pdfclass/class.ezpdf.php');
//include_once (HAUPT_PATH . '/' . BERLUS_PATH . "/pdfclass/class.ezpdf.php");
// include_once('classes/class_bpdf.php');
include_once (HAUPT_PATH . '/' . BERLUS_PATH . "/classes/class_bpdf.php");
include_once (HAUPT_PATH . '/' . BERLUS_PATH . "/classes/class_buchen.php");
class sepa {
	function get_iban_bicALTFALSCH($konto_nr, $blz, $land = 'DE') {
		$this->BIC = '';
		$this->IBAN = '';
		$this->IBAN1 = '';
		$this->BANKNAME = '';
		$this->BANKNAME_K = '';
		if ($land == 'DE') {
			if (! is_numeric ( $blz ) or ! is_numeric ( $konto_nr )) {
				// die('ABBRUCH: Kontonummer und BLZ müssen aus zahlen bestehen!!!');
				// $this->konto_info = '';
				// $this->konto_info = (object) null;
				$this->BIC = ' ';
				$this->IBAN = ' ';
				$this->IBAN1 = '  ';
				return;
			}
			
			$result = mysql_query ( "SELECT * FROM `BLZ` WHERE `BLZ` ='$blz' LIMIT 0 , 1" );
			$numrows = mysql_numrows ( $result );
			if ($numrows) {
				$row = mysql_fetch_assoc ( $result );
				$konto_info = ( object ) $row;
				$this->BIC = $konto_info->BIC;
				$this->BANKNAME = $konto_info->BEZEICHNUNG;
				$this->BANKNAME_K = $konto_info->KURZ_BEZ;
				
				// echo '<pre>';
				// print_r($this->konto_info);
				// die();
				// BBAN: 8-stellige BLZ plus 10-stellige Kontonummer (ggf. führende Nullen hinzufügen)
				$iban_str = str_pad ( $blz, 8, "0", STR_PAD_LEFT ) . str_pad ( $konto_nr, 10, "0", STR_PAD_LEFT );
				// Länderkennzahl:
				// - Position des Buchstaben im Alphabet plus 9 --> A = 10, B = 11 etc.
				// - In der ASCII-Tabelle befinden sich Großbuchstaben an den Positionen 65 bis 90. Es wird also die ASCII-Position der Buchstaben ausgelesen und das Ergebnis minus 64 plus 9 (=55) gerechnet
				// - An die vierstellige Länderkennzahl werden zwei Nullen angehängt.
				$land_num = strval ( ord ( substr ( $land, 0, 1 ) ) - 55 ) . strval ( ord ( substr ( $land, 1, 1 ) ) - 55 ) . "00";
				
				// Modulus 97 der aneinandergehängten BBAN und Länderkennzahl ergibt die Prüfzahl als Teil der IBAN:
				$pz = str_pad ( 98 - intval ( bcmod ( $iban_str . $land_num, "97" ) ), 2, "0", STR_PAD_LEFT );
				
				// Die IBAN setzt sich wie folgt zusammen:
				$iban = $land . $pz . $iban_str;
				
				// echo "Die IBAN zum angegebenen Konto lautet \"".$iban."\"";
				
				$iban_1 = chunk_split ( $iban, 4, ' ' );
				$this->IBAN = $iban;
				$this->IBAN1 = $iban_1;
				// $this->pruefziffer($konto_nr);
			}
			return true;
		} else {
			$this->BIC = $land;
			$this->IBAN = $land;
			$this->IBAN1 = $land;
		}
	}
	function get_iban_bic($konto_nr, $blz, $land = 'DE') {
		$this->BIC = '';
		$this->IBAN = '';
		$this->IBAN1 = '';
		$this->BANKNAME = '';
		$this->BANKNAME_K = '';
		if ($land == 'DE') {
			$result = mysql_query ( "SELECT * FROM `BLZ` WHERE `BLZ` ='$blz' LIMIT 0 , 1" );
			$numrows = mysql_numrows ( $result );
			if ($numrows) {
				$row = mysql_fetch_assoc ( $result );
				$konto_info = ( object ) $row;
				$this->BIC = $konto_info->BIC;
				$this->BANKNAME = $konto_info->BEZEICHNUNG;
				$this->BANKNAME_K = $konto_info->KURZ_BEZ;
			}
			
			$iban = $this->get_iban_de ( $konto_nr, $blz );
			if (strlen ( $iban ) == 22) {
				$iban_1 = chunk_split ( $iban, 4, ' ' );
				// $iban_1 = $this->iban_to_human_format($iban);
			} else {
				$iban_1 = $iban;
			}
			
			$this->IBAN = $iban;
			$this->IBAN1 = $iban_1;
			
			return true;
		} else {
			$this->BIC = $land;
			$this->IBAN = $land;
			$this->IBAN1 = $land;
		}
	}
	function test_sepa() {
		$this->import_dtaustn ();
		echo '<pre>';
		/*
		 * $this->get_iban_bic(835298, 10070848);
		 * print_r($this);
		 * $this->get_iban_bic(3578762, 10070024);
		 * print_r($this);
		 * #
		 * $this->get_iban_bic(7430192, 10070024);
		 * print_r($this);
		 * #
		 * $this->get_iban_bic(619445, 10070024);
		 * print_r($this);
		 * #
		 * $this->get_iban_bic(2660884, 10070848);
		 * print_r($this);
		 *
		 * $this->get_iban_bic(7435431, 10070024);
		 * print_r($this);
		 *
		 * $this->get_iban_bic(6081319, 10040000);
		 * print_r($this);
		 *
		 * $this->get_iban_bic(4168761, 10070024);
		 * print_r($this);
		 * #
		 * # echo '<hr><br><br><br><br><br><br><br><br><br><br><br><br><pre>';
		 * # print_r($this->konto_info);
		 * #}
		 */
	}
	
	/* sivac_iban('800101561', '10050000', 'DE'); */
	function get_iban_de($kto, $blz, $land = 'DE') {
		/*
		 * Die Berechnung erfolgt in mehreren Schritten. Zuerst wird die Länderkennung um zwei Nullen ergänzt.
		 * Danach wird aus Kontonummer und Bankleitzahl die BBAN kreiert.
		 * Also beispielsweise Bankleitzahl 70090100 und Kontonummer 1234567890 ergeben die BBAN 700901001234567890.
		 * Modulo 97-10.
		 */
		
		/*
		 * Anschließend werden die beiden Alpha-Zeichen der Länderkennung sowie weitere eventuell in der Kontonummer enthaltene Buchstaben in rein numerische Ausdrücke umgewandelt.
		 * Die Grundlage für die Zahlen, die aus den Buchstaben gebildet werden sollen, bildet ihre Position der jeweiligen Alpha-Zeichen im lateinischen Alphabet.
		 * Zu diesem Zahlenwert wird 9 addiert. Die Summe ergibt die Zahl, die den jeweiligen Buchstaben ersetzen soll.
		 * Dementsprechend steht für A (Position 1+9) die Zahl 10, für D (Position 4+9) die 13 und für E (Position 5+9) die 14.
		 * Der Länderkennung DE entspricht also die Ziffernfolge 1314.
		 *
		 * Im nächsten Schritt wird diese Ziffernfolge, ergänzt um die beiden Nullen, an die BBAN gehängt.
		 * Hieraus ergibt sich 700901001234567890131400. Diese bei deutschen Konten immer 24-stellige Zahl wird anschließend Modulo 97 genommen.
		 * Das heißt, es wird der Rest berechnet, der sich bei der Teilung der 24-stelligen Zahl durch 97 ergibt. Das ist für dieses Beispiel 90.
		 * Dieses Ergebnis wird von der nach ISO-Standard festgelegten Zahl 98 subtrahiert.
		 * Ist das Resultat, wie in diesem Beispiel, kleiner als Zehn, so wird der Zahl eine Null vorangestellt, sodass sich wieder ein zweistelliger Wert ergibt.
		 * Somit ist die errechnete Prüfziffer 08. Aus der Länderkennung, der zweistelligen Prüfsumme und der BBAN wird nun die IBAN generiert.
		 * Die ermittelte IBAN lautet in unserem Beispiel: DE08700901001234567890.
		 *
		 * Zur besseren Veranschaulichung das ganze noch einmal zusammengefasst:
		 * Bankleitzahl 70090100
		 * Kontonummer 1234567890
		 * BBAN 700901001234567890
		 * alphanumerische Länderkennung DE
		 * numerische Länderkennung 1314 (D = 13, E = 14)
		 * numerische Länderkennung ergänzt um 00 131400
		 * Prüfsumme 700901001234567890131400
		 * Prüfsumme Modulo 97 90
		 * Prüfziffer 08 (98 - 90, ergänzt um führende Null)
		 * Länderkennung +Prüfziffer + BBAN = IBAN DE08700901001234567890
		 *
		 * Die Prüfung der IBAN erfolgt, indem ihre ersten vier Stellen ans Ende verschoben und die Buchstaben wieder durch 1314 ersetzt werden.
		 * Die Zahl 700901001234567890131408 Modulo 97 muss 1 ergeben. Dann ist die IBAN gültig, was auf unser Beispiel zutrifft.
		 */
		
		/*
		 * Beispiel für Ausnahmebanken Kontonummern
		 *
		 * DE91 1007 0024 0003 5787 62
		 * richtige IBAN: DE43 1007 0024 0357 8762 00
		 * DE43 1007 0024 0357 8762 00
		 * DE43 1007 0024 0357 8762 00
		 *
		 * Falsche IBAN: DE07 1007 0848 0002 6608 84
		 * DE20 1007 0848 0266 0884 00
		 * DE20 1007 0848 0266 0884 00
		 * DEUTDEDBBER
		 */
		
		/* ALNUM Prüfung */
		$err = '';
		if (! ctype_digit ( $kto )) {
			$err .= "Kto $kto nicht nummerisch";
		}
		if (! ctype_digit ( $blz )) {
			$err .= "\nBLZ $blz nicht nummerisch";
		}
		if (! ctype_alpha ( $land )) {
			$err .= "\nLAND $land ist nicht ALPHA";
		}
		
		/* LAND */
		if (strlen ( $land ) > 2) {
			$lk = substr ( $land, 0, 2 );
		} else {
			$lk = $land;
		}
		$lk_zahl = $this->iban_checksum_string_replace ( $lk . '00' );
		
		/* KTO */
		if (strlen ( $kto ) == 10) {
			$kto_neu = $kto;
		}
		if (strlen ( $kto ) < 10) {
			/* Ausnahmebanken bei kurzen Kontonummern */
			if ($blz == '10070024' or $blz == '10070848' or $blz == '10040000' or $blz == '76026000') {
				// $kto_temp = "0".$kto;
				// $kto_neu=str_pad($kto_temp, 10, "0", STR_PAD_RIGHT);
				if (strlen ( $kto ) < 9) {
					$kto_temp = str_pad ( $kto, 8, "0", STR_PAD_LEFT );
				}
				if (strlen ( $kto ) == 9) {
					$kto_temp = str_pad ( $kto, 10, "0", STR_PAD_LEFT );
					// echo "<b>$kto_temp<br></b>";
				}
				$kto_neu = str_pad ( $kto_temp, 10, "0", STR_PAD_RIGHT );
				// echo "$kto $kto_temp $kto_neu <br>";
			} else {
				$kto_neu = str_pad ( $kto, 10, "0", STR_PAD_LEFT );
			}
		}
		if (strlen ( $kto ) > 10) {
			$kto_neu = substr ( $kto, 0, 10 );
		}
		
		/* BLZ */
		if (strlen ( $blz ) > 8) {
			$err .= "\nBLZ zu lang";
			$blz_neu = '';
		}
		if (strlen ( $blz ) == 8) {
			$blz_neu = $blz;
		}
		if (strlen ( $blz ) < 8) {
			$blz_neu = str_pad ( $blz, 8, "0", STR_PAD_RIGHT );
		}
		
		if (empty ( $err )) {
			$bban = $blz_neu . $kto_neu;
			$bban_digit = $bban . $lk_zahl;
			$pz = $this->pz_iban_mod97_10 ( $bban_digit );
			$iban = $lk . $pz . $blz_neu . $kto_neu;
			// echo $err;
			
			return $iban;
		} else {
			// return $err;
		}
	}
	function iban_checksum_string_replace($string) {
		$iban_replace_chars = range ( 'A', 'Z' );
		foreach ( range ( 10, 35 ) as $tempvalue ) {
			$iban_replace_values [] = strval ( $tempvalue );
		}
		return str_replace ( $iban_replace_chars, $iban_replace_values, $string );
	}
	function pz_iban_mod97_10($bban_digit) {
		// $rest = intval($bban_digit % 97);//falsch
		$rest = bcmod ( $bban_digit, 97 );
		$pz_mod = 98 - $rest;
		if ($pz_mod < 10) {
			$pz_mod = str_pad ( $pz_mod, 2, "0", STR_PAD_LEFT );
		}
		return $pz_mod;
	}
	function iban_to_human_format($iban) {
		$human_iban = '';
		for($i = 0; $i < strlen ( $iban ); $i ++) {
			$human_iban .= substr ( $iban, $i, 1 );
			if (($i > 0) && (($i + 1) % 4 == 0)) {
				$human_iban .= ' ';
			}
		}
		return $human_iban;
	}
	function import_dtaustn($objekt_id = 41, $m_adatum = '', $m_udatum = '') {
		if ($m_adatum == '') {
			$m_adatum = '2014-02-01';
		}
		if ($m_udatum == '') {
			$m_udatum = '2014-02-01';
		}
		
		echo '<pre>';
		// $o = new objekt();
		// $o->objekt_informationen($objekt_id);
		// print_r($o);
		// die();
		
		$result = mysql_query ( "SELECT DETAIL_ZUORDNUNG_ID FROM `DETAIL` WHERE `DETAIL_NAME` LIKE 'Einzugsermächtigung' AND `DETAIL_INHALT` LIKE 'JA' AND `DETAIL_AKTUELL` = '1'" );
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			$arr = Array ();
			$z = 0;
			while ( $row = mysql_fetch_assoc ( $result ) ) {
				$mieter = ( object ) $row;
				$mv_id = $mieter->DETAIL_ZUORDNUNG_ID;
				/*
				 * $d = new detail;
				 * $arr[$z]['MV_ID'] = $mv_id;
				 * $arr[$z]['NAME'] = $d->finde_detail_inhalt('MIETVERTRAG', $mv_id, 'Kontoinhaber-AutoEinzug');
				 * $arr[$z]['KONTONR'] = $d->finde_detail_inhalt('MIETVERTRAG', $mv_id, 'Kontonummer-AutoEinzug');
				 * $arr[$z]['BLZ'] = $d->finde_detail_inhalt('MIETVERTRAG', $mv_id, 'BLZ-AutoEinzug');
				 * $arr[$z]['BANKNAME'] = $d->finde_detail_inhalt('MIETVERTRAG', $mv_id, 'Bankname-AutoEinzug');
				 * $arr[$z]['EINZUGSART'] = $d->finde_detail_inhalt('MIETVERTRAG', $mv_id, 'Autoeinzugsart');
				 * $arr[$z]['M_REFERENZ'] = "MV".$mv_id;
				 */
				$mv = new mietvertraege ();
				$mv->get_mietvertrag_infos_aktuell ( $mv_id );
				if ($mv->mietvertrag_aktuell == '1' && $this->check_objekt_aktiv ( $mv->objekt_id ) && $mv->objekt_id == $objekt_id) {
					$o = new objekt ();
					$o->objekt_informationen ( $mv->objekt_id );
					
					$arr [$z] ['GLAEUBIGER_GK_ID'] = $o->geld_konten_arr [0] ['KONTO_ID'];
					$arr [$z] ['BEGUENSTIGTER'] = $o->geld_konten_arr [0] ['BEGUENSTIGTER'];
					
					$d = new detail ();
					$arr [$z] ['GLAEUBIGER_ID'] = $d->finde_detail_inhalt ( 'GELD_KONTEN', $arr [$z] ['GLAEUBIGER_GK_ID'], 'GLAEUBIGER_ID' );
					
					$arr [$z] ['MV_ID'] = $mv_id;
					$arr [$z] ['NAME'] = $mv->ls_konto_inhaber;
					$arr [$z] ['KONTONR'] = $mv->ls_konto_nummer;
					$arr [$z] ['BLZ'] = $mv->ls_blz;
					$arr [$z] ['EINZUGSART'] = $mv->ls_autoeinzugsart;
					$arr [$z] ['M_REFERENZ'] = "MV" . $mv_id;
					$arr [$z] ['IBAN'] = $mv->ls_iban;
					$arr [$z] ['BIC'] = $mv->ls_bic;
					$arr [$z] ['BANKNAME'] = $mv->ls_bankname_sep_k;
					$arr [$z] ['ANSCHRIFT'] = "$mv->haus_strasse $mv->haus_nr, $mv->haus_plz $mv->haus_stadt";
					if (! isset ( $mv->haus_strasse )) {
						die ( "MV nicht in Ordnung, strasse prüfen $mv_id" );
					}
					
					$arr [$z] ['mietvertrag_aktuell'] = $mv->mietvertrag_aktuell;
					print_r ( $mv );
					$z ++;
				} // end if MV aktuell
					  // print_r($konto_info);
			}
			
			print_r ( $arr );
			$anz = count ( $arr );
			for($a = 0; $a < $anz; $a ++) {
				$last_id = last_id2 ( 'SEPA_MANDATE', 'M_ID' ) + 1;
				$m_r = $arr [$a] ['M_REFERENZ'];
				$g_id = $arr [$a] ['GLAEUBIGER_ID'];
				$g_gk_id = $arr [$a] ['GLAEUBIGER_GK_ID'];
				$beg = $arr [$a] ['BEGUENSTIGTER'];
				$name = $arr [$a] ['NAME'];
				$ans = $arr [$a] ['ANSCHRIFT'];
				$kto = $arr [$a] ['KONTONR'];
				$blz = $arr [$a] ['BLZ'];
				$iban = $arr [$a] ['IBAN'];
				$bic = $arr [$a] ['BIC'];
				$bank = $arr [$a] ['BANKNAME'];
				$eart = $arr [$a] ['EINZUGSART'];
				$mv_id = $arr [$a] ['MV_ID'];
				$sql = "INSERT INTO `SEPA_MANDATE` VALUES (NULL, '$last_id', '$m_r', '$g_id', '$g_gk_id', '$beg', '$name', '$ans', '$kto', '$blz', '$iban', '$bic', '$bank', '$m_udatum', '$m_adatum', '9999-12-31', 'WIEDERKEHREND', 'MIETZAHLUNG', '$eart', 'MIETVERTRAG', '$mv_id', '1');";
				echo "$sql<br>";
				$result = mysql_query ( $sql );
			}
		}
	}
	function check_objekt_aktiv($objekt_id) {
		$result = mysql_query ( "SELECT * FROM `OBJEKT` WHERE `OBJEKT_ID`=$objekt_id && OBJEKT_AKTUELL='1'" );
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			return true;
		} else {
			return false;
		}
	}
	function get_mandate_arr($nutzungsart = 'Alle') {
		if (! isset ( $_SESSION ['geldkonto_id'] ) && $nutzungsart != 'Alle') {
			$_SESSION ['last_url'] = '?daten=sepa&option=mandate_mieter';
			fehlermeldung_ausgeben ( 'Geldkonto wählen' );
			die ();
		}
		$datum_heute = date ( "Y-m-d" );
		if ($nutzungsart == 'Alle') {
			$result = mysql_query ( "SELECT * FROM `SEPA_MANDATE` WHERE `AKTUELL` = '1' AND M_EDATUM>='$datum_heute' ORDER BY NAME ASC" );
		} else {
			$gk_id = $_SESSION ['geldkonto_id'];
			$result = mysql_query ( "SELECT * FROM `SEPA_MANDATE` WHERE `AKTUELL` = '1' && M_EDATUM>='$datum_heute' && NUTZUNGSART='$nutzungsart' && GLAEUBIGER_GK_ID='$gk_id' ORDER BY NAME ASC" );
		}
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			while ( $row = mysql_fetch_assoc ( $result ) )
				$arr [] = $row;
			return $arr;
		}
	}
	function alle_mandate_anzeigen_kurz($nutzungsart = 'Alle') {
		if (! isset ( $_SESSION ['geldkonto_id'] ) && $nutzungsart != 'Alle') {
			$_SESSION ['last_url'] = '?daten=sepa&option=mandate_mieter';
			fehlermeldung_ausgeben ( 'Geldkonto wählen' );
			die ();
		}
		$datum_heute = date ( "Y-m-d" );
		if ($nutzungsart == 'Alle') {
			$result = mysql_query ( "SELECT * FROM `SEPA_MANDATE` WHERE `AKTUELL` = '1' AND M_EDATUM>='$datum_heute' ORDER BY NAME ASC" );
		} else {
			$gk_id = $_SESSION ['geldkonto_id'];
			$result = mysql_query ( "SELECT * FROM `SEPA_MANDATE` WHERE `AKTUELL` = '1' &&  M_EDATUM>='$datum_heute' AND NUTZUNGSART='$nutzungsart' && GLAEUBIGER_GK_ID='$gk_id' ORDER BY NAME ASC" );
		}
		
		$monat = date ( "m" );
		$jahr = date ( "Y" );
		
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			
			if ($nutzungsart == 'MIETZAHLUNG') {
				
				echo "<table class=\"sortable\">";
				echo "<thead><tr><th>NR</th><th>EINHEIT</th><th>Name</th><th>REF</th><th>NUTZUNG</th><th>EINZUGSART</th><th>Anschrift</th><th>IBAN DB</th><th>BIC</th></tr></thead>";
				$z = 0;
				$zz = 0;
				$datensaetze = 0;
				$summe_ziehen_alle = 0.00;
				$summe_saldo_alle = 0.00;
				$summe_diff_alle = 0.00;
				while ( $row = mysql_fetch_assoc ( $result ) ) {
					$z ++;
					$zz ++; // Zeile
					       
					// $mand = (object) $row;
					$row ['IBAN1'] = chunk_split ( $row ['IBAN'], 4, ' ' );
					/* TEST IBAN */
					/*
					 * $tsep = new sepa();
					 * $tsep->IBAN ='';
					 * $tsep->IBAN_A ='';
					 * $tsep->get_iban_bic($row['KONTONR'], $row['BLZ']);
					 * if($row['IBAN'] != $tsep->IBAN){
					 * $tsep->IBAN_A = "<b>$tsep->IBAN<br>$row[IBAN]<br>$row[KONTONR]<br>$row[BLZ] $row[DAT]</b>";
					 * }else{
					 * $tsep->IBAN_A = $tsep->IBAN;
					 * }
					 */
					/*
					 * $sep_alt = new sepa();
					 * $sep_alt->get_iban_bicALTFALSCH($row['KONTONR'], $row['BLZ']);
					 * if($tsep->IBAN != $sep_alt->IBAN){
					 * $sep_alt->IBAN_A = "<b>$sep_alt->IBAN</b>";
					 * }else{
					 * $sep_alt->IBAN_A = $sep_alt->IBAN;
					 * }
					 */
					
					// ######################
					$mv = new mietvertraege ();
					$mv->get_mietvertrag_infos_aktuell ( $row ['M_KOS_ID'] );
					// echo '<pre>';
					// print_r($mv);
					// die();
					$mandat_status = $this->get_mandat_seq_status ( $row ['M_REFERENZ'], $row ['IBAN'] );
					$link_nutzungen = "<a href=\"?daten=sepa&option=mandat_nutzungen_anzeigen&m_ref=$row[M_REFERENZ]\">$mandat_status</a>";
					/* Saldo berechnen */
					// $mz = new miete();
					// $mz->mietkonto_berechnung($row['M_KOS_ID']);
					
					echo "<tr class=\"zeile$z\"><td>$zz.</td><td><a href=\"?daten=sepa&option=mandat_edit_mieter&mref_dat=$row[DAT]\">$mv->einheit_kurzname</a></td><td>$row[NAME]</td><td>$row[M_REFERENZ]</td><td>$link_nutzungen</td><td>$row[EINZUGSART]</td></td><td>$row[ANSCHRIFT]</td><td>$row[IBAN]<br>$row[IBAN1]</td><td>$row[BIC]</td></tr>";
					// $mz->erg = 0.00;
					$diff = 0.00;
					
					/* Zeilenfarbetausch */
					if ($z == 2) {
						$z = 0;
					}
				}
				$summe_ziehen_alle_a = nummer_punkt2komma_t ( $summe_ziehen_alle );
				$summe_saldo_alle_a = nummer_punkt2komma_t ( $summe_saldo_alle );
				$summe_diff_alle_a = nummer_punkt2komma_t ( $summe_diff_alle );
				// echo "<tfoot><tr><th colspan=\"6\"><b>SUMMEN ANZAHL DS: $datensaetze</b></th><th><b>$summe_ziehen_alle_a</b></th><th>$summe_saldo_alle_a</th><th>$summe_diff_alle_a</th><th colspan=\"5\"></th></tr></tfoot>";
				
				echo "</table>";
				/*
				 * $f = new formular();
				 * $f->erstelle_formular('SEPA-Datei', '');
				 * $js='';
				 * $f->check_box_js('sammelbetrag', '1', 'Sammelbetrag', $js, 'checked');
				 * $f->hidden_feld('option', 'sepa_download');
				 * $f->send_button('Button', 'SEPA-Datei erstellen');
				 * $f->ende_formular();
				 */
			}
			/* ENDE MIETZAHLUNGEN */
			
			/* ANFANG RECHNUNGEN */
			if ($nutzungsart == 'RECHNUGEN') {
				echo "Ansicht LS für Rechnungen, folgt noch!!!";
			}
			/* ENDE RECHNUNGEN */
			/* ANFANG HAUSGELD */
			if ($nutzungsart == 'HAUSGELD') {
				echo "Ansicht LS für Hausgeld, folgt noch!!!<br>";
				echo "<table class=\"sortable\">";
				echo "<thead><tr><th>EINHEIT</th><th>Name</th><th>REF</th><th>NUTZUNG</th><th>EINZUGSART</th><th>ZIEHEN</th><th>SALDO</th><th>DIFF</th><th>Anschrift</th><th>IBAN</th><th>BIC</th></tr></thead>";
				$z = 0;
				$summe_ziehen_alle = 0.00;
				$summe_saldo_alle = 0.00;
				$summe_diff_alle = 0.00;
				while ( $row = mysql_fetch_assoc ( $result ) ) {
					$z ++;
					$mand = ( object ) $row;
					$mand->IBAN1 = chunk_split ( $mand->IBAN, 4, ' ' );
					$mandat_status = $this->get_mandat_seq_status ( $mand->M_REFERENZ, $mand->IBAN );
					$link_nutzungen = "<a href=\"?daten=sepa&option=mandat_nutzungen_anzeigen&m_ref=$mand->M_REFERENZ\">$mandat_status</a>";
					
					$weg = new weg ();
					$einheit_id = $weg->get_einheit_id_from_eigentuemer ( $mand->M_KOS_ID );
					$e = new einheit ();
					$e->get_einheit_info ( $einheit_id );
					// $weg->get_wg_info($monat, $jahr, 'Einheit', $einheit_id, 'Hausgeld');
					// $weg->hausgeld_kontoauszug_stand($mand->M_KOS_ID);
					$weg->get_eigentuemer_saldo ( $mand->M_KOS_ID, $einheit_id );
					// die();
					// echo $mand->M_KOS_ID;
					// echo '<pre>';
					// print_r($weg);
					// die();
					// if($weg->hg_erg<0.00)
					// $weg->hg_erg_a = nummer_punkt2komma_t($weg->hg_erg);
					// echo "$mand->NAME<br>";
					// $weg->Wohngeld_soll_a //monatliches Hausgeld
					
					if ($mand->EINZUGSART == 'Aktuelles Saldo komplett') {
						if ($weg->hg_erg < 0) {
							$summe_zu_ziehen = substr ( $weg->hg_erg, 1 );
							$diff = $summe_zu_ziehen + $weg->hg_erg;
						} else {
							$summe_zu_ziehen = 0.00;
							$diff = $summe_zu_ziehen + $weg->hg_erg;
						}
					}
					
					if ($mand->EINZUGSART == 'Nur die Summe aus Vertrag') {
						
						$summe_zu_ziehen = substr ( $weg->soll_aktuell, 1 );
						$diff = $summe_zu_ziehen + $weg->hg_erg;
					}
					
					$summe_zu_ziehen_a = nummer_punkt2komma_t ( $summe_zu_ziehen );
					$summe_saldo_alle += $weg->hg_erg;
					$summe_ziehen_alle += $summe_zu_ziehen;
					$summe_diff_alle += $diff;
					$diff_a = nummer_punkt2komma ( $diff );
					echo "<tr class=\"zeile$z\"><td><a href=\"?daten=sepa&option=mandat_edit_mieter&mref_dat=$mand->DAT\">$e->einheit_kurzname</a></td><td>$mand->NAME</td><td>$mand->M_REFERENZ</td><td>$link_nutzungen</td><td>$mand->EINZUGSART</td></td><td>$summe_zu_ziehen_a</td><td>$weg->hg_erg_a</td><td>$diff_a</td><td>$mand->ANSCHRIFT</td><td>$mand->IBAN<br>$mand->IBAN1</td><td>$mand->BIC</td></tr>";
					/* Zeilenfarbetausch */
					if ($z == 2) {
						$z = 0;
					}
				}
				$summe_ziehen_alle_a = nummer_punkt2komma_t ( $summe_ziehen_alle );
				$summe_saldo_alle_a = nummer_punkt2komma_t ( $summe_saldo_alle );
				$summe_diff_alle_a = nummer_punkt2komma_t ( $summe_diff_alle );
				echo "<tfoot><tr><th colspan=\"5\"><b>SUMMEN</b></th><th><b>$summe_ziehen_alle_a</b></th><th>$summe_saldo_alle_a</th><th>$summe_diff_alle_a</th><th colspan=\"3\"></th></tr></tfoot>";
				echo "</table>";
			}
			
			if ($nutzungsart == 'Alle') {
				echo "Übersicht alle Mandate, in Arbeit!!!!";
			}
			
			if (isset ( $summe_ziehen_alle ) && $summe_ziehen_alle > 0.00) {
				$f = new formular ();
				$f->erstelle_formular ( 'SEPA-Datei', '' );
				$js = '';
				$f->check_box_js ( 'sammelbetrag', '1', 'Sammelbetrag', $js, 'checked' );
				$f->hidden_feld ( 'option', 'sepa_download' );
				$f->hidden_feld ( 'nutzungsart', $nutzungsart );
				$f->send_button ( 'Btn-SEPApdf', "PDF-Begleitzettell" );
				$f->send_button ( 'Button', "SEPA-Datei für $nutzungsart erstellen" );
				$f->ende_formular ();
			}
			
			unset ( $row );
		} else {
			fehlermeldung_ausgeben ( "Keine Mandate für $nutzungsart in der Datenbank!" );
		}
		// unset($mand);
		// unset($tsep);
	}
	function alle_mandate_anzeigen($nutzungsart = 'Alle') {
		if (! isset ( $_SESSION ['geldkonto_id'] ) && $nutzungsart != 'Alle') {
			$_SESSION ['last_url'] = '?daten=sepa&option=mandate_mieter';
			fehlermeldung_ausgeben ( 'Geldkonto wählen' );
			die ();
		}
		$datum_heute = date ( "Y-m-d" );
		if ($nutzungsart == 'Alle') {
			$result = mysql_query ( "SELECT * FROM `SEPA_MANDATE` WHERE `AKTUELL` = '1' AND M_EDATUM>='$datum_heute' ORDER BY NAME ASC" );
		} else {
			$gk_id = $_SESSION ['geldkonto_id'];
			$result = mysql_query ( "SELECT * FROM `SEPA_MANDATE` WHERE `AKTUELL` = '1' && NUTZUNGSART='$nutzungsart' AND M_EDATUM>='$datum_heute' && GLAEUBIGER_GK_ID='$gk_id' ORDER BY NAME ASC" );
		}
		
		$monat = date ( "m" );
		$jahr = date ( "Y" );
		
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			
			if ($nutzungsart == 'MIETZAHLUNG') {
				
				echo "<table class=\"sortable\">";
				echo "<thead><tr><th>NR</th><th>EINHEIT</th><th>Name</th><th>REF</th><th>NUTZUNG</th><th>EINZUGSART</th><th>ZIEHEN</th><th>SALDO</th><th>SALDO NACH</th><th>Anschrift</th><th>IBAN DB</th><th>BIC</th></tr></thead>";
				$z = 0;
				$zz = 0;
				$datensaetze = 0;
				$summe_ziehen_alle = 0.00;
				$summe_saldo_alle = 0.00;
				$summe_diff_alle = 0.00;
				while ( $row = mysql_fetch_assoc ( $result ) ) {
					$z ++;
					$zz ++; // Zeile
					       
					// $mand = (object) $row;
					$row ['IBAN1'] = chunk_split ( $row ['IBAN'], 4, ' ' );
					/* TEST IBAN */
					/*
					 * $tsep = new sepa();
					 * $tsep->IBAN ='';
					 * $tsep->IBAN_A ='';
					 * $tsep->get_iban_bic($row['KONTONR'], $row['BLZ']);
					 * if($row['IBAN'] != $tsep->IBAN){
					 * $tsep->IBAN_A = "<b>$tsep->IBAN<br>$row[IBAN]<br>$row[KONTONR]<br>$row[BLZ] $row[DAT]</b>";
					 * }else{
					 * $tsep->IBAN_A = $tsep->IBAN;
					 * }
					 */
					/*
					 * $sep_alt = new sepa();
					 * $sep_alt->get_iban_bicALTFALSCH($row['KONTONR'], $row['BLZ']);
					 * if($tsep->IBAN != $sep_alt->IBAN){
					 * $sep_alt->IBAN_A = "<b>$sep_alt->IBAN</b>";
					 * }else{
					 * $sep_alt->IBAN_A = $sep_alt->IBAN;
					 * }
					 */
					
					// ######################
					$mv = new mietvertraege ();
					$mv->get_mietvertrag_infos_aktuell ( $row ['M_KOS_ID'] );
					// echo '<pre>';
					// print_r($mv);
					// die();
					$mandat_status = $this->get_mandat_seq_status ( $row ['M_REFERENZ'], $row ['IBAN'] );
					$link_nutzungen = "<a href=\"?daten=sepa&option=mandat_nutzungen_anzeigen&m_ref=$row[M_REFERENZ]\">$mandat_status</a>";
					/* Saldo berechnen */
					$mz = new miete ();
					$mz->mietkonto_berechnung ( $row ['M_KOS_ID'] );
					
					if ($row ['EINZUGSART'] == 'Aktuelles Saldo komplett') {
						if ($mz->erg < 0) {
							$summe_zu_ziehen = substr ( $mz->erg, 1 );
							$diff = nummer_punkt2komma_t ( $summe_zu_ziehen + $mz->erg );
							$datensaetze ++;
						} else {
							$summe_zu_ziehen = 0.00;
							$diff = $mz->erg;
						}
					}
					
					if ($row ['EINZUGSART'] == 'Nur die Summe aus Vertrag') {
						
						$mk = new mietkonto ();
						$summe_zu_ziehen_arr = explode ( '|', $mk->summe_forderung_monatlich ( $row ['M_KOS_ID'], $monat, $jahr ) );
						
						$summe_zu_ziehen = $summe_zu_ziehen_arr [0];
						$diff = $summe_zu_ziehen + $mz->erg;
						$datensaetze ++;
					}
					
					if ($row ['EINZUGSART'] == 'Ratenzahlung') {
						$mk = new mietkonto ();
						$summe_zu_ziehen_arr = explode ( '|', $mk->summe_forderung_monatlich ( $row ['M_KOS_ID'], $monat, $jahr ) );
						
						$summe_raten = $mk->summe_rate_monatlich ( $row ['M_KOS_ID'], $monat, $jahr );
						$summe_zu_ziehen = $summe_zu_ziehen_arr [0] + $summe_raten;
						$diff = $summe_zu_ziehen + $mz->erg;
						$datensaetze ++;
					}
					
					/*
					 * aus DTAUS
					 * $summe_raten = $mk->summe_rate_monatlich($mv_id, $monat, $jahr);
					 * $forderung_monatlich = $mk->summe_forderung_monatlich($mv_id, $monat, $jahr);
					 * $mietsumme = $summe_raten + $forderung_monatlich;
					 * $this->alle_teilnehmer[$a]['Autoeinzugsart'] = $einzugsart;
					 */
					
					if (! $summe_zu_ziehen) {
						// $summe_zu_ziehen = 0.00;
						/* NICHT ZIEHEN */
						// echo '<pre>';
						// print_r($mz);
						// die("$mand->M_KOS_ID $mand->EINZUGSART");
					}
					$summe_zu_ziehen_a = nummer_punkt2komma_t ( $summe_zu_ziehen );
					$summe_saldo_alle += $mz->erg;
					$summe_ziehen_alle += $summe_zu_ziehen;
					$summe_diff_alle += $diff;
					echo "<tr class=\"zeile$z\"><td>$zz.</td><td><a href=\"?daten=sepa&option=mandat_edit_mieter&mref_dat=$row[DAT]\">$mv->einheit_kurzname</a></td><td>$row[NAME]</td><td>$row[M_REFERENZ]</td><td>$link_nutzungen</td><td>$row[EINZUGSART]</td></td><td>$summe_zu_ziehen_a</td><td>$mz->erg</td><td>$diff</td><td>$row[ANSCHRIFT]</td><td>$row[IBAN]<br>$row[IBAN1]</td><td>$row[BIC]</td></tr>";
					$mz->erg = 0.00;
					$diff = 0.00;
					
					/* Zeilenfarbetausch */
					if ($z == 2) {
						$z = 0;
					}
				}
				$summe_ziehen_alle_a = nummer_punkt2komma_t ( $summe_ziehen_alle );
				$summe_saldo_alle_a = nummer_punkt2komma_t ( $summe_saldo_alle );
				$summe_diff_alle_a = nummer_punkt2komma_t ( $summe_diff_alle );
				echo "<tfoot><tr><th colspan=\"6\"><b>SUMMEN ANZAHL DS: $datensaetze</b></th><th><b>$summe_ziehen_alle_a</b></th><th>$summe_saldo_alle_a</th><th>$summe_diff_alle_a</th><th colspan=\"5\"></th></tr></tfoot>";
				
				echo "</table>";
				/*
				 * $f = new formular();
				 * $f->erstelle_formular('SEPA-Datei', '');
				 * $js='';
				 * $f->check_box_js('sammelbetrag', '1', 'Sammelbetrag', $js, 'checked');
				 * $f->hidden_feld('option', 'sepa_download');
				 * $f->send_button('Button', 'SEPA-Datei erstellen');
				 * $f->ende_formular();
				 */
			}
			/* ENDE MIETZAHLUNGEN */
			
			/* ANFANG RECHNUNGEN */
			if ($nutzungsart == 'RECHNUGEN') {
				echo "Ansicht LS für Rechnungen, folgt noch!!!";
			}
			/* ENDE RECHNUNGEN */
			/* ANFANG HAUSGELD */
			if ($nutzungsart == 'HAUSGELD') {
				echo "Ansicht LS für Hausgeld, folgt noch!!!<br>";
				echo "<table class=\"sortable\">";
				echo "<thead><tr><th>EINHEIT</th><th>Name</th><th>REF</th><th>NUTZUNG</th><th>EINZUGSART</th><th>ZIEHEN</th><th>SALDO</th><th>DIFF</th><th>Anschrift</th><th>IBAN</th><th>BIC</th></tr></thead>";
				$z = 0;
				$summe_ziehen_alle = 0.00;
				$summe_saldo_alle = 0.00;
				$summe_diff_alle = 0.00;
				while ( $row = mysql_fetch_assoc ( $result ) ) {
					$z ++;
					$mand = ( object ) $row;
					$mand->IBAN1 = chunk_split ( $mand->IBAN, 4, ' ' );
					$mandat_status = $this->get_mandat_seq_status ( $mand->M_REFERENZ, $mand->IBAN );
					$link_nutzungen = "<a href=\"?daten=sepa&option=mandat_nutzungen_anzeigen&m_ref=$mand->M_REFERENZ\">$mandat_status</a>";
					
					$weg = new weg ();
					$einheit_id = $weg->get_einheit_id_from_eigentuemer ( $mand->M_KOS_ID );
					$e = new einheit ();
					$e->get_einheit_info ( $einheit_id );
					// $weg->get_wg_info($monat, $jahr, 'Einheit', $einheit_id, 'Hausgeld');
					// $weg->hausgeld_kontoauszug_stand($mand->M_KOS_ID);
					$weg->get_eigentuemer_saldo ( $mand->M_KOS_ID, $einheit_id );
					// die();
					// echo $mand->M_KOS_ID;
					// echo '<pre>';
					// print_r($weg);
					// die();
					// if($weg->hg_erg<0.00)
					// $weg->hg_erg_a = nummer_punkt2komma_t($weg->hg_erg);
					// echo "$mand->NAME<br>";
					// $weg->Wohngeld_soll_a //monatliches Hausgeld
					
					if ($mand->EINZUGSART == 'Aktuelles Saldo komplett') {
						if ($weg->hg_erg < 0) {
							$summe_zu_ziehen = $weg->soll_aktuell;
							$diff = 0.00;
						} else {
							$summe_zu_ziehen = $weg->soll_aktuell;
							$summe_zu_ziehen = 0.00;
							$diff = 0.00;
						}
					}
					
					if ($mand->EINZUGSART == 'Nur die Summe aus Vertrag') {
						
						$summe_zu_ziehen = $weg->soll_aktuell;
						$diff = 0.00;
					}
					
					$summe_zu_ziehen_a = nummer_punkt2komma_t ( $summe_zu_ziehen );
					$summe_saldo_alle += $weg->hg_erg;
					$summe_ziehen_alle += $summe_zu_ziehen;
					$summe_diff_alle += $diff;
					$diff_a = nummer_punkt2komma ( $diff );
					echo "<tr class=\"zeile$z\"><td><a href=\"?daten=sepa&option=mandat_edit_mieter&mref_dat=$mand->DAT\">$e->einheit_kurzname</a></td><td>$mand->NAME</td><td>$mand->M_REFERENZ</td><td>$link_nutzungen</td><td>$mand->EINZUGSART</td></td><td>$summe_zu_ziehen_a</td><td>$weg->hg_erg_a</td><td>$diff_a</td><td>$mand->ANSCHRIFT</td><td>$mand->IBAN<br>$mand->IBAN1</td><td>$mand->BIC</td></tr>";
					/* Zeilenfarbetausch */
					if ($z == 2) {
						$z = 0;
					}
				}
				$summe_ziehen_alle_a = nummer_punkt2komma_t ( $summe_ziehen_alle );
				$summe_saldo_alle_a = nummer_punkt2komma_t ( $summe_saldo_alle );
				$summe_diff_alle_a = nummer_punkt2komma_t ( $summe_diff_alle );
				echo "<tfoot><tr><th colspan=\"5\"><b>SUMMEN</b></th><th><b>$summe_ziehen_alle_a</b></th><th>$summe_saldo_alle_a</th><th>$summe_diff_alle_a</th><th colspan=\"3\"></th></tr></tfoot>";
				echo "</table>";
			}
			
			if ($nutzungsart == 'Alle') {
				echo "Übersicht alle Mandate, in Arbeit!!!!";
			}
			
			if (isset ( $summe_ziehen_alle ) && $summe_ziehen_alle > 0.00) {
				$f = new formular ();
				$f->erstelle_formular ( 'SEPA-Datei', '' );
				$js = '';
				$f->check_box_js ( 'sammelbetrag', '1', 'Sammelbetrag', $js, 'checked' );
				$f->hidden_feld ( 'option', 'sepa_download' );
				$f->hidden_feld ( 'nutzungsart', $nutzungsart );
				$f->send_button ( 'Btn-SEPApdf', "PDF-Begleitzettell" );
				$f->send_button ( 'Button', "SEPA-Datei für $nutzungsart erstellen" );
				$f->ende_formular ();
			}
			
			unset ( $row );
		} else {
			fehlermeldung_ausgeben ( "Keine Mandate für $nutzungsart in der Datenbank!" );
		}
		// unset($mand);
		// unset($tsep);
	}
	function get_mandat_infos($dat) {
		if (isset ( $this->mand )) {
			unset ( $this->mand );
		}
		$result = mysql_query ( "SELECT * FROM `SEPA_MANDATE` WHERE `DAT` ='$dat'" );
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			$row = mysql_fetch_assoc ( $result );
			$this->mand = ( object ) $row;
		}
	}
	function get_mandat_infos_mref($m_ref) {
		if (isset ( $this->mand )) {
			unset ( $this->mand );
		}
		$result = mysql_query ( "SELECT * FROM `SEPA_MANDATE` WHERE `M_REFERENZ` ='$m_ref' && AKTUELL='1' ORDER BY DAT LIMIT 0,1" );
		// echo "SELECT * FROM `SEPA_MANDATE` WHERE `M_REFERENZ` ='$m_ref' && AKTUELL='1' ORDER BY DAT LIMIT 0,1";
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			$row = mysql_fetch_assoc ( $result );
			$this->mand = ( object ) $row;
		}
	}
	function form_mandat_mieter_neu($gk_id) {
		$f = new formular ();
		$e = new einheit ();
		$mv = new mietvertraege ();
		$f->erstelle_formular ( 'Neues Mietermandat erfassen', '' );
		
		$gk = new gk ();
		
		$geld_konto_info = new geldkonto_info ();
		$geld_konto_info->geld_konto_details ( $gk_id );
		$f->hidden_feld ( 'BEGUENSTIGTER', $geld_konto_info->konto_beguenstigter );
		
		$objekt_id = $gk->get_objekt_id ( $gk_id );
		if (! isset ( $objekt_id )) {
			fehlermeldung_ausgeben ( "Objekt nicht gefunden!!!<br>, siehe Geldkontozuweisung zum Objekt!!!" );
			die ();
		}
		$_SESSION ['objekt_id'] = $objekt_id;
		// print_r($_SESSION);
		$d = new detail ();
		$glaeubiger_id = $d->finde_detail_inhalt ( 'GELD_KONTEN', $gk_id, 'GLAEUBIGER_ID' );
		if ($glaeubiger_id == false) {
			die ( 'Zum Geldkonto wurde die Glüubiger ID nicht gespeichert, siehe DETAILS vomn GK' );
		}
		$f->hidden_feld ( 'GLAEUBIGER_ID', $glaeubiger_id );
		$f->text_feld_inaktiv ( 'Begünstigter', 'BEGBEZ', $geld_konto_info->konto_beguenstigter, 35, 'BEGBEZ' );
		$f->text_feld_inaktiv ( 'Ihre GläubigerID', 'GLAEUBIGER_ID', $glaeubiger_id, 35, 'GLAEUBIGER_ID' );
		
		$f->text_feld_inaktiv ( 'Mandatsreferenz', 'M_REF', '', 35, 'M_REF' );
		$js = "onclick=\"var show = document.getElementById('M_REF');show.value = 'MV' + this.value;\"";
		$this->dropdown_mieter ( $objekt_id, 'Mieter wählen', 'mv_id', 'mv_id', $js );
		$mv->autoeinzugsarten ( 'Einzugsart', 'einzugsart', 'einzugsart' );
		
		$f->text_feld ( "Kontoinhaber", "NAME", "", "50", 'NAME', '' );
		$f->text_feld ( "Anschrift d. Kontoinhabers", "ANSCHRIFT", "", "50", 'ANSSCHRIFT', '' );
		$f->text_feld ( "IBAN", "IBAN", "", "50", 'IBAN', '' );
		$f->text_feld ( "BIC", "BIC", "", "50", 'BIC', '' );
		$f->text_feld ( "Bank", "BANK", "", "50", 'BANK', '' );
		$heute = date ( "d.m.Y" );
		$f->datum_feld ( 'Datum Unterschrift', 'M_UDATUM', $heute, 'M_UDATUM' );
		$f->datum_feld ( 'Datum Gültigkeit', 'M_ADATUM', $heute, 'A_UDATUM' );
		$f->hidden_feld ( 'GK_ID', $gk_id );
		$f->hidden_feld ( 'M_KOS_TYP', 'Mietvertrag' );
		$f->hidden_feld ( 'option', 'mandat_mieter_neu_send' );
		$f->send_button ( 'Button', 'Mandat erstellen' );
		$f->ende_formular ();
		// $f->fieldset_ende();
	}
	function form_mandat_hausgeld_neu($gk_id) {
		$f = new formular ();
		$e = new einheit ();
		$mv = new mietvertraege ();
		$f->erstelle_formular ( 'Neues Hausgeldmandat erfassen', '' );
		
		$gk = new gk ();
		
		$geld_konto_info = new geldkonto_info ();
		$geld_konto_info->geld_konto_details ( $gk_id );
		$f->hidden_feld ( 'BEGUENSTIGTER', $geld_konto_info->konto_beguenstigter );
		
		$objekt_id = $gk->get_objekt_id ( $gk_id );
		if (! isset ( $objekt_id )) {
			fehlermeldung_ausgeben ( "Objekt nicht gefunden!!!<br>, siehe Geldkontozuweisung zum Objekt!!!" );
			die ();
		}
		$_SESSION ['objekt_id'] = $objekt_id;
		// print_r($_SESSION);
		$d = new detail ();
		$glaeubiger_id = $d->finde_detail_inhalt ( 'GELD_KONTEN', $gk_id, 'GLAEUBIGER_ID' );
		if ($glaeubiger_id == false) {
			die ( 'Zum Geldkonto wurde die Gläubiger ID nicht gespeichert, siehe DETAILS vomn GK' );
		}
		$f->hidden_feld ( 'GLAEUBIGER_ID', $glaeubiger_id );
		$f->text_feld_inaktiv ( 'Begünstigter', 'BEGBEZ', $geld_konto_info->konto_beguenstigter, 35, 'BEGBEZ' );
		$f->text_feld_inaktiv ( 'Ihre GläubigerID', 'GLAEUBIGER_ID', $glaeubiger_id, 35, 'GLAEUBIGER_ID' );
		
		$f->text_feld_inaktiv ( 'Mandatsreferenz', 'M_REF', '', 35, 'M_REF' );
		$js = "onclick=\"var show = document.getElementById('M_REF');show.value = 'WEG-ET' + this.value;\"";
		// $this->dropdown_mieter($objekt_id, 'Mieter wählen', 'mv_id', 'mv_id', $js);
		$this->dropdown_et_vorwahl ( 'x', $objekt_id, "Eigentümer wählen OBJ_ID $objekt_id", 'mv_id', 'mv_id', $js );
		$mv->autoeinzugsarten ( 'Einzugsart', 'einzugsart', 'einzugsart' );
		
		$f->text_feld ( "Kontoinhaber", "NAME", "", "50", 'NAME', '' );
		$f->text_feld ( "Anschrift d. Kontoinhabers", "ANSCHRIFT", "", "50", 'ANSSCHRIFT', '' );
		$f->text_feld ( "IBAN", "IBAN", "", "50", 'IBAN', '' );
		$f->text_feld ( "BIC", "BIC", "", "50", 'BIC', '' );
		$f->text_feld ( "Bank", "BANK", "", "50", 'BANK', '' );
		$heute = date ( "d.m.Y" );
		$f->datum_feld ( 'Datum Unterschrift', 'M_UDATUM', $heute, 'M_UDATUM' );
		$f->datum_feld ( 'Datum Gültigkeit', 'M_ADATUM', $heute, 'A_UDATUM' );
		$f->hidden_feld ( 'GK_ID', $gk_id );
		$f->hidden_feld ( 'M_KOS_TYP', 'Eigentuemer' );
		$f->hidden_feld ( 'option', 'mandat_mieter_neu_send' );
		$f->send_button ( 'Button', 'Mandat erstellen' );
		$f->ende_formular ();
		// $f->fieldset_ende();
	}
	function form_mandat_mieter_edit($dat) {
		$this->get_mandat_infos ( $dat );
		// print_r($this);
		// echo $this->mand->GLAEUBIGER_ID;
		// die();
		
		$f = new formular ();
		$e = new einheit ();
		$mv = new mietvertraege ();
		$f->erstelle_formular ( 'Mietermandat ändern', '' );
		$gk = new gk ();
		$objekt_id = $gk->get_objekt_id ( $this->mand->GLAEUBIGER_GK_ID );
		// $js = "onchange=\"var show = document.getElementById('M_REF');show.value = 'GK' + this.value;\"";
		$js = "onchange=\"get_detail_inhalt('GELD_KONTEN', this.value, 'GLAEUBIGER_ID', 'GLAEUBIGER_ID'); daj3('ajax/ajax_info.php?option=get_gk_infos&var=konto_beguenstigter&gk_id=' + this.value, 'BEGUENSTIGTER');\"";
		$gk->dropdown_geldkonten_alle_vorwahl ( 'Referenzgeldkonto wählen', 'GK_ID', 'GK_ID', $this->mand->GLAEUBIGER_GK_ID, $js );
		// $this->d
		$geld_konto_info = new geldkonto_info ();
		$geld_konto_info->geld_konto_details ( $this->mand->GLAEUBIGER_GK_ID );
		
		$f->text_feld ( "Gläubiger ID", "GLAEUBIGER_ID", $this->mand->GLAEUBIGER_ID, "50", 'GLAEUBIGER_ID', '' );
		$f->text_feld ( 'Begünstigter', 'BEGUENSTIGTER', $geld_konto_info->konto_beguenstigter, 35, 'BEGUENSTIGTER', '' );
		
		$f->text_feld_inaktiv ( 'Mandatsreferenz', 'M_REF', $this->mand->M_REFERENZ, 35, 'M_REF' );
		
		if ($this->mand->NUTZUNGSART == 'MIETZAHLUNG') {
			$js = "onchange=\"var show = document.getElementById('M_REF');show.value = 'MV' + this.value; daj3('ajax/ajax_info.php?option=get_mv_infos&mv_id=' + this.value, 'info_feld_kostentraeger');\"\"";
			$this->dropdown_mieter_vorwahl ( $this->mand->M_KOS_ID, 'Mieter wählen', 'mv_id', 'mv_id', $js );
			$f->hidden_feld ( 'M_KOS_TYP', 'Mietvertrag' );
		}
		
		if ($this->mand->NUTZUNGSART == 'HAUSGELD') {
			$js = "onclick=\"var show = document.getElementById('M_REF');show.value = 'WEG-ET' + this.value;\"";
			$this->dropdown_et_vorwahl ( $this->mand->M_KOS_ID, $objekt_id, 'Eigentümer wählen', 'mv_id', 'mv_id', $js );
			$f->hidden_feld ( 'M_KOS_TYP', 'Eigentuemer' );
		}
		// $mv->autoeinzugsarten('Einzugsart', 'einzugsart', 'einzugsart');
		$mv->dropdown_autoeinzug_selected ( 'Einzugsart', 'einzugsart', $this->mand->EINZUGSART );
		$f->text_feld ( "Kontoinhaber", "NAME", $this->mand->NAME, "50", 'NAME', '' );
		$f->text_feld ( "Anschrift d. Kontoinhabers", "ANSCHRIFT", $this->mand->ANSCHRIFT, "50", 'ANSSCHRIFT', '' );
		$f->text_feld ( "IBAN", "IBAN", $this->mand->IBAN, "50", 'IBAN', '' );
		$f->text_feld ( "BIC", "BIC", $this->mand->BIC, "50", 'BIC', '' );
		$f->text_feld ( "Bank", "BANK", $this->mand->BANKNAME, "50", 'BANK', '' );
		$a_datum = date_mysql2german ( $this->mand->M_ADATUM );
		$u_datum = date_mysql2german ( $this->mand->M_UDATUM );
		$e_datum = date_mysql2german ( $this->mand->M_EDATUM );
		$f->datum_feld ( 'Datum Unterschrift', 'M_UDATUM', $u_datum, 'M_UDATUM' );
		$f->datum_feld ( 'Datum Gültigkeit', 'M_ADATUM', $a_datum, 'M_UDATUM' );
		$f->datum_feld ( 'Datum Ablauf', 'M_EDATUM', $e_datum, 'M_EDATUM' );
		$f->hidden_feld ( 'KTO', $this->mand->KONTONR );
		$f->hidden_feld ( 'BLZ', $this->mand->BLZ );
		$f->hidden_feld ( 'option', 'mandat_mieter_edit_send' );
		$f->send_button ( 'btn_edit_mieter', 'Änderungen speichern' );
		$f->ende_formular ();
		echo "<div id=\"info_feld_kostentraeger\">";
		echo "</div>";
		// $f->fieldset_ende();
	}
	function dropdown_mieter($objekt_id, $label, $name, $id, $js = '') {
		// echo $objekt_id;
		$ob = new objekt ();
		$e_array = $ob->einheiten_objekt_arr ( $objekt_id );
		// echo '<pre>';
		// print_r($e_array);
		if (! is_array ( $e_array )) {
			fehlermeldung_ausgeben ( "Keine Mieter in diesem Objekt OBJ_ID: $objekt_id" );
			die ();
		}
		
		echo "<label for=\"$name\" id=\"label_$name\">$label</label><select name=\"$name\" size=\"1\" id=\"$id\"  $js>\n";
		$anz = count ( $e_array );
		$anz_mv = 0;
		for($a = 0; $a < $anz; $a ++) {
			$einheit_id = $e_array [$a] ['EINHEIT_ID'];
			$einheit_kn = $e_array [$a] ['EINHEIT_KURZNAME'];
			$einheit_typ = $e_array [$a] ['TYP'];
			
			if ($einheit_typ != 'Wohneigentum') {
				$e = new einheit ();
				$mv_id = $e->get_mietvertrag_id ( $einheit_id );
				if ($mv_id) {
					$anz_mv ++;
					$mv = new mietvertraege ();
					$mv->get_mietvertrag_infos_aktuell ( $mv_id );
					if ($mv->mietvertrag_aktuell == '1') {
						$mref = 'MV' . $mv_id;
						if (! $this->check_m_ref ( $mref )) {
							echo "<option value=\"$mv_id\">$einheit_kn | $mv->personen_name_string</option>\n";
						}
					}
				}
			}
		}
		
		if ($anz_mv == 0) {
			echo "<option>Keine Mieter im Objekt</option>\n";
			fehlermeldung_ausgeben ( "Keine Mieter in diesem Objekt" );
			die ();
		}
		echo "</select>\n";
	}
	function dropdown_mieter_vorwahl($vorwahl_mv_id, $label, $name, $id, $js = '') {
		$e = new einheit ();
		$e_array = $e->liste_aller_einheiten ();
		if (! is_array ( $e_array )) {
			fehlermeldung_ausgeben ( "Keine Mieter in diesem Objekt" );
			die ();
		}
		
		// print_r($e_array);
		// die('ENDE');
		echo "<label for=\"$name\" id=\"label_$name\">$label</label><select name=\"$name\" size=\"1\" id=\"$id\"  $js>\n";
		$anz = count ( $e_array );
		for($a = 0; $a < $anz; $a ++) {
			$einheit_id = $e_array [$a] ['EINHEIT_ID'];
			$einheit_kn = $e_array [$a] ['EINHEIT_KURZNAME'];
			$e = new einheit ();
			$mv_id = $e->get_mietvertrag_id ( $einheit_id );
			if ($mv_id) {
				$mv = new mietvertraege ();
				$mv->get_mietvertrag_infos_aktuell ( $mv_id );
				if ($mv->mietvertrag_aktuell == '1') {
					$mref = 'MV' . $mv_id;
					// if(!$this->check_m_ref($mref)){
					if ($mv_id == $vorwahl_mv_id) {
						echo "<option value=\"$mv_id\" selected>$einheit_kn | $mv->personen_name_string</option>\n";
					} else {
						echo "<option value=\"$mv_id\">$einheit_kn | $mv->personen_name_string</option>\n";
					}
					// }
				}
			}
		}
		echo "</select>\n";
	}
	function dropdown_et_vorwahl($vorwahl_et_id, $objekt_id, $label, $name, $id, $js = '') {
		// $e= new einheit;
		// $e_array = $e->liste_aller_einheiten();
		$weg = new weg ();
		$e_array = $weg->einheiten_weg_tabelle_arr ( $objekt_id );
		// echo '<pre>';
		// print_r($e_array);
		if (! is_array ( $e_array )) {
			fehlermeldung_ausgeben ( "Keine Eigentümer in diesem Objekt" );
			die ();
		}
		// print_r($e_array);
		// die('ENDE');
		echo "<label for=\"$name\" id=\"label_$name\">$label</label><select name=\"$name\" size=\"1\" id=\"$id\"  $js>\n";
		$anz = count ( $e_array );
		$anz_et = 0;
		for($a = 0; $a < $anz; $a ++) {
			$einheit_id = $e_array [$a] ['EINHEIT_ID'];
			$e = new einheit ();
			$e->get_einheit_info ( $einheit_id );
			$weg->get_last_eigentuemer_namen ( $einheit_id );
			if ($weg->eigentuemer_namen2) {
				$anz_et ++;
				$weg->get_last_eigentuemer ( $einheit_id );
				$eigentuemer_id = $weg->eigentuemer_id;
				
				$mref = 'WEG-ET' . $eigentuemer_id;
				// if(!$this->check_m_ref($mref)){
				if ($eigentuemer_id == $vorwahl_et_id) {
					echo "<option value=\"$eigentuemer_id\" selected>$e->einheit_kurzname | $weg->eigentuemer_namen2</option>\n";
				} else {
					echo "<option value=\"$eigentuemer_id\">$e->einheit_kurzname | $weg->eigentuemer_namen2</option>\n";
				}
			}
		}
		/*
		 * if($anz_et==0){
		 * echo "<option>Keine Eigentümer im Objekt</option>\n";
		 * fehlermeldung_ausgeben("Keine Eigentümer in diesem Objekt");
		 * die();
		 * }
		 */
		echo "</select>\n";
	}
	
	/* Prüfen ob mandatsreferenz existiert */
	function check_m_ref($mref) {
		$result = mysql_query ( "SELECT * FROM `SEPA_MANDATE` WHERE `M_REFERENZ` = '$mref' AND M_EDATUM='9999-12-31' AND `AKTUELL` = '1' LIMIT 0 , 1" );
		// echo "SELECT * FROM `SEPA_MANDATE` WHERE `M_REFERENZ` = '$mref' AND M_EDATUM='9999-12-31' AND `AKTUELL` = '1' LIMIT 0 , 1";
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			return true;
		}
	}
	function check_m_ref_alle($mref) {
		$result = mysql_query ( "SELECT * FROM `SEPA_MANDATE` WHERE `M_REFERENZ` = '$mref' AND `AKTUELL` = '1' LIMIT 0 , 1" );
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			return true;
		}
	}
	function mandat_aendern($dat, $mref, $glaeubiger_id, $gk_id, $empf, $name, $anschrift, $kto, $blz, $iban, $bic, $bankname, $udatum, $adatum, $edatum, $m_art, $n_art, $e_art, $kos_typ, $kos_id) {
		$this->get_mandat_infos ( $dat );
		if (! isset ( $this->mand )) {
			die ( 'Abbruch, interner fehler class_sepa, mandat_aendern' );
		}
		$this->mandat_dat_deaktivieren ( $dat );
		if ($this->check_m_ref ( $mref )) {
			die ( 'Mandat existiert schon, Ihre eingaben wurden nicht gespeichert' );
		} else {
			$last_id = $this->mand->M_ID;
			$udatum = date_german2mysql ( $udatum );
			$adatum = date_german2mysql ( $adatum );
			$edatum = date_german2mysql ( $edatum );
			$sql = "INSERT INTO `SEPA_MANDATE` VALUES (NULL, '$last_id', '$mref', '$glaeubiger_id', '$gk_id', '$empf', '$name', '$anschrift', '$kto', '$blz', '$iban', '$bic', '$bankname', '$udatum', '$adatum', '$edatum', '$m_art', '$n_art', '$e_art', '$kos_typ', '$kos_id', '1');";
			echo "$sql<br>";
			$result = mysql_query ( $sql ) or die ( mysql_error () );
			;
			
			/* Protokollieren */
			$last_dat = mysql_insert_id ();
			protokollieren ( 'SEPA_MANDATE', $last_dat, $dat );
		}
	}
	function mandat_dat_deaktivieren($dat) {
		mysql_query ( "UPDATE `SEPA_MANDATE` SET AKTUELL='0' WHERE `DAT` = '$dat'" ) or die ( mysql_error () );
		;
		return true;
	}
	function mandat_speichern($mref, $glaeubiger_id, $gk_id, $empf, $name, $anschrift, $kto, $blz, $iban, $bic, $bankname, $udatum, $adatum, $edatum, $m_art, $n_art, $e_art, $kos_typ, $kos_id) {
		if ($this->check_m_ref ( $mref )) {
			die ( 'Mandat existiert schon, Ihre eingaben wurden nicht gespeichert' );
		} else {
			$last_id = last_id2 ( 'SEPA_MANDATE', 'M_ID' ) + 1;
			$udatum = date_german2mysql ( $udatum );
			$adatum = date_german2mysql ( $adatum );
			$edatum = date_german2mysql ( $edatum );
			$sql = "INSERT INTO `SEPA_MANDATE` VALUES (NULL, '$last_id', '$mref', '$glaeubiger_id', '$gk_id', '$empf', '$name', '$anschrift', '$kto', '$blz', '$iban', '$bic', '$bankname', '$udatum', '$adatum', '$edatum', '$m_art', '$n_art', '$e_art', '$kos_typ', '$kos_id', '1');";
			// echo "$sql<br>";
			
			$result = mysql_query ( $sql ) or die ( mysql_error () );
			;
			/* Protokollieren */
			$last_dat = mysql_insert_id ();
			protokollieren ( 'SEPA_MANDATE', $last_dat, '0' );
			echo "Mandat gespeichert";
		}
	}
	function mandat_seq_speichern($mref, $betrag, $datum, $datei, $vzweck, $iban) {
		if (! $this->check_mandat_is_used ( $mref, $iban ) == true) {
			$seq = 'FRST';
		} else {
			$seq = 'RCUR';
		}
		// $datum = date_german2mysql($datum);
		$sql = "INSERT INTO `SEPA_MANDATE_SEQ` VALUES (NULL, '$mref','$iban', '$seq', '$betrag', '$datei', '$datum', '$vzweck', '1');";
		// echo "$sql<br>";
		
		$result = mysql_query ( $sql ) or die ( mysql_error () );
		;
		/* Protokollieren */
		$last_dat = mysql_insert_id ();
		protokollieren ( 'SEPA_MANDATE_SEQ', $last_dat, '0' );
		// echo "Mandat gespeichert";
	}
	function sepa_datei_erstellen($sammelbetrag = 1, $dateiname_msgid, $nutzungsart = 'MIETZAHLUNG', $pdf = 0) {
		$arr = $this->get_mandate_arr ( $nutzungsart );
		// echo '<pre>';
		// print_r($mandate_arr);
		// die();
		$anz = count ( $arr );
		$myKtoSepaSimple = new KtoSepaSimple ();
		$monat = date ( "m" );
		$monatsname = monat2name ( $monat );
		$jahr = date ( "Y" );
		
		$this->summe_frst = 0.00;
		$this->summe_rcur = 0.00;
		
		for($a = 0; $a < $anz; $a ++) {
			
			$name = substr ( $this->umlautundgross ( $arr [$a] ['NAME'] ), 0, 35 ); // auf 70 Zeichen kürzen
			$iban = $arr [$a] ['IBAN'];
			$bic = $arr [$a] ['BIC'];
			$mandat_datum = $arr [$a] ['M_UDATUM'];
			$m_ref = $arr [$a] ['M_REFERENZ'];
			$kos_id = $arr [$a] ['M_KOS_ID'];
			$kos_typ = $arr [$a] ['M_KOS_TYP'];
			$einzugsart = $arr [$a] ['EINZUGSART'];
			
			if ($nutzungsart == 'MIETZAHLUNG') {
				$mv = new mietvertraege ();
				$mv->get_mietvertrag_infos_aktuell ( $kos_id );
				$einheit_kn = $mv->einheit_kurzname;
				$mz = new miete ();
				$mz->mietkonto_berechnung ( $kos_id );
				
				if ($einzugsart == 'Aktuelles Saldo komplett') {
					if ($mz->erg < 0.00) {
						$summe_zu_ziehen = substr ( $mz->erg, 1 );
					} else {
						$summe_zu_ziehen = 0.00;
					}
				}
				
				if ($einzugsart == 'Nur die Summe aus Vertrag') {
					$mk = new mietkonto ();
					$summe_zu_ziehen_arr = explode ( '|', $mk->summe_forderung_monatlich ( $kos_id, $monat, $jahr ) );
					
					$summe_zu_ziehen = $summe_zu_ziehen_arr [0];
				}
				
				if ($einzugsart == 'Ratenzahlung') {
					
					$mk = new mietkonto ();
					$summe_zu_ziehen_arr = explode ( '|', $mk->summe_forderung_monatlich ( $kos_id, $monat, $jahr ) );
					
					$summe_raten = $mk->summe_rate_monatlich ( $kos_id, $monat, $jahr );
					$summe_zu_ziehen = $summe_zu_ziehen_arr [0] + $summe_raten;
				}
				
				/*
				 * $mv = new mietvertraege();
				 * $mv->get_mietvertrag_infos_aktuell($kos_id);
				 *
				 * $mz = new miete();
				 * $mz->mietkonto_berechnung($kos_id);
				 *
				 * if($mz->erg<0.00){
				 * $mz->erg = substr($mz->erg,1);
				 * }
				 */
				
				$kat = 'RENT';
				$vzweck1 = substr ( $this->umlautundgross ( "Mieteinzug $monatsname $jahr für $mv->einheit_kurzname $name" ), 0, 140 );
				$PmtInfId = substr ( $this->umlautundgross ( $mv->objekt_kurzname . " LS-MIETEN $monat/$jahr" ), - 30 );
			}
			
			if ($nutzungsart == 'HAUSGELD') {
				
				/* Berechnung */
				$weg = new weg ();
				$einheit_id = $weg->get_einheit_id_from_eigentuemer ( $kos_id );
				$e = new einheit ();
				$e->get_einheit_info ( $einheit_id );
				// $weg->get_wg_info($monat, $jahr, 'Einheit', $einheit_id, 'Hausgeld');
				$weg->get_eigentuemer_saldo ( $kos_id, $einheit_id );
				$einheit_kn = $e->einheit_kurzname;
				if ($einzugsart == 'Aktuelles Saldo komplett') {
					if ($weg->hg_erg < 0) {
						$summe_zu_ziehen = $weg->soll_aktuell;
						;
					} else {
						$summe_zu_ziehen = 0.00;
					}
				}
				
				if ($einzugsart == 'Nur die Summe aus Vertrag') {
					$summe_zu_ziehen = $weg->soll_aktuell;
				}
				$vzweck1 = substr ( $this->umlautundgross ( "Hausgeld $monatsname $jahr für $e->einheit_kurzname $name" ), 0, 140 );
				$kat = '';
				$PmtInfId = substr ( $e->objekt_kurzname . " HAUSGELDER $monat/$jahr", - 30 );
			}
			
			/* Gemeinsame vars */
			$last_ident = substr ( $this->umlautundgross ( "MANDAT:$m_ref" ), 0, 35 );
			
			/*
			 * SequenceType1Code Wertebereich: FRST (Erstlastschrift), RCUR (Folgelastschrift), OOFF (Einmallastschrift),FNAL (letzte Lastschrift)
			 */
			/* Feststellen ob Erstnutzung, Folgenutzung des Mandats */
			if (! $this->check_mandat_is_used ( $m_ref, $iban ) == true) {
				$abbuchung = 'FRST';
				// $datum = date("2014-01-25");//PLUS TAGE 7
				$o = new objekt ();
				$datum = $o->datum_plus_tage ( date ( "Y-m-d" ), 7 );
				$this->summe_frst += $summe_zu_ziehen;
			} else {
				$abbuchung = 'RCUR';
				// $datum = date("2014-01-20");//PLUS TAGE 3
				$o = new objekt ();
				$datum = $o->datum_plus_tage ( date ( "Y-m-d" ), 3 );
				$this->summe_rcur += $summe_zu_ziehen;
			}
			
			if ($summe_zu_ziehen > 0.00) {
				if ($pdf == 0) {
					$myKtoSepaSimple->Add ( $datum, $summe_zu_ziehen, $name, $iban, $bic, NULL, $kat, $last_ident, $vzweck1, $abbuchung, $m_ref, $mandat_datum );
					/* Eintragen als genutzt */
					$this->mandat_seq_speichern ( $m_ref, $summe_zu_ziehen, $datum, $dateiname_msgid, $vzweck1, $iban );
				} else {
					if ($abbuchung == 'FRST') {
						$tab_frst [$a] ['EINHEIT'] = $einheit_kn;
						$tab_frst [$a] ['DATUM'] = date_mysql2german ( $datum );
						$tab_frst [$a] ['BETRAG'] = nummer_punkt2komma_t ( $summe_zu_ziehen );
						$tab_frst [$a] ['NAME'] = $name;
						$tab_frst [$a] ['ABBUCHUNG'] = $abbuchung;
						$tab_frst [$a] ['IBAN'] = $iban;
						$tab_frst [$a] ['BIC'] = $bic;
						$tab_frst [$a] ['KAT'] = $kat;
						$tab_frst [$a] ['IDENT'] = $last_ident;
						$tab_frst [$a] ['VZWECK'] = $vzweck1;
						$tab_frst [$a] ['M_REF'] = $m_ref;
						$tab_frst [$a] ['M_DATUM'] = $mandat_datum;
					}
					
					if ($abbuchung == 'RCUR') {
						$tab_rcur [$a] ['EINHEIT'] = $einheit_kn;
						$tab_rcur [$a] ['DATUM'] = date_mysql2german ( $datum );
						$tab_rcur [$a] ['BETRAG'] = nummer_punkt2komma_t ( $summe_zu_ziehen );
						$tab_rcur [$a] ['NAME'] = $name;
						$tab_rcur [$a] ['ABBUCHUNG'] = $abbuchung;
						$tab_rcur [$a] ['IBAN'] = $iban;
						$tab_rcur [$a] ['BIC'] = $bic;
						$tab_rcur [$a] ['KAT'] = $kat;
						$tab_rcur [$a] ['IDENT'] = $last_ident;
						$tab_rcur [$a] ['VZWECK'] = $vzweck1;
						$tab_rcur [$a] ['M_REF'] = $m_ref;
						$tab_rcur [$a] ['M_DATUM'] = $mandat_datum;
					}
				}
			}
			
			// $myKtoSepaSimple->Add('2014-02-01', 119.00, 'Kunde,Konrad', 'DE54100700000190001800', 'DEUTDEBBXXX',
			// NULL, NULL, '1111222111', 'Rechnung 111111', 'OOFF', 'KUN1', '2013-09-13');
		}
		
		$gk = new geldkonto_info ();
		$gk->geld_konto_details ( $_SESSION ['geldkonto_id'] );
		$monat = date ( "m" );
		$jahr = date ( "Y" );
		$username = $_SESSION ['username'];
		
		$seps = new sepa ();
		$seps->get_iban_bic ( $gk->kontonummer, $gk->blz );
		$d = new detail ();
		$glaeubiger_id = $d->finde_detail_inhalt ( 'GELD_KONTEN', $_SESSION ['geldkonto_id'], 'GLAEUBIGER_ID' );
		/* SEPA FILE */
		if ($pdf == 0) {
			$xmlstring = $myKtoSepaSimple->GetXML ( 'CORE', $dateiname_msgid, $PmtInfId, $this->umlautundgross ( $gk->konto_beguenstigter ), $this->umlautundgross ( "$gk->konto_beguenstigter - $username" ), $seps->IBAN, $seps->BIC, $glaeubiger_id, $sammelbetrag );
			
			/* SEPA AUSGABE */
			ob_clean ();
			header ( 'Content-type: text/xml; charset=utf-8' );
			header ( "Content-disposition: attachment;filename=$dateiname_msgid" );
			echo $xmlstring;
			die ();
		} else {
			/* PDF */
			$pdf = new Cezpdf ( 'a4', 'landscape' );
			$bpdf = new b_pdf ();
			$bpdf->b_header ( $pdf, 'Partner', $_SESSION [partner_id], 'landscape', 'Helvetica.afm', 6 );
			$this->footer_zahlungshinweis = $bpdf->zahlungshinweis;
			$pdf->ezStopPageNumbers (); // seitennummerirung beenden
			$p = new partners ();
			$p->get_partner_info ( $_SESSION ['partner_id'] );
			$datum = date ( "d.m.Y" );
			
			$cols = array (
					'DATUM' => "Datum",
					'EINHEIT' => "Einheit",
					'BETRAG' => "Betrag",
					'NAME' => "Name",
					'M_REF' => "MANDAT",
					'VZWECK' => "TEXT",
					'ABBUCHUNG' => "RF",
					'BIC' => "BIC",
					'IBAN' => "IBAN" 
			);
			/*
			 * $tab_arr[$a]['IBAN'] = $iban;
			 * $tab_arr[$a]['BIC'] = $bic;
			 * $tab_arr[$a]['KAT'] = $kat;
			 * $tab_arr[$a]['IDENT'] = $last_ident;
			 */
			if (is_array ( $tab_frst )) {
				$tab_frst = array_merge ( $tab_frst, Array () );
				$anz_t = count ( $tab_frst );
				$tab_frst [$anz_t] ['EINHEIT'] = "<b>SUMME</b>";
				$tab_frst [$anz_t] ['BETRAG'] = "<b>$this->summe_frst</b>";
				$pdf->ezTable ( $tab_frst, $cols, "<b>Beigleitzettel " . $this->umlautundgross ( $gk->konto_beguenstigter ) . " - ERSTABBUCHUNGEN</b>", array (
						'rowGap' => 1.5,
						'showLines' => 1,
						'showHeadings' => 1,
						'shaded' => 1,
						'shadeCol' => array (
								0.9,
								0.9,
								0.9 
						),
						'titleFontSize' => 9,
						'fontSize' => 7,
						'xPos' => 50,
						'xOrientation' => 'right',
						'width' => 750,
						'cols' => array (
								'BETRAG' => array (
										'justification' => 'right',
										'width' => 50 
								),
								'NAME' => array (
										'justification' => 'left',
										'width' => 100 
								),
								'VZWECK' => array (
										'justification' => 'left',
										'width' => 200 
								),
								'DATUM' => array (
										'justification' => 'left',
										'width' => 50 
								) 
						) 
				) );
			}
			if (is_array ( $tab_rcur )) {
				$tab_rcur = array_merge ( $tab_rcur, Array () );
				// echo '<pre>';
				// print_r($tab_rcur);
				// print_r($rcur_arr_new);
				// die('RCIR');
				$pdf->ezSetDy ( - 20 );
				$anz_r = count ( $tab_rcur );
				$tab_rcur [$anz_r] ['EINHEIT'] = "<b>SUMME</b>";
				$tab_rcur [$anz_r] ['BETRAG'] = "<b>$this->summe_rcur</b>";
				$pdf->ezTable ( $tab_rcur, $cols, "<b>Beigleitzettel " . $this->umlautundgross ( $gk->konto_beguenstigter ) . " - FOLGEABBUCHUNGEN</b>", array (
						'rowGap' => 1.5,
						'showLines' => 1,
						'showHeadings' => 1,
						'shaded' => 1,
						'shadeCol' => array (
								0.9,
								0.9,
								0.9 
						),
						'titleFontSize' => 9,
						'fontSize' => 7,
						'xPos' => 50,
						'xOrientation' => 'right',
						'width' => 750,
						'cols' => array (
								'BETRAG' => array (
										'justification' => 'right',
										'width' => 50 
								),
								'NAME' => array (
										'justification' => 'left',
										'width' => 100 
								),
								'VZWECK' => array (
										'justification' => 'left',
										'width' => 140 
								),
								'DATUM' => array (
										'justification' => 'left',
										'width' => 50 
								),
								'G_KEY_A' => array (
										'justification' => 'right',
										'width' => 55 
								),
								'E_KEY_A' => array (
										'justification' => 'right',
										'width' => 50 
								),
								'E_BETRAG' => array (
										'justification' => 'right',
										'width' => 50 
								) 
						) 
				) );
			}
			$pdf->ezSetDy ( - 20 );
			$uhrzeit = date ( "d.m.Y H:i:s" );
			$pdf->eztext ( "                Erstellt am: $uhrzeit", 10 );
			ob_clean (); // ausgabepuffer leeren
			$pdf->ezStream ();
		}
	}
	
	/*
	 * UPDATE SEPA_MANDATE_SEQ dest, (SELECT M_REFERENZ, IBAN FROM SEPA_MANDATE WHERE AKTUELL='1') src
	 * SET dest.IBAN = src.IBAN WHERE AKTUELL='1' && dest.M_REFERENZ = src.M_REFERENZ
	 */
	function get_mandat_seq_status($m_ref, $iban) {
		if (! $this->check_mandat_is_used ( $m_ref, $iban ) == true) {
			return 'ERSTEINZUG';
		} else {
			return 'FOLGEEINZUG';
		}
	}
	function check_mandat_is_used($m_ref, $iban) {
		$result = mysql_query ( "SELECT * FROM `SEPA_MANDATE_SEQ` WHERE `M_REFERENZ` = '$m_ref' AND IBAN='$iban' && `AKTUELL` = '1' LIMIT 0 , 1" );
		// $result = mysql_query ("SELECT * FROM `SEPA_MANDATE_SEQ` WHERE `M_REFERENZ` = '$m_ref' && `AKTUELL` = '1' LIMIT 0 , 1");
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			return true;
		}
	}
	function mandat_nutzungen_arr($m_ref) {
		$this->get_mandat_infos_mref ( $m_ref );
		// echo $this->mand->IBAN;
		$iban = $this->mand->IBAN;
		$result = mysql_query ( "SELECT * FROM `SEPA_MANDATE_SEQ` WHERE `M_REFERENZ` = '$m_ref' AND IBAN='$iban' AND `AKTUELL` = '1' ORDER BY DATUM" );
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			while ( $row = mysql_fetch_assoc ( $result ) )
				$arr [] = $row;
			return $arr;
		}
	}
	function mandat_nutzungen_anzeigen($m_ref) {
		$arr = $this->mandat_nutzungen_arr ( $m_ref );
		if (is_array ( $arr )) {
			$anz = count ( $arr );
			echo "<table class=\"sortable\">";
			echo "<thead><tr><th>DATUM</th><th>SEQ</th><th>DATEI</th><th>VZWECK</th><th>BETRAG</th></tr></thead>";
			$summe = 0.00;
			for($a = 0; $a < $anz; $a ++) {
				$seq = $arr [$a] ['SEQ'];
				$datum = date_mysql2german ( $arr [$a] ['DATUM'] );
				$betrag = nummer_punkt2komma_t ( $arr [$a] ['BETRAG'] );
				$summe += $arr [$a] ['BETRAG'];
				$datei = $arr [$a] ['DATEI'];
				$vzweck = $arr [$a] ['VZWECK'];
				echo "<tr><td>$datum</td><td>$seq</td><td>$datei</td><td>$vzweck</td><td>$betrag</td></tr>";
			}
			$summe_a = nummer_punkt2komma_t ( $summe );
			echo "<tfoot><tr><th colspan=\"4\"><b>SUMME</b></th><th><b>$summe_a</b></th></tr></tfoot>";
			echo "</table>";
		}
	}
	function umlautundgross($wort) {
		$tmp = strtoupper ( $wort );
		$suche = array (
				'Ä',
				'Ö',
				'Ü',
				'ß',
				'ä',
				'ö',
				'ü',
				'ß',
				'é',
				'&',
				'*',
				'$',
				'%',
				'€',
				'<BR>' 
		);
		$ersetze = array (
				'AE',
				'OE',
				'UE',
				'SS',
				'AE',
				'OE',
				'UE',
				'SS',
				'E',
				'+',
				'.',
				'.',
				'.',
				'EUR',
				' ' 
		);
		$ret = str_replace ( $suche, $ersetze, $tmp );
		return $ret;
	}
	function test_fremd_sepa_ls() {
		/* TESTEN */
		ob_clean ();
		$this->xml_pruef ( 'classes/xsd/cs.xml', 'classes/xsd/pain.008.003.02.xsd' );
		die ();
	}
	
	/*
	 *
	 * $myKtoSepaSimple = new KtoSepaSimple();
	 * $myKtoSepaSimple->Add('2014-02-01', 119.00, 'Kunde,Konrad', 'DE54100700000190001800', 'DEUTDEBBXXX',
	 * NULL, NULL, '1111222111', 'Rechnung 111111', 'OOFF', 'KUN1', '2013-09-13');
	 *
	 * $myKtoSepaSimple->Add('2014-02-01', 119.00, 'Pero Zdrero', 'DE213123123123123', 'BELADEBEXXX',
	 * NULL, NULL, '2233222111', 'Rechnung 22222', 'OOFF', 'KUN2', '2013-09-13');
	 *
	 * $xmlstring = $myKtoSepaSimple->GetXML('CORE', 'Einzug.2013-09', 'Best.v.13.09.2013',
	 * 'Berlus GmbH', 'Berlus Gmbh Fon', 'DE10100500000800101561', 'DRESDEFF100',
	 * 'DE81ZZZ00000825339');
	 * #print_r($xml);
	 * ob_clean();
	 * header('Content-type: text/xml; charset=utf-8');
	 * echo $xmlstring;
	 * die();
	 *
	 * #$xml = new DOMDocument();
	 * #$xml->load('classes/xsd/sepaOK.xml');
	 * /* $xml->loadString($xmlstring);
	 * if (!$xml->schemaValidate('classes/xsd/pain.008.003.02.xsd')) {
	 * echo "invalid<p/>";
	 * }else {
	 * echo "validated<p/>";
	 * }
	 */
	/*
	 * <Document xmlns="urn:iso:std:iso:20022:tech:xsd:pain.008.003.02"
	 * xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
	 * xsi:schemaLocation="urn:iso:std:iso:20022:tech:xsd:pain.008.003.02 pain.008.003.02.xsd">
	 * <Document xmlns="urn:iso:std:iso:20022:tech:xsd:pain.008.003.02" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="urn:iso:std:iso:20022:tech:xsd:pain.008.003.02 pain.008.003.02.xsd"/>
	 *
	 *
	 */
	/*
	 * $doc = new DOMDocument('1.0', 'utf-8');
	 * $doc->formatOutput = true;
	 * $root = $doc->createElementNS('urn:iso:std:iso:20022:tech:xsd:pain.008.003.02', 'Document');
	 * $root->setAttributeNS('http://www.w3.org/2000/xmlns/' ,'xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
	 *
	 * $domAttribute = $doc->createAttribute('xsi:schemaLocation');
	 * $domAttribute->value = 'urn:iso:std:iso:20022:tech:xsd:pain.008.003.02 pain.008.003.02.xsd';
	 * $root->appendChild($domAttribute);
	 * $doc->appendChild($root);
	 *
	 *
	 * $root = $doc->createElement('CstmrDrctDbtInitn');
	 * $doc->appendChild($root);
	 *
	 * $root->appendChild($firstNode = $doc->createElement("GrpHdr"));
	 * #$firstNode->setAttribute("id", "1");
	 * #$firstNode->setAttribute("kategorie", "123");
	 * $firstNode->appendChild($doc->createElement("MsgId", "Einzug.2013-09"));
	 * $firstNode->appendChild($doc->createElement("CreDtTm", "DATUM MOJ"));
	 * $firstNode->appendChild($doc->createElement("NbOfTxs", "DATUM MOJ"));
	 * $firstNode->appendChild($doc->createElement("CtrlSum", "DATUM MOJ"));
	 * $firstNode->appendChild($firstNode1 = $doc->createElement("InitgPty"));
	 * $firstNode1->appendChild($doc->createElement("Nm", "Berlus GmbH"));
	 * $root->appendChild($secondNode = $doc->createElement("PmtInf"));
	 * $secondNode->appendChild($doc->createElement("PmtInfId", "Einzug.2013-09"));
	 * $secondNode->appendChild($doc->createElement("PmtMtd", "Einzug.2013-09"));
	 * $secondNode->appendChild($doc->createElement("NbOfTxs", "Einzug.2013-09"));
	 * $secondNode->appendChild($doc->createElement("CtrlSum", "Einzug.2013-09"));
	 * $secondNode->appendChild($secondNode1 = $doc->createElement("PmtTpInf"));
	 * #SvcLvl
	 * $secondNode1->appendChild($secondNode2 = $doc->createElement("SvcLvl"));
	 * $secondNode2->appendChild($doc->createElement("CD", "SEPA"));
	 * #LclInstrm
	 * $secondNode1->appendChild($secondNode3 = $doc->createElement("LclInstrm"));
	 * $secondNode3->appendChild($doc->createElement("CD", "CORE"));
	 *
	 * $secondNode->appendChild($doc->createElement("ReqdColltnDt", "2014-12-25"));
	 * $secondNode1->appendChild($secondNode2 = $doc->createElement("Cdtr"));
	 * $secondNode2->appendChild($doc->createElement("Nm", "Berlus FON"));
	 *
	 * $secondNode1->appendChild($secondNode2 = $doc->createElement("CdtrAcct"));
	 * $secondNode2->appendChild($secondNode2a = $doc->createElement("Id"));
	 * $secondNode2a->appendChild($doc->createElement("IBAN", "IBAN8981298392183"));
	 *
	 * $secondNode1->appendChild($secondNode2 = $doc->createElement("CdtrAgt"));
	 * $secondNode2->appendChild($secondNode2a = $doc->createElement("FinInstnId"));
	 * $secondNode2a->appendChild($doc->createElement("BIC", "BIC7831283912183"));
	 * #ChrgBr
	 * $secondNode->appendChild($doc->createElement("ChrgBr", "SLEV"));
	 */
	
	// ob_clean();
	// header('Content-type: text/xml; charset=utf-8');
	// echo $dom->saveXML();
	// echo $doc->saveXML();
	// die();
	/*
	 * <CstmrDrctDbtInitn>
	 * <GrpHdr>
	 * <MsgId>Einzug.2013-09</MsgId>
	 * <CreDtTm>DATUM MOJ</CreDtTm>
	 * <NbOfTxs>DATUM MOJ</NbOfTxs>
	 * <CtrlSum>DATUM MOJ</CtrlSum>
	 * <InitgPty>
	 * <Nm>Berlus GmbH</Nm>
	 * </InitgPty>
	 * </GrpHdr>
	 * <PmtInf>
	 * <PmtInfId>Einzug.2013-09</PmtInfId>
	 * <PmtMtd>Einzug.2013-09</PmtMtd>
	 * <NbOfTxs>Einzug.2013-09</NbOfTxs>
	 * <CtrlSum>Einzug.2013-09</CtrlSum>
	 * <PmtTpInf>
	 * <SvcLvl>
	 * <CD>SEPA</CD>
	 * </SvcLvl>
	 * </PmtTpInf>
	 * </PmtInf>
	 *
	 *
	 */
	// $this->xxm();
	// }
	function hed(&$doc, $a) {
		$root->appendChild ( $secondNode = $doc->createElement ( "PmtInf" ) );
		$secondNode->appendChild ( $doc->createElement ( "PmtInfId", "Einzug.2013-09" ) );
		$secondNode->appendChild ( $doc->createElement ( "PmtMtd", "Einzug.2013-09" ) );
		$secondNode->appendChild ( $doc->createElement ( "NbOfTxs", "Einzug.2013-09" ) );
		$secondNode->appendChild ( $doc->createElement ( "CtrlSum", "Einzug.2013-09" ) );
	}
	function start($MsgId, $CreDtTm, $NbOfTxs, $CtrlSum) {
		$doc = new DOMDocument ( '1.0', 'utf-8' );
		$doc->formatOutput = true;
		$root = $doc->createElementNS ( 'urn:iso:std:iso:20022:tech:xsd:pain.008.003.02', 'Document' );
		$root->setAttributeNS ( 'http://www.w3.org/2000/xmlns/', 'xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance' );
		
		$domAttribute = $doc->createAttribute ( 'xsi:schemaLocation' );
		$domAttribute->value = 'urn:iso:std:iso:20022:tech:xsd:pain.008.003.02 pain.008.003.02.xsd';
		$root->appendChild ( $domAttribute );
		$doc->appendChild ( $root );
		
		$root = $doc->createElement ( 'CstmrDrctDbtInitn' );
		$doc->appendChild ( $root );
		
		$firstNode = $doc->createElement ( "GrpHdr" );
		$firstNode->appendChild ( $doc->createElement ( "MsgId", "$MsgId" ) );
		$firstNode->appendChild ( $doc->createElement ( "CreDtTm", "$CreDtTm" ) );
		$firstNode->appendChild ( $doc->createElement ( "NbOfTxs", "$NbOfTxs" ) );
		$firstNode->appendChild ( $doc->createElement ( "CtrlSum", "$CtrlSum" ) );
		
		// $secondNode = $doc->createElement("PmtInf");
		$root->appendChild ( $firstNode );
		// $root->appendChild($secondNode);
		
		ob_clean ();
		header ( 'Content-type: text/xml; charset=utf-8' );
		$xml = $doc->saveXML ();
		// echo $xml;
		return $doc;
		// die();
		// echo $xml;
	}
	
	// function
	function xxm() {
		$this->start ( 'sdhasjd', '2014-02-02', 11, '112112' );
		/*
		 * $doc = new DOMDocument('1.0', 'utf-8');
		 * $doc->formatOutput = true;
		 * $root = $doc->createElementNS('urn:iso:std:iso:20022:tech:xsd:pain.008.003.02', 'Document');
		 * $root->setAttributeNS('http://www.w3.org/2000/xmlns/' ,'xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
		 *
		 * $domAttribute = $doc->createAttribute('xsi:schemaLocation');
		 * $domAttribute->value = 'urn:iso:std:iso:20022:tech:xsd:pain.008.003.02 pain.008.003.02.xsd';
		 * $root->appendChild($domAttribute);
		 * $doc->appendChild($root);
		 *
		 * $root = $doc->createElement('CstmrDrctDbtInitn');
		 * $doc->appendChild($root);
		 *
		 * $firstNode = $doc->createElement("GrpHdr");
		 * $firstNode->appendChild($doc->createElement("MsgId", "Einzug.2013-09"));
		 * $firstNode->appendChild($doc->createElement("CreDtTm", "DATUM MOJ"));
		 * $firstNode->appendChild($doc->createElement("NbOfTxs", "DATUM MOJ"));
		 * $firstNode->appendChild($doc->createElement("CtrlSum", "DATUM MOJ"));
		 *
		 * $secondNode = $doc->createElement("PmtInf");
		 * $root->appendChild($firstNode);
		 * $root->appendChild($secondNode);
		 */
		
		// $firstNode->setAttribute("id", "1");
		// $firstNode->setAttribute("kategorie", "123");
		// $firstNode->appendChild($doc->createElement("MsgId", "Einzug.2013-09"));
		
		// ob_clean();
		// header('Content-type: text/xml; charset=utf-8');
		// echo $dom->saveXML();
		// echo $doc->saveXML();
		// die();
	}
	
	/* IBM */
	function libxml_display_error($error) {
		$return = "<br/>\n";
		switch ($error->level) {
			
			case LIBXML_ERR_WARNING :
				$return .= "<b>Warning $error->code</b>: ";
				break;
			
			case LIBXML_ERR_ERROR :
				$return .= "<b>Error $error->code</b>: ";
				break;
			
			case LIBXML_ERR_FATAL :
				$return .= "<b>Fatal Error $error->code</b>: ";
				break;
		}
		
		$return .= trim ( $error->message );
		if ($error->file) {
			$return .= " in <b>$error->file</b><br>";
		}
		$return .= " on line <b>$error->line</b><br>";
		return $return;
	}
	function libxml_display_errors() {
		$errors = libxml_get_errors ();
		foreach ( $errors as $error ) {
			print $this->libxml_display_error ( $error );
		}
		libxml_clear_errors ();
	}
	function xml_pruef($xml_datei, $xsd_datei) {
		// Enable user error handling
		libxml_use_internal_errors ( true );
		$xml = new DOMDocument ();
		$xml->load ( $xml_datei );
		if (! $xml->schemaValidate ( $xsd_datei )) {
			print '<b>Errors Found!</b>';
			$this->libxml_display_errors ();
		} else {
			echo "validated<p/>";
		}
	}
	function dropdown_sepa_geldkonten($label, $name, $id, $kos_typ, $kos_id) {
		// $result = mysql_query ("SELECT * FROM `SEPA_KONTOS` WHERE `KOS_TYP` = '$kos_typ' AND `KOS_ID` ='$kos_id' AND `AKTUELL` = '1' ORDER BY BEGUENSTIGTER");
		$result = mysql_query ( "SELECT GELD_KONTEN.KONTO_ID, GELD_KONTEN.IBAN, GELD_KONTEN.BIC, GELD_KONTEN.BEGUENSTIGTER, GELD_KONTEN.KONTONUMMER, GELD_KONTEN.BLZ, GELD_KONTEN.INSTITUT, GELD_KONTEN.BEZEICHNUNG FROM GELD_KONTEN_ZUWEISUNG, GELD_KONTEN WHERE KOSTENTRAEGER_TYP = '$kos_typ' && KOSTENTRAEGER_ID = '$kos_id' && GELD_KONTEN.KONTO_ID = GELD_KONTEN_ZUWEISUNG.KONTO_ID && GELD_KONTEN_ZUWEISUNG.AKTUELL = '1' && GELD_KONTEN.AKTUELL = '1' GROUP BY GELD_KONTEN.KONTO_ID ORDER BY GELD_KONTEN.KONTO_ID ASC" );
		// echo "SELECT * FROM `SEPA_KONTOS` WHERE `KOS_TYP` = '$kos_typ' AND `KOS_ID` ='$kos_id' AND `AKTUELL` = '1' ORDER BY BEGUENSTIGTER";
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			while ( $row = mysql_fetch_assoc ( $result ) )
				$my_array [] = $row;
			
			echo "<label for=\"$id\">$label (Konten:$numrows)</label>\n<select name=\"$name\" id=\"$id\" size=\"1\" >\n";
			if ($numrows > 1) {
				echo "<option>Bitte wählen</option>\n";
			}
			for($a = 0; $a < count ( $my_array ); $a ++) {
				$konto_id = $my_array [$a] ['KONTO_ID'];
				$beguenstigter = $my_array [$a] ['BEGUENSTIGTER'];
				$bez = $my_array [$a] ['BEZEICHNUNG'];
				$iban = $my_array [$a] ['IBAN'];
				$iban1 = $this->iban_convert ( $iban, 0 );
				$bic = $my_array [$a] ['BIC'];
				$bank = $my_array [$a] ['BANKNAME'];
				echo "<option value=\"$konto_id\" >$bez - $iban1 - $bic</option>\n";
			} // end for
			echo "</select>\n";
			return true;
		} else {
			fehlermeldung_ausgeben ( "Kein SEPA-Geldkonto hinterlegt" );
			return FALSE;
		}
	}
	function dropdown_sepa_geldkonten_filter($label, $name, $id, $filter_bez) {
		$result = mysql_query ( "SELECT GELD_KONTEN.KONTO_ID, GELD_KONTEN.IBAN, GELD_KONTEN.BIC, GELD_KONTEN.BEGUENSTIGTER, GELD_KONTEN.KONTONUMMER, GELD_KONTEN.BLZ, GELD_KONTEN.INSTITUT,  GELD_KONTEN.BEZEICHNUNG FROM GELD_KONTEN_ZUWEISUNG, GELD_KONTEN WHERE  GELD_KONTEN.KONTO_ID = GELD_KONTEN_ZUWEISUNG.KONTO_ID && GELD_KONTEN_ZUWEISUNG.AKTUELL = '1' && GELD_KONTEN.AKTUELL = '1' && GELD_KONTEN.BEZEICHNUNG LIKE '%$filter_bez%' GROUP BY GELD_KONTEN.KONTO_ID ORDER BY GELD_KONTEN.BEGUENSTIGTER ASC" );
		// echo "SELECT GELD_KONTEN.KONTO_ID, GELD_KONTEN.IBAN, GELD_KONTEN.BIC, GELD_KONTEN.BEGUENSTIGTER, GELD_KONTEN.KONTONUMMER, GELD_KONTEN.BLZ, GELD_KONTEN.INSTITUT FROM GELD_KONTEN_ZUWEISUNG, GELD_KONTEN WHERE GELD_KONTEN.KONTO_ID = GELD_KONTEN_ZUWEISUNG.KONTO_ID && GELD_KONTEN_ZUWEISUNG.AKTUELL = '1' && GELD_KONTEN.AKTUELL = '1' && GELD_KONTEN.BEZEICHNUNG LIKE '%$filter_bez' ORDER BY GELD_KONTEN.KONTO_ID ASC";
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			while ( $row = mysql_fetch_assoc ( $result ) )
				$my_array [] = $row;
				// print_r($my_array);
			echo "<label for=\"$id\">$label</label>\n<select name=\"$name\" id=\"$id\" size=\"1\" >\n";
			echo "<option selected>Bitte wählen</option>\n";
			for($a = 0; $a < count ( $my_array ); $a ++) {
				$konto_id = $my_array [$a] ['KONTO_ID'];
				$beguenstigter = $my_array [$a] ['BEGUENSTIGTER'];
				$bez = $my_array [$a] ['BEZEICHNUNG'];
				$iban = $my_array [$a] ['IBAN'];
				$iban1 = $this->iban_convert ( $iban, 0 );
				$bic = $my_array [$a] ['BIC'];
				$bank = $my_array [$a] ['BANKNAME'];
				echo "<option value=\"$konto_id\" >$bez - $iban1 - $bic</option>\n";
			} // end for
			echo "</select>\n";
			return true;
		} else {
			fehlermeldung_ausgeben ( "Kein SEPA-Geldkonto hinterlegt" );
			return FALSE;
		}
	}
	function get_sepa_konto_infos($gk_id) {
		if (isset ( $this->iban )) {
			unset ( $this->iban );
		}
		if (isset ( $this->bic )) {
			unset ( $this->bic );
		}
		if (isset ( $this->bankname )) {
			unset ( $this->bankname );
		}
		if (isset ( $this->beguenstigter )) {
			unset ( $this->beguenstigter );
		}
		if (isset ( $this->kos_typ )) {
			unset ( $this->kos_typ );
		}
		if (isset ( $this->kos_id )) {
			unset ( $this->kos_id );
		}
		$result = mysql_query ( "SELECT * FROM `GELD_KONTEN` WHERE `KONTO_ID` = '$gk_id' AND `AKTUELL` = '1' ORDER BY KONTO_DAT DESC LIMIT 0,1" );
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			$row = mysql_fetch_assoc ( $result );
			$this->IBAN = $row ['IBAN'];
			$this->IBAN1 = chunk_split ( $this->IBAN, 4, ' ' );
			$this->BIC = $row ['BIC'];
			
			$this->beguenstigter = $row ['BEGUENSTIGTER'];
			$this->konto_beguenstigter = $row ['BEGUENSTIGTER'];
			
			$this->kontonummer = $row ['KONTONUMMER'];
			$this->blz = $row ['BLZ'];
			
			$this->bankname = $row ['INSTITUT'];
			$this->institut = $row ['INSTITUT'];
			$this->kredit_institut = $row ['INSTITUT'];
			
			$this->geldkonto_bez = $row ['BEZEICHNUNG'];
			$this->geldkonto_bezeichnung = $row ['BEZEICHNUNG'];
			$this->geldkonto_bezeichnung_kurz = $this->geldkonto_bezeichnung;
			
			return true;
		} else {
			return false;
		}
	}
	function sepa_ueberweisung_speichern($von_gk_id, $an_sepa_gk_id, $vzweck, $kat, $kos_typ, $kos_id, $konto, $betrag) {
		// echo "$von_gk_id, $an_sepa_gk_id, $vzweck, $kat, $kos_ytp, $kos_id, $konto, $betrag";
		$bk = new bk ();
		$last_b_id = $bk->last_id ( 'SEPA_UEBERWEISUNG', 'ID' ) + 1;
		
		$sep = new sepa ();
		if (! $sep->get_sepa_konto_infos ( $an_sepa_gk_id )) {
			die ( fehlermeldung_ausgeben ( "EMPFÄNGER SEPAGELDKONTO UNBEKANNT! ID:$an_sepa_gk_id" ) );
		} else {
			if (empty ( $sep->IBAN ) or empty ( $sep->BIC )) {
				die ( fehlermeldung_ausgeben ( "$sep->geldkonto_bez hat keine IBAN oder BIC" ) );
			}
			$vzweck = "$sep->beguenstigter, $vzweck";
			
			$db_abfrage = "INSERT INTO SEPA_UEBERWEISUNG VALUES (NULL, '$last_b_id', NULL, '$kat', '$vzweck', '$betrag', '$von_gk_id', '$sep->IBAN', '$sep->BIC', '$sep->bankname', '$sep->beguenstigter', '$kos_typ', '$kos_id', '$konto', '1')";
			$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
			/* Protokollieren */
			$last_dat = mysql_insert_id ();
			protokollieren ( 'SEPA_UEBERWEISUNG', $last_dat, '0' );
			return $last_b_id;
		}
	}
	function sepa_ueberweisung_speichern_IBAN($von_gk_id, $iban, $bic, $empfaenger, $bank, $vzweck, $kat, $kos_typ, $kos_id, $konto, $betrag) {
		// echo "$von_gk_id, $an_sepa_gk_id, $vzweck, $kat, $kos_typ, $kos_id, $konto, $betrag";
		$empfaenger = umlautundgross ( $empfaenger );
		$bank = umlautundgross ( $bank );
		$bk = new bk ();
		$last_b_id = $bk->last_id ( 'SEPA_UEBERWEISUNG', 'ID' ) + 1;
		$iban = $this->iban_convert ( $iban, 1 );
		
		if (! empty ( $iban ) && ! empty ( $bic )) {
			
			// echo "<hr>";
			$db_abfrage = "INSERT INTO SEPA_UEBERWEISUNG VALUES (NULL, '$last_b_id', NULL, '$kat', '$vzweck', '$betrag', '$von_gk_id', '$iban', '$bic', '$bank', '$empfaenger', '$kos_typ', '$kos_id', '$konto', '1')";
			// echo $db_abfrage;
			// echo "<hr>";
			// die();
			$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
			/* Protokollieren */
			$last_dat = mysql_insert_id ();
			protokollieren ( 'SEPA_UEBERWEISUNG', $last_dat, '0' );
			return $last_b_id;
		} else {
			
			fehlermeldung_ausgeben ( "Sepa Überweisung | BIC IBAN eingeben!!!" );
			die ();
		}
	}
	function sepa_sammler_anzeigen($von_gk_id, $kat = null) {
		$arr = $this->get_sammler_arr ( $von_gk_id, $kat );
		if (is_array ( $arr )) {
			// echo '<pre>';
			// print_r($arr);
			$anz = count ( $arr );
			
			echo "<hr>";
			echo "<h2><b>$kat</b></h2>";
			echo "<hr>";
			echo "<table class=\"sortable\">";
			
			echo "<thead><tr><th>EMPFÄNGER</th><th>VZWECK</th><th>ZUWEISUNG</th><th>IBAN</th><th>BIC</th><th>BETRAG</th><th>KONTO</th><th>OPTION</TH></tr></thead>";
			// echo "<tr><th>$kat</th></tr>";
			$sum = 0;
			for($a = 0; $a < $anz; $a ++) {
				$empf = $arr [$a] ['BEGUENSTIGTER'];
				$vzweck = $arr [$a] ['VZWECK'];
				$betrag = $arr [$a] ['BETRAG'];
				$sum += $betrag;
				$iban = $arr [$a] ['IBAN'];
				$bic = $arr [$a] ['BIC'];
				$konto = $arr [$a] ['KONTO'];
				$z = $a + 1;
				$dat = $arr [$a] ['DAT'];
				$kos_typ = $arr [$a] ['KOS_TYP'];
				$kos_id = $arr [$a] ['KOS_ID'];
				$re = new rechnung ();
				$kos_bez = $re->kostentraeger_ermitteln ( $kos_typ, $kos_id );
				$kos_bez1 = "$kos_typ: $kos_bez";
				
				$link_del = "<a href=\"?daten=sepa&option=sepa_datensatz_del&dat=$dat\">Entfernen</a>";
				echo "<tr><td>$z. $empf</td><td>$vzweck</td><td>$kos_bez1</td><td>$iban</td><td>$bic</td><td>$betrag</td><td>$konto</td><td>$link_del</td></tr>";
			}
			echo "<tfoot><tr><th colspan=\"3\">SUMME</th><th><th>$sum</th><th></th><th></th></tr></tfoot>";
			echo "</table>";
			return true;
		} else {
			fehlermeldung_ausgeben ( "Keine $kat im SEPA-Ü-Sammler" );
		}
	}
	function get_sammler_arr($von_gk_id, $kat = null) {
		if ($kat == null) {
			$result = mysql_query ( "SELECT * FROM `SEPA_UEBERWEISUNG` WHERE `FILE` IS NULL AND `GK_ID_AUFTRAG` ='$von_gk_id' AND `AKTUELL` = '1' ORDER BY DAT" );
		} else {
			$result = mysql_query ( "SELECT * FROM `SEPA_UEBERWEISUNG` WHERE `FILE` IS NULL AND KAT='$kat' AND `GK_ID_AUFTRAG` ='$von_gk_id' AND `AKTUELL` = '1' ORDER BY DAT" );
		}
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			while ( $row = mysql_fetch_assoc ( $result ) )
				$arr [] = $row;
			return $arr;
		}
	}
	function sammler2sepa($von_gk_id, $kat = null, $sammler = 0) {
		if (! $von_gk_id) {
			die ( fehlermeldung_ausgeben ( "Geldkonto wählen" ) );
		}
		// die("$von_gk_id $kat $sammler");
		
		/* Einzelbetrag oder Sammelbetrag beachten $sammler!!!!! */
		
		$arr = $this->get_sammler_arr ( $von_gk_id, $kat );
		if (! is_array ( $arr )) {
			die ( fehlermeldung_ausgeben ( "Keine Datensätze auf Konto $von_gk_id für $kat" ) );
		} else {
			$myKtoSepaSimple = new KtoSepaSimple ();
			$o = new objekt ();
			// $datum = $o->datum_plus_tage(date("Y-m-d"), 2); //TERMINÜberweisung
			$datum = '1999-01-01';
			$datum_h = date ( "dmY" );
			$time_h = date ( "His" );
			
			$anz = count ( $arr );
			$benutzername = $_SESSION ['username'];
			$msg_id = "$von_gk_id-$datum_h-$time_h-$benutzername";
			$dateiname = "$von_gk_id-$datum_h-$time_h-$benutzername.xml";
			for($a = 0; $a < $anz; $a ++) {
				$empf = utf8_decode ( $this->umlautundgross ( $arr [$a] ['BEGUENSTIGTER'] ) );
				$vzweck = substr ( $this->umlautundgross ( $arr [$a] ['VZWECK'] ), 0, 140 );
				$betrag = $arr [$a] ['BETRAG'];
				$sum += $betrag;
				$iban = $arr [$a] ['IBAN'];
				$bic = $arr [$a] ['BIC'];
				$konto = $arr [$a] ['KONTO'];
				$ref_id = $arr [$a] ['ID'];
				
				/* Überweisungsdatensatz hinzufügen */
				$myKtoSepaSimple->Add ( $datum, $betrag, $empf, $iban, $bic, NULL, NULL, $ref_id, $vzweck );
				
				/* Für das hinzufügen des Dateinamens */
				$dat = $arr [$a] ['DAT'];
				$this->sepa_ueberweisung2file ( $dat, $dateiname );
			} // end for
			
			/* Eigene Informationen einfügen */
			$gk = new geldkonto_info ();
			$gk->geld_konto_details ( $von_gk_id );
			
			$xml_string = $myKtoSepaSimple->GetXML ( 'TRF', $msg_id, $dateiname, $this->umlautundgross ( $gk->konto_beguenstigter ), $this->umlautundgross ( "$gk->geldkonto_bez" ), $gk->IBAN, $gk->BIC, $sammler );
			// $xmlstring = $myKtoSepaSimple->GetXML('CORE', $dateiname_msgid , $PmtInfId, $this->umlautundgross($gk->konto_beguenstigter), $this->umlautundgross("$gk->konto_beguenstigter - $username"), $seps->IBAN, $seps->BIC, $glaeubiger_id, $sammelbetrag);
			/* SEPA AUSGABE */
			ob_clean ();
			header ( 'Content-type: text/xml; charset=utf-8' );
			header ( "Content-disposition: attachment;filename=$dateiname" );
			echo $xml_string;
			die ();
		}
	}
	function sepa_ueberweisung2file($dat, $dateiname) {
		$db_abfrage = "UPDATE SEPA_UEBERWEISUNG SET FILE='$dateiname' WHERE DAT='$dat'";
		$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		/* Protokollieren */
		protokollieren ( 'SEPA_UEBERWEISUNG', $dat, $dat );
	}
	function get_sepa_lsfiles_arr() {
		// OK$result = mysql_query ("SELECT COUNT(BETRAG) AS ANZ, DATEI, SUM(BETRAG) AS SUMME, DATUM FROM `SEPA_MANDATE_SEQ` WHERE AKTUELL='1' GROUP BY DATEI ORDER BY DATUM DESC");
		if (isset ( $_SESSION ['geldkonto_id'] ) && ! empty ( $_SESSION ['geldkonto_id'] )) {
			$vorz = $_SESSION ['geldkonto_id'] . '-';
			$result = mysql_query ( "SELECT COUNT(BETRAG) AS ANZ, DATEI, SUM(BETRAG) AS SUMME, DATUM FROM `SEPA_MANDATE_SEQ` WHERE AKTUELL='1' && DATEI LIKE '$vorz%' GROUP BY DATEI ORDER BY DATUM DESC" );
			// echo "SELECT COUNT(BETRAG) AS ANZ, DATEI, SUM(BETRAG) AS SUMME, DATUM FROM `SEPA_MANDATE_SEQ` WHERE AKTUELL='1' && DATEI LIKE '$vorz%' GROUP BY DATEI ORDER BY DATUM DESC";
		} else {
			$result = mysql_query ( "SELECT COUNT(BETRAG) AS ANZ, DATEI, SUM(BETRAG) AS SUMME, DATUM FROM `SEPA_MANDATE_SEQ` WHERE AKTUELL='1' GROUP BY DATEI ORDER BY DATUM DESC" );
		}
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			while ( $row = mysql_fetch_assoc ( $result ) )
				$arr [] = $row;
			return $arr;
		}
	}
	function get_sepa_lsfiles_arr_gk($gk_id) {
		$vorz = $gk_id . '-';
		$result = mysql_query ( "SELECT COUNT(BETRAG) AS ANZ, DATEI, SUM(BETRAG) AS SUMME, DATUM FROM `SEPA_MANDATE_SEQ` WHERE AKTUELL='1' && DATEI LIKE '$vorz%' GROUP BY DATEI ORDER BY DATUM DESC" );
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			while ( $row = mysql_fetch_assoc ( $result ) )
				$arr [] = $row;
			return $arr;
		}
	}
	function finde_ls_file_by_betrag($gk_id, $betrag) {
		$arr = $this->get_sepa_lsfiles_arr_gk ( $gk_id );
		if (is_array ( $arr )) {
			$anz = count ( $arr );
			for($a = 0; $a < $anz; $a ++) {
				$sum = $arr [$a] ['SUMME'];
				if ($sum == $betrag) {
					$arr_n [] = $arr [$a];
				}
			}
		}
		if (isset ( $arr_n )) {
			return $arr_n;
		}
	}
	function finde_ls_file_by_datum($gk_id, $betrag, $datum) {
		$datum = date_german2mysql ( $datum );
		$vorz = $gk_id . '-';
		$result = mysql_query ( "SELECT COUNT(BETRAG) AS ANZ, DATEI, SUM(BETRAG) AS SUMME, DATUM FROM `SEPA_MANDATE_SEQ` WHERE AKTUELL='1' && DATEI LIKE '$vorz%' && DATUM='$datum' GROUP BY DATEI ORDER BY DATUM DESC" );
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			while ( $row = mysql_fetch_assoc ( $result ) )
				$arr [] = $row;
			return $arr;
		}
	}
	function finde_ls_file_by_monat($gk_id, $betrag, $datum) {
		$datum_t = explode ( '.', $datum );
		$monat = $datum_t [1];
		$jahr = $datum_t [2];
		$mon_jahr = "$monat$jahr";
		$arr = $this->finde_ls_file_by_betrag ( $gk_id, $betrag );
		
		if (is_array ( $arr )) {
			$anz = count ( $arr );
			for($a = 0; $a < $anz; $a ++) {
				$datum_xml = explode ( '-', $arr [$a] ['DATUM'] );
				$jahr_xml = $datum_xml [0];
				$mon_xml = $datum_xml [1];
				$mon_jahr_sql = "$mon_xml$jahr_xml";
				if ($mon_jahr == $mon_jahr_sql) {
					$arr_n [] = $arr [$a];
				}
			}
		}
		if (isset ( $arr_n )) {
			return $arr_n;
		}
	}
	function get_sepa_lszeilen_arr($datei) {
		$result = mysql_query ( "SELECT * FROM `SEPA_MANDATE_SEQ` WHERE AKTUELL='1' && DATEI='$datei'" );
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			while ( $row = mysql_fetch_assoc ( $result ) )
				$arr [] = $row;
			return $arr;
		}
	}
	function form_ls_datei_ab($datei) {
		if (! isset ( $_SESSION ['geldkonto_id'] )) {
			fehlermeldung_ausgeben ( "Geldkonto wählen!" );
		} else {
			$gk = new geldkonto_info ();
			$gk->geld_konto_details ( $_SESSION ['geldkonto_id'] );
			fehlermeldung_ausgeben ( "Gebucht wird auf dem Geldkonto $gk->geldkonto_bez" );
			if (! isset ( $_SESSION ['temp_kontoauszugsnummer'] )) {
				fehlermeldung_ausgeben ( "Kontrolldaten eingeben!!!" );
				die ();
			}
			// echo $datei;
			$arr = $this->get_sepa_lszeilen_arr ( $datei );
			// echo '<pre>';
			// print_r($arr);
			if (is_array ( $arr )) {
				$anz = count ( $arr );
				$f = new formular ();
				
				for($a = 0; $a < $anz; $a ++) {
					
					$m_ref = $arr [$a] ['M_REFERENZ'];
					$betrag = $arr [$a] ['BETRAG'];
					$betrag_a = nummer_punkt2komma ( $arr [$a] ['BETRAG'] );
					$vzweck = $arr [$a] ['VZWECK'];
					$datum = date_mysql2german ( $arr [$a] ['DATUM'] );
					
					$lang = strlen ( $m_ref );
					$kt = substr ( $m_ref, 0, 2 );
					
					// echo $m_ref;
					// die(" $kt $kos_id");
					
					if (stristr ( $m_ref, 'MV' ) == TRUE) {
						$kos_typ = 'Mietvertrag';
						$kos_id = substr ( $m_ref, 2 );
					}
					if (stristr ( $m_ref, 'WEG-ET' ) == TRUE) {
						$kos_typ = 'Eigentuemer';
						$kos_id = substr ( $m_ref, 6 );
					}
					if (! isset ( $kos_typ )) {
						fehlermeldung_ausgeben ( "KOstentraeger unbekannt! class_sepa form_ls_datei_ab" );
						die ( 'LALALAL' );
					}
					// die("$kos_typ $kos_id");
					// print_r($_SESSION);
					if ($kos_typ == 'Mietvertrag') {
						$mv = new mietvertraege ();
						$mv->get_mietvertrag_infos_aktuell ( $kos_id );
						$kos_bez = "$mv->einheit_typ $mv->einheit_kurzname $mv->personen_name_string";
					}
					
					if ($kos_typ == 'Eigentuemer') {
						$weg = new weg ();
						$weg->get_eigentuemer_namen ( $kos_id );
						$kos_bez = "$weg->eigentuemer_name_str";
					}
					// echo "$kos_bez";
					$check_datum = date_german2mysql ( $_SESSION ['temp_datum'] );
					if ($this->check_ls_buchung ( $_SESSION ['geldkonto_id'], $m_ref, $betrag, $_SESSION ['temp_kontoauszugsnummer'], $check_datum, $kos_typ, $kos_id ) != true) {
						$f->erstelle_formular ( "LS-BUCHEN $kos_bez MREF:$m_ref", null );
						$f->text_feld ( 'Kontoauzugsnr', 'kontoauszug', $_SESSION ['temp_kontoauszugsnummer'], 10, 'auszugsnr', null );
						$f->datum_feld ( 'Datum', 'datum', $_SESSION ['temp_datum'], 'datum' );
						$f->text_feld ( 'Betrag', 'betrag', $betrag_a, 20, 'betrag', null );
						// $f->text_feld('Buchungstext', 'vzweck', "$vzweck MANDAT:$m_ref", 100, 'vzweck', null);
						$f->text_feld ( 'Buchungstext', 'vzweck', "$vzweck", 100, 'vzweck', null );
						$f->hidden_feld ( 'gk_id', $_SESSION ['geldkonto_id'] );
						$f->hidden_feld ( 'kos_typ', $kos_typ );
						$f->hidden_feld ( 'kos_id', $kos_id );
						$f->hidden_feld ( 'm_ref', $m_ref );
						$f->hidden_feld ( 'option', 'ls_zeile_buchen' );
						$f->hidden_feld ( 'datei', $datei );
						$f->check_box_js ( 'mwst', 'mwst', 'MWSt buchen', '', '' );
						$f->send_button ( 'btnLS', 'Zeile Buchen' );
						// echo "$kt $kos_typ $kos_id";
						$f->ende_formular ();
					} else {
						fehlermeldung_ausgeben ( "$betrag_a - $vzweck<br> wurde bereits verbucht. Doppelbuchung unmöglich!" );
					}
				}
			} else {
				fehlermeldung_ausgeben ( "Keine Lastschriften in der Datei $datei" );
			}
		}
	}
	function check_ls_buchung($gk_id, $m_ref, $betrag, $kontoauszug, $datum, $kos_typ, $kos_id) {
		$result = mysql_query ( "SELECT *  
FROM  `GELD_KONTO_BUCHUNGEN` 
WHERE  `KONTO_AUSZUGSNUMMER` ='$kontoauszug'
AND  `ERFASS_NR` =  '$m_ref'
AND  `BETRAG` = '$betrag'
AND  `GELDKONTO_ID` ='$gk_id'
AND  `KOSTENTRAEGER_TYP` =  '$kos_typ'
AND  `KOSTENTRAEGER_ID` ='$kos_id'
AND DATUM ='$datum'		
AND  `AKTUELL` =  '1'" );
		
		/*
		 * echo "SELECT *
		 * FROM `GELD_KONTO_BUCHUNGEN`
		 * WHERE `KONTO_AUSZUGSNUMMER` ='$kontoauszug'
		 * AND `ERFASS_NR` = '$m_ref'
		 * AND `BETRAG` = '$betrag'
		 * AND `GELDKONTO_ID` ='$gk_id'
		 * AND `KOSTENTRAEGER_TYP` = '$kos_typ'
		 * AND `KOSTENTRAEGER_ID` ='$kos_id'
		 * AND DATUM ='$datum'
		 * AND `AKTUELL` = '1'";
		 */
		// die();
		
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			return $numrows;
		} else {
			return false;
		}
	}
	function betrag_buchen($datum, $kto_auszugsnr, $m_ref, $betrag, $vzweck, $geldkonto_id, $kostentraeger_typ, $kostentraeger_id, $kostenkonto, $mwst = '0.00') {
		$b = new buchen ();
		$buchung_id = $b->get_last_geldbuchung_id ();
		/* neu */
		$datum = date_german2mysql ( $datum );
		$datum_arr = explode ( '-', $datum );
		$jahr = $datum_arr ['0'];
		$g_buchungsnummer = $b->get_last_buchungsnummer_konto ( $geldkonto_id, $jahr );
		$g_buchungsnummer = $g_buchungsnummer + 1;
		// echo "<h1>Neue Buchungsnummer erteilt: $g_buchungsnummer</h1>";
		
		$buchung_id = $buchung_id + 1;
		
		/* neu */
		$db_abfrage = "INSERT INTO GELD_KONTO_BUCHUNGEN VALUES (NULL, '$buchung_id', '$g_buchungsnummer', '$kto_auszugsnr', '$m_ref', '$betrag', '$mwst', '$vzweck', '$geldkonto_id', '$kostenkonto', '$datum', '$kostentraeger_typ', '$kostentraeger_id', '1')";
		$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		/* Protokollieren */
		$last_dat = mysql_insert_id ();
		protokollieren ( 'GELD_KONTO_BUCHUNGEN', $last_dat, '0' );
	}
	function sepa_files_arr($von_gk_id) {
		if ($von_gk_id != null) {
			$result = mysql_query ( "SELECT ID, FILE, SUM(BETRAG) AS SUMME FROM `SEPA_UEBERWEISUNG` WHERE `FILE` IS NOT NULL AND GK_ID_AUFTRAG='$von_gk_id' AND `AKTUELL` = '1' GROUP BY FILE ORDER BY DAT DESC" );
		} else {
			$result = mysql_query ( "SELECT ID, FILE, SUM(BETRAG) AS SUMME FROM `SEPA_UEBERWEISUNG` WHERE `FILE` IS NOT NULL AND `AKTUELL` = '1' GROUP BY FILE ORDER BY DAT DESC LIMIT 0, 300" );
		}
		// echo "SELECT FILE FROM `SEPA_UEBERWEISUNG` WHERE `FILE` IS NOT NULL AND GK_ID_AUFTRAG='$von_gk_id' AND `AKTUELL` = '1' GROUP BY FILE ORDER BY DAT";
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			while ( $row = mysql_fetch_assoc ( $result ) )
				$arr [] = $row;
			return $arr;
		}
	}
	function sepa_gk_arr() {
		$result = mysql_query ( "SELECT GK_ID_AUFTRAG, KAT, SUM(BETRAG) AS SUMME, COUNT(BETRAG) AS ANZ FROM `SEPA_UEBERWEISUNG` WHERE `FILE` IS NULL AND `AKTUELL` = '1' GROUP BY KAT, `GK_ID_AUFTRAG`" );
		// echo "SELECT GK_ID_AUFTRAG, KAT, SUM(BETRAG) AS SUMME FROM `SEPA_UEBERWEISUNG` WHERE `FILE` IS NULL AND `AKTUELL` = '1' GROUP BY KAT, `GK_ID_AUFTRAG`";
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			while ( $row = mysql_fetch_assoc ( $result ) )
				$arr [] = $row;
			return $arr;
		}
	}
	function sepa_sammler_alle() {
		// echo "SAMMLER ALLE";
		// echo "<pre>";
		$arr = $this->sepa_gk_arr ();
		if (is_array ( $arr )) {
			echo "<h2>Zu erstellende SEPA-Dateien</h2>";
			$anz = count ( $arr );
			echo "<table class=\"sortable\">";
			echo "<tr><th>Geldkonto</th><th>kat</th><th>Summe</th></tr>";
			for($a = 0; $a < $anz; $a ++) {
				$gk_id = $arr [$a] ['GK_ID_AUFTRAG'];
				$sum = $arr [$a] ['SUMME'];
				$kat = $arr [$a] ['KAT'];
				$anz_dat = $arr [$a] ['ANZ'];
				$gkk = new geldkonto_info ();
				$gkk->geld_konto_details ( $gk_id );
				// print_r($gkk);
				echo "<tr><td>$gkk->geldkonto_bez</td><td>$kat (Überweisungen:$anz_dat)</td><td>$sum</td></tr>";
				// echo $gk_id;
			}
			echo "</table>";
		} else {
			hinweis_ausgeben ( "Keine Datensätze in SEPA-Sammlern" );
		}
		// print_r($arr);
	}
	function get_summe_sepa_sammler($von_gk_id, $kat, $kos_typ, $kos_id) {
		$result = mysql_query ( "SELECT SUM( BETRAG ) AS SUMME FROM `SEPA_UEBERWEISUNG` WHERE `FILE` IS NULL AND `KOS_TYP` LIKE '$kos_typ' AND `KOS_ID` ='$kos_id' && KAT='$kat' AND `AKTUELL` = '1'" );
		// echo "SELECT SUM( BETRAG ) AS SUMME FROM `SEPA_UEBERWEISUNG` WHERE `FILE` IS NULL AND `KOS_TYP` = '$kos_typ' AND `KOS_ID` ='$kos_id' && KAT='$kat' AND `AKTUELL` = '1'";
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			while ( $row = mysql_fetch_assoc ( $result ) )
				return $row ['SUMME'];
		} else {
			return '0.00';
		}
	}
	function sepa_files($von_gk_id) {
		$arr = $this->sepa_files_arr ( $von_gk_id );
		if (! is_array ( $arr )) {
			fehlermeldung_ausgeben ( "Keine SEPA-Überweisungen vom gewählten Geldkonto!" );
		} else {
			// echo '<pre>';
			// print_r($arr);
			$anz_f = count ( $arr );
			echo "<table class=\"sortable\">";
			echo "<tr><th>NR</th><th>KONTO</th><th>DATEINAME</th><th>BESCHREIBUNG</th><th>SUMME</th><th>OPTIONEN</th><th></th><th></th></tr>";
			for($a = 0; $a < $anz_f; $a ++) {
				$z = $a + 1;
				$dateiname = $arr [$a] ['FILE'];
				$dat_nam_arr = explode ( '-', $dateiname );
				$gk_id = $dat_nam_arr [0];
				$gk = new geldkonto_info ();
				$gk->geld_konto_details ( $gk_id );
				
				$sep_id = $arr [$a] ['ID'];
				$summe_t = nummer_punkt2komma_t ( $arr [$a] ['SUMME'] );
				$link_anzeigen = "<a href=\"?daten=sepa&option=sepa_file_anzeigen&sepa_file=$dateiname\">ANZEIGEN</a>";
				$link_autobuchen = "<a href=\"?daten=sepa&option=sepa_file_buchen&sepa_file=$dateiname\">BUCHEN</a>";
				$link_autobuchen1 = "<a href=\"?daten=sepa&option=sepa_file_buchen_fremd&sepa_file=$dateiname\">BUCHEN FREMDKONTO</a>";
				$link_pdf = "<a href=\"?daten=sepa&option=sepa_file_pdf&sepa_file=$dateiname\"><img src=\"css/pdf.png\"></a>";
				$link_pdf1 = "<a href=\"?daten=sepa&option=sepa_file_pdf&sepa_file=$dateiname&no_logo\"><img src=\"css/pdf2.png\"></a>";
				$link_als_vorlage = "<a href=\"?daten=sepa&option=sepa_file_kopieren&sepa_file=$dateiname\">ALS VORLAGE</a>";
				// $link__autobuchen = "<a href=\"?daten=sepa&option=sepa_file_kopieren&sepa_file=$dateiname\"><b>AUTOBUCHEN<b></a>";
				$link_details = "<a href=\"?daten=details&option=details_anzeigen&detail_tabelle=SEPA_UEBERWEISUNG&detail_id=$sep_id\">DETAILS</a>";
				$de = new detail ();
				$beschr = $de->finde_detail_inhalt ( 'SEPA_UEBERWEISUNG', $sep_id, 'Beschreibung' );
				echo "<tr><td>$z</td><td>$gk->geldkonto_bez</td><td>$dateiname</td><td>$beschr</td><td>$summe_t</td><td>$link_anzeigen $link_pdf $link_pdf1 $link_autobuchen $link_details</td><td>$link_als_vorlage</td><td>$link_autobuchen1</td></tr>";
			}
			echo "</table>";
		}
	}
	function get_sepa_files_daten_arr($file) {
		$result = mysql_query ( "SELECT * FROM `SEPA_UEBERWEISUNG` WHERE `FILE` = '$file' AND `AKTUELL` = '1'" );
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			while ( $row = mysql_fetch_assoc ( $result ) )
				$arr [] = $row;
			return $arr;
		}
	}
	function sepa_file_anzeigen($file) {
		$arr = $this->get_sepa_files_daten_arr ( $file );
		if (! is_array ( $arr )) {
			fehlermeldung_ausgeben ( "Keine Datensätze zur Datei $file" );
		} else {
			$f = new formular ();
			$f->erstelle_formular ( 'SEPA-Datei Vorschau / Autobuchen', null );
			echo "<table class=\"sortable\">";
			echo "<thead><tr><th>EMPFÄNGER</th><th>VZWECK</th><th>IBAN</th><th>BIC</th><th>BETRAG</th><th>KONTO</th></tr></thead>";
			// echo "<tr><th>$kat</th></tr>";
			$sum = 0;
			$anz = count ( $arr );
			for($a = 0; $a < $anz; $a ++) {
				$empf = $arr [$a] ['BEGUENSTIGTER'];
				$vzweck = $arr [$a] ['VZWECK'];
				$betrag = $arr [$a] ['BETRAG'];
				$sum += $betrag;
				$iban = $arr [$a] ['IBAN'];
				$bic = $arr [$a] ['BIC'];
				$konto = $arr [$a] ['KONTO'];
				$z = $a + 1;
				echo "<tr><td>$z. $empf</td><td>$vzweck</td><td>$iban</td><td>$bic</td><td>$betrag</td><td>$konto</td></tr>";
			}
			echo "<tfoot><tr><th colspan=\"3\">SUMME</th><th><th>$sum</th><th></th></tr></tfoot>";
			echo "<tr><td>";
			$f->hidden_feld ( 'option', 'sepa_ue_autobuchen' );
			$f->hidden_feld ( 'file', $file );
			$f->check_box_js ( 'mwst', 'steuer', 'MWSt buchen???', '', '' );
			$f->send_button ( 'Btn_AB', 'Automatisch verbuchen!?!' );
			$f->ende_formular ();
			echo "</td><td><b>Kontrolldaten:<br>
	Datum: $_SESSION[temp_datum]
	<br>Auszug: $_SESSION[temp_kontoauszugsnummer]
	</td></tr>";
			echo "</table>";
		}
	}
	function sepa_file_kopieren($file) {
		$arr = $this->get_sepa_files_daten_arr ( $file );
		if (! is_array ( $arr )) {
			fehlermeldung_ausgeben ( "Keine Datensätze zur Datei $file" );
		} else {
			
			$sum = 0;
			$anz = count ( $arr );
			for($a = 0; $a < $anz; $a ++) {
				$kat = $arr [$a] ['KAT'];
				$empf = $arr [$a] ['BEGUENSTIGTER'];
				$vzweck = $arr [$a] ['VZWECK'];
				$betrag = $arr [$a] ['BETRAG'];
				$iban = $arr [$a] ['IBAN'];
				$bic = $arr [$a] ['BIC'];
				$bankname = $arr [$a] ['BANKNAME'];
				$beguenstigter = $arr [$a] ['BEGUENSTIGTER'];
				$konto = $arr [$a] ['KONTO'];
				$kos_typ = $arr [$a] ['KOS_TYP'];
				$kos_id = $arr [$a] ['KOS_ID'];
				$vzweckn = "$beguenstigter, $vzweck";
				
				$z = $a + 1;
				$sep = new sepa ();
				$von_gk_id = $_SESSION ['geldkonto_id'];
				
				$bk = new bk ();
				$last_b_id = $bk->last_id ( 'SEPA_UEBERWEISUNG', 'ID' ) + 1;
				
				$db_abfrage = "INSERT INTO SEPA_UEBERWEISUNG VALUES (NULL, '$last_b_id', NULL, '$kat', '$vzweckn', '$betrag', '$von_gk_id', '$iban', '$bic', '$bankname', '$beguenstigter', '$kos_typ', '$kos_id', '$konto', '1')";
				$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
				/* Protokollieren */
				$last_dat = mysql_insert_id ();
				protokollieren ( 'SEPA_UEBERWEISUNG', $last_dat, '0' );
			}
			return true;
		}
	}
	function sepa_file_autobuchen($file, $datum, $gk_id, $auszug, $mwst = '0') {
		// echo "$file, $datum, $gk_id, $auszug, $mwst";
		$arr = $this->get_sepa_files_daten_arr ( $file );
		if (! is_array ( $arr )) {
			fehlermeldung_ausgeben ( "Keine Datensätze zur Datei $file" );
		} else {
			$anz = count ( $arr );
			for($a = 0; $a < $anz; $a ++) {
				$empf = $arr [$a] ['BEGUENSTIGTER'];
				$vzweck = $arr [$a] ['VZWECK'];
				$iban = $arr [$a] ['IBAN'];
				$bic = $arr [$a] ['BIC'];
				$konto = $arr [$a] ['KONTO'];
				$kat = $arr [$a] ['KAT'];
				$kos_typ = $arr [$a] ['KOS_TYP'];
				$kos_id = $arr [$a] ['KOS_ID'];
				$betrag = - $arr [$a] ['BETRAG'];
				if ($mwst == '0') {
					$this->betrag_buchen ( $datum, $auszug, $auszug, $betrag, $vzweck, $gk_id, $kos_typ, $kos_id, $konto, '0.00' );
				} else {
					$mwst = $betrag / 119 * 19;
					$this->betrag_buchen ( $datum, $auszug, $auszug, $betrag, $vzweck, $gk_id, $kos_typ, $kos_id, $konto, $mwst );
				}
			}
			
			$geld = new geldkonto_info ();
			$kontostand_aktuell = nummer_punkt2komma ( $geld->geld_konto_stand ( $gk_id ) );
			echo "SEPA-Datei $file wurde verbucht!<br>";
			if (isset ( $_SESSION ['temp_kontostand'] ) && isset ( $_SESSION ['temp_kontoauszugsnummer'] )) {
				$kontostand_temp = nummer_punkt2komma ( $_SESSION ['temp_kontostand'] );
				hinweis_ausgeben ( "<h3>Kontostand am $_SESSION[temp_datum] laut Kontoauszug $_SESSION[temp_kontoauszugsnummer] war $kontostand_temp €</h3>" );
			}
			if ($kontostand_aktuell == $kontostand_temp) {
				echo "<h3>Kontostand aktuell: $kontostand_aktuell €</h3>";
			} else {
				echo "<h3 style=\"color:red\">Kontostand aktuell: $kontostand_aktuell €</h3>";
			}
		}
	}
	function sepa_file_buchen($file) {
		$gk = new geldkonto_info ();
		$gk->geld_konto_details ( $_SESSION ['geldkonto_id'] );
		fehlermeldung_ausgeben ( "SEPA wird gebucht auf dem Geldkonto $gk->geldkonto_bez" );
		if (! isset ( $_SESSION ['temp_kontoauszugsnummer'] )) {
			fehlermeldung_ausgeben ( "Kontrolldaten eingeben!!!" );
			die ();
		}
		
		$arr = $this->get_sepa_files_daten_arr ( $file );
		if (! is_array ( $arr )) {
			fehlermeldung_ausgeben ( "Keine Datensätze zur Datei $file" );
		} else {
			// echo "<tr><th>$kat</th></tr>";
			$anz = count ( $arr );
			for($a = 0; $a < $anz; $a ++) {
				echo "<table>";
				echo "<thead><tr><th>EMPFÄNGER</th><th>DATUM</th><th>AUSZUG</th><th>VZWECK</th><th>BETRAG</th><th>KONTO</th><th></th></tr></thead>";
				$f = new formular ();
				$empf = $arr [$a] ['BEGUENSTIGTER'];
				$vzweck = $arr [$a] ['VZWECK'];
				$iban = $arr [$a] ['IBAN'];
				$bic = $arr [$a] ['BIC'];
				$konto = $arr [$a] ['KONTO'];
				$kat = $arr [$a] ['KAT'];
				$kos_typ = $arr [$a] ['KOS_TYP'];
				$kos_id = $arr [$a] ['KOS_ID'];
				$rr = new rechnung ();
				$kos_bez = $rr->kostentraeger_ermitteln ( $kos_typ, $kos_id );
				$betrag = - $arr [$a] ['BETRAG'];
				$betrag_u = $arr [$a] ['BETRAG'];
				$z = $a + 1;
				$f->erstelle_formular ( "SEPA-Überweisung buchen $kos_typ $empf", null );
				echo "<tr><td>$z. $kos_typ<br>$empf</td><td>$_SESSION[temp_datum]</td><td>$_SESSION[temp_kontoauszugsnummer]</td><td>";
				$f->text_feld ( 'Buchungstext', 'vzweck', "$empf, $kat, $vzweck", 100, 'vzweck', '' );
				echo "</td><td>$betrag</td><td>$konto<br>$kos_typ:$kos_bez</td><td>";
				if ($kat == 'RECHNUNG') {
					die ( fehlermeldung_ausgeben ( 'Rechnungen können nicht automatisch gebucht werden!!!' ) );
				}
				
				$m_ref = $_SESSION ['temp_kontoauszugsnummer'];
				$datum = $_SESSION ['temp_datum'];
				$anz_buchungen = $this->check_ls_buchung ( $_SESSION ['geldkonto_id'], $m_ref, $betrag, $_SESSION ['temp_kontoauszugsnummer'], $datum, $kos_typ, $kos_id );
				$anz_zeilen = $this->get_zeilen_anz_aus_sepa ( $file, $_SESSION ['geldkonto_id'], $betrag_u, $kos_typ, $kos_id );
				// echo $anz_zeilen;
				// if($anz_zeilen>$anz_buchungen){
				// if(!$this->check_ls_buchung($_SESSION['geldkonto_id'], $m_ref, $betrag, $_SESSION['temp_kontoauszugsnummer'], $datum, $kos_typ, $kos_id)){
				if ($anz_zeilen > $anz_buchungen) {
					$f->hidden_feld ( 'gk_id', $_SESSION ['geldkonto_id'] );
					$f->hidden_feld ( 'datum', $_SESSION ['temp_datum'] );
					$f->hidden_feld ( 'auszug', $_SESSION ['temp_kontoauszugsnummer'] );
					$f->hidden_feld ( 'kos_typ', $kos_typ );
					$f->hidden_feld ( 'kos_id', $kos_id );
					$f->hidden_feld ( 'm_ref', $m_ref );
					$f->hidden_feld ( 'konto', $konto );
					$f->hidden_feld ( 'betrag', $betrag );
					$f->hidden_feld ( 'option', 'sepa_ue_buchen' );
					$f->check_box_js ( 'mwst', 'mwst', 'MWSt buchen', '', '' );
					$f->send_button ( 'BuchenBtn', 'Buchen' );
					echo "Zahlungen: $anz_zeilen<br>Buchungen:$anz_buchungen";
				} else {
					echo "Buchungen: $anz_buchungen";
				}
				echo "</td></tr>";
				$f->ende_formular ();
				echo "</table>";
			}
		}
	}
	function sepa_file_buchen_fremd($file) {
		$f = new formular ();
		$f->erstelle_formular ( 'Vorzeichen des Betrages wechseln', '' );
		$f->hidden_feld ( 'vorzeichen', 'TAUSCH' );
		$f->send_button ( 'btn_SepaVZ', 'Vorzeichen wechseln' );
		$f->ende_formular ();
		
		if (isset ( $_REQUEST ['vorzeichen'] )) {
			if ($_SESSION ['sep_vorzeichen'] == '-') {
				$_SESSION ['sep_vorzeichen'] = '';
			} else {
				$_SESSION ['sep_vorzeichen'] = '-';
			}
		}
		$gk = new geldkonto_info ();
		$gk->geld_konto_details ( $_SESSION ['geldkonto_id'] );
		fehlermeldung_ausgeben ( "SEPA wird gebucht auf dem Geldkonto $gk->geldkonto_bez $_SESSION[temp_datum] $_SESSION[temp_kontoauszugsnummer]" );
		if (! isset ( $_SESSION ['temp_kontoauszugsnummer'] )) {
			fehlermeldung_ausgeben ( "Kontrolldaten eingeben!!!" );
			die ();
		}
		
		$arr = $this->get_sepa_files_daten_arr ( $file );
		if (! is_array ( $arr )) {
			fehlermeldung_ausgeben ( "Keine Datensätze zur Datei $file" );
		} else {
			
			$m_ref = $_SESSION ['temp_kontoauszugsnummer'];
			$datum = $_SESSION ['temp_datum'];
			
			// echo "<tr><th>$kat</th></tr>";
			$anz = count ( $arr );
			$f = new formular ();
			$f->erstelle_formular ( "SEPA-Überweisung FREMD", null );
			echo "<table>";
			echo "<thead><tr><th>EMPFöNGER</th><th>DATUM</th><th>AUSZUG</th><th>VZWECK</th><th>BETRAG</th><th>KONTO<input type=\"button\" onclick=\"auswahl_alle(this.form.konto)\" value=\"Alle\"></th><th>Zuweisung</th><th></th></tr></thead>";
			
			for($a = 0; $a < $anz; $a ++) {
				
				$empf = $arr [$a] ['BEGUENSTIGTER'];
				$vzweck = $arr [$a] ['VZWECK'];
				$iban = $arr [$a] ['IBAN'];
				$bic = $arr [$a] ['BIC'];
				$konto = $arr [$a] ['KONTO'];
				$kat = $arr [$a] ['KAT'];
				$kos_typ = $arr [$a] ['KOS_TYP'];
				$kos_id = $arr [$a] ['KOS_ID'];
				$rr = new rechnung ();
				$kos_bez = $rr->kostentraeger_ermitteln ( $kos_typ, $kos_id );
				$betrag = $_SESSION ['sep_vorzeichen'] . $arr [$a] ['BETRAG'];
				$betrag_u = $arr [$a] ['BETRAG'];
				$z = $a + 1;
				
				echo "<tr><td>$z. $kos_typ<br>$empf</td><td>$_SESSION[temp_datum]</td><td>$_SESSION[temp_kontoauszugsnummer]</td><td>";
				$f->text_feld ( 'Buchungstext', 'vzweck[]', "$empf, $kat, $vzweck", 100, 'vzweck', '' );
				$f->hidden_feld ( 'betrag[]', $betrag );
				echo "</td><td>$betrag</td><td>";
				$buc = new buchen ();
				$buc->dropdown_kostenrahmen_nr ( 'Kostenkonto', 'konto[]', 'GELDKONTO', $_SESSION ['geldkonto_id'], '', 'konto' );
				echo "</td><td>$kos_typ:$kos_bez</td>";
				if ($kat == 'RECHNUNG') {
					die ( fehlermeldung_ausgeben ( 'Rechnungen können nicht automatisch gebucht werden!!!' ) );
				}
				$f->hidden_feld ( 'kos_typ[]', $kos_typ );
				$f->hidden_feld ( 'kos_id[]', $kos_id );
				
				echo "</tr>";
				
				// $anz_buchungen = $this->check_ls_buchung($_SESSION['geldkonto_id'], $m_ref, $betrag, $_SESSION['temp_kontoauszugsnummer'], $datum, $kos_typ, $kos_id);
				// $anz_zeilen = $this->get_zeilen_anz_aus_sepa($file, $_SESSION['geldkonto_id'], $betrag_u, $kos_typ, $kos_id);
			}
			echo "</table>";
			$f->hidden_feld ( 'gk_id', $_SESSION ['geldkonto_id'] );
			$f->hidden_feld ( 'datum', $_SESSION ['temp_datum'] );
			$f->hidden_feld ( 'auszug', $_SESSION ['temp_kontoauszugsnummer'] );
			// $f->hidden_feld('kos_typ', $kos_typ);
			// $f->hidden_feld('kos_id', $kos_id);
			$f->hidden_feld ( 'm_ref', $m_ref );
			// $f->hidden_feld('konto', $konto);
			// $f->hidden_feld('betrag', $betrag);
			$f->hidden_feld ( 'option', 'sepa_ue_buchen_fremd' );
			$f->check_box_js ( 'mwst', 'mwst', 'MWSt buchen', '', '' );
			$f->send_button ( 'BuchenBtn', 'Buchen' );
			$f->ende_formular ();
		}
	}
	function get_zeilen_anz_aus_sepa($sepa_file, $gk_id, $betrag, $kos_typ, $kos_id) {
		$result = mysql_query ( "SELECT *  FROM `SEPA_UEBERWEISUNG` WHERE `FILE` LIKE '$sepa_file' AND `BETRAG` = '$betrag' && GK_ID_AUFTRAG='$gk_id' && KOS_TYP='$kos_typ' &&  KOS_ID='$kos_id' AND `AKTUELL` = '1'" );
		
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			return $numrows;
		}
	}
	function sepa_file2pdf($filename) {
		echo $filename;
		// $this->get_sepa_fileinfos($filename);
		// die();
		$arr = $this->get_sepa_files_daten_arr ( $filename );
		if (! is_array ( $arr )) {
			fehlermeldung_ausgeben ( "Keine Datensätze zur Datei $file" );
		} else {
			
			/*
			 * $sum = 0;
			 * $anz = count($arr);
			 * for($a=0;$a<$anz;$a++){
			 * $betrag = $arr[$a]['BETRAG'];
			 * $sum+=$betrag;
			 * }
			 */
			/*
			 * if(isset($this->iban)){
			 * unset($this->iban);
			 * }
			 * if(isset($this->bic)){
			 * unset($this->bic);
			 * }
			 * if(isset($this->bankname)){
			 * unset($this->bankname);
			 * }
			 * if(isset($this->beguenstigter)){
			 */
			
			$this->get_sepa_fileinfos ( $filename );
			
			$arr [$anz] ['VZWECK'] = "<b>SUMME</b>";
			$arr [$anz] ['BETRAG'] = $this->sepa_summe;
			
			/* PDF */
			$pdf = new Cezpdf ( 'a4', 'landscape' );
			$bpdf = new b_pdf ();
			$bpdf->b_header ( $pdf, 'Partner', $_SESSION ['partner_id'], 'landscape', 'Helvetica.afm', 6 );
			
			$pdf->ezText ( "SEPA-Datei: $filename", 12 );
			$pdf->ezText ( "Geldkonto: <b>$this->geldkonto_bez $this->IBAN1 $this->BIC</b>", 12 );
			$pdf->ezSetDy ( - 10 );
			$cols = array (
					'KAT' => "KAT",
					'VZWECK' => "VZWECK",
					'BETRAG' => "Betrag",
					'IBAN' => "IBAN",
					'BIC' => "BIC",
					'BEGUENSTIGTER' => "BEGÜNSTIGTER",
					'KONTO' => "BKONTO" 
			);
			$pdf->ezTable ( $arr, $cols, "Übersicht SEPA-Datei", array (
					'rowGap' => 1.5,
					'showLines' => 1,
					'showHeadings' => 1,
					'shaded' => 1,
					'shadeCol' => array (
							0.9,
							0.9,
							0.9 
					),
					'titleFontSize' => 9,
					'fontSize' => 7,
					'xPos' => 50,
					'xOrientation' => 'right',
					'width' => 750,
					'cols' => array (
							'BETRAG' => array (
									'justification' => 'right',
									'width' => 50 
							),
							'KONTO' => array (
									'justification' => 'right',
									'width' => 50 
							) 
					) 
			) );
			
			/* SEPA AUSGABE */
			ob_clean (); // ausgabepuffer leeren
			$pdf->ezStream ();
		}
	}
	function get_sepa_fileinfos($file) {
		$result = mysql_query ( "SELECT FILE, GK_ID_AUFTRAG, SUM(BETRAG) AS SUMME FROM `SEPA_UEBERWEISUNG` WHERE `FILE`='$file' AND `AKTUELL` = '1' GROUP BY FILE ORDER BY DAT LIMIT 0,1" );
		
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			$row = mysql_fetch_assoc ( $result );
			$this->sepa_summe = $row ['SUMME'];
			$this->sepa_gk_id = $row ['GK_ID_AUFTRAG'];
			
			$this->get_sepa_konto_infos ( $this->sepa_gk_id );
			// print_r($row);
		}
	}
	function form_sammel_ue() {
		if (! isset ( $_SESSION ['geldkonto_id'] )) {
			die ( fehlermeldung_ausgeben ( "Geldkonto wählen" ) );
		} else {
			// if(!isset($_SESSION['partner_id'])){
			// die(fehlermeldung_ausgeben("Partner wählen!"));
			// }
			$gk = new geldkonto_info ();
			$gk->geld_konto_details ( $_SESSION ['geldkonto_id'] );
			// print_r($gk);
			$monat = date ( "m" );
			$jahr = date ( "Y" );
			$sep = new sepa ();
			$f = new formular ();
			$f->erstelle_formular ( 'SEPA-Sammelüberweisung', null );
			
			if (isset ( $_REQUEST ['filter'] )) {
				$filter = $_REQUEST ['filter'];
			} else {
				$filter = '';
			}
			
			$f->text_feld ( 'Filter Empfängergeldkonten', 'filter', $filter, 20, 'filter', '' );
			$f->send_button ( 'btn_Sepa', 'Geldkonten filtern' );
			$f->ende_formular ();
			$f->erstelle_formular ( 'SEPA-Sammelüberweisung', null );
			$f->text_feld_inaktiv ( 'Vom Geldkonto', 'vmgk', $gk->geldkonto_bez, 80, 'vmgkid' );
			$sep->dropdown_sepa_geldkonten_filter ( 'Empfängerkonto wählen', 'empf_sepa_gk_id', 'empf_sepa_gk_id', $filter );
			$f->text_feld ( 'Betrag', 'betrag', "", 10, 'betrag', '' );
			$f->text_feld ( 'VERWENDUNG', 'vzweck', "", 80, 'vzweck', '' );
			$f->hidden_feld ( 'option', 'sepa_sammler_hinzu_ue' );
			// $f->hidden_feld('kat', 'SAMMLER');
			$this->dropdown_sammler_typ ( 'Sammlerkategorie wählen!!!', 'kat', 'kat', '', 'SONSTIGES' );
			$f->hidden_feld ( 'gk_id', $_SESSION ['geldkonto_id'] );
			
			$js_typ = "onchange=\"list_kostentraeger('list_kostentraeger', this.value)\"";
			// $js_typ='';
			$bb = new buchen ();
			// dropdown_kostentreager_typen($label, $name, $id, $js_action){
			
			// dropdown_kostentreager_typen_vw($label, $name, $id, $js_action, $vorwahl_typ){
			if (isset ( $_SESSION ['kos_typ'] )) {
				$bb->dropdown_kostentreager_typen_vw ( 'Kostenträgertyp wählen', 'kos_typ', 'kostentraeger_typ', $js_typ, $_SESSION ['kos_typ'] );
			} else {
				$bb->dropdown_kostentreager_typen ( 'Kostenträgertyp norm', 'kos_typ', 'kostentraeger_typ', $js_typ );
			}
			
			$js_id = "";
			
			if (isset ( $_SESSION ['kos_bez'] )) {
				$bb->dropdown_kostentraeger_bez_vw ( "Kostenträger C1 ", 'kos_id', 'dd_kostentraeger_id', $js_id, $_SESSION ['kos_typ'], $_SESSION ['kos_bez'] );
			} else {
				$bb->dropdown_kostentreager_ids ( 'Kostenträger XC', 'kos_id', 'dd_kostentraeger_id', $js_id );
			}
			// $f->hidden_feld('kos_typ', 'Partner');
			// $f->hidden_feld('kos_id', $_SESSION['partner_id']);
			$kk = new kontenrahmen ();
			$kk->dropdown_kontorahmenkonten ( 'Buchungskonto', 'konto', 'konto', 'GELDKONTO', $_SESSION ['geldkonto_id'], '' );
			// $f->text_feld('Buchungskonto', 'konto', 1000, 5, 'konto', '');
			$f->send_button ( 'btn_Sepa', 'Zum Sammler hinzufügen' );
			// echo "<pre>";
			// print_r($_SESSION);
			$f->ende_formular ();
		}
	}
	function form_sammel_ue_IBAN() {
		if (! isset ( $_SESSION ['geldkonto_id'] )) {
			die ( fehlermeldung_ausgeben ( "Geldkonto wählen" ) );
		} else {
			// if(!isset($_SESSION['partner_id'])){
			// die(fehlermeldung_ausgeben("Partner wählen!"));
			// }
			$gk = new geldkonto_info ();
			$gk->geld_konto_details ( $_SESSION ['geldkonto_id'] );
			// print_r($gk);
			$monat = date ( "m" );
			$jahr = date ( "Y" );
			$sep = new sepa ();
			$f = new formular ();
			
			$f->erstelle_formular ( 'SEPA-Sammelüberweisung an IBAN/BIC', null );
			$f->text_feld_inaktiv ( 'Vom Geldkonto', 'vmgk', $gk->geldkonto_bez, 80, 'vmgkid' );
			// $sep->dropdown_sepa_geldkonten_filter('Empfängerkonto wählen', 'empf_sepa_gk_id', 'empf_sepa_gk_id', $filter);
			$f->text_feld ( 'Empfänger', 'empfaenger', "", 50, 'empfaenger', '' );
			$f->iban_feld ( 'IBAN', 'iban', "", 30, 'iban', '' );
			$f->text_feld ( 'BIC', 'bic', "", 15, 'betrag', '' );
			$f->text_feld ( 'Bankname', 'bank', "", 50, 'bank', '' );
			
			$f->text_feld ( 'Betrag', 'betrag', "", 10, 'betrag', '' );
			$f->text_feld ( 'VERWENDUNG', 'vzweck', "", 80, 'vzweck', '' );
			$f->hidden_feld ( 'option', 'sepa_sammler_hinzu_ue_IBAN' );
			// $f->hidden_feld('kat', 'SAMMLER');
			$this->dropdown_sammler_typ ( 'Sammlerkategorie wählen!!!', 'kat', 'kat', '', 'SONSTIGES' );
			$f->hidden_feld ( 'gk_id', $_SESSION ['geldkonto_id'] );
			
			$js_typ = "onchange=\"list_kostentraeger('list_kostentraeger', this.value)\"";
			// $js_typ='';
			$bb = new buchen ();
			// dropdown_kostentreager_typen($label, $name, $id, $js_action){
			
			// dropdown_kostentreager_typen_vw($label, $name, $id, $js_action, $vorwahl_typ){
			if (isset ( $_SESSION ['kos_typ'] )) {
				$bb->dropdown_kostentreager_typen_vw ( 'Kostenträgertyp wählen', 'kos_typ', 'kostentraeger_typ', $js_typ, $_SESSION ['kos_typ'] );
			} else {
				$bb->dropdown_kostentreager_typen ( 'Kostenträgertyp norm', 'kos_typ', 'kostentraeger_typ', $js_typ );
			}
			
			$js_id = "";
			
			if (isset ( $_SESSION ['kos_bez'] )) {
				$bb->dropdown_kostentraeger_bez_vw ( "Kostenträger C1 ", 'kos_id', 'dd_kostentraeger_id', $js_id, $_SESSION ['kos_typ'], $_SESSION ['kos_bez'] );
			} else {
				$bb->dropdown_kostentreager_ids ( 'Kostenträger XC', 'kos_id', 'dd_kostentraeger_id', $js_id );
			}
			// $f->hidden_feld('kos_typ', 'Partner');
			// $f->hidden_feld('kos_id', $_SESSION['partner_id']);
			$kk = new kontenrahmen ();
			$kk->dropdown_kontorahmenkonten ( 'Buchungskonto', 'konto', 'konto', 'GELDKONTO', $_SESSION ['geldkonto_id'], '' );
			// $f->text_feld('Buchungskonto', 'konto', 1000, 5, 'konto', '');
			$f->send_button ( 'btn_Sepa', 'Zum Sammler hinzufügen' );
			// echo "<pre>";
			// print_r($_SESSION);
			$f->ende_formular ();
		}
	}
	function dropdown_sammler_typ($label, $name, $id, $js_action, $vorwahl_typ) {
		echo "<label for=\"$id\">$label</label><select name=\"$name\" id=\"$id\" size=1 $js_action>\n";
		$arr [0] ['KAT'] = 'ET-AUSZAHLUNG';
		$arr [1] ['KAT'] = 'RECHNUNG';
		$arr [2] ['KAT'] = 'LOHN';
		$arr [3] ['KAT'] = 'KK';
		$arr [4] ['KAT'] = 'STEUERN';
		$arr [5] ['KAT'] = 'HAUSGELD';
		$arr [6] ['KAT'] = 'SONSTIGES';
		
		echo "<option value=\"\">Bitte wählen</option>\n";
		
		for($a = 0; $a < count ( $arr ); $a ++) {
			$typ = $arr [$a] ['KAT'];
			$bez = $arr [$a] ['KAT'];
			if ($vorwahl_typ == $typ) {
				echo "<option value=\"$typ\" selected>$bez</option>\n";
			} else {
				echo "<option value=\"$typ\">$bez</option>\n";
			}
		}
		echo "</select>\n";
	}
	function get_kats_arr($von_gk_id, $kat = null) {
		if ($kat == null) {
			$result = mysql_query ( "SELECT KAT FROM `SEPA_UEBERWEISUNG` WHERE `FILE` IS NULL AND `GK_ID_AUFTRAG` ='$von_gk_id' AND `AKTUELL` = '1' GROUP BY KAT ORDER BY DAT" );
		} else {
			$result = mysql_query ( "SELECT KAT FROM `SEPA_UEBERWEISUNG` WHERE `FILE` IS NULL AND KAT='$kat' AND `GK_ID_AUFTRAG` ='$von_gk_id' AND `AKTUELL` = '1' GROUP BY KAT ORDER BY DAT" );
		}
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			while ( $row = mysql_fetch_assoc ( $result ) )
				$arr [] = $row;
			return $arr;
		}
	}
	function sepa_alle_sammler_anzeigen() {
		$arr = $this->get_kats_arr ( $_SESSION ['geldkonto_id'] );
		if (! is_array ( $arr )) {
			fehlermeldung_ausgeben ( "Keine Dateien im SEPA-Sammler" );
		} else {
			for($a = 0; $a < count ( $arr ); $a ++) {
				$kat = $arr [$a] ['KAT'];
				if ($this->sepa_sammler_anzeigen ( $_SESSION ['geldkonto_id'], $kat ) == true) {
					$gk_id = $_SESSION ['geldkonto_id'];
					echo "<a href=\"?daten=sepa&option=sammler2sepa&gk_id=$gk_id&kat=$kat\">SEPA-Datei für $kat erstellen</a>";
				}
			}
		}
	}
	function datensatz_entfernen($dat) {
		$db_abfrage = "UPDATE SEPA_UEBERWEISUNG SET AKTUELL='0' WHERE DAT='$dat' && FILE IS NULL";
		$result = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		return true;
	}
	function iban_convert($iban, $mysql = 1) {
		if ($mysql != 1) {
			$iban_new = chunk_split ( $iban, 4, ' ' ); // für den Menschen leserlich in 4er Blöcken
		} else {
			$iban_new = str_replace ( ' ', '', $iban ); // für die Maschinen /mysql etc.. hintereinnander
		}
		
		return $iban_new;
	}
	function status_excelsession() {
		
		// if(isset($_SESSION['umsatz_stat'])){
		// $anz_auszuege = count($_SESSION['umsatz_stat']);
		// echo "<span style=\"color:red;\">Konten: $anz_auszuege</span>";
		// }
		if (isset ( $_SESSION ['umsaetze_nok'] )) {
			$anz_nok = count ( $_SESSION ['umsaetze_nok'] );
			$link_nok = "<a href=\"?daten=buchen&option=excel_nok\">NOK: $anz_nok</a>";
			echo "<span style=\"color:red;\">$link_nok</span>";
		}
		
		if (is_array ( $_SESSION ['umsaetze_ok'] )) {
			$anz_ok = count ( $_SESSION ['umsaetze_ok'] );
			echo "&nbsp;|<span style=\"color:green;\">OK: $anz_ok</span>";
		}
		
		if (isset ( $_SESSION ['umsatz_id_temp'] )) {
			$akt = $_SESSION ['umsatz_id_temp'] + 1;
			echo "&nbsp;|&nbsp;<span style=\"color:blue;\">DS: $akt/$anz_ok</span>";
		}
		
		$link_konten = "<a href=\"?daten=buchen&option=uebersicht_excel_konten\">Übersicht Geldkonten</a>";
		echo "&nbsp;|&nbsp;<span style=\"color:yellow;\">$link_konten</span>";
	}
	function menue_konten($gk_id) {
		/*
		 * echo '<pre>';
		 * print_r($_SESSION['umsatz_konten']);
		 */
		$akt_key_int = array_search ( $gk_id, $_SESSION ['umsatz_konten'] );
		$akt_key_aus = $akt_key_int + 1;
		$anz_konten = count ( $_SESSION ['umsatz_konten'] );
		echo "  <b>Geldkonto: $akt_key_aus/$anz_konten</b>";
	}
	function uebersicht_excel_konten() {
		// echo '<pre>';
		// /print_r($_SESSION);
		// print_r($_SESSION['umsatz_konten']);
		// print_r($_SESSION['umsatz_konten_start']);
		// print_r($_SESSION['umsatz_stat']);
		if (is_array ( $_SESSION ['umsatz_konten'] )) {
			echo "<table class=\"sortable\">";
			echo "<thead><tr><th>NR</th><th>GELDKONTO</th><th>Auszug</th><th>ANFANGSSALDO</th><th>ENDSALDO</th><th>AKTUELL</th></tr></thead>";
			$anz_konten = count ( $_SESSION ['umsatz_konten'] );
			for($a = 0; $a < $anz_konten; $a ++) {
				$z = $a + 1;
				$gk_id = $_SESSION ['umsatz_konten'] [$a];
				$start_ds_id = $_SESSION ['umsatz_konten_start'] [$gk_id];
				$gk = new geldkonto_info ();
				$gk->geld_konto_details ( $gk_id );
				
				$auszug = sprintf ( '%01d', $_SESSION ['umsatz_stat'] [$gk_id] ['auszug'] );
				$ksa = nummer_punkt2komma_t ( nummer_komma2punkt ( $_SESSION ['umsatz_stat'] [$gk_id] ['ksa'] ) );
				$kse = nummer_punkt2komma_t ( nummer_komma2punkt ( $_SESSION ['umsatz_stat'] [$gk_id] ['kse'] ) );
				
				$kontostand_aktuell = nummer_punkt2komma_t ( $gk->geld_konto_stand ( $gk_id ) );
				
				if ($kontostand_aktuell == $kse) {
					$ks_aktuell = "<span style=\"color:green;\"><b>$kontostand_aktuell EUR</b></span>";
				} else {
					$ks_aktuell = "<span style=\"color:red;\"><b>$kontostand_aktuell EUR</b></span>";
				}
				
				$link_start = "<a href=\"?daten=buchen&option=excel_buchen_session&ds_id=$start_ds_id\">$gk->geldkonto_bez</a>";
				echo "<tr><td>$z</td><td>$link_start</td><td>$auszug</td><td>$ksa EUR</td><td>$kse EUR</td><td>$ks_aktuell</td></tr>";
			}
			echo "</table>";
		} else {
			fehlermeldung_ausgeben ( 'Keine Konten aus Excelimport!!!' );
		}
	}
	function form_excel_ds($umsatz_id_temp = 0) {
		$kto_verb = $_SESSION ['umsaetze_ok'] [$umsatz_id_temp] [1];
		$gk_id_t = $_SESSION ['umsaetze_ok'] [$umsatz_id_temp] ['GK_ID'];
		$this->menue_konten ( $gk_id_t );
		
		$ksa_bank = $_SESSION ['umsatz_stat'] [$gk_id_t] ['ksa'];
		$kse_bank = $_SESSION ['umsatz_stat'] [$gk_id_t] ['kse'];
		$anz_konten = count ( $_SESSION ['umsatz_stat'] );
		
		$_SESSION ['temp_kontostand'] = $kse_bank;
		$_SESSION ['kontostand_temp'] = $kse_bank;
		
		if (isset ( $_SESSION ['kos_typ'] )) {
			unset ( $_SESSION ['kos_typ'] );
		}
		
		if (isset ( $_SESSION ['kos_id'] )) {
			unset ( $_SESSION ['kos_id'] );
		}
		
		if (isset ( $_SESSION ['kos_bez'] )) {
			unset ( $_SESSION ['kos_bez'] );
		}
		
		$_SESSION ['temp_datum'] = $umsatz_id_temp;
		$akt = $umsatz_id_temp + 1;
		$gesamt = count ( $_SESSION ['umsaetze_ok'] );
		$f = new formular ();
		
		$gk = new geldkonto_info ();
		$gk_id = $_SESSION ['umsaetze_ok'] [$umsatz_id_temp] ['GK_ID'];
		$_SESSION ['geldkonto_id'] = $gk_id;
		
		/* Passendes Objekt wählen */
		$gkk = new gk ();
		$temp_objekt_id = $gkk->get_objekt_id ( $_SESSION ['geldkonto_id'] );
		$_SESSION ['objekt_id'] = $temp_objekt_id;
		
		$gk->geld_konto_details ( $gk_id );
		$kontostand_aktuell = nummer_punkt2komma ( $gk->geld_konto_stand ( $gk_id ) );
		
		if (! isset ( $_SESSION ['temp_kontostand'] )) {
			$_SESSION ['temp_kontostand'] = '0,00';
		}
		
		if ($kontostand_aktuell == $_SESSION ['temp_kontostand']) {
			echo "&nbsp;|&nbsp;<span style=\"color:green;\"><b>KSAKT: $kontostand_aktuell EUR</b></span>";
		} else {
			echo "&nbsp;|&nbsp;<span style=\"color:red;\"><b>KSAKT: $kontostand_aktuell EUR</b></span>";
		}
		
		echo "&nbsp;|&nbsp;<span style=\"color:blue;\">KSA BANK: $ksa_bank | KSE BANK(TEMP): $_SESSION[temp_kontostand] EUR</span>";
		
		$_SESSION ['temp_kontoauszugsnummer'] = sprintf ( '%01d', $_SESSION ['umsaetze_ok'] [$umsatz_id_temp] [3] );
		$_SESSION ['temp_datum'] = $_SESSION ['umsaetze_ok'] [$umsatz_id_temp] [6];
		
		// $f->fieldset('NAVI', 'navi');
		echo "<table style=\"border:0px;padding:1px;><tr><td padding:1px;\"><tr><td>";
		echo "<form method=\"post\" >";
		$f->hidden_feld ( 'vor', '1' );
		$f->send_button ( 'SndNEXT', '<<--' );
		$f->ende_formular ();
		
		echo "</td><td><form method=\"post\">";
		$f->hidden_feld ( 'next', '1' );
		$f->send_button ( 'SndNEXT', '-->>' );
		$f->ende_formular ();
		echo "</td></tr></table>";
		// $f->fieldset_ende();
		
		$art = $_SESSION ['umsaetze_ok'] [$umsatz_id_temp] [13];
		$datum = $_SESSION ['umsaetze_ok'] [$umsatz_id_temp] [6];
		/* FORMULAR */
		$f->erstelle_formular ( "$art - Nummer:$akt/$gesamt | $gk->geldkonto_bez | AUSZUG: $_SESSION[temp_kontoauszugsnummer] | DATUM: $datum ", null );
		
		echo "<table >";
		echo "<tr><td valign=\"top\">";
		$zahler = $_SESSION ['umsaetze_ok'] [$umsatz_id_temp] [25];
		
		$namen_arr = explode ( ',', $zahler );
		if (! isset ( $namen_arr [1] )) {
			$namen_arr = explode ( ' ', $zahler );
		}
		if (! isset ( $namen_arr [1] )) {
			$vorname = '';
		} else {
			$vorname = mysql_real_escape_string ( ltrim ( rtrim ( $namen_arr [1] ) ) );
		}
		$nachname = mysql_real_escape_string ( ltrim ( rtrim ( $namen_arr [0] ) ) );
		
		$zahler_iban = $_SESSION ['umsaetze_ok'] [$umsatz_id_temp] [26];
		$zahler_bic = $_SESSION ['umsaetze_ok'] [$umsatz_id_temp] [27];
		$betrag = $_SESSION ['umsaetze_ok'] [$umsatz_id_temp] [7];
		$betrag_n = str_replace ( '.', '', $betrag );
		echo "<b>$zahler</b><br>$zahler_iban<br>$zahler_bic<br><br><b>BETRAG: $betrag EUR</b>";
		
		$betrag_punkt = nummer_komma2punkt ( $betrag_n );
		$datum_sql = date_german2mysql ( $datum );
		$bu = new buchen ();
		if ($bu->check_buchung ( $_SESSION ['geldkonto_id'], $betrag_punkt, $_SESSION ['temp_kontoauszugsnummer'], $datum_sql )) {
			
			echo "<br><br>";
			fehlermeldung_ausgeben ( "Betrag bereits gebucht!!!" );
		}
		
		echo "<br><hr><u>Buchungstext: </u><hr>";
		
		// echo "</td><td>";
		
		/*
		 * $art = $_SESSION['umsaetze_ok'][$umsatz_id_temp][13];
		 * echo $art;
		 * echo "</td><td>";
		 */
		
		$vzweck = mysql_real_escape_string ( $_SESSION ['umsaetze_ok'] [$umsatz_id_temp] [14] );
		// echo $vzweck;
		// echo '<pre>';
		// print_r($_SESSION);
		
		// echo $vzweck;
		$art = ltrim ( rtrim ( $art ) );
		if (ltrim ( rtrim ( $art ) ) == 'ABSCHLUSS' or $art == 'SEPA-UEBERWEIS.HABEN EINZEL' or $art == 'SEPA-CT HABEN EINZELBUCHUNG' or $art == 'SEPA-DD EINZELB.-SOLL B2B' or $art == 'SEPA-DD EINZELB.SOLL B2B' or $art == 'SEPA-DD EINZELB. SOLL CORE' or $art == 'SEPA-DD EINZELB.SOLL CORE' or $art == 'SEPA Dauerauftragsgutschrift' or $art == 'SEPA DAUERAUFTRAGSGUTSCHR' or $art == 'SEPA-LS EINZELBUCHUNG SOLL' or $art == 'SEPA-UEBERWEIS.HABEN RETOUR' or $art == 'SEPA-CT HABEN RETOUR' or $art == 'ZAHLEINGUEBELEKTRMEDIEN' or $art == 'SCHECKKARTE' or $art == 'ZAHLUNG UEB ELEKTR MEDIEN' or $art == 'LASTSCHRIFT EINZUGSERM') {
			// echo "$art $vzweck";
			$treffer = array ();
			// $pos_svwz = strpos(strtoupper($vzweck), 'ABZR:');
			// if($pos_svwz==true){
			// $vzweck_kurz = substr($vzweck,$pos_svwz+5);
			// }else{
			// $vzweck_kurz = $vzweck;
			// }
			$vzweck_kurz = $vzweck;
			echo $vzweck;
			$laenge = strlen ( $vzweck_kurz );
			// $f->text_feld('Buchungstext', 'text', "$zahler, $vzweck_kurz", 20, 'text', null);
			// echo "<input type=\"text\" id=\"text\" name=\"text\" value=\"$zahler, $vzweck_kurz\" size=\"$laenge\" >";
			
			if (ltrim ( rtrim ( $art ) ) == 'ABSCHLUSS') {
				$zahler = "Bank";
				$vzweck_kurz = "Kontoführungsgebühr, $vzweck_kurz";
				// $bu->dropdown_kostenrahmen_nr('Kostenkonto', 'kostenkonto', 'GELDKONTO', $gk_id, '5060');
			}
			$f->hidden_feld ( 'text', "$zahler, $vzweck_kurz" );
			
			echo "<b>$zahler, $vzweck_kurz</b>";
			echo "</td><td>";
			$bu = new buchen ();
			
			$js_typ = "onchange=\"list_kostentraeger('list_kostentraeger', this.value)\"";
			/* Suche nach IBAN */
			if ($zahler_iban) {
				$gk2 = new gk ();
				$gk2->get_kos_by_iban ( $zahler_iban );
				// echo "IBAN $zahler_iban";
				// echo '<pre>';
				// print_r($gk2);
				if (isset ( $gk2->iban_kos_typ ) && isset ( $gk2->iban_kos_typ )) {
					$_SESSION ['kos_typ'] = $gk2->iban_kos_typ;
					$_SESSION ['kos_id'] = $gk2->iban_kos_id;
					/*
					 * $r = new rechnung();
					 * $akt_kostentraeger_bez =$r->kostentraeger_ermitteln($gk2->iban_kos_typ, $gk2->iban_kos_id);
					 * $_SESSION['kos_bez'] = $akt_kostentraeger_bez;
					 */
					if ($gk2->iban_kos_typ == 'Eigentuemer') {
						$bu->dropdown_kostenrahmen_nr ( 'Kostenkonto ET1', 'kostenkonto', 'GELDKONTO', $gk_id, '6020' );
					}
					if ($gk2->iban_kos_typ == 'Mietvertrag') {
						$bu->dropdown_kostenrahmen_nr ( 'Kostenkonto MV1', 'kostenkonto', 'GELDKONTO', $gk_id, '80001' );
					}
					
					if ($gk2->iban_kos_typ == 'Partner') {
						$bu->dropdown_kostenrahmen_nr ( 'Kostenkonto P', 'kostenkonto', 'GELDKONTO', $gk_id, '' );
					}
					
					if ($gk2->iban_kos_typ == 'Benutzer') {
						$bu->dropdown_kostenrahmen_nr ( 'Kostenkonto B', 'kostenkonto', 'GELDKONTO', $gk_id, '' );
					}
					if ($gk2->iban_kos_typ == 'Objekt') {
						$bu->dropdown_kostenrahmen_nr ( 'Kostenkonto ET1', 'kostenkonto', 'GELDKONTO', $gk_id, '6020' );
						$_SESSION ['kos_typ'] = 'Eigentuemer';
					}
					
					$bu->dropdown_kostentreager_typen_vw ( 'Kostenträgertyp AUTOIBAN', 'kostentraeger_typ', 'kostentraeger_typ', $js_typ, $_SESSION ['kos_typ'] );
					$bu->dropdown_kostentraeger_bez_vw ( "Kostenträger IBAN", 'kostentraeger_id', 'dd_kostentraeger_id', '', $_SESSION ['kos_typ'], $_SESSION ['kos_id'] );
					$treffer [] = 'GK';
				}
			}
			
			if ((strpos ( strtolower ( $vzweck ), 'miet' ) or strpos ( strtolower ( $vzweck ), 'hk' ) or strpos ( strtolower ( $vzweck ), 'bk' )) && count ( $treffer ) < 1) {
				$_SESSION ['kos_typ'] = 'Mietvertrag';
				// $bu->dropdown_kostenrahmen_nr('Kostenkonto', 'kostenkonto', 'GELDKONTO', $gk_id, '80001');
				// $bu->dropdown_kostentreager_typen_vw('Kostenträgertyp vorwahl', 'kostentraeger_typ', 'kostentraeger_typ', $js_typ, 'Mietvertrag');
				// $bu->dropdown_kostentreager_ids('Kostenträger', 'kostentraeger_id', 'dd_kostentraeger_id', '');
				$pe1 = new personen ();
				$treffer = $pe1->finde_kos_typ_id ( $vorname, $nachname );
				
				if ($treffer ['ANZ'] > 0) {
					if ($treffer ['ANZ'] > 1) {
						$kos_typ = $treffer ['ERG_F'] [0] ['KOS_TYP'];
						$kos_id = $treffer ['ERG_F'] [0] ['KOS_ID'];
						$manz = $treffer ['ANZ'];
						echo "<br>";
						fehlermeldung_ausgeben ( "HINWEIS: Mieter kommt mehrmals vor ($manz)!!!" );
						echo "<br>";
					} else {
						$kos_typ = $treffer ['ERG'] [0] ['KOS_TYP'];
						$kos_id = $treffer ['ERG'] [0] ['KOS_ID'];
					}
					
					if ($kos_typ == 'Mietvertrag') {
						$bu->dropdown_kostenrahmen_nr ( 'Kostenkonto M2', 'kostenkonto', 'GELDKONTO', $gk_id, '80001' );
					}
					if ($kos_typ == 'Eigentuemer') {
						$bu->dropdown_kostenrahmen_nr ( 'Kostenkonto E2', 'kostenkonto', 'GELDKONTO', $gk_id, '6020' );
					}
					
					$bu->dropdown_kostentreager_typen_vw ( 'Kostenträgertyp PERSON', 'kostentraeger_typ', 'kostentraeger_typ', $js_typ, $kos_typ );
					$bu->dropdown_kostentraeger_bez_vw ( "Kostenträger PERSON", 'kostentraeger_id', 'dd_kostentraeger_id', '', $kos_typ, $kos_id );
				} else {
					// $bu->dropdown_kostenrahmen_nr('Kostenkonto', 'kostenkonto', 'GELDKONTO', $gk_id, '80001');
					// $bu->dropdown_kostentreager_typen_vw('Kostenträgertyp vorwahl', 'kostentraeger_typ', 'kostentraeger_typ', $js_typ, 'Mietvertrag');
					
					$kos_id = $this->get_mvid_from_vzweck ( $vzweck );
					if (! isset ( $kos_id )) {
						/* ET_ID from* */
						// $kos_id = $this->get_etid_from_vzweck($vzweck);
						// $kos_typ = 'Eigentuemer';
						// $bu->dropdown_kostenrahmen_nr('Kostenkonto', 'kostenkonto', 'GELDKONTO', $gk_id, '6020');
						// $bu->dropdown_kostentreager_typen_vw('ET vorwahl C', 'kostentraeger_typ', 'kostentraeger_typ', $js_typ, 'Eigentuemer');
					} else {
						$kos_typ = 'Mietvertrag';
						$bu->dropdown_kostenrahmen_nr ( 'Kostenkonto', 'kostenkonto', 'GELDKONTO', $gk_id, '80001' );
						$bu->dropdown_kostentreager_typen_vw ( 'Kostenträgertyp vorwahl', 'kostentraeger_typ', 'kostentraeger_typ', $js_typ, 'Mietvertrag' );
					}
					
					if (isset ( $kos_id )) {
						$bu->dropdown_kostentraeger_bez_vw ( "Kostenträger MV2", 'kostentraeger_id', 'dd_kostentraeger_id', '', $kos_typ, $kos_id );
					} else {
						$bu->dropdown_kostenrahmen_nr ( 'Kostenkonto MMM', 'kostenkonto', 'GELDKONTO', $gk_id, '80001' );
						$bu->dropdown_kostentreager_typen_vw ( 'Kostenträger TYP - UNBEKANNT', 'kostentraeger_typ', 'kostentraeger_typ', $js_typ, 'Mietvertrag' );
						$bu->dropdown_kostentreager_ids ( 'Kostenträger UNBEKANNT1', 'kostentraeger_id', 'dd_kostentraeger_id', '' );
					}
				}
				
				/*
				 * if($kos_typ=='Mieter'){
				 * $me = new mietentwicklung;
				 * $me->mietentwicklung_anzeigen($kos_id);
				 * }
				 */
				
				$treffer [] = 'Mieter';
			}
			
			if ((strpos ( strtolower ( $vzweck ), 'hausgeld' ) or strpos ( strtolower ( $vzweck ), 'wohngeld' )) && count ( $treffer ) < 1) {
				/*
				 * $gk2 = new gk;
				 * $gk2->get_kos_by_iban($zahler_iban);
				 * if(isset($gk2->iban_kos_typ) && isset($gk2->iban_kos_typ)){
				 * $_SESSION['kos_typ'] = $gk2->iban_kos_typ;
				 * $_SESSION['kos_id'] = $gk2->iban_kos_id;
				 * /*$r = new rechnung();
				 * $akt_kostentraeger_bez =$r->kostentraeger_ermitteln($gk2->iban_kos_typ, $gk2->iban_kos_id);
				 * $_SESSION['kos_bez'] = $akt_kostentraeger_bez;
				 */
				// $bu->dropdown_kostentraeger_bez_vw("Kostenträger IBAN $gk2->iban_kos_id", 'kostentraeger_id', 'dd_kostentraeger_id', $js_id, $_SESSION['kos_typ'], $_SESSION['kos_id']);
				
				// }else{
				$_SESSION ['kos_typ'] = 'Eigentuemer';
				// }
				
				$bu->dropdown_kostenrahmen_nr ( 'Kostenkonto', 'kostenkonto', 'GELDKONTO', $gk_id, '6020' );
				$bu->dropdown_kostentreager_typen_vw ( 'Kostenträgertyp vorwahl', 'kostentraeger_typ', 'kostentraeger_typ', $js_typ, 'Eigentuemer' );
				$bu->dropdown_kostentreager_ids ( 'Kostenträger', 'kostentraeger_id', 'dd_kostentraeger_id', '' );
				$treffer [] = 'Eigentuemer';
			}
			/* Suche na IBAN */
			/*
			 * $gk2 = new gk;
			 * $gk2->get_kos_by_iban($zahler_iban);
			 * if(isset($gk->iban_kos_typ) && isset($gk->iban_kos_typ)){
			 * $_SESSION['kos_typ'] = $gk->iban_kos_typ;
			 * $_SESSION['kos_id'] = $gk->iban_kos_id;
			 * $bu->dropdown_kostenrahmen_nr('Kostenkonto', 'kostenkonto', 'GELDKONTO', $gk_id, '');
			 * $bu->dropdown_kostentreager_typen_vw('Kostenträgertyp vorwahl', 'kostentraeger_typ', 'kostentraeger_typ', $js_typ, 'Eigentuemer');
			 * #$bu->dropdown_kostentreager_ids('Kostenträger', 'kostentraeger_id', 'dd_kostentraeger_id', '');
			 * $treffer[]= $gk->iban_kos_typ;
			 * }
			 */
			
			/* Wenn nichts gefunden */
			if (count ( $treffer ) < 1) {
				unset ( $_SESSION ['kos_typ'] );
				unset ( $_SESSION ['kos_id'] );
				unset ( $_SESSION ['kos_bez'] );
				
				$pe1 = new personen ();
				// $pe1-get_person_ids_byname_arr($vorname, $nachname);
				
				// $pe1->finde_personen_name($string)
				
				/*
				 * $personen_ids_arr = $pe1->get_person_ids_byname_arr($vorname, $nachname);
				 * if(is_array($personen_ids_arr)){
				 * print_r($personen_ids_arr);
				 * }else{
				 * fehlermeldung_ausgeben("KEINE PERSONEN $vorname $nachname");
				 * }
				 *
				 * $pe = new person;
				 * #$mv_arr=$pe->get_vertrags_ids_von_person($person_id);
				 */
				// echo '<pre>';
				$treffer = $pe1->finde_kos_typ_id ( $vorname, $nachname );
				
				if ($treffer ['ANZ'] > 0) {
					if ($treffer ['ANZ'] > 1) {
						$kos_typ = $treffer ['ERG_F'] [0] ['KOS_TYP'];
						$kos_id = $treffer ['ERG_F'] [0] ['KOS_ID'];
						$manz = $treffer ['ANZ'];
						echo "<br>";
						fehlermeldung_ausgeben ( "HINWEIS: Mieter kommt mehrmals vor ($manz)!!!" );
						echo "<br>";
					} else {
						$kos_typ = $treffer ['ERG'] [0] ['KOS_TYP'];
						$kos_id = $treffer ['ERG'] [0] ['KOS_ID'];
					}
					
					if ($kos_typ == 'Mietvertrag') {
						$bu->dropdown_kostenrahmen_nr ( 'Kostenkonto', 'kostenkonto', 'GELDKONTO', $gk_id, '80001' );
					}
					if ($kos_typ == 'Eigentuemer') {
						$bu->dropdown_kostenrahmen_nr ( 'Kostenkonto', 'kostenkonto', 'GELDKONTO', $gk_id, '6020' );
					}
					
					$bu->dropdown_kostentreager_typen_vw ( 'Kostenträgertyp PERSON2', 'kostentraeger_typ', 'kostentraeger_typ', $js_typ, $kos_typ );
					$bu->dropdown_kostentraeger_bez_vw ( "Kostenträger PERSON2", 'kostentraeger_id', 'dd_kostentraeger_id', '', $kos_typ, $kos_id );
					
					echo "</td></tr><tr><td>";
					
					/*
					 * if($kos_typ=='Mietvertrag'){
					 * $me = new mietentwicklung();
					 * $me->mietentwicklung_anzeigen($kos_id);
					 * }
					 */
				}
				
				if ($treffer ['ANZ'] < 1) {
					$kos_id = $this->get_mvid_from_vzweck ( $vzweck );
					if (isset ( $kos_id )) {
						$kos_typ = 'Mietvertrag';
						$bu->dropdown_kostenrahmen_nr ( 'Kostenkonto', 'kostenkonto', 'GELDKONTO', $gk_id, '80001' );
						$bu->dropdown_kostentreager_typen_vw ( 'Kostenträgertyp MV2', 'kostentraeger_typ', 'kostentraeger_typ', $js_typ, $kos_typ );
						$bu->dropdown_kostentraeger_bez_vw ( "Kostenträger MV2", 'kostentraeger_id', 'dd_kostentraeger_id', '', $kos_typ, $kos_id );
					} else {
						$kos_id = $this->get_etid_from_vzweck ( $vzweck );
						if (isset ( $kos_id )) {
							$kos_typ = 'Eigentuemer';
							$bu->dropdown_kostenrahmen_nr ( 'Kostenkonto', 'kostenkonto', 'GELDKONTO', $gk_id, '6020' );
							$bu->dropdown_kostentreager_typen_vw ( 'ET vorwahl', 'kostentraeger_typ', 'kostentraeger_typ', $js_typ, 'Eigentuemer' );
							$bu->dropdown_kostentraeger_bez_vw ( "Kostenträger MV2", 'kostentraeger_id', 'dd_kostentraeger_id', '', $kos_typ, $kos_id );
						} else {
							if ($art == 'ABSCHLUSS') {
								$kos_id = $this->get_etid_from_vzweck ( $vzweck );
								// echo "SANEL $kos_id";
								$bu->dropdown_kostenrahmen_nr ( 'Kostenkonto', 'kostenkonto', 'GELDKONTO', $gk_id, '5060' );
								// $bu->dropdown_kostentreager_typen('Kostenträgertyp NIXX', 'kostentraeger_typ', 'kostentraeger_typ', 'Objekt');
								$bu->dropdown_kostentreager_typen_vw ( 'ET vorwahl', 'kostentraeger_typ', 'kostentraeger_typ', $js_typ, 'Objekt' );
								$bu->dropdown_kostentraeger_bez_vw ( "Kostenträger MV2", 'kostentraeger_id', 'dd_kostentraeger_id', '', 'Objekt', $_SESSION ['objekt_id'] );
							} else {
								
								$kos_id = $this->get_etid_from_vzweck ( $vzweck );
								// echo "SANEL $kos_id";
								$bu->dropdown_kostenrahmen_nr ( 'Kostenkonto NIX3', 'kostenkonto', 'GELDKONTO', $gk_id, '80001' );
								// $bu->dropdown_kostentreager_typen('Kostenträgertyp NIXX3', 'kostentraeger_typ', 'kostentraeger_typ', $js_typ);
								$bu->dropdown_kostentreager_typen_vw ( 'Kostenträgertyp NIXX3', 'kostentraeger_typ', 'kostentraeger_typ', $js_typ, 'Mietvertrag' );
								$bu->dropdown_kostentraeger_bez_vw ( "Kostenträger NIXX3", 'kostentraeger_id', 'dd_kostentraeger_id', '', 'Mietvertrag', null );
							}
						}
					}
				}
			}
			// $bu->dropdown_kostentreager_typen('Kostenträgertyp', 'kostentraeger_typ', 'kostentraeger_typ', $js_typ);
			
			$f->hidden_feld ( 'option', 'excel_einzelbuchung' );
			$f->hidden_feld ( 'betrag', $betrag_n );
			$f->check_box_js ( 'mwst', 'mwst', 'Mit Mehrwertsteuer buchen', '', '' );
			$f->send_button ( 'SndEB', "Buchen [$betrag EUR]" );
			
			// echo "</td><td>";
			
			/*
			 * if($art=='SEPA Dauerauftragsgutschrift'){
			 * $pos_svwz = strpos(strtoupper($vzweck), 'SVWZ+');
			 * if($pos_svwz==true){
			 * $vzweck_kurz = substr($vzweck,$pos_svwz+5);
			 * }
			 * echo "<br><b>$vzweck_kurz</b>";
			 * }
			 */
			
			// echo "</td><td>";
			
			// echo $betrag;
			echo "</td>";
		} // ##############ENDE EINZELBUCHUNGEN*/
		if ($art == 'SEPA-UEBERWEIS.SAMMLER-SOLL' or $art == 'SEPA-CT SAMMLER-SOLL') {
			echo $vzweck;
			$pos_svwz = strpos ( strtoupper ( $vzweck ), '.XML' );
			if ($pos_svwz == true) {
				$vzweck_kurz = substr ( $vzweck, 0, $pos_svwz + 4 );
				$sepa_ue__file = str_replace ( ' ', '', substr ( $vzweck_kurz, 5 ) );
			} else {
				$vzweck_kurz = $vzweck;
				$sepa_ue__file = ' ----> SEPA-UEBERWEIS.SAMMLER - DATEI - UNBEKANNT!!!!';
			}
			echo "<br><b>$vzweck_kurz $betrag</b><br>$sepa_ue__file";
			echo "</td></tr>";
			echo "<tr><td colspan=\"2\">";
			$sep = new sepa ();
			$sep->sepa_file_anzeigen ( $sepa_ue__file );
		}
		/* LASTSCHRIFTEN LS */
		if ($art == 'SEPA-LS SAMMLER-HABEN') {
			echo "<b>$vzweck<br>";
			echo "<h1>LASTSCHRIFTEN</h1>";
			$betrag_punkt = nummer_komma2punkt ( $betrag_n );
			// $arr_ls_files = $this->finde_ls_file_by_betrag($_SESSION['geldkonto_id'], $betrag_punkt);
			// $arr_ls_files = $this->finde_ls_file_by_monat($_SESSION['geldkonto_id'], $betrag_punkt, $_SESSION['temp_datum']);
			$arr_ls_files = $this->finde_ls_file_by_monat ( $_SESSION ['geldkonto_id'], $betrag_punkt, $_SESSION ['temp_datum'] );
			// echo '<pre>';
			// print_r($arr_ls_files);
			$anz_lf = count ( $arr_ls_files );
			for($lf = 0; $lf < $anz_lf; $lf ++) {
				$ls_file = $arr_ls_files [$lf] ['DATEI'];
				echo "<form method=\"post\">";
				echo "<table>";
				echo "<tr><th colspan=\"1\">$ls_file</th><th>";
				$f->hidden_feld ( 'ls_file', $ls_file );
				$f->hidden_feld ( 'option', 'excel_ls_sammler_buchung' );
				$f->hidden_feld ( 'betrag', $betrag_n );
				$f->check_box_js ( 'mwst', 'mwst', 'Mit Mehrwertsteuer buchen', '', '' );
				$f->send_button ( 'SndEB', "Buchen [$betrag EUR]" );
				
				echo "</th></tr>";
				
				$arr_ls_zeilen = $this->get_sepa_lszeilen_arr ( $ls_file );
				// echo '<pre>';
				// print_r($arr_ls_zeilen);
				$anz_ze = count ( $arr_ls_zeilen );
				for($ze = 0; $ze < $anz_ze; $ze ++) {
					$zweck_ls = $arr_ls_zeilen [$ze] ['VZWECK'];
					$betrag_ls = $arr_ls_zeilen [$ze] ['BETRAG'];
					echo "<tr><td>$zweck_ls</td><td>$betrag_ls</td></tr>";
				}
				echo "</table></form>";
			}
		}
		/* LASTSCHRIFTEN LS */
		if ($art == 'SEPA-LS SOLL RUECKBELASTUNG') {
			echo "<b>$vzweck";
			echo "$betrag</b>";
			$betrag_punkt = nummer_komma2punkt ( $betrag_n );
			// $arr_ls_files = $this->finde_ls_file_by_betrag($_SESSION['geldkonto_id'], $betrag_punkt);
			$arr_ls_files = $this->finde_ls_file_by_datum ( $_SESSION ['geldkonto_id'], $betrag_punkt, $_SESSION ['temp_datum'] );
			// echo '<pre>';
			// print_r($arr_ls_files);
		}
		
		if ($art == 'SEPA DIRECT DEBIT (EINZELBUCHUNG-SOLL, B2B)') {
			echo "<b>$vzweck";
			echo "$betrag</b>";
			fehlermeldung_ausgeben ( "Abbuchung bzw. Rechnungen manuell buchen!!!" );
		}
		
		echo "</td>";
		
		echo "</tr></table>";
		// echo '<pre>';
		// print_r($_SESSION);
		$f->ende_formular ();
	}
	function get_mvid_from_vzweck($vzweck) {
		$vzweck = str_replace ( ',', ' ', $vzweck );
		$vzweck = str_replace ( '.', ' ', $vzweck );
		$vzweck = str_replace ( ' -', ' ', $vzweck );
		// echo $vzweck;
		$pos_svwz = strpos ( strtoupper ( $vzweck ), 'SVWZ+' );
		if ($pos_svwz == true) {
			$vzweck_kurz = str_replace ( ')', ' ', str_replace ( '(', ' ', substr ( $vzweck, $pos_svwz + 5 ) ) );
		} else {
			$vzweck_kurz = $vzweck;
		}
		
		$vzweck_arr = explode ( ' ', strtoupper ( $vzweck_kurz ) );
		$ein = new einheit ();
		$einheiten_arr = $ein->liste_aller_einheiten ();
		
		for($ei = 0; $ei < count ( $einheiten_arr ); $ei ++) {
			$einheit_kurzname = str_replace ( ' ', '', ltrim ( rtrim ( $einheiten_arr [$ei] ['EINHEIT_KURZNAME'] ) ) );
			$ein_arr [] = $einheit_kurzname;
			
			$pos_leer = strpos ( $einheiten_arr [$ei] ['EINHEIT_KURZNAME'], ' ' );
			if ($pos_leer == true) {
				$erstteil = substr ( strtoupper ( $einheiten_arr [$ei] ['EINHEIT_KURZNAME'] ), 0, $pos_leer );
				$ein_arr [] = $erstteil;
			}
		}
		unset ( $einheiten_arr );
		$new_arr = array_intersect ( $vzweck_arr, $ein_arr );
		$arr_keys = array_keys ( $new_arr );
		$anz_keys = count ( $arr_keys );
		for($tt = 0; $tt < $anz_keys; $tt ++) {
			$key1 = $arr_keys [$tt];
			$new_arr1 [] = $new_arr [$key1];
		}
		
		/*
		 * echo '<pre>';
		 * print_r($new_arr);
		 * print_r($new_arr1);
		 */
		if (isset ( $new_arr1 [0] )) {
			$anfang = $new_arr1 [0];
			$einheit_id_n = $ein->finde_einheit_id_by_kurz ( $anfang );
			
			$ein->get_mietvertrag_id ( $einheit_id_n );
			// echo "$anfang $einheit_id_n $ein->mietvertrag_id";
			// $mvs = new mietvertraege();
			// $mvs->get_mietvertrag_infos_aktuell($ein->mietvertrag_id);
			
			/*
			 * echo '<pre>';
			 * print_r($mvs);
			 * #print_r($array3);
			 * print_r($new_arr1);
			 * #print_r($new_arr1);
			 *
			 * print_r($vzweck_arr);
			 * print_r($ein_arr);
			 */
			if (isset ( $ein->mietvertrag_id )) {
				return $ein->mietvertrag_id;
			}
		}
	}
	function get_etid_from_vzweck($vzweck) {
		$vzweck = str_replace ( ',', ' ', $vzweck );
		$vzweck = str_replace ( '.', ' ', $vzweck );
		$vzweck = str_replace ( ' -', ' ', $vzweck );
		// echo $vzweck;
		$pos_svwz = strpos ( strtoupper ( $vzweck ), 'SVWZ+' );
		if ($pos_svwz == true) {
			$vzweck_kurz = str_replace ( ')', ' ', str_replace ( '(', ' ', substr ( $vzweck, $pos_svwz + 5 ) ) );
		} else {
			$vzweck_kurz = $vzweck;
		}
		
		$vzweck_arr = explode ( ' ', strtoupper ( $vzweck_kurz ) );
		$ein = new einheit ();
		$einheiten_arr = $ein->liste_aller_einheiten ();
		
		for($ei = 0; $ei < count ( $einheiten_arr ); $ei ++) {
			$einheit_kurzname = str_replace ( ' ', '', ltrim ( rtrim ( $einheiten_arr [$ei] ['EINHEIT_KURZNAME'] ) ) );
			$ein_arr [] = $einheit_kurzname;
			
			$pos_leer = strpos ( $einheiten_arr [$ei] ['EINHEIT_KURZNAME'], ' ' );
			if ($pos_leer == true) {
				$erstteil = substr ( strtoupper ( $einheiten_arr [$ei] ['EINHEIT_KURZNAME'] ), 0, $pos_leer );
				$ein_arr [] = $erstteil;
			}
		}
		unset ( $einheiten_arr );
		$new_arr = array_intersect ( $vzweck_arr, $ein_arr );
		$arr_keys = array_keys ( $new_arr );
		$anz_keys = count ( $arr_keys );
		for($tt = 0; $tt < $anz_keys; $tt ++) {
			$key1 = $arr_keys [$tt];
			$new_arr1 [] = $new_arr [$key1];
		}
		
		/*
		 * echo '<pre>';
		 * print_r($vzweck_arr);
		 * print_r($new_arr);
		 * print_r($new_arr1);
		 */
		
		if (isset ( $new_arr1 [0] )) {
			$anfang = $new_arr1 [0];
			$einheit_id_n = $ein->finde_einheit_id_by_kurz ( $anfang );
			
			$weg = new weg ();
			$weg->get_last_eigentuemer_id ( $einheit_id_n );
			if (isset ( $weg->eigentuemer_id )) {
				return $weg->eigentuemer_id;
			}
		}
	}
	function form_ds_kontoauszug($ds) {
		$js_typ = "onchange=\"list_kostentraeger('list_kostentraeger', this.value)\"";
		$akt = $ds + 1;
		/* FORMULAR */
		if (isset ( $_SESSION ['kto_auszug_arr'] )) {
			$gesamt = count ( $_SESSION ['kto_auszug_arr'] ) - 2;
			$kto_nr = $_SESSION ['kto_auszug_arr'] ['kto'];
			$kto_blz = $_SESSION ['kto_auszug_arr'] ['blz'];
			/* Suche nach KTO und BLZ */
			$gk = new gk ();
			$gk_id = $gk->get_geldkonto_id2 ( $kto_nr, $kto_blz );
			if (! $gk_id) {
				fehlermeldung_ausgeben ( "Geldkonto <b>$kto_nr - $kto_blz</b> nicht gefunden" );
				die ( 'Abbruch!!!' );
			}
			$_SESSION ['geldkonto_id'] = $gk_id;
			$gk2 = new geldkonto_info ();
			$gk2->geld_konto_details ( $gk_id );
			
			$_SESSION ['temp_datum'] = $_SESSION ['kto_auszug_arr'] [$ds] ['datum'];
			$_SESSION ['temp_kontoauszugsnummer'] = $_SESSION ['kto_auszug_arr'] [$ds] ['auszug'];
			
			$f = new formular ();
			$f->erstelle_formular ( "$gk2->geldkonto_bez | $kto_nr | $kto_blz |DS:$akt/$gesamt AUSZUG: $_SESSION[temp_kontoauszugsnummer] | DATUM: $_SESSION[temp_datum] ", null );
			$f->text_feld_inaktiv ( 'Name', 'btsdxt', $_SESSION ['kto_auszug_arr'] [$ds] ['name'], 100, 'bxcvvctdtd' );
			$f->text_feld_inaktiv ( 'Buchungstext', 'btxt', $_SESSION ['kto_auszug_arr'] [$ds] ['vzweck'], 100, 'btdtd' );
			$f->hidden_feld ( 'text', $_SESSION ['kto_auszug_arr'] [$ds] ['vzweck'] );
			$f->text_feld_inaktiv ( 'Betrag', 'besd', $_SESSION ['kto_auszug_arr'] [$ds] ['betrag'], 10, 'btdsdtd' );
			$f->hidden_feld ( 'betrag', $_SESSION ['kto_auszug_arr'] [$ds] ['betrag'] );
			$bu = new buchen ();
			$kos_id = $this->get_etid_from_vzweck ( $_SESSION ['kto_auszug_arr'] [$ds] ['vzweck'] );
			if (isset ( $kos_id )) {
				$kos_typ = 'Eigentuemer';
				$bu->dropdown_kostenrahmen_nr ( 'Kostenkonto', 'kostenkonto', 'GELDKONTO', $gk_id, '6020' );
				$bu->dropdown_kostentreager_typen_vw ( 'ET vorwahl', 'kostentraeger_typ', 'kostentraeger_typ', $js_typ, 'Eigentuemer' );
				$bu->dropdown_kostentraeger_bez_vw ( "Kostenträger MV2", 'kostentraeger_id', 'dd_kostentraeger_id', '', $kos_typ, $kos_id );
			} else {
				$kos_id = $this->get_mvid_from_vzweck ( $_SESSION ['kto_auszug_arr'] [$ds] ['vzweck'] );
				if (isset ( $kos_id )) {
					$kos_typ = 'Mietvertrag';
					$bu->dropdown_kostenrahmen_nr ( 'Kostenkonto', 'kostenkonto', 'GELDKONTO', $gk_id, '80001' );
					$bu->dropdown_kostentreager_typen_vw ( 'MV vorwahl', 'kostentraeger_typ', 'kostentraeger_typ', $js_typ, 'Eigentuemer' );
					$bu->dropdown_kostentraeger_bez_vw ( "Kostenträger MV2", 'kostentraeger_id', 'dd_kostentraeger_id', '', $kos_typ, $kos_id );
				} else {
					
					$pe1 = new personen ();
					$namen_arr = explode ( ' ', str_replace ( ',', '', $_SESSION ['kto_auszug_arr'] [$ds] ['name'] ) );
					$vorname = $namen_arr [0];
					$nachname = $namen_arr [1];
					$treffer = $pe1->finde_kos_typ_id ( $vorname, $nachname );
					
					if ($treffer ['ANZ'] > 0) {
						if ($treffer ['ANZ'] > 1) {
							$kos_typ = $treffer ['ERG_F'] [0] ['KOS_TYP'];
							$kos_id = $treffer ['ERG_F'] [0] ['KOS_ID'];
						} else {
							$kos_typ = $treffer ['ERG'] [0] ['KOS_TYP'];
							$kos_id = $treffer ['ERG'] [0] ['KOS_ID'];
						}
						
						if ($kos_typ == 'Mietvertrag') {
							$bu->dropdown_kostenrahmen_nr ( 'Kostenkonto PPP', 'kostenkonto', 'GELDKONTO', $gk_id, '80001' );
							$bu->dropdown_kostentreager_typen_vw ( 'MV vorwahl', 'kostentraeger_typ', 'kostentraeger_typ', $js_typ, 'Mietvertrag' );
							$bu->dropdown_kostentraeger_bez_vw ( "Kostenträger MV2", 'kostentraeger_id', 'dd_kostentraeger_id', '', $kos_typ, $kos_id );
						}
						if ($kos_typ == 'Eigentuemer') {
							$bu->dropdown_kostenrahmen_nr ( 'Kostenkonto PPP', 'kostenkonto', 'GELDKONTO', $gk_id, '6020' );
							$bu->dropdown_kostentreager_typen_vw ( 'MV vorwahl', 'kostentraeger_typ', 'kostentraeger_typ', $js_typ, 'Eigentuemer' );
							$bu->dropdown_kostentraeger_bez_vw ( "Kostenträger MV2", 'kostentraeger_id', 'dd_kostentraeger_id', '', $kos_typ, $kos_id );
						}
					} else {
						
						$bu->dropdown_kostenrahmen_nr ( 'Kostenkonto', 'kostenkonto', 'GELDKONTO', $gk_id, '' );
						$bu->dropdown_kostentreager_typen ( 'Kostenträgertyp NIXX', 'kostentraeger_typ', 'kostentraeger_typ', $js_typ );
						$bu->dropdown_kostentreager_ids ( 'Kostenträger NIXX', 'kostentraeger_id', 'dd_kostentraeger_id', '' );
					}
					
					/*
					 * if(!$kos_typ && !$kos_id){
					 *
					 * $bu->dropdown_kostenrahmen_nr('Kostenkonto', 'kostenkonto', 'GELDKONTO', $gk_id, '');
					 * $bu->dropdown_kostentreager_typen('Kostenträgertyp NIXX', 'kostentraeger_typ', 'kostentraeger_typ', $js_typ);
					 * $bu->dropdown_kostentreager_ids('Kostenträger NIXX', 'kostentraeger_id', 'dd_kostentraeger_id', '');
					 *
					 * }
					 */
				}
			}
			$f->hidden_feld ( 'option', 'excel_einzelbuchung' );
			$f->check_box_js ( 'mwst', 'mwst', 'Mit Mehrwertsteuer buchen', '', '' );
			$betrag = $_SESSION ['kto_auszug_arr'] [$ds] ['betrag'];
			$f->send_button ( 'SndEB', "Buchen [$betrag EUR]" );
			// echo "<pre>";
			// print_r($_SESSION['kto_auszug_arr'][$ds]);
			$f->ende_formular ();
		} else {
			fehlermeldung_:
			ausgeben ( "Keine Daten" );
		}
	}
	function form_upload_excel_ktoauszug($action = null) {
		$f = new formular ();
		$f->fieldset ( 'Upload Excel-Kontoauszüge aus Bank *.XLSX', 'upxel' );
		if ($action == null) {
			echo "<form method=\"post\" enctype=\"multipart/form-data\">";
		} else {
			echo "<form method=\"post\" enctype=\"multipart/form-data\" action=\"$action\">";
		}
		echo '<input type="file" name="file"  />&nbsp;&nbsp;<input type="submit" value="Datei Hochladen" />';
		$f->fieldset_ende ();
		// </form>';
	}
} // end class sepa

?>