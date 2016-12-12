<?php

function form_main($target_id)
{
    $onclick = "onclick=\"daj2('/wartungsplaner/ajax?option=form_intern',document.getElementById('leftBox'))\" onfocus=\"daj2('/wartungsplaner/ajax?option=xxx',document.getElementById('rightBox'))\"";
    echo "<label for=\"int\">Intern</label>";
    echo "<input type=\"radio\" id=\"int\" name=\"int_ext\" $onclick />";

    $onclick1 = "onclick=\"daj2('/wartungsplaner/ajax?option=form_extern',document.getElementById('leftBox'))\" onfocus=\"daj2('/wartungsplaner/ajax?option=xxx',document.getElementById('rightBox'))\"";
    echo "<label for=\"extern\">Extern</label>";
    echo "<input type=\"radio\" id=\"ext\" name=\"int_ext\" $onclick1/>";
    echo '       Startadresse: ' . START_ADRESSE;
}

/*Termin eintragen*/
function form_termin_eintragen($benutzer_id, $datum_d, $VON, $BIS)
{
    formular('', 'formx');
    $benutzername = strtoupper(get_benutzername($benutzer_id));
    if (empty($VON) or empty($BIS)) {
        $g = new general();
        $profil_arr = $g->get_wteam_profil($benutzer_id);
        extract($profil_arr);
    }

    echo "<p class=\"zeile_ueber\">$benutzername: Termin am $datum_d eintragen.</p>";
    $js_z = "onchange=\"zeitdiff('von', 'bis', 'dauer', 'dauer_min')\"";
    if ($VON{0} == '0') {
        $VON = substr($VON, 1);
    }

    $von_arr = explode(':', $VON);
    $von_std = $von_arr[0];
    $von_min = $von_arr[1];

    ###MODIFIZIERUNG FÜR EINE STUNDE
    $vs1 = $von_std + 1;
    $BIS = "$vs1:$von_min";
    $bis_arr = explode(':', $BIS);

    dropdown_zeiten('Von', 'von', 'von', $VON, $js_z);
    echo "<br>";
    dropdown_zeiten('Bis', 'bis', 'bis', $BIS, $js_z);
    echo "<br>";
    echo "<input type=\"hidden\" id=\"dauer_min\">";
    $js_z1 = "onmouseover=\"zeitdiff('von', 'bis', 'dauer', 'dauer_min')\"";
    $dauer_min = getzeitdiff_min($VON, $BIS);
    $dauer_zeit = min_in_zeit($dauer_min);
    text_feld_inaktiv('Dauer', 'dauer', 'dauer', $dauer_zeit, 10, $js_z1);
    text_bereich('Text', 'text', '', 30, 5, 'text', '');
    text_bereich('Hinweis', 'hinweis', '', 30, 2, 'hinweis', '');
    $g_id = $_SESSION['g_id'];
    $funk1 = "termin_pruefen|/wartungsplaner/ajax?option=termin_speichern&b_id=$benutzer_id&datum=$datum_d&g_id=$g_id|rightBox1";
    $funk2 = "daj3|/wartungsplaner/ajax?option=termine_tag_tab&b_id=$benutzer_id&datum=$datum_d|rightBox1";

    $js_onsubmit = "onclick='yes_no(\"$funk1\", \"$funk2\")'";
    button('btn', 'btn', 'Termin speichern', $js_onsubmit);
    formular_ende();
}

function termin_loeschen_db($termin_dat)
{
    $b_id = session()->get('benutzer_id');
    DB::update("UPDATE GEO_TERMINE SET AKTUELL='0', ABGESAGT_AM=NOW(), ABGESAGT_VON='$b_id' WHERE DAT='$termin_dat'");
}


/*Formular fär interne Wartungen*/
function form_intern($target_id)
{
    echo "FORMULAR fär Interne Wartungen<br>";
    /*Infos vom Gerät holen und irgendwo anzeigen*/
    $js = "onchange=\"daj2('" . route('web::wartungsplaner::ajax', ['option' => 'form_extern'], false) . "',document.getElementById('rightBox'))\" ";
    dropdown_w_geraete('Wartungsteil wählen', 'w_geraet', 'wg', $js);


}


function text_feld($label, $name, $id, $size, $js = '', $wert, $class_r = 'reihe', $class_f = 'feld', $class_b = 'defbreite')
{
    echo "<div class=\"$class_r\">";
    echo "<span class=\"label\">$label</span>";
    echo "<span class=\"$class_f\">";
    echo "<input type=\"text\" class=\"$class_b\" id=\"$id\" value=\"$wert\" size=\"$size\" $js />";
    echo "</span>";
    echo "</div>";
}

/*Textbereichsfeld erstellen*/
function text_bereich($beschreibung, $name, $wert, $cols, $rows, $id, $js, $class_r = 'reihe', $class_f = 'feld', $class_b = 'defbreite')
{
    echo "<div class=\"$class_r\">";
    echo "<span class=\"label\">$beschreibung</span>";
    echo "<span class=\"$class_f\">";
    echo "<textarea id=\"$id\" name=\"$name\"  cols=\"$cols\" rows=\"$rows\" $js>$wert</textarea>\n";
    echo "</span>";
    echo "</div>";
}

function text_feld_inaktiv($label, $name, $id, $value, $size, $js = '', $class_r = 'reihe', $class_f = 'feld', $class_b = 'defbreite')
{
    echo "<div class=\"$class_r\">";
    echo "<span class=\"label\">$label</span>";
    echo "<span class=\"$class_f\">";
    echo "<input disabled type=\"text\" value=\"$value\" class=\"$class_b\" id=\"$id\" size=\"$size\" $js />";
    echo "</span>";
    echo "</div>";
}

function formular($action, $name, $class = 'formbox', $js = '')
{
    echo "<div class=\"$class\">";
    echo "<form action=\"$action\" $js>";
}

function formular_ende()
{
    echo "</form>";
    echo "</div>";
}

function text_feld1($label, $name, $id, $js = '', $wert)
{
    echo "<span><label for=\"$id\">$label</label>\n";
    echo "<input type=\"text\" name=\"$name\" id=\"$id\" value=\"$wert\" $js></span>";
}


/*Formular fär externe Wartungen*/
function form_extern($target_id)
{
    $funk1 = "partner_pruefen|" . route('web::wartungsplaner::ajax', ['option' => 'partner_save'], false) . "|rightBox";
    $funk2 = "partner_form_del_value|";

    $js_onsubmit = "onclick='yes_no(\"$funk1\", \"$funk2\")'";
    echo "<p class=\"zeile_ueber\">Such- und Eingabemaske</p>";
    formular('', 'formx');
    $js = "onkeydown=\"daj2('/wartungsplaner/ajax?option=suche_kontakt&string=' + document.getElementById('partner_name').value,document.getElementById('rightBox'))\" ";
    $js1 = "onclick=\"umkreissuche('rightBox')\"";
    $ggg = new general();
    $js_team_change = "onchange=\"daj3('/wartungsplaner/ajax?option=reg_team&team_id='+this.options[this.selectedIndex].value,'rightBox')\"";
    $ggg->dropdown_teams('Team wählen', 't_id', 't_id', '1', $js_team_change);
    text_feld('<b>Auftraggeber / Suchbegriff</b>', 'partner_name', 'partner_name', 30, $js, '');
    text_feld('Strasse', 'strasse', 'strasse', 30, '', '');
    text_feld('Hausnummer', 'nr', 'nr', 10, '', '');
    text_feld('Postleitzahl', 'plz', 'plz', 10, '', '');
    text_feld('Ort', 'ort', 'ort', 30, '', 'Berlin');
    button('btn_suche', 'btn_suche', 'Umkreissuche', $js1);
    text_feld('Land', 'land', 'land', 30, '', 'Deutschland');
    text_feld('Wohnlage', 'wohnlage', 'wohnlage', 30, '', '');
    text_feld('Telefon', 'tel', 'tel', 30, '', '');
    text_feld('Handy', 'mobil', 'mobil', 30, '', '');
    text_feld('Email', 'email', 'email', 30, '', '');
    button('btn_speichern', 'btn_speichern', 'Neuen Partner speichern', $js_onsubmit);
    formular_ende();
}

function button($name, $id, $value, $js, $class = 'knopf')
{
    echo "<input type=\"button\" id=\"$id\" name=\"$name\" value=\"$value\" $js class=\"$class\">";
}

function button_bild($name, $id, $value, $bild, $js, $class = 'knopf')
{
    echo "<button type=\"button\" id=\"$id\" name=\"$name\" value=\"$value\" $js class=\"$class\"><img src=\"$bild\"></button>";
}

function check_mietvertrag_aktuell($mv_id)
{
    $datum_heute = date("Y-m-d");
    $result = DB::select("SELECT * FROM MIETVERTRAG WHERE MIETVERTRAG_ID='$mv_id' && (MIETVERTRAG_BIS='0000-00-00' OR MIETVERTRAG_BIS='$datum_heute') && MIETVERTRAG_AKTUELL='1'");
    return !empty($result);
}


function kontakt_suche($target_id, $string)
{
    $datum_d = date("d.m.Y");
    echo "<p class=\"zeile_ueber\">Suchergebnisse, auf Datensatz klicken um zu übernehmen</p>";
    echo "<table>";
    $db_abfrage = "SELECT * FROM PERSON WHERE (PERSON_NACHNAME LIKE '$string%' OR PERSON_VORNAME LIKE '%$string%')  && PERSON_AKTUELL='1'";
    $result = DB::select($db_abfrage);
    $z = 0;
    if (!empty($result)) {
        foreach ($result as $row) {
            $p_id = $row['PERSON_ID'];
            /*MV_IDS abfragen*/
            $db_abfrage = "SELECT PERSON_MIETVERTRAG_MIETVERTRAG_ID FROM PERSON_MIETVERTRAG WHERE PERSON_MIETVERTRAG_PERSON_ID='$p_id' && PERSON_MIETVERTRAG_AKTUELL='1'";
            $result1 = DB::select($db_abfrage);
            if (!empty($result1)) {
                foreach ($result1 as $row1) {
                    $mv_id = $row1['PERSON_MIETVERTRAG_MIETVERTRAG_ID'];
                    if (!empty($mv_id) && check_mietvertrag_aktuell($mv_id) == true) {
                        $einheit_id = get_einheit_id_vom_mv($mv_id);
                        $einheit_info_arr = get_einheit_info($einheit_id);

                        extract($einheit_info_arr);
                        $z++;
                        $js = "onclick=\"setTimeout('daj3('" . route('web::wartungsplaner::ajax', ['option' => 'kos_typ_register', 'kos_typ' => 'Partner', 'kos_id' => $EIGENTUEMER_PARTNER], false) . "', 'leftBox1')', 100);";
                        $js .= "setTimeout('daj3('" . route('web::wartungsplaner::ajax', ['option' => 'unset_g_id'], false) . "', 'rightBox')', 100);";
                        $js .= "setTimeout('daj3('" . route('web::wartungsplaner::ajax', ['option' => 'wartungsteil_waehlen'], false) . "', 'leftBox')', 1000);";
                        $js .= "setTimeout('daj3('" . route('web::wartungsplaner::ajax', ['option' => 'einheit_register', 'einheit_id' => $einheit_id, 'einheit_bez' => $EINHEIT_KURZNAME], false) . "', 'rightBox')', 500);";
                        $js .= "setTimeout('daj3('" . route('web::wartungsplaner::ajax', ['option' => 'get_partner_info'], false) . "', 'rightBox')', 1000);\"";

                        $p_nachname = $row['PERSON_NACHNAME'];
                        $p_vorname = $row['PERSON_VORNAME'];
                        echo "<tr class=\"zeile$z\" $js><td>MIETER</td><td>$p_nachname $p_vorname $EINHEIT_KURZNAME</td></tr>";
                        if ($z == 2) {
                            $z = 0;
                        }
                    }
                    unset($einheit_info_arr);
                }
            }
        }
    }

    $db_abfrage = "SELECT * FROM PARTNER_LIEFERANT WHERE (PARTNER_NAME LIKE '%$string%' OR STRASSE LIKE '%$string%' OR NUMMER LIKE '%$string%' OR ORT LIKE '%$string%')  && AKTUELL='1'";
    $result = DB::select($db_abfrage);
    if (!empty($result)) {
        foreach ($result as $row) {
            $z++;
            $p_id = $row['PARTNER_ID'];
            $js_t = "onclick=\"setTimeout('daj3(\'/wartungsplaner/ajax?option=kos_typ_register&kos_typ=Partner&kos_id=$p_id\', \'rightBox\')', 100);";
            $js_t .= "setTimeout('daj3(\'/wartungsplaner/ajax?option=get_partner_info\', \'rightBox\')', 1000);";
            $js_t .= "setTimeout('daj3(\'/wartungsplaner/ajax?option=unset_g_id\', \'rightBox\')', 100);";
            $js_t .= "setTimeout('daj3(\'/wartungsplaner/ajax?option=wartungsteil_waehlen\', \'leftBox\')', 1000);";
            $js_t .= "setTimeout('daj3(\'/wartungsplaner/ajax?option=einheit_register&einheit_id=Wartungsteil&einheit_bez=Bitte waehlen\', \'rightBox\')', 500);";
            $js_t .= "setTimeout('termin_suchen_btn1(\'$datum_d\')', 1500);";
            $js_tages_ansicht = $js_t . "\"";

            $js_t1 = $js_tages_ansicht;


            $pa_name = $row['PARTNER_NAME'];
            $pa_str = $row['STRASSE'];
            $pa_nr = $row['NUMMER'];
            $pa_ort = $row['ORT'];
            echo "<tr class=\"zeile$z\" $js_tages_ansicht $js_t1><td>PARTNER</td><td>$pa_name $pa_str $pa_nr $pa_ort</td></tr>";

            if ($z == 2) {
                $z = 0;
            }
        }
    }

    $db_abfrage = "SELECT * FROM EINHEIT WHERE EINHEIT_KURZNAME LIKE '%$string%'  && EINHEIT_AKTUELL='1'";
    $result = DB::select($db_abfrage);
    $datum_d = date("d.m.Y");
    if (!empty($result)) {
        $z = 0;
        foreach ($result as $row) {
            $z++;
            $einheit_id = $row['EINHEIT_ID'];
            $einheit_bez = $row['EINHEIT_KURZNAME'];
            $einheit_info_arr = get_einheit_info($einheit_id);
            $mietername = $einheit_info_arr['MIETER'];
            $haus_str = $einheit_info_arr['HAUS_STRASSE'];
            $haus_nr = $einheit_info_arr['HAUS_NUMMER'];
            $e_lage = $einheit_info_arr['EINHEIT_LAGE'];
            $objekt_id = $einheit_info_arr['OBJEKT_ID'];
            $eigentuemer_p_id = $einheit_info_arr['EIGENTUEMER_PARTNER'];

            $g_id_arr = get_g_id_arr('Partner', $eigentuemer_p_id, $einheit_id);
            if (!empty($g_id_arr)) {
                for ($a = 0; $a < count($g_id_arr); $a++) {
                    $g_id = $g_id_arr[$a]['GERAETE_ID'];

                    $js_t = "onclick=\"setTimeout('daj3(\'/wartungsplaner/ajax?option=kos_typ_register&kos_typ=Partner&kos_id=$eigentuemer_p_id\', \'rightBox\')', 100);";
                    $js_t .= "setTimeout('daj3(\'/wartungsplaner/ajax?option=einheit_register&einheit_id=$einheit_id&einheit_bez=$einheit_bez\', \'rightBox\')', 850);";
                    $js_t .= "setTimeout('daj3(\'/wartungsplaner/ajax?option=get_partner_info\', \'rightBox\')', 1000);";
                    $js_t .= "setTimeout('daj3(\'/wartungsplaner/ajax?option=unset_g_id\', \'rightBox\')', 100);";
                    $js_t .= "setTimeout('daj3(\'/wartungsplaner/ajax?option=wartungsteil_waehlen&g_id=$g_id\', \'leftBox\')', 1000);";
                    $js_t .= "setTimeout('daj3(\'/wartungsplaner/ajax?option=get_datum_lw&g_id=$g_id\', \'lw_datum\')', 1500);";
                    $js_t .= "setTimeout('termin_suchen_btn1(\'$datum_d\')', 1500);";
                    $js_tages_ansicht = $js_t . "\"";

                    echo "<tr $js_tages_ansicht class=\"zeile$z\"><td>Einheit</td><td>$row[EINHEIT_KURZNAME] $mietername $haus_str $haus_nr $e_lage $objekt_id Gerät:$g_id</td></tr>";
                }
            } else {
                echo "<tr class=\"zeile$z\"><td>Einheit</td><td>$row[EINHEIT_KURZNAME] $mietername $haus_str $haus_nr, $e_lage - Kein Gerät</td></tr>";
            }
            if ($z == 2) {
                $z = 0;
            }
        }
    }
    echo "</table>";
}


function str_suche($target_id, $string)
{
    $datum_d = date("d.m.Y");
    echo "<p class=\"zeile_ueber\">Suchergebnisse, auf Datensatz klicken um zu übernehmen</p>";
    echo "<table>";
    $db_abfrage = "SELECT * FROM PERSON WHERE (PERSON_NACHNAME LIKE '$string%' OR PERSON_VORNAME LIKE '%$string%')  && PERSON_AKTUELL='1'";
    $result = DB::select($db_abfrage);
    $z = 0;
    if (!empty($result)) {
        foreach ($result as $row) {
            $p_id = $row['PERSON_ID'];
            /*MV_IDS abfragen*/
            $db_abfrage = "SELECT PERSON_MIETVERTRAG_MIETVERTRAG_ID FROM PERSON_MIETVERTRAG WHERE PERSON_MIETVERTRAG_PERSON_ID='$p_id' && PERSON_MIETVERTRAG_AKTUELL='1'";
            $result1 = DB::select($db_abfrage);
            if (!empty($result1)) {
                foreach ($result1 as $row1) {
                    $mv_id = $row1['PERSON_MIETVERTRAG_MIETVERTRAG_ID'];
                    if (!empty($mv_id)) {
                        $einheit_id = get_einheit_id_vom_mv($mv_id);
                        $einheit_info_arr = get_einheit_info($einheit_id);
                        extract($einheit_info_arr);
                        $z++;
                        $js = "onclick=\"setTimeout('daj3(\'/wartungsplaner/ajax?option=kos_typ_register&kos_typ=Partner&kos_id=$EIGENTUEMER_PARTNER\', \'leftBox1\')', 100);";
                        $js = $js . "setTimeout('daj3(\'/wartungsplaner/ajax?option=wartungsteil_waehlen\', \'leftBox\')', 1000);";
                        $js = $js . "setTimeout('daj3(\'/wartungsplaner/ajax?option=get_partner_info\', \'rightBox\')', 1000);\"";

                        echo "<tr class=\"zeile$z\" $js><td>MIETER</td><td>$row[PERSON_NACHNAME] $row[PERSON_VORNAME] $EINHEIT_KURZNAME</td></tr>";
                        if ($z == 2) {
                            $z = 0;
                        }
                    }
                    unset($einheit_info_arr);
                }
            }
        }
    }

    $db_abfrage = "SELECT * FROM PARTNER_LIEFERANT WHERE (PARTNER_NAME LIKE '%$string%' OR STRASSE LIKE '%$string%' OR NUMMER LIKE '%$string%' OR ORT LIKE '%$string%')  && AKTUELL='1'";
    $result = DB::select($db_abfrage);
    if (!empty($result)) {
        foreach ($result as $row) {
            $z++;
            $p_id = $row['PARTNER_ID'];
            $js_t = "onclick=\"setTimeout('daj3(\'/wartungsplaner/ajax?option=kos_typ_register&kos_typ=Partner&kos_id=$p_id\', \'rightBox\')', 100);";
            $js_t = $js_t . "setTimeout('daj3(\'/wartungsplaner/ajax?option=get_partner_info\', \'rightBox\')', 1000);";
            $js_t = $js_t . "setTimeout('daj3(\'/wartungsplaner/ajax?option=wartungsteil_waehlen\', \'leftBox\')', 1000);";
            $js_t = $js_t . "setTimeout('termin_suchen_btn1(\'$datum_d\')', 1500);";
            $js_tages_ansicht = $js_t . "\"";


            echo "<tr class=\"zeile$z\" $js_tages_ansicht><td>PARTNER</td><td>$row[PARTNER_NAME] $row[STRASSE] $row[NUMMER] $row[ORT]</td></tr>";

            if ($z == 2) {
                $z = 0;
            }
        }
    }

    $db_abfrage = "SELECT * FROM EINHEIT WHERE EINHEIT_KURZNAME LIKE '%$string%'  && EINHEIT_AKTUELL='1'";
    $result = DB::select($db_abfrage);
    $datum_d = date("d.m.Y");
    if (!empty($result)) {
        foreach ($result as $row) {
            $einheit_id = $row['EINHEIT_ID'];
            $einheit_info_arr = get_einheit_info($einheit_id);
            $mietername = $einheit_info_arr['MIETER'];
            $haus_str = $einheit_info_arr['HAUS_STRASSE'];
            $haus_nr = $einheit_info_arr['HAUS_NUMMER'];
            $e_lage = $einheit_info_arr['EINHEIT_LAGE'];
            $objekt_id = $einheit_info_arr['OBJEKT_ID'];
            $eigentuemer_p_id = $einheit_info_arr['EIGENTUEMER_PARTNER'];

            $g_id_arr = get_g_id_arr('Partner', $eigentuemer_p_id, $einheit_id);
            if (!empty($g_id_arr)) {
                for ($a = 0; $a < count($g_id_arr); $a++) {
                    $g_id = $g_id_arr[$a]['GERAETE_ID'];

                    $js_t = "onclick=\"setTimeout('daj3(\'/wartungsplaner/ajax?option=kos_typ_register&kos_typ=Partner&kos_id=$eigentuemer_p_id\', \'rightBox\')', 100);";
                    $js_t = $js_t . "setTimeout('daj3(\'/wartungsplaner/ajax?option=get_partner_info\', \'rightBox\')', 1000);";
                    $js_t = $js_t . "setTimeout('daj3(\'/wartungsplaner/ajax?option=wartungsteil_waehlen&g_id=$g_id\', \'leftBox\')', 1000);";
                    $js_t = $js_t . "setTimeout('daj3(\'/wartungsplaner/ajax?option=get_datum_lw&g_id=$g_id\', \'lw_datum\')', 1500);";
                    $js_t = $js_t . "setTimeout('termin_suchen_btn1(\'$datum_d\')', 1500);";
                    $js_tages_ansicht = $js_t . "\"";

                    echo "<tr $js_tages_ansicht><td>Einheit</td><td>$row[EINHEIT_KURZNAME] $mietername $haus_str $haus_nr $e_lage $objekt_id G:$g_id</td></tr>";
                }
            } else {
                echo "<tr><td>Einheit</td><td>$row[EINHEIT_KURZNAME] $mietername $haus_str $haus_nr $e_lage $objekt_id</td></tr>";
            }
        }
        #return $my_array;
    }
    echo "</table>";
}

function get_g_id_arr($kos_typ, $kos_id, $einheit_id)
{
    $einheit_info_arr = get_einheit_info($einheit_id);
    $einheit_kurzname = $einheit_info_arr['EINHEIT_KURZNAME'];
    $db_abfrage = "SELECT GERAETE_ID FROM W_GERAETE WHERE AKTUELL='1' && LAGE_RAUM='$einheit_kurzname' && KOSTENTRAEGER_TYP='$kos_typ' && KOSTENTRAEGER_ID='$kos_id'";
    $result = DB::select($db_abfrage);
    return $result;
}


function form_inaktiv($kos_typ, $kos_id)
{
    if ($kos_typ == 'Partner') {
        $anschrift = get_partner_anschrift($kos_id);
        $kos_bez = get_partner_name($kos_id);
    }
    formular('', 'inaktiv');
    get_entfernung_km($anschrift);
    echo "<h1>Terminplanung bei $kos_bez</h1>";
    formular_ende();
}

function get_partner_name($partner_id)
{
    $result = DB::select("SELECT * FROM PARTNER_LIEFERANT WHERE PARTNER_ID='$partner_id' && AKTUELL = '1'");
    if (!empty($result)) {
        return $result[0]['PARTNER_NAME'];
    } else {
        return 'unbekannt';
    }
}

function get_partner_id($partner_name, $str, $nr, $plz)
{
    $result = DB::select("SELECT PARTNER_ID FROM PARTNER_LIEFERANT WHERE PARTNER_NAME='$partner_name' && STRASSE='$str' && NUMMER='$nr' && PLZ='$plz' && AKTUELL = '1'");
    if (!empty($result)) {
        return $result[0]['PARTNER_ID'];
    }
}

function get_gruppe_id($gruppen_bez)
{
    $result = DB::select("SELECT GRUPPE_ID FROM W_GRUPPE WHERE GRUPPE='$gruppen_bez' && AKTUELL = '1' ORDER BY GRUPPE ASC LIMIT 0,1");
    if (!empty($result)) {
        return $result[0]['GRUPPE_ID'];
    }
}


function get_gruppen_bez($gruppe_id)
{
    $result = DB::select("SELECT GRUPPE FROM W_GRUPPE WHERE GRUPPE_ID='$gruppe_id' && AKTUELL = '1' ORDER BY GRUPPE ASC LIMIT 0,1");
    if (!empty($result)) {
        return $result[0]['GRUPPE'];
    }
}

function get_partner_arr()
{
    $result = DB::select("SELECT * FROM PARTNER_LIEFERANT WHERE AKTUELL = '1' ORDER BY PARTNER_NAME ASC");
    return $result;
}

function dropdown_partner_vorwahl($p_id, $arr, $label, $name, $id, $js, $class_r = 'reihe', $class_f = 'feld')
{
    if (!is_array($arr)) {
        die('Keine Partner gefunden gefunden, bitte erst Partner erfassen!');
    } else {
        $anz = count($arr);
        echo "<div class=\"$class_r\">";
        echo "<span class=\"label\">$label</span>";
        echo "<span class=\"$class_f\">";
        echo "<select name=\"$name\" size=\"1\" id=\"$id\" $js>\n";
        echo "<option value=\"\" selected>Bitte wählen</option>\n";
        for ($a = 0; $a < $anz; $a++) {
            $partner_id = $arr[$a]['PARTNER_ID'];
            $partner_name = $arr[$a]['PARTNER_NAME'];
            if ($p_id == $partner_id) {
                echo "<option value=\"$partner_id\" selected>$partner_name</option>\n";
            } else {
                echo "<option value=\"$partner_id\">$partner_name</option>\n";
            }
        }
        echo "</select>\n";
        echo "</span>";
        echo "</div>";
    }
}

function get_partner_anschrift($partner_id)
{
    $result = DB::select("SELECT *  FROM PARTNER_LIEFERANT WHERE PARTNER_ID='$partner_id' && AKTUELL = '1'");
    if (!empty($result)) {
        $row = $result[0];
        return $row['STRASSE'] . ' ' . $row['NUMMER'] . ', ' . $row['PLZ'] . ' ' . $row['ORT'];
    } else {
        return 'unbekannt';
    }
}


function get_entfernung_km($start, $destination = START_ADRESSE)
{
    echo "ENTFERNUNG wird berechnet!";
    $url = "http://maps.google.com/maps/api/directions/xml?origin=$start &destination=$destination &sensor=false&language=de";
    $xml = simplexml_load_file($url);
    sleep(2);
    if ($xml === FALSE) {
        die('Keine route');
    } else {

        $status = $xml->status;
        if ($status == 'OK') {
            $start_a = $xml->route->leg->start_address;
            $end_a = $xml->route->leg->end_address;
            $km = $xml->route->leg->distance->text;
            $fahrzeit = $xml->route->leg->duration->text;

            echo "<b>$start_a<br>$end_a<br>ENTFERNUNG $km<br>Fahrzeit: $fahrzeit</b>";
            return $km;
        }
    }
}

function get_km_osm($s_lon, $s_lat, $e_lon, $e_lat)
{
    $url = "http://open.mapquestapi.com/directions/v0/route?outFormat=xml&from=$s_lon,$s_lat&to=$e_lon,$e_lat&callback=renderNarrative&unit=k";
    $xml = simplexml_load_file("$url");
    if ($xml === FALSE) {
        die();
    } else {
        $km = $xml->route->distance / 1;
        return $km;
    }
}

function get_lat_lon_db($str, $nr, $plz, $ort)
{
    $db_abfrage = "SELECT DAT, LAT, LON FROM GEO_LON_LAT WHERE STR='$str' && NR='$nr' && PLZ='$plz' && ORT='$ort' LIMIT 0,1";
    $result = DB::select($db_abfrage);
    if (!empty($result)) {
        $row = $result[0];
        $lat = $row['LAT'];
        $lon = $row['LON'];
        $dat = $row['DAT'];
        return "$lat, $lon, $dat, $str, $nr, $plz, $ort, DB";
    } else {

    }

}


function get_lat_lon_db_osm($str, $nr, $plz, $ort)
{
    if (session()->has('lon_lats')) {
        if (is_array(session()->get('lon_lats'))) {
            if (array_key_exists("$str,$nr, $plz, $ort", session()->get('lon_lats'))) {
                return session()->get("lon_lats.$str,$nr, $plz, $ort");
            }
        }
    }

    $lat_lon = get_lat_lon_db($str, $nr, $plz, $ort);
    if ($lat_lon) {
        session()->put("lon_lats.$str,$nr, $plz, $ort", $lat_lon);
        return $lat_lon;
    }
    $url = "http://maps.google.com/maps/api/geocode/xml?address=$str+$nr+$plz+$ort&sensor=false";
    $xml = simplexml_load_file("$url");
    sleep(1);
    if ($xml === FALSE) {
        die();
    } else {
        $status = $xml->status;
        if ($status == 'OK') {
            $lat = $xml->result->geometry->location->lat;
            $lon = $xml->result->geometry->location->lng;

            $quelle = 'GoogleMaps';
        } else {
            #	echo "google NOK $xml->status<br>$url<br>";
            /*Über den Routenplaner von Google suchen*/
            $url = "http://maps.google.com/maps/api/directions/xml?origin=" . "$str $nr, $plz $ort " . " &destination=Sansibarstr 12, 13351 Berlin&sensor=false";
            $xml = simplexml_load_file("$url");
            if ($xml->status == 'OK') {
                $lat = $xml->route->leg->step->start_location->lat;
                $lon = $xml->route->leg->step->start_location->lng;
                #echo "GGG: $lat, $lon, GoogleMaps";
                $quelle = 'GoogleMapsDirections';
            }
        }
    }

    if (empty($lat) && empty($lon)) {
        $str_uml = umlaute_anpassen($str);
        $url = "http://nominatim.openstreetmap.org/search?q=$str_uml+$nr+$plz&format=xml";
        $xml = simplexml_load_file($url);
        $vars = get_object_vars($xml->place);
        $lat = $vars['@attributes']['lat'];
        $lon = $vars['@attributes']['lon'];
        if (!empty($lat) && !empty($lon)) {
            $quelle = 'Openstreetmap';
        }
    }

    if (!empty($lat) && !empty($lon)) {
        if (!check_str($str, $nr, $plz, $ort)) {
            DB::insert("INSERT INTO GEO_LON_LAT VALUES (NULL, '$str', '$nr', '$plz', '$ort','$lon','$lat','$quelle','1')");
        }
    }

    $lat_lon = get_lat_lon_db($str, $nr, $plz, $ort);
    if (!empty($lat_lon)) {
        session()->put("lon_lats.$str,$nr, $plz, $ort", $lat_lon);
        return $lat_lon;
    }
    return null;
}


/*Hier nur Adressdaten von Google*/
#Docu hier
#http://code.google.com/intl/de-DE/apis/maps/documentation/javascript/v2/services.html#Geocoding_Object
function get_navi_route($s_str, $s_nr, $s_plz, $s_ort, $z_str, $z_nr, $z_plz, $z_ort)
{
    $s_nr = str_replace(',', '', $s_nr);
    $z_nr = str_replace(',', '', $z_nr);
    $s_lat_lon = get_lat_lon_db($s_str, $s_nr, $s_plz, $s_ort);
    $z_lat_lon = get_lat_lon_db($z_str, $z_nr, $z_plz, $z_ort);
    /*Start LON und LAT*/
    $s_arr = explode(',', $s_lat_lon);
    $s_lat = $s_arr['0'];
    $s_lon = $s_arr['1'];
    unset($s_arr);
    /*Ziel LON und LAT*/
    $z_arr = explode(',', $z_lat_lon);
    $z_lat = $z_arr['0'];
    $z_lon = $z_arr['1'];
    unset($z_arr);


    $url = "http://open.mapquestapi.com/directions/v0/route?outFormat=xml&from=$s_lat,$s_lon&to=$z_lat,$z_lon&callback=renderNarrative&unit=k";
    echo "<a href=\"$url\">Route anzeigen XML</a>";
}


function kos_typ_info_anzeigen($kos_typ, $kos_id)
{
    if ($kos_typ == 'Partner' or $kos_typ == 'PARTNER_LIEFERANT') {
        $g = new general();
        $g->get_partner_info($kos_id);
        echo "<p class=\"zeile_hinweis\">";
        echo "$g->partner_name $g->partner_strasse $g->partner_hausnr $g->partner_plz $g->partner_ort";
        echo "</p>";
        alle_details_anzeigen('PARTNER_LIEFERANT', $kos_id);
        form_detail_hinzu('PARTNER_LIEFERANT', $kos_id);
    } else {
        echo "$kos_typ $kos_id keine Details";
    }
}

function finde_detail_inhalt($tab, $id, $detail_name)
{
    $db_abfrage = "SELECT DETAIL_INHALT FROM DETAIL WHERE DETAIL_ZUORDNUNG_TABELLE = '$tab' && DETAIL_NAME = '$detail_name' && DETAIL_ZUORDNUNG_ID = '$id' && DETAIL_AKTUELL = '1' ORDER BY DETAIL_DAT DESC LIMIT 0 , 1";
    $result = DB::select($db_abfrage);
    foreach ($result as $row)
        return $row['DETAIL_INHALT'];
}

function finde_detail_kontakt_arr($tab, $id, $hinweis = '1')
{
    if ($hinweis == 1) {
        $db_abfrage = "SELECT DETAIL_NAME, DETAIL_INHALT FROM DETAIL WHERE DETAIL_ZUORDNUNG_TABELLE = '$tab' && (DETAIL_NAME LIKE '%tel%'or DETAIL_NAME LIKE '%fax%' or DETAIL_NAME LIKE '%mobil%' or DETAIL_NAME LIKE '%handy%' OR DETAIL_NAME LIKE '%mail%' OR DETAIL_NAME LIKE '%hinweis%') && DETAIL_ZUORDNUNG_ID = '$id' && DETAIL_AKTUELL = '1' ORDER BY DETAIL_NAME ASC";
    } else {
        $db_abfrage = "SELECT DETAIL_NAME, DETAIL_INHALT FROM DETAIL WHERE DETAIL_ZUORDNUNG_TABELLE = '$tab' && (DETAIL_NAME LIKE '%tel%'or DETAIL_NAME LIKE '%fax%' or DETAIL_NAME LIKE '%mobil%' or DETAIL_NAME LIKE '%handy%' OR DETAIL_NAME LIKE '%mail%') && DETAIL_ZUORDNUNG_ID = '$id' && DETAIL_AKTUELL = '1' ORDER BY DETAIL_NAME ASC";
    }
    $result = DB::select($db_abfrage);
    return $result;
}


