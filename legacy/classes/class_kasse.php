<?php

class kasse extends rechnung
{
    var $kassen_name;
    var $kassen_verwalter;
    var $kassen_id;
    var $kasse_in_rechnung_gestellt;
    var $kasse_aus_rechnung_erhalten;
    var $kasse_direkt_gezahlt;
    var $kassen_stand;
    var $kassen_forderung_offen;
    var $kassen_ausgaben_offen;
    var $kassen_summe_ein_auszahlung;
    public $kostentraeger_id;
    public $kostentraeger_typ;
    public $akt_beleg_text;
    public $akt_datum;
    public $akt_betrag_komma;
    public $akt_betrag_punkt;
    public $akt_zahlungstyp;
    public $akt_kassen_id;
    public $akt_kassenbuch_id;
    public $akt_kassenbuch_dat;
    public $kassen_partner_id;

    function dropdown_kassen($label, $name, $id)
    {
        $result = DB::select("SELECT KASSEN_ID, KASSEN_NAME, KASSEN_VERWALTER FROM `KASSEN` WHERE AKTUELL = '1'");
        if (!empty($result)) {
            echo "<input type=\"hidden\" name=\"empfaenger_typ\" value=\"Kasse\">";
            echo "<div class='input-field'>";
            echo "<select name=\"$name\" id=\"$id\">";
            foreach ($result as $row) {
                if (session()->has('kasse') && session()->get('kasse') == $row ['KASSEN_ID']) {
                    echo "<option value=\"$row[KASSEN_ID]\" selected>$row[KASSEN_NAME] - $row[KASSEN_VERWALTER]</option>";
                } else {
                    echo "<option value=\"$row[KASSEN_ID]\">$row[KASSEN_NAME] - $row[KASSEN_VERWALTER]</option>";
                }
            }
            echo "</select>";
            echo "<label for=\"$id\">$label</label>";
            echo "</div>";
        } else {
            return false;
        }
    }

    function get_kassen_info($kassen_id)
    {
        $result = DB::select("SELECT KASSEN_NAME, KASSEN_VERWALTER, PARTNER_ID FROM `KASSEN` RIGHT JOIN (KASSEN_PARTNER) ON (KASSEN.KASSEN_ID = KASSEN_PARTNER.KASSEN_ID) WHERE KASSEN.AKTUELL = '1' && KASSEN_PARTNER.AKTUELL = '1' && KASSEN.KASSEN_ID='1' ORDER BY KASSEN_DAT DESC LIMIT 0,1");

        if (!empty($result)) {
            $row = $result[0];
            $this->kassen_name = $row ['KASSEN_NAME'];
            $this->kassen_verwalter = $row ['KASSEN_VERWALTER'];
            $this->kassen_id = $kassen_id;
            $this->summe_ein_ausgaben($kassen_id);
            $this->kasse_in_rechnung_gestellt($kassen_id);
            $this->kasse_einnahme_durch_zahlung($kassen_id);
            $this->ausgabe_durch_barzahlung($kassen_id);
            $this->kasse_offen_zu_zahlen($kassen_id);
            $this->kassen_partner_id = $row ['PARTNER_ID'];
        } else {
            return false;
        }
    }

    function summe_ein_ausgaben($kassen_id)
    {
        /* Gesamtsumme aller Kasseneinzahlungen aus dem Kassenbuch */
        $result = DB::select("SELECT SUM(BETRAG) AS EIN_AUSGABEN FROM KASSEN_BUCH WHERE KASSEN_ID='$kassen_id' && AKTUELL = '1'");
        if (!empty($result)) {
            return $result[0];
        } else {
            return false;
        }
    }

    function kasse_in_rechnung_gestellt($kassen_id)
    {
        /* Gesamtsumme aller in Rechnung gstellten Summen */
        $result = DB::select("SELECT SUM(SKONTOBETRAG) AS kasse_in_rechnung_gestellt FROM RECHNUNGEN WHERE AUSSTELLER_TYP='Kasse' && AUSSTELLER_ID='$kassen_id' && AKTUELL = '1'");
        if (!empty($result)) {
            $row = $result[0];
            $this->kasse_in_rechnung_gestellt = $row ['kasse_in_rechnung_gestellt'];
        } else {
            return false;
        }
    }

    function kasse_einnahme_durch_zahlung($kassen_id)
    {
        /* Gesamtsumme aller in Rechnung gestellten Summen und bezahlten */
        $result = DB::select("SELECT SUM(SKONTOBETRAG) AS kasse_aus_rechnung_erhalten FROM RECHNUNGEN WHERE AUSSTELLER_TYP='Kasse' && AUSSTELLER_ID='$kassen_id' && STATUS_BEZAHLT='1' && AKTUELL = '1'");
        if (!empty($result)) {
            $row = $result[0];
            $this->kasse_aus_rechnung_erhalten = $row ['kasse_aus_rechnung_erhalten'];
        } else {
            return false;
        }
    }

