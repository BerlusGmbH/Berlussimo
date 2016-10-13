<?php

class bk
{

    public $bbk_be_buchung_id;
    public $buchung_betrag;
    public $bbk_kos_typ;
    public $bbk_kos_id;
    public $bbk_kos_bez;
    public $vzweck;
    public $bbk_key_id;
    public $summe_kosten_konto;
    public $summe_kosten_konto_a;
    public $bk_verrechnungs_datum;
    public $konto_bez;
    public $bk_kos_typ;
    public $bk_kos_id;
    public $bk_verrechnungs_datum_d;
    public $bbk_anteil;
    public $bbk_hndl_betrag;
    public $key_daten;
    public $g_key_g_var;
    public $g_key_name;
    public $bk_berechnungs_datum_d;
    public $bk_profil_id;
    public $bk_jahr;
    public $bbk_be_dat;
    public $bk_an_anpassung_ab;
    public $bk_an_keyid;
    public $bk_an_fest_betrag;
    public $bk_an_grund;
    public $bk_an_id;
    public $bk_an_dat;
    public $anzahl_anpassungen;
    public $bk_kos_bez;
    public $bk_bezeichnung;
    public $profil_id;
    public $bk_berechnungs_datum;
    public $wirt_g_qm_wohnen_a;
    public $wirt_g_qm_gewerbe_a;
    public $g_key_me;
    public $buchungsdatum;
    public $footer_zahlungshinweis;
    public $kontenrahmen_id;
    public $konto_hndl;
    public $konto_gkey;
    public $konto;
    public $g_key_e_var;
    public $buchung_betrag_a;
    public $objekt_schluessel_einheit;
    public $objekt_schluessel;
    public $datum;
    public $b_kos_id;
    public $b_kos_typ;
    public $u_kontierung;
    public $wirt_g_qm_gewerbe;
    public $wirt_ges_qm;
    public $wirt_ges_qm_a;
    public $wirt_g_qm_wohnen;
    public $bbk_profil_id;
    public $bbk_be_id;

    /*
	 * Liefert ein Array mit allen Kostenkonten zu einer Kontengruppe, gesucht nach Gruppenbezeichnung
	 * z.B. Umlagefähige Kosten
	 */
    function update_prozent_umlage($profil_id, $bk_konto_id, $prozent)
    {
        $prozent = nummer_komma2punkt($prozent);

        $db_abfrage = "SELECT BUCHUNG_ID FROM BK_BERECHNUNG_BUCHUNGEN WHERE AKTUELL='1' && BK_K_ID='$bk_konto_id' && BK_PROFIL_ID='$profil_id'";
        $result = DB::select($db_abfrage);
        if (!empty($result)) {
            foreach ($result as $row) {
                $buchung_id = $row['BUCHUNG_ID'];
                $ursprung_summe = $this->get_summe($buchung_id);
                DB::update("UPDATE BK_BERECHNUNG_BUCHUNGEN SET ANTEIL='$prozent', HNDL_BETRAG=($ursprung_summe/100)*$prozent  WHERE BUCHUNG_ID='$buchung_id' && BK_K_ID='$bk_konto_id' && BK_PROFIL_ID='$profil_id' && AKTUELL='1'");
            }
        }
    }

    function get_summe($buchungs_id)
    {
        $db_abfrage = "SELECT BETRAG FROM GELD_KONTO_BUCHUNGEN WHERE AKTUELL='1' && GELD_KONTO_BUCHUNGEN_ID='$buchungs_id' ORDER BY GELD_KONTO_BUCHUNGEN_DAT DESC LIMIT 0,1";
        $result = DB::select($db_abfrage);
        if (!empty($result)) {
            return $result[0]['BETRAG'];
        }
    }

    function update_bk_buchung($bk_be_id, $umlagebetrag, $kostentraeger_typ, $kostentraeger_bez, $genkey, $hndl_betrag)
    {
        $this->get_bk_buchung_details($bk_be_id, session()->get('profil_id'));
        $this->bk_buchungen_details($this->bbk_be_buchung_id);

        $anteil_prozent = nummer_komma2punkt($umlagebetrag) / ($this->buchung_betrag / 100);

        $b = new buchen ();
        $kostentraeger_id = $b->kostentraeger_id_ermitteln($kostentraeger_typ, $kostentraeger_bez);

        DB::update("UPDATE BK_BERECHNUNG_BUCHUNGEN SET ANTEIL='$anteil_prozent' , KEY_ID='$genkey' ,KOSTENTRAEGER_TYP='$kostentraeger_typ' , KOSTENTRAEGER_ID='$kostentraeger_id', HNDL_BETRAG='$hndl_betrag' WHERE BK_BE_ID='$bk_be_id'");
    }

    function get_bk_buchung_details($be_id, $profil_id)
    {
        $result = DB::select("SELECT *  FROM `BK_BERECHNUNG_BUCHUNGEN` WHERE AKTUELL='1' && BK_BE_ID='$be_id' && BK_PROFIL_ID='$profil_id'");

        $row = $result[0];

        $this->bbk_be_dat = $row ['BK_BE_DAT'];
        $this->bbk_be_id = $row ['BK_BE_ID'];
        $this->bbk_be_buchung_id = $row ['BUCHUNG_ID'];
        $this->bbk_profil_id = $row ['BK_PROFIL_ID'];
        $this->bbk_key_id = $row ['KEY_ID'];
        $this->bbk_anteil = $row ['ANTEIL'];
        $this->bbk_kos_typ = $row ['KOSTENTRAEGER_TYP'];
        $this->bbk_kos_id = $row ['KOSTENTRAEGER_ID'];
        $this->bbk_hndl_betrag = $row ['HNDL_BETRAG'];

        $r = new rechnung ();
        $this->bbk_kos_bez = strip_tags($r->kostentraeger_ermitteln($this->bbk_kos_typ, $this->bbk_kos_id));
    }

    function bk_buchungen_details($buchung_id)
    {
        unset ($this->buchung_betrag);
        unset ($this->buchungsdatum);
        unset ($this->vzweck);
        $result = DB::select("SELECT * FROM `GELD_KONTO_BUCHUNGEN` WHERE `GELD_KONTO_BUCHUNGEN_ID` ='$buchung_id' AND `AKTUELL` = '1' LIMIT 0 , 1");
        if (!empty($result)) {
            $row = $result[0];

            $this->buchung_betrag = $row['BETRAG'];
            $this->buchungsdatum = $row['DATUM'];
            $this->vzweck = $row['VERWENDUNGSZWECK'];
            $kos_typ = $row ['KOSTENTRAEGER_TYP'];
            $kos_id = $row ['KOSTENTRAEGER_ID'];
            $this->b_kos_typ = $row ['KOSTENTRAEGER_TYP'];
            $this->b_kos_id = $row ['KOSTENTRAEGER_ID'];

            $r = new rechnung ();
            $kos_bez = $r->kostentraeger_ermitteln($kos_typ, $kos_id);
            $this->u_kontierung = $kos_bez;
        } else {
            $this->vzweck = "Keine Buchung unter Buchungsnummer $buchung_id";
        }
    }

    /**
     *
     * @param $bk_be_id
     * @param $profil_id
     */
    function form_buchung_anpassen($bk_be_id, $profil_id)
    {
        $this->get_bk_buchung_details($bk_be_id, $profil_id);
        $this->bk_buchungen_details($this->bbk_be_buchung_id);

        $f = new formular ();
        $f->erstelle_formular("Buchung anpassen2", NULL);
        $f->hidden_feld("bk_be_id", "$bk_be_id");
        $f->hidden_feld("buchung_id", "$this->bbk_be_buchung_id");
        $f->hidden_feld("profil_id", "$profil_id");
        $f->hidden_feld("option", "buchung_aendern");

        $f->text_feld_inaktiv("Kostentraeger", 'kos_typ', $this->bbk_kos_bez, 50, 'kos_typ');
        $f->text_feld_inaktiv("Vollbetrag", 'vollbetrag', nummer_punkt2komma($this->buchung_betrag), 10, 'vollbetrag');
        $f->text_feld_inaktiv("Buchungstext", 'vzweck', $this->vzweck, 100, 'vzweck');

        $umlagebetrag = ($this->buchung_betrag / 100) * $this->bbk_anteil;
        $f->text_feld("Umlagebetrag", 'umlagebetrag', nummer_punkt2komma($umlagebetrag), 10, 'umlagebetrag');
        $f->text_feld("Betrag HNDL vom Umlagebetrag", 'hndl_betrag', nummer_punkt2komma($this->bbk_hndl_betrag), 10, 'hndl_betrag', '');
        $buchung_key_info = $this->get_genkey_infos($this->bbk_key_id);
        $f->text_feld_inaktiv("Aktueller Berechnungsschlüssel", 'genkey', $buchung_key_info, 50, 'genkey');
        $buchung = new buchen ();
        $js_typ = "onchange=\"list_kostentraeger('list_kostentraeger', this.value)\"";
        $buchung->dropdown_kostentreager_typen('Kostenträgertyp wählen', 'kostentraeger_typ', 'kostentraeger_typ', $js_typ);
        $js_id = "";
        $buchung->dropdown_kostentreager_ids('Kostenträger', 'kostentraeger_id', 'dd_kostentraeger_id', $js_id);

        // $f->text_feld_inaktiv("Umlagebetrag", 'umlagebetrag',$umlagebetrag, 10, 'umlagebetrag');

        $this->dropdown_gen_keys();
        $f->hidden_feld("option", "buchung_aendern");
        $f->send_button("submit", "Ändern");
        $f->ende_formular();
    }

    function get_genkey_infos($key_id)
    {
        $result = DB::select("SELECT *  FROM BK_GENERAL_KEYS  WHERE GKEY_ID='$key_id' && AKTUELL='1'");
        if (!empty($result)) {
            $row = $result[0];
            $this->g_key_name = $row ['GKEY_NAME'];
            $this->g_key_g_var = $row ['G_VAR'];
            $this->g_key_e_var = $row ['E_VAR'];
            $this->g_key_me = $row ['ME'];

            return $this->g_key_name;
        } else {
            return 0;
        }
    }

    function dropdown_gen_keys()
    {
        $result = DB::select("SELECT * FROM BK_GENERAL_KEYS WHERE  AKTUELL='1'   ORDER BY GKEY_NAME ASC");

        if (!empty($result)) {
            echo "<div class='input-field'>";
            echo "<select id=\"genkeys\" name=\"genkey\" size=\"1\">";
            foreach ($result as $row) {
                $keyid = $row ['GKEY_ID'];
                $keyname = $row ['GKEY_NAME'];

                echo "<option value=\"$keyid\">$keyname</option>";
            }
            echo "</select><label for=\"genkeys\">Verteilerschlüssel</label>";
            echo "</div>";
        }
    }

    function assistent()
    {
        erstelle_abschnitt("Assistent für BK");
        /* Überprüfen ob Profil ausgewählt wurde */
        /* Falls nein, neues erstellen */
        if (!session()->has('profil_id')) {
            /* Profil auswählen oder neues anlegen */
            /* Wenn keine Daten gesendet für neues Profil */
            $fehler = true;
            if (!request()->isMethod('post')) {
                $this->form_profil_anlegen();
            } else {
                $bez = request()->input('profil_bez');
                $w_id = request()->input('w_id');
                $jahr = request()->input('jahr');
                $ber_datum = request()->input('berechnungsdatum');
                $ver_datum = request()->input('verrechnungsdatum');
                $this->profil_speichern($bez, $w_id, $jahr, $ber_datum, $ver_datum);
                $fehler = false;
                header('Location: ' . route('legacy::bk::index', ['option' => 'assistent'], false));
            }
        } else {
            $this->bk_profil_infos(session()->get('profil_id'));
            echo "Sie arbeiten im Profil für $this->bk_kos_bez<hr>";
        }
        // ##ende profil
        $link_neues_konto = "<a href='" . route('legacy::bk::index', ['option' => 'neues_bk_konto']) . "'>Neues Konto erstellen</b></a>";
        echo $link_neues_konto . '<hr>';

        if (!isset ($fehler)) {
            $k = new kontenrahmen ();
            $this->kontenrahmen_id = $k->get_kontenrahmen($this->bk_kos_typ, $this->bk_kos_id);
            // echo "Es wir der $this->kontenrahmen_id Kontenrahmen verwendet -<hr>";
            if (!$this->angelegte_konten(session()->get('profil_id'))) {
                fehlermeldung_ausgeben("<b>Keine angelegten Konten</b><hr>");
                $fehler = true;
                $this->form_eigenes_konto_anlegen(session()->get('profil_id'));
            } else {
                $fehler = false;
            }
        }

        if (session()->has('bk_konto') && !$fehler) {
            $bk_konto_id = $this->get_konto_id(session()->get('bk_konto'), session()->get('profil_id'));
            session()->put('bk_konto_id', $bk_konto_id);
            echo "<br>Buchungskonto " . session()->get('bk_konto') . " ausgewählt<br>";
        }

        // ##############

        if (session()->has('bk_konto') && session()->has('bk_konto_id')) {
            if (empty(session()->get('buchungen_arr'))) {
                $this->buchungsauswahl(session()->get('bk_konto'), session()->get('bk_konto_id'));
            }
        }

        ende_abschnitt();
    }

    function form_profil_anlegen()
    {
        $f = new formular ();
        $f->erstelle_formular("Neues Berechnungsprofil erstellen", NULL);
        $f->text_feld("Profilbezeichnung", "profil_bez", "", "50", 'profil_bez', '');
        $start_j = date("Y") - 2;
        $end_j = date("Y");
        $this->dropdown_jahr('Berechnungsjahr', 'jahr', 'jahr', $start_j, $end_j, '');
        $wirt = new wirt_e ();
        $wirt->dropdown_we('Wirtschaftseinheit wählen', 'w_id', 'w_id', '');
        $f->datum_feld('Berechnungsdatum', 'berechnungsdatum', '', 'berechnungsdatum');
        $f->datum_feld('Verrechnungsdatum', 'verrechnungsdatum', '', 'verrechnungsdatum');
        $f->hidden_feld("option", "assistent");
        $f->hidden_feld("option1", "profil");
        $f->send_button("submit_prof", "Profil erstellen");
        $f->ende_formular();
    }

    function dropdown_jahr($label, $name, $id, $start_j, $end_j, $js, $vorwahl = null)
    {
        echo "<label for=\"$id\">$label</label><select name=\"$name\" id=\"$id\" size=1 $js>\n";
        echo "<option value=\"\">Bitte wählen</option>\n";
        for ($a = $start_j; $a <= $end_j; $a++) {
            if ($vorwahl == null) {
                echo "<option value=\"$a\">$a</option>\n";
            } else {
                if ($vorwahl == $a) {
                    echo "<option value=\"$a\" selected>$a</option>\n";
                } else {
                    echo "<option value=\"$a\">$a</option>\n";
                }
            }
        }

        echo "</select>\n";
    }

    function profil_speichern($bez, $w_id, $jahr, $ber_datum, $ver_datum)
    {
        $last_bk_id = $this->last_id('BK_PROFILE', 'BK_ID') + 1;
        $ber_datum = date_german2mysql($ber_datum);
        $ver_datum = date_german2mysql($ver_datum);

        DB::insert("INSERT INTO BK_PROFILE VALUES (NULL, '$last_bk_id', '$bez', 'Wirtschaftseinheit', '$w_id','$jahr','$ber_datum','$ver_datum', '1')");
        session()->put('profil_id', $last_bk_id);
    }

    function last_id($tab, $spalte)
    {
        $result = DB::select("SELECT $spalte FROM `$tab` ORDER BY $spalte DESC LIMIT 0,1");
        if (!empty($result)) {
            return $result[0][$spalte];
        } else {
            return 0;
        }
    }

    /* Schritt 2 */

    function bk_profil_infos($profil_id)
    {
        unset ($this->bk_profil_id);
        unset ($this->bk_bezeichnung);
        $result = DB::select("SELECT * FROM `BK_PROFILE` WHERE BK_ID='$profil_id' && AKTUELL='1' ORDER BY BK_DAT DESC LIMIT 0,1");
        if (!empty($result)) {
            $row = $result[0];
            $this->bk_profil_id = $row ['BK_ID'];
            $this->bk_bezeichnung = $row ['BEZEICHNUNG'];
            $this->bk_kos_typ = $row ['TYP'];
            $this->bk_kos_id = $row ['TYP_ID'];
            $this->bk_jahr = $row ['JAHR'];
            $this->bk_berechnungs_datum = $row ['BERECHNUNGS_DATUM'];
            $this->bk_berechnungs_datum_d = date_mysql2german($this->bk_berechnungs_datum);
            $this->bk_verrechnungs_datum = $row ['VERRECHNUNGS_DATUM'];
            $this->bk_verrechnungs_datum_d = date_mysql2german($this->bk_verrechnungs_datum);
            $this->bk_jahr = $row ['JAHR'];
            if ($this->bk_kos_typ != 'Wirtschaftseinheit') {
                $r = new rechnung ();
                $this->bk_kos_bez = $r->kostentraeger_ermitteln($this->bk_kos_typ, $this->bk_kos_id);
            } else {
                $wirt = new wirt_e ();
                $wirt->get_wirt_e_infos($this->bk_kos_id);
                $this->bk_kos_bez = $wirt->w_name;
                $this->wirt_ges_qm = $wirt->g_qm;
                $this->wirt_g_qm_gewerbe = $wirt->g_qm_gewerbe;
                $this->wirt_g_qm_wohnen = $this->wirt_ges_qm - $this->wirt_g_qm_gewerbe;
                $this->wirt_ges_qm_a = nummer_punkt2komma($this->wirt_ges_qm);
                $this->wirt_g_qm_gewerbe_a = nummer_punkt2komma($this->wirt_g_qm_gewerbe);
                $this->wirt_g_qm_wohnen_a = nummer_punkt2komma($this->wirt_ges_qm - $this->wirt_g_qm_gewerbe);
            }
        }
    }

    /* Schritt 3 Teil1. */

    function angelegte_konten($profil_id)
    {
        $konten_arr = $this->bk_konten($profil_id);
        $anzahl_konten = count($konten_arr);

        if (!empty($konten_arr)) {
            echo "<div>";
            echo "<b>Im Profil angelegte Konten</b><br>";
            echo "</div>";

            echo "<table class=\"sortable striped\">";
            echo "<tr><th>Konto</th><th>Bezeichnung</th><th>Summe Auswahl</th><th>Durchschnitt</th><th>Umlage</th><th>OPTION</th></tr>";

            $g_summe = 0;
            $g_summe1 = 0;
            for ($a = 0; $a < $anzahl_konten; $a++) {
                $bk_k_id = $konten_arr [$a] ['BK_K_ID'];
                $konto = $konten_arr [$a] ['KONTO'];
                $konto_bez = $konten_arr [$a] ['KONTO_BEZ'];

                echo "<div class=\"zeile_gruen\">";
                $js = "onclick=\"konto_raus($bk_k_id,$profil_id)\"";
                $k = new kontenrahmen ();
                $k->konto_informationen2($konto, $this->kontenrahmen_id);

                $this->summe_kosten_ausgewaehlt($profil_id, $bk_k_id);
                $t_umlage = $this->summe_kosten_umgelegt($profil_id, $bk_k_id);

                $link_konto_pro_anpassen = "<a href='" . route('legacy::bk::index', ['option' => 'konto_pro_anpassen', 'bk_konto' => $konto, 'bk_konto_id' => $bk_k_id]) . "'>Umlage % anpassen</a>";
                $link_konto_raus = "<a $js><img src=\"images/x.png\" align=\"right\"></a><b>$konto</b>";
                $this->summe_kosten_konto_a = nummer_punkt2komma($this->summe_kosten_konto);

                $t_umlage_a = nummer_punkt2komma($t_umlage);

                echo "<tr><td>$link_konto_raus</td><td>$konto_bez</td><td  align=\"right\">$this->summe_kosten_konto_a </td><td  align=\"right\">$link_konto_pro_anpassen</td><td  align=\"right\">$t_umlage_a</td><td><a href='" . route('legacy::bk::index', ['option' => 'konto_auswahl', 'bk_konto' => $konto, 'bk_konto_id' => $bk_k_id]) . "'>Buchungen bearbeiten</a></td></tr>";
                $g_summe += $t_umlage;
                $g_summe1 += $this->summe_kosten_konto;
            }
            $g_summe_a = nummer_punkt2komma($g_summe);
            $g_summe1_a = nummer_punkt2komma($g_summe1);
            echo "<tfoot><tr><td></td><td><b>SUMME</b></td><td  align=\"right\"><b>$g_summe1_a</b></td><td  align=\"right\"></td><td  align=\"right\"><b>$g_summe_a</b></td><td></td></tr></tfoot>";
            // echo "</div>";
            echo "</table>";
            return true;
        }
    }

    /* Schritt 3 Teil2. - Buchungsauswahl */

    function bk_konten($profil_id)
    {
        $result = DB::select("SELECT * FROM `BK_PROFILE` JOIN BK_KONTEN ON ( BK_PROFILE.BK_ID = BK_KONTEN.BK_PROFIL_ID ) WHERE BK_PROFILE.BK_ID='$profil_id' && BK_KONTEN.AKTUELL='1' && BK_PROFILE.AKTUELL='1' ORDER BY KONTO ASC");
        return $result;
    }

    function summe_kosten_ausgewaehlt($profil_id, $konto_id)
    {
        $this->summe_kosten_konto = 0.00;
        $result = DB::select("SELECT BUCHUNG_ID FROM `BK_BERECHNUNG_BUCHUNGEN` WHERE  `BK_K_ID` ='$konto_id'  && BK_PROFIL_ID='$profil_id' && AKTUELL = '1'");
        if (!empty($result)) {
            foreach ($result as $row) {
                $buchung_id = $row ['BUCHUNG_ID'];
                $this->bk_buchungen_details($buchung_id);
                $this->summe_kosten_konto += $this->buchung_betrag;
            }
        }
    }

    function summe_kosten_umgelegt($profil_id, $konto_id)
    {
        $summe = 0;
        $result = DB::select("SELECT BUCHUNG_ID, ANTEIL FROM `BK_BERECHNUNG_BUCHUNGEN` WHERE  `BK_K_ID` ='$konto_id'  && BK_PROFIL_ID='$profil_id' && AKTUELL = '1'");
        if (!empty($result)) {
            foreach ($result as $row) {
                $buchung_id = $row ['BUCHUNG_ID'];
                $anteil = $row ['ANTEIL'];
                $this->bk_buchungen_details($buchung_id);
                $summe += ($this->buchung_betrag / 100) * $anteil;
            }
            return $summe;
        }
    }

    function form_eigenes_konto_anlegen($profil_id)
    {
        session()->forget('bk_konto');
        session()->forget('bk_konto_id');
        $f = new formular();
        $f->erstelle_formular("BK Konto im Profil anlegen", NULL);
        $f->hidden_feld("profil_id", "$profil_id");
        $f->text_feld("Kostenkonto (z.B. 2000)", 'kostenkonto', '', 10, 'kostenkonto', '');
        $f->text_feld("Kontobezeichnung (In Abrechnungen sichtbar!)", 'konto_bez', '', 50, 'konto_bez', '');
        $f->hidden_feld("option", "eig_konto_anlegen");
        $f->send_button("submit", "Hinzufügen");
        $f->ende_formular();
    }

    function get_konto_id($konto, $profil_id)
    {
        $result = DB::select("SELECT BK_K_ID  FROM `BK_KONTEN` WHERE AKTUELL='1' && BK_PROFIL_ID='$profil_id' && KONTO='$konto' ORDER BY BK_K_DAT DESC LIMIT 0,1");
        return $result[0]['BK_K_ID'];
    }

