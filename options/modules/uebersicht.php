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
 * @filesource   $HeadURL: http://192.168.2.52/svn/berlussimo_1/tags/02.11.2010 - Downloadversion 0.27/options/modules/uebersicht.php $
 * @version      $Revision: 15 $
 * @modifiedby   $LastChangedBy: sivac $
 * @lastmodified $Date: 2011-07-07 10:41:33 +0200 (Do, 07 Jul 2011) $
 * 
 */
include_once ("includes/allgemeine_funktionen.php");

/* überprüfen ob Benutzer Zugriff auf das Modul hat */
if (! check_user_mod ( $_SESSION ['benutzer_id'], 'uebersicht' )) {
	echo '<script type="text/javascript">';
	echo "alert('Keine Berechtigung')";
	echo '</script>';
	die ();
}

include_once ("includes/formular_funktionen.php");
include_once ("classes/berlussimo_class.php");
include_once ("classes/mietkonto_class.php");
include_once ("classes/class_kautionen.php");
/* Klasse "formular" für Formularerstellung laden */
include_once ("classes/class_formular.php");

if (isset ( $_REQUEST ['einheit_id'] )) {
	$einheit_id = $_REQUEST ['einheit_id'];
} elseif (isset ( $_REQUEST ['mietvertrag_id'] )) {
	$mietvertrag_id = $_REQUEST ['mietvertrag_id'];
} else {
	die ();
}
if (isset ( $_REQUEST ["daten"] )) {
	$daten = $_REQUEST ["daten"];
	$anzeigen = $_REQUEST ["anzeigen"];
	switch ($anzeigen) {
		
		case "einheit" :
			//$form = new formular ();
			//$form->erstelle_formular ( "Übersichtsseite", NULL );
			$e = new einheit ();
			if (is_array ( $e->get_mietvertrag_ids ( $einheit_id ) )) {
				uebersicht_einheit ( $einheit_id );
			} else {
				echo "<h2>BISHER LEERSTAND</h2>";
				$e->uebersicht_einheit_leer ( $einheit_id );
			}
			//$form->ende_formular ();
			break;
	}
}

