<?php

class personen
{
    public $person_nachname;
    public $person_geburtstag;
    public $geschlecht;
    public $person_vorname;
    public $person_anzahl_mietvertraege;
    public $p_mv_ids;
    public $anschrift;
    public $zustellanschrift;

    function form_person_erfassen()
    {
        $f = new formular ();
        $f->erstelle_formular('Personen', '');
        $f->fieldset("Neue Person erfassen", 'p_erfassen');
        $f->text_feld("Nachname", "nachname", "", "35", 'nachname', '');
        $f->text_feld("Vorname", "vorname", "", "35", 'vorname', '');
        $f->datum_feld('Geburtsdatum', 'geburtsdatum', '', 'geburtsdatum');
        $this->dropdown_geschlecht('Geschlecht wählen', 'geschlecht', 'geschlecht');
        $f->text_feld("Telefon", "telefon", "", "20", 'telefon', '');
        $f->text_feld("Handy", "handy", "", "20", 'handy', '');
        $f->text_feld("Email", "email", "", "20", 'email', '');
        $f->send_button("submit", "Speichern");
        $f->hidden_feld("anzeigen", "person_erfassen_check");
        $f->fieldset_ende();
        $f->ende_formular();
    }

    function dropdown_geschlecht($beschreibung, $id, $name, $vorwahl = null)
    {
        echo "<label for=\"$id\">$beschreibung</label>";
        echo "<select name=\"$name\" id=\"$id\">";
        if ($vorwahl == 'weiblich') {
            echo "<option value=\"weiblich\" selected>weiblich</option>";
            echo "<option value=\"männlich\">männlich</option>";
        } else {
            echo "<option value=\"weiblich\">weiblich</option>";
            echo "<option value=\"männlich\" selected>männlich</option>";
        }

        echo "</select>";
    }

    function finde_kos_typ_id($vorname, $nachname)
    {
        $treffer ['ANZ'] = 0;
        $personen_ids_arr = $this->get_person_ids_byname_arr($vorname, $nachname);
        if (!empty($personen_ids_arr)) {
            $anz_p = count($personen_ids_arr);
            for ($a = 0; $a < $anz_p; $a++) {
                /* Mietvertraege */
                $person_id = $personen_ids_arr [$a] ['id'];
                $mv_arr = $this->mv_ids_von_person($person_id);
                if (!empty($mv_arr)) {
                    $anz_mv = count($mv_arr);
                    for ($m = 0; $m < $anz_mv; $m++) {
                        $treffer ['ERG'] [$treffer ['ANZ']] ['KOS_TYP'] = 'Mietvertrag';
                        $treffer ['ERG'] [$treffer ['ANZ']] ['KOS_ID'] = $mv_arr [$m];
                        $treffer ['ANZ']++;
                    }
                }
                /* WEG-ET */
                $weg = new weg ();
                $et_arr = $weg->get_eigentuemer_id_from_person_arr($person_id);
                if (!empty($et_arr)) {
                    $treffer ['ET'] [] = $et_arr;
                }
            }

            if ($treffer ['ANZ'] > 1 && session()->has('geldkonto_id')) {
                $anz_t = count($treffer ['ERG']);
                $treffer_f = 0;
                for ($a = 0; $a < $anz_t; $a++) {
                    $kos_typ = $treffer ['ERG'] [$a] ['KOS_TYP'];
                    $kos_id = $treffer ['ERG'] [$a] ['KOS_ID'];
                    if ($kos_typ == 'Mietvertrag') {
                        $mv = new mietvertraege ();
                        $mv->get_mietvertrag_infos_aktuell($kos_id);
                        $gk = new gk ();
                        if ($gk->check_zuweisung_kos_typ(session()->get('geldkonto_id'), 'Objekt', $mv->objekt_id)) {
                            $treffer ['ERG_F'] [$treffer_f] ['KOS_TYP'] = 'Mietvertrag';
                            $treffer ['ERG_F'] [$treffer_f] ['KOS_ID'] = $kos_id;
                            $treffer_f++;
                        }
                    }
                }
            }

            // print_r($treffer);
            return $treffer;
        }
    }

    function get_person_ids_byname_arr($vorname, $nachname)
    {
        $db_abfrage = "SELECT * FROM persons
	WHERE ((LTRIM( RTRIM( REPLACE( name, CHAR( 13, 10 ) ,  '' ) ) ) LIKE ? && LTRIM( RTRIM( REPLACE( first_name, CHAR( 13, 10 ) ,  '' ) ) ) LIKE ?
) OR 
(LTRIM( RTRIM( REPLACE( name, CHAR( 13, 10 ) ,  '' ) ) ) LIKE ? && LTRIM( RTRIM( REPLACE( first_name, CHAR( 13, 10 ) ,  '' ) ) ) LIKE ?
)) AND persons.deleted_at IS NULL
LIMIT 0 , 30";
        $resultat = DB::select($db_abfrage, [$nachname, $vorname, $vorname, $nachname]);
        return $resultat;
    }

    function mv_ids_von_person($person_id)
    {
        $db_abfrage = "SELECT PERSON_MIETVERTRAG_MIETVERTRAG_ID FROM PERSON_MIETVERTRAG where PERSON_MIETVERTRAG_PERSON_ID='$person_id' && PERSON_MIETVERTRAG_AKTUELL='1' ORDER BY PERSON_MIETVERTRAG_MIETVERTRAG_ID DESC";
        $resultat = DB::select($db_abfrage);
        if (!empty($resultat)) {
            $mietvertraege = array();
            foreach($resultat as $row) {
                array_push($mietvertraege, "$row[PERSON_MIETVERTRAG_MIETVERTRAG_ID]");
            }
            return $mietvertraege;
        }
    }

    function get_person_infos($person_id)
    {
        unset ($this->p_mv_ids);
        $result = DB::select("SELECT * FROM persons WHERE id='$person_id'");
        $row = $result[0];
        $this->person_nachname = ltrim(rtrim(strip_tags($row ['name'])));
        $this->person_vorname = ltrim(rtrim(strip_tags($row ['first_name'])));
        $this->person_geburtstag = $row ['birthday'];
        $d = new detail ();
        $this->geschlecht = ltrim(rtrim($d->finde_detail_inhalt('Person', $person_id, 'Geschlecht')));
        $this->get_person_anzahl_mietvertraege_aktuell($person_id);
        if ($this->person_anzahl_mietvertraege > 0) {
            $this->p_mv_ids = $this->mv_ids_von_person($person_id);
        }

        if ($d->finde_detail_inhalt('Person', $person_id, 'Anschrift')) {
            $this->anschrift = $d->finde_detail_inhalt('Person', $person_id, 'Anschrift');
        }
        if ($d->finde_detail_inhalt('Person', $person_id, 'Zustellanschrift')) {
            $this->zustellanschrift = $d->finde_detail_inhalt('Person', $person_id, 'Anschrift');
        }
    }

    function get_person_anzahl_mietvertraege_aktuell($person_id)
    {
        $result = DB::select("SELECT PERSON_MIETVERTRAG_MIETVERTRAG_ID FROM PERSON_MIETVERTRAG WHERE PERSON_MIETVERTRAG_PERSON_ID='$person_id' && PERSON_MIETVERTRAG_AKTUELL='1'");
        $anzahl = count($result);
        $this->person_anzahl_mietvertraege = $anzahl; // Wieviel MV hat die Person (nur aktuelle)
    }
} // end class personen