    /* *Gesamtsumme aller aus der Kasse noch zu bezahlenden Rechnungssummen */

    function ausgabe_durch_barzahlung($kassen_id)
    {
        /* Gesamtsumme aller aus der Kasse bezahlten Rechnungssummen */
        $result = DB::select("SELECT SUM(SKONTOBETRAG) AS kasse_direkt_gezahlt FROM RECHNUNGEN WHERE EMPFAENGER_TYP='Kasse' && EMPFAENGER_ID='$kassen_id' && STATUS_BEZAHLT='1' && AKTUELL = '1'");
        if (!empty($result)) {
            $row = $result[0];
            $this->kasse_direkt_gezahlt = $row ['kasse_direkt_gezahlt'];
        } else {
            return false;
        }
    }

    function kasse_offen_zu_zahlen($kassen_id)
    {
        $result = DB::select("SELECT SUM(SKONTOBETRAG) AS kassen_ausgaben_offen FROM RECHNUNGEN WHERE EMPFAENGER_TYP='Kasse' && EMPFAENGER_ID='$kassen_id' && STATUS_BEZAHLT='0' && AKTUELL = '1'");
        if (!empty($result)) {
            $row = $result[0];
            $this->kassen_ausgaben_offen = $row ['kassen_ausgaben_offen'];
        } else {
            return false;
        }
    }
    
    function buchungsmaske_kasse($kassen_id)
    {
        $form = new formular ();
        $form->erstelle_formular("Buchungsmaske Kasseneinnahmen und Ausgaben", NULL);
        $form->hidden_feld('kassen_id', $kassen_id);
        $datum_feld = 'document.getElementById("datum").value';
        $js_datum = "onchange='check_datum($datum_feld)'";
        $form->text_feld('Datum', 'datum', '', '10', 'datum', $js_datum);
        $this->dropdown_einausgaben('Zahlungstyp', 'zahlungstyp', 'zahlungstyp');
        $form->text_feld('Betrag', 'betrag', '', '10', 'betrag', '');
        $form->text_bereich('Beleg/Text', 'beleg_text', '', 10, 5, 'beleg_text');
        $buchung = new buchen ();
        $js_typ = "onchange=\"list_kostentraeger('list_kostentraeger', this.value)\"";
        $buchung->dropdown_kostentreager_typen('Kostenträgertyp', 'kostentraeger_typ', 'kostentraeger_typ', $js_typ);
        $js_id = "";
        $buchung->dropdown_kostentreager_ids('Kostenträger', 'kostentraeger_id', 'dd_kostentraeger_id', $js_id);
        $form->hidden_feld("option", "kassendaten_gesendet");
        $form->send_button("submit", "Speichern");
        $form->ende_formular();
    }

    function dropdown_einausgaben($beschreibung, $name, $id)
    {
        echo "<label for=\"$id\">$beschreibung</label>";
        echo "<select name=\"$name\" id=\"$id\">";
        echo "<option value=\"Einnahmen\">Einnahmen</option>";
        echo "<option value=\"Ausgaben\">Ausgaben</option>";
        echo "</select>";
    }