    function buchungsauswahl($konto, $konto_id)
    {
        if (request()->has('submit_anzeige')) {
            if (request()->has('anzeigen_von') && request()->has('anzeigen_bis')) {
                if (check_datum(request()->input('anzeigen_von') && check_datum(request()->input('anzeigen_bis')))) {
                    session()->put('anzeigen_von', request()->input('anzeigen_von'));
                    session()->put('anzeigen_bis', request()->input('anzeigen_bis'));
                }
                if (request()->has('konto_anzeigen')) {
                    session()->put('konto_anzeigen', request()->input('konto_anzeigen'));
                }
            }
            header("Location: " . route('legacy::bk::index', ['option' => 'assistent'], false));
        }
        $this->get_genkey_infos(session()->get('genkey'));
        $this->bk_profil_id = session()->get('profil_id');
        $this->bk_profil_infos($this->bk_profil_id);

        $k = new kontenrahmen();
        $this->kontenrahmen_id = $k->get_kontenrahmen($this->bk_kos_typ, $this->bk_kos_id);
        $k->konto_informationen2($konto, $this->kontenrahmen_id);

        // $berechnungs_arr = $this->bk_konten_berechnung($konto_id);
        $this->summe_kosten_ausgewaehlt($this->bk_profil_id, $konto_id);

        $f = new formular();

        if (session()->has('anzeigen_von') && session()->has('anzeigen_bis')) {
            $von = session()->get('anzeigen_von');
            $bis = session()->get('anzeigen_bis');
        } else {
            $von = "01.01.$this->bk_jahr";
            $bis = "31.12.$this->bk_jahr";
        }
        if (session()->has('konto_anzeigen')) {
            $konto = session()->get('konto_anzeigen');
        }

        $f->erstelle_formular('Buchungen filtern', '');
        $f->datum_feld('Von:', 'anzeigen_von', $von, 'anzeigen_von');
        $f->datum_feld('Bis:', 'anzeigen_bis', $bis, 'anzeigen_bis');
        $f->text_feld('Kostenkonto:', 'konto_anzeigen', $konto, 10, 'konto_anzeigen', '');
        $f->send_button("submit_anzeige", "Aktualisieren");
        $f->ende_formular();

        /* Buchungen zur Auswahl */
        $f->erstelle_formular('buchungen_hinzufuegen', '', 'Buchungen hinzufügen');
        echo "<div class='row'>";
        echo "<div class='col s12 m6 l3'>";
        $this->dropdown_gen_keys();
        echo "</div>";
        echo "<div class='col s12 m3 l3'>";
        $this->dropdown_hndl();
        echo "</div>";
        echo "<div class='col s12 m3 l3'>";
        $this->dropdown_uebernahme_kontierung();
        echo "</div>";
        $f->hidden_feld('option', 'buchungen_hinzu');
        echo "<div class='input-field col s12 m12 l3 right-align'>";
        $f->send_button("submit_key", "Bestehende Ändern");
        echo "</div>";
        $geldkonto_id = session()->get('geldkonto_id');
        $buchungen_arr = $this->bk_konten_buchungen_alle($geldkonto_id, $this->bk_jahr, $konto, $konto_id, $this->bk_profil_id);
        $anzahl_buchungen = count($buchungen_arr);
        if (!empty($buchungen_arr)) {
            echo "<table class=\"striped\" border=\"0\">";
            echo "<thead>";
            echo "<tr class=\"feldernamen\"><th>";
            $f->check_box_js_alle('uebernahme_alle[]', 'ue', '', 'Alle', '', '', 'uebernahme');
            echo "</th><th>BUCHUNGSNR</b></th><th>DATUM</th><th>BETRAG</th><th>RESTANTEIL</th><th>TEXT</th><th>KONTIERUNG</th></tr>";
            echo "</thead>";
            $zeile = 0;
            for ($g = 0; $g < $anzahl_buchungen; $g++) {
                $zeile++;
                $buchungs_id = $buchungen_arr [$g] ['GELD_KONTO_BUCHUNGEN_ID'];
                $datum = date_mysql2german($buchungen_arr [$g] ['DATUM']);
                $vzweck = $buchungen_arr [$g] ['VERWENDUNGSZWECK'];
                $betrag = nummer_punkt2komma($buchungen_arr [$g] ['BETRAG']);
                $kos_typ = $buchungen_arr [$g] ['KOSTENTRAEGER_TYP'];
                $kos_id = $buchungen_arr [$g] ['KOSTENTRAEGER_ID'];
                $gesamt_anteil1 = $this->gesamt_anteil($buchungs_id, $this->bk_profil_id, $konto_id);
                $max_anteil = 100 - $gesamt_anteil1;
                $betragp = nummer_komma2punkt($betrag);
                $max_umlage = nummer_punkt2komma(($betragp / 100) * $max_anteil);

                $r = new rechnung ();
                $kos_bez = $r->kostentraeger_ermitteln($kos_typ, $kos_id);

                if ($max_anteil == '100') {
                    $classe = 'zeilebk_' . $zeile . '_r';
                } else {
                    $classe = 'zeilebk_' . $zeile . '_gg';
                }
                $js = "onclick=\"buchung_hinzu($buchungs_id, $konto_id,$this->bk_profil_id)\"";
                $js1 = '';
                echo "<tr class=\"$classe\"><td>";
                $f->check_box_js('uebernahme[]', $buchungs_id, $buchungs_id, $js1, '');
                echo "</td><td><a $js><b>$buchungs_id</b></a></td><td> $datum </td><td>$betrag €</td><td>$max_anteil %=$max_umlage €</td><td>$vzweck</td><td> Kontierung: $kos_bez</td></tr>";

                if ($zeile == 2) {
                    $zeile = 0;
                }
            }

            echo "<tr><td>";
            echo "</td></tr>";
            echo "</table>";
            $f->send_button("submit_key", "Hinzufügen");
        } else {
            echo "<p style='clear:both;'>Es stehen keine weiteren Buchungen zum Kostenkonto $kostenkonto zur Auswahl.</p>";
        }
        $f->ende_formular();
        /* Buchungen schon ausgewählt */
        unset ($buchungen_arr);
        $buchungen_arr = $this->bk_konten_buchungen_hinzu($this->bk_profil_id, $konto_id);
        $anzahl_buchungen = count($buchungen_arr);

        $f->fieldset('Gewählte Buchungen', 'gb');
        if (!empty($buchungen_arr)) {
            echo "<table class=\"striped\" border=\"0\">";
            echo "<thead>";
            echo "<tr><th>BUCHUNGSNR</b></th><th>DATUM</th><th>BU-BETRAG</th><th>UML %</th><th>UMLAGE</th><th>HNDL</th><th>TEXT</th><th>WIRT.EINH.</th><th>KONTIERUNG</th><th>KEY</th><th>OPT.</th></tr>";
            echo "</thead>";
            $zeile = 0;
            $p_id = session()->get('profil_id');
            $sum_gb = 0;
            $sum_hndl = 0;
            $sum_umlage = 0;
            for ($g = 0; $g < $anzahl_buchungen; $g++) {
                $zeile++;
                $bk_be_id = $buchungen_arr [$g] ['BK_BE_ID'];
                $buchung_id = $buchungen_arr [$g] ['BUCHUNG_ID'];
                $link_anpassen = "<a href='" . route('legacy::bk::index', ['option' => 'buchung_anpassen', 'bk_be_id' => $bk_be_id, 'profil_id' => $p_id]) . "'><b>Berechnung anpassen</b></a>";
                $buchung_key_id = $buchungen_arr [$g] ['KEY_ID'];
                $anteil = $buchungen_arr [$g] ['ANTEIL'];
                $gesamt_anteil = $this->gesamt_anteil($buchung_id, $this->bk_profil_id, $konto_id);

                $kos_typ = $buchungen_arr [$g] ['KOSTENTRAEGER_TYP'];
                $kos_id = $buchungen_arr [$g] ['KOSTENTRAEGER_ID'];
                if ($kos_typ != 'Wirtschaftseinheit') {
                    $r = new rechnung ();
                    $kos_bez = $r->kostentraeger_ermitteln($kos_typ, $kos_id);
                } else {
                    $wirt = new wirt_e ();
                    $wirt->get_wirt_e_infos($kos_id);
                    $kos_bez = $wirt->w_name;
                }

                $this->bk_buchungen_details($buchung_id);
                $umlagebetrag = nummer_punkt2komma(($this->buchung_betrag / 100) * $anteil);
                $this->get_genkey_infos($buchung_key_id);
                $js = "onclick=\"buchung_raus($bk_be_id, $konto_id,$this->bk_profil_id);return;\"";
                // $js = 'buchung_hinzu($buchung_id, $konto_id,$profil_id)'
                $datum = date_mysql2german($this->buchungsdatum);
                $buchung_betrag = nummer_punkt2komma($this->buchung_betrag);

                $sum_umlage += $umlagebetrag;

                $sum_gb += $this->buchung_betrag;

                $hndl_betrag = $buchungen_arr [$g] ['HNDL_BETRAG'];
                $hndl_betrag_a = nummer_punkt2komma($hndl_betrag);

                $sum_hndl += $hndl_betrag;

                $this->bk_buchungen_details($buchung_id);

                if ($gesamt_anteil > 100) {
                    $gesamt_anteil = "<b>$gesamt_anteil</b>";
                }
                $classe = 'zeilebk_' . $zeile . '_g';

                if ($hndl_betrag < nummer_komma2punkt($umlagebetrag)) {
                    $classe = 'zeilebk_' . $zeile . '_r';
                    $hndl_betrag_a = "<b>$hndl_betrag_a</b>";
                }
                echo "<tr class=\"$classe\"><td><a $js><b>$buchung_id</b></a></td><td> $datum </td><td>$buchung_betrag</td><td>$anteil%</td><td>$umlagebetrag</td><td>$hndl_betrag_a</td><td>$this->vzweck</td><td>  $kos_bez</td><td>$this->u_kontierung</td>";
                echo "<td>$this->g_key_name</td><td>$link_anpassen</td></tr>";
                if ($zeile == 2) {
                    $zeile = 0;
                }
            }
            echo "</tr>";
            echo "<tr><td></td><td></td><td>$sum_gb</td><td></td><td>$sum_umlage</td><td>$sum_hndl</td></tr>";
            echo "</table>";
        } else {
            echo "Bisher keine ausgewaehlten Buchungen zum Kostenkonto $konto.";
        }
        $f->fieldset_ende();
    }

    function dropdown_hndl()
    {
        echo "<div class='input-field'>";
        echo "<select id=\"hndl\" name=\"hndl\" size=\"1\">";
        echo "<option value=\"1\">JA</option>";
        echo "<option value=\"0\" selected>NEIN</option>";
        echo "</select><label for=\"hndl\">Als HNDL ausweisen</label>";
        echo "</div>";
    }

    function dropdown_uebernahme_kontierung()
    {
        echo "<div class='input-field'>";
        echo "<select id=\"kontierung\" name=\"kontierung\" size=\"1\">";
        echo "<option value=\"1\">JA</option>";
        echo "<option value=\"0\" selected>NEIN</option>";
        echo "</select><label for=\"kontierung\">Kontieren wie Buchung</label>";
        echo "</div>";
    }

    function bk_konten_buchungen_alle($geldkonto_id, $jahr, $kostenkonto, $konto_id, $profil_id)
    {
        $vorjahr = $jahr - 1;
        $nachjahr = $jahr + 1;

        if (!session()->has('anzeigen_von') && !session()->has('anzeigen_bis')) {
            $result = DB::select("SELECT * FROM `GELD_KONTO_BUCHUNGEN` WHERE `GELDKONTO_ID` ='$geldkonto_id' AND `KONTENRAHMEN_KONTO` ='$kostenkonto' AND `AKTUELL` = '1' && DATE_FORMAT(DATUM, '%Y-%m') BETWEEN '$vorjahr-12' AND '$nachjahr-03' AND GELD_KONTO_BUCHUNGEN_ID NOT IN (SELECT BUCHUNG_ID FROM `BK_BERECHNUNG_BUCHUNGEN` WHERE `BK_K_ID` = '$konto_id' GROUP BY BUCHUNG_ID HAVING SUM( ANTEIL ) >= '100' ) ");
        } else {
            $von = date_german2mysql(session()->get('anzeigen_von'));
            $bis = date_german2mysql(session()->get('anzeigen_bis'));
            $result = DB::select("SELECT * FROM `GELD_KONTO_BUCHUNGEN` WHERE `GELDKONTO_ID` ='$geldkonto_id' AND `KONTENRAHMEN_KONTO` ='$kostenkonto' AND `AKTUELL` = '1' && DATE_FORMAT(DATUM, '%Y-%m-%d') BETWEEN '$von' AND '$bis' AND GELD_KONTO_BUCHUNGEN_ID NOT IN (SELECT BUCHUNG_ID FROM `BK_BERECHNUNG_BUCHUNGEN` WHERE `BK_K_ID` = '$konto_id' GROUP BY BUCHUNG_ID HAVING SUM( ANTEIL ) >= '100' ) ");
        }
        return $result;
    }

    function gesamt_anteil($buchung_id, $profil_id, $konto_id)
    {
        $result = DB::select("SELECT SUM( ANTEIL ) AS G_ANTEIL FROM `BK_BERECHNUNG_BUCHUNGEN` WHERE BUCHUNG_ID = '$buchung_id' && `BK_K_ID` = '$konto_id' AND BK_PROFIL_ID = '$profil_id' && `AKTUELL` = '1' GROUP BY BUCHUNG_ID");
        return $result[0]['G_ANTEIL'];
    }

    function bk_konten_buchungen_hinzu($profil_id, $konto_id)
    {
        $result = DB::select("SELECT BK_BE_ID, BUCHUNG_ID,  ANTEIL, KEY_ID, KOSTENTRAEGER_TYP, KOSTENTRAEGER_ID,HNDL_BETRAG FROM `BK_BERECHNUNG_BUCHUNGEN` WHERE  `BK_K_ID` ='$konto_id' AND BK_PROFIL_ID='$profil_id' && `AKTUELL` = '1'  ORDER BY BUCHUNG_ID ASC ");
        return $result;
    }

    function liste_bk_profile()
    {
        $result = DB::select("SELECT * FROM `BK_PROFILE` WHERE AKTUELL='1' ORDER BY BK_ID DESC");
        if (!empty($result)) {
            echo "<table class=\"sortable striped\">";
            echo "<thead><tr><th>Nr.</th><th>Berechnungsprofile</th><th>OPTIONEN</TH></tr></thead>";
            foreach ($result as $row) {
                $profil_id = $row ['BK_ID'];
                $bez = $row ['BEZEICHNUNG'];
                $link = "<a href='" . route('legacy::bk::index', ['option' => 'profil_set', 'profil_id' => $profil_id]) . "'>$bez</a><br>";
                $link_anpassen = "<a href='" . route('legacy::bk::index', ['option' => 'profil_anpassen', 'profil_id' => $profil_id]) . "'>Anpassen</a><br>";
                echo "<tr><td width=\"30px\">$profil_id</td><td>$link</td><td>$link_anpassen</td></tr>";
            }
            echo "</table>";
        }
    }

    function buchungskonten_auswahl()
    {
        if (!session()->has('profil_id')) {
            $this->form_profil_anlegen();
        } else {
            $profil_id = session()->get('profil_id');
            $this->bk_profil_infos($profil_id);
            $text = "Schritt 1 erfolgreich<br>Ausgewähltes Profil: $this->bk_bezeichnung";
            echo $text;
            echo "<hr>";
            /* 1. Kontenrahmen finden */
            $k = new kontenrahmen ();
            $this->kontenrahmen_id = $k->get_kontenrahmen($this->bk_kos_typ, $this->bk_kos_id);
            echo "Es wir der $this->kontenrahmen_id Kontenrahmen verwendet<br>";
            // $this->form_eigenes_konto_anlegen($profil_id);
            if (!$this->angelegte_konten($profil_id)) {
                echo "Keine angelegten Konten";
                $this->form_eigenes_konto_anlegen($profil_id);
            }
            // $this->auswahl_buchungskonten_kontenrahmen($this->kontenrahmen_id);
        }
    }

    function update_genkey($konto_id, $profil_id, $genkey_id, $hndl)
    {
        DB::update("UPDATE BK_KONTEN SET GENKEY_ID='$genkey_id', HNDL='$hndl'  WHERE BK_K_ID='$konto_id' && BK_PROFIL_ID= '$profil_id' && AKTUELL='1'");

        /* Dazugehörige Buchungen anpassen */

        $result = DB::select("SELECT BK_BE_DAT, BUCHUNG_ID, ANTEIL FROM BK_BERECHNUNG_BUCHUNGEN WHERE AKTUELL='1' && BK_K_ID='$konto_id' && BK_PROFIL_ID='$profil_id'");
        if (!empty($result)) {
            foreach ($result as $row) {
                $dat = $row['BK_BE_DAT'];
                $buchung_id = $row['BUCHUNG_ID'];
                $anteil = $row['ANTEIL'];
                $this->bk_buchungen_details($buchung_id);
                $this->bk_profil_infos($profil_id);
                if (session()->get('kontierung') == '1') {
                    $this->bk_buchungen_details($buchung_id);
                    $this->bk_kos_typ = $this->b_kos_typ;
                    $this->bk_kos_id = $this->b_kos_id;
                }
                if ($hndl == '1') {
                    $hndl_betrag_neu = ($this->buchung_betrag / 100) * $anteil;
                } else {
                    $hndl_betrag_neu = '0.000';
                }
                DB::update("UPDATE BK_BERECHNUNG_BUCHUNGEN SET HNDL_BETRAG='$hndl_betrag_neu', KEY_ID='$genkey_id', KOSTENTRAEGER_TYP='$this->bk_kos_typ', KOSTENTRAEGER_ID='$this->bk_kos_id' WHERE BK_BE_DAT='$dat'");
            }
        } else {
            echo "<h5>Fehler 34324324 - Keine Buchungen im Kostenkonto $konto_id</h5>";
        }
    }

    function buchungen_hinzu($buchung_id)
    {
        $bk = new bk ();
        $profil_id = session()->get('profil_id');
        $bk_konto_id = session()->get('bk_konto_id');
        $bk_genkey_id = session()->get('genkey');
        $bk_hndl = session()->get('hndl');

        if ($bk_hndl == '1') {
            $bk->bk_buchungen_details($buchung_id);
            $hndl_betrag = $bk->buchung_betrag;
        } else {
            $hndl_betrag = 0.00;
        }
        $kontierung_uebernehmen = session()->get('kontierung');
        if ($buchung_id && $profil_id && $bk_konto_id) {
            $bk->bk_profil_infos($profil_id);
            $gesamt_anteil = $bk->gesamt_anteil($buchung_id, $profil_id, $bk_konto_id);
            $max_anteil = 100 - $gesamt_anteil;
            if ($kontierung_uebernehmen == '1') {
                $bk->bk_buchungen_details($buchung_id);
                $bk->bk_kos_typ = $bk->b_kos_typ;
                $bk->bk_kos_id = $bk->b_kos_id;
            }
            $last_bk_be_id = last_id2('BK_BERECHNUNG_BUCHUNGEN', 'BK_BE_ID') + 1;
            DB::insert("INSERT INTO BK_BERECHNUNG_BUCHUNGEN VALUES(NULL, '$last_bk_be_id', '$buchung_id', '$bk_konto_id', '$profil_id','$bk_genkey_id', '$max_anteil','$bk->bk_kos_typ', '$bk->bk_kos_id','$hndl_betrag','1')");
        } else {
            echo "Fehler 888888 bk_class";
        }
    }

    function form_konto_pro_anpassen($profil_id, $bk_konto, $bk_konto_id)
    {
        $f = new formular ();
        $this->summe_kosten_ausgewaehlt($profil_id, $bk_konto_id);
        $summe = $this->summe_kosten_konto;
        $summe_a = nummer_punkt2komma($summe);
        $f->erstelle_formular("BK Konto $bk_konto prozentual umlegen", NULL);
        $f->hidden_feld("profil_id", "$profil_id");
        $f->hidden_feld("bk_konto_id", "$bk_konto_id");

        $f->text_feld_inaktiv('Summe der ausgewählten Buchungen', 's', $summe_a, 15, 's');
        $durchschnitt_jetzt = $this->get_durchschnitt_umlegen($profil_id, $bk_konto_id);
        $durchschnitt_jetzt_a = nummer_punkt2komma($durchschnitt_jetzt);

        $f->text_feld("Nach prozent", 'prozent', $durchschnitt_jetzt_a, 15, 'prozent', '');

        $umlagebetrag = ($summe / 100) * $durchschnitt_jetzt;
        $umlagebetrag_a = nummer_punkt2komma($umlagebetrag);
        // prozente(vollbetrag, teilbetrag, feld)
        $js_pro = "onkeyup=\"prozente('$summe', document.getElementById('umlagebetrag').value, 'prozent')\"";
        $f->text_feld("Umgelegt wird", 'umlagebetrag', $umlagebetrag_a, 15, 'umlagebetrag', $js_pro);
        $f->hidden_feld("option", "konto_pro_anpassen_send");
        $f->send_button("submit", "Anpassen");
        $f->ende_formular();
    }

    function get_durchschnitt_umlegen($profil_id, $bk_k_id)
    {
        $result = DB::select("SELECT AVG( ANTEIL ) AS D FROM `BK_BERECHNUNG_BUCHUNGEN` WHERE `BK_K_ID` = '$bk_k_id' && BK_PROFIL_ID='$profil_id'");
        return $result[0]['D'];
    }

    function profil_reset()
    {
        session()->forget('profil_id');
        session()->forget('bk_konto');
        session()->forget('bk_konto_id');
        session()->forget('genkey');
        session()->forget('geldkonto_id');
    }

    function form_bk_hk_anpassung($profil_id)
    {
        $f = new formular ();
        $f->erstelle_formular("BK / HK im Voraus anpassen", NULL);

        $anp_arr = $this->get_anpassung_infos2($profil_id);
        $anz = count($anp_arr);
        if ($anz) {
            echo "<table>";
            for ($a = 0; $a < $anz; $a++) {
                $dat = $anp_arr [$a] ['AN_DAT'];
                $grund = $anp_arr [$a] ['GRUND'];
                $festbetrag = nummer_punkt2komma($anp_arr [$a] ['FEST_BETRAG']);
                $gkey_id = $anp_arr [$a] ['KEY_ID'];
                $this->get_genkey_infos($gkey_id);
                $link_loeschen = "<a href='" . route('legacy::bk::index', ['option' => 'anpassung_bk_hk_del', 'an_dat' => $dat]) . "'>Löschen</a>";
                echo "<tr><td>$grund</td><td> $festbetrag €/ $this->g_key_me / Monat</td><td>$link_loeschen</td><br>";
            }
            echo "</table>";
        } else {
            echo "<p>Keine Anpassung im Profil eingetragen</p>";
        }

        if ($anz < 2) {
            echo "<label for=\"genkeys\">Kostenart wählen</label><select id=\"kostenart\" name=\"kostenart\" size=\"1\">";
            $this->get_anpassung_details($profil_id, 'Nebenkosten Vorauszahlung');
            if (!isset ($this->bk_an_dat)) {
                echo "<option value=\"Nebenkosten Vorauszahlung\">Nebenkosten Vorauszahlung</option>";
            }
            $this->get_anpassung_details($profil_id, 'Heizkosten Vorauszahlung');
            if (!isset ($this->bk_an_dat)) {
                echo "<option value=\"Heizkosten Vorauszahlung\">Heizkosten Vorauszahlung</option>";
            }
            echo "</select>";
            $f->text_feld("Betrag", "betrag", "", "10", 'betrag', '');
            $this->dropdown_gen_keys();
            $f->hidden_feld("profil_id", "$profil_id");
            $f->hidden_feld("option", "anpassung_send");
            $f->send_button("submit_prof", "Anpassung speichern");
        } else {
            echo "<br>Sie haben schon 2 Anpassungen vorgenommen, bitte eine löschen um weitere vornehmen zu können";
        }
        $f->ende_formular();
    }

    function get_anpassung_infos2($profil_id)
    {
        $db_abfrage = "SELECT * FROM BK_ANPASSUNG WHERE PROFIL_ID='$profil_id' && AKTUELL='1' ORDER BY GRUND ASC";
        $result = DB::select($db_abfrage);
        return $result;
    }

    function get_anpassung_details($profil_id, $kostenart)
    {
        unset ($this->bk_an_dat);
        unset ($this->bk_an_id);
        unset ($this->bk_an_grund);
        unset ($this->bk_an_fest_betrag);
        unset ($this->bk_an_key_id);
        unset ($this->bk_an_anpassung_ab);
        $db_abfrage = "SELECT * FROM BK_ANPASSUNG WHERE PROFIL_ID='$profil_id' && GRUND='$kostenart' && AKTUELL='1' ORDER BY AN_DAT DESC LIMIT 0,1";
        $result = DB::select($db_abfrage);
        if (!empty($result)) {
            $row = $result[0];
            $this->anzahl_anpassungen = $numrows;
            $this->bk_an_dat = $row ['AN_DAT'];
            $this->bk_an_id = $row ['AN_ID'];
            $this->bk_an_grund = $row ['GRUND'];
            $this->bk_an_fest_betrag = $row ['FEST_BETRAG'];
            $this->bk_an_keyid = $row ['KEY_ID'];
            $this->bk_an_anpassung_ab = $row ['ANPASSUNG_AB'];
        }
    }

    function bk_hk_anpassung_speichern($profil_id, $kostenart, $betrag, $genkey)
    {
        $last_id = $this->last_id('BK_ANPASSUNG', 'AN_ID') + 1;
        $betrag = nummer_komma2punkt($betrag);
        DB::insert("INSERT INTO BK_ANPASSUNG VALUES (NULL, '$last_id','$kostenart', '$betrag', '$genkey','$profil_id', '1')");
        return $last_id;
    }

    function bk_hk_anpassung_loeschen($an_dat)
    {
        DB::delete("DELETE FROM BK_ANPASSUNG WHERE AN_DAT='$an_dat'");
    }

