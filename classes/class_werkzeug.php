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
//if (file_exists ( 'pdfclass/class.ezpdf.php' )) {
//	include_once ('pdfclass/class.ezpdf.php');
//}
if (file_exists ( 'classes/class_bpdf.php' )) {
	include_once ('classes/class_bpdf.php');
}

/* Klasse "formular" für Formularerstellung laden */
if (file_exists ( "classes/class_formular.php" )) {
	include_once ("classes/class_formular.php");
}
if (file_exists ( "classes/berlussimo_class.php" )) {
	include_once ("classes/berlussimo_class.php");
}

if (file_exists ( "classes/class_benutzer.php" )) {
	include_once ("classes/class_benutzer.php");
}
class werkzeug {
	function form_werkzeug_hizu() {
		$f = new formular ();
		$f->erstelle_formular ( 'Werkzeug hinzufügen', '' );
		$f->text_feld ( 'INTBelegnr', 'beleg_id', '', '20', 'beleg_id', '' );
		$f->text_feld ( 'Postition', 'pos', '', '10', 'pos', '' );
		$js = '';
		$f->button_js ( 'btn_hnz', 'Hinzufügen', $js );
		$f->ende_formular ();
	}
	function werkzeugliste($b_id = NULL) {
		$link_NACH_MIT = "<a href=\"?daten=benutzer&option=werkzeugliste_nach_mitarbeiter&b_id=$b_id\">ÜBERSICHT NACH MITARBEITER</a>";
		echo $link_NACH_MIT . '<br>';
		$f = new formular ();
		$f->fieldset ( 'Werkzeugliste', 'wl' );
		$arr = $this->werkzeugliste_arr ( $b_id );
		if (is_array ( $arr )) {
			$anz = count ( $arr );
			if ($b_id != NULL) {
				$link_rueckgabe_alle = "<a href=\"?daten=benutzer&option=werkzeug_rueckgabe_alle&b_id=$b_id\">Rückgabe vermerken</a>";
				$link_rueckgabe_alle_pdf = "<a href=\"?daten=benutzer&option=werkzeug_rueckgabe_alle_pdf&b_id=$b_id\">Rückgabe PDF</a>";
				$link_ausgabe_alle_pdf = "<a href=\"?daten=benutzer&option=werkzeug_ausgabe_alle_pdf&b_id=$b_id\">Ausgabeschein PDF</a>";
				echo "$link_ausgabe_alle_pdf | $link_rueckgabe_alle_pdf | $link_rueckgabe_alle<br><br>";
			}
			echo "<table class=\"sortable\">";
			echo "<tr><th>LIEFERANT</th><th>WBNR</th><th>BESCHREIBUNG</th><th>KURZINFO</th><th>MENGE</th><th>MITARBITER</th><th>OPTION</th></tr>";
			for($a = 0; $a < $anz; $a ++) {
				$w_id = $arr [$a] ['ID'];
				$beleg_id = $arr [$a] ['BELEG_ID'];
				$art_nr = $arr [$a] ['ARTIKEL_NR'];
				$pos = $arr [$a] ['POS'];
				$menge = $arr [$a] ['MENGE'];
				$kurzinfo = $arr [$a] ['KURZINFO'];
				
				$r = new rechnung ();
				$r->rechnung_grunddaten_holen ( $beleg_id );
				$katalog_info = $r->artikel_info ( $r->rechnungs_aussteller_id, $art_nr );
				$art_info = $katalog_info [0] ['BEZEICHNUNG'];
				
				$lieferant = $r->rechnungs_aussteller_name;
				$link_beleg = "<a href=\"?daten=rechnungen&option=rechnungs_uebersicht&belegnr=$beleg_id\">$lieferant</a>";
				$wb_nr = 'W-' . $w_id;
				echo "<tr><td>$link_beleg</td><td>$wb_nr</td><td>$art_info</td><td>$kurzinfo</td><td>$menge</td>";
				
				$b_id = $arr [$a] ['BENUTZER_ID'];
				if ($b_id) {
					$bb = new benutzer ();
					$bb->get_benutzer_infos ( $b_id );
					$link_mitarbeiter_liste = "<a href=\"?daten=benutzer&option=werkzeuge_mitarbeiter&b_id=$b_id\">$bb->benutzername</a>";
					echo "<td>$link_mitarbeiter_liste</td>";
				} else {
					$link_frei = "<a href=\"?daten=benutzer&option=werkzeug_zuweisen&w_id=$w_id\">Zuweisen</a>";
					echo "<td>FREI $link_frei</td>";
				}
				if ($b_id == NULL) {
					$link_loeschen = "<a href=\"?daten=benutzer&option=werkzeug_raus&w_id=$w_id\">Aus Liste Löschen</td>";
				} else {
					$link_loeschen = "<a href=\"?daten=benutzer&option=werkzeug_rueckgabe&w_id=$w_id&b_id=$b_id\">Einzelrückgabe</td>";
				}
				echo "<td>$link_loeschen</td>";
				echo "</tr>";
			}
			echo "</table>";
			// echo "<pre>";
			// print_r($arr);
		} else {
			echo "Keine Werkzeuge im Werkzeugpool!";
		}
		$f->fieldset_ende ();
	}
	function pdf_werkzeug_rueckgabe_einzel($b_id, $w_id, $scheintext = 'Einzelrückgabeschein') {
		$arr = $this->werkzeugliste_arr ( $b_id );
		$pdf = new Cezpdf ( 'a4', 'landscape' );
		$bpdf = new b_pdf ();
		$bpdf->b_header ( $pdf, 'Partner', $_SESSION ['partner_id'], 'landscape', 'Helvetica.afm', 6 );
		
		$anz = count ( $arr );
		for($a = 0; $a < $anz; $a ++) {
			$beleg_id = $arr [$a] ['BELEG_ID'];
			$art_nr = $arr [$a] ['ARTIKEL_NR'];
			$pos = $arr [$a] ['POS'];
			$menge = $arr [$a] ['MENGE'];
			$kurzinfo = $arr [$a] ['KURZINFO'];
			$id = $arr [$a] ['ID'];
			
			$r = new rechnung ();
			$r->rechnung_grunddaten_holen ( $beleg_id );
			$katalog_info = $r->artikel_info ( $r->rechnungs_aussteller_id, $art_nr );
			$art_info = $katalog_info [0] ['BEZEICHNUNG'];
			
			$lieferant = $r->rechnungs_aussteller_name;
			$bb = new benutzer ();
			$bb->get_benutzer_infos ( $b_id );
			if ($w_id == $id) {
				$arr_n [$a] ['LI'] = $lieferant;
				$arr_n [$a] ['WBNR'] = 'W-' . $w_id;
				$arr_n [$a] ['ART'] = $art_nr;
				$arr_n [$a] ['ART_INFO'] = $art_info;
				$arr_n [$a] ['KURZINFO'] = $kurzinfo;
				$arr_n [$a] ['MENGE'] = $menge;
				$arr_n [$a] ['OK'] = '';
			}
		}
		
		$pdf->ezText ( "<b>$scheintext Mitarbeiter: $bb->benutzername</b>", 14, array (
				'left' => '0' 
		) );
		$datum = date ( "d.m.Y" );
		$pdf->ezText ( "Datum $datum", 10, array (
				'left' => '0' 
		) );
		
		$pdf->ezSetDy ( - 12 ); // abstand
		
		/* Spaltendefinition */
		$cols = array (
				'LI' => "<b>LIEFERANT</b>",
				'WBNR' => "<b>WBNR</b>",
				'ART' => "<b>ARTNR</b>",
				'ART_INFO' => "<b>BEZEICHNUNG</b>",
				'KURZINFO' => "<b>HINWEIS</b>",
				'MENGE' => "<b>MENGE</b>",
				'OK' => "<b>i.O</b>" 
		);
		
		/* Tabellenparameter */
		$tableoptions = array (
				'width' => 600,
				'xPos' => 410,
				'shaded' => 0, // shaded: 0-->Zeile 1 & Zeile 2 --> weiss 1-->Zeile 1 = weiss Zeile 2= grau 2-->Zeile 1= grauA Zeile 2= grauB
				'showHeadings' => 1, // zeig Überschriften der spalten
				'showLines' => 0, // Mach Linien
				'lineCol' => array (
						0.0,
						0.0,
						0.0 
				), // Linienfarbe, hier schwarz
				
				'fontSize' => 8, // schriftgroesse
				'titleFontSize' => 8, // schriftgroesse Überschrift
				'splitRows' => 0,
				'protectRows' => 0,
				'innerLineThickness' => 0.5,
				'outerLineThickness' => 0.5,
				'rowGap' => 1,
				'colGap' => 1,
				'cols' => array (
						'LI' => array (
								'justification' => 'left',
								'width' => 50 
						),
						'ART' => array (
								'justification' => 'left',
								'width' => 50 
						),
						'ART_INFO' => array (
								'justification' => 'left',
								'width' => 50 
						),
						'KURZINFO' => array (
								'justification' => 'right',
								'width' => 50 
						),
						'MENGE' => array (
								'justification' => 'right',
								'width' => 50 
						),
						'OK' => array (
								'justification' => 'left',
								'width' => 50 
						) 
				) 
		);
		
		$pdf->ezTable ( $arr_n, $cols, $tableoptions );
		$pdf->ezSetDy ( - 12 ); // abstand
		$pdf->ezText ( "______________________________", 10, array (
				'left' => '0' 
		) );
		$pdf->ezText ( "Unterschrift $bb->benutzername", 8, array (
				'left' => '20' 
		) );
		
		$pdf->ezSetDy ( 20 ); // abstand
		$pdf->ezText ( "______________________________", 10, array (
				'left' => '500' 
		) );
		$bb->get_benutzer_infos ( $_SESSION ['benutzer_id'] );
		$pdf->ezText ( "$ein_ausgabe_text von $bb->benutzername", 8, array (
				'left' => '520' 
		) );
		
		$this->werkzeug_austragen ( $w_id );
		ob_clean (); // ausgabepuffer leeren
		
		$pdf->ezStream ();
	}
	function get_anzahl_werkzeuge($art_nr, $beleg_id) {
		$db_abfrage = "SELECT * FROM WERKZEUGE WHERE ARTIKEL_NR='$art_nr' && BELEG_ID='$beleg_id' && AKTUELL='1'";
		$result = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			return $numrows;
		} else {
			return '0';
		}
	}
	function werkzeugliste_arr($b_id = NULL) {
		if ($b_id == NULL) {
			$db_abfrage = "SELECT * FROM WERKZEUGE WHERE AKTUELL='1' ORDER BY ID, ARTIKEL_NR, ID, KURZINFO";
		} else {
			$db_abfrage = "SELECT * FROM WERKZEUGE WHERE AKTUELL='1' && BENUTZER_ID='$b_id' ORDER BY ID, ARTIKEL_NR, ID, KURZINFO";
		}
		$result = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			while ( $row = mysql_fetch_assoc ( $result ) )
				$my_arr [] = $row;
			return $my_arr;
		}
	}
	function werkzeugliste_verteilt_arr() {
		$db_abfrage = "SELECT * FROM WERKZEUGE WHERE AKTUELL='1' && BENUTZER_ID IS NOT NULL ORDER BY BENUTZER_ID, ARTIKEL_NR";
		$result = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			while ( $row = mysql_fetch_assoc ( $result ) )
				$my_arr [] = $row;
			return $my_arr;
		}
	}
	function werkzeug_loeschen($w_id) {
		$db_abfrage = "UPDATE WERKZEUGE SET AKTUELL='0' WHERE AKTUELL='1' && ID='$w_id'";
		// echo $db_abfrage;
		$result = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		echo "Werkzeug aus Liste entfernt!";
	}
	function werkzeug_austragen($w_id) {
		$db_abfrage = "UPDATE WERKZEUGE SET BENUTZER_ID=NULL WHERE AKTUELL='1' && ID='$w_id'";
		// echo $db_abfrage;
		$result = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		echo "Werkzeug aus Liste entfernt!";
	}
	function werkzeug_rueckgabe_alle($b_id) {
		$db_abfrage = "UPDATE WERKZEUGE SET BENUTZER_ID=NULL WHERE AKTUELL='1' && BENUTZER_ID='$b_id'";
		// echo $db_abfrage;
		$result = mysql_query ( $db_abfrage ) or die ( mysql_error () );
	}
	function pdf_rueckgabeschein_alle($b_id, $scheintext = 'Rückgabeschein', $ein_ausgabe_text = 'Bearbeitet') {
		$arr = $this->werkzeugliste_arr ( $b_id );
		$pdf = new Cezpdf ( 'a4', 'landscape' );
		$bpdf = new b_pdf ();
		$bpdf->b_header ( $pdf, 'Partner', $_SESSION ['partner_id'], 'landscape', 'Helvetica.afm', 6 );
		
		$anz = count ( $arr );
		for($a = 0; $a < $anz; $a ++) {
			$beleg_id = $arr [$a] ['BELEG_ID'];
			$art_nr = $arr [$a] ['ARTIKEL_NR'];
			$pos = $arr [$a] ['POS'];
			$menge = $arr [$a] ['MENGE'];
			$kurzinfo = $arr [$a] ['KURZINFO'];
			$w_id = $arr [$a] ['ID'];
			
			$r = new rechnung ();
			$r->rechnung_grunddaten_holen ( $beleg_id );
			$katalog_info = $r->artikel_info ( $r->rechnungs_aussteller_id, $art_nr );
			$art_info = $katalog_info [0] ['BEZEICHNUNG'];
			
			$lieferant = $r->rechnungs_aussteller_name;
			$bb = new benutzer ();
			$bb->get_benutzer_infos ( $b_id );
			$arr_n [$a] ['LI'] = $lieferant;
			$arr_n [$a] ['WBNR'] = 'W-' . $w_id;
			$arr_n [$a] ['ART'] = $art_nr;
			$arr_n [$a] ['ART_INFO'] = $art_info;
			$arr_n [$a] ['KURZINFO'] = $kurzinfo;
			$arr_n [$a] ['MENGE'] = $menge;
			$arr_n [$a] ['OK'] = '';
		}
		
		$pdf->ezText ( "<b>$scheintext Mitarbeiter: $bb->benutzername</b>", 14, array (
				'left' => '0' 
		) );
		$datum = date ( "d.m.Y" );
		$pdf->ezText ( "Datum $datum", 10, array (
				'left' => '0' 
		) );
		
		$pdf->ezSetDy ( - 12 ); // abstand
		
		/* Spaltendefinition */
		$cols = array (
				'LI' => "<b>LIEFERANT</b>",
				'WBNR' => "<b>WBNR</b>",
				'ART' => "<b>ARTNR</b>",
				'ART_INFO' => "<b>BEZEICHNUNG</b>",
				'KURZINFO' => "<b>HINWEIS</b>",
				'MENGE' => "<b>MENGE</b>",
				'OK' => "<b>i.O</b>" 
		);
		
		/* Tabellenparameter */
		$tableoptions = array (
				'width' => 600,
				'xPos' => 410,
				'shaded' => 0, // shaded: 0-->Zeile 1 & Zeile 2 --> weiss 1-->Zeile 1 = weiss Zeile 2= grau 2-->Zeile 1= grauA Zeile 2= grauB
				'showHeadings' => 1, // zeig Überschriften der spalten
				'showLines' => 0, // Mach Linien
				'lineCol' => array (
						0.0,
						0.0,
						0.0 
				), // Linienfarbe, hier schwarz
				
				'fontSize' => 8, // schriftgroesse
				'titleFontSize' => 8, // schriftgroesse Überschrift
				'splitRows' => 0,
				'protectRows' => 0,
				'innerLineThickness' => 0.5,
				'outerLineThickness' => 0.5,
				'rowGap' => 1,
				'colGap' => 1,
				'cols' => array (
						'LI' => array (
								'justification' => 'left',
								'width' => 50 
						),
						'ART' => array (
								'justification' => 'left',
								'width' => 50 
						),
						'ART_INFO' => array (
								'justification' => 'left',
								'width' => 50 
						),
						'KURZINFO' => array (
								'justification' => 'right',
								'width' => 50 
						),
						'MENGE' => array (
								'justification' => 'right',
								'width' => 50 
						),
						'OK' => array (
								'justification' => 'left',
								'width' => 50 
						) 
				) 
		);
		
		$pdf->ezTable ( $arr_n, $cols, $tableoptions );
		$pdf->ezSetDy ( - 12 ); // abstand
		$pdf->ezText ( "______________________________", 10, array (
				'left' => '0' 
		) );
		$pdf->ezText ( "Unterschrift $bb->benutzername", 8, array (
				'left' => '20' 
		) );
		
		$pdf->ezSetDy ( 20 ); // abstand
		$pdf->ezText ( "______________________________", 10, array (
				'left' => '500' 
		) );
		$bb->get_benutzer_infos ( $_SESSION ['benutzer_id'] );
		$pdf->ezText ( "$ein_ausgabe_text von $bb->benutzername", 8, array (
				'left' => '520' 
		) );
		
		ob_clean (); // ausgabepuffer leeren
		$pdf->ezStream ();
	}
	function form_werkzeug_zuweisen($w_id) {
		$f = new formular ();
		$f->erstelle_formular ( 'Werkzeug hinzufügen', '' );
		$f->hidden_feld ( 'w_id', $w_id );
		$bb = new benutzer ();
		$this->get_werkzeug_info ( $w_id );
		$f->text_feld_inaktiv ( 'Bezeichnung', 'w', $this->werkzeug_bez, 100, 'wbz' );
		$js = '';
		$bb->dropdown_benutzer2 ( 'Mitarbeiter wählen', 'b_id', 'b_id', $js );
		$f->hidden_feld ( 'option', 'werkzeug_zuweisen_snd' );
		$f->send_button ( 'btn_snd', 'Zuweisen' );
		$f->ende_formular ();
	}
	function werkzeug_zuweisen($b_id, $w_id) {
		$db_abfrage = "UPDATE WERKZEUGE SET BENUTZER_ID='$b_id' WHERE ID='$w_id' && AKTUELL='1'";
		$result = mysql_query ( $db_abfrage ) or die ( mysql_error () );
	}
	function get_werkzeug_info($w_id) {
		$db_abfrage = "SELECT * FROM WERKZEUGE WHERE ID='$w_id' ORDER BY DAT";
		$result = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		$numrows = mysql_numrows ( $result );
		unset ( $this->werkzeug_bez );
		unset ( $this->lieferant );
		if ($numrows) {
			$row = mysql_fetch_assoc ( $result );
			$beleg_id = $row ['BELEG_ID'];
			$art_nr = $row ['ARTIKEL_NR'];
			$pos = $row ['POS'];
			$menge = $row ['MENGE'];
			$kurzinfo = $row ['KURZINFO'];
			
			$r = new rechnung ();
			$r->rechnung_grunddaten_holen ( $beleg_id );
			$katalog_info = $r->artikel_info ( $r->rechnungs_aussteller_id, $art_nr );
			$this->werkzeug_bez = $katalog_info [0] ['BEZEICHNUNG'];
			$this->lieferant = $r->rechnungs_aussteller_name;
		}
	}
	function werkzeugliste_nach_mitarbeiter() {
		$arr = $this->werkzeugliste_verteilt_arr ();
		if (is_array ( $arr )) {
			$anz = count ( $arr );
			// echo "<table class=\"sortable\">";
			// echo "<tr><th>LIEFERANT</th><th>WBNR</th><th>BESCHREIBUNG</th><th>KURZINFO</th><th>MENGE</th><th>MITARBITER</th><th>OPTION</th></tr>";
			$tmp_b_id = '';
			for($a = 0; $a < $anz; $a ++) {
				$w_id = $arr [$a] ['ID'];
				$beleg_id = $arr [$a] ['BELEG_ID'];
				$art_nr = $arr [$a] ['ARTIKEL_NR'];
				$pos = $arr [$a] ['POS'];
				$menge = $arr [$a] ['MENGE'];
				$kurzinfo = $arr [$a] ['KURZINFO'];
				
				$r = new rechnung ();
				$r->rechnung_grunddaten_holen ( $beleg_id );
				$katalog_info = $r->artikel_info ( $r->rechnungs_aussteller_id, $art_nr );
				$art_info = $katalog_info [0] ['BEZEICHNUNG'];
				
				$lieferant = $r->rechnungs_aussteller_name;
				$link_beleg = "<a href=\"?daten=rechnungen&option=rechnungs_uebersicht&belegnr=$beleg_id\">$lieferant</a>";
				$wb_nr = 'W-' . $w_id;
				if ($tmp_b_id != $b_id && $a != 0) {
					$tmp_b_id = $b_id;
					echo "<table class=\"sortable\">";
					echo "<tr><th>LIEFERANT</th><th>WBNR</th><th>BESCHREIBUNG</th><th>KURZINFO</th><th>MENGE</th><th>MITARBITER</th><th>OPTION</th></tr>";
				}
				echo "<tr><td>$link_beleg</td><td>$wb_nr</td><td>$art_info</td><td>$kurzinfo</td><td>$menge</td>";
				
				$b_id = $arr [$a] ['BENUTZER_ID'];
				
				if ($b_id) {
					$bb = new benutzer ();
					$bb->get_benutzer_infos ( $b_id );
					$link_mitarbeiter_liste = "<a href=\"?daten=benutzer&option=werkzeuge_mitarbeiter&b_id=$b_id\">$bb->benutzername</a>";
					echo "<td>$link_mitarbeiter_liste</td>";
				} else {
					$link_frei = "<a href=\"?daten=benutzer&option=werkzeug_zuweisen&w_id=$w_id\">Zuweisen</a>";
					echo "<td>FREI $link_frei</td>";
				}
				if ($b_id == NULL) {
					$link_loeschen = "<a href=\"?daten=benutzer&option=werkzeug_raus&w_id=$w_id\">Aus Liste Löschen</td>";
				} else {
					$link_loeschen = "<a href=\"?daten=benutzer&option=werkzeug_rueckgabe&w_id=$w_id&b_id=$b_id\">Einzelrückgabe</td>";
				}
				echo "<td>$link_loeschen</td>";
				echo "</tr>";
			}
			echo "</table>";
		}
	}
} // end class

?>

