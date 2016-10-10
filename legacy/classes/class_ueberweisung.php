<?php

class ueberweisung
{
    function form_rechnung_dtaus_sepa($belegnr)
    {
        if (!session()->has('geldkonto_id')) {
            fehlermeldung_ausgeben("Geldkonto von welchem überwiesen wird WÄHLEN!!!!");
        }
        $f = new formular ();
        $r = new rechnungen ();
        $g = new geldkonto_info ();
        $r->rechnung_grunddaten_holen($belegnr);

        $f->erstelle_formular("Rechnung über SEPA zahlen", NULL);
        if ($r->status_bezahlt == '0') {

            if ($r->rechnungstyp == 'Rechnung' or $r->rechnungstyp == 'Buchungsbeleg') {
                $sep = new sepa ();
                $gk_a_id = session()->get('geldkonto_id');
                $f->hidden_feld('gk_id', $gk_a_id);

                if ($sep->dropdown_sepa_geldkonten('Überweisen an', 'empf_sepa_gk_id', 'empf_sepa_gk_id', $r->rechnungs_aussteller_typ, $r->rechnungs_aussteller_id) == false) {
                    throw new \App\Exceptions\MessageException(
                        new \App\Messages\ErrorMessage("SEPA Kontoverbindung Rg. Aussteller fehlt.")
                    );
                }

                $js_opt = "onchange=\"var betrag_feld = document.getElementById('betrag'); betrag_feld.value=nummer_punkt2komma(this.value);\";";
                $r->dropdown_buchungs_betrag_kurz_sepa('Zu zahlenden Betrag wählen', 'betrags_art', 'betrags_art', $js_opt);
                $t_betrag = nummer_punkt2komma($r->rechnungs_skontobetrag);

                $f->text_feld('Ausgewählten Betrag eingeben', 'betrag', $t_betrag, '10', 'betrag', '');
                $vzweck_140 = substr("$r->rechnungs_aussteller_name, Rnr:$r->rechnungsnummer, $r->kurzbeschreibung", 0, 140);
                $f->text_bereich('Verwendungszweck Max 140Zeichen', 'vzweck', "$vzweck_140", 60, 60, 'vzweck');
                $kk = new kontenrahmen ();
                $kk->dropdown_kontorahmenkonten('Konto', 'konto', 'konto', 'Geldkonto', session()->get('geldkonto_id'), '');

                $kb = str_replace("<br>", "\n", $r->kurzbeschreibung);

                $f->text_bereich('Buchungstext', 'buchungstext', "Erfnr:$r->belegnr, WE:$r->empfaenger_eingangs_rnr, Zahlungsausgang Rnr:$r->rechnungsnummer, $kb", 60, 60, 'buchungstex');
            }

            /* Alt aus dtaus */
            $f->hidden_feld("bezugstab", "RECHNUNG");
            $f->hidden_feld("bezugsid", $belegnr);

            /* Neu SEPA */
            $f->hidden_feld('option', 'sepa_sammler_hinzu');
            $f->hidden_feld('kat', 'RECHNUNG');
            $f->hidden_feld('kos_typ', $r->rechnungs_aussteller_typ);
            $f->hidden_feld('kos_id', $r->rechnungs_aussteller_id);
            $f->send_button('sndBtn', 'Hinzufügen');
        } else {
            echo "Diese Rechnung wurde am $r->bezahlt_am als bezahlt markiert";
        }
        $f->ende_formular();
    }

    function dtaus_zeilen_arr($g_konto_id)
    {
        $result = DB::select("SELECT A_KONTO_ID, E_KONTO_ID, DATUM, BETRAG, VZWECK1, VZWECK2, VZWECK3, BUCHUNGSTEXT FROM `UEBERWEISUNG` WHERE `A_KONTO_ID` = '$g_konto_id'  AND `AKTUELL` = '1' && DTAUS_ID IS NULL");
        if (!empty($result)) {
            return $result;
        } else {
            return false;
        }
    }

    function letzte_dtaus_id()
    {
        $result = DB::select("SELECT DTAUS_ID  FROM `UEBERWEISUNG` WHERE  `AKTUELL` = '1' && DTAUS_ID IS NOT NULL ORDER BY DTAUS_ID DESC LIMIT 0,1");
        $row = $result[0];
        return $row ['DTAUS_ID'];
    }

    function dtausdatei_zeilen_arr($dtaus_id)
    {
        $result = DB::select("SELECT * FROM `UEBERWEISUNG` WHERE `DTAUS_ID` = '$dtaus_id'  AND `AKTUELL` = '1'");

        if (!empty($result)) {
            return $result;
        } else {
            return false;
        }
    }

    function dtaus_gesamt_summe($dtaus_id)
    {
        $result = DB::select("SELECT SUM(BETRAG) AS SUMME FROM `UEBERWEISUNG` WHERE `DTAUS_ID` = '$dtaus_id'  AND `AKTUELL` = '1'");

        if (!empty($result)) {
            $row = $result[0];
            return $row ['SUMME'];
        } else {
            return FALSE;
        }
    }
} // end class