    function buchungsmaske_kasse_aendern($buchungs_dat)
    {
        $form = new formular ();
        $form->erstelle_formular("Buchungsmaske Kasseneinnahmen und Ausgaben", NULL);
        $this->kassenbuch_dat_infos($buchungs_dat);
        $form->hidden_feld("kassen_dat_alt", $buchungs_dat);
        $form->hidden_feld("kassen_buch_id", $this->akt_kassenbuch_id);
        if (!empty ($this->kostentraeger_typ) && $this->kostentraeger_typ == 'Rechnung') {
            $form->hidden_feld("kostentraeger_typ", $this->kostentraeger_typ);
            $form->hidden_feld("kostentraeger_id", $this->kostentraeger_id);
        }

        $form->hidden_feld('kassen_id', $this->akt_kassen_id);

        $form->text_feld('Datum', 'datum', $this->akt_datum, '10', 'datum', '');
        $this->dropdown_einausgaben_markiert('Zahlungstyp', 'zahlungstyp', 'zahlungstyp', $this->akt_zahlungstyp);
        $form->text_feld('Betrag', 'betrag', $this->akt_betrag_komma, '10', 'betrag', '');
        $form->text_bereich('Beleg/Text', 'beleg_text', $this->akt_beleg_text, 10, 5, 'beleg_text');
        $akt_kostentraeger_bez = $this->kostentraeger_beschreibung($this->kostentraeger_typ, $this->kostentraeger_id);
        $akt_kostentraeger_bez = str_replace("<b>", "", $akt_kostentraeger_bez);
        $akt_kostentraeger_bez = str_replace("</b>", "", $akt_kostentraeger_bez);
        if (empty ($this->kostentraeger_typ) or $this->kostentraeger_typ != 'Rechnung') {
            $form->text_feld_inaktiv('Kostenträger aktuell', 'kostentraeger', $akt_kostentraeger_bez, '30', 'kostentraeger');
            $buchung = new buchen ();
            $js_typ = "onchange=\"list_kostentraeger('list_kostentraeger', this.value)\"";
            $buchung->dropdown_kostentreager_typen('Kostenträgertyp wählen', 'kostentraeger_typ', 'kostentraeger_typ', $js_typ);
            $js_id = "";
            $buchung->dropdown_kostentreager_ids('Kostenträger', 'kostentraeger_id', 'dd_kostentraeger_id', $js_id);
        }
        $form->hidden_feld("option", "kassendaten_aendern");
        $form->send_button("submit", "Änderungen speichern");
        $form->ende_formular();
    }

    function kassenbuch_dat_infos($buchungs_dat)
    {
        $result = DB::select("SELECT * FROM KASSEN_BUCH WHERE KASSEN_BUCH_DAT='$buchungs_dat' && AKTUELL='1' LIMIT 0,1");
        $row = $result[0];
        $this->akt_kassenbuch_dat = $row ['KASSEN_BUCH_DAT'];
        $this->akt_kassenbuch_id = $row ['KASSEN_BUCH_ID'];
        $this->akt_kassen_id = $row ['KASSEN_ID'];
        $this->akt_zahlungstyp = $row ['ZAHLUNGSTYP'];
        $this->akt_betrag_punkt = $row ['BETRAG'];
        $this->akt_betrag_komma = nummer_punkt2komma($row ['BETRAG']);
        $this->akt_datum = date_mysql2german($row ['DATUM']);
        $this->akt_beleg_text = $row ['BELEG_TEXT'];
        $this->kostentraeger_typ = $row ['KOSTENTRAEGER_TYP'];
        $this->kostentraeger_id = $row ['KOSTENTRAEGER_ID'];
    }

    function dropdown_einausgaben_markiert($beschreibung, $name, $id, $zahlungstyp)
    {
        echo "<label for=\"$id\">$beschreibung</label>";
        echo "<select name=\"$name\" id=\"$id\">";
        if ($zahlungstyp == 'Einnahmen') {
            echo "<option value=\"Einnahmen\">Einnahmen</option>";
            echo "<option value=\"Ausgaben\">Ausgaben</option>";
        }
        if ($zahlungstyp == 'Ausgaben') {
            echo "<option value=\"Ausgaben\">Ausgaben</option>";
            echo "<option value=\"Einnahmen\">Einnahmen</option>";
        }
        echo "</select>";
    }

    function kostentraeger_beschreibung($kostentraeger_typ, $kostentraeger_id)
    {
        if ($kostentraeger_typ == 'Objekt') {
            $a = new objekt ();
            $k_bezeichnung = $a->get_objekt_name($kostentraeger_id);
            return "<b>$k_bezeichnung</b>";
        }
        if ($kostentraeger_typ == 'Haus') {
            $a = new haus ();
            $a->get_haus_info($kostentraeger_id);
            $k_bezeichnung = "<b>$a->haus_strasse $a->haus_nummer $a->haus_stadt</b>";
            return $k_bezeichnung;
        }
        if ($kostentraeger_typ == 'Einheit') {
            $a = new einheit ();
            $a->get_einheit_info($kostentraeger_id);
            $k_bezeichnung = "<b>$a->einheit_kurzname</b>";
            return $k_bezeichnung;
        }
    }