    function form_bk_hk_anpassung_alle()
    {
        if (!session()->has('profil_id')) {
            fehlermeldung_ausgeben('BK Profil wählen');
            return;
        }
        $this->bk_profil_infos(session()->get('profil_id'));
        $datum_t_arr = explode('-', $this->bk_verrechnungs_datum);
        $jahr_t = $datum_t_arr [0];
        $monat_t = $datum_t_arr [1];

        $me = new miete ();
        $end_datum = $me->tage_minus($this->bk_verrechnungs_datum, 1);
        $end_datum_d = date_mysql2german($end_datum);
        $f = new formular ();
        $f->erstelle_formular("Anpassung der Mietdefinition: ($this->bk_bezeichnung für das Jahr $this->bk_jahr)", NULL);
        if (!session()->has('me_kostenkat')) {
            session()->put('me_kostenkat', 'Nebenkosten Vorauszahlung');
        }

        if (request()->has('me_kostenkat')) {
            session()->put('me_kostenkat', request()->input('me_kostenkat'));
        }
        $me = new mietentwicklung ();
        $me->dropdown_me_bk_hk('Kostenkategorie auswählen', 'me_kostenkat', session()->get('me_kostenkat'));
        $f->ende_formular();

        $f = new formular ();
        $f->erstelle_formular(session()->get('me_kostenkat') . " in der Mietdefinition ab $this->bk_verrechnungs_datum_d ändern", NULL);
        fehlermeldung_ausgeben("Alle Eingaben werden in der Mietdefinition zum $this->bk_verrechnungs_datum_d gespeichert!!! Verrechnungsdatum im Profil prüfen!!!<br><br>");
        $jahr = date("Y");

        $ob = new objekt ();
        $einheiten_array = $ob->einheiten_objekt_arr(session()->get('objekt_id'));
        $anz = count($einheiten_array);
        for ($a = 0; $a < $anz; $a++) {
            $bk = new bk ();
            $einheit_id = $einheiten_array [$a] ['EINHEIT_ID'];
            $einheit_kn = $einheiten_array [$a] ['EINHEIT_KURZNAME'];
            $arr [$a] ['MVS'] = $bk->mvs_und_leer_jahr($einheit_id, $this->bk_jahr);
            $arr [$a] ['EINHEIT_KURZNAME'] = $einheit_kn;
        }

        $anz = count($arr);

        echo "<table class='striped'><thead>";
        echo "<tr><th>ANZ</th><th>EINHEIT</th><th>MIETER</th><th>VON-BIS</th><th>AUSZUG</th><th>AKTUELL ZUM $this->bk_verrechnungs_datum_d</th><th>NEU ab $this->bk_verrechnungs_datum_d</th></tr>";
        echo "</thead>";
        $z = 0;
        for ($a = 0; $a < $anz; $a++) {
            $anz1 = count($arr [$a] ['MVS']);
            for ($b = 0; $b < $anz1; $b++) {

                $mv_id = $arr [$a] ['MVS'] [$b] ['KOS_ID'];
                $b_von = date_mysql2german($arr [$a] ['MVS'] [$b] ['BERECHNUNG_VON']);
                $b_bis = date_mysql2german($arr [$a] ['MVS'] [$b] ['BERECHNUNG_BIS']);

                if ($mv_id != 'Leerstand') {
                    $z++;
                    $mv = new mietvertraege ();
                    $mv->get_mietvertrag_infos_aktuell($mv_id);
                    $mz = new miete ();

                    $me = new mietentwicklung ();

                    $me_arr = $me->get_kostenkat_info_aktuell($mv_id, $monat_t, $jahr_t, session()->get('me_kostenkat'));
                    if (is_array($me_arr)) {
                        $betrag_akt = nummer_punkt2komma_t($me_arr ['BETRAG']);
                        $dat = $me_arr ['MIETENTWICKLUNG_DAT'];
                    } else {
                        $betrag_akt = nummer_punkt2komma_t(0.00);
                        $dat = '0';
                    }

                    echo "<tr><td>$z.</td><td>$mv->einheit_kurzname</td><td>$mv->personen_name_string</td><td>$b_von - $b_bis</td>";

                    if ($mv->mietvertrag_aktuell == 0) {
                        echo "<td class=\"rot\">$mv->mietvertrag_bis_d</td>";
                    } else {
                        echo "<td></td>";
                    }
                    echo "<td>";

                    $f->text_feld_inaktiv('AKTUELL', "vorschuss$z", $betrag_akt, 10, "vorschuss$z");
                    echo "</td>";
                    // echo "<td>$end_datum_d</td>";
                    echo "<td>";

                    $f->text_feld('Neuer Betrag', 'vorschuss_neu[]', '', 10, "vorschuss_neu$z", null);

                    echo "</td></tr>";
                    $f->hidden_feld("mvs[]", "$mv_id");
                    $f->hidden_feld("dat[]", "$dat");
                } else {
                    // echo "<tr><td>$mv_id auszug</td></tr>";
                    $einheit_kn = $arr [$a] ['EINHEIT_KURZNAME'];
                    echo "<tr class=\"rot\"><td class=\"rot\"></td><td class=\"rot\">$einheit_kn</td><td class=\"rot\">LEERSTAND</td><td class=\"rot\">$b_von - $b_bis</td><td class=\"rot\"></td><td class=\"rot\"></td><td class=\"rot\"></td></tr>";
                    /* echo "<tr><td class=\"gruen\">$einheit_kn</td><td class=\"gruen\"><b>LEERSTAND</b></td><td>$b_von</td><td>$b_bis</td><td>$tage</td><td></td><td></td><td></td></tr>"; */
                }
            }

            // $z++;
        }

        echo "<tr><td></td><td></td><td>";

        // $f->datum_feld('Verrechnungsdatum', 'v_datum', $this->bk_verrechnungs_datum_d, 'dd');
        echo "</td><td>";
        $f->hidden_feld("v_datum", "$this->bk_verrechnungs_datum_d");
        $f->hidden_feld("option", "me_send_hk_bk");
        $f->hidden_feld("kat", session()->get('me_kostenkat'));
        $f->hidden_feld("ende", $end_datum_d);
        $f->send_button("BtN_EN", "Werte in die\nMietdefinition speichern");
        echo "</td></tr></table>";
        $f->ende_formular();
    }

    function mvs_und_leer_jahr($einheit_id, $jahr)
    {
        $abfrage = "SELECT 'MIETVERTRAG' AS KOS_TYP, MIETVERTRAG_ID AS KOS_ID ,`MIETVERTRAG_VON`,`MIETVERTRAG_BIS`,`EINHEIT_ID`,
IF(DATE_FORMAT(MIETVERTRAG_VON, '%Y') < '$jahr', '$jahr-01-01', MIETVERTRAG_VON) AS BERECHNUNG_VON,
IF(DATE_FORMAT(MIETVERTRAG_BIS, '%Y') = '$jahr', MIETVERTRAG_BIS, '$jahr-12-31') AS BERECHNUNG_BIS,
DATEDIFF(IF(DATE_FORMAT(MIETVERTRAG_BIS, '%Y') = '$jahr', MIETVERTRAG_BIS, '$jahr-12-31'), IF(DATE_FORMAT(MIETVERTRAG_VON, '%Y') < '$jahr', '$jahr-01-01', MIETVERTRAG_VON))+1 AS TAGE FROM `MIETVERTRAG` WHERE `MIETVERTRAG_AKTUELL`='1' 
&& DATE_FORMAT(MIETVERTRAG_VON,'%Y') <= '$jahr' && (DATE_FORMAT(MIETVERTRAG_BIS,'%Y') >='$jahr' OR DATE_FORMAT(MIETVERTRAG_BIS,'%Y') ='0000') && EINHEIT_ID='$einheit_id' ORDER BY MIETVERTRAG_VON ASC";

        $my_array = DB::select($abfrage);

        /* Wenn überhaupt vermietet, sonst Leerstand ganzes Jahr, siehe unten nach ELSE */
        if (!empty($result)) {
            $anzahl_zeilen = count($my_array);
            $tage = 0;
            for ($a = 0; $a < $anzahl_zeilen; $a++) {
                $tage = $tage + $my_array [$a] ['TAGE'];
            }

            $tage_im_jahr = $this->tage_im_jahr($jahr);
            /* Voll vermietet */
            if ($tage == $tage_im_jahr) {
                /* Voll mermietet */
                // echo "GANZ VERMIETET = $tage == $tage_im_jahr<br>";
                return $my_array;
            }

            /* Nicht ganzes Jahr vermietet */
            if ($tage < $tage_im_jahr) {

                // echo "TEILWEISE VERMIETET = $tage <> $tage_im_jahr<br>";

                $summe_tage = 0;
                for ($a = 0; $a < $anzahl_zeilen; $a++) {

                    $berechnung_von = $my_array [$a] ['BERECHNUNG_VON'];
                    $berechnung_bis = $my_array [$a] ['BERECHNUNG_BIS'];

                    /* Wenn etwas am Anfang des Jahres fehlt */
                    if ($a == 0) {
                        $diff_zwischen_1_1 = $this->diff_in_tagen("$jahr-01-01", $berechnung_von);
                        if ($diff_zwischen_1_1 > 0) {
                            // echo "AM ANFANG FEHLEN $diff_zwischen_1_1 ($jahr-01-01 bis $berechnung_von)<br>";
                            $berechnung_von_a = $this->datum_minus_tage($berechnung_von, 1);
                            $my_array_neu [] = array(
                                'KOS_TYP' => 'Leerstand',
                                'KOS_ID' => 'Leerstand',
                                'BERECHNUNG_VON' => "$jahr-01-01",
                                'BERECHNUNG_BIS' => "$berechnung_von_a",
                                'TAGE' => $diff_zwischen_1_1
                            );
                            $summe_tage += $diff_zwischen_1_1;
                        } else {
                            // echo "AM ANFANG FEHLEN KEINE TAGE<br>";
                        }
                    }

                    /* Fehlzeiten / Leerstände zwischen den Verträgen */
                    /* In allen Zeilen ausser letzte */

                    if ($a < $anzahl_zeilen - 1) {
                        $berechnung_von_next = $my_array [$a + 1] ['BERECHNUNG_VON'];
                        $diff_zw_mvs = $this->diff_in_tagen($berechnung_bis, $berechnung_von_next) - 1;
                        if ($diff_zw_mvs > 1) {
                            // echo "ES FEHLEN $berechnung_bis $berechnung_von_next $diff_zw_mvs<br>";
                            $berechnung_bis = $this->datum_plus_tage($berechnung_bis, 1);
                            $berechnung_von_next = $this->datum_minus_tage($berechnung_von_next, 1);
                            $my_array_neu [] = array(
                                'KOS_TYP' => 'Leerstand',
                                'KOS_ID' => 'Leerstand',
                                'BERECHNUNG_VON' => $berechnung_bis,
                                'BERECHNUNG_BIS' => $berechnung_von_next,
                                'TAGE' => $diff_zw_mvs
                            );
                            $summe_tage += $diff_zw_mvs;
                        }
                    }

                    $berechnung_von = $my_array [$a] ['BERECHNUNG_VON'];
                    $berechnung_bis = $my_array [$a] ['BERECHNUNG_BIS'];
                    $tage_vermietet = $my_array [$a] ['TAGE'];
                    $mv_id = $my_array [$a] ['KOS_ID'];
                    $my_array_neu [] = array(
                        'KOS_TYP' => 'MIETVERTRAG',
                        'KOS_ID' => $mv_id,
                        'BERECHNUNG_VON' => $berechnung_von,
                        'BERECHNUNG_BIS' => $berechnung_bis,
                        'TAGE' => $tage_vermietet
                    );
                    $summe_tage += $tage_vermietet;

                    /* Wenn etwas am Ende des Jahres fehlt */
                    if ($a == $anzahl_zeilen - 1) {
                        $diff_zwischen_12_31 = $this->diff_in_tagen("$berechnung_bis", "$jahr-12-31");
                        if ($diff_zwischen_12_31 > 0) {

                            $summe_tage += $diff_zwischen_12_31;
                            $berechnung_bis_a = $this->datum_plus_tage($berechnung_bis, 1);

                            // echo "AM ENDE FEHLEN $diff_zwischen_12_31 ($berechnung_bis_a bis $jahr-12-31)<br>";
                            $my_array_neu [] = array(
                                'KOS_TYP' => 'Leerstand',
                                'KOS_ID' => 'Leerstand',
                                'BERECHNUNG_VON' => $berechnung_bis_a,
                                'BERECHNUNG_BIS' => "$jahr-12-31",
                                'TAGE' => $diff_zwischen_12_31
                            );
                        } else {
                            // echo "AM ENDE FEHLEN KEINE TAGE<br>";
                        }
                    }
                } // end for
                // echo "<b>SUMME TAGE $summe_tage</b><br>";
                $arr_sortiert = array_sortByIndex($my_array_neu, 'BERECHNUNG_VON');
                unset ($my_array_neu);
                return $arr_sortiert;
            } else {
                $error_msg = "ERROR BERLUSSIMO L501D (TAGE>TAGE IM JAHR) E:$einheit_id $jahr $tage<$tage_im_jahr\n";
                $error_msg .= "Wahrscheinlich 2 Mietverträge zur gleichen Zeit, bitte Anfangsdatum der mietvertraege der Einheit $einheit_id prüfen";
                throw new \App\Exceptions\MessageException(
                    new \App\Messages\ErrorMessage($error_msg)
                );
            }
        }  // end if $numrows
        else {
            // echo "Leerstand ganzes jahr";
            $tage_im_jahr = $this->tage_im_jahr($jahr);
            $my_array_neu [] = array(
                'KOS_TYP' => 'Leerstand',
                'KOS_ID' => 'Leerstand',
                'BERECHNUNG_VON' => "$jahr-01-01",
                'BERECHNUNG_BIS' => "$jahr-12-31",
                'TAGE' => $tage_im_jahr
            );
            return $my_array_neu;
        }
    }

    function tage_im_jahr($jahr)
    {
        if (date("L", mktime(0, 0, 0, 12, 31, $jahr)) == 1) {
            return 366;
        } else {
            return 365;
        }
    }

    function diff_in_tagen($datum_von, $datum_bis)
    {
        $start_datum_arr = explode("-", $datum_von);
        $tag = $start_datum_arr [2];
        $monat = $start_datum_arr [1];
        $jahr = $start_datum_arr [0];
        $beginn_datum = mktime(0, 0, 0, $monat, $tag, $jahr);

        $end_datum_arr = explode("-", $datum_bis);
        $tag1 = $end_datum_arr [2];
        $monat1 = $end_datum_arr [1];
        $jahr1 = $end_datum_arr [0];
        $end_datum = mktime(0, 0, 0, $monat1, $tag1, $jahr1);
        $tage_vergangen = round(($end_datum - $beginn_datum) / 86400, 0);
        return $tage_vergangen;
    }

    function datum_minus_tage($startdatum, $tage)
    {
        $db_datum = $startdatum;
        list ($db_y, $db_m, $db_t) = explode("-", $db_datum);
        $neues_datum = date("Y-m-d", mktime(0, 0, 0, $db_m, $db_t - $tage, $db_y));
        return $neues_datum;
    }

    /*
	 * Liefert die Summe aus geldkontobuchungen f�r einen bestimmten Zeitraum bezogen auf ein Kostenkonto
	 * z.B. Schornsteinfegerkosten von 1.1. bis 31.12/
	 */

    function datum_plus_tage($startdatum, $tage)
    {
        $db_datum = $startdatum;
        list ($db_y, $db_m, $db_t) = explode("-", $db_datum);
        $neues_datum = date("Y-m-d", mktime(0, 0, 0, $db_m, $db_t + $tage, $db_y));
        return $neues_datum;
    }

    function form_energie()
    {
        if (!session()->has('profil_id')) {
            fehlermeldung_ausgeben('BK Profil wählen');
            return;
        }
        $this->bk_profil_infos(session()->get('profil_id'));

        $f = new formular ();
        $f->erstelle_formular("Energiewerte eingeben: $this->bk_bezeichnung für das Jahr $this->bk_jahr", NULL);
        fehlermeldung_ausgeben("Alle Eingaben werden in der Mietdefinition zum $this->bk_verrechnungs_datum_d gespeichert!!! Verrechnungsdatum im Profil prüfen!!!<br><br>");
        $jahr = $this->bk_jahr;
        $ob = new objekt ();
        $einheiten_array = $ob->einheiten_objekt_arr(session()->get('objekt_id'));
        $anz = count($einheiten_array);
        for ($a = 0; $a < $anz; $a++) {
            $bk = new bk ();
            $einheit_id = $einheiten_array [$a] ['EINHEIT_ID'];
            $einheit_kn = $einheiten_array [$a] ['EINHEIT_KURZNAME'];
            $arr [$a] ['MVS'] = $bk->mvs_und_leer_jahr($einheit_id, $jahr);
            $arr [$a] ['EINHEIT_KURZNAME'] = $einheit_kn;
        }

        $anz = count($arr);
        echo "<table class='striped'>";
        echo "<thead>";
        echo "<tr><th>EINHEIT</th><th>MIETER</th><th>VON</th><th>BIS</th><th>TAGE</th><th>HK\nVORSCHÜSSE</th><th>HK VERBRAUCH</th><th>HK ERGEBNIS</th></tr>";
        echo "</thead>";
        $z = 0;
        for ($a = 0; $a < $anz; $a++) {
            $anz1 = count($arr [$a] ['MVS']);
            for ($b = 0; $b < $anz1; $b++) {

                $mv_id = $arr [$a] ['MVS'] [$b] ['KOS_ID'];
                $b_von = date_mysql2german($arr [$a] ['MVS'] [$b] ['BERECHNUNG_VON']);
                $b_bis = date_mysql2german($arr [$a] ['MVS'] [$b] ['BERECHNUNG_BIS']);
                $tage = $arr [$a] ['MVS'] [$b] ['TAGE'];
                if ($mv_id != 'Leerstand') {
                    $mv = new mietvertraege ();
                    $mv->get_mietvertrag_infos_aktuell($mv_id);
                    $mz = new miete ();
                    $summe_hk_jahr = $mz->summe_heizkosten_im_jahr('MIETVERTRAG', $mv_id, $jahr);

                    if ($tage < 365) {
                        echo "<tr><td class=\"rot\">$mv->einheit_kurzname</td><td class=\"rot\">$mv->personen_name_string</td><td class=\"rot\">$b_von</td><td class=\"rot\">$b_bis</td><td class=\"rot\">$tage</td><td class=\"rot\">";
                        $f->hidden_feld("mvs[]", "$mv_id");
                        $f->text_feld_inaktiv('Vorschuss' . $z, "vorschuss$z", $summe_hk_jahr, 10, "vorschuss$z");
                        echo "</td><td class=\"rot\">";
                        $js = " onkeyup=\"hk_diff('vorschuss$z', 'hk_verbrauch$z', 'hk_ergebnis$z');\"";
                        $me = new mietentwicklung ();
                        if ($me->check_me('MIETVERTRAG', $mv_id, "Energieverbrauch lt. Abr. $jahr", $this->bk_verrechnungs_datum, $this->bk_verrechnungs_datum) != true) {
                            $f->text_feld($mv->einheit_kurzname, "verbrauch[]", '', 7, "hk_verbrauch$z", " $js");
                        } else {
                            echo "erfasst";
                            $f->hidden_feld("verbrauch[]", "0");
                        }
                        echo "</td><td>";
                        $me = new mietentwicklung ();
                        if ($me->check_me('MIETVERTRAG', $mv_id, "Heizkostenabrechnung $jahr", $this->bk_verrechnungs_datum, $this->bk_verrechnungs_datum) != true) {
                            $f->text_feld('Ergebnis', 'ergebnisse[]', '', 10, "hk_ergebnis$z", null);
                        } else {
                            echo "erfasst";
                            $f->hidden_feld("ergebnisse[]", "0");
                        }
                        echo "</td></tr>";
                    } else {
                        echo "<tr><td>$mv->einheit_kurzname</td><td>$mv->personen_name_string</td><td>$b_von</td><td>$b_bis</td><td>$tage</td><td>";
                        $f->hidden_feld("mvs[]", "$mv_id");
                        $f->text_feld_inaktiv('Vorschuss' . $z, "vorschuss$z", $summe_hk_jahr, 10, "vorschuss$z");
                        echo "</td><td>";
                        $js = " onkeyup=\"hk_diff('vorschuss$z', 'hk_verbrauch$z', 'hk_ergebnis$z');\"";
                        $me = new mietentwicklung ();
                        if ($me->check_me('MIETVERTRAG', $mv_id, "Energieverbrauch lt. Abr. $jahr", $this->bk_verrechnungs_datum, $this->bk_verrechnungs_datum, 0) != true) {
                            $f->text_feld($mv->einheit_kurzname, "verbrauch[]", '', 7, "hk_verbrauch$z", " $js");
                        } else {
                            echo "erfasst";
                            $f->hidden_feld("verbrauch[]", "0");
                        }
                        echo "</td><td>";
                        $me = new mietentwicklung ();
                        if ($me->check_me('MIETVERTRAG', $mv_id, "Heizkostenabrechnung $jahr", $this->bk_verrechnungs_datum, $this->bk_verrechnungs_datum, 0) != true) {
                            $f->text_feld('Ergebnis', 'ergebnisse[]', '', 10, "hk_ergebnis$z", null);
                        } else {
                            echo "erfasst";
                            $f->hidden_feld("ergebnisse[]", "0");
                        }
                        echo "</td></tr>";
                    }
                } else {
                    $einheit_kn = $arr [$a] ['EINHEIT_KURZNAME'];
                    echo "<tr><td class=\"gruen\">$einheit_kn</td><td class=\"gruen\"><b>LEERSTAND</b></td><td>$b_von</td><td>$b_bis</td><td>$tage</td><td></td><td></td><td></td></tr>";
                }
                $z++;
            }
        }

        echo "</table>";

        $f->hidden_feld("v_datum", "$this->bk_verrechnungs_datum_d");
        $f->hidden_feld("option", "energie_send");
        $f->hidden_feld("jahr", "$jahr");
        $f->send_button("BtN_EN", "Werte in die\nMietdefinition speichern");
        $f->ende_formular();
    }

    function profil_aendern_db($profil_id, $bez, $jahr, $typ, $typ_id, $b_datum, $v_datum)
    {
        if ($this->profil_deaktivieren($profil_id)) {
            $b_datum_sql = date_german2mysql($b_datum);
            $v_datum_sql = date_german2mysql($v_datum);
            DB::insert("INSERT INTO BK_PROFILE VALUES(NULL, '$profil_id', '$bez', '$typ', '$typ_id', '$jahr', '$b_datum_sql', '$v_datum_sql', '1')");
        } else {
            fehlermeldung_ausgeben("Änderung fehlgeschlagen!");
        }
    }

    /* Alle vorhandenen Buchungen */

    function profil_deaktivieren($profil_id)
    {
        return DB::update("UPDATE BK_PROFILE SET AKTUELL='0' WHERE BK_ID = '$profil_id'");
    }

    /* Alle hinzugefügten Buchungen */

    function form_bk_profil_anpassen($profil_id)
    {
        session()->put('profil_id', $profil_id);
        $f = new formular ();
        $f->erstelle_formular("Profil anpassen", NULL);
        $f->hidden_feld("profil_id", "$profil_id");
        $this->bk_profil_infos($profil_id);
        // print_r($this);
        $f->text_feld("Profilbezeichnung $profil_id", "profil_bez", "$this->bk_bezeichnung", "50", 'profil_bez', '');
        $start_j = $this->bk_jahr - 2;
        $end_j = date("Y");
        $this->dropdown_jahr('Berechnungsjahr', 'jahr', 'jahr', $start_j, $end_j, '', $this->bk_jahr);
        $wirt = new wirt_e ();
        $wirt->dropdown_we('Wirtschaftseinheit wählen', 'w_id', 'w_id', '', $this->bk_kos_id);
        $f->datum_feld('Berechnungsdatum', 'berechnungsdatum', $this->bk_berechnungs_datum_d, 'berechnungsdatum');
        $f->datum_feld('Verrechnungsdatum', 'verrechnungsdatum', $this->bk_verrechnungs_datum_d, 'verrechnungsdatum');
        $f->hidden_feld("option", "profil_aendern");
        $f->send_button("submit_prof", "Änderungen speichern");
        $f->ende_formular();
    }

    function summe_konto_von_bis($von, $bis, $kostenkonto, $geldkonto_id)
    {
        $result = DB::select("SELECT SUM(BETRAG) AS SUMME  FROM `GELD_KONTO_BUCHUNGEN` WHERE `GELDKONTO_ID` = '$geldkonto_id' AND `KONTENRAHMEN_KONTO` = '$kostenkonto' AND DATUM BETWEEN '$von' AND '$bis' AND `AKTUELL` ='1'");
        return $result[0]['SUMME'];
    }

