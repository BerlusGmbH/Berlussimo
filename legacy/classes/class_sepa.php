<?php

class sepa
{
    public $mand;
    public $beguenstigter;
    public $IBAN1;
    public $BIC;
    public $BANKNAME_K;
    public $IBAN;
    public $BANKNAME;
    public $summe_frst;
    public $summe_rcur;
    public $footer_zahlungshinweis;
    public $bankname;
    public $geldkonto_bez;
    public $konto_beguenstigter;
    public $kontonummer;
    public $blz;
    public $institut;
    public $kredit_institut;
    public $geldkonto_bezeichnung;
    public $geldkonto_bezeichnung_kurz;
    public $sepa_summe;
    public $sepa_gk_id;

    function test_sepa()
    {
        $this->import_dtaustn();
        echo '<pre>';
    }

    function import_dtaustn($objekt_id = 41, $m_adatum = '', $m_udatum = '')
    {
        if ($m_adatum == '') {
            $m_adatum = '2014-02-01';
        }
        if ($m_udatum == '') {
            $m_udatum = '2014-02-01';
        }

        echo '<pre>';

        $result = DB::select("SELECT DETAIL_ZUORDNUNG_ID FROM `DETAIL` WHERE `DETAIL_NAME` LIKE 'Einzugsermächtigung' AND `DETAIL_INHALT` LIKE 'JA' AND `DETAIL_AKTUELL` = '1'");
        if (!empty($result)) {
            $arr = Array();
            $z = 0;
            foreach ($result as $row) {
                $mieter = ( object )$row;
                $mv_id = $mieter->DETAIL_ZUORDNUNG_ID;
                $mv = new mietvertraege ();
                $mv->get_mietvertrag_infos_aktuell($mv_id);
                if ($mv->mietvertrag_aktuell == '1' && $this->check_objekt_aktiv($mv->objekt_id) && $mv->objekt_id == $objekt_id) {
                    $o = new objekt ();
                    $o->objekt_informationen($mv->objekt_id);

                    $arr [$z] ['GLAEUBIGER_GK_ID'] = $o->geld_konten_arr [0] ['KONTO_ID'];
                    $arr [$z] ['BEGUENSTIGTER'] = $o->geld_konten_arr [0] ['BEGUENSTIGTER'];

                    $d = new detail ();
                    $arr [$z] ['GLAEUBIGER_ID'] = $d->finde_detail_inhalt('GELD_KONTEN', $arr [$z] ['GLAEUBIGER_GK_ID'], 'GLAEUBIGER_ID');

                    $arr [$z] ['MV_ID'] = $mv_id;
                    $arr [$z] ['NAME'] = $mv->ls_konto_inhaber;
                    $arr [$z] ['KONTONR'] = $mv->ls_konto_nummer;
                    $arr [$z] ['BLZ'] = $mv->ls_blz;
                    $arr [$z] ['EINZUGSART'] = $mv->ls_autoeinzugsart;
                    $arr [$z] ['M_REFERENZ'] = "MV" . $mv_id;
                    $arr [$z] ['IBAN'] = $mv->ls_iban;
                    $arr [$z] ['BIC'] = $mv->ls_bic;
                    $arr [$z] ['BANKNAME'] = $mv->ls_bankname_sep_k;
                    $arr [$z] ['ANSCHRIFT'] = "$mv->haus_strasse $mv->haus_nr, $mv->haus_plz $mv->haus_stadt";
                    if (!isset ($mv->haus_strasse)) {
                        throw new \App\Exceptions\MessageException(
                            new \App\Messages\ErrorMessage("MV nicht in Ordnung, strasse prüfen $mv_id")
                        );
                    }

                    $arr [$z] ['mietvertrag_aktuell'] = $mv->mietvertrag_aktuell;
                    print_r($mv);
                    $z++;
                } // end if MV aktuell
                // print_r($konto_info);
            }

            print_r($arr);
            $anz = count($arr);
            for ($a = 0; $a < $anz; $a++) {
                $last_id = last_id2('SEPA_MANDATE', 'M_ID') + 1;
                $m_r = $arr [$a] ['M_REFERENZ'];
                $g_id = $arr [$a] ['GLAEUBIGER_ID'];
                $g_gk_id = $arr [$a] ['GLAEUBIGER_GK_ID'];
                $beg = $arr [$a] ['BEGUENSTIGTER'];
                $name = $arr [$a] ['NAME'];
                $ans = $arr [$a] ['ANSCHRIFT'];
                $kto = $arr [$a] ['KONTONR'];
                $blz = $arr [$a] ['BLZ'];
                $iban = $arr [$a] ['IBAN'];
                $bic = $arr [$a] ['BIC'];
                $bank = $arr [$a] ['BANKNAME'];
                $eart = $arr [$a] ['EINZUGSART'];
                $mv_id = $arr [$a] ['MV_ID'];
                $sql = "INSERT INTO `SEPA_MANDATE` VALUES (NULL, '$last_id', '$m_r', '$g_id', '$g_gk_id', '$beg', '$name', '$ans', '$kto', '$blz', '$iban', '$bic', '$bank', '$m_udatum', '$m_adatum', '9999-12-31', 'WIEDERKEHREND', 'MIETZAHLUNG', '$eart', 'MIETVERTRAG', '$mv_id', '1');";
                echo "$sql<br>";
                DB::insert($sql);
            }
        }
    }

    /* sivac_iban('800101561', '10050000', 'DE'); */

    function check_objekt_aktiv($objekt_id)
    {
        $result = DB::select("SELECT * FROM `OBJEKT` WHERE `OBJEKT_ID`=$objekt_id && OBJEKT_AKTUELL='1'");
        return !empty($result);
    }

    function alle_mandate_anzeigen_kurz($nutzungsart = 'Alle')
    {
        if (!session()->has('geldkonto_id') && $nutzungsart != 'Alle') {
            throw new \App\Exceptions\MessageException(new \App\Messages\InfoMessage('Bitte wählen Sie ein Geldkonto.'));
        }
        $datum_heute = date("Y-m-d");
        if ($nutzungsart == 'Alle') {
            $result = DB::select("SELECT * FROM `SEPA_MANDATE` WHERE `AKTUELL` = '1' AND M_EDATUM>='$datum_heute' AND M_ADATUM<='$datum_heute' ORDER BY NAME ASC");
        } else {
            $gk_id = session()->get('geldkonto_id');
            $result = DB::select("SELECT * FROM `SEPA_MANDATE` WHERE `AKTUELL` = '1' && M_EDATUM>='$datum_heute' && M_ADATUM<='$datum_heute' AND NUTZUNGSART='$nutzungsart' && GLAEUBIGER_GK_ID='$gk_id' ORDER BY NAME ASC");
        }

        if (!empty($result)) {
            if ($nutzungsart == 'MIETZAHLUNG') {
                echo "<table class=\"sortable striped\">";
                echo "<thead><tr><th>NR</th><th>EINHEIT</th><th>Name</th><th>REF</th><th>NUTZUNG</th><th>EINZUGSART</th><th>Anschrift</th><th>IBAN DB</th><th>BIC</th></tr></thead>";
                $z = 0;
                $zz = 0;
                $summe_ziehen_alle = 0.00;
                $summe_saldo_alle = 0.00;
                $summe_diff_alle = 0.00;
                foreach ($result as $row) {
                    $z++;
                    $zz++; // Zeile

                    $row ['IBAN1'] = chunk_split($row ['IBAN'], 4, ' ');

                    // ######################
                    $mv = new mietvertraege ();
                    $mv->get_mietvertrag_infos_aktuell($row ['M_KOS_ID']);

                    $mandat_status = $this->get_mandat_seq_status($row ['M_REFERENZ'], $row ['IBAN']);
                    $link_nutzungen = "<a href='" . route('web::sepa::legacy', ['option' => 'mandat_nutzungen_anzeigen', 'm_ref' => $row['M_REFERENZ']]) . "'>$mandat_status</a>";
                    /* Saldo berechnen */

                    echo "<tr class=\"zeile$z\"><td>$zz.</td><td><a href='" . route('web::sepa::legacy', ['option' => 'mandat_edit_mieter', 'mref_dat' => $row['DAT']]) . "'>$mv->einheit_kurzname</a></td><td>$row[NAME]</td><td>$row[M_REFERENZ]</td><td>$link_nutzungen</td><td>$row[EINZUGSART]</td></td><td>$row[ANSCHRIFT]</td><td>$row[IBAN]<br>$row[IBAN1]</td><td>$row[BIC]</td></tr>";
                    $diff = 0.00;

                    /* Zeilenfarbetausch */
                    if ($z == 2) {
                        $z = 0;
                    }
                }

                echo "</table>";
            }
            /* ENDE MIETZAHLUNGEN */

            /* ANFANG RECHNUNGEN */
            if ($nutzungsart == 'RECHNUGEN') {
                echo "Ansicht LS für Rechnungen, folgt noch!!!";
            }
            /* ENDE RECHNUNGEN */
            /* ANFANG HAUSGELD */
            if ($nutzungsart == 'HAUSGELD') {
                echo "Ansicht LS für Hausgeld, folgt noch!!!<br>";
                echo "<table class=\"sortable\">";
                echo "<thead><tr><th>EINHEIT</th><th>Name</th><th>REF</th><th>NUTZUNG</th><th>EINZUGSART</th><th>ZIEHEN</th><th>SALDO</th><th>DIFF</th><th>Anschrift</th><th>IBAN</th><th>BIC</th></tr></thead>";
                $z = 0;
                $summe_ziehen_alle = 0.00;
                $summe_saldo_alle = 0.00;
                $summe_diff_alle = 0.00;
                foreach ($result as $row) {
                    $z++;
                    $mand = ( object )$row;
                    $mand->IBAN1 = chunk_split($mand->IBAN, 4, ' ');
                    $mandat_status = $this->get_mandat_seq_status($mand->M_REFERENZ, $mand->IBAN);
                    $link_nutzungen = "<a href='" . route('web::sepa::legacy', ['option' => 'mandat_nutzungen_anzeigen', 'm_ref' => $mand->M_REFERENZ]) . "'>$mandat_status</a>";

                    $weg = new weg ();
                    $einheit_id = $weg->get_einheit_id_from_eigentuemer($mand->M_KOS_ID);
                    $e = new einheit ();
                    $e->get_einheit_info($einheit_id);
                    $weg->get_eigentuemer_saldo($mand->M_KOS_ID, $einheit_id);

                    if ($mand->EINZUGSART == 'Aktuelles Saldo komplett') {
                        if ($weg->hg_erg < 0) {
                            $summe_zu_ziehen = substr($weg->hg_erg, 1);
                            $diff = $summe_zu_ziehen + $weg->hg_erg;
                        } else {
                            $summe_zu_ziehen = 0.00;
                            $diff = $summe_zu_ziehen + $weg->hg_erg;
                        }
                    }

                    if ($mand->EINZUGSART == 'Nur die Summe aus Vertrag') {

                        $summe_zu_ziehen = substr($weg->soll_aktuell, 1);
                        $diff = $summe_zu_ziehen + $weg->hg_erg;
                    }

                    $summe_zu_ziehen_a = nummer_punkt2komma_t($summe_zu_ziehen);
                    $summe_saldo_alle += $weg->hg_erg;
                    $summe_ziehen_alle += $summe_zu_ziehen;
                    $summe_diff_alle += $diff;
                    $diff_a = nummer_punkt2komma($diff);
                    echo "<tr class=\"zeile$z\"><td><a href='" . route('web::sepa::legacy', ['option' => 'mandat_edit_mieter', 'mref_dat' => $mand->DAT]) . "'>$e->einheit_kurzname</a></td><td>$mand->NAME</td><td>$mand->M_REFERENZ</td><td>$link_nutzungen</td><td>$mand->EINZUGSART</td></td><td>$summe_zu_ziehen_a</td><td>$weg->hg_erg_a</td><td>$diff_a</td><td>$mand->ANSCHRIFT</td><td>$mand->IBAN<br>$mand->IBAN1</td><td>$mand->BIC</td></tr>";
                    /* Zeilenfarbetausch */
                    if ($z == 2) {
                        $z = 0;
                    }
                }
                $summe_ziehen_alle_a = nummer_punkt2komma_t($summe_ziehen_alle);
                $summe_saldo_alle_a = nummer_punkt2komma_t($summe_saldo_alle);
                $summe_diff_alle_a = nummer_punkt2komma_t($summe_diff_alle);
                echo "<tfoot><tr><th colspan=\"5\"><b>SUMMEN</b></th><th><b>$summe_ziehen_alle_a</b></th><th>$summe_saldo_alle_a</th><th>$summe_diff_alle_a</th><th colspan=\"3\"></th></tr></tfoot>";
                echo "</table>";
            }

            if ($nutzungsart == 'Alle') {
                echo "Übersicht alle Mandate, in Arbeit!!!!";
            }

            if (isset ($summe_ziehen_alle) && $summe_ziehen_alle > 0.00) {
                $f = new formular ();
                $f->erstelle_formular('SEPA-Datei', '');
                $js = '';
                $f->check_box_js('sammelbetrag', '1', 'Sammelbetrag', $js, 'checked');
                $f->hidden_feld('option', 'sepa_download');
                $f->hidden_feld('nutzungsart', $nutzungsart);
                $f->send_button('Btn-SEPApdf', "PDF-Begleitzettell");
                $f->send_button('Button', "SEPA-Datei für $nutzungsart erstellen");
                $f->ende_formular();
            }

            unset ($row);
        } else {
            fehlermeldung_ausgeben("Keine Mandate für $nutzungsart in der Datenbank!");
        }
    }

    function get_mandat_seq_status($m_ref, $iban)
    {
        if (!$this->check_mandat_is_used($m_ref, $iban) == true) {
            return 'ERSTEINZUG';
        } else {
            return 'FOLGEEINZUG';
        }
    }

    function check_mandat_is_used($m_ref, $iban)
    {
        $result = DB::select("SELECT * FROM `SEPA_MANDATE_SEQ` WHERE `M_REFERENZ` = '$m_ref' AND IBAN='$iban' && `AKTUELL` = '1' LIMIT 0 , 1");
        return !empty($result);
    }

    function alle_mandate_anzeigen($nutzungsart = 'Alle')
    {
        if (!session()->has('geldkonto_id') && $nutzungsart != 'Alle') {
            session()->put('last_url', route('web::sepa::legacy', ['option' => 'mandate_mieter'], false));
            fehlermeldung_ausgeben('Geldkonto wählen');
            return;
        }
        $datum_heute = date("Y-m-d");
        if ($nutzungsart == 'Alle') {
            $result = DB::select("SELECT * FROM `SEPA_MANDATE` WHERE `AKTUELL` = '1' AND M_EDATUM>='$datum_heute' AND M_ADATUM<='$datum_heute' ORDER BY NAME ASC");
        } else {
            $gk_id = session()->get('geldkonto_id');
            $result = DB::select("SELECT * FROM `SEPA_MANDATE` WHERE `AKTUELL` = '1' && NUTZUNGSART='$nutzungsart' AND M_EDATUM>='$datum_heute' AND M_ADATUM<='$datum_heute' && GLAEUBIGER_GK_ID='$gk_id' ORDER BY NAME ASC");
        }

        $monat = date("m");
        $jahr = date("Y");

        if (!empty($result)) {
            if ($nutzungsart == 'MIETZAHLUNG') {
                echo "<table class=\"sortable striped\">";
                echo "<thead><tr><th>NR</th><th>EINHEIT</th><th>Name</th><th>REF</th><th>NUTZUNG</th><th>EINZUGSART</th><th>ZIEHEN</th><th>SALDO</th><th>SALDO NACH</th><th>Anschrift</th><th>IBAN DB</th><th>BIC</th></tr></thead>";
                $z = 0;
                $zz = 0;
                $datensaetze = 0;
                $summe_ziehen_alle = 0.00;
                $summe_saldo_alle = 0.00;
                $summe_diff_alle = 0.00;
                foreach ($result as $row) {
                    $z++;
                    $zz++; // Zeile

                    $row ['IBAN1'] = chunk_split($row ['IBAN'], 4, ' ');

                    // ######################
                    $mv = new mietvertraege ();
                    $mv->get_mietvertrag_infos_aktuell($row ['M_KOS_ID']);
                    $mandat_status = $this->get_mandat_seq_status($row ['M_REFERENZ'], $row ['IBAN']);
                    $link_nutzungen = "<a href='" . route('web::sepa::legacy', ['option' => 'mandat_nutzungen_anzeigen', 'm_ref' => $row['M_REFERENZ']]) . "'>$mandat_status</a>";
                    /* Saldo berechnen */
                    $mz = new miete ();
                    $mz->mietkonto_berechnung($row ['M_KOS_ID']);

                    if ($row ['EINZUGSART'] == 'Aktuelles Saldo komplett') {
                        if ($mz->erg < 0) {
                            $summe_zu_ziehen = substr($mz->erg, 1);
                            $diff = nummer_punkt2komma_t($summe_zu_ziehen + $mz->erg);
                            $datensaetze++;
                        } else {
                            $summe_zu_ziehen = 0.00;
                            $diff = $mz->erg;
                        }
                    }

                    if ($row ['EINZUGSART'] == 'Nur die Summe aus Vertrag') {

                        $mk = new mietkonto ();
                        $summe_zu_ziehen_arr = explode('|', $mk->summe_forderung_monatlich($row ['M_KOS_ID'], $monat, $jahr));

                        $summe_zu_ziehen = $summe_zu_ziehen_arr [0];
                        $diff = $summe_zu_ziehen + $mz->erg;
                        $datensaetze++;
                    }

                    if ($row ['EINZUGSART'] == 'Ratenzahlung') {
                        $mk = new mietkonto ();
                        $summe_zu_ziehen_arr = explode('|', $mk->summe_forderung_monatlich($row ['M_KOS_ID'], $monat, $jahr));

                        $summe_raten = $mk->summe_rate_monatlich($row ['M_KOS_ID'], $monat, $jahr);
                        $summe_zu_ziehen = $summe_zu_ziehen_arr [0] + $summe_raten;
                        $diff = $summe_zu_ziehen + $mz->erg;
                        $datensaetze++;
                    }

                    $summe_zu_ziehen_a = nummer_punkt2komma_t($summe_zu_ziehen);
                    $summe_saldo_alle += $mz->erg;
                    $summe_ziehen_alle += $summe_zu_ziehen;
                    $summe_diff_alle += $diff;
                    echo "<tr class=\"zeile$z\"><td>$zz.</td><td><a href='" . route('web::sepa::legacy', ['option' => 'mandat_edit_mieter', 'mref_dat' => $row['DAT']]) . "'>$mv->einheit_kurzname</a></td><td>$row[NAME]</td><td>$row[M_REFERENZ]</td><td>$link_nutzungen</td><td>$row[EINZUGSART]</td></td><td>$summe_zu_ziehen_a</td><td>$mz->erg</td><td>$diff</td><td>$row[ANSCHRIFT]</td><td>$row[IBAN]<br>$row[IBAN1]</td><td>$row[BIC]</td></tr>";
                    $mz->erg = 0.00;
                    $diff = 0.00;

                    /* Zeilenfarbetausch */
                    if ($z == 2) {
                        $z = 0;
                    }
                }
                $summe_ziehen_alle_a = nummer_punkt2komma_t($summe_ziehen_alle);
                $summe_saldo_alle_a = nummer_punkt2komma_t($summe_saldo_alle);
                $summe_diff_alle_a = nummer_punkt2komma_t($summe_diff_alle);
                echo "<tfoot><tr><th colspan=\"6\"><b>SUMMEN ANZAHL DS: $datensaetze</b></th><th><b>$summe_ziehen_alle_a</b></th><th>$summe_saldo_alle_a</th><th>$summe_diff_alle_a</th><th colspan=\"5\"></th></tr></tfoot>";

                echo "</table>";
            }
            /* ENDE MIETZAHLUNGEN */

            /* ANFANG RECHNUNGEN */
            if ($nutzungsart == 'RECHNUNGEN') {
                echo "Ansicht LS für Rechnungen, folgt noch!!!";
            }
            /* ENDE RECHNUNGEN */
            /* ANFANG HAUSGELD */
            if ($nutzungsart == 'HAUSGELD') {
                echo "<table class=\"sortable striped\">";
                echo "<thead><tr><th>EINHEIT</th><th>Name</th><th>REF</th><th>NUTZUNG</th><th>EINZUGSART</th><th>ZIEHEN</th><th>SALDO</th><th>DIFF</th><th>Anschrift</th><th>IBAN</th><th>BIC</th></tr></thead>";
                $z = 0;
                $summe_ziehen_alle = 0.00;
                $summe_saldo_alle = 0.00;
                $summe_diff_alle = 0.00;
                foreach ($result as $row) {
                    $z++;
                    $mand = ( object )$row;
                    $mand->IBAN1 = chunk_split($mand->IBAN, 4, ' ');
                    $mandat_status = $this->get_mandat_seq_status($mand->M_REFERENZ, $mand->IBAN);
                    $link_nutzungen = "<a href='" . route('web::sepa::legacy', ['option' => 'mandat_nutzungen_anzeigen', 'm_ref' => $mand->M_REFERENZ]) . "'>$mandat_status</a>";

                    $weg = new weg ();
                    $einheit_id = $weg->get_einheit_id_from_eigentuemer($mand->M_KOS_ID);
                    $e = new einheit ();
                    $e->get_einheit_info($einheit_id);
                    $weg->get_eigentuemer_saldo($mand->M_KOS_ID, $einheit_id);

                    if ($mand->EINZUGSART == 'Aktuelles Saldo komplett') {
                        if ($weg->hg_erg < 0) {
                            $summe_zu_ziehen = $weg->soll_aktuell;
                            $diff = 0.00;
                        } else {
                            $summe_zu_ziehen = 0.00;
                            $diff = 0.00;
                        }
                    }

                    if ($mand->EINZUGSART == 'Nur die Summe aus Vertrag') {

                        $summe_zu_ziehen = $weg->soll_aktuell;
                        $diff = 0.00;
                    }

                    $summe_zu_ziehen_a = nummer_punkt2komma_t($summe_zu_ziehen);
                    $summe_saldo_alle += $weg->hg_erg;
                    $summe_ziehen_alle += $summe_zu_ziehen;
                    $summe_diff_alle += $diff;
                    $diff_a = nummer_punkt2komma($diff);
                    echo "<tr class=\"zeile$z\"><td><a href='" . route('web::sepa::legacy', ['option' => 'mandat_edit_mieter', 'mref_dat' => $mand->DAT]) . "'>$e->einheit_kurzname</a></td><td>$mand->NAME</td><td>$mand->M_REFERENZ</td><td>$link_nutzungen</td><td>$mand->EINZUGSART</td></td><td>$summe_zu_ziehen_a</td><td>$weg->hg_erg_a</td><td>$diff_a</td><td>$mand->ANSCHRIFT</td><td>$mand->IBAN<br>$mand->IBAN1</td><td>$mand->BIC</td></tr>";
                    /* Zeilenfarbetausch */
                    if ($z == 2) {
                        $z = 0;
                    }
                }
                $summe_ziehen_alle_a = nummer_punkt2komma_t($summe_ziehen_alle);
                $summe_saldo_alle_a = nummer_punkt2komma_t($summe_saldo_alle);
                $summe_diff_alle_a = nummer_punkt2komma_t($summe_diff_alle);
                echo "<tfoot><tr><th colspan=\"5\"><b>SUMMEN</b></th><th><b>$summe_ziehen_alle_a</b></th><th>$summe_saldo_alle_a</th><th>$summe_diff_alle_a</th><th colspan=\"3\"></th></tr></tfoot>";
                echo "</table>";
            }

            if ($nutzungsart == 'Alle') {
                echo "Übersicht alle Mandate, in Arbeit!!!!";
            }

            if (isset ($summe_ziehen_alle) && $summe_ziehen_alle > 0.00) {
                $f = new formular ();
                $f->erstelle_formular('SEPA-Datei', '');
                $js = '';
                $f->check_box_js('sammelbetrag', '1', 'Sammelbetrag', $js, 'checked');
                echo "&nbsp;";
                $f->hidden_feld('option', 'sepa_download');
                $f->hidden_feld('nutzungsart', $nutzungsart);
                $f->send_button('Btn-SEPApdf', "PDF-Begleitzettell");
                echo "&nbsp;";
                $f->send_button('Button', "SEPA-Datei für $nutzungsart erstellen");
                $f->ende_formular();
            }

            unset ($row);
        } else {
            fehlermeldung_ausgeben("Keine Mandate für $nutzungsart in der Datenbank!");
        }
    }

