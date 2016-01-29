<?php
/* Allgemeine Funktionsdatei laden */
include_once ("includes/allgemeine_funktionen.php");
include_once ("classes/class_mietspiegel.php");

/* �berpr�fen ob Benutzer Zugriff auf das Modul hat */
if (! isset ( $_SESSION ['benutzer_id'] ) or ! check_user_mod ( $_SESSION ['benutzer_id'], 'mietspiegel' )) {
	echo '<script type="text/javascript">';
	echo "alert('Keine Berechtigung')";
	echo '</script>';
	die ();
}

/* Modulabh�ngige Dateien d.h. Links und eigene Klasse */
include_once ("options/links/links.mietspiegel.php");

if (! empty ( $_REQUEST ["option"] )) {
	$option = $_REQUEST ["option"];
} else {
	$option = 'default';
}

/* Optionsschalter */
switch ($option) {
	
	default :
		echo "NIX";
		break;
	
	case "mietspiegelliste" :
		$ms = new mietspiegel ();
		$ms->liste_mietspiegel ();
		break;
	
	case "mietspiegel_anzeigen" :
		$ms = new mietspiegel ();
		$jahr = $_REQUEST ['jahr'];
		if (isset ( $_REQUEST ['ort'] )) {
			$ort = $_REQUEST ['ort'];
		} else {
			$ort = null;
		}
		$ms->mietspiegel_anzeigen ( $jahr, $ort );
		$ms->abzuege_anzeigen ( $jahr, $ort );
		break;
	
	case "neuer_mietspiegel" :
		$ms = new mietspiegel ();
		$ms->form_neuer_mietspiegel ();
		break;
	
	case "ms_speichern" :
		if (isset ( $_REQUEST ['jahr'] ) && ! empty ( $_REQUEST ['jahr'] )) {
			$jahr = $_REQUEST ['jahr'];
			if (isset ( $_REQUEST ['ort'] ) && ! empty ( $_REQUEST ['ort'] )) {
				$ort = $_REQUEST ['ort'];
				$ms = new mietspiegel ();
				$ms->ms_speichern ( $jahr, $ort );
				weiterleiten ( "?daten=mietspiegel&option=mietspiegel_anzeigen&jahr=$jahr&ort=$ort" );
			} else {
				$ms = new mietspiegel ();
				$ms->ms_speichern ( $jahr, null );
				weiterleiten ( "?daten=mietspiegel&option=mietspiegel_anzeigen&jahr=$jahr" );
			}
		}
		break;
	
	case "ms_wert_speichern" :
		if (isset ( $_REQUEST ['jahr'] ) && ! empty ( $_REQUEST ['jahr'] )) {
			$jahr = $_REQUEST ['jahr'];
			if (isset ( $_REQUEST ['ort'] ) && ! empty ( $_REQUEST ['ort'] )) {
				$ort = $_REQUEST ['ort'];
				
				if (isset ( $_REQUEST ['feld'] ) && ! empty ( $_REQUEST ['feld'] ) && isset ( $_REQUEST ['u_wert'] ) && ! empty ( $_REQUEST ['u_wert'] ) && isset ( $_REQUEST ['m_wert'] ) && ! empty ( $_REQUEST ['m_wert'] ) && isset ( $_REQUEST ['o_wert'] ) && ! empty ( $_REQUEST ['o_wert'] )) {
					$ms = new mietspiegel ();
					$ms->ms_speichern ( $jahr, $ort, $_REQUEST ['feld'], $_REQUEST ['u_wert'], $_REQUEST ['m_wert'], $_REQUEST ['o_wert'] );
					weiterleiten ( "?daten=mietspiegel&option=mietspiegel_anzeigen&jahr=$jahr&ort=$ort" );
				} else {
					fehlermeldung_ausgeben ( "Alle Felder ausf�llen" );
				}
			} else {
				if (isset ( $_REQUEST ['feld'] ) && ! empty ( $_REQUEST ['feld'] ) && isset ( $_REQUEST ['u_wert'] ) && ! empty ( $_REQUEST ['u_wert'] ) && isset ( $_REQUEST ['m_wert'] ) && ! empty ( $_REQUEST ['m_wert'] ) && isset ( $_REQUEST ['o_wert'] ) && ! empty ( $_REQUEST ['o_wert'] )) {
					$ms = new mietspiegel ();
					$ms->ms_speichern ( $jahr, null, $_REQUEST ['feld'], $_REQUEST ['u_wert'], $_REQUEST ['m_wert'], $_REQUEST ['o_wert'] );
				} else {
					fehlermeldung_ausgeben ( "Alle Felder ausf�llen" );
				}
				weiterleiten ( "?daten=mietspiegel&option=mietspiegel_anzeigen&jahr=$jahr" );
			}
		}
		
		break;
	
	case "abzug_speichern" :
		print_req ();
		if (isset ( $_REQUEST ['jahr'] ) && ! empty ( $_REQUEST ['jahr'] ) && isset ( $_REQUEST ['merkmal'] ) && ! empty ( $_REQUEST ['merkmal'] ) && isset ( $_REQUEST ['wert'] ) && ! empty ( $_REQUEST ['wert'] ) && isset ( $_REQUEST ['a_klasse'] ) && ! empty ( $_REQUEST ['a_klasse'] )) {
			$ms = new mietspiegel ();
			if (isset ( $_REQUEST ['ort'] ) && ! empty ( $_REQUEST ['ort'] )) {
				$betrag = nummer_komma2punkt ( $_REQUEST ['wert'] );
				$ms->sonderabzug_speichern ( $_REQUEST ['jahr'], $_REQUEST ['merkmal'], $betrag, $_REQUEST ['a_klasse'], $_REQUEST ['ort'] );
				$jahr = $_REQUEST ['jahr'];
				$ort = $_REQUEST ['ort'];
				weiterleiten ( "?daten=mietspiegel&option=mietspiegel_anzeigen&jahr=$jahr&ort=$ort" );
			} else {
				$betrag = nummer_komma2punkt ( $_REQUEST ['wert'] );
				$ms->sonderabzug_speichern ( $_REQUEST ['jahr'], $_REQUEST ['merkmal'], $betrag, $_REQUEST ['a_klasse'], null );
				$jahr = $_REQUEST ['jahr'];
				weiterleiten ( "?daten=mietspiegel&option=mietspiegel_anzeigen&jahr=$jahr" );
			}
		}
		break;
	
	case "ms_wert_del" :
		if (isset ( $_REQUEST ['dat'] ) && ! empty ( $_REQUEST ['dat'] )) {
			$ms = new mietspiegel ();
			$dat = $_REQUEST ['dat'];
			$ms->ms_wert_loeschen ( $dat );
			weiterleiten ( '?daten=mietspiegel&option=mietspiegelliste' );
		}
		break;
	
	case "del_sonderabzug" :
		if (isset ( $_REQUEST ['dat'] ) && ! empty ( $_REQUEST ['dat'] )) {
			$ms = new mietspiegel ();
			$dat = $_REQUEST ['dat'];
			$ms->ms_sonderabzug_loeschen ( $dat );
			weiterleiten ( '?daten=mietspiegel&option=mietspiegelliste' );
		}
		break;
}

?>