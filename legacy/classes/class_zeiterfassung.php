<?php

class zeiterfassung
{
    public $st_benutzer_id;
    public $beschreibung;
    public $erf_datum;
    public $gewerk_id;
    public $stundensatz;
    public $BP_PARTNER_ID;
    public $z_zettel_id;
    public $z_beschreibung;
    public $st_benutzername;
    public $stundenzettel_id;
    public $erf_datum_mysql;

    function zeiterfassung()
    {
        $this->benutzer_id = Auth::user()->id;
        $this->gewerk_finden($this->benutzer_id);
        $this->eigene_zettel_arr = $this->stundenzettel_in_arr($this->benutzer_id);
        $this->anzahl_eigene_zettel = $this->anzahl_e_zettel($this->benutzer_id);
        unset ($this->eigene_zettel_arr);
        $this->stunden_pro_woche = 40;
        $this->gesamt_azeit_min = $this->gesamt_azeit_min($this->benutzer_id);
        $this->gesamt_azeit_std = $this->min2std($this->gesamt_azeit_min);
        $this->gesamt_soll_stunden = $this->stunden_pro_woche * $this->anzahl_eigene_zettel;
        $this->bp_partner_id = $this->get_partner_id_benutzer($this->benutzer_id);
        $p = new partners ();
        $p->get_partner_name($this->bp_partner_id);
        $this->partner_name = $p->partner_name;
    }

    function gewerk_finden($benutzer_id)
    {
        $result = \App\Models\Person::find($benutzer_id);
        if (isset($result->jobsAsEmplyee[0])) {
            $this->gewerk_id = $result->jobsAsEmplyee[0]->job_title_id;
        } else {
            $this->gewerk_id = null;
        }
    }

    function stundenzettel_in_arr($benutzer_id)
    {
        $result = DB::select("SELECT ZETTEL_ID, BESCHREIBUNG, ERFASSUNGSDATUM FROM STUNDENZETTEL WHERE BENUTZER_ID='$benutzer_id' && AKTUELL = '1' ORDER BY ERFASSUNGSDATUM DESC");
        return $result;
    }

    function anzahl_e_zettel($benutzer_id)
    {
        $result = DB::select("SELECT COUNT(*) AS ZAHL FROM `STUNDENZETTEL` WHERE `BENUTZER_ID` = '$benutzer_id' && AKTUELL='1'");
        $row = $result[0];
        return $row ['ZAHL'];
    }

    function gesamt_azeit_min($benutzer_id)
    {
        $this->eigene_zettel_arr = $this->stundenzettel_in_arr($benutzer_id);
        $this->anzahl_eigene_zettel = count($this->eigene_zettel_arr);

        $gesamt_azeit_min = 0;
        for ($a = 0; $a < $this->anzahl_eigene_zettel; $a++) {
            $zettel_id = $this->eigene_zettel_arr [$a] ['ZETTEL_ID'];
            $zeit_min = $this->gzeit_zettel($zettel_id);
            $gesamt_azeit_min = $gesamt_azeit_min + $zeit_min;
        }
        return $gesamt_azeit_min;
    }

    function gzeit_zettel($zettel_id)
    {
        $result = DB::select("SELECT SUM( DAUER_MIN ) AS G_ZEIT FROM `STUNDENZETTEL_POS` WHERE `ZETTEL_ID` = '$zettel_id' && AKTUELL='1'");
        $row = $result[0];
        return $row ['G_ZEIT'];
    }

    function min2std($minuten)
    {
        $stunden = $minuten / 60;
        $volle_stunden = intval($stunden);
        $volle_stunden_in_min = $volle_stunden * 60;
        $restmin = $minuten - $volle_stunden_in_min;
        $restmin = abs(sprintf("%02d", $restmin));
        $restmin = sprintf('%02d', $restmin);
        $std = "$volle_stunden:$restmin";
        return $std;
    }

    function get_partner_id_benutzer($benutzer_id)
    {
        $result = \App\Models\Person::findOrFail($benutzer_id);
        return isset($result->jobsAsEmployee[0]) ? $result->jobsAsEmployee[0]->employer->PARTNER_ID : null;
    }

    function form_zeile_aendern($zettel_id, $pos_dat)
    {
        $f = new formular ();
        $b = new buchen ();
        $f->erstelle_formular('Eintrag ändern', '');
        $zeile_arr = $this->zeile_in_arr($zettel_id, $pos_dat);
        if (!empty($zeile_arr)) {
            $datum = date_mysql2german($zeile_arr [0] ['DATUM']);
            $f->datum_feld("Datum:", "datum", "$datum", "10", 'datum', '');
            $f->hidden_feld("zettel_id", "$zettel_id");
            $f->hidden_feld("pos_dat", "$pos_dat");
            $pos_id = $zeile_arr [0] ['ST_ID'];
            $f->hidden_feld("pos_id", "$pos_id");
            $this->benutzer_id = $this->get_userid($zettel_id);
            $f->hidden_feld("benutzer_id", "$this->benutzer_id");
            $this->gewerk_finden($this->benutzer_id); // setzt gewerk_id vom benutzer
            $f->text_feld("Leistungsbeschreibung eingeben", "leistungs_beschreibung", "", "50", 'leistungsbeschreibung', '');
            $f->hidden_feld('dauer_min', '');
            $js_z = "onchange=\"zeitdiff('beginn', 'ende', 'dauer_be', 'dauer_min')\"";
            $js_z1 = "onclick=\"zeitdiff('beginn', 'ende', 'dauer_be', 'dauer_min')\"";

            $beginn = $zeile_arr [0] ['BEGINN'];
            $ende = $zeile_arr [0] ['ENDE'];
            session()->forget('beginn');
            session()->forget('ende');
            $this->dropdown_zeiten('Beginn', 'beginn', 'beginn', "$beginn", $js_z);
            $this->dropdown_zeiten('Ende', 'ende', 'ende', "$ende", $js_z);

            $dauer_min = $this->getzeitdiff_min($beginn, $ende);
            $zeitdauer = $this->min_in_zeit($dauer_min);

            $f->text_feld_inaktiv_js('Dauer zwischen Beginn und Ende', 'dauer_be', "$zeitdauer", 30, 'dauer_be', $js_z1);
            $js_typ = "onchange=\"list_kostentraeger('list_kostentraeger', this.value)\" onclick=\"list_kostentraeger('list_kostentraeger', this.value)\"";
            $k_typ = $zeile_arr [0] ['KOSTENTRAEGER_TYP'];
            $k_id = $zeile_arr [0] ['KOSTENTRAEGER_ID'];
            $r = new rechnung ();
            $k_bez = $r->kostentraeger_ermitteln($k_typ, $k_id);
            $b->dropdown_kostentreager_typen_vw("Kostenträgertyp ($k_typ)", 'kostentraeger_typ', 'kostentraeger_typ', $js_typ, $k_typ);

            $js_id = "";
            // $b->dropdown_kostentreager_ids("Kostenträger ($k_bez)", 'kostentraeger_id', 'dd_kostentraeger_id', $js_id);
            $b->dropdown_kostentraeger_bez_vw("Kostenträger ($k_bez)", 'kostentraeger_id', 'dd_kostentraeger_id', $js_id, $k_typ, $k_bez);
            $hinweis = $zeile_arr [0] ['HINWEIS'];
            $f->text_bereich('Hinweise / Notizen / Uhrzeiten / Besonderheiten (max. 1000 Zeichen)', 'hinweis', $hinweis, 40, 10, 'hinweis');
            $f->hidden_feld("option", "zettel_zeile_aendern");
            $js = "onmouseover=\"zeitdiff('beginn', 'ende', 'dauer_be', 'dauer_min')\"";
            $f->send_button_js("submit_zettel", "Änderungen Speichern", $js);
        } else {
            echo "Keine Daten vorhanden!";
        }

        $f->ende_formular();
    }

    function zeile_in_arr($zettel_id, $pos_dat)
    {
        $result = DB::select("SELECT ST_DAT, ST_ID, ZETTEL_ID, DATUM, LEISTUNG_ID, DAUER_MIN, KOSTENTRAEGER_TYP, KOSTENTRAEGER_ID, HINWEIS, BEGINN, ENDE, LEISTUNGSKATALOG.BEZEICHNUNG FROM STUNDENZETTEL_POS JOIN LEISTUNGSKATALOG ON (STUNDENZETTEL_POS.LEISTUNG_ID = LEISTUNGSKATALOG.LK_ID) WHERE ZETTEL_ID='$zettel_id' && ST_DAT='$pos_dat' && STUNDENZETTEL_POS.AKTUELL = '1' ORDER BY  DATUM, ST_ID ASC");
        return $result;
    }

    function get_userid($zettel_id)
    {
        $result = DB::select("SELECT BENUTZER_ID FROM STUNDENZETTEL WHERE ZETTEL_ID='$zettel_id' && AKTUELL='1' ORDER BY ZETTEL_DAT DESC LIMIT 0,1");

        $row = $result[0];
        return $row ['BENUTZER_ID'];
    }

    function dropdown_zeiten($label, $name, $id, $von, $js)
    {
        if (session()->has('ende')) {
            $von = session()->get('ende');
        }
        $std = 0;
        $min = '00';
        echo "<label for=\"$id\">$label</label><select name=\"$name\" id=\"$id\" size=\"1\" $js>";
        for ($a = 0; $a < 97; $a++) {
            $zeit = "$std:$min";
            /* Anfangszeit */
            if ($von == $zeit) {
                // echo "<b>$std:$min</b><br>";
                echo "<option value=\"$zeit\" selected><b>$zeit</b></option>";
            } else {
                // echo "$std:$min<br>";
                echo "<option value=\"$zeit\">$zeit</option>";
            }

            $min += 15;
            if ($min == 60) {
                $std += 1;
                $min = '00';
                // echo "$std : $min<br>";
            }
        }
        echo "</select>";
    }

    function getzeitdiff_min($anfangszeit, $endzeit)
    {
        /*
		 * $datum = '2010-01-01';
		 * $datum_arr = explode('-', $datum);
		 * $j = $datum_arr[0];
		 * $m = $datum_arr[1];
		 * $d = $datum_arr[2];
		 */
        // $anfangszeit='06:45';
        $anfangszeit_arr = explode(':', $anfangszeit);
        $a_std = $anfangszeit_arr ['0'] / 1;
        $a_min = $anfangszeit_arr ['1'] / 1;

        // $endzeit='15:15';
        $endzeit_arr = explode(':', $endzeit);
        $e_std = $endzeit_arr ['0'] / 1;
        $e_min = $endzeit_arr ['1'] / 1;

        /*
		 * $pause_von ='11:30';
		 * $pause_bis='12:00';
		 *
		 * $anzeige_von = '06:00';
		 * $anzeige_bis = '06:00';
		 */
        $t1 = mktime($a_std, $a_min, 0, 1, 12, 2000);
        $t2 = mktime($e_std, $e_min, 0, 1, 12, 2000);
        $diff_min = ($t2 - $t1) / 60;
        return $diff_min;
    }

