<?php

class personal
{
    function form_lohn_gehalt_sepa()
    {
        $monat = date("m");
        $jahr = date("Y");
        $jobs = \App\Models\Job::active()
            ->join('persons', 'persons.id', '=', 'jobs.employee_id')
            ->join('PARTNER_LIEFERANT', function ($join) {
                $join->on('jobs.employer_id', '=', 'PARTNER_LIEFERANT.PARTNER_ID');
                $join->on('PARTNER_LIEFERANT.AKTUELL', '=', \DB::raw('1'));
            })
            ->orderBy('PARTNER_LIEFERANT.PARTNER_NAME')
            ->orderBy('persons.name')
            ->orderBy('persons.first_name')
            ->select('jobs.*')
            ->with(['employee', 'employer', 'employer.bankaccounts'])
            ->get();
        if ($jobs->isEmpty()) {
            fehlermeldung_ausgeben("Keine Benutzer/Mitarbeiter gefunden!");
        } else {
            echo "<table class=\"sortable striped\">";
            $z = 0;
            echo "<thead><th>MITARBEITER</th><th>AG</th><th>SEPA GK</th><th>BETRAG</th><th>VZWECK</th><th>KONTO</th><th>OPTION</th></thead>";
            foreach ($jobs as $job) {
                $z++;
                $bankaccount = $job->employer->bankaccounts->first();
                $employee = $job->employee;
                $b_id = $employee->id;
                $b_name_g = $employee->full_name;
                $b_name = $employee->full_name;
                if (!$this->check_datensatz_sepa($bankaccount->KONTO_ID, "Gehalt $monat/$jahr, $b_name_g", 'Person', $b_id, 4000)) {
                    echo "<tr class=\"zeile$z\">";
                    echo "<td><form id=\"sepa_lg_$index\"></form>$b_name_g</td><td>" . $job->employer->PARTNER_NAME . "</td>";
                    $sep = new sepa ();
                    echo "<td>";
                    if ($sep->dropdown_sepa_geldkonten('Überweisen an', 'empf_sepa_gk_id', "empf_sepa_gk_id", 'Person', $b_id, "sepa_lg_" . $index)) {
                        echo "</td>";
                        echo "<td>";
                        $lohn = $this->get_mitarbeiter_summe($bankaccount->KONTO_ID, 4000, $b_name, 'Person', $job->employee->id);
                        $js_action = "onfocus=\"this.value='';\"";
                        $lohn_a = nummer_punkt2komma($lohn * -1);
                        echo "<div class=\"input-field\">";
                        echo "<input type=\"text\" id=\"betrag\" name=\"betrag\" value=\"$lohn_a\" size=\"10\" $js_action form=\"sepa_lg_$index\">\n";
                        echo "<label for=\"$id\">Betrag</label>\n";
                        echo "</div>";
                        echo "</td>";
                        echo "<td>";

                        echo "<div class=\"input-field\">";
                        echo "<input type=\"text\" id=\"vzweck\" name=\"vzweck\" value=\"Gehalt $monat/$jahr, $b_name_g\" size=\"25\" form=\"sepa_lg_$index\">\n";
                        echo "<label for=\"$id\">VERWENDUNG</label>\n";
                        echo "</div>";
                        echo "</td>";
                        echo "<td>";

                        echo "<input type=\"hidden\" id=\"option\" name=\"option\" value=\"sepa_sammler_hinzu\" form=\"sepa_lg_$index\">\n";
                        echo "<input type=\"hidden\" id=\"kat\" name=\"kat\" value=\"LOHN\" form=\"sepa_lg_$index\">\n";
                        echo "<input type=\"hidden\" id=\"gk_id\" name=\"gk_id\" value=\"" . $bankaccount->KONTO_ID . "\" form=\"sepa_lg_$index\">\n";
                        echo "<input type=\"hidden\" id=\"kos_typ\" name=\"kos_typ\" value=\"Person\" form=\"sepa_lg_$index\">\n";
                        echo "<input type=\"hidden\" id=\"kos_id\" name=\"kos_id\" value=\"$b_id\" form=\"sepa_lg_$index\">\n";
                        $kk = new kontenrahmen ();
                        $kk->dropdown_kontorahmenkonten_vorwahl('Buchungskonto', 'konto', 'konto', 'GELDKONTO', $bankaccount->KONTO_ID, '', 4000, "sepa_lg_" . $index);
                        echo "</td>";
                        echo "<td>";
                        echo "<button type=\"submit\" name=\"btn_Sepa\" value=\"Ü-SEPA\" class=\"btn waves-effect waves-light\" id=\"btn_Sepa\" form=\"sepa_lg_$index\"><i class=\"mdi mdi-send right\"></i>Ü-SEPA</button>";
                    }
                    echo "</td>";
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
        $result = DB::select("SELECT BETRAG FROM GELD_KONTO_BUCHUNGEN WHERE GELDKONTO_ID='$gk_id' && `AKTUELL` = '1' && VERWENDUNGSZWECK LIKE '% Gehalt %' && KOSTENTRAEGER_TYP='$kos_typ' && KOSTENTRAEGER_ID='$kos_id' && KONTENRAHMEN_KONTO='$konto' ORDER BY DATUM DESC LIMIT 0,1");
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
