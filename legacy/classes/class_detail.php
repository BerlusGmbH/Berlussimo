<?php

/* Modul DETAILS für Anzeigen/erfassung aller DETAILS bezogen auf OBJEKTE, HÄUSER, MIETER, EINHEITEN USW */

class detail
{

    public $detail_name;
    public $last_detail_id;
    public $dat_tabelle;
    public $dat_id;
    public $det_tabelle;
    public $det_id;
    public $kop;
    public $knr;
    public $lnr;
    public $a_art;
    public $a_nr_hw;
    public $kundentext;
    public $vorgangsnr_gh;
    public $datum;
    public $datum_j;
    public $datum_m;
    public $datum_t;
    public $datum_d;
    public $waehrung;
    public $version;
    public $verantw;
    public $positionen_arr;

    function dropdown_optionen($label, $name, $id, $kat_bez, $vorgabe, $js = null)
    {
        $arr = $this->detail_optionen_arr($kat_bez);
        if (!empty($arr)) {
            echo "<label for=\"$id\">$label</label><select name=\"$name\" id=\"$id\" size=1 $js>\n";
            $anz = count($arr);

            for ($a = 0; $a < $anz; $a++) {
                $u_name = ltrim(rtrim($arr [$a] ['UNTERKATEGORIE_NAME']));
                if (ltrim(rtrim($vorgabe)) == $u_name) {
                    echo "<option value=\"$u_name\" selected>$u_name</option>\n";
                } else {
                    echo "<option value=\"$u_name\">$u_name</option>\n";
                }
            }
            echo "</select>\n";
        } else {
            echo "<label for=\"$name\">$beschreibung</label>\n";
            echo "<input type=\"text\" id=\"$id\" name=\"$name\" value=\"$wert\" size=\"$size\" $js_action >\n";
        }
    }

    function detail_optionen_arr($kat_bez)
    {
        $db_abfrage = "SELECT KATEGORIE_ID, UNTERKATEGORIE_NAME FROM `DETAIL_KATEGORIEN` JOIN DETAIL_UNTERKATEGORIEN ON (`DETAIL_KAT_ID`=KATEGORIE_ID) WHERE `DETAIL_KAT_NAME`='$kat_bez'";
        $result = DB::select($db_abfrage);
        return $result;
    }

    function form_detail_hinzu($tab, $id, $vorauswahl = null)
    {
        $kurzinfo = $this->get_info_detail($tab, $id);
        $form = new formular ();
        $link = '';
        if ($tab == 'EINHEIT') {
            $link = "<a href='" . route('legacy::uebersicht::index', ['anzeigen' => 'einheit', 'einheit_id' => $id]) . "'>Zurück zu Einheit</a>";
        }
        $form->erstelle_formular('Detail hinzufügen', '');
        echo "$link<br>";
        $form->hidden_feld("tabelle", "$tab");
        $form->hidden_feld("id", "$id");
        $det_kat_arr = $this->get_detail_kat_arr($tab);
        $js = "onchange=\"get_detail_ukats(this.value)\" onload=\"get_detail_ukats(this.value)\"";
        $this->select_hauptkats_arr("Detail auswählen zu $kurzinfo", 'detail_kat', 'detail_kat', $js, $vorauswahl, $det_kat_arr);

        $this->select_unterkats('Detailoption auswählen', 'detail_ukat', 'detail_ukat', '');
        $hinw = ' Text als Warnung eingeben: <p class="warnung"> INHALT </p>';

        $form->text_bereich('Detail Inhalt', 'inhalt', '', 20, 10, 'inhalt');
        echo htmlentities($hinw);
        $form->text_bereich('Bemerkung', 'bemerkung', '', 20, 10, 'bemerkung');
        echo "<br>";
        $form->hidden_feld("option", "detail_gesendet");
        $form->send_button("submit_detail", "Eintragen");
        $form->ende_formular();
        $this->detailsanzeigen($tab, $id);
    }

