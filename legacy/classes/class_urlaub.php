<?php

class urlaub
{
    public $benutzername;
    public $anspruch_monate;
    public $anspruch_jahr;
    public $anspruch_vorjahre;
    public $anspruch_gesamt;
    public $genommen;
    public $geplant;
    public $rest_aktuell;
    public $rest_jahr;
    public $eintritt;
    public $austritt;
    public $urlaub;
    public $benutzer_id;
    public $gewerk_id;
    public $stunden_pw;
    public $stundensatz;

    function jahresuebersicht_alle_pdf($jahr)
    {
        $users = $this->mitarbeiter_arr($jahr);
        if ($users->isEmpty()) {
            echo "Keine Mitarbeiter vorhanden, Eintrittsdaten erst nach $jahr";
        } else {
            $zaehler = 0;

            ob_end_clean(); // ausgabepuffer leeren
            $pdf = new Cezpdf ('a4', 'portrait');
            $bpdf = new b_pdf ();
            $bpdf->b_header($pdf, 'Partner', session()->get('partner_id'), 'portrait', 'Helvetica.afm', 6);
            $cols = array(
                'MITARBEITER' => "Mitarbeiter",
                'MONATE' => "Monate",
                'ANSPRUCH' => "Anspruch $jahr",
                'REST_VORJAHR' => "Rest Vorjahr",
                'G_ANSPRUCH' => "Anspruch gesamt",
                'GENOMMEN' => "Genommen",
                'GEPLANT' => "Geplant",
                'REST_AKT' => "Rest aktuell",
                'REST_J' => "Rest Jahr"
            );

            foreach ($users as $user) {
                $benutzer_id = $user->id;
                $this->jahresuebersicht_mitarbeiter_kurz($benutzer_id, $jahr);

                $table_arr [$zaehler] ['MITARBEITER'] = "$this->benutzername";
                $table_arr [$zaehler] ['MONATE'] = "$this->anspruch_monate";
                $table_arr [$zaehler] ['ANSPRUCH'] = "$this->anspruch_jahr";
                $table_arr [$zaehler] ['REST_VORJAHR'] = "$this->anspruch_vorjahre";
                $table_arr [$zaehler] ['G_ANSPRUCH'] = "$this->anspruch_gesamt";
                $table_arr [$zaehler] ['GENOMMEN'] = "$this->genommen";
                $table_arr [$zaehler] ['GEPLANT'] = "$this->geplant";
                $table_arr [$zaehler] ['REST_AKT'] = "$this->rest_aktuell";
                $table_arr [$zaehler] ['REST_J'] = "$this->rest_jahr";
                $zaehler++;
            } // end for

            $pdf->ezTable($table_arr, $cols, "Jahresübersicht $jahr", array(
                'showHeadings' => 1,
                'showLines' => '1',
                'shaded' => 1,
                'shadeCol' => array(
                    0.78,
                    0.95,
                    1
                ),
                'shadeCol2' => array(
                    0.1,
                    0.5,
                    1
                ),
                'titleFontSize' => 10,
                'fontSize' => 7,
                'xPos' => 50,
                'xOrientation' => 'right',
                'width' => 500,
                'cols' => array(
                    'MITARBEITER' => array(
                        'justification' => 'right',
                        'width' => 80
                    )
                )
            ));
            $datum_uhrzeit = date("d.m.Y      H:i");
            $pdf->ezSetDy(-20); // abstand
            $pdf->ezText("Übersicht vom $datum_uhrzeit Uhr", 7, array(
                'left' => '0'
            ));

            $pdf->ezStream();
        }
    }


    /**
     * @param $jahr
     * @return array|\Illuminate\Database\Eloquent\Collection
     */
    function mitarbeiter_arr($jahr)
    {
        $users = \App\Models\User::whereYear('join_date', '<=', $jahr)->whereYear('leave_date', '>=', $jahr)->orWhereNull('leave_date')->orderBy('name', 'asc')->select(['id', 'name', 'holidays', 'join_date', 'leave_date'])->get();
        return $users;
    }

