<?php

function import_me()
{
    $datei = "zahlungen.csv"; // DATEINAME
    $tabelle_in_gross = strtoupper($datei); // Tabelle in GROßBUCHSTABEN
    $array = file($datei); // DATEI IN ARRAY EINLESEN
    echo $array [0]; // ZEILE 0 mit Überschriften
    $feldernamen [] = explode(";", $array [0]); // FELDNAMEN AUS ZEILE 0 IN ARRAY EINLESEN
    $feldnamen_string = implode("`,`", $feldernamen [0]); // FELDERNAMEN ZU STRING
    $feldnamen_string = ltrim($feldnamen_string); // LEERZEICHEN VORN WEG
    $feldnamen_string = rtrim($feldnamen_string); // LEERZEICHEN HINTEN WEG
    $dat_feld = "$tabelle_in_gross" . "_DAT"; // DAT_FELD ZUSAMMENSTELLEN z.B(EINHEIT_DAT)
    $id_feld = "$tabelle_in_gross" . "_ID"; // ID_FELD ZUSAMMENSTELLEN z.B(EINHEIT_ID)
    $aktuell_feld = "$tabelle_in_gross" . "_AKTUELL"; // AKTUELL_FELD ZUSAMMENSTELLEN z.B(EINHEIT_AKTUELL)
    $feldnamen_sql = "`$dat_feld`, `$id_feld`, `"; // ALLE FELDNAMEN FÜR MYSQL ZUSAMMENSTELLEN
    $feldnamen_sql .= "$feldnamen_string";
    $feldnamen_sql .= "`, `$aktuell_feld`";

    echo "<b>Importiere daten aus $datei nach MYSQL $tabelle:</b><br><br>";

    for ($i = 1; $i < count($array); $i++) // Datei ab Zeile1 einlesen, weil Zeile 0 Überschrift ist
    {

        $zeile [$i] = explode(";", $array [$i]); // Zeile in Array einlesen

        $zeilenwerte_string = "'"; // Werte für MYSQL zusammenstellen
        $zeilenwerte_string .= implode(",", $zeile [$i]);
        $zeilenwerte_string = ltrim($zeilenwerte_string); // Leerzeichen vorn weg
        $zeilenwerte_string = rtrim($zeilenwerte_string); // Leerzeichen hinten weg
        $zeilenwerte_string .= "'"; // aktuell

        echo "$zeilenwerte_string<br>";

        $db_abfrage = "INSERT INTO MIETE_ZAHLBETRAG VALUES ($zeilenwerte_string)";
        echo "<br>DB = $db_abfrage<br>";
        $resultat = mysql_query($db_abfrage) or die (mysql_error());
        echo "zeile $i aus $tabelle importiert<br>";
    }
}

import_me();

// import_me(objekt);
// import_me(haus);
// import_me(einheit);
// import_me(person);
