<?php
/*
 * Created on / Erstellt am : 02.11.2010
 * Author: Sivac
 */

/**
 * BERLUSSIMO
 *
 * Hausverwaltungssoftware
 *
 *
 * @copyright Copyright (c) 2010, Berlus GmbH, Fontanestr. 1, 14193 Berlin
 * @link http://www.berlus.de
 * @author Sanel Sivac & Wolfgang Wehrheim
 *         @contact software(@)berlus.de
 * @license http://www.gnu.org/licenses/agpl.html AGPL Version 3
 *         
 * @filesource $HeadURL$
 * @version $Revision$
 *          @modifiedby $LastChangedBy$
 *          @lastmodified $Date$
 *         
 */

/* Allgemeine Funktionsdatei laden */
include_once ("includes/allgemeine_funktionen.php");
/* Wegen PDF */
include_once ('classes/class_bpdf.php');

/* überprüfen ob Benutzer Zugriff auf das Modul hat */
if (! check_user_mod ( $_SESSION ['benutzer_id'], 'mietanpassung' )) {
	echo '<script type="text/javascript">';
	echo "alert('Keine Berechtigung')";
	echo '</script>';
	die ();
}

/* Klasse "formular" für Formularerstellung laden */
include_once ("classes/class_formular.php");

/* Modulabhängige Dateien d.h. Links und eigene Klasse */
include_once ("options/links/links.mietanpassung.php");
include_once ("classes/berlussimo_class.php");
include_once ("classes/class_mietanpassung.php");

if (isset ( $_REQUEST ["option"] ) && ! empty ( $_REQUEST ["option"] )) {
	$option = $_REQUEST ["option"];
} else {
	$option = 'default';
}

