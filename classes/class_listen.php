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
include_once ('pdfclass/class.ezpdf.php');
include_once ('classes/class_bpdf.php');
include_once ('classes/class_leerstand.php');
include_once ('classes/class_details.php');
class listen {
	function inspiration_pdf($ausgezogene = 0, $objekt_id, $monat, $jahr, $lang = 'de') {
		$monat_name = monat2name ( $monat );
		$pdf = new Cezpdf ( 'a4', 'portrait' );
		$bpdf = new b_pdf ();
		$bpdf->b_header ( $pdf, 'Partner', $_SESSION [partner_id], 'portrait', 'pdfclass/fonts/Helvetica.afm', 6 );
		$pdf->ezStopPageNumbers (); // seitennummerirung beenden
		$gk = new geldkonto_info ();
		
		$gk->geld_konto_ermitteln ( 'OBJEKT', $objekt_id );
		echo '<pre>';
		print_r ( $gk );
		if (! $gk->geldkonto_id) {
			// die("$objekt_id Geldkonto zum Objekt hinzuf�gen!!!");
		}
		
		$db_abfrage = "SELECT OBJEKT_KURZNAME, HAUS_STRASSE, HAUS_NUMMER, `EINHEIT_KURZNAME` , `EINHEIT_ID`,  ltrim(rtrim(EINHEIT_LAGE)) AS EINHEIT_LAGE, `EINHEIT_QM` FROM EINHEIT , HAUS, OBJEKT
WHERE `EINHEIT_AKTUELL` = '1' && EINHEIT.HAUS_ID = HAUS.HAUS_ID && HAUS.OBJEKT_ID=OBJEKT.OBJEKT_ID && HAUS_AKTUELL='1' && OBJEKT_AKTUELL='1' && OBJEKT.OBJEKT_ID='$objekt_id' 
ORDER BY EINHEIT_KURZNAME";
		
		$result = mysql_query ( $db_abfrage );
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			$z = 0;
			while ( $row = mysql_fetch_assoc ( $result ) ) {
				$my_arr [] = $row;
				$einheit_id = $row ['EINHEIT_ID'];
				$e = new einheit ();
				$det = new detail ();
				$my_arr [$z] ['WEG-FLAECHE_A'] = $det->finde_detail_inhalt ( 'EINHEIT', $einheit_id, 'WEG-Fl�che' ); // kommt als Kommazahl
				$my_arr [$z] ['WEG-FLAECHE'] = nummer_komma2punkt ( $my_arr [$z] ['WEG-FLAECHE_A'] );
				$weg = new weg ();
				$weg->get_last_eigentuemer ( $einheit_id );
				if (isset ( $weg->eigentuemer_name )) {
					// echo '<pre>';
					// print_r($weg);
					// $my_arr[$z]['EIGENTUEMER'] = $weg->eigentuemer_name;
					$weg->get_eigentuemer_namen ( $weg->eigentuemer_id );
					$my_arr [$z] ['EIGENTUEMER_NAMEN'] = $weg->eigentuemer_name_str_u;
					$my_arr [$z] ['EIGENTUEMER_ID'] = $weg->eigentuemer_id;
				} else {
					$my_arr [$z] ['EIGENTUEMER'] = 'Unbekannt';
				}
				$mv_id = $e->get_mietvertrag_id ( $einheit_id );
				if ($mv_id) {
					$mvs = new mietvertraege ();
					$mvs->get_mietvertrag_infos_aktuell ( $mv_id );
					$kontaktdaten = $e->kontaktdaten_mieter ( $mv_id );
					// $my_arr[$z]['MIETER'] = $mvs->personen_name_string_u."\n".$kontaktdaten;
					$my_arr [$z] ['MIETER'] = $mvs->personen_name_string_u;
					$my_arr [$z] ['MIETVERTRAG_ID'] = $mv_id;
					$my_arr [$z] ['KONTAKT'] = $kontaktdaten;
					$my_arr [$z] ['MIETVERTRAG_ID'] = $mv_id;
					$mz = new miete ();
					$mz->mietkonto_berechnung ( $mv_id );
					$my_arr [$z] ['MIETER_SALDO'] = $mz->erg;
				} else {
					$my_arr [$z] ['MIETER'] = 'Leerstand';
				}
				$z ++;
			}
			// print_r($weg);
			
			unset ( $e );
			unset ( $mvs );
			unset ( $weg );
			
			$anz = count ( $my_arr );
			/* Berechnung Abgaben */
			for($a = 0; $a < $anz; $a ++) {
				if (isset ( $my_arr [$a] ['EIGENTUEMER_ID'] )) {
					$my_arr [$a] ['ABGABEN'] [] ['ABGABE_IHR'] = $my_arr [$a] ['WEG-FLAECHE'] * 0.4;
					$my_arr [$a] ['ABGABEN'] [] ['VG'] = '30.00'; // Verwaltergeb�hr
					
					/* Kosten 1023 Reparatur Einheit */
					$my_arr [$a] ['AUSGABEN'] = $this->get_kosten_arr ( 'EINHEIT', $my_arr [$a] ['EINHEIT_ID'], $monat, $jahr, $gk->geldkonto_id, 1023 );
					$anz_rep = count ( $my_arr [$a] ['AUSGABEN'] );
					$summe_rep = 0;
					for($b = 0; $b < $anz_rep; $b ++) {
						$summe_rep += $my_arr [$a] ['AUSGABEN'] [$b] ['BETRAG'];
					}
					$my_arr [$a] ['AUSGABEN'] [$anz_rep] ['BETRAG'] = '<b>' . nummer_punkt2komma_t ( $summe_rep ) . '</b>';
					$my_arr [$a] ['AUSGABEN'] [$anz_rep] ['VERWENDUNGSZWECK'] = '<b>Summe</b>';
					
					// echo "'EINHEIT', $my_arr[$a]['EINHEIT_ID'], $monat, $jahr, $gk->geldkonto_id<br>";
					// print_r($arr);
					$mk = new mietkonto ();
					$mk->kaltmiete_monatlich ( $my_arr [$a] ['MIETVERTRAG_ID'], $monat, $jahr );
					$brutto_sollmiete_arr = explode ( '|', $mk->summe_forderung_monatlich ( $my_arr [$a] ['MIETVERTRAG_ID'], $monat, $jahr ) );
					$brutto_sollmiete = $brutto_sollmiete_arr [0];
					$my_arr [$a] ['NETTO_SOLL'] = $mk->ausgangs_kaltmiete;
					$my_arr [$a] ['BRUTTO_SOLL'] = $brutto_sollmiete;
					$my_arr [$a] ['IST_EINNAHMEN'] = $this->get_kosten_arr ( 'MIETVERTRAG', $my_arr [$a] ['MIETVERTRAG_ID'], $monat, $jahr, $gk->geldkonto_id, 80001 );
					$anz_me = count ( $my_arr [$a] ['IST_EINNAHMEN'] );
					$summe_einnahmen = 0;
					for($b = 0; $b < $anz_me; $b ++) {
						$summe_einnahmen += $my_arr [$a] ['IST_EINNAHMEN'] [$b] ['BETRAG'];
					}
					$my_arr [$a] ['IST_EINNAHMEN'] [$anz_me] ['BETRAG'] = '<b>' . nummer_punkt2komma_t ( $summe_einnahmen ) . '</b>';
					$my_arr [$a] ['IST_EINNAHMEN'] [$anz_me] ['VERWENDUNGSZWECK'] = '<b>Summe</b>';
					$my_arr [$a] ['BRUTTO_IST'] = $summe_einnahmen;
					// $my_arr[$a]['SUM_EIN_AUS_MIETE'] =
					
					$pdf_tab [$a] ['MIETER_SALDO'] = $my_arr [$a] ['MIETER_SALDO'];
					$pdf_tab [$a] ['MV_ID'] = $my_arr [$a] ['MIETVERTRAG_ID'];
					$pdf_tab [$a] ['EIGENTUEMER_NAMEN'] = $my_arr [$a] ['EIGENTUEMER_NAMEN'];
					$pdf_tab [$a] ['EINHEIT_KURZNAME'] = $my_arr [$a] ['EINHEIT_KURZNAME'];
					$pdf_tab [$a] ['EINHEIT_QM'] = $my_arr [$a] ['EINHEIT_QM'];
					$pdf_tab [$a] ['WEG-FLAECHE'] = $my_arr [$a] ['WEG-FLAECHE'];
					$pdf_tab [$a] ['WEG-FLAECHE_A'] = $my_arr [$a] ['WEG-FLAECHE_A'];
					
					$pdf_tab [$a] ['MIETER'] = $my_arr [$a] ['MIETER'];
					$pdf_tab [$a] ['BRUTTO_SOLL'] = $my_arr [$a] ['BRUTTO_SOLL'];
					$pdf_tab [$a] ['BRUTTO_SOLL_A'] = nummer_punkt2komma_t ( $my_arr [$a] ['BRUTTO_SOLL'] );
					$pdf_tab [$a] ['BRUTTO_IST'] = $my_arr [$a] ['BRUTTO_IST'];
					$pdf_tab [$a] ['BRUTTO_IST_A'] = nummer_punkt2komma_t ( $my_arr [$a] ['BRUTTO_IST'] );
					$pdf_tab [$a] ['DIFF'] = $my_arr [$a] ['BRUTTO_IST'] - $pdf_tab [$a] ['BRUTTO_SOLL'];
					$pdf_tab [$a] ['DIFF_A'] = nummer_punkt2komma_t ( $my_arr [$a] ['BRUTTO_IST'] - $pdf_tab [$a] ['BRUTTO_SOLL'] );
					
					$pdf_tab [$a] ['NETTO_SOLL'] = $my_arr [$a] ['NETTO_SOLL'];
					$pdf_tab [$a] ['NETTO_SOLL_A'] = nummer_punkt2komma_t ( $my_arr [$a] ['NETTO_SOLL'] );
					$pdf_tab [$a] ['ABGABE_IHR'] = $my_arr [$a] ['WEG-FLAECHE'] * - 0.4;
					$pdf_tab [$a] ['ABGABE_IHR_A'] = nummer_punkt2komma_t ( $my_arr [$a] ['WEG-FLAECHE'] * - 0.4 );
					$pdf_tab [$a] ['ABGABE_HV'] = '-30.00';
					$pdf_tab [$a] ['ABGABE_HV_A'] = '-30,00';
					$pdf_tab [$a] ['ZWISCHENSUMME'] = $my_arr [$a] ['NETTO_SOLL'] + $pdf_tab [$a] ['ABGABE_IHR'] + $pdf_tab [$a] ['ABGABE_HV'];
					$pdf_tab [$a] ['ZWISCHENSUMME_A'] = nummer_punkt2komma_t ( $my_arr [$a] ['NETTO_SOLL'] + $pdf_tab [$a] ['ABGABE_IHR'] + $pdf_tab [$a] ['ABGABE_HV'] );
					$pdf_tab [$a] ['SUMME_REP'] = $summe_rep;
					$pdf_tab [$a] ['SUMME_REP_A'] = nummer_punkt2komma_t ( $summe_rep );
					$pdf_tab [$a] ['ENDSUMME'] = $pdf_tab [$a] ['ZWISCHENSUMME'] + $summe_rep;
					$pdf_tab [$a] ['ENDSUMME_A'] = '<b>' . nummer_punkt2komma_t ( $pdf_tab [$a] ['ZWISCHENSUMME'] + $summe_rep ) . '</b>';
					
					// $pdf_tab[$a]['EIG_AUSZAHLUNG'] = $this->get_kosten_arr('EINHEIT', $my_arr[$a]['EINHEIT_ID'], $monat, $jahr, $gk->geldkonto_id,80001);
					
					$e_nam = $pdf_tab [$a] ['EIGENTUEMER_NAMEN'];
					$ein_nam = $pdf_tab [$a] ['EINHEIT_KURZNAME'];
					// echo '<pre>';
					// print_r($pdf_tab);
					// die();
					if ($lang == 'en') {
						$cols = array (
								'MIETER_SALDO' => "Saldo",
								'EIGENTUEMER_NAMEN' => "owner",
								'EINHEIT_KURZNAME' => "apart.No",
								'MIETER' => 'tenant',
								'WEG-FLAECHE_A' => 'size m�',
								'BRUTTO_SOLL_A' => 'to cash g.',
								'BRUTTO_IST_A' => 'paid g.',
								'DIFF_A' => 'diff.',
								'NETTO_SOLL_A' => 'net rent',
								'ABGABE_IHR_A' => 'for maint.',
								'ABGABE_HV_A' => 'mng. Fee',
								'SUMME_REP_A' => 'maint. bills',
								'ENDSUMME_A' => 'pay off' 
						);
						$pdf->ezTable ( $pdf_tab, $cols, "<b>$monat_name $jahr - Overview - $ein_nam</b>", array (
								'showHeadings' => 1,
								'shaded' => 1,
								'titleFontSize' => 8,
								'fontSize' => 7,
								'xPos' => 50,
								'xOrientation' => 'right',
								'width' => 500,
								'cols' => array (
										'ENDSUMME_A' => array (
												'justification' => 'right',
												'width' => 50 
										),
										'EIGENTUEMER_NAMEN' => array (
												'justification' => 'left',
												'width' => 50 
										) 
								) 
						) );
					} else {
						$cols = array (
								'EIGENTUEMER_NAMEN' => "Eigent�mer",
								'EINHEIT_KURZNAME' => "EINHEIT",
								'MIETER' => 'Mieter',
								'WEG-FLAECHE_A' => 'Eig. m�',
								'BRUTTO_SOLL_A' => 'Warm SOLL',
								'BRUTTO_IST_A' => 'Warm IST',
								'DIFF_A' => 'DIFF',
								'NETTO_SOLL_A' => 'rent p.m.\n (actual)',
								'ABGABE_IHR_A' => 'IHR',
								'ABGABE_HV_A' => 'HV',
								'SUMME_REP_A' => 'Rep.',
								'ENDSUMME_A' => 'AUSZAHLUNG' 
						);
						$pdf->ezTable ( $pdf_tab, $cols, "<b>$monat_name $jahr - Gesamt�bersicht - $ein_nam</b>", array (
								'showHeadings' => 1,
								'shaded' => 1,
								'titleFontSize' => 8,
								'fontSize' => 7,
								'xPos' => 30,
								'xOrientation' => 'right',
								'width' => 550,
								'cols' => array (
										'ENDSUMME_A' => array (
												'justification' => 'right',
												'width' => 50 
										),
										'EIGENTUEMER_NAMEN' => array (
												'justification' => 'left',
												'width' => 50 
										) 
								) 
						) );
					}
					
					if ($pdf_tab [$a] ['BRUTTO_IST'] < $pdf_tab [$a] ['ENDSUMME']) {
						$pdf->setColor ( 1.0, 0.0, 0.0 );
						$pdf->ezSetDy ( - 20 ); // abstand
						if ($lang == 'en') {
							$pdf->ezText ( "payout not possible!", 12 );
						} else {
							$pdf->ezText ( "Keine Auszahlung m�glich!", 12 );
						}
					}
					
					// print_r($table_arr);
					// die();
					
					$pdf->ezSetDy ( - 20 ); // abstand
					if (is_array ( $my_arr [$a] ['AUSGABEN'] )) {
						if ($lang == 'en') {
							$cols = array (
									'DATUM' => "Date",
									'VERWENDUNGSZWECK' => "Description",
									'BETRAG' => "Amount" 
							);
							$pdf->ezTable ( $my_arr [$a] ['AUSGABEN'], $cols, "<b>$monat_name $jahr - Maintenance bills 1023 - $ein_nam</b>", array (
									'showHeadings' => 1,
									'shaded' => 1,
									'titleFontSize' => 8,
									'fontSize' => 7,
									'xPos' => 50,
									'xOrientation' => 'right',
									'width' => 500,
									'cols' => array (
											'BETRAG' => array (
													'justification' => 'right',
													'width' => 65 
											),
											'DATUM' => array (
													'justification' => 'left',
													'width' => 50 
											) 
									) 
							) );
						} else {
							$cols = array (
									'DATUM' => "Datum",
									'VERWENDUNGSZWECK' => "Buchungstext",
									'BETRAG' => "Betrag" 
							);
							$pdf->ezTable ( $my_arr [$a] ['AUSGABEN'], $cols, "<b>$monat_name $jahr - Reparaturen 1023 - $ein_nam</b>", array (
									'showHeadings' => 1,
									'shaded' => 1,
									'titleFontSize' => 8,
									'fontSize' => 7,
									'xPos' => 50,
									'xOrientation' => 'right',
									'width' => 500,
									'cols' => array (
											'BETRAG' => array (
													'justification' => 'right',
													'width' => 65 
											),
											'DATUM' => array (
													'justification' => 'left',
													'width' => 50 
											) 
									) 
							) );
						}
					} else {
						$pdf->ezText ( "Keine Reparaturen", 12 );
					}
					$pdf->ezSetDy ( - 20 ); // abstand
					if (is_array ( $my_arr [$a] ['IST_EINNAHMEN'] )) {
						if ($lang == 'en') {
							$cols = array (
									'DATUM' => "Date",
									'VERWENDUNGSZWECK' => "Description",
									'BETRAG' => "Amount" 
							);
							$pdf->ezTable ( $my_arr [$a] ['IST_EINNAHMEN'], $cols, "<b>$monat_name $jahr - income overview 80001 - $ein_nam</b>", array (
									'showHeadings' => 1,
									'shaded' => 1,
									'titleFontSize' => 8,
									'fontSize' => 7,
									'xPos' => 50,
									'xOrientation' => 'right',
									'width' => 500,
									'cols' => array (
											'BETRAG' => array (
													'justification' => 'right',
													'width' => 65 
											),
											'DATUM' => array (
													'justification' => 'left',
													'width' => 50 
											) 
									) 
							) );
						} else {
							$cols = array (
									'DATUM' => "Datum",
									'VERWENDUNGSZWECK' => "Buchungstext",
									'BETRAG' => "Betrag" 
							);
							$pdf->ezTable ( $my_arr [$a] ['IST_EINNAHMEN'], $cols, "<b>$monat_name $jahr - Einnahmen 80001 - $ein_nam</b>", array (
									'showHeadings' => 1,
									'shaded' => 1,
									'titleFontSize' => 8,
									'fontSize' => 7,
									'xPos' => 50,
									'xOrientation' => 'right',
									'width' => 500,
									'cols' => array (
											'BETRAG' => array (
													'justification' => 'right',
													'width' => 65 
											),
											'DATUM' => array (
													'justification' => 'left',
													'width' => 50 
											) 
									) 
							) );
						}
					} else {
						$pdf->ezText ( "Keine Mieteinnahmen", 12 );
					}
					
					// $cols = array('DATUM'=>"Datum",'VERWENDUNGSZWECK'=>"Buchungstext", 'BETRAG'=>"Betrag");
					// $pdf->ezTable($pdf_tab[$a]['EIG_AUSZAHLUNG'], $cols, "<b>$monat_name $jahr - Auszahlung an Eigent�mer 80001 - $ein_nam</b>", array('showHeadings'=>1,'shaded'=>1, 'titleFontSize' => 8, 'fontSize' => 7, 'xPos'=>50,'xOrientation'=>'right', 'width'=>500, 'cols'=>array('BETRAG'=>array('justification'=>'right', 'width'=>65),'DATUM'=>array('justification'=>'left', 'width'=>50))));
					
					if ($my_arr [$a] ['MIETVERTRAG_ID']) {
						$pdf->ezNewPage ();
						$miete = new miete ();
						$miete->mietkontenblatt2pdf ( $pdf, $my_arr [$a] ['MIETVERTRAG_ID'] );
					}
					
					$pdf->ezNewPage ();
					unset ( $pdf_tab );
				}
			}
			
			// print_r($pdf_tab);
			// print_r($pdf_tab_soll);
			// echo '<pre>';
			// print_r($my_arr);
			
			// die();
			
			ob_clean (); // ausgabepuffer leeren
			header ( "Content-type: application/pdf" ); // wird von MSIE ignoriert
			$pdf->ezStream ();
		} else {
			die ( "Keine Einheiten im Objekt $objekt_id" );
		}
	}
	function inspiration_pdf_6($ausgezogene = 0, $objekt_id, $monat, $jahr, $lang = 'de') {
		$monat_name = monat2name ( $monat );
		$pdf = new Cezpdf ( 'a4', 'portrait' );
		$bpdf = new b_pdf ();
		$bpdf->b_header ( $pdf, 'Partner', $_SESSION [partner_id], 'portrait', 'pdfclass/fonts/Helvetica.afm', 6 );
		$pdf->ezStopPageNumbers (); // seitennummerirung beenden
		
		$gk = new geldkonto_info ();
		$gk->geld_konto_ermitteln ( 'OBJEKT', $objekt_id );
		echo '<pre>';
		print_r ( $gk );
		if (! $gk->geldkonto_id) {
			die ( 'Geldkonto zum Objekt hinzuf�gen!!!' );
		}
		$db_abfrage = "SELECT OBJEKT_KURZNAME, HAUS_STRASSE, HAUS_NUMMER, `EINHEIT_KURZNAME` , `EINHEIT_ID`,  ltrim(rtrim(EINHEIT_LAGE)) AS EINHEIT_LAGE, `EINHEIT_QM` FROM EINHEIT , HAUS, OBJEKT
WHERE `EINHEIT_AKTUELL` = '1' && EINHEIT.HAUS_ID = HAUS.HAUS_ID && HAUS.OBJEKT_ID=OBJEKT.OBJEKT_ID && HAUS_AKTUELL='1' && OBJEKT_AKTUELL='1' && OBJEKT.OBJEKT_ID='$objekt_id' 
ORDER BY EINHEIT_KURZNAME";
		
		$result = mysql_query ( $db_abfrage );
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			$z = 0;
			while ( $row = mysql_fetch_assoc ( $result ) ) {
				$my_arr [] = $row;
				$einheit_id = $row ['EINHEIT_ID'];
				$e = new einheit ();
				$det = new detail ();
				$my_arr [$z] ['WEG-FLAECHE_A'] = $det->finde_detail_inhalt ( 'EINHEIT', $einheit_id, 'WEG-Fl�che' ); // kommt als Kommazahl
				$my_arr [$z] ['WEG-FLAECHE'] = nummer_komma2punkt ( $my_arr [$z] ['WEG-FLAECHE_A'] );
				
				$my_arr [$z] ['WG_NR'] = $det->finde_detail_inhalt ( 'EINHEIT', $einheit_id, 'Alte Nr' ); // kommt als Kommazahl
				
				$weg = new weg ();
				$weg->get_last_eigentuemer ( $einheit_id );
				if (isset ( $weg->eigentuemer_name )) {
					// echo '<pre>';
					// print_r($weg);
					// $my_arr[$z]['EIGENTUEMER'] = $weg->eigentuemer_name;
					$weg->get_eigentuemer_namen ( $weg->eigentuemer_id );
					$my_arr [$z] ['EIGENTUEMER_NAMEN'] = $weg->eigentuemer_name_str_u;
					$my_arr [$z] ['EIGENTUEMER_ID'] = $weg->eigentuemer_id;
				} else {
					$my_arr [$z] ['EIGENTUEMER'] = 'Unbekannt';
				}
				$mv_id = $e->get_mietvertrag_id ( $einheit_id );
				if ($mv_id) {
					$mvs = new mietvertraege ();
					$mvs->get_mietvertrag_infos_aktuell ( $mv_id );
					$kontaktdaten = $e->kontaktdaten_mieter ( $mv_id );
					// $my_arr[$z]['MIETER'] = $mvs->personen_name_string_u."\n".$kontaktdaten;
					$my_arr [$z] ['MIETER'] = $mvs->personen_name_string_u;
					$my_arr [$z] ['MIETVERTRAG_ID'] = $mv_id;
					$my_arr [$z] ['KONTAKT'] = $kontaktdaten;
					$mz = new miete ();
					$mz->mietkonto_berechnung ( $mv_id );
					$my_arr [$z] ['MIETER_SALDO'] = $mz->erg;
				} else {
					$my_arr [$z] ['MIETER'] = 'Leerstand';
				}
				$z ++;
			}
			// print_r($weg);
			
			unset ( $e );
			unset ( $mvs );
			unset ( $weg );
			
			$anz = count ( $my_arr );
			/* Berechnung Abgaben */
			for($a = 0; $a < $anz; $a ++) {
				if (isset ( $my_arr [$a] ['EIGENTUEMER_ID'] )) {
					$eige_id = $my_arr [$a] ['EIGENTUEMER_ID'];
					// echo $my_arr[$a]['EIGENTUEMER_ID'];
					// die();
					// $my_arr[$a]['ABGABEN'][]['ABGABE_IHR'] = $my_arr[$a]['WEG-FLAECHE'] * 0.4;
					// $my_arr[$a]['ABGABEN'][]['VG'] = '30.00'; //Verwaltergeb�hr
					
					$weg1 = new weg ();
					$ihr_hg = $weg1->get_summe_kostenkat_monat ( $monat, $jahr, 'Einheit', $einheit_id, '6030' );
					// $my_arr[$a]['ABGABEN'][]['ABGABE_IHR'] = $my_arr[$a]['WEG-FLAECHE'] * -0.4;
					if ($ihr_hg) {
						$my_arr [$a] ['ABGABEN'] [] ['ABGABE_IHR'] = $ihr_hg;
					} else {
						// if(empty($my_arr[$a]['WEG-FLAECHE'])){
						// $my_arr[$a]['ABGABEN'][]['ABGABE_IHR'] = $einheit_qm * -0.4;
						$my_arr [$a] ['ABGABEN'] [] ['ABGABE_IHR'] = $my_arr [$a] ['WEG-FLAECHE'] * 0.4;
					}
					
					/* Kosten 1023 Reparatur Einheit */
					$my_arr [$a] ['AUSGABEN'] = $this->get_kosten_arr ( 'EINHEIT', $my_arr [$a] ['EINHEIT_ID'], $monat, $jahr, $gk->geldkonto_id, 1023 );
					$anz_rep = count ( $my_arr [$a] ['AUSGABEN'] );
					$summe_rep = 0;
					for($b = 0; $b < $anz_rep; $b ++) {
						$summe_rep += $my_arr [$a] ['AUSGABEN'] [$b] ['BETRAG'];
					}
					$my_arr [$a] ['AUSGABEN'] [$anz_rep] ['BETRAG'] = '<b>' . nummer_punkt2komma_t ( $summe_rep ) . '</b>';
					$my_arr [$a] ['AUSGABEN'] [$anz_rep] ['VERWENDUNGSZWECK'] = '<b>Summe</b>';
					
					// echo "'EINHEIT', $my_arr[$a]['EINHEIT_ID'], $monat, $jahr, $gk->geldkonto_id<br>";
					// print_r($arr);
					$mk = new mietkonto ();
					$mk->kaltmiete_monatlich ( $my_arr [$a] ['MIETVERTRAG_ID'], $monat, $jahr );
					$brutto_sollmiete_arr = explode ( '|', $mk->summe_forderung_monatlich ( $my_arr [$a] ['MIETVERTRAG_ID'], $monat, $jahr ) );
					$brutto_sollmiete = $brutto_sollmiete_arr [0];
					$my_arr [$a] ['NETTO_SOLL'] = $mk->ausgangs_kaltmiete;
					
					/* Garantierte Miete abfragen */
					$net_ren_garantie_a = $det->finde_detail_inhalt ( 'EINHEIT', $einheit_id, 'WEG-KaltmieteINS' ); // kommt als Kommazahl
					$net_ren_garantie = nummer_komma2punkt ( $net_ren_garantie_a );
					$my_arr [$a] ['NETTO_SOLL_G_A'] = $net_ren_garantie_a;
					// if($einheit_id=='945'){
					// die("SANEL $einheit_id $net_ren_garantie");
					// }
					if ($net_ren_garantie > $mk->ausgangs_kaltmiete) {
						$my_arr [$a] ['NETTO_SOLL'] = $net_ren_garantie;
					} else {
						$my_arr [$a] ['NETTO_SOLL'] = $mk->ausgangs_kaltmiete;
					}
					
					// $my_arr[$a]['BRUTTO_SOLL'] = $brutto_sollmiete;
					$my_arr [$a] ['AUSZAHLUNG_ET'] = $this->get_kosten_arr ( 'Eigentuemer', $eige_id, $monat, $jahr, $gk->geldkonto_id, 5020 );
					// print_r($my_arr[$a]['AUSZAHLUNG_ET']);
					// die();
					$my_arr [$a] ['IST_EINNAHMEN'] = $this->get_kosten_arr ( 'MIETVERTRAG', $my_arr [$a] ['MIETVERTRAG_ID'], $monat, $jahr, $gk->geldkonto_id, 80001 );
					$anz_me = count ( $my_arr [$a] ['IST_EINNAHMEN'] );
					$summe_einnahmen = 0;
					for($b = 0; $b < $anz_me; $b ++) {
						$summe_einnahmen += $my_arr [$a] ['IST_EINNAHMEN'] [$b] ['BETRAG'];
					}
					$my_arr [$a] ['IST_EINNAHMEN'] [$anz_me] ['BETRAG'] = '<b>' . nummer_punkt2komma_t ( $summe_einnahmen ) . '</b>';
					$my_arr [$a] ['IST_EINNAHMEN'] [$anz_me] ['VERWENDUNGSZWECK'] = '<b>Summe</b>';
					$my_arr [$a] ['BRUTTO_IST'] = $summe_einnahmen;
					// $my_arr[$a]['SUM_EIN_AUS_MIETE'] =
					
					$pdf_tab [$a] ['EIGENTUEMER_NAMEN'] = $my_arr [$a] ['EIGENTUEMER_NAMEN'];
					
					$pdf_tab [$a] ['EINHEIT_KURZNAME'] = $my_arr [$a] ['WG_NR'] . "\n(" . $my_arr [$a] ['EINHEIT_KURZNAME'] . ')';
					
					$pdf_tab [$a] ['EINHEIT_QM'] = $my_arr [$a] ['EINHEIT_QM'];
					$pdf_tab [$a] ['WEG-FLAECHE'] = $my_arr [$a] ['WEG-FLAECHE'];
					$pdf_tab [$a] ['WEG-FLAECHE_A'] = $my_arr [$a] ['WEG-FLAECHE_A'];
					$pdf_tab [$a] ['AUSZAHLUNG_ET'] = $my_arr [$a] ['AUSZAHLUNG_ET'];
					$pdf_tab [$a] ['MIETER'] = $my_arr [$a] ['MIETER'];
					
					if ($my_arr [$a] ['MIETER_SALDO']) {
						$pdf_tab [$a] ['MIETER_SALDO'] = $my_arr [$a] ['MIETER_SALDO'];
					}
					$pdf_tab [$a] ['BRUTTO_SOLL'] = $my_arr [$a] ['BRUTTO_SOLL'];
					$pdf_tab [$a] ['BRUTTO_SOLL_A'] = nummer_punkt2komma_t ( $my_arr [$a] ['BRUTTO_SOLL'] );
					$pdf_tab [$a] ['BRUTTO_IST'] = $my_arr [$a] ['BRUTTO_IST'];
					$pdf_tab [$a] ['BRUTTO_IST_A'] = nummer_punkt2komma_t ( $my_arr [$a] ['BRUTTO_IST'] );
					$pdf_tab [$a] ['DIFF'] = $my_arr [$a] ['BRUTTO_IST'] - $pdf_tab [$a] ['BRUTTO_SOLL'];
					$pdf_tab [$a] ['DIFF_A'] = nummer_punkt2komma_t ( $my_arr [$a] ['BRUTTO_IST'] - $pdf_tab [$a] ['BRUTTO_SOLL'] );
					
					/* Garantiemiete */
					$pdf_tab [$a] ['NETTO_SOLL_G_A'] = $my_arr [$a] ['NETTO_SOLL_G_A'];
					
					$pdf_tab [$a] ['BRUTTO_SOLL'] = $my_arr [$a] ['BRUTTO_SOLL'];
					$pdf_tab [$a] ['BRUTTO_SOLL_A'] = nummer_punkt2komma_t ( $my_arr [$a] ['BRUTTO_SOLL'] );
					$pdf_tab [$a] ['BRUTTO_IST'] = $my_arr [$a] ['BRUTTO_IST'];
					$pdf_tab [$a] ['BRUTTO_IST_A'] = nummer_punkt2komma_t ( $my_arr [$a] ['BRUTTO_IST'] );
					$pdf_tab [$a] ['DIFF'] = $my_arr [$a] ['BRUTTO_IST'] - $pdf_tab [$a] ['BRUTTO_SOLL'];
					$pdf_tab [$a] ['DIFF_A'] = nummer_punkt2komma_t ( $my_arr [$a] ['BRUTTO_IST'] - $pdf_tab [$a] ['BRUTTO_SOLL'] );
					
					$pdf_tab [$a] ['NETTO_SOLL'] = $my_arr [$a] ['NETTO_SOLL'];
					$pdf_tab [$a] ['NETTO_SOLL_MV'] = $my_arr [$a] ['NETTO_SOLL_MV'];
					$pdf_tab [$a] ['NETTO_SOLL_DIFF'] = $pdf_tab [$a] ['NETTO_SOLL'] - $pdf_tab [$a] ['NETTO_SOLL_MV'];
					$pdf_tab [$a] ['NETTO_SOLL_A'] = nummer_punkt2komma_t ( $my_arr [$a] ['NETTO_SOLL'] );
					// echo '<
					
					// $pdf_tab[$a]['NETTO_SOLL'] =$my_arr[$a]['NETTO_SOLL'];
					// $pdf_tab[$a]['NETTO_SOLL_A'] =nummer_punkt2komma_t($my_arr[$a]['NETTO_SOLL']);
					$pdf_tab [$a] ['ABGABE_IHR'] = $my_arr [$a] ['WEG-FLAECHE'] * - 0.4;
					$pdf_tab [$a] ['ABGABE_IHR_A'] = nummer_punkt2komma_t ( $my_arr [$a] ['WEG-FLAECHE'] * - 0.4 );
					$pdf_tab [$a] ['ABGABE_HV'] = '-30.00';
					$pdf_tab [$a] ['ABGABE_HV_A'] = '-30,00';
					$pdf_tab [$a] ['ZWISCHENSUMME'] = $my_arr [$a] ['NETTO_SOLL'] + $pdf_tab [$a] ['ABGABE_IHR'] + $pdf_tab [$a] ['ABGABE_HV'];
					$pdf_tab [$a] ['ZWISCHENSUMME_A'] = nummer_punkt2komma_t ( $my_arr [$a] ['NETTO_SOLL'] + $pdf_tab [$a] ['ABGABE_IHR'] + $pdf_tab [$a] ['ABGABE_HV'] );
					$pdf_tab [$a] ['SUMME_REP'] = $summe_rep;
					$pdf_tab [$a] ['SUMME_REP_A'] = nummer_punkt2komma_t ( $summe_rep );
					$pdf_tab [$a] ['ENDSUMME'] = $pdf_tab [$a] ['ZWISCHENSUMME'] + $summe_rep;
					$pdf_tab [$a] ['ENDSUMME_A'] = '<b>' . nummer_punkt2komma_t ( $pdf_tab [$a] ['ZWISCHENSUMME'] + $summe_rep ) . '</b>';
					
					if (! $my_arr [$a] ['MIETER_SALDO']) {
						$pdf_tab [$a] ['ENDSUMME'] = 0.00;
						$pdf_tab [$a] ['ENDSUMME_A'] = '0,00';
					}
					
					/* Auszahlug Nullen wenn Mietersaldo klein */
					if ($pdf_tab [$a] ['MIETER_SALDO'] < 0) {
						$tmp_minus = substr ( $pdf_tab [$a] ['MIETER_SALDO'], 1 );
						// die($tmp_minus);
						if ($tmp_minus > $pdf_tab [$a] ['ENDSUMME']) {
							$pdf_tab [$a] ['ENDSUMME'] = 0.00;
							$pdf_tab [$a] ['ENDSUMME_A'] = '0,00';
						}
					}
					
					// $pdf_tab[$a]['EIG_AUSZAHLUNG'] = $this->get_kosten_arr('EINHEIT', $my_arr[$a]['EINHEIT_ID'], $monat, $jahr, $gk->geldkonto_id,80001);
					
					$e_nam = $pdf_tab [$a] ['EIGENTUEMER_NAMEN'];
					$ein_nam = $pdf_tab [$a] ['EINHEIT_KURZNAME'];
					
					/* �bersichtstabelle */
					$uebersicht [$a] ['EINHEIT_KURZNAME'] = $ein_nam;
					$uebersicht [$a] ['EIGENTUEMER_NAMEN'] = $e_nam;
					$uebersicht [$a] ['MIETER'] = $pdf_tab [$a] ['MIETER'];
					$uebersicht [$a] ['MIETER_SALDO'] = $pdf_tab [$a] ['MIETER_SALDO'];
					$uebersicht [$a] ['MIETER_SALDO_A'] = nummer_punkt2komma ( $pdf_tab [$a] ['MIETER_SALDO'] );
					$uebersicht [$a] ['NETTO_SOLL_G_A'] = $pdf_tab [$a] ['NETTO_SOLL_G_A'];
					$uebersicht [$a] ['NETTO_SOLL_A'] = nummer_punkt2komma ( $pdf_tab [$a] ['NETTO_SOLL_MV'] );
					$uebersicht [$a] ['NETTO_SOLL_DIFF_A'] = nummer_punkt2komma ( $pdf_tab [$a] ['NETTO_SOLL_DIFF'] );
					$uebersicht [$a] ['ABGABE_HV_A'] = $pdf_tab [$a] ['ABGABE_HV_A'];
					$uebersicht [$a] ['ABGABE_IHR'] = $pdf_tab [$a] ['ABGABE_IHR_A'];
					
					$uebersicht [$a] ['SUMME_REP'] = $pdf_tab [$a] ['SUMME_REP'];
					$uebersicht [$a] ['SUMME_REP_A'] = $pdf_tab [$a] ['SUMME_REP_A'];
					
					$uebersicht [$a] ['ENDSUMME_A'] = $pdf_tab [$a] ['ENDSUMME_A'];
					// $summe_alle_eigentuemer += $pdf_tab[$a]['ENDSUMME'];
					if ($pdf_tab [$a] ['ENDSUMME'] > 0) {
						$summe_alle_eigentuemer += $pdf_tab [$a] ['ENDSUMME'];
					} else {
						$summe_nachzahler += $pdf_tab [$a] ['ENDSUMME'];
					}
					
					if ($lang == 'en') {
						$cols = array (
								'EIGENTUEMER_NAMEN' => "owner",
								'EINHEIT_KURZNAME' => "apart.No",
								'MIETER' => 'tenant',
								'WEG-FLAECHE_A' => 'size m�',
								'NETTO_SOLL_A' => 'net rent',
								'ABGABE_IHR_A' => 'for maint.',
								'ABGABE_HV_A' => 'mng. fee',
								'SUMME_REP_A' => 'maint. bills',
								'ENDSUMME_A' => 'transfer' 
						);
						$pdf->ezTable ( $pdf_tab, $cols, "<b>$monat_name $jahr - Overview - $ein_nam</b>", array (
								'showHeadings' => 1,
								'shaded' => 1,
								'titleFontSize' => 8,
								'fontSize' => 7,
								'xPos' => 50,
								'xOrientation' => 'right',
								'width' => 500,
								'cols' => array (
										'ENDSUMME_A' => array (
												'justification' => 'right',
												'width' => 50 
										),
										'EIGENTUEMER_NAMEN' => array (
												'justification' => 'left',
												'width' => 50 
										) 
								) 
						) );
					} else {
						$cols = array (
								'EIGENTUEMER_NAMEN' => "Eigent�mer",
								'EINHEIT_KURZNAME' => "EINHEIT",
								'MIETER' => 'Mieter',
								'WEG-FLAECHE_A' => 'Eig. m�',
								'BRUTTO_SOLL_A' => 'Warm SOLL',
								'BRUTTO_IST_A' => 'Warm IST',
								'DIFF_A' => 'DIFF',
								'NETTO_SOLL_A' => 'rent p.m.\n (actual)',
								'ABGABE_IHR_A' => 'IHR',
								'ABGABE_HV_A' => 'HV',
								'SUMME_REP_A' => 'Rep.',
								'ENDSUMME_A' => 'AUSZAHLUNG' 
						);
						$pdf->ezTable ( $pdf_tab, $cols, "<b>$monat_name $jahr - Gesamt�bersicht - $ein_nam</b>", array (
								'showHeadings' => 1,
								'shaded' => 1,
								'titleFontSize' => 8,
								'fontSize' => 7,
								'xPos' => 30,
								'xOrientation' => 'right',
								'width' => 550,
								'cols' => array (
										'ENDSUMME_A' => array (
												'justification' => 'right',
												'width' => 50 
										),
										'EIGENTUEMER_NAMEN' => array (
												'justification' => 'left',
												'width' => 50 
										) 
								) 
						) );
					}
					
					/*
					 * if($pdf_tab[$a]['BRUTTO_IST']<$pdf_tab[$a]['ENDSUMME']){
					 * $pdf->setColor(1.0,0.0,0.0);
					 * $pdf->ezSetDy(-20); //abstand
					 * if($lang=='en'){
					 * $pdf->ezText("no payout possible!", 12);
					 * }else{
					 * $pdf->ezText("Keine Auszahlung m�glich!", 12);
					 * }
					 * }
					 */
					
					// print_r($table_arr);
					// die();
					
					$pdf->ezSetDy ( - 20 ); // abstand
					if (is_array ( $my_arr [$a] ['AUSGABEN'] )) {
						if ($lang == 'en') {
							$cols = array (
									'DATUM' => "Date",
									'VERWENDUNGSZWECK' => "Description",
									'BETRAG' => "Amount" 
							);
							$pdf->ezTable ( $my_arr [$a] ['AUSGABEN'], $cols, "<b>$monat_name $jahr - Maintenance bills 1023 - $ein_nam</b>", array (
									'showHeadings' => 1,
									'shaded' => 1,
									'titleFontSize' => 8,
									'fontSize' => 7,
									'xPos' => 50,
									'xOrientation' => 'right',
									'width' => 500,
									'cols' => array (
											'BETRAG' => array (
													'justification' => 'right',
													'width' => 65 
											),
											'DATUM' => array (
													'justification' => 'left',
													'width' => 50 
											) 
									) 
							) );
						} else {
							$cols = array (
									'DATUM' => "Datum",
									'VERWENDUNGSZWECK' => "Buchungstext",
									'BETRAG' => "Betrag" 
							);
							$pdf->ezTable ( $my_arr [$a] ['AUSGABEN'], $cols, "<b>$monat_name $jahr - Reparaturen 1023 - $ein_nam</b>", array (
									'showHeadings' => 1,
									'shaded' => 1,
									'titleFontSize' => 8,
									'fontSize' => 7,
									'xPos' => 50,
									'xOrientation' => 'right',
									'width' => 500,
									'cols' => array (
											'BETRAG' => array (
													'justification' => 'right',
													'width' => 65 
											),
											'DATUM' => array (
													'justification' => 'left',
													'width' => 50 
											) 
									) 
							) );
						}
					} else {
						$pdf->ezText ( "Keine Reparaturen", 12 );
					}
					$pdf->ezSetDy ( - 20 ); // abstand
					/* TAbelle Auszahlung an Eigent�mer */
					// print_r($my_arr[$a]['AUSZAHLUNG_ET']);
					// die();
					if (is_array ( $my_arr [$a] ['AUSZAHLUNG_ET'] )) {
						
						if ($lang == 'en') {
							$cols = array (
									'DATUM' => "Date",
									'VERWENDUNGSZWECK' => "Description",
									'BETRAG' => "Amount" 
							);
							// $pdf->ezTable($my_arr[$a]['AUSZAHLUNG_ET'], $cols, "<b>$monat_name $jahr - transfer 5020 - $ein_nam</b>", array('showHeadings'=>1,'shaded'=>1, 'titleFontSize' => 8, 'fontSize' => 7, 'xPos'=>50,'xOrientation'=>'right', 'width'=>500, 'cols'=>array('BETRAG'=>array('justification'=>'right', 'width'=>65),'DATUM'=>array('justification'=>'left', 'width'=>50))));
						} else {
							$cols = array (
									'DATUM' => "Datum",
									'VERWENDUNGSZWECK' => "Buchungstext",
									'BETRAG' => "Betrag" 
							);
							// $pdf->ezTable($my_arr[$a]['AUSZAHLUNG_ET'], $cols, "<b>$monat_name $jahr - �berweisung 80001 - $ein_nam</b>", array('showHeadings'=>1,'shaded'=>1, 'titleFontSize' => 8, 'fontSize' => 7, 'xPos'=>50,'xOrientation'=>'right', 'width'=>500, 'cols'=>array('BETRAG'=>array('justification'=>'right', 'width'=>65),'DATUM'=>array('justification'=>'left', 'width'=>50))));
						}
					}
					/*
					 * if(is_array($my_arr[$a]['IST_EINNAHMEN'])){
					 * if($lang=='en'){
					 * $cols = array('DATUM'=>"Date", 'VERWENDUNGSZWECK'=>"Description", 'BETRAG'=>"Amount");
					 * $pdf->ezTable($my_arr[$a]['IST_EINNAHMEN'], $cols, "<b>$monat_name $jahr - income overview 80001 - $ein_nam</b>", array('showHeadings'=>1,'shaded'=>1, 'titleFontSize' => 8, 'fontSize' => 7, 'xPos'=>50,'xOrientation'=>'right', 'width'=>500, 'cols'=>array('BETRAG'=>array('justification'=>'right', 'width'=>65),'DATUM'=>array('justification'=>'left', 'width'=>50))));
					 * }else{
					 * $cols = array('DATUM'=>"Datum", 'VERWENDUNGSZWECK'=>"Buchungstext", 'BETRAG'=>"Betrag");
					 * $pdf->ezTable($my_arr[$a]['IST_EINNAHMEN'], $cols, "<b>$monat_name $jahr - Einnahmen 80001 - $ein_nam</b>", array('showHeadings'=>1,'shaded'=>1, 'titleFontSize' => 8, 'fontSize' => 7, 'xPos'=>50,'xOrientation'=>'right', 'width'=>500, 'cols'=>array('BETRAG'=>array('justification'=>'right', 'width'=>65),'DATUM'=>array('justification'=>'left', 'width'=>50))));
					 * }
					 *
					 * }else{
					 * $pdf->ezText("Keine Mieteinnahmen", 12);
					 * }
					 */
					
					// $cols = array('DATUM'=>"Datum",'VERWENDUNGSZWECK'=>"Buchungstext", 'BETRAG'=>"Betrag");
					// $pdf->ezTable($pdf_tab[$a]['EIG_AUSZAHLUNG'], $cols, "<b>$monat_name $jahr - Auszahlung an Eigent�mer 80001 - $ein_nam</b>", array('showHeadings'=>1,'shaded'=>1, 'titleFontSize' => 8, 'fontSize' => 7, 'xPos'=>50,'xOrientation'=>'right', 'width'=>500, 'cols'=>array('BETRAG'=>array('justification'=>'right', 'width'=>65),'DATUM'=>array('justification'=>'left', 'width'=>50))));
					
					if ($pdf_tab [$a] ['MIETER_SALDO'] < 0) {
						$tmp_minus = substr ( $pdf_tab [$a] ['MIETER_SALDO'], 1 );
						// die($tmp_minus);
						if ($tmp_minus > $pdf_tab [$a] ['ENDSUMME']) {
							$pdf_tab [$a] ['ENDSUMME'] = 0.00;
							$pdf_tab [$a] ['ENDSUMME_A'] = '0,00';
							if ($my_arr [$a] ['MIETVERTRAG_ID']) {
								$pdf->ezNewPage ();
								$miete = new miete ();
								$miete->mietkontenblatt2pdf ( $pdf, $my_arr [$a] ['MIETVERTRAG_ID'] );
							}
						}
					}
					
					$pdf->ezNewPage ();
					unset ( $pdf_tab );
				}
			}
			$uebersicht [$anz] ['EIGENTUEMER_NAMEN'] = 'Auszahlungssumme';
			$uebersicht [$anz] ['ENDSUMME_A'] = nummer_punkt2komma_t ( $summe_alle_eigentuemer );
			$uebersicht [$anz + 1] ['EIGENTUEMER_NAMEN'] = 'Zu erhalten';
			$uebersicht [$anz + 1] ['ENDSUMME_A'] = nummer_punkt2komma_t ( $summe_nachzahler );
			
			if ($lang == 'en') {
				$cols = array (
						'EINHEIT_KURZNAME' => "Apt",
						'EIGENTUEMER_NAMEN' => "Own",
						'MIETER' => "Tenant",
						'MIETER_SALDO_A' => 'current',
						'NETTO_SOLL_G_A' => "Garanty",
						'NETTO_SOLL_A' => "net rent",
						'NETTO_SOLL_DIFF_A' => "diff",
						'ABGABE_HV_A' => "fee",
						'ABGABE_IHR' => "for maint.",
						'ENDSUMME_A' => "Amount",
						'SUMME_REP_A' => 'Rep.' 
				);
			}
			
			$pdf->ezTable ( $uebersicht, $cols, null, array (
					'showHeadings' => 1,
					'shaded' => 1,
					'titleFontSize' => 8,
					'fontSize' => 7,
					'xPos' => 50,
					'xOrientation' => 'right',
					'width' => 500 
			) );
			// print_r($pdf_tab);
			
			// print_r($pdf_tab);
			// print_r($pdf_tab_soll);
			// echo '<pre>';
			// print_r($my_arr);
			
			// die();
			
			ob_clean (); // ausgabepuffer leeren
			header ( "Content-type: application/pdf" ); // wird von MSIE ignoriert
			$pdf->ezStream ();
		} else {
			die ( "Keine Einheiten im Objekt $objekt_id" );
		}
	}
	function inspiration_pdf_kurz($ausgezogene = 0, $objekt_id, $monat, $jahr, $lang = 'de') {
		$monat_name = monat2name ( $monat );
		$pdf = new Cezpdf ( 'a4', 'portrait' );
		$bpdf = new b_pdf ();
		$bpdf->b_header ( $pdf, 'Partner', $_SESSION ['partner_id'], 'portrait', 'pdfclass/fonts/Helvetica.afm', 6 );
		$pdf->ezStopPageNumbers (); // seitennummerirung beenden
		$gk = new geldkonto_info ();
		$gk->geld_konto_ermitteln ( 'OBJEKT', $objekt_id );
		echo '<pre>';
		print_r ( $gk );
		if (! $gk->geldkonto_id) {
			die ( 'Geldkonto zum Objekt hinzuf�gen!!!' );
		}
		$db_abfrage = "SELECT OBJEKT_KURZNAME, HAUS_STRASSE, HAUS_NUMMER, `EINHEIT_KURZNAME` , `EINHEIT_ID`,  ltrim(rtrim(EINHEIT_LAGE)) AS EINHEIT_LAGE, `EINHEIT_QM` FROM EINHEIT , HAUS, OBJEKT
WHERE `EINHEIT_AKTUELL` = '1' && EINHEIT.HAUS_ID = HAUS.HAUS_ID && HAUS.OBJEKT_ID=OBJEKT.OBJEKT_ID && HAUS_AKTUELL='1' && OBJEKT_AKTUELL='1' && OBJEKT.OBJEKT_ID='$objekt_id' 
ORDER BY EINHEIT_KURZNAME";
		
		$result = mysql_query ( $db_abfrage );
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			$z = 0;
			while ( $row = mysql_fetch_assoc ( $result ) ) {
				$my_arr [] = $row;
				$einheit_id = $row ['EINHEIT_ID'];
				$einheit_qm = $row ['EINHEIT_QM'];
				$e = new einheit ();
				$det = new detail ();
				$my_arr [$z] ['WEG-FLAECHE_A'] = $det->finde_detail_inhalt ( 'EINHEIT', $einheit_id, 'WEG-Fl�che' ); // kommt als Kommazahl
				if (empty ( $my_arr [$z] ['WEG-FLAECHE_A'] )) {
					$my_arr [$z] ['WEG-FLAECHE_A'] = nummer_punkt2komma ( $einheit_qm );
				}
				$my_arr [$z] ['WEG-FLAECHE'] = nummer_komma2punkt ( $my_arr [$z] ['WEG-FLAECHE_A'] );
				
				$my_arr [$z] ['WG_NR'] = $det->finde_detail_inhalt ( 'EINHEIT', $einheit_id, 'Alte Nr' ); // kommt als Kommazahl
				
				$weg = new weg ();
				$weg->get_last_eigentuemer ( $einheit_id );
				if (isset ( $weg->eigentuemer_name )) {
					// echo '<pre>';
					// print_r($weg);
					// $my_arr[$z]['EIGENTUEMER'] = $weg->eigentuemer_name;
					$weg->get_eigentuemer_namen ( $weg->eigentuemer_id );
					$my_arr [$z] ['EIGENTUEMER_NAMEN'] = $weg->eigentuemer_name_str_u;
					$my_arr [$z] ['EIGENTUEMER_ID'] = $weg->eigentuemer_id;
				} else {
					$my_arr [$z] ['EIGENTUEMER'] = 'Unbekannt';
				}
				$mv_id = $e->get_mietvertrag_id ( $einheit_id );
				if ($mv_id) {
					$mvs = new mietvertraege ();
					$mvs->get_mietvertrag_infos_aktuell ( $mv_id );
					$kontaktdaten = $e->kontaktdaten_mieter ( $mv_id );
					// $my_arr[$z]['MIETER'] = $mvs->personen_name_string_u."\n".$kontaktdaten;
					$my_arr [$z] ['MIETER'] = $mvs->personen_name_string_u;
					$my_arr [$z] ['MIETVERTRAG_ID'] = $mv_id;
					$my_arr [$z] ['KONTAKT'] = $kontaktdaten;
					$my_arr [$z] ['EINHEIT_ID'] = $einheit_id;
					$mz = new miete ();
					$mz->mietkonto_berechnung ( $mv_id );
					$my_arr [$z] ['MIETER_SALDO'] = $mz->erg;
				} else {
					$my_arr [$z] ['MIETER'] = 'Leerstand';
				}
				$z ++;
			}
			// print_r($weg);
			
			unset ( $e );
			unset ( $mvs );
			unset ( $weg );
			
			$anz = count ( $my_arr );
			/* Berechnung Abgaben */
			for($a = 0; $a < $anz; $a ++) {
				$einheit_id = $my_arr [$a] ['EINHEIT_ID'];
				if (isset ( $my_arr [$a] ['EIGENTUEMER_ID'] )) {
					$eige_id = $my_arr [$a] ['EIGENTUEMER_ID'];
					// echo $my_arr[$a]['EIGENTUEMER_ID'];
					// die();
					$my_arr [$a] ['ABGABEN'] [] ['ABGABE_IHR'] = $my_arr [$a] ['WEG-FLAECHE'] * - 0.4;
					if (empty ( $my_arr [$a] ['WEG-FLAECHE'] )) {
						$my_arr [$a] ['ABGABEN'] [] ['ABGABE_IHR'] = $einheit_qm * - 0.4;
					}
					$my_arr [$a] ['ABGABEN'] [] ['VG'] = '30.00'; // Verwaltergeb�hr
					
					/* Kosten 1023 Reparatur Einheit */
					$my_arr [$a] ['AUSGABEN'] = $this->get_kosten_arr ( 'EINHEIT', $my_arr [$a] ['EINHEIT_ID'], $monat, $jahr, $gk->geldkonto_id, 1023 );
					$anz_rep = count ( $my_arr [$a] ['AUSGABEN'] );
					$summe_rep = 0;
					for($b = 0; $b < $anz_rep; $b ++) {
						$summe_rep += $my_arr [$a] ['AUSGABEN'] [$b] ['BETRAG'];
					}
					$my_arr [$a] ['AUSGABEN'] [$anz_rep] ['BETRAG'] = '<b>' . nummer_punkt2komma_t ( $summe_rep ) . '</b>';
					$my_arr [$a] ['AUSGABEN'] [$anz_rep] ['VERWENDUNGSZWECK'] = '<b>Summe</b>';
					
					// echo "'EINHEIT', $my_arr[$a]['EINHEIT_ID'], $monat, $jahr, $gk->geldkonto_id<br>";
					// print_r($arr);
					$mk = new mietkonto ();
					$mk->kaltmiete_monatlich ( $my_arr [$a] ['MIETVERTRAG_ID'], $monat, $jahr );
					$brutto_sollmiete_arr = explode ( '|', $mk->summe_forderung_monatlich ( $my_arr [$a] ['MIETVERTRAG_ID'], $monat, $jahr ) );
					$brutto_sollmiete = $brutto_sollmiete_arr [0];
					$my_arr [$a] ['NETTO_SOLL_MV'] = $mk->ausgangs_kaltmiete;
					
					/* Garantierte Miete abfragen */
					$net_ren_garantie_a = $det->finde_detail_inhalt ( 'EINHEIT', $einheit_id, 'WEG-KaltmieteINS' ); // kommt als Kommazahl
					$net_ren_garantie = nummer_komma2punkt ( $net_ren_garantie_a );
					$my_arr [$a] ['NETTO_SOLL_G_A'] = $net_ren_garantie_a;
					// if($einheit_id=='945'){
					// die("SANEL $einheit_id $net_ren_garantie");
					// }
					if ($net_ren_garantie > $mk->ausgangs_kaltmiete) {
						$my_arr [$a] ['NETTO_SOLL'] = $net_ren_garantie;
					} else {
						$my_arr [$a] ['NETTO_SOLL'] = $mk->ausgangs_kaltmiete;
					}
					
					$my_arr [$a] ['BRUTTO_SOLL'] = $brutto_sollmiete;
					$my_arr [$a] ['AUSZAHLUNG_ET'] = $this->get_kosten_arr ( 'Eigentuemer', $eige_id, $monat, $jahr, $gk->geldkonto_id, 5020 );
					// print_r($my_arr[$a]['AUSZAHLUNG_ET']);
					// die();
					$my_arr [$a] ['IST_EINNAHMEN'] = $this->get_kosten_arr ( 'MIETVERTRAG', $my_arr [$a] ['MIETVERTRAG_ID'], $monat, $jahr, $gk->geldkonto_id, 80001 );
					$anz_me = count ( $my_arr [$a] ['IST_EINNAHMEN'] );
					$summe_einnahmen = 0;
					for($b = 0; $b < $anz_me; $b ++) {
						$summe_einnahmen += $my_arr [$a] ['IST_EINNAHMEN'] [$b] ['BETRAG'];
					}
					$my_arr [$a] ['IST_EINNAHMEN'] [$anz_me] ['BETRAG'] = '<b>' . nummer_punkt2komma_t ( $summe_einnahmen ) . '</b>';
					$my_arr [$a] ['IST_EINNAHMEN'] [$anz_me] ['VERWENDUNGSZWECK'] = '<b>Summe</b>';
					$my_arr [$a] ['BRUTTO_IST'] = $summe_einnahmen;
					// $my_arr[$a]['SUM_EIN_AUS_MIETE'] =
					
					$pdf_tab [$a] ['MIETER_SALDO'] = $my_arr [$a] ['MIETER_SALDO'];
					$pdf_tab [$a] ['EIGENTUEMER_NAMEN'] = $my_arr [$a] ['EIGENTUEMER_NAMEN'];
					$pdf_tab [$a] ['EINHEIT_KURZNAME'] = $my_arr [$a] ['WG_NR'] . "\n(" . $my_arr [$a] ['EINHEIT_KURZNAME'] . ')';
					
					$pdf_tab [$a] ['EINHEIT_QM'] = $my_arr [$a] ['EINHEIT_QM'];
					$pdf_tab [$a] ['EINHEIT_QM_A'] = nummer_punkt2komma ( $my_arr [$a] ['EINHEIT_QM'] );
					$pdf_tab [$a] ['WEG-FLAECHE'] = $my_arr [$a] ['WEG-FLAECHE'];
					$pdf_tab [$a] ['WEG-FLAECHE_A'] = $my_arr [$a] ['WEG-FLAECHE_A'];
					$pdf_tab [$a] ['AUSZAHLUNG_ET'] = $my_arr [$a] ['AUSZAHLUNG_ET'];
					$pdf_tab [$a] ['MIETER'] = $my_arr [$a] ['MIETER'];
					/* Garantiemiete */
					$pdf_tab [$a] ['NETTO_SOLL_G_A'] = $my_arr [$a] ['NETTO_SOLL_G_A'];
					
					$pdf_tab [$a] ['BRUTTO_SOLL'] = $my_arr [$a] ['BRUTTO_SOLL'];
					$pdf_tab [$a] ['BRUTTO_SOLL_A'] = nummer_punkt2komma_t ( $my_arr [$a] ['BRUTTO_SOLL'] );
					$pdf_tab [$a] ['BRUTTO_IST'] = $my_arr [$a] ['BRUTTO_IST'];
					$pdf_tab [$a] ['BRUTTO_IST_A'] = nummer_punkt2komma_t ( $my_arr [$a] ['BRUTTO_IST'] );
					$pdf_tab [$a] ['DIFF'] = $my_arr [$a] ['BRUTTO_IST'] - $pdf_tab [$a] ['BRUTTO_SOLL'];
					$pdf_tab [$a] ['DIFF_A'] = nummer_punkt2komma_t ( $my_arr [$a] ['BRUTTO_IST'] - $pdf_tab [$a] ['BRUTTO_SOLL'] );
					
					$pdf_tab [$a] ['NETTO_SOLL'] = $my_arr [$a] ['NETTO_SOLL'];
					$pdf_tab [$a] ['NETTO_SOLL_MV'] = $my_arr [$a] ['NETTO_SOLL_MV'];
					$pdf_tab [$a] ['NETTO_SOLL_DIFF'] = $pdf_tab [$a] ['NETTO_SOLL'] - $pdf_tab [$a] ['NETTO_SOLL_MV'];
					$pdf_tab [$a] ['NETTO_SOLL_A'] = nummer_punkt2komma_t ( $my_arr [$a] ['NETTO_SOLL'] );
					// echo '<pre>';
					// print_r($my_arr);
					// die();
					if (empty ( $my_arr [$a] ['WEG-FLAECHE'] )) {
						$pdf_tab [$a] ['ABGABE_IHR'] = $pdf_tab [$a] ['EINHEIT_QM'] * - 0.4;
						// die('SSS');
					} else {
						$pdf_tab [$a] ['ABGABE_IHR'] = $my_arr [$a] ['WEG-FLAECHE'] * - 0.4;
						// die('OKOKOK');
					}
					$pdf_tab [$a] ['ABGABE_IHR_A'] = nummer_punkt2komma ( $pdf_tab [$a] ['ABGABE_IHR'] );
					$pdf_tab [$a] ['ABGABE_HV'] = '-30.00';
					$pdf_tab [$a] ['ABGABE_HV_A'] = '-30,00';
					$pdf_tab [$a] ['ZWISCHENSUMME'] = $my_arr [$a] ['NETTO_SOLL'] + $pdf_tab [$a] ['ABGABE_IHR'] + $pdf_tab [$a] ['ABGABE_HV'];
					
					$pdf_tab [$a] ['ZWISCHENSUMME_A'] = nummer_punkt2komma ( $my_arr [$a] ['NETTO_SOLL'] + $pdf_tab [$a] ['ABGABE_IHR'] + $pdf_tab [$a] ['ABGABE_HV'] );
					if (nummer_komma2punkt ( $pdf_tab [$a] ['ZWISCHENSUMME_A'] ) < 0.00) {
						$pdf_tab [$a] ['ZWISCHENSUMME_A'] = '0,00';
					}
					
					$pdf_tab [$a] ['SUMME_REP'] = $summe_rep;
					$pdf_tab [$a] ['SUMME_REP_A'] = nummer_punkt2komma ( $summe_rep );
					$pdf_tab [$a] ['ENDSUMME'] = $pdf_tab [$a] ['ZWISCHENSUMME'];
					$pdf_tab [$a] ['ENDSUMME_A'] = $pdf_tab [$a] ['ZWISCHENSUMME_A'];
					
					// $pdf_tab[$a]['ENDSUMME'] = $pdf_tab[$a]['ZWISCHENSUMME'] + $summe_rep;
					// $pdf_tab[$a]['ENDSUMME_A'] = '<b>'.nummer_punkt2komma_t($pdf_tab[$a]['ZWISCHENSUMME'] + $summe_rep).'</b>';
					
					// $pdf_tab[$a]['EIG_AUSZAHLUNG'] = $this->get_kosten_arr('EINHEIT', $my_arr[$a]['EINHEIT_ID'], $monat, $jahr, $gk->geldkonto_id,80001);
					
					$e_nam = $pdf_tab [$a] ['EIGENTUEMER_NAMEN'];
					$ein_nam = $pdf_tab [$a] ['EINHEIT_KURZNAME'];
					
					/* �bersichtstabelle */
					$uebersicht [$a] ['EINHEIT_KURZNAME'] = $ein_nam;
					$uebersicht [$a] ['EIGENTUEMER_NAMEN'] = $e_nam;
					$uebersicht [$a] ['MIETER'] = $pdf_tab [$a] ['MIETER'];
					$uebersicht [$a] ['MIETER_SALDO'] = $pdf_tab [$a] ['MIETER_SALDO'];
					$uebersicht [$a] ['MIETER_SALDO_A'] = nummer_punkt2komma ( $pdf_tab [$a] ['MIETER_SALDO'] );
					$uebersicht [$a] ['NETTO_SOLL_G_A'] = $pdf_tab [$a] ['NETTO_SOLL_G_A'];
					$uebersicht [$a] ['NETTO_SOLL_A'] = nummer_punkt2komma ( $pdf_tab [$a] ['NETTO_SOLL_MV'] );
					$uebersicht [$a] ['NETTO_SOLL_DIFF_A'] = nummer_punkt2komma ( $pdf_tab [$a] ['NETTO_SOLL_DIFF'] );
					$uebersicht [$a] ['ABGABE_HV_A'] = $pdf_tab [$a] ['ABGABE_HV_A'];
					$uebersicht [$a] ['ABGABE_IHR'] = $pdf_tab [$a] ['ABGABE_IHR_A'];
					
					$uebersicht [$a] ['SUMME_REP'] = $pdf_tab [$a] ['SUMME_REP'];
					$uebersicht [$a] ['SUMME_REP_A'] = $pdf_tab [$a] ['SUMME_REP_A'];
					
					$uebersicht [$a] ['WEG-FLAECHE_A'] = $pdf_tab [$a] ['WEG-FLAECHE_A'];
					$uebersicht [$a] ['EINHEIT_QM_A'] = $pdf_tab [$a] ['EINHEIT_QM_A'];
					
					$uebersicht [$a] ['ENDSUMME_A'] = $pdf_tab [$a] ['ENDSUMME_A'];
					// $summe_alle_eigentuemer += $pdf_tab[$a]['ENDSUMME'];
					if ($pdf_tab [$a] ['ENDSUMME'] > 0) {
						$summe_alle_eigentuemer += $pdf_tab [$a] ['ENDSUMME'];
					} else {
						$summe_nachzahler += $pdf_tab [$a] ['ENDSUMME'];
					}
					
					// print_r($uebersicht);
					// die();
					// $uebersicht[$a]['ZWISCHEN']
					if ($lang == 'en') {
						$cols = array (
								'EIGENTUEMER_NAMEN' => "owner",
								'EINHEIT_KURZNAME' => "apart.No",
								'MIETER' => 'tenant',
								'WEG-FLAECHE_A' => 'size m�',
								'NETTO_SOLL_A' => 'net rent',
								'ABGABE_IHR_A' => 'for maint.',
								'ABGABE_HV_A' => 'mng. fee',
								'ENDSUMME_A' => 'transfer' 
						);
						$pdf->ezTable ( $pdf_tab, $cols, "<b>$monat_name $jahr - Overview - $ein_nam</b>", array (
								'showHeadings' => 1,
								'shaded' => 1,
								'titleFontSize' => 8,
								'fontSize' => 7,
								'xPos' => 50,
								'xOrientation' => 'right',
								'width' => 500,
								'cols' => array (
										'ENDSUMME_A' => array (
												'justification' => 'right',
												'width' => 50 
										),
										'EIGENTUEMER_NAMEN' => array (
												'justification' => 'left',
												'width' => 50 
										) 
								) 
						) );
					} else {
						$cols = array (
								'EIGENTUEMER_NAMEN' => "Eigent�mer",
								'EINHEIT_KURZNAME' => "EINHEIT",
								'MIETER' => 'Mieter',
								'WEG-FLAECHE_A' => 'Eig. m�',
								'BRUTTO_SOLL_A' => 'Warm SOLL',
								'BRUTTO_IST_A' => 'Warm IST',
								'DIFF_A' => 'DIFF',
								'NETTO_SOLL_A' => 'rent p.m.\n (actual)',
								'ABGABE_IHR_A' => 'IHR',
								'ABGABE_HV_A' => 'HV',
								'SUMME_REP_A' => 'Rep.',
								'ENDSUMME_A' => 'AUSZAHLUNG' 
						);
						$pdf->ezTable ( $pdf_tab, $cols, "<b>$monat_name $jahr - Gesamt�bersicht - $ein_nam</b>", array (
								'showHeadings' => 1,
								'shaded' => 1,
								'titleFontSize' => 8,
								'fontSize' => 7,
								'xPos' => 30,
								'xOrientation' => 'right',
								'width' => 550,
								'cols' => array (
										'ENDSUMME_A' => array (
												'justification' => 'right',
												'width' => 50 
										),
										'EIGENTUEMER_NAMEN' => array (
												'justification' => 'left',
												'width' => 50 
										) 
								) 
						) );
					}
					
					/*
					 * if($pdf_tab[$a]['BRUTTO_IST']<$pdf_tab[$a]['ENDSUMME']){
					 * $pdf->setColor(1.0,0.0,0.0);
					 * $pdf->ezSetDy(-20); //abstand
					 * if($lang=='en'){
					 * $pdf->ezText("no payout possible!", 12);
					 * }else{
					 * $pdf->ezText("Keine Auszahlung m�glich!", 12);
					 * }
					 * }
					 */
					
					// print_r($table_arr);
					// die();
					
					$pdf->ezSetDy ( - 20 ); // abstand
					if (is_array ( $my_arr [$a] ['AUSGABEN'] )) {
						if ($lang == 'en') {
							$cols = array (
									'DATUM' => "Date",
									'VERWENDUNGSZWECK' => "Description",
									'BETRAG' => "Amount" 
							);
							// $pdf->ezTable($my_arr[$a]['AUSGABEN'], $cols, "<b>$monat_name $jahr - Maintenance bills 1023 - $ein_nam</b>", array('showHeadings'=>1,'shaded'=>1, 'titleFontSize' => 8, 'fontSize' => 7, 'xPos'=>50,'xOrientation'=>'right', 'width'=>500, 'cols'=>array('BETRAG'=>array('justification'=>'right', 'width'=>65), 'DATUM'=>array('justification'=>'left', 'width'=>50))));
						} else {
							$cols = array (
									'DATUM' => "Datum",
									'VERWENDUNGSZWECK' => "Buchungstext",
									'BETRAG' => "Betrag" 
							);
							$pdf->ezTable ( $my_arr [$a] ['AUSGABEN'], $cols, "<b>$monat_name $jahr - Reparaturen 1023 - $ein_nam</b>", array (
									'showHeadings' => 1,
									'shaded' => 1,
									'titleFontSize' => 8,
									'fontSize' => 7,
									'xPos' => 50,
									'xOrientation' => 'right',
									'width' => 500,
									'cols' => array (
											'BETRAG' => array (
													'justification' => 'right',
													'width' => 65 
											),
											'DATUM' => array (
													'justification' => 'left',
													'width' => 50 
											) 
									) 
							) );
						}
					} else {
						$pdf->ezText ( "Keine Reparaturen", 12 );
					}
					$pdf->ezSetDy ( - 20 ); // abstand
					/* TAbelle Auszahlung an Eigent�mer */
					// print_r($my_arr[$a]['AUSZAHLUNG_ET']);
					// die();
					if (is_array ( $my_arr [$a] ['AUSZAHLUNG_ET'] )) {
						
						if ($lang == 'en') {
							$cols = array (
									'DATUM' => "Date",
									'VERWENDUNGSZWECK' => "Description",
									'BETRAG' => "Amount" 
							);
							// $pdf->ezTable($my_arr[$a]['AUSZAHLUNG_ET'], $cols, "<b>$monat_name $jahr - transfer 5020 - $ein_nam</b>", array('showHeadings'=>1,'shaded'=>1, 'titleFontSize' => 8, 'fontSize' => 7, 'xPos'=>50,'xOrientation'=>'right', 'width'=>500, 'cols'=>array('BETRAG'=>array('justification'=>'right', 'width'=>65),'DATUM'=>array('justification'=>'left', 'width'=>50))));
						} else {
							$cols = array (
									'DATUM' => "Datum",
									'VERWENDUNGSZWECK' => "Buchungstext",
									'BETRAG' => "Betrag" 
							);
							$pdf->ezTable ( $my_arr [$a] ['AUSZAHLUNG_ET'], $cols, "<b>$monat_name $jahr - �berweisung 80001 - $ein_nam</b>", array (
									'showHeadings' => 1,
									'shaded' => 1,
									'titleFontSize' => 8,
									'fontSize' => 7,
									'xPos' => 50,
									'xOrientation' => 'right',
									'width' => 500,
									'cols' => array (
											'BETRAG' => array (
													'justification' => 'right',
													'width' => 65 
											),
											'DATUM' => array (
													'justification' => 'left',
													'width' => 50 
											) 
									) 
							) );
						}
					}
					/*
					 * if(is_array($my_arr[$a]['IST_EINNAHMEN'])){
					 * if($lang=='en'){
					 * $cols = array('DATUM'=>"Date", 'VERWENDUNGSZWECK'=>"Description", 'BETRAG'=>"Amount");
					 * $pdf->ezTable($my_arr[$a]['IST_EINNAHMEN'], $cols, "<b>$monat_name $jahr - income overview 80001 - $ein_nam</b>", array('showHeadings'=>1,'shaded'=>1, 'titleFontSize' => 8, 'fontSize' => 7, 'xPos'=>50,'xOrientation'=>'right', 'width'=>500, 'cols'=>array('BETRAG'=>array('justification'=>'right', 'width'=>65),'DATUM'=>array('justification'=>'left', 'width'=>50))));
					 * }else{
					 * $cols = array('DATUM'=>"Datum", 'VERWENDUNGSZWECK'=>"Buchungstext", 'BETRAG'=>"Betrag");
					 * $pdf->ezTable($my_arr[$a]['IST_EINNAHMEN'], $cols, "<b>$monat_name $jahr - Einnahmen 80001 - $ein_nam</b>", array('showHeadings'=>1,'shaded'=>1, 'titleFontSize' => 8, 'fontSize' => 7, 'xPos'=>50,'xOrientation'=>'right', 'width'=>500, 'cols'=>array('BETRAG'=>array('justification'=>'right', 'width'=>65),'DATUM'=>array('justification'=>'left', 'width'=>50))));
					 * }
					 *
					 * }else{
					 * $pdf->ezText("Keine Mieteinnahmen", 12);
					 * }
					 */
					
					// $cols = array('DATUM'=>"Datum",'VERWENDUNGSZWECK'=>"Buchungstext", 'BETRAG'=>"Betrag");
					// $pdf->ezTable($pdf_tab[$a]['EIG_AUSZAHLUNG'], $cols, "<b>$monat_name $jahr - Auszahlung an Eigent�mer 80001 - $ein_nam</b>", array('showHeadings'=>1,'shaded'=>1, 'titleFontSize' => 8, 'fontSize' => 7, 'xPos'=>50,'xOrientation'=>'right', 'width'=>500, 'cols'=>array('BETRAG'=>array('justification'=>'right', 'width'=>65),'DATUM'=>array('justification'=>'left', 'width'=>50))));
					
					$pdf->ezNewPage ();
					unset ( $pdf_tab );
				}
			}
			$uebersicht [$anz] ['EIGENTUEMER_NAMEN'] = 'Auszahlungssumme';
			$uebersicht [$anz] ['ENDSUMME_A'] = nummer_punkt2komma_t ( $summe_alle_eigentuemer );
			$uebersicht [$anz + 1] ['EIGENTUEMER_NAMEN'] = 'Zu erhalten';
			$uebersicht [$anz + 1] ['ENDSUMME_A'] = nummer_punkt2komma_t ( $summe_nachzahler );
			
			if ($lang == 'en') {
				$cols = array (
						'EINHEIT_KURZNAME' => "Apt",
						'EINHEIT_QM_A' => 'MVm�',
						'WEG-FLAECHE_A' => 'm�',
						'EIGENTUEMER_NAMEN' => "Own",
						'MIETER' => "Tenant",
						'MIETER_SALDO_A' => 'current',
						'NETTO_SOLL_G_A' => "Garanty",
						'NETTO_SOLL_A' => "net rent",
						'NETTO_SOLL_DIFF_A' => "diff",
						'ABGABE_HV_A' => "fee",
						'ABGABE_IHR' => "for maint.",
						'ENDSUMME_A' => "Amount",
						'SUMME_REP_A' => 'Rep.' 
				);
			}
			
			$pdf->ezTable ( $uebersicht, $cols, null, array (
					'showHeadings' => 1,
					'shaded' => 1,
					'titleFontSize' => 8,
					'fontSize' => 7,
					'xPos' => 50,
					'xOrientation' => 'right',
					'width' => 500 
			) );
			// print_r($pdf_tab);
			// print_r($pdf_tab_soll);
			// echo '<pre>';
			// print_r($my_arr);
			
			// die();
			
			ob_clean (); // ausgabepuffer leeren
			header ( "Content-type: application/pdf" ); // wird von MSIE ignoriert
			$pdf->ezStream ();
		} else {
			die ( "Keine Einheiten im Objekt $objekt_id" );
		}
	}
	function form_sepa_ueberweisung_anzeigen($arr) {
		$gk = new geldkonto_info ();
		$gk->geld_konto_ermitteln ( 'OBJEKT', $_SESSION ['objekt_id'] );
		if (! $gk->geldkonto_id) {
			die ( 'Geldkonto vom Objekt nicht bekannt!' );
		}
		
		$monat = date ( "m" );
		$jahr = date ( "Y" );
		
		$f = new formular ();
		// echo '<pre>';
		// print_r($arr);
		if (is_array ( $arr )) {
			$anz = count ( $arr );
			echo "<table class=\"sortable\">";
			echo "<tr><th>EINHEIT</th><th>EIGENT�MER</th><th>Mieter</th><th>SALDO AKT</th><th>KALTM</th><th>INS DIFF</th><th>HV</th><th>IHR</th><th>REP</th><th>TRANSFER</th><th>OPT</th><th>OPT2</th></tr>";
			for($a = 0; $a < $anz; $a ++) {
				$e_kn = $arr [$a] ['EINHEIT_KURZNAME'];
				$et = $arr [$a] ['EIGENTUEMER_NAMEN'];
				$eig_id = $arr [$a] ['EIG_ID'];
				
				$weg = new weg ();
				$weg->get_eigentumer_id_infos3 ( $eig_id );
				
				$mieter = $arr [$a] ['MIETER'];
				$ms = $arr [$a] ['MIETER_SALDO'];
				$nkm = $arr [$a] ['NETTO_SOLL_A'];
				$diff = $arr [$a] ['NETTO_SOLL_DIFF_A'];
				$hv = $arr [$a] ['ABGABE_HV_A'];
				$ihr = $arr [$a] ['ABGABE_IHR'];
				$rep = $arr [$a] ['SUMME_REP'];
				$transfer = nummer_komma2punkt ( nummer_punkt2komma ( $arr [$a] ['TRANSFER'] ) );
				
				$sep = new sepa ();
				$betrag_in_sepa = $sep->get_summe_sepa_sammler ( $gk->geldkonto_id, 'ET-AUSZAHLUNG', 'Eigentuemer', $eig_id );
				// echo "<br>$betrag_in_sepa $transfer";
				if ($betrag_in_sepa < $transfer) {
					// echo "<br>$betrag_in_sepa< $transfer";
					// die();
					$link_sepa_ueberweisen = "<a href=\"?daten=listen&option=sepa_ueberweisen&eig_et=$eig_id&betrag=$transfer\">SEPA-�</a>";
					/* Form */
					echo "<form name=\"sepa_lg\" method=\"post\" action=\"\">";
					
					if ($transfer > 0) {
						echo "<tr class=\"zeile2\"><td>$e_kn</td><td>$et</td><td>$mieter</td><td>$ms</td><td>$nkm</td><td><b>$diff</b></td><td>$hv</td><td>$ihr</td><td>$rep</td><td style=\"color:white;\">";
						// <b>$transfer</b>
						$js_action = "onfocus=\"this.value='';\"";
						$transfer_a = nummer_punkt2komma ( $transfer );
						$f->text_feld ( 'Betrag', 'betrag', $transfer_a, 10, 'betrag', $js_action );
						echo "</td>";
					} else {
						echo "<tr class=\"zeile1\"><td>$e_kn</td><td>$et</td><td>$mieter</td><td>$ms</td><td>$nkm</td><td><b>$diff</b></td><td>$hv</td><td>$ihr</td><td>$rep</td><td style=\"color:white;\">";
						// <b>$transfer</b>
						$js_action = "onfocus=\"this.value='';\"";
						$transfer_a = nummer_punkt2komma ( $transfer );
						$f->text_feld ( 'Betrag', 'betrag', $transfer_a, 10, 'betrag', $js_action );
						echo "</td>";
					}
					echo "<td>$link_sepa_ueberweisen</td>";
					/* Wenn Geldkontenvorhanden */
					$sep = new sepa ();
					echo "<td>";
					if ($sep->dropdown_sepa_geldkonten ( '�berweisen an', 'empf_sepa_gk_id', 'empf_sepa_gk_id', 'Eigentuemer', $eig_id ) == true) {
						// $f->text_feld('VERWENDUNG', 'vzweck', "Eigentuemerentnahme $weg->einheit_kurzname Auszahlung $monat.$jahr", 100, 'vzweck', '');
						$f->hidden_feld ( 'option', 'sepa_sammler_hinzu' );
						$f->hidden_feld ( 'vzweck', "$weg->einheit_kurzname $monat.$jahr / Transfer to owner / Auszahlung" );
						$f->hidden_feld ( 'kat', 'ET-AUSZAHLUNG' );
						$f->hidden_feld ( 'gk_id', $gk->geldkonto_id );
						$f->hidden_feld ( 'kos_typ', 'Eigentuemer' );
						$f->hidden_feld ( 'kos_id', $eig_id );
						$f->hidden_feld ( 'konto', 5020 );
						if ($eig_id == '133' or $eig_id == '139' or $eig_id == '200') {
							$f->send_button ( 'btn_Sepa', 'Zahn�rzte Aufpassen!!!!' );
						} else {
							$f->send_button ( 'btn_Sepa', 'inSEPA' );
						}
					}
					echo "</td>";
					echo "</tr>";
					$f->ende_formular ();
				} else {
					// echo "$betrag_in_sepa vorhanden<br>";
				}
			}
			
			echo "</table>";
		}
	}
	function sepa_ueberweisung_anzeigen($arr) {
		// echo '<pre>';
		// print_r($arr);
		if (is_array ( $arr )) {
			$anz = count ( $arr );
			echo "<table class=\"sortable\">";
			echo "<tr><th>EINHEIT</th><th>EIGENT�MER</th><th>Mieter</th><th>SALDO AKT</th><th>KALTM</th><th>INS DIFF</th><th>HV</th><th>IHR</th><th>REP</th><th>TRANSFER</th><th>OPT</th></tr>";
			for($a = 0; $a < $anz; $a ++) {
				$e_kn = $arr [$a] ['EINHEIT_KURZNAME'];
				$et = $arr [$a] ['EIGENTUEMER_NAMEN'];
				$eig_id = $arr [$a] ['EIG_ID'];
				$mieter = $arr [$a] ['MIETER'];
				$ms = $arr [$a] ['MIETER_SALDO'];
				$nkm = $arr [$a] ['NETTO_SOLL_A'];
				$diff = $arr [$a] ['NETTO_SOLL_DIFF_A'];
				$hv = $arr [$a] ['ABGABE_HV_A'];
				$ihr = $arr [$a] ['ABGABE_IHR'];
				$rep = $arr [$a] ['SUMME_REP'];
				$transfer = nummer_komma2punkt ( nummer_punkt2komma ( $arr [$a] ['TRANSFER'] ) );
				$link_sepa_ueberweisen = "<a href=\"?daten=listen&option=sepa_ueberweisen&eig_et=$eig_id&betrag=$transfer\">SEPA-�</a>";
				if ($mieter == 'Leerstand') {
					echo "<tr class=\"zeile2\"><td>$e_kn</td><td>$et</td><td>$mieter</td><td>$ms</td><td>$nkm</td><td><b>$diff</b></td><td>$hv</td><td>$ihr</td><td>$rep</td><td style=\"color:white;\"><b>$transfer</b></td><td>$link_sepa_ueberweisen</td></tr>";
				} else {
					echo "<tr class=\"zeile1\"><td>$e_kn</td><td>$et</td><td>$mieter</td>";
					if ($ms < 0) {
						echo "<td style=\"background:red;\">$ms</td>";
					} else {
						echo "<td>$ms</td>";
					}
					echo "<td>$nkm</td><td><b>$diff</b></td><td>$hv</td><td>$ihr</td><td>$rep</td>";
					if ($transfer > 0) {
						echo "<td style=\"background:#656565;color:white;\"><b>$transfer</b></td>";
					} else {
						echo "<td style=\"background:red; color:white;\"><b>$transfer</b></td>";
					}
					echo "<td>$link_sepa_ueberweisen</td></tr>";
				}
			}
			echo "</table>";
		}
	}
	function inspiration_pdf_kurz_6_ALT_OK032014($ausgezogene = 0, $objekt_id, $monat, $jahr, $lang = 'de') {
		$monat_name = monat2name ( $monat );
		$pdf = new Cezpdf ( 'a4', 'portrait' );
		$bpdf = new b_pdf ();
		$bpdf->b_header ( $pdf, 'Partner', $_SESSION ['partner_id'], 'portrait', 'pdfclass/fonts/Helvetica.afm', 6 );
		$pdf->ezStopPageNumbers (); // seitennummerirung beenden
		$gk = new geldkonto_info ();
		$gk->geld_konto_ermitteln ( 'OBJEKT', $objekt_id );
		echo '<pre>';
		// print_r($gk);
		if (! $gk->geldkonto_id) {
			die ( 'Geldkonto zum Objekt hinzuf�gen!!!' );
		}
		$db_abfrage = "SELECT OBJEKT_KURZNAME, HAUS_STRASSE, HAUS_NUMMER, `EINHEIT_KURZNAME` , `EINHEIT_ID`,  ltrim(rtrim(EINHEIT_LAGE)) AS EINHEIT_LAGE, `EINHEIT_QM` FROM EINHEIT , HAUS, OBJEKT
WHERE `EINHEIT_AKTUELL` = '1' && EINHEIT.HAUS_ID = HAUS.HAUS_ID && HAUS.OBJEKT_ID=OBJEKT.OBJEKT_ID && HAUS_AKTUELL='1' && OBJEKT_AKTUELL='1' && OBJEKT.OBJEKT_ID='$objekt_id' 
ORDER BY EINHEIT_KURZNAME";
		
		$result = mysql_query ( $db_abfrage );
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			$z = 0;
			while ( $row = mysql_fetch_assoc ( $result ) ) {
				$my_arr [] = $row;
				$einheit_id = $row ['EINHEIT_ID'];
				$einheit_qm = $row ['EINHEIT_QM'];
				$e = new einheit ();
				$det = new detail ();
				$my_arr [$z] ['WEG-FLAECHE_A'] = $det->finde_detail_inhalt ( 'EINHEIT', $einheit_id, 'WEG-Fl�che' ); // kommt als Kommazahl
				if (empty ( $my_arr [$z] ['WEG-FLAECHE_A'] )) {
					$my_arr [$z] ['WEG-FLAECHE_A'] = nummer_punkt2komma ( $einheit_qm );
				}
				$my_arr [$z] ['WEG-FLAECHE'] = nummer_komma2punkt ( $my_arr [$z] ['WEG-FLAECHE_A'] );
				
				$my_arr [$z] ['WG_NR'] = $det->finde_detail_inhalt ( 'EINHEIT', $einheit_id, 'Alte Nr' ); // kommt als Kommazahl
				
				$weg = new weg ();
				$weg->get_last_eigentuemer ( $einheit_id );
				if (isset ( $weg->eigentuemer_name )) {
					// echo '<pre>';
					// print_r($weg);
					// $my_arr[$z]['EIGENTUEMER'] = $weg->eigentuemer_name;
					$weg->get_eigentuemer_namen ( $weg->eigentuemer_id );
					$my_arr [$z] ['EIGENTUEMER_NAMEN'] = $weg->eigentuemer_name_str_u;
					$my_arr [$z] ['EIGENTUEMER_ID'] = $weg->eigentuemer_id;
				} else {
					$my_arr [$z] ['EIGENTUEMER'] = 'Unbekannt';
				}
				$mv_id = $e->get_mietvertrag_id ( $einheit_id );
				if ($mv_id) {
					$mvs = new mietvertraege ();
					$mvs->get_mietvertrag_infos_aktuell ( $mv_id );
					$kontaktdaten = $e->kontaktdaten_mieter ( $mv_id );
					// $my_arr[$z]['MIETER'] = $mvs->personen_name_string_u."\n".$kontaktdaten;
					$my_arr [$z] ['MIETER'] = $mvs->personen_name_string_u;
					$my_arr [$z] ['MIETVERTRAG_ID'] = $mv_id;
					$my_arr [$z] ['KONTAKT'] = $kontaktdaten;
					$my_arr [$z] ['EINHEIT_ID'] = $einheit_id;
					$mz = new miete ();
					$mz->mietkonto_berechnung ( $mv_id );
					$my_arr [$z] ['MIETER_SALDO'] = $mz->erg;
				} else {
					$my_arr [$z] ['MIETER'] = 'Leerstand';
				}
				$z ++;
			}
			// print_r($weg);
			
			unset ( $e );
			unset ( $mvs );
			unset ( $weg );
			
			$anz = count ( $my_arr );
			/* Berechnung Abgaben */
			for($a = 0; $a < $anz; $a ++) {
				$einheit_id = $my_arr [$a] ['EINHEIT_ID'];
				if (isset ( $my_arr [$a] ['EIGENTUEMER_ID'] )) {
					$eige_id = $my_arr [$a] ['EIGENTUEMER_ID'];
					// echo $my_arr[$a]['EIGENTUEMER_ID'];
					// die();
					$my_arr [$a] ['ABGABEN'] [] ['ABGABE_IHR'] = $my_arr [$a] ['WEG-FLAECHE'] * - 0.4;
					if (empty ( $my_arr [$a] ['WEG-FLAECHE'] )) {
						$my_arr [$a] ['ABGABEN'] [] ['ABGABE_IHR'] = $einheit_qm * - 0.4;
					}
					$my_arr [$a] ['ABGABEN'] [] ['VG'] = '30.00'; // Verwaltergeb�hr
					
					/* Kosten 1023 Reparatur Einheit */
					$my_arr [$a] ['AUSGABEN'] = $this->get_kosten_arr ( 'EINHEIT', $my_arr [$a] ['EINHEIT_ID'], $monat, $jahr, $gk->geldkonto_id, 1023 );
					$anz_rep = count ( $my_arr [$a] ['AUSGABEN'] );
					$summe_rep = 0;
					for($b = 0; $b < $anz_rep; $b ++) {
						$summe_rep += $my_arr [$a] ['AUSGABEN'] [$b] ['BETRAG'];
					}
					$my_arr [$a] ['AUSGABEN'] [$anz_rep] ['BETRAG'] = '<b>' . nummer_punkt2komma_t ( $summe_rep ) . '</b>';
					$my_arr [$a] ['AUSGABEN'] [$anz_rep] ['VERWENDUNGSZWECK'] = '<b>Summe</b>';
					
					// echo "'EINHEIT', $my_arr[$a]['EINHEIT_ID'], $monat, $jahr, $gk->geldkonto_id<br>";
					// print_r($arr);
					$mk = new mietkonto ();
					$mk->kaltmiete_monatlich ( $my_arr [$a] ['MIETVERTRAG_ID'], $monat, $jahr );
					$brutto_sollmiete_arr = explode ( '|', $mk->summe_forderung_monatlich ( $my_arr [$a] ['MIETVERTRAG_ID'], $monat, $jahr ) );
					$brutto_sollmiete = $brutto_sollmiete_arr [0];
					$my_arr [$a] ['NETTO_SOLL_MV'] = $mk->ausgangs_kaltmiete;
					
					/* Garantierte Miete abfragen */
					$net_ren_garantie_a = $det->finde_detail_inhalt ( 'EINHEIT', $einheit_id, 'WEG-KaltmieteINS' ); // kommt als Kommazahl
					$net_ren_garantie = nummer_komma2punkt ( $net_ren_garantie_a );
					$my_arr [$a] ['NETTO_SOLL_G_A'] = $net_ren_garantie_a;
					// if($einheit_id=='945'){
					// die("SANEL $einheit_id $net_ren_garantie");
					// }
					if ($net_ren_garantie > $mk->ausgangs_kaltmiete) {
						$my_arr [$a] ['NETTO_SOLL'] = $net_ren_garantie;
					} else {
						$my_arr [$a] ['NETTO_SOLL'] = $mk->ausgangs_kaltmiete;
					}
					
					$my_arr [$a] ['BRUTTO_SOLL'] = $brutto_sollmiete;
					$my_arr [$a] ['AUSZAHLUNG_ET'] = $this->get_kosten_arr ( 'Eigentuemer', $eige_id, $monat, $jahr, $gk->geldkonto_id, 5020 );
					// print_r($my_arr[$a]['AUSZAHLUNG_ET']);
					// die();
					$my_arr [$a] ['IST_EINNAHMEN'] = $this->get_kosten_arr ( 'MIETVERTRAG', $my_arr [$a] ['MIETVERTRAG_ID'], $monat, $jahr, $gk->geldkonto_id, 80001 );
					$anz_me = count ( $my_arr [$a] ['IST_EINNAHMEN'] );
					$summe_einnahmen = 0;
					for($b = 0; $b < $anz_me; $b ++) {
						$summe_einnahmen += $my_arr [$a] ['IST_EINNAHMEN'] [$b] ['BETRAG'];
					}
					$my_arr [$a] ['IST_EINNAHMEN'] [$anz_me] ['BETRAG'] = '<b>' . nummer_punkt2komma_t ( $summe_einnahmen ) . '</b>';
					$my_arr [$a] ['IST_EINNAHMEN'] [$anz_me] ['VERWENDUNGSZWECK'] = '<b>Summe</b>';
					$my_arr [$a] ['BRUTTO_IST'] = $summe_einnahmen;
					// $my_arr[$a]['SUM_EIN_AUS_MIETE'] =
					
					$pdf_tab [$a] ['MIETER_SALDO'] = $my_arr [$a] ['MIETER_SALDO'];
					$pdf_tab [$a] ['EIGENTUEMER_NAMEN'] = $my_arr [$a] ['EIGENTUEMER_NAMEN'];
					$pdf_tab [$a] ['EINHEIT_KURZNAME'] = $my_arr [$a] ['EINHEIT_KURZNAME'];
					
					$pdf_tab [$a] ['EINHEIT_QM'] = $my_arr [$a] ['EINHEIT_QM'];
					$pdf_tab [$a] ['EINHEIT_QM_A'] = nummer_punkt2komma ( $my_arr [$a] ['EINHEIT_QM'] );
					$pdf_tab [$a] ['WEG-FLAECHE'] = $my_arr [$a] ['WEG-FLAECHE'];
					$pdf_tab [$a] ['WEG-FLAECHE_A'] = $my_arr [$a] ['WEG-FLAECHE_A'];
					$pdf_tab [$a] ['AUSZAHLUNG_ET'] = $my_arr [$a] ['AUSZAHLUNG_ET'];
					$pdf_tab [$a] ['MIETER'] = $my_arr [$a] ['MIETER'];
					/* Garantiemiete */
					$pdf_tab [$a] ['NETTO_SOLL_G_A'] = $my_arr [$a] ['NETTO_SOLL_G_A'];
					
					$pdf_tab [$a] ['BRUTTO_SOLL'] = $my_arr [$a] ['BRUTTO_SOLL'];
					$pdf_tab [$a] ['BRUTTO_SOLL_A'] = nummer_punkt2komma_t ( $my_arr [$a] ['BRUTTO_SOLL'] );
					$pdf_tab [$a] ['BRUTTO_IST'] = $my_arr [$a] ['BRUTTO_IST'];
					$pdf_tab [$a] ['BRUTTO_IST_A'] = nummer_punkt2komma_t ( $my_arr [$a] ['BRUTTO_IST'] );
					$pdf_tab [$a] ['DIFF'] = $my_arr [$a] ['BRUTTO_IST'] - $pdf_tab [$a] ['BRUTTO_SOLL'];
					$pdf_tab [$a] ['DIFF_A'] = nummer_punkt2komma_t ( $my_arr [$a] ['BRUTTO_IST'] - $pdf_tab [$a] ['BRUTTO_SOLL'] );
					
					$pdf_tab [$a] ['NETTO_SOLL'] = $my_arr [$a] ['NETTO_SOLL'];
					$pdf_tab [$a] ['NETTO_SOLL_MV'] = $my_arr [$a] ['NETTO_SOLL_MV'];
					$pdf_tab [$a] ['NETTO_SOLL_DIFF'] = $pdf_tab [$a] ['NETTO_SOLL'] - $pdf_tab [$a] ['NETTO_SOLL_MV'];
					$pdf_tab [$a] ['NETTO_SOLL_A'] = nummer_punkt2komma_t ( $my_arr [$a] ['NETTO_SOLL'] );
					// echo '<pre>';
					// print_r($my_arr);
					// die();
					if (empty ( $my_arr [$a] ['WEG-FLAECHE'] )) {
						$pdf_tab [$a] ['ABGABE_IHR'] = $pdf_tab [$a] ['EINHEIT_QM'] * - 0.4;
						// die('SSS');
					} else {
						$pdf_tab [$a] ['ABGABE_IHR'] = $my_arr [$a] ['WEG-FLAECHE'] * - 0.4;
						// die('OKOKOK');
					}
					$pdf_tab [$a] ['ABGABE_IHR_A'] = nummer_punkt2komma ( $pdf_tab [$a] ['ABGABE_IHR'] );
					$pdf_tab [$a] ['ABGABE_HV'] = '-30.00';
					$pdf_tab [$a] ['ABGABE_HV_A'] = '-30,00';
					$pdf_tab [$a] ['ZWISCHENSUMME'] = $my_arr [$a] ['NETTO_SOLL'] + $pdf_tab [$a] ['ABGABE_IHR'] + $pdf_tab [$a] ['ABGABE_HV'];
					
					$pdf_tab [$a] ['ZWISCHENSUMME_A'] = nummer_punkt2komma ( $my_arr [$a] ['NETTO_SOLL'] + $pdf_tab [$a] ['ABGABE_IHR'] + $pdf_tab [$a] ['ABGABE_HV'] );
					// if(nummer_komma2punkt($pdf_tab[$a]['ZWISCHENSUMME_A'])<0.00){
					// $pdf_tab[$a]['ZWISCHENSUMME_A'] ='0,00';
					// }
					
					$pdf_tab [$a] ['SUMME_REP'] = $summe_rep;
					$pdf_tab [$a] ['SUMME_REP_A'] = nummer_punkt2komma ( $summe_rep );
					$pdf_tab [$a] ['ENDSUMME'] = $pdf_tab [$a] ['ZWISCHENSUMME'];
					$pdf_tab [$a] ['ENDSUMME_A'] = $pdf_tab [$a] ['ZWISCHENSUMME_A'];
					
					// $pdf_tab[$a]['ENDSUMME'] = $pdf_tab[$a]['ZWISCHENSUMME'] + $summe_rep;
					// $pdf_tab[$a]['ENDSUMME_A'] = '<b>'.nummer_punkt2komma_t($pdf_tab[$a]['ZWISCHENSUMME'] + $summe_rep).'</b>';
					
					// $pdf_tab[$a]['EIG_AUSZAHLUNG'] = $this->get_kosten_arr('EINHEIT', $my_arr[$a]['EINHEIT_ID'], $monat, $jahr, $gk->geldkonto_id,80001);
					
					$e_nam = $pdf_tab [$a] ['EIGENTUEMER_NAMEN'];
					$ein_nam = $pdf_tab [$a] ['EINHEIT_KURZNAME'];
					
					/* �bersichtstabelle */
					$uebersicht [$a] ['EINHEIT_KURZNAME'] = $ein_nam;
					$uebersicht [$a] ['EIGENTUEMER_NAMEN'] = $e_nam;
					$uebersicht [$a] ['MIETER'] = $pdf_tab [$a] ['MIETER'];
					$uebersicht [$a] ['MIETER_SALDO'] = $pdf_tab [$a] ['MIETER_SALDO'];
					$uebersicht [$a] ['MIETER_SALDO_A'] = nummer_punkt2komma ( $pdf_tab [$a] ['MIETER_SALDO'] );
					$uebersicht [$a] ['NETTO_SOLL_G_A'] = $pdf_tab [$a] ['NETTO_SOLL_G_A'];
					$uebersicht [$a] ['NETTO_SOLL_A'] = nummer_punkt2komma ( $pdf_tab [$a] ['NETTO_SOLL_MV'] );
					$uebersicht [$a] ['NETTO_SOLL_DIFF_A'] = nummer_punkt2komma ( $pdf_tab [$a] ['NETTO_SOLL_DIFF'] );
					$uebersicht [$a] ['ABGABE_HV_A'] = $pdf_tab [$a] ['ABGABE_HV_A'];
					$uebersicht [$a] ['ABGABE_IHR'] = $pdf_tab [$a] ['ABGABE_IHR_A'];
					
					$uebersicht [$a] ['SUMME_REP'] = $pdf_tab [$a] ['SUMME_REP'];
					$uebersicht [$a] ['SUMME_REP_A'] = $pdf_tab [$a] ['SUMME_REP_A'];
					
					$uebersicht [$a] ['WEG-FLAECHE_A'] = $pdf_tab [$a] ['WEG-FLAECHE_A'];
					$uebersicht [$a] ['EINHEIT_QM_A'] = $pdf_tab [$a] ['EINHEIT_QM_A'];
					
					$uebersicht [$a] ['ENDSUMME_A'] = $pdf_tab [$a] ['ENDSUMME_A'];
					// $summe_alle_eigentuemer += $pdf_tab[$a]['ENDSUMME'];
					$uebersicht [$a] ['TRANSFER'] = $pdf_tab [$a] ['ENDSUMME'] + $pdf_tab [$a] ['SUMME_REP'];
					$uebersicht [$a] ['TRANSFER_A'] = nummer_punkt2komma_t ( $pdf_tab [$a] ['ENDSUMME'] + $pdf_tab [$a] ['SUMME_REP'] );
					/*
					 * $trans_tab['ENDSUMME_A'] = $pdf_tab[$a]['ENDSUMME_A'];
					 * $trans_tab['SUMME_REP_A'] = $uebersicht[$a]['SUMME_REP_A'];
					 * $trans_tab['TRANSFER'] = $uebersicht[$a]['TRANSFER'];
					 * $trans_tab['TRANSFER_A'] = $uebersicht[$a]['TRANSFER_A'];
					 */
					
					$summe_transfer += $uebersicht [$a] ['TRANSFER'];
					
					if ($pdf_tab [$a] ['ENDSUMME'] > 0) {
						$summe_alle_eigentuemer += $pdf_tab [$a] ['ENDSUMME'];
					} else {
						$summe_nachzahler += $pdf_tab [$a] ['ENDSUMME'];
					}
					
					// print_r($uebersicht);
					// die();
					// $uebersicht[$a]['ZWISCHEN']
					// $pdf_tab[$a+1]['ENDSUMME_A'] = ' ';
					// $pdf_tab[$a+2]['ENDSUMME_A'] = ' ';
					// array_unshift($pdf_tab, array('EINHEIT_KURZNAME' => ' '));
					// array_unshift($pdf_tab, array('EINHEIT_KURZNAME' => ' '));
					// print_r($pdf_tab);
					// die();
					
					$pdf->ezSetDy ( - 25 ); // abstand
					if ($lang == 'en') {
						
						$cols = array (
								'EIGENTUEMER_NAMEN' => "<b>owner</b>",
								'EINHEIT_KURZNAME' => "<b>apart.No</b>",
								'MIETER' => "<b>tenant</b>",
								'WEG-FLAECHE_A' => "<b>size [m�]</b>",
								'NETTO_SOLL_A' => "<b>net rent [�]</b>",
								'ABGABE_IHR_A' => "<b>for maint. [�]</b>",
								'ABGABE_HV_A' => "<b>mng. fee [�]</b>",
								'ENDSUMME_A' => "<b>Amount [�]</b>" 
						);
						$pdf->ezTable ( $pdf_tab, $cols, "<b>Monthly report $monat/$jahr    $ein_nam</b>", array (
								'showHeadings' => 1,
								'shaded' => 1,
								'titleFontSize' => 8,
								'fontSize' => 7,
								'xPos' => 50,
								'xOrientation' => 'right',
								'width' => 500,
								'cols' => array (
										'ENDSUMME_A' => array (
												'justification' => 'right',
												'width' => 50 
										),
										'EIGENTUEMER_NAMEN' => array (
												'justification' => 'left',
												'width' => 50 
										) 
								) 
						) );
					} else {
						$cols = array (
								'EIGENTUEMER_NAMEN' => "<b>Eigent�mer</b>",
								'EINHEIT_KURZNAME' => "<b>apart.No</b>",
								'MIETER' => "<b>tenant</b>",
								'WEG-FLAECHE_A' => "<b>size [m�]</b>",
								'NETTO_SOLL_A' => "<b>net rent [�]</b>",
								'ABGABE_IHR_A' => "<b>for maint. [�]</b>",
								'ABGABE_HV_A' => "<b>mng. fee [�]</b>",
								'ENDSUMME_A' => "<b>Amount [�]</b>" 
						);
						$pdf->ezTable ( $pdf_tab, $cols, "<b>$monat_name $jahr - Gesamt�bersicht - $ein_nam</b>", array (
								'showHeadings' => 1,
								'shaded' => 1,
								'titleFontSize' => 8,
								'fontSize' => 7,
								'xPos' => 30,
								'xOrientation' => 'right',
								'width' => 550,
								'cols' => array (
										'ENDSUMME_A' => array (
												'justification' => 'right',
												'width' => 50 
										),
										'EIGENTUEMER_NAMEN' => array (
												'justification' => 'left',
												'width' => 50 
										) 
								) 
						) );
					}
					
					/*
					 * if($pdf_tab[$a]['BRUTTO_IST']<$pdf_tab[$a]['ENDSUMME']){
					 * $pdf->setColor(1.0,0.0,0.0);
					 * $pdf->ezSetDy(-20); //abstand
					 * if($lang=='en'){
					 * $pdf->ezText("no payout possible!", 12);
					 * }else{
					 * $pdf->ezText("Keine Auszahlung m�glich!", 12);
					 * }
					 * }
					 */
					
					// print_r($table_arr);
					// die();
					
					$pdf->ezSetDy ( - 20 ); // abstand
					if (is_array ( $my_arr [$a] ['AUSGABEN'] )) {
						// $anzaa = count($my_arr[$a]['AUSGABEN']);
						array_unshift ( $my_arr [$a] ['AUSGABEN'], array (
								'DATUM' => ' ' 
						) );
						array_unshift ( $my_arr [$a] ['AUSGABEN'], array (
								'DATUM' => ' ' 
						) );
						
						// $my_arr[$a]['AUSGABEN'][$anzaa] = ' ';
						// $my_arr[$a]['AUSGABEN'][$anzaa+1] = ' ';
						
						if ($lang == 'en') {
							$cols = array (
									'DATUM' => "<b>Date</b>",
									'VERWENDUNGSZWECK' => "<b>Description</b>",
									'BETRAG' => "<b>Amount [�]</b>" 
							);
							$pdf->ezTable ( $my_arr [$a] ['AUSGABEN'], $cols, "<b>Maintenance bills | cost account: [1023]</b>", array (
									'showHeadings' => 1,
									'shaded' => 1,
									'titleFontSize' => 8,
									'fontSize' => 7,
									'xPos' => 50,
									'xOrientation' => 'right',
									'width' => 500,
									'cols' => array (
											'BETRAG' => array (
													'justification' => 'right',
													'width' => 65 
											),
											'DATUM' => array (
													'justification' => 'left',
													'width' => 50 
											) 
									) 
							) );
						} else {
							$cols = array (
									'DATUM' => "Datum",
									'VERWENDUNGSZWECK' => "Buchungstext",
									'BETRAG' => "Betrag" 
							);
							$pdf->ezTable ( $my_arr [$a] ['AUSGABEN'], $cols, "<b>$monat_name $jahr - Reparaturen 1023 - $ein_nam</b>", array (
									'showHeadings' => 1,
									'shaded' => 1,
									'titleFontSize' => 8,
									'fontSize' => 7,
									'xPos' => 50,
									'xOrientation' => 'right',
									'width' => 500,
									'cols' => array (
											'BETRAG' => array (
													'justification' => 'right',
													'width' => 65 
											),
											'DATUM' => array (
													'justification' => 'left',
													'width' => 50 
											) 
									) 
							) );
						}
					} else {
						$pdf->ezText ( "Keine Reparaturen", 12 );
					}
					$pdf->ezSetDy ( - 20 ); // abstand
					                    // $cols = array('ENDSUMME_A'=>"Amount1", 'SUMME_REP_A'=>"Amount", 'TRANSFER_A'=>"Transfer");
					                    // $pdf->ezTable($trans_tab, $cols);
					                    // unset($trans_tab);
					$trans_tab [0] ['TEXT'] = "Amount [�]";
					$trans_tab [0] ['AM'] = $uebersicht [$a] ['ENDSUMME_A'];
					$trans_tab [1] ['TEXT'] = "Bills [�]";
					$trans_tab [1] ['AM'] = $uebersicht [$a] ['SUMME_REP_A'];
					$trans_tab [2] ['TEXT'] = "<b>Transfer [�]</b>";
					if ($uebersicht [$a] ['TRANSFER'] > 0) {
						$trans_tab [2] ['TEXT'] = "<b>Transfer [�]</b>";
						$trans_tab [2] ['AM'] = "<b>" . $uebersicht [$a] ['TRANSFER_A'] . "</b>";
					} else {
						$trans_tab [2] ['TEXT'] = "<b>Summary [�]</b>";
						$trans_tab [2] ['AM'] = "<b>" . $uebersicht [$a] ['TRANSFER_A'] . "</b>";
						$trans_tab [3] ['TEXT'] = "<b>Transfer [�]</b>";
						$trans_tab [3] ['AM'] = "<b>0,00</b>";
					}
					
					$cols = array (
							'TEXT' => "",
							'AM' => "" 
					);
					$pdf->ezTable ( $trans_tab, $cols, "<b>Summary $monat/$jahr</b>", array (
							'showHeadings' => 0,
							'shaded' => 1,
							'titleFontSize' => 8,
							'fontSize' => 7,
							'xPos' => 235,
							'xOrientation' => 'right',
							'width' => 500,
							'cols' => array (
									'TEXT' => array (
											'justification' => 'right',
											'width' => 250 
									),
									'AM' => array (
											'justification' => 'right',
											'width' => 65 
									) 
							) 
					) );
					unset ( $trans_tab );
					
					/* TAbelle Auszahlung an Eigent�mer */
					// print_r($my_arr[$a]['AUSZAHLUNG_ET']);
					// die();
					if (is_array ( $my_arr [$a] ['AUSZAHLUNG_ET'] )) {
						
						if ($lang == 'en') {
							// $cols = array('DATUM'=>"Date", 'VERWENDUNGSZWECK'=>"Description", 'BETRAG'=>"Amount");
							// $pdf->ezTable($my_arr[$a]['AUSZAHLUNG_ET'], $cols, "<b>$monat_name $jahr - transfer 5020 - $ein_nam</b>", array('showHeadings'=>1,'shaded'=>1, 'titleFontSize' => 8, 'fontSize' => 7, 'xPos'=>50,'xOrientation'=>'right', 'width'=>500, 'cols'=>array('BETRAG'=>array('justification'=>'right', 'width'=>65),'DATUM'=>array('justification'=>'left', 'width'=>50))));
						} else {
							// $cols = array('DATUM'=>"Datum", 'VERWENDUNGSZWECK'=>"Buchungstext", 'BETRAG'=>"Betrag");
							// $pdf->ezTable($my_arr[$a]['AUSZAHLUNG_ET'], $cols, "<b>$monat_name $jahr - �berweisung 80001 - $ein_nam</b>", array('showHeadings'=>1,'shaded'=>1, 'titleFontSize' => 8, 'fontSize' => 7, 'xPos'=>50,'xOrientation'=>'right', 'width'=>500, 'cols'=>array('BETRAG'=>array('justification'=>'right', 'width'=>65),'DATUM'=>array('justification'=>'left', 'width'=>50))));
						}
					}
					/*
					 * if(is_array($my_arr[$a]['IST_EINNAHMEN'])){
					 * if($lang=='en'){
					 * $cols = array('DATUM'=>"Date", 'VERWENDUNGSZWECK'=>"Description", 'BETRAG'=>"Amount");
					 * $pdf->ezTable($my_arr[$a]['IST_EINNAHMEN'], $cols, "<b>$monat_name $jahr - income overview 80001 - $ein_nam</b>", array('showHeadings'=>1,'shaded'=>1, 'titleFontSize' => 8, 'fontSize' => 7, 'xPos'=>50,'xOrientation'=>'right', 'width'=>500, 'cols'=>array('BETRAG'=>array('justification'=>'right', 'width'=>65),'DATUM'=>array('justification'=>'left', 'width'=>50))));
					 * }else{
					 * $cols = array('DATUM'=>"Datum", 'VERWENDUNGSZWECK'=>"Buchungstext", 'BETRAG'=>"Betrag");
					 * $pdf->ezTable($my_arr[$a]['IST_EINNAHMEN'], $cols, "<b>$monat_name $jahr - Einnahmen 80001 - $ein_nam</b>", array('showHeadings'=>1,'shaded'=>1, 'titleFontSize' => 8, 'fontSize' => 7, 'xPos'=>50,'xOrientation'=>'right', 'width'=>500, 'cols'=>array('BETRAG'=>array('justification'=>'right', 'width'=>65),'DATUM'=>array('justification'=>'left', 'width'=>50))));
					 * }
					 *
					 * }else{
					 * $pdf->ezText("Keine Mieteinnahmen", 12);
					 * }
					 */
					
					// $cols = array('DATUM'=>"Datum",'VERWENDUNGSZWECK'=>"Buchungstext", 'BETRAG'=>"Betrag");
					// $pdf->ezTable($pdf_tab[$a]['EIG_AUSZAHLUNG'], $cols, "<b>$monat_name $jahr - Auszahlung an Eigent�mer 80001 - $ein_nam</b>", array('showHeadings'=>1,'shaded'=>1, 'titleFontSize' => 8, 'fontSize' => 7, 'xPos'=>50,'xOrientation'=>'right', 'width'=>500, 'cols'=>array('BETRAG'=>array('justification'=>'right', 'width'=>65),'DATUM'=>array('justification'=>'left', 'width'=>50))));
					// $pdf->Cezpdf('a4','landscape');
					$pdf->ezNewPage ();
					
					/*
					 * $size = array(0,0,595.28,841.89);
					 * $a_k=$size[3];
					 * $size[3]=$size[2];
					 * $size[2]=$a_k;
					 *
					 *
					 * $pdf->ez['pageWidth']=$size[2];
					 * $pdf->ez['pageHeight']=$size[3];
					 * #$pdf->ezSetMargins(120,40,30,30);
					 */
					unset ( $pdf_tab );
				}
			}
			$uebersicht [$anz] ['EIGENTUEMER_NAMEN'] = 'Soll';
			$uebersicht [$anz] ['ENDSUMME_A'] = nummer_punkt2komma_t ( $summe_alle_eigentuemer );
			$uebersicht [$anz + 1] ['EIGENTUEMER_NAMEN'] = 'Auszahlen';
			$uebersicht [$anz + 1] ['TRANSFER_A'] = nummer_punkt2komma_t ( $summe_transfer );
			// $uebersicht[$anz+1]['EIGENTUEMER_NAMEN'] = 'Zu erhalten';
			// $uebersicht[$anz+1]['ENDSUMME_A'] = nummer_punkt2komma_t($summe_nachzahler);
			
			if ($lang == 'en') {
				$cols = array (
						'EINHEIT_KURZNAME' => "Apt",
						'EINHEIT_QM_A' => 'MVm�',
						'WEG-FLAECHE_A' => 'm�',
						'EIGENTUEMER_NAMEN' => "Own",
						'MIETER' => "Tenant",
						'MIETER_SALDO_A' => 'current',
						'NETTO_SOLL_G_A' => "Garanty",
						'NETTO_SOLL_A' => "net rent",
						'NETTO_SOLL_DIFF_A' => "diff",
						'ABGABE_HV_A' => "fee",
						'ABGABE_IHR' => "for maint.",
						'ENDSUMME_A' => "Amount",
						'SUMME_REP_A' => 'Rep.',
						'TRANSFER_A' => 'transfer' 
				);
			}
			
			$pdf->ezTable ( $uebersicht, $cols, null, array (
					'showHeadings' => 1,
					'shaded' => 1,
					'titleFontSize' => 8,
					'fontSize' => 7,
					'xPos' => 50,
					'xOrientation' => 'right',
					'width' => 500 
			) );
			// print_r($pdf_tab);
			// print_r($pdf_tab_soll);
			// echo '<pre>';
			// print_r($my_arr);
			
			// die();
			
			ob_clean (); // ausgabepuffer leeren
			header ( "Content-type: application/pdf" ); // wird von MSIE ignoriert
			$pdf->ezStream ();
		} else {
			die ( "Keine Einheiten im Objekt $objekt_id" );
		}
	}
	function inspiration_pdf_kurz_6($ausgezogene = 0, $objekt_id, $monat, $jahr, $lang = 'de') {
		/* Eingrenzung Kostenabragen */
		if (! isset ( $_REQUEST ['von'] ) or ! isset ( $_REQUEST ['bis'] )) {
			die ( 'Abfragedatum VON BIS in die URL hinzuf�gen' );
		}
		$von = date_german2mysql ( $_REQUEST ['von'] );
		$bis = date_german2mysql ( $_REQUEST ['bis'] );
		
		$monat_name = monat2name ( $monat );
		$pdf = new Cezpdf ( 'a4', 'portrait' );
		$bpdf = new b_pdf ();
		$bpdf->b_header ( $pdf, 'Partner', $_SESSION ['partner_id'], 'portrait', 'pdfclass/fonts/Helvetica.afm', 6 );
		$pdf->ezStopPageNumbers (); // seitennummerirung beenden
		$gk = new geldkonto_info ();
		$gk->geld_konto_ermitteln ( 'OBJEKT', $objekt_id );
		echo '<pre>';
		// print_r($gk);
		if (! $gk->geldkonto_id) {
			die ( 'Geldkonto zum Objekt hinzuf�gen!!!' );
		}
		$db_abfrage = "SELECT OBJEKT_KURZNAME, HAUS_STRASSE, HAUS_NUMMER, `EINHEIT_KURZNAME` , `EINHEIT_ID`,  ltrim(rtrim(EINHEIT_LAGE)) AS EINHEIT_LAGE, `EINHEIT_QM` FROM EINHEIT , HAUS, OBJEKT
WHERE `EINHEIT_AKTUELL` = '1' && EINHEIT.HAUS_ID = HAUS.HAUS_ID && HAUS.OBJEKT_ID=OBJEKT.OBJEKT_ID && HAUS_AKTUELL='1' && OBJEKT_AKTUELL='1' && OBJEKT.OBJEKT_ID='$objekt_id' 
GROUP BY EINHEIT_ID ORDER BY EINHEIT_KURZNAME";
		
		$result = mysql_query ( $db_abfrage );
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			$z = 0;
			while ( $row = mysql_fetch_assoc ( $result ) ) {
				$my_arr [] = $row;
				$einheit_id = $row ['EINHEIT_ID'];
				$einheit_qm = $row ['EINHEIT_QM'];
				$e = new einheit ();
				$e->get_einheit_info ( $einheit_id );
				$my_arr [$z] ['ANSCHRIFT'] = "$e->haus_strasse $e->haus_nummer, $e->haus_plz $e->haus_stadt";
				$det = new detail ();
				$my_arr [$z] ['WEG-FLAECHE_A'] = $det->finde_detail_inhalt ( 'EINHEIT', $einheit_id, 'WEG-Fl�che' ); // kommt als Kommazahl
				if (empty ( $my_arr [$z] ['WEG-FLAECHE_A'] )) {
					$my_arr [$z] ['WEG-FLAECHE_A'] = nummer_punkt2komma ( $einheit_qm );
				}
				$my_arr [$z] ['WEG-FLAECHE'] = nummer_komma2punkt ( $my_arr [$z] ['WEG-FLAECHE_A'] );
				
				$my_arr [$z] ['WG_NR'] = $det->finde_detail_inhalt ( 'EINHEIT', $einheit_id, 'Alte Nr' ); // kommt als Kommazahl
				
				$weg = new weg ();
				$weg->get_last_eigentuemer ( $einheit_id );
				if (isset ( $weg->eigentuemer_name )) {
					// echo '<pre>';
					// print_r($weg);
					// $my_arr[$z]['EIGENTUEMER'] = $weg->eigentuemer_name;
					$weg->get_eigentuemer_namen ( $weg->eigentuemer_id );
					$my_arr [$z] ['EIGENTUEMER_NAMEN'] = $weg->eigentuemer_name_str_u;
					$my_arr [$z] ['EIGENTUEMER_ID'] = $weg->eigentuemer_id;
					
					/* Personenkontaktdaten Eigent�mer */
					$et_p_id = $weg->get_person_id_eigentuemer_arr ( $weg->eigentuemer_id );
					if (is_array ( $et_p_id )) {
						$anz_pp = count ( $et_p_id );
						for($pe = 0; $pe < $anz_pp; $pe ++) {
							$et_p_id_1 = $et_p_id [$pe] ['PERSON_ID'];
							// echo $et_p_id_1;
							$detail = new detail ();
							if (($detail->finde_detail_inhalt ( 'PERSON', $et_p_id_1, 'Email' ))) {
								$email_arr = $detail->finde_alle_details_grup ( 'PERSON', $et_p_id_1, 'Email' );
								for($ema = 0; $ema < count ( $email_arr ); $ema ++) {
									$em_adr = $email_arr [$ema] ['DETAIL_INHALT'];
									$my_arr [$z] ['EMAILS'] [] = $em_adr;
								}
								// $my_arr[$z]['EMAILS'][] = $detail->finde_detail_inhalt('PERSON', $et_p_id_1, 'Email');
							}
						}
					} else {
						die ( "Personen/Eigent�mer unbekannt! ET_ID: $weg->eigentuemer_id" );
					}
				} else {
					$my_arr [$z] ['EIGENTUEMER'] = 'Unbekannt';
				}
				$mv_id = $e->get_mietvertrag_id ( $einheit_id );
				if ($mv_id) {
					$mvs = new mietvertraege ();
					$mvs->get_mietvertrag_infos_aktuell ( $mv_id );
					$kontaktdaten = $e->kontaktdaten_mieter ( $mv_id );
					// $my_arr[$z]['MIETER'] = $mvs->personen_name_string_u."\n".$kontaktdaten;
					$my_arr [$z] ['MIETER'] = $mvs->personen_name_string_u;
					$my_arr [$z] ['MIETVERTRAG_ID'] = $mv_id;
					$my_arr [$z] ['KONTAKT'] = $kontaktdaten;
					$my_arr [$z] ['EINHEIT_ID'] = $einheit_id;
					$mz = new miete ();
					$mz->mietkonto_berechnung ( $mv_id );
					// print_r($mz);
					// die();
					$my_arr [$z] ['MIETER_SALDO'] = $mz->erg;
					$my_arr [$z] ['MIETER_SALDO_VM'] = nummer_punkt2komma ( $mz->saldo_vormonat_end );
				} else {
					$my_arr [$z] ['MIETER'] = 'Leerstand';
				}
				$z ++;
			}
			// print_r($weg);
			
			unset ( $e );
			unset ( $mvs );
			unset ( $weg );
			
			$anz = count ( $my_arr );
			/* Berechnung Abgaben */
			for($a = 0; $a < $anz; $a ++) {
				$einheit_id = $my_arr [$a] ['EINHEIT_ID'];
				if (isset ( $my_arr [$a] ['EIGENTUEMER_ID'] )) {
					$eige_id = $my_arr [$a] ['EIGENTUEMER_ID'];
					// echo $my_arr[$a]['EIGENTUEMER_ID'];
					// die();
					$weg1 = new weg ();
					$ihr_hg = $weg1->get_summe_kostenkat_monat ( $monat, $jahr, 'Einheit', $einheit_id, '6030' );
					// $my_arr[$a]['ABGABEN'][]['ABGABE_IHR'] = $my_arr[$a]['WEG-FLAECHE'] * -0.4;
					if ($ihr_hg) {
						$my_arr [$a] ['ABGABEN'] [] ['ABGABE_IHR'] = - ihr_hg;
					} else {
						// if(empty($my_arr[$a]['WEG-FLAECHE'])){
						$my_arr [$a] ['ABGABEN'] [] ['ABGABE_IHR'] = $einheit_qm * - 0.4;
					}
					$weg1 = new weg ();
					$vg = $weg1->get_summe_kostenkat_monat ( $monat, $jahr, 'Einheit', $einheit_id, '6060' );
					if ($vg) {
						// die("SSSS $vg");
						$my_arr [$a] ['ABGABEN'] [] ['VG'] = $vg; // Verwaltergeb�hr
					} else {
						$my_arr [$a] ['ABGABEN'] [] ['VG'] = '30.00'; // Verwaltergeb�hr
					}
					// $my_arr[$a]['ABGABEN'][]['VG'] = '30.00'; //Verwaltergeb�hr
					// $my_arr[$a]['ABGABEN'][]['VG'] = $vg; //Verwaltergeb�hr
					
					/* Kosten 1023 Reparatur Einheit */
					// $my_arr[$a]['AUSGABEN'] = $this->get_kosten_arr('EINHEIT', $my_arr[$a]['EINHEIT_ID'], $monat, $jahr, $gk->geldkonto_id,1023);
					$my_arr [$a] ['AUSGABEN'] = $this->get_kosten_von_bis ( 'Einheit', $einheit_id, $von, $bis, $gk->geldkonto_id, 1023 );
					$anz_rep = count ( $my_arr [$a] ['AUSGABEN'] );
					$summe_rep = 0;
					for($b = 0; $b < $anz_rep - 1; $b ++) {
						$summe_rep += $my_arr [$a] ['AUSGABEN'] [$b] ['BETRAG'];
					}
					// $my_arr[$a]['AUSGABEN'][$anz_rep]['BETRAG'] = '<b>'.nummer_punkt2komma_t($summe_rep).'</b>';
					// $my_arr[$a]['AUSGABEN'][$anz_rep]['VERWENDUNGSZWECK'] = '<b>Summe</b>';
					
					// echo "'EINHEIT', $my_arr[$a]['EINHEIT_ID'], $monat, $jahr, $gk->geldkonto_id<br>";
					// print_r($arr);
					$mk = new mietkonto ();
					$mk->kaltmiete_monatlich ( $my_arr [$a] ['MIETVERTRAG_ID'], $monat, $jahr );
					$brutto_sollmiete_arr = explode ( '|', $mk->summe_forderung_monatlich ( $my_arr [$a] ['MIETVERTRAG_ID'], $monat, $jahr ) );
					$brutto_sollmiete = $brutto_sollmiete_arr [0];
					$my_arr [$a] ['NETTO_SOLL_MV'] = $mk->ausgangs_kaltmiete;
					
					/* Garantierte Miete abfragen */
					$net_ren_garantie_a = $det->finde_detail_inhalt ( 'EINHEIT', $einheit_id, 'WEG-KaltmieteINS' ); // kommt als Kommazahl
					$net_ren_garantie = nummer_komma2punkt ( $net_ren_garantie_a );
					$my_arr [$a] ['NETTO_SOLL_G_A'] = $net_ren_garantie_a;
					// if($einheit_id=='945'){
					// die("SANEL $einheit_id $net_ren_garantie");
					// }
					if ($net_ren_garantie > $mk->ausgangs_kaltmiete) {
						$my_arr [$a] ['NETTO_SOLL'] = $net_ren_garantie;
					} else {
						$my_arr [$a] ['NETTO_SOLL'] = $mk->ausgangs_kaltmiete;
					}
					
					$my_arr [$a] ['BRUTTO_SOLL'] = $brutto_sollmiete;
					$my_arr [$a] ['AUSZAHLUNG_ET'] = $this->get_kosten_arr ( 'Eigentuemer', $eige_id, $monat, $jahr, $gk->geldkonto_id, 5020 );
					
					/* Andere Kosten */
					/* INS MAKLERGEB�HR */
					$my_arr [$a] ['5500'] = $this->get_kosten_von_bis ( 'Einheit', $einheit_id, $von, $bis, $gk->geldkonto_id, 5500 );
					/* Andere Kosten */
					$my_arr [$a] ['4180'] = $this->get_kosten_von_bis ( 'Einheit', $einheit_id, $von, $bis, $gk->geldkonto_id, 4180 );
					$my_arr [$a] ['4280'] = $this->get_kosten_von_bis ( 'Einheit', $einheit_id, $von, $bis, $gk->geldkonto_id, 4280 );
					// $my_arr[$a]['4280'] = $this->get_kosten_arr('Einheit', $einheit_id, $monat, $jahr, $gk->geldkonto_id,4280);
					$my_arr [$a] ['4281'] = $this->get_kosten_von_bis ( 'Einheit', $einheit_id, $von, $bis, $gk->geldkonto_id, 4281 );
					$my_arr [$a] ['4282'] = $this->get_kosten_von_bis ( 'Einheit', $einheit_id, $von, $bis, $gk->geldkonto_id, 4282 );
					$my_arr [$a] ['5081'] = $this->get_kosten_von_bis ( 'Eigentuemer', $eige_id, $von, $bis, $gk->geldkonto_id, 5081 );
					$my_arr [$a] ['5010'] = $this->get_kosten_von_bis ( 'Eigentuemer', $eige_id, $von, $bis, $gk->geldkonto_id, 5010 );
					
					if (isset ( $_REQUEST ['von_a'] )) {
						$von_a = date_german2mysql ( $_REQUEST ['von_a'] );
					} else {
						$von_a = "$jahr-$monat-01";
					}
					if (! isset ( $_REQUEST ['bis_a'] )) {
						$lt = letzter_tag_im_monat ( $monat, $jahr );
						$bis_a = "$jahr-$monat-$lt";
						// die("$von_a $bis_a");
					} else {
						$bis_a = date_german2mysql ( $_REQUEST ['bis_a'] );
					}
					// die("$von_a $bis_a");
					$my_arr [$a] ['5020'] = $this->get_kosten_von_bis ( 'Eigentuemer', $eige_id, $von_a, $bis_a, $gk->geldkonto_id, 5020 );
					
					// print_r($my_arr[$a]['5020']);
					// die();
					$my_arr [$a] ['IST_EINNAHMEN'] = $this->get_kosten_arr ( 'MIETVERTRAG', $my_arr [$a] ['MIETVERTRAG_ID'], $monat, $jahr, $gk->geldkonto_id, 80001 );
					$anz_me = count ( $my_arr [$a] ['IST_EINNAHMEN'] );
					$summe_einnahmen = 0;
					for($b = 0; $b < $anz_me; $b ++) {
						$summe_einnahmen += $my_arr [$a] ['IST_EINNAHMEN'] [$b] ['BETRAG'];
					}
					$my_arr [$a] ['IST_EINNAHMEN'] [$anz_me] ['BETRAG'] = '<b>' . nummer_punkt2komma_t ( $summe_einnahmen ) . '</b>';
					$my_arr [$a] ['IST_EINNAHMEN'] [$anz_me] ['VERWENDUNGSZWECK'] = '<b>Summe</b>';
					$my_arr [$a] ['BRUTTO_IST'] = $summe_einnahmen;
					// $my_arr[$a]['SUM_EIN_AUS_MIETE'] =
					
					$pdf_tab [$a] ['MIETER_SALDO'] = $my_arr [$a] ['MIETER_SALDO'];
					$pdf_tab [$a] ['MIETER_SALDO_VM'] = $my_arr [$a] ['MIETER_SALDO_VM'];
					$pdf_tab [$a] ['EIGENTUEMER_NAMEN'] = $my_arr [$a] ['EIGENTUEMER_NAMEN'];
					$pdf_tab [$a] ['EINHEIT_KURZNAME'] = $my_arr [$a] ['EINHEIT_KURZNAME'];
					$pdf_tab [$a] ['EINHEIT_ID'] = $my_arr [$a] ['EINHEIT_ID'];
					$pdf_tab [$a] ['ANSCHRIFT'] = $my_arr [$a] ['ANSCHRIFT'];
					$pdf_tab [$a] ['EIGENTUEMER_ID'] = $my_arr [$a] ['EIGENTUEMER_ID'];
					$pdf_tab [$a] ['EMAILS'] = array_unique ( $my_arr [$a] ['EMAILS'] );
					unset ( $emails );
					
					$pdf_tab [$a] ['EINHEIT_QM'] = $my_arr [$a] ['EINHEIT_QM'];
					$pdf_tab [$a] ['EINHEIT_QM_A'] = nummer_punkt2komma ( $my_arr [$a] ['EINHEIT_QM'] );
					$pdf_tab [$a] ['WEG-FLAECHE'] = $my_arr [$a] ['WEG-FLAECHE'];
					$pdf_tab [$a] ['WEG-FLAECHE_A'] = $my_arr [$a] ['WEG-FLAECHE_A'];
					$pdf_tab [$a] ['AUSZAHLUNG_ET'] = $my_arr [$a] ['AUSZAHLUNG_ET'];
					$pdf_tab [$a] ['MIETER'] = $my_arr [$a] ['MIETER'];
					/* Garantiemiete */
					$pdf_tab [$a] ['NETTO_SOLL_G_A'] = $my_arr [$a] ['NETTO_SOLL_G_A'];
					
					$pdf_tab [$a] ['BRUTTO_SOLL'] = $my_arr [$a] ['BRUTTO_SOLL'];
					$pdf_tab [$a] ['BRUTTO_SOLL_A'] = nummer_punkt2komma_t ( $my_arr [$a] ['BRUTTO_SOLL'] );
					$pdf_tab [$a] ['BRUTTO_IST'] = $my_arr [$a] ['BRUTTO_IST'];
					$pdf_tab [$a] ['BRUTTO_IST_A'] = nummer_punkt2komma_t ( $my_arr [$a] ['BRUTTO_IST'] );
					$pdf_tab [$a] ['DIFF'] = $my_arr [$a] ['BRUTTO_IST'] - $pdf_tab [$a] ['BRUTTO_SOLL'];
					$pdf_tab [$a] ['DIFF_A'] = nummer_punkt2komma_t ( $my_arr [$a] ['BRUTTO_IST'] - $pdf_tab [$a] ['BRUTTO_SOLL'] );
					
					$pdf_tab [$a] ['NETTO_SOLL'] = $my_arr [$a] ['NETTO_SOLL'];
					$pdf_tab [$a] ['NETTO_SOLL_MV'] = $my_arr [$a] ['NETTO_SOLL_MV'];
					$pdf_tab [$a] ['NETTO_SOLL_DIFF'] = $pdf_tab [$a] ['NETTO_SOLL'] - $pdf_tab [$a] ['NETTO_SOLL_MV'];
					$pdf_tab [$a] ['NETTO_SOLL_A'] = nummer_punkt2komma_t ( $my_arr [$a] ['NETTO_SOLL'] );
					// echo '<pre>';
					// print_r($my_arr);
					// die();
					// if(empty($my_arr[$a]['WEG-FLAECHE'])){
					// $pdf_tab[$a]['ABGABE_IHR'] = $pdf_tab[$a]['EINHEIT_QM'] * -0.4;
					// die('SSS');
					// }else{
					// $pdf_tab[$a]['ABGABE_IHR'] = $my_arr[$a]['WEG-FLAECHE'] * -0.4;
					$pdf_tab [$a] ['ABGABE_IHR'] = $my_arr [$a] ['ABGABE_IHR'];
					// die('OKOKOK');
					// }
					
					$weg1 = new weg ();
					$vg = $weg1->get_summe_kostenkat_monat ( $monat, $jahr, 'Einheit', $einheit_id, '6060' );
					if (! empty ( $vg )) {
						// die("SSSS $vg");
						$pdf_tab [$a] ['ABGABE_HV'] = - $vg; // Verwaltergeb�hr
						$pdf_tab [$a] ['ABGABE_HV_A'] = nummer_punkt2komma ( - $vg ); // Verwaltergeb�hr
					} else {
						$pdf_tab [$a] ['ABGABE_HV'] = '-30.00';
						$pdf_tab [$a] ['ABGABE_HV_A'] = '-30,00';
					}
					
					$weg1 = new weg ();
					$ihr_hg = $weg1->get_summe_kostenkat_monat ( $monat, $jahr, 'Einheit', $einheit_id, '6030' );
					
					if (! empty ( $ihr_hg )) {
						// die('SIVAC');
						$pdf_tab [$a] ['ABGABE_IHR'] = - $ihr_hg;
					} else {
						// echo '<pre>';
						// print_r($my_arr);
						// die("BLA");
						$pdf_tab [$a] ['ABGABE_IHR'] = $pdf_tab [$a] ['WEG-FLAECHE'] * - 0.4;
					}
					// print_r($pdf_tab);
					// die();
					
					$pdf_tab [$a] ['ABGABE_IHR_A'] = nummer_punkt2komma ( $pdf_tab [$a] ['ABGABE_IHR'] );
					
					// $pdf_tab[$a]['ABGABE_HV'] = '-30.00';
					// $pdf_tab[$a]['ABGABE_HV_A'] = '-30,00';
					$pdf_tab [$a] ['ZWISCHENSUMME'] = $my_arr [$a] ['NETTO_SOLL'] + $pdf_tab [$a] ['ABGABE_IHR'] + $pdf_tab [$a] ['ABGABE_HV'];
					
					$pdf_tab [$a] ['ZWISCHENSUMME_A'] = nummer_punkt2komma ( $my_arr [$a] ['NETTO_SOLL'] + $pdf_tab [$a] ['ABGABE_IHR'] + $pdf_tab [$a] ['ABGABE_HV'] );
					// if(nummer_komma2punkt($pdf_tab[$a]['ZWISCHENSUMME_A'])<0.00){
					// $pdf_tab[$a]['ZWISCHENSUMME_A'] ='0,00';
					// }
					
					/* Andere Kosten Summieren */
					// ############################
					$anz_kk = count ( $my_arr [$a] ['5500'] );
					if ($anz_kk > 1) {
						$sum_k = $my_arr [$a] ['5500'] [$anz_kk - 1] ['BETRAG'];
						// die($sum_k);
						$summe_rep += $my_arr [$a] ['5500'] [$anz_kk - 1] ['BETRAG'];
					}
					
					$anz_kk = count ( $my_arr [$a] ['4180'] );
					if ($anz_kk > 1) {
						$sum_k = $my_arr [$a] ['4180'] [$anz_kk - 1] ['BETRAG'];
						// die($sum_k);
						$summe_rep += $my_arr [$a] ['4180'] [$anz_kk - 1] ['BETRAG'];
					}
					
					$anz_kk = count ( $my_arr [$a] ['4280'] );
					if ($anz_kk > 1) {
						$sum_k = $my_arr [$a] ['4280'] [$anz_kk - 1] ['BETRAG'];
						// die($sum_k);
						$summe_rep += $my_arr [$a] ['4280'] [$anz_kk - 1] ['BETRAG'];
					}
					
					$anz_kk = count ( $my_arr [$a] ['4281'] );
					if ($anz_kk > 1) {
						$sum_k = $my_arr [$a] ['4281'] [$anz_kk - 1] ['BETRAG'];
						// die($sum_k);
						$summe_rep += $my_arr [$a] ['4281'] [$anz_kk - 1] ['BETRAG'];
					}
					$anz_kk = count ( $my_arr [$a] ['4282'] );
					if ($anz_kk > 1) {
						$sum_k = $my_arr [$a] ['4282'] [$anz_kk - 1] ['BETRAG'];
						// die($sum_k);
						$summe_rep += $my_arr [$a] ['4282'] [$anz_kk - 1] ['BETRAG'];
					}
					
					$anz_kk = count ( $my_arr [$a] ['5081'] );
					if ($anz_kk > 1) {
						$sum_k = $my_arr [$a] ['5081'] [$anz_kk - 1] ['BETRAG'];
						// die($sum_k);
						$summe_rep += $my_arr [$a] ['5081'] [$anz_kk - 1] ['BETRAG'];
					}
					
					// $anz_au = count($my_arr[$a]['5020']);
					// if($anz_au>1){
					// $sum_k = $my_arr[$a]['5020'][$anz_au-1]['BETRAG'];
					// #die($sum_k);
					// $summe_rep += $my_arr[$a]['5020'][$anz_au-1]['BETRAG'];
					// }
					
					$pdf_tab [$a] ['SUMME_REP'] = $summe_rep;
					$pdf_tab [$a] ['SUMME_REP_A'] = nummer_punkt2komma ( $summe_rep );
					$pdf_tab [$a] ['ENDSUMME'] = $pdf_tab [$a] ['ZWISCHENSUMME'];
					$pdf_tab [$a] ['ENDSUMME_A'] = $pdf_tab [$a] ['ZWISCHENSUMME_A'];
					
					// $pdf_tab[$a]['ENDSUMME'] = $pdf_tab[$a]['ZWISCHENSUMME'] + $summe_rep;
					// $pdf_tab[$a]['ENDSUMME_A'] = '<b>'.nummer_punkt2komma_t($pdf_tab[$a]['ZWISCHENSUMME'] + $summe_rep).'</b>';
					
					// $pdf_tab[$a]['EIG_AUSZAHLUNG'] = $this->get_kosten_arr('EINHEIT', $my_arr[$a]['EINHEIT_ID'], $monat, $jahr, $gk->geldkonto_id,80001);
					
					$e_nam = $pdf_tab [$a] ['EIGENTUEMER_NAMEN'];
					$ein_nam = $pdf_tab [$a] ['EINHEIT_KURZNAME'];
					
					/* �bersichtstabelle */
					$uebersicht [$a] ['EINHEIT_KURZNAME'] = $ein_nam;
					$uebersicht [$a] ['EIGENTUEMER_NAMEN'] = $e_nam;
					$uebersicht [$a] ['MIETER'] = $pdf_tab [$a] ['MIETER'];
					$uebersicht [$a] ['MIETER_SALDO'] = $pdf_tab [$a] ['MIETER_SALDO'];
					$uebersicht [$a] ['MIETER_SALDO_VM'] = $pdf_tab [$a] ['MIETER_SALDO_VM'];
					$uebersicht [$a] ['MIETER_SALDO_A'] = nummer_punkt2komma ( $pdf_tab [$a] ['MIETER_SALDO'] );
					$uebersicht [$a] ['NETTO_SOLL_G_A'] = $pdf_tab [$a] ['NETTO_SOLL_G_A'];
					$uebersicht [$a] ['NETTO_SOLL_A'] = nummer_punkt2komma ( $pdf_tab [$a] ['NETTO_SOLL_MV'] );
					$uebersicht [$a] ['NETTO_SOLL_DIFF_A'] = nummer_punkt2komma ( $pdf_tab [$a] ['NETTO_SOLL_DIFF'] );
					$uebersicht [$a] ['ABGABE_HV_A'] = $pdf_tab [$a] ['ABGABE_HV_A'];
					$uebersicht [$a] ['ABGABE_IHR'] = $pdf_tab [$a] ['ABGABE_IHR_A'];
					
					$uebersicht [$a] ['SUMME_REP'] = $pdf_tab [$a] ['SUMME_REP'];
					$uebersicht [$a] ['SUMME_REP_A'] = $pdf_tab [$a] ['SUMME_REP_A'];
					
					$uebersicht [$a] ['WEG-FLAECHE_A'] = $pdf_tab [$a] ['WEG-FLAECHE_A'];
					$uebersicht [$a] ['EINHEIT_QM_A'] = $pdf_tab [$a] ['EINHEIT_QM_A'];
					
					$uebersicht [$a] ['ENDSUMME_A'] = $pdf_tab [$a] ['ENDSUMME_A'];
					// $summe_alle_eigentuemer += $pdf_tab[$a]['ENDSUMME'];
					$uebersicht [$a] ['TRANSFER'] = $pdf_tab [$a] ['ENDSUMME'] + $pdf_tab [$a] ['SUMME_REP'];
					$uebersicht [$a] ['TRANSFER_A'] = nummer_punkt2komma_t ( $pdf_tab [$a] ['ENDSUMME'] + $pdf_tab [$a] ['SUMME_REP'] );
					/*
					 * $trans_tab['ENDSUMME_A'] = $pdf_tab[$a]['ENDSUMME_A'];
					 * $trans_tab['SUMME_REP_A'] = $uebersicht[$a]['SUMME_REP_A'];
					 * $trans_tab['TRANSFER'] = $uebersicht[$a]['TRANSFER'];
					 * $trans_tab['TRANSFER_A'] = $uebersicht[$a]['TRANSFER_A'];
					 */
					
					$summe_transfer += $uebersicht [$a] ['TRANSFER'];
					
					if ($pdf_tab [$a] ['ENDSUMME'] > 0) {
						$summe_alle_eigentuemer += $pdf_tab [$a] ['ENDSUMME'];
					} else {
						$summe_nachzahler += $pdf_tab [$a] ['ENDSUMME'];
					}
					
					// print_r($uebersicht);
					// die();
					// $uebersicht[$a]['ZWISCHEN']
					// $pdf_tab[$a+1]['ENDSUMME_A'] = ' ';
					// $pdf_tab[$a+2]['ENDSUMME_A'] = ' ';
					// array_unshift($pdf_tab, array('EINHEIT_KURZNAME' => ' '));
					// array_unshift($pdf_tab, array('EINHEIT_KURZNAME' => ' '));
					// print_r($pdf_tab[$a]);
					// die();
					
					// $pdf->ezSetDy(-25); //abstand
					
					if (isset ( $_REQUEST ['w_monat'] )) {
						$w_monat = $_REQUEST ['w_monat'];
					} else {
						$w_monat = $monat;
					}
					
					if (isset ( $_REQUEST ['w_jahr'] )) {
						$w_jahr = $_REQUEST ['w_jahr'];
					} else {
						$w_jahr = $jahr;
					}
					
					if ($lang == 'en') {
						print_r ( $pdf_tab );
						// die();
						if (is_array ( $pdf_tab [$a] ['EMAILS'] )) {
							
							$anzemail = count ( $pdf_tab [$a] ['EMAILS'] );
							$pdf->setColor ( 255, 255, 255, 255 ); // Weiss
							for($em = 0; $em < $anzemail; $em ++) {
								$akt_seite = $pdf->ezOutput ();
								// print_r($pdf);
								// die();
								$email = $pdf_tab [$a] ['EMAILS'] [$em];
								$pdf->ezText ( "$email ", 10 );
								$pdf->ezSetDy ( 9 ); // abstand
							}
							$pdf->setColor ( 0, 0, 0, 1 ); // schwarz
						}
						$anschrift = $pdf_tab [$a] ['ANSCHRIFT'];
						// $pdf->ezText($pdf_tab[$a]['ANSCHRIFT'], 11);
						$cols = array (
								'EIGENTUEMER_NAMEN' => "<b>owner</b>",
								'EINHEIT_KURZNAME' => "<b>apart.No</b>",
								'MIETER' => "<b>tenant</b>",
								'WEG-FLAECHE_A' => "<b>size [m�]</b>",
								'NETTO_SOLL_A' => "<b>net rent [�]</b>",
								'ABGABE_IHR_A' => "<b>for maint. [�]</b>",
								'ABGABE_HV_A' => "<b>mng. fee [�]</b>",
								'ENDSUMME_A' => "<b>Amount [�]</b>" 
						);
						$pdf->ezTable ( $pdf_tab, $cols, "<b>Monthly report $w_monat/$w_jahr    $ein_nam, $anschrift</b>", array (
								'showHeadings' => 1,
								'shaded' => 1,
								'titleFontSize' => 8,
								'fontSize' => 7,
								'xPos' => 50,
								'xOrientation' => 'right',
								'width' => 500,
								'cols' => array (
										'ENDSUMME_A' => array (
												'justification' => 'right',
												'width' => 50 
										),
										'EIGENTUEMER_NAMEN' => array (
												'justification' => 'left',
												'width' => 50 
										) 
								) 
						) );
					} else {
						// $pdf->ezText($pdf_tab[$a]['ANSCHRIFT'], 11);
						$anschrift = $pdf_tab [$a] ['ANSCHRIFT'];
						$cols = array (
								'EIGENTUEMER_NAMEN' => "<b>Eigent�mer</b>",
								'EINHEIT_KURZNAME' => "<b>apart.No</b>",
								'MIETER' => "<b>tenant</b>",
								'WEG-FLAECHE_A' => "<b>size [m�]</b>",
								'NETTO_SOLL_A' => "<b>net rent [�]</b>",
								'ABGABE_IHR_A' => "<b>for maint. [�]</b>",
								'ABGABE_HV_A' => "<b>mng. fee [�]</b>",
								'ENDSUMME_A' => "<b>Amount [�]</b>" 
						);
						$pdf->ezTable ( $pdf_tab, $cols, "<b>$monat_name $jahr - Gesamt�bersicht - $ein_nam, $anschrift</b>", array (
								'showHeadings' => 1,
								'shaded' => 1,
								'titleFontSize' => 8,
								'fontSize' => 7,
								'xPos' => 30,
								'xOrientation' => 'right',
								'width' => 550,
								'cols' => array (
										'ENDSUMME_A' => array (
												'justification' => 'right',
												'width' => 50 
										),
										'EIGENTUEMER_NAMEN' => array (
												'justification' => 'left',
												'width' => 50 
										) 
								) 
						) );
					}
					
					/*
					 * if($pdf_tab[$a]['BRUTTO_IST']<$pdf_tab[$a]['ENDSUMME']){
					 * $pdf->setColor(1.0,0.0,0.0);
					 * $pdf->ezSetDy(-20); //abstand
					 * if($lang=='en'){
					 * $pdf->ezText("no payout possible!", 12);
					 * }else{
					 * $pdf->ezText("Keine Auszahlung m�glich!", 12);
					 * }
					 * }
					 */
					
					// print_r($table_arr);
					// die();
					
					$pdf->ezSetDy ( - 10 ); // abstand
					if (is_array ( $my_arr [$a] ['AUSGABEN'] )) {
						// $anzaa = count($my_arr[$a]['AUSGABEN']);
						array_unshift ( $my_arr [$a] ['AUSGABEN'], array (
								'DATUM' => ' ' 
						) );
						array_unshift ( $my_arr [$a] ['AUSGABEN'], array (
								'DATUM' => ' ' 
						) );
						
						// $my_arr[$a]['AUSGABEN'][$anzaa] = ' ';
						// $my_arr[$a]['AUSGABEN'][$anzaa+1] = ' ';
						
						if ($lang == 'en') {
							$cols = array (
									'DATUM' => "<b>Date</b>",
									'VERWENDUNGSZWECK' => "<b>Description</b>",
									'BETRAG' => "<b>Amount [�]</b>" 
							);
							$pdf->ezTable ( $my_arr [$a] ['AUSGABEN'], $cols, "<b>Maintenance bills | cost account: [1023]</b>", array (
									'showHeadings' => 1,
									'shaded' => 1,
									'titleFontSize' => 8,
									'fontSize' => 7,
									'xPos' => 50,
									'xOrientation' => 'right',
									'width' => 500,
									'cols' => array (
											'BETRAG' => array (
													'justification' => 'right',
													'width' => 65 
											),
											'DATUM' => array (
													'justification' => 'left',
													'width' => 50 
											) 
									) 
							) );
							
							if (is_array ( $my_arr [$a] ['5500'] ) && count ( $my_arr [$a] ['5500'] ) > 1) {
								$pdf->ezSetDy ( - 10 ); // abstand
								$pdf->ezTable ( $my_arr [$a] ['5500'], $cols, "<b>broker fee | cost account: [5500]</b>", array (
										'showHeadings' => 1,
										'shaded' => 1,
										'titleFontSize' => 8,
										'fontSize' => 7,
										'xPos' => 50,
										'xOrientation' => 'right',
										'width' => 500,
										'cols' => array (
												'BETRAG' => array (
														'justification' => 'right',
														'width' => 65 
												),
												'DATUM' => array (
														'justification' => 'left',
														'width' => 50 
												) 
										) 
								) );
							}
							
							if (is_array ( $my_arr [$a] ['4180'] ) && count ( $my_arr [$a] ['4180'] ) > 1) {
								$pdf->ezSetDy ( - 10 ); // abstand
								$pdf->ezTable ( $my_arr [$a] ['4180'], $cols, "<b>allowed rent increase | cost account: [4180]</b>", array (
										'showHeadings' => 1,
										'shaded' => 1,
										'titleFontSize' => 8,
										'fontSize' => 7,
										'xPos' => 50,
										'xOrientation' => 'right',
										'width' => 500,
										'cols' => array (
												'BETRAG' => array (
														'justification' => 'right',
														'width' => 65 
												),
												'DATUM' => array (
														'justification' => 'left',
														'width' => 50 
												) 
										) 
								) );
							}
							if (is_array ( $my_arr [$a] ['4280'] ) && count ( $my_arr [$a] ['4280'] ) > 1) {
								$pdf->ezSetDy ( - 10 ); // abstand
								$pdf->ezTable ( $my_arr [$a] ['4280'], $cols, "<b>court fees | cost account: [4280]</b>", array (
										'showHeadings' => 1,
										'shaded' => 1,
										'titleFontSize' => 8,
										'fontSize' => 7,
										'xPos' => 50,
										'xOrientation' => 'right',
										'width' => 500,
										'cols' => array (
												'BETRAG' => array (
														'justification' => 'right',
														'width' => 65 
												),
												'DATUM' => array (
														'justification' => 'left',
														'width' => 50 
												) 
										) 
								) );
							}
							if (is_array ( $my_arr [$a] ['4281'] ) && count ( $my_arr [$a] ['4281'] ) > 1) {
								$pdf->ezSetDy ( - 10 ); // abstand
								$pdf->ezTable ( $my_arr [$a] ['4281'], $cols, "<b>payment for lawyer | cost account: [4281]</b>", array (
										'showHeadings' => 1,
										'shaded' => 1,
										'titleFontSize' => 8,
										'fontSize' => 7,
										'xPos' => 50,
										'xOrientation' => 'right',
										'width' => 500,
										'cols' => array (
												'BETRAG' => array (
														'justification' => 'right',
														'width' => 65 
												),
												'DATUM' => array (
														'justification' => 'left',
														'width' => 50 
												) 
										) 
								) );
							}
							if (is_array ( $my_arr [$a] ['4282'] ) && count ( $my_arr [$a] ['4282'] ) > 1) {
								// print_r($my_arr[$a]['4180']);
								$pdf->ezSetDy ( - 10 ); // abstand
								$pdf->ezTable ( $my_arr [$a] ['4282'], $cols, "<b>payment for marshal | cost account: [4282]</b>", array (
										'showHeadings' => 1,
										'shaded' => 1,
										'titleFontSize' => 8,
										'fontSize' => 7,
										'xPos' => 50,
										'xOrientation' => 'right',
										'width' => 500,
										'cols' => array (
												'BETRAG' => array (
														'justification' => 'right',
														'width' => 65 
												),
												'DATUM' => array (
														'justification' => 'left',
														'width' => 50 
												) 
										) 
								) );
							}
							
							if (is_array ( $my_arr [$a] ['5081'] ) && count ( $my_arr [$a] ['5081'] ) > 1) {
								// print_r($my_arr[$a]['4180']);
								$pdf->ezSetDy ( - 10 ); // abstand
								$pdf->ezTable ( $my_arr [$a] ['5081'], $cols, "<b>credit repayment | cost account: [5081]</b>", array (
										'showHeadings' => 1,
										'shaded' => 1,
										'titleFontSize' => 8,
										'fontSize' => 7,
										'xPos' => 50,
										'xOrientation' => 'right',
										'width' => 500,
										'cols' => array (
												'BETRAG' => array (
														'justification' => 'right',
														'width' => 65 
												),
												'DATUM' => array (
														'justification' => 'left',
														'width' => 50 
												) 
										) 
								) );
							}
							
							/*
							 * if(is_array($my_arr[$a]['5010']) && count($my_arr[$a]['5010'])>1){
							 * #print_r($my_arr[$a]['4180']);
							 * $pdf->ezSetDy(-10); //abstand
							 * $pdf->ezTable($my_arr[$a]['5010'], $cols, "<b>Actual transfer income | cost account: [5010]</b>", array('showHeadings'=>1,'shaded'=>1, 'titleFontSize' => 8, 'fontSize' => 7, 'xPos'=>50,'xOrientation'=>'right', 'width'=>500, 'cols'=>array('BETRAG'=>array('justification'=>'right', 'width'=>65), 'DATUM'=>array('justification'=>'left', 'width'=>50))));
							 * }
							 */
							
							// ie("TEST");
						} else {
							$cols = array (
									'DATUM' => "Datum",
									'VERWENDUNGSZWECK' => "Buchungstext",
									'BETRAG' => "Betrag" 
							);
							$pdf->ezTable ( $my_arr [$a] ['AUSGABEN'], $cols, "<b>$monat_name $jahr - Reparaturen 1023 - $ein_nam</b>", array (
									'showHeadings' => 1,
									'shaded' => 1,
									'titleFontSize' => 8,
									'fontSize' => 7,
									'xPos' => 50,
									'xOrientation' => 'right',
									'width' => 500,
									'cols' => array (
											'BETRAG' => array (
													'justification' => 'right',
													'width' => 65 
											),
											'DATUM' => array (
													'justification' => 'left',
													'width' => 50 
											) 
									) 
							) );
						}
					} else {
						$pdf->ezText ( "Keine Reparaturen", 12 );
					}
					$pdf->ezSetDy ( - 20 ); // abstand
					                    // $cols = array('ENDSUMME_A'=>"Amount1", 'SUMME_REP_A'=>"Amount", 'TRANSFER_A'=>"Transfer");
					                    // $pdf->ezTable($trans_tab, $cols);
					                    // unset($trans_tab);
					$trans_tab [0] ['TEXT'] = "Amount [�]";
					$trans_tab [0] ['AM'] = $uebersicht [$a] ['ENDSUMME_A'];
					$trans_tab [1] ['TEXT'] = "Bills [�]";
					$trans_tab [1] ['AM'] = $uebersicht [$a] ['SUMME_REP_A'];
					$trans_tab [2] ['TEXT'] = "<b>To transfer [�]</b>";
					if ($uebersicht [$a] ['TRANSFER'] > 0) {
						$trans_tab [2] ['TEXT'] = "<b>To transfer [�]</b>";
						$trans_tab [2] ['AM'] = "<b>" . $uebersicht [$a] ['TRANSFER_A'] . "</b>";
					} else {
						$trans_tab [2] ['TEXT'] = "<b>Summary [�]</b>";
						$trans_tab [2] ['AM'] = "<b>" . $uebersicht [$a] ['TRANSFER_A'] . "</b>";
						$trans_tab [3] ['TEXT'] = "<b>To transfer [�]</b>";
						$trans_tab [3] ['AM'] = "<b>0,00</b>";
					}
					/* Gebuchte �berweisung Kto: 5020 */
					/*
					 * $trans_tab[3]['TEXT'] = "<b>Current Transfer [�]</b>";
					 * $trans_tab[3]['AM'] = "<b>xxx</b>";
					 */
					
					/*
					 * if(is_array($my_arr[$a]['5020']) && count($my_arr[$a]['5020'])>1){
					 * $pdf->ezSetDy(-10); //abstand
					 * $pdf->ezTable($my_arr[$a]['5020'], $cols, "<b>Current Transfer | cost account: [5020]</b>", array('showHeadings'=>1,'shaded'=>1, 'titleFontSize' => 8, 'fontSize' => 7, 'xPos'=>50,'xOrientation'=>'right', 'width'=>500, 'cols'=>array('BETRAG'=>array('justification'=>'right', 'width'=>65), 'DATUM'=>array('justification'=>'left', 'width'=>50))));
					 * }
					 */
					
					$cols = array (
							'TEXT' => "",
							'AM' => "" 
					);
					$pdf->ezTable ( $trans_tab, $cols, "<b>Summary $w_monat/$jahr</b>", array (
							'showHeadings' => 0,
							'shaded' => 1,
							'titleFontSize' => 8,
							'fontSize' => 7,
							'xPos' => 235,
							'xOrientation' => 'right',
							'width' => 500,
							'cols' => array (
									'TEXT' => array (
											'justification' => 'right',
											'width' => 250 
									),
									'AM' => array (
											'justification' => 'right',
											'width' => 65 
									) 
							) 
					) );
					$cols = array (
							'DATUM' => "<b>Date</b>",
							'VERWENDUNGSZWECK' => "<b>Description</b>",
							'BETRAG' => "<b>Amount [�]</b>" 
					);
					
					// $pdf->setColor(1.0,0.0,0.0);
					// $pdf->ezText("SANEL");
					
					$pdf->options [] = array (
							'textCol' => array (
									1,
									0,
									0 
							) 
					);
					if (is_array ( $my_arr [$a] ['5010'] ) && count ( $my_arr [$a] ['5010'] ) > 1) {
						$anz_aus = count ( $my_arr [$a] ['5010'] );
						
						for($aaa = 0; $aaa < $anz_aus; $aaa ++) {
							if ($aaa == $anz_aus - 1) {
								$bbbb = $my_arr [$a] ['5010'] [$aaa] ['BETRAG'];
								$my_arr [$a] ['5010'] [$aaa] ['BETRAG'] = "<b>$bbbb</b>";
							} else {
								$my_arr [$a] ['5010'] [$aaa] ['BETRAG'] = $my_arr [$a] ['5010'] [$aaa] ['BETRAG'];
							}
						}
						
						$pdf->ezSetDy ( - 10 ); // abstand
						                    // $pdf->options['titleCol'] =array(1,0,0);
						$pdf->ezTable ( $my_arr [$a] ['5010'], $cols, "<b>Actual transfer income | cost account: [5010]</b>", array (
								'showHeadings' => 1,
								'shaded' => 1,
								'titleFontSize' => 8,
								'fontSize' => 7,
								'xPos' => 50,
								'xOrientation' => 'right',
								'width' => 500,
								'cols' => array (
										'BETRAG' => array (
												'justification' => 'right',
												'width' => 65 
										),
										'DATUM' => array (
												'justification' => 'left',
												'width' => 50 
										) 
								) 
						) );
						// $pdf->setColor(0.0,0.0,0.0);
					}
					
					$pdf->ezSetDy ( - 10 ); // abstand
					
					if (is_array ( $my_arr [$a] ['5020'] ) && count ( $my_arr [$a] ['5020'] ) > 1) {
						$anz_aus = count ( $my_arr [$a] ['5020'] );
						
						for($aaa = 0; $aaa < $anz_aus; $aaa ++) {
							if ($aaa == $anz_aus - 1) {
								$bbbb = $my_arr [$a] ['5020'] [$aaa] ['BETRAG'] * - 1; // POSITIVIEREN
								$my_arr [$a] ['5020'] [$aaa] ['BETRAG'] = "<b>$bbbb</b>";
							} else {
								$my_arr [$a] ['5020'] [$aaa] ['BETRAG'] = $my_arr [$a] ['5020'] [$aaa] ['BETRAG'] * - 1; // POSITIVIEREN
							}
						}
					} else {
						$my_arr [$a] ['5020'] [0] ['BETRAG'] = "<b>0.00</b>";
						$my_arr [$a] ['5020'] [0] ['VERWENDUNGSZWECK'] = "<b>No transfer</b>";
					}
					
					$pdf->ezTable ( $my_arr [$a] ['5020'], $cols, "<b>Actual transfer | cost account: [5020]</b>", array (
							'showHeadings' => 1,
							'shaded' => 1,
							'titleFontSize' => 8,
							'fontSize' => 7,
							'xPos' => 50,
							'xOrientation' => 'right',
							'width' => 500,
							'cols' => array (
									'BETRAG' => array (
											'justification' => 'right',
											'width' => 65 
									),
									'DATUM' => array (
											'justification' => 'left',
											'width' => 50 
									) 
							) 
					) );
					
					// $pdf->ezSetDy(-10);
					// $pdf->ezText("<b>For differences between \"to transfer\" and the actually transfer please ask the customer service
					// (fon: +49 30 698 19398-12 or e-mail: service @inspirationgroup.biz)</b>", 10);
					
					unset ( $trans_tab );
					
					/* TAbelle Auszahlung an Eigent�mer */
					// print_r($my_arr[$a]['AUSZAHLUNG_ET']);
					// die();
					if (is_array ( $my_arr [$a] ['AUSZAHLUNG_ET'] )) {
						
						if ($lang == 'en') {
							// $cols = array('DATUM'=>"Date", 'VERWENDUNGSZWECK'=>"Description", 'BETRAG'=>"Amount");
							// $pdf->ezTable($my_arr[$a]['AUSZAHLUNG_ET'], $cols, "<b>$monat_name $jahr - transfer 5020 - $ein_nam</b>", array('showHeadings'=>1,'shaded'=>1, 'titleFontSize' => 8, 'fontSize' => 7, 'xPos'=>50,'xOrientation'=>'right', 'width'=>500, 'cols'=>array('BETRAG'=>array('justification'=>'right', 'width'=>65),'DATUM'=>array('justification'=>'left', 'width'=>50))));
						} else {
							// $cols = array('DATUM'=>"Datum", 'VERWENDUNGSZWECK'=>"Buchungstext", 'BETRAG'=>"Betrag");
							// $pdf->ezTable($my_arr[$a]['AUSZAHLUNG_ET'], $cols, "<b>$monat_name $jahr - �berweisung 80001 - $ein_nam</b>", array('showHeadings'=>1,'shaded'=>1, 'titleFontSize' => 8, 'fontSize' => 7, 'xPos'=>50,'xOrientation'=>'right', 'width'=>500, 'cols'=>array('BETRAG'=>array('justification'=>'right', 'width'=>65),'DATUM'=>array('justification'=>'left', 'width'=>50))));
						}
					}
					/*
					 * if(is_array($my_arr[$a]['IST_EINNAHMEN'])){
					 * if($lang=='en'){
					 * $cols = array('DATUM'=>"Date", 'VERWENDUNGSZWECK'=>"Description", 'BETRAG'=>"Amount");
					 * $pdf->ezTable($my_arr[$a]['IST_EINNAHMEN'], $cols, "<b>$monat_name $jahr - income overview 80001 - $ein_nam</b>", array('showHeadings'=>1,'shaded'=>1, 'titleFontSize' => 8, 'fontSize' => 7, 'xPos'=>50,'xOrientation'=>'right', 'width'=>500, 'cols'=>array('BETRAG'=>array('justification'=>'right', 'width'=>65),'DATUM'=>array('justification'=>'left', 'width'=>50))));
					 * }else{
					 * $cols = array('DATUM'=>"Datum", 'VERWENDUNGSZWECK'=>"Buchungstext", 'BETRAG'=>"Betrag");
					 * $pdf->ezTable($my_arr[$a]['IST_EINNAHMEN'], $cols, "<b>$monat_name $jahr - Einnahmen 80001 - $ein_nam</b>", array('showHeadings'=>1,'shaded'=>1, 'titleFontSize' => 8, 'fontSize' => 7, 'xPos'=>50,'xOrientation'=>'right', 'width'=>500, 'cols'=>array('BETRAG'=>array('justification'=>'right', 'width'=>65),'DATUM'=>array('justification'=>'left', 'width'=>50))));
					 * }
					 *
					 * }else{
					 * $pdf->ezText("Keine Mieteinnahmen", 12);
					 * }
					 */
					
					// $cols = array('DATUM'=>"Datum",'VERWENDUNGSZWECK'=>"Buchungstext", 'BETRAG'=>"Betrag");
					// $pdf->ezTable($pdf_tab[$a]['EIG_AUSZAHLUNG'], $cols, "<b>$monat_name $jahr - Auszahlung an Eigent�mer 80001 - $ein_nam</b>", array('showHeadings'=>1,'shaded'=>1, 'titleFontSize' => 8, 'fontSize' => 7, 'xPos'=>50,'xOrientation'=>'right', 'width'=>500, 'cols'=>array('BETRAG'=>array('justification'=>'right', 'width'=>65),'DATUM'=>array('justification'=>'left', 'width'=>50))));
					// $pdf->Cezpdf('a4','landscape');
					$pdf->ezNewPage ();
					
					/*
					 * $size = array(0,0,595.28,841.89);
					 * $a_k=$size[3];
					 * $size[3]=$size[2];
					 * $size[2]=$a_k;
					 *
					 *
					 * $pdf->ez['pageWidth']=$size[2];
					 * $pdf->ez['pageHeight']=$size[3];
					 * #$pdf->ezSetMargins(120,40,30,30);
					 */
					unset ( $pdf_tab );
				}
			}
			$uebersicht [$anz] ['EIGENTUEMER_NAMEN'] = 'Soll';
			$uebersicht [$anz] ['ENDSUMME_A'] = nummer_punkt2komma_t ( $summe_alle_eigentuemer );
			$uebersicht [$anz + 1] ['EIGENTUEMER_NAMEN'] = 'Auszahlen';
			$uebersicht [$anz + 1] ['TRANSFER_A'] = nummer_punkt2komma_t ( $summe_transfer );
			// $uebersicht[$anz+1]['EIGENTUEMER_NAMEN'] = 'Zu erhalten';
			// $uebersicht[$anz+1]['ENDSUMME_A'] = nummer_punkt2komma_t($summe_nachzahler);
			
			if ($lang == 'en') {
				$cols = array (
						'EINHEIT_KURZNAME' => "Apt",
						'WEG-FLAECHE_A' => 'm�',
						'EIGENTUEMER_NAMEN' => "Own",
						'MIETER' => "Tenant",
						'MIETER_SALDO_VM' => 'VM',
						'MIETER_SALDO_A' => 'current',
						'NETTO_SOLL_G_A' => "Garanty",
						'NETTO_SOLL_A' => "net rent",
						'NETTO_SOLL_DIFF_A' => "diff",
						'ABGABE_HV_A' => "fee",
						'ABGABE_IHR' => "for maint.",
						'ENDSUMME_A' => "Amount",
						'SUMME_REP_A' => 'Rep.',
						'TRANSFER_A' => 'transfer' 
				);
			} else {
				$cols = array (
						'EINHEIT_KURZNAME' => "Apt",
						'WEG-FLAECHE_A' => 'm�',
						'EIGENTUEMER_NAMEN' => "Own",
						'MIETER' => "Tenant",
						'MIETER_SALDO_A' => 'current',
						'NETTO_SOLL_G_A' => "Garanty",
						'NETTO_SOLL_A' => "net rent",
						'NETTO_SOLL_DIFF_A' => "diff",
						'ABGABE_HV_A' => "fee",
						'ABGABE_IHR' => "for maint.",
						'ENDSUMME_A' => "Amount",
						'SUMME_REP_A' => 'Rep.',
						'TRANSFER_A' => 'transfer' 
				);
			}
			$von_d = date_mysql2german ( $von );
			$bis_d = date_mysql2german ( $bis );
			$pdf->ezText ( "<b>Kostenabfrage von: $von_d bis: $bis_d</b>", 12 );
			$pdf->ezSetDy ( - 20 ); // abstand
			$pdf->ezTable ( $uebersicht, $cols, null, array (
					'showHeadings' => 1,
					'shaded' => 1,
					'titleFontSize' => 8,
					'fontSize' => 7,
					'xPos' => 10,
					'xOrientation' => 'right',
					'width' => 550,
					'cols' => array (
							'EINHEIT_KURZNAME' => array (
									'justification' => 'left',
									'width' => '40' 
							),
							'EIGENTUEMER_NAME' => array (
									'justification' => 'right',
									'width' => '40' 
							),
							'MIETER' => array (
									'justification' => 'right',
									'width' => '60' 
							) 
					) 
			) );
			// print_r($pdf_tab);
			// print_r($pdf_tab_soll);
			// echo '<pre>';
			// print_r($my_arr);
			
			// die();
			
			// print_r($pdf);
			// die();
			// $pdf->newPage()
			
			$anz_m = count ( $my_arr );
			$z = 0;
			for($mm = 0; $mm < $anz_m; $mm ++) {
				$saldo_m_et = 0;
				$einheit_kn = $my_arr [$mm] ['EINHEIT_KURZNAME'];
				
				// $et_ue_tab[$einheit_kn][$z]['TXT'] = 'EINHEIT';
				// #$et_ue_tab[$einheit_kn][$z]['BEZ'] = $my_arr[$mm]['EINHEIT_KURZNAME'];
				// $et_ue_tab[$einheit_kn][$z]['BET'] = ' ';
				// $et_ue_tab[$einheit_kn][$z]['DATUM'] = ' ';
				// $z++;
				
				/* Soll Miete */
				/*
				 * $et_ue_tab[$einheit_kn][$z]['TXT'] = 'Monthly';
				 * $et_ue_tab[$einheit_kn][$z]['BEZ'] = 'Rent (B)';
				 * $et_ue_tab[$einheit_kn][$z]['BET'] = $my_arr[$mm]['BRUTTO_SOLL'];
				 * $et_ue_tab[$einheit_kn][$z]['DATUM'] = 'Monthly';
				 * $saldo_m_et += $et_ue_tab[$einheit_kn][$z]['BET'];
				 * $z++;
				 *
				 * /*Abgabe IHR / Hausgeld etc
				 */
				/*
				 * $et_ue_tab[$einheit_kn][$z]['TXT'] = 'Monthly';
				 * $et_ue_tab[$einheit_kn][$z]['BEZ'] = 'for maint.';
				 * $et_ue_tab[$einheit_kn][$z]['BET'] = nummer_komma2punkt(nummer_punkt2komma($my_arr[$mm]['ABGABE_IHR']));
				 * $et_ue_tab[$einheit_kn][$z]['DATUM'] = 'Monthly';
				 * $saldo_m_et += $et_ue_tab[$einheit_kn][$z]['BET'];
				 * $z++;
				 *
				 *
				 *
				 * /*Abgaben
				 */
				/*
				 * if(is_array($my_arr[$mm]['ABGABEN'])){
				 *
				 * $anz_ab = count($my_arr[$mm]['ABGABEN']);
				 * for($ab=0;$ab<$anz_ab;$ab++){
				 * $arr_key = array_keys($my_arr[$mm]['ABGABEN'][$ab]);
				 * $key = $arr_key[0];
				 * if($my_arr[$mm]['ABGABEN'][$ab][$key]<>0){
				 * $et_ue_tab[$einheit_kn][$z]['TXT']= $key;
				 * $et_ue_tab[$einheit_kn][$z]['BEZ'] = 'Man. Fee';
				 * $et_ue_tab[$einheit_kn][$z]['BET'] = $my_arr[$mm]['ABGABEN'][$ab][$key]*-1;
				 * $et_ue_tab[$einheit_kn][$z]['DATUM'] = 'Monthly';
				 * $saldo_m_et += $et_ue_tab[$einheit_kn][$z]['BET'];
				 * $z++;
				 * }
				 * }
				 * }
				 */
				
				/* Ausgaben 1023 */
				if (is_array ( $my_arr [$mm] ['AUSGABEN'] )) {
					
					$anz_ab = count ( $my_arr [$mm] ['AUSGABEN'] );
					for($ab = 0; $ab < $anz_ab; $ab ++) {
						if (isset ( $my_arr [$mm] ['AUSGABEN'] [$ab] ['GELD_KONTO_BUCHUNGEN_DAT'] )) {
							
							$konto = $my_arr [$mm] ['AUSGABEN'] [$ab] ['KONTENRAHMEN_KONTO'];
							$vzweck = $my_arr [$mm] ['AUSGABEN'] [$ab] ['VERWENDUNGSZWECK'];
							
							$et_ue_tab [$einheit_kn] [$z] ['TXT'] = "Repairs $konto";
							$et_ue_tab [$einheit_kn] [$z] ['BEZ'] = $vzweck;
							$et_ue_tab [$einheit_kn] [$z] ['BET'] = $my_arr [$mm] ['AUSGABEN'] [$ab] ['BETRAG'];
							$et_ue_tab [$einheit_kn] [$z] ['DATUM'] = $my_arr [$mm] ['AUSGABEN'] [$ab] ['DATUM'];
							$saldo_m_et += $et_ue_tab [$einheit_kn] [$z] ['BET'];
							$z ++;
						}
					}
				}
				
				/* Ausgaben 5500 */
				if (is_array ( $my_arr [$mm] ['5500'] )) {
					
					$anz_ab = count ( $my_arr [$mm] ['5500'] );
					for($ab = 0; $ab < $anz_ab; $ab ++) {
						if (isset ( $my_arr [$mm] ['5500'] [$ab] ['GELD_KONTO_BUCHUNGEN_DAT'] )) {
							
							$konto = $my_arr [$mm] ['5500'] [$ab] ['KONTENRAHMEN_KONTO'];
							$vzweck = $my_arr [$mm] ['5500'] [$ab] ['VERWENDUNGSZWECK'];
							
							$et_ue_tab [$einheit_kn] [$z] ['TXT'] = "Konto 5500";
							$et_ue_tab [$einheit_kn] [$z] ['BEZ'] = $vzweck;
							$et_ue_tab [$einheit_kn] [$z] ['BET'] = $my_arr [$mm] ['5500'] [$ab] ['BETRAG'];
							$et_ue_tab [$einheit_kn] [$z] ['DATUM'] = $my_arr [$mm] ['5500'] [$ab] ['DATUM'];
							$saldo_m_et += $et_ue_tab [$einheit_kn] [$z] ['BET'];
							$z ++;
						}
					}
				}
				
				/* Ausgaben 4180 */
				if (is_array ( $my_arr [$mm] ['4180'] )) {
					
					$anz_ab = count ( $my_arr [$mm] ['4180'] );
					for($ab = 0; $ab < $anz_ab; $ab ++) {
						if (isset ( $my_arr [$mm] ['4180'] [$ab] ['GELD_KONTO_BUCHUNGEN_DAT'] )) {
							
							$konto = $my_arr [$mm] ['4180'] [$ab] ['KONTENRAHMEN_KONTO'];
							$vzweck = $my_arr [$mm] ['4180'] [$ab] ['VERWENDUNGSZWECK'];
							
							$et_ue_tab [$einheit_kn] [$z] ['TXT'] = "Konto 4180";
							$et_ue_tab [$einheit_kn] [$z] ['BEZ'] = $vzweck;
							$et_ue_tab [$einheit_kn] [$z] ['BET'] = $my_arr [$mm] ['4180'] [$ab] ['BETRAG'];
							$et_ue_tab [$einheit_kn] [$z] ['DATUM'] = $my_arr [$mm] ['4180'] [$ab] ['DATUM'];
							$saldo_m_et += $et_ue_tab [$einheit_kn] [$z] ['BET'];
							$z ++;
						}
					}
				}
				
				/* Ausgaben 4280 */
				if (is_array ( $my_arr [$mm] ['4280'] )) {
					
					$anz_ab = count ( $my_arr [$mm] ['4280'] );
					for($ab = 0; $ab < $anz_ab; $ab ++) {
						if (isset ( $my_arr [$mm] ['4280'] [$ab] ['GELD_KONTO_BUCHUNGEN_DAT'] )) {
							
							$konto = $my_arr [$mm] ['4280'] [$ab] ['KONTENRAHMEN_KONTO'];
							$vzweck = $my_arr [$mm] ['4280'] [$ab] ['VERWENDUNGSZWECK'];
							
							$et_ue_tab [$einheit_kn] [$z] ['TXT'] = "Konto 4280";
							$et_ue_tab [$einheit_kn] [$z] ['BEZ'] = $vzweck;
							$et_ue_tab [$einheit_kn] [$z] ['BET'] = $my_arr [$mm] ['4280'] [$ab] ['BETRAG'];
							$et_ue_tab [$einheit_kn] [$z] ['DATUM'] = $my_arr [$mm] ['4280'] [$ab] ['DATUM'];
							$saldo_m_et += $et_ue_tab [$einheit_kn] [$z] ['BET'];
							$z ++;
						}
					}
				}
				
				/* Ausgaben 4281 */
				if (is_array ( $my_arr [$mm] ['4281'] )) {
					
					$anz_ab = count ( $my_arr [$mm] ['4281'] );
					for($ab = 0; $ab < $anz_ab; $ab ++) {
						if (isset ( $my_arr [$mm] ['4281'] [$ab] ['GELD_KONTO_BUCHUNGEN_DAT'] )) {
							
							$konto = $my_arr [$mm] ['4281'] [$ab] ['KONTENRAHMEN_KONTO'];
							$vzweck = $my_arr [$mm] ['4281'] [$ab] ['VERWENDUNGSZWECK'];
							
							$et_ue_tab [$einheit_kn] [$z] ['TXT'] = "Konto 4281";
							$et_ue_tab [$einheit_kn] [$z] ['BEZ'] = $vzweck;
							$et_ue_tab [$einheit_kn] [$z] ['BET'] = $my_arr [$mm] ['4281'] [$ab] ['BETRAG'];
							$et_ue_tab [$einheit_kn] [$z] ['DATUM'] = $my_arr [$mm] ['4281'] [$ab] ['DATUM'];
							$saldo_m_et += $et_ue_tab [$einheit_kn] [$z] ['BET'];
							$z ++;
						}
					}
				}
				
				/* Ausgaben 4282 */
				if (is_array ( $my_arr [$mm] ['4282'] )) {
					
					$anz_ab = count ( $my_arr [$mm] ['4282'] );
					for($ab = 0; $ab < $anz_ab; $ab ++) {
						if (isset ( $my_arr [$mm] ['4282'] [$ab] ['GELD_KONTO_BUCHUNGEN_DAT'] )) {
							
							$konto = $my_arr [$mm] ['4282'] [$ab] ['KONTENRAHMEN_KONTO'];
							$vzweck = $my_arr [$mm] ['4282'] [$ab] ['VERWENDUNGSZWECK'];
							
							$et_ue_tab [$einheit_kn] [$z] ['TXT'] = "Konto 4282";
							$et_ue_tab [$einheit_kn] [$z] ['BEZ'] = $vzweck;
							$et_ue_tab [$einheit_kn] [$z] ['BET'] = $my_arr [$mm] ['4282'] [$ab] ['BETRAG'];
							$et_ue_tab [$einheit_kn] [$z] ['DATUM'] = $my_arr [$mm] ['4282'] [$ab] ['DATUM'];
							$saldo_m_et += $et_ue_tab [$einheit_kn] [$z] ['BET'];
							$z ++;
						}
					}
				}
				
				/* Ausgaben 5081 */
				if (is_array ( $my_arr [$mm] ['5081'] )) {
					
					$anz_ab = count ( $my_arr [$mm] ['5081'] );
					for($ab = 0; $ab < $anz_ab; $ab ++) {
						if (isset ( $my_arr [$mm] ['5081'] [$ab] ['GELD_KONTO_BUCHUNGEN_DAT'] )) {
							
							$konto = $my_arr [$mm] ['5081'] [$ab] ['KONTENRAHMEN_KONTO'];
							$vzweck = $my_arr [$mm] ['5081'] [$ab] ['VERWENDUNGSZWECK'];
							
							$et_ue_tab [$einheit_kn] [$z] ['TXT'] = "Konto 5081";
							$et_ue_tab [$einheit_kn] [$z] ['BEZ'] = $vzweck;
							$et_ue_tab [$einheit_kn] [$z] ['BET'] = $my_arr [$mm] ['5081'] [$ab] ['BETRAG'];
							$et_ue_tab [$einheit_kn] [$z] ['DATUM'] = $my_arr [$mm] ['5081'] [$ab] ['DATUM'];
							$saldo_m_et += $et_ue_tab [$einheit_kn] [$z] ['BET'];
							$z ++;
						}
					}
				}
				
				/* Ausgaben 5010 */
				if (is_array ( $my_arr [$mm] ['5010'] )) {
					
					$anz_ab = count ( $my_arr [$mm] ['5010'] );
					for($ab = 0; $ab < $anz_ab; $ab ++) {
						if (isset ( $my_arr [$mm] ['5010'] [$ab] ['GELD_KONTO_BUCHUNGEN_DAT'] )) {
							
							$konto = $my_arr [$mm] ['5010'] [$ab] ['KONTENRAHMEN_KONTO'];
							$vzweck = $my_arr [$mm] ['5010'] [$ab] ['VERWENDUNGSZWECK'];
							
							$et_ue_tab [$einheit_kn] [$z] ['TXT'] = "Konto 5010";
							$et_ue_tab [$einheit_kn] [$z] ['BEZ'] = $vzweck;
							$et_ue_tab [$einheit_kn] [$z] ['BET'] = $my_arr [$mm] ['5010'] [$ab] ['BETRAG'];
							$et_ue_tab [$einheit_kn] [$z] ['DATUM'] = $my_arr [$mm] ['5010'] [$ab] ['DATUM'];
							$saldo_m_et += $et_ue_tab [$einheit_kn] [$z] ['BET'];
							$z ++;
						}
					}
				}
				
				/* Ausgaben 5020 */
				if (is_array ( $my_arr [$mm] ['5020'] )) {
					
					$anz_ab = count ( $my_arr [$mm] ['5020'] );
					for($ab = 0; $ab < $anz_ab; $ab ++) {
						if (isset ( $my_arr [$mm] ['5020'] [$ab] ['GELD_KONTO_BUCHUNGEN_DAT'] )) {
							
							$konto = $my_arr [$mm] ['5020'] [$ab] ['KONTENRAHMEN_KONTO'];
							$vzweck = $my_arr [$mm] ['5020'] [$ab] ['VERWENDUNGSZWECK'];
							
							$et_ue_tab [$einheit_kn] [$z] ['TXT'] = "Konto 5020";
							$et_ue_tab [$einheit_kn] [$z] ['BEZ'] = $vzweck;
							$et_ue_tab [$einheit_kn] [$z] ['BET'] = $my_arr [$mm] ['5020'] [$ab] ['BETRAG'] * - 1;
							$et_ue_tab [$einheit_kn] [$z] ['DATUM'] = $my_arr [$mm] ['5020'] [$ab] ['DATUM'];
							$saldo_m_et += $et_ue_tab [$einheit_kn] [$z] ['BET'];
							$z ++;
						}
					}
				}
				
				/* Saldo */
				if ($saldo_m_et != 0) {
					$et_ue_tab [$einheit_kn] [$z] ['TXT'] = 'SALDO';
					$et_ue_tab [$einheit_kn] [$z] ['BEZ'] = 'SALDO';
					$et_ue_tab [$einheit_kn] [$z] ['BET'] = nummer_komma2punkt ( nummer_punkt2komma ( $saldo_m_et ) );
				}
				
				$z = 0;
			}
			
			// echo "<hr>";
			// echo '<pre>';
			// print_r($et_ue_tab);
			// die();
			
			$w_keys = array_unique ( array_keys ( $et_ue_tab ) );
			$colss = array (
					'DATUM' => "Date",
					'TXT' => "Description",
					'BEZ' => "Description1",
					'BET' => "Amount" 
			);
			$pdf->ezNewPage ();
			for($p = 0; $p < count ( $w_keys ); $p ++) {
				$wohnung = $w_keys [$p];
				// $pdf->ezNewPage();
				// $pdf->eztable($et_ue_tab[$wohnung]);
				$pdf->ezTable ( $et_ue_tab [$wohnung], $colss, "<b>$wohnung $monat/$jahr</b>", array (
						'showHeadings' => 1,
						'shaded' => 1,
						'titleFontSize' => 8,
						'fontSize' => 7,
						'xPos' => 50,
						'xOrientation' => 'right',
						'width' => 500,
						'cols' => array (
								'DATUM' => array (
										'justification' => 'left',
										'width' => 50 
								),
								'TXT' => array (
										'justification' => 'right',
										'width' => 100 
								),
								'BEZ' => array (
										'justification' => 'left' 
								),
								'BET' => array (
										'justification' => 'right',
										'width' => 60 
								) 
						) 
				) );
				$pdf->ezSetDy ( - 10 ); // abstand
			}
			
			ob_clean (); // ausgabepuffer leeren
			header ( "Content-type: application/pdf" ); // wird von MSIE ignoriert
			$pdf->ezStream ();
		} else {
			die ( "Keine Einheiten im Objekt $objekt_id" );
		}
	}
	
	/* Mit Warmmiete */
	function inspiration_pdf_kurz_7($ausgezogene = 0, $objekt_id, $monat, $jahr, $lang = 'de') {
		/* Eingrenzung Kostenabragen */
		if (! isset ( $_REQUEST ['von'] ) or ! isset ( $_REQUEST ['bis'] )) {
			die ( 'Abfragedatum VON BIS in die URL hinzuf�gen' );
		}
		$von = date_german2mysql ( $_REQUEST ['von'] );
		$bis = date_german2mysql ( $_REQUEST ['bis'] );
		
		$monat_name = monat2name ( $monat );
		$pdf = new Cezpdf ( 'a4', 'portrait' );
		$bpdf = new b_pdf ();
		$bpdf->b_header ( $pdf, 'Partner', $_SESSION ['partner_id'], 'portrait', 'pdfclass/fonts/Helvetica.afm', 6 );
		$pdf->ezStopPageNumbers (); // seitennummerirung beenden
		$gk = new geldkonto_info ();
		$gk->geld_konto_ermitteln ( 'OBJEKT', $objekt_id );
		echo '<pre>';
		// print_r($gk);
		if (! $gk->geldkonto_id) {
			die ( 'Geldkonto zum Objekt hinzuf�gen!!!' );
		}
		$db_abfrage = "SELECT OBJEKT_KURZNAME, HAUS_STRASSE, HAUS_NUMMER, `EINHEIT_KURZNAME` , `EINHEIT_ID`,  ltrim(rtrim(EINHEIT_LAGE)) AS EINHEIT_LAGE, `EINHEIT_QM` FROM EINHEIT , HAUS, OBJEKT
	WHERE `EINHEIT_AKTUELL` = '1' && EINHEIT.HAUS_ID = HAUS.HAUS_ID && HAUS.OBJEKT_ID=OBJEKT.OBJEKT_ID && HAUS_AKTUELL='1' && OBJEKT_AKTUELL='1' && OBJEKT.OBJEKT_ID='$objekt_id'
	GROUP BY EINHEIT_ID ORDER BY EINHEIT_KURZNAME";
		
		$result = mysql_query ( $db_abfrage );
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			$z = 0;
			while ( $row = mysql_fetch_assoc ( $result ) ) {
				$my_arr [] = $row;
				$einheit_id = $row ['EINHEIT_ID'];
				$einheit_qm = $row ['EINHEIT_QM'];
				$e = new einheit ();
				$e->get_einheit_info ( $einheit_id );
				$my_arr [$z] ['ANSCHRIFT'] = "$e->haus_strasse $e->haus_nummer, $e->haus_plz $e->haus_stadt";
				$det = new detail ();
				$my_arr [$z] ['WEG-FLAECHE_A'] = $det->finde_detail_inhalt ( 'EINHEIT', $einheit_id, 'WEG-Fl�che' ); // kommt als Kommazahl
				if (empty ( $my_arr [$z] ['WEG-FLAECHE_A'] )) {
					$my_arr [$z] ['WEG-FLAECHE_A'] = nummer_punkt2komma ( $einheit_qm );
				}
				$my_arr [$z] ['WEG-FLAECHE'] = nummer_komma2punkt ( $my_arr [$z] ['WEG-FLAECHE_A'] );
				
				$my_arr [$z] ['WG_NR'] = $det->finde_detail_inhalt ( 'EINHEIT', $einheit_id, 'Alte Nr' ); // kommt als Kommazahl
				
				$weg = new weg ();
				$weg->get_last_eigentuemer ( $einheit_id );
				if (isset ( $weg->eigentuemer_name )) {
					// echo '<pre>';
					// print_r($weg);
					// $my_arr[$z]['EIGENTUEMER'] = $weg->eigentuemer_name;
					$weg->get_eigentuemer_namen ( $weg->eigentuemer_id );
					$my_arr [$z] ['EIGENTUEMER_NAMEN'] = $weg->eigentuemer_name_str_u;
					$my_arr [$z] ['EIGENTUEMER_ID'] = $weg->eigentuemer_id;
					
					/* Personenkontaktdaten Eigent�mer */
					$et_p_id = $weg->get_person_id_eigentuemer_arr ( $weg->eigentuemer_id );
					if (is_array ( $et_p_id )) {
						$anz_pp = count ( $et_p_id );
						for($pe = 0; $pe < $anz_pp; $pe ++) {
							$et_p_id_1 = $et_p_id [$pe] ['PERSON_ID'];
							// echo $et_p_id_1;
							$detail = new detail ();
							if (($detail->finde_detail_inhalt ( 'PERSON', $et_p_id_1, 'Email' ))) {
								$email_arr = $detail->finde_alle_details_grup ( 'PERSON', $et_p_id_1, 'Email' );
								for($ema = 0; $ema < count ( $email_arr ); $ema ++) {
									$em_adr = $email_arr [$ema] ['DETAIL_INHALT'];
									$my_arr [$z] ['EMAILS'] [] = $em_adr;
								}
								// $my_arr[$z]['EMAILS'][] = $detail->finde_detail_inhalt('PERSON', $et_p_id_1, 'Email');
							}
						}
					} else {
						die ( "Personen/Eigent�mer unbekannt! ET_ID: $weg->eigentuemer_id" );
					}
				} else {
					$my_arr [$z] ['EIGENTUEMER'] = 'Unbekannt';
				}
				// $mv_id = $e->get_mietvertrag_id($einheit_id);
				$mv_id = $e->get_mietvertraege_zu ( $einheit_id, $jahr, $monat );
				
				if ($mv_id) {
					$mvs = new mietvertraege ();
					$mvs->get_mietvertrag_infos_aktuell ( $mv_id );
					$kontaktdaten = $e->kontaktdaten_mieter ( $mv_id );
					// $my_arr[$z]['MIETER'] = $mvs->personen_name_string_u."\n".$kontaktdaten;
					$my_arr [$z] ['MIETER'] = $mvs->personen_name_string_u;
					$my_arr [$z] ['MIETVERTRAG_ID'] = $mv_id;
					$my_arr [$z] ['KONTAKT'] = $kontaktdaten;
					$my_arr [$z] ['EINHEIT_ID'] = $einheit_id;
					$mz = new miete ();
					$mz->mietkonto_berechnung ( $mv_id );
					// print_r($mz);
					// die();
					$my_arr [$z] ['MIETER_SALDO'] = $mz->erg;
					$my_arr [$z] ['MIETER_SALDO_VM'] = nummer_punkt2komma ( $mz->saldo_vormonat_end );
				} else {
					$my_arr [$z] ['MIETER'] = "<b>Leerstand</b>";
				}
				$z ++;
			}
			// print_r($weg);
			
			unset ( $e );
			unset ( $mvs );
			unset ( $weg );
			
			$anz = count ( $my_arr );
			/* Berechnung Abgaben */
			for($a = 0; $a < $anz; $a ++) {
				$einheit_id = $my_arr [$a] ['EINHEIT_ID'];
				if (isset ( $my_arr [$a] ['EIGENTUEMER_ID'] )) {
					$eige_id = $my_arr [$a] ['EIGENTUEMER_ID'];
					// echo $my_arr[$a]['EIGENTUEMER_ID'];
					// die();
					$weg1 = new weg ();
					$ihr_hg = $weg1->get_summe_kostenkat_monat ( $monat, $jahr, 'Einheit', $einheit_id, '6030' );
					// $my_arr[$a]['ABGABEN'][]['ABGABE_IHR'] = $my_arr[$a]['WEG-FLAECHE'] * -0.4;
					if ($ihr_hg) {
						$my_arr [$a] ['ABGABEN'] [] ['ABGABE_IHR'] = - ihr_hg;
					} else {
						// if(empty($my_arr[$a]['WEG-FLAECHE'])){
						$my_arr [$a] ['ABGABEN'] [] ['ABGABE_IHR'] = $einheit_qm * - 0.4;
					}
					$weg1 = new weg ();
					$vg = $weg1->get_summe_kostenkat_monat ( $monat, $jahr, 'Einheit', $einheit_id, '6060' );
					if ($vg) {
						// die("SSSS $vg");
						$my_arr [$a] ['ABGABEN'] [] ['VG'] = $vg; // Verwaltergeb�hr
					} else {
						$my_arr [$a] ['ABGABEN'] [] ['VG'] = '30.00'; // Verwaltergeb�hr
					}
					// $my_arr[$a]['ABGABEN'][]['VG'] = '30.00'; //Verwaltergeb�hr
					// $my_arr[$a]['ABGABEN'][]['VG'] = $vg; //Verwaltergeb�hr
					
					/* Kosten 1023 Reparatur Einheit */
					// $my_arr[$a]['AUSGABEN'] = $this->get_kosten_arr('EINHEIT', $my_arr[$a]['EINHEIT_ID'], $monat, $jahr, $gk->geldkonto_id,1023);
					$my_arr [$a] ['AUSGABEN'] = $this->get_kosten_von_bis ( 'Einheit', $einheit_id, $von, $bis, $gk->geldkonto_id, 1023 );
					$anz_rep = count ( $my_arr [$a] ['AUSGABEN'] );
					$summe_rep = 0;
					for($b = 0; $b < $anz_rep - 1; $b ++) {
						$summe_rep += $my_arr [$a] ['AUSGABEN'] [$b] ['BETRAG'];
					}
					// $my_arr[$a]['AUSGABEN'][$anz_rep]['BETRAG'] = '<b>'.nummer_punkt2komma_t($summe_rep).'</b>';
					// $my_arr[$a]['AUSGABEN'][$anz_rep]['VERWENDUNGSZWECK'] = '<b>Summe</b>';
					
					// echo "'EINHEIT', $my_arr[$a]['EINHEIT_ID'], $monat, $jahr, $gk->geldkonto_id<br>";
					// print_r($arr);
					$mk = new mietkonto ();
					$mk->kaltmiete_monatlich ( $my_arr [$a] ['MIETVERTRAG_ID'], $monat, $jahr );
					$brutto_sollmiete_arr = explode ( '|', $mk->summe_forderung_monatlich ( $my_arr [$a] ['MIETVERTRAG_ID'], $monat, $jahr ) );
					$brutto_sollmiete = $brutto_sollmiete_arr [0];
					// print_r($brutto_sollmiete_arr);
					// die();
					// #$my_arr[$a]['NETTO_SOLL_MV'] = $mk->ausgangs_kaltmiete;
					$my_arr [$a] ['NETTO_SOLL_MV'] = $brutto_sollmiete;
					
					/* Garantierte Miete abfragen */
					$net_ren_garantie_a = $det->finde_detail_inhalt ( 'EINHEIT', $einheit_id, 'WEG-KaltmieteINS' ); // kommt als Kommazahl
					$net_ren_garantie = nummer_komma2punkt ( $net_ren_garantie_a );
					$my_arr [$a] ['NETTO_SOLL_G_A'] = $net_ren_garantie_a;
					// if($einheit_id=='945'){
					// die("SANEL $einheit_id $net_ren_garantie");
					// }
					if ($net_ren_garantie > $brutto_sollmiete) {
						$my_arr [$a] ['NETTO_SOLL'] = $net_ren_garantie;
					} else {
						$my_arr [$a] ['NETTO_SOLL'] = $brutto_sollmiete;
					}
					
					$my_arr [$a] ['BRUTTO_SOLL'] = $brutto_sollmiete;
					$my_arr [$a] ['AUSZAHLUNG_ET'] = $this->get_kosten_arr ( 'Eigentuemer', $eige_id, $monat, $jahr, $gk->geldkonto_id, 5020 );
					
					/* Andere Kosten */
					/* INS MAKLERGEB�HR */
					$my_arr [$a] ['5500'] = $this->get_kosten_von_bis ( 'Einheit', $einheit_id, $von, $bis, $gk->geldkonto_id, 5500 );
					/* Andere Kosten */
					$my_arr [$a] ['4180'] = $this->get_kosten_von_bis ( 'Einheit', $einheit_id, $von, $bis, $gk->geldkonto_id, 4180 );
					$my_arr [$a] ['4280'] = $this->get_kosten_von_bis ( 'Einheit', $einheit_id, $von, $bis, $gk->geldkonto_id, 4280 );
					// $my_arr[$a]['4280'] = $this->get_kosten_arr('Einheit', $einheit_id, $monat, $jahr, $gk->geldkonto_id,4280);
					$my_arr [$a] ['4281'] = $this->get_kosten_von_bis ( 'Einheit', $einheit_id, $von, $bis, $gk->geldkonto_id, 4281 );
					$my_arr [$a] ['4282'] = $this->get_kosten_von_bis ( 'Einheit', $einheit_id, $von, $bis, $gk->geldkonto_id, 4282 );
					$my_arr [$a] ['5081'] = $this->get_kosten_von_bis ( 'Eigentuemer', $eige_id, $von, $bis, $gk->geldkonto_id, 5081 );
					$my_arr [$a] ['5010'] = $this->get_kosten_von_bis ( 'Eigentuemer', $eige_id, $von, $bis, $gk->geldkonto_id, 5010 );
					
					if (isset ( $_REQUEST ['von_a'] )) {
						$von_a = date_german2mysql ( $_REQUEST ['von_a'] );
					} else {
						$von_a = "$jahr-$monat-01";
					}
					if (! isset ( $_REQUEST ['bis_a'] )) {
						$lt = letzter_tag_im_monat ( $monat, $jahr );
						$bis_a = "$jahr-$monat-$lt";
						// die("$von_a $bis_a");
					} else {
						$bis_a = date_german2mysql ( $_REQUEST ['bis_a'] );
					}
					// die("$von_a $bis_a");
					$my_arr [$a] ['5020'] = $this->get_kosten_von_bis ( 'Eigentuemer', $eige_id, $von_a, $bis_a, $gk->geldkonto_id, 5020 );
					
					// print_r($my_arr[$a]['5020']);
					// die();
					$my_arr [$a] ['IST_EINNAHMEN'] = $this->get_kosten_arr ( 'MIETVERTRAG', $my_arr [$a] ['MIETVERTRAG_ID'], $monat, $jahr, $gk->geldkonto_id, 80001 );
					$anz_me = count ( $my_arr [$a] ['IST_EINNAHMEN'] );
					$summe_einnahmen = 0;
					for($b = 0; $b < $anz_me; $b ++) {
						$summe_einnahmen += $my_arr [$a] ['IST_EINNAHMEN'] [$b] ['BETRAG'];
					}
					$my_arr [$a] ['IST_EINNAHMEN'] [$anz_me] ['BETRAG'] = '<b>' . nummer_punkt2komma_t ( $summe_einnahmen ) . '</b>';
					$my_arr [$a] ['IST_EINNAHMEN'] [$anz_me] ['VERWENDUNGSZWECK'] = '<b>Summe</b>';
					$my_arr [$a] ['BRUTTO_IST'] = $summe_einnahmen;
					// $my_arr[$a]['SUM_EIN_AUS_MIETE'] =
					
					$pdf_tab [$a] ['MIETER_SALDO'] = $my_arr [$a] ['MIETER_SALDO'];
					$pdf_tab [$a] ['MIETER_SALDO_VM'] = $my_arr [$a] ['MIETER_SALDO_VM'];
					$pdf_tab [$a] ['EIGENTUEMER_NAMEN'] = $my_arr [$a] ['EIGENTUEMER_NAMEN'];
					$pdf_tab [$a] ['EINHEIT_KURZNAME'] = $my_arr [$a] ['EINHEIT_KURZNAME'];
					$pdf_tab [$a] ['EINHEIT_ID'] = $my_arr [$a] ['EINHEIT_ID'];
					$pdf_tab [$a] ['ANSCHRIFT'] = $my_arr [$a] ['ANSCHRIFT'];
					$pdf_tab [$a] ['EIGENTUEMER_ID'] = $my_arr [$a] ['EIGENTUEMER_ID'];
					$pdf_tab [$a] ['EMAILS'] = array_unique ( $my_arr [$a] ['EMAILS'] );
					unset ( $emails );
					
					$pdf_tab [$a] ['EINHEIT_QM'] = $my_arr [$a] ['EINHEIT_QM'];
					$pdf_tab [$a] ['EINHEIT_QM_A'] = nummer_punkt2komma ( $my_arr [$a] ['EINHEIT_QM'] );
					$pdf_tab [$a] ['WEG-FLAECHE'] = $my_arr [$a] ['WEG-FLAECHE'];
					$pdf_tab [$a] ['WEG-FLAECHE_A'] = $my_arr [$a] ['WEG-FLAECHE_A'];
					$pdf_tab [$a] ['AUSZAHLUNG_ET'] = $my_arr [$a] ['AUSZAHLUNG_ET'];
					$pdf_tab [$a] ['MIETER'] = $my_arr [$a] ['MIETER'];
					/* Garantiemiete */
					$pdf_tab [$a] ['NETTO_SOLL_G_A'] = $my_arr [$a] ['NETTO_SOLL_G_A'];
					
					$pdf_tab [$a] ['BRUTTO_SOLL'] = $my_arr [$a] ['BRUTTO_SOLL'];
					$pdf_tab [$a] ['BRUTTO_SOLL_A'] = nummer_punkt2komma_t ( $my_arr [$a] ['BRUTTO_SOLL'] );
					$pdf_tab [$a] ['BRUTTO_IST'] = $my_arr [$a] ['BRUTTO_IST'];
					$pdf_tab [$a] ['BRUTTO_IST_A'] = nummer_punkt2komma_t ( $my_arr [$a] ['BRUTTO_IST'] );
					$pdf_tab [$a] ['DIFF'] = $my_arr [$a] ['BRUTTO_IST'] - $pdf_tab [$a] ['BRUTTO_SOLL'];
					$pdf_tab [$a] ['DIFF_A'] = nummer_punkt2komma_t ( $my_arr [$a] ['BRUTTO_IST'] - $pdf_tab [$a] ['BRUTTO_SOLL'] );
					
					$pdf_tab [$a] ['NETTO_SOLL'] = $my_arr [$a] ['NETTO_SOLL'];
					$pdf_tab [$a] ['NETTO_SOLL_MV'] = $my_arr [$a] ['NETTO_SOLL_MV'];
					$pdf_tab [$a] ['NETTO_SOLL_DIFF'] = $pdf_tab [$a] ['NETTO_SOLL'] - $pdf_tab [$a] ['NETTO_SOLL_MV'];
					$pdf_tab [$a] ['NETTO_SOLL_A'] = nummer_punkt2komma_t ( $my_arr [$a] ['NETTO_SOLL'] );
					// echo '<pre>';
					// print_r($my_arr);
					// die();
					// if(empty($my_arr[$a]['WEG-FLAECHE'])){
					// $pdf_tab[$a]['ABGABE_IHR'] = $pdf_tab[$a]['EINHEIT_QM'] * -0.4;
					// die('SSS');
					// }else{
					// $pdf_tab[$a]['ABGABE_IHR'] = $my_arr[$a]['WEG-FLAECHE'] * -0.4;
					$pdf_tab [$a] ['ABGABE_IHR'] = $my_arr [$a] ['ABGABE_IHR'];
					// die('OKOKOK');
					// }
					
					$weg1 = new weg ();
					$vg = $weg1->get_summe_kostenkat_monat ( $monat, $jahr, 'Einheit', $einheit_id, '6060' );
					if (! empty ( $vg )) {
						// die("SSSS $vg");
						$pdf_tab [$a] ['ABGABE_HV'] = - $vg; // Verwaltergeb�hr
						$pdf_tab [$a] ['ABGABE_HV_A'] = nummer_punkt2komma ( - $vg ); // Verwaltergeb�hr
					} else {
						$pdf_tab [$a] ['ABGABE_HV'] = '-30.00';
						$pdf_tab [$a] ['ABGABE_HV_A'] = '-30,00';
					}
					
					$weg1 = new weg ();
					$ihr_hg = $weg1->get_summe_kostenkat_monat ( $monat, $jahr, 'Einheit', $einheit_id, '6030' );
					
					if (! empty ( $ihr_hg )) {
						// die('SIVAC');
						$pdf_tab [$a] ['ABGABE_IHR'] = - $ihr_hg;
					} else {
						// echo '<pre>';
						// print_r($my_arr);
						// die("BLA");
						$pdf_tab [$a] ['ABGABE_IHR'] = $pdf_tab [$a] ['WEG-FLAECHE'] * - 0.4;
					}
					// print_r($pdf_tab);
					// die();
					
					$pdf_tab [$a] ['ABGABE_IHR_A'] = nummer_punkt2komma ( $pdf_tab [$a] ['ABGABE_IHR'] );
					
					// $pdf_tab[$a]['ABGABE_HV'] = '-30.00';
					// $pdf_tab[$a]['ABGABE_HV_A'] = '-30,00';
					$pdf_tab [$a] ['ZWISCHENSUMME'] = $my_arr [$a] ['NETTO_SOLL'] + $pdf_tab [$a] ['ABGABE_IHR'] + $pdf_tab [$a] ['ABGABE_HV'];
					
					$pdf_tab [$a] ['ZWISCHENSUMME_A'] = nummer_punkt2komma ( $my_arr [$a] ['NETTO_SOLL'] + $pdf_tab [$a] ['ABGABE_IHR'] + $pdf_tab [$a] ['ABGABE_HV'] );
					// if(nummer_komma2punkt($pdf_tab[$a]['ZWISCHENSUMME_A'])<0.00){
					// $pdf_tab[$a]['ZWISCHENSUMME_A'] ='0,00';
					// }
					
					/* Andere Kosten Summieren */
					// ############################
					$anz_kk = count ( $my_arr [$a] ['5500'] );
					if ($anz_kk > 1) {
						$sum_k = $my_arr [$a] ['5500'] [$anz_kk - 1] ['BETRAG'];
						// die($sum_k);
						$summe_rep += $my_arr [$a] ['5500'] [$anz_kk - 1] ['BETRAG'];
					}
					
					$anz_kk = count ( $my_arr [$a] ['4180'] );
					if ($anz_kk > 1) {
						$sum_k = $my_arr [$a] ['4180'] [$anz_kk - 1] ['BETRAG'];
						// die($sum_k);
						$summe_rep += $my_arr [$a] ['4180'] [$anz_kk - 1] ['BETRAG'];
					}
					
					$anz_kk = count ( $my_arr [$a] ['4280'] );
					if ($anz_kk > 1) {
						$sum_k = $my_arr [$a] ['4280'] [$anz_kk - 1] ['BETRAG'];
						// die($sum_k);
						$summe_rep += $my_arr [$a] ['4280'] [$anz_kk - 1] ['BETRAG'];
					}
					
					$anz_kk = count ( $my_arr [$a] ['4281'] );
					if ($anz_kk > 1) {
						$sum_k = $my_arr [$a] ['4281'] [$anz_kk - 1] ['BETRAG'];
						// die($sum_k);
						$summe_rep += $my_arr [$a] ['4281'] [$anz_kk - 1] ['BETRAG'];
					}
					$anz_kk = count ( $my_arr [$a] ['4282'] );
					if ($anz_kk > 1) {
						$sum_k = $my_arr [$a] ['4282'] [$anz_kk - 1] ['BETRAG'];
						// die($sum_k);
						$summe_rep += $my_arr [$a] ['4282'] [$anz_kk - 1] ['BETRAG'];
					}
					
					$anz_kk = count ( $my_arr [$a] ['5081'] );
					if ($anz_kk > 1) {
						$sum_k = $my_arr [$a] ['5081'] [$anz_kk - 1] ['BETRAG'];
						// die($sum_k);
						$summe_rep += $my_arr [$a] ['5081'] [$anz_kk - 1] ['BETRAG'];
					}
					
					// $anz_au = count($my_arr[$a]['5020']);
					// if($anz_au>1){
					// $sum_k = $my_arr[$a]['5020'][$anz_au-1]['BETRAG'];
					// #die($sum_k);
					// $summe_rep += $my_arr[$a]['5020'][$anz_au-1]['BETRAG'];
					// }
					
					$pdf_tab [$a] ['SUMME_REP'] = $summe_rep;
					$pdf_tab [$a] ['SUMME_REP_A'] = nummer_punkt2komma ( $summe_rep );
					$pdf_tab [$a] ['ENDSUMME'] = $pdf_tab [$a] ['ZWISCHENSUMME'];
					$pdf_tab [$a] ['ENDSUMME_A'] = $pdf_tab [$a] ['ZWISCHENSUMME_A'];
					
					// $pdf_tab[$a]['ENDSUMME'] = $pdf_tab[$a]['ZWISCHENSUMME'] + $summe_rep;
					// $pdf_tab[$a]['ENDSUMME_A'] = '<b>'.nummer_punkt2komma_t($pdf_tab[$a]['ZWISCHENSUMME'] + $summe_rep).'</b>';
					
					// $pdf_tab[$a]['EIG_AUSZAHLUNG'] = $this->get_kosten_arr('EINHEIT', $my_arr[$a]['EINHEIT_ID'], $monat, $jahr, $gk->geldkonto_id,80001);
					
					$e_nam = $pdf_tab [$a] ['EIGENTUEMER_NAMEN'];
					$ein_nam = $pdf_tab [$a] ['EINHEIT_KURZNAME'];
					
					/* �bersichtstabelle */
					$uebersicht [$a] ['EINHEIT_KURZNAME'] = $ein_nam;
					$uebersicht [$a] ['EIGENTUEMER_NAMEN'] = $e_nam;
					$uebersicht [$a] ['MIETER'] = $pdf_tab [$a] ['MIETER'];
					$uebersicht [$a] ['MIETER_SALDO'] = $pdf_tab [$a] ['MIETER_SALDO'];
					$uebersicht [$a] ['MIETER_SALDO_VM'] = $pdf_tab [$a] ['MIETER_SALDO_VM'];
					$uebersicht [$a] ['MIETER_SALDO_A'] = nummer_punkt2komma ( $pdf_tab [$a] ['MIETER_SALDO'] );
					$uebersicht [$a] ['NETTO_SOLL_G_A'] = $pdf_tab [$a] ['NETTO_SOLL_G_A'];
					$uebersicht [$a] ['NETTO_SOLL_A'] = nummer_punkt2komma ( $pdf_tab [$a] ['NETTO_SOLL_MV'] );
					$uebersicht [$a] ['NETTO_SOLL_DIFF_A'] = nummer_punkt2komma ( $pdf_tab [$a] ['NETTO_SOLL_DIFF'] );
					$uebersicht [$a] ['ABGABE_HV_A'] = $pdf_tab [$a] ['ABGABE_HV_A'];
					$uebersicht [$a] ['ABGABE_IHR'] = $pdf_tab [$a] ['ABGABE_IHR_A'];
					
					$uebersicht [$a] ['SUMME_REP'] = $pdf_tab [$a] ['SUMME_REP'];
					$uebersicht [$a] ['SUMME_REP_A'] = $pdf_tab [$a] ['SUMME_REP_A'];
					
					$uebersicht [$a] ['WEG-FLAECHE_A'] = $pdf_tab [$a] ['WEG-FLAECHE_A'];
					$uebersicht [$a] ['EINHEIT_QM_A'] = $pdf_tab [$a] ['EINHEIT_QM_A'];
					
					$uebersicht [$a] ['ENDSUMME_A'] = $pdf_tab [$a] ['ENDSUMME_A'];
					// $summe_alle_eigentuemer += $pdf_tab[$a]['ENDSUMME'];
					$uebersicht [$a] ['TRANSFER'] = $pdf_tab [$a] ['ENDSUMME'] + $pdf_tab [$a] ['SUMME_REP'];
					$uebersicht [$a] ['TRANSFER_A'] = nummer_punkt2komma_t ( $pdf_tab [$a] ['ENDSUMME'] + $pdf_tab [$a] ['SUMME_REP'] );
					/*
					 * $trans_tab['ENDSUMME_A'] = $pdf_tab[$a]['ENDSUMME_A'];
					 * $trans_tab['SUMME_REP_A'] = $uebersicht[$a]['SUMME_REP_A'];
					 * $trans_tab['TRANSFER'] = $uebersicht[$a]['TRANSFER'];
					 * $trans_tab['TRANSFER_A'] = $uebersicht[$a]['TRANSFER_A'];
					 */
					
					$summe_transfer += $uebersicht [$a] ['TRANSFER'];
					
					if ($pdf_tab [$a] ['ENDSUMME'] > 0) {
						$summe_alle_eigentuemer += $pdf_tab [$a] ['ENDSUMME'];
					} else {
						$summe_nachzahler += $pdf_tab [$a] ['ENDSUMME'];
					}
					
					// print_r($uebersicht);
					// die();
					// $uebersicht[$a]['ZWISCHEN']
					// $pdf_tab[$a+1]['ENDSUMME_A'] = ' ';
					// $pdf_tab[$a+2]['ENDSUMME_A'] = ' ';
					// array_unshift($pdf_tab, array('EINHEIT_KURZNAME' => ' '));
					// array_unshift($pdf_tab, array('EINHEIT_KURZNAME' => ' '));
					// print_r($pdf_tab[$a]);
					// die();
					
					// $pdf->ezSetDy(-25); //abstand
					
					if (isset ( $_REQUEST ['w_monat'] )) {
						$w_monat = $_REQUEST ['w_monat'];
					} else {
						$w_monat = $monat;
					}
					
					if (isset ( $_REQUEST ['w_jahr'] )) {
						$w_jahr = $_REQUEST ['w_jahr'];
					} else {
						$w_jahr = $jahr;
					}
					
					if ($lang == 'en') {
						// print_r($pdf_tab);
						// die();
						if (is_array ( $pdf_tab [$a] ['EMAILS'] )) {
							
							$anzemail = count ( $pdf_tab [$a] ['EMAILS'] );
							$pdf->setColor ( 255, 255, 255, 255 ); // Weiss
							for($em = 0; $em < $anzemail; $em ++) {
								$akt_seite = $pdf->ezOutput ();
								// print_r($pdf);
								// die();
								$email = $pdf_tab [$a] ['EMAILS'] [$em];
								$pdf->ezText ( "$email ", 10 );
								$pdf->ezSetDy ( 9 ); // abstand
							}
							$pdf->setColor ( 0, 0, 0, 1 ); // schwarz
						}
						$anschrift = $pdf_tab [$a] ['ANSCHRIFT'];
						// $pdf->ezText($pdf_tab[$a]['ANSCHRIFT'], 11);
						$cols = array (
								'EIGENTUEMER_NAMEN' => "<b>owner</b>",
								'EINHEIT_KURZNAME' => "<b>apart.No</b>",
								'MIETER' => "<b>tenant</b>",
								'WEG-FLAECHE_A' => "<b>size [m�]</b>",
								'NETTO_SOLL_A' => "<b>rent [�]</b>",
								'ABGABE_IHR_A' => "<b>for maint. [�]</b>",
								'ABGABE_HV_A' => "<b>mng. fee [�]</b>",
								'ENDSUMME_A' => "<b>Amount [�]</b>" 
						);
						$pdf->ezTable ( $pdf_tab, $cols, "<b>Monthly report $w_monat/$w_jahr    $ein_nam, $anschrift</b>", array (
								'showHeadings' => 1,
								'shaded' => 1,
								'titleFontSize' => 8,
								'fontSize' => 7,
								'xPos' => 50,
								'xOrientation' => 'right',
								'width' => 500,
								'cols' => array (
										'ENDSUMME_A' => array (
												'justification' => 'right',
												'width' => 50 
										),
										'EIGENTUEMER_NAMEN' => array (
												'justification' => 'left',
												'width' => 50 
										) 
								) 
						) );
					} else {
						// $pdf->ezText($pdf_tab[$a]['ANSCHRIFT'], 11);
						$anschrift = $pdf_tab [$a] ['ANSCHRIFT'];
						$cols = array (
								'EIGENTUEMER_NAMEN' => "<b>Eigent�mer</b>",
								'EINHEIT_KURZNAME' => "<b>apart.No</b>",
								'MIETER' => "<b>tenant</b>",
								'WEG-FLAECHE_A' => "<b>size [m�]</b>",
								'NETTO_SOLL_A' => "<b>rent [�]</b>",
								'ABGABE_IHR_A' => "<b>for maint. [�]</b>",
								'ABGABE_HV_A' => "<b>mng. fee [�]</b>",
								'ENDSUMME_A' => "<b>Amount [�]</b>" 
						);
						$pdf->ezTable ( $pdf_tab, $cols, "<b>$monat_name $jahr - Gesamt�bersicht - $ein_nam, $anschrift</b>", array (
								'showHeadings' => 1,
								'shaded' => 1,
								'titleFontSize' => 8,
								'fontSize' => 7,
								'xPos' => 30,
								'xOrientation' => 'right',
								'width' => 550,
								'cols' => array (
										'ENDSUMME_A' => array (
												'justification' => 'right',
												'width' => 50 
										),
										'EIGENTUEMER_NAMEN' => array (
												'justification' => 'left',
												'width' => 50 
										) 
								) 
						) );
					}
					
					/*
					 * if($pdf_tab[$a]['BRUTTO_IST']<$pdf_tab[$a]['ENDSUMME']){
					 * $pdf->setColor(1.0,0.0,0.0);
					 * $pdf->ezSetDy(-20); //abstand
					 * if($lang=='en'){
					 * $pdf->ezText("no payout possible!", 12);
					 * }else{
					 * $pdf->ezText("Keine Auszahlung m�glich!", 12);
					 * }
					 * }
					 */
					
					// print_r($table_arr);
					// die();
					
					$pdf->ezSetDy ( - 10 ); // abstand
					if (is_array ( $my_arr [$a] ['AUSGABEN'] )) {
						// $anzaa = count($my_arr[$a]['AUSGABEN']);
						array_unshift ( $my_arr [$a] ['AUSGABEN'], array (
								'DATUM' => ' ' 
						) );
						array_unshift ( $my_arr [$a] ['AUSGABEN'], array (
								'DATUM' => ' ' 
						) );
						
						// $my_arr[$a]['AUSGABEN'][$anzaa] = ' ';
						// $my_arr[$a]['AUSGABEN'][$anzaa+1] = ' ';
						
						if ($lang == 'en') {
							$cols = array (
									'DATUM' => "<b>Date</b>",
									'VERWENDUNGSZWECK' => "<b>Description</b>",
									'BETRAG' => "<b>Amount [�]</b>" 
							);
							$pdf->ezTable ( $my_arr [$a] ['AUSGABEN'], $cols, "<b>Maintenance bills | cost account: [1023]</b>", array (
									'showHeadings' => 1,
									'shaded' => 1,
									'titleFontSize' => 8,
									'fontSize' => 7,
									'xPos' => 50,
									'xOrientation' => 'right',
									'width' => 500,
									'cols' => array (
											'BETRAG' => array (
													'justification' => 'right',
													'width' => 65 
											),
											'DATUM' => array (
													'justification' => 'left',
													'width' => 50 
											) 
									) 
							) );
							
							if (is_array ( $my_arr [$a] ['5500'] ) && count ( $my_arr [$a] ['5500'] ) > 1) {
								$pdf->ezSetDy ( - 10 ); // abstand
								$pdf->ezTable ( $my_arr [$a] ['5500'], $cols, "<b>broker fee | cost account: [5500]</b>", array (
										'showHeadings' => 1,
										'shaded' => 1,
										'titleFontSize' => 8,
										'fontSize' => 7,
										'xPos' => 50,
										'xOrientation' => 'right',
										'width' => 500,
										'cols' => array (
												'BETRAG' => array (
														'justification' => 'right',
														'width' => 65 
												),
												'DATUM' => array (
														'justification' => 'left',
														'width' => 50 
												) 
										) 
								) );
							}
							
							if (is_array ( $my_arr [$a] ['4180'] ) && count ( $my_arr [$a] ['4180'] ) > 1) {
								$pdf->ezSetDy ( - 10 ); // abstand
								$pdf->ezTable ( $my_arr [$a] ['4180'], $cols, "<b>allowed rent increase | cost account: [4180]</b>", array (
										'showHeadings' => 1,
										'shaded' => 1,
										'titleFontSize' => 8,
										'fontSize' => 7,
										'xPos' => 50,
										'xOrientation' => 'right',
										'width' => 500,
										'cols' => array (
												'BETRAG' => array (
														'justification' => 'right',
														'width' => 65 
												),
												'DATUM' => array (
														'justification' => 'left',
														'width' => 50 
												) 
										) 
								) );
							}
							if (is_array ( $my_arr [$a] ['4280'] ) && count ( $my_arr [$a] ['4280'] ) > 1) {
								$pdf->ezSetDy ( - 10 ); // abstand
								$pdf->ezTable ( $my_arr [$a] ['4280'], $cols, "<b>court fees | cost account: [4280]</b>", array (
										'showHeadings' => 1,
										'shaded' => 1,
										'titleFontSize' => 8,
										'fontSize' => 7,
										'xPos' => 50,
										'xOrientation' => 'right',
										'width' => 500,
										'cols' => array (
												'BETRAG' => array (
														'justification' => 'right',
														'width' => 65 
												),
												'DATUM' => array (
														'justification' => 'left',
														'width' => 50 
												) 
										) 
								) );
							}
							if (is_array ( $my_arr [$a] ['4281'] ) && count ( $my_arr [$a] ['4281'] ) > 1) {
								$pdf->ezSetDy ( - 10 ); // abstand
								$pdf->ezTable ( $my_arr [$a] ['4281'], $cols, "<b>payment for lawyer | cost account: [4281]</b>", array (
										'showHeadings' => 1,
										'shaded' => 1,
										'titleFontSize' => 8,
										'fontSize' => 7,
										'xPos' => 50,
										'xOrientation' => 'right',
										'width' => 500,
										'cols' => array (
												'BETRAG' => array (
														'justification' => 'right',
														'width' => 65 
												),
												'DATUM' => array (
														'justification' => 'left',
														'width' => 50 
												) 
										) 
								) );
							}
							if (is_array ( $my_arr [$a] ['4282'] ) && count ( $my_arr [$a] ['4282'] ) > 1) {
								// print_r($my_arr[$a]['4180']);
								$pdf->ezSetDy ( - 10 ); // abstand
								$pdf->ezTable ( $my_arr [$a] ['4282'], $cols, "<b>payment for marshal | cost account: [4282]</b>", array (
										'showHeadings' => 1,
										'shaded' => 1,
										'titleFontSize' => 8,
										'fontSize' => 7,
										'xPos' => 50,
										'xOrientation' => 'right',
										'width' => 500,
										'cols' => array (
												'BETRAG' => array (
														'justification' => 'right',
														'width' => 65 
												),
												'DATUM' => array (
														'justification' => 'left',
														'width' => 50 
												) 
										) 
								) );
							}
							
							if (is_array ( $my_arr [$a] ['5081'] ) && count ( $my_arr [$a] ['5081'] ) > 1) {
								// print_r($my_arr[$a]['4180']);
								$pdf->ezSetDy ( - 10 ); // abstand
								$pdf->ezTable ( $my_arr [$a] ['5081'], $cols, "<b>credit repayment | cost account: [5081]</b>", array (
										'showHeadings' => 1,
										'shaded' => 1,
										'titleFontSize' => 8,
										'fontSize' => 7,
										'xPos' => 50,
										'xOrientation' => 'right',
										'width' => 500,
										'cols' => array (
												'BETRAG' => array (
														'justification' => 'right',
														'width' => 65 
												),
												'DATUM' => array (
														'justification' => 'left',
														'width' => 50 
												) 
										) 
								) );
							}
							
							/*
							 * if(is_array($my_arr[$a]['5010']) && count($my_arr[$a]['5010'])>1){
							 * #print_r($my_arr[$a]['4180']);
							 * $pdf->ezSetDy(-10); //abstand
							 * $pdf->ezTable($my_arr[$a]['5010'], $cols, "<b>Actual transfer income | cost account: [5010]</b>", array('showHeadings'=>1,'shaded'=>1, 'titleFontSize' => 8, 'fontSize' => 7, 'xPos'=>50,'xOrientation'=>'right', 'width'=>500, 'cols'=>array('BETRAG'=>array('justification'=>'right', 'width'=>65), 'DATUM'=>array('justification'=>'left', 'width'=>50))));
							 * }
							 */
							
							// ie("TEST");
						} else {
							$cols = array (
									'DATUM' => "Datum",
									'VERWENDUNGSZWECK' => "Buchungstext",
									'BETRAG' => "Betrag" 
							);
							$pdf->ezTable ( $my_arr [$a] ['AUSGABEN'], $cols, "<b>$monat_name $jahr - Reparaturen 1023 - $ein_nam</b>", array (
									'showHeadings' => 1,
									'shaded' => 1,
									'titleFontSize' => 8,
									'fontSize' => 7,
									'xPos' => 50,
									'xOrientation' => 'right',
									'width' => 500,
									'cols' => array (
											'BETRAG' => array (
													'justification' => 'right',
													'width' => 65 
											),
											'DATUM' => array (
													'justification' => 'left',
													'width' => 50 
											) 
									) 
							) );
						}
					} else {
						$pdf->ezText ( "Keine Reparaturen", 12 );
					}
					$pdf->ezSetDy ( - 20 ); // abstand
					                    // $cols = array('ENDSUMME_A'=>"Amount1", 'SUMME_REP_A'=>"Amount", 'TRANSFER_A'=>"Transfer");
					                    // $pdf->ezTable($trans_tab, $cols);
					                    // unset($trans_tab);
					$trans_tab [0] ['TEXT'] = "Amount [�]";
					$trans_tab [0] ['AM'] = $uebersicht [$a] ['ENDSUMME_A'];
					$trans_tab [1] ['TEXT'] = "Bills [�]";
					$trans_tab [1] ['AM'] = $uebersicht [$a] ['SUMME_REP_A'];
					$trans_tab [2] ['TEXT'] = "<b>To transfer [�]</b>";
					if ($uebersicht [$a] ['TRANSFER'] > 0) {
						$trans_tab [2] ['TEXT'] = "<b>To transfer [�]</b>";
						$trans_tab [2] ['AM'] = "<b>" . $uebersicht [$a] ['TRANSFER_A'] . "</b>";
					} else {
						$trans_tab [2] ['TEXT'] = "<b>Summary [�]</b>";
						$trans_tab [2] ['AM'] = "<b>" . $uebersicht [$a] ['TRANSFER_A'] . "</b>";
						$trans_tab [3] ['TEXT'] = "<b>To transfer [�]</b>";
						$trans_tab [3] ['AM'] = "<b>0,00</b>";
					}
					/* Gebuchte �berweisung Kto: 5020 */
					/*
					 * $trans_tab[3]['TEXT'] = "<b>Current Transfer [�]</b>";
					 * $trans_tab[3]['AM'] = "<b>xxx</b>";
					 */
					
					/*
					 * if(is_array($my_arr[$a]['5020']) && count($my_arr[$a]['5020'])>1){
					 * $pdf->ezSetDy(-10); //abstand
					 * $pdf->ezTable($my_arr[$a]['5020'], $cols, "<b>Current Transfer | cost account: [5020]</b>", array('showHeadings'=>1,'shaded'=>1, 'titleFontSize' => 8, 'fontSize' => 7, 'xPos'=>50,'xOrientation'=>'right', 'width'=>500, 'cols'=>array('BETRAG'=>array('justification'=>'right', 'width'=>65), 'DATUM'=>array('justification'=>'left', 'width'=>50))));
					 * }
					 */
					
					$cols = array (
							'TEXT' => "",
							'AM' => "" 
					);
					$pdf->ezTable ( $trans_tab, $cols, "<b>Summary $w_monat/$jahr</b>", array (
							'showHeadings' => 0,
							'shaded' => 1,
							'titleFontSize' => 8,
							'fontSize' => 7,
							'xPos' => 235,
							'xOrientation' => 'right',
							'width' => 500,
							'cols' => array (
									'TEXT' => array (
											'justification' => 'right',
											'width' => 250 
									),
									'AM' => array (
											'justification' => 'right',
											'width' => 65 
									) 
							) 
					) );
					$cols = array (
							'DATUM' => "<b>Date</b>",
							'VERWENDUNGSZWECK' => "<b>Description</b>",
							'BETRAG' => "<b>Amount [�]</b>" 
					);
					
					// $pdf->setColor(1.0,0.0,0.0);
					// $pdf->ezText("SANEL");
					
					$pdf->options [] = array (
							'textCol' => array (
									1,
									0,
									0 
							) 
					);
					if (is_array ( $my_arr [$a] ['5010'] ) && count ( $my_arr [$a] ['5010'] ) > 1) {
						$anz_aus = count ( $my_arr [$a] ['5010'] );
						
						for($aaa = 0; $aaa < $anz_aus; $aaa ++) {
							if ($aaa == $anz_aus - 1) {
								$bbbb = $my_arr [$a] ['5010'] [$aaa] ['BETRAG'];
								$my_arr [$a] ['5010'] [$aaa] ['BETRAG'] = "<b>$bbbb</b>";
							} else {
								$my_arr [$a] ['5010'] [$aaa] ['BETRAG'] = $my_arr [$a] ['5010'] [$aaa] ['BETRAG'];
							}
						}
						
						$pdf->ezSetDy ( - 10 ); // abstand
						                    // $pdf->options['titleCol'] =array(1,0,0);
						$pdf->ezTable ( $my_arr [$a] ['5010'], $cols, "<b>Actual transfer income | cost account: [5010]</b>", array (
								'showHeadings' => 1,
								'shaded' => 1,
								'titleFontSize' => 8,
								'fontSize' => 7,
								'xPos' => 50,
								'xOrientation' => 'right',
								'width' => 500,
								'cols' => array (
										'BETRAG' => array (
												'justification' => 'right',
												'width' => 65 
										),
										'DATUM' => array (
												'justification' => 'left',
												'width' => 50 
										) 
								) 
						) );
						// $pdf->setColor(0.0,0.0,0.0);
					}
					
					$pdf->ezSetDy ( - 10 ); // abstand
					
					if (is_array ( $my_arr [$a] ['5020'] ) && count ( $my_arr [$a] ['5020'] ) > 1) {
						$anz_aus = count ( $my_arr [$a] ['5020'] );
						
						for($aaa = 0; $aaa < $anz_aus; $aaa ++) {
							if ($aaa == $anz_aus - 1) {
								$bbbb = $my_arr [$a] ['5020'] [$aaa] ['BETRAG'] * - 1; // POSITIVIEREN
								$my_arr [$a] ['5020'] [$aaa] ['BETRAG'] = "<b>$bbbb</b>";
							} else {
								$my_arr [$a] ['5020'] [$aaa] ['BETRAG'] = $my_arr [$a] ['5020'] [$aaa] ['BETRAG'] * - 1; // POSITIVIEREN
							}
						}
					} else {
						$my_arr [$a] ['5020'] [0] ['BETRAG'] = "<b>0.00</b>";
						$my_arr [$a] ['5020'] [0] ['VERWENDUNGSZWECK'] = "<b>No transfer</b>";
					}
					
					$pdf->ezTable ( $my_arr [$a] ['5020'], $cols, "<b>Actual transfer | cost account: [5020]</b>", array (
							'showHeadings' => 1,
							'shaded' => 1,
							'titleFontSize' => 8,
							'fontSize' => 7,
							'xPos' => 50,
							'xOrientation' => 'right',
							'width' => 500,
							'cols' => array (
									'BETRAG' => array (
											'justification' => 'right',
											'width' => 65 
									),
									'DATUM' => array (
											'justification' => 'left',
											'width' => 50 
									) 
							) 
					) );
					
					// $pdf->ezSetDy(-10);
					// $pdf->ezText("<b>For differences between \"to transfer\" and the actually transfer please ask the customer service
					// (fon: +49 30 698 19398-12 or e-mail: service @inspirationgroup.biz)</b>", 10);
					
					unset ( $trans_tab );
					
					/* TAbelle Auszahlung an Eigent�mer */
					// print_r($my_arr[$a]['AUSZAHLUNG_ET']);
					// die();
					if (is_array ( $my_arr [$a] ['AUSZAHLUNG_ET'] )) {
						
						if ($lang == 'en') {
							// $cols = array('DATUM'=>"Date", 'VERWENDUNGSZWECK'=>"Description", 'BETRAG'=>"Amount");
							// $pdf->ezTable($my_arr[$a]['AUSZAHLUNG_ET'], $cols, "<b>$monat_name $jahr - transfer 5020 - $ein_nam</b>", array('showHeadings'=>1,'shaded'=>1, 'titleFontSize' => 8, 'fontSize' => 7, 'xPos'=>50,'xOrientation'=>'right', 'width'=>500, 'cols'=>array('BETRAG'=>array('justification'=>'right', 'width'=>65),'DATUM'=>array('justification'=>'left', 'width'=>50))));
						} else {
							// $cols = array('DATUM'=>"Datum", 'VERWENDUNGSZWECK'=>"Buchungstext", 'BETRAG'=>"Betrag");
							// $pdf->ezTable($my_arr[$a]['AUSZAHLUNG_ET'], $cols, "<b>$monat_name $jahr - �berweisung 80001 - $ein_nam</b>", array('showHeadings'=>1,'shaded'=>1, 'titleFontSize' => 8, 'fontSize' => 7, 'xPos'=>50,'xOrientation'=>'right', 'width'=>500, 'cols'=>array('BETRAG'=>array('justification'=>'right', 'width'=>65),'DATUM'=>array('justification'=>'left', 'width'=>50))));
						}
					}
					/*
					 * if(is_array($my_arr[$a]['IST_EINNAHMEN'])){
					 * if($lang=='en'){
					 * $cols = array('DATUM'=>"Date", 'VERWENDUNGSZWECK'=>"Description", 'BETRAG'=>"Amount");
					 * $pdf->ezTable($my_arr[$a]['IST_EINNAHMEN'], $cols, "<b>$monat_name $jahr - income overview 80001 - $ein_nam</b>", array('showHeadings'=>1,'shaded'=>1, 'titleFontSize' => 8, 'fontSize' => 7, 'xPos'=>50,'xOrientation'=>'right', 'width'=>500, 'cols'=>array('BETRAG'=>array('justification'=>'right', 'width'=>65),'DATUM'=>array('justification'=>'left', 'width'=>50))));
					 * }else{
					 * $cols = array('DATUM'=>"Datum", 'VERWENDUNGSZWECK'=>"Buchungstext", 'BETRAG'=>"Betrag");
					 * $pdf->ezTable($my_arr[$a]['IST_EINNAHMEN'], $cols, "<b>$monat_name $jahr - Einnahmen 80001 - $ein_nam</b>", array('showHeadings'=>1,'shaded'=>1, 'titleFontSize' => 8, 'fontSize' => 7, 'xPos'=>50,'xOrientation'=>'right', 'width'=>500, 'cols'=>array('BETRAG'=>array('justification'=>'right', 'width'=>65),'DATUM'=>array('justification'=>'left', 'width'=>50))));
					 * }
					 *
					 * }else{
					 * $pdf->ezText("Keine Mieteinnahmen", 12);
					 * }
					 */
					
					// $cols = array('DATUM'=>"Datum",'VERWENDUNGSZWECK'=>"Buchungstext", 'BETRAG'=>"Betrag");
					// $pdf->ezTable($pdf_tab[$a]['EIG_AUSZAHLUNG'], $cols, "<b>$monat_name $jahr - Auszahlung an Eigent�mer 80001 - $ein_nam</b>", array('showHeadings'=>1,'shaded'=>1, 'titleFontSize' => 8, 'fontSize' => 7, 'xPos'=>50,'xOrientation'=>'right', 'width'=>500, 'cols'=>array('BETRAG'=>array('justification'=>'right', 'width'=>65),'DATUM'=>array('justification'=>'left', 'width'=>50))));
					// $pdf->Cezpdf('a4','landscape');
					$pdf->ezNewPage ();
					
					/*
					 * $size = array(0,0,595.28,841.89);
					 * $a_k=$size[3];
					 * $size[3]=$size[2];
					 * $size[2]=$a_k;
					 *
					 *
					 * $pdf->ez['pageWidth']=$size[2];
					 * $pdf->ez['pageHeight']=$size[3];
					 * #$pdf->ezSetMargins(120,40,30,30);
					 */
					$my_arr [$a] ['ABGABE_IHR'] = $pdf_tab [$a] ['ABGABE_IHR'];
					unset ( $pdf_tab );
				}
			}
			$uebersicht [$anz] ['EIGENTUEMER_NAMEN'] = 'Soll';
			$uebersicht [$anz] ['ENDSUMME_A'] = nummer_punkt2komma_t ( $summe_alle_eigentuemer );
			$uebersicht [$anz + 1] ['EIGENTUEMER_NAMEN'] = 'Auszahlen';
			$uebersicht [$anz + 1] ['TRANSFER_A'] = nummer_punkt2komma_t ( $summe_transfer );
			// $uebersicht[$anz+1]['EIGENTUEMER_NAMEN'] = 'Zu erhalten';
			// $uebersicht[$anz+1]['ENDSUMME_A'] = nummer_punkt2komma_t($summe_nachzahler);
			
			if ($lang == 'en') {
				$cols = array (
						'EINHEIT_KURZNAME' => "Apt",
						'WEG-FLAECHE_A' => 'm�',
						'EIGENTUEMER_NAMEN' => "Own",
						'MIETER' => "Tenant",
						'MIETER_SALDO_VM' => 'VM',
						'MIETER_SALDO_A' => 'current',
						'NETTO_SOLL_G_A' => "Garanty",
						'NETTO_SOLL_A' => "rent",
						'NETTO_SOLL_DIFF_A' => "diff",
						'ABGABE_HV_A' => "fee",
						'ABGABE_IHR' => "for maint.",
						'ENDSUMME_A' => "Amount",
						'SUMME_REP_A' => 'Rep.',
						'TRANSFER_A' => 'transfer' 
				);
			} else {
				$cols = array (
						'EINHEIT_KURZNAME' => "Apt",
						'WEG-FLAECHE_A' => 'm�',
						'EIGENTUEMER_NAMEN' => "Own",
						'MIETER' => "Tenant",
						'MIETER_SALDO_A' => 'current',
						'NETTO_SOLL_G_A' => "Garanty",
						'NETTO_SOLL_A' => "rent",
						'NETTO_SOLL_DIFF_A' => "diff",
						'ABGABE_HV_A' => "fee",
						'ABGABE_IHR' => "for maint.",
						'ENDSUMME_A' => "Amount",
						'SUMME_REP_A' => 'Rep.',
						'TRANSFER_A' => 'transfer' 
				);
			}
			$von_d = date_mysql2german ( $von );
			$bis_d = date_mysql2german ( $bis );
			$pdf->ezText ( "<b>Kostenabfrage von: $von_d bis: $bis_d</b>", 12 );
			$pdf->ezSetDy ( - 20 ); // abstand
			$pdf->ezTable ( $uebersicht, $cols, null, array (
					'showHeadings' => 1,
					'shaded' => 1,
					'titleFontSize' => 8,
					'fontSize' => 7,
					'xPos' => 10,
					'xOrientation' => 'right',
					'width' => 550,
					'cols' => array (
							'EINHEIT_KURZNAME' => array (
									'justification' => 'left',
									'width' => '40' 
							),
							'EIGENTUEMER_NAME' => array (
									'justification' => 'right',
									'width' => '40' 
							),
							'MIETER' => array (
									'justification' => 'right',
									'width' => '60' 
							) 
					) 
			) );
			// print_r($pdf_tab);
			// print_r($pdf_tab_soll);
			echo '<pre>';
			// print_r($my_arr[1]);
			// die();
			
			$anz_m = count ( $my_arr );
			$z = 0;
			for($mm = 0; $mm < $anz_m; $mm ++) {
				$saldo_m_et = 0;
				$einheit_kn = $my_arr [$mm] ['EINHEIT_KURZNAME'];
				
				// $et_ue_tab[$einheit_kn][$z]['TXT'] = 'EINHEIT';
				// #$et_ue_tab[$einheit_kn][$z]['BEZ'] = $my_arr[$mm]['EINHEIT_KURZNAME'];
				// $et_ue_tab[$einheit_kn][$z]['BET'] = ' ';
				// $et_ue_tab[$einheit_kn][$z]['DATUM'] = ' ';
				// $z++;
				
				/* Soll Miete */
				/*
				 * $et_ue_tab[$einheit_kn][$z]['TXT'] = 'Monthly';
				 * $et_ue_tab[$einheit_kn][$z]['BEZ'] = 'Rent (B)';
				 * $et_ue_tab[$einheit_kn][$z]['BET'] = $my_arr[$mm]['BRUTTO_SOLL'];
				 * $et_ue_tab[$einheit_kn][$z]['DATUM'] = 'Monthly';
				 * $saldo_m_et += $et_ue_tab[$einheit_kn][$z]['BET'];
				 * $z++;
				 *
				 * /*Abgabe IHR / Hausgeld etc
				 */
				/*
				 * $et_ue_tab[$einheit_kn][$z]['TXT'] = 'Monthly';
				 * $et_ue_tab[$einheit_kn][$z]['BEZ'] = 'for maint.';
				 * $et_ue_tab[$einheit_kn][$z]['BET'] = nummer_komma2punkt(nummer_punkt2komma($my_arr[$mm]['ABGABE_IHR']));
				 * $et_ue_tab[$einheit_kn][$z]['DATUM'] = 'Monthly';
				 * $saldo_m_et += $et_ue_tab[$einheit_kn][$z]['BET'];
				 * $z++;
				 *
				 *
				 *
				 * /*Abgaben
				 */
				/*
				 * if(is_array($my_arr[$mm]['ABGABEN'])){
				 *
				 * $anz_ab = count($my_arr[$mm]['ABGABEN']);
				 * for($ab=0;$ab<$anz_ab;$ab++){
				 * $arr_key = array_keys($my_arr[$mm]['ABGABEN'][$ab]);
				 * $key = $arr_key[0];
				 * if($my_arr[$mm]['ABGABEN'][$ab][$key]<>0){
				 * $et_ue_tab[$einheit_kn][$z]['TXT']= $key;
				 * $et_ue_tab[$einheit_kn][$z]['BEZ'] = 'Man. Fee';
				 * $et_ue_tab[$einheit_kn][$z]['BET'] = $my_arr[$mm]['ABGABEN'][$ab][$key]*-1;
				 * $et_ue_tab[$einheit_kn][$z]['DATUM'] = 'Monthly';
				 * $saldo_m_et += $et_ue_tab[$einheit_kn][$z]['BET'];
				 * $z++;
				 * }
				 * }
				 * }
				 */
				
				/* Ausgaben 1023 */
				if (is_array ( $my_arr [$mm] ['AUSGABEN'] )) {
					
					$anz_ab = count ( $my_arr [$mm] ['AUSGABEN'] );
					for($ab = 0; $ab < $anz_ab; $ab ++) {
						if (isset ( $my_arr [$mm] ['AUSGABEN'] [$ab] ['GELD_KONTO_BUCHUNGEN_DAT'] )) {
							
							$konto = $my_arr [$mm] ['AUSGABEN'] [$ab] ['KONTENRAHMEN_KONTO'];
							$vzweck = $my_arr [$mm] ['AUSGABEN'] [$ab] ['VERWENDUNGSZWECK'];
							
							$et_ue_tab [$einheit_kn] [$z] ['TXT'] = "Repairs $konto";
							$et_ue_tab [$einheit_kn] [$z] ['BEZ'] = $vzweck;
							$et_ue_tab [$einheit_kn] [$z] ['BET'] = $my_arr [$mm] ['AUSGABEN'] [$ab] ['BETRAG'];
							$et_ue_tab [$einheit_kn] [$z] ['DATUM'] = $my_arr [$mm] ['AUSGABEN'] [$ab] ['DATUM'];
							$saldo_m_et += $et_ue_tab [$einheit_kn] [$z] ['BET'];
							$z ++;
						}
					}
				}
				
				/* Ausgaben 5500 */
				if (is_array ( $my_arr [$mm] ['5500'] )) {
					
					$anz_ab = count ( $my_arr [$mm] ['5500'] );
					for($ab = 0; $ab < $anz_ab; $ab ++) {
						if (isset ( $my_arr [$mm] ['5500'] [$ab] ['GELD_KONTO_BUCHUNGEN_DAT'] )) {
							
							$konto = $my_arr [$mm] ['5500'] [$ab] ['KONTENRAHMEN_KONTO'];
							$vzweck = $my_arr [$mm] ['5500'] [$ab] ['VERWENDUNGSZWECK'];
							
							$et_ue_tab [$einheit_kn] [$z] ['TXT'] = "Konto 5500";
							$et_ue_tab [$einheit_kn] [$z] ['BEZ'] = $vzweck;
							$et_ue_tab [$einheit_kn] [$z] ['BET'] = $my_arr [$mm] ['5500'] [$ab] ['BETRAG'];
							$et_ue_tab [$einheit_kn] [$z] ['DATUM'] = $my_arr [$mm] ['5500'] [$ab] ['DATUM'];
							$saldo_m_et += $et_ue_tab [$einheit_kn] [$z] ['BET'];
							$z ++;
						}
					}
				}
				
				/* Ausgaben 4180 */
				if (is_array ( $my_arr [$mm] ['4180'] )) {
					
					$anz_ab = count ( $my_arr [$mm] ['4180'] );
					for($ab = 0; $ab < $anz_ab; $ab ++) {
						if (isset ( $my_arr [$mm] ['4180'] [$ab] ['GELD_KONTO_BUCHUNGEN_DAT'] )) {
							
							$konto = $my_arr [$mm] ['4180'] [$ab] ['KONTENRAHMEN_KONTO'];
							$vzweck = $my_arr [$mm] ['4180'] [$ab] ['VERWENDUNGSZWECK'];
							
							$et_ue_tab [$einheit_kn] [$z] ['TXT'] = "Konto 4180";
							$et_ue_tab [$einheit_kn] [$z] ['BEZ'] = $vzweck;
							$et_ue_tab [$einheit_kn] [$z] ['BET'] = $my_arr [$mm] ['4180'] [$ab] ['BETRAG'];
							$et_ue_tab [$einheit_kn] [$z] ['DATUM'] = $my_arr [$mm] ['4180'] [$ab] ['DATUM'];
							$saldo_m_et += $et_ue_tab [$einheit_kn] [$z] ['BET'];
							$z ++;
						}
					}
				}
				
				/* Ausgaben 4280 */
				if (is_array ( $my_arr [$mm] ['4280'] )) {
					
					$anz_ab = count ( $my_arr [$mm] ['4280'] );
					for($ab = 0; $ab < $anz_ab; $ab ++) {
						if (isset ( $my_arr [$mm] ['4280'] [$ab] ['GELD_KONTO_BUCHUNGEN_DAT'] )) {
							
							$konto = $my_arr [$mm] ['4280'] [$ab] ['KONTENRAHMEN_KONTO'];
							$vzweck = $my_arr [$mm] ['4280'] [$ab] ['VERWENDUNGSZWECK'];
							
							$et_ue_tab [$einheit_kn] [$z] ['TXT'] = "Konto 4280";
							$et_ue_tab [$einheit_kn] [$z] ['BEZ'] = $vzweck;
							$et_ue_tab [$einheit_kn] [$z] ['BET'] = $my_arr [$mm] ['4280'] [$ab] ['BETRAG'];
							$et_ue_tab [$einheit_kn] [$z] ['DATUM'] = $my_arr [$mm] ['4280'] [$ab] ['DATUM'];
							$saldo_m_et += $et_ue_tab [$einheit_kn] [$z] ['BET'];
							$z ++;
						}
					}
				}
				
				/* Ausgaben 4281 */
				if (is_array ( $my_arr [$mm] ['4281'] )) {
					
					$anz_ab = count ( $my_arr [$mm] ['4281'] );
					for($ab = 0; $ab < $anz_ab; $ab ++) {
						if (isset ( $my_arr [$mm] ['4281'] [$ab] ['GELD_KONTO_BUCHUNGEN_DAT'] )) {
							
							$konto = $my_arr [$mm] ['4281'] [$ab] ['KONTENRAHMEN_KONTO'];
							$vzweck = $my_arr [$mm] ['4281'] [$ab] ['VERWENDUNGSZWECK'];
							
							$et_ue_tab [$einheit_kn] [$z] ['TXT'] = "Konto 4281";
							$et_ue_tab [$einheit_kn] [$z] ['BEZ'] = $vzweck;
							$et_ue_tab [$einheit_kn] [$z] ['BET'] = $my_arr [$mm] ['4281'] [$ab] ['BETRAG'];
							$et_ue_tab [$einheit_kn] [$z] ['DATUM'] = $my_arr [$mm] ['4281'] [$ab] ['DATUM'];
							$saldo_m_et += $et_ue_tab [$einheit_kn] [$z] ['BET'];
							$z ++;
						}
					}
				}
				
				/* Ausgaben 4282 */
				if (is_array ( $my_arr [$mm] ['4282'] )) {
					
					$anz_ab = count ( $my_arr [$mm] ['4282'] );
					for($ab = 0; $ab < $anz_ab; $ab ++) {
						if (isset ( $my_arr [$mm] ['4282'] [$ab] ['GELD_KONTO_BUCHUNGEN_DAT'] )) {
							
							$konto = $my_arr [$mm] ['4282'] [$ab] ['KONTENRAHMEN_KONTO'];
							$vzweck = $my_arr [$mm] ['4282'] [$ab] ['VERWENDUNGSZWECK'];
							
							$et_ue_tab [$einheit_kn] [$z] ['TXT'] = "Konto 4282";
							$et_ue_tab [$einheit_kn] [$z] ['BEZ'] = $vzweck;
							$et_ue_tab [$einheit_kn] [$z] ['BET'] = $my_arr [$mm] ['4282'] [$ab] ['BETRAG'];
							$et_ue_tab [$einheit_kn] [$z] ['DATUM'] = $my_arr [$mm] ['4282'] [$ab] ['DATUM'];
							$saldo_m_et += $et_ue_tab [$einheit_kn] [$z] ['BET'];
							$z ++;
						}
					}
				}
				
				/* Ausgaben 5081 */
				if (is_array ( $my_arr [$mm] ['5081'] )) {
					
					$anz_ab = count ( $my_arr [$mm] ['5081'] );
					for($ab = 0; $ab < $anz_ab; $ab ++) {
						if (isset ( $my_arr [$mm] ['5081'] [$ab] ['GELD_KONTO_BUCHUNGEN_DAT'] )) {
							
							$konto = $my_arr [$mm] ['5081'] [$ab] ['KONTENRAHMEN_KONTO'];
							$vzweck = $my_arr [$mm] ['5081'] [$ab] ['VERWENDUNGSZWECK'];
							
							$et_ue_tab [$einheit_kn] [$z] ['TXT'] = "Konto 5081";
							$et_ue_tab [$einheit_kn] [$z] ['BEZ'] = $vzweck;
							$et_ue_tab [$einheit_kn] [$z] ['BET'] = $my_arr [$mm] ['5081'] [$ab] ['BETRAG'];
							$et_ue_tab [$einheit_kn] [$z] ['DATUM'] = $my_arr [$mm] ['5081'] [$ab] ['DATUM'];
							$saldo_m_et += $et_ue_tab [$einheit_kn] [$z] ['BET'];
							$z ++;
						}
					}
				}
				
				/* Ausgaben 5010 */
				if (is_array ( $my_arr [$mm] ['5010'] )) {
					
					$anz_ab = count ( $my_arr [$mm] ['5010'] );
					for($ab = 0; $ab < $anz_ab; $ab ++) {
						if (isset ( $my_arr [$mm] ['5010'] [$ab] ['GELD_KONTO_BUCHUNGEN_DAT'] )) {
							
							$konto = $my_arr [$mm] ['5010'] [$ab] ['KONTENRAHMEN_KONTO'];
							$vzweck = $my_arr [$mm] ['5010'] [$ab] ['VERWENDUNGSZWECK'];
							
							$et_ue_tab [$einheit_kn] [$z] ['TXT'] = "Konto 5010";
							$et_ue_tab [$einheit_kn] [$z] ['BEZ'] = $vzweck;
							$et_ue_tab [$einheit_kn] [$z] ['BET'] = $my_arr [$mm] ['5010'] [$ab] ['BETRAG'];
							$et_ue_tab [$einheit_kn] [$z] ['DATUM'] = $my_arr [$mm] ['5010'] [$ab] ['DATUM'];
							$saldo_m_et += $et_ue_tab [$einheit_kn] [$z] ['BET'];
							$z ++;
						}
					}
				}
				
				/* Ausgaben 5020 */
				if (is_array ( $my_arr [$mm] ['5020'] )) {
					
					$anz_ab = count ( $my_arr [$mm] ['5020'] );
					for($ab = 0; $ab < $anz_ab; $ab ++) {
						if (isset ( $my_arr [$mm] ['5020'] [$ab] ['GELD_KONTO_BUCHUNGEN_DAT'] )) {
							
							$konto = $my_arr [$mm] ['5020'] [$ab] ['KONTENRAHMEN_KONTO'];
							$vzweck = $my_arr [$mm] ['5020'] [$ab] ['VERWENDUNGSZWECK'];
							
							$et_ue_tab [$einheit_kn] [$z] ['TXT'] = "Konto 5020";
							$et_ue_tab [$einheit_kn] [$z] ['BEZ'] = $vzweck;
							$et_ue_tab [$einheit_kn] [$z] ['BET'] = $my_arr [$mm] ['5020'] [$ab] ['BETRAG'] * - 1;
							$et_ue_tab [$einheit_kn] [$z] ['DATUM'] = $my_arr [$mm] ['5020'] [$ab] ['DATUM'];
							$saldo_m_et += $et_ue_tab [$einheit_kn] [$z] ['BET'];
							$z ++;
						}
					}
				}
				
				/* Saldo */
				if ($saldo_m_et != 0) {
					$et_ue_tab [$einheit_kn] [$z] ['TXT'] = 'SALDO';
					$et_ue_tab [$einheit_kn] [$z] ['BEZ'] = 'SALDO';
					$et_ue_tab [$einheit_kn] [$z] ['BET'] = nummer_komma2punkt ( nummer_punkt2komma ( $saldo_m_et ) );
				}
				
				$z = 0;
			}
			
			// echo "<hr>";
			// echo '<pre>';
			// print_r($et_ue_tab);
			// die();
			
			$w_keys = array_unique ( array_keys ( $et_ue_tab ) );
			$colss = array (
					'DATUM' => "Date",
					'TXT' => "Description",
					'BEZ' => "Description1",
					'BET' => "Amount" 
			);
			$pdf->ezNewPage ();
			for($p = 0; $p < count ( $w_keys ); $p ++) {
				$wohnung = $w_keys [$p];
				// $pdf->ezNewPage();
				// $pdf->eztable($et_ue_tab[$wohnung]);
				// $pdf->ezTable($et_ue_tab[$wohnung], $colss,"$wohnung $monat/$jahr", array('showHeadings'=>1,'shaded'=>1, 'titleFontSize' => 8, 'fontSize' => 7, 'xPos'=>50,'xOrientation'=>'right', 'width'=>500));
				$pdf->ezTable ( $et_ue_tab [$wohnung], $colss, "<b>$wohnung $monat/$jahr</b>", array (
						'showHeadings' => 1,
						'shaded' => 1,
						'titleFontSize' => 8,
						'fontSize' => 7,
						'xPos' => 50,
						'xOrientation' => 'right',
						'width' => 500,
						'cols' => array (
								'DATUM' => array (
										'justification' => 'left',
										'width' => 50 
								),
								'TXT' => array (
										'justification' => 'right',
										'width' => 100 
								),
								'BEZ' => array (
										'justification' => 'left' 
								),
								'BET' => array (
										'justification' => 'right',
										'width' => 60 
								) 
						) 
				) );
				$pdf->ezSetDy ( - 10 ); // abstand
			}
			
			// die();
			
			// print_r($pdf);
			// die();
			// $pdf->newPage()
			
			// echo '<pre>';
			// print_r($my_arr);
			// die();
			
			// die();
			
			ob_clean (); // ausgabepuffer leeren
			header ( "Content-type: application/pdf" ); // wird von MSIE ignoriert
			$pdf->ezStream ();
		} else {
			die ( "Keine Einheiten im Objekt $objekt_id" );
		}
	}
	function inspiration_sepa_arr($ausgezogene = 0, $objekt_id, $monat, $jahr, $lang = 'de') {
		$monat_name = monat2name ( $monat );
		$gk = new geldkonto_info ();
		$gk->geld_konto_ermitteln ( 'OBJEKT', $objekt_id );
		if (! $gk->geldkonto_id) {
			die ( 'Geldkonto zum Objekt hinzuf�gen!!!' );
		}
		$db_abfrage = "SELECT OBJEKT_KURZNAME, HAUS_STRASSE, HAUS_NUMMER, `EINHEIT_KURZNAME` , `EINHEIT_ID`,  ltrim(rtrim(EINHEIT_LAGE)) AS EINHEIT_LAGE, `EINHEIT_QM` FROM EINHEIT , HAUS, OBJEKT
WHERE `EINHEIT_AKTUELL` = '1' && EINHEIT.HAUS_ID = HAUS.HAUS_ID && HAUS.OBJEKT_ID=OBJEKT.OBJEKT_ID && HAUS_AKTUELL='1' && OBJEKT_AKTUELL='1' && OBJEKT.OBJEKT_ID='$objekt_id' 
ORDER BY EINHEIT_KURZNAME";
		
		$result = mysql_query ( $db_abfrage );
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			$z = 0;
			while ( $row = mysql_fetch_assoc ( $result ) ) {
				$my_arr [] = $row;
				$einheit_id = $row ['EINHEIT_ID'];
				$einheit_qm = $row ['EINHEIT_QM'];
				$e = new einheit ();
				$det = new detail ();
				$my_arr [$z] ['WEG-FLAECHE_A'] = $det->finde_detail_inhalt ( 'EINHEIT', $einheit_id, 'WEG-Fl�che' ); // kommt als Kommazahl
				if (empty ( $my_arr [$z] ['WEG-FLAECHE_A'] )) {
					$my_arr [$z] ['WEG-FLAECHE_A'] = nummer_punkt2komma ( $einheit_qm );
				}
				$my_arr [$z] ['WEG-FLAECHE'] = nummer_komma2punkt ( $my_arr [$z] ['WEG-FLAECHE_A'] );
				
				$my_arr [$z] ['WG_NR'] = $det->finde_detail_inhalt ( 'EINHEIT', $einheit_id, 'Alte Nr' ); // kommt als Kommazahl
				
				$weg = new weg ();
				$weg->get_last_eigentuemer ( $einheit_id );
				if (isset ( $weg->eigentuemer_name )) {
					// echo '<pre>';
					// print_r($weg);
					// $my_arr[$z]['EIGENTUEMER'] = $weg->eigentuemer_name;
					$weg->get_eigentuemer_namen ( $weg->eigentuemer_id );
					$my_arr [$z] ['EIGENTUEMER_NAMEN'] = $weg->eigentuemer_name_str_u;
					$my_arr [$z] ['EIGENTUEMER_ID'] = $weg->eigentuemer_id;
				} else {
					$my_arr [$z] ['EIGENTUEMER'] = 'Unbekannt';
				}
				$mv_id = $e->get_mietvertrag_id ( $einheit_id );
				if ($mv_id) {
					$mvs = new mietvertraege ();
					$mvs->get_mietvertrag_infos_aktuell ( $mv_id );
					$kontaktdaten = $e->kontaktdaten_mieter ( $mv_id );
					// $my_arr[$z]['MIETER'] = $mvs->personen_name_string_u."\n".$kontaktdaten;
					$my_arr [$z] ['MIETER'] = $mvs->personen_name_string_u;
					$my_arr [$z] ['MIETVERTRAG_ID'] = $mv_id;
					$my_arr [$z] ['KONTAKT'] = $kontaktdaten;
					$my_arr [$z] ['EINHEIT_ID'] = $einheit_id;
					$mz = new miete ();
					$mz->mietkonto_berechnung ( $mv_id );
					$my_arr [$z] ['MIETER_SALDO'] = $mz->erg;
				} else {
					$my_arr [$z] ['MIETER'] = 'Leerstand';
				}
				$z ++;
			}
			// print_r($weg);
			
			unset ( $e );
			unset ( $mvs );
			unset ( $weg );
			
			$anz = count ( $my_arr );
			/* Berechnung Abgaben */
			for($a = 0; $a < $anz; $a ++) {
				$einheit_id = $my_arr [$a] ['EINHEIT_ID'];
				if (isset ( $my_arr [$a] ['EIGENTUEMER_ID'] )) {
					$eige_id = $my_arr [$a] ['EIGENTUEMER_ID'];
					// echo $my_arr[$a]['EIGENTUEMER_ID'];
					// die();
					$my_arr [$a] ['ABGABEN'] [] ['ABGABE_IHR'] = $my_arr [$a] ['WEG-FLAECHE'] * - 0.4;
					if (empty ( $my_arr [$a] ['WEG-FLAECHE'] )) {
						$my_arr [$a] ['ABGABEN'] [] ['ABGABE_IHR'] = $einheit_qm * - 0.4;
					}
					$my_arr [$a] ['ABGABEN'] [] ['VG'] = '30.00'; // Verwaltergeb�hr
					
					/* Kosten 1023 Reparatur Einheit */
					$my_arr [$a] ['AUSGABEN'] = $this->get_kosten_arr ( 'EINHEIT', $my_arr [$a] ['EINHEIT_ID'], $monat, $jahr, $gk->geldkonto_id, 1023 );
					$anz_rep = count ( $my_arr [$a] ['AUSGABEN'] );
					$summe_rep = 0;
					for($b = 0; $b < $anz_rep; $b ++) {
						$summe_rep += $my_arr [$a] ['AUSGABEN'] [$b] ['BETRAG'];
					}
					$my_arr [$a] ['AUSGABEN'] [$anz_rep] ['BETRAG'] = '<b>' . nummer_punkt2komma_t ( $summe_rep ) . '</b>';
					$my_arr [$a] ['AUSGABEN'] [$anz_rep] ['VERWENDUNGSZWECK'] = '<b>Summe</b>';
					
					// echo "'EINHEIT', $my_arr[$a]['EINHEIT_ID'], $monat, $jahr, $gk->geldkonto_id<br>";
					// print_r($arr);
					
					$mk = new mietkonto ();
					$mk->kaltmiete_monatlich ( $my_arr [$a] ['MIETVERTRAG_ID'], $monat, $jahr );
					$brutto_sollmiete_arr = explode ( '|', $mk->summe_forderung_monatlich ( $my_arr [$a] ['MIETVERTRAG_ID'], $monat, $jahr ) );
					$brutto_sollmiete = $brutto_sollmiete_arr [0];
					$my_arr [$a] ['NETTO_SOLL_MV'] = $mk->ausgangs_kaltmiete;
					
					/* Garantierte Miete abfragen */
					$net_ren_garantie_a = $det->finde_detail_inhalt ( 'EINHEIT', $einheit_id, 'WEG-KaltmieteINS' ); // kommt als Kommazahl
					$net_ren_garantie = nummer_komma2punkt ( $net_ren_garantie_a );
					$my_arr [$a] ['NETTO_SOLL_G_A'] = $net_ren_garantie_a;
					// if($einheit_id=='945'){
					// die("SANEL $einheit_id $net_ren_garantie");
					// }
					if ($net_ren_garantie > $mk->ausgangs_kaltmiete) {
						$my_arr [$a] ['NETTO_SOLL'] = $net_ren_garantie;
					} else {
						$my_arr [$a] ['NETTO_SOLL'] = $mk->ausgangs_kaltmiete;
					}
					
					$my_arr [$a] ['BRUTTO_SOLL'] = $brutto_sollmiete;
					$my_arr [$a] ['AUSZAHLUNG_ET'] = $this->get_kosten_arr ( 'Eigentuemer', $eige_id, $monat, $jahr, $gk->geldkonto_id, 5020 );
					// print_r($my_arr[$a]['AUSZAHLUNG_ET']);
					// die();
					$my_arr [$a] ['IST_EINNAHMEN'] = $this->get_kosten_arr ( 'MIETVERTRAG', $my_arr [$a] ['MIETVERTRAG_ID'], $monat, $jahr, $gk->geldkonto_id, 80001 );
					$anz_me = count ( $my_arr [$a] ['IST_EINNAHMEN'] );
					$summe_einnahmen = 0;
					for($b = 0; $b < $anz_me; $b ++) {
						$summe_einnahmen += $my_arr [$a] ['IST_EINNAHMEN'] [$b] ['BETRAG'];
					}
					$my_arr [$a] ['IST_EINNAHMEN'] [$anz_me] ['BETRAG'] = '<b>' . nummer_punkt2komma_t ( $summe_einnahmen ) . '</b>';
					$my_arr [$a] ['IST_EINNAHMEN'] [$anz_me] ['VERWENDUNGSZWECK'] = '<b>Summe</b>';
					$my_arr [$a] ['BRUTTO_IST'] = $summe_einnahmen;
					// $my_arr[$a]['SUM_EIN_AUS_MIETE'] =
					
					$pdf_tab [$a] ['MIETER_SALDO'] = $my_arr [$a] ['MIETER_SALDO'];
					$pdf_tab [$a] ['EIGENTUEMER_NAMEN'] = $my_arr [$a] ['EIGENTUEMER_NAMEN'];
					$pdf_tab [$a] ['EINHEIT_KURZNAME'] = $my_arr [$a] ['EINHEIT_KURZNAME'];
					
					$pdf_tab [$a] ['EINHEIT_QM'] = $my_arr [$a] ['EINHEIT_QM'];
					$pdf_tab [$a] ['EINHEIT_QM_A'] = nummer_punkt2komma ( $my_arr [$a] ['EINHEIT_QM'] );
					$pdf_tab [$a] ['WEG-FLAECHE'] = $my_arr [$a] ['WEG-FLAECHE'];
					$pdf_tab [$a] ['WEG-FLAECHE_A'] = $my_arr [$a] ['WEG-FLAECHE_A'];
					$pdf_tab [$a] ['AUSZAHLUNG_ET'] = $my_arr [$a] ['AUSZAHLUNG_ET'];
					$pdf_tab [$a] ['MIETER'] = $my_arr [$a] ['MIETER'];
					/* Garantiemiete */
					$pdf_tab [$a] ['NETTO_SOLL_G_A'] = $my_arr [$a] ['NETTO_SOLL_G_A'];
					
					$pdf_tab [$a] ['BRUTTO_SOLL'] = $my_arr [$a] ['BRUTTO_SOLL'];
					$pdf_tab [$a] ['BRUTTO_SOLL_A'] = nummer_punkt2komma_t ( $my_arr [$a] ['BRUTTO_SOLL'] );
					$pdf_tab [$a] ['BRUTTO_IST'] = $my_arr [$a] ['BRUTTO_IST'];
					$pdf_tab [$a] ['BRUTTO_IST_A'] = nummer_punkt2komma_t ( $my_arr [$a] ['BRUTTO_IST'] );
					$pdf_tab [$a] ['DIFF'] = $my_arr [$a] ['BRUTTO_IST'] - $pdf_tab [$a] ['BRUTTO_SOLL'];
					$pdf_tab [$a] ['DIFF_A'] = nummer_punkt2komma_t ( $my_arr [$a] ['BRUTTO_IST'] - $pdf_tab [$a] ['BRUTTO_SOLL'] );
					
					$pdf_tab [$a] ['NETTO_SOLL'] = $my_arr [$a] ['NETTO_SOLL'];
					$pdf_tab [$a] ['NETTO_SOLL_MV'] = $my_arr [$a] ['NETTO_SOLL_MV'];
					$pdf_tab [$a] ['NETTO_SOLL_DIFF'] = $pdf_tab [$a] ['NETTO_SOLL'] - $pdf_tab [$a] ['NETTO_SOLL_MV'];
					$pdf_tab [$a] ['NETTO_SOLL_A'] = nummer_punkt2komma_t ( $my_arr [$a] ['NETTO_SOLL'] );
					// echo '<pre>';
					// print_r($my_arr);
					// die();
					if (empty ( $my_arr [$a] ['WEG-FLAECHE'] )) {
						$pdf_tab [$a] ['ABGABE_IHR'] = $pdf_tab [$a] ['EINHEIT_QM'] * - 0.4;
						// die('SSS');
					} else {
						$pdf_tab [$a] ['ABGABE_IHR'] = $my_arr [$a] ['WEG-FLAECHE'] * - 0.4;
						// die('OKOKOK');
					}
					$pdf_tab [$a] ['ABGABE_IHR_A'] = nummer_punkt2komma ( $pdf_tab [$a] ['ABGABE_IHR'] );
					$pdf_tab [$a] ['ABGABE_HV'] = '-30.00';
					$pdf_tab [$a] ['ABGABE_HV_A'] = '-30,00';
					$pdf_tab [$a] ['ZWISCHENSUMME'] = $my_arr [$a] ['NETTO_SOLL'] + $pdf_tab [$a] ['ABGABE_IHR'] + $pdf_tab [$a] ['ABGABE_HV'];
					
					$pdf_tab [$a] ['ZWISCHENSUMME_A'] = nummer_punkt2komma ( $my_arr [$a] ['NETTO_SOLL'] + $pdf_tab [$a] ['ABGABE_IHR'] + $pdf_tab [$a] ['ABGABE_HV'] );
					// if(nummer_komma2punkt($pdf_tab[$a]['ZWISCHENSUMME_A'])<0.00){
					// $pdf_tab[$a]['ZWISCHENSUMME_A'] ='0,00';
					// }
					
					$pdf_tab [$a] ['SUMME_REP'] = $summe_rep;
					$pdf_tab [$a] ['SUMME_REP_A'] = nummer_punkt2komma ( $summe_rep );
					$pdf_tab [$a] ['ENDSUMME'] = $pdf_tab [$a] ['ZWISCHENSUMME'];
					$pdf_tab [$a] ['ENDSUMME_A'] = $pdf_tab [$a] ['ZWISCHENSUMME_A'];
					
					// $pdf_tab[$a]['ENDSUMME'] = $pdf_tab[$a]['ZWISCHENSUMME'] + $summe_rep;
					// $pdf_tab[$a]['ENDSUMME_A'] = '<b>'.nummer_punkt2komma_t($pdf_tab[$a]['ZWISCHENSUMME'] + $summe_rep).'</b>';
					
					// $pdf_tab[$a]['EIG_AUSZAHLUNG'] = $this->get_kosten_arr('EINHEIT', $my_arr[$a]['EINHEIT_ID'], $monat, $jahr, $gk->geldkonto_id,80001);
					
					$e_nam = $pdf_tab [$a] ['EIGENTUEMER_NAMEN'];
					$ein_nam = $pdf_tab [$a] ['EINHEIT_KURZNAME'];
					
					/* �bersichtstabelle */
					$uebersicht [$a] ['EINHEIT_KURZNAME'] = $ein_nam;
					$uebersicht [$a] ['EIGENTUEMER_NAMEN'] = $e_nam;
					$uebersicht [$a] ['EIG_ID'] = $eige_id;
					$uebersicht [$a] ['MIETER'] = $pdf_tab [$a] ['MIETER'];
					$uebersicht [$a] ['MIETER_SALDO'] = $pdf_tab [$a] ['MIETER_SALDO'];
					$uebersicht [$a] ['MIETER_SALDO_A'] = nummer_punkt2komma ( $pdf_tab [$a] ['MIETER_SALDO'] );
					$uebersicht [$a] ['NETTO_SOLL_G_A'] = $pdf_tab [$a] ['NETTO_SOLL_G_A'];
					$uebersicht [$a] ['NETTO_SOLL_A'] = nummer_punkt2komma ( $pdf_tab [$a] ['NETTO_SOLL_MV'] );
					$uebersicht [$a] ['NETTO_SOLL_DIFF_A'] = nummer_punkt2komma ( $pdf_tab [$a] ['NETTO_SOLL_DIFF'] );
					$uebersicht [$a] ['ABGABE_HV_A'] = $pdf_tab [$a] ['ABGABE_HV_A'];
					$uebersicht [$a] ['ABGABE_IHR'] = $pdf_tab [$a] ['ABGABE_IHR_A'];
					
					$uebersicht [$a] ['SUMME_REP'] = $pdf_tab [$a] ['SUMME_REP'];
					$uebersicht [$a] ['SUMME_REP_A'] = $pdf_tab [$a] ['SUMME_REP_A'];
					
					$uebersicht [$a] ['WEG-FLAECHE_A'] = $pdf_tab [$a] ['WEG-FLAECHE_A'];
					$uebersicht [$a] ['EINHEIT_QM_A'] = $pdf_tab [$a] ['EINHEIT_QM_A'];
					
					$uebersicht [$a] ['ENDSUMME_A'] = $pdf_tab [$a] ['ENDSUMME_A'];
					// $summe_alle_eigentuemer += $pdf_tab[$a]['ENDSUMME'];
					$uebersicht [$a] ['TRANSFER'] = $pdf_tab [$a] ['ENDSUMME'] + $pdf_tab [$a] ['SUMME_REP'];
					$uebersicht [$a] ['TRANSFER_A'] = nummer_punkt2komma_t ( $pdf_tab [$a] ['ENDSUMME'] + $pdf_tab [$a] ['SUMME_REP'] );
					/*
					 * $trans_tab['ENDSUMME_A'] = $pdf_tab[$a]['ENDSUMME_A'];
					 * $trans_tab['SUMME_REP_A'] = $uebersicht[$a]['SUMME_REP_A'];
					 * $trans_tab['TRANSFER'] = $uebersicht[$a]['TRANSFER'];
					 * $trans_tab['TRANSFER_A'] = $uebersicht[$a]['TRANSFER_A'];
					 */
					/*
					 * $summe_transfer += $uebersicht[$a]['TRANSFER'];
					 *
					 * if($pdf_tab[$a]['ENDSUMME']>0){
					 * $summe_alle_eigentuemer += $pdf_tab[$a]['ENDSUMME'];
					 * }else{
					 * $summe_nachzahler += $pdf_tab[$a]['ENDSUMME'];
					 * }
					 */
					
					if (is_array ( $my_arr [$a] ['AUSGABEN'] )) {
						// $anzaa = count($my_arr[$a]['AUSGABEN']);
						array_unshift ( $my_arr [$a] ['AUSGABEN'], array (
								'DATUM' => ' ' 
						) );
						array_unshift ( $my_arr [$a] ['AUSGABEN'], array (
								'DATUM' => ' ' 
						) );
						
						// $my_arr[$a]['AUSGABEN'][$anzaa] = ' ';
						// $my_arr[$a]['AUSGABEN'][$anzaa+1] = ' ';
					} else {
					}
					
					// $cols = array('ENDSUMME_A'=>"Amount1", 'SUMME_REP_A'=>"Amount", 'TRANSFER_A'=>"Transfer");
					// $pdf->ezTable($trans_tab, $cols);
					// unset($trans_tab);
					$trans_tab [0] ['TEXT'] = "Amount [�]";
					$trans_tab [0] ['AM'] = $uebersicht [$a] ['ENDSUMME_A'];
					$trans_tab [1] ['TEXT'] = "Bills [�]";
					$trans_tab [1] ['AM'] = $uebersicht [$a] ['SUMME_REP_A'];
					$trans_tab [2] ['TEXT'] = "<b>Transfer [�]</b>";
					if ($uebersicht [$a] ['TRANSFER'] > 0) {
						$trans_tab [2] ['TEXT'] = "<b>Transfer [�]</b>";
						$trans_tab [2] ['AM'] = "<b>" . $uebersicht [$a] ['TRANSFER_A'] . "</b>";
					} else {
						$trans_tab [2] ['TEXT'] = "<b>Summary [�]</b>";
						$trans_tab [2] ['AM'] = "<b>" . $uebersicht [$a] ['TRANSFER_A'] . "</b>";
						$trans_tab [3] ['TEXT'] = "<b>Transfer [�]</b>";
						$trans_tab [3] ['AM'] = "<b>0,00</b>";
					}
					
					unset ( $trans_tab );
					
					unset ( $pdf_tab );
				}
			}
			/*
			 * $uebersicht[$anz]['EIGENTUEMER_NAMEN'] = 'Soll';
			 * $uebersicht[$anz]['ENDSUMME_A'] = nummer_punkt2komma_t($summe_alle_eigentuemer);
			 * $uebersicht[$anz+1]['EIGENTUEMER_NAMEN'] = 'Auszahlen';
			 * $uebersicht[$anz+1]['TRANSFER_A'] = nummer_punkt2komma_t($summe_transfer);
			 * #$uebersicht[$anz+1]['EIGENTUEMER_NAMEN'] = 'Zu erhalten';
			 * #$uebersicht[$anz+1]['ENDSUMME_A'] = nummer_punkt2komma_t($summe_nachzahler);
			 *
			 *
			 * #$pdf->ezTable($uebersicht, $cols,null, array('showHeadings'=>1,'shaded'=>1, 'titleFontSize' => 8, 'fontSize' => 7, 'xPos'=>50,'xOrientation'=>'right', 'width'=>500));
			 * #print_r($pdf_tab);
			 * # print_r($pdf_tab_soll);
			 * # echo '<pre>';
			 * # print_r($my_arr);
			 *
			 * #die();
			 *
			 *
			 * /*
			 * ob_clean(); //ausgabepuffer leeren
			 * header("Content-type: application/pdf"); // wird von MSIE ignoriert
			 * $pdf->ezStream();
			 */
			return $uebersicht;
		} else {
			die ( "Keine Einheiten im Objekt $objekt_id" );
		}
	}
	function get_kosten_arr($kos_typ, $kos_id, $monat, $jahr, $gk_id, $konto = null) {
		
		// echo "$kos_typ, $kos_id, $monat, $jahr, $gk_id, $konto=null <br>";
		if ($konto == null) {
			$db_abfrage = "SELECT * FROM `GELD_KONTO_BUCHUNGEN` WHERE `GELDKONTO_ID` = '$gk_id' AND DATE_FORMAT( `DATUM` , '%Y-%m' ) = '$jahr-$monat' AND `KOSTENTRAEGER_TYP` = '$kos_typ' AND `KOSTENTRAEGER_ID` = '$kos_id' AND `AKTUELL` = '1' ORDER BY KONTENRAHMEN_KONTO, DATUM";
		} else {
			$db_abfrage = "SELECT * FROM `GELD_KONTO_BUCHUNGEN` WHERE `GELDKONTO_ID` = '$gk_id' AND DATE_FORMAT( `DATUM` , '%Y-%m' ) = '$jahr-$monat' AND `KOSTENTRAEGER_TYP` = '$kos_typ' AND `KOSTENTRAEGER_ID` = '$kos_id' AND `AKTUELL` = '1' && KONTENRAHMEN_KONTO='$konto' ORDER BY KONTENRAHMEN_KONTO, DATUM";
		}
		// echo $db_abfrage;
		$result = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			while ( $row = mysql_fetch_assoc ( $result ) )
				$my_array [] = $row;
			return $my_array;
		}
	}
	function get_kosten_von_bis($kos_typ, $kos_id, $von, $bis, $gk_id, $konto = null) {
		
		// echo "$kos_typ, $kos_id, $monat, $jahr, $gk_id, $konto=null <br>";
		if ($konto == null) {
			$db_abfrage = "SELECT * FROM `GELD_KONTO_BUCHUNGEN` WHERE `GELDKONTO_ID` = '$gk_id' AND DATUM BETWEEN '$von' and '$bis' AND `KOSTENTRAEGER_TYP` = '$kos_typ' AND `KOSTENTRAEGER_ID` = '$kos_id' AND `AKTUELL` = '1'  ORDER BY KONTENRAHMEN_KONTO, DATUM";
		} else {
			$db_abfrage = "SELECT * FROM `GELD_KONTO_BUCHUNGEN` WHERE `GELDKONTO_ID` = '$gk_id' AND DATUM BETWEEN '$von' and '$bis' AND `KOSTENTRAEGER_TYP` = '$kos_typ' AND `KOSTENTRAEGER_ID` = '$kos_id' AND `AKTUELL` = '1' && KONTENRAHMEN_KONTO='$konto' ORDER BY KONTENRAHMEN_KONTO, DATUM";
		}
		// echo $db_abfrage;
		$result = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		$numrows = mysql_numrows ( $result );
		$sum = 0.00;
		if ($numrows) {
			
			while ( $row = mysql_fetch_assoc ( $result ) ) {
				$my_array [] = $row;
				$sum += $row ['BETRAG'];
			}
		}
		$my_array [] ['BETRAG'] = nummer_komma2punkt ( nummer_punkt2komma ( $sum ) );
		return $my_array;
	}
	function get_kosten_von_bis_o_sum($kos_typ, $kos_id, $von, $bis, $gk_id, $konto, $sort = 'ASC') {
		
		// echo "$kos_typ, $kos_id, $monat, $jahr, $gk_id, $konto=null <br>";
		$db_abfrage = "SELECT * FROM `GELD_KONTO_BUCHUNGEN` WHERE `GELDKONTO_ID` = '$gk_id' AND DATUM BETWEEN '$von' and '$bis' AND `KOSTENTRAEGER_TYP` = '$kos_typ' AND `KOSTENTRAEGER_ID` = '$kos_id' AND `AKTUELL` = '1' && KONTENRAHMEN_KONTO='$konto' ORDER BY KONTENRAHMEN_KONTO, DATUM $sort";
		
		// echo $db_abfrage;
		$result = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		$numrows = mysql_numrows ( $result );
		
		if ($numrows) {
			
			while ( $row = mysql_fetch_assoc ( $result ) ) {
				$my_array [] = $row;
			}
			return $my_array;
		}
	}
	function get_kosten_arr_all($kos_typ, $kos_id, $gk_id, $konto = null) {
		
		// echo "$kos_typ, $kos_id, $monat, $jahr, $gk_id, $konto=null <br>";
		if ($konto == null) {
			$db_abfrage = "SELECT * FROM `GELD_KONTO_BUCHUNGEN` WHERE `GELDKONTO_ID` = '$gk_id' AND  `KOSTENTRAEGER_TYP` = '$kos_typ' AND `KOSTENTRAEGER_ID` = '$kos_id' AND `AKTUELL` = '1' ORDER BY KONTENRAHMEN_KONTO, DATUM DESC";
		} else {
			$db_abfrage = "SELECT * FROM `GELD_KONTO_BUCHUNGEN` WHERE `GELDKONTO_ID` = '$gk_id' AND  `KOSTENTRAEGER_TYP` = '$kos_typ' AND `KOSTENTRAEGER_ID` = '$kos_id' AND `AKTUELL` = '1' && KONTENRAHMEN_KONTO='$konto' ORDER BY KONTENRAHMEN_KONTO, DATUM DESC";
		}
		// echo $db_abfrage;
		$result = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			while ( $row = mysql_fetch_assoc ( $result ) )
				$my_array [] = $row;
			return $my_array;
		}
	}
	function get_kosten_summe_jahr($kos_typ, $kos_id, $gk_id, $jahr, $konto = null) {
		if ($konto == null) {
			$db_abfrage = "SELECT SUM(BETRAG) AS SUMME FROM `GELD_KONTO_BUCHUNGEN` WHERE `GELDKONTO_ID` = '$gk_id' AND  `KOSTENTRAEGER_TYP` = '$kos_typ' AND `KOSTENTRAEGER_ID` = '$kos_id' AND `AKTUELL` = '1' AND DATE_FORMAT( `DATUM` , '%Y' ) = '$jahr'";
		} else {
			$db_abfrage = "SELECT SUM(BETRAG) AS SUMME FROM `GELD_KONTO_BUCHUNGEN` WHERE `GELDKONTO_ID` = '$gk_id' AND  `KOSTENTRAEGER_TYP` = '$kos_typ' AND `KOSTENTRAEGER_ID` = '$kos_id' AND `AKTUELL` = '1' && KONTENRAHMEN_KONTO='$konto' AND DATE_FORMAT( `DATUM` , '%Y' ) = '$jahr'";
		}
		// echo $db_abfrage;
		$result = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			$row = mysql_fetch_assoc ( $result );
			return $row ['SUMME'];
		} else {
			return '0.00';
		}
	}
	function get_kosten_summe_monat($kos_typ, $kos_id, $gk_id, $jahr, $monat, $konto = null) {
		// echo "$kos_typ, $kos_id, $gk_id, $jahr, $monat, $konto";
		if ($konto == null) {
			$db_abfrage = "SELECT SUM(BETRAG) AS SUMME FROM `GELD_KONTO_BUCHUNGEN` WHERE `GELDKONTO_ID` = '$gk_id' AND  `KOSTENTRAEGER_TYP` = '$kos_typ' AND `KOSTENTRAEGER_ID` = '$kos_id' AND `AKTUELL` = '1' AND DATE_FORMAT( `DATUM` , '%Y-%m' ) = '$jahr-$monat'";
		} else {
			$db_abfrage = "SELECT SUM(BETRAG) AS SUMME FROM `GELD_KONTO_BUCHUNGEN` WHERE `GELDKONTO_ID` = '$gk_id' AND  `KOSTENTRAEGER_TYP` = '$kos_typ' AND `KOSTENTRAEGER_ID` = '$kos_id' AND `AKTUELL` = '1' && KONTENRAHMEN_KONTO='$konto' AND DATE_FORMAT( `DATUM` , '%Y-%m' ) = '$jahr-$monat'";
		}
		// echo $db_abfrage."<br>";
		// die();
		$result = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			$row = mysql_fetch_assoc ( $result );
			return $row ['SUMME'];
		} else {
			return '0.00';
		}
	}
	function bilanz1($objekt_id = '41', $start_m = '01', $start_j = '2013', $garantie_m = '6', $hvg = '30.00', $ihr_m2 = '0.40', $akt_monat = null) {
		if ($akt_monat == null) {
			$akt_monat = date ( "m" );
		}
		$db_abfrage = "SELECT OBJEKT_KURZNAME, HAUS_STRASSE, HAUS_NUMMER, `EINHEIT_KURZNAME` , `EINHEIT_ID`,  ltrim(rtrim(EINHEIT_LAGE)) AS EINHEIT_LAGE, `EINHEIT_QM` FROM EINHEIT , HAUS, OBJEKT
WHERE `EINHEIT_AKTUELL` = '1' && EINHEIT.HAUS_ID = HAUS.HAUS_ID && HAUS.OBJEKT_ID=OBJEKT.OBJEKT_ID && HAUS_AKTUELL='1' && OBJEKT_AKTUELL='1' && OBJEKT.OBJEKT_ID='$objekt_id' 
ORDER BY EINHEIT_KURZNAME";
		echo $db_abfrage;
		$result = mysql_query ( $db_abfrage );
		$numrows = mysql_numrows ( $result );
		echo $numrows;
		if ($numrows) {
			$z = 0;
			while ( $row = mysql_fetch_assoc ( $result ) ) {
				$my_arr [] = $row;
				$einheit_id = $row ['EINHEIT_ID'];
				$e = new einheit ();
				$det = new detail ();
				$my_arr [$z] ['WEG-FLAECHE_A'] = $det->finde_detail_inhalt ( 'EINHEIT', $einheit_id, 'WEG-Fl�che' ); // kommt als Kommazahl
				$my_arr [$z] ['WEG-FLAECHE'] = nummer_komma2punkt ( $my_arr [$z] ['WEG-FLAECHE_A'] );
				
				/* IHR */
				$my_arr [$z] [$akt_monat . 'IHR'] = $akt_monat * nummer_komma2punkt ( $my_arr [$z] ['WEG-FLAECHE_A'] ) * $ihr_m2;
				$my_arr [$z] [$akt_monat . 'IHR_A'] = nummer_punkt2komma ( $my_arr [$z] [$akt_monat . 'IHR'] );
				/* HV */
				$my_arr [$z] [$akt_monat . 'HV'] = $akt_monat * $hvg;
				$my_arr [$z] [$akt_monat . 'HV_A'] = nummer_punkt2komma ( $my_arr [$z] [$akt_monat . 'HV'] );
				/* HV BERLUS */
				$my_arr [$z] [$akt_monat . 'HV_BERLUS'] = $akt_monat * ($hvg - 15.01);
				$my_arr [$z] [$akt_monat . 'HV_BERLUS_A'] = nummer_punkt2komma ( $my_arr [$z] [$akt_monat . 'HV_BERLUS'] );
				/* HV INS */
				$my_arr [$z] [$akt_monat . 'HV_INS'] = $akt_monat * ($hvg - 14.99);
				$my_arr [$z] [$akt_monat . 'HV_INS_A'] = nummer_punkt2komma ( $my_arr [$z] [$akt_monat . 'HV_INS'] );
				
				/* Garantiemiete kalt */
				$net_ren_garantie_a = $det->finde_detail_inhalt ( 'EINHEIT', $einheit_id, 'WEG-KaltmieteINS' ); // kommt als Kommazahl
				$net_ren_garantie = nummer_komma2punkt ( $net_ren_garantie_a );
				$my_arr [$z] ['KM_GARANTIE'] = $garantie_m * $net_ren_garantie;
				
				$weg = new weg ();
				$weg->get_last_eigentuemer ( $einheit_id );
				if (isset ( $weg->eigentuemer_name )) {
					// echo '<pre>';
					// print_r($weg);
					// $my_arr[$z]['EIGENTUEMER'] = $weg->eigentuemer_name;
					$weg->get_eigentuemer_namen ( $weg->eigentuemer_id );
					$my_arr [$z] ['EIGENTUEMER_NAMEN'] = $weg->eigentuemer_name_str_u;
					$my_arr [$z] ['EIGENTUEMER_ID'] = $weg->eigentuemer_id;
				} else {
					$my_arr [$z] ['EIGENTUEMER'] = 'Unbekannt';
				}
				$mv_id = $e->get_mietvertrag_id ( $einheit_id );
				if ($mv_id) {
					$mvs = new mietvertraege ();
					$mvs->get_mietvertrag_infos_aktuell ( $mv_id );
					$kontaktdaten = $e->kontaktdaten_mieter ( $mv_id );
					// $my_arr[$z]['MIETER'] = $mvs->personen_name_string_u."\n".$kontaktdaten;
					$my_arr [$z] ['MIETER'] = $mvs->personen_name_string_u;
					$my_arr [$z] ['MIETVERTRAG_ID'] = $mv_id;
					$my_arr [$z] ['KONTAKT'] = $kontaktdaten;
					$my_arr [$z] ['MIETVERTRAG_ID'] = $mv_id;
					$mz = new miete ();
					$mz->mietkonto_berechnung ( $mv_id );
					$my_arr [$z] ['MIETER_SALDO'] = $mz->erg;
				} else {
					$my_arr [$z] ['MIETER'] = 'Leerstand';
				}
				$z ++;
			}
			echo '<pre>';
			// print_r($my_arr);
			
			$leer_jahr = array ();
			for($a = $garantie_m + 1; $a <= 12; $a ++) {
				$le = new leerstand ();
				$monat_zweistellig = sprintf ( '%02d', $a );
				$leer_jahr [$a] = $le->leerstand_finden_monat ( $objekt_id, "$start_j-$monat_zweistellig-01" );
				
				// print_r($le->leerstand_finden_monat($objekt_id, "$start_j-$monat_zweistellig-01"));
			} // end for
			print_r ( $leer_jahr );
		}
	}
	function mieten_pdf($objekt_id, $datum_von, $datum_bis) {
		$mv = new mietvertraege ();
		$arr = $mv->mv_arr_zeitraum ( $objekt_id, $datum_von, $datum_bis );
		if (! is_array ( $arr )) {
			die ( 'NISTA' );
		} else {
			echo "<pre>";
			// print_r($arr);
			$anz_mvs = count ( $arr );
			$mz = new miete ();
			$monate = $mz->diff_in_monaten ( $datum_von, $datum_bis );
			$datum_von_arr = explode ( '-', $datum_von );
			$start_m = $datum_von_arr [1];
			$start_j = $datum_von_arr [0];
			
			$datum_bis_arr = explode ( '-', $datum_bis );
			$end_m = $datum_bis_arr [1];
			$end_j = $datum_bis_arr [0];
			
			/* Schleife f�r jeden Monat */
			$monat = $start_m;
			$jahr = $start_j;
			$summe_g = 0.00;
			for($a = 0; $a < $monate; $a ++) {
				$monat = sprintf ( '%02d', $monat );
				for($b = 0; $b < $anz_mvs; $b ++) {
					$mv_id = $arr [$b] ['MIETVERTRAG_ID'];
					// echo "$monat.$jahr = $mv_id<br>";
					// $n_arr[$b]['MV_ID']=$mv_id;
					// $mk = new mietkonto();
					$mv = new mietvertraege ();
					$mv->get_mietvertrag_infos_aktuell ( $mv_id );
					$n_arr [$b] ['EINHEIT'] = $mv->einheit_kurzname;
					$n_arr [$b] ['EINHEIT_ID'] = $mv->einheit_id;
					
					$einheit_qm = $mv->einheit_qm;
					$det = new detail ();
					$weg_qm = $det->finde_detail_inhalt ( 'EINHEIT', $mv->einheit_id, 'WEG-Fl�che' ); // kommt als Kommazahl
					if (! empty ( $weg_qm )) {
						$einheit_qm = nummer_komma2punkt ( $weg_qm );
					}
					
					$n_arr [$b] ['TYP'] = $mv->einheit_typ;
					$n_arr [$b] ['MIETER'] = $mv->personen_name_string;
					if ($mv->mietvertrag_bis_d == '00.00.0000') {
						$mv->mietvertrag_bis_d = '';
					}
					$n_arr [$b] ['MIETZEIT'] = "$mv->mietvertrag_von_d - $mv->mietvertrag_bis_d";
					$mietsumme = 0.00;
					$mietsumme = $mv->summe_forderung_monatlich ( $mv_id, $monat, $jahr );
					// die($mietsumme);
					$n_arr [$b] ["$monat.$jahr"] = $mietsumme;
					
					$n_arr [$b] ["$monat.$jahr" . '_IHR'] = $einheit_qm * 0.40;
					$n_arr [$b] ["$monat.$jahr" . '_IHR_A'] = nummer_punkt2komma ( $einheit_qm * 0.40 );
					
					$n_arr [$b] ["$monat.$jahr" . '_HV'] = 30.00;
					$n_arr [$b] ["$monat.$jahr" . '_HV_A'] = nummer_punkt2komma ( 30.00 );
					$n_arr [$b] ["$monat.$jahr" . '_AUS'] = $mietsumme - $n_arr [$b] ["$monat.$jahr" . '_IHR'] - $n_arr [$b] ["$monat.$jahr" . '_HV'];
					$n_arr [$b] ["$monat.$jahr" . '_AUS_A'] = nummer_punkt2komma ( $n_arr [$b] ["$monat.$jahr" . '_AUS'] );
					
					$n_arr [$b] ["SUMME"] += $mietsumme;
					$summe_g += $mietsumme;
					$sum = $n_arr [$b] ["SUMME"];
					$n_arr [$b] ["SUMME"] = number_format ( $sum, 2, '.', '' );
					$n_arr [$b] ["SUMME_A"] = nummer_punkt2komma_t ( $sum );
					
					// 1234.57
				}
				// $n_arr[$anz_mvs]["$monat.$jahr"] += $n_arr[$a]["$monat.$jahr"];
				$cols ["$monat.$jahr"] = "$monat.$jahr";
				
				$monat ++;
				$monat = sprintf ( '%02d', $monat );
				
				if ($monat > 12) {
					$monat = 1;
					$jahr ++;
				}
			}
			// print_r($n_arr);
			
			ob_clean (); // ausgabepuffer leeren
			include_once ('pdfclass/class.ezpdf.php');
			include_once ('classes/class_bpdf.php');
			$pdf = new Cezpdf ( 'a4', 'landscape' );
			$bpdf = new b_pdf ();
			$bpdf->b_header ( $pdf, 'Partner', $_SESSION [partner_id], 'landscape', 'pdfclass/fonts/Helvetica.afm', 6 );
			
			$count = count ( $n_arr );
			$n_arr [$anz_mvs] ['SUMME_A'] = "<b>" . nummer_punkt2komma_t ( $summe_g ) . "</b>";
			$n_arr [$anz_mvs] ['MIETER'] = "<b>Gesamt Sollmieten Nettokalt</b>";
			
			ob_clean (); // ausgabepuffer leeren
			            // $cols = array('MIETER'=>"MIETER", 'MIETER'=>"Mieter",'EINZUG'=>"Einzug",'AUSZUG'=>"Auszug"
			            // ,'BETRIEBSKOSTEN'=>"Betriebskosten $jahr", 'HEIZKOSTEN'=>"Heizkosten $jahr");
			$datum_h = date ( "d.m.Y" );
			$cols1 ['EINHEIT'] = 'Einheit';
			$cols1 ['TYP'] = 'Typ';
			$cols1 ['MIETER'] = 'Mieter';
			$cols1 ['MIETZEIT'] = 'Mietzeit';
			/*
			 * $cols1['08.2013'] = '08.2013';
			 * $cols1['08.2013_IHR_A'] = '08 IHR';
			 * $cols1['08.2013_HV_A'] = '08 HV';
			 * $cols1['08.2013_AUS_A'] = 'AUSZAHLUNG';
			 */
			
			// echo '<pre>';
			// print_r($n_arr);
			// die();
			$monat = $start_m;
			for($a = 0; $a < $monate; $a ++) {
				$monat = sprintf ( '%02d', $monat );
				$cols1 ["$monat.$start_j"] = "$monat.$start_j";
				$cols1 ["$monat.$start_j" . "_IHR_A"] = "IHR";
				$cols1 ["$monat.$start_j" . "_HV_A"] = "HV";
				$cols1 ["$monat.$start_j" . "_AUS_A"] = "AUS $monat";
				$monat ++;
			}
			
			$cols1 ['SUMME_A'] = 'BETRAG';
			
			// $pdf->ezTable($n_arr,$cols1,"Nebenkostenhochrechnung f�r das Jahr $jahr vom $datum_h",array('showHeadings'=>1,'shaded'=>1, 'titleFontSize' => 8, 'fontSize' => 7, 'xPos'=>50,'xOrientation'=>'right', 'width'=>500,'cols'=>array('EINHEIT'=>array('justification'=>'left', 'width'=>75),'MIETER'=>array('justification'=>'left', 'width'=>175), 'EINZUG'=>array('justification'=>'right','width'=>50),'AUSZUG'=>array('justification'=>'right','width'=>50),'BETRIEBSKOSTEN'=>array('justification'=>'right','width'=>75), 'HEIZKOSTEN'=>array('justification'=>'right','width'=>75))));
			$datum_von_d = date_mysql2german ( $datum_von );
			$datum_bis_d = date_mysql2german ( $datum_bis );
			// $pdf->ezTable($n_arr, $cols1, "Vereinbarte Sollkaltmieten im Zeitraum: $datum_von_d - $datum_bis_d", array('showHeadings'=>1,'shaded'=>1, 'width'=>500, 'titleFontSize' => 8, 'fontSize' => 7, 'xPos'=>50,'xOrientation'=>'right', 'cols'=>array('SUMME_A'=>array('justification'=>'right'))));
			// sort($n_arr);
			$pdf->ezTable ( $n_arr, $cols1, "Vereinbarte Sollkaltmieten im Zeitraum: $datum_von_d - $datum_bis_d", array (
					'showHeadings' => 1,
					'shaded' => 1,
					'titleFontSize' => 8,
					'fontSize' => 6.5,
					'xPos' => 50,
					'xOrientation' => 'right',
					'cols' => array (
							'SUMME_A' => array (
									'justification' => 'right' 
							) 
					) 
			) );
			ob_clean (); // ausgabepuffer leeren
			            // echo '<pre>';
			            // print_r($n_arr);
			            // die();
			$pdf->ezSetDy ( - 20 );
			$pdf->ezText ( "     Druckdatum: " . date ( "d.m.Y" ), 11 );
			header ( "Content-type: application/pdf" ); // wird von MSIE ignoriert
			
			$pdf->ezStream ();
		}
	}
	function bilanz($objekt_id = '41', $start_m = '01', $start_j = '2013', $garantie_m = '6', $hvg = '30.00', $ihr_m2 = '0.40', $akt_monat = null) {
		if ($akt_monat == null) {
			$akt_monat = date ( "m" );
		}
		
		/* Alle Monate durchlaufen */
		$o = new objekt ();
		$einheit_arr = $o->einheiten_objekt_arr ( $objekt_id );
		echo '<pre>';
		print_r ( $einheit_arr );
		/*
		 * [OBJEKT_KURZNAME] => MS
		 * [EINHEIT_ID] => 897
		 * [EINHEIT_KURZNAME] => MS7-211 (apt. 12)
		 * [EINHEIT_LAGE] => V4R
		 * [EINHEIT_QM] => 125.00
		 * [HAUS_STRASSE] => M�ggelstr.
		 * [HAUS_NUMMER] => 7
		 */
		
		for($a = 1; $a <= $akt_monat; $a ++) {
			$einheit_id = $einheit_arr [$a] ['EINHEIT_ID'];
			$einheit_kn = $einheit_arr [$a] ['EINHEIT_KURZNAME'];
			$einheit_qm = $einheit_arr [$a] ['EINHEIT_QM'];
			echo "$einheit_kn $einheit_qm<br>";
		}
	}
	function saldo_berechnung_et($einheit_id) {
		$pdf = new Cezpdf ( 'a4', 'landscape' );
		$bpdf = new b_pdf ();
		$bpdf->b_header ( $pdf, 'Partner', $_SESSION ['partner_id'], 'landscape', 'pdfclass/fonts/Helvetica.afm', 6 );
		$pdf->ezStopPageNumbers ();
		
		echo '<pre>';
		echo "<p><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>";
		/* Infos zu Einheit */
		$e = new einheit ();
		$e->get_einheit_info ( $einheit_id );
		$gk = new geldkonto_info ();
		$gk->geld_konto_ermitteln ( 'OBJEKT', $e->objekt_id );
		
		/* OBJEKTDATEN */
		/* Garantiemonate Objekt */
		$d = new detail ();
		$garantie_mon_obj = $d->finde_detail_inhalt ( 'Objekt', $e->objekt_id, 'INS-Garantiemonate' );
		if ($garantie_mon_obj) {
			$this->tab ['GARANTIE_OBJ'] = $garantie_mon_obj;
		} else {
			$this->tab ['GARANTIE_OBJ'] = 0;
		}
		
		/* Garantierte Miete */
		/* Garantiemiete */
		$garantie_miete = nummer_komma2punkt ( $d->finde_detail_inhalt ( 'EINHEIT', $einheit_id, 'WEG-KaltmieteINS' ) );
		if ($garantie_miete) {
			$this->tab ['G_MIETE'] = $garantie_miete;
		} else {
			$this->tab ['G_MIETE'] = 0.00;
		}
		
		/* Nutzenlastenwechsel */
		$nl_datum = $d->finde_detail_inhalt ( 'Objekt', $e->objekt_id, 'Nutzen-Lastenwechsel' );
		/* Verwaltungs�bernahme */
		$vu_datum = $d->finde_detail_inhalt ( 'Objekt', $e->objekt_id, 'Verwaltungs�bernahme' );
		echo "GMU: $garantie_mon_obj NLW: $nl_datum VU: $vu_datum<br>";
		
		/* Alle Eigent�mer */
		$weg = new weg ();
		$et_arr = $weg->get_eigentuemer_arr ( $einheit_id );
		
		if (! is_array ( $et_arr )) {
			fehlermeldung_ausgeben ( "Keine Eigent�mer zu $e->einheit_kurzname" );
		} else {
			// print_r($et_arr);
			$anz_et = count ( $et_arr );
			echo "Eigent�meranzahl : $anz_et<hr>";
			/* Schleife f�r die ET */
			for($a = 0; $a < $anz_et; $a ++) {
				$et_id = $et_arr [$a] ['ID'];
				$weg->get_eigentumer_id_infos4 ( $et_id );
				
				/* Zeitraum ET */
				if ($weg->eigentuemer_bis = '0000-00-00') {
					$datum_bis = date ( "Y-m-d" );
				} else {
					$datum_bis = $weg->eigentuemer_bis;
				}
				
				/* Objekt WEG to ARRAY */
				$this->tab [$a] = ( array ) $weg;
				/* Monate f�r den ET */
				$monats_arr = $this->monats_array ( $weg->eigentuemer_von, $datum_bis );
				$this->tab [$a] ['MONATE'] = $monats_arr;
				
				/* MV im ZEITRAUM */
				$mv_et_arr = $this->get_mv_et_zeitraum_arr ( $einheit_id, $weg->eigentuemer_von, $datum_bis );
				$this->tab [$a] ['MVS'] = $mv_et_arr;
				
				/* Garantiemonate Eigentuemer */
				$d_et = new detail ();
				$garantie_mon_et = $d_et->finde_detail_inhalt ( 'EIGENTUEMER', $et_id, 'INS-Garantiemonate' );
				if ($garantie_mon_et) {
					$this->tab [$a] ['GARANTIE_ET'] = $garantie_mon_et;
				} else {
					$this->tab [$a] ['GARANTIE_ET'] = 0;
				}
			} // end for
			
			unset ( $weg );
			
			// print_r($this->tab);
			// die();
			// #####################PDF VORBEREITUNG################
			/* Bebuchte Konten finden */
			$bu = new buchen ();
			$kos_typs [] = "Eigentuemer";
			$kos_typs [] = "Einheit";
			$kos_ids [] = $et_id;
			$kos_ids [] = $einheit_id;
			$konten = $bu->get_bebuchte_konten ( $gk->geldkonto_id, $kos_typs, $kos_ids );
			// print_r($konten);
			// die();
			/*
			 * if(is_array($konten)){
			 * print_r($konten);
			 * die("KONTEN");
			 * }
			 */
			
			$anz_et = count ( $this->tab ) - 2;
			
			echo $anz_et;
			/* Schleife ET */
			
			for($a = 0; $a < $anz_et; $a ++) {
				$et_id = $this->tab [$a] ['eigentuemer_id'];
				$et_name = $this->tab [$a] ['empf_namen'];
				// $this->tab_pdf[$a]['eigentuemer_id'] = $et_id;
				
				if ($this->tab [$a] ['GARANTIE_ET'] > $this->tab ['GARANTIE_OBJ']) {
					$garantie_m = $this->tab [$a] ['GARANTIE_ET'];
				} else {
					$garantie_m = $this->tab ['GARANTIE_OBJ'];
				}
				
				$mon_arr = $this->tab [$a] ['MONATE'];
				$anz_monate = count ( $mon_arr );
				$anz_mvs = count ( $this->tab [$a] ['MVS'] );
				
				$zeile = 0;
				/* Summen */
				$sum_km_soll = 0;
				
				/* Zwischensummen */
				$sum_km_gm = 0; // Summe Garantiemiete
				$sum_km_diff_gm = 0; // Summe Garantiemiete INS DIFFERENZ
				$sum_soll_ausz_r = 0;
				$sum_soll_ausz_b = 0;
				$sum_ist_ausz = 0;
				
				$sum_b_konten = 0;
				$sum_ets = 0;
				$sum_hausgeld = 0;
				/* Schleife Monate */
				for($m = 0; $m < $anz_monate; $m ++) {
					
					$monat = $this->tab [$a] ['MONATE'] [$m] ['MONAT'];
					$jahr = $this->tab [$a] ['MONATE'] [$m] ['JAHR'];
					
					/* Garantiemiete versprochene */
					if ($m == '0') {
						$this->pdf_tab [$a] [$zeile] ['G_MIETE'] = 0;
					} else {
						$this->pdf_tab [$a] [$zeile] ['G_MIETE'] = $this->tab ['G_MIETE'];
						$sum_km_gm += $this->tab ['G_MIETE'];
					}
					/* Schleife Mietvertr�ge */
					for($mvs = 0; $mvs < $anz_mvs; $mvs ++) {
						$mv_id = $this->tab [$a] ['MVS'] [$mvs] ['MIETVERTRAG_ID'];
						
						$mk = new mietkonto ();
						$mk->kaltmiete_monatlich ( $mv_id, $monat, $jahr );
						
						if ($mk->ausgangs_kaltmiete) {
							/* Erste Zeile keine Volle Garantiemiete, sondern nur KM aus MDEF */
							if ($m == '0' && $zeile == 0 && $this->tab ['G_MIETE'] > 0) {
								$this->pdf_tab [$a] [$zeile] ['G_MIETE'] = $mk->ausgangs_kaltmiete;
								$sum_km_gm += $mk->ausgangs_kaltmiete;
							}
							
							$this->pdf_tab [$a] [$zeile] ['MONAT'] = $monat;
							$this->pdf_tab [$a] [$zeile] ['JAHR'] = $jahr;
							echo "SANEL $monat $jahr $mv_id $mk->ausgangs_kaltmiete<br>";
							// $this->pdf_tab[$a][$zeile]['MV_ID'.$mv_id] = $mv_id;
							$mv = new mietvertraege ();
							$mv->get_mietvertrag_infos_aktuell ( $mv_id );
							$this->pdf_tab [$a] [$zeile] ['MIETER'] = $mv->personen_name_string;
							$this->pdf_tab [$a] [$zeile] ['MV_ID'] = $mv_id;
							$this->pdf_tab [$a] [$zeile] ['KM_SOLL'] = $mk->ausgangs_kaltmiete;
							$sum_km_soll += $mk->ausgangs_kaltmiete;
							
							/* Mietersaldo Monat */
							$mz = new miete ();
							$mz->mietkonto_berechnung_monatsgenau ( $mv_id, $jahr, $monat );
							
							$gom = $this->pdf_tab [$a] [$zeile] ['G_MIETE']; // garatiemiete monat
							                                              // echo "$mk->ausgangs_kaltmiete < $gom";
							
							/* Wenn Garantiemiete > als Kaltmiete */
							if ($mk->ausgangs_kaltmiete < $gom) {
								// die("$mk->ausgangs_kaltmiete < $gom");
								$ins_km_diff = ($gom - $mk->ausgangs_kaltmiete);
								$this->pdf_tab [$a] [$zeile] ['G_DIFF_KM'] = nummer_komma2punkt ( nummer_punkt2komma ( $ins_km_diff ) );
								$sum_km_diff_gm += $ins_km_diff;
							} else {
								/* Keine Garantiemiete */
								// unset($this->pdf_tab[$a][$zeile]['G_MIETE']);
								// if($mz->erg<0 && $mz->geleistete_zahlungen<=0){
								$this->pdf_tab [$a] [$zeile] ['G_DIFF_KM'] = ($mz->erg * - 1);
								$sum_km_diff_gm = ($mz->erg * - 1);
								// }
							}
							
							// echo "$mk->ausgangs_kaltmiete + $mz->erg";
							// echo$mk->ausgangs_kaltmiete + $mz->erg;
							// die();
							
							$this->pdf_tab [$a] [$zeile] ['MTR_SLD'] = $mz->erg;
							$this->pdf_tab [$a] [$zeile] ['MTR_ZB'] = $mz->geleistete_zahlungen;
							$this->pdf_tab [$a] [$zeile] ['MTR_NK'] = $mz->davon_umlagen;
							
							/* Fixkosten Hausgeld oder Formel */
							$hg = new weg ();
							// $hausgeld_soll = $hg->get_summe_kostenkat_monat($monat, $jahr, 'Einheit', $einheit_id, 6030);
							// $hausgeld_soll = $hg->get_summe_kostenkat_gruppe_m2($monat, $jahr, 'Einheit', $einheit_id, 6000);
							$hg->get_wg_info ( $monat, $jahr, 'Einheit', $einheit_id, 'Hausgeld' );
							$hausgeld_soll = $hg->gruppe_erg;
							
							/* Fixkosten nach Formel */
							$hg->get_eigentumer_id_infos4 ( $et_id );
							$hausgeld_soll_f = ($hg->einheit_qm_weg * 0.4) + 30;
							if ($hausgeld_soll_f > $hausgeld_soll) {
								$hausgeld_soll = $hausgeld_soll_f;
							}
							
							$this->pdf_tab [$a] [$zeile] ['HG'] = nummer_komma2punkt ( nummer_punkt2komma ( $hausgeld_soll ) );
							$sum_hausgeld += nummer_komma2punkt ( nummer_punkt2komma ( $hausgeld_soll ) );
							
							/* Auszahlung Garantiezeit */
							if ($m < $garantie_m) {
								/* Auszahlung SOLL */
								if ($this->pdf_tab [$a] [$zeile] ['G_DIFF_KM'] > 0) {
									$this->pdf_tab [$a] [$zeile] ['SOLL_AUSZ_R'] = $this->pdf_tab [$a] [$zeile] ['G_MIETE'];
									$this->pdf_tab [$a] [$zeile] ['SOLL_AUSZ_B'] = $this->pdf_tab [$a] [$zeile] ['G_MIETE'] - $this->pdf_tab [$a] [$zeile] ['HG'];
								} else {
									$this->pdf_tab [$a] [$zeile] ['SOLL_AUSZ_R'] = $mk->ausgangs_kaltmiete;
									$this->pdf_tab [$a] [$zeile] ['SOLL_AUSZ_B'] = $mk->ausgangs_kaltmiete - $this->pdf_tab [$a] [$zeile] ['HG'];
								}
								/* Summen */
								$sum_soll_ausz_r += $this->pdf_tab [$a] [$zeile] ['SOLL_AUSZ_R'];
								$sum_soll_ausz_b += $this->pdf_tab [$a] [$zeile] ['SOLL_AUSZ_B'];
							} else {
								// #######################nach garantiE######################################
								/* Nach der Garantiezeit */
								if ($m == $garantie_m) {
									$this->pdf_tab [$a] [$zeile] ['MTR_SLD'] = 0.00;
								}
								
								if (isset ( $this->pdf_tab [$a] [$zeile] ['G_DIFF_KM'] )) {
									$ins_diff_monat = $this->pdf_tab [$a] [$zeile] ['G_DIFF_KM'];
								} else {
									$ins_diff_monat = 0.00;
								}
								
								/* Mietersaldo GUTHABEN ODER AUSGEGELICHEN */
								if ($mz->erg >= 0) {
									/* Keine Schulden im letzten MOnat */
									if ($mz->saldo_vormonat_stand >= 0) {
										$this->pdf_tab [$a] [$zeile] ['SOLL_AUSZ_R'] = $mk->ausgangs_kaltmiete + $ins_diff_monat;
										$this->pdf_tab [$a] [$zeile] ['SOLL_AUSZ_B'] = $mk->ausgangs_kaltmiete + $ins_diff_monat - $this->pdf_tab [$a] [$zeile] ['HG'] - $this->pdf_tab [$a] [$zeile] ['K_SUM'];
										;
									} else {
										/* Schulden im letzten MOnat */
										// $pdf_tab[$pdf_z]['KM_IST'] = $mi_arr['zb'] - $mi_arr['erg'] - $nk;
										$this->pdf_tab [$a] [$zeile] ['SOLL_AUSZ_R'] = $mk->ausgangs_kaltmiete + $ins_diff_monat;
										$this->pdf_tab [$a] [$zeile] ['SOLL_AUSZ_B'] = $mk->ausgangs_kaltmiete + $ins_diff_monat - $this->pdf_tab [$a] [$zeile] ['HG'] - $this->pdf_tab [$a] [$zeile] ['K_SUM'];
									}
									
									// if(($wm_soll*-1)<=0){
									// $pdf_tab[$pdf_z]['KM_IST'] = 0.00;
									// }
									
									// $sum_km_ist += $pdf_tab[$pdf_z]['KM_IST'];
								} /*
								   *
								   *
								   *
								   * /*Mietersaldo MINUS
								   */
								
								if ($mz->erg < 0) {
									
									/* Keine Schulden im letzten MOnat */
									if ($mz->saldo_vormonat_stand >= 0) {
										$this->pdf_tab [$a] [$zeile] ['SOLL_AUSZ_R'] = $mk->ausgangs_kaltmiete + $ins_diff_monat - $this->pdf_tab [$a] [$zeile] ['HG'];
										$this->pdf_tab [$a] [$zeile] ['SOLL_AUSZ_B'] = $mk->ausgangs_kaltmiete + $ins_diff_monat + $mz->erg - $this->pdf_tab [$a] [$zeile] ['HG'] - $this->pdf_tab [$a] [$zeile] ['K_SUM'];
										/* Schulden auch im letzten Monat */
									} else {
										
										/* Wenn MK abgezahlt, diff auszahlen */
										if (($mz->erg >= $mz->saldo_vormonat_stand)) {
											$this->pdf_tab [$a] [$zeile] ['SOLL_AUSZ_R'] = $mk->ausgangs_kaltmiete + $ins_diff_monat + $mz->erg + ($mz->saldo_vormonat * - 1);
											$this->pdf_tab [$a] [$zeile] ['SOLL_AUSZ_B'] = $mk->ausgangs_kaltmiete + $ins_diff_monat + $mz->erg + ($mz->saldo_vormonat * - 1) - $this->pdf_tab [$a] [$zeile] ['HG'] - $this->pdf_tab [$a] [$zeile] ['K_SUM'];
											;
										} else {
											/* Wenn der Mieter noch mehr Schulden mach, keine AUSZ */
											
											/* Wenn �berhaupt was gezahlt und h�he als umlagen */
											if ($mz->geleistete_zahlungen > 0 && $mz->geleistete_zahlungen > $mz->davon_umlagen) {
												// $pdf_tab[$pdf_z]['KM_IST'] = $mi_arr['zb'] - $nk;
												$this->pdf_tab [$a] [$zeile] ['SOLL_AUSZ_R'] = $mz->geleistete_zahlungen + $ins_diff_monat - $mz->davon_umlagen + $mz->erg + ($mz->saldo_vormonat_stand * - 1);
												$this->pdf_tab [$a] [$zeile] ['SOLL_AUSZ_B'] = $mz->geleistete_zahlungen + $ins_diff_monat - $mz->davon_umlagen + $mz->erg + ($mz->saldo_vormonat_stand * - 1) - $this->pdf_tab [$a] [$zeile] ['HG'] - $this->pdf_tab [$a] [$zeile] ['K_SUM'];
											} else {
												/* Wenn nicht gezahlt */
												$this->pdf_tab [$a] [$zeile] ['SOLL_AUSZ_R'] = $ins_diff_monat - $this->pdf_tab [$a] [$zeile] ['HG'] - $this->pdf_tab [$a] [$zeile] ['K_SUM'];
												$this->pdf_tab [$a] [$zeile] ['SOLL_AUSZ_B'] = $ins_diff_monat - $this->pdf_tab [$a] [$zeile] ['HG'] - $this->pdf_tab [$a] [$zeile] ['K_SUM'];
											}
										}
									}
									
									// $sum_km_ist += $pdf_tab[$pdf_z]['KM_IST'];
								}
								
								// $this->pdf_tab[$a][$zeile]['SOLL_AUSZ_R'] = $mk->ausgangs_kaltmiete;
								// $this->pdf_tab[$a][$zeile]['SOLL_AUSZ_B'] = $mk->ausgangs_kaltmiete - $this->pdf_tab[$a][$zeile]['HG'];
								// }
								$sum_soll_ausz_r += $this->pdf_tab [$a] [$zeile] ['SOLL_AUSZ_R'];
								$sum_soll_ausz_b += $this->pdf_tab [$a] [$zeile] ['SOLL_AUSZ_B'];
							}
							
							/* Auszahlung IST */
							// $summe_auszahlung = nummer_komma2punkt(nummer_punkt2komma($this->get_kosten_summe_monat('Eigentuemer', $et_id, $gk->geldkonto_id, $jahr, $monat, 5020)));
							// $this->pdf_tab[$a][$zeile]['AUSZ_IST'] = $summe_auszahlung;
							// $sum_ist_ausz+=$summe_auszahlung;
							
							if (is_array ( $konten )) {
								$anz_konten = count ( $konten );
								$kost_sum = 0;
								for($ko = 0; $ko < $anz_konten; $ko ++) {
									$b_konto = $konten [$ko] ['KONTO'];
									$summe_temp_ein = $this->get_kosten_summe_monat ( 'Einheit', $einheit_id, $gk->geldkonto_id, $jahr, $monat, $b_konto );
									$summe_temp_et = $this->get_kosten_summe_monat ( 'Eigentuemer', $et_id, $gk->geldkonto_id, $jahr, $monat, $b_konto );
									$this->pdf_tab [$a] [$zeile] ['K' . $b_konto] = nummer_punkt2komma ( $summe_temp_ein + $summe_temp_et );
									$sum_b_konten += $summe_temp_ein + $summe_temp_et;
									$kost_sum += $summe_temp_ein + $summe_temp_et;
									// $this->pdf_tab[$a][$zeile]['K_SUM'] = nummer_komma2punkt(nummer_punkt2komma($summe_temp_ein+$summe_temp_et));
								}
								$this->pdf_tab [$a] [$zeile] ['K_SUM'] = nummer_komma2punkt ( nummer_punkt2komma ( $kost_sum ) );
							} else {
								// ##die('KEINE KONTEN');
							}
							
							/*
							 * KOstenblock
							 * $summe_kosten_mon = $this->get_kosten_summe_monat('Einheit', $einheit_id, $gk->geldkonto_id, $jahr, $monat, 1023);
							 * $this->pdf_tab[$a][$zeile]['K1023'] = nummer_punkt2komma($summe_kosten_mon);
							 *
							 * $summe_ins_mg = $this->get_kosten_summe_monat('Einheit', $einheit_id, $gk->geldkonto_id, $jahr, $monat, 5500);
							 * $this->pdf_tab[$a][$zeile]['INSMG'] = nummer_punkt2komma($summe_ins_mg);
							 *
							 *
							 * $summe_4180 = $this->get_kosten_summe_monat('Einheit', $einheit_id, $gk->geldkonto_id, $jahr, $monat, 4180);
							 * $this->pdf_tab[$a][$zeile]['K4180'] = nummer_punkt2komma($summe_4180);
							 *
							 * $summe_4280 = $this->get_kosten_summe_monat('Einheit', $einheit_id, $gk->geldkonto_id, $jahr, $monat, 4280);
							 * $this->pdf_tab[$a][$zeile]['K4280'] = nummer_punkt2komma($summe_4280);
							 *
							 *
							 * $summe_4281 = $this->get_kosten_summe_monat('Einheit', $einheit_id, $gk->geldkonto_id, $jahr, $monat, 4281);
							 * $this->pdf_tab[$a][$zeile]['K4281'] = nummer_punkt2komma($summe_4281);
							 *
							 * $summe_4282 = $this->get_kosten_summe_monat('Einheit', $einheit_id, $gk->geldkonto_id, $jahr, $monat, 4282);
							 * $this->pdf_tab[$a][$zeile]['K4282'] = nummer_punkt2komma($summe_4282);
							 *
							 * $summe_5081 = $this->get_kosten_summe_monat('Eigentuemer', $weg_et->eigentuemer_id, $gk->geldkonto_id, $jahr, $monat, 5081);
							 * $this->pdf_tab[$a][$zeile]['K5081'] = nummer_punkt2komma($summe_5081);
							 *
							 * $summe_5010 = $this->get_kosten_summe_monat('Eigentuemer', $weg_et->eigentuemer_id, $gk->geldkonto_id, $jahr, $monat, 5010);
							 * $this->pdf_tab[$a][$zeile]['K5010'] = nummer_punkt2komma($summe_5010);
							 */
							
							$this->pdf_tab [$a] [$zeile] ['ETS'] = nummer_komma2punkt ( nummer_punkt2komma ( $this->pdf_tab [$a] [$zeile] ['SOLL_AUSZ_B'] + $this->pdf_tab [$a] [$zeile] ['K_SUM'] ) );
							$sum_ets += $this->pdf_tab [$a] [$zeile] ['ETS'];
							$this->pdf_tab [$a] [$zeile] ['ETS_P'] = nummer_komma2punkt ( nummer_punkt2komma ( $this->pdf_tab [$a] [$zeile] ['ETS'] + $this->pdf_tab [$a] [$zeile - 1] ['ETS_P'] ) );
							
							// #########
							$zeile ++;
							unset ( $mk );
							unset ( $mv );
							
							/* Zwischensummen bilden */
							if ($m == $garantie_m - 1) {
								$this->pdf_tab [$a] [$zeile] ['MIETER'] = "<b>GARANTIE</b>";
								$this->pdf_tab [$a] [$zeile] ['KM_SOLL'] = "<b>" . nummer_punkt2komma_t ( $sum_km_soll ) . "</b>";
								$this->pdf_tab [$a] [$zeile] ['G_MIETE'] = "<b>" . nummer_punkt2komma_t ( $sum_km_gm ) . "</b>";
								$this->pdf_tab [$a] [$zeile] ['G_DIFF_KM'] = "<b>" . nummer_punkt2komma_t ( $sum_km_diff_gm ) . "</b>";
								$this->pdf_tab [$a] [$zeile] ['SOLL_AUSZ_R'] = "<b>" . nummer_punkt2komma_t ( $sum_soll_ausz_r ) . "</b>";
								$this->pdf_tab [$a] [$zeile] ['SOLL_AUSZ_B'] = "<b>" . nummer_punkt2komma_t ( $sum_soll_ausz_b ) . "</b>";
								$this->pdf_tab [$a] [$zeile] ['K_SUM'] = "<b>" . nummer_punkt2komma_t ( $sum_b_konten ) . "</b>";
								$this->pdf_tab [$a] [$zeile] ['MTR_SLD'] = "<b>" . nummer_punkt2komma_t ( $this->pdf_tab [$a] [$zeile - 1] ['MTR_SLD'] ) . "</b>";
								
								$this->pdf_tab [$a] [$zeile] ['HG'] = "<b>" . nummer_punkt2komma_t ( $sum_hausgeld ) . "</b>";
								$this->pdf_tab [$a] [$zeile] ['ETS_P'] = "<b>" . $this->pdf_tab [$a] [$zeile] ['ETS'] + $this->pdf_tab [$a] [$zeile - 1] ['ETS_P'] . "</b>";
								
								$this->ins_anteil_km_6 = $this->pdf_tab [$a] [$zeile - 1] ['MTR_SLD'];
								$this->ins_anteil_ets_p = $this->pdf_tab [$a] [$zeile - 1] ['ETS_P'];
								$this->ins_gm_6 = $sum_km_diff_gm;
								$this->sum_ins_nach_6 = $this->ins_anteil_km_6 + $this->ins_anteil_ets_p + $this->ins_gm_6;
								$pdf->ezText ( "$this->ins_anteil_km_6 $this->ins_anteil_ets_p = $this->sum_ins_nach_6" );
								//
								// $sum_ets=0;
								$zeile ++;
							}
							
							/* Zwischensummen nach Dezemer bilden */
							/*
							 * if($monat==12){
							 * $this->pdf_tab[$a][$zeile]['MIETER'] = "<b>$jahr</b>";
							 * $this->pdf_tab[$a][$zeile]['KM_SOLL'] = "<b>".nummer_punkt2komma_t($sum_km_soll)."</b>";
							 * $sum_km_soll = 0;
							 * $this->pdf_tab[$a][$zeile]['G_MIETE'] = "<b>".nummer_punkt2komma_t($sum_km_gm)."</b>";
							 * $sum_km_gm = 0;
							 * $this->pdf_tab[$a][$zeile]['G_DIFF_KM'] = "<b>".nummer_punkt2komma_t($sum_km_diff_gm)."</b>";
							 * $sum_km_diff_gm = 0;
							 * $this->pdf_tab[$a][$zeile]['SOLL_AUSZ_R'] = "<b>".nummer_punkt2komma_t($sum_soll_ausz_r)."</b>";
							 * $sum_soll_ausz_r = 0;
							 * $this->pdf_tab[$a][$zeile]['SOLL_AUSZ_B'] = "<b>".nummer_punkt2komma_t($sum_soll_ausz_b)."</b>";
							 * $sum_soll_ausz_b = 0;
							 * #$this->pdf_tab[$a][$zeile]['AUSZ_IST'] = "<b>".nummer_punkt2komma_t($sum_ist_ausz)."</b>";
							 *
							 * $this->pdf_tab[$a][$zeile]['K_SUM'] = "<b>".nummer_punkt2komma_t($sum_b_konten)."</b>";
							 * $sum_b_konten = 0;
							 * $this->pdf_tab[$a][$zeile]['HG'] = "<b>".nummer_punkt2komma_t($sum_hausgeld)."</b>";
							 * $sum_hausgeld = 0;
							 * $this->pdf_tab[$a][$zeile]['ETS_P'] = "<b>".$this->pdf_tab[$a][$zeile]['ETS'] + $this->pdf_tab[$a][$zeile-1]['ETS_P']."</b>";
							 * #
							 * #$this->pdf_tab[$a][$zeile]['ETS'] = "<b>".nummer_punkt2komma_t($sum_ets)."</b>";
							 * #$this->pdf_tab[$a][$zeile]['ETS_P'] = "<b>".nummer_punkt2komma_t($sum_soll_ausz_b + $sum_b_konten)."</b>";
							 * $zeile++;
							 * }
							 */
						}
						
						// $mz = new miete();
						// $m_arr =$mz->get_monats_ergebnis($mv_id, $monat,$jahr);
						// $this->tab[$a][]
					}
					
					// $zeile++;
				}
				
				$this->pdf_tab [$a] [$zeile] ['MIETER'] = "<b>AKTUELL</b>";
				$this->pdf_tab [$a] [$zeile] ['KM_SOLL'] = "<b>" . nummer_punkt2komma_t ( $sum_km_soll ) . "</b>";
				$this->pdf_tab [$a] [$zeile] ['G_MIETE'] = "<b>" . nummer_punkt2komma_t ( $sum_km_gm ) . "</b>";
				$this->pdf_tab [$a] [$zeile] ['G_DIFF_KM'] = "<b>" . nummer_punkt2komma_t ( $sum_km_diff_gm ) . "</b>";
				
				$this->pdf_tab [$a] [$zeile] ['SOLL_AUSZ_R'] = "<b>" . nummer_punkt2komma_t ( $sum_soll_ausz_r ) . "</b>";
				$this->pdf_tab [$a] [$zeile] ['SOLL_AUSZ_B'] = "<b>" . nummer_punkt2komma_t ( $sum_soll_ausz_b ) . "</b>";
				// $this->pdf_tab[$a][$zeile]['AUSZ_IST'] = "<b>".nummer_punkt2komma_t($sum_ist_ausz)."</b>";
				$this->pdf_tab [$a] [$zeile] ['K_SUM'] = "<b>" . nummer_punkt2komma_t ( $sum_b_konten ) . "</b>";
				// $this->pdf_tab[$a][$zeile]['ETS'] = "<b>".nummer_punkt2komma_t($sum_ets)."</b>";
				// $this->pdf_tab[$a][$zeile]['ETS_P'] = "<b>".nummer_punkt2komma_t($sum_soll_ausz_b + $sum_b_konten)."</b>";
				$this->pdf_tab [$a] [$zeile] ['ETS_P'] = "<b>" . $this->pdf_tab [$a] [$zeile] ['ETS'] + $this->pdf_tab [$a] [$zeile - 1] ['ETS_P'] . "</b>";
			}
			
			// print_r($this->pdf_tab);
			// die();
			// $pdf = new Cezpdf('a4', 'landscape');
			// $bpdf = new b_pdf;
			// $bpdf->b_header($pdf, 'Partner', $_SESSION['partner_id'], 'landscape', 'pdfclass/fonts/Helvetica.afm', 6);
			// $pdf->ezStopPageNumbers();
			
			$cols = array (
					'MON' => MONAT2,
					'WM_SOLL' => WM,
					'NK_SOLL' => NK,
					'ZB_M' => 'ZB_M',
					'SALDO_M' => 'SALDO_M',
					'KM_SOLL' => KM_SOLL,
					'KM_IST' => KM_IST,
					'KOST_ALLE' => 'KOST ALLE',
					'K5081' => 'VZN',
					'K5010' => 'EINZAHLUNG',
					'HG_Z' => 'HG ZAHLUNG',
					'SOLL_AUSZ' => 'AUSZ SOLL',
					'AUSZAHLUNG' => 'AUSZAHLUNG IST',
					'SALDO_MET' => 'SALDO M',
					'PERIOD' => 'PERIOD' 
			);
			// }
			
			for($a = 0; $a < $anz_et; $a ++) {
				$pdf->ezText ( "$et_name $et_id", 16 );
				$pdf->ezSetDy ( - 5 ); // abstand
				                   // $pdf->ezTable($this->pdf_tab[$a]);
				$pdf->ezTable ( $this->pdf_tab [$a], null, EINNAHMEN_REPORT . " $datum_von $datum_bis", array (
						'showHeadings' => 1,
						'shaded' => 1,
						'titleFontSize' => 10,
						'fontSize' => 7,
						'xPos' => 50,
						'xOrientation' => 'right',
						'width' => 750,
						'cols' => array (
								'IHR' => array (
										'justification' => 'right' 
								),
								'HV' => array (
										'justification' => 'right' 
								),
								'REP' => array (
										'justification' => 'right' 
								),
								'AUSZAHLUNG' => array (
										'justification' => 'right' 
								) 
						) 
				) );
			}
			
			/* Legende */
			if (is_array ( $konten )) {
				$kr = new kontenrahmen ();
				$kr_id = $kr->get_kontenrahmen ( 'GELDKONTO', $gk->geldkonto_id );
				
				$anz_konten = count ( $konten );
				$pdf->ezSetDy ( - 20 ); // abstand
				$string = '';
				for($ko = 0; $ko < $anz_konten; $ko ++) {
					$b_konto = $konten [$ko] ['KONTO'];
					$kr->konto_informationen2 ( $b_konto, $kr_id );
					$string .= "K$b_konto - $kr->konto_bezeichnung\n";
				}
				$pdf->ezText ( "<b>$string</b>", 9 );
			}
			
			unset ( $this->pdf_tab );
			unset ( $this->tab );
			unset ( $konten );
			
			ob_clean (); // ausgabepuffer leeren
			header ( "Content-type: application/pdf" ); // wird von MSIE ignoriert
			$pdf->ezStream ();
		}
	}
	function saldo_berechnung_et_DOBARpravo_pdf(&$pdf, $einheit_id) {
		/* Infos zu Einheit */
		$e = new einheit ();
		$e->get_einheit_info ( $einheit_id );
		$gk = new geldkonto_info ();
		$gk->geld_konto_ermitteln ( 'OBJEKT', $e->objekt_id );
		
		/* OBJEKTDATEN */
		/* Garantiemonate Objekt */
		$d = new detail ();
		$garantie_mon_obj = $d->finde_detail_inhalt ( 'Objekt', $e->objekt_id, 'INS-Garantiemonate' );
		if (! $garantie_mon_obj) {
			$garantie_mon_obj = 0;
		} else {
			$this->gmon_obj = $garantie_mon_obj;
		}
		
		/* Garantierte Miete */
		/* Garantiemiete */
		$garantie_miete = nummer_komma2punkt ( $d->finde_detail_inhalt ( 'EINHEIT', $einheit_id, 'WEG-KaltmieteINS' ) );
		if (! $garantie_miete) {
			$garantie_miete = 0;
		}
		/* Nutzenlastenwechsel */
		$nl_datum = $d->finde_detail_inhalt ( 'Objekt', $e->objekt_id, 'Nutzen-Lastenwechsel' );
		$nl_datum_arr = explode ( '.', $nl_datum );
		$nl_tag = $nl_datum_arr [0];
		$nl_monat = $nl_datum_arr [1];
		$nl_jahr = $nl_datum_arr [2];
		
		/* Verwaltungs�bernahme */
		$vu_datum = $d->finde_detail_inhalt ( 'Objekt', $e->objekt_id, 'Verwaltungs�bernahme' );
		$vu_datum_arr = explode ( '.', $vu_datum );
		$vu_tag = $vu_datum_arr [0];
		$vu_monat = $vu_datum_arr [1];
		$vu_jahr = $vu_datum_arr [2];
		
		echo "<h2>GMU: $garantie_mon_obj NLW: $nl_datum VU: $vu_datum</h2>";
		
		/* Alle Eigent�mer */
		$weg = new weg ();
		$et_arr = $weg->get_eigentuemer_arr ( $einheit_id );
		
		if (! is_array ( $et_arr )) {
			fehlermeldung_ausgeben ( "Keine Eigent�mer zu $e->einheit_kurzname" );
		} else {
			// print_r($et_arr);
			$anz_et = count ( $et_arr );
			echo "Eigent�meranzahl : $anz_et<hr>";
			
			/* Schleife f�r die ET */
			for($a = 0; $a < $anz_et; $a ++) {
				$et_id = $et_arr [$a] ['ID'];
				$weg->get_eigentumer_id_infos4 ( $et_id );
				
				/* Zeitraum ET */
				if ($weg->eigentuemer_bis = '0000-00-00') {
					$datum_bis = date ( "Y-m-d" );
				} else {
					$datum_bis = $weg->eigentuemer_bis;
				}
				
				/* Garantiemonate OBJ und ET */
				$this->et_tab [$a] ['GMON_OBJ'] = $garantie_mon_obj;
				
				/* Garantiemonate Eigentuemer */
				$d_et = new detail ();
				$garantie_mon_et = $d_et->finde_detail_inhalt ( 'Eigentuemer', $et_id, 'INS-Garantiemonate' );
				/* Wenn Garantie f�r den ET hinterlegt, dann Anzahl GMONATE AUS DB */
				if ($garantie_mon_et != '') {
					if ($garantie_mon_et != '0') {
						$this->gmon_et = $garantie_mon_et;
					}
				} else {
					/* Wenn keine Garantie f�r den ET hinterlegt, dann objekt garantie */
					if (! empty ( $this->gmon_obj )) {
						$this->gmon_et = $this->gmon_obj;
					}
				}
				
				// if($garantie_mon_obj>$garantie_mon_et){
				// $this->et_tab[$a]['GMON'] = $garantie_mon_obj;
				// }else{
				$this->et_tab [$a] ['GMON'] = $garantie_mon_et;
				// $this->gmon_et = $garantie_mon_et;
				// }
				
				$this->et_tab [$a] ['G_KM'] = $garantie_miete;
				$this->et_tab [$a] ['ET_ID'] = $et_id;
				$this->et_tab [$a] ['ET_VON'] = $weg->eigentuemer_von;
				$this->et_tab [$a] ['ET_BIS'] = $weg->eigentuemer_bis;
				
				if ($a > 0) {
					$this->et_tab [$a - 1] ['ET_BIS'] = $weg->eigentuemer_von;
					if ($this->et_tab [$a] ['ET_BIS'] == '0000-00-00') {
						$this->et_tab [$a] ['ET_BIS'] = $datum_bis;
					}
				}
				
				/* Monate f�r den ET */
				$monats_arr = $this->monats_array ( $weg->eigentuemer_von, $datum_bis );
				
				/* Monate durchlaufen und Tage bestimmen */
				$anz_mon = count ( $monats_arr );
				for($m = 0; $m < $anz_mon; $m ++) {
					$monat = $monats_arr [$m] ['MONAT'];
					$jahr = $monats_arr [$m] ['JAHR'];
					$tage_m = letzter_tag_im_monat ( $monat, $jahr );
					$monats_arr [$m] ['TAGE'] = $tage_m;
					/* Nutzungstage 1. ET */
					if ($a == 0 && $m == 0) {
						$monats_arr [$m] ['N_TAG'] = ($tage_m - $nl_tag + 1);
					}
					if ($a > 0 && $m == 0) {
						$et_von_arr = explode ( '-', $weg->eigentuemer_von );
						$et_von_tag = $et_von_arr [2];
						$monats_arr [$m] ['N_TAG'] = ($tage_m - $et_von_tag + 1);
					}
					if ($m > 0) {
						$monats_arr [$m] ['N_TAG'] = $tage_m;
					}
					
					if ($a == 0 && $m < $this->et_tab [$a] ['GMON']) {
						$monats_arr [$m] ['G'] = 'J';
						// ##########################$this->et_tab[$a]['G_KM']
					} else {
						$monats_arr [$m] ['G'] = 'N';
					}
				}
				/* Monatsarray mit Nutzungstagen */
				$this->et_tab [$a] ['MONATE'] = $monats_arr;
				
				/* MV im ZEITRAUM */
				$mv_et_arr = $this->get_mv_et_zeitraum_arr ( $einheit_id, $weg->eigentuemer_von, $datum_bis );
				$this->et_tab [$a] ['MVS'] = $mv_et_arr;
				unset ( $mv_et_arr );
			} // end for ET SCHLEIFE
			  // echo '<pre>';
			  // print_r($this->et_tab);
			  // die();
			  
			// #############################Vorbereitung PDF###########################
			for($a = 0; $a < $anz_et; $a ++) {
				$et_id = $this->et_tab [$a] ['ET_ID'];
				
				/* Garantiemonate Eigentuemer */
				$d_et = new detail ();
				$garantie_mon_et = $d_et->finde_detail_inhalt ( 'Eigentuemer', $et_id, 'INS-Garantiemonate' );
				/* Wenn Garantie f�r den ET hinterlegt, dann Anzahl GMONATE AUS DB */
				if ($garantie_mon_et != '') {
					if ($garantie_mon_et != '0') {
						$this->gmon_et = $garantie_mon_et;
					}
				} else {
					/* Wenn keine Garantie f�r den ET hinterlegt, dann objekt garantie */
					if (! empty ( $this->gmon_obj )) {
						$this->gmon_et = $this->gmon_obj;
					}
				}
				
				$anz_m = count ( $this->et_tab [$a] ['MONATE'] );
				
				// die("SANEL $anz_m XXX");
				$zeile = 0;
				
				$sum_GM_D_S = 0;
				$sum_FIX_S = 0;
				$sum_KM_I = 0;
				$sum_INS_ANT = 0;
				$sum_INS_ANTR = 0;
				
				// echo "<pre>";
				// print_r($this->pdf_tab_g);
				// die();
				// ######MONATE##########
				for($m = 0; $m < $anz_m; $m ++) {
					$monat = $this->et_tab [$a] ['MONATE'] [$m] ['MONAT'];
					$jahr = $this->et_tab [$a] ['MONATE'] [$m] ['JAHR'];
					$this->pdf_tab_g [$a] [$zeile] ['Z'] = $zeile;
					$this->pdf_tab_g [$a] [$zeile] ['MMJJJJ'] = $this->et_tab [$a] ['MONATE'] [$m] ['MONAT'] . "." . $this->et_tab [$a] ['MONATE'] [$m] ['JAHR'];
					$this->pdf_tab_g [$a] [$zeile] ['N_TAG'] = $this->et_tab [$a] ['MONATE'] [$m] ['N_TAG'];
					$this->pdf_tab_g [$a] [$zeile] ['TAGE'] = $this->et_tab [$a] ['MONATE'] [$m] ['TAGE'];
					
					if ($this->pdf_tab_g [$a] [$zeile] ['TAGE'] != $this->pdf_tab_g [$a] [$zeile] ['N_TAG']) {
						$this->pdf_tab_g [$a] [$zeile] ['VOLLM'] = 'N';
					} else {
						$this->pdf_tab_g [$a] [$zeile] ['VOLLM'] = 'J';
					}
					
					$this->pdf_tab_g [$a] [$zeile] ['G'] = $this->et_tab [$a] ['MONATE'] [$m] ['G'];
					
					/* FIXKOSTEN */
					/* Fixkosten Hausgeld oder Formel */
					$hg = new weg ();
					$hg->get_wg_info ( $monat, $jahr, 'Einheit', $einheit_id, 'Hausgeld' );
					$hausgeld_soll = $hg->gruppe_erg / $this->pdf_tab_g [$a] [$zeile] ['TAGE'] * $this->pdf_tab_g [$a] [$zeile] ['N_TAG'];
					
					/* Fixkosten nach Formel */
					$hg->get_eigentumer_id_infos4 ( $et_id );
					$hausgeld_soll_f = (($hg->einheit_qm_weg * 0.4) + 30) / $this->pdf_tab_g [$a] [$zeile] ['TAGE'] * $this->pdf_tab_g [$a] [$zeile] ['N_TAG'];
					if ($hausgeld_soll_f > $hausgeld_soll) {
						$hausgeld_soll = $hausgeld_soll_f;
					}
					
					$this->pdf_tab_g [$a] [$zeile] ['FIX_S'] = nummer_komma2punkt ( nummer_punkt2komma ( $hausgeld_soll ) );
					$sum_FIX_S += $this->pdf_tab_g [$a] [$zeile] ['FIX_S'];
					/* Garantiemiete */
					$this->pdf_tab_g [$a] [$zeile] ['GM'] = nummer_komma2punkt ( nummer_punkt2komma ( $garantie_miete / $this->pdf_tab_g [$a] [$zeile] ['TAGE'] * $this->pdf_tab_g [$a] [$zeile] ['N_TAG'] ) );
					
					/* 1. Et 1. pr�fung ob leer, wegen Garantie */
					// if($a==0 && $m==0){
					$ltm = letzter_tag_im_monat ( $monat, $jahr );
					// die($ltm);
					$mv_et_arr_1_mon = $this->get_mv_et_zeitraum_arr ( $einheit_id, "$jahr-$monat-01", "$jahr-$monat-$ltm" );
					/* Wenn Wohnung VERMIETET war */
					if (is_array ( $mv_et_arr_1_mon )) {
						$this->pdf_tab_g [$a] [$zeile] ['LEER'] = 'N';
						/* Wenn bei Kauf vermietet */
						if ($a == 0 && $m == 0) {
							$this->kauf_leer = 'N';
							$this->kauf_vermietet = 'J';
						}
						// $this->pdf_tab_g[$a][$zeile]['INS_ANT']='00000';
						// $this->pdf_tab_g[$a][$zeile]['HINWEIS']='CODE_VGOO';
						// print_r($mv_et_arr_1_mon);
						// die();
					} else {
						/* Wenn Wohnung leer im Monat */
						$this->pdf_tab_g [$a] [$zeile] ['LEER'] = 'J';
						if ($a == 0 && $m == 0) {
							$this->kauf_leer = 'J';
							$this->kauf_vermietet = 'N';
						}
						
						// $this->pdf_tab_g[$a][$zeile]['INS_ANT']='000d';
						// $this->pdf_tab_g[$a][$zeile]['HINWEIS']='CODE_LEG';
					}
					
					// }
					/* Bei Leer */
					if ($this->pdf_tab_g [$a] [$zeile] ['LEER'] == 'J') {
						// $this->pdf_tab_g[$a][$zeile]['HINWEIS'] = $this->kauf_vermietet;
						/* Leer in Garantiezeit */
						if ($m < $this->gmon_et) {
							if ($this->kauf_vermietet = 'N') {
								$this->pdf_tab_g [$a] [$zeile] ['HINWEIS'] = 'G_LEER_KAUF';
								$this->pdf_tab_g [$a] [$zeile] ['KM_S'] = nummer_komma2punkt ( nummer_punkt2komma ( $garantie_miete / $this->pdf_tab_g [$a] [$zeile] ['TAGE'] * $this->pdf_tab_g [$a] [$zeile] ['N_TAG'] ) );
								$this->pdf_tab_g [$a] [$zeile] ['GM_D_S'] = $this->pdf_tab_g [$a] [$zeile] ['KM_S'];
								$sum_GM_D_S += $this->pdf_tab_g [$a] [$zeile] ['GM_D_S'];
							} else {
								$this->pdf_tab_g [$a] [$zeile] ['HINWEIS'] = 'G_NO_GARANTY';
								// $this->pdf_tab_g[$a][$zeile]['INS_ANT'] = 'G_NO_GARANTY';
							}
						}
						// $zeile++;
					}
					
					/* Wenn vermietet - Neue Zeilen pro MV */
					/* Alle MVS durchlaufen */
					$anz_mvs = count ( $this->et_tab [$a] ['MVS'] );
					for($mv = 0; $mv < $anz_mvs; $mv ++) {
						$mv_id = $this->et_tab [$a] ['MVS'] [$mv] ['MIETVERTRAG_ID'];
						
						// $mk = new mietkonto();
						// $m_soll = $mk->summe_forderung_monatlich($mv_id, $jahr, $monat);
						// $mk->kaltmiete_monatlich_ink_vz($mv_id,$monat,$jahr);
						
						$mz = new miete ();
						$mz->mietkonto_berechnung_monatsgenau ( $mv_id, $jahr, $monat );
						// print_r($mz);
						// die();
						
						// if($mk->ausgangs_kaltmiete){
						// if((!empty($mz->sollmiete_warm)) or (!empty($mz->geleistete_zahlungen))){
						if ((isset ( $mz->saldo_vormonat_stand ))) {
							// $this->pdf_tab_g[$a][$zeile]['M_SOLL'] = $mk->ausgangs_kaltmiete;
							$tmp_soll_arr = explode ( '|', $mz->sollmiete_warm );
							if (is_array ( $tmp_soll_arr )) {
								$wm = $tmp_soll_arr [0];
								$mwst = $tmp_soll_arr [1];
							} else {
								$wm = $mz->sollmiete_warm;
								$mwst = 0.00;
							}
							
							if ($wm != 0 or $mwst != 0 or $mz->geleistete_zahlungen != 0) {
								$this->pdf_tab_g [$a] [$zeile] ['WM_SOLL'] = $wm;
								$this->pdf_tab_g [$a] [$zeile] ['NK'] = nummer_komma2punkt ( nummer_punkt2komma ( $mz->davon_umlagen ) );
								$this->pdf_tab_g [$a] [$zeile] ['MWST_S'] = $mwst;
								$this->pdf_tab_g [$a] [$zeile] ['M_ZB'] = $mz->geleistete_zahlungen;
								$this->pdf_tab_g [$a] [$zeile] ['M_ERG'] = $mz->erg;
								$this->pdf_tab_g [$a] [$zeile] ['M_SVM'] = $mz->saldo_vormonat_stand;
								$this->pdf_tab_g [$a] [$zeile] ['MMJJJJ'] = $this->et_tab [$a] ['MONATE'] [$m] ['MONAT'] . "." . $this->et_tab [$a] ['MONATE'] [$m] ['JAHR'];
								$this->pdf_tab_g [$a] [$zeile] ['MV_ID'] = $mv_id;
								$mvs = new mietvertraege ();
								$mvs->get_mietvertrag_infos_aktuell ( $mv_id );
								$this->pdf_tab_g [$a] [$zeile] ['MIETER'] = $mvs->personen_name_string;
								
								/* Kaltmiete */
								$kalt_miete = $wm - nummer_komma2punkt ( nummer_punkt2komma ( $mz->davon_umlagen ) );
								$this->pdf_tab_g [$a] [$zeile] ['KM_S'] = nummer_komma2punkt ( nummer_punkt2komma ( $kalt_miete ) );
								
								/* Garantiemiete SOLL DIFF */
								if ($this->pdf_tab_g [$a] [$zeile] ['GM'] > $kalt_miete) {
									$gmk = $this->pdf_tab_g [$a] [$zeile] ['GM'];
									$diff_mon_soll = nummer_komma2punkt ( nummer_punkt2komma ( $this->pdf_tab_g [$a] [$zeile] ['GM'] - $kalt_miete ) );
								} else {
									$diff_mon_soll = '0.00';
								}
								
								$this->pdf_tab_g [$a] [$zeile] ['GM_D_S'] = $diff_mon_soll;
								$sum_GM_D_S += $this->pdf_tab_g [$a] [$zeile] ['GM_D_S'];
								/* Garantiemiete IST DIFF */
								
								/*
								 * if($this->pdf_tab_g[$a][$zeile]['G']=='J'){
								 * $this->pdf_tab_g[$a][$zeile]['GM_D_I'] = $this->pdf_tab_g[$a][$zeile]['GM_D_S'];
								 * }else{
								 * $this->pdf_tab_g[$a][$zeile]['GM_D_I'] = '000.00';
								 * }
								 */
								
								// $zeile++;
							}
						}
						// $zeile++;
					} // end for MV
					
					/* Garantiezeile */
					/* Nur wenn Garantie festgelegt ist */
					if (isset ( $this->gmon_et )) {
						if ($m == $this->gmon_et - 1) {
							$zeile ++;
							$this->pdf_tab_g [$a] [$zeile] ['MMJJJJ'] = "-$monat-$jahr-";
							$this->pdf_tab_g [$a] [$zeile] ['MIETER'] = 'SUMMEN';
							
							$this->pdf_tab_g [$a] [$zeile] ['GM_D_S'] = "<b>" . nummer_punkt2komma_t ( $sum_GM_D_S ) . "</b>";
							$this->pdf_tab_g [$a] [$zeile] ['FIX_S'] = "<b>" . nummer_punkt2komma_t ( $sum_FIX_S ) . "</b>";
							
							$z_sum_GM_D_S = $sum_GM_D_S;
							$z_sum_FIX_S = $sum_FIX_S;
						}
					}
					
					// $this->pdf_tab_g[$a][$zeile]['MVS'] = $this->et_tab[$a]['MONATE'][$m]['MVS'];
					// $zeile++;
					// $this->pdf_tab_g[$a][$zeile]['Z'] = $monat;
					$zeile ++;
				}
				
				/* Vorletzte Zeile - Summe nach Garantie */
				$this->pdf_tab_g [$a] [$zeile] ['MMJJJJ'] = '----------';
				$this->pdf_tab_g [$a] [$zeile] ['MIETER'] = 'NACH GARANTIE';
				$et_sum_GM_D_S = nummer_punkt2komma_t ( $sum_GM_D_S - $z_sum_GM_D_S );
				$et_sum_FIX_S = nummer_punkt2komma_t ( $sum_FIX_S - $z_sum_FIX_S );
				
				$this->pdf_tab_g [$a] [$zeile] ['GM_D_S'] = "<b>$et_sum_GM_D_S</b>";
				$this->pdf_tab_g [$a] [$zeile] ['FIX_S'] = "<b>$et_sum_FIX_S</b>";
				
				/* Letzte ZEile */
				$zeile ++;
				$this->pdf_tab_g [$a] [$zeile] ['MMJJJJ'] = '----------';
				$this->pdf_tab_g [$a] [$zeile] ['MIETER'] = 'GESAMT';
				$this->pdf_tab_g [$a] [$zeile] ['GM_D_S'] = "<b>" . nummer_punkt2komma_t ( $sum_GM_D_S ) . "</b>";
				$this->pdf_tab_g [$a] [$zeile] ['FIX_S'] = "<b>" . nummer_punkt2komma_t ( $sum_FIX_S ) . "</b>";
				$this->pdf_tab_g [$a] [$zeile] ['INS_ANTR'] = "<b>" . nummer_punkt2komma_t ( $sum_INS_ANTR ) . "</b>";
			} // END FOR ET
			
			/* Jeden GarantieMonat */
			/*
			 * if($m<$this->et_tab[$a]['GMON']){
			 * if($this->pdf_tab_g[$a][$zeile]['GM_D_S']>0){
			 * $this->pdf_tab_g[$a][$zeile]['EINNAHME'] = 'KU';
			 * }else{
			 * $this->pdf_tab_g[$a][$zeile]['EINNAHME'] = 'BA';
			 * }
			 * }
			 */
			
			// echo '<pre>';
			// print_r($this);
			// die();
			// $sum_INS_ANTR = 0;
			for($et = 0; $et < $anz_et; $et ++) {
				// if(empty($this->gmon_et)){
				// $this->gmon_et = $this->et_tab[$et]['GMON_OBJ'];
				// }
				$zeilen = count ( $this->pdf_tab_g [$et] );
				// die("ZEILEN $zeilen");
				$sum_KM_I = 0;
				// $sum_INS_ANT = 0;
				// $sum_INS_ANTR = 0;
				for($z = 0; $z < $zeilen; $z ++) {
					// ##NUR GARANTIEMONATE BERECHNEN#####
					
					if ($z < $this->gmon_et) {
						/* Mietgarantie diff */
						if (isset ( $this->pdf_tab_g [$et] [$z] ['GM_D_S'] ) && $this->pdf_tab_g [$et] [$z] ['GM_D_S'] > 0) {
							$this->pdf_tab_g [$et] [$z] ['KM_I'] = $this->pdf_tab_g [$et] [$z] ['GM'];
							$this->pdf_tab_g [$et] [$z] ['INS_ANT'] = '0.000F';
							$this->pdf_tab_g [$et] [$z] ['HINWEIS'] = 'CODE_VG_GM';
							
							/* Wenn Mieter gezahlt und in minus */
							if ($this->pdf_tab_g [$et] [$z] ['M_ZB'] > 0 && $this->pdf_tab_g [$et] [$z] ['M_ERG'] < 0) {
								$this->pdf_tab_g [$et] [$z] ['INS_ANT'] = nummer_komma2punkt ( nummer_punkt2komma ( ($this->pdf_tab_g [$et] [$z] ['M_ERG'] - $this->pdf_tab_g [$et] [$z] ['M_SVM']) * - 1 ) );
								$this->pdf_tab_g [$et] [$z] ['HINWEIS'] = 'CODE_VG_GM_1';
								$this->pdf_tab_g [$et] [$z] ['INS_ANTR'] = '0.00';
							}
							
							/* Wenn Mieter nicht gezahlt und in minus */
							if ($this->pdf_tab_g [$et] [$z] ['M_ZB'] <= 0 && $this->pdf_tab_g [$et] [$z] ['M_ERG'] < 0) {
								$this->pdf_tab_g [$et] [$z] ['INS_ANT'] = $this->pdf_tab_g [$et] [$z] ['M_ERG'] * - 1;
								$this->pdf_tab_g [$et] [$z] ['HINWEIS'] = 'CODE_VG_GM_2';
								$this->pdf_tab_g [$et] [$z] ['INS_ANTR'] = '0.00';
							}
							
							/* Wenn Mieter im PLUS */
							if (isset ( $this->pdf_tab_g [$et] [$z] ['M_ERG'] ) && $this->pdf_tab_g [$et] [$z] ['M_ERG'] >= 0) {
								$this->pdf_tab_g [$et] [$z] ['INS_ANT'] = '0.000';
								$this->pdf_tab_g [$et] [$z] ['HINWEIS'] = 'CODE_VG_GM_3';
								
								$this->pdf_tab_g [$et] [$z] ['INS_ANTR'] = $sum_INS_ANT - $sum_INS_ANTR;
								// $sum_INS_ANTR +=$this->pdf_tab_g[$et][$z]['INS_ANTR'];
							}
							
							/* Wenn LEER */
							if (! isset ( $this->pdf_tab_g [$et] [$z] ['M_ERG'] )) {
								$this->pdf_tab_g [$et] [$z] ['INS_ANT'] = $this->pdf_tab_g [$et] [$z] ['GM'];
								$this->pdf_tab_g [$et] [$z] ['HINWEIS'] = 'CODE_VG_GM_L';
								
								$this->pdf_tab_g [$et] [$z] ['INS_ANTR'] = '0000.0';
								// $sum_INS_ANTR +=0.0;
							}
							
							$sum_INS_ANTR += $this->pdf_tab_g [$et] [$z] ['INS_ANTR'];
							$sum_KM_I += $this->pdf_tab_g [$et] [$z] ['KM_I'];
							$sum_INS_ANT += $this->pdf_tab_g [$et] [$z] ['INS_ANT'];
						} else {
							$this->pdf_tab_g [$et] [$z] ['KM_I'] = $this->pdf_tab_g [$et] [$z] ['KM_S'];
							// $sum_KM_I +=$this->pdf_tab_g[$et][$z]['KM_I'];
							/* Wenn Mieter gezahlt und in minus */
							if ($this->pdf_tab_g [$et] [$z] ['M_ZB'] > 0 && $this->pdf_tab_g [$et] [$z] ['M_ERG'] < 0) {
								$this->pdf_tab_g [$et] [$z] ['INS_ANT'] = nummer_komma2punkt ( nummer_punkt2komma ( ($this->pdf_tab_g [$et] [$z] ['M_ERG'] - $this->pdf_tab_g [$et] [$z] ['M_SVM']) * - 1 ) );
								$this->pdf_tab_g [$et] [$z] ['HINWEIS'] = 'CODE_NG_MM1';
								$this->pdf_tab_g [$et] [$z] ['INS_ANTR'] = '0.00';
							}
							/* Wenn Mieter nicht gezahlt und in minus */
							if ($this->pdf_tab_g [$et] [$z] ['M_ZB'] <= 0 && $this->pdf_tab_g [$et] [$z] ['M_ERG'] < 0) {
								$this->pdf_tab_g [$et] [$z] ['INS_ANT'] = nummer_komma2punkt ( nummer_punkt2komma ( $this->pdf_tab_g [$et] [$z] ['M_ERG'] * - 1 ) );
								$this->pdf_tab_g [$et] [$z] ['HINWEIS'] = 'CODE_VGNM';
							}
							
							/* Wenn Mieter im PLUS */
							if (isset ( $this->pdf_tab_g [$et] [$z] ['M_ERG'] ) && $this->pdf_tab_g [$et] [$z] ['M_ERG'] >= 0) {
								$this->pdf_tab_g [$et] [$z] ['INS_ANT'] = '0.000';
								$this->pdf_tab_g [$et] [$z] ['HINWEIS'] = 'CODE_VG';
								
								$this->pdf_tab_g [$et] [$z] ['INS_ANTR'] = $sum_INS_ANT - $sum_INS_ANTR;
								$sum_INS_ANTR += $this->pdf_tab_g [$et] [$z] ['INS_ANTR'];
							}
							$sum_INS_ANT += $this->pdf_tab_g [$et] [$z] ['INS_ANT'];
							$sum_KM_I += $this->pdf_tab_g [$et] [$z] ['KM_I'];
						}
						
						// $sum_INS_ANT +=$this->pdf_tab_g[$et][$z]['INS_ANT'];
					} // ##ende garantiemonate
					
					/*
					 * ob_clean();
					 * echo '<pre>';
					 * print_r($this);
					 * echo $sum_KM_I;
					 * die();
					 */
					// if(!isset($this->gmon_et)){
					// $this->gmon_et = $this->et_tab[0]['GMON'];
					// }
					
					/* Nur wenn Garantie festgelegt ist */
					if (isset ( $this->gmon_et )) {
						if ($z == $this->gmon_et) {
							$this->pdf_tab_g [$et] [$z] ['KM_I'] = "<b>" . nummer_punkt2komma_t ( $sum_KM_I ) . "</b>";
							$this->pdf_tab_g [$et] [$z] ['INS_ANT'] = "<b>" . nummer_punkt2komma_t ( $sum_INS_ANT ) . "</b>";
							$this->pdf_tab_g [$et] [$z] ['INS_ANTR'] = "<b>" . nummer_punkt2komma_t ( $sum_INS_ANTR ) . "</b>";
							$this->pdf_tab_g [$et] [$z] ['INS_GARANTY'] = "<b>" . nummer_punkt2komma_t ( $sum_INS_ANTR - $sum_INS_ANT ) . "</b>";
							$this->pdf_tab_g [$et] [$z] ['HINWEIS'] = 'ZSX';
						}
					}
					
					/* Monate nach Garantie */
					
					if ($z > $this->gmon_et) {
						/* Mietgarantie diff */
						if (isset ( $this->pdf_tab_g [$et] [$z] ['GM_D_S'] ) && $this->pdf_tab_g [$et] [$z] ['GM_D_S'] > 0) {
							$this->pdf_tab_g [$et] [$z] ['KM_I'] = $this->pdf_tab_g [$et] [$z] ['GM'];
							$this->pdf_tab_g [$et] [$z] ['INS_ANT'] = '-';
							$sum_KM_I += $this->pdf_tab_g [$et] [$z] ['KM_I'];
							$this->pdf_tab_g [$et] [$z] ['HINWEIS'] = 'CODE_NG_GM';
						} else {
							
							/* Wenn Mieter gezahlt und in minus */
							if ($this->pdf_tab_g [$et] [$z] ['M_ZB'] > 0 && $this->pdf_tab_g [$et] [$z] ['M_ERG'] < 0) {
								$this->pdf_tab_g [$et] [$z] ['INS_ANT'] = '-';
								$this->pdf_tab_g [$et] [$z] ['HINWEIS'] = 'CODE_NGBMa';
								/* Saldo ver�ndert sich zum Vormonat */
								if ($this->pdf_tab_g [$et] [$z] ['M_SVM'] != $this->pdf_tab_g [$et] [$z] ['M_ERG']) {
									$this->pdf_tab_g [$et] [$z] ['KM_I'] = $this->pdf_tab_g [$et] [$z] ['M_ZB'] + $this->pdf_tab_g [$et] [$z] ['M_SVM'] - $this->pdf_tab_g [$et] [$z] ['NK'];
									$this->pdf_tab_g [$et] [$z] ['HINWEIS'] = 'CODE_NGBMx';
									$this->pdf_tab_g [$et] [$z] ['INS_ANTR'] = '0.00';
								} else {
									$this->pdf_tab_g [$et] [$z] ['KM_I'] = $this->pdf_tab_g [$et] [$z] ['M_ZB'] - $this->pdf_tab_g [$et] [$z] ['NK'];
									$this->pdf_tab_g [$et] [$z] ['HINWEIS'] = 'CODE_NGBMy';
									$this->pdf_tab_g [$et] [$z] ['INS_ANTR'] = '0.00';
								}
							}
							/* Wenn Mieter nicht gezahlt und in minus */
							if ($this->pdf_tab_g [$et] [$z] ['M_ZB'] <= 0 && $this->pdf_tab_g [$et] [$z] ['M_ERG'] < 0) {
								$this->pdf_tab_g [$et] [$z] ['INS_ANT'] = '-';
								$this->pdf_tab_g [$et] [$z] ['HINWEIS'] = 'CODE_NGM';
								$this->pdf_tab_g [$et] [$z] ['KM_I'] = $this->pdf_tab_g [$et] [$z] ['KM_S'] + $this->pdf_tab_g [$et] [$z] ['M_ERG'];
							}
							
							/* Wenn Mieter im PLUS */
							if (isset ( $this->pdf_tab_g [$et] [$z] ['M_ERG'] ) && $this->pdf_tab_g [$et] [$z] ['M_ERG'] >= 0) {
								$this->pdf_tab_g [$et] [$z] ['INS_ANT'] = '-';
								$this->pdf_tab_g [$et] [$z] ['HINWEIS'] = 'CODE_NGP';
								$this->pdf_tab_g [$et] [$z] ['KM_I'] = $this->pdf_tab_g [$et] [$z] ['KM_S'];
								$this->pdf_tab_g [$et] [$z] ['INS_ANTR'] = $sum_INS_ANT - $sum_INS_ANTR;
								$sum_INS_ANTR += $this->pdf_tab_g [$et] [$z] ['INS_ANTR'];
							}
							// $this->pdf_tab_g[$et][$z]['KM_I'] = $this->pdf_tab_g[$et][$z]['KM_S'];
							$sum_KM_I += $this->pdf_tab_g [$et] [$z] ['KM_I'];
						}
					} // ##ende garantiemonate
				}
				
				// echo '<pre>';
				// print_r($this);
				// die();
				
				/* Nach Schl�ssel sortieren wegen PDF */
				ksort ( $this->pdf_tab_g [$et] );
				
				// unset($this);
				unset ( $mv_et_arr );
				unset ( $mv_et_arr_1_mon );
				unset ( $mv_id );
				// unset($et_arr);
			}
			
			// echo '<pre>';
			// print_r($this);
			// die();
			
			// $pdf->ezSetDy(-20);
			
			$cols = array (
					'Z' => Z,
					'MV_ID' => MV,
					'MMJJJJ' => MONAT2,
					'N_TAG' => N_TAG,
					'TAGE' => TAGE,
					'G' => G,
					'LEER' => LEER,
					'WM_SOLL' => WM_SOLL,
					'MWST_S' => MWST_S,
					'NK' => NK,
					'M_ZB' => M_ZB,
					'M_ERG' => M_ERG,
					'M_SVM' => M_SVM,
					'MIETER' => MIETER,
					'GM' => GM,
					'KM_S' => KM_S,
					'GM_D_S' => GM_D_S,
					'FIX_S' => FIX_S,
					'KM_I' => KM_I,
					'INS_ANT' => INS_ANT,
					'INS_ANTR' => INS_R,
					'INS_GARANTY' => INS_GARANTY,
					'HINWEIS' => CODE 
			);
			
			for($et = 0; $et < $anz_et; $et ++) {
				$pdf->ezText ( "$e->einheit_kurzname", 14 );
				$pdf->ezTable ( $this->pdf_tab_g [$et], $cols, EINNAHMEN_REPORT . " $weg->eigentuemer_von $datum_bis", array (
						'showHeadings' => 1,
						'shaded' => 1,
						'titleFontSize' => 10,
						'fontSize' => 7,
						'xPos' => 50,
						'xOrientation' => 'right',
						'width' => 750,
						'cols' => array (
								'IHR' => array (
										'justification' => 'right' 
								),
								'HV' => array (
										'justification' => 'right' 
								),
								'REP' => array (
										'justification' => 'right' 
								),
								'AUSZAHLUNG' => array (
										'justification' => 'right' 
								) 
						) 
				) );
			}
			// ob_clean(); //ausgabepuffer leeren
			// header("Content-type: application/pdf"); // wird von MSIE ignoriert
			// $pdf->ezStream();
		} // end if ET exist
	}
	function saldo_berechnung_et_DOBARpravo($einheit_id) {
		/* Infos zu Einheit */
		$e = new einheit ();
		$e->get_einheit_info ( $einheit_id );
		$gk = new geldkonto_info ();
		$gk->geld_konto_ermitteln ( 'OBJEKT', $e->objekt_id );
		
		/* OBJEKTDATEN */
		/* Garantiemonate Objekt */
		$d = new detail ();
		$garantie_mon_obj = $d->finde_detail_inhalt ( 'Objekt', $e->objekt_id, 'INS-Garantiemonate' );
		if (! $garantie_mon_obj) {
			$garantie_mon_obj = 0;
		} else {
			$this->gmon_obj = $garantie_mon_obj;
		}
		
		/* Garantierte Miete */
		/* Garantiemiete */
		$garantie_miete = nummer_komma2punkt ( $d->finde_detail_inhalt ( 'EINHEIT', $einheit_id, 'WEG-KaltmieteINS' ) );
		if (! $garantie_miete) {
			$garantie_miete = 0;
		}
		/* Nutzenlastenwechsel */
		$nl_datum = $d->finde_detail_inhalt ( 'Objekt', $e->objekt_id, 'Nutzen-Lastenwechsel' );
		$nl_datum_arr = explode ( '.', $nl_datum );
		$nl_tag = $nl_datum_arr [0];
		$nl_monat = $nl_datum_arr [1];
		$nl_jahr = $nl_datum_arr [2];
		
		/* Verwaltungs�bernahme */
		$vu_datum = $d->finde_detail_inhalt ( 'Objekt', $e->objekt_id, 'Verwaltungs�bernahme' );
		$vu_datum_arr = explode ( '.', $vu_datum );
		$vu_tag = $vu_datum_arr [0];
		$vu_monat = $vu_datum_arr [1];
		$vu_jahr = $vu_datum_arr [2];
		
		echo "<h2>GMU: $garantie_mon_obj NLW: $nl_datum VU: $vu_datum</h2>";
		
		/* Alle Eigent�mer */
		$weg = new weg ();
		$et_arr = $weg->get_eigentuemer_arr ( $einheit_id );
		
		if (! is_array ( $et_arr )) {
			fehlermeldung_ausgeben ( "Keine Eigent�mer zu $e->einheit_kurzname" );
		} else {
			// print_r($et_arr);
			$anz_et = count ( $et_arr );
			echo "Eigent�meranzahl : $anz_et<hr>";
			
			/* Schleife f�r die ET */
			for($a = 0; $a < $anz_et; $a ++) {
				$et_id = $et_arr [$a] ['ID'];
				$weg->get_eigentumer_id_infos4 ( $et_id );
				
				/* Zeitraum ET */
				if ($weg->eigentuemer_bis = '0000-00-00') {
					$datum_bis = date ( "Y-m-d" );
				} else {
					$datum_bis = $weg->eigentuemer_bis;
				}
				
				/* Garantiemonate OBJ und ET */
				$this->et_tab [$a] ['GMON_OBJ'] = $garantie_mon_obj;
				
				/* Garantiemonate Eigentuemer */
				$d_et = new detail ();
				$garantie_mon_et = $d_et->finde_detail_inhalt ( 'Eigentuemer', $et_id, 'INS-Garantiemonate' );
				/* Wenn Garantie f�r den ET hinterlegt, dann Anzahl GMONATE AUS DB */
				if ($garantie_mon_et != '') {
					if ($garantie_mon_et != '0') {
						$this->gmon_et = $garantie_mon_et;
					}
				} else {
					/* Wenn keine Garantie f�r den ET hinterlegt, dann objekt garantie */
					if (! empty ( $this->gmon_obj )) {
						$this->gmon_et = $this->gmon_obj;
					}
				}
				
				// if($garantie_mon_obj>$garantie_mon_et){
				// $this->et_tab[$a]['GMON'] = $garantie_mon_obj;
				// }else{
				$this->et_tab [$a] ['GMON'] = $garantie_mon_et;
				// $this->gmon_et = $garantie_mon_et;
				// }
				
				$this->et_tab [$a] ['G_KM'] = $garantie_miete;
				$this->et_tab [$a] ['ET_ID'] = $et_id;
				$this->et_tab [$a] ['ET_VON'] = $weg->eigentuemer_von;
				$this->et_tab [$a] ['ET_BIS'] = $weg->eigentuemer_bis;
				
				if ($a > 0) {
					$this->et_tab [$a - 1] ['ET_BIS'] = $weg->eigentuemer_von;
					if ($this->et_tab [$a] ['ET_BIS'] == '0000-00-00') {
						$this->et_tab [$a] ['ET_BIS'] = $datum_bis;
					}
				}
				
				/* Monate f�r den ET */
				$monats_arr = $this->monats_array ( $weg->eigentuemer_von, $datum_bis );
				
				/* Monate durchlaufen und Tage bestimmen */
				$anz_mon = count ( $monats_arr );
				for($m = 0; $m < $anz_mon; $m ++) {
					$monat = $monats_arr [$m] ['MONAT'];
					$jahr = $monats_arr [$m] ['JAHR'];
					$tage_m = letzter_tag_im_monat ( $monat, $jahr );
					$monats_arr [$m] ['TAGE'] = $tage_m;
					/* Nutzungstage 1. ET */
					if ($a == 0 && $m == 0) {
						$monats_arr [$m] ['N_TAG'] = ($tage_m - $nl_tag + 1);
					}
					if ($a > 0 && $m == 0) {
						$et_von_arr = explode ( '-', $weg->eigentuemer_von );
						$et_von_tag = $et_von_arr [2];
						$monats_arr [$m] ['N_TAG'] = ($tage_m - $et_von_tag + 1);
					}
					if ($m > 0) {
						$monats_arr [$m] ['N_TAG'] = $tage_m;
					}
					
					if ($a == 0 && $m < $this->et_tab [$a] ['GMON']) {
						$monats_arr [$m] ['G'] = 'J';
						// ##########################$this->et_tab[$a]['G_KM']
					} else {
						$monats_arr [$m] ['G'] = 'N';
					}
				}
				/* Monatsarray mit Nutzungstagen */
				$this->et_tab [$a] ['MONATE'] = $monats_arr;
				
				/* MV im ZEITRAUM */
				$mv_et_arr = $this->get_mv_et_zeitraum_arr ( $einheit_id, $weg->eigentuemer_von, $datum_bis );
				$this->et_tab [$a] ['MVS'] = $mv_et_arr;
			} // end for ET SCHLEIFE
			  // echo '<pre>';
			  // print_r($this->et_tab);
			  // die();
			  
			// #############################Vorbereitung PDF###########################
			for($a = 0; $a < $anz_et; $a ++) {
				$et_id = $this->et_tab [$a] ['ET_ID'];
				
				/* Garantiemonate Eigentuemer */
				$d_et = new detail ();
				$garantie_mon_et = $d_et->finde_detail_inhalt ( 'Eigentuemer', $et_id, 'INS-Garantiemonate' );
				/* Wenn Garantie f�r den ET hinterlegt, dann Anzahl GMONATE AUS DB */
				if ($garantie_mon_et != '') {
					if ($garantie_mon_et != '0') {
						$this->gmon_et = $garantie_mon_et;
					}
				} else {
					/* Wenn keine Garantie f�r den ET hinterlegt, dann objekt garantie */
					if (! empty ( $this->gmon_obj )) {
						$this->gmon_et = $this->gmon_obj;
					}
				}
				
				$anz_m = count ( $this->et_tab [$a] ['MONATE'] );
				$zeile = 0;
				
				$sum_GM_D_S = 0;
				$sum_FIX_S = 0;
				$sum_KM_I = 0;
				// ######MONATE##########
				for($m = 0; $m < $anz_m; $m ++) {
					$monat = $this->et_tab [$a] ['MONATE'] [$m] ['MONAT'];
					$jahr = $this->et_tab [$a] ['MONATE'] [$m] ['JAHR'];
					
					$this->pdf_tab_g [$a] [$zeile] ['MMJJJJ'] = $this->et_tab [$a] ['MONATE'] [$m] ['MONAT'] . "." . $this->et_tab [$a] ['MONATE'] [$m] ['JAHR'];
					$this->pdf_tab_g [$a] [$zeile] ['N_TAG'] = $this->et_tab [$a] ['MONATE'] [$m] ['N_TAG'];
					$this->pdf_tab_g [$a] [$zeile] ['TAGE'] = $this->et_tab [$a] ['MONATE'] [$m] ['TAGE'];
					
					if ($this->pdf_tab_g [$a] [$zeile] ['TAGE'] != $this->pdf_tab_g [$a] [$zeile] ['N_TAG']) {
						$this->pdf_tab_g [$a] [$zeile] ['VOLLM'] = 'N';
					} else {
						$this->pdf_tab_g [$a] [$zeile] ['VOLLM'] = 'J';
					}
					
					$this->pdf_tab_g [$a] [$zeile] ['G'] = $this->et_tab [$a] ['MONATE'] [$m] ['G'];
					
					/* FIXKOSTEN */
					/* Fixkosten Hausgeld oder Formel */
					$hg = new weg ();
					$hg->get_wg_info ( $monat, $jahr, 'Einheit', $einheit_id, 'Hausgeld' );
					$hausgeld_soll = $hg->gruppe_erg / $this->pdf_tab_g [$a] [$zeile] ['TAGE'] * $this->pdf_tab_g [$a] [$zeile] ['N_TAG'];
					
					/* Fixkosten nach Formel */
					$hg->get_eigentumer_id_infos4 ( $et_id );
					$hausgeld_soll_f = (($hg->einheit_qm_weg * 0.4) + 30) / $this->pdf_tab_g [$a] [$zeile] ['TAGE'] * $this->pdf_tab_g [$a] [$zeile] ['N_TAG'];
					if ($hausgeld_soll_f > $hausgeld_soll) {
						$hausgeld_soll = $hausgeld_soll_f;
					}
					
					$this->pdf_tab_g [$a] [$zeile] ['FIX_S'] = nummer_komma2punkt ( nummer_punkt2komma ( $hausgeld_soll ) );
					$sum_FIX_S += $this->pdf_tab_g [$a] [$zeile] ['FIX_S'];
					/* Garantiemiete */
					$this->pdf_tab_g [$a] [$zeile] ['GM'] = nummer_komma2punkt ( nummer_punkt2komma ( $garantie_miete / $this->pdf_tab_g [$a] [$zeile] ['TAGE'] * $this->pdf_tab_g [$a] [$zeile] ['N_TAG'] ) );
					
					/* 1. Et 1. pr�fung ob leer, wegen Garantie */
					// if($a==0 && $m==0){
					$ltm = letzter_tag_im_monat ( $monat, $jahr );
					// die($ltm);
					$mv_et_arr_1_mon = $this->get_mv_et_zeitraum_arr ( $einheit_id, "$jahr-$monat-01", "$jahr-$monat-$ltm" );
					/* Wenn Wohnung VERMIETET war */
					if (is_array ( $mv_et_arr_1_mon )) {
						$this->pdf_tab_g [$a] [$zeile] ['LEER'] = 'N';
						/* Wenn bei Kauf vermietet */
						if ($a == 0 && $m == 0) {
							$this->kauf_leer = 'N';
							$this->kauf_vermietet = 'J';
						}
						// $this->pdf_tab_g[$a][$zeile]['INS_ANT']='00000';
						// $this->pdf_tab_g[$a][$zeile]['HINWEIS']='CODE_VGOO';
						// print_r($mv_et_arr_1_mon);
						// die();
					} else {
						/* Wenn Wohnung leer im Monat */
						$this->pdf_tab_g [$a] [$zeile] ['LEER'] = 'J';
						if ($a == 0 && $m == 0) {
							$this->kauf_leer = 'J';
							$this->kauf_vermietet = 'N';
						}
						
						// $this->pdf_tab_g[$a][$zeile]['INS_ANT']='000d';
						// $this->pdf_tab_g[$a][$zeile]['HINWEIS']='CODE_LEG';
					}
					
					// }
					/* Bei Leer */
					if ($this->pdf_tab_g [$a] [$zeile] ['LEER'] == 'J') {
						// $this->pdf_tab_g[$a][$zeile]['HINWEIS'] = $this->kauf_vermietet;
						/* Leer in Garantiezeit */
						if ($m < $this->gmon_et) {
							if ($this->kauf_vermietet = 'N') {
								$this->pdf_tab_g [$a] [$zeile] ['HINWEIS'] = 'G_LEER_KAUF';
								$this->pdf_tab_g [$a] [$zeile] ['KM_S'] = nummer_komma2punkt ( nummer_punkt2komma ( $garantie_miete / $this->pdf_tab_g [$a] [$zeile] ['TAGE'] * $this->pdf_tab_g [$a] [$zeile] ['N_TAG'] ) );
								$this->pdf_tab_g [$a] [$zeile] ['GM_D_S'] = $this->pdf_tab_g [$a] [$zeile] ['KM_S'];
								$sum_GM_D_S += $this->pdf_tab_g [$a] [$zeile] ['GM_D_S'];
							} else {
								$this->pdf_tab_g [$a] [$zeile] ['HINWEIS'] = 'G_NO_GARANTY';
								// $this->pdf_tab_g[$a][$zeile]['INS_ANT'] = 'G_NO_GARANTY';
							}
						}
						$zeile ++;
					}
					
					/* Wenn vermietet - Neue Zeilen pro MV */
					/* Alle MVS durchlaufen */
					$anz_mvs = count ( $this->et_tab [$a] ['MVS'] );
					for($mv = 0; $mv < $anz_mvs; $mv ++) {
						$mv_id = $this->et_tab [$a] ['MVS'] [$mv] ['MIETVERTRAG_ID'];
						
						// $mk = new mietkonto();
						// $m_soll = $mk->summe_forderung_monatlich($mv_id, $jahr, $monat);
						// $mk->kaltmiete_monatlich_ink_vz($mv_id,$monat,$jahr);
						
						$mz = new miete ();
						$mz->mietkonto_berechnung_monatsgenau ( $mv_id, $jahr, $monat );
						// print_r($mz);
						// die();
						
						// if($mk->ausgangs_kaltmiete){
						// if((!empty($mz->sollmiete_warm)) or (!empty($mz->geleistete_zahlungen))){
						if ((isset ( $mz->saldo_vormonat_stand ))) {
							// $this->pdf_tab_g[$a][$zeile]['M_SOLL'] = $mk->ausgangs_kaltmiete;
							$tmp_soll_arr = explode ( '|', $mz->sollmiete_warm );
							if (is_array ( $tmp_soll_arr )) {
								$wm = $tmp_soll_arr [0];
								$mwst = $tmp_soll_arr [1];
							} else {
								$wm = $mz->sollmiete_warm;
								$mwst = 0.00;
							}
							
							if ($wm != 0 or $mwst != 0 or $mz->geleistete_zahlungen != 0) {
								$this->pdf_tab_g [$a] [$zeile] ['WM_SOLL'] = $wm;
								$this->pdf_tab_g [$a] [$zeile] ['NK'] = nummer_komma2punkt ( nummer_punkt2komma ( $mz->davon_umlagen ) );
								$this->pdf_tab_g [$a] [$zeile] ['MWST_S'] = $mwst;
								$this->pdf_tab_g [$a] [$zeile] ['M_ZB'] = $mz->geleistete_zahlungen;
								$this->pdf_tab_g [$a] [$zeile] ['M_ERG'] = $mz->erg;
								$this->pdf_tab_g [$a] [$zeile] ['M_SVM'] = $mz->saldo_vormonat_stand;
								$this->pdf_tab_g [$a] [$zeile] ['MMJJJJ'] = $this->et_tab [$a] ['MONATE'] [$m] ['MONAT'] . "." . $this->et_tab [$a] ['MONATE'] [$m] ['JAHR'];
								$this->pdf_tab_g [$a] [$zeile] ['MV_ID'] = $mv_id;
								$mvs = new mietvertraege ();
								$mvs->get_mietvertrag_infos_aktuell ( $mv_id );
								$this->pdf_tab_g [$a] [$zeile] ['MIETER'] = $mvs->personen_name_string;
								
								/* Kaltmiete */
								$kalt_miete = $wm - nummer_komma2punkt ( nummer_punkt2komma ( $mz->davon_umlagen ) );
								$this->pdf_tab_g [$a] [$zeile] ['KM_S'] = nummer_komma2punkt ( nummer_punkt2komma ( $kalt_miete ) );
								
								/* Garantiemiete SOLL DIFF */
								if ($this->pdf_tab_g [$a] [$zeile] ['GM'] > $kalt_miete) {
									$gmk = $this->pdf_tab_g [$a] [$zeile] ['GM'];
									$diff_mon_soll = nummer_komma2punkt ( nummer_punkt2komma ( $this->pdf_tab_g [$a] [$zeile] ['GM'] - $kalt_miete ) );
								} else {
									$diff_mon_soll = '0.00';
								}
								
								$this->pdf_tab_g [$a] [$zeile] ['GM_D_S'] = $diff_mon_soll;
								$sum_GM_D_S += $this->pdf_tab_g [$a] [$zeile] ['GM_D_S'];
								/* Garantiemiete IST DIFF */
								
								/*
								 * if($this->pdf_tab_g[$a][$zeile]['G']=='J'){
								 * $this->pdf_tab_g[$a][$zeile]['GM_D_I'] = $this->pdf_tab_g[$a][$zeile]['GM_D_S'];
								 * }else{
								 * $this->pdf_tab_g[$a][$zeile]['GM_D_I'] = '000.00';
								 * }
								 */
								
								$zeile ++;
							}
						}
					} // end for MV
					
					/* Garantiezeile */
					/* Nur wenn Garantie festgelegt ist */
					if (isset ( $this->gmon_et )) {
						if ($m == $this->gmon_et - 1) {
							// $zeile++;
							$this->pdf_tab_g [$a] [$zeile] ['MMJJJJ'] = '----------';
							$this->pdf_tab_g [$a] [$zeile] ['MIETER'] = 'SUMMEN';
							
							$this->pdf_tab_g [$a] [$zeile] ['GM_D_S'] = "<b>" . nummer_punkt2komma_t ( $sum_GM_D_S ) . "</b>";
							$this->pdf_tab_g [$a] [$zeile] ['FIX_S'] = "<b>" . nummer_punkt2komma_t ( $sum_FIX_S ) . "</b>";
							
							$z_sum_GM_D_S = $sum_GM_D_S;
							$z_sum_FIX_S = $sum_FIX_S;
							
							$zeile ++;
						}
					}
					
					// $this->pdf_tab_g[$a][$zeile]['MVS'] = $this->et_tab[$a]['MONATE'][$m]['MVS'];
					// $zeile++;
				}
				
				/* Vorletzte Zeile - Summe nach Garantie */
				$this->pdf_tab_g [$a] [$zeile] ['MMJJJJ'] = '----------';
				$this->pdf_tab_g [$a] [$zeile] ['MIETER'] = 'ET-ZEIT';
				$et_sum_GM_D_S = nummer_punkt2komma_t ( $sum_GM_D_S - $z_sum_GM_D_S );
				$et_sum_FIX_S = nummer_punkt2komma_t ( $sum_FIX_S - $z_sum_FIX_S );
				
				$this->pdf_tab_g [$a] [$zeile] ['GM_D_S'] = "<b>$et_sum_GM_D_S</b>";
				$this->pdf_tab_g [$a] [$zeile] ['FIX_S'] = "<b>$et_sum_FIX_S</b>";
				
				/* Letzte ZEile */
				$zeile ++;
				$this->pdf_tab_g [$a] [$zeile] ['MMJJJJ'] = '----------';
				$this->pdf_tab_g [$a] [$zeile] ['MIETER'] = 'GESAMT';
				$this->pdf_tab_g [$a] [$zeile] ['GM_D_S'] = "<b>" . nummer_punkt2komma_t ( $sum_GM_D_S ) . "</b>";
				$this->pdf_tab_g [$a] [$zeile] ['FIX_S'] = "<b>" . nummer_punkt2komma_t ( $sum_FIX_S ) . "</b>";
				$this->pdf_tab_g [$a] [$zeile] ['INS_ANTR'] = "<b>" . nummer_punkt2komma_t ( $sum_INS_ANTR ) . "</b>";
			}
			
			/* Jeden GarantieMonat */
			/*
			 * if($m<$this->et_tab[$a]['GMON']){
			 * if($this->pdf_tab_g[$a][$zeile]['GM_D_S']>0){
			 * $this->pdf_tab_g[$a][$zeile]['EINNAHME'] = 'KU';
			 * }else{
			 * $this->pdf_tab_g[$a][$zeile]['EINNAHME'] = 'BA';
			 * }
			 * }
			 */
			
			// print_r($this);
			// die();
			for($et = 0; $et < $anz_et; $et ++) {
				// if(empty($this->gmon_et)){
				// $this->gmon_et = $this->et_tab[$et]['GMON_OBJ'];
				// }
				$zeilen = count ( $this->pdf_tab_g [$et] );
				$sum_KM_I = 0;
				$sum_INS_ANT = 0;
				$sum_INS_ANTR = 0;
				for($z = 0; $z < $zeilen; $z ++) {
					// ##NUR GARANTIEMONATE BERECHNEN#####
					
					if ($z < $this->gmon_et) {
						/* Mietgarantie diff */
						if (isset ( $this->pdf_tab_g [$et] [$z] ['GM_D_S'] ) && $this->pdf_tab_g [$et] [$z] ['GM_D_S'] > 0) {
							$this->pdf_tab_g [$et] [$z] ['KM_I'] = $this->pdf_tab_g [$et] [$z] ['GM'];
							$this->pdf_tab_g [$et] [$z] ['INS_ANT'] = '0.000F';
							$this->pdf_tab_g [$et] [$z] ['HINWEIS'] = 'CODE_VG_GM';
							
							/* Wenn Mieter gezahlt und in minus */
							if ($this->pdf_tab_g [$et] [$z] ['M_ZB'] > 0 && $this->pdf_tab_g [$et] [$z] ['M_ERG'] < 0) {
								$this->pdf_tab_g [$et] [$z] ['INS_ANT'] = nummer_komma2punkt ( nummer_punkt2komma ( ($this->pdf_tab_g [$et] [$z] ['M_ERG'] - $this->pdf_tab_g [$et] [$z] ['M_SVM']) * - 1 ) );
								$this->pdf_tab_g [$et] [$z] ['HINWEIS'] = 'CODE_VG_GM_1';
							}
							
							/* Wenn Mieter nicht gezahlt und in minus */
							if ($this->pdf_tab_g [$et] [$z] ['M_ZB'] <= 0 && $this->pdf_tab_g [$et] [$z] ['M_ERG'] < 0) {
								$this->pdf_tab_g [$et] [$z] ['INS_ANT'] = $this->pdf_tab_g [$et] [$z] ['KM_S'];
								$this->pdf_tab_g [$et] [$z] ['HINWEIS'] = 'CODE_VG_GM_2';
							}
							
							/* Wenn Mieter im PLUS */
							if (isset ( $this->pdf_tab_g [$et] [$z] ['M_ERG'] ) && $this->pdf_tab_g [$et] [$z] ['M_ERG'] >= 0) {
								$this->pdf_tab_g [$et] [$z] ['INS_ANT'] = '0.000';
								$this->pdf_tab_g [$et] [$z] ['HINWEIS'] = 'CODE_VG_GM_3';
								
								$this->pdf_tab_g [$et] [$z] ['INS_ANTR'] = $sum_INS_ANT - $sum_INS_ANTR;
								$sum_INS_ANTR += $this->pdf_tab_g [$et] [$z] ['INS_ANTR'];
							}
							
							/* Wenn LEER */
							if (! isset ( $this->pdf_tab_g [$et] [$z] ['M_ERG'] )) {
								$this->pdf_tab_g [$et] [$z] ['INS_ANT'] = $this->pdf_tab_g [$et] [$z] ['GM'];
								$this->pdf_tab_g [$et] [$z] ['HINWEIS'] = 'CODE_VG_GM_L';
								
								$this->pdf_tab_g [$et] [$z] ['INS_ANTR'] = '0000.0';
								$sum_INS_ANTR += 0.0;
							}
							
							// $sum_KM_I +=$this->pdf_tab_g[$et][$z]['KM_I'];
							// $sum_INS_ANT +=$this->pdf_tab_g[$et][$z]['INS_ANT'];
						} else {
							$this->pdf_tab_g [$et] [$z] ['KM_I'] = $this->pdf_tab_g [$et] [$z] ['KM_S'];
							$sum_KM_I += $this->pdf_tab_g [$et] [$z] ['KM_I'];
							/* Wenn Mieter gezahlt und in minus */
							if ($this->pdf_tab_g [$et] [$z] ['M_ZB'] > 0 && $this->pdf_tab_g [$et] [$z] ['M_ERG'] < 0) {
								$this->pdf_tab_g [$et] [$z] ['INS_ANT'] = nummer_komma2punkt ( nummer_punkt2komma ( ($this->pdf_tab_g [$et] [$z] ['M_ERG'] - $this->pdf_tab_g [$et] [$z] ['M_SVM']) * - 1 ) );
								$this->pdf_tab_g [$et] [$z] ['HINWEIS'] = 'CODE_NG_MM2';
							}
							/* Wenn Mieter nicht gezahlt und in minus */
							if ($this->pdf_tab_g [$et] [$z] ['M_ZB'] <= 0 && $this->pdf_tab_g [$et] [$z] ['M_ERG'] < 0) {
								$this->pdf_tab_g [$et] [$z] ['INS_ANT'] = $this->pdf_tab_g [$et] [$z] ['KM_S'];
								$this->pdf_tab_g [$et] [$z] ['HINWEIS'] = 'CODE_VGNM';
							}
							
							/* Wenn Mieter im PLUS */
							if (isset ( $this->pdf_tab_g [$et] [$z] ['M_ERG'] ) && $this->pdf_tab_g [$et] [$z] ['M_ERG'] >= 0) {
								$this->pdf_tab_g [$et] [$z] ['INS_ANT'] = '0.000';
								$this->pdf_tab_g [$et] [$z] ['HINWEIS'] = 'CODE_VG';
								
								$this->pdf_tab_g [$et] [$z] ['INS_ANTR'] = $sum_INS_ANT - $sum_INS_ANTR;
								$sum_INS_ANTR += $this->pdf_tab_g [$et] [$z] ['INS_ANTR'];
							}
						}
						$sum_KM_I += $this->pdf_tab_g [$et] [$z] ['KM_I'];
						$sum_INS_ANT += $this->pdf_tab_g [$et] [$z] ['INS_ANT'];
					} // ##ende garantiemonate
					
					/*
					 * ob_clean();
					 * echo '<pre>';
					 * print_r($this);
					 * echo $sum_KM_I;
					 * die();
					 */
					// if(!isset($this->gmon_et)){
					// $this->gmon_et = $this->et_tab[0]['GMON'];
					// }
					
					/* Nur wenn Garantie festgelegt ist */
					if (isset ( $this->gmon_et )) {
						if ($z == $this->gmon_et) {
							$this->pdf_tab_g [$et] [$z] ['KM_I'] = "<b>" . nummer_punkt2komma_t ( $sum_KM_I ) . "</b>";
							$this->pdf_tab_g [$et] [$z] ['INS_ANT'] = "<b>" . nummer_punkt2komma_t ( $sum_INS_ANT ) . "</b>";
							$this->pdf_tab_g [$et] [$z] ['INS_ANTR'] = "<b>" . nummer_punkt2komma_t ( $sum_INS_ANTR ) . "</b>";
							$this->pdf_tab_g [$et] [$z] ['HINWEIS'] = 'ZSX';
						}
					}
					
					/* Monate nach Garantie */
					
					if ($z > $this->gmon_et) {
						/* Mietgarantie diff */
						if (isset ( $this->pdf_tab_g [$et] [$z] ['GM_D_S'] ) && $this->pdf_tab_g [$et] [$z] ['GM_D_S'] > 0) {
							$this->pdf_tab_g [$et] [$z] ['KM_I'] = $this->pdf_tab_g [$et] [$z] ['GM'];
							$this->pdf_tab_g [$et] [$z] ['INS_ANT'] = '-';
							$sum_KM_I += $this->pdf_tab_g [$et] [$z] ['KM_I'];
							$this->pdf_tab_g [$et] [$z] ['HINWEIS'] = 'CODE_NG_GM';
						} else {
							$this->pdf_tab_g [$et] [$z] ['KM_I'] = $this->pdf_tab_g [$et] [$z] ['KM_S'];
							$sum_KM_I += $this->pdf_tab_g [$et] [$z] ['KM_I'];
							/* Wenn Mieter gezahlt und in minus */
							if ($this->pdf_tab_g [$et] [$z] ['M_ZB'] > 0 && $this->pdf_tab_g [$et] [$z] ['M_ERG'] < 0) {
								$this->pdf_tab_g [$et] [$z] ['INS_ANT'] = '-';
								$this->pdf_tab_g [$et] [$z] ['HINWEIS'] = 'CODE_NGBM';
							}
							/* Wenn Mieter nicht gezahlt und in minus */
							if ($this->pdf_tab_g [$et] [$z] ['M_ZB'] <= 0 && $this->pdf_tab_g [$et] [$z] ['M_ERG'] < 0) {
								$this->pdf_tab_g [$et] [$z] ['INS_ANT'] = '-';
								$this->pdf_tab_g [$et] [$z] ['HINWEIS'] = 'CODE_NGM';
								$this->pdf_tab_g [$et] [$z] ['KM_I'] = $this->pdf_tab_g [$et] [$z] ['KM_S'] + $this->pdf_tab_g [$et] [$z] ['M_ERG'];
							}
							
							/* Wenn Mieter im PLUS */
							if (isset ( $this->pdf_tab_g [$et] [$z] ['M_ERG'] ) && $this->pdf_tab_g [$et] [$z] ['M_ERG'] >= 0) {
								$this->pdf_tab_g [$et] [$z] ['INS_ANT'] = '-';
								$this->pdf_tab_g [$et] [$z] ['HINWEIS'] = 'CODE_NGP';
								
								$this->pdf_tab_g [$et] [$z] ['INS_ANTR'] = $sum_INS_ANT - $sum_INS_ANTR;
								$sum_INS_ANTR += $this->pdf_tab_g [$et] [$z] ['INS_ANTR'];
							}
						}
					} // ##ende garantiemonate
				}
				/* Nach Schl�ssel sortieren wegen PDF */
				ksort ( $this->pdf_tab_g [$et] );
			}
			
			// print_r($this->pdf_tab_g);
			// die();
			
			// print_r($this->pdf_tab_g);
			// die();
			
			$pdf = new Cezpdf ( 'a4', 'landscape' );
			$bpdf = new b_pdf ();
			$bpdf->b_header ( $pdf, 'Partner', $_SESSION ['partner_id'], 'landscape', 'pdfclass/fonts/Helvetica.afm', 6 );
			$pdf->ezStopPageNumbers ();
			$gmon_akt = $this->et_tab [0] ['GMON'];
			$gmon_obj = $this->et_tab [0] ['GMON_OBJ'];
			// $gmon_et = $this->et_tab[0]['GMON_ET'];
			$pdf->ezText ( "$e->einheit_kurzname ETID:$et_id", 11 );
			$pdf->ezText ( "GMON HAUS: $this->gmon_obj" );
			$pdf->ezText ( "GMON_ET: $this->gmon_et" );
			$pdf->ezSetDy ( - 20 );
			
			$cols = array (
					'MMJJJJ' => MONAT2,
					'N_TAG' => N_TAG,
					'TAGE' => TAGE,
					'G' => G,
					'LEER' => LEER,
					'WM_SOLL' => WM_SOLL,
					'MWST_S' => MWST_S,
					'NK' => NK,
					'M_ZB' => M_ZB,
					'M_ERG' => M_ERG,
					'M_SVM' => M_SVM,
					'MIETER' => MIETER,
					'GM' => GM,
					'KM_S' => KM_S,
					'GM_D_S' => GM_D_S,
					'FIX_S' => FIX_S,
					'KM_I' => KM_I,
					'INS_ANT' => INS_ANT,
					'INS_ANTR' => INS_R,
					'HINWEIS' => CODE 
			);
			
			for($et = 0; $et < $anz_et; $et ++) {
				$pdf->ezText ( "ET = $et", 14 );
				$pdf->ezTable ( $this->pdf_tab_g [$et], $cols, EINNAHMEN_REPORT . " $weg->eigentuemer_von $datum_bis", array (
						'showHeadings' => 1,
						'shaded' => 1,
						'titleFontSize' => 10,
						'fontSize' => 7,
						'xPos' => 50,
						'xOrientation' => 'right',
						'width' => 750,
						'cols' => array (
								'IHR' => array (
										'justification' => 'right' 
								),
								'HV' => array (
										'justification' => 'right' 
								),
								'REP' => array (
										'justification' => 'right' 
								),
								'AUSZAHLUNG' => array (
										'justification' => 'right' 
								) 
						) 
				) );
				$pdf->ezNewPage ();
			}
			ob_clean (); // ausgabepuffer leeren
			header ( "Content-type: application/pdf" ); // wird von MSIE ignoriert
			$pdf->ezStream ();
		} // end if ET exist
	}
	function saldo_berechnung_et_pdf(&$pdf, $einheit_id) {
		/* Infos zu Einheit */
		$e = new einheit ();
		$e->get_einheit_info ( $einheit_id );
		$gk = new geldkonto_info ();
		$gk->geld_konto_ermitteln ( 'OBJEKT', $e->objekt_id );
		
		/* OBJEKTDATEN */
		/* Garantiemonate Objekt */
		$d = new detail ();
		$garantie_mon_obj = $d->finde_detail_inhalt ( 'Objekt', $e->objekt_id, 'INS-Garantiemonate' );
		if ($garantie_mon_obj) {
			$this->tab ['GARANTIE_OBJ'] = $garantie_mon_obj;
		} else {
			$this->tab ['GARANTIE_OBJ'] = 0;
		}
		
		/* Garantierte Miete */
		/* Garantiemiete */
		$garantie_miete = nummer_komma2punkt ( $d->finde_detail_inhalt ( 'EINHEIT', $einheit_id, 'WEG-KaltmieteINS' ) );
		if ($garantie_miete) {
			$this->tab ['G_MIETE'] = $garantie_miete;
		} else {
			$this->tab ['G_MIETE'] = 0.00;
		}
		
		/* Nutzenlastenwechsel */
		$nl_datum = $d->finde_detail_inhalt ( 'Objekt', $e->objekt_id, 'Nutzen-Lastenwechsel' );
		/* Verwaltungs�bernahme */
		$vu_datum = $d->finde_detail_inhalt ( 'Objekt', $e->objekt_id, 'Verwaltungs�bernahme' );
		// echo "GMU: $garantie_mon_obj NLW: $nl_datum VU: $vu_datum<br>";
		
		/* Alle Eigent�mer */
		$weg = new weg ();
		$et_arr = $weg->get_eigentuemer_arr ( $einheit_id );
		
		if (! is_array ( $et_arr )) {
			fehlermeldung_ausgeben ( "Keine Eigent�mer zu $e->einheit_kurzname" );
		} else {
			// print_r($et_arr);
			$anz_et = count ( $et_arr );
			// echo "Eigent�meranzahl : $anz_et<hr>";
			/* Schleife f�r die ET */
			for($a = 0; $a < $anz_et; $a ++) {
				$et_id = $et_arr [$a] ['ID'];
				$weg->get_eigentumer_id_infos4 ( $et_id );
				
				/* Zeitraum ET */
				if ($weg->eigentuemer_bis = '0000-00-00') {
					$datum_bis = date ( "Y-m-d" );
				} else {
					$datum_bis = $weg->eigentuemer_bis;
				}
				
				/* Objekt WEG to ARRAY */
				$this->tab [$a] = ( array ) $weg;
				/* Monate f�r den ET */
				$monats_arr = $this->monats_array ( $weg->eigentuemer_von, $datum_bis );
				$this->tab [$a] ['MONATE'] = $monats_arr;
				
				/* MV im ZEITRAUM */
				$mv_et_arr = $this->get_mv_et_zeitraum_arr ( $einheit_id, $weg->eigentuemer_von, $datum_bis );
				$this->tab [$a] ['MVS'] = $mv_et_arr;
				
				/* Garantiemonate Eigentuemer */
				$d_et = new detail ();
				$garantie_mon_et = $d_et->finde_detail_inhalt ( 'EIGENTUEMER', $et_id, 'INS-Garantiemonate' );
				if ($garantie_mon_et) {
					$this->tab [$a] ['GARANTIE_ET'] = $garantie_mon_et;
				} else {
					$this->tab [$a] ['GARANTIE_ET'] = 0;
				}
			} // end for
			
			unset ( $weg );
			
			// print_r($this->tab);
			// die();
			// #####################PDF VORBEREITUNG################
			/* Bebuchte Konten finden */
			$bu = new buchen ();
			$kos_typs [] = "Eigentuemer";
			$kos_typs [] = "Einheit";
			$kos_ids [] = $et_id;
			$kos_ids [] = $einheit_id;
			$konten = $bu->get_bebuchte_konten ( $gk->geldkonto_id, $kos_typs, $kos_ids );
			// print_r($konten);
			// die();
			/*
			 * if(is_array($konten)){
			 * print_r($konten);
			 * die("KONTEN");
			 * }
			 */
			
			$anz_et = count ( $this->tab ) - 2;
			
			// echo $anz_et;
			/* Schleife ET */
			
			for($a = 0; $a < $anz_et; $a ++) {
				$et_id = $this->tab [$a] ['eigentuemer_id'];
				$et_name = $this->tab [$a] ['empf_namen'];
				// $this->tab_pdf[$a]['eigentuemer_id'] = $et_id;
				
				if ($this->tab [$a] ['GARANTIE_ET'] > $this->tab ['GARANTIE_OBJ']) {
					$garantie_m = $this->tab [$a] ['GARANTIE_ET'];
				} else {
					$garantie_m = $this->tab ['GARANTIE_OBJ'];
				}
				
				$mon_arr = $this->tab [$a] ['MONATE'];
				$anz_monate = count ( $mon_arr );
				$anz_mvs = count ( $this->tab [$a] ['MVS'] );
				
				$zeile = 0;
				/* Summen */
				$sum_km_soll = 0;
				
				/* Zwischensummen */
				$sum_km_gm = 0; // Summe Garantiemiete
				$sum_km_diff_gm = 0; // Summe Garantiemiete INS DIFFERENZ
				$sum_soll_ausz_r = 0;
				$sum_soll_ausz_b = 0;
				$sum_ist_ausz = 0;
				
				$sum_b_konten = 0;
				$sum_ets = 0;
				$sum_hausgeld = 0;
				/* Schleife Monate */
				for($m = 0; $m < $anz_monate; $m ++) {
					
					$monat = $this->tab [$a] ['MONATE'] [$m] ['MONAT'];
					$jahr = $this->tab [$a] ['MONATE'] [$m] ['JAHR'];
					
					/* Garantiemiete versprochene */
					if ($m == '0') {
						$this->pdf_tab [$a] [$zeile] ['G_MIETE'] = 0;
					} else {
						$this->pdf_tab [$a] [$zeile] ['G_MIETE'] = $this->tab ['G_MIETE'];
						$sum_km_gm += $this->tab ['G_MIETE'];
					}
					/* Schleife Mietvertr�ge */
					for($mvs = 0; $mvs < $anz_mvs; $mvs ++) {
						$mv_id = $this->tab [$a] ['MVS'] [$mvs] ['MIETVERTRAG_ID'];
						
						$mk = new mietkonto ();
						$mk->kaltmiete_monatlich ( $mv_id, $monat, $jahr );
						
						if ($mk->ausgangs_kaltmiete) {
							/* Erste Zeile keine Volle Garantiemiete, sondern nur KM aus MDEF */
							if ($m == '0' && $zeile == 0 && $this->tab ['G_MIETE'] > 0) {
								$this->pdf_tab [$a] [$zeile] ['G_MIETE'] = $mk->ausgangs_kaltmiete;
								$sum_km_gm += $mk->ausgangs_kaltmiete;
							}
							
							// $this->pdf_tab[$a][$zeile]['MONAT'] = $monat;
							// $this->pdf_tab[$a][$zeile]['JAHR'] = $jahr;
							$this->pdf_tab [$a] [$zeile] ['MMJJJJ'] = "$monat.$jahr";
							// echo "SANEL $monat $jahr $mv_id $mk->ausgangs_kaltmiete<br>";
							// $this->pdf_tab[$a][$zeile]['MV_ID'.$mv_id] = $mv_id;
							$mv = new mietvertraege ();
							$mv->get_mietvertrag_infos_aktuell ( $mv_id );
							$this->pdf_tab [$a] [$zeile] ['MIETER'] = $mv->personen_name_string;
							$this->pdf_tab [$a] [$zeile] ['MV_ID'] = $mv_id;
							$this->pdf_tab [$a] [$zeile] ['KM_SOLL'] = $mk->ausgangs_kaltmiete;
							$sum_km_soll += $mk->ausgangs_kaltmiete;
							
							$gom = $this->pdf_tab [$a] [$zeile] ['G_MIETE']; // garatiemiete monat
							                                              // echo "$mk->ausgangs_kaltmiete < $gom";
							if ($mk->ausgangs_kaltmiete < $gom) {
								// die("$mk->ausgangs_kaltmiete < $gom");
								$ins_km_diff = ($gom - $mk->ausgangs_kaltmiete);
								$this->pdf_tab [$a] [$zeile] ['G_DIFF_KM'] = nummer_komma2punkt ( nummer_punkt2komma ( $ins_km_diff ) );
								$sum_km_diff_gm += $ins_km_diff;
							} else {
								// unset($this->pdf_tab[$a][$zeile]['G_MIETE']);
								$this->pdf_tab [$a] [$zeile] ['G_DIFF_KM'] = '0.00';
							}
							
							/* Mietersaldo Monat */
							$mz = new miete ();
							$mz->mietkonto_berechnung_monatsgenau ( $mv_id, $jahr, $monat );
							// echo "$mk->ausgangs_kaltmiete + $mz->erg";
							// echo$mk->ausgangs_kaltmiete + $mz->erg;
							// die();
							
							$this->pdf_tab [$a] [$zeile] ['MTR_SLD'] = $mz->erg;
							$this->pdf_tab [$a] [$zeile] ['MTR_ZB'] = $mz->geleistete_zahlungen;
							$this->pdf_tab [$a] [$zeile] ['MTR_NK'] = $mz->davon_umlagen;
							
							/* Fixkosten Hausgeld oder Formel */
							$hg = new weg ();
							// $hausgeld_soll = $hg->get_summe_kostenkat_monat($monat, $jahr, 'Einheit', $einheit_id, 6030);
							// $hausgeld_soll = $hg->get_summe_kostenkat_gruppe_m2($monat, $jahr, 'Einheit', $einheit_id, 6000);
							$hg->get_wg_info ( $monat, $jahr, 'Einheit', $einheit_id, 'Hausgeld' );
							$hausgeld_soll = $hg->gruppe_erg;
							
							/* Fixkosten nach Formel */
							$hg->get_eigentumer_id_infos4 ( $et_id );
							$hausgeld_soll_f = ($hg->einheit_qm_weg * 0.4) + 30;
							if ($hausgeld_soll_f > $hausgeld_soll) {
								$hausgeld_soll = $hausgeld_soll_f;
							}
							
							$this->pdf_tab [$a] [$zeile] ['HG'] = nummer_komma2punkt ( nummer_punkt2komma ( $hausgeld_soll ) );
							$sum_hausgeld += nummer_komma2punkt ( nummer_punkt2komma ( $hausgeld_soll ) );
							
							/* Auszahlung Garantiezeit */
							if ($m < $garantie_m) {
								/* Auszahlung SOLL */
								if ($this->pdf_tab [$a] [$zeile] ['G_DIFF_KM'] > 0) {
									$this->pdf_tab [$a] [$zeile] ['SOLL_AUSZ_R'] = $this->pdf_tab [$a] [$zeile] ['G_MIETE'];
									$this->pdf_tab [$a] [$zeile] ['SOLL_AUSZ_B'] = $this->pdf_tab [$a] [$zeile] ['G_MIETE'] - $this->pdf_tab [$a] [$zeile] ['HG'];
								} else {
									$this->pdf_tab [$a] [$zeile] ['SOLL_AUSZ_R'] = $mk->ausgangs_kaltmiete;
									$this->pdf_tab [$a] [$zeile] ['SOLL_AUSZ_B'] = $mk->ausgangs_kaltmiete - $this->pdf_tab [$a] [$zeile] ['HG'];
								}
								/* Summen */
								$sum_soll_ausz_r += $this->pdf_tab [$a] [$zeile] ['SOLL_AUSZ_R'];
								$sum_soll_ausz_b += $this->pdf_tab [$a] [$zeile] ['SOLL_AUSZ_B'];
							} else {
								// #######################
								/* Nach der Garantiezeit */
								/* Wenn Differenzen versprochene Miete und tats�chliche Miete */
								// if($this->pdf_tab[$a][$zeile]['G_DIFF_KM']>0){
								// $this->pdf_tab[$a][$zeile]['SOLL_AUSZ_R'] = $this->pdf_tab[$a][$zeile]['G_MIETE'];
								// $this->pdf_tab[$a][$zeile]['SOLL_AUSZ_B'] = $this->pdf_tab[$a][$zeile]['G_MIETE'] - $this->pdf_tab[$a][$zeile]['HG'] - $this->pdf_tab[$a][$zeile]['K_SUM'];
								// }else{
								
								/* Keine Garantiemiete */
								// print_r($mz);
								// die("$monat $jahr");
								
								if (isset ( $this->pdf_tab [$a] [$zeile] ['G_DIFF_KM'] )) {
									$ins_diff_monat = $this->pdf_tab [$a] [$zeile] ['G_DIFF_KM'];
								} else {
									$ins_diff_monat = 0.00;
								}
								
								/* Mietersaldo GUTHABEN ODER AUSGEGELICHEN */
								if ($mz->erg >= 0) {
									/* Keine Schulden im letzten MOnat */
									if ($mz->saldo_vormonat_stand >= 0) {
										$this->pdf_tab [$a] [$zeile] ['SOLL_AUSZ_R'] = $mk->ausgangs_kaltmiete + $ins_diff_monat;
										$this->pdf_tab [$a] [$zeile] ['SOLL_AUSZ_B'] = $mk->ausgangs_kaltmiete + $ins_diff_monat - $this->pdf_tab [$a] [$zeile] ['HG'] - $this->pdf_tab [$a] [$zeile] ['K_SUM'];
									} else {
										/* Schulden im letzten MOnat */
										
										// $pdf_tab[$pdf_z]['KM_IST'] = $mi_arr['zb'] - $mi_arr['erg'] - $nk;
										$this->pdf_tab [$a] [$zeile] ['SOLL_AUSZ_R'] = $mk->ausgangs_kaltmiete + $ins_diff_monat;
										$this->pdf_tab [$a] [$zeile] ['SOLL_AUSZ_B'] = $mk->ausgangs_kaltmiete + $ins_diff_monat - $this->pdf_tab [$a] [$zeile] ['HG'] - $this->pdf_tab [$a] [$zeile] ['K_SUM'];
									}
									
									// if(($wm_soll*-1)<=0){
									// $pdf_tab[$pdf_z]['KM_IST'] = 0.00;
									// }
									
									// $sum_km_ist += $pdf_tab[$pdf_z]['KM_IST'];
								} /*
								   *
								   *
								   *
								   * /*Mietersaldo MINUS
								   */
								if ($mz->erg < 0) {
									
									/* Keine Schulden im letzten MOnat */
									if ($mz->saldo_vormonat_stand >= 0) {
										$this->pdf_tab [$a] [$zeile] ['SOLL_AUSZ_R'] = $mk->ausgangs_kaltmiete + $ins_diff_monat - $this->pdf_tab [$a] [$zeile] ['HG'];
										$this->pdf_tab [$a] [$zeile] ['SOLL_AUSZ_B'] = $mk->ausgangs_kaltmiete + $ins_diff_monat + $mz->erg - $this->pdf_tab [$a] [$zeile] ['HG'] - $this->pdf_tab [$a] [$zeile] ['K_SUM'];
										/* Schulden auch im letzten Monat */
									} else {
										// ##################PR�FEN##############################
										/* Wenn MK abgezahlt, diff auszahlen */
										if (($mz->erg >= $mz->saldo_vormonat_stand)) {
											
											// echo "HIER TEST SCHULD!!!";
											// die("$mk->ausgangs_kaltmiete + $ins_diff_monat + $mz->erg + ($mz->saldo_vormonat*-1);");
											
											$this->pdf_tab [$a] [$zeile] ['SOLL_AUSZ_R'] = $mk->ausgangs_kaltmiete + $ins_diff_monat + $mz->erg + ($mz->saldo_vormonat * - 1);
											$this->pdf_tab [$a] [$zeile] ['SOLL_AUSZ_B'] = $mk->ausgangs_kaltmiete + $ins_diff_monat + $mz->erg + ($mz->saldo_vormonat * - 1) - $this->pdf_tab [$a] [$zeile] ['HG'] - $this->pdf_tab [$a] [$zeile] ['K_SUM'];
											;
										} else {
											/* Wenn der Mieter noch mehr Schulden mach, keine AUSZ */
											
											/* Wenn �berhaupt was gezahlt und h�he als umlagen */
											if ($mz->geleistete_zahlungen > 0 && $mz->geleistete_zahlungen > $mz->davon_umlagen) {
												// $pdf_tab[$pdf_z]['KM_IST'] = $mi_arr['zb'] - $nk;
												$this->pdf_tab [$a] [$zeile] ['SOLL_AUSZ_R'] = $mz->geleistete_zahlungen + $ins_diff_monat - $mz->davon_umlagen + $mz->erg + ($mz->saldo_vormonat_stand * - 1);
												$this->pdf_tab [$a] [$zeile] ['SOLL_AUSZ_B'] = $mz->geleistete_zahlungen + $ins_diff_monat - $mz->davon_umlagen + $mz->erg + ($mz->saldo_vormonat_stand * - 1) - $this->pdf_tab [$a] [$zeile] ['HG'] - $this->pdf_tab [$a] [$zeile] ['K_SUM'];
											} else {
												/* Wenn nicht gezahlt */
												$this->pdf_tab [$a] [$zeile] ['SOLL_AUSZ_R'] = $ins_diff_monat - $this->pdf_tab [$a] [$zeile] ['HG'] - $this->pdf_tab [$a] [$zeile] ['K_SUM'];
												$this->pdf_tab [$a] [$zeile] ['SOLL_AUSZ_B'] = $ins_diff_monat - $this->pdf_tab [$a] [$zeile] ['HG'] - $this->pdf_tab [$a] [$zeile] ['K_SUM'];
											}
										}
									}
									
									// $sum_km_ist += $pdf_tab[$pdf_z]['KM_IST'];
								}
								
								// $this->pdf_tab[$a][$zeile]['SOLL_AUSZ_R'] = $mk->ausgangs_kaltmiete;
								// $this->pdf_tab[$a][$zeile]['SOLL_AUSZ_B'] = $mk->ausgangs_kaltmiete - $this->pdf_tab[$a][$zeile]['HG'];
								// }
								$sum_soll_ausz_r += $this->pdf_tab [$a] [$zeile] ['SOLL_AUSZ_R'];
								$sum_soll_ausz_b += $this->pdf_tab [$a] [$zeile] ['SOLL_AUSZ_B'];
							}
							
							/* Auszahlung IST */
							// $summe_auszahlung = nummer_komma2punkt(nummer_punkt2komma($this->get_kosten_summe_monat('Eigentuemer', $et_id, $gk->geldkonto_id, $jahr, $monat, 5020)));
							// $this->pdf_tab[$a][$zeile]['AUSZ_IST'] = $summe_auszahlung;
							// $sum_ist_ausz+=$summe_auszahlung;
							
							if (is_array ( $konten )) {
								$anz_konten = count ( $konten );
								$kost_sum = 0;
								for($ko = 0; $ko < $anz_konten; $ko ++) {
									$b_konto = $konten [$ko] ['KONTO'];
									$summe_temp_ein = $this->get_kosten_summe_monat ( 'Einheit', $einheit_id, $gk->geldkonto_id, $jahr, $monat, $b_konto );
									$summe_temp_et = $this->get_kosten_summe_monat ( 'Eigentuemer', $et_id, $gk->geldkonto_id, $jahr, $monat, $b_konto );
									$this->pdf_tab [$a] [$zeile] ['K' . $b_konto] = nummer_punkt2komma ( $summe_temp_ein + $summe_temp_et );
									$sum_b_konten += $summe_temp_ein + $summe_temp_et;
									$kost_sum += $summe_temp_ein + $summe_temp_et;
									// $this->pdf_tab[$a][$zeile]['K_SUM'] = nummer_komma2punkt(nummer_punkt2komma($summe_temp_ein+$summe_temp_et));
								}
								$this->pdf_tab [$a] [$zeile] ['K_SUM'] = nummer_komma2punkt ( nummer_punkt2komma ( $kost_sum ) );
							} else {
								// ##die('KEINE KONTEN');
							}
							
							/*
							 * KOstenblock
							 * $summe_kosten_mon = $this->get_kosten_summe_monat('Einheit', $einheit_id, $gk->geldkonto_id, $jahr, $monat, 1023);
							 * $this->pdf_tab[$a][$zeile]['K1023'] = nummer_punkt2komma($summe_kosten_mon);
							 *
							 * $summe_ins_mg = $this->get_kosten_summe_monat('Einheit', $einheit_id, $gk->geldkonto_id, $jahr, $monat, 5500);
							 * $this->pdf_tab[$a][$zeile]['INSMG'] = nummer_punkt2komma($summe_ins_mg);
							 *
							 *
							 * $summe_4180 = $this->get_kosten_summe_monat('Einheit', $einheit_id, $gk->geldkonto_id, $jahr, $monat, 4180);
							 * $this->pdf_tab[$a][$zeile]['K4180'] = nummer_punkt2komma($summe_4180);
							 *
							 * $summe_4280 = $this->get_kosten_summe_monat('Einheit', $einheit_id, $gk->geldkonto_id, $jahr, $monat, 4280);
							 * $this->pdf_tab[$a][$zeile]['K4280'] = nummer_punkt2komma($summe_4280);
							 *
							 *
							 * $summe_4281 = $this->get_kosten_summe_monat('Einheit', $einheit_id, $gk->geldkonto_id, $jahr, $monat, 4281);
							 * $this->pdf_tab[$a][$zeile]['K4281'] = nummer_punkt2komma($summe_4281);
							 *
							 * $summe_4282 = $this->get_kosten_summe_monat('Einheit', $einheit_id, $gk->geldkonto_id, $jahr, $monat, 4282);
							 * $this->pdf_tab[$a][$zeile]['K4282'] = nummer_punkt2komma($summe_4282);
							 *
							 * $summe_5081 = $this->get_kosten_summe_monat('Eigentuemer', $weg_et->eigentuemer_id, $gk->geldkonto_id, $jahr, $monat, 5081);
							 * $this->pdf_tab[$a][$zeile]['K5081'] = nummer_punkt2komma($summe_5081);
							 *
							 * $summe_5010 = $this->get_kosten_summe_monat('Eigentuemer', $weg_et->eigentuemer_id, $gk->geldkonto_id, $jahr, $monat, 5010);
							 * $this->pdf_tab[$a][$zeile]['K5010'] = nummer_punkt2komma($summe_5010);
							 */
							
							$this->pdf_tab [$a] [$zeile] ['ETS'] = nummer_komma2punkt ( nummer_punkt2komma ( $this->pdf_tab [$a] [$zeile] ['SOLL_AUSZ_B'] + $this->pdf_tab [$a] [$zeile] ['K_SUM'] ) );
							$sum_ets += $this->pdf_tab [$a] [$zeile] ['ETS'];
							$this->pdf_tab [$a] [$zeile] ['ETS_P'] = nummer_komma2punkt ( nummer_punkt2komma ( $this->pdf_tab [$a] [$zeile] ['ETS'] + $this->pdf_tab [$a] [$zeile - 1] ['ETS_P'] ) );
							
							// #########
							$zeile ++;
							unset ( $mk );
							unset ( $mv );
							
							/* Zwischensummen bilden */
							if (! isset ( $_REQUEST ['ohne_zsg'] )) {
								if ($m == $garantie_m - 1) {
									$this->pdf_tab [$a] [$zeile] ['MIETER'] = "<b>GARANTIE</b>";
									$this->pdf_tab [$a] [$zeile] ['KM_SOLL'] = "<b>" . nummer_punkt2komma_t ( $sum_km_soll ) . "</b>";
									$this->pdf_tab [$a] [$zeile] ['G_MIETE'] = "<b>" . nummer_punkt2komma_t ( $sum_km_gm ) . "</b>";
									$this->pdf_tab [$a] [$zeile] ['G_DIFF_KM'] = "<b>" . nummer_punkt2komma_t ( $sum_km_diff_gm ) . "</b>";
									$this->pdf_tab [$a] [$zeile] ['SOLL_AUSZ_R'] = "<b>" . nummer_punkt2komma_t ( $sum_soll_ausz_r ) . "</b>";
									$this->pdf_tab [$a] [$zeile] ['SOLL_AUSZ_B'] = "<b>" . nummer_punkt2komma_t ( $sum_soll_ausz_b ) . "</b>";
									$this->pdf_tab [$a] [$zeile] ['K_SUM'] = "<b>" . nummer_punkt2komma_t ( $sum_b_konten ) . "</b>";
									$this->pdf_tab [$a] [$zeile] ['HG'] = "<b>" . nummer_punkt2komma_t ( $sum_hausgeld ) . "</b>";
									$this->pdf_tab [$a] [$zeile] ['ETS_P'] = "<b>" . $this->pdf_tab [$a] [$zeile] ['ETS'] + $this->pdf_tab [$a] [$zeile - 1] ['ETS_P'] . "</b>";
									//
									// $sum_ets=0;
									$zeile ++;
								}
							}
							
							/* Zwischensummen nach Dezemer bilden */
							if ($monat == 12) {
								$this->pdf_tab [$a] [$zeile] ['MIETER'] = "<b>$jahr</b>";
								$this->pdf_tab [$a] [$zeile] ['KM_SOLL'] = "<b>" . nummer_punkt2komma_t ( $sum_km_soll ) . "</b>";
								$sum_km_soll = 0;
								$this->pdf_tab [$a] [$zeile] ['G_MIETE'] = "<b>" . nummer_punkt2komma_t ( $sum_km_gm ) . "</b>";
								$sum_km_gm = 0;
								$this->pdf_tab [$a] [$zeile] ['G_DIFF_KM'] = "<b>" . nummer_punkt2komma_t ( $sum_km_diff_gm ) . "</b>";
								$sum_km_diff_gm = 0;
								$this->pdf_tab [$a] [$zeile] ['SOLL_AUSZ_R'] = "<b>" . nummer_punkt2komma_t ( $sum_soll_ausz_r ) . "</b>";
								$sum_soll_ausz_r = 0;
								$this->pdf_tab [$a] [$zeile] ['SOLL_AUSZ_B'] = "<b>" . nummer_punkt2komma_t ( $sum_soll_ausz_b ) . "</b>";
								$sum_soll_ausz_b = 0;
								// $this->pdf_tab[$a][$zeile]['AUSZ_IST'] = "<b>".nummer_punkt2komma_t($sum_ist_ausz)."</b>";
								
								$this->pdf_tab [$a] [$zeile] ['K_SUM'] = "<b>" . nummer_punkt2komma_t ( $sum_b_konten ) . "</b>";
								$sum_b_konten = 0;
								$this->pdf_tab [$a] [$zeile] ['HG'] = "<b>" . nummer_punkt2komma_t ( $sum_hausgeld ) . "</b>";
								$sum_hausgeld = 0;
								$this->pdf_tab [$a] [$zeile] ['ETS_P'] = "<b>" . $this->pdf_tab [$a] [$zeile] ['ETS'] + $this->pdf_tab [$a] [$zeile - 1] ['ETS_P'] . "</b>";
								//
								// $this->pdf_tab[$a][$zeile]['ETS'] = "<b>".nummer_punkt2komma_t($sum_ets)."</b>";
								// $this->pdf_tab[$a][$zeile]['ETS_P'] = "<b>".nummer_punkt2komma_t($sum_soll_ausz_b + $sum_b_konten)."</b>";
								$zeile ++;
							}
						}
						
						// $mz = new miete();
						// $m_arr =$mz->get_monats_ergebnis($mv_id, $monat,$jahr);
						// $this->tab[$a][]
					}
					
					// $zeile++;
				}
				$this->pdf_tab [$a] [$zeile] ['MIETER'] = "<b>AKTUELL</b>";
				$this->pdf_tab [$a] [$zeile] ['KM_SOLL'] = "<b>" . nummer_punkt2komma_t ( $sum_km_soll ) . "</b>";
				$this->pdf_tab [$a] [$zeile] ['G_MIETE'] = "<b>" . nummer_punkt2komma_t ( $sum_km_gm ) . "</b>";
				$this->pdf_tab [$a] [$zeile] ['G_DIFF_KM'] = "<b>" . nummer_punkt2komma_t ( $sum_km_diff_gm ) . "</b>";
				
				$this->pdf_tab [$a] [$zeile] ['SOLL_AUSZ_R'] = "<b>" . nummer_punkt2komma_t ( $sum_soll_ausz_r ) . "</b>";
				$this->pdf_tab [$a] [$zeile] ['SOLL_AUSZ_B'] = "<b>" . nummer_punkt2komma_t ( $sum_soll_ausz_b ) . "</b>";
				// $this->pdf_tab[$a][$zeile]['AUSZ_IST'] = "<b>".nummer_punkt2komma_t($sum_ist_ausz)."</b>";
				$this->pdf_tab [$a] [$zeile] ['K_SUM'] = "<b>" . nummer_punkt2komma_t ( $sum_b_konten ) . "</b>";
				// $this->pdf_tab[$a][$zeile]['ETS'] = "<b>".nummer_punkt2komma_t($sum_ets)."</b>";
				// $this->pdf_tab[$a][$zeile]['ETS_P'] = "<b>".nummer_punkt2komma_t($sum_soll_ausz_b + $sum_b_konten)."</b>";
				$this->pdf_tab [$a] [$zeile] ['ETS_P'] = "<b>" . $this->pdf_tab [$a] [$zeile] ['ETS'] + $this->pdf_tab [$a] [$zeile - 1] ['ETS_P'] . "</b>";
			}
			
			// print_r($this->pdf_tab);
			// die();
			// $pdf = new Cezpdf('a4', 'landscape');
			// $bpdf = new b_pdf;
			// $bpdf->b_header($pdf, 'Partner', $_SESSION['partner_id'], 'landscape', 'pdfclass/fonts/Helvetica.afm', 6);
			// $pdf->ezStopPageNumbers();
			
			$cols = array (
					'MON' => MONAT2,
					'WM_SOLL' => WM,
					'NK_SOLL' => NK,
					'ZB_M' => 'ZB_M',
					'SALDO_M' => 'SALDO_M',
					'KM_SOLL' => KM_SOLL,
					'KM_IST' => KM_IST,
					'KOST_ALLE' => 'KOST ALLE',
					'K5081' => 'VZN',
					'K5010' => 'EINZAHLUNG',
					'HG_Z' => 'HG ZAHLUNG',
					'SOLL_AUSZ' => 'AUSZ SOLL',
					'AUSZAHLUNG' => 'AUSZAHLUNG IST',
					'SALDO_MET' => 'SALDO M',
					'PERIOD' => 'PERIOD' 
			);
			// }
			
			for($a = 0; $a < $anz_et; $a ++) {
				$pdf->ezText ( "$et_name $et_id", 16 );
				$pdf->ezSetDy ( - 5 ); // abstand
				                   // $pdf->ezTable($this->pdf_tab[$a]);
				$pdf->ezTable ( $this->pdf_tab [$a], null, EINNAHMEN_REPORT . " $datum_von $datum_bis", array (
						'showHeadings' => 1,
						'shaded' => 1,
						'titleFontSize' => 10,
						'fontSize' => 7,
						'xPos' => 50,
						'xOrientation' => 'right',
						'width' => 750,
						'cols' => array (
								'IHR' => array (
										'justification' => 'right' 
								),
								'HV' => array (
										'justification' => 'right' 
								),
								'REP' => array (
										'justification' => 'right' 
								),
								'AUSZAHLUNG' => array (
										'justification' => 'right' 
								) 
						) 
				) );
			}
			
			/* Legende */
			if (is_array ( $konten )) {
				$kr = new kontenrahmen ();
				$kr_id = $kr->get_kontenrahmen ( 'GELDKONTO', $gk->geldkonto_id );
				
				$anz_konten = count ( $konten );
				$pdf->ezSetDy ( - 20 ); // abstand
				$string = '';
				for($ko = 0; $ko < $anz_konten; $ko ++) {
					$b_konto = $konten [$ko] ['KONTO'];
					$kr->konto_informationen2 ( $b_konto, $kr_id );
					$string .= "K$b_konto - $kr->konto_bezeichnung\n";
				}
				$pdf->ezText ( "<b>$string</b>", 9 );
			}
			
			// ob_clean(); //ausgabepuffer leeren
			// header("Content-type: application/pdf"); // wird von MSIE ignoriert
			// $pdf->ezStream();
			unset ( $this->pdf_tab );
			unset ( $this->tab );
			unset ( $konten );
			return $pdf;
		}
	}
	function salden_pdf_objekt(&$pdf, $objekt_id) {
		$o = new objekt ();
		$arr = $o->einheiten_objekt_arr ( $objekt_id );
		$anz_e = count ( $arr );
		for($a = 0; $a < $anz_e; $a ++) {
			$einheit_id = $arr [$a] ['EINHEIT_ID'];
			$this->saldo_berechnung_et_pdf ( $pdf, $einheit_id );
			$pdf->ezNewPage ();
		}
	}
	function pdf_income_reports2($objekt_id, $jahr = null) {
		/* Garantiemonate Objekt */
		$d = new detail ();
		$garantie_mon_obj = $d->finde_detail_inhalt ( 'Objekt', $objekt_id, 'INS-Garantiemonate' );
		
		// if($jahr==null){
		// $jahr=date("Y")-1;
		// }
		// $f_jahr = $jahr+1;
		/* Nutzenlastenwechsel */
		$d = new detail ();
		$nl_datum = $d->finde_detail_inhalt ( 'Objekt', $objekt_id, 'Nutzen-Lastenwechsel' );
		$vu_datum = $d->finde_detail_inhalt ( 'Objekt', $objekt_id, 'Verwaltungs�bernahme' );
		
		if (! empty ( $vu_datum )) {
			$vu_datum_arr = explode ( '.', $vu_datum );
			$vu_tag = $vu_datum_arr [0];
			$vu_monat = $vu_datum_arr [1];
			$vu_jahr = $vu_datum_arr [2];
			$vu_serial = "$vu_jahr$vu_monat$vu_tag";
		}
		
		if (! empty ( $nl_datum )) {
			$nl_datum_arr = explode ( '.', $nl_datum );
			$nl_tag = $nl_datum_arr [0];
			$nl_monat = $nl_datum_arr [1];
			$nl_jahr = $nl_datum_arr [2];
			$nl_serial = "$nl_jahr$nl_monat$nl_tag";
		}
		
		if (empty ( $nl_datum ) && empty ( $vu_datum )) {
			$datum_von = "$jahr-01-01";
		}
		
		if ($vu_serial > $nl_serial) {
			$datum_von = "$vu_jahr-$vu_monat-$vu_tag";
		} else {
			$datum_von = "$nl_jahr-$nl_monat-$nl_tag";
		}
		
		if (empty ( $datum_von )) {
			$datum_von = "$jahr-01-01";
		}
		
		if ($jahr >= date ( "Y" )) {
			$jahr = date ( "Y" );
			$akt_jahr = date ( "Y" );
			$akt_monat = date ( "m" );
			$ltag = letzter_tag_im_monat ( $akt_monat, $akt_jahr );
			$datum_bis = "$akt_jahr-$akt_monat-$ltag";
		} else {
			$datum_bis = "$jahr-12-31";
		}
		
		if (isset ( $_REQUEST ['lang'] ) && $_REQUEST ['lang'] == 'en') {
			define ( "EINNAHMEN_REPORT", "Income report" );
			define ( "OBJEKT", "Object" );
			define ( "WOHNUNG", "Flat" );
			define ( "EIGENTUEMER", "Owner" );
			define ( "LAGE", "Location" );
			define ( "TYP", "Type" );
			define ( "FLAECHE", "Living space" );
			
			define ( "SUMMEN", "sum [�]" );
			define ( "MONAT2", "month" );
			define ( "IHR", "for maintenance [0,40�*m�]" );
			define ( "HV", "managing fee [�]" );
			define ( "REP", "repairs [�]" );
			define ( "AUSZAHLUNG", "actual transfer [�]" );
			$lang = 'en';
		} else {
			define ( "EINNAHMEN_REPORT", "Einnahmen�bersicht" );
			define ( "OBJEKT", "Objekt" );
			define ( "WOHNUNG", "Wohnung" );
			define ( "EIGENTUEMER", "Eigent�mer" );
			define ( "LAGE", "Lage" );
			define ( "TYP", "Typ" );
			define ( "FLAECHE", "Fl�che" );
			
			define ( "SUMMEN", "Summen [�]" );
			define ( "MONAT2", "Monat" );
			define ( "KALTMIETE", "NET RENT [�]" );
			define ( "IHR", "Instadh. [0,40�*m�]" );
			define ( "HV", "HV-Geb�hr [�]" );
			define ( "REP", "Reparaturen [�]" );
			define ( "AUSZAHLUNG", "Auszahlung [�]" );
			// $cols = array('MONAT2'=>MONAT, 'IHR'=>IHR, 'HV'=>HV,'REP'=>REP,'AUSZAHLUNG'=>AUSZAHLUNG);
			$lang = 'de';
		}
		
		$gk = new geldkonto_info ();
		$gk->geld_konto_ermitteln ( 'OBJEKT', $objekt_id );
		if (! $gk->geldkonto_id) {
			die ( 'GELDKONTO fehlt' );
		}
		
		// echo "$objekt_id $jahr";
		$weg = new weg ();
		$ein_arr = $weg->einheiten_weg_tabelle_arr ( $objekt_id );
		// echo '<pre>';
		// print_r($ein_arr);
		if (is_array ( $ein_arr )) {
			
			$pdf = new Cezpdf ( 'a4', 'landscape' );
			$bpdf = new b_pdf ();
			$bpdf->b_header ( $pdf, 'Partner', $_SESSION ['partner_id'], 'landscape', 'pdfclass/fonts/Helvetica.afm', 6 );
			$pdf->ezStopPageNumbers ();
			
			$anz_e = count ( $ein_arr );
			
			for($a = 0; $a < $anz_e; $a ++) {
				$einheit_id = $ein_arr [$a] ['EINHEIT_ID'];
				// echo "$einheit_id<br>";
				
				$weg_et = new weg ();
				if (isset ( $weg_et->eigentuemer_id )) {
					unset ( $weg_et->eigentuemer_id );
				}
				$weg_et->get_last_eigentuemer ( $einheit_id );
				$weg_et->get_eigentumer_id_infos3 ( $weg_et->eigentuemer_id );
				// if($weg->einheit_typ =='Wohnraum'){
				if (isset ( $weg_et->eigentuemer_id )) {
					/* Garantiemonate Eigentuemer */
					$d_et = new detail ();
					$garantie_mon_et = $d_et->finde_detail_inhalt ( 'EIGENTUEMER', $weg_et->eigentuemer_id, 'INS-Garantiemonate' );
					
					if ($garantie_mon_et > $garantie_mon_obj) {
						$garantie_mon = $garantie_mon_et;
					} else {
						$garantie_mon = $garantie_mon_obj;
					}
					
					$pdf->ezText ( EINNAHMEN_REPORT . " $datum_von $datum_bis", 14 );
					$pdf->ezText ( OBJEKT . ": $weg_et->haus_strasse $weg_et->haus_nummer, $weg_et->haus_plz  $weg_et->haus_stadt", 11 );
					$pdf->ezSetDy ( - 7 );
					$pdf->ezText ( WOHNUNG . ": $weg_et->einheit_kurzname " . LAGE . ": $weg_et->einheit_lage", 11 );
					$pdf->ezText ( FLAECHE . ": $weg_et->einheit_qm_weg m�", 11 );
					$pdf->ezSetDy ( - 10 );
					$pdf->ezText ( EIGENTUEMER . ":\n$weg_et->empf_namen_u", 11 );
					$pdf->ezText ( NUTZENLASTENWECHSEL . ": <b>$nl_datum</b>", 11 );
					$pdf->ezText ( GARANTIEOBJ . ": <b>$garantie_mon_obj</b>", 11 );
					$pdf->ezText ( GARANTIEET . ": <b>$garantie_mon_et</b>", 11 );
					$pdf->ezText ( GARANTIE . ": <b>$garantie_mon</b>", 11 );
					$pdf->ezText ( GARANTIEMIETE . ": <b>$garantie_miete</b>", 11 );
					
					// $m_arr= $weg->monatsarray_erstellen($weg_et->eigentuemer_von,$datum_bis);
					
					if (isset ( $_REQUEST ['bisheute'] )) {
						$datum_bis = date ( "Y-m-d" );
					}
					
					$m_arr = $weg->monatsarray_erstellen ( $datum_von, $datum_bis );
					// print_r($m_arr);
					// die();
					$anz_m = count ( $m_arr );
					$sum_km = 0;
					$sum_ihr = 0;
					$sum_hv = 0;
					$sum_rep = 0;
					$sum_auszahlung = 0;
					
					$sum_km_ist = 0;
					$sum_soll_auszahlung = 0;
					
					$sum_garantie_miete = 0;
					$sum_ins_garantie_km_diff = 0;
					
					$pdf_z = 0;
					for($b = 0; $b < $anz_m; $b ++) {
						// $li = new listen();
						$monat = $m_arr [$b] ['monat'];
						$jahr = $m_arr [$b] ['jahr'];
						// echo "$monat $jahr<br>";
						// die();
						// $kost_arr = $li->get_kosten_arr('Einheit', $weg->einheit_id, $monat, $jahr, $gk->geldkonto_id,1023);
						// $summe_kosten_mon = $this->get_kosten_summe_monat('Einheit', $einheit_id, $monat, $jahr, $gk->geldkonto_id,1023);
						$summe_kosten_mon = $this->get_kosten_summe_monat ( 'Einheit', $einheit_id, $gk->geldkonto_id, $jahr, $monat, 1023 );
						$garantie_miete = nummer_komma2punkt ( $d_et->finde_detail_inhalt ( 'EINHEIT', $einheit_id, 'WEG-KaltmieteINS' ) );
						$pdf_tab [$pdf_z] ['GARANTY_KM'] = $garantie_miete;
						$sum_garantie_miete += $garantie_miete;
						
						$summe_ins_mg = $this->get_kosten_summe_monat ( 'Einheit', $einheit_id, $gk->geldkonto_id, $jahr, $monat, 5500 );
						/* INS MAKLER GEB�HR */
						$pdf_tab [$pdf_z] ['INSMG'] = nummer_punkt2komma ( $summe_ins_mg );
						
						/* Andere Kosten */
						$summe_4180 = $this->get_kosten_summe_monat ( 'Einheit', $einheit_id, $gk->geldkonto_id, $jahr, $monat, 4180 );
						$pdf_tab [$pdf_z] ['K4180'] = nummer_punkt2komma ( $summe_4180 );
						
						$summe_4280 = $this->get_kosten_summe_monat ( 'Einheit', $einheit_id, $gk->geldkonto_id, $jahr, $monat, 4280 );
						$pdf_tab [$pdf_z] ['K4280'] = nummer_punkt2komma ( $summe_4280 );
						
						$summe_4281 = $this->get_kosten_summe_monat ( 'Einheit', $einheit_id, $gk->geldkonto_id, $jahr, $monat, 4281 );
						$pdf_tab [$pdf_z] ['K4281'] = nummer_punkt2komma ( $summe_4281 );
						
						$summe_4282 = $this->get_kosten_summe_monat ( 'Einheit', $einheit_id, $gk->geldkonto_id, $jahr, $monat, 4282 );
						$pdf_tab [$pdf_z] ['K4282'] = nummer_punkt2komma ( $summe_4282 );
						
						$summe_5081 = $this->get_kosten_summe_monat ( 'Eigentuemer', $weg_et->eigentuemer_id, $gk->geldkonto_id, $jahr, $monat, 5081 );
						$pdf_tab [$pdf_z] ['K5081'] = nummer_punkt2komma ( $summe_5081 );
						
						$summe_5010 = $this->get_kosten_summe_monat ( 'Eigentuemer', $weg_et->eigentuemer_id, $gk->geldkonto_id, $jahr, $monat, 5010 );
						$pdf_tab [$pdf_z] ['K5010'] = nummer_punkt2komma ( $summe_5010 );
						
						// if($weg_et->eigentuemer_id=='423'){
						// echo "ET: $monat $jahr $weg_et->eigentuemer_id $summe_5010<br>";
						// }
						
						/* Selbstnutzer */
						$summe_6000 = $this->get_kosten_summe_monat ( 'Eigentuemer', $weg_et->eigentuemer_id, $gk->geldkonto_id, $jahr, $monat, 6000 );
						$pdf_tab [$pdf_z] ['K6000'] = nummer_punkt2komma ( $summe_6000 );
						
						$summe_6010 = $this->get_kosten_summe_monat ( 'Eigentuemer', $weg_et->eigentuemer_id, $gk->geldkonto_id, $jahr, $monat, 6010 );
						$pdf_tab [$pdf_z] ['K6010'] = nummer_punkt2komma ( $summe_6010 );
						
						$summe_6020 = $this->get_kosten_summe_monat ( 'Eigentuemer', $weg_et->eigentuemer_id, $gk->geldkonto_id, $jahr, $monat, 6020 );
						$pdf_tab [$pdf_z] ['K6020'] = nummer_punkt2komma ( $summe_6020 );
						
						$summe_6030 = $this->get_kosten_summe_monat ( 'Eigentuemer', $weg_et->eigentuemer_id, $gk->geldkonto_id, $jahr, $monat, 6030 );
						$pdf_tab [$pdf_z] ['K6030'] = nummer_punkt2komma ( $summe_6030 );
						
						$summe_6060 = $this->get_kosten_summe_monat ( 'Eigentuemer', $weg_et->eigentuemer_id, $gk->geldkonto_id, $jahr, $monat, 6060 );
						$pdf_tab [$pdf_z] ['K6060'] = nummer_punkt2komma ( $summe_6060 );
						
						$sum_rep += $summe_kosten_mon;
						// $pdf->ezText("MOnat $monat.$jahr Kosten $summe_kosten_mon", 11);
						
						$monat_name = monat2name ( $monat, $lang );
						$pdf_tab [$pdf_z] ['MONAT_N'] = $monat_name;
						$pdf_tab [$pdf_z] ['MONAT2'] = "$monat_name $jahr";
						$pdf_tab [$pdf_z] ['MON'] = "$monat.$jahr";
						
						$mv_id = $this->get_mv_zeitraum_arr ( $weg_et->einheit_id, $monat, $jahr );
						if ($mv_id) {
							$mk = new mietkonto ();
							$mk->kaltmiete_monatlich ( $mv_id, $monat, $jahr );
							$pdf_tab [$pdf_z] ['KM_SOLL'] = nummer_punkt2komma_t ( $mk->ausgangs_kaltmiete );
							// $sum_km+=$mk->ausgangs_kaltmiete;
							
							$mz = new miete ();
							$mi_arr = $mz->get_monats_ergebnis ( $mv_id, $monat, $jahr );
							$m_soll_arr = explode ( '|', $mi_arr ['soll'] );
							// print_r($m_soll_arr);
							// print_r($mi_arr);
							
							if (isset ( $m_soll_arr [1] )) {
								$wm_soll = $m_soll_arr [0];
								$wm_soll_mwst = $m_soll_arr [1];
							} else {
								$wm_soll = $mi_arr ['soll'];
								$wm_soll_mwst = '0.00';
							}
							
							if (($wm_soll * - 1) > 0) {
								$nk = ($wm_soll * - 1) - $mk->ausgangs_kaltmiete;
								$sum_km += $mk->ausgangs_kaltmiete;
								
								if ($garantie_miete > $mk->ausgangs_kaltmiete && $mk->ausgangs_kaltmiete > 0) {
									$pdf_tab [$pdf_z] ['KM_INS_DIFF'] = $garantie_miete - $mk->ausgangs_kaltmiete;
									$sum_ins_garantie_km_diff += $garantie_miete - $mk->ausgangs_kaltmiete;
								}
							} else {
								$nk = 0.00;
								$pdf_tab [$pdf_z] ['KM_SOLL'] = '0,00';
							}
							
							$pdf_tab [$pdf_z] ['NK_SOLL'] = nummer_punkt2komma_t ( $nk );
							$pdf_tab [$pdf_z] ['WM_SOLL'] = nummer_punkt2komma_t ( $wm_soll * - 1 );
							$pdf_tab [$pdf_z] ['MWST_SOLL'] = nummer_punkt2komma_t ( $wm_soll_mwst );
							
							$pdf_tab [$pdf_z] ['SALDO_M'] = $mi_arr ['erg'];
							$pdf_tab [$pdf_z] ['ZB_M'] = $mi_arr ['zb'];
							/* Mietersaldo GUTHABEN ODER AUSGEGELICHEN */
							if ($mi_arr ['erg'] >= 0) {
								/* Keine Schulden im letzten MOnat */
								if ($mi_arr ['saldo_vormonat'] >= 0) {
									$pdf_tab [$pdf_z] ['KM_IST'] = $mk->ausgangs_kaltmiete;
								} else {
									$pdf_tab [$pdf_z] ['KM_IST'] = $mi_arr ['zb'] - $mi_arr ['erg'] - $nk;
								}
								
								if (($wm_soll * - 1) <= 0) {
									$pdf_tab [$pdf_z] ['KM_IST'] = 0.00;
								}
								
								$sum_km_ist += $pdf_tab [$pdf_z] ['KM_IST'];
							} /*
							   *
							   * /*Mietersaldo MINUS
							   */
							if ($mi_arr ['erg'] < 0) {
								
								/* Keine Schulden im letzten MOnat */
								if ($mi_arr ['saldo_vormonat'] >= 0) {
									$pdf_tab [$pdf_z] ['KM_IST'] = $mk->ausgangs_kaltmiete + $mi_arr ['erg'];
									/* Schulden auch im letzten Monat */
								} else {
									// if(($mi_arr['erg']*-1)>=$mk->ausgangs_kaltmiete*2){
									// $pdf_tab[$pdf_z]['KM_IST'] ='0.00';
									// }else{
									// $pdf_tab[$pdf_z]['KM_IST'] = $mk->ausgangs_kaltmiete + $mi_arr['erg'] + ($mi_arr['saldo_vormonat']*-1);
									// }
									/* Wenn MK abgezhalt, diff auszahlen */
									if (($mi_arr ['erg'] >= $mi_arr ['saldo_vormonat'])) {
										$pdf_tab [$pdf_z] ['KM_IST'] = $mk->ausgangs_kaltmiete + $mi_arr ['erg'] + ($mi_arr ['saldo_vormonat'] * - 1);
									} else {
										/* Wenn der Mieter noch mehr Schulden mach, keine AUSZ */
										
										/* Wenn �berhaupt was gezahlt */
										if ($mi_arr ['zb'] > 0) {
											$pdf_tab [$pdf_z] ['KM_IST'] = $mi_arr ['zb'] - $nk;
										} else {
											/* Wenn nicht gezahle */
											$pdf_tab [$pdf_z] ['KM_IST'] = '0.00';
										}
									}
								}
								
								$sum_km_ist += $pdf_tab [$pdf_z] ['KM_IST'];
							}
						} else {
							$pdf_tab [$pdf_z] ['KM_SOLL'] = '0.00';
							$pdf_tab [$pdf_z] ['NK_SOLL'] = '0.00';
							$pdf_tab [$pdf_z] ['WM_SOLL'] = '0.00';
							$pdf_tab [$pdf_z] ['MWST_SOLL'] = '0.00';
						}
						
						$pdf_tab [$pdf_z] ['IHR'] = nummer_punkt2komma ( $weg_et->einheit_qm_weg * - 0.4 );
						$sum_ihr += nummer_komma2punkt ( nummer_punkt2komma ( $weg_et->einheit_qm_weg * - 0.4 ) );
						$pdf_tab [$pdf_z] ['HV'] = nummer_punkt2komma ( - 30.00 );
						$sum_hv += nummer_komma2punkt ( nummer_punkt2komma ( - 30.00 ) );
						$pdf_tab [$pdf_z] ['REP'] = nummer_punkt2komma ( $summe_kosten_mon );
						
						$summe_auszahlung = $this->get_kosten_summe_monat ( 'Eigentuemer', $weg_et->eigentuemer_id, $gk->geldkonto_id, $jahr, $monat, 5020 );
						$pdf_tab [$pdf_z] ['AUSZAHLUNG'] = nummer_punkt2komma ( $summe_auszahlung * - 1 );
						$sum_auszahlung += nummer_komma2punkt ( nummer_punkt2komma ( $summe_auszahlung * - 1 ) );
						
						$pdf_tab [$pdf_z] ['SOLL_AUSZ'] = $pdf_tab [$pdf_z] ['KM_IST'] + nummer_komma2punkt ( $pdf_tab [$pdf_z] ['IHR'] ) + nummer_komma2punkt ( $pdf_tab [$pdf_z] ['HV'] ) + nummer_komma2punkt ( $pdf_tab [$pdf_z] ['REP'] ) + nummer_komma2punkt ( $pdf_tab [$pdf_z] ['INSMG'] );
						
						$pdf_tab [$pdf_z] ['SOLL_AUSZ'] += nummer_komma2punkt ( $pdf_tab [$pdf_z] ['K4180'] ) + nummer_komma2punkt ( $pdf_tab [$pdf_z] ['K4280'] ) + nummer_komma2punkt ( $pdf_tab [$pdf_z] ['K4281'] ) + nummer_komma2punkt ( $pdf_tab [$pdf_z] ['K4282'] ) + nummer_komma2punkt ( $pdf_tab [$pdf_z] ['K5081'] ) + nummer_komma2punkt ( $pdf_tab [$pdf_z] ['K5010'] );
						$pdf_tab [$pdf_z] ['HG_Z'] += nummer_komma2punkt ( $pdf_tab [$pdf_z] ['K6000'] ) + nummer_komma2punkt ( $pdf_tab [$pdf_z] ['K6010'] ) + nummer_komma2punkt ( $pdf_tab [$pdf_z] ['K6020'] ) + nummer_komma2punkt ( $pdf_tab [$pdf_z] ['K6030'] ) + nummer_komma2punkt ( $pdf_tab [$pdf_z] ['K6060'] );
						
						$sum_soll_auszahlung += $pdf_tab [$pdf_z] ['SOLL_AUSZ'];
						// $pdf->eztable($kost_arr);
						// unset($kost_arr);
						
						/* Zwischensumme ET nach Garantiezeit */
						if (($b + 1) == $garantie_mon && $garantie_mon > 0) {
							$pdf_z ++;
							$pdf_tab [$pdf_z] ['KM_SOLL'] = "<b>" . nummer_punkt2komma_t ( $sum_km ) . "</b>";
							$pdf_tab [$pdf_z] ['KM_IST'] = "<b>" . nummer_punkt2komma_t ( $sum_km_ist ) . "</b>";
							if (! $garantie_miete) {
								$pdf_tab [$pdf_z] ['INS_DIFF'] = "<b>" . nummer_punkt2komma_t ( $sum_km_ist - $sum_km ) . "</b>";
								$pdf_tab_ergebnis [$weg_et->eigentuemer_id] ['INS_DIFF'] = $sum_km_ist - $sum_km;
							} else {
								$pdf_tab [$pdf_z] ['INS_DIFF'] = "<b>" . nummer_punkt2komma_t ( $sum_km_ist - $sum_garantie_miete ) . "</b>";
								$pdf_tab_ergebnis [$weg_et->eigentuemer_id] ['INS_DIFF'] = $sum_km_ist - $sum_garantie_miete;
							}
							$pdf_tab_ergebnis [$weg_et->eigentuemer_id] ['ET'] = $weg_et->eigentuemer_id;
							$pdf_tab_ergebnis [$weg_et->eigentuemer_id] ['EINHEIT'] = $weg_et->einheit_kurzname;
						}
						$pdf_z ++;
					} // END FOR MONATSSCHLEIFE
					
					$pdf_tab [$pdf_z] ['MONAT2'] = "<b>" . SUMMEN . "</b>";
					$pdf_tab [$pdf_z] ['KM_SOLL'] = "<b>" . nummer_punkt2komma_t ( $sum_km ) . "</b>";
					$pdf_tab [$pdf_z] ['IHR'] = "<b>" . nummer_punkt2komma_t ( $sum_ihr ) . "</b>";
					$pdf_tab [$pdf_z] ['HV'] = "<b>" . nummer_punkt2komma_t ( $sum_hv ) . "</b>";
					$pdf_tab [$pdf_z] ['REP'] = "<b>" . nummer_punkt2komma_t ( $sum_rep ) . "</b>";
					$pdf_tab [$pdf_z] ['AUSZAHLUNG'] = "<b>" . nummer_punkt2komma_t ( $sum_auszahlung ) . "</b>";
					
					$pdf_tab [$pdf_z] ['KM_IST'] = "<b>" . nummer_punkt2komma_t ( $sum_km_ist ) . "</b>";
					$pdf_tab [$pdf_z] ['SOLL_AUSZ'] = "<b>" . nummer_punkt2komma_t ( $sum_soll_auszahlung ) . "</b>";
					
					$pdf_tab [$pdf_z] ['GARANTY_KM'] = "<b>" . nummer_punkt2komma_t ( $sum_garantie_miete ) . "</b>";
					$pdf_tab [$pdf_z] ['KM_INS_DIFF'] = "<b>" . nummer_punkt2komma_t ( $sum_ins_garantie_km_diff ) . "</b>";
					
					$pdf->ezSetDy ( - 20 );
					
					/* SALDENBILDUNG LETZTE SPALTE */
					$anz_tab = count ( $pdf_tab );
					$saldo_et_jahr = 0;
					$sum_ins_mg = 0;
					
					$summe_aller_kosten = 0;
					$summe_einzahlung5010 = 0;
					for($s = 0; $s < $anz_tab - 1; $s ++) {
						/* Nicht in Garantiemonaten */
						if ($s != $garantie_mon) {
							
							$soll_ausz = $pdf_tab [$s] ['SOLL_AUSZ'];
							$ist_ausz = nummer_komma2punkt ( $pdf_tab [$s] ['AUSZAHLUNG'] );
							$diff = $soll_ausz - $ist_ausz;
							$pdf_tab [$s] ['SALDO_MET'] = nummer_punkt2komma ( $diff );
							$saldo_et_jahr += $diff;
							$pdf_tab [$s] ['PERIOD'] = nummer_punkt2komma ( $saldo_et_jahr );
							
							$pdf_tab [$s] ['SOLL_AUSZ'] = nummer_punkt2komma ( $pdf_tab [$s] ['SOLL_AUSZ'] );
							
							$sum_ins_mg += nummer_komma2punkt ( $pdf_tab [$s] ['INSMG'] );
							// $summe_aller_kosten += nummer_komma2punkt($pdf_tab[$s]['HV']) + nummer_komma2punkt($pdf_tab[$s]['IHR']) + nummer_komma2punkt($pdf_tab[$s]['REP']) + nummer_komma2punkt($pdf_tab[$s]['INSMG']);
							$pdf_tab [$s] ['KOST_ALLE'] = nummer_komma2punkt ( $pdf_tab [$s] ['HV'] ) + nummer_komma2punkt ( $pdf_tab [$s] ['IHR'] ) + nummer_komma2punkt ( $pdf_tab [$s] ['REP'] ) + nummer_komma2punkt ( $pdf_tab [$s] ['INSMG'] );
							$pdf_tab [$s] ['KOST_ALLE'] += nummer_komma2punkt ( $pdf_tab [$s] ['K4180'] ) + nummer_komma2punkt ( $pdf_tab [$s] ['K4280'] ) + nummer_komma2punkt ( $pdf_tab [$s] ['K4281'] ) + nummer_komma2punkt ( $pdf_tab [$s] ['K4282'] );
							$summe_aller_kosten += $pdf_tab [$s] ['KOST_ALLE'];
							$summe_einzahlung5010 = nummer_komma2punkt ( $pdf_tab [$s] ['K5010'] );
						} else {
							// $saldo_et_jahr = 0;
						}
					}
					
					$pdf_tab [$s + 1] ['K5010'] = "<b>" . nummer_punkt2komma_t ( $summe_einzahlung5010 ) . "</b>";
					$pdf_tab [$s + 1] ['INSMG'] = "<b>" . nummer_punkt2komma_t ( $sum_ins_mg ) . "</b>";
					$pdf_tab [$s + 1] ['KOST_ALLE'] = "<b>" . nummer_punkt2komma_t ( $summe_aller_kosten ) . "</b>";
					
					$pdf_tab [$s + 2] ['PERIOD'] = nummer_punkt2komma ( $saldo_et_jahr );
					$pdf_tab [$s + 2] ['SALDO_MET'] = "<b>Saldo $jahr</b>";
					
					if (isset ( $_REQUEST ['big'] )) {
						$cols = array (
								'MON' => MONAT2,
								'WM_SOLL' => WM,
								'NK_SOLL' => NK,
								'ZB_M' => 'ZB_M',
								'SALDO_M' => 'SALDO_M',
								'KM_SOLL' => KM_SOLL,
								'KM_IST' => KM_IST,
								'GARANTY_KM' => GARANTIE_KM,
								'KM_INS_DIFF' => INS_DIFF_KM,
								'IHR' => 'IHR',
								'HV' => 'HV',
								'REP' => 'REP',
								'INSMG' => 'INS MG',
								'KOST_ALLE' => 'KOST ALLE',
								'K4180' => '4180',
								'K4280' => '4280',
								'K4281' => '4281',
								'K4282' => '4282',
								'K5081' => 'VZN',
								'K5010' => 'EINZAHLUNG',
								'HG_Z' => 'HG ZAHLUNG',
								'SOLL_AUSZ' => 'AUSZ SOLL',
								'AUSZAHLUNG' => 'AUSZAHLUNG IST',
								'SALDO_MET' => 'SALDO M',
								'PERIOD' => 'PERIOD',
								'INS_DIFF' => 'INS_DIFF' 
						);
					} else {
						$cols = array (
								'MON' => MONAT2,
								'WM_SOLL' => WM,
								'NK_SOLL' => NK,
								'ZB_M' => 'ZB_M',
								'SALDO_M' => 'SALDO_M',
								'KM_SOLL' => KM_SOLL,
								'KM_IST' => KM_IST,
								'KOST_ALLE' => 'KOST ALLE',
								'K5081' => 'VZN',
								'K5010' => 'EINZAHLUNG',
								'HG_Z' => 'HG ZAHLUNG',
								'SOLL_AUSZ' => 'AUSZ SOLL',
								'AUSZAHLUNG' => 'AUSZAHLUNG IST',
								'SALDO_MET' => 'SALDO M',
								'PERIOD' => 'PERIOD' 
						);
					}
					$pdf->ezTable ( $pdf_tab, $cols, EINNAHMEN_REPORT . " $datum_von $datum_bis", array (
							'showHeadings' => 1,
							'shaded' => 1,
							'titleFontSize' => 10,
							'fontSize' => 6,
							'xPos' => 50,
							'xOrientation' => 'right',
							'width' => 750,
							'cols' => array (
									'IHR' => array (
											'justification' => 'right' 
									),
									'HV' => array (
											'justification' => 'right' 
									),
									'REP' => array (
											'justification' => 'right' 
									),
									'AUSZAHLUNG' => array (
											'justification' => 'right' 
									) 
							) 
					) );
					unset ( $pdf_tab );
					$pdf->ezNewPage ();
					// print_r($weg);
					// }
				}
			}
			// die();
			
			/* Bericht INS-Beteiligung */
			$pdf->ezNewPage ();
			$anz_ins = count ( $pdf_tab_ergebnis );
			$ins_keys = array_keys ( $pdf_tab_ergebnis );
			$sum_ins_erg = 0;
			// print_r($ins_keys);
			// die();
			for($in = 0; $in < $anz_ins; $in ++) {
				$key = $ins_keys [$in];
				$sum_ins_erg += $pdf_tab_ergebnis [$key] ['INS_DIFF'];
			}
			
			$pdf_tab_ergebnis [$anz_ins] ['INS_DIFF'] = $sum_ins_erg;
			$pdf->ezTable ( $pdf_tab_ergebnis );
			
			ob_clean (); // ausgabepuffer leeren
			header ( "Content-type: application/pdf" ); // wird von MSIE ignoriert
			$pdf->ezStream ();
		}
	}
	function pdf_income_reports2015_1($pdf, $objekt_id, $jahr) {
		
		/* Abzufragende Konten */
		$kokonten [] = '1023'; // Kosten zu Einheit
		$kokonten [] = '4180'; // Gew�hrte Minderungen
		$kokonten [] = '4280'; // Gerichtskosten
		$kokonten [] = '4281'; // Anwaltskosten MEA
		$kokonten [] = '4282'; // Gerichtsvollzieher
		$kokonten [] = '5010'; // Eigent�mereinlagen
		$kokonten [] = '5020'; // ET Entnahmen TRANSFER
		                      // $kokonten[] = '5021'; // Hausgeld
		                      // $kokonten[] = '5400'; // Durch INS zu Erstatten
		$kokonten [] = '5500'; // INS Maklergeb�hr
		$kokonten [] = '5600'; // Mietaufhegungsvereinbarungen
		                      // $kokonten[] = '6000'; // Hausgeldzahlungen
		                      // $kokonten[] = '6010'; // Heizkosten
		                      // $kokonten[] = '6020'; // Nebenkosten / Hausgeld
		                      // $kokonten[] = '6030'; // IHR
		                      // $kokonten[] = '6060'; // Verwaltergeb�hr
		
		$kokonten [] = '80001'; // Mieteinnahme
		
		define ( "EINNAHMEN_REPORT", "Income report" );
		define ( "OBJEKT", "object" );
		define ( "WOHNUNG", "flat" );
		define ( "EIGENTUEMER", "owner" );
		define ( "LAGE", "location" );
		define ( "TYP", "type" );
		define ( "FLAECHE", "living space" );
		
		define ( "SUMMEN", "sum [�]" );
		define ( "MONAT2", "month" );
		define ( "IHR", "for maintenance [0,40�*m�]" );
		define ( "HV", "managing fee [�]" );
		define ( "REP", "repairs [�]" );
		define ( "AUSZAHLUNG", "actual transfer [�]" );
		define ( "DATUM", "Date" );
		
		$oo = new objekt ();
		$oo->get_objekt_infos ( $objekt_id );
		$datum_von = "$jahr-01-01";
		$datum_bis = "$jahr-12-31";
		$weg = new weg ();
		$m_arr_jahr = $weg->monatsarray_erstellen ( $datum_von, $datum_bis );
		// echo '<pre>';
		// print_r($m_arr_jahr);
		// die();
		$gk = new geldkonto_info ();
		$gk_arr = $gk->geldkonten_arr ( 'OBJEKT', $objekt_id );
		$anz_gk = count ( $gk_arr );
		
		// ###
		// print_r($gk_arr);
		// die();
		// ####
		
		$d = new detail ();
		/* Nutzenlastenwechsel */
		$nl_datum = $d->finde_detail_inhalt ( 'Objekt', $objekt_id, 'Nutzen-Lastenwechsel' );
		$nl_datum_arr = explode ( '.', $nl_datum );
		$nl_tag = $nl_datum_arr [0];
		$nl_monat = $nl_datum_arr [1];
		$nl_jahr = $nl_datum_arr [2];
		
		/* Verwaltungs�bernahme */
		$vu_datum = $d->finde_detail_inhalt ( 'Objekt', $objekt_id, 'Verwaltungs�bernahme' );
		$vu_datum_arr = explode ( '.', $vu_datum );
		$vu_tag = $vu_datum_arr [0];
		$vu_monat = $vu_datum_arr [1];
		$vu_jahr = $vu_datum_arr [2];
		
		// echo "$objekt_id $jahr";
		
		$ein_arr = $weg->einheiten_weg_tabelle_arr ( $objekt_id );
		
		$anz_e = count ( $ein_arr );
		
		// $cols = array('MONAT'=>MONAT,'NT'=>NT, 'IHR'=>IHR, 'HV'=>HV,'FIX'=>FIX, 'LEER'=>LEER, 'MV_NAME'=>MIETER, 'KM_S'=>KM_S, 'KM_SA'=>KMANT, 'M_ERG'=>ERG, 'M_ERGA'=>ERGA);
		$cols ['MONAT'] = MONAT;
		$cols ['MONAT'] = MONAT;
		$cols ['NT'] = NT;
		$cols ['IHR'] = IHR;
		$cols ['HV'] = HV;
		$cols ['FIX'] = FIX;
		// $cols['LEER'] = LEER;
		$cols ['MV_NAME'] = MIETER;
		$cols ['KOS_BEZ'] = KOS_BEZ;
		$cols ['WM_S'] = WM_S;
		$cols ['MWST'] = MWST;
		$cols ['NK'] = NK;
		$cols ['KM_S'] = KM_S;
		$cols ['KM_SA'] = KM_SA;
		$cols ['M_ERG'] = M_ERG;
		$cols ['TXT'] = TXT;
		
		/* schleife Einheiten */
		for($e = 0; $e < $anz_e; $e ++) {
			$einheit_id = $ein_arr [$e] ['EINHEIT_ID'];
			$weg = new weg ();
			$et_arr = $weg->get_eigentuemer_arr ( $einheit_id );
			// echo "$einheit_id ";
			$anz_et = count ( $et_arr );
			/* Schleife f�r ET */
			
			$sum_hv = 0;
			$sum_ihr = 0;
			$sum_fix = 0;
			$sum_km_ant = 0;
			$sum_wm_s = 0;
			$sum_nk = 0;
			$sum_mwst = 0;
			$sum_km_s = 0;
			
			$sum_konten = array ();
			for($et = 0; $et < $anz_et; $et ++) {
				// print_r($et_arr);
				$et_id = $et_arr [$et] ['ID'];
				
				/* Personenkontaktdaten Eigent�mer */
				$weg_nn = new weg ();
				$et_p_id = $weg_nn->get_person_id_eigentuemer_arr ( $et_id );
				$email_arr_a = array ();
				if (is_array ( $et_p_id )) {
					$anz_pp = count ( $et_p_id );
					for($pe = 0; $pe < $anz_pp; $pe ++) {
						$et_p_id_1 = $et_p_id [$pe] ['PERSON_ID'];
						// echo $et_p_id_1;
						$detail = new detail ();
						if (($detail->finde_detail_inhalt ( 'PERSON', $et_p_id_1, 'Email' ))) {
							$email_arr = $detail->finde_alle_details_grup ( 'PERSON', $et_p_id_1, 'Email' );
							for($ema = 0; $ema < count ( $email_arr ); $ema ++) {
								$em_adr = $email_arr [$ema] ['DETAIL_INHALT'];
								$email_arr_a [] = $em_adr;
							}
							// $my_arr[$z]['EMAILS'][] = $detail->finde_detail_inhalt('PERSON', $et_p_id_1, 'Email');
						}
					}
				}
				
				$et_von_sql = $et_arr [$et] ['VON'];
				$et_bis_sql = $et_arr [$et] ['BIS'];
				// echo "$et_id<br>";
				$weg1 = new weg ();
				$weg1->get_eigentumer_id_infos4 ( $et_id );
				$weg->get_eigentumer_id_infos4 ( $et_id );
				echo "<b>$weg1->einheit_kurzname $weg1->empf_namen</b><br>";
				
				/* Zeitarray ET */
				$vond = $jahr . '0101';
				$bisd = $jahr . '1231';
				$et_von = str_replace ( '-', '', $et_von_sql );
				if ($et_bis_sql != '0000-00-00') {
					$et_bis = str_replace ( '-', '', $et_bis_sql );
				} else {
					$et_bis = str_replace ( '-', '', "$jahr-12-31" );
				}
				
				if ($et_von > $vond) {
					$datum_von = $et_von_sql;
				}
				
				if ($et_bis < $bisd) {
					$datum_bis = $et_bis_sql;
				}
				
				if ($et_bis < $vond) {
					$datum_von = '0000-00-00';
					$datum_bis = '0000-00-00';
				}
				
				// $m_arr= $weg->monatsarray_erstellen($weg_et->eigentuemer_von,$datum_bis);
				// echo "$datum_bis - $datum_bis";
				$m_arr = $weg->monatsarray_erstellen ( $datum_von, $datum_bis );
				
				$anz_mon_et = count ( $m_arr );
				$et_mon_arr = '';
				for($me = 0; $me < $anz_mon_et; $me ++) {
					$et_mon_arr [] = $m_arr [$me] ['monat'];
				}
				// print_r($et_mon_arr);
				// die();
				
				/* Datum zur�cksetzen auf Jahresanfang bzw. Ganzjahr */
				$datum_von = "$jahr-01-01";
				$datum_bis = "$jahr-12-31";
				
				// print_r($m_arr);
				$anz_m = count ( $m_arr_jahr );
				/* Schlife Monate */
				$zeile = 0;
				for($m = 0; $m < $anz_m; $m ++) {
					
					$monat = $m_arr_jahr [$m] ['monat'];
					$jahr = $m_arr_jahr [$m] ['jahr'];
					
					/* Wenn der ET vom Monat */
					if (in_array ( $monat, $et_mon_arr )) {
						
						$key = array_search ( $monat, $et_mon_arr );
						$et_monat = $m_arr [$key] ['monat'];
						$et_jahr = $m_arr [$key] ['jahr'];
						
						$tage = $m_arr [$key] ['tage_m'];
						$n_tage = $m_arr [$key] ['tage_n'];
						
						$pdf_tab [$e] [$et] [$zeile] ['NT'] = $n_tage;
						if ($pdf_tab [$e] [$et] [$zeile - 1] ['IHR'] == '---') {
							$n_tage = $tage;
						}
						
						// ##########ANFANG FIXKOSTEN##########################
						/* FIXKOSTEN */
						/* Fixkosten Hausgeld oder Formel */
						$hg = new weg ();
						$hg->get_wg_info ( $monat, $jahr, 'Einheit', $einheit_id, 'Hausgeld' );
						$hausgeld_soll = $hg->gruppe_erg / $tage * $n_tage;
						
						/* Fixkosten nach Formel */
						$hausgeld_soll_f = (($weg->einheit_qm_weg * 0.4) + 30) / $tage * $n_tage;
						// echo "$hausgeld_soll $hausgeld_soll_f<hr>";
						
						if ($hausgeld_soll_f > $hausgeld_soll) {
							$pdf_tab [$e] [$et] [$zeile] ['MONAT'] = "<b>" . monat2name ( $et_monat ) . " $et_jahr</b>";
							$hausgeld_soll = $hausgeld_soll_f;
							$pdf_tab [$e] [$et] [$zeile] ['IHR'] = nummer_punkt2komma ( ($weg->einheit_qm_weg * - 0.4) / $tage * $n_tage );
							$sum_ihr += nummer_komma2punkt ( nummer_punkt2komma ( ($weg->einheit_qm_weg * - 0.4) / $tage * $n_tage ) );
							$pdf_tab [$e] [$et] [$zeile] ['HV'] = nummer_punkt2komma ( - 30.00 / $tage * $n_tage );
							$sum_hv += nummer_komma2punkt ( nummer_punkt2komma ( - 30.00 / $tage * $n_tage ) );
							$pdf_tab [$e] [$et] [$zeile] ['FIX'] = nummer_komma2punkt ( nummer_punkt2komma ( ((($weg->einheit_qm_weg * - 0.4) + - 30) / $tage * $n_tage) ) );
							$sum_fix += nummer_komma2punkt ( nummer_punkt2komma ( ((($weg->einheit_qm_weg * - 0.4) + - 30) / $tage * $n_tage) ) );
						} else {
							/* Wenn nicht der ET vom Monat */
							
							$pdf_tab [$e] [$et] [$zeile] ['MONAT'] = "<b>" . monat2name ( $et_monat ) . " $et_jahr</b>";
							$pdf_tab [$e] [$et] [$zeile] ['IHR'] = '0.000';
							$pdf_tab [$e] [$et] [$zeile] ['HV'] = '0.000';
							$pdf_tab [$e] [$et] [$zeile] ['FIX'] = nummer_komma2punkt ( nummer_punkt2komma ( ($hausgeld_soll * - 1) / $tage * $n_tage ) );
							$sum_fix += nummer_komma2punkt ( nummer_punkt2komma ( ($hausgeld_soll * - 1) / $tage * $n_tage ) );
						}
						// ##########ENDE FIXKOSTEN##########################
						// ##########ANFANG LEERSTAND JA NEIN##########################
						$mv_arr = array ();
						$ltm = letzter_tag_im_monat ( $et_monat, $et_jahr );
						
						$mv_arr = $this->get_mv_et_zeitraum_arr ( $einheit_id, "$et_jahr-$et_monat-01", "$et_jahr-$et_monat-$ltm" );
						
						if (is_array ( $mv_arr )) {
							// print_r($mv_arr);
							// die();
							$pdf_tab [$e] [$et] [$zeile] ['LEER'] = 'N';
							$anz_mv = count ( $mv_arr );
							// #########MIETVERTR�GE IM MONAT###########
							for($mva = 0; $mva < $anz_mv; $mva ++) {
								$mv_id = $mv_arr [$mva] ['MIETVERTRAG_ID'];
								$mvv = new mietvertraege ();
								$mvv->get_mietvertrag_infos_aktuell ( $mv_id );
								$pdf_tab [$e] [$et] [$zeile] ['MV_NAME'] = substr ( bereinige_string ( $mvv->personen_name_string ), 0, 30 );
								$mk = new mietkonto ();
								$mk->kaltmiete_monatlich ( $mv_id, $et_monat, $et_jahr );
								$sum_ford_m_inkl_mwst = $mk->summe_forderung_monatlich ( $mv_id, $et_monat, $et_jahr );
								$sum_for_arr = explode ( '|', $sum_ford_m_inkl_mwst );
								if (count ( $sum_for_arr ) > 1) {
									$wm = $sum_for_arr [0];
									$mwst = $sum_for_arr [1];
								} else {
									$wm = $sum_ford_m_inkl_mwst;
									$mwst = '0.00';
								}
								
								// $mk->summe_forderung_monatlich($mv_id, $monat, $jahr)
								$pdf_tab [$e] [$et] [$zeile] ['WM_S'] = $wm;
								$sum_wm_s += $wm;
								$pdf_tab [$e] [$et] [$zeile] ['MWST'] = $mwst;
								$sum_mwst += $mwst;
								$pdf_tab [$e] [$et] [$zeile] ['NK'] = nummer_komma2punkt ( nummer_punkt2komma ( $wm - nummer_komma2punkt ( nummer_punkt2komma ( $mk->ausgangs_kaltmiete / $tage * $n_tage ) ) ) );
								$sum_nk += $pdf_tab [$e] [$et] [$zeile] ['NK'];
								$pdf_tab [$e] [$et] [$zeile] ['KM_S'] = $mk->ausgangs_kaltmiete;
								$sum_km_s += $pdf_tab [$e] [$et] [$zeile] ['KM_S'];
								$pdf_tab [$e] [$et] [$zeile] ['KM_SA'] = nummer_komma2punkt ( nummer_punkt2komma ( $mk->ausgangs_kaltmiete / $tage * $n_tage ) );
								$sum_km_ant += $pdf_tab [$e] [$et] [$zeile] ['KM_SA'];
								/* Saldoberechnung wegen SALDO VV nicht m�glich */
								$mz = new miete ();
								// $mz->mietkonto_berechnung($mv_id);
								$mz->mietkonto_berechnung_monatsgenau ( $mv_id, $et_jahr, $et_monat );
								$pdf_tab [$e] [$et] [$zeile] ['M_ERG'] = nummer_komma2punkt ( nummer_punkt2komma ( $mz->erg ) );
								$pdf_tab [$e] [$et] [$zeile] ['M_ERGA'] = nummer_komma2punkt ( nummer_punkt2komma ( $mz->erg / $tage * $n_tage ) );
								// print_r($mz);
								// die();
								if ($anz_mv > 0) {
									$zeile ++;
								}
							}
						} else {
							$pdf_tab [$e] [$et] [$zeile] ['LEER'] = 'J';
							$pdf_tab [$e] [$et] [$zeile] ['MV_NAME'] = LEER;
						}
					}  // end if monat!!!

					else {
						// print_r($m_arr);
						$pdf_tab [$e] [$et] [$zeile] ['MONAT'] = "<b>" . monat2name ( $monat ) . " $jahr</b>";
						$pdf_tab [$e] [$et] [$zeile] ['IHR'] = '---';
						$pdf_tab [$e] [$et] [$zeile] ['HV'] = '---';
						$pdf_tab [$e] [$et] [$zeile] ['FIX'] = '---';
					}
					
					if (in_array ( $monat, $et_mon_arr )) {
						/* Schleife GELD-Konto */
						for($g = 0; $g < $anz_gk; $g ++) {
							$gk_id = $gk_arr [$g] ['KONTO_ID'];
							// echo "<b>GK: $gk_id<br></b>";
							// $zeile++;
							if (isset ( $buchungen )) {
								unset ( $buchungen );
							}
							// if(isset($mv_id)){
							if ($pdf_tab [$e] [$et] [$zeile] ['LEER'] != 'J') {
								$buchungen = $this->bebuchte_konten_brutto ( $gk_id, $einheit_id, $monat, $jahr, $et_id, $mv_id );
							} else {
								$buchungen = $this->bebuchte_konten_brutto ( $gk_id, $einheit_id, $monat, $jahr, $et_id );
							}
							if (is_array ( $buchungen )) {
								$anz_bu = count ( $buchungen );
								$gki1 = new geldkonto_info ();
								$gki1->geld_konto_details ( $gk_id );
								
								for($b = 0; $b < $anz_bu; $b ++) {
									$bkonto = $buchungen [$b] ['KONTENRAHMEN_KONTO'];
									if (! empty ( $bkonto )) {
										$b_konten_arr [] = $bkonto;
										$betrag = $buchungen [$b] ['BETRAG'];
										$kos_typ = $buchungen [$b] ['KOSTENTRAEGER_TYP'];
										$kos_id = $buchungen [$b] ['KOSTENTRAEGER_ID'];
										$vzweck = $buchungen [$b] ['VERWENDUNGSZWECK'];
										$datum = $buchungen [$b] ['DATUM'];
										$pdf_tab [$e] [$et] [$zeile] [$bkonto] = $betrag;
										$r = new rechnung ();
										$kos_bez = $r->kostentraeger_ermitteln ( $kos_typ, $kos_id );
										$pdf_tab [$e] [$et] [$zeile] ['KOS_BEZ'] = str_replace ( '<br>', '', $kos_bez );
										$pdf_tab [$e] [$et] [$zeile] ['TXT'] = "<b>$gki1->geldkonto_bez | $gki1->kredit_institut</b> - " . $vzweck;
										$pdf_tab [$e] [$et] [$zeile] ['MONAT'] = date_mysql2german ( $datum );
										$sum_konten [$bkonto] += $betrag;
										$cols [$bkonto] = $bkonto;
										$zeile ++;
									}
								}
							}
							// print_r($buchungen);
						} // end for GK
					}
					// die();
					
					$zeile ++;
				} // end for MONATE
				  // die();
				/* Summe pro ET */
				$anz_z = count ( $pdf_tab [$e] [$et] );
				$pdf_tab [$e] [$et] [$zeile + 1] ['MONAT'] = 'SUMME';
				$pdf_tab [$e] [$et] [$zeile + 1] ['IHR'] = nummer_komma2punkt ( nummer_punkt2komma ( $sum_ihr ) );
				$pdf_tab [$e] [$et] [$zeile + 1] ['HV'] = nummer_komma2punkt ( nummer_punkt2komma ( $sum_hv ) );
				$pdf_tab [$e] [$et] [$zeile + 1] ['FIX'] = nummer_komma2punkt ( nummer_punkt2komma ( $sum_fix ) );
				$pdf_tab [$e] [$et] [$zeile + 1] ['KM_S'] = nummer_komma2punkt ( nummer_punkt2komma ( $sum_km_s ) );
				$pdf_tab [$e] [$et] [$zeile + 1] ['KM_SA'] = nummer_komma2punkt ( nummer_punkt2komma ( $sum_km_ant ) );
				$pdf_tab [$e] [$et] [$zeile + 1] ['WM_S'] = nummer_komma2punkt ( nummer_punkt2komma ( $sum_wm_s ) );
				$pdf_tab [$e] [$et] [$zeile + 1] ['MWST'] = nummer_komma2punkt ( nummer_punkt2komma ( $sum_mwst ) );
				$pdf_tab [$e] [$et] [$zeile + 1] ['NK'] = nummer_komma2punkt ( nummer_punkt2komma ( $sum_nk ) );
				$pdf_tab [$e] [$et] [$zeile + 1] ['EINHEIT'] = $weg1->einheit_kurzname;
				$pdf_tab [$e] [$et] [$zeile + 1] ['ET'] = $weg1->empf_namen;
				
				// $pdf_last[$et_id] = $pdf_tab[$e][$et][$zeile+1];
				
				$bb_keys = array_keys ( $sum_konten );
				for($bb = 0; $bb < count ( $sum_konten ); $bb ++) {
					$kto = $bb_keys [$bb];
					$pdf_tab [$e] [$et] [$zeile + 1] [$kto] = nummer_komma2punkt ( nummer_punkt2komma ( $sum_konten [$kto] ) );
				}
				
				$sum_ihr = 0;
				$sum_hv = 0;
				$sum_fix = 0;
				$sum_km_ant = 0;
				$sum_km_s = 0;
				$sum_wm_s = 0;
				$sum_nk = 0;
				$sum_mwst = 0;
				// $sum_konten = array();
				
				$email_arr_aus = array_unique ( $email_arr_a );
				$anz_email = count ( $email_arr_aus );
				$pdf->setColor ( 255, 255, 255, 255 ); // Weiss
				for($ema = 0; $ema < $anz_email; $ema ++) {
					$email_adr = $email_arr_aus [$ema];
					$pdf->ezText ( "$email_adr", 10 );
					$pdf->ezSetDy ( 10 ); // abstand
				}
				$pdf->setColor ( 0, 0, 0, 1 ); // schwarz
				
				$pdf->ezSetDy ( - 10 ); // abstand
				$weg1->eigentuemer_von_d = date_mysql2german ( $weg1->eigentuemer_von );
				$weg1->eigentuemer_bis_d = date_mysql2german ( $weg1->eigentuemer_bis );
				$pdf->ezSetDy ( - 15 ); // abstand
				$pdf->ezText ( WOHNUNG . ": $weg1->einheit_kurzname\n" . LAGE . ": $weg1->einheit_lage\n$weg1->haus_strasse $weg1->haus_nummer, $weg1->haus_plz $weg1->haus_stadt\n\n" . EIGENTUEMER . ":\n$weg1->empf_namen", 9 );
				
				// $cols = array('MONAT'=>MONAT,'NT'=>NT, 'IHR'=>IHR, 'HV'=>HV,'FIX'=>FIX, 'LEER'=>LEER, 'MV_NAME'=>MIETER, 'KM_S'=>KM_S, 'KM_SA'=>KMANT, 'M_ERG'=>ERG, 'M_ERGA'=>ERGA);
				
				// $pdf->ezTable($pdf_tab[$e][$et], $cols, EINNAHMEN_REPORT." $jahr - $weg->haus_strasse $weg->haus_nummer in $weg->haus_plz $weg->haus_stadt" , array('showHeadings'=>1,'shaded'=>1, 'titleFontSize' => 8, 'fontSize' => 7, 'xPos'=>40,'xOrientation'=>'right', 'width'=>760,'cols'=>array('TXT'=>array('justification'=>'right'), 'IHR'=>array('justification'=>'right'), 'HV'=>array('justification'=>'right'))));
				$sum_keys = array_keys ( $pdf_tab [$e] [$et] );
				$anz_etz = count ( $sum_keys );
				$last_z = $sum_keys [$anz_etz - 1];
				// echo $last_z;
				$pdf->ezSetDy ( - 30 ); // abstand
				                    // echo '<pre>';
				
				$pdf->ezText ( EINNAHMEN_REPORT . " $jahr", 15 );
				$pdf->ezSetDy ( - 20 ); // abstand
				                    
				// print_r($pdf_tab[$e][$et]);
				
				/* Legende */
				$anz_zeilen_et = count ( $pdf_tab [$e] [$et] );
				// echo $anz_zeilen_et;
				// print_r($pdf_tab[$e][$et][$last_z]);
				// die();
				// $pdf->ezTable($pdf_tab[$e][$et][$last_z]);
				$anz_elem = count ( $pdf_tab [$e] [$et] [$last_z] );
				$et_tab = array ();
				$et_za = 0;
				
				$kosten_ko = array ();
				$ko_z = 0;
				foreach ( $pdf_tab [$e] [$et] [$last_z] as $el_key => $el_value ) {
					// echo "$el_key $el_value<br>";
					// $pdf->ezText("<b>$el_key:</b> $el_value", 9);
					
					if ($el_key == 'FIX') {
						$bez = 'Fixed owner costs (Mng. Fee and maintenance fund)';
						$kosten_ko [$ko_z] ['BEZ'] = $bez;
						$kosten_ko [$ko_z] ['BETRAG'] = nummer_komma2punkt ( nummer_punkt2komma ( $el_value ) );
						$ko_z ++;
					}
					
					if ($el_key == 'KM_S') {
						$el_key = 'Net rent only (debit side)';
					}
					
					if ($el_key == 'NK') {
						$bez = 'Running Service Costs (debit side)';
						$kosten_ko [$ko_z] ['BEZ'] = $bez;
						$kosten_ko [$ko_z] ['BETRAG'] = nummer_komma2punkt ( nummer_punkt2komma ( $el_value * - 1 ) );
						$ko_z ++;
					}
					
					if ($el_key == 'WM_S') {
						$el_key = 'Total Rent Income (Brutto) (debit side)';
					}
					
					if (is_numeric ( $el_key )) {
						if ($el_key == '80001') {
							$bez = "$el_key - Total Rent Income (Brutto) - All payments by tenant, incl. Running service costs";
						}
						
						if ($el_key == '5020') {
							$bez = "$el_key - Transfer to owner";
							// $el_value = $el_value*-1;
						}
						
						if ($el_key == '5021') {
							$bez = "$el_key - Housing benefit";
						}
						
						if ($el_key == '1023') {
							$bez = "$el_key - Costs/repairs apartment";
						}
						
						if ($el_key == '5101') {
							$bez = "$el_key - Tenant security deposit";
						}
						
						if ($el_key == '5500') {
							$bez = "$el_key - Broker fee";
						}
						
						if ($el_key == '5600') {
							$bez = "$el_key - Tenant evacuation";
						}
						
						if ($el_key == '6000') {
							$bez = "$el_key - Housing benefit";
						}
						
						if ($el_key == '6010') {
							$bez = "$el_key - Heating costs";
						}
						
						if ($el_key == '6020') {
							$bez = "$el_key - Running costs";
						}
						
						if ($el_key == '6030') {
							$bez = "$el_key - Reserve";
						}
						
						if ($el_key == '6060') {
							$bez = "$el_key - Management fee";
						}
						
						if (empty ( $bez )) {
							$bez = $el_key;
						}
						
						// $kosten_ko[$ko_z]['BEZ'] = $el_key;
						if ($el_value != 0 && in_array ( $el_key, $kokonten )) {
							$kosten_ko [$ko_z] ['BEZ'] = $bez;
							$kosten_ko [$ko_z] ['BETRAG'] = nummer_komma2punkt ( nummer_punkt2komma ( $el_value ) );
							$bez = '';
							$ko_z ++;
						}
					}
					
					if ($el_key != 'MONAT' && $el_key != 'IHR' && $el_key != 'NK' && $el_key != 'HV' && $el_key != 'FIX' && $el_key != 'MWST' && ! is_numeric ( $el_key ) && $el_key != 'KM_SA' && $el_key != 'ET' && $el_key != 'EINHEIT') {
						$et_tab [$et_za] ['BEZ'] = $el_key;
						$et_tab [$et_za] ['BETRAG'] = nummer_komma2punkt ( nummer_punkt2komma ( $el_value ) );
						// $pdf->ezTable($pdf_tab[$e][$et][$last_z]);
						$et_za ++;
					}
				}
				ksort ( $et_tab );
				arsort ( $kosten_ko );
				
				$et_tab1 = array_sortByIndex ( $et_tab, 'BETRAG', 'SORT_DESC' );
				$kosten_ko1 = array_sortByIndex ( $kosten_ko, 'BETRAG', 'SORT_DESC' );
				
				$et_tab1 [] ['BEZ'] = ' ';
				// $pdf->ezTable($et_tab);
				// $pdf->ezTable($kosten_ko);
				
				$anz_oo = count ( $kosten_ko1 );
				$amount_et = 0;
				for($ooo = 0; $ooo < $anz_oo; $ooo ++) {
					$amount_et += $kosten_ko1 [$ooo] ['BETRAG'];
				}
				
				$kosten_ko1 [$anz_oo] ['BEZ'] = "<b>Balance</b>";
				$kosten_ko1 [$anz_oo] ['BETRAG'] = "<b>" . nummer_komma2punkt ( nummer_punkt2komma ( $amount_et ) ) . "</b>";
				
				$et_tab_new = array_merge ( $et_tab1, $kosten_ko1 );
				
				$cols_et = array (
						'BEZ' => 'Description',
						'BETRAG' => 'Amount' 
				);
				$pdf->ezTable ( $et_tab_new, $cols_et, EINNAHMEN_REPORT . " $jahr  - $oo->objekt_kurzname", array (
						'showHeadings' => 1,
						'shaded' => 1,
						'titleFontSize' => 9,
						'fontSize' => 8,
						'xPos' => 50,
						'xOrientation' => 'right',
						'width' => 500,
						'cols' => array (
								'BETRAG' => array (
										'justification' => 'right',
										'width' => 100 
								) 
						) 
				) );
				
				// $pdf->ezTable($et_tab_new);
				
				// die();
				
				if (is_array ( $sum_konten )) {
					
					$gki = new geldkonto_info ();
					$gki->geld_konto_ermitteln ( 'OBJEKT', $objekt_id );
					if ($gki->geldkonto_id) {
						$kr = new kontenrahmen ();
						$kr_id = $kr->get_kontenrahmen ( 'GELDKONTO', $gki->geldkonto_id );
						
						$string = '';
						$bb_keys = array_keys ( $sum_konten );
						for($bb = 0; $bb < count ( $sum_konten ); $bb ++) {
							$kto = $bb_keys [$bb];
							$kr->konto_informationen2 ( $kto, $kr_id );
							$string .= "$kto - $kr->konto_bezeichnung\n";
							// $betrag = $pdf_tab[$e][$et][$anz_zeilen_et-1][$kto];
							// $pdf->ezText("<b>$string - $betrag</b>", 9);
							unset ( $cols [$kto] );
						}
						// $pdf->ezSetDy(-20); //abstand
						// $pdf->ezText("<b>$string</b>", 9);
					}
				}
				
				$pdf_last [$et_id] = $pdf_tab [$e] [$et] [$zeile + 1];
				
				$sum_konten = array ();
				// $pdf->ezTable($pdf_tab[$e][$et]);
				$pdf->ezNewPage ();
				
				$sum_ihr = 0;
				$sum_hv = 0;
				$sum_fix = 0;
				$sum_km_ant = 0;
				$sum_km_s = 0;
				$sum_wm_s = 0;
				$sum_nk = 0;
				$sum_mwst = 0;
			} // end for ET
				  
			// echo "<hr>";
				  
			// print_r($pdf_tab[$e]);
				  // die();
				  // $pdf->ezTable($pdf_tab[$e]);
		} // end for Einheit
		  
		// $pdf->ezTable($pdf_last);
		unset ( $cols ['M_ERG'] );
		unset ( $cols ['TXT'] );
		unset ( $cols ['MV_NAME'] );
		unset ( $cols ['KOS_BEZ'] );
		unset ( $cols ['NT'] );
		unset ( $cols ['MONAT'] );
		$cols ['EINHEIT'] = EINHEIT;
		$cols ['ET'] = ET;
		
		/* Legende */
		if (is_array ( $b_konten_arr )) {
			// echo '<pre>';
			// print_r($b_konten_arr);
			// die();
			$b_konten_arr1 = array_unique ( $b_konten_arr );
			// echo '<pre>';
			// print_r($b_konten_arr1);
			// die();
			$gki = new geldkonto_info ();
			$gki->geld_konto_ermitteln ( 'OBJEKT', $objekt_id );
			$string = '';
			if ($gki->geldkonto_id) {
				$kr = new kontenrahmen ();
				$kr_id = $kr->get_kontenrahmen ( 'GELDKONTO', $gki->geldkonto_id );
				// echo "KR: $kr_id | $gki->geldkonto_id";
				// die();
				$bb_keys = array_keys ( $b_konten_arr1 );
				for($bb = 0; $bb < count ( $b_konten_arr1 ); $bb ++) {
					$ktokey = $bb_keys [$bb];
					$kto = $b_konten_arr1 [$ktokey];
					$cols [$kto] = $kto;
					$kr->konto_informationen2 ( $kto, $kr_id );
					$string .= "<b>$kto</b> - $kr->konto_bezeichnung, ";
				}
				
				$anz_sumk = count ( $pdf_last );
				$sum_80001 = 0;
				$sum_5020 = 0;
				
				$id_keys = array_keys ( $pdf_last );
				
				for($x = 0; $x < $anz_sumk; $x ++) {
					$key = $id_keys [$x];
					$sum_80001 += $pdf_last [$key] ['80001'];
					$sum_5020 += $pdf_last [$key] ['5020'];
				}
				
				$pdf_last [$anz_sumk + 1000] ['ET'] = 'SUMME';
				$pdf_last [$anz_sumk + 1000] ['80001'] = $sum_80001;
				$pdf_last [$anz_sumk + 1000] ['5020'] = $sum_5020;
				// echo '<pre>';
				// print_r($pdf_last);
				// die();
				
				unset ( $cols ['MONAT'] );
				unset ( $cols ['IHR'] );
				unset ( $cols ['HV'] );
				unset ( $cols ['MWST'] );
				
				$pdf->ezTable ( $pdf_last, $cols, UEBERSICHT . " $jahr  - $oo->objekt_kurzname", array (
						'showHeadings' => 1,
						'shaded' => 1,
						'titleFontSize' => 7,
						'fontSize' => 5,
						'xPos' => 5,
						'xOrientation' => 'right',
						'width' => 600 
				) );
				$pdf->ezSetDy ( - 20 ); // abstand
				$pdf->ezText ( "$string", 9 );
			}
		}
		
		// print_r($pdf_tab);
	}
	function pdf_income_reports2015_4($pdf, $objekt_id, $jahr) {
	}
	function pdf_income_reports2015_3($pdf, $objekt_id, $jahr) {
		$cols_num ['MONAT'] ['TXT'] = 'Month';
		
		$cols_num ['FIX'] ['TXT'] = 'Fixed costs';
		$cols_num ['FIX'] ['TXT1'] = 'Management fee, maintenance reserve';
		
		$cols_num ['NK'] ['TXT'] = 'Running Costs';
		$cols_num ['NK'] ['TXT1'] = 'Running service costs, cleaning, heating, housekeeping, etc..';
		
		/* Abzufragende Konten */
		$kokonten [] = '1023'; // Kosten zu Einheit
		$cols_num ['1023'] ['TXT'] = 'Repairs';
		$cols_num ['1023'] ['TXT1'] = 'Repairs and general expenses';
		
		$kokonten [] = '4180'; // Gew�hrte Minderungen
		$cols_num ['4180'] ['TXT'] = 'Rent decrease';
		$cols_num ['4180'] ['TXT1'] = '';
		
		$kokonten [] = '4280'; // Gerichtskosten
		$cols_num ['4280'] ['TXT'] = 'Legal';
		$cols_num ['4280'] ['TXT1'] = 'Legal costs - court fees, lawyers, execution';
		
		$kokonten [] = '4281'; // Anwaltskosten MEA
		$cols_num ['4281'] ['TXT'] = 'Legal';
		$cols_num ['4281'] ['TXT1'] = 'Legal costs - court fees, lawyers, execution';
		
		$kokonten [] = '4282'; // Gerichtsvollzieher
		$cols_num ['4282'] ['TXT'] = 'Legal';
		$cols_num ['4282'] ['TXT1'] = 'Legal costs - court fees, lawyers, execution';
		
		$kokonten [] = '5010'; // Eigent�mereinlagen
		$cols_num ['5010'] ['TXT'] = 'Payment by owner';
		$cols_num ['5010'] ['TXT1'] = 'Money received by the owner';
		
		$kokonten [] = '5020'; // ET Entnahmen TRANSFER
		$cols_num ['5020'] ['TXT'] = 'Transfer';
		$cols_num ['5020'] ['TXT1'] = 'Money transfered to owner';
		
		$kokonten [] = '5081'; // ET Entnahmen TRANSFER DARLEHEN
		$cols_num ['5081'] ['TXT'] = 'Loan';
		$cols_num ['5081'] ['TXT1'] = 'Money transfered to banc';
		
		// $kokonten[] = '5021'; // Hausgeld
		// $kokonten[] = '5400'; // Durch INS zu Erstatten
		$kokonten [] = '5500'; // INS Maklergeb�hr
		$cols_num ['5500'] ['TXT'] = 'Brokerage fee';
		$cols_num ['5500'] ['TXT1'] = '';
		
		$kokonten [] = '5600'; // Mietaufhegungsvereinbarungen
		$cols_num ['5600'] ['TXT'] = 'Compensation';
		$cols_num ['5600'] ['TXT1'] = 'Compensation for evacuation';
		// $kokonten[] = '6000'; // Hausgeldzahlungen
		// $kokonten[] = '6010'; // Heizkosten
		// $kokonten[] = '6020'; // Nebenkosten / Hausgeld
		// $kokonten[] = '6030'; // IHR
		// $kokonten[] = '6060'; // Verwaltergeb�hr
		
		$kokonten [] = '80001'; // Mieteinnahme
		$cols_num ['80001'] ['TXT'] = 'Rental Income';
		$cols_num ['80001'] ['TXT1'] = 'Rent received by the tenant (Brutto, \'warm\'), including service costs, heating, etc.';
		
		define ( "EINNAHMEN_REPORT", "Income report" );
		define ( "OBJEKT", "Object" );
		define ( "WOHNUNG", "Flat" );
		define ( "EIGENTUEMER", "<b>Owner</b>" );
		define ( "LAGE", "Location" );
		define ( "TYP", "Type" );
		define ( "FLAECHE", "Living space" );
		
		define ( "SUMMEN", "sum [�]" );
		define ( "MONAT2", "month" );
		define ( "IHR", "for maintenance [0,40�*m�]" );
		define ( "HV", "managing fee [�]" );
		define ( "REP", "repairs [�]" );
		define ( "AUSZAHLUNG", "actual transfer [�]" );
		define ( "DATUM", "Date" );
		
		$oo = new objekt ();
		$oo->get_objekt_infos ( $objekt_id );
		$datum_von = "$jahr-01-01";
		$datum_bis = "$jahr-12-31";
		$weg = new weg ();
		$m_arr_jahr = $weg->monatsarray_erstellen ( $datum_von, $datum_bis );
		// echo '<pre>';
		// print_r($m_arr_jahr);
		// die();
		$gk = new geldkonto_info ();
		$gk_arr = $gk->geldkonten_arr ( 'OBJEKT', $objekt_id );
		$anz_gk = count ( $gk_arr );
		
		// ###
		// print_r($gk_arr);
		// die();
		// ####
		
		$d = new detail ();
		/* Nutzenlastenwechsel */
		$nl_datum = $d->finde_detail_inhalt ( 'Objekt', $objekt_id, 'Nutzen-Lastenwechsel' );
		$nl_datum_arr = explode ( '.', $nl_datum );
		$nl_tag = $nl_datum_arr [0];
		$nl_monat = $nl_datum_arr [1];
		$nl_jahr = $nl_datum_arr [2];
		
		/* Verwaltungs�bernahme */
		$vu_datum = $d->finde_detail_inhalt ( 'Objekt', $objekt_id, 'Verwaltungs�bernahme' );
		$vu_datum_arr = explode ( '.', $vu_datum );
		$vu_tag = $vu_datum_arr [0];
		$vu_monat = $vu_datum_arr [1];
		$vu_jahr = $vu_datum_arr [2];
		
		// echo "$objekt_id $jahr";
		
		$ein_arr = $weg->einheiten_weg_tabelle_arr ( $objekt_id );
		
		$anz_e = count ( $ein_arr );
		
		// $cols = array('MONAT'=>MONAT,'NT'=>NT, 'IHR'=>IHR, 'HV'=>HV,'FIX'=>FIX, 'LEER'=>LEER, 'MV_NAME'=>MIETER, 'KM_S'=>KM_S, 'KM_SA'=>KMANT, 'M_ERG'=>ERG, 'M_ERGA'=>ERGA);
		$cols ['MONAT'] = MONAT;
		$cols ['NT'] = NT;
		$cols ['IHR'] = IHR;
		$cols ['HV'] = HV;
		$cols ['FIX'] = FIX;
		// $cols['LEER'] = LEER;
		$cols ['MV_NAME'] = MIETER;
		$cols ['KOS_BEZ'] = KOS_BEZ;
		$cols ['WM_S'] = WM_S;
		$cols ['MWST'] = MWST;
		$cols ['NK'] = NK;
		$cols ['KM_S'] = KM_S;
		$cols ['KM_SA'] = KM_SA;
		$cols ['M_ERG'] = M_ERG;
		$cols ['TXT'] = TXT;
		
		/* schleife Einheiten */
		for($e = 0; $e < $anz_e; $e ++) {
			$einheit_id = $ein_arr [$e] ['EINHEIT_ID'];
			$weg = new weg ();
			// $et_arr = $weg->get_eigentuemer_arr($einheit_id);
			echo '<pre>';
			$et_arr = $weg->get_eigentuemer_arr_jahr ( $einheit_id, $jahr );
			// echo "$einheit_id ";
			// print_r($et_arr);
			// die();
			$anz_et = count ( $et_arr );
			/* Schleife f�r ET */
			
			$sum_hv = 0;
			$sum_ihr = 0;
			$sum_fix = 0;
			$sum_km_ant = 0;
			$sum_wm_s = 0;
			$sum_nk = 0;
			$sum_mwst = 0;
			$sum_km_s = 0;
			
			$sum_konten = array ();
			for($et = 0; $et < $anz_et; $et ++) {
				// print_r($et_arr);
				$et_id = $et_arr [$et] ['ID'];
				
				/* Personenkontaktdaten Eigent�mer */
				$weg_nn = new weg ();
				$et_p_id = $weg_nn->get_person_id_eigentuemer_arr ( $et_id );
				$email_arr_a = array ();
				if (is_array ( $et_p_id )) {
					$anz_pp = count ( $et_p_id );
					for($pe = 0; $pe < $anz_pp; $pe ++) {
						$et_p_id_1 = $et_p_id [$pe] ['PERSON_ID'];
						// echo $et_p_id_1;
						$detail = new detail ();
						if (($detail->finde_detail_inhalt ( 'PERSON', $et_p_id_1, 'Email' ))) {
							$email_arr = $detail->finde_alle_details_grup ( 'PERSON', $et_p_id_1, 'Email' );
							for($ema = 0; $ema < count ( $email_arr ); $ema ++) {
								$em_adr = $email_arr [$ema] ['DETAIL_INHALT'];
								$email_arr_a [] = $em_adr;
							}
							// $my_arr[$z]['EMAILS'][] = $detail->finde_detail_inhalt('PERSON', $et_p_id_1, 'Email');
						}
					}
				}
				
				$et_von_sql = $et_arr [$et] ['VON'];
				$et_bis_sql = $et_arr [$et] ['BIS'];
				// echo "$et_id<br>";
				$weg1 = new weg ();
				$weg1->get_eigentumer_id_infos4 ( $et_id );
				$weg->get_eigentumer_id_infos4 ( $et_id );
				echo "<b>$weg1->einheit_kurzname $weg1->empf_namen</b><br>";
				
				/* Zeitarray ET */
				$vond = $jahr . '0101';
				$bisd = $jahr . '1231';
				$et_von = str_replace ( '-', '', $et_von_sql );
				if ($et_bis_sql != '0000-00-00') {
					$et_bis = str_replace ( '-', '', $et_bis_sql );
				} else {
					$et_bis = str_replace ( '-', '', "$jahr-12-31" );
				}
				
				if ($et_von > $vond) {
					$datum_von = $et_von_sql;
				}
				
				if ($et_bis < $bisd) {
					$datum_bis = $et_bis_sql;
				}
				
				if ($et_bis < $vond) {
					$datum_von = '0000-00-00';
					$datum_bis = '0000-00-00';
				}
				
				// $m_arr= $weg->monatsarray_erstellen($weg_et->eigentuemer_von,$datum_bis);
				// echo "$datum_bis - $datum_bis";
				$m_arr = $weg->monatsarray_erstellen ( $datum_von, $datum_bis );
				
				$anz_mon_et = count ( $m_arr );
				$et_mon_arr = '';
				for($me = 0; $me < $anz_mon_et; $me ++) {
					$et_mon_arr [] = $m_arr [$me] ['monat'];
				}
				// print_r($et_mon_arr);
				// die();
				
				/* Datum zur�cksetzen auf Jahresanfang bzw. Ganzjahr */
				$datum_von = "$jahr-01-01";
				$datum_bis = "$jahr-12-31";
				
				// print_r($m_arr);
				$anz_m = count ( $m_arr_jahr );
				/* Schlife Monate */
				$zeile = 0;
				for($m = 0; $m < $anz_m; $m ++) {
					
					$monat = $m_arr_jahr [$m] ['monat'];
					$jahr = $m_arr_jahr [$m] ['jahr'];
					
					/* Wenn der ET vom Monat */
					if (in_array ( $monat, $et_mon_arr )) {
						
						$key = array_search ( $monat, $et_mon_arr );
						$et_monat = $m_arr [$key] ['monat'];
						$et_jahr = $m_arr [$key] ['jahr'];
						
						$tage = $m_arr [$key] ['tage_m'];
						$n_tage = $m_arr [$key] ['tage_n'];
						
						$pdf_tab [$e] [$et] [$monat] ['NT'] = $n_tage;
						// if($pdf_tab[$e][$et]$et_monat]['IHR']=='---'){
						// $n_tage = $tage;
						// }
						
						// ##########ANFANG FIXKOSTEN##########################
						/* FIXKOSTEN */
						/* Fixkosten Hausgeld oder Formel */
						$hg = new weg ();
						$hg->get_wg_info ( $monat, $jahr, 'Einheit', $einheit_id, 'Hausgeld' );
						$hausgeld_soll = $hg->gruppe_erg / $tage * $n_tage;
						
						/* Fixkosten nach Formel */
						$hausgeld_soll_f = (($weg->einheit_qm_weg * 0.4) + 30) / $tage * $n_tage;
						// echo "$hausgeld_soll $hausgeld_soll_f<hr>";
						
						if ($hausgeld_soll_f > $hausgeld_soll) {
							$pdf_tab [$e] [$et] [$monat] ['MONAT'] = "<b>" . monat2name ( $et_monat, 'en' ) . " $et_jahr</b>";
							$hausgeld_soll = $hausgeld_soll_f;
							$pdf_tab [$e] [$et] [$monat] ['IHR'] = nummer_punkt2komma ( ($weg->einheit_qm_weg * - 0.4) / $tage * $n_tage );
							
							$sum_ihr += nummer_komma2punkt ( nummer_punkt2komma ( ($weg->einheit_qm_weg * - 0.4) / $tage * $n_tage ) );
							$pdf_tab [$e] [$et] [$monat] ['HV'] = nummer_punkt2komma ( - 30.00 / $tage * $n_tage );
							$sum_hv += nummer_komma2punkt ( nummer_punkt2komma ( - 30.00 / $tage * $n_tage ) );
							$pdf_tab [$e] [$et] [$monat] ['FIX'] = nummer_komma2punkt ( nummer_punkt2komma ( ((($weg->einheit_qm_weg * - 0.4) + - 30) / $tage * $n_tage) ) );
							$sum_fix += nummer_komma2punkt ( nummer_punkt2komma ( ((($weg->einheit_qm_weg * - 0.4) + - 30) / $tage * $n_tage) ) );
						} else {
							/* Wenn nicht der ET vom Monat */
							
							$pdf_tab [$e] [$et] [$monat] ['MONAT'] = "<b>" . monat2name ( $et_monat ) . " $et_jahr</b>";
							$pdf_tab [$e] [$et] [$monat] ['IHR'] = '0.000';
							$pdf_tab [$e] [$et] [$monat] ['HV'] = '0.000';
							$pdf_tab [$e] [$et] [$monat] ['FIX'] = nummer_komma2punkt ( nummer_punkt2komma ( ($hausgeld_soll * - 1) / $tage * $n_tage ) );
							$sum_fix += nummer_komma2punkt ( nummer_punkt2komma ( ($hausgeld_soll * - 1) / $tage * $n_tage ) );
						}
						// ##########ENDE FIXKOSTEN##########################
						// ##########ANFANG LEERSTAND JA NEIN##########################
						if (isset ( $mv_arr )) {
							unset ( $mv_arr );
						}
						// $mv_arr = array();
						$ltm = letzter_tag_im_monat ( $et_monat, $et_jahr );
						$mv_arr = $this->get_mv_et_zeitraum_arr ( $einheit_id, "$et_jahr-$et_monat-01", "$et_jahr-$et_monat-$ltm" );
						
						// if($einheit_id=='1384'){
						// echo "<b>"."$et_jahr-$et_monat-01", "$et_jahr-$et_monat-$ltm"."</b>";
						// print_r($mv_arr);
						// die();
						// }
						
						if (is_array ( $mv_arr )) {
							// print_r($mv_arr);
							// die();
							$pdf_tab [$e] [$et] [$monat] ['LEER'] = 'N';
							$anz_mv = count ( $mv_arr );
							// #########MIETVERTR�GE IM MONAT###########
							for($mva = 0; $mva < $anz_mv; $mva ++) {
								$mv_id = $mv_arr [$mva] ['MIETVERTRAG_ID'];
								$mvv = new mietvertraege ();
								$mvv->get_mietvertrag_infos_aktuell ( $mv_id );
								$pdf_tab [$e] [$et] [$monat] ['MV_NAME'] = substr ( bereinige_string ( $mvv->personen_name_string ), 0, 30 );
								$mk = new mietkonto ();
								$mk->kaltmiete_monatlich ( $mv_id, $et_monat, $et_jahr );
								$sum_ford_m_inkl_mwst = $mk->summe_forderung_monatlich ( $mv_id, $et_monat, $et_jahr );
								$sum_for_arr = explode ( '|', $sum_ford_m_inkl_mwst );
								if (count ( $sum_for_arr ) > 1) {
									$wm = $sum_for_arr [0];
									$mwst = $sum_for_arr [1];
								} else {
									$wm = $sum_ford_m_inkl_mwst;
									$mwst = '0.00';
								}
								
								// $mk->summe_forderung_monatlich($mv_id, $monat, $jahr)
								$pdf_tab [$e] [$et] [$monat] ['WM_S'] = $wm;
								$sum_wm_s += $wm;
								$pdf_tab [$e] [$et] [$monat] ['MWST'] = $mwst;
								$sum_mwst += $mwst;
								$pdf_tab [$e] [$et] [$monat] ['NK'] = nummer_komma2punkt ( nummer_punkt2komma ( $wm - nummer_komma2punkt ( nummer_punkt2komma ( $mk->ausgangs_kaltmiete / $tage * $n_tage ) ) ) );
								$pdf_tab [$e] [$et] [$monat] ['NK'] = nummer_komma2punkt ( nummer_punkt2komma ( $pdf_tab [$e] [$et] [$monat] ['NK'] * - 1 ) );
								// $sum_nk += $pdf_tab[$e][$et][$zeile]['NK'];
								$sum_nk += $pdf_tab [$e] [$et] [$monat] ['NK'];
								$pdf_tab [$e] [$et] [$monat] ['KM_S'] = $mk->ausgangs_kaltmiete;
								$sum_km_s += $pdf_tab [$e] [$et] [$monat] ['KM_S'];
								$pdf_tab [$e] [$et] [$monat] ['KM_SA'] = nummer_komma2punkt ( nummer_punkt2komma ( $mk->ausgangs_kaltmiete / $tage * $n_tage ) );
								$sum_km_ant += $pdf_tab [$e] [$et] [$monat] ['KM_SA'];
								/* Saldoberechnung wegen SALDO VV nicht m�glich */
								$mz = new miete ();
								// $mz->mietkonto_berechnung($mv_id);
								$mz->mietkonto_berechnung_monatsgenau ( $mv_id, $et_jahr, $et_monat );
								$pdf_tab [$e] [$et] [$monat] ['M_ERG'] = nummer_komma2punkt ( nummer_punkt2komma ( $mz->erg ) );
								$pdf_tab [$e] [$et] [$monat] ['M_ERGA'] = nummer_komma2punkt ( nummer_punkt2komma ( $mz->erg / $tage * $n_tage ) );
								// print_r($mz);
								// die();
								if ($anz_mv > 0) {
									$zeile ++;
								}
								
								// unset($mv_id);
							}
						} else {
							$pdf_tab [$e] [$et] [$monat] ['LEER'] = 'J';
							$pdf_tab [$e] [$et] [$monat] ['MV_NAME'] = LEER;
							$mv_arr = '';
						}
					}  // end if monat!!!

					else {
						// print_r($m_arr);
						$pdf_tab [$e] [$et] [$monat] ['MONAT'] = "<b>" . monat2name ( $monat ) . " $jahr</b>";
						$pdf_tab [$e] [$et] [$monat] ['IHR'] = '---';
						$pdf_tab [$e] [$et] [$monat] ['HV'] = '---';
						$pdf_tab [$e] [$et] [$monat] ['FIX'] = '---';
					}
					
					if (in_array ( $monat, $et_mon_arr )) {
						/* Schleife GELD-Konto */
						for($g = 0; $g < $anz_gk; $g ++) {
							$gk_id = $gk_arr [$g] ['KONTO_ID'];
							// echo "<b>GK: $gk_id<br></b>";
							// $zeile++;
							if (isset ( $buchungen )) {
								unset ( $buchungen );
							}
							// if(isset($mv_id)){
							if ($pdf_tab [$e] [$et] [$zeile] ['LEER'] != 'J') {
								$buchungen = $this->bebuchte_konten_brutto ( $gk_id, $einheit_id, $monat, $jahr, $et_id, $mv_arr );
							} else {
								$buchungen = $this->bebuchte_konten_brutto ( $gk_id, $einheit_id, $monat, $jahr, $et_id );
							}
							if (is_array ( $buchungen )) {
								$anz_bu = count ( $buchungen );
								$gki1 = new geldkonto_info ();
								$gki1->geld_konto_details ( $gk_id );
								
								for($b = 0; $b < $anz_bu; $b ++) {
									$bkonto = $buchungen [$b] ['KONTENRAHMEN_KONTO'];
									if (! empty ( $bkonto )) {
										$b_konten_arr [] = $bkonto;
										$betrag = nummer_komma2punkt ( nummer_punkt2komma ( $buchungen [$b] ['BETRAG'] ) );
										if ($bkonto == '5020') {
											$betrag = nummer_komma2punkt ( nummer_punkt2komma ( $buchungen [$b] ['BETRAG'] ) ) * - 1;
											// $betrag = nummer_komma2punkt(nummer_punkt2komma($buchungen[$b]['BETRAG']));
										}
										$kos_typ = $buchungen [$b] ['KOSTENTRAEGER_TYP'];
										$kos_id = $buchungen [$b] ['KOSTENTRAEGER_ID'];
										$vzweck = $buchungen [$b] ['VERWENDUNGSZWECK'];
										$datum = $buchungen [$b] ['DATUM'];
										// echo "$betrag<br>";
										
										$pdf_tab [$e] [$et] [$monat] [$bkonto] += nummer_komma2punkt ( nummer_punkt2komma ( $betrag ) ); // NEU
										$betrag_p = $pdf_tab [$e] [$et] [$monat] [$bkonto];
										$pdf_tab [$e] [$et] [$monat] [$bkonto] = nummer_komma2punkt ( nummer_punkt2komma ( $betrag_p ) );
										// $pdf_tab[$e][$et][$monat][$bkonto] = nummer_komma2punkt(nummer_punkt2komma($pdf_tab[$e][$et][$monat][$bkonto]));//NEU
										// echo nummer_komma2punkt(nummer_punkt2komma($betrag));//NEU
										// die();
										$r = new rechnung ();
										$kos_bez = $r->kostentraeger_ermitteln ( $kos_typ, $kos_id );
										// $pdf_tab[$e][$et][$zeile]['KOS_BEZ'] = str_replace('<br>','',$kos_bez);
										// $pdf_tab[$e][$et][$zeile]['TXT'] = "<b>$gki1->geldkonto_bez | $gki1->kredit_institut</b> - ".$vzweck;
										// $pdf_tab[$e][$et][$zeile]['MONAT'] = date_mysql2german($datum);
										$sum_konten [$bkonto] += nummer_komma2punkt ( nummer_punkt2komma ( $betrag ) );
										$sum_konten [$bkonto] = nummer_komma2punkt ( nummer_punkt2komma ( $sum_konten [$bkonto] ) );
										$cols [$bkonto] = $bkonto;
										$zeile ++;
									}
								}
							}
							// print_r($buchungen);
						} // end for GK
					}
					// die();
					
					$zeile ++;
				} // end for MONATE
				  // die();
				/* Summe pro ET */
				$anz_z = count ( $pdf_tab [$e] [$et] );
				$pdf_tab [$e] [$et] [$monat + 1] ['MONAT'] = "<b>SUMME</b>";
				$pdf_tab [$e] [$et] [$monat + 1] ['IHR'] = "<b>" . nummer_komma2punkt ( nummer_punkt2komma ( $sum_ihr ) ) . "</b>";
				$pdf_tab [$e] [$et] [$monat + 1] ['HV'] = "<b>" . nummer_komma2punkt ( nummer_punkt2komma ( $sum_hv ) ) . "</b>";
				$pdf_tab [$e] [$et] [$monat + 1] ['FIX'] = "<b>" . nummer_komma2punkt ( nummer_punkt2komma ( $sum_fix ) ) . "</b>";
				$pdf_tab [$e] [$et] [$monat + 1] ['KM_S'] = "<b>" . nummer_komma2punkt ( nummer_punkt2komma ( $sum_km_s ) ) . "</b>";
				$pdf_tab [$e] [$et] [$monat + 1] ['KM_SA'] = "<b>" . nummer_komma2punkt ( nummer_punkt2komma ( $sum_km_ant ) ) . "</b>";
				$pdf_tab [$e] [$et] [$monat + 1] ['WM_S'] = "<b>" . nummer_komma2punkt ( nummer_punkt2komma ( $sum_wm_s ) ) . "</b>";
				$pdf_tab [$e] [$et] [$monat + 1] ['MWST'] = "<b>" . nummer_komma2punkt ( nummer_punkt2komma ( $sum_mwst ) ) . "</b>";
				$pdf_tab [$e] [$et] [$monat + 1] ['NK'] = "<b>" . nummer_komma2punkt ( nummer_punkt2komma ( $sum_nk ) ) . "</b>";
				$pdf_tab [$e] [$et] [$monat + 1] ['EINHEIT'] = "<b>" . $weg1->einheit_kurzname . "</b>";
				;
				$pdf_tab [$e] [$et] [$monat + 1] ['ET'] = "<b>" . $weg1->empf_namen . "</b>";
				;
				
				// $pdf_last[$et_id] = $pdf_tab[$e][$et][$zeile+1];
				
				$bb_keys = array_keys ( $sum_konten );
				for($bb = 0; $bb < count ( $sum_konten ); $bb ++) {
					$kto = $bb_keys [$bb];
					$pdf_tab [$e] [$et] [$monat + 1] [$kto] = "<b>" . nummer_komma2punkt ( nummer_punkt2komma ( $sum_konten [$kto] ) ) . "</b>";
				}
				
				$sum_ihr = 0;
				$sum_hv = 0;
				$sum_fix = 0;
				$sum_km_ant = 0;
				$sum_km_s = 0;
				$sum_wm_s = 0;
				$sum_nk = 0;
				$sum_mwst = 0;
				// $sum_konten = array();
				
				$email_arr_aus = array_unique ( $email_arr_a );
				$anz_email = count ( $email_arr_aus );
				$pdf->setColor ( 255, 255, 255, 255 ); // Weiss
				for($ema = 0; $ema < $anz_email; $ema ++) {
					$email_adr = $email_arr_aus [$ema];
					$pdf->ezText ( "$email_adr", 10 );
					$pdf->ezSetDy ( 10 ); // abstand
				}
				
				$pdf->setColor ( 0, 0, 0, 1 ); // schwarz
				$pdf->ezSetDy ( 10 ); // abstand
				
				$weg1->eigentuemer_von_d = date_mysql2german ( $weg1->eigentuemer_von );
				$weg1->eigentuemer_bis_d = date_mysql2german ( $weg1->eigentuemer_bis );
				
				$weg1->empf_namen = str_replace ( 'Frau', 'Ms.', $weg1->empf_namen );
				$weg1->empf_namen = str_replace ( 'Herr', 'Mr.', $weg1->empf_namen );
				$pdf->ezText ( WOHNUNG . ": $weg1->einheit_kurzname\n" . LAGE . ": $weg1->einheit_lage\n$weg1->haus_strasse $weg1->haus_nummer, $weg1->haus_plz $weg1->haus_stadt\n\n" . EIGENTUEMER . ":\n$weg1->empf_namen", 10 );
				
				// $cols = array('MONAT'=>MONAT,'NT'=>NT, 'IHR'=>IHR, 'HV'=>HV,'FIX'=>FIX, 'LEER'=>LEER, 'MV_NAME'=>MIETER, 'KM_S'=>KM_S, 'KM_SA'=>KMANT, 'M_ERG'=>ERG, 'M_ERGA'=>ERGA);
				
				// $pdf->ezTable($pdf_tab[$e][$et], $cols, EINNAHMEN_REPORT." $jahr - $weg->haus_strasse $weg->haus_nummer in $weg->haus_plz $weg->haus_stadt" , array('showHeadings'=>1,'shaded'=>1, 'titleFontSize' => 8, 'fontSize' => 7, 'xPos'=>40,'xOrientation'=>'right', 'width'=>760,'cols'=>array('TXT'=>array('justification'=>'right'), 'IHR'=>array('justification'=>'right'), 'HV'=>array('justification'=>'right'))));
				// $pdf->ezTable($pdf_tab[$e][$et]);
				echo '<pre>';
				$anz_kkk = count ( $pdf_tab [$e] [$et] );
				$cols_arr = array ();
				$cols_arr = array_keys ( $pdf_tab [$e] [$et] [$anz_kkk] );
				// print_r($cols_arr);
				// die();
				$cols = array ();
				
				$colsnumkeys_arr = array_keys ( $cols_num );
				
				$cols_num1 ['MONAT'] = 'Month';
				// $cols_num1['LEER'] = 'Empty (J/N)';
				// $cols_num1['WM_S'] = 'WMS';
				// $cols_num1['MV_NAME'] = 'Tenant';
				$cols_num1 ['80001'] = $cols_num ['80001'] ['TXT'];
				$cols_num1 ['FIX'] = $cols_num ['FIX'] ['TXT'];
				$cols_num1 ['NK'] = $cols_num ['NK'] ['TXT'];
				foreach ( $cols_arr as $kl => $vl ) {
					if (is_numeric ( $vl )) {
						if (in_array ( $vl, $colsnumkeys_arr )) {
							
							if ($vl != '80001' && $vl != '5020') {
								$cols_num1 [$vl] = $cols_num [$vl] ['TXT'];
							}
						} else {
							// $cols_num1[$vl] = $vl;
						}
					} else {
						$cols_alpha [$vl] = $vl;
					}
				}
				$cols_num1 ['5020'] = $cols_num ['5020'] ['TXT'];
				
				// die('BLA');
				// $cols_num[5020] = 'TRANSF';
				// $cols_num[80001] = 'WM';
				
				// unset($cols['NT']);
				/* Sanel */
				
				$anz_s = count ( $pdf_tab [$e] [$et] );
				for($s = 0; $s < $anz_s; $s ++) {
					$s_keys = array_keys ( $pdf_tab [$e] [$et] [$s] );
				}
				// print_r($pdf_tab[$e][$et]);
				/*
				 * if($et=='510'){
				 * print_r($pdf_tab[$e][$et]);
				 * die();
				 * }
				 */
				
				// $pdf->ezTable($pdf_tab[$e][$et], $cols_alpha, EINNAHMEN_REPORT." $jahr - $weg->haus_strasse $weg->haus_nummer in $weg->haus_plz $weg->haus_stadt" , array('showHeadings'=>1,'shaded'=>1, 'titleFontSize' => 8, 'fontSize' => 7, 'xPos'=>40,'xOrientation'=>'right', 'width'=>760,'cols'=>array('TXT'=>array('justification'=>'right'), 'IHR'=>array('justification'=>'right'), 'HV'=>array('justification'=>'right'))));
				// print_r($pdf_tab[$e][$et]);
				// die();
				$pdf->ezTable ( $pdf_tab [$e] [$et], $cols_num1, EINNAHMEN_REPORT . " $jahr  - $weg->haus_strasse $weg->haus_nummer in $weg->haus_plz $weg->haus_stadt", array (
						'showHeadings' => 1,
						'shaded' => 1,
						'titleFontSize' => 10,
						'fontSize' => 9,
						'xPos' => 35,
						'xOrientation' => 'right',
						'width' => 760,
						'cols' => array (
								'TXT' => array (
										'justification' => 'right' 
								),
								'IHR' => array (
										'justification' => 'right' 
								),
								'HV' => array (
										'justification' => 'right' 
								) 
						) 
				) );
				
				$genutzte_ktos = array_keys ( $cols_num1 );
				// print_r($genutzte_ktos);
				// die();
				$pdf->ezSetDy ( - 15 ); // abstand
				foreach ( $genutzte_ktos as $keyk ) {
					if ($keyk != 'MONAT' && $keyk != 'LEER' && $keyk != 'MV_NAME') {
						$text_k = $cols_num [$keyk] ['TXT'];
						$text_k1 = $cols_num [$keyk] ['TXT1'];
						$pdf->ezText ( "<b>$text_k</b>: $text_k1", 9 );
					}
				}
				$genutzte_ktos = array ();
				$cols_num1 = array ();
				
				$sum_keys = array_keys ( $pdf_tab [$e] [$et] );
				$anz_etz = count ( $sum_keys );
				$last_z = $sum_keys [$anz_etz - 1];
				// echo $last_z;
				$pdf->ezSetDy ( - 30 ); // abstand
				                    // echo '<pre>';
				                    
				// $pdf->ezText(EINNAHMEN_REPORT." $jahr", 15);
				                    // $pdf->ezSetDy(-20); //abstand
				                    
				// print_r($pdf_tab[$e][$et]);
				
				/* Legende */
				$anz_zeilen_et = count ( $pdf_tab [$e] [$et] );
				// echo $anz_zeilen_et;
				// print_r($pdf_tab[$e][$et][$last_z]);
				// die();
				// $pdf->ezTable($pdf_tab[$e][$et][$last_z]);
				$anz_elem = count ( $pdf_tab [$e] [$et] [$last_z] );
				$et_tab = array ();
				$et_za = 0;
				
				$kosten_ko = array ();
				$ko_z = 0;
				foreach ( $pdf_tab [$e] [$et] [$last_z] as $el_key => $el_value ) {
					// echo "$el_key $el_value<br>";
					// $pdf->ezText("<b>$el_key:</b> $el_value", 9);
					
					if ($el_key == 'FIX') {
						$bez = 'Fixed owner costs (Mng. Fee and maintenance fund)';
						$kosten_ko [$ko_z] ['BEZ'] = $bez;
						$kosten_ko [$ko_z] ['BETRAG'] = nummer_komma2punkt ( nummer_punkt2komma ( $el_value ) );
						$ko_z ++;
					}
					
					if ($el_key == 'KM_S') {
						$el_key = 'Net rent only (debit side)';
					}
					
					if ($el_key == 'NK') {
						$bez = 'Running Service Costs (debit side)';
						$kosten_ko [$ko_z] ['BEZ'] = $bez;
						$kosten_ko [$ko_z] ['BETRAG'] = nummer_komma2punkt ( nummer_punkt2komma ( $el_value * - 1 ) );
						$ko_z ++;
					}
					
					if ($el_key == 'WM_S') {
						$el_key = 'Total Rent Income (Brutto) (debit side)';
					}
					
					if (is_numeric ( $el_key )) {
						if ($el_key == '80001') {
							$bez = "$el_key - Total Rent Income (Brutto) - All payments by tenant, incl. Running service costs";
						}
						
						if ($el_key == '5020') {
							$bez = "$el_key - Transfer to owner";
							// $el_value = $el_value*-1;
						}
						
						if ($el_key == '5021') {
							$bez = "$el_key - Housing benefit";
						}
						
						if ($el_key == '1023') {
							$bez = "$el_key - Costs/repairs apartment";
						}
						
						if ($el_key == '5101') {
							$bez = "$el_key - Tenant security deposit";
						}
						
						if ($el_key == '5500') {
							$bez = "$el_key - Broker fee";
						}
						
						if ($el_key == '5600') {
							$bez = "$el_key - Tenant evacuation";
						}
						
						if ($el_key == '6000') {
							$bez = "$el_key - Housing benefit";
						}
						
						if ($el_key == '6010') {
							$bez = "$el_key - Heating costs";
						}
						
						if ($el_key == '6020') {
							$bez = "$el_key - Running costs";
						}
						
						if ($el_key == '6030') {
							$bez = "$el_key - Reserve";
						}
						
						if ($el_key == '6060') {
							$bez = "$el_key - Management fee";
						}
						
						if (empty ( $bez )) {
							$bez = $el_key;
						}
						
						// $kosten_ko[$ko_z]['BEZ'] = $el_key;
						if ($el_value != 0 && in_array ( $el_key, $kokonten )) {
							$kosten_ko [$ko_z] ['BEZ'] = $bez;
							$kosten_ko [$ko_z] ['BETRAG'] = nummer_komma2punkt ( nummer_punkt2komma ( $el_value ) );
							$bez = '';
							$ko_z ++;
						}
					}
					
					if ($el_key != 'MONAT' && $el_key != 'IHR' && $el_key != 'NK' && $el_key != 'HV' && $el_key != 'FIX' && $el_key != 'MWST' && ! is_numeric ( $el_key ) && $el_key != 'KM_SA' && $el_key != 'ET' && $el_key != 'EINHEIT') {
						$et_tab [$et_za] ['BEZ'] = $el_key;
						$et_tab [$et_za] ['BETRAG'] = nummer_komma2punkt ( nummer_punkt2komma ( $el_value ) );
						// $pdf->ezTable($pdf_tab[$e][$et][$last_z]);
						$et_za ++;
					}
				}
				ksort ( $et_tab );
				arsort ( $kosten_ko );
				
				$et_tab1 = array_sortByIndex ( $et_tab, 'BETRAG', 'SORT_DESC' );
				$kosten_ko1 = array_sortByIndex ( $kosten_ko, 'BETRAG', 'SORT_DESC' );
				
				$et_tab1 [] ['BEZ'] = ' ';
				// $pdf->ezTable($et_tab);
				// $pdf->ezTable($kosten_ko);
				
				$anz_oo = count ( $kosten_ko1 );
				$amount_et = 0;
				for($ooo = 0; $ooo < $anz_oo; $ooo ++) {
					$amount_et += $kosten_ko1 [$ooo] ['BETRAG'];
				}
				
				$kosten_ko1 [$anz_oo] ['BEZ'] = "<b>Balance</b>";
				$kosten_ko1 [$anz_oo] ['BETRAG'] = "<b>" . nummer_komma2punkt ( nummer_punkt2komma ( $amount_et ) ) . "</b>";
				
				$et_tab_new = array_merge ( $et_tab1, $kosten_ko1 );
				echo '<pre>';
				
				// print_r($kosten_ko1);
				// die();
				$cols_et = array (
						'BEZ' => 'Description',
						'BETRAG' => 'Amount' 
				);
				// $pdf->ezTable($et_tab_new, $cols_et, EINNAHMEN_REPORT." $jahr - $oo->objekt_kurzname" , array('showHeadings'=>1,'shaded'=>1, 'titleFontSize' => 9, 'fontSize' => 8, 'xPos'=>50,'xOrientation'=>'right', 'width'=>500, 'cols'=>array('BETRAG'=>array('justification'=>'right', 'width'=>100)) ));
				
				// $pdf->ezTable($et_tab_new);
				
				// die();
				
				if (is_array ( $sum_konten )) {
					
					$gki = new geldkonto_info ();
					$gki->geld_konto_ermitteln ( 'OBJEKT', $objekt_id );
					if ($gki->geldkonto_id) {
						$kr = new kontenrahmen ();
						$kr_id = $kr->get_kontenrahmen ( 'GELDKONTO', $gki->geldkonto_id );
						
						$string = '';
						$bb_keys = array_keys ( $sum_konten );
						for($bb = 0; $bb < count ( $sum_konten ); $bb ++) {
							$kto = $bb_keys [$bb];
							$kr->konto_informationen2 ( $kto, $kr_id );
							$string .= "$kto - $kr->konto_bezeichnung\n";
							// $betrag = $pdf_tab[$e][$et][$anz_zeilen_et-1][$kto];
							// $pdf->ezText("<b>$string - $betrag</b>", 9);
							unset ( $cols [$kto] );
						}
						// $pdf->ezSetDy(-20); //abstand
						// $pdf->ezText("<b>$string</b>", 9);
					}
				}
				
				$pdf_last [$et_id] = $pdf_tab [$e] [$et] [$zeile + 1];
				
				$sum_konten = array ();
				// $pdf->ezTable($pdf_tab[$e][$et]);
				// if(isset($et_id)){
				$pdf->ezNewPage ();
				// $pdf->eztext("Seite Einheit $e/$anz_e $et/$anz_et",20);
				// }
				$sum_ihr = 0;
				$sum_hv = 0;
				$sum_fix = 0;
				$sum_km_ant = 0;
				$sum_km_s = 0;
				$sum_wm_s = 0;
				$sum_nk = 0;
				$sum_mwst = 0;
			} // end for ET
				  
			// echo "<hr>";
				  
			// print_r($pdf_tab[$e]);
				  // die();
				  // $pdf->ezTable($pdf_tab[$e]);
		} // end for Einheit
		  
		// $pdf->ezTable($pdf_last);
		unset ( $cols ['M_ERG'] );
		unset ( $cols ['TXT'] );
		unset ( $cols ['MV_NAME'] );
		unset ( $cols ['KOS_BEZ'] );
		unset ( $cols ['NT'] );
		unset ( $cols ['MONAT'] );
		$cols ['EINHEIT'] = EINHEIT;
		$cols ['ET'] = ET;
		
		/* Legende */
		if (is_array ( $b_konten_arr )) {
			// echo '<pre>';
			// print_r($b_konten_arr);
			// die();
			$b_konten_arr1 = array_unique ( $b_konten_arr );
			// echo '<pre>';
			// print_r($b_konten_arr1);
			// die();
			$gki = new geldkonto_info ();
			$gki->geld_konto_ermitteln ( 'OBJEKT', $objekt_id );
			$string = '';
			if ($gki->geldkonto_id) {
				$kr = new kontenrahmen ();
				$kr_id = $kr->get_kontenrahmen ( 'GELDKONTO', $gki->geldkonto_id );
				// echo "KR: $kr_id | $gki->geldkonto_id";
				// die();
				$bb_keys = array_keys ( $b_konten_arr1 );
				for($bb = 0; $bb < count ( $b_konten_arr1 ); $bb ++) {
					$ktokey = $bb_keys [$bb];
					$kto = $b_konten_arr1 [$ktokey];
					$cols [$kto] = $kto;
					$kr->konto_informationen2 ( $kto, $kr_id );
					$string .= "<b>$kto</b> - $kr->konto_bezeichnung, ";
				}
				
				$anz_sumk = count ( $pdf_last );
				$sum_80001 = 0;
				$sum_5020 = 0;
				
				$id_keys = array_keys ( $pdf_last );
				
				for($x = 0; $x < $anz_sumk; $x ++) {
					$key = $id_keys [$x];
					$sum_80001 += $pdf_last [$key] ['80001'];
					$sum_5020 += $pdf_last [$key] ['5020'];
				}
				
				$pdf_last [$anz_sumk + 1000] ['ET'] = 'SUMME';
				$pdf_last [$anz_sumk + 1000] ['80001'] = $sum_80001;
				$pdf_last [$anz_sumk + 1000] ['5020'] = $sum_5020;
				// echo '<pre>';
				// print_r($pdf_last);
				// die();
				
				unset ( $cols ['MONAT'] );
				unset ( $cols ['IHR'] );
				unset ( $cols ['HV'] );
				unset ( $cols ['MWST'] );
				
				// $pdf->ezTable($pdf_last, $cols, UEBERSICHT." $jahr - $oo->objekt_kurzname" , array('showHeadings'=>1,'shaded'=>1, 'titleFontSize' => 7, 'fontSize' => 5, 'xPos'=>5,'xOrientation'=>'right', 'width'=>600));
				// $pdf->ezSetDy(-20); //abstand
				// $pdf->ezText("$string", 9);
			}
		}
		
		// print_r($pdf_tab);
		// die();
	}
	function pdf_income_reports2015($pdf, $objekt_id, $jahr) {
		$oo = new objekt ();
		$oo->get_objekt_infos ( $objekt_id );
		$datum_von = "$jahr-01-01";
		$datum_bis = "$jahr-12-31";
		$weg = new weg ();
		$m_arr_jahr = $weg->monatsarray_erstellen ( $datum_von, $datum_bis );
		// echo '<pre>';
		// print_r($m_arr_jahr);
		// die();
		$gk = new geldkonto_info ();
		$gk_arr = $gk->geldkonten_arr ( 'OBJEKT', $objekt_id );
		$anz_gk = count ( $gk_arr );
		
		// ###
		// print_r($gk_arr);
		// die();
		// ####
		
		$d = new detail ();
		/* Nutzenlastenwechsel */
		$nl_datum = $d->finde_detail_inhalt ( 'Objekt', $objekt_id, 'Nutzen-Lastenwechsel' );
		$nl_datum_arr = explode ( '.', $nl_datum );
		$nl_tag = $nl_datum_arr [0];
		$nl_monat = $nl_datum_arr [1];
		$nl_jahr = $nl_datum_arr [2];
		
		/* Verwaltungs�bernahme */
		$vu_datum = $d->finde_detail_inhalt ( 'Objekt', $objekt_id, 'Verwaltungs�bernahme' );
		$vu_datum_arr = explode ( '.', $vu_datum );
		$vu_tag = $vu_datum_arr [0];
		$vu_monat = $vu_datum_arr [1];
		$vu_jahr = $vu_datum_arr [2];
		
		// echo "$objekt_id $jahr";
		
		$ein_arr = $weg->einheiten_weg_tabelle_arr ( $objekt_id );
		
		$anz_e = count ( $ein_arr );
		
		// $cols = array('MONAT'=>MONAT,'NT'=>NT, 'IHR'=>IHR, 'HV'=>HV,'FIX'=>FIX, 'LEER'=>LEER, 'MV_NAME'=>MIETER, 'KM_S'=>KM_S, 'KM_SA'=>KMANT, 'M_ERG'=>ERG, 'M_ERGA'=>ERGA);
		$cols ['MONAT'] = MONAT;
		// $cols['MONAT']=MONAT;
		// #$cols['NT']=NT;
		// $cols['IHR']=IHR;
		// $cols['HV']=HV;
		// $cols['FIX']=FIX;
		// $cols['LEER'] = LEER;
		// $cols['MV_NAME'] = MIETER;
		// $cols['KOS_BEZ']=KOS_BEZ;
		// $cols['WM_S'] = WM_S;
		// $cols['MWST'] = MWST;
		// $cols['NK'] = NK;
		// $cols['KM_S'] = KM_S;
		// $cols['KM_SA'] = KM_SA;
		// $cols['M_ERG'] = M_ERG;
		// $cols['TXT'] = TXT;
		
		/* schleife Einheiten */
		for($e = 0; $e < $anz_e; $e ++) {
			$einheit_id = $ein_arr [$e] ['EINHEIT_ID'];
			$weg = new weg ();
			$et_arr = $weg->get_eigentuemer_arr ( $einheit_id );
			// echo "$einheit_id ";
			$anz_et = count ( $et_arr );
			/* Schleife f�r ET */
			
			$sum_hv = 0;
			$sum_ihr = 0;
			$sum_fix = 0;
			$sum_km_ant = 0;
			$sum_wm_s = 0;
			$sum_nk = 0;
			$sum_mwst = 0;
			$sum_km_s = 0;
			
			$sum_konten = array ();
			for($et = 0; $et < $anz_et; $et ++) {
				// print_r($et_arr);
				$et_id = $et_arr [$et] ['ID'];
				$et_von_sql = $et_arr [$et] ['VON'];
				$et_bis_sql = $et_arr [$et] ['BIS'];
				// echo "$et_id<br>";
				$weg1 = new weg ();
				$weg1->get_eigentumer_id_infos4 ( $et_id );
				$weg->get_eigentumer_id_infos4 ( $et_id );
				echo "<b>$weg1->einheit_kurzname $weg1->empf_namen</b><br>";
				
				/* Zeitarray ET */
				$vond = $jahr . '0101';
				$bisd = $jahr . '1231';
				$et_von = str_replace ( '-', '', $et_von_sql );
				if ($et_bis_sql != '0000-00-00') {
					$et_bis = str_replace ( '-', '', $et_bis_sql );
				} else {
					$et_bis = str_replace ( '-', '', "$jahr-12-31" );
				}
				
				if ($et_von > $vond) {
					$datum_von = $et_von_sql;
				}
				
				if ($et_bis < $bisd) {
					$datum_bis = $et_bis_sql;
				}
				
				if ($et_bis < $vond) {
					$datum_von = '0000-00-00';
					$datum_bis = '0000-00-00';
				}
				
				// $m_arr= $weg->monatsarray_erstellen($weg_et->eigentuemer_von,$datum_bis);
				// echo "$datum_bis - $datum_bis";
				$m_arr = $weg->monatsarray_erstellen ( $datum_von, $datum_bis );
				
				$anz_mon_et = count ( $m_arr );
				$et_mon_arr = '';
				for($me = 0; $me < $anz_mon_et; $me ++) {
					$et_mon_arr [] = $m_arr [$me] ['monat'];
				}
				// print_r($et_mon_arr);
				// die();
				
				/* Datum zur�cksetzen auf Jahresanfang bzw. Ganzjahr */
				$datum_von = "$jahr-01-01";
				$datum_bis = "$jahr-12-31";
				
				// print_r($m_arr);
				$anz_m = count ( $m_arr_jahr );
				/* Schlife Monate */
				$zeile = 0;
				for($m = 0; $m < $anz_m; $m ++) {
					
					$monat = $m_arr_jahr [$m] ['monat'];
					$jahr = $m_arr_jahr [$m] ['jahr'];
					
					/* Wenn der ET vom Monat */
					if (in_array ( $monat, $et_mon_arr )) {
						
						$key = array_search ( $monat, $et_mon_arr );
						$et_monat = $m_arr [$key] ['monat'];
						$et_jahr = $m_arr [$key] ['jahr'];
						
						$tage = $m_arr [$key] ['tage_m'];
						$n_tage = $m_arr [$key] ['tage_n'];
						
						$pdf_tab [$e] [$et] [$zeile] ['NT'] = $n_tage;
						if ($pdf_tab [$e] [$et] [$zeile - 1] ['IHR'] == '---') {
							$n_tage = $tage;
						}
						
						// ##########ANFANG FIXKOSTEN##########################
						/* FIXKOSTEN */
						/* Fixkosten Hausgeld oder Formel */
						$hg = new weg ();
						$hg->get_wg_info ( $monat, $jahr, 'Einheit', $einheit_id, 'Hausgeld' );
						$hausgeld_soll = $hg->gruppe_erg / $tage * $n_tage;
						
						/* Fixkosten nach Formel */
						$hausgeld_soll_f = (($weg->einheit_qm_weg * 0.4) + 30) / $tage * $n_tage;
						// echo "$hausgeld_soll $hausgeld_soll_f<hr>";
						
						if ($hausgeld_soll_f > $hausgeld_soll) {
							$pdf_tab [$e] [$et] [$zeile] ['MONAT'] = "<b>" . monat2name ( $et_monat ) . " $et_jahr</b>";
							$hausgeld_soll = $hausgeld_soll_f;
							$pdf_tab [$e] [$et] [$zeile] ['IHR'] = nummer_punkt2komma ( ($weg->einheit_qm_weg * - 0.4) / $tage * $n_tage );
							$sum_ihr += nummer_komma2punkt ( nummer_punkt2komma ( ($weg->einheit_qm_weg * - 0.4) / $tage * $n_tage ) );
							$pdf_tab [$e] [$et] [$zeile] ['HV'] = nummer_punkt2komma ( - 30.00 / $tage * $n_tage );
							$sum_hv += nummer_komma2punkt ( nummer_punkt2komma ( - 30.00 / $tage * $n_tage ) );
							$pdf_tab [$e] [$et] [$zeile] ['FIX'] = nummer_komma2punkt ( nummer_punkt2komma ( ((($weg->einheit_qm_weg * - 0.4) + - 30) / $tage * $n_tage) ) );
							$sum_fix += nummer_komma2punkt ( nummer_punkt2komma ( ((($weg->einheit_qm_weg * - 0.4) + - 30) / $tage * $n_tage) ) );
						} else {
							/* Wenn nicht der ET vom Monat */
							
							$pdf_tab [$e] [$et] [$zeile] ['MONAT'] = "<b>" . monat2name ( $et_monat ) . " $et_jahr</b>";
							$pdf_tab [$e] [$et] [$zeile] ['IHR'] = '0.000';
							$pdf_tab [$e] [$et] [$zeile] ['HV'] = '0.000';
							$pdf_tab [$e] [$et] [$zeile] ['FIX'] = nummer_komma2punkt ( nummer_punkt2komma ( ($hausgeld_soll * - 1) / $tage * $n_tage ) );
							$sum_fix += nummer_komma2punkt ( nummer_punkt2komma ( ($hausgeld_soll * - 1) / $tage * $n_tage ) );
						}
						// ##########ENDE FIXKOSTEN##########################
						// ##########ANFANG LEERSTAND JA NEIN##########################
						$mv_arr = array ();
						$ltm = letzter_tag_im_monat ( $et_monat, $et_jahr );
						
						$mv_arr = $this->get_mv_et_zeitraum_arr ( $einheit_id, "$et_jahr-$et_monat-01", "$et_jahr-$et_monat-$ltm" );
						
						if (is_array ( $mv_arr )) {
							// print_r($mv_arr);
							// die();
							$pdf_tab [$e] [$et] [$zeile] ['LEER'] = 'N';
							$anz_mv = count ( $mv_arr );
							// #########MIETVERTR�GE IM MONAT###########
							for($mva = 0; $mva < $anz_mv; $mva ++) {
								$mv_id = $mv_arr [$mva] ['MIETVERTRAG_ID'];
								$mvv = new mietvertraege ();
								$mvv->get_mietvertrag_infos_aktuell ( $mv_id );
								$pdf_tab [$e] [$et] [$zeile] ['MV_NAME'] = substr ( bereinige_string ( $mvv->personen_name_string ), 0, 30 );
								$mk = new mietkonto ();
								$mk->kaltmiete_monatlich ( $mv_id, $et_monat, $et_jahr );
								$sum_ford_m_inkl_mwst = $mk->summe_forderung_monatlich ( $mv_id, $et_monat, $et_jahr );
								$sum_for_arr = explode ( '|', $sum_ford_m_inkl_mwst );
								if (count ( $sum_for_arr ) > 1) {
									$wm = $sum_for_arr [0];
									$mwst = $sum_for_arr [1];
								} else {
									$wm = $sum_ford_m_inkl_mwst;
									$mwst = '0.00';
								}
								
								// $mk->summe_forderung_monatlich($mv_id, $monat, $jahr)
								$pdf_tab [$e] [$et] [$zeile] ['WM_S'] = $wm;
								$sum_wm_s += $wm;
								$pdf_tab [$e] [$et] [$zeile] ['MWST'] = $mwst;
								$sum_mwst += $mwst;
								$pdf_tab [$e] [$et] [$zeile] ['NK'] = nummer_komma2punkt ( nummer_punkt2komma ( $wm - nummer_komma2punkt ( nummer_punkt2komma ( $mk->ausgangs_kaltmiete / $tage * $n_tage ) ) ) );
								$sum_nk += $pdf_tab [$e] [$et] [$zeile] ['NK'];
								$pdf_tab [$e] [$et] [$zeile] ['KM_S'] = $mk->ausgangs_kaltmiete;
								$sum_km_s += $pdf_tab [$e] [$et] [$zeile] ['KM_S'];
								$pdf_tab [$e] [$et] [$zeile] ['KM_SA'] = nummer_komma2punkt ( nummer_punkt2komma ( $mk->ausgangs_kaltmiete / $tage * $n_tage ) );
								$sum_km_ant += $pdf_tab [$e] [$et] [$zeile] ['KM_SA'];
								/* Saldoberechnung wegen SALDO VV nicht m�glich */
								$mz = new miete ();
								// $mz->mietkonto_berechnung($mv_id);
								$mz->mietkonto_berechnung_monatsgenau ( $mv_id, $et_jahr, $et_monat );
								$pdf_tab [$e] [$et] [$zeile] ['M_ERG'] = nummer_komma2punkt ( nummer_punkt2komma ( $mz->erg ) );
								$pdf_tab [$e] [$et] [$zeile] ['M_ERGA'] = nummer_komma2punkt ( nummer_punkt2komma ( $mz->erg / $tage * $n_tage ) );
								// print_r($mz);
								// die();
								if ($anz_mv > 0) {
									$zeile ++;
								}
							}
						} else {
							$pdf_tab [$e] [$et] [$zeile] ['LEER'] = 'J';
							$pdf_tab [$e] [$et] [$zeile] ['MV_NAME'] = LEER;
						}
					}  // end if monat!!!

					else {
						// print_r($m_arr);
						$pdf_tab [$e] [$et] [$zeile] ['MONAT'] = "<b>" . monat2name ( $monat ) . " $jahr</b>";
						$pdf_tab [$e] [$et] [$zeile] ['IHR'] = '---';
						$pdf_tab [$e] [$et] [$zeile] ['HV'] = '---';
						$pdf_tab [$e] [$et] [$zeile] ['FIX'] = '---';
					}
					
					if (in_array ( $monat, $et_mon_arr )) {
						/* Schleife GELD-Konto */
						for($g = 0; $g < $anz_gk; $g ++) {
							$gk_id = $gk_arr [$g] ['KONTO_ID'];
							// echo "<b>GK: $gk_id<br></b>";
							// $zeile++;
							if (isset ( $buchungen )) {
								unset ( $buchungen );
							}
							// if(isset($mv_id)){
							if ($pdf_tab [$e] [$et] [$zeile] ['LEER'] != 'J') {
								$buchungen = $this->bebuchte_konten_brutto ( $gk_id, $einheit_id, $monat, $jahr, $et_id, $mv_id );
							} else {
								$buchungen = $this->bebuchte_konten_brutto ( $gk_id, $einheit_id, $monat, $jahr, $et_id );
							}
							if (is_array ( $buchungen )) {
								$anz_bu = count ( $buchungen );
								$gki1 = new geldkonto_info ();
								$gki1->geld_konto_details ( $gk_id );
								
								for($b = 0; $b < $anz_bu; $b ++) {
									$bkonto = $buchungen [$b] ['KONTENRAHMEN_KONTO'];
									if (! empty ( $bkonto )) {
										$b_konten_arr [] = $bkonto;
										$betrag = $buchungen [$b] ['BETRAG'];
										$kos_typ = $buchungen [$b] ['KOSTENTRAEGER_TYP'];
										$kos_id = $buchungen [$b] ['KOSTENTRAEGER_ID'];
										$vzweck = $buchungen [$b] ['VERWENDUNGSZWECK'];
										$datum = $buchungen [$b] ['DATUM'];
										$pdf_tab [$e] [$et] [$zeile] [$bkonto] = $betrag;
										$r = new rechnung ();
										$kos_bez = $r->kostentraeger_ermitteln ( $kos_typ, $kos_id );
										$pdf_tab [$e] [$et] [$zeile] ['KOS_BEZ'] = str_replace ( '<br>', '', $kos_bez );
										$pdf_tab [$e] [$et] [$zeile] ['TXT'] = "<b>$gki1->geldkonto_bez | $gki1->kredit_institut</b> - " . $vzweck;
										$pdf_tab [$e] [$et] [$zeile] ['MONAT'] = date_mysql2german ( $datum );
										$sum_konten [$bkonto] += $betrag;
										$cols [$bkonto] = $bkonto;
										$zeile ++;
									}
								}
							}
							// print_r($buchungen);
						} // end for GK
					}
					// die();
					
					$zeile ++;
				} // end for MONATE
				  // die();
				/* Summe pro ET */
				$anz_z = count ( $pdf_tab [$e] [$et] );
				$pdf_tab [$e] [$et] [$zeile + 1] ['MONAT'] = 'SUMME';
				$pdf_tab [$e] [$et] [$zeile + 1] ['IHR'] = nummer_komma2punkt ( nummer_punkt2komma ( $sum_ihr ) );
				$pdf_tab [$e] [$et] [$zeile + 1] ['HV'] = nummer_komma2punkt ( nummer_punkt2komma ( $sum_hv ) );
				$pdf_tab [$e] [$et] [$zeile + 1] ['FIX'] = nummer_komma2punkt ( nummer_punkt2komma ( $sum_fix ) );
				$pdf_tab [$e] [$et] [$zeile + 1] ['KM_S'] = nummer_komma2punkt ( nummer_punkt2komma ( $sum_km_s ) );
				$pdf_tab [$e] [$et] [$zeile + 1] ['KM_SA'] = nummer_komma2punkt ( nummer_punkt2komma ( $sum_km_ant ) );
				$pdf_tab [$e] [$et] [$zeile + 1] ['WM_S'] = nummer_komma2punkt ( nummer_punkt2komma ( $sum_wm_s ) );
				$pdf_tab [$e] [$et] [$zeile + 1] ['MWST'] = nummer_komma2punkt ( nummer_punkt2komma ( $sum_mwst ) );
				$pdf_tab [$e] [$et] [$zeile + 1] ['NK'] = nummer_komma2punkt ( nummer_punkt2komma ( $sum_nk ) );
				$pdf_tab [$e] [$et] [$zeile + 1] ['EINHEIT'] = $weg1->einheit_kurzname;
				$pdf_tab [$e] [$et] [$zeile + 1] ['ET'] = $weg1->empf_namen;
				
				// $pdf_last[$et_id] = $pdf_tab[$e][$et][$zeile+1];
				
				$bb_keys = array_keys ( $sum_konten );
				for($bb = 0; $bb < count ( $sum_konten ); $bb ++) {
					$kto = $bb_keys [$bb];
					$pdf_tab [$e] [$et] [$zeile + 1] [$kto] = nummer_komma2punkt ( nummer_punkt2komma ( $sum_konten [$kto] ) );
				}
				
				$sum_ihr = 0;
				$sum_hv = 0;
				$sum_fix = 0;
				$sum_km_ant = 0;
				$sum_km_s = 0;
				$sum_wm_s = 0;
				$sum_nk = 0;
				$sum_mwst = 0;
				// $sum_konten = array();
				$pdf->ezSetDy ( 10 ); // abstand
				$weg1->eigentuemer_von_d = date_mysql2german ( $weg1->eigentuemer_von );
				$weg1->eigentuemer_bis_d = date_mysql2german ( $weg1->eigentuemer_bis );
				$pdf->ezText ( "$weg1->einheit_kurzname Lage: $weg1->einheit_lage\n$weg1->empf_namen\nVON:$weg1->eigentuemer_von_d BIS:$weg1->eigentuemer_bis_d", 9 );
				
				// $cols = array('MONAT'=>MONAT,'NT'=>NT, 'IHR'=>IHR, 'HV'=>HV,'FIX'=>FIX, 'LEER'=>LEER, 'MV_NAME'=>MIETER, 'KM_S'=>KM_S, 'KM_SA'=>KMANT, 'M_ERG'=>ERG, 'M_ERGA'=>ERGA);
				
				// if(in_array('80001', $cols)){
				$cols ['FIX'] = FIX;
				$cols ['NK'] = NK;
				
				// }
				
				if (in_array ( '5020', $cols )) {
					$_5020_ok = true;
					unset ( $cols ['5020'] );
					// ob_clean();
					// echo '<pre>';
					// print_r($cols);
					// die();
				}
				
				if ($_5020_ok == true) {
					$cols ['5020'] = 'Transfer';
				}
				
				$pdf->ezTable ( $pdf_tab [$e] [$et], $cols, EINNAHMEN_REPORT . " $jahr  - $weg->haus_strasse $weg->haus_nummer in $weg->haus_plz $weg->haus_stadt", array (
						'showHeadings' => 1,
						'shaded' => 1,
						'titleFontSize' => 8,
						'fontSize' => 7,
						'xPos' => 40,
						'xOrientation' => 'right',
						'width' => 650,
						'cols' => array (
								'TXT' => array (
										'justification' => 'right' 
								),
								'IHR' => array (
										'justification' => 'right' 
								),
								'HV' => array (
										'justification' => 'right' 
								) 
						) 
				) );
				/* Legende */
				if (is_array ( $sum_konten )) {
					
					$gki = new geldkonto_info ();
					$gki->geld_konto_ermitteln ( 'OBJEKT', $objekt_id );
					if ($gki->geldkonto_id) {
						$kr = new kontenrahmen ();
						$kr_id = $kr->get_kontenrahmen ( 'GELDKONTO', $gki->geldkonto_id );
						
						$string = '';
						$bb_keys = array_keys ( $sum_konten );
						for($bb = 0; $bb < count ( $sum_konten ); $bb ++) {
							$kto = $bb_keys [$bb];
							$kr->konto_informationen2 ( $kto, $kr_id );
							$string .= "$kto - $kr->konto_bezeichnung\n";
							unset ( $cols [$kto] );
						}
						$pdf->ezSetDy ( - 20 ); // abstand
						$pdf->ezText ( "<b>$string</b>", 9 );
					}
				}
				
				$pdf_last [$et_id] = $pdf_tab [$e] [$et] [$zeile + 1];
				
				$sum_konten = array ();
				// $pdf->ezTable($pdf_tab[$e][$et]);
				$pdf->ezNewPage ();
			} // end for ET
				  
			// echo "<hr>";
				  
			// print_r($pdf_tab[$e]);
				  // die();
				  // $pdf->ezTable($pdf_tab[$e]);
		} // end for Einheit
		  
		// $pdf->ezTable($pdf_last);
		unset ( $cols ['M_ERG'] );
		unset ( $cols ['TXT'] );
		unset ( $cols ['MV_NAME'] );
		unset ( $cols ['KOS_BEZ'] );
		unset ( $cols ['NT'] );
		unset ( $cols ['MONAT'] );
		$cols ['EINHEIT'] = EINHEIT;
		$cols ['ET'] = ET;
		
		/* Legende */
		if (is_array ( $b_konten_arr )) {
			// echo '<pre>';
			// print_r($b_konten_arr);
			// die();
			$b_konten_arr1 = array_unique ( $b_konten_arr );
			// echo '<pre>';
			// print_r($b_konten_arr1);
			// die();
			$gki = new geldkonto_info ();
			$gki->geld_konto_ermitteln ( 'OBJEKT', $objekt_id );
			$string = '';
			if ($gki->geldkonto_id) {
				$kr = new kontenrahmen ();
				$kr_id = $kr->get_kontenrahmen ( 'GELDKONTO', $gki->geldkonto_id );
				// echo "KR: $kr_id | $gki->geldkonto_id";
				// die();
				$bb_keys = array_keys ( $b_konten_arr1 );
				for($bb = 0; $bb < count ( $b_konten_arr1 ); $bb ++) {
					$ktokey = $bb_keys [$bb];
					$kto = $b_konten_arr1 [$ktokey];
					$cols [$kto] = $kto;
					$kr->konto_informationen2 ( $kto, $kr_id );
					$string .= "<b>$kto</b> - $kr->konto_bezeichnung, ";
				}
				
				$anz_sumk = count ( $pdf_last );
				$sum_80001 = 0;
				$sum_5020 = 0;
				
				$id_keys = array_keys ( $pdf_last );
				
				for($x = 0; $x < $anz_sumk; $x ++) {
					$key = $id_keys [$x];
					$sum_80001 += $pdf_last [$key] ['80001'];
					$sum_5020 += $pdf_last [$key] ['5020'];
				}
				
				$pdf_last [$anz_sumk + 1000] ['ET'] = 'SUMME';
				$pdf_last [$anz_sumk + 1000] ['80001'] = $sum_80001;
				$pdf_last [$anz_sumk + 1000] ['5020'] = $sum_5020;
				// echo '<pre>';
				// print_r($pdf_last);
				// die();
				
				$pdf->ezTable ( $pdf_last, $cols, UEBERSICHT . " $jahr  - $oo->objekt_kurzname", array (
						'showHeadings' => 1,
						'shaded' => 1,
						'titleFontSize' => 8,
						'fontSize' => 7,
						'xPos' => 40,
						'xOrientation' => 'right',
						'width' => 760 
				) );
				$pdf->ezSetDy ( - 20 ); // abstand
				$pdf->ezText ( "$string", 9 );
			}
		}
		
		// print_r($pdf_tab);
	}
	function pdf_income_reports2014($objekt_id, $jahr) {
		$datum_bis = "$jahr-12-31";
		if (isset ( $_REQUEST ['lang'] ) && $_REQUEST ['lang'] == 'en') {
			define ( "EINNAHMEN_REPORT", "Income report" );
			define ( "OBJEKT", "object" );
			define ( "WOHNUNG", "flat" );
			define ( "EIGENTUEMER", "owner" );
			define ( "LAGE", "location" );
			define ( "TYP", "type" );
			define ( "FLAECHE", "living space" );
			
			define ( "SUMMEN", "sum [�]" );
			define ( "MONAT2", "month" );
			define ( "IHR", "for maintenance [0,40�*m�]" );
			define ( "HV", "managing fee [�]" );
			define ( "REP", "repairs [�]" );
			define ( "AUSZAHLUNG", "actual transfer [�]" );
			define ( "DATUM", "Date" );
			$lang = 'en';
		} else {
			define ( "EINNAHMEN_REPORT", "Einnahmen�bersicht" );
			define ( "OBJEKT", "Objekt" );
			define ( "WOHNUNG", "Wohnung" );
			define ( "EIGENTUEMER", "Eigent�mer" );
			define ( "LAGE", "Lage" );
			define ( "TYP", "Typ" );
			define ( "FLAECHE", "Fl�che" );
			
			define ( "SUMMEN", "Summen [�]" );
			define ( "MONAT2", "Monat" );
			define ( "IHR", "Instadh. [0,40�*m�]" );
			define ( "HV", "HV-Geb�hr [�]" );
			define ( "REP", "Reparaturen [�]" );
			define ( "AUSZAHLUNG", "Auszahlung [�]" );
			define ( "DATUM", "Datum" );
			// $cols = array('MONAT2'=>MONAT, 'IHR'=>IHR, 'HV'=>HV,'REP'=>REP,'AUSZAHLUNG'=>AUSZAHLUNG);
			$lang = 'de';
		}
		
		$d = new detail ();
		/* Nutzenlastenwechsel */
		$nl_datum = $d->finde_detail_inhalt ( 'Objekt', $objekt_id, 'Nutzen-Lastenwechsel' );
		$nl_datum_arr = explode ( '.', $nl_datum );
		$nl_tag = $nl_datum_arr [0];
		$nl_monat = $nl_datum_arr [1];
		$nl_jahr = $nl_datum_arr [2];
		
		/* Verwaltungs�bernahme */
		$vu_datum = $d->finde_detail_inhalt ( 'Objekt', $objekt_id, 'Verwaltungs�bernahme' );
		$vu_datum_arr = explode ( '.', $vu_datum );
		$vu_tag = $vu_datum_arr [0];
		$vu_monat = $vu_datum_arr [1];
		$vu_jahr = $vu_datum_arr [2];
		
		// echo "$objekt_id $jahr";
		$weg = new weg ();
		$ein_arr = $weg->einheiten_weg_tabelle_arr ( $objekt_id );
		// echo '<pre>';
		// print_r($ein_arr);
		if (is_array ( $ein_arr )) {
			
			$gk = new geldkonto_info ();
			$gk_arr = $gk->geldkonten_arr ( 'OBJEKT', $objekt_id );
			$anz_gk = count ( $gk_arr );
			if ($anz_gk == 1) {
				$gk->geld_konto_ermitteln ( 'OBJEKT', $objekt_id );
				if (! $gk->geldkonto_id) {
					die ( 'GELDKONTO fehlt' );
				}
			} else {
				echo '<pre>';
				print_r ( $gk_arr );
				die ();
			}
			
			$pdf = new Cezpdf ( 'a4', 'portrait' );
			$bpdf = new b_pdf ();
			$bpdf->b_header ( $pdf, 'Partner', $_SESSION ['partner_id'], 'portrait', 'pdfclass/fonts/Helvetica.afm', 6 );
			$pdf->ezStopPageNumbers ();
			
			$anz_e = count ( $ein_arr );
			
			for($a = 0; $a < $anz_e; $a ++) {
				$einheit_id = $ein_arr [$a] ['EINHEIT_ID'];
				// echo "$einheit_id<br>";
				
				$weg_et = new weg ();
				if (isset ( $weg_et->eigentuemer_id )) {
					unset ( $weg_et->eigentuemer_id );
				}
				$weg_et->get_last_eigentuemer ( $einheit_id );
				$weg_et->get_eigentumer_id_infos4 ( $weg_et->eigentuemer_id );
				// if($weg->einheit_typ =='Wohnraum'){
				if (isset ( $weg_et->eigentuemer_id )) {
					
					$pdf->ezText ( EINNAHMEN_REPORT . " $jahr", 14 );
					$pdf->ezText ( OBJEKT . ": $weg_et->haus_strasse $weg_et->haus_nummer, $weg_et->haus_plz  $weg_et->haus_stadt", 11 );
					$pdf->ezSetDy ( - 7 );
					$pdf->ezText ( DATUM . ": NL: $nl_datum VU: $vu_datum", 11 );
					$pdf->ezText ( WOHNUNG . ": $weg_et->einheit_kurzname " . LAGE . ": $weg_et->einheit_lage", 11 );
					$pdf->ezText ( FLAECHE . ": $weg_et->einheit_qm_weg m�", 11 );
					$pdf->ezSetDy ( - 10 );
					$pdf->ezText ( EIGENTUEMER . ":\n$weg_et->empf_namen_u", 11 );
					$pdf->ezText ( EIGENTUEMER . ":\n$weg_et->eigentuemer_von $weg_et->eigentuemer_bis", 11 );
					
					$datum_von = "$jahr-01-01";
					
					/* Datum vergleichen und festelegen */
					$vud = str_replace ( '-', '', date_german2mysql ( $vu_datum ) );
					$nld = str_replace ( '-', '', date_german2mysql ( $vu_datum ) );
					$vond = $jahr . '0101';
					$bisd = $jahr . '1231';
					$et_von = str_replace ( '-', '', $weg_et->eigentuemer_von );
					if ($weg_et->eigentuemer_bis != '0000-00-00') {
						$et_bis = str_replace ( '-', '', $weg_et->eigentuemer_bis );
					} else {
						$et_bis = str_replace ( '-', '', "$jahr-12-31" );
					}
					
					if ($et_von > $vond) {
						$datum_von = $weg_et->eigentuemer_von;
					}
					
					if ($et_bis < $bisd) {
						$datum_bis = $weg_et->eigentuemer_bis;
					}
					
					// $m_arr= $weg->monatsarray_erstellen($weg_et->eigentuemer_von,$datum_bis);
					$m_arr = $weg->monatsarray_erstellen ( $datum_von, $datum_bis );
					$datum_von = "$jahr-01-01";
					$datum_bis = "$jahr-12-31";
					
					// echo '<pre>';
					// print_r($m_arr);
					// die();
					$anz_m = count ( $m_arr );
					$sum_km = 0;
					$sum_ihr = 0;
					$sum_hv = 0;
					$sum_rep = 0;
					$sum_auszahlung = 0;
					for($b = 0; $b < $anz_m; $b ++) {
						// $li = new listen();
						$monat = $m_arr [$b] ['monat'];
						$jahr = $m_arr [$b] ['jahr'];
						
						// echo "$monat $jahr";
						// die();
						// $kost_arr = $li->get_kosten_arr('Einheit', $weg->einheit_id, $monat, $jahr, $gk->geldkonto_id,1023);
						// $summe_kosten_mon = $this->get_kosten_summe_monat('Einheit', $einheit_id, $monat, $jahr, $gk->geldkonto_id,1023);
						$summe_kosten_mon = $this->get_kosten_summe_monat ( 'Einheit', $einheit_id, $gk->geldkonto_id, $jahr, $monat, 1023 );
						$sum_rep += $summe_kosten_mon;
						// $pdf->ezText("MOnat $monat.$jahr Kosten $summe_kosten_mon", 11);
						
						/* FIXKOSTEN */
						/* Fixkosten Hausgeld oder Formel */
						$hg = new weg ();
						$hg->get_wg_info ( $monat, $jahr, 'Einheit', $einheit_id, 'Hausgeld' );
						$hausgeld_soll = $hg->gruppe_erg;
						
						/* Fixkosten nach Formel */
						$hg->get_eigentumer_id_infos4 ( $weg_et->eigentuemer_id );
						$hausgeld_soll_f = (($hg->einheit_qm_weg * 0.4) + 30);
						if ($hausgeld_soll_f > $hausgeld_soll) {
							$hausgeld_soll = $hausgeld_soll_f;
							$pdf_tab [$b] ['IHR'] = nummer_punkt2komma ( $weg_et->einheit_qm_weg * - 0.4 );
							$sum_ihr += nummer_komma2punkt ( nummer_punkt2komma ( $weg_et->einheit_qm_weg * - 0.4 ) );
							$pdf_tab [$b] ['HV'] = nummer_punkt2komma ( - 30.00 );
							$sum_hv += nummer_komma2punkt ( nummer_punkt2komma ( - 30.00 ) );
						} else {
							unset ( $pdf_tab [$b] ['IHR'] );
							unset ( $pdf_tab [$b] ['HV'] );
							$pdf_tab [$b] ['FIX'] = nummer_komma2punkt ( nummer_punkt2komma ( $hausgeld_soll * - 1 ) );
						}
						
						$monat_name = monat2name ( $monat, $lang );
						$pdf_tab [$b] ['MONAT_N'] = $monat_name;
						$pdf_tab [$b] ['MONAT2'] = "$monat_name $jahr";
						$pdf_tab [$b] ['MON'] = "$monat.$jahr";
						// $pdf_tab[$b]['KM'] = '';
						
						$pdf_tab [$b] ['REP'] = nummer_punkt2komma ( $summe_kosten_mon );
						
						$summe_auszahlung = $this->get_kosten_summe_monat ( 'Eigentuemer', $weg_et->eigentuemer_id, $gk->geldkonto_id, $jahr, $monat, 5020 );
						$pdf_tab [$b] ['AUSZAHLUNG'] = nummer_punkt2komma ( $summe_auszahlung * - 1 );
						$sum_auszahlung += nummer_komma2punkt ( nummer_punkt2komma ( $summe_auszahlung * - 1 ) );
						
						// $pdf->eztable($kost_arr);
						// unset($kost_arr);
					}
					unset ( $m_arr );
					$pdf_tab [$b + 1] ['MONAT2'] = "<b>" . SUMMEN . "</b>";
					// $pdf_tab[$b+1]['KM'] = $sum_km;
					$pdf_tab [$b + 1] ['IHR'] = "<b>" . nummer_punkt2komma_t ( $sum_ihr ) . "</b>";
					$pdf_tab [$b + 1] ['HV'] = "<b>" . nummer_punkt2komma_t ( $sum_hv ) . "</b>";
					$pdf_tab [$b + 1] ['REP'] = "<b>" . nummer_punkt2komma_t ( $sum_rep ) . "</b>";
					$pdf_tab [$b + 1] ['AUSZAHLUNG'] = "<b>" . nummer_punkt2komma_t ( $sum_auszahlung ) . "</b>";
					
					$pdf->ezSetDy ( - 20 );
					
					$cols = array (
							'MONAT2' => MONAT2,
							'IHR' => IHR,
							'HV' => HV,
							'FIX' => FIX,
							'REP' => REP,
							'AUSZAHLUNG' => AUSZAHLUNG 
					);
					$pdf->ezTable ( $pdf_tab, $cols, EINNAHMEN_REPORT . " $jahr", array (
							'showHeadings' => 1,
							'shaded' => 1,
							'titleFontSize' => 8,
							'fontSize' => 7,
							'xPos' => 50,
							'xOrientation' => 'right',
							'width' => 500,
							'cols' => array (
									'IHR' => array (
											'justification' => 'right',
											'width' => 100 
									),
									'HV' => array (
											'justification' => 'right',
											'width' => 70 
									),
									'REP' => array (
											'justification' => 'right',
											'width' => 70 
									),
									'AUSZAHLUNG' => array (
											'justification' => 'right',
											'width' => 70 
									) 
							) 
					) );
					unset ( $pdf_tab );
					$pdf->ezNewPage ();
					// print_r($weg);
					// }
				}
			}
			// die();
			ob_clean (); // ausgabepuffer leeren
			header ( "Content-type: application/pdf" ); // wird von MSIE ignoriert
			$pdf->ezStream ();
		}
	}
	function pdf_income_reports($objekt_id, $jahr) {
		$datum_bis = "$jahr-12-31";
		if (isset ( $_REQUEST ['lang'] ) && $_REQUEST ['lang'] == 'en') {
			define ( "EINNAHMEN_REPORT", "Income report" );
			define ( "OBJEKT", "object" );
			define ( "WOHNUNG", "flat" );
			define ( "EIGENTUEMER", "owner" );
			define ( "LAGE", "location" );
			define ( "TYP", "type" );
			define ( "FLAECHE", "living space" );
			
			define ( "SUMMEN", "sum [�]" );
			define ( "MONAT2", "month" );
			define ( "IHR", "for maintenance [0,40�*m�]" );
			define ( "HV", "managing fee [�]" );
			define ( "REP", "repairs [�]" );
			define ( "AUSZAHLUNG", "actual transfer [�]" );
			$lang = 'en';
		} else {
			define ( "EINNAHMEN_REPORT", "Einnahmen�bersicht" );
			define ( "OBJEKT", "Objekt" );
			define ( "WOHNUNG", "Wohnung" );
			define ( "EIGENTUEMER", "Eigent�mer" );
			define ( "LAGE", "Lage" );
			define ( "TYP", "Typ" );
			define ( "FLAECHE", "Fl�che" );
			
			define ( "SUMMEN", "Summen [�]" );
			define ( "MONAT2", "Monat" );
			define ( "IHR", "Instadh. [0,40�*m�]" );
			define ( "HV", "HV-Geb�hr [�]" );
			define ( "REP", "Reparaturen [�]" );
			define ( "AUSZAHLUNG", "Auszahlung [�]" );
			// $cols = array('MONAT2'=>MONAT, 'IHR'=>IHR, 'HV'=>HV,'REP'=>REP,'AUSZAHLUNG'=>AUSZAHLUNG);
			$lang = 'de';
		}
		
		$gk = new geldkonto_info ();
		$gk->geld_konto_ermitteln ( 'OBJEKT', $objekt_id );
		if (! $gk->geldkonto_id) {
			die ( 'GELDKONTO fehlt' );
		}
		
		// echo "$objekt_id $jahr";
		$weg = new weg ();
		$ein_arr = $weg->einheiten_weg_tabelle_arr ( $objekt_id );
		// echo '<pre>';
		// print_r($ein_arr);
		if (is_array ( $ein_arr )) {
			
			$pdf = new Cezpdf ( 'a4', 'portrait' );
			$bpdf = new b_pdf ();
			$bpdf->b_header ( $pdf, 'Partner', $_SESSION ['partner_id'], 'portrait', 'pdfclass/fonts/Helvetica.afm', 6 );
			$pdf->ezStopPageNumbers ();
			
			$anz_e = count ( $ein_arr );
			
			for($a = 0; $a < $anz_e; $a ++) {
				$einheit_id = $ein_arr [$a] ['EINHEIT_ID'];
				// echo "$einheit_id<br>";
				
				$weg_et = new weg ();
				if (isset ( $weg_et->eigentuemer_id )) {
					unset ( $weg_et->eigentuemer_id );
				}
				$weg_et->get_last_eigentuemer ( $einheit_id );
				$weg_et->get_eigentumer_id_infos3 ( $weg_et->eigentuemer_id );
				// if($weg->einheit_typ =='Wohnraum'){
				if (isset ( $weg_et->eigentuemer_id )) {
					$pdf->ezText ( EINNAHMEN_REPORT . " $jahr", 14 );
					$pdf->ezText ( OBJEKT . ": $weg_et->haus_strasse $weg_et->haus_nummer, $weg_et->haus_plz  $weg_et->haus_stadt", 11 );
					$pdf->ezSetDy ( - 7 );
					$pdf->ezText ( WOHNUNG . ": $weg_et->einheit_kurzname " . LAGE . ": $weg_et->einheit_lage", 11 );
					$pdf->ezText ( FLAECHE . ": $weg_et->einheit_qm_weg m�", 11 );
					$pdf->ezSetDy ( - 10 );
					$pdf->ezText ( EIGENTUEMER . ":\n$weg_et->empf_namen_u", 11 );
					
					$datum_von = "$jahr-01-01";
					// $m_arr= $weg->monatsarray_erstellen($weg_et->eigentuemer_von,$datum_bis);
					$m_arr = $weg->monatsarray_erstellen ( $datum_von, $datum_bis );
					// print_r($m_arr);
					// die();
					$anz_m = count ( $m_arr );
					$sum_km = 0;
					$sum_ihr = 0;
					$sum_hv = 0;
					$sum_rep = 0;
					$sum_auszahlung = 0;
					for($b = 0; $b < $anz_m; $b ++) {
						// $li = new listen();
						$monat = $m_arr [$b] ['monat'];
						$jahr = $m_arr [$b] ['jahr'];
						// echo "$monat $jahr";
						// die();
						// $kost_arr = $li->get_kosten_arr('Einheit', $weg->einheit_id, $monat, $jahr, $gk->geldkonto_id,1023);
						// $summe_kosten_mon = $this->get_kosten_summe_monat('Einheit', $einheit_id, $monat, $jahr, $gk->geldkonto_id,1023);
						$summe_kosten_mon = $this->get_kosten_summe_monat ( 'Einheit', $einheit_id, $gk->geldkonto_id, $jahr, $monat, 1023 );
						$sum_rep += $summe_kosten_mon;
						// $pdf->ezText("MOnat $monat.$jahr Kosten $summe_kosten_mon", 11);
						
						$monat_name = monat2name ( $monat, $lang );
						$pdf_tab [$b] ['MONAT_N'] = $monat_name;
						$pdf_tab [$b] ['MONAT2'] = "$monat_name $jahr";
						$pdf_tab [$b] ['MON'] = "$monat.$jahr";
						// $pdf_tab[$b]['KM'] = '';
						$pdf_tab [$b] ['IHR'] = nummer_punkt2komma ( $weg_et->einheit_qm_weg * - 0.4 );
						$sum_ihr += nummer_komma2punkt ( nummer_punkt2komma ( $weg_et->einheit_qm_weg * - 0.4 ) );
						$pdf_tab [$b] ['HV'] = nummer_punkt2komma ( - 30.00 );
						$sum_hv += nummer_komma2punkt ( nummer_punkt2komma ( - 30.00 ) );
						$pdf_tab [$b] ['REP'] = nummer_punkt2komma ( $summe_kosten_mon );
						
						$summe_auszahlung = $this->get_kosten_summe_monat ( 'Eigentuemer', $weg_et->eigentuemer_id, $gk->geldkonto_id, $jahr, $monat, 5020 );
						$pdf_tab [$b] ['AUSZAHLUNG'] = nummer_punkt2komma ( $summe_auszahlung * - 1 );
						$sum_auszahlung += nummer_komma2punkt ( nummer_punkt2komma ( $summe_auszahlung * - 1 ) );
						
						// $pdf->eztable($kost_arr);
						// unset($kost_arr);
					}
					
					$pdf_tab [$b + 1] ['MONAT2'] = "<b>" . SUMMEN . "</b>";
					// $pdf_tab[$b+1]['KM'] = $sum_km;
					$pdf_tab [$b + 1] ['IHR'] = "<b>" . nummer_punkt2komma_t ( $sum_ihr ) . "</b>";
					$pdf_tab [$b + 1] ['HV'] = "<b>" . nummer_punkt2komma_t ( $sum_hv ) . "</b>";
					$pdf_tab [$b + 1] ['REP'] = "<b>" . nummer_punkt2komma_t ( $sum_rep ) . "</b>";
					$pdf_tab [$b + 1] ['AUSZAHLUNG'] = "<b>" . nummer_punkt2komma_t ( $sum_auszahlung ) . "</b>";
					
					$pdf->ezSetDy ( - 20 );
					
					$cols = array (
							'MONAT2' => MONAT2,
							'IHR' => IHR,
							'HV' => HV,
							'REP' => REP,
							'AUSZAHLUNG' => AUSZAHLUNG 
					);
					$pdf->ezTable ( $pdf_tab, $cols, EINNAHMEN_REPORT, array (
							'showHeadings' => 1,
							'shaded' => 1,
							'titleFontSize' => 8,
							'fontSize' => 7,
							'xPos' => 50,
							'xOrientation' => 'right',
							'width' => 500,
							'cols' => array (
									'IHR' => array (
											'justification' => 'right',
											'width' => 100 
									),
									'HV' => array (
											'justification' => 'right',
											'width' => 70 
									),
									'REP' => array (
											'justification' => 'right',
											'width' => 70 
									),
									'AUSZAHLUNG' => array (
											'justification' => 'right',
											'width' => 70 
									) 
							) 
					) );
					unset ( $pdf_tab );
					$pdf->ezNewPage ();
					// print_r($weg);
					// }
				}
			}
			// die();
			ob_clean (); // ausgabepuffer leeren
			header ( "Content-type: application/pdf" ); // wird von MSIE ignoriert
			$pdf->ezStream ();
		}
	}
	function form_sepa_ueberweisung_et($e_id, $betrag) {
		// echo "$e_id $betrag";
		$gk = new geldkonto_info ();
		$gk->geld_konto_ermitteln ( 'OBJEKT', $_SESSION ['objekt_id'] );
		if (! $gk->geldkonto_id) {
			die ( 'Geldkonto vom Objekt nicht bekannt!' );
		}
		
		$betrag = nummer_punkt2komma ( $betrag );
		$weg = new weg ();
		$weg->get_eigentumer_id_infos3 ( $e_id );
		// echo '<pre>';
		// print_r($weg);
		
		$f = new formular ();
		$f->erstelle_formular ( 'SEPA �BERWEISUNG', null );
		$f->text_feld_inaktiv ( 'KONTO', 'kto', $gk->bez, 100, 'kto' );
		$f->text_feld_inaktiv ( 'EINHEIT', 'eig', "$weg->einheit_kurzname", 25, 'eig' );
		$f->text_feld_inaktiv ( "EIGENT�MER ($weg->anz_personen)", 'eig', "$weg->empf_namen", 100, 'eig' );
		$monat = date ( "m" );
		$jahr = date ( "Y" );
		
		$f->text_feld ( 'VERWENDUNG', 'vzweck', "$weg->einheit_kurzname $monat.$jahr / Transfer to owner / Auszahlung", 100, 'vzweck', '' );
		$f->text_feld ( 'BETRAG', 'betrag', $betrag, 20, 'betrag', '' );
		$sep = new sepa ();
		if ($sep->dropdown_sepa_geldkonten ( 'Empf�ngerkonto', 'empf_sepa_gk_id', 'empf_sepa_gk_id', 'Eigentuemer', $e_id ) != false) {
			// if($gk->dropdown_geldkonten_k('GKONTO', 'empf_sepa_gk_id', 'empf_sepa_gk_id', 'Eigentuemer', $e_id)){
			$f->hidden_feld ( 'option', 'sepa_sammler_hinzu' );
			$f->hidden_feld ( 'kat', 'ET-AUSZAHLUNG' );
			$f->hidden_feld ( 'gk_id', $gk->geldkonto_id );
			$f->hidden_feld ( 'kos_typ', 'Eigentuemer' );
			$f->hidden_feld ( 'kos_id', $e_id );
			// $f->text_feld('Buchungskonto', 'konto', 5020, 20, 'konto', '');
			$kk = new kontenrahmen ();
			$kk->dropdown_kontorahmenkonten_vorwahl ( 'Buchungskonto', 'konto', 'konto', 'GELDKONTO', $_SESSION ['geldkonto_id'], '', '5020' );
			// $kk->dropdown_kontorahmenkonten_vorwahl('Buchungskonto', 'konto', 'konto', 'Partner', $_SESSION['partner_id'], '', 4000);
			$f->send_button ( 'sndBtn', 'Hinzuf�gen' );
		}
		$f->ende_formular ();
	}
	function pdf_bericht_se($objekt_id, $monat, $jahr, $lang = 'de') {
		echo "PDF-Bericht SE";
		if (! isset ( $_SESSION ['geldkonto_id'] )) {
			fehlermeldung_ausgeben ( "Geldkonto w�hlen" );
		}
		$weg = new weg ();
		$ein_arr = $weg->einheiten_weg_tabelle_arr ( $objekt_id );
		echo '<pre>';
		if (is_array ( $ein_arr )) {
			$anz_e = count ( $ein_arr );
			for($a = 0; $a < $anz_e; $a ++) {
				$einheit_id = $ein_arr [$a] ['EINHEIT_ID'];
				$weg1 = new weg ();
				$weg1->get_last_eigentuemer ( $einheit_id );
				if (isset ( $weg1->eigentuemer_id )) {
					$ein_arr [$a] ['ET_ID'] = $weg1->eigentuemer_id;
					unset ( $weg1->eigentuemer_id );
				}
				$ein_arr [$a] ['HAUSGELD_SOLL'] = $weg1->get_monatliche_def ( $monat, $jahr, 'Einheit', $einheit_id );
				if (is_array ( $ein_arr [$a] ['HAUSGELD_SOLL'] )) {
					$anz_def = count ( $ein_arr [$a] ['HAUSGELD_SOLL'] );
					$ein_arr [$a] ['HAUSGELD_SOLL_G'] = 0.00;
					for($def = 0; $def < $anz_def; $def ++) {
						$ein_arr [$a] ['HAUSGELD_SOLL_G'] += $ein_arr [$a] ['HAUSGELD_SOLL'] [$def] ['SUMME'];
					}
				} else {
					$ein_arr [$a] ['HAUSGELD_SOLL_G'] = 0.00;
				}
				
				/* MV */
				$ee = new einheit ();
				$mv_id = $ee->get_mietvertrag_id ( $einheit_id );
				if ($mv_id) {
					$ein_arr [$a] ['MV_ID'] = $mv_id;
					$mk = new mietkonto ();
					$mk->kaltmiete_monatlich ( $mv_id, $monat, $jahr );
					$ein_arr [$a] ['KM_SOLL'] = $mk->ausgangs_kaltmiete;
				} else {
					$ein_arr [$a] ['KM_SOLL'] = 0.00;
				}
				
				/* Transfer SOLL */
				$ein_arr [$a] ['TRANSFER_SOLL'] = $ein_arr [$a] ['KM_SOLL'] - $ein_arr [$a] ['HAUSGELD_SOLL_G'];
				/* Kssten bzw. IST Buchungen */
				$et_id = $ein_arr [$a] ['ET_ID'];
				$ein_arr [$a] ['BUCHUNGEN'] = $this->bebuchte_konten ( $_SESSION ['geldkonto_id'], $einheit_id, $monat, $jahr, $et_id, $mv_id );
			}
		}
		
		$einheit_arr = $ein_arr [1];
		echo "<table class=\"sortable\">";
		$ek = $einheit_arr ['EINHEIT_KURZNAME'];
		echo "<tr><th colspan=\"3\">$ek</th><th>SOLL</th><th>IST</th></tr>";
		echo "<tr><th colspan=\"3\">SOLLKOSTEN</th></tr>";
		
		$anz_hg = count ( $einheit_arr ['HAUSGELD_SOLL'] );
		for($a = 0; $a < $anz_hg; $a ++) {
			$koskat_soll = $einheit_arr ['HAUSGELD_SOLL'] [$a] ['KOSTENKAT'];
			$summe_soll = $einheit_arr ['HAUSGELD_SOLL'] [$a] ['SUMME'];
			$kto_soll = $einheit_arr ['HAUSGELD_SOLL'] [$a] ['E_KONTO'];
			$datum_soll = "01.$monat.$jahr";
			echo "<tr><td>$datum_soll</td><td>$kto_soll</td><td>$koskat_soll</td><td>$summe_soll</td><td></td></tr>";
		}
		$hausgeld_g = $einheit_arr ['HAUSGELD_SOLL_G'];
		echo "<tr><th>$datum_soll</th><th></th><th>Gesamt</th><th>$hausgeld_g</th><th></th></tr>";
		echo "<tr><td></td><td></td><td></td><td></td><td></td></tr>";
		
		$anz_bu = count ( $einheit_arr ['BUCHUNGEN'] );
		echo "<tr><th colspan=\"3\">ISTKOSTEN</th></tr>";
		for($a = 0; $a < $anz_bu; $a ++) {
			$kto_ist = $einheit_arr ['BUCHUNGEN'] [$a] ['KONTENRAHMEN_KONTO'];
			$koskat_ist = $einheit_arr ['BUCHUNGEN'] [$a] ['VERWENDUNGSZWECK'];
			$summe_ist = $einheit_arr ['BUCHUNGEN'] [$a] ['BETRAG'];
			$datum_ist = date_mysql2german ( $einheit_arr ['BUCHUNGEN'] [$a] ['DATUM'] );
			echo "<tr><td>$datum_ist</td><td>$kto_ist</td><td>$koskat_ist</td><td></td><td>$summe_ist</td></tr>";
		}
		
		echo "</table>";
		
		print_r ( $ein_arr );
	}
	function bebuchte_konten($gk_id, $einheit_id, $monat, $jahr, $et_id, $mv_id = null) {
		// echo "$gk_id, $einheit_id, $et_id, $monat, $jahr, $mv_id<br>";
		if ($mv_id != null) {
			$db_abfrage = "SELECT * FROM `GELD_KONTO_BUCHUNGEN` WHERE `GELDKONTO_ID` = '$gk_id' AND DATE_FORMAT( `DATUM` , '%Y-%m' ) = '$jahr-$monat' AND ((`KOSTENTRAEGER_TYP` = 'Einheit' AND `KOSTENTRAEGER_ID` = '$einheit_id') OR (`KOSTENTRAEGER_TYP` = 'Mietvertrag' AND `KOSTENTRAEGER_ID` = '$mv_id') OR (`KOSTENTRAEGER_TYP` = 'Eigentuemer' AND `KOSTENTRAEGER_ID` = '$et_id'))    AND `AKTUELL` = '1' ORDER BY DATUM";
		} else {
			$db_abfrage = "SELECT * FROM `GELD_KONTO_BUCHUNGEN` WHERE `GELDKONTO_ID` = '$gk_id' AND DATE_FORMAT( `DATUM` , '%Y-%m' ) = '$jahr-$monat' AND ((`KOSTENTRAEGER_TYP` = 'Einheit' AND `KOSTENTRAEGER_ID` = '$einheit_id') OR  (`KOSTENTRAEGER_TYP` = 'Eigentuemer' AND `KOSTENTRAEGER_ID` = '$et_id'))    AND `AKTUELL` = '1' ORDER BY DATUM";
		}
		// echo $db_abfrage;
		$result = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			while ( $row = mysql_fetch_assoc ( $result ) ) {
				// echo '<pre>';
				// print_r($row);
				
				$kos_typ = $row ['KOSTENTRAEGER_TYP'];
				$kos_id = $row ['KOSTENTRAEGER_ID'];
				$betrag = $row ['BETRAG'];
				if ($kos_typ == 'Mietvertrag') {
					/* Nebenkosten abziehen */
					if ($mv_id != null) {
						$mk = new mietkonto ();
						$mk->kaltmiete_monatlich ( $kos_id, $monat, $jahr );
						
						$row ['VERWENDUNGSZWECK'] = $row ['VERWENDUNGSZWECK'] . " Brutto " . $row ['BETRAG'];
						if ($betrag > 0) {
							$row ['BETRAG'] = $mk->ausgangs_kaltmiete;
						}
					}
				}
				$my_array [] = $row;
			}
			// print_r($my_array);
			return $my_array;
			// echo "<hr>";
		}
	}
	function bebuchte_konten_brutto($gk_id, $einheit_id, $monat, $jahr, $et_id, $mv_arr = null) {
		// echo "$gk_id, $einheit_id, $et_id, $monat, $jahr, $mv_id<br>";
		if ($mv_arr != null) {
			$anz_mv = count ( $mv_arr );
			$mv_string = '';
			for($m = 0; $m < $anz_mv; $m ++) {
				$mv_id = $mv_arr [$m] ['MIETVERTRAG_ID'];
				$mv_string .= " OR (`KOSTENTRAEGER_TYP` = 'Mietvertrag' AND `KOSTENTRAEGER_ID` = '$mv_id') ";
			}
			
			$db_abfrage = "SELECT * FROM `GELD_KONTO_BUCHUNGEN` WHERE `GELDKONTO_ID` = '$gk_id' AND DATE_FORMAT( `DATUM` , '%Y-%m' ) = '$jahr-$monat' AND ((`KOSTENTRAEGER_TYP` = 'Einheit' AND `KOSTENTRAEGER_ID` = '$einheit_id')  OR (`KOSTENTRAEGER_TYP` = 'Eigentuemer' AND `KOSTENTRAEGER_ID` = '$et_id') $mv_string)    AND `AKTUELL` = '1' ORDER BY DATUM";
		} else {
			$db_abfrage = "SELECT * FROM `GELD_KONTO_BUCHUNGEN` WHERE `GELDKONTO_ID` = '$gk_id' AND DATE_FORMAT( `DATUM` , '%Y-%m' ) = '$jahr-$monat' AND ((`KOSTENTRAEGER_TYP` = 'Einheit' AND `KOSTENTRAEGER_ID` = '$einheit_id') OR  (`KOSTENTRAEGER_TYP` = 'Eigentuemer' AND `KOSTENTRAEGER_ID` = '$et_id'))    AND `AKTUELL` = '1' ORDER BY DATUM";
		}
		// echo $db_abfrage;
		$result = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			while ( $row = mysql_fetch_assoc ( $result ) ) {
				$my_array [] = $row;
			}
			// print_r($my_array);
			return $my_array;
			// echo "<hr>";
		}
	}
	function kto_auszug_einheit($einheit_id) {
		$e = new einheit ();
		$e->get_einheit_info ( $einheit_id );
		// echo '<pre>';
		// print_r($e);
		$weg = new weg ();
		$weg->get_last_eigentuemer ( $einheit_id );
		// print_r($weg);
		
		$e_id = $weg->eigentuemer_id;
		$von = $weg->von;
		$bis = $weg->bis;
		if ($bis = '0000-00-00') {
			$bis = date ( "Y-m-d" );
		}
		
		$weg->get_eigentumer_id_infos3 ( $e_id );
		$et_qm = $weg->einheit_qm_weg;
		
		$monats_array = $this->monats_array ( $von, $bis );
		
		// print_r($monats_array);
		$anz_monate = count ( $monats_array );
		$buchungen_arr = array ();
		for($a = 0; $a < $anz_monate; $a ++) {
			$monat = $monats_array [$a] ['MONAT'];
			$jahr = $monats_array [$a] ['JAHR'];
			
			$mv_id = $this->get_mv_monat ( $einheit_id, $monat, $jahr );
			
			$buchungen_arr [$a] = $this->bebuchte_konten ( $_SESSION ['geldkonto_id'], $einheit_id, $monat, $jahr, $e_id, $mv_id );
			$anz_b = count ( $buchungen_arr [$a] );
			$buchungen_arr [$a] [$anz_b] ['KONTENRAHMEN_KONTO'] = "6000";
			$buchungen_arr [$a] [$anz_b] ['KOSTENTRAEGER_TYP'] = "Einheit";
			$buchungen_arr [$a] [$anz_b] ['DATUM'] = "$jahr-$monat-01";
			$buchungen_arr [$a] [$anz_b] ['BETRAG'] = $weg->get_sume_hausgeld ( 'Einheit', $einheit_id, $monat, $jahr );
			$buchungen_arr [$a] [$anz_b] ['VERWENDUNGSZWECK'] = 'HAUSGELD';
			/*
			 * $buchungen_arr[$a][$anz_b+1]['KONTENRAHMEN_KONTO'] = "6030";
			 * $buchungen_arr[$a][$anz_b+1]['KOSTENTRAEGER_TYP'] = "Einheit";
			 * $buchungen_arr[$a][$anz_b+1]['DATUM'] = "$jahr-$monat-01";
			 * $buchungen_arr[$a][$anz_b+1]['BETRAG'] = $weg->get_sume_hausgeld('EInheit', $einheit_id, $monat, $jahr);
			 * $buchungen_arr[$a][$anz_b+1]['VERWENDUNGSZWECK'] = 'IHR';
			 */
			$buchungen_arr [$a] ['MONAT'] = $monat; //
			$buchungen_arr [$a] ['JAHR'] = $jahr;
		}
		// print_r($buchungen_arr);
		// print_r($weg);
		
		$anz_mon = count ( $buchungen_arr );
		echo "<table class=\"sortable\">";
		echo "<tr><td>Datum</td><td>kos_typ</td><td>konto</td><td>text</td><td>Betrag</td></tr>";
		$sum = 0;
		for($a = 0; $a < $anz_mon; $a ++) {
			$monat = $buchungen_arr [$a];
			$anz_buch = count ( $monat );
			$akt_monat = $buchungen_arr [$a] ['MONAT'];
			$akt_jahr = $buchungen_arr [$a] ['JAHR'];
			echo "<tr><th colspan=\"5\">$akt_monat/$akt_jahr</th></tr>";
			for($b = 0; $b < $anz_buch - 2; $b ++) {
				$betrag = $monat [$b] ['BETRAG'];
				$konto = $monat [$b] ['KONTENRAHMEN_KONTO'];
				$datum = date_mysql2german ( $monat [$b] ['DATUM'] );
				$kos_typ = $monat [$b] ['KOSTENTRAEGER_TYP'];
				$text = $monat [$b] ['VERWENDUNGSZWECK'];
				$sum += $betrag;
				echo "<tr><td>$datum</td><td>$kos_typ</td><td>$konto</td><td>$text</td><td>$betrag</td></tr>";
			}
			
			echo "<tr><td></td><td></td><td></td><th>MONATSSALDO</th><th>";
			if ($sum > 0) {
				echo "<b>$sum</b>";
			} else {
				fehlermeldung_ausgeben ( $sum );
			}
			echo "</th></tr>";
			echo "<tr><td></td><td></td><td></td><td></td><td></td></tr>";
		}
		echo "</table>";
	}
	function get_mv_monat($einheit_id, $monat, $jahr) {
		$datum_von = "$jahr-$monat-01";
		$ltag = letzter_tag_im_monat ( $monat, $jahr );
		$datum_bis = "$jahr-$monat-$ltag";
		$result = mysql_query ( "SELECT MIETVERTRAG_ID FROM MIETVERTRAG WHERE EINHEIT_ID = '$einheit_id' && MIETVERTRAG_AKTUELL = '1'  && MIETVERTRAG_VON<='$datum_bis' && ( MIETVERTRAG_BIS >= '$datum_von' OR MIETVERTRAG_BIS = '0000-00-00' ) ORDER BY MIETVERTRAG_VON DESC LIMIT 0 , 1 " );
		$numrows = mysql_numrows ( $result );
		
		if ($numrows) {
			$row = mysql_fetch_assoc ( $result );
			return $row ['MIETVERTRAG_ID'];
		}
	}
	function get_mv_zeitraum_arr($einheit_id, $monat, $jahr) {
		$datum_von = "$jahr-$monat-01";
		$ltag = letzter_tag_im_monat ( $monat, $jahr );
		$datum_bis = "$jahr-$monat-$ltag";
		$result = mysql_query ( "SELECT MIETVERTRAG_ID FROM MIETVERTRAG WHERE EINHEIT_ID = '$einheit_id' && MIETVERTRAG_AKTUELL = '1'  && MIETVERTRAG_VON<='$datum_bis' && ( MIETVERTRAG_BIS >= '$datum_von' OR MIETVERTRAG_BIS = '0000-00-00' ) ORDER BY MIETVERTRAG_VON DESC LIMIT 0 , 1 " );
		// echo "<br><br><br>SELECT MIETVERTRAG_ID FROM MIETVERTRAG WHERE EINHEIT_ID = '$einheit_id' && MIETVERTRAG_AKTUELL = '1' && MIETVERTRAG_VON<='$datum_bis' && ( MIETVERTRAG_BIS >= '$datum_von' OR MIETVERTRAG_BIS = '0000-00-00' ) ORDER BY MIETVERTRAG_VON DESC LIMIT 0 , 1 ";
		// die();
		$numrows = mysql_numrows ( $result );
		
		if ($numrows) {
			$row = mysql_fetch_assoc ( $result );
			// die($row['MIETVERTRAG_ID']);
			return $row ['MIETVERTRAG_ID'];
		}
	}
	function get_mv_et_zeitraum_arr($einheit_id, $datum_von, $datum_bis) {
		$db_abfrage = "SELECT * FROM MIETVERTRAG WHERE EINHEIT_ID = '$einheit_id' && MIETVERTRAG_AKTUELL = '1'  && MIETVERTRAG_VON<='$datum_bis' && ( MIETVERTRAG_BIS > '$datum_von' OR MIETVERTRAG_BIS = '0000-00-00' ) ORDER BY MIETVERTRAG_VON ASC";
		// echo $db_abfrage;
		$result = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			while ( $row = mysql_fetch_assoc ( $result ) )
				$my_array [] = $row;
			return $my_array;
		}
	}
	function monats_array($von, $bis) {
		if ($bis == '0000-00-00') {
			$bis = date ( "Y-m-d" );
		}
		
		$mz = new miete ();
		$monate = $mz->diff_in_monaten ( $von, $bis );
		
		$von_arr = explode ( '-', $von );
		$monat = $von_arr [1];
		$jahr = $von_arr [0];
		
		$monats_array = array ();
		for($m = 0; $m < $monate; $m ++) {
			
			if ($monat < 12) {
				$monats_array [$m] ['MONAT'] = sprintf ( '%02d', $monat );
				$monats_array [$m] ['JAHR'] = $jahr;
				$monat ++;
			} else {
				$monats_array [$m] ['MONAT'] = sprintf ( '%02d', $monat );
				$monats_array [$m] ['JAHR'] = $jahr;
				$monat = 1;
				$jahr ++;
			}
		}
		return $monats_array;
	}
	function form_profil_neu() {
		$f = new formular ();
		$f->erstelle_formular ( 'Neues Profil f�r die Berichte erstellen', null );
		$f->text_feld ( 'Kurzbeschreibung', 'kurz_b', '', 50, 'kurz_b', null );
		$o = new objekt ();
		$o->dropdown_objekte ( 'objekt_id', 'objekt_id' );
		$sep = new sepa ();
		if (isset ( $_SESSION ['geldkonto_id'] )) {
			$gk = new geldkonto_info ();
			$gk->geld_konto_details ( $_SESSION ['geldkonto_id'] );
			$filter_bez = $gk->geldkonto_bez;
		} else {
			$filter_bez = '';
		}
		$sep->dropdown_sepa_geldkonten_filter ( 'Geldkonto w�hlen', 'gk_id', 'gk_id', $filter_bez );
		$p = new partner ();
		$p->partner_dropdown ( 'Hausverwaltung w�hlen', 'p_id', 'p_id' );
		$f->hidden_feld ( 'option', 'step2' );
		$f->send_button ( 'snd_listenProf', 'Weiter zu Schritt 2' );
		$f->ende_formular ();
	}
	function report_profil_anlegen($kurz_b, $objekt_id, $gk_id, $p_id) {
		$last_id = last_id2 ( 'REPORT_PROFILE', 'ID' ) + 1;
		$db_abfrage = "INSERT INTO REPORT_PROFILE VALUES (NULL, '$last_id', '$kurz_b', '$objekt_id', '$gk_id', '$p_id', '1')";
		$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		/* Protokollieren */
		$last_dat = mysql_insert_id ();
		protokollieren ( 'REPORT_PROFILE', $last_dat, '0' );
		return $last_id;
	}
	function form_profil_step2($profil_id) {
		$this->get_r_profil_infos ( $profil_id );
		$f = new formular ();
		$f->erstelle_formular ( 'Buchungskonten f�r das Profil w�hlen', null );
		$kr = new kontenrahmen ();
		$kr_id = $kr->get_kontenrahmen ( 'GELDKONTO', $this->gk_id );
		// echo "$this->kurz_b $kr_id";
		$arr = $kr->konten_in_arr_rahmen ( $kr_id );
		// echo '<pre>';
		// print_r($arr);
		if (! is_array ( ($arr) )) {
			fehlermeldung_ausgeben ( "Kontenrahmen unbekannt!" );
			die ();
		} else {
			// echo '<pre>';
			$anz = count ( $arr );
			$b_konten = $this->profil_liste_konten_arr ( $profil_id );
			// echo '<pre>';
			// print_r($b_konten);
			for($a = 0; $a < $anz; $a ++) {
				$konto = $arr [$a] ['KONTO'];
				$bez = $arr [$a] ['BEZEICHNUNG'];
				if (! in_array ( $konto, $b_konten )) {
					$f->check_box_js1 ( "b_konten[$a]", 'b_konto' . $a, $konto, "$konto $bez", null, '' );
				} else {
					$f->check_box_js1 ( "b_konten[$a]", 'b_konto' . $a, $konto, "$konto $bez", null, 'checked' );
				}
				$f->hidden_feld ( "bez_arr[$a]", $bez );
			}
		}
		// print_r($bez_arr);
		$f->send_button ( 'Snd_konten', 'speichern' );
		$f->hidden_feld ( 'option', 'konten_bearbeiten' );
		$f->hidden_feld ( 'profil_id', $profil_id );
		$f->ende_formular ();
	}
	function profil_liste_arr() {
		// echo "Profile hier";
		$db_abfrage = "SELECT * FROM REPORT_PROFILE WHERE AKTUELL='1' ORDER BY KURZ_B";
		$result = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		while ( $row = mysql_fetch_assoc ( $result ) ) {
			$arr [] = $row;
		}
		if (isset ( $arr )) {
			return $arr;
		}
	}
	function profil_liste_konten_arr($profil_id) {
		// echo "Profile hier";
		$db_abfrage = "SELECT KONTO FROM REPORT_PROFILE_K WHERE PROFIL_ID='$profil_id' ORDER BY KONTO ASC";
		$result = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		$arr = array ();
		while ( $row = mysql_fetch_assoc ( $result ) ) {
			$arr [] = $row ['KONTO'];
		}
		return $arr;
	}
	function bk_konten_arr($profil_id) {
		// echo "Profile hier";
		$db_abfrage = "SELECT * FROM REPORT_PROFILE_K WHERE PROFIL_ID='$profil_id' ORDER BY KONTO ASC";
		// echo $db_abfrage;
		$result = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		$arr;
		while ( $row = mysql_fetch_assoc ( $result ) ) {
			$arr [] = $row;
		}
		if (isset ( $arr ) && is_array ( $arr )) {
			return $arr;
		}
	}
	function profil_liste() {
		// unset($_SESSION['r_profil_id']);
		if (isset ( $_SESSION ['r_profil_id'] )) {
			$this->get_r_profil_infos ( $_SESSION ['r_profil_id'] );
			fehlermeldung_ausgeben ( "Aktuelles Profil: $this->kurz_b" );
			$_SESSION ['partner_id'] = $this->partner_id;
		}
		$arr = $this->profil_liste_arr ();
		if (is_array ( $arr )) {
			$anz = count ( $arr );
			echo "<table>";
			echo "<tr><th>NR</th><th>PROFIL</th><th>OBJEKT</th><th>GELDKONTO</th><th>HV LOGO</th><th>OPTIONEN</th></tr>";
			for($a = 0; $a < $anz; $a ++) {
				$text = $arr [$a] ['KURZ_B'];
				$profil_id = $arr [$a] ['ID'];
				$objekt_id = $arr [$a] ['OBJEKT_ID'];
				$gk_id = $arr [$a] ['GK_ID'];
				$gk_info = new geldkonto_info ();
				$gk_info->geld_konto_details ( $gk_id );
				$partner_id = $arr [$a] ['PARTNER_ID'];
				$pp = new partner ();
				$partner_name = $pp->get_partner_name ( $partner_id );
				$oo = new objekt ();
				$objekt_name = $oo->get_objekt_name ( $objekt_id );
				$link_profil_wahl = "<a href=\"?daten=listen&option=profil_wahl&profil_id=$profil_id\">$text</a>";
				$link_profil_edit = "<a href=\"?daten=listen&option=profil_edit&profil_id=$profil_id\">Konten �ndern</a>";
				$link_bericht = "<a href=\"?daten=listen&option=pruefung_bericht&profil_id=$profil_id\">Bericht erstellen</a>";
				if (isset ( $_SESSION ['r_profil_id'] ) && $_SESSION ['r_profil_id'] == $profil_id) {
					echo "<tr class=\"zeile2\"><td>$profil_id</td><td>$link_profil_wahl</td><td>$objekt_name</td><td>$gk_info->geldkonto_bezeichnung_kurz</td><td>$partner_name</td><td>$link_profil_edit $link_bericht</td></tr>";
				} else {
					echo "<tr><td>$profil_id</td><td>$link_profil_wahl</td><td>$objekt_name</td><td>$gk_info->geldkonto_bezeichnung_kurz</td><td>$partner_name</td><td></td></tr>";
				}
			}
			echo "</table>";
		} else {
			die ( 'Keine Profile vorhanden!!!' );
		}
	}
	function get_r_profil_infos($profil_id) {
		$db_abfrage = "SELECT * FROM REPORT_PROFILE WHERE ID='$profil_id' && AKTUELL='1' ORDER BY DAT DESC LIMIT 0,1";
		$result = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			$row = mysql_fetch_assoc ( $result );
			$this->kurz_b = $row ['KURZ_B'];
			$this->objekt_id = $row ['OBJEKT_ID'];
			$this->gk_id = $row ['GK_ID'];
			$this->partner_id = $row ['PARTNER_ID'];
		} else {
			fehlermeldung_ausgeben ( "Profilinfos f�r Profil $profil_id unbekannt!" );
		}
	}
	function b_konten_edit($profil_id, $arr, $bez_arr) {
		// echo '<pre>';
		// print_r($bez_arr);
		// print_r($arr);
		// die();
		$this->del_konten ( $profil_id );
		/*
		 * $anz = count($arr);
		 * for($a=0;$a<$anz;$a++){
		 * $kto = $arr[$a];
		 * $bez_de = $bez_arr[$a];
		 * $bez_en = $bez_arr[$a];
		 * $db_abfrage = "INSERT INTO REPORT_PROFILE_K VALUES (NULL, '$profil_id', '$kto', '$bez_de', '$bez_en')";
		 * $result = mysql_query($db_abfrage) or
		 * die(mysql_error());
		 * }
		 */
		foreach ( $arr as $key => $konto ) {
			// echo "$key $konto<br>";
			$bez_de = $bez_arr [$key];
			$bez_en = $bez_arr [$key];
			$db_abfrage = "INSERT INTO REPORT_PROFILE_K VALUES (NULL, '$profil_id', '$konto', '$bez_de', '$bez_en')";
			$result = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		}
		// die();
	}
	function del_konten($profil_id) {
		$b_arr = $this->profil_liste_konten_arr ( $profil_id );
		if (is_array ( $b_arr )) {
			$db_abfrage = "DELETE FROM REPORT_PROFILE_K WHERE PROFIL_ID='$profil_id'";
			$result = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		}
	}
	function pruefung_bericht($profil_id, $monat = null) {
		if ($monat == null) {
			$monat = date ( "m" );
		}
		$jahr = date ( "Y" );
		
		$this->get_r_profil_infos ( $profil_id );
		$email_err = $this->pruefen_emails ( $this->objekt_id );
		if (is_array ( $email_err )) {
			echo "<pre>";
			print_r ( $email_err );
			$anz_e = count ( $email_err );
			fehlermeldung_ausgeben ( "FOlgende Eigent�mer haben keine Emailadresse!!!" );
			echo "<table>";
			for($e = 0; $e < $anz_e; $e ++) {
				$weg = new weg ();
				$e_id = $arr [$e] ['ET_ID'];
				$weg->get_eigentumer_id_infos3 ( $e_id );
				echo "<tr><td>$weg->einheit_kurzname</td><td>$weg->empf_namen_u</td></tr>";
			}
			echo "</table>";
			die ();
		} else {
			fehlermeldung_ausgeben ( "Keine Email fehler!" );
			$bk_konten_arr = $this->bk_konten_arr ( $profil_id );
			if (! is_array ( $bk_konten_arr )) {
				fehlermeldung_ausgeben ( "Keine Kostenkonten gew�hlt!!!" );
			} else {
				// print_r($bk_konten_arr);
				$anz_k = count ( $bk_konten_arr );
				$f = new formular ();
				$f->erstelle_formular ( "Bericht erstellen", null );
				$this->get_r_profil_infos ( $profil_id );
				echo "<hr>$this->kurz_b<hr>";
				echo "<table>";
				for($a = 0; $a < $anz_k; $a ++) {
					$kto = $bk_konten_arr [$a] ['KONTO'];
					$bez_de = $bk_konten_arr [$a] ['BEZ_DE'];
					$bez_en = $bk_konten_arr [$a] ['BEZ_EN'];
					$this->get_last_zeitraum ( $profil_id, $kto );
					
					if (! isset ( $this->report_bis )) {
						$this->report_von_neu = "$jahr-$monat-01";
						$lt = letzter_tag_im_monat ( $monat, $jahr );
						$this->report_bis_neu = "$jahr-$monat-$lt";
					} else {
						$this->report_von_neu = tage_plus ( $this->report_bis, 1 );
						$von_n_arr = explode ( '-', $this->report_von );
						$von_m_neu = $von_n_arr [1];
						$lt_neu = letzter_tag_im_monat ( $monat, $jahr );
						$this->report_bis_neu = "$jahr-$monat-$lt_neu";
					}
					
					$this->report_von_neu_d = date_mysql2german ( $this->report_von_neu );
					$this->report_bis_neu_d = date_mysql2german ( $this->report_bis_neu );
					
					echo "<tr><td>$kto</td><td>$bez_de</td><td>$bez_en</td><td>";
					echo "ALT: $this->report_von<br>NEU:$this->report_von_neu<br>";
					$f->datum_feld ( 'VON', 'bericht_von[]', $this->report_von_neu_d, 'von' );
					echo "</td><td>";
					echo "ALT: $this->report_bis<br>NEU:$this->report_bis_neu<br>";
					
					$f->datum_feld ( 'BIS', 'bericht_bis[]', $this->report_bis_neu_d, 'bis' );
					echo "</td></tr>";
					$f->hidden_feld ( 'bk_konten[]', $kto );
				}
				echo "</table>";
				// print_r($this);
				$f->hidden_feld ( 'monat', $monat );
				$f->hidden_feld ( 'jahr', $jahr );
				$f->hidden_feld ( 'objekt_id', $this->objekt_id );
				$f->hidden_feld ( 'option', 'dyn_pdf' );
				$f->hidden_feld ( 'lang', 'en' );
				$this->dropdown_lang ( 'Sprache', 'lang', 'lng' );
				$f->hidden_feld ( 'profil_id', $profil_id );
				$f->send_button ( 'Bnt_Bericht', 'PDF-Anzeigen' );
				$f->ende_formular ();
			}
		}
	}
	function dropdown_lang($label, $name, $id) {
		echo "<label for=\"$name\">$label</label>\n<select name=\"$name\" id=\"$id\" size=\"1\" >\n";
		echo "<option value=\"en\" >English</option>\n";
		echo "<option value=\"de\" >Deutsch</option>\n";
		echo "</select>\n";
	}
	function get_last_zeitraum($profil_id, $konto) {
		if (isset ( $this->report_von )) {
			unset ( $this->report_von );
		}
		if (isset ( $this->report_bis )) {
			unset ( $this->report_bis );
		}
		
		$db_abfrage = "SELECT * FROM REPORT_ZEITRAUM WHERE PROFIL_ID='$profil_id' && KONTO='$konto' ORDER BY DAT DESC LIMIT 0,1";
		$result = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		$row = mysql_fetch_assoc ( $result );
		$this->report_von = $row ['VON'];
		$this->report_bis = $row ['BIS'];
	}
	function pruefen_emails($objekt_id) {
		// echo "PR�FE EMAILS!!!";
		// echo $objekt_id;
		$weg = new weg ();
		$ein_arr = $weg->einheiten_weg_tabelle_arr ( $objekt_id );
		if (! is_array ( $ein_arr )) {
			fehlermeldung_ausgeben ( "Keine Einheiten im Objekt" );
		} else {
			$anz_e = count ( $ein_arr );
			
			$et_arr;
			for($a = 0; $a < $anz_e; $a ++) {
				$einheit_id = $ein_arr [$a] ['EINHEIT_ID'];
				$weg1 = new weg ();
				$weg1->get_last_eigentuemer ( $einheit_id );
				// print_r($weg1);
				if (isset ( $weg1->eigentuemer_id )) {
					$error = 0;
					$anz_p = count ( $weg1->eigentuemer_name );
					for($g = 0; $g < $anz_p; $g ++) {
						$person_id = $weg1->eigentuemer_name [$g] ['person_id'];
						
						$dd = new detail ();
						$email = $dd->finde_detail_inhalt ( 'PERSON', $person_id, 'Email' );
						if (! $email) {
							$error ++;
						} else {
							$error --;
						}
					}
					if ($error >= $anz_p) {
						$et_arr [$a] ['ET_ID'] = $weg1->eigentuemer_id;
					}
					unset ( $weg1->eigentuemer_id );
				}
			}
		}
		if (isset ( $et_arr ) && is_array ( $et_arr )) {
			return $et_arr;
		}
	}
	function dyn_pdf($profil_id, $objekt_id, $monat, $jahr, $bericht_von_arr, $bericht_bis_arr, $b_konten_arr, $lang = 'de') {
		$this->get_r_profil_infos ( $profil_id );
		$gk_id = $this->gk_id;
		
		/* Eingrenzung Kostenabragen */
		if (! isset ( $_REQUEST ['von'] ) or ! isset ( $_REQUEST ['bis'] )) {
			// die('Abfragedatum VON BIS in die URL hinzuf�gen');
			$von = "01.$monat.$jahr";
			$lt = letzter_tag_im_monat ( $monat, $jahr );
			$bis = "$lt.$monat.$jahr";
		}
		$von = date_german2mysql ( $von );
		$bis = date_german2mysql ( $bis );
		
		$monat_name = monat2name ( $monat );
		$pdf = new Cezpdf ( 'a4', 'portrait' );
		$bpdf = new b_pdf ();
		$bpdf->b_header ( $pdf, 'Partner', $this->partner_id, 'portrait', 'pdfclass/fonts/Helvetica.afm', 6 );
		$pdf->ezStopPageNumbers (); // seitennummerirung beenden
		                           // $gk = new geldkonto_info();
		                           // $gk->geld_konto_ermitteln('OBJEKT', $objekt_id);
		                           // echo '<pre>';
		                           // print_r($gk);
		                           // if(!$gk->#geldkonto_id){
		                           // die('Geldkonto zum Objekt hinzuf�gen!!!');
		                           // }
		
		/* Schleife f�r jede Einheit */
		$weg = new weg ();
		$ein_arr = $weg->einheiten_weg_tabelle_arr ( $objekt_id );
		$anz_e = count ( $ein_arr );
		for($e = 0; $e < $anz_e; $e ++) {
			$weg = new weg ();
			$einheit_id = $ein_arr [$e] ['EINHEIT_ID'];
			$weg->get_last_eigentuemer ( $einheit_id );
			
			if (isset ( $weg->eigentuemer_id )) {
				$ein_arr [$e] ['ET_ID'] = $weg->eigentuemer_id;
				$weg->get_eigentumer_id_infos3 ( $weg->eigentuemer_id );
				$ein_arr [$e] ['ET_NAMEN'] = $weg->empf_namen_u;
			} else {
			}
			if (isset ( $weg->versprochene_miete )) {
				$ein_arr [$e] ['V_MIETE'] = $weg->versprochene_miete;
			} else {
				$ein_arr [$e] ['V_MIETE'] = '0.00';
			}
			$ein_arr [$e] ['WEG-QM'] = $weg->einheit_qm_weg;
			
			/* Mieter */
			$ee = new einheit ();
			$mv_id = $ee->get_mietvertrag_id ( $einheit_id );
			if ($mv_id) {
				$mvs = new mietvertraege ();
				$mvs->get_mietvertrag_infos_aktuell ( $mv_id );
				$kontaktdaten = $ee->kontaktdaten_mieter ( $mv_id );
				// $my_arr[$z]['MIETER'] = $mvs->personen_name_string_u."\n".$kontaktdaten;
				$ein_arr [$e] ['MIETER'] = $mvs->personen_name_string_u;
				$ein_arr [$e] ['MIETVERTRAG_ID'] = $mv_id;
				$mk = new mietkonto ();
				$mk->kaltmiete_monatlich ( $mv_id, $monat, $jahr );
				$ein_arr [$e] ['KALTMIETE'] = $mk->ausgangs_kaltmiete;
				
				$ein_arr [$e] ['KONTAKT'] = $kontaktdaten;
				$ein_arr [$e] ['EINHEIT_ID'] = $einheit_id;
				$mz = new miete ();
				// $mz->mietkonto_berechnung($mv_id);
				$mz->mietkonto_berechnung_monatsgenau ( $mv_id, $jahr, $monat );
				$ein_arr [$e] ['MIETER_SALDO'] = $mz->erg;
			} else {
				$ein_arr [$e] ['MIETER'] = 'Leerstand';
			}
			/* Differenz Kaltmiete und Versprochene */
			if ($ein_arr [$e] ['V_MIETE'] != '0.00') {
				$ein_arr [$e] ['DIFF_KW'] = $ein_arr [$e] ['KALTMIETE'] - $ein_arr [$e] ['V_MIETE'];
			} else {
				$ein_arr [$e] ['DIFF_KW'] = '0.00';
			}
			
			foreach ( $b_konten_arr as $b_key => $b_konto ) {
				$this->get_b_konto_bez ( $profil_id, $b_konto );
				
				if ($lang == 'de') {
					$txt = $this->kto_bez_de;
				}
				if ($lang == 'en') {
					$txt = $this->kto_bez_en;
				}
				
				$buchung_von_d = $bericht_von_arr [$b_key];
				$buchung_von = date_german2mysql ( $buchung_von_d );
				$buchung_bis_d = $bericht_bis_arr [$b_key];
				$buchung_bis = date_german2mysql ( $buchung_bis_d );
				
				// echo "$b_key $b_konto $txt $buchung_von $buchung_bis<br>";
				// $ein_arr[$e][$b_konto]
				// $ein_arr[$e][$b_konto]['EINHEIT'][] = $this->get_kosten_arr('Einheit', $einheit_id, $buchung_von, $buchung_bis, $gk_id,$b_konto);
				$ein_arr [$e] [$b_konto] ['EINHEIT'] = $this->get_kosten_von_bis_o_sum ( 'Einheit', $einheit_id, $buchung_von, $buchung_bis, $gk_id, $b_konto );
				$ein_arr [$e] [$b_konto] ['ET'] = $this->get_kosten_von_bis_o_sum ( 'Eigentuemer', $weg->eigentuemer_id, $buchung_von, $buchung_bis, $gk_id, $b_konto );
				// $ein_arr[$e][$b_konto]['MIETER']= $this->get_kosten_von_bis_o_sum('MIETVERTRAG',$mv_id, $buchung_von, $buchung_bis, $gk_id,$b_konto);
				
				if (is_array ( $ein_arr [$e] [$b_konto] ['EINHEIT'] ) && is_array ( $ein_arr [$e] [$b_konto] ['ET'] )) {
					$ein_arr [$e] ['KONTEN'] [$b_konto] = array_merge ( $ein_arr [$e] [$b_konto] ['EINHEIT'], $ein_arr [$e] [$b_konto] ['ET'] );
				}
				
				if (is_array ( $ein_arr [$e] [$b_konto] ['EINHEIT'] ) && ! is_array ( $ein_arr [$e] [$b_konto] ['ET'] )) {
					$ein_arr [$e] ['KONTEN'] [$b_konto] = $ein_arr [$e] [$b_konto] ['EINHEIT'];
				}
				if (! is_array ( $ein_arr [$e] [$b_konto] ['EINHEIT'] ) && is_array ( $ein_arr [$e] [$b_konto] ['ET'] )) {
					$ein_arr [$e] ['KONTEN'] [$b_konto] = $ein_arr [$e] [$b_konto] ['ET'];
				}
				
				$ein_arr [$e] ['KONTEN_VB'] [$b_konto] ['VON'] = $buchung_von_d;
				$ein_arr [$e] ['KONTEN_VB'] [$b_konto] ['BIS'] = $buchung_bis_d;
				
				unset ( $ein_arr [$e] [$b_konto] );
			} // END FOR BUCHUNGSKONTEN
			/* Kopf */
			$pdf->ezText ( $ein_arr [$e] ['EINHEIT_KURZNAME'], 11 );
			$pdf->ezText ( $ein_arr [$e] ['HAUS_STRASSE'] . ' ' . $ein_arr [$e] ['HAUS_NUMMER'] . ' ' . $ein_arr [$e] ['HAUS_PLZ'] . ' ' . $ein_arr [$e] ['HAUS_STADT'], 11 );
			$pdf->ezText ( $ein_arr [$e] ['ET_NAMEN'], 11 );
			
			if (isset ( $ein_arr [$e] ['KONTEN'] )) {
				foreach ( $ein_arr [$e] ['KONTEN'] as $b_key => $b_konto ) {
					// $pdf->ezTable($ein_arr[$e]['KONTEN'][$b_key]);
					$this->get_b_konto_bez ( $profil_id, $b_key );
					
					/* Tabellen f�r Konten */
					// $tmp_b_arr = $ein_arr[$e]['KONTEN'][$b_key];
					$tmp_b_arr = $this->summieren_arr ( $ein_arr [$e] ['KONTEN'] [$b_key] );
					$anz_tmp = count ( $tmp_b_arr );
					if ($lang == 'en') {
						$cols = array (
								'DATUM' => "<b>Date</b>",
								'VERWENDUNGSZWECK' => "<b>Description</b>",
								'BETRAG' => "<b>Amount [�]</b>" 
						);
						$b_von = date_german2mysql ( $ein_arr [$e] ['KONTEN_VB'] [$b_key] ['VON'] );
						$b_bis = date_german2mysql ( $ein_arr [$e] ['KONTEN_VB'] [$b_key] ['BIS'] );
						$titel = $this->kto_bez_en;
						$tab_ue = "<b>[cost account: $b_key] $titel Period:$b_von $b_bis</b>";
						$tmp_b_arr [$anz_tmp - 1] ['VERWENDUNGSZWECK'] = "<b>SUM</b>";
					}
					
					if ($lang == 'de') {
						$cols = array (
								'DATUM' => "<b>Datum</b>",
								'VERWENDUNGSZWECK' => "<b>Beschreibung</b>",
								'BETRAG' => "<b>Betrag [�]</b>" 
						);
						$b_von = $ein_arr [$e] ['KONTEN_VB'] [$b_key] ['VON'];
						$b_bis = $ein_arr [$e] ['KONTEN_VB'] [$b_key] ['BIS'];
						$titel = $this->kto_bez_de;
						$tab_ue = "<b>[Konto: $b_key] $titel Zeitraum: $b_von  $b_bis</b>";
						$tmp_b_arr [$anz_tmp - 1] ['VERWENDUNGSZWECK'] = "<b>SUMME</b>";
					}
					$pdf->ezTable ( $tmp_b_arr, $cols, "$tab_ue", array (
							'showHeadings' => 1,
							'shaded' => 1,
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
									) 
							) 
					) );
					$pdf->ezSetDy ( - 5 ); // abstand
				} // end foreach
			} // Ende Konten
			  
			// $pdf->ezText($ein_arr[$e],11);
			$pdf->ezNewPage ();
		} // END FOR EINHEITEN
		  // print_r($ein_arr);
		  // die();
		
		ob_clean ();
		$pdf->ezStream ();
	}
	function summieren_arr($arr) {
		// print_r($arr);
		if (is_array ( $arr )) {
			$anz = count ( $arr );
			$sum = 0;
			for($a = 0; $a < $anz; $a ++) {
				$sum += $arr [$a] ['BETRAG'];
			}
			// $arr[$anz]['VERWENDUNGSZWECK'] = 'SUM';
			$arr [$anz] ['BETRAG'] = $sum;
			return $arr;
		}
		// print_r($arr);
		// die();
	}
	function get_b_konto_bez($profil_id, $konto) {
		if (isset ( $this->kto_bez_de )) {
			unset ( $this->kto_bez_de );
			unset ( $this->kto_bez_en );
		}
		$db_abfrage = "SELECT * FROM REPORT_PROFILE_K WHERE PROFIL_ID='$profil_id' && KONTO='$konto' ORDER BY DAT DESC LIMIT 0,1";
		// echo $db_abfrage;
		$result = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		$row = mysql_fetch_assoc ( $result );
		$this->kto_bez_de = $row ['BEZ_DE'];
		$this->kto_bez_en = $row ['BEZ_EN'];
	}
	function auszugtest($einheit_id, $et_id, $vor_saldo_et, $jahr = null) {
		$e = new einheit ();
		$e->get_einheit_info ( $einheit_id );
		
		if ($jahr == null) {
			$jahr = date ( "Y" );
		}
		// echo "$jahr anfangsaldo:$vor_saldo_et";
		
		/* Alle M;VS ARR */
		$mv = new mietvertrag ();
		$mv_arr = $mv->get_mietvertraege_bis ( $einheit_id, $jahr, 12 );
		echo '<pre>';
		// print_r($mv_arr);
		$anz_mv = count ( $mv_arr );
		echo "<table>";
		echo "<tr><th>$e->einheit_kurzname  JAHR:$jahr</th><th> $vor_saldo_et</th></tr>";
		/* MONATSSCHLEIFE */
		$saldo_et = $vor_saldo_et;
		$sum_auszahlen = 0;
		$akt_jahr = date ( "Y" );
		$akt_monat = sprintf ( '%02d', date ( "m" ) );
		if ($jahr == $akt_jahr) {
			$end_monat = $akt_monat;
		} else {
			$end_monat = 12;
		}
		for($m = 1; $m <= $end_monat; $m ++) {
			$monat = sprintf ( '%02d', $m );
			
			/* KOSTEn */
			$gk = new geldkonto_info ();
			$gk->geld_konto_ermitteln ( 'Objekt', $e->objekt_id );
			$von = "$jahr-$monat-01";
			$ltm = letzter_tag_im_monat ( $monat, $jahr );
			$bis = "$jahr-$monat-$ltm";
			if (isset ( $my_arr )) {
				unset ( $my_arr );
			}
			$my_arr [] = $this->get_kosten_von_bis ( 'Einheit', $einheit_id, $von, $bis, $gk->geldkonto_id );
			
			if (isset ( $my_arr1 )) {
				unset ( $my_arr1 );
			}
			$my_arr1 [] = $this->get_kosten_von_bis ( 'Eigentuemer', $et_id, $von, $bis, $gk->geldkonto_id );
			
			if (isset ( $my_arr2 )) {
				unset ( $my_arr2 );
			}
			$weg = new weg ();
			$my_arr2 [] = $weg->get_monatliche_def ( $monat, $jahr, 'Einheit', $einheit_id );
			
			// print_r($my_arr1);
			
			$monatname = monat2name ( $monat, 'en' );
			echo "<table><tr><th>$monatname $jahr</th></tr><tr><td>";
			
			/* MV SCHLEIFE */
			echo "<table>";
			echo "<tr><th>MIETER</th><th>SOLL WM</th><th>GEZAHLT</th><th>SALDO</th><th>KALTMIETE</th><th>NK</th><th>BETRAG</th></tr>";
			for($a = 0; $a < $anz_mv; $a ++) {
				$mv_id = $mv_arr [$a] ['MIETVERTRAG_ID'];
				$mv = new mietvertraege ();
				$mv->get_mietvertrag_infos_aktuell ( $mv_id );
				$mk = new mietkonto ();
				$mk->kaltmiete_monatlich ( $mv_id, $monat, $jahr );
				
				$mz = new miete ();
				$m_arr = $mz->get_monats_ergebnis ( $mv_id, $monat, $jahr );
				// print_r($m_arr);
				$m_erg = ( object ) $m_arr;
				if (isset ( $m_erg->zb ) or isset ( $m_erg->erg )) {
					$m_erg->soll_arr = explode ( '|', $m_erg->soll );
					if (isset ( $m_erg->soll_arr [1] )) {
						$m_erg->soll_wm = $m_erg->soll_arr [0];
						$m_erg->soll_mwst = $m_erg->soll_arr [1];
					} else {
						$m_erg->soll_wm = $m_erg->soll;
						$m_erg->soll_mwst = 0;
					}
					$nk = ($m_erg->soll_wm * - 1) - $mk->ausgangs_kaltmiete;
					/* Wenn Mieter gezahlt hat */
					if ($m_erg->zb > 0) {
						$auszahlen = $m_erg->zb - $nk;
					} else {
						$auszahlen = 0.00;
					}
					$sum_auszahlen += $auszahlen;
					// $ausgezahlt = 0.00;
					// $saldo_et +=$auszahlen+$ausgezahlt;
					
					// echo "<tr><td>$mv_id $mv->personen_name_string_u</td><td>$m_erg->soll_wm</td><td>$m_erg->zb</td><td>$m_erg->erg</td><td>$mk->ausgangs_kaltmiete</td><td>$nk</td><td>$auszahlen</td><td>$ausgezahlt</td><td>$saldo_et</td></tr>";
					echo "<tr><td>$mv_id $mv->personen_name_string_u</td><td>$m_erg->soll_wm</td><td>$m_erg->zb</td><td>$m_erg->erg</td><td>$mk->ausgangs_kaltmiete</td><td>$nk</td><td>$auszahlen</td></tr>";
				}
			} // end MV SCHLEIFE
			$saldo_et += $auszahlen;
			
			echo "<tr><th colspan=\"9\"></th></tr>";
			echo "<tr><td colspan=\"6\">SUMME EINNAHMEN</td><td>$auszahlen</td></tr>";
			
			// print_r($my_arr2);
			/* Hausgelddefinitionen */
			echo "<tr><th colspan=\"6\">HAUSGELD</th><th></th></tr>";
			$anz_buchungen = count ( $my_arr2 [0] );
			for($k = 0; $k < $anz_buchungen; $k ++) {
				$txt = $my_arr2 [0] [$k] ['KOSTENKAT'];
				$betrag = $my_arr2 [0] [$k] ['SUMME'] * - 1;
				// $auszahlen = $sum_auszahlen+$betrag;
				$saldo_et += $betrag;
				echo "<tr><td colspan=\"6\">$txt</td><td>$betrag</td></tr>";
			}
			
			/* Buchungen zu Einheit */
			echo "<tr><th colspan=\"6\">WOHNUNGSKOSTEN</th><th></th></tr>";
			$anz_buchungen = count ( $my_arr [0] );
			for($k = 0; $k < $anz_buchungen - 1; $k ++) {
				$txt = $my_arr [0] [$k] ['VERWENDUNGSZWECK'];
				$betrag = $my_arr [0] [$k] ['BETRAG'];
				$saldo_et += $betrag;
				echo "<tr><td colspan=\"6\">$txt</td><td>$betrag</td></tr>";
			}
			
			/* Buchungen zu ET */
			echo "<tr><th colspan=\"6\">EIGENT�MER</th><th></th></tr>";
			$anz_buchungen = count ( $my_arr1 [0] );
			for($k = 0; $k < $anz_buchungen - 1; $k ++) {
				$txt = $my_arr1 [0] [$k] ['VERWENDUNGSZWECK'];
				$betrag = $my_arr1 [0] [$k] ['BETRAG'];
				$saldo_et += $betrag;
				echo "<tr><td colspan=\"6\">$txt</td><td>$betrag</td></tr>";
			}
			echo "<tr><th colspan=\"6\">SALDO NEU</th><th>$saldo_et</th></tr>";
			
			echo "</table>";
			
			echo "</td></tr></table>";
		}
		echo "</table>";
		return $saldo_et;
	}
	function auszugtest1($et_id, $von = null, $bis = null, $saldo_et = '0.00') {
		$this->saldo_et = $saldo_et;
		$weg = new weg ();
		$einheit_id = $weg->get_einheit_id_from_eigentuemer ( $et_id );
		$e = new einheit ();
		$e->get_einheit_info ( $einheit_id );
		
		$gk = new geldkonto_info ();
		$gk->geld_konto_ermitteln ( 'Objekt', $e->objekt_id );
		// $gk->geldkonto_id
		// $this->eigentuemer_von = $weg->eigentuemer_von;
		// $this->eigentuemer_bis = $weg->eigentuemer_bis;
		
		/* ET DATEN */
		if ($weg->eigentuemer_bis == '0000-00-00') {
			$weg->eigentuemer_bis = date ( "Y-m-d" );
		}
		
		if ($von == null) {
			$von = $weg->eigentuemer_von;
		}
		if ($bis == null) {
			$bis = $weg->eigentuemer_bis;
		}
		
		/* MIETVERTRAEGE ZEITRAUM ET */
		$mv_arr = $this->get_mv_et_zeitraum_arr ( $einheit_id, $von, $bis );
		$anz_mv = count ( $mv_arr );
		if (is_array ( $mv_arr )) {
			// echo '<pre>';
			// print_r($mv_arr);
		} else {
			echo "NO MV - NUR KOSTEN";
		}
		
		$zeit_arr = $this->monats_array ( $von, $bis );
		// print_r($zeit_arr);
		
		/* Durchlauf alle Monate */
		if (is_array ( $zeit_arr )) {
			$anz_m = count ( $zeit_arr );
			for($m = 0; $m < $anz_m; $m ++) {
				/* Saldo Vormonat */
				$this->saldo_et_vm = $this->saldo_et;
				$zeit_arr [$m] ['SALDO_VM'] = $this->saldo_et_vm;
				
				$monat = $zeit_arr [$m] ['MONAT'];
				$jahr = $zeit_arr [$m] ['JAHR'];
				
				$m_von = "$jahr-$monat-01";
				$ltm = letzter_tag_im_monat ( $monat, $jahr );
				$m_bis = "$jahr-$monat-$ltm";
				
				/* Mieteinnahmen */
				for($a = 0; $a < $anz_mv; $a ++) {
					$mv_id = $mv_arr [$a] ['MIETVERTRAG_ID'];
					$mv = new mietvertraege ();
					$mv->get_mietvertrag_infos_aktuell ( $mv_id );
					$mk = new mietkonto ();
					$mk->kaltmiete_monatlich ( $mv_id, $monat, $jahr );
					
					$mz = new miete ();
					$m_arr = $mz->get_monats_ergebnis ( $mv_id, $monat, $jahr );
					// print_r($m_arr);
					
					$m_soll_arr = explode ( '|', $m_arr ['soll'] );
					if (isset ( $m_soll_arr [1] )) {
						$m_arr ['soll_wm'] = $m_soll_arr [0];
						$m_arr ['soll_mwst'] = $m_soll_arr [1];
					} else {
						$m_arr ['soll_wm'] = $m_arr ['soll'];
						$m_arr ['soll_mwst'] = '0.00';
					}
					$nk = ($m_arr ['soll_wm'] * - 1) - $mk->ausgangs_kaltmiete;
					$zeit_arr [$m] ['KM_SOLL'] [$a] ['MV_ID'] = $mv_id;
					$zeit_arr [$m] ['KM_SOLL'] [$a] ['M_NAME'] = $mv->personen_name_string;
					$zeit_arr [$m] ['KM_SOLL'] [$a] ['BETRAG'] = $mk->ausgangs_kaltmiete;
					$zeit_arr [$m] ['NK_SOLL'] [$a] ['BETRAG'] = $nk;
					$zeit_arr [$m] ['WM_SOLL'] [$a] ['BETRAG'] = $m_arr ['soll_wm'] * - 1;
					
					/* Wenn Mieter gezahlt hat */
					if (isset ( $m_arr ['zb'] ) && $m_arr ['zb'] > 0) {
						$auszahlen = $m_arr ['zb'] - $nk;
						$zeit_arr [$m] ['EINNAHMEN'] [$a] ['MV_ID'] = $mv_id;
						$zeit_arr [$m] ['EINNAHMEN'] [$a] ['TXT'] = "$mv->personen_name_string_u " . " (ZB:" . $m_arr ['zb'] . ")";
						$zeit_arr [$m] ['EINNAHMEN'] [$a] ['BETRAG'] = $auszahlen;
					} else {
						$auszahlen = '0.00';
					}
					$zeit_arr [$m] ['AUSZAHLEN'] = $auszahlen;
					
					$this->saldo_et += $auszahlen;
					$zeit_arr [$m] ['SALDO_MONAT'] = $this->saldo_et;
				}
				
				/* Hausgeld Fixkosten */
				$weg = new weg ();
				$kosten_arr = $weg->get_monatliche_def ( $monat, $jahr, 'Einheit', $einheit_id );
				$anz_buchungen = count ( $kosten_arr );
				for($k = 0; $k < $anz_buchungen; $k ++) {
					$txt = $kosten_arr [$k] ['KOSTENKAT'];
					$betrag = $kosten_arr [$k] ['SUMME'] * - 1;
					// $auszahlen = $sum_auszahlen+$betrag;
					// $saldo_et += $betrag;
					// echo "$txt $betrag<br>";
					$zeit_arr [$m] ['HAUSGELD'] [$txt] = $betrag;
					
					$this->saldo_et += $betrag;
					
					$zeit_arr [$m] ['SALDO_MONAT'] = $this->saldo_et;
				}
				
				/* Buchungen zu Einheit */
				$kosten_arr = $this->get_kosten_von_bis ( 'Einheit', $einheit_id, $m_von, $m_bis, $gk->geldkonto_id );
				// print_r($kosten_arr);
				if (is_array ( $kosten_arr )) {
					$anz_buchungen = count ( $kosten_arr );
					for($k = 0; $k < $anz_buchungen - 1; $k ++) {
						$datum = $kosten_arr [$k] ['DATUM'];
						$txt = bereinige_string ( $kosten_arr [$k] ['VERWENDUNGSZWECK'] );
						$betrag = $kosten_arr [$k] ['BETRAG'];
						
						$zeit_arr [$m] ['EINHEIT'] [$k] ['DATUM'] = $datum;
						$zeit_arr [$m] ['EINHEIT'] [$k] ['TXT'] = $txt;
						$zeit_arr [$m] ['EINHEIT'] [$k] ['BETRAG'] = $betrag;
						
						$this->saldo_et += $betrag;
						$zeit_arr [$m] ['SALDO_MONAT'] = $this->saldo_et;
					}
				}
				/* Buchungen zum ET */
				$kosten_arr = $this->get_kosten_von_bis ( 'Eigentuemer', $et_id, $m_von, $m_bis, $gk->geldkonto_id );
				if (is_array ( $kosten_arr )) {
					$anz_buchungen = count ( $kosten_arr );
					for($k = 0; $k < $anz_buchungen - 1; $k ++) {
						$datum = $kosten_arr [$k] ['DATUM'];
						$txt = bereinige_string ( $kosten_arr [$k] ['VERWENDUNGSZWECK'] );
						$betrag = $kosten_arr [$k] ['BETRAG'];
						
						$zeit_arr [$m] ['ET'] [$k] ['DATUM'] = $datum;
						$zeit_arr [$m] ['ET'] [$k] ['TXT'] = $txt;
						$zeit_arr [$m] ['ET'] [$k] ['BETRAG'] = $betrag;
						
						$this->saldo_et += $betrag;
						$zeit_arr [$m] ['SALDO_MONAT'] = $this->saldo_et;
					}
				}
				
				/*
				 * if($this->check_vg($gk->geldkonto_id, $monat, $jahr, $et_id,'-14.99', null, null)=='0'){
				 * $zeit_arr[$m]['ET'][$k]['DATUM'] ="$jahr-$monat-01";
				 * $zeit_arr[$m]['ET'][$k]['TXT'] = 'Verwaltergeb�hr SR';
				 * $zeit_arr[$m]['ET'][$k]['BETRAG'] = '-14.99';
				 * $this->saldo_et+=-14.99;
				 * $zeit_arr[$m]['SALDO_MONAT'] = $this->saldo_et;
				 * }else{
				 *
				 * }
				 */
			}
		} else {
			die ( "Zeitraum falsch $von $bis" );
		}
		
		return $zeit_arr;
		/*
		 * $this->saldo_et_vm
		 * $this->saldo_et
		 */
	}
	function auszugtest2($et_id, $von = null, $bis = null, $saldo_et = '0.00') {
		$this->saldo_et = $saldo_et;
		$weg = new weg ();
		$einheit_id = $weg->get_einheit_id_from_eigentuemer ( $et_id );
		$e = new einheit ();
		$e->get_einheit_info ( $einheit_id );
		
		$gk = new geldkonto_info ();
		$gk->geld_konto_ermitteln ( 'Objekt', $e->objekt_id );
		
		/* ET DATEN */
		if ($weg->eigentuemer_bis == '0000-00-00') {
			$weg->eigentuemer_bis = date ( "Y-m-d" );
		}
		
		if ($von == null) {
			$von = $weg->eigentuemer_von;
		}
		if ($bis == null) {
			$bis = $weg->eigentuemer_bis;
		}
		
		/* MIETVERTRAEGE ZEITRAUM ET */
		$mv_arr = $this->get_mv_et_zeitraum_arr ( $einheit_id, $von, $bis );
		$anz_mv = count ( $mv_arr );
		if (is_array ( $mv_arr )) {
			// echo '<pre>';
			// print_r($mv_arr);
		} else {
			echo "NO MV - NUR KOSTEN";
		}
		
		$zeit_arr = $this->monats_array ( $von, $bis );
		// print_r($zeit_arr);
		
		/* Durchlauf alle Monate */
		if (is_array ( $zeit_arr )) {
			$anz_m = count ( $zeit_arr );
			for($m = 0; $m < $anz_m; $m ++) {
				/* Saldo Vormonat */
				$this->saldo_et_vm = $this->saldo_et;
				$zeit_arr [$m] ['SALDO_VM'] = $this->saldo_et_vm;
				
				$monat = $zeit_arr [$m] ['MONAT'];
				$jahr = $zeit_arr [$m] ['JAHR'];
				
				$m_von = "$jahr-$monat-01";
				$ltm = letzter_tag_im_monat ( $monat, $jahr );
				$m_bis = "$jahr-$monat-$ltm";
				
				/* Mieteinnahmen */
				for($a = 0; $a < $anz_mv; $a ++) {
					$mv_id = $mv_arr [$a] ['MIETVERTRAG_ID'];
					$mv = new mietvertraege ();
					$mv->get_mietvertrag_infos_aktuell ( $mv_id );
					$mk = new mietkonto ();
					// $mk->kaltmiete_monatlich($mv_id,$monat,$jahr);
					$mk->kaltmiete_monatlich_ink_vz ( $mv_id, $monat, $jahr );
					
					$mz = new miete ();
					$m_arr = $mz->get_monats_ergebnis ( $mv_id, $monat, $jahr );
					$zeit_arr [$m] ['M_ERG'] = $mz->erg;
					// print_r($m_arr);
					// die();
					
					$m_soll_arr = explode ( '|', $m_arr ['soll'] );
					if (isset ( $m_soll_arr [1] )) {
						$m_arr ['soll_wm'] = $m_soll_arr [0];
						$m_arr ['soll_mwst'] = $m_soll_arr [1];
					} else {
						$m_arr ['soll_wm'] = $m_arr ['soll'];
						$m_arr ['soll_mwst'] = '0.00';
					}
					$nk = ($m_arr ['soll_wm'] * - 1) - $mk->ausgangs_kaltmiete;
					// $zeit_arr[$m]['M_ERG'][$a]['M_ERG'] = $mz->erg;
					$zeit_arr [$m] ['KM_SOLL'] [$a] ['MV_ID'] = $mv_id;
					$zeit_arr [$m] ['KM_SOLL'] [$a] ['M_NAME'] = $mv->personen_name_string;
					$zeit_arr [$m] ['KM_SOLL'] [$a] ['BETRAG'] = $mk->ausgangs_kaltmiete;
					$zeit_arr [$m] ['NK_SOLL'] [$a] ['BETRAG'] = $nk;
					$zeit_arr [$m] ['WM_SOLL'] [$a] ['BETRAG'] = $m_arr ['soll_wm'] * - 1;
					
					/* Wenn Mieter gezahlt hat */
					if (isset ( $m_arr ['zb'] ) && $m_arr ['zb'] > 0) {
						if ($mz->erg < 0) {
							$auszahlen = $m_arr ['zb'] + $mz->erg;
						} else {
							$auszahlen = $m_arr ['zb'] - $mz->erg;
						}
						
						// $auszahlen = $m_arr['zb'] - $nk;
						$zeit_arr [$m] ['EINNAHMEN'] [$a] ['MV_ID'] = $mv_id;
						$zeit_arr [$m] ['EINNAHMEN'] [$a] ['TXT'] = "$mv->personen_name_string_u " . "$mz->erg" . " (ZB:" . $m_arr ['zb'] . ")";
						$zeit_arr [$m] ['EINNAHMEN'] [$a] ['BETRAG'] = $auszahlen;
					} else {
						$auszahlen = '0.00';
					}
					$zeit_arr [$m] ['AUSZAHLEN'] = $auszahlen;
					
					$this->saldo_et += $auszahlen;
					$zeit_arr [$m] ['SALDO_MONAT'] = $this->saldo_et;
				}
				
				/* Hausgeld Fixkosten */
				$weg = new weg ();
				$kosten_arr = $weg->get_monatliche_def ( $monat, $jahr, 'Einheit', $einheit_id );
				$anz_buchungen = count ( $kosten_arr );
				for($k = 0; $k < $anz_buchungen; $k ++) {
					$txt = $kosten_arr [$k] ['KOSTENKAT'];
					$betrag = $kosten_arr [$k] ['SUMME'] * - 1;
					// $auszahlen = $sum_auszahlen+$betrag;
					// $saldo_et += $betrag;
					// echo "$txt $betrag<br>";
					$zeit_arr [$m] ['HAUSGELD'] [$txt] = $betragx;
					
					$this->saldo_et += $betragx;
					$zeit_arr [$m] ['SALDO_MONAT'] = $this->saldo_et;
				}
				
				/* Buchungen zu Einheit */
				$kosten_arr = $this->get_kosten_von_bis ( 'Einheit', $einheit_id, $m_von, $m_bis, $gk->geldkonto_id );
				// print_r($kosten_arr);
				if (is_array ( $kosten_arr )) {
					$anz_buchungen = count ( $kosten_arr );
					for($k = 0; $k < $anz_buchungen - 1; $k ++) {
						$datum = $kosten_arr [$k] ['DATUM'];
						$txt = bereinige_string ( $kosten_arr [$k] ['VERWENDUNGSZWECK'] );
						$betrag = $kosten_arr [$k] ['BETRAG'];
						
						$zeit_arr [$m] ['EINHEIT'] [$k] ['DATUM'] = $datum;
						$zeit_arr [$m] ['EINHEIT'] [$k] ['TXT'] = $txt;
						$zeit_arr [$m] ['EINHEIT'] [$k] ['BETRAG'] = $betrag;
						
						$this->saldo_et += $betrag;
						$zeit_arr [$m] ['SALDO_MONAT'] = $this->saldo_et;
					}
				}
				/* Buchungen zum ET */
				$kosten_arr = $this->get_kosten_von_bis ( 'Eigentuemer', $et_id, $m_von, $m_bis, $gk->geldkonto_id );
				if (is_array ( $kosten_arr )) {
					$anz_buchungen = count ( $kosten_arr );
					for($k = 0; $k < $anz_buchungen - 1; $k ++) {
						$datum = $kosten_arr [$k] ['DATUM'];
						$txt = bereinige_string ( $kosten_arr [$k] ['VERWENDUNGSZWECK'] );
						$betrag = $kosten_arr [$k] ['BETRAG'];
						
						$zeit_arr [$m] ['ET'] [$k] ['DATUM'] = $datum;
						$zeit_arr [$m] ['ET'] [$k] ['TXT'] = $txt;
						$zeit_arr [$m] ['ET'] [$k] ['BETRAG'] = $betrag;
						
						$this->saldo_et += $betrag;
						$zeit_arr [$m] ['SALDO_MONAT'] = $this->saldo_et;
					}
				}
			}
		} else {
			die ( "Zeitraum falsch $von $bis" );
		}
		
		return $zeit_arr;
		/*
		 * $this->saldo_et_vm
		 * $this->saldo_et
		 */
	}
	function auszugtest3($et_id, $von = null, $bis = null, $saldo_et = '0.00') {
		$this->saldo_et = $saldo_et;
		$weg = new weg ();
		$einheit_id = $weg->get_einheit_id_from_eigentuemer ( $et_id );
		// $e = new einheit();
		// $e->get_einheit_info($einheit_id);
		$weg_et = new weg ();
		$weg_et->get_eigentumer_id_infos4 ( $et_id );
		// echo '<pre>';
		// print_r($e);
		
		$gk = new geldkonto_info ();
		$gk->geld_konto_ermitteln ( 'Objekt', $weg_et->objekt_id );
		
		/* OBJEKTDATEN */
		/* Garantiemonate Objekt */
		$d = new detail ();
		$garantie_mon_obj = $d->finde_detail_inhalt ( 'Objekt', $weg_et->objekt_id, 'INS-Garantiemonate' );
		if (! $garantie_mon_obj) {
			$garantie_mon_obj = 0;
		}
		
		/* Garantiemonate Objekt */
		$d = new detail ();
		$garantie_mon_et = $d->finde_detail_inhalt ( 'Eigentuemer', $et_id, 'INS-ET-Garantiemonate' );
		if (! isset ( $garantie_mon_et )) {
			$garantie_mon_et = $garantie_mon_obj;
		}
		
		if ($garantie_mon_et == 0) {
			$garantie = 0;
		}
		
		if ($garantie_mon_et != 0) {
			$garantie = $garantie_mon_et;
		}
		
		/* ET DATEN */
		if ($weg->eigentuemer_bis == '0000-00-00') {
			$weg->eigentuemer_bis = date ( "Y-m-d" );
		}
		
		if ($von == null) {
			$von = $weg->eigentuemer_von;
		}
		if ($bis == null) {
			$bis = $weg->eigentuemer_bis;
		}
		
		/* MIETVERTRAEGE ZEITRAUM ET */
		$mv_arr = $this->get_mv_et_zeitraum_arr ( $einheit_id, $von, $bis );
		$anz_mv = count ( $mv_arr );
		if (is_array ( $mv_arr )) {
			// echo '<pre>';
			// print_r($mv_arr);
		} else {
			echo "NO MV - NUR KOSTEN";
		}
		
		$zeit_arr = $this->monats_array ( $von, $bis );
		// print_r($zeit_arr);
		// die();
		/* Durchlauf alle Monate */
		if (is_array ( $zeit_arr )) {
			$anz_m = count ( $zeit_arr );
			for($m = 0; $m < $anz_m; $m ++) {
				
				/* Garantiemonat */
				if ($m < $garantie) {
					$zeit_arr [$m] ['GAR_MON'] = 'JA';
				} else {
					$zeit_arr [$m] ['GAR_MON'] = 'NEIN';
				}
				
				/* Saldo Vormonat */
				$this->saldo_et_vm = $this->saldo_et;
				$zeit_arr [$m] ['SALDO_VM'] = $this->saldo_et_vm;
				
				$monat = $zeit_arr [$m] ['MONAT'];
				$jahr = $zeit_arr [$m] ['JAHR'];
				
				$m_von = "$jahr-$monat-01";
				$ltm = letzter_tag_im_monat ( $monat, $jahr );
				$m_bis = "$jahr-$monat-$ltm";
				
				$zeit_arr [$m] ['MIETER_M_SOLL'] = 0;
				
				$zeit_arr [$m] ['MIETER_ERG_SUM'] = 0;
				$zeit_arr [$m] ['SUM_MIETER_ZB'] = 0;
				$zeit_arr [$m] ['SUM_MIETER_NK'] = 0;
				$zeit_arr [$m] ['SUM_ET_BUCHUNGEN'] = 0;
				$zeit_arr [$m] ['SUM_EINHEIT_BUCHUNGEN'] = 0;
				
				/* Mieteinnahmen */
				for($a = 0; $a < $anz_mv; $a ++) {
					$mv_id = $mv_arr [$a] ['MIETVERTRAG_ID'];
					$mv = new mietvertraege ();
					$mv->get_mietvertrag_infos_aktuell ( $mv_id );
					$mk = new mietkonto ();
					// $mk->kaltmiete_monatlich($mv_id,$monat,$jahr);
					$mk->kaltmiete_monatlich_ink_vz ( $mv_id, $monat, $jahr );
					
					$mz = new miete ();
					$m_arr = $mz->get_monats_ergebnis ( $mv_id, $monat, $jahr );
					
					$m_soll_arr = explode ( '|', $m_arr ['soll'] );
					if (isset ( $m_soll_arr [1] )) {
						$m_arr ['soll_wm'] = $m_soll_arr [0];
						$m_arr ['soll_mwst'] = $m_soll_arr [1];
					} else {
						$m_arr ['soll_wm'] = $m_arr ['soll'];
						$m_arr ['soll_mwst'] = '0.00';
					}
					$nk = ($m_arr ['soll_wm'] * - 1) - $mk->ausgangs_kaltmiete;
					$zeit_arr [$m] ['MIETER'] [$a] ['MV_ID'] = $mv_id;
					
					$zeit_arr [$m] ['MIETER'] [$a] ['M_NAME'] = $mv->personen_name_string;
					$zeit_arr [$m] ['MIETER'] [$a] ['KM_SOLL'] = $mk->ausgangs_kaltmiete;
					$zeit_arr [$m] ['MIETER'] [$a] ['NK_SOLL'] = $nk;
					$zeit_arr [$m] ['MIETER'] [$a] ['WM_SOLL'] = $m_arr ['soll_wm'] * - 1;
					
					$zeit_arr [$m] ['MIETER_M_SOLL'] += $m_arr ['soll_wm'] * - 1;
					
					$zeit_arr [$m] ['MIETER'] [$a] ['MI_ERG'] = $m_arr ['erg'];
					
					$zeit_arr [$m] ['MIETER_ERG_SUM'] += $m_arr ['erg'];
					
					$zeit_arr [$m] ['MIETER'] [$a] ['MI_ZB'] = $m_arr ['zb'];
					$zeit_arr [$m] ['SUM_MIETER_ZB'] += $m_arr ['zb'];
					$zeit_arr [$m] ['SUM_MIETER_NK'] += $nk;
				} // ende MV*S
				
				/* Hausgeld Fixkosten */
				$weg = new weg ();
				$kosten_arr = $weg->get_monatliche_def ( $monat, $jahr, 'Einheit', $einheit_id );
				$anz_buchungen = count ( $kosten_arr );
				$sum_fixkosten = 0;
				for($k = 0; $k < $anz_buchungen; $k ++) {
					// $txt = $kosten_arr[$k]['KOSTENKAT'];
					$betrag = $kosten_arr [$k] ['SUMME'] * - 1;
					// $auszahlen = $sum_auszahlen+$betrag;
					// $saldo_et += $betrag;
					// echo "$txt $betrag<br>";
					// $zeit_arr[$m]['HAUSGELD'][$txt] = $betragx;
					// $this->saldo_et+=$betragx;
					// $zeit_arr[$m]['SALDO_MONAT'] = $this->saldo_et;
					$sum_fixkosten += $betrag;
				}
				
				if ($sum_fixkosten != 0) {
					$zeit_arr [$m] ['FIXKOSTEN'] = nummer_komma2punkt ( nummer_punkt2komma ( $sum_fixkosten ) );
				} else {
					$zeit_arr [$m] ['FIXKOSTEN'] = nummer_komma2punkt ( nummer_punkt2komma ( ($weg_et->einheit_qm_weg * 0.4) + 30 ) );
				}
				
				/* Abzufragende Konten */
				$kokonten [] = '1023'; // Kosten zu Einheit
				$kokonten [] = '4180'; // Gew�hrte Minderungen
				$kokonten [] = '4280'; // Gerichtskosten
				$kokonten [] = '4281'; // Anwaltskosten MEA
				$kokonten [] = '4282'; // Gerichtsvollzieher
				$kokonten [] = '5010'; // Eigent�mereinlagen
				$kokonten [] = '5020'; // ET Entnahmen
				$kokonten [] = '5021'; // Hausgeld
				$kokonten [] = '5400'; // Durch INS zu Erstatten
				$kokonten [] = '5500'; // INS Maklergeb�hr
				$kokonten [] = '5600'; // Mietaufhegungsvereinbarungen
				$kokonten [] = '6000'; // Hausgeldzahlungen
				$kokonten [] = '6010'; // Heizkosten
				$kokonten [] = '6020'; // Nebenkosten / Hausgeld
				$kokonten [] = '6030'; // IHR
				$kokonten [] = '6060'; // Verwaltergeb�hr
				
				/* Buchungen zu Einheit */
				$kosten_arr = $this->get_kosten_von_bis ( 'Einheit', $einheit_id, $m_von, $m_bis, $gk->geldkonto_id );
				// print_r($kosten_arr);
				if (is_array ( $kosten_arr )) {
					$anz_buchungen = count ( $kosten_arr );
					for($k = 0; $k < $anz_buchungen - 1; $k ++) {
						$datum = $kosten_arr [$k] ['DATUM'];
						$txt = bereinige_string ( $kosten_arr [$k] ['VERWENDUNGSZWECK'] );
						$betrag = $kosten_arr [$k] ['BETRAG'];
						$kkonto = $kosten_arr [$k] ['KONTENRAHMEN_KONTO'];
						if (in_array ( $kkonto, $kokonten )) {
							$zeit_arr [$m] ['EINHEIT'] [$k] ['DATUM'] = $datum;
							$zeit_arr [$m] ['EINHEIT'] [$k] ['KTO'] = $kkonto;
							$zeit_arr [$m] ['EINHEIT'] [$k] ['TXT'] = $txt;
							$zeit_arr [$m] ['EINHEIT'] [$k] ['BETRAG'] = $betrag;
							$zeit_arr [$m] ['SUM_EINHEIT_BUCHUNGEN'] += $betrag;
						}
						
						// $this->saldo_et+=$betrag;
						// $zeit_arr[$m]['SALDO_MONAT'] = $this->saldo_et;
					}
				} else {
					$zeit_arr [$m] ['EINHEIT'] = array ();
				}
				
				/* Buchungen zum ET */
				
				$kosten_arr = $this->get_kosten_von_bis ( 'Eigentuemer', $et_id, $m_von, $m_bis, $gk->geldkonto_id );
				if (is_array ( $kosten_arr )) {
					$anz_buchungen = count ( $kosten_arr );
					for($k = 0; $k < $anz_buchungen - 1; $k ++) {
						$datum = $kosten_arr [$k] ['DATUM'];
						$txt = bereinige_string ( $kosten_arr [$k] ['VERWENDUNGSZWECK'] );
						$betrag = $kosten_arr [$k] ['BETRAG'];
						$kkonto = $kosten_arr [$k] ['KONTENRAHMEN_KONTO'];
						if (in_array ( $kkonto, $kokonten )) {
							$zeit_arr [$m] ['ET'] [$k] ['DATUM'] = $datum;
							$zeit_arr [$m] ['ET'] [$k] ['KTO'] = $kkonto;
							$zeit_arr [$m] ['ET'] [$k] ['TXT'] = $txt;
							$zeit_arr [$m] ['ET'] [$k] ['BETRAG'] = $betrag;
							$zeit_arr [$m] ['SUM_ET_BUCHUNGEN'] += $betrag;
						}
						// $this->saldo_et+=$betrag;
						// $zeit_arr[$m]['SALDO_MONAT'] = $this->saldo_et;
					}
				}
				
				$zeit_arr [$m] ['SALDO_MONAT_ET1'] = ($zeit_arr [$m] ['SUM_MIETER_ZB'] - $zeit_arr [$m] ['SUM_MIETER_NK'] - $zeit_arr [$m] ['FIXKOSTEN']) + ($zeit_arr [$m] ['SUM_EINHEIT_BUCHUNGEN'] + $zeit_arr [$m] ['SUM_ET_BUCHUNGEN']);
				$zeit_arr [$m] ['SALDO_MONAT_ET'] = $zeit_arr [$m] ['SALDO_VM'] + ($zeit_arr [$m] ['SUM_MIETER_ZB'] - $zeit_arr [$m] ['SUM_MIETER_NK'] - $zeit_arr [$m] ['FIXKOSTEN']) + ($zeit_arr [$m] ['SUM_EINHEIT_BUCHUNGEN'] + $zeit_arr [$m] ['SUM_ET_BUCHUNGEN']);
				$this->saldo_et = $zeit_arr [$m] ['SALDO_MONAT_ET'];
				// $zeit_arr[$m]['SALDO_MONAT_MATH'] = $this->saldo_et;
				
				/* letzter Monat */
				if ($m == $anz_m - 1 && $zeit_arr [$m] ['MIETER_ERG_SUM'] > 0) {
					$zeit_arr [$m] ['SALDO_MONAT_ET'] = $zeit_arr [$m] ['SALDO_MONAT_ET'] - $zeit_arr [$m] ['MIETER_ERG_SUM'];
					$this->saldo_et = $zeit_arr [$m] ['SALDO_MONAT_ET'];
				}
				
				if ($m < $garantie && $this->saldo_et < 0) {
					$zeit_arr [$m] ['SALDO_MONAT_INS'] = $this->saldo_et;
				}
				
				if ($m + 1 == $garantie) {
					$zeit_arr [$m] ['SALDO_MONAT_ET'] = 0;
					$this->saldo_et = 0;
				}
			} // ende monat
		} else {
			die ( "Zeitraum falsch $von $bis" );
		}
		
		// echo '<pre>';
		// print_r($zeit_arr);
		// die();
		
		// echo "SANEL";
		$this->ausgabe_saldo_et15 ( $et_id, $zeit_arr );
		// die();
		// return $zeit_arr;
		/*
		 * $this->saldo_et_vm
		 * $this->saldo_et
		 */
	}
	function ausgabe_saldo_et15($et_id, $arr) {
		$wegg = new weg ();
		$wegg->get_eigentumer_id_infos4 ( $et_id );
		$wegg->empf_namen;
		
		$mon = count ( $arr );
		echo "<table>";
		echo "<tr><th>$wegg->einheit_kurzname  - $wegg->empf_namen</th><th>SOLL</th><th>IST</th><th>SALDO M</th><th>SALDO ET</th><th>SALDO INS</th></tr>";
		for($a = 0; $a < $mon; $a ++) {
			$monatnr = $a + 1;
			$monat = $arr [$a] ['MONAT'];
			$jahr = $arr [$a] ['JAHR'];
			$gar = $arr [$a] ['GAR_MON'];
			$saldo_vm = nummer_punkt2komma_t ( $arr [$a] ['SALDO_VM'] );
			$m_m_soll = nummer_punkt2komma_t ( $arr [$a] ['MIETER_M_SOLL'] * - 1 );
			$m_erg_sum = nummer_punkt2komma_t ( $arr [$a] ['MIETER_ERG_SUM'] );
			$m_sum_zb = nummer_punkt2komma_t ( $arr [$a] ['SUM_MIETER_ZB'] );
			$m_sum_nk = nummer_punkt2komma_t ( $arr [$a] ['SUM_MIETER_NK'] * - 1 );
			
			$ein_sum_buchungen = nummer_punkt2komma_t ( $arr [$a] ['SUM_EINHEIT_BUCHUNGEN'] );
			$ein_et_buchungen = nummer_punkt2komma_t ( $arr [$a] ['SUM_ET_BUCHUNGEN'] );
			$sum_fix = nummer_punkt2komma_t ( $arr [$a] ['FIXKOSTEN'] * - 1 );
			$saldo_et = nummer_punkt2komma_t ( $arr [$a] ['SALDO_MONAT_ET'] );
			$saldo_et1 = nummer_punkt2komma_t ( $arr [$a] ['SALDO_MONAT_ET1'] );
			$saldo_ins = nummer_punkt2komma_t ( $arr [$a] ['SALDO_MONAT_INS'] );
			
			// $saldo_et_math = nummer_punkt2komma_t($arr[$a]['SALDO_MONAT_MATH']);
			if ($gar == 'JA') {
				$bgcolor = "#FFB6C1";
			} else {
				$bgcolor = "#8FBC8F";
			}
			echo "<tr><td colspan=\"5\" align=\"center\" bgcolor=\"$bgcolor\">($monatnr. GARANTIE:$gar) <b> $monat.$jahr</b></td></tr>";
			echo "<tr><td colspan=\"4\"><b>SALDO VM</b></td><td><b>$saldo_vm</b></td></tr>";
			// echo "<tr><td >MIETER</td><td>$m_m_soll</td><td>$m_sum_zb</td><td>$m_erg_sum</td><td></td></tr>";
			
			if (isset ( $arr [$a] ['MIETER'] )) {
				echo "<tr><td><details><summary>MIETER BBBB</summary><ul>";
				$anz_bu = count ( $arr [$a] ['MIETER'] );
				echo "<table>";
				echo "<tr><th>MIETER</th><th>KM SOLL</th><th>NK</th><th>WM</th><th>ZB</th><th>ERG</th></tr>";
				for($bu = 0; $bu < $anz_bu; $bu ++) {
					$mname = $arr [$a] ['MIETER'] [$bu] ['M_NAME'];
					$mi_zb = $arr [$a] ['MIETER'] [$bu] ['MI_ZB'];
					
					// if(!empty($mi_zb) && $mi_zb!='0.00'){
					$km_soll = $arr [$a] ['MIETER'] [$bu] ['KM_SOLL'];
					$nk_soll = $arr [$a] ['MIETER'] [$bu] ['NK_SOLL'];
					$wm_soll = $arr [$a] ['MIETER'] [$bu] ['WM_SOLL'];
					$mi_erg = $arr [$a] ['MIETER'] [$bu] ['MI_ERG'];
					;
					
					echo "<tr><td>$mname</td><td>$km_soll</td><td>$nk_soll</td><td>$wm_soll</td><td>$mi_zb</td><td>$mi_erg</td></tr>";
					// }
				}
				echo "</table>";
				echo "</ul></details>";
			} else {
				echo "<tr><td>BUCHUNG MIETER";
			}
			
			echo "</td><td>$m_m_soll</td><td>$m_sum_zb</td><td>$m_erg_sum</td><td></td></tr>";
			
			echo "<tr><td>NEBENKOSTEN</td><td></td><td>$m_sum_nk</td><td></td><td></td></tr>";
			echo "<tr><td>FIXKOSTEN</td><td></td><td>$sum_fix</td><td></td><td></td></tr>";
			// echo "<tr><td>BUCHUNG EINHEIT</td><td></td><td>$ein_sum_buchungen</td><td></td><td></td></tr>";
			
			if (isset ( $arr [$a] ['EINHEIT'] )) {
				echo "<tr><td><details><summary>BUCHUNG EINHEIT</summary><ul>";
				$anz_bu = count ( $arr [$a] ['EINHEIT'] );
				echo "<table>";
				for($bu = 0; $bu < $anz_bu; $bu ++) {
					$kto = $arr [$a] ['EINHEIT'] [$bu] ['KTO'];
					$datum = $arr [$a] ['EINHEIT'] [$bu] ['DATUM'];
					$txt = $arr [$a] ['EINHEIT'] [$bu] ['TXT'];
					$b_betrag = $arr [$a] ['EINHEIT'] [$bu] ['BETRAG'];
					echo "<tr><td>$datum</td><td>$kto</td><td>$txt</td><td>$b_betrag</td></tr>";
				}
				echo "</table>";
				echo "</ul></details>";
			} else {
				echo "<tr><td>BUCHUNG EINHEIT";
			}
			
			echo "</td><td></td><td>$ein_sum_buchungen</td><td></td><td></td></tr>";
			
			if (isset ( $arr [$a] ['ET'] )) {
				echo "<tr><td><details><summary>BUCHUNG ET</summary><ul>";
				$anz_bu = count ( $arr [$a] ['ET'] );
				echo "<table>";
				for($bu = 0; $bu < $anz_bu; $bu ++) {
					$kto = $arr [$a] ['ET'] [$bu] ['KTO'];
					$datum = $arr [$a] ['ET'] [$bu] ['DATUM'];
					$txt = $arr [$a] ['ET'] [$bu] ['TXT'];
					$b_betrag = $arr [$a] ['ET'] [$bu] ['BETRAG'];
					echo "<tr><td>$datum</td><td>$kto</td><td>$txt</td><td>$b_betrag</td></tr>";
				}
				echo "</table>";
				echo "</ul></details>";
			} else {
				echo "<tr><td>BUCHUNG ET";
			}
			
			echo "</td><td></td><td>$ein_et_buchungen</td><td></td><td></td></tr>";
			
			// echo "<tr><td>SALDEN MIETER</td><td>$m_erg_sum</td><td></td></tr>";
			echo "<tr><td><b>SALDO MONAT ET</b></td><td></td><td><b>$saldo_et1</b></td><td></td><td><b>$saldo_et</b></td></tr>";
			echo "<tr><td><b>SALDO MONAT INS</b></td><td></td><td><b></b></td><td></td><td></td><td><b>$saldo_ins</b></td></tr>";
			echo "<tr><td colspan=\"6\"><hr></td></tr>";
		}
		echo "</table>";
		/*
		 * echo '
		 * <details>
		 * <summary>�bungen zu Kapitel 1</summary>
		 * <ul>
		 * <li><a href="/?exercise=A1E1">Grammar: simple past tense</a></li>
		 * <li><a href="/?exercise=A1E2">Vocabulary: things to eat</a></li>
		 * <li><a href="/?exercise=A1E3">Fun: watch the apes</a></li>
		 * </ul>
		 * </details>
		 * <details>
		 * <summary>�bungen zu Kapitel 2</summary>
		 * <ul>
		 * <li><a href="/?exercise=A2E1">Story: to be the first one</a></li>
		 * <li><a href="/?exercise=A2E2">Grammar: would</a></li>
		 * <li><a href="/?exercise=A2E3">Vocabulary: traffic</a></li>
		 * </ul>
		 * </details>';
		 */
		
		if (isset ( $_REQUEST ['pdf'] )) {
			ob_clean (); // ausgabepuffer leeren
			$pdf = new Cezpdf ( 'a4', 'portrait' );
			$bpdf = new b_pdf ();
			$bpdf->b_header ( $pdf, 'Partner', $_SESSION ['partner_id'], 'portrait', 'pdfclass/fonts/Helvetica.afm', 6 );
			
			$cols = array (
					'MONAT' => "Monat",
					'JAHR' => "Jahr",
					'GAR_MON' => "Gar.",
					'SUM_MIETER_ZB' => 'ZB',
					'SUM_MIETER_NK' => 'NK',
					'SUM_ET_BUCHUNGEN' => 'ET',
					'SUM_EINHEIT_BUCHUNGEN' => 'FLAT',
					'FIXKOSTEN' => 'FIX',
					'SALDO_MONAT_ET' => 'SALDOET',
					'SALDO_MONAT_ET1' => 'SALDOET1',
					'SALDO_MONAT_INS' => 'S_INS' 
			);
			
			// $seit_monat = monat2name($drucken_m);
			// $pdf->ezTable($arr);
			$pdf->ezTable ( $arr, $cols, "Mietkontenblatt seit $seit_monat $drucken_j", array (
					'showHeadings' => 1,
					'shaded' => 0,
					'titleFontSize' => 8,
					'fontSize' => 7,
					'xPos' => 50,
					'xOrientation' => 'right',
					'width' => 500,
					'rowGap' => 1,
					'cols' => array (
							'DATUM' => array (
									'justification' => 'right',
									'width' => 50 
							),
							'BEMERKUNG' => array (
									'justification' => 'left',
									'width' => 300 
							),
							'BETRAG' => array (
									'justification' => 'right',
									'width' => 75 
							),
							'SALDO' => array (
									'justification' => 'right',
									'width' => 75 
							) 
					) 
			) );
			ob_clean (); // ausgabepuffer leeren
			            // $gk_bez = date("Y_m_d").'_Mietkonto_kurz_'.str_replace(' ', '_', $mv->einheit_kurzname);
			            // $pdf_opt['Content-Disposition'] = $gk_bez;
			$pdf->ezStream ();
		}
	}
	function pdf_auszug1($et_id, $arr) {
		// echo '<pre>';
		// print_r($arr);
		// die();
		$weg = new weg ();
		$weg->get_eigentumer_id_infos3 ( $et_id );
		
		$pdf = new Cezpdf ( 'a4', 'portrait' );
		$bpdf = new b_pdf ();
		$bpdf->b_header ( $pdf, 'Partner', $_SESSION [partner_id], 'portrait', 'pdfclass/fonts/Helvetica.afm', 6 );
		
		$pdf->ezText ( "<b>$weg->empf_namen</b>", 10 );
		$pdf->ezSetDy ( - 5 );
		$pdf->ezText ( "$weg->einheit_kurzname - $weg->haus_strasse $weg->haus_nummer, $weg->haus_plz $weg->haus_stadt", 10 );
		$pdf->line ( 50, $pdf->y - 2, 545, $pdf->y - 2 ); // Linie
		
		$anz_m = count ( $arr );
		for($m = 0; $m < $anz_m; $m ++) {
			$monat = $arr [$m] ['MONAT'];
			$jahr = $arr [$m] ['JAHR'];
			$monat_name = monat2name ( $monat );
			$pdf->ezSetDy ( - 10 );
			/* Wenn kein platz - neue Seite */
			if ($pdf->y < '240.000') {
				$pdf->ezNewPage ();
			}
			
			$pdf->ezText ( "<b><i>Auszug $monat.$monatname $jahr</i></b>", 10 );
			$pdf->ezSetDy ( 11 );
			
			$pdf->ezSetMargins ( 135, 70, 50, 280 );
			$pdf->ezText ( "<b>SOLL WM</b>", 10, array (
					'justification' => 'right' 
			) );
			$pdf->ezSetMargins ( 135, 70, 50, 50 );
			$pdf->ezSetDy ( 11 );
			
			$pdf->ezSetMargins ( 135, 70, 50, 200 );
			$pdf->ezText ( "<b>SOLL EUR</b>", 10, array (
					'justification' => 'right' 
			) );
			$pdf->ezSetMargins ( 135, 70, 50, 50 );
			
			$pdf->ezSetDy ( 11 );
			$pdf->ezSetMargins ( 135, 70, 50, 130 );
			$pdf->ezText ( "<b>IST EUR</b>", 10, array (
					'justification' => 'right' 
			) );
			$pdf->ezSetMargins ( 135, 70, 50, 50 );
			
			$pdf->ezSetDy ( 11 );
			$pdf->ezText ( "<b>SALDO EUR</b>", 10, array (
					'justification' => 'right' 
			) );
			// $pdf->ezSetDy(1);
			$pdf->line ( 50, $pdf->y - 2, 545, $pdf->y - 2 ); // Linie
			
			$saldo_vm = $arr [$m] ['SALDO_VM'];
			$saldo_monat = $arr [$m] ['SALDO_MONAT'];
			$pdf->ezText ( "Saldo Vormonat", 10, array (
					'justification' => 'left' 
			) );
			$pdf->ezSetDy ( 12 );
			$saldo_vm_a = nummer_punkt2komma_t ( $saldo_vm );
			$pdf->ezText ( "<b>$saldo_vm_a EUR</b>", 10, array (
					'justification' => 'right' 
			) );
			// $pdf->line(50,$pdf->y-2,545,$pdf->y-2); // Linie
			
			$pdf->ezText ( "<u><b>Miete</b></u>" );
			
			$pdf->ezSetMargins ( 135, 70, 50, 200 );
			$anz_km_soll = count ( $arr [$m] ['KM_SOLL'] );
			$sum_km_soll = 0;
			for($km = 0; $km < $anz_km_soll; $km ++) {
				$km_soll = $arr [$m] ['KM_SOLL'] [$km] ['BETRAG'];
				
				$wm_soll_a = nummer_punkt2komma ( $arr [$m] ['WM_SOLL'] [$km] ['BETRAG'] );
				$km_soll_a = nummer_punkt2komma ( $arr [$m] ['KM_SOLL'] [$km] ['BETRAG'] );
				$m_erg_a = nummer_punkt2komma ( $arr [$m] ['M_ERG'] );
				$sum_km_soll += $arr [$m] ['KM_SOLL'] [$km] ['BETRAG'];
				$pdf->ezText ( "Sollmiete $m_erg_a xxxx", 10, array (
						'justification' => 'left' 
				) );
				// $pdf->ezSetMargins(135,70,50,130);
				$pdf->ezSetDy ( 12 );
				$pdf->ezText ( "$km_soll EUR", 10, array (
						'justification' => 'right' 
				) );
				$pdf->ezSetDy ( 12 );
				$pdf->ezSetMargins ( 50, 70, 50, 280 );
				$pdf->ezText ( "$wm_soll_a EUR", 10, array (
						'justification' => 'right' 
				) );
			}
			
			$pdf->ezSetMargins ( 135, 70, 50, 50 );
			
			if (is_array ( $arr [$m] ['EINNAHMEN'] )) {
				$anz_hg = count ( $arr [$m] ['EINNAHMEN'] );
				$hg_keys = array_keys ( $arr [$m] ['EINNAHMEN'] );
				$sum_km_ist = 0;
				for($hg = 0; $hg < $anz_hg; $hg ++) {
					$hg_key = $hg_keys [$hg];
					$hg_txt = $arr [$m] ['EINNAHMEN'] [$hg_key] ['TXT'];
					$hg_betrag = $arr [$m] ['EINNAHMEN'] [$hg_key] ['BETRAG'];
					$sum_km_ist += $hg_betrag;
					$pdf->ezText ( "$hg_txt EUR3", 10, array (
							'justification' => 'left' 
					) );
					$pdf->ezSetDy ( 12 ); // abstand
					$hg_betrag_a = nummer_punkt2komma_t ( $hg_betrag );
					$pdf->ezSetMargins ( 135, 70, 50, 130 );
					$pdf->ezText ( "$hg_betrag_a EUR", 10, array (
							'justification' => 'right' 
					) );
				}
				// print_r($hg_keys);
			} else {
				$sum_km_ist = 0;
				// $pdf->setColor(255,255,255);
				$pdf->setColor ( 1.0, 0.0, 0.0 );
				// $pdf->ezSetDy(12); //abstand
				// $pdf->ezText("Keine Mietzahlungen",10,array('justification' => 'left'));
				// $pdf->ezSetDy(10); //abstand);
				$pdf->ezSetDy ( 12 );
				$pdf->ezSetMargins ( 135, 70, 50, 130 );
				$pdf->ezText ( "0,00 EUR", 10, array (
						'justification' => 'right' 
				) );
				$pdf->setColor ( 0.0, 0.0, 0.0 );
			}
			
			if (is_array ( $arr [$m] ['HAUSGELD'] )) {
				$pdf->ezSetMargins ( 135, 70, 50, 200 ); // Abstand rechts 200
				
				$anz_hg = count ( $arr [$m] ['HAUSGELD'] );
				$hg_keys = array_keys ( $arr [$m] ['HAUSGELD'] );
				$pdf->ezText ( "<u><b>Feste Kosten</b></u>" );
				$sum_fixkosten = 0;
				for($hg = 0; $hg < $anz_hg; $hg ++) {
					$hg_key = $hg_keys [$hg];
					$hg_betrag = $arr [$m] ['HAUSGELD'] [$hg_key];
					$sum_fixkosten += $hg_betrag;
					$pdf->ezText ( "$hg_key", 10, array (
							'justification' => 'left' 
					) );
					$pdf->ezSetDy ( 12 ); // abstand);
					$hg_betrag_a = nummer_punkt2komma_t ( $hg_betrag );
					$pdf->ezText ( "$hg_betrag_a EUR", 10, array (
							'justification' => 'right' 
					) );
					
					$pdf->ezSetDy ( 12 );
					$pdf->ezSetMargins ( 135, 70, 50, 130 );
					$hg_betrag_a1 = nummer_punkt2komma_t ( $hg_betrag );
					$pdf->ezText ( "$hg_betrag_a1 EUR", 10, array (
							'justification' => 'right' 
					) );
					$pdf->ezSetMargins ( 135, 70, 50, 200 );
					
					if ($this->check_vg ( $_SESSION ['geldkonto_id'], $monat, $jahr, $et_id, $hg_betrag, null, null ) == '0') {
						$pdf->ezSetDy ( 12 );
						$pdf->ezSetMargins ( 135, 70, 50, 50 );
						$hg_betrag_a1 = nummer_punkt2komma_t ( $hg_betrag );
						$pdf->ezText ( "$hg_betrag_a1 EUR", 10, array (
								'justification' => 'right' 
						) );
						$pdf->ezSetMargins ( 135, 70, 50, 200 );
						// echo $arr[$m]['SALDO_MONAT'];
						// echo "$hg_betrag<br>";
						// $arr[$m]['SALDO_MONAT']+=$hg_betrag;
						// echo $arr[$m]['SALDO_MONAT'];
						// die();
					}
				}
				$pdf->ezSetDy ( - 3 ); // abstand);
				                   // $pdf->line(50,$pdf->y,545,$pdf->y); //erste Linie
				$pdf->ezSetDy ( - 2 ); // abstand);
				                   // $pdf->line(50,$pdf->y,545,$pdf->y); //zweite Linie
				                   // $pdf->ezSetDy(-2); //abstand);
				
				$pdf->ezText ( "<b>Zwischensummen</b>", 10, array (
						'justification' => 'left' 
				) );
				$pdf->ezSetDy ( 10 ); // abstand);
				$sum_fixkosten_a = nummer_punkt2komma_t ( $sum_fixkosten );
				$sum_kaltmiete = nummer_punkt2komma_t ( $sum_kaltmiete );
				$sum_zwischen_soll = $sum_km_soll + $sum_fixkosten;
				// echo "$sum_zwischen_soll = $sum_km_soll+$sum_fixkosten";
				// echo $sum_fixkosten;
				// die();
				$sum_zwischen_soll_a = nummer_punkt2komma_t ( $sum_zwischen_soll );
				$pdf->ezText ( "<b>$sum_zwischen_soll_a EUR</b>", 10, array (
						'justification' => 'right' 
				) );
				
				$sum_zwischen_ist = $sum_km_ist + $sum_fixkosten;
				$sum_zwischen_ist_a = nummer_punkt2komma_t ( $sum_zwischen_ist );
				
				$pdf->ezSetDy ( 12 );
				$pdf->ezSetMargins ( 135, 70, 50, 130 );
				if ($sum_zwischen_ist < 0) {
					$pdf->setColor ( 1.0, 0.0, 0.0 );
					$pdf->ezText ( "<b>$sum_zwischen_ist_a EUR</b>", 10, array (
							'justification' => 'right' 
					) );
					$pdf->setColor ( 0.0, 0.0, 0.0 );
				} else {
					$pdf->ezText ( "<b>$sum_zwischen_ist_a EUR</b>", 10, array (
							'justification' => 'right' 
					) );
				}
				
				$pdf->ezSetDy ( - 3 ); // abstand);
				                   // $pdf->line(50,$pdf->y,545,$pdf->y); //zweite Linie
				$pdf->ezSetMargins ( 135, 70, 50, 50 );
				
				// print_r($hg_keys);
			}
			
			if (is_array ( $arr [$m] ['EINHEIT'] )) {
				$anz_hg = count ( $arr [$m] ['EINHEIT'] );
				$hg_keys = array_keys ( $arr [$m] ['EINHEIT'] );
				$pdf->ezSetDy ( - 5 ); // abstand);
				$pdf->ezText ( "<u><b>Einheit</b></u>" );
				// $pdf->ezSetMargins(135,70,50,130);
				for($hg = 0; $hg < $anz_hg; $hg ++) {
					$hg_key = $hg_keys [$hg];
					$hg_txt = strip_tags ( $arr [$m] ['EINHEIT'] [$hg_key] ['TXT'] );
					$hg_betrag = $arr [$m] ['EINHEIT'] [$hg_key] ['BETRAG'];
					$hg_datum = $arr [$m] ['EINHEIT'] [$hg_key] ['DATUM'];
					$pdf->ezSetMargins ( 135, 70, 50, 130 );
					$pdf->ezText ( "$hg_datum $hg_txt", 10, array (
							'justification' => 'left' 
					) );
					$pdf->ezSetDy ( 10 ); // abstand);
					$hg_betrag_a = nummer_punkt2komma_t ( $hg_betrag );
					$pdf->ezSetMargins ( 135, 70, 50, 50 );
					$pdf->ezText ( "$hg_betrag_a EUR", 10, array (
							'justification' => 'right' 
					) );
				}
				// $pdf->ezSetMargins(135,70,50,50);
				// print_r($hg_keys);
			} else {
				// $pdf->ezText("Keine Reparaturen 0,00");
			}
			
			if (is_array ( $arr [$m] ['ET'] )) {
				$anz_hg = count ( $arr [$m] ['ET'] );
				$hg_keys = array_keys ( $arr [$m] ['ET'] );
				$pdf->ezSetDy ( - 5 ); // abstand);
				$pdf->ezText ( "<u><b>Eigent�mer</b></u>" );
				for($hg = 0; $hg < $anz_hg; $hg ++) {
					$hg_key = $hg_keys [$hg];
					$hg_txt = $arr [$m] ['ET'] [$hg_key] ['TXT'];
					$hg_betrag = $arr [$m] ['ET'] [$hg_key] ['BETRAG'];
					$pdf->ezSetMargins ( 135, 70, 50, 130 );
					$pdf->ezText ( "$hg_txt", 10, array (
							'justification' => 'left' 
					) );
					$pdf->ezSetDy ( 10 ); // abstand);
					$hg_betrag_a = nummer_punkt2komma_t ( $hg_betrag );
					$pdf->ezSetMargins ( 135, 70, 50, 50 );
					$pdf->ezText ( "$hg_betrag_a EUR", 10, array (
							'justification' => 'right' 
					) );
				}
				// print_r($hg_keys);
			} else {
				// $pdf->ezText("Keine Reparaturen 0,00");
			}
			
			$pdf->ezSetMargins ( 135, 70, 50, 50 );
			// $pdf->ezText("<b>=====================================================================================</b>");
			// $pdf->line(50,$pdf->y-2,545,$pdf->y-2); // Linie
			$pdf->ezText ( "<b>Saldo $monat.$jahr</b>", 10, array (
					'justification' => 'left' 
			) );
			$pdf->ezSetDy ( 10 ); // abstand););
			$saldo_monat_a = nummer_punkt2komma_t ( $saldo_monat );
			if ($saldo_monat < 0) {
				$pdf->setColor ( 1.0, 0.0, 0.0 );
				$pdf->ezText ( "<b><u>$saldo_monat_a EUR</u></b>", 10, array (
						'justification' => 'right' 
				) );
				$pdf->setColor ( 0.0, 0.0, 0.0 );
			} else {
				$pdf->ezText ( "<b><u>$saldo_monat_a EUR</u></b>", 10, array (
						'justification' => 'right' 
				) );
			}
			$pdf->line ( 50, $pdf->y - 3, 545, $pdf->y - 3 ); // Linie
			                                        
			// $pdf->ezText("<b>----------------------------------------------------------------------------------------------------------------------------------------------------</b>");
			
			$pdf->ezSetDy ( - 10 ); // abstand
		}
		// $pdf->ezTable($arr);
		ob_clean ();
		$pdf->ezStream ();
	}
	function check_vg($gk_id, $monat, $jahr, $et_id, $betrag = null, $text = null, $konto = null) {
		$weg = new weg ();
		$weg->get_eigentumer_id_infos3 ( $et_id );
		$einheit_id = $weg->einheit_id;
		if ($betrag != null) {
			$betrag_sql = " AND BETRAG='$betrag' ";
		} else {
			$betrag_sql = '';
		}
		
		if ($text != null) {
			$text_sql = " AND VERWENDUNGSZWECK LIKE '%text%' ";
		} else {
			$text_sql = '';
		}
		
		if ($konto != null) {
			$konto_sql = " AND KONTENRAHMEN_KONTO ='$konto' ";
		} else {
			$konto_sql = '';
		}
		
		$db_abfrage = "SELECT * FROM `GELD_KONTO_BUCHUNGEN` WHERE `GELDKONTO_ID` = '$gk_id' AND DATE_FORMAT( `DATUM` , '%Y-%m' ) = '$jahr-$monat' AND ((`KOSTENTRAEGER_TYP` = 'Einheit' AND `KOSTENTRAEGER_ID` = '$einheit_id') OR (`KOSTENTRAEGER_TYP` = 'Eigentuemer' AND `KOSTENTRAEGER_ID` = '$et_id')) $betrag_sql $text_sql $konto_sql   AND `AKTUELL` = '1' ORDER BY DATUM";
		$result = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			return $numrows;
		} else {
			return '0';
		}
	}
	function parse_auszug($upload_file) {
		$file = file ( $upload_file );
		// print_r($file);
		$anz = count ( $file );
		$auszug = 0;
		$datum_temp = '';
		for($a = 0; $a < $anz; $a ++) {
			$zeile = explode ( '*', $file [$a] );
			if ($a == 0) {
				$zeile1 ['kto'] = $zeile [41];
				$zeile1 ['blz'] = $zeile [40];
			}
			
			$datum = $zeile [1];
			if ($datum != $datum_temp) {
				$auszug ++;
				$datum_temp = $datum;
			}
			
			$z = $a + 1;
			
			$zeile [3] = $auszug;
			$vorzeichen = $zeile [6];
			if ($vorzeichen == '-') {
				$zeile [5] = $vorzeichen . $zeile [5];
			}
			$zeile1 [$a] ['datum'] = $zeile [1];
			$zeile1 [$a] ['auszug'] = $auszug;
			$zeile1 [$a] ['name'] = $zeile [20];
			$zeile1 [$a] ['betrag'] = $zeile [5];
			$zeile1 [$a] ['abs_kto'] = $zeile [14];
			$zeile1 [$a] ['abs_blz'] = $zeile [13];
			
			$zeile1 [$a] ['vzweck'] = str_replace ( 'MREF+', ' ', str_replace ( 'EREF+', '', str_replace ( 'KREF+', '', str_replace ( '  ', ' ', str_replace ( 'SVWZ+', ' ', str_replace ( 'PURP+RINP', '', $zeile [10] . ', ' . ltrim ( rtrim ( $zeile [22] ) ) . ' ' . ltrim ( $zeile [23] ) . $zeile [24] . $zeile [25] . $zeile [26] . $zeile [27] . $zeile [28] . ' ' . $zeile [29] . ' ' . $zeile [30] . ' ' . $zeile [31] . ' ' . $zeile [32] ) ) ) ) ) );
			
			// echo $auszug;
			// echo "$z<br>";
			// print_r($zeile);
		}
		return $zeile1;
		/*
		 * echo "<pre>";
		 * $_SESSION['kto_auszug'] = $zeile1;
		 * print_r($_SESSION['kto_auszug']);
		 */
	}
	function form_export_objekte() {
		$o = new objekt ();
		$arr = $o->liste_aller_objekte_kurz ();
		$anz = count ( $arr );
		$f = new formular ();
		$f->erstelle_formular ( 'Objekte f�r Export w�hlen', null );
		$f->hidden_feld ( 'option', 'exp_obj' );
		$f->send_button ( 'sndBtn', 'ALS CSV EXPORTIEREN' );
		echo "<table>";
		echo "<tr>";
		$z = 1;
		for($a = 0; $a < $anz; $a ++) {
			$o_id = $arr [$a] ['OBJEKT_ID'];
			$o_kn = $arr [$a] ['OBJEKT_KURZNAME'];
			echo "<td>";
			$f->check_box_js ( 'objekte_arr[]', $o_id, $o_kn, null, 'jhchecked' );
			echo "</td>";
			if ($z == '15') {
				echo "</tr><tr>";
				$z = 0;
			}
			$z ++;
		}
		echo "</tr></table>";
		$f->ende_formular ();
	}
} // end class listen

?>