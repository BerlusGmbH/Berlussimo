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
 * @filesource   $HeadURL: http://192.168.2.52/svn/berlussimo_1/tags/02.11.2010 - Downloadversion 0.27/options/modules/lager.php $
 * @version      $Revision: 15 $
 * @modifiedby   $LastChangedBy: sivac $
 * @lastmodified $Date: 2011-07-07 10:41:33 +0200 (Do, 07 Jul 2011) $
 * 
 */
include_once ("includes/allgemeine_funktionen.php");
include_once ('classes/class_lager.php');

/* überprüfen ob Benutzer Zugriff auf das Modul hat */
if (! check_user_mod ( $_SESSION ['benutzer_id'], 'lager' )) {
	echo '<script type="text/javascript">';
	echo "alert('Keine Berechtigung')";
	echo '</script>';
	die ();
}

//include_once ("options/links/links.lager.php");
include_once ("classes/berlussimo_class.php");

$option = $_REQUEST ["option"];
$lager_info = new lager ();

switch ($option) {
	
	default :
		unset ( $_SESSION ['objekt_id'] );
		break;
	
	case "lagerbestand" :
		unset ( $_SESSION ['objekt_id'] );
		$link = '?daten=lager&option=lagerbestand';
		$lager_info->lager_auswahl_liste ( $link );
		$form = new mietkonto ();
		$form->erstelle_formular ( "Lagerbestand ->", NULL );
		$lager_info->lagerbestand_anzeigen ();
		$form->ende_formular ();
		break;
	
	case "lagerbestand_bis_form" :
		$l = new lager_v ();
		$link = '?daten=lager&option=lagerbestand_bis_form';
		$lager_info->lager_auswahl_liste ( $link );
		if (empty ( $_SESSION ['lager_id'] )) {
			die ( "Lager wählen" );
		} else {
			$f = new formular ();
			$lager_bez = $lager_info->lager_bezeichnung ( $_SESSION ['lager_id'] );
			$f->erstelle_formular ( "Lagerbestand vom $lager_bez bis zum... anzeigen", '' );
			$f->datum_feld ( 'Datum bis', 'datum', '', 'datum' );
			$f->check_box_js ( 'pdf_check', '', 'PDF-Ausgabe', '', 'checked' );
			$f->hidden_feld ( 'option', 'lagerbestand_bis' );
			$f->send_button ( 'send', 'Lagerbestand anzeigen' );
			$f->ende_formular ();
		}
		break;
	
	case "lagerbestand_bis" :
		unset ( $_SESSION ['objekt_id'] );
		$link = '?daten=lager&option=lagerbestand';
		$lager_info->lager_auswahl_liste ( $link );
		$form = new mietkonto ();
		$form->erstelle_formular ( "Lagerbestand ->", NULL );
		if (! empty ( $_REQUEST ['datum'] )) {
			/* Class_lager) */
			$l = new lager_v ();
			if (! isset ( $_REQUEST ['pdf_check'] )) {
				$l->lagerbestand_anzeigen_bis ( $_REQUEST ['datum'] );
			} else {
				$l->lagerbestand_anzeigen_bis_pdf ( $_REQUEST ['datum'] );
			}
		} else {
			fehlermeldung_ausgeben ( "Datum eingeben" );
		}
		$form->ende_formular ();
		break;
	
	case "ra" :
		$link = '?daten=lager&option=ra';
		$lager_info->lager_auswahl_liste ( $link );
		if (! empty ( $_SESSION ['lager_id'] )) {
			$monat = $_REQUEST ['monat'];
			$jahr = $_REQUEST ['jahr'];
			if (empty ( $monat )) {
				$monat = date ( "m" );
			}
			if (empty ( $jahr )) {
				$jahr = date ( "Y" );
			}
			$r = new rechnung ();
			$lager_id = $_SESSION ['lager_id'];
			$r->rechnungsausgangsbuch ( 'Lager', $lager_id, $monat, $jahr, 'Rechnung' );
		}
		break;
	
	case "re" :
		$link = '?daten=lager&option=re';
		$lager_info->lager_auswahl_liste ( $link );
		if (! empty ( $_SESSION ['lager_id'] )) {
			$monat = $_REQUEST ['monat'];
			$jahr = $_REQUEST ['jahr'];
			if (empty ( $monat )) {
				$monat = date ( "m" );
			}
			if (empty ( $jahr )) {
				$jahr = date ( "Y" );
			}
			$r = new rechnung ();
			$lager_id = $_SESSION ['lager_id'];
			$r->rechnungseingangsbuch ( 'Lager', $lager_id, $monat, $jahr, 'Rechnung' );
		}
		break;
	
	case "artikelsuche" :
		$l = new lager ();
		$l->artikel_suche_einkauf_form ();
		break;
	
	case "artikel_suche" :
		if (! empty ( $_REQUEST ['artikel_nr'] )) {
			$artikel_nr = $_REQUEST ['artikel_nr'];
			$l = new lager ();
			$l->artikel_suche_einkauf ( $artikel_nr, 'Lager', $_SESSION ['lager_id'] );
		}
		break;
	
	case "lieferschein_erfassen" :
		$l = new lager_v ();
		$l->form_lieferschein_erfassen ();
		break;
	
	case "lieferschein_send" :
		$l = new lager_v ();
		// $l->form_lieferschein_erfassen();
		print_req ();
		if (! empty ( $_REQUEST ['lieferant_id'] ) && ! empty ( $_REQUEST ['empfaenger_id'] ) && ! empty ( $_REQUEST ['l_nr'] ) && ! empty ( $_REQUEST ['l_datum'] )) {
			$l->lieferschein_speichern ( 'Partner', $_REQUEST ['lieferant_id'], 'Partner', $_REQUEST ['empfaenger_id'], $_REQUEST ['l_datum'], $_REQUEST ['l_nr'] );
		}
		break;
	
	case "rep_kontierungsdatum" :
		$l = new lager_v ();
		$l->reparatur_kontierungsdatum ();
		break;
} // end switch

