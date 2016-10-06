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
 * @contact         software(@)berlus.de
 * @license     http://www.gnu.org/licenses/agpl.html AGPL Version 3
 *
 * @filesource   $HeadURL: http://192.168.2.52/svn/berlussimo_1/tags/02.11.2010 - Downloadversion 0.27/includes/allgemeine_funktionen.php $
 * @version      $Revision: 15 $
 * @modifiedby   $LastChangedBy: sivac $
 * @lastmodified $Date: 2011-07-07 10:41:33 +0200 (Do, 07 Jul 2011) $
 *
 */
function umlautundgross($wort)
{
    $tmp = strtoupper($wort);
    $suche = array(
        'Ä',
        'Ö',
        'Ü',
        'ß',
        'ä',
        'ö',
        'ü',
        'ß'
    );
    $ersetze = array(
        'AE',
        'OE',
        'UE',
        'SS',
        'AE',
        'OE',
        'UE',
        'SS'
    );
    $ret = str_replace($suche, $ersetze, $tmp);
    return $ret;
}

function gib_zahlen($string)
{
    $arr = explode(' ', $string);
    // print_r($arr);
    if (is_array($arr)) {
        $anz = count($arr);

        for ($a = 0; $a < $anz; $a++) {
            if (($arr [$a]) != '') {
                if (!ctype_alpha($arr [$a])) {
                    $n_arr [] = $arr [$a];
                }
            }
        }
        if (isset ($n_arr)) {
            // $n_arr1 = array_unique($n_arr);
            return $n_arr;
        }
    }
    // is_
}

function p($arr)
{
    echo '<pre>';
    print_r($arr);
}

function check_user_mod($benutzer_id, $module_name)
{
    $result = DB::select("SELECT BM_DAT FROM BENUTZER_MODULE WHERE BENUTZER_ID='$benutzer_id' && (MODUL_NAME='$module_name' OR MODUL_NAME='*') && AKTUELL='1'");
    if (!empty($result)) {
        return true;
    } else {
        /* Fehlerhaften Zugriff protokollieren */
        $wer = Auth::user()->email;
        $ip = $_SERVER ['REMOTE_ADDR'];
        $host = gethostbyaddr($_SERVER ['REMOTE_ADDR']);
        /* Nur wenn jemand seine eigenen Rechte überlisten will, sonst ist das der Admin */
        if (Auth::user()->id == $benutzer_id) {
            DB::insert("INSERT INTO ZUGRIFF_ERROR VALUES(NULL, '$benutzer_id','$wer', NULL, '$module_name', '$ip', '$host')");
        }
        return false;
    }
}

function tage_plus($datum, $tage)
{
    // echo "$datum T$tage<br>";
    $dat_arr = explode('-', $datum);
    $j = $dat_arr [0];
    $m = $dat_arr [1];
    $d = $dat_arr [2];
    return date('Y-m-d', mktime(0, 0, 0, $m, $d + $tage, $j));
    // $gestern = date('d.m.Y',mktime(0,0,0,$m,$d-1,$j));
}

function tage_minus($datum, $tage)
{
    // echo "$datum T$tage<br>";
    $dat_arr = explode('-', $datum);
    $j = $dat_arr [0];
    $m = $dat_arr [1];
    $d = $dat_arr [2];
    return date('Y-m-d', mktime(0, 0, 0, $m, $d - $tage, $j));
    // $gestern = date('d.m.Y',mktime(0,0,0,$m,$d-1,$j));
}

function tage_plus_wp($datum, $tage)
{
    $dat_arr = explode('.', $datum);
    $j = $dat_arr[2];
    $m = $dat_arr[1];
    $d = $dat_arr[0];
    return date('d.m.Y', mktime(0, 0, 0, $m, $d + $tage, $j));
}

function tage_minus_wp($datum, $tage)
{
    $dat_arr = explode('.', $datum);
    $j = $dat_arr[2];
    $m = $dat_arr[1];
    $d = $dat_arr[0];
    return date('d.m.Y', mktime(0, 0, 0, $m, $d - $tage, $j));
}

function check_user_links($benutzer_id, $module_name)
{
    $result = DB::select("SELECT BM_DAT FROM BENUTZER_MODULE WHERE BENUTZER_ID='$benutzer_id' && (MODUL_NAME='$module_name' OR MODUL_NAME='*') && AKTUELL='1'");
    if (!empty($result)) {
        return 1;
    }
}

function last_id2($tab, $spalte)
{
    $result = DB::select("SELECT $spalte FROM `$tab` ORDER BY $spalte DESC LIMIT 0,1");
    if (!empty($result)) {
        return $result[0][$spalte];
    }
}

function backlink()
{
    echo "<hr class=\"backlink\"><a class=\"backlink\" href=\"javascript:history.back()\"><b>Zurück</b></a><hr class=\"backlink\">\n";
}

function letzte_objekt_dat()
{
    $result = DB::select("SELECT OBJEKT_DAT FROM OBJEKT ORDER BY OBJEKT_DAT DESC LIMIT 0,1");
    foreach ($result as $row)
        return $row['OBJEKT_DAT'];
}

function letzte_objekt_dat_kurzname($kurzname)
{
    $result = DB::select("SELECT OBJEKT_DAT FROM OBJEKT WHERE OBJEKT_AKTUELL='1' ORDER BY OBJEKT_DAT DESC LIMIT 0,1");
    foreach ($result as $row)
        return $row['OBJEKT_DAT'];
}

function protokollieren($tabele, $dat_neu, $dat_alt)
{
    $wer = Auth::user()->email;
    $ip = $_SERVER ['REMOTE_ADDR'];
    DB::insert("INSERT INTO PROTOKOLL VALUES (NULL,'$wer', '$ip', NULL, '$tabele', '$dat_neu', '$dat_alt')");
}

function anzahl_haeuser_im_objekt($obj_id)
{
    $result = DB::select("SELECT COUNT(HAUS_ID) AS ANZAHL FROM HAUS WHERE OBJEKT_ID='$obj_id' && HAUS_AKTUELL='1'");
    return $result[0]['ANZAHL'];
}

function objekt_kurzname($obj_id)
{
    $result = DB::select("SELECT OBJEKT_KURZNAME FROM OBJEKT WHERE OBJEKT_ID='$obj_id' && OBJEKT_AKTUELL='1' ORDER BY OBJEKT_DAT DESC LIMIT 0,1");
    foreach ($result as $row)
        return $row['OBJEKT_KURZNAME'];
}

function objekt_kurzname_of_haus($haus_id)
{
    $result = DB::select("SELECT OBJEKT_ID FROM HAUS WHERE HAUS_ID='$haus_id' && HAUS_AKTUELL='1' ORDER BY HAUS_DAT DESC LIMIT 0,1");
    foreach ($result as $row) {
        $result1 = DB::select("SELECT OBJEKT_KURZNAME FROM OBJEKT WHERE OBJEKT_ID='$row[OBJEKT_ID]' && OBJEKT_AKTUELL='1' ORDER BY OBJEKT_DAT DESC LIMIT 0,1");
        foreach ($result1 as $row1) {
            return $row1['OBJEKT_KURZNAME'];
        }
    }
}

function last_id($tabelle)
{
    $spaltenname_in_gross = strtoupper($tabelle);
    $zusatz = "_ID";
    $select_spaltenname = $spaltenname_in_gross . $zusatz;
    $result = DB::select("SELECT $select_spaltenname FROM $spaltenname_in_gross ORDER BY $select_spaltenname DESC LIMIT 0,1");
    foreach ($result as $row)
        return $row['$select_spaltenname'];
}

