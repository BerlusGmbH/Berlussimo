<?php

/* Klasse Rechnungen bzw. Erweiterung der Klasse Rechnung aus class_berlussimo wegen Positionserfassung */

class rechnungen
{
    /* Diese Variablen werden von rechnung_grunddaten_holen($rechnung_id) gesetzt */
    var $belegnr;
    var $rechnungsnummer;
    var $aussteller_ausgangs_rnr;
    var $empfaenger_eingangs_rnr;
    var $rechnungstyp;
    var $rechnungsdatum;
    var $eingangsdatum;
    var $faellig_am;
    var $bezahlt_am;
    var $rechnungs_netto;
    var $rechnungs_brutto;
    var $rechnungs_mwst;
    var $rechnungs_skontobetrag;
    var $rechnungs_aussteller_typ;
    var $rechnungs_aussteller_id;
    var $rechnungs_aussteller_name;
    var $rechnungs_empfaenger_typ;
    var $rechnungs_empfaenger_id;
    var $rechnungs_empfaenger_name;
    var $status_erfasst;
    var $status_vollstaendig;
    var $status_zugewiesen;
    var $status_zahlung_freigegeben;
    var $status_bezahlt;
    var $kurzbeschreibung;
    var $skonto;
    var $empfangs_geld_konto;
    var $rechnungs_aussteller_strasse;
    var $rechnungs_aussteller_hausnr;
    var $rechnungs_aussteller_plz;
    var $rechnungs_aussteller_ort;
    var $rechnungs_empfaenger_strasse;
    var $rechnungs_empfaenger_hausnr;
    var $rechnungs_empfaenger_plz;
    var $rechnungs_empfaenger_ort;
    public $rechnung_dat;
    public $kb;
    public $status_bestaetigt;
    public $rechnungs_skontoabzug;
    public $anzahl_positionen;
    public $rechnungs_kuerzel;
    public $rechnungsnummer_kuerzel;
    public $rechnung_aussteller_partner_id;
    public $rechnung_empfaenger_partner_id;
    public $rechnungs_typ_druck;
    public $anzahl_positionen_aktuell;
    public $ursprungs_array;
    public $rg_betrag;
    public $rg_prozent;
    public $v_beleg_nr;
    public $anz_preise;
    public $v_rabatt_satz;
    public $v_preis;
    public $rechnungs_brutto_schluss;
    public $rechnungs_mwst_schluss;
    public $rechnungs_netto_schluss;
    public $rechnungs_skontoabzug_schluss;
    public $rechnungs_brutto_schluss_a;
    public $ursprungs_array_a;

    function get_summe_kosten_pool($empf_typ, $empf_id)
    {
        $result = DB::select("SELECT SUM((`GESAMT_SUMME`/100)*(100-RABATT_SATZ)) AS GESAMT  FROM `KONTIERUNG_POSITIONEN` WHERE `KOSTENTRAEGER_TYP` = '$empf_typ' AND `KOSTENTRAEGER_ID` = '$empf_id' AND `WEITER_VERWENDEN` = '1' AND `AKTUELL` = '1'");
        if (!empty($result)) {
            return nummer_punkt2komma_t($result[0]['GESAMT']);
        }
    }