    function get_konten_nach_gruppe($gruppenbez)
    {
        $result = DB::select("SELECT KONTENRAHMEN_GRUPPEN.BEZEICHNUNG AS G_BEZEICHNUNG, KONTO, KONTENRAHMEN_KONTEN.BEZEICHNUNG, KONTO_ART FROM `KONTENRAHMEN_GRUPPEN` INNER JOIN (KONTENRAHMEN_KONTEN) ON ( KONTENRAHMEN_GRUPPEN_ID = GRUPPE )
WHERE KONTENRAHMEN_GRUPPEN.BEZEICHNUNG = '$gruppenbez' ORDER BY `KONTENRAHMEN_KONTEN`.`KONTO` ASC");
        return $result;
    }

    function zeige()
    {
        $konto_arr = $this->get_konten_nach_gruppe('Umlagefähige Kosten');
        echo '<pre>';
        $anzahl_konten = count($konto_arr);
        $von = '2008-01-01';
        $bis = '2009-12-31';
        $g_summe = 0;
        for ($a = 0; $a < $anzahl_konten; $a++) {
            $kostenkonto = $konto_arr [$a] ['KONTO'];
            $geldkonto_id = '5';
            $summe = $this->summe_konto_von_bis($von, $bis, $kostenkonto, $geldkonto_id);
            $g_bez = $konto_arr [$a] ['G_BEZEICHNUNG'];
            $konto_bez = $konto_arr [$a] ['BEZEICHNUNG'];
            if ($summe) {
                $g_summe = $g_summe + $summe;
                echo "<b>$kostenkonto</b> SUMME $summe $g_bez $konto_bez <br>";
            }
        }
        echo "GESAMT Umlagefähige Kosten $g_summe €";
    }

    function zusammenfassung($profil_id)
    {
        $this->bk_profil_infos($profil_id);
        $r = new rechnung ();
        echo "Berechnung für " . $r->kostentraeger_ermitteln($this->bk_kos_typ, $this->bk_kos_id) . " $this->bk_jahr<hr>";
        $this->bk_nk_profil_berechnung($profil_id);
    }

    function bk_nk_profil_berechnung($profil_id)
    {
        /* Profil Information holen, z.B. um Einheiten Array zu Bilden, d.h. f�r wenn ist die BK & NK */
        $this->bk_profil_infos($profil_id);
        $jahr = $this->bk_jahr;

        /* Alle ausgewählten BK Kontensummen mit Key und Kostenträger wählen */
        $summen_arr = $this->get_buchungssummen_konto_arr($profil_id);
        $anzahl_summen = count($summen_arr);

        $k = new kontenrahmen ();
        $this->kontenrahmen_id = $k->get_kontenrahmen($this->bk_kos_typ, $this->bk_kos_id);

        $diff = 0.00;
        // Anfangsdifferenz = 0;
        $hndl_diff = 0.00;
        // Anfangsdifferenz = 0;
        $check_bt = 0.00;
        $check_bt_hndl = 0.00;
        /* Schleife $a, äußere Schleife für jede Summe */
        for ($a = 0; $a < $anzahl_summen; $a++) {
            /* Berechnungsschlüssel infos holen */
            $key_id = $summen_arr [$a] ['KEY_ID'];
            $this->get_genkey_infos($key_id);

            $summe_konto = $summen_arr [$a] ['G_SUMME'];
            $bk_k_id = $summen_arr [$a] ['BK_K_ID'];

            $this->get_konto_infos_byid($bk_k_id, $profil_id);
            $bk_res ['kontrolle'] [$a] [$bk_k_id] ['KOSTENART'] = $this->konto_bez;
            // neu mit eigenen bezeichnungen der konten

            $kos_typ = $summen_arr [$a] ['KOS_TYP'];
            $kos_id = $summen_arr [$a] ['KOS_ID'];

            $anteil = $summen_arr [$a] ['ANTEIL'];
            $anteil_a = nummer_punkt2komma($anteil);
            $anteil_betrag = $summen_arr [$a] ['A_SUMME'];

            $hndl_anteil_betrag = $summen_arr [$a] ['HNDL_BETRAG'];
            $bk_res ['kontrolle'] [$a] [$bk_k_id] ['SUMME'] = $anteil_betrag;
            $bk_res ['kontrolle'] [$a] [$bk_k_id] ['HNDL'] = $hndl_anteil_betrag;
            $bk_res ['kontrolle'] [$a] [$bk_k_id] ['KOS_TYP'] = $kos_typ;
            $bk_res ['kontrolle'] [$a] [$bk_k_id] ['KOS_ID'] = $kos_id;

            if ($kos_typ != 'Wirtschaftseinheit') {
                $r = new rechnung ();
                $g_kos_bez = $r->kostentraeger_ermitteln($kos_typ, $kos_id);
                $bk_res ['kontrolle'] [$a] [$bk_k_id] ['G_KOS_BEZ'] = $g_kos_bez;
            }

            if ($kos_typ == 'Wirtschaftseinheit') {
                $wirt = new wirt_e ();
                $wirt->get_wirt_e_infos($kos_id);
                $gesamt_qm_alle = $wirt->g_qm;
                $gesamt_qm_gewerbe = $wirt->g_qm_gewerbe;
                $gesamt_qm = $gesamt_qm_alle - $gesamt_qm_gewerbe;
                $einheiten_arr = $wirt->get_einheiten_from_wirte($kos_id);
                $bk_res ['kontrolle'] [$a] [$bk_k_id] ['G_KOS_BEZ'] = $wirt->w_name;
                $anzahl_ge = $wirt->anzahl_ge;
                $anzahl_wo = $wirt->anzahl_wo;
            }

            if ($kos_typ == 'Objekt') {
                $o = new objekt ();
                $gesamt_qm_alle = $o->get_qm_gesamt($kos_id);
                $gesamt_qm_gewerbe = $o->get_qm_gesamt_gewerbe($kos_id);
                $gesamt_qm = $gesamt_qm_alle - $gesamt_qm_gewerbe;
                $einheiten_arr = $o->einheiten_objekt_arr($kos_id);
                $anzahl_wo = count($einheiten_arr);
            }

            /* BK & Nk für alle Einheiten in einem Haus */
            if ($kos_typ == 'Haus') {
                $h = new haus ();
                $gesamt_qm_alle = $h->get_qm_gesamt($kos_id);
                $gesamt_qm_gewerbe = $h->get_qm_gesamt_gewerbe($kos_id);
                $gesamt_qm = $gesamt_qm_alle - $gesamt_qm_gewerbe;
                $einheiten_arr = $h->liste_aller_einheiten_im_haus($kos_id);
                $anzahl_wo = count($einheiten_arr);
            }

            /* BK & Nk für eine Einheit */
            if ($kos_typ == 'Einheit') {
                $e = new einheit ();
                $e->get_einheit_info($kos_id);
                $gesamt_qm_alle = $e->einheit_qm;
                $gesamt_qm_gewerbe = $e->einheit_qm_gewerbe;
                $gesamt_qm = $gesamt_qm_alle - $gesamt_qm_gewerbe;
                $einheiten_arr = $e->get_einheit_as_array($kos_id);
                $anzahl_wo = count($einheiten_arr);
            }

            /* BK & Nk für eine Einheit */
            if ($kos_typ == 'Mietvertrag') {
                $mv = new mietvertraege ();
                $mv->get_mietvertrag_infos_aktuell($kos_id);
                $e = new einheit ();
                $e->get_einheit_info($mv->einheit_id);
                $gesamt_qm_alle = $e->einheit_qm;
                $gesamt_qm_gewerbe = $e->einheit_qm_gewerbe;
                $gesamt_qm = $gesamt_qm_alle - $gesamt_qm_gewerbe;
                $einheiten_arr = $e->get_einheit_as_array($mv->einheit_id);
                $anzahl_wo = count($einheiten_arr);
            }

            $bk_res ['kontrolle'] [$a] [$bk_k_id] ['KOSTEN_GESAMT'] = nummer_punkt2komma($anteil_betrag);
            $bk_res ['kontrolle'] [$a] [$bk_k_id] ['KOSTEN_GEWERBE'] = nummer_punkt2komma(($anteil_betrag / $gesamt_qm_alle) * $gesamt_qm_gewerbe);
            $bk_res ['kontrolle'] [$a] [$bk_k_id] ['KOSTEN_WOHNRAUM'] = nummer_punkt2komma($anteil_betrag - (($anteil_betrag / $gesamt_qm_alle) * $gesamt_qm_gewerbe));

            $anzahl_einheiten = count($einheiten_arr);
            /* Schleife $b, zweite Schleife für jede Einheit */
            for ($b = 0; $b < $anzahl_einheiten; $b++) {
                $einheit_id = $einheiten_arr [$b] ['EINHEIT_ID'];
                $einheit_qm = $einheiten_arr [$b] ['EINHEIT_QM'];
                $einheit_typ = $einheiten_arr [$b] ['TYP'];
                // Gewerbe / Wohnraum
                if ($einheit_typ == 'Gewerbe') {
                    $this->key_daten [$key_id] ['g_einheit_qm'] = $gesamt_qm_gewerbe;
                    $anteil_betrag_teilen = round(($anteil_betrag / $gesamt_qm_alle) * $gesamt_qm_gewerbe, 2);
                    $hndl_betrag_teilen = round(($hndl_anteil_betrag / $gesamt_qm_alle) * $gesamt_qm_gewerbe, 2);
                    $this->key_daten [$key_id] ['einheit_qm'] = $einheit_qm;
                    $this->key_daten [$key_id] ['g_anzahl_einheiten'] = $anzahl_ge;
                    $this->key_daten [$key_id] ['anzahl_einheiten'] = 1;
                } else {
                    $this->key_daten [$key_id] ['g_einheit_qm'] = $gesamt_qm;
                    $anteil_betrag_teilen = ($anteil_betrag / $gesamt_qm_alle) * $gesamt_qm;
                    $hndl_betrag_teilen = ($hndl_anteil_betrag / $gesamt_qm_alle) * $gesamt_qm;
                    $this->key_daten [$key_id] ['g_anzahl_einheiten'] = $anzahl_wo;
                    $this->key_daten [$key_id] ['anzahl_einheiten'] = 1;
                    $this->key_daten [$key_id] ['einheit_qm'] = $einheit_qm;
                }

                $einheit_name = $einheiten_arr [$b] ['EINHEIT_KURZNAME'];
                /* wenn kosten nicht nur einem MV zugeordnet, dann alle mvs und leerstände */
                if ($kos_typ != 'Mietvertrag') {
                    $leerstand_und_mvs = $this->mvs_und_leer_jahr($einheit_id, $jahr);
                } else {
                    $leerstand_und_mvs = $this->mvs_und_leer_jahr_1mv($kos_id, $jahr);
                }
                $anzahl_einheiten_mvs = count($leerstand_und_mvs);
                /* Schleife $c, dritte Schleife für jeden Mietvertrag oder Leerstand */
                for ($c = 0; $c < $anzahl_einheiten_mvs; $c++) {
                    $kos_typ_e = $leerstand_und_mvs [$c] ['KOS_TYP'];
                    $kos_id_e = $leerstand_und_mvs [$c] ['KOS_ID'];
                    $von = $leerstand_und_mvs [$c] ['BERECHNUNG_VON'];
                    $bis = $leerstand_und_mvs [$c] ['BERECHNUNG_BIS'];
                    $zeitraum = date_mysql2german($leerstand_und_mvs [$c] ['BERECHNUNG_VON']) . ' - ' . date_mysql2german($leerstand_und_mvs [$c] ['BERECHNUNG_BIS']);
                    if ($kos_typ != 'Mietvertrag') {
                        if ($kos_typ_e == 'Leerstand') {
                            $empfaenger = 'Leerstand';
                        } else {
                            $mv = new mietvertraege ();
                            $mv->get_mietvertrag_infos_aktuell($kos_id_e);
                            $empfaenger = $mv->personen_name_string;
                        }
                    } else {
                        if ($kos_typ_e == 'Leerstand') {
                            $empfaenger = "Eigentümer - Kosten betreffen nur den fehlenden Zeitraum, bzw. Jahr - Zeitraum vermietet, Beteiligung Mieter und Vermieter, keine Vor- und Nachmieterbeteiligung";
                        } else {
                            $mv = new mietvertraege ();
                            $mv->get_mietvertrag_infos_aktuell($kos_id_e);
                            $empfaenger = $mv->personen_name_string;
                        }
                    }

                    /* KOSTENKONTO */
                    // $beteiligung_genau =$this->beteiligung_berechnen($anteil_betrag, $von, $bis, $gesamt_qm, $einheit_qm)+$diff; //genau + $diff
                    $g_b = $this->key_daten [$key_id] [$this->g_key_g_var];
                    $e_b = $this->key_daten [$key_id] [$this->g_key_e_var];
                    // echo "REST DAVOR $diff<br>";
                    // $beteiligung_genau =$this->beteiligung_berechnen($anteil_betrag, $von, $bis, $g_b, $e_b,$einheit_name)+$diff; //genau + $diff

                    $beteiligung_genau100 = $this->beteiligung_berechnen($anteil_betrag_teilen, $von, $bis, $g_b, $e_b, $einheit_name);
                    // genau + $diff
                    // echo "1. $beteiligung_genau100<br>";
                    $beteiligung_genau = $beteiligung_genau100 + $diff;
                    // genau + $diff
                    $beteiligung = round($beteiligung_genau, 2);
                    // runden
                    /* Eigene Diff */
                    $eig_diff = $beteiligung_genau - $beteiligung;
                    // eigene diff, die weiter gegeben wird
                    $beteiligung_a = nummer_punkt2komma($beteiligung);
                    $check_bt = $check_bt + $beteiligung;
                    // echo "1.<b>$empfaenger</b>$anteil_betrag_teilen | $beteiligung_genau100 + $alt_diff = $beteiligung_genau gerundet = <b>$beteiligung</b> ($beteiligung_a) | EIG. REST: $eig_diff | <b>$check_bt</b><br>";
                    $diff = $eig_diff;

                    /* HNDL */

                    $beteiligung_hndl_genau100 = $this->beteiligung_berechnen($hndl_betrag_teilen, $von, $bis, $g_b, $e_b, $einheit_name);
                    // genau + $diff
                    // echo "2. $beteiligung_hndl_genau100<br>";
                    $beteiligung_hndl_genau = $beteiligung_hndl_genau100 + $hndl_diff;
                    // genau + $diff
                    $beteiligung_hndl = round($beteiligung_hndl_genau, 2);
                    // runden
                    /* Eigene Diff */
                    $hndl_eig_diff = $beteiligung_hndl_genau - $beteiligung_hndl;
                    // eigene diff, die weiter gegeben wird
                    $beteiligung_hndl_a = nummer_punkt2komma($beteiligung_hndl);
                    $check_bt_hndl = $check_bt_hndl + $beteiligung_hndl;
                    // echo "2.<b>$empfaenger</b>$hndl_betrag_teilen | $beteiligung_hndl_genau100 + $hndl_alt_diff= $beteiligung_hndl_genau gerundet = <b>$beteiligung_hndl</b> ($beteiligung_hndl_a) | EIG. REST: $hndl_eig_diff | <b>$check_bt_hndl</b><br>";
                    $hndl_diff = $hndl_eig_diff;

                    $bk_res [$einheit_name . ' ' . $zeitraum] ['EMPF'] = "$empfaenger";
                    // $bk_res[$einheit_name.' '.$zeitraum]['G_KOS_BEZ']=$g_kos_bez;
                    $bk_res [$einheit_name . ' ' . $zeitraum] ['KOS_TYP'] = "$kos_typ_e";
                    $bk_res [$einheit_name . ' ' . $zeitraum] ['KOS_ID'] = "$kos_id_e";
                    $bk_res [$einheit_name . ' ' . $zeitraum] ['QM_G_OBJEKT'] = "$gesamt_qm_alle";
                    $bk_res [$einheit_name . ' ' . $zeitraum] ['QM_G'] = "$gesamt_qm";
                    $bk_res [$einheit_name . ' ' . $zeitraum] ['QM_G_GEWERBE'] = "$gesamt_qm_gewerbe";
                    $bk_res [$einheit_name . ' ' . $zeitraum] ['EINHEIT_QM'] = "$einheit_qm";
                    $bk_res [$einheit_name . ' ' . $zeitraum] ['ZEITRAUM'] = "$zeitraum";
                    $bk_res [$einheit_name . ' ' . $zeitraum] ['EINHEIT_NAME'] = "$einheit_name";
                    $bk_res [$einheit_name . ' ' . $zeitraum] ['EINHEIT_TYP'] = "$einheit_typ";
                    $anteil_betrag_teilen_a = nummer_punkt2komma($anteil_betrag_teilen);
                    $anteil_betrag_teilen_hndl_a = nummer_punkt2komma($hndl_betrag_teilen);

                    $bk_res [$einheit_name . ' ' . $zeitraum] [] = array(
                        'KOSTENART' => "$this->konto_bez",
                        'G_KOS_BEZ' => "$anteil",
                        'G_HNDL' => "$anteil_betrag_teilen_hndl_a",
                        'BK_K_ID' => "$bk_k_id",
                        'G_BETRAG' => "$anteil_betrag_teilen_a",
                        'ANTEIL' => "$anteil_a",
                        'UMLAGE' => "$anteil_betrag_teilen_a",
                        'G_KEY' => "$g_b",
                        'QM' => "$e_b",
                        'ME' => "$this->g_key_me",
                        'BET_G' => "$beteiligung_a",
                        'BET_HNDL' => "$beteiligung_hndl_a",
                        'GENKEY_ID' => "$genkey_id"
                    );

                    $bk_res ['kontrolle'] [$a] [$bk_k_id] ['KOSTENART'] = $this->konto_bez;
                    $bk_res ['kontrolle'] [$a] [$bk_k_id] ['SUMME_K'] += $beteiligung;
                    $bk_res ['kontrolle'] [$a] [$bk_k_id] ['HNDL_K'] += $beteiligung_hndl;
                } // end for $c
            } // end for $b

            $diff = 0.00;
            $check_bt = 0.00;

            $hndl_diff = 0.00;
            $check_bt_hndl = 0.00;
        } // end for $a

        return $bk_res;
    }

    function get_buchungssummen_konto_arr($profil_id)
    {
        $db_abfrage = " SELECT KONTO, t1.BK_K_ID,SUM(BETRAG) AS G_SUMME, SUM(A_SUMME) AS A_SUMME, KEY_ID,  KOS_TYP,  KOS_ID, ANTEIL, SUM(HNDL_BETRAG) AS HNDL_BETRAG FROM (SELECT BK_K_ID, BETRAG,  (BETRAG/100)*ANTEIL AS A_SUMME,  HNDL_BETRAG, KEY_ID, BK_BERECHNUNG_BUCHUNGEN.KOSTENTRAEGER_TYP AS KOS_TYP, BK_BERECHNUNG_BUCHUNGEN.KOSTENTRAEGER_ID AS KOS_ID, ANTEIL
FROM `BK_BERECHNUNG_BUCHUNGEN`
JOIN GELD_KONTO_BUCHUNGEN ON ( BK_BERECHNUNG_BUCHUNGEN.BUCHUNG_ID = GELD_KONTO_BUCHUNGEN.GELD_KONTO_BUCHUNGEN_ID )
WHERE `BK_PROFIL_ID` = '$profil_id' && BK_BERECHNUNG_BUCHUNGEN.AKTUELL = '1' && GELD_KONTO_BUCHUNGEN.AKTUELL = '1'
ORDER BY GELD_KONTO_BUCHUNGEN.KONTENRAHMEN_KONTO) as t1, BK_KONTEN WHERE BK_KONTEN.BK_K_ID=t1.BK_K_ID GROUP BY BK_K_ID, KEY_ID, KOS_TYP, KOS_ID ORDER BY KONTO";
        $result = DB::select($db_abfrage);
        return $result;
    }

    function get_konto_infos_byid($bk_k_id, $profil_id)
    {
        unset ($this->konto_bez);
        $result = DB::select("SELECT *  FROM `BK_KONTEN` WHERE AKTUELL='1' && BK_PROFIL_ID='$profil_id' && BK_K_ID='$bk_k_id' ORDER BY BK_K_DAT DESC LIMIT 0,1");
        $row = $result[0];
        $this->konto = $row ['KONTO'];
        $this->konto_bez = $row ['KONTO_BEZ'];
        $this->konto_gkey = $row ['GENKEY_ID'];
        $this->konto_hndl = $row ['HNDL'];
    }

    function mvs_und_leer_jahr_1mv($mv1_id, $jahr)
    {
        $abfrage = "SELECT 'MIETVERTRAG' AS KOS_TYP, MIETVERTRAG_ID AS KOS_ID ,`MIETVERTRAG_VON`,`MIETVERTRAG_BIS`,`EINHEIT_ID`,
IF(DATE_FORMAT(MIETVERTRAG_VON, '%Y') < '$jahr', '$jahr-01-01', MIETVERTRAG_VON) AS BERECHNUNG_VON,
IF(DATE_FORMAT(MIETVERTRAG_BIS, '%Y') = '$jahr', MIETVERTRAG_BIS, '$jahr-12-31') AS BERECHNUNG_BIS,
DATEDIFF(IF(DATE_FORMAT(MIETVERTRAG_BIS, '%Y') = '$jahr', MIETVERTRAG_BIS, '$jahr-12-31'), IF(DATE_FORMAT(MIETVERTRAG_VON, '%Y') < '$jahr', '$jahr-01-01', MIETVERTRAG_VON))+1 AS TAGE FROM `MIETVERTRAG` WHERE `MIETVERTRAG_AKTUELL`='1' 
&& DATE_FORMAT(MIETVERTRAG_VON,'%Y') <= '$jahr' && (DATE_FORMAT(MIETVERTRAG_BIS,'%Y') >='$jahr' OR DATE_FORMAT(MIETVERTRAG_BIS,'%Y') ='0000') && MIETVERTRAG_ID='$mv1_id' ORDER BY MIETVERTRAG_VON ASC";

        $result = DB::select($abfrage);

        /* Wenn überhaupt vermietet, sonst Leerstand ganzes Jahr, siehe unten nach ELSE */
        if (!empty($result)) {
            $anzahl_zeilen = count($result);
            $tage = 0;
            for ($a = 0; $a < $anzahl_zeilen; $a++) {
                $tage = $tage + $result [$a] ['TAGE'];
            }

            $tage_im_jahr = $this->tage_im_jahr($jahr);
            /* Voll vermietet */
            if ($tage == $tage_im_jahr) {
                return $result;
            }

            /* Nicht ganzes Jahr vermietet */
            if ($tage < $tage_im_jahr) {

                // echo "TEILWEISE VERMIETET = $tage <> $tage_im_jahr<br>";

                $summe_tage = 0;
                for ($a = 0; $a < $anzahl_zeilen; $a++) {

                    $berechnung_von = $result [$a] ['BERECHNUNG_VON'];
                    $berechnung_bis = $result [$a] ['BERECHNUNG_BIS'];

                    /* Wenn etwas am Anfang des Jahres fehlt */
                    if ($a == 0) {
                        $diff_zwischen_1_1 = $this->diff_in_tagen("$jahr-01-01", $berechnung_von);
                        if ($diff_zwischen_1_1 > 0) {
                            // echo "AM ANFANG FEHLEN $diff_zwischen_1_1 ($jahr-01-01 bis $berechnung_von)<br>";
                            $berechnung_von_a = $this->datum_minus_tage($berechnung_von, 1);
                            $my_array_neu [] = array(
                                'KOS_TYP' => 'Leerstand',
                                'KOS_ID' => 'Leerstand',
                                'BERECHNUNG_VON' => "$jahr-01-01",
                                'BERECHNUNG_BIS' => "$berechnung_von_a",
                                'TAGE' => $diff_zwischen_1_1
                            );
                            $summe_tage += $diff_zwischen_1_1;
                        } else {
                            // echo "AM ANFANG FEHLEN KEINE TAGE<br>";
                        }
                    }

                    /* Fehlzeiten / leerstände zwischen den verträgen */
                    /* In allen Zeilen ausser letzte */

                    if ($a < $anzahl_zeilen - 1) {
                        $berechnung_von_next = $result [$a + 1]['BERECHNUNG_VON'];
                        $diff_zw_mvs = $this->diff_in_tagen($berechnung_bis, $berechnung_von_next) - 1;
                        if ($diff_zw_mvs > 1) {
                            // echo "ES FEHLEN $berechnung_bis $berechnung_von_next $diff_zw_mvs<br>";
                            $berechnung_bis = $this->datum_plus_tage($berechnung_bis, 1);
                            $berechnung_von_next = $this->datum_minus_tage($berechnung_von_next, 1);
                            $my_array_neu [] = array(
                                'KOS_TYP' => 'Leerstand',
                                'KOS_ID' => 'Leerstand',
                                'BERECHNUNG_VON' => $berechnung_bis,
                                'BERECHNUNG_BIS' => $berechnung_von_next,
                                'TAGE' => $diff_zw_mvs
                            );
                            $summe_tage += $diff_zw_mvs;
                        }
                    }

                    $berechnung_von = $my_array [$a] ['BERECHNUNG_VON'];
                    $berechnung_bis = $my_array [$a] ['BERECHNUNG_BIS'];
                    $tage_vermietet = $my_array [$a] ['TAGE'];
                    $mv_id = $my_array [$a] ['KOS_ID'];
                    $my_array_neu [] = array(
                        'KOS_TYP' => 'MIETVERTRAG',
                        'KOS_ID' => $mv_id,
                        'BERECHNUNG_VON' => $berechnung_von,
                        'BERECHNUNG_BIS' => $berechnung_bis,
                        'TAGE' => $tage_vermietet
                    );
                    $summe_tage += $tage_vermietet;

                    /* Wenn etwas am Ende des Jahres fehlt */
                    if ($a == $anzahl_zeilen - 1) {
                        $diff_zwischen_12_31 = $this->diff_in_tagen("$berechnung_bis", "$jahr-12-31");
                        if ($diff_zwischen_12_31 > 0) {

                            $summe_tage += $diff_zwischen_12_31;
                            $berechnung_bis_a = $this->datum_plus_tage($berechnung_bis, 1);

                            // echo "AM ENDE FEHLEN $diff_zwischen_12_31 ($berechnung_bis_a bis $jahr-12-31)<br>";
                            $my_array_neu [] = array(
                                'KOS_TYP' => 'Leerstand',
                                'KOS_ID' => 'Leerstand',
                                'BERECHNUNG_VON' => $berechnung_bis_a,
                                'BERECHNUNG_BIS' => "$jahr-12-31",
                                'TAGE' => $diff_zwischen_12_31
                            );
                        } else {
                            // echo "AM ENDE FEHLEN KEINE TAGE<br>";
                        }
                    }
                } // end for
                $arr_sortiert = array_sortByIndex($my_array_neu, 'BERECHNUNG_VON');
                unset ($my_array_neu);
                return $arr_sortiert;
            } else {
                $error_msg  = "ERROR BERLUSSIMO L501D (TAGE>TAGE IM JAHR) E:$einheit_id $jahr $tage<$tage_im_jahr\n";
                $error_msg .= "Wahrscheinlich 2 Mietverträge zur gleichen Zeit, bitte Anfangsdatum der mietvertraege der Einheit $einheit_id prüfen";
                throw new \App\Exceptions\MessageException(
                    new \App\Messages\ErrorMessage($error_msg)
                );
            }
        }  // end if $numrows
        else {
            $tage_im_jahr = $this->tage_im_jahr($jahr);
            $my_array_neu [] = array(
                'KOS_TYP' => 'Leerstand',
                'KOS_ID' => 'Leerstand',
                'BERECHNUNG_VON' => "$jahr-01-01",
                'BERECHNUNG_BIS' => "$jahr-12-31",
                'TAGE' => $tage_im_jahr
            );
            return $my_array_neu;
        }
    }

