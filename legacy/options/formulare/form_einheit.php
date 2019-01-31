<?php

$objekt_id = request()->input('objekt_id');
$haus_id = request()->input('haus_id');
$einheit_id = request()->input('einheit_id');
$objekt_kurzname = objekt_kurzname($objekt_id);
$haus_kurzname = haus_strasse_nr($haus_id);
$einheit_update = request()->input('einheit_update');
if (request()->filled('daten_rein')) {
    switch (request()->input('daten_rein')) {

        case "anlegen" :
            $form = new mietkonto ();
            $form->erstelle_formular("Einheit anlegen", NULL);
            iframe_start();
            echo "<h1>Einheit anlegen</h1>";
            if (!isset ($objekt_id)) {
                objekt_links();
            }
            if (isset ($objekt_id) && !isset ($haus_id)) {
                $objekt_kurzname = objekt_kurzname($objekt_id);
                hinweis_ausgeben("Objekt: $objekt_kurzname");
                haeuser_links($objekt_id);
            }
            if (isset ($objekt_id) && isset ($haus_id)) {
                $objekt_kurzname = objekt_kurzname($objekt_id);
                $haus_kurzname = haus_strasse_nr($haus_id);
                hinweis_ausgeben("Objekt: $objekt_kurzname");
                hinweis_ausgeben("Haus: $haus_kurzname");
                if (!request()->filled('submit_einheit')) {
                    einheit_eingabe_form($haus_id);
                    einheiten_liste($haus_id);
                }
            }

            if (request()->filled('submit_einheit')) {
                foreach (request()->request->all() as $key => $value) {
                    if (empty ($value)) {
                        fehlermeldung_ausgeben("FEHLER: Alle Felder müssen ausgefüllt werden!");
                        backlink();
                        $error = 1;
                        break;
                    }
                }
                if (!isset ($error)) {
                    erstelle_formular('einheit_in_db', NULL); // name, action
                    echo "<tr><td><h1>Folgende Daten wurden übermittelt:\n</h1></td></tr>\n";
                    echo "<tr><td><h2>Objektkurzname: $objekt_kurzname</h2></td></tr>\n";
                    echo "<tr><td><h2>Haus: $haus_kurzname</h2></td></tr>\n";
                    echo "<tr><td><h2>Einheit: " . request()->input('einheit_kurzname') . " - " . request()->input('einheit_qm') . "m² - Lage: " . request()->input('einheit_lage') . "</h2></td></tr>\n";
                    echo "<tr><td>";
                    warnung_ausgeben("Sind Sie sicher, daß Sie die neue Einheit " . request()->input('einheit_kurzname') . " (" . request()->input('einheit_qm') . "m²) im Objekt $objekt_kurzname, $haus_kurzname anlegen wollen?");
                    echo "</td></tr>";
                    erstelle_hiddenfeld("haus_id", "$haus_id");
                    erstelle_hiddenfeld("einheit_kurzname", request()->input('einheit_kurzname'));
                    erstelle_hiddenfeld("einheit_qm", request()->input('einheit_qm'));
                    erstelle_hiddenfeld("einheit_lage", request()->input('einheit_lage'));
                    erstelle_hiddenfeld("daten_rein", "speichern");
                    erstelle_submit_button("einheit_speichern", "Speichern"); // name, wert
                    ende_formular();
                }
            }
            iframe_end();
            $form->ende_formular();
            break;

        case "speichern" :
            $form = new mietkonto ();
            $form->erstelle_formular("Einheit speichern", NULL);
            iframe_start();
            hinweis_ausgeben("Objekt: $objekt_kurzname");
            hinweis_ausgeben("Haus: $haus_kurzname");
            neue_einheit_in_db(request()->input('haus_id'), request()->input('einheit_kurzname'), request()->input('einheit_lage'), request()->input('einheit_qm'));
            weiterleiten(route('web::einheiten::legacy', ['einheit_raus' => 'einheit_kurz', 'haus_id' => request()->input('haus_id')], false));
            iframe_end();
            $form->ende_formular();
            break;

        case "aendern" :
            $form = new mietkonto ();
            $form->erstelle_formular("Einheit ändern", NULL);
            iframe_start();
            echo "<h1>Einheit ändern</h1>";
            if (!isset ($objekt_id)) {
                objekt_links();
            }
            if (isset ($objekt_id) && !isset ($haus_id)) {
                $objekt_kurzname = objekt_kurzname($objekt_id);
                hinweis_ausgeben("Objekt: $objekt_kurzname");
                haeuser_links($objekt_id);
            }
            if (isset ($objekt_id) && isset ($haus_id) && !isset ($einheit_id)) {
                $objekt_id = objekt_id_of_haus($haus_id);
                $objekt_kurzname = objekt_kurzname($objekt_id);
                $haus_kurzname = haus_strasse_nr($haus_id);
                hinweis_ausgeben("Objekt: $objekt_kurzname");
                hinweis_ausgeben("Haus: $haus_kurzname");
                einheiten_links($objekt_id, $haus_id);
            }
            if (isset ($objekt_id) && isset ($haus_id) && isset ($einheit_id) && !request()->filled('aendern_einheit')) {
                $objekt_id = objekt_id_of_haus($haus_id);
                $haus_id = haus_id_of_einheit($einheit_id);
                $objekt_kurzname = objekt_kurzname($objekt_id);
                $haus_kurzname = haus_strasse_nr($haus_id);
                $einheit_kurzname = einheit_kurzname($einheit_id);
                hinweis_ausgeben("Objekt: $objekt_kurzname");
                hinweis_ausgeben("Haus: $haus_kurzname");
                hinweis_ausgeben("Einheit: $einheit_kurzname");
                einheit_aendern_form($einheit_id);
                einheiten_liste($haus_id);
            }

            if (request()->filled('aendern_einheit')) {
                foreach (request()->request->all() as $key => $value) {
                    if (empty ($value)) {
                        fehlermeldung_ausgeben("FEHLER: Alle Felder müssen ausgefüllt werden!");
                        $error = 1;
                        break;
                    }
                    echo "$key $value";
                }
                if (!isset ($error)) {
                    erstelle_formular('einheit_in_db', NULL); // name, action
                    echo "<tr><td><h1>Folgende Daten wurden übermittelt:\n</h1></td></tr>\n";
                    echo "<tr><td><h2>Objektkurzname: $objekt_kurzname</h2></td></tr>\n";
                    echo "<tr><td><h2>Haus: $haus_kurzname</h2></td></tr>\n";
                    echo "<tr><td><h2>Einheit: " . request()->input('einheit_kurzname') . " - " . request()->input('einheit_qm') . "m² - Lage: " . request()->input('einheit_lage') . "</h2></td></tr>\n";
                    echo "<tr><td>";
                    warnung_ausgeben("Sind Sie sicher, daß Sie die neue Einheit " . request()->input('einheit_kurzname') . " (" . request()->input('einheit_qm') . "m²) im Objekt $objekt_kurzname, $haus_kurzname anlegen wollen?");
                    echo "</td></tr>";
                    erstelle_hiddenfeld("haus_id", "$haus_id");
                    erstelle_hiddenfeld("einheit_kurzname", request()->input('einheit_kurzname'));
                    erstelle_hiddenfeld("einheit_id", request()->input('einheit_id'));
                    erstelle_hiddenfeld("einheit_dat", request()->input('einheit_dat'));
                    erstelle_hiddenfeld("einheit_qm", request()->input('einheit_qm'));
                    erstelle_hiddenfeld("einheit_lage", request()->input('einheit_lage'));
                    erstelle_hiddenfeld("daten_rein", "einheit_update");
                    erstelle_submit_button("einheit_update", "Speichern"); // name, wert
                    ende_formular();
                }
            }

            iframe_end();
            $form->ende_formular();
            break;

        case "einheit_update" :
            iframe_start();
            hinweis_ausgeben("Objekt: $objekt_kurzname");
            hinweis_ausgeben("Haus: $haus_kurzname");
            einheit_deaktivieren(request()->input('einheit_dat'));
            hinweis_ausgeben("DAT " . request()->input('einheit_dat') . " inaktiv");
            einheit_geandert_in_db(request()->input('einheit_dat'), request()->input('einheit_id'), request()->input('haus_id'), request()->input('einheit_kurzname'), request()->input('einheit_lage'), request()->input('einheit_qm'));
            hinweis_ausgeben("EINHEIT " . request()->input('einheit_kurzname') . " WURDE GEÄNDERT!");
            einheiten_liste($haus_id);
            iframe_end();
            break;
    }
}
function objekt_links()
{
    $daten_rein = request()->input('daten_rein');
    $result = DB::select("SELECT OBJEKT_DAT, OBJEKT_ID, OBJEKT_KURZNAME FROM OBJEKT WHERE OBJEKT_AKTUELL='1' ORDER BY OBJEKT_KURZNAME ASC ");
    echo "<b>Objekt auswählen:</b><br>\n ";
    foreach ($result as $row) {
        echo "<a class=\"objekt_links\" href='" . route('web::einheitenform::legacy', ['daten_rein' => $daten_rein, 'objekt_id' => $row['OBJEKT_ID']]) . ">$row[OBJEKT_KURZNAME]</a><br>\n";
    }
}

