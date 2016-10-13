<?php

function erstelle_formular($name, $action)
{
    if (!isset ($action)) {
        echo "<form name=\"$name\" action=\"$self\"  method=\"post\">\n";
    } else {
        echo "<form name=\"$name\" action=\"$action\" method=\"post\">\n";
    }

    echo "<table class=\"formular_tabelle\">\n<tr><td>";
    // $self = $_SERVER['PHP_SELF'];
    $scriptname = $_SERVER ['REQUEST_URI'];
    $servername = $_SERVER ['SERVER_NAME'];
    $serverport = $_SERVER ['SERVER_PORT'];
    $https = $_SERVER ['HTTPS'];

    if (isset($https) && $https !== 'off') {
        $self = "https://$servername:$serverport$scriptname";
    } else {
        $self = "http://$servername:$serverport$scriptname";
    }
    echo "</td></tr>\n";
}

function ende_formular()
{
    echo "</table></form>\n";
}

function erstelle_hiddenfeld($name, $wert)
{
    echo "<input type=\"hidden\" name=\"$name\" value=\"$wert\">\n";
}

function erstelle_button($name, $wert, $onclick)
{
    echo "<input type=\"button\" name=\"$name\" value=\"$wert\" onclick=\"\">";
}

function erstelle_back_button()
{
    echo "<input type=\"button\" name=\"zurueck\" value=\"Abbrechen und Zurück\" onclick=\"javascript:history.back()\" class=\"buttons\">";
}

function erstelle_eingabefeld($beschreibung, $name, $wert, $size)
{
    echo "<tr><td>$beschreibung:</td><td><input type=\"text\" name=\"$name\" value=\"$wert\" size=\"$size\"></td></tr>\n";
}

function erstelle_submit_button($name, $wert)
{
    echo "<tr><td colspan=2><input type=\"submit\" name=\"$name\" value=\"$wert\" class=\"buttons\">";
    erstelle_back_button();
    echo "</td></tr>\n";
}

function erstelle_submit_button_nur($name, $wert)
{
    echo "<tr><td colspan=2><button class='btn waves-effect waves-light' type='submit' name='$name' value='$wert'>$wert<i class=\"material-icons right\">send</i></button>";
    echo "</td></tr>\n";
}

function objekt_kurzname_anzahl($kurzname)
{
    $result = DB::select("SELECT COUNT(OBJEKT_KURZNAME) AS ANZAHL FROM OBJEKT WHERE OBJEKT_KURZNAME LIKE '$kurzname' && OBJEKT_AKTUELL='1'");
    return $result[0]['ANZAHL'];
}

function letzte_obj_id()
{
    $result = DB::select("SELECT OBJEKT_ID FROM OBJEKT ORDER BY OBJEKT_ID DESC LIMIT 0,1");
    foreach ($result as $row)
        return $row['OBJEKT_ID'];
}

function check_objekt_kurzname($kurzname)
{
    $result = DB::select("SELECT COUNT(OBJEKT_KURZNAME) AS ANZAHL FROM OBJEKT ORDER BY OBJEKT_ID WHERE OBJEKT_KURZNAME='$kurzname' && OBJEKT_AKTUELL=1");
    return $result[0]['ANZAHL'];
}

function neues_objekt_anlegen($objekt_kurzname, $eigentuemer)
{
    $letzte_obj_id = letzte_obj_id();
    $letzte_obj_id = $letzte_obj_id + 1;

    DB::insert("INSERT INTO OBJEKT (OBJEKT_DAT, OBJEKT_ID, OBJEKT_AKTUELL, OBJEKT_KURZNAME, EIGENTUEMER_PARTNER) VALUES (NULL,'$letzte_obj_id','1', '$objekt_kurzname', '$eigentuemer')");

    $obj_dat_neu = letzte_objekt_dat_kurzname($objekt_kurzname);
    protokollieren("OBJEKT", $obj_dat_neu, 0);
}

function liste_aktueller_objekte_edit()
{
    $result = DB::select("SELECT OBJEKT_DAT, OBJEKT_ID, OBJEKT_KURZNAME FROM OBJEKT WHERE OBJEKT_AKTUELL='1' ORDER BY OBJEKT_KURZNAME ASC ");
    foreach ($result as $row)
        echo "$row[OBJEKT_KURZNAME] - <a href='" . route('legacy::objekteform::index', ['daten_rein' => 'aendern', 'obj_id' => $row['OBJEKT_ID']]) . "'>Edit </a> - <a href='" . route('legacy::objekteform::index', ['daten_rein' => 'loeschen', 'obj_dat' => $row['OBJEKT_DAT']]) . "'>Löschen</a><br>\n";
}

