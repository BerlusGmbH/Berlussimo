<?php

$admin_panel = request()->input('admin_panel');
if (isset ($admin_panel)) {

    switch ($admin_panel) {
        case "menu" :
            break;

        case "details_neue_kat" :
            detail_kategorie_form();
            liste_detail_kat();
            break;

        case "details_neue_ukat" :
            detail_unterkategorie_form();
            liste_udetail_kat();
            break;

        case "liste_detail_kat" :
            liste_detail_kat();
            break;
    }
}
function detail_kategorie_form()
{
    echo "<div><span>Hauptdetail / Detailgruppe erstellen</span><hr/>";

    if (!request()->has('submit_detail_kat')) {
        erstelle_formular(NULL, NULL);
        detail_drop_down_kategorie();
        erstelle_eingabefeld("Detail / Detailgruppe", "detail_kat_name", "", 30);
        erstelle_submit_button_nur("submit_detail_kat", "Erstellen");
        ende_formular();
    }
    if (request()->has('submit_detail_kat')) {
        if (!request()->has('detail_kat_name')) {
            fehlermeldung_ausgeben("Geben Sie bitte einen Kategorienamen ein!");
            erstelle_back_button();
        } elseif (!request()->has('bereich_kategorie')) {
            fehlermeldung_ausgeben("Wählen Sie bitte eine Detailtabelle aus!");
            erstelle_back_button();
        } else {
            $detail_kat_name = bereinige_string(request()->input('detail_kat_name'));
            $bereich_kategorie = bereinige_string(request()->input('bereich_kategorie'));
            $detail_kat_exists = check_detail_kat($detail_kat_name);
            if ($detail_kat_exists == 0) {
                DB::insert("INSERT INTO DETAIL_KATEGORIEN VALUES (NULL, '$detail_kat_name', '$bereich_kategorie', '1')");
                hinweis_ausgeben("Detail bzw. Detailgruppe $detail_kat_name wurde dem Bereich $bereich_kategorie hinzugefügt.");
            } else {
                fehlermeldung_ausgeben("Gleichnamige Detailkategorie existiert!");
                erstelle_back_button();
            }
        }
    }

    echo "</div>";
}

function detail_unterkategorie_form()
{
    echo "<div class=\"div balken_detail_kat_form\"><span class=\"font_balken_uberschrift\">AUSWAHLOPTIONEN</span><hr />";

    if (!request()->has('submit_detail_ukat')) {
        erstelle_formular(NULL, NULL);
        detail_drop_down_kategorie_db();
        erstelle_eingabefeld("Auswahloption", "detail_kat_uname", "", 30);
        erstelle_submit_button_nur("submit_detail_ukat", "Erstellen");
        ende_formular();
    }
    if (request()->has('submit_detail_ukat')) {
        if (request()->has('detail_kat_uname') && empty (request()->input('detail_kat_uname'))) {
            fehlermeldung_ausgeben("Geben Sie bitte eine Option ein!");
            erstelle_back_button();
        } else {
            $detail_kat_uname = bereinige_string(request()->input('detail_kat_uname'));
            $bereich_kategorie = bereinige_string(request()->input('bereich_kategorie'));
            echo $detail_kat_uname;
            echo $bereich_kategorie;
            $u_kat_exists = check_detail_ukat($bereich_kategorie, $detail_kat_uname);
            $haupt_kat_name = get_detail_kat_name($bereich_kategorie);
            if ($u_kat_exists == 0) {
                DB::insert("INSERT INTO DETAIL_UNTERKATEGORIEN VALUES (NULL, '$bereich_kategorie', '$detail_kat_uname', '1')");
                hinweis_ausgeben("Unterdetail <u>$detail_kat_uname</u> bzw. Auswahloption wurde dem Bereich $haupt_kat_name hinzugefügt.");
            } else {
                fehlermeldung_ausgeben("Gleichnamige Detailoption existiert!");
                erstelle_back_button();
            }
        }
    }

    echo "</div>";
}

function check_detail_kat($kat)
{
    $result = DB::select("SELECT COUNT(DETAIL_KAT_ID) AS ANZAHL FROM DETAIL_KATEGORIEN WHERE DETAIL_KAT_NAME='$kat' && DETAIL_KAT_AKTUELL='1'");
    return $result[0]['ANZAHL'];
}

