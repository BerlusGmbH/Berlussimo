<?php

class wirt_e
{
    public $g_qm_gewerbe_a;
    public $g_qm_a;
    public $g_qm;
    public $g_qm_gewerbe;
    public $w_name;
    public $anzahl_e;
    public $anzahl_wo;
    public $anzahl_ge;
    public $g_mea;
    public $g_anzahl_einheiten;
    public $g_einheit_qm;
    public $g_verbrauch;
    public $anzahl_einheiten;

    function check_einheit_in_we($einheit_id, $w_id)
    {
        $db_abfrage = "SELECT * FROM `WIRT_EIN_TAB` WHERE `id` ='$w_id' AND `EINHEIT_ID` ='$einheit_id' AND `AKTUELL` = '1' LIMIT 0 , 1";
        $result = DB::select($db_abfrage);
        return !empty($result);
    }

    function get_id_from_wirte($w_name)
    {
        $db_abfrage = "SELECT id AS W_ID  FROM `WIRT_EINHEITEN` WHERE  WIRT_EINHEITEN.AKTUELL='1' &&  WIRT_EINHEITEN.W_NAME='$w_name' ORDER BY W_DAT DESC LIMIT 0,1 ";
        $result = DB::select($db_abfrage);
        if (!empty($result)) {
            $row = $result[0];
            return $row ['W_ID'];
        }
    }

    function dropdown_we($label, $name, $id, $js_action, $vorwahl = null)
    {
        echo "<label for=\"$id\">$label</label><select name=\"$name\" id=\"$id\" size=1 $js_action>\n";
        echo "<option value=\"\">Bitte wählen</option>\n";

        $wirt_e_arr = $this->get_wirt_e_arr();
        $anzahl = count($wirt_e_arr);
        for ($a = 0; $a < $anzahl; $a++) {
            $w_id = $wirt_e_arr [$a] ['W_ID'];
            $w_name = $wirt_e_arr [$a] ['W_NAME'];
            if ($vorwahl == null) {
                echo "<option value=\"$w_id\">$w_name</option>\n";
            } else {
                if ($vorwahl == $w_id) {
                    echo "<option value=\"$w_id\" selected>$w_name</option>\n";
                } else {
                    echo "<option value=\"$w_id\">$w_name</option>\n";
                }
            }
        }

        echo "</select>\n";
    }

    function get_wirt_e_arr()
    {
        $db_abfrage = "SELECT id AS W_ID, W_NAME FROM `WIRT_EINHEITEN` WHERE `AKTUELL`='1' ORDER BY W_NAME ASC";
        $result = DB::select($db_abfrage);
        if (!empty($result)) {
            return $result;
        }
    }

    function form_new_we()
    {
        $f = new formular ();
        $f->erstelle_formular("Neue Wirtschaftseinheit erstellen", NULL);
        $f->text_feld("Name der Wirtschafseinheit", "w_name", "", "50", 'w_name', '');
        $f->hidden_feld("option", "new_we");
        $f->send_button("submit_we", "Erstellen");
        $f->ende_formular();
    }

    function neue_we_speichern($w_name)
    {
        $bk = new bk ();
        $last_w_id = $bk->last_id('WIRT_EINHEITEN', 'id') + 1;

        $db_abfrage = "INSERT INTO WIRT_EINHEITEN VALUES (NULL, '$last_w_id', '$w_name',  '1')";
        DB::insert($db_abfrage);
    }

