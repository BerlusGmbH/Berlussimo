<?php

class personal
{
    function form_lohn_gehalt_sepa($p_id = null)
    {
        $monat = date("m");
        $jahr = date("Y");
        $b = new benutzer ();
        $b = $b->get_all_users_arr2(0); // 1 für alle, 0 für aktuelle
        if ($b->isEmpty()) {
            fehlermeldung_ausgeben("Keine Benutzer/Mitarbeiter gefunden!");
        } else {
            $f = new formular ();

            echo "<table class=\"sortable\">";
            $z = 0;
            echo "<tr><th>MITARBEITER</th><th>AG</th><th>SEPA GK</th><th>BETRAG</th><th>VZWECK</th><th>KONTO</th><th>OPTION</th></tr>";
            foreach ($b as $benutzer) {
                $z++;
                $b_id = $benutzer->id;
                $b_name_g = strtoupper($benutzer->name);
                $b_name = $benutzer->name;
                $b_eintritt = $benutzer->join_date;
                $b_austritt = $benutzer->leave_date;
                $ze = new zeiterfassung ();
                $partner_id = $ze->get_partner_id_benutzer($b_id);
                if ($partner_id) {
                    $p = new partners ();
                    $p->get_partner_name($partner_id);
                }
                if (!$this->check_datensatz_sepa(session()->get('geldkonto_id'), "Lohn $monat/$jahr, $b_name_g", 'Benutzer', $b_id, 4000)) {
                    echo "<tr class=\"zeile$z\"><td>$b_name_g</td><td>$p->partner_name</td>";
                    $sep = new sepa ();
                    echo "<form name=\"sepa_lg\" method=\"post\" action=\"\">";
                    echo "<td>";
                    if ($sep->dropdown_sepa_geldkonten('Überweisen an', 'empf_sepa_gk_id', 'empf_sepa_gk_id', 'Benutzer', $b_id) == true) {
                        echo "</td>";
                        echo "<td>";
                        $lohn = $this->get_mitarbeiter_summe(session()->get('geldkonto_id'), 4000, $b_name);
                        $js_action = "onfocus=\"this.value='';\"";
                        $lohn_a = nummer_punkt2komma($lohn * -1);
                        $f->text_feld('Betrag', 'betrag', $lohn_a, 10, 'betrag', $js_action);
                        echo "</td>";
                        echo "<td>";

                        $f->text_feld('VERWENDUNG', 'vzweck', "Lohn $monat/$jahr, $b_name_g", 25, 'vzweck', '');
                        echo "</td>";
                        // echo nummer_punkt2komma($lohn*-1);
                        // $f->hidden_feld('option', 'lohn2sepa');
                        echo "</td>";
                        echo "<td>";

                        $f->hidden_feld('option', 'sepa_sammler_hinzu');
                        $f->hidden_feld('kat', 'LOHN');
                        $f->hidden_feld('gk_id', session()->get('geldkonto_id'));
                        $f->hidden_feld('kos_typ', 'Benutzer');
                        $f->hidden_feld('kos_id', $b_id);
                        // $f->text_feld('Buchungskonto', 'konto', 4000, 5, 'konto', '');
                        $kk = new kontenrahmen ();
                        $kk->dropdown_kontorahmenkonten_vorwahl('Buchungskonto', 'konto', 'konto', 'GELDKONTO', session()->get('geldkonto_id'), '', 4000);
                        echo "</td>";
                        echo "<td>";
                        $f->send_button('btn_Sepa', 'Ü-SEPA');
                        echo "</td>";
                        $f->ende_formular();
                        echo "</td>";
                    }
                    echo "</tr>";
                }
                if ($z == 2) {
                    $z = 0;
                }
            }
            echo "</table>";
        }
    }

    function check_datensatz_sepa($gk_id, $vzweck, $kos_typ, $kos_id, $konto)
    {
        $result = DB::select("SELECT * FROM SEPA_UEBERWEISUNG WHERE FILE IS NULL && GK_ID_AUFTRAG ='$gk_id' && `AKTUELL` = '1' && VZWECK = '$vzweck' && KONTO='$konto' && KOS_TYP='$kos_typ' && KOS_ID='$kos_id' LIMIT 0,1");
        return !empty($result);
    }

