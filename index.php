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
 * @contact         software(@)berlus.de
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

define("BERLUS_PATH", __DIR__);

/* neu */
/* KONFIG */
include_once(BERLUS_PATH . "/classes/config.inc.php");
include_once(BERLUS_PATH . "/includes/config.php");
/* KLASSEN */
require __DIR__ . '/vendor/autoload.php';
include_once(BERLUS_PATH . "/classes/class_bpdf.php");

include_once(BERLUS_PATH . "/classes/class_person.php");

include_once(BERLUS_PATH . "/classes/class_details.php");

include_once(BERLUS_PATH . "/classes/class_weg.php");
include_once(BERLUS_PATH . "/classes/class_sepa.php");
include_once(BERLUS_PATH . "/classes/berlussimo_class.php");
include_once(BERLUS_PATH . "/includes/allgemeine_funktionen.php");

include_once(BERLUS_PATH . "/classes/class_sepa.php");

include_once(BERLUS_PATH . "/classes/class_buchen.php");
include_once(BERLUS_PATH . "/classes/class_mietvertrag.php");
include_once(BERLUS_PATH . "/classes/mietzeit_class.php");
include_once(BERLUS_PATH . "/classes/mietkonto_class.php");
include_once(BERLUS_PATH . "/classes/class_formular.php");
include_once(BERLUS_PATH . "/classes/class_benutzer.php");
include_once(BERLUS_PATH . "/classes/class_mietentwicklung.php");

include_once(BERLUS_PATH . "/classes/class_ueberweisung.php");

include_once(BERLUS_PATH . "/classes/class_import.php");
include_once(BERLUS_PATH . "/classes/class_todo.php");
include_once(BERLUS_PATH . "/classes/class_wartungen.php");
include_once(BERLUS_PATH . "/classes/class_serienbrief.php");

include_once(BERLUS_PATH . "/classes/class_stammdaten.php");
include_once(BERLUS_PATH . "/classes/class_thumbs.php");

include_once(BERLUS_PATH . "/classes/phplot.php");
include_once(BERLUS_PATH . "/classes/class_kundenweb.php");
include_once(BERLUS_PATH . "classes/class_partners.php");

/* Alt */
/*
 * include_once("classes/config.inc.php");
 * include_once("includes/config.php");
 * include_once("classes/class_formular.php");
 * include_once("classes/class_benutzer.php");
 */
ob_clean();
set_time_limit(120);
session_start();
ob_start();
// Ausgabepuffer Starten
// session_start();

if (empty ($_SESSION ['autorisiert']) && !empty ($_REQUEST ['send_login'])) {
    $usercheck = check_user($_REQUEST ['benutzername'], $_REQUEST ['passwort']);
    if ($usercheck) {
        $_SESSION ['username'] = $usercheck;
        $benutzer_id = get_benutzer_id($_SESSION ['username']);
        $_SESSION ['benutzer_id'] = $benutzer_id;
        $_SESSION ['autorisiert'] = '1';
    } else {
        fehlermeldung_ausgeben("Anmeldung gescheitert!");
        weiterleiten_in_sec('index.php', 2);
    }
}

echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">';
echo "<head>";
// echo "<script src='https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js'></script>";
// echo "<script type='text/javascript' src='js/lightbox.js'></script>";
echo "<script src='bower_components/jquery/dist/jquery.min.js'></script>";
echo "<script src='bower_components/bootstrap/dist/js/bootstrap.min.js' integrity='sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS' crossorigin='anonymous'></script>";
echo "<script src='bower_components/bootstrap-treeview/dist/bootstrap-treeview.min.js'></script>";
echo "<script type='text/javascript' src='ajax/ajax.js'></script>\n";
echo "<script type='text/javascript' src='ajax/dd_kostenkonto.js'></script>\n";
echo "<script type='text/javascript' src='js/javascript.js'></script>\n";
echo "<script type='text/javascript' src='js/sorttable.js'></script>\n";
echo "<script type='text/javascript' src='js/navigation.js'></script>\n";
if (isset ($_SESSION ['autorisiert'])) {
    if (check_user_links($_SESSION['benutzer_id'], 'partner'))
        echo "<script type='text/javascript' src='js/navigation_partner.js'></script>\n";
    if (check_user_links($_SESSION['benutzer_id'], 'objekte_raus'))
        echo "<script type='text/javascript' src='js/navigation_object.js'></script>\n";
    if (check_user_links($_SESSION['benutzer_id'], 'haus_raus'))
        echo "<script type='text/javascript' src='js/navigation_house.js'></script>\n";
    if (check_user_links($_SESSION['benutzer_id'], 'einheit_raus'))
        echo "<script type='text/javascript' src='js/navigation_unit.js'></script>\n";
    if (check_user_links($_SESSION['benutzer_id'], 'mietvertrag_raus'))
        echo "<script type='text/javascript' src='js/navigation_contract.js'></script>\n";
    if (check_user_links($_SESSION['benutzer_id'], 'person'))
        echo "<script type='text/javascript' src='js/navigation_person.js'></script>\n";
    if (check_user_links($_SESSION['benutzer_id'], 'details'))
        echo "<script type='text/javascript' src='js/navigation_detail.js'></script>\n";
    if (check_user_links($_SESSION['benutzer_id'], 'mietkonten_blatt'))
        echo "<script type='text/javascript' src='js/navigation_rent.js'></script>\n";
    if (check_user_links($_SESSION['benutzer_id'], 'mietanpassung'))
        echo "<script type='text/javascript' src='js/navigation_rentajustment.js'></script>\n";
    if (check_user_links($_SESSION['benutzer_id'], 'rechnungen'))
        echo "<script type='text/javascript' src='js/navigation_bills.js'></script>\n";
    if (check_user_links($_SESSION['benutzer_id'], 'katalog'))
        echo "<script type='text/javascript' src='js/navigation_catalog.js'></script>\n";
    if (check_user_links($_SESSION['benutzer_id'], 'kontenrahmen'))
        echo "<script type='text/javascript' src='js/navigation_account_chart.js'></script>\n";
    if (check_user_links($_SESSION['benutzer_id'], 'geldkonto'))
        echo "<script type='text/javascript' src='js/navigation_account.js'></script>\n";
    if (check_user_links($_SESSION['benutzer_id'], 'kasse'))
        echo "<script type='text/javascript' src='js/navigation_register.js'></script>\n";
    if (check_user_links($_SESSION['benutzer_id'], 'lager'))
        echo "<script type='text/javascript' src='js/navigation_warehouse.js'></script>\n";
    if (check_user_links($_SESSION['benutzer_id'], 'buchen'))
        echo "<script type='text/javascript' src='js/navigation_booking.js'></script>\n";
    if (check_user_links($_SESSION['benutzer_id'], 'leerstand'))
        echo "<script type='text/javascript' src='js/navigation_vacancy.js'></script>\n";
    if (check_user_links($_SESSION['benutzer_id'], 'statistik'))
        echo "<script type='text/javascript' src='js/navigation_statistic.js'></script>\n";
    if (check_user_links($_SESSION['benutzer_id'], 'zeiterfassung'))
        echo "<script type='text/javascript' src='js/navigation_time_tracking.js'></script>\n";
    if (check_user_links($_SESSION['benutzer_id'], 'urlaub'))
        echo "<script type='text/javascript' src='js/navigation_vacation.js'></script>\n";
    if (check_user_links($_SESSION['benutzer_id'], 'kautionen'))
        echo "<script type='text/javascript' src='js/navigation_deposit.js'></script>\n";
    if (check_user_links($_SESSION['benutzer_id'], 'bk'))
        echo "<script type='text/javascript' src='js/navigation_opperating_cost.js'></script>\n";
    if (check_user_links($_SESSION['benutzer_id'], 'wartung'))
        echo "<script type='text/javascript' src='js/navigation_maintainance.js'></script>\n";
    if (check_user_links($_SESSION['benutzer_id'], 'sepa'))
        echo "<script type='text/javascript' src='js/navigation_sepa.js'></script>\n";
    if (check_user_links($_SESSION['benutzer_id'], 'benutzer'))
        echo "<script type='text/javascript' src='js/navigation_user.js'></script>\n";
    if (check_user_links($_SESSION['benutzer_id'], 'weg'))
        echo "<script type='text/javascript' src='js/navigation_weg.js'></script>\n";
    if (check_user_links($_SESSION['benutzer_id'], 'todo'))
        echo "<script type='text/javascript' src='js/navigation_todo.js'></script>\n";
    if (check_user_links($_SESSION['benutzer_id'], 'wartung'))
        echo "<script type='text/javascript' src='js/navigation_maintainance_planner.js'></script>\n";
    if (check_user_links($_SESSION['benutzer_id'], 'admin_panel'))
        echo "<script type='text/javascript' src='js/navigation_administration.js'></script>\n";
    if (check_user_links($_SESSION['benutzer_id'], 'listen'))
        echo "<script type='text/javascript' src='js/navigation_list.js'></script>\n";
    if (check_user_links($_SESSION['benutzer_id'], 'tickets'))
        echo "<script type='text/javascript' src='js/navigation_tickets.js'></script>\n";
    if (check_user_links($_SESSION['benutzer_id'], 'kundenweb'))
        echo "<script type='text/javascript' src='js/navigation_customerweb.js'></script>\n";
    if (check_user_links($_SESSION['benutzer_id'], 'mietspiegel'))
        echo "<script type='text/javascript' src='js/navigation_rent_index.js'></script>\n";
    echo "<script type='text/javascript' src='js/navigation_manual.js'></script>\n";
}
echo "<link href='css/lightbox.css' rel='stylesheet'>";
echo "<link href='bower_components/bootstrap/dist/css/bootstrap.min.css' rel='stylesheet' integrity='sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7' crossorigin='anonymous'>";
echo "<link rel='stylesheet' type='text/css'  href='bower_components/bootstrap-treeview/dist/bootstrap-treeview.min.css' media='screen'>\n";
echo "<link rel='stylesheet' type='text/css'  href='css/uebersicht.css' media='screen'>\n";
echo "<link rel='stylesheet' type='text/css'  href='css/berlussimo.css'  media='screen'>\n";
//echo "<link href='css/demo.css'       rel='stylesheet' type='text/css' /  media='screen'>";