    function form_einheit_hinzu($w_id)
    {
        echo "<table><tr><td>";
        $this->liste_einh_in($w_id);
        echo "</td><td>";
        $f = new formular ();
        $f->erstelle_formular("Vorauswahl / Einheiten aus ...", NULL);
        $link_o = "<a href='" . route('web::bk::legacy', ['option' => 'wirt_einheiten_hinzu', 'w_id' => $w_id, 'anzeigen' => 'objekt']) . "'>Objekt</a>";
        $link_h = "<a href='" . route('web::bk::legacy', ['option' => 'wirt_einheiten_hinzu', 'w_id' => $w_id, 'anzeigen' => 'haus']) . "'>Häuser</a>";
        $link_e = "<a href='" . route('web::bk::legacy', ['option' => 'wirt_einheiten_hinzu', 'w_id' => $w_id, 'anzeigen' => 'einheit']) . "'>Einheiten</a>";
        echo "$link_o<br>";
        echo "$link_h<br>";
        echo "$link_e<br>";
        $f->ende_formular();
        echo "</td><td>";
        $f = new formular ();
        $f->erstelle_formular("Bitte wählen", NULL);
        $anzeigen = request()->input('anzeigen');
        // echo $anzeigen;
        if ($anzeigen == 'objekt') {
            $o = new objekt ();
            $o_array = $o->liste_aller_objekte();
            $anzahl = count($o_array);
            echo "<SELECT SIZE=\"10\" NAME=\"IMPORT_AUS\">";
            for ($a = 0; $a < $anzahl; $a++) {
                $objekt_n = $o_array [$a] ['OBJEKT_KURZNAME'];
                $objekt_id = $o_array [$a] ['OBJEKT_ID'];
                if ($a == 0) {
                    echo "<OPTION  value='$objekt_id' selected>$objekt_n</OPTION>";
                } else {
                    echo "<OPTION  value='$objekt_id'>$objekt_n</OPTION>";
                }
            }
            echo "</SELECT>";
            $f->hidden_feld("anzeigen", "$anzeigen");
            $f->send_button("submit_we", "Übernehmen");
        }
        if ($anzeigen == 'haus') {
            $h = new haus ();
            $h_array = $h->liste_aller_haeuser();
            $anzahl = count($h_array);
            echo "<SELECT SIZE=\"10\" NAME=\"IMPORT_AUS\">";
            for ($a = 0; $a < $anzahl; $a++) {
                $haus_n = $h_array [$a] ['HAUS_STRASSE'] . $h_array [$a] ['HAUS_NUMMER'];
                $haus_id = $h_array [$a] ['HAUS_ID'];
                if ($a == 0) {
                    echo "<OPTION  value=\"$haus_id\" selected>$haus_n</OPTION>";
                } else {
                    echo "<OPTION  value=\"$haus_id\">$haus_n</OPTION>";
                }
            }
            echo "</SELECT>";
            $f->hidden_feld("anzeigen", "$anzeigen");
            $f->send_button("submit_we", "Übernehmen");
        }
        if ($anzeigen == 'einheit') {
            $e_array = $this->liste_aller_einheiten($w_id);
            $anzahl = count($e_array);
            echo "<SELECT SIZE=\"10\" NAME=\"IMPORT_AUS\">";
            for ($a = 0; $a < $anzahl; $a++) {
                $ein_id = $e_array [$a] ['EINHEIT_ID'];
                $ein_n = $e_array [$a] ['EINHEIT_KURZNAME'];
                if ($a == 0) {
                    echo "<OPTION value=\"$ein_id\" selected>$ein_n</OPTION>";
                } else {
                    echo "<OPTION value=\"$ein_id\">$ein_n</OPTION>";
                }
            }
            echo "</SELECT>";
            $f->hidden_feld("anzeigen", "$anzeigen");

            $f->send_button("submit_we", "Übernehmen");
        }
        $f->hidden_feld("anzeigen", "$anzeigen");
        $f->hidden_feld("option", "wirt_hinzu");
        $f->ende_formular();
        echo "</td></tr>";
        echo "</table>";
        // }
    }