/* liefert true wenn datum1, kleiner als datum 2 */
function datum_kleiner($datum1, $datum2)
{
    $datum1 = str_replace('-', '', $datum1);
    $datum2 = str_replace('-', '', $datum2);
    /* 20080103 kleiner als 20101231 */
    if ($datum1 < $datum2) {
        return true;
    }
}

function detail_check($tab, $id)
{
    $result = DB::select("SELECT COUNT(*) AS ANZAHL FROM DETAIL WHERE DETAIL_ZUORDNUNG_TABELLE='$tab' && DETAIL_ZUORDNUNG_ID='$id' && DETAIL_AKTUELL='1'");
    return $result[0]['ANZAHL'];
}

function check_datum($datum)
{
    $tmp = explode(".", $datum);
    if (checkdate($tmp [1], $tmp [0], $tmp [2])) {
        return true;
    } else {
        return false;
    }
}

function einheit_name($id)
{
    $result = DB::select("SELECT EINHEIT_KURZNAME FROM EINHEIT WHERE EINHEIT_AKTUELL='1' && EINHEIT_ID = '$id' order by EINHEIT_DAT DESC limit 0,1");
    foreach ($result as $row)
        return $row['EINHEIT_KURZNAME'];
}

function mieternamen_als_string($mietvetrag_id)
{
    $mieternamen = mieterids_zum_vertrag($mietvetrag_id);
    return $mieternamen;
}

function mieterids_zum_vertrag($id)
{
    $result = DB::select("SELECT PERSON_MIETVERTRAG_PERSON_ID FROM PERSON_MIETVERTRAG where PERSON_MIETVERTRAG_MIETVERTRAG_ID='$id' && PERSON_MIETVERTRAG_AKTUELL='1'");

    $mieterids = array();
    foreach ($result as $row) {
        array_push($mieterids, "$row[PERSON_MIETVERTRAG_PERSON_ID]");
    }
    $mieternamen = [];
    foreach ($mieterids as $mieter) {
        $mietername = mieternamen_in_array($mieter);
        array_push($mieternamen, "$mietername");
    }
    $mieternamen = implode(",", $mieternamen);
    return $mieternamen;
}

function mieternamen_in_array($mieter_id)
{
    $result = DB::select("SELECT PERSON_NACHNAME, PERSON_VORNAME FROM PERSON where PERSON_ID='$mieter_id' order by PERSON_DAT DESC limit 0,1");
    foreach ($result as $row) {
        $mieter = " $row[PERSON_NACHNAME] $row[PERSON_VORNAME]";
        return $mieter;
    }
}

function mieter_anzahl($einheit_id)
{
    $datum_heute = date("Y-m-d");
    $result = DB::select("SELECT MIETVERTRAG_ID, MIETVERTRAG_VON, MIETVERTRAG_BIS, EINHEIT_ID FROM MIETVERTRAG where EINHEIT_ID='$einheit_id' && MIETVERTRAG_AKTUELL='1' && (MIETVERTRAG_BIS='0000-00-00' OR MIETVERTRAG_BIS>='$datum_heute') ORDER BY MIETVERTRAG_VON DESC LIMIT 0,1");
    if (empty($result)) {
        return "unvermietet";
    } else {
        foreach ($result as $row) {
            $mieter_im_vertrag = anzahl_mieter_im_vertrag($row['MIETVERTRAG_ID']);
            return $mieter_im_vertrag;
        }
    }
}

function einheit_kurzname($einheit_id)
{
    $result = DB::select("SELECT EINHEIT_KURZNAME FROM EINHEIT where EINHEIT_ID='$einheit_id' && EINHEIT_AKTUELL='1'");
    foreach ($result as $row)
        return $row['EINHEIT_KURZNAME'];
}

function einheit_id($mietvertrag_id)
{
    $result = DB::select("SELECT EINHEIT_ID FROM MIETVERTRAG where MIETVERTRAG_ID='$mietvertrag_id' && MIETVERTRAG_AKTUELL='1'");
    foreach ($result as $row)
        return $row['EINHEIT_ID'];
}

function haus_id($einheit_id)
{
    $result = DB::select("SELECT HAUS_ID FROM EINHEIT where EINHEIT_ID='$einheit_id' && EINHEIT_AKTUELL='1'");
    foreach ($result as $row)
        return $row['HAUS_ID'];
}

function haus_strasse_nr($haus_id)
{
    $result = DB::select("SELECT HAUS_STRASSE, HAUS_NUMMER FROM HAUS where HAUS_ID='$haus_id' && HAUS_AKTUELL='1'");
    foreach ($result as $row)
        $haus_info = "$row[HAUS_STRASSE] $row[HAUS_NUMMER]";
    if (!empty ($haus_info)) {
        return $haus_info;
    }
}

function getExtension($str)
{
    $i = strrpos($str, ".");
    if (!$i) {
        return "";
    }
    $l = strlen($str) - $i;
    $ext = substr($str, $i + 1, $l);
    return $ext;
}

function anzahl_mieter_im_vertrag($vertrag_id)
{
    $result = DB::select("SELECT COUNT(PERSON_MIETVERTRAG_PERSON_ID) AS ANZAHL FROM PERSON_MIETVERTRAG where PERSON_MIETVERTRAG_MIETVERTRAG_ID='$vertrag_id' && PERSON_MIETVERTRAG_AKTUELL='1'");
    return $result[0]['ANZAHL'];
}

function mieterid_zum_vertrag($id)
{
    $result = DB::select("SELECT PERSON_MIETVERTRAG_PERSON_ID FROM PERSON_MIETVERTRAG where PERSON_MIETVERTRAG_MIETVERTRAG_ID='$id' && PERSON_MIETVERTRAG_AKTUELL='1'");
    if (!empty($result)) {
        foreach ($result as $row) {
            mieternamen($row['PERSON_MIETVERTRAG_PERSON_ID']);
        }
    }
}

function mieternamen($mieter_id)
{
    $result = DB::select("SELECT PERSON_NACHNAME, PERSON_VORNAME FROM PERSON where PERSON_ID='$mieter_id' && PERSON_AKTUELL='1'");
    $mycounter = 0;
    foreach ($result as $row) {
        if ($mycounter == 0) {
            echo " $row[PERSON_NACHNAME] $row[PERSON_VORNAME] ";
            $mycounter++;
        } else {
            echo ", $row[PERSON_NACHNAME] $row[PERSON_VORNAME] ";
        }
    }
}

function personen_name($person_id)
{
    $result = DB::select("SELECT PERSON_NACHNAME, PERSON_VORNAME FROM PERSON where PERSON_ID='$person_id' && PERSON_AKTUELL='1' ORDER BY PERSON_DAT LIMIT 0,1");
    foreach ($result as $row) {
        return "$row[PERSON_NACHNAME] $row[PERSON_VORNAME] ";
    }
}

function vertrags_id($einheit_id)
{
    $heute = date("Y-m-d");
    $result = DB::select("SELECT MIETVERTRAG_ID FROM MIETVERTRAG where EINHEIT_ID='$einheit_id' && MIETVERTRAG_AKTUELL='1' && (MIETVERTRAG_BIS ='0000-00-00' OR MIETVERTRAG_BIS>=$heute) ORDER BY MIETVERTRAG_VON DESC LIMIT 0,1");
    foreach ($result as $row) {
        return $row['MIETVERTRAG_ID'];
    }
}