function haeuser_links($obj_id)
{
    $daten_rein = request()->input('daten_rein');
    $result = DB::select("SELECT HAUS_DAT, HAUS_ID, HAUS_STRASSE, HAUS_NUMMER FROM HAUS WHERE OBJEKT_ID='$obj_id' && HAUS_AKTUELL='1' ORDER BY HAUS_STRASSE, HAUS_NUMMER ASC");
    if (empty($result)) {
        echo "<h5 class=\"fehler\">Keine Häuser im ausgewählten Objekt</h5><br>\n";
        echo "Erst Haus im Objekt anlegen - <a href='" . route('web::haeuserform::legacy', ['daten_rein' => 'anlegen']) . "'>Hauseningabe hier&nbsp;</a>\n<br>\n";
    } else {
        foreach ($result as $row) {
            echo "<a class=\"objekt_links\" href='" . route('web::einheitenform::legacy', ['daten_rein' => $daten_rein, 'objekt_id' => $obj_id, 'haus_id' => $row['HAUS_ID']]) . "'>$row[HAUS_STRASSE] $row[HAUS_NUMMER]</a><br>\n";
        }
    }
}

function objekt_id_of_haus($haus_id)
{
    $result = DB::select("SELECT OBJEKT_ID FROM HAUS WHERE HAUS_ID='$haus_id' && HAUS_AKTUELL='1' ORDER BY HAUS_DAT DESC LIMIT 0,1");
    if (!empty($result)) {
        foreach ($result as $row) {
            return $row['OBJEKT_ID'];
        }
    }
}