    function liste_einh_in($w_id)
    {
        $f = new formular ();
        $this->get_wirt_e_infos($w_id);
        $f->erstelle_formular("Liste der Einheiten in $this->w_name", NULL);
        $einheiten_arr = $this->get_einheiten_from_wirte($w_id);
        $anzahl = count($einheiten_arr);
        echo "<div class='row'>
                <div class='col-xs-8'>";
        if ($anzahl) {
            echo "<SELECT class='browser-default secondary' style='height: 200px' NAME='IMPORT_AUS[]' multiple>";
            for ($a = 0; $a < $anzahl; $a++) {
                $e_id = $einheiten_arr [$a] ['EINHEIT_ID'];
                $e_name = $einheiten_arr [$a] ['EINHEIT_KURZNAME'];
                echo "<OPTION value='$e_id'>$e_name</OPTION>";
            }
            echo "</SELECT>";
            echo "</div><div class='col-xs-4'>";
            echo "<p>Einheiten: $this->anzahl_e</p>";
            echo "<p>QM: $this->g_qm m²</p>";
            echo "<p>Gew.: $this->g_qm_gewerbe m²</p>";
            echo "</div></div>";
            $f->send_button("submit_del", "Auswahl Entfernen");
            echo "&nbsp;";
            $f->send_button("submit_del_all", "Alle Entfernen");
            //echo "</div></div>";
        } else {
            echo "Keine Einheiten in der Wirtschafseinheit $this->w_name";
        }
        $f->hidden_feld("option", "wirt_delete");
        $f->ende_formular();
    }

    function get_wirt_e_infos($w_id)
    {
        $this->w_name = '';
        $this->g_qm = '0.00';
        $this->g_qm_gewerbe = '0.00';
        $db_abfrage = "SELECT W_NAME FROM `WIRT_EINHEITEN`  WHERE  WIRT_EINHEITEN.AKTUELL='1' && WIRT_EINHEITEN.id='$w_id' LIMIT 0,1";
        $result = DB::select($db_abfrage);
        if (!empty($result)) {
            $row = $result[0];
            $this->w_name = $row ['W_NAME'];
            $this->g_qm = $this->get_qm_from_wirte($w_id);
            $this->g_qm_gewerbe = $this->get_qm_gewerb_from_wirte($w_id);
            $this->anzahl_e = $this->get_anzahl_e($w_id);
            $this->anzahl_ge = $this->get_anzahl_einheiten_from_wirte($w_id, 'Gewerbe');
            $this->anzahl_wo = $this->anzahl_e - $this->anzahl_ge;

            /* NEU aus class WEG Function ->key_daten_formel */
            $d = new detail ();
            $anteile_g = $d->finde_detail_inhalt('Wirtschaftseinheit', $w_id, 'Gesamtanteile');
            if (empty ($anteile_g)) {
                $einheiten = $this->get_einheiten_from_wirte($w_id);
                $anteile_g = 0.0;
                foreach ($einheiten as $einheit) {
                    $anteil_e = $d->finde_detail_inhalt('Einheit', $einheit['EINHEIT_ID'], 'WEG-Anteile');
                    if (!empty ($anteil_e)) {
                        $anteile_g += nummer_komma2punkt($anteil_e);
                    }
                }
            }
            $this->g_mea = $anteile_g;
            $this->g_einheit_qm = $this->g_qm;
            $this->g_anzahl_einheiten = $this->anzahl_e;
            $this->g_verbrauch = '0.00';
        }
    }

    function get_qm_from_wirte($w_id)
    {
        $db_abfrage = "SELECT SUM(EINHEIT_QM) AS G_QM  FROM `WIRT_EINHEITEN` JOIN WIRT_EIN_TAB ON (WIRT_EINHEITEN.id=WIRT_EIN_TAB.W_ID) JOIN EINHEIT ON (EINHEIT.id=WIRT_EIN_TAB.EINHEIT_ID) JOIN HAUS ON (HAUS.id=EINHEIT.HAUS_ID) WHERE  WIRT_EINHEITEN.AKTUELL='1' && WIRT_EIN_TAB.AKTUELL='1' && WIRT_EINHEITEN.id='$w_id' && EINHEIT.EINHEIT_AKTUELL='1' && HAUS_AKTUELL='1' ";
        $result = DB::select($db_abfrage);
        if (!empty($result)) {
            $row = $result[0];
            return $row ['G_QM'];
        }
    }