function objekt_zum_aendern_holen($obj_id)
{
    $result = DB::select("SELECT OBJEKT_DAT, OBJEKT_ID, OBJEKT_AKTUELL, OBJEKT_KURZNAME FROM OBJEKT WHERE OBJEKT_ID='$obj_id' ORDER BY OBJEKT_DAT DESC LIMIT 0,1");
    erstelle_formular(NULL, NULL);
    foreach ($result as $row) {
        echo "<input type=\"hidden\" name=\"objekt_dat\" value=\"$row[OBJEKT_DAT]\"><br>\n";
        echo "<input type=\"text\" name=\"objekt_kurzname\" value=\"$row[OBJEKT_KURZNAME]\" size=\"20\"><br>\n";
    }
    erstelle_submit_button("submit_update_objekt", "Ändern");
    ende_formular();
}

function objekt_update_kurzname($obj_dat, $obj_id, $obj_kurzname)
{
    $obj_kurzname = trim($obj_kurzname);
    if ($obj_kurzname != '') {
        DB::update("UPDATE OBJEKT SET OBJEKT_AKTUELL='0' WHERE OBJEKT_DAT='$obj_dat'");
        DB::insert("INSERT INTO OBJEKT (OBJEKT_DAT, OBJEKT_ID, OBJEKT_AKTUELL, OBJEKT_KURZNAME) VALUES (NULL,'$obj_id','1', '$obj_kurzname')");
        $dat_dat_alt = $obj_dat;
        $dat_dat_neu = letzte_objekt_dat();
        protokollieren("OBJEKT", $dat_dat_neu, $dat_dat_alt);
    } else {
        fehlermeldung_ausgeben("Bitte tragen Sie einen Objektnamen ein, Objekte ohne Namen sind nicht erlaubt.");
    }
}

function objekt_loeschen($obj_dat)
{
    DB::update("UPDATE OBJEKT SET OBJEKT_AKTUELL='0' WHERE OBJEKT_DAT='$obj_dat'");
    protokollieren("OBJECT", $obj_dat, $obj_dat);
}

function objekt_liste_dropdown()
{
    $result = DB::select("SELECT OBJEKT_DAT, OBJEKT_ID, OBJEKT_KURZNAME FROM OBJEKT WHERE OBJEKT_AKTUELL='1' ORDER BY OBJEKT_KURZNAME ASC ");
    echo "<b>Objekt auswählen:</b><br>\n ";
    echo "<select name=\"haus_objekt\" size=\"1\">\n";
    foreach ($result as $row) {
        echo "<option value=\"$row[OBJEKT_ID]\">$row[OBJEKT_KURZNAME]</option>\n";
    }
    echo "</select><br>";
}

function detail_drop_down_kategorie()
{
    echo "<tr><td>Detailzugehörigkeit:</td><td><select name=\"bereich_kategorie\" size=\"1\">\n";
    echo "<option value=\"OBJEKT\">OBJEKT</option>\n";
    echo "<option value=\"HAUS\">HAUS</option>\n";
    echo "<option value=\"EINHEIT\">EINHEIT</option>\n";
    echo "<option value=\"PERSON\">PERSON</option>\n";
    echo "<option value=\"MIETVERTRAG\">MIETVERTRAG</option>\n";
    echo "<option value=\"PARTNER_LIEFERANT\">PARTNER/LIEFERANT</option>\n";
    echo "<option value=\"SEPA_UEBERWEISUNG\">SEPA_UEBERWEISUNG</option>\n";
    echo "</select></td></tr>";
}

function detail_drop_down_kategorie_db()
{
    $result = DB::select("SELECT DETAIL_KAT_ID, DETAIL_KAT_NAME FROM DETAIL_KATEGORIEN WHERE DETAIL_KAT_AKTUELL='1' ORDER BY DETAIL_KAT_NAME ASC");
    if (empty($result)) {
        fehlermeldung_ausgeben("Keine Hauptkategorien");
        erstelle_back_button();
    } else {
        echo "<tr><td>Detailzugehörigkeit:</td><td> <select name=\"bereich_kategorie\" size=\"1\">\n";
        foreach ($result as $row) {
            echo "<option value=\"$row[DETAIL_KAT_ID]\">$row[DETAIL_KAT_NAME]</option>\n";
        }
        echo "</td></tr></select>";
    }
}

