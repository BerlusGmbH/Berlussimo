<?php

class werkzeug
{
    public $werkzeug_bez;
    public $lieferant;

    function werkzeugliste($b_id = NULL)
    {
        $link_NACH_MIT = "<a href='" . route('web::benutzer::legacy', ['option' => 'werkzeugliste_nach_mitarbeiter', 'b_id' => $b_id]) . "'>ÜBERSICHT NACH MITARBEITER</a>";
        echo $link_NACH_MIT . '<br>';
        $f = new formular ();
        $f->fieldset('Werkzeugliste', 'wl');
        $arr = $this->werkzeugliste_arr($b_id);
        if (!empty($arr)) {
            $anz = count($arr);
            if ($b_id != NULL) {
                $link_rueckgabe_alle = "<a href='" . route('web::benutzer::legacy', ['option' => 'werkzeug_rueckgabe_alle', 'b_id' => $b_id]) . "'>Rückgabe vermerken</a>";
                $link_rueckgabe_alle_pdf = "<a href='" . route('web::benutzer::legacy', ['option' => 'werkzeug_rueckgabe_alle_pdf', 'b_id' => $b_id]) . "'>Rückgabe PDF</a>";
                $link_ausgabe_alle_pdf = "<a href='" . route('web::benutzer::legacy', ['option' => 'werkzeug_ausgabe_alle_pdf', 'b_id' => $b_id]) . "'>Ausgabeschein PDF</a>";
                echo "$link_ausgabe_alle_pdf | $link_rueckgabe_alle_pdf | $link_rueckgabe_alle<br><br>";
            }
            echo "<table class=\"sortable striped\">";
            echo "<tr><th>LIEFERANT</th><th>WBNR</th><th>BESCHREIBUNG</th><th>KURZINFO</th><th>MENGE</th><th>MITARBITER</th><th>OPTION</th></tr>";
            for ($a = 0; $a < $anz; $a++) {
                $w_id = $arr [$a] ['ID'];
                $beleg_id = $arr [$a] ['BELEG_ID'];
                $art_nr = $arr [$a] ['ARTIKEL_NR'];
                $menge = $arr [$a] ['MENGE'];
                $kurzinfo = $arr [$a] ['KURZINFO'];

                $r = new rechnung ();
                $r->rechnung_grunddaten_holen($beleg_id);
                $katalog_info = $r->artikel_info($r->rechnungs_aussteller_id, $art_nr);
                $art_info = $katalog_info [0] ['BEZEICHNUNG'];

                $lieferant = $r->rechnungs_aussteller_name;
                $link_beleg = "<a href='" . route('web::rechnungen::legacy', ['option' => 'rechnungs_uebersicht', 'belegnr' => $beleg_id]) . "'>$lieferant</a>";
                $wb_nr = 'W-' . $w_id;
                echo "<tr><td>$link_beleg</td><td>$wb_nr</td><td>$art_info</td><td>$kurzinfo</td><td>$menge</td>";

                $b_id = $arr [$a] ['BENUTZER_ID'];
                if ($b_id) {
                    $bb = new benutzer ();
                    $bb->get_benutzer_infos($b_id);
                    $link_mitarbeiter_liste = "<a href='" . route('web::benutzer::legacy', ['option' => 'werkzeuge_mitarbeiter', 'b_id' => $b_id]) . "'>$bb->benutzername</a>";
                    echo "<td>$link_mitarbeiter_liste</td>";
                } else {
                    $link_frei = "<a href='" . route('web::benutzer::legacy', ['option' => 'werkzeug_zuweisen', 'w_id' => $w_id]) . "'>Zuweisen</a>";
                    echo "<td>FREI $link_frei</td>";
                }
                if ($b_id == NULL) {
                    $link_loeschen = "<a href='" . route('web::benutzer::legacy', ['option' => 'werkzeug_raus', 'w_id' => $w_id]) . "'>Aus Liste Löschen</a>";
                } else {
                    $link_loeschen = "<a href='" . route('web::benutzer::legacy', ['option' => 'werkzeug_rueckgabe', 'b_id' => $b_id, 'w_id' => $w_id]) . "'>Einzelrückgabe</a>";
                }
                echo "<td>$link_loeschen</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "Keine Werkzeuge im Werkzeugpool!";
        }
        $f->fieldset_ende();
    }

    function werkzeugliste_arr($b_id = NULL)
    {
        if ($b_id == NULL) {
            $db_abfrage = "SELECT * FROM WERKZEUGE WHERE AKTUELL='1' ORDER BY ID, ARTIKEL_NR, ID, KURZINFO";
        } else {
            $db_abfrage = "SELECT * FROM WERKZEUGE WHERE AKTUELL='1' && BENUTZER_ID='$b_id' ORDER BY ID, ARTIKEL_NR, ID, KURZINFO";
        }
        $result = DB::select($db_abfrage);
        return $result;
    }

    function pdf_werkzeug_rueckgabe_einzel($b_id, $w_id, $scheintext = 'Einzelrückgabeschein')
    {
        $arr = $this->werkzeugliste_arr($b_id);
        $pdf = new Cezpdf ('a4', 'landscape');
        $bpdf = new b_pdf ();
        $bpdf->b_header($pdf, 'Partner', session()->get('partner_id'), 'landscape', 'Helvetica.afm', 6);

        $anz = count($arr);
        for ($a = 0; $a < $anz; $a++) {
            $beleg_id = $arr [$a] ['BELEG_ID'];
            $art_nr = $arr [$a] ['ARTIKEL_NR'];
            $menge = $arr [$a] ['MENGE'];
            $kurzinfo = $arr [$a] ['KURZINFO'];
            $id = $arr [$a] ['ID'];

            $r = new rechnung ();
            $r->rechnung_grunddaten_holen($beleg_id);
            $katalog_info = $r->artikel_info($r->rechnungs_aussteller_id, $art_nr);
            $art_info = $katalog_info [0] ['BEZEICHNUNG'];

            $lieferant = $r->rechnungs_aussteller_name;
            $bb = new benutzer ();
            $bb->get_benutzer_infos($b_id);
            if ($w_id == $id) {
                $arr_n [$a] ['LI'] = $lieferant;
                $arr_n [$a] ['WBNR'] = 'W-' . $w_id;
                $arr_n [$a] ['ART'] = $art_nr;
                $arr_n [$a] ['ART_INFO'] = $art_info;
                $arr_n [$a] ['KURZINFO'] = $kurzinfo;
                $arr_n [$a] ['MENGE'] = $menge;
                $arr_n [$a] ['OK'] = '';
            }
        }

        $pdf->ezText("<b>$scheintext Mitarbeiter: $bb->benutzername</b>", 14, array(
            'left' => '0'
        ));
        $datum = date("d.m.Y");
        $pdf->ezText("Datum $datum", 10, array(
            'left' => '0'
        ));

        $pdf->ezSetDy(-12); // abstand

        /* Spaltendefinition */
        $cols = array(
            'LI' => "<b>LIEFERANT</b>",
            'WBNR' => "<b>WBNR</b>",
            'ART' => "<b>ARTNR</b>",
            'ART_INFO' => "<b>BEZEICHNUNG</b>",
            'KURZINFO' => "<b>HINWEIS</b>",
            'MENGE' => "<b>MENGE</b>",
            'OK' => "<b>i.O</b>"
        );

        /* Tabellenparameter */
        $tableoptions = array(
            'width' => 600,
            'xPos' => 410,
            'shaded' => 0, // shaded: 0-->Zeile 1 & Zeile 2 --> weiss 1-->Zeile 1 = weiss Zeile 2= grau 2-->Zeile 1= grauA Zeile 2= grauB
            'showHeadings' => 1, // zeig Überschriften der spalten
            'showLines' => 0, // Mach Linien
            'lineCol' => array(
                0.0,
                0.0,
                0.0
            ), // Linienfarbe, hier schwarz

            'fontSize' => 8, // schriftgroesse
            'titleFontSize' => 8, // schriftgroesse Überschrift
            'splitRows' => 0,
            'protectRows' => 0,
            'innerLineThickness' => 0.5,
            'outerLineThickness' => 0.5,
            'rowGap' => 1,
            'colGap' => 1,
            'cols' => array(
                'LI' => array(
                    'justification' => 'left',
                    'width' => 50
                ),
                'ART' => array(
                    'justification' => 'left',
                    'width' => 50
                ),
                'ART_INFO' => array(
                    'justification' => 'left',
                    'width' => 50
                ),
                'KURZINFO' => array(
                    'justification' => 'right',
                    'width' => 50
                ),
                'MENGE' => array(
                    'justification' => 'right',
                    'width' => 50
                ),
                'OK' => array(
                    'justification' => 'left',
                    'width' => 50
                )
            )
        );

        $pdf->ezTable($arr_n, $cols, $tableoptions);
        $pdf->ezSetDy(-12); // abstand
        $pdf->ezText("______________________________", 10, array(
            'left' => '0'
        ));
        $pdf->ezText("Unterschrift $bb->benutzername", 8, array(
            'left' => '20'
        ));

        $pdf->ezSetDy(20); // abstand
        $pdf->ezText("______________________________", 10, array(
            'left' => '500'
        ));
        $bb->get_benutzer_infos(Auth::user()->id);
        $pdf->ezText("$ein_ausgabe_text von $bb->benutzername", 8, array(
            'left' => '520'
        ));

        $this->werkzeug_austragen($w_id);
        ob_end_clean(); // ausgabepuffer leeren

        $pdf->ezStream();
    }

    function werkzeug_austragen($w_id)
    {
        $db_abfrage = "UPDATE WERKZEUGE SET BENUTZER_ID=NULL WHERE AKTUELL='1' && ID='$w_id'";
        DB::update($db_abfrage);
        echo "Werkzeug aus Liste entfernt!";
    }

    function get_anzahl_werkzeuge($art_nr, $beleg_id)
    {
        $db_abfrage = "SELECT * FROM WERKZEUGE WHERE ARTIKEL_NR='$art_nr' && BELEG_ID='$beleg_id' && AKTUELL='1'";
        $result = DB::select($db_abfrage);
        return count($result);
    }

    function werkzeug_loeschen($w_id)
    {
        $db_abfrage = "UPDATE WERKZEUGE SET AKTUELL='0' WHERE AKTUELL='1' && ID='$w_id'";
        DB::update($db_abfrage);
        echo "Werkzeug aus Liste entfernt!";
    }

    function werkzeug_rueckgabe_alle($b_id)
    {
        $db_abfrage = "UPDATE WERKZEUGE SET BENUTZER_ID=NULL WHERE AKTUELL='1' && BENUTZER_ID='$b_id'";
        DB::update($db_abfrage);
    }

    function pdf_rueckgabeschein_alle($b_id, $scheintext = 'Rückgabeschein', $ein_ausgabe_text = 'Bearbeitet')
    {
        $arr = $this->werkzeugliste_arr($b_id);
        $pdf = new Cezpdf ('a4', 'landscape');
        $bpdf = new b_pdf ();
        $bpdf->b_header($pdf, 'Partner', session()->get('partner_id'), 'landscape', 'Helvetica.afm', 6);

        $anz = count($arr);
        for ($a = 0; $a < $anz; $a++) {
            $beleg_id = $arr [$a] ['BELEG_ID'];
            $art_nr = $arr [$a] ['ARTIKEL_NR'];
            $menge = $arr [$a] ['MENGE'];
            $kurzinfo = $arr [$a] ['KURZINFO'];
            $w_id = $arr [$a] ['ID'];

            $r = new rechnung ();
            $r->rechnung_grunddaten_holen($beleg_id);
            $katalog_info = $r->artikel_info($r->rechnungs_aussteller_id, $art_nr);
            $art_info = $katalog_info [0] ['BEZEICHNUNG'];

            $lieferant = $r->rechnungs_aussteller_name;
            $bb = new benutzer ();
            $bb->get_benutzer_infos($b_id);
            $arr_n [$a] ['LI'] = $lieferant;
            $arr_n [$a] ['WBNR'] = 'W-' . $w_id;
            $arr_n [$a] ['ART'] = $art_nr;
            $arr_n [$a] ['ART_INFO'] = $art_info;
            $arr_n [$a] ['KURZINFO'] = $kurzinfo;
            $arr_n [$a] ['MENGE'] = $menge;
            $arr_n [$a] ['OK'] = '';
        }

        $pdf->ezText("<b>$scheintext Mitarbeiter: $bb->benutzername</b>", 14, array(
            'left' => '0'
        ));
        $datum = date("d.m.Y");
        $pdf->ezText("Datum $datum", 10, array(
            'left' => '0'
        ));

        $pdf->ezSetDy(-12); // abstand

        /* Spaltendefinition */
        $cols = array(
            'LI' => "<b>LIEFERANT</b>",
            'WBNR' => "<b>WBNR</b>",
            'ART' => "<b>ARTNR</b>",
            'ART_INFO' => "<b>BEZEICHNUNG</b>",
            'KURZINFO' => "<b>HINWEIS</b>",
            'MENGE' => "<b>MENGE</b>",
            'OK' => "<b>i.O</b>"
        );

        /* Tabellenparameter */
        $tableoptions = array(
            'width' => 600,
            'xPos' => 410,
            'shaded' => 0, // shaded: 0-->Zeile 1 & Zeile 2 --> weiss 1-->Zeile 1 = weiss Zeile 2= grau 2-->Zeile 1= grauA Zeile 2= grauB
            'showHeadings' => 1, // zeig Überschriften der spalten
            'showLines' => 0, // Mach Linien
            'lineCol' => array(
                0.0,
                0.0,
                0.0
            ), // Linienfarbe, hier schwarz

            'fontSize' => 8, // schriftgroesse
            'titleFontSize' => 8, // schriftgroesse Überschrift
            'splitRows' => 0,
            'protectRows' => 0,
            'innerLineThickness' => 0.5,
            'outerLineThickness' => 0.5,
            'rowGap' => 1,
            'colGap' => 1,
            'cols' => array(
                'LI' => array(
                    'justification' => 'left',
                    'width' => 50
                ),
                'ART' => array(
                    'justification' => 'left',
                    'width' => 50
                ),
                'ART_INFO' => array(
                    'justification' => 'left',
                    'width' => 50
                ),
                'KURZINFO' => array(
                    'justification' => 'right',
                    'width' => 50
                ),
                'MENGE' => array(
                    'justification' => 'right',
                    'width' => 50
                ),
                'OK' => array(
                    'justification' => 'left',
                    'width' => 50
                )
            )
        );

        $pdf->ezTable($arr_n, $cols, $tableoptions);
        $pdf->ezSetDy(-12); // abstand
        $pdf->ezText("______________________________", 10, array(
            'left' => '0'
        ));
        $pdf->ezText("Unterschrift $bb->benutzername", 8, array(
            'left' => '20'
        ));

        $pdf->ezSetDy(20); // abstand
        $pdf->ezText("______________________________", 10, array(
            'left' => '500'
        ));
        $bb->get_benutzer_infos(Auth::user()->id);
        $pdf->ezText("$ein_ausgabe_text von $bb->benutzername", 8, array(
            'left' => '520'
        ));

        ob_end_clean(); // ausgabepuffer leeren
        $pdf->ezStream();
    }

    function form_werkzeug_zuweisen($w_id)
    {
        $f = new formular ();
        $f->erstelle_formular('Werkzeug hinzufügen', '');
        $f->hidden_feld('w_id', $w_id);
        $bb = new benutzer ();
        $this->get_werkzeug_info($w_id);
        $f->text_feld_inaktiv('Bezeichnung', 'w', $this->werkzeug_bez, 100, 'wbz');
        $js = '';
        $bb->dropdown_benutzer2('Mitarbeiter wählen', 'b_id', 'b_id', $js);
        $f->hidden_feld('option', 'werkzeug_zuweisen_snd');
        $f->send_button('btn_snd', 'Zuweisen');
        $f->ende_formular();
    }

    function get_werkzeug_info($w_id)
    {
        $db_abfrage = "SELECT * FROM WERKZEUGE WHERE ID='$w_id' ORDER BY DAT";
        $result = DB::select($db_abfrage);
        unset ($this->werkzeug_bez);
        unset ($this->lieferant);
        if (!empty($result)) {
            $row = $result[0];
            $beleg_id = $row ['BELEG_ID'];
            $art_nr = $row ['ARTIKEL_NR'];

            $r = new rechnung ();
            $r->rechnung_grunddaten_holen($beleg_id);
            $katalog_info = $r->artikel_info($r->rechnungs_aussteller_id, $art_nr);
            $this->werkzeug_bez = $katalog_info [0] ['BEZEICHNUNG'];
            $this->lieferant = $r->rechnungs_aussteller_name;
        }
    }

    function werkzeug_zuweisen($b_id, $w_id)
    {
        $db_abfrage = "UPDATE WERKZEUGE SET BENUTZER_ID='$b_id' WHERE ID='$w_id' && AKTUELL='1'";
        DB::update($db_abfrage);
    }

    function werkzeugliste_nach_mitarbeiter()
    {
        $arr = $this->werkzeugliste_verteilt_arr();
        if (!empty($arr)) {
            $anz = count($arr);
            echo "<table class=\"sortable striped\">";
            echo "<tr><th>LIEFERANT</th><th>WBNR</th><th>BESCHREIBUNG</th><th>KURZINFO</th><th>MENGE</th><th>MITARBITER</th><th>OPTION</th></tr>";
            $tmp_b_id = $arr [0] ['BENUTZER_ID'];
            for ($a = 0; $a < $anz; $a++) {
                $w_id = $arr [$a] ['ID'];
                $beleg_id = $arr [$a] ['BELEG_ID'];
                $art_nr = $arr [$a] ['ARTIKEL_NR'];
                $menge = $arr [$a] ['MENGE'];
                $kurzinfo = $arr [$a] ['KURZINFO'];

                $r = new rechnung ();
                $r->rechnung_grunddaten_holen($beleg_id);
                $katalog_info = $r->artikel_info($r->rechnungs_aussteller_id, $art_nr);
                $art_info = $katalog_info [0] ['BEZEICHNUNG'];

                $lieferant = $r->rechnungs_aussteller_name;
                $link_beleg = "<a href='" . route('web::rechnungen::legacy', ['option' => 'rechnungs_uebersicht', 'belegnr' => $beleg_id]) . "'>$lieferant</a>";
                $wb_nr = 'W-' . $w_id;
                $b_id = $arr [$a] ['BENUTZER_ID'];
                if ($tmp_b_id != $b_id && $a != 0) {
                    $tmp_b_id = $b_id;
                    echo "</table>";
                    echo "<table class=\"sortable striped\">";
                    echo "<tr><th>LIEFERANT</th><th>WBNR</th><th>BESCHREIBUNG</th><th>KURZINFO</th><th>MENGE</th><th>MITARBITER</th><th>OPTION</th></tr>";
                }
                echo "<tr><td>$link_beleg</td><td>$wb_nr</td><td>$art_info</td><td>$kurzinfo</td><td>$menge</td>";

                if ($b_id) {
                    $bb = new benutzer ();
                    $bb->get_benutzer_infos($b_id);
                    $link_mitarbeiter_liste = "<a href='" . route('web::benutzer::legacy', ['option' => 'werkzeuge_mitarbeiter', 'b_id' => $b_id]) . "'>$bb->benutzername</a>";
                    echo "<td>$link_mitarbeiter_liste</td>";
                } else {
                    $link_frei = "<a href='" . route('web::benutzer::legacy', ['option' => 'werkzeug_zuweisen', 'w_id' => $w_id]) . "'>Zuweisen</a>";
                    echo "<td>FREI $link_frei</td>";
                }
                if ($b_id == NULL) {
                    $link_loeschen = "<a href='" . route('web::benutzer::legacy', ['option' => 'werkzeug_raus', 'w_id' => $w_id]) . "'>Aus Liste Löschen</td>";
                } else {
                    $link_loeschen = "<a href='" . route('web::benutzer::legacy', ['option' => 'werkzeug_rueckgabe', 'b_id' => $b_id, 'w_id' => $w_id]) . "'>Einzelrückgabe</td>";
                }
                echo "<td>$link_loeschen</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    }

    function werkzeugliste_verteilt_arr()
    {
        $db_abfrage = "SELECT * FROM WERKZEUGE WHERE AKTUELL='1' && BENUTZER_ID IS NOT NULL ORDER BY BENUTZER_ID, ARTIKEL_NR";
        $result = DB::select($db_abfrage);
        return $result;
    }
} // end class

