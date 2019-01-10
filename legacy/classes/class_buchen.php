<?php

class buchen
{
    var $globalMultisortVar = array();
    public $summe_konto_buchungen;
    public $akt_betrag_punkt;
    public $akt_konto_bezeichnung;
    public $konto_art_bezeichnung;
    public $akt_buch_id;
    public $g_buchungsnummer;
    public $geldkonto_id;
    public $akt_datum;
    public $akt_auszugsnr;
    public $akt_erfass_nr;
    public $akt_betrag_komma;
    public $akt_mwst_anteil_komma;
    public $kostentraeger_typ;
    public $kostentraeger_id;
    public $kostenkonto;
    public $akt_vzweck;
    public $akt_buch_dat;
    public $summe_konto_buchungen_a;
    public $summe_mwst;
    public $footer_zahlungshinweis;
    public $akt_mwst_anteil;
    public $summe_mwst_a;

    function geldkonto_auswahl()
    {
        session()->put('url.intended', URL::previous());

        if (!session()->has('geldkonto_id')) {
            $geld_konten_arr = $this->alle_geldkonten_arr();
            $anzahl_objekte = count($geld_konten_arr);
            if (!empty($geld_konten_arr)) {
                echo "<p class=\"geldkonto_auswahl\">";
                for ($i = 0; $i < $anzahl_objekte; $i++) {
                    $konto_id = $geld_konten_arr [$i] ['KONTO_ID'];
                    $gk = new gk ();
                    if ($gk->check_zuweisung_kos_typ($konto_id, 'Objekt', '')) {
                        $sortiert [] = $geld_konten_arr [$i];
                    } else {

                        $unsortiert [] = $geld_konten_arr [$i];
                    }
                }
                echo "<table class=\"sortable\">";
                echo "<tr><th>GELDKONTEN DER OBJEKTE</th></tr>";
                $z = 0;
                // Aufruf von array_multisort() mit dem Array, das sortiert werden soll und den entsprechenden Flags
                $records = array_sortByIndex($sortiert, 'BEZEICHNUNG');
                $sortiert = $records;
                unset ($records);
                for ($i = 0; $i < count($sortiert); $i++) {
                    $z++;
                    $konto_id = $sortiert [$i] ['KONTO_ID'];
                    $bez = $sortiert [$i] ['BEZEICHNUNG'];
                    echo "<tr class=\"zeile$z\"><td><a class=\"objekt_auswahl_buchung\" href='" . route('web::geldkonten::select', [$konto_id]) . "'>$bez</a>&nbsp;</td></tr>";
                    if ($z == 2) {
                        $z = 0;
                    }
                }

                echo "</table>";

                echo "<table>";
                echo "<tr><th>ANDERE GELDKONTEN</th></tr>";
                $z = 0;

                for ($i = 0; $i < count($unsortiert); $i++) {
                    $z++;
                    $konto_id = $unsortiert [$i] ['KONTO_ID'];
                    $bez = $unsortiert [$i] ['BEZEICHNUNG'];
                    echo "<tr class=\"zeile$z\"><td><a class=\"objekt_auswahl_buchung\" href='" . route('web::geldkonten::select', [$konto_id]) . "'>$bez</a>&nbsp;</td></tr>";
                    if ($z == 2) {
                        $z = 0;
                    }
                }
                echo "</table>";
                echo "</p>";
            } else {
                echo "Keine Geldkonten";
            }
        }
    }

    function alle_geldkonten_arr()
    {
        $result = DB::select("SELECT GELD_KONTEN.KONTO_ID,GELD_KONTEN.BEZEICHNUNG, GELD_KONTEN.BEGUENSTIGTER, GELD_KONTEN.KONTONUMMER, GELD_KONTEN.BLZ FROM GELD_KONTEN_ZUWEISUNG, GELD_KONTEN WHERE GELD_KONTEN.KONTO_ID = GELD_KONTEN_ZUWEISUNG.KONTO_ID && GELD_KONTEN_ZUWEISUNG.AKTUELL = '1' && GELD_KONTEN.AKTUELL = '1' GROUP BY GELD_KONTEN.KONTO_ID  ORDER BY GELD_KONTEN.KONTO_ID ASC");
        return $result;
    }

    function geldkonto_header()
    {
        $link_kontoauszug = "<a href='" . route('web::buchen::legacy', ['option' => 'kontoauszug_form']) . "'>Kontrolldaten zum Kontoauszug eingeben</a>";
        $link_sepa_ls = "<a href='" . route('web::sepa::legacy', ['option' => 'ls_auto_buchen']) . "'>LS-Autobuchen</a>";
        $aendern_link = "<a href='" . route('web::buchen::legacy', ['option' => 'geldkonto_aendern']) . "'>Geldkonto ändern</a>";
        $this->akt_konto_bezeichnung = $this->geld_konto_bezeichung(session()->get('geldkonto_id'));
        echo "Ausgewähltes Geldkonto -> $this->akt_konto_bezeichnung $aendern_link $link_kontoauszug $link_sepa_ls<br>";
        $geld = new geldkonto_info ();
        $kontostand_aktuell = nummer_punkt2komma($geld->geld_konto_stand(session()->get('geldkonto_id')));
        if (session()->has('temp_kontostand') && session()->has('temp_kontoauszugsnummer')) {
            $kontostand_temp = nummer_punkt2komma(session()->get('temp_kontostand'));
            echo "<h3>Kontostand am " . session()->get('temp_datum') . " laut Kontoauszug " . session()->get('temp_kontoauszugsnummer') . " war $kontostand_temp €</h3>";
        }
        if ($kontostand_aktuell == $kontostand_temp) {
            echo "<h3>Kontostand aktuell: $kontostand_aktuell €</h3>";
        } else {
            echo "<h3 style=\"color:red\">Kontostand aktuell: $kontostand_aktuell €</h3>";
        }
    }

    function geld_konto_bezeichung($id)
    {
        $result = DB::select("SELECT GELD_KONTEN.BEZEICHNUNG FROM GELD_KONTEN WHERE  KONTO_ID='$id' && GELD_KONTEN.AKTUELL = '1' ORDER BY KONTO_DAT DESC LIMIT 0,1");
        if (!empty($result)) {
            return $result[0]['BEZEICHNUNG'];
        } else {
            return FALSE;
        }
    }

    function dropdown_ra_buch($kos_typ, $kos_id, $anzahl = 100, $rnr_kurz)
    {
        $result = DB::select("SELECT * FROM RECHNUNGEN WHERE AUSSTELLER_ID='$kos_id' && AUSSTELLER_TYP='$kos_typ' && AKTUELL = '1' ORDER BY BELEG_NR DESC LIMIT 0,$anzahl");
        echo "<label for=\"geld_konto_dropdown\">Ausgangsbeleg</label>";
        echo "<select name=\"erf_nr\">";
        echo "<option value=\"OHNE BELEG\">Ohne Beleg</option>";
        if (!empty($result)) {
            foreach ($result as $row) {
                $rnr = ltrim(rtrim($row ['RECHNUNGSNUMMER']));
                $erf_nr = $row ['BELEG_NR'];
                if (!empty ($rnr_kurz) && $rnr == $rnr_kurz) {
                    echo "<option value=\"$erf_nr\" selected>$rnr</option>";
                } else {
                    echo "<option value=\"$erf_nr\">$rnr</option>";
                }
            }
        }
        echo "</select>";
    }

    function einnahmen_ausgaben($geldkonto_id, $jahr)
    {
        $k = new kontenrahmen ();
        $k_id = $k->get_kontenrahmen('GELDKONTO', $geldkonto_id);

        $konten_arr = $this->konten_aus_buchungen($geldkonto_id);
        echo "<pre>";
        print_r($konten_arr);
        $anz = count($konten_arr);
        for ($a = 0; $a < $anz; $a++) {
            $konto = $konten_arr [$a] ['KONTO'];
            $k->konto_informationen2($konto, $k_id);
            $this->konto_art_bezeichnung [] = $k->konto_art_bezeichnung;
        }
        $konto_arten_arr = array_unique($this->konto_art_bezeichnung);
        unset ($this->konto_art_bezeichnung);
        print_r($konto_arten_arr);
        $konto_arten_arr = array_values($konto_arten_arr);
        print_r($konto_arten_arr);

        $anz_art = count($konto_arten_arr);
        $z = 0;
        for ($a = 0; $a < $anz; $a++) {
            $konto = $konten_arr [$a] ['KONTO'];
            $k->konto_informationen2($konto, $k_id);

            for ($b = 0; $b < $anz_art; $b++) {
                $art = $konto_arten_arr [$b];
                if ($art == $k->konto_art_bezeichnung) {
                    $new_arr [$art] [$z] ['KONTO'] = $konto;
                    $new_arr [$art] [$z] ['BEZ'] = $k->konto_bezeichnung;
                    $new_arr [$art] [$z] ['SUMME_A'] = nummer_punkt2komma_t($this->summe_kontobuchungen_jahr($geldkonto_id, $konto, $jahr));
                    $new_arr [$art] [$z] ['SUMME'] = $this->summe_kontobuchungen_jahr($geldkonto_id, $konto, $jahr);
                    $z++;
                }
            }
        }

        print_r($new_arr);

        ob_clean(); // ausgabepuffer leeren
        $pdf = new Cezpdf ('a4', 'portrait');
        $bpdf = new b_pdf ();
        $bpdf->b_header($pdf, 'Partner', session()->get('partner_id'), 'portrait', 'Helvetica.afm', 6);
        for ($b = 0; $b < $anz_art; $b++) {
            $art = $konto_arten_arr [$b];
            $pdf->ezTable($new_arr [$art]);
            $pdf->ezSetDy(-12); // abstand
        }
        ob_end_clean(); // ausgabepuffer leeren
        $pdf->ezStream();
    }

    function konten_aus_buchungen($geldkonto_id)
    {
        $result = DB::select("SELECT KONTENRAHMEN_KONTO AS KONTO FROM GELD_KONTO_BUCHUNGEN WHERE GELDKONTO_ID='$geldkonto_id' && AKTUELL='1' GROUP BY KONTENRAHMEN_KONTO ORDER BY KONTENRAHMEN_KONTO ASC");
        return $result;
    }

    function summe_kontobuchungen_jahr($geldkonto_id, $kostenkonto, $jahr)
    {
        $result = DB::select("SELECT SUM(BETRAG) AS SUMME FROM GELD_KONTO_BUCHUNGEN WHERE GELDKONTO_ID='$geldkonto_id' && KONTENRAHMEN_KONTO = '$kostenkonto' && ( DATE_FORMAT( DATUM, '%Y' ) = '$jahr') && AKTUELL='1'");
        $this->summe_konto_buchungen = 0.00;
        if (isset($result)) {
            $this->summe_konto_buchungen = $result[0]['SUMME'];
        } else {
            $this->summe_konto_buchungen = 0.00;
        }
        return $this->summe_konto_buchungen;
    }

    function dropdown_re_buch($kos_typ, $kos_id, $anzahl = 100, $rnr_kurz)
    {
        $result = DB::select("SELECT * FROM RECHNUNGEN WHERE EMPFAENGER_ID='$kos_id' && EMPFAENGER_TYP='$kos_typ' && AKTUELL = '1' ORDER BY BELEG_NR DESC LIMIT 0,$anzahl");
        echo "<label for=\"geld_konto_dropdown\">Eingangsbeleg</label>";
        echo "<select name=\"erf_nr\">";
        echo "<option value=\"OHNE BELEG\">Ohne Beleg</option>";
        if (!empty($result)) {
            foreach ($result as $row) {
                $rnr = ltrim(rtrim($row ['RECHNUNGSNUMMER']));
                $erf_nr = $row ['BELEG_NR'];
                if ($rnr == $rnr_kurz) {
                    echo "<option value=\"$erf_nr\" selected>$rnr</option>";
                } else {
                    echo "<option value=\"$erf_nr\">$rnr</option>";
                }
            }
        }
        echo "</select>";
    }

    function zb_buchen_form($geldkonto_id)
    {
        $geldkonto_id = session()->get('geldkonto_id');
        $form = new formular ();
        $form->hidden_feld("geldkonto_id", "$geldkonto_id");
        if (!session()->has('temp_datum')) {
            $heute = date("d.m.Y");
        } else {
            $heute = session()->get('temp_datum');
        }

        session()->put('last_url', route('web::buchen::legacy', ['option' => 'kontoauszug_form'], false));

        $form->text_feld("Datum:", "datum", $heute, "10", 'datum', '');
        $form->text_feld("Kontoauszugsnummer:", "kontoauszugsnummer", session()->get('temp_kontoauszugsnummer'), "10", 'kontoauszugsnummer', '');
        $form->text_feld("R-Erfassungsnr:", "rechnungsnr", session()->get('temp_kontoauszugsnummer'), "10", 'rechnungsnr', '1200');
        $form->text_feld("Betrag:", "betrag", "-", "10", 'betrag', '');
        $js_mwst = "onclick=\"mwst_rechnen('betrag','mwst', '19')\" ondblclick=\"mwst_rechnen('betrag','mwst', '7')\"";
        $form->text_feld("MwSt-Anteil:", "mwst", "", "10", 'mwst', $js_mwst);
        $this->dropdown_kostenrahmen_nr('Kostenkonto', 'kostenkonto', 'GELDKONTO', $geldkonto_id, '');
        // $form->text_feld_inaktiv("Kontobezeichnung", "kontobezeichnung", "", "20", 'kontobezeichnung');
        // $form->text_feld_inaktiv("Kontoart", "kontoart", "", "20", 'kontoart');
        // $form->text_feld_inaktiv("Kostengruppe", "kostengruppe", "", "20", 'kostengruppe');
        $js_typ = "onchange=\"list_kostentraeger('list_kostentraeger', this.value)\"";
        // $js_typ='';
        $this->dropdown_kostentreager_typen('Kostenträgertyp', 'kostentraeger_typ', 'kostentraeger_typ', $js_typ);
        $js_id = "";
        $this->dropdown_kostentreager_ids('Kostenträger', 'kostentraeger_id', 'dd_kostentraeger_id', $js_id);
        $form->text_bereich('Buchungstext', 'vzweck', '', 15, 20, 'v_zweck_buchungstext');

        $form->send_button("submit_zb_buchen", "Buchen");
        $form->hidden_feld("option", "buchung_gesendet");
        $form->ende_formular();
    }

    /* Funktion zur Ermittlung der Geldkonten und Rückgabe als Array */

    function dropdown_kostenrahmen_nr($label, $name, $typ, $typ_id, $vorwahl_konto = '', $id = null)
    {
        if ($id == null) {
            $id = $name;
        }
        $konten_info = new kontenrahmen ();
        $js = "onchange=\"kostenkonto_vorwahl(this.value)\"";
        $konten_info->dropdown_kontorahmenkonten_vorwahl($label, $id, $name, $typ, $typ_id, $js, $vorwahl_konto);
    }

    function dropdown_kostentreager_typen($label, $name, $id, $js_action)
    {
        if (session()->has('kos_typ') && session()->has('kos_bez')) {
            $this->dropdown_kostentreager_typen_vw($label, $name, $id, $js_action, session()->get('kos_typ'));
        } else {
            echo "<div class='input-field'>";
            echo "<select name=\"$name\" id=\"$id\" size=1 $js_action>";
            echo "<option value=\"\">Bitte wählen</option>\n";
            echo "<option value=\"Objekt\">Objekt</option>\n";
            echo "<option value=\"Wirtschaftseinheit\">Wirtschaftseinheit</option>\n";
            echo "<option value=\"Haus\">Haus</option>\n";
            echo "<option value=\"Einheit\">Einheit</option>\n";
            // echo "<option value=\"Rechnung\">Rechnung</option>\n";
            echo "<option value=\"Partner\">Partner</option>\n";
            echo "<option value=\"Mietvertrag\">Mietvertrag</option>\n";
            echo "<option value=\"GELDKONTO\">Geldkonto</option>\n";
            echo "<option value=\"Eigentuemer\">Kaufvertrag (WEG-Eigentümer)</option>\n";
            echo "<option value=\"Baustelle_ext\">Baustelle extern</option>\n";
            echo "<option value=\"Person\">Mitarbeiter</option>\n";
            echo "<option value=\"Lager\">Lager</option>\n";
            echo "<option value=\"ALLE\">Alle</option>\n";
            echo "</select>\n";
            echo "<label for=\"$id\">$label</label>\n";
            echo "</div>";
        }
    }

    function dropdown_kostentreager_typen_vw($label, $name, $id, $js_action, $vorwahl_typ)
    {
        echo "<div class='input-field'>";
        echo "<select name=\"$name\" id=\"$id\" size=1 $js_action>\n";
        $arr [0] ['typ'] = 'Objekt';
        $arr [0] ['bez'] = 'Objekt';
        $arr [1] ['typ'] = 'Haus';
        $arr [1] ['bez'] = 'Haus';
        $arr [2] ['typ'] = 'Einheit';
        $arr [2] ['bez'] = 'Einheit';
        $arr [3] ['typ'] = 'Baustelle_ext';
        $arr [3] ['bez'] = 'Baustelle extern';
        $arr [4] ['typ'] = 'Wirtschaftseinheit';
        $arr [4] ['bez'] = 'Wirtschaftseinheit';
        $arr [5] ['typ'] = 'Partner';
        $arr [5] ['bez'] = 'Partner';
        $arr [6] ['typ'] = 'Mietvertrag';
        $arr [6] ['bez'] = 'Mietvertrag';
        $arr [7] ['typ'] = 'GELDKONTO';
        $arr [7] ['bez'] = 'Geldkonto';
        $arr [8] ['typ'] = 'Eigentuemer';
        $arr [8] ['bez'] = 'Kaufvertrag (WEG-Eigentümer)';
        $arr [9] ['typ'] = 'Person';
        $arr [9] ['bez'] = 'Mitarbeiter';
        $arr [10] ['typ'] = 'Lager';
        $arr [10] ['bez'] = 'Lager';
        $arr [11] ['typ'] = 'ALLE';
        $arr [11] ['bez'] = 'Alle';

        echo "<option value=\"\">Bitte wählen</option>\n";

        for ($a = 0; $a < count($arr); $a++) {
            $typ = $arr [$a] ['typ'];
            $bez = $arr [$a] ['bez'];
            if ($vorwahl_typ == $typ) {
                echo "<option value=\"$typ\" selected>$bez</option>\n";
            } else {
                echo "<option value=\"$typ\">$bez</option>\n";
            }
        }

        echo "</select>\n";
        echo "<label for=\"$id\">$label</label>";
        echo "</div>";
    }

    /* Kostenträgerliste als dropdown */

    function dropdown_kostentreager_ids($label, $name, $id, $js_action)
    {
        if ($js_action == '') {
            $js_action = "onchange=\"drop_kos_register('kostentraeger_typ', 'dd_kostentraeger_id');\"";
        }
        if (session()->has('kos_typ')) {
            $kostentraeger_bez = session()->has('kos_bez') ? session()->get('kos_bez') : session()->get('kos_id');
            $kostentraeger_typ = session()->get('kos_typ');
            $this->dropdown_kostentraeger_bez_vw($label, $name, $id, $js_action, $kostentraeger_typ, $kostentraeger_bez);
        } else {
            echo "<div class='input-field'>";
            echo "<select name=\"$name\" id=\"$id\" size=1 $js_action>\n";
            echo "<option value=\"\">Bitte wählen</option>\n";
            echo "</select>\n";
            echo "<label for=\"$id\">$label</label>";
            echo "</div>";
        }
    }

