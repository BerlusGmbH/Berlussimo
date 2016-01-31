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
 * @filesource   $HeadURL: http://192.168.2.52/svn/berlussimo_1/tags/02.11.2010 - Downloadversion 0.27/classes/class_buchen.php $
 * @version      $Revision: 15 $
 * @modifiedby   $LastChangedBy: sivac $
 * @lastmodified $Date: 2011-07-07 10:41:33 +0200 (Do, 07 Jul 2011) $
 * 
 */

/*
 * Klasse Buchen ist für die Buchung von Zahlbeträgen zu Geldkonten verantwortlich
 * Aufgaben: Buchungsformulare, Speichern von Datensätzen
 */
include_once ("config.inc.php");
if (file_exists ( "classes/kasse_class.php" )) {
	include_once ("classes/kasse_class.php");
}
if (file_exists ( "classes/class_statistik.php" )) {
	include_once ("classes/class_statistik.php"); /* Erforderlich für den Monatsabschluss */
}
if (file_exists ( "classes/mietkonto_class.php" )) {
	include_once ("classes/mietkonto_class.php"); /* Erforderlich für den Monatsabschluss */
}
if (file_exists ( "classes/class_mietvertrag.php" )) {
	include_once ("classes/class_mietvertrag.php"); /* Erforderlich für den Monatsabschluss */
}
if (file_exists ( "classes/class_geldkonten.php" )) {
	include_once ("classes/class_geldkonten.php"); /* Erforderlich für den Monatsabschluss */
}
if (file_exists ( "classes/class_weg.php" )) {
	include_once ("classes/class_weg.php");
}
class buchen {
	var $globalMultisortVar = array ();
	function geldkonto_auswahl($link) {
		if (isset ( $_REQUEST ['geldkonto_id'] )) {
			$_SESSION ['geldkonto_id'] = $_REQUEST ['geldkonto_id'];
			
			/* Passendes Objekt wühlen */
			$gkk = new gk ();
			$temp_objekt_id = $gkk->get_objekt_id ( $_SESSION ['geldkonto_id'] );
			$_SESSION ['objekt_id'] = $temp_objekt_id;
			
			if (isset ( $_SESSION ['last_url'] )) {
				$url = $_SESSION ['last_url'];
				unset ( $_SESSION ['last_url'] );
				weiterleiten ( $url );
			} else {
				header ( "Location: index.php" );
			}
		}
		if (isset ( $_SESSION ['geldkonto_id'] )) {
			$link_kontoauszug = "<a href=\"?daten=buchen&option=kontoauszug_form\">Kontrolldaten zum Kontoauszug eingeben</a>";
			$link_sepa_ls = "<a href=\"index.php?daten=sepa&option=ls_auto_buchen\">LS-Autobuchen</a>";
			$aendern_link = "<a href=\"?daten=buchen&option=geldkonto_aendern\">Geldkonto ändern</a>";
			$this->akt_konto_bezeichnung = $this->geld_konto_bezeichung ( $_SESSION ['geldkonto_id'] );
			echo "Ausgewähltes Geldkonto -> $this->akt_konto_bezeichnung $aendern_link $link_kontoauszug $link_sepa_ls<br>";
			$geld = new geldkonto_info ();
			$kontostand_aktuell = nummer_punkt2komma ( $geld->geld_konto_stand ( $_SESSION ['geldkonto_id'] ) );
			// echo "<h3>Kontostand aktuell: $kontostand_aktuell €</h3>";
			if (isset ( $_SESSION ['temp_kontostand'] ) && isset ( $_SESSION ['temp_kontoauszugsnummer'] )) {
				$kontostand_temp = nummer_punkt2komma ( $_SESSION ['temp_kontostand'] );
				echo "<h3>Kontostand am $_SESSION[temp_datum] laut Kontoauszug $_SESSION[temp_kontoauszugsnummer] war $kontostand_temp €</h3>";
			} else {
				echo "<h3 style=\"color:red\">Kontrolldaten zum Kontoauszug fehlen</h3>";
				echo "<h3 style=\"color:red\">Weiterleitung erfolgt</h3>";
				weiterleiten_in_sec ( "?daten=buchen&option=kontoauszug_form", 2 );
			}
			if ($kontostand_aktuell == $kontostand_temp) {
				echo "<h3>Kontostand aktuell: $kontostand_aktuell €</h3>";
			} else {
				echo "<h3 style=\"color:red\">Kontostand aktuell: $kontostand_aktuell €</h3>";
			}
		}
		if (! isset ( $_SESSION ['geldkonto_id'] )) {
			// echo "Geldkonto wählen<br>";
			$geld_konten_arr = $this->alle_geldkonten_arr ();
			// print_r($geld_konten_arr);
			$anzahl_objekte = count ( $geld_konten_arr );
			if (is_array ( $geld_konten_arr )) {
				echo "<p class=\"geldkonto_auswahl\">";
				for($i = 0; $i < $anzahl_objekte; $i ++) {
					$konto_id = $geld_konten_arr [$i] ['KONTO_ID'];
					// echo $konto_id;
					$gk = new gk ();
					if ($gk->check_zuweisung_kos_typ ( $konto_id, 'Objekt', '' )) {
						$sortiert [] = $geld_konten_arr [$i];
					} else {
						
						$unsortiert [] = $geld_konten_arr [$i];
					}
					// echo "<a class=\"objekt_auswahl_buchung\" href=\"$link&geldkonto_id=".$geld_konten_arr[$i][KONTO_ID]."\">".$geld_konten_arr[$i][BEZEICHNUNG]."</a>&nbsp;<br>";
				}
				echo "<table class=\"sortable\">";
				echo "<tr><th>GELDKONTEN DER OBJEKTE</th></tr>";
				$z = 0;
				// echo '<pre>';
				
				// print_r($sortiert);
				// Aufruf von array_multisort() mit dem Array, das sortiert werden soll und den entsprechenden Flags
				$records = array_sortByIndex ( $sortiert, 'BEZEICHNUNG' );
				// echo "<HR>";
				// print_r($records);
				$sortiert = $records;
				unset ( $records );
				for($i = 0; $i < count ( $sortiert ); $i ++) {
					$z ++;
					$konto_id = $sortiert [$i] ['KONTO_ID'];
					$bez = $sortiert [$i] ['BEZEICHNUNG'];
					echo "<tr class=\"zeile$z\"><td><a class=\"objekt_auswahl_buchung\" href=\"$link&geldkonto_id=$konto_id\">$bez</a>&nbsp;</td></tr>";
					if ($z == 2) {
						$z = 0;
					}
				}
				
				echo "</table>";
				
				echo "<table>";
				echo "<tr><th>ANDERE GELDKONTEN</th></tr>";
				$z = 0;
				/*
				 * $records = array_sortByIndex($unsortiert,'BEZEICHNUNG');
				 * $unsortiert = $records;
				 * unset($records);
				 */
				for($i = 0; $i < count ( $unsortiert ); $i ++) {
					$z ++;
					$konto_id = $unsortiert [$i] ['KONTO_ID'];
					$bez = $unsortiert [$i] ['BEZEICHNUNG'];
					echo "<tr class=\"zeile$z\"><td><a class=\"objekt_auswahl_buchung\" href=\"$link&geldkonto_id=$konto_id\">$bez</a>&nbsp;</td></tr>";
					if ($z == 2) {
						$z = 0;
					}
				}
				echo "</table>";
				echo "</p>";
			} else {
				echo "Keine Geldkonten";
			}
		}
	}
	
	// arrayColumnSort(string $field, [options, ], string $field2, [options, ], .... , $array) /
	
	// ____________________
	// arrayColumnSort() /
	function arrayColumnSort() {
		$args = func_get_args ();
		$array = array_pop ( $args );
		if (! is_array ( $array ))
			return false;
			// Here we'll sift out the values from the columns we want to sort on, and put them in numbered 'subar' ("sub-array") arrays.
			// (So when sorting by two fields with two modifiers (sort options) each, this will create $subar0 and $subar3)
		foreach ( $array as $key => $row ) // loop through source array
			foreach ( $args as $akey => $val ) // loop through args (fields and modifiers)
				if (is_string ( $val )) // if the arg's a field, add its value from the source array to a sub-array
					${"subar$akey"} [$key] = $row [$val];
			// $multisort_args contains the arguments that would (/will) go into array_multisort(): sub-arrays, modifiers and the source array
		$multisort_args = array ();
		foreach ( $args as $key => $val )
			$multisort_args [] = (is_string ( $val ) ? ${"subar$key"} : $val);
		$multisort_args [] = &$array; // finally add the source array, by reference
		call_user_func_array ( "array_multisort", $multisort_args );
		return $array;
	}
	function geldkonto_auswahl_menu($link) {
		// print_r($_SESSION);
		if (isset ( $_REQUEST ['geldkonto_id'] )) {
			$_SESSION ['geldkonto_id'] = $_REQUEST ['geldkonto_id'];
		}
		
		if (isset ( $_SESSION ['geldkonto_id'] )) {
			$aendern_link = "<a href=\"?daten=buchen&option=geldkonto_aendern\">Geldkonto ändern</a>";
			$this->akt_konto_bezeichnung = $this->geld_konto_bezeichung ( $_SESSION [geldkonto_id] );
			echo "Ausgewähltes Geldkonto -> $this->akt_konto_bezeichnung $aendern_link<br>";
			$geld = new geldkonto_info ();
			$kontostand_aktuell = nummer_punkt2komma ( $geld->geld_konto_stand ( $_SESSION [geldkonto_id] ) );
		}
		if (! isset ( $_SESSION ['geldkonto_id'] )) {
			echo "Geldkonto wählen<br>";
			$geld_konten_arr = $this->alle_geldkonten_arr ();
			$anzahl_objekte = count ( $geld_konten_arr );
			if (is_array ( $geld_konten_arr )) {
				echo "<p class=\"geldkonto_auswahl\">";
				for($i = 0; $i <= $anzahl_objekte; $i ++) {
					echo "<a class=\"objekt_auswahl_buchung\" href=\"$link&geldkonto_id=" . $geld_konten_arr [$i] ['KONTO_ID'] . "\">" . $geld_konten_arr [$i] ['BEZEICHNUNG'] . "</a>&nbsp;<br>";
				}
				echo "</p>";
			} else {
				echo "Keine Geldkonten";
			}
		}
	}
	function dropdown_ra_buch($kos_typ, $kos_id, $anzahl = 100, $rnr_kurz) {
		$r = new rechnungen ();
		$result = mysql_query ( "SELECT * FROM RECHNUNGEN WHERE AUSSTELLER_ID='$kos_id' && AUSSTELLER_TYP='$kos_typ' && AKTUELL = '1' ORDER BY BELEG_NR DESC LIMIT 0,$anzahl" );
		$numrows = mysql_numrows ( $result );
		echo "<label for=\"geld_konto_dropdown\">Ausgangsbeleg</label>";
		echo "<select name=\"erf_nr\">";
		echo "<option value=\"OHNE BELEG\">Ohne Beleg</option>";
		if ($numrows > 0) {
			
			while ( $row = mysql_fetch_assoc ( $result ) ) {
				$rnr = ltrim ( rtrim ( $row ['RECHNUNGSNUMMER'] ) );
				$erf_nr = $row ['BELEG_NR'];
				if (! empty ( $rnr_kurz ) && $rnr == $rnr_kurz) {
					echo "<option value=\"$erf_nr\" selected>$rnr</option>";
				} else {
					echo "<option value=\"$erf_nr\">$rnr</option>";
				}
			}
		}
		echo "</select>";
	}
	function einnahmen_ausgaben($geldkonto_id, $jahr) {
		$k = new kontenrahmen ();
		$k_id = $k->get_kontenrahmen ( 'GELDKONTO', $geldkonto_id );
		
		$konten_arr = $this->konten_aus_buchungen ( $geldkonto_id );
		echo "<pre>";
		print_r ( $konten_arr );
		$anz = count ( $konten_arr );
		for($a = 0; $a < $anz; $a ++) {
			$konto = $konten_arr [$a] ['KONTO'];
			$k->konto_informationen2 ( $konto, $k_id );
			$this->konto_art_bezeichnung [] = $k->konto_art_bezeichnung;
		}
		$konto_arten_arr = array_unique ( $this->konto_art_bezeichnung );
		unset ( $this->konto_art_bezeichnung );
		print_r ( $konto_arten_arr );
		$konto_arten_arr = array_values ( $konto_arten_arr );
		print_r ( $konto_arten_arr );
		
		$anz_art = count ( $konto_arten_arr );
		$z = 0;
		for($a = 0; $a < $anz; $a ++) {
			$konto = $konten_arr [$a] ['KONTO'];
			$k->konto_informationen2 ( $konto, $k_id );
			
			for($b = 0; $b < $anz_art; $b ++) {
				$art = $konto_arten_arr [$b];
				if ($art == $k->konto_art_bezeichnung) {
					$new_arr [$art] [$z] ['KONTO'] = $konto;
					$new_arr [$art] [$z] ['BEZ'] = $k->konto_bezeichnung;
					$new_arr [$art] [$z] ['SUMME_A'] = nummer_punkt2komma_t ( $this->summe_kontobuchungen_jahr ( $geldkonto_id, $konto, $jahr ) );
					$new_arr [$art] [$z] ['SUMME'] = $this->summe_kontobuchungen_jahr ( $geldkonto_id, $konto, $jahr );
					$z ++;
				}
			}
		}
		
		print_r ( $new_arr );
		
		ob_clean (); // ausgabepuffer leeren
		//include_once ('pdfclass/class.ezpdf.php');
		include_once ('classes/class_bpdf.php');
		$pdf = new Cezpdf ( 'a4', 'portrait' );
		$bpdf = new b_pdf ();
		$bpdf->b_header ( $pdf, 'Partner', $_SESSION [partner_id], 'portrait', 'pdfclass/fonts/Helvetica.afm', 6 );
		for($b = 0; $b < $anz_art; $b ++) {
			$art = $konto_arten_arr [$b];
			$pdf->ezTable ( $new_arr [$art] );
			$pdf->ezSetDy ( - 12 ); // abstand
		}
		ob_clean (); // ausgabepuffer leeren
		$pdf->ezStream ();
	}
	function dropdown_re_buch($kos_typ, $kos_id, $anzahl = 100, $rnr_kurz) {
		$r = new rechnungen ();
		$result = mysql_query ( "SELECT * FROM RECHNUNGEN WHERE EMPFAENGER_ID='$kos_id' && EMPFAENGER_TYP='$kos_typ' && AKTUELL = '1' ORDER BY BELEG_NR DESC LIMIT 0,$anzahl" );
		$numrows = mysql_numrows ( $result );
		echo "<label for=\"geld_konto_dropdown\">Eingangsbeleg</label>";
		echo "<select name=\"erf_nr\">";
		echo "<option value=\"OHNE BELEG\">Ohne Beleg</option>";
		if ($numrows > 0) {
			
			while ( $row = mysql_fetch_assoc ( $result ) ) {
				$rnr = ltrim ( rtrim ( $row ['RECHNUNGSNUMMER'] ) );
				$erf_nr = $row ['BELEG_NR'];
				if ($rnr == $rnr_kurz) {
					echo "<option value=\"$erf_nr\" selected>$rnr</option>";
				} else {
					echo "<option value=\"$erf_nr\">$rnr</option>";
				}
			}
		}
		echo "</select>";
	}
	function zb_buchen_form($geldkonto_id) {
		// echo "<hr><br>";
		// echo "<pre>";
		// print_r($_SESSION);
		$geldkonto_id = $_SESSION ['geldkonto_id'];
		$form = new formular ();
		$form->hidden_feld ( "geldkonto_id", "$geldkonto_id" );
		if (! isset ( $_SESSION ['temp_datum'] )) {
			$heute = date ( "d.m.Y" );
		} else {
			$heute = $_SESSION ['temp_datum'];
		}
		
		$_SESSION ['last_url'] = '?daten=buchen&option=kontoauszug_form';
		
		$form->text_feld ( "Datum:", "datum", $heute, "10", 'datum', '' );
		$form->text_feld ( "Kontoauszugsnummer:", "kontoauszugsnummer", $_SESSION ['temp_kontoauszugsnummer'], "10", 'kontoauszugsnummer', '' );
		$form->text_feld ( "R-Erfassungsnr:", "rechnungsnr", $_SESSION ['temp_kontoauszugsnummer'], "10", 'rechnungsnr', '1200' );
		$form->text_feld ( "Betrag:", "betrag", "-", "10", 'betrag', '' );
		$js_mwst = "onclick=\"mwst_rechnen('betrag','mwst', '19')\" ondblclick=\"mwst_rechnen('betrag','mwst', '7')\"";
		$form->text_feld ( "MwSt-Anteil:", "mwst", "", "10", 'mwst', $js_mwst );
		$this->dropdown_kostenrahmen_nr ( 'Kostenkonto', 'kostenkonto', 'GELDKONTO', $geldkonto_id, '' );
		// $form->text_feld_inaktiv("Kontobezeichnung", "kontobezeichnung", "", "20", 'kontobezeichnung');
		// $form->text_feld_inaktiv("Kontoart", "kontoart", "", "20", 'kontoart');
		// $form->text_feld_inaktiv("Kostengruppe", "kostengruppe", "", "20", 'kostengruppe');
		$js_typ = "onchange=\"list_kostentraeger('list_kostentraeger', this.value)\"";
		// $js_typ='';
		$this->dropdown_kostentreager_typen ( 'Kostenträgertyp', 'kostentraeger_typ', 'kostentraeger_typ', $js_typ );
		$js_id = "";
		$this->dropdown_kostentreager_ids ( 'Kostenträger', 'kostentraeger_id', 'dd_kostentraeger_id', $js_id );
		$form->text_bereich ( 'Buchungstext', 'vzweck', '', 15, 20, 'v_zweck_buchungstext' );
		
		$form->send_button ( "submit_zb_buchen", "Buchen" );
		$form->hidden_feld ( "option", "buchung_gesendet" );
		$form->ende_formular ();
	}
	function geldbuchungs_dat_infos($buchungs_dat) {
		$result = mysql_query ( "SELECT * FROM GELD_KONTO_BUCHUNGEN WHERE GELD_KONTO_BUCHUNGEN_DAT='$buchungs_dat' && AKTUELL='1' LIMIT 0,1" );
		$row = mysql_fetch_assoc ( $result );
		$this->akt_buch_dat = $row ['GELD_KONTO_BUCHUNGEN_DAT'];
		$this->akt_buch_id = $row ['GELD_KONTO_BUCHUNGEN_ID'];
		$this->g_buchungsnummer = $row ['G_BUCHUNGSNUMMER'];
		$this->akt_auszugsnr = $row ['KONTO_AUSZUGSNUMMER'];
		$this->akt_erfass_nr = $row ['ERFASS_NR'];
		unset ( $this->akt_betrag_punkt );
		$this->akt_betrag_punkt = $row ['BETRAG'];
		$this->akt_betrag_komma = nummer_punkt2komma ( $row ['BETRAG'] );
		$this->akt_datum = date_mysql2german ( $row ['DATUM'] );
		$this->akt_vzweck = $row ['VERWENDUNGSZWECK'];
		$this->kostentraeger_typ = $row ['KOSTENTRAEGER_TYP'];
		$this->kostentraeger_id = $row ['KOSTENTRAEGER_ID'];
		$this->kostenkonto = $row ['KONTENRAHMEN_KONTO'];
		$this->geldkonto_id = $row ['GELDKONTO_ID'];
		$this->akt_mwst_anteil = $row ['MWST_ANTEIL'];
		$this->akt_mwst_anteil_komma = nummer_punkt2komma ( $row ['MWST_ANTEIL'] );
	}
	function geldbuchungs_dat_deaktivieren($buchungs_dat) {
		$db_abfrage = "UPDATE GELD_KONTO_BUCHUNGEN SET AKTUELL='0' WHERE GELD_KONTO_BUCHUNGEN_DAT='$buchungs_dat'";
		$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		protokollieren ( 'GELD_KONTO_BUCHUNGEN_DAT', $buchungs_dat, $buchungs_dat );
		echo "Alter Eintrag deaktiviert<br>";
	}
	function speichern_in_geldbuchungen($geldbuchung_id, $g_buchungsnummer, $betrag, $datum, $kostentraeger_typ, $kostentraeger_bez, $vzweck, $kostenkonto, $geldkonto_id, $kontoauszugsnr, $erfass_nr, $mwst = '0.00', $alt_dat = '') {
		$buchung = new buchen ();
		
		if ($kostentraeger_typ != 'Rechnung' && $kostentraeger_typ != 'Mietvertrag') {
			// if($kostentraeger_typ !=='Rechnung'){
			$kostentraeger_id = $buchung->kostentraeger_id_ermitteln ( $kostentraeger_typ, $kostentraeger_bez );
		} else {
			$kostentraeger_id = $kostentraeger_bez;
		}
		
		if (! is_numeric ( $kostentraeger_id ) or $kostentraeger_id == '0' or $kostentraeger_id == null or ! $kostentraeger_id) {
			/* deaktivierte Buchung aktivieren */
			$db_abfrage = "UPDATE GELD_KONTO_BUCHUNGEN SET AKTUELL='1' WHERE GELD_KONTO_BUCHUNGEN_DAT='$alt_dat'";
			$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
			protokollieren ( 'GELD_KONTO_BUCHUNGEN_DAT', $alt_dat, $alt_dat );
			echo "Alter Eintrag Aktiviert<br>";
			die ( fehlermeldung_ausgeben ( "Fehler mit Kostenträgern, keine Änderung gespeichert!!!!" ) );
		}
		
		$datum = date_german2mysql ( $datum );
		$datum_arr = explode ( '-', $datum );
		$t_jahr = $datum_arr [0];
		$t_monat = $datum_arr [1];
		$t_tag = $datum_arr [2];
		
		$_SESSION ['t_tag'] = $t_tag;
		$_SESSION ['t_monat'] = $t_monat;
		$_SESSION ['t_jahr'] = $t_jahr;
		
		$betrag1 = nummer_komma2punkt ( $betrag );
		$mwst1 = nummer_komma2punkt ( $mwst );
		$db_abfrage = "INSERT INTO GELD_KONTO_BUCHUNGEN VALUES (NULL, '$geldbuchung_id', '$g_buchungsnummer', '$kontoauszugsnr', '$erfass_nr','$betrag1', '$mwst1', '$vzweck', '$geldkonto_id', '$kostenkonto', '$datum', '$kostentraeger_typ', '$kostentraeger_id', '1')";
		$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		echo "Buchungsnr $geldbuchung_id wurde geändert!<br>";
		echo "Sie werden zum Buchungsjournal weitergeleitet!";
		weiterleiten_in_sec ( "?daten=buchen&option=buchungs_journal_druckansicht&monat=$t_monat&jahr=$t_jahr", 2 );
	}
	function buchungsmaske_buchung_aendern($buchungs_dat) {
		$form = new formular ();
		$form->erstelle_formular ( "Buchung ändern", NULL );
		
		echo '<pre>';
		$this->geldbuchungs_dat_infos ( $buchungs_dat );
		// print_r($this);
		// die();
		$form->hidden_feld ( "buch_dat_alt", $buchungs_dat );
		$form->hidden_feld ( "akt_buch_id", $this->akt_buch_id );
		$form->hidden_feld ( "g_buchungsnummer", $this->g_buchungsnummer );
		$form->text_feld_inaktiv ( 'Buchungsnr', 'g_buchungsnummer', $this->g_buchungsnummer, '10', 'Buchungsnr' );
		
		$form->hidden_feld ( 'geldkonto_id', $this->geldkonto_id );
		
		$form->text_feld ( 'Datum', 'datum', $this->akt_datum, '10', 'datum', '' );
		$form->text_feld ( 'Kontoauszugsnr', 'kontoauszugsnr', $this->akt_auszugsnr, '10', 'kontoauszugsnr', '' );
		$form->text_feld ( 'Erfassungsnr', 'erfassungsnr', $this->akt_erfass_nr, '10', 'erfassungsnr', '' );
		$form->text_feld ( 'Betrag', 'betrag', $this->akt_betrag_komma, '10', 'betrag', '' );
		$js_mwst = "onclick=\"mwst_rechnen('betrag','mwst', '19')\" ondblclick=\"mwst_rechnen('betrag','mwst', '7')\"";
		$form->text_feld ( "MwSt-Anteil:", "mwst", "$this->akt_mwst_anteil_komma", "10", 'mwst', $js_mwst );
		$form->text_feld_inaktiv ( 'Kostenträger & Kontierung', 'info', "$this->kostentraeger_typ $this->kostentraeger_id | Kostenkonto: $this->kostenkonto", '50', '' );
		$this->dropdown_kostenrahmen_nr ( 'Kostenbkonto', 'kostenkonto', 'GELDKONTO', $this->geldkonto_id, $this->kostenkonto );
		$form->text_bereich ( 'Verwendungszweck', 'vzweck', $this->akt_vzweck, 10, 5, 'vzweck' );
		// $k = new kasse;
		// $akt_kostentraeger_bez = $k->kostentraeger_beschreibung($this->kostentraeger_typ, $this->kostentraeger_id);
		$r = new rechnung ();
		$akt_kostentraeger_bez = $r->kostentraeger_ermitteln ( $this->kostentraeger_typ, $this->kostentraeger_id );
		$akt_kostentraeger_bez = str_replace ( "<b>", "", $akt_kostentraeger_bez );
		$akt_kostentraeger_bez = str_replace ( "</b>", "", $akt_kostentraeger_bez );
		
		// if($this->kostentraeger_typ!='Rechnung' && $this->kostentraeger_typ!='Mietvertrag'){
		if ($this->kostentraeger_typ != 'Rechnung') {
			$buchung = new buchen ();
			$js_typ = "onchange=\"list_kostentraeger('list_kostentraeger', this.value)\"";
			$buchung->dropdown_kostentreager_typen_vw ( 'Kostenträgertyp wählen', 'kostentraeger_typ', 'kostentraeger_typ', $js_typ, $this->kostentraeger_typ );
			
			$js_id = "";
			
			// $buchung->dropdown_kostentreager_ids('Kostenträger', 'kostentraeger_id', 'dd_kostentraeger_id', $js_id);
			$buchung->dropdown_kostentraeger_bez_vw ( "Kostenträger $akt_kostentraeger_bez", 'kostentraeger_id', 'dd_kostentraeger_id', $js_id, $this->kostentraeger_typ, $this->kostentraeger_id );
			
			// die('TEST');
		} else {
			$form->hidden_feld ( "kostentraeger_typ", $this->kostentraeger_typ );
			$form->hidden_feld ( "kostentraeger_id", $this->kostentraeger_id );
		}
		
		$form->hidden_feld ( "option", "geldbuchung_aendern1" );
		$form->send_button ( "submit", "Änderungen speichern" );
		$form->ende_formular ();
	}
	
