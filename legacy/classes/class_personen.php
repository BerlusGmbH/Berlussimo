<?php

class personen
{
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

    function dropdown_personen($label, $name, $id, $vorwahl = null)
    {
        $arr = $this->personen_arr();
        echo "<label for=\"$id\">$label</label><select name=\"$name\" size=\"1\" id=\"$id\">";
        for ($a = 0; $a < count($arr); $a++) {
            $person_id = $arr [$a] ['PERSON_ID'];
            $person_nn = $arr [$a] ['PERSON_NACHNAME'];
            $person_vn = $arr [$a] ['PERSON_VORNAME'];

            if ($vorwahl == $person_id) {
                echo "<option value=\"$person_id\" selected>$person_nn $person_vn</OPTION>\n";
            } else {
                echo "<option value=\"$person_id\">$person_nn $person_vn</OPTION>\n";
            }
        }
        echo "</select><br>\n";
    }

    function personen_arr()
    {
        $db_abfrage = "SELECT PERSON_ID, PERSON_NACHNAME, PERSON_VORNAME FROM PERSON WHERE PERSON_AKTUELL='1' ORDER BY PERSON_NACHNAME ASC, PERSON_VORNAME ASC";
        $result = mysql_query($db_abfrage) or die (mysql_error());
        $numrows = mysql_numrows($result);
        if ($numrows) {
            while ($row = mysql_fetch_assoc($result)) {
                $arr [] = $row;
            }
            return $arr;
        } else {
            return false;
        }
    }

    function save_person($nachname, $vorname, $geb_dat, $geschlecht, $telefon, $handy, $email)
    {
        if (!$this->person_exists($vorname, $nachname, $geb_dat)) {
            $l_pid = $this->letzte_person_id() + 1;
            $geb_dat_db = date_german2mysql($geb_dat);
            $db_abfrage = "INSERT INTO PERSON VALUES (NULL, '$l_pid', '$nachname', '$vorname', '$geb_dat_db', '1')";
            $resultat = mysql_query($db_abfrage) or die (mysql_error());

            /* Protokollieren */
            $last_dat = mysql_insert_id();
            protokollieren('PERSON', $last_dat, '0');

            echo "Person wurde gespeichert";
            $l_pid = $this->letzte_person_id();

            $d = new detail ();
            $stand = 'Stand ' . date("d.m.Y");

            if ($geschlecht) {
                $d->detail_speichern_2('PERSON', $l_pid, 'Geschlecht', $geschlecht, '');
            }

            if ($telefon) {
                $d->detail_speichern_2('PERSON', $l_pid, 'Telefon', $telefon, $stand);
            }
            if ($handy) {
                $d->detail_speichern_2('PERSON', $l_pid, 'Handy', $handy, $stand);
            }

            if ($email) {
                $d->detail_speichern_2('PERSON', $l_pid, 'Email', $email, $stand);
            }
        } else {
            fehlermeldung_ausgeben("Mieter mit dem selben Namen, Vornamen und Geburtsdatum exisitiert bereits");
        }
        return $l_pid;
    }

    function person_exists($vorname, $nachname, $geb_dat)
    {
        $geb_dat_db = date_german2mysql($geb_dat);
        $db_abfrage = "SELECT * FROM PERSON WHERE  PERSON_NACHNAME='$nachname' && PERSON_VORNAME='$vorname' && PERSON_GEBURTSTAG='$geb_dat_db' &&PERSON_AKTUELL='1'";
        $resultat = mysql_query($db_abfrage) or die (mysql_error());
        $numrows = mysql_numrows($resultat);
        if ($numrows) {
            return true;
        } else {
            return false;
        }
    }

    function letzte_person_id()
    {
        $db_abfrage = "SELECT PERSON_ID FROM PERSON ORDER BY PERSON_ID DESC LIMIT 0,1";
        $resultat = mysql_query($db_abfrage) or die (mysql_error());
        while (list ($PERSON_ID) = mysql_fetch_row($resultat))
            return $PERSON_ID;
    }