    function dropdown_kostentraeger_bez_vw($label, $name, $id, $js_action, $kos_typ, $vorwahl_bez)
    {
        $typ = $kos_typ;

        echo "<div class='input-field'>";
        echo "<select name=\"$name\" id=\"$id\" size=1 $js_action>\n";
        echo "<option value=\"\">Bitte wählen</option>\n";
        if ($typ == 'Objekt') {
            $db_abfrage = "SELECT OBJEKT_KURZNAME, OBJEKT_ID FROM OBJEKT WHERE OBJEKT_AKTUELL='1' ORDER BY OBJEKT_KURZNAME ASC";
            $resultat = DB::select($db_abfrage);
            foreach ($resultat as $row) {
                if (!session()->has('geldkonto_id')) {
                    if ($vorwahl_bez == $row['OBJEKT_ID']) {
                        echo "<option value=\"$row[OBJEKT_ID]\" selected>$row[OBJEKT_KURZNAME]</option>";
                    } else {
                        echo "<option value=\"$row[OBJEKT_ID]\">$row[OBJEKT_KURZNAME]</option>";
                    }
                } else {

                    $gk = new gk ();
                    if ($gk->check_zuweisung_kos_typ(session()->get('geldkonto_id'), 'Objekt', $row['OBJEKT_ID'])) {
                        if ($vorwahl_bez == $row['OBJEKT_ID']) {
                            echo "<option value=\"$row[OBJEKT_ID]\" selected>$row[OBJEKT_KURZNAME]</option>";
                        } else {
                            echo "<option value=\"$row[OBJEKT_ID]\">$row[OBJEKT_KURZNAME]</option>";
                        }
                    }
                }
            }
        }

        if ($typ == 'Wirtschaftseinheit') {
            $db_abfrage = "SELECT W_NAME, W_ID FROM WIRT_EINHEITEN WHERE AKTUELL='1' ORDER BY W_NAME ASC";
            $resultat = DB::select($db_abfrage);
            if (is_numeric($vorwahl_bez)) {
                foreach ($resultat as $row) {
                    if ($vorwahl_bez == $row['W_ID']) {
                        echo "<option value=\"$row[W_NAME]\" selected>$row[W_NAME]</option>";
                    } else {
                        echo "<option value=\"$row[W_NAME]\">$row[W_NAME]</option>";
                    }
                }
            } else {
                foreach ($resultat as $row) {
                    if ($vorwahl_bez == $row['W_NAME']) {
                        echo "<option value=\"$row[W_NAME]\" selected>$row[W_NAME]</option>";
                    } else {
                        echo "<option value=\"$row[W_NAME]\">$row[W_NAME]</option>";
                    }
                }
            }
        }

        if ($typ == 'Haus') {
            $haeuserQuery = \App\Models\Haeuser::with('objekt')->defaultOrder();

            if (session()->has('geldkonto_id')) {
                $haeuserQuery->whereHas('objekt.bankkonten', function ($query) {
                    //todo check if fixed 'GELD_KONTEN.KONTO_ID' <-> 'KONTO_ID'
                    $query->where('GELD_KONTEN.KONTO_ID', session()->get('geldkonto_id'));
                });
            }

            $haeuser = $haeuserQuery->get();
            foreach ($haeuser as $haus) {
                if ($vorwahl_bez == $haus->HAUS_ID) {
                    echo "<option value=\"$haus->HAUS_ID\" selected>$haus->HAUS_STRASSE $haus->HAUS_NUMMER | " . $haus->objekt->OBJEKT_KURZNAME . "</option>";
                } else {
                    echo "<option value=\"$haus->HAUS_ID\">$haus->HAUS_STRASSE $haus->HAUS_NUMMER | " . $haus->objekt->OBJEKT_KURZNAME . "</option>";
                }
            }
        }

        if ($typ == 'Einheit') {
            $db_abfrage = "SELECT EINHEIT_KURZNAME, EINHEIT_ID FROM EINHEIT WHERE EINHEIT_AKTUELL='1' ORDER BY EINHEIT_KURZNAME ASC";
            $resultat = DB::select($db_abfrage);
            foreach ($resultat as $row) {
                if ($vorwahl_bez == $row['EINHEIT_ID']) {
                    echo "<option value=\"$row[EINHEIT_ID]\" selected>$row[EINHEIT_KURZNAME]</option>";
                } else {
                    echo "<option value=\"$row[EINHEIT_ID]\">$row[EINHEIT_KURZNAME]</option>";
                }
            }
        }

        if ($typ == 'Partner') {
            $db_abfrage = "SELECT PARTNER_NAME, PARTNER_ID FROM PARTNER_LIEFERANT WHERE AKTUELL='1' ORDER BY PARTNER_NAME ASC";
            $resultat = DB::select($db_abfrage);
            foreach ($resultat as $row) {
                $PARTNER_NAME1 = str_replace('<br>', ' ', $row['PARTNER_NAME']);
                if (!is_numeric($vorwahl_bez)) {
                    if ($vorwahl_bez == $PARTNER_NAME1) {
                        echo "<option value=\"$row[PARTNER_ID]\" selected>$PARTNER_NAME1</option>";
                    } else {
                        echo "<option value=\"$row[PARTNER_ID]\">$PARTNER_NAME1</option>";
                    }
                } else {
                    if ($vorwahl_bez == $row['PARTNER_ID']) {
                        echo "<option value=\"$row[PARTNER_ID]\" selected>$PARTNER_NAME1</option>";
                    } else {
                        echo "<option value=\"$row[PARTNER_ID]\">$PARTNER_NAME1</option>";
                    }
                }
            }
        }

        if ($typ == 'Mietvertrag') {
            $einheiten = \App\Models\Einheiten::defaultOrder()
                ->has('mietvertraege')
                ->with(['mietvertraege' => function ($query) {
                    $query->defaultOrder();
                }, 'mietvertraege.mieter' => function ($query) {
                    $query->defaultOrder();
                }]);
            if (session()->has('geldkonto_id')) {
                $einheiten->whereHas('haus.objekt.bankkonten', function ($query) {
                    //todo check if fixed 'GELD_KONTEN.KONTO_ID' <-> 'KONTO_ID'
                    $query->where('GELD_KONTEN.KONTO_ID', session()->get('geldkonto_id'));
                });
            }
            $einheiten = $einheiten->get();
            foreach ($einheiten as $einheit) {
                foreach ($einheit->mietvertraege as $mietvertrag) {
                    if (!$mietvertrag->isActive('<=')) {
                        if ($vorwahl_bez == $mietvertrag->MIETVERTRAG_ID) {
                            echo "<option value='$mietvertrag->MIETVERTRAG_ID' selected>NEUMIETER: $einheit->EINHEIT_KURZNAME | $mietvertrag->mieter_namen</option>\n";
                        } else {
                            echo "<option value='$mietvertrag->MIETVERTRAG_ID'>NEUMIETER: $einheit->EINHEIT_KURZNAME | $mietvertrag->mieter_namen</option>\n";
                        }
                    } elseif ($mietvertrag->isActive()) {
                        if ($vorwahl_bez == $mietvertrag->MIETVERTRAG_ID) {
                            echo "<option value='$mietvertrag->MIETVERTRAG_ID' selected>$einheit->EINHEIT_KURZNAME | $mietvertrag->mieter_namen</option>\n";
                        } else {
                            echo "<option value='$mietvertrag->MIETVERTRAG_ID'>$einheit->EINHEIT_KURZNAME | $mietvertrag->mieter_namen</option>\n";
                        }
                    } elseif ($mietvertrag->isActive('<')) {
                        if ($vorwahl_bez == $mietvertrag->MIETVERTRAG_ID) {
                            echo "<option value='$mietvertrag->MIETVERTRAG_ID' selected>ALTMIETER: $einheit->EINHEIT_KURZNAME | $mietvertrag->mieter_namen</option>\n";
                        } else {
                            echo "<option value='$mietvertrag->MIETVERTRAG_ID'>ALTMIETER: $einheit->EINHEIT_KURZNAME | $mietvertrag->mieter_namen</option>\n";
                        }
                    }
                }
            }
        }

        if ($typ == 'GELDKONTO') {
            $db_abfrage = "SELECT KONTO_ID, BEZEICHNUNG  FROM `GELD_KONTEN`  WHERE AKTUELL='1' ORDER BY BEZEICHNUNG ASC";
            $resultat = DB::select($db_abfrage);
            foreach ($resultat as $row) {
                if ($vorwahl_bez == $row['BEZEICHNUNG']) {
                    echo "<option value=\"$row[BEZEICHNUNG]\" selected>$row[BEZEICHNUNG]</option>";
                } else {
                    echo "<option value=\"$row[BEZEICHNUNG]\">$row[BEZEICHNUNG]</option>";
                }
            }
        }

        if ($typ == 'Lager') {
            $db_abfrage = "SELECT LAGER_ID, LAGER_NAME  FROM `LAGER`  WHERE AKTUELL='1' ORDER BY LAGER_NAME ASC";
            $resultat = DB::select($db_abfrage);
            if (is_numeric($vorwahl_bez)) {
                foreach ($resultat as $row) {
                    if ($vorwahl_bez == $row['LAGER_ID']) {
                        echo "<option value=\"$row[LAGER_ID]\" selected>$row[LAGER_NAME]</option>";
                    } else {
                        echo "<option value=\"$row[LAGER_ID]\">$row[LAGER_NAME]</option>";
                    }
                }
            } else {
                foreach ($resultat as $row) {
                    if ($vorwahl_bez == $row['LAGER_NAME']) {
                        echo "<option value=\"$row[LAGER_ID]\" selected>$row[LAGER_NAME]</option>";
                    } else {
                        echo "<option value=\"$row[LAGER_ID]\">$row[LAGER_NAME]</option>";
                    }
                }
            }
        }

        if ($typ == 'Baustelle_ext') {
            $db_abfrage = "SELECT ID, BEZ  FROM `BAUSTELLEN_EXT`  WHERE AKTUELL='1' ORDER BY BEZ ASC";
            $resultat = DB::select($db_abfrage);
            if (is_numeric($vorwahl_bez)) {
                foreach ($resultat as $row) {
                    if ($vorwahl_bez == $row['ID']) {
                        echo "<option value=\"$row[BEZ]\" selected>$row[BEZ]</option>";
                    } else {
                        echo "<option value=\"$row[BEZ]\">$row[BEZ]</option>";
                    }
                }
            } else {
                foreach ($resultat as $row) {
                    if ($vorwahl_bez == $row['BEZ']) {
                        echo "<option value=\"$row[BEZ]\" selected>$row[BEZ]</option>";
                    } else {
                        echo "<option value=\"$row[BEZ]\">$row[BEZ]</option>";
                    }
                }
            }
        }

        if ($typ == 'Eigentuemer') {
            echo "VORWAHL $vorwahl_bez";

            $gk_arr_objekt = $this->get_objekt_arr_gk(session()->get('geldkonto_id'));
            if (!empty($gk_arr_objekt)) {

                $db_abfrage = "SELECT ID, WEG_MITEIGENTUEMER.EINHEIT_ID, EINHEIT_KURZNAME, EINHEIT.HAUS_ID, HAUS.OBJEKT_ID FROM `WEG_MITEIGENTUEMER` , EINHEIT, HAUS WHERE EINHEIT_AKTUELL = '1' && HAUS_AKTUELL = '1' && AKTUELL = '1' && EINHEIT.HAUS_ID = HAUS.HAUS_ID && EINHEIT.EINHEIT_ID = WEG_MITEIGENTUEMER.EINHEIT_ID ";

                $anz_gk = count($gk_arr_objekt);
                for ($go = 0; $go < $anz_gk; $go++) {
                    $oo_id = $gk_arr_objekt [$go];
                    $db_abfrage .= "&& HAUS.OBJEKT_ID=$oo_id[KOSTENTRAEGER_ID] ";
                }

                $db_abfrage .= "GROUP BY ID ORDER BY  EINHEIT_KURZNAME ASC ";
            } else {

                $db_abfrage = "SELECT ID, WEG_MITEIGENTUEMER.EINHEIT_ID, EINHEIT_KURZNAME, EINHEIT.HAUS_ID, HAUS.OBJEKT_ID FROM `WEG_MITEIGENTUEMER` , EINHEIT, HAUS WHERE EINHEIT_AKTUELL = '1' && HAUS_AKTUELL = '1' && AKTUELL = '1' && EINHEIT.HAUS_ID = HAUS.HAUS_ID && EINHEIT.EINHEIT_ID = WEG_MITEIGENTUEMER.EINHEIT_ID GROUP BY ID ORDER BY  EINHEIT_KURZNAME ASC";
            }

            $result = DB::select($db_abfrage);
            foreach ($result as $row) {
                $weg = new weg ();
                $ID = $row ['ID'];
                $einheit_id = $row ['EINHEIT_ID'];
                $weg->get_eigentuemer_namen($row ['ID']); // $weg->eigentuemer_name_str
                $einheit_kn = $row ['EINHEIT_KURZNAME'];

                if (!session()->has('geldkonto_id')) {
                    if ($vorwahl_bez == $ID) {
                        echo "<option value=\"$ID\" selected>$einheit_kn | $weg->eigentuemer_name_str</option>";
                    } else {
                        echo "<option value=\"$ID\" >$einheit_kn | $weg->eigentuemer_name_str</option>";
                    }
                } else {
                    $eee = new einheit ();
                    $eee->get_einheit_info($einheit_id);
                    $gk = new gk ();
                    if ($gk->check_zuweisung_kos_typ(session()->get('geldkonto_id'), 'Objekt', $eee->objekt_id)) {
                        if ($vorwahl_bez == $ID) {
                            echo "<option value=\"$ID\" selected>$einheit_kn | $weg->eigentuemer_name_str</option>";
                        } else {
                            echo "<option value=\"$ID\" >$einheit_kn | $weg->eigentuemer_name_str</option>";
                        }
                    }
                }
            }
        }

        if ($typ == 'Person') {
            $users = \App\Models\Person::has('jobsAsEmployee')->defaultOrder()->get();
            foreach ($users as $user) {
                if ($vorwahl_bez == $user->id) {
                    echo "<option value='$user->id' selected>$user->name</option>";
                } else {
                    echo "<option value='$user->id'>$user->name</option>";
                }
            }
        }

        echo "</select>\n<label for=\"$id\">$label</label>";
        echo "</div>";
    }

    /* Kostenträgerliste als dropdown */

    function get_objekt_arr_gk($gk_id)
    {
        $db_abfrage = "SELECT KOSTENTRAEGER_ID  FROM  GELD_KONTEN_ZUWEISUNG WHERE AKTUELL = '1' && KONTO_ID='$gk_id' && KOSTENTRAEGER_TYP='Objekt'";
        $result = DB::select($db_abfrage);
        return $result;
    }

    function geldbuchungs_dat_deaktivieren($buchungs_dat)
    {
        DB::update("UPDATE GELD_KONTO_BUCHUNGEN SET AKTUELL='0' WHERE GELD_KONTO_BUCHUNGEN_DAT='$buchungs_dat'");
        protokollieren('GELD_KONTO_BUCHUNGEN_DAT', $buchungs_dat, $buchungs_dat);
        echo "Alter Eintrag deaktiviert<br>";
    }

    function speichern_in_geldbuchungen($geldbuchung_id, $g_buchungsnummer, $betrag, $datum, $kostentraeger_typ, $kostentraeger_bez, $vzweck, $kostenkonto, $geldkonto_id, $kontoauszugsnr, $erfass_nr, $mwst = '0.00', $alt_dat = '')
    {
        $buchung = new buchen ();

        if ($kostentraeger_typ != 'Rechnung' && $kostentraeger_typ != 'Mietvertrag') {
            $kostentraeger_id = $buchung->kostentraeger_id_ermitteln($kostentraeger_typ, $kostentraeger_bez);
        } else {
            $kostentraeger_id = $kostentraeger_bez;
        }

        if (!is_numeric($kostentraeger_id) or $kostentraeger_id == '0' or $kostentraeger_id == null or !$kostentraeger_id) {
            /* deaktivierte Buchung aktivieren */
            $db_abfrage = "UPDATE GELD_KONTO_BUCHUNGEN SET AKTUELL='1' WHERE GELD_KONTO_BUCHUNGEN_DAT='$alt_dat'";
            DB::update($db_abfrage);
            protokollieren('GELD_KONTO_BUCHUNGEN_DAT', $alt_dat, $alt_dat);
            throw new Exception("Fehler mit Kostenträgern, keine Änderung gespeichert!!!!");
        }

        $datum = date_german2mysql($datum);
        $datum_arr = explode('-', $datum);
        $t_jahr = $datum_arr [0];
        $t_monat = $datum_arr [1];
        $t_tag = $datum_arr [2];

        session()->put('t_tag', $t_tag);
        session()->put('t_monat', $t_monat);
        session()->put('t_jahr', $t_jahr);

        $betrag1 = nummer_komma2punkt($betrag);
        $mwst1 = nummer_komma2punkt($mwst);
        $db_abfrage = "INSERT INTO GELD_KONTO_BUCHUNGEN VALUES (NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, '1')";
        DB::insert($db_abfrage, [$geldbuchung_id, $g_buchungsnummer, $kontoauszugsnr, $erfass_nr, $betrag1, $mwst1, $vzweck, $geldkonto_id, $kostenkonto, $datum, $kostentraeger_typ, $kostentraeger_id]);
        weiterleiten(route('web::buchen::legacy', ['option' => 'buchungs_journal', 'monat' => $t_monat, 'jahr' => $t_jahr], false));
    }

    function kostentraeger_id_ermitteln($kostentraeger_typ, $kostentraeger_bez)
    {
        if (is_numeric($kostentraeger_bez)) {
            return $kostentraeger_bez;
        }
        if ($kostentraeger_typ == 'Objekt') {
            $obj = new objekt ();
            $obj->get_objekt_id($kostentraeger_bez);
            return $obj->objekt_id;
        }

        if ($kostentraeger_typ == 'Wirtschaftseinheit') {
            $w = new wirt_e ();
            $wirt_id = $w->get_id_from_wirte($kostentraeger_bez);
            return $wirt_id;
        }

        if ($kostentraeger_typ == 'Haus') {
            $haus = new haus ();

            $haus->get_haus_id($kostentraeger_bez);
            return $haus->haus_id;
        }
        if ($kostentraeger_typ == 'Einheit') {
            $einheit = new einheit ();
            $einheit->get_einheit_id($kostentraeger_bez);
            return $einheit->einheit_id;
        }
        if ($kostentraeger_typ == 'Partner') {
            $p = new partner ();
            $p->getpartner_id_name($kostentraeger_bez);
            return $p->partner_id;
        }
        if ($kostentraeger_typ == 'Mietvertrag') {
            $mv_arr = explode("*", $kostentraeger_bez);
            $mv_id = $mv_arr [2];
            // echo '<pre>';
            // print_r($mv_arr);
            return $mv_id;
        }

        if ($kostentraeger_typ == 'Eigentuemer') {
            $eig_arr = explode("*", $kostentraeger_bez);
            $eig_id = $eig_arr [1];
            // echo '<pre>';
            // print_r($mv_arr);
            return $eig_id;
        }

        if ($kostentraeger_typ == 'Baustelle_ext') {
            $s = new statistik ();
            return $s->get_baustelle_ext_id($kostentraeger_bez);
        }

        if ($kostentraeger_typ == 'GELDKONTO') {
            $gk = new gk ();
            return $gk->get_geldkonto_id($kostentraeger_bez);
        }

        if ($kostentraeger_typ == 'ALLE') {
            return '0';
        }

        if ($kostentraeger_typ == 'Person') {
            $be = new benutzer ();
            return $be->get_benutzer_id($kostentraeger_bez);
        }

        if ($kostentraeger_typ == 'Lager') {
            $la = new lager ();
            return $la->get_lager_id($kostentraeger_bez);
        }
    }

    /* Manuelle Buchung */

    function buchungsmaske_buchung_aendern($buchungs_dat)
    {
        $form = new formular ();
        $form->erstelle_formular("Buchung ändern", NULL);

        $this->geldbuchungs_dat_infos($buchungs_dat);
        $form->hidden_feld("buch_dat_alt", $buchungs_dat);
        $form->hidden_feld("akt_buch_id", $this->akt_buch_id);
        $form->hidden_feld("g_buchungsnummer", $this->g_buchungsnummer);
        $form->text_feld_inaktiv('Buchungsnr', 'g_buchungsnummer', $this->g_buchungsnummer, '10', 'Buchungsnr');

        $form->hidden_feld('geldkonto_id', $this->geldkonto_id);

        $form->text_feld('Datum', 'datum', $this->akt_datum, '10', 'datum', '');
        $form->text_feld('Kontoauszugsnr', 'kontoauszugsnr', $this->akt_auszugsnr, '10', 'kontoauszugsnr', '');
        $form->text_feld('Erfassungsnr', 'erfassungsnr', $this->akt_erfass_nr, '10', 'erfassungsnr', '');
        $form->text_feld('Betrag', 'betrag', $this->akt_betrag_komma, '10', 'betrag', '');
        $js_mwst = "onclick=\"mwst_rechnen('betrag','mwst', '19')\" ondblclick=\"mwst_rechnen('betrag','mwst', '7')\"";
        $form->text_feld("MwSt-Anteil:", "mwst", "$this->akt_mwst_anteil_komma", "10", 'mwst', $js_mwst);
        $form->text_feld_inaktiv('Kostenträger & Kontierung', 'info', "$this->kostentraeger_typ $this->kostentraeger_id | Kostenkonto: $this->kostenkonto", '50', '');
        $this->dropdown_kostenrahmen_nr('Kostenbkonto', 'kostenkonto', 'GELDKONTO', $this->geldkonto_id, $this->kostenkonto);
        $form->text_bereich('Verwendungszweck', 'vzweck', $this->akt_vzweck, 10, 5, 'vzweck');
        // $k = new kasse;
        // $akt_kostentraeger_bez = $k->kostentraeger_beschreibung($this->kostentraeger_typ, $this->kostentraeger_id);
        $r = new rechnung ();
        $akt_kostentraeger_bez = $r->kostentraeger_ermitteln($this->kostentraeger_typ, $this->kostentraeger_id);
        $akt_kostentraeger_bez = str_replace("<b>", "", $akt_kostentraeger_bez);
        $akt_kostentraeger_bez = str_replace("</b>", "", $akt_kostentraeger_bez);

        // if($this->kostentraeger_typ!='Rechnung' && $this->kostentraeger_typ!='Mietvertrag'){
        if ($this->kostentraeger_typ != 'Rechnung') {
            $buchung = new buchen ();
            $js_typ = "onchange=\"list_kostentraeger('list_kostentraeger', this.value)\"";
            $buchung->dropdown_kostentreager_typen_vw('Kostenträgertyp wählen', 'kostentraeger_typ', 'kostentraeger_typ', $js_typ, $this->kostentraeger_typ);

            $js_id = "";
            $buchung->dropdown_kostentraeger_bez_vw("Kostenträger $akt_kostentraeger_bez", 'kostentraeger_id', 'dd_kostentraeger_id', $js_id, $this->kostentraeger_typ, $this->kostentraeger_id);

        } else {
            $form->hidden_feld("kostentraeger_typ", $this->kostentraeger_typ);
            $form->hidden_feld("kostentraeger_id", $this->kostentraeger_id);
        }

        $form->hidden_feld("option", "geldbuchung_aendern1");
        $form->send_button("submit", "Änderungen speichern");
        $form->ende_formular();
    }