    function speichern_in_kassenbuch($kassen_id, $betrag, $datum, $zahlungstyp, $beleg_text, $kostentraeger_typ, $kostentraeger_bez)
    {
        $buchung = new buchen ();
        if ($kostentraeger_typ !== 'Rechnung') {
            $kostentraeger_id = $buchung->kostentraeger_id_ermitteln($kostentraeger_typ, $kostentraeger_bez);
        } else {
            $kostentraeger_id = $kostentraeger_bez;
        }

        $letzte_kb_id = $this->letzte_kassenbuch_id($kassen_id);
        $letzte_kb_id = $letzte_kb_id + 1;

        $datum = date_german2mysql($datum);
        $betrag1 = nummer_komma2punkt($betrag);
        $db_abfrage = "INSERT INTO KASSEN_BUCH VALUES (NULL, '$letzte_kb_id','$kassen_id', '$zahlungstyp','$betrag1', '$datum', '$beleg_text', '1', '$kostentraeger_typ', '$kostentraeger_id')";
        DB::insert($db_abfrage);
        echo "Betrag von $betrag € wurde ins Kassenbuch eingetragen!<br>";
        echo "Sie werden zum Kassenbuch weitergeleitet!";
        weiterleiten_in_sec(route('web::kassen::legacy', ['option' => 'kassenbuch'], false), 2);
    }

    function letzte_kassenbuch_id($kassen_id)
    {
        $result = DB::select("SELECT KASSEN_BUCH_ID FROM KASSEN_BUCH WHERE KASSEN_ID='$kassen_id' && AKTUELL='1' ORDER BY KASSEN_BUCH_ID DESC LIMIT 0,1");
        $row = $result[0];
        return $row ['KASSEN_BUCH_ID'];
    }

    function speichern_in_kassenbuch_id($kassen_id, $betrag, $datum, $zahlungstyp, $beleg_text, $kostentraeger_typ, $kostentraeger_bez, $letzte_kb_id)
    {
        $buchung = new buchen ();
        if ($kostentraeger_typ !== 'Rechnung') {
            $kostentraeger_id = $buchung->kostentraeger_id_ermitteln($kostentraeger_typ, $kostentraeger_bez);
        } else {
            $kostentraeger_id = $kostentraeger_bez;
        }

        $datum = date_german2mysql($datum);
        $betrag1 = nummer_komma2punkt($betrag);
        $db_abfrage = "INSERT INTO KASSEN_BUCH VALUES (NULL, '$letzte_kb_id','$kassen_id', '$zahlungstyp','$betrag1', '$datum', '$beleg_text', '1', '$kostentraeger_typ', '$kostentraeger_id')";
        DB::insert($db_abfrage);
        echo "Betrag von $betrag € wurde ins Kassenbuch eingetragen!<br>";
        echo "Sie werden zum Kassenbuch weitergeleitet!";
        weiterleiten_in_sec(route('web::kassen::legacy', ['option' => 'kassenbuch'], false), 2);
    }

    function kassenbuch_dat_deaktivieren($buchungs_dat)
    {
        $db_abfrage = "UPDATE KASSEN_BUCH SET AKTUELL='0' WHERE KASSEN_BUCH_DAT='$buchungs_dat'";
        DB::update($db_abfrage);
        protokollieren('KASSEN_BUCH', $buchungs_dat, $buchungs_dat);
        echo "Alter Eintrag deaktiviert<br>";
    }

    function rechnung_in_kassenbuch($kassen_id, $betrag, $datum, $zahlungstyp, $beleg_text, $kostentraeger_typ, $kostentraeger_bez)
    {
        $buchung = new buchen ();
        if ($kostentraeger_typ !== 'Rechnung') {
            $kostentraeger_id = $buchung->kostentraeger_id_ermitteln($kostentraeger_typ, $kostentraeger_bez);
        } else {
            $kostentraeger_id = $kostentraeger_bez;
        }

        $letzte_kb_id = $this->letzte_kassenbuch_id($kassen_id);
        $letzte_kb_id = $letzte_kb_id + 1;

        $datum = date_german2mysql($datum);
        $db_abfrage = "INSERT INTO KASSEN_BUCH VALUES (NULL, '$letzte_kb_id','$kassen_id', '$zahlungstyp','$betrag', '$datum', '$beleg_text', '1', '$kostentraeger_typ', '$kostentraeger_id')";
        DB::insert($db_abfrage);
        echo "Betrag von $betrag € wurde ins Kassenbuch eingetragen!<br>";
    }

