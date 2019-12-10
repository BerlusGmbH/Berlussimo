<?php

class kundenweb
{
    function kundendaten_anzeigen_alle()
    {
        $arr = $this->get_kundendaten_arr();
        if (empty($arr)) {
            fehlermeldung_ausgeben("Keine Kundweb Benutzer vorhanden");
        } else {
            $f = new formular ();
            $f->fieldset("Kunden mit Zugriff auf Kundenweb", 'kp');
            $anz = count($arr);
            echo "<table class=\"sortable\"><tr><th>PERSON</th><th>FIRMA</th><th>BENUTZERNAME</th><th>EMAIL</th><th>BERECHTIGUNG</th><th>LÖSCHEN</th></tr>";
            for ($a = 0; $a < $anz; $a++) {
                $kunden_id = $arr [$a] ['ID'];
                $username = $arr [$a] ['USERNAME'];
                $email = $arr [$a] ['EMAIL'];
                $person_id = $arr [$a] ['PERSON_ID'];
                $partner_id = $arr [$a] ['PDF_PARTNER_ID'];

                $p = new person ();
                $p->get_person_infos($person_id);
                $pa = new partners ();
                $pa->get_partner_info($partner_id);
                $link_ber = "<a href='" . route('web::kundenweb::legacy', ['option' => 'berechtigung', 'kunden_id' => $kunden_id]) . "'>Berechtigung</a>";
                $link_deakt = "<a href='" . route('web::kundenweb::legacy', ['option' => 'deaktivieren', 'kunden_id' => $kunden_id]) . "'>Deaktivieren/Löschen</a>";
                echo "<tr><td>$p->person_nachname $p->person_vorname</td><td>$pa->partner_name</td><td>$username</td><td>$email</td><td>$link_ber</td><td>$link_deakt</td></tr>";
            }
            echo "</table>";
            $f->fieldset_ende();
        }
    }

    function get_kundendaten_arr($kunden_id = 0)
    {
        if ($kunden_id == 0) {
            $db_abfrage = "SELECT * FROM  `KUNDEN_LOGIN` WHERE AKTUELL='1'";
        } else {
            $db_abfrage = "SELECT * FROM  `KUNDEN_LOGIN` WHERE ID='$kunden_id' && AKTUELL='1'";
        }
        $result = DB::select($db_abfrage);
        return $result;
    }

    function kundendaten_anzeigen($kunden_id)
    {
        $arr = $this->get_kundendaten_arr($kunden_id);
        if (empty($arr)) {
            fehlermeldung_ausgeben("Keine Kundweb Benutzer vorhanden");
        } else {
            $anz = count($arr);
            $f = new formular ();
            $f->fieldset("Kundenprofil", 'kp');
            echo "<table class=\"sortable\"><tr><th>PERSON</th><th>FIRMA</th><th>BENUTZERNAME</th><th>EMAIL</th><th>PASSWORT</th></tr>";
            for ($a = 0; $a < $anz; $a++) {
                $kunden_id = $arr [$a] ['ID'];
                $username = $arr [$a] ['USERNAME'];
                $passwd = $arr [$a] ['PASSWORD'];
                $email = $arr [$a] ['EMAIL'];
                $person_id = $arr [$a] ['PERSON_ID'];
                $partner_id = $arr [$a] ['PDF_PARTNER_ID'];

                $p = new person ();
                $p->get_person_infos($person_id);
                $pa = new partners ();
                $pa->get_partner_info($partner_id);
                echo "<tr><td>$p->person_nachname $p->person_vorname</td><td>$pa->partner_name</td><td>$username</td><td>$email</td><td>$passwd</td></tr>";
            }
            echo "</table>";

            /* Formular für Neue berechtigungen */
            $this->form_berechtigung($kunden_id);

            /* Berechtigungen */
            $arr_ber = $this->kunden_berr_arr($kunden_id);
            if (empty($arr_ber)) {
                die ('Keine Berechtigung für den Kunden');
            } else {
                $anz = count($arr_ber);
                echo "<table class=\"sortable\"><tr><th>NR</th><th>TYP</th><th>BEZEICHNUNG</th><th>OPTIONEN</th></tr>";
                $z = 0;
                for ($a = 0; $a < $anz; $a++) {
                    $z++;
                    $ber_obj = $arr_ber [$a] ['ZUGRIFF_OBJ'];
                    $ber_id = $arr_ber [$a] ['ZUGRIFF_ID'];
                    $r = new rechnung ();
                    $kos_bez = $r->kostentraeger_ermitteln($ber_obj, $ber_id);
                    $link_loeschen = "<a href='" . route('web::kundenweb::legacy', ['option' => 'berechtigung_del', 'kunden_id' => $kunden_id, 'ber_obj' => $ber_obj, 'ber_id' => $ber_id]) . "'>Löschen</a>";
                    echo "<tr><td>$z</td><td>$ber_obj</td><td>$kos_bez</td><td>$link_loeschen</td></tr>";
                }
                echo "</table>";

                $f->fieldset_ende();
            }
        }
    }