    function finde_kos_typ_id($vorname, $nachname)
    {
        $treffer ['ANZ'] = 0;
        // echo '<pre>';
        $personen_ids_arr = $this->get_person_ids_byname_arr($vorname, $nachname);
        if (is_array($personen_ids_arr)) {
            // print_r($personen_ids_arr);

            $anz_p = count($personen_ids_arr);
            for ($a = 0; $a < $anz_p; $a++) {
                /* Mietvertraege */
                $person_id = $personen_ids_arr [$a] ['PERSON_ID'];
                $mv_arr = $this->mv_ids_von_person($person_id);
                if (is_array($mv_arr)) {
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
                if (is_array($et_arr)) {
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
                        if ($gk->check_zuweisung_kos_typ(session()->has('geldkonto_id'), 'Objekt', $mv->objekt_id)) {
                            $treffer ['ERG_F'] [$treffer_f] ['KOS_TYP'] = 'Mietvertrag';
                            $treffer ['ERG_F'] [$treffer_f] ['KOS_ID'] = $kos_id;
                            $treffer_f++;
                        }
                    }
                }
            }

            // print_r($treffer);
            return $treffer;
        } else {
            // fehlermeldung_ausgeben("KEINE PERSONEN $vorname $nachname");
        }
    }

    function get_person_ids_byname_arr($vorname, $nachname)
    {
        $db_abfrage = "SELECT * FROM PERSON
	WHERE (LTRIM( RTRIM( REPLACE( PERSON_NACHNAME, CHAR( 13, 10 ) ,  '' ) ) ) LIKE  '$nachname' && LTRIM( RTRIM( REPLACE( PERSON_VORNAME, CHAR( 13, 10 ) ,  '' ) ) ) LIKE  '$vorname'
) OR 
(LTRIM( RTRIM( REPLACE( PERSON_NACHNAME, CHAR( 13, 10 ) ,  '' ) ) ) LIKE  '$vorname' && LTRIM( RTRIM( REPLACE( PERSON_VORNAME, CHAR( 13, 10 ) ,  '' ) ) ) LIKE  '$nachname'
)
&& PERSON_AKTUELL =  '1'
LIMIT 0 , 30";
        // echo $db_abfrage;
        $resultat = mysql_query($db_abfrage) or die (mysql_error());
        $numrows = mysql_numrows($resultat);
        if ($numrows) {
            while ($row = mysql_fetch_assoc($resultat))
                $personen_arr [] = $row;
        }
        if (isset ($personen_arr) && is_array($personen_arr)) {
            return $personen_arr;
        }
    }

    function mv_ids_von_person($person_id)
    {
        $db_abfrage = "SELECT PERSON_MIETVERTRAG_MIETVERTRAG_ID FROM PERSON_MIETVERTRAG where PERSON_MIETVERTRAG_PERSON_ID='$person_id' && PERSON_MIETVERTRAG_AKTUELL='1' ORDER BY PERSON_MIETVERTRAG_MIETVERTRAG_ID DESC";
        $resultat = mysql_query($db_abfrage) or die (mysql_error());
        $numrows = mysql_numrows($resultat);
        if ($numrows > 0) {
            $mietvertraege = array();
            while (list ($PERSON_MIETVERTRAG_MIETVERTRAG_ID) = mysql_fetch_row($resultat)) {
                array_push($mietvertraege, "$PERSON_MIETVERTRAG_MIETVERTRAG_ID");
            }
            return $mietvertraege;
        }
    }

    function personen_ohne_geschlecht()
    {
        /*
         * SELECT * FROM `PERSON` WHERE PERSON_AKTUELL='1' && PERSON_ID NOT IN (
         * SELECT DETAIL_ZUORDNUNG_ID FROM `DETAIL` WHERE DETAIL_ZUORDNUNG_TABELLE='PERSON' && `DETAIL_NAME` LIKE 'Geschlecht' && DETAIL_AKTUELL='1')
         */
    }