    function get_qm_gewerb_from_wirte($w_id)
    {
        $db_abfrage = "SELECT SUM(EINHEIT_QM) AS G_QM  FROM `WIRT_EINHEITEN` JOIN WIRT_EIN_TAB ON (WIRT_EINHEITEN.id=WIRT_EIN_TAB.W_ID) JOIN EINHEIT ON (EINHEIT.id=WIRT_EIN_TAB.EINHEIT_ID) JOIN HAUS ON (HAUS.id=EINHEIT.HAUS_ID) WHERE  WIRT_EINHEITEN.AKTUELL='1' && EINHEIT.TYP='Gewerbe' && WIRT_EIN_TAB.AKTUELL='1' && WIRT_EINHEITEN.id='$w_id' && EINHEIT.EINHEIT_AKTUELL='1' && HAUS_AKTUELL='1'";
        $result = DB::select($db_abfrage);
        if (!empty($result)) {
            $row = $result[0];
            return $row ['G_QM'];
        }
    }

    function get_anzahl_e($w_id)
    {
        $db_abfrage = "SELECT WZ_DAT FROM WIRT_EIN_TAB WHERE AKTUELL='1' && W_ID='$w_id'";
        $result = DB::select($db_abfrage);
        return count($result);
    }

    function get_anzahl_einheiten_from_wirte($w_id, $typ)
    {
        $db_abfrage = "SELECT * FROM `WIRT_EINHEITEN` JOIN WIRT_EIN_TAB ON (WIRT_EINHEITEN.id=WIRT_EIN_TAB.W_ID) JOIN EINHEIT ON (EINHEIT.id=WIRT_EIN_TAB.EINHEIT_ID) JOIN HAUS ON (HAUS.id=EINHEIT.HAUS_ID) WHERE  WIRT_EINHEITEN.AKTUELL='1' && WIRT_EIN_TAB.AKTUELL='1' && WIRT_EINHEITEN.id='$w_id' && EINHEIT.EINHEIT_AKTUELL='1' && HAUS_AKTUELL='1' && EINHEIT.TYP='$typ' ORDER BY EINHEIT.EINHEIT_KURZNAME ASC";
        $result = DB::select($db_abfrage);
        return count($result);
    }

    function get_einheiten_from_wirte($w_id)
    {
        $db_abfrage = "SELECT EINHEIT.*, EINHEIT.id AS EINHEIT_ID, HAUS.*, HAUS.id AS HAUS_ID FROM `WIRT_EINHEITEN` JOIN WIRT_EIN_TAB ON (WIRT_EINHEITEN.id=WIRT_EIN_TAB.W_ID) JOIN EINHEIT ON (EINHEIT.id=WIRT_EIN_TAB.EINHEIT_ID) JOIN HAUS ON (HAUS.id=EINHEIT.HAUS_ID) WHERE  WIRT_EINHEITEN.AKTUELL='1' && WIRT_EIN_TAB.AKTUELL='1' && WIRT_EINHEITEN.id='$w_id' && EINHEIT.EINHEIT_AKTUELL='1' && HAUS_AKTUELL='1' ORDER BY LENGTH(EINHEIT.EINHEIT_KURZNAME) ASC, EINHEIT.EINHEIT_KURZNAME ASC";
        $result = DB::select($db_abfrage);
        if (!empty($result)) {
            return $result;
        }
    }

    function liste_aller_einheiten($w_id)
    {
        $result = DB::select("SELECT EINHEIT.id , EINHEIT.id AS EINHEIT_ID, EINHEIT_KURZNAME  FROM EINHEIT WHERE EINHEIT_AKTUELL='1' && EINHEIT.id NOT IN (SELECT EINHEIT_ID FROM WIRT_EIN_TAB WHERE AKTUELL='1' && W_ID='$w_id') GROUP BY EINHEIT.id ORDER BY EINHEIT_KURZNAME ASC");
        foreach ($result as $row)
            $einheiten_array [] = $row;
        $this->anzahl_einheiten = count($einheiten_array);
        return $einheiten_array;
    }

