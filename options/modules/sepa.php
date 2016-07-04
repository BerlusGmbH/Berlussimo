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
if (! check_user_mod ( $_SESSION ['benutzer_id'], 'sepa' )) {
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
//include_once ("options/links/links.sepa.php");
include_once ("classes/class_sepa.php");

if (isset ( $_REQUEST ["option"] )) {
	$option = $_REQUEST ["option"];
} else {
	$option = 'default';
}
/* Optionsschalter */
switch ($option) {
	
	default :
		$sep = new sepa ();
		// $sep->alle_mandate_anzeigen();
		$sep->sepa_sammler_alle ();
		
		break;
	
	case "mandate_mieter_kurz" :
		$sep = new sepa ();
		$sep->alle_mandate_anzeigen_kurz ( 'MIETZAHLUNG' );
		break;
	
	case "mandate_mieter" :
		$sep = new sepa ();
		$sep->alle_mandate_anzeigen ( 'MIETZAHLUNG' );
		break;
	
	case "mandate_rechnungen" :
		$sep = new sepa ();
		$sep->alle_mandate_anzeigen ( 'RECHNUNGEN' );
		break;
	
	case "mandate_hausgeld" :
		$sep = new sepa ();
		$sep->alle_mandate_anzeigen ( 'HAUSGELD' );
		break;
	
	case "mandat_mieter_neu" :
		$sep = new sepa ();
		if (isset ( $_SESSION ['geldkonto_id'] )) {
			$sep->form_mandat_mieter_neu ( $_SESSION ['geldkonto_id'] );
		} else {
			fehlermeldung_ausgeben ( "Erst Geldkonto wählen!!!" );
		}
		break;
	
	case "mandat_hausgeld_neu" :
		$sep = new sepa ();
		if (isset ( $_SESSION ['geldkonto_id'] )) {
			$sep->form_mandat_hausgeld_neu ( $_SESSION ['geldkonto_id'] );
		} else {
			fehlermeldung_ausgeben ( "Erst Geldkonto wählen!!!" );
		}
		break;
	
	case "mandat_mieter_neu_send" :
		echo "<hr><br><br><br><br><br><br><br><br><br><br>";
		if (isset ( $_REQUEST ['Button'] )) {
			if (! empty ( $_REQUEST ['mv_id'] )) {
				
				$kos_typ = $_REQUEST ['M_KOS_TYP'];
				
				if ($kos_typ == 'Mietvertrag') {
					$mref = 'MV' . $_REQUEST ['mv_id'];
					$n_art = 'MIETZAHLUNG';
				}
				if ($kos_typ == 'Eigentuemer') {
					$mref = 'WEG-ET' . $_REQUEST ['mv_id'];
					$n_art = 'HAUSGELD';
				}
				
				// $mref = 'MV'.$_REQUEST['mv_id'];
				$sep = new sepa ();
				if ($sep->check_m_ref ( $mref )) {
					fehlermeldung_ausgeben ( "Mandat $mref existiert schon!!!" );
				} else {
					// echo "existiert nicht";
					if (! empty ( $_REQUEST ['einzugsart'] ) && ! empty ( $_REQUEST ['BEGUENSTIGTER'] ) && ! empty ( $_REQUEST ['NAME'] ) && ! empty ( $_REQUEST ['ANSCHRIFT'] ) && ! empty ( $_REQUEST ['IBAN'] ) && ! empty ( $_REQUEST ['BIC'] ) && ! empty ( $_REQUEST ['BANK'] ) && ! empty ( $_REQUEST ['M_UDATUM'] ) && ! empty ( $_REQUEST ['M_ADATUM'] ) && ! empty ( $_REQUEST ['GK_ID'] ) && ! empty ( $_REQUEST ['GLAEUBIGER_ID'] )) {
						// echo "POST ok";
						/*
						 * [BEGUENSTIGTER] => Katrin Buchmann c/o Berlus GmbH
						 * [GLAEUBIGER_ID] => DE97ZZZ00000825342
						 * [mv_id] => 861
						 * [einzugsart] => Nur die Summe aus Vertrag
						 * [NAME] => Sanel Sivac
						 * [ANSCHRIFT] => Gartenfelder Str. 76A, 13599 Berlin
						 * [IBAN] => IB389123812939312938
						 * [BIC] => BIC30912931239129312
						 * [BANK] => Berliner Sparkasse
						 * [M_UDATUM] => 09.01.2014
						 * [M_ADATUM] => 09.01.2014
						 * [GK_ID] => 381
						 */
						
						$glaeubiger_id = $_REQUEST ['GLAEUBIGER_ID'];
						$gk_id = $_REQUEST ['GK_ID'];
						$empf = $_REQUEST ['BEGUENSTIGTER'];
						$name = $_REQUEST ['NAME'];
						$anschrift = $_REQUEST ['ANSCHRIFT'];
						$kto = '';
						$blz = '';
						$iban = $_REQUEST ['IBAN'];
						$bic = $_REQUEST ['BIC'];
						$bankname = $_REQUEST ['BANK'];
						$udatum = $_REQUEST ['M_UDATUM'];
						$adatum = $_REQUEST ['M_ADATUM'];
						// $edatum='';
						$m_art = 'WIEDERKEHREND';
						$e_art = $_REQUEST ['einzugsart'];
						
						$kos_id = $_REQUEST ['mv_id'];
						$edatum = '31.12.9999';
						$sep = new sepa ();
						$sep->mandat_speichern ( $mref, $glaeubiger_id, $gk_id, $empf, $name, $anschrift, $kto, $blz, $iban, $bic, $bankname, $udatum, $adatum, $edatum, $m_art, $n_art, $e_art, $kos_typ, $kos_id );
					} else {
						fehlermeldung_ausgeben ( "Eingabe unvollständig, bitte alle Felder ausfüllen!" );
					}
				}
			}
		}
		// print_req();
		
		break;
	
	case "mandat_edit_mieter" :
		$sep = new sepa ();
		$sep->form_mandat_mieter_edit ( $_REQUEST ['mref_dat'] );
		break;
	
	case "mandat_mieter_edit_send" :
		echo "<hr><br><br><br><br><br><br><br><br><br><br>";
		print_req ();
		
		if (isset ( $_REQUEST ['btn_edit_mieter'] ) && isset ( $_REQUEST ['mref_dat'] )) {
			if (! empty ( $_REQUEST ['mv_id'] )) {
				
				if (! empty ( $_REQUEST ['einzugsart'] ) && ! empty ( $_REQUEST ['BEGUENSTIGTER'] ) && ! empty ( $_REQUEST ['NAME'] ) && ! empty ( $_REQUEST ['ANSCHRIFT'] ) && ! empty ( $_REQUEST ['IBAN'] ) && ! empty ( $_REQUEST ['BIC'] ) && ! empty ( $_REQUEST ['BANK'] ) && ! empty ( $_REQUEST ['M_UDATUM'] ) && ! empty ( $_REQUEST ['M_ADATUM'] ) && ! empty ( $_REQUEST ['GK_ID'] ) && ! empty ( $_REQUEST ['GLAEUBIGER_ID'] )) {
					
					$kos_typ = $_REQUEST ['M_KOS_TYP'];
					
					if ($kos_typ == 'Mietvertrag') {
						$mref = 'MV' . $_REQUEST ['mv_id'];
						$n_art = 'MIETZAHLUNG';
					}
					if ($kos_typ == 'Eigentuemer') {
						$mref = 'WEG-ET' . $_REQUEST ['mv_id'];
						$n_art = 'HAUSGELD';
					}
					
					// $mref = 'MV'.$_REQUEST['mv_id'];
					$glaeubiger_id = $_REQUEST ['GLAEUBIGER_ID'];
					$gk_id = $_REQUEST ['GK_ID'];
					$empf = $_REQUEST ['BEGUENSTIGTER'];
					$name = $_REQUEST ['NAME'];
					$anschrift = $_REQUEST ['ANSCHRIFT'];
					$kto = $_REQUEST ['KTO'];
					$blz = $_REQUEST ['BLZ'];
					$iban = $_REQUEST ['IBAN'];
					$bic = $_REQUEST ['BIC'];
					$bankname = $_REQUEST ['BANK'];
					$udatum = $_REQUEST ['M_UDATUM'];
					$adatum = $_REQUEST ['M_ADATUM'];
					$edatum = $_REQUEST ['M_EDATUM'];
					// $edatum='';
					$m_art = 'WIEDERKEHREND';
					// $n_art='MIETZAHLUNG';
					$e_art = $_REQUEST ['einzugsart'];
					// $kos_typ='MIETVERTRAG';
					$kos_typ = $_REQUEST ['M_KOS_TYP'];
					$kos_id = $_REQUEST ['mv_id'];
					// $edatum = '31.12.9999';
					$sep = new sepa ();
					$dat = $_REQUEST ['mref_dat'];
					$sep->mandat_aendern ( $dat, $mref, $glaeubiger_id, $gk_id, $empf, $name, $anschrift, $kto, $blz, $iban, $bic, $bankname, $udatum, $adatum, $edatum, $m_art, $n_art, $e_art, $kos_typ, $kos_id );
				} else {
					fehlermeldung_ausgeben ( "Eingabe unvollständig, bitte alle Felder ausfüllen!" );
				}
			}
		}
		// print_req();
		break;
	
	case "sepa" :
		$sep = new sepa ();
		$sep->test_sepa ();
		break;
	
	case "import_dtaus" :
		$sep = new sepa ();
		$sep->import_dtaustn ( 31, '', '2013-11-15' );
		// import_dtaustn($objekt_id=41, $m_adatum='', $m_udatum=''){
		break;
	
	case "sepa_download" :
		if (isset ( $_POST ['Btn-SEPApdf'] )) {
			$pdf = '1';
		} else {
			$pdf = '0';
		}
		// print_req();
		// die();
		// echo "SEPA Download";
		// header("Cache-Control: no-store, no-cache");
		$dateiname_msgid = $_SESSION ['geldkonto_id'] . '-' . $_SESSION ['username'] . '-' . microtime ( 1 ) . '.xml';
		// echo $dateiname;
		// print_r($_SESSION);
		// die();
		// header("Content-disposition: attachment;filename=$dateiname_msgid");
		// header('Content-type: text/xml; charset=utf-8');
		$sep = new sepa ();
		// $sep->test_fremd_sepa_ls();///OKOKOK
		if (isset ( $_POST ['sammelbetrag'] )) {
			$sammelbetrag = $_POST ['sammelbetrag'];
			$nutzungsart = $_POST ['nutzungsart'];
			$sep->sepa_datei_erstellen ( 1, $dateiname_msgid, $nutzungsart, $pdf ); // als Sammelbetrag auf dem Kontoauszug!
		} else {
			$sep->sepa_datei_erstellen ( 0, $dateiname_msgid, $nutzungsart, $pdf ); // Einzelbeträge auf dem Kontoauszug
		}
		
		// $sep->sepa_datei_erstellen();
		break;
	
	case "test_ls" :
		$sep = new sepa ();
		$sep->test_fremd_sepa_ls ();
		break;
	
	case "mandat_nutzungen_anzeigen" :
		if (isset ( $_REQUEST ['m_ref'] ) && ! empty ( $_REQUEST ['m_ref'] )) {
			$sep = new sepa ();
			$sep->mandat_nutzungen_anzeigen ( $_REQUEST ['m_ref'] );
		} else {
			fehlermeldung_ausgeben ( "Mandat wählen" );
		}
		break;
	
	case "sammler_anzeigen" :
		if (isset ( $_SESSION ['geldkonto_id'] )) {
			$gk = new geldkonto_info ();
			$gk->geld_konto_details ( $_SESSION ['geldkonto_id'] );
			$sep = new sepa ();
			$sep->get_iban_bic ( $gk->kontonummer, $gk->blz );
			$gk_id = $_SESSION ['geldkonto_id'];
			echo "<h1>$gk->geldkonto_bezeichnung - $sep->IBAN1 - $sep->BIC</h1>";
			/*
			 * if($sep->sepa_sammler_anzeigen($_SESSION['geldkonto_id'], 'RECHNUNG')==true){
			 * $kat = 'RECHNUNG';
			 * echo "<a href=\"?daten=sepa&option=sammler2sepa&gk_id=$gk_id&kat=$kat\">SEPA-Datei für $kat erstellen</a>";
			 * }
			 * if($sep->sepa_sammler_anzeigen($_SESSION['geldkonto_id'], 'ET-AUSZAHLUNG')==true){
			 * $kat = 'ET-AUSZAHLUNG';
			 * echo "<a href=\"?daten=sepa&option=sammler2sepa&gk_id=$gk_id&kat=$kat\">SEPA-Datei für $kat erstellen</a>";
			 * }
			 *
			 * if($sep->sepa_sammler_anzeigen($_SESSION['geldkonto_id'], 'LOHN')==true){
			 * $kat = 'LOHN';
			 * echo "<a href=\"?daten=sepa&option=sammler2sepa&gk_id=$gk_id&kat=$kat\">SEPA-Datei für $kat erstellen</a>";
			 * }
			 *
			 * if($sep->sepa_sammler_anzeigen($_SESSION['geldkonto_id'], 'KK')==true){
			 * $kat = 'KK';
			 * echo "<a href=\"?daten=sepa&option=sammler2sepa&gk_id=$gk_id&kat=$kat\">SEPA-Datei für $kat erstellen</a>";
			 * }
			 * if($sep->sepa_sammler_anzeigen($_SESSION['geldkonto_id'], 'STEUERN')==true){
			 * $kat = 'STEUERN';
			 * echo "<a href=\"?daten=sepa&option=sammler2sepa&gk_id=$gk_id&kat=$kat\">SEPA-Datei für $kat erstellen</a>";
			 * }
			 */
			$sep->sepa_alle_sammler_anzeigen ();
		} else {
			fehlermeldung_ausgeben ( "Geldkonto wählen" );
		}
		break;
	
	case "sammler2sepa" :
		$sep = new sepa ();
		if (isset ( $_REQUEST ['gk_id'] ) && ! empty ( $_REQUEST ['gk_id'] ) && isset ( $_REQUEST ['kat'] ) && ! empty ( $_REQUEST ['kat'] )) {
			$von_gk_id = $_REQUEST ['gk_id'];
			$kat = $_REQUEST ['kat'];
			if ($kat == 'ET_AUSZAHLUNG') {
				$sammler = '0'; // Einzelbeträge
			} else {
				$sammler = '1'; // Nur einen Betrag
			}
			$sep->sammler2sepa ( $von_gk_id, $kat, $sammler );
		} else {
			fehlermeldung_ausgeben ( "Geldkonto und Kategorie wählen!!!" );
		}
		break;
	
	case "re_zahlen" :
		if (empty ( $_SESSION ['geldkonto_id'] )) {
			hinweis_ausgeben ( "Bitte Geldkonto auswählen!" );
		} else {
			$g = new geldkonto_info ();
			$g->geld_konto_details ( $_SESSION ['geldkonto_id'] );
			echo "<b>Ausgewähltes Konto $g->geldkonto_bezeichnung_kurz</b><br>";
		}
		
		if (isset ( $_REQUEST ['partner_wechseln'] )) {
			unset ( $_SESSION ['partner_id'] );
		}
		
		if (isset ( $_REQUEST ['partner_id'] )) {
			$_SESSION ['partner_id'] = $_REQUEST ['partner_id'];
		}
		
		$r = new rechnungen ();
		$p = new partner ();
		$link = "?daten=sepa&option=re_zahlen";
		
		if (isset ( $_REQUEST ['monat'] ) && isset ( $_REQUEST ['jahr'] )) {
			if ($_REQUEST ['monat'] != 'alle') {
				$_SESSION ['monat'] = sprintf ( '%02d', $_REQUEST ['monat'] );
			} else {
				$_SESSION ['monat'] = $_REQUEST ['monat'];
			}
			$_SESSION ['jahr'] = $_REQUEST ['jahr'];
		}
		
		if (empty ( $_SESSION ['partner_id'] )) {
			$p->partner_auswahl ( $link );
		} else {
			// $p->partner_auswahl($link);
			
			if (empty ( $_SESSION ['monat'] ) or empty ( $_SESSION ['jahr'] )) {
				$monat = date ( "m" );
				$jahr = date ( "Y" );
			} else {
				$monat = $_SESSION ['monat'];
				$jahr = $_SESSION ['jahr'];
			}
			
			if (! isset ( $_REQUEST ['belegnr'] )) {
				// $r->rechnungseingangsbuch_kurz_zahlung('Partner', $partner_id, $monat, $jahr, 'Rechnung');
				$r->rechnungseingangsbuch_kurz_zahlung_sepa ( 'Partner', $_SESSION ['partner_id'], $monat, $jahr, 'Rechnung' );
			} else {
				$u = new ueberweisung ();
				$belegnr = $_REQUEST ['belegnr'];
				$u->form_rechnung_dtaus_sepa ( $belegnr );
			}
		}
		break;
	
	case "ra_zahlen" :
		if (empty ( $_SESSION ['geldkonto_id'] )) {
			hinweis_ausgeben ( "Bitte Geldkonto auswählen!" );
		} else {
			$g = new geldkonto_info ();
			$g->geld_konto_details ( $_SESSION ['geldkonto_id'] );
			echo "<b>Ausgewähltes Konto $g->geldkonto_bezeichnung_kurz</b><br>";
		}
		
		if (isset ( $_REQUEST ['partner_wechseln'] )) {
			unset ( $_SESSION ['partner_id'] );
		}
		
		if (isset ( $_REQUEST ['partner_id'] )) {
			$_SESSION ['partner_id'] = $_REQUEST ['partner_id'];
		}
		
		$r = new rechnungen ();
		$p = new partner ();
		$link = "?daten=sepa&option=ra_zahlen";
		
		if (isset ( $_REQUEST ['monat'] ) && isset ( $_REQUEST ['jahr'] )) {
			if ($_REQUEST ['monat'] != 'alle') {
				$_SESSION ['monat'] = sprintf ( '%02d', $_REQUEST ['monat'] );
			} else {
				$_SESSION ['monat'] = $_REQUEST ['monat'];
			}
			$_SESSION ['jahr'] = $_REQUEST ['jahr'];
		}
		
		if (empty ( $_SESSION ['partner_id'] )) {
			$p->partner_auswahl ( $link );
		} else {
			// $p->partner_auswahl($link);
			
			if (empty ( $_SESSION ['monat'] ) or empty ( $_SESSION ['jahr'] )) {
				$monat = date ( "m" );
				$jahr = date ( "Y" );
			} else {
				$monat = $_SESSION ['monat'];
				$jahr = $_SESSION ['jahr'];
			}
			
			if (! isset ( $_REQUEST ['belegnr'] )) {
				// $r->rechnungseingangsbuch_kurz_zahlung('Partner', $partner_id, $monat, $jahr, 'Rechnung');
				$r->rechnungsausgangsbuch_kurz_zahlung_sepa ( 'Partner', $_SESSION ['partner_id'], $monat, $jahr, 'Rechnung' );
			} else {
				$u = new ueberweisung ();
				$belegnr = $_REQUEST ['belegnr'];
				$u->form_rechnung_dtaus_sepa ( $belegnr );
			}
		}
		break;
	
	/* Gleiche Option gibbt es in modul listen.php */
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
		$kat = $_POST ['kat'];
		$kos_typ = $_POST ['kos_typ'];
		$kos_id = $_POST ['kos_id'];
		$konto = $_POST ['konto'];
		$betrag = nummer_komma2punkt ( $_POST ['betrag'] );
		if ($betrag <= 0) {
			die ( 'ABBRUCH MINUSBETRAG' );
		}
		if ($sep->sepa_ueberweisung_speichern ( $von_gk_id, $an_sepa_gk_id, $vzweck, $kat, $kos_typ, $kos_id, $konto, $betrag ) == false) {
			fehlermeldung_ausgeben ( "AUFTRAG KONNTE NICHT GESPEICHERT WERDEN!" );
		} else {
			if ($kat == 'RECHNUNG') {
				weiterleiten ( "?daten=sepa&option=sammler_anzeigen" );
			}
			if ($kat == 'ET-AUSZAHLUNG') {
				weiterleiten ( "?daten=listen&option=sammler_anzeigen" );
			}
		}
		break;
	
	case "sepa_files" :
		if (! isset ( $_SESSION ['geldkonto_id'] )) {
			die ( fehlermeldung_ausgeben ( "Geldkonto wählen" ) );
		} else {
			$sep = new sepa ();
			$sep->sepa_files ( $_SESSION ['geldkonto_id'] );
		}
		break;
	
	case "sepa_files_fremd" :
		if (! isset ( $_SESSION ['geldkonto_id'] )) {
			die ( fehlermeldung_ausgeben ( "Geldkonto wählen" ) );
		} else {
			$sep = new sepa ();
			$sep->sepa_files ( null );
		}
		break;
	
	case "sepa_file_buchen_fremd" :
		if (! isset ( $_REQUEST ['sepa_file'] )) {
			die ( fehlermeldung_ausgeben ( "SEPA-DATEI wählen" ) );
		} else {
			$sep = new sepa ();
			$sep->sepa_file_buchen_fremd ( $_REQUEST ['sepa_file'] );
		}
		break;
	
	case "sepa_ue_buchen_fremd" :
		// print_req();
		if (is_array ( $_POST ['betrag'] )) {
			
			$anz = count ( $_POST ['betrag'] );
			for($a = 0; $a < $anz; $a ++) {
				
				$datum = $_POST ['datum'];
				$betrag = $_POST ['betrag'] [$a];
				if (isset ( $_POST ['mwst'] )) {
					$mwst = $betrag / 119 * 19;
				} else {
					$mwst = '0';
				}
				$kos_typ = $_POST ['kos_typ'] [$a];
				$kos_id = $_POST ['kos_id'] [$a];
				$geldkonto_id = $_POST ['gk_id'];
				$kostenkonto = $_POST ['konto'] [$a];
				$m_ref = $_POST ['m_ref'];
				$vzweck = $_POST ['vzweck'] [$a];
				$kto_auszugsnr = $_POST ['auszug'];
				if (! empty ( $kostenkonto )) {
					
					// print_req();
					$s = new sepa ();
					$s->betrag_buchen ( $datum, $kto_auszugsnr, $m_ref, $betrag, $vzweck, $geldkonto_id, $kos_typ, $kos_id, $kostenkonto, $mwst );
					hinweis_ausgeben ( "$vzweck $betrag gebucht." );
				} else {
					fehlermeldung_ausgeben ( "$vzweck $betrag nicht gebucht, Kostenkonto fehlt!!!!!" );
				}
			}
		}
		
		// $datei=$_REQUEST['sepa_file'];
		// weiterleiten("?daten=sepa&option=sepa_file_buchen&sepa_file=$datei");
		break;
	
	case "sepa_file_anzeigen" :
		if (! isset ( $_REQUEST ['sepa_file'] )) {
			die ( fehlermeldung_ausgeben ( "SEPA-DATEI wählen" ) );
		} else {
			$sep = new sepa ();
			$sep->sepa_file_anzeigen ( $_REQUEST ['sepa_file'] );
		}
		break;
	
	/* Sepafile Inhalt in Pool schieben, als Vorlage nutzen */
	case "sepa_file_kopieren" :
		if (! isset ( $_REQUEST ['sepa_file'] )) {
			die ( fehlermeldung_ausgeben ( "SEPA-DATEI wählen" ) );
		} else {
			$sep = new sepa ();
			if ($sep->sepa_file_kopieren ( $_REQUEST ['sepa_file'] )) {
				weiterleiten ( "?daten=sepa&option=sammler_anzeigen" );
			}
		}
		break;
	
	case "sepa_file_buchen" :
		if (! isset ( $_REQUEST ['sepa_file'] )) {
			die ( fehlermeldung_ausgeben ( "SEPA-DATEI wählen" ) );
		} else {
			$sep = new sepa ();
			$sep->sepa_file_buchen ( $_REQUEST ['sepa_file'] );
		}
		break;
	
	case "sepa_ue_buchen" :
		$datum = $_POST ['datum'];
		$betrag = $_POST ['betrag'];
		if (isset ( $_POST ['mwst'] )) {
			$mwst = $betrag / 119 * 19;
		} else {
			$mwst = '0';
		}
		$kos_typ = $_POST ['kos_typ'];
		$kos_id = $_POST ['kos_id'];
		$geldkonto_id = $_POST ['gk_id'];
		$kostenkonto = $_POST ['konto'];
		
		$m_ref = $_POST ['m_ref'];
		$vzweck = $_POST ['vzweck'];
		$kto_auszugsnr = $_POST ['auszug'];
		print_req ();
		$s = new sepa ();
		$s->betrag_buchen ( $datum, $kto_auszugsnr, $m_ref, $betrag, $vzweck, $geldkonto_id, $kos_typ, $kos_id, $kostenkonto, $mwst );
		$datei = $_REQUEST ['sepa_file'];
		weiterleiten ( "?daten=sepa&option=sepa_file_buchen&sepa_file=$datei" );
		break;
	
	case "sepa_file_pdf" :
		// print_req();
		if (isset ( $_REQUEST ['sepa_file'] ) && ! empty ( $_REQUEST ['sepa_file'] )) {
			$filename = $_REQUEST ['sepa_file'];
			$sep = new sepa ();
			$sep->sepa_file2pdf ( $filename );
		}
		break;
	
	case "sammel_ue" :
		$sep = new sepa ();
		$sep->form_sammel_ue ();
		$sep->sepa_alle_sammler_anzeigen ();
		break;
	
	case "sammel_ue_IBAN" :
		$sep = new sepa ();
		$sep->form_sammel_ue_IBAN ();
		$sep->sepa_alle_sammler_anzeigen ();
		break;
	
	case "sepa_sammler_hinzu_ue" :
		$sep = new sepa ();
		$vzweck = $_POST ['vzweck'];
		$von_gk_id = $_POST ['gk_id'];
		$_SESSION ['geldkonto_id'] = $von_gk_id;
		$an_sepa_gk_id = $_POST ['empf_sepa_gk_id'];
		$kat = $_POST ['kat'];
		$kos_typ = $_POST ['kos_typ'];
		$kos_bez = $_POST ['kos_id'];
		$bu = new buchen ();
		$kos_id = $bu->kostentraeger_id_ermitteln ( $kos_typ, $kos_bez );
		$konto = $_POST ['konto'];
		$betrag = nummer_komma2punkt ( $_POST ['betrag'] );
		
		if (empty ( $vzweck )) {
			die ( 'Verwendungszweck eingeben!!!!' );
		}
		if ($betrag <= 0) {
			die ( 'ABBRUCH BETRAG NULL ODER KLEINER' );
		}
		if ($sep->sepa_ueberweisung_speichern ( $von_gk_id, $an_sepa_gk_id, $vzweck, $kat, $kos_typ, $kos_id, $konto, $betrag ) == false) {
			fehlermeldung_ausgeben ( "AUFTRAG KONNTE NICHT GESPEICHERT WERDEN!" );
		} else {
			$_SESSION ['kos_typ'] = $kos_typ;
			$_SESSION ['kos_bez'] = $kos_id;
			// weiterleiten("?daten=sepa&option=sammler_anzeigen");
			weiterleiten ( "?daten=sepa&option=sammel_ue" );
		}
		break;
	
	case "sepa_sammler_hinzu_ue_IBAN" :
		$sep = new sepa ();
		$vzweck = $_POST ['vzweck'];
		$von_gk_id = $_POST ['gk_id'];
		$_SESSION ['geldkonto_id'] = $von_gk_id;
		$iban = $_POST ['iban'];
		$bic = $_POST ['bic'];
		$empfaenger = $_POST ['empfaenger'];
		$bank = $_POST ['bank'];
		$kat = $_POST ['kat'];
		$kos_typ = $_POST ['kos_typ'];
		$kos_bez = $_POST ['kos_id'];
		$bu = new buchen ();
		$kos_id = $bu->kostentraeger_id_ermitteln ( $kos_typ, $kos_bez );
		$konto = $_POST ['konto'];
		$betrag = nummer_komma2punkt ( $_POST ['betrag'] );
		
		if (empty ( $vzweck )) {
			die ( 'Verwendungszweck eingeben!!!!' );
		}
		if ($betrag <= 0) {
			die ( 'ABBRUCH BETRAG NULL ODER KLEINER' );
		}
		if ($sep->sepa_ueberweisung_speichern_IBAN ( $von_gk_id, $iban, $bic, $empfaenger, $bank, $vzweck, $kat, $kos_typ, $kos_id, $konto, $betrag ) == false) {
			fehlermeldung_ausgeben ( "AUFTRAG KONNTE NICHT GESPEICHERT WERDEN!" );
		} else {
			$_SESSION ['kos_typ'] = $kos_typ;
			$_SESSION ['kos_bez'] = $kos_id;
			// weiterleiten("?daten=sepa&option=sammler_anzeigen");
			weiterleiten ( "?daten=sepa&option=sammel_ue" );
		}
		break;
	
	case "sepa_datensatz_del" :
		echo "DEL";
		print_req ();
		if (isset ( $_REQUEST ['dat'] ) && ! empty ( $_REQUEST ['dat'] )) {
			$sep = new sepa ();
			if ($sep->datensatz_entfernen ( $_REQUEST ['dat'] )) {
				weiterleiten ( "?daten=sepa&option=sammler_anzeigen" );
			}
		}
		break;
	
	case "ls_auto_buchen" :
		// echo "LS_AUTOBUCHEN";
		$s = new sepa ();
		$arr = $s->get_sepa_lsfiles_arr ();
		if (is_array ( $arr )) {
			$anz = count ( $arr );
			echo "<table class=\"sortable\">";
			echo "<tr><th>DATUM</th><th>DATEI</th><th>ANZAHL LS</th><th>SUMME</th><th>OPTIONEN</th></tr>";
			$z = 0;
			for($a = 0; $a < $anz; $a ++) {
				$z ++;
				$anzahl = $arr [$a] ['ANZ'];
				$datei = $arr [$a] ['DATEI'];
				$summe_a = nummer_punkt2komma_t ( $arr [$a] ['SUMME'] );
				$datum = date_mysql2german ( $arr [$a] ['DATUM'] );
				$link_ab = "<a href=\"?daten=sepa&option=ls_auto_buchen_file&datei=$datei\">Autobuchen</a>";
				echo "<tr class=\"zeile$z\"><td>$datum</td><td>$datei</td><td>$anzahl</td><td>$summe_a</td><td>$link_ab</td></tr>";
				if ($z == 2) {
					$z = 0;
				}
			}
			echo "</table>";
		} else {
			fehlermeldung_ausgeben ( "Keine Lastschriftdateien vorhanden!" );
		}
		break;
	
	case "ls_auto_buchen_file" :
		if (isset ( $_REQUEST ['datei'] ) && ! empty ( $_REQUEST ['datei'] )) {
			$datei = $_REQUEST ['datei'];
			// echo $datei;
			$s = new sepa ();
			$s->form_ls_datei_ab ( $datei );
		}
		break;
	
	case "ls_zeile_buchen" :
		$datum = $_POST ['datum'];
		$betrag = nummer_komma2punkt ( $_POST ['betrag'] );
		if (isset ( $_POST ['mwst'] )) {
			$mwst = $betrag / 119 * 19;
		} else {
			$mwst = '0';
		}
		$kos_typ = $_POST ['kos_typ'];
		$kos_id = $_POST ['kos_id'];
		$geldkonto_id = $_POST ['gk_id'];
		
		$m_ref = $_POST ['m_ref'];
		if (stristr ( $m_ref, 'MV' ) == TRUE) {
			$kostenkonto = '80001';
		}
		
		if (stristr ( $m_ref, 'WEG-ET' ) == TRUE) {
			$kostenkonto = '6020';
		}
		if (! $kostenkonto) {
			DIE ( 'Kein Kostenkonto gewählt' );
		}
		
		$vzweck = "SEPA-LS $m_ref";
		$vzweck .= " " . $_POST ['vzweck'];
		$datei = $_POST ['datei'];
		$kto_auszugsnr = $_POST ['kontoauszug'];
		// print_req();
		$s = new sepa ();
		$s->betrag_buchen ( $datum, $kto_auszugsnr, $m_ref, $betrag, $vzweck, $geldkonto_id, $kos_typ, $kos_id, $kostenkonto, $mwst );
		weiterleiten ( "index.php?daten=sepa&option=ls_auto_buchen_file&datei=$datei" );
		break;
	
	case "sepa_ue_autobuchen" :
		// echo '<pre>';
		// print_r($_SESSION);
		if (isset ( $_POST )) {
			// print_r($_POST);
			if (! isset ( $_SESSION ['geldkonto_id'] )) {
				fehlermeldung_ausgeben ( "Geldkonto wählen" );
				die ();
			}
			
			if (! isset ( $_SESSION ['temp_kontoauszugsnummer'] )) {
				fehlermeldung_ausgeben ( "Kontrolldatein eingeben Kontoauszugsnummer!" );
				die ();
			}
			if (! isset ( $_SESSION ['temp_datum'] )) {
				fehlermeldung_ausgeben ( "Kontrolldatein eingeben Buchungsdatum!" );
				die ();
			}
			if (isset ( $_POST ['mwst'] )) {
				$mwst = 1;
			} else {
				$mwst = '0';
			}
			$file = $_POST ['file'];
			$sep = new sepa ();
			$sep->sepa_file_autobuchen ( $file, $_SESSION ['temp_datum'], $_SESSION ['geldkonto_id'], $_SESSION ['temp_kontoauszugsnummer'], $mwst );
		} else {
			fehlermeldung_ausgeben ( "Fehler beim Verbuchen EC232" );
		}
		break;
	
	case "excel_ue_autobuchen" :
		$_SESSION ['temp_datum'] = $_REQUEST ['datum'];
		$_SESSION ['geldkonto_id'] = $_REQUEST ['gk_id'];
		$_SESSION ['temp_kontoauszugsnummer'] = $_REQUEST ['auszug'];
		// print_r($_SESSION);
		// die();
		if (isset ( $_REQUEST ['mwst'] )) {
			$mwst = 1;
		} else {
			$mwst = '0';
		}
		
		$file = $_REQUEST ['datei'];
		$sep = new sepa ();
		$sep->sepa_file_autobuchen ( $file, $_SESSION ['temp_datum'], $_SESSION ['geldkonto_id'], $_SESSION ['temp_kontoauszugsnummer'], $mwst );
		break;
}
?>