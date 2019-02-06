<?php

function erstelle_formular($name, $action)
{
    $scriptname = $_SERVER ['REQUEST_URI'];

    if (!isset ($action)) {
        echo "<form name=\"$name\" action=\"$scriptname\"  method=\"post\">\n";
    } else {
        echo "<form name=\"$name\" action=\"$action\" method=\"post\">\n";
    }

    echo csrf_field() . "\n";

    echo "<table class=\"formular_tabelle\">\n<tr><td>";
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
    echo "<a class='btn waves-effect waves-light' href='javascript:history.back()'>Abbrechen und Zurück</a>\n";
}

function erstelle_eingabefeld($beschreibung, $name, $wert, $size)
{
    echo "<div class='input-field'>
            <input type='text' id='$name' name='$name' value='$wert' size='$size'>
            <label for='$name'>$beschreibung</label>
          </div>\n";
}

function erstelle_submit_button($name, $wert)
{
    echo "<button class='btn waves-effect waves-light' type='submit' name='$name' value='$wert'>$wert</button>&nbsp;";
    erstelle_back_button();
}

function erstelle_submit_button_nur($name, $wert)
{
    echo "<button class='btn waves-effect waves-light' type='submit' name='$name' value='$wert'>$wert<i class=\"mdi mdi-send right\"></i></button>";
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
        echo "$row[OBJEKT_KURZNAME] - <a href='" . route('web::objekteform::legacy', ['daten_rein' => 'aendern', 'obj_id' => $row['OBJEKT_ID']]) . "'>Edit </a> - <a href='" . route('web::objekteform::legacy', ['daten_rein' => 'loeschen', 'obj_dat' => $row['OBJEKT_DAT']]) . "'>Löschen</a><br>\n";
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

function detail_drop_down_kategorie()
{
    echo "<tr><td>Detailzugehörigkeit:</td><td><select name=\"bereich_kategorie\" size=\"1\">\n";
    echo "<option value=\"Objekt\">Objekt</option>\n";
    echo "<option value=\"Haus\">Haus</option>\n";
    echo "<option value=\"Einheit\">Einheit</option>\n";
    echo "<option value=\"Person\">Person</option>\n";
    echo "<option value=\"Mietvertrag\">Mietvertrag</option>\n";
    echo "<option value=\"Partner\">Partner</option>\n";
    echo "<option value=\"SEPA_UEBERWEISUNG\">Sepa Überweisung</option>\n";
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
