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
 * @filesource   $HeadURL: http://192.168.2.52/svn/berlussimo_1/tags/02.11.2010 - Downloadversion 0.27/options/modules/benutzer.php $
 * @version      $Revision: 6 $
 * @modifiedby   $LastChangedBy: sivac $
 * @lastmodified $Date: 2010-09-21 10:34:50 +0200 (Di, 21 Sep 2010) $
 * 
 */
if (file_exists ( "classes/class_werkzeug.php" )) {
	include_once ("classes/class_werkzeug.php");
}

/* Allgemeine Funktionsdatei laden */
include_once ("includes/allgemeine_funktionen.php");

/* �berpr�fen ob Benutzer Zugriff auf das Modul hat */
if (! check_user_mod ( $_SESSION ['benutzer_id'], 'benutzer' )) {
	echo '<script type="text/javascript">';
	echo "alert('Keine Berechtigung')";
	echo '</script>';
	die ();
}

/* Klasse "formular" f�r Formularerstellung laden */
include_once ("classes/class_formular.php");

/* Modulabh�ngige Dateien d.h. Links und eigene Klasse */
include_once ("options/links/links.benutzer.php");
include_once ("classes/class_benutzer.php");

if (isset ( $_REQUEST ["option"] ) && ! empty ( $_REQUEST ["option"] )) {
	$option = $_REQUEST ["option"];
} else {
	$option = 'default';
}