    function jahresuebersicht_mitarbeiter_kurz($benutzer_id, $jahr)
    {
        $this->mitarbeiter_details($benutzer_id);
        $mitarbeiter = $this->benutzername;

        $eintritt = $this->eintritt;
        $eintritt_arr = explode("-", $eintritt);
        $eintritt_jahr = $eintritt_arr [0];

        $austritt = $this->austritt;
        $austritt_arr = explode("-", $austritt);
        $austritt_jahr = $austritt_arr [0];

        $anspruch = $this->urlaub;
        $anspruch_pro_m = $anspruch / 12;
        $anspruch_pro_tag = $anspruch / 365;

        /* Erstes Jahr in der Firma */
        if ($eintritt_jahr == $jahr) {
            /* Mitarbeiter noch beschäftigt */

            if ($austritt_jahr == '0000') {
                $bis = "$jahr-12-31";
                $tage = $this->tage_zwischen($eintritt, $bis);
                $monate = $tage / 30;
            } else {

                $jahre = $austritt_jahr - $eintritt_jahr;
                /* Mitarbeiter nicht mehr beschäftigt, gleiches Jahr ausgetreten */
                if ($jahre == 0) {
                    $tage = $this->tage_zwischen($eintritt, $austritt);
                    $monate = $tage / 30;
                }
                /* Mitarbeiter nicht mehr beschäftigt, Jahre danach ausgetreten */
                if ($jahre > 0) {
                    $bis = "$jahr-12-31";
                    $tage = $this->tage_zwischen($eintritt, $bis);
                    $monate = $tage / 30;
                }
            }
        }

        /* Jahre danach in der Firma */
        if ($eintritt_jahr < $jahr) {
            /* Mitarbeiter noch beschäftigt */

            if ($austritt_jahr == '0000') {
                $tage = 365;
                $monate = 12;
            } else {

                $jahre = $austritt_jahr - $eintritt_jahr;

                /* Mitarbeiter nicht mehr beschäftigt, 1 Jahr danach ausgetreten */
                if ($jahre == 1) {
                    $von = "$jahr-01-01";
                    $tage = $this->tage_zwischen($von, $austritt);
                    $monate = $tage / 30;
                }
                /* Mitarbeiter nicht mehr beschäftigt, Jahre danach ausgetreten */
                if ($jahre > 1) {
                    if ($jahr != $austritt_jahr) {
                        $tage = 365;
                        $monate = 12;
                    } else {
                        $von = "$jahr-01-01";
                        $tage = $this->tage_zwischen($von, $austritt);
                        $monate = $tage / 30;
                    }
                }
            }
        }

        // $monate = floor($monate);
        $anspruch = $monate * $anspruch_pro_m;
        /* Jahr vor Eintritt in die Firma */
        if ($eintritt_jahr > $jahr) {
            $anspruch = '0.0';
        }

        $genommen_arr = $this->anzahl_genommene_tage($jahr, $benutzer_id);
        $geplant_arr = $this->anzahl_geplanter_tage($jahr, $benutzer_id);
        if (isset($geplant_arr)) {
            $geplant = $geplant_arr['GEPLANT'];
        } else {
            $geplant = '0';
        }
        if (isset($genommen_arr)) {
            $genommen = $genommen_arr['GENOMMEN'];
        } else {
            $genommen = '0';
        }
        $rest_aus_vorjahren = $this->rest_aus_vorjahren($jahr, $benutzer_id);

        $g_anspruch = $anspruch + $rest_aus_vorjahren;
        $g_anspruch = $this->runden($g_anspruch);
        $rest_aktuell = $g_anspruch - $genommen;
        $rest_jahr = $g_anspruch - $genommen - $geplant;

        $g_anspruch = nummer_punkt2komma($g_anspruch);

        $rest_aus_vorjahren = nummer_punkt2komma($rest_aus_vorjahren);
        $anspruch = nummer_punkt2komma($anspruch);
        $geplant = nummer_punkt2komma($geplant);
        $genommen = nummer_punkt2komma($genommen);
        $rest_aktuell = nummer_punkt2komma($rest_aktuell);
        $rest_jahr = nummer_punkt2komma($rest_jahr);
        $anspruch = nummer_punkt2komma($anspruch);
        $monate = nummer_punkt2komma($monate);
        // echo "<tr class=\"zeile1\"><td>$mitarbeiter</td><td>$link_urlaubsantrag $link_jahresansicht </td><td>$monate</td><td>$anspruch</td><td> $rest_aus_vorjahren</td><td><b>$g_anspruch</b></td><td>$genommen</td><td>$geplant</td><td>$rest_aktuell <b>($rest_jahr)</b></td></tr>";

        $this->anspruch_jahr = $anspruch;
        $this->anspruch_monate = $monate;
        $this->anspruch_gesamt = $g_anspruch;
        $this->anspruch_vorjahre = $rest_aus_vorjahren;
        $this->genommen = $genommen;
        $this->geplant = $geplant;
        $this->rest_aktuell = $rest_aktuell;
        $this->rest_jahr = $rest_jahr;

        unset ($genommen_arr);
        unset ($geplant_arr);

        // echo "</TABLE>";
    }

    function mitarbeiter_details($benutzer_id)
    {
        $result = \App\Models\User::find($benutzer_id);
        if (isset($result)) {
            $this->benutzername = $result->name;
            $this->benutzer_id = $benutzer_id;
            $this->gewerk_id = $result->trade_id;
            $this->eintritt = $result->join_date;
            $this->austritt = $result->leave_date;
            $this->urlaub = $result->holidays;
            $this->stunden_pw = $result->hours_per_week;
            $this->stundensatz = $result->hourly_rate;
        } else {
            return false;
        }
    }

    function tage_zwischen($von, $bis)
    {
        $von = strtotime("$von");
        $bis = strtotime("$bis");
        $differenz = $bis - $von;
        // $differenz = floor($differenz / (3600*24));
        $differenz = floor($differenz / 86400);
        return $differenz;
    }