	/* Funktion zur Ermittlung der Geldkonten und Rückgabe als Array */
	function alle_geldkonten_arr() {
		$result = mysql_query ( "SELECT GELD_KONTEN.KONTO_ID,GELD_KONTEN.BEZEICHNUNG, GELD_KONTEN.BEGUENSTIGTER, GELD_KONTEN.KONTONUMMER, GELD_KONTEN.BLZ FROM GELD_KONTEN_ZUWEISUNG, GELD_KONTEN WHERE GELD_KONTEN.KONTO_ID = GELD_KONTEN_ZUWEISUNG.KONTO_ID && GELD_KONTEN_ZUWEISUNG.AKTUELL = '1' && GELD_KONTEN.AKTUELL = '1' GROUP BY GELD_KONTEN.KONTO_ID  ORDER BY GELD_KONTEN.KONTO_ID ASC" );
		
		$numrows = mysql_numrows ( $result );
		if ($numrows > 0) {
			while ( $row = mysql_fetch_assoc ( $result ) )
				$my_array [] = $row;
			return $my_array;
		} else {
			return FALSE;
		}
	}
	function geld_konto_bezeichung($id) {
		$result = mysql_query ( "SELECT GELD_KONTEN.BEZEICHNUNG FROM GELD_KONTEN WHERE  KONTO_ID='$id' && GELD_KONTEN.AKTUELL = '1' ORDER BY KONTO_DAT DESC LIMIT 0,1" );
		
		$numrows = mysql_numrows ( $result );
		if ($numrows > 0) {
			$row = mysql_fetch_assoc ( $result );
			return $row ['BEZEICHNUNG'];
		} else {
			return FALSE;
		}
	}
	function dropdown_kostenrahmen_nr($label, $name, $typ, $typ_id, $vorwahl_konto, $id = null) {
		if ($id == null) {
			$id = $name;
		}
		$konten_info = new kontenrahmen ();
		$js = "onchange=\"kostenkonto_vorwahl(this.value)\"";
		// $konten_info->dropdown_kontorahmenkonten('Kostenkonto', 'kostenkonto', 'kostenkonto', $typ, $typ_id, $js);
		$konten_info->dropdown_kontorahmenkonten_vorwahl ( $label, $id, $name, $typ, $typ_id, $js, $vorwahl_konto );
	}
	
	/* Kostenträgerliste als dropdown */
	function dropdown_kostentreager_typen($label, $name, $id, $js_action) {
		if (! empty ( $_SESSION ['kos_typ'] ) && ! empty ( $_SESSION ['kos_bez'] )) {
			// print_r($_SESSION);
			$this->dropdown_kostentreager_typen_vw ( $label, $name, $id, $js_action, $_SESSION ['kos_typ'] );
		} else {
			echo "<label for=\"$id\">$label</label><select name=\"$name\" id=\"$id\" size=1 $js_action>\n";
			echo "<option value=\"\">Bitte wählen</option>\n";
			echo "<option value=\"Objekt\">Objekt</option>\n";
			echo "<option value=\"Wirtschaftseinheit\">Wirtschaftseinheit</option>\n";
			echo "<option value=\"Haus\">Haus</option>\n";
			echo "<option value=\"Einheit\">Einheit</option>\n";
			// echo "<option value=\"Rechnung\">Rechnung</option>\n";
			echo "<option value=\"Partner\">Partner</option>\n";
			echo "<option value=\"Mietvertrag\">Mieter</option>\n";
			echo "<option value=\"GELDKONTO\">Geldkonto</option>\n";
			echo "<option value=\"Eigentuemer\">Eigentuemer</option>\n";
			echo "<option value=\"Baustelle_ext\">Baustelle extern</option>\n";
			echo "<option value=\"Benutzer\">Benutzer</option>\n";
			echo "<option value=\"Lager\">Lager</option>\n";
			echo "<option value=\"ALLE\">Alle</option>\n";
			echo "</select>\n";
		}
	}
	function dropdown_kostentreager_typen_vw($label, $name, $id, $js_action, $vorwahl_typ) {
		echo "<label for=\"$id\">$label</label><select name=\"$name\" id=\"$id\" size=1 $js_action>\n";
		$arr [0] ['typ'] = 'Objekt';
		$arr [0] ['bez'] = 'Objekt';
		$arr [1] ['typ'] = 'Haus';
		$arr [1] ['bez'] = 'Haus';
		$arr [2] ['typ'] = 'Einheit';
		$arr [2] ['bez'] = 'Einheit';
		$arr [3] ['typ'] = 'Baustelle_ext';
		$arr [3] ['bez'] = 'Baustelle extern';
		$arr [4] ['typ'] = 'Wirtschaftseinheit';
		$arr [4] ['bez'] = 'Wirtschaftseinheit';
		$arr [5] ['typ'] = 'Partner';
		$arr [5] ['bez'] = 'Partner';
		$arr [6] ['typ'] = 'Mietvertrag';
		$arr [6] ['bez'] = 'Mieter';
		$arr [7] ['typ'] = 'GELDKONTO';
		$arr [7] ['bez'] = 'Geldkonto';
		$arr [8] ['typ'] = 'Eigentuemer';
		$arr [8] ['bez'] = 'Eigentuemer';
		$arr [9] ['typ'] = 'Lager';
		$arr [9] ['bez'] = 'Lager';
		$arr [10] ['typ'] = 'ALLE';
		$arr [10] ['bez'] = 'Alle';
		$arr [11] ['typ'] = 'Benutzer';
		$arr [11] ['bez'] = 'Benutzer';
		
		echo "<option value=\"\">Bitte wählen</option>\n";
		
		for($a = 0; $a < count ( $arr ); $a ++) {
			$typ = $arr [$a] ['typ'];
			$bez = $arr [$a] ['bez'];
			if ($vorwahl_typ == $typ) {
				echo "<option value=\"$typ\" selected>$bez</option>\n";
			} else {
				echo "<option value=\"$typ\">$bez</option>\n";
			}
		}
		
		echo "</select>\n";
	}
	