    function finde_personen_name($string)
    {
        $db_abfrage = "SELECT PERSON_DAT, PERSON_ID, PERSON_NACHNAME, PERSON_VORNAME, PERSON_GEBURTSTAG FROM PERSON WHERE PERSON_AKTUELL='1' && (PERSON_NACHNAME LIKE '%$string%' OR PERSON_VORNAME LIKE '%$string%' )ORDER BY PERSON_NACHNAME, PERSON_VORNAME ASC LIMIT 0,50";
        $resultat = mysql_query($db_abfrage) or die (mysql_error());

        $numrows = mysql_numrows($resultat);
        if ($numrows) {
            while ($row = mysql_fetch_assoc($resultat))
                $personen_arr [] = $row;
        }
        if (isset ($personen_arr) && is_array($personen_arr)) {
            return $personen_arr;
        }
    }

    function personen_liste_alle()
    {
        if (request()->has('suchfeld')) {
            $suchbegriff = request()->input('suchfeld');
        } else {
            $suchbegriff = '';
        }
        echo "<form method=\"post\" >";
        echo "<table class=\"formular_tabelle\">";
        echo "<tr>";
        echo "<td  width=50% align=left>Suchbegriff: <input type=\"text\" name=\"suchfeld\" size=\"50\" value=\"$suchbegriff\"></td>";
        echo "<td width=30% align=left>suchen in:  <select name=\"suche_nach\">";
        echo "<option value=\"Nachname\">Nachname</option>";
        echo "<option value=\"Vorname\">Vorname</option>";
        echo "</select></td>";
        echo "<td width=20% align=left><button type=\"submit\" name=\"person_finden\" value=\"Finden\" class=\"btn waves-effect waves-light\" id=\"person_finden\"><i class=\"material-icons right\">send</i>Finden</button></td></tr>";
        echo "</table>";
        echo "</form>";
        
        if (request()->has('person_finden')) {
            if (request()->input('suche_nach') == "Nachname") {
                $such_tabelle = "PERSON_NACHNAME";
            }
            if (request()->input('suche_nach') == "Vorname") {
                $such_tabelle = "PERSON_VORNAME";
            }
            $suchbegriff = request()->input('suchfeld');
            $db_abfrage = "SELECT PERSON_DAT, PERSON_ID, PERSON_NACHNAME, PERSON_VORNAME, PERSON_GEBURTSTAG FROM PERSON WHERE PERSON_AKTUELL='1' && $such_tabelle LIKE '$suchbegriff%' ORDER BY PERSON_NACHNAME ASC LIMIT 0,50";
        } else {
            return [];
        }
        $resultat = mysql_query($db_abfrage) or die (mysql_error());

        $numrows = mysql_numrows($resultat);
        if ($numrows > 0) {
            while ($row = mysql_fetch_assoc($resultat))
                $personen_arr [] = $row;
        }

        echo "<table>";
        echo "<tr><th>Personenliste</th><th  colspan=\"5\">";
        sprungmarken_links();
        echo "</th></tr>\n";
        echo "</table>";
        iframe_start();
        echo "<table class=\"sortable\">";
        echo "<tr><th >Nachname</th><th>Vorname</th><th>Anschrift</th><th>Einheit</th><th>MIETKONTO</th><th>Zusatzinformationen</th></tr>\n";

        $buchstaben = array();
        $zeile = 0;
        for ($a = 0; $a < $numrows; $a++) {
            $zeile++;
            $person_id = $personen_arr [$a] ['PERSON_ID'];
            $person_nachname = $personen_arr [$a] ['PERSON_NACHNAME'];
            $person_vorname = $personen_arr [$a] ['PERSON_VORNAME'];

            $person_gebam = date_mysql2german($personen_arr [$a] ['PERSON_GEBURTSTAG']);
            $aendern_link = "<a class=\"table_links\" href='" . route('legacy::personen::index', ['anzeigen' => 'person_aendern', 'person_id' => $person_id]) . "'>Person ändern</a>";

            $detail_check = detail_check("PERSON", $person_id);
            if ($detail_check > 0) {
                $detail_link = "<a class=\"table_links\" href='" . route('legacy::details::index', ['option' => 'details_anzeigen', 'detail_tabelle' => 'PERSON', 'detail_id' => $person_id]) . "'>Details</a>";
            } else {
                $detail_link = "<a class=\"table_links\" href='" . route('legacy::details::index', ['option' => 'details_hinzu', 'detail_tabelle' => 'PERSON', 'detail_id' => $person_id]) . "'>Neues Detail</a>";
            }

            $erster_buchstabe = substr($person_nachname, 0, 1);
            if (!in_array($erster_buchstabe, $buchstaben)) {
                $buchstaben [] = $erster_buchstabe;
                $sprung_marke_link = "<a name=\"$erster_buchstabe\"><b>$person_nachname</b></a>";
            } else {
                $sprung_marke_link = "$person_nachname";
            }

            echo "<tr class=\"zeile$zeile\" valign=\"top\"><td>$sprung_marke_link</td><td>$person_vorname</td>";
            $this->get_person_infos($person_id);
            if ($this->person_anzahl_mietvertraege > 0) {
                $haus_info_link = '';
                $einheit_link = '';
                $mietkonto_link = '';
                for ($b = 0; $b < $this->person_anzahl_mietvertraege; $b++) {
                    $mv = new mietvertraege ();
                    $mv_id = $this->p_mv_ids [$b];
                    $mv->get_mietvertrag_infos_aktuell($mv_id);
                    $haus_info_link .= "<a href='" . route('legacy::einheiten::index', ['einheit_raus' => 'einheit_kurz', 'haus_id' => $mv->haus_id]) . "'>$mv->haus_strasse $mv->haus_nr</a><br>";
                    if ($mv->mietvertrag_aktuell) {
                        $einheit_link .= "<a  href='" . route('legacy::uebersicht::index', ['anzeigen' => 'einheit', 'einheit_id' => $mv->einheit_id, 'mietvertrag_id' => $mv_id]) . "'><b>$mv->einheit_kurzname</b></a><br>";
                    } else {
                        $einheit_link .= "<a id=\"link_rot_fett\" href='" . route('legacy::uebersicht::index', ['anzeigen' => 'einheit', 'einheit_id' => $mv->einheit_id, 'mietvertrag_id' => $mv_id]) . "'>$mv->einheit_kurzname</a><br>";
                    }
                    $mietkonto_link .= "<a href='" . route('legacy::mietkontenblatt::index', ['anzeigen' => 'mk_pdf', 'mietvertrag_id' => $mv_id]) . "'><img src=\"images/pdf_light.png\"></a>&nbsp;<a href='" . route('legacy::mietkontenblatt::index', ['anzeigen' => 'mietkonto_ab', 'mietvertrag_id' => $mv_id]) . "'><img src=\"images/pdf_dark.png\"></a><br>";

                    if ($b < $this->person_anzahl_mietvertraege - 1) {
                        // $haus_info_link .= "<br>";
                        // $einheit_link .= "<br>";
                        // $mietkonto_link .= "<br>";
                    }
                }
            } else {
                $haus_info_link = "Kein Mieter";
                $einheit_link = "";
                $mietkonto_link = "";
            }

            $weg = new weg ();
            $eigentuemer_id_arr = $weg->get_eigentuemer_id_from_person_arr($person_id);
            $eigentuemer_link = '';
            if (is_array($eigentuemer_id_arr)) {
                if ($haus_info_link == 'Kein Mieter') {
                    $haus_info_link = '';
                }
                $anz_e = count($eigentuemer_id_arr);
                for ($ee = 0; $ee < $anz_e; $ee++) {
                    $eig_id = $eigentuemer_id_arr [$ee] ['WEG_EIG_ID'];
                    $weg->get_eigentumer_id_infos($eig_id);
                    $einheit_link .= "<a href='" . route('legacy::weg::index', ['option' => 'einheit_uebersicht', 'einheit_id' => $weg->einheit_id]) . "'>$weg->einheit_kurzname</a><br>";
                    $haus_info_link .= "$weg->haus_strasse $weg->haus_nummer<br>";
                    $mietkonto_link .= "<a href='" . route('legacy::weg::index', ['option' => 'hg_kontoauszug', 'einheit_id' => $weg->einheit_id]) . "'><img src=\"images/pdf_light.png\"></a> <a href='" . route('legacy::weg::index', ['option' => 'hg_kontoauszug', 'einheit_id' => $weg->einheit_id, 'no_logo']) . "'><img src=\"images/pdf_dark.png\"></a><br>";
                }
            }

            echo "<td>$haus_info_link</td><td>$einheit_link</td><td valign=\"top\">$mietkonto_link</td><td>$aendern_link $detail_link</td></tr>";
            if ($zeile == 2) {
                $zeile = 0;
            }
        }

        iframe_end();
        echo "</table>";
    }

