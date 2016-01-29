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
 * @filesource   $HeadURL: http://192.168.2.52/svn/berlussimo_1/tags/02.11.2010 - Downloadversion 0.27/classes/class_ueberweisung.php $
 * @version      $Revision: 15 $
 * @modifiedby   $LastChangedBy: sivac $
 * @lastmodified $Date: 2011-07-07 10:41:33 +0200 (Do, 07 Jul 2011) $
 * 
 */

/* Allgemeine Funktionsdatei laden */
include_once ("includes/allgemeine_funktionen.php");

/* Klasse "formular" f�r Formularerstellung laden */
include_once ("classes/class_formular.php");
include_once ("classes/berlussimo_class.php");
class ueberweisung {
	function form_rechnung_dtaus($belegnr) {
		$f = new formular ();
		$r = new rechnungen ();
		$g = new geldkonto_info ();
		$r->rechnung_grunddaten_holen ( $belegnr );
		
		$f->erstelle_formular ( "Rechnung �ber DTAUS zahlen", NULL );
		if ($r->status_bezahlt == '0') {
			
			if ($r->rechnungstyp == 'Rechnung' or $r->rechnungstyp == 'Buchungsbeleg') {
				$g->dropdown_geldkonten_k ( "�berweisen von $r->rechnungs_empfaenger_name -> Geldkonto ausw�hlen", 'a_konto_id', 'a_konto_id', $r->rechnungs_empfaenger_typ, $r->rechnungs_empfaenger_id );
				
				$g->dropdown_geldkonten_k ( "�berweisen an $r->rechnungs_aussteller_name -> Geldkonto ausw�hlen", 'e_konto_id', 'e_konto_id', $r->rechnungs_aussteller_typ, $r->rechnungs_aussteller_id );
				
				$r->dropdown_buchungs_betrag_kurz ( 'Zu zahlenden Betrag w�hlen', 'betrags_art', 'betrags_art', '' );
				$t_betrag = nummer_punkt2komma ( $r->rechnungs_skontobetrag );
				
				$f->text_feld ( 'Ausgew�hlten Betrag eingeben', 'betrag', $t_betrag, '10', 'betrag', '' );
				$f->text_feld ( 'Verwendungszweck1 (max. 27 Zeichen)', 'vzweck1', "Rechnung $r->rechnungsnummer", '27', 'vzweck1', '' );
				$f->text_feld ( 'Verwendungszweck2 (max. 27 Zeichen)', 'vzweck2', "", '27', 'vzweck1', '' );
				$f->text_feld ( 'Verwendungszweck3 (max. 27 Zeichen)', 'vzweck3', "", '27', 'vzweck1', '' );
				
				$kb = str_replace ( "<br>", "\n", $r->kurzbeschreibung );
				
				$f->text_bereich ( 'Buchungstext', 'buchungstext', "Erfnr:$r->belegnr, WE:$r->empfaenger_eingangs_rnr, Zahlungsausgang Rnr:$r->rechnungsnummer, $kb", 60, 60, 'buchungstex' );
			}
			
			$f->hidden_feld ( "bezugstab", "RECHNUNG" );
			$f->hidden_feld ( "bezugsid", $belegnr );
			
			$f->hidden_feld ( "option", "ueberweisung_dtaus" );
			$f->send_button ( "submit_dtaus", "Zu DTAUS hinzuf�gen" );
		} else {
			echo "Diese Rechnung wurde am $r->bezahlt_am als bezahlt markiert";
		}
		$f->ende_formular ();
		// echo'<pre>';
		// print_r($r);
	}
	function form_rechnung_dtaus_sepa($belegnr) {
		if (! isset ( $_SESSION ['geldkonto_id'] )) {
			fehlermeldung_ausgeben ( "Geldkonto von welchem �berwiesen wird W�HLEN!!!!" );
		}
		$f = new formular ();
		$r = new rechnungen ();
		$g = new geldkonto_info ();
		$r->rechnung_grunddaten_holen ( $belegnr );
		
		$f->erstelle_formular ( "Rechnung �ber SEPA zahlen", NULL );
		if ($r->status_bezahlt == '0') {
			
			if ($r->rechnungstyp == 'Rechnung' or $r->rechnungstyp == 'Buchungsbeleg') {
				// $g->dropdown_geldkonten_k("�berweisen von $r->rechnungs_empfaenger_name -> Geldkonto ausw�hlen", 'a_konto_id', 'a_konto_id', $r->rechnungs_empfaenger_typ, $r->rechnungs_empfaenger_id);
				$sep = new sepa ();
				// if($sep->dropdown_sepa_geldkonten('�berweisen von', 'gk_id', 'gk_id', $r->rechnungs_empfaenger_typ, $r->rechnungs_empfaenger_id) ==false){
				// fehlermeldung_ausgeben("SEPA Kontoverbondung Rg.Empf�nger fehlt!!!");
				// die();
				$gk_a_id = $_SESSION ['geldkonto_id'];
				$f->hidden_feld ( 'gk_id', $gk_a_id );
				
				// }
				if ($sep->dropdown_sepa_geldkonten ( '�berweisen an', 'empf_sepa_gk_id', 'empf_sepa_gk_id', $r->rechnungs_aussteller_typ, $r->rechnungs_aussteller_id ) == false) {
					fehlermeldung_ausgeben ( "SEPA Kontoverbindung Rg. Aussteller fehlt!!!" );
					die ();
				}
				
				// $g->dropdown_geldkonten_k("�berweisen an $r->rechnungs_aussteller_name -> Geldkonto ausw�hlen", 'e_konto_id', 'e_konto_id', $r->rechnungs_aussteller_typ, $r->rechnungs_aussteller_id);
				$js_opt = "onchange=\"var betrag_feld = document.getElementById('betrag'); betrag_feld.value=nummer_punkt2komma(this.value);\";";
				// $js_opt = "onfocus='document.getElementById(\"betrag\").value=this.value);'";
				$r->dropdown_buchungs_betrag_kurz_sepa ( 'Zu zahlenden Betrag w�hlen', 'betrags_art', 'betrags_art', $js_opt );
				$t_betrag = nummer_punkt2komma ( $r->rechnungs_skontobetrag );
				
				$f->text_feld ( 'Ausgew�hlten Betrag eingeben', 'betrag', $t_betrag, '10', 'betrag', '' );
				// $f->text_feld('Verwendungszweck1 (max. 27 Zeichen)', 'vzweck1', "Rechnung $r->rechnungsnummer", '27', 'vzweck1', '');
				$vzweck_140 = substr ( "$r->rechnungs_aussteller_name, Rnr:$r->rechnungsnummer, $r->kurzbeschreibung", 0, 140 );
				$f->text_bereich ( 'Verwendungszweck Max 140Zeichen', 'vzweck', "$vzweck_140", 60, 60, 'vzweck' );
				// $f->text_feld('Verwendungszweck2 (max. 27 Zeichen)', 'vzweck2', "", '27', 'vzweck1', '');
				// $f->text_feld('Verwendungszweck3 (max. 27 Zeichen)', 'vzweck3', "", '27', 'vzweck1', '');
				// $f->text_feld('Buchungskonto', 'konto', 7000, 20, 'konto', '');
				$kk = new kontenrahmen ();
				$kk->dropdown_kontorahmenkonten ( 'Konto', 'konto', 'konto', 'Geldkonto', $_SESSION ['geldkonto_id'], '' );
				
				$kb = str_replace ( "<br>", "\n", $r->kurzbeschreibung );
				
				$f->text_bereich ( 'Buchungstext', 'buchungstext', "Erfnr:$r->belegnr, WE:$r->empfaenger_eingangs_rnr, Zahlungsausgang Rnr:$r->rechnungsnummer, $kb", 60, 60, 'buchungstex' );
			}
			
			/* Alt aus dtaus */
			$f->hidden_feld ( "bezugstab", "RECHNUNG" );
			$f->hidden_feld ( "bezugsid", $belegnr );
			
			/* Neu SEPA */
			$f->hidden_feld ( 'option', 'sepa_sammler_hinzu' );
			$f->hidden_feld ( 'kat', 'RECHNUNG' );
			$f->hidden_feld ( 'kos_typ', $r->rechnungs_aussteller_typ );
			$f->hidden_feld ( 'kos_id', $r->rechnungs_aussteller_id );
			$f->send_button ( 'sndBtn', 'Hinzuf�gen' );
		} else {
			echo "Diese Rechnung wurde am $r->bezahlt_am als bezahlt markiert";
		}
		$f->ende_formular ();
		// echo'<pre>';
		// print_r($r);
	}
	function check_dtaus_vorhanden($a_konto_id, $e_konto_id, $bezugstab, $bezugsid) {
		$db_abfrage = "SELECT * FROM UEBERWEISUNG WHERE  A_KONTO_ID='$a_konto_id' && E_KONTO_ID='$e_konto_id' && BEZUGSTAB='$bezugstab' && BEZUGS_ID='$bezugsid'";
		$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		$numrows = mysql_numrows ( $resultat );
		if ($numrows) {
			return true;
		} else {
			return false;
		}
	}
	function zahlung2dtaus($a_konto_id, $e_konto_id, $betrag, $betrags_art, $vzweck1, $vzweck2, $vzweck3, $bezugstab, $bezugsid, $buchungstext, $zahlungart) {
		if (! $this->check_dtaus_vorhanden ( $a_konto_id, $e_konto_id, $bezugstab, $bezugsid )) {
			
			$datum = date ( "Y-m-d" );
			$betrag = nummer_komma2punkt ( $betrag );
			$db_abfrage = "INSERT INTO UEBERWEISUNG VALUES (NULL, NULL, '$datum', '$a_konto_id', '$e_konto_id', '$betrag', '$betrags_art', '$vzweck1', '$vzweck2', '$vzweck3', '$buchungstext','$zahlungart', '$bezugstab', '$bezugsid', NULL, NULL,   '1')";
			$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
			
			/* Protokollieren */
			$last_dat = mysql_insert_id ();
			protokollieren ( 'UEBERWEISUNG', $last_dat, '0' );
			hinweis_ausgeben ( "EINGABE WURDE IN DEN DTAUS POOL AUFGENOMMEN" );
		} else {
			fehlermeldung_ausgeben ( "IN DTAUS VORHANDEN" );
		}
	}
	function dtaus_poll_arr() {
		$result = mysql_query ( "SELECT A_KONTO_ID FROM `UEBERWEISUNG` WHERE `AKTUELL`='1' && DTAUS_ID IS NULL GROUP BY `A_KONTO_ID`" );
		
		$numrows = mysql_numrows ( $result );
		if ($numrows > 0) {
			while ( $row = mysql_fetch_assoc ( $result ) )
				$my_array [] = $row;
			return $my_array;
		} else {
			return FALSE;
		}
	}
	function dtaus_uebersicht() {
		$a_konto_ids_arr = $this->dtaus_poll_arr ();
		$g = new geldkonto_info ();
		if (! is_array ( $a_konto_ids_arr )) {
			echo "DTAUS POOL LEER";
		} else {
			echo "IM DTAUS POLL SIND DATEN F�R FOLGENDE GELDKONTEN VORHANDEN<br>";
			$anzahl_konten = count ( $a_konto_ids_arr );
			for($a = 0; $a < $anzahl_konten; $a ++) {
				$g_konto_id = $a_konto_ids_arr [$a] ['A_KONTO_ID'];
				$g->geld_konto_details ( $g_konto_id );
				$link_erstellen = "<a href=\"?daten=ueberweisung&option=u_dtaus_erstellen&konto_id=$g_konto_id\">DTAUS ERSTELLEN</a>";
				echo '<b>' . $g->geldkonto_bezeichnung . '</b>' . " $link_erstellen<br>";
				$this->dtaus_zeilen_anzeigen ( $g_konto_id );
				$summe = $this->dtaus_summe_geldk ( $g_konto_id );
				echo "<b>$g->geldkonto_bezeichnung Gesamtsumme im Pool  $summe</b>";
			}
		}
	}
	function dtaus_zeilen_arr($g_konto_id) {
		$result = mysql_query ( "SELECT A_KONTO_ID, E_KONTO_ID, DATUM, BETRAG, VZWECK1, VZWECK2, VZWECK3, BUCHUNGSTEXT FROM `UEBERWEISUNG` WHERE `A_KONTO_ID` = '$g_konto_id'  AND `AKTUELL` = '1' && DTAUS_ID IS NULL" );
		
		$numrows = mysql_numrows ( $result );
		if ($numrows > 0) {
			while ( $row = mysql_fetch_assoc ( $result ) )
				$my_array [] = $row;
			return $my_array;
		} else {
			return FALSE;
		}
	}
	function dtaus_zeilen_anzeigen($g_konto_id) {
		$zeilen_arr = $this->dtaus_zeilen_arr ( $g_konto_id );
		if (! is_array ( $zeilen_arr )) {
			echo "Keine Daten f�r Konto $g_konto_id vorhanden";
		} else {
			$anzahl_zeilen = count ( $zeilen_arr );
			$g = new geldkonto_info ();
			$g1 = new geldkonto_info ();
			for($a = 0; $a < $anzahl_zeilen; $a ++) {
				$e_konto_id = $zeilen_arr [$a] ['E_KONTO_ID'];
				$datum = $zeilen_arr [$a] ['DATUM'];
				$betrag = $zeilen_arr [$a] ['BETRAG'];
				$vzweck1 = $zeilen_arr [$a] ['VZWECK1'];
				$vzweck2 = $zeilen_arr [$a] ['VZWECK2'];
				$vzweck3 = $zeilen_arr [$a] ['VZWECK3'];
				$buchungstext = $zeilen_arr [$a] ['BUCHUNGSTEXT'];
				$g->geld_konto_details ( $g_konto_id );
				$g1->geld_konto_details ( $e_konto_id );
				
				echo " $g->kontonummer $g->blz |  $g1->geldkonto_bezeichnung $g1->kontonummer $g1->blz $betrag $vzweck1 $vzweck2 $vzweck3<br>";
			}
		}
	}
	function gesamt_summe($g_konto_id) {
		$result = mysql_query ( "SELECT SUM(BETRAG) AS SUMME FROM `UEBERWEISUNG` WHERE `A_KONTO_ID` = '$g_konto_id'  AND `AKTUELL` = '1' && DTAUS_ID IS NULL" );
		
		$numrows = mysql_numrows ( $result );
		if ($numrows > 0) {
			$row = mysql_fetch_assoc ( $result );
			return $row ['SUMME'];
		} else {
			return FALSE;
		}
	}
	function dtaus_von_konto_erstellen($g_konto_id) {
		$zeilen_arr = $this->dtaus_zeilen_arr ( $g_konto_id );
		if (! is_array ( $zeilen_arr )) {
			fehlermeldung_ausgeben ( "F�r die DTAUS-Erstellung sind keine Daten verf�gbar" );
		} else {
			$anzahl_zeilen = count ( $zeilen_arr );
			$g = new geldkonto_info ();
			$g->geld_konto_details ( $g_konto_id );
			
			echo "<table>";
			echo "<tr><td colspan=\"4\">�bersicht</td></tr>";
			echo "<tr><td>Art der Auftr�ge:</td><td>Gutschrift</td><td>Auftraggeber:</td><td>$g->geldkonto_bezeichnung</td></tr>";
			$datum = date ( "d.m.Y" );
			echo "<tr><td>Erstelldatum:</td><td>$datum</td><td>BLZ:</td><td>$g->blz</td></tr>";
			echo "<tr><td>Anzahl der Auftr�ge:</td><td>$anzahl_zeilen</td><td>Kontonummer:</td><td>$g->kontonummer</td></tr>";
			$gesamt_summe = $this->gesamt_summe ( $g_konto_id );
			$gesamt_summe = nummer_punkt2komma ( $gesamt_summe );
			echo "<tr><td>Gesamtsumme:</td><td>$gesamt_summe</td><td></td><td></td></tr>";
			echo "</table><br>";
			
			echo "<table>";
			echo "<tr><td><b>Zahlungsempf�nger</b></td><td align=\"right\"><b>Betrag/EUR</b></td><td><b>KtoNr.</b></td><td><b>BLZ</b></td><td><b>Verwendungszweck</b></td></tr>";
			
			for($a = 0; $a < $anzahl_zeilen; $a ++) {
				$g1 = new geldkonto_info ();
				$e_konto_id = $zeilen_arr [$a] ['E_KONTO_ID'];
				$datum = $zeilen_arr [$a] ['DATUM'];
				$betrag = $zeilen_arr [$a] ['BETRAG'];
				$betrag = nummer_punkt2komma ( $betrag );
				$vzweck1 = $zeilen_arr [$a] ['VZWECK1'];
				$vzweck2 = $zeilen_arr [$a] ['VZWECK2'];
				$vzweck3 = $zeilen_arr [$a] ['VZWECK3'];
				$buchungstext = $zeilen_arr [$a] ['BUCHUNGSTEXT'];
				$g1->geld_konto_details ( $e_konto_id );
				// echo " $g->kontonummer $g->blz | $g1->geldkonto_bezeichnung $g1->kontonummer $g1->blz $betrag $vzweck1 $vzweck2 $vzweck3<br>";
				echo "<tr><td>$g1->geldkonto_bezeichnung</td><td align=\"right\">$betrag</td><td>$g1->kontonummer</td><td>$g1->blz</td><td>$vzweck1</td></tr>";
			}
			
			echo "<tr><td><b>Gesamtsumme:</b></td><td align=\"right\"><b>$gesamt_summe</b></td><td colspan=\"3\"></td></tr>";
			echo "</table><br>";
			
			$this->dtaus_datei_erstellen ( $g_konto_id );
		}
	}
	function dtaus_datei_erstellen($g_konto_id) {
		include_once ('classes/class.dtaus.php'); // �berweisung oder Lastschrift
		include_once ('classes/class_dtaus_berlussimo.php'); // zum speichern
		
		$dtaus = new dtaus_berlus ();
		
		$zeilen_arr = $this->dtaus_zeilen_arr ( $g_konto_id );
		$anzahl_zeilen = count ( $zeilen_arr );
		
		$g = new geldkonto_info ();
		$g->geld_konto_details ( $g_konto_id );
		$beguenstgter = $dtaus->umbrueche_entfernen ( $g->konto_beguenstigter );
		$kontonr = $g->kontonummer;
		$blz = $g->blz;
		$institut = $g->kredit_institut;
		
		$dt = new dtaus ();
		// Wo soll die Kohle hin ? (Name, BLZ, Kontonummer)
		$dt->meineDaten ( $beguenstgter, $blz, $kontonr );
		
		for($a = 0; $a < $anzahl_zeilen; $a ++) {
			$g1 = new geldkonto_info ();
			$e_konto_id = $zeilen_arr [$a] ['E_KONTO_ID'];
			$datum = $zeilen_arr [$a] ['DATUM'];
			$betrag = $zeilen_arr [$a] ['BETRAG'];
			// $betrag = nummer_punkt2komma($betrag);
			$vzweck1 = $zeilen_arr [$a] ['VZWECK1'];
			$vzweck2 = $zeilen_arr [$a] ['VZWECK2'];
			$vzweck3 = $zeilen_arr [$a] ['VZWECK3'];
			$buchungstext = $zeilen_arr [$a] ['BUCHUNGSTEXT'];
			$g1->geld_konto_details ( $e_konto_id );
			$g1->konto_beguenstigter = $dtaus->umbrueche_entfernen ( $g1->konto_beguenstigter );
			$dt->lastschrift ( "$g1->konto_beguenstigter", "$g1->blz", "$g1->kontonummer", $betrag, $vzweck1, $vzweck2, $vzweck3 );
		}
		
		$string = $dt->doSome1 ( 'GK', '51000' );
		
		$letzte_dtaus_id = $this->letzte_dtaus_id ();
		$letzte_dtaus_id = $letzte_dtaus_id + 1;
		$filename = "$letzte_dtaus_id.txt";
		// $dtaus = new dtaus_berlus; //nach oben gewandert wegen umr�che entfernen
		$monat = date ( "m" );
		$jahr = date ( "Y" );
		
		$folder = "GELDKONTEN/$g_konto_id/UEBERWEISUNGEN/DTAUS/$jahr/$monat";
		/* Wenn erfolgreich gespeichert */
		if ($dtaus->dtaus_datei_speichern ( $folder, $filename, $string )) {
			echo "DTAUS-Datei $filename wurde gespeichert";
			$zugewiesene_l_id = $this->update_dtaus_id ( $g_konto_id );
			
			if ($zugewiesene_l_id !== $letzte_dtaus_id) {
				echo "FEHLER 606, DTAUS ERSTELLUNG (LETZTE_DTAUS_ID WURDE VER�NDERT";
			}
		}
		
		// ausgabe zum beispiel in einem textfeld f�r copy & paste
		// echo '<textarea class="content" style="width:900; height:1200;">' . $dt->doSome1('0128', 'GK', ' 51000') . '</textarea><br><br>';
	}
	function letzte_dtaus_id() {
		$result = mysql_query ( "SELECT DTAUS_ID  FROM `UEBERWEISUNG` WHERE  `AKTUELL` = '1' && DTAUS_ID IS NOT NULL ORDER BY DTAUS_ID DESC LIMIT 0,1" );
		$row = mysql_fetch_assoc ( $result );
		return $row ['DTAUS_ID'];
	}
	function update_dtaus_id($g_konto_id) {
		$letzte_dtaus_id = $this->letzte_dtaus_id ();
		$letzte_dtaus_id = $letzte_dtaus_id + 1;
		$datum = date ( "Y-m-d" );
		$result = mysql_query ( "UPDATE`UEBERWEISUNG`SET DTAUS_ID='$letzte_dtaus_id', DATUM='$datum' WHERE `A_KONTO_ID` = '$g_konto_id'  AND `AKTUELL` = '1' && DTAUS_ID IS NULL" );
		return $letzte_dtaus_id;
	}
	function dtaus_ids_arr($g_konto_id) {
		$result = mysql_query ( "SELECT DTAUS_ID, DATUM, BEZUGSTAB  FROM `UEBERWEISUNG` WHERE `AKTUELL`='1' && A_KONTO_ID='$g_konto_id'  && DTAUS_ID IS NOT NULL GROUP BY DTAUS_ID  ORDER BY DATUM DESC" );
		
		$numrows = mysql_numrows ( $result );
		if ($numrows > 0) {
			while ( $row = mysql_fetch_assoc ( $result ) )
				$my_array [] = $row;
			return $my_array;
		} else {
			return FALSE;
		}
	}
	function dtaus_dateien_anzeigen($g_konto_id) {
		$dtaus_id_arr = $this->dtaus_ids_arr ( $g_konto_id );
		if (! is_array ( $dtaus_id_arr )) {
			hinweis_ausgeben ( "Keine DTAUS Dateien vorhanden" );
		} else {
			$anzahl_zeilen = count ( $dtaus_id_arr );
			for($a = 0; $a < $anzahl_zeilen; $a ++) {
				$dtaus_id = $dtaus_id_arr [$a] ['DTAUS_ID'];
				$dateiname = "$dtaus_id.txt";
				$datum = $dtaus_id_arr [$a] ['DATUM'];
				$datum_arr = explode ( "-", $datum );
				$jahr = $datum_arr [0];
				$monat = $datum_arr [1];
				$verzeichnis = "GELDKONTEN/$g_konto_id/UEBERWEISUNGEN/DTAUS/$jahr/$monat/";
				$link_download = "<a href=\"GELDKONTEN/$g_konto_id/UEBERWEISUNGEN/DTAUS/$jahr/$monat/$dateiname\">DTAUS$dateiname</a>";
				$datum1 = date_mysql2german ( $datum );
				$link_ansicht = "<a href=\"?daten=ueberweisung&option=dtaus_ansicht&dtaus_id=$dtaus_id\">Ansicht</a>";
				$link_buchen = "<a href=\"?daten=ueberweisung&option=dtaus_buchen&dtaus_id=$dtaus_id\">Datei buchen</a>";
				$pdf_link = "<a href=\"?daten=ueberweisung&option=dtaus_ansicht_pdf&dtaus_id=$dtaus_id\"><img src=\"css/pdf.png\"></a>";
				$pdf_link1 = "<a href=\"?daten=ueberweisung&option=dtaus_ansicht_pdf&dtaus_id=$dtaus_id&no_logo\"><img src=\"css/pdf2.png\"></a>";
				
				$z = $a + 1;
				$bezugs_tab = $dtaus_id_arr [$a] ['BEZUGSTAB'];
				echo "$z. &nbsp;&nbsp; $datum1 &nbsp;&nbsp;$link_download&nbsp;&nbsp; $link_ansicht";
				if ($bezugs_tab != '') {
					echo "&nbsp;&nbsp;$link_buchen";
				}
				echo "&nbsp;$pdf_link &nbsp;$pdf_link1";
				echo "<br>";
			}
		}
	}
	function dtausdatei_zeilen_arr($dtaus_id) {
		$result = mysql_query ( "SELECT *  FROM `UEBERWEISUNG` WHERE `DTAUS_ID` = '$dtaus_id'  AND `AKTUELL` = '1'" );
		
		$numrows = mysql_numrows ( $result );
		if ($numrows > 0) {
			while ( $row = mysql_fetch_assoc ( $result ) )
				$my_array [] = $row;
			return $my_array;
		} else {
			return FALSE;
		}
	}
	function dtaus_gesamt_summe($dtaus_id) {
		$result = mysql_query ( "SELECT SUM(BETRAG) AS SUMME FROM `UEBERWEISUNG` WHERE `DTAUS_ID` = '$dtaus_id'  AND `AKTUELL` = '1'" );
		
		$numrows = mysql_numrows ( $result );
		if ($numrows > 0) {
			$row = mysql_fetch_assoc ( $result );
			return $row ['SUMME'];
		} else {
			return FALSE;
		}
	}
	function dtaus_summe_geldk($geldkonto_id) {
		$result = mysql_query ( "SELECT SUM(BETRAG) AS SUMME FROM `UEBERWEISUNG` WHERE `DTAUS_ID`  IS NULL  AND `AKTUELL` = '1' && A_KONTO_ID='$geldkonto_id' " );
		
		$numrows = mysql_numrows ( $result );
		if ($numrows > 0) {
			$row = mysql_fetch_assoc ( $result );
			return $row ['SUMME'];
		} else {
			return FALSE;
		}
	}
	function dtaus_datei_uebersicht($dtaus_id) {
		$zeilen_arr = $this->dtausdatei_zeilen_arr ( $dtaus_id );
		if (! is_array ( $zeilen_arr )) {
			fehlermeldung_ausgeben ( "DTAUS DATEI FEHLERHAFT, KEINE DATEN IN DER DATENBANK" );
		} else {
			$anzahl_zeilen = count ( $zeilen_arr );
			$g = new geldkonto_info ();
			$g_konto_id = $zeilen_arr [0] ['A_KONTO_ID'];
			$g->geld_konto_details ( $g_konto_id );
			
			echo "<table>";
			echo "<tr><td colspan=\"4\">�bersicht</td></tr>";
			echo "<tr><td>Art der Auftr�ge:</td><td>Gutschrift</td><td>Auftraggeber:</td><td>$g->geldkonto_bezeichnung</td></tr>";
			$datum = date ( "d.m.Y" );
			echo "<tr><td>Erstelldatum:</td><td>$datum</td><td>BLZ:</td><td>$g->blz</td></tr>";
			echo "<tr><td>Anzahl der Auftr�ge:</td><td>$anzahl_zeilen</td><td>Kontonummer:</td><td>$g->kontonummer</td></tr>";
			$gesamt_summe = $this->dtaus_gesamt_summe ( $dtaus_id );
			$gesamt_summe = nummer_punkt2komma ( $gesamt_summe );
			echo "<tr><td>Gesamtsumme:</td><td>$gesamt_summe</td><td></td><td></td></tr>";
			echo "</table><br>";
			
			echo "<table>";
			echo "<tr><td><b>Zahlungsempf�nger</b></td><td align=\"right\"><b>Betrag/EUR</b></td><td><b>KtoNr.</b></td><td><b>BLZ</b></td><td><b>Verwendungszweck</b></td></tr>";
			
			for($a = 0; $a < $anzahl_zeilen; $a ++) {
				$g1 = new geldkonto_info ();
				$e_konto_id = $zeilen_arr [$a] ['E_KONTO_ID'];
				$datum = $zeilen_arr [$a] ['DATUM'];
				$betrag = $zeilen_arr [$a] ['BETRAG'];
				$betrag = nummer_punkt2komma ( $betrag );
				$vzweck1 = $zeilen_arr [$a] ['VZWECK1'];
				$vzweck2 = $zeilen_arr [$a] ['VZWECK2'];
				$vzweck3 = $zeilen_arr [$a] ['VZWECK3'];
				$buchungstext = $zeilen_arr [$a] ['BUCHUNGSTEXT'];
				$g1->geld_konto_details ( $e_konto_id );
				// echo " $g->kontonummer $g->blz | $g1->geldkonto_bezeichnung $g1->kontonummer $g1->blz $betrag $vzweck1 $vzweck2 $vzweck3<br>";
				echo "<tr><td>$g1->geldkonto_bezeichnung</td><td align=\"right\">$betrag</td><td>$g1->kontonummer</td><td>$g1->blz</td><td>$vzweck1</td></tr>";
			}
			
			echo "<tr><td><b>Gesamtsumme:</b></td><td align=\"right\"><b>$gesamt_summe</b></td><td colspan=\"3\"></td></tr>";
			echo "</table><br>";
		}
	}
	function pdf_dtaus_datei_uebersicht($dtaus_id) {
		$zeilen_arr = $this->dtausdatei_zeilen_arr ( $dtaus_id );
		if (! is_array ( $zeilen_arr )) {
			fehlermeldung_ausgeben ( "DTAUS DATEI FEHLERHAFT, KEINE DATEN IN DER DATENBANK" );
		} else {
			$anzahl_zeilen = count ( $zeilen_arr );
			$g = new geldkonto_info ();
			$g_konto_id = $zeilen_arr [0] [A_KONTO_ID];
			$g->geld_konto_details ( $g_konto_id );
			
			$pdf = new Cezpdf ( 'a4', 'portrait' );
			$bpdf = new b_pdf ();
			$bpdf->b_header ( $pdf, 'Partner', $_SESSION ['partner_id'], 'portrait', 'pdfclass/fonts/Helvetica.afm', 6 );
			
			$pdf->ezText ( "�bersicht DTAUS �berweisungen - DTAUS-ID: $dtaus_id", 14 );
			$pdf->ezText ( "<b>Konto: $g->geldkonto_bezeichnung</b>", 10 );
			$pdf->ezText ( "Kontonr: $g->kontonummer      BLZ:$g->blz" );
			$datum = date ( "d.m.Y" );
			$pdf->ezText ( "Druckdatum: $datum" );
			$pdf->ezText ( "Zeilenanzahl: $anzahl_zeilen" );
			
			$gesamt_summe = $this->dtaus_gesamt_summe ( $dtaus_id );
			$gesamt_summe = nummer_punkt2komma ( $gesamt_summe );
			$pdf->ezText ( "Gesamtsume: $gesamt_summe" );
			$pdf->ezSetdy ( - 20 );
			
			$cols = array (
					'DATUM' => "Datum",
					'BUCHUNGSTEXT' => "Buchungstext",
					'VZWECK1' => 'Verwendung',
					'BETRAG' => 'Betrag' 
			);
			
			// $pdf->ezTable($zeilen_arr, $cols);
			$pdf->ezTable ( $zeilen_arr, $cols, "<b>�bersicht DTAUS</b>", array (
					'showHeadings' => 1,
					'shaded' => 1,
					'titleFontSize' => 8,
					'fontSize' => 7,
					'xPos' => 30,
					'xOrientation' => 'right',
					'width' => 550,
					'cols' => array (
							'BETRAG' => array (
									'justification' => 'right',
									'width' => 50 
							),
							'DATUM' => array (
									'justification' => 'left',
									'width' => 50 
							) 
					) 
			) );
			ob_clean (); // ausgabepuffer leeren
			header ( "Content-type: application/pdf" ); // wird von MSIE ignoriert
			$pdf->ezStream ();
		}
	}
	function form_dtaus_datei_buchen($dtaus_id) {
		$f = new formular ();
		$f->erstelle_formular ( '�berweisungen verbuchen', null );
		$zeilen_arr = $this->dtausdatei_zeilen_arr ( $dtaus_id );
		if (! is_array ( $zeilen_arr )) {
			fehlermeldung_ausgeben ( "DTAUS DATEI FEHLERHAFT, KEINE DATEN IN DER DATENBANK" );
		} else {
			$anzahl_zeilen = count ( $zeilen_arr );
			$g = new geldkonto_info ();
			$g_konto_id = $zeilen_arr [0] [A_KONTO_ID];
			$g->geld_konto_details ( $g_konto_id );
			
			echo "<table>";
			echo "<tr><td colspan=\"4\">�bersicht</td></tr>";
			echo "<tr><td>Art der Auftr�ge:</td><td>Gutschrift</td><td>Auftraggeber:</td><td>$g->geldkonto_bezeichnung</td></tr>";
			$datum = date ( "d.m.Y" );
			echo "<tr><td>Erstelldatum:</td><td>$datum</td><td>BLZ:</td><td>$g->blz</td></tr>";
			echo "<tr><td>Anzahl der Auftr�ge:</td><td>$anzahl_zeilen</td><td>Kontonummer:</td><td>$g->kontonummer</td></tr>";
			$gesamt_summe = $this->dtaus_gesamt_summe ( $dtaus_id );
			$gesamt_summe = nummer_punkt2komma ( $gesamt_summe );
			echo "<tr><td>Gesamtsumme:</td><td>$gesamt_summe</td><td></td><td></td></tr>";
			echo "</table><br>";
			
			echo "<table>";
			echo "<tr><td>Status</td><td><b>Zahlungsempf�nger</b></td><td align=\"right\"><b>Betrag/EUR</b></td><td><b>KtoNr.</b></td><td><b>BLZ</b></td><td><b>Verwendungszweck</b></td><td>Buchungstext</td></tr>";
			
			for($a = 0; $a < $anzahl_zeilen; $a ++) {
				$g1 = new geldkonto_info ();
				$u_dat = $zeilen_arr [$a] ['U_DAT'];
				$e_konto_id = $zeilen_arr [$a] ['E_KONTO_ID'];
				$datum = $zeilen_arr [$a] ['DATUM'];
				$betrag = $zeilen_arr [$a] ['BETRAG'];
				$betrag = nummer_punkt2komma ( $betrag );
				$vzweck1 = $zeilen_arr [$a] ['VZWECK1'];
				$vzweck2 = $zeilen_arr [$a] ['VZWECK2'];
				$vzweck3 = $zeilen_arr [$a] ['VZWECK3'];
				$buchungstext = $zeilen_arr [$a] ['BUCHUNGSTEXT'];
				$g1->geld_konto_details ( $e_konto_id );
				// echo " $g->kontonummer $g->blz | $g1->geldkonto_bezeichnung $g1->kontonummer $g1->blz $betrag $vzweck1 $vzweck2 $vzweck3<br>";
				
				/* Pr�fen ob Buchung als Zahlungsausgang gebucht wurde */
				$erfass_nr = $zeilen_arr [$a] ['BEZUGS_ID'];
				$f = new formular ();
				
				echo "<tr><td>";
				$r_typ = $this->check_r_typ ( $erfass_nr );
				if ($this->check_buchung ( $erfass_nr, $g_konto_id, $buchungstext )) {
					$fehler = true;
					echo "<a id=\"link_rot_fett\">gebucht</a> $r_typ";
				} else {
					// $buchungs_status = "<a>nicht gebucht</a>";
					if (! $this->check_mbuchung ( $g_konto_id, $buchungstext )) {
						echo "<a id=\"link_rot_fett\">nicht gebucht</a> $r_typ";
					} else {
						echo "manuell gebucht";
					}
				}
				
				echo "</td><td>$g1->geldkonto_bezeichnung</td><td align=\"right\">$betrag</td><td>$g1->kontonummer</td><td>$g1->blz</td><td>$vzweck1</td><td>$buchungstext</td></tr>";
			}
			
			echo "<tr><td></td><td><b>Gesamtsumme:</b></td><td align=\"right\"><b>$gesamt_summe</b></td><td colspan=\"4\"></td></tr>";
			echo "<tr id=\"tfoot\"><td colspan=\"7\">";
			if (! isset ( $fehler )) {
				$f->hidden_feld ( 'option', 'autobuchen' );
				$f->text_feld ( 'Kontoauszugsnr', 'kontoauszugsnr', '', 10, 'kontoauszugsnr', '' );
				$f->datum_feld ( 'Datum', 'datum', '', 'datum' );
				$f->send_button ( 'Buchen', 'Buchen' );
			} else {
				hinweis_ausgeben ( "Diese Datei oder ein Teil davon wurde schon gebucht!" );
			}
			echo "</td></tr>";
			echo "</table><br>";
		}
		$f->ende_formular ();
	}
	function check_buchung($erfass_nr, $geldkonto_id, $buchungstext) {
		$result = mysql_query ( "SELECT *  FROM  GELD_KONTO_BUCHUNGEN WHERE AKTUELL = '1' && ERFASS_NR='$erfass_nr' && GELDKONTO_ID='$geldkonto_id' && VERWENDUNGSZWECK='$buchungstext'" );
		$numrows = mysql_numrows ( $result );
		
		if ($numrows > 0) {
			return true;
		} else {
			return false;
		}
	}
	function check_mbuchung($geldkonto_id, $buchungstext) {
		$result = mysql_query ( "SELECT *  FROM  GELD_KONTO_BUCHUNGEN WHERE AKTUELL = '1' && GELDKONTO_ID='$geldkonto_id' && VERWENDUNGSZWECK='$buchungstext'" );
		$numrows = mysql_numrows ( $result );
		
		if ($numrows > 0) {
			return true;
		} else {
			return false;
		}
	}
	function check_r_typ($erfass_nr) {
		$result = mysql_query ( "SELECT RECHNUNGSTYP  FROM  RECHNUNGEN WHERE AKTUELL = '1' && BELEG_NR='$erfass_nr' " );
		$numrows = mysql_numrows ( $result );
		
		if ($numrows > 0) {
			$row = mysql_fetch_assoc ( $result );
			return $row ['RECHNUNGSTYP'];
		}
	}
	