    function form_berechtigung($kunden_id)
    {
        $person_id = $this->get_person_of_kunde($kunden_id);

        if (!empty ($person_id)) {
            $p = new person ();
            $p->get_person_infos($person_id);
            $f = new formular ();
            $f->erstelle_formular("Berechtigungen für $p->person_nachname $p->person_vorname hinzufügen", null);
            $bu = new buchen ();
            $js_typ = "onchange=\"list_kostentraeger('list_kostentraeger', this.value)\"";
            $bu->dropdown_kostentreager_typen('Kostenträgertyp', 'kostentraeger_typ', 'kostentraeger_typ', $js_typ);
            $js_id = "";
            $bu->dropdown_kostentreager_ids('Kostenträger', 'kostentraeger_id', 'dd_kostentraeger_id', $js_id);
            $f->hidden_feld('option', 'ber_hinzu');
            $f->hidden_feld('person_id', $person_id);
            $f->send_button('BTN_ber', 'Berechtigung hinzufügen');
            $f->ende_formular();
        }
    }

    function get_person_of_kunde($kunden_id)
    {
        $db_abfrage = "SELECT * FROM  `KUNDEN_LOGIN` WHERE ID='$kunden_id' && AKTUELL='1' ORDER BY DAT DESC LIMIT 0,1";
        $result = DB::select($db_abfrage);
        if (!empty($result)) {
            $row = $result[0];
            return $row ['PERSON_ID'];
        }
    }

    function kunden_berr_arr($kunden_id = 0)
    {
        if ($kunden_id == 0) {
            fehlermeldung_ausgeben("Kundennr 0 unbekannt!");
            die ();
        } else {
            $person_id = $this->get_person_of_kunde($kunden_id);
            if (empty ($person_id)) {
                die ("KEINE PERSON DER KUNDENNR ZUGEWIESEN");
            }
            $db_abfrage = "SELECT * FROM  `KUNDEN_LOG_BER` WHERE PERSON_ID='$person_id' && AKTUELL='1'";
            $result = DB::select($db_abfrage);
            return $result;
        }
    }

    function get_kunden_id_of_person($person_id)
    {
        $db_abfrage = "SELECT * FROM  `KUNDEN_LOGIN` WHERE PERSON_ID='$person_id' && AKTUELL='1' ORDER BY DAT DESC LIMIT 0,1";
        $result = DB::select($db_abfrage);
        if (!empty($result)) {
            $row = $result[0];
            return $row ['ID'];
        }
    }

    function berechtigung_del($kunden_id, $ber_obj, $ber_id)
    {
        $person_id = $this->get_person_of_kunde($kunden_id);

        if (!empty ($person_id)) {
            $db_abfrage = "UPDATE `KUNDEN_LOG_BER` SET AKTUELL='0' WHERE PERSON_ID='$person_id' &&  ZUGRIFF_OBJ='$ber_obj' && ZUGRIFF_ID='$ber_id' && AKTUELL='1'";
            DB::update($db_abfrage);
        }
        return true;
    }

    function berechtigung_speichern($person_id, $ber_obj, $ber_id)
    {
        /* Prüfen ob Berechtigung bereits vorhanden, wenn nicht, dann speichen */
        if ($this->check_ber($person_id, $ber_obj, $ber_id) == false) {
            $last_id = last_id2('KUNDEN_LOG_BER', 'ID') + 1;
            $sql = "INSERT INTO `KUNDEN_LOG_BER` VALUES (NULL, '$last_id', '$person_id', '$ber_obj', '$ber_id', '1');";
            DB::insert($sql);
            /* Protokollieren */
            $last_dat = DB::getPdo()->lastInsertId();
            protokollieren('KUNDEN_LOG_BER', $last_dat, '0');
        }
        return true;
    }

    function check_ber($person_id, $ber_obj, $ber_id)
    {
        $db_abfrage = "SELECT * FROM  `KUNDEN_LOG_BER` WHERE PERSON_ID='$person_id' && ZUGRIFF_OBJ='$ber_obj' && ZUGRIFF_ID='$ber_id' && AKTUELL='1' ORDER BY DAT DESC LIMIT 0,1";
        $result = DB::select($db_abfrage);
        return !empty($result);
    }

    function benutzer_speichern($person_id, $partner_id, $username, $passwd, $email)
    {
        $last_id = last_id2('KUNDEN_LOGIN', 'ID') + 1;
        $sql = "INSERT INTO `KUNDEN_LOGIN` VALUES (NULL, '$last_id', '$username', '$passwd', '$email', '$person_id', '$partner_id','1');";
        DB::insert($sql);
        /* Protokollieren */
        $last_dat = DB::getPdo()->lastInsertId();
        protokollieren('KUNDEN_LOGIN', $last_dat, '0');

        return true;
    }

    /* Kundenlogin deaktivieren und Berechtigungen entziehen */
    function kunden_deaktivieren($kunden_id)
    {
        $person_id = $this->get_person_of_kunde($kunden_id);

        if (!empty ($person_id)) {
            /* Logindaten deaktivieren */
            $db_abfrage = "UPDATE `KUNDEN_LOGIN` SET AKTUELL='0' WHERE ID='$kunden_id' && AKTUELL='1'";
            DB::update($db_abfrage);

            /* Berechtigungen deaktivieren */
            $db_abfrage = "UPDATE `KUNDEN_LOG_BER` SET AKTUELL='0' WHERE PERSON_ID='$person_id' && AKTUELL='1'";
            DB::update($db_abfrage);
        }
    }
}