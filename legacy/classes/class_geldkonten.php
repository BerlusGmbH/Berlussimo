<?php

class gk
{
    public $iban_kos_id;

    function form_geldkonto_neu()
    {
        $f = new formular ();
        $f->erstelle_formular("Neues Geldkonto erstellen", NULL);
        $f->text_feld("Geldkontobezeichnung", "g_bez", "", "50", 'g_bez', '');
        $f->text_feld("Begünstigter", "beguenstigter", "", "50", 'beguenstigter', '');
        $f->text_feld("Kontonummer", "kontonummer", "", "50", 'kontonummer', '');
        $f->text_feld("BLZ", "blz", "", "50", 'blz', '');
        $js_iban_bic = "onclick=\"get_iban_bic('kontonummer', 'blz')\"";
        $f->button_js('chkk_ibanbic', 'IBAN/BIC berechnen', $js_iban_bic);
        $f->text_feld("IBAN", "iban", "", "50", 'iban', '');
        $f->text_feld("BIC", "bic", "", "50", 'bic', '');
        $f->text_feld("Geldinstitut", "institut", "", "50", 'institut', '');

        $b = new buchen ();
        $js_typ = "onchange=\"list_kostentraeger('list_kostentraeger', this.value)\"";
        // $js_typ='';
        $b->dropdown_kostentreager_typen('Geldkonto zuweisen an', 'kostentraeger_typ', 'kostentraeger_typ', $js_typ);
        $js_id = "";
        $b->dropdown_kostentreager_ids('Bitte Zuweisung wählen', 'kostentraeger_id', 'dd_kostentraeger_id', $js_id);

        $f->hidden_feld("option", "new_gk");
        $f->send_button("submit_gk", "Erstellen");
        $f->ende_formular();
    }

    function form_geldkonto_edit($gk_id)
    {
        $gkk = new geldkonto_info ();
        $gkk->geld_konto_details($gk_id);

        $f = new formular ();
        $f->erstelle_formular("Geldkonto ändern", NULL);
        $f->text_feld("Geldkontobezeichnung", "g_bez", "$gkk->geldkonto_bez", "50", 'g_bez', '');
        $f->text_feld("Begünstigter", "beguenstigter", "$gkk->beguenstigter", "50", 'beguenstigter', '');
        $f->text_feld("Kontonummer", "kontonummer", "$gkk->kontonummer", "50", 'kontonummer', '');
        $f->text_feld("BLZ", "blz", "$gkk->blz", "50", 'blz', '');
        $js_iban_bic = "onclick=\"get_iban_bic('kontonummer', 'blz')\"";
        $f->button_js('chkk_ibanbic', 'IBAN/BIC berechnen', $js_iban_bic);
        $f->text_feld("IBAN", "iban", "$gkk->IBAN1", "50", 'iban', '');
        $f->text_feld("BIC", "bic", "$gkk->BIC", "50", 'bic', '');
        $f->text_feld("Geldinstitut", "institut", "$gkk->institut", "50", 'institut', '');
        /*
         * $b = new buchen;
         * $js_typ = "onchange=\"list_kostentraeger('list_kostentraeger', this.value)\"";
         * #$js_typ='';
         * $b->dropdown_kostentreager_typen('Geldkonto zuweisen an', 'kostentraeger_typ', 'kostentraeger_typ', $js_typ);
         * $js_id = "";
         * $b->dropdown_kostentreager_ids('Bitte Zuweisung wählen', 'kostentraeger_id', 'dd_kostentraeger_id', $js_id);
         */

        $f->hidden_feld("option", "gk_update");
        $f->send_button("submit_gk", "ändern");
        $f->ende_formular();
    }

    function geldkonto_update($gk_id, $g_bez, $beguenstigter, $kontonummer, $blz, $institut, $iban, $bic)
    {
        $db_abfrage = "UPDATE GELD_KONTEN SET AKTUELL='0' WHERE KONTO_ID='$gk_id'";
        DB::update($db_abfrage);

        $db_abfrage = "INSERT INTO GELD_KONTEN VALUES (NULL, '$gk_id', '$g_bez','$beguenstigter', '$kontonummer', '$blz','$iban', '$bic', '$institut', '1')";
        DB::insert($db_abfrage);
    }

