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
 * @filesource   $HeadURL: http://192.168.2.52/svn/berlussimo_1/tags/02.11.2010 - Downloadversion 0.27/index.php $
 * @version      $Revision: 15 $
 * @modifiedby   $LastChangedBy: sivac $
 * @lastmodified $Date: 2011-07-07 10:41:33 +0200 (Do, 07 Jul 2011) $
 *
 */
// ini_set('display_errors','On');
// error_reporting(E_ALL|E_STRICT);
// ini_set('allow_url_include', 1);
// ini_set("auto_detect_line_endings", true);

define ( "BERLUS_PATH", __DIR__ );

/* neu */
/* KONFIG */
include_once (BERLUS_PATH . "/classes/config.inc.php");
include_once (BERLUS_PATH . "/includes/config.php");
/* KLASSEN */
require __DIR__ . '/vendor/autoload.php';
include_once (BERLUS_PATH . "/classes/class_bpdf.php");

include_once (BERLUS_PATH . "/classes/class_person.php");

include_once (BERLUS_PATH . "/classes/class_details.php");

include_once (BERLUS_PATH . "/classes/class_weg.php");
include_once (BERLUS_PATH . "/classes/class_sepa.php");
include_once (BERLUS_PATH . "/classes/berlussimo_class.php");
include_once (BERLUS_PATH . "/includes/allgemeine_funktionen.php");

include_once (BERLUS_PATH . "/classes/class_sepa.php");

include_once (BERLUS_PATH . "/classes/class_buchen.php");
include_once (BERLUS_PATH . "/classes/class_mietvertrag.php");
include_once (BERLUS_PATH . "/classes/mietzeit_class.php");
include_once (BERLUS_PATH . "/classes/mietkonto_class.php");
include_once (BERLUS_PATH . "/classes/class_formular.php");
include_once (BERLUS_PATH . "/classes/class_benutzer.php");
include_once (BERLUS_PATH . "/classes/class_mietentwicklung.php");

include_once (BERLUS_PATH . "/classes/class_ueberweisung.php");

include_once (BERLUS_PATH . "/classes/class_import.php");
include_once (BERLUS_PATH . "/classes/class_todo.php");
include_once (BERLUS_PATH . "/classes/class_wartungen.php");
include_once (BERLUS_PATH . "/classes/class_serienbrief.php");

include_once (BERLUS_PATH . "/classes/class_stammdaten.php");
include_once (BERLUS_PATH . "/classes/class_thumbs.php");

include_once (BERLUS_PATH . "/classes/phplot.php");
include_once (BERLUS_PATH . "/classes/class_kundenweb.php");

/* Alt */
/*
 * include_once("classes/config.inc.php");
 * include_once("includes/config.php");
 * include_once("classes/class_formular.php");
 * include_once("classes/class_benutzer.php");
 */
ob_clean ();
set_time_limit ( 120 );
session_start ();
ob_start ();
// Ausgabepuffer Starten
// session_start();

echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">';
echo "<head>";
// echo "<script src=\"https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js\"></script>";
echo "<link href=\"css/lightbox.css\" rel=\"stylesheet\">";
// echo "<script type=\"text/javascript\" src=\"js/lightbox.js\"></script>";

echo "<script type=\"text/javascript\" src=\"ajax/ajax.js\"></script>\n";
echo "<script type=\"text/javascript\" src=\"ajax/dd_kostenkonto.js\"></script>\n";
echo "<script type=\"text/javascript\" src=\"js/javascript.js\"></script>\n";
echo "<script type=\"text/javascript\" src=\"js/sorttable.js\"></script>\n";
echo "<link rel=\"stylesheet\" type=\"text/css\"  href=\"css/uebersicht.css\" media=\"screen\">\n";
echo "<link rel=\"stylesheet\" type=\"text/css\"  href=\"css/berlussimo.css\"  media=\"screen\">\n";
echo "<link href=\"css/demo.css\"       rel=\"stylesheet\" type=\"text/css\" /  media=\"screen\">";

// echo "<script src=\"js/lightbox-plus-jquery.min.js\"></script>";

echo "<meta http-equiv=\"content-type\" content=\"text/html; charset=UTF-8\">\n";
// echo "<meta content=\"text/html; charset=ISO-8859-1\" http-equiv=\"content-type\">";
echo "</head>";

if (isset ( $_REQUEST ['logout'] )) {
	echo "AUSGELOGGT!<br>";
	$_SESSION = array ();
	weiterleiten ( 'index.php' );
}

if (empty ( $_SESSION ['autorisiert'] )) {
	
	if (empty ( $_REQUEST ['send_login'] )) {
		
		$f = new formular ();
		$f->erstelle_formular ( 'Berlussimo - Bitte anmelden', '' );
		$f->fieldset ( 'Benutzernamen und Passwort eingeben', 'bin' );
		$f->text_feld ( 'Benutzername', 'benutzername', '', 30, 'benutzername', '' );
		$f->passwort_feld ( 'Password', 'passwort', '', 30, 'passwort', '' );
		$f->send_button ( 'send_login', 'Anmelden' );
		$f->fieldset_ende ();
		$f->ende_formular ();
	} else {
		$usercheck = check_user ( $_REQUEST ['benutzername'], $_REQUEST ['passwort'] );
		if ($usercheck) {
			$_SESSION ['username'] = $usercheck;
			$benutzer_id = get_benutzer_id ( $_SESSION ['username'] );
			$_SESSION ['benutzer_id'] = $benutzer_id;
			$_SESSION ['autorisiert'] = '1';
		} else {
			fehlermeldung_ausgeben ( "Anmeldung gescheitert!" );
			weiterleiten_in_sec ( 'index.php', 2 );
		}
	}
}