function alle_gegen_alle($gruppe_id = '1')
{
    session()->put('gruppe_id', $gruppe_id);
    echo "<b>ANFANG" . date("H:i:s") . "</b>";
    echo "<br>";
    $arr = DB::select("SELECT `GERAETE_ID`, LAGE_RAUM AS EINBAUORT, HERSTELLER, BEZEICHNUNG, KOSTENTRAEGER_TYP, KOSTENTRAEGER_ID, `INTERVAL_M`, DATE_FORMAT(NOW(),'%Y-%m-%d') as HEUTE, DATE_FORMAT(DATE_ADD(DATE_FORMAT(NOW(),'%Y-%m-%d'), INTERVAL -INTERVAL_M MONTH),'%Y-%m-%d') AS L_WART_FAELLIG, (SELECT DATUM FROM GEO_TERMINE WHERE GERAETE_ID=W_GERAETE.GERAETE_ID && AKTUELL='1' ORDER BY DATUM DESC LIMIT 0,1) AS L_WART FROM `W_GERAETE` WHERE `AKTUELL`='1' && GRUPPE_ID='$gruppe_id' && 
GERAETE_ID NOT IN
(SELECT GERAETE_ID FROM GEO_TERMINE WHERE AKTUELL='1' && (DATUM>=DATE_FORMAT(DATE_ADD(DATE_FORMAT(NOW(),'%Y-%m-%d'), INTERVAL -(INTERVAL_M-2) MONTH),'%Y-%m-%d') AND DATUM <= DATE_FORMAT(NOW(),'%Y-%m-%d')) )
AND
GERAETE_ID NOT IN
(SELECT GERAETE_ID FROM GEO_TERMINE WHERE AKTUELL='1' && DATUM>DATE_FORMAT(NOW(),'%Y-%m-%d')) ORDER BY `L_WART_FAELLIG` ASC, INTERVAL_M ASC, KOSTENTRAEGER_TYP ASC, KOSTENTRAEGER_ID ASC
");

    if (!empty($arr)) {
        if (session()->has('kreuz')) {
            session()->forget('kreuz');
        }
        session()->put('kreuz', []);

        $numrows = count($arr);
        $z1 = 0;
        for ($a = 0; $a < $numrows; $a++) {

            $kos_typ = $arr[$a]['KOSTENTRAEGER_TYP'];
            $kos_id = $arr[$a]['KOSTENTRAEGER_ID'];

            /*Wenn Fremd, kein Mieter*/
            if ($kos_typ == 'Partner') {
                $g = new general();
                $g->get_partner_info($kos_id);
                $lat_lon_db_start = get_lat_lon_db_osm($g->partner_strasse, $g->partner_hausnr, $g->partner_plz, $g->partner_ort);
            }

            /*Alle anderen durchlaufen*/
            for ($b = 0; $b < $numrows; $b++) {
                $g1 = new general();
                $kos_id2 = $arr[$b]['KOSTENTRAEGER_ID'];
                $g1->get_partner_info($kos_id2);
                $lat_lon_db_ziel = get_lat_lon_db_osm($g1->partner_strasse, $g1->partner_hausnr, $g1->partner_plz, $g1->partner_ort);

                if ($lat_lon_db_start != $lat_lon_db_ziel) {

                    if (!in_array("$lat_lon_db_start|$lat_lon_db_ziel", session()->get('kreuz'))) {
                        session()->push('kreuz', "$lat_lon_db_start|$lat_lon_db_ziel");
                        $start_arr = explode(',', $lat_lon_db_start);
                        $von_str = "$g->partner_strasse $g->partner_hausnr";


                        $ziel_arr = explode(',', $lat_lon_db_ziel);
                        $bis_str = "$g1->partner_strasse $g1->partner_hausnr";

                        $en = new general();
                        $en->get_fahrzeit_entf($lat_lon_db_start, $lat_lon_db_ziel);
                        echo "$a.$z1 $von_str bis $bis_str = $en->km km | Fahrzeit $en->fahrzeit<br>";
                        $z1++;
                    }

                }

            }

            #	die("ENDE $a $z1".date("H:i"));
        }//end while
        echo "<b>ENDE:";
        echo date("H:i:s");
        echo '<pre>';
        print_r(session()->get('kreuz'));
        session()->forget('kreuz');
    } else {
        echo "Keine zu erleigen";
    }


}//end alle gegen alle

function alle_details_anzeigen($tab, $tab_id)
{
    $result = DB::select("SELECT * FROM DETAIL WHERE DETAIL_AKTUELL='1' && DETAIL_ZUORDNUNG_TABELLE='$tab' && DETAIL_ZUORDNUNG_ID='$tab_id' ORDER BY DETAIL_NAME ASC");
    if (!empty($result)) {
        echo "<hr>";
        foreach($result as $row) {
            echo '<p class="zeile_detail"><b>' . $row['DETAIL_NAME'] . '</b>: ' . $row['DETAIL_INHALT'] . '</p>';
        }
        echo "<hr>";
    }
}

function alle_details_anzeigen_br($tab, $tab_id)
{
    $result = DB::select("SELECT * FROM DETAIL WHERE DETAIL_AKTUELL='1' && DETAIL_ZUORDNUNG_TABELLE='$tab' && DETAIL_ZUORDNUNG_ID='$tab_id' ORDER BY DETAIL_NAME ASC");
    if (!empty($result)) {
        echo "<hr>";
        foreach($result as $row) {
            echo '<b>' . $row['DETAIL_NAME'] . '</b>: ' . $row['DETAIL_INHALT'] . '<br>';
        }
        echo "<hr>";
    }
}

function form_detail_hinzu2($tab, $tab_id)
{
    $funk1 = "detail_speichern2|" . route('web::wartungsplaner::ajax', ['option' => 'detail_speichern2', 'tab' => $tab, 'tab_id' => $tab_id]) . "|rightBox1|$tab";
    $funk2 = "detail_form_del_value|";

    $js_onsubmit = "onclick='yes_no(\"$funk1\", \"$funk2\")'";
    formular('', 'formx');
    text_feld('Detailbezeichnung', $tab . 'detail_name', $tab . 'detail_name', 30, '', '');
    $js_on_keyup = "onkeyup='text_kuerzen(\"$tab.detail_inhalt\", \"400\")'";
    text_bereich('Detailinhalt (max. 400 Zeichen)', $tab . 'detail_inhalt', '', 35, 5, $tab . 'detail_inhalt', $js_on_keyup);
    button('btn_speichern', 'btn_speichern', 'Detail speichern', $js_onsubmit);
    formular_ende();
}

function form_detail_hinzu($tab, $tab_id)
{
    $funk1 = "detail_speichern|" . route('web::wartungsplaner::ajax', ['option' => 'detail_speichern']) . "|rightBox";
    $funk2 = "detail_form_del_value|";

    $js_onsubmit = "onclick='yes_no(\"$funk1\", \"$funk2\")'";
    formular('', 'formx');
    text_feld('Detailbezeichnung', 'detail_name', 'detail_name', 30, '', '');
    $js_on_keyup = "onkeyup='text_kuerzen(\"detail_inhalt\", \"400\")'";
    text_bereich('Detailinhalt (max. 400 Zeichen)', 'detail_inhalt', '', 35, 5, 'detail_inhalt', $js_on_keyup);
    button('btn_speichern', 'btn_speichern', 'Detail speichern', $js_onsubmit);
    formular_ende();
}


function save_to_db($table, $values, $id_spalte = 'x')
{
    if ($id_spalte != 'x') {
        $id = last_id2($table, $id_spalte) + 1;
        session()->put('dat', $id);
        $db_abfrage = "INSERT INTO $table VALUES (NULL, '$id', $values)";
    } else {
        $db_abfrage = "INSERT INTO $table VALUES (NULL, $values)";
    }
    DB::insert($db_abfrage);

    session()->put($id_spalte, $id);
    return true;
}


function deactivate_wteil($g_id)
{
    DB::update("UPDATE W_GERAETE SET AKTUELL='0' WHERE GERAETE_ID='$g_id'");
    return true;
}

function partner_2_session($kos_typ, $kos_id)
{
    session()->put('kos_typ', $kos_typ);
    session()->put('kos_id', $kos_id);
}

function form_wartungsteil($kos_typ, $kos_id)
{
    $arr = get_wartungsteile_arr($kos_typ, $kos_id);
    $js_neues_teil = "onclick=\"daj3('" . route('web::wartungsplaner::ajax', ['option' => 'wartungsteil erfassen']) . "','leftBox');\"";
    formular('', 'formx1');

    if (!empty($arr)) {
        $datum_ab = date("d.m.Y");
        $datum_ab = tage_plus_wp($datum_ab, 1);
        $anz = count($arr);
        if (session()->has('einheit_bez')) {
            echo "<p class=\"zeile_hinweis_rot\">EINHEIT: " . session()->get('einheit_bez') . "</p>";
        }
        if ($anz == 1) {
            echo "<p class=\"zeile_hinweis\">Kunde hat nur ein Wartungsteil!!!</p>";
        } else {
            /*Mehrere Geräte*/
            echo "<p class=\"zeile_hinweis\">Kunde hat mehrere Wartungsteile!!!</p>";
        }
        button('neues_wt', 'neues_wt', 'Neues Wartungsteil erfassen', $js_neues_teil, 'button');
        echo "<p class=\"zeile_ueber\">BITTE DAS WARTUNGSTEIL WÄHLEN</p>";
        datum_feld2('Terminsuche ab dem', 'datum_ab', 'datum_ab', $datum_ab);
        $js = "onchange=\"termin_dauer_aendern('termin_dauer'); termin_suchen_btn1()\"";

        if (session()->has('termin_dauer')) {

            session()->put('termin_dauer', 90);
            $dauer = session()->get('termin_dauer');

        } else {
            $dauer = 90;
        }
        dropdown_dauer('Termin Dauer', 'termin_dauer', 'termin_dauer', $dauer, $js, $class_r = 'reihe', $class_f = 'feld');
        $js1 = "onclick=\"termin_suchen_btn1()\"";
        button('Suchen', 'suchen', 'Termine suchen', $js1);
        echo "<br><hr>";
        $d_onchange = "onChange=\"daj3('/wartungsplaner/ajax?option=detail_geraet&tab=W_GERAETE&tab_id='+this.value,'rightBox1');daj3('/wartungsplaner/ajax?option=geraete_info_anzeigen&g_id='+this.value,'rightBox');daj3('/wartungsplaner/ajax?option=termin_suchen_neu&g_id='+this.value,'leftBox1');daj3('/wartungsplaner/ajax?option=get_datum_lw&g_id='+this.value,'lw_datum')\"";
        dropdown_wgeraet($arr, 'Wartungsteil wählen', 'g_id', 'g_id', $d_onchange);
        echo "<br><hr>";

        echo "<h3 id=\"lw_datum\" class=\"zeile_ueber\">";
        if (session()->has('g_id')) {
            echo get_datum_lw(session()->get('g_id'));
            echo get_datum_nw(session()->get('g_id'));
        }
        echo "</h3><hr>";

        #}
        /*Keine Geräte, i.d.R nach dem Erfassen eines Neukunden*/
    } else {
        echo "<p class=\"zeile_ueber\">Wartungsteil erfassen</p>";
        form_wartungsteil_erfassen($kos_typ, $kos_id);
    }
    formular_ende();
}


function dropdown_dauer($label, $id, $name, $selected, $js, $class_r = 'reihe', $class_f = 'feld')
{
    echo "<div class=\"$class_r\">";
    echo "<span class=\"label\">$label</span>";
    echo "<span class=\"$class_f\">";
    echo "<select name=\"$name\" size=\"1\" id=\"$id\" $js>\n";
    $min = 0;
    for ($a = 0; $a < 32; $a++) {
        $min += 15;
        if ($selected == $min) {
            echo "<option value=\"$min\" selected>$min Min</option>\n";
        } else {
            echo "<option value=\"$min\">$min Min</option>\n";
        }
    }


    echo "</select>\n";
    echo "</span>";
    echo "</div>";
}


function check_termin_frei($b_id, $datum_sql, $von, $bis)
{
    $db_abfrage = "SELECT *  FROM `GEO_TERMINE` WHERE `DATUM` = '$datum_sql' AND BENUTZER_ID='$b_id' AND `AKTUELL` = '1' AND `VON` < '$bis' AND BIS > '$von'";
    $result = DB::select($db_abfrage);
    return empty($result);
}


function dropdown_wgeraet($arr, $label, $name, $id, $js, $class_r = 'reihe', $class_f = 'feld')
{

    if (!is_array($arr)) {
        #die('Keine Wartungsteile gefunden, bitte erst Wartungsteil erfassen!');
    } else {
        $anz = count($arr);
        #echo '<pre>';
        #print_r($arr);
        #die('TEST');
        $srt = new arr_multisort();
        $srt->setArray($arr);
        $srt->addColumn("LAGE_RAUM", SRT_ASC);
        unset($arr);
        $arr = $srt->sort();
        echo "<div class=\"$class_r\">";
        echo "<span class=\"label\">$label</span>";
        echo "<span class=\"$class_f\">";
        echo "<select name=\"$name\" size=\"1\" id=\"$id\" $js>\n";
        echo "<option value=\"\" selected>Bitte wählen</option>\n";
        for ($a = 0; $a < $anz; $a++) {
            $g_id = $arr[$a]['GERAETE_ID'];
            $g_bez = $arr[$a]['BEZEICHNUNG'];
            $einbauort = $arr[$a]['LAGE_RAUM'];
            if (session()->has('g_id')) {
                if ($g_id == session()->get('g_id')) {
                    echo "<option value=\"$g_id\" selected>$einbauort - $g_bez</option>\n";
                } else {
                    echo "<option value=\"$g_id\">$einbauort - $g_bez</option>\n";
                }
            } else {
                echo "<option value=\"$g_id\">$einbauort - $g_bez</option>\n";
            }
        }
        echo "</select>\n";
        echo "</span>";
        echo "</div>";
    }
}


function dropdown_monate($label, $name, $id, $js, $class_r = 'reihe', $class_f = 'feld')
{
    echo "<div class=\"$class_r\">";
    echo "<span class=\"label\">$label</span>";
    echo "<span class=\"$class_f\">";
    echo "<select name=\"$name\" size=\"1\" id=\"$id\" $js>\n";
    echo "<option value=\"0\">Einmalig</option>\n";
    for ($a = 1; $a <= 24; $a++) {
        if ($a == 12) {
            echo "<option value=\"$a\" selected>Alle $a. Mon.</option>\n";
        } else {
            echo "<option value=\"$a\">Alle $a. Mon.</option>\n";
        }
    }
    echo "</select>\n";
    echo "</span>";
    echo "</div>";

}


function dropdown_w_gruppen($arr, $label, $name, $id, $js, $class_r = 'reihe', $class_f = 'feld')
{

    if (!is_array($arr)) {
        #die('Keine Wartungsgruppen gefunden, bitte erst Wartungsgruppen erfassen!');
    } else {
        $anz = count($arr);
        echo "<div class=\"$class_r\">";
        echo "<span class=\"label\">$label</span>";
        echo "<span class=\"$class_f\">";
        echo "<select name=\"$name\" size=\"1\" id=\"$id\" $js>\n";
        echo "<option value=\"\" selected>Bitte wählen</option>\n";
        for ($a = 0; $a < $anz; $a++) {
            $g_id = $arr[$a]['GRUPPE_ID'];
            $g_bez = $arr[$a]['GRUPPE'];
            if (session()->has('vorschlag_gruppe_id') && session()->has('vorschlag_gruppe_id')) {
                if (request()->get('vorschlag_gruppe_id') == $g_id) {
                    echo "<option value=\"$g_id\" selected>$g_bez</option>\n";
                } else {
                    echo "<option value=\"$g_id\">$g_bez</option>\n";
                }
            } else {
                echo "<option value=\"$g_id\">$g_bez</option>\n";
            }
        }
        echo "</select>\n";
        echo "</span>";
        echo "</div>";
    }
}

function dropdown_w_teile_gruppe($arr, $label, $name, $id, $js, $class_r = 'reihe', $class_f = 'feld')
{
    if (!is_array($arr)) {
        #die('Keine Wartungsteile in der gewählten Gruppe gefunden, bitte manuell erfassen!');
    } else {
        $anz = count($arr);
        echo "<div class=\"$class_r\">";
        echo "<span class=\"label\">$label</span>";
        echo "<span class=\"$class_f\">";
        echo "<select name=\"$name\" size=\"1\" id=\"$id\" $js>\n";
        echo "<option value=\"\" selected>Bitte wählen</option>\n";
        for ($a = 0; $a < $anz; $a++) {
            $g_bez = $arr[$a]['BEZEICHNUNG'];
            echo "<option value=\"$g_bez\">$g_bez</option>\n";
        }
        echo "</select>\n";
        echo "</span>";
        echo "</div>";
    }
}

function dropdown_hersteller($arr, $label, $name, $id, $js, $class_r = 'reihe', $class_f = 'feld')
{
    if (!is_array($arr)) {
        #die('Keine Hersteller erfasst!');
    } else {
        $anz = count($arr);
        echo "<div class=\"$class_r\">";
        echo "<span class=\"label\">$label</span>";
        echo "<span class=\"$class_f\">";
        echo "<select name=\"$name\" size=\"1\" id=\"$id\" $js>\n";
        echo "<option value=\"\" selected>Bitte wählen</option>\n";
        for ($a = 0; $a < $anz; $a++) {
            $g_her = $arr[$a]['HERSTELLER'];
            echo "<option value=\"$g_her\">$g_her</option>\n";
        }
        echo "</select>\n";
        echo "</span>";
        echo "</div>";
    }
}


function form_wartungsteil_erfassen($kos_typ, $kos_id)
{
    $arr = get_wartungsgruppen_arr();
    formular('', 'formx');
    echo "<p class=\"zeile_ueber\">Neues Wartungsteil erfassen</p>";
    $js = "onchange=\"drop_change_check('w_gruppe_id', 'gbez');lade_dropdown('w_gruppe_id', 'g_bez', 'g_hersteller', '" . route('web::wartungsplaner::ajax', ['option' => 'get_hersteller_gruppe'], false) . "')\"";
    dropdown_w_gruppen($arr, 'Wartungsgruppe wählen oder ...', 'w_gruppe_id', 'w_gruppe_id', $js);
    $js_gbez = "onkeyup='text_kuerzen(\"gbez\", \"50\")'";
    text_feld('Gruppenbezeichnung eingeben', 'gbez', 'gbez', 30, $js_gbez, '');

    $arr = get_wartungsteile_hersteller_arr();
    $js = " onchange=\"drop_change_check('g_hersteller', 'hersteller');lade_dropdown('g_hersteller', 'hersteller', 'modell', '" . route('web::wartungsplaner::ajax', ['option' => 'get_hersteller_modelle'], false) . "')\"";
    dropdown_hersteller($arr, 'Hersteller wählen oder ...', 'g_hersteller', 'g_hersteller', $js);
    $js_hersteller = "onkeyup='text_kuerzen(\"hersteller\", \"50\")'";
    text_feld('Hersteller', 'hersteller', 'hersteller', 30, $js_hersteller, '');

    $arr = get_wartungsteile_gruppe_arr(1);
    $js = " onchange=\"drop_change_check('modell', 'modell_bez')\"";
    dropdown_w_teile_gruppe($arr, 'Mögliche Bezeichnung/Modell wählen oder ...', 'modell', 'modell', $js);
    $js_modell = "onkeyup='text_kuerzen(\"modell_bez\", \"50\")'";
    text_feld('Bezeichnung eingeben', 'modell_bez', 'modell_bez', 30, $js_modell, '');

    $js_baujahr = "onkeyup='text_kuerzen(\"baujahr\", \"4\")'";
    text_feld('Baujahr eingeben (yyyy)', 'baujahr', 'baujahr', 10, $js_baujahr, '');

    /*Lage der Therme, bei Objekten Wohnugsnummer, bei Externen der Raum*/
    $js_lage = "onkeyup='text_kuerzen(\"lage_raum\", \"50\")'";
    text_feld('Einbauort (ext)/Wohnungsnummer (int) eingeben (50)', 'lage_raum', 'lage_raum', 10, $js_lage, '');
    dropdown_monate('Wartungsintervall', 'wartungsintervall', 'wartungsintervall', '');

    echo "<br><br><hr>";
    $onclick = "onclick=\"daj3('" . route('web::wartungsplaner::ajax', ['option' => 'form_abweichende_r_anschrift', false]) . "','rightBox')\"";
    echo "<label for=\"rech_ansch_ab_ja\">Abweichende Rechnungsanschrift JA</label>";
    echo "<input type=\"radio\" id=\"rech_ansch_ab_ja\" name=\"rech_ansch_ab_ja\" $onclick />";

    $onclick1 = "onclick=\"daj3('" . route('web::wartungsplaner::ajax', ['option' => 'get_partner_info_r_an'], false) . "','rightBox')\" ";
    echo "<label for=\"rech_ansch_ab_no\">Abweichende Rechnungsanschrift NEIN</label>";
    echo "<input type=\"radio\" id=\"rech_ansch_ab_no\" name=\"rech_ansch_ab_ja\" $onclick1/>";
    echo "<hr>";

    $funk1 = "wgeraet_pruefen|" . route('web::wartungsplaner::ajax', ['option' => 'wgeraet_save'], false) . "|leftBox";
    $funk2 = "w_geraet_form_del|";
    $js_onsubmit = "onclick='yes_no(\"$funk1\", \"$funk2\")'";
    button('btn_speichern', 'btn_speichern', 'Neues Wartungsteil speichern', $js_onsubmit);
    formular_ende();

}

function form_wt_aendern($g_id)
{
    if (empty($g_id)) {
        echo "Gerät unbekannt";
    } else {

        $gereate_info_arr = geraete_info_arr($g_id);
        if (empty($gereate_info_arr)) {
            die('Gerät unbekannt xsdsdsd');
        } else {
            extract($gereate_info_arr[0]);
        }


        $arr = get_wartungsgruppen_arr();
        formular('', 'formx');
        echo "<input type=\"hidden\" value=\"$g_id\">";
        $p_info = get_partner_name($KOSTENTRAEGER_ID);
        echo "<p class=\"zeile_hinweis\">Wartungsteil ändern / $p_info / $LAGE_RAUM</p>";
        $js = " onchange=\"drop_change_check('w_gruppe_id', 'gbez');lade_dropdown('w_gruppe_id', 'g_bez', 'g_hersteller', '" . route('web::wartungsplaner::ajax', ['option' => 'get_hersteller_gruppe'], false) . "')\"";
        dropdown_w_gruppen($arr, 'Wartungsgruppe wählen oder ...', 'w_gruppe_id', 'w_gruppe_id', $js);
        $js_gbez = "onkeyup='text_kuerzen(\"gbez\", \"50\")'";
        $gruppen_bez = get_gruppen_bez($GRUPPE_ID);
        text_feld('Gruppenbezeichnung eingeben', 'gbez', 'gbez', 30, $js_gbez, $gruppen_bez);

        $arr = get_wartungsteile_hersteller_arr();
        $js = " onchange=\"drop_change_check('g_hersteller', 'hersteller');lade_dropdown('g_hersteller', 'hersteller', 'modell', '" . route('web::wartungsplaner::ajax', ['option' => 'get_hersteller_modelle'], false) . "')\"";
        dropdown_hersteller($arr, 'Hersteller wählen oder ...', 'g_hersteller', 'g_hersteller', $js);
        $js_hersteller = "onkeyup='text_kuerzen(\"hersteller\", \"50\")'";
        text_feld('Hersteller', 'hersteller', 'hersteller', 30, $js_hersteller, $HERSTELLER);

        $arr = get_wartungsteile_gruppe_arr(1);
        $js = " onchange=\"drop_change_check('modell', 'modell_bez')\"";
        dropdown_w_teile_gruppe($arr, 'Mögliche Bezeichnung/Modell wählen oder ...', 'modell', 'modell', $js);
        $js_modell = "onkeyup='text_kuerzen(\"modell_bez\", \"50\")'";
        text_feld('Bezeichnung eingeben', 'modell_bez', 'modell_bez', 30, $js_modell, $BEZEICHNUNG);

        $js_baujahr = "onkeyup='text_kuerzen(\"baujahr\", \"4\")'";
        text_feld('Baujahr eingeben (yyyy)', 'baujahr', 'baujahr', 10, $js_baujahr, $BAUJAHR);

        /*Lage der Therme, bei Objekten Wohnugsnummer, bei Externen der Raum*/
        $js_lage = "onkeyup='text_kuerzen(\"lage_raum\", \"50\")'";
        text_feld('Einbauort eingeben (50)', 'lage_raum', 'lage_raum', 10, $js_lage, $LAGE_RAUM);
        dropdown_monate('Wartungsintervall', 'wartungsintervall', 'wartungsintervall', '');

        echo "<br><br><hr>";
        $onclick = "onclick=\"daj3('" . route('web::wartungsplaner::ajax', ['option' => 'form_abweichende_r_anschrift', 'g_id' => $g_id], false) . "','leftBox')\"";
        echo "<label for=\"rech_ansch_ab_ja\">Abweichende Rechnungsanschrift JA</label>";
        echo "<input type=\"radio\" id=\"rech_ansch_ab_ja\" name=\"rech_ansch_ab_ja\"   $onclick />";

        $onclick1 = "onclick=\"daj3('" . route('web::wartungsplaner::ajax', ['option' => 'get_partner_info_r_an', 'g_id' => $g_id], false) . "','leftBox')\" ";
        echo "<label for=\"rech_ansch_ab_no\">Abweichende Rechnungsanschrift NEIN</label>";
        echo "<input type=\"radio\" id=\"rech_ansch_ab_no\" name=\"rech_ansch_ab_ja\"  $onclick1/>";

        echo "<hr>";

        $funk1 = "wgeraet_pruefen|" . route('web::wartungsplaner::ajax', ['option' => 'wgeraet_aendern', 'g_id' => $g_id, 'kos_typ' => $KOSTENTRAEGER_TYP, 'kos_id' => $KOSTENTRAEGER_ID], false) . "|leftBox";
        $funk2 = "w_geraet_form_del|";
        $js_onsubmit = "onclick='yes_no(\"$funk1\", \"$funk2\")'";
        button('btn_speichern', 'btn_speichern', 'Wartungsteil ändern', $js_onsubmit);
        formular_ende();

    }
}


function form_wartungsvertrag($geraet_id)
{
    echo "Wartungsvertrag hier abschliessen<br>PARTNER WäHLEN dann Wartungszyklus bestimmen";
}

/*Datumsfeld mit ID, JS-Action und Label*/
function datum_feld($beschreibung, $name, $id)
{
    $js_datum = "onchange=check_datum('$id')"; //check_datum holt sich den wert vom feld mit der id und präft ihn!
    text_feld($beschreibung, $name, $id, 10, $js_datum);
}

function datum_feld2($beschreibung, $name, $id, $datum)
{
    $js_datum = "onchange=check_datum('$id')"; //check_datum holt sich den wert vom feld mit der id und präft ihn!
    text_feld1($beschreibung, $name, $id, $js_datum, $datum);
}


function get_wartungsteile_arr($kos_typ, $kos_id)
{
    $result = DB::select("SELECT * FROM W_GERAETE WHERE AKTUELL='1' && KOSTENTRAEGER_TYP='$kos_typ' && KOSTENTRAEGER_ID='$kos_id' ORDER BY BEZEICHNUNG ASC");
    return $result;
}


function geraete_liste()
{
    $arr = get_alle_wartungsteile_arr();
    $anz = count($arr);
    echo "<table class=\"sortable\">";
    echo "<tr><th>G_ID</th><th>GRUPPE</th><th>BEZEICHNUNG</th><th>EINBAUORT</th><th>BJ</th><th>INFO</th><th>L.Wartung</th><th>N. Wartung</th></tr>";
    $z = 0;
    for ($a = 0; $a < $anz; $a++) {
        $z++;
        $g_id = $arr[$a]['GERAETE_ID'];
        $lage_raum = $arr[$a]['LAGE_RAUM'];
        $bj = $arr[$a]['BAUJAHR'];
        $gruppe_id = $arr[$a]['GRUPPE_ID'];
        $gruppen_bez = get_gruppen_bez($gruppe_id);
        $kos_id = $arr[$a]['KOSTENTRAEGER_ID'];
        $g_bez = $arr[$a]['BEZEICHNUNG'];
        $l_w_datum = get_datum_lw($g_id);
        $n_w_datum = get_datum_nw($g_id);
        if (!check_is_eigentuemer($kos_id)) {
            $g = new general();
            $g->get_partner_info($kos_id);
            $pa_name = "$g->partner_name, $g->partner_strasse $g->partner_hausnr, $g->partner_plz $g->partner_ort";
            $p_name = "<b>KUNDE:</b>$pa_name";
        } else {
            $einheit_id = get_einheit_id($lage_raum);
            $einheit_info_arr = get_einheit_info($einheit_id);
            $mietername = $einheit_info_arr['MIETER'];
            $haus_str = $einheit_info_arr['HAUS_STRASSE'];
            $haus_nr = $einheit_info_arr['HAUS_NUMMER'];
            $e_lage = $einheit_info_arr['EINHEIT_LAGE'];
            $p_name = "<b>MIETER:</b> $lage_raum $mietername $haus_str $haus_nr $e_lage";


        }
        echo "<tr class=\"zeile$z\"><td>$g_id</td><td>$gruppen_bez</td><td>$g_bez</td><td>$lage_raum</td><td>$bj</td><td>$p_name</td><td>$l_w_datum</td><td>$n_w_datum</td></tr>";
        if ($z == 2) {
            $z = 0;
        }
    }

    echo "</table>";
}

function get_alle_wartungsteile_arr()
{
    $result = DB::select("SELECT * FROM W_GERAETE WHERE AKTUELL='1' ORDER BY GRUPPE_ID, KOSTENTRAEGER_TYP, KOSTENTRAEGER_ID, LAGE_RAUM, BEZEICHNUNG ASC");
    return $result;
}

function geraete_info_anzeigen($g_id)
{
    $result = DB::select("SELECT * FROM W_GERAETE WHERE AKTUELL='1' && GERAETE_ID='$g_id' LIMIT 0,1");
    if (!empty($result)) {
        foreach($result as $row) {
            $bezeichnung = $row['BEZEICHNUNG'];
            $hersteller = $row['HERSTELLER'];
            $baujahr = $row['BAUJAHR'];

            /*Raum oder Wohnung, Einbauort*/
            $lage_raum = $row['LAGE_RAUM'];

            /*Eigentämer der Therme*/
            $kos_typ = $row['KOSTENTRAEGER_TYP'];
            $kos_id = $row['KOSTENTRAEGER_ID'];

            if ($kos_typ == 'Partner') {
                $anschrift = get_partner_anschrift($kos_id);
                $kos_bez = get_partner_name($kos_id);
                $kos_typ_d = 'PARTNER_LIEFERANT';
            } else {
                $kos_typ_d = $kos_typ;
            }

            /*Rechnungsempfänger*/
            $rech_an = nl2br($row['RECHNUNG_AN']);

            echo "<p class=\"zeile_ueber\"><b>Wartungsteil</b>: $hersteller - $bezeichnung</p>";
            echo "<p class=\"zeile_ueber\"><b>Baujahr</b>: $baujahr</p>";
            echo "<p class=\"zeile_ueber\"><b>Einbauort</b>: $lage_raum</p>";
            echo "<br>";
            echo "<p class=\"zeile_hinweis\"><b>Inhaber</b>: $kos_bez</p>";
            echo "<p class=\"zeile_hinweis\"><b>Anschrift</b>: $anschrift</p>";
            if (empty($rech_an)) {
                $rech_an = 'o.g. Anschrift';
            }
            echo "<p class=\"zeile_hinweis\"><b>Rechnung an</b>: <br>$rech_an</p>";
            alle_details_anzeigen($kos_typ_d, $kos_id);
            echo "<p class=\"zeile_ueber\">DETAIL ZUM KUNDEN</p>";
            form_detail_hinzu($kos_typ_d, $kos_id);
        }
    } else {
        echo "FEHLER 3x00f in function geraete_info_anzeigen()";
    }
}

function geraete_info_arr($g_id)
{
    $result = DB::select("SELECT * FROM W_GERAETE WHERE AKTUELL='1' && GERAETE_ID='$g_id' LIMIT 0,1", [], false);
    return $result;
}


function get_wartungsteile_hersteller_arr()
{
    $result = DB::select("SELECT HERSTELLER FROM W_GERAETE WHERE AKTUELL='1' GROUP BY HERSTELLER ORDER BY HERSTELLER ASC");
    return $result;
}

function get_wartungsgruppen_arr()
{
    $result = DB::select("SELECT * FROM W_GRUPPE WHERE AKTUELL='1' ORDER BY GRUPPE ASC");
    return $result;
}

/*Alle Wartungsteile einer Gruppe*/
function get_wartungsteile_gruppe_arr($gruppe_id)
{
    $result = DB::select("SELECT * FROM W_GERAETE WHERE GRUPPE_ID='$gruppe_id' && AKTUELL='1' ORDER BY BEZEICHNUNG ASC");
    return $result;
}

/*Alle Wartungsteile eines Herstellers*/
function get_hersteller_modelle_arr($hersteller)
{
    $result = DB::select("SELECT * FROM W_GERAETE WHERE HERSTELLER='$hersteller' && AKTUELL='1' ORDER BY BEZEICHNUNG ASC");
    return $result;
}


function get_wgeraete_bez($gruppe_id)
{
    $arr = get_wartungsteile_gruppe_arr($gruppe_id);
    if (!empty($arr)) {
        $anz = count($arr);
        for ($a = 0; $a < $anz; $a++) {
            $g_id = $arr[$a]['GERAETE_ID'];
            $g_bez = $arr[$a]['BEZEICHNUNG'];
            echo "$g_id,$g_bez|";
        }
    }
}

function get_hersteller_gruppe($gruppe_id)
{
    $arr = get_wartungsteile_gruppe_arr($gruppe_id);
    if (!empty($arr)) {
        $anz = count($arr);
        for ($a = 0; $a < $anz; $a++) {
            $h_bez = $arr[$a]['HERSTELLER'];
            $her_arr[] = $h_bez;
        }
        $herr_arr_u = array_unique($her_arr);
        foreach ($herr_arr_u as $value) {
            echo "$value,$value|";
        }


    }
}

function get_hersteller_modelle($hersteller)
{
    $arr = get_hersteller_modelle_arr($hersteller);
    if (!empty($arr)) {
        $anz = count($arr);
        for ($a = 0; $a < $anz; $a++) {
            $bez = $arr[$a]['BEZEICHNUNG'];
            $mod_arr[] = $bez;
        }
        $mod_arr_u = array_unique($mod_arr);

        foreach ($mod_arr_u as $value) {
            echo "$value,$value|";
        }


    }
}


function form_abweichende_r_anschrift()
{
    if (request()->has('g_id')) {
        $g_info_arr = geraete_info_arr(request()->input('g_id'));
        extract($g_info_arr[0]);
        $r_text = $RECHNUNG_AN;
    } else {
        $r_text = '';
    }
    formular('', 'formx');
    echo "<p class=\"zeile_ueber\">Rechnungsanschrift eingeben</p>";
    text_bereich("", 'zustell_ans', $r_text, 35, 5, 'zustell_ans', '');
    echo "<br>";
    echo "<br>";
    echo "<br>";
    echo "<br>";
    echo "<br>";
    echo "<br>";
    echo "<br>";
    echo "<br>";
    echo "<br>";
    echo "<p class=\"zeile_hinweis\">Die o.g Anschrift erscheint auf der Rechnung!!!</p>";
    echo "<p class=\"zeile_hinweis\">Der eingegebene Partner ist der Eigentämer des Wartungteiles!!!</p>";
    echo "<br>";
    formular_ende();
}


function detail_speichern_2($tabelle, $id, $det_name, $det_inhalt, $det_bemerkung)
{
    $last_detail_id = last_id2('DETAIL', 'DETAIL_ID');
    $det_inhalt = str_replace("\n", '<br />', $det_inhalt);
    return DB::insert("INSERT INTO DETAIL VALUES (NULL, '$last_detail_id', '$det_name','$det_inhalt', '$det_bemerkung', '1','$tabelle','$id')");
}

function termin_suchen($g_id)
{
    session()->put('g_id', $g_id);
    $g_info_arr = geraete_info_arr($g_id);
    if (!empty($g_info_arr)) {
        $gruppe_id = $g_info_arr[0]['GRUPPE_ID'];
        $kos_typ = $g_info_arr[0]['KOSTENTRAEGER_TYP'];
    } else {
        die('Keine Geräte Informationen!!! Fehler 43332! Z:945. termin_suchen!');
    }
    if ($kos_typ != 'Partner') {
        die('ABBRUCH - Geräteeigentämer kein PARTNER');
    }
    $kos_id = $g_info_arr[0]['KOSTENTRAEGER_ID'];

    /*Präfen ob Therme einem Eigentuemer gehärt*/
    /*Bei true is der Eigentämer auch Eigentämer eines Wohnobjektes*/
    $g = new general();
    $g->get_partner_info($kos_id);
    $g->get_gruppen_info($gruppe_id);

    /*OPTIMIERUNG gleich bei OSM fragen, da ist db abfrage integriert*/

    if (!check_is_eigentuemer($kos_id)) {
        $g_z = new general();
        $g_z->get_partner_info($kos_id);
        $lat_lon_db_ziel = get_lat_lon_db_osm($g_z->partner_strasse, $g_z->partner_hausnr, $g_z->partner_plz, $g_z->partner_ort);
        session()->put('ziel_str', $g_z->partner_strasse);
        session()->put('ziel_nr', $g_z->partner_hausnr);
        session()->put('ziel_plz', $g_z->partner_plz);
        session()->put('ziel_ort', $g_z->partner_ort);
    } else {
        /*Mieterinformationen holen*/
        $g_info_arr = geraete_info_arr($g_id);
        $einheit_bez = $g_info_arr[0]['LAGE_RAUM'];
        $gr = new general();
        $gr->get_lanlon_mieter($einheit_bez);
        $lat_lon_db_ziel = $gr->mieter_lat_lon_db_ziel;
    }
    $ziel_arr = explode(',', $lat_lon_db_ziel);
    session()->put('ziel_lon', $ziel_arr[0]);
    session()->put('ziel_lat', $ziel_arr[1]);


    $tt = new general();
    /*Wenn Datum gesetzt äbernehmen sonst von heute rechnen*/
    if (request()->has('datum_ab')) {
        $datum_df = request()->input('datum_ab');
    } else {
        $datum_df = date("d.m.Y");
    }
    if (!isset($g->team_id)) {
        die('<h3>Keine Teaminformationen gefunden!<br>Ist ein Team fär die Arbeiten erstellt worden????</h3>');
    }
    $tages_arr = $tt->get_termin_arr($g->team_id, $datum_df, 90);

    if (is_array($tages_arr)) {
        $srt = new arr_multisort();
        $srt->setArray($tages_arr);
        $srt->addColumn("D_KM", SRT_ASC);

        $f_termine_sort = $srt->sort();
        unset($tages_arr);
        freie_termine_tab($f_termine_sort);


    } else {
        echo "Keine Mitarbeiter die die Arbeiten durchfähren kännen!";
    }
}

function termin_suchen3($g_id)
{
    session()->put('g_id', $g_id);
    $g_info_arr = geraete_info_arr($g_id);
    if (!empty($g_info_arr)) {
        $gruppe_id = $g_info_arr[0]['GRUPPE_ID'];
        session()->put('gruppe_id', $gruppe_id);
        $kos_typ = $g_info_arr[0]['KOSTENTRAEGER_TYP'];

    } else {
        die('Keine Geräte Informationen!!! Fehler 43332! Z:945. termin_suchen!');
    }
    if ($kos_typ != 'Partner') {
        die('ABBRUCH - Geräteeigentümer kein PARTNER');
    }
    $kos_id = $g_info_arr[0]['KOSTENTRAEGER_ID'];

    /*Präfen ob Therme einem Eigentuemer gehärt*/
    /*Bei true is der Eigentämer auch Eigentämer eines Wohnobjektes*/
    $g = new general();
    $g->get_partner_info($kos_id);
    $g->get_gruppen_info($gruppe_id);

    /*OPTIMIERUNG gleich bei OSM fragen, da ist db abfrage integriert*/
    if (!check_is_eigentuemer($kos_id)) {
        $g_z = new general();
        $g_z->get_partner_info($kos_id);
        $lat_lon_db_ziel = get_lat_lon_db_osm($g_z->partner_strasse, $g_z->partner_hausnr, $g_z->partner_plz, $g_z->partner_ort);
        session()->put('ziel_str', $g_z->partner_strasse);
        session()->put('ziel_nr', $g_z->partner_hausnr);
        session()->put('ziel_plz', $g_z->partner_plz);
        session()->put('ziel_ort', $g_z->partner_ort);
    } else {
        /*Mieterinformationen holen*/
        $g_info_arr = geraete_info_arr($g_id);
        $einheit_bez = $g_info_arr[0]['LAGE_RAUM'];
        $gr = new general();
        $gr->get_lanlon_mieter($einheit_bez);
        $lat_lon_db_ziel = $gr->mieter_lat_lon_db_ziel;
    }
    $ziel_arr = explode(',', $lat_lon_db_ziel);
    session()->put('ziel_lon', $ziel_arr[0]);
    session()->put('ziel_lat', $ziel_arr[1]);


    $tt = new general();
    /*Wenn Datum gesetzt äbernehmen sonst von heute rechnen*/
    if (request()->has('datum_ab')) {
        $datum_df = request()->input('datum_ab');
    } else {
        $datum_df = date("d.m.Y");
    }
    if (!isset($g->team_id)) {
        die('<h3>Keine Teaminformationen gefunden!<br>Ist ein Team fär die Arbeiten erstellt worden????</h3>');
    }
    $tages_arr = $tt->get_termin_arr($g->team_id, $datum_df, 90);

    if (is_array($tages_arr)) {
        $srt = new arr_multisort();
        //	Set the array to be sorted
        $srt->setArray($tages_arr);

        if (session()->has('sortby')) {
            echo "NEUE SORTIERUNG = " . session()->get('sortby');
            $srt->addColumn(session()->get('sortby'), SRT_ASC);
            session()->forget('sortby');
        } else {
            $srt->addColumn("D_KM", SRT_ASC);
            $srt->addColumn("DATUMZ", SRT_ASC);
        }

        $f_termine_sort = $srt->sort();
        unset($tages_arr);
        freie_termine_tab3($f_termine_sort);

    } else {
        echo "Keine Mitarbeiter die die Arbeiten durchfähren kännen!";
    }
}

function termin_suchen4($g_id, $team_id = '1')
{
    if (request()->has('datum_ab')) {
        $datum_df = request()->input('datum_ab');
    } else {
        $datum_df = date("d.m.Y");
    }
    $tt = new general();
    $tages_arr = $tt->get_termin_arr1($team_id, $datum_df, 90);
    if (is_array($tages_arr)) {
        echo "<pre>";
        print_r($tages_arr); /*WENN Termin-Dauer gewählt schon mit passenden Läcken*/
    } else {
        echo $tages_arr;
    }
    echo "TErminsuche 4";

}


function termin_suchen2($str, $nr, $plz, $ort, $team_id = '1')
{
    $js_termn_dauer = "onchange=\"termin_dauer_aendern('termin_dauer');umkreissuche('rightBox')\"";
    if (session()->has('termin_dauer')) {
        dropdown_dauer('Termindauer', 'termin_dauer', 'termin_dauer', session()->get('termin_dauer'), $js_termn_dauer);
    } else {
        dropdown_dauer('Termindauer', 'termin_dauer', 'termin_dauer', 60, $js_termn_dauer);
    }

    /*OPTIMIERUNG gleich bei OSM fragen, da ist db abfrage integriert*/
    $lat_lon_db_ziel = get_lat_lon_db_osm(umlaute_anpassen($str), $nr, $plz, umlaute_anpassen($ort));
    session()->put('ziel_str', $str);
    session()->put('ziel_nr', $nr);
    session()->put('ziel_plz', $plz);
    session()->put('ziel_ort', $ort);


    $ziel_arr = explode(',', $lat_lon_db_ziel);
    session()->put('ziel_lon', $ziel_arr[0]);
    session()->put('ziel_lat', $ziel_arr[1]);


    $tt = new general();
    /*Wenn Datum gesetzt äbernehmen sonst von heute rechnen*/
    if (request()->has('datum_ab')) {
        $datum_df = request()->get('datum_ab');
    } else {
        $datum_df = date("d.m.Y");
    }

    $tages_arr = $tt->get_termin_arr($team_id, $datum_df, 90);

    if (is_array($tages_arr)) {
        $srt = new arr_multisort();
        $srt->setArray($tages_arr);

        $srt->addColumn("D_KM", SRT_ASC);
        $srt->addColumn("DATUMZ", SRT_ASC);

        $f_termine_sort = $srt->sort();
        unset($tages_arr);
        freie_termine_tab2($f_termine_sort);
    } else {
        echo "Keine Mitarbeiter die die Arbeiten durchfähren kännen!";
    }
}


function besten_termin_suchen($g_id, $datum_df)
{
    session()->put('g_id', $g_id);
    $g_info_arr = geraete_info_arr($g_id);
    if (!empty($g_info_arr)) {

        $gruppe_id = $g_info_arr[0]['GRUPPE_ID'];
        $kos_typ = $g_info_arr[0]['KOSTENTRAEGER_TYP'];
        $lage_raum = $g_info_arr[0]['LAGE_RAUM'];
        $kos_id = $g_info_arr[0]['KOSTENTRAEGER_ID'];
    } else {
        die('Keine Geräte Informationen!!! Fehler 43332! Z:945. termin_suchen!');
    }
    if ($kos_typ != 'Partner') {
        die('ABBRUCH - Geräteeigentämer kein PARTNER');
    }


    /*Präfen ob Therme einem Eigentuemer gehärt*/
    /*Bei true is der Eigentämer auch Eigentämer eines Wohnobjektes*/
    $g = new general();
    if (!check_is_eigentuemer($kos_id)) {
        $g->get_partner_info($kos_id);
        $lat_lon_db_ziel = get_lat_lon_db_osm($g->partner_strasse, $g->partner_hausnr, $g->partner_plz, $g->partner_ort);
    } else {
        /*Mieter*/
        /*Wohnungsnummer = lage_raum*/
        $einheit_name = $lage_raum;
        if (empty($einheit_name)) {
            die('EINHEIT FEHLT');
        }

        $einheit_id = get_einheit_id($einheit_name);
        if (empty($einheit_id)) {
            die("$kos_typ $kos_id<br> <b>$einheit_name vom GID $g_id als Wohnungsnummer nicht bekannt, Wartungsteil korriegieren LAGE_RAUM");
        } else {
            $einheit_info_arr = get_einheit_info($einheit_id);
            if (!is_array($einheit_info_arr)) {
                die("Einheitinformationen zu $einheit_id $einheit_name fehlern, ABBRUCH");
            } else {
                extract($einheit_info_arr);
                $lat_lon_db_ziel = get_lat_lon_db_osm($HAUS_STRASSE, $HAUS_NUMMER, $HAUS_PLZ, $HAUS_STADT);
            }


            $mv_id = get_last_mietvertrag_id($einheit_id);

        }


    }
    $g->get_gruppen_info($gruppe_id);

    /*OPTIMIERUNG gleich bei OSM fragen, da ist db abfrage integriert*/
    $ziel_arr = explode(',', $lat_lon_db_ziel);
    session()->put('ziel_lon', $ziel_arr[0]);
    session()->put('ziel_lat', $ziel_arr[1]);


    $tt = new general();

    /*Wenn Datum gesetzt äbernehmen sonst von heute rechnen*/
    if (!isset($g->team_id)) {
        die('<h3>Keine Teaminformationen gefunden!<br>Ist ein Team fär die Arbeiten erstellt worden????</h3>');
    }
    $tages_arr = $tt->get_termin_arr($g->team_id, $datum_df, 90);
    if (is_array($tages_arr)) {

        $srt = new arr_multisort();
        $srt->setArray($tages_arr);
        $srt->addColumn("D_KM", SRT_ASC);
        $srt->addColumn("DATUMZ", SRT_ASC);
        $f_termine_sort = $srt->sort();
        unset($tages_arr);
        $my_arr = $f_termine_sort[0];
        if (isset($einheit_name)) {
            $my_arr['EINHEIT_NAME'] = $einheit_name;
            $my_arr['EINHEIT_ID'] = $einheit_id;
            $my_arr['EINHEIT_STR'] = $HAUS_STRASSE;
            $my_arr['EINHEIT_NR'] = $HAUS_NUMMER;
            $my_arr['EINHEIT_PLZ'] = $HAUS_PLZ;
            $my_arr['EINHEIT_ORT'] = $HAUS_STADT;
        }
        if (!empty($mv_id)) {
            $my_arr['MV_ID'] = $mv_id;
            $my_arr['MIETERNAME'] = 'MIETERNAME';
        }

        return $my_arr;
    } else {
        echo "Keine Mitarbeiter die die Arbeiten durchfähren kännen!";
    }
    return null;
}


function min_in_zeit($min)
{
    if ($min < 1) {
        return '00:00';
    } else {
        $std = sprintf('%02d', intval($min / 60));
        $minuten = sprintf('%02d', $min % 60);
        return "$std:$minuten";
    }
}

function getzeitdiff_min($anfangszeit, $endzeit)
{
    $anfangszeit_arr = explode(':', $anfangszeit);
    $a_std = $anfangszeit_arr['0'] / 1;
    $a_min = $anfangszeit_arr['1'] / 1;

    $endzeit_arr = explode(':', $endzeit);
    $e_std = $endzeit_arr['0'] / 1;
    $e_min = $endzeit_arr['1'] / 1;

    $t1 = mktime($a_std, $a_min, 0, 1, 12, 2000);
    $t2 = mktime($e_std, $e_min, 0, 1, 12, 2000);
    $diff_min = ($t2 - $t1) / 60;
    return $diff_min;
}

function freie_termine_tab($arr)
{
    if (!is_array($arr)) {
        die('Keine freien Termine verfägbar');
    } else {
        $anz = count($arr);
        echo "<table class=\"sortable\">";
        echo "<tr class=\"zeile2\"><th>KW</th><th>Datum</th><th>Wochentag</th><th>Mitarbeiter</th><th>Entfernung</th><th>TERMINE<br>frei/von</th></tr>";
        $z = 0;
        for ($a = 0; $a < $anz; $a++) {
            $z++;
            extract($arr[$a]);
            $wochentag = get_wochentag_name($DATUM);
            $wt = get_wochentag($DATUM);
            $kw = get_kw($DATUM);
            $js_neues_teil = "onclick=\"daj3('" . route('web::wartungsplaner::ajax', ['option' => 'termine_tag_tab', 'b_id' => $BENUTZER_ID, 'datum' => $DATUM], false) . "','rightBox1');daj3('" . route('web::wartungsplaner::ajax', ['option' => 'karte', 'b_id' => $BENUTZER_ID, 'datum_d' => $DATUM], false) . "','rightBox');\"";
            if ($wt == 6 or $wt == 7) {
                echo "<tr $js_neues_teil class=\"zeile$z\"><td>$kw. KW</td><td>$DATUM</td><td class=\"zeile_belegt\">$wochentag</td><td>$benutzername</td><td>$D_KM km</td><td>$FREI/$TERMINE_TAG</td></tr>";
            } else {
                echo "<tr $js_neues_teil class=\"zeile$z\"><td>$kw. KW</td><td>$DATUM</td><td>$wochentag</td><td>$benutzername</td><td>$D_KM km</td><td>$FREI/$TERMINE_TAG</td></tr>";

            }
            if ($z == 2) {
                $z = 0;
            }
        }
        echo "<tr class=\"zeile_detail\"><td colspan=\"3\">";
        $datum_start = tage_minus_wp($arr[0]['DATUM'], 7);
        $datum_end = $arr[$anz - 1]['DATUM'];
        $js = "onclick=\"termin_suchen_btn1('$datum_start')\"";
        $js1 = "onclick=\"termin_suchen_btn1('$datum_end')\"";
        button('termine_vor', 'btn_termine_vor', "Terminsuche nach dem $datum_start", $js);
        echo "</td><td colspan=\"3\">";
        button('termine_vor', 'btn_termine_vor', "Terminsuche nach dem $datum_end", $js1);
        echo "</td></tr>";
        echo "</table>";
    }
}


function freie_termine_tab3($arr)
{
    $sort_js = "onclick=\"daj3('" . route('web::wartungsplaner::ajax', ['option' => 'reg_sortieren', 'sortby' => 'DATUMZ'], false) . "', 'rightBox1');termin_suchen_btn1();\"";
    button('btn_sortby', 'btn_sortby', 'NACH DATUM', $sort_js);
    if (!is_array($arr)) {
        die('Keine freien Termine verfägbar');
    } else {
        if (!session()->has('termin_dauer')) {
            session()->put('termin_dauer', 60);
        }

        $anz = count($arr);
        for ($a = 0; $a < $anz; $a++) {
            $anz_lueck = count($arr[$a]['LUECKEN']);
            $zeiten = '';
            for ($cc = 0; $cc < $anz_lueck; $cc++) {
                $dauer = $arr[$a]['LUECKEN'][$cc]['DAUER'];
                if ($dauer >= session()->get('termin_dauer')) {
                    $zeiten[] = $arr[$a]['LUECKEN'][$cc];
                }
            }
            if (is_array($zeiten)) {
                unset($arr[$a]['LUECKEN']);
                $arr[$a]['LUECKEN'] = $zeiten;
                unset($zeiten);
            } else {
                unset($arr[$a]);
            }
        }

        $arr = array_merge($arr);

        $anz = count($arr);
        echo "<table class=\"sortable\">";
        echo "<thead>";
        echo "<tr><th>KW</th><th>Datum</th><th>Zeit</th><th>Wochentag</th><th>Mitarbeiter</th><th>Entfernung</th><th>TERMINE<br>frei/von</th></tr>";
        echo "</thead>";
        $z = 0;
        for ($a = 0; $a < $anz; $a++) {
            $z++;
            extract($arr[$a]);
            $wochentag = get_wochentag_name($DATUM);
            $wt = get_wochentag($DATUM);
            $kw = get_kw($DATUM);
            $js_neues_teil = "onclick=\"daj3('" . route('web::wartungsplaner::ajax', ['option' => 'termine_tag_tab', 'b_id' => $BENUTZER_ID, 'datum' => $DATUM], false) . "','rightBox1');daj3('" . route('web::wartungsplaner::ajax', ['option' => 'karte', 'b_id' => $BENUTZER_ID, 'datum_d' => $DATUM], false) . "','rightBox');\"";

            $anz_freie = count($arr[$a]['LUECKEN']);


            if ($wt == 6 or $wt == 7) {
                echo "<tr $js_neues_teil class=\"zeile$z\"><td>$kw. KW</td><td>$DATUM</td><td class=\"zeile_belegt\">$wochentag</td><td>$benutzername</td><td>$D_KM km</td><td>$FREI/$TERMINE_TAG</td></tr>";
            } else {
                echo "<tr $js_neues_teil class=\"zeile$z\"><td>$kw. KW</td><td>$DATUM</td><td>";
                $b_id = $arr[$a]['BENUTZER_ID'];
                $ganzer_tag_arr = tages_ansicht_arr($b_id, $DATUM);
                echo "<table>";
                for ($cc = 0; $cc < $anz_freie; $cc++) {
                    $von = $arr[$a]['LUECKEN'][$cc]['VON'];
                    $bis = $arr[$a]['LUECKEN'][$cc]['BIS'];
                    echo "<tr class=\"zeile3\"><td>$von-$bis</td>";
                    echo get_entf_vor_nach($ganzer_tag_arr, $von, $bis) . '</tr>';
                }
                echo "</table>";
                echo "</td><td>$wochentag</td><td>$benutzername</td><td>$D_KM km</td><td>$anz_freie/$TERMINE_TAG</td></tr>";

            }
            if ($z == 2) {
                $z = 0;
            }
        }
        echo "<tr class=\"zeile_detail\"><td colspan=\"3\">";
        $datum_start = tage_minus_wp($arr[0]['DATUM'], 7);
        $datum_end = $arr[$anz - 1]['DATUM'];
        $js = "onclick=\"termin_suchen_btn1('$datum_start')\"";
        $js1 = "onclick=\"termin_suchen_btn1('$datum_end')\"";
        button('termine_vor', 'btn_termine_vor', "Terminsuche nach dem $datum_start", $js);
        echo "</td><td colspan=\"3\">";
        button('termine_vor', 'btn_termine_vor', "Terminsuche nach dem $datum_end", $js1);
        echo "</td></tr>";
        echo "</table>";
    }
}


function get_entf_vor_nach($arr, $von, $bis)
{
    if (is_array($arr)) {
        $anz = count($arr);
        for ($a = 0; $a < $anz; $a++) {
            $von_t = $arr[$a]['VON'];
            $bis_t = $arr[$a]['BIS'];
            if (($von_t === $von) && $bis === $bis_t) {
                return '<td><b>' . $arr[$a]['VON_KM'] . ' km</b></td><td>' . $arr[$a]['VON_ZEIT'] . '</td>';

            }
        }
    }

}


function freie_termine_tab2($arr)
{
    if (!is_array($arr)) {
        die('Keine freien Termine verfägbar');
    } else {
        if (!session()->has('termin_dauer')) {
            session()->put('termin_dauer', 60);
        }

        $anz = count($arr);
        for ($a = 0; $a < $anz; $a++) {
            $anz_lueck = count($arr[$a]['LUECKEN']);
            $zeiten = '';
            for ($cc = 0; $cc < $anz_lueck; $cc++) {
                $dauer = $arr[$a]['LUECKEN'][$cc]['DAUER'];
                if ($dauer >= session()->get('termin_dauer')) {
                    $zeiten[] = $arr[$a]['LUECKEN'][$cc];
                }
            }
            if (is_array($zeiten)) {
                unset($arr[$a]['LUECKEN']);
                $arr[$a]['LUECKEN'] = $zeiten;
                unset($zeiten);
            } else {
                unset($arr[$a]);
            }
        }

        $arr = array_merge($arr);


        $anz = count($arr);
        echo "<table class=\"sortable\">";
        echo "<tr class=\"zeile2\"><th>KW</th><th>Datum</th><th>ZEIT</th><th>Wochentag</th><th>Mitarbeiter</th><th>Entfernung</th><th>TERMINE<br>frei/von</th></tr>";
        $z = 0;
        for ($a = 0; $a < $anz; $a++) {
            $z++;
            extract($arr[$a]);
            $wochentag = get_wochentag_name($DATUM);
            $wt = get_wochentag($DATUM);
            $kw = get_kw($DATUM);
            $freie_t = count($arr[$a]['LUECKEN']);

            if ($wt == 6 or $wt == 7) {
                echo "<tr $js_neues_teil class=\"zeile$z\"><td>$kw. KW</td><td>$DATUM</td>";

                echo "<td class=\"zeile_belegt\">$wochentag</td><td>$benutzername</td><td>$D_KM km</td><td>$FREI/$TERMINE_TAG</td></tr>";
            } else {
                echo "<tr class=\"zeile$z\"><td>$kw. KW</td><td>$DATUM</td>";
                echo "<td>";
                echo "<table>";
                $ganzer_tag_arr = tages_ansicht_umkreis_arr($BENUTZER_ID, $DATUM);

                for ($b = 0; $b < $freie_t; $b++) {
                    $von = $arr[$a]['LUECKEN'][$b]['VON'];
                    $bis = $arr[$a]['LUECKEN'][$b]['BIS'];
                    $js_neues_teil = "onclick=\"daj3('" . route('web::wartungsplaner::ajax', ['option' => 'termine_tag_tab2', 'b_id' => $BENUTZER_ID, 'datum' => $DATUM], false) . "','rightBox1');daj3('" . route('web::wartungsplaner::ajax', ['option' => 'karte', 'b_id' => $BENUTZER_ID, 'datum_d' => $DATUM], false) . "','leftBox1');zumAnker('$von-$bis')\"";
                    echo "<tr $js_neues_teil class=\"zeile3\"><td>$von-$bis</td>";
                    echo get_entf_vor_nach($ganzer_tag_arr, $von, $bis) . '</tr>';
                }
                echo "</table>";
                echo "</td>";
                echo "<td>$wochentag</td><td>$benutzername</td><td>$D_KM km</td><td>$freie_t/$TERMINE_TAG</td></tr>";

            }
            if ($z == 2) {
                $z = 0;
            }
        }
        echo "</table>";
    }
}


function tages_termine_arr_b($benutzer_id, $datum_d)
{
    $arr = get_termine_tag_arr($benutzer_id, $datum_d);

    $luecken_termine = get_luecken_termine($benutzer_id, $datum_d);

    if (is_array($luecken_termine) && is_array($arr)) {
        $my_arr = array_sortByIndex(array_merge($arr, $luecken_termine), 'VON');
    }
    if (is_array($luecken_termine) && !is_array($arr)) {
        $my_arr = $luecken_termine;
    }
    if (!is_array($luecken_termine) && is_array($arr)) {
        $my_arr = $arr;
    }
    return $my_arr;
}


function kontaktdaten_anzeigen($g_id = 1)
{
    return "<b><br>FALSCH WIRD BEAREBEITET</b>";
}

function kontaktdaten_anzeigen_kunde($kos_id)
{
    $arr = finde_detail_kontakt_arr('PARTNER_LIEFERANT', $kos_id);
    if (!empty($arr)) {
        $kontaktdaten = '';
        foreach ($arr as $a) {
            $dname = $arr[$a]['DETAIL_NAME'];
            $dinhalt = $arr[$a]['DETAIL_INHALT'];
            $kontaktdaten .= "<br><b>$dname</b>:$dinhalt";
        }
        return $kontaktdaten;
    } else {
        return "<br><b>keine Kontaktdaten</b>";
    }
}

function kontaktdaten_anzeigen_mieter($einheit_bez, $hinweis_an = 1)
{
    $einheit_id = get_einheit_id($einheit_bez);
    $mv_id = get_last_mietvertrag_id($einheit_id);
    if (empty($mv_id)) {
        /*Nie vermietet*/
        return 'Leerstand';
    } else {
        $result = DB::select("SELECT PERSON_MIETVERTRAG_PERSON_ID FROM PERSON_MIETVERTRAG WHERE PERSON_MIETVERTRAG_MIETVERTRAG_ID='$mv_id' && PERSON_MIETVERTRAG_AKTUELL='1' ORDER BY PERSON_MIETVERTRAG_ID ASC");
        if (!empty($result)) {
            $kontaktdaten = '';
            Foreach ($result as $row) {
                $person_id = $row['PERSON_MIETVERTRAG_PERSON_ID'];
                $arr = finde_detail_kontakt_arr('PERSON', $person_id, $hinweis_an);
                if (!empty($arr)) {
                    $anz = count($arr);
                    for ($a = 0; $a < $anz; $a++) {
                        $dname = $arr[$a]['DETAIL_NAME'];
                        $dinhalt = $arr[$a]['DETAIL_INHALT'];
                        if ($dname == 'Hinweis') {
                            $kontaktdaten .= "<br><p style=\"color:#0000ff\"><b>$dname:$dinhalt</b></p>";
                        } else {
                            $kontaktdaten .= "<br><b>$dname</b>:$dinhalt";
                        }
                    }
                }
            }
            return $kontaktdaten;
        }
    }
}


function tages_ansicht($benutzer_id, $datum_d)
{
    session()->put('mitarbeiter_id', $benutzer_id);
    $benutzername = get_benutzername($benutzer_id);
    $wochentag = get_wochentag_name($datum_d);
    $kw = get_kw($datum_d);
    $tag_davor = tage_minus_wp($datum_d, 1);
    $tag_danach = tage_plus_wp($datum_d, 1);
    $link_tag_davor = "<a class=\"rot\" onclick=\"daj3('" . route('web::wartungsplaner::ajax', ['option' => 'termine_tag_tab', 'b_id' => $benutzer_id, 'datum' => $tag_davor], false) . "', 'rightBox1');daj3('" . route('web::wartungsplaner::ajax', ['option' => 'karte', 'b_id' => $benutzer_id, 'datum_d' => $tag_davor], false) . "','rightBox')\">$tag_davor</a>";
    $link_tag_danach = "<a class=\"rot\"  onclick=\"daj3('" . route('web::wartungsplaner::ajax', ['option' => 'termine_tag_tab', 'b_id' => $benutzer_id, 'datum' => $tag_danach], false) . "', 'rightBox1');daj3('" . route('web::wartungsplaner::ajax', ['option' => 'karte', 'b_id' => $benutzer_id, 'datum_d' => $tag_danach], false) . "','rightBox')\">$tag_danach</a>";
    echo "<p class=\"zeile_hinweis\"><b>$link_tag_davor | Kalender von $benutzername (KW: $kw) Datum: $wochentag, $datum_d | <b>$link_tag_danach</b></p>";
    $arr = tages_termine_arr_b($benutzer_id, $datum_d);
    $anz = count($arr);

    echo "<table><tr><th>ZEIT</th><th>TERMIN</th><th>INFOS</th></tr>";

    $von_str = '';

    for ($a = 0; $a < $anz; $a++) {
        /*Zeile TERMIN FREI*/
        $von = $arr[$a]['VON'];
        $bis = $arr[$a]['BIS'];
        $txt = $arr[$a]['TEXT'];
        if (!empty($arr[$a]['HINWEIS'])) {
            $hinweis = $arr[$a]['HINWEIS'];
        }
        /*Alle Termine ohne Vortermin, also nur erster Termin*/
        if ($a == 0) {
            /*Von zu Hause*/
            $ggg = new general();
            $ben_profil = $ggg->get_wteam_profil($benutzer_id);
            if (isset($ben_profil['START_ADRESSE'])) {
                $b_str_arr = explode(',', $ben_profil['START_ADRESSE']);
                $b_str = $b_str_arr[0];
                $b_nr = $b_str_arr[1];
                $b_plz = $b_str_arr[2];
                $b_ort = $b_str_arr[3];
                $lat_lon_db_start = get_lat_lon_db_osm($b_str, $b_nr, $b_plz, $b_ort);
                $von_str = "$b_str $b_nr";
            } else {
                /*Aus der Basis*/
                $lat_lon_db_start = get_lat_lon_db_osm(START_STRASSE, START_NR, START_PLZ, START_ORT);
                $von_str = START_STRASSE . ' ' . START_NR;
            }
        }

        /*Aktueller Termin ist frei*/
        if (!isset($arr[$a]['KOSTENTRAEGER_TYP'])) {
            $status = 'frei';
            if (!check_is_eigentuemer(session()->get('kos_id'))) {
                $g_z = new general();
                $g_z->get_partner_info(session()->get('kos_id'));
                $lat_lon_db_ziel = get_lat_lon_db_osm($g_z->partner_strasse, $g_z->partner_hausnr, $g_z->partner_plz, $g_z->partner_ort);
                $bis_str = "$g_z->partner_strasse $g_z->partner_hausnr";
                $kunden_info = "$g_z->partner_name<br>$g_z->partner_strasse $g_z->partner_hausnr, $g_z->partner_plz $g_z->partner_ort";
                $kunden_info .= kontaktdaten_anzeigen_kunde(session()->get('kos_id'));
            } else {
                /*Mieterinformationen holen*/
                $g_id = session()->get('g_id');
                $g_info_arr = geraete_info_arr($g_id);
                $einheit_bez = $g_info_arr[0]['LAGE_RAUM'];
                $gr = new general();
                $gr->get_lanlon_mieter($einheit_bez);
                $lat_lon_db_ziel = $gr->mieter_lat_lon_db_ziel;
                $kunden_info = "$gr->mieter_info";
                $kunden_info .= kontaktdaten_anzeigen_mieter($einheit_bez);
                $bis_str = $gr->mieter_bis_str;
            }
        } else {
            $status = 'belegt';
            if (!check_is_eigentuemer($arr[$a]['KOSTENTRAEGER_ID'])) {
                $g_z = new general();
                $g_z->get_partner_info($arr[$a]['KOSTENTRAEGER_ID']);
                $lat_lon_db_ziel = get_lat_lon_db_osm($g_z->partner_strasse, $g_z->partner_hausnr, $g_z->partner_plz, $g_z->partner_ort);
                $bis_str = "$g_z->partner_strasse $g_z->partner_hausnr";
                $kunden_info = "$g_z->partner_name<br>$g_z->partner_strasse $g_z->partner_hausnr, $g_z->partner_plz $g_z->partner_ort";
                $kunden_info .= kontaktdaten_anzeigen_kunde($arr[$a]['KOSTENTRAEGER_ID']);
            } else {
                /*Mieterinformationen holen*/
                $g_id = $arr[$a]['GERAETE_ID'];
                $g_info_arr = geraete_info_arr($g_id);
                $einheit_bez = $g_info_arr[0]['LAGE_RAUM'];
                $gr = new general();
                $gr->get_lanlon_mieter($einheit_bez);
                $lat_lon_db_ziel = $gr->mieter_lat_lon_db_ziel;
                $kunden_info = "$gr->mieter_info";
                $kunden_info .= kontaktdaten_anzeigen_mieter($einheit_bez);
                $bis_str = $gr->mieter_bis_str;
            }
        }

        if ($von_str != $bis_str) {
            $g_e = new general();
            $g_e->get_fahrzeit_entf($lat_lon_db_start, $lat_lon_db_ziel);
            echo "<tr class=\"zeile_detail\"><td colspan=\"3\">$von_str bis $bis_str | <b>Entfernung: $g_e->km | Fahrzeit: $g_e->fahrzeit</b> |</td></tr>";
        }


        if ($status == 'frei') {
            $js_termin = "onclick=\"daj3('" . route('web::wartungsplaner::ajax', ['option' => 'form_termin_eintragen', 'b_id' => $benutzer_id, 'datum' => $datum_d, 'von' => $von, 'bis' => $bis], false) . "','rightBox1');\"";
            $link_neuer_termin = "<button type=\"button\" $js_termin value=\"Termin eintragen\">Termin eintragen</button>";
            $g_id = session()->get('g_id');
            $g_info_arr = geraete_info_arr($g_id);
            $g_info = $g_info_arr[0]['HERSTELLER'] . '<br>' . $g_info_arr[0]['BEZEICHNUNG'];
        } else {
            $g_id = $arr[$a]['GERAETE_ID'];
            $g_info_arr = geraete_info_arr($g_id);
            $g_info = $g_info_arr[0]['HERSTELLER'] . '<br>' . $g_info_arr[0]['BEZEICHNUNG'];
            $link_neuer_termin = '';
            $termin_dat = $arr[$a]['DAT'];
            $funk1 = "termin_loeschen|$termin_dat";
            $funk2 = "daj3|" . route('web::wartungsplaner::ajax', ['option' => 'termine_tag_tab', 'b_id' => $benutzer_id, 'datum' => $datum_d, 'von' => $von, 'bis' => $bis], false) . "|rightBox1";
            $js_onsubmit = "onclick='yes_no(\"$funk1\", \"$funk2\")'";
        }
        echo "<tr class=\"zeile_$status\"><td>$von<br>$bis";
        $von_arr = explode(':', $von);
        $bis_arr = explode(':', $bis);
        $std = $bis_arr[0] - $von_arr[0];
        for ($br = 0; $br < $std; $br++) {
            echo "<br>";
        }
        if ($status == 'belegt') {

            button('btl_del', 'btn_del', 'Absagen', $js_onsubmit);
        }
        echo "</td><td valign=\"top\">$kunden_info<br>$link_neuer_termin<br>";
        if ($status == 'belegt') {
            if ($txt or $hinweis) {
                if ($txt != 'x') {
                    echo "<p class=\"zeile_hinweis\">$txt</p>";
                }
                if ($hinweis != 'x') {
                    echo "<p class=\"zeile_hinweis\">$hinweis</p>";
                }
            }
        }
        echo "</td><td valign=\"top\">$g_info";
        if ($status == 'frei') {
            $js_aendern = "onclick=\"daj3('" . route('web::wartungsplaner::ajax', ['option' => 'wt_aendern', 'g_id' => session()->get('g_id')], false) . "', 'rightBox')\"";
        } else {
            $js_aendern = "onclick=\"daj3('" . route('web::wartungsplaner::ajax', ['option' => 'wt_aendern', 'g_id' => $g_id], false) . "', 'rightBox')\"";

        }
        echo "<br>";
        button('btl_aen', 'btn_aen', 'Gerät ändern', $js_aendern);
        echo "</td></tr>";
        echo "<tr class=\"zeile_ueber\"><td colspan=\"3\"></td></tr>";

        $von_str = $bis_str;
        $lat_lon_db_start = $lat_lon_db_ziel;

    }//end for
}


function tages_ansicht_arr($benutzer_id, $datum_d)
{
    session()->put('mitarbeiter_id', $benutzer_id);

    $arr = tages_termine_arr_b($benutzer_id, $datum_d);
    $anz = count($arr);


    $von_str = '';

    for ($a = 0; $a < $anz; $a++) {
        /*Zeile TERMIN FREI*/
        $von = $arr[$a]['VON'];
        $bis = $arr[$a]['BIS'];
        /*Alle Termine ohne Vortermin, also nur erster Termin*/
        if ($a == 0) {
            /*Von zu Hause*/
            $ggg = new general();
            $ben_profil = $ggg->get_wteam_profil($benutzer_id);
            if (isset($ben_profil['START_ADRESSE'])) {
                $b_str_arr = explode(',', $ben_profil['START_ADRESSE']);
                $b_str = $b_str_arr[0];
                $b_nr = $b_str_arr[1];
                $b_plz = $b_str_arr[2];
                $b_ort = $b_str_arr[3];
                $lat_lon_db_start = get_lat_lon_db_osm($b_str, $b_nr, $b_plz, $b_ort);
                $von_str = "$b_str $b_nr";
            } else {
                /*Aus der Basis*/
                $lat_lon_db_start = get_lat_lon_db_osm(START_STRASSE, START_NR, START_PLZ, START_ORT);
                $von_str = START_STRASSE . ' ' . START_NR;
            }
        }

        /*Aktueller Termin ist frei*/
        if (!isset($arr[$a]['KOSTENTRAEGER_TYP'])) {
            if (!check_is_eigentuemer(session()->get('kos_id'))) {
                $g_z = new general();
                $g_z->get_partner_info(session()->get('kos_id'));
                $lat_lon_db_ziel = get_lat_lon_db_osm($g_z->partner_strasse, $g_z->partner_hausnr, $g_z->partner_plz, $g_z->partner_ort);
                $bis_str = "$g_z->partner_strasse $g_z->partner_hausnr";
                $kunden_info = "$g_z->partner_name<br>$g_z->partner_strasse $g_z->partner_hausnr, $g_z->partner_plz $g_z->partner_ort";
                $kunden_info .= kontaktdaten_anzeigen_kunde(session()->get('kos_id'));
            } else {
                /*Mieterinformationen holen*/
                $g_id = session()->get('g_id');
                $g_info_arr = geraete_info_arr($g_id);
                $einheit_bez = $g_info_arr[0]['LAGE_RAUM'];
                $gr = new general();
                $gr->get_lanlon_mieter($einheit_bez);
                $lat_lon_db_ziel = $gr->mieter_lat_lon_db_ziel;
                $kunden_info = "$gr->mieter_info";
                $kunden_info .= kontaktdaten_anzeigen_mieter($einheit_bez);
                $bis_str = $gr->mieter_bis_str;
            }
        } else {
            $status = 'belegt';
            if (!check_is_eigentuemer($arr[$a]['KOSTENTRAEGER_ID'])) {
                $g_z = new general();
                $g_z->get_partner_info($arr[$a]['KOSTENTRAEGER_ID']);
                $lat_lon_db_ziel = get_lat_lon_db_osm($g_z->partner_strasse, $g_z->partner_hausnr, $g_z->partner_plz, $g_z->partner_ort);
                $bis_str = "$g_z->partner_strasse $g_z->partner_hausnr";
                $kunden_info = "$g_z->partner_name<br>$g_z->partner_strasse $g_z->partner_hausnr, $g_z->partner_plz $g_z->partner_ort";
                $kunden_info .= kontaktdaten_anzeigen_kunde($arr[$a]['KOSTENTRAEGER_ID']);
            } else {
                /*Mieterinformationen holen*/
                $g_id = $arr[$a]['GERAETE_ID'];
                $g_info_arr = geraete_info_arr($g_id);
                $einheit_bez = $g_info_arr[0]['LAGE_RAUM'];
                $gr = new general();
                $gr->get_lanlon_mieter($einheit_bez);
                $lat_lon_db_ziel = $gr->mieter_lat_lon_db_ziel;
                $kunden_info = "$gr->mieter_info";
                $kunden_info .= kontaktdaten_anzeigen_mieter($einheit_bez);
                $bis_str = $gr->mieter_bis_str;
            }
        }

        $entf[$a]['VON'] = "$von";
        $entf[$a]['BIS'] = "$bis";
        $entf[$a]['VON_BIS'] = "$von_str | $bis_str";
        $lat_lon_db_start_arr = explode(',', $lat_lon_db_start);
        $lat_lon_db_ziel_arr = explode(',', $lat_lon_db_ziel);
        $lat1 = $lat_lon_db_start_arr[0];
        $lon1 = $lat_lon_db_start_arr[1];
        $lat2 = $lat_lon_db_ziel_arr[0];
        $lon2 = $lat_lon_db_ziel_arr[1];
        $entf[$a]['VON_KM'] = get_math_entfernung($lat1, $lon1, $lat2, $lon2);
        $entf[$a]['VON_ZEIT'] = get_fahrzeit(30, $entf[$a]['VON_KM']);

        $von_str = $bis_str;
        $lat_lon_db_start = $lat_lon_db_ziel;

    }//end for
    return $entf;
}


function get_fahrzeit($g = 50, $km)
{
    $fz_min = $km * 60 / $g;
    return min_in_zeit($fz_min);
}


function tages_ansicht_neu($benutzer_id, $datum_d, $hinweis_an = 1)
{
    session()->put('mitarbeiter_id', $benutzer_id);
    $wochentag = get_wochentag_name($datum_d);
    $arr = tages_termine_arr_b($benutzer_id, $datum_d);
    $anz = count($arr);
    echo "<table height=\"100%\"><tr height=\"20px\" ><th>ZEIT</th><th>TERMIN";
    $js_pdf = "onclick=\"window.location='" . route('web::wartungsplaner::ajax', ['option' => 'pdf_wp', 'datum_d' => $datum_d, 'benutzer_id' => $benutzer_id], false) . "'\" target=\"_blank\"";
    button('btn_pdf', 'btn_pdf', 'PDF', $js_pdf);
    echo "</th><th>INFOS</th></tr>";
    echo "<tr><td height=\"20px\" colspan=\"3\"><b>$wochentag, $datum_d</b></td></tr>";
    $bis_str = '';
    $kunden_info = '';

    for ($a = 0; $a < $anz; $a++) {
        /*Zeile TERMIN FREI*/
        $von = $arr[$a]['VON'];
        $bis = $arr[$a]['BIS'];
        $hinweis = $arr[$a]['HINWEIS'];

        /*Alle Termine ohne Vortermin, also nur erster Termin*/
        if ($a == 0) {
            $start = 'BASIS/HAUS';
            /*Von zu Hause*/
            $ggg = new general();
            $ben_profil = $ggg->get_wteam_profil($benutzer_id);
            if (isset($ben_profil['START_ADRESSE'])) {
                $b_str_arr = explode(',', $ben_profil['START_ADRESSE']);
                $b_str = $b_str_arr[0];
                $b_nr = $b_str_arr[1];
                $b_plz = $b_str_arr[2];
                $b_ort = $b_str_arr[3];
                $von_str = "$b_str $b_nr";
            } else {
                /*Aus der Basis*/
                $von_str = START_STRASSE . ' ' . START_NR;
                $kunden_info = "BASIS/HAUS";
            }
        }

        /*Aktueller Termin ist frei*/
        if (!isset($arr[$a]['KOSTENTRAEGER_TYP'])) {
            $status = 'frei';
            if (!check_is_eigentuemer(session()->get('kos_id'))) {
                $g_z = new general();
                $g_z->get_partner_info(session()->get('kos_id'));
                $kunden_info = 'FREI';
            } else {
                /*Mieterinformationen holen*/
                $kunden_info = 'FREI';
            }
        } else {
            $status = 'belegt';
            if (!check_is_eigentuemer($arr[$a]['KOSTENTRAEGER_ID'])) {
                $g_z = new general();
                $g_z->get_partner_info($arr[$a]['KOSTENTRAEGER_ID']);
                $bis_str = "$g_z->partner_strasse $g_z->partner_hausnr";
                $kunden_info = "$g_z->partner_name<br>$g_z->partner_strasse $g_z->partner_hausnr, $g_z->partner_plz $g_z->partner_ort";
                $kunden_info .= kontaktdaten_anzeigen_kunde($arr[$a]['KOSTENTRAEGER_ID']);
            } else {
                /*Mieterinformationen holen*/
                $g_id = $arr[$a]['GERAETE_ID'];
                $g_info_arr = geraete_info_arr($g_id);
                $einheit_bez = $g_info_arr[0]['LAGE_RAUM'];
                $gr = new general();
                $gr->get_lanlon_mieter($einheit_bez);
                $kunden_info = "$gr->mieter_info";
                $kunden_info .= kontaktdaten_anzeigen_mieter($einheit_bez, $hinweis_an);
                $bis_str = $gr->mieter_bis_str;
            }
            if ($hinweis != 'x') {
                $kunden_info .= "<br>Hinweis: <b>$hinweis</b>";
            }
        }

        if (!$status == 'frei') {
            $g_id = $arr[$a]['GERAETE_ID'];
            $g_info_arr = geraete_info_arr($g_id);
            $g_info = $g_info_arr[0]['HERSTELLER'] . '<br>' . $g_info_arr[0]['BEZEICHNUNG'];
            $link_neuer_termin = ' ';
            $termin_dat = $arr[$a]['DAT'];
            $funk1 = "termin_loeschen|$termin_dat";
            $funk2 = "daj3|" . route('web::wartungsplaner::ajax', ['option' => 'termine_tag_tab', 'b_id' => $benutzer_id, 'datum' => $datum_d], false) . "|rightBox1";
        }
        $von_arr = explode(':', $von);
        $bis_arr = explode(':', $bis);
        $std = $bis_arr[0] - $von_arr[0];
        echo "<tr class=\"zeile_$status\"><td>$von<br>$bis</td>";
        echo "<td valign=\"top\">$kunden_info<br>$link_neuer_termin";
        if ($status == 'frei') {
            for ($br = 0; $br < $std; $br++) {
                echo "<br>";

            }
        }

        echo "</td><td valign=\"top\">$g_info";
        echo "<br>";
        echo "</td></tr>";
    }//end for
    echo "</table>";
}

function tages_ansicht_umkreis($benutzer_id, $datum_d)
{
    session()->put('mitarbeiter_id', $benutzer_id);
    $benutzername = get_benutzername($benutzer_id);
    $wochentag = get_wochentag_name($datum_d);
    $kw = get_kw($datum_d);
    $tag_davor = tage_minus_wp($datum_d, 1);
    $tag_danach = tage_plus_wp($datum_d, 1);
    $link_tag_davor = "<a class=\"rot\" onclick=\"daj3('" . route('web::wartungsplaner::ajax', ['option' => 'termine_tag_tab', 'b_id' => $benutzer_id, 'datum' => $tag_davor], false) . "', 'rightBox1')\">$tag_davor</a>";
    $link_tag_danach = "<a class=\"rot\"  onclick=\"daj3('" . route('web::wartungsplaner::ajax', ['option' => 'termine_tag_tab', 'b_id' => $benutzer_id, 'datum' => $tag_danach], false) . "', 'rightBox1')\">$tag_danach</a>";
    echo "<p class=\"zeile_ueber\"><b>$link_tag_davor | Kalender von $benutzername (KW: $kw) Datum: $wochentag, $datum_d | <b>$link_tag_danach</b></p>";
    $arr = tages_termine_arr_b($benutzer_id, $datum_d);
    $anz = count($arr);
    echo "<table><tr><th>ZEIT</th><th>TERMIN</th><th>OPTION</th></tr>";

    $von_str = '';
    for ($a = 0; $a < $anz; $a++) {
        /*Zeile TERMIN FREI*/
        $von = $arr[$a]['VON'];
        $bis = $arr[$a]['BIS'];

        /*Alle Termine ohne Vortermin, also nur erster Termin*/
        if ($a == 0) {
            $start = 'BASIS/HAUS';
            /*Von zu Hause*/
            $ggg = new general();
            $ben_profil = $ggg->get_wteam_profil($benutzer_id);
            if (isset($ben_profil['START_ADRESSE'])) {
                $b_str_arr = explode(',', $ben_profil['START_ADRESSE']);
                $b_str = $b_str_arr[0];
                $b_nr = $b_str_arr[1];
                $b_plz = $b_str_arr[2];
                $b_ort = $b_str_arr[3];
                $lat_lon_db_start = get_lat_lon_db_osm($b_str, $b_nr, $b_plz, $b_ort);
                $von_str = "$b_str $b_nr";
            } else {
                /*Aus der Basis*/
                $lat_lon_db_start = get_lat_lon_db_osm(START_STRASSE, START_NR, START_PLZ, START_ORT);
                $von_str = START_STRASSE . ' ' . START_NR;
                $kunden_info = "BASIS/HAUS";
            }
        }

        /*Aktueller Termin ist frei*/
        if (!isset($arr[$a]['KOSTENTRAEGER_TYP'])) {
            $status = 'frei';
            if (session()->has('kos_id')) {
                if (!check_is_eigentuemer(session()->get('kos_id'))) {
                    $g_z = new general();
                    $g_z->get_partner_info(session()->get('kos_id'));
                    $lat_lon_db_ziel = get_lat_lon_db_osm($g_z->partner_strasse, $g_z->partner_hausnr, $g_z->partner_plz, $g_z->partner_ort);
                    $bis_str = "$g_z->partner_strasse $g_z->partner_hausnr";
                    $kunden_info = "$g_z->partner_name<br>$g_z->partner_strasse $g_z->partner_hausnr, $g_z->partner_plz $g_z->partner_ort";
                } else {
                    /*Mieterinformationen holen*/
                    $g_id = session()->get('g_id');
                    $g_info_arr = geraete_info_arr($g_id);
                    $einheit_bez = $g_info_arr[0]['LAGE_RAUM'];
                    $gr = new general();
                    $gr->get_lanlon_mieter($einheit_bez);
                    $lat_lon_db_ziel = $gr->mieter_lat_lon_db_ziel;
                    $kunden_info = "$gr->mieter_info";
                    $bis_str = $gr->mieter_bis_str;
                }
            } else {
                $bis_str = session()->get('ziel_str') . ' ' . session()->get('ziel_nr');
                $kunden_info = "NEUKUNDE xx<br><b>$bis_str</b>";
                $lat_lon_db_ziel = get_lat_lon_db_osm(session()->get('ziel_str'), session()->get('ziel_nr'), session()->get('ziel_plz'), session()->get('ziel_ort'));
            }
        } else {
            $status = 'belegt';
            if (!check_is_eigentuemer($arr[$a]['KOSTENTRAEGER_ID'])) {
                $g_z = new general();
                $g_z->get_partner_info($arr[$a]['KOSTENTRAEGER_ID']);
                $lat_lon_db_ziel = get_lat_lon_db_osm($g_z->partner_strasse, $g_z->partner_hausnr, $g_z->partner_plz, $g_z->partner_ort);
                $bis_str = "$g_z->partner_strasse $g_z->partner_hausnr";
                $kunden_info = "$g_z->partner_name<br>$g_z->partner_strasse $g_z->partner_hausnr, $g_z->partner_plz $g_z->partner_ort";
                $g_id = $arr[$a]['GERAETE_ID'];
            } else {
                /*Mieterinformationen holen*/
                $g_id = $arr[$a]['GERAETE_ID'];
                $g_info_arr = geraete_info_arr($g_id);
                $einheit_bez = $g_info_arr[0]['LAGE_RAUM'];
                $gr = new general();
                $gr->get_lanlon_mieter($einheit_bez);
                $lat_lon_db_ziel = $gr->mieter_lat_lon_db_ziel;
                $kunden_info = "$gr->mieter_info";
                $bis_str = $gr->mieter_bis_str;
            }
        }

        if ($von_str != $bis_str) {
            $g_e = new general();
            $g_e->get_fahrzeit_entf($lat_lon_db_start, $lat_lon_db_ziel);

            echo "<tr class=\"zeile_detail\"><td colspan=\"3\">$von_str bis $bis_str | <b>Entfernung: $g_e->km | Fahrzeit: $g_e->fahrzeit</b> |</td></tr>";
        }

        echo "<tr class=\"zeile_$status\"><td id=\"$von-$bis\" name=\"$von-$bis\">$von<br>$bis";

        echo "</td><td valign=\"top\">$kunden_info<br><br></td>";
        if ($status == 'frei') {
            $js_res = "onclick=\"termin_reservieren('$datum_d', '$von', '$bis', '$benutzer_id');\"";
            echo "<td>";
            $dauer_min = getzeitdiff_min($von, $bis);
            if (session()->has('termin_dauer')) {
                if ($dauer_min > session()->get('termin_dauer')) {
                    button('btn_reserve', "btn_reserve$von$bis", 'Termin vormerken', $js_res);
                }
            } else {
                button('btn_reserve', "btn_reserve$von$bis", 'Termin vormerken', $js_res);
            }
            echo "</td>";
        }
        echo "<br>";
        echo "</tr>";
        echo "<tr class=\"zeile_ueber\"><td colspan=\"3\"></td></tr>";

        $von_str = $bis_str;
        $lat_lon_db_start = $lat_lon_db_ziel;

    }//end for
}


function tages_ansicht_umkreis_arr($benutzer_id, $datum_d)
{
    session()->put('mitarbeiter_id', $benutzer_id);
    $arr = tages_termine_arr_b($benutzer_id, $datum_d);
    $anz = count($arr);

    $von_str = '';

    for ($a = 0; $a < $anz; $a++) {
        /*Zeile TERMIN FREI*/
        $von = $arr[$a]['VON'];
        $bis = $arr[$a]['BIS'];

        /*Alle Termine ohne Vortermin, also nur erster Termin*/
        if ($a == 0) {
            $start = 'BASIS/HAUS';
            /*Von zu Hause*/
            $ggg = new general();
            $ben_profil = $ggg->get_wteam_profil($benutzer_id);
            if (isset($ben_profil['START_ADRESSE'])) {
                $b_str_arr = explode(',', $ben_profil['START_ADRESSE']);
                $b_str = $b_str_arr[0];
                $b_nr = $b_str_arr[1];
                $b_plz = $b_str_arr[2];
                $b_ort = $b_str_arr[3];
                $lat_lon_db_start = get_lat_lon_db_osm($b_str, $b_nr, $b_plz, $b_ort);
                $von_str = "$b_str $b_nr";
            } else {
                /*Aus der Basis*/
                $lat_lon_db_start = get_lat_lon_db_osm(START_STRASSE, START_NR, START_PLZ, START_ORT);
                $von_str = START_STRASSE . ' ' . START_NR;
                $kunden_info = "BASIS/HAUS";
            }
        }

        /*Aktueller Termin ist frei*/
        if (!isset($arr[$a]['KOSTENTRAEGER_TYP'])) {
            $status = 'frei';
            if (session()->has('kos_id')) {
                if (!check_is_eigentuemer(session()->get('kos_id'))) {
                    $g_z = new general();
                    $g_z->get_partner_info(session()->get('kos_id'));
                    $lat_lon_db_ziel = get_lat_lon_db_osm($g_z->partner_strasse, $g_z->partner_hausnr, $g_z->partner_plz, $g_z->partner_ort);
                    $bis_str = "$g_z->partner_strasse $g_z->partner_hausnr";
                    $kunden_info = "$g_z->partner_name<br>$g_z->partner_strasse $g_z->partner_hausnr, $g_z->partner_plz $g_z->partner_ort";
                } else {
                    /*Mieterinformationen holen*/
                    $g_id = session()->get('g_id');
                    $g_info_arr = geraete_info_arr($g_id);
                    $einheit_bez = $g_info_arr[0]['LAGE_RAUM'];
                    $gr = new general();
                    $gr->get_lanlon_mieter($einheit_bez);
                    $lat_lon_db_ziel = $gr->mieter_lat_lon_db_ziel;
                    $kunden_info = "$gr->mieter_info";
                    $bis_str = $gr->mieter_bis_str;
                }
            } else {
                $bis_str = session()->get('ziel_str') . ' ' . session()->get('ziel_nr');
                $kunden_info = "NEUKUNDE yy<br><b>$bis_str</b>";
                $lat_lon_db_ziel = get_lat_lon_db_osm(session()->get('ziel_str'), session()->get('ziel_nr'), session()->get('ziel_plz'), session()->get('ziel_ort'));
            }
        } else {
            $status = 'belegt';
            if (!check_is_eigentuemer($arr[$a]['KOSTENTRAEGER_ID'])) {
                $g_z = new general();
                $g_z->get_partner_info($arr[$a]['KOSTENTRAEGER_ID']);
                $lat_lon_db_ziel = get_lat_lon_db_osm($g_z->partner_strasse, $g_z->partner_hausnr, $g_z->partner_plz, $g_z->partner_ort);
                $bis_str = "$g_z->partner_strasse $g_z->partner_hausnr";
                $kunden_info = "$g_z->partner_name<br>$g_z->partner_strasse $g_z->partner_hausnr, $g_z->partner_plz $g_z->partner_ort";
                $g_id = $arr[$a]['GERAETE_ID'];
            } else {
                /*Mieterinformationen holen*/
                $g_id = $arr[$a]['GERAETE_ID'];
                $g_info_arr = geraete_info_arr($g_id);
                $einheit_bez = $g_info_arr[0]['LAGE_RAUM'];
                $gr = new general();
                $gr->get_lanlon_mieter($einheit_bez);
                $lat_lon_db_ziel = $gr->mieter_lat_lon_db_ziel;
                $kunden_info = "$gr->mieter_info";
                $bis_str = $gr->mieter_bis_str;
            }
        }

        $entf[$a]['VON'] = "$von";
        $entf[$a]['BIS'] = "$bis";
        $entf[$a]['VON_BIS'] = "$von_str | $bis_str";
        $lat_lon_db_start_arr = explode(',', $lat_lon_db_start);
        $lat_lon_db_ziel_arr = explode(',', $lat_lon_db_ziel);
        $lat1 = $lat_lon_db_start_arr[0];
        $lon1 = $lat_lon_db_start_arr[1];
        $lat2 = $lat_lon_db_ziel_arr[0];
        $lon2 = $lat_lon_db_ziel_arr[1];
        $entf[$a]['VON_KM'] = get_math_entfernung($lat1, $lon1, $lat2, $lon2);
        $entf[$a]['VON_ZEIT'] = get_fahrzeit(30, $entf[$a]['VON_KM']);

        $von_str = $bis_str;
        $lat_lon_db_start = $lat_lon_db_ziel;

    }//end for
    return $entf;
}


function tages_ansicht_gross($benutzer_id, $datum_d)
{
    session()->put('mitarbeiter_id', $benutzer_id);
    $benutzername = get_benutzername($benutzer_id);
    $wochentag = get_wochentag_name($datum_d);
    $kw = get_kw($datum_d);
    $tag_davor = tage_minus_wp($datum_d, 1);
    $tag_danach = tage_plus_wp($datum_d, 1);
    $link_tag_davor = "<a class=\"rot\" onclick=\"daj3('" . route('web::wartungsplaner::ajax', ['option' => 'termine_tag_tab', 'b_id' => $benutzer_id, 'datum' => $tag_davor], false) . "', 'rightBox1')\">$tag_davor</a>";
    $link_tag_danach = "<a class=\"rot\"  onclick=\"daj3('" . route('web::wartungsplaner::ajax', ['option' => 'termine_tag_tab', 'b_id' => $benutzer_id, 'datum' => $tag_danach], false) . "', 'rightBox1')\">$tag_danach</a>";
    echo "<p class=\"zeile_ueber\"><b>Kalender von $benutzername (KW: $kw) Datum: $wochentag, $datum_d</b></p>";
    $arr = tages_termine_arr_b($benutzer_id, $datum_d);
    $anz = count($arr);
    echo "<table><tr><th>ZEIT</th><th>TERMIN</th><th>INFOS</th></tr>";

    $von_str = '';
    $bis_str = '';
    $kunden_info = '';

    for ($a = 0; $a < $anz; $a++) {
        /*Zeile TERMIN FREI*/
        $von = $arr[$a]['VON'];
        $bis = $arr[$a]['BIS'];

        /*Alle Termine ohne Vortermin, also nur erster Termin*/
        if ($a == 0) {
            $start = 'BASIS/HAUS';
            /*Von zu Hause*/
            $ggg = new general();
            $ben_profil = $ggg->get_wteam_profil($benutzer_id);
            if (isset($ben_profil['START_ADRESSE'])) {
                $b_str_arr = explode(',', $ben_profil['START_ADRESSE']);
                $b_str = $b_str_arr[0];
                $b_nr = $b_str_arr[1];
                $b_plz = $b_str_arr[2];
                $b_ort = $b_str_arr[3];
                $lat_lon_db_start = get_lat_lon_db_osm($b_str, $b_nr, $b_plz, $b_ort);
                $von_str = "$b_str $b_nr";
            } else {
                /*Aus der Basis*/
                $lat_lon_db_start = get_lat_lon_db_osm(START_STRASSE, START_NR, START_PLZ, START_ORT);
                $von_str = START_STRASSE . ' ' . START_NR;
                $kunden_info = "BASIS/HAUS";
            }
        }

        /*Aktueller Termin ist frei*/
        if (!isset($arr[$a]['KOSTENTRAEGER_TYP'])) {
            $status = 'frei';
            if (!check_is_eigentuemer(session()->get('kos_id'))) {
                $g_z = new general();
                $g_z->get_partner_info(session()->get('kos_id'));
                $lat_lon_db_ziel = get_lat_lon_db_osm($g_z->partner_strasse, $g_z->partner_hausnr, $g_z->partner_plz, $g_z->partner_ort);
                $bis_str = "$g_z->partner_strasse $g_z->partner_hausnr";
                #$kunden_info= "$g_z->partner_name $bis_str" ;
                $kunden_info = "$g_z->partner_name<br>$g_z->partner_strasse $g_z->partner_hausnr, $g_z->partner_plz $g_z->partner_ort";
                $kunden_info .= kontaktdaten_anzeigen_kunde(session()->get('kos_id'));
            } else {
                /*Mieterinformationen holen*/
                $g_id = session()->get('g_id');
                $g_info_arr = geraete_info_arr($g_id);
                $einheit_bez = $g_info_arr[0]['LAGE_RAUM'];
                $gr = new general();
                $gr->get_lanlon_mieter($einheit_bez);
                $lat_lon_db_ziel = $gr->mieter_lat_lon_db_ziel;
                $kunden_info = "$gr->mieter_info";
                $kunden_info .= kontaktdaten_anzeigen_mieter($einheit_bez);
                $bis_str = $gr->mieter_bis_str;
            }
        } else {
            $status = 'belegt';
            if (!check_is_eigentuemer($arr[$a]['KOSTENTRAEGER_ID'])) {
                $g_z = new general();
                $g_z->get_partner_info($arr[$a]['KOSTENTRAEGER_ID']);
                $lat_lon_db_ziel = get_lat_lon_db_osm($g_z->partner_strasse, $g_z->partner_hausnr, $g_z->partner_plz, $g_z->partner_ort);
                $bis_str = "$g_z->partner_strasse $g_z->partner_hausnr";
                $kunden_info = "$g_z->partner_name<br>$g_z->partner_strasse $g_z->partner_hausnr, $g_z->partner_plz $g_z->partner_ort";
                $g_id = $arr[$a]['GERAETE_ID'];
                $kunden_info .= kontaktdaten_anzeigen_kunde($arr[$a]['KOSTENTRAEGER_ID']);
            } else {
                /*Mieterinformationen holen*/
                $g_id = $arr[$a]['GERAETE_ID'];
                $g_info_arr = geraete_info_arr($g_id);
                $einheit_bez = $g_info_arr[0]['LAGE_RAUM'];
                $gr = new general();
                $gr->get_lanlon_mieter($einheit_bez);
                $lat_lon_db_ziel = $gr->mieter_lat_lon_db_ziel;
                $kunden_info = "$gr->mieter_info";
                $kunden_info .= kontaktdaten_anzeigen_mieter($einheit_bez);
                $bis_str = $gr->mieter_bis_str;
            }
        }

        if ($von_str != $bis_str) {
            $g_e = new general();
            $g_e->get_fahrzeit_entf($lat_lon_db_start, $lat_lon_db_ziel);
            echo "<tr class=\"zeile_detail\"><td colspan=\"3\">$von_str bis $bis_str | <b>Entfernung: $g_e->km | Fahrzeit: $g_e->fahrzeit</b> |</td></tr>";
        }


        if ($status == 'frei') {
            $js_termin = "onclick=\"daj3('" . route('web::wartungsplaner::ajax', ['option' => 'form_termin_eintragen', 'b_id' => $benutzer_id, 'datum' => $datum_d, 'von' => $von, 'bis' => $bis], false) . "','rightBox1');\"";
            $g_id = session()->get('g_id');
            $g_info_arr = geraete_info_arr($g_id);
            $g_info = $g_info_arr[0]['HERSTELLER'] . '<br>' . $g_info_arr[0]['BEZEICHNUNG'];
        } else {
            $g_id = $arr[$a]['GERAETE_ID'];
            $g_info_arr = geraete_info_arr($g_id);
            $g_info = $g_info_arr[0]['HERSTELLER'] . '<br>' . $g_info_arr[0]['BEZEICHNUNG'];
            $link_neuer_termin = '';
            $termin_dat = $arr[$a]['DAT'];
            $funk1 = "termin_loeschen|$termin_dat";
            $funk2 = "daj3|" . route('web::wartungsplaner::ajax', ['option' => 'termine_tag_tab', 'b_id' => $benutzer_id, 'datum' => $datum_d], false) . "|rightBox1";
            $js_onsubmit = "onclick='yes_no(\"$funk1\", \"$funk2\")'";
        }
        echo "<tr class=\"zeile_$status\"><td>$von<br>$bis";
        $von_arr = explode(':', $von);
        $bis_arr = explode(':', $bis);
        $std = $bis_arr[0] - $von_arr[0];
        for ($br = 0; $br < $std; $br++) {
            echo "<br>";
        }
        echo "</td><td valign=\"top\">$kunden_info<br>$link_neuer_termin<br></td><td valign=\"top\">$g_info";
        if ($status == 'frei') {
            $js_aendern = "onclick=\"daj3('" . route('web::wartungsplaner::ajax', ['option' => 'wt_aendern', 'g_id' => session()->get('g_id')], false) . "', 'rightBox')\"";
        } else {
            $js_aendern = "onclick=\"daj3('" . route('web::wartungsplaner::ajax', ['option' => 'wt_aendern', 'g_id' => $g_id], false) . "', 'rightBox')\"";
        }
        echo "<br>";
        echo "</td></tr>";
        echo "<tr class=\"zeile_ueber\"><td colspan=\"3\"></td></tr>";

        $von_str = $bis_str;
        $lat_lon_db_start = $lat_lon_db_ziel;

    }//end for
}


function termine_tag_tab($benutzer_id, $datum_d)
{
    tages_ansicht($benutzer_id, $datum_d);

}

/*Umkreisanzeige des Tages*/
function termine_tag_tab2($benutzer_id, $datum_d)
{
    tages_ansicht_umkreis($benutzer_id, $datum_d);
}


function termine_tag_kw($benutzer_id, $datum_d)
{
    $wochentag = get_wochentag_name($datum_d);
    $kw = get_kw($datum_d);
    $arr = get_termine_tag_arr($benutzer_id, $datum_d);

    $luecken_termine = get_luecken_termine($benutzer_id, $datum_d);

    if (is_array($luecken_termine) && !empty($arr)) {
        $my_arr = array_sortByIndex(array_merge($arr, $luecken_termine), 'VON');
    }
    if (is_array($luecken_termine) && empty($arr)) {
        $my_arr = $luecken_termine;
    }
    if (!is_array($luecken_termine) && !empty($arr)) {
        $my_arr = $arr;
    }
    $arr = $my_arr;
    unset($my_arr);

    session()->put('mitarbeiter_id', $benutzer_id);
    session()->put('kw', $kw);
    if (is_array($arr)) {
        $anz = count($arr);
        echo "<table height=\"100%\">";
        $link_tages_kal = "<a href=\"" . route('web::wartungsplaner::ajax', ['option' => 'tageskalender', 'g_id' => $g_id, 'b_id' => $benutzer_id, 'datum' => $datum_d], false) . "\">$wochentag</a>";
        echo "<tr height=\"20px\"><th colspan=\"2\">$link_tages_kal</th></tr>";
        echo "<tr height=\"20px\"><th colspan=\"2\">$datum_d</th></tr>";
        echo "<tr height=\"20px\"><th>VON:BIS</th><th>TERMIN</th></tr>";
        $datum_sql = date_german2mysql($datum_d);
        if (!check_anwesenheit($benutzer_id, $datum_sql)) {
            echo "<tr class=\"zeile_belegt\"><td valign=\"top\" colspan=\"2\">URLAUB</td></tr>";
        } else {
            $gg = new general();
            $profil_arr = $gg->get_wteam_profil($benutzer_id);
            extract($profil_arr);


            for ($a = 0; $a < $anz; $a++) {
                extract($arr[$a]);
                /*TERMIN VORHANDEN*/
                /*erster Termin*/
                if (isset($KOSTENTRAEGER_TYP)) {
                    if (!check_is_eigentuemer($KOSTENTRAEGER_ID)) {
                        $g = new general();
                        $g->get_partner_info($KOSTENTRAEGER_ID);
                        $geraete_info_arr = geraete_info_arr($GERAETE_ID);
                        extract($geraete_info_arr['0']);
                        $freie_zeit_min = getzeitdiff_min($VON, $BIS);
                        $zeit = min_in_zeit($freie_zeit_min);
                        echo "<tr class=\"zeile_belegt\"><td valign=\"top\">$VON<BR>$BIS</b></td><td valign=\"top\"><b>Kunde:</b>$g->partner_name<br>$g->partner_strasse $g->partner_hausnr<br>$g->partner_plz $g->partner_ort<br><b>Text:</b>$TEXT<br><b>Hinweis:</b>$HINWEIS<br><b>Hersteller:</b>$HERSTELLER<br><b>Bezeichnung:</b>$BEZEICHNUNG<br><b>Lage:</b>$LAGE_RAUM</td></tr>";
                        echo "<tr class=\"zeile_belegt\"><td valign=\"top\" colspan=\"2\">Dauer $zeit</td></tr>";
                        UNSET($KOSTENTRAEGER_TYP);

                    } else {
                        /*Hier Mieteranschrift wählen*/
                        echo "<tr class=\"zeile_belegt\"><td valign=\"top\">$VON<BR>$BIS</b></td><td valign=\"top\"><b>BEIM MIETER UNVOLLSTäNDIG</td></tr>";
                        echo "<tr class=\"zeile_belegt\"><td valign=\"top\" colspan=\"2\">Dauer $zeit</td></tr>";

                    }

                } else {


                    $freie_zeit_min = getzeitdiff_min($VON, $BIS);
                    $zeit = min_in_zeit($freie_zeit_min);
                    echo "<tr class=\"zeile_frei\"><td valign=\"top\">$VON<BR>$BIS</b></td><td valign=\"top\">FREI</td></tr>";
                    echo "<tr class=\"zeile_detail\"><td valign=\"top\" colspan=\"2\">Dauer $zeit</td></tr>";
                }

            }


        }//end for $a
        echo "</table>";
    } else {
        echo "<p class=\"zeile_detail\">KEINE TERMINE</p>";
    }

}


function get_luecken_termine($benutzer_id, $datum_d)
{
    $g = new general();
    $prof_arr = $g->get_wteam_profil($benutzer_id);
    extract($prof_arr);
    $start_time_arbeit_a = $VON;
    $end_time_arbeit_a = $BIS;
    $start_time_arbeit = str_replace(':', '', $VON);
    $end_time_arbeit = str_replace(':', '', $BIS);
    $arbeitszeit_min = getzeitdiff_min($VON, $BIS);
    $termine_am_tag = $TERMINE_TAG;
    $dauer_termin_min = $arbeitszeit_min / $termine_am_tag;
    unset($prof_arr);

    $arr = get_termine_tag_arr($benutzer_id, $datum_d);
    if (!empty($arr)) {
        $anz = count($arr);
        $z = 0;
        for ($a = 0; $a < $anz; $a++) {
            $VON = str_replace(':', '', substr($arr[$a]['VON'], 0, 5));
            $BIS = str_replace(':', '', substr($arr[$a]['BIS'], 0, 5));

            /*Nur erster Termin*/
            if ($a == 0) {
                /*Wenn erster Termin nach Arbeitszeitanfang*/
                if ($VON > $start_time_arbeit) {
                    $start_time = $start_time_arbeit_a;
                    $end_time = $arr[$a]['VON'];

                    if ($start_time != $end_time) {
                        $arr_new[$z]['VON'] = $start_time;
                        $arr_new[$z]['BIS'] = $end_time;
                        $arr_new[$z]['TEXT'] = 'FREI';
                        $arr_new[$z]['DAUER'] = getzeitdiff_min($start_time, $end_time);
                        $z++;
                    }
                }
            }

            /*Wenn es Nachtermine gibt*/
            if (isset($arr[$a + 1]['VON'])) {
                $start_time = $arr[$a]['BIS'];
                $end_time = $arr[$a + 1]['VON'];

                if ($start_time != $end_time) {
                    $arr_new[$z]['VON'] = $start_time;
                    $arr_new[$z]['BIS'] = $end_time;
                    $arr_new[$z]['TEXT'] = 'FREI';
                    $arr_new[$z]['DAUER'] = getzeitdiff_min($start_time, $end_time);
                    $z++;
                }

            }
            if ($a == $anz - 1) {
                //Keine Nachtermine d.h letzter Termin*/
                if ($BIS < $end_time_arbeit) {
                    $start_time = $arr[$a]['BIS'];
                    $end_time = $end_time_arbeit_a;
                    if ($start_time != $end_time) {
                        $arr_new[$z]['VON'] = $start_time;
                        $arr_new[$z]['BIS'] = $end_time;
                        $arr_new[$z]['TEXT'] = 'FREI';
                        #echo "$start_time $end_time<br>";
                        $arr_new[$z]['DAUER'] = getzeitdiff_min($start_time, $end_time);

                        $z++;
                    }
                }
            }


        }//end for


        /*WENN KEINE TERMINE AM TAG EINGETRAGEN*/
    } else {
        $start_zeit = $VON;
        $end_zeit = $BIS;
        $min = getzeitdiff_min($VON, $BIS);
        $termin_min = $min / $TERMINE_TAG;
        for ($t = 0; $t < $TERMINE_TAG; $t++) {
            $bis_zeit = zeit_plus_min($start_zeit, $termin_min);
            $von_zeit = $start_zeit;
            $arr_new[$t]['VON'] = $start_zeit;
            $arr_new[$t]['BIS'] = $bis_zeit;
            $arr_new[$t]['TEXT'] = 'FREI';
            $arr_new[$t]['DAUER'] = getzeitdiff_min($start_zeit, $bis_zeit);
            $start_zeit = $bis_zeit;
        }
    }
    if (isset($arr_new)) {
        return $arr_new;
    }
}


function get_luecken_termine1($benutzer_id, $datum_d)
{
    $g = new general();
    $prof_arr = $g->get_wteam_profil($benutzer_id);
    extract($prof_arr);
    $start_time_arbeit_a = $VON;
    $end_time_arbeit_a = $BIS;
    $start_time_arbeit = str_replace(':', '', $VON);
    $end_time_arbeit = str_replace(':', '', $BIS);
    $arbeitszeit_min = getzeitdiff_min($VON, $BIS);
    $termine_am_tag = $TERMINE_TAG;
    $dauer_termin_min = $arbeitszeit_min / $termine_am_tag;
    unset($prof_arr);

    $arr = get_termine_tag_arr($benutzer_id, $datum_d);
    if (!empty($arr)) {
        $anz = count($arr);
        $z = 0;
        for ($a = 0; $a < $anz; $a++) {
            $VON = str_replace(':', '', substr($arr[$a]['VON'], 0, 5));
            $BIS = str_replace(':', '', substr($arr[$a]['BIS'], 0, 5));

            /*Nur erster Termin*/
            if ($a == 0) {
                /*Wenn erster Termin nach Arbeitszeitanfang*/
                if ($VON > $start_time_arbeit) {
                    $start_time = $start_time_arbeit_a;
                    $end_time = $arr[$a]['VON'];

                    if ($start_time != $end_time) {
                        $teil_dauer = getzeitdiff_min($start_time, $end_time);
                        if (session()->has('termin_dauer')) {
                            if ($teil_dauer >= session()->get('termin_dauer')) {
                                $arr_new[$z]['VON'] = $start_time;
                                $arr_new[$z]['BIS'] = $end_time;
                                $arr_new[$z]['TEXT'] = 'FREI';
                                $arr_new[$z]['DAUER'] = $teil_dauer;
                            }
                        } else {
                            $arr_new[$z]['VON'] = $start_time;
                            $arr_new[$z]['BIS'] = $end_time;
                            $arr_new[$z]['TEXT'] = 'FREI';
                            $arr_new[$z]['DAUER'] = $teil_dauer;
                        }
                        $z++;
                    }

                }

            }

            /*Wenn es Nachtermine gibt*/
            if (isset($arr[$a + 1]['VON'])) {
                $start_time = $arr[$a]['BIS'];
                $end_time = $arr[$a + 1]['VON'];

                if ($start_time != $end_time) {
                    $teil_dauer = getzeitdiff_min($start_time, $end_time);
                    if (session()->has('termin_dauer')) {
                        if ($teil_dauer >= session()->get('termin_dauer')) {
                            $arr_new[$z]['VON'] = $start_time;
                            $arr_new[$z]['BIS'] = $end_time;
                            $arr_new[$z]['TEXT'] = 'FREI';
                            $arr_new[$z]['DAUER'] = $teil_dauer;
                        }
                    } else {
                        $arr_new[$z]['VON'] = $start_time;
                        $arr_new[$z]['BIS'] = $end_time;
                        $arr_new[$z]['TEXT'] = 'FREI';
                        $arr_new[$z]['DAUER'] = $teil_dauer;
                    }
                    $z++;
                }

            }
            if ($a == $anz - 1) {
                //Keine Nachtermine d.h letzter Termin*/
                if ($BIS < $end_time_arbeit) {
                    $start_time = $arr[$a]['BIS'];
                    $end_time = $end_time_arbeit_a;
                    if ($start_time != $end_time) {

                        $teil_dauer = getzeitdiff_min($start_time, $end_time);
                        if (session()->has('termin_dauer')) {
                            if ($teil_dauer >= session()->get('termin_dauer')) {
                                $arr_new[$z]['VON'] = $start_time;
                                $arr_new[$z]['BIS'] = $end_time;
                                $arr_new[$z]['TEXT'] = 'FREI';
                                $arr_new[$z]['DAUER'] = $teil_dauer;
                            }
                        } else {
                            $arr_new[$z]['VON'] = $start_time;
                            $arr_new[$z]['BIS'] = $end_time;
                            $arr_new[$z]['TEXT'] = 'FREI';
                            $arr_new[$z]['DAUER'] = $teil_dauer;
                        }

                        $z++;
                    }
                }
            }
        }//end for


        /*WENN KEINE TERMINE AM TAG EINGETRAGEN*/
    } else {
        $start_zeit = $VON;
        $end_zeit = $BIS;
        $min = getzeitdiff_min($VON, $BIS);
        $termin_min = $min / $TERMINE_TAG;
        for ($t = 0; $t < $TERMINE_TAG; $t++) {
            $bis_zeit = zeit_plus_min($start_zeit, $termin_min);
            $von_zeit = $start_zeit;
            $teil_dauer = getzeitdiff_min($start_zeit, $bis_zeit);
            if (session()->has('termin_dauer')) {
                if ($teil_dauer >= session()->get('termin_dauer')) {
                    $arr_new[$t]['VON'] = $start_zeit;
                    $arr_new[$t]['BIS'] = $bis_zeit;
                    $arr_new[$t]['TEXT'] = 'FREI';
                    $arr_new[$t]['DAUER'] = $teil_dauer;
                }
            } else {
                $arr_new[$t]['VON'] = $start_zeit;
                $arr_new[$t]['BIS'] = $bis_zeit;
                $arr_new[$t]['TEXT'] = 'FREI';
                $arr_new[$t]['DAUER'] = $teil_dauer;
            }
            $start_zeit = $bis_zeit;
        }
    }
    if (isset($arr_new)) {
        return $arr_new;
    }
}


function zeit_plus_min($zeit, $plusmin)
{
    $zeit_arr = explode(':', $zeit);
    $std = $zeit_arr[0];
    $min = $zeit_arr[1];
    $time = mktime($std, $min, 0, 1, 12, 2000);
    return date('H:i', $time + $plusmin * 60);
}

function get_termine_tag_arr($benutzer_id, $datum_d)
{
    $datum = date_german2mysql($datum_d);
    $result = DB::select("SELECT GEO_TERMINE.DAT, GEO_TERMINE.DATUM, DATE_FORMAT(GEO_TERMINE.VON, '%H:%i') AS VON, DATE_FORMAT(GEO_TERMINE.BIS, '%H:%i') AS BIS, GEO_TERMINE.TEXT, GEO_TERMINE.HINWEIS, GEO_TERMINE.GERAETE_ID, KOSTENTRAEGER_TYP, KOSTENTRAEGER_ID, RECHNUNG_AN FROM GEO_TERMINE, W_GERAETE WHERE GEO_TERMINE.AKTUELL='1' && W_GERAETE.AKTUELL='1' && BENUTZER_ID='$benutzer_id' && DATUM='$datum' && GEO_TERMINE.GERAETE_ID=W_GERAETE.GERAETE_ID ORDER BY VON ASC");
    return $result;
}


function get_alle_termine_arr()
{
    $result = DB::select("SELECT GEO_TERMINE.DAT, GEO_TERMINE.DATUM, DATE_FORMAT(GEO_TERMINE.VON, '%H:%i') AS VON, DATE_FORMAT(GEO_TERMINE.BIS, '%H:%i') AS BIS, GEO_TERMINE.TEXT, GEO_TERMINE.HINWEIS, GEO_TERMINE.GERAETE_ID, KOSTENTRAEGER_TYP, KOSTENTRAEGER_ID, RECHNUNG_AN FROM GEO_TERMINE, W_GERAETE WHERE GEO_TERMINE.AKTUELL='1' && W_GERAETE.AKTUELL='1' && GEO_TERMINE.GERAETE_ID=W_GERAETE.GERAETE_ID GROUP BY W_GERAETE.GERAETE_ID");
    return $result;
}


function get_durchschnitt_km($benutzer_id, $datum)
{
    #echo "$benutzer_id $datum $lat_lon_db_ziel<br><br>";
    $db_abfrage = "SELECT GEO_TERMINE.GERAETE_ID, KOSTENTRAEGER_TYP, KOSTENTRAEGER_ID FROM GEO_TERMINE, W_GERAETE WHERE GEO_TERMINE.AKTUELL='1' && W_GERAETE.AKTUELL='1' && BENUTZER_ID='$benutzer_id' && DATUM='$datum' && GEO_TERMINE.GERAETE_ID=W_GERAETE.GERAETE_ID";
    $result = DB::select($db_abfrage);
    $numrows = count($result);

    /*Profil holen fär von zu Hause*/
    $gg = new general();
    $profil_arr = $gg->get_wteam_profil($benutzer_id);
    extract($profil_arr);

    $y1 = session()->get('ziel_lon');
    $y2 = session()->get('ziel_lat');
    unset($ziel_arr);


    if ($numrows) {
        $summe_km = 0;

        foreach($result as $row) {
            $g_id = $row['GERAETE_ID'];
            $kos_typ = $row['KOSTENTRAEGER_TYP'];
            $kos_id = $row['KOSTENTRAEGER_ID'];

            if (!check_is_eigentuemer($kos_id)) {
                $p_anschrift = get_partner_anschrift($kos_id);
                $g = new general();
                $g->get_partner_info($kos_id);
                $lat_lon_db_start = get_lat_lon_db_osm(umlaute_anpassen($g->partner_strasse), $g->partner_hausnr, $g->partner_plz, umlaute_anpassen($g->partner_ort));
            } else {
                /*BEI MIETER ERSETZEN*/
                $g_info_arr = geraete_info_arr($g_id);
                $einheit_bez = $g_info_arr[0]['LAGE_RAUM'];
                $gr = new general();
                $gr->get_lanlon_mieter($einheit_bez);
                $lat_lon_db_start = $gr->mieter_lat_lon_db_ziel;
            }
            $start_arr = explode(',', $lat_lon_db_start);
            $x1 = ltrim(rtrim($start_arr[0]));
            $x2 = ltrim(rtrim($start_arr[1]));
            unset($start_arr);

            $summe_km += get_math_entfernung($x1, $x2, $y1, $y2);
        }
        $nenn = $numrows;
        return $summe_km / $nenn;
    } else {
        if (!$START_ADRESSE) {
            $lat_lon_db_start = get_lat_lon_db(START_STRASSE, START_NR, START_PLZ, START_ORT);
            $start_arr = explode(',', $lat_lon_db_start);
            $x1 = ltrim(rtrim($start_arr[0]));
            $x2 = ltrim(rtrim($start_arr[1]));
            unset($start_arr);
        } else {
            $START_ADRESSE_arr = explode(',', $START_ADRESSE);
            $START_STRASSE = $START_ADRESSE_arr[0];
            $START_NR = $START_ADRESSE_arr[1];
            $START_PLZ = $START_ADRESSE_arr[2];
            $START_ORT = $START_ADRESSE_arr[3];
            $lat_lon_db_start = get_lat_lon_db_osm($START_STRASSE, $START_NR, $START_PLZ, $START_ORT);
            $start_arr = explode(',', $lat_lon_db_start);
            $x1 = ltrim(rtrim($start_arr[0]));
            $x2 = ltrim(rtrim($start_arr[1]));
            unset($start_arr);
        }
        return get_math_entfernung($x1, $x2, $y1, $y2);
    }
}


function get_durchschnitt_km3($benutzer_id, $datum)
{
    $db_abfrage = "SELECT GEO_TERMINE.GERAETE_ID, KOSTENTRAEGER_TYP, KOSTENTRAEGER_ID FROM GEO_TERMINE, W_GERAETE WHERE GEO_TERMINE.AKTUELL='1' && W_GERAETE.AKTUELL='1' && BENUTZER_ID='$benutzer_id' && DATUM='$datum' && GEO_TERMINE.GERAETE_ID=W_GERAETE.GERAETE_ID";
    $result = DB::select($db_abfrage);
    $numrows = count($result);

    /*Profil holen fär von zu Hause*/
    $gg = new general();
    $profil_arr = $gg->get_wteam_profil($benutzer_id);
    extract($profil_arr);

    $y1 = session()->get('ziel_lon');
    $y2 = session()->get('ziel_lat');
    unset($ziel_arr);


    if ($numrows) {
        $summe_km = 100000;

        foreach($result as $row) {
            $g_id = $row['GERAETE_ID'];
            $kos_typ = $row['KOSTENTRAEGER_TYP'];
            $kos_id = $row['KOSTENTRAEGER_ID'];

            if (!check_is_eigentuemer($kos_id)) {
                $p_anschrift = get_partner_anschrift($kos_id);
                $g = new general();
                $g->get_partner_info($kos_id);
                $lat_lon_db_start = get_lat_lon_db_osm($g->partner_strasse, $g->partner_hausnr, $g->partner_plz, $g->partner_ort);
            } else {
                /*BEI MIETER ERSETZEN*/

                $g_info_arr = geraete_info_arr($g_id);
                $einheit_bez = $g_info_arr[0]['LAGE_RAUM'];
                $gr = new general();
                $gr->get_lanlon_mieter($einheit_bez);
                $lat_lon_db_start = $gr->mieter_lat_lon_db_ziel;
            }
            $start_arr = explode(',', $lat_lon_db_start);
            $x1 = ltrim(rtrim($start_arr[0]));
            $x2 = ltrim(rtrim($start_arr[1]));
            unset($start_arr);

            $luftlinie = get_math_entfernung($x1, $x2, $y1, $y2);
            if ($summe_km > $luftlinie) {
                $summe_km = $luftlinie;
            }
        }
        $nenn = $numrows;
        return $summe_km;
    } else {
        if (!$START_ADRESSE) {
            $lat_lon_db_start = get_lat_lon_db(START_STRASSE, START_NR, START_PLZ, START_ORT);
            $start_arr = explode(',', $lat_lon_db_start);
            $x1 = ltrim(rtrim($start_arr[0]));
            $x2 = ltrim(rtrim($start_arr[1]));
            unset($start_arr);
        } else {
            $START_ADRESSE_arr = explode(',', $START_ADRESSE);
            $START_STRASSE = $START_ADRESSE_arr[0];
            $START_NR = $START_ADRESSE_arr[1];
            $START_PLZ = $START_ADRESSE_arr[2];
            $START_ORT = $START_ADRESSE_arr[3];
            $lat_lon_db_start = get_lat_lon_db_osm($START_STRASSE, $START_NR, $START_PLZ, $START_ORT);
            $start_arr = explode(',', $lat_lon_db_start);
            $x1 = ltrim(rtrim($start_arr[0]));
            $x2 = ltrim(rtrim($start_arr[1]));
            unset($start_arr);
        }
        return get_math_entfernung($x1, $x2, $y1, $y2);
    }
}


function get_durchschnitt_km2($benutzer_id, $datum, $lat_lon_db_ziel)
{
    $db_abfrage = "SELECT GEO_TERMINE.GERAETE_ID, KOSTENTRAEGER_TYP, KOSTENTRAEGER_ID FROM GEO_TERMINE, W_GERAETE WHERE GEO_TERMINE.AKTUELL='1' && W_GERAETE.AKTUELL='1' && BENUTZER_ID='$benutzer_id' && DATUM='$datum' && GEO_TERMINE.GERAETE_ID=W_GERAETE.GERAETE_ID";
    $result = DB::select($db_abfrage);
    $numrows = count($result);

    /*Profil holen fär von zu Hause*/
    $gg = new general();
    $profil_arr = $gg->get_wteam_profil($benutzer_id);
    extract($profil_arr);

    $ziel_arr = explode(',', $lat_lon_db_ziel);
    $y1 = ltrim(rtrim($ziel_arr[0]));
    $y2 = ltrim(rtrim($ziel_arr[1]));
    if (!$y1) {
        die($lat_lon_db_ziel);
    }
    unset($ziel_arr);


    if ($numrows) {
        $summe_km = 0;

        foreach($result as $row) {
            $g_id = $row['GERAETE_ID'];
            $kos_typ = $row['KOSTENTRAEGER_TYP'];
            $kos_id = $row['KOSTENTRAEGER_ID'];

            if (!check_is_eigentuemer($kos_id)) {
                $p_anschrift = get_partner_anschrift($kos_id);
                $g = new general();
                $g->get_partner_info($kos_id);
                $lat_lon_db_start = get_lat_lon_db_osm($g->partner_strasse, $g->partner_hausnr, $g->partner_plz, $g->partner_ort);
            }
            $start_arr = explode(',', $lat_lon_db_start);
            $x1 = ltrim(rtrim($start_arr[0]));
            $x2 = ltrim(rtrim($start_arr[1]));
            unset($start_arr);

            $summe_km += get_math_entfernung($x1, $x2, $y1, $y2);
        }

        /*Von zu Hause*/
        $START_ADRESSE_arr = explode(',', $START_ADRESSE);
        $START_STRASSE = $START_ADRESSE_arr[0];
        $START_NR = $START_ADRESSE_arr[1];
        $START_PLZ = $START_ADRESSE_arr[2];
        $START_ORT = $START_ADRESSE_arr[3];

        $lat_lon_db_start = get_lat_lon_db_osm($START_STRASSE, $START_NR, $START_PLZ, $START_ORT);
        $start_arr = explode(',', $lat_lon_db_start);
        $x1 = ltrim(rtrim($start_arr[0]));
        $x2 = ltrim(rtrim($start_arr[1]));
        $summe_km += get_math_entfernung($x1, $x2, $y1, $y2);

        /*Von BASIS ANSCHRIFT*/
        $lat_lon_db_start = get_lat_lon_db_osm(START_STRASSE, START_NR, START_PLZ, START_ORT);
        $start_arr = explode(',', $lat_lon_db_start);
        $x1 = ltrim(rtrim($start_arr[0]));
        $x2 = ltrim(rtrim($start_arr[1]));
        $summe_km += get_math_entfernung($x1, $x2, $y1, $y2);
        $nenn = $numrows + 2;
        return $summe_km / $nenn;
    } else {
        if (!$START_ADRESSE) {
            $lat_lon_db_start = get_lat_lon_db(START_STRASSE, START_NR, START_PLZ, START_ORT);
            $start_arr = explode(',', $lat_lon_db_start);
            $x1 = ltrim(rtrim($start_arr[0]));
            $x2 = ltrim(rtrim($start_arr[1]));
            unset($start_arr);
        } else {
            $START_ADRESSE_arr = explode(',', $START_ADRESSE);
            $START_STRASSE = $START_ADRESSE_arr[0];
            $START_NR = $START_ADRESSE_arr[1];
            $START_PLZ = $START_ADRESSE_arr[2];
            $START_ORT = $START_ADRESSE_arr[3];
            $lat_lon_db_start = get_lat_lon_db_osm($START_STRASSE, $START_NR, $START_PLZ, $START_ORT);
            $start_arr = explode(',', $lat_lon_db_start);
            $x1 = ltrim(rtrim($start_arr[0]));
            $x2 = ltrim(rtrim($start_arr[1]));
            unset($start_arr);
        }
        return get_math_entfernung($x1, $x2, $y1, $y2);
    }
}


function get_math_entfernung($lat1, $lon1, $lat2, $lon2)
{
    $A = $lat1 / 57.29577951;
    $B = $lon1 / 57.29577951;
    $C = $lat2 / 57.29577951;
    $D = $lon2 / 57.29577951;

    if ($A == $C && $B == $D) {
        $dist = 0;
    } else if ((sin($A) * sin($C) + cos($A) * cos($C) * cos($B - $D)) > 1) {
        $dist = 3963.1 * acos(1);// solved a prob I ran into.  I haven't fullyanalyzed it yet
    } else {
        $dist = 3963.1 * acos(sin($A) * sin($C) + cos($A) * cos($C) * cos($B - $D));
    }

    return (round($dist * 1.609, 2));
}

function datums_array_erstellen($datum_heute_d, $tage = 31)
{
    if (empty($datum_heute_d)) {
        $datum_heute_d = date("d.m.Y");
    }
    for ($b = 0; $b < $tage; $b++) {
        if (!isset($datum_temp)) {
            $datum_temp = $datum_heute_d;
        }
        $datum_temp = tage_plus_wp($datum_temp, 1);
        $datum_arr[] = $datum_temp;
    }
    return $datum_arr;
}

/*Gibt freie Termine aus dem Zeitraum $datum_arr vom Benutzer aus*/
function get_freie_termine_benutzer($benutzer_id, $profil_arr, $datum_arr)
{
    $termine_tag = $profil_arr['TERMINE_TAG'];
    $_1 = $profil_arr['1'];
    $_2 = $profil_arr['2'];
    $_3 = $profil_arr['3'];
    $_4 = $profil_arr['4'];
    $_5 = $profil_arr['5'];
    $_6 = $profil_arr['6'];
    $_7 = $profil_arr['7'];

    $anz_t = count($datum_arr);
    $f_zaehler = 0;
    for ($a = 0; $a < $anz_t; $a++) {
        $datum_d = $datum_arr[$a];
        /*Präfen ob der Mitarbeiter an dem Wochentag arbeiten kann*/
        $w_tag = get_wochentag($datum_d);
        $w_tag_name = get_wochentag_name($datum_d);
        /*Wenn Mitarbeiter an dem Wochentag arbeiten kann*/
        if ($profil_arr[$w_tag] == 1) {
            /*Präfen ob im Urlaub und wenn nicht, dann ob noch ein Termin frei ist*/
            if (is_termin_frei($benutzer_id, $datum_d, $termine_tag)) {
                #echo "$datum_d $benutzer_id FREI<br>";
                $freie_tage[$f_zaehler]['TERMINE_TAG'] = $termine_tag;
                $freie_tage[$f_zaehler]['BENUTZER_ID'] = $benutzer_id;
                $freie_tage[$f_zaehler]['DATUM'] = $datum_d;
                $f_zaehler++;
            }
        }
    }//end for

    if (isset($freie_tage)) {
        return $freie_tage;
    }

}


function get_wochentag($datum)
{
    $datum_arr = explode('.', $datum);
    $d = $datum_arr[0];
    $m = $datum_arr[1];
    $j = $datum_arr[2];
    $tstamp = mktime(0, 0, 0, $m, $d, $j);
    $tdatum = getdate($tstamp);
    $wday = $tdatum["wday"];
    if ($wday == 0) {
        $wday = 7;
    }
    return $wday;
}

function get_wochentag_name($datum)
{
    $dat_arr = explode('.', $datum);
    $j = $dat_arr[2];
    $m = $dat_arr[1];
    $d = $dat_arr[0];
    $timestamp = mktime(0, 0, 0, $m, $d, $j);
    $tage = array("Sonntag", "Montag", "Dienstag", "Mittwoch", "Donnerstag", "Freitag", "Samstag");
    $tag = $tage[date("w", $timestamp)];
    return $tag;
}


function get_anzahl_termine($benutzer_id, $datum_d)
{
    $datum = date_german2mysql($datum_d);
    $db_abfrage = "SELECT COUNT(*) AS ANZAHL FROM GEO_TERMINE WHERE AKTUELL='1' && BENUTZER_ID='$benutzer_id' && DATUM='$datum'";
    $result = DB::select($db_abfrage);
    return $result[0]['ANZAHL'];
}

function get_datum_lw($g_id)
{
    $datum_heute = date("Y-m-d");
    $db_abfrage = "SELECT DATE_FORMAT(DATUM, '%d.%m.%Y') AS DATUM, BENUTZER_ID FROM GEO_TERMINE WHERE AKTUELL='1' && GERAETE_ID='$g_id' && DATUM<='$datum_heute' ORDER BY DATUM DESC";
    $result = DB::select($db_abfrage);
    if (!empty($result)) {
        $link = '';
        foreach($result as $row) {
            $datum = $row['DATUM'];
            $b_id = $row['BENUTZER_ID'];
            $b_name = get_benutzername($b_id);
            $link .= "<input type=\"button\" onclick=\"daj3('" . route('web::wartungsplaner::ajax', ['option' => 'termine_tag_tab', 'b_id' => $b_id, 'datum' => $datum], false) . "','rightBox1');daj3('" . route('web::wartungsplaner::ajax', ['option' => 'karte', 'b_id' => $b_id, 'datum_d' => $datum], false) . "','rightBox');\" value=\"Wartung: $datum\">$b_name<br>";
        }
        return $link;
    }
}

/*Datum nächste Wartung*/
function get_datum_nw($g_id)
{
    $datum_heute = date("Y-m-d");
    $db_abfrage = "SELECT DATE_FORMAT(DATUM, '%d.%m.%Y') AS DATUM, BENUTZER_ID, VON, BIS FROM GEO_TERMINE WHERE AKTUELL='1' && GERAETE_ID='$g_id' && DATUM>'$datum_heute' ORDER BY DATUM ASC";
    $result = DB::select($db_abfrage);
    if (!empty($result)) {
        $link = '';
        foreach($result as $row) {
            $datum = $row['DATUM'];
            $b_id = $row['BENUTZER_ID'];
            $von = substr($row['VON'], 0, 5);
            $bis = substr($row['BIS'], 0, 5);
            $b_name = get_benutzername($b_id);
            $link .= "<input type=\"button\" onclick=\"daj3('" . route('web::wartungsplaner::ajax', ['option' => 'termine_tag_tab', 'b_id' => $b_id, 'datum' => $datum], false) . "','rightBox1');daj3('" . route('web::wartungsplaner::ajax', ['option' => 'karte', 'b_id' => $b_id, 'datum_d' => $datum], false) . "','rightBox');\" value=\"Nächste Wartung: $datum\">$b_name $von - $bis<br>";
        }
        return $link;
    }
}


function is_termin_frei($benutzer_id, $datum_d, $termine_tag)
{
    $datum = date_german2mysql($datum_d);
    if (check_anwesenheit($benutzer_id, $datum)) {

        $db_abfrage = "SELECT * FROM GEO_TERMINE WHERE AKTUELL='1' && BENUTZER_ID='$benutzer_id' && DATUM='$datum' ";
        $result = DB::select($db_abfrage);
        $numrows = count($result);

        if ($numrows < $termine_tag) {
            return true;
        } else {
            return false;
        }
    } else {
        return false;
    }
}

/*URLAUB*/
function check_anwesenheit($benutzer_id, $datum)
{
    $result = DB::select("SELECT * FROM URLAUB WHERE AKTUELL='1' && BENUTZER_ID='$benutzer_id' && DATUM='$datum' ");
    return empty($result);
}

function get_kw($datum)
{
    $dat_arr = explode('.', $datum);
    $j = $dat_arr[2];
    $m = $dat_arr[1];
    $d = $dat_arr[0];
    $timestamp = mktime(0, 0, 0, $m, $d, $j);
    $kw = date("W", $timestamp);
    return $kw;
}


function check_is_eigentuemer($partner_id)
{
    $result = DB::select("SELECT * FROM OBJEKT WHERE EIGENTUEMER_PARTNER='$partner_id' && OBJEKT_AKTUELL='1'");
    return !empty($result);
}


function get_lon_lat_osm($str, $nr, $plz, $ort, $w_datum)
{
    if (empty($w_datum)) {
        $w_datum = date("d.m.Y");
    }
    session()->put('w_datum', $w_datum);

    $lat_lon = get_lat_lon_db($str, $nr, $plz, $ort);
    if (!empty($lat_lon)) {
        return $lat_lon;
    }

    $url = "http://maps.google.com/maps/api/directions/xml?origin=" . "$str $nr, $plz $ort " . " &destination=" . START_ADRESSE . "&sensor=false";
    $xml = simplexml_load_file("$url");
    sleep(2);
    if ($xml === FALSE) {
        die();
    } else {
        $status = $xml->status;
        if ($status == 'OK') {
            $lat = $xml->route->leg->step->start_location->lat;
            $lon = $xml->route->leg->step->start_location->lng;
            echo "$lat, $lon, GoogleMaps";
            $quelle = 'GoogleMaps';
        }
    }
    if (empty($lat) && empty($lon)) {

        $url = urldecode("http://nominatim.openstreetmap.org/search?q=$str&nbsp;$nr&nbsp;$plz&nbsp;$ort&format=xml");

        $xml = simplexml_load_file($url);
        $vars = get_object_vars($xml->place);
        $lat = $vars['@attributes']['lat'];
        $lon = $vars['@attributes']['lon'];
        if (!empty($lat) && !empty($lon)) {
            echo "$lat, $lon, openstreetmap";
            $quelle = 'Openstreetmap';
        }
    }

    if (!empty($lat) && !empty($lon)) {
        if (!check_str($str, $nr, $plz, $ort)) {
            DB::insert("INSERT INTO GEO_LON_LAT VALUES (NULL, '$str', '$nr', '$plz', '$ort','$lon','$lat','$quelle','1')");
        }
    }

}


function check_str($str, $nr, $plz, $ort)
{
    $db_abfrage = "SELECT * FROM GEO_LON_LAT WHERE STR='$str' && NR='$nr' && PLZ='$plz' && ORT='$ort'";
    $result = DB::select($db_abfrage);
    return !empty($result);
}

function get_benutzername($benutzer_id)
{
    $user = \App\Models\User::find($benutzer_id);
    return isset($user) ? $user->name : '';
}

function dropdown_zeiten($label, $name, $id, $von, $js, $class_r = 'reihe', $class_f = 'feld')
{
    $std = 0;
    $min = '00';
    echo "<div class=\"$class_r\">";
    echo "<span class=\"label\">$label</span>";
    echo "<span class=\"$class_f\">";
    echo "<select name=\"$name\" id=\"$id\" size=\"1\" $js>";
    $max = (24 * 60) / 5;
    for ($a = 0; $a < $max; $a++) {
        $zeit = "$std:$min";
        if ($von == $zeit) {
            echo "<option value=\"$zeit\" selected><b>$zeit</b></option>";
            $treffer = '1';
        } else {
            echo "<option value=\"$zeit\">$zeit</option>";
        }

        $min += 5;
        if ($min == 60) {
            $std += 1;
            $min = '00';
        }
    }
    if (!isset($treffer)) {
        $von_arr = explode(':', $von);
        $von_std = $von_arr[0];
        $von_min = $von_arr[1];

        $x = sprintf('%02d', round($von_min / 15) * 15, 0);
        $neue_zeit_viertel = "$von_std:$x";
        echo "<option value=\"$neue_zeit_viertel\" selected><b>$neue_zeit_viertel</b></option>";
    }
    echo "</select>";
    echo "</select>\n";
    echo "</span>";
    echo "</div>";
}


function wochenkalender($b_id, $kw)
{
    $benutzername = get_benutzername($b_id);
    echo "<table width=\"100%\">";
    $jahr = date("Y");
    $vor_kw = $kw - 1;
    $nach_kw = $kw + 1;


    $link_vor_kw = "<a href=\"?option=wochenkalender&kw=$vor_kw&b_id=$b_id\">$vor_kw. KW</a>";
    $link_nach_kw = "<a href=\"?option=wochenkalender&kw=$nach_kw&b_id=$b_id\">$nach_kw. KW</a>";
    $link_termin_vorschlaege = "<a href=\"?option=termin_vorschlaege&kw=$kw&b_id=$b_id\">Terminvorschläge</a>";
    echo "<tr><th>$benutzername</th><th>$link_vor_kw</th><th>$kw. KW</th><th>$link_nach_kw</th><th></th></tr>\n";
    echo "<tr>";
    $datum_d = datum_montag_kw($kw);
    for ($a = 1; $a <= 5; $a++) {
        echo "<td valign=\"top\"  width=\"14%\" height=\"100%\">";
        tages_ansicht_neu($b_id, $datum_d, 0);
        echo "</td>\n";
        $datum_d = tage_plus_wp($datum_d, 1);
    }
    echo "</tr>\n</table>";
}


function tageskalender($b_id, $datum)
{
    $benutzername = get_benutzername($b_id);
    echo "<table width=\"100%\">";
    $wt = get_wochentag_name($datum);
    echo "<tr><th>$benutzername $wt, $datum</th></tr>\n";
    echo "<tr>";
    echo "<td valign=\"top\" width=\"14%\" height=\"100%\">";
    tages_termine($b_id, $datum);
    echo "</td>\n";
    echo "</tr>\n</table>";
}

function tages_termine($benutzer_id, $datum_d)
{
    $wochentag = get_wochentag_name($datum_d);
    $kw = get_kw($datum_d);
    $arr = get_termine_tag_arr($benutzer_id, $datum_d);

    $luecken_termine = get_luecken_termine($benutzer_id, $datum_d);
    if (is_array($luecken_termine) && !empty($arr)) {
        $my_arr = array_sortByIndex(array_merge($arr, $luecken_termine), 'VON');
    }
    if (is_array($luecken_termine) && empty($arr)) {
        $my_arr = $luecken_termine;
    }
    if (!is_array($luecken_termine) && !empty($arr)) {
        $my_arr = $arr;
    }
    $arr = $my_arr;
    unset($my_arr);

    session()->put('mitarbeiter_id', $benutzer_id);
    session()->put('kw', $kw);
    if (is_array($arr)) {

        $anz = count($arr);
        echo "<table height=\"100%\">";
        $link_wochen_kal = "<a href=\"" . route('web::wartungsplaner::ajax', ['option' => 'wochenkalender', 'b_id' => $benutzer_id, 'kw' => $kw], false) . "\">KW: $kw</a>";
        echo "<tr height=\"20px\"><th colspan=\"2\">$link_wochen_kal</th></tr>";
        echo "<tr height=\"20px\"><th colspan=\"2\">$kw KW, $datum_d</th></tr>";
        echo "<tr height=\"20px\"><th>VON:BIS</th><th>TERMIN</th></tr>";
        $datum_sql = date_german2mysql($datum_d);
        if (!check_anwesenheit($benutzer_id, $datum_sql)) {
            echo "<tr class=\"zeile_belegt\"><td valign=\"top\" colspan=\"2\">URLAUB</td></tr>";
        } else {
            $gg = new general();
            $profil_arr = $gg->get_wteam_profil($benutzer_id);
            extract($profil_arr);


            for ($a = 0; $a < $anz; $a++) {
                extract($arr[$a]);
                /*TERMIN VORHANDEN*/
                /*erster Termin*/
                if (isset($KOSTENTRAEGER_TYP)) {
                    #	echo "1. TERMIN VORHANDEN START VOM KUNDEN";
                    if (!check_is_eigentuemer($KOSTENTRAEGER_ID)) {
                        $g = new general();
                        $g->get_partner_info($KOSTENTRAEGER_ID);
                        $geraete_info_arr = geraete_info_arr($GERAETE_ID);
                        extract($geraete_info_arr['0']);
                        $freie_zeit_min = getzeitdiff_min($VON, $BIS);
                        $zeit = min_in_zeit($freie_zeit_min);
                        $wohnlage_det = finde_detail_inhalt('PARTNER_LIEFERANT', $KOSTENTRAEGER_ID, 'Wohnlage');
                        echo "<tr class=\"zeile_belegt\"><td valign=\"top\">$VON<BR>$BIS</b></td><td valign=\"top\"><b>Kunde:</b>$g->partner_name<br>$g->partner_strasse $g->partner_hausnr<br>$g->partner_plz $g->partner_ort<br><b>Wohnlage:</b>$wohnlage_det</b><br><b>Text:</b>$TEXT<br><b>Hinweis:</b>$HINWEIS<br><b>Hersteller:</b>$HERSTELLER<br><b>Bezeichnung:</b>$BEZEICHNUNG<br><b>Lage:</b>$LAGE_RAUM";
                        alle_details_anzeigen('PARTNER_LIEFERANT', $KOSTENTRAEGER_ID);
                        echo "</td></tr>";
                        echo "<tr class=\"zeile3\"><td valign=\"top\" colspan=\"2\">Dauer $zeit</td></tr>";
                        UNSET($KOSTENTRAEGER_TYP);

                    }
                } else {
                    $freie_zeit_min = getzeitdiff_min($VON, $BIS);
                    $zeit = min_in_zeit($freie_zeit_min);
                    echo "<tr class=\"zeile_frei\"><td valign=\"top\">$VON<BR>$BIS</b></td><td valign=\"top\">FREI</td></tr>";
                    echo "<tr class=\"zeile_detail\"><td valign=\"top\" colspan=\"2\">Dauer $zeit</td></tr>";
                }
            }
        }//end for $a
        echo "</table>";
    } else {
        echo "<p class=\"zeile_detail\">KEINE TERMINE</p>";
    }

}


function datum_montag_kw($kw)
{
    $jahr = date("Y");
    return date('d.m.Y', strtotime('Monday', kalenderwoche($kw, $jahr)));
}

function datum_montag_kw_jahr($kw, $jahr)
{
    return date('d.m.Y', strtotime('Monday', kalenderwoche($kw, $jahr)));
}

function kalenderwoche($kw, $year)
{
    $time = strtotime("4 January " . $year);
    if (date('w', $time) != 1)
        $time = strtotime("last Monday", $time);
    $time = strtotime("+" . ($kw - 1) . " weeks", $time);

    return $time;
}

function umlaute_anpassen($str)
{
    $str = str_replace('ä', 'ae', $str);
    $str = str_replace('ö', 'oe', $str);
    $str = str_replace('ü', 'ue', $str);
    $str = str_replace('Ä', 'Ae', $str);
    $str = str_replace('Ö', 'Oe', $str);
    $str = str_replace('Ü', 'Ue', $str);
    $str = str_replace('ß', 'ss', $str);

    return $str;
}

function umlaute_anpassen_2($str)
{
    return $str;
}

/*Alle von heute an gesehen, nicht innerhalb des vorgesehenen Intervalls erledigten, die auch in der Zukunft keinen Termin haben*/
function alle_noch_zu_machen_arr($gruppe_id = '1')
{
    session()->put('gruppe_id', $gruppe_id);
    /*Hier ok + 4 Monate*/
    $result = DB::select("SELECT `GERAETE_ID`, LAGE_RAUM AS EINBAUORT, HERSTELLER, BEZEICHNUNG, KOSTENTRAEGER_TYP, KOSTENTRAEGER_ID, `INTERVAL_M`, DATE_FORMAT(NOW(),'%Y-%m-%d') as HEUTE, DATE_FORMAT(DATE_ADD(DATE_FORMAT(NOW(),'%Y-%m-%d'), INTERVAL -INTERVAL_M MONTH),'%Y-%m-%d') AS L_WART_FAELLIG, (SELECT DATUM FROM GEO_TERMINE WHERE GERAETE_ID=W_GERAETE.GERAETE_ID && AKTUELL='1' ORDER BY DATUM DESC LIMIT 0,1) AS L_WART FROM `W_GERAETE` WHERE `AKTUELL`='1' && GRUPPE_ID='$gruppe_id' && 
GERAETE_ID NOT IN
(SELECT GERAETE_ID FROM GEO_TERMINE WHERE AKTUELL='1' && (DATUM>=DATE_FORMAT(DATE_ADD(DATE_FORMAT(NOW(),'%Y-%m-%d'), INTERVAL -(INTERVAL_M-4) MONTH),'%Y-%m-%d') AND DATUM <= DATE_FORMAT(NOW(),'%Y-%m-%d')) )
AND
GERAETE_ID NOT IN
(SELECT GERAETE_ID FROM GEO_TERMINE WHERE AKTUELL='1' && DATUM>DATE_FORMAT(NOW(),'%Y-%m-%d') GROUP BY GERAETE_ID) ORDER BY `L_WART_FAELLIG` ASC, INTERVAL_M ASC, KOSTENTRAEGER_TYP ASC, KOSTENTRAEGER_ID ASC
");
    return $result;
}

function alle_noch_zu_machen_arr_chrono($gruppe_id = '1')
{
    session()->put('gruppe_id', $gruppe_id);
    /*Hier ok + 4 Monate*/
    $result = DB::select("SELECT `GERAETE_ID`, LAGE_RAUM AS EINBAUORT, HERSTELLER, BEZEICHNUNG, KOSTENTRAEGER_TYP, KOSTENTRAEGER_ID, `INTERVAL_M`, DATE_FORMAT(NOW(),'%Y-%m-%d') as HEUTE, DATE_FORMAT(DATE_ADD(DATE_FORMAT(NOW(),'%Y-%m-%d'), INTERVAL -INTERVAL_M MONTH),'%Y-%m-%d') AS L_WART_FAELLIG, (SELECT DATUM FROM GEO_TERMINE WHERE GERAETE_ID=W_GERAETE.GERAETE_ID && AKTUELL='1' ORDER BY DATUM DESC LIMIT 0,1) AS L_WART FROM `W_GERAETE` WHERE `AKTUELL`='1' && GRUPPE_ID='$gruppe_id' &&
			GERAETE_ID NOT IN
			(SELECT GERAETE_ID FROM GEO_TERMINE WHERE AKTUELL='1' && (DATUM>=DATE_FORMAT(DATE_ADD(DATE_FORMAT(NOW(),'%Y-%m-%d'), INTERVAL -(INTERVAL_M) MONTH),'%Y-%m-%d') AND DATUM <= DATE_FORMAT(NOW(),'%Y-%m-%d')) )
			AND
			GERAETE_ID NOT IN
			(SELECT GERAETE_ID FROM GEO_TERMINE WHERE AKTUELL='1' && DATUM>DATE_FORMAT(NOW(),'%Y-%m-%d') GROUP BY GERAETE_ID) ORDER BY `L_WART_FAELLIG` ASC, INTERVAL_M ASC, KOSTENTRAEGER_TYP ASC, KOSTENTRAEGER_ID ASC
			");
    return $result;
}


function vorschlag($gruppe_id = '1', $gemacht = 'NOT')
{
    session()->put('gruppe_id', $gruppe_id);
    $arr = alle_noch_zu_machen_arr($gruppe_id);
    if (!empty($arr)) {
        $anz = count($arr);
        $datum_sql = date("Y-m-d");
        $termine_tag = 5;
        $tt = 1;
        for ($a = 0; $a < $anz; $a++) {
            $kos_typ = $arr[$a]['KOSTENTRAEGER_TYP'];
            $kos_id = $arr[$a]['KOSTENTRAEGER_ID'];
            if ($kos_typ == 'Partner') {
                $g = new general();
                $g->get_partner_info($kos_id);
                $arr_new[$a] = $arr[$a];
                $arr_new[$a]['STRASSE'] = $g->partner_strasse;
                $arr_new[$a]['NR'] = $g->partner_hausnr;
                $arr_new[$a]['PLZ'] = $g->partner_plz;

                $g_id = $arr[$a]['GERAETE_ID'];
                $datum_df = date_mysql2german($datum_sql);
                $best_r = besten_termin_suchen($g_id, $datum_df);
                $arr_new[$a]['KM'] = $best_r['D_KM'];
                $arr_new[$a]['DATUM'] = $best_r['DATUM'];
                $arr_new[$a]['DATUMZ'] = $best_r['DATUMZ'];
                $arr_new[$a]['MITARBEITER_ID'] = $best_r['BENUTZER_ID'];
                if (isset($best_r['EINHEIT_NAME'])) {
                    $arr_new[$a]['EINHEIT_NAME'] = $best_r['EINHEIT_NAME'];
                    $arr_new[$a]['EINHEIT_ID'] = $best_r['EINHEIT_ID'];
                    $arr_new[$a]['STRASSE'] = $best_r['EINHEIT_STR'];
                    $arr_new[$a]['NR'] = $best_r['EINHEIT_NR'];
                    $arr_new[$a]['PLZ'] = $best_r['EINHEIT_PLZ'];

                }
                if (isset($best_r['MV_ID'])) {
                    $arr_new[$a]['MV_ID'] = $best_r['MV_ID'];
                }
                if (isset($best_r['MIETERNAME'])) {
                    $arr_new[$a]['MIETERNAME'] = $best_r['MIETERNAME'];
                }
            }
        }
        $srt = new arr_multisort();
        $srt->setArray($arr_new);

        $srt->addColumn("L_WART_FAELLIG", SRT_ASC);
        $srt->addColumn("KM", SRT_ASC);
        $srt->addColumn("MITARBEITER_ID", SRT_ASC);
        $srt->addColumn("DATUMZ", SRT_ASC);
        $arr_sort = $srt->sort();
        $anz = count($arr_sort);
        echo "<table class=\"sortable\">";
        echo "<tr><th class=\"sorttable_numeric\">Z</th><th>Mitarbeiter</th><th class=\"sorttable_numeric\">V. Datum</th><th class=\"sorttable_numeric\">Entfernung</th><th>Kunde</th><th>Hersteller</th><th>Bezeichnung</th><th>Einbauort</th><th class=\"sorttable_numeric\">SOLLDATUM</th><th class=\"sorttable_numeric\">L. Termin</th><th class=\"sorttable_numeric\">Intervall</th></tr>";
        $z = 0;
        $ze = 0;
        for ($a = 0; $a < $anz; $a++) {
            $z++;
            $ze++;
            $g_id = $arr_sort[$a]['GERAETE_ID'];
            $kos_typ = $arr_sort[$a]['KOSTENTRAEGER_TYP'];
            $kos_id = $arr_sort[$a]['KOSTENTRAEGER_ID'];
            $l_faellig = date_mysql2german($arr_sort[$a]['L_WART_FAELLIG']);
            $str = $arr_sort[$a]['STRASSE'];
            $nr = $arr_sort[$a]['NR'];
            $plz = $arr_sort[$a]['PLZ'];
            $km = $arr_sort[$a]['KM'];
            $datum = $arr_sort[$a]['DATUM'];
            $kos_bez = substr(get_partner_name($kos_id), 0, 46);
            $mitarbeiter_id = $arr_sort[$a]['MITARBEITER_ID'];
            $mitarbeiter_name = get_benutzername($mitarbeiter_id);
            $intervall = $arr_sort[$a]['INTERVAL_M'];
            $datumz = $arr_sort[$a]['DATUMZ'];
            $einbauort = $arr_sort[$a]['EINBAUORT'];
            $hersteller = $arr_sort[$a]['HERSTELLER'];
            $g_bez = $arr_sort[$a]['BEZEICHNUNG'];

            $js_t = "onclick=\"setTimeout('daj3(\'/wartungsplaner/ajax?option=kos_typ_register&kos_typ=$kos_typ&kos_id=$kos_id\', \'TERMIN_BOX\')', 100);";
            $js_t = $js_t . "setTimeout('daj3(\'/wartungsplaner/ajax?option=termine_tag_tab&b_id=$mitarbeiter_id&datum=$datum&g_id=$g_id\', \'TERMIN_BOX\')', 1000);";
            $js_tages_ansicht = $js_t . "\"";


            if (isset($arr_sort[$a]['L_WART'])) {
                $l_wartung = date_mysql2german($arr_sort[$a]['L_WART']);
            } else {
                $l_wartung = '<b>nicht bekannt</b>';
            }
            /*Bei Mietern*/
            if (isset($arr_sort[$a]['EINHEIT_ID'])) {
                $mietername = $arr_sort[$a]['MIETERNAME'];
                $einheit_name = $arr_sort[$a]['EINHEIT_NAME'];
                echo "<tr valign=\"top\" class=\"zeile$ze\" $js_tages_ansicht><td>$z.</td><td>$mitarbeiter_name</td><td>$datum</td><td>$km km</td><td><b>Einheit: $einheit_name</b><br>MIETER: $mietername<br>$str $nr $plz<br>Kunde:$kos_bez</td><td>$hersteller</td><td>$g_bez</td><td>$einbauort</td><td>$l_faellig</td><td>$l_wartung</td><td>Alle $intervall M.</td></tr>";
            } else {
                echo "<tr valign=\"top\" class=\"zeile$ze\" $js_tages_ansicht><td>$z.</td><td>$mitarbeiter_name</td><td>$datum</td><td>$km km</td><td><b>KUNDE: $kos_bez</b><br>$str $nr $plz</td><td>$hersteller</td><td>$g_bez</td><td>$einbauort</td><td>$l_faellig</td><td>$l_wartung</td><td>Alle $intervall M.</td></tr>";
            }
            if ($ze == 2) {
                $ze = 0;
            }
        }
        echo "</table>";
    } else {
        echo "Keine Vorschläge";
    }

}


function vorschlag_kurz($gruppe_id = '1', $gemacht = 'NOT')
{
    session()->put('gruppe_id', $gruppe_id);
    $arr = alle_noch_zu_machen_arr($gruppe_id);
    if (!empty($arr)) {
        $anz = count($arr);
        if (!session()->has('datum_df')) {
            $datum_sql = date("Y-m-d");
        } else {
            $datum_sql = date_german2mysql(session()->get('datum_df'));
        }
        if (!request()->has('datum_d')) {
            $datum_df = date_mysql2german($datum_sql);
        } else {
            $datum_sql = date_german2mysql(request()->input('datum_d'));
            $datum_df = request()->input('datum_d');
            session()->put('datum_df', $datum_df);
        }
        echo "<br>";
        datum_feld2('Terminvorschläge nach dem', 'datum_d', 'datum_d', $datum_df);
        $datum_feld = 'document.getElementById(\'datum_d\').value';

        $gruppe_element = 'document.getElementById(\'vorschlag_gruppe_id\')';
        $selected_index = $gruppe_element . '.selectedIndex';
        $vorschlag_gruppe_id = $gruppe_element . ".options[$selected_index].value";
        $d_onchange = "onChange=\"daj3('/wartungsplaner/ajax?option=detail_geraet&tab=W_GERAETE&tab_id='+this.value,'rightBox1');daj3('/wartungsplaner/ajax?option=geraete_info_anzeigen&g_id='+this.value,'rightBox');daj3('/wartungsplaner/ajax?option=termin_suchen&g_id='+this.value,'leftBox1');daj3('/wartunsplaner/ajax?option=get_datum_lw&g_id='+this.value,'lw_datum')\"";
        $js_suche = "onclick=\"daj3('" . route('web::wartungsplaner::ajax', ['option' => 'termin_vorschlaege_kurz', 'datum_d' => $datum_feld, 'vorschlag_gruppe_id' => $vorschlag_gruppe_id], false) . ", 'leftBox1');\"";
        button('btn_heute', 'btn_heute', 'Erneut vorschlagen', $js_suche, '');
        $tt = 1;
        if (request()->has('vorschlaege_anzeigen')) {
            $start_a = intval(request()->input('vorschlaege_anzeigen'));
        } else {
            $start_a = 0;
        }
        for ($a = $start_a; ($a < $start_a + 10 && $a < $anz); $a++) {

            $kos_typ = $arr[$a]['KOSTENTRAEGER_TYP'];
            $kos_id = $arr[$a]['KOSTENTRAEGER_ID'];

            if ($kos_typ == 'Partner') {
                $g = new general();
                $g->get_partner_info($kos_id);
                $arr_new[$a] = $arr[$a];
                $arr_new[$a]['STRASSE'] = $g->partner_strasse;
                $arr_new[$a]['NR'] = $g->partner_hausnr;
                $arr_new[$a]['PLZ'] = $g->partner_plz;

                $g_id = $arr[$a]['GERAETE_ID'];
                $datum_df = date_mysql2german($datum_sql);
                $best_r = besten_termin_suchen($g_id, $datum_df);
                $arr_new[$a]['KM'] = $best_r['D_KM'];
                $arr_new[$a]['DATUM'] = $best_r['DATUM'];
                $arr_new[$a]['DATUMZ'] = $best_r['DATUMZ'];
                $arr_new[$a]['MITARBEITER_ID'] = $best_r['BENUTZER_ID'];

                $l_wartung = $arr[$a]['L_WART'];
                $arr_new[$a]['L_WART'] = $l_wartung;
                if (isset($best_r['EINHEIT_NAME'])) {
                    $arr_new[$a]['EINHEIT_NAME'] = $best_r['EINHEIT_NAME'];
                    $arr_new[$a]['EINHEIT_ID'] = $best_r['EINHEIT_ID'];
                    $arr_new[$a]['STRASSE'] = $best_r['EINHEIT_STR'];
                    $arr_new[$a]['NR'] = $best_r['EINHEIT_NR'];
                    $arr_new[$a]['PLZ'] = $best_r['EINHEIT_PLZ'];
                    $arr_new[$a]['ORT'] = $best_r['EINHEIT_ORT'];
                }
                if (isset($best_r['MV_ID'])) {
                    $arr_new[$a]['MV_ID'] = $best_r['MV_ID'];
                }
                if (isset($best_r['MIETERNAME'])) {
                    $arr_new[$a]['MIETERNAME'] = $best_r['MIETERNAME'];
                }
            }
        }
        unset($arr);
        $srt = new arr_multisort();
        $srt->setArray($arr_new);
        unset($arr_new);
        $srt->addColumn("L_WART", SRT_ASC);
        $srt->addColumn("DATUMZ", SRT_ASC);
        $srt->addColumn("KM", SRT_ASC);
        $arr_sort = $srt->sort();
        $anz1 = count($arr_sort);
        $weiter_stueck = $start_a + 10;
        if ($weiter_stueck > $anz) {
            $weiter_stueck = 0;
        }
        $js_weiter = "onclick=\"daj3('" . route('web::wartungsplaner::ajax', ['option' => 'termin_vorschlaege_kurz', 'vorschlaege_anzeigen' => $weiter_stueck], false) . "','leftBox1');\"";
        button('btn_weiter', 'btn_w', 'Weitere anzeigen', $js_weiter);
        echo "<table class=\"sortable\">";
        echo "<tr><th class=\"sorttable_numeric\">Z</th><th>Mitarbeiter</th><th class=\"sorttable_numeric\">DATUM</th><th>LETZTE</th><th class=\"sorttable_numeric\">Entf</th><th>Kunde</th><th>Hersteller<br>Bezeichnung</th><th class=\"sorttable_numeric\">Alle</th></tr>";
        $z = 0;
        $ze = 0;
        for ($a = 0; $a < $anz1; $a++) {
            $z++;
            $ze++;
            $g_id = $arr_sort[$a]['GERAETE_ID'];
            $kos_typ = $arr_sort[$a]['KOSTENTRAEGER_TYP'];
            $kos_id = $arr_sort[$a]['KOSTENTRAEGER_ID'];
            $l_wartung = $arr_sort[$a]['L_WART'];
            $l_faellig = date_mysql2german($arr_sort[$a]['L_WART_FAELLIG']);
            $km = $arr_sort[$a]['KM'];
            $datum = $arr_sort[$a]['DATUM'];
            $kos_bez = substr(get_partner_name($kos_id), 0, 46);
            $mitarbeiter_id = $arr_sort[$a]['MITARBEITER_ID'];
            $mitarbeiter_name = get_benutzername($mitarbeiter_id);
            $intervall = $arr_sort[$a]['INTERVAL_M'];
            $datumz = $arr_sort[$a]['DATUMZ'];
            $einbauort = $arr_sort[$a]['EINBAUORT'];
            $hersteller = $arr_sort[$a]['HERSTELLER'];
            $g_bez = $arr_sort[$a]['BEZEICHNUNG'];
            $js_t = "onclick=\"setTimeout('daj3(\'/wartungsplaner/ajax?option=kos_typ_register&kos_typ=$kos_typ&kos_id=$kos_id\', \'rightBox\')', 100);";
            $js_t = $js_t . "setTimeout('daj3(\'/wartungsplaner/ajax?option=wartungsteil_waehlen\', \'leftBox\')', 1000);";
            $js_t = $js_t . "setTimeout('daj3(\'/wartungsplaner/ajax?option=get_datum_lw&g_id=$g_id\', \'lw_datum\')', 1500);";
            $js_t = $js_t . "setTimeout('daj3(\'/wartungsplaner/ajax?option=karte&b_id=$mitarbeiter_id&datum_d=$datum\', \'rightBox\')', 1000);";
            $js_t = $js_t . "setTimeout('daj3(\'/wartungsplaner/ajax?option=termine_tag_tab&b_id=$mitarbeiter_id&datum=$datum&g_id=$g_id\', \'rightBox1\')', 1000);";

            $js_tages_ansicht = $js_t . "\"";


            if (isset($arr_sort[$a]['L_WART'])) {
                $l_wartung = date_mysql2german($arr_sort[$a]['L_WART']);
            } else {
                $l_wartung = '<b>nicht bekannt</b>';
            }
            /*Bei Mietern*/
            if (isset($arr_sort[$a]['EINHEIT_ID'])) {
                $str = $arr_sort[$a]['STRASSE'];
                $nr = $arr_sort[$a]['NR'];
                $plz = $arr_sort[$a]['PLZ'];
                $ort = $arr_sort[$a]['ORT'];
                $einheit_info_arr = get_einheit_info($arr_sort[$a]['EINHEIT_ID']);
                $mietername = $einheit_info_arr['MIETER'];
                $einheit_name = $arr_sort[$a]['EINHEIT_NAME'];
                if (!$mietername == 'Leerstand') {
                    $mietername .= kontaktdaten_anzeigen_mieter($einheit_name);
                }
                echo "<tr valign=\"top\" class=\"zeile$ze\" $js_tages_ansicht><td>$z.";
                $art_id = $arr_sort[$a]['EINHEIT_ID'];
                $url = route('web::wartungsplaner::ajax', ['option' => 'pdf_anschreiben', 'art' => 'Mieter', 'art_id' => $art_id], false);
                $js_pdf_zettel = "onclick=\"window.open('$url');\"";
                button('btn_pdf_a', 'btn_pdf_a', 'Einwurfzettel', $js_pdf_zettel);
                echo "</td><td>$mitarbeiter_name</td><td>$datum</td><td>$l_wartung</td><td>$km km</td><td><b>Einheit: $einheit_name</b><br><b>MIETER:</b> $mietername<br>$str $nr $plz<br><b>Kunde</b>:$kos_bez</td><td>$hersteller<br>$g_bez<br>$einbauort</td><td>Alle $intervall M.</td></tr>";
            } else {
                $kos_bez .= kontaktdaten_anzeigen_kunde($arr_sort[$a]['KOSTENTRAEGER_ID']);
                $ku = new general();
                $ku->get_partner_info($arr_sort[$a]['KOSTENTRAEGER_ID']);

                echo "<tr valign=\"top\" class=\"zeile$ze\" $js_tages_ansicht><td>$z.";

                $art_id = $arr_sort[$a]['KOSTENTRAEGER_ID'];
                $url = route('web::wartungsplaner::ajax', ['option' => 'pdf_anschreiben', 'art' => 'Partner', 'art_id' => $art_id], false);
                $js_pdf_zettel = "onclick=\"window.open('$url');\"";
                button('btn_pdf_a', 'btn_pdf_a', 'Einwurfzettel', $js_pdf_zettel);

                echo "</td><td>$mitarbeiter_name</td><td>$datum</td><td>$l_wartung</td><td>$km km</td><td><b>KUNDE: $kos_bez</b><br>$ku->partner_strasse $ku->partner_hausnr<br>$ku->partner_plz $ku->partner_ort</td><td>$hersteller<br>$g_bez<br>$einbauort</td><td>Alle $intervall M.</td></tr>";
            }
            if ($ze == 2) {
                $ze = 0;
            }
        }
        echo "</table>";

        #print_r($arr_sort);
        $weiter_stueck = $start_a + 10;
        if ($weiter_stueck > $anz) {
            $weiter_stueck = 0;
        }
        $js_weiter = "onclick=\"daj3('" . route('web::wartungsplaner::ajax', ['option' => 'termin_vorschlaege_kurz', 'vorschlaege_anzeigen' => $weiter_stueck], false) . "','leftBox1');\"";
        button('btn_weiter', 'btn_w', 'Weitere anzeigen', $js_weiter);
    } else {
        echo "Keine Vorschläge";
    }

}


function vorschlag_kurz_chrono($gruppe_id = '1', $gemacht = 'NOT')
{
    session()->put('gruppe_id', $gruppe_id);
    $arr = alle_noch_zu_machen_arr_chrono($gruppe_id);
    if (!empty($arr)) {
        $anz = count($arr);
        if (session()->has('datum_df')) {
            $datum_sql = date("Y-m-d");
        } else {
            $datum_sql = date_german2mysql(session()->get('datum_df'));
        }
        if (!request()->has('datum_d')) {
            $datum_df = date_mysql2german($datum_sql);
        } else {
            $datum_sql = date_german2mysql(request()->input('datum_d'));
            $datum_df = request()->input('datum_d');
            session()->put('datum_df', $datum_df);
        }
        echo "<br>";
        datum_feld2('Terminvorschläge nach dem', 'datum_d', 'datum_d', $datum_df);
        $datum_feld = 'document.getElementById(\'datum_d\').value';

        $gruppe_element = 'document.getElementById(\'vorschlag_gruppe_id\')';
        $selected_index = $gruppe_element . '.selectedIndex';
        $vorschlag_gruppe_id = $gruppe_element . ".options[$selected_index].value";
        $d_onchange = "onChange=\"daj3('/wartungsplaner/ajax?option=detail_geraet&tab=W_GERAETE&tab_id='+this.value,'rightBox1');daj3('/wartungsplaner/ajax?option=geraete_info_anzeigen&g_id='+this.value,'rightBox');daj3('/wartungsplaner/ajax?option=termin_suchen&g_id='+this.value,'leftBox1');daj3('/wartungsplaner/ajax?option=get_datum_lw&g_id='+this.value,'lw_datum')\"";
        $js_suche = "onclick=\"daj3('" . route('web::wartungsplaner::ajax', ['option' => 'termin_vorschlaege_kurz', 'datum_d' => $datum_feld, 'vorschlag_gruppe_id' => $vorschlag_gruppe_id], false) . ", 'leftBox1');\"";
        button('btn_heute', 'btn_heute', 'Erneut vorschlagen', $js_suche, '');
        $tt = 1;
        if (request()->has('vorschlaege_anzeigen')) {
            $start_a = intval(request()->input('vorschlaege_anzeigen'));
        } else {
            $start_a = 0;
        }
        for ($a = $start_a; ($a < $start_a + 10 && $a < $anz); $a++) {

            $kos_typ = $arr[$a]['KOSTENTRAEGER_TYP'];
            $kos_id = $arr[$a]['KOSTENTRAEGER_ID'];

            if ($kos_typ == 'Partner') {
                $g = new general();
                $g->get_partner_info($kos_id);
                $arr_new[$a] = $arr[$a];
                $arr_new[$a]['STRASSE'] = $g->partner_strasse;
                $arr_new[$a]['NR'] = $g->partner_hausnr;
                $arr_new[$a]['PLZ'] = $g->partner_plz;

                $g_id = $arr[$a]['GERAETE_ID'];
                $datum_df = date_mysql2german($datum_sql);
                $best_r = besten_termin_suchen($g_id, $datum_df);

                $arr_new[$a]['KM'] = $best_r['D_KM'];
                $arr_new[$a]['DATUM'] = $best_r['DATUM'];
                $arr_new[$a]['DATUMZ'] = $best_r['DATUMZ'];
                $arr_new[$a]['MITARBEITER_ID'] = $best_r['BENUTZER_ID'];

                $l_wartung = $arr[$a]['L_WART'];
                $arr_new[$a]['L_WART'] = $l_wartung;
                if (isset($best_r['EINHEIT_NAME'])) {
                    $arr_new[$a]['EINHEIT_NAME'] = $best_r['EINHEIT_NAME'];
                    $arr_new[$a]['EINHEIT_ID'] = $best_r['EINHEIT_ID'];
                    $arr_new[$a]['STRASSE'] = $best_r['EINHEIT_STR'];
                    $arr_new[$a]['NR'] = $best_r['EINHEIT_NR'];
                    $arr_new[$a]['PLZ'] = $best_r['EINHEIT_PLZ'];
                    $arr_new[$a]['ORT'] = $best_r['EINHEIT_ORT'];
                }
                if (isset($best_r['MV_ID'])) {
                    $arr_new[$a]['MV_ID'] = $best_r['MV_ID'];
                }
                if (isset($best_r['MIETERNAME'])) {
                    $arr_new[$a]['MIETERNAME'] = $best_r['MIETERNAME'];
                }
            }
        }
        unset($arr);
        $srt = new arr_multisort();
        $srt->setArray($arr_new);
        unset($arr_new);
        $srt->addColumn("L_WART", SRT_ASC);
        $srt->addColumn("DATUMZ", SRT_ASC);
        $srt->addColumn("KM", SRT_ASC);
        $arr_sort = $srt->sort();
        $anz1 = count($arr_sort);
        $weiter_stueck = $start_a + 10;
        if ($weiter_stueck > $anz) {
            $weiter_stueck = 0;
        }
        $js_weiter = "onclick=\"daj3('" . route('web::wartungsplaner::ajax', ['option' => 'termin_vorschlaege_kurz', 'vorschlaege_anzeigen' => $weiter_stueck], false) . "','leftBox1');\"";
        button('btn_weiter', 'btn_w', 'Weitere anzeigen', $js_weiter);
        echo "<table class=\"sortable\">";
        echo "<tr><th class=\"sorttable_numeric\">Z</th><th>Mitarbeiter</th><th class=\"sorttable_numeric\">DATUM</th><th>LETZTE</th><th class=\"sorttable_numeric\">Entf</th><th>Kunde</th><th>Hersteller<br>Bezeichnung</th><th class=\"sorttable_numeric\">Alle</th></tr>";
        $z = 0;
        $ze = 0;
        for ($a = 0; $a < $anz1; $a++) {
            $z++;
            $ze++;
            $g_id = $arr_sort[$a]['GERAETE_ID'];
            $kos_typ = $arr_sort[$a]['KOSTENTRAEGER_TYP'];
            $kos_id = $arr_sort[$a]['KOSTENTRAEGER_ID'];
            $l_wartung = $arr_sort[$a]['L_WART'];
            $l_faellig = date_mysql2german($arr_sort[$a]['L_WART_FAELLIG']);
            $km = $arr_sort[$a]['KM'];
            $datum = $arr_sort[$a]['DATUM'];
            $kos_bez = substr(get_partner_name($kos_id), 0, 46);
            $mitarbeiter_id = $arr_sort[$a]['MITARBEITER_ID'];
            $mitarbeiter_name = get_benutzername($mitarbeiter_id);
            $intervall = $arr_sort[$a]['INTERVAL_M'];
            $datumz = $arr_sort[$a]['DATUMZ'];
            $einbauort = $arr_sort[$a]['EINBAUORT'];
            $hersteller = $arr_sort[$a]['HERSTELLER'];
            $g_bez = $arr_sort[$a]['BEZEICHNUNG'];
            $js_t = "onclick=\"setTimeout('daj3(\'/wartungsplaner/ajax?option=kos_typ_register&kos_typ=$kos_typ&kos_id=$kos_id\', \'rightBox\')', 100);";
            $js_t = $js_t . "setTimeout('daj3(\'/wartungsplaner/ajax?option=wartungsteil_waehlen\', \'leftBox\')', 1000);";
            $js_t = $js_t . "setTimeout('daj3(\'/wartungsplaner/ajax?option=get_datum_lw&g_id=$g_id\', \'lw_datum\')', 1500);";
            $js_t = $js_t . "setTimeout('daj3(\'/wartungsplaner/ajax?option=karte&b_id=$mitarbeiter_id&datum_d=$datum\', \'rightBox\')', 1000);";
            $js_t = $js_t . "setTimeout('daj3(\'/wartungsplaner/ajax?option=termine_tag_tab&b_id=$mitarbeiter_id&datum=$datum&g_id=$g_id\', \'rightBox1\')', 1000);";

            $js_tages_ansicht = $js_t . "\"";

            if (isset($arr_sort[$a]['L_WART'])) {
                $l_wartung = date_mysql2german($arr_sort[$a]['L_WART']);
            } else {
                $l_wartung = '<b>nicht bekannt</b>';
            }
            /*Bei Mietern*/
            if (isset($arr_sort[$a]['EINHEIT_ID'])) {
                $str = $arr_sort[$a]['STRASSE'];
                $nr = $arr_sort[$a]['NR'];
                $plz = $arr_sort[$a]['PLZ'];
                $ort = $arr_sort[$a]['ORT'];
                $einheit_info_arr = get_einheit_info($arr_sort[$a]['EINHEIT_ID']);
                $mietername = $einheit_info_arr['MIETER'];
                $einheit_name = $arr_sort[$a]['EINHEIT_NAME'];
                if ($mietername == 'Leerstand') {

                } else {

                    $mietername .= kontaktdaten_anzeigen_mieter($einheit_name);


                }
                echo "<tr valign=\"top\" class=\"zeile$ze\" $js_tages_ansicht><td>$z.";
                $art_id = $arr_sort[$a]['EINHEIT_ID'];
                $url = route('web::wartungsplaner::ajax', ['option' => 'pdf_anschreiben', 'art' => 'Mieter', 'art_id' => $art_id], false);
                $js_pdf_zettel = "onclick=\"window.open('$url');\"";
                button('btn_pdf_a', 'btn_pdf_a', 'Einwurfzettel', $js_pdf_zettel);
                echo "</td><td>$mitarbeiter_name</td><td>$datum</td><td>$l_wartung</td><td>$km km</td><td><b>Einheit: $einheit_name</b><br><b>MIETER:</b> $mietername<br>$str $nr $plz<br><b>Kunde</b>:$kos_bez</td><td>$hersteller<br>$g_bez<br>$einbauort</td><td>Alle $intervall M.</td></tr>";
            } else {
                $kos_bez .= kontaktdaten_anzeigen_kunde($arr_sort[$a]['KOSTENTRAEGER_ID']);
                $ku = new general();
                $ku->get_partner_info($arr_sort[$a]['KOSTENTRAEGER_ID']);
                echo "<tr valign=\"top\" class=\"zeile$ze\" $js_tages_ansicht><td>$z.";
                $art_id = $arr_sort[$a]['KOSTENTRAEGER_ID'];
                $url = route('web::wartungsplaner::ajax', ['option' => 'pdf_anschreiben', 'art' => 'Partner', 'art_id' => $art_id], false);
                $js_pdf_zettel = "onclick=\"window.open('$url');\"";
                button('btn_pdf_a', 'btn_pdf_a', 'Einwurfzettel', $js_pdf_zettel);
                echo "</td><td>$mitarbeiter_name</td><td>$datum</td><td>$l_wartung</td><td>$km km</td><td><b>KUNDE: $kos_bez</b><br>$ku->partner_strasse $ku->partner_hausnr<br>$ku->partner_plz $ku->partner_ort</td><td>$hersteller<br>$g_bez<br>$einbauort</td><td>Alle $intervall M.</td></tr>";
            }
            if ($ze == 2) {
                $ze = 0;
            }
        }
        echo "</table>";
        $weiter_stueck = $start_a + 10;
        if ($weiter_stueck > $anz) {
            $weiter_stueck = 0;
        }
        $js_weiter = "onclick=\"daj3('" . route('web::wartungsplaner::ajax', ['option' => 'termin_vorschlaege_kurz', 'vorschlaege_anzeigen' => $weiter_stueck], false) . "','leftBox1');\"";
        button('btn_weiter', 'btn_w', 'Weitere anzeigen', $js_weiter);
    } else {
        echo "Keine Vorschläge";
    }

}


function get_einheit_id($einheit_name)
{
    $result = DB::select("SELECT EINHEIT_ID FROM EINHEIT WHERE EINHEIT_AKTUELL='1' && EINHEIT_KURZNAME='$einheit_name' ORDER BY EINHEIT_DAT DESC LIMIT 0,1");
    return $result[0]['EINHEIT_ID'];
}


function get_einheit_id_vom_mv($mv_id)
{
    $result = DB::select("SELECT EINHEIT_ID FROM MIETVERTRAG WHERE MIETVERTRAG_AKTUELL='1' && MIETVERTRAG_ID='$mv_id' ORDER BY MIETVERTRAG_VON DESC LIMIT 0,1");
    return $result[0]['EINHEIT_ID'];
}


function get_last_mietvertrag_id($einheit_id)
{
    $result = DB::select("SELECT MIETVERTRAG_ID FROM MIETVERTRAG WHERE EINHEIT_ID = '$einheit_id' && MIETVERTRAG_AKTUELL = '1' && (MIETVERTRAG_BIS='0000-00-00' OR MIETVERTRAG_BIS>=DATE_FORMAT(NOW(), '%Y-%m-%d')) ORDER BY MIETVERTRAG_VON DESC LIMIT 0 , 1 ");
    if (!empty($result)) {
        return $result[0]['MIETVERTRAG_ID'];
    }
}


function get_mieter_infos($mv_id)
{
    $result = DB::select("SELECT PERSON_MIETVERTRAG_PERSON_ID FROM PERSON_MIETVERTRAG WHERE PERSON_MIETVERTRAG_MIETVERTRAG_ID='$mv_id' && PERSON_MIETVERTRAG_AKTUELL='1' ORDER BY PERSON_MIETVERTRAG_ID ASC");
    if (!empty($result)) {
        $person_string = '';
        foreach($result as $row) {
            $person_id = $row['PERSON_MIETVERTRAG_PERSON_ID'];
            $result1 = DB::select("SELECT PERSON_NACHNAME, PERSON_VORNAME FROM PERSON WHERE PERSON_ID='$person_id' && PERSON_AKTUELL='1' ORDER BY PERSON_VORNAME, PERSON_VORNAME ASC");
            if (!empty($result1)) {
                foreach($result1 as $row1) {
                    $p_nname = $row1['PERSON_NACHNAME'];
                    $p_vname = $row1['PERSON_VORNAME'];
                    $person_string .= "$p_nname $p_vname\n";
                }
            }
        }
        return $person_string;
    } else {
        return 'Personinfos unbekannt';
    }
}

function get_einheit_info($einheit_id)
{
    $result = DB::select("SELECT EINHEIT_KURZNAME, EINHEIT.EINHEIT_ID, EINHEIT_QM, EINHEIT_LAGE, TYP,  HAUS.HAUS_STRASSE, HAUS_NUMMER, HAUS_STADT, HAUS_PLZ, OBJEKT.OBJEKT_ID, OBJEKT_KURZNAME, EIGENTUEMER_PARTNER FROM EINHEIT, HAUS, OBJEKT WHERE EINHEIT_AKTUELL='1' && EINHEIT_ID='$einheit_id' && EINHEIT.HAUS_ID=HAUS.HAUS_ID && HAUS.OBJEKT_ID=OBJEKT.OBJEKT_ID && HAUS_AKTUELL='1' && OBJEKT_AKTUELL='1' ORDER BY EINHEIT_DAT DESC LIMIT 0,1");
    if (!empty($result)) {
        $row = $result[0];
        $mv_id = get_last_mietvertrag_id($einheit_id);
        if (empty($mv_id)) {
            /*Nie vermietet*/
            $row['MIETER'] = 'Leerstand';
        } else {
            $row['MIETER'] = get_mieter_infos($mv_id);
            $row['MV_ID'] = $mv_id;
        }
        return $row;
    } else {
        $row['MIETER'] = "Einheit $einheit_id unbekannt";
        return $row;
    }

}

function handy($datum_d)
{
    if (!session()->has('mitarbeiter_id')) {
        die('Mitarbeiter wählen');
    }

    $datum_gestern = tage_minus_wp($datum_d, 1);
    $datum_morgen = tage_plus_wp($datum_d, 1);
    echo "<p class=\"zeile_ueber\"><a href=\"" . route('web::wartungsplaner::ajax', ['option' => 'handy', 'datum_d' => $datum_gestern], false) . "\">GESTERN $datum_gestern</a></p>";

    $arr = tages_termine_arr_b(session()->get('mitarbeiter_id'), $datum_d);
    $anz = count($arr);
    $wt_name = get_wochentag_name($datum_d);
    echo "<p class=\"zeile_detail\"><b>$wt_name $datum_d</b></p>";
    for ($a = 0; $a < $anz; $a++) {
        $von = $arr[$a]['VON'];
        $bis = $arr[$a]['BIS'];
        $txt = $arr[$a]['TEXT'];


        if (isset($arr[$a]['KOSTENTRAEGER_ID'])) {
            $dat = $arr[$a]['DAT'];
            if (!check_is_eigentuemer($arr[$a]['KOSTENTRAEGER_ID'])) {
                $g_z = new general();
                $g_z->get_partner_info($arr[$a]['KOSTENTRAEGER_ID']);
                $lat_lon_db_ziel = get_lat_lon_db_osm($g_z->partner_strasse, $g_z->partner_hausnr, $g_z->partner_plz, $g_z->partner_ort);
                $bis_str = "$g_z->partner_strasse $g_z->partner_hausnr";
                $kunden_info = "<b>KUNDE:</b>$g_z->partner_name<br>$g_z->partner_strasse$g_z->partner_hausnr, $g_z->partner_plz $g_z->partner_ort";
            } else {
                /*Mieterinformationen holen*/
                $g_id = $arr[$a]['GERAETE_ID'];
                $g_info_arr = geraete_info_arr($g_id);
                extract($g_info_arr[0]);
                $einheit_bez = $g_info_arr[0]['LAGE_RAUM'];
                $gr = new general();
                $gr->get_lanlon_mieter($einheit_bez);
                $lat_lon_db_ziel = $gr->mieter_lat_lon_db_ziel;
                $kunden_info = $gr->mieter_info;
                $bis_str = $gr->mieter_bis_str;
                $g_info = "<b>$HERSTELLER / $BEZEICHNUNG</b>";

            }
            echo "<a class=\"handy\" href=\"" . route('web::wartungsplaner::ajax', ['option' => 'form_start_stop', 'tab' => 'GEO_TERMINE', 'tab_dat' => $dat], false) . "\"><p class=\"zeile_belegt\">$von - $bis<br>$kunden_info<br>$g_info</p></a>";
        } else {
            echo "<p class=\"zeile_frei\">$von - $bis<br>$txt</p>";
        }
    }//end for
    echo "<p class=\"zeile_ueber\"><a href=\"" . route('web::wartungsplaner::ajax', ['option' => 'handy', 'datum_d' => $datum_morgen], false) . "\">MORGEN $datum_morgen</a></p>";


}

function t_starten($tab, $tab_dat)
{
    $gg = new general();
    $gg->check_status($tab, $tab_dat);
    if ($gg->status == 'nicht angefangen') {
        $b_id = session()->get('mitarbeiter_id');
        DB::insert("INSERT INTO START_STOP VALUES('NULL','$tab','$tab_dat', NULL, NULL, NULL, '$b_id', '1')");
    }
    form_start_stop($tab, $tab_dat);
}

function t_beenden($tab, $tab_dat)
{
    $gg = new general();
    $gg->check_status($tab, $tab_dat);
    if ($gg->status == 'aktiv') {
        $b_id = session()->get('mitarbeiter_id');
        DB::update("UPDATE START_STOP SET END_TIME=CURRENT_TIMESTAMP, BENUTZER_ID='$b_id' WHERE TAB='$tab' && TAB_DAT='$tab_dat'");
    }
    form_start_stop($tab, $tab_dat);
}


function form_start_stop($tab, $tab_dat)
{
    $bn = get_benutzername(session()->get('benutzer_id'));
    $mn = get_benutzername(session()->get('mitarbeiter_id'));
    $gg = new general();

    if ($gg->check_my_active(session()->get('mitarbeiter_id'), $tab)) {
        $tab_dat_neu = $gg->check_my_active(session()->get('mitarbeiter_id'), $tab);
        if ($tab_dat_neu == $tab_dat) {
            echo "<p class=\"zeile_hinweis\">$tab_dat DIESE AUFGABE IST SCHON ANGEFANGEN, ERST DIESE BEENDEN ODER ABBRECHEN!</p>";
        } else {
            echo "<p class=\"zeile_hinweis\">$tab_dat ANDERE AUFGABE IST SCHON ANGEFANGEN, ERST DIESE BEENDEN ODER ABBRECHEN!</p>";
            $tab_dat = $tab_dat_neu;
        }
    }

    $gg->check_status($tab, $tab_dat);
    if ($gg->status == 'nicht angefangen') {
        echo "<p class=\"zeile_hinweis\">STATUS: NICHT ANGEFANGEN</p>";
        $js_start = "onclick=\"wopen('" . route('web::wartungsplaner::ajax', ['option' => 't_starten', 'tab' => $tab, 'tab_dat' => $tab_dat], false) . "','');\"";
        echo "<p $js_start class=\"zeile_frei\">STARTEN</p>";
    }

    if ($gg->status == 'erledigt') {
        echo "<p class=\"zeile_hinweis\">STATUS: ERLEDIGT</p>";
        $js_druck = "onclick=\"wopen('" . route('web::wartungsplaner::ajax', ['option' => 't_drucken', 'tab' => $tab, 'tab_dat' => $tab_dat], false) . "','');\"";
        echo "<p $js_druck class=\"zeile_frei\">DRUCKEN</p>";
    }

    if ($gg->status == 'unterbrochen') {
        echo "<p class=\"zeile_hinweis\">STATUS: UNTERBROCHEN</p>";
        $js_neustart = "onclick=\"wopen('" . route('web::wartungsplaner::ajax', ['option' => 't_neustart', 'tab' => $tab, 'tab_dat' => $tab_dat], false) . "','');\"";
        echo "<p $js_neustart class=\"zeile_frei\">NEUSTARTEN</p>";

        $js_druck = "onclick=\"wopen('" . route('web::wartungsplaner::ajax', ['option' => 't_drucken', 'tab' => $tab, 'tab_dat' => $tab_dat], false) . "','');\"";
        echo "<p $js_druck class=\"zeile_frei\">DRUCKEN</p>";
    }

    if ($gg->status == 'aktiv') {
        echo "<p class=\"zeile_hinweis\">STATUS: AKTIV/LäUFT</p>";
        $js_ende = "onclick=\"wopen('" . route('web::wartungsplaner::ajax', ['option' => 't_beenden', 'tab' => $tab, 'tab_dat' => $tab_dat], false) . "','');\"";
        echo "<p $js_ende class=\"zeile_frei\">BEENDEN</p>";

        $js_ab = "onclick=\"wopen('" . route('web::wartungsplaner::ajax', ['option' => 't_abbruch', 'tab' => $tab, 'tab_dat' => $tab_dat], false) . "','');\"";
        echo "<p $js_ab class=\"zeile_frei\">ABBRECHEN</p>";

        $js_druck = "onclick=\"wopen('" . route('web::wartungsplaner::ajax', ['option' => 't_drucken', 'tab' => $tab, 'tab_dat' => $tab_dat], false) . "','');\"";
        echo "<p $js_druck class=\"zeile_frei\">DRUCKEN</p>";
    }
    $js = '';
    $kw = get_kw(date("d.m.Y"));
    $gg = new general();
    $gg->start_stop_protokoll($tab, $kw, session()->get('mitarbeiter_id'));
}


/*Klasse general*/

class general
{
    public $partner_strasse;
    public $partner_hausnr;
    public $partner_plz;
    public $partner_ort;
    public $km;
    public $zeit;
    public $team_benutzer_ids;
    public $team_bez;
    public $partner_name;
    public $mieter_lat_lon_db_ziel;
    public $mieter_info;
    public $mieter_bis_str;
    public $mieter_name;
    public $kundschaft;
    public $fahrzeit;
    public $gruppe;
    public $team_id;
    public $team_profile;
    public $t_kos_typ;
    public $t_kos_id;
    public $t_g_id;
    public $t_lage_raum;
    public $v_text;
    public $v_kurztext;
    public $status;
    public $quelle;
    public $anschrift;
    public $kontakt;
    public $partner_dat;
    public $partner_land;
    public $gruppe_id;
    public $t_datum;
    public $t_von;
    public $t_bis;
    public $t_text;
    public $t_hersteller;
    public $t_bez;

    function route_anzeigen_karte($b_id = '21', $datum_d)
    {
        $datum = date_german2mysql($datum_d);
        echo "ROUTE $datum $b_id";
        $arr = get_termine_tag_arr($b_id, $datum_d);
        echo "<pre>";
        print_r($arr);
    }


    function karte_anzeigen($b_id, $datum_d, $breite = 580, $hoehe = 400, $zoom = 10)
    {
        $map_berlin = "http://maps.google.com/maps/api/staticmap?center=Berlin,%20Germany&zoom=$zoom&size=" . $breite . "x" . "$hoehe&maptype=roadmap&sensor=false";
        $map_markers = '';


        /*ZIEL einblenden*/
        if (session()->has('ziel_str') && session()->has('ziel_nr') && session()->has('ziel_plz') && session()->has('ziel_ort')) {
            $zlat = session()->get('ziel_lat');
            $zlon = session()->get('ziel_lon');
            $map_markers .= "&markers=color:black|label:Z|$zlon,$zlat";
        }


        /*Startadresse blau mit W*/
        $lon_lat_start = get_lat_lon_db_osm(START_STRASSE, START_NR, START_PLZ, START_ORT);
        $lon_lat_start_arr = explode(',', $lon_lat_start);
        $z_lon = $lon_lat_start_arr[0];
        $z_lat = $lon_lat_start_arr[1];
        $map_markers .= "&markers=color:blue|label:W|$z_lon,$z_lat";

        /*Startadresse des Mitarbeiters z.B. von zu Hause*/
        $gg = new general();
        $b_profil_arr = $gg->get_wteam_profil($b_id);
        $str = explode(',', $b_profil_arr['START_ADRESSE']);
        if (!empty($str)) {
            $lon_lat_start = get_lat_lon_db_osm($str[0], $str[1], $str[2], $str[3]);
            $lon_lat_start_arr = explode(',', $lon_lat_start);
            $z_lon = $lon_lat_start_arr[0];
            $z_lat = $lon_lat_start_arr[1];
            $map_markers .= "&markers=color:yellow|label:H|$z_lon,$z_lat";
        }
        $termine_tag_arr = get_termine_tag_arr($b_id, $datum_d);
        $anz = count($termine_tag_arr);

        $tz = 0;
        for ($a = 0; $a < $anz; $a++) {
            $tz = $a + 1;
            $arr = $termine_tag_arr[$a];
            $von = $arr['VON'];
            $bis = $arr['BIS'];
            $kos_typ = $arr['KOSTENTRAEGER_TYP'];
            $kos_id = $arr['KOSTENTRAEGER_ID'];
            $text = $arr['TEXT'];
            $hinweis = $arr['HINWEIS'];
            $g_id = $arr['GERAETE_ID'];

            if ($text == 'x') {
                $text = '';
            }
            if ($hinweis == 'x') {
                $hinweis = '';
            }

            $g_info_arr = geraete_info_arr($g_id);
            $g_her = $g_info_arr[0]['HERSTELLER'];
            $g_bez = $g_info_arr[0]['BEZEICHNUNG'];
            $baujahr = $g_info_arr[0]['BAUJAHR'];
            $r_empf = $g_info_arr[0]['RECHNUNG_AN'];
            $lage_raum = umlaute_anpassen($g_info_arr[0]['LAGE_RAUM']);


            if (!check_is_eigentuemer($kos_id)) {
                $g_z = new general();
                $g_z->get_partner_info($kos_id);
                $name = umlaute_anpassen($g_z->partner_name);
                $anschrift = umlaute_anpassen("$g_z->partner_strasse $g_z->partner_hausnr, $g_z->partner_plz $g_z->partner_ort");
                $kontakt_info = ltrim(str_replace('\r', ' ', str_replace('\n', ' ', str_replace('<br />', ' ', str_replace('<br>', ' ', ltrim(rtrim(kontaktdaten_anzeigen_kunde($kos_id))))))));
                $lat_lon = get_lat_lon_db_osm($g_z->partner_strasse, $g_z->partner_hausnr, $g_z->partner_plz, $g_z->partner_ort);
                $lat_lon_arr = explode(',', $lat_lon);
                $lat = $lat_lon_arr[0];
                $lon = $lat_lon_arr[1];
                $map_markers .= "&markers=color:red|label:$tz|$lat,$lon";
                #echo $map_markers;
            } else {
                /*Mieterinformationen holen*/
                $gr = new general();
                $gr->get_lanlon_mieter($lage_raum);
                $name = str_replace('\n', ' ', ltrim(str_replace('<br>', ' ', rtrim(umlaute_anpassen("$gr->mieter_name")))));
                $anschrift = umlaute_anpassen($gr->mieter_bis_str);
                $kontakt_info = ltrim(rtrim(str_replace('\r', ' ', str_replace('\n', ' ', ltrim(str_replace('<br>', ' ', str_replace('<br />', ' ', umlaute_anpassen(kontaktdaten_anzeigen_mieter($lage_raum)))))))));
                $lat_lon = $gr->mieter_lat_lon_db_ziel;
                $lat_lon_arr = explode(',', $lat_lon);
                $lat = $lat_lon_arr[0];
                $lon = $lat_lon_arr[1];
                $map_markers .= "&markers=color:green|label:$tz|$lat,$lon";
            }
        }


        echo "<center><a href=\"" . route('web::wartungsplaner::ajax', ['option' => 'karte_gross', 'b_id' => $b_id, 'datum_d' => $datum_d], false) . "\" target=\"_blank\"><img border=\"0\" src =\"$map_berlin$map_markers\"></a></center>";
    }

    function get_wteam_profil($benutzer_id)
    {
        $result = DB::select("SELECT *, DATE_FORMAT(VON, '%H:%i') AS VON, DATE_FORMAT(BIS, '%H:%i') AS BIS FROM W_TEAM_PROFILE WHERE BENUTZER_ID='$benutzer_id' && AKTUELL = '1'");
        foreach($result as $row) {
            $benutzer_id = $row['BENUTZER_ID'];
            $arr['ID'] = $row['ID'];
            $arr['1'] = $row['1'];
            $arr['2'] = $row['2'];
            $arr['3'] = $row['3'];
            $arr['4'] = $row['4'];
            $arr['5'] = $row['5'];
            $arr['6'] = $row['6'];
            $arr['7'] = $row['7'];
            $arr['VON'] = $row['VON'];
            $arr['BIS'] = $row['BIS'];
            $arr['TERMINE_TAG'] = $row['TERMINE_TAG'];
            $arr['START_ADRESSE'] = $row['START_ADRESSE'];
            $arr['AKTIV'] = $row['AKTIV'];
        }
        return $arr;
    }

    function get_partner_info($partner_id)
    {
        $result = DB::select("SELECT *  FROM PARTNER_LIEFERANT WHERE PARTNER_ID='$partner_id' && AKTUELL = '1'");
        if (!empty($result)) {
            $row = $result[0];
            $this->partner_dat = $row['PARTNER_DAT'];
            $this->partner_name = $row['PARTNER_NAME'];
            $this->partner_strasse = $row['STRASSE'];
            $this->partner_hausnr = $row['NUMMER'];
            $this->partner_plz = $row['PLZ'];
            $this->partner_ort = $row['ORT'];
            $this->partner_land = $row['LAND'];
            get_lat_lon_db_osm($this->partner_strasse, $this->partner_hausnr, $this->partner_plz, $this->partner_ort);
        }
    }

    function get_lanlon_mieter($einheit_bez)
    {
        $einheit_id = get_einheit_id($einheit_bez);
        $einheit_info_arr = get_einheit_info($einheit_id);
        $mieter = $einheit_info_arr['MIETER'];
        if (isset($einheit_info_arr['HAUS_STRASSE'])) {
            $m_str = $einheit_info_arr['HAUS_STRASSE'];
            $m_nr = $einheit_info_arr['HAUS_NUMMER'];
            $m_plz = $einheit_info_arr['HAUS_PLZ'];
            $m_ort = $einheit_info_arr['HAUS_STADT'];
            $m_lage = $einheit_info_arr['EINHEIT_LAGE'];
            $kunden_info = "<b>EINHEIT: $einheit_bez<br>Mieter: $mieter</b><br>$m_str $m_nr, $m_plz $m_ort<br>Lage:$m_lage";
        } else {
            $kunden_info = "EINHEIT: $einheit_bez<br>Mieter: <b>$mieter</b>";
        }
        $this->mieter_info = $kunden_info;
        $this->mieter_name = $mieter;
        $this->mieter_lat_lon_db_ziel = get_lat_lon_db_osm(umlaute_anpassen($m_str), $m_nr, $m_plz, umlaute_anpassen($m_ort));
        $this->mieter_bis_str = "$m_str $m_nr";
    }

    function karte_anzeigen_alle_geraete()
    {
        $map_markers = '';


        $termine_tag_arr = get_alle_termine_arr();
        $anz = count($termine_tag_arr);

        $tz = 0;
        for ($a = 0; $a < $anz; $a++) {
            $tz = $a + 1;
            $arr = $termine_tag_arr[$a];
            $von = $arr['VON'];
            $bis = $arr['BIS'];
            $kos_typ = $arr['KOSTENTRAEGER_TYP'];
            $kos_id = $arr['KOSTENTRAEGER_ID'];
            $text = $arr['TEXT'];
            $hinweis = $arr['HINWEIS'];
            $g_id = $arr['GERAETE_ID'];

            if ($text == 'x') {
                $text = '';
            }
            if ($hinweis == 'x') {
                $hinweis = '';
            }

            $g_info_arr = geraete_info_arr($g_id);
            $g_her = $g_info_arr[0]['HERSTELLER'];
            $g_bez = $g_info_arr[0]['BEZEICHNUNG'];
            $baujahr = $g_info_arr[0]['BAUJAHR'];
            $r_empf = $g_info_arr[0]['RECHNUNG_AN'];
            $lage_raum = umlaute_anpassen($g_info_arr[0]['LAGE_RAUM']);


            if (!check_is_eigentuemer($kos_id)) {
                $g_z = new general();
                $g_z->get_partner_info($kos_id);
                $name = umlaute_anpassen($g_z->partner_name);
                $anschrift = umlaute_anpassen("$g_z->partner_strasse $g_z->partner_hausnr, $g_z->partner_plz $g_z->partner_ort");
                $kontakt_info = ltrim(str_replace('\r', ' ', str_replace('\n', ' ', str_replace('<br />', ' ', str_replace('<br>', ' ', ltrim(rtrim(kontaktdaten_anzeigen_kunde($kos_id))))))));
                $lat_lon = get_lat_lon_db_osm($g_z->partner_strasse, $g_z->partner_hausnr, $g_z->partner_plz, $g_z->partner_ort);
                $lat_lon_arr = explode(',', $lat_lon);
                $lat = $lat_lon_arr[0];
                $lon = $lat_lon_arr[1];
                if (!empty($lot) && !empty($lon)) {
                    $this->kundschaft[] = "$tz,$lat,$lon";
                }
                $map_markers .= "&markers=color:red|label:$tz|$lat,$lon";
            } else {
                /*Mieterinformationen holen*/
                $gr = new general();
                $gr->get_lanlon_mieter($lage_raum);
                $name = str_replace('\n', ' ', ltrim(str_replace('<br>', ' ', rtrim(umlaute_anpassen("$gr->mieter_name")))));
                $anschrift = umlaute_anpassen($gr->mieter_bis_str);
                $kontakt_info = ltrim(rtrim(str_replace('\r', ' ', str_replace('\n', ' ', ltrim(str_replace('<br>', ' ', str_replace('<br />', ' ', umlaute_anpassen(kontaktdaten_anzeigen_mieter($lage_raum)))))))));
                $lat_lon = $gr->mieter_lat_lon_db_ziel;
                $lat_lon_arr = explode(',', $lat_lon);
                $lat = $lat_lon_arr[0];
                $lon = $lat_lon_arr[1];
                if (!empty($lat) && !empty($lon)) {
                    $this->kundschaft[] = "$tz,$lat,$lon";
                }
                $map_markers .= "&markers=color:green|label:$tz|$lat,$lon";
            }
        }

        $anz = count($this->kundschaft);

        echo '<?xml version="1.0"?>';

        echo "<markers>";
        for ($a = 0; $a < $anz; $a++) {
            echo "<marker>";
            $zeile = $this->kundschaft[$a];
            $info = explode(',', $zeile);
            echo "<name>";
            echo $info[0];
            echo "</name>";
            echo "<address>";
            echo '';
            echo "</address>";
            echo "<lat>";
            echo $info[1];
            echo "</lat>";
            echo "<lng>";
            echo $info[2];
            echo "</lng>";
            echo "</marker>";
        }
        echo "</markers>";
    }

    /*Liste der Mitarbeiter die nicht im Team sind*/

    function dropdown_teams($label, $id, $name, $selected, $js, $class_r = 'reihe', $class_f = 'feld')
    {
        echo "<div class=\"$class_r\">";
        echo "<span class=\"label\">$label</span>";
        echo "<span class=\"$class_f\">";
        echo "<select name=\"$name\" size=\"1\" id=\"$id\" $js>\n";
        $arr = $this->get_teams_arr();
        if (!empty($arr)) {
            $anz = count($arr);
            for ($a = 0; $a < $anz; $a++) {
                $t_id = $arr[$a]['TEAM_ID'];
                $t_bez = $arr[$a]['TEAM_BEZ'];
                if ($selected == $t_id) {
                    echo "<option selected value=\"$t_id\">$t_bez</option>";
                } else {
                    echo "<option value=\"$t_id\">$t_bez</option>";
                }
            }
            echo "</select>\n";
            echo "</span>";
            echo "</div>";

        } else {
            echo "Keine Teams vorhanden";
        }
    }

    function get_teams_arr()
    {
        $abfrage = "SELECT  * FROM W_TEAMS WHERE AKTUELL='1' ORDER BY TEAM_BEZ";
        $result = DB::select($abfrage);
        return $result;
    }

    function dropdown_mitarbeiter($team_id, $label, $id, $name, $selected, $js, $class_r = 'reihe', $class_f = 'feld')
    {
        $arr = $this->get_wteam_benutzer($team_id);
        if (!empty($arr)) {
            echo "<div class=\"$class_r\">";
            echo "<span class=\"label\">$label</span>";
            echo "<span class=\"$class_f\">";
            echo "<select name=\"$name\" size=\"10\" id=\"$id\" $js>\n";

            $anz = count($arr);
            for ($a = 0; $a < $anz; $a++) {
                $b_id = $arr[$a]['BENUTZER_ID'];
                $b_name = get_benutzername($b_id);
                if ($selected == $b_id) {
                    echo "<option selected value=\"$b_id\">$b_name</option>";
                } else {
                    echo "<option value=\"$b_id\">$b_name</option>";
                }
            }
            echo "</select>\n";
            echo "</span>";
            echo "</div>";
            return true;
        } else {
            echo "Keine Mitarbeiter vorhanden";
            return false;
        }
    }

    function get_wteam_benutzer($team_id)
    {
        $result = DB::select("SELECT BENUTZER_ID FROM W_TEAMS_BENUTZER WHERE TEAM_ID='$team_id' && AKTUELL = '1'");
        return $result;
    }

    function dropdown_mitarbeiter_n_team($team_id, $label, $id, $name, $selected, $js, $class_r = 'reihe', $class_f = 'feld')
    {

        $arr = $this->get_not_team_benutzer($team_id);
        if (!empty($arr)) {
            echo "<div class=\"$class_r\">";
            echo "<span class=\"label\">$label</span>";
            echo "<span class=\"$class_f\">";
            echo "<select name=\"$name\" size=\"5\" id=\"$id\" $js>\n";

            foreach ($arr as $row) {
                $b_id = $row['BENUTZER_ID'];
                $b_name = get_benutzername($b_id);
                if ($selected == $b_id) {
                    echo "<option selected value=\"$b_id\">$b_name</option>";
                } else {
                    echo "<option value=\"$b_id\">$b_name</option>";
                }
            }
            echo "</select>\n";
            echo "</span>";
            echo "</div>";
            return true;
        } else {
            echo "Keine Mitarbeiter vorhanden";
            return false;
        }
    }

    function get_not_team_benutzer($team_id)
    {
        $result = DB::select("SELECT id AS BENUTZER_ID FROM users WHERE id NOT IN (SELECT BENUTZER_ID FROM W_TEAMS_BENUTZER WHERE TEAM_ID=? && AKTUELL = '1') ORDER BY name", [$team_id]);
        return $result;
    }

    function team_hinzu($team_bez)
    {
        $id = last_id2('W_TEAMS', 'TEAM_ID') + 1;
        DB::insert("INSERT INTO W_TEAMS VALUES(NULL, '$id', '$team_bez', '1')");
        echo "Team $team_bez wurde hinzugefügt!";
    }

    function get_team_liste($link)
    {
        $arr = $this->get_teams_arr();
        if (!empty($arr)) {
            $anz = count($arr);
            for ($a = 0; $a < $anz; $a++) {
                $t_id = $arr[$a]['TEAM_ID'];
                $t_bez = $arr[$a]['TEAM_BEZ'];
                echo "<a href=\"$link&team_id=$t_id\">$t_bez</a><br>";
            }

        } else {

            echo "Keine Teams vorhanden";
        }
    }

    function mitarbeiter_entfernen($team_id, $b_id)
    {
        DB::update("UPDATE W_TEAMS_BENUTZER SET AKTUELL='0' WHERE TEAM_ID='$team_id' && BENUTZER_ID='$b_id'");
    }

    function mitarbeiter_hinzu($team_id, $b_id)
    {
        $id = last_id2('W_TEAMS_BENUTZER', 'ID') + 1;
        DB::insert("INSERT INTO W_TEAMS_BENUTZER VALUES(NULL, '$id', '$team_id', '$b_id', '1')");

        /*Falls kein profil vorhanden, Leerprofil erstellen*/
        if (!is_array($this->get_wteam_profil($b_id))) {
            $id = last_id2('W_TEAM_PROFILE', 'ID') + 1;
            $start_adresse = START_STRASSE . ',' . START_NR . ',' . START_PLZ . ',' . START_ORT;

            $db_abfrage = 'INSERT INTO W_TEAM_PROFILE VALUES(';
            $db_abfrage .= "NULL, '$id', '$b_id', '0','0','0','0','0','0','0','06:45','15:15','5', '$start_adresse', '1', '1'";
            $db_abfrage .= ")";

            DB::insert($db_abfrage);
        }
    }

    function get_fahrzeit_entf($lat_lon_db_start, $lat_lon_db_ziel)
    {
        unset($this->km);
        unset($this->fahrzeit);
        unset($this->quelle);

        $start_arr = explode(',', $lat_lon_db_start);
        $dat1 = ltrim(rtrim($start_arr[2]));

        $ziel_arr = explode(',', $lat_lon_db_ziel);
        $dat2 = ltrim(rtrim($ziel_arr[2]));

        $db_abfrage = "SELECT * FROM GEO_ENTFERNUNG WHERE AKTUELL='1' && GEO_DAT_START='$dat1' && GEO_DAT_ZIEL='$dat2' ORDER BY DAT DESC LIMIT 0,1";
        $result = DB::select($db_abfrage);
        if (!empty($result)) {
            $row = $result[0];
            if ($row['QUELLE'] == 'OpenStreetMaps') {
                $this->km = $row['KM'] . ' km';
                $this->fahrzeit = $row['FAHRZEIT'];
                $this->quelle = $row['QUELLE'];
            } else {
                $this->km = $row['KM'];
                $this->fahrzeit = $row['FAHRZEIT'];
                $this->quelle = $row['QUELLE'];
            }

        } else {
            /*Berechnen*/
            $s_str = ltrim(rtrim($start_arr[3]));
            $s_nr = ltrim(rtrim($start_arr[4]));
            $s_plz = ltrim(rtrim($start_arr[5]));
            $s_ort = ltrim(rtrim($start_arr[6]));

            $z_str = ltrim(rtrim($ziel_arr[3]));
            $z_nr = ltrim(rtrim($ziel_arr[4]));
            $z_plz = ltrim(rtrim($ziel_arr[5]));
            $z_ort = ltrim(rtrim($ziel_arr[6]));
            $url = "http://maps.google.com/maps/api/directions/xml?origin=$s_str+$s_nr+$s_plz+$s_ort&destination=$z_str+$z_nr+$z_plz+$z_ort&sensor=false&language=de";
            $xml = simplexml_load_file("$url");
            sleep(1);
            if (!$xml) {
                die('Keine route');
            } else {

                $status = $xml->status;
                echo "STATUS $status";
                if ($status == 'OK') {
                    $km = $xml->route->leg->distance->text;
                    $fahrzeit = $xml->route->leg->duration->text;
                    $this->km = $km;
                    $this->fahrzeit = $fahrzeit;
                    $this->quelle = 'GoogleMaps';
                    /*Werte in DB Speichern*/

                } else {
                    /*Hier bei Nominatim anfragen*/
                    $x1 = ltrim(rtrim($start_arr[0]));
                    $x2 = ltrim(rtrim($start_arr[1]));
                    $y1 = ltrim(rtrim($ziel_arr[0]));
                    $y2 = ltrim(rtrim($ziel_arr[1]));
                    $this->quelle = 'OpenStreetMaps';
                    $this->get_strecken_info($x1, $x2, $y1, $y2);
                }

                $values = "'$dat1','$dat2', '$this->km', '$this->fahrzeit', '$this->quelle', '1'";
                save_to_db('GEO_ENTFERNUNG', $values, 'x');
            }

        }

    }

    function get_strecken_info($s_lon, $s_lat, $e_lon, $e_lat)
    {
        $url = "http://open.mapquestapi.com/directions/v0/route?outFormat=xml&from=$s_lon,$s_lat&to=$e_lon,$e_lat&callback=renderNarrative&unit=k&locale=de_DE";
        $xml = simplexml_load_file("$url");
        if (!$xml === FALSE) {
            $this->km = $xml->route->distance / 1;
            $this->zeit = $xml->route->formattedTime;
            $this->fahrzeit = $xml->route->formattedTime;
            unset($xml);
        }
    }

    function get_termin_arr($TEAM_ID = 1, $datum_df, $tage = 7)
    {
        $datum_df = tage_minus_wp($datum_df, 1);
        $dat_arr = datums_array_erstellen($datum_df, $tage);
        $anz = count($dat_arr);
        $z = 0;
        for ($a = 0; $a < $anz; $a++) {
            $datum_d = $dat_arr[$a];
            $datum_sql = date_german2mysql($datum_d);
            $wochentag_nr = get_wochentag($datum_d);

            $abfrage = "SELECT '$datum_d' AS DATUM, DATE_FORMAT('$datum_sql','%Y%m%d') AS DATUMZ, benutzername, W_TEAMS_BENUTZER.BENUTZER_ID,  TERMINE_TAG,  (SELECT COUNT(DAT) FROM GEO_TERMINE WHERE DATUM='$datum_sql' && W_TEAMS_BENUTZER.BENUTZER_ID=GEO_TERMINE.BENUTZER_ID && AKTUELL='1') AS T_BELEGT, TERMINE_TAG-(SELECT COUNT(DAT) FROM GEO_TERMINE WHERE DATUM='$datum_sql' && W_TEAMS_BENUTZER.BENUTZER_ID=GEO_TERMINE.BENUTZER_ID && AKTUELL='1') AS FREI, START_ADRESSE  FROM BENUTZER, `W_TEAMS_BENUTZER`, W_TEAM_PROFILE, GEO_TERMINE WHERE W_TEAM_PROFILE.$wochentag_nr='1' AND `TEAM_ID` = '$TEAM_ID' AND W_TEAMS_BENUTZER.AKTUELL = '1' AND W_TEAM_PROFILE.BENUTZER_ID=W_TEAMS_BENUTZER.BENUTZER_ID AND W_TEAM_PROFILE.AKTUELL='1' AND W_TEAM_PROFILE.AKTIV='1' AND TERMINE_TAG>(SELECT COUNT(*) AS ANZ FROM GEO_TERMINE WHERE GEO_TERMINE.BENUTZER_ID=W_TEAMS_BENUTZER.BENUTZER_ID && GEO_TERMINE.AKTUELL='1' && DATUM='$datum_sql') && W_TEAMS_BENUTZER.BENUTZER_ID NOT IN(SELECT BENUTZER_ID FROM URLAUB WHERE URLAUB.DATUM='$datum_sql' && URLAUB.AKTUELL='1') && BENUTZER.benutzer_id=W_TEAMS_BENUTZER.BENUTZER_ID  GROUP BY  W_TEAMS_BENUTZER.BENUTZER_ID ORDER BY DATUMZ ASC, FREI ASC";

            $result = DB::select($abfrage);
            if (!empty($result)) {
                foreach($result as $row) {
                    $arr[] = $row;
                    $b_id = $row['BENUTZER_ID'];
                    $arr[$z]['D_KM'] = str_replace('.', ',', number_format(get_durchschnitt_km($b_id, $datum_sql), 2));
                    $arr[$z]['LUECKEN'] = get_luecken_termine($b_id, $datum_d);
                    $z++;
                }
            }

        }//end for

        if (isset($arr)) {
            $anz_v_termine = count($arr);
            if ($anz_v_termine < 3) {
                $tage += 15;
                $arr = $this->get_termin_arr($TEAM_ID, $datum_df, $tage);
            } else {
                return $arr;
            }
        } else {
            if ($tage < 766) {
                $tage += 21;
                $arr = $this->get_termin_arr($TEAM_ID, $datum_df, $tage);
            }
        }
        if (isset($arr)) {
            return $arr;
        }

    }

    function get_termin_arr1($TEAM_ID = '1', $datum_df, $tage = 7)
    {
        $datum_df = tage_minus_wp($datum_df, 1);
        $dat_arr = datums_array_erstellen($datum_df, $tage);
        $anz = count($dat_arr);
        $z = 0;
        for ($a = 0; $a < $anz; $a++) {
            $datum_d = $dat_arr[$a];
            $datum_sql = date_german2mysql($datum_d);
            $wochentag_nr = get_wochentag($datum_d);
            /*Mit freien Terminen*/
            $abfrage1 = "SELECT '$datum_d' AS DATUM,  DATE_FORMAT('$datum_sql','%Y%m%d') AS DATUMZ, benutzername, W_TEAMS_BENUTZER.BENUTZER_ID,  TERMINE_TAG,  START_ADRESSE  FROM BENUTZER, `W_TEAMS_BENUTZER`, W_TEAM_PROFILE, GEO_TERMINE WHERE W_TEAM_PROFILE.$wochentag_nr='1' AND `TEAM_ID` = '$TEAM_ID' AND W_TEAMS_BENUTZER.AKTUELL = '1' AND W_TEAM_PROFILE.BENUTZER_ID=W_TEAMS_BENUTZER.BENUTZER_ID AND W_TEAM_PROFILE.AKTUELL='1' AND W_TEAM_PROFILE.AKTIV='1' AND TERMINE_TAG>(SELECT COUNT(*) AS ANZ FROM GEO_TERMINE WHERE GEO_TERMINE.BENUTZER_ID=W_TEAMS_BENUTZER.BENUTZER_ID && GEO_TERMINE.AKTUELL='1' && DATUM='$datum_sql') && W_TEAMS_BENUTZER.BENUTZER_ID NOT IN(SELECT BENUTZER_ID FROM URLAUB WHERE URLAUB.DATUM='$datum_sql' && URLAUB.AKTUELL='1') && BENUTZER.benutzer_id=W_TEAMS_BENUTZER.BENUTZER_ID  GROUP BY  W_TEAMS_BENUTZER.BENUTZER_ID ORDER BY DATUMZ ASC";
            /*Egal ob Termine frei, wird nachher geschaut*/
            $abfrage = "SELECT  '$datum_d' AS DATUM,  DATE_FORMAT('$datum_sql','%Y%m%d') AS DATUMZ, benutzername, W_TEAMS_BENUTZER.BENUTZER_ID,  TERMINE_TAG,  START_ADRESSE  FROM BENUTZER, `W_TEAMS_BENUTZER`, W_TEAM_PROFILE, GEO_TERMINE WHERE W_TEAM_PROFILE.$wochentag_nr='1' AND `TEAM_ID` = '$TEAM_ID' AND W_TEAMS_BENUTZER.AKTUELL = '1' AND W_TEAM_PROFILE.BENUTZER_ID=W_TEAMS_BENUTZER.BENUTZER_ID AND W_TEAM_PROFILE.AKTUELL='1' AND W_TEAM_PROFILE.AKTIV='1'  && W_TEAMS_BENUTZER.BENUTZER_ID NOT IN(SELECT BENUTZER_ID FROM URLAUB WHERE URLAUB.DATUM='$datum_sql' && URLAUB.AKTUELL='1') && BENUTZER.benutzer_id=W_TEAMS_BENUTZER.BENUTZER_ID  GROUP BY  W_TEAMS_BENUTZER.BENUTZER_ID ORDER BY DATUMZ ASC";
            $result = DB::select($abfrage1);
            if (!empty($result)) {
                foreach($result as $row) {
                    $b_id = $row['BENUTZER_ID'];
                    if (is_array(get_luecken_termine1($b_id, $datum_d))) {
                        $arr[$z] = $row;
                        $arr[$z]['LUECKEN'] = get_luecken_termine1($b_id, $datum_d);
                        $z++;
                    }

                }
            }

        }//end for

        if (isset($arr)) {
            $anz_v_termine = count($arr);
            if ($anz_v_termine < 3) {
                $tage += 15;
                $arr = $this->get_termin_arr1($TEAM_ID, $datum_df, $tage);
            } else {
                return $arr;
            }
        } else {
            if ($tage < 766) {
                $tage += 21;
                $arr = $this->get_termin_arr1($TEAM_ID, $datum_df, $tage);
            }
        }
        if (isset($arr)) {
            return $arr;
        }

    }

    function get_strecken_route($s_lon, $s_lat, $e_lon, $e_lat)
    {
        $url = "http://open.mapquestapi.com/directions/v0/route?outFormat=xml&from=$s_lon,$s_lat&to=$e_lon,$e_lat&callback=renderNarrative&unit=k&locale=de_DE";
        $xml = simplexml_load_file("$url");
        if (!$xml === FALSE) {
            $this->km = $xml->route->distance / 1;
            $this->zeit = $xml->route->formattedTime;
            $anz = count($xml->route->legs->leg->maneuvers->maneuver);
            #echo "ANZ $anz";
            for ($a = 0; $a < $anz; $a++) {
                $z = $a + 1;
                $gif = $xml->route->legs->leg->maneuvers->maneuver[$a]->iconUrl;
                if ($a < $anz - 1) {
                    echo "<img src=\"$gif\">";
                    echo "$z." . $xml->route->legs->leg->maneuvers->maneuver[$a]->narrative . "<br>";
                }
            }

        }
    }

    function get_gruppen_info($gruppen_id)
    {
        unset($this->gruppe_id);
        unset($this->gruppe);
        unset($this->team_id);
        $result = DB::select("SELECT * FROM W_GRUPPE WHERE GRUPPE_ID='$gruppen_id' && AKTUELL = '1' LIMIT 0,1");
        if (!empty($result)) {
            $row = $result[0];
            $this->gruppe_id = $row['GRUPPE_ID'];
            $this->gruppe = $row['GRUPPE'];
            $this->team_id = $row['TEAM_ID'];
            $this->get_wteam_info($this->team_id);
        }
    }

    function get_wteam_info($team_id)
    {
        unset($this->team_id);
        unset($this->team_bez);
        unset($this->team_benutzer_ids);
        $result = DB::select("SELECT * FROM W_TEAMS WHERE TEAM_ID='$team_id' && AKTUELL = '1' LIMIT 0,1");
        if (!empty($result)) {
            $row = $result[0];
            $this->team_id = $team_id;
            $this->team_bez = $row['TEAM_BEZ'];
            $this->team_benutzer_ids = $this->get_wteam_benutzer($team_id);
            if (is_array($this->team_benutzer_ids)) {
                $anz = count($this->team_benutzer_ids);
                for ($a = 0; $a < $anz; $a++) {
                    $benutzer_id = $this->team_benutzer_ids[$a]['BENUTZER_ID'];
                    $this->team_profile[$benutzer_id] = $this->get_wteam_profil($benutzer_id);
                }
            }
        }
    }

    /*Die nur an einem Tag können*/

    function get_wteam_benutzer_tag($team_id, $tag_nr = '1')
    {
        $result = DB::select("SELECT BENUTZER_ID FROM W_TEAMS_BENUTZER WHERE TEAM_ID='$team_id' && AKTUELL = '1'");
        return $result;
    }

    function check_status($tab, $tab_dat)
    {
        $result = DB::select("SELECT * FROM START_STOP WHERE TAB='$tab' && TAB_DAT='$tab_dat' && AKTUELL='1' ORDER BY S_DAT DESC LIMIT 0,1");
        if (empty($result)) {
            $this->status = 'nicht angefangen';
        } else {
            $row = $result[0];
            if ($row['START_TIME'] != NULL && $row['END_TIME'] != NULL && $row['UNTERBROCHEN'] == NULL) {
                $this->status = 'erledigt';
            }

            if ($row['START_TIME'] != NULL && $row['END_TIME'] == NULL && $row['UNTERBROCHEN'] == NULL) {
                $this->status = 'aktiv';
            }

            if ($row['UNTERBROCHEN'] == '1') {
                $this->status = 'unterbrochen';
            }
        }
    }

    function check_my_active($b_id, $tab)
    {
        $result = DB::select("SELECT * FROM START_STOP WHERE TAB='$tab' && BENUTZER_ID='$b_id' && AKTUELL='1' && END_TIME IS NULL && UNTERBROCHEN IS NULL ORDER BY S_DAT DESC LIMIT 0,1");
        if (!empty($result)) {
            return $result[0]['TAB_DAT'];
        } else {
            return false;
        }
    }

    function start_stop_protokoll($tab = '', $kw, $b_id)
    {
        if ($tab != '') {
            $result = DB::select("SELECT *, TIME_TO_SEC(TIMEDIFF(END_TIME, START_TIME))/60 AS DAUER_MIN, TIME_TO_SEC(TIMEDIFF(END_TIME, START_TIME))/3600 AS DAUER_STD FROM START_STOP WHERE TAB='$tab' && BENUTZER_ID='$b_id' && AKTUELL='1' ORDER BY START_TIME");
        } else {
            $result = DB::select("SELECT *, TIME_TO_SEC(TIMEDIFF(END_TIME, START_TIME))/60 AS DAUER_MIN, TIME_TO_SEC(TIMEDIFF(END_TIME, START_TIME))/3600 AS DAUER_STD FROM START_STOP WHERE BENUTZER_ID='$b_id' && AKTUELL='1' ORDER BY START_TIME");
        }
        if (!empty($result)) {
            echo "<table class=\"sortable\">";
            $bname = get_benutzername($b_id);
            echo "<tr><th colspan=\"4\">$bname</th></tr>";
            $z = 0;
            foreach($result as $row) {
                $z++;
                $table = $row['TAB'];
                $tab_dat = $row['TAB_DAT'];

                if ($table == 'GEO_TERMINE') {
                    $table = 'TERMIN';
                    $gg = new general();
                    $gg->get_termin_details($tab_dat);
                    $anschrift = $gg->anschrift;
                    $kontakt = $gg->kontakt;
                } else {
                    $anschrift = 'Keine Anschrift';
                    $kontakt = 'keine Kontaktdaten';
                }

                $start = $row['START_TIME'];
                $end = $row['END_TIME'];
                $dauer_min = number_format($row['DAUER_MIN'], 0);
                $dauer_std = min_in_zeit($dauer_min);
                $unterbrochen = $row['UNTERBROCHEN'];

                echo "<tr class=\"zeile$z\"><td>$anschrift<br>$kontakt</td><td>$start</td><td>$end</td><td>$dauer_min Min./ $dauer_std Std.</td><td>$unterbrochen</td></tr>";
                if ($z == 2) {
                    $z = 0;
                }
            }
            echo "</table>";
        }
    }

    function get_termin_details($dat)
    {
        $db_abfrage = "SELECT * FROM GEO_TERMINE WHERE AKTUELL='1' && DAT='$dat' LIMIT 0,1";
        $result = DB::select($db_abfrage);
        if (!empty($result)) {
            $row = $result[0];
            $this->t_datum = $row['DATUM'];
            $this->t_von = $row['VON'];
            $this->t_bis = $row['BIS'];
            $this->t_text = $row['TEXT'];
            $this->t_g_id = $row['GERAETE_ID'];
            $g_info = geraete_info_arr($this->t_g_id);
            extract($g_info[0]);
            $this->t_hersteller = $HERSTELLER;
            $this->t_bez = $BEZEICHNUNG;
            $this->t_kos_typ = $KOSTENTRAEGER_TYP;
            $this->t_kos_id = $KOSTENTRAEGER_ID;
            $this->t_lage_raum = $LAGE_RAUM;
            if ($this->t_kos_typ == 'Partner') {
                if (check_is_eigentuemer($this->t_kos_id)) {
                    $einheit_id = get_einheit_id($this->t_lage_raum);
                    $einheit_info = get_einheit_info($einheit_id);
                    extract($einheit_info);
                    $this->anschrift = "<b>EINHEIT:</b> $this->t_lage_raum<br><b>MIETER:</b>$MIETER<br>$HAUS_STRASSE $HAUS_NUMMER $HAUS_PLZ $HAUS_STADT";
                    $this->kontakt = kontaktdaten_anzeigen_mieter($this->t_lage_raum);
                } else {
                    $gg1 = new general();
                    $gg1->get_partner_info($this->t_kos_id);
                    $this->anschrift = "<b>KUNDE:</b>$gg1->partner_name<br>$gg1->partner_strasse $gg1->partner_hausnr $gg1->partner_plz $gg1->partner_ort";
                    $this->kontakt = kontaktdaten_anzeigen_kunde($this->t_kos_id);
                }
            }
        }

    }

    function pdf_protokoll($datum_d, $b_id)
    {
        $pdf = new Cezpdf('a4', 'portrait');
        $bpdf = new b_pdf;
        $termine_tag_arr = get_termine_tag_arr($b_id, $datum_d);
        $anz = count($termine_tag_arr);
        for ($a = 0; $a < $anz; $a++) {
            $termin_arr = $termine_tag_arr[$a];
            $this->pdf_protokoll_seite($pdf, $datum_d, $b_id, $termin_arr);
            if ($a < ($anz - 1)) {
                $pdf->ezNewPage();
            }
        }
        ob_clean(); //ausgabepuffer leeren
        header("Content-type: application/pdf");  // wird von MSIE ignoriert
        $pdf->ezStream();
    }

    function pdf_protokoll_seite(Cezpdf &$pdf, $datum_d, $b_id, $arr)
    {
        if (!is_array($arr)) {
            die('ABBRUCH KEINE TERMINDATEN');
        }

        $benutzername = get_benutzername($b_id);

        $von = $arr['VON'];
        $bis = $arr['BIS'];
        $kos_typ = $arr['KOSTENTRAEGER_TYP'];
        $kos_id = $arr['KOSTENTRAEGER_ID'];
        $text = $arr['TEXT'];
        $hinweis = $arr['HINWEIS'];
        $g_id = $arr['GERAETE_ID'];
        $rechnung_an = $arr['RECHNUNG_AN'];

        if ($text == 'x') {
            $text = '';
        }
        if ($hinweis == 'x') {
            $hinweis = '';
        }

        $g_info_arr = geraete_info_arr($g_id);
        $g_her = $g_info_arr[0]['HERSTELLER'];
        $g_bez = $g_info_arr[0]['BEZEICHNUNG'];
        $baujahr = $g_info_arr[0]['BAUJAHR'];
        $r_empf = $g_info_arr[0]['RECHNUNG_AN'];
        $lage_raum = umlaute_anpassen($g_info_arr[0]['LAGE_RAUM']);


        if (!check_is_eigentuemer($kos_id)) {
            $g_z = new general();
            $g_z->get_partner_info($kos_id);
            $name = umlaute_anpassen($g_z->partner_name);
            $anschrift = umlaute_anpassen("$g_z->partner_strasse $g_z->partner_hausnr, $g_z->partner_plz $g_z->partner_ort");
            $kontakt_info = ltrim(str_replace('\r', ' ', str_replace('\n', ' ', str_replace('<br />', ' ', str_replace('<br>', ' ', ltrim(rtrim(kontaktdaten_anzeigen_kunde($kos_id))))))));
            $wohnlage = finde_detail_inhalt('PARTNER_LIEFERANT', $kos_id, 'Wohnlage');
        } else {
            /*Mieterinformationen holen*/
            $gr = new general();
            $gr->get_lanlon_mieter($lage_raum);
            $name = str_replace('\n', ' ', ltrim(str_replace('<br>', ' ', rtrim(umlaute_anpassen("$gr->mieter_name")))));
            $anschrift = umlaute_anpassen($gr->mieter_bis_str);
            $kontakt_info = ltrim(rtrim(str_replace('\r', ' ', str_replace('\n', ' ', ltrim(str_replace('<br>', ' ', str_replace('<br />', ' ', umlaute_anpassen(kontaktdaten_anzeigen_mieter($lage_raum, 0)))))))));
            $einheit_id = get_einheit_id(ltrim(rtrim($lage_raum)));
            $einheit_info_arr = get_einheit_info($einheit_id);
            $wohnlage = $einheit_info_arr['EINHEIT_LAGE'] . $g_id;

        }


        $datum_sql = date_german2mysql($datum_d);

        $text_schrift = '../' . BERLUS_PATH . '/pdfclass/fonts/Helvetica.afm';
        $pdf->selectFont($text_schrift);

        $pdf->ezSetMargins(135, 70, 50, 50);
        $pdf->setLineStyle(0.5);
        $pdf->ezText("<b>Kundendienst/Wartungsbericht</b>", 14, array('left' => '0'));
        $pdf->ezSetDy(-20); //abstand
        $wt = get_wochentag_name($datum_d);
        $pdf->addText(50, $pdf->y, 10, "Termin Datum/Uhrzeit:");
        $pdf->addText(180, $pdf->y, 10, "$wt, $datum_d $von Uhr bis $bis Uhr");
        $pdf->ezSetDy(-15); //abstand
        $pdf->addText(50, $pdf->y, 10, "Name:");

        $pdf->addText(180, $pdf->y, 10, "$name");
        $pdf->ezSetDy(-15); //abstand
        $pdf->addText(50, $pdf->y, 10, "Anschrift:");
        $pdf->addText(180, $pdf->y, 10, "$anschrift");
        $pdf->ezSetDy(-15); //abstand
        $pdf->addText(50, $pdf->y, 10, "Einbauort/Mieternummer:");
        $pdf->addText(350, $pdf->y, 10, "Wohnlage:");
        $pdf->addText(400, $pdf->y, 10, "$wohnlage");
        $lage_raum_a = umlaute_anpassen($lage_raum);
        $pdf->addText(180, $pdf->y, 10, "$lage_raum_a");
        $pdf->ezSetDy(-15); //abstand
        $pdf->addText(50, $pdf->y, 10, "Kontakt");
        $pdf->ezSetMargins(135, 70, 180, 200);
        $pdf->ezSetDy(10);
        $pdf->ezText("$kontakt_info", 10);
        $pdf->ezSetMargins(135, 70, 50, 50);
        $pdf->ezSetDy(-6); //abstand
        $pdf->addText(50, $pdf->y, 10, "------------------------------------------------------------------------------------------------------------------------------------------------------");
        if (!empty($rechnung_an)) {
            $rechnung_an = str_replace('<BR>', ' ', $rechnung_an);
            $pdf->ezText("<b>Rechnung an: $rechnung_an</b>", 10);
            $pdf->addText(50, $pdf->y - 10, 10, "------------------------------------------------------------------------------------------------------------------------------------------------------");
            $pdf->ezSetDy(-6); //abstand
        }
        if (!empty($hinweis) or !empty($text)) {
            $text = umlaute_anpassen($text);
            $hinweis = umlaute_anpassen($hinweis);
            $pdf->ezText("$text $hinweis", 10);
        }
        $pdf->ezSetDy(-10); //abstand
        $pdf->addText(50, $pdf->y, 10, "Grund/Fehlerbeschreibung: Thermenwartung");
        $pdf->ezSetDy(-15); //abstand
        $pdf->line(50, $pdf->y - 1, 550, $pdf->y - 1);
        $pdf->ezSetDy(-15); //abstand
        $pdf->addText(50, $pdf->y, 10, "Geraet: $g_her $g_bez");
        $pdf->line(85, $pdf->y - 1, 400, $pdf->y - 1);
        $pdf->addText(405, $pdf->y, 10, "Baujahr: $baujahr");
        $pdf->line(445, $pdf->y - 1, 550, $pdf->y - 1);
        $pdf->ezSetDy(-25); //abstand
        $pdf->addText(50, $pdf->y, 10, "Folgende Arbeiten wurden heute durchgefuehrt:");

        $pdf->ellipse(65, $pdf->y - 15, 5);
        $pdf->ezSetDy(-18); //abstand
        $pdf->addText(80, $pdf->y, 10, "Wartung der Anlage mit Pruefung und Reinigung von Waermeblock und Brenner");

        $pdf->ellipse(65, $pdf->y - 15, 5);
        $pdf->ezSetDy(-18); //abstand
        $pdf->addText(80, $pdf->y, 10, "Ueberpruefung der Abgaswege und der Einstellung der Gasmenge");

        $pdf->ellipse(65, $pdf->y - 15, 5);
        $pdf->ezSetDy(-18); //abstand
        $pdf->addText(80, $pdf->y, 10, "Dichtheitspruefung an wasser- und gasfuehrenden Bauteilen");

        $pdf->ellipse(65, $pdf->y - 15, 5);
        $pdf->ezSetDy(-18); //abstand
        $pdf->addText(80, $pdf->y, 10, "Ueberpruefung der Zuendelektroden, Ueberwachungselektroden, Regeleinrichtungen und");
        $pdf->ezSetDy(-10); //abstand
        $pdf->addText(80, $pdf->y, 10, "Sicherheitseinrichtungen");

        $pdf->ellipse(65, $pdf->y - 15, 5);
        $pdf->ezSetDy(-18); //abstand
        $pdf->addText(80, $pdf->y, 10, "Funktionspruefung des Geraetes und der Abgasueberwachungseinrichtung");

        $pdf->ellipse(65, $pdf->y - 15, 5);
        $pdf->ezSetDy(-18); //abstand
        $pdf->addText(80, $pdf->y, 10, "<i><b>Wasserdruck geprueft / Wasser aufgefuellt</b></i>");

        $pdf->ellipse(65, $pdf->y - 15, 5);
        $pdf->ezSetDy(-18); //abstand
        $pdf->addText(80, $pdf->y, 10, "Brennerdichtung gewechselt");


        $pdf->ezSetDy(-25); //abstand
        $pdf->line(50, $pdf->y - 1, 550, $pdf->y - 1);
        $pdf->ezSetDy(-25); //abstand
        $pdf->line(50, $pdf->y - 1, 550, $pdf->y - 1);
        $pdf->ezSetDy(-25); //abstand
        $pdf->line(50, $pdf->y - 1, 550, $pdf->y - 1);

        $pdf->ezSetDy(-25); //abstand
        $pdf->addText(50, $pdf->y, 10, "Anzahl");
        $pdf->addText(100, $pdf->y, 10, "Material");
        $pdf->ezSetDy(-25); //abstand
        $pdf->line(50, $pdf->y - 1, 80, $pdf->y - 1);
        $pdf->line(100, $pdf->y - 1, 550, $pdf->y - 1);

        $pdf->ezSetDy(-25); //abstand
        $pdf->line(50, $pdf->y - 1, 80, $pdf->y - 1);
        $pdf->line(100, $pdf->y - 1, 550, $pdf->y - 1);

        $pdf->ezSetDy(-30); //abstand
        $pdf->line(50, $pdf->y - 1, 550, $pdf->y - 1);

        $pdf->ezSetDy(-40); //abstand
        $pdf->addText(50, $pdf->y, 8, "Ankunft:");
        $pdf->addText(220, $pdf->y, 8, "Abfahrt:");
        $pdf->addText(380, $pdf->y, 8, "<b>Kein Bargeldverkehr, Sie erhalten eine Rechnung.</b>");
        $pdf->line(50, $pdf->y - 1, 200, $pdf->y - 1);
        $pdf->line(220, $pdf->y - 1, 360, $pdf->y - 1);
        $pdf->ezSetDy(-40); //abstand
        $pdf->addText(50, $pdf->y, 10, "Datum");
        $pdf->line(95, $pdf->y - 1, 200, $pdf->y - 1);
        $pdf->addText(220, $pdf->y - 10, 10, "             Monteur");
        $pdf->line(220, $pdf->y - 1, 360, $pdf->y - 1);
        $pdf->addText(400, $pdf->y - 10, 10, "        Auftraggeber/Kunde");
        $pdf->line(400, $pdf->y - 1, 550, $pdf->y - 1);

        $pdf->ezSetDy(-10); //abstand
        $pdf->addText(220, $pdf->y - 10, 10, "           Unterschrift");
        $pdf->addText(400, $pdf->y - 10, 10, "        Unterschrift");
        return $pdf;
    }

    function erstelle_brief_vorlage(&$pdf, $v_dat, $empf_typ, $empf_id_arr, $option = '0')
    {

        $anz_empf = count($empf_id_arr);
        if ($anz_empf > 0) {
            $this->get_texte($v_dat);


            for ($index = 0; $index < sizeof($empf_id_arr); $index++) {
                if ($index > 0) {
                    $pdf->ezNewPage();
                }


                $pdf_einzeln = new Cezpdf('a4', 'portrait');
                $bpdf->b_header($pdf_einzeln, 'Partner', session()->get('partner_id'), 'portrait', 'Helvetica.afm', 6);
                $pdf_einzeln->ezStopPageNumbers(); //seitennummerirung beenden

                /*Faltlinie*/
                $pdf->setLineStyle(0.2);
                $pdf_einzeln->setLineStyle(0.2);
                $pdf->line(5, 542, 20, 542);
                $pdf_einzeln->line(5, 542, 20, 542);

                $mv_id = $empf_id_arr[$index];
                $mv = new mietvertraege;

                $mv->get_mietvertrag_infos_aktuell($mv_id);
                $pdf->ezText("$mv->personen_name_string_u\n$mv->haus_strasse $mv->haus_nr\n\n$mv->haus_plz $mv->haus_stadt", 12);
                $pdf_einzeln->ezText("$mv->personen_name_string_u\n$mv->haus_strasse $mv->haus_nr\n\n$mv->haus_plz $mv->haus_stadt", 12);
                $pdf->ezSetDy(-60);
                $pdf_einzeln->ezSetDy(-80);
                $datum_heute = date("d.m.Y");
                $p = new partners;
                $p->get_partner_info(session()->get('partner_id'));

                $pdf->ezText("$p->partner_ort, $datum_heute", 12, array('justification' => 'right'));
                $pdf->ezText("<b>Objekt: $mv->haus_strasse $mv->haus_nr, $mv->haus_plz $mv->haus_stadt</b>", 12);
                $pdf->ezText("<b>Einheit: $mv->einheit_kurzname</b>", 12);
                $pdf->ezText("<b>$this->v_kurztext</b>", 12);
                $pdf->ezSetDy(-30);
                $pdf->ezText("$mv->mv_anrede", 12);
                $meine_var{$this->v_text} = $this->v_text;
                $pdf->ezText("$this->v_text", 12, array('justification' => 'full'));

                $pdf_einzeln->ezText("$p->partner_ort, $datum_heute", 12, array('justification' => 'right'));
                $pdf_einzeln->ezText("<b>Objekt: $mv->haus_strasse $mv->haus_nr, $mv->haus_plz $mv->haus_stadt</b>", 12);
                $pdf_einzeln->ezText("<b>Einheit: $mv->einheit_kurzname</b>", 12);
                $pdf_einzeln->ezText("<b>$this->v_kurztext</b>", 12);
                $pdf_einzeln->ezSetDy(-30);
                $pdf_einzeln->ezText("$mv->mv_anrede", 12);
                $pdf_einzeln->ezText("$this->v_text", 12, array('justification' => 'full'));

                $this->pdf_speichern("SERIENBRIEFE/" . session()->get('username'), "$mv->einheit_kurzname - $this->v_kurztext vom $datum_heute" . '.pdf', $pdf_einzeln->output());

            }

            /*erste packen und gz erstellen*/
            $dir = 'SERIENBRIEFE';
            $tar_dir_name = "$dir/" . session()->get('username');

            if (!file_exists($tar_dir_name)) {
                mkdir($tar_dir_name, 0777);
            }


            exec("tar cfvz $tar_dir_name/Serienbrief.tar.gz $tar_dir_name/*.pdf");
            exec("rm $tar_dir_name/*.pdf");

            if (request()->has('emailsend')) {
                /*Als Email versenden*/
                $from = 'serienbrief@berlus.de';
                $to = 'info@berlus.de';
                $DATEI = "$tar_dir_name/Serienbrief.tar.gz";
                $content_type = 'application/x-tar';
                $subject = "Serienbriefe $this->v_kurztext vom $datum_heute - " . session()->get('username');

// Do not change anything from here

                $random_hash = md5(date('r', time()));

                $headers = "From: " . $from . "\r\nReply-To: " . $from;
                $headers .= "\r\nContent-Type: multipart/mixed; boundary=\"PHP-mixed-" . $random_hash . "\"";
                $attachment = chunk_split(base64_encode(file_get_contents($DATEI)));

                $message = "--PHP-mixed-" . $random_hash . "\n" .
                    "Content-Type: multipart/alternative; boundary=\"PHP-alt-" . $random_hash . "\"\n\n" .
                    "--PHP-alt-" . $random_hash . "\n" .
                    "Content-Type: text/plain; charset=\"iso-8859-1\"\n" .
                    "Content-Transfer-Encoding: 7bit\n\n" .
                    "Serienbriefe im Anhang.\n" .
                    "\n\n" .
                    "--PHP-alt-" . $random_hash . "--\n\n" .
                    "--PHP-mixed-" . $random_hash . "\n" .
                    "Content-Type: " . $content_type . "; name=\"$this->v_kurztext vom $datum_heute.tar.gz\"\n" .
                    "Content-Transfer-Encoding: base64\n" .
                    "Content-Disposition: attachment\n\n" .
                    $attachment . "\n" .
                    "--PHP-mixed-" . $random_hash . "--\n\n";

                /*Wenn Email versendet, dann PDF ANZEIGEN, sonst die(fehler)";*/
                if (@mail($to, $subject, $message, $headers)) {
                    exec("rm $tar_dir_name/Serienbrief.tar.gz");
                    /*Ausgabe*/
                    ob_clean(); //ausgabepuffer leeren
                    header("Content-type: application/pdf");  // wird von MSIE ignoriert
                    $pdf->ezStream();
                }
                /*das Raus*/
                ob_clean(); //ausgabepuffer leeren
                header("Content-type: application/pdf");  // wird von MSIE ignoriert
                $pdf->ezStream();

            } else { //emalsend
                /*Kein Emailversand angefordert, nur ansehen*/
                /*Ausgabe*/
                ob_clean(); //ausgabepuffer leeren
                header("Content-type: application/pdf");  // wird von MSIE ignoriert
                $pdf->ezStream();
            }
        } else {
            die('Keine Empfänger gewählt');
        }

    }

    function pdf_einwurfzettel($art = 'Mieter', $art_id = '1', $vorlagen_id = 15)
    {
        $pdf = new Cezpdf('a4', 'portrait');
        $text_schrift = '../' . BERLUS_PATH . '/pdfclass/fonts/Helvetica.afm';
        $bpdf = new b_pdf();
        session()->put('partner_id', 1);
        $logo_file = '../' . BERLUS_PATH . "/print_css/Partner/1_logo.png";
        $bpdf->b_header($pdf, 'Partner', 1, 'portrait', $text_schrift, 6, $logo_file);
        $pdf->selectFont($text_schrift);
        $bpdf->get_texte($vorlagen_id);


        if ($art == 'Mieter') {
            $einheit_id = $art_id;

            $mv_id = get_last_mietvertrag_id($einheit_id);
            if ($mv_id) {

                $mv = new mietvertraege();
                $mv->get_mietvertrag_infos_aktuell($mv_id);

                $pdf->ezText("$mv->personen_name_string_u\n$mv->haus_strasse $mv->haus_nr\n\n$mv->haus_plz $mv->haus_stadt", 10);
                $pdf->ezSetDy(-60);


                $datum_heute = date("d.m.Y");
                $p = new partners;
                $p->get_partner_info(session()->get('partner_id'));

                $pdf->ezText("$p->partner_ort, $datum_heute", 12, array('justification' => 'right'));
                $pdf->ezText("<b>Objekt: $mv->haus_strasse $mv->haus_nr, $mv->haus_plz $mv->haus_stadt</b>", 12);
                $pdf->ezText("<b>Einheit: $mv->einheit_kurzname</b>", 12);
            } else {
                $pdf->ezTEXT("LEERSTAND\n\n", 30);

                $ee = new einheit();
                $ee->get_einheit_info($einheit_id);
                $bpdf->v_kurztext = 'LEERSTAND ' . $ee->einheit_kurzname . "\n" . $ee->haus_strasse . " " . $ee->haus_nummer . "\nLage:" . $ee->einheit_lage;
                $bpdf->v_text = 'Beim Leerstand warten wir bei Neuvermietung!';
            }
        }

        if ($art == 'Partner') {
            $partner_id = $art_id;
            echo "$art $art_id";
            $pp = new general();
            $pp->get_partner_info($partner_id);

            $pdf->ezText("$pp->partner_name\n$pp->partner_strasse $pp->partner_hausnr\n\n$pp->partner_plz $pp->partner_ort", 10);
            $pdf->ezSetDy(-60);
            $datum_heute = date("d.m.Y");
            $p = new partners;
            $p->get_partner_info(session()->get('partner_id'));
            $pdf->ezText("$p->partner_ort, $datum_heute", 12, array('justification' => 'right'));

        }


        /*Faltlinie*/
        $pdf->setLineStyle(0.2);
        $pdf->line(5, 542, 20, 542);


        $pdf->ezText("<b>$bpdf->v_kurztext</b>", 12);
        $pdf->ezSetDy(-30);
        if (isset($mv->mv_anrede)) {
            $pdf->ezText("$mv->mv_anrede", 12);
        } else {
            $pdf->ezText("Sehr geehrte Damen und Herren,\n", 12);
        }

        $meine_var{$bpdf->v_text} = $bpdf->v_text;
        $pdf->ezText("$bpdf->v_text", 12, array('justification' => 'full'));

        /*Ausgabe*/
        ob_clean();
        header("Content-type: application/pdf");  // wird von MSIE ignoriert
        $pdf->ezStream();


    }

    function update_profil($b_id, $spalte, $wert)
    {
        DB::update("UPDATE W_TEAM_PROFILE SET `$spalte`='$wert' WHERE BENUTZER_ID='$b_id'");
        echo "<p class=\"zeile_hinweis_rot\">Profil geändert</p>";
    }


}//end class general