function iframe_start()
{
    echo "<div id=\"iframe_4\" class=\"iframe_1\">\n";
    echo "<div class=\"abstand_iframe\">\n";
    echo "<div class=\"scrollbereich\">\n";
    echo "<div class=\"scrollbarabstand\">\n";
}

function iframe_start_skaliert($breite, $hoehe)
{
    echo "<div id=\"iframe_4\" class=\"iframe_1\" style=\"width:$breite; height:$hoehe;\">\n";
    echo "<div class=\"abstand_iframe\">\n";
    echo "<div class=\"scrollbereich\">\n";
    echo "<div class=\"scrollbarabstand\">\n";
}

function iframe_end()
{
    echo "</div>\n";
    echo "</div>\n";
    echo "</div>\n";
    echo "</div>\n";
}

function erstelle_abschnitt($ueberschrift)
{
    echo "<div class='abschnitt'>";
    echo "<div class='heading'>$ueberschrift</div>";
    echo "<div class='body'>";
}

function ende_abschnitt()
{
    echo "</div></div>";
}

function personen_liste_alle()
{
    if (request()->has('person_finden')) {
        if (request()->input('suche_nach') == "Nachname") {
            $such_tabelle = "PERSON_NACHNAME";
        }
        if (request()->input('suche_nach') == "Vorname") {
            $such_tabelle = "PERSON_VORNAME";
        }
        $suchbegriff = request()->input('suchfeld');
        // echo "$such_tabelle=$suchbegriff";
        $db_abfrage = "SELECT PERSON_DAT, PERSON_ID, PERSON_NACHNAME, PERSON_VORNAME, PERSON_GEBURTSTAG FROM PERSON WHERE PERSON_AKTUELL='1' && $such_tabelle LIKE '$suchbegriff%' ORDER BY PERSON_NACHNAME ASC";
    } else {
        $db_abfrage = "SELECT PERSON_DAT, PERSON_ID, PERSON_NACHNAME, PERSON_VORNAME, PERSON_GEBURTSTAG FROM PERSON WHERE PERSON_AKTUELL='1' ORDER BY PERSON_NACHNAME ASC";
    }
    $result = DB::select($db_abfrage);

    if (!empty($result)) {
        echo "<table>";
        echo "<tr ><th>Personenliste</th><th  colspan=\"5\">";
        sprungmarken_links();
        echo "</th></tr>\n";
        // echo "</table>";
        // echo "<table>";
        echo "<tr><th >Nachname</th><th>Vorname</th><th>Anschrift</th><th>Einheit</th><th>Geburtstag</th><th>Zusatzinformationen</th></tr>\n";
        echo "</table>";
        iframe_start();
        echo "<table width=100% >";

        $counter = 0;
        $buchstaben = array();

        foreach ($result as $row) {
            $PERSON_GEBURTSTAG = date_mysql2german($row['PERSON_GEBURTSTAG']);
            $erster_buchstabe = substr($row['PERSON_NACHNAME'], 0, 1);

            if (!in_array($erster_buchstabe, $buchstaben)) {
                $buchstaben [] = $erster_buchstabe;
                $sprung_marke_link = "<a name=\"$erster_buchstabe\"><b>$row[PERSON_NACHNAME]</b></a>";
            } else {
                $sprung_marke_link = "$row[PERSON_NACHNAME]";
            }

            $counter++;
            $mietvertraege_arr = mietvertraege_ids_vom_mieter($row['PERSON_ID']);
            $anzahl_mv = count($mietvertraege_arr);

            $detail_check = detail_check("PERSON", $row['PERSON_ID']);
            $delete_link = "<a class=\"table_links\" href='" . route('legacy::personen::index', ['anzeigen' => 'person_loeschen', 'person_dat' => $row['PERSON_DAT']]) . "'>Löschen</a>";
            $aendern_link = "<a class=\"table_links\" href='" . route('legacy::personen::index', ['anzeigen' => 'person_aendern', 'person_id' => $row['PERSON_ID']]) . "'>Ändern</a>";
            $mietvertrag_link = "";
            $haus_info = "";
            $haus_info_link = "";

            if ($anzahl_mv > 0) {

                for ($i = 0; $i < $anzahl_mv; $i++) {
                    $mietvertrags_nr = $mietvertraege_arr [$i];
                    $mv = new mietvertraege ();
                    $mv->get_mietvertrag_infos_aktuell($mietvertrags_nr);
                    $einheit_id = einheit_id($mietvertrags_nr);
                    $einheit_kurzname = einheit_kurzname($einheit_id);
                    $haus_id = haus_id($einheit_id);
                    $haus_info = haus_strasse_nr($haus_id);
                    if ($mv->mietvertrag_aktuell) {
                        $haus_info_link .= "<a href='" . route('legacy::einheiten::index', ['einheit_raus' => 'einheit_kurz', 'haus_id' => $haus_id]) . "'>$haus_info</a><br>";

                        $mietvertrag_link .= "<a class=\"table_links\" href='" . route('legacy::mietkontenblatt::index', ['anzeigen' => 'mietkonto_uebersicht_detailiert', 'mietvertrag_id' => $mietvertrags_nr]) . "'>MIETKONTO</a><br>";
                        $einheit_link .= "<a class=\"table_links\" href='" . route('legacy::uebersicht::index', ['anzeigen' => 'einheit', 'einheit_id' => $einheit_id, 'mietvertrag_id' => $mietvertrags_nr]) . "'>$einheit_kurzname</a> <br>";
                    } else {
                        $haus_info_link .= "<a class=\"table_links_2\" href='" . route('legacy::einheiten::index', ['einheit_raus' => 'einheit_kurz', 'haus_id' => $haus_id]) . "'>$haus_info</a><br>";

                        $mietvertrag_link .= "<a class=\"table_links_2\" href='" . route('legacy::mietkontenblatt::index', ['anzeigen' => 'mietkonto_uebersicht_detailiert', 'mietvertrag_id' => $mietvertrags_nr]) . "'>MIETKONTO</a><br>";
                        $einheit_link .= "<a class=\"table_links_2\" href='" . route('legacy::uebersicht::index', ['anzeigen' => 'einheit', 'einheit_id' => $einheit_id, 'mietvertrag_id' => $mietvertrags_nr]) . "'>$einheit_kurzname</a> <br>";
                    }
                }
            } else {
                $mietvertrag_link = "Kein Mieter";
            }

            if ($detail_check > 0) {
                $detail_link = "<a class=\"table_links\" href='" . route('legacy::details::index', ['option' => 'details_anzeigen', 'detail_tabelle' => 'PERSON', 'detail_id' => $row['PERSON_ID']]) . "'>Details</a>";
            } else {
                $detail_link = "<a class=\"table_links\" href='" . route('legacy::details::index', ['option' => 'details_hinzu', 'detail_tabelle' => 'PERSON', 'detail_id' => $row['PERSON_ID']]) . "'>Neues Detail</a>";
            }

            if ($counter == 1) {
                echo "<tr class=\"zeile1\"><td width=15%>$sprung_marke_link</td><td width=15%>$row[PERSON_VORNAME]</td><td width=20%>$haus_info_link</td><td>$einheit_link</td><td>$mietvertrag_link </td><td width=10%>$row[PERSON_GEBURTSTAG]</td><td width=10%>$aendern_link</td><td width=10%>$delete_link</td><td width=10%>$detail_link</td></tr>";
                $mietvertrag_link = "";
                $einheit_link = "";
            }
            if ($counter == 2) {
                echo "<tr class=\"zeile2\"><td width=15%>$sprung_marke_link</td><td width=15%>$row[PERSON_VORNAME]</td><td width=20%>$haus_info_link</td><td>$einheit_link</td><td> $mietvertrag_link </td><td width=10%>$row[PERSON_GEBURTSTAG]</td><td width=10%>$aendern_link</td><td width=10%>$delete_link</td><td width=10%>$detail_link</td></tr>";
                $counter = 0;
                $mietvertrag_link = "";
                $einheit_link = "";
            }
        }
        iframe_end();
        echo "</table>";
    }
}

