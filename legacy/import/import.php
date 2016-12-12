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
 * @filesource   $HeadURL: http://192.168.2.52/svn/berlussimo_1/tags/02.11.2010 - Downloadversion 0.27/import/import.php $
 * @version      $Revision: 6 $
 * @modifiedby   $LastChangedBy: sivac $
 * @lastmodified $Date: 2010-09-21 10:34:50 +0200 (Di, 21 Sep 2010) $
 *
 */
/*
 * Das ist eine Test bzw. Spieledatei, aus der kann man viel lernen,
 *
 */

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
        echo "<br>DB = $db_abfrage<br>";
        $resultat = mysql_query($db_abfrage) or die (mysql_error());
        echo "zeile $i aus $tabelle importiert<br>";
    }
}

update_flaeche("einheit");
function update_flaeche($tabelle)
{
    $tabelle_in_gross = strtoupper($tabelle); // Tabelle in GROßBUCHSTABEN
    $datei = "$tabelle.csv"; // DATEINAME
    $array = file($datei); // DATEI IN ARRAY EINLESEN
    echo $array [0]; // ZEILE 0 mit Überschriften
    $feldernamen [] = explode(":", $array [0]); // FELDNAMEN AUS ZEILE 0 IN ARRAY EINLESEN
    $feld1 = $feldernamen [0] [0]; // FELD1 - IMPORT nur zur info
    echo "<h1>$feld1</h1>";

    echo "<b>Importiere daten aus $datei nach MYSQL $tabelle_in_gross:</b><br><br>";
    $zeile [1] = explode(":", $array [1]); // Zeile in Array einlesen
    $zeile [2] = explode(":", $array [2]); // Zeile in Array einlesen
    for ($i = 1; $i < count($array); $i++) // Datei ab Zeile1 einlesen, weil Zeile 0 Überschrift ist
    {

        $zeile [$i] = explode(";", $array [$i]); // Zeile in Array einlesen
        /* MV begin */
        $form = new mietkonto ();
        $e_name = $zeile [$i] [0];
        $flaeche = $zeile [$i] [2];
        $flaeche = $form->nummer_komma2punkt($flaeche);

        $e_dat = dat_of_einheit($e_name);
        echo "$e_dat - $e_name - $flaeche<br>";

        $db_abfrage = "UPDATE EINHEIT SET EINHEIT_QM='$flaeche' WHERE EINHEIT_AKTUELL='1' && EINHEIT_DAT='$e_dat'";
        $resultat = mysql_query($db_abfrage) or die (mysql_error());
    }
}

function dat_of_einheit($einheit_name)
{
    $db_abfrage = "SELECT EINHEIT_DAT FROM EINHEIT WHERE EINHEIT_KURZNAME='$einheit_name' && EINHEIT_AKTUELL='1' order by EINHEIT_DAT DESC limit 0,1";
    $resultat = mysql_query($db_abfrage) or die (mysql_error());

    while (list ($EINHEIT_DAT) = mysql_fetch_row($resultat)) {
        return $EINHEIT_DAT;
    }
}