    function beteiligung_berechnen($betrag, $mietvertrag_von, $mietvertrag_bis, $g_einheiten, $my_einheiten, $einheit_name)
    {
        if ($g_einheiten == null or $my_einheiten == null) {
            return '0.00';
        } else {

            $datum_arr = explode("-", $mietvertrag_von);
            $jahr = $datum_arr [0];
            $tage_im_jahr = $this->tage_im_jahr($jahr);
            $diff_in_tagen = $this->diff_in_tagen($mietvertrag_von, $mietvertrag_bis);
            $berechnungstage = $diff_in_tagen + 1;
            $g_kosten_pro_tag = $betrag / $tage_im_jahr;

            $g_kosten_pro_einheit_tag = $g_kosten_pro_tag / $g_einheiten;

            $beteiligung = $g_kosten_pro_einheit_tag * $my_einheiten * $berechnungstage;
            return $beteiligung;
        }
    }

    /* Wenn Kosten nur einem Mietvertrag zugeordnet */

    function mvs_und_leer_jahr_zeitraum($einheit_id, $von, $jahr)
    {
        $abfrage = "SELECT 'MIETVERTRAG' AS KOS_TYP, MIETVERTRAG_ID AS KOS_ID ,`MIETVERTRAG_VON`,`MIETVERTRAG_BIS`,`EINHEIT_ID`,
IF(DATE_FORMAT(MIETVERTRAG_VON, '%Y') < '$jahr', '$von', MIETVERTRAG_VON) AS BERECHNUNG_VON,
IF(DATE_FORMAT(MIETVERTRAG_BIS, '%Y') = '$jahr', MIETVERTRAG_BIS, '$jahr-12-31') AS BERECHNUNG_BIS,
DATEDIFF(IF(DATE_FORMAT(MIETVERTRAG_BIS, '%Y') = '$jahr', MIETVERTRAG_BIS, '$jahr-12-31'), IF(DATE_FORMAT(MIETVERTRAG_VON, '%Y') < '$jahr', '$von', MIETVERTRAG_VON))+1 AS TAGE FROM `MIETVERTRAG` WHERE `MIETVERTRAG_AKTUELL`='1' 
&& DATE_FORMAT(MIETVERTRAG_VON,'%Y') <= '$jahr' && (DATE_FORMAT(MIETVERTRAG_BIS,'%Y') >='$jahr' OR DATE_FORMAT(MIETVERTRAG_BIS,'%Y') ='0000') && EINHEIT_ID='$einheit_id' ORDER BY MIETVERTRAG_VON ASC";

        $my_array = DB::select($abfrage);
        /* Wenn überhaupt vermietet, sonst Leerstand ganzes Jahr, siehe unten nach ELSE */
        if (!empty($my_array)) {
            $anzahl_zeilen = count($my_array);
            $tage = 0;
            for ($a = 0; $a < $anzahl_zeilen; $a++) {
                $tage = $tage + $my_array [$a] ['TAGE'];
            }

            $tage_im_jahr = $this->tage_im_jahr($jahr);
            /* Voll vermietet */
            if ($tage == $tage_im_jahr) {
                /* Voll mermietet */
                // echo "GANZ VERMIETET = $tage == $tage_im_jahr<br>";
                return $my_array;
            }

            /* Nicht ganzes Jahr vermietet */
            if ($tage < $tage_im_jahr) {
                $summe_tage = 0;
                for ($a = 0; $a < $anzahl_zeilen; $a++) {

                    $berechnung_bis = $my_array [$a] ['BERECHNUNG_BIS'];

                    /* Fehlzeiten / leerstände zwischen den Verträgen */
                    /* In allen Zeilen ausser letzte */

                    if ($a < $anzahl_zeilen - 1) {
                        $berechnung_von_next = $my_array [$a + 1] ['BERECHNUNG_VON'];
                        $diff_zw_mvs = $this->diff_in_tagen($berechnung_bis, $berechnung_von_next) - 1;
                        if ($diff_zw_mvs > 1) {
                            // echo "ES FEHLEN $berechnung_bis $berechnung_von_next $diff_zw_mvs<br>";
                            $berechnung_bis = $this->datum_plus_tage($berechnung_bis, 1);
                            $berechnung_von_next = $this->datum_minus_tage($berechnung_von_next, 1);
                            $my_array_neu [] = array(
                                'KOS_TYP' => 'Leerstand',
                                'KOS_ID' => 'Leerstand',
                                'BERECHNUNG_VON' => $berechnung_bis,
                                'BERECHNUNG_BIS' => $berechnung_von_next,
                                'TAGE' => $diff_zw_mvs
                            );
                            $summe_tage += $diff_zw_mvs;
                        }
                    }

                    $berechnung_von = $my_array [$a] ['BERECHNUNG_VON'];
                    $berechnung_bis = $my_array [$a] ['BERECHNUNG_BIS'];
                    $tage_vermietet = $my_array [$a] ['TAGE'];
                    $mv_id = $my_array [$a] ['KOS_ID'];
                    $my_array_neu [] = array(
                        'KOS_TYP' => 'MIETVERTRAG',
                        'KOS_ID' => $mv_id,
                        'BERECHNUNG_VON' => $berechnung_von,
                        'BERECHNUNG_BIS' => $berechnung_bis,
                        'TAGE' => $tage_vermietet
                    );
                    $summe_tage += $tage_vermietet;

                    /* Wenn etwas am Ende des Jahres fehlt */
                    if ($a == $anzahl_zeilen - 1) {
                        $diff_zwischen_12_31 = $this->diff_in_tagen("$berechnung_bis", "$jahr-12-31");
                        if ($diff_zwischen_12_31 > 0) {

                            $summe_tage += $diff_zwischen_12_31;
                            $berechnung_bis_a = $this->datum_plus_tage($berechnung_bis, 1);

                            // echo "AM ENDE FEHLEN $diff_zwischen_12_31 ($berechnung_bis_a bis $jahr-12-31)<br>";
                            $my_array_neu [] = array(
                                'KOS_TYP' => 'Leerstand',
                                'KOS_ID' => 'Leerstand',
                                'BERECHNUNG_VON' => $berechnung_bis_a,
                                'BERECHNUNG_BIS' => "$jahr-12-31",
                                'TAGE' => $diff_zwischen_12_31
                            );
                        } else {
                            // echo "AM ENDE FEHLEN KEINE TAGE<br>";
                        }
                    }
                } // end for
                // echo "<b>SUMME TAGE $summe_tage</b><br>";
                $arr_sortiert = array_sortByIndex($my_array_neu, 'BERECHNUNG_VON');
                unset ($my_array_neu);
                return $arr_sortiert;
            } else {
                $error_msg = "ERROR BERLUSSIMO L501D (TAGE>TAGE IM JAHR) E:$einheit_id $jahr $tage<$tage_im_jahr\n";
                $error_msg .= "Wahrscheinlich 2 Mietverträge zur gleichen Zeit, bitte Anfangsdatum der Mietvertraege der Einheit $einheit_id prüfen";
                throw new \App\Exceptions\MessageException(new \App\Messages\ErrorMessage($error_msg));
            }
        }  // end if $numrows
        else {
            // echo "Leerstand ganzes jahr";
            $tage_im_jahr = $this->tage_im_jahr($jahr);
            $my_array_neu [] = array(
                'KOS_TYP' => 'Leerstand',
                'KOS_ID' => 'Leerstand',
                'BERECHNUNG_VON' => "$jahr-01-01",
                'BERECHNUNG_BIS' => "$jahr-12-31",
                'TAGE' => $tage_im_jahr
            );
            return $my_array_neu;
        }
    }

    function bk_berechnung_arr($profil_id, $bk_k_id)
    {
        $db_abfrage = "SELECT * FROM `BK_BERECHNUNG_BUCHUNGEN` WHERE `BK_K_ID` ='$bk_k_id' AND `BK_PROFIL_ID` ='$profil_id' AND `AKTUELL` ='1' ";
        $result = DB::select($db_abfrage);
        return $result;
    }

    /* objekte, haus usw, vor wirtschaftseinheiten implementation */

