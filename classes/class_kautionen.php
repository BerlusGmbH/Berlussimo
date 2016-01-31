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
 * @filesource   $HeadURL: http://192.168.2.52/svn/berlussimo_1/tags/02.11.2010 - Downloadversion 0.27/classes/class_kautionen.php $
 * @version      $Revision: 15 $
 * @modifiedby   $LastChangedBy: sivac $
 * @lastmodified $Date: 2011-07-07 10:41:33 +0200 (Do, 07 Jul 2011) $
 * 
 */
include_once ("config.inc.php");
class kautionen {
	function get_kautionsbetrag($mietvertrag_id) {
		$this->kautions_betrag = 0;
		$result = mysql_query ( "SELECT DETAIL_INHALT FROM DETAIL WHERE DETAIL_ZUORDNUNG_TABELLE='MIETVERTRAG' && DETAIL_ZUORDNUNG_ID = '$mietvertrag_id' && DETAIL_AKTUELL = '1' && DETAIL_NAME LIKE '%Kaution%' ORDER BY DETAIL_DAT DESC LIMIT 0,1" );
		$row = mysql_fetch_assoc ( $result );
		$this->kautions_betrag = $row ['DETAIL_INHALT'];
		if ($this->kautions_betrag == '') {
			$this->kautions_betrag = 'Keine Kautionsdaten';
		} else {
			$this->kautions_betrag = $this->kautions_betrag . ' €';
		}
		return $this->kautions_betrag;
	}
	function kautionen_info($kostentraeger_typ, $kostentraeger_id, $kostenkonto) {
		$this->anzahl_zahlungen = 0;
		unset ( $this->kautionszahlungen_array );
		if (is_array ( $this->kautionszahlungen_arr ( $kostentraeger_typ, $kostentraeger_id, $kostenkonto ) )) {
			$this->kautionszahlungen_array = $this->kautionszahlungen_arr ( $kostentraeger_typ, $kostentraeger_id, $kostenkonto );
			$this->anzahl_zahlungen = count ( $this->kautionszahlungen_array );
		}
	}
	function kautionszahlungen_arr($kostentraeger_typ, $kostentraeger_id, $kautions_konto_id) {
		// $result = mysql_query ("SELECT DATUM, BETRAG FROM KAUTIONS_BUCHUNGEN WHERE KOSTENTRAEGER_TYP='$kostentraeger_typ' && KOSTENTRAEGER_ID='$kostentraeger_id' ORDER BY DATUM");
		$result = mysql_query ( "SELECT DATUM, BETRAG, VERWENDUNGSZWECK FROM GELD_KONTO_BUCHUNGEN WHERE KOSTENTRAEGER_TYP='$kostentraeger_typ' && KOSTENTRAEGER_ID='$kostentraeger_id' && GELDKONTO_ID='$kautions_konto_id' && AKTUELL='1' ORDER BY DATUM" );
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			while ( $row = mysql_fetch_assoc ( $result ) )
				$my_arr [] = $row;
			return $my_arr;
		} else {
			return false;
		}
	}
	function kautionszahlungen_alle_arr($konto) {
		// $result = mysql_query ("SELECT DATUM, BETRAG, KOSTENTRAEGER_TYP, KOSTENTRAEGER_ID FROM KAUTIONS_BUCHUNGEN ORDER BY KOSTENTRAEGER_TYP, KOSTENTRAEGER_ID, DATUM ASC");
		if (empty ( $_SESSION [geldkonto_id] )) {
			die ( "Kautionskonto wählen" );
		} else {
			
			$gk_id = $_SESSION [geldkonto_id];
			$result = mysql_query ( "SELECT DATUM, BETRAG, KOSTENTRAEGER_TYP, KOSTENTRAEGER_ID FROM GELD_KONTO_BUCHUNGEN  WHERE   AKTUELL='1' && GELDKONTO_ID='$gk_id' ORDER BY KOSTENTRAEGER_TYP, KOSTENTRAEGER_ID, DATUM ASC" );
			$numrows = mysql_numrows ( $result );
			if ($numrows) {
				while ( $row = mysql_fetch_assoc ( $result ) )
					$my_arr [] = $row;
				return $my_arr;
			} else {
				return false;
			}
		}
	}
	function kautionszahlungen_alle_arr_bis($datum_bis) {
		// $result = mysql_query ("SELECT DATUM, BETRAG, KOSTENTRAEGER_TYP, KOSTENTRAEGER_ID FROM KAUTIONS_BUCHUNGEN ORDER BY KOSTENTRAEGER_TYP, KOSTENTRAEGER_ID, DATUM ASC");
		if (empty ( $_SESSION [geldkonto_id] )) {
			die ( "Kautionskonto wählen" );
		} else {
			
			$gk_id = $_SESSION [geldkonto_id];
			$result = mysql_query ( "SELECT DATUM, BETRAG, KOSTENTRAEGER_TYP, KOSTENTRAEGER_ID, VERWENDUNGSZWECK FROM GELD_KONTO_BUCHUNGEN  WHERE  DATUM<='$datum_bis' && AKTUELL='1' && GELDKONTO_ID='$gk_id' && KONTENRAHMEN_KONTO!='2002' && KONTENRAHMEN_KONTO!='2003' && KONTENRAHMEN_KONTO!='2004' ORDER BY KOSTENTRAEGER_TYP, KOSTENTRAEGER_ID, DATUM ASC" );
			$numrows = mysql_numrows ( $result );
			if ($numrows) {
				while ( $row = mysql_fetch_assoc ( $result ) )
					$my_arr [] = $row;
				return $my_arr;
			} else {
				return false;
			}
		}
	}
	function form_hochrechnung_mv($mietvertrag_id) {
		$mv = new mietvertraege ();
		$mv->get_mietvertrag_infos_aktuell ( $mietvertrag_id );
		
		$f = new formular ();
		$f->erstelle_formular ( "Kautionshochrechnung $mv->einheit_kurzname $mv->personen_name_string", NULL );
		$f->datum_feld ( 'Gewünschtes Auszahlungsdatum', 'datum_bis', "", 'datum_bis' );
		$f->hidden_feld ( "mietvertrag_id", "$mietvertrag_id" );
		$f->hidden_feld ( "option", "hochrechnung_mv" );
		$f->send_button ( "submit", "Berechnen" );
		$f->ende_formular ();
	}
	function form_kautionsbuchung_mieter($mietvertrag_id) {
		$mv = new mietvertraege ();
		$mv->get_mietvertrag_infos_aktuell ( $mietvertrag_id );
		$mietekalt_1 = $this->summe_mietekalt ( $mietvertrag_id );
		$kaution = nummer_punkt2komma ( $mietekalt_1 * 3 );
		$f = new formular ();
		$f->erstelle_formular ( "Kautionen buchen für $mv->einheit_kurzname $mv->personen_name_string", NULL );
		$f->datum_feld ( 'Datum', 'datum', "", 'datum' );
		$f->text_feld ( 'Betrag', 'betrag', "$kaution", '10', 'betrag', '' );
		$f->text_bereich ( 'Buchungstext', 'text', "", '10', 'text', '' );
		$f->hidden_feld ( "mietvertrag_id", "$mietvertrag_id" );
		$f->hidden_feld ( "option", "kaution_gesendet" );
		$f->send_button ( "submit", "Buchen" );
		$f->ende_formular ();
	}
	function kautionsberechnung($kostentraeger_typ, $kostentraeger_id, $datum_bis, $zins_pj, $kap_prozent, $soli_prozent) {
		// echo "$kostentraeger_typ, $kostentraeger_id, $datum_bis, $zins_pj, $kap_prozent, $soli_prozent";
		if (! empty ( $_SESSION [geldkonto_id] )) {
			$zahlungen_arr = $this->kautionszahlungen_arr ( $kostentraeger_typ, $kostentraeger_id, $_SESSION [geldkonto_id] );
		} else {
			die ( "Kautionskonto wählen" );
		}
		$summe = 0.00;
		$summe_verzinst = 0.00;
		
		if ($kostentraeger_typ == 'Mietvertrag' or $kostentraeger_typ == 'MIETVERTRAG') {
			$mv = new mietvertraege ();
			$mv->get_mietvertrag_infos_aktuell ( $kostentraeger_id );
		}
		
		/*
		 * if($mv->mietvertrag_aktuell == false){
		 * $datum_bis = $mv->mietvertrag_bis;
		 * }
		 */
		
		$datum_bis_a = date_mysql2german ( $datum_bis );
		
		if (is_array ( $zahlungen_arr )) {
			echo "<table>";
			$pdf_link = "<a href=\"?daten=kautionen&option=hochrechner_pdf&mietvertrag_id=$kostentraeger_id&datum_bis=$datum_bis_a\">PDF</a>";
			echo "<tr class=\"feldernamen\"><td colspan=\"7\">$mv->einheit_kurzname $mv->personen_name_string $pdf_link</td></tr>";
			echo "<tr class=\"feldernamen\"><td>EINZAHLUNG</td><td>ZINSTAGE</td><td>BETRAG</td><td>VERZINST BIS $datum_bis_a</td><td>KAP $kap_prozent %</td><td>SOLI $soli_prozent %</td><td>BETRAG</td></tr>";
			$anzahl_zahlungen = count ( $zahlungen_arr );
			for($a = 0; $a < $anzahl_zahlungen; $a ++) {
				$datum_von = $zahlungen_arr [$a] ['DATUM'];
				$betrag = $zahlungen_arr [$a] ['BETRAG'];
				if ($betrag > 0.00) {
					$datum_von_a = date_mysql2german ( $datum_von );
					$zinstage = $this->zins_tage ( $datum_von, $datum_bis );
					
					$betrag_verzinst = nummer_runden ( ($betrag * $zins_pj * $zinstage) / 360 + $betrag, 3 );
					$kap = nummer_runden ( ($betrag_verzinst - $betrag) * $kap_prozent / 100, 3 );
					$soli = nummer_runden ( $kap * $soli_prozent / 100, 3 );
					$betrag_rein = nummer_runden ( $betrag_verzinst - $kap - $soli, 3 );
					
					echo "<tr><td>$datum_von_a</td><td>$zinstage</td><td>$betrag</td><td>$betrag_verzinst</td><td>$kap</td><td>$soli</td><td>$betrag_rein</td>";
					$summe = $summe + $betrag;
					$summe_verzinst = $summe_verzinst + $betrag_verzinst;
				} else {
					$datum_von_a = date_mysql2german ( $datum_von );
					$zinstage = $this->zins_tage ( $datum_von, $datum_bis );
					
					$betrag_verzinst = nummer_runden ( ($betrag * $zins_pj * $zinstage) / 360 + $betrag, 3 );
					$kap = nummer_runden ( ($betrag_verzinst - $betrag) * $kap_prozent / 100, 3 );
					$soli = nummer_runden ( $kap * $soli_prozent / 100, 3 );
					$betrag_rein = nummer_runden ( $betrag_verzinst - $kap - $soli, 3 );
					
					echo "<tr><td>$datum_von_a</td><td>$zinstage</td><td>$betrag</td><td>$betrag_verzinst</td><td>$kap</td><td>$soli</td><td>$betrag_rein</td>";
					$summe = $summe + $betrag;
					$summe_verzinst = $summe_verzinst + $betrag_verzinst;
				}
			}
			
			$summe = nummer_kuerzen ( $summe, 3 );
			
			$kap_g = ($summe_verzinst - $summe) * ($kap_prozent / 100);
			$soli_g = $kap_g * $soli_prozent / 100;
			
			// echo "$summe $summe_verzinst $kap_g $soli_g<br>";
			$endsumme = nummer_runden ( $summe_verzinst - $kap_g - $soli_g, 2 );
			echo "<tr class=\"feldernamen\"><td colspan=\"5\" >$datum_bis_a</td><td>SUMME</td><td>$endsumme</td></tr>";
			
			$this->anfangs_summe = $summe;
			$this->end_summe = $endsumme;
			$this->kap_g = $kap_g;
			$this->soli_g = $soli_g;
			
			echo "</table>";
			
			// echo "<b>ENDSUMME $endsumme</b><br>";
		} else {
			$gk = new geldkonto_info ();
			$gk->geld_konto_details ( $_SESSION [geldkonto_id] );
			echo "Keine kautionszahlungen auf <b>$gk->geldkonto_bezeichnung</b> gebucht.";
		}
	}
	function kautionsberechnung_2($betrag, $datum_von, $datum_bis, $zins_pj, $kap_prozent, $soli_prozent) {
		$zinstage = $this->zins_tage_ok ( $datum_von, $datum_bis );
		$betrag_verzinst = ($betrag * $zins_pj * $zinstage) / 360 + $betrag;
		$kap = ($betrag_verzinst - $betrag) * $kap_prozent / 100;
		$soli = $kap * $soli_prozent / 100;
		$betrag_rein = nummer_komma2punkt ( nummer_punkt2komma ( ($betrag_verzinst - $kap - $soli) ) );
		
		$datum_von_a = date_mysql2german ( $datum_von );
		$datum_bis_a = date_mysql2german ( $datum_bis );
		echo "<table>";
		echo "<tr><td>$datum_von_a</td><td>$zinstage</td><td>$betrag</td><td>$betrag_verzinst</td><td>$kap</td><td>$soli</td><td>$betrag_rein</td>";
		echo "</table>";
	}
	function kautionsberechnung_pdf($kostentraeger_typ, $kostentraeger_id, $datum_bis, $zins_pj, $kap_prozent, $soli_prozent) {
		ob_clean (); // ausgabepuffer leeren
		//include_once ('pdfclass/class.ezpdf.php');
		include_once ('classes/class_bpdf.php');
		$pdf = new Cezpdf ( 'a4', 'portrait' );
		$bpdf = new b_pdf ();
		$bpdf->b_header ( $pdf, 'Partner', $_SESSION [partner_id], 'portrait', 'Helvetica.afm', 6 );
		
		if (! empty ( $_SESSION [geldkonto_id] )) {
			$zahlungen_arr = $this->kautionszahlungen_arr ( $kostentraeger_typ, $kostentraeger_id, $_SESSION [geldkonto_id] );
		} else {
			die ( "Kautionskonto wählen" );
		}
		$summe = 0.00;
		$summe_verzinst = 0.00;
		
		if ($kostentraeger_typ == 'Mietvertrag') {
			$mv = new mietvertraege ();
			$mv->get_mietvertrag_infos_aktuell ( $kostentraeger_id );
		}
		
		$datum_bis_a = date_mysql2german ( $datum_bis );
		
		if (is_array ( $zahlungen_arr )) {
			
			$cols = array (
					'DATUM' => "Einzahlung",
					'BETRAG' => "Betrag",
					'ZINSTAGE' => "Zinstage",
					'AUSZAHLUNG' => "Auszahlung",
					'BETRAG_VERZINST' => "Verzinst bis $datum_bis_a ($zins_pj%)",
					'VERWENDUNGSZWECK' => "Buchungstext",
					'KAP' => "KAP $kap_prozent%",
					'SOLI' => "Soli $soli_prozent %",
					'BETRAG_REIN' => "Auszahlung" 
			);
			
			// echo "<table>";
			// echo "<tr class=\"feldernamen\"><td colspan=\"7\">$mv->einheit_kurzname $mv->personen_name_string</td></tr>";
			// echo "<tr class=\"feldernamen\"><td>EINZAHLUNG</td><td>ZINSTAGE</td><td>BETRAG</td><td>VERZINST BIS $datum_bis_a</td><td>KAP $kap_prozent %</td><td>SOLI $soli_prozent %</td><td>BETRAG</td></tr>";
			$anzahl_zahlungen = count ( $zahlungen_arr );
			for($a = 0; $a < $anzahl_zahlungen; $a ++) {
				$datum_von = $zahlungen_arr [$a] ['DATUM'];
				$betrag = $zahlungen_arr [$a] ['BETRAG'];
				$vzweck = $zahlungen_arr [$a] [VERWENDUNGSZWECK];
				if ($betrag > 0.00) {
					$datum_von_a = date_mysql2german ( $datum_von );
					$zinstage = $this->zins_tage ( $datum_von, $datum_bis );
					
					$betrag_verzinst = nummer_runden ( ($betrag * $zins_pj * $zinstage) / 360 + $betrag, 3 );
					$kap = nummer_runden ( ($betrag_verzinst - $betrag) * $kap_prozent / 100, 3 );
					$soli = nummer_runden ( $kap * $soli_prozent / 100, 3 );
					$betrag_rein = nummer_runden ( $betrag_verzinst - $kap - $soli, 3 );
					
					$table_arr [$a] [DATUM] = $datum_von_a;
					$table_arr [$a] [ZINSTAGE] = $zinstage;
					$table_arr [$a] [BETRAG] = $betrag;
					$table_arr [$a] [AUSZAHLUNG] = $datum_bis_a;
					$table_arr [$a] [BETRAG_VERZINST] = $betrag_verzinst;
					$table_arr [$a] [KAP] = $kap;
					$table_arr [$a] [SOLI] = $soli;
					$table_arr [$a] [BETRAG_REIN] = $betrag_rein;
					$table_arr [$a] [VERWENDUNGSZWECK] = $vzweck;
					
					// echo "<tr><td>$datum_von_a</td><td>$zinstage</td><td>$betrag</td><td>$betrag_verzinst</td><td>$kap</td><td>$soli</td><td>$betrag_rein</td>";
					$summe = $summe + $betrag;
					$summe_verzinst = $summe_verzinst + $betrag_verzinst;
				} else {
					$datum_von_a = date_mysql2german ( $datum_von );
					$zinstage = $this->zins_tage ( $datum_von, $datum_bis );
					
					$betrag_verzinst = nummer_runden ( ($betrag * $zins_pj * $zinstage) / 360 + $betrag, 3 );
					$kap = nummer_runden ( ($betrag_verzinst - $betrag) * $kap_prozent / 100, 3 );
					$soli = nummer_runden ( $kap * $soli_prozent / 100, 3 );
					$betrag_rein = nummer_runden ( $betrag_verzinst - $kap - $soli, 3 );
					
					$table_arr [$a] [DATUM] = $datum_von_a;
					$table_arr [$a] [ZINSTAGE] = $zinstage;
					$table_arr [$a] [BETRAG] = $betrag;
					$table_arr [$a] [AUSZAHLUNG] = $datum_bis_a;
					$table_arr [$a] [BETRAG_VERZINST] = $betrag_verzinst;
					$table_arr [$a] [KAP] = $kap;
					$table_arr [$a] [SOLI] = $soli;
					$table_arr [$a] [BETRAG_REIN] = $betrag_rein;
					$vzweck = $zahlungen_arr [$a] [VERWENDUNGSZWECK];
					
					// echo "<tr><td>$datum_von_a</td><td>$zinstage</td><td>$betrag</td><td>$betrag_verzinst</td><td>$kap</td><td>$soli</td><td>$betrag_rein</td>";
					$summe = $summe + $betrag;
					$summe_verzinst = $summe_verzinst + $betrag_verzinst;
				}
			}
			
			$summe = nummer_kuerzen ( $summe, 3 );
			
			$kap_g = ($summe_verzinst - $summe) * ($kap_prozent / 100);
			$soli_g = $kap_g * $soli_prozent / 100;
			
			// echo "$summe $summe_verzinst $kap_g $soli_g<br>";
			$endsumme = nummer_runden ( $summe_verzinst - $kap_g - $soli_g, 2 );
			// echo "<tr class=\"feldernamen\"><td colspan=\"5\" >$datum_bis_a</td><td>SUMME</td><td>$endsumme</td></tr>";
			
			$anzahl_zeilen = count ( $table_arr );
			$l_zeile = $anzahl_zeilen + 1;
			$table_arr [$a] [SOLI] = '<b>GESAMT</b>';
			$endsumme_a = nummer_punkt2komma ( $endsumme );
			$table_arr [$a] [BETRAG_REIN] = "<b>$endsumme_a</b>";
			
			$pdf->ezTable ( $table_arr, $cols, "Kautionsberechnung $mv->einheit_kurzname $mv->personen_name_string", array (
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
							'BETRAG' => array (
									'justification' => 'right',
									'width' => 50 
							),
							'VERWENDUNGSZWECK' => array (
									'justification' => 'right' 
							) 
					) 
			) );
			
			$this->anfangs_summe = $summe;
			$this->end_summe = $endsumme;
			$this->kap_g = $kap_g;
			$this->soli_g = $soli_g;
			
			// echo "</table>";
			
			// echo "<b>ENDSUMME $endsumme</b><br>";
			header ( "Content-type: application/pdf" ); // wird von MSIE ignoriert
			$pdf->ezStream ();
		} else {
			$gk = new geldkonto_info ();
			$gk->geld_konto_details ( $_SESSION [geldkonto_id] );
			echo "Keine kautionszahlungen auf <b>$gk->geldkonto_bezeichnung</b> gebucht.";
		}
	}
	
	/*
	 *
	 *
	 * $kap = ($zins - $betrag_vormonat)*($kap_prozent/$kap_prozent);
	 * $soli = $kap*$soli_prozent/100;
	 *
	 * #$betrag_ende_monat = $betrag_ende_monat + $kap + $soli;
	 *
	 * $kap_a = nummer_kuerzen($kap,3);
	 * $soli_a = nummer_kuerzen($soli,3);
	 * $b_vm = $betrag_vormonat;
	 * $b_em = nummer_kuerzen($zins,3);
	 * #echo "$a. $betrag_vormonat $betrag_ende_monat<br>";
	 * #echo "<b> REST $zinstage tage<br> $zins $b_vm $kap_a $soli_a $b_em</b><br>";
	 */
	function zinsen($kaution, $zins_pj, $datum_von, $datum_bis) {
		// $kaution = '589.27';
		// $zins_pj ='6'; //%
		
		// $alt = strtotime("2009-01-01") ;
		$alt = strtotime ( "$datum_von" );
		echo "<br>";
		// $aktuell = strtotime("2009-12-31") ;
		$aktuell = strtotime ( "$datum_bis" );
		// $aktuell = strtotime(date("Y-m-d")) ;
		
		$differenz = $aktuell - $alt;
		$differenz = $differenz / 86400;
		
		echo "Tage: $differenz<br>";
		
		// ###
		$datum_von_arr = explode ( '-', $datum_von );
		$datum_von_jahr = $datum_von_arr [0];
		$datum_von_monat = $datum_von_arr [1];
		$datum_von_tag = $datum_von_arr [2];
		
		$resttage_anfang = 30 - $datum_von_tag + 1;
		
		$datum_bis_arr = explode ( '-', $datum_bis );
		$datum_bis_jahr = $datum_bis_arr [0];
		$datum_bis_monat = $datum_bis_arr [1];
		$datum_bis_tag = $datum_bis_arr [2];
		
		$resttage_ende = 30 - $datum_bis_tag + 1;
		
		$jahre = $datum_bis_jahr - $datum_von_jahr;
		if ($jahre > 0) {
			$monate_1_jahr = 12 - $datum_von_monat;
			$monate_end_jahr = $datum_bis_monat;
			$monate_gesamt = $monate_1_jahr + $monate_end_jahr;
			$jahre = $jahre - 1;
		} else {
			$monate_1_jahr = 12 - $datum_von_monat;
			$monate_end_jahr = $datum_bis_monat;
			$monate_gesamt = $monate_end_jahr - $monate_1_jahr;
			$jahre = 0;
		}
		
		$tage_gesamt = $resttage_anfang + $resttage_ende;
		
		echo "<br>M$monate_gesamt = $monate_1_jahr + $monate_end_jahr<br>";
		
		echo "$tage_gesamt Tage $monate_gesamt Monate $jahre Jahre";
		$tage_aus_monaten = $monate_gesamt * 30;
		// $ta
		$tage_gesamt = $tage_gesamt + $tage_aus_monaten;
		echo "<br>Gesamttage = $tage_gesamt<br>";
		// ###
		
		$anlege_jahre = 1;
		$anlege_monate = 0;
		$anlege_tage = 10;
		
		$kap_prozent = 25;
		$soli_prozent = 5.5;
		
		// $gesamt_tage = ($anlege_jahre * 360) + ($anlege_monate * 30) + $anlege_tage;
		$gesamt_tage = $differenz;
		$berechnungs_monate = $gesamt_tage / 30;
		$berechnungs_monate_voll = intval ( $berechnungs_monate );
		$rest_tage = $gesamt_tage - ($berechnungs_monate_voll * 30);
		
		echo "<b>$berechnungs_monate_voll Monate und $rest_tage Tage</b><br>";
		// =SUMME(C11*0,005*30)/360+C11
		
		$betrag_vormonat = $kaution;
		for($a = 1; $a <= $berechnungs_monate_voll; $a ++) {
			/* =(C11*0,005*30)/360+C11 */
			$betrag_ende_monat = ($betrag_vormonat * $zins_pj * 30) / 360 + $betrag_vormonat;
			$kap = ($betrag_ende_monat - $betrag_vormonat) * $kap_prozent / $kap_prozent;
			$soli = $kap * $soli_prozent / 100;
			
			// $betrag_ende_monat = $betrag_ende_monat + $kap + $soli;
			
			$kap_a = nummer_kuerzen ( $kap, 3 );
			$soli_a = nummer_kuerzen ( $soli, 3 );
			$b_vm = nummer_kuerzen ( $betrag_vormonat, 3 );
			$b_em = nummer_kuerzen ( $betrag_ende_monat, 3 );
			/*
			 * $kap_a = nummer_punkt2komma($kap);
			 * $soli_a = nummer_punkt2komma($soli);
			 * $b_vm = nummer_punkt2komma($betrag_vormonat);
			 * $b_em = nummer_punkt2komma($betrag_ende_monat);
			 */
			
			// echo "$a. $betrag_vormonat $betrag_ende_monat<br>";
			echo " Monat $a.           $b_vm      $kap_a      $soli_a   $b_em<br>";
			// echo "$a. $betrag_vormonat $betrag_ende_monat<br>";
			$betrag_vormonat = $betrag_ende_monat;
		}
		
		if ($rest_tage > 0) {
			
			$betrag_ende_monat = ($betrag_vormonat * $zins_pj * $rest_tage) / 360 + $betrag_vormonat;
			$kap = ($betrag_ende_monat - $betrag_vormonat) * $kap_prozent / $kap_prozent;
			$soli = $kap * $soli_prozent / 100;
			
			// $betrag_ende_monat = $betrag_ende_monat + $kap + $soli;
			
			$kap_a = nummer_punkt2komma ( $kap );
			$soli_a = nummer_punkt2komma ( $soli );
			$b_vm = nummer_punkt2komma ( $betrag_vormonat );
			$b_em = nummer_punkt2komma ( $betrag_ende_monat );
			// echo "$a. $betrag_vormonat $betrag_ende_monat<br>";
			echo "<b> REST $rest_tage tage<br>           $b_vm      $kap_a      $soli_a   $b_em</b><br>";
			// echo "$a. $betrag_vormonat $betrag_ende_monat<br>";
			$betrag_vormonat = $betrag_ende_monat;
		}
		
		return $betrag_ende_monat;
		/* Beispiel: Kaution Euro 1000 bei Mietdauer 1 Jahr und 14 Tagen: 1000 : 100 : 1 Jahr x Zins 3 x 1 Jahr = Euro 30. Folgejahr: 1030 : 100 : 360 x 3 x 14 Tage = 0,40 Euro - Der Mieter erhält mit Zinseszinses vom Vermieter Euro 1.030,40 zurück! */
	}
	function zins_tage_alt($datum_von, $datum_bis) {
		include_once ('classes/class_urlaub.php');
		$u = new urlaub ();
		$tage = $u->tage_zwischen ( $datum_von, $datum_bis );
		return $tage;
	}
	function zinstag($datum) {
		list ( $y, $m, $d ) = explode ( '-', $datum );
		if ($m == 2 && $d == ($y % 4 ? 28 : ($y % 100 ? 29 : ($y % 400 ? 28 : 29))))
			$d = 30;
		return 360 * $y + 30 * $m + min ( 30, $d );
	}
	function zins_tage($von, $bis) {
		// $von = "2010-06-18";
		// $bis = "2010-12-31";
		$von1 = $this->zinstag ( $von );
		$bis1 = $this->zinstag ( $bis );
		$differenz = $bis1 - $von1;
		// if($von == "2010-06-18"){
		// die("$differenz = $bis1 - $von1;");
		// }
		return $differenz;
	}
	function zins_tage_ok($datum_von, $datum_bis) {
		$datum_von_arr = explode ( '-', $datum_von );
		$datum_von_jahr = $datum_von_arr [0];
		$datum_von_monat = $datum_von_arr [1];
		$datum_von_tag = $datum_von_arr [2];
		
		$datum_bis_arr = explode ( '-', $datum_bis );
		$datum_bis_jahr = $datum_bis_arr [0];
		$datum_bis_monat = $datum_bis_arr [1];
		$datum_bis_tag = $datum_bis_arr [2];
		
		/* Gleiches Jahr */
		if ($datum_von_jahr == $datum_bis_jahr) {
			$jahre = 0;
			if ($datum_bis_monat == $datum_von_monat) {
				$zinstage = $datum_bis_tag - $datum_von_tag;
				// echo "<h1>ZINSTAGE gleiches jahr $zinstage</h1>";
			}
			
			if ($datum_bis_monat - $datum_von_monat == 1) {
				$zinstage = $datum_bis_tag - $datum_von_tag + 30;
				// echo "<h1>ZINSTAGE2 gleiches jahr $zinstage</h1>";
			}
			
			if ($datum_bis_monat - $datum_von_monat > 1) {
				$monate = $datum_bis_monat - $datum_von_monat - 1;
				$zinstage = ($monate * 30) + $datum_bis_tag + (30 - $datum_von_tag);
				// echo "<h1>ZINSTAGE2 gleiches jahr $zinstage</h1>";
			}
		}
		
		/* Nächstes Jahr */
		if ($datum_von_jahr < $datum_bis_jahr) {
			$jahre = $datum_bis_jahr - $datum_von_jahr;
			if ($jahre == 1) {
				$monate = $datum_bis_monat + (12 - $datum_von_monat);
				$zinstage = (30 - $datum_von_tag) - (30 - $datum_bis_tag) - 1;
				$zinstage = ($monate * 30) + $zinstage;
			}
			
			if ($jahre > 1) {
				$monate = $datum_bis_monat + (12 - $datum_von_monat);
				if ($monate < 12) {
					$jahre = $jahre - 1;
				}
				$zinstage_jahr = $jahre * 360;
				$zinstage = (30 - $datum_von_tag) - (30 - $datum_bis_tag);
				$zinstage = ($monate * 30) + $zinstage + $zinstage_jahr;
			}
			
			// echo "$monate $zinstage";
		}
		if ($zinstage) {
			return $zinstage;
		} else {
			return '0';
		}
	}
	function summe_mietekalt($mv_id) {
		$result = mysql_query ( "SELECT SUM(BETRAG) AS SUMME FROM MIETENTWICKLUNG WHERE KOSTENTRAEGER_TYP='Mietvertrag' && KOSTENTRAEGER_ID = '$mv_id' && MIETENTWICKLUNG_AKTUELL = '1' && KOSTENKATEGORIE='Miete kalt'  ORDER BY ANFANG ASC LIMIT 0,1" );
		
		$numrows = mysql_numrows ( $result );
		if (! $numrows) {
			return false;
		} else {
			$row = mysql_fetch_assoc ( $result );
			return $row ['SUMME'];
		}
	}
	function kaution_speichern($datum, $kostentraeger_typ, $kostentraeger_id, $betrag, $text, $konto) {
		$db_abfrage = "INSERT INTO KAUTIONS_BUCHUNGEN VALUES (NULL, '$kostentraeger_typ', '$kostentraeger_id','$datum', '$betrag','$text', '$konto')";
		$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		if ($resultat) {
			/* Protokollieren */
			$last_dat = mysql_insert_id ();
			protokollieren ( 'KAUTIONS_BUCHUNGEN', $last_dat, '0' );
			echo "<br>Kaution wurde gespeichert";
		} else {
			echo "<br>Kaution wurde NICHT gespeichert";
		}
	}
	function kontohochrechnung($datum_bis, $zins_pj, $kap_prozent, $soli_prozent) {
		$bg = new berlussimo_global ();
		$link = '?daten=kautionen&option=kontohochrechnung';
		
		$jahr = date ( "Y" );
		
		// echo "$kostentraeger_typ, $kostentraeger_id, $datum_bis, $zins_pj, $kap_prozent, $soli_prozent";
		
		// $zahlungen_arr = $this->kautionszahlungen_alle_arr('');
		
		// $zahlungen_arr = $this->kautionszahlungen_alle_arr(1000);
		$summe = 0.00;
		$summe_verzinst = 0.00;
		
		if (! empty ( $_REQUEST [monat] ) && ! empty ( $_REQUEST [jahr] )) {
			if (empty ( $_REQUEST [tag] )) {
				$l_tag = letzter_tag_im_monat ( $_REQUEST [monat], $_REQUEST [jahr] );
			} else {
				$l_tag = $_REQUEST [tag];
			}
			$datum_bis = "$_REQUEST[jahr]-$_REQUEST[monat]-$l_tag";
			$jahr = $_REQUEST [jahr];
		}
		
		$bg->monate_jahres_links ( $jahr, $link );
		$datum_bis_a = date_mysql2german ( $datum_bis );
		
		$zahlungen_arr = $this->kautionszahlungen_alle_arr_bis ( $datum_bis );
		
		$pdf_link = "<a href=\"?daten=kautionen&option=kontohochrechnung&datum_bis=$datum_bis_a&pdf\"><img src=\"css/pdf.png\"></a>";
		
		if (is_array ( $zahlungen_arr )) {
			echo "<table>";
			// echo "<tr class=\"feldernamen\"><td colspan=\"7\">$mv->einheit_kurzname $mv->personen_name_string</td></tr>";
			echo "<tr><th colspan=\"9\">$pdf_link</th></tr>";
			echo "<tr><th>BESCHREIBUNG</th><th>Einheit</th><th>EINZAHLUNG</t><th>ZINSTAGE</th><th>BETRAG</th><th>VERZINST BIS $datum_bis_a</th><th>KAP $kap_prozent %</th><th>SOLI $soli_prozent %</th><th>BETRAG</th></tr>";
			$anzahl_zahlungen = count ( $zahlungen_arr );
			
			for($a = 0; $a < $anzahl_zahlungen; $a ++) {
				$kostentraeger_typ = $zahlungen_arr [$a] ['KOSTENTRAEGER_TYP'];
				$kostentraeger_id = $zahlungen_arr [$a] ['KOSTENTRAEGER_ID'];
				$b_text = $zahlungen_arr [$a] ['VERWENDUNGSZWECK'];
				
				if ($kostentraeger_typ == 'MIETVERTRAG' or $kostentraeger_typ == 'Mietvertrag') {
					$mv = new mietvertraege ();
					$mv->get_mietvertrag_infos_aktuell ( $kostentraeger_id );
				}
				$r = new rechnung ();
				$kos_bez = $r->kostentraeger_ermitteln ( $kostentraeger_typ, $kostentraeger_id );
				
				/*
				 * if($mv->mietvertrag_aktuell == false){
				 * $datum_bis = $mv->mietvertrag_bis;
				 * }
				 */
				
				$datum_von = $zahlungen_arr [$a] ['DATUM'];
				$betrag = $zahlungen_arr [$a] ['BETRAG'];
				
				$datum_von_a = date_mysql2german ( $datum_von );
				
				$zinstage = $this->zins_tage ( $datum_von, $datum_bis );
				
				$betrag_verzinst = nummer_runden ( ($betrag * $zins_pj * $zinstage) / 360 + $betrag, 3 );
				$kap = nummer_runden ( ($betrag_verzinst - $betrag) * $kap_prozent / 100, 3 );
				$soli = nummer_runden ( $kap * $soli_prozent / 100, 3 );
				$betrag_rein = nummer_runden ( $betrag_verzinst - $kap - $soli, 2 );
				$summe_kos_id += $betrag_rein;
				
				$summe_kos_id_a = nummer_punkt2komma ( $summe_kos_id );
				
				// if($kostentraeger_typ == 'MIETVERTRAG' or $kostentraeger_typ == 'Mietvertrag'){
				if ($mv->einheit_kurzname == '') {
					$mv->einheit_kurzname = $b_text;
				}
				echo "<tr><td>$kos_bez</td><td>$mv->einheit_kurzname</td><td>$datum_von_a</td><td>$zinstage</td><td>$betrag</td><td>$betrag_verzinst</td><td>$kap</td><td>$soli</td><td>$betrag_rein</td></tr>";
				$tab_arr [$a] ['BEZ'] = $kos_bez;
				$tab_arr [$a] ['VZWECK'] = $mv->einheit_kurzname;
				$tab_arr [$a] ['DATUM'] = $datum_von_a;
				$tab_arr [$a] ['BETRAG'] = $betrag;
				$tab_arr [$a] ['BETRAG_A'] = nummer_runden ( $betrag_rein, 2 );
				
				/*
				 * #echo "<tr><td colspan=\"8\">$this->temp_kos_typ:$kostentraeger_typ $this->temp_kos_id:$kostentraeger_id</td></tr>";
				 * if(($this->temp_kos_typ == $kostentraeger_typ) && ($this->temp_kos_id == $kostentraeger_id)){
				 * echo "<tr><td>$kos_bez</td><td>$datum_von_a</td><td>$zinstage</td><td>$betrag</td><td>$betrag_verzinst</td><td>$kap</td><td>$soli</td><td>$betrag_rein</td></tr>";
				 * #echo "<tr><td><b>$mv->einheit_kurzname $mv->personen_name_string</td><td>$datum_von_a</td><td>$zinstage</td><td>$betrag</td><td>$betrag_verzinst</td><td>$kap</td><td>$soli</td><td>$betrag_rein</td></tr>";
				 * }else{
				 * echo "<tr><td>$kos_bez</td><td>$datum_von_a</td><td>$zinstage</td><td>$betrag</td><td>$betrag_verzinst</td><td>$kap</td><td>$soli</td><td>$betrag_rein</td></tr>";
				 * echo "<tr><td colspan=\"8\"><b>$kos_bez</td></tr>";
				 * }
				 */
				
				$summe = $summe + $betrag;
				$summe_verzinst = $summe_verzinst + $betrag_verzinst;
				
				$mv->einheit_kurzname = '';
				// }
			} // end for
			
			$summe = nummer_kuerzen ( $summe, 3 );
			
			$kap_g = ($summe_verzinst - $summe) * ($kap_prozent / 100);
			$soli_g = $kap_g * $soli_prozent / 100;
			
			// echo "$summe $summe_verzinst $kap_g $soli_g<br>";
			$endsumme = nummer_runden ( $summe_verzinst - $kap_g - $soli_g, 2 );
			echo "<tfoot><tr><th colspan=\"5\">$datum_bis_a</th><th>SUMME VERZINST</th><th>$summe_verzinst</th><th>SUMME O. KAP+SOLI</t><th>$endsumme</th></tr></tfoot>";
			
			/* Summe letzte Zeile */
			$tab_arr [$a + 1] ['BEZ'] = $datum_bis_a;
			$tab_arr [$a + 1] ['VZWECK'] = "Summen hochgerechnet";
			$tab_arr [$a + 1] ['BETRAG_A'] = $endsumme;
			
			$this->anfangs_summe = $summe;
			$this->end_summe = $endsumme;
			$this->kap_g = $kap_g;
			$this->soli_g = $soli_g;
			
			echo "</table>";
			
			// echo "<b>ENDSUMME $endsumme</b><br>";
			if (isset ( $_REQUEST [pdf] )) {
				//include_once ('pdfclass/class.ezpdf.php');
				include_once ('classes/class_bpdf.php');
				$pdf = new Cezpdf ( 'a4', 'portrait' );
				$bpdf = new b_pdf ();
				$bpdf->b_header ( $pdf, 'Partner', $_SESSION [partner_id], 'portrait', 'Helvetica.afm', 6 );
				$this->footer_zahlungshinweis = $bpdf->zahlungshinweis;
				
				// $pdf->ezStopPageNumbers(); //seitennummerirung beenden
				$p = new partners ();
				$p->get_partner_info ( $_SESSION [partner_id] );
				$cols = array (
						'BEZ' => "<b>ZUORDNUNG</b>",
						'VZWECK' => "<b>VERWENDUNG\nBUCHUNGSTEXT</b>",
						'DATUM' => "<b>DATUM</b>",
						'BETRAG' => "<b>BETRAG</b>",
						'BETRAG_A' => "<b>BETRAG \n $datum_bis_a</b>" 
				);
				$pdf->ezSetDy ( - 10 );
				$pdf->ezTable ( $tab_arr, $cols, "", array (
						'showHeadings' => 1,
						'shaded' => 1,
						'showLines' => 1,
						'titleFontSize' => 8,
						'fontSize' => 7,
						'xPos' => 50,
						'xOrientation' => 'right',
						'width' => 500,
						'rowGap' => 0.6 
				) );
				ob_clean (); // ausgabepuffer leeren
				$pdf->ezStream ();
			}
		} else {
			echo "keine kautionszahlungen gebucht";
		}
	}
	function mieter_ohne_kaution_arr($geldkonto_id, $kostenkonto) {
		$db_abfrage = "SELECT MIETVERTRAG_ID FROM `MIETVERTRAG`
WHERE `MIETVERTRAG_AKTUELL` = '1' && MIETVERTRAG_ID NOT IN (SELECT KOSTENTRAEGER_ID AS MIETVERTRAG_ID FROM GELD_KONTO_BUCHUNGEN WHERE GELDKONTO_ID='$geldkonto_id' && KOSTENTRAEGER_TYP='MIETVERTRAG')
    ORDER BY EINHEIT_ID ASC,`MIETVERTRAG`.`MIETVERTRAG_BIS`  ASC";
		$result = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		$numrows = mysql_numrows ( $result );
		
		if ($numrows) {
			while ( $row = mysql_fetch_assoc ( $result ) ) {
				$my_arr [] = $row;
			}
			return $my_arr;
		}
	}
	function mieter_ohne_kaution_anzeigen($geldkonto_id, $kostenkonto) {
		$mv_ids_arr = $this->mieter_ohne_kaution_arr ( $geldkonto_id, $kostenkonto );
		if (! is_array ( $mv_ids_arr )) {
			echo "Keine Mieter ohne Buchungen auf Konto $kostenkonto";
		} else {
			$anzahl = count ( $mv_ids_arr );
			echo "<b>Mieter ohne Buchungen auf Konto $kostenkonto</b><hr>";
			for($a = 0; $a < $anzahl; $a ++) {
				$zeile = $a + 1;
				$mv = new mietvertraege ();
				$mv_id = $mv_ids_arr [$a] ['MIETVERTRAG_ID'];
				$mv->get_mietvertrag_infos_aktuell ( $mv_id );
				if ($mv->mietvertrag_aktuell == true) {
					echo "$zeile. $mv->einheit_kurzname $mv->personen_name_string - AKTUELL<br>";
				} else {
					echo "$zeile. $mv->einheit_kurzname $mv->personen_name_string - AUSGEZOGEN<br>";
				}
			}
		}
	}
	function kautions_uebersicht($objekt_id, $alle = null) {
		$o = new objekt ();
		$ein_arr = $o->einheiten_objekt_arr ( $objekt_id );
		// echo '<pre>';
		// print_r($ein_arr);
		if (! is_array ( $ein_arr )) {
			fehlermeldung_ausgeben ( "Keine Einheiten im Objekt" );
		} else 

		{
			$anz_e = count ( $ein_arr );
			echo "<table>";
			echo "<tr><th>EINHEIT</th><th>TYP</th><th>MIETER</th><th>VON</th><th>BIS</th><th>DAUER</th>";
			$felder_arr = $this->get_felder_arr ();
			if (is_array ( $felder_arr )) {
				$anz_felder = count ( $felder_arr );
				$cols = $anz_felder + 6;
				for($a = 0; $a < $anz_felder; $a ++) {
					$feld = $felder_arr [$a] ['FELD'];
					echo "<th>$feld</th>";
				}
			}
			echo "</tr>";
			for($a = 0; $a < $anz_e; $a ++) {
				$einheit_id = $ein_arr [$a] ['EINHEIT_ID'];
				$einheit_kn = $ein_arr [$a] ['EINHEIT_KURZNAME'];
				$typ = $ein_arr [$a] ['TYP'];
				$e = new einheit ();
				/* aktuelle Mieter nur */
				if ($alle == null) {
					$mv_id = $e->get_last_mietvertrag_id ( $einheit_id );
					$mv_arr [] ['MIETVERTRAG_ID'] = $mv_id;
				} else {
					
					/* alle Mieter */
					$mv_arr = $e->get_mietvertrag_ids ( $einheit_id );
				}
				
				$anz_mv = count ( $mv_arr );
				// print_r($mv_arr);
				/* Jeden MV durchlaufen */
				for($m = 0; $m < $anz_mv; $m ++) {
					$mv_id = $mv_arr [$m] ['MIETVERTRAG_ID'];
					if (! empty ( $mv_id )) {
						$mv = new mietvertraege ();
						$mv->get_mietvertrag_infos_aktuell ( $mv_id );
						// echo "$mv->einheit_kurzname | $typ | $mv->personen_name_string_u2<br>";
						if ($mv->mietvertrag_aktuell == '1') {
							echo "<tr style=\"background-color:#d5ffe5;\">";
						} else {
							echo "<tr>";
						}
						$d1 = new DateTime ( $mv->mietvertrag_von_d );
						if ($mv->mietvertrag_bis_d == "00.00.0000") {
							$d2 = new DateTime ( date ( "d.m.Y" ) );
						} else {
							$d2 = new DateTime ( $mv->mietvertrag_bis_d );
						}
						$diff = $d2->diff ( $d1 );
						// "$diff->y";
						echo "<td>$einheit_kn</td><td>$typ</td><td>$mv->personen_name_string</td><td>$mv->mietvertrag_von_d</td><td>$mv->mietvertrag_bis_d</td><td>$diff->y J/$diff->m M";
						
						for($f = 0; $f < $anz_felder; $f ++) {
							$feld = $felder_arr [$f] ['FELD'];
							$wert = $this->get_feld_wert ( $mv_id, $feld );
							if (empty ( $wert )) {
								$wert = "----";
							}
							$link_wert = "<a class=\"details\" onclick=\"change_kautionsfeld('$feld', '$wert', '$mv_id')\">$wert</a>";
							// change_kautionsfeld(feld, wert, mv_id)
							echo "<td>$link_wert</td>";
						}
						echo "</tr>";
					} else {
						echo "<tr style=\"background-color:#f88b8b;\"><td>$einheit_kn</td><td>$typ</td><td colspan=\"$cols\">IMMER LEER</td></tr>";
					}
					unset ( $mv_id );
				}
				unset ( $mv_arr );
				
				echo "<tr><td colspan=\"$cols\" style=\"background-color:#faffc4;\"></td></tr>";
			}
			echo "</table>";
		}
	}
	function get_felder_arr() {
		$result = mysql_query ( "SELECT * FROM  `KAUTION_FELD` WHERE AKTUELL='1' ORDER BY DAT ASC" );
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			while ( $row = mysql_fetch_assoc ( $result ) )
				$my_arr [] = $row;
			return $my_arr;
		} else {
			return false;
		}
	}
	function feld_speichern($feld) {
		$db_abfrage = "INSERT INTO KAUTION_FELD VALUES (NULL, '$feld',  '1')";
		$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		// protokollieren
		$last_dat = mysql_insert_id ();
		protokollieren ( 'KAUTION_FELD', $last_dat, $last_dat );
	}
	function feld_del($dat) {
		$db_abfrage = "UPDATE KAUTION_FELD SET AKTUELL='0' WHERE DAT='$dat'";
		$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		// protokollieren
		$last_dat = mysql_insert_id ();
		protokollieren ( 'KAUTION_FELD', $dat, $dat );
	}
	function feld_wert_speichern($mv_id, $feld, $wert) {
		$db_abfrage = "UPDATE KAUTION_DATEN SET AKTUELL='0' WHERE MV_ID='$mv_id' && FELD='$feld'";
		$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		
		$db_abfrage = "INSERT INTO KAUTION_DATEN VALUES (NULL, '$mv_id', '$feld', '$wert', '1')";
		$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		
		// protokollieren
		$last_dat = mysql_insert_id ();
		protokollieren ( 'KAUTION_DATEN', $last_dat, $last_dat );
	}
	function get_feld_wert($mv_id, $feld) {
		$result = mysql_query ( "SELECT * FROM  `KAUTION_DATEN` WHERE  `MV_ID` = '$mv_id' AND  `FELD` ='$feld' AND  `AKTUELL` =  '1' ORDER BY DAT DESC LIMIT 0,1" );
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			$row = mysql_fetch_assoc ( $result );
			return $row ['WERT'];
		} else {
			return "";
		}
	}
} // end class
?>