    function get_person_infos($person_id)
    {
        unset ($this->p_mv_ids);
        $result = mysql_query("SELECT * FROM PERSON WHERE PERSON_AKTUELL='1' && PERSON_ID='$person_id' ORDER BY PERSON_DAT DESC LIMIT 0,1");
        $row = mysql_fetch_assoc($result);
        $this->person_nachname = ltrim(rtrim(strip_tags($row ['PERSON_NACHNAME'])));
        $this->person_vorname = ltrim(rtrim(strip_tags($row ['PERSON_VORNAME'])));
        $this->person_geburtstag = $row ['PERSON_GEBURTSTAG'];
        $d = new detail ();
        $this->geschlecht = ltrim(rtrim($d->finde_detail_inhalt('PERSON', $person_id, 'Geschlecht')));
        $this->get_person_anzahl_mietvertraege_aktuell($person_id);
        if ($this->person_anzahl_mietvertraege > 0) {
            $this->p_mv_ids = $this->mv_ids_von_person($person_id);
        }

        if ($d->finde_detail_inhalt('PERSON', $person_id, 'Anschrift')) {
            $this->anschrift = $d->finde_detail_inhalt('PERSON', $person_id, 'Anschrift');
        }
        if ($d->finde_detail_inhalt('PERSON', $person_id, 'Zustellanschrift')) {
            $this->zustellanschrift = $d->finde_detail_inhalt('PERSON', $person_id, 'Anschrift');
        }
    }