    function min_in_zeit($min)
    {
        if ($min < 1) {
            return '00:00';
        } else {
            $std = sprintf('%02d', intval($min / 60));
            $minuten = sprintf('%02d', $min % 60);
            return "$std:$minuten";
        }
    }

    function eigene_stundenzettel_anzeigen()
    {
        $f = new formular ();
        $f->fieldset("Eigene Stundennachweise", 'z_anlegen');
        $this->benutzer_id = Auth::user()->id;
        $eigene_zettel_arr = $this->stundenzettel_in_arr($this->benutzer_id);
        if (empty($eigene_zettel_arr)) {
            echo "Sie haben keine Stundennachweise";
        } else {
            $anzahl_stundenzettel = count($eigene_zettel_arr);
            echo "<b>Sie haben $anzahl_stundenzettel Stundennachweise</b><br><br>";
            // echo "<b>NR.&nbsp;&nbsp; DATUM&nbsp;&nbsp;&nbsp; BESCHREIBUNG</b><br><br>";
            echo "<table>";
            // echo "<tr class=\"feldernamen\"><td>Nr.</td><td>Datum</td><td>Beschreibung</td><td>Optionen</td></tr>";
            echo "<tr>";
            echo "<th>Nr.</th>";
            echo "<th>Datum</th>";
            echo "<th>Beschreibung</th>";
            echo "<th>Optionen</th>";
            echo "</tr>";
            $z = 0;
            for ($a = 0; $a < $anzahl_stundenzettel; $a++) {
                $z++;
                $zeile = $a + 1;
                $zettel_id = $eigene_zettel_arr [$a] ['ZETTEL_ID'];

                if (!$this->check_if_beleg_erstellt($zettel_id)) {
                    $link_stundenzettel_del = "<a href='" . route('web::zeiterfassung::legacy', ['option' => 'zettel_loeschen', 'zettel_id' => $zettel_id]) . "'>Löschen</a>";
                } else {
                    $link_stundenzettel_del = '';
                }

                $link_stundenzettel_ansehen = "<a href='" . route('web::zeiterfassung::legacy', ['option' => 'zettel_ansehen', 'zettel_id' => $zettel_id]) . "'>Ansehen</a>";
                $link_stundenzettel_eingabe = "<a href='" . route('web::zeiterfassung::legacy', ['option' => 'zettel_eingabe', 'zettel_id' => $zettel_id]) . "'>Eingabe</a>";
                $link_pdf = "<a href='" . route('web::zeiterfassung::legacy', ['option' => 'zettel2pdf', 'zettel_id' => $zettel_id]) . "'>PDF-Ansicht</a>";
                $beschreibung = $eigene_zettel_arr [$a] ['BESCHREIBUNG'];
                $datum = date_mysql2german($eigene_zettel_arr [$a] ['ERFASSUNGSDATUM']);

                $in_belegnr = $this->check_if_beleg_erstellt($zettel_id);
                $anzahl_pos_in_zettel = $this->anzahl_pos_zettel($zettel_id);
                if ($in_belegnr) {
                    $status = "<b>BELEG ERSTELLT</b>";
                    $link_stundenzettel_eingabe = '';
                }
                if (!$in_belegnr && $anzahl_pos_in_zettel) {
                    $status = "Aktiv";
                }
                if (!$in_belegnr && !$anzahl_pos_in_zettel) {
                    $status = "<b>Stundennachweis leer</b>";
                }

                echo "<tr class=\"zeile$z\"><td>$zeile.</td><td>$datum</td><td>$beschreibung</td><td>$link_stundenzettel_ansehen $link_stundenzettel_eingabe $link_pdf $link_stundenzettel_del $status</td></tr>";
                if ($z == 2) {
                    $z = 0;
                }
            }
            echo "</table>";
        }
        $f->fieldset_ende();
    }

    function check_if_beleg_erstellt($zettel_id)
    {
        $result = DB::select("SELECT IN_BELEG FROM STUNDENZETTEL_POS WHERE ZETTEL_ID='$zettel_id' && AKTUELL='1' && (IN_BELEG != '0' OR IN_BELEG != NULL) LIMIT 0,1");
        return count($result);
    }

    function anzahl_pos_zettel($zettel_id)
    {
        $result = DB::select("SELECT * FROM STUNDENZETTEL_POS WHERE ZETTEL_ID='$zettel_id' && AKTUELL='1'");
        return count($result);
    }

    function stundenzettel_erfassen($zettel_id)
    {
        $this->stundenzettel_anzeigen($zettel_id);
        $f = new formular ();
        $b = new buchen ();
        $f->erstelle_formular("Neue Zeile", NULL);
        $f->datum_feld("Datum:", "datum", '', "datum");
        $f->hidden_feld("zettel_id", "$zettel_id");
        $f->hidden_feld("benutzer_id", "$this->benutzer_id");
        $f->text_feld("Leistungsbeschreibung eingeben", "leistungs_beschreibung", "", "50", 'leistungsbeschreibung', '');
        $f->hidden_feld('dauer_min', '');
        $js_z = "onchange=\"zeitdiff('beginn', 'ende', 'dauer_be', 'dauer_min');Materialize.updateTextFields();\"";
        $js_z1 = "onclick=\"zeitdiff('beginn', 'ende', 'dauer_be', 'dauer_min');Materialize.updateTextFields();\"";

        $this->dropdown_zeiten('Beginn', 'beginn', 'beginn', '6:45', $js_z);
        $this->dropdown_zeiten('Ende', 'ende', 'ende', '15:15', $js_z);
        $f->text_feld_inaktiv_js('Dauer zwischen Beginn und Ende', 'dauer_be', '', 30, 'dauer_be', $js_z1);
        $js_typ = "onchange=\"list_kostentraeger('list_kostentraeger', this.value)\"";
        $b->dropdown_kostentreager_typen('Kostenträgertyp', 'kostentraeger_typ', 'kostentraeger_typ', $js_typ);
        $js_id = "";
        $b->dropdown_kostentreager_ids('Kostenträger', 'kostentraeger_id', 'dd_kostentraeger_id', $js_id);
        $f->text_bereich('Hinweise / Notizen / Uhrzeiten / Besonderheiten (max. 1000 Zeichen)', 'hinweis', '', 40, 10, 'hinweis');
        $f->hidden_feld("option", "zettel_eingabe1");
        $js = "onmouseover=\"zeitdiff('beginn', 'ende', 'dauer_be', 'dauer_min');Materialize.updateTextFields();\"";
        $f->send_button_js("submit_zettel", "Speichern", $js);
        $f->ende_formular();
    }

    function stundenzettel_anzeigen($id)
    {
        $benutzer_id = $this->get_userid($id);
        $fehler = 0;
        if ($benutzer_id != Auth::user()->id) {
            if (!Auth::user()->hasAnyRole([
                \App\Libraries\Role::ROLE_BUCHHALTER,
                \App\Libraries\Role::ROLE_ADMINISTRATOR
            ])
            ) {
                $fehler = 1;
            }
        }

        if ($fehler == 1) {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\ErrorMessage('Keine Berechtigung')
            );
        }

        $f = new formular ();
        $this->stundenzettel_grunddaten($id);
        $this->bp_partner_id = $this->get_partner_id_benutzer($this->st_benutzer_id);
        $p = new partners ();
        $p->get_partner_name($this->bp_partner_id);
        $this->partner_name = $p->partner_name;

        // echo "Sie sehen den Stundennachweis <b>$this->beschreibung vom $this->erf_datum. Ersteller: $this->st_benutzername</b> Mitarbeiter von $this->partner_name<br>";

        // echo "<pre>";
        // print_r($this);
        $f->fieldset("Sie sehen den Stundennachweis <b>$this->beschreibung vom $this->erf_datum. Ersteller: $this->st_benutzername</b> Mitarbeiter von $this->partner_name", 'st_u');

