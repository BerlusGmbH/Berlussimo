<?php

class weg
{
    public $weg_anteile;
    public $e;
    public $eigentuemer_id;
    public $gruppe_erg;
    public $Wohngeld_soll_vj_a;
    public $Wohngeld_soll_a;
    public $kostenkat_erg;
    public $kostenkat_erg_a;
    public $e_konto;
    public $von;
    public $bis;
    public $eigentuemer_name;
    public $einheit_kurzname;
    public $empf_namen_u;
    public $einheit_id;
    public $eigentuemer_von;
    public $eigentuemer_bis;
    public $et_code;
    public $einheit_qm;
    public $einheit_qm_weg_d;
    public $einheit_qm_weg;
    public $haus_strasse;
    public $haus_nummer;
    public $haus_plz;
    public $haus_stadt;
    public $post_anschrift_haus;
    public $personen_id_arr;
    public $einheit_lage;
    public $einheit_qm_d;
    public $versprochene_miete;
    public $objekt_id;
    public $anz_personen;
    public $anschriften;
    public $personen_id_arr1;
    public $anrede_brief;
    public $anz_zustell;
    public $anz_anschrift;
    public $post_anschrift;
    public $eigentuemer_person_ids;
    public $eigentuemer_name_str;
    public $eigentuemer_name_str_u;
    public $eigentuemer_anzahl;
    public $eigentuemer_namen;
    public $eigentuemer_namen2;
    public $hg_erg_a;
    public $saldo_jahr;
    public $zb_bisher;
    public $hg_erg;
    public $Wohngeld_soll;
    public $Wohngeld_soll_g;
    public $hg_saldo;
    public $anschriften_p;
    public $zustellanschriften;
    public $zustellanschriften_p;
    public $empf_namen;
    public $eigentuemer_von_a;
    public $saldo_jahr_a;
    public $postanschrift;
    public $eigentuemer_name_str_u1;
    public $pdf_anrede;
    public $eig_namen_u;
    public $eig_namen_u_pdf;
    public $wg_def_kos_id;
    public $wg_def_von_d;
    public $wg_def_bis_d;
    public $wg_def_id;
    public $wg_def_von;
    public $wg_def_bis;
    public $wg_def_betrag;
    public $wg_def_betrag_a;
    public $wg_def_koskat;
    public $wg_def_e_konto;
    public $wg_def_g_konto;
    public $wg_def_kos_typ;
    public $wg_def_kos_aktuell;
    public $wg_def_gruppen_bez;
    public $hg_saldo_a;
    public $anrede;
    public $eigentuemer_namen_a;
    public $g_konto;
    public $zahlung_gesamt;
    public $soll_gesamt;
    public $soll_aktuell;
    public $SEPA_MANDAT;
    public $SEPA_MANDAT_AKTIV;
    public $MAND;
    public $hausgeld_einnahmen_summe_a;
    public $hausgeld_einnahmen_summe;
    public $summe_kosten_hg;
    public $summe_kosten_hg_a;
    public $wp_jahr;
    public $hausgelder_neu;
    public $wp_objekt_id;
    public $wp_objekt_name;
    public $einheit_anteile;
    public $einheit_anteile_a;
    public $geldkonto_id;
    public $OBJ_KONTONUMMER;
    public $OBJ_BLZ;
    public $OBJ_BIC;
    public $OBJ_IBAN;
    public $OBJ_IBAN1;
    public $p_jahr;
    public $p_gk_id;
    public $p_objekt_id;
    public $summe_zeilen;
    public $summe_zeilen_a;
    public $footer_zahlungshinweis;
    public $OBJ_BEGUENSTIGTER;
    public $OBJ_GELD_INSTITUT;
    public $BIC;
    public $IBAN;
    public $NAME;
    public $p_bez;
    public $p_ihr_gk_id;
    public $p_wplan_id;
    public $hg_konto;
    public $hk_konto;
    public $ihr_konto;
    public $p_von;
    public $p_bis;
    public $p_von_d;
    public $p_bis_d;
    public $summe_hndl;
    public $konto_has_entry;
    public $man3;
    public $summe_alle_diff_a;
    public $summe_alle_ist;
    public $summe_alle_diff;
    public $summe_alle_soll;
    public $ausstehend;
    public $summe_alle_ist_a;
    public $summe_alle_soll_a;
    public $n_tage;
    public $eigentuemer_von_d;
    public $eigentuemer_bis_d;
    public $eigentuemer_von_t;
    public $eigentuemer_bis_t;
    public $eigentuemer_bis_t_a;
    public $eigentuemer_von_t_a;
    public $summe_hndl_a;
    public $konto_su_auszahlen;

    function uebersicht_einheit($einheit_id)
    {
        $f = new formular ();
        $f->fieldset('Übersicht Einheit', 'u_id');
        $this->get_weg_einheit_info($einheit_id);
        $f->fieldset_ende();
    }

    function get_weg_einheit_info($einheit_id)
    {
        $e = new einheit ();
        $e->get_einheit_info($einheit_id);
        $d = new detail ();
        $this->weg_anteile = $d->finde_detail_inhalt('Einheit', $einheit_id, 'WEG-Anteile');
        $this->e = $e;

        /* OBJEKT DETAILS */
        $de = new detail ();
        $details_obj = $de->finde_alle_details_arr('Objekt', $e->objekt_id);
        if (!empty($details_obj)) {
            echo "<table>";
            echo "<tr><th colspan=\"2\">OBJEKT DETAILS</th></tr>";
            $anz_det = count($details_obj);
            for ($dd = 0; $dd < $anz_det; $dd++) {
                $d_name = $details_obj [$dd] ['DETAIL_NAME'];
                echo "<tr>";
                echo "<td>$d_name</td>";
                echo "<td>" . $details_obj [$dd] ['DETAIL_INHALT'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }

        echo "<table class=\"sortable\">";
        echo "<tr><th>EINHEIT</th><th>$e->einheit_kurzname</th></tr>";
        echo "<tr><td>TYP</td><td>$e->typ</td></tr>";
        echo "<tr><td>ANSCHRIFT</td><td>$e->haus_strasse $e->haus_nummer, $e->haus_plz $e->haus_stadt</td></tr>";
        echo "<tr><td>LAGE</td><td>$e->einheit_lage</td></tr>";
        echo "<tr><td>OBJEKT</td><td>$e->objekt_name</td></tr>";
        echo "<tr><td>FLÄCHE</td><td>$e->einheit_qm m²</td></tr>";
        echo "<tr><td>ANTEILE</td><td>$this->weg_anteile</td></tr>";
        echo "</table>";

        /* EINHEIT DETAILS */
        $de = new detail ();
        $details_e = $de->finde_alle_details_arr('Einheit', $einheit_id);
        if (!empty($details_e)) {
            echo "<table>";
            echo "<tr><th colspan=\"2\">EINHEIT DETAILS</th></tr>";
            $anz_det = count($details_e);
            for ($dd = 0; $dd < $anz_det; $dd++) {
                $d_name = $details_e [$dd] ['DETAIL_NAME'];
                echo "<tr>";
                echo "<td>$d_name</td>";
                echo "<td>" . $details_e [$dd] ['DETAIL_INHALT'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }

        echo "<table>";
        $this->get_last_eigentuemer_ausgabe($einheit_id);
        echo "</table>";

        /* ET DETAILS */
        $de = new detail ();
        $details_et = $de->finde_alle_details_arr('Eigentuemer', $this->eigentuemer_id);
        if (!empty($details_et)) {

            echo "<table>";
            echo "<tr><th colspan=\"2\">ET DETAILS</th></tr>";
            $anz_det = count($details_et);
            for ($dd = 0; $dd < $anz_det; $dd++) {
                $d_name = $details_et [$dd] ['DETAIL_NAME'];
                echo "<tr>";
                echo "<td>$d_name</td>";
                echo "<td>" . $details_et [$dd] ['DETAIL_INHALT'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
        echo "<table>";

        $v_monat = 12;
        $vorjahr = date("Y") - 1;
        $monat = date("m");
        $jahr = date("Y");
        $this->get_wg_info($v_monat, $vorjahr, 'Einheit', $einheit_id, 'Hausgeld');
        $this->Wohngeld_soll_vj_a = nummer_punkt2komma($this->gruppe_erg);
        $this->get_wg_info($monat, $jahr, 'Einheit', $einheit_id, 'Hausgeld');
        $this->Wohngeld_soll_a = nummer_punkt2komma($this->gruppe_erg);

        $wg_keys = $this->wg_def_in_array('Einheit', $einheit_id, $monat, $jahr);
        if (!empty($wg_keys)) {
            echo "<tr><th colspan=\"2\">HAUSGELD</th></tr>";
            $anz = count($wg_keys);
            for ($a = 0; $a < $anz; $a++) {
                $kostenkat = $wg_keys [$a] ['KOSTENKAT'];
                $this->get_kostenkat_info($monat, $jahr, 'Einheit', $einheit_id, $kostenkat);
                $this->kostenkat_erg_a = nummer_punkt2komma($this->kostenkat_erg);
                echo "<tr><td><b>$this->e_konto</b> $kostenkat</td><td>$this->kostenkat_erg_a €</td></tr>";
            }
            echo "<tr><td>HAUSGELD AKTUELL</td><td>$this->Wohngeld_soll_a €</td></tr>";
        }

        $v_monat_name = monat2name($v_monat);
        echo "<tr><td>HAUSGELD $v_monat_name $vorjahr</td><td>$this->Wohngeld_soll_vj_a €</td></tr>";
        echo "</table>";

        $link_auftrage_im_haus_objekt = "<a href='" . route('web::construction::legacy', ['option' => 'auftrag_haus', 'haus_id' => $e->haus_id, 'einheit_id' => $einheit_id]) . "'>Aufträge im Haus - > HIER KLICKEN!!!!</a>";

        echo "<table><tr><th>$link_auftrage_im_haus_objekt</th></tr></table>";

        $t = new todo ();
        $t_arr = $t->get_auftraege_einheit('Einheit', $einheit_id, 0);

        $anz_t = count($t_arr);
        echo "<table>";
        echo "<tr><th style=\"background-color:red;\">OFFEN EINHEIT</th></tr>";
        echo "<tr><th>DATUM</th><th>VON/AN</th><th>TEXT</th></tr>";
        for ($t = 0; $t < $anz_t; $t++) {
            $txt = $t_arr [$t] ['TEXT'];
            $d_erstellt = date_mysql2german($t_arr [$t] ['ANZEIGEN_AB']);
            $t_id = $t_arr [$t] ['T_ID'];
            $verfasser_id = $t_arr [$t] ['VERFASSER_ID'];
            $b = new benutzer ();
            $b->get_benutzer_infos($verfasser_id);
            $verfasser_name = $b->benutzername;
            $beteiligt_id = $t_arr [$t] ['BENUTZER_ID'];
            $beteiligt_typ = $t_arr [$t] ['BENUTZER_TYP'];
            if ($beteiligt_typ == 'Benutzer' or empty ($beteiligt_typ)) {

                $b1 = new benutzer ();
                $b1->get_benutzer_infos($beteiligt_id);
                $beteiligt_name = "<b>$b1->benutzername</b>";
            }

            if ($beteiligt_typ == 'Partner') {
                $pp = new partners ();
                $pp->get_partner_info($beteiligt_id);
                $beteiligt_name = "<b>$pp->partner_name</b>";
            }

            $link_pdf = "<a href='" . route('web::construction::legacy', ['option' => 'pdf_auftrag', 'proj_id' => $t_id]) . "'><img src=\"images/pdf_dark.png\"></a>";
            $link_txt = "<a href='" . route('web::construction::legacy', ['option' => 'edit', 't_id' => $t_id]) . "'>$txt</a>";

            echo "<tr><td>$d_erstellt<br>$link_pdf</td><td>$verfasser_name<br>$beteiligt_name</td><td>$link_txt</td></tr>";
        }
        echo "</table>";

        $t = new todo ();
        $t_arr = $t->get_auftraege_einheit('Einheit', $einheit_id, 1);
        $anz_t = count($t_arr);
        echo "<table>";
        echo "<tr><th style=\"background-color:green;\">ERLEDIGT EINHEIT</th></tr>";
        echo "<tr><th>DATUM</th><th>VON/AN</th><th>TEXT</th></tr>";
        for ($t = 0; $t < $anz_t; $t++) {
            $txt = $t_arr [$t] ['TEXT'];
            $d_erstellt = date_mysql2german($t_arr [$t] ['ANZEIGEN_AB']);
            $t_id = $t_arr [$t] ['T_ID'];
            $verfasser_id = $t_arr [$t] ['VERFASSER_ID'];
            $b = new benutzer ();
            $b->get_benutzer_infos($verfasser_id);
            $verfasser_name = $b->benutzername;
            $beteiligt_id = $t_arr [$t] ['BENUTZER_ID'];
            $beteiligt_typ = $t_arr [$t] ['BENUTZER_TYP'];
            if ($beteiligt_typ == 'Benutzer' or empty ($beteiligt_typ)) {

                $b1 = new benutzer ();
                $b1->get_benutzer_infos($beteiligt_id);
                $beteiligt_name = "<b>$b1->benutzername</b>";
            }

            if ($beteiligt_typ == 'Partner') {
                $pp = new partners ();
                $pp->get_partner_info($beteiligt_id);
                $beteiligt_name = "<b>$pp->partner_name</b>";
            }

            $link_pdf = "<a href='" . route('web::construction::legacy', ['option' => 'pdf_auftrag', 'proj_id' => $t_id]) . "'><img src=\"images/pdf_dark.png\"></a>";
            $link_txt = "<a href='" . route('web::construction::legacy', ['option' => 'edit', 't_id' => $t_id]) . "'>$txt</a>";

            echo "<tr><td>$d_erstellt<br>$link_pdf</td><td>$verfasser_name<br>$beteiligt_name</td><td>$link_txt</td></tr>";
        }
        echo "</table>";

        unset ($t_arr);
        $t = new todo ();
        $t_arr = $t->get_auftraege_einheit('Eigentuemer', $this->eigentuemer_id, 0);
        $anz_t = count($t_arr);
        echo "<table>";
        echo "<tr><th style=\"background-color:blue;\">AN ET: OFFEN</th></tr>";
        echo "<tr><th>DATUM</th><th>VON/AN</th><th>TEXT</th></tr>";
        for ($t = 0; $t < $anz_t; $t++) {
            $txt = $t_arr [$t] ['TEXT'];
            $d_erstellt = date_mysql2german($t_arr [$t] ['ANZEIGEN_AB']);
            $t_id = $t_arr [$t] ['T_ID'];
            $verfasser_id = $t_arr [$t] ['VERFASSER_ID'];
            $b = new benutzer ();
            $b->get_benutzer_infos($verfasser_id);
            $verfasser_name = $b->benutzername;
            $beteiligt_id = $t_arr [$t] ['BENUTZER_ID'];
            $beteiligt_typ = $t_arr [$t] ['BENUTZER_TYP'];
            if ($beteiligt_typ == 'Benutzer' or empty ($beteiligt_typ)) {

                $b1 = new benutzer ();
                $b1->get_benutzer_infos($beteiligt_id);
                $beteiligt_name = "<b>$b1->benutzername</b>";
            }

            if ($beteiligt_typ == 'Partner') {
                $pp = new partners ();
                $pp->get_partner_info($beteiligt_id);
                $beteiligt_name = "<b>$pp->partner_name</b>";
            }

            $link_pdf = "<a href='" . route('web::construction::legacy', ['option' => 'pdf_auftrag', 'proj_id' => $t_id]) . "'><img src=\"images/pdf_dark.png\"></a>";
            $link_txt = "<a href='" . route('web::construction::legacy', ['option' => 'edit', 't_id' => $t_id]) . "'>$txt</a>";

            echo "<tr><td>$d_erstellt<br>$link_pdf</td><td>$verfasser_name<br>$beteiligt_name</td><td>$link_txt</td></tr>";
        }
        echo "</table>";

        unset ($t_arr);
        $t = new todo ();
        $t_arr = $t->get_auftraege_einheit('Eigentuemer', $this->eigentuemer_id, 1);
        $anz_t = count($t_arr);
        echo "<table>";
        echo "<tr><th style=\"background-color:skyblue;\">AN ET: ERLEDIGT</th></tr>";
        echo "<tr><th>DATUM</th><th>VON/AN</th><th>TEXT</th></tr>";
        for ($t = 0; $t < $anz_t; $t++) {
            $txt = $t_arr [$t] ['TEXT'];
            $d_erstellt = date_mysql2german($t_arr [$t] ['ANZEIGEN_AB']);
            $t_id = $t_arr [$t] ['T_ID'];
            $verfasser_id = $t_arr [$t] ['VERFASSER_ID'];
            $b = new benutzer ();
            $b->get_benutzer_infos($verfasser_id);
            $verfasser_name = $b->benutzername;
            $beteiligt_id = $t_arr [$t] ['BENUTZER_ID'];
            $beteiligt_typ = $t_arr [$t] ['BENUTZER_TYP'];
            if ($beteiligt_typ == 'Benutzer' or empty ($beteiligt_typ)) {

                $b1 = new benutzer ();
                $b1->get_benutzer_infos($beteiligt_id);
                $beteiligt_name = "<b>$b1->benutzername</b>";
            }

            if ($beteiligt_typ == 'Partner') {
                $pp = new partners ();
                $pp->get_partner_info($beteiligt_id);
                $beteiligt_name = "<b>$pp->partner_name</b>";
            }

            $link_pdf = "<a href='" . route('web::construction::legacy', ['option' => 'pdf_auftrag', 'proj_id' => $t_id]) . "'><img src=\"images/pdf_dark.png\"></a>";
            $link_txt = "<a href='" . route('web::construction::legacy', ['option' => 'edit', 't_id' => $t_id]) . "'>$txt</a>";

            echo "<tr><td>$d_erstellt<br>$link_pdf</td><td>$verfasser_name<br>$beteiligt_name</td><td>$link_txt</td></tr>";
        }
        echo "</table>";
    }

    function get_last_eigentuemer_ausgabe($einheit_id)
    {
        $this->get_last_eigentuemer($einheit_id);
        if (isset ($this->eigentuemer_name)) {
            $anz = count($this->eigentuemer_name);
        } else {
            $this->eigentuemer_name = 'UNBEKANNT!!!';
        }
        if (isset ($anz)) {
            for ($a = 0; $a < $anz; $a++) {
                $nachname = $this->eigentuemer_name [$a] ['Nachname'];
                $vorname = $this->eigentuemer_name [$a] ['Vorname'];
                $person_id = $this->eigentuemer_name [$a] ['person_id'];
                $eig_zahl = $a + 1;
                echo "<tr><th>EIGENTÜMER $eig_zahl</th><th>$vorname $nachname</th></tr>";
                $d = new detail ();
                $arr = $d->finde_alle_details_arr('Person', $person_id);
                $anz_detail = count($arr);
                for ($b = 0; $b < $anz_detail; $b++) {
                    $detail_name = $arr [$b] ['DETAIL_NAME'];
                    $detail_inhalt = $arr [$b] ['DETAIL_INHALT'];
                    $detail_bem = $arr [$b] ['DETAIL_BEMERKUNG'];
                    echo "<tr><td>$detail_name</td><td>$detail_inhalt";
                    if (!empty ($detail_bem)) {
                        echo "<br>($detail_bem)";
                    }
                    echo "</td></tr>";
                }
            }
        } else {
            $this->eigentuemer_name = 'UNBEKANNT!!!';
            fehlermeldung_ausgeben("Eigentümer unbekannt oder nicht hinterlegt!!!");
        }
    }

    function get_last_eigentuemer($einheit_id)
    {
        $arr = $this->get_last_eigentuemer_arr($einheit_id);
        $anz = count($arr);
        if (!$anz) {
        } else {
            $this->von = $arr ['VON'];
            $this->bis = $arr ['BIS'];
            $this->eigentuemer_id = $arr ['ID'];

            $personen_id_arr = $this->get_person_id_eigentuemer_arr($this->eigentuemer_id);
            $anz_p = count($personen_id_arr);
            unset ($this->eigentuemer_name);
            for ($a = 0; $a < $anz_p; $a++) {
                $person_id = $personen_id_arr [$a] ['PERSON_ID'];
                $p = new personen ();
                $p->get_person_infos($person_id);
                $this->eigentuemer_name [$a] ['person_id'] = $person_id;
                $this->eigentuemer_name [$a] ['Nachname'] = $p->person_nachname;
                $this->eigentuemer_name [$a] ['Vorname'] = $p->person_vorname;
                $this->eigentuemer_name [$a] ['Geburtstag'] = $p->person_geburtstag;
                $this->eigentuemer_name [$a] ['Geschlecht'] = $p->geschlecht;
            }
        }
    }

    function get_last_eigentuemer_arr($einheit_id)
    {
        $result = DB::select("SELECT * FROM WEG_MITEIGENTUEMER WHERE EINHEIT_ID='$einheit_id' && AKTUELL='1' ORDER BY VON DESC LIMIT 0,1");
        if (!empty($result)) {
            return $result[0];
        }
    }

    function get_person_id_eigentuemer_arr($id)
    {
        $result = DB::select("SELECT PERSON_ID FROM WEG_EIGENTUEMER_PERSON WHERE WEG_EIG_ID='$id' && AKTUELL='1'");
        return $result;
    }

    function get_wg_info($monat, $jahr, $kos_typ, $kos_id, $gruppe)
    {
        $this->gruppe_erg = $this->get_summe_kostenkat_gruppe_m2($monat, $jahr, $kos_typ, $kos_id, $gruppe);
    }

    function get_summe_kostenkat_gruppe_m2($monat, $jahr, $kos_typ, $kos_id, $gruppe)
    {
        $result = DB::select("SELECT SUM(BETRAG) AS SUMME, G_KONTO FROM WEG_WG_DEF WHERE KOS_TYP='$kos_typ' && KOS_ID='$kos_id' && E_KONTO!=6050 && AKTUELL='1' && ( ENDE = '0000-00-00' OR DATE_FORMAT( ENDE, '%Y-%m' ) >= '$jahr-$monat' && DATE_FORMAT( ANFANG, '%Y-%m' ) <= '$jahr-$monat' ) && DATE_FORMAT( ANFANG, '%Y-%m' ) <= '$jahr-$monat' && GRUPPE = '$gruppe' ORDER BY ANFANG ASC");
        if (!empty($result)) {
            $row = $result[0];
            $summe = $row ['SUMME'];
            return $summe;
        }
    }

    function wg_def_in_array($kos_typ, $kos_id, $monat, $jahr)
    {
        $result = DB::select("SELECT KOSTENKAT FROM WEG_WG_DEF WHERE KOS_TYP LIKE '$kos_typ' && KOS_ID='$kos_id' && AKTUELL='1' && ( ENDE = '0000-00-00' OR DATE_FORMAT( ENDE, '%Y-%m' ) >= '$jahr-$monat' && DATE_FORMAT( ANFANG, '%Y-%m' ) <= '$jahr-$monat' ) && DATE_FORMAT( ANFANG, '%Y-%m' ) <= '$jahr-$monat' GROUP BY KOSTENKAT ORDER BY E_KONTO ASC");
        return $result;
    }

    function get_kostenkat_info($monat, $jahr, $kos_typ, $kos_id, $kostenkat)
    {
        $this->kostenkat_erg = $this->get_summe_kostenkat($monat, $jahr, $kos_typ, $kos_id, $kostenkat);
    }

    function get_summe_kostenkat($monat, $jahr, $kos_typ, $kos_id, $kostenkat)
    {
        $result = DB::select("SELECT SUM(BETRAG) AS SUMME, E_KONTO FROM WEG_WG_DEF WHERE KOS_TYP LIKE '$kos_typ' && KOS_ID='$kos_id' && AKTUELL='1' && ( ENDE = '0000-00-00' OR DATE_FORMAT( ENDE, '%Y-%m' ) >= '$jahr-$monat' && DATE_FORMAT( ANFANG, '%Y-%m' ) <= '$jahr-$monat' ) && DATE_FORMAT( ANFANG, '%Y-%m' ) <= '$jahr-$monat' && KOSTENKAT = '$kostenkat' ORDER BY ANFANG ASC");
        $row = $result[0];
        if (!empty ($row ['SUMME'])) {
            $this->e_konto = $row ['E_KONTO'];
            return $row ['SUMME'];
        }
    }

    function get_summe_kostenkat_monat($monat, $jahr, $kos_typ, $kos_id, $e_konto)
    {
        $result = DB::select("SELECT SUM(BETRAG) AS SUMME, G_KONTO, ANFANG, ENDE FROM WEG_WG_DEF WHERE KOS_TYP='$kos_typ' && KOS_ID='$kos_id' && AKTUELL='1' && ( ENDE = '0000-00-00' OR DATE_FORMAT( ENDE, '%Y-%m' ) >= '$jahr-$monat' && DATE_FORMAT( ANFANG, '%Y-%m' ) <= '$jahr-$monat' ) && DATE_FORMAT( ANFANG, '%Y-%m' ) <= '$jahr-$monat' && E_KONTO = '$e_konto' ORDER BY ANFANG ASC");
        $row = $result[0];
        if (!empty ($row ['SUMME'])) {
            return $row ['SUMME'];
        }
    }

    function form_eigentuemer_einheit($objekt_id)
    {
        $f = new formular ();
        $mv = new mietvertraege ();
        $f->fieldset('Eigentuemer zu Einheit', 'ee_id');
        $f->erstelle_formular('Eigentümerwechsel', '');
        $f->text_feld_inaktiv('Aktueller Eigentümer', 'ae', 'Erst Einheit wählen', '50', 'ae');
        $js = "onchange=\"get_eigentuemer('einheit_id','ae')\"";
        $this->dropdown_einheiten('Einheit', 'einheit_id', 'einheit_id', $objekt_id, $js);
        $javaaction = "onchange=\"add2list('q_liste','z_liste')\"";
        $mv->dropdown_personen_liste('Personen als Eigentümer wählen', 'q_liste', 'q_liste', $javaaction);
        $javaaction1 = "onchange=\"remove_from_dd('z_liste')\"";
        $mv->ausgewahlte_mieter_liste('Ausgewählte Personen', 'z_liste[]', 'z_liste', $javaaction1, '5');
        $f->datum_feld('Eigentuemer seit', 'eigentuemer_seit', '', 'eigentuemer_seit');
        $f->datum_feld('Eigentuemer bis', 'eigentuemer_bis', '', 'eigentuemer_bis');
        $f->hidden_feld('option', 'eigentuemer_send');
        $f->send_button('Button', 'Eintragen');
        $f->ende_formular();
        $f->fieldset_ende();
    }

    function dropdown_einheiten($label, $name, $id, $objekt_id, $js)
    {
        $einheiten_arr = $this->einheiten_weg_tabelle_arr($objekt_id);
        if (!empty($einheiten_arr)) {
            echo "<label for=\"$id\">$label</label>";
            echo "<select name=\"$name\" size=\"1\" id=\"$id\" $js>\n";
            $anz = count($einheiten_arr);
            //echo "$anz Einheiten";
            echo "<option>Bitte wählen</option>\n";
            for ($a = 0; $a < $anz; $a++) {
                $einheit_id = $einheiten_arr [$a] ['EINHEIT_ID'];
                $e = new einheit ();
                $e->get_einheit_info($einheit_id);
                echo "<option value=\"$einheit_id\">$e->einheit_kurzname</option>\n";
            }
            echo "</select>\n";
        } else {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\WarningMessage('Keine Einheiten im Objekt vorhanden.')
            );
        }
    }

    function einheiten_weg_tabelle_arr($objekt_id)
    {
        $o = new objekt ();
        $einheiten_arr = $o->einheiten_objekt_arr($objekt_id);
        return $einheiten_arr;
    }

    function form_eigentuemer_aendern($et_id)
    {
        $f = new formular ();
        $mv = new mietvertraege ();
        $f->fieldset('Eigentuemer zu Einheit', 'ee_id');
        $this->get_eigentumer_id_infos4($et_id);
        $f->text_feld_inaktiv("Aktueller Eigentümer der Einheit $this->einheit_kurzname", 'ae', "$this->empf_namen_u", '50', 'ae');
        $f->erstelle_formular('Eigentümerdaten ändern', '');
        $bu = new buchen ();
        $bu->dropdown_kostentraeger_bez_vw('Einheit', 'einheit_id', 'einheit_id', '', 'Einheit', $this->einheit_id);
        $javaaction = "onchange=\"add2list('q_liste','z_liste')\"";
        $mv->dropdown_personen_liste('Personen als Eigentümer wählen', 'q_liste', 'q_liste', $javaaction);
        $javaaction1 = "onclick=\"remove_from_dd('z_liste')\"";
        $this->ausgewahlte_et_liste_aendern('Ausgewählte Personen', 'z_liste[]', 'z_liste', $javaaction1, '5', '');
        $eigentuemer_von = date_mysql2german($this->eigentuemer_von);
        $eigentuemer_bis = date_mysql2german($this->eigentuemer_bis);
        $f->datum_feld('Eigentuemer seit', 'eigentuemer_seit', "$eigentuemer_von", 'eigentuemer_seit');
        $f->datum_feld('Eigentuemer bis', 'eigentuemer_bis', "$eigentuemer_bis", 'eigentuemer_bis');
        $f->hidden_feld('et_id', $et_id);
        $f->hidden_feld('option', 'eigentuemer_send_aendern');
        $f->send_button('Button', 'Eintragen');
        $f->ende_formular();
        $f->fieldset_ende();
    }

    function get_eigentumer_id_infos4($e_id)
    {
        $this->eigentuemer_id = $e_id;
        $this->et_code = $e_id;
        if ($this->et_code < 1000) {
            $this->et_code = substr($this->et_code, 1) . $this->et_code . '1';
        }
        if (strlen($this->et_code) > 4) {
            $abs = strlen($this->et_code) - 4;
            $this->et_code = substr($this->et_code, $abs);
        }
        if (isset ($this->GLAEUBIGER_ID)) {
            unset ($this->GLAEUBIGER_ID);
        }

        $einheit_id = $this->get_einheit_id_from_eigentuemer($e_id);
        $this->einheit_id = $einheit_id;
        $e = new einheit ();
        $e->get_einheit_info($einheit_id);
        // print_r($e);
        $this->einheit_kurzname = $e->einheit_kurzname;
        $this->einheit_lage = $e->einheit_lage;
        $this->einheit_qm = $e->einheit_qm;
        $this->einheit_qm_d = $e->einheit_qm_d;
        $det = new detail ();

        $versprochene_miete = $det->finde_detail_inhalt('Einheit', $einheit_id, 'WEG-KaltmieteINS');
        if ($versprochene_miete) {
            $this->versprochene_miete = nummer_komma2punkt($versprochene_miete);
        }

        $this->einheit_qm_weg_d = $det->finde_detail_inhalt('Einheit', $einheit_id, 'WEG-Fläche'); // kommt als Kommazahl
        if ($this->einheit_qm_weg_d) {
            $this->einheit_qm_weg = nummer_komma2punkt($this->einheit_qm_weg_d);
        } else {
            $this->einheit_qm_weg = $this->einheit_qm;
            $this->einheit_qm_weg_d = $this->einheit_qm_d;
        }

        $this->haus_strasse = $e->haus_strasse;
        $this->haus_nummer = $e->haus_nummer;
        $this->haus_plz = $e->haus_plz;
        $this->haus_stadt = $e->haus_stadt;
        $this->einheit_kurzname = $e->einheit_kurzname;
        $this->objekt_id = $e->objekt_id;
        $this->post_anschrift_haus = "$this->haus_strasse $this->haus_nummer\n<b>$this->haus_plz $this->haus_stadt</b>";
        $this->personen_id_arr = $this->get_person_id_eigentuemer_arr($e_id);
        $this->anz_personen = count($this->personen_id_arr);
        for ($a = 0; $a < $this->anz_personen; $a++) {
            $person_id = $this->personen_id_arr [$a] ['PERSON_ID'];
            $p = new personen ();
            $p->get_person_infos($person_id);
            $this->personen_id_arr [$a] ['geschlecht'] = $p->geschlecht;
            if ($p->geschlecht == 'weiblich') {
                if ($this->anz_personen > 1) {
                    $this->personen_id_arr [$a] ['anrede_p'] = "Frau $p->person_vorname $p->person_nachname";
                    $this->personen_id_arr [$a] ['anrede_t'] = "Sehr geehrte Frau $p->person_nachname,";
                } else {
                    $this->personen_id_arr [$a] ['anrede_p'] = "Frau\n$p->person_vorname $p->person_nachname";
                    $this->personen_id_arr [$a] ['anrede_t'] = "Sehr geehrte Frau $p->person_nachname,";
                }
            }
            if ($p->geschlecht == 'männlich') {
                if ($this->anz_personen > 1) {
                    $this->personen_id_arr [$a] ['anrede_p'] = "Herr $p->person_vorname $p->person_nachname";
                    $this->personen_id_arr [$a] ['anrede_t'] = "Sehr geehrter Herr $p->person_nachname,";
                } else {
                    $this->personen_id_arr [$a] ['anrede_p'] = "Herr\n$p->person_vorname $p->person_nachname";
                    $this->personen_id_arr [$a] ['anrede_t'] = "Sehr geehrter Herr $p->person_nachname,";
                }
            }
            if (empty ($p->geschlecht)) {

                $this->personen_id_arr [$a] ['anrede_p'] = "$p->person_vorname $p->person_nachname";
                $this->personen_id_arr [$a] ['anrede_t'] = "geehrte Damen und Herren,";
            }

            if (isset ($p->anschrift)) {
                $this->anschriften [] = $p->anschrift;
                $this->anschriften_p [$person_id] = br2n($p->anschrift);
            }
            if (isset ($p->zustellanschrift)) {
                $this->zustellanschriften [] = br2n($p->zustellanschrift);
                $this->zustellanschriften_p [$person_id] = $p->zustellanschrift;
            }
        }
        /* Sortieren nach Geschlecht */
        $this->personen_id_arr1 = array_sortByIndex($this->personen_id_arr, 'geschlecht', SORT_DESC);
        unset ($this->personen_id_arr);
        $this->anrede_brief = '';

        /* Anredetext */
        for ($a = 0; $a < $this->anz_personen; $a++) {
            if ($a < $this->anz_personen - 1) {
                $this->anrede_brief .= $this->personen_id_arr1 [$a] ['anrede_t'] . "\n";
                $this->empf_namen_u .= $this->personen_id_arr1 [$a] ['anrede_p'] . "\n";
            } else {
                /* Kleinbuchstaben zweite Zeile sehr... */
                if ($this->anz_personen > 1) {
                    $this->anrede_brief .= lcfirst($this->personen_id_arr1 [$a] ['anrede_t'] . "\n");
                    $this->empf_namen_u .= $this->personen_id_arr1 [$a] ['anrede_p'] . "";
                } else {
                    $this->anrede_brief .= $this->personen_id_arr1 [$a] ['anrede_t'] . "\n";
                    $this->empf_namen_u .= $this->personen_id_arr1 [$a] ['anrede_p'] . "";
                }
            }
        }
        /* Anschriften zählen */
        if (isset ($this->zustellanschriften)) {
            $this->anz_zustell = count($this->zustellanschriften);
        }

        if (isset ($this->anschriften)) {
            $this->anz_anschrift = count($this->anschriften);
        }

        /* Postanschrift kreiren */
        if (!isset ($this->anz_anschrift) && !isset ($this->anz_zustell)) {
            $this->post_anschrift = "$this->empf_namen_u\n$this->post_anschrift_haus";
        }

        if (isset ($this->anz_anschrift) && !isset ($this->anz_zustell)) {
            $this->post_anschrift = br2n($this->anschriften [0]);
        }

        if (!isset ($this->anz_anschrift) && isset ($this->anz_zustell)) {
            $this->post_anschrift = br2n($this->zustellanschriften [0]);
        }

        if (isset ($this->anz_anschrift) && isset ($this->anz_zustell)) {
            $this->post_anschrift = br2n($this->zustellanschriften [0]);
        }

        $this->empf_namen = bereinige_string($this->empf_namen_u);
    }

    function get_einheit_id_from_eigentuemer($e_id)
    {
        $result = DB::select("SELECT EINHEIT_ID, VON, BIS FROM WEG_MITEIGENTUEMER WHERE ID='$e_id' && AKTUELL='1' ORDER BY DAT DESC LIMIT 0,1");
        if (!empty($result)) {
            $row = $result[0];
            $this->eigentuemer_von = $row ['VON'];
            $this->eigentuemer_bis = $row ['BIS'];
            return $row ['EINHEIT_ID'];
        }
    }

    function ausgewahlte_et_liste_aendern($label, $name, $id, $javaaction, $size, $et_arr)
    {
        $person_info = new person ();
        echo "<div class='input-field'>";
        echo "<select name=\"$name\" id=\"$id\" $javaaction size=\"$size\" style='visibility:visible;' multiple>";
        if (is_array($et_arr)) {
            for ($a = 0; $a < count($et_arr); $a++) {
                $person_id = $et_arr [$a] ['PERSON_ID'];
                $person_info->get_person_infos($person_id);
                echo "<option value=\"$person_id\">$person_info->person_nachname $person_info->person_vorname</option>";
            }
        }
        echo "</select><label for=\"$id\">$label</label>";
        echo "</div>";
    }

    function eigentuemer_aendern_db($et_id, $einheit_id, $eigent_arr, $eigentuemer_von, $eigentuemer_bis)
    {
        /* ET_ID INAKTIV */
        $db_abfrage = "UPDATE WEG_MITEIGENTUEMER SET AKTUELL='0' where AKTUELL='1' && ID='$et_id'";
        DB::update($db_abfrage);

        /* PERSONEN von ET_ID INAKTIV */
        $db_abfrage = "UPDATE WEG_EIGENTUEMER_PERSON SET AKTUELL='0' where AKTUELL='1' && WEG_EIG_ID='$et_id'";
        DB::update($db_abfrage);

        $this->eigentuemer_speichern_mit_id($et_id, $einheit_id, $eigent_arr, $eigentuemer_von, $eigentuemer_bis);
    }

    function eigentuemer_speichern_mit_id($et_id, $einheit_id, $eigent_arr, $eigentuemer_von, $eigentuemer_bis)
    {
        if (!is_array($eigent_arr)) {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\ErrorMessage('Eigentümerauswahl nicht vollständig.')
            );
        }

        $eigentuemer_von = date_german2mysql($eigentuemer_von);
        $eigentuemer_bis = date_german2mysql($eigentuemer_bis);

        /* Neue Eigentümer eintragen */
        $id = $et_id;
        $db_abfrage = "INSERT INTO WEG_MITEIGENTUEMER VALUES (NULL, '$id', '$einheit_id', '$eigentuemer_von', '$eigentuemer_bis', '1')";
        DB::insert($db_abfrage);

        /* Zugewiesene MIETBUCHUNG_DAT auslesen */
        $last_dat = DB::getPdo()->lastInsertId();
        protokollieren('WEG_MITEIGENTUEMER', '0', $last_dat);

        /* Personen zu ID eintragen */
        $anz = count($eigent_arr);
        for ($a = 0; $a < $anz; $a++) {
            $p_id = last_id2('WEG_EIGENTUEMER_PERSON', 'ID') + 1;
            $person_id = $eigent_arr [$a];
            $db_abfrage = "INSERT INTO WEG_EIGENTUEMER_PERSON VALUES (NULL, '$p_id', '$id', '$person_id', '1')";
            DB::insert($db_abfrage);
            /* Zugewiesene MIETBUCHUNG_DAT auslesen */
            $last_dat = DB::getPdo()->lastInsertId();
            protokollieren('WEG_EIGENTUEMER_PERSON', '0', $last_dat);
        } // end for
    }

    function get_eigentuemer_namen_str($e_id)
    {
        $person_string = '';
        $person_string_u = '';
        $personen_id_arr = $this->get_person_id_eigentuemer_arr($e_id);
        if (empty($personen_id_arr)) {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\ErrorMessage("Eigentümer (ID: $e_id) unbekannt")
            );
        } else {
            $anz = count($personen_id_arr);
            if ($anz) {
                for ($a = 0; $a < $anz; $a++) {
                    $person_id = $personen_id_arr [$a] ['PERSON_ID'];
                    $this->eigentuemer_person_ids [] = $person_id;
                    $p = new personen ();
                    $p->get_person_infos($person_id);

                    $person_string .= "$p->person_nachname, $p->person_vorname<br>";
                    $person_string_u .= "$p->person_nachname, $p->person_vorname<br>";
                }
                $this->eigentuemer_name_str = $person_string;
                $this->eigentuemer_name_str_u = $person_string_u;
            }
        }
    }

    function get_eigentuemer_id_from_person_arr($person_id)
    {
        $result = DB::select("SELECT WEG_EIG_ID FROM WEG_EIGENTUEMER_PERSON WHERE PERSON_ID='$person_id' && AKTUELL='1'");
        return $result;
    }

    function liste_weg_objekte()
    {
        session()->put('url.intended', URL::previous());
        $arr = $this->weg_objekte_arr();
        if (is_array($arr)) {
            echo "<div class='row'>";
            $anz = count($arr);
            for ($a = 0; $a < $anz; $a++) {
                $objekt_id = $arr [$a];
                $o = new objekt ();
                $o->get_objekt_infos($objekt_id);
                echo "<div class='col-xs-12 col-md-6 col-lg-3'>";
                echo "<a href='" . route('web::objekte::select', ['id' => $objekt_id]) . "'>$o->objekt_kurzname</a>";
                echo "</div>";
            }
            echo "</div>";
        }
    }

    function weg_objekte_arr()
    {
        $result = DB::select("SELECT HAUS_ID FROM EINHEIT WHERE TYP LIKE '%eigentum%' && EINHEIT_AKTUELL='1' GROUP BY HAUS_ID");
        if (!empty($result)) {
            foreach ($result as $row) {
                $haus_id_arr [] = $row ['HAUS_ID'];
            }

            $haus_id_arr_uni = array_values(array_unique($haus_id_arr));
            $anz = count($haus_id_arr_uni);
            if ($anz > 0) {
                $h = new haus ();
                for ($a = 0; $a < $anz; $a++) {
                    $haus_id = $haus_id_arr_uni [$a];
                    $h->get_haus_info($haus_id);
                    $objekt_arr [] = $h->objekt_id;
                }
                $objekt_id_arr_uni = array_values(array_unique($objekt_arr));
                return $objekt_id_arr_uni;
            }
        }
    }

    function einheiten_weg_tabelle_anzeigen($objekt_id)
    {
        $arr = $this->einheiten_weg_tabelle_arr($objekt_id);
        if (!empty($arr)) {
            $o = new objekt ();
            $o->get_objekt_infos($objekt_id);
            $qm_g = nummer_punkt2komma($o->get_qm_gesamt($objekt_id));
            echo "<table class='striped'>";
            echo "<tr><th>OBJEKT</th><th>WEG</th><th></th><th>QM</th><th></th><th></th><th>GESAMTANTEILE</th><th></th><th></th><th></th></tr>";
            $d = new detail ();
            $weg_bez = $d->finde_detail_inhalt('Objekt', $objekt_id, 'WEG-Bezeichnung');
            $anteile_g = $d->finde_detail_inhalt('Objekt', $objekt_id, 'Gesamtanteile');
            echo "<tr><td><b>$o->objekt_kurzname</b></td><td>$weg_bez</td><td></td><td>$qm_g m²</td><td></td><td>$anteile_g</td><td></td><td></td><td></td></tr>";
            echo "<tr><th>EINHEIT</th><th>EIGENTÜMER</th><th>STRASSE, NR, PLZ ORT</th><th>QM</th><th>QM ET</th><th>LAGE</th><th>ANTEILE</th><th>OPTIONEN</th><th>VOREIGENTÜMER</th><th>ET-OPTION</th></tr>";
            $g_qm = 0;
            $g_ant = 0;
            $anz = count($arr);
            for ($a = 0; $a < $anz; $a++) {
                $einheit_id = $arr [$a] ['EINHEIT_ID'];
                $e = new einheit ();
                $d = new detail ();
                $e->get_einheit_info($einheit_id);
                $u_link = "<a href='" . route('web::weg::legacy', ['option' => 'einheit_uebersicht', 'einheit_id' => $einheit_id]) . "'>$e->einheit_kurzname</a>";
                $this->get_last_eigentuemer_namen($einheit_id);
                $this->weg_anteile = $d->finde_detail_inhalt('Einheit', $einheit_id, 'WEG-Anteile');
                $e->einheit_qm_a = nummer_punkt2komma($e->einheit_qm);
                $g_qm += $e->einheit_qm;
                $et_qm = $d->finde_detail_inhalt('Einheit', $einheit_id, 'WEG-Fläche');
                $def_link = "<a href='" . route('web::weg::legacy', ['option' => 'wohngeld_definieren', 'einheit_id' => $einheit_id]) . "'>Wohngeld bestimmen</a>";
                $hg_auszug_link = "<a href='" . route('web::weg::legacy', ['option' => 'hausgeld_kontoauszug', 'eigentuemer_id' => $this->eigentuemer_id]) . "'>Hausgeld Kontoauszug</a>";
                $hg_auszug_link1 = "<a href='" . route('web::weg::legacy', ['option' => 'hg_kontoauszug', 'eigentuemer_id' => $this->eigentuemer_id, 'jahr' => date('Y')]) . "'><img src=\"images/pdf_light.png\"></a>";
                $hg_auszug_link2 = "<a href='" . route('web::weg::legacy', ['option' => 'hg_kontoauszug', 'eigentuemer_id' => $this->eigentuemer_id, 'jahr' => date('Y'), 'no_logo']) . "'><img src=\"images/pdf_dark.png\"></a>";

                echo "<tr><td>$u_link</td><td>$this->eigentuemer_namen</td><td>$e->haus_strasse $e->haus_nummer, $e->haus_plz $e->haus_stadt</td><td>$e->einheit_qm_a m²</td><td>$et_qm</td><td>$e->einheit_lage</td><td>$this->weg_anteile</td><td>$def_link<hr>$hg_auszug_link<br>$hg_auszug_link1 $hg_auszug_link2</td>";
                echo "<td>";
                $arr_e = $this->get_eigentuemer_arr($einheit_id);

                $anz_e = count($arr_e);
                if (!empty($arr_e)) {
                    for ($e = 0; $e < $anz_e; $e++) {
                        $et_nr = $e + 1;
                        $v_id = $arr_e [$e] ['ID'];
                        $v_von = date_mysql2german($arr_e [$e] ['VON']);
                        $v_bis = date_mysql2german($arr_e [$e] ['BIS']);
                        $hg_auszug_link_ve = "<a href='" . route('web::weg::legacy', ['option' => 'hg_kontoauszug', 'eigentuemer_id' => $v_id, 'jahr' => date('Y')]) . "'>$et_nr. <img src=\"images/pdf_dark.png\"></a>";
                        echo "$hg_auszug_link_ve $v_von - $v_bis<br>";
                    }
                }
                echo "</td><td>";

                $anz_e = count($arr_e);
                if (!empty($arr_e)) {
                    for ($e = 0; $e < $anz_e; $e++) {
                        $v_id = $arr_e [$e] ['ID'];
                        $et_nr = $e + 1;
                        $et_aendern = "<a href='" . route('web::weg::legacy', ['option' => 'eigentuemer_aendern', 'eigentuemer_id' => $v_id]) . "'>$et_nr. ET-Daten ändern</a>";
                        echo "$et_aendern<br>";
                    }
                }

                echo "</td></tr>";
                // $g_qm += $e->einheit_qm;
                $g_ant += nummer_komma2punkt($this->weg_anteile);
                unset ($this->eigentuemer_namen);
            }
            $g_ant_a = nummer_punkt2komma_t($g_ant);
            $g_qm_a = nummer_punkt2komma_t($g_qm);
            echo "<tfoot><tr><th></th><th></th><th></th><th>$g_qm_a m²</th><th></th><th></th><th>$g_ant_a</th><th></th></tr></tfoot>";
            echo "</table>";
        }
    }

    function get_last_eigentuemer_namen($einheit_id)
    {
        $this->eigentuemer_anzahl = 0;
        $this->eigentuemer_namen = '';
        $this->eigentuemer_namen2 = '';
        if (isset ($this->eigentuemer_name)) {
            unset ($this->eigentuemer_name);
        }
        $this->get_last_eigentuemer($einheit_id);
        if (isset ($this->eigentuemer_name) && $this->eigentuemer_name != '') {
            $anz = count($this->eigentuemer_name);
            $this->eigentuemer_anzahl = $anz;
        } else {
            $this->eigentuemer_name = 'Kein Eigentümer!';
        }
        if (isset ($anz)) {

            for ($a = 0; $a < $anz; $a++) {
                $nachname = $this->eigentuemer_name [$a] ['Nachname'];
                $vorname = $this->eigentuemer_name [$a] ['Vorname'];
                $eig_zahl = $a + 1;
                $this->eigentuemer_namen .= "$eig_zahl. $nachname $vorname<br>";
                $this->eigentuemer_namen2 .= "$nachname $vorname ";
            }
        } else {
            $this->eigentuemer_name = 'Kein Eigentümer!';
        }
    }

    function get_eigentuemer_arr($einheit_id)
    {
        $result = DB::select("SELECT * FROM WEG_MITEIGENTUEMER WHERE EINHEIT_ID='$einheit_id' && AKTUELL='1' ORDER BY VON ASC");
        return $result;
    }

    function wp_liste($objekt_id)
    {
        $arr = $this->get_wps_arr($objekt_id);
        $o = new objekt ();
        $obj_name = $o->get_objekt_name($objekt_id);

        if (!empty($arr)) {
            echo "<table>";
            echo "<tr><th>Wirtschaftsjahr</th><th>OBJEKT</th><th>OPTIONEN</th></tr>";
            $anz = count($arr);
            for ($a = 0; $a < $anz; $a++) {
                $plan_id = $arr [$a] ['PLAN_ID'];
                $jahr = $arr [$a] ['JAHR'];
                $link_zeile_hinzu = "<a href='" . route('web::weg::legacy', ['option' => 'wp_zeile_neu', 'wp_id' => $plan_id]) . "'>Bearbeiten</a>";
                $link_pdf = "<a href='" . route('web::weg::legacy', ['option' => 'wplan_pdf', 'wp_id' => $plan_id]) . "'><img src=\"images/pdf_light.png\"></a>";
                echo "<tr><td>$jahr</td><td>$obj_name</td><td>$link_zeile_hinzu $link_pdf</td></tr>";
            }
            echo "</table>\n";
        } else {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\WarningMessage("Keine Wirtschaftspläne für das Objekt $obj_name.")
            );
        }
    }

    function get_wps_arr($objekt_id)
    {
        $result = DB::select("SELECT * FROM WEG_WPLAN WHERE AKTUELL='1' && OBJEKT_ID='$objekt_id' ORDER BY JAHR DESC");
        return $result;
    }

    function eigentuemer_speichern($einheit_id, $eigent_arr, $eigentuemer_von, $eigentuemer_bis)
    {
        if (!is_array($eigent_arr)) {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\ErrorMessage('Eigentümerauswahl nicht vollständig.')
            );
        }

        $eigentuemer_von = date_german2mysql($eigentuemer_von);
        $eigentuemer_bis = date_german2mysql($eigentuemer_bis);
        /* Letzten Eigentümer Ende auf Anfang -1 Tag setzen, falls überhaupt vorhanden */
        $alt_id = $this->check_miteigentuemer($einheit_id);
        if (isset ($alt_id) && !empty ($alt_id)) {
            $o = new objekt ();
            $akt_eigentuemer_bis = $o->datum_minus_tage($eigentuemer_von, 1);
            $db_abfrage = "UPDATE WEG_MITEIGENTUEMER SET BIS='$akt_eigentuemer_bis' where AKTUELL='1' && ID='$alt_id'";
            DB::update($db_abfrage);
        }

        /* Neue Eigentümer eintragen */
        $id = last_id2('WEG_MITEIGENTUEMER', 'ID') + 1;
        $db_abfrage = "INSERT INTO WEG_MITEIGENTUEMER VALUES (NULL, '$id', '$einheit_id', '$eigentuemer_von', '$eigentuemer_bis', '1')";
        DB::insert($db_abfrage);
        /* Zugewiesene MIETBUCHUNG_DAT auslesen */
        $last_dat = DB::getPdo()->lastInsertId();
        protokollieren('WEG_MITEIGENTUEMER', '0', $last_dat);

        /* Personen zu ID eintragen */
        $anz = count($eigent_arr);
        for ($a = 0; $a < $anz; $a++) {
            $p_id = last_id2('WEG_EIGENTUEMER_PERSON', 'ID') + 1;
            $person_id = $eigent_arr [$a];
            $db_abfrage = "INSERT INTO WEG_EIGENTUEMER_PERSON VALUES (NULL, '$p_id', '$id', '$person_id', '1')";
            DB::insert($db_abfrage);
            /* Zugewiesene MIETBUCHUNG_DAT auslesen */
            $last_dat = DB::getPdo()->lastInsertId();
            protokollieren('WEG_EIGENTUEMER_PERSON', '0', $last_dat);
        } // end for
    }

    function check_miteigentuemer($einheit_id)
    {
        $result = DB::select("SELECT ID FROM WEG_MITEIGENTUEMER WHERE EINHEIT_ID='$einheit_id' && AKTUELL='1' ORDER BY VON DESC LIMIT 0,1");
        if (!empty($result)) {
            $row = $result[0];
            return $row ['ID'];
        }
    }

    function eigentuemer_neu($einheit_id, $von, $bis)
    {
        /* Neue Eigentümer eintragen */
        $id = last_id2('WEG_MITEIGENTUEMER', 'ID') + 1;
        $db_abfrage = "INSERT INTO WEG_MITEIGENTUEMER VALUES (NULL, '$id', '$einheit_id', '$von', '$bis', '1')";
        DB::insert($db_abfrage);
        /* Zugewiesene MIETBUCHUNG_DAT auslesen */
        $last_dat = DB::getPdo()->lastInsertId();
        protokollieren('WEG_MITEIGENTUEMER', '0', $last_dat);
        return $id;
    }

    function person_zu_et($et_id, $person_id)
    {
        /* Personen zu ID eintragen */
        $p_id = last_id2('WEG_EIGENTUEMER_PERSON', 'ID') + 1;
        $db_abfrage = "INSERT INTO WEG_EIGENTUEMER_PERSON VALUES (NULL, '$p_id', '$et_id', '$person_id', '1')";
        DB::insert($db_abfrage);
        $last_dat = DB::getPdo()->lastInsertId();
        protokollieren('WEG_EIGENTUEMER_PERSON', '0', $last_dat);
    }

    function form_wg_einheiten($monat, $jahr, $objekt_id)
    {
        $arr = $this->einheiten_weg_tabelle_arr($objekt_id);
        $anz = count($arr);
        if ($anz > 0) {
            echo "<table class=\"sortable striped\">";
            echo "<tr><th>Einheit</th><th>Eigentümer</th><th>Optionen</th><th>Hausgeld soll</th><th>Saldo</th></tr>";
            for ($a = 0; $a < $anz; $a++) {
                $einheit_id = $arr [$a] ['EINHEIT_ID'];
                $e = new einheit ();
                $e->get_einheit_info($einheit_id);
                $u_link = "<a href='" . route('web::weg::legacy', ['option' => 'einheit_uebersicht', 'einheit_id' => $einheit_id]) . "'>$e->einheit_kurzname</a>";
                $this->get_last_eigentuemer_namen($einheit_id);
                $this->get_wg_info($monat, $jahr, 'Einheit', $einheit_id, 'Hausgeld');
                $this->Wohngeld_soll_a = nummer_punkt2komma($this->gruppe_erg);
                $b_link = "<a href='" . route('web::weg::legacy', ['option' => 'wohngeld_buchen_maske', 'einheit_id' => $einheit_id]) . "'>Hausgeld buchen</a>";

                $this->hausgeld_kontoauszug_stand($this->eigentuemer_id);
                echo "<tr><td>$u_link</td><td>$this->eigentuemer_namen</td><td>$b_link</td><td>$this->Wohngeld_soll_a</td><td>$this->hg_erg_a €</td></tr>";
            }
            echo "</table";
        }
    }

    function hausgeld_kontoauszug_stand($eigentuemer_id)
    {
        $this->saldo_jahr = 0;
        $this->zb_bisher = 0;
        $this->hg_erg = 0;
        $this->hg_erg_a = 0;
        $this->Wohngeld_soll = 0;
        $this->Wohngeld_soll_g = 0;

        if (!$eigentuemer_id) {
            $this->hg_erg = 0;
            $this->hg_erg_a = 0;
            return 0;
        }

        $this->hg_saldo = '0.00';
        $this->eigentuemer_von_a = date_mysql2german($this->eigentuemer_von);

        $datum_arr = explode('-', $this->eigentuemer_von);
        $j = $datum_arr [0]; // Jahr
        $m = $datum_arr [1]; // Monat
        $t = $datum_arr [2]; // Tag
        if (!request()->has('jahr') || request()->input('jahr') == date("Y")) {
            $akt_jahr = date("Y");
            $akt_monat = date("m");
        } else {
            $akt_jahr = request()->input('jahr');
            $akt_monat = 12;
        }

        $akt_datum = date("Y-m-d");
        $datum_1_def = $this->datum_erste_hg_def('Einheit', $this->einheit_id);
        $datum_1_def_arr = explode('-', $datum_1_def);
        $dat2 = $datum_1_def_arr [0] . $datum_1_def_arr [2] . $datum_1_def_arr [1];
        $dat1 = $j . $t . $m;
        if ($dat1 >= $dat2) {
            $datum_ab = "$j-$m-$t";
        } else {
            $datum_ab = $datum_1_def_arr [0] . '-' . $datum_1_def_arr [1] . '-' . $datum_1_def_arr [2];
        }

        $mi = new miete ();

        $datum_ab_arr = explode('-', $datum_ab);
        $j = $datum_ab_arr [0];

        for ($a = 1; $a <= $akt_monat; $a++) {
            $m = sprintf('%02d', $a);
            // echo "$a. $m.$j<br>";
            $soll_array [$a - 1] ['monat'] = $m;
            $soll_array [$a - 1] ['jahr'] = $akt_jahr;

            if ($m == 12) {
                $m = 0;
                $j++;
            }
        }

        /* überschriften der Tabelle dynamisch */
        $moegliche_def_arr = $this->get_moegliche_def('Einheit', $this->einheit_id, $akt_jahr);
        $anz = count($moegliche_def_arr);
        for ($k = 0; $k < $anz; $k++) {
            $kostenkat = $moegliche_def_arr [$k] ['KOSTENKAT'];
            if (strlen($kostenkat) > 14) {
                $kostenkat_t1 = substr($kostenkat, 0, 14);
                $kostenkat_t2 = substr($kostenkat, 14, strlen($kostenkat));
            }
        }

        /* Monatsschleife */
        $anz_monate = count($soll_array);
        for ($a = 0; $a < $anz_monate; $a++) {
            $monat = $soll_array [$a] ['monat'];
            $jahr = $soll_array [$a] ['jahr'];
            $this->get_wg_info($monat, $jahr, 'Einheit', $this->einheit_id, 'Hausgeld');
            $this->Wohngeld_soll = $this->gruppe_erg;
            $this->Wohngeld_soll_g += $this->gruppe_erg;
            $this->Wohngeld_soll_a = nummer_punkt2komma($this->gruppe_erg);

            $gg = new geldkonto_info ();
            $gg->geld_konto_ermitteln('Objekt', session()->get('objekt_id'), $jahr . '-' . $monat . '-01', 'Hausgeld');
            $geldkonto_id = $gg->geldkonto_id;

            $summe_zahlungen_6 = $this->get_summe_zahlungen('Eigentuemer', $eigentuemer_id, $monat, $jahr, $geldkonto_id, 6000);
            $summe_zahlungen_hz = $this->get_summe_zahlungen('Eigentuemer', $eigentuemer_id, $monat, $jahr, $geldkonto_id, 6010);
            $summe_zahlungen_hg = $this->get_summe_zahlungen('Eigentuemer', $eigentuemer_id, $monat, $jahr, $geldkonto_id, 6020);
            $summe_zahlungen_ihr = $this->get_summe_zahlungen('Eigentuemer', $eigentuemer_id, $monat, $jahr, $geldkonto_id, 6030);
            $summe_zahlungen = $summe_zahlungen_hz + $summe_zahlungen_hg + $summe_zahlungen_ihr + $summe_zahlungen_6;
            $this->zb_bisher += $summe_zahlungen;

            if ($summe_zahlungen > 0) {
                // $this->hg_saldo += $summe_zahlungen;
                // $this->hg_saldo_a = nummer_punkt2komma($this->hg_saldo);
            } else {
                $this->hg_saldo += 0.00;
            }

            // $this->hg_saldo_a = nummer_punkt2komma($this->hg_saldo);
        } // ende Monatsschleife
        $this->saldo_jahr = $this->zb_bisher - $this->Wohngeld_soll_g;
        $this->saldo_jahr_a = nummer_punkt2komma($this->saldo_jahr);
        $this->hg_erg = $this->saldo_jahr;
        $this->hg_erg_a = $this->saldo_jahr_a;

        $this->saldo_jahr = $this->zb_bisher - $this->Wohngeld_soll_g;
        return $this->saldo_jahr;
    }

    function datum_erste_hg_def($kos_typ, $kos_id)
    {
        $result = DB::select("SELECT ANFANG FROM WEG_WG_DEF WHERE AKTUELL='1' &&  KOS_TYP LIKE '$kos_typ' && KOS_ID='$kos_id' ORDER BY ANFANG ASC LIMIT 0,1");
        if (!empty($result)) {
            $row = $result[0];
            return $row ['ANFANG'];
        }
    }

    function get_moegliche_def($kos_typ, $kos_id, $jahr)
    {
        $result = DB::select("SELECT E_KONTO, KOSTENKAT FROM WEG_WG_DEF WHERE KOS_TYP LIKE '$kos_typ' && KOS_ID='$kos_id' && E_KONTO != 6050 && AKTUELL='1' && YEAR(ANFANG)<=$jahr && (YEAR(ENDE)='0000' || YEAR(ENDE)>=$jahr) GROUP BY KOSTENKAT ORDER BY E_KONTO ASC");
        if (!empty($result)) {
            return $result;
        }
    }

    function get_summe_zahlungen($kos_typ, $kos_id, $monat, $jahr, $geldkonto_id, $kostenkonto = '6020')
    {
        $result = DB::select("SELECT SUM(BETRAG) AS SUMME FROM GELD_KONTO_BUCHUNGEN WHERE DATE_FORMAT(DATUM, '%Y-%m') = '$jahr-$monat' && KOSTENTRAEGER_TYP LIKE '$kos_typ' && KOSTENTRAEGER_ID='$kos_id' && AKTUELL='1' && GELDKONTO_ID='$geldkonto_id' && `KONTENRAHMEN_KONTO` = '$kostenkonto' GROUP BY KOSTENTRAEGER_ID ORDER BY DATUM ASC");
        if (!empty($result)) {
            $row = $result[0];
            return $row ['SUMME'];
        }
    }

    function form_wohngeld_buchen($monat, $jahr, $einheit_id)
    {
        $monatsname = monat2name($monat);
        $f = new formular ();
        $f->fieldset("Geldkontoinfos", 'kontrol');
        $f->erstelle_formular('Buchen', '');
        $e = new einheit ();
        $bg = new berlussimo_global ();
        $bg->monate_jahres_links($jahr, route('web::weg::legacy', ['option' => 'wohngeld_buchen_maske', 'einheit_id' => $einheit_id], false));
        $e->get_einheit_info($einheit_id);
        $this->get_last_eigentuemer($einheit_id);
        $this->get_last_eigentuemer_namen($einheit_id);

        $g = new geldkonto_info ();
        $kontostand_aktuell = nummer_punkt2komma($g->geld_konto_stand(session()->get('geldkonto_id')));

        if (session()->has('temp_kontostand')) {
            $kontostand_temp = nummer_punkt2komma(session()->get('temp_kontostand'));
            echo "<h3>Kontostand am " . session()->get('temp_datum') . " laut Kontoauszug " . session()->get('temp_kontoauszugsnummer') . " war $kontostand_temp €</h3>";
        }

        if ($kontostand_aktuell == $kontostand_temp) {
            echo "<h3>Kontostand aktuell: $kontostand_aktuell €</h3>";
        } else {
            echo "<h3 style=\"color:red\">Kontostand aktuell: $kontostand_aktuell €</h3>";
        }
        $f->fieldset_ende();

        $g = new geldkonto_info ();
        $f->fieldset("Wohngeld/Hausgeld buchen für $e->einheit_kurzname", 'ee_id');
        $g->geld_konten_ermitteln('Objekt', "$e->objekt_id");
        $this->eigentuemer_namen_a = strip_tags($this->eigentuemer_namen);
        $f->text_feld_inaktiv("Eigentuemer aktuell", "eigentuemer_namen", $this->eigentuemer_namen_a, 50, 'eigentuemer_namen');
        $this->dropdown_eigentuemer($einheit_id, 'Eigentümer wählen', 'eigentuemer_id', 'eigentuemer_id');
        $wg_keys = $this->wg_def_in_array('Einheit', $einheit_id, $monat, $jahr);
        if (!empty($wg_keys)) {
            $f->text_feld("Datum", "datum", session()->get('temp_datum'), 10, 'datum', '');
            $f->text_feld("Kontoauszugsnr", "kontoauszugsnr", session()->get('temp_kontoauszugsnummer'), 10, 'kontoauszugsnr', '');
            $anz = count($wg_keys);
            for ($a = 0; $a < $anz; $a++) {
                $kostenkat = $wg_keys [$a] ['KOSTENKAT'];
                $this->get_kostenkat_info($monat, $jahr, 'Einheit', $einheit_id, $kostenkat);

                $this->kostenkat_erg_a = nummer_punkt2komma($this->kostenkat_erg);
                $f->text_feld(" $this->e_konto | $kostenkat ", "def_array[$this->e_konto]", $this->kostenkat_erg_a, 10, $kostenkat, '');
                $f->hidden_feld('text_array[]', "$kostenkat");
                // $f->text_feld(" $this->e_konto | $kostenkat ", "text_array[$kostenkat]", $this->kostenkat_erg_a, 10, $kostenkat,'');
            }
            $this->get_wg_info($monat, $jahr, 'Einheit', $einheit_id, 'Hausgeld');
            if ($this->gruppe_erg < 0.00) {
                $this->gruppe_erg = substr("$this->gruppe_erg", 1);
            }

            $this->Wohngeld_soll_a = nummer_punkt2komma($this->gruppe_erg);

            $f->text_feld("$this->g_konto | Hausgeld gesamt ", "wohngeld", $this->Wohngeld_soll_a, 10, 'wohngeld', '');
            $f->hidden_feld('g_konto', $this->g_konto);
            $b_text_wert = "Hausgeldeingang $monatsname $jahr";
            $f->text_bereich("Buchungstext", "b_text", $b_text_wert, 10, 10, 'b_text');
            $f->send_button('sb', 'Buchen');
            $f->hidden_feld('option', 'wg_buchen_send');
        } else {
            echo "Die Höhe des Hausgeldes wurde nicht festgelegt.";
        }

        $f->ende_formular();
        $f->fieldset_ende();
    }

    function dropdown_eigentuemer($einheit_id, $label, $name, $id)
    {
        $arr = $this->get_eigentuemer_arr_2($einheit_id);
        if (!empty($arr)) {
            $anz = count($arr);
            echo "<label for=\"$id\">$label</label><select name=\"$name\" id=\"$id\">";
            for ($a = 0; $a < $anz; $a++) {
                $e_id = $arr [$a] ['ID'];
                $this->get_eigentuemer_namen($e_id);
                echo "<option value=\"$e_id\">" . substr($this->eigentuemer_name_str, 0, -2) . "</option>";
            }
            echo "</select>";
        }
    }

    function get_eigentuemer_arr_2($einheit_id, $sortvon = 'DESC')
    {
        $result = DB::select("SELECT * FROM WEG_MITEIGENTUEMER WHERE EINHEIT_ID='$einheit_id' && AKTUELL='1' ORDER BY VON $sortvon");
        return $result;
    }

    function get_eigentuemer_namen($e_id)
    {
        $person_string = '';
        $person_string_u = '';
        $personen_id_arr = $this->get_person_id_eigentuemer_arr($e_id);
        if (empty($personen_id_arr)) {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\ErrorMessage("Eigentümer (ID: $e_id) unbekannt.")
            );
        } else {
            $anz = count($personen_id_arr);
            if ($anz) {
                for ($a = 0; $a < $anz; $a++) {
                    $person_id = $personen_id_arr [$a] ['PERSON_ID'];
                    $this->eigentuemer_person_ids [] = $person_id;
                    $p = new personen ();
                    $p->get_person_infos($person_id);

                    if ($a < $anz - 1) {
                        $person_string .= "$p->person_nachname $p->person_vorname, ";
                        $person_string_u .= "$p->person_nachname $p->person_vorname\n";
                    } else {
                        $person_string .= "$p->person_nachname $p->person_vorname";
                        $person_string_u .= "$p->person_nachname $p->person_vorname";
                    }
                }
                $this->eigentuemer_name_str = $person_string;
                $this->eigentuemer_name_str_u = $person_string_u;
            }
        }
    }

    function form_wohngeld_def_edit($dat)
    {
        $this->get_hausgeld_def($dat);
        if (!isset ($this->wg_def_dat)) {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\InfoMessage('Hausgelddefintion existiert nicht.')
            );
        } else {
            $f = new formular ();
            $e = new einheit ();
            $e->get_einheit_info($this->wg_def_kos_id);
            $f->erstelle_formular('Hausgeld', '');
            $f->fieldset('Hausgeld definieren', 'ee_id');
            echo "<table class=\"sortable\">";

            echo "<tr><th>Datum vom</th><th>Datum bis</th><th>BETRAG</th><th>KOSTENART</th><th></th><th>OPTIONEN</th></tr>";
            echo "<tr valign=\"top\"><td>";
            $f->datum_feld('Vom', 'von', $this->wg_def_von_d, 'von');
            echo "</td>";
            echo "<td>";
            $f->datum_feld('Bis', 'bis', $this->wg_def_bis_d, 'bis');
            echo "</td>";
            echo "<td>";
            $f->text_feld('Betrag', 'betrag', $this->wg_def_betrag_a, 10, 'betrag', '');
            echo "</td>";

            echo "<td>";
            // $f->text_feld('Kostenkategorie', 'kostenkat', $this->wg_def_koskat, 30, 'kostenkat', '');
            $this->dropdown_def('Kostenart', 'kostenart', 'kostenart', '');
            echo "</td>";
            echo "<td>";
            $f->send_button('sendb', 'Ändern');
            echo "</td>";
            $f->hidden_feld('option', 'wg_def_edit');
            $f->hidden_feld('dat', "$this->wg_def_dat");
            $f->hidden_feld('id', "$this->wg_def_id");
            $f->hidden_feld('kos_typ', "$this->wg_def_kos_typ");
            $f->hidden_feld('kos_id', "$this->wg_def_kos_id");

            echo "</tr></table>";

            $f->fieldset_ende();
            $f->ende_formular();
        }
    }

    function get_hausgeld_def($dat)
    {
        if (isset ($this->wg_def_dat)) {
            unset ($this->wg_def_dat);
            unset ($this->wg_def_id);
            unset ($this->wg_def_von);
            unset ($this->wg_def_von_d);
            unset ($this->wg_def_bis);
            unset ($this->wg_def_bis_d);
            unset ($this->wg_def_betrag);
            unset ($this->wg_def_betrag_a);
            unset ($this->wg_def_koskat);
            unset ($this->wg_def_e_konto);
            unset ($this->wg_def_gruppen_bez);
            unset ($this->wg_def_g_konto);
            unset ($this->wg_def_kos_typ);
            unset ($this->wg_def_kos_id);
            unset ($this->wg_def_kos_aktuell);
        }

        $result = DB::select("SELECT * FROM WEG_WG_DEF WHERE AKTUELL='1' && DAT='$dat'");
        if (!empty($result)) {
            $row = $result[0];
            $this->wg_def_dat = $row ['DAT'];
            $this->wg_def_id = $row ['ID'];
            $this->wg_def_von = $row ['ANFANG'];
            $this->wg_def_von_d = date_mysql2german($row ['ANFANG']);
            $this->wg_def_bis_d = date_mysql2german($row ['ENDE']);
            $this->wg_def_bis = $row ['ENDE'];
            $this->wg_def_betrag = $row ['BETRAG'];
            $this->wg_def_betrag_a = nummer_punkt2komma($row ['BETRAG']);
            $this->wg_def_koskat = $row ['KOSTENKAT'];
            $this->wg_def_e_konto = $row ['E_KONTO'];
            $this->wg_def_gruppen_bez = $row ['GRUPPE'];
            $this->wg_def_g_konto = $row ['G_KONTO'];
            $this->wg_def_kos_typ = $row ['KOS_TYP'];
            $this->wg_def_kos_id = $row ['KOS_ID'];
            $this->wg_def_kos_aktuell = $row ['AKTUELL'];
        }
    }

    function dropdown_def($label, $name, $id, $js)
    {
        $arr = $this->get_definitionen_arr();
        if (!empty($arr)) {
            echo "<label for=\"$id\">$label</label>";
            echo "<select name=\"$name\" size=\"1\" id=\"$id\" $js>\n";
            echo "<option >Bitte wählen</option>\n";
            $anz = count($arr);
            for ($a = 0; $a < $anz; $a++) {
                $kostenkat = $arr [$a] ['KOSTENKAT'];
                $e_konto = $arr [$a] ['E_KONTO'];
                $g_konto = $arr [$a] ['G_KONTO'];
                $gruppe = $arr [$a] ['GRUPPE'];
                echo "<option value=\"$e_konto|$kostenkat|$gruppe|$g_konto\">$e_konto | $kostenkat | $gruppe | $g_konto</option>\n";
            }
            echo "</select>";
        }
    }

    function get_definitionen_arr()
    {
        $result = DB::select("SELECT * FROM WEG_WG_DEF WHERE AKTUELL='1' GROUP BY KOSTENKAT, E_KONTO ORDER BY E_KONTO, KOSTENKAT ASC");
        return $result;
    }

    function form_wg_definition_neu($einheit_id)
    {
        $this->wohngeld_uebersicht('Einheit', $einheit_id);
        $f = new formular ();
        $e = new einheit ();
        $e->get_einheit_info($einheit_id);
        $f->erstelle_formular('Hausgeld', '');
        $f->fieldset('Hausgeld definieren', 'ee_id');
        echo "<table class=\"sortable\">";
        $link_neu = "<a href='" . route('web::weg::legacy', ['option' => 'wohngeld_definieren', 'einheit_id' => $einheit_id, 'neu']) . "'>Neue Kostenart</a>";

        if ($this->check_def() && !request()->exists('neu')) {
            echo "<tr><th>Datum vom</th><th>Datum bis</th><th>BETRAG</th><th>KOSTENART</th><th></th><th>OPTIONEN</th></tr>";
            echo "<tr valign=\"top\"><td>";
            $f->datum_feld('Vom', 'von', '', 'von');
            echo "</td>";
            echo "<td>";
            $f->datum_feld('Bis', 'bis', '', 'bis');
            echo "</td>";
            echo "<td>";
            $f->text_feld('Betrag', 'betrag', '', 10, 'betrag', '');
            echo "</td>";
            echo "<td>";
            $this->dropdown_def('Kostenart', 'kostenart', 'kostenart', '');
            echo "</td>";
            echo "<td>";
            $f->send_button('sendb', 'Speichern');
            echo "</td>";
            echo "<td>$link_neu</td>";
            $f->hidden_feld('option', 'wg_def_exists');
        } else {
            echo "<tr><th>Datum vom</th><th>BETRAG</th><th>KOSTENART</th><th>BUCHUNGSKONTO</th><th>GRUPPENBEZ.</th><th>GRUPPENKONTO</th><th></th></tr>";
            echo "<tr><td>";
            $f->datum_feld('Vom', 'von', '', 'von');
            echo "</td>";
            echo "<td>";
            $f->datum_feld('Bis', 'bis', '', 'bis');
            echo "</td>";
            echo "<td>";
            $f->text_feld('Betrag', 'betrag', '', 10, 'betrag', '');
            echo "</td>";
            echo "<td>";
            $f->text_feld('Kostenkategorie', 'kostenkat', '', 30, 'kostenkat', '');
            echo "</td><td>";
            $f->text_feld('Buchungskonto', 'e_konto', '', 10, 'e_konto', '');
            echo "</td><td>";
            $f->text_feld('Gruppe', 'gruppe', 'Hausgeld', 10, 'gruppe', '');
            echo "</td><td>";
            $f->text_feld('Gruppenkonto', 'g_konto', '', 10, 'g_konto', '');
            echo "</td>";
            echo "<td>";
            $f->send_button('sendb', 'Speichern');
            echo "</td>";
            $f->hidden_feld('option', 'wg_def_neu');
        }

        echo "</tr></table>";

        $f->fieldset_ende();
        $f->ende_formular();
    }

    function wohngeld_uebersicht($kos_typ, $kos_id)
    {
        $arr = $this->get_definitionen_arr_kos($kos_typ, $kos_id);
        $anz = count($arr);
        if ($anz > 0) {
            $r = new rechnung ();
            $bez = $r->kostentraeger_ermitteln($kos_typ, $kos_id);
            echo "<table>";
            echo "<tr><th colspan=\"8\">$bez</th></tr>";
            echo "<tr><th>VON</th><th>BIS</th><th>BETRAG</th><th>KOSTENKATEGORIE</th><th>BUCHUNGSKONTO</th><th>GRUPPE</th><th>GRUPPENKONTO</th><th>OPTION</th></tr>";
            for ($a = 0; $a < $anz; $a++) {
                $von = date_mysql2german($arr [$a] ['ANFANG']);
                $bis = date_mysql2german($arr [$a] ['ENDE']);
                $dat = $arr [$a] ['DAT'];
                $betrag = $arr [$a] ['BETRAG'];
                $betrag_a = nummer_punkt2komma($betrag);
                $kostenkat = $arr [$a] ['KOSTENKAT'];

                $e_konto = $arr [$a] ['E_KONTO'];
                $g_konto = $arr [$a] ['G_KONTO'];
                $gruppe = $arr [$a] ['GRUPPE'];

                $link_del = "<a href='" . route('web::weg::legacy', ['option' => 'wohngeld_def_del', 'dat' => $dat, 'einheit_id' => $kos_id]) . "'>Löschen</a>";
                $link_aendern = "<a href='" . route('web::weg::legacy', ['option' => 'wohngeld_def_aendern', 'dat' => $dat]) . "'>Ändern</a>";

                echo "<tr><td>$von</td><td>$bis</td><td>$betrag_a</td><td>$kostenkat</td><td>$e_konto</td><td>$gruppe</td><td>$g_konto</td><td>$link_aendern<br>$link_del</td></tr>";
            }
            echo "</table>";
        } else {
            echo "Keine Hausgelddefinition!!!";
        }
    }

    function get_definitionen_arr_kos($kos_typ, $kos_id)
    {
        $result = DB::select("SELECT * FROM WEG_WG_DEF WHERE AKTUELL='1' && KOS_TYP='$kos_typ' && KOS_ID='$kos_id' ORDER BY ANFANG ASC, ENDE DESC");
        if (!empty($result)) {
            return $result;
        }
    }

    function check_def()
    {
        $result = DB::select("SELECT ID FROM WEG_WG_DEF WHERE AKTUELL='1'");
        return !empty($result);
    }

    function wohngeld_def_speichern($von, $bis, $betrag, $kostenkat, $e_konto, $gruppe, $g_konto, $einheit_id)
    {
        $von = date_german2mysql($von);
        $bis = date_german2mysql($bis);
        $id = last_id2('WEG_WG_DEF', 'ID') + 1;
        $betrag = nummer_komma2punkt($betrag);
        $db_abfrage = "INSERT INTO WEG_WG_DEF VALUES (NULL, '$id', '$von', '$bis', '$betrag', '$kostenkat', '$e_konto', '$gruppe', '$g_konto','Einheit', '$einheit_id', '1')";
        DB::insert($db_abfrage);
        /* Zugewiesene MIETBUCHUNG_DAT auslesen */
        $last_dat = DB::getPdo()->lastInsertId();
        protokollieren('WEG_WG_DEF', '0', $last_dat);
    }

    function wohngeld_def_delete($dat)
    {
        $db_abfrage = "UPDATE WEG_WG_DEF SET AKTUELL='0' WHERE DAT='$dat'";
        DB::update($db_abfrage);
        protokollieren('WEG_WG_DEF', $dat, $dat);
    }

    function wohngeld_buchung_speichern($eigentuemer_id, $einheit_id, $geldkonto_id, $datum, $kontoauszug, $def_array, $def_b_texte, $wg_g_konto, $wg_g_betrag, $buchungstext)
    {
        /* Wohngeldgesamtbetrag buchen */
        $kontoauszugsnr = $kontoauszug;
        $b = new buchen ();
        $datum = date_german2mysql($datum);
        $wg_g_betrag = nummer_komma2punkt($wg_g_betrag);
        $b->geldbuchung_speichern_rechnung($datum, $kontoauszugsnr, $kontoauszugsnr, $wg_g_betrag, $buchungstext, $geldkonto_id, 'Eigentuemer', $eigentuemer_id, $wg_g_konto);

        /* Buchung der Einzelbeträge */
        $anz = count($def_array);
        if ($anz > 0) {
            $def_konten = array_keys($def_array);
        }
        for ($a = 0; $a < $anz; $a++) {
            $buchungskonto = $def_konten [$a];
            $buchungs_betrag = $def_array [$buchungskonto];

            $buchungstext1 = $def_b_texte [$a];
            /* Teilbuchung auf dem Gruppenkonto runter nehmen als Negativbetrag */
            if ($buchungs_betrag > 0) {
                $buchungs_betrag = '-' . $buchungs_betrag;
            } else {
                $buchungs_betrag = substr($buchungs_betrag, 1);
            }

            $buchungs_betrag_db = nummer_komma2punkt($buchungs_betrag);

            $b->geldbuchung_speichern_rechnung($datum, $kontoauszugsnr, $kontoauszugsnr, $buchungs_betrag_db, $buchungstext1, $geldkonto_id, 'Eigentuemer', $eigentuemer_id, $wg_g_konto);

            if ($buchungs_betrag < 0) {
                $buchungs_betrag = substr($buchungs_betrag, 1);
            } else {
                $buchungs_betrag = '-' . $buchungs_betrag;
            }

            $buchungs_betrag_db = nummer_komma2punkt($buchungs_betrag);

            /* Teilbuchung auf separatem Buchungskonto */
            $b->geldbuchung_speichern_rechnung($datum, $kontoauszugsnr, $kontoauszugsnr, $buchungs_betrag_db, $buchungstext1, $geldkonto_id, 'Eigentuemer', $eigentuemer_id, $buchungskonto);
        }
        $f = new formular ();
        $f->fieldset("Geldkontoinfos", 'kontrol');
        $g = new geldkonto_info ();
        $kontostand_aktuell = nummer_punkt2komma($g->geld_konto_stand(session()->get('geldkonto_id')));

        if (session()->has('temp_kontostand') && session()->has('temp_kontoauszugsnummer')) {
            $kontostand_temp = nummer_punkt2komma(session()->get('temp_kontostand'));
            echo "<h3>Kontostand am " . session()->get('temp_datum') . " laut Kontoauszug " . session()->get('temp_kontoauszugsnummer') . " war $kontostand_temp €</h3>";
        }

        if ($kontostand_aktuell == $kontostand_temp) {
            echo "<h3>Kontostand aktuell: $kontostand_aktuell €</h3>";
        } else {
            echo "<h3 style=\"color:red\">Kontostand aktuell: $kontostand_aktuell €</h3>";
        }
        $f->fieldset_ende();
        weiterleiten_in_sec(route('web::weg::legacy', ['option' => 'wohngeld_buchen_auswahl_e'], false), 3);
    }

    function hausgeld_kontoauszug($eigentuemer_id)
    {
        $this->hg_saldo = '0.00';
        $kos_bez = $this->get_eigentumer_id_infos($eigentuemer_id);
        $this->eigentuemer_von_a = date_mysql2german($this->eigentuemer_von);

        $datum_arr = explode('-', $this->eigentuemer_von);
        $j = $datum_arr [0]; // Jahr
        $m = $datum_arr [1]; // Monat
        $t = $datum_arr [2]; // Tag

        $akt_jahr = date("Y");
        $akt_datum = date("Y-m-d");
        $datum_1_def = $this->datum_erste_hg_def('Einheit', $this->einheit_id);
        $datum_1_def_arr = explode('-', $datum_1_def);
        $dat2 = $datum_1_def_arr [0] . $datum_1_def_arr [2] . $datum_1_def_arr [1];
        $dat1 = $j . $t . $m;
        if ($dat1 >= $dat2) {
            $datum_ab = "$j-$m-$t";
        } else {
            $datum_ab = $datum_1_def_arr [0] . '-' . $datum_1_def_arr [1] . '-' . $datum_1_def_arr [2];
        }

        $mi = new miete ();
        $diff_in_monaten = $mi->diff_in_monaten($datum_ab, $akt_datum);

        $datum_ab_arr = explode('-', $datum_ab);
        $j = $datum_ab_arr [0];
        $m = $datum_ab_arr [1];

        for ($a = 1; $a <= $diff_in_monaten; $a++) {
            $m = sprintf('%02d', $m);
            $soll_array [$a - 1] ['monat'] = $m;
            $soll_array [$a - 1] ['jahr'] = $j;

            if ($m == 12) {
                $m = 0;
                $j++;
            }
            $m++;
        }
        $moegliche_def_arr = $this->get_moegliche_def('Einheit', $this->einheit_id, date('Y'));
        $anz = count($moegliche_def_arr);
        echo "<table>";
        $spalten = $anz + 3;
        echo "<tr><th colspan=\"$spalten\">HAUSGELD-KONTOAUSZUG</th></tr>";
        echo "<tr><th colspan=\"$spalten\">$kos_bez</th></tr>";
        echo "<tr><th></th><th>Datum</th>";
        for ($k = 0; $k < $anz; $k++) {
            $kostenkat = $moegliche_def_arr [$k] ['KOSTENKAT'];
            if (strlen($kostenkat) > 14) {
                $kostenkat_t1 = substr($kostenkat, 0, 14);
                $kostenkat_t2 = substr($kostenkat, 14, strlen($kostenkat));
                $kostenkat = "$kostenkat_t1 $kostenkat_t2";
            }
            $e_konto = $moegliche_def_arr [$k] ['E_KONTO'];
            echo "<th>$kostenkat<br>$e_konto</th>";
        }
        echo "<th>GESAMT</th></tr>";

        /* Monatsschleife */
        $anz_monate = count($soll_array);
        for ($a = 0; $a < $anz_monate; $a++) {
            $monat = $soll_array [$a] ['monat'];
            $monatsname = monat2name($monat);
            $jahr = $soll_array [$a] ['jahr'];

            $gg = new geldkonto_info ();
            $gg->geld_konto_ermitteln('Objekt', $this->objekt_id, $jahr . '-' . $monat . '-01', 'Hausgeld');
            $geldkonto_id = $gg->geldkonto_id;

            $this->get_wg_info($monat, $jahr, 'Einheit', $this->einheit_id, 'Hausgeld');
            $this->Wohngeld_soll_a = nummer_punkt2komma($this->gruppe_erg);
            // echo "01.$monat.$jahr Wohngeld soll $this->Wohngeld_soll_a<br>";
            /* Sollzeile */
            echo "<tr><td><b>SOLL</b></td><td>$monatsname $jahr</td>";

            $monatliche_def_arr = $this->get_monatliche_def($monat, $jahr, 'Einheit', $this->einheit_id);
            $anz_defs = count($monatliche_def_arr);
            if ($anz_defs > 0) {

                for ($b = 0; $b < $anz; $b++) {
                    $teil_summe_a = 0;
                    $teil_summe = $monatliche_def_arr [$b] ['SUMME'];
                    if ($teil_summe > 0) {
                        $teil_summe_a = '-' . $teil_summe;
                    } elseif ($teil_summe < 0) {
                        $teil_summe_a = '+' . substr($teil_summe, 1);
                    }
                    if ($teil_summe == '0.00') {
                        $teil_summe_a = nummer_punkt2komma(0.00);
                    }
                    $teil_summe_a = nummer_punkt2komma($teil_summe_a);
                    /* Jede mögliche Definitionsspalte */
                    echo "<td align=\"right\">$teil_summe_a" . "€</td>";
                    /* Gesamtsoll spalte */
                    if ($b == $anz - 1) {
                        echo "<td align=\"right\">$this->Wohngeld_soll_a" . "€</td>";
                        $this->hg_saldo += nummer_komma2punkt($this->Wohngeld_soll_a);
                    }
                }
                echo "</tr>";
            }

            /* Wenn Zahlungen im Monat vorhanden */
            $summe_zahlungen = $this->get_summe_zahlungen('Eigentuemer', $eigentuemer_id, $monat, $jahr, $geldkonto_id);
            if ($summe_zahlungen > 0) {
                $this->hg_saldo += $summe_zahlungen;
                $this->hg_saldo_a = nummer_punkt2komma($this->hg_saldo);

                $spalten4 = count($moegliche_def_arr) + 1;
                $summe_zahlungen_a = nummer_punkt2komma($summe_zahlungen);
                echo "<tr class=\"zeile1\"><td><b>ZAHLUNGEN</b></td><td colspan=\"$spalten4\"><b>$monatsname $jahr</b></td><td align=\"right\">$summe_zahlungen_a" . "€</td></tr>";
            } else {
                $spalten4 = count($moegliche_def_arr) + 2;
                echo "<tr class=\"zeile1\"><td colspan=\"$spalten4\"><p class=\"rot\"><b>$monatsname $jahr - Keine Zahlungen </b></p></td><td align=\"right\"></td></tr>";
                $this->hg_saldo += 0.00;
            }

            $spalten2 = $spalten - 1;
            $spalten3 = $spalten - 2;
            $this->hg_saldo_a = nummer_punkt2komma($this->hg_saldo);
            echo "<tr class=\"zeile2\"><td colspan=\"$spalten2\"><b>Saldo $monatsname $jahr</b></td><td align=\"right\"><b>$this->hg_saldo_a" . "€</b></td></tr>";
        } // ende Monatsschleife
        $akt_datum_a = date_mysql2german($akt_datum);
        echo "<tr><th>$akt_datum_a</th><th colspan=\"$spalten3\">Saldo</th><th align=\"right\">$this->hg_saldo_a" . "€</th></tr>";
        echo "</table>";
    }

    function get_eigentumer_id_infos($e_id)
    {
        $einheit_id = $this->get_einheit_id_from_eigentuemer($e_id);
        $this->einheit_id = $einheit_id;
        $e = new einheit ();
        $e->get_einheit_info($einheit_id);
        $this->haus_strasse = $e->haus_strasse;
        $this->haus_nummer = $e->haus_nummer;
        $this->einheit_kurzname = $e->einheit_kurzname;
        $this->objekt_id = $e->objekt_id;
        $this->get_last_eigentuemer_namen($einheit_id);
        $miteigentuemer_namen = strip_tags($this->eigentuemer_namen2);
        $this->get_anrede_eigentuemer($e_id);
        return "$e->einheit_kurzname $miteigentuemer_namen";
    }

    function get_anrede_eigentuemer($e_id)
    {
        unset ($this->postanschrift);
        unset ($this->eig_namen_u);
        unset ($this->eig_namen_u_pdf);
        $this->eigentuemer_id = $e_id;

        $personen_id_arr = $this->get_person_id_eigentuemer_arr($this->eigentuemer_id);
        $anz_p = count($personen_id_arr);
        if (!$anz_p) {
        } else {
            unset ($this->eigentuemer_name);

            for ($a = 0; $a < $anz_p; $a++) {
                $person_id = $personen_id_arr [$a] ['PERSON_ID'];
                $p = new personen ();
                $p->get_person_infos($person_id);
                $this->eigentuemer_name [$a] ['person_id'] = $person_id;
                $this->eigentuemer_name [$a] ['Nachname'] = $p->person_nachname;
                $this->eigentuemer_name [$a] ['Vorname'] = $p->person_vorname;
                $this->eigentuemer_name [$a] ['Geburtstag'] = $p->person_geburtstag;
                $this->eigentuemer_name [$a] ['Geschlecht'] = $p->geschlecht;
                if ($p->geschlecht == 'weiblich') {
                    $this->eigentuemer_name [$a] ['Anrede'] = "geehrte Frau $p->person_nachname";
                    $this->eigentuemer_name [$a] ['HRFRAU'] = "Frau";
                }

                if ($p->geschlecht == 'männlich') {
                    $this->eigentuemer_name [$a] ['Anrede'] = "geehrter Herr $p->person_nachname";
                    $this->eigentuemer_name [$a] ['HRFRAU'] = "Herrn";
                }

                if (!$p->geschlecht) {
                    $this->eigentuemer_name [$a] ['Anrede'] = "geehrte Damen und Herren";
                }
                if (isset ($this->eigentuemer_name [$a] ['HRFRAU'])) {
                    $anrede = $this->eigentuemer_name [$a] ['HRFRAU'];
                } else {
                    $anrede = '';
                }

                $d = new detail ();
                if ($d->finde_detail_inhalt('Person', $person_id, 'Anschrift')) {
                    $this->postanschrift [$a] = $d->finde_detail_inhalt('Person', $person_id, 'Anschrift');
                } else {
                    $this->postanschrift [$a] = "$this->haus_strasse $this->haus_nummer\n$this->haus_plz $this->haus_stadt";
                    $this->eigentuemer_name_str_u1 .= "$anrede $p->person_nachname $p->person_vorname\n";
                }
            }

            $arr = array_sortByIndex($this->eigentuemer_name, 'Geschlecht', 'DESC');
            unset ($this->eigentuemer_name);
            $this->eigentuemer_name = $arr;
            $this->pdf_anrede = 'Sehr ';
            for ($a = 0; $a < $anz_p; $a++) {
                if ($a == 0) {
                    $this->pdf_anrede .= $this->eigentuemer_name [$a] ['Anrede'] . ',<br>';
                } else {
                    $this->pdf_anrede .= 'sehr ' . $this->eigentuemer_name [$a] ['Anrede'] . ',<br>';
                }
                if ($anz_p == 1) {
                    $this->eig_namen_u .= $this->eigentuemer_name [$a] ['HRFRAU'] . '<br>' . $this->eigentuemer_name [$a] ['Vorname'] . ' ' . $this->eigentuemer_name [$a] ['Nachname'] . '<br>';
                    $this->eig_namen_u_pdf .= $this->eigentuemer_name [$a] ['HRFRAU'] . "\n" . $this->eigentuemer_name [$a] ['Vorname'] . ' ' . $this->eigentuemer_name [$a] ['Nachname'] . "\n";
                } else {

                    if (!isset ($this->eigentuemer_name [$a] ['HRFRAU'])) {
                        $this->eigentuemer_name [$a] ['HRFRAU'] = '';
                    }

                    $this->eig_namen_u .= $this->eigentuemer_name [$a] ['HRFRAU'] . ' ' . $this->eigentuemer_name [$a] ['Vorname'] . ' ' . $this->eigentuemer_name [$a] ['Nachname'] . '<br>';
                    $this->eig_namen_u_pdf .= $this->eigentuemer_name [$a] ['HRFRAU'] . ' ' . $this->eigentuemer_name [$a] ['Vorname'] . ' ' . $this->eigentuemer_name [$a] ['Nachname'] . "\n";
                }

                $this->eigentuemer_name_str_u1 = '';
                $d = new detail ();
                if ($d->finde_detail_inhalt('Person', $person_id, 'Anschrift')) {
                    $this->postanschrift [$a] = $d->finde_detail_inhalt('Person', $person_id, 'Anschrift');
                } else {
                    $this->postanschrift [$a] = "$this->eig_namen_u_pdf$this->haus_strasse $this->haus_nummer\n$this->haus_plz $this->haus_stadt";
                    $this->eigentuemer_name_str_u1 .= "$anrede $p->person_nachname $p->person_vorname\n";
                }
            }

            $this->anrede = $this->pdf_anrede;
            $this->pdf_anrede = str_replace("<br>", "\n", $this->anrede);
        }
    }

    function get_monatliche_def($monat, $jahr, $kos_typ, $kos_id)
    {
        $result = DB::select("SELECT SUM(BETRAG) AS SUMME, E_KONTO, KOSTENKAT FROM WEG_WG_DEF WHERE KOS_TYP LIKE '$kos_typ' && KOS_ID='$kos_id' && E_KONTO!=6050 && AKTUELL='1' && ( ENDE = '0000-00-00' OR DATE_FORMAT( ENDE, '%Y-%m' ) >= '$jahr-$monat' && DATE_FORMAT( ANFANG, '%Y-%m' ) <= '$jahr-$monat' ) && DATE_FORMAT( ANFANG, '%Y-%m' ) <= '$jahr-$monat' GROUP BY KOSTENKAT ORDER BY E_KONTO ASC");
        if (!empty($result)) {
            return $result;
        }
    }

    function einnahmen_ausgaben($objekt_id)
    {
        $k = new kontenrahmen ();
        $kontenrahmen_id = $k->get_kontenrahmen('Objekt', $objekt_id);
        $gg = new geldkonto_info ();
        $gg->geld_konto_ermitteln('Objekt', $objekt_id, null, 'Hausgeld');
        $geldkonto_id = $gg->geldkonto_id;

        $kostenkonten_alle = $this->kostenkonten_in_array($geldkonto_id);
        $anz = count($kostenkonten_alle);
        for ($a = 0; $a < $anz; $a++) {
            $konto = $kostenkonten_alle [$a] ['KONTENRAHMEN_KONTO'];
            $k->konto_informationen2($konto, $kontenrahmen_id);
            $gruppen_arr [$a] ['GRUPPE'] = $k->konto_gruppen_bezeichnung;
            $gruppen_arr [$a] ['KONTOART'] = $k->konto_art_bezeichnung;
            $gruppen_arr [$a] ['KONTO'] = $k->konto;
            $gruppen_arr [$a] ['KONTO_BEZ'] = $k->konto_bezeichnung;

        }
        echo '<pre>';
        $gruppen_arr1 = array_sortByIndex($gruppen_arr, 'GRUPPE');

        echo "<table>";
        echo "<tr><th>Konto</th><th>ART</th><th>Gruppe</th><th>Bezeichnung</th><th>Summe</th></tr>";
        $g_summe = 0;
        for ($a = 0; $a < $anz; $a++) {
            $konto = $gruppen_arr1 [$a] ['KONTO'];
            $konto_bez = $gruppen_arr1 [$a] ['KONTO_BEZ'];
            $kontoart = $gruppen_arr1 [$a] ['KONTOART'];
            $gruppe = $gruppen_arr1 [$a] ['GRUPPE'];
            $summe = $gg->summe_geld_konto_buchungen_kontiert($geldkonto_id, $konto);
            $summe_a = nummer_punkt2komma($summe);
            echo "<tr><td>$konto</td><td>$kontoart</td><td>$gruppe</td><td>$konto_bez</td><td align=\"right\">$summe_a €</td></tr>";
            $g_summe + $summe;
        }
        echo "</table>";
    }

    function kostenkonten_in_array($geld_konto_id)
    {
        $result = DB::select("SELECT KONTENRAHMEN_KONTO FROM GELD_KONTO_BUCHUNGEN WHERE  AKTUELL='1' && GELDKONTO_ID='$geld_konto_id' GROUP BY KONTENRAHMEN_KONTO ORDER BY KONTENRAHMEN_KONTO ASC");
        if (!empty($result)) {
            return $result;
        }
    }

    function mahnliste($objekt_id)
    {
        $monat = date("m");
        $jahr = date("Y");
        $arr = $this->einheiten_weg_tabelle_arr($objekt_id);
        $anz = count($arr);
        if ($anz > 0) {
            echo "<table class=\"sortable striped\">";
            echo "<tr><th>einheit</th><th>Eigentümer</th><th>Hausgeld soll</th><th>Saldo</th><th>OPTIONEN</th></tr>";
            $summe = 0;
            for ($a = 0; $a < $anz; $a++) {
                $this->einheit_id = $arr [$a] ['EINHEIT_ID'];
                $e = new einheit ();
                $e->get_einheit_info($this->einheit_id);
                $u_link = "<a href='" . route('web::weg::legacy', ['option' => 'einheit_uebersicht', 'einheit_id' => $this->einheit_id]) . "'>$e->einheit_kurzname</a>";
                $this->get_last_eigentuemer_namen($this->einheit_id);
                $this->get_wg_info($monat, $jahr, 'Einheit', $this->einheit_id, 'Hausgeld');
                $this->Wohngeld_soll_a = nummer_punkt2komma($this->gruppe_erg);
                $mahn_link = "<a href='" . route('web::weg::legacy', ['option' => 'mahnen', 'eig' => $this->eigentuemer_id]) . "'>Mahnen</a>";

                $this->hausgeld_kontoauszug_stand($this->eigentuemer_id);
                echo "<tr><td>$u_link</td><td>$this->eigentuemer_namen</td><td>$this->Wohngeld_soll_a</td><td><b>$this->hg_erg_a €</b><td>$mahn_link</td></tr>";
                $summe += $this->hg_erg;
            }
            $summe_a = nummer_punkt2komma_t($summe);
            echo "<tr><td></td><td>SUMME</td><td></td><td><b>$summe_a</b><td></td></tr>";
            echo "</table";
        }
    }

    function get_eigentuemer_saldo($eigentuemer_id, $einheit_id)
    {
        $datum_def = $this->datum_erste_hg_def('Einheit', $einheit_id);
        $datum_def_a = str_replace('-', '', $datum_def);

        $eigentuemer_von = $this->eigentuemer_von;
        $eigentuemer_von_a = str_replace('-', '', $this->eigentuemer_von);;
        if ($datum_def_a > $eigentuemer_von_a) {
            $datum = $datum_def;
        } else {
            $datum = $eigentuemer_von;
        }

        $arr = $this->monatsarray_erstellen($datum, $this->eigentuemer_bis);

        $anz = count($arr);
        $this->zahlung_gesamt = 0.00;
        $this->soll_gesamt = 0.00;
        $this->soll_aktuell = 0.00;
        for ($a = 0; $a < $anz; $a++) {
            $mo = $arr [$a] ['monat'];
            $ja = $arr [$a] ['jahr'];

            $arr [$a] ['zahlung'] = $this->get_summe_zahlungen('Eigentuemer', $eigentuemer_id, $mo, $ja, session()->get('geldkonto_id'), '6020');
            $arr [$a] ['zahlung'] += $this->get_summe_zahlungen('Eigentuemer', $eigentuemer_id, $mo, $ja, session()->get('geldkonto_id'), '6030');
            $arr [$a] ['zahlung'] += $this->get_summe_zahlungen('Eigentuemer', $eigentuemer_id, $mo, $ja, session()->get('geldkonto_id'), '6040');
            $zahlung = $arr [$a] ['zahlung'];
            $this->zahlung_gesamt += $zahlung;

            $arr [$a] ['soll'] = $this->get_summe_kostenkat_gruppe_m2($mo, $ja, 'Einheit', $einheit_id, 'Hausgeld');
            $this->soll_gesamt += $arr [$a] ['soll'];
            $this->soll_aktuell = $arr [$a] ['soll'];
        }
        $this->hg_erg = $this->zahlung_gesamt + $this->soll_gesamt;
        $this->hg_erg_a = nummer_punkt2komma($this->hg_erg);
    }

    function monatsarray_erstellen($von, $bis)
    {
        $mi = new miete ();
        $diff_in_monaten = $mi->diff_in_monaten($von, $bis);

        $von_arr = explode('-', $von);
        $von_j = $von_arr [0];
        $von_m = $von_arr [1];
        $von_d = $von_arr [2];

        $bis_arr = explode('-', $bis);
        $bis_d = $bis_arr [2];

        $monat = $von_m;
        $jahr = $von_j;

        $tag_anz = 0;
        for ($a = 0; $a < $diff_in_monaten; $a++) {

            if ($a == 0) {
                $arr [$a] ['tage_n'] = $this->tage_im_monat($monat, $jahr) - $von_d + 1; // FALSCH ein tag fehlt bei beginn 1 oder 31
                $tag_anz += $this->tage_im_monat($monat, $jahr) - $von_d;
            } else {
                $arr [$a] ['tage_n'] = $this->tage_im_monat($monat, $jahr);
                $tag_anz += $this->tage_im_monat($monat, $jahr);
            }
            if ($a == $diff_in_monaten - 1) {
                $arr [$a] ['tage_n'] = $bis_d;
                $tag_anz += $bis_d - $this->tage_im_monat($monat, $jahr);;
            }

            $arr [$a] ['tage_b'] = $tag_anz;

            if ($monat < 12) {
                $arr [$a] ['monat'] = sprintf('%02d', $monat);
                $arr [$a] ['jahr'] = $jahr;
                $arr [$a] ['tage_m'] = $this->tage_im_monat($monat, $jahr);
            }
            // $monat_zweistellig = sprintf('%02d',$a);
            if ($monat == 12) {
                $arr [$a] ['monat'] = sprintf('%02d', $monat);
                $arr [$a] ['jahr'] = $jahr;
                $arr [$a] ['tage_m'] = $this->tage_im_monat($monat, $jahr);

                $monat = 01;
                $jahr++;
            } else {
                $monat++;
            }
        }
        return $arr;
    }

    function tage_im_monat($monat, $jahr)
    {
        return cal_days_in_month(CAL_GREGORIAN, $monat, $jahr);
    }

    function form_mahnen($eig)
    {
        $f = new formular ();
        $this->hausgeld_kontoauszug_stand($eig);
        $eig_bez = $this->get_eigentumer_id_infos($eig);
        $f->erstelle_formular("Mahnen $eig_bez", null);
        $this->dropdown_anschrift('Letzte Anschriften der Eigentuemer aus den Details', 'anschriften', 'ans', $eig, '');
        $f->datum_feld('Datum Frist', 'datum', date("d.m.Y"), 'datum');
        $f->text_feld('Mahngebühr', 'mahngebuehr', '0,00', 10, 'mahngebuehr', '');
        $f->text_feld_inaktiv('Mahnbetrag', 'mahnbetrag', $this->hg_erg_a, 10, 'mahnbetrag');
        $f->hidden_feld('option', 'mahnung_sent');
        $f->send_button('send', 'PDF Ansicht');
        $f->fieldset_ende();
        $f->ende_formular();
    }

    function dropdown_anschrift($label, $name, $id, $eig, $js)
    {
        $this->get_eigentumer_id_infos($eig);
        if (!empty ($this->postanschrift [0])) {
            $anz_anschriften = count($this->postanschrift);
            echo "<label for=\"$id\">$label</label>";
            echo "<select name=\"$name\" size=\"1\" id=\"$id\" $js>\n";
            for ($a = 0; $a < $anz_anschriften; $a++) {
                $anschrift = $this->postanschrift [$a];
                echo "<option value=\"$a\">$anschrift</option>\n";
            }
            echo "</select>";
        } else {
            echo "<p>Das Schreiben geht an $this->haus_strasse $this->haus_nummer</p>";
        }
    }

    function pdf_mahnschreiben($eig, $datum, $mahngebuehr, $anschrift = '')
    {
        $this->get_eigentumer_id_infos($eig);
        $this->hausgeld_kontoauszug_stand($eig);
        $g = new geldkonto_info ();
        $g->geld_konto_ermitteln('Objekt', $this->objekt_id, null, 'Hausgeld');
        $geldkonto_id = $g->geldkonto_id;
        $e = new einheit ();
        $e->get_einheit_info($this->einheit_id);

        if ($anschrift != '') {
            $standard_anschrift = str_replace('<br />', "\n", $this->postanschrift [$anschrift]);
        } else {
            $standard_anschrift = "$this->eig_namen_u_pdf$e->haus_strasse $e->haus_nummer\n\n$e->haus_plz $e->haus_stadt";
        }
        $pdf = new Cezpdf ('a4', 'portrait');
        $bpdf = new b_pdf ();
        $bpdf->b_header($pdf, 'Partner', session()->get('partner_id'), 'portrait', 'Helvetica.afm', 6);

        $pdf->ezStopPageNumbers(); // seitennummerirung beenden
        $p = new partners ();
        $p->get_partner_info(session()->get('partner_id'));
        $datum_heute = date("d.m.Y");
        $pdf->addText(480, 697, 8, "$p->partner_ort, $datum_heute");

        $pdf->ezText($standard_anschrift, 12);
        $pdf->ezSetDy(-60);
        /* Betreff */
        $pdf->ezText("<b>Mahnung </b>", 11);
        $pdf->ezSetDy(-10);
        /* Faltlinie */
        $pdf->setLineStyle(0.2);
        $pdf->line(5, 542, 20, 542);
        /* Anrede */
        $pdf->ezText("$anrede", 11);
        $pdf->ezText("$this->pdf_anrede", 11);
        $brief_text = "nach Durchsicht unserer Buchhaltungsunterlagen mussten wir feststellen, dass Ihr Hausgeldkonto für die Einheit <b>$this->einheit_kurzname</b>, $e->haus_strasse $e->haus_nummer (Lage $e->einheit_lage), in $e->haus_plz $e->haus_stadt folgenden Rückstand aufweist:\n";
        $pdf->ezText("$brief_text", 11, array(
            'justification' => 'full'
        ));

        $pdf->ezSetCmMargins(3, 3, 6, 7);
        $pdf->ezText("<b>Hausgeldrückstand</b>", 12);

        $pdf->ezSetDy(13);
        $pdf->setColor(1.0, 0.0, 0.0);
        if ($this->hg_erg < 0.00) {
            $this->hg_erg = substr($this->hg_erg, 1);
            $this->hg_erg_a = nummer_punkt2komma($this->hg_erg);
        }
        $pdf->ezText("<b>$this->hg_erg_a €</b>", 12, array(
            'justification' => 'right'
        ));

        $pdf->setColor(0.0, 0.0, 0.0);
        $pdf->ezText("<b>zzgl. Mahngebühr</b>", 12);
        $pdf->ezSetDy(13);

        $pdf->ezText("<b>$mahngebuehr €</b>", 12, array(
            'justification' => 'right'
        ));
        /* Linie über Gesamtrückstand */
        $pdf->ezSetDy(-5);
        $pdf->line(170, $pdf->y, 403, $pdf->y);
        $pdf->setColor(0.0, 0.0, 0.0);
        $pdf->ezText("<b>Gesamtrückstand</b>", 12);
        $pdf->ezSetDy(13);
        $pdf->setColor(1.0, 0.0, 0.0);
        $mahngebuehr_r = nummer_komma2punkt($mahngebuehr);
        $gesamt_rueckstand = abs($this->hg_erg) + $mahngebuehr_r;
        $gesamt_rueckstand = nummer_punkt2komma($gesamt_rueckstand);
        $pdf->ezText("<b>$gesamt_rueckstand €</b>\n", 12, array(
            'justification' => 'right'
        ));

        $g->geld_konto_details($geldkonto_id);
        $pdf->ezSetMargins(135, 70, 50, 50);

        $pdf->setColor(0.0, 0.0, 0.0);
        $pdf->ezText("Die konkreten Fehlbeträge entnehmen Sie bitte dem beigefügten Hausgeld-Kontoauszug.", 11);
        $pdf->ezText("Wir fordern Sie auf, den genannten Betrag unter Angabe Ihrer Eigentümernummer\n<b>$this->einheit_kurzname</b> bis zum", 11);
        $pdf->ezSetCmMargins(3, 3, 9, 3);
        $pdf->setColor(1.0, 0.0, 0.0);
        $pdf->ezText("<b>$datum</b>\n", 11);
        $pdf->ezSetMargins(135, 70, 50, 50);
        $pdf->ezText("<b>auf das Konto der WEG (IBAN: $g->IBAN1, BIC: $g->BIC)\nbei der $g->kredit_institut\n</b>", 11);
        $pdf->setColor(0.0, 0.0, 0.0);
        $pdf->ezText("zu überweisen.\n", 11);
        $pdf->ezText("Für Rückfragen stehen wir Ihnen gerne zur Verfügung.\n", 11);
        $pdf->ezText("Mit freundlichen Grüßen\n\n\n", 11);
        $pdf->ezText("Berlus GmbH\n\n", 11);
        $pdf->ezText("Dieses Schreiben wurde maschinell erstellt und ist daher ohne Unterschrift gültig.\n", 11);
        $pdf->addInfo('Title', "Mahnung $mv->personen_name_string");
        $pdf->addInfo('Author', Auth::user()->email);
        $this->hausgeld_kontoauszug_pdf($pdf, $eig, 1);
        ob_end_clean(); // ausgabepuffer leeren
        $pdf->ezStream();
    }

    function hausgeld_kontoauszug_pdf(Cezpdf &$pdf, $eigentuemer_id, $seite = "0")
    {
        if ($seite != 0) {
            $pdf->ezNewPage();
        }
        $this->hg_saldo = '0.00';
        $kos_bez = $this->get_eigentumer_id_infos2($eigentuemer_id);
        $this->eigentuemer_von_a = date_mysql2german($this->eigentuemer_von);

        $datum_arr = explode('-', $this->eigentuemer_von);
        $j = $datum_arr [0]; // Jahr
        $m = $datum_arr [1]; // Monat
        $t = $datum_arr [2]; // Tag

        if (!request()->has('jahr') || request()->input('jahr') == date("Y")) {
            $akt_jahr = date("Y");
            $akt_monat = date("m");
        } else {
            $akt_jahr = request()->input('jahr');
            $akt_monat = 12;
        }

        $akt_datum = date("Y-m-d");
        $datum_1_def = $this->datum_erste_hg_def('Einheit', $this->einheit_id);

        $datum_1_def_arr = explode('-', $datum_1_def);
        $dat2 = $datum_1_def_arr [0] . $datum_1_def_arr [2] . $datum_1_def_arr [1];
        $dat1 = $j . $t . $m;
        if ($dat1 >= $dat2) {
            $datum_ab = "$j-$m-$t";
        } else {
            $datum_ab = $datum_1_def_arr [0] . '-' . $datum_1_def_arr [1] . '-' . $datum_1_def_arr [2];
        }

        $mi = new miete ();

        $datum_ab_arr = explode('-', $datum_ab);
        $j = $datum_ab_arr [0];

        $datum_arr = explode('-', $this->eigentuemer_von);
        $je = $datum_arr [0]; // Jahr
        $me = $datum_arr [1]; // Monat
        $te = $datum_arr [2]; // Tag

        if ($je >= $akt_jahr) {
            $akt_jahr = $je;
            $m = $me;
            $j = $je;
        } else {
            $m = 1;
        }

        for ($a = $m; $a <= $akt_monat; $a++) {
            $m = sprintf('%02d', $a);
            $soll_array [$a - 1] ['monat'] = $m;
            $soll_array [$a - 1] ['jahr'] = $akt_jahr;

            if ($m == 12) {
                $m = 0;
                $j++;
            }
        }

        $moegliche_def_arr = $this->get_moegliche_def('Einheit', $this->einheit_id, $akt_jahr);
        $anz = count($moegliche_def_arr);

        $spalten = $anz + 3;
        $pdf->ezText("<b>$kos_bez, $this->haus_strasse $this->haus_nummer</b>", 11, array(
            'justification' => 'full'
        ));
        $pdf->ezText("HAUSGELD-KONTOAUSZUG", 11, array(
            'justification' => 'full'
        ));

        $zeile = 0;
        for ($k = 0; $k < $anz; $k++) {
            $kostenkat = $moegliche_def_arr [$k] ['KOSTENKAT'];
            if (strlen($kostenkat) > 14) {
                $kostenkat_t1 = substr($kostenkat, 0, 14);
                $kostenkat_t2 = substr($kostenkat, 14, strlen($kostenkat));
            }
        }
        $anz_monate = count($soll_array);

        $soll_monate = array_keys($soll_array);
        $xx = $soll_monate [0]; // erster
        $anz_monate = $soll_monate [$anz_monate - 1]; // letzter

        for ($a = $xx; $a <= $anz_monate; $a++) {
            $monat = $soll_array [$a] ['monat'];
            $monatsname = monat2name($monat);
            $jahr = $soll_array [$a] ['jahr'];

            $gg = new geldkonto_info ();
            $gg->geld_konto_ermitteln('Objekt', $this->objekt_id, $jahr . '-' . $monat . '-01', 'Hausgeld');
            $geldkonto_id = $gg->geldkonto_id;

            $this->get_wg_info($monat, $jahr, 'Einheit', $this->einheit_id, 'Hausgeld');
            if ($this->eigentuemer_bis !== "0000-00-00") {
                $date = new DateTime("$jahr-$monat-01");
                $bis = new DateTime($this->eigentuemer_bis);
                if ($bis > $date) {
                    $this->Wohngeld_soll_a = nummer_punkt2komma($this->gruppe_erg);
                } else {
                    $this->Wohngeld_soll_a = "0,00";
                }
            } else {
                $this->Wohngeld_soll_a = nummer_punkt2komma($this->gruppe_erg);
            }

            $monatliche_def_arr = $this->get_monatliche_def($monat, $jahr, 'Einheit', $this->einheit_id);
            $anz_defs = count($monatliche_def_arr);
            if ($anz_defs > 0) {

                for ($b = 0; $b < $anz; $b++) {
                    $teil_summe_a = 0;
                    $teil_summe = $monatliche_def_arr [$b] ['SUMME'];
                    if ($teil_summe > 0) {
                        $teil_summe_a = '-' . $teil_summe;
                    } elseif ($teil_summe < 0) {
                        $teil_summe_a = '+' . substr($teil_summe, 1);
                    }
                    if ($teil_summe == '0.00') {
                        $teil_summe_a = nummer_punkt2komma(0.00);
                    }
                    if ($b == $anz - 1) {
                        $tab_arr [$zeile] ['DATUM'] = "01.$monat.$jahr";
                        $tab_arr [$zeile] ['TEXT'] = "Hausgeld soll";
                        $tab_arr [$zeile] ['BETRAG'] = nummer_punkt2komma(nummer_komma2punkt($this->Wohngeld_soll_a) * -1);
                        $zeile++;
                        $this->hg_saldo += nummer_komma2punkt($this->Wohngeld_soll_a) * -1;
                    }
                }
            }

            $k = new kontenrahmen ();
            $kontenrahmen_id = $k->get_kontenrahmen('Geldkonto', $geldkonto_id);

            $su_konten_im_kontenrahmen = DB::table('KONTENRAHMEN_KONTEN')
                ->where('AKTUELL', '1')
                ->where('SONDERUMLAGE', '1')
                ->where('KONTO_ART', 4)
                ->where('KONTENRAHMEN_ID', $kontenrahmen_id)
                ->get(['KONTO']);

            /* Wenn Zahlungen im Monat vorhanden */
            $summe_zahlungen_6 = $this->get_summe_zahlungen('Eigentuemer', $eigentuemer_id, $monat, $jahr, $geldkonto_id, 6000);
            $summe_zahlungen_hz = $this->get_summe_zahlungen('Eigentuemer', $eigentuemer_id, $monat, $jahr, $geldkonto_id, 6010);
            $summe_zahlungen_hg = $this->get_summe_zahlungen('Eigentuemer', $eigentuemer_id, $monat, $jahr, $geldkonto_id, 6020);
            $summe_zahlungen_ihr = $this->get_summe_zahlungen('Eigentuemer', $eigentuemer_id, $monat, $jahr, $geldkonto_id, 6030);
            $summe_zahlungen_vg = $this->get_summe_zahlungen('Eigentuemer', $eigentuemer_id, $monat, $jahr, $geldkonto_id, 6060);
            $summe_zahlungen_su = 0;
            foreach ($su_konten_im_kontenrahmen as $konto) {
                $summe_zahlungen_su += $this->get_summe_zahlungen('Eigentuemer', $eigentuemer_id, $monat, $jahr, $geldkonto_id, $konto['KONTO']);
            }
            $summe_zahlungen = $summe_zahlungen_hz + $summe_zahlungen_hg + $summe_zahlungen_ihr + $summe_zahlungen_vg + $summe_zahlungen_6 + $summe_zahlungen_su;
            if ($summe_zahlungen) {

                $this->hg_saldo += $summe_zahlungen;
                $this->hg_saldo_a = nummer_punkt2komma($this->hg_saldo);

                $summe_zahlungen_a = nummer_punkt2komma($summe_zahlungen);
                $tab_arr [$zeile] ['DATUM'] = "$monatsname $jahr";
                $tab_arr [$zeile] ['TEXT'] = "Summe Buchungen";
                $tab_arr [$zeile] ['BETRAG'] = "$summe_zahlungen_a";
                $zeile++;
            } else {
                $tab_arr [$zeile] ['DATUM'] = "$monatsname $jahr";
                $tab_arr [$zeile] ['TEXT'] = "Keine Hausgeldzahlung";
                $tab_arr [$zeile] ['BETRAG'] = "0,00";
                $this->hg_saldo += 0.00;
                $zeile++;
            }

            $this->hg_saldo_a = nummer_punkt2komma($this->hg_saldo);
            $tab_arr [$zeile] ['DATUM'] = "<b>$monatsname $jahr</b>";
            $tab_arr [$zeile] ['TEXT'] = "<b>Monatssaldo</b>";
            $tab_arr [$zeile] ['SALDO'] = "<b>$this->hg_saldo_a</b>";
            $zeile++;

            $tab_arr [$zeile] ['DATUM'] = "_____________";
            $tab_arr [$zeile] ['TEXT'] = "______________________________________";
            $tab_arr [$zeile] ['BETRAG'] = "________________";
            $tab_arr [$zeile] ['SALDO'] = "________________";
            $zeile++;
        } // ende Monatsschleife
        if ($akt_jahr == date("Y")) {
            $akt_datum_a = date_mysql2german($akt_datum);
        } else {
            $akt_datum_a = date_mysql2german("$akt_jahr-12-31");
        }
        $tab_arr [$zeile + 1] ['DATUM'] = "<b>$akt_datum_a</b>";
        $tab_arr [$zeile + 1] ['TEXT'] = "<b>Saldo aktuell</b>";
        $tab_arr [$zeile + 1] ['SALDO'] = "<b>$this->hg_saldo_a</b>";

        $cols = array(
            'DATUM' => "DATUM",
            'TEXT' => "BESCHREIBUNG",
            'BETRAG' => "BETRAG",
            'SALDO' => "SALDO"
        );
        $pdf->ezSetDy(-10);
        $pdf->ezTable($tab_arr, $cols, "", array(
            'showHeadings' => 1,
            'shaded' => 0,
            'titleFontSize' => 8,
            'fontSize' => 7,
            'xPos' => 50,
            'xOrientation' => 'right',
            'width' => 500,
            'rowGap' => 1,
            'cols' => array(
                'DATUM' => array(
                    'justification' => 'right',
                    'width' => 70
                ),
                'BEMERKUNG' => array(
                    'justification' => 'left',
                    'width' => 300
                ),
                'BETRAG' => array(
                    'justification' => 'right',
                    'width' => 75
                ),
                'SALDO' => array(
                    'justification' => 'right',
                    'width' => 75
                )
            )
        ));

        return $pdf;
    }

    function get_eigentumer_id_infos2($e_id)
    {
        $einheit_id = $this->get_einheit_id_from_eigentuemer($e_id);
        $this->einheit_id = $einheit_id;
        $e = new einheit ();
        $e->get_einheit_info($einheit_id);
        $this->haus_strasse = $e->haus_strasse;
        $this->haus_nummer = $e->haus_nummer;
        $this->haus_plz = $e->haus_plz;
        $this->haus_stadt = $e->haus_stadt;
        $this->einheit_kurzname = $e->einheit_kurzname;
        $this->objekt_id = $e->objekt_id;
        $this->get_eigentuemer_namen($e_id);
        $this->get_anrede_eigentuemer($e_id);
        $miteigentuemer_namen = strip_tags($this->eigentuemer_name_str);

        $this->SEPA_MANDAT = 'WEG-ET' . $e_id;
        $sep = new sepa ();
        if ($sep->check_m_ref($this->SEPA_MANDAT)) {
            $this->SEPA_MANDAT_AKTIV = 1;
            $sep->get_mandat_infos_mref($this->SEPA_MANDAT);
            $this->MAND = $sep->mand;
        } else {
            $this->SEPA_MANDAT_AKTIV = 0;
        }
        return "$e->einheit_kurzname $miteigentuemer_namen";
    }

    function hg_kontoauszug_anzeigen_pdf($eigentuemer_id)
    {
        $this->get_eigentumer_id_infos($eigentuemer_id);
        $this->hausgeld_kontoauszug_stand($eigentuemer_id);

        $pdf = new Cezpdf ('a4', 'portrait');
        $bpdf = new b_pdf ();
        $bpdf->b_header($pdf, 'Partner', session()->get('partner_id'), 'portrait', 'Helvetica.afm', 6);
        $this->hausgeld_kontoauszug_pdf($pdf, $eigentuemer_id, 0); // null für keine neue Seite
        if (request()->has('jahr')) {
            $this->hg_ist_soll_pdf($pdf, $eigentuemer_id);
            $this->hga_uebersicht_pdf($pdf, $eigentuemer_id);
        }
        $pdf->ezStream();
    }

    function hg_ist_soll_pdf(Cezpdf $pdf, $eigentuemer_id)
    {
        $this->get_eigentumer_id_infos($eigentuemer_id);
        if (request()->has('jahr')) {
            $jahr = request()->input('jahr');
            if (new DateTime("$jahr-01-01") < new DateTime($this->eigentuemer_von)) {
                $anfangsdatum = $this->eigentuemer_von;
            } else {
                $anfangsdatum = "$jahr-01-01";
            }
            $akt_jahrt = date("Y");
            if ($jahr == $akt_jahrt) {
                $ende_datum = date("Y-m-d");
            } else {
                $ende_datum = "$jahr-12-31";
            }
        } else {
            $anfangsdatum = $this->eigentuemer_von;
            $ende_datum = $this->eigentuemer_bis;
        }

        if (!empty ($anfangsdatum)) {
            $anfangsdatum_arr = explode('-', $anfangsdatum);
            $m = $anfangsdatum_arr [1];
            $j = $anfangsdatum_arr [0];
        } else {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\ErrorMessage("Kein eigentuemer_von Datum")
            );
        }

        $moegliche_defs = $this->get_moegliche_def('Einheit', $this->einheit_id, $j);
        $akt_datum = date("Y-m-d");
        $akt_jahr = date("Y");

        $mi = new miete ();
        if ($ende_datum == '0000-00-00') {
            $diff_in_monaten = $mi->diff_in_monaten($anfangsdatum, $akt_datum);
        } else {
            $diff_in_monaten = $mi->diff_in_monaten($anfangsdatum, $ende_datum);
        }

        for ($a = 1; $a <= $diff_in_monaten; $a++) {
            $m = sprintf('%02d', $m);
            $soll_array [$a - 1] ['monat'] = $m;
            $soll_array [$a - 1] ['jahr'] = $j;

            if ($m == 12) {
                $m = 0;
                $j++;
            }
            $m++;
        }

        $anz_monate = count($soll_array);
        $anz_defs = count($moegliche_defs);
        for ($a = 0; $a < $anz_monate; $a++) {
            $monat = $soll_array [$a] ['monat'];
            $jahr = $soll_array [$a] ['jahr'];
            if ($this->eigentuemer_bis !== "0000-00-00") {
                $date = new DateTime("$jahr-$monat-01");
                $bis = new DateTime($this->eigentuemer_bis);
                if ($bis <= $date) {
                    continue;
                }
            }

            $gg = new geldkonto_info ();
            $gg->geld_konto_ermitteln('Objekt', $this->objekt_id, $jahr . '-' . $monat . '-01', 'Hausgeld');
            $geldkonto_id = $gg->geldkonto_id;

            for ($b = 0; $b < $anz_defs; $b++) {
                $e_konto = $moegliche_defs [$b] ['E_KONTO'];
                $kostenkat = $moegliche_defs [$b] ['KOSTENKAT'];
                $soll_ist_arr [$b] ['KOSTENKAT'] = $kostenkat;
                $summe_kostenkat = $this->get_summe_kostenkat($monat, $jahr, 'Einheit', $this->einheit_id, $kostenkat);
                $soll_ist_arr [$b] ['SUMME_SOLL'] += $summe_kostenkat;
                if ($b > 0 && $moegliche_defs [$b] ['E_KONTO'] == $moegliche_defs [$b - 1] ['E_KONTO']) {
                    $soll_ist_arr [$b] ['KONTO'] = '';
                    $summe_ist_zahlungen = 0;
                } else {
                    $soll_ist_arr [$b] ['KONTO'] = $e_konto;
                    $summe_ist_zahlungen = $this->get_summe_zahlungen_kostenkonto('Eigentuemer', $eigentuemer_id, $monat, $jahr, $geldkonto_id, $e_konto);
                }
                $soll_ist_arr [$b] ['SUMME_IST'] += $summe_ist_zahlungen;
            }
        }

        $anz_konten = count($soll_ist_arr);
        $g_ist = 0;
        $g_soll = 0;
        $g_saldo = 0;
        $aggregate_saldo = 0;
        $aggregate_saldo_row = 0;
        for ($a = 0; $a < $anz_konten; $a++) {
            $soll = $soll_ist_arr [$a] ['SUMME_SOLL'];
            $soll_ist_arr [$a] ['SUMME_SOLL'] = nummer_punkt2komma($soll);
            $g_soll += $soll;
            if ($soll_ist_arr [$a] ['KONTO'] == '') {
                $soll_ist_arr [$a] ['SUMME_IST'] = '';
                $soll_ist_arr [$a] ['SALDO'] = '';
                $aggregate_saldo -= $soll;
                $g_saldo -= $soll;
                $soll_ist_arr [$aggregate_saldo_row] ['SALDO'] = nummer_punkt2komma($aggregate_saldo);
            } else {
                $ist = $soll_ist_arr [$a] ['SUMME_IST'];
                $soll_ist_arr [$a] ['SUMME_IST'] = nummer_punkt2komma($ist);
                $g_ist += $ist;
                $saldo = $ist - $soll;
                $g_saldo += $saldo;
                $aggregate_saldo = $saldo;
                $aggregate_saldo_row = $a;
                $soll_ist_arr [$a] ['SALDO'] = nummer_punkt2komma($saldo);
            }
        }

        $soll_ist_arr [$a + 1] ['KOSTENKAT'] = '<b>Summen</b>';
        $soll_ist_arr [$a + 1] ['SUMME_SOLL'] = '<b>' . nummer_punkt2komma($g_soll) . '</b>';
        $soll_ist_arr [$a + 1] ['SUMME_IST'] = '<b>' . nummer_punkt2komma($g_ist) . '</b>';
        $soll_ist_arr [$a + 1] ['SALDO'] = '<b>' . nummer_punkt2komma($g_saldo) . '</b>';

        $cols = array(
            'KONTO' => "<b>KONTO</b>",
            'KOSTENKAT' => "<b>BEZEICHNUNG</b>",
            'SUMME_SOLL' => "<b>SOLL</b>",
            'SUMME_IST' => "<b>IST</b>",
            'SALDO' => "<b>SALDO</b>"
        );
        $pdf->ezSetDy(-10);
        $pdf->ezTable($soll_ist_arr, $cols, "", array(
            'showHeadings' => 1,
            'shaded' => 0,
            'titleFontSize' => 8,
            'fontSize' => 7,
            'xPos' => 50,
            'xOrientation' => 'right',
            'width' => 500,
            'rowGap' => 1,
            'cols' => array(
                'DATUM' => array(
                    'justification' => 'right',
                    'width' => 70
                ),
                'BEMERKUNG' => array(
                    'justification' => 'left',
                    'width' => 300
                ),
                'BETRAG' => array(
                    'justification' => 'right',
                    'width' => 75
                ),
                'SALDO' => array(
                    'justification' => 'right',
                    'width' => 75
                )
            )
        ));
        return $pdf;
    }

    function get_summe_zahlungen_kostenkonto($kos_typ, $kos_id, $monat, $jahr, $geldkonto_id, $buchungskonto)
    {
        $result = DB::select("SELECT SUM(BETRAG) AS SUMME FROM GELD_KONTO_BUCHUNGEN WHERE DATE_FORMAT(DATUM, '%Y-%m') = '$jahr-$monat' && KOSTENTRAEGER_TYP LIKE '$kos_typ' && KOSTENTRAEGER_ID='$kos_id' && AKTUELL='1' && GELDKONTO_ID='$geldkonto_id' && KONTENRAHMEN_KONTO='$buchungskonto' ORDER BY DATUM ASC");
        if (!empty($result)) {
            return $result[0]['SUMME'];
        }
        return 0;
    }

    function hga_uebersicht_pdf(Cezpdf $pdf, $eigentuemer_id)
    {
        $e_konto = 6050;
        $this->get_eigentumer_id_infos($eigentuemer_id);
        if (request()->has('jahr')) {
            $jahr = request()->input('jahr');
        } else {
            $jahr = $akt_jahr = date("Y");
        }

        $gg = new geldkonto_info ();
        $gg->geld_konto_ermitteln('Objekt', $this->objekt_id, null, 'Hausgeld');
        $geldkonto_id = $gg->geldkonto_id;
        if (!isset($geldkonto_id)) {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\InfoMessage("Es existiert kein Geldkonto für das Objekt.")
            );
        }

        $eigentuemer_ids = $this->get_eigentuemer_arr($this->einheit_id);

        $g_soll = 0;
        $g_ist = 0;

        $anz = count($eigentuemer_ids);
        for ($a = 0; $a < $anz; $a++) {
            $g_ist += $this->get_ergebnis_hga_ist($this->objekt_id, $eigentuemer_ids[$a]['ID'], $jahr, $geldkonto_id, $e_konto);
        }

        $ergebnisse_hga = $this->get_ergebnisse_hga_soll($this->einheit_id, $jahr, $e_konto);

        $anz = count($ergebnisse_hga);
        for ($a = 0; $a < $anz; $a++) {
            $g_soll += $ergebnisse_hga [$a] ['SOLL'];
            $ergebnisse_hga [$a] ['SOLL'] = nummer_punkt2komma($ergebnisse_hga [$a] ['SOLL']);
        }

        $g_saldo = $g_ist - $g_soll;
        if ($a > 0) {
            $ergebnisse_hga[0]['IST'] = nummer_punkt2komma($g_ist);
            $ergebnisse_hga[0]['SALDO'] = nummer_punkt2komma($g_saldo);
        }

        $ergebnisse_hga [$a + 1] ['HGA'] = '<b>Summen</b>';
        $ergebnisse_hga [$a + 1] ['SOLL'] = '<b>' . nummer_punkt2komma($g_soll) . '</b>';
        $ergebnisse_hga [$a + 1] ['IST'] = '<b>' . nummer_punkt2komma($g_ist) . '</b>';
        $ergebnisse_hga [$a + 1] ['SALDO'] = '<b>' . nummer_punkt2komma($g_saldo) . '</b>';

        // print_r($soll_ist_arr);

        $cols = array(
            'HGA' => "<b>HGA</b>",
            'SOLL' => "<b>SOLL (- ist Guthaben)</b>",
            'IST' => "<b>IST</b>",
            'SALDO' => "<b>SALDO</b>"
        );
        $page = $pdf->ezGetCurrentPageNumber();
        $pdf->transaction('start');
        $pdf->ezSetDy(-10);
        $pdf->ezText("ERGEBNISSE HAUSGELDABRECHNUNGEN", 11, array(
            'justification' => 'full'
        ));
        $pdf->ezSetDy(-5);
        $pdf->ezTable($ergebnisse_hga, $cols, "", array(
            'showHeadings' => 1,
            'shaded' => 0,
            'titleFontSize' => 11,
            'titleJustification' => 'left',
            'fontSize' => 7,
            'xPos' => 50,
            'xOrientation' => 'right',
            'width' => 500,
            'rowGap' => 1,
            'cols' => array(
                'DATUM' => array(
                    'justification' => 'right',
                    'width' => 70
                ),
                'BEMERKUNG' => array(
                    'justification' => 'left',
                    'width' => 300
                ),
                'BETRAG' => array(
                    'justification' => 'right',
                    'width' => 75
                ),
                'SALDO' => array(
                    'justification' => 'right',
                    'width' => 75
                )
            )
        ));
        if ($pdf->ezGetCurrentPageNumber() > $page) {
            $pdf->transaction('abort');
            $pdf->ezNewPage();
            $pdf->ezText("ERGEBNISSE HAUSGELDABRECHNUNGEN", 11, array(
                'justification' => 'full'
            ));
            $pdf->ezSetDy(-5);
            $pdf->ezTable($ergebnisse_hga, $cols, "", array(
                'showHeadings' => 1,
                'shaded' => 0,
                'titleFontSize' => 11,
                'titleJustification' => 'left',
                'fontSize' => 7,
                'xPos' => 50,
                'xOrientation' => 'right',
                'width' => 500,
                'rowGap' => 1,
                'cols' => array(
                    'DATUM' => array(
                        'justification' => 'right',
                        'width' => 70
                    ),
                    'BEMERKUNG' => array(
                        'justification' => 'left',
                        'width' => 300
                    ),
                    'BETRAG' => array(
                        'justification' => 'right',
                        'width' => 75
                    ),
                    'SALDO' => array(
                        'justification' => 'right',
                        'width' => 75
                    )
                )
            ));
        } else {
            $pdf->transaction('commit');
        }
        return $pdf;
    }

    function get_ergebnis_hga_ist($objekt_id, $eigentuemer_id, $jahr, $geldkonto, $buchungskonto)
    {
        $bankAccounts = DB::table('GELD_KONTEN_ZUWEISUNG')
            ->where('KOSTENTRAEGER_TYP', 'Objekt')
            ->where('KOSTENTRAEGER_ID', $objekt_id)
            ->where('AKTUELL', '1')
            ->where('VERWENDUNGSZWECK', 'Hausgeld')
            ->get();

        $builder = DB::table('GELD_KONTO_BUCHUNGEN')
            ->where('KOSTENTRAEGER_TYP', 'Eigentuemer')
            ->where('KOSTENTRAEGER_ID', $eigentuemer_id)
            ->where('AKTUELL', '1')
            ->where('KONTENRAHMEN_KONTO', $buchungskonto)
            ->select(DB::raw('IF(SUM(BETRAG) IS NULL,0,SUM(BETRAG)) AS IST'));

        $builder->where(function ($query) use ($bankAccounts) {
            foreach ($bankAccounts as $bankAccount) {
                $query->orWhere(function ($query) use ($bankAccount) {
                    $query->where('DATUM', '>=', $bankAccount['VON'])
                        ->where('DATUM', '<=', $bankAccount['BIS'] ?: '9999-12-31')
                        ->where('GELDKONTO_ID', $bankAccount['KONTO_ID']);
                });
            }
        });

        $result = $builder->get();

        if (!empty($result)) {
            $row = $result[0];
            return $row['IST'];
        }
        return 0;
    }

    function get_ergebnisse_hga_soll($kos_id_soll, $jahr, $buchungskonto)
    {
        $result = DB::select("
SELECT BETRAG AS SOLL, KOSTENKAT AS HGA
FROM WEG_WG_DEF 
WHERE KOS_TYP LIKE 'Einheit' 
	&& KOS_ID = $kos_id_soll 
	&& E_KONTO = $buchungskonto
	&& AKTUELL = '1' 
	&& DATE_FORMAT(ANFANG, '%Y') <= '$jahr'
ORDER BY HGA;");
        if (!empty($result)) {
            return $result;
        }
    }

    function form_wplan_neu()
    {
        $f = new formular ();
        $f->erstelle_formular('WP', '');
        $f->fieldset('Neuen Wirtschaftsplan erstellen', 'wp');
        $f->text_feld('Wirtschaftsjahr', 'wjahr', date("Y") + 1, '10', 'wjahr', '');
        $o = new objekt ();
        $o->dropdown_objekte('objekt_id', 'objekt_id');
        $f->hidden_feld('option', 'wp_neu_send');
        $f->send_button('wp_send', 'Erstellen');
        $f->fieldset_ende();
        $f->ende_formular();
    }

    function form_wplan_zeile($wp_id)
    {
        $f = new formular ();
        $f->erstelle_formular('WP', '');
        $f->fieldset('Wirtschaftsplan bearbeiten', 'wp');
        if (empty ($wp_id)) {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\InfoMessage("Bitte wählen Sie einen Wirtschaftsplan.")
            );
        }
        $k = new kontenrahmen ();
        $obj_id = session()->get('objekt_id');
        $vorjahr = $this->get_jahr_wp($wp_id) - 1;
        $kostenkonto = "document.getElementById('bkonto').options[document.getElementById('bkonto').selectedIndex].value";
        $js = "onclick=\"get_wp_vorjahr_wert($obj_id,$vorjahr, $kostenkonto, 'vsumme')\"";
        $js1 = "onchange=\"get_wp_vorjahr_wert($obj_id,$vorjahr, $kostenkonto, 'vsumme')\"";
        $k->dropdown_kontorahmenkonten('Buchungskonto wählen', 'bkonto', 'bkonto', 'Objekt', session()->get('objekt_id'), $js1);
        $f->check_box_js('su', '', 'Summe aus Vorjahr anzeigen?', $js, '');
        $f->text_feld('Voraussichtliche Summe', 'vsumme', '', '10', 'vsumme', '');
        $this->dropdown_wp_keys('Aufteilung', 'formel', 'formel');
        $wi = new wirt_e ();
        $wi->dropdown_we('Wirtschaftseinheit', 'wirt_id', 'wirt_id', null);

        $f->hidden_feld('summe_vj', '');
        $f->hidden_feld('option', 'wp_zeile_send');
        $f->send_button('wp_send', 'Eintragen');
        $this->wp_zeilen_anzeigen($wp_id);
        $f->fieldset_ende();
        $f->ende_formular();
    }

    function get_jahr_wp($wp_id)
    {
        $result = DB::select("SELECT JAHR FROM WEG_WPLAN WHERE AKTUELL='1' && PLAN_ID='$wp_id' LIMIT 0,1");
        if (!empty($result)) {
            $row = $result[0];
            return $row ['JAHR'];
        }
    }

    function dropdown_wp_keys($label, $name, $id, $js = null)
    {
        echo "<label for=\"$name\">$label</label><select id=\"$id\" name=\"$name\" size=\"1\" $js>";
        echo "<option value=\"MEA\">MEA</option>";
        echo "<option value=\"QM\">Quadratmeter - M²</option>";
        echo "<option value=\"ME\">Anzahl Einheiten - ME</option>";
        echo "<option value=\"AUFZUG_PROZENT\">% Aufzug</option>";
        echo "<option value=\"WE_PROZENT\">WE-PROZENT</option>";
        echo "</select>";
    }

    function wp_zeilen_anzeigen($wplan_id)
    {
        $arr = $this->wp_zeilen_arr($wplan_id);
        if (!empty($arr)) {
            $k = new kontenrahmen ();
            $kontenrahmen_id = $k->get_kontenrahmen('Objekt', session()->get('objekt_id'));
            // echo "<table>";
            for ($a = 0; $a < count($arr); $a++) {

                $kkonto = $arr [$a] ['KOSTENKONTO'];
                $k->konto_informationen2($kkonto, $kontenrahmen_id);
                $arr [$a] ['GRUPPE_ID'] = $k->gruppe_id;
                $arr [$a] ['GRUPPEN_BEZ'] = $k->konto_gruppen_bezeichnung;
                $arr [$a] ['KONTOART_ID'] = $k->konto_art_id;
                $arr [$a] ['KONTOART_BEZ'] = $k->konto_art_bezeichnung;
                $arr [$a] ['KONTO_BEZ'] = $k->konto_bezeichnung;
            }

            $arr1 = array_sortByIndex($arr, 'GRUPPEN_BEZ', 'KONTOART_ID');
            $arr = $arr1;
            unset ($arr1);

            $temp_g_id = '';
            echo "<table>";
            $summe_gruppe = 0;
            $summe_gruppe_vj = 0;

            for ($a = 0; $a < count($arr); $a++) {
                $gruppe_id = $arr [$a] ['GRUPPE_ID'];
                $gruppen_bez = $arr [$a] ['GRUPPEN_BEZ'];
                $betrag = $arr [$a] ['BETRAG'];
                $betrag_vj = $arr [$a] ['BETRAG_VJ'];
                $dat = $arr [$a] ['DAT'];
                $kkonto = $arr [$a] ['KOSTENKONTO'];
                $kontoart_bez = $arr [$a] ['KONTOART_BEZ'];
                $konto_bez = $arr [$a] ['KONTO_BEZ'];

                $link_loeschen = "<a href='" . route('web::weg::legacy', ['option' => 'wp_zeile_del', 'dat' => $dat]) . "'>Löschen</a>";

                if ($temp_g_id != $gruppe_id) {
                    $temp_g_id = $gruppe_id;
                    if ($summe_gruppe > 0) {
                        echo "<tr><td></td><td><b>SUMME</b></td><td><b>$summe_gruppe</b></td><td><b>$summe_gruppe_vj</b></td><td></td></tr>";
                    }
                    echo "<tr><th colspan=\"2\">$kontoart_bez - $gruppen_bez</th><th>BETRAG</th><th>BETRAG VORJAHR</th><th>OPTION</th></tr>";
                    $summe_gruppe = 0;
                    $summe_gruppe_vj = 0;
                }
                $summe_gruppe += $betrag;
                $summe_gruppe_vj += $betrag_vj;

                $betrag_a = nummer_punkt2komma($betrag);
                $betrag_vj_a = nummer_punkt2komma($betrag_vj);
                echo "<tr><td>$kkonto</td><td>$konto_bez</td><td>$betrag_a</td><td>$betrag_vj_a</td><td>$link_loeschen</td></tr>";
            }

            $summe_gruppe_a = nummer_punkt2komma($summe_gruppe);
            $summe_gruppe_vj_a = nummer_punkt2komma($summe_gruppe_vj);
            echo "<tr><td></td><td><b>SUMME</b></td><td><b>$summe_gruppe_a</b></td><td><b>$summe_gruppe_vj_a</b></td><td></td></tr>";
            echo "</table>";
        }
    }

    function wp_zeilen_arr($wplan_id)
    {
        $result = DB::select("SELECT * FROM WEG_WPLAN_ZEILEN WHERE AKTUELL='1' && WPLAN_ID='$wplan_id' ORDER BY KOSTENKONTO ASC");
        return $result;
    }

    function wp_plan_speichern($wjahr, $objekt_id)
    {
        if (!$this->check_wplan_exists($wjahr, $objekt_id)) {
            $id = last_id2('WEG_WPLAN', 'PLAN_ID') + 1;
            $db_abfrage = "INSERT INTO WEG_WPLAN VALUES (NULL, '$id', '$wjahr', '$objekt_id', '1')";
            DB::insert($db_abfrage);

            $last_dat = DB::getPdo()->lastInsertId();
            protokollieren('WEG_WPLAN', '0', $last_dat);
        } else {
            $o = new objekt ();
            $objekt_name = $o->get_objekt_name($objekt_id);
            throw new \App\Exceptions\MessageException(
                new \App\Messages\ErrorMessage("Wirtschaftsplan $wjahr für $objekt_name existiert bereits!")
            );
        }
    }

    function check_wplan_exists($wjahr, $objekt_id)
    {
        $result = DB::select("SELECT PLAN_ID FROM WEG_WPLAN WHERE AKTUELL='1' && JAHR='$wjahr' && OBJEKT_ID='$objekt_id'");
        return !empty($result);
    }

    function wp_zeile_speichern($wp_id, $kostenkonto, $betrag, $betrag_vj, $formel, $wirt_id)
    {
        $id = last_id2('WEG_WPLAN_ZEILEN', 'ID') + 1;
        $betrag = nummer_komma2punkt($betrag);
        $betrag_vj = nummer_komma2punkt($betrag_vj);
        $db_abfrage = "INSERT INTO WEG_WPLAN_ZEILEN VALUES (NULL, '$id', '$wp_id', '$kostenkonto', '$betrag' , '$betrag_vj' , '$formel', '$wirt_id', '1')";
        DB::insert($db_abfrage);

        $last_dat = DB::getPdo()->lastInsertId();
        protokollieren('WEG_WPLAN_ZEILEN', '0', $last_dat);
    }

    function wp_zeile_loeschen($dat)
    {
        $db_abfrage = "UPDATE WEG_WPLAN_ZEILEN SET AKTUELL='0' WHERE DAT='$dat'";
        DB::update($db_abfrage);
        protokollieren('WEG_WPLAN_ZEILEN', $dat, $dat);
        return true;
    }

    function pdf_wplan($wp_id)
    {
        if (!session()->has('partner_id')) {
            session()->put('partner_id', null);
        }
        $pdf = new Cezpdf ('a4', 'portrait');
        $bpdf = new b_pdf ();
        $bpdf->b_header($pdf, 'Partner', session()->get('partner_id'), 'portrait', 'Helvetica.afm', 5);
        $pdf->ezStopPageNumbers(); // seitennummerirung beenden
        $p = new partners ();
        $p->get_partner_info(session()->get('partner_id'));
        $datum = date("d.m.Y");
        $pdf->addText(480, 697, 8, "$p->partner_ort, $datum");

        $this->pdf_g_wplan($pdf, $wp_id);

        $this->einzel_wp($pdf, $wp_id);

        $pdf->ezNewPage();
        $this->hausgeld_einnahmen_summe_a = nummer_punkt2komma_t($this->hausgeld_einnahmen_summe);
        $summe_jahr = nummer_komma2punkt($this->hausgeld_einnahmen_summe_a) * 12;
        $summe_jahr_a = nummer_punkt2komma_t($summe_jahr);

        $pdf->addText(100, 680, 10, "Summe Hausgeldeinnahmen monatlich: $this->hausgeld_einnahmen_summe_a €");
        $pdf->addText(100, 670, 10, "Summe Hausgeldeinnahmen jährlich: $summe_jahr_a € ");
        $this->summe_kosten_hg_a = nummer_punkt2komma_t($this->summe_kosten_hg);
        $diff = $summe_jahr - $this->summe_kosten_hg;
        $pdf->addText(100, 660, 10, "Summe Kosten jährlich laut WP $this->wp_jahr: $this->summe_kosten_hg_a € ");
        $pdf->addText(100, 650, 10, "Überschuß/Unterdeckung $diff € ");
        $pdf->ezSetY(640);
        $cols = array(
            'EINHEIT' => "EINHEIT",
            'NAME' => "NAME",
            'BETRAG_ALT' => "ALT €",
            'BETRAG' => "BETRAG €",
            'DIFF' => "DIFF €"
        );
        $pdf->ezTable($this->hausgelder_neu, $cols, "", array(
            'showHeadings' => 1,
            'shaded' => 1,
            'shadeCol' => array(
                0.9,
                0.9,
                0.9
            ),
            'titleFontSize' => 8,
            'fontSize' => 8,
            'xPos' => 55,
            'xOrientation' => 'right',
            'width' => 500,
            'cols' => array(
                'BETEILIGUNG_ANT' => array(
                    'justification' => 'right',
                    'width' => 50
                ),
                'BETRAG' => array(
                    'justification' => 'right',
                    'width' => 65
                )
            )
        ));

        ob_end_clean(); // ausgabepuffer leeren
        $pdf->ezStream();
    }

    function pdf_g_wplan(Cezpdf $pdf, $wplan_id)
    { // ALTE VERSION
        $jahr = $this->get_jahr_wp($wplan_id);
        $vorjahr = $jahr - 1;
        $vorjahr1 = $jahr - 2;
        if (session()->has('geldkonto_id')) {
            $geldkonto_id = session()->get('geldkonto_id');
        } else {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\InfoMessage('Bitte wählen Sie ein Geldkonto.')
            );
        }

        $o = new objekt ();
        $objekt_name = $o->get_objekt_name(session()->get('objekt_id'));

        $pdf->setColor(0.6, 0.6, 0.6);
        $pdf->filledRectangle(50, 690, 500, 20);

        $pdf->setColor(0, 0, 0);
        $pdf->ezSetDy(3);
        $pdf->ezText(" GESAMTWIRTSCHAFTSPLAN $jahr / OBJEKT: $objekt_name", 12, array(
            'justification' => 'full'
        ));
        $pdf->setColor(0, 0, 0);

        $pdf->ezSetDy(-6);

        $arr = $this->wp_zeilen_arr($wplan_id);
        if (!empty($arr)) {
            $k = new kontenrahmen ();
            $kontenrahmen_id = $k->get_kontenrahmen('Objekt', session()->get('objekt_id'));
            for ($a = 0; $a < count($arr); $a++) {
                $kkonto = $arr [$a] ['KOSTENKONTO'];
                $formel = $arr [$a] ['FORMEL'];
                if ($formel == null) {
                    $formel = 'MEA';
                }

                $arr [$a] ['FORMEL'] = $formel;

                $wirt_id = $arr [$a] ['WIRT_ID'];
                if ($wirt_id != null) {
                    $wirte = new wirt_e ();
                    $wirte->get_wirt_e_infos($wirt_id);
                    $arr [$a] ['WIRT_ID'] = $wirt_id;
                    $arr [$a] ['WIRT_E'] = $wirte->w_name;
                } else {
                    $arr [$a] ['WIRT_E'] = 'ALLE';
                }

                $k->konto_informationen2($kkonto, $kontenrahmen_id);
                $arr [$a] ['GRUPPE_ID'] = $k->gruppe_id;
                $arr [$a] ['GRUPPEN_BEZ'] = $k->konto_gruppen_bezeichnung;
                $arr [$a] ['KONTOART_ID'] = $k->konto_art_id;
                $arr [$a] ['KONTOART_BEZ'] = $k->konto_art_bezeichnung;
                $arr [$a] ['KONTO_BEZ'] = $k->konto_bezeichnung;
            }

            $arr1 = array_orderBy($arr, 'GRUPPEN_BEZ', SORT_DESC, 'KONTOART_BEZ', SORT_ASC, 'KOSTENKONTO', SORT_ASC);
            $arr = $arr1;
            unset ($arr1);

            $temp_g_id = '';
            $summe_gruppe = 0;
            $summe_gruppe_vj = 0;
            $summe_g = 0;
            $summe_g_vj = 0;

            $zeile_tab = 0;

            $vorjahr2 = $vorjahr1 - 1;

            $zeile_tab++;
            for ($a = 0; $a < count($arr); $a++) {
                $gruppe_id = $arr [$a] ['GRUPPE_ID'];
                $gruppen_bez = $arr [$a] ['GRUPPEN_BEZ'];
                $betrag = $arr [$a] ['BETRAG'];
                $betrag_vj = $arr [$a] ['BETRAG_VJ'];
                $kkonto = $arr [$a] ['KOSTENKONTO'];
                $kontoart_bez = $arr [$a] ['KONTOART_BEZ'];
                $konto_bez = $arr [$a] ['KONTO_BEZ'];

                if ($temp_g_id != $gruppe_id) {
                    if (is_array($tab_arr)) {
                        $tab_arr [$zeile_tab] ['KONTO_BEZ'] = '<b>Zwischensumme</b>';
                        $summe_gruppe_a = nummer_punkt2komma_t($summe_gruppe);

                        $tab_arr [$zeile_tab] ['BETRAG'] = "<b>$summe_gruppe_a</b>";
                        $summe_gruppe = 0;
                        $summe_gruppe_vj = 0;
                        $zeile_tab++;
                    }
                }

                $temp_g_id = $gruppe_id;

                $tab_arr [$zeile_tab] ['WIRT_ID'] = $arr [$a] ['WIRT_ID'];
                $tab_arr [$zeile_tab] ['WIRT_E'] = $arr [$a] ['WIRT_E'];
                $tab_arr [$zeile_tab] ['FORMEL'] = $arr [$a] ['FORMEL'];;

                $tab_arr [$zeile_tab] ['KONTO'] = $kkonto;
                $tab_arr [$zeile_tab] ['GRUPPEN_BEZ'] = $gruppen_bez;
                $tab_arr [$zeile_tab] ['KONTO_BEZ'] = $konto_bez;
                $tab_arr [$zeile_tab] ['KONTOART_BEZ'] = $kontoart_bez;

                $bb = new buchen ();
                $tab_arr [$zeile_tab] ['BETRAG_VJ'] = $bb->summe_kontobuchungen_jahr($geldkonto_id, $kkonto, $vorjahr);
                $tab_arr [$zeile_tab] ['BETRAG_VJ1'] = $bb->summe_kontobuchungen_jahr($geldkonto_id, $kkonto, $vorjahr1);

                $tab_arr [$zeile_tab] ['BETRAG'] = nummer_punkt2komma_t($betrag);

                $summe_gruppe = $summe_gruppe + $betrag;
                $summe_gruppe_vj = $summe_gruppe_vj + $betrag_vj;
                $summe_g = $summe_g + $betrag;
                $summe_g_vj = $summe_g_vj + $betrag_vj;
                $zeile_tab++;
            } // end for

            $tab_arr [$zeile_tab] ['KONTO_BEZ'] = '<b>Zwischensumme</b>';
            $summe_gruppe_a = nummer_punkt2komma_t($summe_gruppe);
            $summe_gruppe_vj_a = nummer_punkt2komma_t($summe_gruppe_vj);
            $tab_arr [$zeile_tab] ['BETRAG_VJ'] = "<b>$summe_gruppe_vj_a</b>";
            $tab_arr [$zeile_tab] ['BETRAG'] = "<b>$summe_gruppe_a</b>";
            $zeile_tab++;

            $zeile_tab++;
            $tab_arr [$zeile_tab] ['KONTO_BEZ'] = '<b>Gesamtsumme</b>';
            $summe_g += $energie_alle;
            $tab_arr [$zeile_tab] ['BETRAG_VJ'] = "<b>" . nummer_punkt2komma_t($summe_g_vj) . "</b>";
            $tab_arr [$zeile_tab] ['BETRAG'] = "<b>" . nummer_punkt2komma_t($summe_g) . "</b>";
            $this->summe_kosten_hg = $summe_g;
            $cols = array(
                'KONTO' => "Konto",
                'KONTO_BEZ' => "Bezeichnung",
                'GRUPPEN_BEZ' => "Kostenart",
                'WIRT_E' => "Aufteilung",
                'FORMEL' => "SCHL.",
                'BETRAG' => "Betrag (€)"
            );
            $pdf->ezSetDy(-6);
            $pdf->ezTable($tab_arr, $cols, "", array(
                'showHeadings' => 1,
                'shaded' => 1,
                'shadeCol' => array(
                    0.9,
                    0.9,
                    0.9
                ),
                'titleFontSize' => 8,
                'fontSize' => 8,
                'xPos' => 50,
                'xOrientation' => 'right',
                'width' => 500,
                'cols' => array(
                    'BETRAG_VJ' => array(
                        'justification' => 'right',
                        'width' => 60
                    ),
                    'KONTO' => array(
                        'justification' => 'right',
                        'width' => 35
                    ),
                    'BETRAG' => array(
                        'justification' => 'right',
                        'width' => 60
                    ),
                    'WIRT_E' => array(
                        'width' => 80
                    ),
                    'GRUPPEN_BEZ' => array(
                        'width' => 120
                    )
                )
            ));
            $pdf->ezSetDy(-10);
            $datum = date("d.m.Y");
            $pdf->ezText("Druckdatum: $datum");
        }


        return $pdf;
    }

    function einzel_wp(Cezpdf $pdf, $wp_id)
    {
        set_time_limit(0);

        $this->get_wplan_infos($wp_id);
        $einheiten_arr = $this->einheiten_weg_tabelle_arr($this->wp_objekt_id);
        $anz_einheiten = count($einheiten_arr);

        $wp_zeilen = $this->wplan_gesamt_tab_arr($wp_id);

        $wtab_arr = $wp_zeilen;
        $anz_tab = count($wtab_arr);

        unset ($wp_zeilen);

        /* Alle Einheiten durchlaufen */
        for ($a = 0; $a < $anz_einheiten; $a++) {
            $einheit_id = $einheiten_arr [$a] ['EINHEIT_ID'];
            $e = new einheit ();
            $e->get_einheit_info($einheit_id);
            /* Neue Seite für jede Einheit */
            $pdf->ezNewPage();
            $pdf->setColor(0.6, 0.6, 0.6);
            $pdf->filledRectangle(50, 690, 500, 20);

            $pdf->setColor(0, 0, 0);
            $pdf->ezSetDy(3);
            $pdf->ezText(" EINZELWIRTSCHAFTSPLAN $this->wp_jahr / OBJEKT: $this->wp_objekt_name / EINHEIT: $e->einheit_kurzname", 12, array(
                'justification' => 'full'
            ));
            $pdf->setColor(0, 0, 0);

            /*
			 * Aktuellen bzw. letzten Eigentümer einer Einheit holen
			 * ohne die davor, weil nur letzter den Wirtschaftsplan bekommt
			 */

            $this->get_last_eigentuemer_id($einheit_id);
            $eig_id = $this->eigentuemer_id;

            $this->get_eigentumer_id_infos3($eig_id);
            $pdf->ezSetDy(-20);

            $pdf->ezText("$this->empf_namen_u");

            $pdf->ezText("$this->haus_strasse $this->haus_nummer");
            $pdf->ezText("$this->haus_plz $this->haus_stadt");

            $d = new detail ();
            $anteile_g = $d->finde_detail_inhalt('Objekt', $e->objekt_id, 'Gesamtanteile');
            $anteile_g_a = nummer_punkt2komma_t($anteile_g);
            $pdf->addText(405, 670, 8, "Einheiten:");
            $pdf->addText(465, 670, 8, "$anz_einheiten");
            $pdf->addText(405, 660, 8, "Einheit:");
            $pdf->addText(465, 660, 8, "$e->einheit_kurzname");
            $pdf->addText(405, 650, 8, "Gesamtanteile:");
            $pdf->addText(465, 650, 8, "$anteile_g_a");
            $this->einheit_anteile = $d->finde_detail_inhalt('Einheit', $einheit_id, 'WEG-Anteile');
            $pdf->addText(405, 640, 8, "Ihre MEA:");
            $this->einheit_anteile_a = nummer_punkt2komma_t($this->einheit_anteile);
            $pdf->addText(465, 640, 8, "$this->einheit_anteile_a");

            $e->einheit_qm_a = nummer_punkt2komma_t($e->einheit_qm);
            $pdf->addText(405, 630, 8, "Ihre Fläche:");
            $pdf->addText(465, 630, 8, "$e->einheit_qm_a m²");
            $oo = new objekt ();
            $qm_gesamt = $oo->get_qm_gesamt($e->objekt_id);
            $qm_gesamt_a = nummer_punkt2komma_t($qm_gesamt);
            $pdf->addText(405, 620, 8, "Gesamtfläche:");
            $pdf->addText(465, 620, 8, "$qm_gesamt_a m²");

            $jahres_beteiligung = 0;
            $gruppen_summe = 0;
            for ($c = 0; $c < $anz_tab; $c++) {
                $betrag = strip_tags($wtab_arr [$c] ['BETRAG']);
                $kkonto = $wtab_arr [$c] ['KONTO'];
                if (isset ($wtab_arr [$c] ['FORMEL'])) {
                    $formel = $wtab_arr [$c] ['FORMEL'];
                }

                // ####NEU 2014#####
                $pos_wirt_id = $wtab_arr [$c] ['WIRT_ID'];
                $wtab_arr [$c] ['WIRT_ID'] = $pos_wirt_id;
                // ##WENN KEINE BESTIMMTE WIRTE ZUR AUFTEILUNG ANGEGEBEN*/

                if (!isset ($pos_wirt_id) or empty ($pos_wirt_id)) {

                    if (empty ($formel)) {
                        $wtab_arr [$c] ['FORMEL'] = 'MEA';
                        $wtab_arr [$c] ['AUFTEILEN_G'] = "$anteile_g MEA";
                        $beteiligung_ant = (nummer_komma2punkt($betrag) / $anteile_g) * nummer_komma2punkt($this->einheit_anteile);
                        $wtab_arr [$c] ['AUFTEILEN_T'] = "$this->einheit_anteile MEA";
                    }
                    if ($formel == 'MEA') {
                        $wtab_arr [$c] ['AUFTEILEN_G'] = "$anteile_g MEA";
                        $beteiligung_ant = (nummer_komma2punkt($betrag) / $anteile_g) * nummer_komma2punkt($this->einheit_anteile);
                        $wtab_arr [$c] ['AUFTEILEN_T'] = "$this->einheit_anteile MEA";
                    }

                    if ($formel == 'ME') {
                        $wtab_arr [$c] ['AUFTEILEN_G'] = "$anz_einheiten WE";
                        $beteiligung_ant = nummer_komma2punkt($betrag) / $anz_einheiten;
                        $wtab_arr [$c] ['AUFTEILEN_T'] = "1 WE";
                    }

                    if ($formel == 'QM') {
                        $wtab_arr [$c] ['AUFTEILEN_G'] = "$qm_gesamt m²";
                        $wtab_arr [$c] ['AUFTEILEN_T'] = "$e->einheit_qm m²";
                        $beteiligung_ant = nummer_komma2punkt($betrag) / $qm_gesamt * $e->einheit_qm;
                    }

                    if ($formel == 'AUFZUG_PROZENT') {
                        /* Aufzug nach Prozent */
                        $de = new detail ();
                        $aufzug_prozent = $de->finde_detail_inhalt('Einheit', $einheit_id, 'WEG-Aufzugprozent');
                        $wtab_arr [$c] ['AUFTEILEN_G'] = "100%";
                        $wtab_arr [$c] ['AUFTEILEN_T'] = "$aufzug_prozent%";
                        $beteiligung_ant = nummer_komma2punkt($betrag) / 100 * $aufzug_prozent;
                    }

                    if ($formel == 'WE-PROZENT') {
                        /* Nach Prozenten der Wohnung */
                        $de = new detail ();
                        $we_prozent = $de->finde_detail_inhalt('Einheit', $einheit_id, 'WE-Prozent');
                        $wtab_arr [$c] ['AUFTEILEN_G'] = "100%";
                        $wtab_arr [$c] ['AUFTEILEN_T'] = "$we_prozent%";
                        $beteiligung_ant = nummer_komma2punkt($betrag) / 100 * $we_prozent;
                    }
                } else {
                    // ##WENN BESTIMMTE WIRTE ZUR AUFTEILUNG ANGEGEBEN*/

                    $wirte = new wirt_e ();
                    if (!$wirte->check_einheit_in_we($einheit_id, $pos_wirt_id)) {
                        $beteiligung_ant = '0.00';
                        $wtab_arr [$c] ['AUFTEILEN_T'] = '';
                    } else {
                        $wirte->get_wirt_e_infos($pos_wirt_id);
                        $wtab_arr [$c] ['AUFTEILEN'] = $wirte->w_name;

                        /* Aufteilung nur an bestimmte pos WIRT */
                        if (!$formel) {
                            $wtab_arr [$c] ['FORMEL'] = 'MEA';
                            $wtab_arr [$c] ['AUFTEILEN_G'] = "$wirte->g_mea MEA";
                            $beteiligung_ant = (nummer_komma2punkt($betrag) / $wirte->g_mea) * nummer_komma2punkt($this->einheit_anteile);
                            $wtab_arr [$c] ['AUFTEILEN_T'] = "$this->einheit_anteile MEA";
                        }
                        if ($formel == 'MEA') {
                            $wtab_arr [$c] ['AUFTEILEN_G'] = "$wirte->g_mea MEA";
                            $beteiligung_ant = (nummer_komma2punkt($betrag) / $wirte->g_mea) * nummer_komma2punkt($this->einheit_anteile);
                            $wtab_arr [$c] ['AUFTEILEN_T'] = "$this->einheit_anteile MEA";
                        }

                        if ($formel == 'ME') {
                            $wtab_arr [$c] ['AUFTEILEN_G'] = "$wirte->g_anzahl_einheiten WE";
                            $wtab_arr [$c] ['AUFTEILEN_T'] = "1 WE";
                            $beteiligung_ant = nummer_komma2punkt($betrag) / $wirte->g_anzahl_einheiten;
                        }

                        if ($formel == 'QM') {
                            $wtab_arr [$c] ['AUFTEILEN_G'] = "$wirte->g_einheit_qm m²";
                            $beteiligung_ant = nummer_komma2punkt($betrag) / $wirte->g_einheit_qm * $e->einheit_qm;
                            $wtab_arr [$c] ['AUFTEILEN_T'] = "$e->einheit_qm m²";
                        }

                        if ($formel == 'AUFZUG_PROZENT') {
                            /* Aufzug nach Prozent */
                            $de = new detail ();
                            $aufzug_prozent = $de->finde_detail_inhalt('Einheit', $einheit_id, 'WEG-Aufzugprozent');
                            $wtab_arr [$c] ['AUFTEILEN_G'] = "100%";
                            $wtab_arr [$c] ['AUFTEILEN_T'] = "$aufzug_prozent%";
                            $beteiligung_ant = nummer_komma2punkt($betrag) / 100 * $aufzug_prozent;
                        }

                        if ($formel == 'WE-PROZENT') {
                            /* Nach Prozenten der Wohnung */
                            $de = new detail ();
                            $we_prozent = $de->finde_detail_inhalt('Einheit', $einheit_id, 'WE-Prozent');
                            $wtab_arr [$c] ['AUFTEILEN_G'] = "100%";
                            $wtab_arr [$c] ['AUFTEILEN_T'] = "$we_prozent%";
                            $beteiligung_ant = nummer_komma2punkt($betrag) / 100 * $we_prozent;
                        }
                    }
                }

                if (!empty ($kkonto)) {
                    $wtab_arr [$c] ['BETEILIGUNG_ANT'] = nummer_punkt2komma_t($beteiligung_ant);
                    $jahres_beteiligung = $jahres_beteiligung + nummer_komma2punkt(nummer_punkt2komma($beteiligung_ant));
                    $gruppen_summe += $beteiligung_ant;
                    if ($a == 0) {
                        $wtab_arr [$c] ['BETRAG'] = nummer_punkt2komma_t($betrag);
                    }
                } else {
                    if (strip_tags($wtab_arr [$c] ['KONTOART_BEZ']) == 'Zwischensumme') {
                        $gruppen_summe_a = nummer_punkt2komma_t($gruppen_summe);
                        $wtab_arr [$c] ['KONTO_BEZ'] = "<b>Zwischensumme</b>";
                        if ($a == 0) {
                            $wtab_arr [$c] ['BETRAG'] = '<b>' . nummer_punkt2komma_t($betrag) . '</b>';
                        }
                        $wtab_arr [$c] ['BETEILIGUNG_ANT'] = "<b>$gruppen_summe_a</b>";
                        $gruppen_summe = 0;
                        $wtab_arr [$c] ['AUFTEILEN_G'] = "";
                        $wtab_arr [$c] ['AUFTEILEN_T'] = "";
                    }
                    if (strip_tags($wtab_arr [$c] ['KONTOART_BEZ']) == 'SALDO') {
                        $jahres_beteiligung_a = nummer_punkt2komma_t($jahres_beteiligung);
                        $wtab_arr [$c] ['KONTO_BEZ'] = "<b>Gesamtsumme</b>";
                        if ($a == 0) {
                            $wtab_arr [$c] ['BETRAG'] = '<b>' . nummer_punkt2komma_t($betrag) . '</b>';
                        }
                        $wtab_arr [$c] ['BETEILIGUNG_ANT'] = "<b>$jahres_beteiligung_a</b>";
                        $wtab_arr [$c] ['AUFTEILEN_G'] = "";
                        $wtab_arr [$c] ['AUFTEILEN_T'] = "";
                    }
                }

                $beteiligung_ant = 0;
            }

            $hausgeld_neu_genau = nummer_punkt2komma_t($jahres_beteiligung / 12);
            $hausgeld_neu = round(($jahres_beteiligung / 12), 0, PHP_ROUND_HALF_DOWN);
            $hausgeld_neu_a = nummer_punkt2komma_t($hausgeld_neu);
            $wtab_arr [$c + 4] ['KONTO_BEZ'] = "<b>Hausgeld $this->wp_jahr\nGerundet auf vollen Euro-Betrag</b>";
            $wtab_arr [$c + 4] ['BETEILIGUNG_ANT'] = "<b>$hausgeld_neu_genau\n$hausgeld_neu_a</b>";

            $monat = sprintf('%02d', date("m"));
            $hausgeld_aktuell_a = nummer_punkt2komma_t($this->get_sume_hausgeld('Einheit', $einheit_id, $monat, $this->wp_jahr) * -1);
            $wtab_arr [$c + 3] ['KONTO_BEZ'] = "Hausgeld bisher";
            $wtab_arr [$c + 3] ['BETEILIGUNG_ANT'] = "$hausgeld_aktuell_a";

            $this->hausgeld_einnahmen_summe += $hausgeld_neu;
            $this->hausgelder_neu [$a] ['EINHEIT'] = "$e->einheit_kurzname";
            $this->hausgelder_neu [$a] ['NAME'] = "$this->empf_namen";
            $this->hausgelder_neu [$a] ['BETRAG_ALT'] = "$hausgeld_aktuell_a €";
            $this->hausgelder_neu [$a] ['BETRAG'] = "$hausgeld_neu_a €";

            $this->hausgelder_neu [$a] ['DIFF'] = nummer_punkt2komma_t($hausgeld_neu - ($this->get_sume_hausgeld('Einheit', $einheit_id, $monat, $this->wp_jahr) * -1));
            $this->hausgelder_neu [$a] ['DIFF2M'] = nummer_punkt2komma_t(($hausgeld_neu - ($this->get_sume_hausgeld('Einheit', $einheit_id, $monat, $this->wp_jahr) * -1)) * 2);
            $this->hausgelder_neu [$a] ['SE'] = nummer_punkt2komma($hausgeld_neu + ($hausgeld_neu - ($this->get_sume_hausgeld('Einheit', $einheit_id, $monat, $this->wp_jahr) * -1)) * 2);

            //
            $cols = array(
                'KONTO' => "Konto",
                'KONTO_BEZ' => "Bezeichnung",
                'BETRAG' => "Betrag (€)",
                'AUFTEILEN' => "",
                'AUFTEILEN_G' => "",
                'AUFTEILEN_T' => "",
                'BETEILIGUNG_ANT' => "Ihr Anteil (€)"
            );
            $pdf->ezSetDy(-10);
            $pdf->ezTable($wtab_arr, $cols, "", array(
                'showHeadings' => 1,
                'shaded' => 1,
                'shadeCol' => array(
                    0.9,
                    0.9,
                    0.9
                ),
                'titleFontSize' => 8,
                'fontSize' => 7,
                'xPos' => 50,
                'xOrientation' => 'right',
                'width' => 500,
                'cols' => array(
                    'BETEILIGUNG_ANT' => array(
                        'justification' => 'right',
                        'width' => 50
                    ),
                    'BETRAG' => array(
                        'justification' => 'right',
                        'width' => 65
                    )
                )
            ));
            $pdf->ezSetDy(-5);
            $datum = date("d.m.Y");
            $pdf->ezText("Druckdatum: $datum");
            $pdf->ezSetDy(-5);
            $pdf->ezText("Verteiler: MEA - Nach Miteingentumsanteilen          Formel: Betrag/$anteile_g_a*$this->einheit_anteile_a");
            $pdf->ezText("Verteiler: ME -  Nach Anzahl der WEG-Einheiten     Formel: Betrag/$anz_einheiten");
            $pdf->ezText("Verteiler: QM -  Nach Quadratmeter                         Formel: Betrag/$qm_gesamt_a*$e->einheit_qm_a");

            unset ($this->empf_namen_u);
        }
    }

    function get_wplan_infos($wp_id)
    {
        unset ($this->wp_jahr);
        unset ($this->wp_objekt_id);
        $result = DB::select("SELECT * FROM WEG_WPLAN WHERE PLAN_ID='$wp_id' && AKTUELL='1' ORDER BY DAT DESC LIMIT 0,1");
        if (!empty($result)) {
            $row = $result[0];
            $this->wp_jahr = $row ['JAHR'];
            $this->wp_objekt_id = $row ['OBJEKT_ID'];
            $o = new objekt ();
            $this->wp_objekt_name = $o->get_objekt_name($this->wp_objekt_id);
        }
    }

    function wplan_gesamt_tab_arr($wplan_id)
    {
        $this->get_wplan_infos($wplan_id);

        $o = new objekt ();
        $arr = $this->wp_zeilen_arr($wplan_id);
        if (!empty($arr)) {
            $k = new kontenrahmen ();
            $kontenrahmen_id = $k->get_kontenrahmen('Objekt', $this->wp_objekt_id);
            for ($a = 0; $a < count($arr); $a++) {
                $kkonto = $arr [$a] ['KOSTENKONTO'];
                $k->konto_informationen2($kkonto, $kontenrahmen_id);
                $arr [$a] ['GRUPPE_ID'] = $k->gruppe_id;
                $arr [$a] ['GRUPPEN_BEZ'] = $k->konto_gruppen_bezeichnung;
                $arr [$a] ['KONTOART_ID'] = $k->konto_art_id;
                $arr [$a] ['KONTOART_BEZ'] = $k->konto_art_bezeichnung;
                $arr [$a] ['KONTO_BEZ'] = $k->konto_bezeichnung;
            }

            $arr1 = array_orderBy($arr, 'GRUPPEN_BEZ', SORT_DESC, 'KONTOART_BEZ', SORT_ASC, 'KOSTENKONTO', SORT_ASC);
            $arr = $arr1;
            unset ($arr1);

            $temp_g_id = '';
            $summe_gruppe = 0;
            $summe_gruppe_vj = 0;
            $summe_g = 0;
            $summe_g_vj = 0;

            $zeile_tab = 0;
            for ($a = 0; $a < count($arr); $a++) {
                $gruppe_id = $arr [$a] ['GRUPPE_ID'];
                $gruppen_bez = $arr [$a] ['GRUPPEN_BEZ'];
                $betrag = $arr [$a] ['BETRAG'];
                $betrag_vj = $arr [$a] ['BETRAG_VJ'];
                $kkonto = $arr [$a] ['KOSTENKONTO'];
                $kontoart_bez = $arr [$a] ['KONTOART_BEZ'];
                $konto_bez = $arr [$a] ['KONTO_BEZ'];

                if ($temp_g_id != $gruppe_id) {
                    if (isset ($tab_arr) && is_array($tab_arr)) {
                        $tab_arr [$zeile_tab] ['KONTOART_BEZ'] = '<b>Zwischensumme</b>';
                        $summe_gruppe_a = nummer_punkt2komma($summe_gruppe);
                        $summe_gruppe_vj_a = nummer_punkt2komma($summe_gruppe_vj);
                        $tab_arr [$zeile_tab] ['BETRAG_VJ'] = "<b>$summe_gruppe_vj_a</b>";
                        $tab_arr [$zeile_tab] ['BETRAG'] = "<b>$summe_gruppe_a</b>";
                        $summe_gruppe = 0;
                        $summe_gruppe_vj = 0;
                        $zeile_tab++;
                    }
                }

                $temp_g_id = $gruppe_id;
                $tab_arr [$zeile_tab] ['KONTO'] = $kkonto;
                $tab_arr [$zeile_tab] ['GRUPPEN_BEZ'] = $gruppen_bez;
                $tab_arr [$zeile_tab] ['KONTO_BEZ'] = $konto_bez;
                $tab_arr [$zeile_tab] ['KONTOART_BEZ'] = $kontoart_bez;
                $tab_arr [$zeile_tab] ['BETRAG_VJ'] = nummer_punkt2komma($betrag_vj);
                $tab_arr [$zeile_tab] ['BETRAG'] = nummer_punkt2komma($betrag);
                $tab_arr [$zeile_tab] ['FORMEL'] = $arr [$a] ['FORMEL'];;
                $tab_arr [$zeile_tab] ['WIRT_ID'] = $arr [$a] ['WIRT_ID'];;
                $summe_gruppe = $summe_gruppe + $betrag;
                $summe_gruppe_vj = $summe_gruppe_vj + $betrag_vj;
                $summe_g = $summe_g + $betrag;
                $summe_g_vj = $summe_g_vj + $betrag_vj;
                $zeile_tab++;
            } // end for
            $summe_gruppe_a = nummer_punkt2komma($summe_gruppe);
            $summe_gruppe_vj_a = nummer_punkt2komma($summe_gruppe_vj);
            $tab_arr [$zeile_tab] ['KONTOART_BEZ'] = '<b>Zwischensumme</b>';
            $tab_arr [$zeile_tab] ['BETRAG_VJ'] = "<b>$summe_gruppe_vj_a</b>";
            $tab_arr [$zeile_tab] ['BETRAG'] = "<b>$summe_gruppe_a</b>";

            $zeile_tab++;
            $tab_arr [$zeile_tab] ['KONTOART_BEZ'] = '<b>SALDO</b>';
            $tab_arr [$zeile_tab] ['BETRAG_VJ'] = "<b>" . nummer_punkt2komma($summe_g_vj) . "</b>";
            $tab_arr [$zeile_tab] ['BETRAG'] = "<b>" . nummer_punkt2komma($summe_g) . "</b>";
        }
        return $tab_arr;
    }

    function get_last_eigentuemer_id($einheit_id)
    {
        $arr = $this->get_last_eigentuemer_arr($einheit_id);
        $anz = count($arr);
        if (!$anz) {
            // $this->eigentuemer_name[0]['Nachname'] = 'unbekannt';
            $this->eigentuemer_id = 'unbekannt';
        } else {
            $this->eigentuemer_id = $arr ['ID'];
        }
    }

    function get_eigentumer_id_infos3($e_id)
    {
        $this->eigentuemer_id = $e_id;
        $this->et_code = $e_id;
        if ($this->et_code < 1000) {
            $this->et_code = substr($this->et_code, 1) . $this->et_code . '1';
        }
        if (strlen($this->et_code) > 4) {
            $abs = strlen($this->et_code) - 4;
            $this->et_code = substr($this->et_code, $abs);
        }
        if (isset ($this->GLAEUBIGER_ID)) {
            unset ($this->GLAEUBIGER_ID);
        }

        $einheit_id = $this->get_einheit_id_from_eigentuemer($e_id);
        $this->einheit_id = $einheit_id;
        $e = new einheit ();
        $e->get_einheit_info($einheit_id);
        $this->einheit_kurzname = $e->einheit_kurzname;
        $this->einheit_lage = $e->einheit_lage;
        $this->einheit_qm = $e->einheit_qm;
        $this->einheit_qm_d = $e->einheit_qm_d;
        $det = new detail ();

        $versprochene_miete = $det->finde_detail_inhalt('Einheit', $einheit_id, 'WEG-KaltmieteINS');
        if ($versprochene_miete) {
            $this->versprochene_miete = nummer_komma2punkt($versprochene_miete);
        }

        $this->einheit_qm_weg_d = $det->finde_detail_inhalt('Einheit', $einheit_id, 'WEG-Fläche'); // kommt als Kommazahl
        if ($this->einheit_qm_weg_d) {
            $this->einheit_qm_weg = nummer_komma2punkt($this->einheit_qm_weg_d);
        } else {
            $this->einheit_qm_weg = $this->einheit_qm;
            $this->einheit_qm_weg_d = $this->einheit_qm_d;
        }

        $this->weg_anteile = $det->finde_detail_inhalt('Einheit', $einheit_id, 'WEG-Anteile');


        $this->haus_strasse = $e->haus_strasse;
        $this->haus_nummer = $e->haus_nummer;
        $this->haus_plz = $e->haus_plz;
        $this->haus_stadt = $e->haus_stadt;
        $this->einheit_kurzname = $e->einheit_kurzname;
        $this->objekt_id = $e->objekt_id;
        $this->post_anschrift_haus = "$this->haus_strasse $this->haus_nummer\n<b>$this->haus_plz $this->haus_stadt</b>";
        $this->personen_id_arr = $this->get_person_id_eigentuemer_arr($e_id);
        $this->anz_personen = count($this->personen_id_arr);
        for ($a = 0; $a < $this->anz_personen; $a++) {
            $person_id = $this->personen_id_arr [$a] ['PERSON_ID'];
            $p = new personen ();
            $p->get_person_infos($person_id);
            $this->personen_id_arr [$a] ['geschlecht'] = $p->geschlecht;
            if ($p->geschlecht == 'weiblich') {
                if ($this->anz_personen > 1) {
                    $this->personen_id_arr [$a] ['anrede_p'] = "Frau $p->person_vorname $p->person_nachname";
                    $this->personen_id_arr [$a] ['anrede_t'] = "Sehr geehrte Frau $p->person_nachname,";
                } else {
                    $this->personen_id_arr [$a] ['anrede_p'] = "Frau\n$p->person_vorname $p->person_nachname";
                    $this->personen_id_arr [$a] ['anrede_t'] = "Sehr geehrte Frau $p->person_nachname,";
                }
            }
            if ($p->geschlecht == 'männlich') {
                if ($this->anz_personen > 1) {
                    $this->personen_id_arr [$a] ['anrede_p'] = "Herr $p->person_vorname $p->person_nachname";
                    $this->personen_id_arr [$a] ['anrede_t'] = "Sehr geehrter Herr $p->person_nachname,";
                } else {
                    $this->personen_id_arr [$a] ['anrede_p'] = "Herr\n$p->person_vorname $p->person_nachname";
                    $this->personen_id_arr [$a] ['anrede_t'] = "Sehr geehrter Herr $p->person_nachname,";
                }
            }
            if (empty ($p->geschlecht)) {

                $this->personen_id_arr [$a] ['anrede_p'] = "$p->person_vorname $p->person_nachname";
                $this->personen_id_arr [$a] ['anrede_t'] = "geehrte Damen und Herren,";
            }

            if (isset ($p->anschrift)) {
                $this->anschriften [] = $p->anschrift;
                $this->anschriften_p [$person_id] = br2n($p->anschrift);
            }
            if (isset ($p->zustellanschrift)) {
                $this->zustellanschriften [] = br2n($p->zustellanschrift);
                $this->zustellanschriften_p [$person_id] = $p->zustellanschrift;
            }
        }
        /* Sortieren nach Geschlecht */
        $this->personen_id_arr1 = array_sortByIndex($this->personen_id_arr, 'geschlecht', SORT_DESC);
        unset ($this->personen_id_arr);
        $this->anrede_brief = '';

        /* Anredetext */
        for ($a = 0; $a < $this->anz_personen; $a++) {
            if ($a < $this->anz_personen - 1) {
                $this->anrede_brief .= $this->personen_id_arr1 [$a] ['anrede_t'] . "\n";
                $this->empf_namen_u .= $this->personen_id_arr1 [$a] ['anrede_p'] . "\n";
            } else {
                /* Kleinbuchstaben zweite Zeile sehr... */
                if ($this->anz_personen > 1) {
                    $this->anrede_brief .= lcfirst($this->personen_id_arr1 [$a] ['anrede_t'] . "\n");
                    $this->empf_namen_u .= $this->personen_id_arr1 [$a] ['anrede_p'] . "";
                } else {
                    $this->anrede_brief .= $this->personen_id_arr1 [$a] ['anrede_t'] . "\n";
                    $this->empf_namen_u .= $this->personen_id_arr1 [$a] ['anrede_p'] . "";
                }
            }
        }
        /* Anschriften zählen */
        if (isset ($this->zustellanschriften)) {
            $this->anz_zustell = count($this->zustellanschriften);
        }

        if (isset ($this->anschriften)) {
            $this->anz_anschrift = count($this->anschriften);
        }

        /* Postanschrift kreiren */
        if (!isset ($this->anz_anschrift) && !isset ($this->anz_zustell)) {
            $this->post_anschrift = "$this->empf_namen_u\n$this->post_anschrift_haus";
        }

        if (isset ($this->anz_anschrift) && !isset ($this->anz_zustell)) {
            $this->post_anschrift = br2n($this->anschriften [0]);
        }

        if (!isset ($this->anz_anschrift) && isset ($this->anz_zustell)) {
            $this->post_anschrift = br2n($this->zustellanschriften [0]);
        }

        if (isset ($this->anz_anschrift) && isset ($this->anz_zustell)) {
            $this->post_anschrift = br2n($this->zustellanschriften [0]);
        }

        $this->empf_namen = bereinige_string($this->empf_namen_u);
        $gg = new geldkonto_info ();
        $gg->geld_konto_ermitteln('Objekt', $this->objekt_id, null, 'Hausgeld');
        $this->geldkonto_id = $gg->geldkonto_id;
        $this->OBJ_KONTONUMMER = $gg->kontonummer;
        $this->OBJ_BLZ = $gg->blz;
        $this->OBJ_BEGUENSTIGTER = $gg->beguenstigter;
        $this->OBJ_GELD_INSTITUT = $gg->geld_institut;

        $this->OBJ_BIC = $gg->BIC;
        $this->OBJ_IBAN = $gg->IBAN;
        $this->OBJ_IBAN1 = $gg->IBAN1;

        $this->SEPA_MANDAT = 'WEG-ET' . $e_id;
        $this->GLAEUBIGER_ID = '';
        $sep = new sepa ();
        if ($sep->check_m_ref($this->SEPA_MANDAT)) {
            $this->SEPA_MANDAT_AKTIV = 1;
            $sep->get_mandat_infos_mref($this->SEPA_MANDAT);
            $this->MAND = $sep->mand;
            $this->BIC = $sep->mand->BIC;
            $this->IBAN = $sep->mand->IBAN;
            $this->NAME = $sep->mand->NAME;
            $this->GLAEUBIGER_ID = $sep->mand->GLAEUBIGER_ID;
        } else {
            $this->SEPA_MANDAT_AKTIV = 0;
            $this->IBAN = 'FEHLT';
            $this->BIC = 'FEHLT';
            $this->NAME = 'FEHLT';

            $d = new detail ();
            $glaeubiger_id = $d->finde_detail_inhalt('GELD_KONTEN', $gg->geldkonto_id, 'GLAEUBIGER_ID');
            if ($glaeubiger_id == false) {
                $this->GLAEUBIGER_ID = "<b>Zum Geldkonto wurde die Gläubiger ID nicht gespeichert, siehe DETAILS vom GK</b>";
            } else {
                $this->GLAEUBIGER_ID = $glaeubiger_id;
            }
        }
    }

    function get_sume_hausgeld($kos_typ, $kos_id, $monat, $jahr)
    {
        $result = DB::select("SELECT SUM(BETRAG) AS SUMME, G_KONTO, E_KONTO, ANFANG, ENDE
FROM WEG_WG_DEF
WHERE KOS_TYP='$kos_typ'
    && KOS_ID='$kos_id'
    && AKTUELL='1'
    && E_KONTO = '6020'
        && (
            ( ENDE = '0000-00-00' && DATE_FORMAT( ANFANG, '%Y-%m' ) <= '$jahr-$monat')
            OR
            ( DATE_FORMAT( ENDE, '%Y-%m' ) >= '$jahr-$monat' && DATE_FORMAT( ANFANG, '%Y-%m' ) <= '$jahr-$monat' ))");

        $row = $result[0];
        if (!empty ($row ['SUMME'])) {
            return $row ['SUMME'] * -1;
        }
    }

    function hg_gesamtabrechnung_pdf($p_id = '0')
    {
        /* Art = Ausgaben, Einnahmen, Mittelverwendung */

        $this->get_hga_profil_infos($p_id);
        $bb = new buchen ();

        $vj = $this->p_jahr - 1;
        $datum11 = $vj . "-12-31";
        $datum11_d = "31.12.$vj";

        $kontostand11 = $bb->kontostand_tagesgenau_bis($this->p_gk_id, $datum11_d);
        if (!$kontostand11) {
            $kontostand11 = $this->get_kontostand_manuell($this->p_gk_id, $datum11);
        }
        $kontostand11_a = nummer_punkt2komma_t($kontostand11);
        $zeileb = 0;
        $berechnungs_tab [$zeileb] ['BEZ'] = "<b>KONTOSTAND 1.1.$this->p_jahr</b>";
        $berechnungs_tab [$zeileb] ['BETRAG'] = "<b>$kontostand11_a</b>";

        $kk = new kontenrahmen ();
        $kontenrahmen_id = $kk->get_kontenrahmen('Objekt', $this->p_objekt_id);

        $einnahme_manuell = $this->get_summe_zahlungen_manuell($p_id);
        if (empty($einnahme_manuell)) {
            $einnahme_konten_arr = $kk->get_konten_nach_art_gruppe('Einnahmen', 'Einnahmen Hausgeld', $kontenrahmen_id);

            $anz_e = count($einnahme_konten_arr);
            $e_summe = 0;
            $e_summe_vorjahr = 0;
            $zeile_einnahmen = 0;
            for ($a = 0; $a < $anz_e; $a++) {
                $kbez = $einnahme_konten_arr [$a] ['BEZEICHNUNG'];
                $ekonto = $einnahme_konten_arr [$a] ['KONTO'];
                $bb->summe_kontobuchungen_jahr($this->p_gk_id, $ekonto, $this->p_jahr);
                $summe_ekonto = $bb->summe_konto_buchungen;
                $bb->summe_kontobuchungen_jahr($this->p_gk_id, $ekonto, $this->p_jahr - 1);
                $summe_vorjahr_ekonto = $bb->summe_konto_buchungen;
                if ($summe_ekonto) {
                    $summe_ekonto_a = nummer_punkt2komma_t($summe_ekonto);
                    $summe_vorjahr_ekonto_a = nummer_punkt2komma_t($summe_vorjahr_ekonto);

                    $einnahmen_tab [$zeile_einnahmen] ['KONTO'] = $ekonto;
                    $einnahmen_tab [$zeile_einnahmen] ['KONTOART'] = 'Einnahmen';
                    $einnahmen_tab [$zeile_einnahmen] ['BEZ'] = $kbez;
                    $einnahmen_tab [$zeile_einnahmen] ['BETRAG'] = $summe_ekonto_a;
                    $einnahmen_tab [$zeile_einnahmen] ['BETRAG_VORJAHR'] = $summe_vorjahr_ekonto_a;
                    $zeile_einnahmen++;
                }
                $e_summe += $summe_ekonto;
                $e_summe_vorjahr += $summe_vorjahr_ekonto;
            }

            $e_summe_a = nummer_punkt2komma_t($e_summe);
            $e_summe_vorjahr_a = nummer_punkt2komma_t($e_summe_vorjahr);
            $zeile_einnahmen++;
            $einnahmen_tab [$zeile_einnahmen] ['BEZ'] = "<b>SUMME</b>";
            $einnahmen_tab [$zeile_einnahmen] ['BETRAG'] = "<b>$e_summe_a</b>";
            $einnahmen_tab [$zeile_einnahmen] ['BETRAG_VORJAHR'] = $e_summe_vorjahr_a;
        } else {
            $anz_m = count($einnahme_manuell);
            $e_summe = 0;
            $zeile_einnahmen = 0;
            for ($a = 0; $a < $anz_m; $a++) {
                $ki = new kontenrahmen ();

                $konto = $einnahme_manuell [$a] ['KOSTENKONTO'];
                $ki->konto_informationen2($konto, $kontenrahmen_id);
                $ksumme = $einnahme_manuell [$a] ['SUMME'];
                $ksumme_a = nummer_punkt2komma_t($ksumme);
                $e_summe += $ksumme;
                echo "<tr><td>$konto</td><td>$ki->konto_art_bezeichnung</td><td>$ki->konto_bezeichnung</td><td>$ksumme_a</td></tr>";
                $einnahmen_tab [$zeile_einnahmen] ['KONTO'] = $konto;
                $einnahmen_tab [$zeile_einnahmen] ['KONTOART'] = $ki->konto_art_bezeichnung;
                $einnahmen_tab [$zeile_einnahmen] ['BEZ'] = $ki->konto_bezeichnung;
                $einnahmen_tab [$zeile_einnahmen] ['BETRAG'] = $ksumme_a;
                $zeile_einnahmen++;
            }
            $e_summe_a = nummer_punkt2komma_t($e_summe);
            echo "<tfoot><tr><td></td><td></td><th><b>SUMME</b></th><th><b>$e_summe_a</b></th></tr></tfoot>";
            echo "<table>";
            $einnahmen_tab [$zeile_einnahmen] ['BEZ'] = '<b>SUMME</b>';
            $einnahmen_tab [$zeile_einnahmen] ['BETRAG'] = "<b>$e_summe_a</b>";
        }

        $_umlage_ktos = $this->get_hgkonten_arr($p_id, 'Ausgaben/Einnahmen');
        $_umlage_ktos_sort = array_sortByIndex($_umlage_ktos, 'GRUPPE', SORT_DESC);
        $_umlage_ktos = $_umlage_ktos_sort;
        unset ($_umlage_ktos_sort);

        $anz_k = count($_umlage_ktos);

        $znr = 0;
        $g_summe = 0;
        $g_summe_vorjahr = 0;
        $zeile_ausgaben = 0;

        for ($a = 0; $a < $anz_k; $a++) {
            $konto = $_umlage_ktos [$a] ['KONTO'];
            $gruppe = $_umlage_ktos [$a] ['GRUPPE'];
            $kontoart = $_umlage_ktos [$a] ['KONTO_ART'];

            $betraege_arr = $this->get_betraege_arr($p_id, $konto);
            $anz_b = count($betraege_arr);

            for ($b = 0; $b < $anz_b; $b++) {
                $konto_b = $betraege_arr [$b] ['KONTO'];
                $text = $betraege_arr [$b] ['TEXT'];
                $gen_key_id = $betraege_arr [$b] ['GEN_KEY_ID'];
                $betrag = $betraege_arr [$b] ['BETRAG'];
                $betrag_vorjahr = $betraege_arr [$b] ['BETRAG_VORJAHR'];
                $kos_typ = $betraege_arr [$b] ['KOS_TYP'];
                $kos_id = $betraege_arr [$b] ['KOS_ID'];

                $r = new rechnung ();

                $bk = new bk ();
                $bk->get_genkey_infos($gen_key_id);
                $betrag_a = nummer_punkt2komma_t($betrag);
                $betrag_vorjahr_a = nummer_punkt2komma_t($betrag_vorjahr);

                $ausgaben_tab [$zeile_ausgaben] ['KONTO'] = $konto_b;
                $ausgaben_tab [$zeile_ausgaben] ['KONTOART'] = $kontoart;
                $ausgaben_tab [$zeile_ausgaben] ['GRUPPE'] = $gruppe;
                $ausgaben_tab [$zeile_ausgaben] ['BEZ'] = $text;
                $ausgaben_tab [$zeile_ausgaben] ['BETRAG'] = $betrag_a;
                $ausgaben_tab [$zeile_ausgaben] ['BETRAG_VORJAHR'] = $betrag_vorjahr_a;


                $g_summe += $betrag;
                $g_summe_vorjahr += $betrag_vorjahr;
                $zeile_ausgaben++;
                $znr++;
            }
        }
        $g_summe_a = nummer_punkt2komma_t($g_summe);
        $g_summe_vorjahr_a = nummer_punkt2komma_t($g_summe_vorjahr);

        $ausgaben_tab = array_orderby($ausgaben_tab, 'GRUPPE', SORT_DESC, 'KONTO', SORT_ASC);

        /* Summe der Kosten und Einnahmen bilden und als letzte Zeile einfägen */
        $ausgaben_tab [$zeile_ausgaben] ['BEZ'] = '<b>SUMME</b>';
        $ausgaben_tab [$zeile_ausgaben] ['BETRAG'] = "<b>$g_summe_a</b>";
        $ausgaben_tab [$zeile_ausgaben] ['BETRAG_VORJAHR'] = "$g_summe_vorjahr_a";

        /* Art = Ausgaben, Einnahmen, Mittelverwendung - Jetzt wird sortiert */
        $_umlage_ktos = $this->get_hgkonten_arr($p_id, 'Mittelverwendung');
        $_umlage_ktos_sort = array_sortByIndex($_umlage_ktos, 'GRUPPE', SORT_DESC);
        $_umlage_ktos = $_umlage_ktos_sort;
        unset ($_umlage_ktos_sort);

        $anz_k = count($_umlage_ktos);

        $znr = 0;
        $g_summe1 = 0;
        for ($a = 0; $a < $anz_k; $a++) {
            $konto = $_umlage_ktos [$a] ['KONTO'];
            $gruppe = $_umlage_ktos [$a] ['GRUPPE'];

            $betraege_arr = $this->get_betraege_arr($p_id, $konto);
            $anz_b = count($betraege_arr);

            for ($b = 0; $b < $anz_b; $b++) {
                $konto_b = $betraege_arr [$b] ['KONTO'];
                $text = $betraege_arr [$b] ['TEXT'];
                $gen_key_id = $betraege_arr [$b] ['GEN_KEY_ID'];
                $betrag = $betraege_arr [$b] ['BETRAG'];
                $betrag_vorjahr = $betraege_arr [$b] ['BETRAG_VORJAHR'];
                $kos_typ = $betraege_arr [$b] ['KOS_TYP'];
                $kos_id = $betraege_arr [$b] ['KOS_ID'];

                $r = new rechnung ();

                $bk = new bk ();
                $bk->get_genkey_infos($gen_key_id);
                $betrag_a = nummer_punkt2komma_t($betrag);
                $betrag_vorjahr_a = nummer_punkt2komma_t($betrag_vorjahr);
                echo "<tr><td>$konto_b</td><td>$gruppe</td><td>$text</td><td>$betrag_a</td></tr>";

                $mv_tab [$znr] ['KONTO'] = $konto_b;
                $mv_tab [$znr] ['KONTO_ART'] = $gruppe;
                $mv_tab [$znr] ['BEZ'] = $text;
                $mv_tab [$znr] ['BETRAG'] = $betrag_a;
                $mv_tab [$znr] ['BETRAG_VORJAHR'] = $betrag_vorjahr_a;
                $g_summe1 += $betrag;
                $g_summe1_vorjahr += $betrag_vorjahr;

                $znr++;
            }
        }
        $g_summe1_a = nummer_punkt2komma_t($g_summe1);
        $g_summe1_vorjahr_a = nummer_punkt2komma_t($g_summe1_vorjahr);

        $mv_tab = array_orderby($mv_tab, 'GRUPPE', SORT_DESC, 'KONTO', SORT_ASC);
        $mv_tab [$znr] ['BEZ'] = '<b>SUMME</b>';
        $mv_tab [$znr] ['BETRAG'] = "<b>$g_summe1_a</b>";
        $mv_tab [$znr] ['BETRAG_VORJAHR'] = "$g_summe1_vorjahr_a";

        $ergebnis = $e_summe + $g_summe + $g_summe1;
        $ergebnis_a = nummer_punkt2komma_t($ergebnis);
        if ($ergebnis > 0) {
            $erg_text = 'GUTHABEN';
        }
        if ($ergebnis < 0) {
            $erg_text = 'NACHZAHLUNG';
        }

        $zz = 0;
        $berechnung_tab [$zz] ['BEZ'] = "Einnahmen";
        $berechnung_tab [$zz] ['BETRAG'] = $e_summe_a;
        $zz++;
        $berechnung_tab [$zz] ['BEZ'] = "Bewirtschaftungskosten /-Einnahmen";
        $berechnung_tab [$zz] ['BETRAG'] = $g_summe_a;
        $zz++;
        $e_a_zw_summe_a = nummer_punkt2komma_t($e_summe + $g_summe);
        $berechnung_tab [$zz] ['BEZ'] = "<b>Zwischenergebnis (Einnahmen - Ausgaben)</b>";
        $berechnung_tab [$zz] ['BETRAG'] = "<b>$e_a_zw_summe_a</b>";
        $zz++;

        $berechnung_tab [$zz] ['BEZ'] = "Mittelverwendung";
        $berechnung_tab [$zz] ['BETRAG'] = $g_summe1_a;
        $zz++;
        $berechnung_tab [$zz] ['BEZ'] = "<b>Saldo aus Hausgeldabrechnung ($erg_text)</b>";
        $berechnung_tab [$zz] ['BETRAG'] = "<b>$ergebnis_a</b>";

        /* Kontoentwicklung */
        $zz = 0;
        $kto_tab [$zz] ['BEZ'] = "Kontostand 01.01.$this->p_jahr";
        $kto_tab [$zz] ['BETRAG'] = $kontostand11_a;
        $zz++;
        $kto_tab [$zz] ['BEZ'] = "Saldo aus Hausgeldabrechnung ($erg_text)";
        $kto_tab [$zz] ['BETRAG'] = "$ergebnis_a";
        $zz++;

        /* Verrechnungkontos holen, z.b. Forderungen / Verbindlichkeiten */
        $verr_ktos = $this->get_hgkonten_arr($p_id, 'Verrechnung');

        $anz_v = count($verr_ktos);
        if ($anz_v) {
            $summe_verrechnung = 0;
            for ($v = 0; $v < $anz_v; $v++) {
                $v_konto = $verr_ktos [$v] ['KONTO'];
                $v_bez = $verr_ktos [$v] ['TEXT'];
                $kto_tab [$zz] ['BEZ'] = "$v_bez";
                $this->get_summe_zeilen($v_konto, $p_id);
                $this->summe_zeilen_a = nummer_punkt2komma_t($this->summe_zeilen);
                $kto_tab [$zz] ['BETRAG'] = "$this->summe_zeilen_a";
                $summe_verrechnung += $this->summe_zeilen;
                $zz++;
            }
        }

        $kto_stand = $ergebnis + $kontostand11 + $summe_verrechnung;
        $kto_stand_a = nummer_punkt2komma_t($kto_stand);
        $kto_tab [$zz] ['BEZ'] = "Kontostand 31.12.$this->p_jahr      (SOLL)";
        $kto_tab [$zz] ['BETRAG'] = "$kto_stand_a";

        $datum3112 = "$this->p_jahr" . "-12-31";
        $datum3112_d = "31.12.$this->p_jahr";

        $kontostand3112 = $bb->kontostand_tagesgenau_bis($this->p_gk_id, $datum3112_d);
        $txt = 'Aus Buchungen';
        if (!$kontostand3112) {
            $kontostand3112 = $this->get_kontostand_manuell($this->p_gk_id, $datum3112);
            $txt = 'Manuell eingegeben';
        }
        $kontostand3112_a = nummer_punkt2komma_t($kontostand3112);
        $zz++;
        $kto_tab [$zz] ['BEZ'] = "<b>Kontostand 31.12.$this->p_jahr      (IST) - $txt</b>";
        $kto_tab [$zz] ['BETRAG'] = "<b>$kontostand3112_a</b>";

        $pdf = new Cezpdf ('a4', 'portrait');
        $bpdf = new b_pdf ();
        $bpdf->b_header($pdf, 'Partner', session()->get('partner_id'), 'portrait', 'Helvetica.afm', 6);
        $this->footer_zahlungshinweis = $bpdf->zahlungshinweis;
        $pdf->setColor(0.6, 0.6, 0.6);
        $pdf->filledRectangle(50, 690, 500, 15);
        $pdf->setColor(0, 0, 0);
        $pdf->ezSetY(720);
        $datum = date("d.m.Y");
        $p = new partners ();
        $p->get_partner_info(session()->get('partner_id'));
        $pdf->ezSetDy(-30);
        $pdf->ezText("$p->partner_ort, den $datum", 10, array(
            'justification' => 'right'
        ));
        $pdf->ezSetY(705);

        $o = new objekt ();
        $o->get_objekt_infos($this->p_objekt_id);
        $pdf->ezText(" <b>HAUSGELD-GESAMTABRECHNUNG $this->p_jahr | OBJEKT: $o->objekt_kurzname</b>", 10, array(
            'justification' => 'full'
        ));
        $pdf->ezSetDy(-15);

        $p = new partners ();
        $p->get_partner_info(session()->get('partner_id'));

        $cols_2 = array(
            'KONTO' => "Konto",
            'BEZ' => "Bezeichnung",
            'KONTOART' => "Kontoart",
            'BETRAG_VORJAHR' => "Betrag Vorjahr",
            'BETRAG' => "Betrag"
        );
        $bpdf->addTable($pdf, $einnahmen_tab, $cols_2, '<b>HAUSGELDEINNAHMEN</b>', array(
            'rowGap' => 1.5,
            'showLines' => 1,
            'showHeadings' => 1,
            'shaded' => 1,
            'shadeCol' => array(
                0.9,
                0.9,
                0.9
            ),
            'titleFontSize' => 10,
            'fontSize' => 8,
            'xPos' => 55,
            'xOrientation' => 'right',
            'width' => 500,
            'cols' => array(
                'KONTO' => array(
                    'justification' => 'left',
                    'width' => 45
                ),
                'KONTOART' => array(
                    'justification' => 'left',
                    'width' => 55
                ),
                'BETRAG' => array(
                    'justification' => 'right',
                    'width' => 55
                ),
                'BETRAG_VORJAHR' => array(
                    'justification' => 'right',
                    'width' => 55
                )
            )
        ));

        $pdf->ezSetDy(-15);
        $cols_2 = array(
            'KONTO' => "Konto",
            'BEZ' => "Bezeichnung",
            'GRUPPE' => "Kostenart",
            'KONTOART' => "Kontoart",
            'BETRAG_VORJAHR' => "Betrag Vorjahr",
            'BETRAG' => "Betrag",

        );
        $bpdf->addTable($pdf, $ausgaben_tab, $cols_2, '<b>BEWIRTSCHAFTUNGSKOSTEN/-EINNAHMEN</b>', array(
            'rowGap' => 1.5,
            'showLines' => 1,
            'showHeadings' => 1,
            'shaded' => 1,
            'shadeCol' => array(
                0.9,
                0.9,
                0.9
            ),
            'titleFontSize' => 10,
            'fontSize' => 8,
            'xPos' => 55,
            'xOrientation' => 'right',
            'width' => 500,
            'cols' => array(
                'KONTO' => array(
                    'justification' => 'left',
                    'width' => 45
                ),
                'KONTOART' => array(
                    'justification' => 'left',
                    'width' => 55
                ),
                'BETRAG_VORJAHR' => array(
                    'justification' => 'right',
                    'width' => 55
                ),
                'BETRAG' => array(
                    'justification' => 'right',
                    'width' => 55
                )

            )
        ));
        $cols_2 = array(
            'KONTO' => "Konto",
            'BEZ' => "Bezeichnung",
            'KONTO_ART' => "Kontoart",
            'BETRAG_VORJAHR' => "Betrag Vorjahr",
            'BETRAG' => "Betrag"
        );
        $pdf->ezSetDy(-15);
        $bpdf->addTable($pdf, $mv_tab, $cols_2, '<b>MITTELVERWENDUNG</b>', array(
            'rowGap' => 1.5,
            'showLines' => 1,
            'showHeadings' => 1,
            'shaded' => 1,
            'shadeCol' => array(
                0.9,
                0.9,
                0.9
            ),
            'titleFontSize' => 10,
            'fontSize' => 8,
            'xPos' => 55,
            'xOrientation' => 'right',
            'width' => 500,
            'cols' => array(
                'KONTO' => array(
                    'justification' => 'left',
                    'width' => 45
                ),
                'KONTOART' => array(
                    'justification' => 'left',
                    'width' => 100
                ),
                'BEZ' => array(
                    'justification' => 'left',
                    'width' => 250
                ),
                'BETRAG' => array(
                    'justification' => 'right',
                    'width' => 55
                ),
                'BETRAG_VORJAHR' => array(
                    'justification' => 'right',
                    'width' => 55
                )
            )
        ));

        $pdf->ezSetDy(-15);
        $cols_ber = array(
            'BEZ' => "",
            'BETRAG' => "Betrag"
        );
        $bpdf->addTable($pdf, $berechnung_tab, $cols_ber, '<b>BERECHNUNG</b>', array(
            'rowGap' => 1.5,
            'showLines' => 1,
            'showHeadings' => 1,
            'shaded' => 1,
            'shadeCol' => array(
                0.9,
                0.9,
                0.9
            ),
            'titleFontSize' => 10,
            'fontSize' => 8,
            'xPos' => 55,
            'xOrientation' => 'right',
            'width' => 500,
            'cols' => array(
                'BEZ' => array(
                    'justification' => 'left',
                ),
                'BETRAG' => array(
                    'justification' => 'right',
                    'width' => 55
                )
            )
        ));
        $pdf->ezSetDy(-15);

        $bpdf->addTable($pdf, $kto_tab, $cols_ber, "<b>GELDKONTOENTWICKLUNG (HAUSGELD)</b>", array(
            'rowGap' => 1.5,
            'showLines' => 1,
            'showHeadings' => 1,
            'shaded' => 1,
            'shadeCol' => array(
                0.9,
                0.9,
                0.9
            ),
            'titleFontSize' => 10,
            'fontSize' => 8,
            'xPos' => 55,
            'xOrientation' => 'right',
            'width' => 500,
            'cols' => array(
                'BEZ' => array(
                    'justification' => 'left'
                ),
                'BETRAG' => array(
                    'justification' => 'right',
                    'width' => 55
                )
            )
        ));

        ob_end_clean();
        $pdf->ezStream();
    }

    function get_hga_profil_infos($p_id)
    {
        $result = DB::select("SELECT * FROM WEG_HGA_PROFIL WHERE AKTUELL='1' && ID='$p_id' ORDER BY DAT ASC LIMIT 0,1");
        if (!empty($result)) {
            $row = $result[0];
            $this->p_jahr = $row ['JAHR'];
            $this->p_objekt_id = $row ['OBJEKT_ID'];
            $this->p_gk_id = $row ['GELDKONTO_ID'];
            $this->p_bez = $row ['BEZEICHNUNG'];
            $this->p_ihr_gk_id = $row ['IHR_GK_ID'];
            $this->p_wplan_id = $row ['WPLAN_ID'];
            $this->hg_konto = $row ['HG_KONTO'];
            $this->hk_konto = $row ['HK_KONTO'];
            $this->ihr_konto = $row ['IHR_KONTO'];
            $this->p_von = $row ['VON'];
            $this->p_bis = $row ['BIS'];
            $this->p_von_d = date_mysql2german($row ['VON']);
            $this->p_bis_d = date_mysql2german($row ['BIS']);
        } else {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\ErrorMessage("Profil $id existiert nicht.")
            );
        }
    }

    function get_kontostand_manuell($gk_id, $datum)
    {
        $result = DB::select("SELECT BETRAG FROM WEG_KONTOSTAND WHERE GK_ID='$gk_id' && AKTUELL='1' && DATUM='$datum' ORDER BY DAT DESC LIMIT 0,1");
        if (!empty($result)) {
            $row = $result[0];
            return $row ['BETRAG'];
        }
    }

    function get_summe_zahlungen_manuell($p_id)
    {
        $result = DB::select("SELECT KOSTENKONTO, SUM(BUCHUNGS_SUMME) AS SUMME FROM WEG_HG_ZAHLUNGEN WHERE  AKTUELL='1' &&  WEG_HGA_ID='$p_id' GROUP BY KOSTENKONTO ORDER BY `WEG_HG_ZAHLUNGEN`.`KOSTENKONTO` ASC");
        return $result;
    }

    function get_hgkonten_arr($p_id, $art, $last_year = true)
    {
        if (!session()->has('objekt_id')) {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\ErrorMessage('Objekt wählen -> Error 2312x2')
            );
        }
        $k = new kontenrahmen ();
        $kontenrahmen_id = $k->get_kontenrahmen('Objekt', session()->get('objekt_id'));

        if ($last_year) {
            $p_id_vorjahr = $this->get_pid_lastyear($p_id);
            if (!is_null($p_id_vorjahr)) {
                $p_id_query = ' IN( ' . $p_id . ', ' . $p_id_vorjahr . ' )';
            } else {
                $p_id_query = '=' . $p_id;
            }
        } else {
            $p_id_query = '=' . $p_id;
        }

        $result = DB::select("SELECT KONTO
FROM WEG_HGA_ZEILEN 
WHERE AKTUELL='1' 
	&& WEG_HG_P_ID" . $p_id_query . " 
	&& ART='$art' 
	&& KONTO NOT IN (SELECT KONTO
		FROM WEG_HGA_ZEILEN 
		WHERE AKTUELL='1' 
			&& WEG_HG_P_ID = '$p_id'
			&& ART!='$art')
GROUP BY KONTO
ORDER BY KONTO ASC
");
        if (!empty($result)) {
            $z = 0;
            foreach ($result as $row) {
                $konto = $row ['KONTO'];
                $k->konto_informationen2($konto, $kontenrahmen_id);
                $arr [$z] ['KONTO'] = $konto;
                $arr [$z] ['GRUPPE'] = $k->konto_gruppen_bezeichnung;
                $arr [$z] ['KONTO_BEZ'] = $k->konto_bezeichnung;
                $arr [$z] ['KONTO_ART'] = $k->konto_art_bezeichnung;
                $arr [$z] ['TEXT'] = $row ['TEXT'];
                $z++;
            }
        }
        return $arr;
    }

    function get_pid_lastyear($p_id)
    {
        $p_id_vorjahr = null;
        $result = DB::select("SELECT P2.ID FROM WEG_HGA_PROFIL AS P1 JOIN WEG_HGA_PROFIL AS P2 ON(P1.OBJEKT_ID = P2.OBJEKT_ID) WHERE P1.AKTUELL='1' && P2.AKTUELL='1' && P1.ID='$p_id' && P2.JAHR = P1.JAHR - 1");
        if (!empty($result)) {
            $p_id_vorjahr = $result[0];
            $p_id_vorjahr = $p_id_vorjahr['ID'];
        }
        return $p_id_vorjahr;
    }

    function get_betraege_arr($p_id, $konto)
    {
        $p_id_vorjahr = $this->get_pid_lastyear($p_id);
        if (!is_null($p_id_vorjahr)) {
            $result = DB::select("(
SELECT Z1.*, Z2.BETRAG AS BETRAG_VORJAHR 
FROM (SELECT * FROM WEG_HGA_ZEILEN WHERE WEG_HG_P_ID='$p_id' AND AKTUELL='1' ) AS Z1 
	LEFT JOIN (SELECT * FROM WEG_HGA_ZEILEN WHERE WEG_HG_P_ID='$p_id_vorjahr' AND AKTUELL='1' ) AS Z2 ON (Z1.KONTO = Z2.KONTO) 
WHERE Z1.KONTO='$konto' 
)
UNION ALL
(
SELECT Z2.DAT, Z2.ID, Z2.WEG_HG_P_ID, Z2.KONTO, Z2.ART, Z2.TEXT, Z2.GEN_KEY_ID, Z1.BETRAG, Z2.HNDL_BETRAG, Z2.KOS_TYP, Z2.KOS_ID, Z2.AKTUELL, Z2.SU_AUSZAHLEN, Z2.BETRAG AS BETRAG_VORJAHR 
FROM (SELECT * FROM WEG_HGA_ZEILEN WHERE WEG_HG_P_ID='$p_id' AND AKTUELL='1' ) AS Z1 
	RIGHT JOIN (SELECT * FROM WEG_HGA_ZEILEN WHERE WEG_HG_P_ID='$p_id_vorjahr' AND AKTUELL='1' ) AS Z2 ON (Z1.KONTO = Z2.KONTO) 
WHERE Z1.KONTO IS NULL AND Z2.KONTO='$konto'
)");
        } else {
            $result = DB::select("SELECT *, 0 AS BETRAG_VORJAHR FROM WEG_HGA_ZEILEN WHERE AKTUELL='1' && WEG_HG_P_ID='$p_id' && KONTO='$konto' ORDER BY KONTO ASC");
        }
        if (!empty($result)) {
            return $result;
        }
    }

    function get_summe_zeilen($konto, $profil_id)
    {
        $result = DB::select(
            "SELECT SUM(BETRAG) AS SUMME, SUM(HNDL_BETRAG) AS SUMME_HNDL, BIT_OR(SU_AUSZAHLEN) AS SU_AUSZAHLEN FROM WEG_HGA_ZEILEN WHERE AKTUELL='1' && WEG_HG_P_ID='$profil_id' && KONTO='$konto'");
        $this->summe_zeilen = 0;
        $this->summe_hndl = 0;
        $this->konto_has_entry = false;
        $this->konto_su_auszahlen = false;

        if (!empty($result)) {
            $row = $result[0];
            $this->summe_zeilen = $row ['SUMME'];
            $this->summe_hndl = $row ['SUMME_HNDL'];
            $this->konto_su_auszahlen = ($row['SU_AUSZAHLEN'] == '1');
            if (isset($row ['SUMME_HNDL']) || isset($row ['SUMME'])) {
                $this->konto_has_entry = true;
            }
        }
    }

    function ihr($p_id)
    {
        echo "<table>";
        echo "<tr><th><b><h2>Entwicklung der Instandhaltungsrücklage</h2></th><th><h2><b>SOLL</b></h2></th><th><h2><b>IST</b></h2></th></tr>";
        $this->get_hga_profil_infos($p_id);
        $this->p_ihr_gk_id;
        $kk = new kontenrahmen ();
        $bb = new buchen ();
        $kontostand11 = $bb->kontostand_tagesgenau_bis($this->p_ihr_gk_id, "01.01.$this->p_jahr");
        if (!$kontostand11) {
            $kontostand11 = $this->get_kontostand_manuell($this->p_ihr_gk_id, $this->p_jahr . "-01-01");
        }
        $kontostand11_a = nummer_punkt2komma($kontostand11);
        echo "<tr><td><b><h1>I. Anfangsbestand 01.01.$this->p_jahr</h1></td><td><h1>$kontostand11_a €</h1></td><td><h1>$kontostand11_a €</h1></td></tr>";

        $soll_summe_wp = $this->get_soll_betrag_wp(6040, $this->p_wplan_id);
        $soll_summe_wp_a = nummer_punkt2komma($soll_summe_wp);
        echo "<tr><td><b><h1>II. Soll-Zuführung zur Rücklage laut WP</h1></td><td><h1>$soll_summe_wp_a €</h1></td><td></td></tr>";

        $this->III_tab_anzeigen($p_id);

        $soll_endbestand = $kontostand11 + $soll_summe_wp;
        $soll_endbestand_a = nummer_punkt2komma($soll_endbestand);
        // echo "IV. Soll-Endbestand 31.12.$this->p_jahr $soll_endbestand_a €<br>";
        echo "<tr><td><b><h1>IV. Soll-Endbestand 31.12.$this->p_jahr</h1></td><td><h1>$soll_endbestand_a €</h1></td><td><h1></h1></td></tr>";

        $n_jahr = $this->p_jahr + 1;
        $kontostand11 = $bb->kontostand_tagesgenau_bis($this->p_ihr_gk_id, "01.01.$n_jahr");
        if (!$kontostand11) {
            $kontostand11 = $this->get_kontostand_manuell($this->p_ihr_gk_id, $this->p_jahr . "-12-31");
        }

        $kontostand11_a = nummer_punkt2komma($kontostand11);
        echo "<tr><td><b><h1>V. Ist-Endbestand 31.12.$this->p_jahr</h1></td><td><h1></h1></td><td><h1>$kontostand11_a €</h1></td></tr>";

        if (!$this->man3) {
            $this->VI_ausstehend($p_id, 6030);
            echo "<tr><td><b><h1>VI. Ausstehende Beträge</h1></td><td><h1></h1></td><td><h1>$this->summe_alle_diff_a €</h1></td></tr>";

            $this->VI_ausstehend_tab();
        }
    }

    function get_soll_betrag_wp($konto, $wplan_id)
    {
        $result = DB::select("SELECT SUM(BETRAG) AS BETRAG FROM WEG_WPLAN_ZEILEN WHERE AKTUELL='1' && WPLAN_ID='$wplan_id' && KOSTENKONTO='$konto'");
        if (!empty($result)) {
            $row = $result[0];
            return $row ['BETRAG'];
        }
    }

    function III_tab_anzeigen($p_id)
    {
        $this->get_hga_profil_infos($p_id);
        $arr = $this->get_summen_konten_arr($this->p_ihr_gk_id, $this->p_jahr);
        if (empty($arr)) {
            $this->man3 = true;
            echo "MANU $this->p_ihr_gk_id, $this->p_jahr";
            $arr = $this->get_summen_konten_arr_manuell($this->p_ihr_gk_id, $this->p_jahr);
        } else {
            echo "NEMANU";
            print_r($arr);
        }
        if (!empty($arr)) {
            $kk = new kontenrahmen ();
            $kontenrahmen_id = $kk->get_kontenrahmen('Geldkonto', $this->p_ihr_gk_id);
            $anz = count($arr);
            $z = 0;
            for ($a = 0; $a < $anz; $a++) {
                $z++;
                $konto = $arr [$a] ['KONTENRAHMEN_KONTO'];
                $summe = $arr [$a] ['SUMME'];
                $summe_a = nummer_punkt2komma($summe);
                $kk->konto_informationen2($konto, $kontenrahmen_id);
                echo "<tr><td><b><h1>III $z. $kk->konto_bezeichnung</h1></td><td></td><td><h1>$summe_a €</h1></td></tr>";
            }
        } else {
            echo "<tr><td><b><h1>III. Zuführungen / Entnahmen / Ausgaben</h1></td><td></td><td><h1>0,00 €</h1></td></tr>";
        }
    }

    function get_summen_konten_arr($gk_id, $jahr)
    {
        $result = DB::select("SELECT `KONTENRAHMEN_KONTO`, SUM(BETRAG) AS SUMME  FROM `GELD_KONTO_BUCHUNGEN` WHERE `GELDKONTO_ID` = '$gk_id' AND `AKTUELL` = '1' && DATE_FORMAT(DATUM, '%Y') = '$jahr' GROUP BY `KONTENRAHMEN_KONTO` ORDER BY DATUM ASC");
        return $result;
    }

    function get_summen_konten_arr_manuell($gk_id, $jahr)
    {
        $result = DB::select("SELECT `KONTENRAHMEN_KONTO`, SUM(BETRAG) AS SUMME  FROM `WEG_IHR_III` WHERE `IHR_GK_ID` = '$gk_id' AND `AKTUELL` = '1' && DATE_FORMAT(DATUM, '%Y') = '$jahr' GROUP BY `KONTENRAHMEN_KONTO` ORDER BY DATUM ASC");
        if (!empty($result)) {
            return $result;
        }
    }

    function VI_ausstehend($p_id, $konto)
    {
        $this->get_hga_profil_infos($p_id);
        $einheiten_arr = $this->einheiten_weg_tabelle_arr($this->p_objekt_id);
        $anz = count($einheiten_arr);
        if ($anz) {
            $z = 0;
            $von = "$this->p_jahr" . "-01-01";
            $bis = "$this->p_jahr" . "-12-31";
            $this->summe_alle_ist = 0;
            $this->summe_alle_soll = 0;
            $this->summe_alle_diff = 0;
            for ($a = 0; $a < $anz; $a++) {
                $kos_typ = 'Einheit';
                $kos_id = $einheiten_arr [$a];
                $soll_betrag = $this->hg_tab_soll_ist_einnahmen($konto, $kos_typ, $kos_id, $von, $bis);
                $this->summe_alle_soll += $soll_betrag;
                $eig_arr = $this->get_eigentuemer_arr_jahr($kos_id, $this->p_jahr);
                $anz_e = count($eig_arr);
                $summeg_eig = 0;
                for ($b = 0; $b < $anz_e; $b++) {
                    $eig_id = $eig_arr [$b] ['E_ID'];
                    $bb = new buchen ();
                    $ist_betrag = $bb->summe_kontobuchungen_dyn2($this->p_gk_id, $konto, $von, $bis, 'Eigentuemer', $eig_id);
                    $summeg_eig += $ist_betrag;
                }
                $diff = $summeg_eig - $soll_betrag;

                $diff_a = nummer_punkt2komma($diff);
                if ($diff_a == '-0,00') {
                    $diff = '0.00';
                }
                $e = new einheit ();
                $e->get_einheit_info($kos_id);
                $this->ausstehend [$z] ['EINHEIT_ID'] = $kos_id;
                $this->ausstehend [$z] ['EINHEIT'] = $e->einheit_kurzname;
                $this->ausstehend [$z] ['DIFF'] = nummer_punkt2komma_t($diff);
                $this->ausstehend [$z] ['SOLL'] = nummer_punkt2komma_t($soll_betrag);
                $this->ausstehend [$z] ['IST'] = nummer_punkt2komma_t($summeg_eig);
                $this->summe_alle_ist += $summeg_eig;
                $this->summe_alle_diff += $diff;
                $z++;
            }

            $this->summe_alle_ist_a = nummer_punkt2komma_t($this->summe_alle_ist);
            $this->summe_alle_soll_a = nummer_punkt2komma_t($this->summe_alle_soll);
            $this->summe_alle_diff_a = nummer_punkt2komma_t($this->summe_alle_diff);
            /* Summen als letzte Zeile */
            $this->ausstehend [$z] ['EINHEIT'] = "<b>SUMMEN</b>";
            $this->ausstehend [$z] ['DIFF'] = "<b>$this->summe_alle_diff_a € </b>";
            $this->ausstehend [$z] ['SOLL'] = "<b>$this->summe_alle_soll_a € </b>";
            $this->ausstehend [$z] ['IST'] = "<b>$this->summe_alle_ist_a € </b>";
        }
    }

    function hg_tab_soll_ist_einnahmen($e_konto, $kos_typ, $kos_id, $von, $bis)
    {
        $monats_arr = $this->monatsarray_erstellen($von, $bis);

        $anz_m = count($monats_arr);
        $soll_summe = 0;
        for ($a = 0; $a < $anz_m; $a++) {
            $monat = $monats_arr [$a] ['monat'];
            $jahr = $monats_arr [$a] ['jahr'];

            $result = DB::select("SELECT SUM( BETRAG ) AS SUMME FROM WEG_WG_DEF WHERE KOS_TYP = '$kos_typ' && KOS_ID = '$kos_id' && AKTUELL = '1' && ( ENDE = '0000-00-00'
OR DATE_FORMAT( ENDE, '%Y-%m' ) >= '$jahr-$monat' && DATE_FORMAT( ANFANG, '%Y-%m' ) <= '$jahr-$monat' ) && DATE_FORMAT( ANFANG, '%Y-%m' ) <= '$jahr-$monat' && E_KONTO = '$e_konto'");
            if (!empty($result)) {
                foreach ($result as $row) {
                    $soll_summe += $row ['SUMME'];
                }
            }
        }

        return $soll_summe;
    }

    function get_eigentuemer_arr_jahr($einheit_id, $jahr)
    {
        $time_jahr1_1 = mktime(0, 0, 0, 1, 1, $jahr);
        $time_jahr31_12 = mktime(23, 59, 59, 12, 31, $jahr);

        $bk = new bk ();

        $result = DB::select("SELECT * FROM WEG_MITEIGENTUEMER WHERE EINHEIT_ID='$einheit_id' && AKTUELL='1' && (DATE_FORMAT(VON, '%Y') <='$jahr' AND BIS='0000-00-00' OR DATE_FORMAT(VON, '%Y') <='$jahr' AND DATE_FORMAT(BIS, '%Y') >='$jahr') ORDER BY VON ASC");
        if (!empty($result)) {
            $z = 0;
            foreach ($result as $row) {
                $von = $row ['VON'];
                $von_arr = explode('-', $von);
                $von_d = $von_arr [2];
                $von_m = $von_arr [1];
                $von_j = $von_arr [0];
                $time_von = mktime(0, 0, 0, $von_m, $von_d, $von_j);

                if ($time_von <= $time_jahr1_1) {
                    $datum_von = $time_jahr1_1;
                } else {
                    $datum_von = $time_von;
                }

                $bis = $row ['BIS'];
                $bis_arr = explode('-', $bis);
                $bis_d = $bis_arr [2];
                $bis_m = $bis_arr [1];
                $bis_j = $bis_arr [0];
                $time_bis = mktime(0, 0, 0, $bis_m, $bis_d, $bis_j);

                if ($time_bis < $time_jahr31_12) {
                    $datum_bis = $time_bis;
                }

                if ($time_bis >= $time_jahr31_12) {
                    $datum_bis = $time_jahr31_12;
                }

                if ($bis == '0000-00-00') {
                    $datum_bis = $time_jahr31_12;
                }

                $einheit_id = $row ['EINHEIT_ID'];
                $eigentuemer_id = $row ['ID'];

                $datum_von_d = date('Y-m-d', $datum_von);
                $datum_bis_d = date('Y-m-d', $datum_bis);

                $bk = new bk ();
                $diff = $bk->diff_in_tagen($datum_von_d, $datum_bis_d) + 1;

                $my_arr [$z] ['EINHEIT_ID'] = $einheit_id;
                $my_arr [$z] ['ID'] = $eigentuemer_id;
                $my_arr [$z] ['VON'] = $datum_von_d;
                $my_arr [$z] ['BIS'] = $datum_bis_d;
                $my_arr [$z] ['TAGE'] = $diff;

                $z++;
            }
            return $my_arr;
        }
    }

    function VI_ausstehend_tab()
    {
        if (is_array($this->ausstehend)) {
            $anz = count($this->ausstehend);
            echo "<table class=\"sortable\">";
            echo "<tr><th>EINHEIT</th><th>EIGENTUEMER</th><th>SOLL</th><th>IST</th><th>FEHLBETRAG</th></tr>";
            for ($a = 0; $a < $anz; $a++) {
                $einheit_id = $this->ausstehend [$a] ['EINHEIT_ID'] ['EINHEIT_ID'];
                $diff = $this->ausstehend [$a] ['DIFF'];
                $soll = $this->ausstehend [$a] ['SOLL'];
                $ist = $this->ausstehend [$a] ['IST'];
                $e = new einheit ();
                $e->get_einheit_info($einheit_id);
                echo "<tr><td>$einheit_id</td><td>$e->einheit_kurzname</td><td>$soll</td><td>$ist</td><td>$diff</td></tr>";
            }
            echo "</table>";
        }
    }

    function ihr_pdf($p_id)
    {
        $pdf = new Cezpdf ('a4', 'portrait');
        $bpdf = new b_pdf ();
        $bpdf->b_header($pdf, 'Partner', session()->get('partner_id'), 'portrait', 'Helvetica.afm', 6);
        $this->footer_zahlungshinweis = $bpdf->zahlungshinweis;
        $this->ihr_pdf_einzeln($pdf, $p_id);

        ob_end_clean();
        $pdf->ezStream();
    }

    function ihr_pdf_einzeln(Cezpdf &$pdf, $p_id)
    {
        $this->get_hga_profil_infos($p_id);
        $this->p_ihr_gk_id;
        $kk = new kontenrahmen ();
        $kontenrahmen_id = $kk->get_kontenrahmen('Geldkonto', $this->p_ihr_gk_id);
        $bb = new buchen ();
        $kontostand11 = $bb->kontostand_tagesgenau_bis($this->p_ihr_gk_id, "01.01.$this->p_jahr");
        if (!$kontostand11) {
            $kontostand11 = $this->get_kontostand_manuell($this->p_ihr_gk_id, $this->p_jahr . "-01-01");
        }
        $kontostand11_a = nummer_punkt2komma_t($kontostand11);
        $ze = 0;
        $tab_arr [$ze] ['TEXT'] = "I. Anfangsbestand 01.01.$this->p_jahr";
        $tab_arr [$ze] ['SOLL'] = "$kontostand11_a € ";
        $tab_arr [$ze] ['IST'] = "$kontostand11_a € ";
        $ze++;
        $soll_summe_wp = $this->get_soll_betrag_wp(6040, $this->p_wplan_id);
        $soll_summe_wp_a = nummer_punkt2komma_t($soll_summe_wp);
        $tab_arr [$ze] ['TEXT'] = "II. Soll-Zuführung zur Rücklage laut WP";
        $tab_arr [$ze] ['SOLL'] = "";
        $tab_arr [$ze] ['IST'] = "$soll_summe_wp_a € ";
        $ze++;

        $iii_arr [] = $this->III_tab_anzeigen_pdf($p_id);

        if (is_array($iii_arr)) {
            $iii_arr = $iii_arr [0];
            for ($a = 0; $a < sizeof($iii_arr); $a++) {
                $text3 = $iii_arr [$a] ['TEXT'];
                $ist3 = $iii_arr [$a] ['IST'];
                $tab_arr [$ze] ['TEXT'] = $text3;
                $tab_arr [$ze] ['IST'] = $ist3;
                $ze++;
            }
        }

        $soll_endbestand = $kontostand11 + $soll_summe_wp;
        $soll_endbestand_a = nummer_punkt2komma_t($soll_endbestand);

        $tab_arr [$ze] ['TEXT'] = "IV. Soll-Endbestand 31.12.$this->p_jahr";
        $tab_arr [$ze] ['SOLL'] = "";
        $tab_arr [$ze] ['IST'] = "$soll_endbestand_a € ";

        $n_jahr = $this->p_jahr + 1;
        $kontostand11 = $bb->kontostand_tagesgenau_bis($this->p_ihr_gk_id, "01.01.$n_jahr");
        if (!$kontostand11) {
            $kontostand11 = $this->get_kontostand_manuell($this->p_ihr_gk_id, $this->p_jahr . "-12-31");
        }

        $kontostand11_a = nummer_punkt2komma_t($kontostand11);


        $ze++;
        $tab_arr [$ze] ['TEXT'] = "V. Endbestand 31.12.$this->p_jahr";
        $tab_arr [$ze] ['SOLL'] = "";
        $tab_arr [$ze] ['IST'] = "$kontostand11_a € ";

        $this->footer_zahlungshinweis = $bpdf->zahlungshinweis;
        $pdf->setColor(0.6, 0.6, 0.6);
        $pdf->filledRectangle(50, 690, 500, 15);
        $pdf->setColor(0, 0, 0);
        $pdf->ezSety(720);
        // $pdf->ezSetY(650);
        $datum = date("d.m.Y");
        $p = new partners ();
        $p->get_partner_info(session()->get('partner_id'));
        $pdf->ezText("$p->partner_ort, den $datum", 10, array(
            'justification' => 'right'
        ));

        $pdf->ezSetY(705);
        $o = new objekt ();
        $o->get_objekt_infos($this->p_objekt_id);
        $pdf->ezText(" <b>Entwicklung der Instandhaltungsrücklage $this->p_jahr | OBJEKT: $o->objekt_kurzname</b>", 10, array(
            'justification' => 'full'
        ));
        $pdf->ezSetDy(-15);

        $pdf->ezStopPageNumbers(); // seitennummerirung beenden
        $p = new partners ();
        $p->get_partner_info(session()->get('partner_id'));
        $cols = array(
            'TEXT' => "",
            'IST' => "IST-BETRAG"
        );
        $pdf->ezTable($tab_arr, $cols, "", array(
            'rowGap' => 1.5,
            'showLines' => 1,
            'showHeadings' => 1,
            'shaded' => 1,
            'shadeCol' => array(
                0.9,
                0.9,
                0.9
            ),
            'titleFontSize' => 7,
            'fontSize' => 10,
            'xPos' => 'left',
            'xOrientation' => 'right',
            'width' => 500,
            'cols' => array(
                'TEXT' => array(
                    'justification' => 'left'
                ),
                'SOLL' => array(
                    'justification' => 'right',
                    'width' => 100
                ),
                'IST' => array(
                    'justification' => 'right',
                    'width' => 100
                )
            )
        ));

        $this->get_hga_profil_infos($p_id);
        $d = new detail ();
        $anteile_g = $d->finde_detail_inhalt('Objekt', $this->p_objekt_id, 'Gesamtanteile');

        $einheiten_arr = $this->einheiten_weg_tabelle_arr($this->p_objekt_id);

        $anz_einheiten = count($einheiten_arr);

        $anz_konten = count($tab_arr);

        $gkkk = new geldkonto_info ();
        $gkkk->geld_konto_details($this->p_ihr_gk_id);

        $datum_heute = date("d.m.Y");
        $kontostand_aktuell = nummer_punkt2komma_t($bb->kontostand_tagesgenau_bis($this->p_ihr_gk_id, "$datum_heute"));

        for ($a = 0; $a < $anz_einheiten; $a++) {
            $pdf->ezNewPage();
            $e_kn = $einheiten_arr [$a] ['EINHEIT_KURZNAME'];
            $einheit_id = $einheiten_arr [$a] ['EINHEIT_ID'];
            $einheit_anteile = $d->finde_detail_inhalt('Einheit', $einheit_id, 'WEG-Anteile');

            $pdf->setColor(0.6, 0.6, 0.6);
            $pdf->filledRectangle(50, 690, 500, 15);
            $pdf->setColor(0, 0, 0);
            $pdf->ezSety(720);
            $pdf->ezText("$p->partner_ort, den $datum", 10, array(
                'justification' => 'right'
            ));

            $pdf->ezSetDy(-2);
            $pdf->ezText("<b> Anteil in der Instandhaltungsrücklage für $e_kn | OBJEKT: $o->objekt_kurzname $this->p_jahr</b>", 10, array(
                'justification' => 'full'
            ));

            $pdf->ezSetDy(-10);
            $pdf->ezText("<b>Geldkontobezeichnung:</b> $gkkk->geldkonto_bez", 9);
            $pdf->ezText("<b>Geldinstitut:</b> $gkkk->kredit_institut", 9);
            $pdf->ezText("<b>IBAN:</b> $gkkk->IBAN1", 9);
            $pdf->ezText("<b>BIC:</b> $gkkk->BIC", 9);

            for ($b = 0; $b < $anz_konten; $b++) {
                $tab_arr_e [$b] ['TEXT'] = $tab_arr [$b] ['TEXT'];
                $tab_arr_e [$b] ['IST'] = $tab_arr [$b] ['IST'];
                $tab_arr_e [$b] ['ANTEIL'] = nummer_punkt2komma_t(nummer_komma2punkt(str_replace('.', '', $tab_arr [$b] ['IST'])) / $anteile_g * $einheit_anteile) . " € ";
            }

            $pdf->ezSetDy(-15);
            $cols = array(
                'TEXT' => "",
                'IST' => "IST-BETRAG"
            );
            $pdf->ezTable($tab_arr, $cols, "", array(
                'rowGap' => 1.5,
                'showLines' => 1,
                'showHeadings' => 1,
                'shaded' => 1,
                'shadeCol' => array(
                    0.9,
                    0.9,
                    0.9
                ),
                'titleFontSize' => 7,
                'fontSize' => 10,
                'xPos' => 'left',
                'xOrientation' => 'right',
                'width' => 500,
                'cols' => array(
                    'TEXT' => array(
                        'justification' => 'left'
                    ),
                    'SOLL' => array(
                        'justification' => 'right',
                        'width' => 100
                    ),
                    'IST' => array(
                        'justification' => 'right',
                        'width' => 100
                    )
                )
            ));

            $pdf->ezSetDy(-15);
            $cols = array(
                'TEXT' => "",
                'IST' => "IST-BETRAG",
                'ANTEIL' => "IHR ANTEIL"
            );
            $pdf->ezTable($tab_arr_e, $cols, "", array(
                'rowGap' => 1.5,
                'showLines' => 1,
                'showHeadings' => 1,
                'shaded' => 1,
                'shadeCol' => array(
                    0.9,
                    0.9,
                    0.9
                ),
                'titleFontSize' => 7,
                'fontSize' => 10,
                'xPos' => 'left',
                'xOrientation' => 'right',
                'width' => 500,
                'cols' => array(
                    'TEXT' => array(
                        'justification' => 'left'
                    ),
                    'ANTEIL' => array(
                        'justification' => 'right',
                        'width' => 100
                    ),
                    'IST' => array(
                        'justification' => 'right',
                        'width' => 100
                    )
                )
            ));

            unset ($tab_arr_e);

            /* WEG LAUTERSTR 2014 */
            $pdf->ezSetDy(-15);
            $cols_laut = array(
                'TXT' => "",
                'BETRAG' => ""
            );

            $tab_laut [0] ['TXT'] = "Kontostand des IHR-Geldkontos vom $datum_heute";
            $tab_laut [0] ['BETRAG'] = "$kontostand_aktuell € ";

            $pdf->ezTable($tab_laut, $cols_laut, "Zusatzinformationen", array(
                'rowGap' => 1.5,
                'showLines' => 1,
                'showHeadings' => 0,
                'shaded' => 1,
                'shadeCol' => array(
                    0.9,
                    0.9,
                    0.9
                ),
                'titleFontSize' => 7,
                'fontSize' => 10,
                'xPos' => 'left',
                'xOrientation' => 'right',
                'width' => 500,
                'cols' => array(
                    'TXXT' => array(
                        'justification' => 'left'
                    ),
                    'BETRAG' => array(
                        'justification' => 'right',
                        'width' => 100
                    )
                )
            ));
        }
    }

    function III_tab_anzeigen_pdf($p_id)
    {
        $this->get_hga_profil_infos($p_id);
        $arr = $this->get_summen_konten_arr($this->p_ihr_gk_id, $this->p_jahr);
        if (empty($arr)) {
            $this->man3 = true;
            $arr = $this->get_summen_konten_arr_manuell($this->p_ihr_gk_id, $this->p_jahr);
        }
        if (!empty($arr)) {
            $kk = new kontenrahmen ();
            $kontenrahmen_id = $kk->get_kontenrahmen('Geldkonto', $this->p_ihr_gk_id);
            $anz = count($arr);
            $z = 0;
            for ($a = 0; $a < $anz; $a++) {
                $z++;
                $konto = $arr [$a] ['KONTENRAHMEN_KONTO'];
                $summe = $arr [$a] ['SUMME'];
                $summe_a = nummer_punkt2komma_t($summe);
                $kk->konto_informationen2($konto, $kontenrahmen_id);
                $new_arr [$a] ['TEXT'] = "III $z. $kk->konto_bezeichnung";
                $new_arr [$a] ['IST'] = "$summe_a € ";
            }
            return $new_arr;
        } else {
            $new_arr [0] ['TEXT'] = "III. Zuführungen / Entnahmen / Ausgaben";
            $new_arr [0] ['IST'] = "0,00 € ";
            return $new_arr;
        }
    }

    function hg_gesamtabrechnung($p_id = '0')
    {
        echo "<a href='" . route('web::weg::legacy', ['option' => 'testhgg_pdf']) . "'>PDF</a>";
        /* Art = Ausgaben, Einnahmen, Mittelverwendung */
        $_umlage_ktos = $this->get_hgkonten_arr($p_id, 'Ausgaben/Einnahmen');
        $_umlage_ktos = array_orderby($_umlage_ktos, 'GRUPPE', SORT_DESC, 'KONTO', SORT_ASC);

        $this->get_hga_profil_infos($p_id);
        $bb = new buchen ();
        $datum11 = $this->p_jahr . "-01-01";
        $kontostand11 = $bb->kontostand_tagesgenau_bis($this->p_gk_id, $datum11);
        if (!$kontostand11) {
            $kontostand11 = $this->get_kontostand_manuell($this->p_gk_id, $datum11);
        }
        $kontostand11_a = nummer_punkt2komma($kontostand11);
        echo "Kontostand  $kontostand11_a $this->p_gk_id $datum11<br>";

        $kk = new kontenrahmen ();
        $kontenrahmen_id = $kk->get_kontenrahmen('Objekt', $this->p_objekt_id);
        echo "Kontenrahmen $kontenrahmen_id<br>";

        $einnahme_manuell = $this->get_summe_zahlungen_manuell($p_id);
        if (empty($einnahme_manuell)) {
            $einnahme_konten_arr = $kk->get_konten_nach_art_gruppe('Einnahmen', 'Einnahmen Hausgeld', $kontenrahmen_id);

            $anz_e = count($einnahme_konten_arr);
            $e_summe = 0;
            echo "<table>";
            echo "<thead><tr><th colspan=\"4\">HAUSGELDEINNAHMEN AUS BUCHUNGSJOURNAL</th></tr></thead>";
            echo "<tr><th>KONTO</th><th>KONTOART</th><th>BEZEICHNUNG</th><th>BETRAG</th></tr>";

            for ($a = 0; $a < $anz_e; $a++) {
                $kbez = $einnahme_konten_arr [$a] ['BEZEICHNUNG'];
                $ekonto = $einnahme_konten_arr [$a] ['KONTO'];
                $bb->summe_kontobuchungen_jahr($this->p_gk_id, $ekonto, $this->p_jahr);
                $summe_ekonto = $bb->summe_konto_buchungen;
                if ($summe_ekonto) {
                    // echo "$kbez $summe_ekonto<br>";
                    $summe_ekonto_a = nummer_punkt2komma($summe_ekonto);
                    echo "<tr><td>$ekonto</td><td>Einnahmen</td><td>$kbez</td><td>$summe_ekonto_a</td></tr>";
                }
                $e_summe += $summe_ekonto;
            }
            if (!$e_summe) {

            }

            $e_summe_a = nummer_punkt2komma($e_summe);
            echo "<tfoot><tr><td></td><td></td><th><b>SUMME EINNAHMEN</b></th><th><b>$e_summe_a</b></th></tr></tfoot>";
            echo "</table>";
        } else {
            $anz_m = count($einnahme_manuell);
            echo "<table>";
            echo "<thead><tr><th colspan=\"4\">HAUSGELDEINNAHMEN MANUELL</th></tr></thead>";
            echo "<tr><th>KONTO</th><th>KONTOART</th><th>BEZEICHNUNG</th><th>BETRAG</th></tr>";
            $e_summe = 0;
            for ($a = 0; $a < $anz_m; $a++) {
                $ki = new kontenrahmen ();

                $konto = $einnahme_manuell [$a] ['KOSTENKONTO'];
                $ki->konto_informationen2($konto, $kontenrahmen_id);
                $ksumme = $einnahme_manuell [$a] ['SUMME'];
                $ksumme_a = nummer_punkt2komma($ksumme);
                $e_summe += $ksumme;
                echo "<tr><td>$konto</td><td>$ki->konto_art_bezeichnung</td><td>$ki->konto_bezeichnung</td><td>$ksumme_a</td></tr>";
            }
            $e_summe_a = nummer_punkt2komma($e_summe);
            echo "<tfoot><tr><td></td><td></td><th><b>SUMME EINNAHMEN</b></th><th><b>$e_summe_a</b></th></tr></tfoot>";
            echo "<table>";
        }

        $anz_k = count($_umlage_ktos);

        echo "<table>";
        echo "<thead><tr><th colspan=\"4\">BEWIRTSCHAFTUNGSKOSTEN/-EINNAHMEN</th></tr></thead>";
        echo "<tr><th>Konto</th><th>Kontoart</th><th>Text</th><th>Betrag</th></tr>";

        $znr = 0;
        $g_summe = 0;
        for ($a = 0; $a < $anz_k; $a++) {
            $konto = $_umlage_ktos [$a] ['KONTO'];
            $gruppe = $_umlage_ktos [$a] ['GRUPPE'];

            $betraege_arr = $this->get_betraege_arr($p_id, $konto);
            $anz_b = count($betraege_arr);

            for ($b = 0; $b < $anz_b; $b++) {
                $konto_b = $betraege_arr [$b] ['KONTO'];
                $text = $betraege_arr [$b] ['TEXT'];
                $gen_key_id = $betraege_arr [$b] ['GEN_KEY_ID'];
                $betrag = $betraege_arr [$b] ['BETRAG'];
                $betrag_hndl = $betraege_arr [$b] ['HNDL_BETRAG'];
                $kos_typ = $betraege_arr [$b] ['KOS_TYP'];
                $kos_id = $betraege_arr [$b] ['KOS_ID'];

                $r = new rechnung ();
                $kos_bez = $r->kostentraeger_ermitteln($kos_typ, $kos_id);

                $bk = new bk ();
                $bk->get_genkey_infos($gen_key_id);
                $betrag_a = nummer_punkt2komma($betrag);
                echo "<tr><td>$konto_b</td><td>$gruppe</td><td>$text</td><td>$betrag_a</td></tr>";

                if ($kos_typ == 'Wirtschaftseinheit') {
                    $wi = new wirt_e ();
                    $zeilen_arr [$znr] ['KONTO'] = $konto;
                    $zeilen_arr [$znr] ['KONTOB'] = $konto_b;
                    $zeilen_arr [$znr] ['BETRAG'] = $betrag;
                    $zeilen_arr [$znr] ['HNDL_BETRAG'] = $betrag_hndl;
                    $zeilen_arr [$znr] ['GEN_KEY_ID'] = $gen_key_id;
                    $zeilen_arr [$znr] ['KOS_TYP'] = $kos_typ;
                    $zeilen_arr [$znr] ['KOS_ID'] = $kos_id;
                    $zeilen_arr [$znr] ['TEXT'] = $text;
                    $zeilen_arr [$znr] ['GRUPPE'] = $gruppe;
                    $zeilen_arr [$znr] ['EINHEITEN'] = $wi->get_einheiten_from_wirte($kos_id);
                    $g_summe += $betrag;
                }
                $znr++;
            }
        }
        $g_summe_a = nummer_punkt2komma($g_summe);
        echo "<tfoot><tr><td></td><td></td><th><b>AUSGABEN GESAMT</b></th><th><b>$g_summe_a</b></th></tr></tfoot>";
        echo "</table>";

        /* Art = Ausgaben, Einnahmen, Mittelverwendung */
        $_umlage_ktos = $this->get_hgkonten_arr($p_id, 'Mittelverwendung');
        $_umlage_ktos_sort = array_sortByIndex($_umlage_ktos, 'GRUPPE', SORT_DESC);
        $_umlage_ktos = $_umlage_ktos_sort;
        unset ($_umlage_ktos_sort);

        $anz_k = count($_umlage_ktos);

        echo "<table>";
        echo "<thead><tr><th colspan=\"4\">MITTELVERWENDUNG</th></tr></thead>";
        echo "<tr><th>Konto</th><th>Kontoart</th><th>Text</th><th>Betrag</th></tr>";

        $znr = 0;
        $g_summe1 = 0;
        for ($a = 0; $a < $anz_k; $a++) {
            $konto = $_umlage_ktos [$a] ['KONTO'];
            $gruppe = $_umlage_ktos [$a] ['GRUPPE'];

            $betraege_arr = $this->get_betraege_arr($p_id, $konto);

            $anz_b = count($betraege_arr);

            for ($b = 0; $b < $anz_b; $b++) {
                $konto_b = $betraege_arr [$b] ['KONTO'];
                $text = $betraege_arr [$b] ['TEXT'];
                $gen_key_id = $betraege_arr [$b] ['GEN_KEY_ID'];
                $betrag = $betraege_arr [$b] ['BETRAG'];
                $betrag_hndl = $betraege_arr [$b] ['HNDL_BETRAG'];
                $kos_typ = $betraege_arr [$b] ['KOS_TYP'];
                $kos_id = $betraege_arr [$b] ['KOS_ID'];

                $r = new rechnung ();
                $kos_bez = $r->kostentraeger_ermitteln($kos_typ, $kos_id);

                $bk = new bk ();
                $bk->get_genkey_infos($gen_key_id);
                $betrag_a = nummer_punkt2komma($betrag);
                echo "<tr><td>$konto_b</td><td>$gruppe</td><td>$text</td><td>$betrag_a</td></tr>";

                if ($kos_typ == 'Wirtschaftseinheit') {
                    $wi = new wirt_e ();
                    $zeilen_arr [$znr] ['KONTO'] = $konto;
                    $zeilen_arr [$znr] ['KONTOB'] = $konto_b;
                    $zeilen_arr [$znr] ['BETRAG'] = $betrag;
                    $zeilen_arr [$znr] ['HNDL_BETRAG'] = $betrag_hndl;
                    $zeilen_arr [$znr] ['GEN_KEY_ID'] = $gen_key_id;
                    $zeilen_arr [$znr] ['KOS_TYP'] = $kos_typ;
                    $zeilen_arr [$znr] ['KOS_ID'] = $kos_id;
                    $zeilen_arr [$znr] ['TEXT'] = $text;
                    $zeilen_arr [$znr] ['GRUPPE'] = $gruppe;
                    $zeilen_arr [$znr] ['EINHEITEN'] = $wi->get_einheiten_from_wirte($kos_id);
                    $g_summe1 += $betrag;
                }
                $znr++;
            }
        }
        $g_summe1_a = nummer_punkt2komma($g_summe1);
        echo "<tfoot><tr><td></td><td></td><th><b>GESAMT</b></th><th><b>$g_summe1_a</b></th></tr></tfoot>";
        echo "</table>";

        $ergebnis = $kontostand11 + $e_summe + $g_summe + $g_summe1;
        $ergebnis_a = nummer_punkt2komma($ergebnis);
        if ($ergebnis > 0) {
            $erg_text = 'GUTHABEN';
        }
        if ($ergebnis < 0) {
            $erg_text = 'NACHZAHLUNG';
        }
        echo "<table>";
        echo "<thead><tr><th colspan=\"2\">BERECHNUNG</th></tr></thead>";
        echo "<tr><th>BEZEICHNUNG</th><th>Betrag</th></tr>";
        echo "<tr><td>KONTOSTAND 1.1.$this->p_jahr</td><td>$kontostand11_a</td></tr>";
        echo "<tr><td>EINNAHMEN</td><td>$e_summe_a</td></tr>";
        echo "<tr><td>KOSTEN/EINNAHMEN</td><td>$g_summe_a</td></tr>";
        echo "<tr><td>MITTELVERWENDUNG</td><td>$g_summe1_a</td></tr>";
        echo "<tfoot><tr><th><b>SALDO ($erg_text)</b></th><th><b>$ergebnis_a</b></th></tr></tfoot>";
        echo "</table>";
    }

    function tage_zwischen($datum1, $datum2)
    {
        $alt = strtotime($datum1);
        $aktuell = strtotime($datum2);

        $differenz = $aktuell - $alt;
        $differenz = $differenz / 86400;

        return intval($differenz);
    }

    function hga_einzeln($p_id = '0')
    {
        $this->get_hga_profil_infos($p_id);
        /* Tage im Jahr */
        $jahr = $this->p_jahr;
        $bk = new bk ();
        if (empty ($this->p_von) or empty ($this->p_bis)) {
            $tj = $bk->tage_im_jahr($jahr);
        } else {
            $bk = new bk ();
            $tj = $bk->diff_in_tagen($this->p_von, $this->p_bis) + 1;
        }
        /* Art = Ausgaben, Einnahmen, Mittelverwendung */
        $_umlage_ktos = $this->get_hgkonten_arr($p_id, 'Ausgaben/Einnahmen', false);
        $_umlage_ktos_sort = array_sortByIndex($_umlage_ktos, 'GRUPPE', 'DESC');
        $_umlage_ktos = $_umlage_ktos_sort;
        unset ($_umlage_ktos_sort);

        $anz_k = count($_umlage_ktos);
        if (!$anz_k) {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\WarningMessage('Keine Kosten im Profil. Bitte Kostenkonten hinzufügen')
            );
        }

        for ($a = 0; $a < $anz_k; $a++) {
            $konto = $_umlage_ktos [$a] ['KONTO'];
            $betraege_arr [] = $this->get_betraege_arr($p_id, $konto);
        }

        $einheiten_arr = $this->einheiten_weg_tabelle_arr($this->p_objekt_id);
        /* Alle Einheiten durchlaufen um die Eigentümer und Tage zu Sammeln */
        $anz_einheiten = count($einheiten_arr);
        for ($b = 0; $b < $anz_einheiten; $b++) {
            $einheit_id = $einheiten_arr [$b] ['EINHEIT_ID'];

            $eig_arr = $this->get_eigentuemer_arr_jahr($einheit_id, $this->p_jahr);
            // print_r($eig_arr);
            $anz_eig = count($eig_arr);
            /* Eigentuemer untereinander */
            for ($c = 0; $c < $anz_eig; $c++) {
                $eig_arr_1 = $eig_arr [$c];
                $einheiten_arr_eig [] = $eig_arr_1;
            }
        }

        unset ($einheiten_arr);

        $anz_eig = count($einheiten_arr_eig);

        /* Kontenrahmen vom Geldkonto holen */
        $k = new kontenrahmen ();
        $kontenrahmen_id = $k->get_kontenrahmen('Geldkonto', $this->p_gk_id);
        /**
         * **********PROGRAMMABBRUCH OHNE KONTENRAHMEN**************
         */
        if (!$kontenrahmen_id) {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\ErrorMessage('Kontenrahmen zum Geldkonto fehlt, bitte zuweisen!')
            );
        }

        $su_konten_im_kontenrahmen = collect(DB::table('KONTENRAHMEN_KONTEN')
            ->where('AKTUELL', '1')
            ->where('SONDERUMLAGE', '1')
            ->where('KONTENRAHMEN_ID', $kontenrahmen_id)
            ->get());

        /* Jeden Eigentuemer für alle Konten durchlaufen */
        $anz_b = count($betraege_arr); // Anzahl Konten

        for ($a = 0; $a < $anz_eig; $a++) {
            $eigentuemer_id = $einheiten_arr_eig [$a] ['ID'];

            $tab_arr [$a] ['EIG_ID'] = $eigentuemer_id;
            $tab_arr [$a] ['EINHEIT_ID'] = $einheiten_arr_eig [$a] ['EINHEIT_ID'];
            $tab_arr [$a] ['VON'] = $einheiten_arr_eig [$a] ['VON'];
            $tab_arr [$a] ['BIS'] = $einheiten_arr_eig [$a] ['BIS'];
            $tab_arr [$a] ['TAGE'] = $einheiten_arr_eig [$a] ['TAGE'];

            $su_def_zeilen_alle = collect(DB::table('WEG_WG_DEF')
                ->whereIn('E_KONTO', $su_konten_im_kontenrahmen->pluck('KONTO'))
                ->where('KOS_TYP', 'Einheit')
                ->where('KOS_ID', $einheiten_arr_eig [$a] ['EINHEIT_ID'])
                ->where('AKTUELL', '1')
                ->whereYear('ANFANG', '<=', $this->p_jahr)
                ->selectRaw('E_KONTO + 1 AS E_KONTO, KOSTENKAT')
                ->groupBy('E_KONTO')
                ->orderBy('KOSTENKAT')
                ->get());

            /* Jedes Konto durchlaufen, Daten in Tab und für Eigentuemer berechnen */
            $g_summe_aller_kosten = 0;
            $hndl_z = 0;
            for ($b = 0; $b < $anz_b; $b++) {
                $konto = $betraege_arr [$b] ['0'] ['KONTO'];
                $k->konto_informationen2($konto, $kontenrahmen_id);

                $gen_key_id = $betraege_arr [$b] ['0'] ['GEN_KEY_ID'];
                $bk = new bk ();
                $bk->get_genkey_infos($gen_key_id);
                $betrag = $betraege_arr [$b] ['0'] ['BETRAG'];
                $g_summe_aller_kosten += $betrag;
                $hndl_betrag = $betraege_arr [$b] ['0'] ['HNDL_BETRAG'];
                $kos_typ = $betraege_arr [$b] ['0'] ['KOS_TYP'];
                $kos_id = $betraege_arr [$b] ['0'] ['KOS_ID'];
                $su_auszahlen = $betraege_arr [$b] ['0'] ['SU_AUSZAHLEN'] == '1';

                $tab_arr [$a] ['ZEILEN'] [$b] ['KONTO'] = $konto;
                $tab_arr [$a] ['ZEILEN'] [$b] ['KONTO_BEZ'] = $k->konto_bezeichnung;
                $tab_arr [$a] ['ZEILEN'] [$b] ['KONTO_ART'] = $k->konto_art_bezeichnung;
                $tab_arr [$a] ['ZEILEN'] [$b] ['GRUPPE'] = $k->konto_gruppen_bezeichnung;
                $tab_arr [$a] ['ZEILEN'] [$b] ['BETRAG'] = number_format($betrag, 2, ',', '.');
                $tab_arr [$a] ['ZEILEN'] [$b] ['HNDL_BETRAG'] = nummer_punkt2komma($hndl_betrag);

                $tab_arr [$a] ['ZEILEN'] [$b] ['ART'] = $betraege_arr [$b] ['0'] ['ART'];
                if ($hndl_betrag == '0.00') {
                    $tab_arr [$a] ['ZEILEN'] [$b] ['BEZ'] = $betraege_arr [$b] ['0'] ['TEXT'];
                } else {
                    $tab_arr [$a] ['ZEILEN'] [$b] ['BEZ'] = $betraege_arr [$b] ['0'] ['TEXT'] . ' *';
                }

                $r = new rechnung ();
                $tab_arr [$a] ['ZEILEN'] [$b] ['KOS_BEZ'] = $r->kostentraeger_ermitteln($kos_typ, $kos_id);

                $einheit_id = $tab_arr [$a] ['EINHEIT_ID'];

                if (isset ($this->berechnen)) {
                    unset ($this->berechnen);
                }

                if ($kos_typ == 'Wirtschaftseinheit') {
                    $wi = new wirt_e ();
                    /* Hier weiter mt g_value */
                    $g_value = $this->key_daten_gesamt($gen_key_id, 'Wirtschaftseinheit', $kos_id);

                    if (isset ($this->$ein_berr)) {
                        unset ($this->ein_berr);
                    }

                    if ($wi->check_einheit_in_we($einheit_id, $kos_id)) {
                        $this->berechnen = '1';
                    } else {
                        $this->berechnen = '0';
                    }
                }

                /* Berechnungsquote ermitteln pro Konto */
                $d = new detail ();
                $e = new einheit ();
                $e->get_einheit_info($einheit_id);
                if ($gen_key_id == 1) {
                    $e_anteile = nummer_punkt2komma($e->einheit_qm);
                }
                if ($gen_key_id == 2) {
                    $e_anteile = 1;
                }
                if ($gen_key_id == 3) {
                    $e_anteile = $d->finde_detail_inhalt('Einheit', $einheit_id, 'WEG-Anteile');
                }
                if ($gen_key_id == 4) {
                    $e_anteile = 0.00;
                    $g_value = $betrag;
                }
                /* Aufzug nach Prozent */
                if ($gen_key_id == 5) {
                    $e_anteile = $d->finde_detail_inhalt('Einheit', $einheit_id, 'WEG-Aufzugprozent');
                    $g_value = 100;
                }

                $tage = $tab_arr [$a] ['TAGE'];

                /* Prüfen ob Einheit im Array der Aufteilung */
                if ($this->berechnen == 0) {
                    $e_anteile = 0;
                    $tab_arr [$a] ['ZEILEN'] [$b] ['G_KEY_A'] = number_format($g_value, 2, ',', '') . ' ' . $bk->g_key_me;
                    $tab_arr [$a] ['ZEILEN'] [$b] ['E_KEY_A'] = number_format($e_anteile, 2, ',', '') . ' ' . $bk->g_key_me;
                    $tab_arr [$a] ['ZEILEN'] [$b] ['KEY_A'] = $tab_arr [$a] ['ZEILEN'] [$b] ['E_KEY_A'] . ' / ' . $tab_arr [$a] ['ZEILEN'] [$b] ['G_KEY_A'];
                }

                if ($this->berechnen == 1) {
                    $betrag = number_format($betrag, 2, '.', '');
                    if ($su_def_zeilen_alle->contains('E_KONTO', $konto)) {
                        $su_soll_alle = $this->get_summe_konto($this->p_gk_id, $konto, $this->p_jahr);
                        $su_soll_vorjahre = $this->get_summe_konto($this->p_gk_id, $konto, $this->p_jahr - 1);
                        $su_soll_abrechnungsjahr = $su_soll_alle - $su_soll_vorjahre;
                    }
                    $e_anteile = punkt_zahl($e_anteile);
                    $e_anteile_a = number_format($e_anteile, 2, ',', '.');

                    $g_value = punkt_zahl($g_value);
                    $g_value_a = number_format($g_value, 2, ',', '.');

                    $bet = ((($betrag * $e_anteile) / $g_value) * $tage) / $tj;
                    $bet = number_format($bet, 2, '.', '');
                    $su_soll_alle = ((($su_soll_alle * $e_anteile) / $g_value) * $tage) / $tj;
                    $su_soll_alle = number_format($su_soll_alle, 2, '.', '');
                    $su_soll_vorjahre = ((($su_soll_vorjahre * $e_anteile) / $g_value) * $tage) / $tj;
                    $su_soll_vorjahre = number_format($su_soll_vorjahre, 2, '.', '');
                    $su_soll_abrechnungsjahr = ($su_soll_abrechnungsjahr * $e_anteile) / $g_value;
                    $su_soll_abrechnungsjahr = number_format($su_soll_abrechnungsjahr, 2, '.', '');
                    $bet_hndl = ((($hndl_betrag * $e_anteile) / $g_value) * $tage) / $tj;
                    $tab_arr [$a] ['ZEILEN'] [$b] ['G_KEY_NAME'] = $bk->g_key_name;
                    $tab_arr [$a] ['ZEILEN'] [$b] ['G_KEY_ME'] = $bk->g_key_me;
                    $tab_arr [$a] ['ZEILEN'] [$b] ['G_KEY_A'] = $g_value_a . ' ' . $bk->g_key_me;
                    $tab_arr [$a] ['ZEILEN'] [$b] ['E_KEY_A'] = $e_anteile_a . ' ' . $bk->g_key_me;
                    $tab_arr [$a] ['ZEILEN'] [$b] ['KEY_A'] = $e_anteile_a . ' ' . $bk->g_key_me . ' / ' . $g_value_a . ' ' . $bk->g_key_me;
                } else {
                    $bet = 0.00;
                    $su_soll_alle = 0.00;
                    $su_soll_vorjahre = 0.00;
                    $su_soll_abrechnungsjahr = 0.00;
                    $bet_hndl = 0.00;
                }

                /* HNDL Betrag */
                $bet_a = nummer_punkt2komma_t($bet);
                $su_bet_a = nummer_punkt2komma_t($su_bet);
                $bet_hndl_a = nummer_punkt2komma_t($bet_hndl);

                $tab_arr [$a] ['ZEILEN'] [$b] ['E_BETRAG'] = $bet_a;
                $tab_arr [$a] ['ZEILEN'] [$b] ['E_BETRAG_HNDL'] = $bet_hndl_a;
                $tab_arr [$a] ['ZEILEN'] [$b] ['E_SU_ALLE_NUMBER'] = $su_soll_alle;
                $tab_arr [$a] ['ZEILEN'] [$b] ['E_SU_VORJAHRE_NUMBER'] = $su_soll_vorjahre;
                $tab_arr [$a] ['ZEILEN'] [$b] ['E_SU_ABRECHNUNGSJAHR_NUMBER'] = $su_soll_abrechnungsjahr;
                $tab_arr [$a] ['ZEILEN'] [$b] ['E_SU_JAHRESANTEIL'] = $tage / $tj;
                $tab_arr [$a] ['ZEILEN'] [$b] ['E_SU_AUSZAHLEN'] = $su_auszahlen;
                $tab_arr [$a] ['ZEILEN'] [$b] ['E_BETRAG_NUMBER'] = $bet;
                $tab_arr [$a] ['ZEILEN'] [$b] ['E_BETRAG_HNDL_NUMBER'] = $bet_hndl;

                /* Ergebnistabelle füttern */
                $eigent_id = $tab_arr [$a] ['EIG_ID'];

                $tab_erg ['BETRAG'] [$eigent_id] ['BETEILIGUNG'] += nummer_komma2punkt($bet_a);

                if ($bet_hndl != 0.00) {
                    $tab_erg ['BETRAG_HNDL'] [$eigent_id] ['BETEILIGUNG'] += nummer_komma2punkt($bet_hndl_a);

                    $hndl_arr [$eigent_id] ['ZEILEN'] [$hndl_z] ['KONTO'] = $konto;
                    $hndl_arr [$eigent_id] ['ZEILEN'] [$hndl_z] ['KONTO_BEZ'] = $k->konto_bezeichnung;
                    $hndl_arr [$eigent_id] ['ZEILEN'] [$hndl_z] ['KONTO_ART'] = $k->konto_art_bezeichnung;
                    $hndl_arr [$eigent_id] ['ZEILEN'] [$hndl_z] ['GRUPPE'] = $k->konto_gruppen_bezeichnung;
                    $hndl_arr [$eigent_id] ['ZEILEN'] [$hndl_z] ['BETRAG'] = nummer_punkt2komma_t($betrag);
                    $hndl_arr [$eigent_id] ['ZEILEN'] [$hndl_z] ['BETRAG_HNDL'] = nummer_punkt2komma_t($hndl_betrag);
                    $hndl_arr [$eigent_id] ['ZEILEN'] [$hndl_z] ['G_KEY_NAME'] = $bk->g_key_name;
                    $hndl_arr [$eigent_id] ['ZEILEN'] [$hndl_z] ['G_KEY_ME'] = $bk->g_key_me;

                    $hndl_arr [$eigent_id] ['ZEILEN'] [$hndl_z] ['E_KEY_A'] = $e_anteile_a . ' ' . $bk->g_key_me;
                    $hndl_arr [$eigent_id] ['ZEILEN'] [$hndl_z] ['E_BETRAG_HNDL'] = $bet_hndl_a;
                    $hndl_arr [$eigent_id] ['ZEILEN'] [$hndl_z] ['KOS_BEZ'] = $r->kostentraeger_ermitteln($kos_typ, $kos_id);
                    $hndl_arr [$eigent_id] ['ZEILEN'] [$hndl_z] ['BEZ'] = $betraege_arr [$b] ['0'] ['TEXT'] . ' *';
                    $hndl_arr [$eigent_id] ['ZEILEN'] [$hndl_z] ['G_KEY_A'] = $g_value_a . ' ' . $bk->g_key_me;
                    $hndl_arr [$eigent_id] ['ZEILEN'] [$hndl_z] ['SCHLUESSEL'] = $e_anteile_a . ' ' . $bk->g_key_me . ' / ' . $g_value_a . ' ' . $bk->g_key_me;
                    $hndl_z++;
                }
            } // end for Konten $b

            //Heizkosten
            $hk_verbrauch_ist = $this->get_summe_hk('Eigentuemer', $eigentuemer_id, $p_id);
            $tab_arr [$a] ['ZEILEN'] [$b] ['KONTO'] = $this->hk_konto;
            $tab_arr [$a] ['ZEILEN'] [$b] ['KONTO_BEZ'] = "Heiz- und Wassererwärmungskosten";
            $tab_arr [$a] ['ZEILEN'] [$b] ['KONTO_ART'] = "Ausgaben";
            $tab_arr [$a] ['ZEILEN'] [$b] ['GRUPPE'] = "Umlagefähige Kosten";
            $tab_arr [$a] ['ZEILEN'] [$b] ['BEZ'] = "Heiz- und Wassererwärmungskosten";
            $tab_arr [$a] ['ZEILEN'] [$b] ['ART'] = "Ausgaben/Einnahmen";
            $tab_arr [$a] ['ZEILEN'] [$b] ['KEY_A'] = "gem. externer Abrechnung";
            $tab_arr [$a] ['ZEILEN'] [$b] ['E_BETRAG'] = nummer_punkt2komma_t($hk_verbrauch_ist);
            $tab_arr [$a] ['ZEILEN'] [$b] ['HNDL_BETRAG'] = 0;
            $tab_arr [$a] ['ZEILEN'] [$b] ['E_BETRAG_HNDL'] = 0;
            $tab_erg ['BETRAG'] [$eigent_id] ['BETEILIGUNG'] += $hk_verbrauch_ist;
        } // end for Eigentuemer $a

        $tab_erg ['BETRAG'] ['G_SUMME'] = $g_summe_aller_kosten;

        $anz = count($tab_arr);
        for ($a = 0; $a < $anz; $a++) {
            $eig_id = $tab_arr [$a] ['EIG_ID'];
            $zeilen = $tab_arr [$a] ['ZEILEN'];
            $anz_z = count($zeilen);
            for ($b = 0; $b < $anz_z; $b++) {
                $konto = $zeilen [$b] ['KONTO'];
                $bez = $zeilen [$b] ['BEZ'];
                $betrag = $zeilen [$b] ['BETRAG'];
                $kos_bez = $zeilen [$b] ['KOS_BEZ'];
                $e_betrag = $zeilen [$b] ['E_BETRAG'];
                if ($b == ($anz_z - 1)) {
                    $g_summe = nummer_punkt2komma_t($tab_erg ['BETRAG'] ['G_SUMME']);
                    $g_bet = nummer_punkt2komma_t($tab_erg ['BETRAG'] [$eig_id] ['BETEILIGUNG']);
                    $tab_arr [$a] ['ZEILEN'] [$b + 1] ['BETRAG'] = $g_summe;
                    $tab_arr [$a] ['ZEILEN'] [$b + 1] ['E_BETRAG'] = "<b>" . $g_bet . "</b>";
                    $tab_arr [$a] ['ZEILEN'] [$b + 1] ['U_BETRAG'] = $g_bet;
                    $tab_arr [$a] ['ZEILEN'] [$b + 1] ['GRUPPE'] = "<b>Zwischensumme zu 1.</b>";
                }
            }
            $g_summe = nummer_punkt2komma_t($tab_erg ['BETRAG'] ['G_SUMME']);
            $g_bet = nummer_punkt2komma_t($tab_erg ['BETRAG'] [$eig_id] ['BETEILIGUNG']);
        }

        /* Gesamtbeteiligung als letzte Zeile hinzufügen */
        $pdf = new Cezpdf ('a4', 'portrait');
        $bpdf = new b_pdf ();
        $bpdf->b_header($pdf, 'Partner', session()->get('partner_id'), 'portrait', 'Helvetica.afm', 6);
        //$pdf->ezStopPageNumbers(); // seitennummerirung beenden
        $p = new partners ();
        $p->get_partner_info(session()->get('partner_id'));
        $datum = date("d.m.Y");

        $anz = count($tab_arr);
        for ($a = 0; $a < $anz; $a++) {

            /* Bei jedem Eigentümer, mit neuer Seite anfangen */
            if ($a > 0) {
                $pdf->ezNewPage();
                $pdf->ezStartPageNumbers(545, 715, 6, '', 'Seite {PAGENUM} von {TOTALPAGENUM}', 1);
                $pdf->setColor(0, 0, 0);
                $pdf->ezSetDy(-5);
            }

            $eig_id = $tab_arr [$a] ['EIG_ID'];
            $this->n_tage = $tab_arr [$a] ['TAGE'];
            /* Kopf Anfang */
            $einheit_id = $tab_arr [$a] ['EINHEIT_ID'];
            $this->einheit_id = $einheit_id;
            $e = new einheit ();
            $e->get_einheit_info($einheit_id);
            $oo = new objekt ();
            $anz_einheiten = $oo->anzahl_einheiten_objekt($e->objekt_id);
            $pdf->setColor(0, 0, 0);
            $pdf->setStrokeColor(0, 0, 0);
            $pdf->setLineStyle(0.5);
            $pdf->rectangle(400, 601, 165, 87);
            $this->get_eigentumer_id_infos2($eig_id);
            $this->get_anrede_eigentuemer($eig_id);

            $standard_anschrift = str_replace('<br>', "\n", end($this->postanschrift));
            if (!empty ($standard_anschrift)) {
                $pdf->ezText("$standard_anschrift", 10);
            } else {
                $pdf->ezText("$this->eig_namen_u_pdf", 10);
                $pdf->ezSetDy(10);
                $pdf->ezText("$this->haus_strasse $this->haus_nummer", 10);
                $pdf->ezSetDy(-10);
                $pdf->ezText("$this->haus_plz $this->haus_stadt", 10);
            }

            $d = new detail ();
            $anteile_g = $d->finde_detail_inhalt('Objekt', $e->objekt_id, 'Gesamtanteile');
            $pdf->addText(405, 680, 8, "Einheiten:");
            $pdf->addText(465, 680, 8, "$anz_einheiten");
            $pdf->addText(405, 670, 8, "Einheit:");
            $pdf->addText(465, 670, 8, "$e->einheit_kurzname");
            $pdf->addText(405, 660, 8, "Gesamtanteile:");
            $pdf->addText(465, 660, 8, "$anteile_g");
            $this->einheit_anteile = $d->finde_detail_inhalt('Einheit', $einheit_id, 'WEG-Anteile');
            $pdf->addText(405, 650, 8, "Ihre MEA:");
            $pdf->addText(465, 650, 8, "$this->einheit_anteile");

            $e->einheit_qm_a = nummer_punkt2komma($e->einheit_qm);
            $pdf->addText(405, 640, 8, "Fläche:");
            $pdf->addText(465, 640, 8, "$e->einheit_qm_a m²");

            $pdf->addText(405, 630, 8, "Aufzug %:");
            $pdf->addText(465, 630, 8, "$e->aufzug_prozent_d");

            $this->eigentuemer_von_d = date_mysql2german($this->eigentuemer_von);
            $this->eigentuemer_bis_d = date_mysql2german($this->eigentuemer_bis);
            if ($this->eigentuemer_bis_d == '00.00.0000') {
                $this->eigentuemer_bis_d = "31.12.$jahr";
            }
            $e_jahr_arr = explode(".", $this->eigentuemer_bis_d);
            $e_jahr = $e_jahr_arr [2];
            if ($e_jahr > $jahr) {
                $this->eigentuemer_bis_d = "31.12.$jahr";
            }

            $e_ajahr_arr = explode(".", $this->eigentuemer_von_d);
            $e_ajahr = $e_ajahr_arr [2];
            if ($e_ajahr < $jahr) {
                $this->eigentuemer_von_d = "01.01.$jahr";
            }

            $pdf->addText(405, 618, 7, "Zeitraum:");
            if ($this->p_von && $this->p_bis) {
                $pdf->addText(465, 618, 7, "$this->p_von_d - $this->p_bis_d");
            } else {
                $pdf->addText(465, 618, 7, "01.01.$jahr - 31.12.$jahr");
            }
            $pdf->addText(405, 611, 7, "Nutzungszeitraum: ");
            $pdf->addText(465, 611, 7, "$this->eigentuemer_von_d - $this->eigentuemer_bis_d");
            $pdf->addText(405, 604, 7, "Tage:");
            $pdf->addText(465, 604, 7, "$this->n_tage von $tj");
            $pdf->ezSetY(590);
            $pdf->ezText("$p->partner_ort, $datum", 10, array(
                'justification' => 'right'
            ));
            $pdf->ezSetY(590);

            $pdf->ezText("$this->pdf_anrede", 10);
            $pdf->ezText("beiliegend übersenden wir Ihnen die Hausgeld-Einzelabrechnung zur Jahresabrechnung $jahr.", 10);

            $pdf->ezSetDy(-10);

            $zeilen = array_orderby($tab_arr [$a] ['ZEILEN'], 'GRUPPE', SORT_DESC, 'KONTO', SORT_ASC);

            $cols = array(
                'GRUPPE' => "Kontoart",
                'KONTO' => "Konto",
                'BEZ' => "Kontobezeichnung",
                'KEY_A' => "Verteilungsschlüssel (Ihr Anteil / Gesamt)",
                'BETRAG' => "Gesamt €",
                'E_BETRAG' => "Ihr Anteil €"
            );
            $pdf->ezTable($zeilen, $cols, "<b>1. Bewirtschaftungskosten</b>", array(
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
                'xPos' => 40,
                'xOrientation' => 'right',
                'width' => 530,
                'cols' => array(
                    'KONTO' => array(
                        'justification' => 'left',
                        'width' => 32
                    ),
                    'GRUPPE' => array(
                        'justification' => 'left',
                        'width' => 95
                    ),
                    'BETRAG' => array(
                        'justification' => 'right',
                        'width' => 45
                    ),
                    'KOS_BEZ' => array(
                        'justification' => 'left',
                        'width' => 65
                    ),
                    'KEY_A' => array(
                        'justification' => 'right',
                    ),
                    'E_BETRAG' => array(
                        'justification' => 'right',
                        'width' => 50
                    )
                )
            ));
            $pdf->ezText("<b> *) Kostenkonto beinhaltet haushaltsnahe Dienstleistungen</b>", 8);

            $g = new geldkonto_info ();
            $g->geld_konto_details($this->p_gk_id);

            $this->get_eigentumer_id_infos2($eig_id);

            if (str_replace('-', '', $this->eigentuemer_von) < "$jahr" . '0101') {
                $this->eigentuemer_von_t = "$jahr-01-01";
            } else {
                $this->eigentuemer_von_t = $this->eigentuemer_von;
            }

            if (str_replace('-', '', $this->eigentuemer_bis) > "$jahr" . '1231') {
                $this->eigentuemer_bis_t = "$jahr-12-31";
            } else {
                $this->eigentuemer_bis_t = $this->eigentuemer_bis;
            }

            if ($this->eigentuemer_bis == '0000-00-00') {
                $this->eigentuemer_bis_t = "$jahr-12-31";
            }

            $g = new geldkonto_info ();
            $g->geld_konto_details($this->p_gk_id);
            $geldkonto_id = $this->p_gk_id;

            /* Zwischenergebnis 1 */
            $zw1 = $tab_erg ['BETRAG'] [$eig_id] ['BETEILIGUNG'];

            /* Hier noch 6020 als CONST, in VAR ändern */

            $this->eigentuemer_von_t_a = date_mysql2german($this->eigentuemer_von_t);
            $this->eigentuemer_bis_t_a = date_mysql2german($this->eigentuemer_bis_t);

            /* Instandhaltungstabelle */
            $inst_kosten_soll = $this->hg_tab_soll_ist_einnahmen($this->ihr_konto, 'Einheit', $this->einheit_id, $this->eigentuemer_von_t, $this->eigentuemer_bis_t);
            $ru_tab = [];
            $ru_tab [0] ['ART'] = "Zuführung zur Instandhaltungsrücklage";
            $ru_tab [0] ['ANTEIL'] = '-' . nummer_punkt2komma($inst_kosten_soll);

            $su_im_wirtschaftsjahr = DB::table('WEG_WG_DEF')
                ->whereIn('E_KONTO', $su_konten_im_kontenrahmen->pluck('KONTO'))
                ->where('KOS_TYP', 'Einheit')
                ->where('KOS_ID', $this->einheit_id)
                ->where('AKTUELL', '1')
                ->whereYear('ANFANG', '<=', $this->p_jahr)
                ->where(function ($query) {
                    $query->whereYear('ENDE', '>=', $this->p_jahr)
                        ->orWhere('ENDE', '0000-00-00');
                })
                ->select(['E_KONTO', 'KOSTENKAT'])
                ->groupBy('E_KONTO')
                ->orderBy('KOSTENKAT')
                ->get();

            $su_kosten_summe = 0;

            foreach ($su_im_wirtschaftsjahr as $su) {
                $ru_zeile = [];
                $su_kosten_soll = $this->hg_tab_soll_ist_einnahmen($su['E_KONTO'], 'Einheit', $this->einheit_id, $this->eigentuemer_von_t, $this->eigentuemer_bis_t);
                $su_kosten_summe += $su_kosten_soll;
                $ru_zeile['ART'] = "Zuführung zur Rücklage aus Sonderumlage ($su[KOSTENKAT])";
                $ru_zeile['ANTEIL'] = '-' . nummer_punkt2komma($su_kosten_soll);
                $ru_tab[] = $ru_zeile;
            }

            $su_ausgaben_summe = 0;

            $su_def_zeilen_alle = DB::table('WEG_WG_DEF')
                ->whereIn('E_KONTO', $su_konten_im_kontenrahmen->pluck('KONTO'))
                ->where('KOS_TYP', 'Einheit')
                ->where('KOS_ID', $this->einheit_id)
                ->where('AKTUELL', '1')
                ->whereYear('ANFANG', '<=', $this->p_jahr)
                ->selectRaw("E_KONTO + 1 AS E_KONTO, KOSTENKAT, MIN(ANFANG) AS ANFANG, IF(MIN(ENDE)='0000-00-00', '9999-12-31', MAX(ENDE)) AS ENDE")
                ->groupBy('E_KONTO')
                ->orderBy('KOSTENKAT')
                ->get();

            foreach ($zeilen as $zeile) {
                foreach ($su_def_zeilen_alle as $su) {
                    if ($su['E_KONTO'] == $zeile['KONTO']) {
                        $su_soll_alle = $zeile['E_SU_ALLE_NUMBER'];
                        $von = $su['ANFANG'];
                        $bis = $su['ENDE'];
                        $su_def_alle = $this->hg_tab_soll_ist_einnahmen(
                            $su['E_KONTO'] - 1,
                            'Einheit',
                            $this->einheit_id,
                            $von,
                            $bis
                        );
                        if ($zeile['E_SU_AUSZAHLEN'] || 0 > $su_def_alle - $su_soll_alle) {
                            $su_soll_vorjahre = $zeile['E_SU_VORJAHRE_NUMBER'];
                            $betrag = ($su_def_alle - $su_soll_vorjahre) * $zeile['E_SU_JAHRESANTEIL'];
                        } else {
                            $betrag = -$zeile['E_SU_ABRECHNUNGSJAHR_NUMBER'];
                        }
                        $su_ausgaben_summe += $betrag;
                        $ru_zeile = [];
                        $ru_zeile['ART'] = "Entnahme aus Rücklage zur Sonderumlage ($su[KOSTENKAT])";
                        $ru_zeile['ANTEIL'] = number_format($betrag, 2, ',', '.');
                        $ru_tab[] = $ru_zeile;
                    }
                }
            }

            $zw2 = -1 * ($inst_kosten_soll + $su_kosten_summe - $su_ausgaben_summe);

            $ru_zeile = [];
            $ru_zeile ['ART'] = '<b>Zwischensumme zu 2.</b>';
            $ru_zeile ['ANTEIL'] = '<b>' . number_format($zw2, 2, ',', '.') . '</b>';
            $ru_tab[] = $ru_zeile;

            $ge_tab [0] ['ART'] = "<b>Gesamtsumme der Hausgeldabrechnung (Zwischensumme 1 + 2)</b>";
            $ge_tab [0] ['ANTEIL'] = '<b>' . nummer_punkt2komma_t($zw1 + $zw2) . '</b>';

            $hg_kosten_soll = $this->hg_tab_soll_ist_einnahmen($this->hg_konto, 'Einheit', $this->einheit_id, $this->eigentuemer_von_t, $this->eigentuemer_bis_t);
            $ge_tab [1] ['ART'] = "Hausgeld gem. Wirtschaftsplan";
            $ge_tab [1] ['ANTEIL'] = nummer_punkt2komma_t($hg_kosten_soll + $inst_kosten_soll);

            $ge_tab [2] ['ART'] = "Sonderumlagen gem. Beschluss";
            $ge_tab [2] ['ANTEIL'] = nummer_punkt2komma_t($su_kosten_summe);

            $spitze = $zw1 + $zw2 + $hg_kosten_soll + $inst_kosten_soll + $su_kosten_summe;

            $ge_tab [3] ['ART'] = "<b>Ihre Abrechnungsspitze (- = Nachzahlung / + = Guthaben)</b>";
            $ge_tab [3] ['ANTEIL'] = '<b>' . nummer_punkt2komma_t($spitze) . '</b>';

            $hg_ist_summe = $this->get_summe_zahlungen_arr_jahr('Eigentuemer', $eig_id, $jahr, $geldkonto_id, $this->hg_konto);
            if (!$hg_ist_summe) {
                $hg_ist_summe = $this->get_summe_zahlungen_hga('Eigentuemer', $eig_id, $p_id, $this->hg_konto);
            }

            $inst_ist_summe = $this->get_summe_zahlungen_arr_jahr('Eigentuemer', $eig_id, $jahr, $geldkonto_id, $this->ihr_konto);
            if (!$inst_ist_summe) {
                $inst_ist_summe = $this->get_summe_zahlungen_hga('Eigentuemer', $eig_id, $p_id, $this->ihr_konto);
            }

            $su_ist_summe = 0;
            foreach ($su_im_wirtschaftsjahr as $su) {
                $su_ist = $this->get_summe_zahlungen_arr_jahr('Eigentuemer', $eig_id, $jahr, $geldkonto_id, $su['E_KONTO']);
                if (!$su_ist) {
                    $su_ist = $this->get_summe_zahlungen_hga('Eigentuemer', $eig_id, $p_id, $su['E_KONTO']);
                }
                $su_ist_summe += $su_ist;
            }

            $hg_saldo = $hg_ist_summe - $hg_kosten_soll + $inst_ist_summe - $inst_kosten_soll + $su_ist_summe - $su_kosten_summe;

            $ge_tab [5] ['ART'] = "Saldo ihres Hausgeldkontos (- = Rückstände / + = Überzahlung)";
            $ge_tab [5] ['ANTEIL'] = nummer_punkt2komma_t($hg_saldo);

            $abr_saldo = $spitze + $hg_saldo;

            $ge_tab [6] ['ART'] = "<b>Ihr Abrechnungssaldo (- = Nachzahlung / + = Guthaben)</b>";
            $ge_tab [6] ['ANTEIL'] = '<b>' . nummer_punkt2komma_t($abr_saldo) . '</b>';

            $uebersicht [$a] ['EINHEIT_ID'] = $einheit_id;
            $uebersicht [$a] ['EINHEIT'] = $this->einheit_kurzname . "  " . ltrim(rtrim($this->eigentuemer_name_str));

            $uebersicht [$a] ['ZAHLUNGEN_J'] = nummer_punkt2komma_t(
                $hg_kosten_soll + $inst_kosten_soll + $su_kosten_summe
            );
            $uebersicht [$a] ['KOSTEN_J'] = nummer_punkt2komma_t($zw1 + $zw2);
            $uebersicht [$a] ['SPITZE'] = nummer_punkt2komma_t($spitze);
            $uebersicht [$a] ['SALDO'] = nummer_punkt2komma_t($hg_saldo);
            $uebersicht [$a] ['ERGEBNIS'] = nummer_punkt2komma_t($abr_saldo);

            $zzz++;
            $pdf->ezSetDy(-5);

            /* Zweite Seite */
            $pdf->ezNewPage();

            $cols_4 = array(
                'ART' => "",
                'ANTEIL' => "Ihr Anteil €"
            );
            $pdf->ezTable($ru_tab, $cols_4, '<b>2. Rücklagen</b>', array(
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
                'xPos' => 40,
                'xOrientation' => 'right',
                'width' => 530,
                'cols' => array(
                    'ART' => array(
                        'justification' => 'left'
                    ),
                    'ANTEIL' => array(
                        'justification' => 'right',
                        'width' => 50
                    ),
                )
            ));
            $pdf->ezSetDy(-10);
            $cols_2 = array(
                'ART' => "",
                'ANTEIL' => "Ihr Anteil €"
            );
            $pdf->ezTable($ge_tab, $cols_2, '<b>3. Gesamt</b>', array(
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
                'xPos' => 40,
                'xOrientation' => 'right',
                'width' => 530,
                'cols' => array(
                    'ART' => array(
                        'justification' => 'left'
                    ),
                    'ANTEIL' => array(
                        'justification' => 'right',
                        'width' => 50
                    )
                )
            ));

            /* Zweite Seite */
            $pdf->ezSetDy(-30);
            $pdf->ezText("Sollte Ihre Abrechnung ein Guthaben ausweisen, werden wir nach der Genehmigung der Jahresabrechnung durch die Eigentümerversammlung den Guthabenbetrag auf Ihr Konto überweisen, soweit es nicht zum Ausgleich von aktuellen Rückständen benötigt wird. Wir bitten um die Mitteilung ihrer derzeitigen Kontoverbindung, auch wenn sich diese seit der letzten Abrechnung nicht geändert hat.", 10, array(
                'justification' => 'full'
            ));
            $pdf->ezSetDy(-10);
            $pdf->ezText("Sollte Ihre Abrechnung eine Nachzahlung ausweisen, überweisen Sie bitte den Nachzahlungsbetrag nach der Genehmigung der Jahresabrechnung durch die Eigentümerversammlung auf das Ihnen bekannte Hausgeldkonto (Inh.: $g->beguenstigter, IBAN: $g->IBAN1 bei der $g->bankname). Als <b>Verwendungszweck</b> geben Sie bitte <b>Hausgeldabrechnung $jahr $e->einheit_kurzname</b> an.", 10, array(
                'justification' => 'full'
            ));
            $pdf->ezSetDy(-10);
            $pdf->ezText("Den Zeitpunkt der Fälligkeit der Guthabenauszahlung bzw. der Nachzahlung entnehmen Sie bitte dem entsprechenden Genehmigungsbeschluss.", 10, array(
                'justification' => 'full'
            ));
            $pdf->ezSetDy(-10);
            $pdf->ezText("Wir behalten uns eine Berichtigung dieser Abrechnung vor, falls nachträglich Rechnungen Dritter für den Abrechnungszeitraum eingehen, die bei der Abrechnung hätten berücksichtigt werden müssen, Fehler anerkannt werden, welche zunächst nicht ohne weiteres erkennbar waren (z. B. Fehler von Messdiensten) oder der Abrechnungsfehler zu einem schlechthin unzumutbaren Nachteil für eine Vertragspartei führt.", 10, array(
                'justification' => 'full'
            ));
            $pdf->ezSetDy(-25);

            $pdf->ezStopPageNumbers(1, 1, $a);

            $cols_bu = array(
                'DATUMD' => "Datum",
                'KONTENRAHMEN_KONTO' => "Konto",
                'BETRAG' => "Betrag [€]",
                'VERWENDUNGSZWECK' => "Buchungstext"
            );
            $bu_arr_et = $this->get_buchungen_et_HG($geldkonto_id, $eig_id, $jahr);
            if (is_array($bu_arr_et)) {
                $pdf->ezNewPage();
                $pdf->ezTable($bu_arr_et, $cols_bu, "Buchungsübersicht $jahr $e->einheit_kurzname", array(
                    'rowGap' => 1.5,
                    'showLines' => 1,
                    'showHeadings' => 0,
                    'shaded' => 1,
                    'shadeCol' => array(
                        0.9,
                        0.9,
                        0.9
                    ),
                    'titleFontSize' => 9,
                    'fontSize' => 7,
                    'xPos' => 40,
                    'xOrientation' => 'right',
                    'width' => 530,
                    'cols' => array(
                        'KONTENRAHMEN_KONTO' => array(
                            'justification' => 'left',
                            'width' => 40
                        ),
                        'BETRAG' => array(
                            'justification' => 'right'
                        )
                    )
                ));
            }

            if (is_array($hndl_arr)) {
                $pdf->ezNewPage();

                $pdf->ezSetDy(-10);

                $pdf->setColor(0, 0, 0);
                $pdf->setStrokeColor(0, 0, 0);
                $pdf->setLineStyle(0.5);
                $pdf->rectangle(400, 601, 165, 87);
                $this->get_eigentumer_id_infos2($eig_id);
                $this->get_anrede_eigentuemer($eig_id);

                $standard_anschrift = str_replace('<br>', "\n", end($this->postanschrift));
                if (!empty ($standard_anschrift)) {
                    $pdf->ezText("$standard_anschrift", 10);
                } else {
                    $pdf->ezText("$this->eig_namen_u_pdf", 10);
                    $pdf->ezSetDy(10);
                    $pdf->ezText("$this->haus_strasse $this->haus_nummer", 10);
                    $pdf->ezSetDy(-10);
                    $pdf->ezText("$this->haus_plz $this->haus_stadt", 10);
                }

                $d = new detail ();
                $anteile_g = $d->finde_detail_inhalt('Objekt', $e->objekt_id, 'Gesamtanteile');
                $pdf->addText(405, 680, 8, "Einheiten:");
                $pdf->addText(465, 680, 8, "$anz_einheiten");
                $pdf->addText(405, 670, 8, "Einheit:");
                $pdf->addText(465, 670, 8, "$e->einheit_kurzname");
                $pdf->addText(405, 660, 8, "Gesamtanteile:");
                $pdf->addText(465, 660, 8, "$anteile_g");
                $this->einheit_anteile = $d->finde_detail_inhalt('Einheit', $einheit_id, 'WEG-Anteile');
                $pdf->addText(405, 650, 8, "Ihre MEA:");
                $pdf->addText(465, 650, 8, "$this->einheit_anteile");

                $e->einheit_qm_a = nummer_punkt2komma($e->einheit_qm);
                $pdf->addText(405, 640, 8, "Fläche:");
                $pdf->addText(465, 640, 8, "$e->einheit_qm_a m²");

                $pdf->addText(405, 630, 8, "Aufzug %:");
                $pdf->addText(465, 630, 8, "$e->aufzug_prozent_d");

                $this->eigentuemer_von_d = date_mysql2german($this->eigentuemer_von);
                $this->eigentuemer_bis_d = date_mysql2german($this->eigentuemer_bis);
                if ($this->eigentuemer_bis_d == '00.00.0000') {
                    $this->eigentuemer_bis_d = "31.12.$jahr";
                }
                $e_jahr_arr = explode(".", $this->eigentuemer_bis_d);
                $e_jahr = $e_jahr_arr [2];
                if ($e_jahr > $jahr) {
                    $this->eigentuemer_bis_d = "31.12.$jahr";
                }

                $e_ajahr_arr = explode(".", $this->eigentuemer_von_d);
                $e_ajahr = $e_ajahr_arr [2];
                if ($e_ajahr < $jahr) {
                    $this->eigentuemer_von_d = "01.01.$jahr";
                }

                $pdf->addText(405, 618, 7, "Zeitraum:");
                if ($this->p_von && $this->p_bis) {
                    $pdf->addText(465, 618, 7, "$this->p_von_d - $this->p_bis_d");
                } else {
                    $pdf->addText(465, 618, 7, "01.01.$jahr - 31.12.$jahr");
                }
                $pdf->addText(405, 611, 7, "Nutzungszeitraum: ");
                $pdf->addText(465, 611, 7, "$this->eigentuemer_von_d - $this->eigentuemer_bis_d");
                $pdf->addText(405, 604, 7, "Tage:");
                $pdf->addText(465, 604, 7, "$this->n_tage von $tj");
                $pdf->ezSetY(590);
                $pdf->ezText("$p->partner_ort, $datum", 10, array(
                    'justification' => 'right'
                ));

                $pdf->ezText("<b>Nachweis der haushaltsnahen Dienstleistungen im Sinne §35a EStG. für das Jahr $jahr</b>", 10);
                $pdf->ezSetDy(-20);
                $zeilen = $hndl_arr [$eig_id] ['ZEILEN'];
                $anz_zeilen = count($zeilen);
                $hndl_arr [$eig_id] ['ZEILEN'] [$anz_zeilen] ['E_BETRAG_HNDL'] = '<b>' . nummer_punkt2komma_t($tab_erg ['BETRAG_HNDL'] [$eig_id] ['BETEILIGUNG']) . '</b>';
                $zeilen = $hndl_arr [$eig_id] ['ZEILEN'];
                $cols = array(
                    'KONTO' => "Konto",
                    'BEZ' => "Kontobezeichnung",
                    'GRUPPE' => "Kontoart",
                    'KOS_BEZ' => "Aufteilung",
                    'SCHLUESSEL' => "Verteilungsschlüssel (Ihr Anteil / Gesamt)",
                    'BETRAG_HNDL' => "Gesamt € ",
                    'E_BETRAG_HNDL' => "Ihr Anteil € "
                );
                $pdf->ezTable($zeilen, $cols, "<b>Haushaltsnahe- und Handwerkerdienstleistungen </b>", array(
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
                    'xPos' => 55,
                    'xOrientation' => 'right',
                    'width' => 500,
                    'cols' => array(
                        'KONTO' => array(
                            'justification' => 'left',
                            'width' => 35
                        ),
                        'GRUPPE' => array(
                            'justification' => 'left',
                            'width' => 80
                        ),
                        'BETRAG_HNDL' => array(
                            'justification' => 'right',
                            'width' => 45
                        ),
                        'KOS_BEZ' => array(
                            'justification' => 'left',
                            'width' => 60
                        ),
                        'SCHLUESSEL' => array(
                            'justification' => 'right',
                            'width' => 80
                        ),
                        'E_BETRAG_HNDL' => array(
                            'justification' => 'right',
                            'width' => 50
                        )
                    )
                ));
                $pdf->ezText("<b>*) Kostenkonto beinhaltet haushaltsnahe Dienstleistungen</b>", 7);
                $summe_hndl_pdf = nummer_punkt2komma_t(abs($tab_erg ['BETRAG_HNDL'] [$eig_id] ['BETEILIGUNG']));

                $pdf->ezSetDy(-20);
                $pdf->ezText("<b>Ihr steuerbegünstigter Kostenanteil beträgt $summe_hndl_pdf €</b>", 8);
                $pdf->ezSetDy(-20);
                $pdf->ezText("In den oben aufgeführten Kostenarten sind nur Lohnkosten und eventuelle An-/Abfahrtskosten enthalten.", 8);
                $pdf->ezText("Die Verteilung \"Ihr Anteil\" an den Gesamtkosten wird aus den jeweils aufgeführten Verteilerfaktoren der Hausgeld-Einzelabrechnung ermittelt.", 8);
                $pdf->ezSetDy(-10);
                $pdf->ezText("Die Originalbelege (Rechnungen) liegen bei der Hausverwaltung zur Einsicht vor.", 8);
                $pdf->ezSetDy(-10);
                $pdf->ezText("Bei steuerlichen Fragen, wenden Sie sich bitte an Ihren Steuerberater.", 8);

                $pdf->ezSetDy(-10);
                $pdf->ezText("Diese Angaben sind im Rahmen ordnungsgemäßer Verwaltung und nach bestem Wissen ermittelt worden. Für tatsächliche Gewährung einer Steuerbegünstigung durch das zuständige Finanzamt wird indes keine Haftung übernommen.", 8);
                $pdf->ezSetDy(-10);
                $pdf->ezText("Dieses Schreiben wurde maschinell erstellt und ist daher ohne Unterschrift gültig.\n", 8);
                $pdf->ezSetDy(-10);
                $bpdf->zahlungshinweis = strip_tags($bpdf->zahlungshinweis);
                // $pdf->ezText("$bpdf->zahlungshinweis",8);
            }

            $tab_zahl_rueck [$a] ['EINHEIT'] = $this->einheit_kurzname;
            $tab_zahl_rueck [$a] ['NAME'] = substr($this->eigentuemer_name_str, 0, -2);
            $tab_zahl_rueck [$a] ['VORJAHRE'] = '';
            $tab_zahl_rueck [$a] ['SOLL_IHR'] = nummer_punkt2komma($inst_kosten_soll);
            $tab_zahl_rueck [$a] ['IST_IHR'] = nummer_punkt2komma($inst_ist_summe);
            $tab_zahl_rueck [$a] ['SALDO'] = nummer_punkt2komma($inst_diff + $vorjahre);
        } // end for $a

        $anz_u = count($uebersicht);
        $summe_alle = 0;
        $summe_nachzahlung = 0;
        $summe_guthaben = 0;

        $summe_saldo = 0;
        $summe_spitze = 0;

        $summe_kosten = 0;
        $summe_zahlungen = 0;

        $summe_einheit = 0;
        $einheit_temp = '';
        $sum_pro_einheit = 0;

        for ($a = 0; $a < $anz_u; $a++) {
            $einheit_id_n = $uebersicht [$a] ['EINHEIT_ID'];
            $betrag = nummer_komma2punkt($uebersicht [$a] ['ERGEBNIS']);

            $sum_pro_einheit += $betrag;

            if ($a == 0) {
                $einheit_temp = $einheit_id_n;
                $summe_einheit += $betrag;
            }

            if ($a > 0) {
                if ($einheit_temp == $einheit_id_n) {
                    $summe_einheit += $betrag;
                } else {
                    $uebersicht [$a - 1] ['ERGEBNIS_E'] = nummer_punkt2komma_t($summe_einheit);
                    $summe_einheit = 0;
                    $einheit_temp = $einheit_id_n;
                    $summe_einheit += $betrag;
                }
                /* Letzte Zeile/Einheit */
                if ($a == $anz_u - 1) {
                    $uebersicht [$a] ['ERGEBNIS_E'] = nummer_punkt2komma_t($summe_einheit);
                }
            }

            $betrag = nummer_komma2punkt($uebersicht [$a] ['ERGEBNIS']);
            $kosten_j = nummer_komma2punkt($uebersicht [$a] ['KOSTEN_J']);
            $zahlungen_j = nummer_komma2punkt($uebersicht [$a] ['ZAHLUNGEN_J']);
            $saldo = nummer_komma2punkt($uebersicht [$a] ['SALDO']);
            $spitze = nummer_komma2punkt($uebersicht [$a] ['SPITZE']);

            $summe_kosten += $kosten_j;
            $summe_zahlungen += $zahlungen_j;
            $summe_saldo += $saldo;
            $summe_spitze += $spitze;

            if ($betrag < '0.00') {
                $summe_nachzahlung += $betrag;
            } else {
                $summe_guthaben += $betrag;
            }
            $summe_alle += $betrag;
        }
        $pdf->ezNewPage();
        $pdf->ezSetDy(-20);
        $uebersicht [$anz_u + 1] ['EINHEIT'] = '<b>SUMME NACHZAHLUNGEN (EINNAHMEN)</b>';
        $uebersicht [$anz_u + 1] ['ERGEBNIS'] = '<b>' . nummer_punkt2komma_t($summe_nachzahlung) . '</b>';
        $uebersicht [$anz_u] ['EINHEIT'] = '<b>SUMME GUTHABEN (AUSZAHLUNGEN)</b>';
        $uebersicht [$anz_u] ['ERGEBNIS'] = '<b>' . nummer_punkt2komma_t($summe_guthaben) . '</b>';

        if ($summe_alle > '0.00') {
            $uebersicht [$anz_u + 2] ['EINHEIT'] = '<b>GESAMT GUTHABEN</b>';
        } else {
            $uebersicht [$anz_u + 2] ['EINHEIT'] = '<b>GESAMT NACHZAHLUNG</b>';
        }

        $uebersicht [$anz_u + 2] ['ZAHLUNGEN_J'] = "<b>" . nummer_punkt2komma_t($summe_zahlungen) . "</b>";
        $uebersicht [$anz_u + 2] ['KOSTEN_J'] = "<b>" . nummer_punkt2komma_t($summe_kosten) . "</b>";
        $uebersicht [$anz_u + 2] ['ERGEBNIS'] = '<b>' . nummer_punkt2komma_t($summe_alle) . '</b>';
        $uebersicht [$anz_u + 2] ['SALDO'] = "<b>" . nummer_punkt2komma_t($summe_saldo) . "</b>";
        $uebersicht [$anz_u + 2] ['SPITZE'] = '<b>' . nummer_punkt2komma_t($summe_spitze) . '</b>';

        $uebersicht [$anz_u + 2] ['ERGEBNIS_E'] = '<b>' . nummer_punkt2komma_t($sum_pro_einheit) . '</b>';

        $cols = array(
            'EINHEIT' => "Einheit / Eigentümer",
            'ZAHLUNGEN_J' => "HG und SU",
            'KOSTEN_J' => "Zw 1+2",
            'SPITZE' => "Abrechnungsspitze",
            'SALDO' => "HGK Saldo",
            'ERGEBNIS' => "Abrechungssaldo",
            'ERGEBNIS_E' => "pro Einheit"
        );
        $pdf->ezTable($uebersicht, $cols, "<b>Abrechnungsergebnis $this->p_jahr</b>", array(
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
            'fontSize' => 9,
            'xPos' => 55,
            'xOrientation' => 'right',
            'width' => 500,
            'cols' => array(
                'ERGEBNIS' => array(
                    'justification' => 'right',
                    'width' => 65
                ),
                'ERGEBNIS_E' => array(
                    'justification' => 'right',
                    'width' => 60
                ),
                'SPITZE' => array(
                    'justification' => 'right',
                    'width' => 60
                ),
                'SALDO' => array(
                    'justification' => 'right',
                    'width' => 60
                ),
                'KOSTEN_J' => array(
                    'justification' => 'right',
                    'width' => 60
                ),
                'ZAHLUNGEN_J' => array(
                    'justification' => 'right',
                    'width' => 60
                )
            )
        ));
        // $pdf->ezTable($tab_erg[BETRAG_HNDL]);

        ob_end_clean(); // ausgabepuffer leeren
        $pdf->ezStream();
    }

    function key_daten_gesamt($key_id, $kos_typ, $kos_id)
    {
        $bk = new bk ();
        $bk->get_genkey_infos($key_id);

        if ($kos_typ == 'Wirtschaftseinheit') {
            $wi = new wirt_e ();
            $wi->get_wirt_e_infos($kos_id);
            $g_var_name = $bk->g_key_g_var; // z.B. g_mea
            return $wi->$g_var_name;
        }
    }

    function get_summe_konto($gk_id, $buchungskonto, $jahr)
    {
        $result = DB::select("SELECT SUM(BETRAG) AS SUMME  FROM `GELD_KONTO_BUCHUNGEN` WHERE `GELDKONTO_ID` = '$gk_id' AND `AKTUELL` = '1' && KONTENRAHMEN_KONTO='$buchungskonto' AND YEAR(DATUM)<='$jahr'");
        if (isset($result)) {
            return $result[0]['SUMME'];
        } else {
            return 0.00;
        }
    }

    function get_summe_hk($kos_typ, $kos_id, $hga_id)
    {
        $result = DB::select("SELECT SUM(BETRAG) AS BETRAG FROM WEG_HGA_HK WHERE KOS_TYP='$kos_typ' && KOS_ID='$kos_id' && AKTUELL='1' && WEG_HGA_ID='$hga_id'");
        if (!empty($result)) {
            $row = $result[0];
            return $row ['BETRAG'];
        }
    }

    function get_summe_zahlungen_arr_jahr($kos_typ, $kos_id, $jahr, $geldkonto_id, $kostenkonto)
    {
        $result = DB::select("SELECT SUM(BETRAG) AS SUMME FROM GELD_KONTO_BUCHUNGEN WHERE DATE_FORMAT(DATUM, '%Y') = '$jahr' && KOSTENTRAEGER_TYP LIKE '$kos_typ' && KOSTENTRAEGER_ID='$kos_id' && AKTUELL='1' && GELDKONTO_ID='$geldkonto_id' && KONTENRAHMEN_KONTO='$kostenkonto' ORDER BY DATUM ASC");
        if (!empty($result)) {
            $row = $result[0];
            return $row ['SUMME'];
        }
    }

    function get_summe_zahlungen_hga($kos_typ, $kos_id, $hga_id, $kostenkonto)
    {
        $result = DB::select("SELECT BUCHUNGS_DAT, BUCHUNGS_SUMME FROM WEG_HG_ZAHLUNGEN WHERE  AKTUELL='1' && KOS_TYP='$kos_typ' && KOS_ID='$kos_id' && WEG_HGA_ID='$hga_id' && KOSTENKONTO='$kostenkonto' ORDER BY BUCHUNGS_DAT ASC");
        $numrows = count($result);
        $summe = 0;
        if ($numrows > 1) {
            foreach ($result as $row) {
                $buchungs_dat = $row ['BUCHUNGS_DAT'];
                $b = new buchen ();
                $b->geldbuchungs_dat_infos($buchungs_dat);
                $summe += $b->akt_betrag_punkt;
            }
        }
        if ($numrows = 1) {
            $row = $result[0];
            $buchungs_dat = $row ['BUCHUNGS_DAT'];
            if (empty ($buchungs_dat)) {
                $summe = $row ['BUCHUNGS_SUMME'];
            } else {
                $b = new buchen ();
                $b->geldbuchungs_dat_infos($buchungs_dat);
                $summe += $b->akt_betrag_punkt;
            }
        }

        return $summe;
    }

    function get_buchungen_et_HG($gk_id, $et_id, $jahr)
    {
        $result = DB::select("SELECT  DATE_FORMAT(`DATUM`, '%d.%m.%Y') AS DATUMD, DATUM ,  `KONTENRAHMEN_KONTO` ,  `BETRAG` ,  `VERWENDUNGSZWECK`
FROM  `GELD_KONTO_BUCHUNGEN`
WHERE  `GELDKONTO_ID` ='$gk_id' &&  `KOSTENTRAEGER_TYP` =  'Eigentuemer' &&  `KOSTENTRAEGER_ID` =  '$et_id' && DATE_FORMAT(  `DATUM` ,  '%Y' ) =  '$jahr' && AKTUELL='1' ORDER BY KONTENRAHMEN_KONTO, DATUM ASC");
        if (!empty($result)) {
            $sum_kto = 0;
            $konten_arr = array();
            $zaehler = 0;
            foreach ($result as $row) {
                $kto = $row ['KONTENRAHMEN_KONTO'];

                $le = count($konten_arr) - 1;

                if (isset ($konten_arr [$le]) && $konten_arr [$le] == $kto && $zaehler > 0) {
                    $sum_kto += $row ['BETRAG'];
                } else {
                    $my_arr [] ['BETRAG'] = "<b>" . nummer_punkt2komma_t($sum_kto) . "</b>";
                    $anz_bisher = count($my_arr);
                    $my_arr [$anz_bisher - 1] ['KONTENRAHMEN_KONTO'] = "<b>Summe</b>";
                    $sum_kto = 0;
                    $sum_kto += $row ['BETRAG'];
                }

                $konten_arr [] = $kto;
                $my_arr [] = $row;

                $zaehler++;

                if ($zaehler == $numrows) {
                    $my_arr [] ['BETRAG'] = "<b>" . nummer_punkt2komma_t($sum_kto) . "</b>";
                    $anz_bisher = count($my_arr);
                    $my_arr [$anz_bisher - 1] ['KONTENRAHMEN_KONTO'] = "<b>Summe</b>";
                }
            }

            print_r($konten_arr);

            return $my_arr;
        }
    }

    function assistent()
    {
        if (!session()->has('geldkonto_id')) {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\InfoMessage('Bitte wählen Sie ein Geldkonto.')
            );
        }
        if (!request()->has('schritt')) {
            $f = new formular ();
            $f->fieldset('Assistent für HG Abrechnung', 'ass_weg');
            $f->erstelle_formular('Schritt 1', '');
            $f->text_feld('Profilbezeichnung eingeben', 'profilbez', '', '50', 'profilbez', '');
            $this->dropdown_weg_objekte('WEG-Verwaltungsobjekt wählen', 'objekt_id', 'objekt_id');
            $jahr = date("Y") - 1;
            $f->text_feld('Jahr eingeben', 'jahr', $jahr, 5, 'jahr', '');
            $gk = new gk ();
            $gk->dropdown_geldkonten_alle('Geldkonto für die IHR wählen', 'gk_id_ihr', 'gk_ihr_id');
            $f->hidden_feld('geldkonto_id', session()->get('geldkonto_id'));
            $this->dropdown_wps_alle('Dazugehörigen Wirtschaftsplan wählen', 'wp_id', 'wp_id', '');
            $kk = new kontenrahmen ();
            $kk->dropdown_kontorahmenkonten('Konto für Hausgeldeinnahmen für Kosten wählen', 'hg_konto', 'hg_konto', 'Geldkonto', session()->get('geldkonto_id'), '');
            $kk->dropdown_kontorahmenkonten('Konto für Hausgeldeinnahmen für Heizkosten wählen', 'hk_konto', 'hk_konto', 'Geldkonto', session()->get('geldkonto_id'), '');
            $kk->dropdown_kontorahmenkonten('Konto für Hausgeldeinnahmen für IHR wählen', 'ihr_konto', 'ihr_konto', 'Geldkonto', session()->get('geldkonto_id'), '');
            $f->hidden_feld('option', 'profil_send');
            $f->send_button('send', 'Speichern');
            $f->ende_formular();
            $f->fieldset_ende();
        }

        if (request()->has('schritt')) {
            switch (request()->input('schritt')) {
                case "2" :
                    if (request()->has('profil_id')) {
                        session()->put('hga_profil_id', request()->input('profil_id'));
                        $this->get_hga_profil_infos(request()->input('profil_id'));
                        session()->put('objekt_id', $this->p_objekt_id);
                    }
                    $p_id = session()->get('hga_profil_id');
                    $gk_id = session()->get('geldkonto_id');
                    echo "<h5>Schritt 2</h5>";
                    $gk_info = new geldkonto_info ();
                    $gk_info->geld_konto_details($gk_id);
                    $this->get_hga_profil_infos($p_id);
                    $this->tab_konten_auswahl_summen_arr($gk_id, $this->p_jahr);
                    $this->tab_konten_auswahl_ohne_zuordnung_arr($gk_id, $this->p_jahr, session()->get('hga_profil_id'));
                    break;

                case "3" :
                    if (request()->has('profil_id')) {
                        session()->put('hga_profil_id', request()->input('profil_id'));
                    }
                    $this->form_hk_verbrauch(session()->get('hga_profil_id'));
                    break;

                case "4" :
                    if (request()->has('profil_id')) {
                        session()->put('hga_profil_id', request()->input('profil_id'));
                    }
                    $this->form_hg_zahlungen(session()->get('hga_profil_id'));
                    break;
            }
        }
    }

    function dropdown_weg_objekte($label, $name, $id, $vorwahl = null)
    {
        $arr = $this->weg_objekte_arr();
        if (is_array($arr)) {
            echo "<label for=\"weg_objekte\">$label</label>\n<select name=\"$name\" id=\"$id\" size=\"1\" >\n";
            $anz = count($arr);
            for ($a = 0; $a < $anz; $a++) {
                $objekt_id = $arr [$a];
                $o = new objekt ();
                $o->get_objekt_infos($objekt_id);
                if ($vorwahl == $objekt_id) {
                    echo "<option value=\"$objekt_id\" selected>$o->objekt_kurzname</option>\n";
                } else {
                    echo "<option value=\"$objekt_id\" >$o->objekt_kurzname</option>\n";
                }
            }

            echo "</select>\n";
        } else {
            echo "Keine WEG-Objekte verfügbar";
        }
    }

    function dropdown_wps_alle($label, $name, $id, $js, $vorwahl = null)
    {
        $arr = $this->get_wps_alle_arr();

        if (!empty($arr)) {
            echo "<label for=\"$id\">$label</label>";
            echo "<select name=\"$name\" size=\"1\" id=\"$id\" $js>\n";
            $anz = count($arr);
            echo "$anz Einheiten";
            for ($a = 0; $a < $anz; $a++) {
                $plan_id = $arr [$a] ['PLAN_ID'];
                $objekt_id = $arr [$a] ['OBJEKT_ID'];
                $jahr = $arr [$a] ['JAHR'];
                $o = new objekt ();
                $o->get_objekt_name($objekt_id);
                if ($plan_id == $vorwahl) {
                    echo "<option value=\"$plan_id\" selected>WP $jahr $o->objekt_name ($plan_id.)</option>\n";
                } else {
                    echo "<option value=\"$plan_id\">WP $jahr $o->objekt_name ($plan_id.)</option>\n";
                }
            }
            echo "</select>\n";
        } else {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\WarningMessage("Keine Wirtschaftspläne für das Objekt $e->objekt_kurzname")
            );
        }
    }

    function get_wps_alle_arr()
    {
        $result = DB::select("SELECT * FROM WEG_WPLAN WHERE AKTUELL='1' ORDER BY JAHR DESC");
        return $result;
    }

    function tab_konten_auswahl_summen_arr($gk_id, $jahr)
    {
        $arr = $this->konten_auswahl_summen_arr($gk_id, $jahr);
        $anz = count($arr);
        if ($anz) {
            echo "<table class='striped'>";
            echo "<thead><tr><th>KONTO</th><th>BEZ</th><th>GRUPPE</th><th>SUMME</th><th>ÜBERNOMMEN</th><th>HNDL</th><th>OPTION</th></tr></thead>";
            $summe_g_u = 0;
            $summe_g_hndl_u = 0;
            for ($a = 0; $a < $anz; $a++) {
                $konto = $arr [$a] ['KONTENRAHMEN_KONTO'];
                $summe = $arr [$a] ['SUMME'];

                $summe_a = nummer_punkt2komma($summe);
                $k = new kontenrahmen ();
                $kontenrahmen_id = $k->get_kontenrahmen('Geldkonto', $gk_id);
                session()->put('kontenrahmen_id', $kontenrahmen_id);
                $k->konto_informationen2($konto, $kontenrahmen_id);

                $this->get_summe_zeilen($konto, session()->get('hga_profil_id'));
                $this->summe_zeilen_a = nummer_punkt2komma($this->summe_zeilen);
                $this->summe_hndl_a = nummer_punkt2komma($this->summe_hndl);
                $summe_g_u += $this->summe_zeilen;
                $summe_g_hndl_u += $this->summe_hndl;
                $link_del = "<a href='" . route('web::weg::legacy', ['option' => 'konto_del', 'konto' => $konto, 'profil_id' => session()->get('hga_profil_id')]) . "'>Löschen</a>";
                $link_u = "<a href='" . route('web::weg::legacy', ['option' => 'konto_hinzu', 'schritt' => 2, 'konto' => $konto]) . "'>Übernehmen</a>";

                echo "<tr><td>$konto</td><td>$k->konto_bezeichnung</td><td>$k->konto_art_bezeichnung | $k->konto_gruppen_bezeichnung</td><td>$summe_a</td><td>$this->summe_zeilen_a</td><td>$this->summe_hndl_a</td><td>";
                if ($k->konto_sonderumlage && $k->konto_art_bezeichnung == 'Ausgaben') {
                    if ($this->konto_su_auszahlen) {
                        $text = "Sonderumlage behalten";
                    } else {
                        $text = "Sonderumlage auszahlen";
                    }
                    $f = new formular();
                    $form_id = "su_auszahlen_$konto";
                    echo "<form id='$form_id' method='post' action='" . route('web::weg::hga::change-su') . "'>";
                    $f->hidden_feld('profil_id', session()->get('hga_profil_id'));
                    $f->hidden_feld('konto', $konto);
                    echo "<a href='javascript:{document.getElementById(\"$form_id\").submit();}'>" . $text . "</a>";
                    echo "</form>";
                }
                if (!$this->konto_has_entry) {
                    echo $link_u;
                }

                if ($summe != $this->summe_zeilen && $this->konto_has_entry) {
                    $f = new formular();
                    $form_id = "auto_$konto";
                    echo "<form id='$form_id'>";
                    $f->hidden_feld('profil_id', session()->get('hga_profil_id'));
                    $f->hidden_feld('konto', $konto);
                    $f->hidden_feld('betrag', $summe);
                    $f->hidden_feld('option', 'autokorrkto');
                    echo "<a href='javascript:{document.getElementById(\"$form_id\").submit();}' class='red-text'>Autokorrektur</a>";
                    echo "</form>";
                }
                if ($this->konto_has_entry) {
                    echo "$link_del";
                }
                echo "</td></tr>";
            } // end for
            $summe_g_u_a = nummer_punkt2komma($summe_g_u);
            echo "<tr><td>Gesamtsumme</td><td>EIN/AUSGABEN</td><td></td><td>$summe_g_u_a</td><td></td></tr>";
            echo "</table>";
        } else {
            echo "<hr><b>Es gibt keine Buchungen auf dem Konto im Jahr $jahr zur Auswahl.</b><hr><br>";
            $this->form_konto_hinzu('');
        }
    }

    function konten_auswahl_summen_arr($gk_id, $jahr)
    {
        $result = DB::select("SELECT KONTENRAHMEN_KONTO, SUM(BETRAG) AS SUMME FROM GELD_KONTO_BUCHUNGEN WHERE DATE_FORMAT(DATUM, '%Y') = '$jahr'  && AKTUELL='1' && GELDKONTO_ID='$gk_id' GROUP BY KONTENRAHMEN_KONTO ORDER BY KONTENRAHMEN_KONTO ASC");
        if (!empty($result)) {
            return $result;
        }
    }

    function form_konto_hinzu($konto)
    {
        $this->tab_zeilen(session()->get('hga_profil_id'));
        $f = new formular ();
        $f->fieldset('Konto zu Hausgeldabrechnung hinzufügen', 'hga');
        $f->erstelle_formular('Schritt 2', '');
        $f->text_feld('Konto', 'konto', $konto, 10, 'konto', '');
        $b = new buchen ();
        $b->summe_kontobuchungen_jahr(session()->get('geldkonto_id'), $konto, session()->get('jahr'));
        $summe = nummer_punkt2komma($b->summe_konto_buchungen);
        $f->text_feld('Summe', 'summe', $summe, 10, 'summe', '');
        $f->text_feld('Summe HNDL', 'summe_hndl', '0,00', 10, 'summe_hndl', '');
        $k = new kontenrahmen ();
        $k->konto_informationen2($konto, session()->get('kontenrahmen_id'));
        $f->text_feld_inaktiv('Kontobezeichnung', 'kontobez', $k->konto_bezeichnung, 100, 'kontobez', '');
        $f->text_feld('Zeilentext für PDF', 'textbez', $k->konto_bezeichnung, 100, 'textbez', '');
        $this->dropdown_art('Kostenkontoart', 'art', 'art');
        $bk = new bk ();
        $bk->dropdown_gen_keys();
        $wirt = new wirt_e ();
        $wirt->dropdown_we('Wirtschaftseinheit wählen', 'wirt_id', 'wirt_id', '');
        $f->hidden_feld('option', 'konto_zu_zeilen');
        $f->send_button('send', 'Eintragen');
        $f->ende_formular();
        $f->fieldset_ende();
    }

    function tab_zeilen($p_id)
    {
        $result = DB::update("SELECT * FROM WEG_HGA_ZEILEN WHERE AKTUELL='1' && WEG_HG_P_ID='$p_id' ORDER BY KONTO, ART ASC");
        if (!empty($result)) {
            echo "<table class=\"sortable\">";
            echo "<tr><th>KONTO</th><th>TEXT</th><th>ART</th><th>BETRAG</th><th>HNDL BETRAG</th><th>ZUORDNUNG</th><th>SCHLÜSSEL</th></tr>";
            foreach ($result as $row) {
                $konto = $row ['KONTO'];
                $art = $row ['ART'];
                $text = $row ['TEXT'];
                $betrag = $row ['BETRAG'];
                $betrag_hndl = $row ['HNDL_BETRAG'];
                $genkey_id = $id = $row ['GEN_KEY_ID'];
                $kos_typ = $row ['KOS_TYP'];
                $kos_id = $row ['KOS_ID'];
                $r = new rechnung ();
                $kos_bez = $r->kostentraeger_ermitteln($kos_typ, $kos_id);
                $bk = new bk ();
                $bk->get_genkey_infos($genkey_id);
                echo "<tr><td>$konto</td><td>$text</td><td>$art</td><td>$betrag</td><td>$betrag_hndl</td><td>$kos_bez</td><td>$bk->g_key_name</td></tr>";
            }
            echo "</table>";
        }
    }

    function dropdown_art($label, $name, $id)
    {
        echo "<label for=\"$id\">$label</label>\n<select name=\"$name\" id=\"$id\" size=\"1\" >\n";
        echo "<option value=\"Ausgaben/Einnahmen\">Ausgaben/Einnahmen</option>\n";
        echo "<option value=\"Mittelverwendung\" >Mittelverwendung</option>\n";
        echo "</select>\n";
    }

    function tab_konten_auswahl_ohne_zuordnung_arr($gk_id, $jahr, $profil_id)
    {
        $arr = $this->konten_auswahl_ohne_zuordnung_arr($gk_id, $jahr, $profil_id);
        $anz = count($arr);
        if ($anz) {
            $f = new formular ();
            $f->erstelle_formular("Übernommene Einnahmen/Ausgaben ohne Geldbuchung (Diese Buchungen verfälschen die Abrechnung)", NULL);
            $f->ende_formular();
            echo "<table>";
            echo "<tr><th>KONTO</th><th>BEZ</th><th>BEZ IM PDF</th><th>GRUPPE</th><th>ÜBERNOMMEN</th><th>€ HNDL</th><th>OPTION</th></tr>";

            for ($a = 0; $a < $anz; $a++) {
                $konto = $arr [$a] ['KONTO'];
                $summe = $arr [$a] ['BETRAG'];
                $hndl = $arr [$a] ['HNDL_BETRAG'];
                $bez_pdf = $arr [$a] ['TEXT'];

                $k = new kontenrahmen ();
                $kontenrahmen_id = $k->get_kontenrahmen('Geldkonto', $gk_id);
                $k->konto_informationen2($konto, $kontenrahmen_id);

                $link_del = "<a href='" . route('web::weg::legacy', ['option' => 'konto_del', 'konto' => $konto, 'profil_id' => session()->get('hga_profil_id')]) . "'>Löschen</a>";

                echo "<tr><td>$konto</td><td>$k->konto_bezeichnung</td><td>$bez_pdf</td><td>$k->konto_art_bezeichnung | $k->konto_gruppen_bezeichnung</td><td>$summe</td><td>$hndl</td><td>";


                echo "$link_del";
                echo "</td></tr>";
            } // end for
            echo "</table>";
        }
    }

    function konten_auswahl_ohne_zuordnung_arr($gk_id, $jahr, $profil_id)
    {
        $result = DB::select("SELECT * FROM WEG_HGA_ZEILEN
                               WHERE KONTO NOT IN (SELECT KONTENRAHMEN_KONTO
					                               FROM GELD_KONTO_BUCHUNGEN
					                               WHERE DATE_FORMAT(DATUM, '%Y') = '$jahr'
						                              AND AKTUELL='1'
						                              AND GELDKONTO_ID='$gk_id'
					                               GROUP BY KONTENRAHMEN_KONTO)
	                              AND WEG_HG_P_ID=$profil_id
	                              AND AKTUELL='1'
	                           ORDER BY KONTO DESC;");
        if (!empty($result)) {
            return $result;
        }
    }

    function form_hk_verbrauch($p_id)
    {
        $this->get_hga_profil_infos($p_id);

        $o = new objekt ();
        $einheiten_arr = $o->einheiten_objekt_arr($this->p_objekt_id);
        $anz_e = count($einheiten_arr);
        $f = new formular ();
        $f->erstelle_formular('Schritt 3', '');
        $f->fieldset('Heizkostenverbrauch / Heizkostenabrechnung', 'f_hk');
        echo "<table>";
        echo "<tr><th>EINHEIT</th><th>EIGENTUEMER</th><th>VON</th><th>BIS</th><th>TAGE</th><th>HK VERBRAUCH</th></tr>";
        $z = 1;
        for ($a = 0; $a < $anz_e; $a++) {

            $einheit_id = $einheiten_arr [$a] ['EINHEIT_ID'];
            $einheit_kn = $einheiten_arr [$a] ['EINHEIT_KURZNAME'];
            $eig_arr = $this->get_eigentuemer_arr_jahr($einheit_id, $this->p_jahr);
            // echo "<pre>";
            // print_r($eig_arr);

            $anz_eig = count($eig_arr);
            // print_r($eig_arr);
            for ($b = 0; $b < $anz_eig; $b++) {
                $eig_id = $eig_arr [$b] ['ID'];
                $von = date_mysql2german($eig_arr [$b] ['VON']);
                $bis = date_mysql2german($eig_arr [$b] ['BIS']);
                $tage = $eig_arr [$b] ['TAGE'];
                $this->get_eigentumer_id_infos2($eig_id);

                echo "<tr><td>$z.) $einheit_kn</td><td>$this->eigentuemer_name_str</td><td>$von</td><td>$bis</td><td>$tage</td><td>";
                $f->text_feld('Heizkostenverbrauch in €', 'hk_verbrauch[]', '', 10, 'hk_verbrauch', '');
                echo "</td></tr>";
                $f->hidden_feld('eig_id[]', $eig_id);
                $z++;
            }
        }
        $f->hidden_feld('p_id', $p_id);
        $f->hidden_feld('option', 'hk_verbrauch_send');
        echo "<tr><td colspan=\"6\">";
        $f->send_button('sendb', 'Heizkostenverbrauch eintragen');
        echo "</td></tr>";
        echo "</table>";

        $f->fieldset_ende();
        $f->ende_formular();
    }

    function form_hg_zahlungen($p_id)
    {
        $this->get_hga_profil_infos($p_id);

        $o = new objekt ();
        $einheiten_arr = $o->einheiten_objekt_arr($this->p_objekt_id);
        $anz_e = count($einheiten_arr);
        $f = new formular ();
        $f->erstelle_formular('Schritt 3', '');
        $f->fieldset('Hausgeldzahlungen als Summe', 'f_hg');
        echo "<table>";
        echo "<tr><th>EINHEIT</th><th>EIGENTUEMER</th><th>VON</th><th>BIS</th><th>TAGE</th><th>HK VERBRAUCH</th></tr>";
        for ($a = 0; $a < $anz_e; $a++) {
            $einheit_id = $einheiten_arr [$a] ['EINHEIT_ID'];
            $einheit_kn = $einheiten_arr [$a] ['EINHEIT_KURZNAME'];
            $eig_arr = $this->get_eigentuemer_arr_jahr($einheit_id, $this->p_jahr);

            $anz_eig = count($eig_arr);
            // print_r($eig_arr);
            for ($b = 0; $b < $anz_eig; $b++) {
                $eig_id = $eig_arr [$b] ['E_ID'];
                $von = date_mysql2german($eig_arr [$b] ['VON']);
                $bis = date_mysql2german($eig_arr [$b] ['BIS']);
                $tage = $eig_arr [$b] ['TAGE'];
                $this->get_eigentumer_id_infos2($eig_id);
                echo "<tr><td>$einheit_kn</td><td>$this->eigentuemer_name_str</td><td>$von</td><td>$bis</td><td>$tage</td><td>";
                $f->text_feld('Heizkostenverbrauch in €', 'hk_verbrauch[]', '', 10, 'hk_verbrauch', '');
                echo "</td></tr>";
                $f->hidden_feld('eig_id[]', $eig_id);
            }
        }
        $f->hidden_feld('p_id', $p_id);
        $f->hidden_feld('option', 'hk_verbrauch_send');
        echo "<tr><td colspan=\"6\">";
        $f->send_button('sendb', 'Heizkostenverbrauch eintragen');
        echo "</td></tr>";
        echo "</table>";

        $f->fieldset_ende();
        $f->ende_formular();
    }

    function konto_loeschen($profil_id, $konto)
    {
        $db_abfrage = "UPDATE WEG_HGA_ZEILEN SET AKTUELL='0' WHERE KONTO='$konto' && WEG_HG_P_ID='$profil_id'";
        DB::update($db_abfrage);
    }

    function autokorr_hga($profil_id, $konto, $betrag)
    {
        DB::update("UPDATE WEG_HGA_ZEILEN SET BETRAG='$betrag' WHERE AKTUELL='1' && WEG_HG_P_ID='$profil_id' && KONTO='$konto'");
    }

    function tab_profile()
    {
        $result = DB::select("SELECT * FROM WEG_HGA_PROFIL WHERE AKTUELL='1' ORDER BY ID DESC");
        if (!empty($result)) {
            echo "<table class=\"sortable striped\">";
            echo "<tr><th>Bezeichnung</th><th>Jahr</th><th>Objekt</th><th>Optionen</th></tr>";
            foreach ($result as $row) {
                $id = $row ['ID'];
                $bez = $row ['BEZEICHNUNG'];
                $objekt_id = $row ['OBJEKT_ID'];
                $jahr = $row ['JAHR'];
                $o = new objekt ();
                $o->get_objekt_infos($objekt_id);
                $link_del = "<a href='" . route('web::weg::legacy', ['option' => 'hga_profile_del', 'profil_id' => $id]) . "'>Löschen</a>";
                $link_s2 = "<a href='" . route('web::weg::legacy', ['option' => 'assistent', 'schritt' => 2, 'profil_id' => $id]) . "'>Bearbeiten</a>";
                $link_gd = "<a href='" . route('web::weg::legacy', ['option' => 'grunddaten_profil', 'profil_id' => $id]) . "'>Grunddaten des Profils ändern</a>";
                echo "<tr><td>$bez</td><td>$jahr</td><td>$o->objekt_kurzname</td><td>$link_s2 | $link_del | $link_gd</td></tr>";
            }
            echo "<table>";
        } else {
            echo "Keine Hausgeldabrechnungsprofile vorhanden!<br>Mit dem HGA -Assistenten können Sie eines erstellen!";
        }
    }

    function hga_profil_del($id)
    {
        $db_abfrage = "UPDATE WEG_HGA_PROFIL SET AKTUELL='0' WHERE ID='$id' && AKTUELL='1'";
        DB::update($db_abfrage);

        $db_abfrage = "UPDATE WEG_HGA_HK SET AKTUELL='0' WHERE WEG_HGA_ID='$id' && AKTUELL='1'";
        DB::update($db_abfrage);

        $db_abfrage = "UPDATE WEG_HGA_ZEILEN SET AKTUELL='0' WHERE WEG_HG_P_ID='$id' && AKTUELL='1'";
        DB::update($db_abfrage);

        $db_abfrage = "UPDATE WEG_HG_ZAHLUNGEN SET AKTUELL='0' WHERE WEG_HGA_ID='$id' && AKTUELL='1'";
        DB::update($db_abfrage);
    }

    function hk_verbrauch_eintragen($p_id, $eig_id, $betrag)
    {
        $db_abfrage = "UPDATE WEG_HGA_HK SET AKTUELL='0' WHERE WEG_HGA_ID='$p_id' && KOS_ID='$eig_id'";
        DB::update($db_abfrage);

        $id = last_id2('WEG_HGA_HK', 'ID') + 1;
        $betrag_a = nummer_komma2punkt($betrag);
        $db_abfrage = "INSERT INTO WEG_HGA_HK VALUES (NULL, '$id', '$betrag_a', 'Eigentuemer','$eig_id', '$p_id', '1')";
        DB::insert($db_abfrage);
        /* Protokollieren */
        $last_dat = DB::getPdo()->lastInsertId();
        protokollieren('WEG_HGA_HK', '0', $last_dat);
    }

    function tab_hk_verbrauch($p_id)
    {
        $this->get_hga_profil_infos($p_id);

        $o = new objekt ();
        $einheiten_arr = $o->einheiten_objekt_arr($this->p_objekt_id);
        $anz_e = count($einheiten_arr);
        $f = new formular ();
        $f->fieldset("Heizkostenverbrauch / Heizkostenabrechnung - $this->p_bez", 'f_hk');
        echo "<table class='striped'><thead>";
        echo "<tr><th>Einheit</th><th>Eigentümer</th><th>Von</th><th>Bis</th><th>Tage</th><th>Heizkosten</th></tr></thead>";
        $z = 1;
        $sum = 0;
        for ($a = 0; $a < $anz_e; $a++) {

            $einheit_id = $einheiten_arr [$a] ['EINHEIT_ID'];
            $einheit_kn = $einheiten_arr [$a] ['EINHEIT_KURZNAME'];
            $eig_arr = $this->get_eigentuemer_arr_jahr($einheit_id, $this->p_jahr);

            $anz_eig = count($eig_arr);
            for ($b = 0; $b < $anz_eig; $b++) {
                $eig_id = $eig_arr [$b] ['ID'];
                $von = date_mysql2german($eig_arr [$b] ['VON']);
                $bis = date_mysql2german($eig_arr [$b] ['BIS']);
                $tage = $eig_arr [$b] ['TAGE'];
                $this->get_eigentumer_id_infos2($eig_id);
                $betrag_a = nummer_punkt2komma($this->get_hga_hk_betrag($p_id, $eig_id));
                $sum += nummer_komma2punkt($betrag_a);
                echo "<tr><td>$z.) $einheit_kn</td><td>$this->eigentuemer_name_str</td><td>$von</td><td>$bis</td><td>$tage</td><td>";
                $link_wert = "<a class=\"details\" onclick=\"change_hk_wert_et('Heizungsverbrauch', '$eig_id', '$betrag_a', '$p_id')\">$betrag_a</a>";
                echo $link_wert;
                $f->hidden_feld('eig_id[]', $eig_id);
                echo "</td></tr>";
                $z++;
            }
        }
        $sum_a = nummer_punkt2komma($sum);
        echo "<tr><td colspan=\"5\">SUMME</td><td>$sum_a<td></tr>";
        echo "</table>";
        $f->fieldset_ende();
    }

    function get_hga_hk_betrag($p_id, $et_id)
    {
        $result = DB::select("SELECT BETRAG FROM `WEG_HGA_HK` WHERE KOS_TYP='Eigentuemer' && `KOS_ID` ='$et_id' AND  `WEG_HGA_ID` ='$p_id' AND  `AKTUELL` =  '1' ORDER BY DAT DESC LIMIT 0,1");
        if (!empty($result)) {
            $row = $result[0];
            return $row ['BETRAG'];
        } else {
            return '0.00';
        }
    }

    function hga_zeile_speichern($profil_id, $konto, $art, $text, $gen_key_id, $betrag, $hndl_betrag, $kos_typ, $kos_id)
    {
        $id = last_id2('WEG_HGA_ZEILEN', 'ID') + 1;
        $betrag_a = nummer_komma2punkt($betrag);
        $hndl_betrag_a = nummer_komma2punkt($hndl_betrag);
        $db_abfrage = "INSERT INTO WEG_HGA_ZEILEN VALUES (NULL, '$id', '$profil_id', '$konto','$art', '$text', '$gen_key_id','$betrag_a','$hndl_betrag_a', '$kos_typ','$kos_id','1', false)";
        DB::insert($db_abfrage);
        /* Protokollieren */
        $last_dat = DB::getPdo()->lastInsertId();
        protokollieren('WEG_HGA_ZEILEN', '0', $last_dat);
    }

    function hga_profil_speichern($objekt_id, $gk_id, $jahr, $bez, $gk_id_ihr, $wp_id, $hg_konto, $hk_konto, $ihr_konto, $von = '', $bis = '')
    {
        if ($von == '') {
            $von = "$jahr-01-01";
        }
        if ($bis == '') {
            $bis = "$jahr-12-31";
        }

        $id = last_id2('WEG_HGA_PROFIL', 'ID') + 1;
        $db_abfrage = "INSERT INTO WEG_HGA_PROFIL VALUES (NULL, '$id', '$bez', '$jahr','$von','$bis', '$objekt_id', '$gk_id', '$gk_id_ihr', '$wp_id', '$hg_konto', '$hk_konto', '$ihr_konto', '1')";
        DB::insert($db_abfrage);
        /* Protokollieren */
        $last_dat = DB::getPdo()->lastInsertId();
        protokollieren('WEG_HGA_PROFIL', '0', $last_dat);
        session()->put('hga_profil_id', $id);
        session()->put('jahr', $jahr);
    }

    function hga_profil_aendern($profil_id, $objekt_id, $gk_id, $jahr, $bez, $gk_id_ihr, $wp_id, $hg_konto, $hk_konto, $ihr_konto, $von, $bis)
    {
        $db_abfrage = "UPDATE WEG_HGA_PROFIL SET AKTUELL='0' WHERE ID='$profil_id'";
        DB::update($db_abfrage);
        /* Protokollieren */
        protokollieren('WEG_HGA_PROFIL', $profil_id, $profil_id);

        $von = date_german2mysql($von);
        $bis = date_german2mysql($bis);

        $db_abfrage = "INSERT INTO WEG_HGA_PROFIL VALUES (NULL, '$profil_id', '$bez', '$jahr','$von','$bis', '$objekt_id', '$gk_id', '$gk_id_ihr', '$wp_id', '$hg_konto', '$hk_konto', '$ihr_konto', '1')";
        DB::insert($db_abfrage);
        /* Protokollieren */
        $last_dat = DB::getPdo()->lastInsertId();
        protokollieren('WEG_HGA_PROFIL', '0', $last_dat);

        session()->put('hga_profil_id', $profil_id);
        session()->put('jahr', $jahr);
    }

    function form_kontostand_erfassen()
    {
        if (!session()->has('geldkonto_id')) {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\InfoMessage("Bitte wählen Sie ein Geldkonto.")
            );
        } else {
            $b = new buchen ();
            $f = new formular ();
            $f->erstelle_formular("WEG->Geldkontenstände", '');
            $f->fieldset("Geldkontostand erfassen", 'gk_f');
            $f->datum_feld("Datum", 'datum', '', 'datum');
            $f->text_feld("Betrag", 'betrag', '', 10, 'betrag', '');
            $f->hidden_feld('option', 'kto_stand_send');
            $f->send_button("sendbtn", 'Speichern');
            $f->fieldset_ende();
            $f->ende_formular();
        }
    }

    function kontostand_speichern($gk_id, $datum, $betrag)
    {
        if (!$this->kontostand_check_exists($gk_id, $datum)) {
            $id = last_id2('WEG_KONTOSTAND', 'PLAN_ID') + 1;
            $betrag_db = nummer_komma2punkt($betrag);
            $db_abfrage = "INSERT INTO WEG_KONTOSTAND VALUES (NULL, '$id', '$gk_id', '$datum', '$betrag_db', '1')";
            DB::insert($db_abfrage);

            $last_dat = DB::getPdo()->lastInsertId();
            protokollieren('WEG_KONTOSTAND', '0', $last_dat);
            return true;
        } else {
            $datum = date_mysql2german($datum);
            echo "Kontostand vom $datum wurde bereits eingegeben.<br>Es können keine 2 Kontostände für den einen Tag eingegeben werden.";
        }
    }

    function kontostand_check_exists($gk_id, $datum)
    {
        $result = DB::select("SELECT ID FROM WEG_KONTOSTAND WHERE AKTUELL='1' && GK_ID='$gk_id' && DATUM='$datum'");
        return !empty($result);
    }

    function kontostand_anzeigen($gk_id)
    {
        $result = DB::select("SELECT * FROM WEG_KONTOSTAND WHERE AKTUELL='1' && GK_ID='$gk_id' ORDER BY DATUM ASC");
        if (!empty($result)) {
            echo "<table>";
            echo "<tr><th>DATUM</th><th>BETRAG</th></tr>";
            foreach ($result as $row) {
                $datum = date_mysql2german($row ['DATUM']);
                $betrag = $row ['BETRAG'];
                echo "<tr><td>$datum</td><td>$betrag</td></tr>";
            }
        }
    }

    /* Serienbriefe */

    function form_eigentuemer_checkliste($objekt_id)
    {
        $o = new objekt ();
        $o->get_objekt_infos($objekt_id);
        echo "<h5>Liste Eigentümer $o->objekt_kurzname</h5>";
        $einheiten_arr = $o->einheiten_objekt_arr($objekt_id);
        $anz_e = count($einheiten_arr);
        for ($a = 0; $a < $anz_e; $a++) {
            $einheit_id = $einheiten_arr [$a] ['EINHEIT_ID'];
            $eigent_arr [] = $this->get_eigentuemer_arr_2($einheit_id);
        }
        unset ($einheiten_arr);

        $f = new formular ();
        $f->erstelle_formular('Serienbrief an Eigentümer', null);
        echo "<div class='row'>";
        echo "<div class='input-field col-xs-12 col-md-6 col-lg-3'>";
        $f->send_button('Button', 'Vorlage wählen');
        echo "</div>";
        echo "<div class='input-field col-xs-12 col-md-6 col-lg-3'>";
        $f->send_button("delete", "Alle Löschen");
        echo "</div>";
        echo "</div>";
        echo "<div class='row'>";
        echo "<div class='input-field col-xs-12 col-md-6 col-lg-3'>";
        $f->check_box_js_alle('c_alle', 'c_alle', 1, 'Alle', '', '', 'eig_ids');
        echo "</div>";
        $anz_einheit = count($eigent_arr);
        for ($a = 0; $a < $anz_einheit; $a++) {
            echo "<div class='input-field col-xs-12 col-md-6 col-lg-3'>";
            $eig_id = $eigent_arr [$a] [0] ['ID'];
            $this->get_eigentumer_id_infos2($eig_id);

            if (session()->has('eig_ids') && in_array($eig_id, session()->get('eig_ids'))) {
                $f->check_box_js1('eig_ids[]', 'eig_id_' . $eig_id, $eig_id, "$this->einheit_kurzname $this->eigentuemer_name_str", '', 'checked');
            } else {
                $f->check_box_js1('eig_ids[]', 'eig_id' . $eig_id, $eig_id, "$this->einheit_kurzname $this->eigentuemer_name_str", '', '');
            }
            echo "</div>";
        }
        echo "</div>";
        $f->hidden_feld('option', 'serien_brief_vorlagenwahl');
        echo "<div class='row'>";
        echo "<div class='input-field col-xs-12 col-md-6 col-lg-3'>";
        $f->send_button('Button', 'Vorlage wählen');
        echo "</div>";
        echo "</div>";
        $f->ende_formular();
    }

    function form_hausgeldzahlungen($objekt_id)
    {
        if (!request()->has('jahr')) {
            $jahr = date("Y");
        } else {
            $jahr = request()->input('jahr');
        }
        $o = new objekt ();
        $o->get_objekt_infos($objekt_id);
        echo "<h5>OBJEKT:$o->objekt_kurzname</h5>";
        $gk = new geldkonto_info ();
        $gk_ids = $gk->geldkonten_arr('Objekt', $objekt_id);
        if (!empty($gk_ids)) {
            $anz = count($gk_ids);
            echo "<br>$anz Konten<br>";
            for ($a = 0; $a < $anz; $a++) {
                $gk_id = $gk_ids [$a] ['KONTO_ID'];
                $gk1 = new geldkonto_info ();
                $gk1->geld_konto_details($gk_id);
                fehlermeldung_ausgeben("$gk1->geldkonto_bez");
                $this->kontobuchungen_anzeigen_jahr($gk_id, $jahr);
            }
        } else {
            fehlermeldung_ausgeben("Keine Geldkonten dem Objekt zugewiesen!");
        }
    }

    function kontobuchungen_anzeigen_jahr($geldkonto_id, $jahr)
    {
        $result = DB::select("SELECT SUM(BETRAG) AS SUMME, KONTENRAHMEN_KONTO, `KOSTENTRAEGER_TYP`, `KOSTENTRAEGER_ID` FROM GELD_KONTO_BUCHUNGEN WHERE GELDKONTO_ID='$geldkonto_id' && ( DATE_FORMAT( DATUM, '%Y' ) = '$jahr') && AKTUELL='1' GROUP BY KONTENRAHMEN_KONTO, KOSTENTRAEGER_TYP, KOSTENTRAEGER_ID");
        if (!empty($result)) {
            $k = new kontenrahmen ();
            $kontenrahmen_id = $k->get_kontenrahmen('GELDKONTO', $geldkonto_id);

            $kto_temp = '';
            $z = 0;
            $kto_sum = 0;
            foreach ($result as $row) {
                $kostenkonto = $row ['KONTENRAHMEN_KONTO'];
                $k->konto_informationen2($kostenkonto, $kontenrahmen_id);
                if ($kostenkonto != $kto_temp) {
                    if ($z > 0) {
                        $kto_sum_a = nummer_punkt2komma_t($kto_sum);
                        echo "<tr><th colspan=\"3\">$kto_sum_a</th></tr>";
                        echo "</table>";
                        $kto_sum = 0;
                    }
                    echo "<table class=\"sortable striped\"><thead>";
                    echo "<tr><th colspan=\"3\">$kostenkonto $k->konto_bezeichnung</th></tr>";
                    echo "<tr><th>SUMME</th><th>ZUWEISUNG</th><th>ZUWEISUNG1</th></tr></thead>";
                }

                $betrag = nummer_punkt2komma($row ['SUMME']);
                $kto_sum += $row ['SUMME'];
                $kos_typ = $row ['KOSTENTRAEGER_TYP'];
                $kos_id = $row ['KOSTENTRAEGER_ID'];
                $r = new rechnung ();
                $kos_bez = $r->kostentraeger_ermitteln($kos_typ, $kos_id);

                if ($kos_typ == 'Mietvertrag') {
                    $mvs = new mietvertraege ();
                    $mvs->get_mietvertrag_infos_aktuell($kos_id);
                    $kos_bez_tmp = $kos_bez;
                    $kos_bez = "$mvs->einheit_kurzname $kos_bez_tmp";
                }

                echo "<tr><td>$betrag</td><td>$kos_typ</td><td>$kos_bez</td></tr>";

                $kto_temp = $kostenkonto;
                $z++;
            }
            $kto_sum_a = nummer_punkt2komma_t($kto_sum);
            echo "<tr><th colspan=\"3\">$kto_sum_a</th></tr>";
            echo "</table><br>";
        }
    }

    function form_hausgeldzahlungen_xls($objekt_id)
    {
        if (!request()->has('jahr')) {
            $jahr = date("Y");
        } else {
            $jahr = request()->input('jahr');
        }
        $o = new objekt ();
        $o->get_objekt_infos($objekt_id);
        echo "<h1>OBJEKT:$o->objekt_kurzname</h1>";
        $gk = new geldkonto_info ();
        $gk_ids = $gk->geldkonten_arr('Objekt', $objekt_id);
        if (!empty($gk_ids)) {
            $anz = count($gk_ids);
            echo "<br>$anz Konten<br>";
            for ($a = 0; $a < $anz; $a++) {
                $gk_id = $gk_ids [$a] ['KONTO_ID'];
                $gk1 = new geldkonto_info ();
                $gk1->geld_konto_details($gk_id);
                fehlermeldung_ausgeben("$gk1->geldkonto_bez");
                $this->kontobuchungen_anzeigen_jahr_xls($gk_id, $jahr);
            }
        } else {
            fehlermeldung_ausgeben("Keine Geldkonten dem Objekt zugewiesen!");
        }
    }

    function kontobuchungen_anzeigen_jahr_xls($geldkonto_id, $jahr)
    {
        $result = DB::select("SELECT SUM(BETRAG) AS SUMME, KONTENRAHMEN_KONTO, `KOSTENTRAEGER_TYP`, `KOSTENTRAEGER_ID` FROM GELD_KONTO_BUCHUNGEN WHERE GELDKONTO_ID='$geldkonto_id' && ( DATE_FORMAT( DATUM, '%Y' ) = '$jahr') && AKTUELL='1' GROUP BY KONTENRAHMEN_KONTO, KOSTENTRAEGER_TYP, KOSTENTRAEGER_ID");
        if (!empty($result)) {

            ob_clean(); // ausgabepuffer leeren
            $gk = new geldkonto_info ();
            $gk->geld_konto_details($geldkonto_id);
            $fileName = "$gk->geldkonto_bezeichnung - Buchungskonten summiert $jahr" . '.xls';
            header("Content-Type: application/vnd.ms-excel");
            header("Content-Disposition: inline; filename=$fileName");

            echo "<html><head>";
            echo "<meta http-equiv=\"content-type\" content=\"text/html; charset=UTF-8\">";
            echo "</head><body>";
            echo "<table class=\"sortable\">";
            echo "<tr><th>SUMME</th><th>ZUWEISUNG</th><th>BEZEICHNUNG</th></tr>";
            $k = new kontenrahmen ();
            $kontenrahmen_id = $k->get_kontenrahmen('GELDKONTO', $geldkonto_id);

            $kto_temp = '';
            $z = 0;
            $kto_sum = 0;
            foreach ($result as $row) {
                $kostenkonto = $row ['KONTENRAHMEN_KONTO'];
                $k->konto_informationen2($kostenkonto, $kontenrahmen_id);

                if ($kostenkonto != $kto_temp) {

                    if ($z > 0) {
                        $kto_sum_a = nummer_punkt2komma($kto_sum);
                        echo "<tr><th></th><th colspan=\"2\">$kto_sum_a</th></tr>";
                        $kto_sum = 0;
                    }
                    echo "<tr><th></th><th colspan=\"2\">Kostenkonto: $kostenkonto $k->konto_bezeichnung</th></tr>";
                }

                $betrag = nummer_punkt2komma($row ['SUMME']);
                $kto_sum += $row ['SUMME'];
                $kos_typ = $row ['KOSTENTRAEGER_TYP'];
                $kos_id = $row ['KOSTENTRAEGER_ID'];
                $r = new rechnung ();
                $kos_bez = $r->kostentraeger_ermitteln($kos_typ, $kos_id);

                if ($kos_typ == 'Mietvertrag') {
                    $mvs = new mietvertraege ();
                    $mvs->get_mietvertrag_infos_aktuell($kos_id);
                    $kos_bez_tmp = $kos_bez;
                    $kos_bez = "$mvs->einheit_kurzname $kos_bez_tmp";
                }

                echo "<tr><td>$betrag</td><td>$kos_typ</td><td>$kos_bez</td></tr>";

                $kto_temp = $kostenkonto;
                $z++;
            }
            $kto_sum_a = nummer_punkt2komma_t($kto_sum);
            echo "<tr><th colspan=\"3\">$kto_sum_a</th></tr>";
            echo "</table>";
            echo "</body></html>";
        }
    }

    function pdf_et_liste_alle_kurz($objekt_id)
    {
        $o = new objekt ();
        $o->objekt_informationen($objekt_id);

        $det1 = new detail ();
        $objekt_mea = $det1->finde_detail_inhalt('Objekt', $objekt_id, 'Gesamtanteile');

        $ein_arr = $this->einheiten_weg_tabelle_arr($objekt_id);

        $anz_e = count($ein_arr);

        $zeile = 0;
        /* schleife Einheiten */
        for ($e = 0; $e < $anz_e; $e++) {
            $einheit_id = $ein_arr [$e] ['EINHEIT_ID'];

            $det1 = new detail ();
            $einheit_mea = $det1->finde_detail_inhalt('Einheit', $einheit_id, 'WEG-Anteile');

            $weg = new weg ();
            $et_arr = $weg->get_eigentuemer_arr($einheit_id);

            $anz_et = count($et_arr);

            for ($et = 0; $et < $anz_et; $et++) {
                $et_id = $et_arr [$et] ['ID'];
                $weg1 = new weg ();
                $weg1->get_eigentumer_id_infos4($et_id);

                $pdf_tab [$zeile] ['P_DETAILS'] = '';
                for ($p = 0; $p < $weg1->anz_personen; $p++) {
                    $det1 = new detail ();
                    $person_id = $weg1->personen_id_arr1 [$p] ['PERSON_ID'];
                    $alle_details = $det1->finde_alle_details_arr('Person', $person_id);
                    $pers = new person ();
                    $pers->get_person_infos($person_id);
                    $pdf_tab [$zeile] ['P_DETAILS'] .= "<b>$pers->person_vorname $pers->person_nachname</b>\n";
                    $pdf_tab [$zeile] ['P_DETAILS'] .= "<b>Geb. am:</b> $pers->person_geburtstag\n";
                    for ($dd = 0; $dd < count($alle_details); $dd++) {

                        $pdf_tab [$zeile] ['P_DETAILS'] .= "<b>" . rtrim(ltrim(strip_tags($alle_details [$dd] ['DETAIL_NAME']))) . ":</b> " . rtrim(ltrim(strip_tags($alle_details [$dd] ['DETAIL_INHALT'])));
                        if ($dd < count($alle_details) - 1) {
                            $pdf_tab [$zeile] ['P_DETAILS'] .= "\n";
                        }
                    }
                    if ($p < $weg1->anz_personen - 1) {
                        $pdf_tab [$zeile] ['P_DETAILS'] .= "\n<b>++++++++++++++++++++++++++++++++++++++++++++++</b>\n";
                    }
                }
                unset ($alle_details);

                $pdf_tab [$zeile] ['EINHEIT_KN'] = $weg1->einheit_kurzname;
                $pdf_tab [$zeile] ['ET_NAME'] = $weg1->empf_namen;
                $pdf_tab [$zeile] ['VON'] = date_mysql2german($weg1->eigentuemer_von);
                $pdf_tab [$zeile] ['BIS'] = date_mysql2german($weg1->eigentuemer_bis);
                $pdf_tab [$zeile] ['EINHEIT_QM'] = $weg1->einheit_qm_weg_d;
                $pdf_tab [$zeile] ['EINHEIT_L'] = $weg1->einheit_lage;
                $pdf_tab [$zeile] ['MEA'] = $einheit_mea;
                $pdf_tab [$zeile] ['HAUS'] = $weg1->haus_strasse;
                $pdf_tab [$zeile] ['HAUSNR'] = $weg1->haus_nummer;
                $pdf_tab [$zeile] ['PLZ'] = $weg1->haus_plz;
                $pdf_tab [$zeile] ['ORT'] = $weg1->haus_stadt;

                $zeile++;
            }
        }

        $pdf = new Cezpdf ('a4', 'landscape');
        $bpdf = new b_pdf ();
        $bpdf->b_header($pdf, 'Partner', session()->get('partner_id'), 'landscape', 'Helvetica.afm', 6);
        $pdf->ezSetDy(15); // abstand
        $pdf->ezText("Objektname: $o->objekt_name, Gesamtanteile: $objekt_mea MEA", 12);
        $pdf->ezSetDy(-10); // abstand
        $cols = array(
            'EINHEIT_KN' => "EINHEIT",
            'ET_NAME' => "EIGENTÜMER",
            'P_DETAILS' => "DETAILS",
            'VON' => "VON",
            'BIS' => "BIS",
            'EINHEIT_QM' => "m²",
            'EINHEIT_L' => "LAGE",
            'MEA' => "MEA",
            'HAUS' => "Straße",
            'HAUSNR' => "Hausnr.",
            'PLZ' => "PLZ",
            'ORT' => "Ort"
        );
        $pdf->ezTable($pdf_tab, $cols, "$o->objekt_name", array(
            'showHeadings' => 1,
            'shaded' => 1,
            'titleFontSize' => 8,
            'fontSize' => 7,
            'xPos' => 40,
            'xOrientation' => 'right',
            'width' => 760,
            'cols' => array(
                'P_DETAILS' => array(
                    'justification' => 'left',
                    'width' => 200
                )
            )
        ));

        ob_end_clean(); // ausgabepuffer leeren
        $pdf->ezStream();
    }

    function stammdaten_weg($objekt_id, $export = null)
    {
        $o = new objekt ();
        $o->objekt_informationen($objekt_id);
        $arr = $this->einheiten_weg_tabelle_arr($objekt_id);
        $csv = $this->einheiten_weg_tabelle_arr($objekt_id);

        if (empty($arr)) {
            fehlermeldung_ausgeben("Keine Einheiten im Objekt");
        } else {
            $anz_e = count($arr);
            for ($a = 0; $a < $anz_e; $a++) {
                $einheit_id = $arr [$a] ['EINHEIT_ID'];
                $csv [$a] ['EINHEIT_ID'] = $einheit_id;
                /* Einheitdetails */
                $det = new details ();
                $arr [$a] ['E_DETAILS_ARR'] = $det->get_details('Einheit', $einheit_id);
                $det1 = new detail ();
                $arr [$a] ['EINHEIT_MEA'] = $det1->finde_detail_inhalt('Einheit', $einheit_id, 'WEG-Anteile');
                $csv [$a] ['EINHEIT_MEA'] = $arr [$a] ['EINHEIT_MEA'];

                $anz_e_det = count($arr [$a] ['E_DETAILS_ARR']);
                $det_string = "";
                for ($dd = 0; $dd < $anz_e_det; $dd++) {
                    $det_name = $arr [$a] ['E_DETAILS_ARR'] [$dd] ['DETAIL_NAME'];
                    $det_inhalt = bereinige_string($arr [$a] ['E_DETAILS_ARR'] [$dd] ['DETAIL_INHALT']);
                    $det_string .= "$det_name: $det_inhalt\n";
                }
                $arr [$a] ['E_DETAILS'] = $det_string;
                $csv [$a] ['E_DETAILS'] = $det_string;
                unset ($arr [$a] ['E_DETAILS_ARR']);

                $w = new weg ();
                $w->get_last_eigentuemer_id($einheit_id);
                $arr [$a] ['ET_ID'] = $w->eigentuemer_id;
                /* Geldkonto infos */
                $gk = new geldkonto_info ();
                $gk_arr = $gk->geldkonten_arr('Eigentuemer', $w->eigentuemer_id);
                $anz_gk = count($gk_arr);
                $gk_string = "";
                for ($g = 0; $g < $anz_gk; $g++) {
                    $gk_id = $gk_arr [$g] ['KONTO_ID'];
                    $sep = new sepa ();
                    $sep->get_sepa_konto_infos($gk_id);
                    $gk_string .= "<b>$sep->beguenstigter</b>\n$sep->IBAN1\n$sep->BIC\n";
                }
                $arr [$a] ['GK'] = $gk_string;
                $csv [$a] ['GK'] = $gk_string;

                $w->get_eigentumer_id_infos3($w->eigentuemer_id);
                $arr [$a] ['EINHEIT_QM_ET'] = $w->einheit_qm_weg;
                $csv [$a] ['EINHEIT_QM_ET'] = $w->einheit_qm_weg;
                // print_r($w);
                $arr [$a] ['ET_NAMEN'] = $w->empf_namen_u;
                $csv [$a] ['ET_NAMEN'] = $w->empf_namen_u;
                $arr [$a] ['P_INFO_ARR'] = $w->personen_id_arr1;
                $arr [$a] ['ET_ANZ'] = count($arr [$a] ['P_INFO_ARR']);
                if ($arr [$a] ['ET_ANZ'] > 0) {
                    for ($p = 0; $p < $arr [$a] ['ET_ANZ']; $p++) {
                        $det = new details ();
                        $p_id = $arr [$a] ['P_INFO_ARR'] [$p] ['PERSON_ID'];
                        $arr [$a] ['P_DETAILS'] [] = $det->get_details('Person', $p_id);
                    }
                    $det_string = "";
                    $anz_det_et = count($arr [$a] ['P_DETAILS']);
                    for ($dd = 0; $dd < $anz_det_et; $dd++) {
                        $anz_det_et1 = count($arr [$a] ['P_DETAILS'] [$dd]);
                        for ($dd1 = 0; $dd1 < $anz_det_et1; $dd1++) {
                            $det_name = $arr [$a] ['P_DETAILS'] [$dd] [$dd1] ['DETAIL_NAME'];
                            $det_inhalt = bereinige_string($arr [$a] ['P_DETAILS'] [$dd] [$dd1] ['DETAIL_INHALT']);
                            $det_string .= "$det_name: $det_inhalt\n";
                        }
                    }

                    $arr [$a] ['ET_DETAILS'] = $det_string;
                    $csv [$a] ['ET_DETAILS'] = $det_string;
                }

                echo "<br>";
            }
        }

        if ($export != null) {
            ob_clean();
            $ueberschrift = array_keys($csv [0]);
            $anz_k = count($ueberschrift);
            $csv_string = '';
            for ($k = 0; $k < $anz_k; $k++) {
                $csv_string .= $ueberschrift [$k] . ";";
            }
            $csv_string .= "\n";

            foreach ($csv as $key => $zeile) {
                foreach ($zeile as $ue => $wert) {
                    $wert_a = str_replace('<br>', ' ', bereinige_string($wert));
                    $csv_string .= "\"$wert_a\";";
                }
                $csv_string .= "\n";
            }
            return $csv_string;
        }
        if (request()->has('csv')) {
            ob_clean();
            header("Content-Disposition: attachment; filename='ET.CSV");
            $ueberschrift = array_keys($csv [0]);
            $anz_k = count($ueberschrift);
            for ($k = 0; $k < $anz_k; $k++) {
                echo $ueberschrift [$k] . ";";
            }
            echo "\n";

            foreach ($csv as $key => $zeile) {
                foreach ($zeile as $ue => $wert) {
                    $wert_a = str_replace('<br>', ' ', bereinige_string($wert));
                    echo "\"$wert_a\";";
                }
                echo "\n";
            }
            return;
        }

        $pdf = new Cezpdf ('a4', 'landscape');
        $bpdf = new b_pdf ();
        $bpdf->b_header($pdf, 'Partner', session()->get('partner_id'), 'landscape', 'Helvetica.afm', 5);
        $pdf->ezStopPageNumbers(); // seitennummerirung beenden
        $p = new partners ();
        $p->get_partner_info(session()->get('partner_id'));
        if (request()->has('lang') && request()->input('lang') == 'en') {
            $cols = array(
                'EINHEIT_KURZNAME' => "AP",
                'HAUS_STRASSE' => "STREET",
                'HAUS_NUMMER' => "NO.",
                'TYP' => "TYP",
                'EINHEIT_LAGE' => "LOCATION",
                'ET_NAMEN' => "OWNER",
                'ET_DETAILS' => "DETAILS",
                'GK' => "BANC ACCOUNT",
                'EINHEIT_QM' => "Tm²",
                'EINHEIT_QM_ET' => "Om²",
                'EINHEIT_MEA' => "MEA"
            );
        } else {
            $cols = array(
                'EINHEIT_KURZNAME' => "AP",
                'HAUS_STRASSE' => "STREET",
                'HAUS_NUMMER' => "NO.",
                'TYP' => "TYP",
                'EINHEIT_LAGE' => "LOCATION",
                'ET_NAMEN' => "OWNER",
                'ET_DETAILS' => "DETAILS",
                'GK' => "BANC ACCOUNT",
                'EINHEIT_QM' => "Tm²",
                'EINHEIT_QM_ET' => "Om²",
                'EINHEIT_MEA' => "MEA"
            );
        }

        $pdf->ezTable($arr, $cols, "$o->objekt_name", array(
            'showHeadings' => 1,
            'shaded' => 1,
            'shadeCol' => array(
                0.9,
                0.9,
                0.9
            ),
            'titleFontSize' => 14,
            'fontSize' => 9,
            'xPos' => 25,
            'xOrientation' => 'right',
            'width' => 800
        ));
        ob_end_clean(); // ausgabepuffer leeren
        $pdf->ezStream();
    } // end function

    function pdf_hausgelder($objekt_id, $jahr)
    {
        $monat = 12;
        $vorjahr = $jahr - 1;
        $nachjahr = $jahr + 1;

        $o = new objekt ();
        $einheiten_arr = $o->einheiten_objekt_arr($objekt_id);
        if (!empty($einheiten_arr)) {
            $anz = count($einheiten_arr);
            echo "<table class='striped'><thead>";
            echo "<tr><th>Einheit</th><th>$monat.$vorjahr</th><th>$monat.$jahr</th><th>$monat.$nachjahr</th></tr></thead>";
            $sum_monvj = 0;
            $sum_mon = 0;
            $sum_monnj = 0;
            for ($a = 0; $a < $anz; $a++) {
                $einheit_id = $einheiten_arr [$a] ['EINHEIT_ID'];
                $e = new einheit ();
                $e->get_einheit_info($einheit_id);

                $hg_monvj = nummer_punkt2komma_t($this->get_sume_hausgeld('Einheit', $einheit_id, $monat, $vorjahr) * -1);
                $hg_mon = nummer_punkt2komma_t($this->get_sume_hausgeld('Einheit', $einheit_id, $monat, $jahr) * -1);
                $hg_monnj = nummer_punkt2komma_t($this->get_sume_hausgeld('Einheit', $einheit_id, $monat, $nachjahr) * -1);

                $sum_monvj += $this->get_sume_hausgeld('Einheit', $einheit_id, $monat, $vorjahr) * -1;
                $sum_mon += $this->get_sume_hausgeld('Einheit', $einheit_id, $monat, $jahr) * -1;
                $sum_monnj += $this->get_sume_hausgeld('Einheit', $einheit_id, $monat, $nachjahr) * -1;
                echo "<tr><td>$e->einheit_kurzname</td><td>$hg_monvj EUR</td><td>$hg_mon EUR</td><td>$hg_monnj EUR</td></tr>";
            }
            $sum_monvj_a = nummer_punkt2komma_t($sum_monvj);
            $sum_mon_a = nummer_punkt2komma_t($sum_mon);
            $sum_monnj_a = nummer_punkt2komma_t($sum_monnj);
            echo "<tr><td>SUMME</td><td>$sum_monvj_a EUR</td><td>$sum_mon_a EUR</td><td>$sum_monnj_a EUR</td></tr>";
            echo "</table>";
        } else {
            fehlermeldung_ausgeben("Keine Einheiten im Objekt!");
        }
    }

    function form_hga_profil_grunddaten($profil_id)
    {
        $this->get_hga_profil_infos($profil_id);
        $f = new formular ();
        $f->erstelle_formular("Grunddaten des HGA-Profils ändern $this->p_bez", null);
        $f->text_feld('Profilbezeichnung eingeben', 'profilbez', $this->p_bez, '50', 'profilbez', '');
        $this->dropdown_weg_objekte('WEG-Verwaltungsobjekt wählen', 'objekt_id', 'objekt_id', $this->p_objekt_id);
        $f->text_feld('Jahr eingeben', 'jahr', $this->p_jahr, 5, 'jahr', '');
        $f->datum_feld('Berechnung von', 'p_von', $this->p_von_d, 'p_von');
        $f->datum_feld('Berechnung bis', 'p_bis', $this->p_bis_d, 'p_bis');

        $gk = new gk ();
        $gk->dropdown_geldkonten_alle_vorwahl('Hausgeldkonto wählen', 'geldkonto_id', 'geldkonto_id', $this->p_gk_id, null);
        $gk->dropdown_geldkonten_alle_vorwahl('Geldkonto für die IHR wählen', 'gk_id_ihr', 'gk_id_ihr', $this->p_ihr_gk_id, null);
        $this->dropdown_wps_alle('Dazugehörigen Wirtschaftsplan wählen', 'wp_id', 'wp_id', '', $this->p_wplan_id);
        $kk = new kontenrahmen ();
        $kk->dropdown_kontorahmenkonten_vorwahl('Konto für Hausgeldeinnahmen für Kosten wählen', 'hg_konto', 'hg_konto', 'Geldkonto', $this->p_gk_id, '', $this->hg_konto);
        $kk->dropdown_kontorahmenkonten_vorwahl('Konto für Hausgeldeinnahmen für Heizkostenkosten wählen', 'hk_konto', 'hk_konto', 'Geldkonto', $this->p_gk_id, '', $this->hk_konto);
        $kk->dropdown_kontorahmenkonten_vorwahl('Konto für Hausgeldeinnahmen für die IHR wählen', 'ihr_konto', 'ihr_konto', 'Geldkonto', $this->p_gk_id, '', $this->ihr_konto);
        $f->hidden_feld('option', 'profil_send_gaendert');
        $f->hidden_feld('profil_id', $profil_id);
        $f->send_button('send', 'Änderungen speichern');
        $f->ende_formular();
    }
} // end class