    function kassenbuch_anzeigen($jahr, $kassen_id)
    {
        $my_array = DB::select("SELECT * FROM KASSEN_BUCH WHERE KASSEN_ID = '$kassen_id' && AKTUELL='1' && DATUM BETWEEN '$jahr-01-01' AND '$jahr-12-31' ORDER BY DATUM, KASSEN_BUCH_ID ASC");
        $numrows = count($my_array);
        if ($numrows > 0) {
            echo "<table>";
            echo "<tr class=feldernamen><td>Nr.</td><td>Datum</td><td>Einnahmen</td><td>Ausgaben</td><td>Beleg / Text</td><td>Info</td><td>Optionen</td></tr>";
            $vorjahr = $jahr - 1;
            $kassenstand_vorjahr = $this->kassenstand_vorjahr($vorjahr, $kassen_id);
            $kassenstand_vorjahr_komma = nummer_punkt2komma($kassenstand_vorjahr);
            echo "<tr><td></td><td>01.01.$jahr</td>";
            echo "<td>$kassenstand_vorjahr_komma</td><td></td>";
            echo "<td><b>Kassenstand Vorjahr</b></td><td></td></tr>";
            $zaehler = 0;
            for ($a = 0; $a < $numrows; $a++) {
                $zaehler++;
                $zeile = $a + 1;
                $dat = $my_array [$a] ['KASSEN_BUCH_DAT'];
                $datum = $my_array [$a] ['DATUM'];
                $datum = date_mysql2german($datum);
                $betrag = $my_array [$a] ['BETRAG'];
                $betrag = nummer_punkt2komma($betrag);
                $zahlungstyp = $my_array [$a] ['ZAHLUNGSTYP'];
                $beleg_text = $my_array [$a] ['BELEG_TEXT'];
                $kostentraeger_typ = $my_array [$a] ['KOSTENTRAEGER_TYP'];
                $kostentraeger_id = $my_array [$a] ['KOSTENTRAEGER_ID'];
                if ($kostentraeger_typ == 'Rechnung') {
                    $info_link = "<a href='" . route('web::rechnungen.show', ['id' => $kostentraeger_id]) . "'>$kostentraeger_typ</a>";
                } else {
                    $info_link = $this->kostentraeger_beschreibung($kostentraeger_typ, $kostentraeger_id);
                }
                $aendern_link = "<a href='" . route('web::kassen::legacy', ['option' => 'kasseneintrag_aendern', 'eintrag_dat' => $dat]) . "'>Ändern</a>";
                $loeschen_link = "<a href='" . route('web::kassen::legacy', ['option' => 'kasseneintrag_loeschen', 'eintrag_dat' => $dat]) . "'>Löschen</a>";
                if ($zaehler == 1) {
                    echo "<tr class=\"zeile1\"><td>$zeile</td><td>$datum</td>";
                }
                if ($zaehler == 2) {
                    echo "<tr class=\"zeile2\"><td>$zeile</td><td>$datum</td>";
                    $zaehler = 0;
                }
                if ($zahlungstyp == 'Einnahmen') {
                    echo "<td>$betrag</td><td></td><td>$beleg_text</td><td>$info_link</td><td>$aendern_link $loeschen_link</td></tr>";
                }
                if ($zahlungstyp == 'Ausgaben') {
                    echo "<td></td><td>$betrag</td><td>$beleg_text</td><td>$info_link</td><td>$aendern_link $loeschen_link</td></tr>";
                }
            }
            $summe_einnahmen = $this->summe_einnahmen($jahr, $kassen_id);
            $summe_einnahmen = $summe_einnahmen + $kassenstand_vorjahr;
            $summe_ausgaben = $this->summe_ausgaben($jahr, $kassen_id);
            $kassenstand = $summe_einnahmen - $summe_ausgaben;
            // $kassenstand = number_format($kassenstand, ',' , '2', '');
            // $summe_einnahmen = nummer_punkt2komma($summe_einnahmen);
            // $summe_ausgaben = nummer_punkt2komma($summe_ausgaben);
            // $kassenstand = sprintf("%01.2f", $kassenstand);
            $kassenstand = nummer_punkt2komma($kassenstand);
            echo "<tr class=feldernamen><td></td><td></td><td>$summe_einnahmen €</td><td>$summe_ausgaben €</td><td>Kassenstand: $kassenstand €</td><td></td><td></td></tr>";
            echo "</table>";
        } else {
            echo "kassenbuch leer";
        }
    }

    function kassenstand_vorjahr($vorjahr, $kassen_id)
    {
        $einnahmen_vorjahr = $this->summe_einnahmen_bis_vorjahr($vorjahr, $kassen_id);
        $ausgaben_vorjahr = $this->summe_ausgaben_bis_vorjahr($vorjahr, $kassen_id);
        $kassenstand_vorjahr = $einnahmen_vorjahr - $ausgaben_vorjahr;
        return $kassenstand_vorjahr;
    }

    function summe_einnahmen_bis_vorjahr($vorjahr, $kassen_id)
    {
        $result = DB::select("SELECT SUM(BETRAG) AS BETRAG FROM KASSEN_BUCH WHERE KASSEN_ID='$kassen_id' && ZAHLUNGSTYP='Einnahmen' && AKTUELL='1' && DATUM <='$vorjahr-12-31'");
        $row = $result[0];
        return $row ['BETRAG'];
    }