function einheit_eingabe_form($haus_id)
{
    erstelle_formular(NULL, NULL);
    erstelle_hiddenfeld("haus_id", "$haus_id");
    erstelle_eingabefeld("Kurzname", "einheit_kurzname", "", "50");
    erstelle_eingabefeld("Lage (V1L)", "einheit_lage", "", "50");
    erstelle_eingabefeld("m²", "einheit_qm", "", "5");
    erstelle_submit_button("submit_einheit", "Senden");
    ende_formular();
}

function letzte_einheit_id()
{
    $result = DB::select("SELECT EINHEIT_ID FROM EINHEIT ORDER BY EINHEIT_ID DESC LIMIT 0,1");
    foreach ($result as $row)
        return $row['EINHEIT_ID'];
}

function kurzname_exist($einheit_kurzname)
{
    $result = DB::select("SELECT COUNT(EINHEIT_KURZNAME) AS ANZAHL FROM EINHEIT WHERE EINHEIT_KURZNAME LIKE '$einheit_kurzname' LIMIT 0,1");
    return $result[0]['ANZAHL'];
}

function neue_einheit_in_db($haus_id, $einheit_kurzname, $einheit_lage, $einheit_qm)
{
    // echo "eingabe: $haus_id, $einheit_kurzname, $einheit_lage, $einheit_qm, $einheit_ausstattung";
    $kurzname = kurzname_exist($einheit_kurzname);
    if ($kurzname > 0) {
        echo "Einheit mit dem selben Kurznamen existiert!!!<br>";
        backlink();
    } else {

        $einheit_id = letzte_einheit_id();
        $einheit_id = $einheit_id + 1;
        $dat_alt = letzte_einheit_dat_of_einheit_id($einheit_id);
        DB::insert("INSERT INTO EINHEIT (EINHEIT_DAT, EINHEIT_ID, EINHEIT_QM, EINHEIT_LAGE, HAUS_ID, EINHEIT_AKTUELL, EINHEIT_KURZNAME) VALUES (NULL,'$einheit_id','$einheit_qm', '$einheit_lage', '$haus_id', '1', '$einheit_kurzname')");
        $dat_neu = letzte_einheit_dat_of_einheit_id($einheit_id);
        hinweis_ausgeben("Einheit " . request()->input('einheit_kurzname') . " mit der Lage " . request()->input('einheit_lage') . " und Größe von " . request()->input('einheit_qm') . "m² wurde angelegt.");
        protokollieren('EINHEIT', $dat_neu, $dat_alt);
    }
}

function einheit_liste_dropdown($haus_id)
{
    if (isset ($haus_id)) {
        $db_abfrage = "SELECT EINHEIT_ID, EINHEIT_KURZNAME FROM EINHEIT WHERE HAUS_ID='$haus_id' && EINHEIT_AKTUELL='1' ORDER BY EINHEIT_KURZNAME ASC ";
    } else {
        $db_abfrage = "SELECT EINHEIT_ID, EINHEIT_KURZNAME FROM EINHEIT WHERE EINHEIT_AKTUELL='1' ORDER BY EINHEIT_KURZNAME ASC ";
    }
    $result = DB::select($db_abfrage);
    if (empty($result)) {
        echo "<h2 class=\"fehler\">Keine Einheiten im ausgewählten Haus</h2>";
        echo "<p class=\"hinweis\">Bitte zuerst Einheit im Haus anlegen - <a href='" . route('legacy::einheitenform::index', ['daten_rein' => 'anlegen']) . "'>Einheit anlegen HIER&nbsp;</a></p><br>";
    } else {
        echo "<b>Einheit auswählen:</b><br>\n ";
        echo "<select name=\"einheiten\" size=\"1\">\n";
        foreach ($result as $row) {
            echo "<option value=\"$row[EINHEIT_ID]\">$row[EINHEIT_KURZNAME]</option>\n";
        }
        echo "</select><br>";
    }
}

