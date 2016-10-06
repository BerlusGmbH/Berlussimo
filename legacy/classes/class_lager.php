<?php

class lager {
	var $lagerbestand_arr;
	function lager_in_array() {
		$result = DB::select( "SELECT * FROM LAGER WHERE AKTUELL = '1' ORDER BY LAGER_NAME ASC" );
		if (!empty($result)) {
			return $result;
		} else {
			return false;
		}
	}
	function lager_dropdown($label, $name, $id) {
		$lager_arr = $this->lager_in_array ();
		echo "<label for=\"$id\">$label</label><select name=\"$name\" size=\"1\" id=\"$id\">\n";
		for($a = 0; $a < count ( $lager_arr ); $a ++) {
			$lager_id = $lager_arr [$a] ['LAGER_ID'];
			$lager_name = $lager_arr [$a] ['LAGER_NAME'];
			echo "<option value=\"$lager_id\">$lager_name</OPTION>\n";
		}
		echo "</select><br>\n";
	}
	function lager_bezeichnung($id) {
		$result = DB::select( "SELECT LAGER_NAME FROM LAGER WHERE AKTUELL = '1' && LAGER_ID='$id' ORDER BY LAGER_NAME ASC" );
		if (!empty($result)) {
			$row = $result[0];
			return $row ['LAGER_NAME'];
		} else {
			return false;
		}
	}
	function get_lager_id($bez) {
		$result = DB::select( "SELECT LAGER_ID FROM LAGER WHERE AKTUELL = '1' && LAGER_NAME='$bez' ORDER BY LAGER_NAME ASC LIMIT 0,1" );
		if (!empty($result)) {
			$row = $result[0];
			return $row ['LAGER_ID'];
		} else {
			return false;
		}
	}
	function lager_name_partner($id) {
		$result = DB::select( "SELECT LAGER_NAME, PARTNER_ID FROM LAGER RIGHT JOIN(LAGER_PARTNER) ON (LAGER.LAGER_ID = LAGER_PARTNER.LAGER_ID) WHERE LAGER.AKTUELL = '1' && LAGER_PARTNER.AKTUELL = '1' && LAGER.LAGER_ID='$id' ORDER BY LAGER_NAME ASC" );
		if (!empty($result)) {
			$row = $result[0];
			$this->lager_name = $row ['LAGER_NAME'];
			$this->lager_partner_id = $row ['PARTNER_ID'];
		} else {
			return false;
		}
	}
	
