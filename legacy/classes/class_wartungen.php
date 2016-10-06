<?php

class wartung
{
    function durchgefuehrte_wartungen($monat, $jahr, $plan_id)
    {
        $sql = "SELECT WARTUNGSPLAN.`PLAN_ID`, IM_EINSATZ, `PLAN_BEZEICHNUNG`, `INTERVALL_NAME`, `FAKTOR`, W_GERAETE.GERAETE_ID, HERSTELLER, W_GERAETE.BEZEICHNUNG FROM `WARTUNGSPLAN` JOIN (WARTUNG_ZUWEISUNG, W_GERAETE) ON (WARTUNG_ZUWEISUNG.PLAN_ID=WARTUNGSPLAN.PLAN_ID && WARTUNG_ZUWEISUNG.GERAETE_ID=W_GERAETE.GERAETE_ID) WHERE DATE_FORMAT(IM_EINSATZ, '%m') = '01' && W_GERAETE.GERAETE_ID IN (SELECT GERAETE_ID FROM WARTUNGEN WHERE DATE_FORMAT(WARTUNGSDATUM, '%Y') = '2010')";
    }

    /* Liefer ein Array mit Geräten die im aktuellen und vorjahr nicht gewartet worden sind */
    function alle_geraete_ng_arr($plan_id)
    {
        $result = DB::select("select W_GERAETE.GERAETE_ID, W_GERAETE.BAUJAHR, W_GERAETE.BEZEICHNUNG, W_GERAETE.HERSTELLER, DATE_FORMAT(W_GERAETE.IM_EINSATZ,'%d.%m.%Y') IM_EINSATZ, LAGE_TYP, LAGE_ID, WARTUNG_ZUWEISUNG.PLAN_ID
from W_GERAETE
LEFT OUTER JOIN WARTUNG_ZUWEISUNG on (W_GERAETE.GERAETE_ID = WARTUNG_ZUWEISUNG.GERAETE_ID)

WHERE W_GERAETE.AKTUELL='1' && WARTUNG_ZUWEISUNG.AKTUELL='1'  && WARTUNG_ZUWEISUNG.PLAN_ID='$plan_id' && W_GERAETE.GERAETE_ID NOT IN
(
SELECT GERAETE_ID FROM WARTUNGEN WHERE DATE_FORMAT(WARTUNGEN.WARTUNGSDATUM, '%Y') = YEAR(NOW()) OR DATE_FORMAT(WARTUNGEN.WARTUNGSDATUM, '%Y') = YEAR(NOW())-1 && WARTUNGEN.PLAN_ID='$plan_id' && WARTUNGEN.AKTUELL='1'
)
ORDER BY LAGE_TYP ASC, LAGE_ID ASC , W_GERAETE.IM_EINSATZ ASC");

        if (!empty($result)) {
            return $result;
        } else {
            return false;
        }
    }

    function alle_g_array($plan_id)
    {
        $this->get_wplan_info($plan_id);
        $result = DB::select(" SELECT W_GERAETE.GERAETE_ID, W_GERAETE.BAUJAHR, W_GERAETE.BEZEICHNUNG, W_GERAETE.HERSTELLER, DATE_FORMAT( W_GERAETE.IM_EINSATZ, '%d.%m.%Y' ) AS IM_EINSATZ, DATE_FORMAT( WARTUNGSDATUM, '%d.%m.%Y' ) AS L_WARTUNG, DATE_FORMAT( DATE_ADD( WARTUNGSDATUM, INTERVAL $this->intervall $this->intervall_period ) , '%d.%m.%Y' ) AS N_WARTUNG, KOSTENTRAEGER_TYP, KOSTENTRAEGER_ID, WARTUNG_ZUWEISUNG.PLAN_ID
FROM W_GERAETE
JOIN WARTUNG_ZUWEISUNG ON ( W_GERAETE.GERAETE_ID = WARTUNG_ZUWEISUNG.GERAETE_ID )
JOIN WARTUNGEN ON ( W_GERAETE.GERAETE_ID = WARTUNGEN.GERAETE_ID )
WHERE W_GERAETE.AKTUELL = '1' && WARTUNG_ZUWEISUNG.AKTUELL = '1' && WARTUNG_ZUWEISUNG.PLAN_ID = '1'
ORDER BY KOSTENTRAEGER_TYP ASC , KOSTENTRAEGER_ID ASC , W_GERAETE.IM_EINSATZ ASC");
        if (!empty($result)) {
            return $result;
        } else {
            return false;
        }
    }

    function get_wplan_info($plan_id)
    {
        $result = DB::select("SELECT * FROM `WARTUNGSPLAN` WHERE `PLAN_ID` ='$plan_id'   AND `AKTUELL` = '1' ORDER BY DAT DESC LIMIT 0,1");
        if (!empty($result)) {
            $row = $result[0];
            $this->plan_id = $plan_id;
            $this->plan_bez = $row ['PLAN_BEZEICHNUNG'];
            $this->intervall = $row ['INTERVALL'];
            $this->intervall_period = $row ['INTERVALL_PERIOD'];
            $this->gewerk_id = $row ['GEWERK_ID'];
        }
    }

    function wplan_geraet($plan_id, $geraete_id)
    {
        $this->get_wplan_info($plan_id);
        $this->letzte_wartung_infos($plan_id, $geraete_id);
    }

    function letzte_wartung_infos($plan_id, $geraet_id)
    {
        $this->get_wplan_info($plan_id);
        $result = DB::select("SELECT *, DATE_ADD( WARTUNGSDATUM, INTERVAL $this->intervall $this->intervall_period )  AS N_WARTUNG FROM `WARTUNGEN` WHERE `GERAETE_ID` ='$geraet_id'  AND `PLAN_ID` ='$plan_id' AND `AKTUELL` = '1' ORDER BY WARTUNGSDATUM DESC LIMIT 0,1");

        if (!empty($result)) {
            $row = $result[0];
            $this->geraet_id = $geraet_id;
            $this->plan_id = $plan_id;
            $this->wartungsdatum = $row ['WARTUNGSDATUM'];
            $this->n_wartung = $row ['N_WARTUNG'];
            $this->benutzer_id = $row ['BENUTZER_ID'];
            $this->bemerkung = $row ['BEMERKUNG'];
            $u = new urlaub ();
            $u->mitarbeiter_details($this->benutzer_id);
            $this->gewartet_von = $u->benutzername;
        } else {

            $this->n_wartung = date("Y-m-d");
        }
    }

    function list_plan($plan_id)
    {
        $f = new formular ();
        $f->erstelle_formular("Terminliste Wartungen", NULL);
        $geraete_arr = $this->alle_geraete_in_arr($plan_id);
        $anz = count($geraete_arr);
        // print_r($geraete_arr);
        if ($anz > 0) {
            $this->get_wplan_info($plan_id);
            // $this->intervall; int
            // $this->intervall_period /day month year
            for ($a = 0; $a < $anz; $a++) {
                $geraet_id = $geraete_arr [$a] ['GERAETE_ID'];
                $this->letzte_wartung_infos($plan_id, $geraet_id);
                $kos_typ = $geraete_arr [$a] ['KOSTENTRAEGER_TYP'];
                $kos_id = $geraete_arr [$a] ['KOSTENTRAEGER_ID'];
                $r = new rechnung ();
                $kos_bez = $r->kostentraeger_ermitteln($kos_typ, $kos_id);
                $ger_bez = $geraete_arr [$a] ['BEZEICHNUNG'];

                $this->termin_check_geraet($plan_id, $geraet_id);
                if ($this->termin_g) {
                    $wartungstermine [$a] ['GERAETE_ID'] = $geraet_id;
                    $wartungstermine [$a] ['GER_BEZ'] = $ger_bez;
                    $wartungstermine [$a] ['KOS_BEZ'] = $kos_bez;
                    $wartungstermine [$a] ['L_WARTUNG'] = $this->wartungsdatum;
                    $wartungstermine [$a] ['F_WARTUNG'] = $this->n_wartung;
                    $wartungstermine [$a] ['TERMIN'] = $this->termin_g;
                    $wartungstermine [$a] ['MITARBEITER'] = $this->termin_von;
                } else {
                    $wartungsplan [$a] ['GERAETE_ID'] = $geraet_id;
                    $wartungsplan [$a] ['GER_BEZ'] = $ger_bez;
                    $wartungsplan [$a] ['KOS_BEZ'] = $kos_bez;
                    $wartungsplan [$a] ['L_WARTUNG'] = $this->wartungsdatum;
                    $wartungsplan [$a] ['F_WARTUNG'] = $this->n_wartung;
                }
            }

            $termine = array_sortByIndex($wartungstermine, 'TERMIN', SORT_ASC);
            $anzahl_termine = count($termine);
            echo "<table>";
            echo "<tr class=\"feldernamen\"><td>$this->plan_bez TERMINE ($anzahl_termine)</td></tr>";
            echo "</table>";
            if ($anzahl_termine > 0) {

                echo "<table class=\"sortable\">";
                echo "<tr><th>Gerät</th><th>Lage</th><th>Letzte Wartung</th><th>Wartung fällig</th><th>TERMIN</th><th>Mietarbeiter</th></tr>";
                for ($a = 0; $a < $anzahl_termine; $a++) {
                    $geraete_id = $termine [$a] ['GERAETE_ID'];
                    $kos_bez = $termine [$a] ['KOS_BEZ'];
                    $l_wartung = date_mysql2german($termine [$a] ['L_WARTUNG']);
                    $f_wartung = date_mysql2german($termine [$a] ['F_WARTUNG']);
                    $termin = $termine [$a] ['TERMIN'];
                    $mitarbeiter = $termine [$a] ['MITARBEITER'];
                    $ger_bez = $termine [$a] ['GER_BEZ'];
                    echo "<tr><td>$ger_bez</td><td>$kos_bez</td><td>$l_wartung</td><td>$f_wartung</td><td>$termin</td><td>$mitarbeiter</td></tr>";
                }
                echo "</table>";
            } else {
                echo "<table>";
                echo "<tr><th>Keine Termine</th></tr>";
                echo "</table>";
            }

            unset ($termine);

            $wartungsplan_s = array_sortByIndex($wartungsplan, 'F_WARTUNG', SORT_ASC);
            echo "<br><br>";
            // print_r($wartungsplan_s);

            $anzahl_geraete = count($wartungsplan_s);
            echo "<table >";
            echo "<tr class=\"feldernamen\"><td>$this->plan_bez GERÄTELISTE ($anzahl_geraete)</td></tr>";
            echo "</table>";
            if ($anzahl_geraete > 0) {

                echo "<table class=\"sortable\">";
                echo "<tr><th>Gerät</th><th>Lage</th><th>Letzte Wartung</th><th>Wartung fällig</th><th>TERMIN</th><th>Mietarbeiter</th></tr>";
                for ($a = 0; $a < $anzahl_geraete; $a++) {
                    $geraete_id = $wartungsplan_s [$a] ['GERAETE_ID'];
                    $kos_bez = $wartungsplan_s [$a] ['KOS_BEZ'];
                    $l_wartung = date_mysql2german($wartungsplan_s [$a] ['L_WARTUNG']);
                    $f_wartung = date_mysql2german($wartungsplan_s [$a] ['F_WARTUNG']);
                    $ger_bez = $wartungsplan_s [$a] ['GER_BEZ'];
                    $link_termin_neu = "<a href=\"?daten=wartung&option=termin_neu&plan_id=$plan_id&geraete_id=$geraete_id\">Termin vereinbaren</a>";
                    echo "<tr><td>$ger_bez</td><td>$kos_bez</td><td>$l_wartung</td><td>$f_wartung</td><td>nicht vereinbart</td><td>$link_termin_neu</td></tr>";
                }
                echo "</table>";
            } else {
                echo "<table>";
                echo "<tr><th>Keine Geräte</th></tr>";
                echo "</table>";
            }

            $f->ende_formular();
        } else {
            echo "Keine Geräte";
        }
    }

    function alle_geraete_in_arr($plan_id)
    {
        $result = DB::select(" SELECT W_GERAETE.GERAETE_ID, W_GERAETE.BAUJAHR, W_GERAETE.BEZEICHNUNG, W_GERAETE.HERSTELLER, DATE_FORMAT( W_GERAETE.IM_EINSATZ, '%d.%m.%Y' ) AS IM_EINSATZ, KOSTENTRAEGER_TYP, KOSTENTRAEGER_ID, WARTUNG_ZUWEISUNG.PLAN_ID
FROM W_GERAETE
JOIN WARTUNG_ZUWEISUNG ON ( W_GERAETE.GERAETE_ID = WARTUNG_ZUWEISUNG.GERAETE_ID )
WHERE W_GERAETE.AKTUELL = '1' && WARTUNG_ZUWEISUNG.AKTUELL = '1' && WARTUNG_ZUWEISUNG.PLAN_ID = '$plan_id'
ORDER BY KOSTENTRAEGER_TYP, KOSTENTRAEGER_ID, `W_GERAETE`.`GERAETE_ID` ASC");
        if (!empty($result)) {
            return $result;
        }
    }

    function termin_check_geraet($plan_id, $geraete_id)
    {
        $this->termin_g = '';
        $this->termin_von = '';

        $result = DB::select("SELECT DATE_FORMAT(TERMIN, '%d.%m.%Y') AS DATUM, DATE_FORMAT(TERMIN, '%H:%i') AS ZEIT, BENUTZER_ID FROM `W_TERMINE` WHERE GERAETE_ID='$geraete_id' && DATE_FORMAT( TERMIN, '%Y' ) = YEAR( NOW( ) )  && PLAN_ID='$plan_id'  && ABGESAGT='0'  && TERMIN >NOW()  ORDER BY TERMIN DESC LIMIT 0,1");
        if (!empty($result)) {
            $this->termin_g = '';
            $this->termin_von = '';
            $row = $result[0];
            $this->datum_g = $row ['DATUM'];
            $zeit = $row ['ZEIT'];
            $benutzer_id = $row ['BENUTZER_ID'];
            $this->termin_g = "$this->datum_g $zeit";
            $u = new urlaub ();
            $u->mitarbeiter_details($benutzer_id);
            $this->termin_von = $u->benutzername;
        }
    }

    /*
     * SELECT *, DATE_ADD( WARTUNGSDATUM, INTERVAL 1 YEAR ) AS N_WARTUNG, DATE_ADD( WARTUNGSDATUM, INTERVAL 2*1 YEAR ) AS N_WARTUNG1, DATE_ADD( WARTUNGSDATUM, INTERVAL 3*1 YEAR ) AS N_WARTUNG2 FROM `WARTUNGEN` WHERE GERAETE_ID ='1' AND PLAN_ID ='1' AND AKTUELL = '1' ORDER BY WARTUNGSDATUM DESC LIMIT 0,1
     *
     *
     * SELECT *, DATE_ADD( WARTUNGSDATUM, INTERVAL 1 YEAR ) AS N_WARTUNG1, DATE_ADD( DATE_ADD( WARTUNGSDATUM, INTERVAL 1 YEAR ), INTERVAL 1 YEAR ) AS N_WARTUNG2, DATE_ADD( DATE_ADD( DATE_ADD( WARTUNGSDATUM, INTERVAL 1 YEAR ), INTERVAL 1 YEAR ), INTERVAL 1 YEAR ) AS N_WARTUNG3 FROM `WARTUNGEN` WHERE PLAN_ID ='4' AND AKTUELL = '1' ORDER BY WARTUNGSDATUM DESC
     *
     * SELECT * , DATE_ADD( WARTUNGSDATUM, INTERVAL 1 YEAR ) AS N_WARTUNG1, DATE_ADD( DATE_ADD( WARTUNGSDATUM, INTERVAL 1 YEAR ) , INTERVAL 1 YEAR ) AS N_WARTUNG2, DATE_ADD( DATE_ADD( DATE_ADD( WARTUNGSDATUM, INTERVAL 1 YEAR ) , INTERVAL 1 YEAR ) , INTERVAL 1 YEAR ) AS N_WARTUNG3
     * FROM `WARTUNGEN`
     * WHERE PLAN_ID = '1'
     * AND AKTUELL = '1'
     * ORDER BY `N_WARTUNG1` ASC
     * LIMIT 0 , 30
     */

    function wartungen_anstehend($plan_id)
    {
        $wartungen = $this->wartungen_ue_arr($plan_id);
        // print_req($wartungen);
        // print_req();
        $anz = count($wartungen);
        if ($anz > 0) {
            echo "<table>";
            echo "<tr class=\"feldernamen\"><td>$this->plan_bez</td></tr>";
            echo "</table>";
            echo "<table class=\"sortable\">";
            echo "<tr><th>AUFGABE</th><th>Letzte WARTUNG</th><th>NT1</th><th>NT2</th><th>NT3</th><th>Mitarbeiter</th><th>Bemerkung</th>";
            for ($a = 0; $a < $anz; $a++) {
                $lw = date_mysql2german($wartungen [$a] ['WARTUNGSDATUM']);
                $nw1 = date_mysql2german($wartungen [$a] ['N_WARTUNG1']);
                $nw2 = date_mysql2german($wartungen [$a] ['N_WARTUNG2']);
                $nw3 = date_mysql2german($wartungen [$a] ['N_WARTUNG3']);
                $this->geraete_infos($plan_id, $geraete_id);
                // $this->gewartet_von;
                echo "<tr><td>$this->kostentraeger_bez<br>$this->bezeichnung</td><td>$lw</td><td>$nw1</td><td>$nw2</td><td>$nw3</td><td>$this->gewartet_von</td><td>$this->bemerkung</td>";
            }

            echo "</table";
        } else {
            echo "Keine Wartungen oder Geräte im Wartungsplan";
        }
    }

    function wartungen_ue_arr($plan_id)
    {
        $this->get_wplan_info($plan_id);

        $result = DB::select("SELECT MAX(WARTUNGSDATUM) AS WARTUNGSDATUM, DATE_ADD( MAX(WARTUNGSDATUM), INTERVAL $this->intervall $this->intervall_period ) AS N_WARTUNG1, DATE_ADD( DATE_ADD( MAX(WARTUNGSDATUM), INTERVAL $this->intervall $this->intervall_period ) , INTERVAL $this->intervall $this->intervall_period ) AS N_WARTUNG2, DATE_ADD( DATE_ADD( DATE_ADD( MAX(WARTUNGSDATUM), INTERVAL $this->intervall $this->intervall_period ) , INTERVAL $this->intervall $this->intervall_period ) , INTERVAL $this->intervall $this->intervall_period ) AS N_WARTUNG3
FROM `WARTUNGEN`
WHERE PLAN_ID = '$plan_id'
AND AKTUELL = '1' GROUP BY GERAETE_ID
ORDER BY `N_WARTUNG1` ASC");

        if (!empty($result)) {
            return $result;
        }
    }

    function geraete_infos($plan_id, $geraet_id)
    {
        unset ($this->geraet_id);
        unset ($this->baujahr);
        unset ($this->bezeichnung);
        unset ($this->hersteller);
        unset ($this->im_einsatz);
        unset ($this->kostentraeger_typ);
        unset ($this->kostentraeger_id);
        unset ($this->kostentraeger_bez);
        unset ($this->wartungsdatum);
        unset ($this->benutzer_id);
        unset ($this->bemerkung);
        unset ($this->gewartet_von);

        $result = DB::select("SELECT * FROM `W_GERAETE` WHERE `GERAETE_ID` ='$geraet_id'   AND `AKTUELL` = '1'  LIMIT 0,1");
        if (!empty($result)) {
            $row = $result[0];
            $this->geraet_id = $geraet_id;
            $this->baujahr = $row ['BAUJAHR'];
            $this->bezeichnung = $row ['BEZEICHNUNG'];
            $this->hersteller = $row ['HERSTELLER'];
            $this->im_einsatz = date_mysql2german($row ['IM_EINSATZ']);
            $this->kostentraeger_typ = $row ['KOSTENTRAEGER_TYP'];
            $this->kostentraeger_id = $row ['KOSTENTRAEGER_ID'];
            $r = new rechnung ();
            $this->kostentraeger_bez = $r->kostentraeger_ermitteln($this->kostentraeger_typ, $this->kostentraeger_id);
            $this->letzte_wartung_infos($plan_id, $geraet_id);
        } else {
            return false;
        }
    }

    function wochentag()
    {
        $wt = array(
            "So",
            "Mo",
            "Di",
            "Mi",
            "Do",
            "Fr",
            "Sa"
        );
        $tag = date("w");
        $heute = "$wt[$tag]";
        $this->wochentag = $heute;
    }

    function termine_anzeigen($benutzer_id, $plan_id, $ab, $bis)
    {
        $termine_arr = $this->terminkalender_arr($benutzer_id, $plan_id, $ab, $bis);
        $anz = count($termine_arr);
        if ($anz) {
            $ab_a = date_mysql2german($ab);
            $bis_a = date_mysql2german($bis);
            $z = new zeiterfassung ();
            $benutzer_name = $z->get_benutzer_name($benutzer_id);
            $this->get_wplan_info($plan_id);
            echo "<h3>$this->plan_bez<br>$benutzer_name<br>Terminansicht von $ab_a bis $bis_a</h3>";
            for ($a = 0; $a < $anz; $a++) {
                $termin = $termine_arr [$a] ['TERMIN'];
                $datum = $termine_arr [$a] ['DATUM'];
                $zeit = $termine_arr [$a] ['ZEIT'];
                $dauer = $termine_arr [$a] ['DAUER'];
                $geraete_id = $termine_arr [$a] ['GERAETE_ID'];
                $abgesagt = $termine_arr [$a] ['ABGESAGT'];
                $abgesagt_r = $termine_arr [$a] ['ABGESAGT_RECHTZEITIG'];
                $abgesagt_von = $termine_arr [$a] ['ABGESAGT_VON'];

                if ($abgesagt == 0) {
                    $status = 'aktuell';
                }

                if ($abgesagt == 1) {
                    $status = '<b>abgesagt</b>';
                }

                $this->geraete_infos($plan_id, $geraete_id);
                echo "<table>";
                echo "<tr><td width=\"70\">Datum</td><td>$datum</td></tr>";
                echo "<tr><td width=\"70\">Uhrzeit</td><td>$zeit Uhr</td></tr>";
                echo "<tr><td width=\"70\">Status</td><td>$status $abgesagt_von</td></tr>";
                echo "<tr><td width=\"70\">Kostenträger</td><td>$this->kostentraeger_typ $this->kostentraeger_bez</td></tr>";
                if ($this->kostentraeger_typ == 'Einheit') {
                    $e = new einheit ();
                    $e->get_einheit_info($this->kostentraeger_id);
                    $mv_id = $e->get_mietvertrag_id($this->kostentraeger_id);
                    $mv = new mietvertraege ();
                    $mv->get_mietvertrag_infos_aktuell($mv_id);
                    echo "<tr><td width=\"70\">Anschrift</td><td>$e->haus_strasse $e->haus_nummer $e->einheit_lage</td></tr>";
                    echo "<tr><td width=\"70\">Mieter</td><td>$mv->personen_name_string_u</td></tr>";
                }
                echo "<tr><td width=\"70\"><hr></td><td><hr></td></tr>";
                echo "<tr><td width=\"70\">Gerät</td><td>$this->bezeichnung</td></tr>";
                echo "<tr><td width=\"70\">Hersteller</td><td>$this->hersteller</td></tr>";
                echo "<tr><td width=\"70\">Im Einsatz</td><td>$this->im_einsatz</td></tr>";
                $this->wartungsdatum_a = date_mysql2german($this->wartungsdatum);
                $this->n_wartung_a = date_mysql2german($this->n_wartung);
                echo "<tr><td width=\"70\">Letzte Wartung</td><td>$this->wartungsdatum_a  $this->gewartet_von</td></tr>";
                echo "<tr><td width=\"70\">Wartung fällig</td><td>$this->n_wartung_a</td></tr>";
                echo "</table><br>";
            }
        } else {
            echo "Keine Termine von $ab bis $bis";
        }
    }

    function terminkalender_arr($benutzer_id, $plan_id, $ab, $bis)
    {
        if ($benutzer_id != '') {
            $result = DB::select("SELECT *, DATE_FORMAT(TERMIN,'%d.%m.%Y') AS DATUM, DATE_FORMAT(TERMIN,'%H:%i') AS ZEIT FROM W_TERMINE WHERE PLAN_ID='$plan_id' && BENUTZER_ID='$benutzer_id' && DATE_FORMAT(TERMIN,'%Y-%m-%d') BETWEEN '$ab' AND '$bis' ORDER BY TERMIN ASC");
        } else {
            $result = DB::select("SELECT *, DATE_FORMAT(TERMIN,'%d.%m.%Y') AS DATUM, DATE_FORMAT(TERMIN,'%H:%i') AS ZEIT FROM W_TERMINE WHERE PLAN_ID='$plan_id' && DATE_FORMAT(TERMIN,'%Y-%m-%d') BETWEEN '$ab' AND '$bis' ORDER BY TERMIN ASC");
        }
        if (!empty($result)) {
            return $result;
        }
    }

    function termine_anzeigen_pdf($benutzer_id, $plan_id, $ab, $bis)
    {
        $termine_arr = $this->terminkalender_arr($benutzer_id, $plan_id, $ab, $bis);
        $anz = count($termine_arr);
        if ($anz) {
            ob_clean(); // ausgabepuffer leeren
            $ab_a = date_mysql2german($ab);
            $bis_a = date_mysql2german($bis);
            $z = new zeiterfassung ();

            $this->get_wplan_info($plan_id);

            include_once('classes/class_bpdf.php');
            $pdf = new Cezpdf ('a4', 'portrait');
            $bpdf = new b_pdf ();
            $pdf->setLineStyle(1);
            if ($benutzer_id != '') {
                $this->bp_partner_id = $z->get_partner_id_benutzer($benutzer_id);
                $bpdf->b_header($pdf, 'Partner', $this->bp_partner_id, 'portrait', 'Helvetica.afm', 6);
            } else {
                $bpdf->b_header($pdf, 'Partner', session()->get('partner_id'), 'portrait', 'Helvetica.afm', 6);
            }

            $pdf->ezText("$benutzer_name", 10);
            $pdf->ezText("$this->plan_bez", 10);
            $pdf->ezText("Terminansicht von $ab_a bis $bis_a", 10);
            $pdf->ezSetDy(-15); // abstand
            for ($a = 0; $a < $anz; $a++) {
                $pdf->ezSetDy(-5); // abstand
                $pdf->line(50, $pdf->y, 550, $pdf->y);
                $benutzer_id = $termine_arr [$a] ['BENUTZER_ID'];
                $benutzer_name = $z->get_benutzer_name($benutzer_id);
                $termin = $termine_arr [$a] ['TERMIN'];
                $datum = $termine_arr [$a] ['DATUM'];
                $zeit = $termine_arr [$a] ['ZEIT'];
                $dauer = $termine_arr [$a] ['DAUER'];
                $geraete_id = $termine_arr [$a] ['GERAETE_ID'];
                $abgesagt = $termine_arr [$a] ['ABGESAGT'];
                $abgesagt_r = $termine_arr [$a] ['ABGESAGT_RECHTZEITIG'];
                $abgesagt_von = $termine_arr [$a] ['ABGESAGT_VON'];
                if ($abgesagt == 0) {
                    $status = 'aktuell';
                }

                if ($abgesagt == 1) {
                    $status = 'abgesagt';
                }

                $this->geraete_infos($plan_id, $geraete_id);
                $pdf->ezText("Mitarbeiter:  $benutzer_name", 8);
                $pdf->ezText("Datum:  $datum", 8);
                $pdf->ezText("Uhrzeit: $zeit Uhr", 8);
                $pdf->ezText("Status:  $status $abgesagt_von", 8);
                $pdf->ezSetDy(20); // abstand
                $pdf->rectangle(250, $pdf->y, 10, 10);
                $pdf->addText(263, $pdf->y + 2, 8, 'Abgesagt am ______________ von ___________________________________');
                $pdf->ezSetDy(-15); // abstand
                $pdf->addText(263, $pdf->y + 2, 8, 'Absagegrund:_____________________________________________________');
                $pdf->ezSetDy(-20); // abstand
                $pdf->rectangle(250, $pdf->y, 10, 10);
                $pdf->addText(263, $pdf->y + 2, 8, 'Erledigt');
                $pdf->ezSetDy(-15); // abstand
                $pdf->addText(260, $pdf->y + 2, 8, 'Bemerkungen / Hinweise');
                $pdf->rectangle(250, $pdf->y - 107, 300, 107);
                $pdf->ezSetDy(20); // abstand
                $pdf->ezText("Kostenträger: $this->kostentraeger_typ $this->kostentraeger_bez", 8);

                if ($this->kostentraeger_typ == 'Einheit') {
                    $e = new einheit ();
                    $e->get_einheit_info($this->kostentraeger_id);
                    $mv_id = $e->get_mietvertrag_id($this->kostentraeger_id);
                    $mv = new mietvertraege ();
                    $mv->get_mietvertrag_infos_aktuell($mv_id);
                    $e = new einheit ();
                    $e->get_einheit_info($this->kostentraeger_id);
                    $mv_id = $e->get_mietvertrag_id($this->kostentraeger_id);
                    $mv = new mietvertraege ();
                    $mv->get_mietvertrag_infos_aktuell($mv_id);
                    $pdf->ezText("Anschrift: $e->haus_strasse $e->haus_nummer Lage: $e->einheit_lage", 8);
                    $pdf->ezText("Mieter: $mv->personen_name_string", 8);
                }
                $pdf->ezText("Gerät: $this->bezeichnung", 8);
                $pdf->ezText("Hersteller: $this->hersteller", 8);
                $pdf->ezText("Im Einsatz: $this->im_einsatz", 8);

                if ($this->wartungsdatum) {
                    $this->wartungsdatum_a = date_mysql2german($this->wartungsdatum);
                } else {
                    $this->wartungsdatum_a = 'k. A.';
                }
                $this->n_wartung_a = date_mysql2german($this->n_wartung);

                $pdf->ezText("Wartung fällig: $this->n_wartung_a", 8);
                $pdf->ezText("Letzte Wartung: $this->wartungsdatum_a  $this->gewartet_von", 8);
                if ($this->gewartet_von) {
                    $pdf->ezText("Bemerkungen von $this->gewartet_von:", 7);
                    $pdf->ezSetCmMargins(0, 0, 1.78, 12.5);
                    $pdf->ezText("<b>$this->bemerkung</b>", 7);
                    $pdf->ezSetMargins(135, 70, 50, 50);
                }

                $pdf->ezSetDy(-40); // abstand

                $pdf->ezSetDy(-10); // abstand

                $pdf->ezSetDy(-5); // abstand

                // $pdf->line(50,$pdf->y,550,$pdf->y);
            }
            ob_clean;
            $pdf->ezStream();
        } else {
            echo "Keine Termine von $ab bis $bis";
        }
    }

    function geraet_speichern($bezeichnung, $hersteller, $baujahr, $eingebaut, $kostentraeger_typ, $kostentraeger_bez, $plan_id)
    {
        $eingebaut = date_german2mysql($eingebaut);
        $b = new buchen ();
        $kostentraeger_id = $b->kostentraeger_id_ermitteln($kostentraeger_typ, $kostentraeger_bez);
        $geraete_id = $this->letzte_geraete_id() + 1;

        $db_abfrage = "INSERT INTO W_GERAETE VALUES (NULL, '$geraete_id', '$bezeichnung', '$hersteller','$baujahr', '$eingebaut', '$kostentraeger_typ', '$kostentraeger_id',  '1')";
        DB::insert($db_abfrage);

        /* Protokollieren */
        $last_dat = DB::getPdo()->lastInsertId();
        protokollieren('W_GERAETE', $last_dat, '0');

        $this->geraet_zu_plan($geraete_id, $plan_id);
        if (request()->has('wartungstermin')) {
            $wartungsdatum = date_german2mysql(request()->get('wartungstermin'));
            $bemerkung = 'Übernahme Excel ' . date("d.m.Y") . " " . Auth::user()->name;
            $this->wartung_speichern($geraete_id, $plan_id, $wartungsdatum, '1', $bemerkung);
        }
    }

    function letzte_geraete_id()
    {
        $result = DB::select("SELECT GERAETE_ID FROM W_GERAETE WHERE AKTUELL='1' ORDER BY GERAETE_ID DESC");

        $row = $result[0];
        return $row ['GERAETE_ID'];
    }

    function geraet_zu_plan($geraete_id, $plan_id)
    {
        $db_abfrage = "INSERT INTO WARTUNG_ZUWEISUNG VALUES (NULL, '$geraete_id', '$plan_id',  '1')";
        DB::insert($db_abfrage);

        /* Protokollieren */
        $last_dat = DB::getPdo()->lastInsertId();
        protokollieren('WARTUNG_ZUWEISUNG', $last_dat, '0');
    }

    function wartung_speichern($geraete_id, $plan_id, $wartungsdatum, $benutzer_id, $bemerkung)
    {
        $datum = date_german2mysql($datum);

        $db_abfrage = "INSERT INTO WARTUNGEN VALUES (NULL, '$geraete_id', '$plan_id', '$wartungsdatum','$benutzer_id', '$bemerkung', '1')";
        DB::insert($db_abfrage);

        /* Protokollieren */
        $last_dat = DB::getPdo()->lastInsertId();
        protokollieren('WARTUNGEN', $last_dat, '0');
    }

    function test($plan_id)
    {

        // $this->form_geraete_erfassen();

        /* Alle geräte die nicht gemacht worden sind */
        /*
         * $this->kalenderwoche();
         * $this->wochentag();
         * $montagsdatum = $this->erster_montag_kw($this->kalenderwoche, 2010);
         * echo "Aktuelle Woche ist KW $this->kalenderwoche $this->wochentag<br>";
         * $geraete_arr = $this->alle_geraete_ng_arr($plan_id);
         * $anzahl = count($geraete_arr);
         *
         * if($anzahl>0){
         * for($a=0;$a<$anzahl;$a++){
         * $geraet_id = $geraete_arr[$a]['GERAETE_ID'];
         * $this->geraete_infos($plan_id, $geraet_id);
         * echo "$this->bezeichnung $this->wartungsdatum $this->gewartet_von $this->kostentraeger_bez<br>";
         * }
         * }else{
         * hinweis_ausgeben("GRATULATION : Alle Geräte sind im aktuellen und Vorjahr gewartet worden");
         * }
         */
        // $this->form_termin(1,1);
        // $this->wochenansicht(4,1,17);
        $this->wochenansicht(5, $plan_id, 1);
        // $this->wochenansicht(4,2,18);
        // $this->wochenansicht(5,1,18);
        // $this->wochenansicht(5,1,18);
        // $this->wochenansicht(5,1,18);
        // $this->wochenansicht(6,1,18);
        // $this->monatsansicht(1, 18, 2010, 01);
    }

    function wochenansicht($kw, $plan_id, $benutzer_id)
    {
        if (empty ($benutzer_id)) {
            echo "ALLE BENUTZER";
        }
        $kw = $this->kalenderwoche();
        $jahr = date("Y");
        $montagsdatum = $this->erster_montag_kw($kw, $jahr);
        echo "Wochenübersicht für $kw $jahr $montagsdatum<br>";
        $uhrzeit_1_wartung = '07:00';
        $dauer_wartung_min = '90';
        $dauer_zwischen_min = '10';
        $wartungen_tag = '5';
        /* Überschriften der Tage mit Datumsangaben */
        echo "<TABLE><tr class=\"feldernamen\">";
        $this->tag_1_datum = $montagsdatum;
        $this->tag_2_datum = $this->tag_danach($this->tag_1_datum);
        $this->tag_3_datum = $this->tag_danach($this->tag_2_datum);
        $this->tag_4_datum = $this->tag_danach($this->tag_3_datum);
        $this->tag_5_datum = $this->tag_danach($this->tag_4_datum);
        $this->tag_6_datum = $this->tag_danach($this->tag_5_datum);
        $this->tag_7_datum = $this->tag_danach($this->tag_6_datum);
        echo "<td>$this->tag_1_datum</td><td>$this->tag_2_datum</td><td>$this->tag_3_datum</td><td>$this->tag_4_datum</td><td>$this->tag_5_datum</td><td>$this->tag_6_datum</td><td>$this->tag_7datum</td></tr>";
        // ###############
        // ###############

        $tag = $montagsdatum;
        /* Alle Geräte die nicht gewartet worden sind */
        $geraete_arr = $this->alle_geraete_ng_arr_o_termin($plan_id);
        $geraete_zaehler = 0;
        // ################
        /* for 1 */
        for ($c = 0; $c < 5; $c++) {
            $wochenplan_arr ['TERMINE'] [$c] [DATUM] = $tag;

            $termine_arr = $this->termine_arr($tag, $plan_id, $benutzer_id);
            if ($termine_arr) {
                $anzahl_termine = count($termine_arr);
                for ($k = 0; $k < $anzahl_termine; $k++) {
                    $zeit = $termine_arr [$k] ['ZEIT'];
                    $t_geraete_id = $termine_arr [$k] ['GERAETE_ID'];
                    $abgesagt = $termine_arr [$k] ['ABGESAGT'];
                    $dauer = $termine_arr [$k] ['DAUER_MIN'];
                    $wochenplan_arr ['TERMINE'] [$c] ['TERMINE'] [$k] ['DAUER'] = $dauer;
                    /* Wenn Termin abgesagt wurde */
                    if ($abgesagt == '1') {
                        $wochenplan_arr ['TERMINE'] [$c] ['TERMINE'] [$k] ['ZEIT'] = $zeit;
                        $wochenplan_arr ['TERMINE'] [$c] ['TERMINE'] [$k] ['GERAETE_ID'] = $t_geraete_id;
                        $wochenplan_arr ['TERMINE'] [$c] ['TERMINE'] [$k] ['ABGESAGT'] = '1';
                        $s_geraete_id = $geraete_arr [$geraete_zaehler] ['GERAETE_ID'];
                        $wochenplan_arr ['TERMINE'] [$c] ['TERMINE'] [$k] ['TEMP_GERAETE_ID'] = $s_geraete_id;
                        $geraete_zaehler++;
                    } else {
                        /* Wenn Termin noch aktuell */

                        $wochenplan_arr ['TERMINE'] [$c] ['TERMINE'] [$k] ['ZEIT'] = $zeit;
                        $wochenplan_arr ['TERMINE'] [$c] ['TERMINE'] [$k] ['GERAETE_ID'] = $t_geraete_id;
                        $wochenplan_arr ['TERMINE'] [$c] ['TERMINE'] [$k] ['ABGESAGT'] = '0';
                        $next_termin = $zeit;
                        $next_termin = $this->naechster_termin($next_termin, $dauer_wartung_min, $dauer_zwischen_min);
                    }
                } // end for
            } else {

                /* Alle Termine frei */
                $next_termin = $uhrzeit_1_wartung;

                for ($b = 0; $b < $wartungen_tag; $b++) {
                    $wochenplan_arr ['TERMINE'] [$c] ['TERMINE'] [$b] ['ZEIT'] = $next_termin;
                    $next_termin = $this->naechster_termin($next_termin, $dauer_wartung_min, $dauer_zwischen_min);
                    $s_geraete_id = $geraete_arr [$geraete_zaehler] ['GERAETE_ID'];
                    if ($s_geraete_id) {
                        $wochenplan_arr ['TERMINE'] [$c] ['TERMINE'] [$b] ['TEMP_GERAETE_ID'] = $s_geraete_id;
                    } else {
                        $wochenplan_arr ['TERMINE'] [$c] ['TERMINE'] [$b] ['TEMP_GERAETE_ID'] = 'FREI';
                    }
                    $wochenplan_arr ['TERMINE'] [$c] ['TERMINE'] [$b] ['DAUER'] = $dauer_wartung_min;
                    $geraete_zaehler++;
                }
            } // end else

            $tag = $this->tag_danach($tag);
        } // end for 1
        // echo '<pre>';
        // print_r($wochenplan_arr);

        $anzahl_tage = count($wochenplan_arr ['TERMINE']);
        echo "<tr>";
        for ($a = 0; $a < $anzahl_tage; $a++) {
            $tag = $a;
            echo "<td valign=\"top\">";
            $this->tag_anzeigen($plan_id, $benutzer_id, $tag, $wochenplan_arr, $dauer_zwischen_min);
            echo "</td>";
        }
        echo "</tr>";

        echo "</TABLE>";
        if (request()->has('termine')) {
            echo "<h1>TERMINE VEREINBAREN AUTOMATSICH</h1>";
            $this->alle_termine_vereinbaren($wochenplan_arr, $plan_id, $benutzer_id);
        }

        if (request()->has('tag_termine')) {
            echo "<h1>TAGESTERMINE VEREINBAREN AUTOMATSICH</h1>";
            $tag = request()->input('tag_termine');
            if (!$tag) {
                $tag = '0';
            }
            $this->tag_termine_vereinbaren($wochenplan_arr, $plan_id, $benutzer_id, $tag);
        }
    }

    function kalenderwoche()
    {
        $this->kalenderwoche = '';
        $this->kalenderwoche = date("W");
        return date("W");
    }

    function erster_montag_kw($kw, $jahr)
    {
        {
            $Jan_1 = mktime(1, 1, 1, 1, 1, $jahr);
            $ersterMontag = (11 - date('w', $Jan_1)) % 7 - 3;
            $erster_montag_woche = strtotime(($kw - 1) . ' weeks ' . $ersterMontag . ' days', $Jan_1);
            return date("d.m.Y", $erster_montag_woche);
        }
    }

    function tag_danach($datum)
    {
        $datum_arr = explode(".", $datum);
        $jahr = $datum_arr [2];
        $monat = $datum_arr [1];
        $tag = $datum_arr [0];
        $morgen = date('d.m.Y', mktime(0, 0, 0, $monat, $tag + 1, $jahr));
        return $morgen;
    }

    function alle_geraete_ng_arr_o_termin($plan_id)
    {
        $this->get_wplan_info($plan_id);
        $tage = $this->intervall_tage;

        $result = DB::select(" SELECT W_GERAETE.GERAETE_ID, W_GERAETE.BAUJAHR, W_GERAETE.BEZEICHNUNG, W_GERAETE.HERSTELLER, DATE_FORMAT( W_GERAETE.IM_EINSATZ, '%d.%m.%Y' ) IM_EINSATZ, KOSTENTRAEGER_TYP,KOSTENTRAEGER_ID, WARTUNG_ZUWEISUNG.PLAN_ID
FROM W_GERAETE
LEFT OUTER JOIN WARTUNG_ZUWEISUNG ON ( W_GERAETE.GERAETE_ID = WARTUNG_ZUWEISUNG.GERAETE_ID )
WHERE W_GERAETE.AKTUELL = '1' && WARTUNG_ZUWEISUNG.AKTUELL = '1' && WARTUNG_ZUWEISUNG.PLAN_ID = '$plan_id' && W_GERAETE.GERAETE_ID NOT
IN (

SELECT GERAETE_ID
FROM WARTUNGEN
WHERE DATE_FORMAT( WARTUNGEN.WARTUNGSDATUM, '%Y' ) = YEAR( NOW( ) )
&&  DATE_FORMAT( WARTUNGEN.WARTUNGSDATUM, '%Y' ) = YEAR( NOW( ) ) -1 && WARTUNGEN.PLAN_ID = '$plan_id' && WARTUNGEN.AKTUELL = '1'
) && W_GERAETE.GERAETE_ID NOT
IN (

SELECT GERAETE_ID
FROM W_TERMINE
WHERE DATE_FORMAT( TERMIN, '%Y' ) = YEAR( NOW( ) ) && PLAN_ID = '$plan_id' && AKTUELL='1' && ABGESAGT='0')
ORDER BY KOSTENTRAEGER_TYP,KOSTENTRAEGER_ID, W_GERAETE.IM_EINSATZ ASC
");

        if (!empty($result)) {
            return $result;
        }
    }

    function termine_arr($datum, $plan_id, $benutzer_id)
    {
        $datum_sql = date_german2mysql($datum);
        $result = DB::select("SELECT DATE_FORMAT(TERMIN,'%H:%i') AS ZEIT, GERAETE_ID, ABGESAGT, DAUER_MIN FROM `W_TERMINE` WHERE DATE_FORMAT( TERMIN, '%Y-%m-%d' ) = '$datum_sql'  &&  BENUTZER_ID='$benutzer_id' && AKTUELL='1' && PLAN_ID='$plan_id'   ORDER BY TERMIN ASC");
        if (!empty($result)) {
            return $result;
        } else {
            return false;
        }
    }

    function naechster_termin($startzeit, $dauer_min, $pause_min)
    {
        $timestamp = strtotime("$startzeit");
        //
        $dauer_gesamt = $dauer_min + $pause_min;
        $naechster = strtotime("+$dauer_gesamt minutes", $timestamp);
        //
        $naechster_termin = date('H:i', $naechster);
        return $naechster_termin;
    }

    function tag_anzeigen($plan_id, $benutzer_id, $tag, $array, $dauer_zwischen_min)
    {
        $anzahl_termine = count($array ['TERMINE'] [$tag] ['TERMINE']);
        $datum = $array ['TERMINE'] [$tag] [DATUM];
        // echo "<h1>$datum</h1>";
        for ($a = 0; $a < $anzahl_termine; $a++) {
            $zeit = $array ['TERMINE'] [$tag] ['TERMINE'] [$a] ['ZEIT'];
            $dauer = $array ['TERMINE'] [$tag] ['TERMINE'] [$a] ['DAUER'];
            $abgesagt = $array ['TERMINE'] [$tag] ['TERMINE'] [$a] ['ABGESAGT'];

            $geraete_id = $array ['TERMINE'] [$tag] ['TERMINE'] [$a] ['GERAETE_ID'];
            $temp_geraete_id = $array ['TERMINE'] [$tag] ['TERMINE'] [$a] ['TEMP_GERAETE_ID'];
            if (!$geraete_id) {
                $geraete_id = $temp_geraete_id;
            }
            $this->geraete_infos($plan_id, $geraete_id);
            if ($abgesagt == '1') {
                $termin_ende = $this->naechster_termin($zeit, $dauer, $dauer_zwischen_min);
                echo "<p id=\"status_rot\"><b>$zeit - $termin_ende $this->kostentraeger_bez ABGESAGT</b></p>";
                $this->termin_check($plan_id, $benutzer_id, $datum, $zeit);
                if ($this->termin == 'FREI') {
                    echo "<p id=\"status_gruen\"><a href=\"?daten=urlaub&option=test&tag_termine=$tag\">Termin eintragen</a></p><hr>";
                }
            }
            if ($abgesagt == '0') {
                $termin_ende = $this->naechster_termin($zeit, $dauer, $dauer_zwischen_min);
                echo "<p id=\"status_schwarz\">$zeit - $termin_ende $dauer min<br> <b>$this->kostentraeger_bez</b></p><p id=\"status_rot\">LW:$this->wartungsdatum</p><hr>";
            }

            if (!isset ($abgesagt)) {
                $termin_ende = $this->naechster_termin($zeit, $dauer, $dauer_zwischen_min);
                echo "<p id=\"status_gruen\">$zeit - $termin_ende $dauer min<br> <b>$this->kostentraeger_bez</b></p>";
                echo "<p id=\"status_gruen\"><a href=\"?daten=urlaub&option=test&tag_termine=$tag\">Termin eintragen</a></p><hr>";
            }
        }
        echo "<a href=\"?daten=urlaub&option=test&tag_termine=$tag\">Tag vereinbaren</a>";
    }

    function alle_termine_vereinbaren($array, $plan_id, $benutzer_id)
    {
        $anzahl_tage = count($array ['TERMINE']);
        for ($z = 0; $z < $anzahl_tage; $z++) {
            $tag = $z;

            $anzahl_termine = count($array ['TERMINE'] [$tag] ['TERMINE']);
            $datum = date_german2mysql($array ['TERMINE'] [$tag] [DATUM]);
            // echo "<h1>$datum</h1>";
            for ($a = 0; $a <= $anzahl_termine; $a++) {
                $zeit = $array ['TERMINE'] [$tag] ['TERMINE'] [$a] ['ZEIT'];
                $dauer = $array ['TERMINE'] [$tag] ['TERMINE'] [$a] ['DAUER'];
                $abgesagt = $array ['TERMINE'] [$tag] ['TERMINE'] [$a] ['ABGESAGT'];

                $geraete_id = $array ['TERMINE'] [$tag] ['TERMINE'] [$a] ['GERAETE_ID'];
                $temp_geraete_id = $array ['TERMINE'] [$tag] ['TERMINE'] [$a] ['TEMP_GERAETE_ID'];
                if ($abgesagt == '1') {
                    $geraete_id = $temp_geraete_id;
                }
                if (!$geraete_id) {
                    $geraete_id = $temp_geraete_id;
                }
                // $datum = date_german2mysql($datum);
                // $termin_dat_zeit = "$datum $zeit";

                if ($geraete_id > 0) {
                    $this->termin_speichern($benutzer_id, $plan_id, $datum, $zeit, $geraete_id, $dauer);
                }
            } // end for a
        } // end for z
    }

    function termin_speichern($benutzer_id, $plan_id, $datum, $zeit, $geraete_id, $dauer)
    {
        $datum1 = date_mysql2german($datum);
        $this->termin_check($plan_id, $benutzer_id, $datum1, $zeit);

        if ($this->termin == 'FREI') {
            $termin_dat_zeit = "$datum $zeit";
            $db_abfrage = "INSERT INTO W_TERMINE VALUES (NULL, '$plan_id', '$termin_dat_zeit', '$dauer','$geraete_id', '$benutzer_id', '0', NULL, NULL,NULL,NULL, NULL, '1')";
            DB::insert($db_abfrage);
        }
    }

    function termin_check($plan_id, $benutzer_id, $datum, $zeit)
    {
        $datum_sql = date_german2mysql($datum);
        unset ($this->termin);

        $result = DB::select("SELECT * FROM `W_TERMINE` WHERE DATE_FORMAT( TERMIN, '%Y-%m-%d' ) = '$datum_sql'  && DATE_FORMAT( TERMIN, '%H:%i' )= '$zeit' && BENUTZER_ID='$benutzer_id' && PLAN_ID='$plan_id' && ABGESAGT='0'");
        if (!empty($result)) {
            $this->termin = '';
            $row = $result[0];
            $geraet_id = $row ['GERAETE_ID'];
            $this->geraete_infos($plan_id, $geraet_id);
            $this->termin = $this->kostentraeger_bez;
        } else {
            $this->termin = 'FREI';
        }
    }

    function tag_termine_vereinbaren($array, $plan_id, $benutzer_id, $tag)
    {
        $anzahl_termine = count($array ['TERMINE'] [$tag] ['TERMINE']);
        $datum = date_german2mysql($array ['TERMINE'] [$tag] [DATUM]);
        // echo "<h1>$datum</h1>";
        for ($a = 0; $a <= $anzahl_termine; $a++) {
            $zeit = $array ['TERMINE'] [$tag] ['TERMINE'] [$a] ['ZEIT'];
            $dauer = $array ['TERMINE'] [$tag] ['TERMINE'] [$a] ['DAUER'];
            $abgesagt = $array ['TERMINE'] [$tag] ['TERMINE'] [$a] ['ABGESAGT'];

            $geraete_id = $array ['TERMINE'] [$tag] ['TERMINE'] [$a] ['GERAETE_ID'];
            $temp_geraete_id = $array ['TERMINE'] [$tag] ['TERMINE'] [$a] ['TEMP_GERAETE_ID'];
            if ($abgesagt == '1') {
                $geraete_id = $temp_geraete_id;
            }
            if (!$geraete_id) {
                $geraete_id = $temp_geraete_id;
            }
            // $datum = date_german2mysql($datum);
            // $termin_dat_zeit = "$datum $zeit";

            if ($geraete_id > 0) {
                $this->termin_speichern($benutzer_id, $plan_id, $datum, $zeit, $geraete_id, $dauer);
            }
        } // end for a
    }

    function monatsansicht($plan_id, $benutzer_id, $jahr, $monat)
    {
        $uhrzeit_1_wartung = '07:00';
        $dauer_wartung_min = '90';
        $dauer_zwischen_min = '10';
        $wartungen_tag = '6';

        $anzahl_tage = letzter_tag_im_monat($monat, $jahr);

        echo "<TABLE>";
        echo "<tr><td>DATUM</td>";
        $next_termin = $uhrzeit_1_wartung;
        for ($a = 1; $a < $wartungen_tag; $a++) {

            echo "<td>$next_termin</td>";
            $next_termin = $this->naechster_termin($next_termin, $dauer_wartung_min, $dauer_zwischen_min);
        }
        echo "</tr></TABLE>";
        unset ($a); // wegen nächste for schleife
        echo "<b>$erster_termin</b>";

        for ($a = 1; $a <= $anzahl_tage; $a++) {
            $datum = "$a.$monat.$jahr";
            echo "$datum<br>";
        } // end for monatsschleife
    }

    function form_geraete_erfassen()
    {
        $f = new formular ();
        $f->erstelle_formular("Geräte für Wartungen erfassen", NULL);
        $f->text_feld("Gerätebezeichnung", "bezeichnung", "", '50', 'bezeichnung', '');
        $f->text_feld("Hersteller", "hersteller", "", '50', 'hersteller', '');
        $f->text_feld('Baujahr', 'baujahr', "", '10', 'baujahr', '');
        $f->datum_feld('Eingebaut am', 'eingebaut', "", 'eingebaut');
        $f->datum_feld('Datum der letzten Wartung', 'wartungstermin', "", 'wartungstermin');
        $b = new buchen ();
        $js_typ = "onchange=\"list_kostentraeger('list_kostentraeger', this.value)\"";
        $b->dropdown_kostentreager_typen('Kostenträgertyp', 'kostentraeger_typ', 'kostentraeger_typ', $js_typ);

        $js_id = "";
        $b->dropdown_kostentreager_ids('Kostenträger', 'kostentraeger_id', 'dd_kostentraeger_id', $js_id);
        $this->dropdown_wplaene();
        $f->hidden_feld("option", "geraet_gesendet");
        $f->send_button("submit", "Eintragen");
        $f->ende_formular();
    }

    function dropdown_wplaene()
    {
        $result = DB::select("SELECT PLAN_ID, PLAN_BEZEICHNUNG  FROM `WARTUNGSPLAN` WHERE  `AKTUELL` ='1' ORDER BY PLAN_BEZEICHNUNG ASC");
        echo "<label for=\"plan_id\">Wartungsplan</label><select name=\"plan_id\" id=\"plan_id\" size=1>\n";
        echo "<option value=\"\">Bitte wählen</option>\n";
        if (!empty($result)) {

            foreach($result as $row) {
                $plan_id = $row ['PLAN_ID'];
                $beschreibung = $row ['PLAN_BEZEICHNUNG'];
                if (session()->has('plan_id')) {
                    if ($plan_id == session()->get('plan_id')) {
                        echo "<option value=\"$plan_id\" selected>$beschreibung</option>\n";
                    } else {
                        echo "<option value=\"$plan_id\">$beschreibung</option>\n";
                    }
                } else {
                    echo "<option value=\"$plan_id\">$beschreibung</option>\n";
                }
            }
        }
        echo "</select>\n";
    }

    function wartungsplan_auswahl()
    {
        if (!session()->has('plan_id')) {
            $f = new formular ();
            $f->erstelle_formular("Wartungsplan auswählen", NULL);
            $this->dropdown_wplaene();
            $f->hidden_feld("option", "wplan_gesendet");
            $f->send_button("submit", "Auswählen");
            $f->ende_formular();
        }
    }

    function wartungsplan_infos($plan_id)
    {
        $result = DB::select("SELECT * FROM `WARTUNGSPLAN` WHERE `PLAN_ID` ='$plan_id'   AND `AKTUELL` = '1'  LIMIT 0,1");
        if (!empty($result)) {
            $row = $result[0];
            $this->plan_bezeichnung = $row ['PLAN_BEZEICHNUNG'];
            $this->intervall = $row ['INTERVALL_NAME'];
            $this->faktor = $row ['FAKTOR'];
            $this->gewerk_id = $row ['GEWERK_ID'];
        } else {
            return false;
        }
    }

    function form_termin($geraet_id, $plan_id)
    {
        $f = new formular ();
        $this->geraete_infos($plan_id, $geraet_id);
        $f->erstelle_formular("Manuelle Termineingabe für $this->bezeichnung $this->kostentraeger_bez", NULL);
        $f->datum_feld('Datum', 'datum', "", 'datum');

        $f->text_feld("Uhrzeit z.B. 10:00", "uhrzeit", "", '10', 'uhrzeit', '');
        $f->text_feld("Dauer/Min", "dauer", "", '10', 'dauer', '');
        $b = new benutzer ();
        $b->dropdown_benutzer();
        // $f->hidden_feld("benutzer_id", "$benutzer_id");
        $f->hidden_feld("option", "wartungstermin");
        $f->send_button("submit", "Eintragen");
        $f->ende_formular();
    }

    function geraete_uebersicht_alle($plan_id)
    {
        $geraete_arr = $this->alle_geraete_arr($plan_id);
        $anzahl = count($geraete_arr);
        echo "<table>";
        echo "<tr class=\"feldernamen\"><td>GERÄT</td><td>BEZEICHNUNG</td><td>BAUJAHR</td><td>EINGEBAUT</td><td>L. WARTUNG</td><td>TERMIN</td></tr>";
        for ($a = 0; $a < $anzahl; $a++) {
            $geraete_id = $geraete_arr [$a] ['GERAETE_ID'];
            $this->geraete_infos($plan_id, $geraete_id);
            $this->termin_check_geraet($plan_id, $geraete_id);
            echo "<tr><td>$this->kostentraeger_bez</td><td>$this->bezeichnung</td><td>$this->baujahr</td><td>$this->im_einsatz</td><td>$this->wartungsdatum $this->gewartet_von</td><td>$this->termin_g $this->termin_von</td></tr>";
        }
        echo "</table>";
    }

    function alle_geraete_arr($plan_id)
    {
        $result = DB::select("SELECT W_GERAETE.GERAETE_ID  FROM W_GERAETE JOIN WARTUNG_ZUWEISUNG ON(W_GERAETE.GERAETE_ID=WARTUNG_ZUWEISUNG.GERAETE_ID) WHERE WARTUNG_ZUWEISUNG.PLAN_ID='$plan_id' && WARTUNG_ZUWEISUNG.AKTUELL='1' && W_GERAETE.AKTUELL='1' ORDER BY KOSTENTRAEGER_TYP, KOSTENTRAEGER_ID ASC");
        if (!empty($result)) {
            return $result;
        } else {
            return false;
        }
    }

    function wartungen_anzeigen($lage)
    {
        $g_arr = $this->get_geraete_arr($lage);
        if (is_array($g_arr)) {
            $anz_g = count($g_arr);
            for ($a = 0; $a < $anz_g; $a++) {
                $g_id = $g_arr [$a] ['GERAETE_ID'];
                $g_bez = $g_arr [$a] ['BEZEICHNUNG'];
                $termine = $this->get_termine_($g_id);
                if (is_array($termine)) {
                    $anz_t = count($termine);
                    echo "<table class=\"sortable\">";
                    echo "<tr><th colspan=\"2\">$g_bez</th></tr>";
                    for ($t = 0; $t < $anz_t; $t++) {
                        $datum = date_mysql2german($termine [$t] ['DATUM']);
                        $von = $termine [$t] ['VON'];
                        $bis = $termine [$t] ['BIS'];
                        $b_id = $termine [$t] ['BENUTZER_ID'];
                        $text = $termine [$t] ['TEXT'];
                        if ($text == 'x' or $text == 'X') {
                            $text = '';
                        } else {
                            $text = $text . '<br>';
                        }
                        $bb = new benutzer ();
                        $bb->get_benutzer_infos($b_id);
                        echo "<tr><td>$datum</td><td>$text$bb->benutzername</td></tr>";
                    }
                    echo "</table>";
                }
            }
        } else {
            echo "<b>Keine Wartungsgeräte</b><br>";
        }
    }

    function get_geraete_arr($lage)
    {
        $result = DB::select("SELECT * FROM `W_GERAETE` WHERE  `LAGE_RAUM` =  '$lage' AND  `AKTUELL` =  '1'");
        if (!empty($result)) {
            return $result;
        } else {
            return false;
        }
    }

    function get_termine_($g_id)
    {
        $result = DB::select("SELECT * FROM  `GEO_TERMINE` WHERE  `GERAETE_ID` ='$g_id' && AKTUELL='1' ORDER BY DATUM DESC");
        if (!empty($result)) {
            return $result;
        } else {
            return false;
        }
    }

    function ausgabe($gruppen_id, $monate_plus_int, $format = 'tab')
    {
        $monat = date("m");
        $jahr = date("Y");

        $thermen_arr = $this->wartungen($gruppen_id, $monate_plus_int);
        if (is_array($thermen_arr)) {
            $anz = count($thermen_arr);
            // echo "ANZ: $anz<br>";
            for ($a = 0; $a < $anz; $a++) {
                $einheit_kn = ltrim(rtrim($thermen_arr [$a] ['EINBAUORT']));
                $e = new einheit ();
                $e->get_einheit_id($einheit_kn);
                $e->get_einheit_info($e->einheit_id);
                // echo "$einheit_kn $e->einheit_id<br>";
                // echo '<pre>';
                // print_r($e);
                $thermen_arr [$a] ['STR'] = "$e->haus_strasse $e->haus_nummer, $e->haus_plz $e->haus_stadt";
                $thermen_arr [$a] ['LAGE'] = $e->einheit_lage;

                $mv_id = $e->get_mietvertraege_zu($e->einheit_id, $jahr, $monat, 'DESC'); // OK

                if ($mv_id) {
                    $mvs = new mietvertraege ();
                    $mvs->get_mietvertrag_infos_aktuell($mv_id);
                    // print_r($mvs);

                    // $kontaktdaten = $e->kontaktdaten_mieter($mv_id);
                    $thermen_arr [$a] ['KONTAKT'] = $e->kontaktdaten_mieter($mv_id);
                    $thermen_arr [$a] ['MIETER'] = $mvs->personen_name_string_u;
                    $kontaktdaten = '';
                } else {
                    $thermen_arr [$a] ['KONTAKT'] = 'Hausverwaltung!!';
                    $thermen_arr [$a] ['MIETER'] = 'Leerstand';
                }

                $thermen_arr [$a] ['L_WART'] = date_mysql2german($thermen_arr [$a] ['L_WART']);
                $thermen_arr [$a] ['TERMIN_NEU'] = ' ';
                $thermen_arr [$a] ['Z'] = $a + 1;
                unset ($mv_id);
                unset ($e);
            } // end for
        } else {
            echo "KEINE WARTUNGEN";
            die ();
        }
        // echo '<pre>';
        // print_r($thermen_arr);
        // die();
        if ($format == 'PDF') {
            ob_clean();
            $pdf = new Cezpdf ('a4', 'landscape');
            $bpdf = new b_pdf ();
            $bpdf->b_header($pdf, 'Partner', session()->get('partner_id'), 'landscape', 'Helvetica.afm', 6);

            $cols = array(
                'Z' => '',
                'EINBAUORT' => "WOHNUNG",
                'MIETER' => 'MIETER',
                'KONTAKT' => 'KONTAKT',
                'STR' => 'ANSCHRIFT',
                'LAGE' => 'LAGE',
                'HERSTELLER' => 'HERSTELLER',
                'BEZEICHNUNG' => 'BEZEICHNUNG',
                'L_WART' => 'LETZTE W.',
                'PART' => 'RG AN',
                'TERMIN_NEU' => 'TERMIN (DATUM: UHRZEIT'
            );
            $pdf->ezTable($thermen_arr, $cols, "Thermen", array(
                'showHeadings' => 1,
                'shaded' => 1,
                'titleFontSize' => 8,
                'fontSize' => 7,
                'xPos' => 50,
                'xOrientation' => 'right',
                'width' => 700,
                'cols' => array(
                    'PART' => array(
                        'justification' => 'left',
                        'width' => 150
                    ),
                    'KOSTENTRAEGER_BEZ' => array(
                        'justification' => 'left',
                        'width' => 75
                    ),
                    'KONTO' => array(
                        'justification' => 'right',
                        'width' => 30
                    ),
                    'AUSZUG' => array(
                        'justification' => 'right',
                        'width' => 35
                    ),
                    'PLATZ' => array(
                        'justification' => 'left',
                        'width' => 50
                    )
                )
            ));

            header("Content-type: application/pdf"); // wird von MSIE ignoriert
            $pdf->ezStream();
        }
    }

    function wartungen($gruppen_id, $monate_plus_int)
    {
        $db_abfrage = "SELECT (SELECT PARTNER_NAME FROM PARTNER_LIEFERANT WHERE PARTNER_ID=W_GERAETE.KOSTENTRAEGER_ID && AKTUELL='1' ORDER BY PARTNER_DAT DESC LIMIT 0,1) AS PART, `GERAETE_ID`, LAGE_RAUM AS EINBAUORT, HERSTELLER, BEZEICHNUNG, KOSTENTRAEGER_TYP, KOSTENTRAEGER_ID, `INTERVAL_M`, DATE_FORMAT(NOW(),'%Y-%m-%d') as HEUTE, DATE_FORMAT(DATE_ADD(DATE_FORMAT(NOW(),'%Y-%m-%d'), INTERVAL -INTERVAL_M MONTH),'%Y-%m-%d') AS L_WART_FAELLIG, (SELECT DATUM FROM GEO_TERMINE WHERE GERAETE_ID=W_GERAETE.GERAETE_ID && AKTUELL='1' ORDER BY DATUM DESC LIMIT 0,1) AS L_WART FROM `W_GERAETE` WHERE `AKTUELL`='1' && GRUPPE_ID='$gruppen_id' && `KOSTENTRAEGER_TYP`='Partner' && (`KOSTENTRAEGER_ID`='3' or `KOSTENTRAEGER_ID`='947' or `KOSTENTRAEGER_ID`='986' or `KOSTENTRAEGER_ID`='661' or `KOSTENTRAEGER_ID`='1148' or `KOSTENTRAEGER_ID`='974') &&
	GERAETE_ID NOT IN
	(SELECT GERAETE_ID FROM GEO_TERMINE WHERE AKTUELL='1' && (DATUM>=DATE_FORMAT(DATE_ADD(DATE_FORMAT(NOW(),'%Y-%m-%d'), INTERVAL -(INTERVAL_M-$monate_plus_int) MONTH),'%Y-%m-%d') AND DATUM <= DATE_FORMAT(NOW(),'%Y-%m-%d')) )
	AND
	GERAETE_ID NOT IN
	(SELECT GERAETE_ID FROM GEO_TERMINE WHERE AKTUELL='1' && DATUM>DATE_FORMAT(NOW(),'%Y-%m-%d') GROUP BY GERAETE_ID) ORDER BY EINBAUORT ASC, L_WART ASC";
        $result = DB::select($db_abfrage);
        if (!empty($result)) {
            return $result;
        }
    }
} // end class wartungen

/*
 * SELECT WARTUNGSPLAN.`PLAN_ID`, `PLAN_BEZEICHNUNG`, `INTERVALL_NAME`, `FAKTOR`, GERAETE_ID FROM `WARTUNGSPLAN` join WARTUNG_ZUWEISUNG ON (WARTUNG_ZUWEISUNG.PLAN_ID=WARTUNGSPLAN.PLAN_ID)
 *
 *
 * SELECT WARTUNGSPLAN.`PLAN_ID`, IM_EINSATZ, `PLAN_BEZEICHNUNG`, `INTERVALL_NAME`, `FAKTOR`, W_GERAETE.GERAETE_ID, HERSTELLER, W_GERAETE.BEZEICHNUNG FROM `WARTUNGSPLAN` join (WARTUNG_ZUWEISUNG, W_GERAETE) ON (WARTUNG_ZUWEISUNG.PLAN_ID=WARTUNGSPLAN.PLAN_ID && WARTUNG_ZUWEISUNG.GERAETE_ID=W_GERAETE.GERAETE_ID) WHERE DATE_FORMAT(IM_EINSATZ, '%m') = '01' && W_GERAETE.GERAETE_ID NOT IN (SELECT GERAETE_ID FROM WARTUNGEN WHERE DATE_FORMAT(WARTUNGSDATUM, '%Y') = '2010')
 *
 * SELECT WARTUNGSPLAN.`PLAN_ID`, IM_EINSATZ, `PLAN_BEZEICHNUNG`, `INTERVALL_NAME`, `FAKTOR`, W_GERAETE.GERAETE_ID, HERSTELLER, W_GERAETE.BEZEICHNUNG FROM `WARTUNGSPLAN` join (WARTUNG_ZUWEISUNG, W_GERAETE) ON (WARTUNG_ZUWEISUNG.PLAN_ID=WARTUNGSPLAN.PLAN_ID && WARTUNG_ZUWEISUNG.GERAETE_ID=W_GERAETE.GERAETE_ID) WHERE DATE_FORMAT(IM_EINSATZ, '%m') = '01' && W_GERAETE.GERAETE_ID NOT IN (SELECT GERAETE_ID FROM WARTUNGEN WHERE DATE_FORMAT(WARTUNGSDATUM, '%Y') = '2009')
 *
 * SELECT WARTUNGSPLAN.`PLAN_ID`, IM_EINSATZ, `PLAN_BEZEICHNUNG`, `INTERVALL_NAME`, `FAKTOR`, W_GERAETE.GERAETE_ID, HERSTELLER, W_GERAETE.BEZEICHNUNG, WARTUNGSDATUM FROM `WARTUNGSPLAN` join (WARTUNG_ZUWEISUNG, W_GERAETE, WARTUNGEN) ON (WARTUNG_ZUWEISUNG.PLAN_ID=WARTUNGSPLAN.PLAN_ID && WARTUNG_ZUWEISUNG.GERAETE_ID=W_GERAETE.GERAETE_ID && WARTUNGEN.GERAETE_ID=W_GERAETE.GERAETE_ID) WHERE DATE_FORMAT(IM_EINSATZ, '%m') = '01' && DATE_FORMAT(WARTUNGSDATUM, '%Y') <='2010'
 *
 */

?>