    function pdf_ausgabe_alle($profil_id)
    {
        ob_clean();
        // ausgabepuffer leeren
        $pdf = new Cezpdf ('a4', 'portrait');
        $bpdf = new b_pdf ();
        $bpdf->b_header($pdf, 'Partner', session()->get('partner_id'), 'portrait', 'Helvetica.afm', 6);
        $this->footer_zahlungshinweis = $bpdf->zahlungshinweis;

        $pdf->ezStopPageNumbers();
        // seitennummerirung beenden
        $p = new partners ();
        $p->get_partner_info(session()->get('partner_id'));
        /* Alle Berechnungen als Array */
        $bk_res_arr = $this->bk_nk_profil_berechnung($profil_id);

        /* Kontrolldatenblatt */
        $kontroll_arr = $bk_res_arr ['kontrolle'];
        $anz_konten = count($kontroll_arr);
        for ($a = 0; $a < $anz_konten; $a++) {
            $konten = array_keys($kontroll_arr [$a]);
            $bk_k_id = $konten [0];
            $kostenart = $kontroll_arr [$a] [$bk_k_id] ['KOSTENART'];
            $g_kos_bez = $kontroll_arr [$a] [$bk_k_id] ['G_KOS_BEZ'];
            $kos_typ = $kontroll_arr [$a] [$bk_k_id] ['KOS_TYP'];

            $kostenart_summe = nummer_punkt2komma($kontroll_arr [$a] [$bk_k_id] ['SUMME']);
            $kostenart_kontrolliert = nummer_punkt2komma($kontroll_arr [$a] [$bk_k_id] ['SUMME_K']);
            if (ltrim(rtrim($kostenart_summe)) == ltrim(rtrim($kostenart_kontrolliert))) {
                $status_summe = 'OK';
            } else {
                $status_summe = '<b>FALSCH</b>';
            }
            $kostenart_hndl = nummer_punkt2komma($kontroll_arr [$a] [$bk_k_id] ['HNDL']);
            $kostenart_hndl_kontrolliert = nummer_punkt2komma($kontroll_arr [$a] [$bk_k_id] ['HNDL_K']);
            if (ltrim(rtrim($kostenart_hndl)) != ltrim(rtrim($kostenart_hndl_kontrolliert))) {
                $status_hndl = '<b>FALSCH</b>';
            } else {
                $status_hndl = 'OK';
            }

            if (nummer_komma2punkt($kostenart_hndl) < nummer_komma2punkt($kostenart_summe)) {
                $status_hndl = '<b>FALSCH HNDL > SUMME</b>';
                $status_summe = '<b>FALSCH HNDL > SUMME</b>';
            }

            if ($kos_typ != 'Einheit') {
                $gesamt_kosten = $kontroll_arr [$a] [$bk_k_id] ['KOSTEN_GESAMT'];
                $gesamt_gewerbe = $kontroll_arr [$a] [$bk_k_id] ['KOSTEN_GEWERBE'];
                $gesamt_wohnraum = $kontroll_arr [$a] [$bk_k_id] ['KOSTEN_WOHNRAUM'];
                $kontroll_tab_druck [$a] ['KOSTENART'] = $kostenart;
                $kontroll_tab_druck [$a] ['KOSTEN_GESAMT'] = $gesamt_kosten . ' €';
                $kontroll_tab_druck [$a] ['KOSTEN_GEWERBE'] = $gesamt_gewerbe . ' €';
                $kontroll_tab_druck [$a] ['KOSTEN_WOHNRAUM'] = $gesamt_wohnraum . ' €';
            }

            $kontroll_tab [$a] ['KOSTENART'] = $kostenart;
            $kontroll_tab [$a] ['SUMME'] = $kostenart_summe;
            $kontroll_tab [$a] ['SUMME_K'] = $kostenart_kontrolliert;
            $kontroll_tab [$a] ['STATUS1'] = $status_summe;
            $kontroll_tab [$a] ['HNDL'] = $kostenart_hndl;
            $kontroll_tab [$a] ['HNDL_K'] = $kostenart_hndl_kontrolliert;
            $kontroll_tab [$a] ['STATUS2'] = $status_hndl;
            $kontroll_tab [$a] ['G_KOS_BEZ'] = $g_kos_bez;
        }

        if (session()->get('berechnung_ok') == 'OK') {
            $this->bk_abrechnung_speichern($this->profil_id, session()->get('partner_id'), $this->bk_jahr, $wirt_e, $wirt_name, $datum, $anz_einheiten, $qm_gesamt, $qm_wohnraum, $qm_gewerbe, $anz_konten, $anz_abrechnungen, $ersteller, $partner_id, $kontenrahmen_id);
        }

        /* Anfang BK-Abrechnungsseiten */
        $keys = array_keys($bk_res_arr);
        // unsortiert mit Kontrolle!!!!

        /* Sortierung der Seiten für PDF Ausgabe */
        // echo "'<pre>";

        unset ($keys [0]);
        // Kontolle rausnehmen
        // print_r($keys);
        natsort($keys);

        $ind = array_values($keys);
        // sortiert nat und ohne kontrolle
        unset ($keys);
        $keys = $ind;
        unset ($ind);

        $anzahl_abr = count($keys);
        $anzahl_abr_real = $anzahl_abr - 1;

        for ($a = 0; $a < $anzahl_abr; $a++) { // $a=1 weil [kontrolle] übersprungen werden soll
            /* Kopfdaten */
            $key_val = $keys [$a];
            $liste_abrechnungen [$a] ['SEITE'] = $a + 1;

            $abrechnung = $bk_res_arr [$key_val];
            $empfaenger_name = trim(rtrim($abrechnung ['EMPF']));
            $zraum = $abrechnung ['ZEITRAUM'];
            $e_name = ltrim(rtrim($abrechnung ['EINHEIT_NAME']));
            $liste_abrechnungen [$a] ['EINHEIT'] = $e_name;
            $liste_abrechnungen [$a] ['ZEITRAUM'] = $zraum;
            $liste_abrechnungen [$a] ['EMPF'] = $empfaenger_name;
            $liste_abrechnungen [$a] ['MV_ID'] = $abrechnung ['KOS_ID'];

            if ($abrechnung ['KOS_ID'] != 'Leerstand') {
                $mvv = new mietvertraege ();
                $mvv->get_mietvertrag_infos_aktuell($abrechnung ['KOS_ID']);

                $liste_abrechnungen [$a] ['LAGE'] = $mvv->einheit_lage;
                $liste_abrechnungen [$a] ['TYP'] = $mvv->einheit_typ;

                $liste_abrechnungen [$a] ['ZUSTELL_DATUM'] = '';

                if ($mvv->mietvertrag_aktuell == '0') {
                    $liste_abrechnungen [$a] ['ZUSTELLEN'] = 'VERSCHICKEN';
                    $liste_abrechnungen [$a] ['ZUSTELLER'] = 'POSTWEG';
                }
                /* Aktuelle Mieter */
                // if($mvv->mietvertrag_aktuell=='1'){

                /* Zustelladresse oder ins Haus?!? */
                if ($mvv->anz_zustellanschriften > 0 or $mvv->anz_verzugsanschriften > 0) {
                    $liste_abrechnungen [$a] ['ZUSTELLEN'] = 'VERSENDEN Z/V';
                    $liste_abrechnungen [$a] ['ZUSTELLER'] = 'POSTWEG';
                    $liste_abrechnungen [$a] ['ANSCHRIFT'] = $mvv->postanschrift [0] ['anschrift'];
                } else {
                    $liste_abrechnungen [$a] ['ZUSTELLEN'] = 'EINWERFEN';
                    $liste_abrechnungen [$a] ['ZUSTELLER'] = '';
                }
            } else {
                $liste_abrechnungen [$a] ['ZUSTELL_DATUM'] = '';
                $liste_abrechnungen [$a] ['ZUSTELLEN'] = 'AN ET';
                $liste_abrechnungen [$a] ['ZUSTELLER'] = '';
            }
        }

        $this->bk_profil_infos($profil_id);
        $pdf->ezText("Profilname: $this->bk_bezeichnung", 8);
        $pdf->ezText("Profilnr: $profil_id", 8);
        $pdf->ezText("Profil für $this->bk_kos_bez", 8);
        $pdf->ezText("Anzahl Abrechnungen: $anzahl_abr_real", 8);
        $pdf->ezSetDy(-20);
        $cols = array(
            'KOSTENART' => "Kostenart",
            'G_KOS_BEZ' => "Aufteilung",
            'SUMME' => "Summe",
            'SUMME_K' => 'Kontrolle',
            'STATUS1' => 'Prüfung',
            'HNDL' => 'HNDL Summe',
            'HNDL_K' => 'HNDL Kontrolle',
            'STATUS2' => 'Prüfung HNDL'
        );
        $pdf->ezTable($kontroll_tab, $cols, "Kontrolle der Berechnungen / Aufteilung der Gesamtkosten", array(
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

        $pdf->ezSetDy(-20);

        $anp_arr = $this->get_anpassung_infos2($profil_id);
        $anz = count($anp_arr);
        if ($anz) {

            for ($a = 0; $a < $anz; $a++) {
                $grund = $anp_arr [$a] ['GRUND'];
                $festbetrag = $anp_arr [$a] ['FEST_BETRAG'];
                $gkey_id = $anp_arr [$a] ['KEY_ID'];
                $this->get_genkey_infos($gkey_id);
                // echo "<tr><td>$grund</td><td> $festbetrag / $this->g_key_me</td><br>";
                $anp_tab [$a] ['GRUND'] = $grund;
                $anp_tab [$a] ['BETRAG'] = nummer_punkt2komma($festbetrag) . ' €';
                $anp_tab [$a] ['ME'] = $this->g_key_me . ' / Monat';
            }
            $cols = array(
                'GRUND' => "Kostenart",
                'BETRAG' => "Betrag",
                'ME' => "Anpassungsformel"
            );
            $pdf->ezTable($anp_tab, $cols, "Anpassungstabelle wegen voraussichtlich steigender Kosten", array(
                'showHeadings' => 1,
                'shaded' => 1,
                'titleFontSize' => 8,
                'fontSize' => 7,
                'xPos' => 55,
                'xOrientation' => 'right',
                'width' => 300,
                'cols' => array(
                    'GRUND' => array(
                        'justification' => 'left',
                        'width' => 150
                    ),
                    'BETRAG' => array(
                        'justification' => 'right',
                        'width' => 50
                    ),
                    'ME' => array(
                        'justification' => 'right',
                        'width' => 50
                    )
                )
            ));
            $pdf->ezSetDy(-20);
        } else {
            // echo "Keine Anpassung im Profil eingetragen";
        }

        $pdf->ezNewPage();
        $cols = array(
            'SEITE' => "Seite",
            'EINHEIT' => "Mieteinheit",
            'LAGE' => "Lage",
            'TYP' => "Typ",
            'ZEITRAUM' => "Abrechnungszeitraum",
            'EMPF' => "Empfänger",
            'ZUSTELLEN' => "ART",
            'ANSCHRIFT' => "ANSCHRIFT Z/V",
            'ZUSTELL_DATUM' => "Z-DATUM",
            'ZUSTELLER' => "ZUSTELLLER"
        );
        $pdf->ezTable($liste_abrechnungen, $cols, "Einwurfliste/Inhaltsverzeichnis der Betriebskostenabrechnung $this->bk_bezeichnung für $this->bk_kos_bez", array(
            'showHeadings' => 1,
            'shaded' => 1,
            'titleFontSize' => 7,
            'fontSize' => 7,
            'xPos' => 55,
            'xOrientation' => 'right',
            'width' => 500,
            'cols' => array(
                'SEITE' => array(
                    'justification' => 'left',
                    'width' => 27
                ),
                'EINHEIT' => array(
                    'justification' => 'left',
                    'width' => 50
                ),
                'ZEITRAUM' => array(
                    'justification' => 'left',
                    'width' => 90
                ),
                'EMPF' => array(
                    'justification' => 'left'
                )
            )
        ));
        // $pdf->ezNewPage();

        /* Ende Kontrolldatenblatt */

        /* Zusammenstellung des Profils */
        $this->pdf_uebersicht_profil($pdf, $profil_id);
        // mit pdf;

        /*
		 * $g_summen_arr = $this->get_buchungssummen_konto_arr_2($profil_id);
		 * $anz_zeilen = count($g_summen_arr);
		 * for($a=0;$a<$anz_zeilen;$a++){
		 * $kostenart = $g_summen_arr[$a][$bk_k_id][KOSTENART];
		 * $gesamt_kosten = $g_summen_arr[$a][$bk_k_id][KOSTEN_GESAMT];
		 * $gesamt_gewerbe = $g_summen_arr[$a][$bk_k_id][KOSTEN_GEWERBE];
		 * $gesamt_wohnraum = $g_summen_arr[$a][$bk_k_id][KOSTEN_WOHNRAUM];
		 * $kontroll_tab_druck1[$a][KOSTENART] = $kostenart;
		 * $kontroll_tab_druck1[$a][KOSTEN_GESAMT] = $gesamt_kosten.' €';
		 * $kontroll_tab_druck1[$a][KOSTEN_GEWERBE] = $gesamt_gewerbe.' €';
		 * $kontroll_tab_druck1[$a][KOSTEN_WOHNRAUM] = $gesamt_wohnraum.' €';
		 * }
		 *
		 */

        // echo "BK Abrechnungen: $anzahl_abr<br>";
        for ($a = 0; $a < $anzahl_abr; $a++) { // $a=1 weil [kontrolle] übersprungen werden soll
            /* Kopfdaten */
            $key_val = $keys [$a];
            // $pdf->ezText($bk_res_arr[$key_val],12);
            // $this->pdf_zeitraum($pdf, $bk_res_arr[$key_val]);

            $abrechnung = $bk_res_arr [$key_val];
            $this->pdf_einzel_tab($pdf, $abrechnung, $label, $kontroll_tab_druck);
        }

        $anzahl = count($pdf->ergebnis_tab);
        $array_keys = array_keys($pdf->ergebnis_tab);
        $summe = 0;
        $summe_alt = 0;
        $summe_neu = 0;

        for ($a = 0; $a < $anzahl; $a++) {
            $key = $array_keys [$a];
            $pdf_tab [$a] ['MIETER'] = $key;
            $pdf_tab [$a] ['ERGEBNIS'] = $pdf->ergebnis_tab [$key] ['ERGEBNIS'];
            $pdf_tab [$a] ['ERGEBNIS_A'] = nummer_punkt2komma($pdf->ergebnis_tab [$key] ['ERGEBNIS']);
            $summe += nummer_komma2punkt($pdf_tab [$a] ['ERGEBNIS_A']);

            $pdf_tab [$a] ['NK_VORSCHUSS_ALT'] = $pdf->ergebnis_tab [$key] ['VORSCHUSS_ALT'];
            $summe_alt += nummer_komma2punkt($pdf->ergebnis_tab [$key] ['VORSCHUSS_ALT']);

            $pdf_tab [$a] ['NK_VORSCHUSS_NEU'] = $pdf->ergebnis_tab [$key] ['VORSCHUSS_NEU'];
            $summe_neu += nummer_komma2punkt($pdf->ergebnis_tab [$key] ['VORSCHUSS_NEU']);

            $pdf_tab [$a] ['HK_VORSCHUSS_ALT'] = $pdf->ergebnis_tab [$key] ['HK_VORSCHUSS_ALT'];
            $pdf_tab [$a] ['HK_VORSCHUSS_NEU'] = $pdf->ergebnis_tab [$key] ['HK_VORSCHUSS_NEU'];
            $pdf_tab [$a] ['HK_SUMME'] = $pdf->ergebnis_tab [$key] ['HK_SUMME'];

            $pdf_tab [$a] ['ZEITRAUM'] = $pdf->ergebnis_tab [$key] ['ZEITRAUM'];
            $pdf_tab [$a] ['ANZ_MONATE'] = $pdf->ergebnis_tab [$key] ['ANZ_MONATE'];

            $pdf_tab [$a] ['A_MIETE'] = $pdf->ergebnis_tab [$key] ['A_MIETE'];
            $pdf_tab [$a] ['N_MIETE'] = $pdf->ergebnis_tab [$key] ['N_MIETE'];
            $pdf_tab [$a] ['ZEITRAUM'] = $pdf->ergebnis_tab [$key] ['ZEITRAUM'];

            /* Importieren in die Mietentwicklung */
            if (request()->has('me_import')) {

                $kos_typ = $pdf->ergebnis_tab [$key] ['KOS_TYP'];
                $kos_id = $pdf->ergebnis_tab [$key] ['KOS_ID'];
                $bk_ergebnis = $pdf_tab [$a] ['ERGEBNIS'];

                /* Betriebskostenergebnis speichern */
                if ($kos_typ == 'MIETVERTRAG') {
                    /* Prüfen ob Betriebskostenabrechnung in Mietentwicklung vorhanden, wenn nein, speichern, also nicht doppelt */
                    if (!$this->check_me($kos_typ, $kos_id, "Betriebskostenabrechnung $this->bk_jahr", $this->bk_verrechnungs_datum, $this->bk_verrechnungs_datum)) {

                        $last_me_id = $this->last_id('MIETENTWICKLUNG', 'MIETENTWICKLUNG_ID') + 1;
                        DB::insert("INSERT INTO MIETENTWICKLUNG VALUES(NULL, '$last_me_id', '$kos_typ', '$kos_id', 'Betriebskostenabrechnung $this->bk_jahr', '$this->bk_verrechnungs_datum', '$this->bk_verrechnungs_datum', '$bk_ergebnis', '1')");
                    } // end if check_me

                    /* Bei Veränderung der NK Vorauszahlungen, Änderungen speichern */
                    $nk_v_alt = nummer_komma2punkt($pdf_tab [$a] ['NK_VORSCHUSS_ALT']);
                    $nk_v_neu = nummer_komma2punkt($pdf_tab [$a] ['NK_VORSCHUSS_NEU']);

                    $nk_anpassungs_betrag = $nk_v_neu - $nk_v_alt;
                    if ($nk_anpassungs_betrag != 0) {

                        /* Neue NK Vorauszahlung speichern bzw. definieren ab Verrechnungsdatum */
                        /* Prüfen ob Nebenkostenanpassung in Mietentwicklung vorhanden, wenn nein, speichern, also nicht doppelt */
                        if (!$this->check_me($kos_typ, $kos_id, "Nebenkosten Vorauszahlung", $this->bk_verrechnungs_datum, '0000-00-00')) {

                            $last_me_id = $this->last_id('MIETENTWICKLUNG', 'MIETENTWICKLUNG_ID') + 1;
                            DB::insert("INSERT INTO MIETENTWICKLUNG VALUES(NULL, '$last_me_id', '$kos_typ', '$kos_id', 'Nebenkosten Vorauszahlung', '$this->bk_verrechnungs_datum', '0000-00-00', '$nk_v_neu', '1')");

                            /* Alte Nk Vorauszahlung mit Enddatum versehen */
                            $o = new objekt ();
                            $ablauf_datum = $o->datum_minus_tage($this->bk_verrechnungs_datum, '1');
                            DB::update("UPDATE MIETENTWICKLUNG SET ENDE='$ablauf_datum' WHERE KOSTENKATEGORIE = 'Nebenkosten Vorauszahlung' && BETRAG='$nk_v_alt' && MIETENTWICKLUNG_AKTUELL='1' && KOSTENTRAEGER_TYP='MIETVERTRAG' && KOSTENTRAEGER_ID='$kos_id'");
                        } // end if check_me
                    } // end if $nk_anpassungs_betrag <> 0)

                    /* Heizkostenvorauszahlungen anpassen */

                    /* Bei Veränderung der HK Vorauszahlungen, Änderungen speichern */
                    $hk_v_alt = nummer_komma2punkt($pdf_tab [$a] ['HK_VORSCHUSS_ALT']);
                    $hk_v_neu = nummer_komma2punkt($pdf_tab [$a] ['HK_VORSCHUSS_NEU']);

                    $hk_anpassungs_betrag = $hk_v_neu - $hk_v_alt;
                    if ($hk_anpassungs_betrag != 0) {

                        /* Neue HK Vorauszahlung speichern bzw. definieren ab Verrechnungsdatum */
                        /* Prüfen ob Heizkostenanpassung in Mietentwicklung vorhanden, wenn nein, speichern, also nicht doppelt */
                        if (!$this->check_me($kos_typ, $kos_id, "Heizkosten Vorauszahlung", $this->bk_verrechnungs_datum, '0000-00-00')) {

                            $last_me_id = $this->last_id('MIETENTWICKLUNG', 'MIETENTWICKLUNG_ID') + 1;
                            DB::insert("INSERT INTO MIETENTWICKLUNG VALUES(NULL, '$last_me_id', '$kos_typ', '$kos_id', 'Heizkosten Vorauszahlung', '$this->bk_verrechnungs_datum', '0000-00-00', '$hk_v_neu', '1')");

                            /* Alte HK Vorauszahlung mit Enddatum versehen */
                            $o = new objekt ();
                            $ablauf_datum = $o->datum_minus_tage($this->bk_verrechnungs_datum, '1');
                            DB::update("UPDATE MIETENTWICKLUNG SET ENDE='$ablauf_datum' WHERE KOSTENKATEGORIE = 'Heizkosten Vorauszahlung' && BETRAG='$hk_v_alt' && MIETENTWICKLUNG_AKTUELL='1' && KOSTENTRAEGER_TYP='MIETVERTRAG' && KOSTENTRAEGER_ID='$kos_id'");
                        } // end check hk vorhanden
                    } // end anpassung hk <> 0
                } // end if MV
            } // end if isset($_REQUEST[me_import]
        } // end for schleife

        $pdf_tab [$anzahl] ['ERGEBNIS'] = 'SUMME';
        $pdf_tab [$anzahl] ['ERGEBNIS_A'] = nummer_punkt2komma($summe);
        $pdf_tab [$anzahl] ['NK_VORSCHUSS_ALT'] = nummer_punkt2komma($summe_alt);
        $pdf_tab [$anzahl] ['NK_VORSCHUSS_NEU'] = nummer_punkt2komma($summe_neu);

        $cols = array(
            'MIETER' => "MIETER",
            'ZEITRAUM' => "Zeitraum",
            'ANZ_MONATE' => "Monate",
            'ERGEBNIS_A' => "Summe BK",
            'NK_VORSCHUSS_ALT' => "NK ALT",
            'NK_VORSCHUSS_NEU' => "NK NEU",
            'HK_SUMME' => "Summe HK",
            'HK_VORSCHUSS_ALT' => "HK ALT",
            'HK_VORSCHUSS_NEU' => "HK NEU",
            'A_MIETE' => "M. ALT",
            'N_MIETE' => "M.NEU"
        );
        $pdf->ezNewPage();
        $pdf->ezTable($pdf_tab, $cols, "Einzelergebnise der Abrechnung", array(
            'showHeadings' => 1,
            'shaded' => 1,
            'titleFontSize' => 7,
            'fontSize' => 7,
            'xPos' => 50,
            'xOrientation' => 'right',
            'width' => 500,
            'cols' => array(
                'MIETER' => array(
                    'justification' => 'left'
                ),
                'ERGEBNIS' => array(
                    'justification' => 'right'
                ),
                'ERGEBNIS_A' => array(
                    'justification' => 'right',
                    'width' => '40'
                ),
                'NK_VORSCHUSS_ALT' => array(
                    'justification' => 'right',
                    'width' => '40'
                ),
                'NK_VORSCHUSS_NEU' => array(
                    'justification' => 'right',
                    'width' => '40'
                ),
                'HK_VORSCHUSS_ALT' => array(
                    'justification' => 'right',
                    'width' => '40'
                ),
                'HK_VORSCHUSS_NEU' => array(
                    'justification' => 'right',
                    'width' => '40'
                ),
                'HK_SUMME' => array(
                    'justification' => 'right',
                    'width' => '40'
                )
            )
        ));
        // #######ÜBERSICHT ENERGIEVERBRAUCHSEITEN VORSCHÜSSE HK - KOSTEN TECHEM ETC*/
        $anz_ene = count($pdf->energie_abr);
        if ($anz_ene > 0) {
            $pdf->ezNewPage();
        }
        natsort($pdf->energie_abr);
        for ($a = 0; $a < $anz_ene; $a++) {
            $keys = array_keys($pdf->energie_abr [$a]);

            $key = $keys [0];
            // $eine_abr = $pdf->energie_abr[$a][$key];
            // $pdf->eztext($key);

            $cols = array(
                'KOSTENKAT' => "Bezeichnung",
                "BETRAG" => "Betrag"
            );
            $pdf->ezTable($pdf->energie_abr [$a] [$key], $cols, "", array(
                'showHeadings' => 0,
                'shaded' => 1,
                'titleFontSize' => 7,
                'fontSize' => 8,
                'xPos' => 55,
                'xOrientation' => 'right',
                'width' => 500,
                'cols' => array(
                    'BETRAG' => array(
                        'justification' => 'right',
                        'width' => 80
                    ),
                    'KOSTENKAT' => array(
                        'justification' => 'left'
                    )
                )
            ));
            $pdf->ezSetDy(-2);
            // $pdf->ezTable($pdf->energie_abr[$a][$key]);
        }

        /* Ausgabe */
        ob_end_clean();

        $dateiname = "$this->bk_berechnungs_datum $this->bk_bezeichnung - $this->bk_jahr.pdf";
        $pdf_opt ['Content-Disposition'] = $dateiname;
        $pdf->ezStream($pdf_opt);
    }

    function bk_abrechnung_speichern($profil_id, $profil_bez, $profil_jahr, $wirt_e, $wirt_name, $datum, $anz_einheiten, $qm_gesamt, $qm_wohnraum, $qm_gewerbe, $anz_konten, $anz_abrechnungen, $ersteller, $partner_id, $kontenrahmen_id)
    {
        $last_b_id = $this->last_id('BK_ABRECHNUNGEN', 'B_ID') + 1;

        DB::insert("INSERT INTO BK_ABRECHNUNGEN VALUES (NULL, '$last_b_id', '$profil_id', '$profil_bez','$profil_jahr', '$wirt_e', '$wirt_name', '$datum', '$anz_einheiten', '$qm_gesamt', '$qm_wohnraum', '$qm_gewerbe', '$anz_konten', '$anz_abrechnungen', '$ersteller', '$partner_id','$kontenrahmen_id','1')");
        return $last_b_id;
    }

    function pdf_uebersicht_profil($pdf, $profil_id)
    {
        if (empty ($pdf)) {
            ob_clean();
            // ausgabepuffer leeren
            $pdf = new Cezpdf ('a4', 'portrait');
            $bpdf = new b_pdf ();
            $bpdf->b_header($pdf, 'Partner', session()->get('partner_id'), 'portrait', 'Helvetica.afm', 6);
        }

        $profil_zeilen_arr = $this->uebersicht_profil_arr($profil_id);
        $anzahl = count($profil_zeilen_arr);

        for ($a = 0; $a < $anzahl; $a++) {
            $table_arr [$a] ['KONTO'] = $profil_zeilen_arr [$a] ['KONTO'];
            $table_arr [$a] ['KONTO_BEZ'] = $profil_zeilen_arr [$a] ['KONTO_BEZ'];
            $table_arr [$a] ['BUCHUNG_ID'] = $profil_zeilen_arr [$a] ['BUCHUNG_ID'];
            $table_arr [$a] ['KEY_ID'] = $profil_zeilen_arr [$a] ['KEY_ID'];
            $table_arr [$a] ['ANTEIL'] = $profil_zeilen_arr [$a] ['ANTEIL'];
            $table_arr [$a] ['KOSTENTRAEGER_TYP'] = $profil_zeilen_arr [$a] ['KOSTENTRAEGER_TYP'];
            $table_arr [$a] ['KOSTENTRAEGER_ID'] = $profil_zeilen_arr [$a] ['KOSTENTRAEGER_ID'];
            $table_arr [$a] ['HNDL_BETRAG'] = $profil_zeilen_arr [$a] ['HNDL_BETRAG'];
            if ($table_arr [$a] ['KOSTENTRAEGER_TYP'] != 'Wirtschaftseinheit') {
                $r = new rechnung ();
                $kos_bez = $r->kostentraeger_ermitteln($table_arr [$a] ['KOSTENTRAEGER_TYP'], $table_arr [$a] ['KOSTENTRAEGER_ID']);
            } else {
                $w = new wirt_e ();
                $w->get_wirt_e_infos($profil_zeilen_arr [$a] ['KOSTENTRAEGER_ID']);
                $kos_bez = $w->w_name;
            }

            $table_arr [$a] ['KOSTENTRAEGER_BEZ'] = $table_arr [$a] ['KOSTENTRAEGER_TYP'] . '  ' . $kos_bez;
            $this->bk_buchungen_details($table_arr [$a] ['BUCHUNG_ID']);
            $table_arr [$a] ['BETRAG'] = $this->buchung_betrag;
            $table_arr [$a] ['DATUM'] = date_mysql2german($this->buchungsdatum);

            $this->get_genkey_infos($table_arr [$a] ['KEY_ID']);
            $table_arr [$a] ['KEY_ME'] = $this->g_key_me;
        }

        if (!empty ($pdf)) {
            $pdf->ezNewPage();
        }
        $cols = array(
            'KONTO' => "Kostenkonto",
            'KONTO_BEZ' => "Bezeichnung",
            'BUCHUNG_ID' => "Buchungsnr",
            'KEY_ME' => 'Gen. Schl.',
            'ANTEIL' => 'Anteil',
            'KOSTENTRAEGER_BEZ' => 'Aufteilung Kosten',
            'DATUM' => 'Buchungsdatum',
            'BETRAG' => 'BETRAG',
            'HNDL_BETRAG' => 'HNDL BETRAG'
        );
        $pdf->ezTable($table_arr, $cols, "Profilübersicht $this->bk_bezeichnung $this->bk_kos_bez $this->bk_jahr", array(
            'showHeadings' => 1,
            'shaded' => 1,
            'titleFontSize' => 8,
            'fontSize' => 7,
            'xPos' => 50,
            'xOrientation' => 'right',
            'width' => 500
        ));

        /* Auch ausgeben falls kein pdf objekt �bermittelt */
        if (empty ($pdf)) {
            ob_end_clean();
            $pdf->ezStream();
        }
    }

    function uebersicht_profil_arr($profil_id)
    {
        $db_abfrage = " SELECT BK_KONTEN.BK_K_ID, KONTO, KONTO_BEZ, BK_BERECHNUNG_BUCHUNGEN . * FROM `BK_KONTEN` JOIN BK_BERECHNUNG_BUCHUNGEN ON ( BK_KONTEN.BK_K_ID = BK_BERECHNUNG_BUCHUNGEN.BK_K_ID ) WHERE BK_KONTEN.BK_PROFIL_ID = '$profil_id' AND BK_KONTEN.`AKTUELL` = '1' && BK_BERECHNUNG_BUCHUNGEN.AKTUELL = '1' ORDER BY KONTO ASC ";
        $result = DB::select($db_abfrage);
        return $result;
    }

    function pdf_einzel_tab(Cezpdf &$pdf, $bk_arr, $label, $kontroll_tab_druck)
    {
        $empf = $bk_arr ['EMPF'];
        $empf_kos_typ = $bk_arr ['KOS_TYP'];
        $empf_kos_id = $bk_arr ['KOS_ID'];
        $mieternummer = $bk_arr ['EINHEIT_NAME'];
        $zeitraum = $bk_arr ['ZEITRAUM'];
        $zeitraum_arr = explode('.', $zeitraum);
        $anzahl_monate = $zeitraum_arr [3] - $zeitraum_arr [1] + 1;

        $pdf->ergebnis_tab ["$mieternummer - $empf"] ['KOS_TYP'] = $empf_kos_typ;
        $pdf->ergebnis_tab ["$mieternummer - $empf"] ['KOS_ID'] = $empf_kos_id;
        $pdf->ergebnis_tab ["$mieternummer - $empf"] ['ZEITRAUM'] = $zeitraum;
        $pdf->ergebnis_tab ["$mieternummer - $empf"] ['ANZ_MONATE'] = $anzahl_monate;

        $einheit_typ = $bk_arr ['EINHEIT_TYP'];
        $einheit_qm = $bk_arr ['EINHEIT_QM'];

        $this->bk_profil_infos(session()->get('profil_id'));

        $anzahl_zeilen = count($bk_arr) - 10;
        // WICHTIG 10 felder abschneiden
        $cols = array(
            'KOSTENART' => "Betriebskostenart",
            'G_BETRAG' => "Gesamtkosten umlagefähige Betriebskosten",
            'G_HNDL' => "Gesamtkosten haushaltsnahe Dienst- und Handwerkerleistungen",
            'G_KEY' => "Wohnfläche / Verteiler- Schlüssel in Mieteinheiten (ME)",
            'QM_ME' => "Ihre ME",
            'BET_HNDL' => "Anteil für Ihre Wohnung haushaltsnahe Dienst- und Handwerkerleistungen",
            'BET_G' => "Beteiligung"
        );
        $g_beteiligung = 0.00;
        for ($b = 0; $b < $anzahl_zeilen; $b++) {
            $tab [$b] ['KOSTENART'] = $bk_arr [$b] ['KOSTENART'];
            $tab [$b] ['G_KOS_BEZ'] = $bk_arr [$b] ['G_KOS_BEZ'];
            $tab [$b] ['G_BETRAG'] = $bk_arr [$b] ['G_BETRAG'];
            $tab [$b] ['ANTEIL'] = $bk_arr [$b] ['ANTEIL'] . '%';
            $tab [$b] ['UMLAGE'] = $bk_arr [$b] ['UMLAGE'];
            $tab [$b] ['ME'] = $bk_arr [$b] ['ME'];
            $tab [$b] ['G_KEY'] = nummer_punkt2komma($bk_arr [$b] ['G_KEY']) . ' ' . $bk_arr [$b] ['ME'];
            $tab [$b] ['QM'] = $bk_arr [$b] ['QM'];
            $tab [$b] ['QM_ME'] = nummer_punkt2komma($bk_arr [$b] ['QM']) . ' ' . $bk_arr [$b] ['ME'];
            $tab [$b] ['BET_G'] = $bk_arr [$b] ['BET_G'];
            $tab [$b] ['G_HNDL'] = $bk_arr [$b] ['G_HNDL'];
            $tab [$b] ['BET_HNDL'] = $bk_arr [$b] ['BET_HNDL'];
            $tab [$b] ['GENKEY_ID'] = $bk_arr [$b] ['GENKEY_ID'];
            $g_beteiligung += nummer_komma2punkt($bk_arr [$b] ['BET_G']);
            $g_beteiligung_hndl += nummer_komma2punkt($bk_arr [$b] ['BET_HNDL']);
        }

        /* Prüfen ob Kaltwasserkosten worden sind */
        $check_kw = $this->check_kw_abrechnung($empf_kos_typ, $empf_kos_id, $this->bk_jahr);
        if ($check_kw == true) {
            $kw_summe = $this->summe_kw_abrechnung($empf_kos_typ, $empf_kos_id, $this->bk_jahr);
            $kw_summe_a = nummer_punkt2komma($kw_summe);
            $tab [$b + 1] ['KOSTENART'] = 'Be- und Entwässerung lt. Kaltwasserabr.';
            $tab [$b + 1] ['G_KEY'] = 'n. Verbrauch';
            $tab [$b + 1] ['BET_G'] = $kw_summe_a;
            $g_beteiligung += $kw_summe;
        }

        $b++;

        $tab [$b + 1] ['KOSTENART'] = '<b>Gesamtkosten</b>';
        $tab [$b + 1] ['BET_G'] = '<b>' . nummer_punkt2komma($g_beteiligung) . '</b>';
        $tab [$b + 1] ['BET_HNDL'] = '<b>' . nummer_punkt2komma($g_beteiligung_hndl) . '</b>';

        if ($empf_kos_typ == 'MIETVERTRAG') {
            $mz = new miete ();
            $summe_nebenkosten_jahr = $mz->summe_nebenkosten_im_jahr($empf_kos_typ, $empf_kos_id, $this->bk_jahr);
            $summe_hk_jahr = $mz->summe_nebenkosten_im_jahr($empf_kos_typ, $empf_kos_id, $this->bk_jahr);
        } else {
            $summe_nebenkosten_jahr = 0.00;
            $summe_hk_jahr = '0.00';
        }

        $tab [$b + 2] ['KOSTENART'] = '<b>Ihr Vorschuss/Jahr</b>';
        $tab [$b + 2] ['BET_G'] = '<b>' . nummer_punkt2komma($summe_nebenkosten_jahr) . '</b>';

        $ergebnis = $g_beteiligung + $summe_nebenkosten_jahr;
        if ($ergebnis < 0) {
            $txt = 'Nachzahlung';
            $ergebnis_a = substr($ergebnis, 1);
        }
        if ($ergebnis > 0) {
            $txt = 'Guthaben';
            $ergebnis_a = $ergebnis;
        }

        if ($ergebnis == null) {
            $txt = 'Ergebnis';
            $ergebnis_a = $ergebnis;
        }

        $tab [$b + 3] ['KOSTENART'] = "<b>$txt</b>";
        $tab [$b + 3] ['BET_G'] = '<b>' . nummer_punkt2komma($ergebnis_a) . '</b>';

        $pdf->ezNewPage();
        $pdf->ezStopPageNumbers();
        $cols1 = array(
            'KOSTENART' => "Betriebskostenart",
            "KOSTEN_GESAMT" => "Kosten gesamt",
            'KOSTEN_GEWERBE' => "Gewerbeanteil $this->wirt_g_qm_gewerbe_a  m²",
            'KOSTEN_WOHNRAUM' => "Wohnanteil $this->wirt_g_qm_wohnen_a  m²"
        );
        // $i=$pdf->ezStartPageNumbers(545,728,6,'','Seite {PAGENUM} von {TOTALPAGENUM}',1);
        $p = new partners ();
        $p->get_partner_info(session()->get('partner_id'));
        $pdf->addText(480, 697, 8, "$p->partner_ort, $this->bk_berechnungs_datum_d");
        // $zeitraum = "01.09.2011 - 31.12.2011";
        $pdf->ezText('<b>Betriebskostenabrechnung für den Zeitraum:   ' . $zeitraum . '</b>', 8);
        $pdf->ezSetDy(-15);
        $pdf->ezText("<b>$this->bk_bezeichnung</b>", 8);
        $pdf->ezText("Wirtschaftseinheit: $this->bk_kos_bez ", 8);
        $pdf->ezText('Mieternummer:   ' . $mieternummer . " - $einheit_typ", 8);
        $pdf->ezText('Mieter:                ' . $empf, 8);
        $pdf->ezSetDy(-20);

        /* Ergebnis in die Übersichtstabelle */
        $pdf->ergebnis_tab ["$mieternummer - $empf"] ['ERGEBNIS'] = $ergebnis;
        $pdf->ergebnis_tab ["$mieternummer - $empf"] ['SUMME_NK'] = $summe_nebenkosten_jahr;

        $pdf->ezTable($kontroll_tab_druck, $cols1, "Aufteilung Gewerbe- / Wohnfläche", array(
            'showHeadings' => 1,
            'shaded' => 1,
            'titleFontSize' => 7,
            'fontSize' => 7,
            'xPos' => 55,
            'xOrientation' => 'right',
            'width' => 500,
            'cols' => array(
                'KOSTENART' => array(
                    'justification' => 'left'
                ),
                'KOSTEN_GESAMT' => array(
                    'justification' => 'right'
                ),
                'KOSTEN_GEWERBE' => array(
                    'justification' => 'right'
                ),
                'KOSTEN_WOHNRAUM' => array(
                    'justification' => 'right'
                )
            )
        ));
        $pdf->ezSetDy(-20);

        $pdf->ezTable($tab, $cols, "$label", array(
            'showHeadings' => 1,
            'shaded' => 1,
            'titleFontSize' => 8,
            'fontSize' => 7,
            'xPos' => 55,
            'xOrientation' => 'right',
            'width' => 500,
            'cols' => array(
                'KOSTENART' => array(
                    'justification' => 'left'
                ),
                'G_BETRAG' => array(
                    'justification' => 'right',
                    'width' => 60
                ),
                'G_HNDL' => array(
                    'justification' => 'right',
                    'width' => 60
                ),
                'ANTEIL' => array(
                    'justification' => 'right',
                    'width' => 40
                ),
                'UMLAGE' => array(
                    'justification' => 'right',
                    'width' => 45
                ),
                'G_KOS_BEZ' => array(
                    'justification' => 'right',
                    'width' => 45
                ),
                'G_KEY' => array(
                    'justification' => 'right',
                    'width' => 55
                ),
                'QM_ME' => array(
                    'justification' => 'right',
                    'width' => 50
                ),
                'BET_G' => array(
                    'justification' => 'right',
                    'width' => 45
                ),
                'BET_HNDL' => array(
                    'justification' => 'right',
                    'width' => 65
                )
            )
        ));

        if ($empf_kos_typ == 'MIETVERTRAG') {
            $mz = new miete ();
            $mk = new mietkonto ();
            $monat = date("m");
            $jahr = date("Y");
            $ber_datum_arr = explode('-', $this->bk_berechnungs_datum);
            $ver_datum_arr = explode('-', $this->bk_verrechnungs_datum);
            $monat_b = $ber_datum_arr [1];
            $jahr_b = $ber_datum_arr [0];
            $monat_v = $ver_datum_arr [1];
            $jahr_v = $ver_datum_arr [0];

            $mk->kaltmiete_monatlich_ink_vz($empf_kos_id, $monat_b, $jahr_b);
            $mk->ausgangs_kaltmiete_a = nummer_punkt2komma($mk->ausgangs_kaltmiete);

            $anp_tab [0] ['KOSTENKAT'] = 'Miete kalt';
            $anp_tab [0] ['AKTUELL'] = "$mk->ausgangs_kaltmiete_a €";
            $anp_tab [0] ['ANPASSUNG'] = '--';

            $mk1 = new mietkonto ();
            $mk1->kaltmiete_monatlich_ink_vz($empf_kos_id, $monat_v, $jahr_v);
            $mk1->ausgangs_kaltmiete_a = nummer_punkt2komma($mk1->ausgangs_kaltmiete);

            $anp_tab [0] ['NEU'] = "$mk1->ausgangs_kaltmiete_a €";

            $this->get_anpassung_details(session()->get('profil_id'), 'Nebenkosten Vorauszahlung');

            $mv = new mietvertraege();
            $mv->get_mietvertrag_infos_aktuell($empf_kos_id);

            $pdf->addText(480, 697, 8, "$p->partner_ort, $this->bk_berechnungs_datum_d");
            /* Wenn MV aktuell anpassen, wenn ausgezogen nicht */

            if ($empf_kos_typ != 'Leerstand') {
                // ##########NEU ENERGIEVERBRAUCH GEGEN VORSCHÜSSE###################
                /* prüfen ob HK Vorschüsse vorhanden */
                $mz2 = new miete ();
                $met = new mietentwicklung ();
                // $summe_nebenkosten_jahr = $mz->summe_nebenkosten_im_jahr($empf_kos_typ,$empf_kos_id,$this->bk_jahr);
                $summe_hk_vorschuss = $mz2->summe_heizkosten_im_jahr($empf_kos_typ, $empf_kos_id, $this->bk_jahr);
                $energiekosten_jahr = $met->get_energieverbrauch(strtoupper($empf_kos_typ), $empf_kos_id, $this->bk_jahr);
                if ($energiekosten_jahr > 0) {

                    $pdf->ezNewPage();
                    $pdf->addText(480, 697, 8, "$p->partner_ort, $this->bk_berechnungs_datum_d");
                    $pdf->ezText('<b>Energiekostenabrechnung für den Zeitraum:   ' . $zeitraum . '</b>', 8);
                    $pdf->ezSetDy(-15);
                    $pdf->ezText("<b>$this->bk_bezeichnung</b>", 8);
                    $pdf->ezText("Wirtschaftseinheit: $this->bk_kos_bez ", 8);
                    $pdf->ezText('Mieternummer:   ' . $mieternummer . " - $einheit_typ", 8);
                    $pdf->ezText('Mieter:                ' . $empf, 8);
                    $pdf->ezSetDy(-20);

                    $pdf->ezText("$mv->mv_anrede", 9);
                    $pdf->ezText("die Abrechnung der Energiekosten für den o.g. Zeitraum stellt sich wie folgt da:", 9);
                    $hk_verbrauch_tab [0] ['KOSTENKAT'] = "Ihre Vorauszahlung im Jahr $this->bk_jahr";
                    $hk_verbrauch_tab [0] ['BETRAG'] = nummer_punkt2komma_t($summe_hk_vorschuss);

                    /* Heizkostenverbrauch abfragen */

                    // $energiekosten_jahr = $met->get_energieverbrauch(strtoupper($empf_kos_typ), $empf_kos_id, $this->bk_jahr);
                    $hk_verbrauch_tab [1] ['KOSTENKAT'] = "Angefallene Kosten lt. Abrechnung in $this->bk_jahr";
                    $hk_verbrauch_tab [1] ['BETRAG'] = nummer_punkt2komma_t($energiekosten_jahr);

                    /* Ergebnis ermittlen */
                    $ergebnis_energie = $summe_hk_vorschuss - $energiekosten_jahr;
                    if ($ergebnis_energie < 0) {
                        $energie_text = "Ihre Nachzahlung";
                    }
                    if ($ergebnis_energie > 0) {
                        $energie_text = "Ihr Guthaben";
                    }

                    if ($ergebnis_energie == 0) {
                        $energie_text = "Saldo";
                    }

                    $hk_verbrauch_tab [2] ['KOSTENKAT'] = "<b>$energie_text $this->bk_jahr</b>";
                    $hk_verbrauch_tab [2] ['BETRAG'] = "<b>" . nummer_punkt2komma_t($ergebnis_energie) . "</b>";

                    $pdf->ezSetDy(-20);
                    $cols = array(
                        'KOSTENKAT' => "Bezeichnung",
                        "BETRAG" => "Betrag"
                    );
                    $pdf->ezTable($hk_verbrauch_tab, $cols, "", array(
                        'showHeadings' => 0,
                        'shaded' => 1,
                        'titleFontSize' => 7,
                        'fontSize' => 8,
                        'xPos' => 55,
                        'xOrientation' => 'right',
                        'width' => 500,
                        'cols' => array(
                            'BETRAG' => array(
                                'justification' => 'right',
                                'width' => 80
                            ),
                            'KOSTENKAT' => array(
                                'justification' => 'left'
                            )
                        )
                    ));
                    $pdf->ezSetDy(-20);
                    $pdf->ezText("Die Energieabrechnung des Abrechnungsunternehmens legen wir dieser Abrechnung bei.", 9);
                    $pdf->ezSetDy(-10);

                    $pdf->ezText("Mit freundlichen Grüßen", 9);
                    $pdf->ezSetDy(-30);
                    $pdf->ezText("Ihre Hausverwaltung", 9);

                    $hk_verbrauch_tab [3] ['KOSTENKAT'] = "$mieternummer - $empf - $zeitraum";

                    $pdf->energie_abr [] ["$mieternummer - $empf - $zeitraum"] = $hk_verbrauch_tab;
                } // #ende wenn energieabrecnung drin
            } // #ende wenn nicht leerstand

            // ########################################################################

            if ($mv->mietvertrag_aktuell) {
                /* ANPASSUNGSBLATT */
                $pdf->ezNewPage();
                $pdf->addText(480, 697, 8, "$p->partner_ort, $this->bk_berechnungs_datum_d");

                $pap = $mv->postanschrift [0] ['anschrift'];
                if (!empty ($pap)) {
                    $pdf->ezText("$pap", 10);
                } else {
                    $pdf->ezText("$mv->personen_name_string_u\n$mv->haus_strasse $mv->haus_nr\n\n$mv->haus_plz $mv->haus_stadt", 10);
                }
                $pdf->ezSetDy(-60);
                $check_hk = $this->check_hk_abrechnung($empf_kos_typ, $empf_kos_id, $this->bk_jahr);
                $bk_summe = $ergebnis;

                /* Summe aus der Abrechnung */
                $hk_summe = $this->summe_hk_abrechnung($empf_kos_typ, $empf_kos_id, $this->bk_jahr);
                /* NEU */
                /* Anpassung Nachzahlung Heizkosten */
                /* Wenn Nachzahlung, dann mindestens 50/12+1EUR=5.00 EUR */

                if ($hk_summe < 0) {
                    // echo $hk_summe;
                    $hk_monatlich_letzte_vj = $mz->letzte_hk_vorauszahlung($empf_kos_typ, $empf_kos_id, $this->bk_jahr, 'Heizkosten Vorauszahlung');
                    $hk_monatlich_letzte = $mz->letzte_hk_vorauszahlung($empf_kos_typ, $empf_kos_id, $jahr, 'Heizkosten Vorauszahlung');
                    $hk_jahr_aktuell = $hk_monatlich_letzte * 12;
                    $hk_diff = $hk_jahr_aktuell - (($hk_summe * -1) + ($anzahl_monate * $hk_monatlich_letzte_vj));
                    $hk_anp_betrag_neu = ($hk_summe - 25) / 12;
                    $hk_anp_betrag_neu = intval($hk_anp_betrag_neu - 1);
                    if ($hk_diff >= 0) {
                        $hk_anp_betrag_neu = '0.00';
                    } else {
                        $hk_anp_betrag_neu = ($hk_diff / 12) * -1;
                    }
                } else {
                    /* Guthaben bei HK */
                    $hk_anp_betrag_neu = ($hk_summe - 50) / 12;
                    $hk_anp_betrag_neu = intval($hk_anp_betrag_neu);
                    if ($hk_anp_betrag_neu < 0) {
                        $hk_anp_betrag_neu = 0.00;
                    }
                    /* Unter 5 Euro nicht anpassen */
                    if ($hk_anp_betrag_neu > 0.00 && $hk_anp_betrag_neu < 5.00) {
                        $hk_anp_betrag_neu = 0.00;
                    }
                    if ($hk_anp_betrag_neu > 5) {
                        $hk_anp_betrag_neu = -$hk_anp_betrag_neu - 1;
                    }

                    if ($hk_summe == 0 or $summe_hk_jahr == 0) {
                        $hk_anp_betrag_neu = '0.00';
                    }
                } // END HK ANPASSUNG

                /* NEU BK */
                /* Anpassung Nachzahlung BK */

                /* Summe aus der Abrechnung */
                if ($bk_summe < 0) {
                    // echo $hk_summe;
                    $bk_anp_betrag_neu = ($bk_summe - 24) / 12;
                    $bk_anp_betrag_neu = intval($bk_anp_betrag_neu - 1);
                    $bk_anp_betrag_neu = substr($bk_anp_betrag_neu, 1);
                } else {
                    /* Guthaben bei BK */
                    if ($bk_summe > 24) {
                        $bk_anp_betrag_neu = ($bk_summe - 24) / 12;
                    } else {
                        $bk_anp_betrag_neu = $bk_summe / 12;
                    }

                    $bk_anp_betrag_neu = intval($bk_anp_betrag_neu);
                    if ($bk_anp_betrag_neu < 0) {
                        $bk_anp_betrag_neu = 0.00;
                    }

                    /* Unter 5 Euro nicht anpassen */
                    if ($bk_anp_betrag_neu > 0.00 && $bk_anp_betrag_neu < 5.00) {
                        $bk_anp_betrag_neu = 0.00;
                    }

                    if ($bk_anp_betrag_neu > 5) {
                        $bk_anp_betrag_neu = $bk_anp_betrag_neu - 1;
                    }
                } // ENDE BK ANPASSUNGSERMITTLUNG

                if ($bk_summe == 0 or $summe_nebenkosten_jahr == 0) {
                    $bk_anp_betrag_neu = '0.00';
                }

                $pdf->ergebnis_tab ["$mieternummer - $empf"] ['HK_SUMME'] = nummer_punkt2komma($hk_summe);
                /* Summe aller Vorauszahlungen im Jahr der Abrechnung */
                $summe_hk_jahr = $mz->summe_heizkosten_im_jahr($empf_kos_typ, $empf_kos_id, $this->bk_jahr);
                $this->get_anpassung_details(session()->get('profil_id'), 'Heizkosten Vorauszahlung');

                $hk_monatlich_bisher_schnitt = $summe_hk_jahr / $anzahl_monate;
                $hk_monatlich_letzte = $mz->letzte_hk_vorauszahlung($empf_kos_typ, $empf_kos_id, $jahr, 'Heizkosten Vorauszahlung');

                /* bis hier alles ok */
                $hk_monatlich_genau = (-$summe_hk_jahr + $hk_summe) / $anzahl_monate;
                if ($hk_monatlich_genau < 0) {
                    $hk_monatlich_genau = substr($hk_monatlich_genau, 1);
                }
                echo "$hk_monatlich_genau = (-$summe_hk_jahr + $hk_summe)/$anzahl_monate;";

                $vorauszahlung_n_jahr = $hk_monatlich_letzte * $anzahl_monate;
                $festgesetzt_n_jahr = $vorauszahlung_n_jahr - $hk_summe;
                $hk_vorschuss_neu = $festgesetzt_n_jahr / $anzahl_monate;
                $x = $hk_monatlich_letzte + $hk_anp_betrag_neu;

                echo "HK $hk_summe $hk_monatlich_letzte $hk_anp_betrag_neu  $x  $hk_vorschuss_neu<br>";
                echo "HK ANP: $hk_anp_betrag_neu<br><hr>";

                $hk_vorschuss_neu = $x;
                $hk_monatlich_bisher_a = nummer_punkt2komma($hk_monatlich_letzte);

                $this->get_genkey_infos($this->bk_an_keyid);
                if ($this->bk_an_keyid == '1') {
                    $hk_vorschuss_neu = $hk_vorschuss_neu + ($this->bk_an_fest_betrag * $einheit_qm);
                }
                if ($this->bk_an_keyid == '2') {
                    $hk_vorschuss_neu = $hk_vorschuss_neu + $this->bk_an_fest_betrag;
                }

                $hk_anp_betrag_a = nummer_punkt2komma($hk_anp_betrag_neu);
                $hk_vorschuss_neu_a = nummer_punkt2komma($hk_vorschuss_neu);
                if ($check_hk == true) {
                    $anp_tab [2] ['KOSTENKAT'] = 'Heizkosten Vorauszahlung';
                    $anp_tab [2] ['AKTUELL'] = "$hk_monatlich_bisher_a €";

                    $anp_tab [2] ['ANPASSUNG'] = "$hk_anp_betrag_a €";
                    $anp_tab [2] ['NEU'] = "$hk_vorschuss_neu_a €";
                    $pdf->ergebnis_tab ["$mieternummer - $empf"] ['HK_VORSCHUSS_ALT'] = $hk_monatlich_bisher_a;
                    $pdf->ergebnis_tab ["$mieternummer - $empf"] ['HK_VORSCHUSS_NEU'] = $hk_vorschuss_neu_a;

                    if ($hk_summe > $hk_monatlich_bisher_schnitt * $anzahl_monate) {
                        throw new \App\Exceptions\MessageException(new \App\Messages\WarningMessage("$mieternummer $empf -  Summe Hk Abrechnung > eingezahlte Summe für HK im Jahr"));
                    }
                }
                if ($check_hk == true) {
                    $pdf->ezText("<b>Anpassung der monatlichen Heiz- und Betriebskostenvorauszahlungen ab $this->bk_verrechnungs_datum_d</b>", 10);
                } else {
                    $pdf->ezText("<b>Anpassung der monatlichen Betriebskostenvorauszahlungen ab $this->bk_verrechnungs_datum_d</b>", 10);
                }
                $pdf->ezText("<b>$this->bk_bezeichnung</b>", 10);
                $pdf->ezText("Wirtschaftseinheit: $this->bk_kos_bez      Einheit: $mv->einheit_kurzname", 10);
                $pdf->ezSetDy(-10);
                /* Faltlinie */
                $pdf->setLineStyle(0.2);
                $pdf->line(5, 542, 20, 542);
                $pdf->ezText("$anrede", 10);

                $pdf->ezText("$mv->mv_anrede", 10);
                $text_nachzahlung = "aufgrund der vorliegenden Nebenkostenabrechnung und zu erwartender Kostensteigerungen, erfolgt hiermit eine Änderung der monatlichen Betriebskostenvorauszahlungen auf der Grundlage des § 560 BGB, wie nachfolgend aufgeführt ab dem $this->bk_verrechnungs_datum_d.";

                $text_guthaben = "aufgrund der vorliegenden Nebenkostenabrechnung und zu erwartender Kostensteigerungen, erfolgt hiermit eine Änderung der monatlichen Betriebskostenvorauszahlungen auf der Grundlage des § 560 BGB, wie nachfolgend aufgeführt ab dem $this->bk_verrechnungs_datum_d.";

                if ($txt == 'Nachzahlung') {
                    $pdf->ezText("$text_nachzahlung", 10, array(
                        'justification' => 'full'
                    ));
                }

                if ($txt == 'Guthaben') {
                    $pdf->ezText("$text_guthaben", 10, array(
                        'justification' => 'full'
                    ));
                }
                $pdf->ezSetDy(-15);

                /* BK NK ANPASSUNG */

                $this->get_anpassung_details(session()->get('profil_id'), 'Nebenkosten Vorauszahlung');
                $jahr_vorschuss = date("Y");
                $vorschuesse_aktuell = $mz->letzte_hk_vorauszahlung($empf_kos_typ, $empf_kos_id, $jahr_vorschuss, 'Nebenkosten Vorauszahlung');

                $vorschuesse_neu = $g_beteiligung / $anzahl_monate;
                $vorschuesse_aktuell_a = nummer_punkt2komma($vorschuesse_aktuell);
                if ($vorschuesse_neu < 0) {
                    $vorschuesse_neu = substr($vorschuesse_neu, 1);
                }

                $this->get_genkey_infos($this->bk_an_keyid);
                if ($this->bk_an_keyid == '1') {
                    $vorschuesse_neu = $vorschuesse_neu + ($this->bk_an_fest_betrag * $einheit_qm);
                }
                if ($this->bk_an_keyid == '2') {
                    $vorschuesse_neu = $vorschuesse_neu + $this->bk_an_fest_betrag;
                }
                $anp_betrag = $vorschuesse_neu - $vorschuesse_aktuell;

                if ($ergebnis > 0) {
                    $xbk = intval($vorschuesse_aktuell - $bk_anp_betrag_neu);
                } else {
                    $xbk = intval($vorschuesse_aktuell + $bk_anp_betrag_neu);
                }
                $bk_anp_betrag_neu = $xbk - $vorschuesse_aktuell;
                echo "BK: $vorschuesse_aktuell $bk_anp_betrag_neu  $xbk<br>";
                echo "BK_ANP $bk_anp_betrag_neu<br>";

                /* Wenn keine VZ Anteilig gezahlt, BK Anpassen - Nettomieter!!!!!!!!! */
                $mkk = new mietkonto ();
                if ($mkk->check_vz_anteilig($empf_kos_id, $monat, $jahr) == true) {
                    $anp_betrag_a = nummer_punkt2komma(intval($anp_betrag));
                    $vorschuesse_neu = $vorschuesse_aktuell + intval($anp_betrag);
                    $vorschuesse_neu_a = nummer_punkt2komma($vorschuesse_neu);
                } else {
                    /* Wenn VZ Anteilig gezahlt, keine BK Anpassen - Bruttomieter!!!!!!!!! */
                    $vorschuesse_aktuell = 0;
                    $vorschuesse_neu = 0;

                    $anp_betrag_a = nummer_punkt2komma(0);
                    $vorschuesse_neu_a = nummer_punkt2komma($vorschuesse_aktuell);
                }

                $anp_tab [1] ['KOSTENKAT'] = 'Betriebskosten Vorauszahlung';
                $anp_tab [1] ['AKTUELL'] = "$vorschuesse_aktuell_a €";
                $anp_tab [1] ['ANPASSUNG'] = "$anp_betrag_a €";
                $anp_tab [1] ['NEU'] = "$vorschuesse_neu_a €";

                $pdf->ergebnis_tab ["$mieternummer - $empf"] ['VORSCHUSS_ALT'] = "$vorschuesse_aktuell_a";
                if ($vorschuesse_neu > $vorschuesse_aktuell) {
                    $pdf->ergebnis_tab ["$mieternummer - $empf"] ['VORSCHUSS_NEU'] = "<b>$vorschuesse_neu_a</b>";
                } else {
                    $pdf->ergebnis_tab ["$mieternummer - $empf"] ['VORSCHUSS_NEU'] = "$vorschuesse_neu_a";
                }

                $anp_tab [3] ['KOSTENKAT'] = '';
                $anp_tab [3] ['AKTUELL'] = "";
                $anp_tab [3] ['ANPASSUNG'] = "";
                $anp_tab [3] ['NEU'] = "";

                $anp_tab [4] ['KOSTENKAT'] = '';
                $a_km = nummer_punkt2komma($mk->ausgangs_kaltmiete + $vorschuesse_aktuell + $hk_monatlich_letzte);

                $n_km = nummer_punkt2komma($mk->ausgangs_kaltmiete + $vorschuesse_neu + $hk_vorschuss_neu);

                $anp_tab [4] ['AKTUELL'] = "$a_km €";
                $anp_tab [4] ['ANPASSUNG'] = "<b>Neue Miete</b>";
                $anp_tab [4] ['NEU'] = "<b>$n_km €</b>";
                $pdf->ergebnis_tab ["$mieternummer - $empf"] ['A_MIETE'] = $a_km;
                $pdf->ergebnis_tab ["$mieternummer - $empf"] ['N_MIETE'] = $n_km;

                $cols = array(
                    'KOSTENKAT' => "",
                    "AKTUELL" => "Derzeitige Miete",
                    'ANPASSUNG' => "Anpassungsbetrag",
                    'NEU' => "Neue Miete ab $this->bk_verrechnungs_datum_d"
                );
                $pdf->ezTable($anp_tab, $cols, "", array(
                    'showHeadings' => 1,
                    'shaded' => 1,
                    'titleFontSize' => 7,
                    'fontSize' => 7,
                    'xPos' => 55,
                    'xOrientation' => 'right',
                    'width' => 500,
                    'cols' => array(
                        'AKTUELL' => array(
                            'justification' => 'right',
                            'width' => 100
                        ),
                        'ANPASSUNG' => array(
                            'justification' => 'right',
                            'width' => 100
                        ),
                        'NEU' => array(
                            'justification' => 'right',
                            'width' => 100
                        )
                    )
                ));
                $pdf->ezSetDy(-15);
                $pdf->ezText("Die Anpassung des Heiz- und Betriebskostenvorschusses hat eine vertragsverändernde Wirkung, bedarf aber nicht Ihrer Zustimmung. Sollte Sie bei Ihrer Bank einen Dauerauftrag eingerichtet haben, bitten wir diesen ändern zu lassen. Bei uns vorliegenden Einzugsermächtigung erfolgt automatisch ab $this->bk_verrechnungs_datum_d der Lastschrifteinzug der geänderten Miete. \n\n", 10, array(
                    'justification' => 'full'
                ));
                $pdf->ezSetDy(-15);
                // $pdf->ezText("$this->footer_zahlungshinweis",10);
                // $pdf->ezText("$this->footer_zahlungshinweis", 10, array('justification'=>'full'));
            }

            /* ENDE ANPASSUNGSBLATT */

            /* Anschreiben nur für Mietverträge */
            if ($empf_kos_typ == 'MIETVERTRAG') {
                $mv = new mietvertraege ();
                $mv->get_mietvertrag_infos_aktuell($empf_kos_id);
                /* Wenn Mietvertrag aktuell anpassen, sonst nicht (d.h. Mieter wohnt noch in der Einheit) */
                $this->get_anpassung_details($this->profil_id, 'Nebenkosten Vorauszahlung');
                $p = new partners ();
                $p->get_partner_info(session()->get('partner_id'));

                $pdf->ezNewPage();

                $pdf->addText(480, 697, 8, "$p->partner_ort, $this->bk_berechnungs_datum_d");

                $pap = $mv->postanschrift [0] ['anschrift'];
                if (!empty ($pap)) {
                    $pdf->ezText("$pap", 10);
                } else {
                    $pdf->ezText("$mv->personen_name_string_u\n$mv->haus_strasse $mv->haus_nr\n\n$mv->haus_plz $mv->haus_stadt", 10);
                }
                $pdf->ezSetDy(-60);
                /* Prüfen ob heizkostenabgerechnet worden sind */
                $check_hk = $this->check_hk_abrechnung($empf_kos_typ, $empf_kos_id, $this->bk_jahr);
                if ($check_hk == true) {
                    $hk_summe = $this->summe_hk_abrechnung($empf_kos_typ, $empf_kos_id, $this->bk_jahr);
                    $hk_summe_a = nummer_punkt2komma($hk_summe);
                    $pdf->ezText("<b>Heiz- und Betriebskostenabrechnung Einheit: $mv->einheit_kurzname</b>", 10);
                    $tab_ans [1] ['KOSTENART'] = 'Heizkosten/Warmwasser';
                    if ($hk_summe < 0) {
                        $hk_erg_a = 'Nachzahlung';
                    } else {
                        $hk_erg_a = 'Guthaben';
                    }
                    $tab_ans [1] ['ERGEBNIS'] = $hk_erg_a;
                    $tab_ans [1] ['SUMME'] = $hk_summe_a . ' €';
                } else {
                    $pdf->ezText("<b>Betriebskostenabrechnung $this->bk_jahr Einheit: $mv->einheit_kurzname</b>", 10);
                }

                $pdf->ezText("<b>$this->bk_bezeichnung</b>", 10);
                $pdf->ezText("Wirtschaftseinheit: $this->bk_kos_bez", 10);
                // $pdf->ezText("Einheit: $mv->einheit_kurzname",12);
                $pdf->ezSetDy(-12);
                /* Faltlinie */
                $pdf->setLineStyle(0.2);
                $pdf->line(5, 542, 20, 542);
                $pdf->ezText("$anrede", 12);

                $pdf->ezText("$mv->mv_anrede", 10);
                $pdf->ezText("namens und im Auftrag der Eigentümer erhalten Sie nachfolgend die Betriebs- und Heizkostenabrechnung für das Kalenderjahr $this->bk_jahr mit entsprechenden Erläuterungen zu den einzelnen Abrechnungspositionen und eventuellen Veränderungen zu vorangegangenen Abrechnungen.

		Daraus ergibt sich für Sie folgendes Ergebnis:", 10, array(
                    'justification' => 'full'
                ));

                $tab_ans [0] ['KOSTENART'] = 'Betriebskosten';
                if ($ergebnis < 0) {
                    $bk_ergebnis = 'Nachzahlung';
                }
                if ($ergebnis > 0) {
                    $bk_ergebnis = 'Guthaben';
                }
                if ($ergebnis == 0) {
                    $bk_ergebnis = 'Ergebnis';
                }

                /* Wenn kein Bruttomieter */
                $mkk = new mietkonto ();
                if ($mkk->check_vz_anteilig($empf_kos_id, $monat, $jahr) == true) {
                    $tab_ans [0] ['ERGEBNIS'] = $bk_ergebnis;
                    $ergebnis_a_a = nummer_punkt2komma($ergebnis);
                    $tab_ans [0] ['SUMME'] = $ergebnis_a_a . ' €';
                } else {
                    $ergebnis_a_a = nummer_punkt2komma(0.00);
                    $tab_ans [0] ['SUMME'] = $ergebnis_a_a . ' €';
                    $ergebnis = 0.00;
                }

                $tab_ans [2] ['KOSTENART'] = '';
                $tab_ans [2] ['ERGEBNIS'] = '';
                $tab_ans [2] ['SUMME'] = '';

                $end_erg = $hk_summe + $ergebnis;
                if ($end_erg < 0) {
                    $end_erg_ergebnis = 'Nachzahlung';
                }
                if ($end_erg > 0) {
                    $end_erg_ergebnis = 'Guthaben';
                }
                if ($end_erg == 0) {
                    $end_erg_ergebnis = 'Ergebnis';
                }
                $tab_ans [3] ['KOSTENART'] = '<b>Gesamtergebnis</b>';
                $tab_ans [3] ['ERGEBNIS'] = "<b>$end_erg_ergebnis</b>";
                $tab_ans [3] ['SUMME'] = '<b>' . nummer_punkt2komma($end_erg) . ' €' . '</b>';
                $pdf->ezSetDy(-8);
                $cols = array(
                    'KOSTENART' => "Betriebskostenart",
                    'ERGEBNIS' => "Ergebnis",
                    'SUMME' => "Summe"
                );
                $pdf->ezTable($tab_ans, $cols, "", array(
                    'showHeadings' => 1,
                    'shaded' => 1,
                    'titleFontSize' => 7,
                    'fontSize' => 7,
                    'xPos' => 55,
                    'xOrientation' => 'right',
                    'width' => 500,
                    'cols' => array(
                        'KOSTENART' => array(
                            'justification' => 'left',
                            'width' => 345
                        ),
                        'ERGEBNIS' => array(
                            'justification' => 'left',
                            'width' => 55
                        ),
                        'SUMME' => array(
                            'justification' => 'right',
                            'width' => 100
                        )
                    )
                ));
            }
            $pdf->ezSetDy(-10);
            $pdf->ezText("Die Abrechnungsunterlagen können nach vorheriger Terminabsprache bei uns im Büro eingesehen werden. Eventuelle Einwände gegen die Abrechnung sind bitte innerhalb eines Jahres nach Zugang der Abrechnung schriftlich bei uns anzuzeigen.", 10, array(
                'justification' => 'full'
            ));
            $pdf->ezSetDy(-10);
            $v_monat_arr = explode('-', $this->bk_verrechnungs_datum);

            $v_monat_name = monat2name($v_monat_arr ['1']);
            $v_jahr = $v_monat_arr ['0'];
            $pdf->ezText("Bei Vorlage einer Einzugsermächtigung wird das Guthaben aus der Abrechnung mit der Miete für den Monat $v_monat_name $v_jahr verrechnet. Nachzahlungsbeträge werden mit der Zahlung der Miete für den Monat $v_monat_name $v_jahr mit eingezogen, bitte sorgen Sie für eine ausreichende Kontodeckung bzw. informieren uns unbedingt, falls der Nachzahlungsbetrag nicht per Lastschrift eingezogen werden soll.", 10, array(
                'justification' => 'full'
            ));
            $pdf->ezSetDy(-10);

            if (session()->has('geldkonto_id')) {
                $g = new geldkonto_info ();
                $g->geld_konto_details(session()->get('geldkonto_id'));
            } else {
                throw new \App\Exceptions\MessageException(
                    new \App\Messages\InfoMessage("Bitte Geldkonto auswählen."),
                    0,
                    null,
                    route('legacy::buchen::index', ['option' => 'geldkonto_aendern'])
                );
            }
            $pdf->ezText("Sollte uns keine Einzugsermächtigung vorliegen, bitten wir das Guthaben mit der nächsten Mietzahlung zu\nverrechnen bzw. den Nachzahlungsbetrag unter Angabe Ihrer Mieternummer <b>$mieternummer</b> auf das Konto mit der\n<b>IBAN</b> <b>$g->IBAN1</b> bei der <b>$g->kredit_institut</b> zu überweisen.", 10, array(
                'justification' => 'left'
            ));
            $pdf->ezSetDy(-10);
            $pdf->ezText("Bei verzogenen Mietern ist es uns nicht möglich die Nachzahlungsbeträge per Lastschrift einzuziehen, wir bitten hier um Überweisung auf das o.g. Geldkonto.", 10, array(
                'justification' => 'full'
            ));
            $pdf->ezText("Für die Erstattung eines Guthabens bitten wir Sie uns Ihre aktuelle Kontonummer schriftlich mitzuteilen.", 10);
            $pdf->ezSetDy(-15);
            /* Anschreiben ENDE */
        }
    }

    function check_kw_abrechnung($kos_typ, $kos_id, $jahr)
    {
        $result = DB::select("SELECT * FROM MIETENTWICKLUNG WHERE KOSTENTRAEGER_TYP='$kos_typ' && KOSTENTRAEGER_ID = '$kos_id' && MIETENTWICKLUNG_AKTUELL = '1' && KOSTENKATEGORIE LIKE 'Kaltwasserabrechnung $jahr'");
        return !empty($result);
    }

    function summe_kw_abrechnung($kos_typ, $kos_id, $jahr)
    {
        $result = DB::select("SELECT SUM(BETRAG) AS SUMME  FROM MIETENTWICKLUNG WHERE KOSTENTRAEGER_TYP='$kos_typ' && KOSTENTRAEGER_ID = '$kos_id' && MIETENTWICKLUNG_AKTUELL = '1' && KOSTENKATEGORIE LIKE 'Kaltwasserabrechnung $jahr'");
        if (!empty($result)) {
            return $result[0]['SUMME'];
        }
    }

    function check_hk_abrechnung($kos_typ, $kos_id, $jahr)
    {
        $result = DB::select("SELECT * FROM MIETENTWICKLUNG WHERE KOSTENTRAEGER_TYP='$kos_typ' && KOSTENTRAEGER_ID = '$kos_id' && MIETENTWICKLUNG_AKTUELL = '1' && KOSTENKATEGORIE LIKE 'Heizkostenabrechnung $jahr'");
        return !empty($result);
    }

    function summe_hk_abrechnung($kos_typ, $kos_id, $jahr)
    {
        $result = DB::select("SELECT SUM(BETRAG) AS SUMME  FROM MIETENTWICKLUNG WHERE KOSTENTRAEGER_TYP='$kos_typ' && KOSTENTRAEGER_ID = '$kos_id' && MIETENTWICKLUNG_AKTUELL = '1' && KOSTENKATEGORIE LIKE 'Heizkostenabrechnung $jahr'");
        if (!empty($result)) {
            return $result[0]['SUMME'];
        }
    }

    function check_me($kos_typ, $kos_id, $kategorie, $anfang, $ende)
    {
        $result = DB::select("SELECT * FROM MIETENTWICKLUNG WHERE KOSTENTRAEGER_TYP='$kos_typ' && KOSTENTRAEGER_ID = '$kos_id' && MIETENTWICKLUNG_AKTUELL = '1' && KOSTENKATEGORIE='$kategorie' && ANFANG='$anfang' && ENDE='$ende'");
        return !empty($result);
    }

    /* NEU */

    function wirtschaftseinheiten()
    {
        $f = new formular ();
        $f->erstelle_formular("Alle Wirtschaftseinheiten", NULL);
        $wirt = new wirt_e ();
        $wirt_einheiten_arr = $wirt->get_wirt_e_arr();
        $anzahl = count($wirt_einheiten_arr);
        echo "<table class=\"sortable striped\"><thead>";
        echo "<tr><th>WE</th><th>QM Gesamt</th><th>Gewerbe</th><th>Optionen</th></tr>";
        echo "</thead>";
        $zeile = 0;
        for ($a = 0; $a < $anzahl; $a++) {
            $zeile++;
            $w_id = $wirt_einheiten_arr [$a] ['W_ID'];
            $w_name = $wirt_einheiten_arr [$a] ['W_NAME'];
            $wirt->get_wirt_e_infos($w_id);
            // echo "$w_name (Qm: $wirt->g_qm m² | Gew:$wirt->g_qm_gewerbe m²)<br>";
            $link_hinzu = "<a href='" . route('legacy::bk::index', ['option' => 'wirt_einheiten_hinzu', 'w_id' => $w_id]) . "'>Einheiten bearbeiten</a>";
            $wirt->g_qm_gewerbe_a = nummer_punkt2komma($wirt->g_qm_gewerbe);
            $wirt->g_qm_a = nummer_punkt2komma($wirt->g_qm);
            if ($zeile == '1') {
                echo "<tr class=\"zeile1\"><td>$w_name</td><td>$wirt->g_qm_a</td><td>$wirt->g_qm_gewerbe_a</td><td>$link_hinzu</td></tr>";
            } else {
                echo "<tr class=\"zeile2\"><td>$w_name</td><td>$wirt->g_qm_a</td><td>$wirt->g_qm_gewerbe_a</td><td>$link_hinzu</td></tr>";
                $zeile = 0;
            }
        }
        echo "</table>";
        $f->ende_formular();
    }

    function bk_konto_speichern($profil_id, $kostenkonto, $konto_bez)
    {
        $last_id = $this->last_id('BK_KONTEN', 'BK_K_ID') + 1;
        DB::insert("INSERT INTO BK_KONTEN VALUES (NULL, '$last_id','$kostenkonto', '$konto_bez', '$profil_id','1','0', '1')");
        return $last_id;
    }

    function test_res($profil_id)
    {
        $bk_res_arr = $this->bk_nk_profil_berechnung($profil_id);
        $this->ber_array_anzeigen($bk_res_arr);
    }

    function ber_array_anzeigen($bk_res_arr)
    {
        $this->kontroll_blatt_anzeigen($bk_res_arr);
        echo "<br>";
        $keys = array_keys($bk_res_arr);
        $anzahl_abr = count($keys);

        // print_r($keys);
        echo "BK Abrechnungen: $anzahl_abr<br>";

        for ($a = 1; $a < $anzahl_abr; $a++) {
            $key_val = $keys [$a];
            // echo $key_val.'<br>';
            echo "<table>";
            $empfaenger = $bk_res_arr [$key_val] ['EMPF'];
            $qm_g_objekt = $bk_res_arr [$key_val] ['QM_G_OBJEKT'];
            $qm_g = $bk_res_arr [$key_val] ['QM_G'];
            $qm_g_gewerbe = $bk_res_arr [$key_val] ['QM_G_GEWERBE'];
            $einheit_qm = $bk_res_arr [$key_val] ['EINHEIT_QM'];
            $zeitraum = $bk_res_arr [$key_val] ['ZEITRAUM'];
            $einheit_name = $bk_res_arr [$key_val] ['EINHEIT_NAME'];
            $empf_kos_typ = $bk_res_arr [$key_val] ['KOS_TYP'];
            $empf_kos_id = $bk_res_arr [$key_val] ['KOS_ID'];

            echo "<tr class=\"feldernamen\"><td>Einheit</td><td>Empfänger</td><td>Zeitraum</td></tr>";
            echo "<tr><td>$einheit_name</td><td>$empfaenger</td><td>$zeitraum</td></tr>";
            echo "<tr class=\"feldernamen\"><td>Gesamt qm</td><td>QM Gewerbe</td><td>QM Wohnen</td></tr>";
            echo "<tr><td>$qm_g_objekt m²</td><td>$qm_g_gewerbe m²</td><td>$qm_g m²</td></tr>";
            echo "<tr class=\"feldernamen\"><td>Kostenart</td><td>Gesamt</td><td>Umlegeanteil %</td><td>Um. Betrag</td><td>Gesamt HNDL</td><td>Ihre Wohnfl.</td><td>KEY</td><td>Anteil HNDL</td><td>Anteil / Wohnung</td></tr>";
            $anzahl_zeilen = count($bk_res_arr [$key_val]) - 10;
            // WICHTIG 10 felder abschneiden
            $summe = 0;
            for ($b = 0; $b < $anzahl_zeilen; $b++) {
                $kostenart = $bk_res_arr [$key_val] [$b] ['KOSTENART'];
                $g_betrag = $bk_res_arr [$key_val] [$b] ['G_BETRAG'];
                $anteil = $bk_res_arr [$key_val] [$b] ['ANTEIL'];
                $umlage = $bk_res_arr [$key_val] [$b] ['UMLAGE'];
                $bet_g = $bk_res_arr [$key_val] [$b] ['BET_G'];
                $bet_hndl = $bk_res_arr [$key_val] [$b] ['BET_HNDL'];
                $genkey_id = $bk_res_arr [$key_val] [$b] ['GENKEY_ID'];
                $summe += nummer_komma2punkt($bet_g);
                echo "<tr><td>$kostenart</td><td>$g_betrag</td><td>$anteil</td><td>$umlage</td><td>0.00</td><td>$einheit_qm</td><td>$qm_g/$genkey_id</td><td>$bet_hndl</td><td>$bet_g</td></tr>";
            }

            if ($empf_kos_typ == 'MIETVERTRAG') {
                $mz = new miete ();
                $summe_nebenkosten_jahr = $mz->summe_nebenkosten_im_jahr($empf_kos_typ, $empf_kos_id, $this->bk_jahr);
            } else {
                $summe_nebenkosten_jahr = 0.00;
            }

            $ergebnis = $summe + $summe_nebenkosten_jahr;
            if ($ergebnis < 0) {
                $txt = 'Nachzahlung';
                $ergebnis_a = substr($ergebnis, 1);
            }
            if ($ergebnis > 0) {
                $txt = 'Guthaben';
                $ergebnis_a = $ergebnis;
            }

            if ($ergebnis == null) {
                $txt = 'Ergebnis';
                $ergebnis_a = $ergebnis;
            }

            $vorschuesse_aktuell = nummer_punkt2komma($summe_nebenkosten_jahr / 12);
            $vorschuesse_neu = nummer_punkt2komma($summe / 12);
            if ($vorschuesse_neu < 0) {
                $vorschuesse_neu = substr($vorschuesse_neu, 1);
            }

            $summe_nebenkosten_jahr_a = nummer_punkt2komma($summe_nebenkosten_jahr);

            echo "<tr><td></td><td></td><td></td><td></td><td></td><td></td><td>SUMME KOSTEN</td><td></td><td><b>$summe €</b></td></tr>";
            echo "<tr><td></td><td></td><td></td><td></td><td></td><td></td><td>Ihr Vorschuss Jahr</td><td></td><td><b>$summe_nebenkosten_jahr_a</b></td></tr>";
            echo "<tr><td></td><td></td><td></td><td></td><td></td><td></td><td><b>$txt</b></td><td></td><td><b>$ergebnis_a €</b></td></tr>";
            if ($empf_kos_typ == 'MIETVERTRAG') {
                if ($vorschuesse_aktuell > 0) {
                    $prozent_anp = nummer_punkt2komma(($vorschuesse_neu / ($vorschuesse_aktuell / 100)) - 100);
                    echo "<tr><td></td><td></td><td></td><td></td><td></td><td>ANPASSUNG VORSCHÜSSE</td><td>$vorschuesse_aktuell</td><td>$prozent_anp %</td><td><b>$vorschuesse_neu</b></td></tr>";
                }
            }
            echo "</table><br><br>";
        }
    }

    function kontroll_blatt_anzeigen($bk_res_arr)
    {
        $kontroll_arr = $bk_res_arr ['kontrolle'];
        $anz_konten = count($kontroll_arr);
        echo "<table>";
        echo "<tr class=\"feldernamen\"><td colspan=\"7\">KONTROLLTABELLE</td></tr> ";
        echo "<tr class=\"feldernamen\"><td>Kostenart</td><td>Gesamt</td><td>Aus Teilsummen</td><td>STATUS</td><td>HNDL Gesamt</td><td>HNDL aus Teilsummen</td><td>STATUS HNDL</td></tr> ";
        for ($a = 0; $a < $anz_konten; $a++) {
            $konten = array_keys($kontroll_arr [$a]);
            $bk_k_id = $konten [0];

            $kostenart = $kontroll_arr [$a] [$bk_k_id] ['KOSTENART'];
            $kostenart_summe = $kontroll_arr [$a] [$bk_k_id] ['SUMME'];
            $kostenart_kontrolliert = $kontroll_arr [$a] [$bk_k_id] ['SUMME_K'];
            if (ltrim(rtrim($kostenart_summe)) == ltrim(rtrim($kostenart_kontrolliert))) {
                $status_summe = '<b>OK</b>';
            } else {
                $status_summe = '<b>FALSCH</b>';
            }

            $kostenart_hndl = nummer_punkt2komma($kontroll_arr [$a] [$bk_k_id] ['HNDL']);
            $kostenart_hndl_kontrolliert = nummer_punkt2komma($kontroll_arr [$a] [$bk_k_id] ['HNDL_K']);

            if (ltrim(rtrim($kostenart_hndl)) != ltrim(rtrim($kostenart_hndl_kontrolliert))) {
                $status_hndl = '<b>FALSCH</b>';
            } else {
                $status_hndl = '<b>OK</b>';
            }

            echo "<tr><td>$kostenart</td><td>$kostenart_summe</td><td>$kostenart_kontrolliert</td><td>$status_summe</td><td>$kostenart_hndl</td><td>$kostenart_hndl_kontrolliert</td><td>$status_hndl</td></tr> ";
        } // end for
        echo "</table>";
    }

    function bk_profil_kopieren($profil_id, $bezeichung, $buchungen_kopieren)
    {
        $this->bk_profil_infos($profil_id);
        /* Profil kopieren mit neuer Bezeichnung */
        $this->profil_speichern($bezeichung, $this->bk_kos_id, $this->bk_jahr, $this->bk_berechnungs_datum_d, $this->bk_verrechnungs_datum_d);
        $n_profil_id = session()->get('profil_id');

        /* BK KONTEN holen und kopieren */
        $konten_arr = $this->bk_konten($profil_id);
        $anzahl_konten = count($konten_arr);

        if (!empty($konten_arr)) {
            for ($a = 0; $a < $anzahl_konten; $a++) {
                $konto = $konten_arr [$a] ['KONTO'];
                $bk_k_id = $konten_arr [$a] ['BK_K_ID'];
                $konto_bez = $konten_arr [$a] ['KONTO_BEZ'];
                $genkey_id = $konten_arr [$a] ['GENKEY_ID'];
                $hndl = $konten_arr [$a] ['HNDL'];
                $n_bk_konto_id = $this->bk_konto_speichern_1($n_profil_id, $konto, $konto_bez, $genkey_id, $hndl);

                /* Berechungsbuchungen zum Konto holen */
                if ($buchungen_kopieren == 1) {
                    echo "BUCHUNGEN WERDEN KOPIERT";
                    $buchungen_arr = $this->bk_berechnung_arr($profil_id, $bk_k_id);
                    $anz_buchungen = count($buchungen_arr);
                    if ($anz_buchungen > 0) {
                        for ($b = 0; $b < $anz_buchungen; $b++) {
                            $buchung_id = $buchungen_arr [$b] ['BUCHUNG_ID'];
                            $key_id = $buchungen_arr [$b] ['KEY_ID'];
                            $anteil = $buchungen_arr [$b] ['ANTEIL'];
                            $kos_typ = $buchungen_arr [$b] ['KOSTENTRAEGER_TYP'];
                            $kos_id = $buchungen_arr [$b] ['KOSTENTRAEGER_ID'];
                            $hndl_betrag = $buchungen_arr [$b] ['HNDL_BETRAG'];
                            $this->bk_buchung_speichern($buchung_id, $n_profil_id, $n_bk_konto_id, $key_id, $anteil, $kos_typ, $kos_id, $hndl_betrag);
                            echo "<b>$buchung_id, $n_profil_id, $n_bk_konto_id, $key_id, $anteil, $kos_typ,$kos_id, $hndl_betrag</b><br>";
                        } // end for $b
                    }
                }
            } // end for $a
        } else {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\ErrorMessage('ABBRUCH - KEINE KONTEN DIE MAN KOPIEREN KÖNNTE')
            );
        }
        echo "Kopieren beendet";
    }