    /* Manuelle Buchung mit der Rechnung Brutto, Netto, Skonto */

    function geldbuchungs_dat_infos($buchungs_dat)
    {
        $result = DB::select("SELECT * FROM GELD_KONTO_BUCHUNGEN WHERE GELD_KONTO_BUCHUNGEN_DAT='$buchungs_dat' && AKTUELL='1' LIMIT 0,1");
        $row = $result[0];
        $this->akt_buch_dat = $row ['GELD_KONTO_BUCHUNGEN_DAT'];
        $this->akt_buch_id = $row ['GELD_KONTO_BUCHUNGEN_ID'];
        $this->g_buchungsnummer = $row ['G_BUCHUNGSNUMMER'];
        $this->akt_auszugsnr = $row ['KONTO_AUSZUGSNUMMER'];
        $this->akt_erfass_nr = $row ['ERFASS_NR'];
        unset ($this->akt_betrag_punkt);
        $this->akt_betrag_punkt = $row ['BETRAG'];
        $this->akt_betrag_komma = nummer_punkt2komma($row ['BETRAG']);
        $this->akt_datum = date_mysql2german($row ['DATUM']);
        $this->akt_vzweck = $row ['VERWENDUNGSZWECK'];
        $this->kostentraeger_typ = $row ['KOSTENTRAEGER_TYP'];
        $this->kostentraeger_id = $row ['KOSTENTRAEGER_ID'];
        $this->kostenkonto = $row ['KONTENRAHMEN_KONTO'];
        $this->geldkonto_id = $row ['GELDKONTO_ID'];
        $this->akt_mwst_anteil = $row ['MWST_ANTEIL'];
        $this->akt_mwst_anteil_komma = nummer_punkt2komma($row ['MWST_ANTEIL']);
    }

    function geldbuchung_speichern($datum, $kto_auszugsnr, $rechnungsnr, $betrag, $vzweck, $geldkonto_id, $kostentraeger_typ, $kostentraeger_bez, $kostenkonto, $mwst = '0.00')
    {
        $kostentraeger_id = $this->kostentraeger_id_ermitteln($kostentraeger_typ, $kostentraeger_bez);
        if (!is_numeric($kostentraeger_id) or $kostentraeger_id == '0' or $kostentraeger_id == null or !$kostentraeger_id) {
            throw new Exception("Es wurde nicht gebucht, Kostenträger unbekannt!");
        }
        /* alt */
        $buchung_id = $this->get_last_geldbuchung_id();
        /* neu */
        $datum_arr = explode('-', $datum);
        $jahr = $datum_arr ['0'];
        $g_buchungsnummer = $this->get_last_buchungsnummer_konto($geldkonto_id, $jahr);
        $g_buchungsnummer = $g_buchungsnummer + 1;

        $buchung_id = $buchung_id + 1;
        /* alt */
        // $db_abfrage = "INSERT INTO GELD_KONTO_BUCHUNGEN VALUES (NULL, '$buchung_id','$kto_auszugsnr', '$rechnungsnr', '$betrag', '$vzweck', '$geldkonto_id', '$kostenkonto', '$datum', '$kostentraeger_typ', '$kostentraeger_id', '1')";

        /* neu */
        DB::insert("INSERT INTO GELD_KONTO_BUCHUNGEN VALUES (NULL, '$buchung_id', '$g_buchungsnummer', '$kto_auszugsnr', '$rechnungsnr', '$betrag', '$mwst', ?, '$geldkonto_id', '$kostenkonto', '$datum', '$kostentraeger_typ', '$kostentraeger_id', '1')", [$vzweck]);
        echo "<h5>Neue Buchungsnummer erteilt: $g_buchungsnummer</h5>";

        /* Protokollieren */
        $last_dat = DB::getPdo()->lastInsertId();
        protokollieren('GELD_KONTO_BUCHUNGEN', $last_dat, '0');
        echo "<h3>Betrag von $betrag € wurde gebucht.</h3>";
        weiterleiten_in_sec(route('web::buchen::legacy', ['option' => 'zahlbetrag_buchen']), 1);
    }

    /* Ermitteln der letzten geldbuchungs_id ALT, buchungsnummer nacheinander */

    function get_last_geldbuchung_id()
    {
        $result = DB::select("SELECT GELD_KONTO_BUCHUNGEN_ID FROM GELD_KONTO_BUCHUNGEN ORDER BY GELD_KONTO_BUCHUNGEN_ID DESC LIMIT 0,1");
        $row = $result[0];
        return $row ['GELD_KONTO_BUCHUNGEN_ID'];
    }

    /* Ermitteln der letzten geldbuchungs_id NEU für jeder Geldkonto separat, jährlich ab 1 angefangen */

    function get_last_buchungsnummer_konto($geldkonto_id, $jahr)
    {
        $result = DB::select("SELECT G_BUCHUNGSNUMMER FROM GELD_KONTO_BUCHUNGEN WHERE GELDKONTO_ID='$geldkonto_id' && DATE_FORMAT(DATUM, '%Y' ) = '$jahr'  && `AKTUELL` = '1' ORDER BY G_BUCHUNGSNUMMER DESC LIMIT 0,1");
        $row = $result[0];
        return $row ['G_BUCHUNGSNUMMER'];
    }

    function geldbuchung_speichern_rechnung($datum, $kto_auszugsnr, $rechnungsnr, $betrag, $vzweck, $geldkonto_id, $kostentraeger_typ, $kostentraeger_id, $kostenkonto, $mwst = '0.00')
    {
        $datum_arr = explode('-', $datum);
        $jahr = $datum_arr ['0'];
        $g_buchungsnummer = $this->get_last_buchungsnummer_konto($geldkonto_id, $jahr);
        $g_buchungsnummer = $g_buchungsnummer + 1;
        $buchung_id = $this->get_last_geldbuchung_id();
        $buchung_id = $buchung_id + 1;
        $db_abfrage = "INSERT INTO GELD_KONTO_BUCHUNGEN VALUES (NULL, '$buchung_id', '$g_buchungsnummer', '$kto_auszugsnr', '$rechnungsnr', '$betrag','$mwst', '$vzweck', '$geldkonto_id', '$kostenkonto', '$datum', '$kostentraeger_typ', '$kostentraeger_id', '1')";
        DB::insert($db_abfrage);
        /* Protokollieren */
        $last_dat = DB::getPdo()->lastInsertId();
        protokollieren('GELD_KONTO_BUCHUNGEN', $last_dat, '0');
    }