function check_detail_ukat($kat_id, $kat_name)
{
    $result = DB::select("SELECT COUNT(*) AS ANZAHL FROM DETAIL_UNTERKATEGORIEN WHERE KATEGORIE_ID='$kat_id' && UNTERKATEGORIE_NAME='$kat_name' && AKTUELL='1'");
    return $result[0]['ANZAHL'];
}

function get_detail_kat_name($id)
{
    $result = DB::select("SELECT DETAIL_KAT_NAME FROM DETAIL_KATEGORIEN WHERE DETAIL_KAT_ID='$id' && DETAIL_KAT_AKTUELL='1'");
    foreach($result as $row)
        return $row['DETAIL_KAT_NAME'];
}

function liste_detail_kat()
{
    if (request()->has('table')) {
        $result = DB::select("SELECT DETAIL_KAT_ID, DETAIL_KAT_NAME, DETAIL_KAT_KATEGORIE FROM DETAIL_KATEGORIEN WHERE DETAIL_KAT_AKTUELL='1' && DETAIL_KAT_KATEGORIE='" . request()->input('table') . "' ORDER BY DETAIL_KAT_KATEGORIE ASC ");
    } else {
        $result = DB::select("SELECT DETAIL_KAT_ID, DETAIL_KAT_NAME, DETAIL_KAT_KATEGORIE FROM DETAIL_KATEGORIEN WHERE DETAIL_KAT_AKTUELL='1' ORDER BY DETAIL_KAT_KATEGORIE ASC ");
    }
    echo "<div><table class='striped'>\n";
    echo "<thead><tr><th colspan='2'>Hauptdetails</th></tr>\n";
    echo "<tr class=\"feldernamen\"><th>Detailname</th><th>Kategorie</th></tr></thead>\n";
    $counter = 0;
    foreach($result as $row) {
        $auswahl_link = "<a href='" . route('web::admin::legacy', ['admin_panel' => 'details_neue_kat', 'table' => $row['DETAIL_KAT_KATEGORIE']]) . "'>" . $row['DETAIL_KAT_KATEGORIE'] . "</a>";

        $counter++;
        if ($counter == 1) {
            echo "<tr class=\"zeile1\"><td>" . $row['DETAIL_KAT_NAME'] . "</td><td>$auswahl_link</td></tr>\n";
        }
        if ($counter == 2) {
            echo "<tr class=\"zeile2\"><td>" . $row['DETAIL_KAT_NAME'] . "</td><td>$auswahl_link</td></tr>\n";
            $counter = 0;
        }
    }
    echo "</table></div>";
}

function liste_udetail_kat()
{
    if (request()->has('table') && !empty (request()->input('table'))) {
        $result = DB::select("SELECT UKAT_DAT, KATEGORIE_ID, UNTERKATEGORIE_NAME FROM DETAIL_UNTERKATEGORIEN WHERE AKTUELL='1' ORDER BY KATEGORIE_ID ASC ");
    } else {
        $result = DB::select("SELECT UKAT_DAT, KATEGORIE_ID, UNTERKATEGORIE_NAME FROM DETAIL_UNTERKATEGORIEN WHERE AKTUELL='1' ORDER BY KATEGORIE_ID ASC ");
    }
    echo "<div class=\"tabelle_objekte\"><table>\n";
    echo "<tr class=\"feldernamen\"><td colspan=\"2\">HAUPTDETAILS</td></tr>\n";
    echo "<tr class=\"feldernamen\"><td>DETAIL</td><td>OPTION</tr>\n";
    $counter = 0;
    foreach($result as $row) {
        $kat_name = get_detail_kat_name($row['KATEGORIE_ID']);
        $counter++;
        if ($counter == 1) {
            echo "<tr class=\"zeile1\"><td>$kat_name</td><td>" . $row['UNTERKATEGORIE_NAME'] . "</td></tr>\n";
        }
        if ($counter == 2) {
            echo "<tr class=\"zeile1\"><td>$kat_name</td><td>" . $row['UNTERKATEGORIE_NAME'] . "</td></tr>\n";
            $counter = 0;
        }
    }
    echo "</table></div>";
}
