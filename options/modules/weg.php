<?php
/*
 * Created on / Erstellt am : 16.11.2010
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

/* überprüfen ob Benutzer Zugriff auf das Modul hat */
if (! check_user_mod ( $_SESSION ['benutzer_id'], 'weg' )) {
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
include_once ("options/links/links.weg.php");
include_once ("classes/class_weg.php");
include_once ("classes/class_serienbrief.php");

if (! empty ( $_REQUEST ['objekt_id'] )) {
	$_SESSION ['objekt_id'] = $_REQUEST ['objekt_id'];
}

if (isset ( $_REQUEST ["option"] ) && ! empty ( $_REQUEST ["option"] )) {
	$option = $_REQUEST ["option"];
} else {
	$option = 'default';
}

if (! check_user_mod ( $_SESSION ['benutzer_id'], $option )) {
	echo '<script type="text/javascript">';
	echo "alert('Keine Berechtigung zu Option $option')";
	echo '</script>';
	die ();
}

/* Optionsschalter */
switch ($option) {
	
	default :
		// echo "WEG DEFAULSITE";
		$weg = new weg ();
		// $weg->uebersicht_einheit(609);
		if (! empty ( $_SESSION ['objekt_id'] )) {
			$o = new objekt ();
			$o->get_objekt_infos ( $_SESSION ['objekt_id'] );
			echo "Aktuelles Objekt $o->objekt_kurzname<br>Weitere Auswahl treffen";
			
			$einheiten_arr = $weg->einheiten_weg_tabelle_arr ( $_SESSION ['objekt_id'] );
			$anz = count ( $einheiten_arr );
			
			for($a = 0; $a < $anz; $a ++) {
				$einheit_id = $einheiten_arr [$a] ['EINHEIT_ID'];
				$weg->get_last_eigentuemer ( $einheit_id );
				if (isset ( $weg->eigentuemer_id )) {
					
					$et_p_id = $weg->get_person_id_eigentuemer_arr ( $weg->eigentuemer_id );
					if (is_array ( $et_p_id )) {
						$anz_pp = count ( $et_p_id );
						for($pe = 0; $pe < $anz_pp; $pe ++) {
							$et_p_id_1 = $et_p_id [$pe] ['PERSON_ID'];
							$detail = new detail ();
							if (($detail->finde_detail_inhalt ( 'PERSON', $et_p_id_1, 'Email' ))) {
								$email_arr = $detail->finde_alle_details_grup ( 'PERSON', $et_p_id_1, 'Email' );
								for($ema = 0; $ema < count ( $email_arr ); $ema ++) {
									$em_adr = $email_arr [$ema] ['DETAIL_INHALT'];
									$emails_arr [] = $em_adr;
								}
							}
						}
					}
				}
			}
			
			if (is_array ( $emails_arr )) {
				$emails_arr_u = array_values ( array_unique ( $emails_arr ) );
				$anz = count ( $emails_arr_u );
				echo "<hr><a href=\"mailto:";
				for($a = 0; $a < $anz; $a ++) {
					$email = $emails_arr_u [$a];
					echo "$email";
					if ($a < $anz - 1) {
						echo ",";
					}
				}
				echo "\">Email an alle Eigentümer ($anz Emailadressen)</a>";
			}
			
			// echo '<pre>';
			// print_r($emails_arr);
			// print_r($emails_arr_u);
			// print_r($einheiten_arr);
		} else {
			echo "Bitte ein Objekt aus de Objektliste wählen";
		}
		break;
	
	case "objekt_auswahl" :
		$weg = new weg ();
		$weg->liste_weg_objekte ( '?daten=weg' );
		break;
	
	case "einheiten" :
		if (! empty ( $_SESSION ['objekt_id'] )) {
			$weg = new weg ();
			$weg->einheiten_weg_tabelle_anzeigen ( $_SESSION ['objekt_id'] );
		} else {
			weiterleiten ( '?daten=weg&option=objekt_auswahl' );
		}
		break;
	
	case "einheit_uebersicht" :
		if (! empty ( $_REQUEST ['einheit_id'] )) {
			$weg = new weg ();
			$weg->uebersicht_einheit ( $_REQUEST ['einheit_id'] );
		} else {
			die ( 'Einheit wählen' );
		}
		break;
	
	case "eigentuemer_wechsel" :
		if (! empty ( $_SESSION ['objekt_id'] )) {
			$weg = new weg ();
			$weg->form_eigentuemer_einheit ( $_SESSION ['objekt_id'] );
		} else {
			die ( 'Bitte vorher Objekt wählen!!!' );
		}
		break;
	
	case "eigentuemer_aendern" :
		if (! empty ( $_REQUEST ['eigentuemer_id'] )) {
			$weg = new weg ();
			$weg->form_eigentuemer_aendern ( $_REQUEST ['eigentuemer_id'] );
		} else {
			die ( 'Bitte den Eigentümer wählen!!!' );
		}
		break;
	
	case "eigentuemer_send_aendern" :
		// echo '<pre>';
		// print_req();
		$weg = new weg ();
		
		if (isset ( $_REQUEST ['et_id'] )) {
			$et_id = $_REQUEST ['et_id'];
		} else {
			die ( 'Eigentümer ID fehlt' );
		}
		
		if (isset ( $_REQUEST ['einheit_id'] )) {
			$einheit_id = $_REQUEST ['einheit_id'];
		} else {
			die ( 'Einheit nicht gewählt' );
		}
		
		if (isset ( $_REQUEST ['z_liste'] )) {
			$eigent_arr = $_REQUEST ['z_liste'];
		} else {
			echo '<pre>';
			print_req ();
			die ( 'Personen nicht gewählt' );
		}
		
		if (isset ( $_REQUEST ['eigentuemer_seit'] )) {
			$eigentuemer_von = $_REQUEST ['eigentuemer_seit'];
		} else {
			die ( 'Datum Eigentümer SEIT fehlt!' );
		}
		
		if (isset ( $_REQUEST ['eigentuemer_bis'] )) {
			$eigentuemer_bis = $_REQUEST ['eigentuemer_bis'];
		} else {
			die ( 'Datum Eigentümer BIS fehlt!' );
		}
		
		$weg->eigentuemer_aendern_db ( $et_id, $einheit_id, $eigent_arr, $eigentuemer_von, $eigentuemer_bis );
		weiterleiten ( "?daten=weg&option=einheiten" );
		break;
	
	case "eigentuemer_send" :
		if (is_array ( $_POST [z_liste] )) {
			if (! empty ( $_POST [einheit_id] ) && ! empty ( $_POST [eigentuemer_seit] )) {
				$einheit_id = $_POST [einheit_id];
				$eigentuemer_seit = $_POST ['eigentuemer_seit'];
				$eigentuemer_bis = $_POST ['eigentuemer_bis'];
				$eigent_arr = $_POST [z_liste];
				$weg = new weg ();
				if (! isset ( $eigentuemer_bis ) or empty ( $eigentuemer_bis )) {
					$eigentuemer_bis = '00.00.0000';
				}
				$weg->eigentuemer_speichern ( $einheit_id, $eigent_arr, $eigentuemer_seit, $eigentuemer_bis );
				weiterleiten ( "?daten=weg&option=einheiten" );
			}
		} else {
			die ( 'Neue Eigentümer wählen!' );
		}
		break;
	
	case "wohngeld_buchen_auswahl_e" :
		if (! empty ( $_SESSION ['objekt_id'] )) {
			$w = new weg ();
			if (empty ( $_REQUEST ['monat'] )) {
				$monat = date ( "m" );
			}
			if (empty ( $_REQUEST ['jahr'] )) {
				$jahr = date ( "Y" );
			}
			$w->form_wg_einheiten ( $monat, $jahr, $_SESSION [objekt_id] );
		} else {
			echo "Objekt auswählen";
		}
		break;
	
	case "wohngeld_buchen_maske" :
		if (! empty ( $_REQUEST [einheit_id] )) {
			$w = new weg ();
			if (empty ( $_REQUEST [monat] )) {
				$monat = date ( "m" );
			} else {
				$monat = $_REQUEST [monat];
			}
			if (empty ( $_REQUEST [jahr] )) {
				$jahr = date ( "Y" );
			} else {
				$jahr = $_REQUEST [jahr];
			}
			
			$w->form_wohngeld_buchen ( $monat, $jahr, $_REQUEST [einheit_id] );
		} else {
			echo "Einheit wählen";
		}
		break;
	
	case "wohngeld_definieren" :
		if (! empty ( $_REQUEST ['einheit_id'] )) {
			$w = new weg ();
			$w->form_wg_definition_neu ( $_REQUEST ['einheit_id'] );
		} else {
			echo "Einheit wählen";
		}
		break;
	
	case "wg_def_exists" :
		if (! empty ( $_REQUEST ['einheit_id'] ) && ! empty ( $_REQUEST ['von'] ) && ! empty ( $_REQUEST ['betrag'] ) && ! empty ( $_REQUEST ['kostenart'] )) {
			$w = new weg ();
			$von = $_REQUEST ['von'];
			$bis = $_REQUEST ['bis'];
			$betrag = $_REQUEST ['betrag'];
			$e_konto_arr = explode ( '|', $_REQUEST ['kostenart'] );
			$e_konto = $e_konto_arr [0];
			$kostenkat = $e_konto_arr [1];
			$gruppe = $e_konto_arr [2];
			$g_konto = $e_konto_arr [3];
			// $kostenkat = $w->get_kostenkat($e_konto);
			// $gruppe = $_REQUEST['gruppe'];
			// $g_konto = $_REQUEST['g_konto'];
			$einheit_id = $_REQUEST ['einheit_id'];
			$w->wohngeld_def_speichern ( $von, $bis, $betrag, $kostenkat, $e_konto, $gruppe, $g_konto, $einheit_id );
			echo "Ihre Eingabe wurde gespeichert, sie werden zur Eingabemaske weitergeleitet.";
		} else {
			// print_req();
			echo "Dateneingabe unvollständig";
			// die();
		}
		if (! empty ( $_REQUEST ['einheit_id'] )) {
			$einheit_id = $_REQUEST ['einheit_id'];
			weiterleiten_in_sec ( "?daten=weg&option=wohngeld_definieren&einheit_id=$einheit_id", 2 );
		}
		
		break;
	
	case "wg_def_neu" :
		
		if (! empty ( $_REQUEST [einheit_id] ) && ! empty ( $_REQUEST [von] ) && ! empty ( $_REQUEST [betrag] ) && ! empty ( $_REQUEST [kostenkat] ) && ! empty ( $_REQUEST [e_konto] ) && ! empty ( $_REQUEST [gruppe] ) && ! empty ( $_REQUEST [g_konto] )) {
			$w = new weg ();
			$von = $_REQUEST ['von'];
			$bis = $_REQUEST ['bis'];
			$betrag = $_REQUEST ['betrag'];
			$kostenkat = $_REQUEST ['kostenkat'];
			$e_konto = $_REQUEST ['e_konto'];
			$gruppe = $_REQUEST ['gruppe'];
			$g_konto = $_REQUEST ['g_konto'];
			$einheit_id = $_REQUEST ['einheit_id'];
			$w->wohngeld_def_speichern ( $von, $bis, $betrag, $kostenkat, $e_konto, $gruppe, $g_konto, $einheit_id );
			echo "Ihre Eingabe wurde gespeichert, sie werden zur Eingabemaske weitergeleitet.";
		} else {
			echo "Dateneingabe unvollständig";
		}
		
		if (! empty ( $_REQUEST [einheit_id] )) {
			$einheit_id = $_REQUEST [einheit_id];
			weiterleiten_in_sec ( "?daten=weg&option=wohngeld_definieren&einheit_id=$einheit_id", 2 );
		}
		break;
	
	case "wg_buchen_send" :
		print_req ();
		if (! empty ( $_REQUEST ['eigentuemer_id'] ) && ! empty ( $_REQUEST ['einheit_id'] ) && ! empty ( $_REQUEST ['geld_konto'] ) && ! empty ( $_REQUEST ['datum'] ) && ! empty ( $_REQUEST [kontoauszugsnr] ) && is_array ( $_REQUEST [def_array] ) && is_array ( $_REQUEST [text_array] ) && ! empty ( $_REQUEST [wohngeld] ) && ! empty ( $_REQUEST [g_konto] ) && ! empty ( $_REQUEST [b_text] )) {
			$eigentuemer_id = $_REQUEST ['eigentuemer_id'];
			$einheit_id = $_REQUEST ['einheit_id'];
			$geldkonto_id = $_REQUEST ['geld_konto'];
			$datum = $_REQUEST ['datum'];
			$kontoauszugsnr = $_REQUEST ['kontoauszugsnr'];
			$def_array = $_REQUEST ['def_array'];
			$def_b_texte = $_REQUEST ['text_array'];
			$wg_g_konto = $_REQUEST ['g_konto'];
			$wg_g_betrag = $_REQUEST ['wohngeld'];
			$b_text = $_REQUEST ['b_text'];
			$w = new weg ();
			$w->wohngeld_buchung_speichern ( $eigentuemer_id, $einheit_id, $geldkonto_id, $datum, $kontoauszugsnr, $def_array, $def_b_texte, $wg_g_konto, $wg_g_betrag, $b_text );
		} else {
			echo "Buchungsdaten sind unvollständig";
		}
		// print_req();
		break;
	
	case "hausgeld_kontoauszug" :
		if (! empty ( $_REQUEST ['eigentuemer_id'] )) {
			$w = new weg ();
			$w->hausgeld_kontoauszug ( $_REQUEST ['eigentuemer_id'] );
		} else {
			echo "Einheit wählen";
		}
		break;
	
	case "wohngeld_def_del" :
		if (! empty ( $_REQUEST ['dat'] ) && ! empty ( $_REQUEST ['einheit_id'] )) {
			$w = new weg ();
			$w->wohngeld_def_delete ( $_REQUEST ['dat'] );
			$einheit_id = $_REQUEST ['einheit_id'];
			weiterleiten ( "?daten=weg&option=wohngeld_definieren&einheit_id=$einheit_id" );
		} else {
			echo "Hausgelddefintion wählen!";
		}
		break;
	
	case "wohngeld_def_aendern" :
		if (! empty ( $_REQUEST ['dat'] )) {
			$w = new weg ();
			$w->form_wohngeld_def_edit ( $_REQUEST ['dat'] );
		} else {
			echo "Hausgelddefintion wählen!";
		}
		break;
	
	case "wg_def_edit" :
		// print_req();
		if (isset ( $_REQUEST ['dat'] ) && $_REQUEST ['kostenart'] != 'Bitte wählen') {
			$dat = $_REQUEST ['dat'];
			$id = $_REQUEST ['id'];
			$kos_typ = $_REQUEST ['kos_typ'];
			$kos_id = $_REQUEST ['kos_id'];
			$von = $_REQUEST ['von'];
			$bis = $_REQUEST ['bis'];
			$betrag = $_REQUEST ['betrag'];
			$koskat_arr = explode ( '|', $_REQUEST ['kostenart'] );
			$e_konto = $koskat_arr [0];
			$kostenkat = $koskat_arr [1];
			$gruppe = $koskat_arr [2];
			$g_konto = $koskat_arr [3];
			// print_r($koskat_arr);
			/* Löschen */
			$w = new weg ();
			$w->wohngeld_def_delete ( $_REQUEST ['dat'] );
			/* Neu speichern */
			$w->wohngeld_def_speichern ( $von, $bis, $betrag, $kostenkat, $e_konto, $gruppe, $g_konto, $kos_id );
			weiterleiten ( "?daten=weg&option=wohngeld_definieren&einheit_id=$kos_id" );
		} else {
			fehlermeldung_ausgeben ( "Eingabe unvollständig" );
			weiterleiten_in_sec ( "?daten=weg&option=wohngeld_definieren&einheit_id=$_REQUEST[kos_id]", 2 );
		}
		break;
	
	case "einnahmen_ausgaben" :
		$f = new formular ();
		$f->fieldset ( 'Einnahmen/Ausgaben', 'ein_aus' );
		$w = new weg ();
		$w->einnahmen_ausgaben ( 19 );
		$f->fieldset_ende ();
		
		break;
	
	case "mahnliste" :
		if (! empty ( $_SESSION ['objekt_id'] )) {
			$w = new weg ();
			$w->mahnliste ( $_SESSION ['objekt_id'] );
		} else {
			echo "Objekt wählen";
		}
		break;
	
	case "mahnen" :
		if (! empty ( $_REQUEST [eig] )) {
			$w = new weg ();
			$w->form_mahnen ( $_REQUEST [eig] );
		} else {
			echo "Eigentümer wählen";
		}
		break;
	
	case "mahnung_sent" :
		if (! empty ( $_REQUEST [eig] ) && ! empty ( $_REQUEST [datum] ) && ! empty ( $_REQUEST [mahngebuehr] )) {
			$w = new weg ();
			$anschrift = $_REQUEST [anschriften];
			$w->pdf_mahnschreiben ( $_REQUEST [eig], $_REQUEST [datum], $_REQUEST [mahngebuehr], $anschrift );
		} else {
			echo "Eingaben unvollständig für ein Mahnschreiben!";
		}
		break;
	
	case "hg_kontoauszug" :
		if (! empty ( $_REQUEST [eigentuemer_id] )) {
			$w = new weg ();
			$w->hg_kontoauszug_anzeigen_pdf ( $_REQUEST [eigentuemer_id] );
			/*
			 * include_once('pdfclass/class.ezpdf.php');
			 * include_once('classes/class_bpdf.php');
			 * $pdf = new Cezpdf('a4', 'portrait');
			 * $bpdf = new b_pdf;
			 * $bpdf->b_header($pdf, 'Partner', $_SESSION[partner_id], 'portrait', 'Helvetica.afm', 6);
			 *
			 *
			 * $w->hg_ist_soll_pdf($pdf, $_REQUEST[eigentuemer_id]);
			 * ob_clean(); //ausgabepuffer leeren
			 * $pdf->ezStream();
			 */
		} else {
			echo "Eigentuemer wählen";
		}
		break;
	
	case "wpliste" :
		if (! empty ( $_SESSION ['objekt_id'] )) {
			$w = new weg ();
			$w->wp_liste ( $_SESSION ['objekt_id'] );
		} else {
			echo "Objekt wählen!";
		}
		break;
	
	case "wp_neu" :
		$w = new weg ();
		$w->form_wplan_neu ();
		break;
	
	case "wp_neu_send" :
		if (! empty ( $_REQUEST [wjahr] ) && ! empty ( $_REQUEST [objekt_id] )) {
			$w = new weg ();
			print_req ();
			$w->wp_plan_speichern ( $_REQUEST [wjahr], $_REQUEST [objekt_id] );
		} else {
			echo "Wirtschaftjahr eingeben und Objekt wählen bitte!";
		}
		
		break;
	
	case "wp_zeile_neu" :
		if (! empty ( $_REQUEST [wp_id] )) {
			$_SESSION [wp_id] = $_REQUEST [wp_id];
			$w = new weg ();
			$w->form_wplan_zeile ( $_SESSION [wp_id] );
		}
		
		break;
	
	case "wp_zeile_send" :
		echo '<pre>';
		print_r ( $_POST );
		if (! empty ( $_REQUEST ['bkonto'] ) && ! empty ( $_SESSION ['wp_id'] ) && ! empty ( $_REQUEST ['vsumme'] ) && ! empty ( $_REQUEST ['formel'] ) && ! empty ( $_REQUEST ['wirt_id'] )) {
			$weg = new weg ();
			$betrag_vj = $_REQUEST ['summe_vj'];
			$formel = $_REQUEST ['formel'];
			$wirt_id = $_REQUEST ['wirt_id'];
			$weg->wp_zeile_speichern ( $_SESSION ['wp_id'], $_REQUEST ['bkonto'], $_REQUEST ['vsumme'], $betrag_vj, $formel, $wirt_id );
			weiterleiten ( "?daten=weg&option=wp_zeile_neu&wp_id=$_SESSION[wp_id]" );
		}
		break;
	
	case "wplan_pdf" :
		if (! empty ( $_REQUEST ['wp_id'] )) {
			$w = new weg ();
			$w->pdf_wplan ( $_REQUEST ['wp_id'] );
		} else {
			echo "Wirtschaftsplan wählen!";
		}
		break;
	
	case "hga_profile" :
		$w = new weg ();
		$w->tab_profile ();
		break;
	
	case "hga_profile_del" :
		$w = new weg ();
		$w->hga_profil_del ( $_REQUEST ['profil_id'] );
		weiterleiten ( "?daten=weg&option=hga_profile" );
		break;
	
	case "hga_profile_wahl" :
		$w = new weg ();
		$w->hga_profil_wahl ( $_REQUEST ['profil_id'] );
		break;
	
	case "grunddaten_profil" :
		if (isset ( $_REQUEST ['profil_id'] ) && ! empty ( $_REQUEST ['profil_id'] )) {
			$_SESSION ['hga_profil_id'] = $_REQUEST ['profil_id'];
			$weg = new weg ();
			$weg->form_hga_profil_grunddaten ( $_REQUEST ['profil_id'] );
		} else {
			fehlermeldung_ausgeben ( "HGA Profil wählen!" );
		}
		break;
	
	case "profil_send_gaendert" :
		// print_req();
		if (! empty ( $_REQUEST ['profil_id'] ) && ! empty ( $_REQUEST ['profilbez'] ) && ! empty ( $_REQUEST ['objekt_id'] ) && ! empty ( $_REQUEST ['jahr'] ) && ! empty ( $_REQUEST ['geldkonto_id'] ) && ! empty ( $_REQUEST ['gk_id_ihr'] ) && ! empty ( $_REQUEST ['wp_id'] ) && ! empty ( $_REQUEST ['hg_konto'] ) && ! empty ( $_REQUEST ['hk_konto'] ) && ! empty ( $_REQUEST ['ihr_konto'] )) {
			$weg = new weg ();
			$weg->hga_profil_aendern ( $_REQUEST ['profil_id'], $_REQUEST ['objekt_id'], $_REQUEST ['geldkonto_id'], $_REQUEST ['jahr'], $_REQUEST ['profilbez'], $_REQUEST ['gk_id_ihr'], $_REQUEST ['wp_id'], $_REQUEST ['hg_konto'], $_REQUEST ['hk_konto'], $_REQUEST ['ihr_konto'], $_REQUEST ['p_von'], $_REQUEST ['p_bis'] );
			fehlermeldung_ausgeben ( "Profil geändert, bitte warten!!!!" );
			$profil_id = $_REQUEST ['profil_id'];
			weiterleiten_in_sec ( "?daten=weg&option=grunddaten_profil&profil_id=$profil_id", 2 );
		} else {
			fehlermeldung_ausgeben ( "Profil nicht geändert, Daten unvollständig!!!!" );
		}
		break;
	
	case "hga_einzeln" :
		$w = new weg ();
		$w->test ( $_SESSION ['hga_profil_id'] );
		break;
	
	case "hga_gesamt" :
		$w = new weg ();
		$w->hg_gesamtabrechnung ( $_SESSION ['hga_profil_id'] );
		break;
	
	case "hga_gesamt_pdf" :
		$w = new weg ();
		$w->hg_gesamtabrechnung_pdf ( $_SESSION ['hga_profil_id'] );
		break;
	
	case "ihr" :
		$w = new weg ();
		$w->ihr ( $_SESSION ['hga_profil_id'] );
		break;
	
	case "pdf_ihr" :
		$w = new weg ();
		$w->ihr_pdf ( $_SESSION ['hga_profil_id'] );
		break;
	
	case "assistent" :
		$w = new weg ();
		$w->assistent ();
		break;
	
	case "profil_send" :
		// print_req();
		if (! empty ( $_REQUEST ['profilbez'] ) && ! empty ( $_REQUEST ['objekt_id'] ) && ! empty ( $_REQUEST ['jahr'] ) && ! empty ( $_REQUEST ['geldkonto_id'] ) && ! empty ( $_REQUEST ['gk_id_ihr'] ) && ! empty ( $_REQUEST ['wp_id'] ) && ! empty ( $_REQUEST ['hg_konto'] ) && ! empty ( $_REQUEST ['hk_konto'] ) && ! empty ( $_REQUEST ['ihr_konto'] )) {
			$w = new weg ();
			$w->hga_profil_speichern ( $_REQUEST ['objekt_id'], $_REQUEST ['geldkonto_id'], $_REQUEST ['jahr'], $_REQUEST ['profilbez'], $_REQUEST ['gk_id_ihr'], $_REQUEST ['wp_id'], $_REQUEST ['hg_konto'], $_REQUEST ['hk_konto'], $_REQUEST ['ihr_konto'] );
			// weiterleiten('?daten=weg&option=assistent&schritt=2');
		} else {
			echo "Daten unvollständig";
		}
		break;
	
	/* Schritt 2 Ausgewählte Kontensummen zu HGA_ZEILEN */
	case "konto_hinzu" :
		// print_req();
		$w = new weg ();
		$w->form_konto_hinzu ( $_REQUEST ['konto'] );
		break;
	
	/* Konto aus einem Profil entfernen */
	case "konto_del" :
		// print_req();
		if (isset ( $_REQUEST ['profil_id'] ) && ! empty ( $_REQUEST ['profil_id'] ) && isset ( $_REQUEST ['konto'] ) && ! empty ( $_REQUEST ['konto'] )) {
			$weg = new weg ();
			$weg->konto_loeschen ( $_REQUEST ['profil_id'], $_REQUEST ['konto'] );
			fehlermeldung_ausgeben ( "Konto $konto wurde gelöscht!" );
		}
		weiterleiten_in_sec ( '?daten=weg&option=assistent&schritt=2', 2 );
		break;
	
	case "konto_zu_zeilen" :
		$w = new weg ();
		// print_r($_SESSION);
		// print_req();
		$w->hga_zeile_speichern ( $_SESSION [hga_profil_id], $_REQUEST [konto], $_REQUEST [art], $_REQUEST [textbez], $_REQUEST [genkey], $_REQUEST [summe], $_REQUEST [summe_hndl], 'Wirtschaftseinheit', $_REQUEST [wirt_id] );
		weiterleiten ( '?daten=weg&option=assistent&schritt=2' );
		break;
	
	case "hk_verbrauch_send" :
		// print_req();
		$w = new weg ();
		$anz_e = count ( $_REQUEST ['eig_id'] );
		$p_id = $_REQUEST ['p_id'];
		
		for($a = 0; $a < $anz_e; $a ++) {
			$eig_id = $_REQUEST [eig_id] [$a];
			$betrag = $_REQUEST [hk_verbrauch] [$a];
			$w->hk_verbrauch_eintragen ( $p_id, $eig_id, $betrag );
		}
		weiterleiten ( 'index.php?daten=weg&option=hk_verbrauch_tab' );
		break;
	
	case "hk_verbrauch_tab" :
		$w = new weg ();
		if ($_SESSION ['hga_profil_id']) {
			$w->tab_hk_verbrauch ( $_SESSION ['hga_profil_id'] );
		} else {
			echo "Hausgeldabrechnungsprofil wählen!";
		}
		break;
	
	case "kontostand_erfassen" :
		$w = new weg ();
		$w->form_kontostand_erfassen ();
		break;
	
	case "kto_stand_send" :
		if (! empty ( $_REQUEST ['datum'] ) && ! empty ( $_REQUEST ['betrag'] ) && ! empty ( $_SESSION ['geldkonto_id'] )) {
			$w = new weg ();
			$datum = date_german2mysql ( $_REQUEST ['datum'] );
			$gk_id = $_SESSION ['geldkonto_id'];
			$betrag = $_REQUEST ['betrag'];
			if ($w->kontostand_speichern ( $gk_id, $datum, $betrag )) {
				echo "Kontostand eingegeben!";
				weiterleiten_in_sec ( 'index.php?daten=weg&option=kontostaende', 3 );
			}
		} else {
			echo "Daten unvollständig eingegeben";
		}
		break;
	
	case "kontostaende" :
		$w = new weg ();
		$w->kontostand_anzeigen ( $_SESSION ['geldkonto_id'] );
		break;
	
	case "serienbrief" :
		if (! isset ( $_SESSION ['objekt_id'] ) && ! empty ( $_SESSION ['objekt_id'] )) {
			$weg = new weg ();
			$weg->liste_weg_objekte ( '?daten=weg&option=serienbrief' );
		} else {
			$weg = new weg ();
			$weg->form_eigentuemer_checkliste ( $_SESSION ['objekt_id'] );
		}
		break;
	
	case "serien_brief_vorlagenwahl" :
		// p($_POST);
		// print_req();
		if (isset ( $_REQUEST ['delete'] )) {
			unset ( $_SESSION ['eig_ids'] );
			$_SESSION ['eig_ids'] = array ();
			echo "Alle gelöscht!";
			break;
			// weiterleiten_in_sec("?daten=weg&option=serienbrief", 2);
		}
		if (! isset ( $_SESSION ['eig_ids'] )) {
			$_SESSION ['eig_ids'] = array ();
		}
		if (isset ( $_POST ['eig_ids'] ) && is_array ( $_POST ['eig_ids'] )) {
			// p($_POST['eig_ids']);
			$_SESSION ['eig_ids'] = array_merge ( $_SESSION ['eig_ids'], $_POST ['eig_ids'] );
			$_SESSION ['eig_ids'] = array_unique ( $_SESSION ['eig_ids'] );
			// p($_SESSION);
			$s = new serienbrief ();
			if (isset ( $_REQUEST ['kat'] )) {
				$s->vorlage_waehlen ( 'Eigentuemer', $_REQUEST ['kat'] );
			} else {
				$s->vorlage_waehlen ( 'Eigentuemer' );
			}
		} else {
			fehlermeldung_ausgeben ( "Bitte Eigentümer aus Liste wählen!" );
		}
		break;
	
	case "serienbrief_pdf" :
		// p($_SESSION);
		// unset($_SESSION['eig_ids']);
		print_req ();
		$bpdf = new b_pdf ();
		$s = new serienbrief ();
		$s->erstelle_brief_vorlage ( $_REQUEST ['vorlagen_dat'], 'Eigentuemer', $_SESSION ['eig_ids'], $option = '0' );
		// $bpdf->form_mieter2sess();
		break;
	
	case "hausgeld_zahlungen" :
		if (isset ( $_SESSION ['objekt_id'] ) && ! empty ( $_SESSION ['objekt_id'] )) {
			$weg = new weg ();
			$weg->form_hausgeldzahlungen ( $_SESSION ['objekt_id'] );
		} else {
			fehlermeldung_ausgeben ( "Objekt wählen!!!" );
		}
		break;
	
	case "hausgeld_zahlungen_xls" :
		if (isset ( $_SESSION ['objekt_id'] ) && ! empty ( $_SESSION ['objekt_id'] )) {
			$weg = new weg ();
			$weg->form_hausgeldzahlungen_xls ( $_SESSION ['objekt_id'] );
		} else {
			fehlermeldung_ausgeben ( "Objekt wählen!!!" );
		}
		break;
	
	case "autokorrkto" :
		// print_req();
		$profil_id = $_POST ['profil_id'];
		$konto = $_POST ['konto'];
		$betrag = $_POST ['betrag'];
		$weg = new weg ();
		$weg->autokorr_hga ( $profil_id, $konto, $betrag );
		// die();
		weiterleiten_in_sec ( "?daten=weg&option=assistent&schritt=2&profil_id=$profil_id", 1 );
		break;
	
	/*
	 * case "ihr_iii":
	 * $w = new weg();
	 * $w->ihr_iii($p_id);
	 * break;
	 */
	
	case "stammdaten_weg" :
		if (! isset ( $_SESSION ['objekt_id'] )) {
			fehlermeldung_ausgeben ( "Objekt wählen!!!" );
		} else {
			$weg = new weg ();
			$weg->stammdaten_weg ( $_SESSION ['objekt_id'] );
		}
		break;
	
	case "pdf_et_liste_alle_kurz" :
		if (! isset ( $_SESSION ['objekt_id'] )) {
			fehlermeldung_ausgeben ( "Objekt wählen!!!" );
		} else {
			$weg = new weg ();
			$weg->pdf_et_liste_alle_kurz ( $_SESSION ['objekt_id'] );
		}
		break;
	
	case "pdf_hausgelder" :
		$w = new weg ();
		if (! isset ( $_REQUEST ['jahr'] )) {
			$jahr = date ( "Y" );
		} else {
			$jahr = $_REQUEST ['jahr'];
		}
		if (! isset ( $_SESSION ['objekt_id'] )) {
			die ( fehlermeldung_ausgeben ( "Objekt wählen" ) );
		}
		$w->pdf_hausgelder ( $_SESSION ['objekt_id'], $jahr );
		break;
	
	case "wp_zeile_del" :
		if (isset ( $_REQUEST ['dat'] ) && ! empty ( $_REQUEST ['dat'] )) {
			$weg = new weg ();
			if ($weg->wp_zeile_loeschen ( $_REQUEST ['dat'] ) == true) {
				// print_r($_SESSION);
				$wp_id = $_SESSION ['wp_id'];
				weiterleiten_in_sec ( "?daten=weg&option=wp_zeile_neu&wp_id=$wp_id", 0 );
			}
		} else {
			fehlermeldung_ausgeben ( "Zeile aus dem WP wählen!!!" );
		}
		break;
} // end switch

?>