    function form_mandat_mieter_neu($gk_id)
    {
        $f = new formular ();
        $mv = new mietvertraege ();
        $f->erstelle_formular('Neues Mietermandat erfassen', '');

        $gk = new gk ();

        $geld_konto_info = new geldkonto_info ();
        $geld_konto_info->geld_konto_details($gk_id);
        $f->hidden_feld('BEGUENSTIGTER', $geld_konto_info->konto_beguenstigter);

        $objekt_id = $gk->get_objekt_id($gk_id);
        if (!isset ($objekt_id)) {
            fehlermeldung_ausgeben("Objekt nicht gefunden.<br>Siehe Geldkontozuweisung zum Objekt.");
            return;
        }
        session()->put('objekt_id', $objekt_id);
        $d = new detail ();
        $glaeubiger_id = $d->finde_detail_inhalt('GELD_KONTEN', $gk_id, 'GLAEUBIGER_ID');
        if ($glaeubiger_id == false) {
            fehlermeldung_ausgeben('Zum Geldkonto wurde die Gläubiger ID nicht gespeichert, siehe DETAILS vomn GK');
            return;
        }
        $f->hidden_feld('GLAEUBIGER_ID', $glaeubiger_id);
        $f->text_feld_inaktiv('Begünstigter', 'BEGBEZ', $geld_konto_info->konto_beguenstigter, 35, 'BEGBEZ');
        $f->text_feld_inaktiv('Ihre GläubigerID', 'GLAEUBIGER_ID', $glaeubiger_id, 35, 'GLAEUBIGER_ID');

        $f->text_feld_inaktiv('Mandatsreferenz', 'M_REF', '', 35, 'M_REF');
        $js = "onclick=\"var show = document.getElementById('M_REF');show.value = 'MV' + this.value;\"";
        $this->dropdown_mieter($objekt_id, 'Mieter wählen', 'mv_id', 'mv_id', $js);
        $mv->autoeinzugsarten('Einzugsart', 'einzugsart', 'einzugsart');

        $f->text_feld("Kontoinhaber", "NAME", "", "50", 'NAME', '');
        $f->text_feld("Anschrift d. Kontoinhabers", "ANSCHRIFT", "", "50", 'ANSSCHRIFT', '');
        $f->text_feld("IBAN", "IBAN", "", "50", 'IBAN', '');
        $f->text_feld("BIC", "BIC", "", "50", 'BIC', '');
        $f->text_feld("Bank", "BANK", "", "50", 'BANK', '');
        $heute = date("d.m.Y");
        $f->datum_feld('Datum Unterschrift', 'M_UDATUM', $heute, 'M_UDATUM');
        $f->datum_feld('Datum Gültigkeit', 'M_ADATUM', $heute, 'A_UDATUM');
        $f->hidden_feld('GK_ID', $gk_id);
        $f->hidden_feld('M_KOS_TYP', 'Mietvertrag');
        $f->hidden_feld('option', 'mandat_mieter_neu_send');
        $f->send_button('Button', 'Mandat erstellen');
        $f->ende_formular();
        // $f->fieldset_ende();
    }

    function dropdown_mieter($objekt_id, $label, $name, $id, $js = '')
    {
        $ob = new objekt ();
        $e_array = $ob->einheiten_objekt_arr($objekt_id);
        if (empty($e_array)) {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\ErrorMessage("Keine Mieter in diesem Objekt OBJ_ID: $objekt_id")
            );
        }

        echo "<label for=\"$name\" id=\"label_$name\">$label</label><select name=\"$name\" size=\"1\" id=\"$id\"  $js>\n";
        $anz = count($e_array);
        $anz_mv = 0;
        for ($a = 0; $a < $anz; $a++) {
            $einheit_id = $e_array [$a] ['EINHEIT_ID'];
            $einheit_kn = $e_array [$a] ['EINHEIT_KURZNAME'];
            $einheit_typ = $e_array [$a] ['TYP'];

            if ($einheit_typ != 'Wohneigentum') {
                $e = new einheit ();
                $mv_id = $e->get_mietvertrag_id($einheit_id);
                if ($mv_id) {
                    $anz_mv++;
                    $mv = new mietvertraege ();
                    $mv->get_mietvertrag_infos_aktuell($mv_id);
                    if ($mv->mietvertrag_aktuell == '1') {
                        $mref = 'MV' . $mv_id;
                        if (!$this->check_m_ref($mref)) {
                            echo "<option value=\"$mv_id\">$einheit_kn | $mv->personen_name_string</option>\n";
                        }
                    }
                }
            }
        }