/* Optionsschalter */
switch ($option) {
	
	/* übersichtstabelle nach Mittelwert */
	case "uebersicht" :
		$ma = new mietanpassung ();
		$bg = new berlussimo_global ();
		$bg->objekt_auswahl_liste ( '?daten=mietanpassung&option=uebersicht' );
		// $ma->get_einheit_daten(237);
		// echo "<hr>";
		// $ma->get_einheit_daten(238);
		// #echo "<hr>";
		// $ma->get_einheit_daten(239);
		// echo "<hr>";
		// $ma->get_einheit_daten(39);
		// $ma->update_wohnlage(4);
		// $ma->liste_anzeigen(17);
		if (! empty ( $_SESSION ['objekt_id'] )) {
			$ma->liste_anzeigen ( $_SESSION ['objekt_id'] );
			// $ma->update_klassen('1'); // block 2
		}
		// $ma->update_klassen(4);
		// $ma->get_einheit_daten(597);
		break;
	
	/* übersicht der Mieter, die nach Mittelwert des MS aufeinmal erhöht werden sollen, SAMMELPDF */
	case "uebersicht_mw_netto" :
		$f = new formular ();
		$f->erstelle_formular ( "Stapelmieterhöhungen für Nettomieter", null );
		$ma = new mietanpassung ();
		$bg = new berlussimo_global ();
		$bg->objekt_auswahl_liste ( '?daten=mietanpassung&option=uebersicht_mw_netto' );
		
		if (! empty ( $_SESSION ['objekt_id'] )) {
			$arr = $ma->nettomieter_daten_arr ( $_SESSION ['objekt_id'], 'Nettomieter' );
			// echo '<pre>';
			// print_r($arr);
			// die();
			if (is_array ( $arr )) {
				$anz = count ( $arr );
				echo "<table>";
				echo "<tr><th>NR</th><th>";
				$f->check_box_js_alle ( 'einheit_all', 'a_ids', null, 'Alle', null, null, 'einheit_ids' );
				// $f->check_box_js('einheit_ids[]', $einheit_id, $einheit_kn, null, 'checked');
				// check_box_js($name, $wert, $label, $js, $checked)
				echo "</th><th>EINHEIT</th><th>NAME</th><th>L. ERH.</th><th>L. BETRAG</th><th>L %</th><th>WARTEN</th><th>ERHÖHUNG</th><th>ERH %</th></tr>";
				$sum = 0;
				$schnitt_proz = 0;
				$z = 0;
				for($a = 0; $a < $anz; $a ++) {
					
					$mon_mehr = nummer_punkt2komma ( $arr [$a] ['MONATLICH_MEHR'] );
					
					$mieter_art = $arr [$a] ['MIETER_ART'];
					$mietername = $arr [$a] ['MIETER'];
					$einheit_kn = $arr [$a] ['EINHEIT'];
					$einheit_id = $arr [$a] ['EINHEIT_ID'];
					$mv_id = $arr [$a] ['MV_ID'];
					
					/* Prüfen ob Mieter auszieht */
					$mv1 = new mietvertraege ();
					if ($mv1->check_auszug ( $mv_id ) == true) {
						$zieht_aus = 'JA';
					} else {
						$zieht_aus = 'NEIN';
					}
					/* Prüfen ob eventuel schon erhöht */
					if ($ma->check_erhoehung ( $mv_id ) == true) {
						$erhoeht = 'JA';
					} else {
						$erhoeht = 'NEIN';
					}
					// unset($this->naechste_erhoehung_datum);
					// unset($this->naechste_erhoehung_betrag);
					
					$l_datum = $arr [$a] ['L_ANSTIEG_DATUM'];
					$l_betrag = $arr [$a] ['L_ANSTIEG_BETRAG'];
					$l_anstieg_proz = nummer_komma2punkt ( nummer_punkt2komma ( $arr [$a] ['ANSTIEG_3J'] ) );
					$prozent_neu = nummer_punkt2komma ( $arr [$a] ['ANSTIEG_UM_PROZENT'] );
					// echo '<pre>';
					// print_r($arr);
					// die();
					
					$l_anstieg_vor_monaten = $arr [$a] ['L_ANSTIEG_MONATE'];
					$noch_monate_15 = 36 - $l_anstieg_vor_monaten;
					$o = new objekt ();
					$datum_15 = date_mysql2german ( $o->datum_plus_tage ( date ( "Y-m-d" ), $noch_monate_15 * 30 ) );
					$max_miete_mw = $arr [$a] ['NEUE_MIETE_M_WERT_W'];
					$neue_miete = $arr [$a] ['NEUE_MIETE'];
					$diff_abwarten = nummer_punkt2komma ( $max_miete_mw - $neue_miete );
					
					if ($mon_mehr > 0 && $mieter_art == 'Nettomieter' && $zieht_aus == 'NEIN' && $erhoeht == 'NEIN') {
						$z ++;
						// echo "$einheit_kn $mietername $mon_mehr<br>";
						if ($diff_abwarten == '0,00' or $prozent_neu == '15,00') {
							echo "<tr style=\"background-color:#99d0a5;\"><td>$z</td><td>";
						} else {
							echo "<tr><td>$z</td><td>";
						}
						if ($noch_monate_15 < 0 or $neue_miete == $max_miete_mw) {
							$f->check_box_js ( 'einheit_ids[]', $einheit_id, $einheit_kn, null, 'checked' );
						} else {
							$f->check_box_js ( 'einheit_ids[]', $einheit_id, $einheit_kn, null, null );
						}
						echo "</td><td>$einheit_kn</td><td>$mietername</td><td>$l_datum</td><td>$l_betrag</td><td>$l_anstieg_proz %</td>";
						echo "<td>$noch_monate_15 Mon. / +$diff_abwarten €</td>";
						
						echo "<td>$mon_mehr EUR</td><td>$prozent_neu %</td></tr>";
						$sum += nummer_komma2punkt ( $mon_mehr );
						$schnitt_proz += $prozent_neu;
					}
				}
				$prozent_schnitt_neu = nummer_punkt2komma ( $schnitt_proz / $z );
				$sum_a = nummer_punkt2komma_t ( $sum );
				echo "<tr><td></td><td></td><td></td><td></td><td></td><td></td><td><b>SUMME</b></td><td><b>$sum_a EUR</b></td><td>$prozent_schnitt_neu %</td></tr>";
			}
			$datum_h = date ( "d.m.Y" );
			$f->datum_feld ( 'Druckdatum', 'druckdatum', $datum_h, 'dddd' );
			$f->send_button ( 'BTN_Netto', 'PDF-Erstellen' );
			$f->hidden_feld ( 'option', 'nettostapel' );
			// $ma->nettomieter_daten_arr($_SESSION['objekt_id'], 'Bruttomieter');
		} else {
			fehlermeldung_ausgeben ( "Objekt wählen" );
		}
		// echo '<pre>';
		// print_r($arr);
		$f->ende_formular ();
		break;
	
	/* Nach Auswahl der Nettomieter CHECKBOX, die Stapel-PDF erstellen */
	case "nettostapel" :
		
		if (isset ( $_POST ['einheit_ids'] ) && isset ( $_POST ['druckdatum'] ) && isset ( $_POST ['BTN_Netto'] )) {
			print_req ();
			$anz_e = count ( $_POST ['einheit_ids'] );
			$druckdatum = $_POST ['druckdatum'];
			$man = new mietanpassung ();
			$ms_jahr = $man->get_ms_jahr ();
			
			ob_clean (); // ausgabepuffer leeren
			//include_once ('pdfclass/class.ezpdf.php');
			include_once ('classes/class_bpdf.php');
			$pdf = new Cezpdf ( 'a4', 'portrait' );
			$bpdf = new b_pdf ();
			$bpdf->b_header ( $pdf, 'Partner', $_SESSION [partner_id], 'portrait', 'pdfclass/fonts/Helvetica.afm', 6 );
			// $this->footer_zahlungshinweis = $bpdf->zahlungshinweis;
			
			$pdf->ezStopPageNumbers (); // seitennummerirung beenden
			
			$tab_ue = array ();
			$sum = 0;
			for($a = 0; $a < $anz_e; $a ++) {
				$einheit_id = $_POST ['einheit_ids'] [$a];
				$ma = new mietanpassung ();
				$array = $ma->get_einheit_daten ( $einheit_id, $ms_jahr );
				// echo '<pre>';
				// print_r($array);
				// die();
				$mietername = $array ['MIETER'];
				$tab_ue [$a] ['EINHEIT'] = $array ['EINHEIT'];
				$tab_ue [$a] ['MIETER'] = $array ['MIETER'];
				$tab_ue [$a] ['MEHR'] = nummer_punkt2komma ( $array ['MONATLICH_MEHR'] );
				$sum += nummer_komma2punkt ( $tab_ue [$a] ['MEHR'] );
				$tab_ue [$a] ['N_ANSTIEG_DATUM'] = $array ['N_ANSTIEG_DATUM'];
				$tab_ue [$a] ['NEUE_MIETE'] = nummer_punkt2komma ( $array ['NEUE_MIETE'] );
				
				$keys = array_keys ( $array );
				$anzahl_keys = count ( $keys );
				for($z = 0; $z < $anzahl_keys; $z ++) {
					$feld_keyname = $keys [$z];
					$feld_keyvalue = $array [$feld_keyname];
					$ber_array [$feld_keyname] = $feld_keyvalue;
				}
				
				$mv_id = $array ['MV_ID'];
				$anstiegs_datum = $array ['N_ANSTIEG_DATUM'];
				
				$mvv = new mietvertraege ();
				$mvv->get_mietvertrag_infos_aktuell ( $mv_id );
				$tab_ue [$a] ['ANSCHRIFT'] = "$mvv->haus_strasse $mvv->haus_nr";
				// print_r($mvv);
				// die();
				
				$datum_erh_arr = explode ( '.', $anstiegs_datum );
				$monat_erhoehung = $datum_erh_arr [1];
				$jahr_erhoehung = $datum_erh_arr [2];
				
				$nk_vorauszahlung = $ma->kosten_monatlich ( $mv_id, $monat_erhoehung, $jahr_erhoehung, 'Nebenkosten Vorauszahlung' );
				$nk_vorauszahlung_a = nummer_punkt2komma ( $nk_vorauszahlung );
				$hk_vorauszahlung = $ma->kosten_monatlich ( $mv_id, $monat_erhoehung, $jahr_erhoehung, 'Heizkosten Vorauszahlung' );
				$hk_vorauszahlung_a = nummer_punkt2komma ( $hk_vorauszahlung );
				$ber_array ['B_AKT_NK'] = "$nk_vorauszahlung_a";
				$ber_array ['B_AKT_HK'] = "$hk_vorauszahlung_a";
				
				$tab_ue [$a] ['NK'] = nummer_punkt2komma ( $ber_array ['B_AKT_NK'] );
				$tab_ue [$a] ['HK'] = nummer_punkt2komma ( $ber_array ['B_AKT_HK'] );
				
				$miete_aktuell = $array ['MIETE_AKTUELL'];
				$aktuelle_end_miete = $miete_aktuell + $nk_vorauszahlung + $hk_vorauszahlung;
				$aktuelle_end_miete_a = nummer_punkt2komma ( $aktuelle_end_miete );
				$ber_array ['B_AKT_ENDMIETE'] = "$aktuelle_end_miete_a";
				$tab_ue [$a] ['WM'] = nummer_punkt2komma ( nummer_komma2punkt ( $tab_ue [$a] ['NEUE_MIETE'] ) + nummer_komma2punkt ( $tab_ue [$a] ['MEHR'] ) + nummer_komma2punkt ( $tab_ue [$a] ['NK'] ) + nummer_komma2punkt ( $tab_ue [$a] ['HK'] ) );
				
				if ($a > 0) {
					$pdf->ezNewPage ();
				}
				$ma->pdf_anschreiben_MW_stapel ( $pdf, $ber_array, $druckdatum );
				// die();
			}
			
			// print_r($tab_ue);
			// die();
			/* übersichtseite */
			$tab_ue [$anz] ['MEHR'] = nummer_punkt2komma_t ( $sum );
			$pdf->ezNewPage ();
			$cols = array (
					'EINHEIT' => "Einheit",
					'MIETER' => "Mieter",
					'ANSCHRIFT' => "Anschrift",
					'MEHR' => "Erhö.",
					'NEUE_MIETE' => "Neue\nMiete",
					'NK' => "NK",
					'HK' => "HK",
					'WM' => "WM",
					'N_ANSTIEG_DATUM' => "ZUM" 
			);
			$pdf->ezTable ( $tab_ue, $cols, "Mieterhöhungen vom $druckdatum", array (
					'showHeadings' => 1,
					'shaded' => 1,
					'showLines' => 1,
					'titleFontSize' => 11,
					'fontSize' => 9,
					'xPos' => 55,
					'xOrientation' => 'right',
					'width' => 500,
					'cols' => array (
							'MEHR' => array (
									'justification' => 'right' 
							),
							'WM' => array (
									'justification' => 'right' 
							) 
					) 
			) );
			
			/* Ausgabe */
			ob_clean (); // ausgabepuffer leeren
			header ( "Content-type: application/pdf" ); // wird von MSIE ignoriert
			$dateiname = "Stapel_MHG_" . $ber->N_ANSTIEG_DATUM . "_vom_" . $druckdatum . ".pdf";
			$pdf_opt ['Content-Disposition'] = $dateiname;
			$pdf->ezStream ( $pdf_opt );
		}
		
		break;
	
	case "miete_anpassen_mw" :
		include_once ("classes/class_mietanpassung.php");
		if (! empty ( $_REQUEST ['einheit_id'] )) {
			$einheit_id = $_REQUEST ['einheit_id'];
			$ma = new mietanpassung ();
			$ms_jahr = $ma->get_ms_jahr ();
			$ma->form_mietanpassung ( $einheit_id, $ms_jahr );
		} else {
			echo "Einheit wählen";
		}
		break;
	
	case "ber_uebernehmen_netto" :
		// print_req();
		// die();
		if (isset ( $_SESSION [ber_arr] )) {
			unset ( $_SESSION [ber_arr] );
		}
		$ber = $_POST ['ber_array'];
		if (isset ( $_REQUEST ['ber_uebernehmen_netto'] )) {
			$ma = new mietanpassung ();
			if (isset ( $_POST ['druckdatum'] ) && ! empty ( $_POST ['druckdatum'] )) {
				$datum_d = $_POST ['druckdatum'];
			} else {
				$datum_d = date ( "d.m.Y" );
			}
			
			// die('SANEL');
			$ma->pdf_anschreiben ( $ber, $datum_d );
		}
		
		if (isset ( $_REQUEST ['ber_prozent'] )) {
			// $ma = new mietanpassung;
			// $ma->pdf_anschreiben_prozent($_POST[ber_array], date("d.m.Y"));
			$f = new formular ();
			$f->erstelle_formular ( "Mieterhöhung um  x Prozent", null );
			
			$f->text_feld ( 'Prozent eingeben', 'prozent', '', 5, 'prozent', '' );
			$f->hidden_feld ( 'option', 'ber_prozentual' );
			$_SESSION ['ber_arr'] = $ber;
			// $f->hidden_feld('ber_array', $ber);
			$f->send_button ( 'submit_pro', 'Berechnen -> PDF' );
			$f->ende_formular ();
		}
		break;
	
	case "ber_uebernehmen_brutto" :
		
		echo "<legend>";
		print_req ();
		echo "</legend>";
		// echo print_r($_SESSION['ber_array']);
		$ma = new mietanpassung ();
		$datum = date ( "d.m.Y" );
		$ber_array = $_REQUEST ['ber_array'];
		$ma->pdf_anschreiben_bruttomieter ( $ber_array, $datum );
		break;
	
	case "ber_prozentual" :
		$ber = $_SESSION ['ber_arr'];
		
		if (is_array ( $_SESSION [ber_arr] ) && ! empty ( $_REQUEST [prozent] )) {
			$prozent = nummer_komma2punkt ( $_REQUEST [prozent] );
			$ma = new mietanpassung ();
			$ma->pdf_anschreiben_prozent ( $_SESSION [ber_arr], date ( "d.m.Y" ) );
			
			$_SESSION [ber_arr] [ANSTIEG_UM_PROZENT] = $prozent;
			$neue_miete = (($_SESSION [ber_arr] ['MIETE_3_JAHRE'] / 100) * $prozent) + $_SESSION [ber_arr] ['MIETE_3_JAHRE'] + $_SESSION [ber_arr] ['GESAMT_ABZUG'];
			if ($neue_miete > $_SESSION [ber_arr] ['NEUE_MIETE']) {
				$max_miete = $_SESSION ['ber_arr'] ['NEUE_MIETE'];
				echo "Neue Miete = $neue_miete |||  Kappung: $max_miete<br>";
				die ( "NEUE MIETE HÖHER ALS KAPPUNGSGRENZE ODER MIETE NACH MIETSPIEGEL, PROZENTE REDUZIEREN!!!!" );
			} else {
				$_SESSION [ber_arr] ['NEUE_MIETE'] = $neue_miete;
			}
			
			$_SESSION [ber_arr] ['MONATLICH_MEHR'] = $_SESSION [ber_arr] ['NEUE_MIETE'] - $_SESSION [ber_arr] ['MIETE_AKTUELL'];
			$_SESSION [ber_arr] ['M2_PREIS_NEU'] = $_SESSION [ber_arr] ['NEUE_MIETE'] / $_SESSION [ber_arr] ['EINHEIT_QM'];
			$_SESSION [ber_arr] ['B_NEUE_ENDMIETE'] = nummer_punkt2komma ( $_SESSION [ber_arr] ['NEUE_MIETE'] + nummer_komma2punkt ( $_SESSION [ber_arr] ['B_AKT_NK'] ) + nummer_komma2punkt ( $_SESSION [ber_arr] ['B_AKT_HK'] ) );
			$_SESSION [ber_arr] [ANSTIEG_UM_PROZENT] = $_SESSION [ber_arr] ['NEUE_MIETE'] / ($_SESSION [ber_arr] ['MIETE_3_JAHRE'] / 100) - 100;
			print ('<pre>') ;
			print_r ( $_SESSION [ber_arr] );
			
			$ma->pdf_anschreiben_prozent ( $_SESSION [ber_arr], '19.01.2011' );
		} else {
			echo "DATEN UNVOLLSTÄNDIG";
		}
		break;
	
	/* Allen Einheiten die Auststattungsklasse 4 in Details hinzufügen */
	case "ak4" :
		if (! empty ( $_SESSION ['objekt_id'] )) {
			$ma = new mietanpassung ();
			echo "UPDATE....";
			// $ma->update_wohnlage($_SESSION['objekt_id']);
			$ma->update_klassen ( $_SESSION ['objekt_id'] );
		} else {
			fehlermeldung_ausgeben ( "Objekt wählen!!!" );
		}
		break;
} // end switch

?>