/* Neue Version zu Einheit oder Einheit und MV */
function uebersicht_einheit($einheit_id) {
	// echo "ES WIRD BEARBEITET - Hr. Sivac";
	if (! empty ( $_REQUEST ['mietvertrag_id'] )) {
		$mietvertrag_id = $_REQUEST ['mietvertrag_id'];
		$mv = new mietvertraege ();
		$mv->get_mietvertrag_infos_aktuell ( $mietvertrag_id );
		$einheit_id = $mv->einheit_id;
		$e = new einheit ();
		$e->get_einheit_info ( $einheit_id );
	} else {
		$e = new einheit ();
		$e->get_einheit_info ( $einheit_id );
		$mietvertrag_id = $e->get_last_mietvertrag_id ( $einheit_id );
		
		if (empty ( $mietvertrag_id )) {
			die ( 'Keine Informationen, weil keine Vormietverträge' );
		}
		$mv = new mietvertraege ();
		$mv->get_mietvertrag_infos_aktuell ( $mietvertrag_id );
	}
	/*
	 * echo '<pre>';
	 * print_r($mv);
	 * print_r($e);
	 * echo '</pre>';
	 */
	// ################################## BALKEN EINHEIT---->
	
	$weg = new weg ();
	// $et_arr = $weg->get_eigentuemer_arr($einheit_id);
	$weg->get_last_eigentuemer ( $einheit_id );
	if (isset ( $weg->eigentuemer_id )) {
		$e_id = $weg->eigentuemer_id;
		// $weg->get_eigentumer_id_infos3($e_id);
		$weg->get_eigentuemer_namen_str ( $e_id );
		// $miteigentuemer_namen = strip_tags($weg->eigentuemer_name_str);
		$miteigentuemer_namen = $weg->eigentuemer_name_str_u;
		
		/* ################Betreuer################## */
		$anz_p = count ( $weg->eigentuemer_person_ids );
		$betreuer_str = '';
		$betreuer_arr;
		for($be = 0; $be < $anz_p; $be ++) {
			$et_p_id = $weg->eigentuemer_person_ids [$be];
			$d_k = new detail ();
			$dt_arr = $d_k->finde_alle_details_grup ( 'PERSON', $et_p_id, 'INS-Kundenbetreuer' );
			
			if (is_array ( $dt_arr )) {
				$anz_bet = count ( $dt_arr );
				for($bet = 0; $bet < $anz_bet; $bet ++) {
					$bet_str = $dt_arr [$bet] ['DETAIL_INHALT'];
					$betreuer_str .= "$bet_str<br>";
					$betreuer_arr [] = $bet_str;
				}
			} else {
				// $betreuer_str .= "<b>KEINE BET</b>";
			}
		}
		
		if (is_array ( $betreuer_arr )) {
			$betreuer_str = '';
			$betreuer_arr1 = array_unique ( $betreuer_arr );
			for($bbb = 0; $bbb < count ( $betreuer_arr1 ); $bbb ++) {
				$betreuer_str .= $betreuer_arr1 [$bbb];
			}
		}
	} else {
		$miteigentuemer_namen = "UNBEKANNT";
	}
	
	// echo '<pre>';
	// print_r($weg);
	
	// echo '<pre>';
	// print_r($weg);
	echo "<div class='container-fluid'>";
    echo "<div class='row panel'>";
    $link_neuer_auftrag_int = "<a href=\"?daten=todo&option=neues_projekt&typ=Benutzer&kos_typ=Einheit&kos_id=$einheit_id\"><button class='btn btn-default btn-berlus'>Interner Auftrag</button></a>";
    $link_neuer_auftrag_ext = "<a href=\"?daten=todo&option=neues_projekt&typ=Partner&kos_typ=Einheit&kos_id=$einheit_id\"><button class='btn btn-default btn-berlus'>Externer Auftrag</button></a>";
    echo "<div>$link_neuer_auftrag_int $link_neuer_auftrag_ext</div>";
    echo "</div>";
    echo "<div class='row'>";

    ##### OBJEKT #####

    echo "<div class='berlus-overview col-lg-2'>";
    echo "<div class='panel panel-default'>";
    $details_info = new details ();
    $objekt_details_arr = $details_info->get_details ( 'OBJEKT', $e->objekt_id );
    echo "<div class='panel-heading'><h3 class='panel-title'>OBJEKT: $e->objekt_name</h3></div>";
    echo "<div class='panel-body'>";
    for($i = 0; $i < count ( $objekt_details_arr ); $i ++) {
        echo "<b>" . $objekt_details_arr [$i] ['DETAIL_NAME'] . "</b><br>" . $objekt_details_arr [$i] ['DETAIL_INHALT'] . "<br>";
    }
    $oo = new objekt ();
    $oo->get_objekt_infos ( $e->objekt_id );
    echo "<hr><span class=\"warnung\">OBJEKT-ET: $oo->objekt_eigentuemer</span><hr>";
    $link_objekt_details = "<a href=\"?daten=details&option=details_hinzu&detail_tabelle=OBJEKT&detail_id=$e->objekt_id\">NEUES DETAIL ZUM OBJEKT $e->objekt_name</a>";
    echo "$link_objekt_details";
    echo "</div></div></div>";

    ##### ENDE OBJEKT #####

    ##### EINHEIT #########

    echo "<div class='berlus-overview col-lg-2'>";
    echo "<div class='panel panel-default'>";
    echo "<div class='panel-heading'><h3 class='panel-title'>EINHEIT: $e->einheit_kurzname</h3></div>";
    echo "<div class=\"panel-body\">";

	echo "<p class=\"warnung\">WEG-ET:<br>$miteigentuemer_namen</p><hr>";
	if (isset ( $betreuer_str )) {
		echo "<p style=\"color:green;font-size:10px;\"><u><b>BETREUER</b></u>:<br>$betreuer_str</p><hr>";
	}
	echo "$e->haus_strasse $e->haus_nummer<br/>";
	echo "$e->haus_plz $e->haus_stadt<br/>";
	echo "<hr><a href=\"?index.php&daten=todo&option=auftrag_haus&haus_id=$e->haus_id&einheit_id=$einheit_id\">Aufträge an Haus</a><hr>";
	echo "Lage: $e->einheit_lage QM: $e->einheit_qm m² <b>TYP:$e->typ</b><hr>";
	$war = new wartung ();
	$war->wartungen_anzeigen ( $e->einheit_kurzname );
	echo "<hr>";
	
	// print_r($e);
	$details_info = new details ();
	$einheit_details_arr = $details_info->get_details ( 'EINHEIT', $einheit_id );
	if (count ( $einheit_details_arr ) > 0) {
		echo "<b>AUSSTATTUNG</b><hr>";
		for($i = 0; $i < count ( $einheit_details_arr ); $i ++) {
			/* Expose bzw. Vermietungsdetails filtern */
			if (stripos ( $einheit_details_arr [$i] ['DETAIL_NAME'], 'Vermietung' ) === false) {
				if (stripos ( $einheit_details_arr [$i] ['DETAIL_NAME'], 'Expose' ) === false) {
					echo "<b>" . $einheit_details_arr [$i] ['DETAIL_NAME'] . "</b>:<br>" . $einheit_details_arr [$i] ['DETAIL_INHALT'] . "<br>";
				}
			}
		}
	} else {
		echo "k.A zur Ausstattung";
	}
	$link_einheit_details = "<a href=\"?daten=details&option=details_hinzu&detail_tabelle=EINHEIT&detail_id=$einheit_id\">NEUES DETAIL ZUR EINHEIT $e->einheit_kurzname</a>";
	echo "<hr>$link_einheit_details";
    echo "</div>";
    echo "</div></div>";
	#### ende einheit ####
	
	// ######## MIETER

    $mv->personen_name_string_u3 = str_replace ( ',', '', $mv->personen_name_string_u2 );
    echo "<div class='berlus-overview col-lg-2'>";
    echo "<div class='panel panel-default'>";
    echo "<div class='panel-heading'><h3 class='panel-title'>MIETER: $mv->personen_name_string_u3</h3></div>";
    echo "<div class=\"panel-body\">";
	//echo "<div class='col-lg-2'><span class=\"font_balken_uberschrift\">MIETER<br> ($mv->personen_name_string_u3)</span><hr />";
	// echo "Personen im MV: $anzahl_personen_im_mv";
	if ($mv->anzahl_personen < 1) {
		echo "leer";
	}
	// ####INFOS ÜBER PERSON/MIETER
	$person_info = new person ();
	for($i = 0; $i < $mv->anzahl_personen; $i ++) {
		$person_info->get_person_infos ( $mv->personen_ids [$i] ['PERSON_MIETVERTRAG_PERSON_ID'] );
		$akt_person_id = $mv->personen_ids [$i] ['PERSON_MIETVERTRAG_PERSON_ID'];
		$person_info->get_person_anzahl_mietvertraege_aktuell ( $mv->personen_ids [$i] ['PERSON_MIETVERTRAG_PERSON_ID'] );
		$person_anzahl_mvs = $person_info->person_anzahl_mietvertraege;
		$person_nachname = $person_info->person_nachname;
		$person_vorname = $person_info->person_vorname;
		$person_geburtstag = $person_info->person_geburtstag;
		// $person_info2 = $person_info->get_person_anzahl_mietvertraege_alt($personen_ids_arr[$i]['PERSON_MIETVERTRAG_PERSON_ID']);
		// $person_anzahl_mietvertraege_alt = $person_info->person_anzahl_mietvertraege_alt;
		$person_mv_id_array = $person_info->get_vertrags_ids_von_person ( $mv->personen_ids [$i] ['PERSON_MIETVERTRAG_PERSON_ID'] );
		$zeile = $i + 1;
		$mieternamen_str = "<b>$zeile. $person_nachname, $person_vorname</b><br> geb. am: $person_geburtstag<br>";
		$aktuelle_einheit_link = "";
		$alte_einheit_link = "";
		// ####DETAILS VOM MIETER
		$details_info_mieter = new details ();
		$mieter_details_arr = $details_info_mieter->get_details ( 'PERSON', $mv->personen_ids [$i] ['PERSON_MIETVERTRAG_PERSON_ID'] );
		$mieter_details = "";
		for($p = 0; $p < count ( $mieter_details_arr ); $p ++) {
			$mieter_details .= "<b>" . $mieter_details_arr [$p] ['DETAIL_NAME'] . "</b><br>" . $mieter_details_arr [$p] ['DETAIL_INHALT'] . "<br>";
		}
		
		for($a = 0; $a < count ( $person_mv_id_array ); $a ++) {
			$person_info2 = new person ();
			$mv_status = $person_info2->get_vertrags_status ( $person_mv_id_array [$a] ['PERSON_MIETVERTRAG_MIETVERTRAG_ID'] );
			$mietvertrag_info2 = new mietvertrag ();
			$p_einheit_id = $mietvertrag_info2->get_einheit_id_von_mietvertrag ( $person_mv_id_array [$a] ['PERSON_MIETVERTRAG_MIETVERTRAG_ID'] );
			$p_einheit_kurzname = $mietvertrag_info2->einheit_kurzname;
			
			if ($mv_status == TRUE) {
				// echo "".$person_mv_id_array[$a]['PERSON_MIETVERTRAG_MIETVERTRAG_ID']." TRUE $p_einheit_id $p_einheit_kurzname";
				// if($einheit_id != $p_einheit_id){
				// echo "<a href=\"?daten=uebersicht&anzeigen=einheit&einheit_id=$p_einheit_id\">$p_einheit_kurzname</a>&nbsp; ";
				// }
				$aktuelle_einheit_link .= "<a href=\"?daten=uebersicht&anzeigen=einheit&einheit_id=$p_einheit_id\">$p_einheit_kurzname</a>&nbsp; ";
			} else {
				// $alte_einheit_link = "".$person_mv_id_array[$a]['PERSON_MIETVERTRAG_MIETVERTRAG_ID']." FALSE";
				$alte_einheit_link = "<a href=\"?daten=uebersicht&anzeigen=einheit&einheit_id=$p_einheit_id\">$p_einheit_kurzname</a>&nbsp; ";
			}
		}
		echo "<br>$mieternamen_str";
		if (! empty ( $mieter_details )) {
			echo "<br>$mieter_details";
		}
		echo "<br>Anzahl Verträge:  $person_anzahl_mvs<br>";
		echo "Aktuelle Verträge:<br>";
		echo "$aktuelle_einheit_link<br>";
		if (! empty ( $alte_einheit_link )) {
			echo "Alte Verträge:<br>";
			echo "$alte_einheit_link<br>";
		}
		$link_person_details = "<a href=\"?daten=details&option=details_hinzu&detail_tabelle=PERSON&detail_id=$akt_person_id\">NEUES DETAIL ZU $person_info->person_nachname $person_info->person_vorname</a>";
		echo "<hr>$link_person_details<hr>";
	}
	
	// ######### LETZTER MIETER#########
	echo "VORMIETER:<br>";
	$vormieter_ids_array = $e->letzter_vormieter ( $einheit_id );
	if (! empty ( $vormieter_ids_array )) {
		for($b = 0; $b < count ( $vormieter_ids_array ); $b ++) {
			// echo $vormieter_ids_array[$b]['PERSON_MIETVERTRAG_PERSON_ID'];
			$person_info->get_person_infos ( $vormieter_ids_array [$b] ['PERSON_MIETVERTRAG_PERSON_ID'] );
			$person_nachname = $person_info->person_nachname;
			$person_vorname = $person_info->person_vorname;
			echo "$person_nachname $person_vorname<br>";
		}
	} else {
		echo "<p class=rot>Keine Vormieter</p>";
	}
	echo "<hr><a href=\"?daten=mietvertrag_raus&mietvertrag_raus=mietvertrag_kurz&einheit_id=$einheit_id\">Alle Mietverträge von $e->einheit_kurzname</a>";
	// echo "<pre>";
	// print_r($person_mv_id_array);
	// echo "</pre>";
	echo "</div>";
    echo "</div></div>";
	
	// #####BALKEN 3 VERTRAGSDATEN
    echo "<div class='berlus-overview col-lg-2'>";
    echo "<div class='panel panel-default'>";
    echo "<div class='panel-heading'><h3 class='panel-title'>VERTRAGSDATEN</h3></div>";
    echo "<div class=\"panel-body\">";
	//echo "<div class='col-lg-2'><span class=\"font_balken_uberschrift\">VERTRAGSDATEN</span><hr />";
	
	$mietvertrag_info = new mietvertrag ();
	$anzahl_mietvertraege = $mietvertrag_info->get_anzahl_mietvertrag_id_zu_einheit ( $einheit_id );
	$anzahl_mietvertraege = $mietvertrag_info->anzahl_mietvertraege_gesamt;
	
	if (! empty ( $mietvertrag_id )) {
		echo "<b><a href=\"?daten=mietkonten_blatt&anzeigen=mietkonto_uebersicht_detailiert&mietvertrag_id=$mietvertrag_id\">MIETKONTENBLATT</a></b><br>";
		echo "<b><a href=\"?daten=mietkonten_blatt&anzeigen=mietkonto_ab&mietvertrag_id=$mietvertrag_id\">MIETKONTENBLATT AB</a></b><br>";
	}
	if (! empty ( $mv->mietvertrag_von )) {
		$mietvertrag_von_datum = date_mysql2german ( $mv->mietvertrag_von );
		echo "EINZUG: <b>$mietvertrag_von_datum</b><br>";
	}
	if (! empty ( $mv->mietvertrag_bis )) {
		$mietvertrag_bis_datum = date_mysql2german ( $mv->mietvertrag_bis );
		if ($mietvertrag_bis_datum == '00.00.0000') {
			echo "AUSZUG: <b>ungekündigt</b><br>";
			echo "<a href=\"?daten=mietvertrag_raus&mietvertrag_raus=mietvertrag_beenden&mietvertrag_id=$mietvertrag_id\">VERTRAG BEENDEN</a><br>";
		} else {
			echo "<p class=auszug_bald>AUSZUG: $mietvertrag_bis_datum</p>";
		}
		
		$sep = new sepa ();
		$m_ref = 'MV' . $mietvertrag_id;
		if ($sep->check_m_ref_alle ( $m_ref )) {
			$sep->get_mandat_infos_mref ( $m_ref );
			// print_r($sep->mand);
			$d_heute = date ( "Ymd" );
			$enddatum_mandat = str_replace ( '-', '', $sep->mand->M_EDATUM );
			// echo $enddatum_mandat;
			if ($enddatum_mandat >= $d_heute) {
				echo "<hr><p style=\"color:green;\"><b>Gültiges SEPA-Mandat</b><br>";
				$konto_inh = $sep->mand->NAME;
				echo "<b>Kto-Inhaber:</b> $konto_inh<br>";
				$iban = $iban_1 = chunk_split ( $sep->mand->IBAN, 4, ' ' );
				$bic = $sep->mand->BIC;
				echo "<b>IBAN:</b> $iban<br>";
				echo "<b>BIC:</b> $bic<br>";
				$u_datum = date_mysql2german ( $sep->mand->M_UDATUM );
				$a_datum = date_mysql2german ( $sep->mand->M_ADATUM );
				$e_datum = date_mysql2german ( $sep->mand->M_EDATUM );
				echo "<b>Unterschrieben:</b> $u_datum<br>";
				echo "<b>Gültig ab:</b>      $u_datum<br>";
				echo "<b>Gültig bis:</b>     $e_datum<br>";
				$m_ein_art = $sep->mand->EINZUGSART;
				echo "<b>Einzugsart:</b>$m_ein_art<br>";
				echo "</p><hr>";
			} else {
				$m_ende = date_mysql2german ( $sep->mand->M_EDATUM );
				echo "<hr><p class=\"warnung\">SEPA-Mandat abgelaufen am $m_ende</p><hr>";
			}
		} else {
			echo "<hr><p class=\"warnung\">Keine SEPA-Mandate</p><hr>";
		}
	}
	/*
	 * $kaution = new kautionen;
	 * $kautionsbetrag_string = $kaution->get_kautionsbetrag($mietvertrag_id);
	 * if(!empty($kautionsbetrag_string)){
	 * echo "<hr>KAUTION: $kautionsbetrag_string";
	 * }
	 */
	
	// ###DETAILS ZUM VERTRAG
	$mv_details_info = new details ();
	$mv_details_arr = $mv_details_info->get_details ( 'MIETVERTRAG', $mietvertrag_id );
	if (count ( $mv_details_arr ) > 0) {
		echo "<b>VEREINBARUNGEN</b><hr>";
		for($i = 0; $i < count ( $mv_details_arr ); $i ++) {
			echo "<b>" . $mv_details_arr [$i] ['DETAIL_NAME'] . "</b>:<br>" . $mv_details_arr [$i] ['DETAIL_INHALT'] . "<br>";
		}
	} else {
		echo "<p class=rot>k.A zum Mietvertrag</p>";
	}
	$link_mv_details = "<a href=\"?daten=details&option=details_hinzu&detail_tabelle=MIETVERTRAG&detail_id=$mietvertrag_id\">NEUES DETAIL ZUM MIETVERTRAG</a>";
	echo "<br><hr>$link_mv_details<hr>";
	
	// echo "</div>"; //ende balken3
	
	$k = new kautionen ();
	$soll_kaution = nummer_punkt2komma ( 3 * $k->summe_mietekalt ( $mietvertrag_id ) );
	echo "<br><span class=\"font_balken_uberschrift\"><b>Kaution (Soll:$soll_kaution €)</b></span><hr>";
	
	$k->kautionen_info ( 'Mietvertrag', $mietvertrag_id, '13' );
	if ($k->anzahl_zahlungen >= 1) {
		echo "<b>Kautionsbuchungen: ($k->anzahl_zahlungen)</b><br>";
		$buchung_zeile = 0;
		for($a = 0; $a < $k->anzahl_zahlungen; $a ++) {
			$buchung_zeile ++;
			$datum = date_mysql2german ( $k->kautionszahlungen_array [$a] ['DATUM'] );
			$betrag = nummer_punkt2komma ( $k->kautionszahlungen_array [$a] ['BETRAG'] );
			$vzweck = $k->kautionszahlungen_array [$a] ['VERWENDUNGSZWECK'];
			echo "$buchung_zeile. $datum $betrag € $vzweck<br>";
		}
	} else {
		echo "Keine Kautionsbuchungen vorhanden";
	}
	echo "<hr>";
	
	echo "<a href=\"?daten=kautionen&option=kautionen_buchen&mietvertrag_id=$mietvertrag_id\">Kautionen buchen</a><hr>";
	echo "<a href=\"?daten=kautionen&option=hochrechner&mietvertrag_id=$mietvertrag_id\">Kautionshöhe hochrechnen</a><hr>";
	
	// #####BALKEN 4 MIETE
	$monat = date ( "M" );
	$jahr = date ( "Y" );
	// echo "<div class=\"div balken4\" align=\"right\"><span class=\"font_balken_uberschrift\">MIETE $monat $jahr</span><hr />";
	
	$buchung = new mietkonto ();
	$monat = date ( "m" );
	$jahr = date ( "Y" );
	echo "<hr><span class=\"font_balken_uberschrift\"><b>MIETE</b></span><hr><b>Forderungen</b><br>";
	$forderungen_arr = $buchung->aktuelle_forderungen_array ( $mietvertrag_id );
	for($i = 0; $i < count ( $forderungen_arr ); $i ++) {
		echo "" . $forderungen_arr [$i] ['KOSTENKATEGORIE'] . " " . $forderungen_arr [$i] ['BETRAG'] . " €<br>";
	}
	$summe_forderungen_aktuell = $buchung->summe_forderung_monatlich ( $mietvertrag_id, $monat, $jahr );
	echo "<hr>Summe Forderungen: $summe_forderungen_aktuell €";
	// echo "<b><a href=\"?daten=mietkonten_blatt&anzeigen=forderung_aus_monat&mietvertrag_id=$mietvertrag_id&monat=$vormonat&jahr=$jahr\">Forderungen Vormonat</a><hr>";
	
	$summe_zahlungen = $buchung->summe_zahlung_monatlich ( $mietvertrag_id, $buchung->monat_heute, $buchung->jahr_heute );
	echo "<hr>Summe Zahlungen: $summe_zahlungen €<hr>";
	
	$a = new miete ();
	$a->mietkonto_berechnung ( $mietvertrag_id );
	
	echo "SALDO: $a->erg €";
	// echo "</div><div class=\"div balken4\" align=\"right\"><span class=\"font_balken_uberschrift\">MIETE $monat $jahr</span><hr />";
	// echo "<span class=\"font_balken_uberschrift\">MIETKONTENBLATT</span><hr>";
	// iframe_start_skaliert(290, 550);
	// $a->mietkonten_blatt_balken($mietvertrag_id);
	// ######################
	// iframe_end();
	
	// $mietvertrag_info->tage_berechnen_bis_heute("01.05.2008");
	
	echo "</div>";
    echo "</div></div>";
    ### ENDE VERTRAGSDATEN ####
	echo "<div class='berlus-overview col-lg-4'>";
	$det = new detail ();
	$hinw_einheit = $det->finde_detail_inhalt ( 'Einheit', $einheit_id, 'Hinweis_zu_Einheit' );
	if (! empty ( $hinw_einheit )) {
		$tmps = str_replace ( 'nils@inspirationgroup.biz', 'alon@inspirationgroup.biz', $hinw_einheit );
		echo str_replace ( 'chen@inspirationgroup.biz', 'alon@inspirationgroup.biz', $tmps ) . "<br>";
	}

    //$link_neuer_auftrag_int = "<a href=\"?daten=todo&option=neues_projekt&typ=Benutzer&kos_typ=Einheit&kos_id=$einheit_id\">Neuer Auftrag INT</a>";
    //$link_neuer_auftrag_ext = "<a href=\"?daten=todo&option=neues_projekt&typ=Partner&kos_typ=Einheit&kos_id=$einheit_id\">Neuer Auftrag EXT</a>";
    //echo "<span class='font_balken_uberschrift' style='float: right'>$link_neuer_auftrag_int $link_neuer_auftrag_ext</span>";
    $t = new todo ();
	$t_arr = $t->get_auftraege_einheit ( 'Einheit', $einheit_id, '0' );
	// echo '<pre>';
	// print_r($t_arr);
	
	$anz_t = count ( $t_arr );
	echo "<table class=\"bsbs\">";
	echo "<tr><th>DATUM</th><th>VON/AN</th><th>AUFTRAG</th></tr>";
	for($t = 0; $t < $anz_t; $t ++) {
		$txt = $t_arr [$t] ['TEXT'];
		$d_erstellt = date_mysql2german ( $t_arr [$t] ['ANZEIGEN_AB'] );
		$t_id = $t_arr [$t] ['T_ID'];
		$verfasser_id = $t_arr [$t] ['VERFASSER_ID'];
		$b = new benutzer ();
		$b->get_benutzer_infos ( $verfasser_id );
		$verfasser_name = $b->benutzername;
		
		$beteiligt_typ = $t_arr [$t] ['BENUTZER_TYP'];
		$beteiligt_id = $t_arr [$t] ['BENUTZER_ID'];
		
		if ($beteiligt_typ == 'Benutzer' or empty ( $beteiligt_typ )) {
			
			$b1 = new benutzer ();
			$b1->get_benutzer_infos ( $beteiligt_id );
			$beteiligt_name = "<b>$b1->benutzername</b>";
		}
		
		if ($beteiligt_typ == 'Partner') {
			$pp = new partners ();
			$pp->get_partner_info ( $beteiligt_id );
			$beteiligt_name = "<b>$pp->partner_name</b>";
		}
		$link_pdf = "<a href=\"?daten=todo&option=pdf_auftrag&proj_id=$t_id\"><img src=\"css/pdf2.png\"></a>";
		$link_txt = "<a href=\"?daten=todo&option=edit&t_id=$t_id\">$txt</a>";
		
		echo "<tr><td>$d_erstellt<br>$link_pdf</td><td>$verfasser_name<br>$beteiligt_name</td><td>$link_txt</td></tr>";
	}
	$t_arr = array ();
	$t = new todo ();
	$t_arr = $t->get_auftraege_einheit ( 'Einheit', $einheit_id, '1' );
	// echo '<pre>';
	// print_r($t_arr);
	
	$anz_t = count ( $t_arr );
	
	echo "<tr><th>DATUM</th><th>VON/AN</th><th>ERLEDIGT</th></tr>";
	for($t = 0; $t < $anz_t; $t ++) {
		$txt = $t_arr [$t] ['TEXT'];
		$d_erstellt = date_mysql2german ( $t_arr [$t] ['ANZEIGEN_AB'] );
		$t_id = $t_arr [$t] ['T_ID'];
		$verfasser_id = $t_arr [$t] ['VERFASSER_ID'];
		$b = new benutzer ();
		$b->get_benutzer_infos ( $verfasser_id );
		$verfasser_name = $b->benutzername;
		$beteiligt_id = $t_arr [$t] ['BENUTZER_ID'];
		$beteiligt_typ = $t_arr [$t] ['BENUTZER_TYP'];
		if ($beteiligt_typ == 'Benutzer' or empty ( $beteiligt_typ )) {
			
			$b1 = new benutzer ();
			$b1->get_benutzer_infos ( $beteiligt_id );
			$beteiligt_name = "<b>$b1->benutzername</b>";
		}
		
		if ($beteiligt_typ == 'Partner') {
			$pp = new partners ();
			$pp->get_partner_info ( $beteiligt_id );
			$beteiligt_name = "<b>$pp->partner_name</b>";
		}
		
		$link_pdf = "<a href=\"?daten=todo&option=pdf_auftrag&proj_id=$t_id\"><img src=\"css/pdf2.png\"></a>";
		$link_txt = "<a href=\"?daten=todo&option=edit&t_id=$t_id\">$txt</a>";
		
		echo "<tr><td>$d_erstellt<br>$link_pdf</td><td>$verfasser_name<br>$beteiligt_name</td><td>$link_txt</td></tr>";
	}
	echo "</table>";
	echo "</div>";
    echo "</div></div>";
}

