<?php

import_me('miete_214');
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
    for ($i = 20000; $i < 30163; $i++) // Datei ab Zeile1 einlesen, weil Zeile 0
    {
        $zeile [$i] = explode(":", $array [$i]); // Zeile in Array einlesen

        $zeile [$i] [0] = textrep($zeile [$i] [0]);
        $zeile [$i] [1] = textrep($zeile [$i] [1]);
        $zeile [$i] [2] = textrep($zeile [$i] [2]);
        $zeile [$i] [3] = textrep($zeile [$i] [3]);
        $zeile [$i] [4] = textrep($zeile [$i] [4]);
        $zeile [$i] [5] = textrep($zeile [$i] [5]);
        $zeile [$i] [6] = textrep($zeile [$i] [6]);
        $zeile [$i] [7] = textrep($zeile [$i] [7]);
        $zeile [$i] [8] = textrep($zeile [$i] [8]);
        $zeile [$i] [9] = textrep($zeile [$i] [9]);

        /* MV begin */
        $form = new mietkonto ();

        $FMeinheit_name = rtrim(ltrim($zeile [$i] [0]));
        if (!empty ($FMeinheit_name)) {
            $datum = rtrim(ltrim($zeile [$i] [1]));
            $betrag = rtrim(ltrim($zeile [$i] [2]));
            $mv_id = mv_id_aus_transtab($FMeinheit_name);
            $betrag = explode(",", $betrag);
            $vorkomma = $betrag [0];
            $nachkomma = $betrag [1];
            $betrag = "$vorkomma.$nachkomma";
            $bemerkung = rtrim(ltrim($zeile [$i] [3]));
            $bemerkung = mysql_escape_string($bemerkung);

            if (!isset ($mv_id) or $mv_id == 0) {
                $einheit_id = einheit_id_aus_transtab($FMeinheit_name);
                $kostentraeger_typ = 'Einheit';
                if (!empty ($einheit_id)) {
                    echo "Einheit $einheit_id in me<br>";
                    if (!preg_match("/Miete Sollstellung/i", $bemerkung)) {
                        if (preg_match("/Betriebskostenabrechnung/i", $bemerkung) or preg_match("/Heizkostenabrechnung/i", $bemerkung) or preg_match("/Saldo Vortrag Vorverwaltung/", $bemerkung)) {
                            $form = new mietkonto ();
                            $datum_arr = explode(".", $datum);
                            $tag = $datum_arr [0];
                            $monat = $datum_arr [1];
                            $jahr = $datum_arr [2];
                            $a_datum = "$jahr-$monat-$tag";
                            $e_datum = "$jahr-$monat-$tag";
                            $form->mietentwicklung_speichern('Einheit', $einheit_id, $bemerkung, $betrag, $a_datum, $e_datum);
                        } else {
                            $kostentraeger_typ = 'Einheit';
                            $kostentraeger_id = $einheit_id;
                            $geldkonto_einheit = new geld_konten_id_ermitteln ();
                            $geldkonto_einheit->geld_konten_id_ermitteln_f('Einheit', $einheit_id);
                            if (!empty ($geldkonto_einheit->konto_id)) {
                                $form->import_miete_zahlbetrag_buchen('999999', 'Einheit', $einheit_id, $datum, $betrag, $bemerkung, $geldkonto_einheit->konto_id, '80001');
                                echo "$i e_id->zb gespeichert<br>";
                            }
                        }
                    } else {
                        echo "$i - sollst<br>";
                    } // end if sollstellung
                } // ENDE IF EINHEIT
            } // !mv_idend if

            if (isset ($mv_id) && $mv_id != 0) {
                if (!preg_match("/Miete Sollstellung/", $bemerkung)) {
                    $kostentraeger_typ = 'Mietvertrag';
                    if (preg_match("/Betriebskostenabrechnung/i", $bemerkung) or preg_match("/Heizkostenabrechnung/i", $bemerkung) or preg_match("/Saldo Vortrag Vorverwaltung/", $bemerkung)) {
                        $form = new mietkonto ();
                        $datum_arr = explode(".", $datum);
                        $tag = $datum_arr [0];
                        $monat = $datum_arr [1];
                        $jahr = $datum_arr [2];
                        $a_datum = "$jahr-$monat-$tag";
                        $e_datum = "$jahr-$monat-$tag";
                        echo "$i mv->me gespeichert<br>";
                        $form->mietentwicklung_speichern('Mietvertrag', $mv_id, $bemerkung, $betrag, $a_datum, $e_datum);
                    } else {
                        $kostentraeger_typ = 'Mietvertrag';
                        $kostentraeger_id = $mv_id;
                        $geldkonto_ins = new geld_konten_id_ermitteln ();
                        $geldkonto_ins->geld_konten_id_ermitteln_f('Mietvertrag', $mv_id);
                        if (!empty ($geldkonto_ins->konto_id)) {
                            $form->import_miete_zahlbetrag_buchen('999999', 'MIETVERTRAG', $mv_id, $datum, $betrag, $bemerkung, $geldkonto_ins->konto_id, '80001');
                            echo "$i mv->zb gespeichert<br>";
                        } else {
                            echo "$i mv->me nicht gespeichert, kein gk<br>";
                        }
                    }
                } else {
                    echo "$i mv soll<br>";
                } // sollmiete
            } // kein mv_id
        } // kein einheitname

        $zb_exists = $form->check_zahlbetrag('999999', $kostentraeger_typ, $kostentraeger_id, $datum, $betrag, $bemerkung, $geldkonto_ins->konto_id, '80001');
        if (!$zb_exists) {
            echo "Nicht importiert Zeile $i +1:<br><br>";
            print_r($zeile [$i]);
        }
    } // end for
} // end function