function sprungmarken_links()
{
    $buchstaben = array(
        "A",
        "B",
        "C",
        "D",
        "E",
        "F",
        "G",
        "H",
        "I",
        "J",
        "K",
        "L",
        "M",
        "N",
        "O",
        "P",
        "Q",
        "R",
        "S",
        "T",
        "U",
        "V",
        "W",
        "X",
        "Y",
        "Z"
    );
    $anzahl_buchstaben = count($buchstaben);
    for ($i = 0; $i < $anzahl_buchstaben; $i++) {
        echo "<a href=\"#$buchstaben[$i]\" >$buchstaben[$i]</a>&nbsp;";
    }
}

function einheiten_ids_by_objekt($objekt_id)
{
    $result = DB::select("SELECT HAUS_ID FROM HAUS where OBJEKT_ID='$objekt_id' && HAUS_AKTUELL='1'");
    $einheit_ids = [];
    foreach ($result as $row) {
        $result1 = DB::select("SELECT EINHEIT_ID, EINHEIT_KURZNAME FROM EINHEIT WHERE HAUS_ID='" . $row->HAUS_ID . "' && EINHEIT_AKTUELL='1' ORDER BY EINHEIT_KURZNAME ASC");
        foreach ($result1 as $row1)
            $einheit_ids [] = $row1;
    }
    $leerstand = array();
    foreach ($einheit_ids as $einheit_id) {
        $mietvertrag_exists = mietvertrag_anzahl_einheit("" . $einheit_id['EINHEIT_ID'] . "");
        $einheit_kurzname = einheit_kurzname($einheit_id['EINHEIT_ID']);
        if ($mietvertrag_exists == 0) {
            $leerstand [] = array(
                "EINHEIT_KURZNAME" => "$einheit_kurzname",
                "EINHEIT_ID" => "" . $einheit_id['EINHEIT_ID'] . ""
            );
        }
    }

    $leerstand = msort($leerstand, "EINHEIT_KURZNAME", false);
    $anzahl_leer = count($leerstand);
    for ($i = 0; $i < $anzahl_leer; $i++) {
        $link = "<a href='" . route('legacy::mietvertraege::index', ['mietvertrag_raus' => 'mietvertrag_neu', 'einheit_id' => $leerstand [$i] ['EINHEIT_ID']]);
        $link .= "'>" . $leerstand [$i] ['EINHEIT_KURZNAME'] . "</a>\n<br>\n";
        echo $link;
    }
}

function msort($array, $id = "id")
{
    $temp_array = array();
    while (count($array) > 0) {
        $lowest_id = 0;
        $index = 0;
        foreach ($array as $item) {
            if (isset ($item [$id]) && $array [$lowest_id] [$id]) {
                if ($item [$id] < $array [$lowest_id] [$id]) {
                    $lowest_id = $index;
                }
            }
            $index++;
        }
        $temp_array [] = $array [$lowest_id];
        $array = array_merge(array_slice($array, 0, $lowest_id), array_slice($array, $lowest_id + 1));
    }
    return $temp_array;
}

function personen_liste_multi()
{
    $result = DB::select("SELECT PERSON_DAT, PERSON_ID, PERSON_NACHNAME, PERSON_VORNAME, PERSON_GEBURTSTAG FROM PERSON WHERE PERSON_AKTUELL='1' ORDER BY PERSON_NACHNAME ASC");
    if (!empty($result)) {
        echo "<tr><td colspan=3><select class=\"personen_mv\" NAME=\"PERSON_ID[]\" MULTIPLE SIZE=25>\n";
        foreach ($result as $row) {
            echo "<option value=\"$row[PERSON_ID]\">$row[PERSON_NACHNAME] $row[PERSON_VORNAME] ($row[PERSON_GEBURTSTAG])</option>\n";
        }
        echo "</select></td></tr>\n";
    }
}

function mietvertrag_liste_string()
{
    $datum_heute = date("Y-m-d");
    $result = DB::select("SELECT MIETVERTRAG_ID FROM MIETVERTRAG where MIETVERTRAG_AKTUELL='1' && ((MIETVERTRAG_BIS='0000-00-00') OR (MIETVERTRAG_BIS<'$datum_heute'))");
    $mietervertrag_ids = array();
    foreach ($result as $row) {
        array_push($mietervertrag_ids, "$row[MIETVERTRAG_ID]");
    }
    $mietervertrag_ids_string = implode(",", $mietervertrag_ids);
    return $mietervertrag_ids_string;
}

function personen_ids_der_mieter()
{
    $vertrags_ids_string = mietvertrag_liste_string();
    $vertrags_ids_array = explode(",", $vertrags_ids_string);
    $anzahl_vertraege = count($vertrags_ids_array);
    $mieterids = array();
    for ($a = 0; $a < $anzahl_vertraege; $a++) {
        $$result = DB::select("SELECT PERSON_MIETVERTRAG_PERSON_ID FROM PERSON_MIETVERTRAG where PERSON_MIETVERTRAG_MIETVERTRAG_ID='$vertrags_ids_array[$a]' && PERSON_MIETVERTRAG_AKTUELL='1'");
        foreach ($result as $row) {
            array_push($mieterids, "$row[PERSON_MIETVERTRAG_PERSON_ID]");
        }
    } // for end
    $mieterids = array_unique($mieterids); // doppelte personen ids entfernen
    $mieter_ids_string = implode(",", $mieterids);
    return $mieter_ids_string;
}

function mieternamen_in_string($mieter_id)
{
    $result = DB::select("SELECT PERSON_NACHNAME, PERSON_VORNAME FROM PERSON where PERSON_ID='$mieter_id' order by PERSON_DAT DESC limit 0,1");
    foreach ($result as $row) {
        $mieter = "$row[PERSON_NACHNAME] $row[PERSON_VORNAME]";
        return $mieter;
    }
}