    function get_mitarbeiter_summe($gk_id, $konto, $benutzername, $kos_typ = null, $kos_id = null)
    {
        $result = DB::select("SELECT BETRAG FROM GELD_KONTO_BUCHUNGEN WHERE GELDKONTO_ID='$gk_id' && `AKTUELL` = '1' && VERWENDUNGSZWECK LIKE '%$benutzername%' && KONTENRAHMEN_KONTO='$konto' ORDER BY G_BUCHUNGSNUMMER DESC LIMIT 0,1");
        $row = $result[0];
        return $row ['BETRAG'];
    }

    function form_krankenkassen()
    {
        if (!session()->has('geldkonto_id')) {
            fehlermeldung_ausgeben("Geldkonto wählen");
            return;
        } else {
            if (!session()->has('partner_id')) {
                fehlermeldung_ausgeben("Partner wählen!");
                return;
            }
            $gk = new geldkonto_info ();
            $gk->geld_konto_details(session()->get('geldkonto_id'));
            $monat = date("m");
            $jahr = date("Y");
            $sep = new sepa ();
            $f = new formular ();
            $f->erstelle_formular('SEPA-Krankenkassenbeiträge', null);
            $sep->dropdown_sepa_geldkonten_filter('Krankenkasse wählen', 'empf_sepa_gk_id', 'empf_sepa_gk_id', 'krankenkasse');
            $f->text_feld('Betrag', 'betrag', "", 10, 'betrag', '');
            $f->text_feld('VERWENDUNG', 'vzweck', "$gk->beguenstigter Beitrag $monat/$jahr Betriebsnummer ", 80, 'vzweck', '');
            $f->hidden_feld('option', 'sepa_sammler_hinzu');
            $f->hidden_feld('kat', 'KK');
            $f->hidden_feld('gk_id', session()->get('geldkonto_id'));
            $f->hidden_feld('kos_typ', 'Partner');
            $f->hidden_feld('kos_id', session()->get('partner_id'));
            $kk = new kontenrahmen ();
            $kk->dropdown_kontorahmenkonten_vorwahl('Buchungskonto', 'konto', 'konto', 'Partner', session()->get('partner_id'), '', 4001);
            $f->send_button('btn_Sepa', 'Zum Sammler hinzufügen');
            $f->ende_formular();
        }
    }

    function form_finanzamt()
    {
        if (!session()->has('geldkonto_id')) {
            fehlermeldung_ausgeben("Geldkonto wählen");
            return;
        } else {
            if (!session()->has('partner_id')) {
                fehlermeldung_ausgeben("Partner wählen!");
                return;
            }
            $gk = new geldkonto_info ();
            $gk->geld_konto_details(session()->get('geldkonto_id'));
            $monat = date("m");
            $jahr = date("Y");
            $sep = new sepa ();
            $f = new formular ();
            $f->erstelle_formular('SEPA-Finanzamt', null);
            $sep->dropdown_sepa_geldkonten_filter('Finanzamt wählen', 'empf_sepa_gk_id', 'empf_sepa_gk_id', 'amt');
            $f->text_feld('Betrag', 'betrag', "", 10, 'betrag', '');
            $f->text_feld('VERWENDUNG', 'vzweck', "$gk->beguenstigter Steuer $monat/$jahr", 80, 'vzweck', '');
            $f->hidden_feld('option', 'sepa_sammler_hinzu');
            $f->hidden_feld('kat', 'STEUERN');
            $f->hidden_feld('gk_id', session()->get('geldkonto_id'));
            $f->hidden_feld('kos_typ', 'Partner');
            $f->hidden_feld('kos_id', session()->get('partner_id'));
            $kk = new kontenrahmen ();
            $kk->dropdown_kontorahmenkonten_vorwahl('Buchungskonto', 'konto', 'konto', 'Partner', session()->get('partner_id'), '', 1000);
            $f->send_button('btn_Sepa', 'Zum Sammler hinzufügen');
            $f->ende_formular();
        }
    }
} // end class