    function geldkonto_speichern($kos_typ, $kos_id, $g_bez, $beguenstigter, $kontonummer, $blz, $institut, $iban, $bic)
    {
        if (!$this->check_gk_exists($kontonummer, $blz, $institut)) {
            $bk = new bk ();
            $last_b_id = $bk->last_id('GELD_KONTEN', 'KONTO_ID') + 1;

            $db_abfrage = "INSERT INTO GELD_KONTEN VALUES (NULL, '$last_b_id', '$g_bez','$beguenstigter', '$kontonummer', '$blz','$iban', '$bic', '$institut', '1')";
            DB::insert($db_abfrage);

            /* Protokollieren */
            $last_dat = DB::getPdo()->lastInsertId();
            protokollieren('GELD_KONTEN', $last_dat, '0');

            if ($this->check_zuweisung_kos($last_b_id, $kos_typ, $kos_id)) {
                echo "Zuweisung existiert bereits.";
            } else {
                $this->zuweisung_speichern($kos_typ, $kos_id, $last_b_id);
            }

            echo "Geldkonto wurde gespeichert.";
            return $last_b_id;
        } else {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\InfoMessage("Geldkonto existiert schon")
            );
        }
    }

    function check_gk_exists($kontonummer, $blz, $institut)
    {
        $result = DB::select("SELECT * FROM GELD_KONTEN WHERE AKTUELL = '1' && KONTONUMMER='$kontonummer' && BLZ='$blz' && INSTITUT='$institut'");
        return !empty($result);
    }

    function check_zuweisung_kos($geldkonto_id, $kos_typ, $kos_id)
    {
        $result = DB::select("SELECT * FROM GELD_KONTEN_ZUWEISUNG WHERE AKTUELL = '1' && KONTO_ID='$geldkonto_id' && KOSTENTRAEGER_TYP='$kos_typ' && KOSTENTRAEGER_ID='$kos_id'");
        return !empty($result);
    }

    function zuweisung_speichern($kos_typ, $kos_id, $geldkonto_id, $von, $bis, $vwzk)
    {
        $bk = new bk ();
        $last_b_id = $bk->last_id('GELD_KONTEN_ZUWEISUNG', 'ZUWEISUNG_ID') + 1;

        if ($bis == '' || $bis == '0000-00-00') {
            $bis = "NULL";
        } else {
            $bis = "'$bis'";
        }

        $db_abfrage = "INSERT INTO GELD_KONTEN_ZUWEISUNG VALUES (NULL, '$last_b_id', '$geldkonto_id', '$kos_typ','$kos_id', '1', '$von', $bis, '$vwzk')";
        DB::insert($db_abfrage);
        return $last_b_id;
    }

    function form_geldkonto_zuweisen($id = null)
    {
        $konto_id = null;
        $kostentraeger_typ = null;
        $kostentraeger_id = null;
        $vwzk = null;
        $von = null;
        $bis = null;
        $result = null;
        if ($id) {
            $result = DB::table('GELD_KONTEN_ZUWEISUNG')
                ->where('ZUWEISUNG_ID', $id)
                ->where('AKTUELL', '1')
                ->first();
            if ($result) {
                $konto_id = $result['KONTO_ID'];
                $kostentraeger_typ = $result['KOSTENTRAEGER_TYP'];
                $kostentraeger_id = $result['KOSTENTRAEGER_ID'];
                $vwzk = $result['VERWENDUNGSZWECK'];
                $von = $result['VON'];
                $bis = $result['BIS'];
            }
        }
        $f = new formular ();
        $f->erstelle_formular("Geldkonto zuweisen", NULL);
        $this->dropdown_geldkonten_alle('Geldkonto wählen', 'geldkonto_id', 'geldkonto_id', $konto_id);

        $b = new buchen ();
        $js_typ = "onchange=\"list_kostentraeger('list_kostentraeger', this.value)\"";
        $b->dropdown_kostentreager_typen_vw('Geldkonto zuweisen an', 'kostentraeger_typ', 'kostentraeger_typ', $js_typ, $kostentraeger_typ);
        $js_id = "";
        $b->dropdown_kostentraeger_bez_vw('Bitte Zuweisung wählen', 'kostentraeger_id', 'dd_kostentraeger_id', $js_id, $kostentraeger_typ, $kostentraeger_id);

        $options = [
            '' => 'Keiner',
            'Lohnzahlung' => 'Lohnzahlung (Benutzer)',
            'Eigentümerentnahme' => 'Eigentümerentnahme (Partner / WEG-Eigentümer)',
            'Hausgeld' => 'Hausgeld (Objekt)',
            'IHR' => 'IHR (Objekt)',
            'Kaution' => 'Kaution (Objekt)',
            'Kreditor' => 'Kreditor (Partner)'
        ];

        echo "<div class='input-field'>";
        echo "<select name=\"vwzk\" id=\"vwzk\" size=1>";
        foreach ($options as $value => $name) {
            echo "<option value=\"";
            $v = is_numeric($value) ? $name : $value;
            echo "$v\"";
            echo " " . (($v === $vwzk && $vwzk) ? 'selected' : '');
            echo ">$name</option>\n";
        }
        echo "</select>\n";
        echo "<label for=\"vwzk\">Verwendungszweck</label>\n";
        echo "</div>";

        echo "<div class='input-field'>";
        $v = $von ?: \Carbon\Carbon::now()->toDateString();
        echo "<input name='von' id='von' type='date' value='" . $v . "'>";
        echo "<label for='von' class='active'>Von</label>";
        echo "</div>";

        echo "<div class='input-field'>";
        echo "<input name='bis' id='bis' type='date' value='" . $bis . "'>";
        echo "<label for='bis' class='active'>Bis</label>";
        echo "</div>";

        if ($result) {
            $f->hidden_feld("option", "aendern_gk");
            $f->hidden_feld("zuweisung_id", $id);
            $f->send_button("submit_gk", "Ändern");
        } else {
            $f->hidden_feld("option", "zuweisen_gk");
            $f->send_button("submit_gk", "Zuweisen");
        }
        $f->ende_formular();
    }

    function dropdown_geldkonten_alle($label, $name, $id, $selected_id = null)
    {
        $my_array = DB::select("SELECT *  FROM  GELD_KONTEN WHERE GELD_KONTEN.AKTUELL = '1' ORDER BY BEZEICHNUNG ASC");

        $numrows = count($my_array);
        if ($numrows) {
            echo "<label for=\"$id\">$label</label>\n<select name=\"$name\" id=\"$id\" size=\"1\" >\n";
            for ($a = 0; $a < $numrows; $a++) {
                $konto_id = $my_array [$a] ['KONTO_ID'];
                $kontonummer = $my_array [$a] ['KONTONUMMER'];
                $blz = $my_array [$a] ['BLZ'];
                $bez = $my_array [$a] ['BEZEICHNUNG'];
                if (session()->get('geldkonto_id') == $konto_id || $selected_id == $konto_id) {
                    echo "<option value=\"$konto_id\" selected>$bez - Knr:$kontonummer - Blz: $blz</option>\n";
                } else {
                    echo "<option value=\"$konto_id\" >$bez - Knr:$kontonummer - Blz: $blz</option>\n";
                }
            } // end for
            echo "</select>\n";
        } else {
            echo "<b>Kein Geldkonto hinterlegt</b>";
            return FALSE;
        }
    }

    function dropdown_geldkonten_alle_vorwahl($label, $name, $id, $vorwahl_gk_id, $js)
    {
        $result = DB::select("SELECT *  FROM  GELD_KONTEN WHERE GELD_KONTEN.AKTUELL = '1' ORDER BY BEZEICHNUNG ASC");
        $numrows = count($result);
        if ($numrows) {
            echo "<label for=\"$id\">$label</label>\n<select name=\"$name\" id=\"$id\" size=\"1\" $js>\n";
            foreach ($result as $row) {
                $konto_id = $row ['KONTO_ID'];
                $bez = $row ['BEZEICHNUNG'];
                $iban = $row ['IBAN'];
                $bic = $row ['BIC'];
                $iban1 = chunk_split($iban, 4, ' ');
                if ($vorwahl_gk_id == $konto_id) {
                    echo "<option value=\"$konto_id\" selected>$bez - $iban1 -  $bic</option>\n";
                } else {
                    echo "<option value=\"$konto_id\" >$bez - $iban - $bic</option>\n";
                }
            } // end for
            echo "</select>\n";
        } else {
            echo "<b>Kein Geldkonto hinterlegt</b>";
            return FALSE;
        }
    }

    function uebersicht_zuweisung()
    {
        $my_array = DB::select("SELECT *  FROM  GELD_KONTEN WHERE GELD_KONTEN.AKTUELL = '1' ORDER BY BEZEICHNUNG ASC");
        $numrows = count($my_array);
        if ($numrows > 0) {
            echo "<table class=\"sortable\">";
            echo "<tr><th>BEZEICHNUNG</th><th width=\"220\">IBAN</th><th>BIC</th><th>ZUWEISUNG</th></tr>";
            for ($a = 0; $a < $numrows; $a++) {
                $konto_id = $my_array [$a] ['KONTO_ID'];
                $iban = chunk_split($my_array [$a] ['IBAN'], 4, ' ');
                $bic = $my_array [$a] ['BIC'];
                $bez = $my_array [$a] ['BEZEICHNUNG'];
                $zuweisung_string = $this->check_zuweisung($konto_id);
                echo "<tr><td>$bez</td><td>$iban</td><td>$bic</td><td>$zuweisung_string</td></tr>";
                unset ($zuweisung_string);
            } // end for
            echo "</table>";
        } else {
            echo "Keine Geldkonten hinterlegt";
        }
    }

    function check_zuweisung($geldkonto_id)
    {
        $my_array = DB::select("SELECT * FROM GELD_KONTEN_ZUWEISUNG WHERE AKTUELL = '1' && KONTO_ID='$geldkonto_id'");

        $numrows = count($my_array);

        if ($numrows > 0) {
            $kos_bez_string = '';
            for ($a = 0; $a < $numrows; $a++) {
                $zaehler = $a + 1;
                $kos_typ = $my_array [$a] ['KOSTENTRAEGER_TYP'];
                $kos_id = $my_array [$a] ['KOSTENTRAEGER_ID'];
                $verwendungszweck = $my_array [$a] ['VERWENDUNGSZWECK'];
                $zuweisung_id = $my_array [$a] ['ZUWEISUNG_ID'];
                $r = new rechnung ();
                $kos_bez = $r->kostentraeger_ermitteln($kos_typ, $kos_id);
                $link_loeschen = "<a href='" . route('web::geldkonten::legacy', ['option' => 'zuweisung_loeschen', 'geldkonto_id' => $geldkonto_id, 'kos_typ' => $kos_typ, 'kos_id' => $kos_id]) . "'><b>Aufheben</b></a>";
                $link_aendern = "<a href='" . route('web::geldkonten::legacy', ['option' => 'zuweisung_aendern', 'zuweisung_id' => $zuweisung_id]) . "'><b>Bearbeiten</b></a>";
                $kos_bez_string .= "$zaehler. " . $kos_bez . " | " . $verwendungszweck . " | $link_aendern  |  $link_loeschen<br>";
            }
            return $kos_bez_string;
        } else {
            return "<b>Keine Zuweisung</b>";
        }
    }

    function get_objekt_id($geldkonto_id)
    {
        $result = DB::select("SELECT KOSTENTRAEGER_ID FROM GELD_KONTEN_ZUWEISUNG WHERE AKTUELL = '1' && KONTO_ID='$geldkonto_id' && KOSTENTRAEGER_TYP='Objekt' && VERWENDUNGSZWECK='Hausgeld' ORDER BY VON DESC LIMIT 0,1");
        if (!empty($result)) {
            $row = $result[0];
            $kos_id = $row ['KOSTENTRAEGER_ID'];
            return $kos_id;
        }
    }

    function get_zuweisung_kos_arr($kos_typ, $kos_id)
    {
        $db_abfrage = "SELECT * FROM GELD_KONTEN_ZUWEISUNG WHERE AKTUELL = '1' && KOSTENTRAEGER_TYP='$kos_typ' && KOSTENTRAEGER_ID='$kos_id'";
        $result = DB::select($db_abfrage);
        return $result;
    }

    function check_zuweisung_kos_typ($geldkonto_id, $kos_typ, $kos_id)
    {
        if (!empty ($kos_id)) {
            $result = DB::select("SELECT *  FROM  GELD_KONTEN_ZUWEISUNG WHERE AKTUELL = '1' && KONTO_ID='$geldkonto_id' && KOSTENTRAEGER_TYP='$kos_typ' && KOSTENTRAEGER_ID='$kos_id'");
        } else {
            $result = DB::select("SELECT *  FROM  GELD_KONTEN_ZUWEISUNG WHERE AKTUELL = '1' && KONTO_ID='$geldkonto_id' && KOSTENTRAEGER_TYP='$kos_typ'");
        }
        return !empty($result);
    }

    function zuweisung_aufheben($kos_typ, $kos_id, $geldkonto_id)
    {
        $db_abfrage = "UPDATE GELD_KONTEN_ZUWEISUNG SET AKTUELL='0' WHERE AKTUELL = '1' && KONTO_ID='$geldkonto_id' && KOSTENTRAEGER_TYP='$kos_typ' && KOSTENTRAEGER_ID='$kos_id'";
        DB::update($db_abfrage);
    }

    function get_geldkonto_id($bezeichnung)
    {
        $result = DB::select("SELECT KONTO_ID FROM GELD_KONTEN WHERE BEZEICHNUNG='$bezeichnung' && AKTUELL='1' ORDER BY KONTO_DAT DESC LIMIT 0,1");
        $row = $result[0];
        return $row ['KONTO_ID'];
    }

    function get_geldkonto_id2($kto, $blz, $iban = null)
    {
        if ($iban == null) {
            $result = DB::select("SELECT KONTO_ID  FROM GELD_KONTEN WHERE KONTONUMMER='$kto' && BLZ='$blz' &&  AKTUELL='1' ORDER BY KONTO_DAT DESC LIMIT 0,2");
        } else {
            $result = DB::select("SELECT KONTO_ID  FROM GELD_KONTEN WHERE IBAN='$iban'  &&  AKTUELL='1' ORDER BY KONTO_DAT DESC LIMIT 0,2");
        }
        $numrows = count($result);
        if ($numrows == 1) {
            $row = $result[0];
            return $row ['KONTO_ID'];
        }

        if ($numrows > 1) {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\ErrorMessage("$kto $blz $iban\nexistiert in Geldkonten $numrows mal.")
            );
        }
    }

    function get_geldkonto_id_from_approximation($kto, $blz)
    {
        $kto = trim($kto, '0');
        $result = DB::select("SELECT KONTO_ID  FROM GELD_KONTEN WHERE KONTONUMMER like '%$kto%' && BLZ='$blz' &&  AKTUELL='1' ORDER BY KONTO_DAT DESC LIMIT 0,2");

        $numrows = count($result);
        if ($numrows == 1) {
            $row = $result[0];
            return $row ['KONTO_ID'];
        }

        if ($numrows > 1) {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\ErrorMessage("$kto $blz $iban\nexistiert in Geldkonten $numrows mal.")
            );
        }
    }

    function get_kos_by_iban($iban)
    {
        if (isset ($this->iban_kos_typ)) {
            unset ($this->iban_kos_typ);
        }
        if (isset ($this->iban_kos_id)) {
            unset ($this->iban_kos_id);
        }
        if (isset ($this->iban_konto_id)) {
            unset ($this->iban_konto_id);
        }
        $result = DB::select("SELECT GELD_KONTEN.KONTO_ID, IBAN, GELD_KONTEN_ZUWEISUNG.KOSTENTRAEGER_TYP, GELD_KONTEN_ZUWEISUNG.KOSTENTRAEGER_ID   FROM `GELD_KONTEN`, GELD_KONTEN_ZUWEISUNG WHERE GELD_KONTEN.IBAN = '$iban' AND GELD_KONTEN.KONTO_ID=GELD_KONTEN_ZUWEISUNG.KONTO_ID && GELD_KONTEN.AKTUELL = '1' && GELD_KONTEN_ZUWEISUNG.AKTUELL = '1' LIMIT 0,1");
        $row = $result[0];
        $this->iban_kos_typ = $row ['KOSTENTRAEGER_TYP'];
        $this->iban_kos_id = $row ['KOSTENTRAEGER_ID'];
        $this->iban_konto_id = $row ['KONTO_ID'];
    }

    function update_iban_bic_alle()
    {
        $result = DB::select("SELECT *  FROM  GELD_KONTEN WHERE GELD_KONTEN.AKTUELL = '1' ORDER BY KONTO_DAT");

        $numrows = count($result);
        if ($numrows) {
            foreach ($result as $row) {
                $dat = $row ['KONTO_DAT'];
                $kto = $row ['KONTONUMMER'];
                $blz = $row ['BLZ'];
                $sep = new sepa ();
                $sep->get_iban_bic($kto, $blz);
                /* Update */
                $db_abfrage = "UPDATE GELD_KONTEN SET IBAN='$sep->IBAN', BIC='$sep->BIC' WHERE KONTO_DAT='$dat'";
                DB::update($db_abfrage);
            }
            echo "Alle vorhandenen Geldkonten mit IBAN und BIC versehen!!!";
        }
    }
} // end class gk
