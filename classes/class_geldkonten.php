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
 * @filesource   $HeadURL: http://192.168.2.52/svn/berlussimo_1/tags/02.11.2010 - Downloadversion 0.27/classes/class_geldkonten.php $
 * @version      $Revision: 15 $
 * @modifiedby   $LastChangedBy: sivac $
 * @lastmodified $Date: 2011-07-07 10:41:33 +0200 (Do, 07 Jul 2011) $
 * 
 */

/* Klasse die die Geldkonten verwaltet */
include_once ('classes/class_bk.php');
class gk {
	function form_geldkonto_neu() {
		$f = new formular ();
		$f->erstelle_formular ( "Neues Geldkonto erstellen", NULL );
		$f->text_feld ( "Geldkontobezeichnung", "g_bez", "", "50", 'g_bez', '' );
		$f->text_feld ( "Beg�nstigter", "beguenstigter", "", "50", 'beguenstigter', '' );
		$f->text_feld ( "Kontonummer", "kontonummer", "", "50", 'kontonummer', '' );
		$f->text_feld ( "BLZ", "blz", "", "50", 'blz', '' );
		$js_iban_bic = "onclick=\"get_iban_bic('kontonummer', 'blz')\"";
		$f->check_box_js1 ( 'chkk_ibanbic', 'chkk_ibanbic', '', 'IBAN/BIC berechnen?!', $js_iban_bic, '' );
		$f->text_feld ( "IBAN", "iban", "", "50", 'iban', '' );
		$f->text_feld ( "BIC", "bic", "", "50", 'bic', '' );
		$f->text_feld ( "Geldinstitut", "institut", "", "50", 'institut', '' );
		
		$b = new buchen ();
		$js_typ = "onchange=\"list_kostentraeger('list_kostentraeger', this.value)\"";
		// $js_typ='';
		$b->dropdown_kostentreager_typen ( 'Geldkonto zuweisen an', 'kostentraeger_typ', 'kostentraeger_typ', $js_typ );
		$js_id = "";
		$b->dropdown_kostentreager_ids ( 'Bitte Zuweisung w�hlen', 'kostentraeger_id', 'dd_kostentraeger_id', $js_id );
		
		$f->hidden_feld ( "option", "new_gk" );
		$f->send_button ( "submit_gk", "Erstellen" );
		$f->ende_formular ();
	}
	function form_geldkonto_edit($gk_id) {
		$gkk = new geldkonto_info ();
		$gkk->geld_konto_details ( $gk_id );
		
		$f = new formular ();
		$f->erstelle_formular ( "Geldkonto �ndern", NULL );
		$f->text_feld ( "Geldkontobezeichnung", "g_bez", "$gkk->geldkonto_bez", "50", 'g_bez', '' );
		$f->text_feld ( "Beg�nstigter", "beguenstigter", "$gkk->beguenstigter", "50", 'beguenstigter', '' );
		$f->text_feld ( "Kontonummer", "kontonummer", "$gkk->kontonummer", "50", 'kontonummer', '' );
		$f->text_feld ( "BLZ", "blz", "$gkk->blz", "50", 'blz', '' );
		$js_iban_bic = "onclick=\"get_iban_bic('kontonummer', 'blz')\"";
		$f->check_box_js1 ( 'chkk_ibanbic', 'chkk_ibanbic', '', 'IBAN/BIC berechnen?!', $js_iban_bic, '' );
		$f->text_feld ( "IBAN", "iban", "$gkk->IBAN1", "50", 'iban', '' );
		$f->text_feld ( "BIC", "bic", "$gkk->BIC", "50", 'bic', '' );
		$f->text_feld ( "Geldinstitut", "institut", "$gkk->institut", "50", 'institut', '' );
		/*
		 * $b = new buchen;
		 * $js_typ = "onchange=\"list_kostentraeger('list_kostentraeger', this.value)\"";
		 * #$js_typ='';
		 * $b->dropdown_kostentreager_typen('Geldkonto zuweisen an', 'kostentraeger_typ', 'kostentraeger_typ', $js_typ);
		 * $js_id = "";
		 * $b->dropdown_kostentreager_ids('Bitte Zuweisung w�hlen', 'kostentraeger_id', 'dd_kostentraeger_id', $js_id);
		 */
		
		$f->hidden_feld ( "option", "gk_update" );
		$f->send_button ( "submit_gk", "�ndern" );
		$f->ende_formular ();
	}
	function geldkonto_update($gk_id, $g_bez, $beguenstigter, $kontonummer, $blz, $institut, $iban, $bic) {
		$db_abfrage = "UPDATE GELD_KONTEN SET AKTUELL='0' WHERE KONTO_ID='$gk_id'";
		$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		
		$db_abfrage = "INSERT INTO GELD_KONTEN VALUES (NULL, '$gk_id', '$g_bez','$beguenstigter', '$kontonummer', '$blz','$iban', '$bic', '$institut', '1')";
		$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		
		/* Protokollieren */
		// $last_dat = mysql_insert_id();
		// protokollieren('GELD_KONTEN', $last_dat, $last_dat);
	}
	function geldkonto_speichern($kos_typ, $kos_id, $g_bez, $beguenstigter, $kontonummer, $blz, $institut, $iban, $bic) {
		if (! $this->check_gk_exists ( $kontonummer, $blz, $institut )) {
			$bk = new bk ();
			$last_b_id = $bk->last_id ( 'GELD_KONTEN', 'KONTO_ID' ) + 1;
			
			$db_abfrage = "INSERT INTO GELD_KONTEN VALUES (NULL, '$last_b_id', '$g_bez','$beguenstigter', '$kontonummer', '$blz','$iban', '$bic', '$institut', '1')";
			$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
			
			/* Protokollieren */
			$last_dat = mysql_insert_id ();
			protokollieren ( 'GELD_KONTEN', $last_dat, '0' );
			
			if ($this->check_zuweisung_kos ( $last_b_id, $kos_typ, $kos_id )) {
				echo "Zuweisung existiert bereits.";
			} else {
				$this->zuweisung_speichern ( $kos_typ, $kos_id, $last_b_id );
			}
			
			echo "Geldkonto wurde gespeichert.";
			return $last_b_id;
		} else {
			echo "Geldkonto existiert schon";
			die ();
		}
	}
	function form_geldkonto_zuweisen() {
		$f = new formular ();
		$f->erstelle_formular ( "Geldkonto zuweisen", NULL );
		$this->dropdown_geldkonten_alle ( 'Geldkonto w�hlen', 'geldkonto_id', 'geldkonto_id' );
		
		$b = new buchen ();
		$js_typ = "onchange=\"list_kostentraeger('list_kostentraeger', this.value)\"";
		// $js_typ='';
		$b->dropdown_kostentreager_typen ( 'Geldkonto zuweisen an', 'kostentraeger_typ', 'kostentraeger_typ', $js_typ );
		$js_id = "";
		$b->dropdown_kostentreager_ids ( 'Bitte Zuweisung w�hlen', 'kostentraeger_id', 'dd_kostentraeger_id', $js_id );
		
		$f->hidden_feld ( "option", "zuweisen_gk" );
		$f->send_button ( "submit_gk", "Zuweisen" );
		$f->ende_formular ();
	}
	function dropdown_geldkonten_alle($label, $name, $id) {
		$result = mysql_query ( "SELECT *  FROM  GELD_KONTEN WHERE GELD_KONTEN.AKTUELL = '1' ORDER BY BEZEICHNUNG ASC" );
		
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			while ( $row = mysql_fetch_assoc ( $result ) )
				$my_array [] = $row;
			
			echo "<label for=\"$id\">$label</label>\n<select name=\"$name\" id=\"$id\" size=\"1\" >\n";
			for($a = 0; $a < count ( $my_array ); $a ++) {
				$konto_id = $my_array [$a] ['KONTO_ID'];
				$beguenstigter = $my_array [$a] ['BEGUENSTIGTER'];
				$kontonummer = $my_array [$a] ['KONTONUMMER'];
				$blz = $my_array [$a] ['BLZ'];
				$geld_institut = $my_array [$a] ['INSTITUT'];
				$bez = $my_array [$a] ['BEZEICHNUNG'];
				if (isset ( $_SESSION ['geldkonto_id'] ) && $_SESSION ['geldkonto_id'] == $konto_id) {
					echo "<option value=\"$konto_id\" selected>$bez - Knr:$kontonummer - Blz: $blz</option>\n";
				} else {
					echo "<option value=\"$konto_id\" >$bez - Knr:$kontonummer - Blz: $blz</option>\n";
				}
			} // end for
			echo "</select>\n";
		} else {
			echo "<b>Kein Geldkonto hinterlegt</b>";
			return FALSE;
		}
	}
	function dropdown_geldkonten_alle_vorwahl($label, $name, $id, $vorwahl_gk_id, $js) {
		$result = mysql_query ( "SELECT *  FROM  GELD_KONTEN WHERE GELD_KONTEN.AKTUELL = '1' ORDER BY BEZEICHNUNG ASC" );
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			
			echo "<label for=\"$id\">$label</label>\n<select name=\"$name\" id=\"$id\" size=\"1\" $js>\n";
			while ( $row = mysql_fetch_assoc ( $result ) ) {
				$konto_id = $row ['KONTO_ID'];
				$beguenstigter = $row ['BEGUENSTIGTER'];
				// $kontonummer = $row['KONTONUMMER'];
				// $blz = $row['BLZ'];
				$geld_institut = $row ['INSTITUT'];
				$bez = $row ['BEZEICHNUNG'];
				$iban = $row ['IBAN'];
				$bic = $row ['BIC'];
				$iban1 = chunk_split ( $iban, 4, ' ' );
				if ($vorwahl_gk_id == $konto_id) {
					echo "<option value=\"$konto_id\" selected>$bez - $iban1 -  $bic</option>\n";
				} else {
					echo "<option value=\"$konto_id\" >$bez - $iban - $bic</option>\n";
				}
			} // end for
			echo "</select>\n";
		} else {
			echo "<b>Kein Geldkonto hinterlegt</b>";
			return FALSE;
		}
	}
	function uebersicht_zuweisung() {
		$result = mysql_query ( "SELECT *  FROM  GELD_KONTEN WHERE GELD_KONTEN.AKTUELL = '1' ORDER BY BEZEICHNUNG ASC" );
		
		$numrows = mysql_numrows ( $result );
		if ($numrows > 0) {
			while ( $row = mysql_fetch_assoc ( $result ) )
				$my_array [] = $row;
			echo "<table class=\"sortable\">";
			echo "<tr><th>BEZEICHNUNG</th><th width=\"200\">IBAN</th><th>BIC</th><th>ZUWEISUNG</th></tr>";
			for($a = 0; $a < count ( $my_array ); $a ++) {
				$konto_id = $my_array [$a] ['KONTO_ID'];
				$beguenstigter = $my_array [$a] ['BEGUENSTIGTER'];
				$kontonummer = $my_array [$a] ['KONTONUMMER'];
				$blz = $my_array [$a] ['BLZ'];
				$iban = chunk_split ( $my_array [$a] ['IBAN'], 4, ' ' );
				// $iban_1 = chunk_split($iban, 4, ' ');
				$bic = $my_array [$a] ['BIC'];
				$geld_institut = $my_array [$a] ['INSTITUT'];
				$bez = $my_array [$a] ['BEZEICHNUNG'];
				$zuweisung_string = $this->check_zuweisung ( $konto_id );
				echo "<tr><td>$bez</td><td>$iban</td><td>$bic</td><td>$zuweisung_string</td></tr>";
				unset ( $zuweisung_string );
			} // end for
			echo "</table>";
		} else {
			echo "Keine Geldkonten hinterlegt";
		}
	}
	function check_zuweisung($geldkonto_id) {
		$result = mysql_query ( "SELECT *  FROM  GELD_KONTEN_ZUWEISUNG WHERE AKTUELL = '1' && KONTO_ID='$geldkonto_id'" );
		
		$numrows = mysql_numrows ( $result );
		
		if ($numrows > 0) {
			while ( $row = mysql_fetch_assoc ( $result ) )
				$my_array [] = $row;
			
			$kos_bez_string = '';
			for($a = 0; $a < count ( $my_array ); $a ++) {
				$zaehler = $a + 1;
				$kos_typ = $my_array [$a] ['KOSTENTRAEGER_TYP'];
				$kos_id = $my_array [$a] ['KOSTENTRAEGER_ID'];
				$r = new rechnung ();
				$kos_bez = $r->kostentraeger_ermitteln ( $kos_typ, $kos_id );
				$link_loeschen = "<a href=\"?daten=geldkonten&option=zuweisung_loeschen&geldkonto_id=$geldkonto_id&kos_typ=$kos_typ&kos_id=$kos_id\"><b>Aufheben</b></a>";
				$kos_bez_string .= "$zaehler. " . $kos_bez . "  |  $link_loeschen<br>";
			}
			return $kos_bez_string;
		} else {
			return "<b>Keine Zuweisung</b>";
		}
	}
	function get_zuweisung_arr($geldkonto_id) {
		$result = mysql_query ( "SELECT *  FROM  GELD_KONTEN_ZUWEISUNG WHERE AKTUELL = '1' && KONTO_ID='$geldkonto_id'" );
		
		$numrows = mysql_numrows ( $result );
		
		if ($numrows > 0) {
			while ( $row = mysql_fetch_assoc ( $result ) )
				$my_array [] = $row;
			return $my_array;
		}
	}
	function get_zuweisung_string_kurz($geldkonto_id) {
		$arr = $this->get_zuweisung_arr ( $geldkonto_id );
		// print_r($arr);
		if (is_array ( $arr )) {
			$anz = count ( $arr );
			$kos_bez_alle = '';
			for($a = 0; $a < $anz; $a ++) {
				$kos_typ = $arr [$a] ['KOSTENTRAEGER_TYP'];
				$kos_id = $arr [$a] ['KOSTENTRAEGER_ID'];
				if ($kos_typ != 'Eigentuemer') {
					$r = new rechnung ();
					$kos_bez_alle .= $r->kostentraeger_ermitteln ( $kos_typ, $kos_id ) . ', ';
				} else {
					$weg = new weg ();
					$weg->get_eigentumer_id_infos4 ( $kos_id );
					$kos_bez_alle .= $weg->einheit_kurzname . ', ';
				}
				if ($a == $anz - 1) {
					$kos_bez_alle = substr ( $kos_bez_alle, 0, - 2 );
				}
			}
			return $kos_bez_alle;
		} else {
			return 'Keine Zuweisung';
		}
	}
	function get_objekt_id($geldkonto_id) {
		$result = mysql_query ( "SELECT KOSTENTRAEGER_ID FROM  GELD_KONTEN_ZUWEISUNG WHERE AKTUELL = '1' && KONTO_ID='$geldkonto_id' && KOSTENTRAEGER_TYP='Objekt' LIMIT 0,1" );
		// echo "SELECT KOSTENTRAEGER_ID FROM GELD_KONTEN_ZUWEISUNG WHERE AKTUELL = '1' && KONTO_ID='$geldkonto_id' && KOSTENTRAEGER_TYP='Objekt' LIMIT 0,1";
		$numrows = mysql_numrows ( $result );
		
		if ($numrows) {
			$row = mysql_fetch_assoc ( $result );
			$kos_id = $row ['KOSTENTRAEGER_ID'];
			return $kos_id;
		}
	}
	function check_zuweisung_kos($geldkonto_id, $kos_typ, $kos_id) {
		$result = mysql_query ( "SELECT *  FROM  GELD_KONTEN_ZUWEISUNG WHERE AKTUELL = '1' && KONTO_ID='$geldkonto_id' && KOSTENTRAEGER_TYP='$kos_typ' && KOSTENTRAEGER_ID='$kos_id'" );
		
		$numrows = mysql_numrows ( $result );
		
		if ($numrows > 0) {
			return true;
		} else {
			return false;
		}
	}
	function get_zuweisung_kos_arr($kos_typ, $kos_id) {
		$db_abfrage = "SELECT *  FROM  GELD_KONTEN_ZUWEISUNG WHERE AKTUELL = '1' && KOSTENTRAEGER_TYP='$kos_typ' && KOSTENTRAEGER_ID='$kos_id'";
		// echo $db_abfrage;
		$result = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			while ( $row = mysql_fetch_assoc ( $result ) ) {
				$arr [] = $row;
			}
			return $arr;
		} else {
			return false;
		}
	}
	function check_zuweisung_kos_typ($geldkonto_id, $kos_typ, $kos_id) {
		if (! empty ( $kos_id )) {
			$result = mysql_query ( "SELECT *  FROM  GELD_KONTEN_ZUWEISUNG WHERE AKTUELL = '1' && KONTO_ID='$geldkonto_id' && KOSTENTRAEGER_TYP='$kos_typ' && KOSTENTRAEGER_ID='$kos_id'" );
		} else {
			$result = mysql_query ( "SELECT *  FROM  GELD_KONTEN_ZUWEISUNG WHERE AKTUELL = '1' && KONTO_ID='$geldkonto_id' && KOSTENTRAEGER_TYP='$kos_typ'" );
		}
		$numrows = mysql_numrows ( $result );
		
		if ($numrows) {
			return true;
		} else {
			return false;
		}
	}
	function check_gk_exists($kontonummer, $blz, $institut) {
		$result = mysql_query ( "SELECT *  FROM  GELD_KONTEN WHERE AKTUELL = '1' && KONTONUMMER='$kontonummer' && BLZ='$blz' && INSTITUT='$institut'" );
		$numrows = mysql_numrows ( $result );
		
		if ($numrows > 0) {
			return true;
		} else {
			return false;
		}
	}
	function zuweisung_speichern($kos_typ, $kos_id, $geldkonto_id) {
		$bk = new bk ();
		$last_b_id = $bk->last_id ( 'GELD_KONTEN_ZUWEISUNG', 'ZUWEISUNG_ID' ) + 1;
		
		$db_abfrage = "INSERT INTO GELD_KONTEN_ZUWEISUNG VALUES (NULL, '$last_b_id', '$geldkonto_id', '$kos_typ','$kos_id', '1')";
		$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		/* Protokollieren */
		$last_dat = mysql_insert_id ();
		// protokollieren('GELD_KONTEN_ZUWEISUNG', $last_dat, '0');
		return $last_b_id;
	}
	function zuweisung_aufheben($kos_typ, $kos_id, $geldkonto_id) {
		$db_abfrage = "UPDATE GELD_KONTEN_ZUWEISUNG SET AKTUELL='0' WHERE AKTUELL = '1' && KONTO_ID='$geldkonto_id' && KOSTENTRAEGER_TYP='$kos_typ' && KOSTENTRAEGER_ID='$kos_id'";
		$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		/* Protokollieren */
		$last_dat = mysql_insert_id ();
		// protokollieren('GELD_KONTEN_ZUWEISUNG', $last_dat, '$last_dat');
	}
	function get_geldkonto_id($bezeichnung) {
		$result = mysql_query ( "SELECT KONTO_ID  FROM GELD_KONTEN WHERE BEZEICHNUNG='$bezeichnung' && AKTUELL='1' ORDER BY KONTO_DAT DESC LIMIT 0,1" );
		// echo "SELECT KONTO_ID FROM GELD_KONTEN WHERE BEZEICHNUNG='$bezeichnung' && AKTUELL='1' ORDER BY KONTO_DAT DESC LIMIT 0,1<br>";
		$row = mysql_fetch_assoc ( $result );
		return $row ['KONTO_ID'];
	}
	function get_geldkonto_id2($kto, $blz, $iban = null) {
		if ($iban == null) {
			$result = mysql_query ( "SELECT KONTO_ID  FROM GELD_KONTEN WHERE KONTONUMMER='$kto' && BLZ='$blz' &&  AKTUELL='1' ORDER BY KONTO_DAT DESC LIMIT 0,2" );
			// echo "SELECT KONTO_ID FROM GELD_KONTEN WHERE KONTONUMMER='$kto' && BLZ='$blz' && AKTUELL='1' ORDER BY KONTO_DAT DESC LIMIT 0,1<br>";
		} else {
			$result = mysql_query ( "SELECT KONTO_ID  FROM GELD_KONTEN WHERE IBAN='$iban'  &&  AKTUELL='1' ORDER BY KONTO_DAT DESC LIMIT 0,2" );
			// echo "SELECT KONTO_ID FROM GELD_KONTEN WHERE IBAN='$iban' && AKTUELL='1' ORDER BY KONTO_DAT DESC LIMIT 0,1<br>";
		}
		$numrows = mysql_numrows ( $result );
		if ($numrows == 1) {
			$row = mysql_fetch_assoc ( $result );
			// echo "<h1>".$row['KONTO_ID']."</h1>";
			return $row ['KONTO_ID'];
		}
		
		if ($numrows > 1) {
			fehlermeldung_ausgeben ( "$kto $blz $iban<br>existiert in Geldkonten $numrows - MAL!!!" );
			die ();
		}
	}
	function get_kos_by_iban($iban) {
		if (isset ( $this->iban_kos_typ )) {
			unset ( $this->iban_kos_typ );
		}
		if (isset ( $this->iban_kos_id )) {
			unset ( $this->iban_kos_id );
		}
		if (isset ( $this->iban_konto_id )) {
			unset ( $this->iban_konto_id );
		}
		$result = mysql_query ( "SELECT GELD_KONTEN.KONTO_ID, IBAN, GELD_KONTEN_ZUWEISUNG.KOSTENTRAEGER_TYP, GELD_KONTEN_ZUWEISUNG.KOSTENTRAEGER_ID   FROM `GELD_KONTEN`, GELD_KONTEN_ZUWEISUNG WHERE GELD_KONTEN.IBAN = '$iban' AND GELD_KONTEN.KONTO_ID=GELD_KONTEN_ZUWEISUNG.KONTO_ID && GELD_KONTEN.AKTUELL = '1' && GELD_KONTEN_ZUWEISUNG.AKTUELL = '1' LIMIT 0,1" );
		$row = mysql_fetch_assoc ( $result );
		$this->iban_kos_typ = $row ['KOSTENTRAEGER_TYP'];
		$this->iban_kos_id = $row ['KOSTENTRAEGER_ID'];
		$this->iban_konto_id = $row ['KONTO_ID'];
	}
	function update_iban_bic_alle() {
		$result = mysql_query ( "SELECT *  FROM  GELD_KONTEN WHERE GELD_KONTEN.AKTUELL = '1' ORDER BY KONTO_DAT" );
		
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			while ( $row = mysql_fetch_assoc ( $result ) ) {
				$dat = $row ['KONTO_DAT'];
				$kto = $row ['KONTONUMMER'];
				$blz = $row ['BLZ'];
				$sep = new sepa ();
				$sep->get_iban_bic ( $kto, $blz );
				// echo "$sep->IBAN|$sep->BIC|$sep->BANKNAME_K<br>";
				/* Update */
				$db_abfrage = "UPDATE GELD_KONTEN SET IBAN='$sep->IBAN', BIC='$sep->BIC' WHERE KONTO_DAT='$dat'";
				$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
				// die();
			}
			echo "Alle vorhandenen Geldkonten mit IBAN und BIC versehen!!!";
		}
	}
} // end class gk

?>
