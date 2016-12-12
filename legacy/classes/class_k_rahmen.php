<?php

class k_rahmen
{
    public $name;
    public $dat;
    public $kontenrahmen_id;
    public $kontoart_id;
    public $gruppen_id;
    public $konto;
    public $konto_bez;

    function kontenrahmen_liste_anzeigen()
    {
        $arr = $this->kontenrahmen_in_arr();
        $anz = count($arr);
        if ($anz > 0) {

            echo "<TABLE class=\"sortable\">";
            echo "<tr><th>ID</th><th>BEZEICHNUNG</th><th>OPTIONEN</th></tr>";
            for ($a = 0; $a < $anz; $a++) {
                $id = $arr [$a] ['KONTENRAHMEN_ID'];
                $bez = $arr [$a] ['NAME'];
                $pdf_link = "<a href='" . route('web::kontenrahmen::legacy', ['option' => 'konten_anzeigen', 'k_id' => $id, 'pdf']) . "'>PDF ANSICHT</a>";
                $link = "<a href='" . route('web::kontenrahmen::legacy', ['option' => 'konten_anzeigen', 'k_id' => $id]) . "'>Kostenkonten anzeigen</a>";
                echo "<tr><td>$id</td><td>$bez";
                $this->zuweisung_anzeigen($id);
                echo "</td><td>$link $pdf_link</td></tr>";
            }
            echo "</TABLE>";
        } else {
            echo "Keine Kontenrahmen vorhanden";
        }
    }

    function kontenrahmen_in_arr()
    {
        $result = DB::select("SELECT *  FROM KONTENRAHMEN WHERE  AKTUELL='1' ORDER BY NAME ASC");
        return $result;
    }

    function zuweisung_anzeigen($kontenrahmen_id)
    {
        $result = DB::select("SELECT * FROM `KONTENRAHMEN_ZUWEISUNG` WHERE `KONTENRAHMEN_ID` ='$kontenrahmen_id' AND `AKTUELL`='1'");
        if (!empty($result)) {
            foreach($result as $row) {
                $dat = $row ['DAT'];
                $typ = $row ['TYP'];
                $id = $row ['TYP_ID'];
                $r = new rechnung ();
                $kos_bez = $r->kostentraeger_ermitteln($typ, $id);
                $link_zuweis_loeschen = "<a href='" . route('web::kontenrahmen::legacy', ['option' => 'zuweisung_del', 'dat' => $dat]) . "'>Zuweisung löschen</a>";
                echo "<br><b>$typ: $kos_bez</b> - $link_zuweis_loeschen";
            }
        } else {
            echo "<br><b>Nicht zugewiesen</b>";
        }
    }

    function konten_liste_anzeigen($kontenrahmen_id)
    {
        session()->put('kontenrahmen_id', $kontenrahmen_id);
        $this->get_kontenrahmen_infos($kontenrahmen_id);
        $arr = $this->konten_in_arr_rahmen($kontenrahmen_id);
        $anz = count($arr);
        if ($anz > 0) {
            echo "<TABLE class=\"sortable\">";
            echo "<tr><th colspan=\"5\">$this->name</th></tr>";
            echo "<tr><th>KONTO</th><th>BEZEICHNUNG</th><th>GRUPPE</th><th>KOntoart</th><th>OPTIONEN</th></tr>";
            for ($a = 0; $a < $anz; $a++) {
                $dat = $arr [$a] ['KONTENRAHMEN_KONTEN_DAT'];
                $konto = $arr [$a] ['KONTO'];
                $bez = $arr [$a] ['BEZEICHNUNG'];
                $gruppe = $arr [$a] ['GRUPPE'];
                $kontoart = $arr [$a] ['KONTOART'];

                $link = "<a href='" . route('web::kontenrahmen::legacy', ['option' => 'kostenkonto_ae', 'k_dat' => $dat]) . "'>KONTO ÄNDERN</a>";
                echo "<tr><td>$konto</td><td>$bez</td><td>$gruppe</td><td>$kontoart</td><td>$link</td></tr>";
            }
            echo "</TABLE>";
        } else {
            echo "Keine Kostenkonten vorhanden";
        }
    }

