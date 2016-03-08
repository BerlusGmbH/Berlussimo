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
 * @filesource   $HeadURL: http://192.168.2.52/svn/berlussimo_1/tags/02.11.2010 - Downloadversion 0.27/options/modules/katalog.php $
 * @version      $Revision: 15 $
 * @modifiedby   $LastChangedBy: sivac $
 * @lastmodified $Date: 2011-07-07 10:41:33 +0200 (Do, 07 Jul 2011) $
 * 
 */

/* Allgemeine Funktionsdatei laden */
include_once ("includes/allgemeine_funktionen.php");

/* überprüfen ob Benutzer Zugriff auf das Modul hat */
if (! check_user_mod ( $_SESSION ['benutzer_id'], 'katalog' )) {
	echo '<script type="text/javascript">';
	echo "alert('Keine Berechtigung')";
	echo '</script>';
	die ();
}

/* Klasse "formular" für Formularerstellung laden */
include_once ("classes/class_formular.php");

/* Modulabhängige Dateien d.h. Links und eigene Klasse */
//include_once ("options/links/links.katalog.php");
include_once ("classes/class_katalog.php");

if (isset ( $_REQUEST ['option'] ) && ! empty ( $_REQUEST ['option'] )) {
	$option = $_REQUEST ["option"];
} else {
	$option = 'default';
}