	/*
	 * Array mit Kontierungsdaten einer Rechnung/Beleges f�r die Buchung einer Rechnung, wie kontiert
	 * vergleichen ob der �berwiesene Betrag Netto,. Brutto oder skontierter Betrag ist und danach buchen
	 */
	function ueberwiesene_rechnung_buchen($datum, $kto_auszugsnr, $belegnr, $vorzeichen, $rechnungs_betrag, $vzweck, $geldkonto_id) {
		$r = new rechnung (); // aus berlussimo_class
		$b = new buchen (); // ben�tigt zum verbuchen einzelner positionen nach kontierung
		$r->rechnung_grunddaten_holen ( $belegnr );
		$kontierungs_status = $r->rechnung_auf_kontierung_pruefen ( $belegnr );
		if ($kontierungs_status == 'vollstaendig') {
			$result = mysql_query ( "SELECT sum( GESAMT_SUMME - ( GESAMT_SUMME /100 * RABATT_SATZ ) ) AS NETTO, sum( (
(
GESAMT_SUMME - ( GESAMT_SUMME /100 * RABATT_SATZ ) ) /100
) * ( 100 + MWST_SATZ )
) AS BRUTTO, sum( (
(
(
GESAMT_SUMME - ( GESAMT_SUMME /100 * RABATT_SATZ ) ) /100
) * ( 100 + MWST_SATZ ) /100
) * ( 100 - SKONTO )
) AS SKONTO_BETRAG, KONTENRAHMEN_KONTO, KOSTENTRAEGER_TYP, KOSTENTRAEGER_ID, MWST_SATZ
FROM `KONTIERUNG_POSITIONEN`
WHERE BELEG_NR = '$belegnr' && AKTUELL = '1'
GROUP BY KOSTENTRAEGER_TYP, KOSTENTRAEGER_ID, KONTENRAHMEN_KONTO" ) or die ( mysql_error () );
			
			$numrows = mysql_numrows ( $result );
			if ($numrows > 0) {
				while ( $row = mysql_fetch_assoc ( $result ) ) {
					// $my_array[] = $row;
					// $art_bez = $r->kontierungsartikel_holen($belegnr, $pos);
					// $vzweck_neu = "ERFNR:$belegnr, Position $pos,"." $menge x $art_bez";
					$kostentraeger_typ = $row ['KOSTENTRAEGER_TYP'];
					$kostentraeger_id = $row ['KOSTENTRAEGER_ID'];
					$kostenkonto = $row ['KONTENRAHMEN_KONTO'];
					$netto = sprintf ( "%01.2f", $row ['NETTO'] );
					$brutto = sprintf ( "%01.2f", $row ['BRUTTO'] );
					$skonto = sprintf ( "%01.2f", $row ['SKONTO_BETRAG'] );
					$mwst_satz = $row ['MWST_SATZ'];
					
					/* Netto ohne MWST buchen */
					if ($rechnungs_betrag == $r->rechnungs_netto) {
						$b->geldbuchung_speichern_rechnung ( $datum, $kto_auszugsnr, $belegnr, $vorzeichen . $netto, $vzweck, $geldkonto_id, $kostentraeger_typ, $kostentraeger_id, $kostenkonto );
					}
					/* Brutto oder Skontiert = brutto, also OHNE SKONTO */
					if ($rechnungs_betrag == $r->rechnungs_brutto) {
						$mwst = ($brutto / (100 + $mwst_satz)) * $mwst_satz;
						$b->geldbuchung_speichern_rechnung ( $datum, $kto_auszugsnr, $belegnr, $vorzeichen . $brutto, $vzweck, $geldkonto_id, $kostentraeger_typ, $kostentraeger_id, $kostenkonto, $vorzeichen . $mwst );
					}
					/* Skontiert buchen */
					if ($rechnungs_betrag == $r->rechnungs_skontobetrag && $rechnungs_betrag < $r->rechnungs_brutto) {
						$mwst = ($skonto / (100 + $mwst_satz)) * $mwst_satz;
						$b->geldbuchung_speichern_rechnung ( $datum, $kto_auszugsnr, $belegnr, $vorzeichen . $skonto, $vzweck, $geldkonto_id, $kostentraeger_typ, $kostentraeger_id, $kostenkonto, $vorzeichen . $mwst );
					}
				} // end while
			} // end if $numrows
			echo "RECHNUNG $belegnr wurde gebucht!<br>";
			// weiterleiten_in_sec('?daten=buchen&option=buchungs_journal', 2);
		}  // end if
else {
			fehlermeldung_ausgeben ( "FEHLER: Kontierung $kontierungs_status ->Erfassungsnr: $belegnr" );
			// weiterleiten_in_sec("?daten=rechnungen&option=rechnung_kontieren&belegnr=$belegnr", 3);
		}
	} // end function
	function get_gezahlter_betrag($dtaus_id, $belegnr) {
		$result = mysql_query ( " SELECT SUM( BETRAG ) AS SUMME FROM `UEBERWEISUNG` WHERE `DTAUS_ID` = '$dtaus_id' && BEZUGSTAB = 'Rechnung' && BEZUGS_ID = '$belegnr' AND `AKTUELL` = '1' " );
		
		$numrows = mysql_numrows ( $result );
		if ($numrows > 0) {
			$row = mysql_fetch_assoc ( $result );
			return $row ['SUMME'];
		}
	}
	function dtaus_zeilen_arr_holen($dtaus_id) {
		$result = mysql_query ( "SELECT *  FROM `UEBERWEISUNG` WHERE `DTAUS_ID` = '$dtaus_id'  AND `AKTUELL` = '1'" );
		
		$numrows = mysql_numrows ( $result );
		if ($numrows > 0) {
			while ( $row = mysql_fetch_assoc ( $result ) )
				$my_array [] = $row;
			return $my_array;
		} else {
			return FALSE;
		}
	}
	function autobuchen_zahlung($dtaus_id, $datum, $kto_auszugsnr) {
		$rechnungen_arr = $this->dtaus_zeilen_arr_holen ( $dtaus_id );
		$anzahl = count ( $rechnungen_arr );
		if ($anzahl > 0) {
			for($a = 0; $a < $anzahl; $a ++) {
				$u_dat = $rechnungen_arr [$a] ['U_DAT'];
				$ueberwiesener_betrag = $rechnungen_arr [$a] ['BETRAG'];
				$a_konto_id = $rechnungen_arr [$a] ['A_KONTO_ID'];
				$buchungstext = $rechnungen_arr [$a] ['BUCHUNGSTEXT'];
				$vorzeichen = '-';
				$bezugs_tab = $rechnungen_arr [$a] ['BEZUGSTAB'];
				$belegnr = $rechnungen_arr [$a] ['BEZUGS_ID'];
				if ($bezugs_tab == 'RECHNUNG') {
					/* Wie kontiert buchen */
					$this->ueberwiesene_rechnung_buchen ( $datum, $kto_auszugsnr, $belegnr, $vorzeichen, $ueberwiesener_betrag, $buchungstext, $a_konto_id );
					/* In der Tab UEBERWEISUNGEN Kontoauszugsnr und Datum updaten */
					$this->update_ueberweisung_dat ( $u_dat, $datum, $kto_auszugsnr );
					$r = new rechnung ();
					$r->rechnung_als_freigegeben ( $belegnr );
					$r->rechnung_als_gezahlt ( $belegnr, $datum );
				}
			} // end for
		} else {
			die ( "Kein Ihnalt in der DTAUS-DATEI" );
		}
	}
	function update_ueberweisung_dat($u_dat, $datum, $kto_auszugsnr) {
		$result = mysql_query ( "UPDATE `UEBERWEISUNG` SET AUSZUGSNR='$kto_auszugsnr', AUSZUGS_DATUM='$datum' WHERE `U_DAT` = '$u_dat'" );
	}
	function form_ueberweisung_manuell() {
		if (empty ( $_SESSION [geldkonto_id] ) or empty ( $_SESSION [partner_id] )) {
			die ( 'ABBRUCH - Partner und Geldkonto w�hlen!!!' );
		} else {
			$g = new geldkonto_info ();
			$konto_id = $_SESSION [geldkonto_id];
			$g->geld_konto_details ( $konto_id );
			$b = new buchen ();
			$f = new formular ();
			$f->fieldset ( "Sammel�berweisung erfassen vom $g->geldkonto_bezeichnung_kurz", 'su' );
			$b->dropdown_geldkonten_alle ( 'Empf�ngergeldkonto w�hlen' );
			// $f->text_feld('Beg�nstigter', 'beguenstigter', '', 50, 'beg', '');
			// $f->text_feld('Kontonummer', 'kontonummer', '', 15, 'knr', '');
			// $f->text_feld('BLZ', 'blz', '', 15, 'blz', '');
			// $f->text_feld('Kreditinstitut', 'Kreditinstitut', '', 50, 'ki', '');
			$f->text_feld ( 'Betrag', 'betrag', '', 15, 'betrag', '' );
			$f->text_feld ( 'Verwendungszweck1', 'vzweck1', '', 30, 'vzweck1', '' );
			$f->text_feld ( 'Verwendungszweck2', 'vzweck2', '', 30, 'vzweck2', '' );
			$f->text_feld ( 'Verwendungszweck3', 'vzweck3', '', 30, 'vzweck3', '' );
			$f->send_button ( 'sb', 'in Pool' );
			$f->hidden_feld ( 'option', 'ue_send' );
			$f->fieldset_ende ();
		}
	}
} // end class

?>