// echo "<script src='js/lightbox-plus-jquery.min.js'></script>";

echo "<meta http-equiv='content-type' content='text/html; charset=UTF-8'>\n";
// echo "<meta content='text/html; charset=ISO-8859-1' http-equiv='content-type'>";
echo "</head>";

if (isset ($_REQUEST ['logout'])) {
    echo "AUSGELOGGT!<br>";
    $_SESSION = array();
    weiterleiten('index.php');
}

if (empty ($_SESSION ['autorisiert']) && empty ($_REQUEST ['send_login'])) {
    $f = new formular ();
    $f->erstelle_formular('Berlussimo - Bitte anmelden', '');
    $f->fieldset('Benutzernamen und Passwort eingeben', 'bin');
    $f->text_feld('Benutzername', 'benutzername', '', 30, 'benutzername', '');
    $f->passwort_feld('Password', 'passwort', '', 30, 'passwort', '');
    $f->send_button('send_login', 'Anmelden');
    $f->fieldset_ende();
    $f->ende_formular();
}

if (isset ($_SESSION ['autorisiert'])) {


    include_once("classes/mietkonto_class.php");
    include_once("includes/config.php");
    echo "<body>\n";
    echo "<div class='page-container'>\n";

    //<!-- top navbar -->
    echo "<div class='navbar navbar-berlus navbar-fixed-top' role='navigation'>\n";
    echo "<div class='container-fluid'>\n";
    echo "<div class='navbar-header'>\n";
    echo "<button type='button' class='navbar-toggle' data-toggle='offcanvas' data-target='.sidebar-nav'>\n";
    echo "<span class='icon-bar'></span>\n";
    echo "<span class='icon-bar'></span>\n";
    echo "<span class='icon-bar'></span>\n";
    echo "</button>\n";
    echo "<img class='navbar-brand navbar-berlus-logo' src='images/berlus_logo.svg' alt='Berlus Logo'><a class='navbar-brand' href='#'>Berlussimo</a>\n";
    echo "</div>\n";
    echo "<ul class='nav navbar-nav navbar-right'>\n";
    //echo "<li class='navbar-text'>Benutzer: " . $_SESSION['username'] . "</li>";
    echo "<li class='dropdown'>\n";
    echo "<a href=\"#\" class=\"dropdown-toggle\" data-toggle=\"dropdown\" role=\"button\" aria-haspopup=\"true\" aria-expanded=\"false\">" . $_SESSION['username'] . "<span class=\"caret\"></span></a>\n";
    echo "<ul class=\"dropdown-menu\">\n";
    echo "<li><a href=\"?logout\">Logout</a></li>\n";
    echo "</ul></ul>";
    echo "<div class='nav navbar-nav navbar-right'>\n";
    if (check_user_links($_SESSION ['benutzer_id'], 'rechnungen') && isset ($_SESSION ['partner_id'])) {
        $p = new partners ();
        $p->get_partner_name($_SESSION ['partner_id']);
        echo "<a href='?daten=rechnungen&option=eingangsbuch&partner_wechseln'><button type='button' class='btn btn-default btn-berlus navbar-btn'>Partner: <b>$p->partner_name</b></button></a>";
    } else {
        echo "<a href='?daten=rechnungen&option=eingangsbuch&partner_wechseln'><button type='button' class='btn btn-default btn-berlus navbar-btn'>Partner wählen</b></button></a>";
    }
    if (check_user_links($_SESSION ['benutzer_id'], 'buchen') && isset ($_SESSION ['geldkonto_id'])) {
        $g = new geldkonto_info ();
        $g->geld_konto_details($_SESSION ['geldkonto_id']);
        echo "<a href='?daten=buchen&option=geldkonto_aendern'><button type='button' class='btn btn-default btn-berlus navbar-btn'>Geldkonto: <b>$g->geldkonto_bezeichnung_kurz</b></button></a>";
    } else {
        echo "<a href='?daten=buchen&option=geldkonto_aendern'><button type='button' class='btn btn-default btn-berlus navbar-btn'>Geldkonto wählen</button></a>";
    }
    echo "</div>";
    echo "</div>\n";
    echo "</div>\n";

    echo "<div class='container'>\n";
    echo "<div class='row row-offcanvas row-offcanvas-left'>\n";

    //<!-- sidebar -->
    echo "<div class='col-xs-6 col-sm-3 col-lg-2 sidebar-offcanvas' id='sidebar' role='navigation'>\n";
    echo "<div id='tree'></div>";
    //echo "<div class='well' style='width:250px; padding: 8px 0;'>\n";
    //echo "<div style='overflow-y: scroll; overflow-x: hidden; height: 500px;'>\n";
    //include("options/links/links.statisch.php");
    //echo "</div>\n";
    //echo "</div>\n";
    echo "</div>\n";

    //<!-- main area -->
    echo "<div id='main' class='col-xs-12 col-sm-9 col-lg-10 default-panel panel'>\n";
    echo "<div class='panel-body'>\n";
    include_options();
    echo "</div>\n";
    echo "</div>\n";//<!-- /.col-xs-12 main -->
    echo "</div>\n";//<!--/.row-->
    echo "</div>\n";//<!--/.container-->
    echo "</div>\n";//<!--/.page-container-->

    $form = new mietkonto (); //TODO needed?
    $benutzer = $_SESSION ['username'];
//$form->erstelle_formular("Benutzer: $benutzer", NULL);
    $p = new partners ();

//include_options();

}
echo "<div id='aus' class='default-panel panel'>\n";
echo "<div class='panel-body'>\n";
echo "<center><b>Berlussimo</b> wird von der <a target='_new'  href='http://www.berlus.de'>Berlus GmbH</a> - Hausverwaltung zur Verfügung gestellt.</center>";
echo "</div>\n";
echo "</div>\n";

