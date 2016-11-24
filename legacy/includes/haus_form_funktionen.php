<?php

function objekt_liste_links()
{
    $result = DB::select("SELECT OBJEKT_DAT, OBJEKT_ID, OBJEKT_KURZNAME FROM OBJEKT WHERE OBJEKT_AKTUELL='1' ORDER BY OBJEKT_KURZNAME ASC ");
    echo "<b>Objekt auswählen:</b><br>\n ";
    foreach($result as $row) {
        echo "<a class='objekt_links' href='" . route('legacy::haeuserform::index', ['daten_rein' => 'anlegen', 'haus_objekt' => $row['OBJEKT_ID']]) . "'>$row[OBJEKT_KURZNAME]</a><br>\n";
    }
}

function objekt_liste_links_aenderung()
{
    $result = DB::select("SELECT OBJEKT_DAT, OBJEKT_ID, OBJEKT_KURZNAME FROM OBJEKT WHERE OBJEKT_AKTUELL='1' ORDER BY OBJEKT_KURZNAME ASC ");
    echo "<b>Objekt auswählen:</b><br>\n ";
    foreach($result as $row) {
        echo "<a class='objekt_links' href='" . route('legacy::haeuserform::index', ['daten_rein' => 'aendern_liste', 'objekt_id' => $row['OBJEKT_ID']]) . "'>$row[OBJEKT_KURZNAME]</a><br>\n";
    }
}

function haus_eingabe_formular($objekt_id)
{
    $objekt_kurzname = objekt_kurzname($objekt_id);
    $anzahl_haeuser = anzahl_haeuser_im_objekt($objekt_id);
    echo "<p class=\"form_ausgewaehlt\">Ausgewähltes Objekt: $objekt_kurzname (Häuser: $anzahl_haeuser)</p>";
    erstelle_formular("haus_eingabe_form", NULL);
    erstelle_hiddenfeld("objekt_id", $objekt_id);
    erstelle_eingabefeld("Strasse", "haus_strasse", "", "50");
    erstelle_eingabefeld("Hausnummer", "haus_nummer", "", "5");
    erstelle_eingabefeld("Ort/Stadt", "haus_stadt", "", "50");
    erstelle_eingabefeld("PLZ", "haus_plz", "", "50");
    erstelle_eingabefeld("Haus in m²", "haus_qm", "", "50");
    erstelle_submit_button("submit_haus", "Senden");
    ende_formular();
}

function haus_aendern_formular($haus_id)
{
    erstelle_formular("haus_aendern_form", route('legacy::haeuserform::index', ['daten_rein' => 'aendern'], false));
    $result = DB::select("SELECT HAUS_DAT, HAUS_STRASSE, HAUS_NUMMER, HAUS_STADT, HAUS_PLZ, HAUS_QM, OBJEKT_ID FROM HAUS WHERE HAUS_ID='$haus_id' && HAUS_AKTUELL='1' ORDER BY HAUS_DAT DESC LIMIT 0,1");

    foreach($result as $row) {
        erstelle_hiddenfeld("haus_dat", $row['HAUS_DAT']);
        erstelle_hiddenfeld("haus_id", $haus_id);
        erstelle_hiddenfeld("objekt_id", $row['OBJEKT_ID']);
        erstelle_eingabefeld("Strasse", "haus_strasse", "$row[HAUS_STRASSE]", "50");
        erstelle_eingabefeld("Hausnummer", "haus_nummer", "$row[HAUS_NUMMER]", "5");
        erstelle_eingabefeld("Ort/Stadt", "haus_stadt", "$row[HAUS_STADT]", "50");
        erstelle_eingabefeld("PLZ", "haus_plz", "$row[HAUS_PLZ]", "50");
        erstelle_eingabefeld("Haus in m²", "haus_qm", "$row[HAUS_QM]", "50");
    }
    erstelle_submit_button("submit_haus", "Senden");
    ende_formular();
}

function letzte_haus_id()
{
    $result = DB::select("SELECT HAUS_ID FROM HAUS ORDER BY HAUS_ID DESC LIMIT 0,1");
    foreach($result as $row)
        return $row['HAUS_ID'];
}

