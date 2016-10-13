<?php

import_me('e201');
function import_me($tabelle)
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
    for ($i = 0; $i < count($array); $i++) // Datei ab Zeile1 einlesen, weil Zeile 0 Überschrift ist
    {

        $zeile [$i] = explode(":", $array [$i]); // Zeile in Array einlesen
        /* MV begin */
        $form = new mietkonto ();
        $mv_id = $zeile [$i] [1];
        $datum = $zeile [$i] [3];
        $betrag = $zeile [$i] [4];
        $betrag = $form->nummer_komma2punkt($betrag);
        $bemerkung = $zeile [$i] [5];

        if (preg_match("/Betriebskosten/i", $bemerkung) || preg_match("/Heizkosten/i", $bemerkung)) {
            echo "$i Es wurde eine Übereinstimmung gefunden.<br>";
            $form = new mietkonto ();
            $datum_arr = explode(".", $datum);
            $monat = $datum_arr [1];
            $jahr = $datum_arr [2];
            $betrag = substr($betrag, 1);
            $lastday = date('d', mktime(0, 0, -1, $monat, 1, $jahr));
            $a_datum = "$jahr-$monat-01";
            $e_datum = "$jahr-$monat-$lastday";

            // echo "<h1>$lastday</h1>";

            $form->mietentwicklung_speichern($mv_id, $bemerkung, $betrag, $a_datum, $e_datum);
        } else {
            $form = new mietkonto ();
            $datum_arr = explode(".", $datum);
            $monat = $datum_arr [1];
            $jahr = $datum_arr [2];
            $tag = $datum_arr [0];
            $buchungsdatum = "$jahr-$monat-$tag";
            echo "$i $mv_id $datum $betrag $bemerkung<br>";
            $form->miete_zahlbetrag_buchen('999999', $mv_id, $buchungsdatum, $betrag, $bemerkung, '11');
        }
    }
}