    function get_info_detail($tab, $id)
    {
        if ($tab == "OBJEKT") {
            $db_abfrage = "SELECT OBJEKT_KURZNAME FROM OBJEKT WHERE OBJEKT_AKTUELL='1' && OBJEKT_ID = '$id' order by OBJEKT_DAT DESC limit 0,1";
            $resultat = DB::select($db_abfrage);
            if (!empty($resultat))
                return $resultat[0]['OBJEKT_KURZNAME'];
        }
        if ($tab == "HAUS") {
            $db_abfrage = "SELECT HAUS_STRASSE, HAUS_NUMMER FROM HAUS WHERE HAUS_AKTUELL='1' && HAUS_ID = '$id' order by HAUS_DAT DESC limit 0,1";
            $resultat = DB::select($db_abfrage);
            if (!empty($resultat))
                return $resultat[0]['HAUS_STRASSE'] . " " . $resultat[0]['HAUS_NUMMER'];
        }
        if ($tab == "EINHEIT") {
            $db_abfrage = "SELECT EINHEIT_KURZNAME FROM EINHEIT WHERE EINHEIT_AKTUELL='1' && EINHEIT_ID = '$id' order by EINHEIT_DAT DESC limit 0,1";
            $resultat = DB::select($db_abfrage);
            if(!empty($resultat))
                return $resultat[0]['EINHEIT_KURZNAME'];
        }
        if ($tab == "MIETVERTRAG") {
            $mieternamen = mieternamen_als_string($id);
            $db_abfrage = "SELECT EINHEIT_ID, MIETVERTRAG_VON, MIETVERTRAG_BIS FROM MIETVERTRAG WHERE MIETVERTRAG_AKTUELL='1' && MIETVERTRAG_ID = '$id' order by MIETVERTRAG_DAT DESC limit 0,1";
            $resultat = DB::select($db_abfrage);
            foreach($resultat as $row) {
                $einheit_name = einheit_name($resultat[0]['EINHEIT_ID']);
                $anzahl_mieter = anzahl_mieter_im_vertrag($id);
                $ausgabe = "$einheit_name vermietet an $anzahl_mieter Personen ($mieternamen) am $resultat[MIETVERTRAG_VON] bis $resultat[MIETVERTRAG_BIS]";
                return $ausgabe;
            }
        }
        if ($tab == "PERSON") {
            $p = new person ();
            $p->get_person_infos($id);
            $kurzinfo = "$p->person_nachname $p->person_vorname";
            return $kurzinfo;
        }
    }