	function lager_auswahl_liste($link) {
		if (request()->has('lager_id')) {
			session()->put('lager_id', request()->input('lager_id'));
		}

		$mieten = new mietkonto ();
		$mieten->erstelle_formular ( "Lager auswählen...", NULL );
		if (session()->has('lager_id')) {
			$lager_bezeichnung = $this->lager_bezeichnung(session()->get('lager_id'));
			echo "<p>&nbsp;<b>Ausgewähltes Lager</b> -> $lager_bezeichnung";
		} else {
			echo "<p>&nbsp;<b>Lager auswählen</b>";
		}
		echo "<p class=\"objekt_auswahl\">";
		$lager_arr = $this->lager_in_array ();
		$anzahl_lager = count ( $lager_arr );

		for($i = 0; $i <= $anzahl_lager; $i ++) {
			echo "<a class=\"objekt_auswahl_buchung\" href=\"$link&lager_id=" . $lager_arr [$i] ['LAGER_ID'] . "\">" . $lager_arr [$i] ['LAGER_NAME'] . "</a>&nbsp;";
		}
		echo "</p>";
		$mieten->ende_formular ();
	}
	function artikel_suche_einkauf($artikel_nr, $empfaenger_typ, $empfaenger_id) {
		$r = new rechnung ();
		$bez = $r->kostentraeger_ermitteln ( $empfaenger_typ, $empfaenger_id );
		$result = DB::select( " SELECT RECHNUNGSNUMMER, RECHNUNGSDATUM, RECHNUNGEN_POSITIONEN.BELEG_NR, U_BELEG_NR, POSITION, ART_LIEFERANT, ARTIKEL_NR, MENGE, PREIS
FROM `RECHNUNGEN_POSITIONEN` , RECHNUNGEN
WHERE `ARTIKEL_NR` LIKE '%$artikel_nr%'
AND RECHNUNGEN.AKTUELL = '1'
AND RECHNUNGEN_POSITIONEN.AKTUELL = '1'
AND RECHNUNGEN.EMPFAENGER_TYP = '$empfaenger_typ' && RECHNUNGEN.EMPFAENGER_ID = '$empfaenger_id' && RECHNUNGEN_POSITIONEN.BELEG_NR = RECHNUNGEN.BELEG_NR" );
		if (!empty($result)) {
			echo "<h3>Suchergebnis in Rechnungen von $bez  zu: $artikel_nr</h3>";
			echo "<table class=\"sortable\">";
			echo "<tr><th>LIEFERANT</th><th>ARTIKELNR</th><th>RDATUM</th><th>RNR</th><th>POSITION</th><th>BEZEICHNUNG</th><th>MENGE EINGANG</th><th>MENGE RAUS</th><th>RESTMENGE</th><th>PREIS</th></tr>";
			$g_menge = 0;
			$g_kontiert = 0;
			foreach($result as $row) {
				$p = new partners ();

				$r_nr = $row ['RECHNUNGSNUMMER'];
				$beleg_nr = $row ['BELEG_NR'];
				$u_beleg_nr = $row ['U_BELEG_NR'];
				$position = $row ['POSITION'];
				$art_lieferant = $row ['ART_LIEFERANT'];
				$p->get_partner_name ( $art_lieferant );
				$art_nr = $row ['ARTIKEL_NR'];
				$menge = $row ['MENGE'];
				$r = new rechnung ();
				$artikel_info_arr = $r->artikel_info ( $art_lieferant, $art_nr );
				$anz_bez = count ( $artikel_info_arr );
				$artikel_bez = $artikel_info_arr [0] ['BEZEICHNUNG'];
				$kontierte_menge = nummer_punkt2komma ( $r->position_auf_kontierung_pruefen ( $beleg_nr, $position ) );
				$g_kontiert += nummer_komma2punkt ( $kontierte_menge );
				$g_menge += $menge;
				$rest_menge_pos = nummer_punkt2komma ( $menge - nummer_komma2punkt ( $kontierte_menge ) );
				$rdatum = date_mysql2german ( $row ['RECHNUNGSDATUM'] );
				$preis = $row ['PREIS'];
				$r_link = "<a href='" . route('legacy::rechnungen::index', ['option' => 'rechnungs_uebersicht', 'belegnr' => $beleg_nr]) . "'>$r_nr</a>";
				echo "<tr><td>$p->partner_name</td><td>$art_nr</td><td>$rdatum</td><td>$r_link</td><td>$position</td><td>$artikel_bez</td><td>$menge</td><td>$kontierte_menge</td><td>$rest_menge_pos</td><td>$preis</td></tr>";
			}
			$g_rest = nummer_punkt2komma ( $g_menge - $g_kontiert );
			$g_menge = nummer_punkt2komma ( $g_menge );
			$g_kontiert = nummer_punkt2komma ( $g_kontiert );
			echo "<tfoot><tr ><td colspan=\"5\"><b>BESTAND</b></td><td><b>$g_menge</b></td><td><b>$g_kontiert</b></td><td><b>$g_rest</b></td><td></td></tr></tfoot>";
			echo "</table>";
		} else {
			echo "KEINE ARTIKEL GEFUNDEN $artikel_nr, $empfaenger_typ, $empfaenger_id";
		}
	}
	function artikel_suche_einkauf_form() {
		$f = new formular ();
		$f->erstelle_formular ( "Artikel im Lager suchen", NULL );
		$f->text_feld ( "Artikelnummer oder ein Teil davon eingeben", "artikel_nr", "", "35", 'artikel_nr', '' );
		$f->hidden_feld ( "option", "artikel_suche" );
		$f->send_button ( "submit", "Suchen" );
		$f->ende_formular ();
	}
	function lagerbestand_anzeigen() {
		if (session()->has('lager_id')) {
			$lager_id = session()->get('lager_id');
			DB::statement( "SET SQL_BIG_SELECTS=1" );
			$my_array = DB::select( "SELECT RECHNUNGEN.EINGANGSDATUM, RECHNUNGEN_POSITIONEN.BELEG_NR, POSITION, BEZEICHNUNG, RECHNUNGEN_POSITIONEN.ART_LIEFERANT, RECHNUNGEN_POSITIONEN.ARTIKEL_NR, RECHNUNGEN_POSITIONEN.MENGE AS GEKAUFTE_MENGE, RECHNUNGEN_POSITIONEN.PREIS, RECHNUNGEN_POSITIONEN.MWST_SATZ FROM RECHNUNGEN RIGHT JOIN (RECHNUNGEN_POSITIONEN, POSITIONEN_KATALOG) ON ( RECHNUNGEN.BELEG_NR = RECHNUNGEN_POSITIONEN.BELEG_NR && POSITIONEN_KATALOG.ART_LIEFERANT = RECHNUNGEN_POSITIONEN.ART_LIEFERANT && RECHNUNGEN_POSITIONEN.ARTIKEL_NR = POSITIONEN_KATALOG.ARTIKEL_NR  ) WHERE EMPFAENGER_TYP = 'Lager' && EMPFAENGER_ID = '$lager_id' && RECHNUNGEN_POSITIONEN.AKTUELL='1' && RECHNUNGEN.AKTUELL='1'  GROUP BY RECHNUNGEN_POSITIONEN.ARTIKEL_NR, BELEG_NR ORDER BY BEZEICHNUNG ASC" );
			$az = count( $my_array );
			if ($az) {
				echo "<table class=\"sortable\">";
				echo "<tr><th>Datum</th><th>LIEFERANT</th><th>Rechnung</th><th>Artikelnr.</th><th>Bezeichnung</th><th>Menge</th><th>rest</th><th>Preis</th><th>Mwst</th><th>Restwert</th></tr>";
				$gesamt_lager_wert = 0;
				$zaehler = 0;
				$rechnung_info = new rechnung ();
				for($a = 0; $a < $az; $a ++) {
					$datum = date_mysql2german ( $my_array [$a] ['EINGANGSDATUM'] );
					$beleg_nr = $my_array [$a] ['BELEG_NR'];
					$lieferant_id = $my_array [$a] ['ART_LIEFERANT'];
					$pp = new partners ();
					$pp->get_partner_name ( $lieferant_id );
					$position = $my_array [$a] ['POSITION'];
					$menge = $my_array [$a] ['GEKAUFTE_MENGE'];
					$preis = $my_array [$a] ['PREIS'];

					$kontierte_menge = $rechnung_info->position_auf_kontierung_pruefen ( $beleg_nr, $position );
					// $rechnung_info->rechnung_grunddaten_holen($beleg_nr);
					$rest_menge = $menge - $kontierte_menge;
					// $rest_menge = number_format($rest_menge,'',2,'.');
					// echo "$beleg_nr: $position. $menge - $kontierte_menge = $rest_menge<br>";
					$artikel_nr = $my_array [$a] ['ARTIKEL_NR'];
					$bezeichnung = $my_array [$a] ['BEZEICHNUNG'];
					$pos_mwst_satz = $my_array [$a] ['MWST_SATZ'];
					$waren_wert = ($rest_menge * $preis) / 100 * (100 + $pos_mwst_satz);

					$menge = nummer_punkt2komma ( $menge );
					$preis = nummer_punkt2komma ( $preis );
					$rest_menge = nummer_punkt2komma ( $rest_menge );
					$waren_wert_a = nummer_punkt2komma ( $waren_wert );

					$link_artikel_suche = "<a href='" . route('legacy::lager::index', ['option' => 'artikel_suche', 'artikel_nr' => $artikel_nr]) . "'>$artikel_nr</a>";
					$beleg_link = "<a href='" . route('legacy::rechnungen::index', ['option' => 'rechnungs_uebersicht', 'belegnr' => $beleg_nr]) . "'>Rechnung</a>";

					if ($rest_menge != '0,00') {
						$zaehler ++;
						$gesamt_lager_wert = $gesamt_lager_wert + $waren_wert;
						$beleg_link = "<a href='" . route('legacy::rechnungen::index', ['option' => 'rechnungs_uebersicht', 'belegnr' => $beleg_nr]) . "'>Rechnung</a>";

						if ($zaehler == '1') {
							$beleg_link = "<a href='" . route('legacy::rechnungen::index', ['option' => 'rechnungs_uebersicht', 'belegnr' => $beleg_nr]) . "'>Rechnung</a>";
							echo "<tr class=\"zeile1\" align=\"right\"><td>$datum</td><td>$pp->partner_name</td><td>$beleg_link</td><td>$link_artikel_suche</td><td>$bezeichnung</td><td>$menge</td><td>$rest_menge</td><td>$preis €</td><td>$pos_mwst_satz %</td><td>$waren_wert_a €</td></tr>";
						}

						if ($zaehler == '2') {
							$beleg_link = "<a href='" . route('legacy::rechnungen::index', ['option' => 'rechnungs_uebersicht', 'belegnr' => $beleg_nr]) . "'>Rechnung</a>";
							echo "<tr class=\"zeile2\" align=\"right\"><td>$datum</td><td>$pp->partner_name</td><td>$beleg_link</td><td>$link_artikel_suche</td><td>$bezeichnung</td><td>$menge</td><td>$rest_menge</td><td>$preis €</td><td>$pos_mwst_satz %</td><td>$waren_wert_a €</td></tr>";
						}
					}

					if ($zaehler == 2) {
						$zaehler = 0;
					}
				} // end for

				$gesamt_lager_wert_a = nummer_punkt2komma ( $gesamt_lager_wert );
				echo "<tr align=\"right\"><td colspan=9>Restwarenwert gesamt</td><td>$gesamt_lager_wert_a €</td></tr>";
				echo "</table>";
			} else {
				return false;
			}
		} else {
			warnung_ausgeben ( "Bitte Lager wählen" );
		}
	}
} // end class lager
