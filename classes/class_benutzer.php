<?php
/**
 * BERLUSSIMO
 *
 * Hausverwaltungssoftware
 *
 *
 * @copyright    Copyright (c) 2010, Berlus GmbH, Fontanestr. 1, 14193 Berlin
 * @link         http://www.berlus.de
 * @author       Sanel Sivac & Wolfgang Wehrheim
 * @contact		 software(@)berlus.de
 * @license     http://www.gnu.org/licenses/agpl.html AGPL Version 3
 *
 * @filesource   $HeadURL: http://192.168.2.52/svn/berlussimo_1/tags/02.11.2010 - Downloadversion 0.27/classes/class_benutzer.php $
 * @version      $Revision: 15 $
 * @modifiedby   $LastChangedBy: sivac $
 * @lastmodified $Date: 2011-07-07 10:41:33 +0200 (Do, 07 Jul 2011) $
 *
 */

/*Allgemeine Funktionsdatei laden*/
if (file_exists("includes/allgemeine_funktionen.php")) {
    include_once ("includes/allgemeine_funktionen.php");
}

/*Klasse "formular" für Formularerstellung laden*/
if (file_exists("classes/class_formular.php")) {
    include_once ("classes/class_formular.php");
}
if (file_exists("classes/class_zeiterfassung.php")) {
    include_once ("classes/class_zeiterfassung.php");
}

class benutzer {

    function get_all_users_arr() {
        $db_abfrage = "SELECT BENUTZER .  *  FROM BENUTZER  ORDER BY `BENUTZER`.`benutzername` ASC";
        $result = mysql_query($db_abfrage) or die(mysql_error());
        $numrows = mysql_numrows($result);
        if ($numrows) {
            while ($row = mysql_fetch_assoc($result))
                $my_arr[] = $row;
            return $my_arr;
        }
    }

    function get_all_users_arr2($alle = 1) {
        if ($alle == 1) {
            $db_abfrage = "SELECT BENUTZER .  *  FROM BENUTZER  ORDER BY `BENUTZER`.`benutzername` ASC";
        } else {
            $heute = date("Y-m-d");
            $db_abfrage = "SELECT BENUTZER .  *  FROM BENUTZER WHERE (AUSTRITT='0000-00-00' OR AUSTRITT>='$heute') ORDER BY `BENUTZER`.`benutzername` ASC";
        }
        $result = mysql_query($db_abfrage) or die(mysql_error());
        $numrows = mysql_numrows($result);
        if ($numrows) {
            while ($row = mysql_fetch_assoc($result))
                $my_arr[] = $row;
            return $my_arr;
        }
    }

    function get_user_info($b_id) {

        $result = mysql_query("SELECT * FROM BENUTZER JOIN BENUTZER_PARTNER ON (benutzer_id=BP_BENUTZER_ID)  WHERE benutzer_id='$b_id' GROUP BY benutzer_id  ORDER BY `BP_PARTNER_ID`, GEWERK_ID, benutzername ASC LIMIT 0,1");

        $numrows = mysql_numrows($result);
        if ($numrows) {
            while ($row = mysql_fetch_assoc($result))
                $my_arr[] = $row;
            return $my_arr;
        } else {
            $result = mysql_query("SELECT * FROM BENUTZER  WHERE benutzer_id='$b_id' LIMIT 0,1");
            $numrows = mysql_numrows($result);
            if ($numrows) {
                while ($row = mysql_fetch_assoc($result))
                    $my_arr[] = $row;
                return $my_arr;
            }
        }
    }

    function get_benutzer_id($benutzername) {
        $result = mysql_query("SELECT benutzer_id FROM BENUTZER WHERE benutzername='$benutzername' ORDER BY benutzer_id DESC LIMIT 0,1");
        $row = mysql_fetch_assoc($result);
        return $row['benutzer_id'];
    }

