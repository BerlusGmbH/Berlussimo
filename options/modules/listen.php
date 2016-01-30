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

/* überprüfen ob Benutzer Zugriff auf das Modul hat */
if (! check_user_mod ( $_SESSION ['benutzer_id'], 'listen' )) {
	echo '<script type="text/javascript">';
	echo "alert('Keine Berechtigung')";
	echo '</script>';
	die ();
}

/* Allgemeine Funktionsdatei laden */
include_once ("includes/allgemeine_funktionen.php");
/* Klasse "formular" für Formularerstellung laden */
include_once ("classes/class_formular.php");
/* Modulabhängige Dateien d.h. Links und eigene Klasse */
include_once ("options/links/links.listen.php");
include_once ("classes/class_listen.php");
include_once ("classes/class_sepa.php");

if (isset ( $_REQUEST ["option"] )) {
	$option = $_REQUEST ["option"];
} else {
	$option = 'default';
}
/* Optionsschalter */
switch ($option) {
	
	default :
		echo "Bitte wählen!";
		break;
	
	case "mieterliste_aktuell" :
		$e = new einheit ();
		if (isset ( $_REQUEST ['objekt_id'] ) && ! empty ( $_REQUEST ['objekt_id'] )) {
			$e->pdf_mieterliste ( 0, $_REQUEST ['objekt_id'] );
		} else {
			$e->pdf_mieterliste ( 0 );
		}
		break;
	
	case "inspiration_pdf" :
		$li = new listen ();
		if (isset ( $_REQUEST ['objekt_id'] ) && ! empty ( $_REQUEST ['objekt_id'] )) {
			if (isset ( $_REQUEST ['monat'] )) {
				$monat = $_REQUEST ['monat'];
			} else {
				$monat = date ( "m" );
			}
			
			if (isset ( $_REQUEST ['jahr'] )) {
				$jahr = $_REQUEST ['jahr'];
			} else {
				$jahr = date ( "Y" );
			}
			
			if (isset ( $_REQUEST ['lang'] )) {
				$lang = $_REQUEST ['lang'];
			} else {
				$lang = 'de';
			}
			
			$li->inspiration_pdf ( 0, $_REQUEST ['objekt_id'], $monat, $jahr, $lang );
		} else {
			hinweis_ausgeben ( "Auswahl treffen!!!" );
		}
		break;
	
	case "inspiration_pdf_6" :
		$li = new listen ();
		if (isset ( $_REQUEST ['objekt_id'] ) && ! empty ( $_REQUEST ['objekt_id'] )) {
			if (isset ( $_REQUEST ['monat'] )) {
				$monat = $_REQUEST ['monat'];
			} else {
				$monat = date ( "m" );
			}
			
			if (isset ( $_REQUEST ['jahr'] )) {
				$jahr = $_REQUEST ['jahr'];
			} else {
				$jahr = date ( "Y" );
			}
			
			if (isset ( $_REQUEST ['lang'] )) {
				$lang = $_REQUEST ['lang'];
			} else {
				$lang = 'de';
			}
			
			/* Heisst nach Wunsch von IG */
			// $li->inspiration_pdf_6(0, $_REQUEST['objekt_id'], $monat, $jahr, $lang);
			// echo "$monat $jahr".$_POST['objekt_id'];
			// die();
			
			$li->inspiration_pdf_kurz_6 ( 0, $_REQUEST ['objekt_id'], $monat, $jahr, $lang );
		} else {
			hinweis_ausgeben ( "ObjektID fehlt" );
		}
		break;
	
	case "inspiration_pdf_7" :
		$li = new listen ();
		if (isset ( $_REQUEST ['objekt_id'] ) && ! empty ( $_REQUEST ['objekt_id'] )) {
			if (isset ( $_REQUEST ['monat'] )) {
				$monat = $_REQUEST ['monat'];
			} else {
				$monat = date ( "m" );
			}
			
			if (isset ( $_REQUEST ['jahr'] )) {
				$jahr = $_REQUEST ['jahr'];
			} else {
				$jahr = date ( "Y" );
			}
			
			if (isset ( $_REQUEST ['lang'] )) {
				$lang = $_REQUEST ['lang'];
			} else {
				$lang = 'de';
			}
			
			/* Heisst nach Wunsch von IG */
			// $li->inspiration_pdf_6(0, $_REQUEST['objekt_id'], $monat, $jahr, $lang);
			// echo "$monat $jahr".$_POST['objekt_id'];
			// die();
			
			$li->inspiration_pdf_kurz_7 ( 0, $_REQUEST ['objekt_id'], $monat, $jahr, $lang );
		} else {
			hinweis_ausgeben ( "ObjektID fehlt" );
		}
		break;
	
	case "inspiration_sepa" :
		$li = new listen ();
		if (isset ( $_SESSION ['objekt_id'] ) && ! empty ( $_SESSION ['objekt_id'] )) {
			if (isset ( $_REQUEST ['monat'] )) {
				$monat = $_REQUEST ['monat'];
			} else {
				$monat = date ( "m" );
			}
			
			if (isset ( $_REQUEST ['jahr'] )) {
				$jahr = $_REQUEST ['jahr'];
			} else {
				$jahr = date ( "Y" );
			}
			
			if (isset ( $_REQUEST ['lang'] )) {
				$lang = $_REQUEST ['lang'];
			} else {
				$lang = 'de';
			}
			
			/* Vorschlag zur überweisung */
			$uebersicht_arr = $li->inspiration_sepa_arr ( 0, $_SESSION ['objekt_id'], $monat, $jahr, $lang );
			// $li->sepa_ueberweisung_anzeigen($uebersicht_arr);
			$li->form_sepa_ueberweisung_anzeigen ( $uebersicht_arr );
		} else {
			// print_r($_SESSION);
			hinweis_ausgeben ( "Auswahl treffen!!!" );
		}
		break;
	
	case "sepa_ueberweisen" :
		if (isset ( $_REQUEST ['eig_et'] ) && ! empty ( $_REQUEST ['eig_et'] ) && isset ( $_REQUEST ['betrag'] ) && ! empty ( $_REQUEST ['betrag'] )) {
			$e_id = $_REQUEST ['eig_et'];
			$betrag = $_REQUEST ['betrag'];
			$li = new listen ();
			$li->form_sepa_ueberweisung_et ( $e_id, $betrag );
		} else {
			fehlermeldung_ausgeben ( "Eigentümer und Betrag fehlen!!" );
		}
		break;
	
	case "sepa_sammler_hinzu" :
		// echo "<br><br><br><br><br><br><br><br><br><br><br>";
		// echo '<pre>';
		// print_r($_POST);
		// die();
		$sep = new sepa ();
		$vzweck = $_POST ['vzweck'];
		$von_gk_id = $_POST ['gk_id'];
		$_SESSION ['geldkonto_id'] = $von_gk_id;
		$an_sepa_gk_id = $_POST ['empf_sepa_gk_id'];
		
		$gk_infos = new geldkonto_info ();
		$gk_infos->geld_konto_details ( $an_sepa_gk_id );
		$vzweck_new = "$gk_infos->beguenstigter, $vzweck";
		
		$kat = $_POST ['kat'];
		$kos_typ = $_POST ['kos_typ'];
		$kos_id = $_POST ['kos_id'];
		$konto = $_POST ['konto'];
		$betrag = nummer_komma2punkt ( $_POST ['betrag'] );
		if ($betrag < 0) {
			die ( 'ABBRUCH MINUSBETRAG' );
		}
		if ($sep->sepa_ueberweisung_speichern ( $von_gk_id, $an_sepa_gk_id, $vzweck_new, $kat, $kos_typ, $kos_id, $konto, $betrag ) == false) {
			fehlermeldung_ausgeben ( "AUFTRAG KONNTE NICHT GESPEICHERT WERDEN!" );
		} else {
			weiterleiten ( "?daten=listen&option=inspiration_sepa" );
		}
		break;
	
	case "sammler_anzeigen" :
		if (isset ( $_SESSION ['geldkonto_id'] )) {
			$sep = new sepa ();
			$sep->sepa_sammler_anzeigen ( $_SESSION ['geldkonto_id'], 'ET-AUSZAHLUNG' );
		} else {
			fehlermeldung_ausgeben ( "Geldkonto wählen" );
		}
		break;
	
	case "inspiration_pdf_kurz" :
		$li = new listen ();
		if (isset ( $_REQUEST ['objekt_id'] ) && ! empty ( $_REQUEST ['objekt_id'] )) {
			if (isset ( $_REQUEST ['monat'] )) {
				$monat = $_REQUEST ['monat'];
			} else {
				$monat = date ( "m" );
			}
			
			if (isset ( $_REQUEST ['jahr'] )) {
				$jahr = $_REQUEST ['jahr'];
			} else {
				$jahr = date ( "Y" );
			}
			
			if (isset ( $_REQUEST ['lang'] )) {
				$lang = $_REQUEST ['lang'];
			} else {
				$lang = 'de';
			}
			
			/* Heisst nach Wunsch von IG */
			$li->inspiration_pdf_kurz ( 0, $_REQUEST ['objekt_id'], $monat, $jahr, $lang );
		} else {
			hinweis_ausgeben ( "Auswahl treffen!!!" );
		}
		break;
	
	case "bilanz" :
		$l = new listen ();
		$l->bilanz ();
		break;
	
	/* Sollmieten Zeitraum */
	case "sollmieten_zeitraum" :
		$li = new listen ();
		$objekt_id = $_REQUEST ['objekt_id'];
		
		// $mv->mieten_tabelle(4, '2011-12-01', '2012-11-31'); //XLS
		$li->mieten_pdf ( $objekt_id, '2013-08-01', '2013-08-31' );
		break;
	
	case "income_report" :
		$bg = new berlussimo_global ();
		$bg->objekt_auswahl_liste ( '?daten=listen&option=income_report' );
		
		if (! isset ( $_REQUEST ['jahr'] )) {
			$jahr = date ( "Y" ) - 1;
		} else {
			$jahr = $_REQUEST ['jahr'];
		}
		
		$bg->jahres_links ( $jahr, '?daten=listen&option=income_report' );
		
		if (isset ( $_REQUEST ['objekt_id'] )) {
			$_SESSION ['objekt_id'] = $_REQUEST ['objekt_id'];
		}
		// echo "$jahr".$_SESSION['objekt_id'];
		// die();
		$pdf = new Cezpdf ( 'a4', 'landscape' );
		$bpdf = new b_pdf ();
		$bpdf->b_header ( $pdf, 'Partner', $_SESSION ['partner_id'], 'landscape', 'pdfclass/fonts/Helvetica.afm', 6 );
		$pdf->ezStopPageNumbers ();
		$li = new listen ();
		// die('SIVAC');
		$li->pdf_income_reports2015_3 ( $pdf, $_SESSION ['objekt_id'], '2014' );
		
		// $li-> pdf_income_reports2($_SESSION['objekt_id'], $jahr);
		// $li->saldo_berechnung_et1(1733); //LEI FARHI voll vermietet
		// $li->saldo_berechnung_et1(1695);//LEI20-224 (AP24) Auszug 6. monat
		// $li->saldo_berechnung_et_DOBARpravo(1692);//WES-230 (apt 32) leer bei kauf
		// $li->saldo_berechnung_et_DOBARpravo_pdf($pdf, 1733);
		// $li->saldo_berechnung_et1(1548);//duschinger napf-se
		ob_clean (); // ausgabepuffer leeren
		header ( "Content-type: application/pdf" ); // wird von MSIE ignoriert
		$pdf->ezStream ();
		break;
	
	case "saldenpdf" :
		
		$pdf = new Cezpdf ( 'a4', 'landscape' );
		$bpdf = new b_pdf ();
		$bpdf->b_header ( $pdf, 'Partner', $_SESSION ['partner_id'], 'landscape', 'pdfclass/fonts/Helvetica.afm', 6 );
		$pdf->ezStopPageNumbers ();
		$objekt_id = $_SESSION ['objekt_id'];
		$o = new objekt ();
		$arr = $o->einheiten_objekt_arr ( $objekt_id );
		$anz_e = count ( $arr );
		for($a = 0; $a < $anz_e; $a ++) {
			$einheit_id = $arr [$a] ['EINHEIT_ID'];
			// $this->saldo_berechnung_et_pdf(&$pdf, $einheit_id);
			$li = new listen ();
			$li->saldo_berechnung_et_DOBARpravo_pdf ( $pdf, $einheit_id );
			$pdf->ezNewPage ();
		}
		// $li->salden_pdf_objekt($pdf, $objekt_id);
		
		// $li->saldo_berechnung_et_DOBARpravo_pdf($pdf, $einheit_id);
		ob_clean (); // ausgabepuffer leeren
		header ( "Content-type: application/pdf" ); // wird von MSIE ignoriert
		$pdf->ezStream ();
		
		break;
	
	case "sepa" :
		$sep = new sepa ();
		$sep->test_sepa ();
		break;
	
	case "pdf_bericht_se" :
		$li = new listen ();
		$objekt_id = $_REQUEST ['objekt_id'];
		
		if (! isset ( $_REQUEST ['jahr'] )) {
			$jahr = date ( "Y" );
		} else {
			$jahr = $_REQUEST ['jahr'];
		}
		
		if (isset ( $_REQUEST ['monat'] )) {
			$monat = $_REQUEST ['monat'];
		} else {
			$monat = date ( "m" );
		}
		
		// $li->pdf_bericht_se($objekt_id, $monat, $jahr);
		if (isset ( $_REQUEST ['einheit_id'] )) {
			$einheit_id = $_REQUEST ['einheit_id'];
		} else {
			$einheit_id = 914;
		}
		$li->kto_auszug_einheit ( $einheit_id );
		
		break;
	
	/* Profil für ein Objekt anlegen */
	case "profil_neu" :
		$l = new listen ();
		$l->form_profil_neu ();
		break;
	
	case "step2" :
		// echo '<pre>';
		if (! isset ( $_POST )) {
			fehlermeldung_ausgeben ( 'Profilformular ausfüllen!!!' );
		} else {
			if (isset ( $_POST ['kurz_b'] ) && isset ( $_POST ['objekt_id'] ) && isset ( $_POST ['gk_id'] ) && isset ( $_POST ['p_id'] )) {
				$kurz_b = $_POST ['kurz_b'];
				$obj_id = $_POST ['objekt_id'];
				$gk_id = $_POST ['gk_id'];
				$p_id = $_POST ['p_id'];
				// echo "$kurz_b $obj_id $gk_id $p_id";
				$l = new listen ();
				$profil_id = $l->report_profil_anlegen ( $kurz_b, $obj_id, $gk_id, $p_id );
				if (! is_numeric ( $profil_id )) {
					fehlermeldung_ausgeben ( "Profil nicht gespeichert!!" );
				} else {
					$_SESSION ['r_profil_id'] = $profil_id;
					$l->form_profil_step2 ( $profil_id );
				}
			}
		}
		
		break;
	
	case "profil_liste" :
		$l = new listen ();
		$l->profil_liste ();
		break;
	
	case "profil_wahl" :
		if (isset ( $_REQUEST ['profil_id'] )) {
			$_SESSION ['r_profil_id'] = $_REQUEST ['profil_id'];
		}
		weiterleiten ( "?daten=listen&option=profil_liste" );
		break;
	
	case "profil_edit" :
		if (isset ( $_REQUEST ['profil_id'] )) {
			$_SESSION ['r_profil_id'] = $_REQUEST ['profil_id'];
			$l = new listen ();
			$l->form_profil_step2 ( $_REQUEST ['profil_id'] );
		}
		
		break;
	
	case "konten_bearbeiten" :
		// print_req();
		if (isset ( $_POST ['profil_id'] ) && is_array ( $_POST ['b_konten'] )) {
			$l = new listen ();
			$l->b_konten_edit ( $_POST ['profil_id'], $_POST ['b_konten'], $_POST ['bez_arr'] );
			$_SESSION ['r_profil_id'] = $_POST ['profil_id'];
			$profil_id = $_SESSION ['r_profil_id'];
			weiterleiten ( "?daten=listen&option=profil_edit&profil_id=$profil_id" );
		} else {
			fehlermeldung_ausgeben ( "Buchungskonten für den Bericht wählen!!!" );
		}
		break;
	
	case "pruefung_bericht" :
		if (isset ( $_REQUEST ['profil_id'] )) {
			$_SESSION ['r_profil_id'] = $_REQUEST ['profil_id'];
			$li = new listen ();
			$li->pruefung_bericht ( $_SESSION ['r_profil_id'] );
		} else {
			fehlermeldung_ausgeben ( "Profil wählen" );
		}
		break;
	/* Neue PDF über Profile */
	case "dyn_pdf" :
		echo '<pre>';
		// print_r($_POST);
		// die();
		$li = new listen ();
		$li->dyn_pdf ( $_SESSION ['r_profil_id'], $_POST ['objekt_id'], $_POST ['monat'], $_POST ['jahr'], $_POST ['bericht_von'], $_POST ['bericht_bis'], $_POST ['bk_konten'], $_POST ['lang'] );
		
		break;
	
	case "auszugtest":
     	/*MOSH*/
     	#$einheit_id = 1690;
     	#$et_id = 931;
     	$li = new listen ();
		$einheit_id = 1693;
		$et_id = 1003;
		// $arr = $li->auszugtest1($et_id,'2014-06-01', '2014-12-31', '0.00');
		// $arr = $li->auszugtest1($et_id);
		$f = new formular ();
		$f->fieldset ( 'ET-SALDO', 'ets' );
		$arr = $li->auszugtest3 ( $et_id, '2014-06-01' );
		$f->fieldset_ende ();
		// echo $li->saldo_et_vm;
		// echo $li->saldo_et;
		// echo '<pre>';
		// print_r($arr);
		// die();
		// $li->pdf_auszug1($et_id, $arr);
		// $saldo = $li->auszugtest($einheit_id,$et_id, '0.00',2013);
		// $li->auszugtest($einheit_id,$et_id, $saldo, 2014);
		
		break;
	
	case "LST" :
		$file = file ( 'BOE.TXT' );
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
		echo "<pre>";
		$_SESSION ['kto_auszug'] = $zeile1;
		print_r ( $_SESSION ['kto_auszug'] );
		
		break;
	
	case "upload_auszug" :
		
		$sep = new sepa ();
		$sep->form_upload_excel_ktoauszug ( '' );
		if ($_FILES) {
			if (isset ( $_SESSION ['kto_auszug_arr'] )) {
				unset ( $_SESSION ['kto_auszug_arr'] );
			}
			
			$datei = $_FILES ['file'] ['tmp_name'];
			echo $datei;
			$li = new listen ();
			$_SESSION ['kto_auszug_arr'] = $li->parse_auszug ( $datei );
			
			/*
			 * echo '<pre>';
			 * print_r($_SESSION['kto_auszug_arr']);
			 */
		}
		// echo '<pre>';
		// print_r($_SESSION);
		
		if (is_array ( $_SESSION ['kto_auszug_arr'] )) {
			
			if (isset ( $_REQUEST ['ds_id'] )) {
				$_SESSION ['kto_auszug_ds'] = $_REQUEST ['ds_id'];
			}
			
			if (! isset ( $_SESSION ['kto_auszug_ds'] )) {
				$_SESSION ['kto_auszug_ds'] = 0;
			}
			
			if (isset ( $_REQUEST ['next'] )) {
				$_SESSION ['kto_auszug_ds'] ++;
			}
			
			if (isset ( $_REQUEST ['vor'] )) {
				$_SESSION ['kto_auszug_ds'] --;
			}
			
			$anz_ok = count ( $_SESSION ['kto_auszug_arr'] ) - 2;
			if ($_SESSION ['kto_auszug_ds'] >= $anz_ok or $_SESSION ['kto_auszug_ds'] < 0) {
				$_SESSION ['kto_auszug_ds'] = 0;
			}
			
			$sep->form_ds_kontoauszug ( $_SESSION ['kto_auszug_ds'] );
			if (isset ( $_SESSION ['geldkonto_id'] ) && isset ( $_SESSION ['temp_kontoauszugsnummer'] )) {
				$bu = new buchen ();
				$bu->buchungsjournal_auszug ( $_SESSION ['geldkonto_id'], $_SESSION ['temp_kontoauszugsnummer'] );
			}
		}
		
		break;
	
	case "excel_einzelbuchung" :
		// echo '<pre>';
		// print_req();
		// print_r($_SESSION);
		$kostentraeger_typ = $_POST ['kostentraeger_typ'];
		$kostentraeger_id = $_POST ['kostentraeger_id'];
		$kto_auszugsnr = $_SESSION ['temp_kontoauszugsnummer'];
		$datum = date_german2mysql ( $_SESSION ['temp_datum'] );
		$betrag = nummer_komma2punkt ( $_POST ['betrag'] );
		$kostenkonto = $_POST ['kostenkonto'];
		$vzweck = $_POST ['text'];
		$geldkonto_id = $_SESSION ['geldkonto_id'];
		$rechnungsnr = $kto_auszugsnr;
		
		if ($_POST ['mwst']) {
			$mwst = $betrag / 100 * 19;
		} else {
			$mwst = '0.00';
		}
		
		// die();
		$bu = new buchen ();
		$bu->geldbuchung_speichern ( $datum, $kto_auszugsnr, $rechnungsnr, $betrag, $vzweck, $geldkonto_id, $kostentraeger_typ, $kostentraeger_id, $kostenkonto, $mwst );
		
		// weiterleiten_in_sec('?daten=buchen&option=excel_buchen_session', 1);
		weiterleiten ( '?daten=listen&option=upload_auszug&next' );
		break;
	
	case "export_ins_objekte" :
		$li = new listen ();
		$li->form_export_objekte ();
		break;
	
	case "exp_obj" :
		
		if (isset ( $_POST ['objekte_arr'] )) {
			$weg = new weg ();
			$anz = count ( $_POST ['objekte_arr'] );
			$string = '';
			for($a = 0; $a < $anz; $a ++) {
				$obj_id = $_POST ['objekte_arr'] [$a];
				$str = $weg->stammdaten_weg ( $obj_id, 'export' );
				if ($a == 0) {
					$string .= $str;
				} else {
					$pos = strpos ( $str, "\n" ); // strpos($string, "\n");
					if ($pos) {
						$str_ohne_ue = substr ( $str, $pos + 1 );
						$string .= $str_ohne_ue;
					}
				}
			}
			ob_clean ();
			header ( "Content-Disposition: attachment; filename='OBJEKTE.CSV" );
			echo $string;
			die ();
		} else {
			fehlermeldung_ausgeben ( "Objekte wählen!" );
		}
		break;
}
?>