    function get_kontenrahmen_infos($kontenrahmen_id)
    {
        $result = DB::select("SELECT *  FROM  KONTENRAHMEN WHERE AKTUELL = '1' && KONTENRAHMEN_ID='$kontenrahmen_id'");
        unset ($this->dat);
        unset ($this->name);
        if (!empty($result)) {
            $row = $result[0];
            $this->dat = $row ['KONTENRAHMEN_DAT'];
            $this->name = $row ['NAME'];
        }
    }

    function konten_in_arr_rahmen($kontenrahmen_id)
    {
        $result = DB::select("SELECT KONTENRAHMEN_KONTEN_DAT, KONTO, KONTENRAHMEN_KONTEN.BEZEICHNUNG, GRUPPE AS GRUPPEN_ID, KONTENRAHMEN_GRUPPEN.BEZEICHNUNG AS GRUPPE, KONTO_ART AS KONTOART_ID, KONTOART
FROM KONTENRAHMEN_KONTEN, KONTENRAHMEN_GRUPPEN, KONTENRAHMEN_KONTOARTEN
WHERE KONTENRAHMEN_ID = '$kontenrahmen_id' && KONTENRAHMEN_GRUPPEN.AKTUELL = '1' && KONTENRAHMEN_KONTEN.AKTUELL = '1' && KONTENRAHMEN_KONTOARTEN.AKTUELL = '1' && KONTENRAHMEN_KONTEN.GRUPPE = KONTENRAHMEN_GRUPPEN.KONTENRAHMEN_GRUPPEN_ID && KONTO_ART = KONTENRAHMEN_KONTOART_ID
ORDER BY KONTO ASC");
        return $result;
    }

    function konten_liste_anzeigen_pdf($kontenrahmen_id)
    {
        ob_clean(); // ausgabepuffer leeren
        $pdf = new Cezpdf ('a4', 'portrait');
        $bpdf = new b_pdf ();
        $bpdf->b_header($pdf, 'Partner', session()->get('partner_id'), 'portrait', 'Helvetica.afm', 6);
        $pdf->ezStopPageNumbers(); // seitennummerirung beenden
        $p = new partners ();
        $p->get_partner_info(session()->get('partner_id'));

        session()->put('kontenrahmen_id', $kontenrahmen_id);
        $this->get_kontenrahmen_infos($kontenrahmen_id);
        $arr = $this->konten_in_arr_rahmen($kontenrahmen_id);
        $anz = count($arr);
        if ($anz > 0) {
            $cols = array(
                'KONTO' => "KONTO",
                'BEZ' => "BEZEICHNUNG",
                'GRUPPE' => "GRUPPE",
                'KONTOART' => 'KONTOART'
            );

            for ($a = 0; $a < $anz; $a++) {
                $tab [$a] ['KONTO'] = $arr [$a] ['KONTO'];
                $tab [$a] ['BEZ'] = $arr [$a] ['BEZEICHNUNG'];
                $tab [$a] ['GRUPPE'] = $arr [$a] ['GRUPPE'];
                $tab [$a] ['KONTOART'] = $arr [$a] ['KONTOART'];
            }

            $pdf->ezTable($tab, $cols, "$this->name", array(
                'showHeadings' => 1,
                'shaded' => 1,
                'titleFontSize' => 8,
                'fontSize' => 7,
                'xPos' => 55,
                'xOrientation' => 'right',
                'width' => 500,
                'cols' => array(
                    'DATUM' => array(
                        'justification' => 'right',
                        'width' => 65
                    ),
                    'G_BUCHUNGSNUMMER' => array(
                        'justification' => 'right',
                        'width' => 30
                    ),
                    'BETRAG' => array(
                        'justification' => 'right',
                        'width' => 75
                    )
                )
            ));

            /* Ausgabe */
            ob_end_clean(); // ausgabepuffer leeren
            $pdf->ezStream();
        } else {
            echo "Keine Kostenkonten vorhanden";
        }
    }

    function form_kontenrahmen_neu()
    {
        $f = new formular ();
        $f->erstelle_formular("Neuen Kontenrahmen erstellen", NULL);
        $f->text_feld("Kontenrahmenbezeichnung", "k_bez", "", "50", 'k_bez', '');
        $f->hidden_feld("option", "k_bez_neu");
        $f->send_button("submit_k_bez", "Erstellen");
        $f->ende_formular();
    }

    function kontenrahmen_speichern($k_bez)
    {
        if (!$this->check_k_exists($k_bez)) {
            $k_id = last_id2("KONTENRAHMEN", "KONTENRAHMEN_ID") + 1;
            $db_abfrage = "INSERT INTO KONTENRAHMEN VALUES (NULL, '$k_id', '$k_bez', '1')";
            DB::insert($db_abfrage);
            /* Protokollieren */
            $last_dat = DB::getPdo()->lastInsertId();
            protokollieren('KONTENRAHMEN', $last_dat, '0');
            return true;
        } else {
            echo "$k_bez exisitiert schon";
            return false;
        }
    }

    function check_k_exists($k_bez)
    {
        $result = DB::select("SELECT *  FROM  KONTENRAHMEN WHERE AKTUELL = '1' && NAME='$k_bez' ");
        return !empty($result);
    }

    function form_kostenkonto_neu()
    {
        $f = new formular ();
        $f->erstelle_formular("Neues Kostenkonto erstellen", NULL);
        $this->dropdown_kontenrahmen('Kontenrahmen', 'kontenrahmen_id', 'kontenrahmen_id');
        $f->text_feld("Kostenkonto", "konto", "", "10", 'konto', '');
        $f->text_feld("Kostenkontobezeichnung", "bez", "", "50", 'bez', '');
        $this->dropdown_k_arten('Kontoart', 'kontoart_id', 'kontoart_id');
        $this->dropdown_k_gruppen('Gruppe', 'k_gruppe', 'k_gruppe');
        $f->hidden_feld("option", "konto_neu");
        $f->send_button("submit_konto", "Erstellen");
        $f->ende_formular();
    }

    function dropdown_kontenrahmen($label, $name, $id)
    {
        $my_array = DB::select("SELECT * FROM KONTENRAHMEN WHERE AKTUELL='1' ORDER BY NAME ASC");
        if (!empty($my_array)) {
            echo "<div class='input-field'>";
            echo "<select name=\"$name\" id=\"$id\" size=\"1\" >\n";
            $numrows = count($my_array);
            for ($a = 0; $a < $numrows; $a++) {
                $id = $my_array [$a] ['KONTENRAHMEN_ID'];
                $bez = $my_array [$a] ['NAME'];
                if (session()->has('kontenrahmen_id') && session()->get('kontenrahmen_id') == $id) {
                    echo "<option value=\"$id\" selected>$bez</option>\n";
                } else {
                    echo "<option value=\"$id\">$bez</option>\n";
                }
            } // end for
            echo "</select>\n<label for=\"$id\">$label</label>\n";
            echo "</div>";
        } else {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\ErrorMessage("Es existieren keine Kontenrahmen")
            );
        }
    }

