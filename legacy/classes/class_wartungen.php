<?php

class wartung
{
    public $intervall_period;
    public $gewerk_id;
    public $plan_id;
    public $benutzer_id;
    public $datum_g;
    public $kostentraeger_typ;
    public $kostentraeger_id;
    public $kostentraeger_bez;
    public $plan_bez;
    public $intervall;
    public $geraet_id;
    public $wartungsdatum;
    public $n_wartung;
    public $bemerkung;
    public $gewartet_von;
    public $termin_von;
    public $termin_g;
    public $baujahr;
    public $bezeichnung;
    public $termin;

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
    
    function wartungen_anzeigen($lage)
    {
        $g_arr = $this->get_geraete_arr($lage);
        if (!empty($g_arr)) {
            $anz_g = count($g_arr);
            for ($a = 0; $a < $anz_g; $a++) {
                $g_id = $g_arr [$a] ['GERAETE_ID'];
                $g_bez = $g_arr [$a] ['BEZEICHNUNG'];
                $termine = $this->get_termine_($g_id);
                if (!empty($termine)) {
                    $anz_t = count($termine);
                    echo "<table class=\"sortable\">";
                    echo "<tr><th colspan=\"2\">$g_bez</th></tr>";
                    for ($t = 0; $t < $anz_t; $t++) {
                        $datum = date_mysql2german($termine [$t] ['DATUM']);
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
        return $result;
    }

    function get_termine_($g_id)
    {
        $result = DB::select("SELECT * FROM  `GEO_TERMINE` WHERE  `GERAETE_ID` ='$g_id' && AKTUELL='1' ORDER BY DATUM DESC");
        return $result;
    }
} // end class wartungen