/* Optionsschalter */
switch ($option) {
	
	default :
		$b = new benutzer ();
		$b->benutzer_anzeigen ();
		// $b->benutzer_anzeigen('benutzername', 'SORT_ASC');
		break;
	
	case "berechtigungen" :
		if (! empty ( $_REQUEST ['b_id'] )) {
			$b_id = $_REQUEST ['b_id'];
			$b = new benutzer ();
			$b->berechtigungen ( $b_id );
		} else {
			echo "Benutzer/Mitarbeiter w�hlen";
		}
		break;
	
	case "aendern" :
		if (! empty ( $_REQUEST ['b_id'] )) {
			$b_id = $_REQUEST ['b_id'];
			$b = new benutzer ();
			$b->form_benutzer_aendern ( $b_id );
		} else {
			echo "Benutzer/Mitarbeiter w�hlen";
		}
		break;
	
	case "benutzer_aendern_send" :
		// print_req();
		if (isset ( $_REQUEST ['b_id'] ) && ! empty ( $_REQUEST ['b_id'] )) {
			$benutzer_name = $_REQUEST ['benutzername'];
			$b_id = $_REQUEST ['b_id'];
			$passwort = $_REQUEST ['passwort'];
			$partner_id = $_REQUEST ['partner_id'];
			$gewerk_id = $_REQUEST ['gewerk_id'];
			$geburtstag = $_REQUEST ['geburtstag'];
			$eintritt = $_REQUEST ['eintritt'];
			$austritt = $_REQUEST ['austritt'];
			
			$urlaub = $_REQUEST ['urlaub'];
			$stunden_pw = $_REQUEST ['stunden_pw'];
			$stundensatz = $_REQUEST ['stundensatz'];
			$be = new benutzer ();
			$be->benutzer_aenderungen_speichern ( $b_id, $benutzer_name, $passwort, $partner_id, $stundensatz, $geburtstag, $gewerk_id, $eintritt, $austritt, $urlaub, $stunden_pw );
			fehlermeldung_ausgeben ( "Bitte warten..." );
			weiterleiten_in_sec ( "?daten=benutzer&option=aendern&b_id=$b_id", 2 );
		} else {
			fehlermeldung_ausgeben ( "Benutzerdaten unvollst�ndig" );
		}
		break;
	
	case "zugriff_send" :
		if (! empty ( $_POST ['b_id'] ) && ! empty ( $_POST ['modul_name'] )) {
			$b_id = $_POST ['b_id'];
			$modul_name = $_POST ['modul_name'];
			
			$b = new benutzer ();
			if (isset ( $_POST ['submit_ja'] )) {
				$b->berechtigungen_speichern ( $b_id, $modul_name );
			}
			if (isset ( $_POST ['submit_no'] )) {
				$b->berechtigungen_entziehen ( $b_id, $modul_name );
			}
			weiterleiten ( "index.php?daten=benutzer&option=berechtigungen&b_id=$b_id" );
		}
		break;
	
	case "neuer_benutzer" :
		$b = new benutzer ();
		$b->form_neuer_benutzer ();
		break;
	
	case "benutzer_send" :
		if ($_POST) {
			if (! empty ( $_POST ['benutzername'] ) && ! empty ( $_POST ['passwort'] ) && ! empty ( $_POST ['partner_id'] ) && ! empty ( $_POST ['geburtstag'] ) && ! empty ( $_POST ['eintritt'] ) && ! empty ( $_POST ['urlaub'] ) && ! empty ( $_POST ['stunden_pw'] )) {
				// echo '<pre>';
				// print_r($_POST);
				$b = new benutzer ();
				$benutzername = $_POST ['benutzername'];
				$passwort = $_POST ['passwort'];
				$partner_id = $_POST ['partner_id'];
				$stundensatz = $_POST ['stundensatz'];
				$geb_dat = $_POST ['geburtstag'];
				$gewerk_id = $_POST ['gewerk_id'];
				$eintritt = $_POST ['eintritt'];
				$austritt = $_POST ['austritt'];
				$urlaub = $_POST ['urlaub'];
				$stunden_pw = $_POST ['stunden_pw'];
				if (check_datum ( $geb_dat ) && check_datum ( $eintritt )) {
					$geb_dat = date_german2mysql ( $geb_dat );
					$eintritt = date_german2mysql ( $eintritt );
					if (! empty ( $austritt )) {
						$austritt = date_german2mysql ( $austritt );
					}
					$stundensatz = nummer_komma2punkt ( $stundensatz );
					$benutzer_id = $b->benutzer_speichern ( $benutzername, $passwort, $partner_id, $stundensatz, $geb_dat, $gewerk_id, $eintritt, $austritt, $urlaub, $stunden_pw );
					weiterleiten ( "?daten=benutzer&option=berechtigungen&b_id=$benutzer_id" );
				} else {
					die ( 'Datumsangaben falsch' );
				}
			} else {
				die ( 'Fehler xg763663 - Daten unvollst�ndig' );
			}
		} else {
			die ( 'Fehler xg763664' );
		}
		break;
	
	case "werkzeuge" :
		ini_set ( 'display_errors', 'On' );
		error_reporting ( E_ALL | E_STRICT );
		$w = new werkzeug ();
		// $w->form_werkzeug_hizu();
		// echo "<hr>";
		$w->werkzeugliste ();
		break;
	
	case "werkzeuge_mitarbeiter" :
		ini_set ( 'display_errors', 'On' );
		error_reporting ( E_ALL | E_STRICT );
		$w = new werkzeug ();
		if (! empty ( $_REQUEST ['b_id'] )) {
			$b_id = $_REQUEST ['b_id'];
			$w->werkzeugliste ( $b_id );
		} else {
			echo "Mitarbeiter w�hlen!";
			$w->werkzeugliste ();
		}
		
		// $w->werkzeuge_mitarbeiter();
		break;
	
	case "werkzeug_rueckgabe_alle_pdf" :
		if (! empty ( $_REQUEST ['b_id'] )) {
			$b_id = $_REQUEST ['b_id'];
			$w = new werkzeug ();
			$w->pdf_rueckgabeschein_alle ( $b_id, 'Werkzeugr�ckgabeschein ' );
		} else {
			fehlermeldung_ausgeben ( 'Mitarbeiter w�hlen' );
		}
		break;
	
	case "werkzeug_ausgabe_alle_pdf" :
		if (! empty ( $_REQUEST ['b_id'] )) {
			$b_id = $_REQUEST ['b_id'];
			$w = new werkzeug ();
			$w->pdf_rueckgabeschein_alle ( $b_id, 'Werkzeugausgabegabeschein ' );
		} else {
			fehlermeldung_ausgeben ( 'Mitarbeiter w�hlen' );
		}
		break;
	
	case "werkzeug_rueckgabe_alle" :
		ini_set ( 'display_errors', 'On' );
		error_reporting ( E_ALL | E_STRICT );
		if (! empty ( $_REQUEST ['b_id'] )) {
			$b_id = $_REQUEST ['b_id'];
			$w = new werkzeug ();
			// $w->pdf_rueckgabeschein_alle($b_id);
			$w->werkzeug_rueckgabe_alle ( $b_id ); // �nderung der DB
		} else {
			fehlermeldung_ausgeben ( 'Mitarbeiter w�hlen' );
		}
		break;
	
	case "werkzeug_zuweisen" :
		ini_set ( 'display_errors', 'On' );
		error_reporting ( E_ALL | E_STRICT );
		if (! empty ( $_REQUEST ['w_id'] )) {
			$w_id = $_REQUEST ['w_id'];
			$w = new werkzeug ();
			// $w->pdf_rueckgabeschein_alle($b_id);
			$w->form_werkzeug_zuweisen ( $w_id ); // �nderung der DB
		} else {
			fehlermeldung_ausgeben ( 'Werkzeug w�hlen' );
		}
		break;
	
	case "werkzeug_zuweisen_snd" :
		// print_r($_REQUEST);
		if (! empty ( $_REQUEST ['w_id'] ) && ! empty ( $_REQUEST ['b_id'] )) {
			$w_id = $_REQUEST ['w_id'];
			$b_id = $_REQUEST ['b_id'];
			$w = new werkzeug ();
			$w->werkzeug_zuweisen ( $b_id, $w_id );
			echo "Zugewiesen";
			weiterleiten_in_sec ( "?daten=benutzer&option=werkzeuge", 1 );
		} else {
			fehlermeldung_ausgeben ( "Mitarbeiter und Werkzeug w�hlen!" );
		}
		break;
	
	case "werkzeug_rueckgabe" :
		if (! empty ( $_REQUEST ['w_id'] ) && ! empty ( $_REQUEST ['b_id'] )) {
			$w_id = $_REQUEST ['w_id'];
			$b_id = $_REQUEST ['b_id'];
			$w = new werkzeug ();
			$w->pdf_werkzeug_rueckgabe_einzel ( $b_id, $w_id );
		}
		break;
	
	case "werkzeug_raus" :
		if (! empty ( $_REQUEST ['w_id'] )) {
			$w_id = $_REQUEST ['w_id'];
			$w = new werkzeug ();
			$w->werkzeug_loeschen ( $w_id );
		}
		break;
	
	case "werkzeugliste_nach_mitarbeiter" :
		$w = new werkzeug ();
		$w->werkzeugliste_nach_mitarbeiter ();
		break;
} // END SWITCH

?>
