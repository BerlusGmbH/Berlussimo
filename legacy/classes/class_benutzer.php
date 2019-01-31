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