    function summe_ausgaben_bis_vorjahr($vorjahr, $kassen_id)
    {
        $result = DB::select("SELECT SUM(BETRAG) AS BETRAG FROM KASSEN_BUCH WHERE KASSEN_ID='$kassen_id' && ZAHLUNGSTYP='Ausgaben' && AKTUELL='1' && DATUM <='$vorjahr-12-31'");
        $row = $result[0];
        return $row ['BETRAG'];
    }

    function summe_einnahmen($jahr, $kassen_id)
    {
        $result = DB::select("SELECT SUM(BETRAG) AS BETRAG FROM KASSEN_BUCH WHERE KASSEN_ID='$kassen_id' && ZAHLUNGSTYP='Einnahmen' && AKTUELL='1' && DATUM BETWEEN '$jahr-01-01' AND '$jahr-12-31' ORDER BY KASSEN_BUCH_ID DESC LIMIT 0,1");
        $row = $result[0];
        return $row ['BETRAG'];
    }

    function summe_ausgaben($jahr, $kassen_id)
    {
        $result = DB::select("SELECT SUM(BETRAG) AS BETRAG FROM KASSEN_BUCH WHERE KASSEN_ID='$kassen_id' && ZAHLUNGSTYP='Ausgaben' && AKTUELL='1' && DATUM BETWEEN '$jahr-01-01' AND '$jahr-12-31' ORDER BY KASSEN_BUCH_ID DESC LIMIT 0,1");
        $row = $result[0];
        return $row ['BETRAG'];
    }

    function monatskassenbuch_anzeigen($monat, $jahr, $kassen_id)
    {
        $zeile = $this->anzahl_buchungen_bis_monat($monat, $jahr, $kassen_id);
        $zeile = $zeile + 1;

        $my_array = DB::select("SELECT * FROM KASSEN_BUCH WHERE KASSEN_ID = '$kassen_id' && AKTUELL='1' && DATE_FORMAT(DATUM, '%Y-%m') = '$jahr-$monat' ORDER BY DATUM, KASSEN_BUCH_ID ASC");
        $numrows = count($my_array);
        if ($numrows) {
            echo "<table>";
            echo "<tr class=feldernamen><td>Nr.</td><td>Datum</td><td>Einnahmen</td><td>Ausgaben</td><td>Beleg / Text</td><td>Info</td><td>Optionen</td></tr>";
            $vorjahr = $jahr - 1;
            $kassenstand_vorjahr = $this->kassenstand_vorjahr($vorjahr, $kassen_id);
            $kassenstand_vorjahr_komma = nummer_punkt2komma($kassenstand_vorjahr);
            echo "<tr><td></td><td>01.01.$jahr</td>";
            echo "<td>$kassenstand_vorjahr_komma</td><td></td>";
            // echo "<td>$kassenstand_vormonat</td><td></td>";
            echo "<td><b>Kassenstand Vorjahr</b></td><td></td></tr>";
            $zaehler = 0;
            for ($a = 0; $a < $numrows; $a++) {
                $zaehler++;
                $dat = $my_array [$a] ['KASSEN_BUCH_DAT'];
                $datum = $my_array [$a] ['DATUM'];
                $datum = date_mysql2german($datum);
                $betrag = $my_array [$a] ['BETRAG'];
                $betrag = nummer_punkt2komma($betrag);
                $zahlungstyp = $my_array [$a] ['ZAHLUNGSTYP'];
                $beleg_text = $my_array [$a] ['BELEG_TEXT'];
                $kostentraeger_typ = $my_array [$a] ['KOSTENTRAEGER_TYP'];
                $kostentraeger_id = $my_array [$a] ['KOSTENTRAEGER_ID'];
                if ($kostentraeger_typ == 'Rechnung') {
                    $info_link = "<a href='" . route('web::rechnungen.show', ['id' => $kostentraeger_id]) . "'>$kostentraeger_typ</a>";
                } else {
                    $info_link = $this->kostentraeger_beschreibung($kostentraeger_typ, $kostentraeger_id);
                }
                $aendern_link = "<a href='" . route('web::kassen::legacy', ['option' => 'kasseneintrag_aendern', 'eintrag_dat' => $dat]) . "'>Ändern</a>";
                $loeschen_link = "<a href='" . route('web::kassen::legacy', ['option' => 'kasseneintrag_loeschen', 'eintrag_dat' => $dat]) . "'>Löschen</a>";
                if ($zaehler == 1) {
                    echo "<tr class=\"zeile1\"><td>$zeile</td><td>$datum</td>";
                }
                if ($zaehler == 2) {
                    echo "<tr class=\"zeile2\"><td>$zeile</td><td>$datum</td>";
                    $zaehler = 0;
                }
                if ($zahlungstyp == 'Einnahmen') {
                    echo "<td>$betrag</td><td></td><td>$beleg_text</td><td>$info_link</td><td>$aendern_link $loeschen_link</td></tr>";
                }
                if ($zahlungstyp == 'Ausgaben') {
                    echo "<td></td><td>$betrag</td><td>$beleg_text</td><td>$info_link</td><td>$aendern_link $loeschen_link</td></tr>";
                }

                $zeile = $zeile + 1;
            }
            $summe_einnahmen = $this->summe_einnahmen($jahr, $kassen_id);
            $summe_einnahmen = $summe_einnahmen + $kassenstand_vorjahr;
            $summe_ausgaben = $this->summe_ausgaben($jahr, $kassen_id);
            $kassenstand = $summe_einnahmen - $summe_ausgaben;
            $kassenstand = nummer_punkt2komma($kassenstand);
            echo "<tr class=feldernamen><td></td><td></td><td>$summe_einnahmen €</td><td>$summe_ausgaben €</td><td>Kassenstand: $kassenstand €</td><td></td><td></td></tr>";
            echo "</table>";
        } else {
            echo "kassenbuch leer";
        }
    }

