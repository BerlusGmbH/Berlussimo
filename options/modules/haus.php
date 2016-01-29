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
 * @filesource   $HeadURL: http://192.168.2.52/svn/berlussimo_1/tags/02.11.2010 - Downloadversion 0.27/options/modules/haus.php $
 * @version      $Revision: 15 $
 * @modifiedby   $LastChangedBy: sivac $
 * @lastmodified $Date: 2011-07-07 10:41:33 +0200 (Do, 07 Jul 2011) $
 * 
 */
include_once ("includes/allgemeine_funktionen.php");

/* �berpr�fen ob Benutzer Zugriff auf das Modul hat */
if (! check_user_mod ( $_SESSION ['benutzer_id'], 'haus_raus' )) {
	echo '<script type="text/javascript">';
	echo "alert('Keine Berechtigung')";
	echo '</script>';
	die ();
}

$daten = $_REQUEST ["daten"];
$haus_raus = $_REQUEST ["haus_raus"];
if (! empty ( $_REQUEST ['objekt_id'] )) {
	$objekt_id = $_REQUEST ["objekt_id"];
} else {
	$objekt_id = '';
}
include_once ("options/links/links.form_haus.php");
switch ($haus_raus) {
	
	/* Liste der H�user anzeigen */
	case "haus_kurz" :
		$form = new mietkonto ();
		$form->erstelle_formular ( "H�userliste", NULL );
		haus_kurz ( $objekt_id );
		$form->ende_formular ();
		break;
	
	/* Formular zum �ndern des Hauses aufrufen */
	case "haus_aendern" :
		$f = new formular ();
		
		$bk = new berlussimo_global ();
		$link = "?daten=haus_raus&haus_raus=haus_aendern";
		$bk->objekt_auswahl_liste ( $link );
		if (! isset ( $_REQUEST ['haus_id'] )) {
			if (isset ( $_SESSION ['objekt_id'] )) {
				$f->fieldset ( 'H�user zum �ndern w�hlen', 'hww' );
				haus_kurz ( $_SESSION ['objekt_id'] );
				$f->fieldset_ende ();
			}
		} else {
			$h = new haus ();
			$haus_id = $_REQUEST ['haus_id'];
			$h->form_haus_aendern ( $haus_id );
		}
		
		break;
	
	/* �nderungen des Hauses speichern */
	case "haus_aend_speichern" :
		// print_req();
		if (isset ( $_REQUEST ['haus_id'] ) && ! empty ( $_REQUEST ['haus_id'] ) && isset ( $_REQUEST ['strasse'] ) && ! empty ( $_REQUEST ['strasse'] ) && isset ( $_REQUEST ['haus_nr'] ) && ! empty ( $_REQUEST ['haus_nr'] ) && isset ( $_REQUEST ['ort'] ) && ! empty ( $_REQUEST ['ort'] ) && isset ( $_REQUEST ['plz'] ) && ! empty ( $_REQUEST ['plz'] ) && isset ( $_REQUEST ['qm'] ) && ! empty ( $_REQUEST ['qm'] ) && isset ( $_REQUEST ['Objekt'] ) && ! empty ( $_REQUEST ['Objekt'] )) {
			$haus_id = $_REQUEST ['haus_id'];
			$strasse = $_REQUEST ['strasse'];
			$haus_nr = $_REQUEST ['haus_nr'];
			$ort = $_REQUEST ['ort'];
			$plz = $_REQUEST ['plz'];
			$qm = nummer_komma2punkt ( $_REQUEST ['qm'] );
			$objekt_id = $_REQUEST ['Objekt'];
			
			$h = new haus ();
			$h->haus_aenderung_in_db ( $strasse, $haus_nr, $ort, $plz, $qm, $objekt_id, $haus_id );
			fehlermeldung_ausgeben ( "Haus ge�ndert!" );
			weiterleiten_in_sec ( "?daten=haus_raus&haus_raus=haus_kurz&objekt_id=$objekt_id", 3 );
		} else {
			fehlermeldung_ausgeben ( "Eingegebene Daten unvollst�ndig, erneut versuchen bitte!" );
			$haus_id = $_REQUEST ['haus_id'];
			weiterleiten_in_sec ( "?daten=haus_raus&haus_raus=haus_aendern&haus_id=$haus_id", 3 );
		}
		break;
}
function haus_kurz($objekt_id = '') {
	if (empty ( $objekt_id )) {
		$db_abfrage = "SELECT OBJEKT_ID, HAUS_ID, HAUS_STRASSE, HAUS_NUMMER, HAUS_PLZ, HAUS_QM FROM HAUS WHERE HAUS_AKTUELL='1' ORDER BY HAUS_STRASSE,  0+HAUS_NUMMER, OBJEKT_ID ASC";
		$title = "Alle H�user";
	} else {
		$db_abfrage = "SELECT OBJEKT_ID, HAUS_ID, HAUS_STRASSE, HAUS_NUMMER, HAUS_PLZ, HAUS_QM FROM HAUS where OBJEKT_ID='$objekt_id' && HAUS_AKTUELL='1' ORDER BY HAUS_STRASSE, 0+HAUS_NUMMER, OBJEKT_ID ASC";
		$objekt_kurzname = objekt_namen_by_id ( $objekt_id );
		$title = "H�user vom Objekt:  $objekt_kurzname";
	}
	
	$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
	
	$numrows = mysql_numrows ( $resultat );
	if ($numrows < 1) {
		echo "<h1><b>Keine H�user vorhanden!!!</b></h1>\n";
	} else {
		// echo "<div id=\"iframe_1\">";
		// echo "<div class=\"abstand_iframe\">";
		// echo "<div class=\"scrollbereich\">";
		iframe_start ();
		echo "<table class=\"sortable\">\n";
		// echo "<tr class=\"feldernamen\"><td colspan=8>$title</td></tr>\n";
		// echo "<tr class=\"feldernamen\"><td width=155>Stra�e</td><td width=60>Nr.</td><td width=60>PLZ</td><td width=60>H m�</td><td width=100>E m�</td><td colspan=2>Zusatzinfo</td></tr>\n";
		echo "<tr><th>Strasse</th><th>Nr.</th><th>PLZ</th><th>m�</th><th>Em�</th><th>Einheiten</th><th>INFOS</th><th>OPTION</th></tr>";
		
		$counter = 0;
		while ( list ( $OBJEKT_ID, $HAUS_ID, $HAUS_STRASSE, $HAUS_NUMMER, $HAUS_PLZ, $HAUS_QM ) = mysql_fetch_row ( $resultat ) ) {
			$detail_check = detail_check ( "HAUS", $HAUS_ID );
			if ($detail_check > 0) {
				$detail_link = "<a class=\"table_links\" href=\"?daten=details&option=details_anzeigen&detail_tabelle=HAUS&detail_id=$HAUS_ID\">Details</a>";
			} else {
				$detail_link = "<a class=\"table_links\" href=\"?daten=details&option=details_hinzu&detail_tabelle=HAUS&detail_id=$HAUS_ID\">Neues Detail</a>";
			}
			$einheiten_im_haus = anzahl_einheiten_im_haus ( $HAUS_ID );
			$gesammtflaeche_einheiten = einheiten_gesamt_qm ( $HAUS_ID );
			if (empty ( $gesammtflaeche_einheiten )) {
				$gesammtflaeche_einheiten = "0";
			}
			$counter ++;
			
			$link_haus_aendern = "<a href=\"?daten=haus_raus&haus_raus=haus_aendern&haus_id=$HAUS_ID\">Haus �ndern</th>";
			
			if ($counter == 1) {
				echo "<tr class=\"zeile1\"><td width=150>$HAUS_STRASSE</td><td width=60>$HAUS_NUMMER</td><td width=60>$HAUS_PLZ</td><td width=60>$HAUS_QM m�</td><td width=100>$gesammtflaeche_einheiten m�</td><td><a class=\"table_links\" href=\"?daten=einheit_raus&einheit_raus=einheit_kurz&haus_id=$HAUS_ID\">Einheiten (<b>$einheiten_im_haus</b>)</a></td><td>$detail_link</td><td>$link_haus_aendern</td></tr>";
			}
			if ($counter == 2) {
				echo "<tr class=\"zeile2\"><td width=150>$HAUS_STRASSE</td><td width=60>$HAUS_NUMMER</td><td width=60>$HAUS_PLZ</td><td width=60>$HAUS_QM m�</td><td width=60>$gesammtflaeche_einheiten m�</td><td><a class=\"table_links\" href=\"?daten=einheit_raus&einheit_raus=einheit_kurz&haus_id=$HAUS_ID\">Einheiten (<b>$einheiten_im_haus</b>)</a></td><td>$detail_link</td><td>$link_haus_aendern</td></tr>";
				$counter = 0;
			}
		}
		echo "</table>";
		echo "</div>";
		echo "</div>";
		echo "</div>";
	}
}
function anzahl_einheiten_im_haus($haus_id) {
	$db_abfrage = "SELECT * FROM EINHEIT WHERE HAUS_ID='$haus_id' && EINHEIT_AKTUELL='1'";
	$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
	
	$numrows = mysql_numrows ( $resultat );
	return $numrows;
}
function einheiten_gesamt_qm($haus_id) {
	$db_abfrage = "SELECT SUM(EINHEIT_QM) AS SUMME FROM EINHEIT where HAUS_ID='$haus_id' && EINHEIT_AKTUELL='1'";
	$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
	while ( list ( $SUMME ) = mysql_fetch_row ( $resultat ) )
		return $SUMME;
}
function objekt_namen_by_id($objekt_id) {
	$db_abfrage = "SELECT OBJEKT_KURZNAME FROM OBJEKT where OBJEKT_ID='$objekt_id' && OBJEKT_AKTUELL='1'";
	$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
	while ( list ( $OBJEKT_KURZNAME ) = mysql_fetch_row ( $resultat ) )
		return $OBJEKT_KURZNAME;
}

?>