function mieternamen_liste_alle()
{
    $person_ids_string = personen_ids_der_mieter();
    $person_ids_array = explode(",", $person_ids_string);
    $anzahl_mieter = count($person_ids_array);

    $mieter_liste = array();
    $mieter_liste1 = array();
    echo "<table width=100%>\n";
    echo "<tr class=\"feldernamen\"><td colspan=3>Mieterliste</td></tr>\n";
    echo "<tr class=\"feldernamen\"><td>Namen</td><td>Vertrag</td><td>Info</td></tr>\n";
    for ($a = 0; $a < $anzahl_mieter; $a++) {
        $mieternamen = mieternamen_in_string($person_ids_array [$a]);
        array_push($mieter_liste, "$mieternamen");
        $mieter_liste1 [$a] ['personen_id'] = $person_ids_array [$a];
        $mieter_liste1 [$a] ['namen'] = $mieternamen;

        $mieter_vertrag_string = mietvertrag_id_vom_mieter($person_ids_array [$a]);
        $mieter_vertraege = explode(",", $mieter_vertrag_string);
        $anz_vertraege = count($mieter_vertraege);
        // echo $anz_vertraege;
        $mieter_liste1 [$a] ['vertrags_anzahl'] = $anz_vertraege;
        for ($i = 0; $i < $anz_vertraege; $i++) {
            $mieter_liste1 [$a] ['vertrags_id'] [$i] = $mieter_vertraege [$i];
        }
    }
    sort($mieter_liste);
    sort($mieter_liste1);

    usort($mieter_liste1, "cmp");

    // print_r($mieter_liste);
    // echo "<pre>";
    // print_r($mieter_liste1);
    // echo "</pre>";
    $anz = count($mieter_liste1);

    $anzahl_mieter_in_liste = count($mieter_liste1);
    $counter = 0;
    for ($a = 0; $a < $anzahl_mieter_in_liste; $a++) {
        $counter++;
        $detail_check = detail_check("PERSON", $mieter_liste1 [$a] ['personen_id']);
        $mid = $mieter_liste1 [$a] ['personen_id'];
        if ($detail_check > 0) {
            $detail_link = "<a class=\"table_links\" href='" . route('legacy::details::index', ['option' => 'details_anzeigen', 'detail_tabelle' => 'PERSON', 'detail_id' => $mid]) . "'>Details</a>";
        } else {
            $detail_link = "<a class=\"table_links\" href='" . route('legacy::details::index', ['option' => 'details_hinzu', 'detail_tabelle' => 'PERSON', 'detail_id' => $mid]) . "'>Neues Detail</a>";
        }

        if ($counter == 1) {
            for ($b = 0; $b < $mieter_liste1 [$a] ['vertrags_anzahl']; $b++) {
                $akt_vertrag_id = $mieter_liste1 [$a] ['vertrags_id'] [$b];
                $vertrag_detail_check = detail_check("MIETVERTRAG", $akt_vertrag_id);
                $einheit_id = einheit_id($akt_vertrag_id);
                $einheit_kurzname = einheit_kurzname($einheit_id);
                if ($vertrag_detail_check > 0) {
                    $vertrags_link = "<a href='" . route('legacy::details::index', ['option' => 'details_anzeigen', 'detail_tabelle' => 'MIETVERTRAG', 'detail_id' => $akt_vertrag_id]) . "'>Vertrag:$akt_vertrag_id</a>&nbsp;\n";
                } else {
                    $vertrags_link = "Vertrag: $einheit_kurzname \n";
                }
                $namen_link = "<a href='" . route('legacy::uebersicht::index', ['anzeigen' => 'einheit', 'einheit_id' => $einheit_id]) . "'>$mieter_liste1[$a][namen]</a>";
                echo "<tr class=\"zeile1\"><td>$namen_link</td>";
                echo "<td>$vertrags_link</td><td>$detail_link</td></tr>\n";
            }
        }

        if ($counter == 2) {
            echo "<tr class=\"zeile1\"><td>$namen_link</td>";;
            echo "<td>";
            // echo $mieter_liste1[$a][vertrags_anzahl];
            for ($b = 0; $b < $mieter_liste1 [$a] ['vertrags_anzahl']; $b++) {
                $akt_vertrag_id = $mieter_liste1 [$a] ['vertrags_id'] [$b];
                $vertrag_detail_check = detail_check("MIETVERTRAG", $akt_vertrag_id);
                $einheit_id = einheit_id($akt_vertrag_id);
                $einheit_kurzname = einheit_kurzname($einheit_id);
                if ($vertrag_detail_check > 0) {
                    $vertrags_link = "<a href='" . route('legacy::details::index', ['option' => 'details_anzeigen', 'detail_tabelle' => 'MIETVERTRAG', 'detail_id' => $akt_vertrag_id]) . "'>$einheit_kurzname Vertrag:$akt_vertrag_id</a>&nbsp;\n";
                } else {
                    $vertrags_link = "Vertrag: $einheit_kurzname &nbsp;\n";
                }
                // echo $mieter_liste1[$a][vertrags_id][$b];
                echo "$vertrags_link";
            }
            echo "</td><td>$detail_link</td></tr>\n";

            $counter = 0;
        }
    }
    echo "</table>";
}

function cmp($a, $b)
{
    return strcmp($a ["namen"], $b ["namen"]);
}

function mietvertrag_id_vom_mieter($mieter_id)
{
    $result = DB::select("SELECT PERSON_MIETVERTRAG_MIETVERTRAG_ID FROM PERSON_MIETVERTRAG where PERSON_MIETVERTRAG_PERSON_ID='$mieter_id' && PERSON_MIETVERTRAG_AKTUELL='1'");
    $mietvertraege = array();
    foreach ($result as $row) {
        array_push($mietvertraege, "$row[PERSON_MIETVERTRAG_MIETVERTRAG_ID]");
    }
    $mietervertrag_ids_string = implode(",", $mietvertraege);
    return $mietervertrag_ids_string;
}

function mietvertraege_ids_vom_mieter($mieter_id)
{
    $datum_heute = date("Y-m-d");
    $result = DB::select("SELECT PERSON_MIETVERTRAG_MIETVERTRAG_ID FROM PERSON_MIETVERTRAG where PERSON_MIETVERTRAG_PERSON_ID='$mieter_id' && PERSON_MIETVERTRAG_AKTUELL='1' ORDER BY PERSON_MIETVERTRAG_MIETVERTRAG_ID DESC");
    $mietvertraege = array();
    foreach ($result as $row) {
        array_push($mietvertraege, "$row[PERSON_MIETVERTRAG_MIETVERTRAG_ID]");
    }
    return $mietvertraege;
}

function date_mysql2german($date)
{
    $d = explode("-", $date);
    return sprintf("%02d.%02d.%04d", $d [2], $d [1], $d [0]);
}

function date_german2mysql($date)
{
    $d = explode(".", $date);
    return sprintf("%04d-%02d-%02d", $d [2], $d [1], $d [0]);
}

function letzte_person_id()
{
    $result = DB::select("SELECT PERSON_ID FROM PERSON ORDER BY PERSON_ID DESC LIMIT 0,1");
    foreach ($result as $row)
        return $row['PERSON_ID'];
}

function letzte_person_dat_of_person_id($person_id)
{
    $result = DB::select("SELECT PERSON_DAT FROM PERSON WHERE PERSON_ID='$person_id' ORDER BY PERSON_DAT DESC LIMIT 0,1");
    foreach($result as $row)
        return $row['PERSON_DAT'];
}

function letzte_objekt_dat_of_objekt_id($objekt_id)
{
    $result = DB::select("SELECT OBJEKT_DAT FROM OBJEKT WHERE OBJEKT_ID='$objekt_id' ORDER BY OBJEKT_DAT DESC LIMIT 0,1");
    foreach($result as $row)
        return $row['OBJEKT_DAT'];
}

function letzte_haus_dat_of_haus_id($haus_id)
{
    $result = DB::select("SELECT HAUS_DAT FROM HAUS WHERE HAUS_ID='$haus_id' ORDER BY HAUS_DAT DESC LIMIT 0,1");
    foreach($result as $row)
        return $row['HAUS_DAT'];
}

function letzte_einheit_dat_of_einheit_id($einheit_id)
{
    $result = DB::select("SELECT EINHEIT_DAT FROM EINHEIT WHERE EINHEIT_ID='$einheit_id' ORDER BY EINHEIT_DAT DESC LIMIT 0,1");
    foreach($result as $row)
        return $row['EINHEIT_DAT'];
}

function objekt_kurzname_finden($objekt_dat)
{
    $result = DB::select("SELECT OBJEKT_KURZNAME FROM OBJEKT WHERE OBJEKT_DAT='$objekt_dat' && OBJEKT_AKTUELL='1' ORDER BY OBJEKT_DAT DESC LIMIT 0,1");
    foreach($result as $row)
        return $row['OBJEKT_KURZNAME'];
}