    function anzahl_buchungen_bis_monat($monat, $jahr, $kassen_id)
    {
        $result = DB::select("SELECT COUNT(*) AS ANZAHL FROM KASSEN_BUCH WHERE KASSEN_ID = '$kassen_id' && AKTUELL='1' && DATE_FORMAT(DATUM, '%Y-%m') < '$jahr-$monat' && DATE_FORMAT(DATUM, '%Y') = '$jahr'  ORDER BY DATUM, KASSEN_BUCH_ID ASC");
        return $result[0]['ANZAHL'];
    }

    function kassenstand_vormonat($aktmonat, $jahr, $kassen_id)
    {
        if ($aktmonat < 2) {
            $vormonat = '12';
            $jahr = $jahr - 1;
        } else {
            $vormonat = $aktmonat - 1;
        }

        $einnahmen_bis_vormonat = $this->summe_einnahmen_bis_monat($jahr, $vormonat, $kassen_id);
        $ausgaben_bis_vormonat = $this->summe_ausgaben_bis_monat($jahr, $vormonat, $kassen_id);
        $kassenstand_monat = $einnahmen_bis_vormonat - $ausgaben_bis_vormonat;
        return $kassenstand_monat;
    }

    function summe_einnahmen_bis_monat($jahr, $monat, $kassen_id)
    {
        $result = DB::select("SELECT SUM(BETRAG) AS BETRAG FROM KASSEN_BUCH WHERE KASSEN_ID='$kassen_id' && ZAHLUNGSTYP='Einnahmen' && AKTUELL='1' && DATE_FORMAT(DATUM, '%y-%m') < '$jahr-$monat'");
        $row = $result[0];
        return $row ['BETRAG'];
    }

    function summe_ausgaben_bis_monat($jahr, $monat, $kassen_id)
    {
        $result = DB::select("SELECT SUM(BETRAG) AS BETRAG FROM KASSEN_BUCH WHERE KASSEN_ID='$kassen_id' && ZAHLUNGSTYP='Ausgaben' && AKTUELL='1' && DATE_FORMAT(DATUM, '%Y-%m') < '$jahr-$monat'");
        $row = $result[0];
        return $row ['BETRAG'];
    }