if (isset ( $_SESSION ['autorisiert'] )) {
	
	include_once ("classes/mietkonto_class.php");
	include_once ("includes/config.php");
	// echo "<p align=center style=\"filter:FlipH();\">BERLUSSIMO</p>\n";
	echo "<body>\n";
	// echo "<hr><a href=\"?formular=objekte\"><b>Objektverwaltung&nbsp;</b></a>\n ";
	// echo "<a href =\"?daten=objekte_raus\"><b>Objektübersicht&nbsp;</b></a>\n<hr>";
	$form = new mietkonto ();
	$benutzer = $_SESSION ['username'];
	// echo "<div class=\"willkommen\">Wilkommen $_SESSION[username]</div>";
	$form->erstelle_formular ( "Benutzer: $benutzer", NULL );
	include_once ('classes/class_partners.php');
	$p = new partners ();

    if (isset($_REQUEST ['partner_id'])) {
        $_SESSION ['partner_id'] = $_REQUEST ['partner_id'];
    }
	if (isset ( $_SESSION ['partner_id'] )) {
		$p->get_partner_name ( $_SESSION ['partner_id'] );
	}
	
	if (check_user_links ( $_SESSION ['benutzer_id'], 'rechnungen' )) {
		if (isset ( $_SESSION ['partner_id'] )) {
			$link_partner = "<a href=\"?daten=rechnungen&option=partner_wechseln\">Partner wechseln: <b>$p->partner_name</b></a>&nbsp;&nbsp;";
		} else {
			$link_partner = "<a href=\"?daten=rechnungen&option=partner_wechseln\">Partner wählen</b></a>&nbsp;&nbsp;";
		}
	} else {
		$link_partner = '';
	}
	
	if (isset ( $_SESSION ['geldkonto_id'] )) {
		$g = new geldkonto_info ();
		$g->geld_konto_details ( $_SESSION ['geldkonto_id'] );
	}
	
	if (check_user_links ( $_SESSION ['benutzer_id'], 'buchen' )) {
		if (isset ( $_SESSION ['geldkonto_id'] )) {
			$link_geldkonto = "<a href=\"?daten=buchen&option=geldkonto_aendern\">Geldkonto: $g->geldkonto_bezeichnung_kurz</a>&nbsp;&nbsp;";
		} else {
			$link_geldkonto = "<a href=\"?daten=buchen&option=geldkonto_aendern\">Geldkonto wählen</a>&nbsp;&nbsp;";
		}
	} else {
		$link_geldkonto = '';
	}
	
	echo "<div><center>$link_partner &nbsp; $link_geldkonto</center></div>";
	// echo "<div id=\"berlussimo_logo_top\"><img src=\"css/berlussimo_top_logo.png\"/><br>$link_partner $link_geldkonto</div>";
	// echo "<div id=\"berlussimo_logo_top\">$link_partner <br>$link_geldkonto</div>";
	include ("options/links/links.statisch.php");
	
	include_options ();
	
	// todo_liste($_SESSION[benutzer_id]);
}
echo "<div  id=\"aus\"><center><b>Berlussimo</b> wurde von der <a target=\"_new\"  href=\"http://www.berlus.de\">Berlus GmbH</a> - Hausverwaltung zur Verfügung gestellt.</center></div>";

echo "</body></html>";
function include_options() {
	$optdir = dir ( "options/case" );
	while ( $func = $optdir->read () ) {
		if (substr ( $func, 0, 5 ) == "case.") {
			include ($optdir->path . "/" . $func);
		}
	}
	closedir ( $optdir->handle );
}
function check_user($benutzername, $passwort) {
	// $benutzername = mysql_real_escape_string($benutzername);
	// $passwort = mysql_real_escape_string($passwort);
	include_once ("includes/config.php");
	// $passwort = md5($passwort);
	/* ' or 1=1-- */
	$db_abfrage = "SELECT benutzername FROM BENUTZER WHERE benutzername='$benutzername' && passwort='$passwort' ";
	// $db_abfrage1 = mysql_escape_string($db_abfrage);
	// echo $db_abfrage; die();
	$resultat = mysql_query ( stripslashes ( mysql_escape_string ( $db_abfrage ) ) ) or die ( mysql_error () );
	// $resultat = mysql_query($db_abfrage) or die(mysql_error());
	// $resultat = mysql_query($db_abfrage) or die(mysql_error());
	// mysql_real_escape_string
	
	$numrows = mysql_numrows ( $resultat );
	if ($numrows < 1) {
		return false;
	} else {
		while ( list ( $benutzername ) = mysql_fetch_row ( $resultat ) )
			return $benutzername;
	}
}
function get_benutzer_id($benutzername) {
	$result = mysql_query ( "SELECT benutzer_id FROM BENUTZER WHERE benutzername='$benutzername' LIMIT 0,1" );
	
	$row = mysql_fetch_assoc ( $result );
	return $row ['benutzer_id'];
}
function compressed_output() {
	$encoding = getEnv ( "HTTP_ACCEPT_ENCODING" );
	$useragent = getEnv ( "HTTP_USER_AGENT" );
	$method = trim ( getEnv ( "REQUEST_METHOD" ) );
	$msie = preg_match ( "=msie=i", $useragent );
	$gzip = preg_match ( "=gzip=i", $encoding );
	
	if ($gzip && ($method != "POST" or ! $msie)) {
		ob_start ( "ob_gzhandler" );
	} else {
		ob_start ();
	}
}

// $output = ob_get_clean();
// echo $output;
?>