        if ($anz_mv == 0) {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\ErrorMessage("Keine Mieter in diesem Objekt.")
            );
        }
        echo "</select>\n";
    }

    function check_m_ref($mref)
    {
        $result = DB::select("SELECT * FROM `SEPA_MANDATE` WHERE `M_REFERENZ` = '$mref' AND M_EDATUM='9999-12-31' AND `AKTUELL` = '1' LIMIT 0 , 1");
        return !empty($result);
    }

    function form_mandat_hausgeld_neu($gk_id)
    {
        $f = new formular ();
        $mv = new mietvertraege ();
        $f->erstelle_formular('Neues Hausgeldmandat erfassen', '');

        $gk = new gk ();

        $geld_konto_info = new geldkonto_info ();
        $geld_konto_info->geld_konto_details($gk_id);
        $f->hidden_feld('BEGUENSTIGTER', $geld_konto_info->konto_beguenstigter);

        $objekt_id = $gk->get_objekt_id($gk_id);
        if (!isset ($objekt_id)) {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\ErrorMessage("Objekt nicht gefunden, siehe Geldkontozuweisung zum Objekt.")
            );
        }
        session()->put('objekt_id', $objekt_id);
        $d = new detail ();
        $glaeubiger_id = $d->finde_detail_inhalt('GELD_KONTEN', $gk_id, 'GLAEUBIGER_ID');
        if ($glaeubiger_id == false) {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\ErrorMessage('Zum Geldkonto wurde die Gläubiger ID nicht gespeichert. Siehe DETAILS vom GK.')
            );
        }
        $f->hidden_feld('GLAEUBIGER_ID', $glaeubiger_id);
        $f->text_feld_inaktiv('Begünstigter', 'BEGBEZ', $geld_konto_info->konto_beguenstigter, 35, 'BEGBEZ');
        $f->text_feld_inaktiv('Ihre GläubigerID', 'GLAEUBIGER_ID', $glaeubiger_id, 35, 'GLAEUBIGER_ID');

        $f->text_feld_inaktiv('Mandatsreferenz', 'M_REF', '', 35, 'M_REF');
        $js = "onclick=\"var show = document.getElementById('M_REF');show.value = 'WEG-ET' + this.value;\"";
        // $this->dropdown_mieter($objekt_id, 'Mieter wählen', 'mv_id', 'mv_id', $js);
        $this->dropdown_et_vorwahl('x', $objekt_id, "Eigentümer wählen OBJ_ID $objekt_id", 'mv_id', 'mv_id', $js);
        $mv->autoeinzugsarten('Einzugsart', 'einzugsart', 'einzugsart');

        $f->text_feld("Kontoinhaber", "NAME", "", "50", 'NAME', '');
        $f->text_feld("Anschrift d. Kontoinhabers", "ANSCHRIFT", "", "50", 'ANSSCHRIFT', '');
        $f->text_feld("IBAN", "IBAN", "", "50", 'IBAN', '');
        $f->text_feld("BIC", "BIC", "", "50", 'BIC', '');
        $f->text_feld("Bank", "BANK", "", "50", 'BANK', '');
        $heute = date("d.m.Y");
        $f->datum_feld('Datum Unterschrift', 'M_UDATUM', $heute, 'M_UDATUM');
        $f->datum_feld('Datum Gültigkeit', 'M_ADATUM', $heute, 'A_UDATUM');
        $f->hidden_feld('GK_ID', $gk_id);
        $f->hidden_feld('M_KOS_TYP', 'Eigentuemer');
        $f->hidden_feld('option', 'mandat_mieter_neu_send');
        $f->send_button('Button', 'Mandat erstellen');
        $f->ende_formular();
        // $f->fieldset_ende();
    }

    function dropdown_et_vorwahl($vorwahl_et_id, $objekt_id, $label, $name, $id, $js = '')
    {
        $weg = new weg ();
        $e_array = $weg->einheiten_weg_tabelle_arr($objekt_id);
        if (empty($e_array)) {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\ErrorMessage("Keine Eigentümer in diesem Objekt.")
            );
        }
        echo "<label for=\"$name\" id=\"label_$name\">$label</label><select name=\"$name\" size=\"1\" id=\"$id\"  $js>\n";
        $anz = count($e_array);
        $anz_et = 0;
        for ($a = 0; $a < $anz; $a++) {
            $einheit_id = $e_array [$a] ['EINHEIT_ID'];
            $e = new einheit ();
            $e->get_einheit_info($einheit_id);
            $weg->get_last_eigentuemer_namen($einheit_id);
            if ($weg->eigentuemer_namen2) {
                $anz_et++;
                $weg->get_last_eigentuemer($einheit_id);
                $eigentuemer_id = $weg->eigentuemer_id;

                if ($eigentuemer_id == $vorwahl_et_id) {
                    echo "<option value=\"$eigentuemer_id\" selected>$e->einheit_kurzname | $weg->eigentuemer_namen2</option>\n";
                } else {
                    echo "<option value=\"$eigentuemer_id\">$e->einheit_kurzname | $weg->eigentuemer_namen2</option>\n";
                }
            }
        }
        echo "</select>\n";
    }

    function form_mandat_mieter_edit($dat)
    {
        $this->get_mandat_infos($dat);

        $f = new formular ();
        $mv = new mietvertraege ();
        $f->erstelle_formular('Mietermandat ändern', '');
        $gk = new gk ();
        $objekt_id = $gk->get_objekt_id($this->mand->GLAEUBIGER_GK_ID);
        // $js = "onchange=\"var show = document.getElementById('M_REF');show.value = 'GK' + this.value;\"";
        $js = "onchange=\"get_detail_inhalt('GELD_KONTEN', this.value, 'GLAEUBIGER_ID', 'GLAEUBIGER_ID'); daj3('ajax/ajax_info.php?option=get_gk_infos&var=konto_beguenstigter&gk_id=' + this.value, 'BEGUENSTIGTER');\"";
        $gk->dropdown_geldkonten_alle_vorwahl('Referenzgeldkonto wählen', 'GK_ID', 'GK_ID', $this->mand->GLAEUBIGER_GK_ID, $js);
        $geld_konto_info = new geldkonto_info ();
        $geld_konto_info->geld_konto_details($this->mand->GLAEUBIGER_GK_ID);

        $f->text_feld("Gläubiger ID", "GLAEUBIGER_ID", $this->mand->GLAEUBIGER_ID, "50", 'GLAEUBIGER_ID', '');
        $f->text_feld('Begünstigter', 'BEGUENSTIGTER', $geld_konto_info->konto_beguenstigter, 35, 'BEGUENSTIGTER', '');

        $f->text_feld_inaktiv('Mandatsreferenz', 'M_REF', $this->mand->M_REFERENZ, 35, 'M_REF');

        if ($this->mand->NUTZUNGSART == 'MIETZAHLUNG') {
            $js = "onchange=\"var show = document.getElementById('M_REF');show.value = 'MV' + this.value; daj3('ajax/ajax_info.php?option=get_mv_infos&mv_id=' + this.value, 'info_feld_kostentraeger');\"\"";
            $this->dropdown_mieter_vorwahl($this->mand->M_KOS_ID, 'Mieter wählen', 'mv_id', 'mv_id', $js);
            $f->hidden_feld('M_KOS_TYP', 'Mietvertrag');
        }

        if ($this->mand->NUTZUNGSART == 'HAUSGELD') {
            $js = "onclick=\"var show = document.getElementById('M_REF');show.value = 'WEG-ET' + this.value;\"";
            $this->dropdown_et_vorwahl($this->mand->M_KOS_ID, $objekt_id, 'Eigentümer wählen', 'mv_id', 'mv_id', $js);
            $f->hidden_feld('M_KOS_TYP', 'Eigentuemer');
        }
        // $mv->autoeinzugsarten('Einzugsart', 'einzugsart', 'einzugsart');
        $mv->dropdown_autoeinzug_selected('Einzugsart', 'einzugsart', $this->mand->EINZUGSART);
        $f->text_feld("Kontoinhaber", "NAME", $this->mand->NAME, "50", 'NAME', '');
        $f->text_feld("Anschrift d. Kontoinhabers", "ANSCHRIFT", $this->mand->ANSCHRIFT, "50", 'ANSSCHRIFT', '');
        $f->text_feld("IBAN", "IBAN", $this->mand->IBAN, "50", 'IBAN', '');
        $f->text_feld("BIC", "BIC", $this->mand->BIC, "50", 'BIC', '');
        $f->text_feld("Bank", "BANK", $this->mand->BANKNAME, "50", 'BANK', '');
        $a_datum = date_mysql2german($this->mand->M_ADATUM);
        $u_datum = date_mysql2german($this->mand->M_UDATUM);
        $e_datum = date_mysql2german($this->mand->M_EDATUM);
        $f->datum_feld('Datum Unterschrift', 'M_UDATUM', $u_datum, 'M_UDATUM');
        $f->datum_feld('Datum Gültigkeit', 'M_ADATUM', $a_datum, 'M_UDATUM');
        $f->datum_feld('Datum Ablauf', 'M_EDATUM', $e_datum, 'M_EDATUM');
        $f->hidden_feld('KTO', $this->mand->KONTONR);
        $f->hidden_feld('BLZ', $this->mand->BLZ);
        $f->hidden_feld('option', 'mandat_mieter_edit_send');
        $f->send_button('btn_edit_mieter', 'Änderungen speichern');
        $f->ende_formular();
        echo "<div id=\"info_feld_kostentraeger\">";
        echo "</div>";
        // $f->fieldset_ende();
    }

    function get_mandat_infos($dat)
    {
        if (isset ($this->mand)) {
            unset ($this->mand);
        }
        $result = DB::select("SELECT * FROM `SEPA_MANDATE` WHERE `DAT` ='$dat'");
        if (!empty($result)) {
            $row = $result[0];
            $this->mand = ( object )$row;
        }
    }

    function dropdown_mieter_vorwahl($vorwahl_mv_id, $label, $name, $id, $js = '')
    {
        $e = new einheit ();
        $e_array = $e->liste_aller_einheiten();
        if (empty($e_array)) {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\ErrorMessage("Keine Mieter in diesem Objekt")
            );
        }

        echo "<label for=\"$name\" id=\"label_$name\">$label</label><select name=\"$name\" size=\"1\" id=\"$id\"  $js>\n";
        $anz = count($e_array);
        for ($a = 0; $a < $anz; $a++) {
            $einheit_id = $e_array [$a] ['EINHEIT_ID'];
            $einheit_kn = $e_array [$a] ['EINHEIT_KURZNAME'];
            $e = new einheit ();
            $mv_id = $e->get_mietvertrag_id($einheit_id);
            if ($mv_id) {
                $mv = new mietvertraege ();
                $mv->get_mietvertrag_infos_aktuell($mv_id);
                if ($mv->mietvertrag_aktuell == '1') {
                    if ($mv_id == $vorwahl_mv_id) {
                        echo "<option value=\"$mv_id\" selected>$einheit_kn | $mv->personen_name_string</option>\n";
                    } else {
                        echo "<option value=\"$mv_id\">$einheit_kn | $mv->personen_name_string</option>\n";
                    }
                }
            }
        }
        echo "</select>\n";
    }

    function check_m_ref_alle($mref)
    {
        $result = DB::select("SELECT * FROM `SEPA_MANDATE` WHERE `M_REFERENZ` = '$mref' AND `AKTUELL` = '1' LIMIT 0 , 1");
        return !empty($result);
    }

    function mandat_beenden($mv_id, $edatum)
    {
        $edatum = date_german2mysql($edatum);
        $result = DB::select("SELECT * FROM `SEPA_MANDATE` WHERE M_KOS_TYP LIKE 'MIETVERTRAG' AND M_KOS_ID = '$mv_id' AND AKTUELL = '1' AND M_EDATUM = '9999-12-31' LIMIT 0 , 1");
        if (!empty($result)) {
            DB::update("UPDATE `SEPA_MANDATE` SET AKTUELL='0' WHERE M_KOS_TYP LIKE 'MIETVERTRAG' AND M_KOS_ID = '$mv_id'");
            $sql = "INSERT INTO `SEPA_MANDATE`(M_ID, M_REFERENZ, GLAEUBIGER_ID, GLAEUBIGER_GK_ID, BEGUENSTIGTER, NAME, ANSCHRIFT, KONTONR, BLZ, IBAN, BIC, BANKNAME, M_UDATUM, M_ADATUM, M_EDATUM, M_ART, NUTZUNGSART, EINZUGSART, M_KOS_TYP, M_KOS_ID, AKTUELL) SELECT M_ID, M_REFERENZ, GLAEUBIGER_ID, GLAEUBIGER_GK_ID, BEGUENSTIGTER, NAME, ANSCHRIFT, KONTONR, BLZ, IBAN, BIC, BANKNAME, M_UDATUM, M_ADATUM, '$edatum', M_ART, NUTZUNGSART, EINZUGSART, M_KOS_TYP, M_KOS_ID, '1' FROM SEPA_MANDATE WHERE M_KOS_TYP LIKE 'MIETVERTRAG' AND M_KOS_ID = '$mv_id' AND M_EDATUM = '9999-12-31' LIMIT 1;";
            DB::insert($sql);
            return true;
        } else {
            return false;
        }
    }

    function mandat_aendern($dat, $mref, $glaeubiger_id, $gk_id, $empf, $name, $anschrift, $kto, $blz, $iban, $bic, $bankname, $udatum, $adatum, $edatum, $m_art, $n_art, $e_art, $kos_typ, $kos_id)
    {
        $this->get_mandat_infos($dat);
        if (!isset ($this->mand)) {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\ErrorMessage('Abbruch, interner fehler class_sepa, mandat_aendern.')
            );
        }
        $this->mandat_dat_deaktivieren($dat);
        if ($this->check_m_ref($mref)) {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\InfoMessage('Mandat existiert schon, ihre eingaben wurden nicht gespeichert.')
            );
        } else {
            $last_id = $this->mand->M_ID;
            $udatum = date_german2mysql($udatum);
            $adatum = date_german2mysql($adatum);
            $edatum = date_german2mysql($edatum);
            $sql = "INSERT INTO `SEPA_MANDATE` VALUES (NULL, '$last_id', '$mref', '$glaeubiger_id', '$gk_id', '$empf', '$name', '$anschrift', '$kto', '$blz', '$iban', '$bic', '$bankname', '$udatum', '$adatum', '$edatum', '$m_art', '$n_art', '$e_art', '$kos_typ', '$kos_id', '1');";
            echo "$sql<br>";
            DB::insert($sql);

            /* Protokollieren */
            $last_dat = DB::getPdo()->lastInsertId();
            protokollieren('SEPA_MANDATE', $last_dat, $dat);
        }
    }

    /* Prüfen ob mandatsreferenz existiert */

    function mandat_dat_deaktivieren($dat)
    {
        DB::update("UPDATE `SEPA_MANDATE` SET AKTUELL='0' WHERE `DAT` = '$dat'");
        return true;
    }

    function mandat_speichern($mref, $glaeubiger_id, $gk_id, $empf, $name, $anschrift, $kto, $blz, $iban, $bic, $bankname, $udatum, $adatum, $edatum, $m_art, $n_art, $e_art, $kos_typ, $kos_id)
    {
        if ($this->check_m_ref($mref)) {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\InfoMessage('Mandat existiert schon, Ihre eingaben wurden nicht gespeichert.')
            );
        } else {
            $last_id = last_id2('SEPA_MANDATE', 'M_ID') + 1;
            $udatum = date_german2mysql($udatum);
            $adatum = date_german2mysql($adatum);
            $edatum = date_german2mysql($edatum);
            $sql = "INSERT INTO `SEPA_MANDATE` VALUES (NULL, '$last_id', '$mref', '$glaeubiger_id', '$gk_id', '$empf', '$name', '$anschrift', '$kto', '$blz', '$iban', '$bic', '$bankname', '$udatum', '$adatum', '$edatum', '$m_art', '$n_art', '$e_art', '$kos_typ', '$kos_id', '1');";
            DB::insert($sql);
            /* Protokollieren */
            $last_dat = DB::getPdo()->lastInsertId();
            protokollieren('SEPA_MANDATE', $last_dat, '0');
            echo "Mandat gespeichert";
        }
    }

    function sepa_datei_erstellen($sammelbetrag = 1, $dateiname_msgid, $nutzungsart = 'MIETZAHLUNG', $pdf = 0)
    {
        $arr = $this->get_mandate_arr($nutzungsart);
        $anz = count($arr);
        $myKtoSepaSimple = new KtoSepaSimple ();
        $monat = date("m");
        $monatsname = monat2name($monat);
        $jahr = date("Y");

        $this->summe_frst = 0.00;
        $this->summe_rcur = 0.00;

        for ($a = 0; $a < $anz; $a++) {

            $name = substr($this->umlautundgross($arr [$a] ['NAME']), 0, 35); // auf 70 Zeichen kürzen
            $iban = $arr [$a] ['IBAN'];
            $bic = $arr [$a] ['BIC'];
            $mandat_datum = $arr [$a] ['M_UDATUM'];
            $m_ref = $arr [$a] ['M_REFERENZ'];
            $kos_id = $arr [$a] ['M_KOS_ID'];
            $einzugsart = $arr [$a] ['EINZUGSART'];
            if ($nutzungsart == 'MIETZAHLUNG') {
                $mv = new mietvertraege ();
                $mv->get_mietvertrag_infos_aktuell($kos_id);
                $einheit_kn = $mv->einheit_kurzname;
                $mz = new miete ();
                $mz->mietkonto_berechnung($kos_id);

                if ($einzugsart == 'Aktuelles Saldo komplett') {
                    if ($mz->erg < 0.00) {
                        $summe_zu_ziehen = substr($mz->erg, 1);
                    } else {
                        $summe_zu_ziehen = 0.00;
                    }
                }

                if ($einzugsart == 'Nur die Summe aus Vertrag') {
                    $mk = new mietkonto ();
                    $summe_zu_ziehen_arr = explode('|', $mk->summe_forderung_monatlich($kos_id, $monat, $jahr));

                    $summe_zu_ziehen = $summe_zu_ziehen_arr [0];
                }

                if ($einzugsart == 'Ratenzahlung') {

                    $mk = new mietkonto ();
                    $summe_zu_ziehen_arr = explode('|', $mk->summe_forderung_monatlich($kos_id, $monat, $jahr));

                    $summe_raten = $mk->summe_rate_monatlich($kos_id, $monat, $jahr);
                    $summe_zu_ziehen = $summe_zu_ziehen_arr [0] + $summe_raten;
                }

                /*
				 * $mv = new mietvertraege();
				 * $mv->get_mietvertrag_infos_aktuell($kos_id);
				 *
				 * $mz = new miete();
				 * $mz->mietkonto_berechnung($kos_id);
				 *
				 * if($mz->erg<0.00){
				 * $mz->erg = substr($mz->erg,1);
				 * }
				 */

                $kat = 'RENT';
                $vzweck1 = substr($this->umlautundgross("Mieteinzug $monatsname $jahr für $mv->einheit_kurzname $name"), 0, 140);
                $PmtInfId = substr($this->umlautundgross($mv->objekt_kurzname . " LS-MIETEN $monat/$jahr"), -30);
            }

            if ($nutzungsart == 'HAUSGELD') {

                /* Berechnung */
                $weg = new weg ();
                $einheit_id = $weg->get_einheit_id_from_eigentuemer($kos_id);
                $e = new einheit ();
                $e->get_einheit_info($einheit_id);
                // $weg->get_wg_info($monat, $jahr, 'Einheit', $einheit_id, 'Hausgeld');
                $weg->get_eigentuemer_saldo($kos_id, $einheit_id);
                $einheit_kn = $e->einheit_kurzname;
                if ($einzugsart == 'Aktuelles Saldo komplett') {
                    if ($weg->hg_erg < 0) {
                        $summe_zu_ziehen = $weg->soll_aktuell;;
                    } else {
                        $summe_zu_ziehen = 0.00;
                    }
                }

                if ($einzugsart == 'Nur die Summe aus Vertrag') {
                    $summe_zu_ziehen = $weg->soll_aktuell;
                }
                $vzweck1 = substr($this->umlautundgross("Hausgeld $monatsname $jahr für $e->einheit_kurzname $name"), 0, 140);
                $kat = '';
                $PmtInfId = substr($e->objekt_kurzname . " HAUSGELDER $monat/$jahr", -30);
            }

            /* Gemeinsame vars */
            $last_ident = substr($this->umlautundgross("MANDAT:$m_ref"), 0, 35);

            /*
			 * SequenceType1Code Wertebereich: FRST (Erstlastschrift), RCUR (Folgelastschrift), OOFF (Einmallastschrift),FNAL (letzte Lastschrift)
			 */
            /* Feststellen ob Erstnutzung, Folgenutzung des Mandats */
            if (!$this->check_mandat_is_used($m_ref, $iban) == true) {
                $abbuchung = 'FRST';
                //PLUS 5 TAGE
                $o = new objekt ();
                $datum = $o->datum_plus_tage(date("Y-m-d"), 5);
                $this->summe_frst += $summe_zu_ziehen;
            } else {
                $abbuchung = 'RCUR';
                //PLUS 1 TAG
                $o = new objekt ();
                $datum = $o->datum_plus_tage(date("Y-m-d"), 1);
                $this->summe_rcur += $summe_zu_ziehen;
            }

            if ($summe_zu_ziehen > 0.00) {
                if ($pdf == 0) {
                    $myKtoSepaSimple->Add($datum, $summe_zu_ziehen, $name, $iban, $bic, NULL, $kat, $last_ident, $vzweck1, $abbuchung, $m_ref, $mandat_datum);
                    /* Eintragen als genutzt */
                    $this->mandat_seq_speichern($m_ref, $summe_zu_ziehen, $datum, $dateiname_msgid, $vzweck1, $iban);
                } else {
                    if ($abbuchung == 'FRST') {
                        $tab_frst [$a] ['EINHEIT'] = $einheit_kn;
                        $tab_frst [$a] ['DATUM'] = date_mysql2german($datum);
                        $tab_frst [$a] ['BETRAG'] = nummer_punkt2komma_t($summe_zu_ziehen);
                        $tab_frst [$a] ['NAME'] = $name;
                        $tab_frst [$a] ['ABBUCHUNG'] = $abbuchung;
                        $tab_frst [$a] ['IBAN'] = $iban;
                        $tab_frst [$a] ['BIC'] = $bic;
                        $tab_frst [$a] ['KAT'] = $kat;
                        $tab_frst [$a] ['IDENT'] = $last_ident;
                        $tab_frst [$a] ['VZWECK'] = $vzweck1;
                        $tab_frst [$a] ['M_REF'] = $m_ref;
                        $tab_frst [$a] ['M_DATUM'] = $mandat_datum;
                    }

                    if ($abbuchung == 'RCUR') {
                        $tab_rcur [$a] ['EINHEIT'] = $einheit_kn;
                        $tab_rcur [$a] ['DATUM'] = date_mysql2german($datum);
                        $tab_rcur [$a] ['BETRAG'] = nummer_punkt2komma_t($summe_zu_ziehen);
                        $tab_rcur [$a] ['NAME'] = $name;
                        $tab_rcur [$a] ['ABBUCHUNG'] = $abbuchung;
                        $tab_rcur [$a] ['IBAN'] = $iban;
                        $tab_rcur [$a] ['BIC'] = $bic;
                        $tab_rcur [$a] ['KAT'] = $kat;
                        $tab_rcur [$a] ['IDENT'] = $last_ident;
                        $tab_rcur [$a] ['VZWECK'] = $vzweck1;
                        $tab_rcur [$a] ['M_REF'] = $m_ref;
                        $tab_rcur [$a] ['M_DATUM'] = $mandat_datum;
                    }
                }
            }
        }

        $gk = new geldkonto_info ();
        $gk->geld_konto_details(session()->get('geldkonto_id'));

        $seps = new sepa ();
        $seps->get_iban_bic($gk->kontonummer, $gk->blz);
        $d = new detail ();
        $glaeubiger_id = $d->finde_detail_inhalt('GELD_KONTEN', session()->get('geldkonto_id'), 'GLAEUBIGER_ID');
        /* SEPA FILE */
        if ($pdf == 0) {
            $xmlstring = $myKtoSepaSimple->GetXML('CORE', $dateiname_msgid, $PmtInfId, $this->umlautundgross($gk->konto_beguenstigter), $this->umlautundgross($gk->konto_beguenstigter), $seps->IBAN, $seps->BIC, $glaeubiger_id, $sammelbetrag);

            /* SEPA AUSGABE */
            ob_clean();
            header('Content-type: text/xml; charset=utf-8');
            header("Content-disposition: attachment;filename=$dateiname_msgid");
            echo $xmlstring;
            die ();
        } else {
            /* PDF */
            $pdf = new Cezpdf ('a4', 'landscape');
            $bpdf = new b_pdf ();
            $bpdf->b_header($pdf, 'Partner', session()->get('partner_id'), 'landscape', 'Helvetica.afm', 6);
            $this->footer_zahlungshinweis = $bpdf->zahlungshinweis;
            $pdf->ezStopPageNumbers(); // seitennummerirung beenden
            $p = new partners ();
            $p->get_partner_info(session()->get('partner_id'));

            $cols = array(
                'DATUM' => "Datum",
                'EINHEIT' => "Einheit",
                'BETRAG' => "Betrag",
                'NAME' => "Name",
                'M_REF' => "MANDAT",
                'VZWECK' => "TEXT",
                'ABBUCHUNG' => "RF",
                'BIC' => "BIC",
                'IBAN' => "IBAN"
            );
            if (is_array($tab_frst)) {
                $tab_frst = array_merge($tab_frst, Array());
                $anz_t = count($tab_frst);
                $tab_frst [$anz_t] ['EINHEIT'] = "<b>SUMME</b>";
                $tab_frst [$anz_t] ['BETRAG'] = "<b>$this->summe_frst</b>";
                $pdf->ezTable($tab_frst, $cols, "<b>Beigleitzettel " . $this->umlautundgross($gk->konto_beguenstigter) . " - ERSTABBUCHUNGEN</b>", array(
                    'rowGap' => 1.5,
                    'showLines' => 1,
                    'showHeadings' => 1,
                    'shaded' => 1,
                    'shadeCol' => array(
                        0.9,
                        0.9,
                        0.9
                    ),
                    'titleFontSize' => 9,
                    'fontSize' => 7,
                    'xPos' => 50,
                    'xOrientation' => 'right',
                    'width' => 750,
                    'cols' => array(
                        'BETRAG' => array(
                            'justification' => 'right',
                            'width' => 50
                        ),
                        'NAME' => array(
                            'justification' => 'left',
                            'width' => 100
                        ),
                        'VZWECK' => array(
                            'justification' => 'left',
                            'width' => 200
                        ),
                        'DATUM' => array(
                            'justification' => 'left',
                            'width' => 50
                        )
                    )
                ));
            }
            if (is_array($tab_rcur)) {
                $tab_rcur = array_merge($tab_rcur, Array());
                $pdf->ezSetDy(-20);
                $anz_r = count($tab_rcur);
                $tab_rcur [$anz_r] ['EINHEIT'] = "<b>SUMME</b>";
                $tab_rcur [$anz_r] ['BETRAG'] = "<b>$this->summe_rcur</b>";
                $pdf->ezTable($tab_rcur, $cols, "<b>Beigleitzettel " . $this->umlautundgross($gk->konto_beguenstigter) . " - FOLGEABBUCHUNGEN</b>", array(
                    'rowGap' => 1.5,
                    'showLines' => 1,
                    'showHeadings' => 1,
                    'shaded' => 1,
                    'shadeCol' => array(
                        0.9,
                        0.9,
                        0.9
                    ),
                    'titleFontSize' => 9,
                    'fontSize' => 7,
                    'xPos' => 50,
                    'xOrientation' => 'right',
                    'width' => 750,
                    'cols' => array(
                        'BETRAG' => array(
                            'justification' => 'right',
                            'width' => 50
                        ),
                        'NAME' => array(
                            'justification' => 'left',
                            'width' => 100
                        ),
                        'VZWECK' => array(
                            'justification' => 'left',
                            'width' => 140
                        ),
                        'DATUM' => array(
                            'justification' => 'left',
                            'width' => 50
                        ),
                        'G_KEY_A' => array(
                            'justification' => 'right',
                            'width' => 55
                        ),
                        'E_KEY_A' => array(
                            'justification' => 'right',
                            'width' => 50
                        ),
                        'E_BETRAG' => array(
                            'justification' => 'right',
                            'width' => 50
                        )
                    )
                ));
            }
            $pdf->ezSetDy(-20);
            $uhrzeit = date("d.m.Y H:i:s");
            $pdf->ezText("                Erstellt am: $uhrzeit", 10);
            ob_end_clean(); // ausgabepuffer leeren
            $pdf->ezStream();
        }
    }

    function get_mandate_arr($nutzungsart = 'Alle')
    {
        if (!session()->has('geldkonto_id') && $nutzungsart != 'Alle') {
            session()->put('last_url', route('web::sepa::legacy', ['option' => 'mandate_mieter'], false));
            throw new \App\Exceptions\MessageException(
                new \App\Messages\InfoMessage('Geldkonto wählen')
            );
        }
        $datum_heute = date("Y-m-d");
        if ($nutzungsart == 'Alle') {
            $result = DB::select("SELECT * FROM `SEPA_MANDATE` WHERE `AKTUELL` = '1' AND M_EDATUM>='$datum_heute' AND M_ADATUM<='$datum_heute' ORDER BY NAME ASC");
        } else {
            $gk_id = session()->get('geldkonto_id');
            $result = DB::select("SELECT * FROM `SEPA_MANDATE` WHERE `AKTUELL` = '1' && M_EDATUM>='$datum_heute' && M_ADATUM<='$datum_heute' && NUTZUNGSART='$nutzungsart' && GLAEUBIGER_GK_ID='$gk_id' ORDER BY NAME ASC");
        }
        if (!empty($result)) {
            return $result;
        }
    }

    function umlautundgross($wort)
    {
        $tmp = strtoupper($wort);
        $suche = array(
            'Ä',
            'Ö',
            'Ü',
            'ß',
            'ä',
            'ö',
            'ü',
            'ß',
            'é',
            '&',
            '*',
            '$',
            '%',
            '€',
            '<BR>'
        );
        $ersetze = array(
            'AE',
            'OE',
            'UE',
            'SS',
            'AE',
            'OE',
            'UE',
            'SS',
            'E',
            '+',
            '.',
            '.',
            '.',
            'EUR',
            ' '
        );
        $ret = str_replace($suche, $ersetze, $tmp);
        return $ret;
    }

    function mandat_seq_speichern($mref, $betrag, $datum, $datei, $vzweck, $iban)
    {
        if (!$this->check_mandat_is_used($mref, $iban) == true) {
            $seq = 'FRST';
        } else {
            $seq = 'RCUR';
        }
        // $datum = date_german2mysql($datum);
        $sql = "INSERT INTO `SEPA_MANDATE_SEQ` VALUES (NULL, '$mref','$iban', '$seq', '$betrag', '$datei', '$datum', '$vzweck', '1');";
        // echo "$sql<br>";

        DB::insert($sql);
        /* Protokollieren */
        $last_dat = DB::getPdo()->lastInsertId();
        protokollieren('SEPA_MANDATE_SEQ', $last_dat, '0');
        // echo "Mandat gespeichert";
    }

    function get_iban_bic($konto_nr, $blz, $land = 'DE')
    {
        $this->BIC = '';
        $this->IBAN = '';
        $this->IBAN1 = '';
        $this->BANKNAME = '';
        $this->BANKNAME_K = '';
        if ($land == 'DE') {
            $result = DB::select("SELECT * FROM `BLZ` WHERE `BLZ` ='$blz' LIMIT 0 , 1");
            if (!empty($result)) {
                $row = $result[0];
                $konto_info = ( object )$row;
                $this->BIC = $konto_info->BIC;
                $this->BANKNAME = $konto_info->BEZEICHNUNG;
                $this->BANKNAME_K = $konto_info->KURZ_BEZ;
            }

            $iban = $this->get_iban_de($konto_nr, $blz);
            if (strlen($iban) == 22) {
                $iban_1 = chunk_split($iban, 4, ' ');
                // $iban_1 = $this->iban_to_human_format($iban);
            } else {
                $iban_1 = $iban;
            }

            $this->IBAN = $iban;
            $this->IBAN1 = $iban_1;

            return true;
        } else {
            $this->BIC = $land;
            $this->IBAN = $land;
            $this->IBAN1 = $land;
        }
    }

    function get_iban_de($kto, $blz, $land = 'DE')
    {
        /*
		 * Die Berechnung erfolgt in mehreren Schritten. Zuerst wird die Länderkennung um zwei Nullen ergänzt.
		 * Danach wird aus Kontonummer und Bankleitzahl die BBAN kreiert.
		 * Also beispielsweise Bankleitzahl 70090100 und Kontonummer 1234567890 ergeben die BBAN 700901001234567890.
		 * Modulo 97-10.
		 */

        /*
		 * Anschließend werden die beiden Alpha-Zeichen der Länderkennung sowie weitere eventuell in der Kontonummer enthaltene Buchstaben in rein numerische Ausdrücke umgewandelt.
		 * Die Grundlage für die Zahlen, die aus den Buchstaben gebildet werden sollen, bildet ihre Position der jeweiligen Alpha-Zeichen im lateinischen Alphabet.
		 * Zu diesem Zahlenwert wird 9 addiert. Die Summe ergibt die Zahl, die den jeweiligen Buchstaben ersetzen soll.
		 * Dementsprechend steht für A (Position 1+9) die Zahl 10, für D (Position 4+9) die 13 und für E (Position 5+9) die 14.
		 * Der Länderkennung DE entspricht also die Ziffernfolge 1314.
		 *
		 * Im nächsten Schritt wird diese Ziffernfolge, ergänzt um die beiden Nullen, an die BBAN gehängt.
		 * Hieraus ergibt sich 700901001234567890131400. Diese bei deutschen Konten immer 24-stellige Zahl wird anschließend Modulo 97 genommen.
		 * Das heißt, es wird der Rest berechnet, der sich bei der Teilung der 24-stelligen Zahl durch 97 ergibt. Das ist für dieses Beispiel 90.
		 * Dieses Ergebnis wird von der nach ISO-Standard festgelegten Zahl 98 subtrahiert.
		 * Ist das Resultat, wie in diesem Beispiel, kleiner als Zehn, so wird der Zahl eine Null vorangestellt, sodass sich wieder ein zweistelliger Wert ergibt.
		 * Somit ist die errechnete Prüfziffer 08. Aus der Länderkennung, der zweistelligen Prüfsumme und der BBAN wird nun die IBAN generiert.
		 * Die ermittelte IBAN lautet in unserem Beispiel: DE08700901001234567890.
		 *
		 * Zur besseren Veranschaulichung das ganze noch einmal zusammengefasst:
		 * Bankleitzahl 70090100
		 * Kontonummer 1234567890
		 * BBAN 700901001234567890
		 * alphanumerische Länderkennung DE
		 * numerische Länderkennung 1314 (D = 13, E = 14)
		 * numerische Länderkennung ergänzt um 00 131400
		 * Prüfsumme 700901001234567890131400
		 * Prüfsumme Modulo 97 90
		 * Prüfziffer 08 (98 - 90, ergänzt um führende Null)
		 * Länderkennung +Prüfziffer + BBAN = IBAN DE08700901001234567890
		 *
		 * Die Prüfung der IBAN erfolgt, indem ihre ersten vier Stellen ans Ende verschoben und die Buchstaben wieder durch 1314 ersetzt werden.
		 * Die Zahl 700901001234567890131408 Modulo 97 muss 1 ergeben. Dann ist die IBAN gültig, was auf unser Beispiel zutrifft.
		 */

        /*
		 * Beispiel für Ausnahmebanken Kontonummern
		 *
		 * DE91 1007 0024 0003 5787 62
		 * richtige IBAN: DE43 1007 0024 0357 8762 00
		 * DE43 1007 0024 0357 8762 00
		 * DE43 1007 0024 0357 8762 00
		 *
		 * Falsche IBAN: DE07 1007 0848 0002 6608 84
		 * DE20 1007 0848 0266 0884 00
		 * DE20 1007 0848 0266 0884 00
		 * DEUTDEDBBER
		 */

        /* ALNUM Prüfung */
        $err = '';
        if (!ctype_digit($kto)) {
            $err .= "Kto $kto nicht nummerisch";
        }
        if (!ctype_digit($blz)) {
            $err .= "\nBLZ $blz nicht nummerisch";
        }
        if (!ctype_alpha($land)) {
            $err .= "\nLAND $land ist nicht ALPHA";
        }

        /* LAND */
        if (strlen($land) > 2) {
            $lk = substr($land, 0, 2);
        } else {
            $lk = $land;
        }
        $lk_zahl = $this->iban_checksum_string_replace($lk . '00');

        /* KTO */
        if (strlen($kto) == 10) {
            $kto_neu = $kto;
        }
        if (strlen($kto) < 10) {
            /* Ausnahmebanken bei kurzen Kontonummern */
            if ($blz == '10070024' or $blz == '10070848' or $blz == '10040000' or $blz == '76026000') {
                // $kto_temp = "0".$kto;
                // $kto_neu=str_pad($kto_temp, 10, "0", STR_PAD_RIGHT);
                if (strlen($kto) < 9) {
                    $kto_temp = str_pad($kto, 8, "0", STR_PAD_LEFT);
                }
                if (strlen($kto) == 9) {
                    $kto_temp = str_pad($kto, 10, "0", STR_PAD_LEFT);
                    // echo "<b>$kto_temp<br></b>";
                }
                $kto_neu = str_pad($kto_temp, 10, "0", STR_PAD_RIGHT);
                // echo "$kto $kto_temp $kto_neu <br>";
            } else {
                $kto_neu = str_pad($kto, 10, "0", STR_PAD_LEFT);
            }
        }
        if (strlen($kto) > 10) {
            $kto_neu = substr($kto, 0, 10);
        }

        /* BLZ */
        if (strlen($blz) > 8) {
            $err .= "\nBLZ zu lang";
            $blz_neu = '';
        }
        if (strlen($blz) == 8) {
            $blz_neu = $blz;
        }
        if (strlen($blz) < 8) {
            $blz_neu = str_pad($blz, 8, "0", STR_PAD_RIGHT);
        }

        if (empty ($err)) {
            $bban = $blz_neu . $kto_neu;
            $bban_digit = $bban . $lk_zahl;
            $pz = $this->pz_iban_mod97_10($bban_digit);
            $iban = $lk . $pz . $blz_neu . $kto_neu;
            // echo $err;

            return $iban;
        } else {
            // return $err;
        }
    }

    function iban_checksum_string_replace($string)
    {
        $iban_replace_chars = range('A', 'Z');
        foreach (range(10, 35) as $tempvalue) {
            $iban_replace_values [] = strval($tempvalue);
        }
        return str_replace($iban_replace_chars, $iban_replace_values, $string);
    }

    function pz_iban_mod97_10($bban_digit)
    {
        // $rest = intval($bban_digit % 97);//falsch
        $rest = bcmod($bban_digit, 97);
        $pz_mod = 98 - $rest;
        if ($pz_mod < 10) {
            $pz_mod = str_pad($pz_mod, 2, "0", STR_PAD_LEFT);
        }
        return $pz_mod;
    }

    function mandat_nutzungen_anzeigen($m_ref)
    {
        $arr = $this->mandat_nutzungen_arr($m_ref);
        if (!empty($arr)) {
            $anz = count($arr);
            echo "<table class=\"sortable\">";
            echo "<thead><tr><th>DATUM</th><th>SEQ</th><th>DATEI</th><th>VZWECK</th><th>BETRAG</th></tr></thead>";
            $summe = 0.00;
            for ($a = 0; $a < $anz; $a++) {
                $seq = $arr [$a] ['SEQ'];
                $datum = date_mysql2german($arr [$a] ['DATUM']);
                $betrag = nummer_punkt2komma_t($arr [$a] ['BETRAG']);
                $summe += $arr [$a] ['BETRAG'];
                $datei = $arr [$a] ['DATEI'];
                $vzweck = $arr [$a] ['VZWECK'];
                echo "<tr><td>$datum</td><td>$seq</td><td>$datei</td><td>$vzweck</td><td>$betrag</td></tr>";
            }
            $summe_a = nummer_punkt2komma_t($summe);
            echo "<tfoot><tr><th colspan=\"4\"><b>SUMME</b></th><th><b>$summe_a</b></th></tr></tfoot>";
            echo "</table>";
        }
    }

    function mandat_nutzungen_arr($m_ref)
    {
        $this->get_mandat_infos_mref($m_ref);
        $iban = $this->mand->IBAN;
        $result = DB::select("SELECT * FROM `SEPA_MANDATE_SEQ` WHERE `M_REFERENZ` = '$m_ref' AND IBAN='$iban' AND `AKTUELL` = '1' ORDER BY DATUM");
        return $result;
    }

    function get_mandat_infos_mref($m_ref)
    {
        if (isset ($this->mand)) {
            unset ($this->mand);
        }
        $result = DB::select("SELECT * FROM `SEPA_MANDATE` WHERE `M_REFERENZ` ='$m_ref' && AKTUELL='1' ORDER BY DAT LIMIT 0,1");
        if (!empty($result)) {
            $row = $result[0];
            $this->mand = ( object )$row;
        }
    }

    function test_fremd_sepa_ls()
    {
        /* TESTEN */
        ob_clean();
        $this->xml_pruef('classes/xsd/cs.xml', 'classes/xsd/pain.008.003.02.xsd');
    }

    function xml_pruef($xml_datei, $xsd_datei)
    {
        // Enable user error handling
        libxml_use_internal_errors(true);
        $xml = new DOMDocument ();
        $xml->load($xml_datei);
        if (!$xml->schemaValidate($xsd_datei)) {
            print '<b>Errors Found!</b>';
            $this->libxml_display_errors();
        } else {
            echo "validated<p/>";
        }
    }

    function libxml_display_errors()
    {
        $errors = libxml_get_errors();
        foreach ($errors as $error) {
            print $this->libxml_display_error($error);
        }
        libxml_clear_errors();
    }

    // function

    function libxml_display_error($error)
    {
        $return = "<br/>\n";
        switch ($error->level) {

            case LIBXML_ERR_WARNING :
                $return .= "<b>Warning $error->code</b>: ";
                break;

            case LIBXML_ERR_ERROR :
                $return .= "<b>Error $error->code</b>: ";
                break;

            case LIBXML_ERR_FATAL :
                $return .= "<b>Fatal Error $error->code</b>: ";
                break;
        }

        $return .= trim($error->message);
        if ($error->file) {
            $return .= " in <b>$error->file</b><br>";
        }
        $return .= " on line <b>$error->line</b><br>";
        return $return;
    }

    function start($MsgId, $CreDtTm, $NbOfTxs, $CtrlSum)
    {
        $doc = new DOMDocument ('1.0', 'utf-8');
        $doc->formatOutput = true;
        $root = $doc->createElementNS('urn:iso:std:iso:20022:tech:xsd:pain.008.003.02', 'Document');
        $root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');

        $domAttribute = $doc->createAttribute('xsi:schemaLocation');
        $domAttribute->value = 'urn:iso:std:iso:20022:tech:xsd:pain.008.003.02 pain.008.003.02.xsd';
        $root->appendChild($domAttribute);
        $doc->appendChild($root);

        $root = $doc->createElement('CstmrDrctDbtInitn');
        $doc->appendChild($root);

        $firstNode = $doc->createElement("GrpHdr");
        $firstNode->appendChild($doc->createElement("MsgId", "$MsgId"));
        $firstNode->appendChild($doc->createElement("CreDtTm", "$CreDtTm"));
        $firstNode->appendChild($doc->createElement("NbOfTxs", "$NbOfTxs"));
        $firstNode->appendChild($doc->createElement("CtrlSum", "$CtrlSum"));

        $root->appendChild($firstNode);

        ob_clean();
        header('Content-type: text/xml; charset=utf-8');
        $xml = $doc->saveXML();
        return $doc;
    }

    function dropdown_sepa_geldkonten($label, $name, $id, $kos_typ, $kos_id)
    {
        $my_array = DB::select("SELECT GELD_KONTEN.KONTO_ID, GELD_KONTEN.IBAN, GELD_KONTEN.BIC, GELD_KONTEN.BEGUENSTIGTER, GELD_KONTEN.KONTONUMMER, GELD_KONTEN.BLZ, GELD_KONTEN.INSTITUT, GELD_KONTEN.BEZEICHNUNG FROM GELD_KONTEN_ZUWEISUNG, GELD_KONTEN WHERE KOSTENTRAEGER_TYP = '$kos_typ' && KOSTENTRAEGER_ID = '$kos_id' && GELD_KONTEN.KONTO_ID = GELD_KONTEN_ZUWEISUNG.KONTO_ID && GELD_KONTEN_ZUWEISUNG.AKTUELL = '1' && GELD_KONTEN.AKTUELL = '1' GROUP BY GELD_KONTEN.KONTO_ID ORDER BY GELD_KONTEN.KONTO_ID ASC");
        $numrows = count($my_array);
        if ($numrows) {
            echo "<label for=\"$id\">$label (Konten:$numrows)</label>\n<select name=\"$name\" id=\"$id\" size=\"1\" >\n";
            if ($numrows > 1) {
                echo "<option>Bitte wählen</option>\n";
            }
            for ($a = 0; $a < $numrows; $a++) {
                $konto_id = $my_array [$a] ['KONTO_ID'];
                $bez = $my_array [$a] ['BEZEICHNUNG'];
                $iban = $my_array [$a] ['IBAN'];
                $iban1 = $this->iban_convert($iban, 0);
                $bic = $my_array [$a] ['BIC'];
                echo "<option value=\"$konto_id\" >$bez - $iban1 - $bic</option>\n";
            } // end for
            echo "</select>\n";
            return true;
        } else {
            fehlermeldung_ausgeben("Kein SEPA-Geldkonto hinterlegt");
            return FALSE;
        }
    }

    function iban_convert($iban, $mysql = 1)
    {
        if ($mysql != 1) {
            $iban_new = chunk_split($iban, 4, ' '); // für den Menschen leserlich in 4er Blöcken
        } else {
            $iban_new = str_replace(' ', '', $iban); // für die Maschinen /mysql etc.. hintereinnander
        }

        return $iban_new;
    }

    function sepa_ueberweisung_speichern($von_gk_id, $an_sepa_gk_id, $vzweck, $kat, $kos_typ, $kos_id, $konto, $betrag)
    {
        // echo "$von_gk_id, $an_sepa_gk_id, $vzweck, $kat, $kos_ytp, $kos_id, $konto, $betrag";
        $bk = new bk ();
        $last_b_id = $bk->last_id('SEPA_UEBERWEISUNG', 'ID') + 1;

        $sep = new sepa ();
        if (!$sep->get_sepa_konto_infos($an_sepa_gk_id)) {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\ErrorMessage("EMPFÄNGER SEPAGELDKONTO UNBEKANNT! ID:$an_sepa_gk_id")
            );
        } else {
            if (empty ($sep->IBAN) or empty ($sep->BIC)) {
                throw new \App\Exceptions\MessageException(
                    new \App\Messages\ErrorMessage("$sep->geldkonto_bez hat keine IBAN oder BIC")
                );
            }
            $vzweck = "$sep->beguenstigter, $vzweck";

            $db_abfrage = "INSERT INTO SEPA_UEBERWEISUNG VALUES (NULL, '$last_b_id', NULL, '$kat', '$vzweck', '$betrag', '$von_gk_id', '$sep->IBAN', '$sep->BIC', '$sep->bankname', '$sep->beguenstigter', '$kos_typ', '$kos_id', '$konto', '1')";
            DB::insert($db_abfrage);
            /* Protokollieren */
            $last_dat = DB::getPdo()->lastInsertId();
            protokollieren('SEPA_UEBERWEISUNG', $last_dat, '0');
            return $last_b_id;
        }
    }

    function get_sepa_konto_infos($gk_id)
    {
        if (isset ($this->iban)) {
            unset ($this->iban);
        }
        if (isset ($this->bic)) {
            unset ($this->bic);
        }
        if (isset ($this->bankname)) {
            unset ($this->bankname);
        }
        if (isset ($this->beguenstigter)) {
            unset ($this->beguenstigter);
        }
        if (isset ($this->kos_typ)) {
            unset ($this->kos_typ);
        }
        if (isset ($this->kos_id)) {
            unset ($this->kos_id);
        }
        $result = DB::select("SELECT * FROM `GELD_KONTEN` WHERE `KONTO_ID` = '$gk_id' AND `AKTUELL` = '1' ORDER BY KONTO_DAT DESC LIMIT 0,1");
        if (!empty($result)) {
            $row = $result[0];
            $this->IBAN = $row ['IBAN'];
            $this->IBAN1 = chunk_split($this->IBAN, 4, ' ');
            $this->BIC = $row ['BIC'];

            $this->beguenstigter = $row ['BEGUENSTIGTER'];
            $this->konto_beguenstigter = $row ['BEGUENSTIGTER'];

            $this->kontonummer = $row ['KONTONUMMER'];
            $this->blz = $row ['BLZ'];

            $this->bankname = $row ['INSTITUT'];
            $this->institut = $row ['INSTITUT'];
            $this->kredit_institut = $row ['INSTITUT'];

            $this->geldkonto_bez = $row ['BEZEICHNUNG'];
            $this->geldkonto_bezeichnung = $row ['BEZEICHNUNG'];
            $this->geldkonto_bezeichnung_kurz = $this->geldkonto_bezeichnung;

            return true;
        } else {
            return false;
        }
    }

    function sepa_ueberweisung_speichern_IBAN($von_gk_id, $iban, $bic, $empfaenger, $bank, $vzweck, $kat, $kos_typ, $kos_id, $konto, $betrag)
    {
        // echo "$von_gk_id, $an_sepa_gk_id, $vzweck, $kat, $kos_typ, $kos_id, $konto, $betrag";
        $empfaenger = umlautundgross($empfaenger);
        $bank = umlautundgross($bank);
        $bk = new bk ();
        $last_b_id = $bk->last_id('SEPA_UEBERWEISUNG', 'ID') + 1;
        $iban = $this->iban_convert($iban, 1);

        if (!empty ($iban) && !empty ($bic)) {

            $db_abfrage = "INSERT INTO SEPA_UEBERWEISUNG VALUES (NULL, '$last_b_id', NULL, '$kat', '$vzweck', '$betrag', '$von_gk_id', '$iban', '$bic', '$bank', '$empfaenger', '$kos_typ', '$kos_id', '$konto', '1')";
            DB::insert($db_abfrage);
            /* Protokollieren */
            $last_dat = DB::getPdo()->lastInsertId();
            protokollieren('SEPA_UEBERWEISUNG', $last_dat, '0');
            return $last_b_id;
        } else {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\ErrorMessage("Sepa Überweisung | BIC IBAN eingeben!!!")
            );
        }
    }

    function sammler2sepa($von_gk_id, $kat = null, $sammler = 0)
    {
        if (!$von_gk_id) {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\InfoMessage("Bitte wählen Sie ein Geldkonto.")
            );
        }
        /* Einzelbetrag oder Sammelbetrag beachten $sammler!!!!! */

        $arr = $this->get_sammler_arr($von_gk_id, $kat);
        if (empty($arr)) {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\InfoMessage("Keine Datensätze auf Konto $von_gk_id für $kat")
            );
        } else {
            $myKtoSepaSimple = new KtoSepaSimple ();
            $datum = '1999-01-01';
            $datum_h = date("dmY");
            $time_h = date("His");

            $anz = count($arr);
            $benutzername = Auth::user()->name;
            $msg_id = "$von_gk_id-$datum_h-$time_h-" . str_limit(umlautundgross($benutzername), 35);
            $dateiname = "$von_gk_id-$datum_h-$time_h-" . str_limit(umlautundgross($benutzername), 35) . ".xml";
            for ($a = 0; $a < $anz; $a++) {
                $empf = $this->umlautundgross($arr [$a] ['BEGUENSTIGTER']);
                $vzweck = substr($this->umlautundgross($arr [$a] ['VZWECK']), 0, 140);
                $betrag = $arr [$a] ['BETRAG'];
                $sum += $betrag;
                $iban = $arr [$a] ['IBAN'];
                $bic = $arr [$a] ['BIC'];
                $ref_id = $arr [$a] ['ID'];

                /* Überweisungsdatensatz hinzufügen */
                $myKtoSepaSimple->Add($datum, $betrag, $empf, $iban, $bic, NULL, NULL, $ref_id, $vzweck);

                /* Für das hinzufügen des Dateinamens */
                $dat = $arr [$a] ['DAT'];
                $this->sepa_ueberweisung2file($dat, $dateiname);
            } // end for

            /* Eigene Informationen einfügen */
            $gk = new geldkonto_info ();
            $gk->geld_konto_details($von_gk_id);

            $xml_string = $myKtoSepaSimple->GetXML('TRF', $msg_id, $dateiname, $this->umlautundgross($gk->konto_beguenstigter), $this->umlautundgross("$gk->geldkonto_bez"), $gk->IBAN, $gk->BIC, $sammler);
            // $xmlstring = $myKtoSepaSimple->GetXML('CORE', $dateiname_msgid , $PmtInfId, $this->umlautundgross($gk->konto_beguenstigter), $this->umlautundgross("$gk->konto_beguenstigter - $username"), $seps->IBAN, $seps->BIC, $glaeubiger_id, $sammelbetrag);
            /* SEPA AUSGABE */
            ob_clean();
            header('Content-type: text/xml; charset=utf-8');
            header("Content-disposition: attachment;filename=$dateiname");
            echo $xml_string;
            die ();
        }
    }

    function get_sammler_arr($von_gk_id, $kat = null)
    {
        if ($kat == null) {
            $result = DB::select("SELECT * FROM `SEPA_UEBERWEISUNG` WHERE `FILE` IS NULL AND `GK_ID_AUFTRAG` ='$von_gk_id' AND `AKTUELL` = '1' ORDER BY DAT");
        } else {
            $result = DB::select("SELECT * FROM `SEPA_UEBERWEISUNG` WHERE `FILE` IS NULL AND KAT='$kat' AND `GK_ID_AUFTRAG` ='$von_gk_id' AND `AKTUELL` = '1' ORDER BY DAT");
        }
        return $result;
    }

    function sepa_ueberweisung2file($dat, $dateiname)
    {
        $db_abfrage = "UPDATE SEPA_UEBERWEISUNG SET FILE='$dateiname' WHERE DAT='$dat'";
        DB::update($db_abfrage);
        /* Protokollieren */
        protokollieren('SEPA_UEBERWEISUNG', $dat, $dat);
    }

    function get_sepa_lsfiles_arr()
    {
        if (session()->has('geldkonto_id')) {
            $vorz = session()->get('geldkonto_id') . '-';
            $result = DB::select("SELECT COUNT(BETRAG) AS ANZ, DATEI, SUM(BETRAG) AS SUMME, DATUM FROM `SEPA_MANDATE_SEQ` WHERE AKTUELL='1' && DATEI LIKE '$vorz%' GROUP BY DATEI ORDER BY DATUM DESC");
        } else {
            $result = DB::select("SELECT COUNT(BETRAG) AS ANZ, DATEI, SUM(BETRAG) AS SUMME, DATUM FROM `SEPA_MANDATE_SEQ` WHERE AKTUELL='1' GROUP BY DATEI ORDER BY DATUM DESC");
        }
        return $result;
    }

    function form_ls_datei_ab($datei)
    {
        if (!session()->has('geldkonto_id')) {
            fehlermeldung_ausgeben("Geldkonto wählen!");
        } else {
            $gk = new geldkonto_info ();
            $gk->geld_konto_details(session()->get('geldkonto_id'));
            fehlermeldung_ausgeben("Gebucht wird auf dem Geldkonto $gk->geldkonto_bez");
            if (!session()->has('temp_kontoauszugsnummer')) {
                fehlermeldung_ausgeben("Kontrolldaten eingeben!!!");
                return;
            }
            $arr = $this->get_sepa_lszeilen_arr($datei);
            if (!empty($arr)) {
                $anz = count($arr);
                $f = new formular ();

                echo "<div class='row'>";
                for ($a = 0; $a < $anz; $a++) {
                    $m_ref = $arr [$a] ['M_REFERENZ'];
                    $betrag = $arr [$a] ['BETRAG'];
                    $betrag_a = nummer_punkt2komma($arr [$a] ['BETRAG']);
                    $vzweck = $arr [$a] ['VZWECK'];

                    if (stristr($m_ref, 'MV') == TRUE) {
                        $kos_typ = 'Mietvertrag';
                        $kos_id = substr($m_ref, 2);
                    }
                    if (stristr($m_ref, 'WEG-ET') == TRUE) {
                        $kos_typ = 'Eigentuemer';
                        $kos_id = substr($m_ref, 6);
                    }
                    if (!isset ($kos_typ)) {
                        throw new \App\Exceptions\MessageException(
                            new \App\Messages\ErrorMessage("Kostentraeger unbekannt.")
                        );
                    }
                    if ($kos_typ == 'Mietvertrag') {
                        $mv = new mietvertraege ();
                        $mv->get_mietvertrag_infos_aktuell($kos_id);
                        $kos_bez = "$mv->einheit_typ $mv->einheit_kurzname $mv->personen_name_string";
                    }

                    if ($kos_typ == 'Eigentuemer') {
                        $weg = new weg ();
                        $weg->get_eigentuemer_namen($kos_id);
                        $kos_bez = "$weg->eigentuemer_name_str";
                    }
                    // echo "$kos_bez";
                    $check_datum = date_german2mysql(session()->get('temp_datum'));
                    if ($this->check_ls_buchung(session()->get('geldkonto_id'), $m_ref, $betrag, session()->get('temp_kontoauszugsnummer'), $check_datum, $kos_typ, $kos_id) != true) {
                        $f->erstelle_formular("LS-BUCHEN $kos_bez MREF:$m_ref", null);
                        echo "<div class='input-field col-xs-12 col-md-4 col-lg-2'>";
                        $f->text_feld('Kontoauzugsnr', 'kontoauszug', session()->get('temp_kontoauszugsnummer'), 10, 'auszugsnr', null);
                        echo "</div>";
                        echo "<div class='input-field col-xs-12 col-md-4 col-lg-2'>";
                        $f->datum_feld('Datum', 'datum', session()->get('temp_datum'), 'datum');
                        echo "</div>";
                        echo "<div class='input-field col-xs-12 col-md-4 col-lg-2'>";
                        $f->text_feld('Betrag', 'betrag', $betrag_a, 20, 'betrag', null);
                        echo "</div>";
                        echo "<div class='input-field col-xs-12 col-md-12 col-lg-6'>";
                        $f->text_feld('Buchungstext', 'vzweck', "$vzweck", 100, 'vzweck', null);
                        echo "</div>";
                        $f->hidden_feld('gk_id', session()->get('geldkonto_id'));
                        $f->hidden_feld('kos_typ', $kos_typ);
                        $f->hidden_feld('kos_id', $kos_id);
                        $f->hidden_feld('m_ref', $m_ref);
                        $f->hidden_feld('option', 'ls_zeile_buchen');
                        $f->hidden_feld('datei', $datei);
                        echo "<div class='input-field col-xs-12 col-md-4 col-lg-2'>";
                        $f->check_box_js('mwst', 'mwst_' . $a, 'MWSt buchen', '', '');
                        echo "</div>";
                        echo "<div class='input-field col-xs-12 col-md-6 col-lg-4'>";
                        $f->send_button('btnLS', 'Zeile Buchen');
                        echo "</div>";
                        $f->ende_formular();
                    } else {
                        $f->erstelle_formular("LS-BUCHEN $kos_bez MREF:$m_ref", null);
                        echo "<div class='input-field col-xs-12 col-md-4 col-lg-2'>";
                        $f->text_feld_inaktiv('Kontoauzugsnr', 'kontoauszug', session()->get('temp_kontoauszugsnummer'), 10, 'auszugsnr');
                        echo "</div>";
                        echo "<div class='input-field col-xs-12 col-md-4 col-lg-2'>";
                        $f->text_feld_inaktiv('Datum', 'datum', session()->get('temp_datum'), 'datum', 'datum');
                        echo "</div>";
                        echo "<div class='input-field col-xs-12 col-md-4 col-lg-2'>";
                        $f->text_feld_inaktiv('Betrag', 'betrag', $betrag_a, 20, 'betrag');
                        echo "</div>";
                        echo "<div class='input-field col-xs-12 col-md-12 col-lg-6'>";
                        $f->text_feld_inaktiv('Buchungstext', 'vzweck', "$vzweck", 100, 'vzweck');
                        echo "</div>";
                        echo "<div class='input-field col-xs-12'>";
                        echo "Bereits verbucht.";
                        echo "</div>";
                        $f->ende_formular();
                        //fehlermeldung_ausgeben("$betrag_a - $vzweck<br> wurde bereits verbucht. Doppelbuchung unmöglich!");
                    }
                }
                echo "</div>";
            } else {
                fehlermeldung_ausgeben("Keine Lastschriften in der Datei $datei");
            }
        }
    }

    function get_sepa_lszeilen_arr($datei)
    {
        $result = DB::select("SELECT * FROM `SEPA_MANDATE_SEQ` WHERE AKTUELL='1' && DATEI='$datei'");
        return $result;
    }

    function check_ls_buchung($gk_id, $m_ref, $betrag, $kontoauszug, $datum, $kos_typ, $kos_id)
    {
        $result = DB::select("SELECT *  
FROM  `GELD_KONTO_BUCHUNGEN` 
WHERE  `KONTO_AUSZUGSNUMMER` ='$kontoauszug'
AND  `ERFASS_NR` =  '$m_ref'
AND  `BETRAG` = '$betrag'
AND  `GELDKONTO_ID` ='$gk_id'
AND  `KOSTENTRAEGER_TYP` =  '$kos_typ'
AND  `KOSTENTRAEGER_ID` ='$kos_id'
AND DATUM ='$datum'		
AND  `AKTUELL` =  '1'");

        $numrows = count($result);
        if ($numrows) {
            return $numrows;
        } else {
            return false;
        }
    }

    function sepa_sammler_alle()
    {
        $arr = $this->sepa_gk_arr();
        if (!empty($arr)) {
            echo "<h2>Zu erstellende SEPA-Dateien</h2>";
            $anz = count($arr);
            echo "<table class=\"sortable\">";
            echo "<tr><th>Geldkonto</th><th>kat</th><th>Summe</th></tr>";
            for ($a = 0; $a < $anz; $a++) {
                $gk_id = $arr [$a] ['GK_ID_AUFTRAG'];
                $sum = $arr [$a] ['SUMME'];
                $kat = $arr [$a] ['KAT'];
                $anz_dat = $arr [$a] ['ANZ'];
                $gkk = new geldkonto_info ();
                $gkk->geld_konto_details($gk_id);
                echo "<tr><td>$gkk->geldkonto_bez</td><td>$kat (Überweisungen:$anz_dat)</td><td>$sum</td></tr>";
            }
            echo "</table>";
        } else {
            hinweis_ausgeben("Keine Datensätze in SEPA-Sammlern");
        }
        // print_r($arr);
    }

    function sepa_gk_arr()
    {
        $result = DB::select("SELECT GK_ID_AUFTRAG, KAT, SUM(BETRAG) AS SUMME, COUNT(BETRAG) AS ANZ FROM `SEPA_UEBERWEISUNG` WHERE `FILE` IS NULL AND `AKTUELL` = '1' GROUP BY KAT, `GK_ID_AUFTRAG`");
        return $result;
    }

    function get_summe_sepa_sammler($von_gk_id, $kat, $kos_typ, $kos_id)
    {
        $result = DB::select("SELECT SUM( BETRAG ) AS SUMME FROM `SEPA_UEBERWEISUNG` WHERE `FILE` IS NULL AND `KOS_TYP` LIKE '$kos_typ' AND `KOS_ID` ='$kos_id' && KAT='$kat' AND `AKTUELL` = '1'");
        if (!empty($result)) {
            return $result[0]['SUMME'];
        } else {
            return '0.00';
        }
    }

    function sepa_files($von_gk_id)
    {
        $arr = $this->sepa_files_arr($von_gk_id);
        if (empty($arr)) {
            fehlermeldung_ausgeben("Keine SEPA-Überweisungen vom gewählten Geldkonto!");
        } else {
            $anz_f = count($arr);
            echo "<table class=\"sortable\">";
            echo "<tr><th>NR</th><th>KONTO</th><th>DATEINAME</th><th>BESCHREIBUNG</th><th>SUMME</th><th>OPTIONEN</th><th></th><th></th></tr>";
            for ($a = 0; $a < $anz_f; $a++) {
                $z = $a + 1;
                $dateiname = $arr [$a] ['FILE'];
                $dat_nam_arr = explode('-', $dateiname);
                $gk_id = $dat_nam_arr [0];
                $gk = new geldkonto_info ();
                $gk->geld_konto_details($gk_id);

                $sep_id = $arr [$a] ['ID'];
                $summe_t = nummer_punkt2komma_t($arr [$a] ['SUMME']);
                $link_anzeigen = "<a href='" . route('web::sepa::legacy', ['option' => 'sepa_file_anzeigen', 'sepa_file' => $dateiname]) . "'>ANZEIGEN</a>";
                $link_autobuchen = "<a href='" . route('web::sepa::legacy', ['option' => 'sepa_file_buchen', 'sepa_file' => $dateiname]) . "'>BUCHEN</a>";
                $link_autobuchen1 = "<a href='" . route('web::sepa::legacy', ['option' => 'sepa_file_buchen_fremd', 'sepa_file' => $dateiname]) . "'>BUCHEN FREMDKONTO</a>";
                $link_pdf = "<a href='" . route('web::sepa::legacy', ['option' => 'sepa_file_pdf', 'sepa_file' => $dateiname]) . "'><img src=\"images/pdf_light.png\"></a>";
                $link_pdf1 = "<a href='" . route('web::sepa::legacy', ['option' => 'sepa_file_pdf', 'sepa_file' => $dateiname, 'no_logo']) . "'><img src=\"images/pdf_dark.png\"></a>";
                $link_als_vorlage = "<a href='" . route('web::sepa::legacy', ['option' => 'sepa_file_kopieren', 'sepa_file' => $dateiname]) . "'>ALS VORLAGE</a>";
                $link_details = "<a href='" . route('web::details::legacy', ['option' => 'details_anzeigen', 'detail_tabelle' => 'SEPA_UEBERWEISUNG', 'detail_id' => $sep_id]) . "'>DETAILS</a>";
                $de = new detail ();
                $beschr = $de->finde_detail_inhalt('SEPA_UEBERWEISUNG', $sep_id, 'Beschreibung');
                echo "<tr><td>$z</td><td>$gk->geldkonto_bez</td><td>$dateiname</td><td>$beschr</td><td>$summe_t</td><td>$link_anzeigen $link_pdf $link_pdf1 $link_autobuchen $link_details</td><td>$link_als_vorlage</td><td>$link_autobuchen1</td></tr>";
            }
            echo "</table>";
        }
    }

    function sepa_files_arr($von_gk_id)
    {
        if ($von_gk_id != null) {
            $result = DB::select("SELECT ID, FILE, SUM(BETRAG) AS SUMME FROM `SEPA_UEBERWEISUNG` WHERE `FILE` IS NOT NULL AND GK_ID_AUFTRAG='$von_gk_id' AND `AKTUELL` = '1' GROUP BY FILE ORDER BY DAT DESC");
        } else {
            $result = DB::select("SELECT ID, FILE, SUM(BETRAG) AS SUMME FROM `SEPA_UEBERWEISUNG` WHERE `FILE` IS NOT NULL AND `AKTUELL` = '1' GROUP BY FILE ORDER BY DAT DESC LIMIT 0, 300");
        }
        return $result;
    }

    function sepa_file_kopieren($file)
    {
        $arr = $this->get_sepa_files_daten_arr($file);
        if (empty($arr)) {
            fehlermeldung_ausgeben("Keine Datensätze zur Datei $file");
        } else {
            $anz = count($arr);
            for ($a = 0; $a < $anz; $a++) {
                $kat = $arr [$a] ['KAT'];
                $vzweck = $arr [$a] ['VZWECK'];
                $betrag = $arr [$a] ['BETRAG'];
                $iban = $arr [$a] ['IBAN'];
                $bic = $arr [$a] ['BIC'];
                $bankname = $arr [$a] ['BANKNAME'];
                $beguenstigter = $arr [$a] ['BEGUENSTIGTER'];
                $konto = $arr [$a] ['KONTO'];
                $kos_typ = $arr [$a] ['KOS_TYP'];
                $kos_id = $arr [$a] ['KOS_ID'];
                $vzweckn = "$beguenstigter, $vzweck";

                $von_gk_id = session()->get('geldkonto_id');

                $bk = new bk ();
                $last_b_id = $bk->last_id('SEPA_UEBERWEISUNG', 'ID') + 1;

                $db_abfrage = "INSERT INTO SEPA_UEBERWEISUNG VALUES (NULL, '$last_b_id', NULL, '$kat', '$vzweckn', '$betrag', '$von_gk_id', '$iban', '$bic', '$bankname', '$beguenstigter', '$kos_typ', '$kos_id', '$konto', '1')";
                DB::insert($db_abfrage);
                /* Protokollieren */
                $last_dat = DB::getPdo()->lastInsertId();
                protokollieren('SEPA_UEBERWEISUNG', $last_dat, '0');
            }
            return true;
        }
    }

    function get_sepa_files_daten_arr($file)
    {
        $result = DB::select("SELECT * FROM `SEPA_UEBERWEISUNG` WHERE `FILE` = '$file' AND `AKTUELL` = '1'");
        return $result;
    }

    function sepa_file_autobuchen($file, $datum, $gk_id, $auszug, $mwst = '0')
    {
        $arr = $this->get_sepa_files_daten_arr($file);
        if (empty($arr)) {
            fehlermeldung_ausgeben("Keine Datensätze zur Datei $file");
        } else {
            $anz = count($arr);
            for ($a = 0; $a < $anz; $a++) {
                $vzweck = $arr [$a] ['VZWECK'];
                $konto = $arr [$a] ['KONTO'];
                $kos_typ = $arr [$a] ['KOS_TYP'];
                $kos_id = $arr [$a] ['KOS_ID'];
                $betrag = -$arr [$a] ['BETRAG'];
                if ($mwst == '0') {
                    $this->betrag_buchen($datum, $auszug, $auszug, $betrag, $vzweck, $gk_id, $kos_typ, $kos_id, $konto, '0.00');
                } else {
                    $mwst = $betrag / 119 * 19;
                    $this->betrag_buchen($datum, $auszug, $auszug, $betrag, $vzweck, $gk_id, $kos_typ, $kos_id, $konto, $mwst);
                }
            }

            $geld = new geldkonto_info ();
            $kontostand_aktuell = nummer_punkt2komma($geld->geld_konto_stand($gk_id));
            echo "SEPA-Datei $file wurde verbucht!<br>";
            if (session()->has('temp_kontostand') && session()->has('temp_kontoauszugsnummer')) {
                $kontostand_temp = nummer_punkt2komma(session()->get('temp_kontostand'));
                hinweis_ausgeben("<h3>Kontostand am " . session()->get('temp_datum') . " laut Kontoauszug " . session()->get('temp_kontoauszugsnummer') . " war $kontostand_temp €</h3>");
            }
            if ($kontostand_aktuell == $kontostand_temp) {
                echo "<h3>Kontostand aktuell: $kontostand_aktuell €</h3>";
            } else {
                echo "<h3 style=\"color:red\">Kontostand aktuell: $kontostand_aktuell €</h3>";
            }
        }
    }

    function betrag_buchen($datum, $kto_auszugsnr, $m_ref, $betrag, $vzweck, $geldkonto_id, $kostentraeger_typ, $kostentraeger_id, $kostenkonto, $mwst = '0.00')
    {
        $b = new buchen ();
        $buchung_id = $b->get_last_geldbuchung_id();
        /* neu */
        $datum = date_german2mysql($datum);
        $datum_arr = explode('-', $datum);
        $jahr = $datum_arr ['0'];
        $g_buchungsnummer = $b->get_last_buchungsnummer_konto($geldkonto_id, $jahr);
        $g_buchungsnummer = $g_buchungsnummer + 1;
        // echo "<h1>Neue Buchungsnummer erteilt: $g_buchungsnummer</h1>";

        $buchung_id = $buchung_id + 1;

        /* neu */
        $db_abfrage = "INSERT INTO GELD_KONTO_BUCHUNGEN VALUES (NULL, '$buchung_id', '$g_buchungsnummer', '$kto_auszugsnr', '$m_ref', '$betrag', '$mwst', '$vzweck', '$geldkonto_id', '$kostenkonto', '$datum', '$kostentraeger_typ', '$kostentraeger_id', '1')";
        DB::insert($db_abfrage);
        /* Protokollieren */
        $last_dat = DB::getPdo()->lastInsertId();
        protokollieren('GELD_KONTO_BUCHUNGEN', $last_dat, '0');
    }

    function sepa_file_buchen($file)
    {
        $gk = new geldkonto_info ();
        $gk->geld_konto_details(session()->get('geldkonto_id'));
        fehlermeldung_ausgeben("SEPA wird gebucht auf dem Geldkonto $gk->geldkonto_bez");
        if (!session()->has('temp_kontoauszugsnummer')) {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\ErrorMessage("Kontrolldaten eingeben.")
            );
        }
        $arr = $this->get_sepa_files_daten_arr($file);
        if (empty($arr)) {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\ErrorMessage("Keine Datensätze zur Datei $file")
            );
        } else {
            $anz = count($arr);
            for ($a = 0; $a < $anz; $a++) {
                echo "<table>";
                echo "<thead><tr><th>EMPFÄNGER</th><th>DATUM</th><th>AUSZUG</th><th>VZWECK</th><th>BETRAG</th><th>KONTO</th><th></th></tr></thead>";
                $f = new formular ();
                $empf = $arr [$a] ['BEGUENSTIGTER'];
                $vzweck = $arr [$a] ['VZWECK'];
                $konto = $arr [$a] ['KONTO'];
                $kat = $arr [$a] ['KAT'];
                $kos_typ = $arr [$a] ['KOS_TYP'];
                $kos_id = $arr [$a] ['KOS_ID'];
                $rr = new rechnung ();
                $kos_bez = $rr->kostentraeger_ermitteln($kos_typ, $kos_id);
                $betrag = -$arr [$a] ['BETRAG'];
                $betrag_u = $arr [$a] ['BETRAG'];
                $z = $a + 1;
                $f->erstelle_formular("SEPA-Überweisung buchen $kos_typ $empf", null);
                echo "<tr><td>$z. $kos_typ<br>$empf</td><td>" . session()->get('temp_datum') . "</td><td>" . session()->get('temp_kontoauszugsnummer') . "</td><td>";
                $f->text_feld('Buchungstext', 'vzweck', "$empf, $kat, $vzweck", 100, 'vzweck', '');
                echo "</td><td>$betrag</td><td>$konto<br>$kos_typ:$kos_bez</td><td>";
                if ($kat == 'RECHNUNG') {
                    throw new \App\Exceptions\MessageException(
                        new \App\Messages\ErrorMessage('Rechnungen können nicht automatisch gebucht werden.')
                    );
                }

                $m_ref = session()->get('temp_kontoauszugsnummer');
                $datum = session()->get('temp_datum');
                $anz_buchungen = $this->check_ls_buchung(session()->get('geldkonto_id'), $m_ref, $betrag, session()->get('temp_kontoauszugsnummer'), $datum, $kos_typ, $kos_id);
                $anz_zeilen = $this->get_zeilen_anz_aus_sepa($file, session()->get('geldkonto_id'), $betrag_u, $kos_typ, $kos_id);
                if ($anz_zeilen > $anz_buchungen) {
                    $f->hidden_feld('gk_id', session()->get('geldkonto_id'));
                    $f->hidden_feld('datum', session()->get('temp_datum'));
                    $f->hidden_feld('auszug', session()->get('temp_kontoauszugsnummer'));
                    $f->hidden_feld('kos_typ', $kos_typ);
                    $f->hidden_feld('kos_id', $kos_id);
                    $f->hidden_feld('m_ref', $m_ref);
                    $f->hidden_feld('konto', $konto);
                    $f->hidden_feld('betrag', $betrag);
                    $f->hidden_feld('option', 'sepa_ue_buchen');
                    $f->check_box_js('mwst', 'mwst' . $z, 'MWSt buchen', '', '');
                    $f->send_button('BuchenBtn', 'Buchen');
                    echo "Zahlungen: $anz_zeilen<br>Buchungen:$anz_buchungen";
                } else {
                    echo "Buchungen: $anz_buchungen";
                }
                echo "</td></tr>";
                $f->ende_formular();
                echo "</table>";
            }
        }
    }

    function get_zeilen_anz_aus_sepa($sepa_file, $gk_id, $betrag, $kos_typ, $kos_id)
    {
        $result = DB::select("SELECT *  FROM `SEPA_UEBERWEISUNG` WHERE `FILE` LIKE '$sepa_file' AND `BETRAG` = '$betrag' && GK_ID_AUFTRAG='$gk_id' && KOS_TYP='$kos_typ' &&  KOS_ID='$kos_id' AND `AKTUELL` = '1'");
        return count($result);
    }

    function sepa_file_buchen_fremd($file)
    {
        $f = new formular ();
        $f->erstelle_formular('Vorzeichen des Betrages wechseln', '');
        $f->hidden_feld('vorzeichen', 'TAUSCH');
        $f->send_button('btn_SepaVZ', 'Vorzeichen wechseln');
        $f->ende_formular();

        if (request()->has('vorzeichen')) {
            if (session()->get('sep_vorzeichen') == '-') {
                session()->put('sep_vorzeichen', '');
            } else {
                session()->put('sep_vorzeichen', '-');
            }
        }
        $gk = new geldkonto_info ();
        $gk->geld_konto_details(session()->get('geldkonto_id'));
        fehlermeldung_ausgeben("SEPA wird gebucht auf dem Geldkonto $gk->geldkonto_bez " . session()->get('temp_datum') . " " . session()->get('temp_kontoauszugsnummer') . "]");
        if (!session()->has('temp_kontoauszugsnummer')) {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\ErrorMessage("Kontrolldaten eingeben.")
            );
        }

        $arr = $this->get_sepa_files_daten_arr($file);
        if (empty($arr)) {
            fehlermeldung_ausgeben("Keine Datensätze zur Datei $file");
        } else {

            $m_ref = session()->get('temp_kontoauszugsnummer');
            $anz = count($arr);
            $f = new formular ();
            $f->erstelle_formular("SEPA-Überweisung FREMD", null);
            echo "<table>";
            echo "<thead><tr><th>EMPFöNGER</th><th>DATUM</th><th>AUSZUG</th><th>VZWECK</th><th>BETRAG</th><th>KONTO<input type=\"button\" onclick=\"auswahl_alle(this.form.konto)\" value=\"Alle\"></th><th>Zuweisung</th><th></th></tr></thead>";

            for ($a = 0; $a < $anz; $a++) {
                $empf = $arr [$a] ['BEGUENSTIGTER'];
                $vzweck = $arr [$a] ['VZWECK'];
                $kat = $arr [$a] ['KAT'];
                $kos_typ = $arr [$a] ['KOS_TYP'];
                $kos_id = $arr [$a] ['KOS_ID'];
                $rr = new rechnung ();
                $kos_bez = $rr->kostentraeger_ermitteln($kos_typ, $kos_id);
                $betrag = session()->get('sep_vorzeichen') . $arr [$a] ['BETRAG'];
                $z = $a + 1;

                echo "<tr><td>$z. $kos_typ<br>$empf</td><td>" . session()->get('temp_datum') . "</td><td>" . session()->get('temp_kontoauszugsnummer') . "</td><td>";
                $f->text_feld('Buchungstext', 'vzweck[]', "$empf, $kat, $vzweck", 100, 'vzweck', '');
                $f->hidden_feld('betrag[]', $betrag);
                echo "</td><td>$betrag</td><td>";
                $buc = new buchen ();
                $buc->dropdown_kostenrahmen_nr('Kostenkonto', 'konto[]', 'GELDKONTO', session()->get('geldkonto_id'), '', 'konto');
                echo "</td><td>$kos_typ:$kos_bez</td>";
                if ($kat == 'RECHNUNG') {
                    throw new \App\Exceptions\MessageException(
                        new \App\Messages\ErrorMessage('Rechnungen können nicht automatisch gebucht werden.')
                    );
                }
                $f->hidden_feld('kos_typ[]', $kos_typ);
                $f->hidden_feld('kos_id[]', $kos_id);

                echo "</tr>";
            }
            echo "</table>";
            $f->hidden_feld('gk_id', session()->get('geldkonto_id'));
            $f->hidden_feld('datum', session()->get('temp_datum'));
            $f->hidden_feld('auszug', session()->get('temp_kontoauszugsnummer'));
            $f->hidden_feld('m_ref', $m_ref);
            $f->hidden_feld('option', 'sepa_ue_buchen_fremd');
            $f->check_box_js('mwst', 'mwst', 'MWSt buchen', '', '');
            $f->send_button('BuchenBtn', 'Buchen');
            $f->ende_formular();
        }
    }

    function sepa_file2pdf($filename)
    {
        echo $filename;
        $arr = $this->get_sepa_files_daten_arr($filename);
        if (empty($arr)) {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\ErrorMessage("Keine Datensätze zur Datei $file")
            );
        } else {
            $this->get_sepa_fileinfos($filename);

            $arr [$anz] ['VZWECK'] = "<b>SUMME</b>";
            $arr [$anz] ['BETRAG'] = $this->sepa_summe;

            /* PDF */
            $pdf = new Cezpdf ('a4', 'landscape');
            $bpdf = new b_pdf ();
            $bpdf->b_header($pdf, 'Partner', session()->get('partner_id'), 'landscape', 'Helvetica.afm', 6);

            $pdf->ezText("SEPA-Datei: $filename", 12);
            $pdf->ezText("Geldkonto: <b>$this->geldkonto_bez $this->IBAN1 $this->BIC</b>", 12);
            $pdf->ezSetDy(-10);
            $cols = array(
                'KAT' => "KAT",
                'VZWECK' => "VZWECK",
                'BETRAG' => "Betrag",
                'IBAN' => "IBAN",
                'BIC' => "BIC",
                'BEGUENSTIGTER' => "BEGÜNSTIGTER",
                'KONTO' => "BKONTO"
            );
            $pdf->ezTable($arr, $cols, "Übersicht SEPA-Datei", array(
                'rowGap' => 1.5,
                'showLines' => 1,
                'showHeadings' => 1,
                'shaded' => 1,
                'shadeCol' => array(
                    0.9,
                    0.9,
                    0.9
                ),
                'titleFontSize' => 9,
                'fontSize' => 7,
                'xPos' => 50,
                'xOrientation' => 'right',
                'width' => 750,
                'cols' => array(
                    'BETRAG' => array(
                        'justification' => 'right',
                        'width' => 50
                    ),
                    'KONTO' => array(
                        'justification' => 'right',
                        'width' => 50
                    )
                )
            ));

            /* SEPA AUSGABE */
            ob_end_clean(); // ausgabepuffer leeren
            $pdf->ezStream();
        }
    }

    function get_sepa_fileinfos($file)
    {
        $result = DB::select("SELECT FILE, GK_ID_AUFTRAG, SUM(BETRAG) AS SUMME FROM `SEPA_UEBERWEISUNG` WHERE `FILE`='$file' AND `AKTUELL` = '1' GROUP BY FILE ORDER BY DAT LIMIT 0,1");

        if (!empty($result)) {
            $row = $result[0];
            $this->sepa_summe = $row ['SUMME'];
            $this->sepa_gk_id = $row ['GK_ID_AUFTRAG'];

            $this->get_sepa_konto_infos($this->sepa_gk_id);
            // print_r($row);
        }
    }

    function form_sammel_ue()
    {
        if (!session()->has('geldkonto_id')) {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\InfoMessage("Bitte wählen Sie ein Geldkonto.")
            );
        } else {
            $gk = new geldkonto_info ();
            $gk->geld_konto_details(session()->get('geldkonto_id'));
            $sep = new sepa ();
            $f = new formular ();
            $f->erstelle_formular('SEPA-Sammelüberweisung', null);

            if (request()->has('filter')) {
                $filter = request()->input('filter');
            } else {
                $filter = '';
            }

            $f->text_feld('Filter Empfängergeldkonten', 'filter', $filter, 20, 'filter', '');
            $f->send_button('btn_Sepa', 'Geldkonten filtern');
            $f->ende_formular();
            $f->erstelle_formular('SEPA-Sammelüberweisung', null);
            $f->text_feld_inaktiv('Vom Geldkonto', 'vmgk', $gk->geldkonto_bez, 80, 'vmgkid');
            $sep->dropdown_sepa_geldkonten_filter('Empfängerkonto wählen', 'empf_sepa_gk_id', 'empf_sepa_gk_id', $filter);
            $f->text_feld('Betrag', 'betrag', "", 10, 'betrag', '');
            $f->text_feld('VERWENDUNG', 'vzweck', "", 80, 'vzweck', '');
            $f->hidden_feld('option', 'sepa_sammler_hinzu_ue');
            // $f->hidden_feld('kat', 'SAMMLER');
            $this->dropdown_sammler_typ('Sammlerkategorie wählen!!!', 'kat', 'kat', '', 'SONSTIGES');
            $f->hidden_feld('gk_id', session()->get('geldkonto_id'));

            $js_typ = "onchange=\"list_kostentraeger('list_kostentraeger', this.value)\"";
            // $js_typ='';
            $bb = new buchen ();
            // dropdown_kostentreager_typen($label, $name, $id, $js_action){

            // dropdown_kostentreager_typen_vw($label, $name, $id, $js_action, $vorwahl_typ){
            if (session()->has('kos_typ')) {
                $bb->dropdown_kostentreager_typen_vw('Kostenträgertyp wählen', 'kos_typ', 'kostentraeger_typ', $js_typ, session()->get('kos_typ'));
            } else {
                $bb->dropdown_kostentreager_typen('Kostenträgertyp norm', 'kos_typ', 'kostentraeger_typ', $js_typ);
            }

            $js_id = "";

            if (session()->has('kos_bez')) {
                $bb->dropdown_kostentraeger_bez_vw("Kostenträger C1 ", 'kos_id', 'dd_kostentraeger_id', $js_id, session()->get('kos_typ'), session()->get('kos_bez'));
            } else {
                $bb->dropdown_kostentreager_ids('Kostenträger XC', 'kos_id', 'dd_kostentraeger_id', $js_id);
            }
            $kk = new kontenrahmen ();
            $kk->dropdown_kontorahmenkonten('Buchungskonto', 'konto', 'konto', 'GELDKONTO', session()->get('geldkonto_id'), '');
            $f->send_button('btn_Sepa', 'Zum Sammler hinzufügen');
            $f->ende_formular();
        }
    }

    function dropdown_sepa_geldkonten_filter($label, $name, $id, $filter_bez)
    {
        $my_array = DB::select("SELECT GELD_KONTEN.KONTO_ID, GELD_KONTEN.IBAN, GELD_KONTEN.BIC, GELD_KONTEN.BEGUENSTIGTER, GELD_KONTEN.KONTONUMMER, GELD_KONTEN.BLZ, GELD_KONTEN.INSTITUT,  GELD_KONTEN.BEZEICHNUNG FROM GELD_KONTEN_ZUWEISUNG, GELD_KONTEN WHERE  GELD_KONTEN.KONTO_ID = GELD_KONTEN_ZUWEISUNG.KONTO_ID && GELD_KONTEN_ZUWEISUNG.AKTUELL = '1' && GELD_KONTEN.AKTUELL = '1' && GELD_KONTEN.BEZEICHNUNG LIKE '%$filter_bez%' GROUP BY GELD_KONTEN.KONTO_ID ORDER BY GELD_KONTEN.BEGUENSTIGTER ASC");
        $numrows = count($my_array);
        if ($numrows) {
            echo "<label for=\"$id\">$label</label>\n<select name=\"$name\" id=\"$id\" size=\"1\" >\n";
            echo "<option selected>Bitte wählen</option>\n";
            for ($a = 0; $a < $numrows; $a++) {
                $konto_id = $my_array [$a] ['KONTO_ID'];
                $bez = $my_array [$a] ['BEZEICHNUNG'];
                $iban = $my_array [$a] ['IBAN'];
                $iban1 = $this->iban_convert($iban, 0);
                $bic = $my_array [$a] ['BIC'];
                echo "<option value=\"$konto_id\" >$bez - $iban1 - $bic</option>\n";
            } // end for
            echo "</select>\n";
            return true;
        } else {
            fehlermeldung_ausgeben("Kein SEPA-Geldkonto hinterlegt");
            return FALSE;
        }
    }

    function dropdown_sammler_typ($label, $name, $id, $js_action, $vorwahl_typ)
    {
        echo "<label for=\"$id\">$label</label><select name=\"$name\" id=\"$id\" size=1 $js_action>\n";
        $arr [0] ['KAT'] = 'ET-AUSZAHLUNG';
        $arr [1] ['KAT'] = 'RECHNUNG';
        $arr [2] ['KAT'] = 'LOHN';
        $arr [3] ['KAT'] = 'KK';
        $arr [4] ['KAT'] = 'STEUERN';
        $arr [5] ['KAT'] = 'HAUSGELD';
        $arr [6] ['KAT'] = 'SONSTIGES';

        echo "<option value=\"\">Bitte wählen</option>\n";

        for ($a = 0; $a < count($arr); $a++) {
            $typ = $arr [$a] ['KAT'];
            $bez = $arr [$a] ['KAT'];
            if ($vorwahl_typ == $typ) {
                echo "<option value=\"$typ\" selected>$bez</option>\n";
            } else {
                echo "<option value=\"$typ\">$bez</option>\n";
            }
        }
        echo "</select>\n";
    }

    function form_sammel_ue_IBAN()
    {
        if (!session()->has('geldkonto_id')) {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\InfoMessage("Bitte wählen Sie ein Geldkonto.")
            );
        } else {
            $gk = new geldkonto_info ();
            $gk->geld_konto_details(session()->get('geldkonto_id'));
            $f = new formular ();

            $f->erstelle_formular('SEPA-Sammelüberweisung an IBAN/BIC', null);
            $f->text_feld_inaktiv('Vom Geldkonto', 'vmgk', $gk->geldkonto_bez, 80, 'vmgkid');
            $f->text_feld('Empfänger', 'empfaenger', "", 50, 'empfaenger', '');
            $f->iban_feld('IBAN', 'iban', "", 30, 'iban', '');
            $f->text_feld('BIC', 'bic', "", 15, 'betrag', '');
            $f->text_feld('Bankname', 'bank', "", 50, 'bank', '');

            $f->text_feld('Betrag', 'betrag', "", 10, 'betrag', '');
            $f->text_feld('VERWENDUNG', 'vzweck', "", 80, 'vzweck', '');
            $f->hidden_feld('option', 'sepa_sammler_hinzu_ue_IBAN');
            $this->dropdown_sammler_typ('Sammlerkategorie wählen!!!', 'kat', 'kat', '', 'SONSTIGES');
            $f->hidden_feld('gk_id', session()->get('geldkonto_id'));

            $js_typ = "onchange=\"list_kostentraeger('list_kostentraeger', this.value)\"";
            $bb = new buchen ();
            if (session()->has('kos_typ')) {
                $bb->dropdown_kostentreager_typen_vw('Kostenträgertyp wählen', 'kos_typ', 'kostentraeger_typ', $js_typ, session()->get('kos_typ'));
            } else {
                $bb->dropdown_kostentreager_typen('Kostenträgertyp norm', 'kos_typ', 'kostentraeger_typ', $js_typ);
            }

            $js_id = "";

            if (session()->has('kos_bez')) {
                $bb->dropdown_kostentraeger_bez_vw("Kostenträger C1 ", 'kos_id', 'dd_kostentraeger_id', $js_id, session()->get('kos_typ'), session()->get('kos_bez'));
            } else {
                $bb->dropdown_kostentreager_ids('Kostenträger XC', 'kos_id', 'dd_kostentraeger_id', $js_id);
            }
            $kk = new kontenrahmen ();
            $kk->dropdown_kontorahmenkonten('Buchungskonto', 'konto', 'konto', 'GELDKONTO', session()->get('geldkonto_id'), '');
            $f->send_button('btn_Sepa', 'Zum Sammler hinzufügen');
            $f->ende_formular();
        }
    }

    function sepa_alle_sammler_anzeigen()
    {
        $arr = $this->get_kats_arr(session()->get('geldkonto_id'));
        if (empty($arr)) {
            fehlermeldung_ausgeben("Keine Dateien im SEPA-Sammler");
        } else {
            for ($a = 0; $a < count($arr); $a++) {
                $kat = $arr [$a] ['KAT'];
                if ($this->sepa_sammler_anzeigen(session()->get('geldkonto_id'), $kat) == true) {
                    $gk_id = session()->get('geldkonto_id');
                    echo "<a href='" . route('web::sepa::legacy', ['option' => 'sammler2sepa', 'gk_id' => $gk_id, 'kat' => $kat]) . "'>SEPA-Datei für $kat erstellen</a>";
                }
            }
        }
    }

    function get_kats_arr($von_gk_id, $kat = null)
    {
        if ($kat == null) {
            $result = DB::select("SELECT KAT FROM `SEPA_UEBERWEISUNG` WHERE `FILE` IS NULL AND `GK_ID_AUFTRAG` ='$von_gk_id' AND `AKTUELL` = '1' GROUP BY KAT ORDER BY DAT");
        } else {
            $result = DB::select("SELECT KAT FROM `SEPA_UEBERWEISUNG` WHERE `FILE` IS NULL AND KAT='$kat' AND `GK_ID_AUFTRAG` ='$von_gk_id' AND `AKTUELL` = '1' GROUP BY KAT ORDER BY DAT");
        }
        return $result;
    }

    function sepa_sammler_anzeigen($von_gk_id, $kat = null)
    {
        $arr = $this->get_sammler_arr($von_gk_id, $kat);
        if (!empty($arr)) {
            $anz = count($arr);

            echo "<hr>";
            echo "<h2><b>$kat</b></h2>";
            echo "<hr>";
            echo "<table class=\"sortable\">";

            echo "<thead><tr><th>EMPFÄNGER</th><th>VZWECK</th><th>ZUWEISUNG</th><th>IBAN</th><th>BIC</th><th>BETRAG</th><th>KONTO</th><th>OPTION</TH></tr></thead>";
            $sum = 0;
            for ($a = 0; $a < $anz; $a++) {
                $empf = $arr [$a] ['BEGUENSTIGTER'];
                $vzweck = $arr [$a] ['VZWECK'];
                $betrag = $arr [$a] ['BETRAG'];
                $sum += $betrag;
                $iban = $arr [$a] ['IBAN'];
                $bic = $arr [$a] ['BIC'];
                $konto = $arr [$a] ['KONTO'];
                $z = $a + 1;
                $dat = $arr [$a] ['DAT'];
                $kos_typ = $arr [$a] ['KOS_TYP'];
                $kos_id = $arr [$a] ['KOS_ID'];
                $re = new rechnung ();
                $kos_bez = $re->kostentraeger_ermitteln($kos_typ, $kos_id);
                $kos_bez1 = "$kos_typ: $kos_bez";

                $link_del = "<a href='" . route('web::sepa::legacy', ['option' => 'sepa_datensatz_del', 'dat' => $dat]) . "'>Entfernen</a>";
                echo "<tr><td>$z. $empf</td><td>$vzweck</td><td>$kos_bez1</td><td>$iban</td><td>$bic</td><td>$betrag</td><td>$konto</td><td>$link_del</td></tr>";
            }
            echo "<tfoot><tr><th colspan=\"3\">SUMME</th><th><th>$sum</th><th></th><th></th></tr></tfoot>";
            echo "</table>";
            return true;
        } else {
            fehlermeldung_ausgeben("Keine $kat im SEPA-Ü-Sammler");
        }
    }

    function datensatz_entfernen($dat)
    {
        $db_abfrage = "UPDATE SEPA_UEBERWEISUNG SET AKTUELL='0' WHERE DAT='$dat' && FILE IS NULL";
        DB::update($db_abfrage);
        return true;
    }

    function status_excelsession()
    {
        if (session()->has('umsaetze_nok')) {
            $anz_nok = count(session()->get('umsaetze_nok'));
            $link_nok = "<a href='" . route('web::buchen::legacy', ['option' => 'excel_nok']) . "'>NOK: $anz_nok</a>";
            echo "<span style=\"color:red;\">$link_nok</span>";
        }

        if (is_array(session()->get('umsaetze_ok'))) {
            $anz_ok = count(session()->get('umsaetze_ok'));
            echo "&nbsp;|<span style=\"color:green;\">OK: $anz_ok</span>";
        }

        if (session()->has('umsatz_id_temp')) {
            $akt = session()->get('umsatz_id_temp') + 1;
            echo "&nbsp;|&nbsp;<span style=\"color:blue;\">DS: $akt/$anz_ok</span>";
        }

        $link_konten = "<a href='" . route('web::buchen::legacy', ['option' => 'uebersicht_excel_konten']) . "'>Übersicht Geldkonten</a>";
        echo "&nbsp;|&nbsp;<span style=\"color:yellow;\">$link_konten</span>";
    }

    function uebersicht_excel_konten()
    {
        if (is_array(session()->get('umsatz_konten'))) {
            echo "<table class=\"sortable striped\">";
            echo "<thead><tr><th>NR</th><th>GELDKONTO</th><th>Auszug</th><th>ANFANGSSALDO</th><th>ENDSALDO</th><th>AKTUELL</th></tr></thead>";
            $anz_konten = count(session()->get('umsatz_konten'));
            for ($a = 0; $a < $anz_konten; $a++) {
                $z = $a + 1;
                $gk_id = session()->get('umsatz_konten')[$a];
                $start_ds_id = session()->get('umsatz_konten_start')[$gk_id];
                $gk = new geldkonto_info ();
                $gk->geld_konto_details($gk_id);

                $auszug = sprintf('%01d', session()->get('umsatz_stat')[$gk_id]['auszug']);
                $ksa = nummer_punkt2komma_t(nummer_komma2punkt(session()->get('umsatz_stat')[$gk_id]['ksa']));
                $kse = nummer_punkt2komma_t(nummer_komma2punkt(session()->get('umsatz_stat')[$gk_id]['kse']));

                $kontostand_aktuell = nummer_punkt2komma_t($gk->geld_konto_stand($gk_id));

                if ($kontostand_aktuell == $kse) {
                    $ks_aktuell = "<span style=\"color:green;\"><b>$kontostand_aktuell €</b></span>";
                } else {
                    $ks_aktuell = "<span style=\"color:red;\"><b>$kontostand_aktuell €</b></span>";
                }

                $link_start = "<a href='" . route('web::buchen::legacy', ['option' => 'excel_buchen_session', 'ds_id' => $start_ds_id]) . "'>$gk->geldkonto_bez</a>";
                echo "<tr><td>$z</td><td>$link_start</td><td>$auszug</td><td>$ksa €</td><td>$kse €</td><td>$ks_aktuell</td></tr>";
            }
            echo "</table>";
        } else {
            echo 'Bitte laden Sie zuerst einen Kontoauszug.';
        }
    }

    function form_excel_ds($umsatz_id_temp = 0)
    {
        $gk_id_t = session()->get('umsaetze_ok')[$umsatz_id_temp]['GK_ID'];
        $this->menue_konten($gk_id_t);

        $ksa_bank = session()->get('umsatz_stat')[$gk_id_t]['ksa'];
        $kse_bank = session()->get('umsatz_stat')[$gk_id_t]['kse'];

        session()->put('temp_kontostand', $kse_bank);
        session()->put('kontostand_temp', $kse_bank);

        if (session()->has('kos_typ')) {
            session()->forget('kos_typ');
        }

        if (session()->has('kos_id')) {
            session()->forget('kos_id');
        }

        if (session()->has('kos_bez')) {
            session()->forget('kos_bez');
        }

        session()->put('temp_datum', $umsatz_id_temp);
        $akt = $umsatz_id_temp + 1;
        $gesamt = count(session()->get('umsaetze_ok'));
        $f = new formular ();

        $gk = new geldkonto_info ();
        $gk_id = session()->get('umsaetze_ok')[$umsatz_id_temp]['GK_ID'];
        session()->put('geldkonto_id', $gk_id);

        /* Passendes Objekt wählen */
        $gkk = new gk ();
        $temp_objekt_id = $gkk->get_objekt_id(session()->get('geldkonto_id'));
        session()->put('objekt_id', $temp_objekt_id);

        $gk->geld_konto_details($gk_id);
        $kontostand_aktuell = nummer_punkt2komma($gk->geld_konto_stand($gk_id));

        if (!session()->has('temp_kontostand')) {
            session()->put('temp_kontostand', '0,00');
        }

        if ($kontostand_aktuell == session()->get('temp_kontostand')) {
            echo "&nbsp;|&nbsp;<span style=\"color:green;\"><b>KSAKT: $kontostand_aktuell EUR</b></span>";
        } else {
            echo "&nbsp;|&nbsp;<span style=\"color:red;\"><b>KSAKT: $kontostand_aktuell EUR</b></span>";
        }

        echo "&nbsp;|&nbsp;<span style=\"color:blue;\">KSA BANK: $ksa_bank | KSE BANK(TEMP): " . session()->get('temp_kontostand') . " €</span>";

        session()->put('temp_kontoauszugsnummer', sprintf('%01d', session()->get('umsaetze_ok')[$umsatz_id_temp][3]));
        session()->put('temp_datum', session()->get('umsaetze_ok')[$umsatz_id_temp][6]);

        // $f->fieldset('NAVI', 'navi');
        echo "<table style=\"border:0px;padding:1px;><tr><td padding:1px;\"><tr><td>";
        echo "<form method=\"post\" >";
        $f->hidden_feld('vor', '1');
        $f->send_button('SndNEXT', '', 'arrow-left', '');
        $f->ende_formular();

        echo "</td><td><form method=\"post\">";
        $f->hidden_feld('next', '1');
        $f->send_button('SndNEXT', '', 'arrow-right', '');
        $f->ende_formular();
        echo "</td></tr></table>";
        // $f->fieldset_ende();

        $art = session()->get('umsaetze_ok')[$umsatz_id_temp][13];
        $datum = session()->get('umsaetze_ok')[$umsatz_id_temp][6];
        /* FORMULAR */
        $f->erstelle_formular("$art - Nummer:$akt/$gesamt | $gk->geldkonto_bez | AUSZUG: " . session()->get('temp_kontoauszugsnummer') . " | DATUM: $datum ", null);

        echo "<table >";
        echo "<tr><td valign=\"top\">";
        $zahler = session()->get('umsaetze_ok')[$umsatz_id_temp][25];

        $namen_arr = explode(',', $zahler);
        if (!isset ($namen_arr [1])) {
            $namen_arr = explode(' ', $zahler);
        }
        if (!isset ($namen_arr [1])) {
            $vorname = '';
        } else {
            $vorname = ltrim(rtrim($namen_arr [1]));
        }
        $nachname = ltrim(rtrim($namen_arr [0]));

        $zahler_iban = session()->get('umsaetze_ok')[$umsatz_id_temp][26];
        $zahler_bic = session()->get('umsaetze_ok')[$umsatz_id_temp][27];
        $betrag = session()->get('umsaetze_ok')[$umsatz_id_temp][7];
        $betrag_n = str_replace('.', '', $betrag);
        echo "<b>$zahler</b><br>$zahler_iban<br>$zahler_bic<br><br><b>BETRAG: $betrag €</b>";

        $betrag_punkt = nummer_komma2punkt($betrag_n);
        $datum_sql = date_german2mysql($datum);
        $bu = new buchen ();
        if ($bu->check_buchung(session()->get('geldkonto_id'), $betrag_punkt, session()->get('temp_kontoauszugsnummer'), $datum_sql)) {

            echo "<br><br>";
            fehlermeldung_ausgeben("Betrag bereits gebucht!!!");
        }

        echo "<br><hr><u>Buchungstext: </u><hr>";

        $vzweck = session()->get('umsaetze_ok')[$umsatz_id_temp][14];

        $art = ltrim(rtrim($art));
        if (ltrim(rtrim($art)) == 'ABSCHLUSS' or $art == 'SEPA-UEBERWEIS.HABEN EINZEL' or $art == 'SEPA-CT HABEN EINZELBUCHUNG' or $art == 'SEPA-DD EINZELB.-SOLL B2B' or $art == 'SEPA-DD EINZELB.SOLL B2B' or $art == 'SEPA-DD EINZELB. SOLL CORE' or $art == 'SEPA-CC EINZELB.SOLL' or $art == 'SEPA-CC EINZELB.SOLL KARTE' or $art == 'SEPA-DD EINZELB.SOLL CORE' or $art == 'SEPA Dauerauftragsgutschrift' or $art == 'SEPA DAUERAUFTRAGSGUTSCHR' or $art == 'SEPA-LS EINZELBUCHUNG SOLL' or $art == 'SEPA-UEBERWEIS.HABEN RETOUR' or $art == 'SEPA-CT HABEN RETOUR' or $art == 'ZAHLEINGUEBELEKTRMEDIEN' or $art == 'SCHECKKARTE' or $art == 'ZAHLUNG UEB ELEKTR MEDIEN' or $art == 'LASTSCHRIFT EINZUGSERM') {
            $treffer = array();
            $vzweck_kurz = $vzweck;
            echo $vzweck;
            if (ltrim(rtrim($art)) == 'ABSCHLUSS') {
                $zahler = "Bank";
                $vzweck_kurz = "Kontoführungsgebühr, $vzweck_kurz";
            }
            $f->hidden_feld('text', "$zahler, $vzweck_kurz");

            echo "<b>$zahler, $vzweck_kurz</b>";
            echo "</td><td>";
            $bu = new buchen ();

            $js_typ = "onchange=\"list_kostentraeger('list_kostentraeger', this.value)\"";
            /* Suche nach IBAN */
            if ($zahler_iban) {
                $gk2 = new gk ();
                $gk2->get_kos_by_iban($zahler_iban);
                if (isset ($gk2->iban_kos_typ) && isset ($gk2->iban_kos_typ)) {
                    session()->put('kos_typ', $gk2->iban_kos_typ);
                    session()->put('kos_id', $gk2->iban_kos_id);
                    if ($gk2->iban_kos_typ == 'Eigentuemer') {
                        $bu->dropdown_kostenrahmen_nr('Kostenkonto ET1', 'kostenkonto', 'GELDKONTO', $gk_id, '6020');
                    }
                    if ($gk2->iban_kos_typ == 'Mietvertrag') {
                        $bu->dropdown_kostenrahmen_nr('Kostenkonto MV1', 'kostenkonto', 'GELDKONTO', $gk_id, '80001');
                    }

                    if ($gk2->iban_kos_typ == 'Partner') {
                        $bu->dropdown_kostenrahmen_nr('Kostenkonto P', 'kostenkonto', 'GELDKONTO', $gk_id, '');
                    }

                    if ($gk2->iban_kos_typ == 'Benutzer') {
                        $bu->dropdown_kostenrahmen_nr('Kostenkonto B', 'kostenkonto', 'GELDKONTO', $gk_id, '');
                    }
                    if ($gk2->iban_kos_typ == 'Objekt') {
                        $bu->dropdown_kostenrahmen_nr('Kostenkonto ET1', 'kostenkonto', 'GELDKONTO', $gk_id, '6020');
                        session()->put('kos_typ', 'Eigentuemer');
                    }

                    $bu->dropdown_kostentreager_typen_vw('Kostenträgertyp AUTOIBAN', 'kostentraeger_typ', 'kostentraeger_typ', $js_typ, session()->get('kos_typ'));
                    $bu->dropdown_kostentraeger_bez_vw("Kostenträger IBAN", 'kostentraeger_id', 'dd_kostentraeger_id', '', session()->get('kos_typ'), session()->get('kos_id'));
                    $treffer [] = 'GK';
                }
            }

            if ((strpos(strtolower($vzweck), 'miet') or strpos(strtolower($vzweck), 'hk') or strpos(strtolower($vzweck), 'bk')) && count($treffer) < 1) {
                session()->put('kos_typ', 'Mietvertrag');
                $pe1 = new personen ();
                $treffer = $pe1->finde_kos_typ_id($vorname, $nachname);

                if ($treffer ['ANZ'] > 0) {
                    if ($treffer ['ANZ'] > 1) {
                        $kos_typ = $treffer ['ERG_F'] [0] ['KOS_TYP'];
                        $kos_id = $treffer ['ERG_F'] [0] ['KOS_ID'];
                        $manz = $treffer ['ANZ'];
                        echo "<br>";
                        fehlermeldung_ausgeben("HINWEIS: Mieter kommt mehrmals vor ($manz)!!!");
                        echo "<br>";
                    } else {
                        $kos_typ = $treffer ['ERG'] [0] ['KOS_TYP'];
                        $kos_id = $treffer ['ERG'] [0] ['KOS_ID'];
                    }

                    if ($kos_typ == 'Mietvertrag') {
                        $bu->dropdown_kostenrahmen_nr('Kostenkonto M2', 'kostenkonto', 'GELDKONTO', $gk_id, '80001');
                    }
                    if ($kos_typ == 'Eigentuemer') {
                        $bu->dropdown_kostenrahmen_nr('Kostenkonto E2', 'kostenkonto', 'GELDKONTO', $gk_id, '6020');
                    }

                    $bu->dropdown_kostentreager_typen_vw('Kostenträgertyp PERSON', 'kostentraeger_typ', 'kostentraeger_typ', $js_typ, $kos_typ);
                    $bu->dropdown_kostentraeger_bez_vw("Kostenträger PERSON", 'kostentraeger_id', 'dd_kostentraeger_id', '', $kos_typ, $kos_id);
                } else {
                    // $bu->dropdown_kostenrahmen_nr('Kostenkonto', 'kostenkonto', 'GELDKONTO', $gk_id, '80001');
                    // $bu->dropdown_kostentreager_typen_vw('Kostenträgertyp vorwahl', 'kostentraeger_typ', 'kostentraeger_typ', $js_typ, 'Mietvertrag');

                    $kos_id = $this->get_mvid_from_vzweck($vzweck);
                    if (!isset ($kos_id)) {
                        /* ET_ID from* */
                        // $kos_id = $this->get_etid_from_vzweck($vzweck);
                        // $kos_typ = 'Eigentuemer';
                        // $bu->dropdown_kostenrahmen_nr('Kostenkonto', 'kostenkonto', 'GELDKONTO', $gk_id, '6020');
                        // $bu->dropdown_kostentreager_typen_vw('ET vorwahl C', 'kostentraeger_typ', 'kostentraeger_typ', $js_typ, 'Eigentuemer');
                    } else {
                        $kos_typ = 'Mietvertrag';
                        $bu->dropdown_kostenrahmen_nr('Kostenkonto', 'kostenkonto', 'GELDKONTO', $gk_id, '80001');
                        $bu->dropdown_kostentreager_typen_vw('Kostenträgertyp vorwahl', 'kostentraeger_typ', 'kostentraeger_typ', $js_typ, 'Mietvertrag');
                    }

                    if (isset ($kos_id)) {
                        $bu->dropdown_kostentraeger_bez_vw("Kostenträger MV2", 'kostentraeger_id', 'dd_kostentraeger_id', '', $kos_typ, $kos_id);
                    } else {
                        $bu->dropdown_kostenrahmen_nr('Kostenkonto MMM', 'kostenkonto', 'GELDKONTO', $gk_id, '80001');
                        $bu->dropdown_kostentreager_typen_vw('Kostenträger TYP - UNBEKANNT', 'kostentraeger_typ', 'kostentraeger_typ', $js_typ, 'Mietvertrag');
                        $bu->dropdown_kostentreager_ids('Kostenträger UNBEKANNT1', 'kostentraeger_id', 'dd_kostentraeger_id', '');
                    }
                }

                /*
				 * if($kos_typ=='Mieter'){
				 * $me = new mietentwicklung;
				 * $me->mietentwicklung_anzeigen($kos_id);
				 * }
				 */

                $treffer [] = 'Mieter';
            }

            if ((strpos(strtolower($vzweck), 'hausgeld') or strpos(strtolower($vzweck), 'wohngeld')) && count($treffer) < 1) {
                session()->put('kos_typ', 'Eigentuemer');

                $bu->dropdown_kostenrahmen_nr('Kostenkonto', 'kostenkonto', 'GELDKONTO', $gk_id, '6020');
                $bu->dropdown_kostentreager_typen_vw('Kostenträgertyp vorwahl', 'kostentraeger_typ', 'kostentraeger_typ', $js_typ, 'Eigentuemer');
                $bu->dropdown_kostentreager_ids('Kostenträger', 'kostentraeger_id', 'dd_kostentraeger_id', '');
                $treffer [] = 'Eigentuemer';
            }
            /* Suche na IBAN */
            /* Wenn nichts gefunden */
            if (count($treffer) < 1) {
                session()->forget('kos_typ');
                session()->forget('kos_id');
                session()->forget('kos_bez');

                $pe1 = new personen ();
                $treffer = $pe1->finde_kos_typ_id($vorname, $nachname);

                if ($treffer ['ANZ'] > 0) {
                    if ($treffer ['ANZ'] > 1) {
                        $kos_typ = $treffer ['ERG_F'] [0] ['KOS_TYP'];
                        $kos_id = $treffer ['ERG_F'] [0] ['KOS_ID'];
                        $manz = $treffer ['ANZ'];
                        echo "<br>";
                        fehlermeldung_ausgeben("HINWEIS: Mieter kommt mehrmals vor ($manz)!!!");
                        echo "<br>";
                    } else {
                        $kos_typ = $treffer ['ERG'] [0] ['KOS_TYP'];
                        $kos_id = $treffer ['ERG'] [0] ['KOS_ID'];
                    }

                    if ($kos_typ == 'Mietvertrag') {
                        $bu->dropdown_kostenrahmen_nr('Kostenkonto', 'kostenkonto', 'GELDKONTO', $gk_id, '80001');
                    }
                    if ($kos_typ == 'Eigentuemer') {
                        $bu->dropdown_kostenrahmen_nr('Kostenkonto', 'kostenkonto', 'GELDKONTO', $gk_id, '6020');
                    }

                    $bu->dropdown_kostentreager_typen_vw('Kostenträgertyp PERSON2', 'kostentraeger_typ', 'kostentraeger_typ', $js_typ, $kos_typ);
                    $bu->dropdown_kostentraeger_bez_vw("Kostenträger PERSON2", 'kostentraeger_id', 'dd_kostentraeger_id', '', $kos_typ, $kos_id);

                    //echo "</td></tr><tr><td>";

                    /*
					 * if($kos_typ=='Mietvertrag'){
					 * $me = new mietentwicklung();
					 * $me->mietentwicklung_anzeigen($kos_id);
					 * }
					 */
                }

                if ($treffer ['ANZ'] < 1) {
                    $kos_id = $this->get_mvid_from_vzweck($vzweck);
                    if (isset ($kos_id)) {
                        $kos_typ = 'Mietvertrag';
                        $bu->dropdown_kostenrahmen_nr('Kostenkonto', 'kostenkonto', 'GELDKONTO', $gk_id, '80001');
                        $bu->dropdown_kostentreager_typen_vw('Kostenträgertyp MV2', 'kostentraeger_typ', 'kostentraeger_typ', $js_typ, $kos_typ);
                        $bu->dropdown_kostentraeger_bez_vw("Kostenträger MV2", 'kostentraeger_id', 'dd_kostentraeger_id', '', $kos_typ, $kos_id);
                    } else {
                        $kos_id = $this->get_etid_from_vzweck($vzweck);
                        if (isset ($kos_id)) {
                            $kos_typ = 'Eigentuemer';
                            $bu->dropdown_kostenrahmen_nr('Kostenkonto', 'kostenkonto', 'GELDKONTO', $gk_id, '6020');
                            $bu->dropdown_kostentreager_typen_vw('ET vorwahl', 'kostentraeger_typ', 'kostentraeger_typ', $js_typ, 'Eigentuemer');
                            $bu->dropdown_kostentraeger_bez_vw("Kostenträger MV2", 'kostentraeger_id', 'dd_kostentraeger_id', '', $kos_typ, $kos_id);
                        } else {
                            if ($art == 'ABSCHLUSS') {
                                $bu->dropdown_kostenrahmen_nr('Kostenkonto', 'kostenkonto', 'GELDKONTO', $gk_id, '5060');
                                $bu->dropdown_kostentreager_typen_vw('ET vorwahl', 'kostentraeger_typ', 'kostentraeger_typ', $js_typ, 'Objekt');
                                $bu->dropdown_kostentraeger_bez_vw("Kostenträger MV2", 'kostentraeger_id', 'dd_kostentraeger_id', '', 'Objekt', session()->get('objekt_id'));
                            } else {
                                $bu->dropdown_kostenrahmen_nr('Kostenkonto NIX3', 'kostenkonto', 'GELDKONTO', $gk_id, '80001');
                                $bu->dropdown_kostentreager_typen_vw('Kostenträgertyp NIXX3', 'kostentraeger_typ', 'kostentraeger_typ', $js_typ, 'Mietvertrag');
                                $bu->dropdown_kostentraeger_bez_vw("Kostenträger NIXX3", 'kostentraeger_id', 'dd_kostentraeger_id', '', 'Mietvertrag', null);
                            }
                        }
                    }
                }
            }
            $f->hidden_feld('option', 'excel_einzelbuchung');
            $f->hidden_feld('betrag', $betrag_n);
            $f->check_box_js('mwst', 'mwst', 'Mit Mehrwertsteuer buchen', '', '');
            $f->send_button('SndEB', "Buchen [$betrag EUR]");

            echo "</td>";
        } // ##############ENDE EINZELBUCHUNGEN*/
        if ($art == 'SEPA-UEBERWEIS.SAMMLER-SOLL' or $art == 'SEPA-CT SAMMLER-SOLL') {
            echo $vzweck;
            $pos_svwz = strpos(strtoupper($vzweck), '.XML');
            if ($pos_svwz == true) {
                $vzweck_kurz = substr($vzweck, 0, $pos_svwz + 4);
                $sepa_ue__file = str_replace(' ', '', substr($vzweck_kurz, 5));
            } else {
                $vzweck_kurz = $vzweck;
                $sepa_ue__file = ' ----> SEPA-UEBERWEIS.SAMMLER - DATEI - UNBEKANNT!!!!';
            }
            echo "<br><b>$vzweck_kurz $betrag</b><br>$sepa_ue__file";
            echo "</td></tr>";
            echo "<tr><td colspan=\"2\">";
            $sep = new sepa ();
            $sep->sepa_file_anzeigen($sepa_ue__file);
        }
        /* LASTSCHRIFTEN LS */
        if ($art == 'SEPA-LS SAMMLER-HABEN') {
            echo "<b>$vzweck<br>";
            echo "<h1>LASTSCHRIFTEN</h1>";
            $betrag_punkt = nummer_komma2punkt($betrag_n);
            $arr_ls_files = $this->finde_ls_file_by_monat(session()->get('geldkonto_id'), $betrag_punkt, session()->get('temp_datum'));
            $anz_lf = count($arr_ls_files);
            for ($lf = 0; $lf < $anz_lf; $lf++) {
                $ls_file = $arr_ls_files [$lf] ['DATEI'];
                echo "<form method=\"post\">";
                echo "<table>";
                echo "<tr><th colspan=\"1\">$ls_file</th><th>";
                $f->hidden_feld('ls_file', $ls_file);
                $f->hidden_feld('option', 'excel_ls_sammler_buchung');
                $f->hidden_feld('betrag', $betrag_n);
                $f->check_box_js('mwst', 'mwst', 'Mit Mehrwertsteuer buchen', '', '');
                $f->send_button('SndEB', "Buchen [$betrag EUR]");

                echo "</th></tr>";

                $arr_ls_zeilen = $this->get_sepa_lszeilen_arr($ls_file);
                $anz_ze = count($arr_ls_zeilen);
                for ($ze = 0; $ze < $anz_ze; $ze++) {
                    $zweck_ls = $arr_ls_zeilen [$ze] ['VZWECK'];
                    $betrag_ls = $arr_ls_zeilen [$ze] ['BETRAG'];
                    echo "<tr><td>$zweck_ls</td><td>$betrag_ls</td></tr>";
                }
                echo "</table></form>";
            }
        }
        /* LASTSCHRIFTEN LS */
        if ($art == 'SEPA-LS SOLL RUECKBELASTUNG') {
            echo "<b>$vzweck";
            echo "$betrag</b>";
            $betrag_punkt = nummer_komma2punkt($betrag_n);
            $arr_ls_files = $this->finde_ls_file_by_datum(session()->get('geldkonto_id'), $betrag_punkt, session()->get('temp_datum'));
        }

        if ($art == 'SEPA DIRECT DEBIT (EINZELBUCHUNG-SOLL, B2B)') {
            echo "<b>$vzweck";
            echo "$betrag</b>";
            fehlermeldung_ausgeben("Abbuchung bzw. Rechnungen manuell buchen!!!");
        }

        echo "</td>";
        echo "</tr></table>";
        $f->ende_formular();
    }

    function menue_konten($gk_id)
    {
        $akt_key_int = array_search($gk_id, session()->get('umsatz_konten'));
        $akt_key_aus = $akt_key_int + 1;
        $anz_konten = count(session()->get('umsatz_konten'));
        echo "  <b>Geldkonto: $akt_key_aus/$anz_konten</b>";
    }

    function get_mvid_from_vzweck($vzweck)
    {
        $vzweck = str_replace(',', ' ', $vzweck);
        $vzweck = str_replace('.', ' ', $vzweck);
        $vzweck = str_replace(' -', ' ', $vzweck);
        // echo $vzweck;
        $pos_svwz = strpos(strtoupper($vzweck), 'SVWZ+');
        if ($pos_svwz == true) {
            $vzweck_kurz = str_replace(')', ' ', str_replace('(', ' ', substr($vzweck, $pos_svwz + 5)));
        } else {
            $vzweck_kurz = $vzweck;
        }

        $vzweck_arr = explode(' ', strtoupper($vzweck_kurz));
        $ein = new einheit ();
        $einheiten_arr = $ein->liste_aller_einheiten();

        for ($ei = 0; $ei < count($einheiten_arr); $ei++) {
            $einheit_kurzname = str_replace(' ', '', ltrim(rtrim($einheiten_arr [$ei] ['EINHEIT_KURZNAME'])));
            $ein_arr [] = $einheit_kurzname;

            $pos_leer = strpos($einheiten_arr [$ei] ['EINHEIT_KURZNAME'], ' ');
            if ($pos_leer == true) {
                $erstteil = substr(strtoupper($einheiten_arr [$ei] ['EINHEIT_KURZNAME']), 0, $pos_leer);
                $ein_arr [] = $erstteil;
            }
        }
        unset ($einheiten_arr);
        $new_arr = array_intersect($vzweck_arr, $ein_arr);
        $arr_keys = array_keys($new_arr);
        $anz_keys = count($arr_keys);
        for ($tt = 0; $tt < $anz_keys; $tt++) {
            $key1 = $arr_keys [$tt];
            $new_arr1 [] = $new_arr [$key1];
        }

        /*
		 * echo '<pre>';
		 * print_r($new_arr);
		 * print_r($new_arr1);
		 */
        if (isset ($new_arr1 [0])) {
            $anfang = $new_arr1 [0];
            $einheit_id_n = $ein->finde_einheit_id_by_kurz($anfang);

            $ein->get_mietvertrag_id($einheit_id_n);
            // echo "$anfang $einheit_id_n $ein->mietvertrag_id";
            // $mvs = new mietvertraege();
            // $mvs->get_mietvertrag_infos_aktuell($ein->mietvertrag_id);

            /*
			 * echo '<pre>';
			 * print_r($mvs);
			 * #print_r($array3);
			 * print_r($new_arr1);
			 * #print_r($new_arr1);
			 *
			 * print_r($vzweck_arr);
			 * print_r($ein_arr);
			 */
            if (isset ($ein->mietvertrag_id)) {
                return $ein->mietvertrag_id;
            }
        }
    }

    function get_etid_from_vzweck($vzweck)
    {
        $vzweck = str_replace(',', ' ', $vzweck);
        $vzweck = str_replace('.', ' ', $vzweck);
        $vzweck = str_replace(' -', ' ', $vzweck);
        // echo $vzweck;
        $pos_svwz = strpos(strtoupper($vzweck), 'SVWZ+');
        if ($pos_svwz == true) {
            $vzweck_kurz = str_replace(')', ' ', str_replace('(', ' ', substr($vzweck, $pos_svwz + 5)));
        } else {
            $vzweck_kurz = $vzweck;
        }

        $vzweck_arr = explode(' ', strtoupper($vzweck_kurz));
        $ein = new einheit ();
        $einheiten_arr = $ein->liste_aller_einheiten();

        for ($ei = 0; $ei < count($einheiten_arr); $ei++) {
            $einheit_kurzname = str_replace(' ', '', ltrim(rtrim($einheiten_arr [$ei] ['EINHEIT_KURZNAME'])));
            $ein_arr [] = $einheit_kurzname;

            $pos_leer = strpos($einheiten_arr [$ei] ['EINHEIT_KURZNAME'], ' ');
            if ($pos_leer == true) {
                $erstteil = substr(strtoupper($einheiten_arr [$ei] ['EINHEIT_KURZNAME']), 0, $pos_leer);
                $ein_arr [] = $erstteil;
            }
        }
        unset ($einheiten_arr);
        $new_arr = array_intersect($vzweck_arr, $ein_arr);
        $arr_keys = array_keys($new_arr);
        $anz_keys = count($arr_keys);
        for ($tt = 0; $tt < $anz_keys; $tt++) {
            $key1 = $arr_keys [$tt];
            $new_arr1 [] = $new_arr [$key1];
        }

        /*
		 * echo '<pre>';
		 * print_r($vzweck_arr);
		 * print_r($new_arr);
		 * print_r($new_arr1);
		 */

        if (isset ($new_arr1 [0])) {
            $anfang = $new_arr1 [0];
            $einheit_id_n = $ein->finde_einheit_id_by_kurz($anfang);

            $weg = new weg ();
            $weg->get_last_eigentuemer_id($einheit_id_n);
            if (isset ($weg->eigentuemer_id)) {
                return $weg->eigentuemer_id;
            }
        }
    }

    function sepa_file_anzeigen($file)
    {
        $arr = $this->get_sepa_files_daten_arr($file);
        if (empty($arr)) {
            fehlermeldung_ausgeben("Keine Datensätze zur Datei $file");
        } else {
            $f = new formular ();
            $f->erstelle_formular('SEPA-Datei Vorschau / Autobuchen', null);
            echo "<table class=\"sortable\">";
            echo "<thead><tr><th>EMPFÄNGER</th><th>VZWECK</th><th>IBAN</th><th>BIC</th><th>BETRAG</th><th>KONTO</th></tr></thead>";
            // echo "<tr><th>$kat</th></tr>";
            $sum = 0;
            $anz = count($arr);
            for ($a = 0; $a < $anz; $a++) {
                $empf = $arr [$a] ['BEGUENSTIGTER'];
                $vzweck = $arr [$a] ['VZWECK'];
                $betrag = $arr [$a] ['BETRAG'];
                $sum += $betrag;
                $iban = $arr [$a] ['IBAN'];
                $bic = $arr [$a] ['BIC'];
                $konto = $arr [$a] ['KONTO'];
                $z = $a + 1;
                echo "<tr><td>$z. $empf</td><td>$vzweck</td><td>$iban</td><td>$bic</td><td>$betrag</td><td>$konto</td></tr>";
            }
            echo "<tfoot><tr><th colspan=\"3\">SUMME</th><th><th>$sum</th><th></th></tr></tfoot>";
            echo "<tr><td>";
            $f->hidden_feld('option', 'sepa_ue_autobuchen');
            $f->hidden_feld('file', $file);
            $f->check_box_js('mwst', 'steuer', 'MWSt buchen???', '', '');
            $f->send_button('Btn_AB', 'Automatisch verbuchen!?!');
            $f->ende_formular();
            echo "</td><td><b>Kontrolldaten:<br> Datum: " . session()->get('temp_datum') . "<br>Auszug: " . session()->get('temp_kontoauszugsnummer') . "</td></tr>";
            echo "</table>";
        }
    }

    function finde_ls_file_by_monat($gk_id, $betrag, $datum)
    {
        $datum_t = explode('.', $datum);
        $monat = $datum_t [1];
        $jahr = $datum_t [2];
        $mon_jahr = "$monat$jahr";
        $arr = $this->finde_ls_file_by_betrag($gk_id, $betrag);

        if (!empty($arr)) {
            $anz = count($arr);
            for ($a = 0; $a < $anz; $a++) {
                $datum_xml = explode('-', $arr [$a] ['DATUM']);
                $jahr_xml = $datum_xml [0];
                $mon_xml = $datum_xml [1];
                $mon_jahr_sql = "$mon_xml$jahr_xml";
                if ($mon_jahr == $mon_jahr_sql) {
                    $arr_n [] = $arr [$a];
                }
            }
        }
        if (isset ($arr_n)) {
            return $arr_n;
        }
    }

    function finde_ls_file_by_betrag($gk_id, $betrag)
    {
        $arr = $this->get_sepa_lsfiles_arr_gk($gk_id);
        $arr_n = [];
        if (!empty($arr)) {
            $anz = count($arr);
            for ($a = 0; $a < $anz; $a++) {
                $sum = $arr [$a] ['SUMME'];
                if ($sum == $betrag) {
                    $arr_n [] = $arr [$a];
                }
            }
        }
        return $arr_n;
    }

    function get_sepa_lsfiles_arr_gk($gk_id)
    {
        $vorz = $gk_id . '-';
        $result = DB::select("SELECT COUNT(BETRAG) AS ANZ, DATEI, SUM(BETRAG) AS SUMME, DATUM FROM `SEPA_MANDATE_SEQ` WHERE AKTUELL='1' && DATEI LIKE '$vorz%' GROUP BY DATEI ORDER BY DATUM DESC");
        return $result;
    }

    function finde_ls_file_by_datum($gk_id, $betrag, $datum)
    {
        $datum = date_german2mysql($datum);
        $vorz = $gk_id . '-';
        $result = DB::select("SELECT COUNT(BETRAG) AS ANZ, DATEI, SUM(BETRAG) AS SUMME, DATUM FROM `SEPA_MANDATE_SEQ` WHERE AKTUELL='1' && DATEI LIKE '$vorz%' && DATUM='$datum' GROUP BY DATEI ORDER BY DATUM DESC");
        if (!empty($result)) {
            return $result;
        }
    }

    function form_ds_kontoauszug($ds)
    {
        $js_typ = "onchange=\"list_kostentraeger('list_kostentraeger', this.value)\"";
        $akt = $ds + 1;
        /* FORMULAR */
        if (session()->has('kto_auszug_arr')) {
            $gesamt = count(session()->get('kto_auszug_arr')) - 2;
            $kto_nr = session()->get('kto_auszug_arr')['kto'];
            $kto_blz = session()->get('kto_auszug_arr')['blz'];
            /* Suche nach KTO und BLZ */
            $gk = new gk ();
            $gk_id = $gk->get_geldkonto_id2($kto_nr, $kto_blz);
            if (!$gk_id) {
                throw new \App\Exceptions\MessageException(
                    new \App\Messages\ErrorMessage("Geldkonto <b>$kto_nr - $kto_blz</b> nicht gefunden")
                );
            }
            session()->put('geldkonto_id', $gk_id);
            $gk2 = new geldkonto_info ();
            $gk2->geld_konto_details($gk_id);

            session()->put('temp_datum', session()->get('kto_auszug_arr')[$ds]['datum']);
            session()->put('temp_kontoauszugsnummer', session()->get('kto_auszug_arr')[$ds]['auszug']);

            $f = new formular ();
            $f->erstelle_formular("$gk2->geldkonto_bez | $kto_nr | $kto_blz |DS:$akt/$gesamt AUSZUG: " . session()->get('temp_kontoauszugsnummer') . " | DATUM: " . session()->get('temp_datum'), null);
            $f->text_feld_inaktiv('Name', 'btsdxt', session()->get('kto_auszug_arr')[$ds]['name'], 100, 'bxcvvctdtd');
            $f->text_feld_inaktiv('Buchungstext', 'btxt', session()->get('kto_auszug_arr')[$ds]['vzweck'], 100, 'btdtd');
            $f->hidden_feld('text', session()->get('kto_auszug_arr')[$ds]['vzweck']);
            $f->text_feld_inaktiv('Betrag', 'besd', session()->get('kto_auszug_arr')[$ds]['betrag'], 10, 'btdsdtd');
            $f->hidden_feld('betrag', session()->get('kto_auszug_arr')[$ds]['betrag']);
            $bu = new buchen ();
            $kos_id = $this->get_etid_from_vzweck(session()->get('kto_auszug_arr')[$ds]['vzweck']);
            if (isset ($kos_id)) {
                $kos_typ = 'Eigentuemer';
                $bu->dropdown_kostenrahmen_nr('Kostenkonto', 'kostenkonto', 'GELDKONTO', $gk_id, '6020');
                $bu->dropdown_kostentreager_typen_vw('ET vorwahl', 'kostentraeger_typ', 'kostentraeger_typ', $js_typ, 'Eigentuemer');
                $bu->dropdown_kostentraeger_bez_vw("Kostenträger MV2", 'kostentraeger_id', 'dd_kostentraeger_id', '', $kos_typ, $kos_id);
            } else {
                $kos_id = $this->get_mvid_from_vzweck(session()->get('kto_auszug_arr')[$ds]['vzweck']);
                if (isset ($kos_id)) {
                    $kos_typ = 'Mietvertrag';
                    $bu->dropdown_kostenrahmen_nr('Kostenkonto', 'kostenkonto', 'GELDKONTO', $gk_id, '80001');
                    $bu->dropdown_kostentreager_typen_vw('MV vorwahl', 'kostentraeger_typ', 'kostentraeger_typ', $js_typ, 'Eigentuemer');
                    $bu->dropdown_kostentraeger_bez_vw("Kostenträger MV2", 'kostentraeger_id', 'dd_kostentraeger_id', '', $kos_typ, $kos_id);
                } else {

                    $pe1 = new personen ();
                    $namen_arr = explode(' ', str_replace(',', '', session()->get('kto_auszug_arr')[$ds]['name']));
                    $vorname = $namen_arr [0];
                    $nachname = $namen_arr [1];
                    $treffer = $pe1->finde_kos_typ_id($vorname, $nachname);

                    if ($treffer ['ANZ'] > 0) {
                        if ($treffer ['ANZ'] > 1) {
                            $kos_typ = $treffer ['ERG_F'] [0] ['KOS_TYP'];
                            $kos_id = $treffer ['ERG_F'] [0] ['KOS_ID'];
                        } else {
                            $kos_typ = $treffer ['ERG'] [0] ['KOS_TYP'];
                            $kos_id = $treffer ['ERG'] [0] ['KOS_ID'];
                        }

                        if ($kos_typ == 'Mietvertrag') {
                            $bu->dropdown_kostenrahmen_nr('Kostenkonto PPP', 'kostenkonto', 'GELDKONTO', $gk_id, '80001');
                            $bu->dropdown_kostentreager_typen_vw('MV vorwahl', 'kostentraeger_typ', 'kostentraeger_typ', $js_typ, 'Mietvertrag');
                            $bu->dropdown_kostentraeger_bez_vw("Kostenträger MV2", 'kostentraeger_id', 'dd_kostentraeger_id', '', $kos_typ, $kos_id);
                        }
                        if ($kos_typ == 'Eigentuemer') {
                            $bu->dropdown_kostenrahmen_nr('Kostenkonto PPP', 'kostenkonto', 'GELDKONTO', $gk_id, '6020');
                            $bu->dropdown_kostentreager_typen_vw('MV vorwahl', 'kostentraeger_typ', 'kostentraeger_typ', $js_typ, 'Eigentuemer');
                            $bu->dropdown_kostentraeger_bez_vw("Kostenträger MV2", 'kostentraeger_id', 'dd_kostentraeger_id', '', $kos_typ, $kos_id);
                        }
                    } else {

                        $bu->dropdown_kostenrahmen_nr('Kostenkonto', 'kostenkonto', 'GELDKONTO', $gk_id, '');
                        $bu->dropdown_kostentreager_typen('Kostenträgertyp NIXX', 'kostentraeger_typ', 'kostentraeger_typ', $js_typ);
                        $bu->dropdown_kostentreager_ids('Kostenträger NIXX', 'kostentraeger_id', 'dd_kostentraeger_id', '');
                    }

                    /*
					 * if(!$kos_typ && !$kos_id){
					 *
					 * $bu->dropdown_kostenrahmen_nr('Kostenkonto', 'kostenkonto', 'GELDKONTO', $gk_id, '');
					 * $bu->dropdown_kostentreager_typen('Kostenträgertyp NIXX', 'kostentraeger_typ', 'kostentraeger_typ', $js_typ);
					 * $bu->dropdown_kostentreager_ids('Kostenträger NIXX', 'kostentraeger_id', 'dd_kostentraeger_id', '');
					 *
					 * }
					 */
                }
            }
            $f->hidden_feld('option', 'excel_einzelbuchung');
            $f->check_box_js('mwst', 'mwst', 'Mit Mehrwertsteuer buchen', '', '');
            $betrag = session()->get('kto_auszug_arr')[$ds]['betrag'];
            $f->send_button('SndEB', "Buchen [$betrag EUR]");
            $f->ende_formular();
        } else {
            fehlermeldung_ausgeben("Keine Daten");
        }
    }

    function form_upload_excel_ktoauszug($action = null)
    {
        $f = new formular ();
        $f->fieldset('Upload Excel-Kontoauszüge aus Bank *.XLSX', 'upxel');
        if ($action == null) {
            echo "<form method=\"post\" enctype=\"multipart/form-data\">";
        } else {
            echo "<form method=\"post\" enctype=\"multipart/form-data\" action=\"$action\">";
        }
        ?>
        <div class="file-field input-field">
            <div class="btn">
                <span>Datei</span>
                <input type="file" name="file">
            </div>
            <div class="file-path-wrapper">
                <input class="file-path validate" type="text" placeholder="Bitte laden Sie einen Kontoauszug hoch.">
            </div>
        </div>
        <button class="btn waves-effect waves-light" type="submit">Hochladen
            <i class="mdi mdi-send right"></i>
        </button>
        <?php
        echo "</form>";
        $f->fieldset_ende();
    }
} // end class sepa