function einheit_aendern_form($einheit_id)
{
    erstelle_formular(NULL, NULL);
    erstelle_hiddenfeld("einheit_id", "$einheit_id");
    $result = DB::select("SELECT EINHEIT_DAT, EINHEIT_ID, EINHEIT_QM, EINHEIT_LAGE, HAUS_ID, EINHEIT_KURZNAME FROM EINHEIT WHERE EINHEIT_ID='$einheit_id' && EINHEIT_AKTUELL='1' ORDER BY EINHEIT_DAT DESC LIMIT 0,1");
    foreach ($result as $row) {
        erstelle_hiddenfeld("einheit_dat", "$row[EINHEIT_DAT]");
        erstelle_hiddenfeld("haus_id", "$row[HAUS_ID]");
        erstelle_eingabefeld("Kurzname", "einheit_kurzname", "$row[EINHEIT_KURZNAME]", "50");
        erstelle_eingabefeld("Lage (V1L)", "einheit_lage", "$row[EINHEIT_LAGE]", "50");
        erstelle_eingabefeld("m²", "einheit_qm", "$row[EINHEIT_QM]", "5");
    }
    erstelle_submit_button("aendern_einheit", "Ändern");
    ende_formular();
}

function einheit_deaktivieren($einheit_dat)
{
    DB::update("UPDATE EINHEIT SET EINHEIT_AKTUELL='0' WHERE EINHEIT_DAT='$einheit_dat'");
}

function letzte_einheit_dat()
{
    $result = DB::select("SELECT EINHEIT_DAT FROM EINHEIT WHERE EINHEIT_AKTUELL='1' ORDER BY EINHEIT_ID DESC LIMIT 0,1");
    foreach ($result as $row)
        return $row['EINHEIT_DAT'];
}

function einheit_geandert_in_db($einheit_dat, $einheit_id, $haus_id, $einheit_kurzname, $einheit_lage, $einheit_qm)
{
    DB::insert("INSERT INTO EINHEIT (EINHEIT_DAT, EINHEIT_ID, EINHEIT_QM, EINHEIT_LAGE, HAUS_ID, EINHEIT_AKTUELL, EINHEIT_KURZNAME) VALUES (NULL,'$einheit_id','$einheit_qm', '$einheit_lage', '$haus_id', '1', '$einheit_kurzname')");
    $akt_einheit_dat = letzte_einheit_dat();
    protokollieren("EINHEIT", $akt_einheit_dat, $einheit_dat);
}

// ##neu
function get_kategorie_name($kategorie_id)
{
    $result = DB::select("SELECT DETAIL_KAT_NAME FROM DETAIL_KATEGORIEN WHERE DETAIL_KAT_ID='$kategorie_id' && DETAIL_KAT_AKTUELL='1' ORDER BY DETAIL_KAT_ID DESC limit 0,1");
    foreach ($result as $row) {
        return $row['DETAIL_KAT_NAME'];
    }
}

function text_area($name, $breite, $hoehe)
{
    echo "<br>$name:<br> <textarea name=\"$name\" cols=\"$breite\" rows=\"$hoehe\"></textarea><br>\n";
}

// #### person
function person_erfassen_form()
{
    erstelle_formular(NULL, NULL);
    erstelle_eingabefeld("Nachname", "person_nachname", "", "50");
    erstelle_eingabefeld("Vorname", "person_vorname", "", "50");
    erstelle_eingabefeld("Geburtstag (dd.mm.jjjj)", "person_geburtstag", "", "10");
    erstelle_submit_button("submit_person", "Eintragen");
    ende_formular();
}

function person_hidden_form($nachname, $vorname, $geburtstag)
{
    erstelle_formular(NULL, NULL);
    erstelle_hiddenfeld("person_nachname", "$nachname");
    erstelle_hiddenfeld("person_vorname", "$vorname");
    erstelle_hiddenfeld("person_geburtstag", "$geburtstag");
    erstelle_submit_button("submit_person_direkt", "Trotzdem eintragen");
    ende_formular();
}

function person_aendern_from($person_id)
{
    $result = DB::select("SELECT PERSON_ID, PERSON_NACHNAME, PERSON_VORNAME, PERSON_GEBURTSTAG FROM PERSON WHERE PERSON_ID='$person_id' && PERSON_AKTUELL='1'");
    if (!empty($result)) {
        erstelle_formular(NULL, NULL);
        foreach($result as $row) {
            $PERSON_GEBURTSTAG = date_mysql2german($row['PERSON_GEBURTSTAG']);
            erstelle_hiddenfeld("person_id", "$row[PERSON_ID]");
            erstelle_eingabefeld("Nachname", "person_nachname", "$row[PERSON_NACHNAME]", "50");
            erstelle_eingabefeld("Vorname", "person_vorname", "$row[PERSON_VORNAME]", "50");
            erstelle_eingabefeld("Geburtstag (dd.mm.jjjj)", "person_geburtstag", "$row[PERSON_GEBURTSTAG]", "10");
        }
        erstelle_submit_button("submit_person_aendern", "Aendern");
        ende_formular();
    } else {
        hinweis_ausgeben("Person mit der Person ID $person_id existiert nicht!");
    }
}