    function get_person_anzahl_mietvertraege_aktuell($person_id)
    {
        $result = mysql_query("SELECT PERSON_MIETVERTRAG_MIETVERTRAG_ID FROM PERSON_MIETVERTRAG WHERE PERSON_MIETVERTRAG_PERSON_ID='$person_id' && PERSON_MIETVERTRAG_AKTUELL='1'");
        $anzahl = mysql_numrows($result);
        $this->person_anzahl_mietvertraege = $anzahl; // Wieviel MV hat die Person (nur aktuelle)
    }

    function get_person_hinweis()
    {
        $abfrage = "SELECT PERSON_ID, PERSON_NACHNAME, PERSON_VORNAME, `DETAIL_NAME` , `DETAIL_INHALT` , `DETAIL_BEMERKUNG` , `DETAIL_ZUORDNUNG_ID`
FROM `DETAIL` , PERSON
WHERE `DETAIL_NAME` LIKE '%Hinweis%'
AND `DETAIL_AKTUELL` = '1'
AND `DETAIL_ZUORDNUNG_TABELLE` LIKE 'PERSON' && DETAIL_ZUORDNUNG_ID = PERSON_ID ORDER BY PERSON_NACHNAME ASC";

        $result = mysql_query($abfrage);
        $numrows = mysql_numrows($result);
        if ($numrows) {
            echo "<table class=\"sortable\">";
            echo "<tr><th>Name</th><th>Vorname</th><th>DETAIL</th><th>Inhalt</th><th>Bemerkung</th></tr>";
            while ($row = mysql_fetch_assoc($result)) {
                // $arr[] = $row;
                $pname = $row ['PERSON_NACHNAME'];
                $person_id = $row ['PERSON_ID'];
                $vname = $row ['PERSON_VORNAME'];
                $detname = $row ['DETAIL_NAME'];
                $detinhalt = $row ['DETAIL_INHALT'];
                $det_bem = $row ['DETAIL_BEMERKUNG'];
                echo "<tr>";
                $mv_ids_arr = $this->mv_ids_von_person($person_id);
                echo "<td>";
                if (is_array($mv_ids_arr)) {
                    // print_r($mv_ids_arr);
                    $anz = count($mv_ids_arr);
                    for ($a = 0; $a < $anz; ++$a) {
                        $mv = new mietvertraege ();
                        $mv_id = $mv_ids_arr [$a];
                        $mv->get_mietvertrag_infos_aktuell($mv_id);
                        echo "$mv->einheit_kurzname\n";
                        // echo '<pre>';
                        // print_r($mv);
                        // die();
                    }
                }
                echo "</td>";
                echo "<td>$pname</td><td>$vname</td><td>$detname</td><td>$detinhalt</td><td>$det_bem</td></tr>";
            }
            echo "</table>";
        } else {
            echo "Keine Hinweise zu Personen vorhanden!";
        }
    } // end function