    function get_detail_kat_arr($tabelle)
    {
        $result = DB::select("SELECT DETAIL_KAT_ID, DETAIL_KAT_NAME FROM `DETAIL_KATEGORIEN` WHERE `DETAIL_KAT_KATEGORIE` = '$tabelle'
AND `DETAIL_KAT_AKTUELL` = '1' ORDER BY DETAIL_KAT_NAME ASC");
        return $result;
    }

    function select_hauptkats_arr($beschreibung, $name, $id, $js, $selected_value, $arr)
    {
        if (is_array($arr) && !empty($arr)) {
            echo "<label for=\"$id\">$beschreibung</label>\n";
            echo "<select name=\"$name\" id=\"$id\" $js>\n";
            $anzahl = count($arr);
            echo "<option value=\"nooption\">Bitte wählen</option>\n";
            for ($a = 0; $a < $anzahl; $a++) {
                $kat_id = $arr [$a] ['DETAIL_KAT_ID'];
                $kat_name = $arr [$a] ['DETAIL_KAT_NAME'];

                if ($kat_name == $selected_value) {
                    echo "<option value=\"$kat_id\" selected>$kat_name</option>\n";
                } else {
                    echo "<option value=\"$kat_id\">$kat_name</option>\n";
                }
            }
            echo "</select>\n";
        } else {
            echo "Fehler beim Lesen aus der DB / Error:D123";
        }
    }

    function select_unterkats($beschreibung, $name, $id, $js)
    {
        echo "<label for=\"$id\">$beschreibung</label>\n";
        echo "<select name=\"$name\" id=\"$id\" $js>\n";
        echo "<option value=\"nooption\">Manuell eintragen</option>";
        echo "</select>\n";
    }

    function detailsanzeigen($detail_tabelle, $detail_id)
    {
        $f = new formular ();
        $f->fieldset("Details menü", 'details_menue');
        $link = route('legacy::details::index', ['option' => 'details_hinzu', 'detail_tabelle' => $detail_tabelle, 'detail_id' => $detail_id]);
        echo "<a href=\"$link\">Neues Detail hinzufügen</a>&nbsp;";
        $f->fieldset_ende();

        $db_abfrage = "SELECT DETAIL_DAT, DETAIL_ID, DETAIL_NAME, DETAIL_INHALT, DETAIL_BEMERKUNG FROM DETAIL WHERE DETAIL_AKTUELL='1' && DETAIL_ZUORDNUNG_TABELLE = '$detail_tabelle' && DETAIL_ZUORDNUNG_ID = '$detail_id' ORDER BY DETAIL_NAME ASC";
        $resultat = DB::select($db_abfrage);

        $numrows = count($resultat);

        if ($numrows) {
            echo "<table>\n";
            $kurzinfo = $this->get_info_detail($detail_tabelle, $detail_id);
            echo "<tr class=\"feldernamen\"><td colspan=4>Details über $kurzinfo</td></tr>\n";
            echo "<tr class=\"feldernamen\"><td>Beschreibung</td><td>Inhalt</td><td>Bemerkung</td><td>Optionen</td></tr>\n";

            $counter = 0;
            foreach ($resultat as $row) {
                $counter++;
                $loeschen_link = "<a href='" . route('legacy::details::index', ['option' => 'detail_loeschen', 'detail_dat' => $row['DETAIL_DAT']]) . "'>Löschen</a>";

                if ($counter == 1) {
                    echo "<tr class=\"zeile1\"><td>$row[DETAIL_NAME]</td><td>$row[DETAIL_INHALT]</td><td>$row[DETAIL_BEMERKUNG]</td><td>$loeschen_link</td></tr>\n";
                }
                if ($counter == 2) {
                    echo "<tr class=\"zeile2\"><td>$row[DETAIL_NAME]</td><td>$row[DETAIL_INHALT]</td><td>$row[DETAIL_BEMERKUNG]</td><td>$loeschen_link</td></tr>\n";
                    $counter = 0;
                }
            }
            echo "<tr><td colspan=2>";
            echo "</td></tr>";
            echo "</table>";
        } else {
            echo "Keine Details vorhanden";
        }
    }

    function get_katname($kat_id)
    {
        $this->detail_name = '';
        $db_abfrage = "SELECT DETAIL_KAT_NAME FROM DETAIL_KATEGORIEN WHERE DETAIL_KAT_AKTUELL='1' && DETAIL_KAT_ID = '$kat_id' limit 0,1";
        $resultat = DB::select($db_abfrage);
        if(!empty($resultat))
            $this->detail_name = $resultat[0]['DETAIL_KAT_NAME'];
    }

    function detail_speichern($tabelle, $id, $det_name, $det_inhalt, $det_bemerkung)
    {
        $this->letzte_detail_id();
        $db_abfrage = "INSERT INTO DETAIL VALUES (NULL, '$this->last_detail_id', '$det_name','$det_inhalt', '$det_bemerkung', '1','$tabelle','$id')";
        $resultat = DB::insert($db_abfrage);
        if ($resultat) {
            echo "<br>Detail wurde gespeichert";
        } else {
            echo "<br>FEHLER: Detail wurde NICHT gespeichert";
        }
        weiterleiten_in_sec(route('legacy::details::index', ['option' => 'details_anzeigen', 'detail_tabelle' => $tabelle, 'detail_id' => $id]), 2);
    }

    function letzte_detail_id()
    {
        $this->last_detail_id = '';
        $db_abfrage = "SELECT DETAIL_ID FROM DETAIL ORDER BY DETAIL_ID DESC LIMIT 0,1";
        $resultat = DB::select($db_abfrage);
        if(!empty($resultat))
            $this->last_detail_id = $resultat[0]['DETAIL_ID'] + 1;
    }

    function detail_aktualisieren($tab, $tab_id, $det_name, $det_inhalt, $det_bemerkung)
    {
        if ($this->check_detail_exist($tab, $tab_id, $det_name)) {
            $this->details_deaktivieren($tab, $tab_id, $det_name);
        }
        $this->detail_speichern_2($tab, $tab_id, $det_name, $det_inhalt, $det_bemerkung);
    }

    function check_detail_exist($tab, $tab_id, $det_name)
    {
        $db_abfrage = "SELECT DETAIL_INHALT FROM DETAIL WHERE DETAIL_AKTUELL='1' && DETAIL_NAME='$det_name' && DETAIL_ZUORDNUNG_TABELLE='$tab' && DETAIL_ZUORDNUNG_ID='$tab_id'";
        $result = DB::select($db_abfrage);
        return !empty($result);
    }

    function details_deaktivieren($tab, $tab_id, $det_name)
    {
        $db_abfrage = "UPDATE DETAIL SET DETAIL_AKTUELL='0' WHERE DETAIL_NAME='$det_name' && DETAIL_ZUORDNUNG_TABELLE='$tab' && DETAIL_ZUORDNUNG_ID='$tab_id'";
        DB::update($db_abfrage);
    }

    function detail_speichern_2($tabelle, $id, $det_name, $det_inhalt, $det_bemerkung)
    {
        $this->letzte_detail_id();
        if ($det_bemerkung == '') {
            $det_bemerkung = Auth::user()->email . '-' . date("d.m.Y H:i");
        }
        $db_abfrage = "INSERT INTO DETAIL VALUES (NULL, '$this->last_detail_id', '$det_name','$det_inhalt', '$det_bemerkung', '1','$tabelle','$id')";
        DB::insert($db_abfrage);
    }

    function detail_loeschen($detail_dat)
    {
        $db_abfrage = "UPDATE DETAIL SET DETAIL_AKTUELL='0' WHERE DETAIL_DAT='$detail_dat'";
        $resultat = DB::update($db_abfrage);
        if ($resultat > 0) {
            echo "<br>Detail wurde gelöscht";
        } else {
            echo "<br>FEHLER: Detail wurde NICHT gelöscht";
        }
        $this->finde_tab_id($detail_dat);
        $link = route('legacy::details::index', ['option' => 'details_anzeigen', 'detail_tabelle' => $this->dat_tabelle, 'detail_id' => $this->dat_id], false);
        weiterleiten_in_sec($link, 2);
    }

    function finde_tab_id($detail_dat)
    {
        $this->det_tabelle = '';
        $this->det_id = '';
        $db_abfrage = "SELECT DETAIL_ZUORDNUNG_TABELLE, DETAIL_ZUORDNUNG_ID FROM DETAIL WHERE DETAIL_DAT='$detail_dat'";
        $resultat = DB::select($db_abfrage);
        foreach($resultat as $row) {
            $this->dat_tabelle = $row ['DETAIL_ZUORDNUNG_TABELLE'];
            $this->dat_id = $row ['DETAIL_ZUORDNUNG_ID'];
        }
    }

    function get_detail_info($detail_dat)
    {
        $db_abfrage = "SELECT * FROM DETAIL WHERE DETAIL_DAT='$detail_dat'";
        $resultat = DB::select($db_abfrage);
        $row = $resultat[0];
        return $row;
    }

    /* mandantennr finden falls exisitiert */

    function finde_mandanten_nr($partner_id)
    {
        $db_abfrage = "SELECT DETAIL_INHALT FROM DETAIL WHERE DETAIL_ZUORDNUNG_TABELLE='PARTNER_LIEFERANT' && DETAIL_NAME='Mandanten-Nr' && DETAIL_ZUORDNUNG_ID='$partner_id' && DETAIL_AKTUELL='1' ORDER BY DETAIL_DAT DESC limit 0,1";
        $resultat = DB::select($db_abfrage);
        foreach($resultat as $row)
            return $row['DETAIL_INHALT'];
    }

    /* geschlecht finden falls exisitiert */
    function finde_person_geschlecht($person_id)
    {
        $db_abfrage = " SELECT DETAIL_INHALT FROM DETAIL WHERE DETAIL_ZUORDNUNG_TABELLE = 'PERSON' && DETAIL_NAME = 'Geschlecht' && DETAIL_ZUORDNUNG_ID = '$person_id' && DETAIL_AKTUELL = '1' ORDER BY DETAIL_DAT DESC LIMIT 0 , 1 ";
        $resultat = DB::select($db_abfrage);
        foreach($resultat as $row)
            return ltrim(rtrim($row['DETAIL_INHALT']));
    }

    /* Funktion um alle Details zu finden anhand des Detailnamens, werden die Detailinhalte angezeigt */
    function finde_detail_inhalt($tab, $id, $detail_name)
    {
        $db_abfrage = "SELECT DETAIL_INHALT FROM DETAIL WHERE DETAIL_ZUORDNUNG_TABELLE = '$tab' && DETAIL_NAME = '$detail_name' && DETAIL_ZUORDNUNG_ID = '$id' && DETAIL_AKTUELL = '1' ORDER BY DETAIL_DAT DESC LIMIT 0 , 1";
        $resultat = DB::select($db_abfrage);
        if(!empty($resultat)) {
            return ltrim(rtrim($resultat[0]['DETAIL_INHALT']));
        } else {
            return '';
        }
    }

    function finde_alle_details_grup($tab, $id, $detail_name)
    {
        $db_abfrage = " SELECT DETAIL_INHALT FROM DETAIL WHERE DETAIL_ZUORDNUNG_TABELLE = '$tab' && DETAIL_ZUORDNUNG_ID = '$id' && DETAIL_NAME = '$detail_name'  && DETAIL_AKTUELL = '1' ORDER BY DETAIL_INHALT DESC";
        $my_arr = DB::select($db_abfrage);
        return $my_arr;
    }

    function finde_detail_inhalt_arr($detail_name)
    {
        $db_abfrage = " SELECT * FROM DETAIL WHERE DETAIL_NAME = '$detail_name'  && DETAIL_AKTUELL = '1' ORDER BY DETAIL_DAT DESC";
        $my_arr = DB::select($db_abfrage);
        return $my_arr;
    }

    function finde_detail_inhalt_last_arr($tab, $id, $detail_name)
    {
        $db_abfrage = " SELECT * FROM DETAIL WHERE DETAIL_ZUORDNUNG_TABELLE = '$tab' && DETAIL_ZUORDNUNG_ID = '$id' && DETAIL_NAME = '$detail_name'  && DETAIL_AKTUELL = '1' ORDER BY DETAIL_INHALT DESC LIMIT 0,1";
        $my_arr = DB::select($db_abfrage);
        return $my_arr;
    }

    function finde_alle_details_arr($tab, $tab_id)
    {
        $db_abfrage = "SELECT DETAIL_NAME, DETAIL_INHALT,  DETAIL_BEMERKUNG FROM DETAIL WHERE DETAIL_ZUORDNUNG_TABELLE = '$tab' && DETAIL_ZUORDNUNG_ID = '$tab_id' && DETAIL_AKTUELL = '1' ORDER BY DETAIL_NAME ASC";
        $my_arr= DB::select($db_abfrage);
        return $my_arr;
    }

    function dropdown_details($label, $name, $id)
    {
        $arr = $this->get_det_arr();
        echo "<label for=\"$id\">$label</label><select name=\"$name\" id=\"$id\" size=1 >\n";
        echo "<option value=\"\">Bitte wählen</option>\n";
        if (!empty($arr)) {
            $anz = count($arr);
            for ($a = 0; $a < $anz; $a++) {
                $det_name = $arr [$a] ['DETAIL_NAME'];
                echo "<option value=\"$det_name\">$det_name</option>\n";
            }
        }
        echo "</select>\n";
    }

    function get_det_arr()
    {
        $db_abfrage = "SELECT  `DETAIL_NAME` FROM  `DETAIL` WHERE  `DETAIL_AKTUELL` =  '1' GROUP BY DETAIL_NAME";
        $my_arr = DB::select($db_abfrage);
        return $my_arr;
    }

    function finde_detail($suchtext, $det_name = null)
    {
        if ($det_name == null) {
            $db_abfrage = "SELECT * FROM  `DETAIL` WHERE  `DETAIL_INHALT` LIKE  '%$suchtext%' AND  `DETAIL_AKTUELL` =  '1' ORDER BY DETAIL_NAME ASC";
        } else {
            $db_abfrage = "SELECT * FROM  `DETAIL` WHERE  `DETAIL_NAME`='$det_name' && `DETAIL_INHALT` LIKE  '%$suchtext%' AND  `DETAIL_AKTUELL` =  '1'";
        }
        $my_arr = DB::select($db_abfrage);
        if (!empty($my_arr)) {
            $anz = count($my_arr);
            echo "<table>";
            echo "<tr><th>DETNAME</th><th>INHALT</th><th>BEZ</th></tr>";
            for ($a = 0; $a < $anz; $a++) {
                $det_name = $my_arr [$a] ['DETAIL_NAME'];
                $det_inhalt = $my_arr [$a] ['DETAIL_INHALT'];
                $det_tab = ucfirst(strtolower($my_arr [$a] ['DETAIL_ZUORDNUNG_TABELLE']));
                $det_tab_id = $my_arr [$a] ['DETAIL_ZUORDNUNG_ID'];
                if (strtolower($my_arr [$a] ['DETAIL_ZUORDNUNG_TABELLE']) == 'objekt') {
                    $o = new objekt ();
                    $o->get_objekt_infos($det_tab_id);
                    $link_e = "<a href='" . route('legacy::details::index', ['option' => 'details_anzeigen', 'detail_tabelle' => 'OBJEKT', 'detail_id' => $det_tab_id]) . "'>Objekt: $o->objekt_kurzname</a>";
                }
                if (strtolower($my_arr [$a] ['DETAIL_ZUORDNUNG_TABELLE']) == 'einheit') {
                    $e = new einheit ();
                    $e->get_einheit_info($det_tab_id);
                    $link_e = "<a href='" . route('legacy::uebersicht::index', ['anzeigen' => 'einheit', 'einheit_id' => $det_tab_id]) . "'>Einheit: $e->einheit_kurzname</a>";
                }

                if (strtolower($my_arr [$a] ['DETAIL_ZUORDNUNG_TABELLE']) == 'mietvertrag') {
                    $mvs = new mietvertraege ();
                    $mvs->get_mietvertrag_infos_aktuell($det_tab_id);

                    $link_e = "<a href='" . route('legacy::uebersicht::index', ['anzeigen' => 'einheit', 'einheit_id' => $mvs->einheit_id, 'mietvertrag_id' => $det_tab_id]) . "'>Mieter: $mvs->einheit_kurzname $mvs->personen_name_string</a>";
                }

                if (strtolower($my_arr [$a] ['DETAIL_ZUORDNUNG_TABELLE']) == 'person') {
                    $pp = new personen ();
                    $pp->get_person_infos($det_tab_id);
                    if ($pp->person_anzahl_mietvertraege > 0) {
                        $link_e = '';
                        for ($pm = 0; $pm < $pp->person_anzahl_mietvertraege; $pm++) {
                            $mv_id = $pp->p_mv_ids [$pm];
                            $mvs = new mietvertraege ();
                            $mvs->get_mietvertrag_infos_aktuell($mv_id);
                            $link_e .= "Mieter: $mvs->einheit_kurzname $pp->person_nachname $pp->person_vorname<br>";
                        }
                    } else {
                        $link_e = "Kein Mieter: $pp->person_nachname $pp->person_vorname";
                    }
                }

                if (!isset ($link_e)) {
                    $link_e = "$det_tab $det_tab_id";
                }

                echo "<tr><td>$det_name</td><td>$det_inhalt</td><td>$link_e</td></tr>";
            }
            echo "</table>";
        } else {
            echo "NOT FOUND!!!";
        }
    }
} // class details ende