function haus_id_of_einheit($einheit_id)
{
    $result = DB::select("SELECT HAUS_ID FROM EINHEIT WHERE EINHEIT_ID='$einheit_id' && EINHEIT_AKTUELL='1' ORDER BY EINHEIT_DAT DESC LIMIT 0,1");
    if (!empty($result)) {
        foreach ($result as $row) {
            return $row['HAUS_ID'];
        }
    }
}

function einheiten_links($objekt_id, $haus_id)
{
    $daten_rein = request()->input('daten_rein');
    $result = DB::select("SELECT EINHEIT_ID, EINHEIT_KURZNAME, EINHEIT_LAGE FROM EINHEIT WHERE HAUS_ID='$haus_id' && EINHEIT_AKTUELL='1' ORDER BY EINHEIT_KURZNAME ASC ");
    if (empty($result)) {
        echo "<h5 class=\"fehler\">Keine Einheiten im ausgewählten Haus</h5>";
        echo "<p class=\"hinweis\">Bitte zuerst Einheit im Haus anlegen - <a href='" . route('web::einheitenform::legacy', ['daten_rein' => 'anlegen']) . "'>Einheit anlegen HIER&nbsp;</a></p><br>";
    } else {
        foreach($result as $row) {
            echo "<a class=\"objekt_links\" href='" . route('web::einheitenform::legacy', ['daten_rein' => $daten_rein, 'objekt_id' => $objekt_id, 'haus_id' => $haus_id, 'einheit_id' => $row['EINHEIT_ID']]) . "'>$row[EINHEIT_KURZNAME] - $row[EINHEIT_LAGE]</a><br>\n";
        }
    }
}

function einheiten_liste($haus_id)
{
    $result = DB::select("SELECT EINHEIT_ID, EINHEIT_KURZNAME, EINHEIT_LAGE, EINHEIT_QM FROM EINHEIT WHERE HAUS_ID='$haus_id' && EINHEIT_AKTUELL='1' ORDER BY EINHEIT_KURZNAME ASC ");
    if (empty($result)) {
        fehlermeldung_ausgeben("<h5 class=\"fehler\">Keine Einheiten im ausgewählten Haus</h5>");
        hinweis_ausgeben("Bitte zuerst hier Einheit im Haus anlegen</p>");
    } else {
        echo "<div class=\"tabelle\">";
        echo "<table>";
        echo "<tr class=\"feldernamen\"><td>EINHEIT KURZNAME</td><td>EINHEIT LAGE</td><td>FLÄCHE</td></tr>\n";
        $counter = 0;
        foreach ($result as $row) {
            $counter++;
            if ($counter == 1) {
                echo "<tr class=\"zeile1\"><td>$row[EINHEIT_KURZNAME]</td><td>$row[EINHEIT_LAGE]</td><td>$row[EINHEIT_QM] m²</td></tr>\n";
            }
            if ($counter == 2) {
                echo "<tr class=\"zeile2\"><td>$row[EINHEIT_KURZNAME]</td><td>$row[EINHEIT_LAGE]</td><td>$row[EINHEIT_QM] m²</td></tr>\n";
                $counter = 0;
            }
        }

        echo "</table>";
        echo "</div>";
    }
}