    function einheit2_wirt($w_id, $import_aus, $anzeigen)
    {
        echo $import_aus . $anzeigen;
        $bk = new bk ();

        if ($anzeigen == 'einheit') {
            $last_wtab_id = $bk->last_id('WIRT_EIN_TAB', 'WZ_ID') + 1;
            $db_abfrage = "INSERT INTO WIRT_EIN_TAB VALUES (NULL, '$last_wtab_id', '$w_id', '$import_aus', '1')";
            DB::insert($db_abfrage);
        }

        if ($anzeigen == 'haus') {
            $einheiten_arr = $this->liste_aller_einheiten_haus($w_id, $import_aus);
            $anzahl = count($einheiten_arr);
            for ($a = 0; $a < $anzahl; $a++) {
                $ein_id = $einheiten_arr [$a] ['EINHEIT_ID'];
                $last_wtab_id = $bk->last_id('WIRT_EIN_TAB', 'WZ_ID') + 1;
                $db_abfrage = "INSERT INTO WIRT_EIN_TAB VALUES (NULL, '$last_wtab_id', '$w_id', '$ein_id', '1')";
                DB::insert($db_abfrage);
            }
        }

        if ($anzeigen == 'objekt') {
            $einheiten_arr = $this->liste_aller_einheiten_objekt($w_id, $import_aus);
            $anzahl = count($einheiten_arr);
            for ($a = 0; $a < $anzahl; $a++) {
                $ein_id = $einheiten_arr [$a] ['EINHEIT_ID'];
                $last_wtab_id = $bk->last_id('WIRT_EIN_TAB', 'WZ_ID') + 1;
                $db_abfrage = "INSERT INTO WIRT_EIN_TAB VALUES (NULL, '$last_wtab_id', '$w_id', '$ein_id', '1')";
                DB::select($db_abfrage);
            }
        }

        header("Location: " . route('web::bk::legacy', ['option' => 'wirt_einheiten_hinzu', 'w_id' => $w_id, 'anzeigen' => $anzeigen], false));
    }

    function liste_aller_einheiten_haus($w_id, $haus_id)
    {
        $result = DB::select("SELECT EINHEIT.id, EINHEIT.id AS EINHEIT_ID, EINHEIT_KURZNAME  FROM EINHEIT WHERE HAUS_ID='$haus_id' && EINHEIT_AKTUELL='1' && EINHEIT_ID NOT IN (SELECT EINHEIT_ID FROM WIRT_EIN_TAB WHERE AKTUELL='1' && W_ID='$w_id') GROUP BY EINHEIT_ID ORDER BY EINHEIT_KURZNAME ASC");
        foreach ($result as $row)
            $einheiten_array [] = $row;
        $this->anzahl_einheiten = count($einheiten_array);
        return $einheiten_array;
    }

    function liste_aller_einheiten_objekt($w_id, $objekt_id)
    {
        $result = DB::select("SELECT EINHEIT.id, EINHEIT.id AS EINHEIT_ID, EINHEIT_KURZNAME  FROM EINHEIT JOIN HAUS ON(HAUS.id=EINHEIT.HAUS_ID) JOIN OBJEKT ON(HAUS.OBJEKT_ID=OBJEKT.id) WHERE OBJEKT.id='$objekt_id' && EINHEIT_AKTUELL='1' && EINHEIT_ID NOT IN (SELECT EINHEIT_ID FROM WIRT_EIN_TAB WHERE AKTUELL='1' && W_ID='$w_id') GROUP BY EINHEIT.id ORDER BY EINHEIT_KURZNAME ASC");
        foreach ($result as $row)
            $einheiten_array [] = $row;
        $this->anzahl_einheiten = count($einheiten_array);
        return $einheiten_array;
    }

    function del_all($w_id)
    {
        $db_abfrage = "DELETE FROM WIRT_EIN_TAB WHERE W_ID='$w_id'";
        DB::delete($db_abfrage);
        header("Location: " . route('web::bk::legacy', ['option' => 'wirt_einheiten_hinzu', 'w_id' => $w_id], false));
    }

    function del_eine($w_id, $e_id)
    {
        $db_abfrage = "DELETE FROM WIRT_EIN_TAB WHERE W_ID='$w_id' && EINHEIT_ID='$e_id'";
        DB::delete($db_abfrage);
        weiterleiten(route('web::bk::legacy', ['option' => 'wirt_einheiten_hinzu', 'w_id' => $w_id], false));
    }
} // end class wirt_e