        $stundenzettel_pos_arr = $this->stundenzettelleistungen_in_arr($id);
        if (empty($stundenzettel_pos_arr)) {
            echo "Stundenzettel enthält keine Daten";
        } else {
            $link_pdf = "<a href='" . route('web::zeiterfassung::legacy', ['option' => 'zettel2pdf', 'zettel_id' => $id]) . "'>PDF-Ansicht</a>";
            echo "<br>Stundenzettelinhalt $link_pdf<br><hr>";
            $anzahl_pos = count($stundenzettel_pos_arr);
            $gesamt_min = 0;
            echo "<table>";
            echo "<tr>";
            echo "<th>Zeile</th>";
            echo "<th>Datum</th>";
            echo "<th>Von</th>";
            echo "<th>Bis</th>";
            echo "<th>Beschreibung</th>";
            echo "<th>Kostenträger TYP</th>";
            echo "<th>Kostenträger</th>";
            echo "<th>Dauer</th>";
            echo "<th>Dauer/Min</th>";
            echo "<th>Option</th>";

            echo "</tr>";
            for ($a = 0; $a < $anzahl_pos; $a++) {
                $zeile = $a + 1;
                $beschreibung = $stundenzettel_pos_arr [$a] ['BEZEICHNUNG'];
                $datum = date_mysql2german($stundenzettel_pos_arr [$a] ['DATUM']);
                $kostentraeger_typ = $stundenzettel_pos_arr [$a] ['KOSTENTRAEGER_TYP'];
                $kostentraeger_id = $stundenzettel_pos_arr [$a] ['KOSTENTRAEGER_ID'];
                $dauer_min = $stundenzettel_pos_arr [$a] ['DAUER_MIN'];
                $gesamt_min = $gesamt_min + $dauer_min;
                $pos_dat = $stundenzettel_pos_arr [$a] ['ST_DAT'];
                $hinweis = $stundenzettel_pos_arr [$a] ['HINWEIS'];
                $beginn = $stundenzettel_pos_arr [$a] ['BEGINN'];
                $ende = $stundenzettel_pos_arr [$a] ['ENDE'];

                $link_loschen = "<a href='" . route('web::zeiterfassung::legacy', ['option' => 'loeschen', 'zettel_id' => $id, 'pos_id' => $pos_dat]) . "'>Löschen</a>";
                $link_aendern = "<a href='" . route('web::zeiterfassung::legacy', ['option' => 'aendern', 'zettel_id' => $id, 'pos_id' => $pos_dat]) . "'>Ändern</a>";

                if ($kostentraeger_typ == 'Mietvertrag') {
                    $mv = new mietvertraege ();
                    $mv->get_mietvertrag_infos_aktuell($kostentraeger_id);
                    $kostentraeger_bez = $mv->personen_name_string_u;
                } else {
                    $r = new rechnung ();
                    $kostentraeger_bez = $r->kostentraeger_ermitteln($kostentraeger_typ, $kostentraeger_id);
                }
                $dauer_std = $this->min2std($dauer_min);
                echo "<tr><td>$zeile</td><td>$datum</td><td>$beginn<br><td>$ende<br><td>$beschreibung<br><b>$hinweis</b></td><td>$kostentraeger_typ</td><td>$kostentraeger_bez</td><td>$dauer_std Std.</td><td>$dauer_min Min.</td><td>$link_aendern $link_loschen</td></tr>";
                // echo "$zeile. $datum $beschreibung <b>$kostentraeger_typ</b> $kostentraeger_bez $dauer_std / $dauer_min min.<br>";
            }
            echo "</table>";
            echo "<hr>";
            $stunden_woche = $this->stunden_pro_woche;
            $stunden_woche_soll = $stunden_woche * 60; // std x min
            $stundengesamt = $gesamt_min / 60;
            $stunden_voll = intval($stundengesamt);
            $saldo_woche_min = $stunden_woche_soll - $gesamt_min;
            $saldo_woche_std = intval($saldo_woche_min / 60);
            $rest_std_in_min = $saldo_woche_std * 60;
            $restsaldo_min = $saldo_woche_min - $rest_std_in_min;

            if ($gesamt_min < $stunden_woche_soll) {
                $saldo_woche_std = '-' . $saldo_woche_std;
            } else {
                $saldo_woche_std = abs($saldo_woche_std);
            }

            if ($restsaldo_min < 0) {
                $restsaldo_min = abs($restsaldo_min);
            }

            $arbeitsdauer = $this->min2std($gesamt_min);

            $restsaldo_min = sprintf("%02d", $restsaldo_min);
            echo "<br><br><b>ÜBERSICHT DIESER STUNDENNACHWEIS</b><hr>";
            echo "Arbeitsdauer: $arbeitsdauer, ";
            echo "Soll: $stunden_woche:00, ";
            echo "Saldo: $saldo_woche_std Std. und $restsaldo_min Minuten<br><br>";
            echo "<hr><b>ÜBERSICHT: MITARBEITER -> $this->st_benutzername<hr>Gesamtarbeitszeit in Stunden: $this->gesamt_azeit_std<br>";
            echo "Gesamt Sollstunden: $this->gesamt_soll_stunden<br>";

            $g_ist_arbeitsdauer = $this->zeit2decimal($this->gesamt_azeit_std); // =80
            $g_soll_arbeitsdauer = $this->zeit2decimal($this->gesamt_soll_stunden);
            $stundenkonto_in_std_dec = $g_ist_arbeitsdauer - $g_soll_arbeitsdauer;
            $stundenkonto_in_std = $this->decimal2zeit($stundenkonto_in_std_dec);

            echo "Stundenkonto: $stundenkonto_in_std </b><hr>";
        }
    }

    function stundenzettel_grunddaten($id)
    {
        $result = DB::select("SELECT ZETTEL_ID, STUNDENZETTEL.BENUTZER_ID, BESCHREIBUNG, ERFASSUNGSDATUM, name, hourly_rate, hours_per_week 
                              FROM STUNDENZETTEL 
                                JOIN persons ON (STUNDENZETTEL.BENUTZER_ID = persons.id)
                                JOIN jobs ON (persons.id = jobs.employee_id)
                              WHERE ZETTEL_ID=? && AKTUELL = '1' LIMIT 0,1", [$id]);
        $row = $result[0];
        $this->stundenzettel_id = $row['ZETTEL_ID'];
        $this->st_benutzer_id = $row['BENUTZER_ID'];
        $this->beschreibung = $row['BESCHREIBUNG'];
        $this->erf_datum_mysql = $row['ERFASSUNGSDATUM'];
        $this->erf_datum = date_mysql2german($row['ERFASSUNGSDATUM']);
        $this->st_benutzername = $row['name'];
        $this->stundensatz = $row['hourly_rate'];
        $this->anzahl_eigene_zettel = $this->anzahl_e_zettel($this->st_benutzer_id);
        $this->stunden_pro_woche = $row['hours_per_week'];
        $this->gesamt_azeit_min = $this->gesamt_azeit_min($this->st_benutzer_id);
        $this->gesamt_azeit_std = $this->min2std($this->gesamt_azeit_min);
        $this->gesamt_soll_stunden = $this->stunden_pro_woche * $this->anzahl_eigene_zettel;
    }

    function stundenzettelleistungen_in_arr($zettel_id)
    {
        $result = DB::select("SELECT ST_DAT, ST_ID, ZETTEL_ID, DATUM, LEISTUNG_ID, DAUER_MIN, KOSTENTRAEGER_TYP, KOSTENTRAEGER_ID, HINWEIS, BEGINN, ENDE, LEISTUNGSKATALOG.BEZEICHNUNG FROM STUNDENZETTEL_POS JOIN LEISTUNGSKATALOG ON (STUNDENZETTEL_POS.LEISTUNG_ID = LEISTUNGSKATALOG.LK_ID) WHERE ZETTEL_ID='$zettel_id' && STUNDENZETTEL_POS.AKTUELL = '1' ORDER BY  DATUM, ST_ID ASC");
        return $result;
    }

    function zeit2decimal($zeit)
    {
        $zeit_arr = explode(':', $zeit);
        $std = $zeit_arr [0];
        $min = $zeit_arr [1];
        $g_min = ($std * 60) + $min;
        $g_std = $g_min / 60;
        return $g_std;
    }

    function decimal2zeit($decimal)
    {
        $minuten = $decimal * 60;
        $zeit = $this->min2std($minuten);
        return $zeit;
    }

    function stundenzettel_anlegen($benutzer_id)
    {
        $f = new formular ();
        $f->erstelle_formular("Neuer Stundenzettel", NULL);
        $f->fieldset("Neuen Stundenzettel anlegen", 'z_anlegen');
        $f->hidden_feld("benutzer_id", "$benutzer_id");
        $f->text_feld("Überschrift (KW)", "beschreibung", "", "50", 'beschreibung', '');
        $f->send_button("zettel_anlegen", "Stundenzettel anlegen");
        $f->hidden_feld("option", "zettel_anlegen");
        $f->fieldset_ende();
        $f->ende_formular();
    }

    function stundenzettel_speichern($benutzer_id, $beschreibung)
    {
        $datum = date("Y-m-d");
        $l_zettel_id = $this->letzte_zettel_id() + 1;
        $db_abfrage = "INSERT INTO STUNDENZETTEL VALUES (NULL, '$l_zettel_id', '$benutzer_id', '$beschreibung', '$datum',  '1')";
        DB::insert($db_abfrage);

        /* Protokollieren */
        $last_dat = DB::getPdo()->lastInsertId();
        protokollieren('STUNDENZETTEL', $last_dat, '0');
        $mein_letzerzettel_id = $this->mein_letzer_zettel($benutzer_id);
        hinweis_ausgeben('Stundennachweis wurde gespeichert!<br>Sie werden weitergeleitet.');
        weiterleiten_in_sec(route('web::zeiterfassung::legacy', ['option' => 'zettel_eingabe', 'zettel_id' => $mein_letzerzettel_id]), 2);
    }

    function letzte_zettel_id()
    {
        $result = DB::select("SELECT ZETTEL_ID FROM STUNDENZETTEL WHERE AKTUELL = '1' ORDER BY ZETTEL_ID DESC LIMIT 0,1");
        $row = $result[0];
        return $row ['ZETTEL_ID'];
    }

    function mein_letzer_zettel($benutzer_id)
    {
        $result = DB::select("SELECT ZETTEL_ID FROM STUNDENZETTEL WHERE BENUTZER_ID='$benutzer_id' && AKTUELL = '1' ORDER BY ZETTEL_ID DESC LIMIT 0,1");
        $row = $result[0];
        return $row ['ZETTEL_ID'];
    }

    function leistung_in_katalog($datum, $benutzer_id, $leistungs_beschreibung, $zettel_id, $dauer_min, $kostentraeger_typ, $kostentraeger_bez, $hinweis, $beginn, $ende)
    {
        // echo "$datum, $benutzer_id, $leistungs_beschreibung, $zettel_id, $dauer_min, $kostentraeger_typ, $kostentraeger_bez GW $this->gewerk_id ";
        $lk_id = $this->letzte_leistung_id() + 1;
        $datum = date_german2mysql($datum);
        $bb = new benutzer ();
        $bb->get_benutzer_infos($benutzer_id);
        $benutzer_name = $bb->benutzername;
        $datum_d = date_mysql2german($datum);
        $leistungs_beschreibung = "$datum_d $beginn-$ende Uhr $benutzer_name - $leistungs_beschreibung";
        $db_abfrage = "INSERT INTO LEISTUNGSKATALOG VALUES (NULL, '$lk_id', '$leistungs_beschreibung', '$this->gewerk_id',  '1')";
        DB::insert($db_abfrage);
        /* Protokollieren */
        $last_dat = DB::getPdo()->lastInsertId();
        if ($last_dat) {
            protokollieren('LEISTUNGSKATALOG', $last_dat, '0');

            /* Leistung ins Lieferkatalog speichern */
            $bp_partner_id = $this->get_partner_id_benutzer($benutzer_id);
            $artikel_nr = 'L-' . $benutzer_id . '-' . $lk_id;
            $artikel_preis = $this->stundensatz($benutzer_id);

            $r = new rechnung ();

            $r->artikel_leistung_mit_artikelnr_speichern($bp_partner_id, $leistungs_beschreibung, $artikel_preis, $artikel_nr, '0', 'Std', '19', '0.00');

            // $r->artikel_leistung_speichern($bp_partner_id, $leistungs_beschreibung, $artikel_preis, '0', 'Std', '19');

            $zugewiesene_l_id = $this->get_leistung_id_by_beschr($this->gewerk_id, $leistungs_beschreibung);
            $datum = date_mysql2german($datum); // weil die nachfolgende funktion deutsches datumsformat erwartet
            $this->zettel_pos_speichern($datum, $benutzer_id, $zugewiesene_l_id, $zettel_id, $dauer_min, $kostentraeger_typ, $kostentraeger_bez, $hinweis, $beginn, $ende);
            weiterleiten_in_sec(route('web::zeiterfassung::legacy', ['option' => 'zettel_eingabe', 'zettel_id' => $zettel_id]), 1);
        } else {
            hinweis_ausgeben("Leistungsbeschreibung zu lang, max 160 zeichen");
        }
    }

    function letzte_leistung_id()
    {
        $result = DB::select("SELECT LK_ID FROM LEISTUNGSKATALOG WHERE AKTUELL = '1' ORDER BY LK_ID DESC LIMIT 0,1");
        $row = $result[0];
        return $row ['LK_ID'];
    }

    function stundensatz($benutzer_id)
    {
        $result = DB::select("SELECT hourly_rate FROM persons JOIN jobs ON (persons.id = jobs.employee_id) WHERE persons.id = ? ORDER BY persons.id DESC LIMIT 0,1", [$benutzer_id]);
        return !empty($result) ? $result[0]['hourly_rate'] : 0;
    }

    function get_leistung_id_by_beschr($gewerk_id, $beschreibung)
    {
        $result = DB::select("SELECT LK_ID FROM LEISTUNGSKATALOG WHERE GEWERK='$gewerk_id' && BEZEICHNUNG='$beschreibung' && AKTUELL = '1' ORDER BY LK_ID DESC LIMIT 0,1");
        $row = $result[0];
        return $row ['LK_ID'];
    }

    function zettel_pos_speichern($datum, $benutzer_id, $leistung_id, $zettel_id, $dauer_min, $kostentraeger_typ, $kostentraeger_bez, $hinweis, $beginn, $ende)
    {
        $l_id = $this->letzte_zettel_pos_id() + 1;
        $b = new buchen ();
        $kostentraeger_id = $b->kostentraeger_id_ermitteln($kostentraeger_typ, $kostentraeger_bez);
        $datum = date_german2mysql($datum);
        $db_abfrage = "INSERT INTO STUNDENZETTEL_POS VALUES (NULL, '$l_id', '$zettel_id', '$datum', '$beginn', '$ende', '$leistung_id', '$dauer_min', '$kostentraeger_typ', '$kostentraeger_id','$hinweis', '0', '1')";
        DB::insert($db_abfrage);

        /* Protokollieren */
        $last_dat = DB::getPdo()->lastInsertId();
        protokollieren('STUNDENZETTEL_POS', $last_dat, '0');

        // hinweis_ausgeben('Ihre Eingabe wurde gespeichert!<br>Sie werden weitergeleitet.');

        /* Prüfen ob Leistung in POSITIONEN_KATALOG existiert, falls nicht, hinzufügen */
        if (!$this->check_leistung_pos_leistung($benutzer_id, $leistung_id)) {
            $r = new rechnung ();

            /* Leistung ins Lieferkatalog speichern */
            $bp_partner_id = $this->get_partner_id_benutzer($benutzer_id);
            $artikel_nr = 'L-' . $benutzer_id . '-' . $leistung_id;
            $artikel_preis = $this->stundensatz($benutzer_id);
            $leistungs_beschreibung = $this->get_beschr_by_l_id($leistung_id);
            $r->artikel_leistung_mit_artikelnr_speichern($bp_partner_id, $leistungs_beschreibung, $artikel_preis, $artikel_nr, '0', 'Std', '19', '0.00');
        }

        weiterleiten(route('web::zeiterfassung::legacy', ['option' => 'zettel_eingabe', 'zettel_id' => $zettel_id], false));
    }

    function letzte_zettel_pos_id()
    {
        $result = DB::select("SELECT ST_ID FROM STUNDENZETTEL_POS WHERE AKTUELL = '1' ORDER BY ST_ID DESC LIMIT 0,1");

        $row = $result[0];
        return $row ['ST_ID'];
    }

    function check_leistung_pos_leistung($benutzer_id, $leistung_id)
    {
        $artikel_nr = 'L-' . $benutzer_id . '-' . $leistung_id;
        $result = DB::select("SELECT ARTIKEL_NR FROM POSITIONEN_KATALOG WHERE ARTIKEL_NR='$artikel_nr' && AKTUELL = '1'");
        return !empty($result);
    }

    function get_beschr_by_l_id($leistung_id)
    {
        $result = DB::select("SELECT BEZEICHNUNG FROM LEISTUNGSKATALOG WHERE LK_ID='$leistung_id' && AKTUELL = '1' ORDER BY LK_ID DESC LIMIT 0,1");
        $row = $result[0];
        return $row ['BEZEICHNUNG'];
    }

    // ###ZEIT METHODEN#########

    /* 20:150 stdunden und min */

    function zettel2beleg($zettel_id)
    {
        $this->stundenzettel_grunddaten($zettel_id);
        /*
		 * $this->st_benutzer_id = $row['BENUTZER_ID'];
		 * $this->beschreibung = $row['BESCHREIBUNG'];
		 * $this->erf_datum_mysql = $row['ERFASSUNGSDATUM'];
		 * $this->erf_datum = date_mysql2german($row['ERFASSUNGSDATUM']);
		 * $this->st_benutzername = $row['benutzername'];
		 * $this->stundensatz = $row['STUNDENSATZ'];
		 */
        $bp_partner_id = $this->get_partner_id_benutzer($this->st_benutzer_id);

        $r = new rechnung ();
        $clean_arr ['RECHNUNG_EMPFAENGER_TYP'] = 'Partner';
        $clean_arr ['RECHNUNG_EMPFAENGER_ID'] = $bp_partner_id;
        $clean_arr ['RECHNUNG_AUSSTELLER_TYP'] = 'Partner';
        $clean_arr ['RECHNUNG_AUSSTELLER_ID'] = $bp_partner_id;

        $clean_arr ['RECHNUNGSDATUM'] = date("d.m.Y");
        $clean_arr ['RECHNUNG_FAELLIG_AM'] = date("d.m.Y");
        $clean_arr ['kurzbeschreibung'] = "Beleg vom Stundennachweis $zettel_id $this->beschreibung $this->st_benutzername";

        $g_zeit_zettel = $this->gzeit_zettel($zettel_id);
        $g_stunden = $g_zeit_zettel / 60;
        $netto_betrag = $this->stundensatz * $g_stunden;
        $clean_arr ['nettobetrag'] = $netto_betrag;

        $brutto_betrag = ($netto_betrag / 100) * 119;
        $clean_arr ['bruttobetrag'] = $brutto_betrag;

        $clean_arr ['skonto'] = '0';

        $gk = new geldkonto_info ();
        $gk->geld_konto_ermitteln('Partner', $bp_partner_id);
        $clean_arr ['EMPFANGS_GELD_KONTO'] = $gk->geldkonto_id;
        $l_erf_nr = $r->auto_rechnung_speichern($clean_arr);
        /* Beleg vom Arbeitgeber an Arbeitgebern gespeichert, nur Grunddaten */
        // echo "<b>LBLG $l_erf_nr</b>";

        $leistungen_arr = $this->stundenzettelleistungen_in_arr($zettel_id);
        $anzahl_leistungen = count($leistungen_arr);

        for ($a = 0; $a < $anzahl_leistungen; $a++) {
            $leistung_id = $leistungen_arr [$a] ['LEISTUNG_ID'];
            $dauer_min = $leistungen_arr [$a] ['DAUER_MIN'];
            $menge = $dauer_min / 60;
            $preis = $this->stundensatz;
            $artikel_nr = 'L-' . $this->st_benutzer_id . '-' . $leistung_id;
            $mwst = 19;
            $rabatt = '0';
            $this->position_speichern($l_erf_nr, $bp_partner_id, $artikel_nr, $menge, $preis, $mwst, $rabatt);

            $st_dat = $leistungen_arr [$a] ['ST_DAT'];
            $this->zettel_pos_in_rg($st_dat, $l_erf_nr);
        }
    }

    function position_speichern($beleg_nr, $lieferant_id, $artikel_nr, $menge, $preis, $mwst, $rabatt)
    {
        $r = new rechnung ();
        $letzte_rech_pos_id = $r->get_last_rechnung_pos_id();
        $letzte_rech_pos_id = $letzte_rech_pos_id + 1;

        $r2 = new rechnungen ();
        $last_pos = $r2->rechnung_last_position($beleg_nr);
        $last_pos = $last_pos + 1;
        $g_netto = $menge * $preis;

        $db_abfrage = "INSERT INTO RECHNUNGEN_POSITIONEN VALUES (NULL, '$letzte_rech_pos_id', '$last_pos', '$beleg_nr','$beleg_nr','$lieferant_id','$artikel_nr', '$menge','$preis','$mwst', '$rabatt', '0.00', '$g_netto','1')";

        DB::insert($db_abfrage);
    }

    function zettel_pos_in_rg($st_dat, $erf_nr)
    {
        $db_abfrage = "UPDATE STUNDENZETTEL_POS SET IN_BELEG='$erf_nr' WHERE ST_DAT='$st_dat'";
        DB::update($db_abfrage);
    }

    function zettel2pdf($id)
    {
        $benutzer_id = $this->get_userid($id);
        $fehler = 0;
        if ($benutzer_id != Auth::user()->id) {
            if (!Auth::user()->hasAnyRole([
                \App\Libraries\Role::ROLE_BUCHHALTER,
                \App\Libraries\Role::ROLE_ADMINISTRATOR
            ])
            ) {
                $fehler = 1;
            }
        }

        if ($fehler == 1) {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\ErrorMessage('Keine Berechtigung')
            );
        }

        ob_end_clean(); // ausgabepuffer leeren
        $pdf = new Cezpdf ('a4', 'portrait');
        $bpdf = new b_pdf ();

        $this->stundenzettel_grunddaten($id);
        $this->bp_partner_id = $this->get_partner_id_benutzer($this->st_benutzer_id);
        $bpdf->b_header($pdf, 'Partner', $this->bp_partner_id, 'portrait', 'Helvetica.afm', 6);
        
        $p = new partners ();
        $p->get_partner_name($this->bp_partner_id);
        $this->partner_name = $p->partner_name;
        $pdf->ezSetMargins(135, 70, 50, 50);
        $pdf->ezText("<b>Arbeitszeitnachweis $this->beschreibung vom $this->erf_datum</b> \nErfasst von: <b>$this->st_benutzername</b> \nMitarbeiter von $this->partner_name", 9, array(
            'left' => '10'
        ));
        $pdf->ezSetDy(-20); // abstand

        $stundenzettel_pos_arr = $this->stundenzettelleistungen_in_arr($id);
        if (empty($stundenzettel_pos_arr)) {
            $pdf->ezText("<b>Stundenzettel enthält keine Daten</b>", 10, array(
                'left' => '10'
            ));
        } else {
            $anzahl_pos = count($stundenzettel_pos_arr);
            $cols = array(
                'ZEILE' => "Zeile",
                'DATUM' => "Datum",
                'BEGINN' => "Beginn",
                'ENDE' => "Ende",
                'KOS_BEZ' => "Bezeichnung",
                'LEISTUNG' => "Leistung",
                'DAUER' => "Dauer"
            );

            $gesamt_min = 0;
            for ($a = 0; $a < $anzahl_pos; $a++) {
                $zeile = $a + 1;
                $beschreibung = $stundenzettel_pos_arr [$a] ['BEZEICHNUNG'];
                $datum = date_mysql2german($stundenzettel_pos_arr [$a] ['DATUM']);
                $kostentraeger_typ = $stundenzettel_pos_arr [$a] ['KOSTENTRAEGER_TYP'];
                $kostentraeger_id = $stundenzettel_pos_arr [$a] ['KOSTENTRAEGER_ID'];
                $dauer_min = $stundenzettel_pos_arr [$a] ['DAUER_MIN'];
                $gesamt_min = $gesamt_min + $dauer_min;
                $hinweis = $stundenzettel_pos_arr [$a] ['HINWEIS'];
                $beginn = $stundenzettel_pos_arr [$a] ['BEGINN'];
                $ende = $stundenzettel_pos_arr [$a] ['ENDE'];

                $r = new rechnung ();
                $kostentraeger_bez = $r->kostentraeger_ermitteln($kostentraeger_typ, $kostentraeger_id);
                $kostentraeger_bez = bereinige_string($kostentraeger_bez);
                $dauer_std = $this->min2std($dauer_min);

                $table_arr [$a] ['ZEILE'] = $zeile;

                /* urlaub oder krank */
                $datum_mysql = $stundenzettel_pos_arr [$a] ['DATUM'];
                $u = new urlaub ();
                $status = $u->check_anwesenheit($benutzer_id, $datum_mysql);
                if (empty ($status)) {
                    $table_arr [$a] ['DATUM'] = $datum;
                } else {
                    if ($benutzer_id != Auth::user()->id) {
                        $status_k = substr($status, 0, 1);
                        $table_arr [$a] ['DATUM'] = "<b>$datum ($status_k)</b>";
                    } else {
                        $table_arr [$a] ['DATUM'] = $datum;
                    }
                }
                $table_arr [$a] ['KOS_BEZ'] = $kostentraeger_bez;
                if (empty ($hinweis)) {
                    $table_arr [$a] ['LEISTUNG'] = $beschreibung;
                } else {
                    $table_arr [$a] ['LEISTUNG'] = "$beschreibung\n<i><b>$hinweis</b></i>";
                }
                $table_arr [$a] ['DAUER'] = "$dauer_std Std. ($dauer_min Min.)";
                $table_arr [$a] ['BEGINN'] = "$beginn";
                $table_arr [$a] ['ENDE'] = "$ende";
            }

            $stunden_woche = nummer_punkt2komma($this->stunden_pro_woche);
            $stunden_woche_soll = $stunden_woche * 60; // std x min
            $stundengesamt = $gesamt_min / 60;
            $stunden_voll = intval($stundengesamt);
            $saldo_woche_min = $stunden_woche_soll - $gesamt_min;
            $saldo_woche_std = intval($saldo_woche_min / 60);
            $rest_std_in_min = $saldo_woche_std * 60;
            $restsaldo_min = $saldo_woche_min - $rest_std_in_min;

            if ($gesamt_min < $stunden_woche_soll) {
                $saldo_woche_std = '-' . $saldo_woche_std;
            } else {
                $saldo_woche_std = abs($saldo_woche_std);
            }

            if ($restsaldo_min < 0) {
                $restsaldo_min = abs($restsaldo_min);
            }

            $arbeitsdauer = $this->min2std($gesamt_min);

            $restsaldo_min = sprintf("%02d", $restsaldo_min);

            $g_ist_arbeitsdauer = $this->zeit2decimal($this->gesamt_azeit_std); // =80
            $g_soll_arbeitsdauer = $this->zeit2decimal($this->gesamt_soll_stunden);
            $stundenkonto_in_std_dec = $g_ist_arbeitsdauer - $g_soll_arbeitsdauer;

            $table_arr [$a + 1] ['DAUER'] = "";
            $table_arr [$a + 2] ['LEISTUNG'] = "<b>Gesamt/Woche</b>";
            $table_arr [$a + 2] ['DAUER'] = "<b>$arbeitsdauer</b>";
            $table_arr [$a + 3] ['LEISTUNG'] = "<b>Soll/Woche</b>";
            $table_arr [$a + 3] ['DAUER'] = "<b>$stunden_woche Stunden</b>";
            $table_arr [$a + 4] ['LEISTUNG'] = "<b>Überstunden/Woche</b>";
            $table_arr [$a + 4] ['DAUER'] = "<b>$saldo_woche_std:$restsaldo_min</b>";

            $table_arr [$a + 5] ['LEISTUNG'] = "";
            $table_arr [$a + 5] ['DAUER'] = "";

            $pdf->ezTable($table_arr, $cols, "Stundennachweis $jahr", array(
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
                'fontSize' => 8,
                'xPos' => 50,
                'xOrientation' => 'right',
                'width' => 500,
                'cols' => array(
                    'ZEILE' => array(
                        'justification' => 'right',
                        'width' => 30
                    )
                )
            ));
            $pdf->ezStream();
        }
    }

    function mitarbeiter_auswahl($ex = 0)
    {
        $benutzer_arr = $this->mitarbeiter_arr($ex);
        if ($ex == 0) {
            echo "<a href='" . route('web::zeiterfassung::legacy', ['option' => 'stundennachweise_ex']) . "'>Ex-Mitarbeiter</a>";
        } else {
            echo "<a href='" . route('web::zeiterfassung::legacy', ['option' => 'stundennachweise']) . "'>Aktuelle Mitarbeiter</a>";
        }

        $partner_name = '';
        echo "<table class='striped'>";
        $z = 0;
        foreach ($benutzer_arr as $user) {
            $z++;
            $benutzer_id = $user['id'];
            $benutzername = $user['name'];
            $this->BP_PARTNER_ID = $user['BP_PARTNER_ID'];
            $p = new partners ();
            $p->get_partner_name($this->BP_PARTNER_ID);
            if ($partner_name != $p->partner_name) {
                $partner_name = $p->partner_name;
                echo "<tr class=\"feldernamen\"><td>$partner_name</td><td></td><td></td><td></td></tr>";
                echo "<tr class=\"feldernamen\"><td>Mitarbeiter</td><td>Gesamt</td><td>Belege</td><td>Offen</td></tr>";
                // echo "<br>".strtoupper($partner_name)."<br><br>";
            }
            $anzahl_zettel_gesamt = $this->anzahl_zettel_mitarbeiter($benutzer_id);
            if ($anzahl_zettel_gesamt > 0) {
                $anzahl_offene_zettel = $this->anzahl_offene_zettel($benutzer_id);
                $anzahl_in_beleg = $anzahl_zettel_gesamt - $anzahl_offene_zettel;
            } else {
                $anzahl_offene_zettel = 0;
                $anzahl_in_beleg = 0;
            }
            $link = "<a href='" . route('web::zeiterfassung::legacy', ['option' => 'nachweisliste', 'mitarbeiter_id' => $benutzer_id]) . "'><b> $benutzername</b></a>";

            if ($anzahl_offene_zettel > 0) {
                $o = "<b>$anzahl_offene_zettel </b>";
            } else {
                $o = "Keine";
            }
            echo "<tr class=\"zeile$z\"><td>$link</td><td>$anzahl_zettel_gesamt</td><td>$anzahl_in_beleg</td><td>$o</td></tr>";
            // echo "$link | G: $anzahl_zettel_gesamt |B: $anzahl_in_beleg | $o <hr>";
            if ($z == 2) {
                $z = 0;
            }
        }
        echo "</table>";
    }

    function mitarbeiter_arr($ex = 0)
    {
        $datum_h = date("Y-m-d");
        if ($ex == 0) {
            $result = DB::select("SELECT persons.id, persons.name, jobs.employer_id FROM persons JOIN jobs ON (persons.id=jobs.employer_id) WHERE jobs.leave_date > ? OR jobs.leave_date IS NULL GROUP BY persons.id ORDER BY jobs.employer_id, persons.name ASC", [$datum_h]);
        } else {
            $result = DB::select("SELECT persons.id, persons.name, jobs.employer_id FROM persons JOIN jobs ON (persons.id=jobs.employer_id) GROUP BY persons.id ORDER BY jobs.employer_id, persons.name ASC");
        }
        return $result;
    }

    function anzahl_zettel_mitarbeiter($benutzer_id)
    {
        $result = DB::select("SELECT count(*) AS ANZAHL FROM STUNDENZETTEL WHERE BENUTZER_ID='$benutzer_id'  && AKTUELL='1'");
        return $result[0]['ANZAHL'];
    }

    function anzahl_offene_zettel($benutzer_id)
    {
        $eig_stunden_zettel = $this->stundenzettel_in_arr($benutzer_id);
        $anzahl = count($eig_stunden_zettel);
        $g_offen = 0;
        if ($anzahl > 0) {
            for ($a = 0; $a < $anzahl; $a++) {
                $zettel_id = $eig_stunden_zettel [$a] ['ZETTEL_ID'];
                // echo "$benutzer_id --> $zettel_id<br>";
                if (!$this->check_if_beleg_erstellt($zettel_id)) {
                    // echo "$benutzer_id O: $zettel_id<br>";
                    $g_offen++;
                }
            }
        }
        return $g_offen;
    }

    function nachweisliste($mitarbeiter_id)
    {
        $benutzer_name = $this->get_benutzer_name($mitarbeiter_id);
        // echo "<b>Nachweisliste von $benutzer_name</b></hr>";
        $f = new formular ();
        $f->fieldset("Stundennachweise", 'z_anlegen');
        $this->benutzer_id = Auth::user()->id;
        $eigene_zettel_arr = $this->stundenzettel_in_arr($mitarbeiter_id);
        if (empty($eigene_zettel_arr)) {
            echo "&nbsp;$benutzer_name hat keine Stundennachweise";
        } else {
            $anzahl_stundenzettel = count($eigene_zettel_arr);
            echo "<b>&nbsp;&nbsp;$benutzer_name hat $anzahl_stundenzettel Stundennachweise</b><br><br>";
            echo "<b>NR.&nbsp;&nbsp; DATUM&nbsp;&nbsp;&nbsp;  BESCHREIBUNG</b><br><br>";
            echo "<table>";
            // echo "<tr class=\"feldernamen\"><td>Nr.</td><td>Datum</td><td>Beschreibung</td><td>Optionen</td></tr>";
            echo "<tr>";
            echo "<th>Nr.</th>";
            echo "<th>Datum</th>";
            echo "<th>Beschreibung</th>";
            echo "<th>Optionen</th>";
            echo "</tr>";
            $z = 0;
            for ($a = 0; $a < $anzahl_stundenzettel; $a++) {
                $z++;
                $zeile = $a + 1;
                $zettel_id = $eigene_zettel_arr [$a] ['ZETTEL_ID'];
                if ($this->check_if_beleg_erstellt($zettel_id)) {
                    $link_stundenzettel_del = "<a href='" . route('web::zeiterfassung::legacy', ['option' => 'zettel_loeschen', 'zettel_id' => $zettel_id]) . "'>Löschen</a>";
                } else {
                    $link_stundenzettel_del = '';
                }
                $link_stundenzettel_ansehen = "<a href='" . route('web::zeiterfassung::legacy', ['option' => 'zettel_ansehen', 'zettel_id' => $zettel_id]) . "'>Ansehen</a>";
                $link_pdf = "<a href='" . route('web::zeiterfassung::legacy', ['option' => 'zettel2pdf', 'zettel_id' => $zettel_id]) . "'>PDF-Ansicht</a>";

                if ($this->check_if_beleg_erstellt($zettel_id)) {
                    $_beleg_arr = $this->get_beleg_id_erstellt($zettel_id);
                    if (!empty($_beleg_arr)) {
                        $anz = count($_beleg_arr);
                        // $link_zettel2beleg = "Beleg $anz Mal erstellt. ";
                        $link_zettel2beleg = "";
                        for ($g = 0; $g < $anz; $g++) {
                            $in_belegnr = $_beleg_arr [$g] ['IN_BELEG'];
                            $link_zettel2beleg .= "<a href='" . route('web::rechnungen::legacy', ['option' => 'rechnungs_uebersicht', 'belegnr' => $in_belegnr]) . "'><b>BELEG ERSTELLT</b></a><br>";
                        }

                        $anzahl_pos_in_zettel = $this->anzahl_pos_zettel($zettel_id);
                        if (empty ($anzahl_pos_in_zettel)) {
                            $link_zettel2beleg = "Stundennachweis leer";
                        }
                    }
                } else {
                    $link_zettel2beleg = "<a href='" . route('web::zeiterfassung::legacy', ['option' => 'zettel_zu_beleg', 'zettel_id' => $zettel_id]) . "'>BELEG ERSTELLEN</a>";
                }

                $beschreibung = $eigene_zettel_arr [$a] ['BESCHREIBUNG'];
                $datum = date_mysql2german($eigene_zettel_arr [$a] ['ERFASSUNGSDATUM']);
                echo "<tr class=\"zeile$z\"><td>$zeile.</td><td>$datum</td><td>$beschreibung</td><td>$link_stundenzettel_ansehen $link_pdf $link_stundenzettel_del | $link_zettel2beleg</td></tr>";
                // echo "$zeile. $datum $beschreibung $link_stundenzettel_ansehen $link_pdf $link_zettel2beleg<br>";
                if ($z == 2) {
                    $z = 0;
                }
            }
            echo "</table>";
        }
        $this->urlaubstage_offen_tab($mitarbeiter_id);
        echo '<br>';
        $this->urlaubstage_eingetragen_tab($mitarbeiter_id);
        $f->fieldset_ende();
    }

    function get_benutzer_name($benutzer_id)
    {
        $user = \App\Models\Person::find($benutzer_id);
        return !empty($user) ? $user->name : '';
    }

    function get_beleg_id_erstellt($zettel_id)
    {
        $result = DB::select("SELECT IN_BELEG FROM STUNDENZETTEL_POS WHERE ZETTEL_ID='$zettel_id' && (IN_BELEG != '0' OR IN_BELEG != NULL) && AKTUELL='1' GROUP BY IN_BELEG");
        return $result;
    }

    function urlaubstage_offen_tab($benutzer_id)
    {
        $url_tage = $this->get_tage_urlaub_offen_arr($benutzer_id); // aktuelles jahr
        $anz = count($url_tage);
        if ($anz) {

            $f = new formular ();
            if (!request()->has('tage')) {
                echo "<table>";
                echo "<tr class=\"feldernamen\"><td colspan=\"3\">$anz URLAUBSTAGE DIE NICHT EINGETRAGEN SIND</td></tr>";
                echo "<tr class=\"feldernamen\"><td width=\"60\">TAG</td><td width=\"80\">DATUM</td><td>ANTEIL</td></tr>";
                $z = 0;
                for ($a = 0; $a < $anz; $a++) {
                    $z++;
                    $datum = date_mysql2german($url_tage [$a] ['DATUM']);
                    $anteil = $url_tage [$a] ['ANTEIL'];
                    echo "<tr><td>";
                    $js = "onchange=\"count_auswahl(this, 5)\"";
                    $f->check_box_js("tage[]", $datum, 'Auswahl', $js, '');
                    echo "</td><td>$datum</td><td>$anteil Tag (-e)</td></tr>";
                    // echo "$datum $anteil<br>";
                }
                echo "<tr><td width=\"60\"></td><td width=\"80\">";
                $f->send_button_js('erstellen', 'STUNDENZETTEL ERSTELLEN', '');
                echo "</td><td></td></tr>";
                echo "</table>";
            }

            if (request()->has('tage')) {
                if (request()->has('speichern')) {
                    echo '<pre>';
                    $this->urlaub2zettel(request()->input('benutzer_id'), request()->input('beschreibung'), request()->input('tage'));
                    weiterleiten(route('web::zeiterfassung::legacy', ['option' => 'nachweisliste', 'mitarbeiter_id' => $benutzer_id], false));
                }
                if (request()->has('erstellen')) {
                    $f->hidden_feld('benutzer_id', $benutzer_id);
                    $anzahl = count(request()->input('tage'));
                    $f->text_feld('Beschreung Stundenzettel (z.B. KW)', 'beschreibung', '', 50, '', '');
                    for ($a = 0; $a < $anzahl; $a++) {
                        $datum = date_german2mysql(request()->input('tage') [$a]);
                        $f->check_box_js("tage[]", $datum, request()->input('tage') [$a], '', 'checked');
                    }
                    $f->send_button_js('speichern', 'STUNDENZETTEL SPEICHERN', '');
                }
            }
        } else {
            echo 'Keine urlaubstage die nicht in dem Stundenzettel eingetragen sind';
        }
    }

    function get_tage_urlaub_offen_arr($benutzer_id)
    {
        $db_abfrage = "SELECT * FROM `URLAUB` WHERE `BENUTZER_ID` = '$benutzer_id' AND `AKTUELL` = '1' && DATE_FORMAT( DATUM, '%Y' ) = YEAR( NOW( ) ) && DATUM NOT IN (
SELECT DATUM
FROM `STUNDENZETTEL` , STUNDENZETTEL_POS
WHERE `BENUTZER_ID` = '$benutzer_id'
AND STUNDENZETTEL.AKTUELL = '1' && STUNDENZETTEL_POS.AKTUELL = '1' && STUNDENZETTEL.ZETTEL_ID = STUNDENZETTEL_POS.ZETTEL_ID
)
ORDER BY `URLAUB`.`DATUM` ASC";

        $result = DB::select($db_abfrage);
        if (!empty($result)) {
            return $result;
        }
    }

    function urlaub2zettel($benutzer_id, $beschreibung, $tage_arr)
    {
        $anz = count($tage_arr);
        if ($anz) {
            $datum = date("Y-m-d");
            $l_zettel_id = $this->letzte_zettel_id() + 1;
            $db_abfrage = "INSERT INTO STUNDENZETTEL VALUES (NULL, '$l_zettel_id', '$benutzer_id', '$beschreibung', '$datum',  '1')";
            DB::insert($db_abfrage);

            /* Protokollieren */
            $last_dat = DB::getPdo()->lastInsertId();
            protokollieren('STUNDENZETTEL', $last_dat, '0');
            $zettel_id = $this->mein_letzer_zettel($benutzer_id);

            // ##POSITIONEN###

            for ($a = 0; $a < $anz; $a++) {
                $leistung_id = 18;
                $l_id = $this->letzte_zettel_pos_id() + 1;
                $datum = $tage_arr [$a];
                $u = new urlaub ();
                $anteil = $u->anteil_datum($datum);
                if ($anteil == '1.0') {
                    $dauer_min = 8 * 60;
                }
                if ($anteil == '0.5') {
                    $dauer_min = 4 * 60;
                }
                $hinweis = "Erstellt von " . Auth::user()->email . " aus Urlaubsdaten.";
                $db_abfrage = "INSERT INTO STUNDENZETTEL_POS VALUES (NULL, '$l_id', '$zettel_id', '$datum', '', '', '$leistung_id', '$dauer_min', 'Objekt', '1','$hinweis', '0', '1')";
                DB::insert($db_abfrage);

                /* Protokollieren */
                $last_dat = DB::getPdo()->lastInsertId();
                protokollieren('STUNDENZETTEL_POS', $last_dat, '0');
            }
        } else {
            echo "Keine Tage gewählt, Stundenzettel wurde nicht erstellt";
        }
    }

    function urlaubstage_eingetragen_tab($benutzer_id)
    {
        $url_tage = $this->get_tage_urlaub_eingetragen_arr($benutzer_id); // aktuelles jahr
        $anz = count($url_tage);
        if ($anz) {
            echo "<table>";
            echo "<tr class=\"feldernamen\"><td colspan=\"4\">$anz URLAUBSTAGE DIE EINGETRAGEN SIND</td></tr>";
            echo "<tr class=\"feldernamen\"><td>TAG</td><td>DATUM</td><td>ANTEIL</td><td>EINGETRAGEN</td></tr>";
            $z = 0;
            for ($a = 0; $a < $anz; $a++) {
                $z++;
                $datum = date_mysql2german($url_tage [$a] ['DATUM']);
                $datum_mysql = date_german2mysql($datum);
                $anteil = $url_tage [$a] ['ANTEIL'];
                $this->get_zettel_infos_of($datum_mysql, $benutzer_id);
                $link_stundenzettel_ansehen = "<a href='" . route('web::zeiterfassung::legacy', ['option' => 'zettel_ansehen', 'zettel_id' => $this->z_zettel_id], false) . "'>Eingetragen in $this->z_beschreibung</a>";

                echo "<tr><td>$z</td><td>$datum</td><td>$anteil Tag (-e)</td><td>$link_stundenzettel_ansehen</td></tr>";
            }
            echo "</table>";
        } else {
            echo 'Keine urlaubstage die in den Stundenzetteln eingetragen sind';
        }
    }

    function get_tage_urlaub_eingetragen_arr($benutzer_id)
    {
        $db_abfrage = "SELECT * FROM `URLAUB` WHERE `BENUTZER_ID` = '$benutzer_id' AND `AKTUELL` = '1' && DATE_FORMAT( DATUM, '%Y' ) = YEAR( NOW( ) ) && DATUM  IN (
SELECT DATUM
FROM `STUNDENZETTEL` , STUNDENZETTEL_POS
WHERE `BENUTZER_ID` = '$benutzer_id'
AND STUNDENZETTEL.AKTUELL = '1' && STUNDENZETTEL_POS.AKTUELL = '1' && STUNDENZETTEL.ZETTEL_ID = STUNDENZETTEL_POS.ZETTEL_ID
)
ORDER BY `URLAUB`.`DATUM` ASC";

        $result = DB::select($db_abfrage);
        if (!empty($result)) {
            return $result;
        }
    }

    function get_zettel_infos_of($datum, $benutzer_id)
    {
        $db_abfrage = " SELECT STUNDENZETTEL_POS.ZETTEL_ID, STUNDENZETTEL.BESCHREIBUNG FROM `STUNDENZETTEL_POS` , STUNDENZETTEL
WHERE `DATUM` = '$datum' AND STUNDENZETTEL_POS.`AKTUELL` = '1' && STUNDENZETTEL.`AKTUELL` = '1' && STUNDENZETTEL.ZETTEL_ID = STUNDENZETTEL_POS.ZETTEL_ID && STUNDENZETTEL.BENUTZER_ID = '$benutzer_id'
LIMIT 0 , 1";

        $result = DB::select($db_abfrage);
        unset ($this->z_beschreibung);
        unset ($this->z_zettel_id);
        if (!empty($result)) {
            $row = $result[0];
            $this->z_beschreibung = $row ['BESCHREIBUNG'];
            $this->z_zettel_id = $row ['ZETTEL_ID'];
        } else {
            $this->z_beschreibung = 'keine Infos';
            $this->z_zettel_id = 'keine Infos';
        }
    }
    
    function pos_loeschen($zettel_id, $pos_id)
    {
        DB::delete("DELETE FROM STUNDENZETTEL_POS WHERE ZETTEL_ID='$zettel_id' && ST_DAT='$pos_id'");
        hinweis_ausgeben("Zeile gelöscht, Sie werden weitergeleitet!");
        weiterleiten_in_sec(route('web::zeiterfassung::legacy', ['option' => 'zettel_eingabe', 'zettel_id' => $zettel_id], false), 2);
    }

    function pos_deaktivieren($zettel_id, $pos_dat)
    {
        DB::update("UPDATE STUNDENZETTEL_POS SET AKTUELL='0' WHERE ZETTEL_ID='$zettel_id' && ST_DAT='$pos_dat'");
    }
    
    function zettel_loeschen_voll($zettel_id)
    {
        DB::update("UPDATE STUNDENZETTEL SET AKTUELL='0' WHERE ZETTEL_ID='$zettel_id'");
        DB::update("UPDATE STUNDENZETTEL_POS SET AKTUELL='0' WHERE ZETTEL_ID='$zettel_id'");
    }

    function form_stunden_anzeigen()
    {
        $f = new formular ();
        $b = new buchen ();
        $be = new benutzer ();

        $f->erstelle_formular('Zeiterfassung durchsuchen', '');
        $be->dropdown_benutzer2('Mitarbeiter wählen', 'benutzer_id', 'benutzer_id', '');
        $be->dropdown_gewerke('Gewerk wählen', 'g_id', 'g_id', '');
        $js_typ = "onchange=\"list_kostentraeger('list_kostentraeger', this.value)\"";
        $b->dropdown_kostentreager_typen('Kostenträgertyp wählen', 'kostentraeger_typ', 'kostentraeger_typ', $js_typ);
        $js_id = "";
        $b->dropdown_kostentreager_ids('Kostenträger', 'kostentraeger_id', 'dd_kostentraeger_id', $js_id);
        $f->datum_feld('Anfangsdatum', 'adatum', '', 'adatum');
        $f->datum_feld('Enddatum', 'edatum', '', 'edatum');
        $f->hidden_feld('option', 'suchen_std');
        $f->send_button('send', 'Suchen');
        $f->ende_formular();
    }

    function stunden_suchen($benutzer_id, $gewerk_id, $kos_typ, $kos_bez, $adatum, $edatum)
    {
        $b = new buchen ();
        $von = date_german2mysql($adatum);
        $bis = date_german2mysql($edatum);
        $kos_id = $b->kostentraeger_id_ermitteln($kos_typ, $kos_bez);
        if (!$kos_id) {
            $kos_id = '%';
        }

        if (!$kos_typ) {
            $kos_typ = '%';
        }

        if ($kos_typ == '%') {
            $kos_typ_db = '';
        } else {
            $kos_typ_db = "&& KOSTENTRAEGER_TYP LIKE  '$kos_typ'";
        }

        if ($kos_id == '%') {
            $kos_id_db = '';
        } else {
            $kos_id_db = "&& KOSTENTRAEGER_ID = '$kos_id'";
        }

        /* Fall 1 Alle auf einer Baustelle */
        if ($benutzer_id == 'Alle' && $gewerk_id == 'Alle') {
            $result = DB::select("
SELECT KOSTENTRAEGER_TYP, KOSTENTRAEGER_ID, STUNDENZETTEL.BENUTZER_ID, name, hourly_rate, SUM( DAUER_MIN ) /60 AS STD, hourly_rate * ( SUM( DAUER_MIN ) /60 ) AS LEISTUNG_EUR
FROM STUNDENZETTEL_POS 
  JOIN STUNDENZETTEL ON ( STUNDENZETTEL.ZETTEL_ID = STUNDENZETTEL_POS.ZETTEL_ID )
  JOIN persons ON ( STUNDENZETTEL.BENUTZER_ID = persons.id )
  JOIN jobs ON (persons.id = jobs.employee_id)
WHERE STUNDENZETTEL.AKTUELL = '1' && STUNDENZETTEL_POS.AKTUELL = '1' $kos_typ_db $kos_id_db && DATUM BETWEEN ? AND ?
GROUP BY STUNDENZETTEL.BENUTZER_ID
ORDER BY GEWERK_ID ASC, STD DESC", [$von, $bis]);
            if (!empty($result)) {
                echo "<table>";
                echo "<tr><th>$kos_bez | $adatum - $edatum</tr>";
                echo "</table>";

                echo "<table class=\"sortable\">";
                echo "<tr><th>Mitarbeiter</th><th>Stunden</th><th>Leistung</th><th>ZUWEISUNG</th></tr>";
                $g_summe = 0;
                $g_summe_std = 0;
                foreach ($result as $row) {
                    $benutzername = $row['name'];
                    $mitarbeiter_ids [] = $row['BENUTZER_ID'];
                    $std = nummer_punkt2komma_t($row['STD']);
                    $eur = nummer_punkt2komma_t($row['LEISTUNG_EUR']);

                    $kostentraeger_typ = $row['KOSTENTRAEGER_TYP'];
                    $kostentraeger_id = $row['KOSTENTRAEGER_ID'];
                    $r = new rechnung ();
                    $kosten_bez = $r->kostentraeger_ermitteln($kostentraeger_typ, $kostentraeger_id);
                    echo "<tr><td>$benutzername</td><td>$std Std.</td><td>$eur €</td><td>$kosten_bez</td></tr>";
                    $g_summe_std += $row['STD'];
                    $g_summe += $row['LEISTUNG_EUR'];
                }
                $g_summe_a = nummer_punkt2komma_t($g_summe);
                $g_summe_std_a = nummer_punkt2komma_t($g_summe_std);

                echo "<tfoot><tr class=\"zeile2\"><td>Gesamt</td><td>$g_summe_std_a Std.</td><td>$g_summe_a €</td><td></td></tfoot>";

                echo "</table>";

                $result = DB::select("
SELECT job_titles.title, SUM( DAUER_MIN /60) AS STD, SUM(jobs.hourly_rate * DAUER_MIN / 60) AS LEISTUNG_EUR
FROM STUNDENZETTEL_POS
JOIN STUNDENZETTEL ON ( STUNDENZETTEL.ZETTEL_ID = STUNDENZETTEL_POS.ZETTEL_ID )
JOIN persons ON ( STUNDENZETTEL.BENUTZER_ID = persons.id )
JOIN jobs ON (persons.id = jobs.employee_id)
JOIN job_titles ON ( jobs.job_title_id = job_titles.id)
WHERE STUNDENZETTEL.AKTUELL = '1' && STUNDENZETTEL_POS.AKTUELL = '1' $kos_typ_db $kos_id_db && DATUM BETWEEN ? AND ?
GROUP BY jobs.job_title_id
ORDER BY STD DESC 
", [$von, $bis]);

                if (!empty($result)) {

                    echo "<table class=\"sortable\">";
                    echo "<tr><th>GEWERK</th><th>Stunden</th><th>Leistung</th></tr>";
                    $g_summe = 0;
                    $g_summe_std = 0;
                    foreach ($result as $row) {
                        $bez = $row['title'];
                        $std = nummer_punkt2komma_t($row['STD']);
                        $eur = nummer_punkt2komma_t($row['LEISTUNG_EUR']);
                        echo "<tr><td>$bez</td><td>$std Std.</td><td>$eur €</td></tr>";
                        $g_summe_std += $row['STD'];
                        $g_summe += $row['LEISTUNG_EUR'];
                    }
                    $g_summe_a = nummer_punkt2komma_t($g_summe);
                    $g_summe_std_a = nummer_punkt2komma_t($g_summe_std);
                    echo "<tfoot><tr class=\"zeile2\"><td>Gesamt</td><td>$g_summe_std_a Std.</td><td>$g_summe_a €</td></td></tfoot>";

                    echo "</table>";
                }

                foreach ($mitarbeiter_ids as $m_id) {
                    $result = DB::select("
SELECT KOSTENTRAEGER_TYP, KOSTENTRAEGER_ID, persons.id, name, DATUM, BEGINN, ENDE, DAUER_MIN, DAUER_MIN/60 AS STUNDEN, LEISTUNG_ID, BEZEICHNUNG FROM STUNDENZETTEL_POS 
JOIN STUNDENZETTEL ON 
(STUNDENZETTEL_POS.ZETTEL_ID=STUNDENZETTEL.ZETTEL_ID)
JOIN persons ON (STUNDENZETTEL.BENUTZER_ID=persons.id)
JOIN LEISTUNGSKATALOG ON (LEISTUNG_ID=LK_ID)
WHERE STUNDENZETTEL_POS.AKTUELL = '1'  && STUNDENZETTEL.AKTUELL = '1' && DATUM BETWEEN ? AND ? && STUNDENZETTEL.BENUTZER_ID=? $kos_typ_db $kos_id_db  ORDER BY DATUM", [$von, $bis, $m_id]);
                    if (!empty($result)) {
                        $bb = new benutzer ();
                        $bb->get_benutzer_infos($m_id);
                        $benutzername = $bb->benutzername;
                        echo "<table>";
                        echo "<tr><th>$kos_bez | Mitarbeiter $benutzername | Zeitraum: $adatum - $edatum</tr>";
                        echo "</table>";

                        echo "<table>";
                        echo "<tr><th>DATUM</th><th>ZEIT</th><th>Dauer</th><th>Leistung</th><th>ZUWEISUNG</th></tr>";

                        foreach ($result as $row) {

                            $datum_m = date_mysql2german($row['DATUM']);
                            $beginn = $row['BEGINN'];
                            $ende = $row['ENDE'];
                            $stunden = nummer_punkt2komma_t($row['STUNDEN']);
                            $d_min = $row['DAUER_MIN'];
                            $lbez = $row['BEZEICHNUNG'];
                            $kostentraeger_typ = $row['KOSTENTRAEGER_TYP'];
                            $kostentraeger_id = $row['KOSTENTRAEGER_ID'];
                            $r = new rechnung ();
                            $kosten_bez = $r->kostentraeger_ermitteln($kostentraeger_typ, $kostentraeger_id);
                            echo "<tr><td>$datum_m</td><td>$beginn - $ende</td><td>$d_min Min. / $stunden Std.</td><td>$lbez</td><td>$kosten_bez</td></tr>";
                        }
                        echo "</table>";
                    }
                }
            }
        }

        /* Fall 2 - Ein mitarbeiter nur */
        if ($benutzer_id != 'Alle') {
            $result = DB::select("SELECT KOSTENTRAEGER_TYP, KOSTENTRAEGER_ID, STUNDENZETTEL.BENUTZER_ID, name, jobs.hourly_rate, SUM( DAUER_MIN ) /60 AS STD, jobs.hourly_rate * ( SUM( DAUER_MIN ) /60 ) AS LEISTUNG_EUR
FROM `STUNDENZETTEL_POS`
JOIN STUNDENZETTEL ON ( STUNDENZETTEL.ZETTEL_ID = STUNDENZETTEL_POS.ZETTEL_ID )
JOIN persons ON ( STUNDENZETTEL.BENUTZER_ID = persons.id )
JOIN jobs ON (persons.id = jobs.employee_id)
WHERE STUNDENZETTEL.AKTUELL = '1' && STUNDENZETTEL_POS.AKTUELL = '1' && STUNDENZETTEL.BENUTZER_ID = ?
&& DATUM BETWEEN ? AND ? $kos_typ_db $kos_id_db 
GROUP BY STUNDENZETTEL.BENUTZER_ID LIMIT 0 , 1", [$benutzer_id, $von, $bis]);

            if (!empty($result)) {
                echo "<table>";
                echo "<tr><th>$kos_bez | $adatum - $edatum</tr>";
                echo "</table>";

                echo "<table>";
                echo "<tr><th>Mitarbeiter</th><th>Stunden</th><th>Leistung</th></tr>";
                foreach ($result as $row) {
                    $benutzername = $row['name'];
                    $std = nummer_punkt2komma_t($row['STD']);
                    $eur = nummer_punkt2komma_t($row['LEISTUNG_EUR']);
                    echo "<tr><td>$benutzername</td><td>$std Std.</td><td>$eur €</td></tr>";
                }
                echo "</table>";
            }

            $result = DB::select("
SELECT KOSTENTRAEGER_TYP, KOSTENTRAEGER_ID, persons.id, persons.name, DATUM, BEGINN, ENDE, DAUER_MIN, DAUER_MIN/60 AS STUNDEN, LEISTUNG_ID, BEZEICHNUNG FROM `STUNDENZETTEL_POS` 
JOIN STUNDENZETTEL ON (STUNDENZETTEL_POS.ZETTEL_ID=STUNDENZETTEL.ZETTEL_ID)
JOIN persons ON (STUNDENZETTEL.BENUTZER_ID=persons.id)
JOIN LEISTUNGSKATALOG ON (LEISTUNG_ID=LK_ID)
WHERE STUNDENZETTEL_POS.AKTUELL = '1' 
  && STUNDENZETTEL.AKTUELL = '1' 
  && DATUM BETWEEN ? AND ? 
  && STUNDENZETTEL.BENUTZER_ID=? $kos_typ_db $kos_id_db 
ORDER BY DATUM", [$von, $bis, $benutzer_id]);
            if (!empty($result)) {
                echo "<table>";
                echo "<tr><th>$kos_bez | Mitarbeiter $benutzername | Zeitraum: $adatum - $edatum</tr>";
                echo "</table>";

                echo "<table>";
                echo "<tr><th>DATUM</th><th>Dauer</th><th>Leistung</th><th>ZUWEISUNG</th></tr>";

                foreach ($result as $row) {
                    $datum_m = date_mysql2german($row['DATUM']);
                    $stunden = nummer_punkt2komma_t($row['STUNDEN']);
                    $d_min = $row['DAUER_MIN'];
                    $lbez = $row['BEZEICHNUNG'];
                    $kostentraeger_typ = $row['KOSTENTRAEGER_TYP'];
                    $kostentraeger_id = $row['KOSTENTRAEGER_ID'];
                    $r = new rechnung ();
                    $kosten_bez = $r->kostentraeger_ermitteln($kostentraeger_typ, $kostentraeger_id);
                    echo "<tr><td>$datum_m</td><td>$d_min Min. / $stunden Std.</td><td>$lbez</td><td>$kosten_bez</td></tr>";
                }
                echo "</table>";
            }
        }

        /* Fall 3 - Ein Gewerk, alle Mitarbeiter */
        if ($benutzer_id == 'Alle' && $gewerk_id != 'Alle') {
            $result = DB::select("
SELECT job_titles.title, SUM( DAUER_MIN /60 ) AS STD, SUM( jobs.hourly_rate * DAUER_MIN /60 ) AS LEISTUNG_EUR
FROM `STUNDENZETTEL_POS`
JOIN STUNDENZETTEL ON ( STUNDENZETTEL.ZETTEL_ID = STUNDENZETTEL_POS.ZETTEL_ID )
JOIN persons ON ( STUNDENZETTEL.BENUTZER_ID = persons.id )
JOIN jobs ON (persons.id = jobs.employee_id)
JOIN job_titles ON ( jobs.job_title_id = job_titles.id)
WHERE STUNDENZETTEL.AKTUELL = '1' 
  && STUNDENZETTEL_POS.AKTUELL = '1' 
  && KOSTENTRAEGER_TYP LIKE ? 
  && KOSTENTRAEGER_ID LIKE ? 
  && job_titles.id = ?
  && DATUM BETWEEN ? AND ? $kos_typ_db $kos_id_db
ORDER BY STD DESC, DATUM", [$kos_typ, $kos_id, $gewerk_id, $von, $bis]);

            if (!empty($result)) {
                echo "<table>";
                echo "<tr><th>$kos_bez | $adatum - $edatum</tr>";
                echo "</table>";

                echo "<table class=\"sortable\">";
                echo "<tr><th>GEWERK</th><th>Stunden</th><th>Leistung</th></tr>";
                $g_summe = 0;
                $g_summe_std = 0;
                foreach ($result as $row) {
                    $bez = $row['title'];
                    $std = nummer_punkt2komma_t($row['STD']);
                    $eur = nummer_punkt2komma_t($row['LEISTUNG_EUR']);
                    echo "<tr><td>$bez</td><td>$std Std.</td><td>$eur €</td></tr>";
                    $g_summe_std += $row['STD'];
                    $g_summe += $row['LEISTUNG_EUR'];
                }
                $g_summe_a = nummer_punkt2komma_t($g_summe);
                $g_summe_std_a = nummer_punkt2komma_t($g_summe_std);
                echo "<tfoot><tr class=\"zeile2\"><td>Gesamt</td><td>$g_summe_std_a Std.</td><td>$g_summe_a €</td></td></tfoot>";
                echo "</table>";

                $result = DB::select("SELECT KOSTENTRAEGER_TYP, KOSTENTRAEGER_ID, STUNDENZETTEL.BENUTZER_ID, name, jobs.hourly_rate, SUM( DAUER_MIN ) /60 AS STD, jobs.hourly_rate * ( SUM( DAUER_MIN ) /60 ) AS LEISTUNG_EUR
FROM `STUNDENZETTEL_POS` JOIN STUNDENZETTEL ON ( STUNDENZETTEL.ZETTEL_ID = STUNDENZETTEL_POS.ZETTEL_ID )
JOIN persons ON ( STUNDENZETTEL.BENUTZER_ID = persons.id )
JOIN jobs ON (persons.id = jobs.employee_id)
WHERE STUNDENZETTEL.AKTUELL = '1' && STUNDENZETTEL_POS.AKTUELL = '1' && DATUM BETWEEN ? AND ? $kos_typ_db $kos_id_db 
GROUP BY STUNDENZETTEL.BENUTZER_ID ORDER BY DATUM ASC, STD DESC, jobs.job_title_id ASC", [$von, $bis]);
                if (!empty($result)) {

                    echo "<br><table class=\"sortable\">";
                    echo "<tr><th>Mitarbeiter</th><th>Stunden</th><th>Leistung</th></tr>";
                    $g_summe = 0;
                    $g_summe_std = 0;
                    foreach ($result as $row) {
                        $mitarbeiter_ids [] = $row['BENUTZER_ID'];
                        $benutzername = $row['name'];
                        $std = nummer_punkt2komma_t($row['STD']);
                        $eur = nummer_punkt2komma_t($row['LEISTUNG_EUR']);
                        echo "<tr><td>$benutzername</td><td>$std Std.</td><td>$eur €</td></tr>";
                        $g_summe_std += $row['STD'];
                        $g_summe += $row['LEISTUNG_EUR'];
                    }
                    $g_summe_a = nummer_punkt2komma_t($g_summe);
                    $g_summe_std_a = nummer_punkt2komma_t($g_summe_std);
                    echo "<tfoot><tr class=\"zeile2\"><td>Gesamt</td><td>$g_summe_std_a Std.</td><td>$g_summe_a €</td></td></tfoot>";
                    echo "</table>";
                }

                foreach ($mitarbeiter_ids as $m_id) {
                    $result = DB::select("SELECT KOSTENTRAEGER_TYP, KOSTENTRAEGER_ID, persons.id, name, DATUM, BEGINN, ENDE, DAUER_MIN, DAUER_MIN/60 AS STUNDEN, LEISTUNG_ID, BEZEICHNUNG FROM STUNDENZETTEL_POS 
JOIN STUNDENZETTEL ON 
(STUNDENZETTEL_POS.ZETTEL_ID=STUNDENZETTEL.ZETTEL_ID)
JOIN persons ON (STUNDENZETTEL.BENUTZER_ID=persons.id)
JOIN LEISTUNGSKATALOG ON (LEISTUNG_ID=LK_ID)
WHERE STUNDENZETTEL_POS.AKTUELL = '1' && STUNDENZETTEL.AKTUELL = '1' && 
DATUM BETWEEN ? AND ? && STUNDENZETTEL.BENUTZER_ID=? $kos_typ_db $kos_id_db ORDER BY DATUM", [$von, $bis, $m_id]);

                    if (!empty($result)) {
                        $bb = new benutzer ();
                        $bb->get_benutzer_infos($m_id);
                        $benutzername = $bb->benutzername;
                        echo "<table>";
                        echo "<tr><th>$kos_bez | Mitarbeiter $benutzername | Zeitraum: $adatum - $edatum</tr>";
                        echo "</table>";

                        echo "<table>";
                        echo "<tr><th>DATUM</th><th>Dauer</th><th>Leistung</th><th>Zuweisung</th></tr>";

                        foreach ($result as $row) {
                            $datum_m = date_mysql2german($row['DATUM']);
                            $stunden = nummer_punkt2komma_t($row['STUNDEN']);
                            $d_min = $row['DAUER_MIN'];
                            $lbez = $row['BEZEICHNUNG'];
                            $kostentraeger_typ = $row['KOSTENTRAEGER_TYP'];
                            $kostentraeger_id = $row['KOSTENTRAEGER_ID'];
                            $r = new rechnung ();
                            $kosten_bez = $r->kostentraeger_ermitteln($kostentraeger_typ, $kostentraeger_id);
                            echo "<tr><td>$datum_m</td><td>$d_min Min. / $stunden Std.</td><td>$lbez</td><td>$kosten_bez</td></tr>";
                        }
                        echo "</table>";
                    }
                }
            }
        }
    }
} // end class zeiterfassung
