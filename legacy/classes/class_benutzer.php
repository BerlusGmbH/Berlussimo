<?php

class benutzer
{
    public $benutzername;
    public $passwort;
    public $gewerk_id;
    public $geb_datum;
    public $datum_eintritt;
    public $datum_austritt;
    public $urlaub;
    public $stunden_wo;
    public $stundensatz;
    public $benutzer_id;
    public $id;
    public $benutzer_email;

    function get_benutzer_id($benutzername)
    {
        $user = \App\Models\User::where('name', $benutzername)->first();
        return $user == null ? null : $user->id;
    }

    function benutzer_anzeigen()
    {
        $users = $this->get_all_users_arr();
        if (!$users->isEmpty()) {
            echo "<table class=\"sortable striped\">";
            echo "<thead>";
            echo "<tr>";
            echo "<th>Benutzername</th>";
            echo "<th>Geburtstag</th>";
            echo "<th>Eintritt</th>";
            echo "<th>Firma</th>";
            echo "<th>Option</th>";
            echo "</tr>";
            echo "</thead>";
            $z = 0;
            foreach ($users as $user) {
                $z++;
                $benutzername = $user->name;
                $b_id = $user->id;
                $geb_dat = date_mysql2german($user->birthday);
                $geb_dat_arr = explode('.', $geb_dat);
                $geb_t = $geb_dat_arr [0];
                $geb_m = $geb_dat_arr [1];
                $geb_j = $geb_dat_arr [2];
                $eintritt = date_mysql2german($user->join_date);
                $ein_dat_arr = explode('.', $eintritt);
                $ein_t = $ein_dat_arr [0];
                $ein_m = $ein_dat_arr [1];
                $ein_j = $ein_dat_arr [2];
                $ze = new zeiterfassung ();
                $partner_id = $ze->get_partner_id_benutzer($b_id);
                if ($partner_id) {
                    $p = new partners ();
                    $p->get_partner_name($partner_id);
                }
                $link_ber = "<a href='" . route('web::benutzer::legacy', ['option' => 'berechtigungen', 'b_id' => $b_id]) . "'>Berechtigungen</a>";
                $link_aendern = "<a href='" . route('web::benutzer::legacy', ['option' => 'aendern', 'b_id' => $b_id]) . "'>Ändern</a>";
                $link_details = "<a href='" . route('web::details::legacy', ['option' => 'details_anzeigen', 'detail_tabelle' => 'BENUTZER', 'detail_id' => $b_id]) . "'>Details</a>";
                echo "<tr class=\"zeile$z\"><td>$benutzername</td><td sorttable_customkey=\"$geb_j$geb_m$geb_t\">$geb_dat</td><td sorttable_customkey=\"$ein_j$ein_m$ein_t\">$eintritt</td><td>$p->partner_name</td><td>$link_ber $link_aendern $link_details</td></tr>";

                if ($z == 2) {
                    $z = 0;
                }
            }
            echo "</table>";
        } else {
            echo "Keine Benutzer in Berlussimo";
        }
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    function get_all_users_arr()
    {
        $users = \App\Models\User::orderBy('name', 'asc')->get();
        return $users;
    }

    function form_benutzer_aendern($b_id)
    {
        $this->get_benutzer_infos($b_id);
        $z = new zeiterfassung ();
        $partner_id = $z->get_partner_id_benutzer($b_id);

        $f = new formular ();
        $f->erstelle_formular("Benutzerdaten von Benutzer $this->benutzername ändern", NULL);
        $f->text_feld("Benutzername", "benutzername", "$this->benutzername", "20", 'benutzername', '');
        $f->passwort_feld("Passwort", "passwort", "$this->passwort", "20", 'passwort', '');
        $p = new partners ();
        $p->partner_dropdown('Mitarbeiter von', 'partner_id', 'partner_id', $partner_id);
        $p->gewerke_dropdown('Gewerk/Abteilung', 'gewerk_id', 'gewerk_id', $this->gewerk_id);
        $f->datum_feld("Geb. am", "geburtstag", date_mysql2german($this->geb_datum), "10", 'geburtstag', '');
        $f->datum_feld("Eintritt", "eintritt", date_mysql2german($this->datum_eintritt), "10", 'eintritt', '');
        $f->datum_feld("Austritt", "austritt", date_mysql2german($this->datum_austritt), "10", 'austritt', '');
        $f->text_feld("urlaubstage im Jahr", "urlaub", "$this->urlaub", "5", 'urlaub', '');
        $f->text_feld("Stunden/Wochen", "stunden_pw", nummer_punkt2komma($this->stunden_wo), "5", 'stunden_pw', '');
        $f->text_feld("Stundensatz", "stundensatz", nummer_punkt2komma($this->stundensatz), "5", 'stundensatz', '');
        $f->hidden_feld("b_id", "$b_id");
        $f->hidden_feld("option", "benutzer_aendern_send");
        $f->send_button("submit_bae", "Änderungen speichern");
        $f->ende_formular();
    }

    function get_benutzer_infos($b_id)
    {
        $b = $this->get_user_info($b_id);
        if (isset($b)) {
            $this->benutzername = $b['name'];
            $this->benutzer_id = $b['id'];
            $this->passwort = $b['password'];

            $this->stundensatz = $b['hourly_rate'];
            $this->geb_datum = $b['birthday'];
            $this->gewerk_id = $b['trade_id'];
            $this->datum_eintritt = $b['join_date'];
            $this->datum_austritt = $b['leave_date'];

            $this->urlaub = $b['holidays'];
            $this->stunden_wo = $b['hours_per_week'];
            $this->benutzer_email = $b['email'];
        }
    }

    function get_user_info($b_id)
    {
        $result = DB::select("SELECT * FROM users JOIN BENUTZER_PARTNER ON (users.id=BP_BENUTZER_ID)  WHERE users.id=? GROUP BY users.id  ORDER BY `BP_PARTNER_ID`, trade_id, users.name ASC LIMIT 0,1", [$b_id]);

        if (!empty($result)) {
            return $result[0];
        } else {
            $result = DB::select("SELECT * FROM users WHERE users.id=? LIMIT 0,1", [$b_id]);
            if (!empty($result)) {
                return $result[0];
            }
        }
        return null;
    }

    function berechtigungen($b_id)
    {
        $z = new zeiterfassung ();
        $benutzername = $z->get_benutzer_name($b_id);
        $ber_arr = $this->berechtigungen_arr($b_id);
        $anz = count($ber_arr);
        if ($anz) {
            echo "<table class='striped'><thead>";
            echo "<tr><th>Benutzer</th><th>$benutzername</th></tr>";
            echo "<tr><th>Zeile</th><th>Modulzugriff</th></tr></thead>";
            for ($a = 0; $a < $anz; $a++) {
                $zeile = $a + 1;
                $modul_name = $ber_arr [$a] ['MODUL_NAME'];

                if ($modul_name == '*') {
                    $modul_name = 'Vollzugriff';
                }
                echo "<tr><td>$zeile</td><td>$modul_name</td></tr>";
            }
            echo "</table>";
        } else {
            echo "Keine Berechtigungen";
        }
        echo "<br><hr>";
        $this->form_mberechtigungen_setzen($b_id);
    }

    function berechtigungen_arr($b_id)
    {
        $db_abfrage = "SELECT * FROM BENUTZER_MODULE WHERE AKTUELL='1' && BENUTZER_ID='$b_id' ORDER BY MODUL_NAME ASC";
        $result = DB::select($db_abfrage);
        return $result;
    }

    function form_mberechtigungen_setzen($b_id)
    {
        $z = new zeiterfassung ();
        $benutzername = $z->get_benutzer_name($b_id);
        $f = new formular ();
        $f->erstelle_formular("Zugriffsberechtigung für Benutzer $benutzername", NULL);
        $f->hidden_feld("b_id", "$b_id");
        $this->checkboxen_anzeigen($b_id);
        $f->hidden_feld("option", "zugriff_send");
        echo "<div class='input-field'>";
        $f->send_button("submit_ja", "Gewaehren");
        echo "</div>";
        $f->ende_formular();
    }

    function checkboxen_anzeigen($b_id)
    {
        $module_arr = $this->module_arr();
        $anz = count($module_arr);
        if ($anz) {
            echo "<div class='row'>";
            echo "<div class='input-field col s12 m4 l2'>";
            echo "<input type=\"checkbox\" name=\"modul_name[]\" value=\"*\" id='all'/>";
            echo "<label for=\"all\">Vollzugriff</label>";
            echo "</div>";

            $z = 1;
            for ($a = 0; $a < $anz; $a++) {
                $z++;
                $modul_name = $module_arr [$a];
                $modul_name_a = ' &nbsp ' . strtoupper($modul_name) . ' &nbsp ';

                echo "<div class='input-field col s12 m4 l2'>";
                if (check_user_mod($b_id, $modul_name)) {
                    echo "<input type=\"checkbox\" name=\"modul_name[]\" value=\"$modul_name\" id=\"$modul_name\" checked />";
                } else {
                    echo "<input type=\"checkbox\" name=\"modul_name[]\" value=\"$modul_name\" id=\"$modul_name\"/>";
                }
                echo "<label for=\"$modul_name\">$modul_name_a</label>";
                echo "</div>";
            }
            echo "</div>";
        } else {
            echo "Keine Module";
        }
    }

    function module_arr()
    {
        $casedir = dir(base_path("legacy/options/case"));
        while ($func = $casedir->read()) {

            if (substr($func, 0, 5) == "case.") {
                // echo $casedir->path."/".$func.'<br>';
                // $content1 = file_get_contents($casedir->path."/".$func);
                $dateiname = "$casedir->path/$func";
                // echo "$dateiname<br>";
                $dat_inhalt = file($dateiname);
                // echo '<pre>';
                // print_r($dat_inhalt);
                $inhalt = '';
                foreach ($dat_inhalt as $value) {
                    $inhalt .= $value . '<br>';
                }
                $content = $inhalt;
                $finden = 'case "';
                $pos = strpos($content, $finden);
                // echo $content;
                if ($pos == true) {
                    $pos_ap1 = strpos($content, '"', $pos);
                    if ($pos_ap1 == true) {
                        $pos_ap2 = strpos($content, '"', $pos_ap1 + 1);
                        if ($pos_ap2 == true) {
                            $laenge = $pos_ap2 - $pos_ap1;
                            $module_name = substr($content, $pos_ap1 + 1, $laenge - 1);
                            $module_arr [] = ltrim(rtrim($module_name));
                            // echo $module_name.'|';
                        } else {
                        }
                    }
                }
            }
        }
        closedir($casedir->handle);
        sort($module_arr);
        // print_r($module_arr);
        return $module_arr;
    }

    function dropdown_benutzer($vorwahl = null, $alle = 0)
    {
        $b = $this->get_all_users_arr2($alle);
        if (!$b->isEmpty()) {
            echo "<label for=\"benutzer_id\">Mitarbeiter wählen</label><select id=\"benutzer_id\" name=\"benutzer_id\" size=\"1\">";
            foreach ($b as $user) {
                $benutzername = $user->name;
                $benutzer_id = $user->id;
                if ($vorwahl != null) {
                    if ($benutzername == $vorwahl) {
                        echo "<option value=\"$benutzer_id\" selected>$benutzername</option>";
                    } else {
                        echo "<option value=\"$benutzer_id\">$benutzername</option>";
                    }
                } else {
                    if (Auth::user()->id == $benutzer_id) {
                        echo "<option value=\"$benutzer_id\" selected>$benutzername</option>";
                    } else {
                        echo "<option value=\"$benutzer_id\">$benutzername</option>";
                    }
                }
                // }
            }
            echo "</select>";
        } else {
            echo "Keine Mitarbeiter, bitte mitarbeiter unter Menüpunkt -> Benutzer anlegen";
        }
    }


    /**
     * @param int $alle
     * @return array|\Illuminate\Database\Eloquent\Collection|static[]
     */
    function get_all_users_arr2($alle = 1)
    {
        $users = \App\Models\User::orderBy('name', 'asc');
        if ($alle != 1) {
            $heute = date("Y-m-d");
            $users = $users->where('leave_date', '>', $heute)->orWhereNull('leave_date');
        }
        return $users->get();
    }

    function dropdown_benutzer2($label, $name, $id, $js)
    {
        $b = $this->get_all_users_arr();
        if (!$b->isEmpty()) {
            echo "<label for=\"$id\">$label</label><select id=\"$id\" name=\"$name\" size=\"1\" $js>";
            echo "<option value=\"Alle\" selected>Alle</option>";
            foreach ($b as $benutzer) {
                $benutzername = $benutzer->name;
                $benutzer_id = $benutzer->id;
                if (Auth::user()->id == $benutzer_id) {
                    echo "<option value=\"$benutzer_id\" selected>$benutzername</option>";
                } else {
                    echo "<option value=\"$benutzer_id\">$benutzername</option>";
                }
            }
            echo "</select>";
        } else {
            echo "Keine Mitarbeiter, bitte mitarbeiter unter Menüpunkt -> Benutzer anlegen";
        }
    }

    function form_neuer_benutzer()
    {
        $f = new formular ();
        $f->erstelle_formular("Neuen Benutzer/Mitarbeiter anlegen", NULL);
        $f->text_feld("Benutzername", "benutzername", "", "20", 'benutzername', '');
        $f->text_feld("Passwort", "passwort", "", "20", 'passwort', '');
        $p = new partners ();
        $p->partner_dropdown('Mitarbeiter von', 'partner_id', 'partner_id');
        $p->gewerke_dropdown('Gewerk/Abteilung', 'gewerk_id', 'gewerk_id');
        // $f->datum_feld("Datum:", "datum", "", "10", 'datum','');
        $f->datum_feld("Geb. am", "geburtstag", "", "10", 'geburtstag', '');
        $f->datum_feld("Eintritt", "eintritt", "", "10", 'eintritt', '');
        $f->datum_feld("Austritt", "austritt", "", "10", 'austritt', '');
        $f->text_feld("urlaubstage im Jahr", "urlaub", "", "5", 'urlaub', '');
        $f->text_feld("Stunden/Wochen", "stunden_pw", "", "5", 'stunden_pw', '');
        $f->text_feld("Stundensatz", "stundensatz", "", "5", 'stundensatz', '');
        $f->hidden_feld("option", "benutzer_send");
        $f->send_button("submit_nb", "Benutzer speichern");
        $f->ende_formular();
    }

    function benutzer_speichern($benutzername, $passwort, $partner_id, $stundensatz, $geb_dat, $gewerk_id, $eintritt, $austritt, $urlaub, $stunden_pw)
    {
        $user = \App\Models\User::create(['name' => $benutzername,
                'email' => \Illuminate\Support\Str::lower($benutzername) . '@berlussimo',
                'hourly_rate' => $stundensatz, 'birthday' => $geb_dat,
                'trade_id' => $gewerk_id, 'join_date' => $eintritt,
                'leave_date' => $austritt, 'holidays' => $urlaub,
                'hours_per_week' => $stunden_pw]
        );

        $user->password = Hash::make($passwort);
        $user->api_token = str_random(60);
        $user->save();

        DB::insert("INSERT INTO BENUTZER_PARTNER VALUES (NULL, ?, ?, '1')", [$user->id, $partner_id]);
        /* Benutzer ID zurückgeben */
        return $user->id;
    }

    function benutzer_aenderungen_speichern($b_id, $benutzername, $passwort, $partner_id, $stundensatz, $geb_dat, $gewerk_id, $eintritt, $austritt, $urlaub, $stunden_pw)
    {
        $user = \App\Models\User::findOrFail($b_id);

        $geb_dat = date_german2mysql($geb_dat);
        $eintritt = date_german2mysql($eintritt);
        $austritt = date_german2mysql($austritt);

        /* Updaten */
        $user->name = $benutzername;
        $user->password = $passwort;
        $user->hourly_rate = $stundensatz;
        $user->birthday = $geb_dat;
        $user->trade_id = $gewerk_id;
        $user->join_date = $eintritt;
        $user->leave_date = $austritt;
        $user->holidays = $urlaub;
        $user->hours_per_week = $stunden_pw;

        $user->save();

        DB::update("UPDATE BENUTZER_PARTNER SET BP_PARTNER_ID=? WHERE BP_BENUTZER_ID=?", [$partner_id, $b_id]);
    }

    function berechtigungen_speichern($b_id, $modul_name)
    {
        if (is_array($modul_name)) {

            DB::delete("DELETE FROM BENUTZER_MODULE WHERE BENUTZER_ID='$b_id'");

            if (in_array('*', $modul_name)) {
                DB::insert("INSERT INTO BENUTZER_MODULE VALUES(NULL,'0', '$b_id', '*', '1')");
            } else {
                $anz = count($modul_name);
                for ($a = 0; $a < $anz; $a++) {
                    $mod = $modul_name [$a];
                    DB::insert("INSERT INTO BENUTZER_MODULE VALUES(NULL,'0', '$b_id', '$mod', '1')");
                }
            }
        } else {
            /* Dropdown auswahl */
            if ($modul_name == '*') {
                // erst bisherige Module löschen
                DB::delete("DELETE  FROM BENUTZER_MODULE WHERE BENUTZER_ID='$b_id'");
            }
            DB::insert("INSERT INTO BENUTZER_MODULE VALUES(NULL,'0', '$b_id', '$modul_name', '1')");
        }
    }

    function berechtigungen_entziehen($b_id, $modul_name)
    {
        DB::delete("DELETE FROM BENUTZER_MODULE WHERE BENUTZER_ID='$b_id' && MODUL_NAME='$modul_name'");
    }

    function dropdown_gewerke($label, $name, $id, $js)
    {
        $result = DB::select("SELECT G_ID, BEZEICHNUNG FROM GEWERKE WHERE AKTUELL='1' ORDER BY BEZEICHNUNG ASC");
        if (!empty($result)) {
            echo "<label for=\"$id\">$label</label><select id=\"$id\" name=\"$name\" $js>";
            echo "<option value=\"Alle\">Alle</option>";
            foreach($result as $row) {
                $gid = $row ['G_ID'];
                $bez = $row ['BEZEICHNUNG'];
                echo "<option value=\"$gid\">$bez</option>";
            }
            echo "</select>";
        }
    }
} // Ende Klasse