/* Neue Version zu Einheit oder Einheit und MV */
function uebersicht_einheit2($einheit_id) {
	// echo "ES WIRD BEARBEITET - Hr. Sivac";
	if (! empty ( $_REQUEST ['mietvertrag_id'] )) {
		$mietvertrag_id = $_REQUEST ['mietvertrag_id'];
		$mv = new mietvertraege ();
		$mv->get_mietvertrag_infos_aktuell ( $mietvertrag_id );
		$einheit_id = $mv->einheit_id;
		$e = new einheit ();
		$e->get_einheit_info ( $einheit_id );
	} else {
		$e = new einheit ();
		$e->get_einheit_info ( $einheit_id );
		$mietvertrag_id = $e->get_last_mietvertrag_id ( $einheit_id );
		
		if (empty ( $mietvertrag_id )) {
			die ( 'Keine Informationen, weil keine Vormietverträge' );
		}
		$mv = new mietvertraege ();
		$mv->get_mietvertrag_infos_aktuell ( $mietvertrag_id );
	}
	/*
	 * echo '<pre>';
	 * print_r($mv);
	 * print_r($e);
	 * echo '</pre>';
	 */
	// ################################## BALKEN EINHEIT---->
	
	$weg = new weg ();
	// $et_arr = $weg->get_eigentuemer_arr($einheit_id);
	$weg->get_last_eigentuemer ( $einheit_id );
	if (isset ( $weg->eigentuemer_id )) {
		$e_id = $weg->eigentuemer_id;
		// $weg->get_eigentumer_id_infos3($e_id);
		$weg->get_eigentuemer_namen ( $e_id );
		$miteigentuemer_namen = strip_tags ( $weg->eigentuemer_name_str );
	} else {
		$miteigentuemer_namen = "UNBEKANNT";
	}
	// echo '<pre>';
	// print_r($weg);
	echo "<div class=\"div balken1\"><span class=\"font_balken_uberschrift\">EINHEIT</span><hr />";
	echo "<span class=\"font_balken_uberschrift\">$e->einheit_kurzname</span><hr/>";
	echo "<p class=\"warnung\">WEG-ET:<br>$miteigentuemer_namen</p><hr>";
	echo "$e->haus_strasse $e->haus_nummer<br/>";
	echo "$e->haus_plz $e->haus_stadt<br/>";
	echo "<hr><a href=\"?index.php&daten=todo&option=auftrag_haus&haus_id=$e->haus_id&einheit_id=$einheit_id\">Aufträge an Haus</a><hr>";
	echo "Lage: $e->einheit_lage QM: $e->einheit_qm m² <b>TYP:$e->typ</b><hr>";
	$war = new wartung ();
	$war->wartungen_anzeigen ( $e->einheit_kurzname );
	echo "<hr>";
	
	// print_r($e);
	$details_info = new details ();
	$einheit_details_arr = $details_info->get_details ( 'EINHEIT', $einheit_id );
	if (count ( $einheit_details_arr ) > 0) {
		echo "<b>AUSSTATTUNG</b><hr>";
		for($i = 0; $i < count ( $einheit_details_arr ); $i ++) {
			echo "<b>" . $einheit_details_arr [$i] ['DETAIL_NAME'] . "</b>:<br>" . $einheit_details_arr [$i] ['DETAIL_INHALT'] . "<br>";
		}
	} else {
		echo "k.A zur Ausstattung";
	}
	$link_einheit_details = "<a href=\"?daten=details&option=details_hinzu&detail_tabelle=EINHEIT&detail_id=$einheit_id\">NEUES DETAIL ZUR EINHEIT $e->einheit_kurzname</a>";
	echo "<hr>$link_einheit_details<hr>";
	$details_info = new details ();
	$objekt_details_arr = $details_info->get_details ( 'OBJEKT', $e->objekt_id );
	echo "<hr /><b>OBJEKT</b>: $e->objekt_name<hr/>";
	for($i = 0; $i < count ( $objekt_details_arr ); $i ++) {
		echo "<b>" . $objekt_details_arr [$i] ['DETAIL_NAME'] . "</b><br>" . $objekt_details_arr [$i] ['DETAIL_INHALT'] . "<br>";
	}
	$oo = new objekt ();
	$oo->get_objekt_infos ( $e->objekt_id );
	echo "<hr><span class=\"warnung\">OBJEKT-ET: $oo->objekt_eigentuemer</span><hr>";
	$link_objekt_details = "<a href=\"?daten=details&option=details_hinzu&detail_tabelle=OBJEKT&detail_id=$e->objekt_id\">NEUES DETAIL ZUM OBJEKT $e->objekt_name</a>";
	echo "<hr>$link_objekt_details<hr>";
	echo "</div>";
	// #ende spalte objekt und einheit####
	
	// ######## balken 2 MIETER
	echo "<div class=\"div balken2\"><span class=\"font_balken_uberschrift\">MIETER<br> ($mv->personen_name_string_u)</span><hr />";
	// echo "Personen im MV: $anzahl_personen_im_mv";
	if ($mv->anzahl_personen < 1) {
		echo "leer";
	}
	// ####INFOS ÜBER PERSON/MIETER
	$person_info = new person ();
	for($i = 0; $i < $mv->anzahl_personen; $i ++) {
		$person_info->get_person_infos ( $mv->personen_ids [$i] ['PERSON_MIETVERTRAG_PERSON_ID'] );
		$akt_person_id = $mv->personen_ids [$i] ['PERSON_MIETVERTRAG_PERSON_ID'];
		$person_info->get_person_anzahl_mietvertraege_aktuell ( $mv->personen_ids [$i] ['PERSON_MIETVERTRAG_PERSON_ID'] );
		$person_anzahl_mvs = $person_info->person_anzahl_mietvertraege;
		$person_nachname = $person_info->person_nachname;
		$person_vorname = $person_info->person_vorname;
		$person_geburtstag = $person_info->person_geburtstag;
		// $person_info2 = $person_info->get_person_anzahl_mietvertraege_alt($personen_ids_arr[$i]['PERSON_MIETVERTRAG_PERSON_ID']);
		// $person_anzahl_mietvertraege_alt = $person_info->person_anzahl_mietvertraege_alt;
		$person_mv_id_array = $person_info->get_vertrags_ids_von_person ( $mv->personen_ids [$i] ['PERSON_MIETVERTRAG_PERSON_ID'] );
		$zeile = $i + 1;
		$mieternamen_str = "<b>$zeile. $person_nachname $person_vorname</b><br> geb. am: $person_geburtstag<br>";
		$aktuelle_einheit_link = "";
		$alte_einheit_link = "";
		// ####DETAILS VOM MIETER
		$details_info_mieter = new details ();
		$mieter_details_arr = $details_info_mieter->get_details ( 'PERSON', $mv->personen_ids [$i] ['PERSON_MIETVERTRAG_PERSON_ID'] );
		$mieter_details = "";
		for($p = 0; $p < count ( $mieter_details_arr ); $p ++) {
			$mieter_details .= "<b>" . $mieter_details_arr [$p] ['DETAIL_NAME'] . "</b><br>" . $mieter_details_arr [$p] ['DETAIL_INHALT'] . "<br>";
		}
		
		for($a = 0; $a < count ( $person_mv_id_array ); $a ++) {
			$person_info2 = new person ();
			$mv_status = $person_info2->get_vertrags_status ( $person_mv_id_array [$a] ['PERSON_MIETVERTRAG_MIETVERTRAG_ID'] );
			$mietvertrag_info2 = new mietvertrag ();
			$p_einheit_id = $mietvertrag_info2->get_einheit_id_von_mietvertrag ( $person_mv_id_array [$a] ['PERSON_MIETVERTRAG_MIETVERTRAG_ID'] );
			$p_einheit_kurzname = $mietvertrag_info2->einheit_kurzname;
			
			if ($mv_status == TRUE) {
				// echo "".$person_mv_id_array[$a]['PERSON_MIETVERTRAG_MIETVERTRAG_ID']." TRUE $p_einheit_id $p_einheit_kurzname";
				// if($einheit_id != $p_einheit_id){
				// echo "<a href=\"?daten=uebersicht&anzeigen=einheit&einheit_id=$p_einheit_id\">$p_einheit_kurzname</a>&nbsp; ";
				// }
				$aktuelle_einheit_link .= "<a href=\"?daten=uebersicht&anzeigen=einheit&einheit_id=$p_einheit_id\">$p_einheit_kurzname</a>&nbsp; ";
			} else {
				// $alte_einheit_link = "".$person_mv_id_array[$a]['PERSON_MIETVERTRAG_MIETVERTRAG_ID']." FALSE";
				$alte_einheit_link = "<a href=\"?daten=uebersicht&anzeigen=einheit&einheit_id=$p_einheit_id\">$p_einheit_kurzname</a>&nbsp; ";
			}
		}
		echo "<br>$mieternamen_str";
		if (! empty ( $mieter_details )) {
			echo "<br>$mieter_details";
		}
		echo "<br>Anzahl Verträge:  $person_anzahl_mvs<br>";
		echo "Aktuelle Verträge:<br>";
		echo "$aktuelle_einheit_link<br>";
		if (! empty ( $alte_einheit_link )) {
			echo "Alte Verträge:<br>";
			echo "$alte_einheit_link<br>";
		}
		$link_person_details = "<a href=\"?daten=details&option=details_hinzu&detail_tabelle=PERSON&detail_id=$akt_person_id\">NEUES DETAIL ZU $person_info->person_nachname $person_info->person_vorname</a>";
		echo "<hr>$link_person_details<hr>";
	}
	
	// ######### LETZTER MIETER#########
	echo "VORMIETER:<br>";
	$vormieter_ids_array = $e->letzter_vormieter ( $einheit_id );
	if (! empty ( $vormieter_ids_array )) {
		for($b = 0; $b < count ( $vormieter_ids_array ); $b ++) {
			// echo $vormieter_ids_array[$b]['PERSON_MIETVERTRAG_PERSON_ID'];
			$person_info->get_person_infos ( $vormieter_ids_array [$b] ['PERSON_MIETVERTRAG_PERSON_ID'] );
			$person_nachname = $person_info->person_nachname;
			$person_vorname = $person_info->person_vorname;
			echo "$person_nachname $person_vorname<br>";
		}
	} else {
		echo "<p class=rot>Keine Vormieter</p>";
	}
	echo "<hr><a href=\"?daten=mietvertrag_raus&mietvertrag_raus=mietvertrag_kurz&einheit_id=$einheit_id\">Alle Mietverträge von $e->einheit_kurzname</a>";
	// echo "<pre>";
	// print_r($person_mv_id_array);
	// echo "</pre>";
	echo "</div>";
	
	// #####BALKEN 3 VERTRAGSDATEN
	echo "<div class=\"div balken3\"><span class=\"font_balken_uberschrift\">VERTRAGSDATEN</span><hr />";
	
	$mietvertrag_info = new mietvertrag ();
	$anzahl_mietvertraege = $mietvertrag_info->get_anzahl_mietvertrag_id_zu_einheit ( $einheit_id );
	$anzahl_mietvertraege = $mietvertrag_info->anzahl_mietvertraege_gesamt;
	
	if (! empty ( $mietvertrag_id )) {
		echo "<b><a href=\"?daten=mietkonten_blatt&anzeigen=mietkonto_uebersicht_detailiert&mietvertrag_id=$mietvertrag_id\">MIETKONTENBLATT</a></b><br>";
		echo "<b><a href=\"?daten=mietkonten_blatt&anzeigen=mietkonto_ab&mietvertrag_id=$mietvertrag_id\">MIETKONTENBLATT AB</a></b><br>";
	}
	if (! empty ( $mv->mietvertrag_von )) {
		$mietvertrag_von_datum = date_mysql2german ( $mv->mietvertrag_von );
		echo "EINZUG: <b>$mietvertrag_von_datum</b><br>";
	}
	if (! empty ( $mv->mietvertrag_bis )) {
		$mietvertrag_bis_datum = date_mysql2german ( $mv->mietvertrag_bis );
		if ($mietvertrag_bis_datum == '00.00.0000') {
			echo "AUSZUG: <b>ungekündigt</b><br>";
			echo "<a href=\"?daten=mietvertrag_raus&mietvertrag_raus=mietvertrag_beenden&mietvertrag_id=$mietvertrag_id\">VERTRAG BEENDEN</a><br>";
		} else {
			echo "<p class=auszug_bald>AUSZUG: $mietvertrag_bis_datum</p>";
		}
		
		$sep = new sepa ();
		$m_ref = 'MV' . $mietvertrag_id;
		if ($sep->check_m_ref_alle ( $m_ref )) {
			$sep->get_mandat_infos_mref ( $m_ref );
			// print_r($sep->mand);
			$d_heute = date ( "Ymd" );
			$enddatum_mandat = str_replace ( '-', '', $sep->mand->M_EDATUM );
			// echo $enddatum_mandat;
			if ($enddatum_mandat >= $d_heute) {
				echo "<hr><p style=\"color:green;\"><b>Gültiges SEPA-Mandat</b><br>";
				$konto_inh = $sep->mand->NAME;
				echo "<b>Kto-Inhaber:</b> $konto_inh<br>";
				$iban = $iban_1 = chunk_split ( $sep->mand->IBAN, 4, ' ' );
				$bic = $sep->mand->BIC;
				echo "<b>IBAN:</b> $iban<br>";
				echo "<b>BIC:</b> $bic<br>";
				$u_datum = date_mysql2german ( $sep->mand->M_UDATUM );
				$a_datum = date_mysql2german ( $sep->mand->M_ADATUM );
				$e_datum = date_mysql2german ( $sep->mand->M_EDATUM );
				echo "<b>Unterschrieben:</b> $u_datum<br>";
				echo "<b>Gültig ab:</b>      $u_datum<br>";
				echo "<b>Gültig bis:</b>     $e_datum<br>";
				$m_ein_art = $sep->mand->EINZUGSART;
				echo "<b>Einzugsart:</b>$m_ein_art<br>";
				echo "</p><hr>";
			} else {
				$m_ende = date_mysql2german ( $sep->mand->M_EDATUM );
				echo "<hr><p class=\"warnung\">SEPA-Mandat abgelaufen am $m_ende</p><hr>";
			}
		} else {
			echo "<hr><p class=\"warnung\">Keine SEPA-Mandate</p><hr>";
		}
	}
	/*
	 * $kaution = new kautionen;
	 * $kautionsbetrag_string = $kaution->get_kautionsbetrag($mietvertrag_id);
	 * if(!empty($kautionsbetrag_string)){
	 * echo "<hr>KAUTION: $kautionsbetrag_string";
	 * }
	 */
	
	// ###DETAILS ZUM VERTRAG
	$mv_details_info = new details ();
	$mv_details_arr = $mv_details_info->get_details ( 'MIETVERTRAG', $mietvertrag_id );
	if (count ( $mv_details_arr ) > 0) {
		echo "<b>VEREINBARUNGEN</b><hr>";
		for($i = 0; $i < count ( $mv_details_arr ); $i ++) {
			echo "<b>" . $mv_details_arr [$i] ['DETAIL_NAME'] . "</b>:<br>" . $mv_details_arr [$i] ['DETAIL_INHALT'] . "<br>";
		}
	} else {
		echo "<p class=rot>k.A zum Mietvertrag</p>";
	}
	$link_mv_details = "<a href=\"?daten=details&option=details_hinzu&detail_tabelle=MIETVERTRAG&detail_id=$mietvertrag_id\">NEUES DETAIL ZUM MIETVERTRAG</a>";
	echo "<br><hr>$link_mv_details<hr>";
	
	// echo "</div>"; //ende balken3
	
	$k = new kautionen ();
	$soll_kaution = nummer_punkt2komma ( 3 * $k->summe_mietekalt ( $mietvertrag_id ) );
	echo "<br><span class=\"font_balken_uberschrift\"><b>Kaution (Soll:$soll_kaution €)</b></span><hr>";
	
	$k->kautionen_info ( 'Mietvertrag', $mietvertrag_id, '13' );
	if ($k->anzahl_zahlungen >= 1) {
		echo "<b>Kautionsbuchungen: ($k->anzahl_zahlungen)</b><br>";
		$buchung_zeile = 0;
		for($a = 0; $a < $k->anzahl_zahlungen; $a ++) {
			$buchung_zeile ++;
			$datum = date_mysql2german ( $k->kautionszahlungen_array [$a] ['DATUM'] );
			$betrag = nummer_punkt2komma ( $k->kautionszahlungen_array [$a] ['BETRAG'] );
			$vzweck = $k->kautionszahlungen_array [$a] ['VERWENDUNGSZWECK'];
			echo "$buchung_zeile. $datum $betrag € $vzweck<br>";
		}
	} else {
		echo "Keine Kautionsbuchungen vorhanden";
	}
	echo "<hr>";
	
	echo "<a href=\"?daten=kautionen&option=kautionen_buchen&mietvertrag_id=$mietvertrag_id\">Kautionen buchen</a><hr>";
	echo "<a href=\"?daten=kautionen&option=hochrechner&mietvertrag_id=$mietvertrag_id\">Kautionshöhe hochrechnen</a><hr>";
	
	// #####BALKEN 4 MIETE
	$monat = date ( "M" );
	$jahr = date ( "Y" );
	// echo "<div class=\"div balken4\" align=\"right\"><span class=\"font_balken_uberschrift\">MIETE $monat $jahr</span><hr />";
	
	$buchung = new mietkonto ();
	$monat = date ( "m" );
	$jahr = date ( "Y" );
	echo "<hr><span class=\"font_balken_uberschrift\"><b>MIETE</b></span><hr><b>Forderungen</b><br>";
	$forderungen_arr = $buchung->aktuelle_forderungen_array ( $mietvertrag_id );
	for($i = 0; $i < count ( $forderungen_arr ); $i ++) {
		echo "" . $forderungen_arr [$i] ['KOSTENKATEGORIE'] . " " . $forderungen_arr [$i] ['BETRAG'] . " €<br>";
	}
	$summe_forderungen_aktuell = $buchung->summe_forderung_monatlich ( $mietvertrag_id, $monat, $jahr );
	echo "<hr>Summe Forderungen: $summe_forderungen_aktuell €";
	// echo "<b><a href=\"?daten=mietkonten_blatt&anzeigen=forderung_aus_monat&mietvertrag_id=$mietvertrag_id&monat=$vormonat&jahr=$jahr\">Forderungen Vormonat</a><hr>";
	
	$summe_zahlungen = $buchung->summe_zahlung_monatlich ( $mietvertrag_id, $buchung->monat_heute, $buchung->jahr_heute );
	echo "<hr>Summe Zahlungen: $summe_zahlungen €<hr>";
	
	$a = new miete ();
	$a->mietkonto_berechnung ( $mietvertrag_id );
	
	echo "SALDO: $a->erg €<hr>";
	// echo "</div><div class=\"div balken4\" align=\"right\"><span class=\"font_balken_uberschrift\">MIETE $monat $jahr</span><hr />";
	// echo "<span class=\"font_balken_uberschrift\">MIETKONTENBLATT</span><hr>";
	// iframe_start_skaliert(290, 550);
	// $a->mietkonten_blatt_balken($mietvertrag_id);
	// ######################
	// iframe_end();
	
	// $mietvertrag_info->tage_berechnen_bis_heute("01.05.2008");
	
	echo "</div>"; // ende balken4
	$link_neuer_auftrag_int = "<a href=\"?daten=todo&option=neues_projekt&typ=Benutzer&kos_typ=Einheit&kos_id=$einheit_id\">Neuer Auftrag INT</a>";
	$link_neuer_auftrag_ext = "<a href=\"?daten=todo&option=neues_projekt&typ=Partner&kos_typ=Einheit&kos_id=$einheit_id\">Neuer Auftrag EXT</a>";
	echo "<div class=\"div balken4\" align=\"right\">";
	$det = new detail ();
	$hinw_einheit = $det->finde_detail_inhalt ( 'Einheit', $einheit_id, 'Hinweis_zu_Einheit' );
	if (! empty ( $hinw_einheit )) {
		echo $hinw_einheit . "<br>";
	}
	echo "<span class=\"font_balken_uberschrift\">$link_neuer_auftrag_int<br>$link_neuer_auftrag_ext</span><hr />";
	$t = new todo ();
	$t_arr = $t->get_auftraege_einheit ( 'Einheit', $einheit_id, 0 );
	// echo '<pre>';
	// print_r($t_arr);
	
	$anz_t = count ( $t_arr );
	echo "<table class=\"bsbs\">";
	echo "<tr><th>DATUM</th><th>VON/AN</th><th>AUFTRAG</th></tr>";
	for($t = 0; $t < $anz_t; $t ++) {
		$txt = $t_arr [$t] ['TEXT'];
		$d_erstellt = date_mysql2german ( $t_arr [$t] ['ANZEIGEN_AB'] );
		$t_id = $t_arr [$t] ['T_ID'];
		$verfasser_id = $t_arr [$t] ['VERFASSER_ID'];
		$b = new benutzer ();
		$b->get_benutzer_infos ( $verfasser_id );
		$verfasser_name = $b->benutzername;
		
		$beteiligt_typ = $t_arr [$t] ['BENUTZER_TYP'];
		$beteiligt_id = $t_arr [$t] ['BENUTZER_ID'];
		
		if ($beteiligt_typ == 'Benutzer' or empty ( $beteiligt_typ )) {
			
			$b1 = new benutzer ();
			$b1->get_benutzer_infos ( $beteiligt_id );
			$beteiligt_name = "<b>$b1->benutzername</b>";
		}
		
		if ($beteiligt_typ == 'Partner') {
			$pp = new partners ();
			$pp->get_partner_info ( $beteiligt_id );
			$beteiligt_name = "<b>$pp->partner_name</b>";
		}
		$link_pdf = "<a href=\"?daten=todo&option=pdf_auftrag&proj_id=$t_id\"><img src=\"css/pdf2.png\"></a>";
		$link_txt = "<a href=\"?daten=todo&option=edit&t_id=$t_id\">$txt</a>";
		
		echo "<tr><td>$d_erstellt<br>$link_pdf</td><td>$verfasser_name<br>$beteiligt_name</td><td>$link_txt</td></tr>";
	}
	
	$t = new todo ();
	$t_arr = $t->get_auftraege_einheit ( 'Einheit', $einheit_id, 1 );
	// echo '<pre>';
	// print_r($t_arr);
	
	$anz_t = count ( $t_arr );
	
	echo "<tr><th>DATUM</th><th>VON/AN</th><th>ERLEDIGT</th></tr>";
	for($t = 0; $t < $anz_t; $t ++) {
		$txt = $t_arr [$t] ['TEXT'];
		$d_erstellt = date_mysql2german ( $t_arr [$t] ['ANZEIGEN_AB'] );
		$t_id = $t_arr [$t] ['T_ID'];
		$verfasser_id = $t_arr [$t] ['VERFASSER_ID'];
		$b = new benutzer ();
		$b->get_benutzer_infos ( $verfasser_id );
		$verfasser_name = $b->benutzername;
		$beteiligt_id = $t_arr [$t] ['BENUTZER_ID'];
		$beteiligt_typ = $t_arr [$t] ['BENUTZER_TYP'];
		if ($beteiligt_typ == 'Benutzer' or empty ( $beteiligt_typ )) {
			
			$b1 = new benutzer ();
			$b1->get_benutzer_infos ( $beteiligt_id );
			$beteiligt_name = "<b>$b1->benutzername</b>";
		}
		
		if ($beteiligt_typ == 'Partner') {
			$pp = new partners ();
			$pp->get_partner_info ( $beteiligt_id );
			$beteiligt_name = "<b>$pp->partner_name</b>";
		}
		
		$link_pdf = "<a href=\"?daten=todo&option=pdf_auftrag&proj_id=$t_id\"><img src=\"css/pdf2.png\"></a>";
		$link_txt = "<a href=\"?daten=todo&option=edit&t_id=$t_id\">$txt</a>";
		
		echo "<tr><td>$d_erstellt<br>$link_pdf</td><td>$verfasser_name<br>$beteiligt_name</td><td>$link_txt</td></tr>";
	}
	echo "</table>";
	echo "</div>";
}
function uebersicht_einheit_alt($einheit_id) {
	if (! empty ( $_REQUEST ['mietvertrag_id'] )) {
		$mietvertrag_id = $_REQUEST ['mietvertrag_id'];
		$mv = new mietvertraege ();
		$mv->get_mietvertrag_infos_aktuell ( $mietvertrag_id );
		$einheit_id = $mv->einheit_id;
	}
	
	// ##INFOS AUS CLASS EINHEIT
	$einheit_info = new einheit ();
	$einheit_informationen = $einheit_info->get_einheit_info ( $einheit_id );
	$einheit_kurzname = $einheit_info->einheit_kurzname;
	$einheit_objekt_name = $einheit_info->objekt_name;
	$einheit_objekt_id = $einheit_info->objekt_id;
	$einheit_haus_strasse = $einheit_info->haus_strasse;
	$einheit_haus_nr = $einheit_info->haus_nummer;
	$einheit_lage = $einheit_info->einheit_lage;
	$einheit_qm = $einheit_info->einheit_qm;
	$einheit_plz = $einheit_info->haus_plz;
	$einheit_stadt = $einheit_info->haus_stadt;
	$datum_heute = $einheit_info->datum_heute;
	$datum_heute = date_mysql2german ( $datum_heute );
	
	/*
	 * #### ÜBERSCHRIFT GANZ OBEN
	 * echo "<div class=\"div ueberschrift\">";
	 *
	 * echo "$einheit_objekt_name | $einheit_haus_strasse $einheit_haus_nr in $einheit_plz $einheit_stadt &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Datum: $datum_heute";
	 * echo "</div>";
	 */
	
	// ################################## BALKEN EINHEIT---->
	echo "<div class=\"div balken1\"><span class=\"font_balken_uberschrift\">EINHEIT</span><hr />";
	echo "<span class=\"font_balken_uberschrift\">$einheit_kurzname</span><hr/>";
	echo "$einheit_haus_strasse $einheit_haus_nr<br/>";
	echo "$einheit_plz $einheit_stadt<br/>";
	echo "Lage: $einheit_lage QM: $einheit_qm m²<hr/>";
	$details_info = new details ();
	$einheit_details_arr = $details_info->get_details ( EINHEIT, $einheit_id );
	if (count ( $einheit_details_arr ) > 0) {
		echo "<b>AUSSTATTUNG</b><hr>";
		for($i = 0; $i < count ( $einheit_details_arr ); $i ++) {
			echo "<b>" . $einheit_details_arr [$i] ['DETAIL_NAME'] . "</b>:<br>" . $einheit_details_arr [$i] [DETAIL_INHALT] . "<br>";
		}
	} else {
		echo "k.A zur Ausstattung";
	}
	// #########################################################
	// ################ details in array mit class details holen-->
	$details_info = new details ();
	$objekt_details_arr = $details_info->get_details ( OBJEKT, $einheit_objekt_id );
	echo "<hr /><b>OBJEKT</b>: $einheit_objekt_name<hr/>";
	for($i = 0; $i < count ( $objekt_details_arr ); $i ++) {
		echo "<b>" . $objekt_details_arr [$i] ['DETAIL_NAME'] . "</b><br>" . $objekt_details_arr [$i] [DETAIL_INHALT] . "<br>";
	}
	echo "</div>";
	
	// ####INFOS AUS CLASS MIETVERTRAG
	$mietvertrag_info = new mietvertrag ();
	$anzahl_mietvertraege = $mietvertrag_info->get_anzahl_mietvertrag_id_zu_einheit ( $einheit_id );
	$anzahl_mietvertraege = $mietvertrag_info->anzahl_mietvertraege_gesamt;
	if (! $mietvertrag_id) {
		$mietvertrag_info->get_mietvertrag_infos_aktuell ( $einheit_id );
		$mietvertrag_id = $mietvertrag_info->mietvertrag_id;
		$mietvertrag_von = $mietvertrag_info->mietvertrag_von;
		$mietvertrag_bis = $mietvertrag_info->mietvertrag_bis;
		$mietvertrag_info->get_anzahl_personen_zu_mietvertrag ( $mietvertrag_id );
		$anzahl_personen_im_mv = $mietvertrag_info->anzahl_personen_im_vertrag;
	} else {
		$mietvertrag_von = $mv->mietvertrag_von;
		$mietvertrag_bis = $mv->mietvertrag_bis;
		$anzahl_personen_im_mv = $mv->anzahl_personen;
		$mietvertrag_id = $mv->mietvertrag_id;
		echo '<pre>';
		print_r ( $mv );
	}
	
	$personen_ids_arr = $mietvertrag_info->get_personen_ids_mietvertrag ( $mietvertrag_id );
	$aktuelle_miete = $mietvertrag_info->get_aktuelle_miete ( $mietvertrag_id );
	
	// ######## balken 2 MIETER
	echo "<div class=\"div balken2\"><span class=\"font_balken_uberschrift\">MIETER ($anzahl_personen_im_mv)</span><hr />";
	// echo "Personen im MV: $anzahl_personen_im_mv";
	if ($anzahl_personen_im_mv < 1) {
		echo "leer";
	}
	// ####INFOS ÜBER PERSON/MIETER
	$person_info = new person ();
	for($i = 0; $i < $anzahl_personen_im_mv; $i ++) {
		$person_info->get_person_infos ( $personen_ids_arr [$i] ['PERSON_MIETVERTRAG_PERSON_ID'] );
		$person_info->get_person_anzahl_mietvertraege_aktuell ( $personen_ids_arr [$i] ['PERSON_MIETVERTRAG_PERSON_ID'] );
		$person_anzahl_mvs = $person_info->person_anzahl_mietvertraege;
		$person_nachname = $person_info->person_nachname;
		$person_vorname = $person_info->person_vorname;
		$person_geburtstag = $person_info->person_geburtstag;
		// $person_info2 = $person_info->get_person_anzahl_mietvertraege_alt($personen_ids_arr[$i]['PERSON_MIETVERTRAG_PERSON_ID']);
		// $person_anzahl_mietvertraege_alt = $person_info->person_anzahl_mietvertraege_alt;
		$person_mv_id_array = $person_info->get_vertrags_ids_von_person ( $personen_ids_arr [$i] ['PERSON_MIETVERTRAG_PERSON_ID'] );
		$zeile = $i + 1;
		$mieternamen_str = "<b>$zeile. $person_nachname $person_vorname</b><br> geb. am: $person_geburtstag<br>";
		$aktuelle_einheit_link = "";
		$alte_einheit_link = "";
		// ####DETAILS VOM MIETER
		$details_info_mieter = new details ();
		$mieter_details_arr = $details_info_mieter->get_details ( PERSON, $personen_ids_arr [$i] ['PERSON_MIETVERTRAG_PERSON_ID'] );
		$mieter_details = "";
		for($p = 0; $p < count ( $mieter_details_arr ); $p ++) {
			$mieter_details .= "<b>" . $mieter_details_arr [$p] ['DETAIL_NAME'] . "</b><br>" . $mieter_details_arr [$p] [DETAIL_INHALT] . "<br>";
		}
		
		for($a = 0; $a < count ( $person_mv_id_array ); $a ++) {
			$person_info2 = new person ();
			$mv_status = $person_info2->get_vertrags_status ( $person_mv_id_array [$a] ['PERSON_MIETVERTRAG_MIETVERTRAG_ID'] );
			$mietvertrag_info2 = new mietvertrag ();
			$p_einheit_id = $mietvertrag_info2->get_einheit_id_von_mietvertrag ( $person_mv_id_array [$a] ['PERSON_MIETVERTRAG_MIETVERTRAG_ID'] );
			$p_einheit_kurzname = $mietvertrag_info2->einheit_kurzname;
			
			if ($mv_status == TRUE) {
				// echo "".$person_mv_id_array[$a]['PERSON_MIETVERTRAG_MIETVERTRAG_ID']." TRUE $p_einheit_id $p_einheit_kurzname";
				// if($einheit_id != $p_einheit_id){
				// echo "<a href=\"?daten=uebersicht&anzeigen=einheit&einheit_id=$p_einheit_id\">$p_einheit_kurzname</a>&nbsp; ";
				// }
				$aktuelle_einheit_link .= "<a href=\"?daten=uebersicht&anzeigen=einheit&einheit_id=$p_einheit_id\">$p_einheit_kurzname</a>&nbsp; ";
			} else {
				// $alte_einheit_link = "".$person_mv_id_array[$a]['PERSON_MIETVERTRAG_MIETVERTRAG_ID']." FALSE";
				$alte_einheit_link = "<a href=\"?daten=uebersicht&anzeigen=einheit&einheit_id=$p_einheit_id\">$p_einheit_kurzname</a>&nbsp; ";
			}
		}
		echo "<br>$mieternamen_str";
		if (! empty ( $mieter_details )) {
			echo "<br>$mieter_details";
		}
		echo "<br>Anzahl Verträge:  $person_anzahl_mvs<br>";
		echo "Aktuelle Verträge:<br>";
		echo "$aktuelle_einheit_link<br>";
		if (! empty ( $alte_einheit_link )) {
			echo "Alte Verträge:<br>";
			echo "$alte_einheit_link<br>";
		}
	}
	echo "<hr>";
	// ######### LETZTER MIETER#########
	echo "VORMIETER:<br>";
	$vormieter_ids_array = $einheit_info->letzter_vormieter ( $einheit_id );
	if (! empty ( $vormieter_ids_array )) {
		for($b = 0; $b < count ( $vormieter_ids_array ); $b ++) {
			// echo $vormieter_ids_array[$b]['PERSON_MIETVERTRAG_PERSON_ID'];
			$person_info->get_person_infos ( $vormieter_ids_array [$b] ['PERSON_MIETVERTRAG_PERSON_ID'] );
			$person_nachname = $person_info->person_nachname;
			$person_vorname = $person_info->person_vorname;
			echo "$person_nachname $person_vorname<br>";
		}
	} else {
		echo "<p class=rot>Keine Vormieter</p>";
	}
	echo "<hr><a href=\"?daten=mietvertrag_raus&mietvertrag_raus=mietvertrag_kurz&einheit_id=$einheit_id\">Alle Mietverträge von $einheit_kurzname</a>";
	echo "</div>";
	
	/* Wenn die Wohnung z.Zeit vermietet ist, dann Vertragsdaten anzeigen */
	if (! empty ( $mietvertrag_id )) {
		// #####BALKEN 3 VERTRAGSDATEN
		echo "<div class=\"div balken3\"><span class=\"font_balken_uberschrift\">VERTRAGSDATEN</span><hr />";
		
		$mietvertrag_info = new mietvertrag ();
		$anzahl_mietvertraege = $mietvertrag_info->get_anzahl_mietvertrag_id_zu_einheit ( $einheit_id );
		$anzahl_mietvertraege = $mietvertrag_info->anzahl_mietvertraege_gesamt;
		$mietvertrag_info->get_mietvertrag_infos_aktuell ( $einheit_id );
		$mietvertrag_id = $mietvertrag_info->mietvertrag_id;
		$mietvertrag_von = $mietvertrag_info->mietvertrag_von;
		$mietvertrag_bis = $mietvertrag_info->mietvertrag_bis;
		$mietvertrag_info->get_anzahl_personen_zu_mietvertrag ( $mietvertrag_id );
		$anzahl_personen_im_mv = $mietvertrag_info->anzahl_personen_im_vertrag;
		$personen_ids_arr = $mietvertrag_info->get_personen_ids_mietvertrag ( $mietvertrag_id );
		
		$mietvertrag_bis_datum = date_mysql2german ( $mietvertrag_bis );
		
		if (! empty ( $mietvertrag_id )) {
			echo "MIETKONTENBLATT: <b><a href=\"?daten=mietkonten_blatt&anzeigen=mietkonto_uebersicht_detailiert&mietvertrag_id=$mietvertrag_id\">MIETKONTO $einheit_kurzname</a></b><br>";
		}
		if (! empty ( $mietvertrag_von )) {
			$mietvertrag_von_datum = date_mysql2german ( $mietvertrag_von );
			echo "EINZUG: <b>$mietvertrag_von_datum</b><br>";
		}
		if (! empty ( $mietvertrag_bis )) {
			$mietvertrag_bis_datum = date_mysql2german ( $mietvertrag_bis );
			if ($mietvertrag_bis_datum == '00.00.0000') {
				echo "AUSZUG: <b>ungekündigt</b><br>";
				echo "<a href=\"?daten=mietvertrag_raus&mietvertrag_raus=mietvertrag_beenden&mietvertrag_id=$mietvertrag_id\">VERTRAG BEENDEN</a><br>";
			} else {
				echo "AUSZUG: <p class=auszug_bald>$mietvertrag_bis_datum</p>";
			}
		}
		/*
		 * $kaution = new kautionen;
		 * $kautionsbetrag_string = $kaution->get_kautionsbetrag($mietvertrag_id);
		 * if(!empty($kautionsbetrag_string)){
		 * echo "<hr>KAUTION: $kautionsbetrag_string";
		 * }
		 */
		echo "<hr>";
		echo "<a href=\"?daten=kautionen&option=kautionen_buchen&mietvertrag_id=$mietvertrag_id\">Kautionen buchen</a><hr>";
		echo "<a href=\"?daten=kautionen&option=hochrechner&mietvertrag_id=$mietvertrag_id\">Kautionshöhe hochrechnen</a><hr>";
		// ###DETAILS ZUM VERTRAG
		$mv_details_info = new details ();
		$mv_details_arr = $mv_details_info->get_details ( MIETVERTRAG, $mietvertrag_id );
		if (count ( $mv_details_arr ) > 0) {
			echo "<b>VEREINBARUNGEN</b><hr>";
			for($i = 0; $i < count ( $mv_details_arr ); $i ++) {
				echo "<b>" . $mv_details_arr [$i] ['DETAIL_NAME'] . "</b>:<br>" . $mv_details_arr [$i] [DETAIL_INHALT] . "<br>";
			}
		} else {
			echo "<p class=rot>k.A zum Mietvertrag</p>";
		}
		// echo "</div>"; //ende balken3
		
		// #####BALKEN 4 MIETE
		$monat = date ( "M" );
		$jahr = date ( "Y" );
		// echo "<div class=\"div balken4\" align=\"right\"><span class=\"font_balken_uberschrift\">MIETE $monat $jahr</span><hr />";
		
		// #########berlussimo class ########
		$mietvertrag_info = new mietvertrag ();
		$anzahl_mietvertraege = $mietvertrag_info->get_anzahl_mietvertrag_id_zu_einheit ( $einheit_id );
		$anzahl_mietvertraege = $mietvertrag_info->anzahl_mietvertraege_gesamt;
		$mietvertrag_info->get_mietvertrag_infos_aktuell ( $einheit_id );
		$mietvertrag_id = $mietvertrag_info->mietvertrag_id;
		$mietvertrag_von = $mietvertrag_info->mietvertrag_von;
		$mietvertrag_bis = $mietvertrag_info->mietvertrag_bis;
		$mietvertrag_info->get_anzahl_personen_zu_mietvertrag ( $mietvertrag_id );
		$anzahl_personen_im_mv = $mietvertrag_info->anzahl_personen_im_vertrag;
		$personen_ids_arr = $mietvertrag_info->get_personen_ids_mietvertrag ( $mietvertrag_id );
		
		$mietvertrag_bis_datum = date_mysql2german ( $mietvertrag_bis );
		// $mietvertrag_info->alle_zahlungen($mietvertrag_id);
		
		// ######## mitkonto class
		
		$buchung = new mietkonto ();
		$monat = date ( "m" );
		$jahr = date ( "Y" );
		echo "<br><br><hr><span class=\"font_balken_uberschrift\"><b>MIETE</b></span><hr><b>Forderungen</b><br>";
		$forderungen_arr = $buchung->aktuelle_forderungen_array ( $mietvertrag_id );
		for($i = 0; $i < count ( $forderungen_arr ); $i ++) {
			echo "" . $forderungen_arr [$i] ['KOSTENKATEGORIE'] . " " . $forderungen_arr [$i] ['BETRAG'] . " €<br>";
		}
		$summe_forderungen_aktuell = $buchung->summe_forderung_monatlich ( $mietvertrag_id, $monat, $jahr );
		echo "<hr>Summe Forderungen: $summe_forderungen_aktuell €";
		// echo "<b><a href=\"?daten=mietkonten_blatt&anzeigen=forderung_aus_monat&mietvertrag_id=$mietvertrag_id&monat=$vormonat&jahr=$jahr\">Forderungen Vormonat</a><hr>";
		
		$summe_zahlungen = $buchung->summe_zahlung_monatlich ( $mietvertrag_id, $buchung->monat_heute, $buchung->jahr_heute );
		echo "<hr>Summe Zahlungen: $summe_zahlungen €<hr>";
		
		$a = new miete ();
		$a->mietkonto_berechnung ( $mietvertrag_id );
		
		echo "SALDO: $a->erg €<hr>";
		echo "</div><div class=\"div balken4\" align=\"right\"><span class=\"font_balken_uberschrift\">MIETE $monat $jahr</span><hr />";
		echo "<span class=\"font_balken_uberschrift\">MIETKONTENBLATT</span><hr>";
		iframe_start_skaliert ( 290, 550 );
		$a->mietkonten_blatt_balken ( $mietvertrag_id );
		// ######################
		iframe_end ();
		
		// $mietvertrag_info->tage_berechnen_bis_heute("01.05.2008");
		
		echo "</div>"; // ende balken4
	} // ende if isset mietvertrag_id
} // end funktion
  