    function bk_konto_speichern_1($profil_id, $kostenkonto, $konto_bez, $genkey_id, $hndl)
    {
        $last_id = $this->last_id('BK_KONTEN', 'BK_K_ID') + 1;
        $db_abfrage = "INSERT INTO BK_KONTEN VALUES (NULL, '$last_id','$kostenkonto', '$konto_bez', '$profil_id','$genkey_id','$hndl', '1')";
        DB::insert($db_abfrage);
        return $last_id;
    }

    function bk_buchung_speichern($buchung_id, $profil_id, $bk_konto_id, $key_id, $anteil, $kos_typ, $kos_id, $hndl_betrag)
    {
        $last_bk_be_id = $this->last_id('BK_BERECHNUNG_BUCHUNGEN', 'BK_BE_ID') + 1;
        $abfrage = "INSERT INTO BK_BERECHNUNG_BUCHUNGEN VALUES(NULL, '$last_bk_be_id', '$buchung_id', '$bk_konto_id', '$profil_id','$key_id', '$anteil','$kos_typ', '$kos_id','$hndl_betrag','1')";
        DB::insert($abfrage);
    }

    function form_profil_kopieren()
    {
        $f = new formular ();
        $f->erstelle_formular("BK Profil kopieren", NULL);
        $this->dropdown_profile();
        $f->text_feld("Neue Profilbezeichnung", "profil_bez", "", "50", 'profil_bez', '');
        // $f->check_box_js('buchungen_kopieren', '', 'Alle Buchungen kopieren', '', 'checked');
        $f->hidden_feld("option", "profil_kopieren");
        $f->send_button("submit_prof", "Profil kopieren");
        $f->ende_formular();
    }

    function dropdown_profile()
    {
        $result = DB::select("SELECT BK_ID AS PROFIL_ID, BEZEICHNUNG FROM BK_PROFILE WHERE  AKTUELL='1' ORDER BY BEZEICHNUNG ASC");
        if (!empty($result)) {
            echo "<label for=\"profil_id\">Profil wählen</label><select id=\"profil_id\" name=\"profil_id\" size=\"1\">";
            foreach ($result as $row) {
                $profil_id = $row ['PROFIL_ID'];
                $bez = $row ['BEZEICHNUNG'];
                echo "<option value=\"$profil_id\">$bez</option>";
            }
            echo "</select>";
        }
    }
} // end class BK