    function dropdown_k_arten($label, $name, $id)
    {
        $my_array = DB::select("SELECT * FROM KONTENRAHMEN_KONTOARTEN WHERE AKTUELL = '1'  ORDER BY KONTOART ASC");

        $numrows = count(!$my_array);
        if ($numrows > 0) {
            echo "<div class='input-field'>";
            echo "<select name=\"$name\" id=\"$id\" size=\"1\" >\n";
            for ($a = 0; $a < count($my_array); $a++) {
                $kid = $my_array [$a] ['KONTENRAHMEN_KONTOART_ID'];
                $bez = $my_array [$a] ['KONTOART'];
                if (session()->has('k_kontoart_id') && session()->get('k_kontoart_id') == $kid) {
                    echo "<option value=\"$kid\" selected>$bez</option>\n";
                } else {
                    echo "<option value=\"$kid\">$bez</option>\n";
                }
            } // end for
            echo "</select>\n<label for=\"$id\">$label</label>\n";
            echo "</div>";
        } else {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\ErrorMessage("Bitte fügen Sie Kontoarten hinzu.")
            );
        }
    }

    function dropdown_k_gruppen($label, $name, $id)
    {
        $my_array = DB::select("SELECT * FROM KONTENRAHMEN_GRUPPEN WHERE AKTUELL = '1'  ORDER BY BEZEICHNUNG ASC");

        $numrows = count($my_array);
        if ($numrows > 0) {
            echo "<div class='input-field'>";
            echo "<select name=\"$name\" id=\"$id\" size=\"1\" >\n";
            for ($a = 0; $a < count($my_array); $a++) {
                $id = $my_array [$a] ['KONTENRAHMEN_GRUPPEN_ID'];
                $bez = $my_array [$a] ['BEZEICHNUNG'];
                if (session()->has('k_gruppen_id') && session()->get('k_gruppen_id') == $id) {
                    echo "<option value=\"$id\" selected>$bez</option>\n";
                } else {
                    echo "<option value=\"$id\">$bez</option>\n";
                }
            } // end for
            echo "</select>\n<label for=\"$id\">$label</label>\n";
            echo "</div>";
        } else {
            echo "<b>Keine Kostenkontengruppen hinterlegt</b>";
            return FALSE;
        }
    }

    function kostenkonto_speichern($kontenrahmen_id, $konto, $bez, $kontoart_id, $k_gruppe_id)
    {
        if (!$this->check_konto_exists($konto, $kontenrahmen_id)) {
            $k_id = last_id2("KONTENRAHMEN_KONTEN", "KONTENRAHMEN_KONTEN_ID") + 1;
            $db_abfrage = "INSERT INTO KONTENRAHMEN_KONTEN VALUES (NULL, '$k_id', '$konto','$bez', '$k_gruppe_id', '$kontoart_id', '$kontenrahmen_id',  '1')";
            DB::insert($db_abfrage);
            /* Protokollieren */
            $last_dat = DB::getPdo()->lastInsertId();
            protokollieren('KONTENRAHMEN_KONTEN', $last_dat, '0');
        } else {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\InfoMessage("$bez exisitiert schon")
            );
        }
    }

    function check_konto_exists($konto, $kontenrahmen_id)
    {
        $result = DB::select("SELECT * FROM KONTENRAHMEN_KONTEN WHERE AKTUELL = '1' && KONTO='$konto' && KONTENRAHMEN_ID='$kontenrahmen_id'");
        return !empty($result);
    }

    function form_kostenkonto_aendern($konto_dat)
    {
        $this->get_kostenkonto_infos($konto_dat);
        session()->put('kontenrahmen_id', $this->kontenrahmen_id);
        session()->put('k_kontoart_id', $this->kontoart_id);
        session()->put('k_gruppen_id', $this->gruppen_id);
        $f = new formular ();
        $f->erstelle_formular("Kostenkonto ändern", NULL);
        $this->dropdown_kontenrahmen('Kontenrahmen', 'kontenrahmen_id', 'kontenrahmen_id');
        $f->text_feld("Kostenkonto", "konto", "$this->konto", "10", 'konto', '');
        $f->text_feld("Kostenkontobezeichnung", "bez", "$this->konto_bez", "50", 'bez', '');
        $this->dropdown_k_arten('Kontoart', 'kontoart_id', 'kontoart_id');
        $this->dropdown_k_gruppen('Gruppe', 'k_gruppe', 'k_gruppe');
        $f->hidden_feld("dat", "$konto_dat");
        $f->hidden_feld("option", "konto_ae_send");
        $f->send_button("submit_konto", "Änderung speichern");
        $f->ende_formular();
    }

    function get_kostenkonto_infos($dat)
    {
        unset ($this->dat);
        unset ($this->konto);
        unset ($this->konto_bez);
        unset ($this->kontoart_id);
        unset ($this->kontenrahmen_id);
        unset ($this->gruppe_id);

        $result = DB::select("SELECT *  FROM  KONTENRAHMEN_KONTEN WHERE AKTUELL = '1' && KONTENRAHMEN_KONTEN_DAT='$dat' ORDER BY KONTENRAHMEN_KONTEN_DAT 	LIMIT 0,1");
        $numrows = count($result);

        if ($numrows) {
            $row = $result[0];
            $this->dat = $row ['KONTENRAHMEN_KONTEN_DAT'];
            $this->konto = $row ['KONTO'];
            $this->konto_bez = $row ['BEZEICHNUNG'];
            $this->kontoart_id = $row ['KONTO_ART'];
            $this->kontenrahmen_id = $row ['KONTENRAHMEN_ID'];
            $this->gruppen_id = $row ['GRUPPE'];
        }
    }

    function kostenkonto_aendern($dat, $kontenrahmen_id, $konto, $bez, $kontoart_id, $k_gruppe_id)
    {

        /* Deaktivieren von DAT */
        $db_abfrage = "UPDATE KONTENRAHMEN_KONTEN SET AKTUELL='0' WHERE KONTENRAHMEN_KONTEN_DAT='$dat'";
        DB::update($db_abfrage);

        if (!$this->check_konto_exists($konto, $kontenrahmen_id)) {
            $k_id = last_id2("KONTENRAHMEN_KONTEN", "KONTENRAHMEN_KONTEN_ID") + 1;
            $db_abfrage = "INSERT INTO KONTENRAHMEN_KONTEN VALUES (NULL, '$k_id', '$konto','$bez', '$k_gruppe_id', '$kontoart_id', '$kontenrahmen_id',  '1')";
            DB::insert($db_abfrage);
            /* Protokollieren */
            $last_dat = DB::getPdo()->lastInsertId();
            protokollieren('KONTENRAHMEN_KONTEN', $last_dat, $dat);
        } else {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\InfoMessage("$bez exisitiert schon")
            );
        }
    }

    function gruppen_anzeigen()
    {
        $my_array = DB::select("SELECT *  FROM  KONTENRAHMEN_GRUPPEN WHERE AKTUELL = '1'  ORDER BY BEZEICHNUNG ASC");

        $numrows = count($my_array);
        if ($numrows > 0) {
            echo "<table class=\"sortable\">";
            echo "<tr><th>Gruppenbezeichnung</th></tr>";
            for ($a = 0; $a < count($my_array); $a++) {
                $bez = $my_array [$a] ['BEZEICHNUNG'];
                echo "<tr><td>$bez</td></tr>";
            } // end for
            echo "</table>";
        } else {
            echo "<b>Keine Kostenkontengruppen hinterlegt</b>";
            return FALSE;
        }
    }

    function form_gruppe_neu()
    {
        $f = new formular ();
        $f->erstelle_formular("Neuen Kostenkontengruppen erstellen", NULL);
        $f->text_feld("Gruppenbezeichnung", "g_bez", "", "50", 'g_bez', '');
        $f->hidden_feld("option", "g_bez_neu");
        $f->send_button("submit_g_bez", "Erstellen");
        $f->ende_formular();
    }

    function gruppe_speichern($g_bez)
    {
        if (!$this->check_gruppe_exists($g_bez)) {
            $g_id = last_id2("KONTENRAHMEN_GRUPPEN", "KONTENRAHMEN_GRUPPEN_ID") + 1;
            $db_abfrage = "INSERT INTO KONTENRAHMEN_GRUPPEN VALUES (NULL, '$g_id', '$g_bez',  '1')";
            DB::insert($db_abfrage);
            /* Protokollieren */
            $last_dat = DB::getPdo()->lastInsertId();
            protokollieren('KONTENRAHMEN_GRUPPEN', $last_dat, 0);
        } else {
            echo "Gruppenbezeichnung existiert schon";
        }
    }

    function check_gruppe_exists($g_bez)
    {
        $result = DB::select("SELECT *  FROM  KONTENRAHMEN_GRUPPEN WHERE AKTUELL = '1' && BEZEICHNUNG='$g_bez'");
        return !empty($result);
    }

    function form_kontoart_neu()
    {
        $f = new formular ();
        $f->erstelle_formular("Neue Kostenkontenart erstellen", NULL);
        $f->text_feld("Kontoart", "kontoart", "", "50", 'kontoart', '');
        $f->hidden_feld("option", "kontoart_neu1");
        $f->send_button("submit_ka", "Erstellen");
        $f->ende_formular();
    }

    function kontoarten_anzeigen()
    {
        $my_array = DB::select("SELECT *  FROM  KONTENRAHMEN_KONTOARTEN WHERE AKTUELL = '1'  ORDER BY KONTOART ASC");

        $numrows = count($my_array);
        if ($numrows > 0) {
            echo "<table class=\"sortable\">";
            echo "<tr><th>Kostenkontoarten</th></tr>";
            for ($a = 0; $a < count($my_array); $a++) {
                $bez = $my_array [$a] ['KONTOART'];
                echo "<tr><td>$bez</td></tr>";
            } // end for
            echo "</table>";
        } else {
            echo "<b>Keine Kostenkontenarten hinterlegt</b>";
            return FALSE;
        }
    }

    function kontoart_speichern($kontoart)
    {
        if (!$this->check_kontoart_exists($kontoart)) {
            $k_id = last_id2("KONTENRAHMEN_KONTOARTEN", "KONTENRAHMEN_KONTOART_ID") + 1;
            $db_abfrage = "INSERT INTO KONTENRAHMEN_KONTOARTEN VALUES (NULL, '$k_id', '$kontoart',  '1')";
            DB::insert($db_abfrage);
            /* Protokollieren */
            $last_dat = DB::getPdo()->lastInsertId();
            protokollieren('KONTENRAHMEN_KONTOARTEN', $last_dat, 0);
        } else {
            echo "Kostenkontoart <b>$kontoart</b> existiert schon.";
        }
    }

    function check_kontoart_exists($kontoart)
    {
        $result = DB::select("SELECT *  FROM KONTENRAHMEN_KONTOARTEN WHERE AKTUELL = '1' && KONTOART='$kontoart'");
        return !empty($result);
    }

    function form_kontenrahmen_zuweisen()
    {
        $f = new formular ();
        $f->erstelle_formular("Kontenrahmen  zuweisen", NULL);
        $this->dropdown_kontenrahmen('Kontenrahmen wählen', 'kontenrahmen_id', 'kontenrahmen_id');
        $b = new buchen ();
        $js_typ = "onchange=\"list_kostentraeger('list_kostentraeger', this.value)\"";
        // $js_typ='';
        $b->dropdown_kostentreager_typen('Kostenträgertyp', 'kostentraeger_typ', 'kostentraeger_typ', $js_typ);
        $js_id = "";
        $b->dropdown_kostentreager_ids('Kostenträger', 'kostentraeger_id', 'dd_kostentraeger_id', $js_id);
        // $this->dropdown_geldkonten_alle('Geldkonto wählen', 'geldkonto_id', 'geldkonto_id');
        $f->hidden_feld("option", "zuweisen_kr");
        $f->send_button("submit_kr", "Zuweisen");
        $f->ende_formular();
    }

    function zuweisung_speichern($kos_typ, $kos_bez, $kontenrahmen_id)
    {
        $b = new buchen ();
        $kos_id = $b->kostentraeger_id_ermitteln($kos_typ, $kos_bez);
        if (!$this->check_zuweisung_exists($kos_typ, $kos_id, $kontenrahmen_id)) {
            $id = last_id2("KONTENRAHMEN_ZUWEISUNG", "ID") + 1;
            $db_abfrage = "INSERT INTO KONTENRAHMEN_ZUWEISUNG VALUES (NULL, '$id', '$kos_typ','$kos_id', '$kontenrahmen_id',  '1')";
            DB::insert($db_abfrage);
            /* Protokollieren */
            $last_dat = DB::getPdo()->lastInsertId();
            protokollieren('KONTENRAHMEN_ZUWEISUNG', $last_dat, 0);
        } else {
            echo "Zuweisung des Kontenrahmens existiert schon";
        }
    }

    function check_zuweisung_exists($kos_typ, $kos_id, $kontenrahmen_id)
    {
        $result = DB::select("SELECT *  FROM  KONTENRAHMEN_ZUWEISUNG WHERE AKTUELL = '1' && KONTENRAHMEN_ID='$kontenrahmen_id' && TYP='$kos_typ' && TYP_ID='$kos_id'");
        return !empty($result);
    }

    function zuweisung_loeschen($dat)
    {
        $db_abfrage = "UPDATE KONTENRAHMEN_ZUWEISUNG SET AKTUELL='0' WHERE DAT='$dat'";
        DB::update($db_abfrage);
        /* Protokollieren */
        protokollieren('KONTENRAHMEN_ZUWEISUNG', $dat, $dat);
    }
} // end class k_rahmen