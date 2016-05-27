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
 * @filesource   $HeadURL: http://192.168.2.52/svn/berlussimo_1/tags/02.11.2010 - Downloadversion 0.27/options/modules/admin_panel.php $
 * @version      $Revision: 6 $
 * @modifiedby   $LastChangedBy: sivac $
 * @lastmodified $Date: 2010-09-21 10:34:50 +0200 (Di, 21 Sep 2010) $
 * 
 */
include_once ("includes/allgemeine_funktionen.php");

/* Überprüfen ob Benutzer Zugriff auf das Modul hat */
if (! check_user_mod ( $_SESSION [benutzer_id], 'admin_panel' )) {
	echo '<script type="text/javascript">';
	echo "alert('Keine Berechtigung')";
	echo '</script>';
	die ();
}

include_once ("includes/formular_funktionen.php");
$admin_panel = $_REQUEST ["admin_panel"];
if (isset ( $admin_panel )) {
	
	switch ($admin_panel) {
		
		case "menu" :
			echo "ADMIN MENU";
			break;
		
		case "details_neue_kat" :
			detail_kategorie_form ();
			liste_detail_kat ();
			break;
		
		case "details_neue_ukat" :
			detail_unterkategorie_form ();
			liste_udetail_kat ();
			break;
		
		case "liste_detail_kat" :
			liste_detail_kat ();
			break;
	}
}
function detail_kategorie_form() {
	echo "<div class=\"div balken_detail_kat_form\"><span class=\"font_balken_uberschrift\">HAUPTDETAIL / DETAILGRUPPE ERSTELLEN</span><hr />";
	
	if (! isset ( $_REQUEST ['submit_detail_kat'] )) {
		erstelle_formular ( NULL, NULL );
		detail_drop_down_kategorie ();
		erstelle_eingabefeld ( "Detail / Detailgruppe", "detail_kat_name", "", 30 );
		erstelle_submit_button_nur ( "submit_detail_kat", "Erstellen" );
		ende_formular ();
	}
	if (isset ( $_REQUEST ['submit_detail_kat'] )) {
		// print_r($_REQUEST);
		if (isset ( $_REQUEST ['detail_kat_name'] ) && empty ( $_REQUEST ['detail_kat_name'] )) {
			fehlermeldung_ausgeben ( "Geben Sie bitte einen Kategorienamen ein!" );
			erstelle_back_button ();
		} elseif (isset ( $_REQUEST ['bereich_kategorie'] ) && empty ( $_REQUEST ['bereich_kategorie'] )) {
			fehlermeldung_ausgeben ( "Wählen Sie bitte eine Detailtabelle aus!" );
			erstelle_back_button ();
		} else {
			$detail_kat_name = bereinige_string ( $_REQUEST ['detail_kat_name'] );
			$bereich_kategorie = bereinige_string ( $_REQUEST ['bereich_kategorie'] );
			echo $detail_kat_name;
			echo $bereich_kategorie;
			$detail_kat_exists = check_detail_kat ( $detail_kat_name );
			if ($detail_kat_exists == 0) {
				$db_abfrage = "INSERT INTO DETAIL_KATEGORIEN VALUES (NULL, '$detail_kat_name', '$bereich_kategorie', '1')";
				$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
				hinweis_ausgeben ( "Detail bzw. Detailgruppe $detail_kat_name wurde dem Bereich $bereich_kategorie hinzugefügt." );
			} else {
				fehlermeldung_ausgeben ( "Gleichnamige Detailkategorie existiert!" );
				erstelle_back_button ();
			}
		}
	}
	
	echo "</div>";
}
function detail_unterkategorie_form() {
	echo "<div class=\"div balken_detail_kat_form\"><span class=\"font_balken_uberschrift\">AUSWAHLOPTIONEN</span><hr />";
	
	if (! isset ( $_REQUEST ['submit_detail_ukat'] )) {
		erstelle_formular ( NULL, NULL );
		detail_drop_down_kategorie_db ();
		erstelle_eingabefeld ( "Auswahloption", "detail_kat_uname", "", 30 );
		erstelle_submit_button_nur ( "submit_detail_ukat", "Erstellen" );
		ende_formular ();
	}
	if (isset ( $_REQUEST ['submit_detail_ukat'] )) {
		// print_r($_REQUEST);
		if (isset ( $_REQUEST ['detail_kat_uname'] ) && empty ( $_REQUEST ['detail_kat_uname'] )) {
			fehlermeldung_ausgeben ( "Geben Sie bitte eine Option ein!" );
			erstelle_back_button ();
		} else {
			$detail_kat_uname = bereinige_string ( $_REQUEST ['detail_kat_uname'] );
			$bereich_kategorie = bereinige_string ( $_REQUEST ['bereich_kategorie'] );
			echo $detail_kat_uname;
			echo $bereich_kategorie;
			$u_kat_exists = check_detail_ukat ( $bereich_kategorie, $detail_kat_uname );
			$haupt_kat_name = get_detail_kat_name ( $bereich_kategorie );
			if ($u_kat_exists == 0) {
				$db_abfrage = "INSERT INTO DETAIL_UNTERKATEGORIEN VALUES (NULL, '$bereich_kategorie', '$detail_kat_uname', '1')";
				$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
				hinweis_ausgeben ( "Unterdetail <u>$detail_kat_uname</u> bzw. Auswahloption wurde dem Bereich $haupt_kat_name hinzugefügt." );
			} else {
				fehlermeldung_ausgeben ( "Gleichnamige Detailoption existiert!" );
				erstelle_back_button ();
			}
		}
	}
	
	echo "</div>";
}
function check_detail_kat($kat) {
	$db_abfrage = "SELECT DETAIL_KAT_ID FROM DETAIL_KATEGORIEN WHERE DETAIL_KAT_NAME='$kat' && DETAIL_KAT_AKTUELL='1'";
	$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
	$numrows = mysql_numrows ( $resultat );
	return $numrows;
}
function check_detail_ukat($kat_id, $kat_name) {
	$db_abfrage = "SELECT * FROM DETAIL_UNTERKATEGORIEN WHERE KATEGORIE_ID='$kat_id' && UNTERKATEGORIE_NAME='$kat_name' && AKTUELL='1'";
	$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
	$numrows = mysql_numrows ( $resultat );
	return $numrows;
}
function get_detail_kat_name($id) {
	$db_abfrage = "SELECT DETAIL_KAT_NAME FROM DETAIL_KATEGORIEN WHERE DETAIL_KAT_ID='$id' && DETAIL_KAT_AKTUELL='1'";
	$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
	while ( list ( $DETAIL_KAT_NAME ) = mysql_fetch_row ( $resultat ) )
		return $DETAIL_KAT_NAME;
}
function liste_detail_kat() {
	if (isset ( $_REQUEST ['table'] ) && ! empty ( $_REQUEST ['table'] )) {
		$db_abfrage = "SELECT DETAIL_KAT_ID, DETAIL_KAT_NAME, DETAIL_KAT_KATEGORIE FROM DETAIL_KATEGORIEN WHERE DETAIL_KAT_AKTUELL='1' && DETAIL_KAT_KATEGORIE='$_REQUEST[table]' ORDER BY DETAIL_KAT_KATEGORIE ASC ";
	} else {
		$db_abfrage = "SELECT DETAIL_KAT_ID, DETAIL_KAT_NAME, DETAIL_KAT_KATEGORIE FROM DETAIL_KATEGORIEN WHERE DETAIL_KAT_AKTUELL='1' ORDER BY DETAIL_KAT_KATEGORIE ASC ";
	}
	$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
	
	echo "<div class=\"tabelle_objekte\"><table>\n";
	echo "<tr class=\"feldernamen\"><td colspan=\"2\">HAUPTDETAILS</td></tr>\n";
	echo "<tr class=\"feldernamen\"><td>DETAILNAME</td><td>KATEGORIE</tr>\n";
	$counter = 0;
	while ( list ( $DETAIL_KAT_ID, $DETAIL_KAT_NAME, $DETAIL_KAT_KATEGORIE ) = mysql_fetch_row ( $resultat ) ) {
		$auswahl_link = "<a href=\"?optionen=admin_panel&admin_panel=details_neue_kat&table=$DETAIL_KAT_KATEGORIE\">$DETAIL_KAT_KATEGORIE</a>";
		
		$counter ++;
		if ($counter == 1) {
			echo "<tr class=\"zeile1\"><td>$DETAIL_KAT_NAME</td><td>$auswahl_link</td></tr>\n";
		}
		if ($counter == 2) {
			echo "<tr class=\"zeile2\"><td>$DETAIL_KAT_NAME</td><td>$auswahl_link</td></tr>\n";
			$counter = 0;
		}
	}
	echo "</table></div>";
}
function liste_udetail_kat() {
	if (isset ( $_REQUEST ['table'] ) && ! empty ( $_REQUEST ['table'] )) {
		$db_abfrage = "SELECT UKAT_DAT, KATEGORIE_ID, UNTERKATEGORIE_NAME FROM DETAIL_UNTERKATEGORIEN WHERE AKTUELL='1' ORDER BY KATEGORIE_ID ASC ";
	} else {
		$db_abfrage = "SELECT UKAT_DAT, KATEGORIE_ID, UNTERKATEGORIE_NAME FROM DETAIL_UNTERKATEGORIEN WHERE AKTUELL='1' ORDER BY KATEGORIE_ID ASC ";
	}
	$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
	
	echo "<div class=\"tabelle_objekte\"><table>\n";
	echo "<tr class=\"feldernamen\"><td colspan=\"2\">HAUPTDETAILS</td></tr>\n";
	echo "<tr class=\"feldernamen\"><td>DETAIL</td><td>OPTION</tr>\n";
	$counter = 0;
	while ( list ( $UKAT_DAT, $KATEGORIE_ID, $UNTERKATEGORIE_NAME ) = mysql_fetch_row ( $resultat ) ) {
		$kat_name = get_detail_kat_name ( $KATEGORIE_ID );
		$counter ++;
		if ($counter == 1) {
			echo "<tr class=\"zeile1\"><td>$kat_name</td><td>$UNTERKATEGORIE_NAME</td></tr>\n";
		}
		if ($counter == 2) {
			echo "<tr class=\"zeile1\"><td>$kat_name</td><td>$UNTERKATEGORIE_NAME</td></tr>\n";
			$counter = 0;
		}
	}
	echo "</table></div>";
}