function haus_in_db_eintragen($strasse, $nummer, $stadt, $plz, $qm, $objekt_id)
{
    $haus_existiert = haus_exists($strasse, $nummer, $stadt, $plz);
    if ($haus_existiert < 1) {
        $haus_id = letzte_haus_id();
        $haus_id = $haus_id + 1;
        DB::insert("INSERT INTO HAUS (HAUS_DAT, HAUS_ID, HAUS_STRASSE, HAUS_NUMMER, HAUS_STADT, HAUS_PLZ, HAUS_QM, HAUS_AKTUELL, OBJEKT_ID) VALUES (NULL,'$haus_id','$strasse', '$nummer', '$stadt', '$plz', '$qm', '1', '$objekt_id')");
        $aktuelle_haus_dat = zugeteilte_haus_dat($strasse, $nummer, $stadt, $plz);
        protokollieren("HAUS", $aktuelle_haus_dat, 0);
        hinweis_ausgeben("Haus $haus_id wurde eingetragen");
        weiterleiten(route('legacy::haeuser::index', ['haus_raus' => 'haus_kurz', 'objekt_id' => $objekt_id], false));
    } else {
        fehlermeldung_ausgeben("Haus in der $strasse $nummer in $stadt $plz existiert bereits.");
        weiterleiten("javascript:history.back()");
    }
}

function haus_geaendert_eintragen($haus_dat, $haus_id, $strasse, $nummer, $stadt, $plz, $qm, $objekt_id)
{
    DB::insert("INSERT INTO HAUS (HAUS_DAT, HAUS_ID, HAUS_STRASSE, HAUS_NUMMER, HAUS_STADT, HAUS_PLZ, HAUS_QM, HAUS_AKTUELL, OBJEKT_ID) VALUES (NULL, '$haus_id', '$strasse', '$nummer', '$stadt', '$plz', '$qm', '1', '$objekt_id')");
    $aktuelle_haus_dat = zugeteilte_haus_dat($strasse, $nummer, $stadt, $plz);
    protokollieren("HAUS", $aktuelle_haus_dat, $haus_dat);
    hinweis_ausgeben("Haus wurde geändert");
    weiterleiten(route('legacy::haeuser::index', ['haus_raus' => 'haus_kurz'], false));
}

function zugeteilte_haus_dat($strasse, $nummer, $stadt, $plz)
{
    $result = DB::select("SELECT HAUS_DAT FROM HAUS WHERE HAUS_STRASSE='$strasse' && HAUS_NUMMER='$nummer' && HAUS_STADT='$stadt' && HAUS_PLZ='$plz' ORDER BY HAUS_DAT DESC LIMIT 0,1");
    foreach($result as $row)
        return $row['HAUS_DAT'];
}

function haus_exists($strasse, $nummer, $stadt, $plz)
{
    $result = DB::select("SELECT COUNT(HAUS_DAT) AS ANZAHL FROM HAUS WHERE HAUS_STRASSE='$strasse' && HAUS_NUMMER='$nummer' && HAUS_STADT='$stadt' && HAUS_PLZ='$plz' ORDER BY HAUS_DAT DESC LIMIT 0,1");
    return $result[0]['ANZAHL'];
}

function haeuser_liste_tabelle($objekt_id)
{
    $objekt_kurzname = objekt_kurzname($objekt_id);
    $result = DB::select("SELECT HAUS_STRASSE, HAUS_NUMMER FROM HAUS WHERE OBJEKT_ID='$objekt_id' && HAUS_AKTUELL='1' ORDER BY HAUS_NUMMER ASC");
    if (!empty($result)) {
        echo "<div class=\"tabelle_haus\"><table>\n";
        echo "<tr class=\"feldernamen\"><td colspan=2>Objekt: $objekt_kurzname</td></tr>\n";
        echo "<tr class=\"feldernamen\"><td>Straße</td><td>Nummer</td></tr>\n";
        $counter = 0;
        foreach($result as $row) {
            $counter++;
            if ($counter == 1) {
                echo "<tr class=\"zeile1\"><td>$row[HAUS_STRASSE]</td><td>$row[HAUS_NUMMER]</td></tr>\n";
            }
            if ($counter == 2) {
                echo "<tr class=\"zeile2\"><td>$row[HAUS_STRASSE]</td><td>$row[HAUS_NUMMER]</td></tr>\n";
                $counter = 0;
            }
        }
        echo "</table></div>";
    }
}

function deaktiviere_haus_dat($haus_dat)
{
    DB::update("UPDATE HAUS SET HAUS_AKTUELL='0' WHERE HAUS_DAT='$haus_dat'");
}