    function anzahl_genommene_tage($jahr, $benutzer_id, $art = 'Urlaub')
    {
        $result = DB::select("SELECT name, holidays AS ANSPRUCH, SUM( ANTEIL )  AS GENOMMEN , holidays - SUM( ANTEIL ) AS REST
FROM URLAUB , users
WHERE URLAUB.ART = ? && URLAUB.BENUTZER_ID = users.id && URLAUB.BENUTZER_ID=? && DATE_FORMAT( DATUM, '%Y' ) = ? && DATUM<= CURDATE() && AKTUELL='1' GROUP BY URLAUB.BENUTZER_ID LIMIT 0 , 1 ", [$art, $benutzer_id, $jahr]);
        return isset($result) ? $result[0] : null;
    }

    function anzahl_geplanter_tage($jahr, $benutzer_id, $art = 'Urlaub')
    {
        $result = DB::select("SELECT name, holidays AS ANSPRUCH, SUM( ANTEIL ) AS GEPLANT , holidays - SUM( ANTEIL ) AS REST
FROM URLAUB , users
WHERE URLAUB.ART = ? && URLAUB.BENUTZER_ID = users.id && URLAUB.BENUTZER_ID=? && DATE_FORMAT( DATUM, '%Y' ) = ? && DATUM> CURDATE() && AKTUELL='1'  GROUP BY URLAUB.BENUTZER_ID LIMIT 0 , 1 ", [$art, $benutzer_id, $jahr]);
        return isset($result) ? $result[0] : null;
    }

    function rest_aus_vorjahren($jahr, $benutzer_id)
    {
        $mitarbeiter_arr = $this->mitarbeiter_info($benutzer_id);
        $eintritt = $mitarbeiter_arr->join_date;
        $eintritt_arr = explode("-", $eintritt);
        $eintritt_jahr = $eintritt_arr [0];

        $austritt = $mitarbeiter_arr->leave_date;
        $austritt_arr = explode("-", $austritt);
        $vorjahr = $jahr - 1;

        $rest_tage = 0;
        for ($a = $eintritt_jahr; $a <= $vorjahr; $a++) {
            $rest_tage = $rest_tage + $this->rest_tage($a, $benutzer_id);
        }

        return $rest_tage;
    }

    function mitarbeiter_info($benutzer_id)
    {
        $result = \App\Models\User::findOrFail($benutzer_id);
        return $result;
    }

    function rest_tage($jahr, $benutzer_id)
    {
        $mitarbeiter_arr = $this->mitarbeiter_info($benutzer_id);
        $eintritt = $mitarbeiter_arr->join_date;
        $eintritt_arr = explode("-", $eintritt);
        $eintritt_jahr = $eintritt_arr [0];

        $austritt = $mitarbeiter_arr->leave_date;
        $austritt_arr = explode("-", $austritt);
        $austritt_jahr = $austritt_arr [0];

        $anspruch = $mitarbeiter_arr->holidays;

        $anspruch_pro_m = $anspruch / 12;
        $anspruch_pro_tag = $anspruch / 365;

        /* Erstes Jahr in der Firma */
        if ($eintritt_jahr == $jahr) {
            /* Mitarbeiter noch beschäftigt */

            if ($austritt_jahr == '0000') {
                $bis = "$jahr-12-31";
                $tage = $this->tage_zwischen($eintritt, $bis);
                $monate = $tage / 30;
            } else {

                $jahre = $austritt_jahr - $eintritt_jahr;
                /* Mitarbeiter nicht mehr beschäftigt, gleiches Jahr ausgetreten */
                if ($jahre == 0) {
                    $tage = $this->tage_zwischen($eintritt, $austritt);
                    $monate = $tage / 30;
                }
                /* Mitarbeiter nicht mehr beschäftigt, Jahre danach ausgetreten */
                if ($jahre > 0) {
                    $bis = "$jahr-12-31";
                    $tage = $this->tage_zwischen($eintritt, $bis);
                    $monate = $tage / 30;
                }
            }
        }

        /* Jahre danach in der Firma */
        if ($eintritt_jahr < $jahr) {
            /* Mitarbeiter noch beschäftigt */

            if ($austritt_jahr == '0000') {
                $tage = 365;
                $monate = 12;
            } else {

                $jahre = $austritt_jahr - $eintritt_jahr;

                /* Mitarbeiter nicht mehr beschäftigt, 1 Jahr danach ausgetreten */
                if ($jahre == 1) {
                    $von = "$jahr-01-01";
                    $tage = $this->tage_zwischen($von, $austritt);
                    $monate = $tage / 30;
                }
                /* Mitarbeiter nicht mehr beschäftigt, Jahre danach ausgetreten */
                if ($jahre > 1) {
                    if ($jahr != $austritt_jahr) {
                        $tage = 365;
                        $monate = 12;
                    } else {
                        $von = "$jahr-01-01";
                        $tage = $this->tage_zwischen($von, $austritt);
                        $monate = $tage / 30;
                    }
                }
            }
        }

        // $monate = floor($monate);
        $anspruch = $monate * $anspruch_pro_m;
        /* Jahr vor Eintritt in die Firma */
        if ($eintritt_jahr > $jahr) {
            $anspruch = '0.0';
        }

        $genommen_arr = $this->anzahl_genommene_tage($jahr, $benutzer_id);
        $geplant_arr = $this->anzahl_geplanter_tage($jahr, $benutzer_id);
        if (isset($geplant_arr)) {
            $geplant = $geplant_arr['GEPLANT'];
        } else {
            $geplant = '0.0';
        }
        if (isset($genommen_arr)) {
            $genommen = $genommen_arr['GENOMMEN'];
        } else {
            $genommen = '0.0';
        }
        $rest_aktuell = $anspruch - $genommen;
        $r1 = $anspruch - $genommen - $geplant;
        return $r1;
    }

    function runden($zahl)
    {
        $zahl = sprintf('%01.2f', $zahl);
        $zahl_arr = explode(".", $zahl);
        $nachkomma = $zahl_arr [1];
        if ($nachkomma == '50') {
            $neue_zahl = $zahl;
        }

        if ($nachkomma > '50') {
            $neue_zahl = round($zahl);
        }
        if ($nachkomma < '50') {
            $neue_zahl = floor($zahl);
        }
        return $neue_zahl;
    }

    function jahresuebersicht_anzeigen($jahr)
    {
        $rest_aktuell = 0;
        $mitarbeiter_arr = $this->mitarbeiter_arr($jahr);
        $vorjahr = $jahr - 1;
        if ($mitarbeiter_arr->isEmpty()) {
            echo "Keine Mitarbeiter vorhanden, Eintrittsdaten erst nach $jahr";
        } else {
            echo "<table class=\"sortable striped\">";
            echo "<thead>";
            echo "<tr><th>Mitarbeiter</th><th>Optionen</th><th>Monate $jahr</th><th>Tage $jahr</th><th>Rest $vorjahr</th><th>Anspruch Gesamt</th><th>Genommen</th><th>Geplant</th><th>Resturlaub</th></tr>";
            echo "</thead>";

            $zaehler = 0;
            foreach ($mitarbeiter_arr as $user) {
                $zaehler++;
                $mitarbeiter = $user->name;
                $benutzer_id = $user->id;

                $eintritt = $user->join_date;
                $eintritt_arr = explode("-", $eintritt);
                $eintritt_jahr = $eintritt_arr [0];

                $austritt = $user->leave_date;
                $austritt_arr = explode("-", $austritt);
                $austritt_jahr = $austritt_arr [0];

                $anspruch = $user->holidays;
                $anspruch_pro_m = $anspruch / 12;
                $anspruch_pro_tag = $anspruch / 365;

                /* Erstes Jahr in der Firma */
                if ($eintritt_jahr == $jahr) {
                    /* Mitarbeiter noch beschäftigt */

                    if ($austritt_jahr == '0000') {
                        $bis = "$jahr-12-31";
                        $tage = $this->tage_zwischen($eintritt, $bis);
                        $monate = $tage / 30;
                    } else {

                        $jahre = $austritt_jahr - $eintritt_jahr;
                        /* Mitarbeiter nicht mehr beschäftigt, gleiches Jahr ausgetreten */
                        if ($jahre == 0) {
                            $tage = $this->tage_zwischen($eintritt, $austritt);
                            $monate = $tage / 30;
                        }
                        /* Mitarbeiter nicht mehr beschäftigt, Jahre danach ausgetreten */
                        if ($jahre > 0) {
                            $bis = "$jahr-12-31";
                            $tage = $this->tage_zwischen($eintritt, $bis);
                            $monate = $tage / 30;
                        }
                    }
                }

                /* Jahre danach in der Firma */
                if ($eintritt_jahr < $jahr) {
                    /* Mitarbeiter noch beschäftigt */

                    if ($austritt_jahr == '0000') {
                        $tage = 365;
                        $monate = 12;
                    } else {

                        $jahre = $austritt_jahr - $eintritt_jahr;

                        /* Mitarbeiter nicht mehr beschäftigt, 1 Jahr danach ausgetreten */
                        if ($jahre == 1) {
                            $von = "$jahr-01-01";
                            $tage = $this->tage_zwischen($von, $austritt);
                            $monate = $tage / 30;
                        }
                        /* Mitarbeiter nicht mehr beschäftigt, Jahre danach ausgetreten */
                        if ($jahre > 1) {
                            if ($jahr != $austritt_jahr) {
                                $tage = 365;
                                $monate = 12;
                            } else {
                                $von = "$jahr-01-01";
                                $tage = $this->tage_zwischen($von, $austritt);
                                $monate = $tage / 30;
                            }
                        }
                    }
                }

                // $monate = floor($monate);
                $anspruch = $monate * $anspruch_pro_m;
                /* Jahr vor Eintritt in die Firma */
                if ($eintritt_jahr > $jahr) {
                    $anspruch = '0.0';
                }

                $genommen_arr = $this->anzahl_genommene_tage($jahr, $benutzer_id);
                $geplant_arr = $this->anzahl_geplanter_tage($jahr, $benutzer_id);

                if (isset($geplant_arr)) {
                    $geplant = $geplant_arr['GEPLANT'];
                } else {
                    $geplant = '0';
                }
                if (isset($genommen_arr)) {
                    $genommen = $genommen_arr['GENOMMEN'];
                } else {
                    $genommen = '0';
                }
                $rest_aus_vorjahren = $this->rest_aus_vorjahren($jahr, $benutzer_id);

                $link_urlaubsantrag = "<a href='" . route('web::urlaub::legacy', ['option' => 'urlaubsantrag', 'benutzer_id' => $benutzer_id]) . "'>Abwesenheit eintragen</a>&nbsp;";
                $link_jahresansicht = "<a href='" . route('web::urlaub::legacy', ['option' => 'jahresansicht', 'jahr' => $jahr, 'benutzer_id' => $benutzer_id]) . "'>Jahresansicht</a>&nbsp;";

                if ($austritt != '0000-00-00') {
                    $mitarbeiter = "<b>$mitarbeiter</b>";
                }
                $g_anspruch = $anspruch + $rest_aus_vorjahren;
                $g_anspruch = $this->runden($g_anspruch);
                $rest_aktuell = $g_anspruch - $genommen;
                $rest_jahr = $g_anspruch - $genommen - $geplant;

                $g_anspruch = nummer_punkt2komma($g_anspruch);

                $rest_aus_vorjahren = nummer_punkt2komma($rest_aus_vorjahren);
                $anspruch = nummer_punkt2komma($anspruch);
                $geplant = nummer_punkt2komma($geplant);
                $genommen = nummer_punkt2komma($genommen);
                $rest_aktuell = nummer_punkt2komma($rest_aktuell);
                $rest_jahr = nummer_punkt2komma($rest_jahr);
                $anspruch = nummer_punkt2komma($anspruch);
                $monate = nummer_punkt2komma($monate);
                if ($zaehler == 1) {
                    echo "<tr class=\"zeile1\"><td>$mitarbeiter</td><td>$link_urlaubsantrag $link_jahresansicht </td><td align=\"right\">$monate</td><td align=\"right\">$anspruch</td><td align=\"right\"> $rest_aus_vorjahren</td><td align=\"right\"><b>$g_anspruch</b></td><td align=\"right\">$genommen</td><td align=\"right\">$geplant</td><td align=\"right\">$rest_aktuell <b>($rest_jahr)</b></td></tr>";
                }
                if ($zaehler == 2) {
                    echo "<tr class=\"zeile2\"><td>$mitarbeiter</td><td>$link_urlaubsantrag $link_jahresansicht </td><td align=\"right\">$monate</td><td align=\"right\">$anspruch</td><td align=\"right\"> $rest_aus_vorjahren</td><td align=\"right\"><b>$g_anspruch</b></td><td align=\"right\">$genommen</td><td align=\"right\">$geplant</td><td align=\"right\">$rest_aktuell <b>($rest_jahr)</b></td></tr>";
                    $zaehler = 0;
                }
                unset ($genommen_arr);
                unset ($geplant_arr);
            }
            echo "</table>";
        }

        // $this->jahresuebersicht_mitarbeiter_kurz(1, 2010);
    }

    function jahres_ansicht($benutzer_id, $jahr)
    {
        $result = DB::select("SELECT U_DAT, name, ANTRAG_D, DATUM, ANTEIL, ART FROM users JOIN URLAUB ON (users.id = URLAUB.BENUTZER_ID) WHERE users.id=? && DATE_FORMAT(URLAUB.DATUM, '%Y') = ? && AKTUELL='1' ORDER BY  DATUM ASC ", [$benutzer_id, $jahr]);
        $result = collect($result);

        if (!$result->isEmpty()) {
            $link_benutzer_jahr_pdf = "<a href='" . route('web::urlaub::legacy', ['option' => 'jahresansicht_pdf', 'jahr' => $jahr, 'benutzer_id' => $benutzer_id]) . "'>PDF-Ansicht</a>";
            echo "<table><tr class=\"feldernamen\"><td colspan=\"6\">$link_benutzer_jahr_pdf</td></tr>";
            echo "<tr class=\"feldernamen\"><td>Zeile</td><td>Antrag vom</td><td>Art</td><td>Datum, Wochentag</td><td>Anteil</td><td>Option</td></tr>";
            $summe_tage = 0;
            $zeile = 0;
            foreach ($result as $user) {
                $zeile++;
                $benutzername = $user['name'];
                $antrag_vom = $user['ANTRAG_D'];
                $urlaubstag = $user['DATUM'];
                $anteil = $user['ANTEIL'];
                $art = $user['ART'];
                $summe_tage = $summe_tage + $anteil;
                $antrag_vom = date_mysql2german($antrag_vom);
                $urlaubstag = date_mysql2german($urlaubstag);
                $wochentag = $this->tagesname($urlaubstag);
                $u_dat = $user['U_DAT'];
                $link_loeschen = "<a href='" . route('web::urlaub::legacy', ['option' => 'urlaubstag_loeschen', 'jahr' => $jahr, 'benutzer_id' => $benutzer_id, 'u_dat' => $u_dat]) . "'>Urlaubstag löschen</a>";
                echo "<tr class=\"zeile1\"><td>$zeile</td><td>$antrag_vom</td><td>$art</td><td>$urlaubstag, $wochentag</td><td>$anteil</td><td>$link_loeschen</td></tr>";
            }
            echo "$benutzername Gesamt: $summe_tage Tage";
            echo "</TABLE>";
        } else {
            echo "KEINE URLAUBSDATEN VORHANDEN";
        }
    }

    function tagesname($datum)
    {
        $tagesname = $this->date2name($datum);
        if ($tagesname == 'Monday') {
            return 'Montag';
        }
        if ($tagesname == 'Tuesday') {
            return 'Dienstag';
        }
        if ($tagesname == 'Wednesday') {
            return 'Mittwoch';
        }
        if ($tagesname == 'Thursday') {
            return 'Donnerstag';
        }
        if ($tagesname == 'Friday') {
            return 'Freitag';
        }
        if ($tagesname == 'Saturday') {
            return 'Samstag';
        }
        if ($tagesname == 'Sunday') {
            return 'Sonntag';
        }
    }

    function date2name($datum)
    {
        $datum = date_german2mysql($datum);
        $datum_arr = explode("-", $datum);
        $jahr = $datum_arr [0];
        $monat = $datum_arr [1];
        $tag = $datum_arr [2];
        // $wochentag_name = date('l', mktime(0, 0, 0, $monat , $tag, $jahr));

        $datum_arr1 = getdate(mktime(0, 0, 0, $monat, $tag, $jahr));
        $wochentag_name = $datum_arr1 ['weekday'];
        return $wochentag_name;
    }

    function jahres_ansicht_pdf($benutzer_id, $jahr)
    {
        $this->jahresuebersicht_mitarbeiter_kurz($benutzer_id, $jahr);

        $result = DB::select("SELECT U_DAT, name, ANTRAG_D, DATUM, ANTEIL, ART FROM users JOIN URLAUB ON (users.id = URLAUB.BENUTZER_ID) WHERE users.id=? && DATE_FORMAT(URLAUB.DATUM, '%Y') = ? && AKTUELL='1' ORDER BY DATUM ASC ", [$benutzer_id, $jahr]);

        $result = collect($result);

        if (!$result->isEmpty()) {
            ob_end_clean(); // ausgabepuffer leeren
            $pdf = new Cezpdf ('a4', 'portrait');
            $bpdf = new b_pdf ();
            $bpdf->b_header($pdf, 'Partner', session()->get('partner_id'), 'portrait', 'Helvetica.afm', 6);

            $summe_tage = 0;
            $summe_krank = 0;
            $summe_ausgezahlt = 0;
            $summe_unbezahlt = 0;
            $zeile = 0;
            $cols = array(
                'ZEILE' => "Tag",
                'ART' => "Grund",
                'URLAUBSTAG' => "Urlaubstag",
                'ANTEIL' => "Anteil"
            );
            foreach ($result as $user) {
                $zeile++;
                $benutzername = $user['name'];
                $antrag_vom = $user['ANTRAG_D'];
                $urlaubstag = $user['DATUM'];
                $anteil = $user['ANTEIL'];
                $art = $user['ART'];
                if ($art == 'Urlaub') {
                    $summe_tage += $anteil;
                }
                if ($art == 'Krank') {
                    $summe_krank += $anteil;
                }

                if ($art == 'Auszahlung') {
                    $summe_ausgezahlt += $anteil;
                }

                if ($art == 'Unbezahlt') {
                    $summe_unbezahlt += $anteil;
                }

                // echo "$zeile. $antrag_vom $urlaubstag $anteil Tag(-e)<br>";
                $urlaubstag = date_mysql2german($urlaubstag);
                $wochentag = $this->tagesname($urlaubstag);
                $table_arr [$zeile] ['URLAUBSTAG'] = "$urlaubstag, $wochentag";
                $table_arr [$zeile] ['ANTEIL'] = "$anteil";
                $table_arr [$zeile] ['ART'] = "$art";
                $table_arr [$zeile] ['ZEILE'] = "$zeile";
            }
            // echo "$benutzername Gesamt: $summe_tage Tage";

            $zz = $zeile + 1;
            $table_arr [$zz] ['URLAUBSTAG'] = "Genommene Urlaubstage";
            $table_arr [$zz] ['ANTEIL'] = "$summe_tage Tage";
            $zz++;
            $table_arr [$zz] ['URLAUBSTAG'] = "Ausgezahlter Urlaub";
            $table_arr [$zz] ['ANTEIL'] = "$summe_ausgezahlt Tage";
            $zz++;
            $table_arr [$zz] ['URLAUBSTAG'] = "----------------------";
            $table_arr [$zz] ['ANTEIL'] = "-----";
            $zz++;
            $table_arr [$zz] ['URLAUBSTAG'] = "<b>Genommen";
            $su_g = $summe_tage + $summe_ausgezahlt;
            $table_arr [$zz] ['ANTEIL'] = "$su_g Tage</b>";
            $zz++;
            $table_arr [$zz] ['URLAUBSTAG'] = "Anspruch Jahr  ($this->anspruch_monate Monate)";
            $table_arr [$zz] ['ANTEIL'] = "$this->anspruch_jahr Tage</b>";
            $zz++;
            $r_urlaub_jahr = $this->anspruch_jahr - $su_g;
            $table_arr [$zz] ['URLAUBSTAG'] = "Resturlaub Jahr";
            $table_arr [$zz] ['ANTEIL'] = "$r_urlaub_jahr Tage</b>";
            $zz++;
            $table_arr [$zz] ['URLAUBSTAG'] = "Rest aus Vorjahren $this->anspruch_vorjahre";
            $table_arr [$zz] ['ANTEIL'] = $this->runden($this->anspruch_vorjahre) . " Tage";
            $zz++;
            $su_noch = $r_urlaub_jahr + $this->anspruch_vorjahre;
            $table_arr [$zz] ['URLAUBSTAG'] = "Rest Vorjahre + Aktuell";
            $table_arr [$zz] ['ANTEIL'] = "$su_noch Tage</i></b>";
            $zz++;
            $table_arr [$zz] ['URLAUBSTAG'] = "<b><i>Unbezahlter Urlaub";
            $table_arr [$zz] ['ANTEIL'] = "$summe_unbezahlt Tage</i></b>";
            $zz++;
            $table_arr [$zz] ['URLAUBSTAG'] = "Krank im Jahr $jahr";
            $table_arr [$zz] ['ANTEIL'] = "$summe_krank Tage";

            $pdf->ezTable($table_arr, $cols, "Abwesenheit $benutzername  $jahr", array(
                'showHeadings' => 1,
                'showLines' => '1',
                'shaded' => 1,
                'shadeCol' => array(
                    0.78,
                    0.95,
                    1
                ),
                'shadeCol2' => array(
                    0.1,
                    0.5,
                    1
                ),
                'titleFontSize' => 10,
                'fontSize' => 7,
                'xPos' => 50,
                'xOrientation' => 'right',
                'width' => 300,
                'cols' => array(
                    'ZEILE' => array(
                        'justification' => 'right',
                        'width' => 30
                    ),
                    'ART' => array(
                        'justification' => 'right',
                        'width' => 60
                    ),
                    'ANTEIL' => array(
                        'justification' => 'right',
                        'width' => 40
                    )
                )
            ));
            $pdf->ezStream();
        } else {
            echo "KEINE URLAUBSDATEN VORHANDEN";
        }
    }

    function form_urlaubsantrag($benutzer_id)
    {
        $f = new formular ();
        $z = new zeiterfassung ();
        $mitarbeiter_name = $z->get_benutzer_name($benutzer_id);
        $f->erstelle_formular("Urlaubsplanung und Abwesenheit für $mitarbeiter_name", NULL);
        $f->datum_feld('Abwesend vom', 'u_vom', "", 'u_vom');
        $f->datum_feld('Abwesend bis', 'u_bis', "", 'u_bis');
        // $f->radio_button('art', 'krank', 'als Krank eintragen');
        $this->dropdown_art('Abwesenheitsgrund', 'art', 'art', '');
        $f->hidden_feld("benutzer_id", "$benutzer_id");
        $f->hidden_feld("option", "urlaubsantrag_check");
        $f->send_button("submit", "Eintragen");
        $f->ende_formular();
        // $this->tag_danach("2009-12-12");
        // $this->tage_arr("2009-12-10", "2009-12-20");
    }

    function dropdown_art($beschreibung, $name, $id, $js)
    {
        echo "<label for=\"$name\">$beschreibung</label><select name=\"$name\" id=\"$id\" $js> \n";
        echo "<option value=\"Urlaub\" selected>Urlaub</option>\n";
        echo "<option value=\"Krank\">Krank</option>\n";
        echo "<option value=\"Auszahlung\">Auszahlung</option>\n";
        echo "<option value=\"Unbezahlt\">Unbezahlt</option>\n";
        echo "</select>";
    }

    function tage_arr($benutzer_id, $datum_a, $datum_e, $art = 'Urlaub')
    {
        if ($datum_a == $datum_e) {
            if ($this->feiertag($datum_a) == 'Arbeitstag') {
                $anteil = $this->anteil_datum($datum_a);
                $this->urlaubstag_speichern($benutzer_id, $datum_a, $anteil, $art);
            } else {
                echo "Der gewünschte Tag ist ein Feiertag oder Wochenende";
            }
        } else {
            /* ersten Tag eingeben */
            if ($this->feiertag($datum_a) == 'Arbeitstag') {
                $anteil = $this->anteil_datum($datum_a);
                $this->urlaubstag_speichern($benutzer_id, $datum_a, $anteil, $art);
            } else {
                echo "Der gewünschte Tag ist ein Feiertag oder Wochenende";
            }

            $zeile = 0;
            while ($datum_a != $datum_e) {

                $datum_a = $this->tag_danach($datum_a);
                echo $datum_a . '   ';
                $tag_name = $this->tagesname($datum_a);
                if ($this->feiertag($datum_a) == 'Arbeitstag') {
                    $anteil = $this->anteil_datum($datum_a);
                    $this->urlaubstag_speichern($benutzer_id, $datum_a, $anteil, $art);
                    echo "$tag_name $datum_a wurde als Urlaubstag eingegeben<br>";
                } else {
                    echo "<b>$tag_name $datum_a ist  " . $this->feiertag($datum_a) . '</b><br>';
                }

                unset ($anteil);
                $zeile++;
            }
        }
    }

    function feiertag($datum)
    {
        $datum = explode("-", $datum);

        $datum [1] = str_pad($datum [1], 2, "0", STR_PAD_LEFT);
        $datum [2] = str_pad($datum [2], 2, "0", STR_PAD_LEFT);

        if (!checkdate($datum [1], $datum [2], $datum [0]))
            return false;

        $datum_arr = getdate(mktime(0, 0, 0, $datum [1], $datum [2], $datum [0]));

        $easter_d = date("d", strtotime("$datum[0]-03-21 +" . easter_days($datum[0]) . " days"));
        $easter_m = date("m", strtotime("$datum[0]-03-21 +" . easter_days($datum[0]) . " days"));

        $status = 'Arbeitstag';
        if ($datum_arr ['wday'] == 0 || $datum_arr ['wday'] == 6)
            $status = 'Wochenende';

        if ($datum [1] . $datum [2] == '0101') {
            return 'Neujahr';
        } elseif ($datum [1] . $datum [2] == date("md", mktime(0, 0, 0, $easter_m, $easter_d - 2, $datum [0]))) {
            return 'Karfreitag';
        } elseif ($datum [1] . $datum [2] == date("md", mktime(0, 0, 0, $easter_m, $easter_d + 1, $datum [0]))) {
            return 'Ostermontag';
        } elseif ($datum [1] . $datum [2] == '0501') {
            return 'Erster Mai';
        } elseif ($datum [1] . $datum [2] == date("md", mktime(0, 0, 0, $easter_m, $easter_d + 39, $datum [0]))) {
            return 'Christi Himmelfahrt';
        } elseif ($datum [1] . $datum [2] == date("md", mktime(0, 0, 0, $easter_m, $easter_d + 50, $datum [0]))) {
            return 'Pfingstmontag';
        } elseif ($datum [1] . $datum [2] == '1003') {
            return 'Tag der deutschen Einheit';
        } elseif ($datum [1] . $datum [2] == '1225') {
            return '1. Weihnachtstag';
        } elseif ($datum [1] . $datum [2] == '1226') {
            return '2. Weihnachtstag';
        } else {
            return $status;
        }
    }

    function anteil_datum($datum)
    {
        $datum_arr = explode("-", $datum);
        $monat = $datum_arr [1];
        $tag = $datum_arr [2];
        $result = DB::select("SELECT ANTEIL FROM URLAUB_EINST WHERE DATE_FORMAT(DATUM, '%m-%d') ='$monat-$tag' LIMIT 0,1 ");
        if (!empty($result)) {
            $row = $result[0];
            return $row ['ANTEIL'];
        } else {
            return '1.0';
        }
    }

    function urlaubstag_speichern($benutzer_id, $datum, $anteil, $art = 'Urlaub')
    {
        $datum_heute = date("Y-m-d");

        if ($this->urlaubstag_eingetragen($datum, $benutzer_id)) {
            $d_a = date_mysql2german($datum);
            echo "$d_a wurde schon als Urlaubstag eingetragen<br>";
        } else {
            $db_abfrage = "INSERT INTO URLAUB VALUES (NULL, '$benutzer_id','$datum_heute','$datum', '$anteil', '1', '$art')";
            DB::insert($db_abfrage);
        }
    }

    function urlaubstag_eingetragen($datum, $benutzer_id)
    {
        $result = DB::select("SELECT * FROM URLAUB WHERE DATUM='$datum' && BENUTZER_ID='$benutzer_id' && AKTUELL='1'");
        return !empty($result);
    }

    function tag_danach($datum)
    {
        $datum_arr = explode("-", $datum);
        $jahr = $datum_arr [0];
        $monat = $datum_arr [1];
        $tag = $datum_arr [2];
        $morgen = date('Y-m-d', mktime(0, 0, 0, $monat, $tag + 1, $jahr));
        return $morgen;
    }

    function urlaubstag_loeschen($dat)
    {
        $db_abfrage = "UPDATE URLAUB SET AKTUELL='0' WHERE U_DAT='$dat'";
        DB::update($db_abfrage);
        echo "gelöscht";
    }

    function urlaubstag_loeschen_datum($benutzer_id, $datum)
    {
        $db_abfrage = "UPDATE URLAUB SET AKTUELL='0' WHERE DATUM='$datum' && BENUTZER_ID='$benutzer_id'";
        DB::update($db_abfrage);
        echo "gelöscht";
    }

    function monatsansicht($monat, $jahr)
    {
        $mitarbeiter_arr = $this->mitarbeiter_arr($jahr);
        if ($mitarbeiter_arr->isEmpty()) {
            echo "Keine Mitarbeiter vorhanden, Eintrittsdaten erst nach $jahr";
        } else {
            $datum = "$jahr-$monat-01";
            $anzahl_t = $this->anzahl_tage_monat($datum);

            if ($monat > 1 && $monat < 12) {
                $vormonat = $monat - 1;
                $vormonatname = monat2name($vormonat);
                $nachmonat = $monat + 1;
                $nachmonatname = monat2name($nachmonat);
                $v_jahr = $jahr;
                $n_jahr = $jahr;
            }
            if ($monat == 1) {
                $vormonat = 12;
                $vormonatname = monat2name($vormonat);
                $nachmonat = $monat + 1;
                $nachmonatname = monat2name($nachmonat);
                $v_jahr = $jahr - 1;
                $n_jahr = $jahr;
            }
            if ($monat == 12) {
                $vormonat = $monat - 1;
                $vormonatname = monat2name($vormonat);
                $nachmonat = 1;
                $nachmonatname = monat2name($nachmonat);
                $v_jahr = $jahr;
                $n_jahr = $jahr + 1;
            }

            $monatsname = monat2name($monat);
            $link_vormonat = "<a class='waves-effect waves-light btn' href='" . route('web::urlaub::legacy', ['option' => 'monatsansicht', 'jahr' => $v_jahr, 'monat' => $vormonat]) . "'><i class=\"mdi mdi-arrow-left left\"></i>$vormonatname</a>";
            $link_nachmonat = "<a class='waves-effect waves-light btn' href='" . route('web::urlaub::legacy', ['option' => 'monatsansicht', 'jahr' => $n_jahr, 'monat' => $nachmonat]) . "'><i class=\"mdi mdi-arrow-right right\"></i>$nachmonatname</a>";
            $link_pdf = "<a class='waves-effect waves-light btn' href='" . route('web::urlaub::legacy', ['option' => 'monatsansicht_pdf', 'jahr' => $n_jahr, 'monat' => $monat]) . "'>PDF</a>";

            /* Ausgabe der Tage */

            echo "<div class='left-align'>$link_vormonat &nbsp;<b>$monatsname $jahr</b>&nbsp; $link_nachmonat $link_pdf</div>";

            echo "<table class=\"sortable striped\">";
            echo "<thead>";
            echo "<tr class=\"rot\">";
            echo "<th class=\"rot\">MITARBEITER</th><th>REST</th>";
            for ($a = 1; $a <= $anzahl_t; $a++) {
                echo "<th>$a</th>";
            }
            echo "</tr></thead>";

            $zaehler = 0;

            foreach ($mitarbeiter_arr as $user) {
                $zaehler++;
                $mitarbeiter = $user->name;
                $benutzer_id = $user->id;

                $this->jahresuebersicht_mitarbeiter_kurz($benutzer_id, $jahr);

                if ($zaehler == 1) {
                    echo "<tr class=\"zeile1\"><td>$mitarbeiter</td><td><b>$this->rest_jahr</b></td>";
                }
                if ($zaehler == 2) {
                    echo "<tr class=\"zeile2\"><td>$mitarbeiter</td><td><b>$this->rest_jahr</b></td>";
                    $zaehler = 0;
                }
                for ($a = 1; $a <= $anzahl_t; $a++) {
                    if ($a < 10) {
                        $tag = '0' . $a;
                    } else {
                        $tag = $a;
                    }
                    $datum = "$jahr-$monat-$tag";
                    $status = $this->feiertag($datum);
                    if ($status == 'Wochenende') {
                        $zeichen = "W";
                    }
                    if ($status != 'Wochenende' & $status != 'Arbeitstag') {
                        $zeichen = "F";
                    }
                    if ($status == 'Arbeitstag') {
                        $zeichen = "";
                    }
                    $status = $this->check_anwesenheit($benutzer_id, $datum);
                    if ($status != '') {
                        $zeichen = $status;
                    }

                    $feld_id = $datum . $benutzer_id;
                    $datum_j = date_mysql2german($datum);
                    $zeichen_k = substr($zeichen, 0, 2);
                    if ($zeichen != '') {
                        echo "<td id=\"$feld_id\" class=\"$zeichen\" onclick=\"urlaub_del_button('$feld_id', '$benutzer_id', '$datum_j')\"><b>$zeichen_k</b></td>";
                    } else {

                        echo "<td id=\"$feld_id\" class=\"gruen\" onclick=\"urlaub_buttons('$feld_id', '$benutzer_id', '$datum_j')\">";
                        echo "</td>";
                    }
                }
                echo "</tr>";
                $zeichen = '';
            }
            echo "</TABLE>";
        }
    }

    function anzahl_tage_monat($datum)
    {
        $datum_arr = explode("-", $datum);
        $jahr = $datum_arr [0];
        $monat = $datum_arr [1];

        $tage = date("t", mktime(0, 0, 0, $monat, 1, $jahr));

        return $tage;
    }

    function check_anwesenheit($benutzer_id, $datum)
    {
        $result = DB::select("SELECT * FROM URLAUB WHERE AKTUELL='1' && BENUTZER_ID='$benutzer_id' && DATUM='$datum'");
        if (!empty($result)) {
            $row = $result[0];
            return $row ['ART'];
        }
        return '';
    }

    function monatsansicht_pdf($monat, $jahr)
    {
        ob_end_clean(); // ausgabepuffer leeren
        $pdf = new Cezpdf ('a4', 'portrait');
        $bpdf = new b_pdf ();
        $bpdf->b_header($pdf, 'Partner', session()->get('partner_id'), 'portrait', 'Helvetica.afm', 6);
        $pdf->addText(43, 710, 6, "U -> Urlaub");
        $pdf->addText(43, 704, 6, "W -> Wochenende");
        $pdf->addText(43, 698, 6, "F -> Feiertag");
        $pdf->addText(43, 692, 6, "*G -> Geburtstag");
        /* Tage */
        $monat = sprintf('%02d', $monat);
        $monatsname = monat2name($monat);
        $datum = "$jahr-$monat-01";
        $anzahl_t = $this->anzahl_tage_monat($datum);
        $cols = array(
            'MITARBEITER' => "Mitarbeiter"
        );

        $mitarbeiter_arr = $this->mitarbeiter_arr($jahr);

        foreach ($mitarbeiter_arr as $user) {
            $mitarbeiter = $user->name;
            $benutzer_id = $user->id;

            for ($b = 1; $b <= $anzahl_t; $b++) {
                $tag = sprintf('%02d', $b);
                $cols ["$tag"] = "$b";

                $datum_a = "$jahr-$monat-$tag";

                $status = $this->feiertag($datum_a);
                if ($status == 'Wochenende') {
                    $zeichen = "W";
                }
                if ($status != 'Wochenende' && $status != 'Arbeitstag') {
                    $zeichen = "F";
                }
                if ($status == 'Arbeitstag') {
                    $zeichen = "";
                }

                $geburtstag = $this->check_geburtstag($benutzer_id, $datum_a);
                if ($geburtstag) {
                    $zeichen .= "*G";
                }

                $status = $this->check_anwesenheit($benutzer_id, $datum_a);
                if ($status != '') {
                    $zeichen = $status;
                }

                $zeichen_k = substr($zeichen, 0, 2);
                // echo "<td class=\"$zeichen\"><b>$zeichen_k</b></td>";

                $table_arr [$c] ['MITARBEITER'] = "$mitarbeiter";
                $table_arr [$c] ["$tag"] = "$zeichen_k";
                $zeichen = '';
                unset ($geburtstag);
            } // end for 2
        } // end for 1

        $pdf->ezTable($table_arr, $cols, "Monatsansicht $monatsname $jahr", array(
            'showHeadings' => 1,
            'showLines' => '1',
            'shaded' => 1,
            'shadeCol' => array(
                0.78,
                0.95,
                1
            ),
            'shadeCol2' => array(
                0.1,
                0.5,
                1
            ),
            'titleFontSize' => 10,
            'fontSize' => 5,
            'xPos' => 50,
            'xOrientation' => 'right',
            'width' => 500,
            'cols' => array(
                'MITARBEITER' => array(
                    'justification' => 'right',
                    'width' => 35
                )
            )
        ));
        $pdf->ezStream();
    }

    function check_geburtstag($benutzer_id, $datum)
    {
        $datum_arr = explode("-", $datum);
        $monat = $datum_arr [1];
        $tag = $datum_arr [2];
        
        $user = \App\Models\User::where('id', $benutzer_id)->whereDay('birthday', '=', $tag)->whereMonth('birthday', '=', $monat)->get();

        return !$user->isEmpty();
    }

    function monatsansicht_pdf_mehrere($monat_a, $monat_e, $jahr)
    {
        set_time_limit(240);
        $pdf = new Cezpdf ('a4', 'portrait');
        $bpdf = new b_pdf ();
        $bpdf->b_header($pdf, 'Partner', session()->get('partner_id'), 'portrait', 'Helvetica.afm', 6);

        $pdf->addText(43, 710, 6, "U -> Urlaub");
        $pdf->addText(43, 704, 6, "W -> Wochenende");
        $pdf->addText(43, 698, 6, "F -> Feiertag");
        $pdf->addText(43, 692, 6, "*G -> Geburtstag");

        for ($f = $monat_a; $f <= $monat_e; $f++) {
            $monat = $f;
            $monat = sprintf('%02d', $monat);
            /* Tage */
            $monatsname = monat2name($monat);
            $datum = "$jahr-$monat-01";
            $anzahl_t = $this->anzahl_tage_monat($datum);
            $cols = array(
                'MITARBEITER' => "Mitarbeiter"
            );

            $mitarbeiter_arr = $this->mitarbeiter_arr($jahr);
            $table_arr = [];

            foreach ($mitarbeiter_arr as $user) {
                $mitarbeiter = $user->name;
                $benutzer_id = $user->id;

                $row = [];
                $row ['MITARBEITER'] = "$mitarbeiter";

                for ($b = 1; $b <= $anzahl_t; $b++) {
                    $tag = sprintf('%02d', $b);
                    $cols ["$tag"] = "$b";

                    $datum_a = "$jahr-$monat-$b";

                    $zeichen = '';

                    $status = $this->feiertag($datum_a);
                    if ($status == 'Wochenende') {
                        $zeichen = "W";
                    }
                    if ($status != 'Wochenende' && $status != 'Arbeitstag') {
                        $zeichen = "F";
                    }
                    if ($status == 'Arbeitstag') {
                        $zeichen = "";
                    }
                    $status = $this->check_anwesenheit($benutzer_id, $datum_a);
                    if ($status != '') {
                        $zeichen = substr($status, 0, 1);
                    }

                    $geburtstag = $this->check_geburtstag($benutzer_id, $datum_a);
                    if ($geburtstag) {
                        $zeichen .= "<b>*G</b>";
                    }

                    $row ["$tag"] = "$zeichen";
                } // end for 3
                $table_arr [] = $row;
            } // end for 2
            $pdf->ezTable($table_arr, $cols, "Monatsansicht $monatsname $jahr", array(
                'showHeadings' => 1,
                'showLines' => '1',
                'shaded' => 1,
                'shadeCol' => array(
                    0.78,
                    0.95,
                    1
                ),
                'shadeCol2' => array(
                    0.1,
                    0.5,
                    1
                ),
                'titleFontSize' => 10,
                'fontSize' => 5,
                'xPos' => 50,
                'xOrientation' => 'right',
                'width' => 500,
                'cols' => array(
                    'MITARBEITER' => array(
                        'justification' => 'right',
                        'width' => 35
                    )
                )
            ));
        } // end for 1

        $pdf->ezStream();
    }
} // end class urlaub
