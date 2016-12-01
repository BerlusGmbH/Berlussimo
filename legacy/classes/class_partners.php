<?php

/*
 * Klasse partners
 * Diese Klasse wird von /options/modules/partner genutzt
 * Beinhaltet wichtige Funktionen wie Formular, speichern von Partnern
 */

class partners
{

    /* Name eines Partner/Lieferand/Eigentümer */
    public $partner_ort;
    public $partner_name;
    public $partner_strasse;
    public $partner_hausnr;
    public $partner_plz;
    public $partner_id;
    public $partner_land;
    public $partner_dat;

    function suche_partner_in_array($suchtext)
    {
        $my_array = DB::select("SELECT * FROM  `PARTNER_LIEFERANT` WHERE  `AKTUELL` =  '1' AND  `PARTNER_NAME` LIKE  '%$suchtext%'
OR  `STRASSE` LIKE  '%$suchtext%'
OR  `PLZ` LIKE  '%$suchtext%'
OR  `ORT` LIKE  '%$suchtext%'
OR  `LAND` LIKE  '%$suchtext%'
 GROUP BY PARTNER_ID ORDER BY PARTNER_NAME ASC");

        if (!empty($my_array)) {
            /* Zusätzlich Stichwortsuche */
            $my_array_stich = $this->suche_partner_stichwort_arr($suchtext);
            if (!empty($my_array_stich)) {
                $anz_stich = count($my_array_stich);
                for ($p = 0; $p < $anz_stich; $p++) {
                    $partner_id = $my_array_stich [$p] ['PARTNER_ID'];
                    $this->get_partner_info($partner_id);
                    $anz = count($my_array);
                    $my_array [$anz] ['PARTNER_ID'] = $partner_id;
                    $my_array [$anz] ['PARTNER_NAME'] = "<b>$this->partner_name</b>";
                    $my_array [$anz] ['STRASSE'] = $this->partner_strasse;
                    $my_array [$anz] ['NUMMER'] = $this->partner_hausnr;
                    $my_array [$anz] ['PLZ'] = $this->partner_plz;
                    $my_array [$anz] ['ORT'] = $this->partner_ort;
                    $my_array [$anz] ['LAND'] = $this->partner_land;
                }
            }

            return $my_array;
        } else {
            $my_array_stich = $this->suche_partner_stichwort_arr($suchtext);
            if (!empty($my_array_stich)) {

                $anz_stich = count($my_array_stich);
                for ($p = 0; $p < $anz_stich; $p++) {
                    $partner_id = $my_array_stich [$p] ['PARTNER_ID'];
                    $this->get_partner_info($partner_id);
                    if (isset ($my_array)) {
                        $anz = count($my_array);
                    } else {
                        $anz = 0;
                    }
                    $my_array [$anz] ['PARTNER_ID'] = $partner_id;
                    $my_array [$anz] ['PARTNER_NAME'] = "<b>$this->partner_name</b>";
                    $my_array [$anz] ['STRASSE'] = $this->partner_strasse;
                    $my_array [$anz] ['NUMMER'] = $this->partner_hausnr;
                    $my_array [$anz] ['PLZ'] = $this->partner_plz;
                    $my_array [$anz] ['ORT'] = $this->partner_ort;
                    $my_array [$anz] ['LAND'] = $this->partner_land;
                }

                return $my_array;
            } else {

                return false;
            }
        }
    }

    function suche_partner_stichwort_arr($stichwort)
    {
        $result = DB::select("SELECT * FROM  `PARTNER_STICHWORT` WHERE  `AKTUELL` =  '1' AND  `STICHWORT` LIKE  '%$stichwort%'
			ORDER BY STICHWORT ASC");
        return $result;
    }

    function get_partner_info($partner_id)
    {
        $result = DB::select("SELECT *  FROM PARTNER_LIEFERANT WHERE PARTNER_ID='$partner_id' && AKTUELL = '1'");
        $row = $result[0];
        if ($row) {
            $this->partner_dat = $row ['PARTNER_DAT'];
            $this->partner_name = $row ['PARTNER_NAME'];
            $this->partner_strasse = $row ['STRASSE'];
            $this->partner_hausnr = $row ['NUMMER'];
            $this->partner_plz = $row ['PLZ'];
            $this->partner_ort = $row ['ORT'];
            $this->partner_land = $row ['LAND'];
        }
    }

    function stichworte_speichern($partner_id, $arr)
    {
        if (is_array($arr)) {
            $anz = count($arr);
            $this->stichworte_loeschen($partner_id);
            for ($a = 0; $a < $anz; $a++) {
                $stichwort = $arr [$a];

                $id = last_id2('PARTNER_STICHWORT', 'ID') + 1;
                $db_abfrage = "INSERT INTO PARTNER_STICHWORT VALUES (NULL, '$id', '$partner_id', '$stichwort',  '1')";
                DB::insert($db_abfrage);
            }
        }
    }

    function stichworte_loeschen($partner_id)
    {
        $db_abfrage = "UPDATE PARTNER_STICHWORT SET AKTUELL='0' WHERE PARTNER_ID='$partner_id'";
        DB::update($db_abfrage);
    }

    function stichwort_speichern($partner_id, $stichwort)
    {
        $id = last_id2('PARTNER_STICHWORT', 'ID') + 1;
        $db_abfrage = "INSERT INTO PARTNER_STICHWORT VALUES (NULL, '$id', '$partner_id', '$stichwort',  '1')";
        DB::insert($db_abfrage);
    }

    /* Name eines Partner/Lieferand/Eigentümer */

    function partner_liste_filter($partner_arr)
    {
        echo "<table class=\"sortable\">";
        echo "<tr><th>Partner</th><th>Anschrift</th><th>GEWERK / Stichwort</th><th>Details</th></tr>";
        $zaehler = 0;
        for ($a = 0; $a < count($partner_arr); $a++) {
            $zaehler++;
            $partner_id = $partner_arr [$a] ['PARTNER_ID'];
            $partner_name = $partner_arr [$a] ['PARTNER_NAME'];
            $partner_link_detail = "<a href='" . route('legacy::partner::index', ['option' => 'partner_im_detail', 'partner_id' => $partner_id]) . "'>$partner_name</a>";
            $link_detail_hinzu = "<a href='" . route('legacy::details::index', ['option' => 'details_hinzu', 'detail_tabelle' => 'PARTNER_LIEFERANT', 'detail_id' => $partner_id]) . "'>Details</a>";
            $partner_strasse = $partner_arr [$a] ['STRASSE'];
            $partner_nr = $partner_arr [$a] ['NUMMER'];
            $partner_plz = $partner_arr [$a] ['PLZ'];
            $partner_ort = $partner_arr [$a] ['ORT'];
            $anschrift = "$partner_strasse $partner_nr, $partner_plz $partner_ort";

            $pp = new partners ();
            $stich_arr = $pp->get_partner_stichwort_arr($partner_id);

            $link_stich_hinzu = "<a href='" . route('legacy::partner::index', ['option' => 'partner_stichwort', 'partner_id' => $partner_id]) . "'><b>Stichwort eingeben</b></a>";

            if ($zaehler == 1) {
                echo "<tr valign=\"top\" class=\"zeile1\"><td>$partner_link_detail</td><td>$anschrift</td><td>";

                if (!empty($stich_arr)) {
                    $anz_s = count($stich_arr);
                    for ($s = 0; $s < $anz_s; $s++) {
                        echo $stich_arr [$s] ['STICHWORT'] . ", ";
                    }
                }
                echo $link_stich_hinzu;

                echo "</td><td>$link_detail_hinzu</td></tr>";
            }
            if ($zaehler == 2) {
                echo "<tr valign=\"top\" class=\"zeile2\"><td>$partner_link_detail</td><td>$anschrift</td><td>";
                if (!empty($stich_arr)) {
                    $anz_s = count($stich_arr);
                    for ($s = 0; $s < $anz_s; $s++) {
                        echo $stich_arr [$s] ['STICHWORT'] . ", ";
                    }
                }
                echo $link_stich_hinzu;

                echo "</td><td>$link_detail_hinzu</td></tr>";
                $zaehler = 0;
            }
        }
        echo "</table><br>\n";
    }

    function get_partner_stichwort_arr($partner_id)
    {
        $result = DB::select("SELECT * FROM  `PARTNER_STICHWORT` WHERE  `AKTUELL` =  '1' AND  `PARTNER_ID` =  '$partner_id'
				 ORDER BY STICHWORT ASC");
        return $result;
    }

    function get_partner_id($partner_name)
    {
        $result = DB::select("SELECT PARTNER_ID FROM PARTNER_LIEFERANT WHERE PARTNER_NAME='$partner_name' && AKTUELL = '1'");
        $row = $result[0];
        $this->partner_id = $row ['PARTNER_ID'];
    }

    function form_partner_stichwort($partner_id)
    {
        $this->get_partner_info($partner_id);

        $f = new formular ();
        $f->erstelle_formular("Partner $this->partner_name Gewerke oder Stichwort eingeben", NULL);

        $stich_arr = $this->get_stichwort_arr();
        if (!empty($stich_arr)) {
            $anz = count($stich_arr);
            for ($a = 0; $a < $anz; $a++) {
                $stich = $stich_arr [$a] ['STICHWORT'];
                if ($this->check_stichwort($partner_id, $stich) == false) {
                    $f->check_box_js('stichworte[]', $stich, $stich, '', '');
                } else {
                    $f->check_box_js('stichworte[]', $stich, $stich, '', 'checked');
                }
            }
            // echo '<pre>';
            // print_r($stich_arr);
        }

        $f->hidden_feld("partner_id", "$partner_id");
        $f->hidden_feld("option", "partner_stich_sent");
        $f->send_button("submit", "Stichworte aktualisieren");
        $f->ende_formular();
    }

    /* Grundinformationen über einen Partner/Lieferand/Eigentümer */

    function get_stichwort_arr()
    {
        $result = DB::select("SELECT STICHWORT FROM  `PARTNER_STICHWORT` WHERE  `AKTUELL` =  '1'  GROUP BY STICHWORT	ORDER BY STICHWORT ASC");
        return $result;
    }

    /* Partner erfassen Formular */

    function check_stichwort($partner_id, $stichwort)
    {
        $result = DB::select("SELECT STICHWORT FROM  `PARTNER_STICHWORT` WHERE  `AKTUELL` =  '1'  && PARTNER_ID='$partner_id' && STICHWORT='$stichwort' LIMIT 0,1");
        return !empty($result);
    }

    /* Partner suchen Formular */

    function form_partner_stichwort_neu($partner_id)
    {
        $this->get_partner_info($partner_id);

        $f = new formular ();
        $f->erstelle_formular("Neues Gewerk / Stichwort eingeben", NULL);
        $f->text_feld("Stichwort", "stichwort", "", "30", 'stichwort_neu', '');
        $f->hidden_feld("partner_id", "$partner_id");
        $f->hidden_feld("option", "partner_stich_sent_neu");
        $f->send_button("submit", "Stichwort hinzufügen");
        $f->ende_formular();
    }

    /* Partner in Datenbank speichern */

    function form_partner_erfassen()
    {
        $form = new mietkonto ();
        $form->erstelle_formular("Partner erfassen", NULL);
        $js = "onkeyup=\"daj3('ajax/ajax_info.php?option=finde_partner&suchstring='+this.value, 'p_fund');\"";

        $f = new formular ();
        $f->text_bereich_js('Partnername', 'partnername', old('partnername'), '20', '3', 'partner_name', $js);
        echo "<div id=\"p_fund\" style=\"color:#ff0000;border:3px;border-color=#ff0000;\"></div>"; //
        $form->text_feld("Strasse:", "strasse", old('strasse'), "50");
        $form->text_feld("Hausnummer:", "hausnummer", old('hausnummer'), "10");
        $form->text_feld("Postleitzahl:", "plz", old('plz'), "10");
        $form->text_feld("Ort:", "ort", old('ort'), "25");
        $form->text_feld("Land:", "land", old('land'), "25");
        $form->text_feld("Telefon:", "tel", old('tel'), "25");
        $form->text_feld("Fax:", "fax", old('fax'), "25");
        $form->text_feld("Email:", "email", old('email'), "30");
        $form->send_button("submit_partner", "Partner speichern");
        $form->hidden_feld("option", "partner_gesendet");
        $form->ende_formular();
    } // Ende funktion

    /* Letzte Partner ID */

    function form_such_partner()
    {
        $form = new mietkonto ();
        $form->erstelle_formular("Partner suchen", NULL);
        $form->text_feld("Suchtext:", "suchtext", "", "50");
        $form->send_button("sBtN_such", "Partner suchen");
        $form->hidden_feld("option", "partner_suchen1");
        $form->ende_formular();
    }

    /* Letzte Partnergeldkonto ID */

    function partner_speichern($clean_arr)
    {
        foreach ($clean_arr as $key => $value) {
            $partnername = $clean_arr ['partnername'];
            $str = $clean_arr ['strasse'];
            $hausnr = $clean_arr ['hausnummer'];
            $plz = $clean_arr ['plz'];
            $ort = $clean_arr ['ort'];
            $land = $clean_arr ['land'];
            $tel = $clean_arr ['tel'];
            $fax = $clean_arr ['fax'];
            $email = $clean_arr ['email'];
            // $kreditinstitut = $clean_arr[kreditinstitut];
            // $kontonummer = $clean_arr[kontonummer];
            // $blz = $clean_arr[blz];

            // print_r($clean_arr);
            if (empty ($partnername) or empty ($str) or empty ($hausnr) or empty ($plz) or empty ($ort) or empty ($land)) {
                throw new \App\Exceptions\MessageException(
                    new \App\Messages\ErrorMessage("Dateneingabe unvollständig.")
                );
            }
        } // Ende foreach

        /* Prüfen ob Partner/Liefernat vorhanden */
        $result_3 = DB::select("SELECT * FROM PARTNER_LIEFERANT WHERE PARTNER_NAME = '$clean_arr[partnername]' && STRASSE='$clean_arr[strasse]' && NUMMER='$clean_arr[hausnummer]' && PLZ='$clean_arr[plz]' && AKTUELL = '1' ORDER BY PARTNER_NAME");
        $numrows_3 = count($result_3);

        /* Wenn kein Fehler durch eingabe oder partner in db nicht vorhanden wird neuer datensatz gespeichert */

        if (!$fehler && $numrows_3 < 1) {
            /* Partnerdaten ohne Kontoverbindung */
            $partner_id = $this->letzte_partner_id();
            $partner_id = $partner_id + 1;
            $db_abfrage = "INSERT INTO PARTNER_LIEFERANT VALUES (NULL, $partner_id, '$clean_arr[partnername]','$clean_arr[strasse]', '$clean_arr[hausnummer]','$clean_arr[plz]','$clean_arr[ort]','$clean_arr[land]','1')";
            $resultat = DB::insert($db_abfrage);
            /* Protokollieren */
            $last_dat = DB::getPdo()->lastInsertId();
            protokollieren('PARTNER_LIEFERANT', $last_dat, '0');
            if ($resultat) {
                session()->flash('info', ["Partner $clean_arr[partnername] wurde gespeichert."]);
                weiterleiten(route('legacy::partner::index', ['option' => 'partner_liste'], false));
            }
        } // ende fehler
        if ($numrows_3 > 0) {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\ErrorMessage("Partner $clean_arr[partnername] exisitiert bereits.")
            );
        }
        session()->forget('partnername');
        session()->forget('strasse');
        session()->forget('hausnummer');
        session()->forget('plz');
        session()->forget('ort');
        session()->forget('land');

        $dd = new detail ();
        if (!empty ($tel)) {
            $dd->detail_speichern_2('PARTNER_LIEFERANT', $partner_id, 'Telefon', $tel, Auth::user()->email . " " . date("d.m.Y H:i:s"));
        }
        if (!empty ($fax)) {
            $dd->detail_speichern_2('PARTNER_LIEFERANT', $partner_id, 'Fax', $fax, Auth::user()->email . " " . date("d.m.Y H:i:s"));
        }
        if (!empty ($email)) {
            $dd->detail_speichern_2('PARTNER_LIEFERANT', $partner_id, 'Email', $email, Auth::user()->email . " " . date("d.m.Y H:i:s"));
        }
    }

    /* Letzte Zuweisunggeldkonto ID */

    function letzte_partner_id()
    {
        $result = DB::select("SELECT PARTNER_ID FROM PARTNER_LIEFERANT  ORDER BY PARTNER_ID DESC LIMIT 0,1");
        $row = $result[0];
        return $row ['PARTNER_ID'];
    }

    /* Alle Partner in ein array laden */

    function letzte_konto_geldkonto_id_p($partner_id)
    {
        $result = DB::select("SELECT KONTO_ID FROM GELD_KONTEN_ZUWEISUNG WHERE KOSTENTRAEGER_TYP='Partner' && KOSTENTRAEGER_ID='$partner_id' ORDER BY ZUWEISUNG_ID DESC LIMIT 0,1");
        $row = $result[0];
        return $row ['KONTO_ID'];
    }

    /* Dropdownfeld mit Partnern/Lieferanten/Eigentümern */

    function partner_rechts_anzeigen()
    {
        $result = DB::select("SELECT * FROM PARTNER_LIEFERANT WHERE AKTUELL = '1' ORDER BY PARTNER_NAME");
        if (!empty($result)) {
            $form = new mietkonto ();
            $form->erstelle_formular("Partner", NULL);
            echo "<div class=\"tabelle\">\n";
            echo "<table class=\"sortable\">\n";
            echo "<tr class=\"feldernamen\"><td>Partner</td></tr>\n";
            echo "<tr><th>Partner</th></tr>";
            $numrows = count($result);
            for ($i = 0; $i < $numrows; $i++) {
                echo "<tr><td>" . $result[$i] ['PARTNER_NAME'] . "</td></tr>\n";
            }
            echo "</table></div>\n";
            $form->ende_formular();
        } else {
            echo "Keine Partner";
        }
    }

    /* Alle Gewerke in ein array laden */

    function partner_dropdown($label, $name, $id, $vorwahl = null)
    {
        $partner_arr = $this->partner_in_array();
        echo "<div class=\"input-field\"><select name=\"$name\" size=\"1\" id=\"$id\">";
        for ($a = 0; $a < count($partner_arr); $a++) {
            $partner_id = $partner_arr [$a] ['PARTNER_ID'];
            $partner_name = $partner_arr [$a] ['PARTNER_NAME'];
            if ($vorwahl == $partner_id) {
                echo "<option value=\"$partner_id\" selected>$partner_name</OPTION>\n";
            } else {
                echo "<option value=\"$partner_id\">$partner_name</OPTION>\n";
            }
        }
        echo "</select><label for=\"$id\">$label</label>\n";
    }

    /* Dropdownfeld mit Gewerken */

    function partner_in_array()
    {
        $result = DB::select("SELECT * FROM PARTNER_LIEFERANT WHERE AKTUELL = '1' ORDER BY PARTNER_NAME ASC");
        if (!empty($result)) {
            return $result;
        } else {
            return false;
        }
    }

    function gewerke_dropdown($label, $name, $id, $vorwahl = null)
    {
        $gewerk_arr = $this->gewerke_in_array();
        echo "<label for=\"$id\">$label</label><select name=\"$name\" size=\"1\" id=\"$id\">";
        for ($a = 0; $a < count($gewerk_arr); $a++) {
            $gewerk_id = $gewerk_arr [$a] ['G_ID'];
            $bezeichnung = $gewerk_arr [$a] ['BEZEICHNUNG'];
            if ($vorwahl == $gewerk_id) {
                echo "<option value=\"$gewerk_id\" selected>$bezeichnung</OPTION>\n";
            } else {
                echo "<option value=\"$gewerk_id\">$bezeichnung</OPTION>\n";
            }
        }
        echo "</select><br>\n";
    }

    function gewerke_in_array()
    {
        $result = DB::select("SELECT * FROM GEWERKE WHERE AKTUELL = '1' ORDER BY BEZEICHNUNG ASC");
        if (!empty($result)) {
            return $result;
        } else {
            return false;
        }
    }

    function partner_liste()
    {
        $partner_arr = $this->partner_in_array();
        echo "<table class=\"sortable striped\">";
        echo "<tr><th>Partner</th><th>Anschrift</th><th>Gewerk / Stichwort</th><th>Details</th></tr>";
        $zaehler = 0;
        for ($a = 0; $a < count($partner_arr); $a++) {
            $zaehler++;
            $partner_id = $partner_arr [$a] ['PARTNER_ID'];
            $partner_name = $partner_arr [$a] ['PARTNER_NAME'];
            $partner_link_detail = "<a href='" . route('legacy::partner::index', ['option' => 'partner_im_detail', 'partner_id' => $partner_id]) . "'>$partner_name</a>";
            $link_detail_hinzu = "<a href='" . route('legacy::details::index', ['option' => 'details_hinzu', 'detail_tabelle' => 'PARTNER_LIEFERANT', 'detail_id' => $partner_id]) . "'>Details</a>";
            $link_aendern = "<a href='" . route('legacy::partner::index', ['option' => 'partner_aendern', 'partner_id' => $partner_id]) . "'>Ändern</a>";
            $partner_strasse = $partner_arr [$a] ['STRASSE'];
            $partner_nr = $partner_arr [$a] ['NUMMER'];
            $partner_plz = $partner_arr [$a] ['PLZ'];
            $partner_ort = $partner_arr [$a] ['ORT'];
            $anschrift = "$partner_strasse $partner_nr, $partner_plz $partner_ort";

            echo "<tr valign=\"top\" class=\"zeile$zaehler\"><td>$partner_link_detail</td><td>$anschrift</td><td>";
            $pp = new partners ();
            $stich_arr = $pp->get_partner_stichwort_arr($partner_id);

            $link_stich_hinzu = "<a href='" . route('legacy::partner::index', ['option' => 'partner_stichwort', 'partner_id' => $partner_id]) . "'><b>Stichwort eingeben</b></a>";

            if (!empty($stich_arr)) {
                $anz_s = count($stich_arr);
                for ($s = 0; $s < $anz_s; $s++) {
                    echo $stich_arr [$s] ['STICHWORT'] . ", ";
                }
            }
            echo $link_stich_hinzu;
            echo "</td><td>$link_detail_hinzu $link_aendern</td></tr>";

            if ($zaehler == 2) {
                $zaehler = 0;
            }
        }
        echo "</table><br>\n";
    }

    function form_partner_aendern($partner_id)
    {
        $this->get_partner_info($partner_id);
        if ($this->partner_name) {
            $f = new formular ();
            $f->erstelle_formular("Partner $this->partner_name ändern", NULL);
            $f->text_bereich("Partnername", "partnername", $this->partner_name, "20", "3", 'partnername');
            $f->text_feld("Strasse:", "strasse", $this->partner_strasse, "30", 'strasse', '');
            $f->text_feld("Nummer:", "hausnummer", $this->partner_hausnr, "10", 'hausnummer', '');
            $f->text_feld("PLZ:", "plz", $this->partner_plz, "10", 'plz', '');
            $f->text_feld("Ort:", "ort", $this->partner_ort, "30", 'ort', '');
            $f->text_feld("Land:", "land", $this->partner_land, "30", 'land', '');
            // $f->text_feld("Kreditinstitut:", "kreditinstitut", "", "10");
            // $f->text_feld("Kontonummer:", "kontonummer", "", "10");
            // $f->text_feld("Bankleitzahl:", "blz", "", "10");
            $f->hidden_feld("partner_dat", "$this->partner_dat");
            $f->hidden_feld("partner_id", "$partner_id");
            $f->hidden_feld("option", "partner_aendern_send");
            $f->send_button("submit", "Änderung speichern");
            $f->ende_formular();
        } else {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\ErrorMessage("Partner $partner_id unbekannt")
            );
        }
    }

    function partner_aendern($partner_dat, $partner_id, $partnername, $strasse, $hausnummer, $plz, $ort, $land)
    {
        /* Deaktivieren */
        $db_abfrage = "UPDATE PARTNER_LIEFERANT SET AKTUELL='0' WHERE PARTNER_DAT='$partner_dat'";
        DB::update($db_abfrage);

        /* Änderung Speichern */
        $db_abfrage = "INSERT INTO PARTNER_LIEFERANT VALUES(NULL, '$partner_id', '$partnername', '$strasse', '$hausnummer', '$plz', '$ort', '$land', '1')";
        DB::insert($db_abfrage);

        /* Protokollieren */
        $last_dat = DB::getPdo()->lastInsertId();
        protokollieren('PARTNER_LIEFERANT', $last_dat, $partner_dat);
    }

    function partner_nach_umsatz()
    {
        $result = DB::select("SELECT  `AUSSTELLER_TYP` , AUSSTELLER_ID, SUM( NETTO ) AS NETTO, SUM( BRUTTO ) AS BRUTTO
FROM  `RECHNUNGEN` 
WHERE  `RECHNUNGSTYP` =  'RECHNUNG'
GROUP BY  `AUSSTELLER_TYP` ,  `AUSSTELLER_ID` 
ORDER BY SUM( BRUTTO ) DESC 
LIMIT 0 , 80");
        if (!empty($result)) {
            foreach ($result as $row) {
                $this->get_partner_name($row ['AUSSTELLER_ID']);
                $row ['PARTNER_NAME'] = $this->partner_name;
            }
            return $result;
        } else {
            return false;
        }
    }

    function get_partner_name($partner_id)
    {
        if (isset ($this->partner_name)) {
            unset ($this->partner_name);
        }
        $result = DB::select("SELECT PARTNER_NAME FROM PARTNER_LIEFERANT WHERE PARTNER_ID='$partner_id' && AKTUELL = '1'");
        $row = $result[0];
        if ($row) {
            $this->partner_name = $row ['PARTNER_NAME'];
        } else {
            $this->partner_name = '<b>unbekannt</b>';
        }
    }

    function form_partner_serienbrief()
    {
        $partner_arr = $this->partner_in_array();
        if (empty($partner_arr)) {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\InfoMessage("Keine Partner gefunden!")
            );
        }

        $f = new formular ();
        $f->erstelle_formular('Serienbrief an Partner', null);
        $f->hidden_feld('option', 'serien_brief_vorlagenwahl');
        echo "<div class='row'>";
        echo "<div class='col l3'>";
        $f->send_button('Button', 'Vorlage wählen');
        echo "</div>";
        echo "<div class='col l3'>";
        $f->send_button("delete", "Auswahl entfernen");
        echo "</div>";
        echo "</div>";

        echo "<div class='row'>";
        echo "<div class='col l3'>";
        $f->check_box_js_alle('c_alle', 'c_alle', 1, 'Alle', '', '', 'p_ids');
        echo "</div>";

        $anz_p = count($partner_arr);
        for ($a = 0; $a < $anz_p; $a++) {
            $p_id = $partner_arr [$a] ['PARTNER_ID'];
            $p_name = $partner_arr [$a] ['PARTNER_NAME'];

            echo "<div class='col l3'>";
            if (session()->has('p_ids') && in_array($p_id, session()->get('p_ids'))) {
                $f->check_box_js1('p_ids[]', 'p_id_' . $p_id, $p_id, "$p_name", '', 'checked');
            } else {
                $f->check_box_js1('p_ids[]', 'p_id_' . $p_id, $p_id, "$p_name", '', '');
            }
            echo "</div>";
        }
        echo "</div>";
        $f->send_button('Button', 'Vorlage wählen');
        $f->ende_formular();
    }
} // Ende Klasse Partner
