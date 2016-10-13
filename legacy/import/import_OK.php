<?php

class ids
{
    var $id;

    public function last_id($tabelle)
    {
        $spaltenname_in_gross = strtoupper($tabelle);
        $zusatz = "_ID";
        $select_spaltenname = "$spaltenname_in_gross$zusatz";
        $db_abfrage = "SELECT $select_spaltenname FROM $spaltenname_in_gross ORDER BY $select_spaltenname DESC LIMIT 0,1";
        $resultat = mysql_query($db_abfrage) or die (mysql_error());
        while (list ($select_spaltenname) = mysql_fetch_row($resultat))
            $this->id = $select_spaltenname;
    }
}

function import_me($tabelle)
{
    $tabelle_in_gross = strtoupper($tabelle); // Tabelle in GROßBUCHSTABEN
    $datei = "$tabelle.csv"; // DATEINAME
    $array = file($datei); // DATEI IN ARRAY EINLESEN
    // echo $array[0]; //ZEILE 0 mit Überschriften
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
    $feldnamen_sql = ltrim($feldnamen_sql);

    echo "<b>Importiere daten aus $datei nach MYSQL $tabelle:</b><br><br>";

    for ($i = 1; $i < count($array); $i++) // Datei ab Zeile1 einlesen, weil Zeile 0 Überschrift ist
    {
        // ####letzte id der tabelle
        $akt_id = new ids (); // Neues Objekt aktuelle Id der tabelle
        $akt_id->last_id($tabelle); // Objektwert zuweisen
        $letzte_tab_id = $akt_id->id; // Letzte id
        $letzte_tab_id = $letzte_tab_id + 1; // Letzte id um 1 erhöhen
        // ####letzte id der tabelle

        $zeile [$i] = explode(";", $array [$i]); // Zeile in Array einlesen

        $zeilenwerte_string = "NULL, '$letzte_tab_id', '"; // Werte für MYSQL zusammenstellen
        $zeilenwerte_string .= implode("','", $zeile [$i]);
        $zeilenwerte_string .= "', '1'"; // aktuell
        $zeilenwerte_string = ltrim($zeilenwerte_string); // Leerzeichen vorn weg
        $zeilenwerte_string = rtrim($zeilenwerte_string); // Leerzeichen hinten weg

        $db_abfrage = "INSERT INTO $tabelle_in_gross ($feldnamen_sql) VALUES ($zeilenwerte_string)";
        $resultat = mysql_query($db_abfrage) or die (mysql_error());
        echo "zeile $i aus $tabelle importiert<br>";
    }
}

// import_me(objekt);
// import_me(haus);
import_me('einheit');