echo "</body></html>";
function include_options()
{
    $optdir = dir("options/case");
    while ($func = $optdir->read()) {
        if (substr($func, 0, 5) == "case.") {
            include($optdir->path . "/" . $func);
        }
    }
    closedir($optdir->handle);
}

function check_user($benutzername, $passwort)
{
    // $benutzername = mysql_real_escape_string($benutzername);
    // $passwort = mysql_real_escape_string($passwort);
    include_once("includes/config.php");
    // $passwort = md5($passwort);
    /* ' or 1=1-- */
    $db_abfrage = "SELECT benutzername FROM BENUTZER WHERE benutzername='$benutzername' && passwort='$passwort' ";
    // $db_abfrage1 = mysql_escape_string($db_abfrage);
    // echo $db_abfrage; die();
    $resultat = mysql_query(stripslashes(mysql_escape_string($db_abfrage))) or die (mysql_error());
    // $resultat = mysql_query($db_abfrage) or die(mysql_error());
    // $resultat = mysql_query($db_abfrage) or die(mysql_error());
    // mysql_real_escape_string

    $numrows = mysql_numrows($resultat);
    if ($numrows < 1) {
        return false;
    } else {
        while (list ($benutzername) = mysql_fetch_row($resultat))
            return $benutzername;
    }
}

function get_benutzer_id($benutzername)
{
    $result = mysql_query("SELECT benutzer_id FROM BENUTZER WHERE benutzername='$benutzername' LIMIT 0,1");

    $row = mysql_fetch_assoc($result);
    return $row ['benutzer_id'];
}

function compressed_output()
{
    $encoding = getEnv("HTTP_ACCEPT_ENCODING");
    $useragent = getEnv("HTTP_USER_AGENT");
    $method = trim(getEnv("REQUEST_METHOD"));
    $msie = preg_match("=msie=i", $useragent);
    $gzip = preg_match("=gzip=i", $encoding);

    if ($gzip && ($method != "POST" or !$msie)) {
        ob_start("ob_gzhandler");
    } else {
        ob_start();
    }
}

//function check_user_links($benutzer_id, $module_name)
//{
//    if (empty ($_SESSION ['benutzer_id'])) {
//        die ("SIE SIND NICHT ANGEMELDET");
//    } else {
//        $db_abfrage = "SELECT BM_DAT FROM BENUTZER_MODULE WHERE BENUTZER_ID='$benutzer_id' && (MODUL_NAME='$module_name' OR MODUL_NAME='*') && AKTUELL='1'";
//        $resultat = mysql_query($db_abfrage) or die (mysql_error());
//        $numrows = mysql_numrows($resultat);
//        if ($numrows) {
//            return 1;
//        }
//    }
//}

// $output = ob_get_clean();
// echo $output;