function letzte_mietvertrag_dat_of_mietvertrag_id($mietvertrag_id)
{
    $result = DB::select("SELECT MIETVERTRAG_DAT FROM MIETVERTRAG WHERE MIETVERTRAG_ID='$mietvertrag_id' ORDER BY MIETVERTRAG_DAT DESC LIMIT 0,1");
    foreach($result as $row)
        return $row['MIETVERTRAG_DAT'];
}

function letzte_person_mietvertrag_dat_by_mietvertrags_id($person_id, $PERSON_MIETVERTRAG_MIETVERTRAG_ID)
{
    $result = DB::select("SELECT PERSON_MIETVERTRAG_DAT FROM PERSON_MIETVERTRAG WHERE PERSON_MIETVERTRAG_MIETVERTRAG_ID='$PERSON_MIETVERTRAG_MIETVERTRAG_ID' && PERSON_MIETVERTRAG_PERSON_ID='$person_id' ORDER BY PERSON_MIETVERTRAG_DAT DESC LIMIT 0,1");
    foreach($result as $row)
        return $row['PERSON_MIETVERTRAG_DAT'];
}

function letzte_detail_dat($tabelle, $zuordnungs_id)
{
    $result = DB::select("SELECT DETAIL_DAT FROM DETAIL WHERE DETAIL_ZUORDNUNG_TABELLE='$tabelle' && DETAIL_ZUORDNUNG_ID='$zuordnungs_id' ORDER BY DETAIL_DAT DESC LIMIT 0,1");
    foreach($result as $row)
        return $row['DETAIL_DAT'];
}

function letzte_person_mietvertrag_dat()
{
    $result = DB::select("SELECT PERSON_MIETVERTRAG_DAT FROM PERSON_MIETVERTRAG ORDER BY PERSON_MIETVERTRAG_DAT DESC LIMIT 0,1");
    foreach($result as $row)
        return $row['PERSON_MIETVERTRAG_DAT'];
}

function letzte_person_mietvertrag_id()
{
    $result = DB::select("SELECT PERSON_MIETVERTRAG_ID FROM PERSON_MIETVERTRAG ORDER BY PERSON_MIETVERTRAG_ID DESC LIMIT 0,1");
    foreach($result as $row)
        return $row['PERSON_MIETVERTRAG_ID'];
}

function fehlermeldung_ausgeben($text)
{
    echo "<p class=\"fehlermeldung\">$text</p>\n";
}

function hinweis_ausgeben($text)
{
    echo "<p class=\"hinweis\">$text</p>\n";
}

function warnung_ausgeben($text)
{
    echo "<p class=\"warnung\">$text</p>\n";
}

function person_pruefen($nachname, $vorname, $geburtstag)
{
    $db_abfrage = "SELECT PERSON_ID, PERSON_NACHNAME, PERSON_VORNAME, PERSON_GEBURTSTAG FROM PERSON WHERE PERSON_NACHNAME='$nachname' && PERSON_VORNAME='$vorname' && PERSON_GEBURTSTAG='$geburtstag' && PERSON_AKTUELL='1' ORDER BY PERSON_ID ASC";
    $result = DB::select($db_abfrage);
    if (empty($result)) {
        $result = DB::select("SELECT PERSON_ID, PERSON_NACHNAME, PERSON_VORNAME, PERSON_GEBURTSTAG FROM PERSON WHERE PERSON_NACHNAME='$nachname' && PERSON_VORNAME='$vorname' && PERSON_GEBURTSTAG='$geburtstag' && PERSON_AKTUELL='1' ORDER BY PERSON_ID ASC");
        if (!empty($result)) {
            foreach($result as $row) {
                echo "$row[PERSON_ID], $row[PERSON_NACHNAME], $row[PERSON_VORNAME], $row[PERSON_GEBURTSTAG] ";
            }
            return "error";
        }
    } else {
        hinweis_ausgeben("Person existiert!!!<br>Ihre Eingaben sind 100%-ig identisch mit folgenden Datenbankeinträgen:");
        foreach($result as $row) {
            echo "$row[PERSON_ID], $row[PERSON_NACHNAME], $row[PERSON_VORNAME], $row[PERSON_GEBURTSTAG] <br>";
        }
        return "error";
    }
}

function person_loeschen($person_dat)
{
    DB::update("UPDATE PERSON SET PERSON_AKTUELL='0' WHERE PERSON_DAT='$person_dat'");

    $dat_alt = $person_dat; // person wurde gelöscht DAT_ALT = DAT_NEU im Protokoll
    $dat_neu = $person_dat;
    protokollieren('PERSON', $dat_neu, $dat_alt);

    hinweis_ausgeben("Person gelöscht!");
    echo "<a href='" . route('legacy::personen::index', ['anzeigen' => 'alle_personen']) . "'>Zurück zu Personenliste</a>";
}

function person_aendern_in_db($person_id)
{
    DB::update("UPDATE PERSON SET PERSON_AKTUELL='0' WHERE PERSON_ID='$person_id'");
    $dat_alt = letzte_person_dat_of_person_id($person_id);

    $gebdatum = request()->input('person_geburtstag');
    $gebdatum = date_german2mysql($gebdatum);
    $akt_person_id = $person_id;
    DB::insert("INSERT INTO PERSON (`PERSON_DAT`, `PERSON_ID`, `PERSON_NACHNAME`, `PERSON_VORNAME`, `PERSON_GEBURTSTAG`, `PERSON_AKTUELL`) VALUES (NULL, '$akt_person_id', '" . request()->input('person_nachname') . "', '" . request()->input('person_vorname') . "', date_format( '$gebdatum', '%Y-%m-%d' ), '1')");

    $dat_neu = letzte_person_dat_of_person_id($akt_person_id);
    protokollieren('PERSON', $dat_neu, $dat_alt);
}

function weiterleiten($ziel)
{
    header("Location: $ziel");
}

function weiterleiten_alt($ziel)
{
    $wartezeit = "2";
    echo "<head>";
    echo "<meta http-equiv=\"refresh\" content=\"$wartezeit; URL=$ziel\">";
    echo "</head>";
}

function weiterleiten_in_sec($ziel, $sec)
{
    echo "<head>";
    echo "<meta http-equiv=\"refresh\" content=\"$sec; URL=$ziel\">";
    echo "</head>";
}

function post_array_bereinigen()
{
    foreach (request()->all() as $key => $value) {
        $clean_value = trim(strip_tags($value));
        $clean_arr [$key] = "$clean_value";
    }
    return $clean_arr;
}

function post_unterarray_bereinigen($arrayname)
{
    foreach (request()->input($arrayname) as $key => $value) {
        $clean_value = trim(strip_tags($value));
        $clean_arr [$key] = "$clean_value";
    }
    return $clean_arr;
}

function umbruch_entfernen($string)
{
    $new = str_replace("\r\n", " ", $string);
    $new = str_replace("\r", " ", $new);
    $new = str_replace("\n", " ", $new);
    $new = str_replace("<br>", " ", $new);
    $new = str_replace("<br \>", " ", $new);
    $new = str_replace("<br\>", " ", $new);
    return $new;
}