// ####INFOS AUS CLASS MV
/*
 * $mietvertrag_info = new mietvertrag;
 * $anzahl_mietvertraege = $mietvertrag_info->get_anzahl_mietvertrag_id_zu_einheit($einheit_id);
 * $anzahl_mietvertraege = $mietvertrag_info->anzahl_mietvertraege_gesamt;
 * $mietvertrag_info->get_mietvertrag_infos_aktuell($einheit_id);
 * $mietvertrag_id = $mietvertrag_info->mietvertrag_id;
 * $mietvertrag_von = $mietvertrag_info->mietvertrag_von;
 * $mietvertrag_bis = $mietvertrag_info->mietvertrag_bis;
 * $mietvertrag_info->get_anzahl_personen_zu_mietvertrag($mietvertrag_id);
 * $anzahl_personen_im_mv = $mietvertrag_info->anzahl_personen_im_vertrag;
 * $personen_ids_arr = $mietvertrag_info->get_personen_ids_mietvertrag($mietvertrag_id);
 * }
 * ####INFOS AUS CLASS PERSON
 * #echo "<pre>";
 * #print_r($mietvertrag_id_array);
 * #echo "</pre>";
 * ###########################
 *
 * /*echo "<p class=formular_tabelle align=center>ÜBERSICHT $einheit_objekt_name -> $einheit_haus_strasse $einheit_haus_nr -> $einheit_kurzname -> $einheit_lage</p>";
 * echo "<p class=formular_tabelle>";
 * #echo "Objekt:-> $einheit_objekt_name<br>";
 * #echo "Haus:-> $einheit_haus_strasse $einheit_haus_nr<br>";
 * #echo "Einheit:-> $einheit_kurzname LAGE: $einheit_lage QM: $einheit_qm m²<br>";
 * #echo "Mietvertraege zur Einheit:-> $einheit_kurzname :-> $anzahl_mietvertraege<br>";
 * #echo "Mietvertrag Aktuell :-> ID: $mietvertrag_id VON: $mietvertrag_von BIS: $mietvertrag_id<br>";
 * #echo "Im Aktuellen MV befinden sich $anzahl_personen_im_mv Person (-en)<br>";
 */