    function form_kostenkonto_pdf()
    {
        if (!session()->has('geldkonto_id')) {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\InfoMessage('Erst Geldkonto wählen'), 0, null,
                route('web::buchen::legacy', ['option' => 'geldkonto_aendern'])
            );
        }
        if (!request()->has('submit_kostenkonto')) {
            $kr = new kontenrahmen ();
            $kontenrahmen_id = $kr->get_kontenrahmen('Geldkonto', session()->get('geldkonto_id'));
            $f = new formular ();
            $f->erstelle_formular('Kostenkonto als PDF', '');
            $kr->dropdown_konten_vom_rahmen('Kostenkonto wählen', 'kostenkonto', 'kk', '', $kontenrahmen_id);
            $f->text_feld("Anfangsdatum:", "anfangsdatum", "", "10", 'anfangsdatum', '');
            $f->text_feld("Enddatum:", "enddatum", "", "10", 'enddatum', '');
            $f->send_button("submit_kostenkonto", "Als PDF anzeigen");
            $f->ende_formular();
        } else {
            $von = date_german2mysql(request()->input('anfangsdatum'));
            $bis = date_german2mysql(request()->input('enddatum'));
            $kostenkonto = request()->input('kostenkonto');
            $abfrage = "SELECT * FROM GELD_KONTO_BUCHUNGEN WHERE AKTUELL='1' && GELDKONTO_ID='" . session()->get('geldkonto_id') . "' && KONTENRAHMEN_KONTO='$kostenkonto' && DATUM BETWEEN '$von' AND '$bis' ORDER BY DATUM ASC";
            $this->finde_buchungen_pdf($abfrage);
        }
    }

    function finde_buchungen_pdf($abfrage)
    {
        $result = DB::select($abfrage);
        if (!empty($result)) {
            ob_end_clean(); // ausgabepuffer leeren
            $pdf = new Cezpdf ('a4', 'portrait');
            $bpdf = new b_pdf ();
            $bpdf->b_header($pdf, 'Partner', session()->get('partner_id'), 'portrait', 'Helvetica.afm', 6);

            $g = new geldkonto_info ();
            $summe = 0;

            $zeile = 0;
            foreach ($result as $row) {
                $datum = date_mysql2german($row ['DATUM']);
                $g_buchungsnummer = $row ['G_BUCHUNGSNUMMER'];
                $betrag = nummer_punkt2komma($row ['BETRAG']);
                $vzweck = $row ['VERWENDUNGSZWECK'];
                $kos_typ = $row ['KOSTENTRAEGER_TYP'];
                $kos_id = $row ['KOSTENTRAEGER_ID'];
                $kostenkonto = $row ['KONTENRAHMEN_KONTO'];
                $geldkonto_id = $row ['GELDKONTO_ID'];
                $g->geld_konto_details($geldkonto_id);
                $r = new rechnung ();
                if ($kos_typ == 'Mietvertrag') {
                    $mv = new mietvertraege ();
                    $mv->get_mietvertrag_infos_aktuell($kos_id);
                    $kostentraeger_bezeichnung = $mv->personen_name_string_u;
                } else {
                    $kostentraeger_bezeichnung = $r->kostentraeger_ermitteln($kos_typ, $kos_id);
                    $kostentraeger_bezeichnung = substr($kostentraeger_bezeichnung, 0, 20);
                }
                $kostentraeger_bezeichnung = strip_tags($kostentraeger_bezeichnung);
                $g->geldkonto_bezeichnung_kurz = substr($g->geldkonto_bezeichnung_kurz, 0, 18);

                $table_arr [$zeile] ['GK'] = $g->geldkonto_bezeichnung;
                $table_arr [$zeile] ['KOS_BEZ'] = $kostentraeger_bezeichnung;
                $table_arr [$zeile] ['DATUM'] = $datum;
                $table_arr [$zeile] ['KONTO'] = $kostenkonto;
                $table_arr [$zeile] ['BUCHUNGSNR'] = $g_buchungsnummer;
                $table_arr [$zeile] ['BETRAG'] = $betrag;
                $table_arr [$zeile] ['VERWENDUNG'] = $vzweck;

                // echo "<tr><td>$g->geldkonto_bezeichnung<td>$kostentraeger_bezeichnung</td><td>$datum</td><td><b>$kostenkonto</b></td><td><b>$g_buchungsnummer</b></td><td>$betrag</td><td>$vzweck</td></tr>";
                $summe = $summe + nummer_komma2punkt($betrag);
                $zeile++;
            }
            $summe = nummer_punkt2komma($summe);
            // echo "<tr class=\"feldernamen\"><td colspan=5 align=\"right\"><b>SUMME</b></td><td colspan=\"2\"><b>$summe</b></td></tr>";

            $table_arr [$zeile + 1] ['BUCHUNGSNR'] = '<b>SUMME</b>';
            $table_arr [$zeile + 1] ['BETRAG'] = "<b>$summe</b>";

            $cols = array(
                'GK' => "Geldkonto",
                'KOS_BEZ' => "Zuordnung",
                'DATUM' => "Datum",
                'KONTO' => "Konto",
                'BUCHUNGSNR' => "Buchungsnr",
                'VERWENDUNG' => "Buchungstext",
                'BETRAG' => "Betrag"
            );
            if (!empty ($kostenkonto)) {
                $kt = new kontenrahmen ();
                $kontenrahmen_id = $kt->get_kontenrahmen('Geldkonto', $geldkonto_id);
                $kt->konto_informationen2($kostenkonto, $kontenrahmen_id);
                $ueberschrift = "Kostenkonto $kostenkonto - $kt->konto_bezeichnung";
            }

            $pdf->ezTable($table_arr, $cols, "$ueberschrift", array(
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
        } else {
            fehlermeldung_ausgeben("Keine Buchung gefunden");
        }
    }

    function kontoauszug_form()
    {
        echo "<hr><br>";
        $geldkonto_bezeichnung = $this->geld_konto_bezeichung(session()->get('geldkonto_id'));
        $form = new formular ();
        $heute = date("Y-m-d");
        $gestern = date_mysql2german(tage_minus($heute, 1));
        $form->erstelle_formular("Kontrolldaten eingeben / verändern", NULL);
        $form->text_feld_inaktiv("Geldkonto:", "geldkonto", $geldkonto_bezeichnung, "30", 'geldkonto', '');
        $form->text_feld("Datum:", "datum", $gestern, "10", 'datum', '');
        $jahr = date("Y");
        $last_kto = $this->get_last_kontoauszug(session()->get('geldkonto_id'), $jahr) + 1;
        $form->text_feld("Kontoauszugsnummer:", "kontoauszugsnummer", "$last_kto", "10", 'kontoauszugsnummer', '');
        $form->text_feld("Kontostand:", "kontostand", "", "10", 'kontostand', '');
        $form->send_button("submit", "Speichern");
        $form->hidden_feld("option", "kontoauszug_gesendet");
    }

    function get_last_kontoauszug($geldkonto_id, $jahr)
    {
        $result = DB::select("SELECT KONTO_AUSZUGSNUMMER FROM GELD_KONTO_BUCHUNGEN WHERE GELDKONTO_ID='$geldkonto_id' && DATE_FORMAT(DATUM, '%Y' ) = '$jahr'  && `AKTUELL` = '1' ORDER BY KONTO_AUSZUGSNUMMER DESC LIMIT 0,1");
        $row = $result[0];
        return $row ['KONTO_AUSZUGSNUMMER'];
    }

    function buchungsjournal_auszug($geldkonto_id, $date)
    {
        $my_array = DB::select("SELECT * FROM GELD_KONTO_BUCHUNGEN WHERE DATUM = '$date' && GELDKONTO_ID='$geldkonto_id' && AKTUELL='1' ORDER BY GELD_KONTO_BUCHUNGEN_ID DESC");
        $numrows = count($my_array);
        if (!empty($my_array)) {
            echo "<table class=\"sortable striped\">";
            echo "<tr><th>AUSZUG</th><th>DATUM</th><th>BETRAG</th><th>MWST</th><th>KONTO</th><th>BUCHUNGSNR</th><th>Verwendung</th><th>BUCHUNGSTEXT</th></tr>";
            $g_betrag = 0;
            for ($a = 0; $a < $numrows; $a++) {

                $b_dat = $my_array [$a] ['GELD_KONTO_BUCHUNGEN_DAT'];
                $g_buchungsnummer = $my_array [$a] ['G_BUCHUNGSNUMMER'];
                $kostenkonto = $my_array [$a] ['KONTENRAHMEN_KONTO'];
                $link_buchungsbeleg = "<a href='" . route('web::buchen::legacy', ['option' => 'geldbuchung_aendern', 'geldbuchung_dat' => $b_dat]) . "'>$g_buchungsnummer ändern</a>";
                $betrag = nummer_punkt2komma($my_array [$a] ['BETRAG']);
                $mwst = nummer_punkt2komma($my_array [$a] ['MWST_ANTEIL']);
                $g_betrag += $my_array [$a] ['BETRAG'];
                $vzweck = $my_array [$a] ['VERWENDUNGSZWECK'];
                $auszug = $my_array [$a] ['KONTO_AUSZUGSNUMMER'];
                $kostentraeger_typ = $my_array [$a] ['KOSTENTRAEGER_TYP'];
                $kostentraeger_id = $my_array [$a] ['KOSTENTRAEGER_ID'];
                if ($kostentraeger_typ == 'Mietvertrag') {
                    $mv = new mietvertraege ();
                    $mv->get_mietvertrag_infos_aktuell($kostentraeger_id);
                    $kostentraeger_bez = $mv->personen_name_string_u;
                } else {
                    $r = new rechnung ();
                    $kostentraeger_bez = $r->kostentraeger_ermitteln($kostentraeger_typ, $kostentraeger_id);
                }
                $datum = date_mysql2german($my_array [$a] ['DATUM']);
                echo "<tr><td>$auszug</td><td>$datum</td><td>$betrag</td><td>$mwst</td><td>$kostenkonto </td><td>$link_buchungsbeleg</td><td>$kostentraeger_bez</td><td> $vzweck</td></tr>";
            }
            echo "<tfoot><tr><td></td><td></td><td><b>$g_betrag €</b></td><td></td><td></td></tr></tfoot>";
            echo "</table>";
        }
    }

    function buchungsjournal_startzeit($geldkonto_id, $datum)
    {
        $datum_arr = explode('-', $datum);
        $jahr = $datum_arr [0];
        $monat = $datum_arr [1];
        if (!request()->has('sort')) {
            $my_array = DB::select("SELECT DATUM, GELD_KONTO_BUCHUNGEN_DAT, MWST_ANTEIL, GELD_KONTO_BUCHUNGEN_ID, G_BUCHUNGSNUMMER, BETRAG, VERWENDUNGSZWECK, KONTO_AUSZUGSNUMMER, ERFASS_NR, KONTENRAHMEN_KONTO, KOSTENTRAEGER_TYP, KOSTENTRAEGER_ID FROM GELD_KONTO_BUCHUNGEN WHERE GELDKONTO_ID='$geldkonto_id' && DATE_FORMAT(DATUM, '%Y-%m') = '$jahr-$monat' && AKTUELL='1' ORDER BY G_BUCHUNGSNUMMER DESC");
        } else {
            $sort = request()->input('sort');
            $my_array = DB::select("SELECT DATUM, GELD_KONTO_BUCHUNGEN_DAT, MWST_ANTEIL, GELD_KONTO_BUCHUNGEN_ID, G_BUCHUNGSNUMMER, BETRAG, VERWENDUNGSZWECK, KONTO_AUSZUGSNUMMER, ERFASS_NR, KONTENRAHMEN_KONTO,KOSTENTRAEGER_TYP, KOSTENTRAEGER_ID FROM GELD_KONTO_BUCHUNGEN WHERE GELDKONTO_ID='$geldkonto_id' && DATE_FORMAT(DATUM, '%Y-%m') = '$jahr-$monat' && AKTUELL='1' ORDER BY $sort ASC");
        }

        $datum_arr = explode("-", $datum);
        $numrows = count($my_array);
        if ($numrows > 0) {
            echo "<table class=\"sortable striped\">";

            echo "<tr><th>DATUM</th><th>ERF/AUSZ</th><th>AUSZUG</th><th>Konto</th><th>Betrag</th><th>MWST</th><th>Verwendung</th><th>BUCHUNGSNR</th><th>Buchungstext</th></tr>";

            for ($a = 0; $a < $numrows; $a++) {
                $datum = date_mysql2german($my_array [$a] ['DATUM']);
                $b_dat = $my_array [$a] ['GELD_KONTO_BUCHUNGEN_DAT'];
                $g_buchungsnummer = $my_array [$a] ['G_BUCHUNGSNUMMER'];
                $link_buchungsbeleg = "<a href='" . route('web::buchen::legacy', ['option' => 'geldbuchung_aendern', 'geldbuchung_dat' => $b_dat]) . "'>$g_buchungsnummer</a>";
                $betrag = nummer_punkt2komma($my_array [$a] ['BETRAG']);
                $mwst_anteil = nummer_punkt2komma($my_array [$a] ['MWST_ANTEIL']);
                $vzweck = $my_array [$a] ['VERWENDUNGSZWECK'];
                $auszug = $my_array [$a] ['KONTO_AUSZUGSNUMMER'];
                $erfass_nr = $my_array [$a] ['ERFASS_NR'];
                $kostenkonto = $my_array [$a] ['KONTENRAHMEN_KONTO'];
                $kostentraeger_typ = $my_array [$a] ['KOSTENTRAEGER_TYP'];
                $kostentraeger_id = $my_array [$a] ['KOSTENTRAEGER_ID'];
                // $k = new kasse;
                $r = new rechnung ();
                // $kostentraeger_bez = $k->kostentraeger_beschreibung($kostentraeger_typ, $kostentraeger_id);
                if ($kostentraeger_typ == 'Mietvertrag') {
                    $mv = new mietvertraege ();
                    $mv->get_mietvertrag_infos_aktuell($kostentraeger_id);
                    $kostentraeger_bez = $mv->personen_name_string_u;
                } else {
                    $kostentraeger_bez = $r->kostentraeger_ermitteln($kostentraeger_typ, $kostentraeger_id);
                }
                echo "<tr><td>$datum</td><td>$erfass_nr</td><td>$auszug</td><td>$kostenkonto</td><td>$betrag</td><td>$mwst_anteil</td><td>$kostentraeger_bez</td><td><b>$link_buchungsbeleg</b></td><td>$vzweck</td></tr>";
            }
            echo "</table>";
        }
    }

    /* ja steht für Jahr */

    function buchungsjournal_startzeit_druck($geldkonto_id, $datum)
    {
        $dat_arr = explode("-", $datum);
        $ja = $dat_arr [0];
        $mo = $dat_arr [1];

        if (!request()->has('sort')) {
            $my_array = DB::select("SELECT DATUM, GELD_KONTO_BUCHUNGEN_DAT, GELD_KONTO_BUCHUNGEN_ID, G_BUCHUNGSNUMMER, BETRAG, VERWENDUNGSZWECK, KONTO_AUSZUGSNUMMER, ERFASS_NR, KONTENRAHMEN_KONTO, KOSTENTRAEGER_TYP, KOSTENTRAEGER_ID FROM GELD_KONTO_BUCHUNGEN WHERE GELDKONTO_ID='$geldkonto_id' && DATE_FORMAT(DATUM, '%Y-%m') = '$ja-$mo' && AKTUELL='1' ORDER BY G_BUCHUNGSNUMMER ASC");
        } else {
            $sort = request()->input('sort');
            $my_array = DB::select("SELECT DATUM, GELD_KONTO_BUCHUNGEN_DAT, GELD_KONTO_BUCHUNGEN_ID, G_BUCHUNGSNUMMER, BETRAG, VERWENDUNGSZWECK, KONTO_AUSZUGSNUMMER, ERFASS_NR, KONTENRAHMEN_KONTO,KOSTENTRAEGER_TYP, KOSTENTRAEGER_ID FROM GELD_KONTO_BUCHUNGEN WHERE GELDKONTO_ID='$geldkonto_id' && DATE_FORMAT(DATUM, '%Y-%m') = '$ja-$mo'  && AKTUELL='1' ORDER BY $sort ASC");
        }
        $numrows = count($my_array);
        if ($numrows > 0) {
            /* Kontostand */
            $datum_ger = date_mysql2german($datum);
            $this->kontostand_tagesgenau_bis($geldkonto_id, $datum_ger);
            $this->summe_konto_buchungen_a = nummer_punkt2komma($this->summe_konto_buchungen);

            $gk = new geldkonto_info ();
            $gk->geld_konto_details($geldkonto_id);
            $beguenstigter = $gk->konto_beguenstigter;

            $p = new partners ();
            $p->get_partner_id($beguenstigter);
            $partner_id = $p->partner_id;

            echo "<table id=\"positionen_tab\" class='striped'>\n";
            echo "<thead>";
            echo "<tr class=feldernamen>";
            echo "<th scopr=\"col\" id=\"tr_ansehen\">Datum</th>";
            echo "<th >Erf.Nr</th>";
            echo "<th scopr=\"col\">Auszug</th>";
            echo "<th scopr=\"col\">Kostenkonto</th>";
            echo "<th scopr=\"col\">Betrag</th>";
            // echo "<th scopr=\"col\">Skontobetrag</th>";
            echo "<th scopr=\"col\">Zuordnung</th>";
            echo "<th scopr=\"col\">Buchungsnr</th>";
            echo "<th scopr=\"col\">Buchungstext</th>";
            echo "</tr>";

            echo "<tr class=feldernamen>";
            echo "<th scopr=\"col\" id=\"tr_ansehen\">$datum_ger</th>";
            echo "<th ></th>";
            echo "<th scopr=\"col\"></th>";
            echo "<th scopr=\"col\"></th>";
            echo "<th scopr=\"col\">$this->summe_konto_buchungen_a</th>";
            // echo "<th scopr=\"col\">Skontobetrag</th>";
            echo "<th scopr=\"col\"></th>";
            echo "<th scopr=\"col\"></th>";
            echo "<th scopr=\"col\">SALDO VORTRAG VORMONAT</th>";
            echo "</tr>";

            echo "</thead>";

            for ($a = 0; $a < $numrows; $a++) {
                $datum = date_mysql2german($my_array [$a] ['DATUM']);
                $b_dat = $my_array [$a] ['GELD_KONTO_BUCHUNGEN_DAT'];
                $g_buchungsnummer = $my_array [$a] ['G_BUCHUNGSNUMMER'];
                $betrag = nummer_punkt2komma($my_array [$a] ['BETRAG']);
                $vzweck = $my_array [$a] ['VERWENDUNGSZWECK'];
                $auszug = $my_array [$a] ['KONTO_AUSZUGSNUMMER'];
                $erfass_nr = $my_array [$a] ['ERFASS_NR'];
                $kostenkonto = $my_array [$a] ['KONTENRAHMEN_KONTO'];
                $kostentraeger_typ = $my_array [$a] ['KOSTENTRAEGER_TYP'];
                $kostentraeger_id = $my_array [$a] ['KOSTENTRAEGER_ID'];
                // $k = new kasse;
                $r = new rechnung ();
                // $kostentraeger_bez = $k->kostentraeger_beschreibung($kostentraeger_typ, $kostentraeger_id);
                if ($kostentraeger_typ == 'Mietvertrag') {
                    $mv = new mietvertraege ();
                    $mv->get_mietvertrag_infos_aktuell($kostentraeger_id);
                    $kostentraeger_bez = $mv->personen_name_string_u;
                } else {
                    $kostentraeger_bez = $r->kostentraeger_ermitteln($kostentraeger_typ, $kostentraeger_id);
                }
                // $kostentraeger_bez = $r->kostentraeger_ermitteln($kostentraeger_typ, $kostentraeger_id);
                $kostentraeger_bez = substr($kostentraeger_bez, 0, 50);

                $link_buchungsbeleg = "<a href='" . route('web::buchen::legacy', ['option' => 'geldbuchung_aendern', 'geldbuchung_dat' => $b_dat]) . "'>$g_buchungsnummer</a>";

                echo "<tr><td>$datum</td><td>$erfass_nr</td><td>$auszug</td><td>$kostenkonto</td><td>$betrag</td><td>$kostentraeger_bez</td><td><b>$link_buchungsbeleg</b></td><td>$vzweck</td></tr>";
            }

            /* Datum Monat danach */
            $d_arr = explode(".", $datum_ger);
            $ja = $d_arr [2];
            $mo = $d_arr [1];

            if ($mo > 11) {
                $mo = 01;
                $ja = $ja + 1;
            } else {
                $mo = $mo + 1;
                $ja = $ja;
            }

            $datum_m_danach = "01.$mo.$ja";
            $this->kontostand_tagesgenau_bis($geldkonto_id, $datum_m_danach);
            $this->summe_konto_buchungen_a = nummer_punkt2komma($this->summe_konto_buchungen);
            echo "<tr><td><b>$datum_m_danach</b></td><td></td><td></td><td></td><td><b>$this->summe_konto_buchungen_a</b></td><td></td><td><b></b></td><td><b>KONTOSTAND</b></td></tr>";
            echo "</table>";
        }
    }

    function kontostand_tagesgenau_bis($geldkonto_id, $datum)
    {
        $datum = date_german2mysql($datum);
        $result = DB::select("SELECT SUM(BETRAG) AS SUMME FROM GELD_KONTO_BUCHUNGEN WHERE GELDKONTO_ID='$geldkonto_id' && DATUM<='$datum' && AKTUELL='1'");

        if (!empty($result)) {
            $row = $result[0];
            if ($row ['SUMME'] == null) {
                $this->summe_konto_buchungen = '0.00';
                return '0.00';
            } else {
                $this->summe_konto_buchungen = $row ['SUMME'];
                return $this->summe_konto_buchungen;
            }
        }
    }

    function buchungsjournal_startzeit_pdf($geldkonto_id, $datum)
    {
        ob_clean(); // ausgabepuffer leeren
        $pdf = new Cezpdf ('a4', 'portrait');
        $bpdf = new b_pdf ();
        $bpdf->b_header($pdf, 'Partner', session()->get('partner_id'), 'portrait', 'Helvetica.afm', 6);
        $p = new partners ();
        $datum_heute = date("d.m.Y");
        $p->get_partner_info(session()->get('partner_id'));
        $pdf->addText(475, 700, 8, "$p->partner_ort, $datum_heute");
        $dat_arr = explode("-", $datum);
        $ja = $dat_arr [0];
        $mo = $dat_arr [1];
        $monatsname = monat2name($mo);

        if (!request()->has('sort')) {
            $my_array = DB::select("SELECT DATUM, GELD_KONTO_BUCHUNGEN_DAT, MWST_ANTEIL,GELD_KONTO_BUCHUNGEN_ID, G_BUCHUNGSNUMMER, BETRAG, VERWENDUNGSZWECK, KONTO_AUSZUGSNUMMER, ERFASS_NR, KONTENRAHMEN_KONTO, KOSTENTRAEGER_TYP, KOSTENTRAEGER_ID FROM GELD_KONTO_BUCHUNGEN WHERE GELDKONTO_ID='$geldkonto_id' && DATE_FORMAT(DATUM, '%Y-%m') = '$ja-$mo' && AKTUELL='1' ORDER BY G_BUCHUNGSNUMMER ASC");
        } else {
            $sort = request()->input('sort');
            $my_array = DB::select("SELECT DATUM, GELD_KONTO_BUCHUNGEN_DAT, MWST_ANTEIL,GELD_KONTO_BUCHUNGEN_ID, G_BUCHUNGSNUMMER, BETRAG, VERWENDUNGSZWECK, KONTO_AUSZUGSNUMMER, ERFASS_NR, KONTENRAHMEN_KONTO,KOSTENTRAEGER_TYP, KOSTENTRAEGER_ID FROM GELD_KONTO_BUCHUNGEN WHERE GELDKONTO_ID='$geldkonto_id' && DATE_FORMAT(DATUM, '%Y-%m') = '$ja-$mo' && AKTUELL='1' ORDER BY $sort ASC");
        }
        $numrows = count($my_array);
        if (!empty($my_array)) {
            /* Kontostand */
            $datum_ger = date_mysql2german(tage_minus($datum, 1));
            $this->kontostand_tagesgenau_bis($geldkonto_id, $datum_ger);
            // $this->summe_konto_buchungen;
            $this->summe_konto_buchungen_a = nummer_punkt2komma($this->summe_konto_buchungen);

            $gk = new geldkonto_info ();
            $gk->geld_konto_details($geldkonto_id);
            $beguenstigter = $gk->konto_beguenstigter;
            $pdf->addText(43, 728, 6, "$gk->geldkonto_bezeichnung");

            $p = new partners ();
            $p->get_partner_id($beguenstigter);

            $table_arr [$a] ['DATUM'] = "<b>$datum_ger</b>";
            $table_arr [$a] ['BETRAG'] = "<b>$this->summe_konto_buchungen_a</b>";
            $table_arr [$a] ['VERWENDUNGSZWECK'] = '<b>SALDO VORMONAT</b>';
            $this->summe_mwst = 0;
            for ($a = 0; $a < $numrows; $a++) {
                $datum = date_mysql2german($my_array [$a] ['DATUM']);
                $g_buchungsnummer = $my_array [$a] ['G_BUCHUNGSNUMMER'];
                $betrag = $my_array [$a] ['BETRAG'];
                $mwst_anteil = $my_array [$a] ['MWST_ANTEIL'];
                $this->summe_mwst += $mwst_anteil;
                $vzweck = $my_array [$a] ['VERWENDUNGSZWECK'];
                $auszug = $my_array [$a] ['KONTO_AUSZUGSNUMMER'];
                $kostenkonto = $my_array [$a] ['KONTENRAHMEN_KONTO'];
                $kostentraeger_typ = $my_array [$a] ['KOSTENTRAEGER_TYP'];
                $kostentraeger_id = $my_array [$a] ['KOSTENTRAEGER_ID'];

                $r = new rechnung ();

                if ($kostentraeger_typ == 'Mietvertrag') {
                    $mv = new mietvertraege ();
                    $mv->get_mietvertrag_infos_aktuell($kostentraeger_id);
                    $kostentraeger_bez = $mv->personen_name_string_u;
                } else {
                    $kostentraeger_bez = $r->kostentraeger_ermitteln($kostentraeger_typ, $kostentraeger_id);
                }
                $kostentraeger_bez = substr($kostentraeger_bez, 0, 50);
                $kostentraeger_bez = strip_tags($kostentraeger_bez);

                $table_arr [$a] ['DATUM'] = $datum;
                $table_arr [$a] ['AUSZUG'] = $auszug;
                $table_arr [$a] ['BETRAG'] = $betrag;
                $table_arr [$a] ['MWST_ANTEIL'] = $mwst_anteil;
                $table_arr [$a] ['KONTO'] = $kostenkonto;
                $table_arr [$a] ['ZUORDNUNG'] = $kostentraeger_bez;
                $table_arr [$a] ['G_BUCHUNGSNUMMER'] = $g_buchungsnummer;
                $table_arr [$a] ['VERWENDUNGSZWECK'] = $vzweck;
                $table_arr [$a] ['KOSTENTRAEGER_BEZ'] = $kostentraeger_bez;
                $table_arr [$a] ['PLATZ'] = "";
            } // end for

            /* Datum Monat danach */
            $d_arr = explode(".", $datum_ger);

            $ja = $d_arr [2];
            $pdf_jahr = $ja;
            $mo = $d_arr [1];

            if ($mo > 11) {
                $mo = 01;
                $ja = $ja + 1;
            } else {
                $mo1 = $mo + 1;
                $mo = sprintf("%02d", $mo1);
                $ja = $ja;
            }

            $letzter_tag = date("t", mktime(0, 0, 0, $mo, 1, $ja));

            $datum_m_danach = "$letzter_tag.$mo.$ja";
            $datum_m_danach_1 = date_german2mysql($datum_m_danach);
            $datum_m_danach_2 = date_mysql2german($datum_m_danach_1);
            $this->kontostand_tagesgenau_bis($geldkonto_id, $datum_m_danach_2);
            $this->summe_konto_buchungen_a = nummer_punkt2komma_t($this->summe_konto_buchungen);

            $table_arr = $this->vzweck_kuerzen($table_arr);

            $L_pos = count($table_arr);
            $table_arr [$L_pos - 1] ['DATUM'] = '<b>Summen</b>';
            // $table_arr[$L_pos]['DATUM']='<b>Summe</b>';
            $this->summe_mwst_a = nummer_punkt2komma_t($this->summe_mwst);
            // $table_arr[$L_pos]['MWST_ANTEIL'] = "<b>$this->summe_mwst_a</b>";
            // $table_arr[$L_pos]['MWST_ANTEIL'] = "<b>$this->summe_mwst_a</b>";

            // $L_pos = count($table_arr);
            $table_arr [$a] ['DATUM'] = "<b>$datum_m_danach</b>";
            $table_arr [$a] ['VERWENDUNGSZWECK'] = '<b></b>';
            $table_arr [$a] ['BETRAG'] = "<b>$this->summe_konto_buchungen_a</b>";

            $cols = array(
                'DATUM' => "Datum",
                'G_BUCHUNGSNUMMER' => "BNR",
                'AUSZUG' => "Auszug",
                'KONTO' => "Konto",
                'BETRAG' => 'Betrag',
                'MWST_ANTEIL' => 'MWSt-Anteil',
                'KOSTENTRAEGER_BEZ' => 'Zuordnung',
                'VERWENDUNGSZWECK' => 'Buchungstext',
                'PLATZ' => 'Hinweis'
            );

            $pdf->ezTable($table_arr, $cols, "Buchungsjournal $monatsname $pdf_jahr $gk->geldkonto_bezeichnung", array(
                'showHeadings' => 1,
                'shaded' => 0,
                'titleFontSize' => 8,
                'fontSize' => 7,
                'xPos' => 50,
                'xOrientation' => 'right',
                'width' => 500,
                'cols' => array(
                    'DATUM' => array(
                        'justification' => 'right',
                        'width' => 50
                    ),
                    'G_BUCHUNGSNUMMER' => array(
                        'justification' => 'right',
                        'width' => 30
                    ),
                    'BETRAG' => array(
                        'justification' => 'right',
                        'width' => 50
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

            $gk_bez = str_replace(' ', '_', $gk->geldkonto_bez) . '-Buchungsjournal_' . "$mo" . "_" . "$ja.pdf";
            $pdf_opt ['Content-Disposition'] = $gk_bez;
            ob_end_clean();
            $pdf->ezStream($pdf_opt);
        } else { // end if numrow
            $pdf->addText(43, 718, 50, "KEINE BUCHUNGEN");
            ob_end_clean();
            $pdf->ezStream($pdf_opt);
        }
    }

    function vzweck_kuerzen($table_arr)
    {
        $anzahl_zeilen = count($table_arr);
        $summe = 0;
        $summe_mwst = 0;
        for ($a = 0; $a < $anzahl_zeilen; $a++) {
            $vzweck = $table_arr [$a] ['VERWENDUNGSZWECK'];
            $betrag_o = $table_arr [$a] ['BETRAG'];
            $mwst_anteil = $table_arr [$a] ['MWST_ANTEIL'];
            $kto_auszugsnr = $table_arr [$a] ['KONTO_AUSZUGSNUMMER'];
            $erfass_nr = $table_arr [$a] ['ERFASS_NR'];
            if (isset ($kto_auszugsnr) && isset ($erfass_nr)) {
                if ($erfass_nr != 'MIETE' && $erfass_nr != 'DTAUS' && $erfass_nr != $kto_auszugsnr) {
                    $rr = new rechnung ();
                    $rr->rechnung_grunddaten_holen($erfass_nr);
                    $aussteller_name = $rr->rechnungs_aussteller_name . ',';
                }
            } else {
                $aussteller_name = '';
            }

            $summe += $betrag_o;
            $summe_mwst += $mwst_anteil;
            $betrag = nummer_punkt2komma($table_arr [$a] ['BETRAG']);
            $mwst_anteil = nummer_punkt2komma($table_arr [$a] ['MWST_ANTEIL']);
            if (preg_match("/Erfnr:/i", "$vzweck")) {
                $pos = strpos($vzweck, ' '); // bis zu Rnr: abschneiden
                if ($pos == true) {
                    $vzweck_neu = substr($vzweck, $pos);
                    $table_arr [$a] ['VERWENDUNGSZWECK'] = $aussteller_name . ' ' . $vzweck_neu;
                }
            }

            $table_arr [$a] ['BETRAG_O_EUR'] = $betrag;
            $table_arr [$a] ['MWST_ANTEIL_O_EUR'] = $mwst_anteil;
            if ($betrag != '0,00') {
                $table_arr [$a] ['BETRAG'] = $betrag . ' €';
                $table_arr [$a] ['MWST_ANTEIL'] = $mwst_anteil . ' €';
            }
            unset ($erfass_nr);
            unset ($kto_auszugsnr);
        } // end for
        $summe = nummer_punkt2komma($summe);
        $summe_mwst_a = nummer_punkt2komma($summe_mwst);
        $table_arr [$anzahl_zeilen] ['BETRAG'] = "<b>$summe €</b>";
        $table_arr [$anzahl_zeilen] ['MWST_ANTEIL'] = "<b>$summe_mwst_a €</b>";
        return $table_arr;
    }

    function buchungsjournal_jahr_pdf($geldkonto_id, $ja, $monat = null)
    {
        ob_clean(); // ausgabepuffer leeren
        $pdf = new Cezpdf ('a4', 'portrait');
        $bpdf = new b_pdf ();
        $bpdf->b_header($pdf, 'Partner', session()->get('partner_id'), 'portrait', 'Helvetica.afm', 6);

        if ($monat == null) {
            $my_array = DB::select("SELECT DATUM, GELD_KONTO_BUCHUNGEN_DAT, GELD_KONTO_BUCHUNGEN_ID, G_BUCHUNGSNUMMER, BETRAG, MWST_ANTEIL,VERWENDUNGSZWECK, KONTO_AUSZUGSNUMMER, ERFASS_NR, KONTENRAHMEN_KONTO, KOSTENTRAEGER_TYP, KOSTENTRAEGER_ID FROM GELD_KONTO_BUCHUNGEN WHERE GELDKONTO_ID='$geldkonto_id' && DATE_FORMAT(DATUM, '%Y') = '$ja' && AKTUELL='1' ORDER BY G_BUCHUNGSNUMMER ASC");
        } else {
            $my_array = DB::select("SELECT DATUM, GELD_KONTO_BUCHUNGEN_DAT, GELD_KONTO_BUCHUNGEN_ID, G_BUCHUNGSNUMMER, BETRAG, MWST_ANTEIL,VERWENDUNGSZWECK, KONTO_AUSZUGSNUMMER, ERFASS_NR, KONTENRAHMEN_KONTO, KOSTENTRAEGER_TYP, KOSTENTRAEGER_ID FROM GELD_KONTO_BUCHUNGEN WHERE GELDKONTO_ID='$geldkonto_id' && DATE_FORMAT(DATUM, '%Y-%m') = '$ja-$monat' && AKTUELL='1' ORDER BY G_BUCHUNGSNUMMER ASC");
        }
        $numrows = count($my_array);
        if ($numrows > 0) {
            /* Kontostand */
            if ($monat == null) {
                $vorjahr = $ja - 1;
                $datum_ger = "31.12." . $vorjahr;
            } else {
                if ($monat == '01') {
                    $vorjahr = $ja - 1;
                    $datum_ger = "31.12." . $vorjahr;
                } else {
                    $vormonat = $this->vormonat($monat);
                    $ltvm = letzter_tag_im_monat($vormonat);
                    $datum_ger = "$ltvm.$vormonat.$ja";
                }
            }
            $this->kontostand_tagesgenau_bis($geldkonto_id, $datum_ger);
            $this->summe_konto_buchungen_a = nummer_punkt2komma_t($this->summe_konto_buchungen);

            $gk = new geldkonto_info ();
            $gk->geld_konto_details($geldkonto_id);
            $beguenstigter = $gk->konto_beguenstigter;
            $pdf->addText(43, 728, 6, "$gk->geldkonto_bezeichnung");

            $p = new partners ();
            $p->get_partner_id($beguenstigter);

            $table_arr [$a] ['DATUM'] = "<b>$datum_ger</b>";
            $table_arr [$a] ['BETRAG'] = "<b>$this->summe_konto_buchungen_a</b>";
            $table_arr [$a] ['VERWENDUNGSZWECK'] = '<b>SALDO VORMONAT</b>';

            for ($a = 0; $a < $numrows; $a++) {
                $datum = date_mysql2german($my_array [$a] ['DATUM']);
                $g_buchungsnummer = $my_array [$a] ['G_BUCHUNGSNUMMER'];
                $betrag = $my_array [$a] ['BETRAG'];
                $mwst_anteil = $my_array [$a] ['MWST_ANTEIL'];
                $vzweck = $my_array [$a] ['VERWENDUNGSZWECK'];
                $auszug = $my_array [$a] ['KONTO_AUSZUGSNUMMER'];
                $kostenkonto = $my_array [$a] ['KONTENRAHMEN_KONTO'];
                $kostentraeger_typ = $my_array [$a] ['KOSTENTRAEGER_TYP'];
                $kostentraeger_id = $my_array [$a] ['KOSTENTRAEGER_ID'];

                $r = new rechnung ();

                if ($kostentraeger_typ == 'Mietvertrag') {
                    $mv = new mietvertraege ();
                    $mv->get_mietvertrag_infos_aktuell($kostentraeger_id);
                    $kostentraeger_bez = $mv->personen_name_string_u;
                } else {
                    $kostentraeger_bez = $r->kostentraeger_ermitteln($kostentraeger_typ, $kostentraeger_id);
                }
                $kostentraeger_bez = substr($kostentraeger_bez, 0, 50);
                $kostentraeger_bez = strip_tags($kostentraeger_bez);

                $table_arr [$a] ['DATUM'] = $datum;
                $table_arr [$a] ['AUSZUG'] = $auszug;
                $table_arr [$a] ['BETRAG'] = $betrag;
                $table_arr [$a] ['MWST_ANTEIL'] = $mwst_anteil;
                $table_arr [$a] ['KONTO'] = $kostenkonto;
                $table_arr [$a] ['ZUORDNUNG'] = $kostentraeger_bez;
                $table_arr [$a] ['G_BUCHUNGSNUMMER'] = $g_buchungsnummer;
                $table_arr [$a] ['VERWENDUNGSZWECK'] = $vzweck;
                $table_arr [$a] ['KOSTENTRAEGER_BEZ'] = $kostentraeger_bez;
                $table_arr [$a] ['PLATZ'] = "";
            } // end for

            if ($monat == null) {
                $datum_m_danach = "31.12.$ja";
            } else {
                $ltm = letzter_tag_im_monat($monat, $jahr);
                $datum_m_danach = "$ltm.$monat.$ja";
            }

            $this->kontostand_tagesgenau_bis($geldkonto_id, $datum_m_danach);
            $this->summe_konto_buchungen_a = nummer_punkt2komma_t($this->summe_konto_buchungen);

            $L_pos = count($table_arr);
            $table_arr [$L_pos] ['DATUM'] = '<b>Summe</b>';
            $table_arr = $this->vzweck_kuerzen($table_arr);
            $table_arr [$a] ['DATUM'] = "<b>$datum_m_danach</b>";
            $table_arr [$a] ['BETRAG'] = "<b>$this->summe_konto_buchungen_a</b>";
            $table_arr [$a] ['VERWENDUNGSZWECK'] = '<b>KONTOSTAND</b>';

            $cols = array(
                'DATUM' => "Datum",
                'G_BUCHUNGSNUMMER' => "BNR",
                'AUSZUG' => "Auszug",
                'KONTO' => "Konto",
                'BETRAG' => 'Betrag',
                'MWST_ANTEIL' => 'MWSt',
                'KOSTENTRAEGER_BEZ' => 'Zuordnung',
                'VERWENDUNGSZWECK' => 'Buchungstext',
                'PLATZ' => 'Hinweis'
            );

            $pdf->ezTable($table_arr, $cols, "Buchungsjournal $ja $gk->geldkonto_bezeichnung", array(
                'showHeadings' => 1,
                'shaded' => 0,
                'titleFontSize' => 8,
                'fontSize' => 7,
                'xPos' => 50,
                'xOrientation' => 'right',
                'width' => 500,
                'cols' => array(
                    'DATUM' => array(
                        'justification' => 'right',
                        'width' => 50
                    ),
                    'G_BUCHUNGSNUMMER' => array(
                        'justification' => 'right',
                        'width' => 30
                    ),
                    'BETRAG' => array(
                        'justification' => 'right',
                        'width' => 50
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

            ob_end_clean();

            if (!request()->exists('xls')) {
                $pdf->ezStream();
            } else {
                ob_clean(); // ausgabepuffer leeren
                $fileName = "$gk->geldkonto_bezeichnung - Buchungsjournal $ja" . '.xls';
                header("Content-Type: application/vnd.ms-excel");
                // header("Content-Disposition: attachment; filename=$fileName");
                header("Content-Disposition: inline; filename=$fileName");
                echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">";
                echo "<html><head>";
                echo "<meta http-equiv=\"content-type\" content=\"text/html; charset=UTF-8\">";
                echo "</head><body>";
                echo "<table class=\"sortable\" id=\"positionen_tab\">";
                echo "<thead>";
                echo "<tr>";
                echo "<th>DATUM</th>";
                echo "<th>BNR</th>";
                echo "<th>AUSZUG</th>";
                echo "<th>KONTO</th>";
                echo "<th>BEZEICHNUNG</th>";
                echo "<th>BETRAG</th>";
                echo "<th>MWST</th>";
                echo "<th>ZUORDNUNG</th>";
                echo "<th>BUCHUNGSTEXT</th>";
                echo "</tr>";
                echo "</thead>";

                $anz_zeilen = count($table_arr);
                for ($aa = 0; $aa < $anz_zeilen - 4; $aa++) {
                    $datum_d = $table_arr [$aa] ['DATUM'];
                    $bnr = $table_arr [$aa] ['G_BUCHUNGSNUMMER'];
                    $auszug = $table_arr [$aa] ['AUSZUG'];
                    $kto = $table_arr [$aa] ['KONTO'];

                    /* Bezeichnung der Konten holen */
                    $k = new kontenrahmen ();
                    $kontenrahmen_id = $k->get_kontenrahmen('GELDKONTO', $geldkonto_id);
                    $k->konto_informationen2($kto, $kontenrahmen_id);
                    /* $k->konto_bezeichnung */

                    $betrag_o_eur = $table_arr [$aa] ['BETRAG_O_EUR'];
                    $mwst = $table_arr [$aa] ['MWST_ANTEIL_O_EUR'];
                    $zuordnung = $table_arr [$aa] ['ZUORDNUNG'];
                    $text = $table_arr [$aa] ['VERWENDUNGSZWECK'];
                    echo "<tr>";
                    echo "<td>$datum_d</td><td>$bnr</td><td>$auszug</td><td>$kto</td><td>$k->konto_bezeichnung</td><td>$betrag_o_eur</td><td>$mwst</td><td>$zuordnung</td><td>$text</td>";
                    echo "</tr>";
                }
                echo "</table>";
                echo "</body></html>";
                return;
            }
        } else { // end if numrow
            $pdf->addText(43, 718, 50, "KEINE BUCHUNGEN");
            if (!request()->has('xls')) {
                ob_end_clean();
                $pdf->ezStream();
            } else {
                fehlermeldung_ausgeben("Keine Buchungen im Jahr $ja");
            }
        }
    }

    function vormonat($monat = null)
    {
        if ($monat != null) {

            if ($monat > 0 && $monat < 13) {

                if ($monat == 1) {
                    $vormonat = 12;
                }

                if ($monat > 1) {
                    $vormonat = $monat - 1;
                }

                return sprintf('%02d', $vormonat);
            }
        }
    }

    function buchungsbeleg_ansicht($buchungsnr)
    {
        echo $buchungsnr;
    } // end function

    function get_bebuchte_konten($geldkonto_id, $kos_typs, $kos_ids)
    {
        $anz = count($kos_typs);
        $str_kos = 'AND ';
        for ($a = 0; $a < $anz; $a++) {
            $kos_typ = $kos_typs [$a];
            $kos_id = $kos_ids [$a];
            if ($a < $anz - 1) {
                $str_kos .= "(KOSTENTRAEGER_TYP='$kos_typ' AND KOSTENTRAEGER_ID='$kos_id') OR ";
            } else {
                $str_kos .= "(KOSTENTRAEGER_TYP='$kos_typ' AND KOSTENTRAEGER_ID='$kos_id') ";
            }
        }
        $result = DB::select("SELECT KONTENRAHMEN_KONTO AS KONTO FROM GELD_KONTO_BUCHUNGEN WHERE GELDKONTO_ID='$geldkonto_id' && AKTUELL='1' $str_kos GROUP BY KONTENRAHMEN_KONTO ORDER BY KONTENRAHMEN_KONTO ASC");
        return $result;
    } // end function

    function get_buchungen_vor($jahr = '2005')
    {
        $result = DB::select("SELECT * FROM `GELD_KONTO_BUCHUNGEN` WHERE  `DATUM` <  '$jahr-01-01' AND  `AKTUELL` =  '1'");
        $numrows = count($result);
        if ($numrows) {
            return $numrows;
        }
        return false;
    }

    function buchungskonten_uebersicht($geldkonto_id)
    {
        $konten_arr = $this->konten_aus_buchungen($geldkonto_id);
        $jahr = request()->input('jahr');
        $monat = request()->input('monat');
        $link = route('web::buchen::legacy', ['option' => 'konten_uebersicht'], false);

        if (isset ($jahr) && isset ($monat)) {
            $this->monate_jahres_links($jahr, $link);
        }
        if (isset ($jahr) && !isset ($monat)) {
            $this->monate_jahres_links($jahr, $link);
        }
        if (!isset ($jahr) && !isset ($monat)) {
            $monat = date("m");
            $jahr = date("Y");
            $this->monate_jahres_links($jahr, $link);
        }

        $form = new formular ();
        $form->fieldset("Kostenbericht $monat $jahr", 'kostenbericht');
        if (isset ($monat) && isset ($jahr)) {
            $pdf_link = "<a href='" . route('web::buchen::legacy', ['option' => 'konten_uebersicht_pdf', 'monat' => $monat, 'jahr' => $jahr]) . "'>PDF ERSTELLEN</a>";
        }

        if (!isset ($monat) && isset ($jahr)) {
            $pdf_link = "<a href='" . route('web::buchen::legacy', ['option' => 'konten_uebersicht_pdf', 'jahr' => $jahr]) . "'>PDF ERSTELLEN</a>";
        }
        echo $pdf_link;

        // ###########
        $anzahl_konten = count($konten_arr);
        for ($a = 0; $a < $anzahl_konten; $a++) {
            $kostenkonto = $konten_arr [$a] ['KONTO'];

            if (isset ($jahr) && isset ($monat)) {
                $this->kontobuchungen_anzeigen_jahr_monat($geldkonto_id, $kostenkonto, $jahr, $monat);
            }
            if (isset ($jahr) && !isset ($monat)) {
                $this->kontobuchungen_anzeigen_jahr($geldkonto_id, $kostenkonto, $jahr);
            }
            if (!isset ($jahr) && !isset ($monat)) {
                $monat = date("m");
                $jahr = date("Y");
                $this->kontobuchungen_anzeigen_jahr_monat($geldkonto_id, $kostenkonto, $jahr, $monat);
            }
        } // end for

        $form->fieldset_ende();
    }

    function monate_jahres_links($jahr, $link)
    {
        $f = new formular ();
        $f->fieldset("Monats- und Jahresauswahl", 'kostenkonten');
        $vorjahr = $jahr - 1;
        $nachjahr = $jahr + 1;
        $link_vorjahr = "&nbsp;<a href=\"$link&jahr=$vorjahr\"><b>$vorjahr</b></a>&nbsp;";
        $link_nach = "&nbsp;<a href=\"$link&jahr=$nachjahr\"><b>$nachjahr</b></a>&nbsp;";
        echo $link_vorjahr;
        $link_alle = "<a href=\"$link&jahr=$jahr\">Alle von $jahr</a>&nbsp;";
        echo $link_alle;
        for ($a = 1; $a <= 12; $a++) {
            $monat_zweistellig = sprintf('%02d', $a);
            $link_neu = "<a href=\"$link&monat=$monat_zweistellig&jahr=$jahr\">$a/$jahr</a>&nbsp;";
            // echo "$a/$jahr<br>";
            echo "$link_neu";
        }
        echo $link_nach;
        $f->fieldset_ende();
    }

    function kontobuchungen_anzeigen_jahr_monat($geldkonto_id, $kostenkonto, $jahr, $monat)
    {
        $result = DB::select("SELECT * FROM GELD_KONTO_BUCHUNGEN WHERE GELDKONTO_ID='$geldkonto_id' && KONTENRAHMEN_KONTO = '$kostenkonto' && ( DATE_FORMAT( DATUM, '%Y-%m' ) = '$jahr-$monat') && AKTUELL='1' ORDER BY GELD_KONTO_BUCHUNGEN_ID DESC");

        $numrows = count($result);
        if ($numrows > 0) {
            $k = new kontenrahmen ();
            // $k->konto_informationen($kostenkonto);
            $kontenrahmen_id = $k->get_kontenrahmen('GELDKONTO', $geldkonto_id);
            $k->konto_informationen2($kostenkonto, $kontenrahmen_id);
            echo "<table>";
            echo "<tr><th>$kostenkonto $k->konto_bezeichnung</th></tr>";
            echo "</table>";
            echo "<table class=\"sortable\">";
            echo "<tr><th>Datum</th><th>Erfassnr</th><th>Betrag</th><th>ZUWEISUNG</th><th>Buchungsnr</th><th>Vzweck</th></tr>";
            foreach ($result as $row) {
                $datum = date_mysql2german($row ['DATUM']);
                $erfass_nr = $row ['ERFASS_NR'];
                $betrag = nummer_punkt2komma($row ['BETRAG']);
                $vzweck = $row ['VERWENDUNGSZWECK'];
                $g_buchungsnummer = $row ['G_BUCHUNGSNUMMER'];
                $kos_typ = $row ['KOSTENTRAEGER_TYP'];
                $kos_id = $row ['KOSTENTRAEGER_ID'];
                $this->summe_kontobuchungen_jahr_monat($geldkonto_id, $kostenkonto, $jahr, $monat);
                $r = new rechnung ();
                $kos_bez = $r->kostentraeger_ermitteln($kos_typ, $kos_id);
                echo "<tr></td><td>$datum</td><td><a href='" . route('web::rechnungen.show', ['id' => $erfass_nr]) . "'>$erfass_nr</a></td><td>$betrag</td><td>$kos_bez</td><td>$g_buchungsnummer</td><td>$vzweck</td></tr>";
            }
            $this->summe_konto_buchungen = nummer_punkt2komma($this->summe_konto_buchungen);
            echo "<tfoot><tr><td></td><td><b>Summe</b></td><td><b>$this->summe_konto_buchungen €</b></td><td></td><td></td><td></td></tr></tfoot>";

            echo "</table><br>";
        }
    }

    function summe_kontobuchungen_jahr_monat($geldkonto_id, $kostenkonto, $jahr, $monat)
    {
        $result = DB::select("SELECT SUM(BETRAG) AS SUMME FROM GELD_KONTO_BUCHUNGEN WHERE GELDKONTO_ID='$geldkonto_id' && KONTENRAHMEN_KONTO = '$kostenkonto' && ( DATE_FORMAT( DATUM, '%Y-%m' ) = '$jahr-$monat') && AKTUELL='1'");
        if (!empty($result)) {
            $this->summe_konto_buchungen = 0;
            $row = $result[0];
            $this->summe_konto_buchungen = $row ['SUMME'];
        } else {
            $this->summe_konto_buchungen = 0;
        }
    }

    function kontobuchungen_anzeigen_jahr($geldkonto_id, $kostenkonto, $jahr)
    {
        $result = DB::select("SELECT * FROM GELD_KONTO_BUCHUNGEN WHERE GELDKONTO_ID='$geldkonto_id' && KONTENRAHMEN_KONTO = '$kostenkonto' && ( DATE_FORMAT( DATUM, '%Y' ) = '$jahr') && AKTUELL='1' ORDER BY GELD_KONTO_BUCHUNGEN_ID DESC");
        if (!empty($result)) {
            $k = new kontenrahmen ();
            $k->konto_informationen($kostenkonto);
            $kontenrahmen_id = $k->get_kontenrahmen('GELDKONTO', $geldkonto_id);
            $k->konto_informationen2($kostenkonto, $kontenrahmen_id);
            echo "<table>";
            echo "<tr><th><b>$kostenkonto  $k->konto_bezeichnung</b></th></tr>";
            echo "</table>";
            echo "<table class=\"sortable\">";
            echo "<tr><th>Datum</th><th>Erfassnr</th><th>Betrag</th><th>ZUWEISUNG</th><th>Buchungsnr</th><th>Vzweck</th></tr>";
            foreach ($result as $row) {
                $datum = date_mysql2german($row ['DATUM']);
                $erfass_nr = $row ['ERFASS_NR'];
                $betrag = nummer_punkt2komma($row ['BETRAG']);
                $vzweck = $row ['VERWENDUNGSZWECK'];
                $g_buchungsnummer = $row ['G_BUCHUNGSNUMMER'];
                $this->summe_kontobuchungen_jahr($geldkonto_id, $kostenkonto, $jahr);

                $kos_typ = $row ['KOSTENTRAEGER_TYP'];
                $kos_id = $row ['KOSTENTRAEGER_ID'];

                $r = new rechnung ();
                $kos_bez = $r->kostentraeger_ermitteln($kos_typ, $kos_id);

                echo "<tr></td><td>$datum</td><td><a href='" . route('web::rechnungen.index', ['id' => $erfass_nr]) . "'>$erfass_nr</a></td><td>$betrag</td><td>$kos_bez</td><td>$g_buchungsnummer</td><td>$vzweck</td></tr>";
            }
            $this->summe_konto_buchungen = nummer_punkt2komma($this->summe_konto_buchungen);

            echo "<tfoot><tr><td></td><td></td><td><b>Summe</b></td><td><b>$this->summe_konto_buchungen €</b></td><td></td><td></td></tr></tfoot>";

            echo "</table><br>";
        }
    }

    function buchungskonten_uebersicht_pdf($geldkonto_id)
    {
        $konten_arr = $this->konten_aus_buchungen($geldkonto_id);
        $g = new geldkonto_info ();
        $g->geld_konto_details($geldkonto_id);

        $jahr = request()->input('jahr');
        if (request()->has('monat')) {
            $monat = request()->input('monat');
        }
        $link = route('web::buchen::legacy', ['option' => 'konten_uebersicht_pdf'] . false);

        if (isset ($jahr) && isset ($monat)) {
            $this->monate_jahres_links($jahr, $link);
        }
        if (isset ($jahr) && !isset ($monat)) {
            $this->monate_jahres_links($jahr, $link);
        }
        if (!isset ($jahr) && !isset ($monat)) {
            $monat = date("m");
            $jahr = date("Y");
            $this->monate_jahres_links($jahr, $link);
        }

        // ###########

        ob_clean(); // ausgabepuffer leeren
        $pdf = new Cezpdf ('a4', 'portrait');
        $bpdf = new b_pdf ();
        $bpdf->b_header($pdf, 'Partner', session()->get('partner_id'), 'portrait', 'Helvetica.afm', 6);
        $pdf->addText(43, 735, 6, "$g->geldkonto_bezeichnung");
        $pdf->addText(43, 728, 6, "KNr:$g->kontonummer BLZ:$g->blz");
        $datum_heute = date("d.m.Y");
        $p = new partners ();
        $p->get_partner_info(session()->get('partner_id'));
        $pdf->addText(475, 700, 8, "$p->partner_ort, $datum_heute");

        if (isset ($monat)) {
            $monatname = monat2name($monat);
            $pdf->ezText("<u>Buchungskontenübersicht $monatname $jahr</u>", 12, array(
                'justification' => 'center'
            ));
        } else {
            $pdf->ezText("<u>Buchungskontenübersicht $jahr</u>", 11, array(
                'justification' => 'center'
            ));
        }
        $pdf->ezSetDy(-10); // abstand

        $k = new kontenrahmen ();
        $anzahl_konten = count($konten_arr);
        for ($a = 0; $a < $anzahl_konten; $a++) {
            $kostenkonto = $konten_arr [$a] ['KONTO'];
            $kontenrahmen_id = $k->get_kontenrahmen('GELDKONTO', $geldkonto_id);
            $k->konto_informationen2($kostenkonto, $kontenrahmen_id);

            if (isset ($monat)) {
                $monat = sprintf('%02d', $monat);
                $table_arr = DB::select("SELECT date_format(DATUM,'%d.%m.%Y') AS DATUM, G_BUCHUNGSNUMMER, ERFASS_NR, KONTO_AUSZUGSNUMMER, BETRAG, MWST_ANTEIL, RTRIM(LTRIM(VERWENDUNGSZWECK)) AS VERWENDUNGSZWECK, KOSTENTRAEGER_TYP, KOSTENTRAEGER_ID FROM GELD_KONTO_BUCHUNGEN WHERE GELDKONTO_ID='$geldkonto_id' && KONTENRAHMEN_KONTO = '$kostenkonto' && ( DATE_FORMAT( DATUM, '%Y-%m' ) = '$jahr-$monat') && AKTUELL='1' ORDER BY GELD_KONTO_BUCHUNGEN_ID ASC");
            } else {
                $table_arr = DB::select("SELECT date_format(DATUM,'%d.%m.%Y') AS DATUM, G_BUCHUNGSNUMMER, ERFASS_NR, KONTO_AUSZUGSNUMMER, BETRAG, MWST_ANTEIL,RTRIM(LTRIM(VERWENDUNGSZWECK)) AS VERWENDUNGSZWECK, KOSTENTRAEGER_TYP, KOSTENTRAEGER_ID FROM GELD_KONTO_BUCHUNGEN WHERE GELDKONTO_ID='$geldkonto_id' && KONTENRAHMEN_KONTO = '$kostenkonto' && ( DATE_FORMAT( DATUM, '%Y' ) = '$jahr') && AKTUELL='1' ORDER BY GELD_KONTO_BUCHUNGEN_ID ASC");
            }

            $numrows = count($result1);
            if (!empty($table_arr)) {
                $L_pos = count($table_arr);
                $table_arr = $this->vzweck_kuerzen($table_arr);
                $table_arr [$L_pos] ['DATUM'] = '<b>Summe</b>';

                for ($ga = 0; $ga < $L_pos; $ga++) {
                    $r = new rechnung ();
                    $kostentraeger_typ = $table_arr [$ga] ['KOSTENTRAEGER_TYP'];
                    $kostentraeger_id = $table_arr [$ga] ['KOSTENTRAEGER_ID'];
                    $kos_bez = $r->kostentraeger_ermitteln($kostentraeger_typ, $kostentraeger_id);
                    $table_arr [$ga] ['KOS_BEZ'] = $kos_bez;
                }

                $cols = array(
                    'DATUM' => "Datum",
                    'KONTO_AUSZUGSNUMMER' => "AUSZUG",
                    'G_BUCHUNGSNUMMER' => "BNR",
                    'BETRAG' => 'Betrag',
                    'MWST_ANTEIL' => 'MWSt-Anteil',
                    'KOS_BEZ' => 'Zuordnung',
                    'VERWENDUNGSZWECK' => 'Buchungstext'
                );
                $pdf->ezTable($table_arr, $cols, "<b>Buchungskonto $kostenkonto - $k->konto_bezeichnung</b>", array(
                    'showHeadings' => 1,
                    'shaded' => 0,
                    'titleFontSize' => 8,
                    'fontSize' => 7,
                    'xPos' => 50,
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
                $pdf->ezSetDy(-5); // abstand
            }

            unset ($table_arr);
        } // end for
        ob_end_clean();
        $gk_bez = str_replace(' ', '_', $g->geldkonto_bez) . '-Buchungskontenuebersicht_' . "$monat" . "_" . "$jahr.pdf";
        $pdf_opt ['Content-Disposition'] = $gk_bez;
        $pdf->ezStream($pdf_opt);
    }

    function form_kontouebersicht()
    {
        echo "<hr><br>";
        $geldkonto_id = session()->get('geldkonto_id');
        $form = new formular ();
        $form->hidden_feld("geldkonto_id", "$geldkonto_id");
        $this->dropdown_kostenrahmen_nr('Kostenkonto', 'kostenkonto', 'GELDKONTO', $geldkonto_id, '');
        $js_typ = "onchange=\"list_kostentraeger('list_kostentraeger', this.value)\"";
        $this->dropdown_kostentreager_typen('Kostenträgertyp', 'kostentraeger_typ', 'kostentraeger_typ', $js_typ);
        $js_id = "";
        $this->dropdown_kostentreager_ids('Kostenträger', 'kostentraeger_id', 'dd_kostentraeger_id', $js_id);
        $form->text_feld("Anfangsdatum:", "anfangsdatum", "", "10", 'anfangsdatum', '');
        $form->text_feld("Enddatum:", "enddatum", "", "10", 'enddatum', '');
        $form->send_button("submit_kostenkonto", "Suchen");
        $form->hidden_feld("option", "kostenkonto_suchen");
        $form->ende_formular();
    }

    /* Funktion für die Kosten und Einnahmenübersicht */

    function form_buchungen_zu_kostenkonto()
    {
        $f = new formular ();
        $k = new kontenrahmen ();
        $k->dropdown_kontorahmenkonten('Konto auswählen', 'kkk', 'kostenkonto', 'Alle', '', '');
        $f->text_feld("Anfangsdatum:", "anfangsdatum", "", "10", 'anfangsdatum', '');
        $f->text_feld("Enddatum:", "enddatum", "", "10", 'enddatum', '');
        $f->send_button("submit_kostenkonto", "Suchen");
        $f->hidden_feld("option", "buchungen_zu_kostenkonto");
    }

    /* erwartet array mit geldkonto_ids und objekt_namen */
    /* array[][] */

    function form_kosten_einnahmen()
    {
        $bg = new berlussimo_global ();
        $link = route('web::buchen::legacy', ['option' => 'kosten_einnahmen'], false);
        if (request()->has('monat') && request()->has('jahr')) {
            if (request()->input('monat') != 'alle') {
                session()->put('monat', sprintf('%02d', request()->input('monat')));
            } else {
                session()->put('monat', request()->input('monat'));
            }
            session()->put('jahr', request()->input('jahr'));
            $jahr = session()->get('jahr');
            $monat = session()->get('monat');
        }
        if (empty ($monat) or empty ($jahr)) {
            $monat = date("m");
            $jahr = date("Y");
        }

        $bg->monate_jahres_links($jahr, $link);
        /* PDF LINK */
        echo "<a href='" . route('web::buchen::legacy', ['option' => 'kosten_einnahmen_pdf', 'monat' => $monat, 'jahr' => $jahr]) . "'>PDF ÜBERSICHT</a>";

        echo "<h4>Block II</h4>";
        $this->kosten_einnahmen($monat, $jahr, [4, 1884]);
        echo "<hr><h4>Block III</h4>";
        $this->kosten_einnahmen($monat, $jahr, [5, 1885]);
        echo "<hr><h4>Block V</h4>";
        $this->kosten_einnahmen($monat, $jahr, 6);
        echo "<hr><h4>HW</h4>";
        $this->kosten_einnahmen($monat, $jahr, 7);
        echo "<hr><h4>GBN</h4>";
        $this->kosten_einnahmen($monat, $jahr, 8);
        echo "<hr><h4>DÜ29</h4>";
        $this->kosten_einnahmen($monat, $jahr, 1920);
        echo "<hr><h4>HO190</h4>";
        $this->kosten_einnahmen($monat, $jahr, 1921);
        echo "<hr><h4>Lager</h4>";
        $this->kosten_einnahmen($monat, $jahr, 12);
    }

    function kosten_einnahmen($monat, $jahr, $geldkonto_ids)
    {
        if (!is_array($geldkonto_ids)) {
            $geldkonto_ids = [$geldkonto_ids];
        }

        $datum_jahresanfang = "01.01.$jahr";
        $kontostand_jahresanfang = 0;
        foreach ($geldkonto_ids as $geldkonto_id) {
            $this->kontostand_tagesgenau_bis($geldkonto_id, $datum_jahresanfang);
            $kontostand_jahresanfang += $this->summe_konto_buchungen;
        }

        $summe_mieteinnahmen_monat = 0;
        foreach ($geldkonto_ids as $geldkonto_id) {
            $this->summe_kontobuchungen_jahr_monat($geldkonto_id, '80001', $jahr, $monat);
            $summe_mieteinnahmen_monat += $this->summe_konto_buchungen;
        }

        $summe_mieteinnahmen_jahr = 0;
        foreach ($geldkonto_ids as $geldkonto_id) {
            $this->summe_miete_jahr($geldkonto_id, '80001', $jahr, $monat);
            $summe_mieteinnahmen_jahr += $this->summe_konto_buchungen;
        }

        $summe_kosten_monat = 0;
        foreach ($geldkonto_ids as $geldkonto_id) {
            $this->summe_kosten_jahr_monat($geldkonto_id, '80001', $jahr, $monat);
            $summe_kosten_monat += $this->summe_konto_buchungen;
        }

        $summe_kosten_jahr = 0;
        foreach ($geldkonto_ids as $geldkonto_id) {
            $this->summe_kosten_jahr($geldkonto_id, '80001', $jahr, $monat);
            $summe_kosten_jahr += $this->summe_konto_buchungen;
        }

        if ($monat < 12) {
            $monat_neu = $monat + 1;
            $jahr_neu = $jahr;
        }
        if ($monat == 12) {
            $monat_neu = 1;
            $jahr_neu = $jahr + 1;
        }
        $datum_heute = "01.$monat_neu.$jahr_neu";
        $kontostand_heute = 0;
        foreach ($geldkonto_ids as $geldkonto_id) {
            $this->kontostand_tagesgenau_bis($geldkonto_id, $datum_heute);
            $kontostand_heute += $this->summe_konto_buchungen;
        }

        $monatname = monat2name($monat);
        echo "<h5>$monatname $jahr</h5>";
        echo "<table><thead><th>Kontostand $datum_jahresanfang</th><th>Mieten (Monat)</th><th>Mieten (Jahr)</th><th>Kosten (Monat)</th><th>Kosten (Jahr)</th><th>Kontostand $datum_heute</th></th></thead>";
        $kontostand_jahresanfang = nummer_punkt2komma_t($kontostand_jahresanfang);
        $summe_mieteinnahmen_monat = nummer_punkt2komma_t($summe_mieteinnahmen_monat);
        $summe_mieteinnahmen_jahr = nummer_punkt2komma_t($summe_mieteinnahmen_jahr);
        $summe_kosten_monat = nummer_punkt2komma_t($summe_kosten_monat);
        $summe_kosten_jahr = nummer_punkt2komma_t($summe_kosten_jahr);
        $kontostand_heute = nummer_punkt2komma_t($kontostand_heute);
        echo "<tr><td>$kontostand_jahresanfang</td><td>$summe_mieteinnahmen_monat</td><td>$summe_mieteinnahmen_jahr</td><td>$summe_kosten_monat</td><td>$summe_kosten_jahr</td><td>$kontostand_heute</td></tr>";
        echo "</table>";
    }

    function summe_miete_jahr($geldkonto_id, $mieteinnahmen_kostenkonto, $jahr, $monat)
    {
        $ltag = letzter_tag_im_monat($monat, $jahr);
        $result = DB::select("SELECT SUM(BETRAG) AS SUMME FROM GELD_KONTO_BUCHUNGEN WHERE GELDKONTO_ID='$geldkonto_id' && KONTENRAHMEN_KONTO = '$mieteinnahmen_kostenkonto' && DATUM BETWEEN '$jahr-01-01' AND '$jahr-$monat-$ltag' && AKTUELL='1'");

        $numrows = count($result);
        if ($numrows > 0) {
            $this->summe_konto_buchungen = 0;
            $row = $result[0];
            $this->summe_konto_buchungen = $row ['SUMME'];
        } else {
            $this->summe_konto_buchungen = 0;
        }
    }

    function summe_kosten_jahr_monat($geldkonto_id, $mieteinnahmen_kostenkonto, $jahr, $monat)
    {
        $result = DB::select("SELECT SUM(BETRAG) AS SUMME FROM GELD_KONTO_BUCHUNGEN WHERE GELDKONTO_ID='$geldkonto_id' && KONTENRAHMEN_KONTO != '$mieteinnahmen_kostenkonto' && ( DATE_FORMAT( DATUM, '%Y-%m' ) = '$jahr-$monat') && AKTUELL='1'");

        $numrows = count($result);
        if ($numrows > 0) {
            $this->summe_konto_buchungen = 0;
            $row = $result[0];
            $this->summe_konto_buchungen = $row ['SUMME'];
        } else {
            $this->summe_konto_buchungen = 0;
        }
    }

    function summe_kosten_jahr($geldkonto_id, $mieteinnahmen_kostenkonto, $jahr, $monat)
    {
        $ltag = letzter_tag_im_monat($monat, $jahr);
        $result = DB::select("SELECT SUM(BETRAG) AS SUMME FROM GELD_KONTO_BUCHUNGEN WHERE GELDKONTO_ID='$geldkonto_id' && KONTENRAHMEN_KONTO != '$mieteinnahmen_kostenkonto' && DATUM BETWEEN '$jahr-01-01' AND '$jahr-$monat-$ltag' && AKTUELL='1'");

        $numrows = count($result);
        if ($numrows > 0) {
            $this->summe_konto_buchungen = 0;
            $row = $result[0];
            $this->summe_konto_buchungen = $row ['SUMME'];
        } else {
            $this->summe_konto_buchungen = 0;
        }
    }

    function kosten_einnahmen_pdf($geldkontos_arr, $monat, $jahr)
    {
        $anzahl_konten = count($geldkontos_arr);
        $datum_jahresanfang = "01.01.$jahr";
        if ($anzahl_konten) {

            ob_clean(); // ausgabepuffer leeren
            /* PDF AUSGABE */
            $pdf = new Cezpdf ('a4', 'portrait');
            $bpdf = new b_pdf ();
            $bpdf->b_header($pdf, 'Partner', session()->get('partner_id'), 'portrait', 'Helvetica.afm', 6);
            $pdf->addInfo('Title', "Monatsbericht $objekt_name $monatname $jahr");

            $g_kosten_jahr = 0.00;

            $monat = sprintf('%02d', $monat);

            $monatname = monat2name($monat);

            $letzter_tag_m = letzter_tag_im_monat($monat, $jahr);

            $datum_bis = "$letzter_tag_m.$monat.$jahr";

            /* Schleife für jedes Geldkonto bzw. Zeilenausgabe */
            for ($a = 0; $a < $anzahl_konten; $a++) {
                $geldkonto_ids = $geldkontos_arr [$a] ['GELDKONTO_ID'];
                if (!is_array($geldkonto_ids)) {
                    $geldkonto_ids = [$geldkonto_ids];
                }
                $objekt_name = $geldkontos_arr [$a] ['OBJEKT_NAME'];

                $kontostand_jahresanfang = 0;
                $summe_mieteinnahmen_monat = 0;
                $summe_mieteinnahmen_jahr = 0;
                $summe_kosten_monat = 0;
                $summe_kosten_jahr = 0;
                $kontostand_heute = 0;

                foreach ($geldkonto_ids as $geldkonto_id) {
                    $this->kontostand_tagesgenau_bis($geldkonto_id, $datum_jahresanfang);
                    $kontostand_jahresanfang += $this->summe_konto_buchungen;
                    $this->summe_kontobuchungen_jahr_monat($geldkonto_id, '80001', $jahr, $monat);
                    $summe_mieteinnahmen_monat += $this->summe_konto_buchungen;
                    $this->summe_miete_jahr($geldkonto_id, '80001', $jahr, $monat);
                    $summe_mieteinnahmen_jahr += $this->summe_konto_buchungen;
                    $this->summe_kosten_jahr_monat($geldkonto_id, '80001', $jahr, $monat);
                    $summe_kosten_monat += $this->summe_konto_buchungen;
                    $this->summe_kosten_jahr($geldkonto_id, '80001', $jahr, $monat);
                    $summe_kosten_jahr += $this->summe_konto_buchungen;
                    $this->kontostand_tagesgenau_bis($geldkonto_id, $datum_bis);
                    $kontostand_heute += $this->summe_konto_buchungen;
                }

                /* Gesamtsummen bilden */
                $g_kontostand_ja = $g_kontostand_ja + $kontostand_jahresanfang;
                $g_me_monat = $g_me_monat + $summe_mieteinnahmen_monat;
                $g_me_jahr = $g_me_jahr + $summe_mieteinnahmen_jahr;
                $g_kosten_monat = $g_kosten_monat + $summe_kosten_monat;
                $g_kosten_jahr += $summe_kosten_jahr;
                $g_kontostand_akt = $g_kontostand_akt + $kontostand_heute;

                $kontostand_jahresanfang = nummer_punkt2komma_t($kontostand_jahresanfang);
                $summe_mieteinnahmen_monat = nummer_punkt2komma_t($summe_mieteinnahmen_monat);
                $summe_mieteinnahmen_jahr = nummer_punkt2komma_t($summe_mieteinnahmen_jahr);
                $summe_kosten_monat = nummer_punkt2komma_t($summe_kosten_monat);
                $summe_kosten_jahr = nummer_punkt2komma_t($summe_kosten_jahr);
                $kontostand_heute = nummer_punkt2komma_t($kontostand_heute);
                // echo "<b>$kontostand_jahresanfang| $summe_mieteinnahmen_monat|$summe_mieteinnahmen_jahr|$summe_kosten_monat|$summe_kosten_jahr|$kontostand_heute</b><br>";

                $table_arr [$a] ['OBJEKT_NAME'] = $objekt_name;
                $table_arr [$a] ['KONTOSTAND1_1'] = $kontostand_jahresanfang;
                $table_arr [$a] ['ME_MONAT'] = $summe_mieteinnahmen_monat;
                $table_arr [$a] ['ME_JAHR'] = $summe_mieteinnahmen_jahr;
                $table_arr [$a] ['KOSTEN_MONAT'] = $summe_kosten_monat;
                $table_arr [$a] ['KOSTEN_JAHR'] = $summe_kosten_jahr;
                $table_arr [$a] ['KONTOSTAND_AKTUELL'] = "<b>$kontostand_heute</b>";
            } // end for

            /* Summenzeile hinzufügen */
            $table_arr [$a] ['OBJEKT_NAME'] = "<b>Summe</b>";
            $table_arr [$a] ['KONTOSTAND1_1'] = '<b>' . nummer_punkt2komma_t($g_kontostand_ja) . '</b>';
            $table_arr [$a] ['ME_MONAT'] = '<b>' . nummer_punkt2komma_t($g_me_monat) . '</b>';
            $table_arr [$a] ['ME_JAHR'] = '<b>' . nummer_punkt2komma_t($g_me_jahr) . '</b>';
            $table_arr [$a] ['KOSTEN_MONAT'] = '<b>' . nummer_punkt2komma_t($g_kosten_monat) . '</b>';
            $table_arr [$a] ['KOSTEN_JAHR'] = '<b>' . nummer_punkt2komma_t($g_kosten_jahr) . '</b>';
            $table_arr [$a] ['KONTOSTAND_AKTUELL'] = '<b>' . nummer_punkt2komma_t($g_kontostand_akt) . '</b>';

            $pdf->ezTable($table_arr, array(
                'OBJEKT_NAME' => 'Objekt',
                'KONTOSTAND1_1' => "Kontostand $datum_jahresanfang",
                'ME_MONAT' => "Mieten Einnahmen $monatname",
                'ME_JAHR' => "Mieten Einnahmen $jahr",
                'KOSTEN_MONAT' => "Kosten $monatname",
                'KOSTEN_JAHR' => "Kosten $jahr",
                'KONTOSTAND_AKTUELL' => "Kontostand"
            ), '<b>Kosten & Einnahmen / Objekt</b>', array(
                'shaded' => 0,
                'width' => '500',
                'justification' => 'right',
                'cols' => array(
                    'OBJEKT_NAME' => array(
                        'width' => 50
                    ),
                    'KONTOSTAND1_1' => array(
                        'justification' => 'right'
                    ),
                    'ME_MONAT' => array(
                        'justification' => 'right'
                    ),
                    'ME_JAHR' => array(
                        'justification' => 'right'
                    ),
                    'KOSTEN_MONAT' => array(
                        'justification' => 'right'
                    ),
                    'KOSTEN_JAHR' => array(
                        'justification' => 'right'
                    ),
                    'KONTOSTAND_AKTUELL' => array(
                        'justification' => 'right'
                    )
                )
            ));

            ob_end_clean(); // ausgabepuffer leeren
            header("Content-Type: application/pdf"); // wird von MSIE ignoriert
            $pdf->ezStream();
        } else {
            echo "Keine Daten Error 65922";
        }
    }

    function summe_buchungen_kostenkonto_bis_heute($geldkonto_id, $kostenkonto, $kos_typ, $kos_id)
    {
        $result = DB::select("SELECT SUM(BETRAG) AS SUMME FROM GELD_KONTO_BUCHUNGEN WHERE GELDKONTO_ID='$geldkonto_id' && KONTENRAHMEN_KONTO = '$kostenkonto' && ( DATE_FORMAT( DATUM, '%Y-%m' ) <= CURDATE()) && AKTUELL='1' && KOSTENTRAEGER_TYP='$kos_typ' && KOSTENTRAEGER_ID='$kos_id'");

        $numrows = count($result);
        if ($numrows > 0) {
            $this->summe_konto_buchungen = 0;
            $row = $result[0];
            $this->summe_konto_buchungen = $row ['SUMME'];
        } else {
            $this->summe_konto_buchungen = 0;
        }
    }

    /* Unabhängig vom Geldkonto */

    function check_buchung($gk_id, $betrag, $datum)
    {
        $result = DB::select("SELECT * 
FROM  `GELD_KONTO_BUCHUNGEN` 
WHERE  `BETRAG` =  '$betrag'
AND  `GELDKONTO_ID` =  '$gk_id'
AND  `DATUM` =  '$datum'
AND  `AKTUELL` =  '1'
LIMIT 0 , 1");
        return !empty($result);
    } // end function

    function finde_buchungen_zu_kostenkonto($kostenkonto, $anfangsdatum, $enddatum)
    {
        $result = DB::select("SELECT * FROM GELD_KONTO_BUCHUNGEN WHERE  KONTENRAHMEN_KONTO = '$kostenkonto' && DATUM BETWEEN '$anfangsdatum' AND '$enddatum' && AKTUELL='1' ORDER BY GELDKONTO_ID, BETRAG, DATUM, KOSTENTRAEGER_TYP, KOSTENTRAEGER_ID ASC");

        $numrows = count($result);
        if ($numrows) {
            $anfangsdatum = date_mysql2german($anfangsdatum);
            $enddatum = date_mysql2german($enddatum);
            $k = new kontenrahmen ();
            $k->konto_informationen($kostenkonto);
            echo "<table>";

            echo "<tr class=\"feldernamen\"><td colspan=6><b>Alle Buchungen zu $kostenkonto vom $anfangsdatum bis $enddatum<br>$kostenkonto $k->konto_bezeichnung</b></td></tr>";
            echo "<tr class=\"feldernamen\"><td>Geldkonto</td><td>Kostenträger</td><td>Datum</td><td>Buchungsnr</td><td>Betrag</td><td width=70%>Vzweck</td></tr>";
            $g = new geldkonto_info ();
            foreach ($result as $row) {
                $datum = date_mysql2german($row ['DATUM']);
                $g_buchungsnummer = $row ['G_BUCHUNGSNUMMER'];
                $betrag = nummer_punkt2komma($row ['BETRAG']);
                $vzweck = $row ['VERWENDUNGSZWECK'];
                $kos_typ = $row ['KOSTENTRAEGER_TYP'];
                $kos_id = $row ['KOSTENTRAEGER_ID'];
                $geldkonto_id = $row ['GELDKONTO_ID'];
                $g->geld_konto_details($geldkonto_id);
                $r = new rechnung ();
                $kostentraeger_bezeichnung = $r->kostentraeger_ermitteln($kos_typ, $kos_id);

                echo "<tr><td>$g->geldkonto_bezeichnung<td>$kostentraeger_bezeichnung</td><td>$datum</td><td><b>$g_buchungsnummer</b></td><td>$betrag</td><td>$vzweck</td></tr>";
            }

            echo "</table><br>";
        } else {
            echo "Keine Buchungen zu Kostenkonto $kostenkonto $k->konto_bezeichnung<br>";
        }
    }

    function kontobuchungen_anzeigen_dyn($geldkonto_id, $kostenkonto, $anfangsdatum, $enddatum, $kostentraeger_typ, $kostentraeger_bez)
    {
        $anfangsdatum = date_german2mysql($anfangsdatum);
        $enddatum = date_german2mysql($enddatum);
        $kostentraeger_id = $this->kostentraeger_id_ermitteln($kostentraeger_typ, $kostentraeger_bez);
        if (empty ($kostentraeger_id)) {
            $result = DB::select("SELECT * FROM GELD_KONTO_BUCHUNGEN WHERE GELDKONTO_ID='$geldkonto_id' && KONTENRAHMEN_KONTO = '$kostenkonto' && DATUM BETWEEN '$anfangsdatum' AND '$enddatum' && AKTUELL='1' ORDER BY KOSTENTRAEGER_TYP, KOSTENTRAEGER_ID ASC");
        } else {
            $result = DB::select("SELECT * FROM GELD_KONTO_BUCHUNGEN WHERE GELDKONTO_ID='$geldkonto_id' && KONTENRAHMEN_KONTO = '$kostenkonto' && DATUM BETWEEN '$anfangsdatum' AND '$enddatum' && AKTUELL='1' && KOSTENTRAEGER_TYP='$kostentraeger_typ' && KOSTENTRAEGER_ID='$kostentraeger_id' ORDER BY DATUM ASC");
        }
        $numrows = count($result);
        if ($numrows > 0) {
            $anfangsdatum = date_mysql2german($anfangsdatum);
            $enddatum = date_mysql2german($enddatum);
            $k = new kontenrahmen ();
            $k->konto_informationen($kostenkonto);
            echo "<table>";
            if (empty ($kostentraeger_id)) {
                echo "<tr class=\"feldernamen\"><td colspan=5><b>Alle Kostenträger vom $anfangsdatum bis $enddatum<br>$kostenkonto $k->konto_bezeichnung</b></td></tr>";
                echo "<tr><td>Kostenträger</td><td>Datum</td><td>Erfassnr</td><td>Betrag</td><td width=70%>Vzweck</td></tr>";
            } else {
                echo "<tr class=\"feldernamen\"><td colspan=5><b>Kostenträger $kostentraeger_bez vom $anfangsdatum bis $enddatum<br>$kostenkonto $k->konto_bezeichnung</b></td></tr>";
                echo "<tr><td>Datum</td><td>Erfassnr</td><td>Betrag</td><td width=70%>Vzweck</td></tr>";
            }
            foreach ($result as $row) {
                $datum = date_mysql2german($row ['DATUM']);
                $erfass_nr = $row ['ERFASS_NR'];
                $betrag = nummer_punkt2komma($row ['BETRAG']);
                $vzweck = $row ['VERWENDUNGSZWECK'];
                $kos_typ = $row ['KOSTENTRAEGER_TYP'];
                $kos_id = $row ['KOSTENTRAEGER_ID'];
                $r = new rechnung ();
                $kostentraeger_bezeichnung = $r->kostentraeger_ermitteln($kos_typ, $kos_id);
                $this->summe_kontobuchungen_dyn($geldkonto_id, $kostenkonto, $anfangsdatum, $enddatum, $kostentraeger_typ, $kostentraeger_bez);
                if (empty ($kostentraeger_id)) {
                    echo "<tr><td>$kostentraeger_bezeichnung</td><td>$datum</td><td>$erfass_nr</td><td>$betrag</td><td>$vzweck</td></tr>";
                } else {
                    echo "<tr><td>$datum</td><td>$erfass_nr</td><td>$betrag</td><td>$vzweck</td></tr>";
                }
            }
            $this->summe_konto_buchungen = nummer_punkt2komma($this->summe_konto_buchungen);
            if (empty ($kostentraeger_id)) {
                echo "<tr class=\"feldernamen\"><td></td><td></td><td><b>Summe</b></td><td><b>$this->summe_konto_buchungen €</b></td><td></td></tr>";
            } else {
                echo "<tr class=\"feldernamen\"><td></td><td><b>Summe</b></td><td><b>$this->summe_konto_buchungen €</b></td><td></td></tr>";
            }
            echo "</table><br>";
        } else {
            echo "Geldkonto $geldkonto_id - Kostenkonto $kostenkonto leer<br>";
        }
    }

    function summe_kontobuchungen_dyn($geldkonto_id, $kostenkonto, $anfangsdatum, $enddatum, $kostentraeger_typ, $kostentraeger_bez)
    {
        $anfangsdatum = date_german2mysql($anfangsdatum);
        $enddatum = date_german2mysql($enddatum);
        $kostentraeger_id = $this->kostentraeger_id_ermitteln($kostentraeger_typ, $kostentraeger_bez);
        if (empty ($kostentraeger_id)) {
            $result = DB::select("SELECT SUM(BETRAG) AS SUMME FROM GELD_KONTO_BUCHUNGEN WHERE GELDKONTO_ID='$geldkonto_id' && KONTENRAHMEN_KONTO = '$kostenkonto' && DATUM BETWEEN '$anfangsdatum' AND '$enddatum' && AKTUELL='1'");
        } else {
            $result = DB::select("SELECT SUM(BETRAG) AS SUMME FROM GELD_KONTO_BUCHUNGEN WHERE GELDKONTO_ID='$geldkonto_id' && KONTENRAHMEN_KONTO = '$kostenkonto' && DATUM BETWEEN '$anfangsdatum' AND '$enddatum' && AKTUELL='1' && KOSTENTRAEGER_TYP='$kostentraeger_typ' && KOSTENTRAEGER_ID='$kostentraeger_id'");
        }
        $numrows = count($result);
        if ($numrows > 0) {
            $this->summe_konto_buchungen = 0;
            $row = $result[0];
            $this->summe_konto_buchungen = $row ['SUMME'];
        } else {
            $this->summe_konto_buchungen = 0;
        }
    }

    function summe_kontobuchungen_dyn2($geldkonto_id, $kostenkonto, $anfangsdatum, $enddatum, $kostentraeger_typ, $kostentraeger_id)
    {
        $result = DB::select("SELECT SUM(BETRAG) AS SUMME FROM GELD_KONTO_BUCHUNGEN WHERE GELDKONTO_ID='$geldkonto_id' && KONTENRAHMEN_KONTO = '$kostenkonto' && DATUM BETWEEN '$anfangsdatum' AND '$enddatum' && AKTUELL='1' && KOSTENTRAEGER_TYP='$kostentraeger_typ' && KOSTENTRAEGER_ID='$kostentraeger_id'");

        $numrows = count($result);
        if ($numrows > 0) {
            $this->summe_konto_buchungen = 0;
            $row = $result[0];
            $this->summe_konto_buchungen = $row ['SUMME'];
            return $row ['SUMME'];
        } else {
            $this->summe_konto_buchungen = 0;
        }
    }

    function monatsbericht_ohne_ausgezogene()
    {
        echo "<h5>Aktuelle Mieterstatistik ohne ausgezogene Mieter<br></h5>";
        $jahr = request()->input('jahr');
        if (empty ($jahr)) {
            $jahr = date("Y");
        } else {
            if (strlen($jahr) < 4) {
                $jahr = date("Y");
            }
        }

        $monat = request()->input('monat');
        if (empty ($monat)) {
            $monat = date("m");
        } else {
            if (strlen($monat) < 2) {
                $monat = '0' . $monat;
            }
        }

        $bg = new berlussimo_global ();
        $link = route('web::buchen::legacy', ['option' => 'monatsbericht_o_a'], false);
        $bg->objekt_auswahl_liste();
        $bg->monate_jahres_links($jahr, $link);
        if (session()->has('objekt_id')) {
            $objekt_id = session()->get('objekt_id');
            $einheit_info = new einheit ();
            $o = new objekt ();
            $objekt_name = $o->get_objekt_name($objekt_id);

            /* Aktuell bzw. gewünschten Monat berechnen */
            $o = new objekt ();
            $einheiten_array = $o->einheiten_objekt_arr($objekt_id);

            $anzahl_aktuell = count($einheiten_array);
            /* PDF */

            $zaehler = 0;

            $pdf = new Cezpdf ('a4', 'portrait');
            $bpdf = new b_pdf ();
            $bpdf->b_header($pdf, 'Partner', session()->get('partner_id'), 'portrait', 'Helvetica.afm', 6);
            $datum_heute = date("d.m.Y");
            $p = new partners ();
            $p->get_partner_info(session()->get('partner_id'));
            $pdf->addText(475, 700, 8, "$p->partner_ort, $datum_heute");
            $this->footer_zahlungshinweis = $bpdf->zahlungshinweis;

            $monatname = monat2name($monat);
            $pdf->addInfo('Title', "Monatsbericht $objekt_name $monatname $jahr");
            $pdf->addInfo('Author', Auth::user()->email);

            $summe_sv = 0;
            $summe_mieten = 0;
            $summe_umlagen = 0;
            $summe_akt_gsoll = 0;
            $summe_g_zahlungen = 0;
            $summe_saldo_neu = 0;

            for ($i = 0; $i < $anzahl_aktuell; $i++) {
                $miete = new miete ();
                $einheit_info->get_mietvertraege_zu("" . $einheiten_array [$i] ['EINHEIT_ID'] . "", $jahr, $monat);

                $zaehler++;
                /* Wenn vermietet */
                if (isset ($einheit_info->mietvertrag_id)) {
                    $mv = new mietvertraege ();
                    $mv->get_mietvertrag_infos_aktuell($einheit_info->mietvertrag_id);
                    $mk = new mietkonto ();
                    $mieter_ids = $mk->get_personen_ids_mietvertrag($einheit_info->mietvertrag_id);
                    for ($a = 0; $a < count($mieter_ids); $a++) {
                        $mieter_daten_arr [] = $mk->get_person_infos($mieter_ids [$a] ['PERSON_MIETVERTRAG_PERSON_ID']);
                    }
                    $miete->mietkonto_berechnung_monatsgenau($einheit_info->mietvertrag_id, $jahr, $monat);
                    // $a = new miete;
                    // $a->mietkonto_berechnung($einheit_info->mietvertrag_id);

                    // $miete->mietkonto_berechnung($einheit_info->mietvertrag_id);
                    // $miete->mietkonto_berechnen($einheit_info->mietvertrag_id, $jahr, $monat);
                    $zeile = $zeile + 1;
                    $einheit_id = $einheiten_array [$i] ['EINHEIT_ID'];

                    $einheit_kurzname = $einheiten_array [$i] ['EINHEIT_KURZNAME'];
                    $vn = rtrim(ltrim($mieter_daten_arr ['0'] ['0'] ['first_name']));
                    $nn = rtrim(ltrim($mieter_daten_arr ['0'] ['0'] ['name']));
                    $akt_gesamt_soll = $miete->sollmiete_warm - $miete->saldo_vormonat_stand;
                    $nn = $this->umlaute_anpassen($nn);
                    $vn = $this->umlaute_anpassen($vn);

                    $tab_arr [$i] ['EINHEIT'] = $einheit_kurzname;
                    $tab_arr [$i] ['MIETER'] = "$mv->personen_name_string_u";

                    /* Kommazahlen für die Ausgabe im PDF */
                    $miete->saldo_vormonat_stand_a = nummer_punkt2komma($miete->saldo_vormonat_stand);
                    $miete->sollmiete_warm_a = nummer_punkt2komma($miete->sollmiete_warm);
                    $miete->davon_umlagen_a = nummer_punkt2komma($miete->davon_umlagen);
                    $akt_gesamt_soll_a = nummer_punkt2komma($akt_gesamt_soll);
                    $miete->geleistete_zahlungen_a = nummer_punkt2komma($miete->geleistete_zahlungen);
                    $miete->erg_a = nummer_punkt2komma($miete->erg);

                    $tab_arr [$i] ['SALDO_VM'] = "$miete->saldo_vormonat_stand_a";
                    $tab_arr [$i] ['SOLL_WM'] = "$miete->sollmiete_warm_a";
                    $tab_arr [$i] ['UMLAGEN'] = "$miete->davon_umlagen_a";
                    $tab_arr [$i] ['G_SOLL_AKT'] = "$akt_gesamt_soll_a";
                    $tab_arr [$i] ['ZAHLUNGEN'] = "$miete->geleistete_zahlungen_a";
                    $tab_arr [$i] ['ERG'] = "$miete->erg_a";

                    $ee = new einheit ();
                    $ee->get_einheit_info($einheit_id);
                    $dd = new detail ();
                    $optiert = $dd->finde_detail_inhalt('Objekt', session()->get('objekt_id'), 'Optiert');
                    if ($optiert == 'JA') {
                        if ($ee->typ == 'Gewerbe') {
                            $tab_arr [$i] ['MWST'] = nummer_punkt2komma($miete->geleistete_zahlungen_mwst);
                            $summe_mwst += $miete->geleistete_zahlungen_mwst;
                        } else {
                            $tab_arr [$i] ['MWST'] = '';
                        }
                    }

                    $summe_sv = $summe_sv + $miete->saldo_vormonat_stand;
                    $summe_mieten = $summe_mieten + $miete->sollmiete_warm;
                    $summe_umlagen = $summe_umlagen + $miete->davon_umlagen;
                    $summe_akt_gsoll = $summe_akt_gsoll + $akt_gesamt_soll;
                    $summe_g_zahlungen = $summe_g_zahlungen + $miete->geleistete_zahlungen;
                    $summe_saldo_neu = $summe_saldo_neu + $miete->erg;

                    /* Leerstand */
                } else {
                    $einheit_kurzname = $einheiten_array [$i] ['EINHEIT_KURZNAME'];
                    $tab_arr [$i] ['EINHEIT'] = "<b>$einheit_kurzname</b>";
                    $tab_arr [$i] ['MIETER'] = "<b>Leerstand</b>";
                }
            }
            unset ($miete);
            unset ($mieter_daten_arr);
            unset ($mk);
            unset ($nn);
            unset ($vn);

            unset ($einheiten_array);
            // $pdf->ezStopPageNumbers();

            /* Summen */
            $tab_arr [$i + 1] ['MIETER'] = '<b>SUMMEN</b>';
            $tab_arr [$i + 1] ['SALDO_VM'] = '<b>' . nummer_punkt2komma($summe_sv) . '</b>';
            $tab_arr [$i + 1] ['SOLL_WM'] = '<b>' . nummer_punkt2komma($summe_mieten) . '</b>';
            $tab_arr [$i + 1] ['UMLAGEN'] = '<b>' . nummer_punkt2komma($summe_umlagen) . '</b>';
            $tab_arr [$i + 1] ['G_SOLL_AKT'] = '<b>' . nummer_punkt2komma($summe_akt_gsoll) . '</b>';
            $tab_arr [$i + 1] ['ZAHLUNGEN'] = '<b>' . nummer_punkt2komma($summe_g_zahlungen) . '</b>';
            $tab_arr [$i + 1] ['ERG'] = '<b>' . nummer_punkt2komma($summe_saldo_neu) . '</b>';
            $tab_arr [$i + 1] ['MWST'] = '<b>' . nummer_punkt2komma($summe_mwst) . '</b>';

            $cols = array(
                'EINHEIT' => "<b>EINHEIT</b>",
                'MIETER' => "<b>MIETER</b>",
                'SALDO_VM' => "<b>SALDO\nVORMONAT</b>",
                'SOLL_WM' => "<b>SOLL\nMIETE\nWARM</b>",
                'UMLAGEN' => "<b>DAVON\nUMLAGEN</b>",
                'G_SOLL_AKT' => "<b>GESAMT\nSOLL\nAKTUELL</b>",
                'ZAHLUNGEN' => "<b>GELEISTETE\nZAHLUNGEN</b>",
                'MWST' => "<b>DAVON\nMWST</b>",
                'ERG' => "<b>SALDO\nNEU</b>"
            );
            $pdf->ezSetDy(-10);

            $pdf->ezTable($tab_arr, $cols, "Monatsbericht ohne Auszug - Objekt:$objekt_name - $monatname $jahr", array(
                'showHeadings' => 1,
                'shaded' => 1,
                'titleFontSize' => 10,
                'fontSize' => 8,
                'xPos' => 50,
                'xOrientation' => 'right',
                'width' => 500,
                'rowGap' => 1,
                'cols' => array(
                    'EINHEIT' => array(
                        'justification' => 'left',
                        'width' => 50
                    ),
                    'SALDO_VM' => array(
                        'justification' => 'right',
                        'width' => 60
                    ),
                    'UMLAGEN' => array(
                        'justification' => 'right',
                        'width' => 55
                    ),
                    'G_SOLL_AKT' => array(
                        'justification' => 'right',
                        'width' => 50
                    ),
                    'ZAHLUNGEN' => array(
                        'justification' => 'right',
                        'width' => 65
                    ),
                    'MWST' => array(
                        'justification' => 'right'
                    ),
                    'ERG' => array(
                        'justification' => 'right',
                        'width' => 50
                    )
                )
            ));
            $pdf->ezStopPageNumbers(); // seitennummerirung beenden
            $content = $pdf->ezOutput();

            $dateiname_org = $objekt_name . '-Monatsbericht_o_Auszug';
            $path = Storage::disk('objektberichte')->basePath();
            $dateiname = $dateiname_org . '_' . $monat . '_' . $jahr . '.pdf';
            $this->save_file($dateiname_org, $path, $objekt_id, $content, $monat, $jahr);
            /* Falls kein Objekt ausgewählt */

            // weiterleiten($dateiname);
            $download_link = "<h5><a href='" . Storage::disk('objektberichte')->url($objekt_id . '/' . $dateiname) . "'>Monatsbericht $objekt_name für $monat/$jahr HIER</a></h5>";
            hinweis_ausgeben("Monatsbericht ohne Vormieter für $objekt_name wurde erstellt<br>");
            echo $download_link;

            // $pdf->ezTable($tab_arr);
            // ob_clean(); //ausgabepuffer leeren
            // $pdf->ezStream();
        }
    }

    function umlaute_anpassen($str)
    {
        $str = str_replace('ä', 'ae', $str);
        $str = str_replace('ö', 'oe', $str);
        $str = str_replace('ü', 'ue', $str);
        $str = str_replace('Ä', 'Ae', $str);
        $str = str_replace('Ö', 'Oe', $str);
        $str = str_replace('Ü', 'Ue', $str);
        $str = str_replace('ß', 'ss', $str);

        return $str;
    }

    function save_file($dateiname, $hauptordner, $unterordner, $content, $monat, $jahr)
    {
        $dir = "$hauptordner/$unterordner";

        if (!file_exists($hauptordner)) {
            mkdir($hauptordner, 0777);
        }
        if (!file_exists($dir)) {
            mkdir($dir, 0777);
        }
        $filename = $dateiname . '_' . $monat . '_' . $jahr . '.pdf';
        $filename = "$dir/$filename";

        if (file_exists($filename)) {
            fehlermeldung_ausgeben("Datei exisitiert bereits, keine Überschreibung möglich");
            $fhandle = fopen($filename, "w");
            fwrite($fhandle, $content);
            fclose($fhandle);
            chmod($filename, 0777);
            return $filename;
        } else {
            $fhandle = fopen($filename, "w");
            fwrite($fhandle, $content);
            fclose($fhandle);
            chmod($filename, 0777);
            return $filename;
        }
    }

    function monatsbericht_mit_ausgezogenen()
    {
        echo "Monatsbericht Mieter - Monatsbericht Kostenkonten<br>";
        echo "<h3>Aktuelle Mieterstatistik mit ausgezogenen Mietern<br></h3>";
        if (request()->has('jahr')) {
            $jahr = request()->input('jahr');
        }
        if (empty ($jahr)) {
            $jahr = date("Y");
        } else {
            if (strlen($jahr) < 4) {
                $jahr = date("Y");
            }
        }
        if (request()->has('monat')) {
            $monat = request()->input('monat');
        }
        if (empty ($monat)) {
            $monat = date("m");
        } else {
            if (strlen($monat) < 2) {
                $monat = '0' . $monat;
            }
        }

        $bg = new berlussimo_global ();
        $link = route('web::buchen::legacy', ['option' => 'monatsbericht_m_a'], false);
        $bg->objekt_auswahl_liste();
        $bg->monate_jahres_links($jahr, $link);
        if (session()->has('objekt_id')) {
            $objekt_id = session()->get('objekt_id');
            $einheit_info = new einheit ();
            $o = new objekt ();
            $objekt_name = $o->get_objekt_name($objekt_id);
            /* Aktuell bzw. gewünschten Monat berechnen */
            $ob = new objekt ();

            $einheiten_array = $ob->einheiten_objekt_arr($objekt_id);

            // PDF#
            $zaehler = 0;

            $pdf = new Cezpdf ('a4', 'portrait');
            $bpdf = new b_pdf ();
            $bpdf->b_header($pdf, 'Partner', session()->get('partner_id'), 'portrait', 'Helvetica.afm', 6);
            $datum_heute = date("d.m.Y");
            $p = new partners ();
            $p->get_partner_info(session()->get('partner_id'));
            $pdf->addText(475, 700, 8, "$p->partner_ort, $datum_heute");
            // $pdf->ezText("$p->partner_ort, $datum_heute",12, array('justification'=>'right'));
            $this->footer_zahlungshinweis = $bpdf->zahlungshinweis;

            $monatname = monat2name($monat);
            $pdf->addInfo('Title', "Monatsbericht $objekt_name $monatname $jahr");
            $pdf->addInfo('Author', Auth::user()->email);

            $summe_sv = 0;
            $summe_mieten = 0;
            $summe_umlagen = 0;
            $summe_akt_gsoll = 0;
            $summe_g_zahlungen = 0;
            $summe_saldo_neu = 0;
            $summe_mwst = 0;

            $anzahl_aktuell = count($einheiten_array);
            $anz_tab = 0;
            for ($i = 0; $i < $anzahl_aktuell; $i++) {

                $miete = new miete ();
                $mv_array = $einheit_info->get_mietvertraege_bis("" . $einheiten_array [$i] ['EINHEIT_ID'] . "", $jahr, $monat);
                $mv_anzahl = count($mv_array);

                if (!empty($mv_array)) {
                    $zeile = 0;
                    for ($b = 0; $b < $mv_anzahl; $b++) {
                        $mv_id = $mv_array [$b] ['MIETVERTRAG_ID'];

                        $mv = new mietvertraege ();
                        $mv->get_mietvertrag_infos_aktuell($mv_id);

                        $tab_arr [$i] ['MV_ID'] = $mv_id;
                        $miete->mietkonto_berechnung_monatsgenau($mv_id, $jahr, $monat);
                        $zeile = $zeile + 1;

                        if ($mv->mietvertrag_aktuell == '1') {
                            $tab_arr [$anz_tab] ['MIETER'] = $mv->personen_name_string_u;
                            $tab_arr [$anz_tab] ['EINHEIT'] = $mv->einheit_kurzname;
                        } else {
                            $tab_arr [$anz_tab] ['MIETER'] = "<b>$mv->personen_name_string_u</b>";
                            $tab_arr [$anz_tab] ['EINHEIT'] = "<b>$mv->einheit_kurzname</b>";
                        }
                        // $tab_arr[$anz_tab]['E_TYP'] = $mv->einheit_typ;
                        // $tab_arr[$anz_tab]['VON'] = $mv->mietvertrag_von_d;
                        // $tab_arr[$anz_tab]['BIS'] = $mv->mietvertrag_bis_d;
                        $tab_arr [$anz_tab] ['SALDO_VM'] = nummer_punkt2komma_t($miete->saldo_vormonat_stand);
                        $tab_arr [$anz_tab] ['G_SOLL_AKT'] = nummer_punkt2komma_t($miete->saldo_vormonat_stand + $miete->sollmiete_warm);
                        $tab_arr [$anz_tab] ['SOLL_WM'] = nummer_punkt2komma_t($miete->sollmiete_warm);
                        $tab_arr [$anz_tab] ['UMLAGEN'] = nummer_punkt2komma_t($miete->davon_umlagen);
                        $tab_arr [$anz_tab] ['ZAHLUNGEN'] = nummer_punkt2komma_t($miete->geleistete_zahlungen);

                        $dd = new detail ();
                        $optiert = $dd->finde_detail_inhalt('Objekt', session()->get('objekt_id'), 'Optiert');
                        if ($optiert == 'JA') {
                            if ($mv->einheit_typ == 'Gewerbe') {
                                $tab_arr [$anz_tab] ['MWST'] = nummer_punkt2komma($miete->geleistete_zahlungen_mwst);
                                $summe_mwst += $miete->geleistete_zahlungen_mwst;
                            } else {
                                $tab_arr [$anz_tab] ['MWST'] = nummer_punkt2komma_t(0);
                            }
                        }

                        $tab_arr [$anz_tab] ['ERG'] = nummer_punkt2komma_t($miete->erg);
                        $anz_tab++;

                        $akt_gesamt_soll = $miete->saldo_vormonat_stand + $miete->sollmiete_warm;
                        echo "$zeile. $einheit_kurzname $mv->personen_name_string_u Saldo: VM: $miete->saldo_vormonat_stand € WM: $miete->sollmiete_warm € UM: $miete->davon_umlagen GSOLL: $akt_gesamt_soll € SALDO NEU:$miete->erg €<br>";

                        $summe_sv = $summe_sv + $miete->saldo_vormonat_stand;
                        $summe_mieten = $summe_mieten + $miete->sollmiete_warm;
                        $summe_umlagen = $summe_umlagen + $miete->davon_umlagen;
                        $summe_akt_gsoll = $summe_akt_gsoll + $akt_gesamt_soll;
                        $summe_g_zahlungen = $summe_g_zahlungen + $miete->geleistete_zahlungen;
                        $summe_saldo_neu = $summe_saldo_neu + $miete->erg;
                        $zaehler++;

                        unset ($mieter_daten_arr);
                    } // end if is_array mv_ids
                }
            }
            /* Ausgabe der Summen */
            $pdf->ezSetDy(-15); // abstand
            // $pdf->ezText("Summen: $summe_sv",10, array('left'=>'150'));
            // $pdf->ezText("$summe_mieten",10, array('left'=>'250'));
            // $pdf->ezText("$summe_umlagen",10, array('left'=>'300'));
            // $pdf->ezText("$summe_akt_gsoll",10, array('left'=>'350'));
            // $pdf->ezText("$summe_g_zahlungen",10, array('left'=>'400'));
            // $pdf->ezText("$summe_saldo_neu",10, array('left'=>'450'));

            $anz_l = count($tab_arr);
            $tab_arr [$anz_l] ['SALDO_VM'] = nummer_punkt2komma_t($summe_sv);
            $tab_arr [$anz_l] ['SOLL_WM'] = nummer_punkt2komma_t($summe_mieten);
            $tab_arr [$anz_l] ['UMLAGEN'] = nummer_punkt2komma_t($summe_umlagen);
            $tab_arr [$anz_l] ['G_SOLL_AKT'] = nummer_punkt2komma_t($summe_akt_gsoll);
            $tab_arr [$anz_l] ['ZAHLUNGEN'] = nummer_punkt2komma_t($summe_g_zahlungen);
            $tab_arr [$anz_l] ['MWST'] = nummer_punkt2komma_t($summe_mwst);
            $tab_arr [$anz_l] ['ERG'] = nummer_punkt2komma_t($summe_saldo_neu);

            // $cols = array('EINHEIT'=>"<b>EINHEIT</b>",'MIETER'=>"<b>MIETER</b>", 'SALDO_VM'=>"<b>SALDO VORMONAT</b>",'AKT_GESAMT_SOLL'=>"SOLL MIETE WARM", 'UMLAGEN'=>"DAVON UMLAGEN",'AKT_GESAMT_SOLL'=>"GESAMT SOLL AKTUELL", 'ZAHLUNGEN'=>"GELEISTETE ZAHLUNGEN", 'ZAHLUNGEN_MWST'=>"DAVON MWST", 'SALDO_NEU'=>"SALDO NEU");
            // echo '<pre>';
            // print_r($tab_arr);
            // $pdf->ezTable($tab_arr, $cols, 'Monatsbericht mit Auszug');
            // array('showHeadings'=>1,'shaded'=>1, 'titleFontSize' => 10, 'fontSize' => 8, 'xPos'=>50,'xOrientation'=>'right', 'width'=>500,'rowGap'=>1, 'cols'=>array('EINHEIT'=>array('justification'=>'left', 'width'=>50), 'SALDO_VM'=>array('justification'=>'right', 'width'=>60), 'UMLAGEN'=>array('justification'=>'right', 'width'=>55), 'G_SOLL_AKT'=>array('justification'=>'right', 'width'=>50), 'ZAHLUNGEN'=>array('justification'=>'right','width'=>65), 'ZAHLUNGEN_MWST'=>array('justification'=>'right'), 'ERG'=>array('justification'=>'right','width'=>50))));
            $cols = array(
                'EINHEIT' => "<b>EINHEIT</b>",
                'MIETER' => "<b>MIETER</b>",
                'SALDO_VM' => "<b>SALDO\nVORMONAT</b>",
                'SOLL_WM' => "<b>SOLL\nMIETE\nWARM</b>",
                'UMLAGEN' => "<b>DAVON\nUMLAGEN</b>",
                'G_SOLL_AKT' => "<b>GESAMT\nSOLL\nAKTUELL</b>",
                'ZAHLUNGEN' => "<b>GELEISTETE\nZAHLUNGEN</b>",
                'MWST' => "<b>DAVON\nMWST</b>",
                'ERG' => "<b>SALDO\nNEU</b>"
            );
            $pdf->ezTable($tab_arr, $cols, "Monatsbericht mit Auszug - Objekt:$objekt_name - $monatname $jahr", array(
                'showHeadings' => 1,
                'shaded' => 1,
                'titleFontSize' => 10,
                'fontSize' => 8,
                'xPos' => 50,
                'xOrientation' => 'right',
                'width' => 500,
                'rowGap' => 1,
                'cols' => array(
                    'EINHEIT' => array(
                        'justification' => 'left',
                        'width' => 50
                    ),
                    'SALDO_VM' => array(
                        'justification' => 'right',
                        'width' => 60
                    ),
                    'UMLAGEN' => array(
                        'justification' => 'right',
                        'width' => 55
                    ),
                    'G_SOLL_AKT' => array(
                        'justification' => 'right',
                        'width' => 50
                    ),
                    'ZAHLUNGEN' => array(
                        'justification' => 'right',
                        'width' => 65
                    ),
                    'MWST' => array(
                        'justification' => 'right'
                    ),
                    'ERG' => array(
                        'justification' => 'right',
                        'width' => 50
                    )
                )
            ));

            $content = $pdf->output();

            $dateiname_org = $objekt_name . '-Monatsbericht_m_Auszug';
            $path = Storage::disk('objektberichte')->basePath();
            $this->save_file($dateiname_org, $path, $objekt_id, $content, $monat, $jahr);
            $dateiname = $dateiname_org . '_' . $monat . '_' . $jahr . '.pdf';
            /* Falls kein Objekt ausgewählt */

            // weiterleiten($dateiname);
            $download_link = "<h5><a href=\"" . Storage::disk('objektberichte')->url($objekt_id . '/' . $dateiname) . "\">Monatsbericht $objekt_name für $monat/$jahr HIER</a></h5>";
            hinweis_ausgeben("Monatsbericht ohne Vormieter für $objekt_name wurde erstellt<br>");
            echo $download_link;
            /* Falls kein Objekt ausgewählt */
        }
    }

    /* Funktion zur Erstellung eines Dropdowns für Empfangsgeldkonto */

    function form_buchung_suchen()
    {
        $f = new formular ();
        $f->erstelle_formular("Buchung suchen", NULL);
        $this->dropdown_geldkonten_alle('Geldkonto wählen');
        $f->text_feld("Zu suchender Betrag", "betrag", '', "10", 'betrag', '');
        $f->text_feld("Suchtext im Buchungstext", "ausdruck", '', "30", 'ausdruck', '');
        $f->datum_feld('Anfangsdatum', 'anfangsdatum', '', 'anfangsdatum');
        $f->datum_feld('Enddatum', 'enddatum', '', 'enddatum');
        $f->datum_feld('Kontoauszug', 'kontoauszug', '', 'kontoauszug');
        $this->dropdown_kostenrahmen_nr('Kostenkonto', 'kostenkonto', 'Geldkonto', session()->get('geldkonto_id'), '');
        $buchung = new buchen ();
        $js_typ = "onchange=\"list_kostentraeger('list_kostentraeger', this.value)\"";
        $buchung->dropdown_kostentreager_typen('Kostenträgertyp wählen', 'kostentraeger_typ', 'kostentraeger_typ', $js_typ);
        $js_id = "";
        $buchung->dropdown_kostentreager_ids('Kostenträger', 'kostentraeger_id', 'dd_kostentraeger_id', $js_id);
        $f->hidden_feld("option", "buchung_suchen_1");
        $f->send_button("submit_php", "Suchen");
        echo "&nbsp;";
        $f->send_button("submit_pdf", "PDF-Ausgabe");
        $f->ende_formular();
    }

    function dropdown_geldkonten_alle($label)
    {
        $my_array = DB::select("SELECT GELD_KONTEN.KONTO_ID, GELD_KONTEN.BEZEICHNUNG, GELD_KONTEN.BEGUENSTIGTER, GELD_KONTEN.KONTONUMMER, GELD_KONTEN.BLZ, GELD_KONTEN.INSTITUT FROM GELD_KONTEN_ZUWEISUNG, GELD_KONTEN WHERE GELD_KONTEN.KONTO_ID = GELD_KONTEN_ZUWEISUNG.KONTO_ID && GELD_KONTEN_ZUWEISUNG.AKTUELL = '1' && GELD_KONTEN.AKTUELL = '1' GROUP BY GELD_KONTEN.KONTO_ID ORDER BY GELD_KONTEN.BEZEICHNUNG ASC");

        $numrows = count($my_array);
        if ($numrows > 0) {
            echo "<label for=\"geld_konto_dropdown\">$label</label>\n<select name=\"geld_konto\" id=\"geld_konto_dropdown\" size=\"1\" >\n";
            echo "<option value=\"alle\" selected>Alle Geldkonten</option>\n";
            for ($a = 0; $a < count($my_array); $a++) {
                $konto_id = $my_array [$a] ['KONTO_ID'];
                $bezeichnung = $my_array [$a] ['BEZEICHNUNG'];
                $kontonummer = $my_array [$a] ['KONTONUMMER'];
                $blz = $my_array [$a] ['BLZ'];
                echo "<option value=\"$konto_id\" >$bezeichnung - Knr:$kontonummer - Blz: $blz</option>\n";
            } // end for
            echo "</select>\n";
        } else {
            echo "<b>Kein Geldkonto hinterlegt bzw zugewiesen</b>";
            return FALSE;
        }
    }

    function finde_buchungen($abfrage)
    {
        $result = DB::select($abfrage);
        $numrows = count($result);

        if ($numrows) {
            echo "<table>";
            echo "<tr class=\"feldernamen\"><td colspan=7><b>Suchergebnis</b></td></tr>";
            echo "<tr class=\"feldernamen\"><td>Geldkonto</td><td>Kostenträger</td><td>Datum</td><td>Kostenkonto</td><td>Buchungsnr</td><td>Betrag</td><td width=70%>Vzweck</td></tr>";
            $g = new geldkonto_info ();
            $summe = 0;
            foreach ($result as $row) {
                $datum = date_mysql2german($row ['DATUM']);
                $g_buchungsnummer = $row ['G_BUCHUNGSNUMMER'];
                $betrag_a = nummer_punkt2komma($row ['BETRAG']);
                $betrag = $row ['BETRAG'];
                $vzweck = $row ['VERWENDUNGSZWECK'];
                $kos_typ = $row ['KOSTENTRAEGER_TYP'];
                $kos_id = $row ['KOSTENTRAEGER_ID'];
                $kostenkonto = $row ['KONTENRAHMEN_KONTO'];
                $geldkonto_id = $row ['GELDKONTO_ID'];
                $g->geld_konto_details($geldkonto_id);
                $r = new rechnung ();
                $kostentraeger_bezeichnung = $r->kostentraeger_ermitteln($kos_typ, $kos_id);
                $dat_bu = $row ['GELD_KONTO_BUCHUNGEN_DAT'];
                $link_aendern = "<a href='" . route('web::buchen::legacy', ['option' => 'geldbuchung_aendern', 'geldbuchung_dat' => $dat_bu]) . "'>$g_buchungsnummer ändern</a>";

                echo "<tr><td>$g->geldkonto_bezeichnung<td>$kostentraeger_bezeichnung</td><td>$datum</td><td><b>$kostenkonto</b></td><td><b>$link_aendern</b></td><td>$betrag_a</td><td>$vzweck</td></tr>";
                $summe = $summe + $betrag;
            }
            $summe = nummer_punkt2komma($summe);
            echo "<tr class=\"feldernamen\"><td colspan=5 align=\"right\"><b>SUMME</b></td><td colspan=\"2\"><b>$summe</b></td></tr>";
            echo "</table><br>";
        } else {
            fehlermeldung_ausgeben("Keine Buchung gefunden");
        }
    }
} // end class