// ## Funktion zur Eintragung der Person mit Datenprüfung.
function person_in_db_eintragen()
{
    $gebdatum = request()->input('person_geburtstag');
    $gebdatum = date_german2mysql($gebdatum);
    $letzte_person_id = letzte_person_id();
    $akt_person_id = $letzte_person_id + 1;
    // echo $gebdatum;
    $person_status = person_pruefen(request()->input('person_nachname'), request()->input('person_vorname'), $gebdatum);
    if ($person_status != "error") {
        $dat_alt = letzte_person_dat_of_person_id($akt_person_id);
        DB::insert("INSERT INTO PERSON (`PERSON_DAT`, `PERSON_ID`, `PERSON_NACHNAME`, `PERSON_VORNAME`, `PERSON_GEBURTSTAG`, `PERSON_AKTUELL`) VALUES (NULL, '$akt_person_id', '" . request()->input('person_nachname') . "', '" . request()->input('person_vorname') . "', '$gebdatum', '1')");
        $dat_neu = letzte_person_dat_of_person_id($akt_person_id);
        protokollieren('PERSON', $dat_neu, $dat_alt);
        hinweis_ausgeben("Person: " . request()->input('person_nachname') . " " . request()->input('person_vorname') . " wurde eingetragen !");
        backlink();
    } else {
        warnung_ausgeben("Person existiert!");
        backlink();
        person_hidden_form(request()->input('person_nachname'), request()->input('person_vorname'), request()->input('person_geburtstag'));
    }
}

// ## Funktion zur Eintragung der Person, obwohl gleichnamige existieren.
function person_in_db_eintragen_direkt()
{
    $gebdatum = request()->input('person_geburtstag');
    $gebdatum = date_german2mysql($gebdatum);
    $letzte_person_id = letzte_person_id();
    $akt_person_id = $letzte_person_id + 1;
    $dat_alt = letzte_person_dat_of_person_id($akt_person_id);

    DB::insert("INSERT INTO PERSON (`PERSON_DAT`, `PERSON_ID`, `PERSON_NACHNAME`, `PERSON_VORNAME`, `PERSON_GEBURTSTAG`, `PERSON_AKTUELL`) VALUES (NULL, '$akt_person_id', '" . request()->input('person_nachname') . "', '" . request()->input('person_vorname') . "', '$gebdatum', '1')");
    $dat_neu = letzte_person_dat_of_person_id($akt_person_id);
    protokollieren('PERSON', $dat_neu, $dat_alt);
    hinweis_ausgeben("Person: " . request()->input('person_nachname') . " " . request()->input('person_vorname') . " wurde eingetragen !");
    backlink();
}

// ###mietvertrag
function mietvertrag_anlegen($von, $bis, $einheit_id)
{
    $akt_mietvertrag_id = mietvertrag_id_letzte();
    $akt_mietvertrag_id = $akt_mietvertrag_id + 1;
    $von = date_german2mysql($von);
    $bis = date_german2mysql($bis);
    $dat_alt = letzte_mietvertrag_dat_of_mietvertrag_id($akt_mietvertrag_id);
    DB::insert("INSERT INTO MIETVERTRAG (`MIETVERTRAG_DAT`, `MIETVERTRAG_ID`, `MIETVERTRAG_VON`, `MIETVERTRAG_BIS`, `EINHEIT_ID`, `MIETVERTRAG_AKTUELL`) VALUES (NULL, '$akt_mietvertrag_id', '$von', '$bis', '$einheit_id', '1')");
    $dat_neu = letzte_mietvertrag_dat_of_mietvertrag_id($akt_mietvertrag_id);
    protokollieren('MIETVERTRAG', $dat_neu, $dat_alt);
    return $akt_mietvertrag_id;
}

function mietvertrag_id_letzte()
{
    $result = DB::select("SELECT MIETVERTRAG_ID FROM MIETVERTRAG ORDER BY MIETVERTRAG_ID DESC LIMIT 0,1");
    foreach($result as $row) {
        return $row['MIETVERTRAG_ID'];
    }
}

function mietvertrag_id_by_dat($mietvertrag_dat)
{
    $result = DB::select("SELECT MIETVERTRAG_ID FROM MIETVERTRAG WHERE MIETVERTRAG_DAT='$mietvertrag_dat'");
    foreach($result as $row) {
        return $row['MIETVERTRAG_ID'];
    }
}

function einheit_id_by_mietvertrag($mietvertrag_dat)
{
    $result = DB::select("SELECT EINHEIT_ID FROM MIETVERTRAG WHERE MIETVERTRAG_DAT='$mietvertrag_dat'");
    foreach($result as $row) {
        return $row['EINHEIT_ID'];
    }
}

function bereinige_string($my_str)
{
    // alle html und php codes entfernen
    $sauber = strip_tags($my_str);
    return $sauber;
}

function br2n($my_str)
{
    // alle html und php codes entfernen
    $sauber = str_replace('<br>', "\n", $my_str);
    $sauber1 = str_replace('<br />', "\n", $sauber);
    return $sauber1;
}

function mietvertrag_by_einheit($einheit_id)
{
    $result = DB::select("SELECT MIETVERTRAG_ID FROM MIETVERTRAG WHERE EINHEIT_ID='$einheit_id' && MIETVERTRAG_AKTUELL='1' order by MIETVERTRAG_ID DESC limit 0,1");
    foreach($result as $row) {
        return $row['MIETVERTRAG_ID'];
    }
}

function mietvertrag_anzahl_einheit($einheit_id)
{
    $datum_heute = date("Y-m-d");
    $result = DB::select("SELECT COUNT(MIETVERTRAG_ID) AS ANZAHL FROM MIETVERTRAG WHERE EINHEIT_ID='$einheit_id' && MIETVERTRAG_AKTUELL='1' && ((MIETVERTRAG_BIS = '0000-00-00') OR (MIETVERTRAG_BIS > '$datum_heute')) order by MIETVERTRAG_ID DESC limit 0,1");
    return $result[0]['ANZAHL'];
}

// ##person zu mietvertrag
function person_zu_mietvertrag($person_id, $mietvertrag_id)
{
    $letzte_pm_id = letzte_person_mietvertrag_id();
    $letzte_pm_id = $letzte_pm_id + 1;
    $dat_alt = letzte_person_mietvertrag_dat_by_mietvertrags_id($person_id, $mietvertrag_id);
    DB::insert("INSERT INTO PERSON_MIETVERTRAG (`PERSON_MIETVERTRAG_DAT`, `PERSON_MIETVERTRAG_ID`, `PERSON_MIETVERTRAG_PERSON_ID`, `PERSON_MIETVERTRAG_MIETVERTRAG_ID`, `PERSON_MIETVERTRAG_AKTUELL`) VALUES (NULL, '$letzte_pm_id', '$person_id', '$mietvertrag_id', '1')");
    $dat_neu = letzte_person_mietvertrag_dat_by_mietvertrags_id($person_id, $mietvertrag_id);
    protokollieren('PERSON_MIETVERTRAG', $dat_neu, $dat_alt);
}

function array_sortByIndex($array, $index, $order = SORT_ASC, $natsort = FALSE, $case_sensitive = FALSE)
{
    if (is_array($array) and (count($array) > 0)) {
        foreach (array_keys($array) as $key) {
            $temp [$key] = $array [$key] [$index];
        }
        if (!$natsort) {
            if ($order == SORT_ASC) {
                asort($temp);
            } else {
                arsort($temp);
            }
        } else {
            if ($case_sensitive) {
                natsort($temp);
            } else {
                natcasesort($temp);
            }
            if ($order != SORT_ASC) {
                $temp = array_reverse($temp, TRUE);
            }
        }
        foreach (array_keys($temp) as $key) {
            if (is_numeric($key)) {
                $sorted [] = $array [$key];
            } else {
                $sorted [$key] = $array [$key];
            }
        }
        return $sorted;
    }
    return $array;
    // Beispiel für ein Array $sx mit den Spalten $sx['dat'], $sx['name'], $sx['id'].
    // $arrSXsorted = array_sortByIndex($sx,'dat');
}