/*
 * $person_info = new person;
 *
 * for($i=0;$i<$anzahl_personen_im_mv;$i++){
 * $person_info->get_person_infos($personen_ids_arr[$i]['PERSON_MIETVERTRAG_PERSON_ID']);
 * $person_info->get_person_anzahl_mietvertraege_aktuell($personen_ids_arr[$i]['PERSON_MIETVERTRAG_PERSON_ID']);
 * $person_anzahl_mvs = $person_info->person_anzahl_mietvertraege;
 * $person_nachname = $person_info->person_nachname;
 * $person_vorname = $person_info->person_vorname;
 * $person_geburtstag = $person_info->person_geburtstag;
 * $person_info2 = $person_info->get_person_anzahl_mietvertraege_alt($personen_ids_arr[$i]['PERSON_MIETVERTRAG_PERSON_ID']);
 * $person_anzahl_mietvertraege_alt = $person_info->person_anzahl_mietvertraege_alt;
 * $person_mv_id_array = $person_info->get_vertrags_ids_von_person($personen_ids_arr[$i]['PERSON_MIETVERTRAG_PERSON_ID']);
 * $mieternamen_str .= "<b>$person_nachname $person_vorname geb. am: $person_geburtstag </b><br>Vertrag (aktuell): $person_anzahl_mvs Vertrag (alt): $person_anzahl_mietvertraege_alt<br>";
 * }
 * $mietvertrag_info = new mietvertrag;
 * $anzahl_mietvertraege_von_person = count($person_mv_id_array);
 * if($anzahl_mietvertraege_von_person > 0){
 * $andere_einheiten = "<br>Andere gemietete Einheiten ";
 * for($a=0;$a<$anzahl_mietvertraege_von_person;$a++){
 * $mietvertrag_info->get_einheit_id_von_mietvertrag($person_mv_id_array[$a]['PERSON_MIETVERTRAG_MIETVERTRAG_ID']);
 * $einheit_id_of_mietvertrag = $mietvertrag_info->einheit_id_of_mietvertrag;
 * $einheit_info = new einheit;
 * $einheit_informationen = $einheit_info->get_einheit_info($einheit_id_of_mietvertrag);
 * $einheit_kurzname = $einheit_info->einheit_kurzname;
 * if($einheit_id != $einheit_id_of_mietvertrag){
 * $andere_einheiten .= "<a href=\"?daten=uebersicht&anzeigen=einheit&einheit_id=$einheit_id_of_mietvertrag\">$einheit_kurzname</a>&nbsp;";
 *
 * }
 * }
 * }
 */
// phpinfo();
?>
