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
 * @filesource   $HeadURL: http://192.168.2.52/svn/berlussimo_1/tags/02.11.2010 - Downloadversion 0.27/options/modules/buchen.php $
 * @version      $Revision: 15 $
 * @modifiedby   $LastChangedBy: sivac $
 * @lastmodified $Date: 2011-07-07 10:41:33 +0200 (Do, 07 Jul 2011) $
 * 
 */

/*
 * WICHTIGE INFOS
 * Tabelle benutzer erhält spalte Email
 *
 *
 */

/* Allgemeine Funktionsdatei laden */
include_once ("includes/allgemeine_funktionen.php");

/* überprüfen ob Benutzer Zugriff auf das Modul hat */
if (! isset ( $_SESSION ['benutzer_id'] ) or ! check_user_mod ( $_SESSION ['benutzer_id'], 'tickets' )) {
	echo '<script type="text/javascript">';
	echo "alert('Keine Berechtigung')";
	echo '</script>';
	die ();
}

/* Modulabhängige Dateien d.h. Links und eigene Klasse */
//include_once ("options/links/links.tickets.php");

$b = new benutzer ();
$b->get_benutzer_infos ( $_SESSION ['benutzer_id'] );
$_SESSION ['benutzer_email'] = $b->benutzer_email;

if (! empty ( $_REQUEST ["option"] )) {
	$option = $_REQUEST ["option"];
} else {
	$option = 'default';
}

$email = $_SESSION ['benutzer_email'];
$url = "http://192.168.2.16/ticket/api/tickets.api.php?option=find_my_user_id&email=$email";
$user_id = file ( $url );
// print_r($user_id);

if (empty ( $user_id ) or $user_id == '0') {
	die ( 'Konnte Sie als Benutzer im Ticketsystem nicht finden!!!!' );
} else {
	$_SESSION ['ticket_user_id'] = $user_id [0];
}

/* Optionsschalter */
switch ($option) {
	
	case "meine_tickets" :
		echo "MEINE TICKETS";
		$url = "http://192.168.2.16/ticket/api/tickets.api.php?option=meine_tickets&user_id=" . $_SESSION ['ticket_user_id'];
		$ant = file_get_contents ( $url );
		echo $ant;
		
		break;
	
	default :
		echo "TICKETS";
		echo '<pre>';
		print_r ( $_SESSION );
		break;
}

?>