function kontrolle_zb_mb()
{
    $result = mysql_query("SELECT BUCHUNGSNUMMER, BETRAG FROM MIETE_ZAHLBETRAG");

    while ($row = mysql_fetch_assoc($result)) {
        $bnr = $row ['BUCHUNGSNUMMER'];
        $zb_betrag = $row ['BETRAG'];
        $intern = summe_mb($bnr);
        // echo $bnr;
        if ($zb_betrag != $intern) {
            echo "NICHT OK $bnr<br>";
        }
    }
}

function summe_mb($bnr)
{
    $result = mysql_query("SELECT SUM(BETRAG) AS INTERN FROM MIETBUCHUNGEN WHERE BUCHUNGSNUMMER='$bnr'");
    $row = mysql_fetch_assoc($result);
    $intern_betrag = $row ['INTERN'];
    return $intern_betrag;
}

function mv_id_aus_transtab($fm_einheitenname)
{
    $db_abfrage = "SELECT MIETVERTRAG_ID FROM TRANSFER_TAB WHERE RTRIM( LTRIM( FM_Einheitenname ) ) ='$fm_einheitenname'  && MIETVERTRAG_ID!='0'order by MIETVERTRAG_ID DESC limit 0,1";
    $resultat = mysql_query($db_abfrage) or die (mysql_error());

    while (list ($MIETVERTRAG_ID) = mysql_fetch_row($resultat)) {
        return $MIETVERTRAG_ID;
    }
}

function einheit_id_aus_transtab($fm_einheitenname)
{
    $db_abfrage = "SELECT EINHEIT_ID FROM TRANSFER_TAB WHERE RTRIM( LTRIM( FM_Einheitenname ) ) ='$fm_einheitenname'  order by MIETVERTRAG_ID DESC limit 0,1";
    $resultat = mysql_query($db_abfrage) or die (mysql_error());

    while (list ($EINHEIT_ID) = mysql_fetch_row($resultat)) {
        return $EINHEIT_ID;
    }
}

class geld_konten_id_ermitteln
{
    /* Berlussimo DATEN */
    var $konto_id;

    /* Diese Funktion ermittelt die hinterlegten vererbbaren Geldkontonummern aus Berlussimo */
    function geld_konten_id_ermitteln_f($kostentraeger_typ, $kostentraeger_id)
    {
        $geldkonten_anzahl = $this->geldkonten_anzahl($kostentraeger_typ, $kostentraeger_id);
        if ($geldkonten_anzahl > 0) {
            $konten_arr = $this->geldkonten_arr($kostentraeger_typ, $kostentraeger_id);
            $this->konto_id = $konten_arr ['0'] ['KONTO_ID'];
        } else {
            if ($kostentraeger_typ == 'Mietvertrag') {
                $mietvertrag_info = new mietvertrag ();
                $einheit_id = $mietvertrag_info->get_einheit_id_von_mietvertrag($kostentraeger_id);
                $this->geld_konten_id_ermitteln_f('Einheit', $einheit_id);
            }

            if ($kostentraeger_typ == 'Einheit') {
                $einheit_info = new einheit ();
                $einheit_info->get_einheit_info($kostentraeger_id);
                $this->geld_konten_id_ermitteln_f('Haus', $einheit_info->haus_id);
            }

            if ($kostentraeger_typ == 'Haus') {
                $haus_info = new haus ();
                $haus_info->get_haus_info($kostentraeger_id);
                $this->geld_konten_id_ermitteln_f('Objekt', $haus_info->objekt_id);
            }
        }
    }

    /* Funktion zur Ermittlung der Anzahl der Geldkonten */

    function geldkonten_anzahl($kostentraeger_typ, $kostentraeger_id)
    {
        $result = mysql_query("SELECT GELD_KONTEN.KONTO_ID, GELD_KONTEN.BEGUENSTIGTER, GELD_KONTEN.KONTONUMMER, GELD_KONTEN.BLZ FROM GELD_KONTEN_ZUWEISUNG, GELD_KONTEN WHERE KOSTENTRAEGER_TYP = '$kostentraeger_typ' && KOSTENTRAEGER_ID = '$kostentraeger_id' && GELD_KONTEN.KONTO_ID = GELD_KONTEN_ZUWEISUNG.KONTO_ID && GELD_KONTEN_ZUWEISUNG.AKTUELL = '1' && GELD_KONTEN.AKTUELL = '1' ORDER BY GELD_KONTEN.KONTO_ID ASC");

        $numrows = mysql_numrows($result);
        return $numrows;
    }

    /* Funktion zur Ermittlung der Anzahl der Geldkonten */

    function geldkonten_arr($kostentraeger_typ, $kostentraeger_id)
    {
        $result = mysql_query("SELECT GELD_KONTEN.KONTO_ID, GELD_KONTEN.BEGUENSTIGTER, GELD_KONTEN.KONTONUMMER, GELD_KONTEN.BLZ FROM GELD_KONTEN_ZUWEISUNG, GELD_KONTEN WHERE KOSTENTRAEGER_TYP = '$kostentraeger_typ' && KOSTENTRAEGER_ID = '$kostentraeger_id' && GELD_KONTEN.KONTO_ID = GELD_KONTEN_ZUWEISUNG.KONTO_ID && GELD_KONTEN_ZUWEISUNG.AKTUELL = '1' && GELD_KONTEN.AKTUELL = '1' ORDER BY GELD_KONTEN.KONTO_ID ASC");

        $numrows = mysql_numrows($result);
        if ($numrows > 0) {
            while ($row = mysql_fetch_assoc($result))
                $my_array [] = $row;
            return $my_array;
        } else {
            return FALSE;
        }
    }
} // end class
function textrep($text)
{
    return str_replace("\r\n", '', $text);
}