    function kassenbuch_als_excel($jahr, $kassen_id)
    {
        $fileName = 'kasse.xls';
        ob_clean(); // ausgabepuffer leeren
        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=$fileName");
        $my_array = DB::select("SELECT * FROM KASSEN_BUCH WHERE KASSEN_ID = '$kassen_id' && AKTUELL='1' && DATUM BETWEEN '$jahr-01-01' AND '$jahr-12-31' ORDER BY DATUM, KASSEN_BUCH_ID ASC");
        $numrows = count($my_array);
        if ($numrows > 0) {
            echo "<table>";
            echo "<tr class=feldernamen><td>Nr.</td><td>Datum</td><td>Einnahmen</td><td>Ausgaben</td><td>Beleg / Text</td><td>Info</td><td>Optionen</td></tr>";
            $vorjahr = $jahr - 1;
            $kassenstand_vorjahr = $this->kassenstand_vorjahr($vorjahr, $kassen_id);
            echo "<tr><td></td><td>01.01.$jahr</td>";
            echo "<td>$kassenstand_vorjahr</td><td></td>";
            echo "<td><b>Kassenstand Vorjahr</b></td><td></td></tr>";
            $zaehler = 0;
            for ($a = 0; $a < $numrows; $a++) {
                $zaehler++;
                $zeile = $a + 1;
                $dat = $my_array [$a] ['KASSEN_BUCH_DAT'];
                $datum = $my_array [$a] ['DATUM'];
                $datum = date_mysql2german($datum);
                $betrag = $my_array [$a] ['BETRAG'];
                $betrag = nummer_punkt2komma($betrag);
                $zahlungstyp = $my_array [$a] ['ZAHLUNGSTYP'];
                $beleg_text = $my_array [$a] ['BELEG_TEXT'];
                $kostentraeger_typ = $my_array [$a] ['KOSTENTRAEGER_TYP'];
                $kostentraeger_id = $my_array [$a] ['KOSTENTRAEGER_ID'];
                if ($kostentraeger_typ == 'Rechnung') {
                    $info_link = "<a href='" . route('web::rechnungen.show', ['id' => $kostentraeger_id]) . "'>$kostentraeger_typ</a>";
                } else {
                    $info_link = $this->kostentraeger_beschreibung($kostentraeger_typ, $kostentraeger_id);
                }
                $aendern_link = "<a href='" . route('web::kassen::legacy', ['option' => 'kasseneintrag_aendern', 'eintrag_dat' => $dat]) . "'>Ändern</a>";
                $loeschen_link = "<a href='" . route('web::kassen::legacy', ['option' => 'kasseneintrag_loeschen', 'eintrag_dat' => $dat]) . "'>Löschen</a>";
                if ($zaehler == 1) {
                    echo "<tr class=\"zeile1\"><td>$zeile</td><td>$datum</td>";
                }
                if ($zaehler == 2) {
                    echo "<tr class=\"zeile2\"><td>$zeile</td><td>$datum</td>";
                    $zaehler = 0;
                }
                if ($zahlungstyp == 'Einnahmen') {
                    echo "<td>$betrag</td><td></td><td>$beleg_text</td><td>$info_link</td><td>$aendern_link $loeschen_link</td></tr>";
                }
                if ($zahlungstyp == 'Ausgaben') {
                    echo "<td></td><td>$betrag</td><td>$beleg_text</td><td>$info_link</td><td>$aendern_link $loeschen_link</td></tr>";
                }
            }
            $summe_einnahmen = $this->summe_einnahmen($jahr, $kassen_id);
            $summe_einnahmen = $summe_einnahmen + $kassenstand_vorjahr;
            $summe_ausgaben = $this->summe_ausgaben($jahr, $kassen_id);
            $kassenstand = $summe_einnahmen - $summe_ausgaben;
            // $kassenstand = number_format($kassenstand, ',' , '2', '');
            $summe_einnahmen = nummer_punkt2komma($summe_einnahmen);
            $summe_ausgaben = nummer_punkt2komma($summe_ausgaben);
            $kassenstand = sprintf("%01.2f", $kassenstand);
            $kassenstand = nummer_punkt2komma($kassenstand);
            echo "<tr class=feldernamen><td></td><td></td><td>$summe_einnahmen €</td><td>$summe_ausgaben €</td><td>Kassenstand: $kassenstand €</td><td></td><td></td></tr>";
            echo "</table>";
        } else {
            echo "kassenbuch leer";
        }
    }

    function kassen_auswahl()
    {
        if (request()->filled('kasse')) {
            session()->put('kasse', request()->input('kasse'));
        }

        $form = new formular ();
        if (!session()->has('kasse')) {
            $form->erstelle_formular("Kasse wählen", NULL);
        } else {
            $form->erstelle_formular("Kassenauswahl - Aktuell: Kasse " . session()->get('kasse'), NULL);
        }
        $result = DB::select("SELECT KASSEN_ID, KASSEN_NAME, KASSEN_VERWALTER FROM `KASSEN` WHERE AKTUELL = '1'");
        echo "<p class=\"objekt_auswahl\">";
        if (!empty($result)) {
            foreach($result as $row) {
                $kassen_link = "<a class=\"objekt_auswahl_buchung\" href='" . route('web::kassen::legacy', ['kasse' => $row['KASSEN_ID'], 'option' => 'kassenbuch']) . "'>$row[KASSEN_NAME] - $row[KASSEN_VERWALTER]</a>";
                echo "| $kassen_link ";
            }
            echo "</p>";
        } else {
            echo "Keine Kasse vorhanden";
            return false;
        }
        $form->ende_formular();
    }
} // end class kasse