    function benutzer_anzeigen($sort = 'benutzername', $reihenfolge = 'SORT_DESC', $anzahl = '') {
        $usr_arr_db = $this -> get_all_users_arr();
        //print_r($usr_arr_db);
        $anz = count($usr_arr_db);
        if ($anz) {
            /*Wenn z.B. Top 5 anzeigen, */
            if ($anzahl < $anz && !empty($anzahl)) {
                $anz = $anzahl;
            }
            // array_sortByIndex($array, $index, $order = SORT_ASC, $natsort = FALSE, $case_sensitive = FALSE) {
            //$usr_arr = array_sortByIndex($usr_arr_db, $sort, $reihenfolge);
            $usr_arr = $usr_arr_db;
            echo "<table class=\"sortable\">";
            #	echo "<tr class=\"feldernamen\"><td>Benutzername</td><td>Geburtstag</td><td>Eintritt</td><td>Firma</td><td>Option</td></tr>";
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
            for ($a = 0; $a < $anz; $a++) {
                $z++;
                $benutzername = $usr_arr[$a]['benutzername'];
                $b_id = $usr_arr[$a]['benutzer_id'];
                $geb_dat = date_mysql2german($usr_arr[$a]['GEB_DAT']);
                $geb_dat_arr = explode('.', $geb_dat);
                $geb_t = $geb_dat_arr[0];
                $geb_m = $geb_dat_arr[1];
                $geb_j = $geb_dat_arr[2];
                $gewerk_id = $usr_arr[$a]['GEWERK_ID'];
                $eintritt = date_mysql2german($usr_arr[$a]['EINTRITT']);
                $ein_dat_arr = explode('.', $eintritt);
                $ein_t = $ein_dat_arr[0];
                $ein_m = $ein_dat_arr[1];
                $ein_j = $ein_dat_arr[2];
                $ze = new zeiterfassung;
                $partner_id = $ze -> get_partner_id_benutzer($b_id);
                if ($partner_id) {
                    $p = new partners;
                    $p -> get_partner_name($partner_id);
                }
                $link_ber = "<a href=\"index.php?daten=benutzer&option=berechtigungen&b_id=$b_id\">Berechtigungen</a>";
                $link_aendern = "<a href=\"index.php?daten=benutzer&option=aendern&b_id=$b_id\">Ändern</a>";
                $link_details = "<a href=\"?daten=details&option=details_anzeigen&detail_tabelle=BENUTZER&detail_id=$b_id\">Details</a>";
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

    function form_benutzer_aendern($b_id) {
        $this -> get_benutzer_infos($b_id);
        $z = new zeiterfassung;
        $partner_id = $z -> get_partner_id_benutzer($b_id);

        $f = new formular;
        $f -> erstelle_formular("Benutzerdaten von Benutzer $this->benutzername ändern", NULL);
        $f -> text_feld("Benutzername", "benutzername", "$this->benutzername", "20", 'benutzername', '');
        //$f->text_feld("Passwort", "passwort", "$this->passwort", "20", 'passwort','');
        $f -> passwort_feld("Passwort", "passwort", "$this->passwort", "20", 'passwort', '');
        $p = new partners;
        $p -> partner_dropdown('Mitarbeiter von', 'partner_id', 'partner_id', $partner_id);
        $p -> gewerke_dropdown('Gewerk/Abteilung', 'gewerk_id', 'gewerk_id', $this -> gewerk_id);
        #$f->datum_feld("Datum:", "datum", "", "10", 'datum','');
        $f -> datum_feld("Geb. am", "geburtstag", date_mysql2german($this -> geb_datum), "10", 'geburtstag', '');
        $f -> datum_feld("Eintritt", "eintritt", date_mysql2german($this -> datum_eintritt), "10", 'eintritt', '');
        $f -> datum_feld("Austritt", "austritt", date_mysql2german($this -> datum_austritt), "10", 'austritt', '');
        $f -> text_feld("urlaubstage im Jahr", "urlaub", "$this->urlaub", "5", 'urlaub', '');
        $f -> text_feld("Stunden/Wochen", "stunden_pw", nummer_punkt2komma($this -> stunden_wo), "5", 'stunden_pw', '');
        $f -> text_feld("Stundensatz", "stundensatz", nummer_punkt2komma($this -> stundensatz), "5", 'stundensatz', '');
        $f -> hidden_feld("b_id", "$b_id");
        $f -> hidden_feld("option", "benutzer_aendern_send");
        $f -> send_button("submit_bae", "Änderungen speichern");
        $f -> ende_formular();
    }

    function berechtigungen_arr($b_id) {
        $db_abfrage = "SELECT *  FROM BENUTZER_MODULE WHERE AKTUELL='1' && BENUTZER_ID='$b_id' ORDER BY MODUL_NAME ASC";
        $result = mysql_query($db_abfrage) or die(mysql_error());
        $numrows = mysql_numrows($result);
        if ($numrows) {
            while ($row = mysql_fetch_assoc($result))
                $my_arr[] = $row;
            return $my_arr;
        }

    }

    function berechtigungen($b_id) {
        $z = new zeiterfassung;
        $benutzername = $z -> get_benutzer_name($b_id);
        $ber_arr = $this -> berechtigungen_arr($b_id);
        $anz = count($ber_arr);
        if ($anz) {
            echo "<table>";
            echo "<tr class=\"feldernamen\"><td>Benutzer</td><td>$benutzername</td></tr>";
            echo "<tr class=\"feldernamen\"><td>Zeile</td><td>Modulzugriff</td></tr>";
            for ($a = 0; $a < $anz; $a++) {
                $zeile = $a + 1;
                $modul_name = $ber_arr[$a]['MODUL_NAME'];

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
        $this -> form_mberechtigungen_setzen($b_id);
    }

    function module_arr() {
        $casedir = dir("options/case");
        while ($func = $casedir -> read()) {

            if (substr($func, 0, 5) == "case.") {
                #echo $casedir->path."/".$func.'<br>';
                #$content1 = file_get_contents($casedir->path."/".$func);
                $dateiname = "$casedir->path/$func";
                #echo "$dateiname<br>";
                $dat_inhalt = file($dateiname);
                #echo '<pre>';
                #print_r($dat_inhalt);
                $inhalt = '';
                foreach ($dat_inhalt as $value) {
                    $inhalt .= $value . '<br>';
                }
                $content = $inhalt;
                $finden = 'case "';
                $pos = strpos($content, $finden);
                #echo $content;
                if ($pos == true) {
                    $pos_ap1 = strpos($content, '"', $pos);
                    if ($pos_ap1 == true) {
                        $pos_ap2 = strpos($content, '"', $pos_ap1 + 1);
                        if ($pos_ap2 == true) {
                            $laenge = $pos_ap2 - $pos_ap1;
                            $module_name = substr($content, $pos_ap1 + 1, $laenge - 1);
                            $module_arr[] = ltrim(rtrim($module_name));
                            #echo $module_name.'|';
                        } else {

                        }

                    }

                }
            }
        }
        closedir($casedir -> handle);
        sort($module_arr);
        #print_r($module_arr);
        return $module_arr;
    }

    function dropdown_benutzer($vorwahl = null) {
        $benutzer_arr = $this -> get_all_users_arr2(0);
        $anz = count($benutzer_arr);
        if ($anz) {
            echo "<label for=\"benutzer_id\">Mitarbeiter wählen</label><select id=\"benutzer_id\" name=\"benutzer_id\" size=\"1\">";
            for ($a = 0; $a < $anz; $a++) {
                $benutzername = $benutzer_arr[$a]['benutzername'];
                $benutzer_id = $benutzer_arr[$a]['benutzer_id'];
                #if(!check_user_mod($b_id, $modul_name)){
                if ($vorwahl != null) {
                    if ($benutzername == $vorwahl) {
                        echo "<option value=\"$benutzer_id\" selected>$benutzername</option>";
                    } else {
                        echo "<option value=\"$benutzer_id\">$benutzername</option>";
                    }
                } else {
                    if ($_SESSION[benutzer_id] == $benutzer_id) {
                        echo "<option value=\"$benutzer_id\" selected>$benutzername</option>";
                    } else {
                        echo "<option value=\"$benutzer_id\">$benutzername</option>";
                    }
                }
                #}
            }
            echo "</select>";
        } else {
            echo "Keine Mitarbeiter, bitte mitarbeiter unter Menüpunkt -> Benutzer anlegen";
        }
    }

    function dropdown_benutzer2($label, $name, $id, $js) {
        $benutzer_arr = $this -> get_all_users_arr();
        $anz = count($benutzer_arr);
        if ($anz) {
            echo "<label for=\"$id\">$label</label><select id=\"$id\" name=\"$name\" size=\"1\" $js>";
            echo "<option value=\"Alle\" selected>Alle</option>";
            for ($a = 0; $a < $anz; $a++) {
                $benutzername = $benutzer_arr[$a][benutzername];
                $benutzer_id = $benutzer_arr[$a][benutzer_id];
                #if(!check_user_mod($b_id, $modul_name)){
                if ($_SESSION[benutzer_id] == $benutzer_id) {
                    echo "<option value=\"$benutzer_id\" selected>$benutzername</option>";
                } else {
                    echo "<option value=\"$benutzer_id\">$benutzername</option>";
                }
                #}
            }
            echo "</select>";
        } else {
            echo "Keine Mitarbeiter, bitte mitarbeiter unter Menüpunkt -> Benutzer anlegen";
        }
    }

    function get_benutzer_infos($b_id) {
        $b_arr = $this -> get_user_info($b_id);
        $this -> benutzername = '';
        if (is_array($b_arr)) {
            $this -> benutzername = $b_arr[0]['benutzername'];
            $this -> benutzer_id = $b_id;
            $this -> passwort = $b_arr[0]['passwort'];

            $this -> stundensatz = $b_arr[0]['STUNDENSATZ'];
            $this -> geb_datum = $b_arr[0]['GEB_DAT'];
            $this -> gewerk_id = $b_arr[0]['GEWERK_ID'];
            $this -> datum_eintritt = $b_arr[0]['EINTRITT'];
            $this -> datum_austritt = $b_arr[0]['AUSTRITT'];

            $this -> urlaub = $b_arr[0]['URLAUB'];
            $this -> stunden_wo = $b_arr[0]['STUNDEN_PW'];

            if (isset($b_arr[0]['EMAIL'])) {
                $this -> benutzer_email = $b_arr[0]['EMAIL'];
            } else {
                $this -> benutzer_email = '';
            }
        }
    }

    function dropdown_module($b_id) {
        $module_arr = $this -> module_arr();
        $anz = count($module_arr);
        if ($anz) {
            echo "<label for=\"modul_name\">Modul wählen</label><select id=\"modul_name\" name=\"modul_name\" size=\"1\">";
            echo "<option value=\"*\">Vollzugriff</option>";
            for ($a = 0; $a < $anz; $a++) {
                $modul_name = $module_arr[$a];
                #if(!check_user_mod($b_id, $modul_name)){
                echo "<option value=\"$modul_name\">$modul_name</option>";
                #}
            }
            echo "</select>";
        } else {
            echo "Keine Module";
        }
    }

    function checkboxen_anzeigen($b_id) {
        $module_arr = $this -> module_arr();
        $anz = count($module_arr);
        if ($anz) {
            echo "<label for=\"modul_tab\">Modul wählen</label>";
            echo "<table id=\"mod_tab\">";
            echo "<tr>";
            echo "<td>";
            echo "<label for=\"modul_name\">Vollzugriff</label>";
            echo "<input type=\"checkbox\" name=\"modul_name[]\" value=\"*\" />";

            $pro_reihe = round(($anz + 1) / 5);
            $z = 1;
            for ($a = 0; $a < $anz; $a++) {
                $z++;
                $modul_name = $module_arr[$a];
                $modul_name_a = ' &nbsp ' . strtoupper($modul_name) . ' &nbsp ';

                echo "<label for=\"modul_name\">$modul_name_a</label>";
                if (check_user_mod($b_id, $modul_name)) {
                    echo "<input type=\"checkbox\" name=\"modul_name[]\" value=\"$modul_name\" checked />";
                } else {
                    echo "<input type=\"checkbox\" name=\"modul_name[]\" value=\"$modul_name\"  />";
                }
                if ($z == abs($pro_reihe)) {
                    echo "</td><td>";
                    $z = 0;
                }

            }
            echo "</td></tr></table>";
            echo "</select>";
        } else {
            echo "Keine Module";
        }
    }

    function form_mberechtigungen_setzen($b_id) {
        $z = new zeiterfassung;
        $benutzername = $z -> get_benutzer_name($b_id);
        $f = new formular;
        $f -> erstelle_formular("Zugriffsberechtigung für Benutzer $benutzername", NULL);
        $f -> hidden_feld("b_id", "$b_id");
        #$this->dropdown_module($b_id);
        $this -> checkboxen_anzeigen($b_id);
        $f -> hidden_feld("option", "zugriff_send");
        $f -> send_button("submit_ja", "Gewähren");
        #$f->send_button("submit_no", "Entziehen");
        $f -> ende_formular();
    }

    function form_neuer_benutzer() {
        $f = new formular;
        $f -> erstelle_formular("Neuen Benutzer/Mitarbeiter anlegen", NULL);
        $f -> text_feld("Benutzername", "benutzername", "", "20", 'benutzername', '');
        $f -> text_feld("Passwort", "passwort", "", "20", 'passwort', '');
        $p = new partners;
        $p -> partner_dropdown('Mitarbeiter von', 'partner_id', 'partner_id');
        $p -> gewerke_dropdown('Gewerk/Abteilung', 'gewerk_id', 'gewerk_id');
        #$f->datum_feld("Datum:", "datum", "", "10", 'datum','');
        $f -> datum_feld("Geb. am", "geburtstag", "", "10", 'geburtstag', '');
        $f -> datum_feld("Eintritt", "eintritt", "", "10", 'eintritt', '');
        $f -> datum_feld("Austritt", "austritt", "", "10", 'austritt', '');
        $f -> text_feld("urlaubstage im Jahr", "urlaub", "", "5", 'urlaub', '');
        $f -> text_feld("Stunden/Wochen", "stunden_pw", "", "5", 'stunden_pw', '');
        $f -> text_feld("Stundensatz", "stundensatz", "", "5", 'stundensatz', '');
        $f -> hidden_feld("option", "benutzer_send");
        $f -> send_button("submit_nb", "Benutzer speichern");
        $f -> ende_formular();
    }

    function benutzer_speichern($benutzername, $passwort, $partner_id, $stundensatz, $geb_dat, $gewerk_id, $eintritt, $austritt, $urlaub, $stunden_pw) {
        $last_id = last_id2('BENUTZER', 'benutzer_id') + 1;

        $db_abfrage = "INSERT INTO BENUTZER VALUES ('$last_id', '$benutzername', '$passwort', '$stundensatz', '$geb_dat', '$gewerk_id', '$eintritt', '$austritt', '$urlaub', '$stunden_pw')";
        $result = mysql_query($db_abfrage) or die(mysql_error());

        $db_abfrage = "INSERT INTO BENUTZER_PARTNER VALUES (NULL, '$last_id', '$partner_id', '1')";
        $result = mysql_query($db_abfrage) or die(mysql_error());
        /*Benutzer ID zurückgeben*/
        return $last_id;
    }

    function benutzer_aenderungen_speichern($b_id, $benutzername, $passwort, $partner_id, $stundensatz, $geb_dat, $gewerk_id, $eintritt, $austritt, $urlaub, $stunden_pw) {
        $geb_dat = date_german2mysql($geb_dat);
        $eintritt = date_german2mysql($eintritt);
        $austritt = date_german2mysql($austritt);

        /*Updaten*/
        $db_abfrage = "UPDATE BENUTZER SET benutzername='$benutzername', passwort='$passwort', STUNDENSATZ='$stundensatz', GEB_DAT='$geb_dat', GEWERK_ID='$gewerk_id', EINTRITT='$eintritt', AUSTRITT='$austritt', URLAUB='$urlaub', STUNDEN_PW='$stunden_pw' WHERE benutzer_id='$b_id'";
        $result = mysql_query($db_abfrage) or die(mysql_error());

        $db_abfrage = "UPDATE BENUTZER_PARTNER SET BP_PARTNER_ID='$partner_id' WHERE BP_BENUTZER_ID='$b_id'";
        $result = mysql_query($db_abfrage) or die(mysql_error());

    }

    function berechtigungen_speichern($b_id, $modul_name) {
        if (is_array($modul_name)) {

            $db_abfrage = "DELETE  FROM BENUTZER_MODULE WHERE BENUTZER_ID='$b_id'";
            $result = mysql_query($db_abfrage) or die(mysql_error());

            if (in_array('*', $modul_name)) {

                $db_abfrage = "INSERT INTO BENUTZER_MODULE VALUES(NULL,'0', '$b_id', '*', '1')";
                $result = mysql_query($db_abfrage) or die(mysql_error());

            } else {
                $anz = count($modul_name);
                for ($a = 0; $a < $anz; $a++) {
                    $mod = $modul_name[$a];
                    $db_abfrage = "INSERT INTO BENUTZER_MODULE VALUES(NULL,'0', '$b_id', '$mod', '1')";
                    $result = mysql_query($db_abfrage) or die(mysql_error());
                }
            }

        } else {

            /*Dropdown auswahl*/
            if ($modul_name == '*') {
                //erst bisherige  Module löschen
                $db_abfrage = "DELETE  FROM BENUTZER_MODULE WHERE BENUTZER_ID='$b_id'";
                $result = mysql_query($db_abfrage) or die(mysql_error());
            }

            $db_abfrage = "INSERT INTO BENUTZER_MODULE VALUES(NULL,'0', '$b_id', '$modul_name', '1')";
            $result = mysql_query($db_abfrage) or die(mysql_error());

        }

    }

    function berechtigungen_entziehen($b_id, $modul_name) {
        $db_abfrage = "DELETE  FROM BENUTZER_MODULE WHERE BENUTZER_ID='$b_id' && MODUL_NAME='$modul_name'";
        $result = mysql_query($db_abfrage) or die(mysql_error());
    }

    function dropdown_gewerke($label, $name, $id, $js) {
        $db_abfrage = "SELECT G_ID, BEZEICHNUNG FROM GEWERKE WHERE AKTUELL='1' ORDER BY BEZEICHNUNG ASC";
        $result = mysql_query($db_abfrage) or die(mysql_error());
        $numrows = mysql_num_rows($result);
        if ($numrows) {
            echo "<label for=\"$id\">$label</label><select id=\"$id\" name=\"$name\" $js>";
            echo "<option value=\"Alle\">Alle</option>";
            while ($row = mysql_fetch_assoc($result)) {
                $gid = $row['G_ID'];
                $bez = $row['BEZEICHNUNG'];
                echo "<option value=\"$gid\">$bez</option>";
            }
            echo "</select>";
        }
    }

}//Ende Klasse
?>
