<?php
/*
 * Created on / Erstellt am : 05.11.2015
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
 *         
 */

/* Allgemeine Funktionsdatei laden */
include_once ("includes/allgemeine_funktionen.php");

/* überprüfen ob Benutzer Zugriff auf das Modul hat */
if (! check_user_mod ( $_SESSION ['benutzer_id'], 'kundenweb' )) {
	echo '<script type="text/javascript">';
	echo "alert('Keine Berechtigung')";
	echo '</script>';
	die ();
}

/* Modulabhängige Dateien d.h. Links und eigene Klasse */
//include_once ("options/links/links.kundenweb.php");
include_once ("classes/class_kundenweb.php");
if (isset ( $_REQUEST ["option"] )) {
	$option = $_REQUEST ["option"];
} else {
	$option = '';
}
/* Optionsschalter */
switch ($option) {
	
	default:
	/*Liste der Kunden mit Zugriff auf das Kundenweb*/
	$k = new kundenweb ();
		$k->kundendaten_anzeigen_alle ();
		break;
	
	/* Berechtigungen eines Kunden anzeigen */
	case "berechtigung" :
		$kunden_id = $_REQUEST ['kunden_id'];
		$k = new kundenweb ();
		$k->kundendaten_anzeigen ( $kunden_id );
		break;
	
	/* Einzelne Berechtigung eines Kunden löschen */
	case "berechtigung_del" :
		$kunden_id = $_REQUEST ['kunden_id'];
		$ber_obj = $_REQUEST ['ber_obj'];
		$ber_id = $_REQUEST ['ber_id'];
		$k = new kundenweb ();
		if ($k->berechtigung_del ( $kunden_id, $ber_obj, $ber_id ) == true) {
			$k->kundendaten_anzeigen ( $kunden_id );
		}
		break;
	
	/* Neue Berechtigung speichern */
	case "ber_hinzu" :
		$ber_obj = $_REQUEST ['kostentraeger_typ'];
		$ber_id = $_REQUEST ['kostentraeger_id'];
		$person_id = $_REQUEST ['person_id'];
		$k = new kundenweb ();
		$k->berechtigung_speichern ( $person_id, $ber_obj, $ber_id );
		/* KundenID abfragen und die Berechtigungen anzeigen */
		$kunden_id = $k->get_kunden_id_of_person ( $person_id );
		$k->kundendaten_anzeigen ( $kunden_id );
		break;
	
	/* Formular um neue Kundenwebbenutzer anzulegen */
	case "neuer_benutzer" :
		$k = new kundenweb ();
		$k->form_neuer_benutzer ();
		break;
	
	case "benutzer_hinzu" :
		// print_req();
		$person_id = $_REQUEST ['person_id'];
		$partner_id = $_REQUEST ['partner_id'];
		$username = $_REQUEST ['username'];
		$passwd = $_REQUEST ['password'];
		$email = $_REQUEST ['email'];
		
		$k = new kundenweb ();
		$k->benutzer_speichern ( $person_id, $partner_id, $username, $passwd, $email );
		$k->kundendaten_anzeigen_alle ();
		break;
	
	/* Kunden und seine Berechtigungen deaktvieren */
	case "deaktivieren" :
		$kunden_id = $_REQUEST ['kunden_id'];
		$k = new kundenweb ();
		$k->kunden_deaktivieren ( $kunden_id );
		$k->kundendaten_anzeigen_alle ();
		break;
}

?>
		