    function verbindlichkeiten($jahr)
    {
        if (!session()->has('partner_id')) {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\InfoMessage('Bitte wählen Sie einen Partner.'),
                0,
                null,
                route('web::rechnungen::legacy', ['option' => 'partner_wechseln'])
            );
        } else {
            $p_id = session()->get('partner_id');
            $abfrage = "SELECT * FROM `RECHNUNGEN` WHERE `RECHNUNGSTYP` IN ('Rechnung') AND `EMPFAENGER_TYP` = 'Partner' AND `EMPFAENGER_ID` = '$p_id' AND `AKTUELL` = '1' AND `STATUS_BESTAETIGT` = '0' && DATE_FORMAT(RECHNUNGSDATUM, '%Y') = '$jahr' ORDER BY `RECHNUNGEN`.`NETTO`  DESC";
            $result = DB::select($abfrage);
            if (!empty($result)) {
                $sum = 0;
                echo "<table class=\"sortable\">";
                echo "<tr><th>DATUM</th><th>RNR</th><th>TYP</th><th>AUSSTELLER</th><th>KURZINFO</th></th><th>BETRAG</th></tr>";
                foreach ($result as $row) {
                    $sum += $row ['NETTO'] . $rn = $row ['RECHNUNGSNUMMER'];
                    $netto = $row ['NETTO'];
                    $beleg_nr = $row ['BELEG_NR'];
                    $rrr = new rechnung ();
                    $rrr->rechnung_grunddaten_holen($beleg_nr);
                    $netto_a = nummer_punkt2komma_t($netto);
                    echo "<tr><td>$rrr->rechnungsdatum</td><td>$rn</td><td>$rrr->rechnungstyp</td><td>$rrr->rechnungs_aussteller_name</td><td>$rrr->kurzbeschreibung</td><td align=\"right\">$netto_a</td></tr>";
                }
                $sum_a = nummer_punkt2komma_t($sum);

                echo "<tfoot><tr><td>SUMME</td><td></td><td></td><td></td><td></td><td>$sum_a</td></tr></tfoot>";
                echo "</table>";
            } else {
                echo "Keine Verbindlichkeiten";
            }
        }
    }

    /* Nicht bezahlte Rechnungen von uns */

    function forderungen($jahr)
    {
        if (!session()->has('partner_id')) {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\InfoMessage('Bitte wählen Sie einen Partner.'),
                0,
                null,
                route('web::rechnungen::legacy', ['option' => 'partner_wechseln'])
            );
        } else {
            $p_id = session()->get('partner_id');
            $abfrage = "SELECT * FROM `RECHNUNGEN` WHERE `RECHNUNGSTYP` IN ('Rechnung') AND `AUSSTELLER_TYP` = 'Partner' AND `AUSSTELLER_ID` = '$p_id' AND `AKTUELL` = '1' AND `STATUS_BEZAHLT` = '0' && DATE_FORMAT(RECHNUNGSDATUM, '%Y') = '$jahr' ORDER BY `RECHNUNGEN`.`NETTO`  DESC";
            $result = DB::select($abfrage);
            if (!empty($result)) {
                $sum = 0;
                echo "<table class=\"sortable\">";
                echo "<tr><th>DATUM</th><th>RNR</th><th>TYP</th><th>EMPFÄNGER</th><th>KURZINFO</th></th><th>BETRAG</th></tr>";
                foreach ($result as $row) {
                    $sum += $row ['NETTO'] . $rn = $row ['RECHNUNGSNUMMER'];
                    $netto = $row ['NETTO'];
                    $beleg_nr = $row ['BELEG_NR'];
                    $rrr = new rechnung ();
                    $rrr->rechnung_grunddaten_holen($beleg_nr);
                    $netto_a = nummer_punkt2komma_t($netto);
                    echo "<tr><td>$rrr->rechnungsdatum</td><td>$rn</td><td>$rrr->rechnungstyp</td><td>$rrr->rechnungs_empfaenger_name</td><td>$rrr->kurzbeschreibung</td><td align=\"right\">$netto_a</td></tr>";
                }
                $sum_a = nummer_punkt2komma_t($sum);
                echo "<tfoot><tr><td>SUMME</td><td></td><td></td><td></td><td></td><td>$sum_a</td></tr></tfoot>";
                echo "</table>";
            } else {
                echo "Keine Verbindlichkeiten";
            }
        }
    }

    /* Rechnungsgrunddaten holen */

    function artikel_pro_kos_anzeigen($partner_id, $kos_typ, $kos_id, $kostenkonto)
    {
        $artikel_arr = $this->suche_artikel_nach_kos_arr($partner_id, $kos_typ, $kos_id, $kostenkonto);
        $anz = count($artikel_arr);
        if ($anz > 0) {
            echo "<table class=\"sortable\">";
            $r1 = new rechnung ();
            $kos_bez = $r1->kostentraeger_ermitteln($kos_typ, $kos_id);
            echo "<tr><th>G_PREIS</th><th>EP</th><th>MENGE</th><th>Brutto skontiert</th><th>Kurzbeschreibung $kos_bez $kostenkonto</th><th>RECHNUNG</th></tr>";
            $s = 0;
            $s_brutto = 0;
            for ($a = 0; $a < $anz; $a++) {
                $einzel_preis = $artikel_arr [$a] ['EINZEL_PREIS'];
                $menge = $artikel_arr [$a] ['MENGE'];
                $g_summe_pos = $artikel_arr [$a] ['GESAMT_SUMME'];
                $mwst = $artikel_arr [$a] ['MWST_SATZ'];
                $kurz_b = $artikel_arr [$a] ['KURZBESCHREIBUNG'];
                $r_nr = $artikel_arr [$a] ['RECHNUNGSNUMMER'];
                $skonto_proz = $artikel_arr [$a] ['SKONTO'];
                $rabatt = $artikel_arr [$a] ['RABATT_SATZ'];
                $pos_brutto = ((($g_summe_pos / 100) * $rabatt) / 100 * $mwst + $g_summe_pos) / 100 * (100 - $skonto_proz);
                echo "<tr><td>$g_summe_pos</td><td>$einzel_preis</td><td>$menge</td><td>$pos_brutto</td><td>$kurz_b</td><td>$r_nr</td></tr>";
                $s += $g_summe_pos;
                $s_brutto += $pos_brutto;
            }
            echo "<tr><td><b>$s</td><td><b>NETTO</td><td><b>BRUTTO</td><td><b>$s_brutto</td><td><b>$kos_bez</td><td></td></tr>";
            echo "</table>";
        }
    }

    function suche_artikel_nach_kos_arr($partner_id, $kos_typ, $kos_id, $kostenkonto)
    {
        $result = DB::select("SELECT * FROM `KONTIERUNG_POSITIONEN` JOIN RECHNUNGEN ON (KONTIERUNG_POSITIONEN.BELEG_NR=RECHNUNGEN.BELEG_NR) WHERE `KOSTENTRAEGER_TYP` = '$kos_typ' AND `KOSTENTRAEGER_ID` = '$kos_id' AND KONTIERUNG_POSITIONEN.AKTUELL = '1' AND RECHNUNGEN.AKTUELL = '1' AND RECHNUNGEN.EMPFAENGER_TYP='Partner' && RECHNUNGEN.EMPFAENGER_ID='$partner_id' && KONTENRAHMEN_KONTO='$kostenkonto'");
        if (!empty($result)) {
            return $result;
        } else {
            echo "keine daten";
        }
    }

    function form_rechnungsgrunddaten_aendern($belegnr)
    {
        $this->rechnung_grunddaten_holen($belegnr);
        $f = new formular ();
        $f->erstelle_formular("Rechnungsgrunddaten ändern", NULL);
        $f->hidden_feld("rechnung_dat", $this->rechnung_dat);
        $f->hidden_feld("belegnr", $belegnr);
        $this->drop_rechnungs_typen('Rechnungstyp', 'rechnungstyp', 'rechnungstyp', '', $this->rechnungstyp);
        $f->text_feld('Rechnungsnummer', 'rechnungsnummer', $this->rechnungsnummer, '15', 'rechnungsnummer', '');
        $datum_feld = 'document.getElementById("rechnungsdatum").value';
        $js_datum = "onchange='check_datum($datum_feld)'";
        $f->text_feld('Rechnungsdatum', 'rechnungsdatum', $this->rechnungsdatum, '15', 'rechnungsdatum', $js_datum);

        $datum_feld = 'document.getElementById("eingangsdatum").value';
        $js_datum = "onchange='check_datum($datum_feld)'";
        $f->text_feld('Eingangsdatum', 'eingangsdatum', $this->eingangsdatum, '15', 'eingangsdatum', $js_datum);

        $datum_feld = 'document.getElementById("faellig_am").value';
        $js_datum = "onchange='check_datum($datum_feld)'";
        $f->text_feld('Fällig am', 'faellig_am', $this->faellig_am, '15', 'faellig_am', $js_datum);

        $netto = nummer_punkt2komma($this->rechnungs_netto);
        $brutto = nummer_punkt2komma($this->rechnungs_brutto);
        // $f->text_feld('Rechnungstyp', 'rechnungstyp', $this->rechnungstyp, '15', 'rechnungstyp', '');
        $f->text_feld('Netto', 'netto', $netto, '15', 'netto', '');
        $f->text_feld('Brutto', 'brutto', $brutto, '15', 'brutto', '');
        $this->rechnungs_skontobetrag = nummer_punkt2komma($this->rechnungs_skontobetrag);
        $f->text_feld('Skontobetrag', 'skontobetrag', $this->rechnungs_skontobetrag, '15', 'skontobetrag', '');
        $f->text_feld('Aussteller Ausgangsnr', 'a_ausnr', $this->aussteller_ausgangs_rnr, '15', 'ausgangsnr', '');
        $f->text_feld('Empfänger Eingangsnr', 'e_einnr', $this->empfaenger_eingangs_rnr, '15', 'eingangsnr', '');

        $this->kb = str_replace("<br>", "\n", $this->kurzbeschreibung);
        $f->text_bereich('Kurzbeschreibung', 'kurzbeschreibung', $this->kb, 30, 30, 'kurzbeschreibung');
        $f->hidden_feld("option", "rechnung_gd_gesendet");
        $f->send_button("submit_r_gd", "Änderung speichern");

        // $f->hidden_feld("a_ausnr", $this->aussteller_ausgangs_rnr);
        // $f->hidden_feld("e_einnr", $this->empfaenger_eingangs_rnr);
        $f->hidden_feld("aus_typ", $this->rechnungs_aussteller_typ);
        $f->hidden_feld("aus_id", $this->rechnungs_aussteller_id);
        $f->hidden_feld("ein_typ", $this->rechnungs_empfaenger_typ);
        $f->hidden_feld("ein_id", $this->rechnungs_empfaenger_id);

        $f->hidden_feld("status_erfasst", $this->status_erfasst);
        $f->hidden_feld("status_voll", $this->status_vollstaendig);
        $f->hidden_feld("status_zugew", $this->status_zugewiesen);
        $f->hidden_feld("status_bezahlt", $this->status_bezahlt);
        $f->hidden_feld("status_z_frei", $this->status_zahlung_freigegeben);
        $f->hidden_feld("status_bestaetigt", $this->status_bestaetigt);
        $f->hidden_feld("bezahlt_am", $this->bezahlt_am);
        $f->hidden_feld("empfangs_geldkonto", $this->empfangs_geld_konto);

        $f->ende_formular();
    }

    function rechnung_grunddaten_holen($belegnr)
    {
        $result = DB::select("SELECT * FROM RECHNUNGEN WHERE BELEG_NR='$belegnr' && AKTUELL='1' ORDER BY BELEG_NR DESC LIMIT 0,1");
        if (!empty($result)) {
            /* Skontogesamtbetrag updaten */
            $this->update_skontobetrag($belegnr);
            $this->update_nettobetrag($belegnr);
            $this->update_bruttobetrag($belegnr);
            $result = DB::select("SELECT * FROM RECHNUNGEN WHERE BELEG_NR='$belegnr' && AKTUELL='1' ORDER BY BELEG_NR DESC LIMIT 0,1");
            $row = $result[0];

            $this->belegnr = $row ['BELEG_NR'];
            $this->rechnung_dat = $row ['RECHNUNG_DAT'];
            $this->aussteller_ausgangs_rnr = $row ['AUSTELLER_AUSGANGS_RNR'];
            $this->empfaenger_eingangs_rnr = $row ['EMPFAENGER_EINGANGS_RNR'];
            $this->rechnungstyp = $row ['RECHNUNGSTYP'];
            $this->rechnungsdatum = date_mysql2german($row ['RECHNUNGSDATUM']);
            $this->eingangsdatum = date_mysql2german($row ['EINGANGSDATUM']);
            $this->faellig_am = date_mysql2german($row ['FAELLIG_AM']);
            $this->rechnungsnummer = ltrim(rtrim($row ['RECHNUNGSNUMMER']));
            $this->rechnungs_netto = $row ['NETTO'];
            $this->rechnungs_brutto = $row ['BRUTTO'];
            $this->rechnungs_mwst = $this->rechnungs_brutto - $this->rechnungs_netto;
            $this->rechnungs_skontobetrag = $row ['SKONTOBETRAG'];
            $this->rechnungs_brutto = $this->rechnungs_netto + $this->rechnungs_mwst;

            $this->rechnungs_skontoabzug = $this->rechnungs_brutto - $this->rechnungs_skontobetrag;

            $this->rechnungs_aussteller_typ = $row ['AUSSTELLER_TYP'];
            $this->rechnungs_aussteller_id = $row ['AUSSTELLER_ID'];
            $this->rechnungs_empfaenger_typ = $row ['EMPFAENGER_TYP'];
            $this->rechnungs_empfaenger_id = $row ['EMPFAENGER_ID'];

            /* Rechnungspartner finden und Rechnungstyp ändern falls Aussteller = Empfänger */
            $this->rechnungs_partner_ermitteln();

            $this->rechnungs_kuerzel = $this->rechnungs_kuerzel_ermitteln($this->rechnungs_aussteller_typ, $this->rechnungs_aussteller_id, $row ['RECHNUNGSDATUM']);
            $this->rechnungsnummer_kuerzel = $this->rechnungs_kuerzel . $this->aussteller_ausgangs_rnr;

            $this->status_erfasst = $row ['STATUS_ERFASST'];
            $this->status_vollstaendig = $row ['STATUS_VOLLSTAENDIG'];
            $this->status_zugewiesen = $row ['STATUS_ZUGEWIESEN'];
            $this->kurzbeschreibung = $row ['KURZBESCHREIBUNG'];
            $this->status_bezahlt = $row ['STATUS_BEZAHLT'];
            $this->status_zahlung_freigegeben = $row ['STATUS_ZAHLUNG_FREIGEGEBEN'];
            $this->status_bestaetigt = $row ['STATUS_BESTAETIGT'];
            $this->bezahlt_am = date_mysql2german($row ['BEZAHLT_AM']);
            $this->empfangs_geld_konto = $row ['EMPFANGS_GELD_KONTO'];

            /* Infos über Positionen */
            $this->rechnung_auf_positionen_pruefen($belegnr);
        } // end if rows>1
    }

    function update_skontobetrag($beleg_nr)
    {
        $betrag = nummer_komma2punkt(nummer_punkt2komma($this->summe_skonto_positionen($beleg_nr)));
        DB::update("UPDATE RECHNUNGEN SET SKONTOBETRAG='$betrag' WHERE BELEG_NR='$beleg_nr' && AKTUELL='1'");
    }

    function summe_skonto_positionen($beleg_nr)
    {
        $result = DB::select("SELECT SUM(GESAMT_NETTO  * ( (100 + MWST_SATZ) /100 ) * ((100-SKONTO)/100 ) )  AS SKONTO_BETRAG  FROM RECHNUNGEN_POSITIONEN WHERE `BELEG_NR`='$beleg_nr' && AKTUELL='1'");
        $row = $result[0];
        return $row ['SKONTO_BETRAG'];
    }

    function update_nettobetrag($beleg_nr)
    {
        $betrag = nummer_komma2punkt(nummer_punkt2komma($this->summe_netto_positionen($beleg_nr)));
        DB::update("UPDATE RECHNUNGEN SET NETTO='$betrag' WHERE BELEG_NR='$beleg_nr' && AKTUELL='1'");
    }

    function summe_netto_positionen($beleg_nr)
    {
        $result = DB::select("SELECT SUM(GESAMT_NETTO) AS NETTO_BETRAG FROM RECHNUNGEN_POSITIONEN WHERE `BELEG_NR`='$beleg_nr' && AKTUELL='1'");
        return $result[0]['NETTO_BETRAG'];
    }

    function update_bruttobetrag($beleg_nr)
    {
        $betrag = nummer_komma2punkt(nummer_punkt2komma($this->summe_brutto_positionen($beleg_nr)));
        DB::update("UPDATE RECHNUNGEN SET BRUTTO='$betrag' WHERE BELEG_NR='$beleg_nr' && AKTUELL='1'");
    }

    function summe_brutto_positionen($beleg_nr)
    {
        $result = DB::select("SELECT SUM(GESAMT_NETTO * ( ( 100 + MWST_SATZ ) /100 ))  AS BRUTTO  FROM RECHNUNGEN_POSITIONEN WHERE `BELEG_NR`='$beleg_nr' && AKTUELL='1'");
        $row = $result[0];
        return $row ['BRUTTO'];
    }

    function rechnungs_partner_ermitteln()
    {
        if ($this->rechnungs_aussteller_typ == 'Partner') {
            /* Partnernamen holen */
            $this->rechnungs_aussteller_name = $this->get_partner_name($this->rechnungs_aussteller_id);
            /* Anschriften holen */
            $this->get_aussteller_info($this->rechnungs_aussteller_id);
            $this->rechnung_aussteller_partner_id = $this->rechnungs_aussteller_id;
        }

        if ($this->rechnungs_empfaenger_typ == 'Partner') {
            $this->rechnungs_empfaenger_name = $this->get_partner_name($this->rechnungs_empfaenger_id);
            /* Anschriften holen */
            $this->get_empfaenger_info($this->rechnungs_empfaenger_id);
            /* Ende Partnernamen holen */
            $this->rechnung_empfaenger_partner_id = $this->rechnungs_empfaenger_id;
        }

        if ($this->rechnungs_empfaenger_typ == 'Eigentuemer') {
            $weg = new weg ();
            $weg->get_eigentumer_id_infos3($this->rechnungs_empfaenger_id);
            $this->rechnungs_empfaenger_name = $weg->post_anschrift;
            /* Anschriften holen */
            // $this->get_empfaenger_info($this->rechnungs_empfaenger_id);
            /* Ende Partnernamen holen */
            // $this->rechnung_empfaenger_partner_id = $this->rechnungs_empfaenger_id;
        }

        if ($this->rechnungs_aussteller_typ == 'Kasse') {
            /* Kassennamen holen */
            $kassen_info = new kasse ();
            $kassen_info->get_kassen_info($this->rechnungs_aussteller_id);
            $this->rechnungs_aussteller_name = "" . $kassen_info->kassen_name . "<br><br>" . $kassen_info->kassen_verwalter . "";
            /* Kassen Partner finden */
            $this->rechnung_aussteller_partner_id = $kassen_info->kassen_partner_id;
        }

        if ($this->rechnungs_empfaenger_typ == 'Kasse') {
            /* Kassennamen holen */
            $kassen_info = new kasse ();
            $kassen_info->get_kassen_info($this->rechnungs_empfaenger_id);
            $this->rechnungs_empfaenger_name = "" . $kassen_info->kassen_name . "<br><br>" . $kassen_info->kassen_verwalter . "";
            /* Kassen Partner finden */
            $this->rechnung_empfaenger_partner_id = $kassen_info->kassen_partner_id;
        }

        if ($this->rechnungs_aussteller_typ == 'Lager') {
            $lager_info = new lager ();
            // $this->rechnungs_aussteller_name = $lager_info->lager_bezeichnung($this->rechnungs_aussteller_id);
            /*
			 * Liefert Lagernamen und Partner id
			 * $lager_info->lager_name
			 * $lager_info->lager_partner_id
			 */
            $lager_info->lager_name_partner($this->rechnungs_aussteller_id);
            /* Partnernamen holen */
            $this->rechnungs_aussteller_name = 'Lager ' . $this->get_partner_name($lager_info->lager_partner_id);
            /* Anschriften holen */
            $this->get_aussteller_info($lager_info->lager_partner_id);
            $this->rechnung_aussteller_partner_id = $lager_info->lager_partner_id;
        }

        if ($this->rechnungs_empfaenger_typ == 'Lager') {
            $lager_info1 = new lager ();
            // $this->rechnungs_empfaenger_name = $lager_info->lager_bezeichnung($this->rechnungs_empfaenger_id); //alt
            /*
			 * Liefert Lagernamen und Partner id
			 * $lager_info->lager_name
			 * $lager_info->lager_partner_id
			 */
            $lager_info1->lager_name_partner($this->rechnungs_empfaenger_id);
            /* Partnernamen finden */
            $this->rechnungs_empfaenger_name = 'Lager ' . $this->get_partner_name($lager_info1->lager_partner_id);
            /* Anschriften holen */
            $this->get_empfaenger_info($lager_info1->lager_partner_id);

            $this->rechnung_empfaenger_partner_id = $lager_info1->lager_partner_id;
        }

        if (isset ($this->rechnung_empfaenger_partner_id) && ($this->rechnung_empfaenger_partner_id === $this->rechnung_aussteller_partner_id)) {
            $this->rechnungs_typ_druck = 'BUCHUNGSBELEG';
        } else {
            // $this->rechnungs_typ_druck = 'RECHNUNG';
            $this->rechnungs_typ_druck = $this->rechnungstyp;
        }
    }

    function get_partner_name($partner_id)
    {
        $result = DB::select("SELECT PARTNER_NAME FROM PARTNER_LIEFERANT WHERE PARTNER_ID='$partner_id' && AKTUELL = '1'");
        $row = $result[0];
        return $row ['PARTNER_NAME'];
    }

    function get_aussteller_info($partner_id)
    {
        $result = DB::select("SELECT PARTNER_NAME, STRASSE, NUMMER, PLZ, ORT FROM PARTNER_LIEFERANT WHERE PARTNER_ID='$partner_id' && AKTUELL = '1'");
        $row = $result[0];
        $this->rechnungs_aussteller_name = $row ['PARTNER_NAME'];
        $this->rechnungs_aussteller_strasse = $row ['STRASSE'];
        $this->rechnungs_aussteller_hausnr = $row ['NUMMER'];
        $this->rechnungs_aussteller_plz = $row ['PLZ'];
        $this->rechnungs_aussteller_ort = $row ['ORT'];
    }

    function get_empfaenger_info($partner_id)
    {
        $result = DB::select("SELECT PARTNER_NAME, STRASSE, NUMMER, PLZ, ORT FROM PARTNER_LIEFERANT WHERE PARTNER_ID='$partner_id' && AKTUELL = '1'");
        $row = $result[0];
        $this->rechnungs_empfaenger_name = $row ['PARTNER_NAME'];
        $this->rechnungs_empfaenger_strasse = $row ['STRASSE'];
        $this->rechnungs_empfaenger_hausnr = $row ['NUMMER'];
        $this->rechnungs_empfaenger_plz = $row ['PLZ'];
        $this->rechnungs_empfaenger_ort = $row ['ORT'];
    }

    function rechnungs_kuerzel_ermitteln($austeller_typ, $aussteller_id, $datum)
    {
        $result = DB::select("SELECT KUERZEL FROM RECHNUNG_KUERZEL WHERE AKTUELL = '1' && AUSSTELLER_TYP = '$austeller_typ' && AUSSTELLER_ID = '$aussteller_id' && ( VON <= '$datum' OR BIS >= '$datum' ) ORDER BY RK_DAT DESC LIMIT 0,1");
        $row = $result[0];
        return $row ['KUERZEL'];
    }

    function rechnung_auf_positionen_pruefen($belegnr)
    {
        $result = DB::select("SELECT * FROM RECHNUNGEN_POSITIONEN WHERE BELEG_NR='$belegnr' && AKTUELL='1'");
        $numrows = count($result);
        $this->anzahl_positionen_aktuell = $numrows;
    }

    function drop_rechnungs_typen($beschreibung, $name, $id, $js, $selected_value)
    {
        $enum_arr = $this->enum_typen_arr('RECHNUNGEN', 'RECHNUNGSTYP');
        if (!empty($enum_arr)) {
            $enum_arr = msort($enum_arr);
            echo "<div class='input-field'>";
            echo "<select name=\"$name\" id=\"$id\" $js>\n";
            $anzahl_enums = count($enum_arr);
            for ($a = 0; $a < $anzahl_enums; $a++) {
                $enum_value = $enum_arr [$a];
                if ($enum_value == $selected_value) {
                    echo "<option value=\"$enum_value\" selected>$enum_value</option>\n";
                } else {
                    echo "<option value=\"$enum_value\">$enum_value</option>\n";
                }
            }
            echo "</select>\n";
            echo "<label for=\"$id\">$beschreibung</label>\n";
            echo "<div>";
        } else {
            echo "Fehler beim Lesen aus der DB";
        }
    }

    function enum_typen_arr($tabelle, $spalte)
    {
        //TODO: check
        $my_arr = DB::select("SHOW COLUMNS FROM $tabelle LIKE '$spalte'");
        $type = $my_arr [0] ['Type'];
        $type = substr($type, 5);
        $type = substr($type, 0, -1);
        $type = str_replace("'", '', $type);
        $type_arr = explode(",", $type);
        return $type_arr;
    }

    function form_positionen_erfassen($belegnr)
    {
        $this->rechnung_grunddaten_holen($belegnr);
        $rb = new rechnung ();
        $rb->rechnungs_kopf($belegnr);
        $beleg_feld = "document.getElementById('belegnr').value";
        echo "<div id=\"positionen\" >";
        echo "<script type=\"text/javascript\">display_positionen($belegnr)</script>\n";
        /* Rechnungsfooter d.h. Netto Brutto usw. */
        echo "</div>";
        $rb->rechnung_footer_tabelle_anzeigen_pe();

        $f = new formular ();
        $f->erstelle_formular("Artikelsuche  $this->rechnungs_aussteller_name", NULL);
        echo "Lieferant: $this->rechnungs_aussteller_name<br>";
        $f->text_feld('Artikelnr/Leistungnr', 'suche_artikelnr', '', '15', 'suche_artikelnr', '');
        $art_feld = "document.getElementById('suche_artikelnr').value";
        $js_check_art = "onclick=\"ajax_check_art($this->rechnungs_aussteller_id, $art_feld)\";";
        $f->button_js('suchen_btn', 'Suchen', $js_check_art);
        $js_neu_berechnen = "onKeyUp=\"refresh_preise()\" onmouseover=\"refresh_preise()\" ";
        $js_listenpreis_berechnen_von_enetto = "onKeyUp=\"listen_stueckpreis_rabatt()\" onclick=\"listen_stueckpreis_rabatt()\"";
        $f->erstelle_formular("Positionen erfassen für Rechnung $this->rechnungsnummer", NULL);

        echo "<table><tr>";
        echo "<td>";
        $f->hidden_feld('belegnr', $belegnr);
        $f->hidden_feld('lieferant_id', $this->rechnungs_aussteller_id);
        // $f->text_feld('Pos', 'pos', $pos, '3', 'pos', '');
        // echo "</td><td>";
        $f->text_feld('Artikelnr/Leistungnr', 'textf_artikelnr', '', '20', 'textf_artikelnr', '');
        echo "</td><td>";
        $f->text_feld('Menge', 'menge', '', '10', 'menge', $js_neu_berechnen);
        echo "</td><td>";
        $this->dropdown_v_einheiten('Mengenangabe', 'einheit', 'einheit');
        echo "</td><td>";
        $f->text_feld('Bezeichnung', 'bezeichnung', '', '60', 'bezeichnung', '');
        echo "</td><td></tr><tr><td>";
        $f->text_feld('Listenpreis', 'lp', '', '10', 'lp', $js_neu_berechnen);
        echo "</td><td>";
        $f->text_feld('MWSt %', 'mwst_satz', '19', '10', 'mwst_satz', $js_neu_berechnen);
        echo "</td><td>";
        $f->text_feld('Rabattsatz', 'rabattsatz', '0.00', '10', 'rabattsatz', $js_neu_berechnen);
        $f->text_feld('Position Skonto', 'pos_skonto', '', '10', 'pos_skonto', $js_neu_berechnen);
        echo "</td><td>";
        $f->text_feld('Nettopreis', 'nettopreis', '', '10', 'nettopreis', $js_listenpreis_berechnen_von_enetto);
        // echo "</td><td>";
        $f->text_feld('Bruttopreis', 'bruttopreis', '', '10', 'bruttopreis', '');
        echo "</td></tr><tr>";
        echo "<td>";
        $f->text_feld('Gesamtnetto', 'netto_gesamt', '', '20', 'netto_gesamt', '');
        $f->text_feld_inaktiv('Gesamtbrutto', 'brutto_gesamt', '', '20', 'brutto_gesamt', '');
        // $js_btn = "onClick=\"schreibe_pos_in_div()\"" ;

        // $js_btn = "onClick=\"display_positionen($beleg_feld)\"" ;
        $js_save = "onClick=\"position_speichern()\"";
        $f->button_js('speichern_btn', 'Speichern', $js_save);
        // $f->button_js('pos_zeigen_btn', 'Positionen anzeigen', $js_btn);
        echo "</td></tr></table>";
        $f->ende_formular();
        $f->ende_formular();
    }

    function dropdown_v_einheiten($beschreibung, $name, $id)
    {
        $result = DB::select("SELECT V_EINHEIT, BEZEICHNUNG  FROM VERPACKUNGS_E WHERE AKTUELL='1' ORDER BY BEZEICHNUNG ASC");
        /* Wenn urpsrungsrechnungen vorhanden, ins array hinzufügen */
        echo "<label for=\"$id\">$beschreibung</label>\n";
        echo "<select name=\"$name\" id=\"$id\">\n";
        if (!empty($result)) {
            foreach ($result as $row) {
                $einheit = $row ['V_EINHEIT'];
                $bezeichnung = $row ['BEZEICHNUNG'];
                echo "<option value=\"$einheit\">$bezeichnung</option>\n";
            }
        } else {
            echo "<option value=\"Ohne\">Einheiten in Datenbank speichern</option>\n";
        }
        echo "</select>\n";
    }

    function form_positionen_erfassen2($belegnr)
    {
        $this->rechnung_grunddaten_holen($belegnr);
        $rb = new rechnung ();
        $rb->rechnungs_kopf($belegnr);
        $beleg_feld = "document.getElementById('belegnr').value";
        $fo = new formular ();

        echo "</p>";
        echo "<table>";
        echo "<tr><td>";
        $fo->text_feld('Alle Rabatte ändern', 'rabbatt_aendern', '', 10, 'r_b', '');
        $rab_value = "document.getElementById('r_b').value";
        $js = "onclick=\"update_rechnung_rabatt($belegnr, $rab_value)\"";
        $fo->button_js('btn_rb', 'Alle Rabatte ändern', $js);
        echo "</td><td>";
        $fo->text_feld('Alle Skonti ändern', 'skonti_aendern', '', 10, 's_k', '');
        $skonti_value = "document.getElementById('s_k').value";
        $js1 = "onclick=\"update_rechnung_skonti($belegnr, $skonti_value)\"";

        $fo->button_js('btn_sk', 'Alle Skonti ändern', $js1);
        echo "</td></tr>";
        echo "</table>";

        echo "<div id=\"positionen\" >";
        echo "<script type=\"text/javascript\">display_positionen($belegnr)</script>\n";
        /* Rechnungsfooter d.h. Netto Brutto usw. */
        echo "</div>";
        $rb->rechnung_footer_tabelle_anzeigen_pe();

        $f = new formular ();
        $f->erstelle_formular("Artikelsuche mit Autovervollständigen $this->rechnungs_aussteller_name", NULL);
        // echo "Rechnung $this->rechnungsnummer hat $this->anzahl_positionen_aktuell Positionen<br>";
        echo "Lieferant: $this->rechnungs_aussteller_name<br>";

        // $f->text_feld($beschreibung, $name, $wert, $size, $id, $js_action);
        $js_autovv = "onkeyup=\"autovervoll_with_delay($this->rechnungs_aussteller_id, this.value);\"";
        echo "<div>";
        $f->text_feld('Artikelnr/Leistungnr', 'suche_artikelnr', '', '25', 'suche_artikelnr', $js_autovv);

        $art_feld = "document.getElementById('suche_artikelnr').value";
        $js_check_art = "onclick=\"ajax_check_art($this->rechnungs_aussteller_id, $art_feld)\";";
        $f->button_js('suchen_btn', 'Suchen', $js_check_art);

        /* Fals Artikel gefunden hier rein - Autovervollständigen */
        echo "</div>";
        echo "<div id=\"artikel_vorhanden\" class=\"artikel_vorhanden\"></div>";

        $js_neu_berechnen = "onKeyUp=\"refresh_preise()\" onmouseover=\"refresh_preise()\" ";
        $js_listenpreis_berechnen_von_enetto = "onKeyUp=\"listen_stueckpreis_rabatt()\" onclick=\"listen_stueckpreis_rabatt()\"";

        $f->erstelle_formular("Positionen erfassen für Rechnung $this->rechnungsnummer", NULL);
        echo "<table><tr>";
        echo "<td>";
        $f->hidden_feld('belegnr', $belegnr);
        $f->hidden_feld('lieferant_id', $this->rechnungs_aussteller_id);
        $f->text_feld('Artikelnr/Leistungnr', 'textf_artikelnr', '', '20', 'textf_artikelnr', '');
        echo "</td><td>";
        $f->text_feld('Menge', 'menge', '', '10', 'menge', $js_neu_berechnen);
        echo "</td><td>";
        $this->dropdown_v_einheiten('Mengenangabe', 'einheit', 'einheit');
        echo "</td><td>";
        $f->text_feld('Bezeichnung', 'bezeichnung', '', '60', 'bezeichnung', '');
        echo "</td><td></tr><tr><td>";
        $f->text_feld('Listenpreis', 'lp', '', '10', 'lp', $js_neu_berechnen);
        echo "</td><td>";
        $f->text_feld('MWSt %', 'mwst_satz', '19', '10', 'mwst_satz', $js_neu_berechnen);
        $f->text_feld('Skonto', 'pos_skonto', '0.00', '10', 'pos_skonto', $js_neu_berechnen);
        echo "</td><td>";
        $f->text_feld('Rabattsatz', 'rabattsatz', '0.00', '10', 'rabattsatz', $js_neu_berechnen);
        echo "</td><td>";
        $f->text_feld('Nettopreis', 'nettopreis', '', '10', 'nettopreis', $js_listenpreis_berechnen_von_enetto);
        // echo "</td><td>";
        $f->text_feld('Bruttopreis', 'bruttopreis', '', '10', 'bruttopreis', '');
        echo "</td></tr><tr>";
        echo "<td>";
        $f->text_feld('Gesamtnetto', 'netto_gesamt', '', '20', 'netto_gesamt', '');
        $f->text_feld_inaktiv('Gesamtbrutto', 'brutto_gesamt', '', '20', 'brutto_gesamt', '');
        // $js_btn = "onClick=\"schreibe_pos_in_div()\"" ;

        // $js_btn = "onClick=\"display_positionen($beleg_feld)\"" ;
        $js_save = "onClick=\"position_speichern()\"";
        $f->button_js('speichern_btn', 'Speichern', $js_save);
        // $f->button_js('pos_zeigen_btn', 'Positionen anzeigen', $js_btn);
        echo "</td></tr></table>";
        $f->ende_formular();
        $f->ende_formular();
    }

    function form_positionen_aendern($pos, $belegnr)
    {
        $artikel_lieferant = $this->artikel_lieferant_finden($belegnr, $pos);
        $this->rechnung_grunddaten_holen($belegnr);
        $rb = new rechnung ();
        $rb->rechnungs_kopf($belegnr);
        $beleg_feld = "document.getElementById('belegnr').value";
        $js_display_pos = "onLoad=\"display_positionen($beleg_feld)\"";
        echo "<div id=\"positionen\" >";
        echo "<script type=\"text/javascript\">document.addEventListener(\"DOMContentLoaded\", function(event) { 
                display_positionen($belegnr);
        });</script>\n";
        /* Rechnungsfooter d.h. Netto Brutto usw. */
        echo "</div>";
        $rb->rechnung_footer_tabelle_anzeigen_pe();

        $f = new formular ();
        $f->erstelle_formular("Artikelsuche  $this->rechnungs_aussteller_name", NULL);
        // echo "Rechnung $this->rechnungsnummer hat $this->anzahl_positionen_aktuell Positionen<br>";
        echo "Rechnungsaussteller: $this->rechnungs_aussteller_name<br>";
        // $f->text_feld($beschreibung, $name, $wert, $size, $id, $js_action);
        $f->text_feld('Artikelnr/Leistungnr', 'suche_artikelnr', '', '15', 'suche_artikelnr', '');
        $art_feld = "document.getElementById('suche_artikelnr').value";
        // $js_check_art = "onclick='checkartikel($this->rechnungs_aussteller_id, $art_feld)'";
        $js_check_art = "onclick=\"ajax_check_art($artikel_lieferant, $art_feld);\"";
        $f->button_js('suchen_btn', 'Suchen', $js_check_art);
        $js_neu_berechnen = "onKeyUp=\"refresh_preise()\" onmouseover=\"refresh_preise();\"";
        $js_listenpreis_berechnen_von_enetto = "onKeyUp=\"listen_stueckpreis_rabatt()\" onclick=\"listen_stueckpreis_rabatt()\"";
        $f->erstelle_formular("Position $pos ändern in Rechnung $this->rechnungsnummer", NULL);

        echo "<table><tr>";
        echo "<td>";
        $f->hidden_feld('pos', $pos);
        $f->hidden_feld('belegnr', $belegnr);
        $f->hidden_feld('lieferant_id', $artikel_lieferant);
        $f->text_feld('Artikelnr/Leistungnr', 'textf_artikelnr', '', '20', 'textf_artikelnr', '');
        echo "</td><td>";
        $f->text_feld('Menge', 'menge', '', '10', 'menge', $js_neu_berechnen);
        echo "</td><td>";
        $this->dropdown_v_einheiten('Mengenangabe', 'einheit', 'einheit');
        echo "</td><td>";
        $f->text_feld('Bezeichnung', 'bezeichnung', '', '60', 'bezeichnung', '');
        echo "</td><td></tr><tr><td>";
        $f->text_feld('Listenpreis', 'lp', '', '10', 'lp', $js_neu_berechnen);
        echo "</td><td>";
        $f->text_feld('MWSt %', 'mwst_satz', '19', '10', 'mwst_satz', $js_neu_berechnen);
        $f->text_feld('Skonto', 'pos_skonto', '0.00', '10', 'pos_skonto', $js_neu_berechnen);
        echo "</td><td>";
        $f->text_feld('Rabattsatz', 'rabattsatz', '', '10', 'rabattsatz', $js_neu_berechnen);
        echo "</td><td>";
        $f->text_feld('Nettopreis', 'nettopreis', '', '10', 'nettopreis', $js_listenpreis_berechnen_von_enetto);
        // echo "</td><td>";
        $f->text_feld('Bruttopreis', 'bruttopreis', '', '10', 'bruttopreis', '');
        echo "</td></tr><tr>";
        echo "<td>";
        $f->text_feld('Gesamtnetto', 'netto_gesamt', '', '20', 'netto_gesamt', '');
        $f->text_feld_inaktiv('Gesamtbrutto', 'brutto_gesamt', '', '20', 'brutto_gesamt', '');
        // $js_btn = "onClick=\"schreibe_pos_in_div()\"" ;

        // $js_btn = "onClick=\"display_positionen($beleg_feld)\"" ;
        $js_save = "onClick=\"position_aendern()\"";
        $f->button_js('speichern_btn', 'Position ändern', "$js_save $js_display_pos");
        // $f->button_js('pos_zeigen_btn', 'Positionen anzeigen', $js_btn);
        echo "</td></tr></table>";
        $f->ende_formular();
        $f->ende_formular();
    }

    function artikel_lieferant_finden($belegnr, $pos)
    {
        $db_abfrage = "SELECT ART_LIEFERANT FROM RECHNUNGEN_POSITIONEN WHERE BELEG_NR='$belegnr' && POSITION='$pos' && AKTUELL='1' ORDER BY RECHNUNGEN_POS_DAT DESC LIMIT 0,1";
        $result = DB::select($db_abfrage);
        if (!empty($result)) {
            $row = $result[0];
            return $row ['ART_LIEFERANT'];
        }
    }

    function form_lieferschein_erfassen($beleg_nr)
    {
        if (request()->has('submit_lief')) {
            $lieferschein = request()->input('lieferschein');
            if (!empty ($lieferschein)) {
                echo "$lieferschein speichern";
                $d = new detail ();
                $d->detail_speichern_2('RECHNUNGEN', $beleg_nr, 'Lieferschein', $lieferschein, Auth::user()->email);
            } else {
                weiterleiten_in_sec(route('web::rechnungen.show', ['id' => $beleg_nr]), 2); // Positionseingabe
            }

            $weiter = request()->input('weiter');
            if ($weiter == 'nein') {
                weiterleiten_in_sec(route('web::rechnungen::legacy', ['option' => 'positionen_erfassen', 'belegnr' => $beleg_nr]), 2); // Positionseingabe
                hinweis_ausgeben("Sie werden zur Rechnung weitergeleitet");
            }
        }

        if (!$weiter or $weiter == 'ja') {
            $r = new rechnungen ();
            $r->rechnung_grunddaten_holen($beleg_nr);
            $f = new formular ();
            $f->erstelle_formular("Lieferscheine", NULL);
            $f->fieldset("Lieferschein zu Rechnung $r->rechnungsnummer hinzufügen", 'lieferschein');
            $link_pos_erf = "<a href='" . route('web::rechnungen.show', ['id' => $beleg_nr]) . "'>Weiter zur Positionerfassung</a>";
            echo $link_pos_erf;
            $f->hidden_feld('belegnr', $beleg_nr);
            $f->text_feld("Lieferschein Nr zu Rechnung $r->rechnungsnummer ", 'lieferschein', '', '20', 'lieferschein', '');
            $f->radio_button("weiter", "ja", 'Anschliessend weiteren Lieferschein hinzufügen');
            // radio_button_js($name, $wert, $label, $js, $checked
            $f->radio_button_js("weiter", "nein", 'Keinen weiteren Lieferschein hinzufügen', '', 'checked');
            $f->send_button_js("submit_lief", "Weiter", '');
            $f->ende_formular();
        }
    }

    function form_rechnung_buchen($belegnr)
    {
        $this->rechnung_als_freigegeben($belegnr); // automatisch freigeben

        $this->rechnung_grunddaten_holen($belegnr);
        $f = new formular ();
        // print_r($this);
        if ($this->status_bestaetigt == '1') {
            $f->fieldset("Rechnung $this->rechnungsnummer von $this->rechnungs_aussteller_name an $this->rechnungs_empfaenger_name ", 'xxx');
            echo "<h1>Rechnung $this->rechnungsnummer von $this->rechnungs_aussteller_name an $this->rechnungs_empfaenger_name wurde gebucht</h1>";
            $f->fieldset_ende();
        } else {

            if ($this->status_zahlung_freigegeben == '1') {

                $g = new geldkonto_info ();
                $b = new buchen ();
                $f->fieldset("Rechnung von $this->rechnungs_aussteller_name an $this->rechnungs_empfaenger_name, Rechnungsnr: $this->rechnungsnummer,  Erfassungsnummer: $this->belegnr", 'rech_buchen');

                if ($this->status_bezahlt == '0') {
                    $f->erstelle_formular("Zahlung der $this->rechnungstyp buchen", NULL);

                    if ($this->rechnungstyp == 'Rechnung' or $this->rechnungstyp == 'Buchungsbeleg') {
                        $g->dropdown_geldkonten_alle("$this->rechnungs_empfaenger_name -> Geldkonto auswählen", $this->rechnungs_empfaenger_typ, $this->rechnungs_empfaenger_id);
                    }
                    if ($this->rechnungstyp == 'Gutschrift') {
                        $g->dropdown_geldkonten_alle("$this->rechnungs_aussteller_name -> Geldkonto auswählen", $this->rechnungs_aussteller_typ, $this->rechnungs_aussteller_id);
                    }
                    $this->dropdown_buchungs_art('Buchungsart wählen', 'buchungsart', 'buchungsart', 'BLABLA');
                    if ($this->rechnungstyp == 'Rechnung' or $this->rechnungstyp == 'Buchungsbeleg') {
                        $b->dropdown_kostenrahmen_nr('kontenrahmen', $this->rechnungs_empfaenger_typ, $this->rechnungs_empfaenger_id);
                    }
                    if ($this->rechnungstyp == 'Gutschrift') {
                        $b->dropdown_kostenrahmen_nr('kontenrahmen', $this->rechnungs_aussteller_typ, $this->rechnungs_aussteller_id);
                    }

                    $f->hidden_feld("kostentraeger_typ", $this->rechnungs_empfaenger_typ);
                    $f->hidden_feld("kostentraeger_id", $this->rechnungs_empfaenger_id);

                    $f->hidden_feld("belegnr", "$belegnr");
                    $f->text_feld('Datum (dd.mm.jjjj)', 'datum', session()->get('temp_datum'), '10', 'datum', '');
                    $f->text_feld('Kontoauszugsnr', 'kontoauszugsnr', session()->get('temp_kontoauszugsnummer'), '10', 'kontoauszugsnr', '');
                    $this->kb = str_replace("<br>", "\n", $this->kurzbeschreibung);
                    $f->text_bereich('Buchungstext', 'vzweck', "Erfnr:$this->belegnr, WE:$this->empfaenger_eingangs_rnr Zahlungsausgang Rnr:$this->rechnungsnummer, $this->kb", 60, 60, 'v_zweck_buchungstext');
                    $pruefen = "onClick=\"felder_pruefen(this.form);return false;\"";
                    $f->send_button_js("submit_rbb", "Buchen", $pruefen);
                    $f->hidden_feld("option", "rechnung_buchen_gesendet");
                }  // ende status unbezahlt
                // ############################################################
                else {
                    $f->erstelle_formular("Empfang durch Kontoauszug bestätigen", NULL);

                    echo "$this->rechnungs_aussteller_typ, $this->rechnungs_aussteller_id  $this->rechnungs_empfaenger_typ, $this->rechnungs_empfaenger_id";

                    if ($this->rechnungstyp == 'Rechnung' or $this->rechnungstyp == 'Buchungsbeleg') {
                        $g->dropdown_geldkonten_alle("$this->rechnungs_aussteller_name -> Geldkonto auswählen", $this->rechnungs_aussteller_typ, $this->rechnungs_aussteller_id);
                    }

                    if ($this->rechnungstyp == 'Gutschrift') {
                        $g->dropdown_geldkonten_alle("$this->rechnungs_empfaenger_name -> Geldkonto auswählen", $this->rechnungs_empfaenger_typ, $this->rechnungs_empfaenger_id);
                    }

                    if ($this->rechnungstyp == 'Rechnung' or $this->rechnungstyp == 'Buchungsbeleg') {
                        $b->dropdown_kostenrahmen_nr('kontenrahmen', $this->rechnungs_aussteller_typ, $this->rechnungs_aussteller_id);
                    }

                    if ($this->rechnungstyp == 'Gutschrift') {
                        $b->dropdown_kostenrahmen_nr('kontenrahmen', $this->rechnungs_empfaenger_typ, $this->rechnungs_empfaenger_id);
                    }

                    $f->hidden_feld("kostentraeger_typ", $this->rechnungs_aussteller_typ);
                    $f->hidden_feld("kostentraeger_id", $this->rechnungs_aussteller_id);

                    $f->hidden_feld("belegnr", "$belegnr");
                    $f->text_feld('Datum (dd.mm.jjjj)', 'datum', session()->get('temp_datum'), '10', 'datum', '');
                    $f->text_feld('Kontoauszugsnr', 'kontoauszugsnr', session()->get('temp_kontoauszugsnummer'), '10', 'kontoauszugsnr', '');
                    $this->kb = str_replace("<br>", "\n", $this->kurzbeschreibung);
                    $f->text_bereich('Buchungstext', 'vzweck', "Erfnr:$this->belegnr, WA:$this->aussteller_ausgangs_rnr Zahlungseingang Rnr:$this->rechnungsnummer, $this->kb", 30, 30, 'v_zweck_buchungstext');
                    $pruefen = "onClick=\"felder_pruefen(this.form);return false;\"";
                    $f->send_button_js("submit_rbb", "Buchen", $pruefen);
                    $f->hidden_feld("option", "rechnung_buchen_gesendet");
                }

                $f->ende_formular();
                $f->fieldset_ende();
            }  // ende status freigegeben
            else {

                echo "NICHT ZUR ZAHLUNG FREIGEGEBEN";
            }
        } // ende verbucht
    }

    function dropdown_buchungs_art($beschreibung, $name, $id, $js_optionen)
    {
        echo "<div class='input-field'>";
        echo "<select name=\"$name\" id=\"$id\" size=\"2\" $js_optionen >\n";
        echo "<option value=\"Teilbetraege\">Wie kontiert buchen</option>\n";
        echo "<option value=\"Gesamtbetrag\" selected>Gesamtbetrag buchen</option>\n";
        echo "</select>\n";
        echo "<label for=\"$id\">$beschreibung</label>\n";
        echo "</div>";
    }

    function form_rechnung_zahlung_buchen($belegnr)
    {
        $this->rechnung_grunddaten_holen($belegnr);
        $f = new formular ();
        if ($this->status_bezahlt == '1') {
            $f->fieldset("Rechnung $this->rechnungsnummer von $this->rechnungs_aussteller_name an $this->rechnungs_empfaenger_name ", 'xxx');
            echo "<h1>Rechnung $this->rechnungsnummer von $this->rechnungs_aussteller_name an $this->rechnungs_empfaenger_name wurde gezahlt</h1>";
            $f->fieldset_ende();
        } else {
            if ($this->status_zahlung_freigegeben == '1') {
                $g = new geldkonto_info ();
                $b = new buchen ();
                $f->fieldset("Rechnung von $this->rechnungs_aussteller_name an $this->rechnungs_empfaenger_name, Rechnungsnr: $this->rechnungsnummer,  Erfassungsnummer: $this->belegnr", 'rech_buchen');

                if ($this->status_bezahlt == '0' && $this->status_vollstaendig == '1') {
                    $f->erstelle_formular("Zahlung der $this->rechnungstyp buchen", NULL);

                    if ($this->rechnungstyp == 'Rechnung' or $this->rechnungstyp == 'Buchungsbeleg') {
                        $g->dropdown_geldkonten_alle("$this->rechnungs_empfaenger_name -> Geldkonto auswählen", $this->rechnungs_empfaenger_typ, $this->rechnungs_empfaenger_id);
                    }
                    if ($this->rechnungstyp == 'Gutschrift') {
                        $g->dropdown_geldkonten_alle("$this->rechnungs_aussteller_name -> Geldkonto auswählen", $this->rechnungs_aussteller_typ, $this->rechnungs_aussteller_id);
                    }

                    $js_optionen = "onclick=\"buchungs_infos(document.getElementById('buchungsart').options[buchungsart.selectedIndex].value)\"";
                    $this->dropdown_buchungs_betrag('Buchungsbetrag wählen', 'buchungsbetrag', 'buchungsbetrag', $js_optionen);

                    $js_optionen = "onChange=\"buchungs_infos(this.value)\"";
                    $this->dropdown_buchungs_art('Buchungsart wählen', 'buchungsart', 'buchungsart', $js_optionen);

                    if ($this->rechnungstyp == 'Rechnung' or $this->rechnungstyp == 'Buchungsbeleg') {

                        $b->dropdown_kostenrahmen_nr('Kostenkonto', 'kostenkonto', $this->rechnungs_empfaenger_typ, $this->rechnungs_empfaenger_id, '7000');
                    }
                    if ($this->rechnungstyp == 'Gutschrift') {
                        $b->dropdown_kostenrahmen_nr('Kostenkonto', 'kostenkonto', $this->rechnungs_aussteller_typ, $this->rechnungs_aussteller_id, '');
                    }

                    $f->hidden_feld("kostentraeger_typ", $this->rechnungs_aussteller_typ);
                    $f->hidden_feld("kostentraeger_id", $this->rechnungs_aussteller_id);

                    $f->hidden_feld("belegnr", "$belegnr");
                    $f->text_feld('Datum (dd.mm.jjjj)', 'datum', session()->get('temp_datum'), '10', 'datum', '');
                    $f->text_feld('Kontoauszugsnr', 'kontoauszugsnr', session()->get('temp_kontoauszugsnummer'), '10', 'kontoauszugsnr', '');
                    $this->kb = str_replace("<br>", "\n", $this->kurzbeschreibung);
                    $f->text_bereich('Buchungstext', 'vzweck', "Erfnr:$this->belegnr, WE:$this->empfaenger_eingangs_rnr, Zahlungsausgang Rnr:$this->rechnungsnummer, $this->kb", 60, 60, 'v_zweck_buchungstext');
                    $pruefen = "onClick=\"felder_pruefen(this.form);return false;\"";
                    $f->send_button_js("submit_rbb", "Buchen", $pruefen);
                    $f->hidden_feld("option", "rechnung_buchen_gesendet");
                }  // ende status unbezahlt und vollständig
                else {
                    echo "NICHT VOLLSTÄNDIG ERFASST/KONTIERT!";
                }
                $f->ende_formular();
                $f->fieldset_ende();
            }  // ende status freigegeben
            else {
                echo "NICHT ZUR ZAHLUNG FREIGEGEBEN!";
            }
        } // ende verbucht
    }

    function dropdown_buchungs_betrag($beschreibung, $name, $id, $js_optionen)
    {
        echo "<div class='input-field'>";
        echo "<select name=\"$name\" id=\"$id\" size=\"3\" $js_optionen >\n";
        echo "<option value=\"Skontobetrag\" selected>Skontobetrag $this->rechnungs_skontobetrag buchen</option>\n";
        echo "<option value=\"Bruttobetrag\">Bruttobetrag $this->rechnungs_brutto buchen</option>\n";
        echo "<option value=\"Nettobetrag\">Nettobetrag $this->rechnungs_netto buchen</option>\n";
        echo "</select>\n";
        echo "<label for=\"$id\">$beschreibung</label>\n";
        echo "</div>";
    }

    function form_rechnung_empfang_buchen($belegnr)
    {
        $this->rechnung_grunddaten_holen($belegnr);
        $f = new formular ();
        if ($this->status_bestaetigt == '1') {
            $f->fieldset("Rechnung $this->rechnungsnummer von $this->rechnungs_aussteller_name an $this->rechnungs_empfaenger_name ", 'xxx');
            echo "<h3>Rechnung $this->rechnungsnummer von $this->rechnungs_aussteller_name an $this->rechnungs_empfaenger_name wurde schon gebucht</h3>";
            $f->fieldset_ende();
        } else {

            if ($this->status_zahlung_freigegeben == '1') {
                $g = new geldkonto_info ();
                $b = new buchen ();
                $f->fieldset("Rechnung von $this->rechnungs_aussteller_name an $this->rechnungs_empfaenger_name, Rechnungsnr: $this->rechnungsnummer,  Erfassungsnummer: $this->belegnr", 'rech_buchen');

                $f->erstelle_formular("Empfang durch Kontoauszug bestätigen", NULL);

                // echo "$this->rechnungs_aussteller_typ, $this->rechnungs_aussteller_id $this->rechnungs_empfaenger_typ, $this->rechnungs_empfaenger_id";

                if ($this->rechnungstyp == 'Rechnung' or $this->rechnungstyp == 'Buchungsbeleg') {
                    $g->dropdown_geldkonten_alle("$this->rechnungs_aussteller_name -> Geldkonto auswählen", $this->rechnungs_aussteller_typ, $this->rechnungs_aussteller_id);
                }

                if ($this->rechnungstyp == 'Gutschrift') {
                    $g->dropdown_geldkonten_alle("$this->rechnungs_empfaenger_name -> Geldkonto auswählen", $this->rechnungs_empfaenger_typ, $this->rechnungs_empfaenger_id);
                }

                $js_optionen = "onclick=\"buchungs_infos(document.getElementById('buchungsart').options[buchungsart.selectedIndex].value)\"";
                $this->dropdown_buchungs_betrag('Buchungsbetrag wählen', 'buchungsbetrag', 'buchungsbetrag', $js_optionen);

                $js_optionen = "onMouseover=\"buchungs_infos(this.value)\"";
                $this->dropdown_buchungs_art('Buchungsart wählen', 'buchungsart', 'buchungsart', $js_optionen);
                if ($this->rechnungstyp == 'Rechnung' or $this->rechnungstyp == 'Buchungsbeleg') {
                    $b->dropdown_kostenrahmen_nr('Kostenkonto', 'kostenkonto', $this->rechnungs_aussteller_typ, $this->rechnungs_aussteller_id, '7001');
                }

                if ($this->rechnungstyp == 'Gutschrift') {
                    $b->dropdown_kostenrahmen_nr('Kostenkonto', 'kostenkonto', $this->rechnungs_empfaenger_typ, $this->rechnungs_empfaenger_id, '');
                }

                $f->hidden_feld("kostentraeger_typ", $this->rechnungs_empfaenger_typ);
                $f->hidden_feld("kostentraeger_id", $this->rechnungs_empfaenger_id);

                $f->hidden_feld("belegnr", "$belegnr");
                $f->text_feld('Datum (dd.mm.jjjj)', 'datum', session()->get('temp_datum'), '10', 'datum', '');
                $f->text_feld('Kontoauszugsnr', 'kontoauszugsnr', session()->get('temp_kontoauszugsnummer'), '10', 'kontoauszugsnr', '');
                $this->kb = str_replace("<br>", "\n", $this->kurzbeschreibung);
                $f->text_bereich('Buchungstext', 'vzweck', "Erfnr:$this->belegnr, WA:$this->aussteller_ausgangs_rnr, Zahlungseingang Rnr:$this->rechnungsnummer, $this->kb", 30, 30, 'v_zweck_buchungstext');
                $pruefen = "onClick=\"felder_pruefen(this.form);return false;\"";
                $f->send_button_js("submit_rbb", "Buchen", $pruefen);
                $f->hidden_feld("option", "rechnung_buchen_gesendet");

                $f->ende_formular();
                $f->fieldset_ende();
            }  // ende status freigegeben
            else {
                echo "NICHT ZUR ZAHLUNG FREIGEGEBEN";
            }
        } // ende verbucht
    }

    function dropdown_buchungs_betrag_kurz($beschreibung, $name, $id, $js_optionen)
    {
        echo "<label for=\"$id\">$beschreibung</label>\n";

        echo "<select name=\"$name\" id=\"$id\" size=\"3\" $js_optionen >\n";
        echo "<option value=\"Skontobetrag\">Skontobetrag $this->rechnungs_skontobetrag</option>\n";
        echo "<option value=\"Bruttobetrag\">Bruttobetrag $this->rechnungs_brutto</option>\n";
        echo "<option value=\"Nettobetrag\">Nettobetrag $this->rechnungs_netto</option>\n";
        echo "</select>\n";
    }

    function dropdown_buchungs_betrag_kurz_sepa($beschreibung, $name, $id, $js_optionen)
    {
        echo "<label for=\"$id\">$beschreibung</label>\n";

        echo "<select name=\"$name\" id=\"$id\" size=\"3\" $js_optionen >\n";
        echo "<option value=\"$this->rechnungs_skontobetrag\">Skontobetrag $this->rechnungs_skontobetrag</option>\n";
        echo "<option value=\"$this->rechnungs_brutto\">Bruttobetrag $this->rechnungs_brutto</option>\n";
        echo "<option value=\"$this->rechnungs_netto\">Nettobetrag $this->rechnungs_netto</option>\n";
        echo "</select>\n";
    }

    function rechnung_als_gezahlt($belegnr, $datum)
    {
        $db_abfrage = "UPDATE RECHNUNGEN SET STATUS_BEZAHLT='1', BEZAHLT_AM='$datum' WHERE BELEG_NR='$belegnr' && AKTUELL='1'";
        DB::update($db_abfrage);
    }

    function rechnung_als_bestaetigt($belegnr, $datum)
    {
        $db_abfrage = "UPDATE RECHNUNGEN SET STATUS_BESTAETIGT='1', BEZAHLT_AM='$datum' WHERE BELEG_NR='$belegnr' && AKTUELL='1'";
        DB::update($db_abfrage);
    }

    function rechnung_deaktivieren($rechnung_dat)
    {
        $db_abfrage = "UPDATE RECHNUNGEN SET AKTUELL='0' WHERE RECHNUNG_DAT='$rechnung_dat' && AKTUELL='1'";
        DB::update($db_abfrage);
        /* Protokollieren */
        protokollieren('RECHNUNGEN', $rechnung_dat, $rechnung_dat);
    }

    function rechnungs_aenderungen_speichern($alt_dat, $belegnr, $rechnungsnummer, $a_ausnr, $e_einnr, $rechnungs_typ, $r_datum, $ein_datum, $netto, $brutto, $skontobetrag, $aussteller_typ, $aussteller_id, $empfaenger_typ, $empfaenger_id, $stat_erfasst, $stat_voll, $stat_zugew, $stat_z_frei, $stat_bezahlt, $faellig_am, $bezahlt_am, $kurzb, $empfangs_gkonto)
    {
        $r_datum = date_german2mysql($r_datum);
        $ein_datum = date_german2mysql($ein_datum);
        $bezahlt_am = date_german2mysql($bezahlt_am);
        $faellig_am = date_german2mysql($faellig_am);

        $netto = nummer_komma2punkt($netto);
        $brutto = nummer_komma2punkt($brutto);

        $db_abfrage = "INSERT INTO RECHNUNGEN VALUES (NULL, '$belegnr', '$rechnungsnummer', '$a_ausnr', '$e_einnr', '$rechnungs_typ', '$r_datum','$ein_datum', '$netto','$brutto','$skontobetrag',  '$aussteller_typ', '$aussteller_id','$empfaenger_typ', '$empfaenger_id', '1', '$stat_erfasst', '$stat_voll', '$stat_zugew', '$stat_z_frei', '$stat_bezahlt', '$stat_bezahlt', '$faellig_am', '$bezahlt_am', '$kurzb', '$empfangs_gkonto')";
        DB::insert($db_abfrage);

        /* Protokollieren */
        $last_dat = DB::getPdo()->lastInsertId();
        protokollieren('RECHNUNGEN', $last_dat, $alt_dat);
        /* Ausgabe weil speichern erfolgreich */
        echo "Grunddaten wurden geändert";
    }

    function beleg_kontierungs_arr($datum, $kto_auszugsnr, $belegnr, $vorzeichen, $buchungsbetrag, $vzweck, $geldkonto_id)
    {
        $this->rechnung_grunddaten_holen($belegnr);
        $r = new rechnung (); // aus berlussimo_class
        $b = new buchen (); // benötigt zum verbuchen einzelner positionen nach kontierung
        $kontierungs_status = $r->rechnung_auf_kontierung_pruefen($belegnr);
        if ($kontierungs_status == 'vollstaendig') {
            $result = DB::select("SELECT *, BRUTTO-NETTO AS MWST_BRUTTO,  (BRUTTO-NETTO)/100*(100-SKONTO) AS  MWST_SKONTIERT FROM ( 
SELECT sum( GESAMT_SUMME - ( GESAMT_SUMME /100 * RABATT_SATZ ) ) AS NETTO, sum( (
(
GESAMT_SUMME - ( GESAMT_SUMME /100 * RABATT_SATZ ) ) /100
) * ( 100 + MWST_SATZ )
) AS BRUTTO, sum( (
(
(
GESAMT_SUMME - ( GESAMT_SUMME /100 * RABATT_SATZ ) ) /100
) * ( 100 + MWST_SATZ ) /100
) * ( 100 - SKONTO )
) AS SKONTO_BETRAG,


 KONTENRAHMEN_KONTO, KOSTENTRAEGER_TYP, KOSTENTRAEGER_ID,SKONTO
FROM `KONTIERUNG_POSITIONEN`
WHERE BELEG_NR = '$belegnr' && AKTUELL = '1'
GROUP BY KOSTENTRAEGER_TYP, KOSTENTRAEGER_ID, KONTENRAHMEN_KONTO) as t1");

            if (!empty($result)) {
                foreach ($result as $row) {
                    $my_array [] = $row;
                    $kostentraeger_typ = $row ['KOSTENTRAEGER_TYP'];
                    $kostentraeger_id = $row ['KOSTENTRAEGER_ID'];
                    $kostenkonto = $row ['KONTENRAHMEN_KONTO'];
                    $netto = sprintf("%01.2f", $row ['NETTO']);
                    $brutto = sprintf("%01.2f", $row ['BRUTTO']);
                    $brutto_mwst = sprintf("%01.2f", $row ['MWST_BRUTTO']);
                    $skonto = sprintf("%01.2f", $row ['SKONTO_BETRAG']);
                    $skonto_mwst = sprintf("%01.2f", $row ['MWST_SKONTIERT']);
                    if ($buchungsbetrag == 'Nettobetrag') {
                        $b->geldbuchung_speichern_rechnung($datum, $kto_auszugsnr, $belegnr, $vorzeichen . $netto, $vzweck, $geldkonto_id, $kostentraeger_typ, $kostentraeger_id, $kostenkonto);
                    }
                    if ($buchungsbetrag == 'Bruttobetrag') {
                        $b->geldbuchung_speichern_rechnung($datum, $kto_auszugsnr, $belegnr, $vorzeichen . $brutto, $vzweck, $geldkonto_id, $kostentraeger_typ, $kostentraeger_id, $kostenkonto, $vorzeichen . $brutto_mwst);
                    }
                    if ($buchungsbetrag == 'Skontobetrag') {
                        $b->geldbuchung_speichern_rechnung($datum, $kto_auszugsnr, $belegnr, $vorzeichen . $skonto, $vzweck, $geldkonto_id, $kostentraeger_typ, $kostentraeger_id, $kostenkonto, $vorzeichen . $skonto_mwst);
                    }
                } // end while
            } // end if $numrows
            weiterleiten_in_sec(route('web::buchen::legacy', ['option' => 'buchungs_journal'], false), 2);
        }  // end if
        else {
            echo "FEHLER: Kontierung $kontierungs_status";
            weiterleiten_in_sec(route('web::rechnungen::legacy', ['option' => 'rechnung_kontieren', 'belegnr' => $belegnr], false), 3);
        }
    }

    function rechnungseingangsbuch_kurz($typ, $partner_id, $monat, $jahr, $rechnungstyp)
    {
        $monatname = monat2name($monat);
        $form = new formular ();
        $p = new partner ();
        $p->partner_grunddaten(session()->get('partner_id'));
        $form->erstelle_formular("Rechnungseingangsbuch $monatname $jahr - $p->partner_name", NULL);
        echo "<table id=\"monate_links\"><tr><td>";
        $bg = new berlussimo_global ();
        $link = route('web::buchen::legacy', ['option' => 'eingangsbuch_kurz'], false);
        $bg->monate_jahres_links($jahr, $link);
        echo "</td></tr></table>";
        $rechnungen_arr = $this->eingangsrechnungen_arr($typ, $partner_id, $monat, $jahr, $rechnungstyp);

        $this->rechnungsbuch_anzeigen_ein_kurz($rechnungen_arr);
        $form->ende_formular();
    }

    /* Array mit Kontierungsdaten einer Rechnung/Beleges für die Buchung einer Rechnung, wie kontiert */

    function eingangsrechnungen_arr($empfaenger_typ, $empfaenger_id, $monat, $jahr, $rechnungstyp)
    {
        // echo "<h1>$monat</h1>";
        if ($rechnungstyp == 'Rechnung') {
            $r_sql = "(RECHNUNGSTYP='$rechnungstyp' OR RECHNUNGSTYP='Stornorechnung' OR RECHNUNGSTYP='Gutschrift')";
        } else {
            $r_sql = "RECHNUNGSTYP='$rechnungstyp'";
        }
        if ($monat == 'alle') {
            $result = DB::select("SELECT * FROM RECHNUNGEN WHERE EMPFAENGER_ID='$empfaenger_id' && EMPFAENGER_TYP='$empfaenger_typ' && DATE_FORMAT( RECHNUNGSDATUM, '%Y' ) = '$jahr' && $r_sql && AKTUELL = '1' GROUP BY BELEG_NR ORDER BY EMPFAENGER_EINGANGS_RNR ASC");
        } else {
            $result = DB::select("SELECT * FROM RECHNUNGEN WHERE EMPFAENGER_ID='$empfaenger_id' && EMPFAENGER_TYP='$empfaenger_typ' && DATE_FORMAT( RECHNUNGSDATUM, '%Y-%m' ) = '$jahr-$monat' && $r_sql && AKTUELL = '1' GROUP BY BELEG_NR ORDER BY EMPFAENGER_EINGANGS_RNR ASC");
        }
        if (!empty($result)) {
            return $result;
        }
    } // end function

    function rechnungsbuch_anzeigen_ein_kurz($arr)
    {
        $anzahl = count($arr);
        if ($anzahl) {
            echo "<span>";
            if (!request()->has('anzeige') || request()->input('anzeige') == 'rechnungsnummer') {
                $anzeige_var = 'rechnungsnummer';
                session()->put('rg_sort', $anzeige_var);
                $link_nr = "<a href='" . route('web::buchen::legacy', ['option' => 'eingangsbuch_kurz', 'anzeige' => 'empfaenger_eingangs_rnr']) . "'>WE-NR anzeigen</a>";
            } else {
                $anzeige_var = request()->input('anzeige');
                session()->put('rg_sort', $anzeige_var);
                $link_nr = "<a href='" . route('web::buchen::legacy', ['option' => 'eingangsbuch_kurz', 'anzeige' => 'rechnungsnummer']) . "'>RG-NR anzeigen</a>";
            }
            echo $link_nr;
            echo "</span>";
            echo "<div>";
            echo "<p id=\"link_rechnung_klein_sw\" style='color: black;'>SCHWARZ - NICHT BEZAHLT + KEIN GELDEINGANG</p>";
            echo "<p id=\"link_rechnung_klein_we\" style='color: grey;'>GRAU - BEZAHLT + KEIN GELDEINGANG</p>";
            echo "<p id=\"link_rechnung_klein_bl\" style='color: blue;'>BLAU - NICHT BEZAHLT + GELDEINGANG BESTÄTIGT</p>";
            echo "<p id=\"link_rechnung_klein_gr\" style='color: green;'>GRÜN - BEZAHLT + GELDEINGANG BESTÄTIGT</p>";
            echo "</div>";
            echo "<table id=\"positionen_tab\">\n";
            echo "<tr><td align=\"left\" valign=\"top\">";

            $zaehler = 0;
            $spalte = 1;
            for ($a = 0; $a < $anzahl; $a++) {
                $zaehler = $zaehler + 1;
                $belegnr = $arr [$a] ['BELEG_NR'];
                $this->rechnung_grunddaten_holen($belegnr);

                // $anzeige_nr = $this->$anzeige_var;
                $anzeige_nr = $this->{session()->get('rg_sort')};

                if ($this->status_bezahlt == 0 && $this->status_bestaetigt == 0) {
                    $beleg_link = "<a id=\"link_rechnung_gross_sw\" style='color: black;' href='" . route('web::rechnungen::legacy', ['option' => 'rechnung_zahlung_buchen', 'belegnr' => $belegnr]) . "'>$anzeige_nr</a>\n";
                }

                if ($this->status_bezahlt == 1 && $this->status_bestaetigt == 0) {
                    $beleg_link = "<a id=\"link_rechnung_gross_we\" style='color: grey;' href='" . route('web::rechnungen::legacy', ['option' => 'rechnung_zahlung_buchen', 'belegnr' => $belegnr]) . "'>$anzeige_nr</a>\n";
                }

                if ($this->status_bezahlt == 0 && $this->status_bestaetigt == 1) {
                    $beleg_link = "<a id=\"link_rechnung_gross_bl\" style='color: blue;' href='" . route('web::rechnungen::legacy', ['option' => 'rechnung_zahlung_buchen', 'belegnr' => $belegnr]) . "'>$anzeige_nr</a>\n";
                }

                if ($this->status_bezahlt == 1 && $this->status_bestaetigt == 1) {
                    $beleg_link = "<a id=\"link_rechnung_gross_gr\" style='color: green;' href='" . route('web::rechnungen::legacy', ['option' => 'rechnung_zahlung_buchen', 'belegnr' => $belegnr]) . "'>$anzeige_nr</a>\n";
                }

                if ($zaehler == 13) {
                    echo "$beleg_link</td><td align=\"left\" valign=\"top\">";
                    $spalte = $spalte + 1;
                    $zaehler = 0;
                } else {
                    echo "$beleg_link<br>";
                }

                if ($spalte == 15) {
                    echo "</td></tr>";
                    echo "<tr><td align=\"left\" valign=\"top\">";
                    $spalte = 1;
                }
            } // end for
            echo "</td></tr></table>";
        } else {
            echo "Keine Rechnungen in diesem Monat";
        }
    }

    function rechnungseingangsbuch_kurz_zahlung($typ, $partner_id, $monat, $jahr, $rechnungstyp)
    {
        $monatname = monat2name($monat);
        $form = new formular ();
        $p = new partner ();
        $p->partner_grunddaten(session()->get('partner_id'));
        $form->erstelle_formular("Ausgewählt: $p->partner_name", NULL);
        $form->erstelle_formular("Rechnungseingangsbuch $monatname $jahr - $p->partner_name", NULL);
        echo "<table id=\"monate_links\"><tr><td>";
        $bg = new berlussimo_global ();
        $link = "?daten=ueberweisung&option=re_zahlen";
        $bg->monate_jahres_links($jahr, $link);
        // $this->r_eingang_monate_links($monat, $jahr);
        echo "</td></tr></table>";
        $rechnungen_arr = $this->eingangsrechnungen_arr($typ, $partner_id, $monat, $jahr, $rechnungstyp);
        /* Druck LOGO */

        $d = new detail ();

        $this->rechnungsbuch_anzeigen_ein_kurz_zahlung($rechnungen_arr);
        $form->ende_formular();
        $form->ende_formular();
    }

    function rechnungsbuch_anzeigen_ein_kurz_zahlung($arr)
    {
        $anzahl = count($arr);
        if ($anzahl) {
            echo "<div>";
            echo "<p id=\"link_rechnung_klein_sw\">SCHWARZ - NICHT BEZAHLT + KEIN GELDEINGANG</p>";
            echo "<p id=\"link_rechnung_klein_we\">WEISS - BEZAHLT + KEIN GELDEINGANG</p>";
            echo "<p id=\"link_rechnung_klein_bl\">BLAU - NICHT BEZAHLT + GELDEINGANG BESTÄTIGT</p>";
            echo "<p id=\"link_rechnung_klein_gr\">GRÜN - BEZAHLT + GELDEINGANG BESTÄTIGT</p>";
            echo "</div>";

            echo "<table id=\"positionen_tab\">\n";
            echo "<tr><td align=\"left\" valign=\"top\">";

            $zaehler = 0;
            $spalte = 1;
            for ($a = 0; $a < $anzahl; $a++) {
                $zaehler = $zaehler + 1;
                $belegnr = $arr [$a] ['BELEG_NR'];
                $this->rechnung_grunddaten_holen($belegnr);

                if ($this->status_bezahlt == 0 && $this->status_bestaetigt == 0) {
                    $beleg_link = "<a id=\"link_rechnung_gross_sw\" href=\"?daten=ueberweisung&option=re_zahlen&belegnr=$belegnr\">$this->rechnungsnummer</a>\n";
                }

                if ($this->status_bezahlt == 1 && $this->status_bestaetigt == 0) {
                    $beleg_link = "<a id=\"link_rechnung_gross_we\" href=\"?daten=ueberweisung&option=re_zahlen&belegnr=$belegnr\">$this->rechnungsnummer</a>\n";
                }

                if ($this->status_bezahlt == 0 && $this->status_bestaetigt == 1) {
                    $beleg_link = "<a id=\"link_rechnung_gross_bl\" href=\"?daten=ueberweisung&option=re_zahlen&belegnr=$belegnr\">$this->rechnungsnummer</a>\n";
                }

                if ($this->status_bezahlt == 1 && $this->status_bestaetigt == 1) {
                    $beleg_link = "<a id=\"link_rechnung_gross_gr\" href=\"?daten=ueberweisung&option=re_zahlen&belegnr=$belegnr\">$this->rechnungsnummer</a>\n";
                }

                if ($zaehler == 13) {
                    echo "$beleg_link</td><td align=\"left\" valign=\"top\">";
                    $spalte = $spalte + 1;
                    $zaehler = 0;
                } else {
                    echo "$beleg_link<br>";
                }

                if ($spalte == 15) {
                    echo "</td></tr><tr><td align=\"left\" valign=\"top\" colspan=\"8\"><hr></td></tr><tr><td align=\"left\" valign=\"top\">";
                    $spalte = 1;
                }
            } // end for
            echo "</td></tr></table>";
        } else {
            echo "Keine Rechnungen in diesem Monat";
        }
    }

    function rechnungseingangsbuch_kurz_zahlung_sepa($typ, $partner_id, $monat, $jahr, $rechnungstyp)
    {
        $monatname = monat2name($monat);
        $form = new formular ();
        $p = new partner ();
        $p->partner_grunddaten(session()->get('partner_id'));
        $form->erstelle_formular("Ausgewählt: $p->partner_name", NULL);
        $form->erstelle_formular("Rechnungseingangsbuch $monatname $jahr - $p->partner_name", NULL);
        echo "<table id=\"monate_links\"><tr><td>";
        $bg = new berlussimo_global ();
        $link = route('web::sepa::legacy', ['option' => 're_zahlen'], false);
        $bg->monate_jahres_links($jahr, $link);
        echo "</td></tr></table>";
        $rechnungen_arr = $this->eingangsrechnungen_arr($typ, $partner_id, $monat, $jahr, $rechnungstyp);
        /* Druck LOGO */

        $d = new detail ();

        $this->rechnungsbuch_anzeigen_ein_kurz_zahlung_sepa($rechnungen_arr);
        $form->ende_formular();
        $form->ende_formular();
    }

    function rechnungsbuch_anzeigen_ein_kurz_zahlung_sepa($arr)
    {
        $anzahl = count($arr);
        if ($anzahl) {
            echo "<div>";
            echo "<p id=\"link_rechnung_klein_sw\">SCHWARZ - NICHT BEZAHLT + KEIN GELDEINGANG</p>";
            echo "<p id=\"link_rechnung_klein_we\">WEISS - BEZAHLT + KEIN GELDEINGANG</p>";
            echo "<p id=\"link_rechnung_klein_bl\">BLAU - NICHT BEZAHLT + GELDEINGANG BESTÄTIGT</p>";
            echo "<p id=\"link_rechnung_klein_gr\">GRÜN - BEZAHLT + GELDEINGANG BESTÄTIGT</p>";
            echo "</div>";

            echo "<table id=\"positionen_tab\">\n";
            echo "<tr><td align=\"left\" valign=\"top\">";

            $zaehler = 0;
            $spalte = 1;
            for ($a = 0; $a < $anzahl; $a++) {
                $zaehler = $zaehler + 1;
                $belegnr = $arr [$a] ['BELEG_NR'];
                $this->rechnung_grunddaten_holen($belegnr);

                if ($this->status_bezahlt == 0 && $this->status_bestaetigt == 0) {
                    $beleg_link = "<a id=\"link_rechnung_gross_sw\" href='" . route('web::sepa::legacy', ['option' => 're_zahlen', 'belegnr' => $belegnr]) . "'>$this->rechnungsnummer</a>\n";
                }

                if ($this->status_bezahlt == 1 && $this->status_bestaetigt == 0) {
                    $beleg_link = "<a id=\"link_rechnung_gross_we\" href='" . route('web::sepa::legacy', ['option' => 're_zahlen', 'belegnr' => $belegnr]) . "'>$this->rechnungsnummer</a>\n";
                }

                if ($this->status_bezahlt == 0 && $this->status_bestaetigt == 1) {
                    $beleg_link = "<a id=\"link_rechnung_gross_bl\" href='" . route('web::sepa::legacy', ['option' => 're_zahlen', 'belegnr' => $belegnr]) . "'>$this->rechnungsnummer</a>\n";
                }

                if ($this->status_bezahlt == 1 && $this->status_bestaetigt == 1) {
                    $beleg_link = "<a id=\"link_rechnung_gross_gr\" href='" . route('web::sepa::legacy', ['option' => 're_zahlen', 'belegnr' => $belegnr]) . "'>$this->rechnungsnummer</a>\n";
                }

                if ($zaehler == 15) {
                    echo "$beleg_link</td><td align=\"left\" valign=\"top\">";
                    $spalte = $spalte + 1;
                    $zaehler = 0;
                } else {
                    echo "$beleg_link<br>";
                }

                if ($spalte == 8) {
                    echo "</td></tr><tr><td align=\"left\" valign=\"top\" colspan=\"8\"><hr></td></tr><tr><td align=\"left\" valign=\"top\">";
                    $spalte = 1;
                }
            } // end for
            echo "</td></tr></table>";
        } else {
            echo "Keine Rechnungen in diesem Monat";
        }
    }

    function rechnungsausgangsbuch_kurz_zahlung_sepa($typ, $partner_id, $monat, $jahr, $rechnungstyp)
    {
        $monatname = monat2name($monat);
        $form = new formular ();
        $p = new partner ();
        $p->partner_grunddaten(session()->get('partner_id'));
        $form->erstelle_formular("Ausgewählt: $p->partner_name", NULL);
        $form->erstelle_formular("Rechnungsausgangsbuch $monatname $jahr - $p->partner_name", NULL);
        echo "<table id=\"monate_links\"><tr><td>";
        $bg = new berlussimo_global ();
        $link = route('web::sepa::legacy', ['option' => 'ra_zahlen'], false);
        $bg->monate_jahres_links($jahr, $link);
        // $this->r_eingang_monate_links($monat, $jahr);
        echo "</td></tr></table>";
        $rechnungen_arr = $this->ausgangsrechnungen_arr($typ, $partner_id, $monat, $jahr, $rechnungstyp);
        /* Druck LOGO */

        $d = new detail ();

        $this->rechnungsbuch_anzeigen_aus_kurz_zahlung_sepa($rechnungen_arr);
        $form->ende_formular();
        $form->ende_formular();
    }

    function ausgangsrechnungen_arr($aussteller_typ, $aussteller_id, $monat, $jahr, $rechnungstyp)
    {
        if ($rechnungstyp == 'Rechnung') {
            $r_sql = "(RECHNUNGSTYP='$rechnungstyp' OR RECHNUNGSTYP='Stornorechnung' OR RECHNUNGSTYP='Gutschrift')";
        } else {
            $r_sql = "RECHNUNGSTYP='$rechnungstyp'";
        }

        if ($monat == 'alle') {
            $result = DB::select("SELECT * FROM RECHNUNGEN WHERE AUSSTELLER_ID='$aussteller_id' && AUSSTELLER_TYP='$aussteller_typ' && DATE_FORMAT( RECHNUNGSDATUM, '%Y' ) = '$jahr' && $r_sql && AKTUELL = '1' ORDER BY AUSTELLER_AUSGANGS_RNR ASC");
        } else {
            $result = DB::select("SELECT * FROM RECHNUNGEN WHERE AUSSTELLER_ID='$aussteller_id' && AUSSTELLER_TYP='$aussteller_typ' && DATE_FORMAT( RECHNUNGSDATUM, '%Y-%m' ) = '$jahr-$monat' && $r_sql && AKTUELL = '1' ORDER BY AUSTELLER_AUSGANGS_RNR ASC");
        }
        return $result;
    }

    function rechnungsbuch_anzeigen_aus_kurz_zahlung_sepa($arr)
    {
        $anzahl = count($arr);
        if ($anzahl) {
            echo "<div>";
            echo "<p id=\"link_rechnung_klein_sw\">SCHWARZ - NICHT BEZAHLT + KEIN GELDEINGANG</p>";
            echo "<p id=\"link_rechnung_klein_we\">WEISS - BEZAHLT + KEIN GELDEINGANG</p>";
            echo "<p id=\"link_rechnung_klein_bl\">BLAU - NICHT BEZAHLT + GELDEINGANG BESTÄTIGT</p>";
            echo "<p id=\"link_rechnung_klein_gr\">GRÜN - BEZAHLT + GELDEINGANG BESTÄTIGT</p>";
            echo "</div>";

            echo "<table id=\"positionen_tab\">\n";
            echo "<tr><td align=\"left\" valign=\"top\">";

            $zaehler = 0;
            $spalte = 1;
            for ($a = 0; $a < $anzahl; $a++) {
                $zaehler = $zaehler + 1;
                $belegnr = $arr [$a] ['BELEG_NR'];
                $this->rechnung_grunddaten_holen($belegnr);

                if ($this->status_bezahlt == 0 && $this->status_bestaetigt == 0) {
                    $beleg_link = "<a id=\"link_rechnung_gross_sw\" href='" . route('web::sepa::legacy', ['option' => 'ra_zahlen', 'belegnr' => $belegnr]) . "'>$this->rechnungsnummer</a>\n";
                }

                if ($this->status_bezahlt == 1 && $this->status_bestaetigt == 0) {
                    $beleg_link = "<a id=\"link_rechnung_gross_we\" href='" . route('web::sepa::legacy', ['option' => 'ra_zahlen', 'belegnr' => $belegnr]) . "'>$this->rechnungsnummer</a>\n";
                }

                if ($this->status_bezahlt == 0 && $this->status_bestaetigt == 1) {
                    $beleg_link = "<a id=\"link_rechnung_gross_bl\" href='" . route('web::sepa::legacy', ['option' => 'ra_zahlen', 'belegnr' => $belegnr]) . "'>$this->rechnungsnummer</a>\n";
                }

                if ($this->status_bezahlt == 1 && $this->status_bestaetigt == 1) {
                    $beleg_link = "<a id=\"link_rechnung_gross_gr\" href='" . route('web::sepa::legacy', ['option' => 'ra_zahlen', 'belegnr' => $belegnr]) . "'>$this->rechnungsnummer</a>\n";
                }

                if ($zaehler == 15) {
                    echo "$beleg_link</td><td align=\"left\" valign=\"top\">";
                    $spalte = $spalte + 1;
                    $zaehler = 0;
                } else {
                    echo "$beleg_link<br>";
                }

                if ($spalte == 8) {
                    echo "</td></tr><tr><td align=\"left\" valign=\"top\" colspan=\"8\"><hr></td></tr><tr><td align=\"left\" valign=\"top\">";
                    $spalte = 1;
                }
            } // end for
            echo "</td></tr></table>";
        } else {
            echo "Keine Rechnungen in diesem Monat";
        }
    }

    function rechnungsausgangsbuch_kurz($typ, $partner_id, $monat, $jahr, $rechnungstyp)
    {
        $monatname = monat2name($monat);
        $form = new formular ();
        $p = new partner ();
        $p->partner_grunddaten(session()->get('partner_id'));
        $form->erstelle_formular("Ausgewählt: $p->partner_name", NULL);
        $form->erstelle_formular("Rechnungseingangsbuch $monatname $jahr - $p->partner_name", NULL);
        echo "<table id=\"monate_links\"><tr><td>";
        $bg = new berlussimo_global ();
        $link = route('web::buchen::legacy', ['option' => 'ausgangsbuch_kurz'], false);
        $bg->monate_jahres_links($jahr, $link);
        echo "</td></tr></table>";
        $rechnungen_arr = $this->ausgangsrechnungen_arr($typ, $partner_id, $monat, $jahr, $rechnungstyp);
        $d = new detail ();
        $mandanten_nr = $d->finde_mandanten_nr($partner_id);

        $logo_file = $typ . "/" . $partner_id . "_logo.png";
        if (Storage::disk('logos')->exists($logo_file)) {
            $logo_file = Storage::disk('logos')->url($logo_file);
            echo "<div id=\"div_logo\"><img src='$logo_file'><br>$p->partner_name Rechnungsausgangsbuch $monatname $jahr Mandanten-Nr.: $mandanten_nr Blatt: $monat<hr></div>\n";
        } else {
            echo "<div id=\"div_logo\">KEIN LOGO<br>Folgende Datei erstellen: " . Storage::disk('logos')->fullPath($logo_file) . "<hr></div>";
        }

        $this->rechnungsbuch_anzeigen_aus_kurz($rechnungen_arr);
        $form->ende_formular();
        $form->ende_formular();
    }

    function rechnungsbuch_anzeigen_aus_kurz($arr)
    {
        $anzahl = count($arr);
        if ($anzahl) {
            echo "<div>";
            echo "<p id=\"link_rechnung_klein_sw\" style='color: black;'>SCHWARZ - NICHT BEZAHLT + KEIN GELDEINGANG</p>";
            echo "<p id=\"link_rechnung_klein_we\" style='color: grey;'>GRAU - BEZAHLT + KEIN GELDEINGANG</p>";
            echo "<p id=\"link_rechnung_klein_bl\" style='color: blue;'>BLAU - NICHT BEZAHLT + GELDEINGANG BESTÄTIGT</p>";
            echo "<p id=\"link_rechnung_klein_gr\" style='color: green;'>GRÜN - BEZAHLT + GELDEINGANG BESTÄTIGT</p>";
            echo "</div>";
            echo "<table id=\"positionen_tab\">\n";
            echo "<tr><td align=\"left\" valign=\"top\">";

            $zaehler = 0;
            $spalte = 1;

            $summe_sw = 0;
            $summe_we = 0;
            $summe_bl = 0;
            $summe_gr = 0;

            for ($a = 0; $a < $anzahl; $a++) {
                $zaehler = $zaehler + 1;
                $belegnr = $arr [$a] ['BELEG_NR'];
                $this->rechnung_grunddaten_holen($belegnr);

                if ($this->status_bezahlt == 0 && $this->status_bestaetigt == 0) {
                    $beleg_link = "<a id=\"link_rechnung_gross_sw\" style='color: black;' href='" . route('web::rechnungen::legacy', ['option' => 'rechnung_empfang_buchen', 'belegnr' => $belegnr]) . "'>$this->rechnungsnummer</a>\n";
                    $summe_sw += $this->rechnungs_brutto;
                }

                if ($this->status_bezahlt == 1 && $this->status_bestaetigt == 0) {
                    $beleg_link = "<a id=\"link_rechnung_gross_we\" style='color: grey;' href='" . route('web::rechnungen::legacy', ['option' => 'rechnung_empfang_buchen', 'belegnr' => $belegnr]) . "'>$this->rechnungsnummer</a>\n";
                    $summe_we += $this->rechnungs_brutto;
                }

                if ($this->status_bezahlt == 0 && $this->status_bestaetigt == 1) {
                    $beleg_link = "<a id=\"link_rechnung_gross_bl\" style='color: blue;' href='" . route('web::rechnungen::legacy', ['option' => 'rechnung_empfang_buchen', 'belegnr' => $belegnr]) . "'>$this->rechnungsnummer</a>\n";
                    $summe_bl += $this->rechnungs_brutto;
                }

                if ($this->status_bezahlt == 1 && $this->status_bestaetigt == 1) {
                    $beleg_link = "<a id=\"link_rechnung_gross_gr\" style='color: green;' href='" . route('web::rechnungen::legacy', ['option' => 'rechnung_empfang_buchen', 'belegnr' => $belegnr]) . "'>$this->rechnungsnummer</a>\n";
                    $summe_gr += $this->rechnungs_brutto;
                }

                if ($zaehler == 15) {
                    echo "$beleg_link</td><td align=\"left\" valign=\"top\">";
                    $spalte = $spalte + 1;
                    $zaehler = 0;
                } else {
                    echo "$beleg_link<br>";
                }

                if ($spalte == 10) {
                    echo "</td></tr><tr><td align=\"left\" valign=\"top\" colspan=\"6\"><hr></td></tr><tr><td align=\"left\" valign=\"top\">";
                    $spalte = 1;
                }
            } // end for
            echo "</td></tr><tr><td colspan=\"5\">Summe UNBEZAHLT: $summe_sw<br>Summe BEZAHLT, nicht verbucht(intern): $summe_we<br>Summe BEZAHLT, nur gebucht, externe: $summe_bl<br>Summe BEZAHLT (intern): $summe_gr<br>";
            echo "</td></tr></table>";
        } else {
            echo "Keine Rechnungen in diesem Monat";
        }
    }

    function get_rechnungsnummer($beleg_nr)
    {
        $result = DB::select("SELECT RECHNUNGSNUMMER FROM RECHNUNGEN WHERE BELEG_NR='$beleg_nr' && AKTUELL = '1'");
        $row = $result[0];
        if ($row) {
            return $row ['RECHNUNGSNUMMER'];
        }
    }

    function bezahlte_rechnungen_anzeigen()
    {
        echo "bez rech";
    }

    function unbezahlte_rechnungen_anzeigen()
    {
        echo "UNbez rech";
    }

    function bestaetigte_rechnungen_anzeigen()
    {
        echo "bestätigte rech";
    }

    function unbestaetigte_rechnungen_anzeigen()
    {
        echo "UNbestätigte rech";
    }

    function ursprungs_rechnungs_nr_arr($beleg_nr)
    {
        $result = DB::select("SELECT U_BELEG_NR FROM RECHNUNGEN_POSITIONEN WHERE BELEG_NR='$beleg_nr' && U_BELEG_NR IS NOT NULL && AKTUELL='1' GROUP BY U_BELEG_NR");
        /* Wenn urpsrungsrechnungen vorhanden, ins array hinzufügen */
        if (!empty($result)) {
            $this->ursprungs_array [$beleg_nr] = $result;
        }

        /* Prüfen ob aktuelle rechnung vorrechnungen hat */
        if (is_array($this->ursprungs_array [$beleg_nr])) {
            $anzahl_zu_beleg = count($this->ursprungs_array [$beleg_nr]);

            if ($anzahl_zu_beleg > 0) {
                for ($a = 0; $a < $anzahl_zu_beleg; $a++) {
                    $u_b_nr = $this->ursprungs_array [$beleg_nr] [$a] ['U_BELEG_NR'];
                    if ($beleg_nr != $u_b_nr) {
                        $this->ursprungs_rechnungs_nr_arr($u_b_nr);
                    }
                }
            }
        }
    }

    function rechnung_2_pdf(&$pdf, $beleg_nr)
    {
        $this->rechnung_grunddaten_holen($beleg_nr);
        /* Prüfen ob Rechnung vorhanden */
        if (!$this->rechnungsnummer) {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\ErrorMessage("Rechnung exisitiert nicht")
            );
        }

        /* Partnerinformationen einholen */
        $p = new partners ();
        $p->get_partner_info($this->rechnung_aussteller_partner_id);

        $table_arr = $this->rechnungs_positionen_arr($beleg_nr);
        $anz = $this->anzahl_positionen;
        $g_netto = 0;
        $new_pos = 0;
        for ($index = 0; $index < $anz; $index++) {
            $menge = $table_arr [$index] ['MENGE'];
            $preis = $table_arr [$index] ['PREIS'];
            $artikel_nr = $table_arr [$index] ['ARTIKEL_NR'];
            $lieferant_id = $table_arr [$index] ['ART_LIEFERANT'];

            /* Infos aus Katalog zu Artikelnr */
            $artikel_info_arr = $this->artikel_info($lieferant_id, $artikel_nr);
            $bezeichnung = '';
            $v_einheit = '';
            for ($i = 0; $i < count($artikel_info_arr); $i++) {
                if (!empty ($artikel_info_arr [$i] ['BEZEICHNUNG'])) {
                    $bezeichnung = str_replace("<br>", "\n", $artikel_info_arr [$i] ['BEZEICHNUNG']);
                    $v_einheit = $artikel_info_arr [$i] ['EINHEIT'];
                } else {
                    $bezeichnung = 'Unbekannt';
                    $v_einheit = '';
                }
            }

            /* Prüfen ob es sich um eine Leistung handelt */
            /*
			 * $L_ = substr($artikel_nr, 0,2);
			 * if($L_ =='L-'){
			 * $u_beleg_nr_l = $table_arr[$index]['ART_LIEFERANT'];
			 * if(!empty($u_beleg_nr_l)){
			 * $u_pos
			 * }
			 * }
			 */

            $artikel_nr = $table_arr [$index] ['ARTIKEL_NR'];
            $mwst_satz = $table_arr [$index] ['MWST_SATZ'];
            $skonto_satz = $table_arr [$index] ['SKONTO'];
            $rabatt_satz = $table_arr [$index] ['RABATT_SATZ'];
            $gesamt_preis = $table_arr [$index] ['GESAMT_NETTO'];
            $pos = $table_arr [$index] ['POSITION'];

            // echo "$beleg_nr $pos<br>";
            if ($this->get_ueberschrift($beleg_nr, $pos)) {
                $ueb = $this->get_ueberschrift($beleg_nr, $pos);
                $tab_arr [$new_pos] ['BEZ'] = "\n" . '<b>' . $ueb . '</b>';
                // $tab_arr[$index]['ARTIKEL_NR'] = '<b>'.$this->get_ueberschrift($beleg_nr, $pos).'</b>';
                $new_pos++;
            }

            $tab_arr [$new_pos] ['POSITION'] = $pos;
            $tab_arr [$new_pos] ['ARTIKEL_NR'] = $artikel_nr;
            $tab_arr [$new_pos] ['BEZ'] = $bezeichnung;
            $tab_arr [$new_pos] ['MENGE'] = nummer_punkt2komma($menge) . " $v_einheit";
            $tab_arr [$new_pos] ['PREIS'] = nummer_punkt2komma($preis);
            $tab_arr [$new_pos] ['MWST_SATZ'] = nummer_punkt2komma($mwst_satz) . '%';
            $tab_arr [$new_pos] ['RABATT_SATZ'] = nummer_punkt2komma($rabatt_satz) . '%';
            $tab_arr [$new_pos] ['SKONTO'] = nummer_punkt2komma($skonto_satz) . '%';
            $tab_arr [$new_pos] ['GESAMT_NETTO'] = nummer_punkt2komma($gesamt_preis) . ' €';
            $g_netto += $gesamt_preis;
            $tab_arr [$new_pos] ['SUMM_NETTO'] = nummer_punkt2komma($g_netto);
            /* Linien und Netto, Brutto usw in den Tabellenarray hinzufügen */
            if ($index == $anz - 1) {
                $tab_arr [$new_pos + 1] ['POSITION'] = '==';
                $tab_arr [$new_pos + 1] ['ARTIKEL_NR'] = '==============';
                $tab_arr [$new_pos + 1] ['BEZ'] = '==========================';
                $tab_arr [$new_pos + 1] ['MENGE'] = '==========';
                $tab_arr [$new_pos + 1] ['PREIS'] = '==========';
                $tab_arr [$new_pos + 1] ['MWST_SATZ'] = '==========';
                $tab_arr [$new_pos + 1] ['RABATT_SATZ'] = '==========';
                $tab_arr [$new_pos + 1] ['SKONTO'] = '==========';
                $tab_arr [$new_pos + 1] ['GESAMT_NETTO'] = '==========';
                $tab_arr [$new_pos + 2] ['SKONTO'] = '<b>Netto</b>';
                // $tab_arr[$new_pos+2]['GESAMT_NETTO'] = '<b>'.nummer_punkt2komma($g_netto).' €</b>'.$this->rechungs_netto;
                $tab_arr [$new_pos + 2] ['GESAMT_NETTO'] = '<b>' . nummer_punkt2komma($this->rechnungs_netto) . ' €</b>';
                $tab_arr [$new_pos + 3] ['SKONTO'] = '<b>MWSt</b>';
                // $this->rechnungs_mwst = $this->rechnungs_brutto - $this->rechnungs_netto;
                $tab_arr [$new_pos + 3] ['GESAMT_NETTO'] = '<b>' . nummer_punkt2komma($this->rechnungs_mwst) . ' €</b>';
                $tab_arr [$new_pos + 4] ['SKONTO'] = '<b>Brutto</b>';
                $tab_arr [$new_pos + 4] ['GESAMT_NETTO'] = '<b>' . nummer_punkt2komma($this->rechnungs_brutto) . " €</b>";
                $tab_arr [$new_pos + 5] ['SKONTO'] = '<b>Skonto</b>';
                $tab_arr [$new_pos + 5] ['GESAMT_NETTO'] = '<b>' . nummer_punkt2komma($this->rechnungs_skontoabzug) . ' €</b>';
                $tab_arr [$new_pos + 6] ['SKONTO'] = '<b>Skontiert</b>';
                $tab_arr [$new_pos + 6] ['GESAMT_NETTO'] = '<b>' . nummer_punkt2komma($this->rechnungs_skontobetrag) . ' €</b>';
            }
            $new_pos++;
        }

        /* Spaltendefinition */
        $cols = array(
            'POSITION' => "<b>POS.</b>",
            'ARTIKEL_NR' => "<b>ARTIKELNR</b>",
            'BEZ' => "<b>BEZEICHNUNG</b>",
            'MENGE' => "<b>MENGE</b>",
            'PREIS' => "<b>NETTO</b>",
            'MWST_SATZ' => "<b>MWST</b>",
            'RABATT_SATZ' => "<b>RABATT</b>",
            'SKONTO' => "<b>SKONTO</b>",
            'GESAMT_NETTO' => "<b>GESAMT</b>"
        );

        /* Tabellenparameter */
        $tableoptions = array(

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
                'POS' => array(
                    'justification' => 'left',
                    'width' => 25
                ),
                'ARTIKEL_NR' => array(
                    'justification' => 'left',
                    'width' => 70
                ),
                'BEZ' => array(
                    'justification' => 'left',
                    'width' => 130
                ),
                'MENGE' => array(
                    'justification' => 'right',
                    'width' => 50
                ),
                'PREIS' => array(
                    'justification' => 'right',
                    'width' => 50
                ),
                'MWST_SATZ' => array(
                    'justification' => 'right',
                    'width' => 50
                ),
                'RABATT_SATZ' => array(
                    'justification' => 'right',
                    'width' => 50
                ),
                'SKONTO' => array(
                    'justification' => 'right',
                    'width' => 50
                ),
                'GESAMT_NETTO' => array(
                    'justification' => 'right',
                    'width' => 50
                )
            )

        );
        /* Ort und Datum */
        // $pdf->addText(474,560,10,"$p->partner_ort, $this->rechnungsdatum");

        /* Faltlinie */
        $pdf->setLineStyle(0.2);
        $pdf->line(5, 542, 20, 542);

        /* Schiftart wählen */
        // $pdf->selectFont("pdfclass/fonts/Courier.afm");
        // $pdf->selectFont("schriften/TSan3___.afm");

        $empfaenger_name = str_replace("<br>", " ", $this->rechnungs_empfaenger_name);
        $pdf->ezText("$empfaenger_name", 10);
        $pdf->ezText("$this->rechnungs_empfaenger_strasse $this->rechnungs_empfaenger_hausnr\n\n$this->rechnungs_empfaenger_plz $this->rechnungs_empfaenger_ort", 10);

        $pdf->ezSetDy(-50); // abstand
        /* Rechnungsnummer */
        $rechnungsnummer = ltrim(rtrim($this->rechnungsnummer));
        $pdf->ezText("$p->partner_ort, $this->rechnungsdatum", 10, array(
            'justification' => 'right'
        ));
        if ($this->rechnungstyp != 'Angebot') {
            $pdf->ezText("Fällig: $this->faellig_am", 10, array(
                'justification' => 'right'
            ));
        }
        $pdf->ezText("<b>$this->rechnungstyp:\n$rechnungsnummer</b>", 12);
        /* Fälligkeit */
        // $pdf->addText(475,550,10,"Fällig: $r->faellig_am");

        $pdf->ezSetDy(-30); // abstand
        /* Kurzbeschreibung */
        $kurzbeschreibung = str_replace(",", ", ", $this->kurzbeschreibung);
        $kurzbeschreibung = str_replace("<br>", "\n", $kurzbeschreibung);
        $pdf->ezText("$kurzbeschreibung", 10, array(
            'justification' => 'full'
        ));

        $pdf->ezSetDy(-10); // abstand
        if ($this->rechnungstyp == 'Angebot') {
            $pdf->ezText("Sehr geehrte Damen und Herren,\n\nwir bedanken uns für Ihre Anfrage und übermitteln Ihnen hiermit unser Angebot, an das wir uns für vier Wochen ab Erstellungsdatum gebunden halten.\n", 9);
        }
        /* Tabelle ausgeben */
        $pdf->ezTable($tab_arr, $cols, "", $tableoptions);
        /* Zahlungshinweis bzw mit freudlichen Grössen usw vom Aussteller */
        // $zahlungshinweis_org = str_replace("<br>","\n",$bpdf->zahlungshinweis_org);
        // $pdf->ezText("$zahlungshinweis_org", 10);

        if ($this->check_abschlag($beleg_nr) == true) {
            $pdf->ezSetDy(-10); // abstand
            $pdf->ezText("<b>Rechnungsaufstellung</b>", 9, array(
                'justification' => 'full'
            ));
            $pdf->ezSetDy(-5); // abstand
            $this->rechnungsaufstellung_teil_rg($pdf, $beleg_nr);
        }

        if ($this->check_abschlag($beleg_nr) == false && $this->rechnungstyp == 'Schlussrechnung') {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\InfoMessage('PDF-Ansicht nicht möglich, erst Teilrechnungen zu dieser Schlussrechnung wählen.')
            );
        }

        if ($this->rechnungstyp != 'Angebot') {
            /* Zahlungshinweis bzw mit freudlichen Grössen usw vom Aussteller */
            $zahlungshinweis_org = str_replace("<br>", "\n", $bpdf->zahlungshinweis_org);
            $r_hinweis = "\n\nWir danken Ihnen für Ihren Auftrag und hören gern von Ihnen. \n";
            $r_hinweis .= "Die gelieferte Ware und die erbrachte Arbeitsleistung bleibt bis zur vollständigen Bezahlung unser Eigentum. ";
            $r_hinweis .= "Lt. Gesetzgeber sind wir zu dem Hinweis verpflichtet: Die gesetzliche Aufbewahrungspflicht für diese Rechnung beträgt für Privatpersonen 2 Jahre / Unternehmen gemäß der gesetzlichen Bestimmungen. Die Aufbewahrungsfrist beginnt mit dem Schluß dieses Kalenderjahres.";
            $r_hinweis .= "\n\n$zahlungshinweis_org";
        } else {
            $r_hinweis .= "Im Auftragsfall bitten wir um eine schriftliche Bestätigung.";
        }

        eval ("\$r_hinweis = \"$r_hinweis\";");; // Variable ausm Text füllen
        $pdf->ezText("$r_hinweis", 8, array(
            'justification' => 'full'
        ));
        /* Seitennummerierung beenden */
        // $pdf->ezStopPageNumbers();
        /* Ausgabepuffer leeren */
    }

    /* Urpsrungsrechnungen ermitteln OKAY aber ohne ausgangsrechnungen */

    function rechnungs_positionen_arr($belegnr)
    {
        $result = DB::select("SELECT *, FORMAT((MENGE*PREIS/100)*(100-RABATT_SATZ),2) AS GESAMT_NETTO1 FROM RECHNUNGEN_POSITIONEN WHERE BELEG_NR='$belegnr' && AKTUELL='1' ORDER BY POSITION ASC");
        $this->anzahl_positionen = count($result);
        return $result;
    } // nd function

    /* Folgesrechnungen ermitteln OKAY aber ohne ausgangsrechnungen */

    function artikel_info($partner_id, $artikel_nr)
    {
        $result = DB::select("SELECT * FROM POSITIONEN_KATALOG WHERE ART_LIEFERANT='$partner_id' && ARTIKEL_NR = '$artikel_nr' && AKTUELL='1' ORDER BY KATALOG_DAT DESC LIMIT 0,1");
        if (empty($result)) {
            return false;
        } else {
            return $result;
        }
        // }
    } // end function

    function get_ueberschrift($beleg_nr, $pos)
    {
        $result = DB::select("SELECT UEBERSCHRIFT FROM POS_GRUPPE WHERE BELEG_NR='$beleg_nr' && POS='$pos' && AKTUELL = '1' ORDER BY B_DAT DESC");
        if (!empty($result[0])) {
            echo $result[0]['UEBERSCHRIFT'];
            return $result[0]['UEBERSCHRIFT'];
        } else {
            return '';
        }
    }

    function check_abschlag($beleg_nr)
    {
        $result = DB::select("SELECT DAT FROM RECHNUNGEN_SCHLUSS WHERE SCHLUSS_R_ID='$beleg_nr' && AKTUELL='1' LIMIT 0,1");
        return !empty($result);
    }

    function rechnungsaufstellung_teil_rg($pdf, $beleg_nr)
    {
        $print_invoice = \App\Models\Invoice::findOrFail($beleg_nr);
        $invoices = $print_invoice
            ->advancePaymentInvoices()
            ->where('RECHNUNGSDATUM', '<=', $print_invoice->RECHNUNGSDATUM)
            ->orderBy('RECHNUNGSDATUM')
            ->get();
        if (!$invoices->isEmpty()) {

            if ($print_invoice->RECHNUNGSTYP === 'Schlussrechnung') {
                $pdf->ezSetDy(-10); // abstand
                $pdf->ezText("<b>Abschlagszahlungen</b>", 9, array(
                    'justification' => 'full'
                ));
                $pdf->ezSetDy(-10); // abstand
            }

            $summe_netto = 0;
            $summe_brutto = 0;
            $summe_mwst = 0;
            $summe_skontiert = 0;
            $summe_skonto = 0;
            $last_invoice = $invoices->pop();
            $lines = collect();

            if (!$invoices->isEmpty()) {
                $lines->prepend([
                    'RDATUM' => 'Abschläge',
                ]);
            }
            foreach ($invoices as $invoice) {
                $summe_netto += $invoice->NETTO;
                $summe_mwst += $invoice->BRUTTO - $invoice->NETTO;
                $summe_brutto += $invoice->BRUTTO;
                $skonto = $invoice->BRUTTO - $invoice->SKONTOBETRAG;
                $summe_skontiert += $invoice->SKONTOBETRAG;
                $summe_skonto += $skonto;
                $lines->push([
                    'RDATUM' => date_mysql2german($invoice->RECHNUNGSDATUM),
                    'RNR' => "<b>$invoice->RECHNUNGSNUMMER</b>",
                    'NETTO' => nummer_punkt2komma_t(-1 * $invoice->NETTO) . " € ",
                    'MWST' => nummer_punkt2komma_t(-1 * ($invoice->BRUTTO - $invoice->NETTO)) . " € ",
                    'BRUTTO' => nummer_punkt2komma_t(-1 * $invoice->BRUTTO) . " € ",
                    'SKONTO' => nummer_punkt2komma_t($skonto) . " € ",
                    'SKONTIERT' => nummer_punkt2komma_t(-1 * $invoice->SKONTOBETRAG) . " € "
                ]);
            }

            if (!$invoices->isEmpty()) {
                $lines->prepend([
                    'RDATUM' => '_______________',
                    'RNR' => '_________________',
                    'NETTO' => '________________',
                    'MWST' => '________________',
                    'BRUTTO' => '________________',
                    'SKONTO' => '________________',
                    'SKONTIERT' => '________________'
                ]);
            }

            if ($last_invoice->RECHNUNGSTYP == 'Schlussrechnung') {
                $lines->prepend([
                    'RDATUM' => date_mysql2german($last_invoice->RECHNUNGSDATUM),
                    'RNR' => "<b>$last_invoice->RECHNUNGSNUMMER</b>",
                    'NETTO' => nummer_punkt2komma_t($last_invoice->NETTO) . " € ",
                    'MWST' => nummer_punkt2komma_t($last_invoice->BRUTTO - $last_invoice->NETTO) . " € ",
                    'BRUTTO' => nummer_punkt2komma_t($last_invoice->BRUTTO) . " € ",
                    'SKONTO' => nummer_punkt2komma_t(-1 * ($last_invoice->BRUTTO - $last_invoice->SKONTOBETRAG)) . " € ",
                    'SKONTIERT' => "<b>" . nummer_punkt2komma_t($last_invoice->SKONTOBETRAG) . " € </b>"
                ]);
            } else {
                $lines->prepend([
                    'RDATUM' => date_mysql2german($last_invoice->RECHNUNGSDATUM),
                    'RNR' => "<b>$last_invoice->RECHNUNGSNUMMER</b>",
                    'NETTO' => nummer_punkt2komma_t($summe_netto + $last_invoice->NETTO) . " € ",
                    'MWST' => nummer_punkt2komma_t($summe_mwst + ($last_invoice->BRUTTO - $last_invoice->NETTO)) . " € ",
                    'BRUTTO' => nummer_punkt2komma_t($summe_brutto + $last_invoice->BRUTTO) . " € ",
                    'SKONTO' => nummer_punkt2komma_t(-1 * ($summe_skonto + ($last_invoice->BRUTTO - $last_invoice->SKONTOBETRAG))) . " € ",
                    'SKONTIERT' => "<b>" . nummer_punkt2komma_t($summe_skontiert + $last_invoice->SKONTOBETRAG) . " € </b>"
                ]);
            }

            $lines->push([
                'RDATUM' => '==============',
                'RNR' => '================',
                'NETTO' => '===============',
                'MWST' => '===============',
                'BRUTTO' => '===============',
                'SKONTO' => '===============',
                'SKONTIERT' => '==============='
            ]);

            if ($last_invoice->RECHNUNGSTYP == 'Schlussrechnung') {
                $rest_netto = $last_invoice->NETTO - $summe_netto;
                $rest_brutto = $last_invoice->BRUTTO - $summe_brutto;
                $rest_mwst = $rest_brutto - $rest_netto;
                $rest_skonto = $last_invoice->SKONTOBETRAG - $last_invoice->BRUTTO + $summe_skonto;
                $rest_skontiert = $rest_brutto + $rest_skonto;
            } else {
                $rest_netto = $last_invoice->NETTO;
                $rest_brutto = $last_invoice->BRUTTO;
                $rest_mwst = $rest_brutto - $rest_netto;
                $rest_skonto = $last_invoice->SKONTOBETRAG - $last_invoice->BRUTTO;
                $rest_skontiert = $rest_brutto + $rest_skonto;
            }

            $lines->push([
                'RDATUM' => "<b>Restforderung</b>",
                'NETTO' => nummer_punkt2komma_t($rest_netto) . " € ",
                'MWST' => nummer_punkt2komma_t($rest_mwst) . " € ",
                'BRUTTO' => nummer_punkt2komma_t($rest_brutto) . " € ",
                'SKONTO' => nummer_punkt2komma_t($rest_skonto) . " € ",
                'SKONTIERT' => "<b>" . nummer_punkt2komma_t($rest_skontiert) . " € </b>"
            ]);

            $cols = array(
                'RDATUM' => "<b>Datum</b>",
                'RNR' => "<b>Rechnungsnr.</b>",
                'NETTO' => "<b>Netto</b>",
                'MWST' => "<b>MwSt.</b>",
                'BRUTTO' => "<b>Brutto</b>",
                'SKONTO' => "<b>Skonto</b>",
                'SKONTIERT' => "<b>Skontiert</b>"
            );

            /* Tabellenparameter */
            $tableoptions = array(

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
                    'RDATUM' => array(
                        'justification' => 'left',
                        'width' => 70
                    ),
                    'RNR' => array(
                        'justification' => 'left',
                        'width' => 80
                    ),
                    'NETTO' => array(
                        'justification' => 'right',
                        'width' => 75
                    ),
                    'BRUTTO' => array(
                        'justification' => 'right',
                        'width' => 75
                    ),
                    'MWST' => array(
                        'justification' => 'right',
                        'width' => 75
                    ),
                    'SKONTO' => array(
                        'justification' => 'right',
                        'width' => 75
                    ),
                    'SKONTIERT' => array(
                        'justification' => 'right',
                        'width' => 75
                    )
                )

            );

            $pdf->ezTable($lines->all(), $cols, "", $tableoptions);
        }
    }

    function rechnung_anzeigen($beleg_nr)
    {
        $this->rechnung_grunddaten_holen($beleg_nr);
        /* Prüfen ob Rechnung vorhanden */
        if (!$this->rechnungsnummer) {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\ErrorMessage("Die Rechnung exisitiert nicht.")
            );
        }

        /* Partnerinformationen einholen */
        $p = new partners ();
        $p->get_partner_info($this->rechnung_aussteller_partner_id);

        /* Eigene PDF-Klasse laden */
        /* Neues PDF-Objekt erstellen */
        $pdf = new Cezpdf ('a4', 'portrait');
        /* Neue Instanz von b_pdf */
        $bpdf = new b_pdf ();
        /* Header und Footer des Rechnungsaustellers in alle PDF-Seiten laden */
        $bpdf->b_header($pdf, 'Partner', $this->rechnung_aussteller_partner_id, 'portrait', 'Helvetica.afm', 6);

        $table_arr = $this->rechnungs_positionen_arr($beleg_nr);
        $anz = $this->anzahl_positionen;
        $g_netto = 0;
        $new_pos = 0;
        for ($index = 0; $index < $anz; $index++) {
            $menge = $table_arr [$index] ['MENGE'];
            $preis = $table_arr [$index] ['PREIS'];
            $artikel_nr = $table_arr [$index] ['ARTIKEL_NR'];
            $lieferant_id = $table_arr [$index] ['ART_LIEFERANT'];

            /* Infos aus Katalog zu Artikelnr */
            $artikel_info_arr = $this->artikel_info($lieferant_id, $artikel_nr);
            $bezeichnung = '';
            $v_einheit = '';
            for ($i = 0; $i < count($artikel_info_arr); $i++) {
                if (!empty ($artikel_info_arr [$i] ['BEZEICHNUNG'])) {
                    $bezeichnung = str_replace("<br>", "\n", $artikel_info_arr [$i] ['BEZEICHNUNG']);
                    $v_einheit = $artikel_info_arr [$i] ['EINHEIT'];
                } else {
                    $bezeichnung = 'Unbekannt';
                    $v_einheit = '';
                }
            }

            $artikel_nr = $table_arr [$index] ['ARTIKEL_NR'];
            $mwst_satz = $table_arr [$index] ['MWST_SATZ'];
            $skonto_satz = $table_arr [$index] ['SKONTO'];
            $rabatt_satz = $table_arr [$index] ['RABATT_SATZ'];
            $gesamt_preis = $table_arr [$index] ['GESAMT_NETTO'];
            $pos = $table_arr [$index] ['POSITION'];

            // echo "$beleg_nr $pos<br>";
            if ($this->get_ueberschrift($beleg_nr, $pos)) {
                $ueb = $this->get_ueberschrift($beleg_nr, $pos);
                $tab_arr [$new_pos] ['BEZ'] = "\n" . '<b>' . $ueb . '</b>';
                // $tab_arr[$index]['ARTIKEL_NR'] = '<b>'.$this->get_ueberschrift($beleg_nr, $pos).'</b>';
                $new_pos++;
            }

            $tab_arr [$new_pos] ['POSITION'] = $pos;
            $tab_arr [$new_pos] ['ARTIKEL_NR'] = $artikel_nr;
            $tab_arr [$new_pos] ['BEZ'] = $bezeichnung;
            $tab_arr [$new_pos] ['MENGE'] = nummer_punkt2komma($menge) . " $v_einheit";
            $tab_arr [$new_pos] ['PREIS'] = nummer_punkt2komma_t($preis);
            $tab_arr [$new_pos] ['MWST_SATZ'] = nummer_punkt2komma($mwst_satz) . '%';
            $tab_arr [$new_pos] ['RABATT_SATZ'] = nummer_punkt2komma($rabatt_satz) . '%';
            $tab_arr [$new_pos] ['SKONTO'] = nummer_punkt2komma_t($skonto_satz) . '%';
            $tab_arr [$new_pos] ['GESAMT_NETTO'] = nummer_punkt2komma_t($gesamt_preis) . ' €';
            $g_netto += $gesamt_preis;
            $tab_arr [$new_pos] ['SUMM_NETTO'] = nummer_punkt2komma_t($g_netto);
            /* Linien und Netto, Brutto usw in den Tabellenarray hinzufügen */
            if ($index == $anz - 1) {
                $tab_arr [$new_pos + 1] ['POSITION'] = '===';
                $tab_arr [$new_pos + 1] ['ARTIKEL_NR'] = '=============';
                $tab_arr [$new_pos + 1] ['BEZ'] = '==========================';
                $tab_arr [$new_pos + 1] ['MENGE'] = '==========';
                $tab_arr [$new_pos + 1] ['PREIS'] = '==========';
                $tab_arr [$new_pos + 1] ['MWST_SATZ'] = '==========';
                $tab_arr [$new_pos + 1] ['RABATT_SATZ'] = '==========';
                $tab_arr [$new_pos + 1] ['SKONTO'] = '==========';
                $tab_arr [$new_pos + 1] ['GESAMT_NETTO'] = '==========';
                $tab_arr [$new_pos + 2] ['SKONTO'] = '<b>Netto</b>';
                // $tab_arr[$new_pos+2]['GESAMT_NETTO'] = '<b>'.nummer_punkt2komma($g_netto).' €</b>'.$this->rechungs_netto;
                $tab_arr [$new_pos + 2] ['GESAMT_NETTO'] = '<b>' . nummer_punkt2komma_t($this->rechnungs_netto) . ' €</b>';
                $tab_arr [$new_pos + 3] ['SKONTO'] = '<b>MWSt</b>';
                // $this->rechnungs_mwst = $this->rechnungs_brutto - $this->rechnungs_netto;
                $tab_arr [$new_pos + 3] ['GESAMT_NETTO'] = '<b>' . nummer_punkt2komma_t($this->rechnungs_mwst) . ' €</b>';
                $tab_arr [$new_pos + 4] ['SKONTO'] = '<b>Brutto</b>';
                $tab_arr [$new_pos + 4] ['GESAMT_NETTO'] = '<b>' . nummer_punkt2komma_t($this->rechnungs_brutto) . " €</b>";
                $tab_arr [$new_pos + 5] ['SKONTO'] = '<b>Skonto</b>';
                $tab_arr [$new_pos + 5] ['GESAMT_NETTO'] = '<b>' . nummer_punkt2komma_t($this->rechnungs_skontoabzug) . ' €</b>';
                $tab_arr [$new_pos + 6] ['SKONTO'] = '<b>Skontiert</b>';
                $tab_arr [$new_pos + 6] ['GESAMT_NETTO'] = '<b>' . nummer_punkt2komma_t($this->rechnungs_skontobetrag) . ' €</b>';
            }
            $new_pos++;
        }

        /* Spaltendefinition */
        $cols = array(
            'POSITION' => "<b>Pos.</b>",
            'ARTIKEL_NR' => "<b>Artikelnr.</b>",
            'BEZ' => "<b>Bezeichnung</b>",
            'MENGE' => "<b>Menge</b>",
            'PREIS' => "<b>Netto</b>",
            'MWST_SATZ' => "<b>MwSt.</b>",
            'RABATT_SATZ' => "<b>Rabatt</b>",
            'SKONTO' => "<b>Skonto</b>",
            'GESAMT_NETTO' => "<b>Gesamt</b>"
        );

        /* Tabellenparameter */
        $tableoptions = array(

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
                'POSITION' => array(
                    'justification' => 'right',
                    'width' => 20
                ),
                'ARTIKEL_NR' => array(
                    'justification' => 'left',
                    'width' => 65
                ),
                'BEZ' => array(
                    'justification' => 'left',
                    'width' => 130
                ),
                'MENGE' => array(
                    'justification' => 'right',
                    'width' => 50
                ),
                'PREIS' => array(
                    'justification' => 'right',
                    'width' => 50
                ),
                'MWST_SATZ' => array(
                    'justification' => 'right',
                    'width' => 50
                ),
                'RABATT_SATZ' => array(
                    'justification' => 'right',
                    'width' => 50
                ),
                'SKONTO' => array(
                    'justification' => 'right',
                    'width' => 50
                ),
                'GESAMT_NETTO' => array(
                    'justification' => 'right',
                    'width' => 50
                )
            )

        );

        /* Faltlinie */
        $pdf->setLineStyle(0.2);
        $pdf->line(5, 542, 20, 542);

        $empfaenger_name = str_replace("<br>", " ", $this->rechnungs_empfaenger_name);
        $pdf->ezText("$empfaenger_name", 10);
        $pdf->ezText("$this->rechnungs_empfaenger_strasse $this->rechnungs_empfaenger_hausnr\n\n$this->rechnungs_empfaenger_plz $this->rechnungs_empfaenger_ort", 10);

        $pdf->ezSetDy(-50); // abstand
        /* Rechnungsnummer */
        $rechnungsnummer = ltrim(rtrim($this->rechnungsnummer));
        $pdf->ezText("$p->partner_ort, $this->rechnungsdatum", 10, array(
            'justification' => 'right'
        ));
        if ($this->rechnungstyp != 'Angebot') {
            $pdf->ezText("Fällig: $this->faellig_am", 10, array(
                'justification' => 'right'
            ));
        }

        $invoice = \App\Models\Invoice::findOrFail($beleg_nr);

        if ($invoice->servicetime_from && $invoice->servicetime_to) {
            $pdf->ezText("Leistungszeitraum: " . date_mysql2german($invoice->servicetime_from) . ' - ' . date_mysql2german($invoice->servicetime_to), 10, array(
                'justification' => 'right'
            ));
        } elseif ($invoice->servicetime_from) {
            $pdf->ezText("Leistungsdatum: " . date_mysql2german($invoice->servicetime_from), 10, array(
                'justification' => 'right'
            ));
        }

        $advance_payment_pos = '';
        if (!$invoice->advancePaymentInvoices->isEmpty() && $invoice->RECHNUNGSTYP != 'Schlussrechnung') {
            $advance_payment_pos = $invoice->advancePaymentInvoices()
                    ->orderBy('RECHNUNGSDATUM')
                    ->get()
                    ->pluck('BELEG_NR')
                    ->search($invoice->BELEG_NR) + 1 . '. Abschlagsrechnung';
            $pdf->ezText("<b>$advance_payment_pos:\n$rechnungsnummer</b>", 12);
        } else {
            $pdf->ezText("<b>$advance_payment_pos$this->rechnungstyp:\n$rechnungsnummer</b>", 12);
        }

        $pdf->ezSetDy(-30); // abstand
        /* Kurzbeschreibung */
        $kurzbeschreibung = str_replace("<br>", "\n", $this->kurzbeschreibung);
        $pdf->ezText("$kurzbeschreibung", 8, array(
            'justification' => 'full'
        ));

        $pdf->ezSetDy(-10); // abstand
        if ($this->rechnungstyp == 'Angebot') {
            $pdf->ezText("Sehr geehrte Damen und Herren,\n\nwir bedanken uns für Ihre Anfrage und übermitteln Ihnen hiermit unser Angebot, an das wir uns für vier Wochen ab Erstellungsdatum gebunden halten.\n", 9);
        }

        if ($invoice->RECHNUNGSTYP !== 'Teilrechnung') {
            /* Tabelle ausgeben */
            $pdf->ezTable($tab_arr, $cols, "", $tableoptions);
        }

        if (in_array($invoice->RECHNUNGSTYP, ['Schlussrechnung', 'Teilrechnung'])) {
            $this->rechnungsaufstellung_teil_rg($pdf, $beleg_nr);
        }

        if ($this->rechnungstyp != 'Angebot') {
            /* Zahlungshinweis bzw mit freudlichen Grüßen usw vom Aussteller */
            $zahlungshinweis_org = str_replace("<br>", "\n", $bpdf->zahlungshinweis_org);
            $r_hinweis .= "\nWir danken Ihnen für Ihren Auftrag!\nDie gelieferte Ware und die erbrachte Arbeitsleistung bleiben bis zur vollständigen Begleichung der Rechnung unser Eigentum. ";
            $r_hinweis .= "Die gesetzliche Aufbewahrungspflicht für diese Rechnung beträgt für Privatpersonen zwei Jahre und für Unternehmen zehn Jahre.\nDie Aufbewahrungsfrist beginnt mit dem Schluss des Kalenderjahres, in dem die Rechnung ausgestellt worden ist.";
            $r_hinweis .= "\n\n$zahlungshinweis_org";
        } else {
            $r_hinweis .= "Im Auftragsfall bitten wir um eine schriftliche Bestätigung.";
        }

        eval ("\$r_hinweis = \"$r_hinweis\";");; // Variable ausm Text füllen
        $pdf->ezText("$r_hinweis", 8, array(
            'justification' => 'full'
        ));
        /* Seitennummerierung beenden */
        // $pdf->ezStopPageNumbers();
        /* Ausgabepuffer leeren */

        ob_end_clean();
        /* PDF-Ausgabe */

        $pdf_opt ['Content-Disposition'] = $rechnungsnummer . "_" . $this->rechnungstyp . "_" . str_replace(" ", "_", $this->rechnungs_aussteller_name . ".pdf");
        $pdf->ezStream($pdf_opt);
    }

    function pool_liste_wahl()
    {
        $kos_arr = $this->get_pool_partner_arr();
        if (!empty($kos_arr)) {
            $anz = count($kos_arr);
            for ($a = 0; $a < $anz; $a++) {
                $kos_typ = $kos_arr [$a] ['KOS_TYP'];
                $kos_id = $kos_arr [$a] ['KOS_ID'];
                $aussteller_typ = $kos_arr [$a] ['AUSSTELLER_TYP'];
                $aussteller_id = $kos_arr [$a] ['AUSSTELLER_ID'];
                $r = new rechnung ();
                $kos_bez = $r->kostentraeger_ermitteln($kos_typ, $kos_id);
                $aus_bez = $r->kostentraeger_ermitteln($aussteller_typ, $aussteller_id);
                $link = "<a href='" . route('web::rechnungen::legacy', ['option' => 'u_pool_edit', 'kos_typ' => $kos_typ, 'kos_id' => $kos_id, 'aussteller_typ' => $aussteller_typ, 'aussteller_id' => $aussteller_id]) . "'>$aus_bez an $kos_bez";
                echo "$link<br>";
            }
        }
    }

    function get_pool_partner_arr()
    {
        $result = DB::select("SELECT * FROM POS_POOL WHERE AKTUELL = '1' GROUP BY KOS_TYP, KOS_ID, AUSSTELLER_TYP, AUSSTELLER_ID");
        return $result;
    }

    function u_pool_edit($kos_typ, $kos_id, $aussteller_typ, $aussteller_id)
    {
        $r = new rechnung ();
        $kos_bez = $r->kostentraeger_ermitteln($kos_typ, $kos_id);
        echo $kos_bez;
        $pos_arr = $this->get_pool_pos_arr($kos_typ, $kos_id, $aussteller_typ, $aussteller_id);
        if (!empty($pos_arr)) {
            $anz = count($pos_arr);
            $js_prozent_spalte_plus = "<a onclick=\"spalte_prozent('+','V_PREIS')\">+%</a>";
            $js_prozent_spalte_minus = "<a onclick=\"spalte_prozent('-','V_PREIS')\">-%</a>";

            echo "<table class=\"bordered\">";
            echo "<thead>";
            echo "<tr><th>P</th><th>ARTNR</th><th>TEXT</th><th>EINHEIT</th><th>MENGE</th><th>EPREIS</th><th>$js_prozent_spalte_plus|$js_prozent_spalte_minus VPREIS</th><th>MWST</th><th>RABATT</th><th>SKONTO</th><th>GPREIS</th></tr>";
            echo "</thead>";
            $pool_id_temp = '';
            $summ = 0;
            $pool_sum = 0;
            for ($a = 0; $a < $anz; $a++) {

                $virt_pos = $a + 1;
                $pool_id = $pos_arr [$a] ['POOL_ID'];
                if ($pool_id_temp != $pool_id) {
                    $pool_id_temp = $pool_id;
                    $pool_name = $this->get_pool_bez($pool_id);
                    $js_prozent_spalte_pool = "<a onclick=\"spalte_prozent_pool('$pool_id','V_PREIS')\">POOL%</a>";
                    $js_einheitspreis_spalte_pool = "<a onclick=\"spalte_einheitspreis_pool('$pool_id','V_PREIS')\">VP POOL €</a>";
                    $js_einheitspreis_rabatt_pool = "<a onclick=\"spalte_einheitspreis_pool('$pool_id','RABATT_SATZ')\">RABATT</a>";
                    $js_einheitspreis_skonto_pool = "<a onclick=\"spalte_einheitspreis_pool('$pool_id','SKONTO')\">SKONTO</a>";
                    $js_einheitspreis_mwst_pool = "<a onclick=\"spalte_einheitspreis_pool('$pool_id','MWST_SATZ')\">MWST</a>";
                    if ($a != 0) {
                        $pool_sum_a = nummer_punkt2komma($pool_sum);
                        echo "<tr><td colspan=\"9\"></td><td><b>SUMME POOL </b></td><td><b>$pool_sum_a</b></td></tr>";
                        $pool_sum = 0;
                    }
                    echo "<tr><th>";
                    $f = new formular ();
                    $js_c = '';
                    $f->check_box_js1('pool_ids[]', 'pool_' . $pool_id, $pool_id, 'R', '', $js_c);

                    echo "</th><th colspan=\"4\"><b>$pool_name</b></th><th>$js_prozent_spalte_pool</th><th>$js_einheitspreis_spalte_pool</th><th>$js_einheitspreis_mwst_pool</th><th>$js_einheitspreis_rabatt_pool</th><th>$js_einheitspreis_skonto_pool</th><th></th></tr>";
                }
                $menge = $pos_arr [$a] ['MENGE'];
                $epreis = $pos_arr [$a] ['EINZEL_PREIS'];
                $vpreis = $pos_arr [$a] ['V_PREIS'];
                $aufschlag_prozente = nummer_punkt2komma_t($pos_arr [$a] ['PROZENTE']);
                $pos = $pos_arr [$a] ['POS'];
                $gsumme = $pos_arr [$a] ['G_SUMME'];
                $summ += $gsumme;
                $mwst = $pos_arr [$a] ['MWST_SATZ'];
                $rabatt = $pos_arr [$a] ['RABATT_SATZ'];
                $skonto = $pos_arr [$a] ['SKONTO'];
                $pp_dat = $pos_arr [$a] ['PP_DAT'];
                $img_oben = "<img src=\"images/p_oben.jpg\" onclick=\"up($pp_dat,$pos,'pool_tab', '$kos_typ', '$kos_id', '$pool_id')\">";
                $img_unten = "<img src=\"images/p_unten.jpg\" onclick=\"down($pp_dat,$pos, 'pool_tab','$kos_typ', '$kos_id', '$pool_id')\">";
                echo "<tr id=\"anker_$virt_pos\">";
                $u_beleg_nr = $pos_arr [$a] ['U_BELEG_NR'];
                $u_pos = $pos_arr [$a] ['U_POS'];
                $art_info = $this->get_position($u_beleg_nr, $u_pos);
                $art_nr = $art_info ['ARTIKEL_NR'];
                $this->get_letzen_preis_aus_rg($art_nr, $aussteller_typ, $aussteller_id, $kos_typ, $kos_id);
                $art_nr_link = "<a href='" . route('web::rechnungen.show', ['id' => $u_beleg_nr]) . "' target=\"_blank\">$art_nr</a>";
                $art_nr_link1 = "<a href='" . route('web::rechnungen.show', ['id' => $this->v_beleg_nr]) . "' target=\"_blank\">$this->v_beleg_nr</a>";
                $art_lieferant = $art_info ['ART_LIEFERANT'];
                $farbe = "black";
                if (isset ($this->v_preis)) {
                    if (ltrim(rtrim($vpreis)) == nummer_komma2punkt(substr(ltrim(rtrim($this->v_preis)), 0, -1))) {
                        $farbe = "green";
                    } else {
                        $farbe = "red";
                    }
                }
                $katalog_info = $this->artikel_info($art_lieferant, $art_nr);
                $text = $katalog_info [0] ['BEZEICHNUNG'];
                $js_text = "onclick=\"change_text('$art_nr', '$art_lieferant', '$text', '$virt_pos')\"";
                $ve = $katalog_info [0] ['EINHEIT'];
                $js_vpreis = "onclick=\"change_zeile('V_PREIS', $vpreis, '$pp_dat')\"";
                $js_vpreis_prozent = "<a onclick=\"aufpreis('V_PREIS', '$pp_dat')\">$aufschlag_prozente %</a>";
                $js_menge = "onclick=\"change_zeile('MENGE', $menge,'$pp_dat')\"";
                $js_rabatt = "onclick=\"change_zeile('RABATT_SATZ', $rabatt, '$pp_dat')\"";
                $js_skonto = "onclick=\"change_zeile('SKONTO', $skonto, '$pp_dat')\"";
                $js_mwst = "onclick=\"change_zeile('MWST_SATZ', $mwst,'$pp_dat')\"";
                $js_pos = "onclick=\"change_zeile('POS', $pos,'$pp_dat')\"";
                echo "<td>";

                echo $img_oben . '';
                echo $img_unten;

                echo "<hr><b $js_pos>$pos</b></td><td>$art_nr_link";
                // echo "<br>Zurück";
                $js_back = "onclick=\"back2pool('$pp_dat');\"";
                $f->button_js('btn_back_p', 'Zurück', $js_back);
                echo "</td><td $js_text>$text</td><td>$ve</td><td $js_menge>$menge</td><td>$epreis</td><td><p $js_vpreis>$vpreis EUR</p><hr>$js_vpreis_prozent<hr><font color=\"$farbe\"><b>$this->anz_preise $this->v_preis $this->v_rabatt_satz $art_nr_link1</b></font></td><td $js_mwst>";
                /*
				 * unset($this->anz_preise);
				 * unset($this->v_preis);
				 * unset($this->v_rabatt_satz);
				 * unset($this->v_beleg_nr);
				 */

                if ($mwst == '0.00') {
                    $mwst = "<font color=\"red\"><b>$mwst</b></font>";
                }
                if ($skonto == '0.00') {
                    $skonto = "<font color=\"red\"><b>$skonto</b></font>";
                }
                $pool_sum += $gsumme;
                echo "$mwst</td><td $js_rabatt>$rabatt</td><td $js_skonto>$skonto</td><td>$gsumme</td></tr>";
                if ($a == $anz - 1) {
                    $pool_sum_a = nummer_punkt2komma($pool_sum);
                    echo "<tr><td colspan=\"9\"></td><td><b>SUMME POOL </b></td><td><b>$pool_sum_a</b></td></tr>";
                    $pool_sum = 0;
                }
            }
            $summ_a = nummer_punkt2komma($summ);
            echo "<tr><th colspan=\"9\"></th><th>SUMME</th><th>$summ_a</th></tr>";
            echo "<tr><td colspan=\"11\">";
            $f = new formular ();
            // $js = "onclick=\"u_pool_rechnung('$kos_typ', '$kos_id', '$aussteller_typ','$aussteller_id')\"";
            $f->datum_feld('Leistungsanfang', 'servicetime_from', "", 'servicetime_from');
            $f->datum_feld('Leistungsende', 'servicetime_to', "", 'servicetime_to');

            $ge = new geldkonto_info ();
            $ge->dropdown_geldkonten_k('Empfangsgeldkonto waehlen', 'gk_id', 'gk_id', $aussteller_typ, $aussteller_id);
            // $f->button_js('r_send', 'Rechnung erstellen', $js);
            $js_t = "onclick=\"u_pool_rechnung_pool_wahl('pool_ids[]', '$kos_typ', '$kos_id', '$aussteller_typ','$aussteller_id')\"";
            $f->button_js('r_send1', 'Rechnung erstellen', $js_t);
            echo "</td></tr>";
            echo "</table>";
        } else {
            echo "NO ARR";
        }
    }

    function get_pool_pos_arr($kos_typ, $kos_id, $aussteller_typ, $aussteller_id)
    {
        $result = DB::select("SELECT *, (V_PREIS-EINZEL_PREIS)/(EINZEL_PREIS/100) AS PROZENTE FROM POS_POOL WHERE AKTUELL = '1' && KOS_TYP='$kos_typ' && KOS_ID='$kos_id' && AUSSTELLER_TYP='$aussteller_typ' && AUSSTELLER_ID='$aussteller_id' ORDER BY POOL_ID, POS, U_BELEG_NR, U_POS ASC");
        return $result;
    }

    function get_pool_bez($pool_id)
    {
        $result = DB::select("SELECT POOL_NAME  FROM  POS_POOLS WHERE ID='$pool_id' LIMIT 0,1");
        if (!empty($result)) {
            $row = $result[0];
            return $row ['POOL_NAME'];
        } else {
            return 'POOL UNBEKANNT';
        }
    }

    function get_position($belegnr, $pos)
    {
        $result = DB::select("SELECT * FROM RECHNUNGEN_POSITIONEN WHERE BELEG_NR='$belegnr' && POSITION='$pos' && AKTUELL='1' ORDER BY RECHNUNGEN_POS_DAT DESC LIMIT 0,1");
        if (!empty($result)) {
            $row = $result[0];
            return $row;
        }
    }

    function get_letzen_preis_aus_rg($art_nr, $aus_typ, $aus_id, $empf_typ, $empf_id)
    {
        unset ($this->anz_preise);
        unset ($this->v_preis);
        unset ($this->v_rabatt_satz);
        unset ($this->v_beleg_nr);
        $db_abfrage = "SELECT POSITION, `ARTIKEL_NR`, MENGE, `PREIS`, `RABATT_SATZ`, `GESAMT_NETTO`, t1.BELEG_NR  FROM `RECHNUNGEN_POSITIONEN` as t1, RECHNUNGEN as t2 WHERE `ARTIKEL_NR` = '$art_nr' && t1.BELEG_NR=t2.BELEG_NR && t2.EMPFAENGER_TYP='$empf_typ' && t2.EMPFAENGER_ID='$empf_id' && t1.AKTUELL='1' && t2.AKTUELL='1' GROUP BY PREIS, RABATT_SATZ";
        $result = DB::select($db_abfrage);
        $numrows = count($result);
        if ($numrows) {
            $this->anz_preise = '(' . $numrows . ')';
            $db_abfrage = "SELECT POSITION, `ARTIKEL_NR`, MENGE, `PREIS`, `RABATT_SATZ`, `GESAMT_NETTO`, t1.BELEG_NR  FROM `RECHNUNGEN_POSITIONEN` as t1, RECHNUNGEN as t2 WHERE `ARTIKEL_NR` = '$art_nr' && t1.BELEG_NR=t2.BELEG_NR && t2.EMPFAENGER_TYP='$empf_typ' && t2.EMPFAENGER_ID='$empf_id' && t1.AKTUELL='1' && t2.AKTUELL='1' GROUP BY PREIS, RABATT_SATZ ORDER BY t1.BELEG_NR DESC LIMIT 0,1";
            $result = DB::select($db_abfrage);
            $row = $result[0];
            $this->v_preis = nummer_punkt2komma($row ['PREIS']) . ' €';
            $this->v_rabatt_satz = nummer_punkt2komma($row ['RABATT_SATZ']) . ' %';
            $this->v_beleg_nr = $row ['BELEG_NR'];
        } else {
            $this->anz_preise = 0;
            $this->v_preis = nummer_punkt2komma(0.00) . ' €';
            $this->v_rabatt_satz = nummer_punkt2komma(0.00) . ' %';
            $this->v_beleg_nr = '';
        }
    }

    function artikel_text_update($art_nr, $lieferant_id, $text)
    {
        $dat = $this->get_last_artikel_dat($art_nr, $lieferant_id);
        $db_abfrage = "UPDATE POSITIONEN_KATALOG SET BEZEICHNUNG='$text' WHERE KATALOG_DAT='$dat' && ARTIKEL_NR='$art_nr' && ART_LIEFERANT='$lieferant_id' && AKTUELL='1'";
        DB::update($db_abfrage);
    }

    function get_last_artikel_dat($art_nr, $lieferant_id)
    {
        $result = DB::select("SELECT KATALOG_DAT FROM POSITIONEN_KATALOG WHERE ARTIKEL_NR='$art_nr' && ART_LIEFERANT='$lieferant_id' && AKTUELL='1' ORDER BY KATALOG_DAT DESC LIMIT 0,1");
        if (!empty($result)) {
            $row = $result[0];
            return $row ['KATALOG_DAT'];
        }
    }

    function erstelle_rechnung_u_pool($kos_typ, $kos_id, $aussteller_typ, $aussteller_id, $r_datum, $f_datum, $kurzinfo, $gk_id, $pool_ids_string, $servicetime_from, $servicetime_to)
    {
        $rr = new rechnung (); // aus berlussimo class

        if ($kos_typ == 'Partner') {
            $kos_typ_n = 'Partner';
            $kos_id_n = $kos_id;
        }

        if ($kos_typ == 'Objekt') {
            $kos_typ_n = 'Partner';
            $kos_id_n = $rr->eigentuemer_ermitteln('Objekt', $kos_id);
        }
        if ($kos_typ == 'Haus') {
            $kos_typ_n = 'Partner';
            $kos_id_n = $rr->eigentuemer_ermitteln('Haus', $kos_id);
        }
        if ($kos_typ == 'Einheit') {
            $kos_typ_n = 'Partner';
            $kos_id_n = $rr->eigentuemer_ermitteln('Einheit', $kos_id);
        }
        if ($kos_typ_n == $aussteller_typ && $aussteller_id == $kos_id_n) {
            $rechnungstyp = 'Buchungsbeleg';
        } else {
            $rechnungstyp = 'Rechnung';
        }
        $datum_arr = explode('.', $r_datum);
        $jahr = $datum_arr [2];

        $servicetime_from = $servicetime_from ? "'" . date_german2mysql($servicetime_from) . "'" : 'NULL';
        $servicetime_to = $servicetime_to ? "'" . date_german2mysql($servicetime_to) . "'" : 'NULL';

        $r = new rechnung ();
        $letzte_aussteller_rnr = $r->letzte_aussteller_ausgangs_nr($aussteller_id, $aussteller_typ, $jahr, $rechnungstyp);
        $letzte_aussteller_rnr = $letzte_aussteller_rnr + 1;
        $letzte_aussteller_rnr1 = sprintf('%03d', $letzte_aussteller_rnr);
        /* Kürzel */
        $rechnungsdatum_sql = date_german2mysql($r_datum);
        $rechnungsnummer = $this->rechnungs_kuerzel . ' ' . $letzte_aussteller_rnr1 . '-' . $jahr;

        /* Prüfen ob Rechnung vorhanden */
        $check_rechnung = $r->check_rechnung_vorhanden($rechnungsnummer, $rechnungsdatum_sql, $aussteller_typ, $aussteller_id, $kos_typ, $kos_id, $rechnungstyp);

        /* Wenn rechnung existiert */
        if ($check_rechnung) {
            fehlermeldung_ausgeben("Abbruch : $rechnungstyp mit der Nummer $rechnungsnummer existiert bereits.");
        } else {
            $letzte_empfaenger_rnr = $r->letzte_empfaenger_eingangs_nr($kos_id_n, $kos_typ_n, $jahr, $rechnungstyp);
            $letzte_empfaenger_rnr = $letzte_empfaenger_rnr + 1;
            /* Letzte Belegnummer holen */
            $letzte_belegnr = $r->letzte_beleg_nr();
            $letzte_belegnr = $letzte_belegnr + 1;
            $f_datum_sql = date_german2mysql($f_datum);
            $db_abfrage = "INSERT INTO RECHNUNGEN VALUES (NULL, '$letzte_belegnr', '$rechnungsnummer', '$letzte_aussteller_rnr', '$letzte_empfaenger_rnr', '$rechnungstyp', '$rechnungsdatum_sql','$rechnungsdatum_sql', '0.00','0.00','0.00', '$aussteller_typ', '$aussteller_id','$kos_typ_n', '$kos_id_n','1', '1', '1', '0', '1', '0', '0', '$f_datum_sql', '0000-00-00', '$kurzinfo', '$gk_id', NULL, $servicetime_from, $servicetime_to)";
            DB::insert($db_abfrage);

            /* Protokollieren */
            $last_dat = DB::getPdo()->lastInsertId();
            protokollieren('RECHNUNGEN', $last_dat, '0');
        }

        $pool_ids_arr = explode('|P|', $pool_ids_string);

        $anz = count($pool_ids_arr);
        unset ($pool_ids_arr [$anz - 1]);
        $anz = count($pool_ids_arr);
        if ($anz > 0) {

            $pool_id_temp = '';
            $v_pos = 0;
            for ($i = 0; $i < $anz; $i++) {
                $pool_id = $pool_ids_arr [$i];
                $pos_arr = $this->get_pool_pos_arr_bypos($kos_typ, $kos_id, $aussteller_typ, $aussteller_id, $pool_id);

                $anz_p = count($pos_arr);
                for ($a = 0; $a < $anz_p; $a++) {
                    $v_pos++;

                    if ($pool_id != $pool_id_temp) {
                        $pool_id_temp = $pool_id;
                        $pool_bez = $this->get_pool_bez($pool_id);
                        $this->insert_pool_bez_in_gruppe($pool_bez, $letzte_belegnr, $v_pos);
                    }

                    $u_beleg_nr = $pos_arr [$a] ['U_BELEG_NR'];
                    $u_pos = $pos_arr [$a] ['U_POS'];
                    // $lieferant_id ='';
                    // $artikel_nr = '';
                    $art_info = $this->get_position($u_beleg_nr, $u_pos);
                    $artikel_nr = $art_info ['ARTIKEL_NR'];
                    $lieferant_id = $art_info ['ART_LIEFERANT'];

                    $menge = $pos_arr [$a] ['MENGE'];
                    $preis = $pos_arr [$a] ['V_PREIS'];
                    $mwst = $pos_arr [$a] ['MWST_SATZ'];
                    $skonto = $pos_arr [$a] ['SKONTO'];
                    $rabatt = $pos_arr [$a] ['RABATT_SATZ'];
                    $g_netto = nummer_komma2punkt(nummer_punkt2komma($pos_arr [$a] ['G_SUMME']));
                    $pp_dat = $pos_arr [$a] ['PP_DAT'];
                    $this->position_speichern($letzte_belegnr, $u_beleg_nr, $lieferant_id, $artikel_nr, $menge, $preis, $mwst, $skonto, $rabatt, $g_netto);
                    $this->pool_pos_deaktivieren($pp_dat);
                }
                $this->pools_clean($pool_id);
            }
        }
    }

    function get_pool_pos_arr_bypos($kos_typ, $kos_id, $aussteller_typ, $aussteller_id, $pool_id)
    {
        $result = DB::select("SELECT *, (V_PREIS-EINZEL_PREIS)/(EINZEL_PREIS/100) AS PROZENTE FROM POS_POOL WHERE POOL_ID='$pool_id' && AKTUELL = '1' && KOS_TYP='$kos_typ' && KOS_ID='$kos_id' && AUSSTELLER_TYP='$aussteller_typ' && AUSSTELLER_ID='$aussteller_id' ORDER BY POS ASC");
        if (!empty($result)) {
            return $result;
        }
    }

    function insert_pool_bez_in_gruppe($pool_bez, $beleg_nr, $pos)
    {
        $last_b_id = last_id2('POS_GRUPPE', 'B_ID') + 1;
        $db_abfrage = "INSERT INTO POS_GRUPPE VALUES (NULL, '$last_b_id', '$beleg_nr', '$pos','$pool_bez','1')";
        DB::insert($db_abfrage);
    }

    function position_speichern($beleg_nr, $u_beleg_nr, $lieferant_id, $artikel_nr, $menge, $preis, $mwst, $skonto, $rabatt, $g_netto)
    {
        $r = new rechnung ();
        $letzte_rech_pos_id = $r->get_last_rechnung_pos_id();
        $letzte_rech_pos_id = $letzte_rech_pos_id + 1;

        $r2 = new rechnungen ();
        $last_pos = $r2->rechnung_last_position($beleg_nr);
        $last_pos = $last_pos + 1;

        $db_abfrage = "INSERT INTO RECHNUNGEN_POSITIONEN VALUES (NULL, '$letzte_rech_pos_id', '$last_pos', '$beleg_nr','$u_beleg_nr','$lieferant_id','$artikel_nr', '$menge','$preis','$mwst', '$rabatt', '$skonto', '$g_netto','1')";
        DB::insert($db_abfrage);
    }

    function rechnung_last_position($belegnr)
    {
        $result = DB::select("SELECT POSITION FROM RECHNUNGEN_POSITIONEN WHERE BELEG_NR='$belegnr' && AKTUELL='1' ORDER BY POSITION DESC LIMIT 0,1");
        if (!empty($result)) {
            $row = $result[0];
            return $row ['POSITION'];
        } else {
            return NULL;
        }
    }

    function pool_pos_deaktivieren($pp_dat)
    {
        $db_abfrage = "UPDATE POS_POOL SET AKTUELL='0' WHERE PP_DAT='$pp_dat'";
        DB::update($db_abfrage);
    }

    function pools_clean($pool_id)
    {
        $db_abfrage = "UPDATE POS_POOLS SET AKTUELL='0' WHERE ID='$pool_id' && AKTUELL='1'";
        DB::update($db_abfrage);
    }

    function btn_pool($kos_typ, $kos_id, $dat, $pos)
    {
        $f = new formular ();
        $pool_arr = $this->get_pools_arr_aktiv($kos_typ, $kos_id);
        if (!empty($pool_arr)) {
            $anz = count($pool_arr);
            for ($a = 0; $a < $anz; $a++) {
                $pool_id = $pool_arr [$a] ['ID'];
                $pool_bez = $this->get_pool_bez($pool_id);
                $js_weiter = "onclick=\"zeile_entfernen($pos, '$dat', '$kos_typ', '$kos_id', '$pool_id')\"";
                $f->button_js('btn_' . $a, "$pool_bez", $js_weiter);
            }
        } else {
            echo "NOPOOL";
        }
    }

    function get_pools_arr_aktiv($kos_typ, $kos_id)
    {
        $result = DB::select("SELECT * FROM POS_POOLS WHERE KOS_TYP='$kos_typ' && KOS_ID='$kos_id' && AKTUELL='1' ORDER BY AKTUELL DESC, POOL_NAME ASC");
        return $result;
    }

    function rechnungsausgangsbuch_pdf($von_typ, $von_id, $monat, $jahr, $rechnungstyp, $sort = 'ASC')
    {
        /* Ausgangsbuch */
        $rechnungen_arr = $this->ausgangsrechnungen_arr_sort($von_typ, $von_id, $monat, $jahr, $rechnungstyp, $sort);

        if (empty($rechnungen_arr)) {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\ErrorMessage("Keine $rechnungstyp vorhanden")
            );
        } else {
            $gesamt_brutto = 0;
            $gesamt_gut_retour = 0;
            $gesamt_skonti = 0;
            $anz_zz = sizeof($rechnungen_arr);
            for ($a = 0; $a < $anz_zz; $a++) {
                $belegnr = $rechnungen_arr [$a] ['BELEG_NR'];
                $this->rechnung_grunddaten_holen($belegnr);
                $tab_arr [$a] ['BELEG_NR'] = $belegnr;
                $tab_arr [$a] ['LFDNR'] = $this->aussteller_ausgangs_rnr;
                $tab_arr [$a] ['EMPFAENGER'] = substr($this->rechnungs_empfaenger_name, 0, 48);
                // $r->rechnungs_empfaenger_name = substr($r->rechnungs_empfaenger_name,0,48);
                $kurzbeschreibung = str_replace(",", ", ", $this->kurzbeschreibung);
                $kurzbeschreibung = str_replace("<br>", " ", $kurzbeschreibung);
                $kurzbeschreibung = str_replace("\n", " ", $kurzbeschreibung);

                $tab_arr [$a] ['KURZTEXT'] = $kurzbeschreibung;
                if ($this->rechnungstyp == 'Rechnung') {
                    $tab_arr [$a] ['BRUTTO'] = nummer_punkt2komma($this->rechnungs_brutto) . '€ ';
                    $gesamt_brutto += $this->rechnungs_brutto;
                }

                if ($this->rechnungstyp == 'Teilrechnung') {
                    $tab_arr [$a] ['BRUTTO'] = nummer_punkt2komma_t($this->rechnungs_brutto) . '€ ';
                    $gesamt_brutto += $this->rechnungs_brutto;
                }

                if ($this->rechnungstyp == 'Schlussrechnung') {
                    $rrr = new rechnungen ();
                    $rrr->get_summen_schlussrechnung($belegnr);
                    $rrr->get_sicherheitseinbehalt($belegnr);
                    if ($rrr->rg_betrag > '0.00') {
                        $rrr->rechnungs_brutto_schluss = $rrr->rechnungs_brutto_schluss - $rrr->rg_betrag;
                        // $rrr->rechnungs_brutto_schluss_a = nummer_punkt2komma_t($rrr->rechnungs_brutto_schluss);
                    }

                    $tab_arr [$a] ['BRUTTO'] = nummer_punkt2komma_t($rrr->rechnungs_brutto_schluss) . '€ ';
                    $gesamt_brutto += $rrr->rechnungs_brutto_schluss;
                }

                if ($this->rechnungstyp == 'Gutschrift' or $this->rechnungstyp == 'Stornorechnung') {
                    $tab_arr [$a] ['GUT_RET'] = nummer_punkt2komma($this->rechnungs_brutto) . '€ ';
                    $gesamt_gut_retour += $this->rechnungs_brutto;
                }

                $tab_arr [$a] ['RNR'] = $this->rechnungsnummer;
                $tab_arr [$a] ['DATUM'] = $this->rechnungsdatum;

                $tab_arr [$a] ['SKONTO'] = nummer_punkt2komma($this->rechnungs_skontoabzug) . '€ ';
                $gesamt_skonti += $this->rechnungs_skontoabzug;

                if ($a == sizeof($rechnungen_arr) - 1) {
                    $tab_arr [$a + 1] ['BRUTTO'] = '<b>=======</b>';
                    $tab_arr [$a + 1] ['GUT_RET'] = '<b>=======</b>';
                    $tab_arr [$a + 1] ['SKONTO'] = '<b>=======</b>';
                    $tab_arr [$a + 2] ['KURZTEXT'] = '<b>SUMMEN:</b>';
                    $tab_arr [$a + 2] ['BRUTTO'] = '<b>' . nummer_punkt2komma($gesamt_brutto) . '€ </b>';
                    $tab_arr [$a + 2] ['GUT_RET'] = '<b>' . nummer_punkt2komma($gesamt_gut_retour) . '€ </b>';
                    $tab_arr [$a + 2] ['SKONTO'] = '<b>' . nummer_punkt2komma($gesamt_skonti) . '€ </b>';
                }
            }
        }
        // echo '<pre>';
        // print_r($tab_arr);

        /* Spaltendefinition */
        $cols = array(
            'LFDNR' => "<b>LFDNR.</b>",
            'EMPFAENGER' => "<b>RECHNUNGSEMPFÄNGER</b>",
            'KURZTEXT' => "<b>LEISTUNG/WARE</b>",
            'BRUTTO' => "<b>BRUTTO</b>",
            'GUT_RET' => "<b>GUTSCHRIFTEN\n RETOUREN</b>",
            'RNR' => "<b>R-NR</b>",
            'DATUM' => "<b>DATUM</b>",
            'SKONTO' => "<b>SKONTO</b>"
        );

        /* Tabellenparameter */
        $tableoptions = array(
            'width' => 730,
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
                'LFDNR' => array(
                    'justification' => 'left'
                ),
                'EMPFAENGER' => array(
                    'justification' => 'left'
                ),
                'KURZTEXT' => array(
                    'justification' => 'left'
                ),
                'BRUTTO' => array(
                    'justification' => 'right'
                ),
                'GUT_RET' => array(
                    'justification' => 'right'
                ),
                'DATUM' => array(
                    'justification' => 'left'
                ),
                'SKONTO' => array(
                    'justification' => 'right'
                )
            )
        );

        /* ezPDF-Klasse laden */
        /* Eigene PDF-Klasse laden */
        /* Neues PDF-Objekt erstellen */
        $pdf = new Cezpdf ('a4', 'landscape');
        /* Neue Instanz von b_pdf */
        $bpdf = new b_pdf ();
        /* Header und Footer des Rechnungsaustellers in alle PDF-Seiten laden */
        $bpdf->b_header($pdf, $von_typ, $von_id, 'landscape', 'Helvetica.afm', 6);

        $all = $pdf->openObject();
        $pdf->saveState();
        $d = new detail ();
        if ($von_typ == 'Partner') {
            $mandanten_nr = $d->finde_mandanten_nr($von_id);
            $pdf->addText(43, 480, 8, "<b>Mandant: $mandanten_nr</b> Blatt: $monat ");
        }
        $pdf->addText(335, 480, 12, "<b>RECHNUNGSAUSGANGSBUCH</b>");
        $pdf->restoreState();
        $pdf->closeObject();
        $pdf->addObject($all, 'all');

        /* Tabelle ausgeben */
        $pdf->ezTable($tab_arr, $cols, "", $tableoptions);

        /* Ausgabepuffer leeren */
        ob_end_clean();
        /* PDF-Ausgabe */
        $pdf->ezStream();
    }

    function ausgangsrechnungen_arr_sort($aussteller_typ, $aussteller_id, $monat, $jahr, $rechnungstyp, $sort = 'ASC')
    {
        // echo "$aussteller_typ, $aussteller_id, $monat, $jahr, $rechnungstyp, $sort";
        if ($rechnungstyp == 'Rechnung') {
            $r_sql = "(RECHNUNGSTYP='$rechnungstyp' OR RECHNUNGSTYP='Stornorechnung' OR RECHNUNGSTYP='Gutschrift' OR RECHNUNGSTYP='Teilrechnung' OR RECHNUNGSTYP='Schlussrechnung')";
        } else {
            $r_sql = "RECHNUNGSTYP='$rechnungstyp'";
        }

        if ($monat == 'alle') {
            $result = DB::select("SELECT BELEG_NR, RECHNUNGSNUMMER, AUSTELLER_AUSGANGS_RNR FROM RECHNUNGEN WHERE AUSSTELLER_ID='$aussteller_id' && AUSSTELLER_TYP='$aussteller_typ' && DATE_FORMAT( RECHNUNGSDATUM, '%Y' ) = '$jahr' && $r_sql && AKTUELL = '1' ORDER BY AUSTELLER_AUSGANGS_RNR $sort");
        } else {
            $result = DB::select("SELECT BELEG_NR, RECHNUNGSNUMMER, AUSTELLER_AUSGANGS_RNR FROM RECHNUNGEN WHERE AUSSTELLER_ID='$aussteller_id' && AUSSTELLER_TYP='$aussteller_typ' && DATE_FORMAT( RECHNUNGSDATUM, '%Y-%m' ) = '$jahr-$monat' && $r_sql && AKTUELL = '1' ORDER BY AUSTELLER_AUSGANGS_RNR $sort");
        }
        return $result;
    }

    function get_summen_schlussrechnung($beleg_nr)
    {
        $result = DB::select("SELECT TEIL_R_ID FROM RECHNUNGEN_SCHLUSS WHERE SCHLUSS_R_ID='$beleg_nr' && AKTUELL='1' GROUP BY TEIL_R_ID ORDER BY TEIL_R_ID ASC");
        if (!empty($result)) {
            $z = 0;
            $summe_netto = 0;
            $summe_brutto = 0;
            $summe_mwst = 0;
            $summe_skontiert = 0;
            $summe_skonto_alle = 0;
            foreach ($result as $row) {
                $teil_r_id = $row ['TEIL_R_ID'];
                $rr = new rechnungen ();
                $rr->rechnung_grunddaten_holen($teil_r_id);
                $summe_netto += $rr->rechnungs_netto;
                $summe_mwst += $rr->rechnungs_mwst;
                $summe_brutto += $rr->rechnungs_brutto;
                $summe_skontiert += $rr->rechnungs_skontobetrag;
                $summe_skonto_alle += $rr->rechnungs_skontoabzug;
                $z++;
            }

            $rr->rechnung_grunddaten_holen($beleg_nr);

            $this->rechnungs_brutto_schluss = $rr->rechnungs_brutto - $summe_brutto;
            $this->rechnungs_mwst_schluss = $rr->rechnungs_mwst - $summe_mwst;
            $this->rechnungs_netto_schluss = $rr->rechnungs_netto - $summe_netto;
            $this->rechnungs_skontoabzug_schluss = $rr->rechnungs_skontoabzug;
        } else {
            $rr = new rechnungen ();
            $rr->rechnung_grunddaten_holen($beleg_nr);
            $this->rechnungs_brutto_schluss = $rr->rechnungs_brutto;
            $this->rechnungs_mwst_schluss = $rr->rechnungs_mwst;
            $this->rechnungs_netto_schluss = $rr->rechnungs_netto;
            $this->rechnungs_skontoabzug_schluss = $rr->rechnungs_skontoabzug;
        }
    }

    function get_sicherheitseinbehalt($beleg_nr)
    {
        if (!empty ($beleg_nr)) {
            $result = DB::select("SELECT * FROM SICH_EINBEHALT WHERE BELEG_NR='$beleg_nr' ORDER BY DAT DESC LIMIT 0,1");
            if (!empty($result)) {
                $row = $result[0];
                $this->rg_prozent = $row ['PROZENT'];
                $this->rg_betrag = $row ['BETRAG'];
            } else {
                $this->rg_prozent = '0.00';
                $this->rg_betrag = '0.00';
            }
        }
    }

    function rechnungseingangsbuch_pdf($von_typ, $von_id, $monat, $jahr, $rechnungstyp, $sort = 'ASC')
    {
        /* Ausgangsbuch */
        $rechnungen_arr = $this->eingangsrechnungen_arr_sort($von_typ, $von_id, $monat, $jahr, $rechnungstyp, $sort);

        if (empty($rechnungen_arr)) {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\ErrorMessage("Keine $rechnungstyp vorhanden")
            );
        } else {
            $gesamt_brutto = 0;
            $gesamt_gut_retour = 0;
            $gesamt_skonti = 0;
            for ($a = 0; $a < sizeof($rechnungen_arr); $a++) {
                $belegnr = $rechnungen_arr [$a] ['BELEG_NR'];
                $this->rechnung_grunddaten_holen($belegnr);
                $tab_arr [$a] ['BELEG_NR'] = $belegnr;
                $tab_arr [$a] ['LFDNR'] = $this->empfaenger_eingangs_rnr;
                $tab_arr [$a] ['EMPFAENGER'] = substr($this->rechnungs_aussteller_name, 0, 48);
                // $r->rechnungs_empfaenger_name = substr($r->rechnungs_empfaenger_name,0,48);
                $kurzbeschreibung = str_replace(",", ", ", $this->kurzbeschreibung);
                $kurzbeschreibung = str_replace("<br>", " ", $kurzbeschreibung);
                $kurzbeschreibung = str_replace("\n", " ", $kurzbeschreibung);

                $tab_arr [$a] ['KURZTEXT'] = $kurzbeschreibung;
                if ($this->rechnungstyp == 'Rechnung') {
                    $tab_arr [$a] ['BRUTTO'] = nummer_punkt2komma($this->rechnungs_brutto) . '€  ';
                    $gesamt_brutto += $this->rechnungs_brutto;
                }

                if ($this->rechnungstyp == 'Teilrechnung') {
                    $tab_arr [$a] ['BRUTTO'] = nummer_punkt2komma_t($this->rechnungs_brutto) . '€  ';
                    $gesamt_brutto += $this->rechnungs_brutto;
                }

                if ($this->rechnungstyp == 'Schlussrechnung') {
                    $tab_arr [$a] ['BRUTTO'] = nummer_punkt2komma_t($this->rechnungs_brutto) . '€  ';
                    $gesamt_brutto += $this->rechnungs_brutto;
                }

                if ($this->rechnungstyp == 'Gutschrift' or $this->rechnungstyp == 'Stornorechnung') {
                    $tab_arr [$a] ['GUT_RET'] = nummer_punkt2komma($this->rechnungs_brutto) . '€  ';
                    $gesamt_gut_retour += $this->rechnungs_brutto;
                }

                $tab_arr [$a] ['RNR'] = $this->rechnungsnummer;
                $tab_arr [$a] ['DATUM'] = $this->rechnungsdatum;

                $tab_arr [$a] ['SKONTO'] = nummer_punkt2komma($this->rechnungs_skontoabzug) . '€  ';
                $gesamt_skonti += $this->rechnungs_skontoabzug;

                if ($a == sizeof($rechnungen_arr) - 1) {
                    $tab_arr [$a + 1] ['BRUTTO'] = '<b>=======</b>';
                    $tab_arr [$a + 1] ['GUT_RET'] = '<b>=======</b>';
                    $tab_arr [$a + 1] ['SKONTO'] = '<b>=======</b>';
                    $tab_arr [$a + 2] ['KURZTEXT'] = '<b>SUMMEN:</b>';
                    $tab_arr [$a + 2] ['BRUTTO'] = '<b>' . nummer_punkt2komma($gesamt_brutto) . '€  </b>';
                    $tab_arr [$a + 2] ['GUT_RET'] = '<b>' . nummer_punkt2komma($gesamt_gut_retour) . '€  </b>';
                    $tab_arr [$a + 2] ['SKONTO'] = '<b>' . nummer_punkt2komma($gesamt_skonti) . '€  </b>';
                }
            }
        }
        // echo '<pre>';
        // print_r($tab_arr);

        /* Spaltendefinition */
        $cols = array(
            'LFDNR' => "<b>LFDNR.</b>",
            'EMPFAENGER' => "<b>RECHNUNGSSTELLER</b>",
            'KURZTEXT' => "<b>LEISTUNG/WARE</b>",
            'BRUTTO' => "<b>BRUTTO</b>",
            'GUT_RET' => "<b>GUTSCHRIFTEN\n RETOUREN</b>",
            'RNR' => "<b>R-NR</b>",
            'DATUM' => "<b>DATUM</b>",
            'SKONTO' => "<b>SKONTO</b>"
        );

        /* Tabellenparameter */
        $tableoptions = array(
            'width' => 730,
            'xPos' => 410,
            'shaded' => 0, // shaded: 0-->Zeile 1 & Zeile 2 --> weiss 1-->Zeile 1 = weiss Zeile 2= grau 2-->Zeile 1= grauA Zeile 2= grauB
            'showHeadings' => 1, // zeig Überschriften der spalten
            'showLines' => 1, // Mach Linien
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
            'rowGap' => 8,
            'colGap' => 1,
            'cols' => array(
                'LFDNR' => array(
                    'justification' => 'left',
                    'width' => 35
                ),
                'EMPFAENGER' => array(
                    'justification' => 'left'
                ),
                'KURZTEXT' => array(
                    'justification' => 'left'
                ),
                'BRUTTO' => array(
                    'justification' => 'right',
                    'width' => 70
                ),
                'GUT_RET' => array(
                    'justification' => 'right',
                    'width' => 70
                ),
                'RNR' => array(
                    'justification' => 'right',
                    'width' => 60
                ),
                'DATUM' => array(
                    'justification' => 'right',
                    'width' => 50
                ),
                'SKONTO' => array(
                    'justification' => 'right',
                    'width' => 40
                )
            )
        );
        /* Neues PDF-Objekt erstellen */
        $pdf = new Cezpdf ('a4', 'landscape');
        /* Neue Instanz von b_pdf */
        $bpdf = new b_pdf ();
        /* Header und Footer des Rechnungsaustellers in alle PDF-Seiten laden */
        $bpdf->b_header($pdf, $von_typ, $von_id, 'landscape', 'Helvetica.afm', 6);

        $all = $pdf->openObject();
        $pdf->saveState();
        $d = new detail ();
        if ($von_typ == 'Partner') {
            $mandanten_nr = $d->finde_mandanten_nr($von_id);
            $pdf->addText(43, 480, 8, "<b>Mandant: $mandanten_nr</b> Zeitraum: $monat-$jahr");
        }
        $pdf->addText(335, 480, 12, "<b>RECHNUNGSEINGANGSBUCH</b>");
        $pdf->restoreState();
        $pdf->closeObject();
        $pdf->addObject($all, 'all');

        /* Tabelle ausgeben */
        $pdf->ezTable($tab_arr, $cols, "", $tableoptions);

        /* Ausgabepuffer leeren */
        ob_end_clean();
        /* PDF-Ausgabe */
        $pdf->ezStream();
    }

    function eingangsrechnungen_arr_sort($empfaenger_typ, $empfaenger_id, $monat, $jahr, $rechnungstyp, $sort = 'ASC')
    {
        if ($rechnungstyp == 'Rechnung') {
            $r_sql = "(RECHNUNGSTYP='$rechnungstyp' OR RECHNUNGSTYP='Stornorechnung' OR RECHNUNGSTYP='Gutschrift' OR RECHNUNGSTYP='Teilrechnung' OR RECHNUNGSTYP='Schlussrechnung')";
        } else {
            $r_sql = "RECHNUNGSTYP='$rechnungstyp'";
        }
        if ($monat == 'alle') {
            $result = DB::select("SELECT BELEG_NR, RECHNUNGSNUMMER, AUSTELLER_AUSGANGS_RNR, EMPFAENGER_EINGANGS_RNR AS WE_NR FROM RECHNUNGEN WHERE EMPFAENGER_ID='$empfaenger_id' && EMPFAENGER_TYP='$empfaenger_typ' && DATE_FORMAT( RECHNUNGSDATUM, '%Y' ) = '$jahr' && $r_sql && AKTUELL = '1' GROUP BY BELEG_NR ORDER BY EMPFAENGER_EINGANGS_RNR $sort");
        } else {
            $result = DB::select("SELECT BELEG_NR, RECHNUNGSNUMMER, AUSTELLER_AUSGANGS_RNR, EMPFAENGER_EINGANGS_RNR AS WE_NR FROM RECHNUNGEN WHERE EMPFAENGER_ID='$empfaenger_id' && EMPFAENGER_TYP='$empfaenger_typ' && DATE_FORMAT( RECHNUNGSDATUM, '%Y-%m' ) = '$jahr-$monat' && $r_sql && AKTUELL = '1' GROUP BY BELEG_NR ORDER BY EMPFAENGER_EINGANGS_RNR $sort");
        }
        return $result;
    }

    function form_rbuecher_suchen()
    {
        $f = new formular ();
        $f->erstelle_formular("Rechnungsbuch als PDF anzeigen ", null, 'rbuch');
        $this->dropdown_rbuch('Rechnungsbuch wählen', 'buchart', 'buchart');
        $js_action = "onchange=\"list_kostentraeger('list_kostentraeger', this.value)\" onmouseover=\"list_kostentraeger('list_kostentraeger', this.value)\"";
        $this->dropdown_rbuch_inhaber('Rechnungsbuchinhaber', 'r_inhaber_t', 'r_inhaber_t', $js_action);
        $this->dropdown_inhaber('Rechnungsinhaber wählen', 'r_inhaber', 'dd_kostentraeger_id');
        $js = '';
        $this->drop_rechnungs_typen('Rechnungsart wählen', 'r_art', 'r_art', $js, 'Rechnung');
        $this->dropdown_monate('Monat wählen', 'monat', 'monat');
        $this->dropdown_jahre('Jahr wählen', 'jahr', 'jahr');
        $f->hidden_feld('option', 'rechnungsbuch_suche1');
        $f->send_button('submit', 'Anzeigen');
        $f->ende_formular();
    }

    /* Artikelinformationen aus dem Katalog holen */

    function dropdown_rbuch($beschreibung, $name, $id)
    {
        echo "<div class='input-field'>";
        echo "<select name=\"$name\" id=\"$id\">\n";
        echo "<option value=\"ausgangsbuch\">Ausgangsbuch</option>\n";
        echo "<option value=\"eingangsbuch\">Eingangsbuch</option>\n";
        echo "</select>\n";
        echo "<label for=\"$id\">$beschreibung</label>\n";
        echo "</div>";
    }

    /* Rechnungspositionen finden */

    function dropdown_rbuch_inhaber($beschreibung, $name, $id, $js_action)
    {
        echo "<div class='input-field'>";
        echo "<select name=\"$name\" id=\"$id\" $js_action>\n";
        echo "<option value=\"Partner\">Partner</option>\n";
        echo "<option value=\"Lager\">Lager</option>\n";
        echo "</select>\n";
        echo "<label for=\"$id\">$beschreibung</label>\n";
        echo "<div>";
    }

    /* Funktionen für Angebote */

    function dropdown_inhaber($label, $name, $id)
    {
        echo "<div class='input-field'>";
        echo "<select name=\"$name\" id=\"$id\" size=1 $js_action>\n";
        echo "<option value=\"\">Bitte wählen</option>\n";
        echo "</select>\n";
        echo "<label for=\"$id\">$label</label>";
        echo "<div>";
    }

    function dropdown_monate($label, $name, $id)
    {
        echo "<div class='input-field'>";
        echo "<select name=\"$name\" id=\"$id\" size=1 $js_action>\n";
        echo "<option value=\"alle\">Alle</option>\n";
        for ($a = 1; $a <= 12; $a++) {
            $monatsname = monat2name($a);
            echo "<option value=\"$a\">$monatsname</option>\n";
        }
        echo "</select>\n";
        echo "<label for=\"$id\">$label</label>";
        echo "<div>";
    }

    function dropdown_jahre($label, $name, $id)
    {
        echo "<div class='input-field'>";
        echo "<select name=\"$name\" id=\"$id\" size=1 $js_action>\n";
        for ($a = date("Y"); $a >= date("Y") - 5; $a--) {
            echo "<option value=\"$a\">$a</option>\n";
        }
        echo "</select>\n";
        echo "<label for=\"$id\">$label</label>";
        echo "<div>";
    }

    function form_angebot_erfassen()
    {
        $f = new formular ();
        $p = new partners ();
        $f->erstelle_formular('Angebot erfassen', '');
        $f->hidden_feld("aussteller_typ", "Partner");
        $p->partner_dropdown(' Angebot von ', 'aussteller_id', 'aussteller');
        $f->hidden_feld("empfaenger_typ", "Partner");
        $p->partner_dropdown(' Angebot an ', 'empfaenger_id', 'empfaenger');
        $f->text_bereich("Kurzbeschreibung", "kurzbeschreibung", "", "50", "10");
        $f->send_button("submit_ang", "Angebot anlegen");
        $f->hidden_feld("option", "angebot_erfassen1");
        $f->ende_formular();
    }

    function angebot_speichern($aussteller_typ, $aussteller_id, $empfaenger_typ, $empfaenger_id, $kurzinfo)
    {
        $id = last_id2('RECHNUNGEN', 'BELEG_NR') + 1;

        $letzte_aussteller_rnr = $this->letzte_ausgangs_ang_nr($aussteller_typ, $aussteller_id);
        $n_ang_nr = $letzte_aussteller_rnr + 1;
        $n_ang_nr_3 = sprintf('%03d', $n_ang_nr);
        $ang_nr = "AN-$aussteller_id-$n_ang_nr_3"; // A-Angebot dann id dann letze angnr +1 z.B. A-1-001

        $letzte_empfaenger_rnr = $this->letzte_eingangs_ang_nr($empfaenger_typ, $empfaenger_id);
        $n_empfaenger_rnr = $letzte_empfaenger_rnr + 1;
        $rechnungsdatum = date("Y-m-d");
        $db_abfrage = "INSERT INTO RECHNUNGEN VALUES (NULL, '$id', '$ang_nr', '$n_ang_nr', '$n_empfaenger_rnr', 'Angebot', '$rechnungsdatum','$rechnungsdatum', '0.00','0.0','0.00', '$aussteller_typ', '$aussteller_id','$empfaenger_typ', '$empfaenger_id','1', '1', '0', '0', '1', '0', '0', '$rechnungsdatum', '$rechnungsdatum', '$kurzinfo', '9999999', NULL, NULL, NULL)";
        DB::insert($db_abfrage);

        /* Protokollieren */
        $last_dat = DB::getPdo()->lastInsertId();
        protokollieren('RECHNUNGEN', $last_dat, '0');
        /* Ausgabe weil speichern erfolgreich */
        weiterleiten_in_sec(route('web::rechnungen::legacy', ['option' => 'lieferschein_erfassen', 'beleg_nr' => $id], false), 2); // Positionseingabe echo "Angebot $n_ang_nr_3 mit der Belegnr.: $id wurde erfasst.";
    }

    function letzte_ausgangs_ang_nr($aussteller_typ, $aussteller_id)
    {
        $result = DB::select("SELECT AUSTELLER_AUSGANGS_RNR FROM RECHNUNGEN WHERE RECHNUNGSTYP='Angebot' && AUSSTELLER_TYP='$aussteller_typ' && AUSSTELLER_ID='$aussteller_id' ORDER BY AUSTELLER_AUSGANGS_RNR DESC LIMIT 0,1");
        $row = $result[0];
        return $row ['AUSTELLER_AUSGANGS_RNR'];
    }

    function letzte_eingangs_ang_nr($empfaenger_typ, $empfaenger_id)
    {
        $result = DB::select("SELECT EMPFAENGER_EINGANGS_RNR FROM RECHNUNGEN WHERE RECHNUNGSTYP='Angebot' && EMPFAENGER_TYP='$empfaenger_typ' && EMPFAENGER_ID='$empfaenger_id' ORDER BY EMPFAENGER_EINGANGS_RNR DESC LIMIT 0,1");
        $row = $result[0];
        return $row ['EMPFAENGER_EINGANGS_RNR'];
    }

    function rechnung_erstellen_ugl($rnr, $r_typ, $r_datum, $eingangsdatum, $aus_typ, $aus_id, $empf_typ, $empf_id, $faellig, $kurzinfo, $netto_betrag, $brutto_betrag, $skonto_betrag, $servicetime_from, $servicetime_to)
    {
        $beleg_nr = last_id2('RECHNUNGEN', 'BELEG_NR') + 1;
        $e_dat_arr = explode('.', $eingangsdatum);

        $a_dat_arr = explode('.', $r_datum);
        $a_jahr = $a_dat_arr [2];
        $r_datum_sql = date_german2mysql($r_datum);

        $servicetime_from = $servicetime_from ? "'" . date_german2mysql($servicetime_from) . "'" : 'NULL';
        $servicetime_to = $servicetime_to ? "'" . date_german2mysql($servicetime_to) . "'" : 'NULL';

        $l_empf_e_nr = $this->letzte_empfaenger_eingangs_nr2($empf_typ, $empf_id, $a_jahr, $r_typ) + 1;
        $l_ausg_rnr = $this->letzte_aussteller_ausgangs_nr2($aus_typ, $aus_id, $a_jahr, $r_typ) + 1;

        $pp = new partners ();
        $empf_gk_id = $pp->letzte_konto_geldkonto_id_p($aus_id);

        $faellig = date_german2mysql($faellig);
        $eingangsdatum_sql = date_german2mysql($eingangsdatum);

        $db_abfrage = "INSERT INTO RECHNUNGEN VALUES (NULL, '$beleg_nr', '$rnr', '$l_ausg_rnr', '$l_empf_e_nr', '$r_typ', '$r_datum_sql','$eingangsdatum_sql', '$netto_betrag','$brutto_betrag','$skonto_betrag', '$aus_typ', '$aus_id','$empf_typ', '$empf_id','1', '1', '1', '0', '1', '0', '0', '$faellig', '0000-00-00', '$kurzinfo', '$empf_gk_id', NULL, $servicetime_from, $servicetime_to)";
        DB::insert($db_abfrage);
        /* Protokollieren */
        $last_dat = DB::getPdo()->lastInsertId();
        protokollieren('RECHNUNGEN', $last_dat, '0');
        echo "$r_typ aus UGL Importiert";
        return $beleg_nr;
    }

    function letzte_empfaenger_eingangs_nr2($empf_typ, $empf_id, $jahr, $rechnungs_typ)
    {
        if ($rechnungs_typ == 'Stornorechnung' or $rechnungs_typ == 'Gutschrift' or $rechnungs_typ == 'Schlussrechnung' or $rechnungs_typ == 'Teilrechnung') {
            $rechnungs_typ == 'Rechnung';
            $result = DB::select("SELECT EMPFAENGER_EINGANGS_RNR FROM RECHNUNGEN WHERE EMPFAENGER_ID='$empf_id' && EMPFAENGER_TYP='$empf_typ' && DATE_FORMAT(RECHNUNGSDATUM, '%Y') = '$jahr' && (RECHNUNGSTYP='$rechnungs_typ' OR RECHNUNGSTYP='Gutschrift' OR RECHNUNGSTYP='Stornorechnung' OR RECHNUNGSTYP='Schlussrechnung'  OR RECHNUNGSTYP='Teilrechnung') && AKTUELL='1' ORDER BY BELEG_NR DESC LIMIT 0,1");
        } else {
            $result = DB::select("SELECT EMPFAENGER_EINGANGS_RNR FROM RECHNUNGEN WHERE EMPFAENGER_ID='$empf_id' && EMPFAENGER_TYP='$empf_typ' && DATE_FORMAT(RECHNUNGSDATUM, '%Y') = '$jahr' && RECHNUNGSTYP='$rechnungs_typ'  && AKTUELL='1' ORDER BY BELEG_NR DESC LIMIT 0,1");
        }
        $row = $result[0];
        return $row ['EMPFAENGER_EINGANGS_RNR'];
    }

    function letzte_aussteller_ausgangs_nr2($aus_typ, $aus_id, $jahr, $rechnungs_typ)
    {
        if ($rechnungs_typ == 'Stornorechnung' or $rechnungs_typ == 'Gutschrift' or $rechnungs_typ == 'Schlussrechnung' or $rechnungs_typ == 'Teilrechnung') {
            $rechnungs_typ == 'Rechnung';
            $result = DB::select("SELECT AUSTELLER_AUSGANGS_RNR FROM RECHNUNGEN WHERE AUSSTELLER_ID='$aus_id' && AUSSTELLER_TYP='$aus_typ' && RECHNUNGSDATUM BETWEEN '$jahr-01-01' AND '$jahr-12-31' && (RECHNUNGSTYP='$rechnungs_typ' OR RECHNUNGSTYP='Gutschrift' OR RECHNUNGSTYP='Stornorechnung' OR RECHNUNGSTYP='Schlussrechnung'  OR RECHNUNGSTYP='Teilrechnung') && AKTUELL='1' ORDER BY AUSTELLER_AUSGANGS_RNR DESC LIMIT 0,1");
        } else {
            $result = DB::select("SELECT AUSTELLER_AUSGANGS_RNR FROM RECHNUNGEN WHERE AUSSTELLER_ID='$aus_id' && AUSSTELLER_TYP='$aus_typ' && RECHNUNGSDATUM BETWEEN '$jahr-01-01' AND '$jahr-12-31' && RECHNUNGSTYP='$rechnungs_typ' && AKTUELL='1' ORDER BY AUSTELLER_AUSGANGS_RNR DESC LIMIT 0,1");
        }
        $row = $result[0];
        return $row ['AUSTELLER_AUSGANGS_RNR'];
    }

    function rechnung_erstellen_csv($r_typ, $r_datum, $eingangsdatum, $aus_typ, $aus_id, $empf_typ, $empf_id, $faellig, $kurzinfo, $netto_betrag, $brutto_betrag, $skonto_betrag, $servicetime_from, $servicetime_to)
    {
        echo "$rnr, $r_typ, $r_datum, $eingangsdatum, $aus_typ, $aus_id, $empf_typ, $empf_id, $faellig, $kurzinfo, $netto_betrag,$brutto_betrag,$skonto_betrag";
        $beleg_nr = last_id2('RECHNUNGEN', 'BELEG_NR') + 1;
        $e_dat_arr = explode('.', $eingangsdatum);

        $a_dat_arr = explode('.', $r_datum);
        $a_jahr = $a_dat_arr [2];
        $r_datum_sql = date_german2mysql($r_datum);

        $l_empf_e_nr = $this->letzte_empfaenger_eingangs_nr2($empf_typ, $empf_id, $a_jahr, $r_typ) + 1;
        $l_ausg_rnr = $this->letzte_aussteller_ausgangs_nr2($aus_typ, $aus_id, $a_jahr, $r_typ) + 1;

        $letzte_aussteller_rnr = sprintf('%03d', $l_ausg_rnr);
        /* Kürzel */
        $r = new rechnung ();

        if ($r_typ == 'Angebot') {
            $rnr = "AN-$aus_id-$letzte_aussteller_rnr"; // A-Angebot dann id dann letze angnr +1 z.B. A-1-001
        } else {
            $rnr = $r->rechnungs_kuerzel . ' ' . $letzte_aussteller_rnr . '-' . $a_jahr;
        }

        $pp = new partners ();
        $empf_gk_id = $pp->letzte_konto_geldkonto_id_p($aus_id);

        $faellig = date_german2mysql($faellig);
        $eingangsdatum_sql = date_german2mysql($eingangsdatum);

        $db_abfrage = "INSERT INTO RECHNUNGEN VALUES (NULL, '$beleg_nr', '$rnr', '$l_ausg_rnr', '$l_empf_e_nr', '$r_typ', '$r_datum_sql','$eingangsdatum_sql', '$netto_betrag','$brutto_betrag','$skonto_betrag', '$aus_typ', '$aus_id','$empf_typ', '$empf_id','1', '1', '1', '0', '1', '0', '0', '$faellig', '0000-00-00', '$kurzinfo', '$empf_gk_id', NULL, $servicetime_from, $servicetime_to)";
        DB::insert($db_abfrage);
        /* Protokollieren */
        $last_dat = DB::getPdo()->lastInsertId();
        protokollieren('RECHNUNGEN', $last_dat, '0');
        echo "$r_typ aus CSV Importiert";
        return $beleg_nr;
    }

    function meine_angebote_anzeigen()
    {
        $arr = $this->meine_angebote_arr();
        if (!empty($arr)) {
            $anz = count($arr);
            echo "<table>";
            echo "<tr><th>ANGEBOTSNR</th><th>EMPFAENGER</th><th>KURZINFO</th><th>OPTION</th><th>PDF</th><th>BUCHUNGSBELEG</th></tr>";
            for ($a = 0; $a < $anz; $a++) {

                $beleg_id = $arr [$a] ['BELEG_NR'];
                $r = new rechnung ();
                $r->rechnung_grunddaten_holen($beleg_id);
                $ang_nr = $arr [$a] ['RECHNUNGSNUMMER'];
                $kurzinfo = $arr [$a] ['KURZBESCHREIBUNG'];
                $link_bearbeiten = "<a href='" . route('web::rechnungen::legacy', ['option' => 'positionen_erfassen', 'belegnr' => $beleg_id]) . "'>Bearbeiten</a>";
                $kurzbeschreibung = "Buchungsbeleg aus Angebot $ang_nr\n$kurzinfo";
                if ($bg = $this->check_beleg_exists($kurzbeschreibung)) {
                    $link_2beleg = "<a href='" . route('web::rechnungen.show', ['belegnr' => $bg]) . "'>Beleg ansehen</a>";
                    $pdf_link3 = "<a href='" . route('web::rechnungen::legacy', ['option' => 'anzeigen_pdf', 'belegnr' => $bg]) . "'><img src=\"images/pdf_light.png\"></a>";
                    $pdf_link4 = "<a href='" . route('web::rechnungen::legacy', ['option' => 'anzeigen_pdf', 'belegnr' => $belegnr, 'no_logo']) . "'><img src=\"images/pdf_dark.png\"></a>";
                } else {
                    $link_2beleg = "<a href='" . route('web::rechnungen::legacy', ['option' => 'ang2beleg', 'belegnr' => $beleg_id]) . "'>Buchungsbeleg erstellen</a>";
                    $pdf_link3 = '';
                    $pdf_link4 = '';
                }

                $pdf_link = "<a href='" . route('web::rechnungen::legacy', ['option' => 'anzeigen_pdf', 'belegnr' => $beleg_id]) . "'><img src=\"images/pdf_light.png\"></a>";
                $pdf_link1 = "<a href='" . route('web::rechnungen::legacy', ['option' => 'anzeigen_pdf', 'belegnr' => $beleg_id, 'no_logo']) . "'><img src=\"images/pdf_dark.png\"></a>";

                echo "<tr><td>$ang_nr</td><td>$r->rechnungs_empfaenger_name</td><td>$r->kurzbeschreibung</td><td>$link_bearbeiten</td><td>$pdf_link $pdf_link1</td><td>$link_2beleg $pdf_link3 $pdf_link4</td></tr>";
            }
            echo "</table>";
        } else {
            if (!session()->has('partner_id')) {
                echo "Keine Angebote vorhanden!";
            } else {
                echo "Keine Angebote von gewähltem Partner vorhanden!";
            }
        }
    }

    function meine_angebote_arr()
    {
        if (!session()->has('partner_id')) {
            $result = DB::select("SELECT * FROM RECHNUNGEN WHERE  AKTUELL='1' && RECHNUNGSTYP='Angebot' ORDER BY RECHNUNGSDATUM DESC");
        } else {
            $aussteller_id = session()->get('partner_id');
            $result = DB::select("SELECT * FROM RECHNUNGEN WHERE  AKTUELL='1' && RECHNUNGSTYP='Angebot' && AUSSTELLER_TYP='Partner' && AUSSTELLER_ID='$aussteller_id' ORDER BY RECHNUNGSDATUM DESC");
        }
        return $result;
    }

    function check_beleg_exists($kurzbeschreibung)
    {
        $result = DB::select("SELECT BELEG_NR FROM RECHNUNGEN WHERE KURZBESCHREIBUNG='$kurzbeschreibung' && AKTUELL='1' LIMIT 0,1");
        if (!empty($result)) {
            $row = $result[0];
            return $row ['BELEG_NR'];
        }
    }

    /* Rechnungspositionen finden die 99.99 % Rabatt oder 9.99 Skonto haben */

    function angebot2beleg($belegnr)
    {
        $r = new rechnung ();
        $r->rechnung_grunddaten_holen($belegnr);

        $bp_partner_id = $r->rechnungs_aussteller_id;

        $clean_arr ['RECHNUNG_EMPFAENGER_TYP'] = 'Partner';
        $clean_arr ['RECHNUNG_EMPFAENGER_ID'] = $bp_partner_id;
        $clean_arr ['RECHNUNG_AUSSTELLER_TYP'] = 'Partner';
        $clean_arr ['RECHNUNG_AUSSTELLER_ID'] = $bp_partner_id;

        $clean_arr ['RECHNUNGSDATUM'] = date("d.m.Y");
        $clean_arr ['RECHNUNG_FAELLIG_AM'] = date("d.m.Y");
        $kurzbeschreibung = "Buchungsbeleg aus Angebot $r->rechnungsnummer\n$r->kurzbeschreibung";
        $clean_arr ['kurzbeschreibung'] = $kurzbeschreibung;
        if ($bg = $this->check_beleg_exists($kurzbeschreibung)) {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\ErrorMessage("Beleg $bg schon erstellt")
            );
        }
        $clean_arr ['nettobetrag'] = 0.00;

        $clean_arr ['bruttobetrag'] = 0.00;

        $clean_arr ['skonto'] = 0.00;

        $gk = new geldkonto_info ();
        $gk->geld_konto_ermitteln('Partner', $bp_partner_id, null, 'Kreditor');
        $clean_arr ['EMPFANGS_GELD_KONTO'] = $gk->geldkonto_id;
        $l_erf_nr = $r->auto_rechnung_speichern($clean_arr);

        $zeilen_arr = $r->rechnungs_positionen_arr($belegnr);
        $anz = count($zeilen_arr);
        for ($a = 0; $a < $anz; $a++) {
            $art_lieferant = $zeilen_arr [$a] ['ART_LIEFERANT'];
            $art_nr = $zeilen_arr [$a] ['ARTIKEL_NR'];
            $menge = $zeilen_arr [$a] ['MENGE'];
            $preis = $zeilen_arr [$a] ['PREIS'];
            $mwst = $zeilen_arr [$a] ['MWST_SATZ'];
            $rabatt = $zeilen_arr [$a] ['RABATT_SATZ'];
            $skonto = $zeilen_arr [$a] ['SKONTO'];
            $g_netto = $zeilen_arr [$a] ['GESAMT_NETTO'];
            $aktuell = $zeilen_arr [$a] ['AKTUELL'];
            /* Nur Aktuelle übertragen, kein Müll */
            if ($aktuell == 1) {
                $this->position_speichern($l_erf_nr, $belegnr, $art_lieferant, $art_nr, $menge, $preis, $mwst, $skonto, $rabatt, $g_netto);
            }
        }
    }

    function seb_rgs_anzeigen()
    {
        echo "<h2>Rechnungen mit Sicherheitseinbehalt</h2>";
        $result = DB::select("SELECT * FROM SICH_EINBEHALT ORDER BY EINBEHALT_BIS");
        if (!empty($result)) {
            foreach ($result as $row) {
                $b_nr = $row ['BELEG_NR'];
                $betrag = $row ['BETRAG'];
                $bis = date_mysql2german($row ['EINBEHALT_BIS']);
                $this->rechnung_grunddaten_holen($b_nr);
                echo "$this->rechnungs_empfaenger_name|$this->rechnungsnummer|BETRAG:$betrag € | $bis<br>";
            }
        }
    }

    function autokorrektur_pos($belegnr)
    {
        $pos_arr = $this->rechnungs_positionen_arr_99($belegnr);
        if (!empty($pos_arr)) {
            $anz = count($pos_arr);
            for ($a = 0; $a < $anz; $a++) {
                $_pos = $pos_arr [$a] ['POSITION'];
                $_ubeleg = $pos_arr [$a] ['U_BELEG_NR'];
                $_artlieferant = $pos_arr [$a] ['ART_LIEFERANT'];
                $_art_nr = $pos_arr [$a] ['ARTIKEL_NR'];
                $_menge = $pos_arr [$a] ['MENGE'];
                $_preis = $pos_arr [$a] ['PREIS'];

                $_1pos_arr = $this->get_position_artikel_nr($_ubeleg, $_art_nr, $_artlieferant);
                if (!empty($_1pos_arr)) {
                    // print_r($_1pos_arr);
                    $u_rabatt = $_1pos_arr ['RABATT_SATZ'];
                    $skonto = $_1pos_arr ['SKONTO'];
                    // echo "$u_rabatt $skonto<br>";

                    // FORMAT((MENGE*PREIS/100)*(100-RABATT_SATZ),2)
                    $neu_preis = number_format(($_menge * $_preis / 100) * (100 - $u_rabatt), 2);
                    $k_neu_preis = number_format($_menge * $_preis, 2);

                    /* Update Rechnung Positionen */
                    $db_abfrage = "UPDATE RECHNUNGEN_POSITIONEN SET RABATT_SATZ='$u_rabatt', SKONTO='$skonto', GESAMT_NETTO='$neu_preis' WHERE POSITION='$_pos' && BELEG_NR='$belegnr' && AKTUELL='1'";
                    DB::update($db_abfrage);

                    echo "Rechnungsposition $_pos wurde geändert<br>";

                    /* Update Rechnung Kontierung */
                    $db_abfrage = "UPDATE KONTIERUNG_POSITIONEN SET RABATT_SATZ='$u_rabatt', SKONTO='$skonto', GESAMT_SUMME='$k_neu_preis' WHERE POSITION='$_pos' && BELEG_NR='$belegnr' && AKTUELL='1'";
                    DB::update($db_abfrage);
                    echo "Kontierungsposition $_pos wurde geändert<br>";
                } else {
                    echo "$_pos nicht verändert, keine Daten in der Ursprungsrechnung";
                }
            }
        } else {
            echo "Rechnungsrabatt und Skonti identisch mit den Ursprungsrechnungen!";
        }
        weiterleiten_in_sec(route('web::rechnungen.show', ['id' => $belegnr]), 1);
    }

    function rechnungs_positionen_arr_99($belegnr)
    {
        $result = DB::select("SELECT POSITION, U_BELEG_NR, ART_LIEFERANT, ARTIKEL_NR, MENGE, PREIS FROM RECHNUNGEN_POSITIONEN WHERE BELEG_NR='$belegnr' && AKTUELL='1' && (RABATT_SATZ='99.99' OR SKONTO='9.99' OR RABATT_SATZ='999.99') ORDER BY POSITION ASC");
        return $result;
    }

    function get_position_artikel_nr($belegnr, $art_nr, $art_lieferant)
    {
        $result = DB::select("SELECT * FROM RECHNUNGEN_POSITIONEN WHERE BELEG_NR='$belegnr' && ART_LIEFERANT='$art_lieferant' && ARTIKEL_NR='$art_nr' && AKTUELL='1' ORDER BY RECHNUNGEN_POS_DAT DESC LIMIT 0,1");
        if (!empty($result)) {
            return $result[0];
        }
        return [];
    }

    function edisp($belegnr)
    {
        $pos_arr = $this->rechnungs_positionen_arr($belegnr);
        if (!empty($pos_arr)) {
            $anz = $this->anzahl_positionen;
            for ($a = 0; $a < $anz; $a++) {
                $pos = $pos_arr [$a] ['POSITION'];
                $preis = $pos_arr [$a] ['PREIS'];
                $menge = $pos_arr [$a] ['MENGE'];
                $rabatt = $pos_arr [$a] ['RABATT_SATZ'];
                $gpreis = number_format(($menge * $preis / 100) * (100 - $rabatt), 2);

                /* Update Rechnung Positionen */
                $db_abfrage = "UPDATE RECHNUNGEN_POSITIONEN SET GESAMT_NETTO='$gpreis' WHERE POSITION='$pos' && BELEG_NR='$belegnr' && AKTUELL='1'";
                DB::update($db_abfrage);
            }
        }
    }

    function u_pools_erstellen()
    {
        $b = new buchen ();
        $js_typ = "onchange=\"list_kostentraeger('list_kostentraeger', this.value)\"";
        $b->dropdown_kostentreager_typen('Kostenträgertyp', 'kostentraeger_typ', 'kostentraeger_typ', $js_typ);
        $js_id = "onchange=\"list_u_pools('kostentraeger_typ', 'dd_kostentraeger_id')\" ";
        $b->dropdown_kostentreager_ids('Kostenträger', 'kostentraeger_id', 'dd_kostentraeger_id', $js_id);
        $f = new formular ();
        echo "<br><br>";
        $f->fieldset("Pools", 'pools');
        $f->fieldset_ende();
    }

    function u_pools_anzeigen($kos_typ, $kos_bez)
    {

        // echo "HIER SIND POOLS von $kos_typ $kos_bez";
        $bu = new buchen ();
        $kos_id = $bu->kostentraeger_id_ermitteln($kos_typ, $kos_bez);
        if (!$kos_id) {
            echo $kos_bez;
            echo "<br>$kos_typ $kos_bez";
            throw new \App\Exceptions\MessageException(
                new \App\Messages\ErrorMessage("Kostenträger konnte nicht gefunden werden.")
            );
        }
        $pool_arr = $this->get_pools_arr($kos_typ, $kos_id);
        echo "<br><table>";
        echo "<tr><th>NEUER UNTERPOOL FÜR $kos_typ $kos_bez</th></tr><tr>";
        $f = new formular ();
        $f->text_feld('Bezeichnung des Pools', 'np', '', '100', 'np', '');
        $js = "onclick=\"u_pool_erstellen('np','kostentraeger_typ', 'dd_kostentraeger_id')\"";
        $f->button_js('btn_np', 'Unterpool erstellen', $js);
        echo "</th></tr>";
        if (!empty($pool_arr)) {
            $anz = count($pool_arr);
            $temp_akt = '';

            for ($a = 0; $a < $anz; $a++) {
                $pool_id = $pool_arr [$a] ['ID'];
                $pool_name = $pool_arr [$a] ['POOL_NAME'];
                $pool_akt = $pool_arr [$a] ['AKTUELL'];
                if ($pool_akt != $temp_akt) {
                    $temp_akt = $pool_akt;
                    if ($pool_akt == '1') {
                        $status = 'Aktiv';
                    } else {
                        $status = 'inaktiv';
                    }
                    echo "<tr><th>$status</th></tr>";
                }
                $link = "<a onclick=\"act_deacivate('$pool_id', '$kos_typ', '$kos_bez', '$kos_id')\">$pool_name</a>";
                echo "<tr><td>$link</td></tr>";
            }
            echo "</table>";
        } else {
            echo "</table>";
            echo "Keine POOLS für $kos_bez";
        }
    }

    function get_pools_arr($kos_typ, $kos_id)
    {
        $result = DB::select("SELECT * FROM POS_POOLS WHERE KOS_TYP='$kos_typ' && KOS_ID='$kos_id' ORDER BY AKTUELL DESC, POOL_NAME ASC");
        return $result;
    }

    function u_pool_erstellen($pool_bez, $kos_typ, $kos_bez)
    {
        $b = new buchen ();
        $kos_id = $b->kostentraeger_id_ermitteln($kos_typ, $kos_bez);
        $l_pool_id = last_id2('POS_POOLS', 'ID') + 1;
        $db_abfrage = "INSERT INTO POS_POOLS VALUES (NULL, '$l_pool_id', '$pool_bez', '$kos_typ','$kos_id','1')";
        DB::insert($db_abfrage);
    }

    function pool_act_deactivate($pool_id, $kos_typ, $kos_id)
    {
        $result = DB::select("SELECT AKTUELL FROM POS_POOLS WHERE ID='$pool_id' && KOS_TYP='$kos_typ' && KOS_ID='$kos_id' LIMIT 0,1");
        if (!empty($result)) {
            $row = $result[0];
            $akt = $row ['AKTUELL'];

            if ($akt == 0) {
                $akt_neu = 1;
            } else {
                $akt_neu = 0;
            }
            DB::update("UPDATE POS_POOLS SET AKTUELL='$akt_neu' WHERE ID='$pool_id' && KOS_TYP='$kos_typ' && KOS_ID='$kos_id'");
        }
    }

    function back2pool($pp_dat)
    {
        $result = DB::select("SELECT U_BELEG_NR, U_POS FROM POS_POOL WHERE PP_DAT='$pp_dat' LIMIT 0,1");
        if (!empty($result)) {
            $row = $result[0];
            $U_BELEG_NR = $row ['U_BELEG_NR'];
            $U_POS = $row ['U_POS'];
            DB::update("UPDATE KONTIERUNG_POSITIONEN SET WEITER_VERWENDEN='1' WHERE AKTUELL='1' && BELEG_NR='$U_BELEG_NR' && POSITION='$U_POS' && WEITER_VERWENDEN='0'");
            /* Löschen aus dem Unterpool */
            DB::delete("DELETE FROM POS_POOL WHERE PP_DAT='$pp_dat'");
        }
    }

    /* Importfunktion HWPWIN Projekttransfer XML */
    function test_xml()
    {
        echo "TESTXML Hr. Gühler";
        $Kategorien = simplexml_load_file('simple.XML');
        // Name, ID und link der ersten Kategorie ausgeben
        echo "<pre>";
        $anzahl_pos = count($Kategorien [0]->Dokument);
        echo "XML-Datei hat $anzahl_pos Positionen<br>";
        // print_r($Kategorien->Dokument[0]);
        // print_r($Kategorien);
        // print_r($Kategorien->Dokument);
        print_r($Kategorien->TLeistung [0]);
        // print_r($Kategorien->Dokument[1]);
        /*
		 * print_r($Kategorien->Positionen[0]->TTextPos);
		 * echo "<hr>";
		 * print_r($Kategorien->Positionen->TArtikel[1]);
		 */
    }

    /* Importfunktion HWPWIN Projekttransfer XML */
    function get_ugl_arr($tmp_datei)
    {
        // echo "TESTXML Hr. Gühler";
        $datei = file_get_contents("$tmp_datei");
        $erste_2_zeichen = substr($datei, 0, 2);
        /* Prüfen ob Version 4.00 der UGL Datei - Unielektro, dann 1. zeile löschen bzw. bis KOP */
        if ($erste_2_zeichen == 'RG') {
            $anfang_position_KOP = strpos($datei, 'KOP');
            $datei = substr($datei, $anfang_position_KOP);
        }

        $ths = new detail ();

        /* Kopfdaten */
        $ths->kop = substr($datei, 0, 3);
        $ths->knr = substr($datei, 3, 10);
        $ths->lnr = substr($datei, 13, 10); // 23
        $ths->a_art = substr($datei, 23, 2); // TB-Abrufauftrag, AN-Preisanfrage, BE-Bestellung, PA-Preisangebot, AB-Auftragsbestätigung
        $ths->a_nr_hw = substr($datei, 25, 15); // Anfragenummer des HW
        $ths->kundentext = substr($datei, 40, 50); // Kundenauftragstext
        $ths->vorgangsnr_gh = substr($datei, 90, 15); // Vorgangsnummer des GH
        $ths->datum = substr($datei, 105, 8);
        $ths->datum_j = substr($ths->datum, 0, 4);
        $ths->datum_m = substr($ths->datum, 4, 2);
        $ths->datum_t = substr($ths->datum, 6, 2);
        $ths->datum_d = "$ths->datum_t.$ths->datum_m.$ths->datum_j";
        $ths->waehrung = substr($datei, 113, 3);
        $ths->version = substr($datei, 116, 5);
        $ths->verantw = substr($datei, 121, 40);

        /* Positionsdaten */
        $ths->verantw = substr($datei, 121, 40);
        $anz_pos = preg_match_all('/POA/i', $datei, $arrResult);

        $pos_arr = explode('POA', $datei);

        for ($a = 1; $a <= $anz_pos; $a++) {
            $position = $pos_arr [$a];
            $ths->positionen_arr [$a] = $this->position_filtern($position);
        }
        $array = ( array )$ths;
        return $array;
    }

    function position_filtern($pos)
    {
        $pos = 'POA' . $pos;
        $arr ['POA'] = substr($pos, 0, 3);
        $arr ['POSNRHW'] = substr($pos, 3, 10);
        $arr ['POSNRGH'] = substr($pos, 13, 10);
        $arr ['ARTIKELNR'] = substr($pos, 23, 15);
        $arr ['MENGE'] = substr($pos, 38, 11);
        $arr ['ARTBEZ1'] = substr($pos, 49, 40);
        $arr ['ARTBEZ2'] = substr($pos, 89, 40);
        $arr ['POS_BRUTTO'] = substr($pos, 129, 11); // 2 nachkommastellen
        $arr ['PE'] = substr($pos, 140, 1); // null leer Stk, 2=10stk, 3=100, 4=1000
        $arr ['POS_NETTO'] = substr($pos, 141, 11); // 2 nachkommastellen
        $arr ['RABATT1'] = substr($pos, 152, 5); // Info
        $arr ['RABATT2'] = substr($pos, 157, 5); // Info
        $arr ['LV_NR'] = substr($pos, 162, 18); // LV-Nummer
        $arr ['POS_ART'] = substr($pos, 180, 1); // Alternativpos leer=iriginal, A=Alternativ
        $arr ['POS_TYP'] = substr($pos, 181, 1); // Positionstyp = J=Jumbo, U=Jumbounterpos, H=regular Art Pos
        return $arr;
    }

    function form_import_ugl()
    {
        $f = new formular ();
        $p = new partners ();
        echo "<form method=\"post\" enctype=\"multipart/form-data\">";
        $f->hidden_feld("aussteller_typ", "Partner");
        $p->partner_dropdown(' Ausgestellt von ', 'aussteller_id', 'aussteller');
        $f->hidden_feld("empfaenger_typ", "Partner");
        $p->partner_dropdown(' Ausgestellt an ', 'empfaenger_id', 'empfaenger');
        $f->text_feld('Rechnungs- bzw. Angebotsnummer', 'rnr', '', 29, 'rnr', '');
        $f->text_feld('Skonto in %', 'skonto', '', 1, 'skonto', '');
        $d_h = date("d.m.Y");
        $f->datum_feld('Rechnungsdatum', 'r_datum', $d_h, 'r_datum');
        $f->datum_feld('Eingangsdatum', 'eingangsdatum', $d_h, 'eingangsdatum');
        $f->datum_feld('Fällig', 'faellig', $d_h, 'faellig');
        $f->datum_feld('Leistungsanfang', 'servicetime_from', "", 'servicetime_from');
        $f->datum_feld('Leistungsende', 'servicetime_to', "", 'servicetime_to');

        $f->text_bereich("Kurzbeschreibung", "kurzbeschreibung", "", "50", "10", "kb");
        ?>
        <div class="file-field input-field">
            <div class="btn">
                <span>Datei</span>
                <input type="file" name="Datei" size="50" maxlength="100000">
            </div>
            <div class="file-path-wrapper">
                <input class="file-path validate" type="text" placeholder="Laden Sie eine UGL Datei hoch.">
            </div>
        </div>
        <?php
        $f->hidden_feld('option', 'ugl_sent');
        $f->send_button('btn_send', 'Hochladen');
        echo "</form>";
    }

    function form_import_csv()
    {
        $f = new formular ();
        $p = new partners ();
        echo "<form method=\"post\" enctype=\"multipart/form-data\">";
        $f->hidden_feld("aussteller_typ", "Partner");
        $p->partner_dropdown(' Ausgestellt von ', 'aussteller_id', 'aussteller');
        $f->hidden_feld("empfaenger_typ", "Partner");
        $p->partner_dropdown(' Ausgestellt an ', 'empfaenger_id', 'empfaenger');
        $this->drop_rechnungs_typen('Belegtyp wählen', 'beleg_typ', 'beleg_typ', '', 'Angebot');
        $f->text_feld('Skonto in %', 'skonto', '', 1, 'skonto', '');
        $d_h = date("d.m.Y");
        $f->datum_feld('Rechnungsdatum', 'r_datum', $d_h, 'r_datum');
        $f->datum_feld('Eingangsdatum', 'eingangsdatum', $d_h, 'eingangsdatum');
        $f->datum_feld('Fällig', 'faellig', $d_h, 'faellig');
        $f->datum_feld('Leistungsanfang', 'servicetime_from', '', 'servicetime_from');
        $f->datum_feld('Leistungsende', 'servicetime_to', '', 'servicetime_to');
        $f->text_bereich("Kurzbeschreibung", "kurzbeschreibung", "", "50", "10", "kb");
        ?>
        <div class="file-field input-field">
            <div class="btn">
                <span>Datei</span>
                <input type="file" name="Datei" size="50" maxlength="100000">
            </div>
            <div class="file-path-wrapper">
                <input class="file-path validate" type="text" placeholder="Laden Sie eine CSV Datei hoch.">
            </div>
        </div>
        <?php
        $f->hidden_feld('option', 'csv_sent');
        $f->send_button('btn_send', 'Hochladen');
        echo "</form>";
    }

    function form_kosten_einkauf()
    {
        $form = new formular ();
        $form->erstelle_formular('Kosten finden', NULL);
        $b = new buchen ();
        $js_typ = "onchange=\"list_kostentraeger('list_kostentraeger', this.value)\"";
        $b->dropdown_kostentreager_typen('Kostenträgertyp', 'kostentraeger_typ', 'kostentraeger_typ', $js_typ);
        $js_id = "";
        $b->dropdown_kostentreager_ids('Kostenträger', 'kostentraeger_id', 'dd_kostentraeger_id', $js_id);
        $p = new partner ();
        $p->partner_dropdown('Rechnungsempfänger wählen', 'empf_id', 'r_id');
        $form->send_button("submit_kostenkonto", "Suchen");
        $form->hidden_feld("option", "kosten_einkauf_send");
        $form->hidden_feld("empf_typ", "Partner");
        $form->ende_formular();
    }

    function kosten_einkauf($kos_typ, $kos_id, $empf_typ, $empf_id)
    {
        $r = new rechnung ();
        $arr = $this->kosten_einkauf_arr($kos_typ, $kos_id, $empf_typ, $empf_id);
        $anz = count($arr);
        echo "<table>";
        $summe_g = 0;
        for ($a = 0; $a < $anz; $a++) {
            $r = new rechnung ();
            $beleg_nr = $arr [$a] ['BELEG_NR'];
            $kurzinfo = $arr [$a] ['KURZBESCHREIBUNG'];
            $summe = $arr [$a] ['SUMME_G'];
            $summe_a = nummer_punkt2komma_t($summe);
            $summe_g += $summe;
            $r->rechnung_grunddaten_holen($beleg_nr);
            $link = "<a href='" . route('web::rechnungen::legacy', ['option' => 'rechnung_kontieren', 'belegnr' => $beleg_nr]) . "'>$beleg_nr</a>";
            echo "<tr><td>$link</td><td>$kurzinfo</td><td>$summe_a</td></tr>";
        }
        $summe_g = nummer_punkt2komma_t($summe_g);
        echo "<tr><td></td><td></td><td>$summe_g</td></tr>";
        echo "</table>";
    }

    function kosten_einkauf_arr($kos_typ, $kos_id, $empf_typ, $empf_id)
    {
        $abfrage = "SELECT RECHNUNGEN . * , SUM(GESAMT_SUMME/100*(100-RABATT_SATZ)) AS SUMME_G
FROM `KONTIERUNG_POSITIONEN` , RECHNUNGEN
WHERE `KOSTENTRAEGER_TYP` LIKE '$kos_typ'
AND `KOSTENTRAEGER_ID` = '$kos_id'
AND KONTIERUNG_POSITIONEN.AKTUELL = '1' && RECHNUNGEN.AKTUELL = '1' && KONTIERUNG_POSITIONEN.BELEG_NR = RECHNUNGEN.BELEG_NR && EMPFAENGER_TYP = '$empf_typ' && EMPFAENGER_ID = '$empf_id' && RECHNUNGSTYP = 'Rechnung'
GROUP BY BELEG_NR
ORDER BY RECHNUNGSNUMMER, POSITION ASC";

        $result = DB::select($abfrage);
        if (!empty($result)) {
            return $result;
        }
    }

    function ibm850_encode($str)
    {
        $text = iconv("CP850", "UTF-8", $str);
        return $text;
    }

    function form_teil_rg_hinzu($beleg_id)
    {
        $form = new formular ();
        $form->erstelle_formular('Teilrechnungen wählen', '');
        $form->hidden_feld("option", "send_teil_rg");
        $form->hidden_feld("beleg_id", "$beleg_id");
        $this->rechnung_grunddaten_holen($beleg_id);
        $this->list_teil_rg_in($this->rechnungs_empfaenger_typ, $this->rechnungs_empfaenger_id, $this->rechnungs_aussteller_typ, $this->rechnungs_aussteller_id, 'Teilrechnung', $beleg_id);
        $this->list_teil_rg($this->rechnungs_empfaenger_typ, $this->rechnungs_empfaenger_id, $this->rechnungs_aussteller_typ, $this->rechnungs_aussteller_id, 'Teilrechnung', 'tr', 'tr_ids[]', 'Teilrechnugs wählen');
        $form->send_button("submit_trg", "Hinzufügen");
        $form->ende_formular();
    }

    function list_teil_rg_in($empf_typ, $empf_id, $aus_typ, $aus_id, $r_typ, $beleg_id)
    {
        $arr = $this->get_teil_rg_arr_in($empf_typ, $empf_id, $aus_typ, $aus_id, $r_typ);
        if (!empty($arr)) {
            $anz = count($arr);
            echo "<table class=\"sortable\">";
            echo "<thead><tr><th colspan=\"5\">BEREITS HINZUGEFÜGT</th></tr></thead>";
            for ($a = 0; $a < $anz; $a++) {
                $t_beleg_id = $arr [$a] ['BELEG_NR'];
                $rnr = $arr [$a] ['RECHNUNGSNUMMER'];
                $info = $arr [$a] ['KURZBESCHREIBUNG'];
                $datum = date_mysql2german($arr [$a] ['RECHNUNGSDATUM']);
                $netto = $arr [$a] ['NETTO'];
                $link_loeschen = "<a href='" . route('web::rechnungen::legacy', ['option' => 'teil_rg_loeschen', 'beleg_id' => $beleg_id, 't_beleg_id' => $t_beleg_id]) . "'>Entfernen</a>";
                echo "<tr><td>$rnr</td><td>$datum</td><td>$info</td><td>Netto: $netto €</td><td>$link_loeschen</td></tr>";
            }
            echo "</table>";
        } else {
            hinweis_ausgeben("Bisher keine $r_typ hinzugefügt");
        }
    }

    function get_teil_rg_arr_in($empf_typ, $empf_id, $aus_typ, $aus_id, $r_typ)
    {
        $result = DB::select("SELECT * FROM RECHNUNGEN WHERE EMPFAENGER_TYP='$empf_typ' && EMPFAENGER_ID='$empf_id'  && AUSSTELLER_ID='$aus_id' && AUSSTELLER_TYP='$aus_typ' && RECHNUNGSTYP='$r_typ' && AKTUELL = '1' && BELEG_NR  IN (SELECT TEIL_R_ID FROM RECHNUNGEN_SCHLUSS WHERE AKTUELL='1')ORDER BY RECHNUNGSDATUM DESC");
        return $result;
    }

    function list_teil_rg($empf_typ, $empf_id, $aus_typ, $aus_id, $r_typ, $id, $name, $label)
    {
        $arr = $this->get_teil_rg_arr($empf_typ, $empf_id, $aus_typ, $aus_id, $r_typ);
        if (!empty($arr)) {
            $anz = count($arr);
            echo "<select class=\"select_rg\" name=\"$name\" id=\"$id\" size=\"20\" multiple>";
            for ($a = 0; $a < $anz; $a++) {
                $t_beleg_id = $arr [$a] ['BELEG_NR'];
                $rnr = $arr [$a] ['RECHNUNGSNUMMER'];
                $info = $arr [$a] ['KURZBESCHREIBUNG'];
                $datum = date_mysql2german($arr [$a] ['RECHNUNGSDATUM']);
                $netto = $arr [$a] ['NETTO'];
                echo "<option value=\"$t_beleg_id\">$rnr - $datum - $info - Netto: $netto €</option>";
            }
            echo "</select>";
        } else {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\InfoMessage("Keine $r_typ zum Hinzufügen vorhanden")
            );
        }
    }

    /* Die als Teilrg schon hinzugefügt sind */

    function get_teil_rg_arr($empf_typ, $empf_id, $aus_typ, $aus_id, $r_typ)
    {
        $result = DB::select("SELECT * FROM RECHNUNGEN WHERE EMPFAENGER_TYP='$empf_typ' && EMPFAENGER_ID='$empf_id'  && AUSSTELLER_ID='$aus_id' && AUSSTELLER_TYP='$aus_typ' && RECHNUNGSTYP='$r_typ' && AKTUELL = '1' && BELEG_NR NOT IN (SELECT TEIL_R_ID FROM RECHNUNGEN_SCHLUSS WHERE AKTUELL='1')ORDER BY RECHNUNGSDATUM DESC");
        return $result;
    }

    function teilrechnungen_hinzu($beleg_id, $tr_ids_arr)
    {
        if (is_numeric($beleg_id) && is_array($tr_ids_arr)) {

            $anz = count($tr_ids_arr);
            for ($a = 0; $a < $anz; $a++) {
                $t_beleg_id = $tr_ids_arr [$a];
                $this->teilrechnung_db($beleg_id, $t_beleg_id);
            }
        } else {
            echo "Eingabe fehlerhaft Error: xhshdhhd800000";
        }
    }

    function teilrechnung_db($beleg_id, $t_beleg_id)
    {
        $id = last_id2('RECHNUNGEN_SCHLUSS', 'ID') + 1;
        $db_abfrage = "INSERT INTO RECHNUNGEN_SCHLUSS VALUES (NULL, '$id', '$beleg_id', '$t_beleg_id', '1')";
        DB::insert($db_abfrage);

        /* Protokollieren */
        $last_dat = DB::getPdo()->lastInsertId();
        protokollieren('RECHNUNGEN_SCHLUSS', $last_dat, '');
        /* Ausgabe weil speichern erfolgreich */
        echo "Teilrechnungen wurden hinzugefügt.";
    }

    function teilrechnungen_loeschen($beleg_id, $t_beleg_id)
    {
        $db_abfrage = "UPDATE RECHNUNGEN_SCHLUSS SET AKTUELL='0' WHERE SCHLUSS_R_ID='$beleg_id' && TEIL_R_ID='$t_beleg_id'";
        DB::update($db_abfrage);

        /* Protokollieren */
        $last_dat = DB::getPdo()->lastInsertId();
        protokollieren('RECHNUNGEN_SCHLUSS', $last_dat, $last_dat);
        /* Ausgabe weil speichern erfolgreich */
        echo "Teilrechnung wurde entfernt.";
    }

    function form_vg_rechnungen($objekt_id, $partner_id)
    {
        $o = new objekt ();
        $o->get_objekt_infos($objekt_id);
        echo $o->objekt_kurzname;
        echo "<br>";
        echo $o->objekt_eigentuemer;
        $ein_arr = $o->einheiten_objekt_arr($objekt_id);
        $anz_e = count($ein_arr);

        for ($a = 0; $a < $anz_e; $a++) {
            $einheit_id = $ein_arr [$a] ['EINHEIT_ID'];
            $einheit_typ = $ein_arr [$a] ['TYP'];
            // echo $einheit_id;
            $weg = new weg ();
            $et_arr = $weg->get_eigentuemer_arr($einheit_id);
            if (!empty($et_arr)) {
                $le_et = count($et_arr) - 1;
                $ein_arr [$a] ['ET_ID'] = $et_arr [$le_et] ['ID'];
                $ein_arr [$a] ['R_EMPFAENGER_TYP'] = 'Eigentuemer';
                $empf_typen [] = 'Eigentuemer';
            } else {
                $ein_arr [$a] ['ET_ID'] = $o->objekt_eigentuemer_id;
                $ein_arr [$a] ['R_EMPFAENGER_TYP'] = 'Partner';
                $empf_typen [] = 'Partner';
            }

            $n_ein_arr [$einheit_typ] [] = $ein_arr [$a];
        } // end for

        unset ($ein_arr);

        $empf_kat = array_unique($empf_typen);

        $keys = array_keys($n_ein_arr);
        $anz_keys = count($keys);

        if (in_array('Eigentuemer', $empf_kat)) {

            for ($k = 0; $k < $anz_keys; $k++) {
                $key = $keys [$k];

                $f = new formular ();
                $f->erstelle_formular("Rechnungen für $key", null);
                if ($key == 'Stellplatz' or $key == 'Keller') {
                    $f->text_feld('Bruttobetrag pro Einheit', 'brutto', '8,00', 10, 'brutto', null);
                } else {
                    $f->text_feld('Bruttobetrag pro Einheit', 'brutto', '14,99', 10, 'brutto', null);
                }
                $f->text_bereich('Kurztext (Einheit wird automatisch hinzugefügt', 'kurztext', 'Verwaltergebühr', 50, 2, 'kurztext');
                echo "<table class=\"sortable\">";
                echo "<tr><th>EINHEIT</th><th>EMPFÄNGER</th><th>BEZ</th></tr>";
                $anz_e = count($n_ein_arr [$key]);
                for ($a = 0; $a < $anz_e; $a++) {
                    $et_id = $n_ein_arr [$key] [$a] ['ET_ID'];
                    $r_empf_typ = $n_ein_arr [$key] [$a] ['R_EMPFAENGER_TYP'];

                    $einheit_kn = $n_ein_arr [$key] [$a] ['EINHEIT_KURZNAME'];
                    $r = new rechnung ();
                    $e_bez = $r->kostentraeger_ermitteln($r_empf_typ, $et_id);
                    echo "<tr><td>";
                    $f->check_box_js1('check[]', 'check', $a, $einheit_kn, '', 'checked');
                    echo "</td><td>$r_empf_typ $et_id</td><td>$e_bez</td></tr>";

                    $f->hidden_feld("EMPF_TYP[]", $n_ein_arr [$key] [$a] ['R_EMPFAENGER_TYP']);
                    $f->hidden_feld("EMPF_ID[]", $n_ein_arr [$key] [$a] ['ET_ID']);
                    $f->hidden_feld("EINHEITEN[]", $n_ein_arr [$key] [$a] ['EINHEIT_ID']);
                } // end for $a
                echo "</table>";

                $f->hidden_feld('typ', $key);
                $f->hidden_feld('option', 'rgg');
                $ko = new kontenrahmen ();
                $ko->dropdown_kontorahmenkonten('Kostenkonto', 'kostenkonto', 'kostenkonto', 'Geldkonto', session()->get('geldkonto_id'), '');
                $f->check_box_js('sepa', '1', 'In SEPA-Überweisungen vorbereiten', '', 'checked');
                $f->send_button('btn_snd', "$anz_e Einzelrechnungen für $key erstellen");
                $f->ende_formular();
            } // end for $k
        }  // Ende wenn verschiedene Empfänger / Eigentümer
        else {
            $f = new formular ();
            $f->erstelle_formular("Gesamtrechnung", null);
            $f->text_bereich('Kurztext', 'kurztext', 'Verwaltergebühr', 50, 2, 'kurztext');
            $f->hidden_feld('empf_typ', 'Partner');;
            $f->hidden_feld('empf_id', $o->objekt_eigentuemer_id);

            for ($k = 0; $k < $anz_keys; $k++) {
                $key = $keys [$k];
                $anz_e = count($n_ein_arr [$key]);
                $f->hidden_feld('mengen[]', $anz_e);
                // echo "$key $anz_e<br>";
                $f->hidden_feld('typ[]', $key);
                if ($key == 'Stellplatz' or $key == 'Keller') {

                    $g = $anz_e * 8;
                    $f->text_feld("$anz_e x Bruttobetrag pro $key = $g EUR", 'brutto[]', '8,00', 10, 'brutto', null);
                } else {
                    $g = $anz_e * 14.99;
                    $f->text_feld("$anz_e x Bruttobetrag pro $key = $g EUR", 'brutto[]', '14,99', 10, 'brutto', null);
                }
            }

            $f->hidden_feld('option', 'rgg_ob');
            $ko = new kontenrahmen ();
            $ko->dropdown_kontorahmenkonten('Kostenkonto', 'kostenkonto', 'kostenkonto', 'Geldkonto', session()->get('geldkonto_id'), '');
            $f->check_box_js('sepa', '1', 'Gesamtbetrag in SEPA-Überweisungen vorbereiten', '', 'checked');
            echo "<div class='input-field'>";
            $f->send_button('btn_snd', "Gesamtrechnung erstellen");
            echo "</div>";
            $f->ende_formular();
        }
    }

    function liste_beleg2rg()
    {
        $arr = $this->beleg_pool_arr();
        $anz = count($arr);
        echo "<table class='striped'>";
        echo "<thead><th>EMPFÄNGER</th><th>BELEG</th><th>INFO</th><th>BRUTTO</th><th>OPTIONEN</th></thead>";
        for ($a = 0; $a < $anz; $a++) {
            $p_id = $arr [$a] ['EMPF_P_ID'];
            $p = new partner ();
            $partner_name = $p->get_partner_name($p_id);
            $beleg_nr = $arr [$a] ['BELEG_NR'];
            $r = new rechnung ();
            $r->rechnung_grunddaten_holen($beleg_nr);
            $a_partner_name = $p->get_partner_name(session()->get('partner_id'));
            $link_rg = "<a href='" . route('web::rechnungen.show', ['id' => $beleg_nr]) . "'>$r->rechnungsnummer</a>";
            $link_rg_neu = "<a href='" . route('web::rechnungen::legacy', ['option' => 'neue_rg', 'belegnr' => $beleg_nr, 't_beleg_id' => $t_beleg_id, 'empf_p_id' => $p_id]) . "'>Neue RG von $a_partner_name erstellen</a>";
            if (session()->get('partner_id') == $r->rechnungs_aussteller_id) {
                echo "<tr><td>$partner_name</td><td>$link_rg</td><td>$r->kurzbeschreibung</td><td>$r->rechnungs_brutto</td><td>";
                echo $link_rg_neu . "<br>";
                echo "<a href=\"#destroy_$a\">Vorlage entfernen</a>";
                echo "<div id=\"destroy_$a\" class=\"modal\">";
                echo "<div class=\"modal-content\">";
                echo "<h4>Beleg \"$r->kurzbeschreibung\" entfernen?</h4>";
                echo "<p>Sie können Vorlagen, die aus Belegen vergangener Jahre erzeugt wurden <b>nicht</b> wieder erstellen.</p>";
                echo "</div>";
                echo "<div class=\"modal-footer\">";
                echo "<form method='post' action='" . route('web::rechnungen::belegpool.destroy', ['id' => $arr[$a]['DAT']]) . "'>";
                echo "<a href=\"javascript:;\" onclick=\"parentNode.submit();\" class='modal-action modal-close waves-effect waves-red btn-flat red-text'>Entfernen</a>";
                echo method_field('delete');
                echo "</form>";
                echo "<a href='' class=\"modal-action modal-close waves-effect waves-green btn-flat\">Abbrechen</a>";
                echo "</div>";
                echo "</div>";
                echo "</td></tr>";
            }
        }
        echo "</table>";
    }

    function beleg_pool_arr()
    {
        $result = DB::select("SELECT * FROM `BELEG2RG` ORDER BY EMPF_P_ID ASC");
        if (!empty($result)) {
            return $result;
        }
    }

    function form_beleg2pool()
    {
        if (request()->has('jahr')) {
            $jahr = request()->input('jahr');
        } else {
            $jahr = date("Y");
        }
        $f = new formular ();
        $f->erstelle_formular("Beleg einem Empfänger zuweisen", null);
        $this->drop_buchungsbelege(session()->get('partner_id'), $jahr, 'Beleg wählen', 'beleg_nr', 'beleg_nr', null, null);
        $p = new partners ();
        $p->partner_dropdown('Empfänger wählen', 'empf_p_id', 'empf_p_id');
        $f->hidden_feld('option', 'beleg_sent');
        $f->send_button('sndBtn', 'Zuweisen');
        $f->ende_formular();
    }

    function drop_buchungsbelege($p_id, $jahr, $beschreibung, $name, $id, $js, $selected_value)
    {
        $beleg_arr = $this->buchungsbelege_arr('alle', $jahr);
        if (!empty($beleg_arr)) {
            echo "<label for=\"$id\">$beschreibung</label>\n";
            echo "<select name=\"$name\" id=\"$id\" $js>\n";
            $anzahl_b = count($beleg_arr);
            for ($a = 0; $a < $anzahl_b; $a++) {
                $text = $beleg_arr [$a] ['KURZBESCHREIBUNG'];
                $rnr = $beleg_arr [$a] ['RECHNUNGSNUMMER'];
                $anr = $beleg_arr [$a] ['AUSTELLER_AUSGANGS_RNR'];
                $enr = $beleg_arr [$a] ['EMPFAENGER_EINGANGS_RNR'];
                $beleg_nr = $beleg_arr [$a] ['BELEG_NR'];
                $bez = "$rnr (RA:$anr-RE:$enr) - $text";
                if ($beleg_nr == $selected_value) {
                    echo "<option value=\"$beleg_nr\" selected>$bez</option>\n";
                } else {
                    echo "<option value=\"$beleg_nr\" >$bez</option>\n";
                }
            }
            echo "</select>\n";
        } else {
            echo "Fehler beim Lesen aus der DB";
        }
    }

    function buchungsbelege_arr($monat, $jahr)
    {
        if ($monat == 'alle') {
            if (session()->has('partner_id')) {
                $p_id = session()->get('partner_id');
                $result = DB::select("SELECT * FROM RECHNUNGEN WHERE EMPFAENGER_ID=AUSSTELLER_ID  && EMPFAENGER_TYP=AUSSTELLER_TYP && AUSSTELLER_ID='$p_id' && DATE_FORMAT( RECHNUNGSDATUM, '%Y' ) = '$jahr' && AKTUELL = '1' GROUP BY BELEG_NR ORDER BY AUSTELLER_AUSGANGS_RNR ASC");
            } else {
                $result = DB::select("SELECT * FROM RECHNUNGEN WHERE EMPFAENGER_ID=AUSSTELLER_ID  && EMPFAENGER_TYP=AUSSTELLER_TYP  && DATE_FORMAT( RECHNUNGSDATUM, '%Y' ) = '$jahr' && AKTUELL = '1' GROUP BY BELEG_NR ORDER BY AUSTELLER_AUSGANGS_RNR ASC");
            }
        } else {
            $result = DB::select("SELECT * FROM RECHNUNGEN WHERE EMPFAENGER_ID=AUSSTELLER_ID  && EMPFAENGER_TYP=AUSSTELLER_TYP && DATE_FORMAT( RECHNUNGSDATUM, '%Y-%m' ) = '$jahr-$monat' && AKTUELL = '1' GROUP BY BELEG_NR ORDER BY BELEG_NR DESC");
        }
        return $result;
    }

    function beleg2rg_db($p_id, $beleg_nr)
    {
        $db_abfrage = "INSERT INTO BELEG2RG VALUES (NULL, '$beleg_nr', '$p_id')";
        DB::insert($db_abfrage);

        /* Protokollieren */
        $last_dat = DB::getPdo()->lastInsertId();
        protokollieren('BELEG2RG', $last_dat, $alt_dat);
    }

    function rechnung_aus_beleg($p_id, $beleg_nr, $empf_p_id)
    {
        $r = new rechnung ();
        $r_org = new rechnung ();
        $r_org->rechnung_grunddaten_holen($beleg_nr);
        $letzte_belegnr = $r->letzte_beleg_nr() + 1;

        $jahr = date("Y");
        $datum = date("Y-m-d");
        $leistung_von = (new \Carbon\Carbon('first day of this month'))->toDateString();
        $leistung_bis = (new \Carbon\Carbon('last day of this month'))->toDateString();
        $letzte_aussteller_rnr = $r->letzte_aussteller_ausgangs_nr($p_id, 'Partner', $jahr, 'Rechnung') + 1;
        $letzte_aussteller_rnr = sprintf('%03d', $letzte_aussteller_rnr);
        $r->rechnungs_kuerzel = $r->rechnungs_kuerzel_ermitteln('Partner', $p_id, $datum);
        $rechnungsnummer = $r->rechnungs_kuerzel . ' ' . $letzte_aussteller_rnr . '-' . $jahr;
        $letzte_empfaenger_rnr = $r->letzte_empfaenger_eingangs_nr($empf_p_id, 'Partner', $jahr, 'Rechnung') + 1;
        $gk = new geldkonto_info ();
        $gk->geld_konto_ermitteln('Partner', $p_id, null, 'Kreditor');
        $faellig_am = tage_plus($datum, 10);
        $db_abfrage = "INSERT INTO RECHNUNGEN VALUES (NULL, '$letzte_belegnr', '$rechnungsnummer', '$letzte_aussteller_rnr', '$letzte_empfaenger_rnr', 'Rechnung', '$datum','$datum', '0','0.00','0.00', 'Partner', '$p_id','Partner', '$empf_p_id','1', '1', '0', '0', '1', '0', '0', '$faellig_am', '0000-00-00', '$r_org->kurzbeschreibung', '$gk->geldkonto_id', NULL, '$leistung_von', '$leistung_bis')";
        DB::insert($db_abfrage);
        /* Protokollieren */
        $last_dat = DB::getPdo()->lastInsertId();
        protokollieren('RECHNUNGEN', $last_dat, '0');

        /* Posititonen */
        $arr = $r->rechnungs_positionen_arr($beleg_nr);
        $anz_p = count($arr);
        for ($a = 0; $a < $anz_p; $a++) {
            $pos = $arr [$a] ['POSITION'];
            $art_nr = $arr [$a] ['ARTIKEL_NR'];
            $menge = $arr [$a] ['MENGE'];
            $preis = $arr [$a] ['PREIS'];
            $mwst = $arr [$a] ['MWST_SATZ'];
            $rab = $arr [$a] ['RABATT_SATZ'];
            $skonto = $arr [$a] ['SKONTO'];
            $preis_g = $arr [$a] ['GESAMT_NETTO'];

            $letzte_rech_pos_id = $r->get_last_rechnung_pos_id() + 1;

            $db_abfrage = "INSERT INTO RECHNUNGEN_POSITIONEN VALUES (NULL, '$letzte_rech_pos_id', '$pos', '$letzte_belegnr', '$beleg_nr','$p_id', '$art_nr', $menge,'$preis','$mwst', '$rab','$skonto', '$preis_g','1')";
            DB::insert($db_abfrage);
            /* Protokollieren */
            $last_dat = DB::getPdo()->lastInsertId();
            protokollieren('RECHNUNGEN_POSITIONEN', $last_dat, '0');

            $r2 = new rechnungen ();
            $kont_arr = $r2->get_kontierung_arr($beleg_nr, $pos);
            $anz_k = count($kont_arr);
            if ($anz_k < 1) {
                fehlermeldung_ausgeben("Position $pos ist nicht kontiert");
            } else {
                for ($p = 0; $p < $anz_k; $p++) {
                    $k_menge = $kont_arr [$p] ['MENGE'];
                    $k_preis = $kont_arr [$p] ['EINZEL_PREIS'];
                    $k_preis_g = $kont_arr [$p] ['GESAMT_SUMME'];
                    $k_mwst = $kont_arr [$p] ['MWST_SATZ'];
                    $k_skonto = $kont_arr [$p] ['SKONTO'];
                    $k_rabatt = $kont_arr [$p] ['RABATT_SATZ'];
                    $k_konto = $kont_arr [$p] ['KONTENRAHMEN_KONTO'];
                    $k_kos_typ = $kont_arr [$p] ['KOSTENTRAEGER_TYP'];
                    $k_kos_id = $kont_arr [$p] ['KOSTENTRAEGER_ID'];

                    /* Kontieren */
                    $kontierung_id = $r->get_last_kontierung_id() + 1;
                    $db_abfrage = "INSERT INTO KONTIERUNG_POSITIONEN VALUES (NULL, '$kontierung_id','$letzte_belegnr', '$pos','$k_menge', '$k_preis', '$k_preis_g', '$k_mwst', '$k_skonto', '$k_rabatt', '$k_konto', '$k_kos_typ', '$k_kos_id', '$datum', '$jahr', '0', '1')";
                    DB::insert($db_abfrage);

                    /* Protokollieren */
                    $last_dat = DB::getPdo()->lastInsertId();
                    protokollieren('KONTIERUNG_POSITIONEN', $last_dat, '0');
                } // end for2
                $r->rechnung_als_vollstaendig($letzte_belegnr);
            } // end if
        } // end for

        weiterleiten(route('web::rechnungen::legacy', ['option' => 'erfasste_rechnungen'], false));
    }

    function get_kontierung_arr($belegnr, $pos)
    {
        $result = DB::select("SELECT * FROM KONTIERUNG_POSITIONEN WHERE BELEG_NR='$belegnr' && POSITION='$pos' && AKTUELL='1' ORDER BY KONTIERUNG_DAT DESC LIMIT 0,1");
        if (!empty($result)) {
            return $result;
        }
    }
} // End class rechnungen