/* Optionsschalter */
switch ($option) {
	
	default :
		echo "Weitere Auswahl treffen!";
		if (! empty ( $_SESSION ['partner_id'] )) {
			$p = new partners ();
			$p_id = $_SESSION ['partner_id'];
			$p->get_partner_info ( $p_id );
			echo "<h3>Preisentwicklung im Katalog von $p->partner_name</h3>";
		}
		break;
	
	case "katalog_anzeigen" :
		if (empty ( $_SESSION ['partner_id'] )) {
			$k = new katalog ();
			$k->partner_auswahl_menue ( '?daten=katalog' );
			die ();
		}
		$p_id = $_SESSION ['partner_id'];
		include_once ('classes/class_partners.php');
		$p = new partners ();
		$p->get_partner_info ( $p_id );
		echo "<h3>Katalog von $p->partner_name</h3>";
		
		$k = new katalog ();
		$k->katalog_artikel_anzeigen ( $p_id );
		break;
	
	case "preisentwicklung" :
		if (empty ( $_REQUEST ['lieferant'] ) && empty ( $_SESSION ['partner_id'] ) && empty ( $_REQUEST ['artikel_nr'] )) {
			echo "Erst Lieferanten wählen";
			die ();
		}
		if (empty ( $_REQUEST ['lieferant'] )) {
			$p_id = $_SESSION ['partner_id'];
		} else {
			$p_id = $_REQUEST ['lieferant'];
		}
		
		include_once ('classes/class_partners.php');
		$p = new partners ();
		$p->get_partner_info ( $p_id );
		echo "<h3>Preisentwicklung im Katalog von $p->partner_name</h3>";
		
		if (empty ( $_REQUEST ['artikel_nr'] )) {
			$k = new katalog ();
			$k->form_preisentwicklung ();
		} else {
			$k = new katalog ();
			$artikel_nr = $_REQUEST ['artikel_nr'];
			$k->preisentwicklung_anzeigen ( $p_id, $artikel_nr );
		}
		break;
	
	case "partner_wechseln" :
		unset ( $_SESSION ['partner_id'] );
		hinweis_ausgeben ( "Lieferantauswahl aufgehoben, Sie werden weitergeleitet" );
		weiterleiten_in_sec ( '?daten=katalog', 2 );
		break;
	
	case "artikelsuche" :
		$k = new katalog ();
		$k->artikel_suche_einkauf_form ();
		break;
	
	case "artikel_suche" :
		if (! empty ( $_REQUEST ['artikel_nr'] )) {
			$artikel_nr = $_REQUEST ['artikel_nr'];
			$k = new katalog ();
			$k->artikel_suche_einkauf ( $artikel_nr );
		}
		break;
	
	case "artikelsuche_freitext" :
		$k = new katalog ();
		$k->artikel_suche_freitext_form ();
		break;
	
	case "artikel_suche_freitext" :
		if (! empty ( $_REQUEST ['artikel_nr'] )) {
			$artikel_nr = $_REQUEST ['artikel_nr'];
			$k = new katalog ();
			$k->artikel_suche_freitext ( $artikel_nr );
		}
		break;
	
	case "zuletzt_gekauft" :
		
		if (isset ( $_SESSION ['partner_id'] )) {
			$k = new katalog ();
			$k->form_zuletzt_gekauft ( $_SESSION ['partner_id'] );
			
			if (isset ( $_REQUEST ['art_anz'] )) {
				$arr_pos = $k->get_positionen_arr ( $_SESSION ['partner_id'], $_REQUEST ['art_anz'] );
			} else {
				$arr_pos = $k->get_positionen_arr ( $_SESSION ['partner_id'], 15 );
			}
			// echo '<pre>';
			// print_r($arr_pos);
			$anz_pos = count ( $arr_pos );
			echo "<table class=\"sortable\">";
			echo "<tr><th>RG</th><th>ARTIKEL</th><th>BEZ</th><th>MENGE</th><th>VE</th><th>BISHER</th><th>PREIS</th></tr>";
			for($a = 0; $a < $anz_pos; $a ++) {
				$art_nr = $arr_pos [$a] ['ARTIKEL_NR'];
				$partner_id = $arr_pos [$a] ['ART_LIEFERANT'];
				$r = new rechnung ();
				$art_arr = $r->artikel_info ( $partner_id, $art_nr );
				// echo '<pre>';
				// print_r($art_arr);
				$rg = $arr_pos [$a] ['BELEG_NR'];
				$menge = $arr_pos [$a] ['MENGE'];
				$preis = $arr_pos [$a] ['PREIS'];
				$ve = $art_arr [0] ['EINHEIT'];
				$bez = $art_arr [0] ['BEZEICHNUNG'];
				$link_rg = "<a href=\"?daten=rechnungen&option=rechnungs_uebersicht&belegnr=$rg\">zur Rg</a>";
				$anz_bisher = $k->get_anz_bisher ( $art_nr, $partner_id );
				echo "<tr><td>$link_rg</td><td>$art_nr</td><td>$bez</td><td>$menge</td><td>$ve</td><td>$anz_bisher</td><td>$preis</td></tr>";
			}
			echo "</table>";
		} else {
			fehlermeldung_ausgeben ( "Partner wählen!" );
		}
		
		break;
	
	case "meist_gekauft" :
		$k = new katalog ();
		$arr_pos = $k->get_meistgekauft_arr ( $_SESSION ['partner_id'] );
		$partner_id = $_SESSION ['partner_id'];
		/*
		 * echo '<pre>';
		 * print_r($arr);
		 */
		
		$anz_pos = count ( $arr_pos );
		echo "<table class=\"sortable\">";
		echo "<tr><th>RG</th><th>ARTIKEL</th><th>BEZ</th><th>MENGE</th><th>VE</th><th>BISHER</th><th>LPREIS</th><th>rabatt</th><th>UPREIS</th><th>ENT.</tr>";
		for($a = 0; $a < $anz_pos; $a ++) {
			$art_nr = $arr_pos [$a] ['ARTIKEL_NR'];
			$menge = $arr_pos [$a] ['G_MENGE'];
			$rg = $arr_pos [$a] ['BELEG_NR'];
			$r = new rechnung ();
			$art_arr = $r->artikel_info ( $partner_id, $art_nr );
			/*
			 * echo '<pre>';
			 * print_r($art_arr);
			 * die();
			 */
			$ve = $art_arr [0] ['EINHEIT'];
			$bez = $art_arr [0] ['BEZEICHNUNG'];
			$lp = $art_arr [0] ['LISTENPREIS'];
			$rabatt = $art_arr [0] ['RABATT_SATZ'];
			$up = nummer_punkt2komma_t ( ($lp / 100) * (100 - $rabatt) );
			$anz_bisher = $k->get_anz_bisher ( $art_nr, $partner_id );
			
			/* Preisentwicklungsinfos */
			$ka = new katalog ();
			$ka->get_preis_entwicklung_infos ( $_SESSION ['partner_id'], $art_nr );
			
			$link_rg = "<a href=\"?daten=rechnungen&option=rechnungs_uebersicht&belegnr=$rg\">zur Rg</a>";
			echo "<tr><td>$link_rg</td><td>$art_nr</td><td>$bez</td><td>$menge</td><td>$ve</td><td>$anz_bisher</td><td>$lp</td><td>$rabatt%</td><td>$up</td><td>$ka->vorzeichen" . "$ka->preis_diff%</td></tr>";
		}
		echo "</table>";
		
		break;
} // end switch

/*
 * Preisentwicklung
 * SELECT `LISTENPREIS` , (
 * LISTENPREIS /100
 * ) * ( 100 - `RABATT_SATZ` )
 * FROM `POSITIONEN_KATALOG`
 * WHERE ARTIKEL_NR = '025150102101'
 * ORDER BY (
 * LISTENPREIS /100
 * ) * ( 100 - `RABATT_SATZ` ) ASC
 * LIMIT 0 , 30
 *
 *
 * SELECT ARTIKEL_NR, BEZEICHNUNG, MIN( LISTENPREIS ) , MAX( LISTENPREIS )
 * FROM `POSITIONEN_KATALOG`
 * GROUP BY ARTIKEL_NR, LISTENPREIS
 * LIMIT 0 , 30
 *
 *
 */

?>
