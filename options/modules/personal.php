<?php
/*
 * Created on / Erstellt am : 18.02.2014
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

/* �berpr�fen ob Benutzer Zugriff auf das Modul hat */
if (! check_user_mod ( $_SESSION ['benutzer_id'], 'personal' )) {
	echo '<script type="text/javascript">';
	echo "alert('Keine Berechtigung')";
	echo '</script>';
	die ();
}

/* Modulabh�ngige Dateien d.h. Links und eigene Klasse */
include_once ("options/links/links.personal.php");
include_once ("classes/class_personal.php");

if (isset ( $_REQUEST ["option"] )) {
	$option = $_REQUEST ["option"];
} else {
	$option = 'default';
}
/* Optionsschalter */
switch ($option) {
	
	default :
		echo "WEITERE WAHL TREFFEN!";
		break;
	
	case "lohn_gehalt_sepa" :
		$pe = new personal ();
		$sep = new sepa ();
		$sep->sepa_sammler_anzeigen ( $_SESSION ['geldkonto_id'], 'LOHN' );
		$pe->form_lohn_gehalt_sepa ();
		break;
	
	case "sepa_sammler_hinzu" :
		// print_req();
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
		if ($betrag < 0) {
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
			if ($kat == 'LOHN') {
				weiterleiten ( "?daten=personal&option=lohn_gehalt_sepa" );
			}
			if ($kat == 'KK') {
				weiterleiten ( "?daten=personal&option=kk" );
			}
			if ($kat == 'STEUERN') {
				weiterleiten ( "?daten=personal&option=steuern" );
			}
		}
		break;
	
	case "kk" :
		$pe = new personal ();
		$sep = new sepa ();
		$sep->sepa_sammler_anzeigen ( $_SESSION ['geldkonto_id'], 'KK' );
		$pe->form_krankenkassen ();
		break;
	
	case "steuern" :
		$pe = new personal ();
		$sep = new sepa ();
		$sep->sepa_sammler_anzeigen ( $_SESSION ['geldkonto_id'], 'STEUERN' );
		$pe->form_finanzamt ();
		break;
}

?>