// #### mietvertrag
function mietvertrag_form_neu()
{
    if (!request()->has('objekt_id') && !request()->has('einheit_id')) {
        mietvertrag_objekt_links();
    }
    if (request()->has('objekt_id')) {
        einheiten_ids_by_objekt(request()->input('objekt_id'));
    }
    if (request()->has('einheit_id') && !request()->has('submit_vertragspartner') && !request()->has('mietvertrag_speichern')) {
        erstelle_formular(NULL, NULL);
        erstelle_hiddenfeld("einheit_id", request()->input('einheit_id'));
        personen_liste_multi();
        erstelle_eingabefeld("Vertragsbeginn)", "mietvertrag_von", "", "10");
        erstelle_eingabefeld("Vertragsende", "mietvertrag_bis", "", "10");
        erstelle_submit_button("submit_vertragspartner", "Vertrag abschließen!");
        ende_formular();
    }
    if (request()->has('submit_vertragspartner')) {
        $anzahl_partner = count(request()->input('PERSON_ID'));
        if ($anzahl_partner < 1) {
            fehlermeldung_ausgeben("Wählen Sie Vertragsparteien aus");
            $error = true;
        } elseif (!request()->has('mietvertrag_von')) {
            fehlermeldung_ausgeben("Vertragsbeginn eintragen");
            $error = true;
        }
        echo $error;
        if ($error != true) {
            erstelle_formular(NULL, NULL); // name, action
            $anzahl_partner = count(request()->input('PERSON_ID'));
            $einheit_kurzname = einheit_kurzname(request()->input('einheit_id'));
            echo "<tr><td><h1>Folgende Daten wurden übermittelt:\n</h1></td></tr>\n";
            echo "<tr><td><h2>Einheitkurzname: $einheit_kurzname</h2></td></tr>\n";
            echo "<tr><td>Vertragsparteien: ";
            for ($a = 0; $a < $anzahl_partner; $a++) {
                mieternamen(request()->input('PERSON_ID') [$a]);
            }
            echo "</td></tr>";
            echo "<tr><td>Von: " . request()->input('mietvertrag_von') . "</td></tr>";
            if (!request()->has('mietvertrag_bis')) {
                $vertrag_bis = "unbefristet";
            } else {
                $vertrag_bis = request()->input('mietvertrag_bis');
            }
            echo "<tr><td>Bis: $vertrag_bis</td></tr>";
            echo "<tr><td>";
            warnung_ausgeben("Sind Sie sicher, daß Sie diesen Mietvertrag abschließen möchten?");
            echo "</td></tr>";
            erstelle_hiddenfeld("einheit_id", request()->input('einheit_id'));
            erstelle_hiddenfeld("mietvertrag_von", request()->input('mietvertrag_von'));
            erstelle_hiddenfeld("mietvertrag_bis", request()->input('mietvertrag_bis'));
            for ($a = 0; $a < $anzahl_partner; $a++) {
                erstelle_hiddenfeld("PERSON_ID[]", request()->input('PERSON_ID')[$a]);
            }
            erstelle_submit_button("mietvertrag_speichern", "Speichern"); // name, wert
            ende_formular();
        }
    }
    // ##vertrag eintragen
    if (request()->has('mietvertrag_speichern')) {
        mietvertrag_anlegen(request()->input('mietvertrag_von'), request()->input('mietvertrag_bis'), request()->input('einheit_id'));
        $zugewiesene_vetrags_id = mietvertrag_by_einheit(request()->input('einheit_id'));
        $anzahl_partner = count(request()->input('PERSON_ID'));
        for ($a = 0; $a < $anzahl_partner; $a++) {
            person_zu_mietvertrag(request()->input('PERSON_ID') [$a], $zugewiesene_vetrags_id);
        }
        hinweis_ausgeben("Mietvertrag wurde erstellt!");
        hinweis_ausgeben("Sie werden zur Mietdefinition weitergeleitet!");
        weiterleiten_in_sec(route('legacy::miete_definieren::index', ['option' => 'miethoehe', 'mietvertrag_id' => $zugewiesene_vetrags_id], false), "2");
    }
}