	/* Kostenträgerliste als dropdown */
	function dropdown_kostentreager_ids($label, $name, $id, $js_action) {
		if ($js_action == '') {
			$js_action = "onchange=\"drop_kos_register('kostentraeger_typ', 'dd_kostentraeger_id');\"";
		}
		if (! isset ( $_SESSION ['kos_typ'] ) && ! isset ( $_SESSION ['kos_id'] )) {
			echo "<label for=\"$id\">$label</label><select name=\"$name\" id=\"$id\" size=1 $js_action>\n";
			echo "<option value=\"\">Bitte wählen</option>\n";
			echo "</select>\n";
		} else {
			if (empty ( $_SESSION ['kos_id'] )) {
				$kos_id = null;
			}
			$this->dropdown_kostentraeger_bez_vw ( $label, $name, $id, $js_action, $_SESSION ['kos_typ'], $kos_id );
		}
	}
	function dropdown_kostentraeger_bez_vw($label, $name, $id, $js_action, $kos_typ, $vorwahl_bez) {
		// echo "$kos_typ $vorwahl_bez";
		// die();
		$typ = $kos_typ;
		
		// if(is_numeric($vorwahl_bez)){
		// $r = new rechnung();
		// $vorwahl_bez = $r->kostentraeger_ermitteln($kos_typ, $vorwahl_bez);
		// }
		
		echo "<label for=\"$id\">$label</label><select name=\"$name\" id=\"$id\" size=1 $js_action>\n";
		echo "<option value=\"\">Bitte wählen</option>\n";
		if ($typ == 'Objekt') {
			$db_abfrage = "SELECT OBJEKT_KURZNAME, OBJEKT_ID FROM OBJEKT WHERE OBJEKT_AKTUELL='1' ORDER BY OBJEKT_KURZNAME ASC";
			$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
			// echo "<meta content=\"text/html; charset=ISO-8859-1\" http-equiv=\"content-type\">";
			while ( list ( $OBJEKT_KURZNAME, $OBJEKT_ID ) = mysql_fetch_row ( $resultat ) ) {
				// echo "$OBJEKT_KURZNAME|";
				
				if (! isset ( $_SESSION ['geldkonto_id'] )) {
					if ($vorwahl_bez == $OBJEKT_ID) {
						echo "<option value=\"$OBJEKT_ID\" selected>$OBJEKT_KURZNAME</option>";
					} else {
						echo "<option value=\"$OBJEKT_ID\">$OBJEKT_KURZNAME</option>";
					}
				} else {
					
					$gk = new gk ();
					if ($gk->check_zuweisung_kos_typ ( $_SESSION ['geldkonto_id'], 'Objekt', $OBJEKT_ID )) {
						if ($vorwahl_bez == $OBJEKT_ID) {
							echo "<option value=\"$OBJEKT_ID\" selected>$OBJEKT_KURZNAME</option>";
						} else {
							echo "<option value=\"$OBJEKT_ID\">$OBJEKT_KURZNAME</option>";
						}
					}
				}
			}
		}
		
		if ($typ == 'Wirtschaftseinheit') {
			$db_abfrage = "SELECT W_NAME FROM WIRT_EINHEITEN WHERE AKTUELL='1' ORDER BY W_NAME ASC";
			$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
			// echo "<meta content=\"text/html; charset=ISO-8859-1\" http-equiv=\"content-type\">";
			while ( list ( $W_NAME ) = mysql_fetch_row ( $resultat ) ) {
				// echo "$W_NAME|";
				if ($vorwahl_bez == $W_NAME) {
					echo "<option value=\"$W_NAME\" selected>$W_NAME</option>";
				} else {
					echo "<option value=\"$W_NAME\">$W_NAME</option>";
				}
			}
		}
		
		if ($typ == 'Haus') {
			$db_abfrage = "SELECT HAUS_ID, HAUS_STRASSE, HAUS_NUMMER, OBJEKT_ID FROM HAUS WHERE HAUS_AKTUELL='1' ORDER BY HAUS_STRASSE,  0+HAUS_NUMMER, OBJEKT_ID ASC";
			$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
			
			// while (list ($HAUS_ID, $HAUS_STRASSE, $HAUS_NUMMER, $OBJEKT_ID) = mysql_fetch_row($resultat))
			while ( $row = mysql_fetch_assoc ( $resultat ) ) {
				// echo "$HAUS_STRASSE $HAUS_NUMMER|";
				$haus_id = $row ['HAUS_ID'];
				print_r ( $row );
				$h = new haus ();
				$h->get_haus_info ( $haus_id );
				
				if ($vorwahl_bez == $haus_id) {
					echo "<option value=\"$haus_id\" selected>$h->haus_strasse $h->haus_nummer - $h->objekt_name</option>";
				} else {
					echo "<option value=\"$haus_id\">$h->haus_strasse $h->haus_nummer - $h->objekt_name</option>";
				}
			}
		}
		
		if ($typ == 'Einheit') {
			$db_abfrage = "SELECT EINHEIT_KURZNAME, EINHEIT_ID FROM EINHEIT WHERE EINHEIT_AKTUELL='1' ORDER BY EINHEIT_KURZNAME ASC";
			$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
			while ( list ( $EINHEIT_KURZNAME, $EINHEIT_ID ) = mysql_fetch_row ( $resultat ) ) {
				// echo "$EINHEIT_KURZNAME|";
				if ($vorwahl_bez == $EINHEIT_ID) {
					echo "<option value=\"$EINHEIT_ID\" selected>$EINHEIT_KURZNAME</option>";
				} else {
					echo "<option value=\"$EINHEIT_ID\">$EINHEIT_KURZNAME</option>";
				}
			}
		}
		
		if ($typ == 'Partner') {
			$db_abfrage = "SELECT PARTNER_NAME, PARTNER_ID FROM PARTNER_LIEFERANT WHERE AKTUELL='1' ORDER BY PARTNER_NAME ASC";
			$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
			while ( list ( $PARTNER_NAME, $PARTNER_ID ) = mysql_fetch_row ( $resultat ) ) {
				$PARTNER_NAME1 = str_replace ( '<br>', ' ', $PARTNER_NAME );
				// echo "$PARTNER_NAME1|";
				
				if (! is_numeric ( $vorwahl_bez )) {
					if ($vorwahl_bez == $PARTNER_NAME1) {
						echo "<option value=\"$PARTNER_ID\" selected>$PARTNER_NAME1</option>";
					} else {
						echo "<option value=\"$PARTNER_ID\">$PARTNER_NAME1</option>";
					}
				} else {
					if ($vorwahl_bez == $PARTNER_ID) {
						echo "<option value=\"$PARTNER_ID\" selected>$PARTNER_NAME1</option>";
					} else {
						echo "<option value=\"$PARTNER_ID\">$PARTNER_NAME1</option>";
					}
				}
			}
		}
		
		/*
		 * if($typ == 'Mietvertrag'){
		 * $db_abfrage = "SELECT MIETVERTRAG_ID, EINHEIT_KURZNAME FROM `MIETVERTRAG` JOIN EINHEIT ON (MIETVERTRAG.EINHEIT_ID=EINHEIT.EINHEIT_ID) WHERE MIETVERTRAG_AKTUELL='1' ORDER BY EINHEIT_KURZNAME ASC";
		 * $resultat = mysql_query($db_abfrage) or
		 * die(mysql_error());
		 * while (list ( $MIETVERTRAG_ID, $EINHEIT_KURZNAME) = mysql_fetch_row($resultat)){
		 * $mv = new mietvertraege;
		 * $mv->get_mietvertrag_infos_aktuell($MIETVERTRAG_ID);
		 *
		 * #echo " $EINHEIT_KURZNAME * $mv->personen_name_string * $MIETVERTRAG_ID|";
		 * if($vorwahl_bez == "$EINHEIT_KURZNAME * $mv->personen_name_string * $MIETVERTRAG_ID"){
		 * echo "<option value=\"$EINHEIT_KURZNAME * $mv->personen_name_string * $MIETVERTRAG_ID\" selected>$EINHEIT_KURZNAME * $mv->personen_name_string * $MIETVERTRAG_ID</option>";
		 * }else{
		 * echo "<option value=\"$EINHEIT_KURZNAME * $mv->personen_name_string * $MIETVERTRAG_ID\">$EINHEIT_KURZNAME * $mv->personen_name_string * $MIETVERTRAG_ID</option>";
		 * }
		 *
		 * }
		 * }
		 */
		
		if ($typ == 'Mietvertrag') {
			
			$gk_arr_objekt = $this->get_objekt_arr_gk ( $_SESSION ['geldkonto_id'] );
			if (is_array ( $gk_arr_objekt )) {
				
				$db_abfrage = "SELECT  HAUS.OBJEKT_ID, OBJEKT_KURZNAME, MIETVERTRAG.EINHEIT_ID, EINHEIT_KURZNAME, MIETVERTRAG_ID FROM `EINHEIT` RIGHT JOIN (HAUS, OBJEKT, MIETVERTRAG) ON ( EINHEIT.HAUS_ID = HAUS.HAUS_ID && HAUS.OBJEKT_ID = OBJEKT.OBJEKT_ID && EINHEIT.EINHEIT_ID=MIETVERTRAG.EINHEIT_ID)
WHERE  HAUS_AKTUELL='1' && EINHEIT_AKTUELL='1' && OBJEKT_AKTUELL='1' && MIETVERTRAG_AKTUELL='1' ";
				
				$anz_gk = count ( $gk_arr_objekt );
				for($go = 0; $go < $anz_gk; $go ++) {
					$oo_id = $gk_arr_objekt [$go];
					$db_abfrage .= "&& HAUS.OBJEKT_ID=$oo_id ";
				}
				
				$db_abfrage .= "GROUP BY MIETVERTRAG_ID ORDER BY LPAD(EINHEIT_KURZNAME, LENGTH(EINHEIT_KURZNAME), '1') ASC";
			} else {
				
				$db_abfrage = "SELECT  HAUS.OBJEKT_ID, OBJEKT_KURZNAME, MIETVERTRAG.EINHEIT_ID, EINHEIT_KURZNAME, MIETVERTRAG_ID FROM `EINHEIT` RIGHT JOIN (HAUS, OBJEKT, MIETVERTRAG) ON ( EINHEIT.HAUS_ID = HAUS.HAUS_ID && HAUS.OBJEKT_ID = OBJEKT.OBJEKT_ID && EINHEIT.EINHEIT_ID=MIETVERTRAG.EINHEIT_ID)
WHERE  HAUS_AKTUELL='1' && EINHEIT_AKTUELL='1' && OBJEKT_AKTUELL='1' && MIETVERTRAG_AKTUELL='1' GROUP BY MIETVERTRAG_ID ORDER BY LPAD(EINHEIT_KURZNAME, LENGTH(EINHEIT_KURZNAME), '1') ASC";
			}
			
			// die($db_abfrage);
			
			// $db_abfrage ="SELECT OBJEKT_KURZNAME, MIETVERTRAG.EINHEIT_ID, EINHEIT_KURZNAME, MIETVERTRAG_ID FROM `EINHEIT` RIGHT JOIN (HAUS, OBJEKT, MIETVERTRAG) ON ( EINHEIT.HAUS_ID = HAUS.HAUS_ID && HAUS.OBJEKT_ID = OBJEKT.OBJEKT_ID && EINHEIT.EINHEIT_ID=MIETVERTRAG.EINHEIT_ID)
			// WHERE HAUS_AKTUELL='1' && EINHEIT_AKTUELL='1' && OBJEKT_AKTUELL='1' && MIETVERTRAG_AKTUELL='1' GROUP BY MIETVERTRAG_ID ORDER BY LPAD(EINHEIT_KURZNAME, LENGTH(EINHEIT_KURZNAME), '1') ASC";
			$result = mysql_query ( $db_abfrage ) or die ( mysql_error () );
			while ( $row = mysql_fetch_assoc ( $result ) ) {
				$mv_id = $row ['MIETVERTRAG_ID'];
				$mv = new mietvertraege ();
				$mv->get_mietvertrag_infos_aktuell ( $mv_id );
				if (! isset ( $_SESSION ['geldkonto_id'] )) {
					if ($vorwahl_bez == "$mv_id") {
						echo "<option value=\"$mv_id\" selected>$mv->einheit_kurzname***$mv->personen_name_string</option>\n";
					} else {
						echo "<option value=\"$mv_id\">$mv->einheit_kurzname***$mv->personen_name_string</option>\n";
					}
				} else {
					$gk = new gk ();
					if ($gk->check_zuweisung_kos_typ ( $_SESSION ['geldkonto_id'], 'Objekt', $mv->objekt_id )) {
						if ($vorwahl_bez == "$mv_id") {
							echo "<option value=\"$mv_id\" selected>$mv->einheit_kurzname***$mv->personen_name_string</option>\n";
						} else {
							echo "<option value=\"$mv_id\">$mv->einheit_kurzname***$mv->personen_name_string</option>\n";
						}
					}
				}
				// echo "$mv->einheit_kurzname*$mv_id*$mv->personen_name_string|";
			}
		}
		
		if ($typ == 'GELDKONTO') {
			$db_abfrage = "SELECT KONTO_ID, BEZEICHNUNG  FROM `GELD_KONTEN`  WHERE AKTUELL='1' ORDER BY BEZEICHNUNG ASC";
			$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
			while ( list ( $KONTO_ID, $BEZEICHNUNG ) = mysql_fetch_row ( $resultat ) ) {
				// echo "$BEZEICHNUNG|";
				if ($vorwahl_bez == $BEZEICHNUNG) {
					echo "<option value=\"$BEZEICHNUNG\" selected>$BEZEICHNUNG</option>";
				} else {
					echo "<option value=\"$BEZEICHNUNG\">$BEZEICHNUNG</option>";
				}
			}
		}
		
		if ($typ == 'Lager') {
			$db_abfrage = "SELECT LAGER_ID, LAGER_NAME  FROM `LAGER`  WHERE AKTUELL='1' ORDER BY LAGER_NAME ASC";
			$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
			while ( list ( $LAGER_ID, $LAGER_NAME ) = mysql_fetch_row ( $resultat ) ) {
				// echo "$LAGER_NAME|";
				if ($vorwahl_bez == $LAGER_NAME) {
					echo "<option value=\"$LAGER_ID\" selected>$LAGER_NAME</option>";
				} else {
					echo "<option value=\"$LAGER_ID\">$LAGER_NAME</option>";
				}
			}
		}
		
		if ($typ == 'Baustelle_ext') {
			$db_abfrage = "SELECT ID, BEZ  FROM `BAUSTELLEN_EXT`  WHERE AKTUELL='1' ORDER BY BEZ ASC";
			$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
			while ( list ( $ID, $BEZ ) = mysql_fetch_row ( $resultat ) ) {
				// echo "$BEZ|";
				if ($vorwahl_bez == $BEZ) {
					echo "<option value=\"$BEZ\" selected>$BEZ</option>";
				} else {
					echo "<option value=\"$BEZ\">$BEZ</option>";
				}
			}
		}
		
		/*
		 * if($typ == 'Eigentuemer'){
		 * ###ALT OK $db_abfrage = "SELECT ID, EINHEIT_ID FROM `WEG_MITEIGENTUEMER` WHERE AKTUELL='1'";
		 * $db_abfrage = "SELECT ID, WEG_MITEIGENTUEMER.EINHEIT_ID, EINHEIT_KURZNAME FROM `WEG_MITEIGENTUEMER` , EINHEIT WHERE EINHEIT_AKTUELL = '1' && AKTUELL = '1' && EINHEIT.EINHEIT_ID = WEG_MITEIGENTUEMER.EINHEIT_ID ORDER BY EINHEIT_KURZNAME ASC";
		 *
		 *
		 *
		 *
		 * $resultat = mysql_query($db_abfrage) or
		 * die(mysql_error());
		 * while (list ( $ID, $EINHEIT_ID) = mysql_fetch_row($resultat)){
		 * $weg = new weg;
		 * $eig_bez[] = $weg->get_eigentumer_id_infos2($ID).'*'. $ID;
		 * }
		 * asort($eig_bez);
		 * $anz = count($eig_bez);
		 * if($anz>0){
		 * for($a=0;$a<$anz;$a++){
		 * $eig_bez1 = $eig_bez[$a];
		 * # echo "$eig_bez1|";
		 *
		 * if(!is_numeric($vorwahl_bez)){
		 * if($vorwahl_bez == $eig_bez1){
		 * echo "<option value=\"$ID\" selected>$eig_bez1</option>";
		 * }else{
		 * echo "<option value=\"$ID\">$eig_bez1</option>";
		 * }
		 * }else{
		 *
		 * $eee_id_arr = explode('*', $eig_bez1);
		 * $eee_id = $eee_id_arr[1];
		 * #echo "$eee_id $vorwahl_bez<br>";
		 * if($vorwahl_bez == $eee_id){
		 * echo "<option value=\"$ID\" selected>$eig_bez1</option>";
		 * }else{
		 * echo "<option value=\"$ID\">$eig_bez1</option>";
		 * }
		 * }
		 * }
		 * }
		 *
		 * }
		 */
		
		if ($typ == 'Eigentuemer') {
			echo "VORWAHL $vorwahl_bez";
			
			$gk_arr_objekt = $this->get_objekt_arr_gk ( $_SESSION ['geldkonto_id'] );
			if (is_array ( $gk_arr_objekt )) {
				
				$db_abfrage = "SELECT ID, WEG_MITEIGENTUEMER.EINHEIT_ID, EINHEIT_KURZNAME, EINHEIT.HAUS_ID, HAUS.OBJEKT_ID FROM `WEG_MITEIGENTUEMER` , EINHEIT, HAUS WHERE EINHEIT_AKTUELL = '1' && HAUS_AKTUELL = '1' && AKTUELL = '1' && EINHEIT.HAUS_ID = HAUS.HAUS_ID && EINHEIT.EINHEIT_ID = WEG_MITEIGENTUEMER.EINHEIT_ID ";
				
				$anz_gk = count ( $gk_arr_objekt );
				for($go = 0; $go < $anz_gk; $go ++) {
					$oo_id = $gk_arr_objekt [$go];
					$db_abfrage .= "&& HAUS.OBJEKT_ID=$oo_id ";
				}
				
				$db_abfrage .= "GROUP BY ID ORDER BY  EINHEIT_KURZNAME ASC ";
			} else {
				
				$db_abfrage = "SELECT ID, WEG_MITEIGENTUEMER.EINHEIT_ID, EINHEIT_KURZNAME, EINHEIT.HAUS_ID, HAUS.OBJEKT_ID FROM `WEG_MITEIGENTUEMER` , EINHEIT, HAUS WHERE EINHEIT_AKTUELL = '1' && HAUS_AKTUELL = '1' && AKTUELL = '1' && EINHEIT.HAUS_ID = HAUS.HAUS_ID && EINHEIT.EINHEIT_ID = WEG_MITEIGENTUEMER.EINHEIT_ID GROUP BY ID ORDER BY  EINHEIT_KURZNAME ASC";
			}
			
			// echo $db_abfrage;
			
			// $db_abfrage = "SELECT ID, EINHEIT_ID FROM `WEG_MITEIGENTUEMER` WHERE AKTUELL='1'";
			/* Mit Haus_id und OBJEKT_ID */
			// SELECT ID, WEG_MITEIGENTUEMER.EINHEIT_ID, EINHEIT_KURZNAME, EINHEIT.HAUS_ID, HAUS.OBJEKT_ID FROM `WEG_MITEIGENTUEMER` , EINHEIT, HAUS WHERE EINHEIT_AKTUELL = '1' && HAUS_AKTUELL = '1' && AKTUELL = '1' && EINHEIT.HAUS_ID = HAUS.HAUS_ID && EINHEIT.EINHEIT_ID = WEG_MITEIGENTUEMER.EINHEIT_ID GROUP BY ID ORDER BY EINHEIT_KURZNAME ASC
			/* OK ALT */
			// $db_abfrage = "SELECT ID, WEG_MITEIGENTUEMER.EINHEIT_ID, EINHEIT_KURZNAME FROM `WEG_MITEIGENTUEMER` , EINHEIT WHERE EINHEIT_AKTUELL = '1' && AKTUELL = '1' && EINHEIT.EINHEIT_ID = WEG_MITEIGENTUEMER.EINHEIT_ID GROUP BY ID ORDER BY EINHEIT_KURZNAME ASC";
			$result = mysql_query ( $db_abfrage ) or die ( mysql_error () );
			while ( $row = mysql_fetch_assoc ( $result ) ) {
				$weg = new weg ();
				// $eig_bez[] = $weg->get_eigentumer_id_infos2($ID).'*'. $ID;
				$ID = $row ['ID'];
				$einheit_id = $row ['EINHEIT_ID'];
				$weg->get_eigentuemer_namen ( $row ['ID'] ); // $weg->eigentuemer_name_str
				                                         // $e = new einheit();
				                                         // $e->get_einheit_info($EINHEIT_ID);
				$einheit_kn = $row ['EINHEIT_KURZNAME'];
				
				if (! isset ( $_SESSION ['geldkonto_id'] )) {
					// echo "$einheit_kn*$ID*$weg->eigentuemer_name_str|";
					if ($vorwahl_bez == $ID) {
						echo "<option value=\"$ID\" selected>$einheit_kn***$weg->eigentuemer_name_str</option>";
					} else {
						echo "<option value=\"$ID\" >$einheit_kn***$weg->eigentuemer_name_str</option>";
					}
					
					// echo "$mv->einheit_kurzname*$mv_id*$mv->personen_name_string|";
				} else {
					$eee = new einheit ();
					$eee->get_einheit_info ( $einheit_id );
					$gk = new gk ();
					if ($gk->check_zuweisung_kos_typ ( $_SESSION ['geldkonto_id'], 'Objekt', $eee->objekt_id )) {
						// echo "$einheit_kn*$weg->eigentuemer_name_str iiii*".$row['ID']."|";
						// echo "$einheit_kn*$ID*$weg->eigentuemer_name_str|";
						if ($vorwahl_bez == $ID) {
							echo "<option value=\"$ID\" selected>$einheit_kn***$weg->eigentuemer_name_str</option>";
						} else {
							echo "<option value=\"$ID\" >$einheit_kn***$weg->eigentuemer_name_str</option>";
						}
					}
				}
			}
		}
		
		echo "</select>\n";
	}
	function get_objekt_arr_gk($gk_id) {
		$db_abfrage = "SELECT KOSTENTRAEGER_ID  FROM  GELD_KONTEN_ZUWEISUNG WHERE AKTUELL = '1' && KONTO_ID='$gk_id' && KOSTENTRAEGER_TYP='Objekt'";
		$result = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			while ( $row = mysql_fetch_assoc ( $result ) ) {
				$arr [] = $row ['KOSTENTRAEGER_ID'];
			}
			return $arr;
		} else {
			return false;
		}
	}
	function kostentraeger_id_ermitteln($kostentraeger_typ, $kostentraeger_bez) {
		if (is_numeric ( $kostentraeger_bez )) {
			return $kostentraeger_bez;
		}
		if ($kostentraeger_typ == 'Objekt') {
			$obj = new objekt ();
			$obj->get_objekt_id ( $kostentraeger_bez );
			return $obj->objekt_id;
		}
		
		if ($kostentraeger_typ == 'Wirtschaftseinheit') {
			$w = new wirt_e ();
			$wirt_id = $w->get_id_from_wirte ( $kostentraeger_bez );
			return $wirt_id;
		}
		
		if ($kostentraeger_typ == 'Haus') {
			$haus = new haus ();
			
			$haus->get_haus_id ( $kostentraeger_bez );
			return $haus->haus_id;
		}
		if ($kostentraeger_typ == 'Einheit') {
			$einheit = new einheit ();
			$einheit->get_einheit_id ( $kostentraeger_bez );
			return $einheit->einheit_id;
		}
		if ($kostentraeger_typ == 'Partner') {
			$p = new partner ();
			$p->getpartner_id_name ( $kostentraeger_bez );
			return $p->partner_id;
		}
		if ($kostentraeger_typ == 'Mietvertrag') {
			$mv_arr = explode ( "*", $kostentraeger_bez );
			$mv_id = $mv_arr [2];
			// echo '<pre>';
			// print_r($mv_arr);
			return $mv_id;
		}
		
		if ($kostentraeger_typ == 'Eigentuemer') {
			$eig_arr = explode ( "*", $kostentraeger_bez );
			$eig_id = $eig_arr [1];
			// echo '<pre>';
			// print_r($mv_arr);
			return $eig_id;
		}
		
		if ($kostentraeger_typ == 'Baustelle_ext') {
			$s = new statistik ();
			return $s->get_baustelle_ext_id ( $kostentraeger_bez );
		}
		
		if ($kostentraeger_typ == 'GELDKONTO') {
			$gk = new gk ();
			return $gk->get_geldkonto_id ( $kostentraeger_bez );
		}
		
		if ($kostentraeger_typ == 'ALLE') {
			return '0';
		}
		
		if ($kostentraeger_typ == 'Benutzer') {
			$be = new benutzer ();
			return $be->get_benutzer_id ( $kostentraeger_bez );
		}
		
		if ($kostentraeger_typ == 'Lager') {
			$la = new lager ();
			return $la->get_lager_id ( $kostentraeger_bez );
		}
	}
	
	/* Manuelle Buchung */
	function geldbuchung_speichern($datum, $kto_auszugsnr, $rechnungsnr, $betrag, $vzweck, $geldkonto_id, $kostentraeger_typ, $kostentraeger_bez, $kostenkonto, $mwst = '0.00') {
		$kostentraeger_id = $this->kostentraeger_id_ermitteln ( $kostentraeger_typ, $kostentraeger_bez );
		if (! is_numeric ( $kostentraeger_id ) or $kostentraeger_id == '0' or $kostentraeger_id == null or ! $kostentraeger_id) {
			fehlermeldung_ausgeben ( "Es wurde nicht gebucht, Kostenträger unbekannt! Zeile. 747 class_buchen" );
			die ();
		}
		/* alt */
		$buchung_id = $this->get_last_geldbuchung_id ();
		/* neu */
		$datum_arr = explode ( '-', $datum );
		$jahr = $datum_arr ['0'];
		$g_buchungsnummer = $this->get_last_buchungsnummer_konto ( $geldkonto_id, $jahr );
		$g_buchungsnummer = $g_buchungsnummer + 1;
		
		$buchung_id = $buchung_id + 1;
		/* alt */
		// $db_abfrage = "INSERT INTO GELD_KONTO_BUCHUNGEN VALUES (NULL, '$buchung_id','$kto_auszugsnr', '$rechnungsnr', '$betrag', '$vzweck', '$geldkonto_id', '$kostenkonto', '$datum', '$kostentraeger_typ', '$kostentraeger_id', '1')";
		
		/* neu */
		$db_abfrage = "INSERT INTO GELD_KONTO_BUCHUNGEN VALUES (NULL, '$buchung_id', '$g_buchungsnummer', '$kto_auszugsnr', '$rechnungsnr', '$betrag', '$mwst', '$vzweck', '$geldkonto_id', '$kostenkonto', '$datum', '$kostentraeger_typ', '$kostentraeger_id', '1')";
		$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		echo "<h1>Neue Buchungsnummer erteilt: $g_buchungsnummer</h1>";
		
		/* Protokollieren */
		$last_dat = mysql_insert_id ();
		protokollieren ( 'GELD_KONTO_BUCHUNGEN', $last_dat, '0' );
		echo "<h3>Betrag von $betrag € wurde gebucht.</h3>";
		// weiterleiten_in_sec('?daten=buchen&option=buchungs_journal', 1);
		weiterleiten_in_sec ( '?daten=buchen&option=zahlbetrag_buchen', 1 );
	}
	
	/* Manuelle Buchung mit der Rechnung Brutto, Netto, Skonto */
	function geldbuchung_speichern_rechnung($datum, $kto_auszugsnr, $rechnungsnr, $betrag, $vzweck, $geldkonto_id, $kostentraeger_typ, $kostentraeger_id, $kostenkonto, $mwst = '0.00') {
		
		/* alt */
		$buchung_id = $this->get_last_geldbuchung_id ();
		/* neu */
		$datum_arr = explode ( '-', $datum );
		$jahr = $datum_arr ['0'];
		$g_buchungsnummer = $this->get_last_buchungsnummer_konto ( $geldkonto_id, $jahr );
		$g_buchungsnummer = $g_buchungsnummer + 1;
		
		$buchung_id = $this->get_last_geldbuchung_id ();
		$buchung_id = $buchung_id + 1;
		/* alt */
		// $db_abfrage = "INSERT INTO GELD_KONTO_BUCHUNGEN VALUES (NULL, '$buchung_id','$kto_auszugsnr', '$rechnungsnr', '$betrag', '$vzweck', '$geldkonto_id', '$kostenkonto', '$datum', '$kostentraeger_typ', '$kostentraeger_id', '1')";
		/* neu */
		$db_abfrage = "INSERT INTO GELD_KONTO_BUCHUNGEN VALUES (NULL, '$buchung_id', '$g_buchungsnummer', '$kto_auszugsnr', '$rechnungsnr', '$betrag','$mwst', '$vzweck', '$geldkonto_id', '$kostenkonto', '$datum', '$kostentraeger_typ', '$kostentraeger_id', '1')";
		$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		/* Protokollieren */
		$last_dat = mysql_insert_id ();
		protokollieren ( 'GELD_KONTO_BUCHUNGEN', $last_dat, '0' );
		// echo "<h3>Betrag von $betrag € wurde gebucht.</h3>";
		// weiterleiten_in_sec('?daten=rechnungen&option=ausgangsbuch', 1);
	}
	function form_kostenkonto_pdf() {
		if (empty ( $_SESSION ['geldkonto_id'] )) {
			die ( 'Erstgeldkonto wählen' );
		}
		if (! isset ( $_REQUEST [submit_kostenkonto] )) {
			$kr = new kontenrahmen ();
			$kontenrahmen_id = $kr->get_kontenrahmen ( 'Geldkonto', $_SESSION ['geldkonto_id'] );
			$f = new formular ();
			$f->erstelle_formular ( 'Kostenkonto als PDF', '' );
			$kr->dropdown_konten_vom_rahmen ( 'Kostenkonto wählen', 'kostenkonto', 'kk', '', $kontenrahmen_id );
			$f->text_feld ( "Anfangsdatum:", "anfangsdatum", "", "10", 'anfangsdatum', '' );
			$f->text_feld ( "Enddatum:", "enddatum", "", "10", 'enddatum', '' );
			$f->send_button ( "submit_kostenkonto", "Als PDF anzeigen" );
			$f->ende_formular ();
		} else {
			// print_req();
			$von = date_german2mysql ( $_REQUEST [anfangsdatum] );
			$bis = date_german2mysql ( $_REQUEST [enddatum] );
			$kostenkonto = $_REQUEST [kostenkonto];
			$abfrage = "SELECT * FROM GELD_KONTO_BUCHUNGEN WHERE AKTUELL='1' && GELDKONTO_ID='$_SESSION[geldkonto_id]' && KONTENRAHMEN_KONTO='$kostenkonto' && DATUM BETWEEN '$von' AND '$bis' ORDER BY DATUM ASC";
			// echo $abfrage;
			// die();
			$this->finde_buchungen_pdf ( $abfrage );
		}
	}
	
	/* Ermitteln der letzten geldbuchungs_id ALT, buchungsnummer nacheinander */
	function get_last_geldbuchung_id() {
		$result = mysql_query ( "SELECT GELD_KONTO_BUCHUNGEN_ID FROM GELD_KONTO_BUCHUNGEN ORDER BY GELD_KONTO_BUCHUNGEN_ID DESC LIMIT 0,1" );
		$row = mysql_fetch_assoc ( $result );
		return $row ['GELD_KONTO_BUCHUNGEN_ID'];
	}
	
	/* Ermitteln der letzten geldbuchungs_id NEU für jeder Geldkonto separat, jährlich ab 1 angefangen */
	function get_last_kontoauszug($geldkonto_id, $jahr) {
		$result = mysql_query ( "SELECT KONTO_AUSZUGSNUMMER FROM GELD_KONTO_BUCHUNGEN WHERE GELDKONTO_ID='$geldkonto_id' && DATE_FORMAT(DATUM, '%Y' ) = '$jahr'  && `AKTUELL` = '1' ORDER BY KONTO_AUSZUGSNUMMER DESC LIMIT 0,1" );
		$row = mysql_fetch_assoc ( $result );
		return $row ['KONTO_AUSZUGSNUMMER'];
	}
	function get_last_buchungsnummer_konto($geldkonto_id, $jahr) {
		$result = mysql_query ( "SELECT G_BUCHUNGSNUMMER FROM GELD_KONTO_BUCHUNGEN WHERE GELDKONTO_ID='$geldkonto_id' && DATE_FORMAT(DATUM, '%Y' ) = '$jahr'  && `AKTUELL` = '1' ORDER BY G_BUCHUNGSNUMMER DESC LIMIT 0,1" );
		$row = mysql_fetch_assoc ( $result );
		return $row ['G_BUCHUNGSNUMMER'];
	}
	function kontoauszug_form() {
		echo "<hr><br>";
		$geldkonto_bezeichnung = $this->geld_konto_bezeichung ( $_SESSION ['geldkonto_id'] );
		$form = new formular ();
		$heute_d = date ( "d.m.Y" );
		$heute = date ( "Y-m-d" );
		$gestern = date_mysql2german ( tage_minus ( $heute, 1 ) );
		$form->erstelle_formular ( "Kontrolldaten eingeben / verändern", NULL );
		$form->text_feld_inaktiv ( "Geldkonto:", "geldkonto", $geldkonto_bezeichnung, "30", 'geldkonto', '' );
		$form->text_feld ( "Datum:", "datum", $gestern, "10", 'datum', '' );
		$jahr = date ( "Y" );
		$last_kto = $this->get_last_kontoauszug ( $_SESSION ['geldkonto_id'], $jahr ) + 1;
		$form->text_feld ( "Kontoauszugsnummer:", "kontoauszugsnummer", "$last_kto", "10", 'kontoauszugsnummer', '' );
		$form->text_feld ( "Kontostand:", "kontostand", "", "10", 'kontostand', '' );
		$form->send_button ( "submit", "Speichern" );
		$form->hidden_feld ( "option", "kontoauszug_gesendet" );
	}
	function buchungsjournal_auszug($geldkonto_id, $kto_auszug) {
		$temp_datum_arr = explode ( '.', $_SESSION ['temp_datum'] );
		$temp_jahr = $temp_datum_arr [2];
		$result = mysql_query ( "SELECT * FROM GELD_KONTO_BUCHUNGEN WHERE DATE_FORMAT( DATUM, '%Y' ) = $temp_jahr   && GELDKONTO_ID='$geldkonto_id' && KONTO_AUSZUGSNUMMER='$kto_auszug' && AKTUELL='1' ORDER BY GELD_KONTO_BUCHUNGEN_ID DESC" );
		$numrows = mysql_numrows ( $result );
		$link_reset_auszug = "<a href=\"?daten=buchen&option=reset_kontoauszug\">Ohne Kontoauszug</a>";
		echo $link_reset_auszug;
		if ($numrows > 0) {
			while ( $row = mysql_fetch_assoc ( $result ) )
				$my_array [] = $row;
			echo "<table classe=\"sortable\">";
			
			// echo "<tr><td colspan=4>$link_reset_auszug</td></tr>";
			// echo "<tr class=\"feldernamen\"><td>Auzugsnr</td><td>Betrag</td><td>Konto</td><td>Buchungsnr</td><td>Verwendung</td><td>Buchungstext</td></tr>";
			
			echo "<tr><th>AUSZUG</th><th>DATUM</th><th>BETRAG</th><th>MWST</th><th>KONTO</th><th>BUCHUNGSNR</th><th>Verwendung</th><th>BUCHUNGSTEXT</th></tr>";
			$g_betrag = 0;
			for($a = 0; $a < $numrows; $a ++) {
				
				$b_id = $my_array [$a] ['GELD_KONTO_BUCHUNGEN_ID'];
				$b_dat = $my_array [$a] ['GELD_KONTO_BUCHUNGEN_DAT'];
				$g_buchungsnummer = $my_array [$a] ['G_BUCHUNGSNUMMER'];
				$kostenkonto = $my_array [$a] ['KONTENRAHMEN_KONTO'];
				$link_buchungsbeleg = "<a href=\"?daten=buchen&option=geldbuchung_aendern&geldbuchung_dat=$b_dat\">$g_buchungsnummer ändern</a>";
				$betrag = nummer_punkt2komma ( $my_array [$a] ['BETRAG'] );
				$mwst = nummer_punkt2komma ( $my_array [$a] ['MWST_ANTEIL'] );
				$g_betrag += $my_array [$a] ['BETRAG'];
				$vzweck = $my_array [$a] ['VERWENDUNGSZWECK'];
				$auszug = $my_array [$a] ['KONTO_AUSZUGSNUMMER'];
				$kostentraeger_typ = $my_array [$a] ['KOSTENTRAEGER_TYP'];
				$kostentraeger_id = $my_array [$a] ['KOSTENTRAEGER_ID'];
				if ($kostentraeger_typ == 'Mietvertrag') {
					$mv = new mietvertraege ();
					$mv->get_mietvertrag_infos_aktuell ( $kostentraeger_id );
					$kostentraeger_bez = $mv->personen_name_string_u;
				} else {
					$r = new rechnung ();
					$kostentraeger_bez = $r->kostentraeger_ermitteln ( $kostentraeger_typ, $kostentraeger_id );
				}
				$datum = date_mysql2german ( $my_array [$a] ['DATUM'] );
				echo "<tr><td>$auszug</td><td>$datum</td><td>$betrag</td><td>$mwst</td><td>$kostenkonto </td><td>$link_buchungsbeleg</td><td>$kostentraeger_bez</td><td> $vzweck</td></tr>";
			}
			echo "<tfoot><tr><td></td><td></td><td><b>$g_betrag €</b></td><td></td><td></td></tr></tfoot>";
			echo "</table>";
		}
	}
	function buchungsjournal_startzeit($geldkonto_id, $datum) {
		$datum_arr = explode ( '-', $datum );
		$jahr = $datum_arr [0];
		$monat = $datum_arr [1];
		if (! isset ( $_REQUEST ['sort'] ) && empty ( $_REQUEST ['sort'] )) {
			$result = mysql_query ( "SELECT DATUM, GELD_KONTO_BUCHUNGEN_DAT, MWST_ANTEIL, GELD_KONTO_BUCHUNGEN_ID, G_BUCHUNGSNUMMER, BETRAG, VERWENDUNGSZWECK, KONTO_AUSZUGSNUMMER, ERFASS_NR, KONTENRAHMEN_KONTO, KOSTENTRAEGER_TYP, KOSTENTRAEGER_ID FROM GELD_KONTO_BUCHUNGEN WHERE GELDKONTO_ID='$geldkonto_id' && DATE_FORMAT(DATUM, '%Y-%m') = '$jahr-$monat' && AKTUELL='1' ORDER BY G_BUCHUNGSNUMMER DESC" );
		} else {
			$sort = $_REQUEST ['sort'];
			$result = mysql_query ( "SELECT DATUM, GELD_KONTO_BUCHUNGEN_DAT, MWST_ANTEIL, GELD_KONTO_BUCHUNGEN_ID, G_BUCHUNGSNUMMER, BETRAG, VERWENDUNGSZWECK, KONTO_AUSZUGSNUMMER, ERFASS_NR, KONTENRAHMEN_KONTO,KOSTENTRAEGER_TYP, KOSTENTRAEGER_ID FROM GELD_KONTO_BUCHUNGEN WHERE GELDKONTO_ID='$geldkonto_id' && DATE_FORMAT(DATUM, '%Y-%m') = '$jahr-$monat' && AKTUELL='1' ORDER BY $sort ASC" );
		}
		
		$datum_arr = explode ( "-", $datum );
		$jahr = $datum_arr [0];
		$monat = $datum_arr [1];
		$numrows = mysql_numrows ( $result );
		if ($numrows > 0) {
			while ( $row = mysql_fetch_assoc ( $result ) )
				$my_array [] = $row;
			
			echo "<table class=\"sortable\">";
			
			echo "<tr><th>DATUM</th><th>ERF/AUSZ</th><th>AUSZUG</th><th>Konto</th><th>Betrag</th><th>MWST</th><th>Verwendung</th><th>BUCHUNGSNR</th><th>Buchungstext</th></tr>";
			$sort_datum_link = "<a href=\"?daten=buchen&option=buchungs_journal&sort=DATUM\">Datum</a>";
			$sort_bnr_link = "<a href=\"?daten=buchen&option=buchungs_journal&sort=G_BUCHUNGSNUMMER\">Buchungsnr</a>";
			$sort_betrag_link = "<a href=\"?daten=buchen&option=buchungs_journal&sort=BETRAG\">Betrag</a>";
			$sort_rechnung_link = "<a href=\"?daten=buchen&option=buchungs_journal&sort=ERFASS_NR\">Erfassungsnr.</a>";
			$sort_auszug_link = "<a href=\"?daten=buchen&option=buchungs_journal&sort=KONTO_AUSZUGSNUMMER\">Kontoauszug</a>";
			$sort_kostenkonto_link = "<a href=\"?daten=buchen&option=buchungs_journal&sort=KONTENRAHMEN_KONTO\">Konto</a>";
			
			// echo "<tr class=\"feldernamen\"><td>$sort_datum_link</td><td>$sort_rechnung_link</td><td>$sort_auszug_link</td><td>$sort_kostenkonto_link</td><td>$sort_betrag_link</td><td>Zuordnung</td></td><td>$sort_bnr_link</td><td>Buchungstext</td></tr>";
			for($a = 0; $a < $numrows; $a ++) {
				$datum = date_mysql2german ( $my_array [$a] ['DATUM'] );
				$b_id = $my_array [$a] ['GELD_KONTO_BUCHUNGEN_ID'];
				$b_dat = $my_array [$a] ['GELD_KONTO_BUCHUNGEN_DAT'];
				$g_buchungsnummer = $my_array [$a] ['G_BUCHUNGSNUMMER'];
				$link_buchungsbeleg = "<a href=\"?daten=buchen&option=geldbuchung_aendern&geldbuchung_dat=$b_dat\">$g_buchungsnummer</a>";
				$betrag = nummer_punkt2komma ( $my_array [$a] ['BETRAG'] );
				$mwst_anteil = nummer_punkt2komma ( $my_array [$a] ['MWST_ANTEIL'] );
				$vzweck = $my_array [$a] ['VERWENDUNGSZWECK'];
				$auszug = $my_array [$a] ['KONTO_AUSZUGSNUMMER'];
				$erfass_nr = $my_array [$a] ['ERFASS_NR'];
				$kostenkonto = $my_array [$a] ['KONTENRAHMEN_KONTO'];
				$kostentraeger_typ = $my_array [$a] ['KOSTENTRAEGER_TYP'];
				$kostentraeger_id = $my_array [$a] ['KOSTENTRAEGER_ID'];
				// $k = new kasse;
				$r = new rechnung ();
				// $kostentraeger_bez = $k->kostentraeger_beschreibung($kostentraeger_typ, $kostentraeger_id);
				if ($kostentraeger_typ == 'Mietvertrag') {
					$mv = new mietvertraege ();
					$mv->get_mietvertrag_infos_aktuell ( $kostentraeger_id );
					$kostentraeger_bez = $mv->personen_name_string_u;
				} else {
					$kostentraeger_bez = $r->kostentraeger_ermitteln ( $kostentraeger_typ, $kostentraeger_id );
				}
				echo "<tr><td>$datum</td><td>$erfass_nr</td><td>$auszug</td><td>$kostenkonto</td><td>$betrag</td><td>$mwst_anteil</td><td>$kostentraeger_bez</td><td><b>$link_buchungsbeleg</b></td><td>$vzweck</td></tr>";
			}
			echo "</table>";
		}
	}
	function buchungsjournal_startzeit_druck($geldkonto_id, $datum) {
		$dat_arr = explode ( "-", $datum );
		$ja = $dat_arr [0];
		$mo = $dat_arr [1];
		$ta = $dat_arr [2];
		
		if (! isset ( $_REQUEST ['sort'] ) && empty ( $_REQUEST ['sort'] )) {
			$result = mysql_query ( "SELECT DATUM, GELD_KONTO_BUCHUNGEN_DAT, GELD_KONTO_BUCHUNGEN_ID, G_BUCHUNGSNUMMER, BETRAG, VERWENDUNGSZWECK, KONTO_AUSZUGSNUMMER, ERFASS_NR, KONTENRAHMEN_KONTO, KOSTENTRAEGER_TYP, KOSTENTRAEGER_ID FROM GELD_KONTO_BUCHUNGEN WHERE GELDKONTO_ID='$geldkonto_id' && DATE_FORMAT(DATUM, '%Y-%m') = '$ja-$mo' && AKTUELL='1' ORDER BY G_BUCHUNGSNUMMER ASC" );
		} else {
			$sort = $_REQUEST ['sort'];
			$result = mysql_query ( "SELECT DATUM, GELD_KONTO_BUCHUNGEN_DAT, GELD_KONTO_BUCHUNGEN_ID, G_BUCHUNGSNUMMER, BETRAG, VERWENDUNGSZWECK, KONTO_AUSZUGSNUMMER, ERFASS_NR, KONTENRAHMEN_KONTO,KOSTENTRAEGER_TYP, KOSTENTRAEGER_ID FROM GELD_KONTO_BUCHUNGEN WHERE GELDKONTO_ID='$geldkonto_id' && DATE_FORMAT(DATUM, '%Y-%m') = '$ja-$mo'  && AKTUELL='1' ORDER BY $sort ASC" );
		}
		$numrows = mysql_numrows ( $result );
		if ($numrows > 0) {
			while ( $row = mysql_fetch_assoc ( $result ) )
				$my_array [] = $row;
			
			$sort_datum_link = "<a href=\"?daten=buchen&option=buchungs_journal_druckansicht&sort=DATUM\">Datum</a>";
			$sort_bnr_link = "<a href=\"?daten=buchen&option=buchungs_journal_druckansicht&sort=G_BUCHUNGSNUMMER\">Buchungsnr</a>";
			$sort_betrag_link = "<a href=\"?daten=buchen&option=buchungs_journal_druckansicht&sort=BETRAG\">Betrag</a>";
			$sort_rechnung_link = "<a href=\"?daten=buchen&option=buchungs_journal_druckansicht&sort=ERFASS_NR\">Erfassungsnr.</a>";
			$sort_auszug_link = "<a href=\"?daten=buchen&option=buchungs_journal_druckansicht&sort=KONTO_AUSZUGSNUMMER\">Kontoauszug</a>";
			$sort_kostenkonto_link = "<a href=\"?daten=buchen&option=buchungs_journal_druckansicht&sort=KONTENRAHMEN_KONTO\">Konto</a>";
			
			/* Kontostand */
			$datum_ger = date_mysql2german ( $datum );
			$this->kontostand_tagesgenau_bis ( $geldkonto_id, $datum_ger );
			// $this->summe_konto_buchungen;
			$this->summe_konto_buchungen_a = nummer_punkt2komma ( $this->summe_konto_buchungen );
			
			$gk = new geldkonto_info ();
			$gk->geld_konto_details ( $geldkonto_id );
			$beguenstigter = $gk->konto_beguenstigter;
			
			$p = new partners ();
			$p->get_partner_id ( $beguenstigter );
			$partner_id = $p->partner_id;
			
			if (file_exists ( "print_css/" . $typ . "/" . $partner_id . "_logo.png" )) {
				echo "<div id=\"div_logo\"><img src=\"print_css/" . $typ . "/" . $partner_id . "_logo.png\"><br>$p->partner_name Rechnungseingangsbuch $monatname $jahr Mandanten-Nr.: $mandanten_nr Blatt: $monat<hr></div>\n";
			} else {
				echo "<div id=\"div_logo\">KEIN LOGO<br>Folgende Datei erstellen: print_css/" . $typ . "/" . $partner_id . "_logo.png<hr></div>";
			}
			
			echo "<table id=\"positionen_tab\">\n";
			echo "<thead>";
			echo "<tr class=feldernamen>";
			echo "<th scopr=\"col\" id=\"tr_ansehen\">Datum</th>";
			echo "<th >Erf.Nr</th>";
			echo "<th scopr=\"col\">Auszug</th>";
			echo "<th scopr=\"col\">Kostenkonto</th>";
			echo "<th scopr=\"col\">Betrag</th>";
			// echo "<th scopr=\"col\">Skontobetrag</th>";
			echo "<th scopr=\"col\">Zuordnung</th>";
			echo "<th scopr=\"col\">Buchungsnr</th>";
			echo "<th scopr=\"col\">Buchungstext</th>";
			echo "</tr>";
			
			echo "</thead>";
			
			echo "<tr class=feldernamen>";
			echo "<th scopr=\"col\" id=\"tr_ansehen\">$datum_ger</th>";
			echo "<th ></th>";
			echo "<th scopr=\"col\"></th>";
			echo "<th scopr=\"col\"></th>";
			echo "<th scopr=\"col\">$this->summe_konto_buchungen_a</th>";
			// echo "<th scopr=\"col\">Skontobetrag</th>";
			echo "<th scopr=\"col\"></th>";
			echo "<th scopr=\"col\"></th>";
			echo "<th scopr=\"col\">SALDO VORTRAG VORMONAT</th>";
			echo "</tr>";
			
			// echo "<tr class=\"feldernamen\"><td>$sort_datum_link</td><td>$sort_rechnung_link</td><td>$sort_auszug_link</td><td>$sort_kostenkonto_link</td><td>$sort_betrag_link</td><td>Zuordnung</td></td><td>$sort_bnr_link</td><td>Buchungstext</td></tr>";
			for($a = 0; $a < $numrows; $a ++) {
				$datum = date_mysql2german ( $my_array [$a] ['DATUM'] );
				$b_id = $my_array [$a] ['GELD_KONTO_BUCHUNGEN_ID'];
				$b_dat = $my_array [$a] ['GELD_KONTO_BUCHUNGEN_DAT'];
				$g_buchungsnummer = $my_array [$a] ['G_BUCHUNGSNUMMER'];
				$betrag = nummer_punkt2komma ( $my_array [$a] ['BETRAG'] );
				$vzweck = $my_array [$a] ['VERWENDUNGSZWECK'];
				$auszug = $my_array [$a] ['KONTO_AUSZUGSNUMMER'];
				$erfass_nr = $my_array [$a] ['ERFASS_NR'];
				$kostenkonto = $my_array [$a] ['KONTENRAHMEN_KONTO'];
				$kostentraeger_typ = $my_array [$a] ['KOSTENTRAEGER_TYP'];
				$kostentraeger_id = $my_array [$a] ['KOSTENTRAEGER_ID'];
				// $k = new kasse;
				$r = new rechnung ();
				// $kostentraeger_bez = $k->kostentraeger_beschreibung($kostentraeger_typ, $kostentraeger_id);
				if ($kostentraeger_typ == 'Mietvertrag') {
					$mv = new mietvertraege ();
					$mv->get_mietvertrag_infos_aktuell ( $kostentraeger_id );
					$kostentraeger_bez = $mv->personen_name_string_u;
				} else {
					$kostentraeger_bez = $r->kostentraeger_ermitteln ( $kostentraeger_typ, $kostentraeger_id );
				}
				// $kostentraeger_bez = $r->kostentraeger_ermitteln($kostentraeger_typ, $kostentraeger_id);
				$kostentraeger_bez = substr ( $kostentraeger_bez, 0, 50 );
				
				$link_buchungsbeleg = "<a href=\"?daten=buchen&option=geldbuchung_aendern&geldbuchung_dat=$b_dat\">$g_buchungsnummer</a>";
				
				echo "<tr><td>$datum</td><td>$erfass_nr</td><td>$auszug</td><td>$kostenkonto</td><td>$betrag</td><td>$kostentraeger_bez</td><td><b>$link_buchungsbeleg</b></td><td>$vzweck</td></tr>";
			}
			
			/* Datum Monat danach */
			$d_arr = explode ( ".", $datum_ger );
			$ja = $d_arr [2];
			$mo = $d_arr [1];
			$ta = $d_arr [0];
			
			if ($mo > 11) {
				$mo = 01;
				$ja = $ja + 1;
			} else {
				$mo = $mo + 1;
				$ja = $ja;
			}
			
			$datum_m_danach = "01.$mo.$ja";
			$this->kontostand_tagesgenau_bis ( $geldkonto_id, $datum_m_danach );
			$this->summe_konto_buchungen_a = nummer_punkt2komma ( $this->summe_konto_buchungen );
			echo "<tr><td><b>$datum_m_danach</b></td><td></td><td></td><td></td><td><b>$this->summe_konto_buchungen_a</b></td><td></td><td><b></b></td><td><b>KONTOSTAND</b></td></tr>";
			echo "</table>";
		}
	}
	function buchungsjournal_startzeit_pdf($geldkonto_id, $datum) {
		ob_clean (); // ausgabepuffer leeren
		//include_once ('pdfclass/class.ezpdf.php');
		include_once ('classes/class_bpdf.php');
		$pdf = new Cezpdf ( 'a4', 'portrait' );
		$bpdf = new b_pdf ();
		$bpdf->b_header ( $pdf, 'Partner', $_SESSION [partner_id], 'portrait', 'pdfclass/fonts/Helvetica.afm', 6 );
		$p = new partners ();
		$datum_heute = date ( "d.m.Y" );
		$p->get_partner_info ( $_SESSION ['partner_id'] );
		$pdf->addText ( 475, 700, 8, "$p->partner_ort, $datum_heute" );
		$dat_arr = explode ( "-", $datum );
		$ja = $dat_arr [0];
		$mo = $dat_arr [1];
		$monatsname = monat2name ( $mo );
		$ta = $dat_arr [2];
		/*
		 * if(!isset($_REQUEST['jahr'])){
		 * if(!isset($_REQUEST['sort']) && empty($_REQUEST['sort'])){
		 * $result = mysql_query ("SELECT DATUM, GELD_KONTO_BUCHUNGEN_DAT, GELD_KONTO_BUCHUNGEN_ID, G_BUCHUNGSNUMMER, BETRAG, VERWENDUNGSZWECK, KONTO_AUSZUGSNUMMER, ERFASS_NR, KONTENRAHMEN_KONTO, KOSTENTRAEGER_TYP, KOSTENTRAEGER_ID FROM GELD_KONTO_BUCHUNGEN WHERE GELDKONTO_ID='$geldkonto_id' && DATE_FORMAT(DATUM, '%Y-%m') = '$ja-$mo' && AKTUELL='1' ORDER BY G_BUCHUNGSNUMMER ASC");
		 * }else{
		 * $sort = $_REQUEST['sort'];
		 * $result = mysql_query ("SELECT DATUM, GELD_KONTO_BUCHUNGEN_DAT, GELD_KONTO_BUCHUNGEN_ID, G_BUCHUNGSNUMMER, BETRAG, VERWENDUNGSZWECK, KONTO_AUSZUGSNUMMER, ERFASS_NR, KONTENRAHMEN_KONTO,KOSTENTRAEGER_TYP, KOSTENTRAEGER_ID FROM GELD_KONTO_BUCHUNGEN WHERE GELDKONTO_ID='$geldkonto_id' && DATE_FORMAT(DATUM, '%Y-%m') = '$ja-$mo' && AKTUELL='1' ORDER BY $sort ASC");
		 * }
		 * }else{
		 * $jahr = $_REQUEST['jahr'];
		 * $result = mysql_query ("SELECT DATUM, GELD_KONTO_BUCHUNGEN_DAT, GELD_KONTO_BUCHUNGEN_ID, G_BUCHUNGSNUMMER, BETRAG, VERWENDUNGSZWECK, KONTO_AUSZUGSNUMMER, ERFASS_NR, KONTENRAHMEN_KONTO, KOSTENTRAEGER_TYP, KOSTENTRAEGER_ID FROM GELD_KONTO_BUCHUNGEN WHERE GELDKONTO_ID='$geldkonto_id' && DATE_FORMAT(DATUM, '%Y') = '$jahr' && AKTUELL='1' ORDER BY G_BUCHUNGSNUMMER ASC");
		 * }
		 */
		
		if (! isset ( $_REQUEST ['sort'] ) && empty ( $_REQUEST ['sort'] )) {
			$result = mysql_query ( "SELECT DATUM, GELD_KONTO_BUCHUNGEN_DAT, MWST_ANTEIL,GELD_KONTO_BUCHUNGEN_ID, G_BUCHUNGSNUMMER, BETRAG, VERWENDUNGSZWECK, KONTO_AUSZUGSNUMMER, ERFASS_NR, KONTENRAHMEN_KONTO, KOSTENTRAEGER_TYP, KOSTENTRAEGER_ID FROM GELD_KONTO_BUCHUNGEN WHERE GELDKONTO_ID='$geldkonto_id' && DATE_FORMAT(DATUM, '%Y-%m') = '$ja-$mo' && AKTUELL='1' ORDER BY G_BUCHUNGSNUMMER ASC" );
		} else {
			$sort = $_REQUEST ['sort'];
			$result = mysql_query ( "SELECT DATUM, GELD_KONTO_BUCHUNGEN_DAT, MWST_ANTEIL,GELD_KONTO_BUCHUNGEN_ID, G_BUCHUNGSNUMMER, BETRAG, VERWENDUNGSZWECK, KONTO_AUSZUGSNUMMER, ERFASS_NR, KONTENRAHMEN_KONTO,KOSTENTRAEGER_TYP, KOSTENTRAEGER_ID FROM GELD_KONTO_BUCHUNGEN WHERE GELDKONTO_ID='$geldkonto_id' && DATE_FORMAT(DATUM, '%Y-%m') = '$ja-$mo' && AKTUELL='1' ORDER BY $sort ASC" );
		}
		$numrows = mysql_numrows ( $result );
		if ($numrows > 0) {
			while ( $row = mysql_fetch_assoc ( $result ) )
				$my_array [] = $row;
				
				/* Kontostand */
			$datum_ger = date_mysql2german ( tage_minus ( $datum, 1 ) );
			$this->kontostand_tagesgenau_bis ( $geldkonto_id, $datum_ger );
			// $this->summe_konto_buchungen;
			$this->summe_konto_buchungen_a = nummer_punkt2komma ( $this->summe_konto_buchungen );
			
			$gk = new geldkonto_info ();
			$gk->geld_konto_details ( $geldkonto_id );
			$beguenstigter = $gk->konto_beguenstigter;
			$pdf->addText ( 43, 728, 6, "$gk->geldkonto_bezeichnung" );
			
			$p = new partners ();
			$p->get_partner_id ( $beguenstigter );
			$partner_id = $p->partner_id;
			
			$table_arr [$a] [DATUM] = "<b>$datum_ger</b>";
			$table_arr [$a] [BETRAG] = "<b>$this->summe_konto_buchungen_a</b>";
			$table_arr [$a] [VERWENDUNGSZWECK] = '<b>SALDO VORMONAT</b>';
			$this->summe_mwst = 0;
			for($a = 0; $a < $numrows; $a ++) {
				$datum = date_mysql2german ( $my_array [$a] [DATUM] );
				$b_id = $my_array [$a] [GELD_KONTO_BUCHUNGEN_ID];
				$b_dat = $my_array [$a] [GELD_KONTO_BUCHUNGEN_DAT];
				$g_buchungsnummer = $my_array [$a] [G_BUCHUNGSNUMMER];
				$betrag = $my_array [$a] [BETRAG];
				$mwst_anteil = $my_array [$a] ['MWST_ANTEIL'];
				$this->summe_mwst += $mwst_anteil;
				$vzweck = $my_array [$a] [VERWENDUNGSZWECK];
				$auszug = $my_array [$a] [KONTO_AUSZUGSNUMMER];
				$erfass_nr = $my_array [$a] [ERFASS_NR];
				$kostenkonto = $my_array [$a] [KONTENRAHMEN_KONTO];
				$kostentraeger_typ = $my_array [$a] [KOSTENTRAEGER_TYP];
				$kostentraeger_id = $my_array [$a] [KOSTENTRAEGER_ID];
				
				$r = new rechnung ();
				
				if ($kostentraeger_typ == 'Mietvertrag') {
					$mv = new mietvertraege ();
					$mv->get_mietvertrag_infos_aktuell ( $kostentraeger_id );
					$kostentraeger_bez = $mv->personen_name_string_u;
				} else {
					$kostentraeger_bez = $r->kostentraeger_ermitteln ( $kostentraeger_typ, $kostentraeger_id );
				}
				$kostentraeger_bez = substr ( $kostentraeger_bez, 0, 50 );
				$kostentraeger_bez = strip_tags ( $kostentraeger_bez );
				
				$table_arr [$a] [DATUM] = $datum;
				$table_arr [$a] [AUSZUG] = $auszug;
				$table_arr [$a] [BETRAG] = $betrag;
				$table_arr [$a] ['MWST_ANTEIL'] = $mwst_anteil;
				$table_arr [$a] [KONTO] = $kostenkonto;
				$table_arr [$a] [ZUORDNUNG] = $kostentraeger_bez;
				$table_arr [$a] [G_BUCHUNGSNUMMER] = $g_buchungsnummer;
				$table_arr [$a] [VERWENDUNGSZWECK] = $vzweck;
				$table_arr [$a] [KOSTENTRAEGER_BEZ] = $kostentraeger_bez;
				$table_arr [$a] [PLATZ] = "";
			} // end for
			
			/* Datum Monat danach */
			$d_arr = explode ( ".", $datum_ger );
			
			$ja = $d_arr [2];
			$pdf_jahr = $ja;
			$mo = $d_arr [1];
			$ta = $d_arr [0];
			
			if ($mo > 11) {
				$mo = 01;
				$ja = $ja + 1;
			} else {
				$mo1 = $mo + 1;
				$mo = sprintf ( "%02d", $mo1 );
				$ja = $ja;
			}
			
			$letzter_tag = date ( "t", mktime ( 0, 0, 0, $mo, 1, $ja ) );
			
			$datum_m_danach = "$letzter_tag.$mo.$ja";
			$datum_m_danach_1 = date_german2mysql ( $datum_m_danach );
			$datum_m_danach_2 = date_mysql2german ( $datum_m_danach_1 );
			$this->kontostand_tagesgenau_bis ( $geldkonto_id, $datum_m_danach_2 );
			$this->summe_konto_buchungen_a = nummer_punkt2komma_t ( $this->summe_konto_buchungen );
			
			$table_arr = $this->vzweck_kuerzen ( $table_arr );
			
			$L_pos = count ( $table_arr );
			$table_arr [$L_pos - 1] ['DATUM'] = '<b>Summen</b>';
			// $table_arr[$L_pos]['DATUM']='<b>Summe</b>';
			$this->summe_mwst_a = nummer_punkt2komma_t ( $this->summe_mwst );
			// $table_arr[$L_pos]['MWST_ANTEIL'] = "<b>$this->summe_mwst_a</b>";
			// $table_arr[$L_pos]['MWST_ANTEIL'] = "<b>$this->summe_mwst_a</b>";
			
			// $L_pos = count($table_arr);
			$table_arr [$a] ['DATUM'] = "<b>$datum_m_danach</b>";
			$table_arr [$a] ['VERWENDUNGSZWECK'] = '<b></b>';
			$table_arr [$a] ['BETRAG'] = "<b>$this->summe_konto_buchungen_a</b>";
			
			$cols = array (
					'DATUM' => "Datum",
					'G_BUCHUNGSNUMMER' => "BNR",
					'AUSZUG' => "Auszug",
					'KONTO' => "Konto",
					'BETRAG' => 'Betrag',
					'MWST_ANTEIL' => 'MWSt-Anteil',
					'KOSTENTRAEGER_BEZ' => 'Zuordnung',
					'VERWENDUNGSZWECK' => 'Buchungstext',
					'PLATZ' => 'Hinweis' 
			);
			
			$pdf->ezTable ( $table_arr, $cols, "Buchungsjournal $monatsname $pdf_jahr $gk->geldkonto_bezeichnung", array (
					'showHeadings' => 1,
					'shaded' => 0,
					'titleFontSize' => 8,
					'fontSize' => 7,
					'xPos' => 50,
					'xOrientation' => 'right',
					'width' => 500,
					'cols' => array (
							'DATUM' => array (
									'justification' => 'right',
									'width' => 50 
							),
							'G_BUCHUNGSNUMMER' => array (
									'justification' => 'right',
									'width' => 30 
							),
							'BETRAG' => array (
									'justification' => 'right',
									'width' => 50 
							),
							'KOSTENTRAEGER_BEZ' => array (
									'justification' => 'left',
									'width' => 75 
							),
							'KONTO' => array (
									'justification' => 'right',
									'width' => 30 
							),
							'AUSZUG' => array (
									'justification' => 'right',
									'width' => 35 
							),
							'PLATZ' => array (
									'justification' => 'left',
									'width' => 50 
							) 
					) 
			) );
			
			header ( "Content-type: application/pdf" ); // wird von MSIE ignoriert
			
			$gk_bez = str_replace ( ' ', '_', $gk->geldkonto_bez ) . '-Buchungsjournal_' . "$mo" . "_" . "$ja.pdf";
			$pdf_opt ['Content-Disposition'] = $gk_bez;
			$pdf->ezStream ( $pdf_opt );
		}  // end if numrow
else {
			$pdf->addText ( 43, 718, 50, "KEINE BUCHUNGEN" );
			$pdf->ezStream ( $pdf_opt );
		}
	}
	function vormonat($monat = null) {
		if ($monat != null) {
			
			if ($monat > 0 && $monat < 13) {
				
				if ($monat == 1) {
					$vormonat = 12;
				}
				
				if ($monat > 1) {
					$vormonat = $monat - 1;
				}
				
				return sprintf ( '%02d', $vormonat );
			}
		}
	}
	
	/* ja steht für Jahr */
	function buchungsjournal_jahr_pdf($geldkonto_id, $ja, $monat = null) {
		ob_clean (); // ausgabepuffer leeren
		//include_once ('pdfclass/class.ezpdf.php');
		include_once ('classes/class_bpdf.php');
		$pdf = new Cezpdf ( 'a4', 'portrait' );
		$bpdf = new b_pdf ();
		$bpdf->b_header ( $pdf, 'Partner', $_SESSION [partner_id], 'portrait', 'pdfclass/fonts/Helvetica.afm', 6 );
		
		if ($monat == null) {
			$result = mysql_query ( "SELECT DATUM, GELD_KONTO_BUCHUNGEN_DAT, GELD_KONTO_BUCHUNGEN_ID, G_BUCHUNGSNUMMER, BETRAG, MWST_ANTEIL,VERWENDUNGSZWECK, KONTO_AUSZUGSNUMMER, ERFASS_NR, KONTENRAHMEN_KONTO, KOSTENTRAEGER_TYP, KOSTENTRAEGER_ID FROM GELD_KONTO_BUCHUNGEN WHERE GELDKONTO_ID='$geldkonto_id' && DATE_FORMAT(DATUM, '%Y') = '$ja' && AKTUELL='1' ORDER BY G_BUCHUNGSNUMMER ASC" );
		} else {
			$result = mysql_query ( "SELECT DATUM, GELD_KONTO_BUCHUNGEN_DAT, GELD_KONTO_BUCHUNGEN_ID, G_BUCHUNGSNUMMER, BETRAG, MWST_ANTEIL,VERWENDUNGSZWECK, KONTO_AUSZUGSNUMMER, ERFASS_NR, KONTENRAHMEN_KONTO, KOSTENTRAEGER_TYP, KOSTENTRAEGER_ID FROM GELD_KONTO_BUCHUNGEN WHERE GELDKONTO_ID='$geldkonto_id' && DATE_FORMAT(DATUM, '%Y-%m') = '$ja-$monat' && AKTUELL='1' ORDER BY G_BUCHUNGSNUMMER ASC" );
		}
		$numrows = mysql_numrows ( $result );
		if ($numrows > 0) {
			while ( $row = mysql_fetch_assoc ( $result ) )
				$my_array [] = $row;
				
				/* Kontostand */
			if ($monat == null) {
				// $datum_ger = '01.01.'.$ja;
				$vorjahr = $ja - 1;
				$datum_ger = "31.12." . $vorjahr;
			} else {
				if ($monat == '01') {
					$vorjahr = $ja - 1;
					$datum_ger = "31.12." . $vorjahr;
				} else {
					$vormonat = $this->vormonat ( $monat );
					$ltvm = letzter_tag_im_monat ( $vormonat );
					$datum_ger = "$ltvm.$vormonat.$ja";
				}
			}
			// $datum_ger = date_mysql2german($datum);
			$this->kontostand_tagesgenau_bis ( $geldkonto_id, $datum_ger );
			// $this->summe_konto_buchungen;
			$this->summe_konto_buchungen_a = nummer_punkt2komma_t ( $this->summe_konto_buchungen );
			
			$gk = new geldkonto_info ();
			$gk->geld_konto_details ( $geldkonto_id );
			$beguenstigter = $gk->konto_beguenstigter;
			$pdf->addText ( 43, 728, 6, "$gk->geldkonto_bezeichnung" );
			
			$p = new partners ();
			$p->get_partner_id ( $beguenstigter );
			$partner_id = $p->partner_id;
			
			$table_arr [$a] ['DATUM'] = "<b>$datum_ger</b>";
			$table_arr [$a] ['BETRAG'] = "<b>$this->summe_konto_buchungen_a</b>";
			$table_arr [$a] ['VERWENDUNGSZWECK'] = '<b>SALDO VORMONAT</b>';
			
			for($a = 0; $a < $numrows; $a ++) {
				$datum = date_mysql2german ( $my_array [$a] ['DATUM'] );
				$b_id = $my_array [$a] ['GELD_KONTO_BUCHUNGEN_ID'];
				$b_dat = $my_array [$a] ['GELD_KONTO_BUCHUNGEN_DAT'];
				$g_buchungsnummer = $my_array [$a] ['G_BUCHUNGSNUMMER'];
				$betrag = $my_array [$a] ['BETRAG'];
				$mwst_anteil = $my_array [$a] ['MWST_ANTEIL'];
				$vzweck = $my_array [$a] ['VERWENDUNGSZWECK'];
				$auszug = $my_array [$a] ['KONTO_AUSZUGSNUMMER'];
				$erfass_nr = $my_array [$a] ['ERFASS_NR'];
				$kostenkonto = $my_array [$a] ['KONTENRAHMEN_KONTO'];
				$kostentraeger_typ = $my_array [$a] ['KOSTENTRAEGER_TYP'];
				$kostentraeger_id = $my_array [$a] ['KOSTENTRAEGER_ID'];
				
				$r = new rechnung ();
				
				if ($kostentraeger_typ == 'Mietvertrag') {
					$mv = new mietvertraege ();
					$mv->get_mietvertrag_infos_aktuell ( $kostentraeger_id );
					$kostentraeger_bez = $mv->personen_name_string_u;
				} else {
					$kostentraeger_bez = $r->kostentraeger_ermitteln ( $kostentraeger_typ, $kostentraeger_id );
				}
				$kostentraeger_bez = substr ( $kostentraeger_bez, 0, 50 );
				$kostentraeger_bez = strip_tags ( $kostentraeger_bez );
				
				$table_arr [$a] ['DATUM'] = $datum;
				$table_arr [$a] ['AUSZUG'] = $auszug;
				$table_arr [$a] ['BETRAG'] = $betrag;
				$table_arr [$a] ['MWST_ANTEIL'] = $mwst_anteil;
				$table_arr [$a] ['KONTO'] = $kostenkonto;
				$table_arr [$a] ['ZUORDNUNG'] = $kostentraeger_bez;
				$table_arr [$a] ['G_BUCHUNGSNUMMER'] = $g_buchungsnummer;
				$table_arr [$a] ['VERWENDUNGSZWECK'] = $vzweck;
				$table_arr [$a] ['KOSTENTRAEGER_BEZ'] = $kostentraeger_bez;
				$table_arr [$a] ['PLATZ'] = "";
			} // end for
			
			if ($monat == null) {
				$datum_m_danach = "31.12.$ja";
			} else {
				$ltm = letzter_tag_im_monat ( $monat, $jahr );
				$datum_m_danach = "$ltm.$monat.$ja";
			}
			
			$this->kontostand_tagesgenau_bis ( $geldkonto_id, $datum_m_danach );
			$this->summe_konto_buchungen_a = nummer_punkt2komma_t ( $this->summe_konto_buchungen );
			
			$L_pos = count ( $table_arr );
			$table_arr [$L_pos] ['DATUM'] = '<b>Summe</b>';
			
			// if(!isset($_REQUEST['xls'])){
			$table_arr = $this->vzweck_kuerzen ( $table_arr );
			// }
			
			$table_arr [$a] ['DATUM'] = "<b>$datum_m_danach</b>";
			$table_arr [$a] ['BETRAG'] = "<b>$this->summe_konto_buchungen_a</b>";
			$table_arr [$a] ['VERWENDUNGSZWECK'] = '<b>KONTOSTAND</b>';
			
			$cols = array (
					'DATUM' => "Datum",
					'G_BUCHUNGSNUMMER' => "BNR",
					'AUSZUG' => "Auszug",
					'KONTO' => "Konto",
					'BETRAG' => 'Betrag',
					'MWST_ANTEIL' => 'MWSt',
					'KOSTENTRAEGER_BEZ' => 'Zuordnung',
					'VERWENDUNGSZWECK' => 'Buchungstext',
					'PLATZ' => 'Hinweis' 
			);
			
			$pdf->ezTable ( $table_arr, $cols, "Buchungsjournal $ja $gk->geldkonto_bezeichnung", array (
					'showHeadings' => 1,
					'shaded' => 0,
					'titleFontSize' => 8,
					'fontSize' => 7,
					'xPos' => 50,
					'xOrientation' => 'right',
					'width' => 500,
					'cols' => array (
							'DATUM' => array (
									'justification' => 'right',
									'width' => 50 
							),
							'G_BUCHUNGSNUMMER' => array (
									'justification' => 'right',
									'width' => 30 
							),
							'BETRAG' => array (
									'justification' => 'right',
									'width' => 50 
							),
							'KOSTENTRAEGER_BEZ' => array (
									'justification' => 'left',
									'width' => 75 
							),
							'KONTO' => array (
									'justification' => 'right',
									'width' => 30 
							),
							'AUSZUG' => array (
									'justification' => 'right',
									'width' => 35 
							),
							'PLATZ' => array (
									'justification' => 'left',
									'width' => 50 
							) 
					) 
			) );
			
			ob_clean ();
			// header("Content-type: application/pdf"); // wird von MSIE ignoriert
			
			if (! isset ( $_REQUEST ['xls'] )) {
				$pdf->ezStream ();
			} else {
				
				ob_clean (); // ausgabepuffer leeren
				$fileName = "$gk->geldkonto_bezeichnung - Buchungsjournal $ja" . '.xls';
				header ( "Content-type: application/vnd.ms-excel" );
				// header("Content-Disposition: attachment; filename=$fileName");
				header ( "Content-Disposition: inline; filename=$fileName" );
				echo "<table class=\"sortable\" id=\"positionen_tab\">";
				echo "<thead>";
				echo "<tr>";
				echo "<th>DATUM</th>";
				echo "<th>BNR</th>";
				echo "<th>AUSZUG</th>";
				echo "<th>KONTO</th>";
				echo "<th>BEZEICHNUNG</th>";
				echo "<th>BETRAG</th>";
				echo "<th>MWST</th>";
				echo "<th>ZUORDNUNG</th>";
				echo "<th>BUCHUNGSTEXT</th>";
				echo "</tr>";
				echo "</thead>";
				
				$anz_zeilen = count ( $table_arr );
				$summe_xls = 0;
				
				for($aa = 0; $aa < $anz_zeilen - 4; $aa ++) {
					$datum_d = $table_arr [$aa] ['DATUM'];
					$bnr = $table_arr [$aa] ['G_BUCHUNGSNUMMER'];
					$auszug = $table_arr [$aa] ['AUSZUG'];
					$kto = $table_arr [$aa] ['KONTO'];
					
					/* Bezeichnung der Konten holen */
					$k = new kontenrahmen ();
					$kontenrahmen_id = $k->get_kontenrahmen ( 'GELDKONTO', $geldkonto_id );
					$k->konto_informationen2 ( $kto, $kontenrahmen_id );
					/* $k->konto_bezeichnung */
					
					$betrag_o_eur = $table_arr [$aa] ['BETRAG_O_EUR'];
					$mwst = $table_arr [$aa] ['MWST_ANTEIL_O_EUR'];
					$zuordnung = $table_arr [$aa] ['ZUORDNUNG'];
					$text = $table_arr [$aa] ['VERWENDUNGSZWECK'];
					echo "<tr>";
					echo "<td>$datum_d</td><td>$bnr</td><td>$auszug</td><td>$kto</td><td>$k->konto_bezeichnung</td><td>$betrag_o_eur</td><td>$mwst</td><td>$zuordnung</td><td>$text</td>";
					echo "</tr>";
				}
				echo "</table>";
				die ();
			}
		}  // end if numrow
else {
			$pdf->addText ( 43, 718, 50, "KEINE BUCHUNGEN" );
			if (! isset ( $_REQUEST ['xls'] )) {
				$pdf->ezStream ();
			} else {
				fehlermeldung_ausgeben ( "Keine Buchungen im Jahr $ja" );
			}
		}
	}
	function buchungsbeleg_ansicht($buchungsnr) {
		echo $buchungsnr;
	}
	function konten_aus_buchungen($geldkonto_id) {
		$result = mysql_query ( "SELECT KONTENRAHMEN_KONTO AS KONTO FROM GELD_KONTO_BUCHUNGEN WHERE GELDKONTO_ID='$geldkonto_id' && AKTUELL='1' GROUP BY KONTENRAHMEN_KONTO ORDER BY KONTENRAHMEN_KONTO ASC" );
		
		$numrows = mysql_numrows ( $result );
		if ($numrows > 0) {
			while ( $row = mysql_fetch_assoc ( $result ) )
				$my_array [] = $row;
		}
		return $my_array;
	}
	function get_bebuchte_konten($geldkonto_id, $kos_typs, $kos_ids) {
		$anz = count ( $kos_typs );
		$str_kos = 'AND ';
		for($a = 0; $a < $anz; $a ++) {
			$kos_typ = $kos_typs [$a];
			$kos_id = $kos_ids [$a];
			if ($a < $anz - 1) {
				$str_kos .= "(KOSTENTRAEGER_TYP='$kos_typ' AND KOSTENTRAEGER_ID='$kos_id') OR ";
			} else {
				$str_kos .= "(KOSTENTRAEGER_TYP='$kos_typ' AND KOSTENTRAEGER_ID='$kos_id') ";
			}
		}
		$result = mysql_query ( "SELECT KONTENRAHMEN_KONTO AS KONTO FROM GELD_KONTO_BUCHUNGEN WHERE GELDKONTO_ID='$geldkonto_id' && AKTUELL='1' $str_kos GROUP BY KONTENRAHMEN_KONTO ORDER BY KONTENRAHMEN_KONTO ASC" );
		// echo "SELECT KONTENRAHMEN_KONTO AS KONTO FROM GELD_KONTO_BUCHUNGEN WHERE GELDKONTO_ID='$geldkonto_id' && AKTUELL='1' $str_kos GROUP BY KONTENRAHMEN_KONTO ORDER BY KONTENRAHMEN_KONTO ASC";
		// die();
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			while ( $row = mysql_fetch_assoc ( $result ) )
				$my_array [] = $row;
			return $my_array;
		}
	}
	function get_buchungen_vor($jahr = '2005') {
		$result = mysql_query ( "SELECT * FROM  `GELD_KONTO_BUCHUNGEN` WHERE  `DATUM` <  '$jahr-01-01' AND  `AKTUELL` =  '1'" );
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			return $numrows;
		}
		return false;
	}
	function monate_jahres_links($jahr, $link) {
		$f = new formular ();
		$f->fieldset ( "Monats- und Jahresauswahl", 'kostenkonten' );
		$vorjahr = $jahr - 1;
		$nachjahr = $jahr + 1;
		$link_vorjahr = "&nbsp;<a href=\"$link&jahr=$vorjahr\"><b>$vorjahr</b></a>&nbsp;";
		$link_nach = "&nbsp;<a href=\"$link&jahr=$nachjahr\"><b>$nachjahr</b></a>&nbsp;";
		echo $link_vorjahr;
		$link_alle = "<a href=\"$link&jahr=$jahr\">Alle von $jahr</a>&nbsp;";
		echo $link_alle;
		for($a = 1; $a <= 12; $a ++) {
			$monat_zweistellig = sprintf ( '%02d', $a );
			$link_neu = "<a href=\"$link&monat=$monat_zweistellig&jahr=$jahr\">$a/$jahr</a>&nbsp;";
			// echo "$a/$jahr<br>";
			echo "$link_neu";
		}
		echo $link_nach;
		$f->fieldset_ende ();
	}
	function buchungskonten_uebersicht($geldkonto_id) {
		$konten_arr = $this->konten_aus_buchungen ( $geldkonto_id );
		// echo "<pre>";
		// print_r($konten_arr);
		$form = new formular ();
		$jahr = $_REQUEST ['jahr'];
		$monat = $_REQUEST ['monat'];
		$link = "?daten=buchen&option=konten_uebersicht";
		
		if (isset ( $jahr ) && isset ( $monat )) {
			$this->monate_jahres_links ( $jahr, $link );
		}
		if (isset ( $jahr ) && ! isset ( $monat )) {
			$this->monate_jahres_links ( $jahr, $link );
		}
		if (! isset ( $jahr ) && ! isset ( $monat )) {
			$monat = date ( "m" );
			$jahr = date ( "Y" );
			$this->monate_jahres_links ( $jahr, $link );
		}
		
		$form = new formular ();
		$form->fieldset ( "Kostenbericht $monat $jahr", 'kostenbericht' );
		if (isset ( $monat ) && isset ( $jahr )) {
			$pdf_link = "<a href=\"?daten=buchen&option=konten_uebersicht_pdf&monat=$monat&jahr=$jahr\">PDF ERSTELLEN</a>";
		}
		
		if (! isset ( $monat ) && isset ( $jahr )) {
			$pdf_link = "<a href=\"?daten=buchen&option=konten_uebersicht_pdf&jahr=$jahr\">PDF ERSTELLEN</a>";
		}
		echo $pdf_link;
		
		// ###########
		$anzahl_konten = count ( $konten_arr );
		for($a = 0; $a < $anzahl_konten; $a ++) {
			$kostenkonto = $konten_arr [$a] ['KONTO'];
			
			if (isset ( $jahr ) && isset ( $monat )) {
				$this->kontobuchungen_anzeigen_jahr_monat ( $geldkonto_id, $kostenkonto, $jahr, $monat );
			}
			if (isset ( $jahr ) && ! isset ( $monat )) {
				$this->kontobuchungen_anzeigen_jahr ( $geldkonto_id, $kostenkonto, $jahr );
			}
			if (! isset ( $jahr ) && ! isset ( $monat )) {
				$monat = date ( "m" );
				$jahr = date ( "Y" );
				$this->kontobuchungen_anzeigen_jahr_monat ( $geldkonto_id, $kostenkonto, $jahr, $monat );
			}
		} // end for
		
		$form->fieldset_ende ();
	} // end function
	function buchungskonten_uebersicht_pdf($geldkonto_id) {
		// die('SANEL DEMO');
		$konten_arr = $this->konten_aus_buchungen ( $geldkonto_id );
		$g = new geldkonto_info ();
		$g->geld_konto_details ( $geldkonto_id );
		
		$form = new formular ();
		$jahr = $_REQUEST ['jahr'];
		if (isset ( $_REQUEST ['monat'] )) {
			$monat = $_REQUEST ['monat'];
		}
		// $monat = sprintf('%02d',$monato);
		$link = "?daten=buchen&option=konten_uebersicht";
		
		if (isset ( $jahr ) && isset ( $monat )) {
			$this->monate_jahres_links ( $jahr, $link );
		}
		if (isset ( $jahr ) && ! isset ( $monat )) {
			$this->monate_jahres_links ( $jahr, $link );
		}
		if (! isset ( $jahr ) && ! isset ( $monat )) {
			$monat = date ( "m" );
			$jahr = date ( "Y" );
			$this->monate_jahres_links ( $jahr, $link );
		}
		
		// ###########
		
		ob_clean (); // ausgabepuffer leeren
		//include_once ('pdfclass/class.ezpdf.php');
		include_once ('classes/class_bpdf.php');
		$pdf = new Cezpdf ( 'a4', 'portrait' );
		$bpdf = new b_pdf ();
		$bpdf->b_header ( $pdf, 'Partner', $_SESSION [partner_id], 'portrait', 'pdfclass/fonts/Helvetica.afm', 6 );
		$pdf->addText ( 43, 735, 6, "$g->geldkonto_bezeichnung" );
		$pdf->addText ( 43, 728, 6, "KNr:$g->kontonummer BLZ:$g->blz" );
		$datum_heute = date ( "d.m.Y" );
		$p = new partners ();
		$p->get_partner_info ( $_SESSION ['partner_id'] );
		$pdf->addText ( 475, 700, 8, "$p->partner_ort, $datum_heute" );
		
		if (isset ( $monat )) {
			$monatname = monat2name ( $monat );
			$pdf->ezText ( "<u>Buchungskontenübersicht $monatname $jahr</u>", 12, array (
					'justification' => 'center' 
			) );
		} else {
			$pdf->ezText ( "<u>Buchungskontenübersicht $jahr</u>", 11, array (
					'justification' => 'center' 
			) );
		}
		$pdf->ezSetDy ( - 10 ); // abstand
		
		$k = new kontenrahmen ();
		$anzahl_konten = count ( $konten_arr );
		for($a = 0; $a < $anzahl_konten; $a ++) {
			$kostenkonto = $konten_arr [$a] ['KONTO'];
			$kontenrahmen_id = $k->get_kontenrahmen ( 'GELDKONTO', $geldkonto_id );
			$k->konto_informationen2 ( $kostenkonto, $kontenrahmen_id );
			
			// $this->kontobuchungen_pdf($geldkonto_id, $kostenkonto, $jahr, $monat);
			if (isset ( $monat )) {
				$monat = sprintf ( '%02d', $monat );
				$result1 = mysql_query ( "SELECT date_format(DATUM,'%d.%m.%Y') AS DATUM, G_BUCHUNGSNUMMER, ERFASS_NR, KONTO_AUSZUGSNUMMER, BETRAG, MWST_ANTEIL, RTRIM(LTRIM(VERWENDUNGSZWECK)) AS VERWENDUNGSZWECK, KOSTENTRAEGER_TYP, KOSTENTRAEGER_ID FROM GELD_KONTO_BUCHUNGEN WHERE GELDKONTO_ID='$geldkonto_id' && KONTENRAHMEN_KONTO = '$kostenkonto' && ( DATE_FORMAT( DATUM, '%Y-%m' ) = '$jahr-$monat') && AKTUELL='1' ORDER BY GELD_KONTO_BUCHUNGEN_ID ASC" );
			} else {
				// die("JAHR: $jahr");
				$result1 = mysql_query ( "SELECT date_format(DATUM,'%d.%m.%Y') AS DATUM, G_BUCHUNGSNUMMER, ERFASS_NR, KONTO_AUSZUGSNUMMER, BETRAG, MWST_ANTEIL,RTRIM(LTRIM(VERWENDUNGSZWECK)) AS VERWENDUNGSZWECK, KOSTENTRAEGER_TYP, KOSTENTRAEGER_ID FROM GELD_KONTO_BUCHUNGEN WHERE GELDKONTO_ID='$geldkonto_id' && KONTENRAHMEN_KONTO = '$kostenkonto' && ( DATE_FORMAT( DATUM, '%Y' ) = '$jahr') && AKTUELL='1' ORDER BY GELD_KONTO_BUCHUNGEN_ID ASC" );
			}
			
			$numrows = mysql_numrows ( $result1 );
			// echo "SELECT date_format(DATUM,'%d.%m.%Y') AS DATUM, G_BUCHUNGSNUMMER, ERFASS_NR, KONTO_AUSZUGSNUMMER, BETRAG, MWST_ANTEIL,RTRIM(LTRIM(VERWENDUNGSZWECK)) AS VERWENDUNGSZWECK, KOSTENTRAEGER_TYP, KOSTENTRAEGER_ID FROM GELD_KONTO_BUCHUNGEN WHERE GELDKONTO_ID='$geldkonto_id' && KONTENRAHMEN_KONTO = '$kostenkonto' && ( DATE_FORMAT( DATUM, '%Y' ) = '$jahr') && AKTUELL='1' ORDER BY GELD_KONTO_BUCHUNGEN_ID ASC";
			// die("SANEL $numrows");
			if ($numrows) {
				while ( $row = mysql_fetch_assoc ( $result1 ) ) {
					$table_arr [] = $row;
				}
				
				// print_r($table_arr);
				// die();
				$L_pos = count ( $table_arr );
				$table_arr = $this->vzweck_kuerzen ( $table_arr );
				$table_arr [$L_pos] ['DATUM'] = '<b>Summe</b>';
				
				for($ga = 0; $ga < $L_pos; $ga ++) {
					$r = new rechnung ();
					$kostentraeger_typ = $table_arr [$ga] ['KOSTENTRAEGER_TYP'];
					$kostentraeger_id = $table_arr [$ga] ['KOSTENTRAEGER_ID'];
					$kos_bez = $r->kostentraeger_ermitteln ( $kostentraeger_typ, $kostentraeger_id );
					$table_arr [$ga] ['KOS_BEZ'] = $kos_bez;
				}
				
				$cols = array (
						'DATUM' => "Datum",
						'KONTO_AUSZUGSNUMMER' => "AUSZUG",
						'G_BUCHUNGSNUMMER' => "BNR",
						'BETRAG' => 'Betrag',
						'MWST_ANTEIL' => 'MWSt-Anteil',
						'KOS_BEZ' => 'Zuordnung',
						'VERWENDUNGSZWECK' => 'Buchungstext' 
				);
				// print_r($table_arr);
				// die();
				$pdf->ezTable ( $table_arr, $cols, "<b>Buchungskonto $kostenkonto - $k->konto_bezeichnung</b>", array (
						'showHeadings' => 1,
						'shaded' => 0,
						'titleFontSize' => 8,
						'fontSize' => 7,
						'xPos' => 50,
						'xOrientation' => 'right',
						'width' => 500,
						'cols' => array (
								'DATUM' => array (
										'justification' => 'right',
										'width' => 65 
								),
								'G_BUCHUNGSNUMMER' => array (
										'justification' => 'right',
										'width' => 30 
								),
								'BETRAG' => array (
										'justification' => 'right',
										'width' => 75 
								) 
						) 
				) );
				$pdf->ezSetDy ( - 5 ); // abstand
			}
			
			unset ( $table_arr );
		} // end for
		  // die();
		ob_clean ();
		// header("Content-type: application/pdf"); // wird von MSIE ignoriert
		header ( 'Content-type: application/pdf' );
		// header("Content-Type: application/download");
		// header("Content-Description: File Transfer");
		// header('Content-Disposition: attachment; filename="testssss.pdf"');
		// print_r($g);
		// die();
		$gk_bez = str_replace ( ' ', '_', $g->geldkonto_bez ) . '-Buchungskontenuebersicht_' . "$monat" . "_" . "$jahr.pdf";
		$pdf_opt ['Content-Disposition'] = $gk_bez;
		$pdf->ezStream ( $pdf_opt );
	} // end function
	function vzweck_kuerzen($table_arr) {
		// echo '<pre>';
		// print_r($table_arr);
		// die();
		$anzahl_zeilen = count ( $table_arr );
		$summe = 0;
		$summe_mwst = 0;
		for($a = 0; $a < $anzahl_zeilen; $a ++) {
			$vzweck = $table_arr [$a] ['VERWENDUNGSZWECK'];
			$betrag_o = $table_arr [$a] ['BETRAG'];
			$mwst_anteil = $table_arr [$a] ['MWST_ANTEIL'];
			$kto_auszugsnr = $table_arr [$a] ['KONTO_AUSZUGSNUMMER'];
			$erfass_nr = $table_arr [$a] ['ERFASS_NR'];
			if (isset ( $kto_auszugsnr ) && isset ( $erfass_nr )) {
				
				// if($erfass_nr == $kto_auszugsnr){
				if ($erfass_nr != 'MIETE' && $erfass_nr != 'DTAUS' && $erfass_nr != $kto_auszugsnr) {
					$rr = new rechnung ();
					$rr->rechnung_grunddaten_holen ( $erfass_nr );
					$aussteller_name = $rr->rechnungs_aussteller_name . ',';
					// die(" $rr->rechnungs_aussteller_name BITTE DEN HERRN SIVAC INFORMIEREN!!!!!!");
				}
			} else {
				$aussteller_name = '';
			}
			
			$summe += $betrag_o;
			$summe_mwst += $mwst_anteil;
			$betrag = nummer_punkt2komma ( $table_arr [$a] ['BETRAG'] );
			$mwst_anteil = nummer_punkt2komma ( $table_arr [$a] ['MWST_ANTEIL'] );
			if (preg_match ( "/Erfnr:/i", "$vzweck" )) {
				// echo "Es wurde eine Übereinstimmung gefunden.";
				// $pos = strpos($vzweck, 'Rnr:'); //bis zu Rnr: abschneiden
				$pos = strpos ( $vzweck, ' ' ); // bis zu Rnr: abschneiden
				if ($pos == true) {
					$vzweck_neu = substr ( $vzweck, $pos );
					// $vzweck_neu = $vzweck;
					// echo $pos.'<br>';
					// echo $vzweck.'<br>';
					// echo $vzweck_neu.'<br>';
					$table_arr [$a] ['VERWENDUNGSZWECK'] = $aussteller_name . ' ' . $vzweck_neu;
				}
			} else {
				// echo "Es wurde keine Übereinstimmung gefunden.";
			}
			
			$table_arr [$a] ['BETRAG_O_EUR'] = $betrag;
			$table_arr [$a] ['MWST_ANTEIL_O_EUR'] = $mwst_anteil;
			if ($betrag != '0,00') {
				$table_arr [$a] ['BETRAG'] = $betrag . ' €';
				$table_arr [$a] ['MWST_ANTEIL'] = $mwst_anteil . ' €';
			}
			unset ( $erfass_nr );
			unset ( $kto_auszugsnr );
		} // end for
		$summe = nummer_punkt2komma ( $summe );
		$summe_mwst_a = nummer_punkt2komma ( $summe_mwst );
		$table_arr [$anzahl_zeilen] ['BETRAG'] = "<b>$summe €</b>";
		$table_arr [$anzahl_zeilen] ['MWST_ANTEIL'] = "<b>$summe_mwst_a €</b>";
		// die();
		return $table_arr;
	}
	function kontobuchungen_anzeigen_jahr($geldkonto_id, $kostenkonto, $jahr) {
		$result = mysql_query ( "SELECT * FROM GELD_KONTO_BUCHUNGEN WHERE GELDKONTO_ID='$geldkonto_id' && KONTENRAHMEN_KONTO = '$kostenkonto' && ( DATE_FORMAT( DATUM, '%Y' ) = '$jahr') && AKTUELL='1' ORDER BY GELD_KONTO_BUCHUNGEN_ID DESC" );
		
		$numrows = mysql_numrows ( $result );
		if ($numrows > 0) {
			$k = new kontenrahmen ();
			$k->konto_informationen ( $kostenkonto );
			$kontenrahmen_id = $k->get_kontenrahmen ( 'GELDKONTO', $geldkonto_id );
			$k->konto_informationen2 ( $kostenkonto, $kontenrahmen_id );
			echo "<table>";
			echo "<tr><th><b>$kostenkonto  $k->konto_bezeichnung</b></th></tr>";
			echo "</table>";
			echo "<table class=\"sortable\">";
			echo "<tr><th>Datum</th><th>Erfassnr</th><th>Betrag</th><th>ZUWEISUNG</th><th>Buchungsnr</th><th>Vzweck</th></tr>";
			while ( $row = mysql_fetch_assoc ( $result ) ) {
				$datum = date_mysql2german ( $row ['DATUM'] );
				$auszugsnr = $row ['KONTO_AUSZUGSNUMMER'];
				$erfass_nr = $row ['ERFASS_NR'];
				$betrag = nummer_punkt2komma ( $row ['BETRAG'] );
				$vzweck = $row ['VERWENDUNGSZWECK'];
				$b_id = $row ['GELD_KONTO_BUCHUNGEN_ID'];
				$g_buchungsnummer = $row ['G_BUCHUNGSNUMMER'];
				$this->summe_kontobuchungen_jahr ( $geldkonto_id, $kostenkonto, $jahr );
				
				$kos_typ = $row ['KOSTENTRAEGER_TYP'];
				$kos_id = $row ['KOSTENTRAEGER_ID'];
				
				$r = new rechnung ();
				$kos_bez = $r->kostentraeger_ermitteln ( $kos_typ, $kos_id );
				
				echo "<tr></td><td>$datum</td><td>$erfass_nr</td><td>$betrag</td><td>$kos_bez</td><td>$g_buchungsnummer</td><td>$vzweck</td></tr>";
			}
			$this->summe_konto_buchungen = nummer_punkt2komma ( $this->summe_konto_buchungen );
			
			echo "<tfoot><tr><td></td><td></td><td><b>Summe</b></td><td><b>$this->summe_konto_buchungen €</b></td><td></td><td></td></tr></tfoot>";
			
			echo "</table><br>";
		} else {
			// echo "Geldkonto $geldkonto_id - Kostenkonto $kostenkonto leer<br>";
		}
	}
	function kontobuchungen_pdf($geldkonto_id, $kostenkonto, $jahr, $monat) {
		if ($monat == '') {
			$result = mysql_query ( "SELECT DATUM, BETRAG, VERWENDUNGSZWECK FROM GELD_KONTO_BUCHUNGEN WHERE GELDKONTO_ID='$geldkonto_id' && KONTENRAHMEN_KONTO = '$kostenkonto' && ( DATE_FORMAT( DATUM, '%Y' ) = '$jahr') && AKTUELL='1' ORDER BY GELD_KONTO_BUCHUNGEN_ID DESC" );
		} else {
			$result = mysql_query ( "SELECT DATUM, BETRAG, VERWENDUNGSZWECK FROM GELD_KONTO_BUCHUNGEN WHERE GELDKONTO_ID='$geldkonto_id' && KONTENRAHMEN_KONTO = '$kostenkonto' && ( DATE_FORMAT( DATUM, '%Y-%m' ) = '$jahr-$monat') && AKTUELL='1' ORDER BY GELD_KONTO_BUCHUNGEN_ID DESC" );
		}
		
		$numrows = mysql_numrows ( $result );
		if ($numrows > 0) {
			$k = new kontenrahmen ();
			$k->konto_informationen ( $kostenkonto );
			
			/* PDF */
			//include_once ('pdfclass/class.ezpdf.php');
			$pdf = new Cezpdf ( 'a4', 'portrait' );
			$pdf->ezSetCmMargins ( 4.5, 2.5, 2.5, 2.5 );
			$berlus_schrift = './fonts/Times-Roman.afm';
			$text_schrift = './fonts/Arial.afm';
			$pdf->addJpegFromFile ( 'pdfclass/hv_logo198_80.jpg', 450, 780, 100, 42 );
			$pdf->setLineStyle ( 0.5 );
			$pdf->selectFont ( $berlus_schrift );
			$pdf->addText ( 42, 743, 6, "BERLUS HAUSVERWALTUNG - Fontanestr. 1 - 14193 Berlin" );
			$pdf->line ( 42, 750, 550, 750 );
			$seite = $pdf->ezGetCurrentPageNumber ();
			$alle_seiten = $pdf->ezPageCount;
			$pdf->addText ( 70, 780, 12, "Kostenkonto $kostenkonto" );
			
			while ( $row = mysql_fetch_assoc ( $result ) ) {
				$datum = date_mysql2german ( $row [DATUM] );
				$betrag = nummer_punkt2komma ( $row [BETRAG] );
				$vzweck = $this->umlaute_anpassen ( ltrim ( rtrim ( $row [VERWENDUNGSZWECK] ) ) );
				$this->summe_kontobuchungen_jahr ( $geldkonto_id, $kostenkonto, $jahr );
				echo "<tr></td><td>$datum</td><td>$erfass_nr</td><td>$betrag</td><td>$b_id</td><td>$vzweck</td></tr><br>";
				$pdf->ezText ( "$datum", 10, array (
						'left' => '0' 
				) );
				if (strlen ( $vzweck ) > '60') {
					$pdf->ezText ( "$vzweck", 10, array (
							'left' => '60' 
					) );
					// $pdf->ezSetDy(-12); //abstand
				} else {
					$pdf->ezText ( "$vzweck", 10, array (
							'left' => '60' 
					) );
				}
				$pdf->ezText ( "$betrag", 10, array (
						'left' => '430' 
				) );
				$pdf->ezSetDy ( - 12 ); // abstand
			}
			
			$content = $pdf->output ();
			
			$dateiname_org = 'Kontenbericht';
			$dateiname = $this->save_file ( $dateiname_org, 'Monatsbericht_Konten', $geldkonto_id, $content, $monat, $jahr );
			
			// weiterleiten($dateiname);
			$download_link = "<h3><a href=\"$dateiname\">Monatsbericht $geldkonto_id für $monat/$jahr HIER</a></h3>";
			
			echo $download_link;
		} else {
			// echo "Geldkonto $geldkonto_id - Kostenkonto $kostenkonto leer<br>";
		}
	}
	function summe_kontobuchungen_jahr($geldkonto_id, $kostenkonto, $jahr) {
		$result = mysql_query ( "SELECT SUM(BETRAG) AS SUMME FROM GELD_KONTO_BUCHUNGEN WHERE GELDKONTO_ID='$geldkonto_id' && KONTENRAHMEN_KONTO = '$kostenkonto' && ( DATE_FORMAT( DATUM, '%Y' ) = '$jahr') && AKTUELL='1'" );
		
		$this->summe_konto_buchungen = 0.00;
		
		$numrows = mysql_numrows ( $result );
		if ($numrows > 0) {
			$this->summe_konto_buchungen = 0;
			$row = mysql_fetch_assoc ( $result );
			$this->summe_konto_buchungen = $row ['SUMME'];
			// echo "$kostenkonto $this->summe_konto_buchungen<br>";
			return $this->summe_konto_buchungen;
		} else {
			$this->summe_konto_buchungen = 0.00;
		}
	}
	function kontobuchungen_anzeigen_jahr_monat($geldkonto_id, $kostenkonto, $jahr, $monat) {
		$result = mysql_query ( "SELECT * FROM GELD_KONTO_BUCHUNGEN WHERE GELDKONTO_ID='$geldkonto_id' && KONTENRAHMEN_KONTO = '$kostenkonto' && ( DATE_FORMAT( DATUM, '%Y-%m' ) = '$jahr-$monat') && AKTUELL='1' ORDER BY GELD_KONTO_BUCHUNGEN_ID DESC" );
		
		$numrows = mysql_numrows ( $result );
		if ($numrows > 0) {
			$k = new kontenrahmen ();
			// $k->konto_informationen($kostenkonto);
			$kontenrahmen_id = $k->get_kontenrahmen ( 'GELDKONTO', $geldkonto_id );
			$k->konto_informationen2 ( $kostenkonto, $kontenrahmen_id );
			echo "<table>";
			echo "<tr><th>$kostenkonto $k->konto_bezeichnung</th></tr>";
			echo "</table>";
			echo "<table class=\"sortable\">";
			echo "<tr><th>Datum</th><th>Erfassnr</th><th>Betrag</th><th>ZUWEISUNG</th><th>Buchungsnr</th><th>Vzweck</th></tr>";
			while ( $row = mysql_fetch_assoc ( $result ) ) {
				$datum = date_mysql2german ( $row ['DATUM'] );
				$auszugsnr = $row ['KONTO_AUSZUGSNUMMER'];
				$erfass_nr = $row ['ERFASS_NR'];
				$betrag = nummer_punkt2komma ( $row ['BETRAG'] );
				$vzweck = $row ['VERWENDUNGSZWECK'];
				$b_id = $row ['GELD_KONTO_BUCHUNGEN_ID'];
				$g_buchungsnummer = $row ['G_BUCHUNGSNUMMER'];
				$kos_typ = $row ['KOSTENTRAEGER_TYP'];
				$kos_id = $row ['KOSTENTRAEGER_ID'];
				$this->summe_kontobuchungen_jahr_monat ( $geldkonto_id, $kostenkonto, $jahr, $monat );
				$r = new rechnung ();
				$kos_bez = $r->kostentraeger_ermitteln ( $kos_typ, $kos_id );
				echo "<tr></td><td>$datum</td><td>$erfass_nr</td><td>$betrag</td><td>$kos_bez</td><td>$g_buchungsnummer</td><td>$vzweck</td></tr>";
			}
			$this->summe_konto_buchungen = nummer_punkt2komma ( $this->summe_konto_buchungen );
			echo "<tfoot><tr><td></td><td><b>Summe</b></td><td><b>$this->summe_konto_buchungen €</b></td><td></td><td></td><td></td></tr></tfoot>";
			
			echo "</table><br>";
		} else {
			// echo "Geldkonto $geldkonto_id - Kostenkonto $kostenkonto leer<br>";
		}
	}
	function summe_kontobuchungen_jahr_monat($geldkonto_id, $kostenkonto, $jahr, $monat) {
		$result = mysql_query ( "SELECT SUM(BETRAG) AS SUMME FROM GELD_KONTO_BUCHUNGEN WHERE GELDKONTO_ID='$geldkonto_id' && KONTENRAHMEN_KONTO = '$kostenkonto' && ( DATE_FORMAT( DATUM, '%Y-%m' ) = '$jahr-$monat') && AKTUELL='1'" );
		
		$numrows = mysql_numrows ( $result );
		if ($numrows > 0) {
			$this->summe_konto_buchungen = 0;
			$row = mysql_fetch_assoc ( $result );
			$this->summe_konto_buchungen = $row ['SUMME'];
		} else {
			$this->summe_konto_buchungen = 0;
			// echo "leer";
		}
	}
	function form_kontouebersicht() {
		echo "<hr><br>";
		/*
		 * echo "<pre>";
		 * print_r($_SESSION);
		 */
		$geldkonto_id = $_SESSION [geldkonto_id];
		$form = new formular ();
		$form->hidden_feld ( "geldkonto_id", "$geldkonto_id" );
		$this->dropdown_kostenrahmen_nr ( 'Kostenkonto', 'kostenkonto', 'GELDKONTO', $geldkonto_id, '' );
		// $form->text_feld_inaktiv("Kontobezeichnung", "kontobezeichnung", "", "20", 'kontobezeichnung');
		// $form->text_feld_inaktiv("Kontoart", "kontoart", "", "20", 'kontoart');
		// $form->text_feld_inaktiv("Kostengruppe", "kostengruppe", "", "20", 'kostengruppe');
		$js_typ = "onchange=\"list_kostentraeger('list_kostentraeger', this.value)\"";
		$this->dropdown_kostentreager_typen ( 'Kostenträgertyp', 'kostentraeger_typ', 'kostentraeger_typ', $js_typ );
		$js_id = "";
		$this->dropdown_kostentreager_ids ( 'Kostenträger', 'kostentraeger_id', 'dd_kostentraeger_id', $js_id );
		$form->text_feld ( "Anfangsdatum:", "anfangsdatum", "", "10", 'anfangsdatum', '' );
		$form->text_feld ( "Enddatum:", "enddatum", "", "10", 'enddatum', '' );
		$form->send_button ( "submit_kostenkonto", "Suchen" );
		$form->hidden_feld ( "option", "kostenkonto_suchen" );
		$form->ende_formular ();
	}
	function form_buchungen_zu_kostenkonto() {
		$f = new formular ();
		$k = new kontenrahmen ();
		$k->dropdown_kontorahmenkonten ( 'Konto auswählen', 'kkk', 'kostenkonto', 'Alle', '', '' );
		$f->text_feld ( "Anfangsdatum:", "anfangsdatum", "", "10", 'anfangsdatum', '' );
		$f->text_feld ( "Enddatum:", "enddatum", "", "10", 'enddatum', '' );
		$f->send_button ( "submit_kostenkonto", "Suchen" );
		$f->hidden_feld ( "option", "buchungen_zu_kostenkonto" );
	}
	function form_kosten_einnahmen() {
		$bg = new berlussimo_global ();
		$link = "?daten=buchen&option=kosten_einnahmen";
		
		/*
		 * if(!isset($_REQUEST['monat'])){
		 * $monat = date("m");
		 * }else{
		 * $monat = $_REQUEST['monat'];
		 * }
		 *
		 * if(!isset($_REQUEST['jahr'])){
		 * $jahr = date("Y");
		 * }else{
		 * $jahr = $_REQUEST['jahr'];
		 * }
		 */
		
		if (! empty ( $_REQUEST [monat] ) && ! empty ( $_REQUEST [jahr] )) {
			if ($_REQUEST [monat] != 'alle') {
				$_SESSION ['monat'] = sprintf ( '%02d', $_REQUEST [monat] );
			} else {
				$_SESSION ['monat'] = $_REQUEST [monat];
			}
			$_SESSION ['jahr'] = $_REQUEST [jahr];
			$jahr = $_SESSION [jahr];
			$monat = $_SESSION [monat];
		}
		if (empty ( $monat ) or empty ( $jahr )) {
			$monat = date ( "m" );
			$jahr = date ( "Y" );
		}
		
		$bg->monate_jahres_links ( $jahr, $link );
		/* PDF LINK */
		echo "<a href=\"?daten=buchen&option=kosten_einnahmen_pdf&monat=$monat&jahr=$jahr\">PDF ÜBERSICHT</a>";
		
		echo "<h1>Block II</h1>";
		$this->kosten_einnahmen ( $monat, $jahr, '4' );
		echo "<hr><h1>Block III</h1>";
		$this->kosten_einnahmen ( $monat, $jahr, '5' );
		echo "<hr><h1>Block V</h1>";
		$this->kosten_einnahmen ( $monat, $jahr, '6' );
		echo "<hr><h1>Block HW</h1>";
		$this->kosten_einnahmen ( $monat, $jahr, '7' );
		echo "<hr><h1>Block GBN</h1>";
		$this->kosten_einnahmen ( $monat, $jahr, '8' );
		echo "<hr><h1>FON</h1>";
		$this->kosten_einnahmen ( $monat, $jahr, '10' );
		echo "<hr><h1>Block E</h1>";
		$this->kosten_einnahmen ( $monat, $jahr, '11' );
	}
	
	/* Funktion für die Kosten und Einnahmenübersicht */
	function kosten_einnahmen($monat, $jahr, $geldkonto_id) {
		$datum_jahresanfang = "01.01.$jahr";
		$this->kontostand_tagesgenau_bis ( $geldkonto_id, $datum_jahresanfang );
		$kontostand_jahresanfang = $this->summe_konto_buchungen;
		
		$this->summe_kontobuchungen_jahr_monat ( $geldkonto_id, '80001', $jahr, $monat );
		$summe_mieteinnahmen_monat = $this->summe_konto_buchungen;
		
		$this->summe_miete_jahr ( $geldkonto_id, '80001', $jahr, $monat );
		$summe_mieteinnahmen_jahr = $this->summe_konto_buchungen;
		
		$this->summe_kosten_jahr_monat ( $geldkonto_id, '80001', $jahr, $monat );
		$summe_kosten_monat = abs ( $this->summe_konto_buchungen );
		
		$this->summe_kosten_jahr ( $geldkonto_id, '80001', $jahr, $monat );
		$summe_kosten_jahr = abs ( $this->summe_konto_buchungen );
		
		if ($monat < 12) {
			$monat_neu = $monat + 1;
			$jahr_neu = $jahr;
		}
		if ($monat == 12) {
			$monat_neu = 1;
			$jahr_neu = $jahr + 1;
		}
		$datum_heute = "01.$monat_neu.$jahr_neu";
		$this->kontostand_tagesgenau_bis ( $geldkonto_id, $datum_heute );
		$kontostand_heute = $this->summe_konto_buchungen;
		
		$monatname = monat2name ( $monat );
		echo "<h3>$monatname $jahr</h3>";
		echo "Kontostand $datum | MietenM| MietenJ| KostenM|KostenJ|Kontostand<br><br>";
		$kontostand_jahresanfang = nummer_punkt2komma ( $kontostand_jahresanfang );
		$summe_mieteinnahmen_monat = nummer_punkt2komma ( $summe_mieteinnahmen_monat );
		$summe_mieteinnahmen_jahr = nummer_punkt2komma ( $summe_mieteinnahmen_jahr );
		$summe_kosten_monat = nummer_punkt2komma ( $summe_kosten_monat );
		$summe_kosten_jahr = nummer_punkt2komma ( $summe_kosten_jahr );
		$kontostand_heute = nummer_punkt2komma ( $kontostand_heute );
		echo "<b>$kontostand_jahresanfang| $summe_mieteinnahmen_monat|$summe_mieteinnahmen_jahr|$summe_kosten_monat|$summe_kosten_jahr|$kontostand_heute</b><br>";
	}
	
	/* erwartet array mit geldkonto_ids und objekt_namen */
	/* array[][] */
	function kosten_einnahmen_pdf($geldkontos_arr, $monat, $jahr) {
		$anzahl_konten = count ( $geldkontos_arr );
		$datum_jahresanfang = "01.01.$jahr";
		if ($anzahl_konten) {
			
			ob_clean (); // ausgabepuffer leeren
			/* PDF AUSGABE */
			//include_once ('pdfclass/class.ezpdf.php');
			$pdf = new Cezpdf ( 'a4', 'portrait' );
			$pdf->selectFont ( 'pdfclass/fonts/Helvetica.afm' );
			$pdf->ezSetCmMargins ( 4.5, 0, 0, 0 );
			/* Kopfzeile */
			$pdf->addJpegFromFile ( 'pdfclass/logo_hv_sw.jpg', 220, 750, 175, 100 );
			$pdf->setLineStyle ( 0.5 );
			$pdf->addText ( 86, 743, 6, "BERLUS HAUSVERWALTUNG * Fontanestr. 1 * 14193 Berlin * Inhaber Wolfgang Wehrheim * Telefon: 89784477 * Fax: 89784479 * Email: info@berlus.de" );
			$pdf->line ( 42, 750, 550, 750 );
			/* Footer */
			$pdf->line ( 42, 50, 550, 50 );
			$pdf->addText ( 170, 42, 6, "BERLUS HAUSVERWALTUNG *  Fontanestr. 1 * 14193 Berlin * Inhaber Wolfgang Wehrheim" );
			$pdf->addText ( 150, 35, 6, "Bankverbindung: Dresdner Bank Berlin * BLZ: 100  800  00 * Konto-Nr.: 05 804 000 00 * Steuernummer: 24/582/61188" );
			$pdf->addInfo ( 'Title', "Monatsbericht $objekt_name $monatname $jahr" );
			$pdf->addInfo ( 'Author', $_SESSION ['username'] );
			$pdf->ezStartPageNumbers ( 100, 760, 8, '', 'Seite {PAGENUM} von {TOTALPAGENUM}', 1 );
			
			$g_kosten_jahr = 0.00;
			
			/* Schleife für jedes Geldkonto bzw. Zeilenausgabe */
			for($a = 0; $a < $anzahl_konten; $a ++) {
				$geldkonto_id = $geldkontos_arr [$a] ['GELDKONTO_ID'];
				$objekt_name = $geldkontos_arr [$a] ['OBJEKT_NAME'];
				
				$this->kontostand_tagesgenau_bis ( $geldkonto_id, $datum_jahresanfang );
				$kontostand_jahresanfang = $this->summe_konto_buchungen;
				$this->summe_kontobuchungen_jahr_monat ( $geldkonto_id, '80001', $jahr, $monat );
				$summe_mieteinnahmen_monat = $this->summe_konto_buchungen;
				$this->summe_miete_jahr ( $geldkonto_id, '80001', $jahr, $monat );
				$summe_mieteinnahmen_jahr = $this->summe_konto_buchungen;
				$this->summe_kosten_jahr_monat ( $geldkonto_id, '80001', $jahr, $monat );
				$summe_kosten_monat = $this->summe_konto_buchungen;
				$this->summe_kosten_jahr ( $geldkonto_id, '80001', $jahr, $monat );
				$summe_kosten_jahr = $this->summe_konto_buchungen;
				
				/*
				 * if($monat < 12){
				 * $monat_neu = $monat + 1;
				 * $jahr_neu = $jahr;
				 * }
				 * if($monat == 12){
				 * $monat_neu = 1;
				 * $jahr_neu = $jahr +1;
				 * }
				 */
				$monat = sprintf ( '%02d', $monat );
				
				$letzter_tag_m = letzter_tag_im_monat ( $monat, $jahr );
				
				$datum_bis = "$letzter_tag_m.$monat.$jahr";
				$this->kontostand_tagesgenau_bis ( $geldkonto_id, $datum_bis );
				$kontostand_heute = $this->summe_konto_buchungen;
				
				$monatname = monat2name ( $monat );
				
				/* Gesamtsummen bilden */
				$g_kontostand_ja = $g_kontostand_ja + $kontostand_jahresanfang;
				$g_me_monat = $g_me_monat + $summe_mieteinnahmen_monat;
				$g_me_jahr = $g_me_jahr + $summe_mieteinnahmen_jahr;
				$g_kosten_monat = $g_kosten_monat + $summe_kosten_monat;
				$g_kosten_jahr += $summe_kosten_jahr;
				$g_kontostand_akt = $g_kontostand_akt + $kontostand_heute;
				
				$kontostand_jahresanfang = nummer_punkt2komma ( $kontostand_jahresanfang );
				$summe_mieteinnahmen_monat = nummer_punkt2komma ( $summe_mieteinnahmen_monat );
				$summe_mieteinnahmen_jahr = nummer_punkt2komma ( $summe_mieteinnahmen_jahr );
				$summe_kosten_monat = nummer_punkt2komma ( $summe_kosten_monat );
				$summe_kosten_jahr = nummer_punkt2komma ( $summe_kosten_jahr );
				$kontostand_heute = nummer_punkt2komma ( $kontostand_heute );
				// echo "<b>$kontostand_jahresanfang| $summe_mieteinnahmen_monat|$summe_mieteinnahmen_jahr|$summe_kosten_monat|$summe_kosten_jahr|$kontostand_heute</b><br>";
				
				$table_arr [$a] [OBJEKT_NAME] = $objekt_name;
				$table_arr [$a] [KONTOSTAND1_1] = $kontostand_jahresanfang;
				$table_arr [$a] [ME_MONAT] = $summe_mieteinnahmen_monat;
				$table_arr [$a] [ME_JAHR] = $summe_mieteinnahmen_jahr;
				$table_arr [$a] [KOSTEN_MONAT] = $summe_kosten_monat;
				$table_arr [$a] [KOSTEN_JAHR] = $summe_kosten_jahr;
				$table_arr [$a] [KONTOSTAND_AKTUELL] = "<b>$kontostand_heute</b>";
			} // end for
			
			/* Summenzeile hinzufügen */
			$table_arr [$a] [OBJEKT_NAME] = "<b>Summe incl. FON</b>";
			$table_arr [$a] [KONTOSTAND1_1] = '<b>' . nummer_punkt2komma ( $g_kontostand_ja ) . '</b>';
			$table_arr [$a] [ME_MONAT] = '<b>' . nummer_punkt2komma ( $g_me_monat ) . '</b>';
			$table_arr [$a] [ME_JAHR] = '<b>' . nummer_punkt2komma ( $g_me_jahr ) . '</b>';
			$table_arr [$a] [KOSTEN_MONAT] = '<b>' . nummer_punkt2komma ( $g_kosten_monat ) . '</b>';
			$table_arr [$a] [KOSTEN_JAHR] = '<b>' . nummer_punkt2komma ( $g_kosten_jahr ) . '</b>';
			$table_arr [$a] [KONTOSTAND_AKTUELL] = '<b>' . nummer_punkt2komma ( $g_kontostand_akt ) . '</b>';
			
			$pdf->ezTable ( $table_arr, array (
					'OBJEKT_NAME' => 'Objekt',
					'KONTOSTAND1_1' => "Kontostand $datum_jahresanfang",
					'ME_MONAT' => "Mieten Einnahmen $monatname",
					'ME_JAHR' => "Mieten Einnahmen $jahr",
					'KOSTEN_MONAT' => "Kosten $monatname",
					'KOSTEN_JAHR' => "Kosten $jahr",
					'KONTOSTAND_AKTUELL' => "Kontostand" 
			), '<b>Kosten & Einnahmen / Objekt (Tabellarische übersicht)</b>', array (
					'shaded' => 0,
					'width' => '500',
					'justification' => 'right',
					'cols' => array (
							'KONTOSTAND1_1' => array (
									'justification' => 'right' 
							),
							'ME_MONAT' => array (
									'justification' => 'right' 
							),
							'ME_MONAT' => array (
									'justification' => 'right' 
							),
							'ME_JAHR' => array (
									'justification' => 'right' 
							),
							'KOSTEN_MONAT' => array (
									'justification' => 'right' 
							),
							'KOSTEN_JAHR' => array (
									'justification' => 'right' 
							),
							'KONTOSTAND_AKTUELL' => array (
									'justification' => 'right' 
							) 
					) 
			) );
			
			ob_clean (); // ausgabepuffer leeren
			header ( "Content-type: application/pdf" ); // wird von MSIE ignoriert
			$pdf->ezStream ();
		} else {
			echo "Keine Daten Error 65922";
		}
	}
	function kontostand_tagesgenau_bis($geldkonto_id, $datum) {
		$datum = date_german2mysql ( $datum );
		$result = mysql_query ( "SELECT SUM(BETRAG) AS SUMME FROM GELD_KONTO_BUCHUNGEN WHERE GELDKONTO_ID='$geldkonto_id' && DATUM<='$datum' && AKTUELL='1'" );
		
		$numrows = mysql_numrows ( $result );
		if ($numrows > 0) {
			$row = mysql_fetch_assoc ( $result );
			if ($row ['SUMME'] == null) {
				$this->summe_konto_buchungen = '0.00';
				return '0.00';
			} else {
				$this->summe_konto_buchungen = $row ['SUMME'];
				return $this->summe_konto_buchungen;
			}
		}
	}
	
	/*
	 * function summe_kontobuchungen_jahr($geldkonto_id, $kostenkonto, $jahr){
	 *
	 * $result = mysql_query ("SELECT SUM(BETRAG) AS SUMME FROM GELD_KONTO_BUCHUNGEN WHERE GELDKONTO_ID='$geldkonto_id' && KONTENRAHMEN_KONTO = '$kostenkonto' && ( DATE_FORMAT( DATUM, '%Y' ) = '$jahr') && AKTUELL='1'");
	 *
	 *
	 * $numrows = mysql_numrows($result);
	 * if($numrows>0){
	 * $this->summe_konto_buchungen = 0;
	 * $row = mysql_fetch_assoc($result);
	 * $this->summe_konto_buchungen = $row['SUMME'];
	 * }else{
	 * $this->summe_konto_buchungen = 0;
	 * #echo "leer";
	 * }
	 * }
	 */
	function summe_kosten_jahr_monat($geldkonto_id, $mieteinnahmen_kostenkonto, $jahr, $monat) {
		$result = mysql_query ( "SELECT SUM(BETRAG) AS SUMME FROM GELD_KONTO_BUCHUNGEN WHERE GELDKONTO_ID='$geldkonto_id' && KONTENRAHMEN_KONTO != '$mieteinnahmen_kostenkonto' && ( DATE_FORMAT( DATUM, '%Y-%m' ) = '$jahr-$monat') && AKTUELL='1'" );
		
		$numrows = mysql_numrows ( $result );
		if ($numrows > 0) {
			$this->summe_konto_buchungen = 0;
			$row = mysql_fetch_assoc ( $result );
			$this->summe_konto_buchungen = $row ['SUMME'];
		} else {
			$this->summe_konto_buchungen = 0;
			// echo "leer";
		}
	}
	function summe_buchungen_kostenkonto_bis_heute($geldkonto_id, $kostenkonto, $kos_typ, $kos_id) {
		$result = mysql_query ( "SELECT SUM(BETRAG) AS SUMME FROM GELD_KONTO_BUCHUNGEN WHERE GELDKONTO_ID='$geldkonto_id' && KONTENRAHMEN_KONTO = '$kostenkonto' && ( DATE_FORMAT( DATUM, '%Y-%m' ) <= CURDATE()) && AKTUELL='1' && KOSTENTRAEGER_TYP='$kos_typ' && KOSTENTRAEGER_ID='$kos_id'" );
		
		$numrows = mysql_numrows ( $result );
		if ($numrows > 0) {
			$this->summe_konto_buchungen = 0;
			$row = mysql_fetch_assoc ( $result );
			$this->summe_konto_buchungen = $row ['SUMME'];
		} else {
			$this->summe_konto_buchungen = 0;
			// echo "leer";
		}
	}
	function summe_kosten_jahr($geldkonto_id, $mieteinnahmen_kostenkonto, $jahr, $monat) {
		$ltag = letzter_tag_im_monat ( $monat, $jahr );
		$result = mysql_query ( "SELECT SUM(BETRAG) AS SUMME FROM GELD_KONTO_BUCHUNGEN WHERE GELDKONTO_ID='$geldkonto_id' && KONTENRAHMEN_KONTO != '$mieteinnahmen_kostenkonto' && DATUM BETWEEN '$jahr-01-01' AND '$jahr-$monat-$ltag' && AKTUELL='1'" );
		
		$numrows = mysql_numrows ( $result );
		if ($numrows > 0) {
			$this->summe_konto_buchungen = 0;
			$row = mysql_fetch_assoc ( $result );
			$this->summe_konto_buchungen = $row ['SUMME'];
		} else {
			$this->summe_konto_buchungen = 0;
			// echo "leer";
		}
	}
	function summe_miete_jahr($geldkonto_id, $mieteinnahmen_kostenkonto, $jahr, $monat) {
		$ltag = letzter_tag_im_monat ( $monat, $jahr );
		$result = mysql_query ( "SELECT SUM(BETRAG) AS SUMME FROM GELD_KONTO_BUCHUNGEN WHERE GELDKONTO_ID='$geldkonto_id' && KONTENRAHMEN_KONTO = '$mieteinnahmen_kostenkonto' && DATUM BETWEEN '$jahr-01-01' AND '$jahr-$monat-$ltag' && AKTUELL='1'" );
		
		$numrows = mysql_numrows ( $result );
		if ($numrows > 0) {
			$this->summe_konto_buchungen = 0;
			$row = mysql_fetch_assoc ( $result );
			$this->summe_konto_buchungen = $row ['SUMME'];
		} else {
			$this->summe_konto_buchungen = 0;
			// echo "leer";
		}
	}
	function check_buchung($gk_id, $betrag, $auszug, $datum) {
		$result = mysql_query ( "SELECT * 
FROM  `GELD_KONTO_BUCHUNGEN` 
WHERE  `KONTO_AUSZUGSNUMMER` =  '$auszug'
AND  `BETRAG` =  '$betrag'
AND  `GELDKONTO_ID` =  '$gk_id'
AND  `DATUM` =  '$datum'
AND  `AKTUELL` =  '1'
LIMIT 0 , 1" );
		// echo "SELECT * FROM `SEPA_MANDATE` WHERE `M_REFERENZ` = '$mref' AND M_EDATUM='9999-12-31' AND `AKTUELL` = '1' LIMIT 0 , 1";
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			return true;
		}
	}
	
	/* Unabhängig vom Geldkonto */
	function finde_buchungen_zu_kostenkonto($kostenkonto, $anfangsdatum, $enddatum) {
		$result = mysql_query ( "SELECT * FROM GELD_KONTO_BUCHUNGEN WHERE  KONTENRAHMEN_KONTO = '$kostenkonto' && DATUM BETWEEN '$anfangsdatum' AND '$enddatum' && AKTUELL='1' ORDER BY GELDKONTO_ID, BETRAG, DATUM, KOSTENTRAEGER_TYP, KOSTENTRAEGER_ID ASC" );
		
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			$anfangsdatum = date_mysql2german ( $anfangsdatum );
			$enddatum = date_mysql2german ( $enddatum );
			$k = new kontenrahmen ();
			$k->konto_informationen ( $kostenkonto );
			echo "<table>";
			
			echo "<tr class=\"feldernamen\"><td colspan=6><b>Alle Buchungen zu $kostenkonto vom $anfangsdatum bis $enddatum<br>$kostenkonto $k->konto_bezeichnung</b></td></tr>";
			echo "<tr class=\"feldernamen\"><td>Geldkonto</td><td>Kostenträger</td><td>Datum</td><td>Buchungsnr</td><td>Betrag</td><td width=70%>Vzweck</td></tr>";
			$g = new geldkonto_info ();
			while ( $row = mysql_fetch_assoc ( $result ) ) {
				$datum = date_mysql2german ( $row [DATUM] );
				$auszugsnr = $row [KONTO_AUSZUGSNUMMER];
				$g_buchungsnummer = $row [G_BUCHUNGSNUMMER];
				$erfass_nr = $row [ERFASS_NR];
				$betrag = nummer_punkt2komma ( $row [BETRAG] );
				$vzweck = $row [VERWENDUNGSZWECK];
				$kos_typ = $row [KOSTENTRAEGER_TYP];
				$kos_id = $row [KOSTENTRAEGER_ID];
				$geldkonto_id = $row [GELDKONTO_ID];
				$g->geld_konto_details ( $geldkonto_id );
				$r = new rechnung ();
				$kostentraeger_bezeichnung = $r->kostentraeger_ermitteln ( $kos_typ, $kos_id );
				
				echo "<tr><td>$g->geldkonto_bezeichnung<td>$kostentraeger_bezeichnung</td><td>$datum</td><td><b>$g_buchungsnummer</b></td><td>$betrag</td><td>$vzweck</td></tr>";
			}
			
			echo "</table><br>";
		} else {
			echo "Keine Buchungen zu Kostenkonto $kostenkonto $k->konto_bezeichnung<br>";
		}
	} // end function
	function kontobuchungen_anzeigen_dyn($geldkonto_id, $kostenkonto, $anfangsdatum, $enddatum, $kostentraeger_typ, $kostentraeger_bez) {
		$anfangsdatum = date_german2mysql ( $anfangsdatum );
		$enddatum = date_german2mysql ( $enddatum );
		$kostentraeger_id = $this->kostentraeger_id_ermitteln ( $kostentraeger_typ, $kostentraeger_bez );
		if (empty ( $kostentraeger_id )) {
			$result = mysql_query ( "SELECT * FROM GELD_KONTO_BUCHUNGEN WHERE GELDKONTO_ID='$geldkonto_id' && KONTENRAHMEN_KONTO = '$kostenkonto' && DATUM BETWEEN '$anfangsdatum' AND '$enddatum' && AKTUELL='1' ORDER BY KOSTENTRAEGER_TYP, KOSTENTRAEGER_ID ASC" );
		} else {
			$result = mysql_query ( "SELECT * FROM GELD_KONTO_BUCHUNGEN WHERE GELDKONTO_ID='$geldkonto_id' && KONTENRAHMEN_KONTO = '$kostenkonto' && DATUM BETWEEN '$anfangsdatum' AND '$enddatum' && AKTUELL='1' && KOSTENTRAEGER_TYP='$kostentraeger_typ' && KOSTENTRAEGER_ID='$kostentraeger_id' ORDER BY DATUM ASC" );
		}
		$numrows = mysql_numrows ( $result );
		if ($numrows > 0) {
			$anfangsdatum = date_mysql2german ( $anfangsdatum );
			$enddatum = date_mysql2german ( $enddatum );
			$k = new kontenrahmen ();
			$k->konto_informationen ( $kostenkonto );
			echo "<table>";
			if (empty ( $kostentraeger_id )) {
				echo "<tr class=\"feldernamen\"><td colspan=5><b>Alle Kostenträger vom $anfangsdatum bis $enddatum<br>$kostenkonto $k->konto_bezeichnung</b></td></tr>";
				echo "<tr><td>Kostenträger</td><td>Datum</td><td>Erfassnr</td><td>Betrag</td><td width=70%>Vzweck</td></tr>";
			} else {
				echo "<tr class=\"feldernamen\"><td colspan=5><b>Kostenträger $kostentraeger_bez vom $anfangsdatum bis $enddatum<br>$kostenkonto $k->konto_bezeichnung</b></td></tr>";
				echo "<tr><td>Datum</td><td>Erfassnr</td><td>Betrag</td><td width=70%>Vzweck</td></tr>";
			}
			while ( $row = mysql_fetch_assoc ( $result ) ) {
				$datum = date_mysql2german ( $row [DATUM] );
				$auszugsnr = $row [KONTO_AUSZUGSNUMMER];
				$erfass_nr = $row [ERFASS_NR];
				$betrag = nummer_punkt2komma ( $row [BETRAG] );
				$vzweck = $row [VERWENDUNGSZWECK];
				$kos_typ = $row [KOSTENTRAEGER_TYP];
				$kos_id = $row [KOSTENTRAEGER_ID];
				$r = new rechnung ();
				$kostentraeger_bezeichnung = $r->kostentraeger_ermitteln ( $kos_typ, $kos_id );
				$this->summe_kontobuchungen_dyn ( $geldkonto_id, $kostenkonto, $anfangsdatum, $enddatum, $kostentraeger_typ, $kostentraeger_bez );
				if (empty ( $kostentraeger_id )) {
					echo "<tr><td>$kostentraeger_bezeichnung</td><td>$datum</td><td>$erfass_nr</td><td>$betrag</td><td>$vzweck</td></tr>";
				} else {
					echo "<tr><td>$datum</td><td>$erfass_nr</td><td>$betrag</td><td>$vzweck</td></tr>";
				}
			}
			$this->summe_konto_buchungen = nummer_punkt2komma ( $this->summe_konto_buchungen );
			if (empty ( $kostentraeger_id )) {
				echo "<tr class=\"feldernamen\"><td></td><td></td><td><b>Summe</b></td><td><b>$this->summe_konto_buchungen €</b></td><td></td></tr>";
			} else {
				echo "<tr class=\"feldernamen\"><td></td><td><b>Summe</b></td><td><b>$this->summe_konto_buchungen €</b></td><td></td></tr>";
			}
			echo "</table><br>";
		} else {
			echo "Geldkonto $geldkonto_id - Kostenkonto $kostenkonto leer<br>";
		}
	}
	function summe_kontobuchungen_dyn($geldkonto_id, $kostenkonto, $anfangsdatum, $enddatum, $kostentraeger_typ, $kostentraeger_bez) {
		$anfangsdatum = date_german2mysql ( $anfangsdatum );
		$enddatum = date_german2mysql ( $enddatum );
		$kostentraeger_id = $this->kostentraeger_id_ermitteln ( $kostentraeger_typ, $kostentraeger_bez );
		if (empty ( $kostentraeger_id )) {
			$result = mysql_query ( "SELECT SUM(BETRAG) AS SUMME FROM GELD_KONTO_BUCHUNGEN WHERE GELDKONTO_ID='$geldkonto_id' && KONTENRAHMEN_KONTO = '$kostenkonto' && DATUM BETWEEN '$anfangsdatum' AND '$enddatum' && AKTUELL='1'" );
		} else {
			$result = mysql_query ( "SELECT SUM(BETRAG) AS SUMME FROM GELD_KONTO_BUCHUNGEN WHERE GELDKONTO_ID='$geldkonto_id' && KONTENRAHMEN_KONTO = '$kostenkonto' && DATUM BETWEEN '$anfangsdatum' AND '$enddatum' && AKTUELL='1' && KOSTENTRAEGER_TYP='$kostentraeger_typ' && KOSTENTRAEGER_ID='$kostentraeger_id'" );
		}
		$numrows = mysql_numrows ( $result );
		if ($numrows > 0) {
			$this->summe_konto_buchungen = 0;
			$row = mysql_fetch_assoc ( $result );
			$this->summe_konto_buchungen = $row ['SUMME'];
		} else {
			$this->summe_konto_buchungen = 0;
			// echo "leer";
		}
	}
	function summe_kontobuchungen_dyn2($geldkonto_id, $kostenkonto, $anfangsdatum, $enddatum, $kostentraeger_typ, $kostentraeger_id) {
		$result = mysql_query ( "SELECT SUM(BETRAG) AS SUMME FROM GELD_KONTO_BUCHUNGEN WHERE GELDKONTO_ID='$geldkonto_id' && KONTENRAHMEN_KONTO = '$kostenkonto' && DATUM BETWEEN '$anfangsdatum' AND '$enddatum' && AKTUELL='1' && KOSTENTRAEGER_TYP='$kostentraeger_typ' && KOSTENTRAEGER_ID='$kostentraeger_id'" );
		
		$numrows = mysql_numrows ( $result );
		if ($numrows > 0) {
			$this->summe_konto_buchungen = 0;
			$row = mysql_fetch_assoc ( $result );
			$this->summe_konto_buchungen = $row ['SUMME'];
			return $row ['SUMME'];
		} else {
			$this->summe_konto_buchungen = 0;
			// echo "leer";
		}
	}
	function summe_kontobuchungen_dyn3($geldkonto_id, $monat, $jahr, $bet = null) {
		if ($bet == null) {
			$result = mysql_query ( "SELECT SUM(BETRAG) AS SUMME FROM GELD_KONTO_BUCHUNGEN WHERE GELDKONTO_ID='$geldkonto_id' && DATE_FORMAT(DATUM,'%Y-%m')='$jahr-$monat' && AKTUELL='1'" );
		}
		if ($bet == '-') {
			$result = mysql_query ( "SELECT SUM(BETRAG) AS SUMME FROM GELD_KONTO_BUCHUNGEN WHERE GELDKONTO_ID='$geldkonto_id' && DATE_FORMAT(DATUM,'%Y-%m')='$jahr-$monat' && AKTUELL='1' && BETRAG<0" );
		}
		
		if ($bet == '+') {
			$result = mysql_query ( "SELECT SUM(BETRAG) AS SUMME FROM GELD_KONTO_BUCHUNGEN WHERE GELDKONTO_ID='$geldkonto_id' && DATE_FORMAT(DATUM,'%Y-%m')='$jahr-$monat' && AKTUELL='1' && BETRAG>0" );
		}
		
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			$row = mysql_fetch_assoc ( $result );
			return number_format ( $row ['SUMME'], 2, '.', '' );
		} else {
			return number_format ( '0.00', 2, '.', '' );
		}
	}
	function monatsbericht_ohne_ausgezogene() {
		
		// echo "Monatsbericht Mieter - Monatsbericht Kostenkonten<br>";
		echo "<h3>Aktuelle Mieterstatistik ohne ausgezogene Mieter<br></h3>";
		$s = new statistik ();
		// $jahr_monat = date("Y-m");
		
		$jahr = $_REQUEST ['jahr'];
		if (empty ( $jahr )) {
			$jahr = date ( "Y" );
		} else {
			if (strlen ( $jahr ) < 4) {
				$jahr = date ( "Y" );
			}
		}
		
		$monat = $_REQUEST ['monat'];
		if (empty ( $monat )) {
			$monat = date ( "m" );
		} else {
			if (strlen ( $monat ) < 2) {
				$monat = '0' . $monat;
			}
		}
		
		// $monat = '04';
		$jahr_monat = $jahr . '-' . $monat;
		// $jahr_vormonat = mktime(0, 0, 0, date("m")-1, date("d"), date("Y"));
		// $jahr_vormonat = date("Y-m",$jahr_vormonat);
		$bg = new berlussimo_global ();
		$link = "?daten=buchen&option=monatsbericht_o_a";
		$bg->objekt_auswahl_liste ( $link );
		$bg->monate_jahres_links ( $jahr, $link );
		if (isset ( $_SESSION ['objekt_id'] )) {
			$objekt_id = $_SESSION ['objekt_id'];
			$einheit_info = new einheit ();
			$o = new objekt ();
			$objekt_name = $o->get_objekt_name ( $objekt_id );
			
			/* Aktuell bzw. gewünschten Monat berechnen */
			// $einheiten_array = $s->vermietete_monat_jahr($jahr_monat,$objekt_id, '');
			$o = new objekt ();
			$einheiten_array = $o->einheiten_objekt_arr ( $objekt_id );
			
			// echo '<pre>';
			// print_r($einheiten_array);
			// die();
			$anzahl_aktuell = count ( $einheiten_array );
			/* PDF */
			
			$zaehler = 0;
			
			//include_once ('pdfclass/class.ezpdf.php');
			include_once ('classes/class_bpdf.php');
			$pdf = new Cezpdf ( 'a4', 'portrait' );
			$bpdf = new b_pdf ();
			$bpdf->b_header ( $pdf, 'Partner', $_SESSION ['partner_id'], 'portrait', 'pdfclass/fonts/Helvetica.afm', 6 );
			$datum_heute = date ( "d.m.Y" );
			$p = new partners ();
			$p->get_partner_info ( $_SESSION ['partner_id'] );
			$pdf->addText ( 475, 700, 8, "$p->partner_ort, $datum_heute" );
			// $pdf->ezText("$p->partner_ort, $datum_heute",12, array('justification'=>'right'));
			$this->footer_zahlungshinweis = $bpdf->zahlungshinweis;
			
			$monatname = monat2name ( $monat );
			$pdf->addInfo ( 'Title', "Monatsbericht $objekt_name $monatname $jahr" );
			$pdf->addInfo ( 'Author', $_SESSION ['username'] );
			
			$summe_sv = 0;
			$summe_mieten = 0;
			$summe_umlagen = 0;
			$summe_akt_gsoll = 0;
			$summe_g_zahlungen = 0;
			$summe_saldo_neu = 0;
			
			for($i = 0; $i < $anzahl_aktuell; $i ++) {
				$miete = new miete ();
				$einheit_info->get_mietvertraege_zu ( "" . $einheiten_array [$i] ['EINHEIT_ID'] . "", $jahr, $monat );
				
				$zaehler ++;
				/* Wenn vermietet */
				if (isset ( $einheit_info->mietvertrag_id )) {
					$mv = new mietvertraege ();
					$mv->get_mietvertrag_infos_aktuell ( $einheit_info->mietvertrag_id );
					$mk = new mietkonto ();
					$mieter_ids = $mk->get_personen_ids_mietvertrag ( $einheit_info->mietvertrag_id );
					for($a = 0; $a < count ( $mieter_ids ); $a ++) {
						$mieter_daten_arr [] = $mk->get_person_infos ( $mieter_ids [$a] ['PERSON_MIETVERTRAG_PERSON_ID'] );
					}
					$miete->mietkonto_berechnung_monatsgenau ( $einheit_info->mietvertrag_id, $jahr, $monat );
					// $a = new miete;
					// $a->mietkonto_berechnung($einheit_info->mietvertrag_id);
					
					// $miete->mietkonto_berechnung($einheit_info->mietvertrag_id);
					// $miete->mietkonto_berechnen($einheit_info->mietvertrag_id, $jahr, $monat);
					$zeile = $zeile + 1;
					$einheit_id = $einheiten_array [$i] ['EINHEIT_ID'];
					
					$einheit_kurzname = $einheiten_array [$i] ['EINHEIT_KURZNAME'];
					$vn = RTRIM ( LTRIM ( $mieter_daten_arr ['0'] ['0'] ['PERSON_VORNAME'] ) );
					$nn = RTRIM ( LTRIM ( $mieter_daten_arr ['0'] ['0'] ['PERSON_NACHNAME'] ) );
					$akt_gesamt_soll = $miete->sollmiete_warm - $miete->saldo_vormonat_stand;
					$nn = $this->umlaute_anpassen ( $nn );
					$vn = $this->umlaute_anpassen ( $vn );
					
					$tab_arr [$i] ['EINHEIT'] = $einheit_kurzname;
					/* Umbruch wenn Nachname und Vorname zu lang */
					$nname_lang = strlen ( $nn );
					$vname_lang = strlen ( $vn );
					$tab_arr [$i] ['MIETER'] = "$mv->personen_name_string_u";
					
					/* Kommazahlen für die Ausgabe im PDF */
					$miete->saldo_vormonat_stand_a = nummer_punkt2komma ( $miete->saldo_vormonat_stand );
					$miete->sollmiete_warm_a = nummer_punkt2komma ( $miete->sollmiete_warm );
					$miete->davon_umlagen_a = nummer_punkt2komma ( $miete->davon_umlagen );
					$akt_gesamt_soll_a = nummer_punkt2komma ( $akt_gesamt_soll );
					$miete->geleistete_zahlungen_a = nummer_punkt2komma ( $miete->geleistete_zahlungen );
					$miete->erg_a = nummer_punkt2komma ( $miete->erg );
					
					$tab_arr [$i] ['SALDO_VM'] = "$miete->saldo_vormonat_stand_a";
					$tab_arr [$i] ['SOLL_WM'] = "$miete->sollmiete_warm_a";
					$tab_arr [$i] ['UMLAGEN'] = "$miete->davon_umlagen_a";
					$tab_arr [$i] ['G_SOLL_AKT'] = "$akt_gesamt_soll_a";
					$tab_arr [$i] ['ZAHLUNGEN'] = "$miete->geleistete_zahlungen_a";
					$tab_arr [$i] ['ERG'] = "$miete->erg_a";
					
					$ee = new einheit ();
					$ee->get_einheit_info ( $einheit_id );
					$dd = new detail ();
					$optiert = $dd->finde_detail_inhalt ( 'OBJEKT', $_SESSION ['objekt_id'], 'Optiert' );
					// echo $_SESSION['objekt_id'];
					// die($optiert);
					if ($optiert == 'JA') {
						if ($ee->typ == 'Gewerbe') {
							$tab_arr [$i] ['MWST'] = nummer_punkt2komma ( $miete->geleistete_zahlungen - ($miete->geleistete_zahlungen / 1.19) );
							$summe_mwst = $summe_mwst + $miete->geleistete_zahlungen - ($miete->geleistete_zahlungen / 1.19);
						} else {
							$tab_arr [$i] ['MWST'] = '';
						}
					}
					
					$summe_sv = $summe_sv + $miete->saldo_vormonat_stand;
					$summe_mieten = $summe_mieten + $miete->sollmiete_warm;
					$summe_umlagen = $summe_umlagen + $miete->davon_umlagen;
					$summe_akt_gsoll = $summe_akt_gsoll + $akt_gesamt_soll;
					$summe_g_zahlungen = $summe_g_zahlungen + $miete->geleistete_zahlungen;
					$summe_saldo_neu = $summe_saldo_neu + $miete->erg;
					
					/* Leerstand */
				} else {
					$einheit_kurzname = $einheiten_array [$i] ['EINHEIT_KURZNAME'];
					$tab_arr [$i] ['EINHEIT'] = "<b>$einheit_kurzname</b>";
					$tab_arr [$i] ['MIETER'] = "<b>Leerstand</b>";
				}
			}
			unset ( $miete );
			unset ( $mieter_daten_arr );
			unset ( $mk );
			unset ( $nn );
			unset ( $vn );
			
			unset ( $einheiten_array );
			// $pdf->ezStopPageNumbers();
			
			/* Summen */
			$tab_arr [$i + 1] ['MIETER'] = '<b>SUMMEN</b>';
			$tab_arr [$i + 1] ['SALDO_VM'] = '<b>' . nummer_punkt2komma ( $summe_sv ) . '</b>';
			$tab_arr [$i + 1] ['SOLL_WM'] = '<b>' . nummer_punkt2komma ( $summe_mieten ) . '</b>';
			$tab_arr [$i + 1] ['UMLAGEN'] = '<b>' . nummer_punkt2komma ( $summe_umlagen ) . '</b>';
			$tab_arr [$i + 1] ['G_SOLL_AKT'] = '<b>' . nummer_punkt2komma ( $summe_akt_gsoll ) . '</b>';
			$tab_arr [$i + 1] ['ZAHLUNGEN'] = '<b>' . nummer_punkt2komma ( $summe_g_zahlungen ) . '</b>';
			$tab_arr [$i + 1] ['ERG'] = '<b>' . nummer_punkt2komma ( $summe_saldo_neu ) . '</b>';
			$tab_arr [$i + 1] ['MWST'] = '<b>' . nummer_punkt2komma ( $summe_mwst ) . '</b>';
			
			$cols = array (
					'EINHEIT' => "<b>EINHEIT</b>",
					'MIETER' => "<b>MIETER</b>",
					'SALDO_VM' => "<b>SALDO\nVORMONAT</b>",
					'SOLL_WM' => "<b>SOLL\nMIETE\nWARM</b>",
					'UMLAGEN' => "<b>DAVON\nUMLAGEN</b>",
					'G_SOLL_AKT' => "<b>GESAMT\nSOLL\nAKTUELL</b>",
					'ZAHLUNGEN' => "<b>GELEISTETE\nZAHLUNGEN</b>",
					'MWST' => "<b>DAVON\nMWST</b>",
					'ERG' => "<b>SALDO\nNEU</b>" 
			);
			$pdf->ezSetDy ( - 10 );
			
			$pdf->ezTable ( $tab_arr, $cols, "Monatsbericht ohne Auszug - Objekt:$objekt_name - $monatname $jahr", array (
					'showHeadings' => 1,
					'shaded' => 1,
					'titleFontSize' => 10,
					'fontSize' => 8,
					'xPos' => 50,
					'xOrientation' => 'right',
					'width' => 500,
					'rowGap' => 1,
					'cols' => array (
							'EINHEIT' => array (
									'justification' => 'left',
									'width' => 50 
							),
							'SALDO_VM' => array (
									'justification' => 'right',
									'width' => 60 
							),
							'UMLAGEN' => array (
									'justification' => 'right',
									'width' => 55 
							),
							'G_SOLL_AKT' => array (
									'justification' => 'right',
									'width' => 50 
							),
							'ZAHLUNGEN' => array (
									'justification' => 'right',
									'width' => 65 
							),
							'MWST' => array (
									'justification' => 'right' 
							),
							'ERG' => array (
									'justification' => 'right',
									'width' => 50 
							) 
					) 
			) );
			$pdf->ezStopPageNumbers (); // seitennummerirung beenden
			$content = $pdf->ezOutput ();
			
			$dateiname_org = $objekt_name . '-Monatsbericht_o_Auszug_';
			$dateiname = $this->save_file ( $dateiname_org, 'Monatsberichte', $objekt_id, $content, $monat, $jahr );
			/* Falls kein Objekt ausgewählt */
			
			// weiterleiten($dateiname);
			$download_link = "<a href=\"$dateiname\">Monatsbericht $objekt_name für $monat/$jahr HIER</a>";
			hinweis_ausgeben ( "Monatsbericht ohne Vormieter für $objekt_name wurde erstellt<br>" );
			echo $download_link;
			
			// $pdf->ezTable($tab_arr);
			// ob_clean(); //ausgabepuffer leeren
			// $pdf->ezStream();
		} else {
			echo "Objekt auswählen";
		}
	}
	function monatsbericht_mit_ausgezogenen() {
		//include_once ('pdfclass/class.ezpdf.php');
		echo "Monatsbericht Mieter - Monatsbericht Kostenkonten<br>";
		echo "<h3>Aktuelle Mieterstatistik mit ausgezogenen Mietern<br></h3>";
		$s = new statistik ();
		if (isset ( $_REQUEST ['jahr'] )) {
			$jahr = $_REQUEST ['jahr'];
		}
		if (empty ( $jahr )) {
			$jahr = date ( "Y" );
		} else {
			if (strlen ( $jahr ) < 4) {
				$jahr = date ( "Y" );
			}
		}
		
		// $jahr_monat = date("Y-m");
		// $jahr = date("Y");
		if (isset ( $_REQUEST ['monat'] )) {
			$monat = $_REQUEST [monat];
		}
		if (empty ( $monat )) {
			$monat = date ( "m" );
		} else {
			if (strlen ( $monat ) < 2) {
				$monat = '0' . $monat;
			}
		}
		// $monat = '04';
		$jahr_monat = $jahr . '-' . $monat;
		// $jahr_vormonat = mktime(0, 0, 0, date("m")-1, date("d"), date("Y"));
		// $jahr_vormonat = date("Y-m",$jahr_vormonat);
		$bg = new berlussimo_global ();
		$link = "?daten=buchen&option=monatsbericht_m_a";
		$bg->objekt_auswahl_liste ( $link );
		$bg->monate_jahres_links ( $jahr, $link );
		if (isset ( $_SESSION ['objekt_id'] )) {
			$objekt_id = $_SESSION ['objekt_id'];
			$einheit_info = new einheit ();
			$o = new objekt ();
			$objekt_name = $o->get_objekt_name ( $objekt_id );
			/* Aktuell bzw. gewünschten Monat berechnen */
			$ob = new objekt ();
			
			$einheiten_array = $ob->einheiten_objekt_arr ( $objekt_id );
			// $einheiten_array = $s->vermietete_monat_jahr($jahr_monat,$objekt_id, '');
			
			/*
			 * echo "<pre>";
			 * print_r($einheiten_array);
			 * echo "<h1> EINHEITEN: $anzahl_aktuell</h1>";
			 * $mv_array = $einheit_info->get_mietvertrag_ids('7');
			 * print_r($mv_array);
			 */
			
			// PDF#
			$zaehler = 0;
			
			//include_once ('pdfclass/class.ezpdf.php');
			include_once ('classes/class_bpdf.php');
			$pdf = new Cezpdf ( 'a4', 'portrait' );
			$bpdf = new b_pdf ();
			$bpdf->b_header ( $pdf, 'Partner', $_SESSION ['partner_id'], 'portrait', 'pdfclass/fonts/Helvetica.afm', 6 );
			$datum_heute = date ( "d.m.Y" );
			$p = new partners ();
			$p->get_partner_info ( $_SESSION ['partner_id'] );
			$pdf->addText ( 475, 700, 8, "$p->partner_ort, $datum_heute" );
			// $pdf->ezText("$p->partner_ort, $datum_heute",12, array('justification'=>'right'));
			$this->footer_zahlungshinweis = $bpdf->zahlungshinweis;
			
			$monatname = monat2name ( $monat );
			$pdf->addInfo ( 'Title', "Monatsbericht $objekt_name $monatname $jahr" );
			$pdf->addInfo ( 'Author', $_SESSION ['username'] );
			
			$summe_sv = 0;
			$summe_mieten = 0;
			$summe_umlagen = 0;
			$summe_akt_gsoll = 0;
			$summe_g_zahlungen = 0;
			$summe_saldo_neu = 0;
			$summe_mwst = 0;
			
			$anzahl_aktuell = count ( $einheiten_array );
			$anz_tab = 0;
			for($i = 0; $i < $anzahl_aktuell; $i ++) {
				
				$miete = new miete ();
				$mv_array = $einheit_info->get_mietvertraege_bis ( "" . $einheiten_array [$i] ['EINHEIT_ID'] . "", $jahr, $monat );
				$mv_anzahl = count ( $mv_array );
				
				if (is_array ( $mv_array )) {
					$zeile = 0;
					for($b = 0; $b < $mv_anzahl; $b ++) {
						$mv_id = $mv_array [$b] ['MIETVERTRAG_ID'];
						
						$mk = new mietkonto ();
						
						$mv = new mietvertraege ();
						$mv->get_mietvertrag_infos_aktuell ( $mv_id );
						
						$tab_arr [$i] ['MV_ID'] = $mv_id;
						$miete->mietkonto_berechnung_monatsgenau ( $mv_id, $jahr, $monat );
						$zeile = $zeile + 1;
						
						if ($mv->mietvertrag_aktuell == '1') {
							$tab_arr [$anz_tab] ['MIETER'] = $mv->personen_name_string_u;
							$tab_arr [$anz_tab] ['EINHEIT'] = $mv->einheit_kurzname;
						} else {
							$tab_arr [$anz_tab] ['MIETER'] = "<b>$mv->personen_name_string_u</b>";
							$tab_arr [$anz_tab] ['EINHEIT'] = "<b>$mv->einheit_kurzname</b>";
						}
						// $tab_arr[$anz_tab]['E_TYP'] = $mv->einheit_typ;
						// $tab_arr[$anz_tab]['VON'] = $mv->mietvertrag_von_d;
						// $tab_arr[$anz_tab]['BIS'] = $mv->mietvertrag_bis_d;
						$tab_arr [$anz_tab] ['SALDO_VM'] = nummer_punkt2komma_t ( $miete->saldo_vormonat_stand );
						$tab_arr [$anz_tab] ['G_SOLL_AKT'] = nummer_punkt2komma_t ( $miete->saldo_vormonat_stand + $miete->sollmiete_warm );
						$tab_arr [$anz_tab] ['SOLL_WM'] = nummer_punkt2komma_t ( $miete->sollmiete_warm );
						$tab_arr [$anz_tab] ['UMLAGEN'] = nummer_punkt2komma_t ( $miete->davon_umlagen );
						$tab_arr [$anz_tab] ['ZAHLUNGEN'] = nummer_punkt2komma_t ( $miete->geleistete_zahlungen );
						if ($mv->einheit_typ == 'Gewerbe') {
							$tab_arr [$anz_tab] ['MWST'] = nummer_punkt2komma_t ( $miete->geleistete_zahlungen / 119 * 19 );
							$summe_mwst += $miete->geleistete_zahlungen / 119 * 19;
						} else {
							$tab_arr [$anz_tab] ['MWST'] = nummer_punkt2komma_t ( 0 );
						}
						
						$tab_arr [$anz_tab] ['ERG'] = nummer_punkt2komma_t ( $miete->erg );
						$anz_tab ++;
						
						$akt_gesamt_soll = $miete->saldo_vormonat_stand + $miete->sollmiete_warm;
						echo "$zeile. $einheit_kurzname $mv->personen_name_string_u Saldo: VM: $miete->saldo_vormonat_stand € WM: $miete->sollmiete_warm € UM: $miete->davon_umlagen GSOLL: $akt_gesamt_soll € SALDO NEU:$miete->erg €<br>";
						
						$summe_sv = $summe_sv + $miete->saldo_vormonat_stand;
						$summe_mieten = $summe_mieten + $miete->sollmiete_warm;
						$summe_umlagen = $summe_umlagen + $miete->davon_umlagen;
						$summe_akt_gsoll = $summe_akt_gsoll + $akt_gesamt_soll;
						$summe_g_zahlungen = $summe_g_zahlungen + $miete->geleistete_zahlungen;
						$summe_saldo_neu = $summe_saldo_neu + $miete->erg;
						$zaehler ++;
						
						unset ( $mieter_daten_arr );
					} // end if is_array mv_ids
				}
			}
			/* Ausgabe der Summen */
			$pdf->ezSetDy ( - 15 ); // abstand
			                    // $pdf->ezText("Summen: $summe_sv",10, array('left'=>'150'));
			                    // $pdf->ezText("$summe_mieten",10, array('left'=>'250'));
			                    // $pdf->ezText("$summe_umlagen",10, array('left'=>'300'));
			                    // $pdf->ezText("$summe_akt_gsoll",10, array('left'=>'350'));
			                    // $pdf->ezText("$summe_g_zahlungen",10, array('left'=>'400'));
			                    // $pdf->ezText("$summe_saldo_neu",10, array('left'=>'450'));
			
			$anz_l = count ( $tab_arr );
			$tab_arr [$anz_l] ['SALDO_VM'] = nummer_punkt2komma_t ( $summe_sv );
			$tab_arr [$anz_l] ['SOLL_WM'] = nummer_punkt2komma_t ( $summe_mieten );
			$tab_arr [$anz_l] ['UMLAGEN'] = nummer_punkt2komma_t ( $summe_umlagen );
			$tab_arr [$anz_l] ['G_SOLL_AKT'] = nummer_punkt2komma_t ( $summe_akt_gsoll );
			$tab_arr [$anz_l] ['ZAHLUNGEN'] = nummer_punkt2komma_t ( $summe_g_zahlungen );
			$tab_arr [$anz_l] ['MWST'] = nummer_punkt2komma_t ( $summe_mwst );
			$tab_arr [$anz_l] ['ERG'] = nummer_punkt2komma_t ( $summe_saldo_neu );
			
			// $cols = array('EINHEIT'=>"<b>EINHEIT</b>",'MIETER'=>"<b>MIETER</b>", 'SALDO_VM'=>"<b>SALDO VORMONAT</b>",'AKT_GESAMT_SOLL'=>"SOLL MIETE WARM", 'UMLAGEN'=>"DAVON UMLAGEN",'AKT_GESAMT_SOLL'=>"GESAMT SOLL AKTUELL", 'ZAHLUNGEN'=>"GELEISTETE ZAHLUNGEN", 'ZAHLUNGEN_MWST'=>"DAVON MWST", 'SALDO_NEU'=>"SALDO NEU");
			// echo '<pre>';
			// print_r($tab_arr);
			// $pdf->ezTable($tab_arr, $cols, 'Monatsbericht mit Auszug');
			// array('showHeadings'=>1,'shaded'=>1, 'titleFontSize' => 10, 'fontSize' => 8, 'xPos'=>50,'xOrientation'=>'right', 'width'=>500,'rowGap'=>1, 'cols'=>array('EINHEIT'=>array('justification'=>'left', 'width'=>50), 'SALDO_VM'=>array('justification'=>'right', 'width'=>60), 'UMLAGEN'=>array('justification'=>'right', 'width'=>55), 'G_SOLL_AKT'=>array('justification'=>'right', 'width'=>50), 'ZAHLUNGEN'=>array('justification'=>'right','width'=>65), 'ZAHLUNGEN_MWST'=>array('justification'=>'right'), 'ERG'=>array('justification'=>'right','width'=>50))));
			$cols = array (
					'EINHEIT' => "<b>EINHEIT</b>",
					'MIETER' => "<b>MIETER</b>",
					'SALDO_VM' => "<b>SALDO\nVORMONAT</b>",
					'SOLL_WM' => "<b>SOLL\nMIETE\nWARM</b>",
					'UMLAGEN' => "<b>DAVON\nUMLAGEN</b>",
					'G_SOLL_AKT' => "<b>GESAMT\nSOLL\nAKTUELL</b>",
					'ZAHLUNGEN' => "<b>GELEISTETE\nZAHLUNGEN</b>",
					'MWST' => "<b>DAVON\nMWST</b>",
					'ERG' => "<b>SALDO\nNEU</b>" 
			);
			$pdf->ezTable ( $tab_arr, $cols, "Monatsbericht mit Auszug - Objekt:$objekt_name - $monatname $jahr", array (
					'showHeadings' => 1,
					'shaded' => 1,
					'titleFontSize' => 10,
					'fontSize' => 8,
					'xPos' => 50,
					'xOrientation' => 'right',
					'width' => 500,
					'rowGap' => 1,
					'cols' => array (
							'EINHEIT' => array (
									'justification' => 'left',
									'width' => 50 
							),
							'SALDO_VM' => array (
									'justification' => 'right',
									'width' => 60 
							),
							'UMLAGEN' => array (
									'justification' => 'right',
									'width' => 55 
							),
							'G_SOLL_AKT' => array (
									'justification' => 'right',
									'width' => 50 
							),
							'ZAHLUNGEN' => array (
									'justification' => 'right',
									'width' => 65 
							),
							'MWST' => array (
									'justification' => 'right' 
							),
							'ERG' => array (
									'justification' => 'right',
									'width' => 50 
							) 
					) 
			) );
			
			$content = $pdf->output ();
			
			$dateiname_org = $objekt_name . '-Monatsbericht_m_Auszug_';
			$dateiname = $this->save_file ( $dateiname_org, 'Monatsberichte', $objekt_id, $content, $monat, $jahr );
			/* Falls kein Objekt ausgewählt */
			
			// weiterleiten($dateiname);
			$download_link = "<h3><a href=\"$dateiname\">Monatsbericht $objekt_name für $monat/$jahr HIER</a></h3>";
			hinweis_ausgeben ( "Monatsbericht ohne Vormieter für $objekt_name wurde erstellt<br>" );
			echo $download_link;
			/* Falls kein Objekt ausgewählt */
		} else {
			echo "Objekt auswählen";
		}
	}
	
	/* Speichern von PDF_datei */
	function save_file($dateiname, $hauptordner, $unterordner, $content, $monat, $jahr) {
		$dir = "$hauptordner/$unterordner";
		
		if (! file_exists ( $hauptordner )) {
			mkdir ( $hauptordner, 0777 );
		}
		if (! file_exists ( $dir )) {
			mkdir ( $dir, 0777 );
		}
		$filename = $dateiname . '_' . $monat . '_' . $jahr . '.pdf';
		$filename = "$dir/$filename";
		
		if (file_exists ( $filename )) {
			fehlermeldung_ausgeben ( "Datei exisitiert bereits, keine Überschreibung möglich" );
			$fhandle = fopen ( $filename, "w" );
			fwrite ( $fhandle, $content );
			fclose ( $fhandle );
			chmod ( $filename, 0777 );
			return $filename;
		} else {
			$fhandle = fopen ( $filename, "w" );
			fwrite ( $fhandle, $content );
			fclose ( $fhandle );
			chmod ( $filename, 0777 );
			return $filename;
		}
	}
	function umlaute_anpassen($str) {
		$str = str_replace ( 'ä', 'ae', $str );
		$str = str_replace ( 'ö', 'oe', $str );
		$str = str_replace ( 'ü', 'ue', $str );
		$str = str_replace ( 'Ä', 'Ae', $str );
		$str = str_replace ( 'Ö', 'Oe', $str );
		$str = str_replace ( 'Ü', 'Ue', $str );
		$str = str_replace ( 'ß', 'ss', $str );
		
		return $str;
	}
	function form_buchung_suchen() {
		$f = new formular ();
		$f->erstelle_formular ( "Suche in", NULL );
		$this->dropdown_geldkonten_alle ( 'Geldkonto wählen' );
		$f->text_feld ( "Zu suchender Betrag", "betrag", '', "10", 'betrag', '' );
		$f->text_feld ( "Suchtext im Buchungstext", "ausdruck", '', "30", 'ausdruck', '' );
		$f->datum_feld ( 'Anfangsdatum', 'anfangsdatum', '', 'anfangsdatum' );
		$f->datum_feld ( 'Enddatum', 'enddatum', '', 'enddatum' );
		$f->datum_feld ( 'Kontoauszug', 'kontoauszug', '', 'kontoauszug' );
		$this->dropdown_kostenrahmen_nr ( 'Kostenkonto', 'kostenkonto', 'Geldkonto', $_SESSION ['geldkonto_id'], '' );
		$buchung = new buchen ();
		$js_typ = "onchange=\"list_kostentraeger('list_kostentraeger', this.value)\"";
		$buchung->dropdown_kostentreager_typen ( 'Kostenträgertyp wählen', 'kostentraeger_typ', 'kostentraeger_typ', $js_typ );
		$js_id = "";
		$buchung->dropdown_kostentreager_ids ( 'Kostenträger', 'kostentraeger_id', 'dd_kostentraeger_id', $js_id );
		$f->hidden_feld ( "option", "buchung_suchen_1" );
		$f->send_button ( "submit_php", "Suchen" );
		$f->send_button ( "submit_pdf", "PDF-Ausgabe" );
		$f->ende_formular ();
	}
	
	/* Funktion zur Erstellung eines Dropdowns für Empfangsgeldkonto */
	function dropdown_geldkonten_alle($label) {
		$result = mysql_query ( "SELECT GELD_KONTEN.KONTO_ID, GELD_KONTEN.BEZEICHNUNG, GELD_KONTEN.BEGUENSTIGTER, GELD_KONTEN.KONTONUMMER, GELD_KONTEN.BLZ, GELD_KONTEN.INSTITUT FROM GELD_KONTEN_ZUWEISUNG, GELD_KONTEN WHERE GELD_KONTEN.KONTO_ID = GELD_KONTEN_ZUWEISUNG.KONTO_ID && GELD_KONTEN_ZUWEISUNG.AKTUELL = '1' && GELD_KONTEN.AKTUELL = '1' GROUP BY GELD_KONTEN.KONTO_ID ORDER BY GELD_KONTEN.KONTO_ID ASC" );
		
		$numrows = mysql_numrows ( $result );
		if ($numrows > 0) {
			
			while ( $row = mysql_fetch_assoc ( $result ) )
				$my_array [] = $row;
			
			echo "<label for=\"geld_konto_dropdown\">$label</label>\n<select name=\"geld_konto\" id=\"geld_konto_dropdown\" size=\"1\" >\n";
			echo "<option value=\"alle\" selected>Alle Geldkonten</option>\n";
			for($a = 0; $a < count ( $my_array ); $a ++) {
				$konto_id = $my_array [$a] ['KONTO_ID'];
				$beguenstigter = $my_array [$a] ['BEGUENSTIGTER'];
				$bezeichnung = $my_array [$a] ['BEZEICHNUNG'];
				$kontonummer = $my_array [$a] ['KONTONUMMER'];
				$blz = $my_array [$a] ['BLZ'];
				$geld_institut = $my_array [$a] ['INSTITUT'];
				echo "<option value=\"$konto_id\" >$bezeichnung - Knr:$kontonummer - Blz: $blz</option>\n";
			} // end for
			echo "</select>\n";
		} else {
			echo "<b>Kein Geldkonto hinterlegt bzw zugewiesen</b>";
			return FALSE;
		}
	}
	function finde_buchungen($abfrage) {
		$result = mysql_query ( "$abfrage" );
		$numrows = mysql_numrows ( $result );
		
		if ($numrows) {
			echo "<table>";
			echo "<tr class=\"feldernamen\"><td colspan=7><b>Suchergebnis</b></td></tr>";
			echo "<tr class=\"feldernamen\"><td>Geldkonto</td><td>Kostenträger</td><td>Datum</td><td>Kostenkonto</td><td>Buchungsnr</td><td>Betrag</td><td width=70%>Vzweck</td></tr>";
			$g = new geldkonto_info ();
			$summe = 0;
			while ( $row = mysql_fetch_assoc ( $result ) ) {
				$datum = date_mysql2german ( $row ['DATUM'] );
				$auszugsnr = $row ['KONTO_AUSZUGSNUMMER'];
				$g_buchungsnummer = $row ['G_BUCHUNGSNUMMER'];
				$erfass_nr = $row ['ERFASS_NR'];
				$betrag_a = nummer_punkt2komma ( $row ['BETRAG'] );
				$betrag = $row ['BETRAG'];
				$vzweck = $row ['VERWENDUNGSZWECK'];
				$kos_typ = $row ['KOSTENTRAEGER_TYP'];
				$kos_id = $row ['KOSTENTRAEGER_ID'];
				$kostenkonto = $row ['KONTENRAHMEN_KONTO'];
				$geldkonto_id = $row ['GELDKONTO_ID'];
				$g->geld_konto_details ( $geldkonto_id );
				$r = new rechnung ();
				$kostentraeger_bezeichnung = $r->kostentraeger_ermitteln ( $kos_typ, $kos_id );
				$dat_bu = $row ['GELD_KONTO_BUCHUNGEN_DAT'];
				$link_aendern = "<a href=\"index.php?daten=buchen&option=geldbuchung_aendern&geldbuchung_dat=$dat_bu\">$g_buchungsnummer ändern</a>";
				
				echo "<tr><td>$g->geldkonto_bezeichnung<td>$kostentraeger_bezeichnung</td><td>$datum</td><td><b>$kostenkonto</b></td><td><b>$link_aendern</b></td><td>$betrag_a</td><td>$vzweck</td></tr>";
				$summe = $summe + $betrag;
			}
			$summe = nummer_punkt2komma ( $summe );
			echo "<tr class=\"feldernamen\"><td colspan=5 align=\"right\"><b>SUMME</b></td><td colspan=\"2\"><b>$summe</b></td></tr>";
			echo "</table><br>";
		} else {
			fehlermeldung_ausgeben ( "Keine Buchung gefunden" );
		}
	}
	function finde_buchungen_pdf($abfrage) {
		$result = mysql_query ( "$abfrage" );
		$numrows = mysql_numrows ( $result );
		
		if ($numrows) {
			
			ob_clean (); // ausgabepuffer leeren
			//include_once ('pdfclass/class.ezpdf.php');
			include_once ('classes/class_bpdf.php');
			$pdf = new Cezpdf ( 'a4', 'portrait' );
			$bpdf = new b_pdf ();
			$bpdf->b_header ( $pdf, 'Partner', $_SESSION [partner_id], 'portrait', 'pdfclass/fonts/Helvetica.afm', 6 );
			
			$g = new geldkonto_info ();
			$summe = 0;
			
			$zeile = 0;
			while ( $row = mysql_fetch_assoc ( $result ) ) {
				$datum = date_mysql2german ( $row [DATUM] );
				$auszugsnr = $row [KONTO_AUSZUGSNUMMER];
				$g_buchungsnummer = $row [G_BUCHUNGSNUMMER];
				$erfass_nr = $row [ERFASS_NR];
				$betrag = nummer_punkt2komma ( $row [BETRAG] );
				$vzweck = $row [VERWENDUNGSZWECK];
				$kos_typ = $row [KOSTENTRAEGER_TYP];
				$kos_id = $row [KOSTENTRAEGER_ID];
				$kostenkonto = $row [KONTENRAHMEN_KONTO];
				$geldkonto_id = $row [GELDKONTO_ID];
				$g->geld_konto_details ( $geldkonto_id );
				$r = new rechnung ();
				if ($kos_typ == 'Mietvertrag') {
					$mv = new mietvertraege ();
					$mv->get_mietvertrag_infos_aktuell ( $kos_id );
					$kostentraeger_bezeichnung = $mv->personen_name_string_u;
				} else {
					$kostentraeger_bezeichnung = $r->kostentraeger_ermitteln ( $kos_typ, $kos_id );
					$kostentraeger_bezeichnung = substr ( $kostentraeger_bezeichnung, 0, 20 );
				}
				$kostentraeger_bezeichnung = strip_tags ( $kostentraeger_bezeichnung );
				$g->geldkonto_bezeichnung_kurz = substr ( $g->geldkonto_bezeichnung_kurz, 0, 18 );
				
				$table_arr [$zeile] ['GK'] = $g->geldkonto_bezeichnung;
				$table_arr [$zeile] ['KOS_BEZ'] = $kostentraeger_bezeichnung;
				$table_arr [$zeile] ['DATUM'] = $datum;
				$table_arr [$zeile] ['KONTO'] = $kostenkonto;
				$table_arr [$zeile] ['BUCHUNGSNR'] = $g_buchungsnummer;
				$table_arr [$zeile] ['BETRAG'] = $betrag;
				$table_arr [$zeile] ['VERWENDUNG'] = $vzweck;
				
				// echo "<tr><td>$g->geldkonto_bezeichnung<td>$kostentraeger_bezeichnung</td><td>$datum</td><td><b>$kostenkonto</b></td><td><b>$g_buchungsnummer</b></td><td>$betrag</td><td>$vzweck</td></tr>";
				$summe = $summe + nummer_komma2punkt ( $betrag );
				$zeile ++;
			}
			$summe = nummer_punkt2komma ( $summe );
			// echo "<tr class=\"feldernamen\"><td colspan=5 align=\"right\"><b>SUMME</b></td><td colspan=\"2\"><b>$summe</b></td></tr>";
			
			$table_arr [$zeile + 1] ['BUCHUNGSNR'] = '<b>SUMME</b>';
			$table_arr [$zeile + 1] ['BETRAG'] = "<b>$summe</b>";
			
			$cols = array (
					'GK' => "Geldkonto",
					'KOS_BEZ' => "Zuordnung",
					'DATUM' => "Datum",
					'KONTO' => "Konto",
					'BUCHUNGSNR' => "Buchungsnr",
					'VERWENDUNG' => "Buchungstext",
					'BETRAG' => "Betrag" 
			);
			if (! empty ( $kostenkonto )) {
				$kt = new kontenrahmen ();
				$kontenrahmen_id = $kt->get_kontenrahmen ( 'Geldkonto', $geldkonto_id );
				$kt->konto_informationen2 ( $kostenkonto, $kontenrahmen_id );
				$ueberschrift = "Kostenkonto $kostenkonto - $kt->konto_bezeichnung";
			}
			
			$pdf->ezTable ( $table_arr, $cols, "$ueberschrift", array (
					'showHeadings' => 1,
					'showLines' => '1',
					'shaded' => 1,
					'shadeCol' => array (
							0.78,
							0.95,
							1 
					),
					'shadeCol2' => array (
							0.1,
							0.5,
							1 
					),
					'titleFontSize' => 10,
					'fontSize' => 8,
					'xPos' => 50,
					'xOrientation' => 'right',
					'width' => 500,
					'cols' => array (
							'ZEILE' => array (
									'justification' => 'right',
									'width' => 30 
							) 
					) 
			) );
			
			$pdf->ezStream ();
		} else {
			fehlermeldung_ausgeben ( "Keine Buchung gefunden" );
		}
	}
} // end class

?>
