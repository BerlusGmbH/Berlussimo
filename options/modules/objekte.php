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
 * @filesource   $HeadURL: http://192.168.2.52/svn/berlussimo_1/tags/02.11.2010 - Downloadversion 0.27/options/modules/objekte.php $
 * @version      $Revision: 15 $
 * @modifiedby   $LastChangedBy: sivac $
 * @lastmodified $Date: 2011-07-07 10:41:33 +0200 (Do, 07 Jul 2011) $
 * 
 */

/* Allgemeine Funktionsdatei laden */
include_once ("includes/allgemeine_funktionen.php");

/* �berpr�fen ob Benutzer Zugriff auf das Modul hat */
if (! check_user_mod ( $_SESSION ['benutzer_id'], 'objekte_raus' )) {
	echo '<script type="text/javascript">';
	echo "alert('Keine Berechtigung')";
	echo '</script>';
	die ();
}

/* Klasse "formular" f�r Formularerstellung laden */
include_once ("classes/class_formular.php");

/* Modulabh�ngige Dateien d.h. Links und eigene Klasse */
include_once ("options/links/links.form_objekte.php");

$daten = $_REQUEST ["daten"];
$objekte_raus = $_REQUEST ["objekte_raus"];

switch ($objekte_raus) {
	
	case "objekte_kurz" :
		$form = new formular ();
		$form->erstelle_formular ( "Liste bestehender Objekte", NULL );
		objekte_kurz ();
		$form->ende_formular ();
		break;
	
	case "objekt_anlegen" :
		$o = new objekt ();
		$o->form_objekt_anlegen ();
		break;
	
	case "objekt_speichern" :
		if ($_POST) {
			if (! empty ( $_POST [objekt_kurzname] ) && ! empty ( $_POST [eigentuemer] )) {
				echo "ALLES OK";
				$o = new objekt ();
				$o->objekt_speichern ( $_POST [objekt_kurzname], $_POST [eigentuemer] );
				weiterleiten ( '?daten=objekte_raus&objekte_raus=objekte_kurz' );
			}
		} else {
			echo "DATEN UNVOLLST�NDIG";
		}
		break;
	
	case "objekt_aendern" :
		$o = new objekt ();
		$o->form_objekt_aendern ( $_REQUEST [objekt_id] );
		break;
	
	case "objekt_aendern_send" :
		print_req ();
		if ($_POST) {
			if (! empty ( $_POST [objekt_dat] ) && ! empty ( $_POST [objekt_id] ) && ! empty ( $_POST [objekt_kurzname] ) && ! empty ( $_POST [eigentuemer] )) {
				echo "ALLES OK";
				$o = new objekt ();
				$o->objekt_aendern ( $_POST [objekt_dat], $_POST [objekt_id], $_POST [objekt_kurzname], $_POST [eigentuemer] );
				weiterleiten ( '?daten=objekte_raus&objekte_raus=objekte_kurz' );
			}
		} else {
			echo "DATEN UNVOLLST�NDIG";
		}
		break;
	
	case "checkliste" :
		if (! empty ( $_REQUEST [objekt_id] )) {
			$o = new objekt ();
			$o->pdf_checkliste ( $_REQUEST [objekt_id] );
		} else {
			echo "Objekt ausw�hlen";
		}
		break;
	
	case "mietaufstellung" :
		if (! empty ( $_REQUEST ['objekt_id'] )) {
			$o = new objekt ();
			$o->pdf_mietaufstellung ( $_REQUEST ['objekt_id'] );
		} else {
			echo "Objekt ausw�hlen";
		}
		break;
	
	case "mietaufstellung_m_j" :
		if (! empty ( $_REQUEST ['objekt_id'] )) {
			$objekt_id = $_REQUEST ['objekt_id'];
			if (! empty ( $_REQUEST ['monat'] ) && ! empty ( $_REQUEST ['jahr'] )) {
				$monat = $_REQUEST ['monat'];
				$jahr = $_REQUEST ['jahr'];
				$o = new objekt ();
				$o->pdf_mietaufstellung_m_j ( $objekt_id, $monat, $jahr );
			} else {
				echo "Monat und Jahr w�hlen";
			}
		}
		break;
	
	case "mietaufstellung_j" :
		if (! empty ( $_REQUEST ['objekt_id'] )) {
			$objekt_id = $_REQUEST ['objekt_id'];
			if (! empty ( $_REQUEST ['jahr'] )) {
				$jahr = $_REQUEST ['jahr'];
				$o = new objekt ();
				$o->pdf_mietaufstellung_j ( $objekt_id, $jahr );
			} else {
				echo "Monat und Jahr w�hlen";
			}
		}
		break;
	
	case "objekt_kopieren" :
		$o = new objekt ();
		$o->form_objekt_kopieren ();
		break;
	
	case "copy_sent" :
		echo "SIVAC";
		print_req ();
		// die();
		/*
		 * Neues Objekt anlegen
		 * alle einheiten kopieren und umbenennen mit vorzeichen
		 */
		if (! empty ( $_POST ['objekt_id'] ) && ! empty ( $_POST ['objekt_kurzname'] ) && ! empty ( $_POST ['vorzeichen'] ) && ! empty ( $_POST ['eigentuemer_id'] ) && ! empty ( $_POST ['datum_u'] )) {
			$objekt_id = $_POST ['objekt_id'];
			$objekt_kurzname = $_POST ['objekt_kurzname'];
			$vorzeichen = $_POST ['vorzeichen'];
			$datum_u = $_POST ['datum_u'];
			$eigentuemer_id = $_POST ['eigentuemer_id'];
			$o = new objekt ();
			if (isset ( $_POST ['saldo_berechnen'] )) {
				$o->objekt_kopieren ( $objekt_id, $eigentuemer_id, $objekt_kurzname, $vorzeichen, $datum_u, 1 );
			} else {
				$o->objekt_kopieren ( $objekt_id, $eigentuemer_id, $objekt_kurzname, $vorzeichen, $datum_u, 0 );
			}
		} else {
			fehlermeldung_ausgeben ( "Bitte alle felder ausf�llen!" );
		}
		break;
	
	/* Sollmieten Zeitraum Formular */
	case "sollmieten_zeitraum_form" :
		$f = new formular ();
		$f->erstelle_formular ( 'Vereinbarte Nettosollmieten f�r Zeitraum', null );
		// $f->fo
		// $mv = new mietvertraege();
		// $mv->mieten_tabelle(4, '2011-12-01', '2012-11-31');
		// $mv->mieten_pdf(4, '2011-12-01', '2012-11-30');
		echo "sanel";
		break;
	
	/* Sollmieten Zeitraum */
	case "sollmieten_zeitraum" :
		$mv = new mietvertraege ();
		// $mv->mieten_tabelle(4, '2011-12-01', '2012-11-31'); //XLS
		$mv->mieten_pdf ( $_SESSION ['objekt_id'], '2013-05-01', '2013-12-31' );
		break;
	
	/* Ist,ieten Zeitraum */
	case "istmieten_zeitraum" :
		$mv = new mietvertraege ();
		// $mv->mieten_tabelle(4, '2011-12-01', '2012-11-31'); //XLS
		// $mv->ist_mieten_pdf($_SESSION['objekt_id'], '2013-05-01', '2013-12-31');
		$arr = $mv->istmieten_zeitraum ( $_SESSION ['geldkonto_id'], '2013-01-01', '2013-12-31', 80001 );
		$mv->pdf_istmieten ( $arr, '01.01.2013', '31.12.2013' );
		break;
	
	case "import" :
		$im = new import ();
		// $im->form_import_gfad('einheiten.csv');
		// $im->import_arr('flug24.csv');//gerichtstr
		// $im->import_arr('qui130.csv');//gquitzow130
		// $im->import_arr('elsen108.csv');//elsen 108
		// $im->import_arr('kast52.csv');//kastanien52
		// $im->import_arr('sto42.csv');//strom42
		// $im->import_arr('eberty43.csv');//eberty43
		// $im->import_arr('boe35.csv');//boediker 35ab
		$im->import_arr ( 'fot31.csv' ); // fontane31
		break;
	
	/* IMPORT GFAD */
	case "einheit_speichern" :
		$e = new einheit ();
		$kurzname = $_POST ['kurzname'];
		$lage = $_POST ['lage'];
		$qm = $_POST ['qm'];
		$haus_id = $_POST ['haus_id'];
		$typ = $_POST ['typ'];
		$einheit_id = $e->einheit_speichern ( $kurzname, $lage, $qm, $haus_id, $typ );
		
		if (! empty ( $_POST ['weg_qm'] )) {
			$qm = $_POST ['weg_qm'];
			$d = new detail ();
			$d->detail_speichern_2 ( 'EINHEIT', $einheit_id, 'WEG-Fl�che', $_POST ['weg_qm'], 'Importiert' );
		}
		
		if (! empty ( $_POST ['weg_mea'] )) {
			$d = new detail ();
			$d->detail_speichern_2 ( 'EINHEIT', $einheit_id, 'WEG-Anteile', $_POST ['weg_mea'], 'Importiert' );
		}
		
		$weg = new weg ();
		$ihr = nummer_punkt2komma ( 0.4 * nummer_komma2punkt ( $qm ) );
		$weg->wohngeld_def_speichern ( '01.01.2014', '00.00.0000', $ihr, 'Instandhaltungsr�cklage', 6030, 'Hausgeld', 6000, $einheit_id );
		$weg->wohngeld_def_speichern ( '01.01.2014', '00.00.0000', 30, 'WEG-Verwaltergeb�hr', 6060, 'Hausgeld', 6000, $einheit_id );
		
		weiterleiten ( "index.php?daten=objekte_raus&objekte_raus=import" );
		break;
	
	case "person_speichern" :
		$p = new personen ();
		$geb_dat = $_POST ['geburtsdatum'];
		$nachname = $_POST ['nachname'];
		$vorname = $_POST ['vorname'];
		$geschlecht = $_POST ['geschlecht'];
		$telefon = $_POST ['telefon'];
		$handy = $_POST ['handy'];
		$email = $_POST ['email'];
		$person_id = $p->save_person ( $nachname, $vorname, $geb_dat, $geschlecht, $telefon, $handy, $email );
		$p_typ = $_POST ['p_typ']; // Mieter oder ET
		$einheit_id = $_POST ['einheit_id'];
		if ($p_typ == 'ET') {
			$et_seit = $_POST ['et_seit'];
			echo "ET $einheit_id $person_id $et_seit";
			$im = new import ();
			$von = date_german2mysql ( $et_seit );
			if ($im->get_last_eigentuemer_id ( $einheit_id ) != false) {
				$et_id = $im->get_last_eigentuemer_id ( $einheit_id );
			} else {
				$et_id = $im->et_erstellen ( $einheit_id, $von );
			}
			if (! empty ( $person_id ) && $person_id != 0) {
				$im->et_person_hinzu ( $et_id, $person_id );
			}
		}
		
		weiterleiten ( "index.php?daten=objekte_raus&objekte_raus=import" );
		break;
	
	case "person_et" :
		print_req ();
		$p_typ = $_POST ['p_typ']; // Mieter oder ET
		$einheit_id = $_POST ['einheit_id'];
		$person_id = $_POST ['name_g'];
		if ($p_typ == 'ET') {
			$et_seit = $_POST ['et_seit'];
			echo "ET $einheit_id $person_id $et_seit";
			$im = new import ();
			$von = date_german2mysql ( $et_seit );
			if ($im->get_last_eigentuemer_id ( $einheit_id ) != false) {
				$et_id = $im->get_last_eigentuemer_id ( $einheit_id );
			} else {
				$et_id = $im->et_erstellen ( $einheit_id, $von );
			}
			$im->et_person_hinzu ( $et_id, $person_id );
		}
		weiterleiten ( "index.php?daten=objekte_raus&objekte_raus=import" );
		break;
	
	case "stammdaten_pdf" :
		if (isset ( $_REQUEST ['objekt_id'] ) && ! empty ( $_REQUEST ['objekt_id'] )) {
			$_SESSION ['objekt_id'] = $_REQUEST ['objekt_id'];
			$pdf = new Cezpdf ( 'a4', 'portrait' );
			$oo = new objekt ();
			$oo->get_objekt_infos ( $_SESSION ['objekt_id'] );
			
			$bpdf = new b_pdf ();
			$bpdf->b_header ( $pdf, 'Partner', $_SESSION ['partner_id'], 'portrait', 'pdfclass/fonts/Helvetica.afm', 6 );
			
			$st = new stammdaten ();
			/* Objektstammdaten */
			$st->pdf_stamm_objekt ( $pdf, $_REQUEST ['objekt_id'] );
			
			ob_clean ();
			$pdf_opt ['Content-Disposition'] = "Stammdaten_" . $oo->objekt_kurzname . '_' . date ( "d.m.Y" ) . '.pdf';
			$pdf->ezStream ( $pdf_opt );
		} else {
			fehlermeldung_ausgeben ( "Objekt w�hlen" );
		}
		break;
	
	case "mv_speichern" :
		// print_req();
		// die();
		$mv = new mietvertraege ();
		$einheit_id = $_POST ['einheit_id'];
		$von = $_POST ['einzug'];
		$bis = $_POST ['auszug'];
		$mv_id = $mv->mietvertrag_speichern ( $von, $bis, $einheit_id );
		
		$anz_p = count ( $_POST ['person_ids'] );
		for($a = 0; $a < $anz_p; $a ++) {
			$person_id = $_POST ['person_ids'] [$a];
			$mv->person_zu_mietvertrag ( $person_id, $mv_id );
		}
		
		$me = new mietentwicklung ();
		// $von = '2014-01-01';
		// $bis = '0000-00-00';
		$von = date_german2mysql ( $von );
		$bis = date_german2mysql ( $bis );
		if (! empty ( $_POST ['km'] )) {
			$km = nummer_komma2punkt ( $_POST ['km'] );
			$me->me_speichern ( 'MIETVERTRAG', $mv_id, 'Miete kalt', $von, $bis, $km, '0.00' );
		}
		if (! empty ( $_POST ['nk'] )) {
			$nk = nummer_komma2punkt ( $_POST ['nk'] );
			$me->me_speichern ( 'MIETVERTRAG', $mv_id, 'Nebenkosten Vorauszahlung', $von, $bis, $nk, '0.00' );
		}
		if (! empty ( $_POST ['hk'] )) {
			$hk = nummer_komma2punkt ( $_POST ['hk'] );
			$me->me_speichern ( 'MIETVERTRAG', $mv_id, 'Heizkosten Vorauszahlung', $von, $bis, $hk, '0.00' );
		}
		
		if (! empty ( $_POST ['kabel_tv'] )) {
			$kabel_tv = nummer_komma2punkt ( $_POST ['kabel_tv'] );
			$me->me_speichern ( 'MIETVERTRAG', $mv_id, 'Kabel TV', $von, $bis, $kabel_tv, '0.00' );
		}
		$jahr_3 = date ( "Y" ) - 3;
		$m_day = date ( "m-d" );
		$datum_3 = "$jahr_3-$m_day";
		if (! empty ( $_POST ['km_3'] )) {
			$km_3 = nummer_komma2punkt ( $_POST ['km_3'] );
			$me->me_speichern ( 'MIETVERTRAG', $mv_id, 'Miete kalt', $datum_3, $datum_3, $kabel_tv, '0.00' );
		}
		
		if (! empty ( $_POST ['kaution'] )) {
			$d = new detail ();
			$d->detail_speichern_2 ( 'MIETVERTRAG', $mv_id, 'Kautionshinweis', $_POST ['kaution'], 'Importiert' );
		}
		
		if (! empty ( $_POST ['klein_rep'] )) {
			$d = new detail ();
			$d->detail_speichern_2 ( 'MIETVERTRAG', $mv_id, 'Kleinreparaturen', $_POST ['klein_rep'], 'Importiert' );
		}
		
		if (! empty ( $_POST ['zusatzinfo'] )) {
			$d = new detail ();
			$d->detail_speichern_2 ( 'MIETVERTRAG', $mv_id, 'Zusatzinfo', $_POST ['zusatzinfo'], 'Importiert' );
		}
		
		/*
		 * if(!empty($_POST['saldo_vv'])){
		 * $saldo_vv = $_POST['saldo_vv'];
		 * $datum_vv = '2013-12-31';
		 * $me->me_speichern('MIETVERTRAG', $mv_id, 'Saldo Vortrag Vorverwaltung', $datum_vv, $datum_vv, $saldo_vv, '0.00');
		 * }
		 */
		
		weiterleiten ( "index.php?daten=objekte_raus&objekte_raus=import" );
		break;
}
function objekte_kurz() {
	$db_abfrage = "SELECT OBJEKT_ID, OBJEKT_KURZNAME FROM OBJEKT WHERE OBJEKT_AKTUELL='1' ORDER BY OBJEKT_KURZNAME";
	$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
	
	$numrows = mysql_numrows ( $resultat );
	if ($numrows < 1) {
		// echo "<h1><b>Keine Objekte vorhanden!!!</b></h1>\n";
	} else {
		
		// echo "<table class=\"sortable\">\n";
		// echo "<tr class=\"feldernamen\"><td colspan=4>Objektliste</td></tr>\n";
		// echo "<tr class=\"feldernamen\"><td width=200>Objektname</td><td width=100>Gesamtfl�che</td><td colspan=2>Zusatzinformationen</td></tr>\n";
		// echo "</table>";
		iframe_start ();
		echo "<table class=\"sortable\">\n";
		echo "<tr><th>Objekt</th><th>FL�CHE</th><th>H�USER</th><th>Einheiten</th><th>INFOS</th><th colspan=\"9\"></th></tr>";
		$counter = 0;
		while ( list ( $OBJEKT_ID, $OBJEKT_KURZNAME ) = mysql_fetch_row ( $resultat ) ) {
			$anzahl_haeuser = anzahl_haeuser_im_objekt ( $OBJEKT_ID );
			$counter ++;
			$flaeche = nummer_punkt2komma ( objekt_flaeche ( $OBJEKT_ID ) );
			$detail_check = detail_check ( "OBJEKT", $OBJEKT_ID );
			if ($detail_check > 0) {
				$detail_link = "<a  href=\"?daten=details&option=details_anzeigen&detail_tabelle=OBJEKT&detail_id=$OBJEKT_ID\">Details</a>";
			} else {
				$detail_link = "<a href=\"?daten=details&option=details_hinzu&detail_tabelle=OBJEKT&detail_id=$OBJEKT_ID\">Neues Detail</a>";
			}
			$aendern_link = "<a href=\"?daten=objekte_raus&objekte_raus=objekt_aendern&objekt_id=$OBJEKT_ID\">�ndern</a>";
			$haus_neu_link = "<a href=\"index.php?formular=haus&daten_rein=haus_neu&objekt_id=$OBJEKT_ID\">Haus erstellen</a>";
			$check_liste_link = "<a href=\"?daten=objekte_raus&objekte_raus=checkliste&objekt_id=$OBJEKT_ID\">Checkliste HW</a>";
			$mietaufstellung_link = "<a href=\"?daten=objekte_raus&objekte_raus=mietaufstellung&objekt_id=$OBJEKT_ID\">Mietaufstellung</a>";
			$monat = date ( "m" );
			$jahr = date ( "Y" );
			$mietaufstellung_link_m_j = "<a href=\"?daten=objekte_raus&objekte_raus=mietaufstellung_m_j&objekt_id=$OBJEKT_ID&monat=$monat&jahr=$jahr\">Mietaufstellung MJ</a>";
			$mietaufstellung_link_m_j_xls = "<a href=\"?daten=objekte_raus&objekte_raus=mietaufstellung_m_j&objekt_id=$OBJEKT_ID&monat=$monat&jahr=$jahr&XLS\">Mietaufstellung MJ-XLS</a>";
			
			$alle_mietkontenblatt_link = "<a href=\"?daten=mietkonten_blatt&anzeigen=alle_mkb&objekt_id=$OBJEKT_ID\">Alle MKB-PDF</a>";
			$link_mieterliste = "<a href=\"?daten=einheit_raus&einheit_raus=mieterliste_aktuell&objekt_id=$OBJEKT_ID\">Mieterliste PDF</a>";
			$link_mieteremail = "<a href=\"?daten=einheit_raus&einheit_raus=mieteremail_aktuell&objekt_id=$OBJEKT_ID\">Mieter-Email</a>";
			$link_stammdaten = "<a href=\"?daten=objekte_raus&objekte_raus=stammdaten_pdf&objekt_id=$OBJEKT_ID\"><img src=\"css/pdf.png\"></a>";
			$vorjahr = date ( "Y" ) - 1;
			$link_sollist = "<a href=\"?daten=objekte_raus&objekte_raus=mietaufstellung_j&objekt_id=$OBJEKT_ID&jahr=$vorjahr\">SOLL/IST $vorjahr</a>";
			echo "<tr class=\"zeile$counter\"><td>$OBJEKT_KURZNAME<br>$link_stammdaten</td><td>$flaeche m�</td><td sorttable_customkey=\"$anzahl_haeuser\"><a  href=\"?daten=haus_raus&haus_raus=haus_kurz&objekt_id=$OBJEKT_ID\">H�userliste (<b>$anzahl_haeuser</b>)</a>  $haus_neu_link</td><td><a href=\"?daten=einheit_raus&einheit_raus=einheit_kurz&objekt_id=$OBJEKT_ID\">Einheitenliste</a></td><td>$detail_link</td><td>$aendern_link</td><td>$check_liste_link</td><td>$mietaufstellung_link</td><td>$mietaufstellung_link_m_j</td><td>$mietaufstellung_link_m_j_xls</td><td>$alle_mietkontenblatt_link</td><td>$link_mieterliste</td><td>$link_mieteremail</td><td>$link_sollist</td></tr>";
			
			if ($counter == 2) {
				$counter = 0;
			}
		}
		echo "</table>";
		iframe_end ();
	}
}
function objekt_flaeche($objekt_id) {
	// $sql = 'SELECT SUM(HAUS_QM) AS Summe FROM HAUS';
	$db_abfrage = "SELECT SUM(HAUS_QM) AS SUMME FROM HAUS WHERE OBJEKT_ID='$objekt_id'";
	$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
	while ( list ( $SUMME ) = mysql_fetch_row ( $resultat ) )
		return $SUMME;
}
function objekt_wohnflaeche($objekt_id) {
	// $sql = 'SELECT SUM(HAUS_QM) AS Summe FROM HAUS';
	$db_abfrage = "SELECT SUM(HAUS_QM) AS SUMME FROM HAUS WHERE OBJEKT_ID='$objekt_id'";
	$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
	while ( list ( $SUMME ) = mysql_fetch_row ( $resultat ) )
		return $SUMME;
}

// ?daten=einheit_raus&einheit_raus=einheit_kurz&objekt_id=4
?>
