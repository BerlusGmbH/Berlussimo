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
        $user = \App\Models\Person::where('name', $benutzername)->has('jobsAsEmployee')->first();
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
                $link_details = "<a href='" . route('web::details::legacy', ['option' => 'details_anzeigen', 'detail_tabelle' => 'Person', 'detail_id' => $b_id]) . "'>Details</a>";
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
        $users = \App\Models\Person::has('jobsAsEmployee')->defaultOrder()->get();
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
        $f->passwort_feld("Neues Passwort", "passwort", "", "20", 'passwort', '');
        $p = new partners ();
        $p->partner_dropdown('Mitarbeiter von', 'partner_id', 'partner_id', $partner_id);
        $p->gewerke_dropdown('Gewerk/Abteilung', 'gewerk_id', 'gewerk_id', $this->gewerk_id);
        $f->datum_feld("Geb. am", "geburtstag", date_mysql2german($this->geb_datum), 'geburtstag');
        $f->datum_feld("Eintritt", "eintritt", date_mysql2german($this->datum_eintritt), 'eintritt');
        $f->datum_feld("Austritt", "austritt", date_mysql2german($this->datum_austritt), 'austritt');
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
        $b = \App\Models\Person::with(['jobsAsEmployee', 'emails'])->findOrFail($b_id);;
        if (isset($b)) {
            $this->benutzername = $b->full_name;
            $this->benutzer_id = $b->id;
            $this->geb_datum = $b->birthday;

            if (isset($b->jobsAsEmployee[0])) {
                $job = $b->jobsAsEmployee[0];
                $this->stundensatz = $job->hourly_rate;
                $this->gewerk_id = $job->job_title_id;
                $this->datum_eintritt = $job->join_date;
                $this->datum_austritt = $job->leave_date;
                $this->urlaub = $job->holidays;
                $this->stunden_wo = $job->hours_per_week;
            }

            if (isset($b->emails[0])) {
                $this->benutzer_email = $b->emails[0];
            }
        }
    }


    /**
     * @param int $alle
     * @return array|\Illuminate\Database\Eloquent\Collection|static[]
     */
    function get_all_users_arr2($alle = 1)
    {
        $users = \App\Models\Person::defaultOrder();
        if ($alle != 1) {
            $users->whereHas('jobsAsEmployee', function ($query) {
                $query->active();
            });
        } else {
            $users->has('jobsAsEmployee');
        }
        return $users->get();
    }

    function dropdown_benutzer2($label, $name, $id, $js)
    {
        $users = $this->get_all_users_arr();
        if (!$users->isEmpty()) {
            echo "<label for=\"$id\">$label</label><select id=\"$id\" name=\"$name\" size=\"1\" $js>";
            echo "<option value=\"Alle\" selected>Alle</option>";
            foreach ($users as $user) {
                $benutzername = $user->name;
                $benutzer_id = $user->id;
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
        if(!empty($passwort)) {
            $user->password = $passwort;
        }
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