function array_orderby()
{
    $args = func_get_args();
    $data = array_shift($args);
    foreach ($args as $n => $field) {
        if (is_string($field)) {
            $tmp = array();
            foreach ($data as $key => $row)
                $tmp [$key] = $row [$field];
            $args [$n] = $tmp;
        }
    }
    $args [] = &$data;
    call_user_func_array('array_multisort', $args);
    return array_pop($args);
}

// $sorted = array_orderby($data, 'volume', SORT_DESC, 'edition', SORT_ASC);
function array_msort_old($array, $cols)
{
    $colarr = array();
    foreach ($cols as $col => $order) {
        $colarr [$col] = array();
        foreach ($array as $k => $row) {
            $colarr [$col] ['_' . $k] = strtolower($row [$col]);
        }
    }
    $params = array();
    foreach ($cols as $col => $order) {
        $params [] = &$colarr [$col];
        $params = array_merge($params, ( array )$order);
    }
    call_user_func_array('array_multisort', $params);
    $ret = array();
    $keys = array();
    $first = true;
    foreach ($colarr as $col => $arr) {
        foreach ($arr as $k => $v) {
            if ($first) {
                $keys [$k] = substr($k, 1);
            }
            $k = $keys [$k];
            if (!isset ($ret [$k]))
                $ret [$k] = $array [$k];
            $ret [$k] [$col] = $array [$k] [$col];
        }
        $first = false;
    }
    return $ret;
}

// $arr2 = array_msort($arr1, array('name'=>array(SORT_DESC,SORT_REGULAR), 'cat'=>SORT_ASC));
function array_msort($array, $cols)
{
    $colarr = array();
    foreach ($cols as $col => $order) {
        $colarr [$col] = array();
        foreach ($array as $k => $row) {
            $colarr [$col] ['_' . $k] = strtolower($row [$col]);
        }
    }
    $params = array();
    foreach ($cols as $col => $order) {

        $params [] = &$colarr [$col];
        $order = ( array )$order;
        foreach ($order as $order_element) {
            // pass by reference, as required by php 5.3
            $params [] = &$order_element;
        }
    }
    call_user_func_array('array_multisort', $params);
    $ret = array();
    $keys = array();
    $first = true;
    foreach ($colarr as $col => $arr) {
        foreach ($arr as $k => $v) {
            if ($first) {
                $keys [$k] = substr($k, 1);
            }
            $k = $keys [$k];

            if (!isset ($ret [$k])) {
                $ret [$k] = $array [$k];
            }

            $ret [$k] [$col] = $array [$k] [$col];
        }
        $first = false;
    }
    return $ret;
}

function isNumeric($num)
{
    return preg_match("/[0-9]+/", $num);
}

function punkt_zahl($zahl)
{
    if (preg_match("/,/i", "$zahl")) {
        return nummer_komma2punkt($zahl);
    }
    if (preg_match("/./i", "$zahl")) {
        return $zahl;
    }
}

function nummer_komma2punkt($nummer)
{
    /* erst Punkte entfernen */
    $zahl = str_replace(".", "", $nummer);
    $zahl = str_replace(",", ".", $zahl);
    // $zahl = sprintf("%01.2f",$zahl);
    // $zahl = number_format($zahl, 2,'.','');

    /*
     * if( !is_float($nummer) ) {
     * list($int, $frac) = explode(',', $nummer);
     * $zahl = floatval($int.'.'.$frac);
     * }
     */
    return $zahl;
}

function nummer_punkt2komma($nummer)
{
    $nummer = sprintf("%01.2f", $nummer);
    list ($int, $frac) = explode('.', $nummer);
    $zahl = $int . ',' . $frac;
    return $zahl;
}

function nummer_punkt2komma_t($nummer)
{
    /*
     * $nummer = sprintf("%01.2f",$nummer);
     * list($int, $frac) = explode('.', $nummer);
     * $anz_ziffern = len($int);
     * $int_arr = explode()
     * $zahl = $int.','.$frac;
     */
    $zahl = number_format($nummer, 2, ',', '.');
    return $zahl;
}

function nummer_runden($zahl, $nachkommastellen)
{
    $nachkommastellen = $nachkommastellen . 'f';
    $nummer = sprintf("%01.$nachkommastellen", $zahl);
    return $nummer;
}

function nummer_kuerzen($zahl, $nachkommastellen)
{
    $nummer_arr = explode('.', $zahl);
    $vorkomma = $nummer_arr [0];
    $nachkomma = $nummer_arr [1];
    $nachkomma = substr($nachkomma, 0, $nachkommastellen);

    $nummer = "$vorkomma.$nachkomma";
    return $nummer;
}

function array_als_tabelle_anzeigen($my_array, $ueberschrift_felder_arr)
{
    /*
     * $ueberschrift_felder_arr[0] = "Konto";
     * $ueberschrift_felder_arr[1] = "Bezeichnung";
     * $ueberschrift_felder_arr[2] = "Gruppe";
     */
    echo "<table class=rechnungen>";
    echo "<tr class=feldernamen>";

    $anzahl_spalten = count($my_array [0]);
    $anzahl_felder = count($ueberschrift_felder_arr);
    // echo "$anzahl_spalten $anzahl_felder";
    foreach ($ueberschrift_felder_arr as $key => $value) {
        echo "<td>$value</td>";
    }
    echo "</tr>";

    for ($a = 0; $a < count($my_array); $a++) {
        echo "<tr>";

        foreach ($my_array [$a] as $key => $value) {
            echo "<td>$value</td>";
        }

        echo "</tr>";
    }

    echo "</table>";
}

function letzter_tag_im_monat($monat, $jahr)
{
    $letzter_tag = date("t", mktime(0, 0, 0, $monat, 1, $jahr));
    return $letzter_tag;
}

function monat2name($monat, $lang = 'de')
{
    $len = strlen($monat);
    if ($len == 1) {
        $monat = '0' . $monat;
    }

    if ($lang == 'de') {
        if ($monat == '01') {
            return 'Januar';
        }
        if ($monat == '02') {
            return 'Februar';
        }
        if ($monat == '03') {
            return 'März';
        }
        if ($monat == '04') {
            return 'April';
        }
        if ($monat == '05') {
            return 'Mai';
        }
        if ($monat == '06') {
            return 'Juni';
        }
        if ($monat == '07') {
            return 'Juli';
        }
        if ($monat == '08') {
            return 'August';
        }
        if ($monat == '09') {
            return 'September';
        }
        if ($monat == '10') {
            return 'Oktober';
        }
        if ($monat == '11') {
            return 'November';
        }
        if ($monat == '12') {
            return 'Dezember';
        }
    }
    if ($lang == 'en') {
        if ($monat == '01') {
            return 'January';
        }
        if ($monat == '02') {
            return 'February';
        }
        if ($monat == '03') {
            return 'March';
        }
        if ($monat == '04') {
            return 'April';
        }
        if ($monat == '05') {
            return 'May';
        }
        if ($monat == '06') {
            return 'June';
        }
        if ($monat == '07') {
            return 'July';
        }
        if ($monat == '08') {
            return 'August';
        }
        if ($monat == '09') {
            return 'September';
        }
        if ($monat == '10') {
            return 'October';
        }
        if ($monat == '11') {
            return 'November';
        }
        if ($monat == '12') {
            return 'December';
        }
    }
}
