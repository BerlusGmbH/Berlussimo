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
 * @filesource   $HeadURL: http://192.168.2.52/svn/berlussimo_1/tags/02.11.2010 - Downloadversion 0.27/options/modules/details.php $
 * @version      $Revision: 6 $
 * @modifiedby   $LastChangedBy: sivac $
 * @lastmodified $Date: 2010-09-21 10:34:50 +0200 (Di, 21 Sep 2010) $
 * 
 */
include_once ("includes/allgemeine_funktionen.php");

/* �berpr�fen ob Benutzer Zugriff auf das Modul hat */
if (! check_user_mod ( $_SESSION ['benutzer_id'], 'details' )) {
	echo '<script type="text/javascript">';
	echo "alert('Keine Berechtigung')";
	echo '</script>';
	die ();
}

include_once ("includes/formular_funktionen.php");

/* Klasse "formular" f�r Formularerstellung laden */
include_once ("classes/class_formular.php");

/* Modulabh�ngige Dateien d.h. Links und eigene Klasse */
include_once ("classes/class_details.php");
include_once ("options/links/links.details.php");

$option = $_REQUEST ["option"];
$detail_tabelle = $_REQUEST ["detail_tabelle"];
$detail_id = $_REQUEST ["detail_id"];

/* Optionsschalter */
switch ($option) {
	
	case "details_anzeigen" :
		$f = new formular ();
		$f->fieldset ( "Details anzeigen", 'details' );
		$d = new detail (); // class details neue, nicht berlussimo
		$d->detailsanzeigen ( $detail_tabelle, $detail_id );
		$f->fieldset_ende ();
		break;
	
	case "details_hinzu" :
		$f = new formular ();
		$f->fieldset ( "Details hinzuf�gen", 'details' );
		$d = new detail (); // class details neue, nicht berlussimo
		$vorauswahl = $_REQUEST ['vorauswahl'];
		$d->form_detail_hinzu ( $detail_tabelle, $detail_id, $vorauswahl );
		$f->fieldset_ende ();
		break;
	
	case "detail_gesendet" :
		$f = new formular ();
		$f->fieldset ( "Details hinzuf�gen", 'details' );
		$d = new detail (); // class details neue, nicht berlussimo
		/*
		 * echo "<pre>";
		 * print_r($_POST);
		 * echo "</pre>";
		 */
		$d = new detail ();
		
		if ($_POST ['detail_kat'] != 'nooption') {
			if ($_POST ['detail_ukat'] != 'nooption') {
				$d->get_katname ( $_POST ['detail_kat'] );
				$u_kat_value = $_POST ['detail_ukat'];
				echo "$d->detail_name: $u_kat_value";
				$tabelle = $_POST ['tabelle'];
				$id = $_POST ['id'];
				$bemerkung = $_POST ['bemerkung'];
				$d->detail_speichern ( $tabelle, $id, $d->detail_name, $u_kat_value, $bemerkung );
			} else {
				$d->get_katname ( $_POST ['detail_kat'] );
				$u_kat_value = $_POST ['inhalt'];
				echo "$d->detail_name: $u_kat_value";
				$tabelle = $_POST ['tabelle'];
				$id = $_POST ['id'];
				$bemerkung = $_POST ['bemerkung'];
				$d->detail_speichern ( $tabelle, $id, $d->detail_name, $u_kat_value, $bemerkung );
			}
		}
		
		$f->fieldset_ende ();
		break;
	
	case "detail_loeschen" :
		$f = new formular ();
		$f->fieldset ( "Detail l�schen", 'details' );
		$d = new detail (); // class details neue, nicht berlussimo
		if (! empty ( $_REQUEST ['detail_dat'] )) {
			$detail_dat = $_REQUEST ['detail_dat'];
			echo $detail_dat;
			$d->detail_loeschen ( $detail_dat );
		}
		
		$f->fieldset_ende ();
		break;
	
	case "bk" :
		$f = new formular ();
		$f->fieldset ( "BK", 'details' );
		include_once ('classes/class_bk.php');
		$bk = new bk (); // betriebskoten
		$bk->zeige ();
		
		$f->fieldset_ende ();
		break;
	
	default :
		echo "<h1>Es wird bearbeitet ;-)</h1>";
		break;
	
	case "detail_suche" :
		$f = new formular ();
		$f->erstelle_formular ( 'Details durchsuchen', '' );
		$d = new detail ();
		$d->dropdown_details ( 'Filter Detail', 'det_name', '_det_name' );
		$f->text_feld ( 'Suchtext', 'suchtext', '', 50, 'suchtext', null );
		$f->hidden_feld ( 'option', 'detail_finden' );
		$f->send_button ( 'BtNSuch', 'Suchen' );
		$f->ende_formular ();
		break;
	
	case "detail_finden" :
		// print_req();
		$suchtext = $_REQUEST ['suchtext'];
		$det_name = $_REQUEST ['det_name'];
		$d = new detail ();
		$d->finde_detail ( $suchtext, $det_name );
		break;
}

?>
