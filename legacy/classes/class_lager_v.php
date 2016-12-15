<?php

/* Klasse für die Lagerverwaltung */
class lager_v {

    /* Formular zum Erfassen von neuen Lieferscheinen */
    function form_lieferschein_erfassen() {
        $f = new formular ();
        $f->erstelle_formular ( "Lieferschein erfassen", NULL );
        $p = new partners ();
        $p->partner_dropdown ( 'Lieferant', 'lieferant_id', 'lieferant_id' );
        $p->partner_dropdown ( 'Empfänger', 'empfaenger_id', 'empfaenger_id' );
        $f->text_feld ( "Lieferscheinnr", 'l_nr', '', 20, 'l_nr', '' );
        $f->datum_feld ( 'Lieferdatum', 'l_datum', '', 'l_datum' );
        $f->hidden_feld ( "option", "lieferschein_send" );
        $f->send_button ( "submit", "Speichern" );
        $f->ende_formular ();
    } // Funktionsende
    function check_lieferschein_exists($li_typ, $li_id, $empf_typ, $empf_id, $datum, $l_nr) {
        $result = DB::select( "SELECT * FROM LIEFERSCHEINE WHERE AKTUELL='1' && DATUM='$datum' && LI_TYP='$li_typ' && LI_ID='$li_id' && EMPF_TYP='$empf_typ' && EMPF_ID='$empf_id'" );
        return !empty($result);
    }
    function lieferschein_speichern($li_typ, $li_id, $empf_typ, $empf_id, $datum, $l_nr) {
        $datum = date_german2mysql ( $datum );
        if (! $this->check_lieferschein_exists ( $li_typ, $li_id, $empf_typ, $empf_id, $datum, $l_nr )) {
            $last_id = last_id2 ( 'LIEFERSCHEINE', 'L_ID' ) + 1;
            $db_abfrage = "INSERT INTO LIEFERSCHEINE VALUES (NULL, '$last_id','$datum', '$li_typ', '$li_id', '$empf_typ','$empf_id','$l_nr', '1')";
            DB::insert( $db_abfrage );
            /* Protokollieren */
            $last_dat = DB::getPdo()->lastInsertId();
            protokollieren ( 'LIEFERSCHEINE', $last_dat, '0' );
        } else {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\InfoMessage('Lieferschein existiert bereits')
            );
        }
    }
    function lagerbestand_anzeigen_bis_pdf($datum_bis) {
        $datum_bis = date_german2mysql ( $datum_bis );
        if (session()->has('lager_id')) {
            $lager_id = session()->get('lager_id');
            $ll = new lager ();
            $ll->lager_name_partner ( $lager_id );
            /* $ll->lager_partner_id */
            DB::statement( "SET SQL_BIG_SELECTS=1" );
            $my_array = DB::select( "SELECT RECHNUNGSNUMMER, RECHNUNGEN.EINGANGSDATUM, RECHNUNGEN_POSITIONEN.BELEG_NR, POSITION, BEZEICHNUNG, RECHNUNGEN_POSITIONEN.ART_LIEFERANT, RECHNUNGEN_POSITIONEN.ARTIKEL_NR, RECHNUNGEN_POSITIONEN.MENGE AS GEKAUFTE_MENGE, RECHNUNGEN_POSITIONEN.PREIS, RECHNUNGEN_POSITIONEN.MWST_SATZ FROM RECHNUNGEN RIGHT JOIN (RECHNUNGEN_POSITIONEN, POSITIONEN_KATALOG) ON ( RECHNUNGEN.BELEG_NR = RECHNUNGEN_POSITIONEN.BELEG_NR && POSITIONEN_KATALOG.ART_LIEFERANT = RECHNUNGEN_POSITIONEN.ART_LIEFERANT && RECHNUNGEN_POSITIONEN.ARTIKEL_NR = POSITIONEN_KATALOG.ARTIKEL_NR  ) WHERE EMPFAENGER_TYP = 'Lager' && EMPFAENGER_ID = '$lager_id' && EINGANGSDATUM<='$datum_bis' && RECHNUNGEN_POSITIONEN.AKTUELL='1' && RECHNUNGEN.AKTUELL='1'  GROUP BY RECHNUNGEN_POSITIONEN.ARTIKEL_NR, BELEG_NR ORDER BY RECHNUNGEN.EINGANGSDATUM ASC" );

            $az = count($my_array); // az = anzahl zeilen
            if ($az) {
                $gesamt_lager_wert = 0;
                $zaehler = 0;
                for($a = 0; $a < $az; $a ++) {

                    $datum = date_mysql2german ( $my_array [$a] ['EINGANGSDATUM'] );
                    $beleg_nr = $my_array [$a] ['BELEG_NR'];
                    $rechnungsnummer = $my_array [$a] ['RECHNUNGSNUMMER'];
                    $lieferant_id = $my_array [$a] ['ART_LIEFERANT'];
                    $pp = new partners ();
                    $pp->get_partner_name ( $lieferant_id );
                    $position = $my_array [$a] ['POSITION'];
                    $menge = $my_array [$a] ['GEKAUFTE_MENGE'];
                    $preis = $my_array [$a] ['PREIS'];

                    // $kontierte_menge = $rechnung_info->position_auf_kontierung_pruefen($beleg_nr, $position);
                    $kontierte_menge = $this->position_auf_kontierung_pruefen ( $beleg_nr, $position, $datum_bis );
                    $rest_menge = $menge - $kontierte_menge;
                    $artikel_nr = $my_array [$a] ['ARTIKEL_NR'];
                    $bezeichnung = $my_array [$a] ['BEZEICHNUNG'];
                    $pos_mwst_satz = $my_array [$a] ['MWST_SATZ'];
                    $waren_wert = ($rest_menge * $preis) / 100 * (100 + $pos_mwst_satz);

                    $menge = nummer_punkt2komma ( $menge );
                    $preis = nummer_punkt2komma ( $preis );
                    $rest_menge = nummer_punkt2komma ( $rest_menge );
                    $waren_wert_a = nummer_punkt2komma ( $waren_wert );

                    if ($rest_menge != '0,00') {

                        $gesamt_lager_wert = $gesamt_lager_wert + $waren_wert;
                        // echo "<tr class=\"zeile1\" align=\"right\"><td>$datum</td><td>$pp->partner_name</td><td>$beleg_link</td><td>$link_artikel_suche</td><td>$bezeichnung</td><td>$menge</td><td>$rest_menge</td><td>$preis €</td><td>$pos_mwst_satz %</td><td>$waren_wert_a €</td></tr>";
                        $tab_arr [$zaehler] ['DATUM'] = $datum;
                        $tab_arr [$zaehler] ['LIEFERANT'] = $pp->partner_name;
                        $tab_arr [$zaehler] ['RNR'] = $rechnungsnummer;
                        $tab_arr [$zaehler] ['ART_NR'] = $artikel_nr;
                        $tab_arr [$zaehler] ['BEZ'] = $bezeichnung;
                        $tab_arr [$zaehler] ['MENGE'] = $menge;
                        $tab_arr [$zaehler] ['RMENGE'] = $rest_menge;
                        $tab_arr [$zaehler] ['PREIS'] = $preis;
                        $tab_arr [$zaehler] ['MWST'] = $pos_mwst_satz;
                        $tab_arr [$zaehler] ['W_WERT'] = $waren_wert_a;
                        $zaehler ++;
                    }
                } // end for

                $gesamt_lager_wert_a = nummer_punkt2komma ( $gesamt_lager_wert );
                // echo "<tr align=\"right\"><td colspan=9>Restwarenwert gesamt</td><td>$gesamt_lager_wert_a €</td></tr>";
                // echo "</table>";
                $tab_arr [$zaehler] ['PREIS'] = "<b>SUMME</b>";
                $tab_arr [$zaehler] ['MWST'] = '<b>' . date_mysql2german ( $datum_bis ) . '</b>';
                $tab_arr [$zaehler] ['W_WERT'] = "<b>$gesamt_lager_wert_a</b>";

                $pdf = new Cezpdf ( 'a4', 'landscape' );
                $bpdf = new b_pdf ();
                session()->put('partner_id', $ll->lager_partner_id);
                $bpdf->b_header ( $pdf, 'Partner', session()->get('partner_id'), 'landscape', 'Helvetica.afm', 6 );
                $p = new partners ();
                $p->get_partner_info ( session()->get('partner_id'));
                $cols = array (
                    'DATUM' => "Datum",
                    'LIEFERANT' => "Lieferant",
                    'RNR' => "RNr.",
                    'ART_NR' => "Artnr",
                    'BEZ' => "Bezeichnung",
                    'MENGE' => "Menge",
                    'RMENGE' => "Restmenge",
                    'PREIS' => "Preis",
                    'MWST' => "MwSt Satz",
                    'W_WERT' => "Wert"
                );
                $pdf->ezSetDy ( - 6 );
                $lager_bez = $ll->lager_bezeichnung(session()->get('lager_id'));
                $dbis = date_mysql2german ( $datum_bis );
                $pdf->ezText ( "<b>Stand am: $dbis | Lager: $lager_bez | Warenwert: $gesamt_lager_wert_a €</b>" );
                $pdf->ezSetDy ( - 6 );
                $pdf->ezTable ( $tab_arr, $cols, "", array (
                    'showHeadings' => 1,
                    'shaded' => 1,
                    'shadeCol' => array (
                        0.9,
                        0.9,
                        0.9
                    ),
                    'titleFontSize' => 8,
                    'fontSize' => 8,
                    'xPos' => 50,
                    'xOrientation' => 'right',
                    'width' => 750,
                    'cols' => array (
                        'MENGE' => array (
                            'justification' => 'right',
                            'width' => 50
                        ),
                        'RMENGE' => array (
                            'justification' => 'right',
                            'width' => 60
                        ),
                        'PREIS' => array (
                            'justification' => 'right',
                            'width' => 50
                        ),
                        'MWST' => array (
                            'justification' => 'right',
                            'width' => 65
                        ),
                        'W_WERT' => array (
                            'justification' => 'right',
                            'width' => 50
                        )
                    )
                ) );

                ob_end_clean (); // ausgabepuffer leeren
                $pdf->ezStream ();
            } else {
                return false;
            }
        } else {
            warnung_ausgeben ( "Bitte Lager wählen" );
        }
    }
    function lagerbestand_anzeigen_bis($datum) {
        $datum = date_german2mysql ( $datum );
        if (session()->has('lager_id')) {
            $lager_id = session()->get('lager_id');
            DB::statement( "SET SQL_BIG_SELECTS=1" );
            $my_array = DB::select( "SELECT RECHNUNGEN.EINGANGSDATUM, RECHNUNGEN_POSITIONEN.BELEG_NR, POSITION, BEZEICHNUNG, RECHNUNGEN_POSITIONEN.ART_LIEFERANT, RECHNUNGEN_POSITIONEN.ARTIKEL_NR, RECHNUNGEN_POSITIONEN.MENGE AS GEKAUFTE_MENGE, RECHNUNGEN_POSITIONEN.PREIS, RECHNUNGEN_POSITIONEN.MWST_SATZ FROM RECHNUNGEN RIGHT JOIN (RECHNUNGEN_POSITIONEN, POSITIONEN_KATALOG) ON ( RECHNUNGEN.BELEG_NR = RECHNUNGEN_POSITIONEN.BELEG_NR && POSITIONEN_KATALOG.ART_LIEFERANT = RECHNUNGEN_POSITIONEN.ART_LIEFERANT && RECHNUNGEN_POSITIONEN.ARTIKEL_NR = POSITIONEN_KATALOG.ARTIKEL_NR  ) WHERE EMPFAENGER_TYP = 'Lager' && EMPFAENGER_ID = '$lager_id' && EINGANGSDATUM<='$datum' && RECHNUNGEN_POSITIONEN.AKTUELL='1' && RECHNUNGEN.AKTUELL='1'  GROUP BY RECHNUNGEN_POSITIONEN.ARTIKEL_NR, BELEG_NR ORDER BY RECHNUNGEN.EINGANGSDATUM ASC" );
            $az = count( $my_array); // az = anzahl zeilen
            if ($az) {
                echo "<table class=\"sortable\">";
                // echo "<tr class=\"feldernamen\" align=\"right\"><td>Ansehen</td><td>Artikelnr.</td><td>Artikelbezeichnung</td><td>MENGE</td><td>RESTMENGE</td><td>PREIS</td><td>MWSt</td><td>RESTWERT</td></tr>";
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
                    if ($rest_menge != '0,00') {
                        $zaehler ++;
                        $gesamt_lager_wert = $gesamt_lager_wert + $waren_wert;
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
    function reparatur_kontierungsdatum() {
        $my_arr = DB::select( "SELECT KONTIERUNG_DAT, KONTIERUNG_ID FROM KONTIERUNG_POSITIONEN WHERE KONTIERUNGS_DATUM='0000-00-00'" );
        $numrows = count( $my_arr);
        if ($numrows > 0) {
            for($a = 0; $a < $numrows; $a ++) {
                $dat = $my_arr [$a] ['KONTIERUNG_DAT'];
                $datum = $this->get_date_from_protokoll ( 'KONTIERUNG_POSITIONEN', $dat );
                echo "$dat, $datum<br>";
                DB::update( "UPDATE KONTIERUNG_POSITIONEN SET KONTIERUNGS_DATUM='$datum' WHERE KONTIERUNG_DAT='$dat' && KONTIERUNGS_DATUM='0000-00-00'" );
            }
        }
    }
    function get_date_from_protokoll($table, $dat) {
        $result = DB::select( "SELECT DATE_FORMAT(`PROTOKOLL_WANN`, '%Y-%m-%d') AS DATUM  FROM `PROTOKOLL` WHERE `PROTOKOLL_TABELE` LIKE '$table' && PROTOKOLL_DAT_NEU='$dat' ORDER BY PROTOKOLL_WANN DESC LIMIT 0,1" );
        if (!empty($result)) {
            $row = $result[0];
            return $row ['DATUM'];
        }
    }
    function position_auf_kontierung_pruefen($beleg_nr, $position, $datum) {
        $result = DB::select( "SELECT SUM( MENGE ) AS KONTIERTE_MENGE FROM `KONTIERUNG_POSITIONEN` WHERE BELEG_NR = '$beleg_nr' && POSITION = '$position' && KONTIERUNGS_DATUM<='$datum' && AKTUELL='1'" );
        $row = $result[0];
        $kontierte_menge = $row ['KONTIERTE_MENGE'];
        return $kontierte_menge;
    }
}//end class