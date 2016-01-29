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
 * @filesource   $HeadURL: http://192.168.2.52/svn/berlussimo_1/tags/02.11.2010 - Downloadversion 0.27/options/modules/kontenrahmen.php $
 * @version      $Revision: 6 $
 * @modifiedby   $LastChangedBy: sivac $
 * @lastmodified $Date: 2010-09-21 10:34:50 +0200 (Di, 21 Sep 2010) $
 * 
 */
include_once ("includes/allgemeine_funktionen.php");
/* �berpr�fen ob Benutzer Zugriff auf das Modul hat */
if (! check_user_mod ( $_SESSION [benutzer_id], 'kontenrahmen' )) {
	echo '<script type="text/javascript">';
	echo "alert('Keine Berechtigung')";
	echo '</script>';
	die ();
}

include_once ("options/links/links.kontenrahmen.php");
include_once ("classes/mietkonto_class.php");
include_once ("classes/berlussimo_class.php");
include_once ("classes/class_kontenrahmen.php");

$option = $_REQUEST ["option"];
switch ($option) {
	
	default :
		$f = new formular ();
		$f->fieldset ( "Kontenrahmen�bersicht", 'kontenrahmen' );
		$konten_info = new k_rahmen ();
		$konten_info->kontenrahmen_liste_anzeigen ();
		$f->fieldset_ende ();
		break;
	
	case "kontenrahmen_uebersicht" :
		$f = new formular ();
		$f->fieldset ( "Kontenrahmen�bersicht", 'kontenrahmen' );
		$konten_info = new k_rahmen ();
		$konten_info->kontenrahmen_liste_anzeigen ();
		$f->fieldset_ende ();
		break;
	
	case "konten_anzeigen" :
		$f = new formular ();
		$f->fieldset ( "Kostenkonten�bersicht", 'kostenkonten' );
		if (! empty ( $_REQUEST [k_id] )) {
			$konten_info = new k_rahmen ();
			if (! isset ( $_REQUEST [pdf] )) {
				$konten_info->konten_liste_anzeigen ( $_REQUEST [k_id] );
			} else {
				$konten_info->konten_liste_anzeigen_pdf ( $_REQUEST [k_id] );
			}
		} else {
			echo "Keine Kostenkonten im Kontenrahmen erstellt";
		}
		$f->fieldset_ende ();
		break;
	
	case "kontenrahmen_neu" :
		$f = new formular ();
		$f->fieldset ( "Neuen Kontenrahmen erstellen", 'nk_e' );
		$konten_info = new k_rahmen ();
		$konten_info->form_kontenrahmen_neu ();
		$f->fieldset_ende ();
		break;
	
	case "k_bez_neu" :
		if (! empty ( $_REQUEST [k_bez] )) {
			$k_bez = $_REQUEST [k_bez];
			$k = new k_rahmen ();
			$k->kontenrahmen_speichern ( $k_bez );
			weiterleiten ( "?daten=kontenrahmen" );
		} else {
			echo "Geben Sie bitte eine Kontenrahmenbezeichnung ein.";
		}
		break;
	
	case "kostenkonto_neu" :
		$k = new k_rahmen ();
		$k->form_kostenkonto_neu ();
		break;
	
	case "konto_neu" :
		if (! empty ( $_REQUEST [konto] ) && ! empty ( $_REQUEST [bez] ) && ! empty ( $_REQUEST [kontenrahmen_id] )) {
			$k = new k_rahmen ();
			$_SESSION [kontenrahmen_id] = $_REQUEST [kontenrahmen_id];
			$_SESSION [k_gruppen_id] = $_REQUEST [k_gruppe];
			$_SESSION [k_kontoart_id] = $_REQUEST [kontoart_id];
			$k->kostenkonto_speichern ( $_REQUEST [kontenrahmen_id], $_REQUEST [konto], $_REQUEST [bez], $_REQUEST [kontoart_id], $_REQUEST [k_gruppe] );
			weiterleiten ( "?daten=kontenrahmen&option=konten_anzeigen&k_id=$_SESSION[kontenrahmen_id]" );
		} else {
			echo "Eingabe unvollst�ndig. Error: S562q357";
		}
		break;
	
	case "kostenkonto_ae" :
		if (! empty ( $_REQUEST [k_dat] )) {
			$k = new k_rahmen ();
			$k->form_kostenkonto_aendern ( $_REQUEST [k_dat] );
		}
		break;
	
	case "konto_ae_send" :
		if (! empty ( $_REQUEST [dat] ) && ! empty ( $_REQUEST [konto] ) && ! empty ( $_REQUEST [bez] ) && ! empty ( $_REQUEST [kontenrahmen_id] )) {
			$k = new k_rahmen ();
			$_SESSION [kontenrahmen_id] = $_REQUEST [kontenrahmen_id];
			$_SESSION [k_gruppen_id] = $_REQUEST [k_gruppe];
			$_SESSION [k_kontoart_id] = $_REQUEST [kontoart_id];
			$k->kostenkonto_aendern ( $_REQUEST [dat], $_REQUEST [kontenrahmen_id], $_REQUEST [konto], $_REQUEST [bez], $_REQUEST [kontoart_id], $_REQUEST [k_gruppe] );
			weiterleiten ( "?daten=kontenrahmen&option=konten_anzeigen&k_id=$_SESSION[kontenrahmen_id]" );
		} else {
			echo "Eingabe unvollst�ndig. Error: S56sdf7";
		}
		
		break;
	
	case "gruppen" :
		$k = new k_rahmen ();
		$k->gruppen_anzeigen ();
		break;
	
	case "gruppe_neu" :
		$k = new k_rahmen ();
		$k->form_gruppe_neu ();
		$k->gruppen_anzeigen ();
		break;
	
	case "g_bez_neu" :
		if (! empty ( $_REQUEST [g_bez] )) {
			$k = new k_rahmen ();
			$k->gruppe_speichern ( $_REQUEST [g_bez] );
			weiterleiten ( "?daten=kontenrahmen&option=gruppe_neu" );
		} else {
			echo "Eingabe unvollst�ndig. Error: 123sdf7";
		}
		break;
	
	case "kontoarten" :
		$k = new k_rahmen ();
		$k->kontoarten_anzeigen ();
		break;
	
	case "kontoart_neu" :
		$k = new k_rahmen ();
		$k->form_kontoart_neu ();
		$k->kontoarten_anzeigen ();
		break;
	
	case "kontoart_neu1" :
		if (! empty ( $_REQUEST [kontoart] )) {
			$k = new k_rahmen ();
			$k->kontoart_speichern ( $_REQUEST [kontoart] );
			weiterleiten ( "?daten=kontenrahmen&option=kontoart_neu" );
		} else {
			echo "Eingabe unvollst�ndig. Error: 94555f7";
		}
		break;
	
	case "kontenrahmen_zuweisen" :
		$k = new k_rahmen ();
		$k->form_kontenrahmen_zuweisen ();
		break;
	
	case "zuweisen_kr" :
		if (! empty ( $_REQUEST [kostentraeger_typ] ) && ! empty ( $_REQUEST [kostentraeger_id] ) && ! empty ( $_REQUEST [kontenrahmen_id] )) {
			$k = new k_rahmen ();
			$k->zuweisung_speichern ( $_REQUEST [kostentraeger_typ], $_REQUEST [kostentraeger_id], $_REQUEST [kontenrahmen_id] );
			weiterleiten ( "?daten=kontenrahmen" );
		} else {
			echo "Eingabe unvollst�ndig. Error: 42gsbx3f7";
		}
		break;
	
	case "zuweisung_del" :
		if (! empty ( $_REQUEST [dat] )) {
			$k = new k_rahmen ();
			$k->zuweisung_loeschen ( $_REQUEST [dat] );
			weiterleiten ( "?daten=kontenrahmen" );
		} else {
			echo "Eingabe unvollst�ndig. Error: 42gsjklasd7";
		}
		break;
}
?>