    function get_person_anschrift()
    {
        $abfrage = "SELECT PERSON_ID, PERSON_NACHNAME, PERSON_VORNAME, `DETAIL_NAME` , `DETAIL_INHALT` , `DETAIL_BEMERKUNG` , `DETAIL_ZUORDNUNG_ID`
FROM `DETAIL` , PERSON
WHERE `DETAIL_NAME` LIKE '%anschrift%'
AND `DETAIL_AKTUELL` = '1'
AND `DETAIL_ZUORDNUNG_TABELLE` LIKE 'PERSON' && DETAIL_ZUORDNUNG_ID = PERSON_ID ORDER BY PERSON_NACHNAME ASC";

        $result = mysql_query($abfrage);
        $numrows = mysql_numrows($result);
        if ($numrows) {
            echo "<table class=\"sortable\">";
            echo "<tr><th>Name</th><th>Vorname</th><th>DETAIL</th><th>Inhalt</th><th>Bemerkung</th></tr>";
            while ($row = mysql_fetch_assoc($result)) {
                // $arr[] = $row;
                $pname = $row ['PERSON_NACHNAME'];
                $person_id = $row ['PERSON_ID'];
                $vname = $row ['PERSON_VORNAME'];
                $detname = $row ['DETAIL_NAME'];
                $detinhalt = $row ['DETAIL_INHALT'];
                $det_bem = $row ['DETAIL_BEMERKUNG'];
                echo "<tr>";
                $mv_ids_arr = $this->mv_ids_von_person($person_id);
                echo "<td>";
                if (is_array($mv_ids_arr)) {
                    // print_r($mv_ids_arr);
                    $anz = count($mv_ids_arr);
                    for ($a = 0; $a < $anz; ++$a) {
                        $mv = new mietvertraege ();
                        $mv_id = $mv_ids_arr [$a];
                        $mv->get_mietvertrag_infos_aktuell($mv_id);
                        echo "$mv->einheit_kurzname\n";
                        // echo '<pre>';
                        // print_r($mv);
                        // die();
                    }
                }
                echo "</td>";
                echo "<td>$pname</td><td>$vname</td><td>$detname</td><td>$detinhalt</td><td>$det_bem</td></tr>";
            }
            echo "</table>";
        } else {
            echo "Keine Hinweise zu Personen vorhanden!";
        }
    } // end function
} // end class personen
