<?php

if (request()->has('option')) {
    $option = request()->input('option');
} else {
    $option = 'default';
}
switch ($option) {

    case "rechnung_erfassen" :
        $form = new mietkonto ();
        $rechnungsformular = new rechnung ();
        $rechnungsformular->form_rechnung_erfassen();
        $form->ende_formular();
        break;

    case "gutschrift_erfassen" :
        $form = new mietkonto ();
        $rechnungsformular = new rechnung ();
        $rechnungsformular->form_gutschrift_erfassen();
        $form->ende_formular();
        break;

    case "rechnung_an_kasse_erfassen" :
        $form = new mietkonto ();
        $rechnungsformular = new rechnung ();
        $rechnungsformular->form_rechnung_erfassen_an_kasse();
        $form->ende_formular();
        break;

    case "rechnung_erfassen1" :
        $form = new mietkonto ();
        $form->erstelle_formular("Rechnungsdaten überprüfen", NULL);
        echo "<p><b>Eingegebene Rechnungsdaten:</b></p>";
        $clean_arr = post_array_bereinigen();
        foreach ($clean_arr as $key => $value) {

            if (($key != 'submit_rechnung1') and ($key != 'option')) {
                // echo "$key " . $value . "<br>";
                $form->hidden_feld($key, $value);
            }
        }
        if ($clean_arr ['aussteller_id'] == $clean_arr ['empfaenger_id']) {
            fehlermeldung_ausgeben("Rechnungsaussteller- und Empfänger sind identisch.<br>");
        }
        if ($clean_arr ['aussteller_id'] == 0) {
            $fehler = true;
            fehlermeldung_ausgeben("Bitte Rechnungsaussteller wählen.<br>");
        }

        if (!isset ($fehler)) {
            if ($clean_arr ['empfaenger_typ'] == 'Kasse') {
                $kassen_info = new kasse ();
                $kassen_info->get_kassen_info($clean_arr ['Empfaenger']);
                $partner_info = new partner ();
                $aussteller = $partner_info->get_partner_name($clean_arr ['Aussteller']);
                $empfaenger = "" . $kassen_info->kassen_name . " - " . $kassen_info->kassen_verwalter . "";
            }
            if ($clean_arr ['empfaenger_typ'] == 'Partner') {
                $partner_info = new partner ();
                $aussteller = $partner_info->get_partner_name($clean_arr ['aussteller_id']);
                $empfaenger = $partner_info->get_partner_name($clean_arr ['empfaenger_id']);
            }

            echo "Rechnungsnummer: $clean_arr[rechnungsnummer]<br>";
            echo "Eingangsdatum: $clean_arr[eingangsdatum]<br>";
            if (preg_match("/,/i", $clean_arr ['nettobetrag'])) {
                $clean_arr ['nettobetrag'] = nummer_komma2punkt($clean_arr ['nettobetrag']);
            }
            if (preg_match("/,/i", $clean_arr ['bruttobetrag'])) {
                $clean_arr ['bruttobetrag'] = nummer_komma2punkt($clean_arr ['bruttobetrag']);
            }
            if (preg_match("/,/i", $clean_arr ['skontobetrag'])) {
                $clean_arr ['skontobetrag'] = nummer_komma2punkt($clean_arr ['skontobetrag']);
            }

            $netto_betrag_komma = nummer_punkt2komma($clean_arr ['nettobetrag']);
            $brutto_betrag_komma = nummer_punkt2komma($clean_arr ['bruttobetrag']);

            echo "Fällig am: $clean_arr[faellig_am] <br>";
            echo "Kurzbeschreibung: $clean_arr[kurzbeschreibung] <br>";
            $geld_konto_info = new geldkonto_info ();
            $geld_konto_info->dropdown_geldkonten('Partner', $clean_arr ['aussteller_id']);

            $form->hidden_feld("option", "rechnung_erfassen2");
            $form->send_button("submit_rechnung2", "Rechnung speichern");
        } else {
            backlink();
        }
        $form->ende_formular();
        break;

    case "rechnung_erfassen2" :
        $form = new mietkonto ();
        $form->erstelle_formular("Rechnungsdaten werden gespeichert", NULL);
        echo "<p><b>Gespeicherte Rechnungsdaten:</b></p>";
        $clean_arr = post_array_bereinigen();
        $rechnung = new rechnung ();
        $rechnung->rechnung_speichern($clean_arr);
        $form->ende_formular();
        break;

    case "erfasste_rechnungen" :
        $form = new mietkonto ();
        $form->erstelle_formular("Erfasste Rechnungen", NULL);
        $rechnung = new rechnung ();
        $rechnung->erfasste_rechungen_anzeigen(); // LIMIT 10,10
        $form->ende_formular();
        break;

    case "vollstaendige_rechnungen" :
        $form = new mietkonto ();
        $form->erstelle_formular("Vollständig erfasste Rechnungen", NULL);
        $rechnung = new rechnung ();
        $rechnung->vollstaendig_erfasste_rechungen_anzeigen();
        $form->ende_formular();
        break;

    case "unvollstaendige_rechnungen" :
        $form = new mietkonto ();
        $form->erstelle_formular("Unvollständig erfasste Rechnungen", NULL);
        $rechnung = new rechnung ();
        $rechnung->unvollstaendig_erfasste_rechungen_anzeigen();
        $form->ende_formular();
        break;

    case "kontierte_rechnungen" :
        $form = new mietkonto ();
        $form->erstelle_formular("Vollständig kontierte Rechnungen", NULL);
        $rechnung = new rechnung ();
        $rechnung->vollstaendig_kontierte_rechungen_anzeigen();
        $form->ende_formular();
        break;

    case "nicht_kontierte_rechnungen" :
        $form = new mietkonto ();
        $form->erstelle_formular("Unvollständig oder nocht nicht kontierte Rechnungen", NULL);
        $rechnung = new rechnung ();
        $rechnung->unvollstaendig_kontierte_rechungen_anzeigen();
        $form->ende_formular();
        break;

    case "bezahlte_rechnungen" :
        $form = new formular ();
        $form->fieldset("Bezahlte Rechnungen", 'bezalte_rechnungen');
        $r = new rechnungen ();
        $r->bezahlte_rechnungen_anzeigen();
        $form->fieldset_ende();
        break;

    case "unbezahlte_rechnungen" :
        $form = new formular ();
        $form->fieldset("Unbezahlte Rechnungen", 'unbezahlte_rechnungen');
        $r = new rechnungen ();
        $r->unbezahlte_rechnungen_anzeigen();
        $form->fieldset_ende();
        break;

    case "bestaetigte_rechnungen" :
        $form = new formular ();
        $form->fieldset("Bezahlte Rechnungen", 'bezalte_rechnungen');
        $r = new rechnungen ();
        $r->bestaetigte_rechnungen_anzeigen();
        $form->fieldset_ende();
        break;

    case "unbestaetigte_rechnungen" :
        $form = new formular ();
        $form->fieldset("Unbezahlte Rechnungen", 'unbezahlte_rechnungen');
        $r = new rechnungen ();
        $r->unbestaetigte_rechnungen_anzeigen();
        $form->fieldset_ende();
        break;

    case "positions_pool" :
        $form = new mietkonto ();
        $form->erstelle_formular("Rechnung erstellen - Positionspool", NULL);
        $rechnung = new rechnung ();
        $objekte_ids = $rechnung->objekte_im_pool();
        for ($k = 0; $k < count($objekte_ids); $k++) {
            $objekt_id = $objekte_ids [$k];
            echo "$objekt_id<hr>";
            $haeuser_ids = $rechnung->haeuser_vom_objekt_im_pool($objekt_id);
            echo "<br>";
            $einheiten_ids = $rechnung->einheiten_vom_objekt_im_pool($objekt_id);
            echo "<hr>";
        }
        $form->ende_formular();
        break;

    case "pool_rechnungen" :
        $form = new mietkonto ();
        $form->erstelle_formular("Rechnung erstellen - aus dem Positionspool", NULL);
        $rechnung = new rechnung ();
        /* $elemente_aus_pool[OBJEKTE] o. [HAUS] o. [EINHEITEN] */
        $elemente_aus_pool = $rechnung->elemente_im_pool_baum();
        $objekte_ids = $elemente_aus_pool ['OBJEKTE'];
        $objekt_info = new objekt ();
        $haus_info = new haus ();
        $einheit_info = new einheit ();
        $lager_info = new lager ();
        if (is_array($elemente_aus_pool)) {
            for ($k = 0; $k < count($objekte_ids); $k++) {
                $objekt_id = $objekte_ids [$k];
                $objekt_info->get_objekt_name($objekt_id);
                $objekt_name = $objekt_info->objekt_name;
                $kontierung_id_arr = $rechnung->rechnung_aus_pool_zusammenstellen('Objekt', $objekt_id);
                $objekt_link_rechnung = "<a href='" . route('web::rechnungen::legacy', ['option' => 'rechnung_an_objekt', 'objekt_id' => $objekt_id]) . "'>Rechnung erstellen</a>";

                $rrg = new rechnungen ();
                $summe_pool = $rrg->get_summe_kosten_pool('Objekt', $objekt_id);
                if ($summe_pool > 0) {
                    $objektkosten_link = "<a href='" . route('web::rechnungen::legacy', ['option' => 'objektkosten_in_rechnung', 'objekt_id' => $objekt_id]) . "' style='color:blue;'>Rechnung erstellen ($summe_pool)</a>";
                } else {
                    $objektkosten_link = '';
                }
                echo "<hr><h3>$objekt_name</h3>";
                echo "<b>Objektbezogene Kosten vom $objekt_name</b><br>";
                echo "<b>|-</b>Gesamtrechnung für Objekt $objekt_name (inkl Häuser / Einheiten) $objekt_link_rechnung<br>";
                echo "<b>|-</b>Objektkostenrechnung für Objekt $objekt_name $objektkosten_link<br>";
                $haeuser_ids = $elemente_aus_pool ['HAUS'];

                if (is_array($haeuser_ids)) {

                    echo "<b>&nbsp;&nbsp;&nbsp;Häuserbezogene Kosten vom $objekt_name</b><br>";
                    echo "<b>&nbsp;&nbsp;&nbsp;Rechnungen pro Haus - Haus wählen bitte</b><br>";
                    for ($g = 0; $g < count($haeuser_ids); $g++) {
                        $haus_id = $haeuser_ids [$g];

                        $rrg = new rechnungen ();
                        $summe_pool = $rrg->get_summe_kosten_pool('Haus', $haus_id);

                        $haus_info->get_haus_info($haus_id);
                        $haus_objekt_id = $haus_info->objekt_id;
                        $haus_link_rechnung = "<a href='" . route('web::rechnungen::legacy', ['option' => 'rechnung_an_haus', 'haus_id' => $haus_id]) . "'>Rechnung inkl. Einheiten</a>";
                        if ($summe_pool > 0) {
                            $hauskosten_link = "<a href='" . route('web::rechnungen::legacy', ['option' => 'hauskosten_in_rechnung', 'haus_id' => $haus_id]) . "' style='color:red;'>Nur Hauskosten ($summe_pool)</a>";
                        } else {
                            $hauskosten_link = '';
                        }
                        if ($objekt_id == $haus_objekt_id) {
                            echo "<b>&nbsp;&nbsp;&nbsp;|-</b> Haus " . $haus_info->haus_strasse . $haus_info->haus_nummer . " $haus_link_rechnung $hauskosten_link<br>";
                        }
                    }
                } // end if is_array $hauser_ids
                $einheiten_ids = $elemente_aus_pool ['EINHEITEN'];
                if (is_array($einheiten_ids)) {
                    echo "<b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;| Einheitsbezogene Kosten vom $objekt_name</b><br>";
                    echo "<b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;| Rechnungen pro Einheit - Einheit wählen bitte</b><br>";
                    for ($e = 0; $e < count($einheiten_ids); $e++) {
                        $einheit_id = $einheiten_ids [$e];
                        $einheit_info->get_einheit_haus($einheit_id);
                        $einheit_objekt_id = $einheit_info->objekt_id;

                        $rrg = new rechnungen ();
                        $summe_pool = $rrg->get_summe_kosten_pool('Einheit', $einheit_id);

                        if ($einheit_objekt_id == $objekt_id) {
                            if ($summe_pool > 0) {
                                $einheit_link_rechnung = "<a href='" . route('web::rechnungen::legacy', ['option' => 'rechnung_an_einheit', 'einheit_id' => $einheit_id]) . "' style='color:green;'>Rechnung erstellen ($summe_pool)</a>";
                            } else {
                                $einheit_link_rechnung = '';
                            }
                            echo "<b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;|-</b> Einheit " . $einheit_info->einheit_kurzname . "&nbsp;" . $einheit_info->haus_strasse . $einheit_info->haus_nummer . $einheit_info->einheit_lage . " $einheit_link_rechnung<br>";
                        }
                    }
                }
            } // end for first
        } // end if is_array elemente
        echo "<hr>";
        $lager_ids = $elemente_aus_pool ['LAGER'];
        if (is_array($lager_ids)) {
            echo "<b>| Lagerbezogene Kosten</b><br>";
            echo "<b>&nbsp;&nbsp;&nbsp;| Rechnungen pro Lager - Lager wählen bitte</b><br>";
            for ($f = 0; $f < count($lager_ids); $f++) {
                $lager_id = $lager_ids [$f];
                $lager_bezeichnung = $lager_info->lager_bezeichnung($lager_id);
                $rrg = new rechnungen ();
                $summe_pool = $rrg->get_summe_kosten_pool('Lager', $lager_id);
                if ($summe_pool > 0) {
                    $lager_link_rechnung = "<a href='" . route('web::rechnungen::legacy', ['option' => 'rechnung_an_lager', 'lager_id' => $lager_id]) . "' style='color:white;'>Rechnung erstellen ($summe_pool)</a>";
                } else {
                    $lager_link_csv = '';
                }
                echo "<b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;|-</b> Lager " . $lager_bezeichnung . "&nbsp;" . $einheit_info->haus_strasse . $einheit_info->haus_nummer . $einheit_info->einheit_lage . " $lager_link_rechnung<br>";
            }
        } else {
            echo "Keine lagerbezogenen Daten im Pool";
        }

        echo "<hr>";
        $partner_ids = $elemente_aus_pool ['PARTNER'];
        if (is_array($partner_ids)) {
            echo "<b>| Partnerbezogene Kosten</b><br>";
            echo "<b>&nbsp;&nbsp;&nbsp;| Rechnungen an Partner - Partner wählen bitte</b><br>";
            $r = new rechnung ();
            for ($f = 0; $f < count($partner_ids); $f++) {
                $partner_id = $partner_ids [$f];
                $rechnungs_empfaenger_name = $r->get_partner_name($partner_id);

                $rrg = new rechnungen ();
                $summe_pool = $rrg->get_summe_kosten_pool('Partner', $partner_id);
                if ($summe_pool > 0) {
                    $partner_link_rechnung = "<a href='" . route('web::rechnungen::legacy', ['option' => 'rechnung_an_partner', 'partner_id' => $partner_id]) . "' style='color:green;'>Rechnung erstellen ($summe_pool)</a>";
                } else {
                    $partner_link_rechnung = '';
                }

                echo "<b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;|-</b> Partner " . $rechnungs_empfaenger_name . "&nbsp;$partner_link_rechnung<br>";
            }
        } else {
            echo "Keine Partner Daten im Pool";
        }

        $form->ende_formular();
        break;

    case "pool_csv" :
        break;

    // #######################################
    case "rechnung_an_objekt" :
        $form = new mietkonto ();
        $form->erstelle_formular("Rechnung an Objekt aus Pool", NULL);
        $rechnung = new rechnung ();
        if (request()->has('objekt_id') && empty (request()->input('aussteller_id'))) {
            $kontierung_id_arr = $rechnung->rechnung_an_objekt_zusammenstellen(request()->input('objekt_id'));
            /* Feldernamen definieren - Überschrift Tabelle */
            if (is_array($kontierung_id_arr)) {
                foreach ($kontierung_id_arr [0] as $key => $value) {
                    $ueberschrift_felder_arr [] = $key;
                }
            }
            /* Rausfinden von wem die Rechnungen ausm Pool geschrieben werden */
            for ($a = 0; $a < count($kontierung_id_arr); $a++) {
                $beleg_nr = $kontierung_id_arr [$a] ['BELEG_NR'];
                $rechnung->rechnung_grunddaten_holen($beleg_nr);
                /* Empfänger der Rechnung wird zum Austeller der Auto...Rechnung */

                if ($rechnung->rechnungs_empfaenger_typ != 'Kasse') {
                    $aussteller_arr [] = $rechnung->rechnungs_empfaenger_id;
                } else {
                    $kassen_arr [] = $rechnung->rechnungs_empfaenger_id;
                }
            }
            if (isset ($aussteller_arr) && is_array($aussteller_arr)) {
                $aussteller_arr = array_unique($aussteller_arr);
                foreach ($aussteller_arr as $key => $value) {
                    $aussteller_arr_sortiert [] = $value;
                }
            }

            if (isset ($kassen_arr) && is_array($kassen_arr)) {
                $kassen_arr = array_unique($kassen_arr);
                foreach ($kassen_arr as $key => $value) {
                    $kassen_arr_sortiert [] = $value;
                }
            }

            /* Ausgabe der Links mit Rechnungsausteller namen */
            echo "<table>";
            echo "<tr><td>Wählen Sie bitte den Rechnungsaussteller aus!</td></tr>";
            if (isset ($aussteller_arr_sortiert) && is_array($aussteller_arr_sortiert)) {
                for ($a = 0; $a < count($aussteller_arr_sortiert); $a++) {
                    $partner_info = new partner ();
                    $partner_info->get_aussteller_info($aussteller_arr_sortiert [$a]);
                    $aussteller_id = $aussteller_arr_sortiert [$a];
                    $rechnung_von_link = "<a href='" . route('web::rechnungen::legacy', ['option' => 'rechnung_an_objekt', 'objekt_id' => request()->input('objekt_id'), 'aussteller_typ' => $rechnung->rechnungs_empfaenger_typ, 'aussteller_id' => $aussteller_id]) . "'>$rechnung->rechnungs_empfaenger_name</a>";

                    echo "<tr><td>$rechnung_von_link</td></tr>";
                }
            }
            if (isset ($kassen_arr_sortiert) && is_array($kassen_arr_sortiert)) {
                for ($a = 0; $a < count($kassen_arr_sortiert); $a++) {
                    $kassen_info = new kasse ();
                    $kassen_info->get_kassen_info($kassen_arr_sortiert [$a]);
                    $aussteller_id = $kassen_arr_sortiert [$a];
                    $rechnung_von_link = "<a href='" . route('web::rechnungen::legacy', ['option' => 'rechnung_an_objekt', 'objekt_id' => request()->input('objekt_id'), 'aussteller_typ' => 'Kasse', 'aussteller_id' => $aussteller_id]) . "'>" . $kassen_info->kassen_name . " - " . $kassen_info->kassen_verwalter . "</a>";
                    echo "<tr><td>$rechnung_von_link</td></tr>";
                }
            }
            echo "</table>";
            /* Ende der Ausgabe der Links mit Rechnungsausteller namen */
        } // end if

        if (request()->has('objekt_id') && request()->has('aussteller_id') && request()->has('aussteller_typ')) {
            $kontierung_id_arr = $rechnung->rechnung_an_objekt_zusammenstellen(request()->input('objekt_id'));
            $kontierung_id_arr_gefiltert = $rechnung->filtern_nach_austeller($kontierung_id_arr, request()->input('aussteller_typ'), request()->input('aussteller_id'));
            $rechnung->rechnung_schreiben_positionen_wahl('Objekt', request()->input('objekt_id'), $kontierung_id_arr_gefiltert, request()->input('aussteller_typ'), request()->input('aussteller_id'));
        }

        $form->ende_formular();
        break;

    // #######################################
    case "objektkosten_in_rechnung" :
        $form = new mietkonto ();
        $form->erstelle_formular("Objektkosten in Rechnung stellen", NULL);
        $rechnung = new rechnung ();
        if (request()->has('objekt_id')) {
            $kontierung_id_arr = $rechnung->rechnung_aus_pool_zusammenstellen('Objekt', request()->input('objekt_id'));
            if ($kontierung_id_arr == false) {
                echo "Keine Objektkosten";
            }
            /* Feldernamen definieren - Überschrift Tabelle */
            if (is_array($kontierung_id_arr)) {
                foreach ($kontierung_id_arr [0] as $key => $value) {
                    $ueberschrift_felder_arr [] = $key;
                }
            }
            /* Rausfinden von wem die Rechnungen ausm Pool geschrieben werden */
            for ($a = 0; $a < count($kontierung_id_arr); $a++) {
                $beleg_nr = $kontierung_id_arr [$a] ['BELEG_NR'];
                $rechnung->rechnung_grunddaten_holen($beleg_nr);
                /* Empfänger der Rechnung wird zum Austeller der Auto...Rechnung */

                if ($rechnung->rechnungs_empfaenger_typ != 'Kasse') {
                    $aussteller_arr [] = $rechnung->rechnungs_empfaenger_id;
                } else {
                    $kassen_arr [] = $rechnung->rechnungs_empfaenger_id;
                }
            }
            if (is_array($aussteller_arr)) {
                $aussteller_arr = array_unique($aussteller_arr);
                foreach ($aussteller_arr as $key => $value) {
                    $aussteller_arr_sortiert [] = $value;
                }
            }

            if (is_array($kassen_arr)) {
                $kassen_arr = array_unique($kassen_arr);
                foreach ($kassen_arr as $key => $value) {
                    $kassen_arr_sortiert [] = $value;
                }
            }

            /* Ausgabe der Links mit Rechnungsausteller namen */
            echo "<table>";
            echo "<tr><td>Wählen Sie bitte den Rechnungsaussteller aus!</td></tr>";
            if (is_array($aussteller_arr_sortiert)) {
                // print_r($aussteller_arr_sortiert);
                for ($a = 0; $a < count($aussteller_arr_sortiert); $a++) {
                    $partner_info = new partner ();
                    $partner_info->get_aussteller_info($aussteller_arr_sortiert [$a]);
                    $aussteller_id = $aussteller_arr_sortiert [$a];
                    $rechnung_von_link = "<a href='" . route('web::rechnungen::legacy', ['option' => 'objektkosten_in_rechnung', 'objekt_id' => request()->input('objekt_id'), 'aussteller_typ' => $rechnung->rechnungs_empfaenger_typ, 'aussteller_id' => $aussteller_id]) . "'>$rechnung->rechnungs_empfaenger_name</a>";

                    echo "<tr><td>$rechnung_von_link</td></tr>";
                }
            }
            if (is_array($kassen_arr_sortiert)) {
                for ($a = 0; $a < count($kassen_arr_sortiert); $a++) {
                    $kassen_info = new kasse ();
                    $kassen_info->get_kassen_info($kassen_arr_sortiert [$a]);
                    $aussteller_id = $kassen_arr_sortiert [$a];
                    $rechnung_von_link = "<a href='" . route('web::rechnungen::legacy', ['option' => 'objektkosten_in_rechnung', 'objekt_id' => request()->input('objekt_id'), 'aussteller_typ' => 'Kasse', 'aussteller_id' => $aussteller_id]) . "'>" . $kassen_info->kassen_name . " - " . $kassen_info->kassen_verwalter . "</a>";
                    echo "<tr><td>$rechnung_von_link</td></tr>";
                }
            }
            echo "</table>";
            /* Ende der Ausgabe der Links mit Rechnungsausteller namen */
        }
        if (request()->has('objekt_id') && request()->has('aussteller_id') && request()->has('aussteller_typ')) {
            $kontierung_id_arr = $rechnung->rechnung_aus_pool_zusammenstellen('Objekt', request()->input('objekt_id'));
            $kontierung_id_arr_gefiltert = $rechnung->filtern_nach_austeller($kontierung_id_arr, request()->input('aussteller_typ'), request()->input('aussteller_id'));
            $rechnung->rechnung_schreiben_positionen_wahl('Objekt', request()->input('objekt_id'), $kontierung_id_arr_gefiltert, request()->input('aussteller_typ'), request()->input('aussteller_id'));
        }
        $form->ende_formular();
        break;

    case "rechnung_an_einheit" :
        $form = new mietkonto ();
        $form->erstelle_formular("Rechnung für eine Einheit erstellen", NULL);
        $rechnung = new rechnung ();
        if (request()->has('einheit_id')) {
            $kontierung_id_arr = $rechnung->rechnung_aus_pool_zusammenstellen('Einheit', request()->input('einheit_id'));
            if ($kontierung_id_arr == false) {
                echo "Keine einheitsbezogene Kosten";
            }
            /* Feldernamen definieren - Überschrift Tabelle */
            if (is_array($kontierung_id_arr)) {
                foreach ($kontierung_id_arr [0] as $key => $value) {
                    $ueberschrift_felder_arr [] = $key;
                }
            }
            /* Rausfinden von wem die Rechnungen ausm Pool geschrieben werden */
            for ($a = 0; $a < count($kontierung_id_arr); $a++) {
                $beleg_nr = $kontierung_id_arr [$a] ['BELEG_NR'];
                $rechnung->rechnung_grunddaten_holen($beleg_nr);
                /* Empfänger der Rechnung wird zum Austeller der Auto...Rechnung */

                if ($rechnung->rechnungs_empfaenger_typ != 'Kasse') {
                    // echo "PARTNER $a<br>";
                    $aussteller_arr [] = $rechnung->rechnungs_empfaenger_id;
                } else {
                    // echo "KASSE $a<br>";
                    $kassen_arr [] = $rechnung->rechnungs_empfaenger_id;
                }
            }
            if (is_array($aussteller_arr)) {
                $aussteller_arr = array_unique($aussteller_arr);
                foreach ($aussteller_arr as $key => $value) {
                    $aussteller_arr_sortiert [] = $value;
                }
            }

            if (isset ($kassen_arr) && is_array($kassen_arr)) {
                $kassen_arr = array_unique($kassen_arr);
                // $kassen_arr_sortiert = Array();
                foreach ($kassen_arr as $key => $value) {
                    $kassen_arr_sortiert [] = $value;
                }
            }

            /* Ausgabe der Links mit Rechnungsausteller namen */
            echo "<table>";
            echo "<tr><td>Wählen Sie bitte den Rechnungsaussteller aus!</td></tr>";
            if (is_array($aussteller_arr_sortiert)) {
                // print_r($aussteller_arr_sortiert);
                for ($a = 0; $a < count($aussteller_arr_sortiert); $a++) {
                    $partner_info = new partner ();
                    $partner_info->get_aussteller_info($aussteller_arr_sortiert [$a]);
                    $aussteller_id = $aussteller_arr_sortiert [$a];
                    $rechnung_von_link = "<a href='" . route('web::rechnungen::legacy',
                            ['option' => 'rechnung_an_einheit',
                                'einheit_id' => request()->input('einheit_id'),
                                'aussteller_typ' => $rechnung->rechnungs_empfaenger_typ,
                                'aussteller_id' => $aussteller_id]) . "'>$rechnung->rechnungs_empfaenger_name</a>";

                    echo "<tr><td>$rechnung_von_link</td></tr>";
                }
            }
            if (isset ($kassen_arr_sortiert) && is_array($kassen_arr_sortiert)) {
                for ($a = 0; $a < count($kassen_arr_sortiert); $a++) {
                    $kassen_info = new kasse ();
                    $kassen_info->get_kassen_info($kassen_arr_sortiert [$a]);
                    $aussteller_id = $kassen_arr_sortiert [$a];
                    $rechnung_von_link = "<a href'" . route('web::rechnungen::legacy',
                            ['option' => 'rechnung_an_einheit',
                                'einheit_id' => request()->input('einheit_id'),
                                'aussteller_typ' => 'Kasse',
                                'aussteller_id' => $aussteller_id]) . "'>" . $kassen_info->kassen_name . " - " . $kassen_info->kassen_verwalter . "</a>";
                    echo "<tr><td>$rechnung_von_link</td></tr>";
                }
            }
            echo "</table>";
            /* Ende der Ausgabe der Links mit Rechnungsausteller namen */
        }
        if (request()->has('einheit_id') && request()->has('aussteller_id') && request()->has('aussteller_typ')) {
            $kontierung_id_arr = $rechnung->rechnung_aus_pool_zusammenstellen('Einheit', request()->input('einheit_id'));
            $kontierung_id_arr_gefiltert = $rechnung->filtern_nach_austeller($kontierung_id_arr, request()->input('aussteller_typ'), request()->input('aussteller_id'));
            $rechnung->rechnung_schreiben_positionen_wahl('Einheit', request()->input('einheit_id'), $kontierung_id_arr_gefiltert, request()->input('aussteller_typ'), request()->input('aussteller_id'));
        }
        $form->ende_formular();
        break;

    case "rechnung_an_haus" :
        $form = new mietkonto ();
        $form->erstelle_formular("Rechnung an Haus aus Pool", NULL);
        $rechnung = new rechnung ();

        if (request()->has('haus_id')) {
            $kontierung_id_arr = $rechnung->rechnung_aus_pool_zusammenstellen('Haus', request()->input('haus_id'));
            if ($kontierung_id_arr == false) {
                echo "Keine hausbezogenen Kosten";
            }

            /* Feldernamen definieren - Überschrift Tabelle */
            if (is_array($kontierung_id_arr)) {
                foreach ($kontierung_id_arr [0] as $key => $value) {
                    $ueberschrift_felder_arr [] = $key;
                }
            }

            /* Rausfinden von wem die Rechnungen ausm Pool geschrieben werden */
            for ($a = 0; $a < count($kontierung_id_arr); $a++) {
                $beleg_nr = $kontierung_id_arr [$a] ['BELEG_NR'];
                $rechnung->rechnung_grunddaten_holen($beleg_nr);
                /* Empfänger der Rechnung wird zum Austeller der Auto...Rechnung */

                if ($rechnung->rechnungs_empfaenger_typ != 'Kasse') {

                    $aussteller_arr [] = $rechnung->rechnungs_empfaenger_id;
                } else {
                    $kassen_arr [] = $rechnung->rechnungs_empfaenger_id;
                }
            }
            if (is_array($aussteller_arr)) {
                $aussteller_arr = array_unique($aussteller_arr);
                foreach ($aussteller_arr as $key => $value) {
                    $aussteller_arr_sortiert [] = $value;
                }
            }

            if (is_array($kassen_arr)) {
                $kassen_arr = array_unique($kassen_arr);
                foreach ($kassen_arr as $key => $value) {
                    $kassen_arr_sortiert [] = $value;
                }
            }
        }

        /* Ausgabe der Links mit Rechnungsausteller namen */
        echo "<table>";
        echo "<tr><td>Wählen Sie bitte den Rechnungsaussteller aus!</td></tr>";
        if (is_array($aussteller_arr_sortiert)) {
            // print_r($aussteller_arr_sortiert);
            for ($a = 0; $a < count($aussteller_arr_sortiert); $a++) {
                $partner_info = new partner ();
                $partner_info->get_aussteller_info($aussteller_arr_sortiert [$a]);
                $aussteller_id = $aussteller_arr_sortiert [$a];
                $rechnung_von_link = "<a href='" . route('web::rechnungen::legacy',
                        ['option' => 'rechnung_an_haus',
                            'haus_id' => request()->input('haus_id'),
                            'aussteller_typ' => $rechnung->rechnungs_empfaenger_typ,
                            'aussteller_id' => $aussteller_id]) . "'>$rechnung->rechnungs_empfaenger_name</a>";

                echo "<tr><td>$rechnung_von_link</td></tr>";
            }
        }
        if (is_array($kassen_arr_sortiert)) {
            for ($a = 0; $a < count($kassen_arr_sortiert); $a++) {
                $kassen_info = new kasse ();
                $kassen_info->get_kassen_info($kassen_arr_sortiert [$a]);
                $aussteller_id = $kassen_arr_sortiert [$a];
                $rechnung_von_link = "<a href='" . route('web::rechnungen::legacy',
                        ['option' => 'rechnung_an_haus',
                            'haus_id' => request()->input('haus_id'),
                            'aussteller_typ' => 'Kasse',
                            'aussteller_id' => $aussteller_id]) . "'>" . $kassen_info->kassen_name . " - " . $kassen_info->kassen_verwalter . "</a>";
                echo "<tr><td>$rechnung_von_link</td></tr>";
            }
        }
        echo "</table>";
        /* Ende der Ausgabe der Links mit Rechnungsausteller namen */

        if (request()->has('haus_id') && request()->has('aussteller_id') && request()->has('aussteller_typ')) {
            $kontierung_id_arr = $rechnung->rechnung_aus_pool_zusammenstellen('Haus', request()->input('haus_id'));
            $kontierung_id_arr_gefiltert = $rechnung->filtern_nach_austeller($kontierung_id_arr, request()->input('aussteller_typ'), request()->input('aussteller_id'));
            $rechnung->rechnung_schreiben_positionen_wahl('Haus', request()->input('haus_id'), $kontierung_id_arr_gefiltert, request()->input('aussteller_typ'), request()->input('aussteller_id'));
        }

        $form->ende_formular();
        break;

    case "hauskosten_in_rechnung" :
        $form = new mietkonto ();
        $form->erstelle_formular("Hauskosten in Rechnung stellen", NULL);
        $rechnung = new rechnung ();
        if (request()->has('haus_id')) {
            $kontierung_id_arr = $rechnung->rechnung_aus_pool_zusammenstellen('Haus', request()->input('haus_id'));
            if ($kontierung_id_arr == false) {
                echo "Keine Hauskosten";
            }
            /* Feldernamen definieren - Überschrift Tabelle */
            if (is_array($kontierung_id_arr)) {
                foreach ($kontierung_id_arr [0] as $key => $value) {
                    $ueberschrift_felder_arr [] = $key;
                }
                $rechnung->rechnung_schreiben_positionen_wahl('Haus', request()->input('haus_id'), $kontierung_id_arr);
            }
        }
        $form->ende_formular();
        break;

    case "rechnung_an_einheit_ALT" :
        $form = new mietkonto ();
        $form->erstelle_formular("Rechnung an Einheit aus Pool", NULL);
        $rechnung = new rechnung ();
        if (request()->has('einheit_id')) {
            $kontierung_id_arr = $rechnung->rechnung_aus_pool_zusammenstellen('Einheit', request()->input('einheit_id'));

            /* Feldernamen definieren - Überschrift Tabelle */
            if (is_array($kontierung_id_arr)) {
                foreach ($kontierung_id_arr [0] as $key => $value) {
                    $ueberschrift_felder_arr [] = $key;
                }
                $rechnung->rechnung_schreiben_positionen_wahl('Einheit', request()->input('einheit_id'), $kontierung_id_arr);
            }
        }
        $form->ende_formular();
        break;

    case "rechnung_an_lager" :
        $form = new mietkonto ();
        $form->erstelle_formular("Rechnung an Lager aus Pool", NULL);
        $rechnung = new rechnung ();
        if (request()->has('lager_id')) {
            $kontierung_id_arr = $rechnung->rechnung_aus_pool_zusammenstellen('Lager', request()->input('lager_id'));

            /* Feldernamen definieren - Überschrift Tabelle */
            if (is_array($kontierung_id_arr)) {
                foreach ($kontierung_id_arr [0] as $key => $value) {
                    $ueberschrift_felder_arr [] = $key;
                }
            }

            /* Rausfinden von wem die Rechnungen ausm Pool geschrieben werden */
            for ($a = 0; $a < count($kontierung_id_arr); $a++) {
                $beleg_nr = $kontierung_id_arr [$a] ['BELEG_NR'];
                $rechnung->rechnung_grunddaten_holen($beleg_nr);
                /* Empfänger der Rechnung wird zum Austeller der Auto...Rechnung */

                if ($rechnung->rechnungs_empfaenger_typ != 'Kasse') {
                    $aussteller_arr [] = $rechnung->rechnungs_empfaenger_id;
                } else {
                    $kassen_arr [] = $rechnung->rechnungs_empfaenger_id;
                }
            }
            if (is_array($aussteller_arr)) {
                $aussteller_arr = array_unique($aussteller_arr);
                foreach ($aussteller_arr as $key => $value) {
                    $aussteller_arr_sortiert [] = $value;
                }
            }

            if (is_array($kassen_arr)) {
                $kassen_arr = array_unique($kassen_arr);
                foreach ($kassen_arr as $key => $value) {
                    $kassen_arr_sortiert [] = $value;
                }
            }

            /* Ausgabe der Links mit Rechnungsausteller namen */
            echo "<table>";
            echo "<tr><td>Wählen Sie bitte den Rechnungsaussteller aus!</td></tr>";
            // print_r($aussteller_arr);
            if (is_array($aussteller_arr_sortiert)) {
                print_r($aussteller_arr_sortiert);
                for ($a = 0; $a < count($aussteller_arr_sortiert); $a++) {
                    $partner_info = new partner ();
                    $partner_info->get_aussteller_info($aussteller_arr_sortiert [$a]);
                    $aussteller_id = $aussteller_arr_sortiert [$a];

                    $rechnung_von_link = "<a href='" . route('web::rechnungen::legacy',
                            ['option' => 'rechnung_an_lager',
                                'lager_id' => request()->input('lager_id'),
                                'aussteller_typ' => $rechnung->rechnungs_empfaenger_typ,
                                'aussteller_id' => $aussteller_id]) . "'>" . $rechnung->rechnungs_empfaenger_name . "</a>";
                    echo "<tr><td>$rechnung_von_link</td></tr>";
                }
            }
            if (is_array($kassen_arr_sortiert)) {
                for ($a = 0; $a < count($kassen_arr_sortiert); $a++) {
                    $kassen_info = new kasse ();
                    $kassen_info->get_kassen_info($kassen_arr_sortiert [$a]);
                    $aussteller_id = $kassen_arr_sortiert [$a];
                    $rechnung_von_link = "<a href='" . route('web::rechnungen::legacy',
                            ['option' => 'rechnung_an_lager',
                                'lager_id' => request()->input('lager_id'),
                                'aussteller_typ' => 'Kasse',
                                'aussteller_id' => $aussteller_id]) . "'>" . $kassen_info->kassen_name . " - " . $kassen_info->kassen_verwalter . "</a>";
                    echo "<tr><td>$rechnung_von_link</td></tr>";
                }
            }
            echo "</table>";
            /* Ende der Ausgabe der Links mit Rechnungsausteller namen */
        } // end if

        if (request()->has('lager_id') && request()->has('aussteller_id') && request()->has('aussteller_typ')) {
            $kontierung_id_arr = $rechnung->rechnung_aus_pool_zusammenstellen('Lager', request()->input('lager_id'));
            $kontierung_id_arr_gefiltert = $rechnung->filtern_nach_austeller($kontierung_id_arr, request()->input('aussteller_typ'), request()->input('aussteller_id'));
            $rechnung->rechnung_schreiben_positionen_wahl('Lager', request()->input('lager_id'), $kontierung_id_arr_gefiltert, request()->input('aussteller_typ'), request()->input('aussteller_id'));
        }

        $form->ende_formular();
        break;

    case "rechnung_an_partner" :
        $form = new mietkonto ();
        $form->erstelle_formular("Rechnung an Partner aus Pool", NULL);
        $rechnung = new rechnung ();
        if (request()->has('partner_id')) {
            $kontierung_id_arr = $rechnung->rechnung_aus_pool_zusammenstellen('Partner', request()->input('partner_id'));

            /* Feldernamen definieren - Überschrift Tabelle */
            if (is_array($kontierung_id_arr)) {
                foreach ($kontierung_id_arr [0] as $key => $value) {
                    $ueberschrift_felder_arr [] = $key;
                }
            }

            /* Rausfinden von wem die Rechnungen ausm Pool geschrieben werden */
            for ($a = 0; $a < count($kontierung_id_arr); $a++) {
                $beleg_nr = $kontierung_id_arr [$a] ['BELEG_NR'];
                $rechnung->rechnung_grunddaten_holen($beleg_nr);
                /* Empfänger der Rechnung wird zum Austeller der Auto...Rechnung */

                if ($rechnung->rechnungs_empfaenger_typ != 'Kasse') {
                    $aussteller_arr [] = $rechnung->rechnungs_empfaenger_id;
                } else {
                    $kassen_arr [] = $rechnung->rechnungs_empfaenger_id;
                }
            }
            if (is_array($aussteller_arr)) {
                $aussteller_arr = array_unique($aussteller_arr);
                foreach ($aussteller_arr as $key => $value) {
                    $aussteller_arr_sortiert [] = $value;
                }
            }

            if (is_array($kassen_arr)) {
                $kassen_arr = array_unique($kassen_arr);
                foreach ($kassen_arr as $key => $value) {
                    $kassen_arr_sortiert [] = $value;
                }
            }

            /* Ausgabe der Links mit Rechnungsausteller namen */
            echo "<table>";
            echo "<tr><td>Wählen Sie bitte den Rechnungsaussteller aus!</td></tr>";
            if (is_array($aussteller_arr_sortiert)) {
                for ($a = 0; $a < count($aussteller_arr_sortiert); $a++) {
                    $partner_info = new partner ();
                    $partner_info->get_aussteller_info($aussteller_arr_sortiert [$a]);
                    $aussteller_id = $aussteller_arr_sortiert [$a];

                    $rechnung_von_link = "<a href='" . route('web::rechnungen::legacy',
                            ['option' => 'rechnung_an_partner',
                                'partner_id' => request()->input('partner_id'),
                                'aussteller_typ' => $rechnung->rechnungs_empfaenger_typ,
                                'aussteller_id' => $aussteller_id]) . "'>" . $rechnung->rechnungs_empfaenger_name . "</a>";
                    echo "<tr><td>$rechnung_von_link</td></tr>";
                }
            }
            if (is_array($kassen_arr_sortiert)) {
                for ($a = 0; $a < count($kassen_arr_sortiert); $a++) {
                    $kassen_info = new kasse ();
                    $kassen_info->get_kassen_info($kassen_arr_sortiert [$a]);
                    $aussteller_id = $kassen_arr_sortiert [$a];
                    $rechnung_von_link = "<a href='" . route('web::rechnungen::legacy',
                            ['option' => 'rechnung_an_partner',
                                'partner_id' => request()->input('partner_id'),
                                'aussteller_typ' => 'Kasse',
                                'aussteller_id' => $aussteller_id]) . "'>" . $kassen_info->kassen_name . " - " . $kassen_info->kassen_verwalter . "</a>";
                    echo "<tr><td>$rechnung_von_link</td></tr>";
                }
            }
            echo "</table>";
            /* Ende der Ausgabe der Links mit Rechnungsausteller namen */
        } // end if

        if (request()->has('partner_id') && request()->has('aussteller_id') && request()->has('aussteller_typ')) {
            $kontierung_id_arr = $rechnung->rechnung_aus_pool_zusammenstellen('Partner', request()->input('partner_id'));
            $kontierung_id_arr_gefiltert = $rechnung->filtern_nach_austeller($kontierung_id_arr, request()->input('aussteller_typ'), request()->input('aussteller_id'));
            $rechnung->rechnung_schreiben_positionen_wahl('Partner', request()->input('partner_id'), $kontierung_id_arr_gefiltert, request()->input('aussteller_typ'), request()->input('aussteller_id'));
        }

        $form->ende_formular();
        break;

    case "rechnungs_uebersicht" :
        $form = new mietkonto ();
        $form->erstelle_formular("Rechnungsübersicht", NULL);
        $rechnung = new rechnung ();

        if (request()->has('belegnr')) {
            $rechnung->rechnung_grunddaten_holen(request()->input('belegnr'));
            $rechnung->rechnung_inkl_positionen_anzeigen(request()->input('belegnr'));
        }
        $form->ende_formular();
        break;

    case "send_positionen" :
        $clean_arr = post_array_bereinigen();
        $rechnung = new rechnung ();

        $form = new mietkonto ();
        $form->erstelle_formular("Rechnung vervollständigen", NULL);
        if (request()->has('belegnr')) {
            $rechnung->rechnungs_kopf(request()->input('belegnr'));

            /* Block mit Artikeln und Leistungen des Rechnungsaustellers */
            $rechnung->artikel_leistungen_block($rechnung->rechnungs_aussteller_id);

            $form->erstelle_formular("Positionen eingeben", NULL);
            echo "<table>";
            for ($a = 1; $a <= $clean_arr ['anzahl_positionen']; $a++) {
                echo "<tr>";
                echo "<td>";
                $form->text_feld_inaktiv("Position $a", "positionen[$a][position]", "$a", "1");
                echo "</td><td>";
                $form->text_feld("Artikel/Leistung", "positionen[$a][artikel_nr]", "", "15");
                echo "</td><td>";
                $form->text_feld("Bezeichnung", "positionen[$a][bezeichnung]", "", "50");
                echo "</td><td>";
                $form->text_feld("Listenpreis", "positionen[$a][preis]", "", "10");
                echo "</td><td>";
                $form->text_feld("Rabatt %", "positionen[$a][rabatt_satz]", "", "10");

                // echo "<label name=\"'inaktiv.'positionen[$a][artikel_nr]\">ss</label>
                echo "</td><td>";

                $form->text_feld("Menge:", "positionen[$a][menge]", "", "3");
                echo "</td></tr>";
            }
            echo "<tr><td colspan=3>";
            $form->hidden_feld("option", "send_positionen2");
            $form->hidden_feld("belegnr", "" . $rechnung->belegnr . "");
            $form->hidden_feld("rechnungsnummer", "" . $rechnung->rechnungsnummer . "");
            $form->hidden_feld("partner_id", "" . $rechnung->rechnungs_aussteller_id . "");
            $form->send_button("senden_art_pos", "Weiter");
            echo "<td></tr>";
            echo "</table>";

            /* Anzeigen von Netto/Brutto werten der aktuellen Rechnung */
            $rechnung->rechnung_footer_tabelle_anzeigen();
            $form->ende_formular();
        } else {
            fehlermeldung_ausgeben("Bitte Rechnung auswählen!");
            weiterleiten_in_sec(route('web::rechnungen::legacy', ['option' => 'erfasste_rechnungen'], false), 2);
        }
        break;

    case "send_positionen2" :
        $clean_arr = post_array_bereinigen();
        $rechnung = new rechnung ();
        $form = new mietkonto ();
        $form->erstelle_formular("Rechnung vervollständigen", NULL);
        if (request()->has('belegnr')) {
            $rechnung->rechnungs_kopf(request()->input('belegnr'));

            /* Prüfen ob Bezeichnung, Preis, Menge eingetragen worden sind */
            for ($b = 1; $b <= count(request()->input('positionen')); $b++) {
                foreach (request()->input('positionen') [$b] as $key1 => $value1) {
                    if ($key1 == 'menge' && empty ($value1)) {
                        throw new \App\Exceptions\MessageException(
                            new \App\Messages\ErrorMessage("<b>Position $b. Die Mengenangabe fehlt</b>\n")
                        );
                    } else {
                        $fehler = false;
                    }
                }
            }

            /* Block mit Artikeln und Leistungen des Rechnungsaustellers */
            $rechnung->artikel_leistungen_block($rechnung->rechnungs_aussteller_id);
            if (!$fehler) {
                $form->erstelle_formular("Zusammenfassung", NULL);
                echo "<table>";
                echo "<tr><td colspan=8>";
                $geld_konto_info = new geldkonto_info ();

                if ($rechnung->rechnungs_empfaenger_typ != 'Kasse') {
                    echo "<b>Diese Rechnung wird/wurde überwiesen an $rechnung->rechnungs_aussteller_name .</b>";
                    $geld_konto_info->dropdown_geldkonten($rechnung->rechnungs_aussteller_typ, $rechnung->rechnungs_aussteller_id);
                } else {
                    echo "<b>Diese Rechnung wird/wurde in BAR an $rechnung->rechnungs_aussteller_name gezaht.</b>";
                }
                echo "</td></tr>";
                echo "<tr class=felder_namen>";
                echo "<td>Pos</td><td>Artikel</td><td>Bezeichnung</td><td>EPreis</td><td>Menge</td><td><input type=\"button\" onclick=\"wert_uebertragen(this.form.mwst_feld)\" value=\"Alle\">
</td><td><input type=\"button\" onclick=\"wert_uebertragen(this.form.rabatt_feld)\" value=\"Alle\"></td><td></td></tr>";

                for ($a = 1; $a <= count(request()->input('positionen')); $a++) {
                    echo "<tr>";
                    echo "<td>";
                    $form->text_feld("Pos.", "positionen[$a]", "$a", "1");
                    echo "</td><td>";
                    /* Artikelinfos als Array verfügbar machen */
                    $artikel_info_arr = $rechnung->artikel_info(request()->input('partner_id'), request()->input('positionen')[$a]['artikel_nr']);

                    /* Prüfen ob Artikelinfos als Array verfügbar sind */
                    if (is_array($artikel_info_arr)) {
                        $bezeichnung = $artikel_info_arr [0] ['BEZEICHNUNG'];
                        $listenpreis = $artikel_info_arr [0] ['LISTENPREIS'];
                        $rabatt_satz = $artikel_info_arr [0] ['RABATT_SATZ'];
                        $gpreis = ((request()->input('positionen')[$a]['menge'] * $listenpreis) / 100) * (100 - $rabatt_satz);
                        $artikel_nr = $artikel_info_arr [0] ['ARTIKEL_NR'];
                    } else {

                        /* Artikel nicht in db vorhanden z.B. neues Artikel / Leistung */
                        if (request()->has("positionen.$a.bezeichnung")) {
                            if (request()->has("positionen.$a.artikel_nr")) {
                                $listenpreis_neuer_artikel = nummer_komma2punkt(request()->input('positionen')[$a]['preis']);
                                $art_nr = $rechnung->artikel_leistung_mit_artikelnr_speichern(request()->input('partner_id'), request()->input('positionen') [$a] ['bezeichnung'], $listenpreis_neuer_artikel, request()->input('positionen') [$a] ['artikel_nr'], request()->input('positionen') [$a] ['rabatt_satz']);
                            } else {
                                $listenpreis_neuer_artikel = nummer_komma2punkt(request()->input('positionen') [$a] ['preis']);
                                $art_nr = $rechnung->artikel_leistung_speichern(request()->input('partner_id'), request()->input('positionen') [$a] ['bezeichnung'], $listenpreis_neuer_artikel, request()->input('positionen')[$a] ['rabatt_satz']);
                            }
                        }
                        /* Artikelinfos als Array verfügbar machen */
                        $artikel_info_arr = $rechnung->artikel_info(request()->input('partner_id'), $art_nr);
                        $bezeichnung = $artikel_info_arr [0] ['BEZEICHNUNG'];
                        $listenpreis = $artikel_info_arr [0] ['LISTENPREIS'];
                        $rabatt_satz = $artikel_info_arr [0] ['RABATT_SATZ'];
                        $artikel_nr = $artikel_info_arr [0] ['ARTIKEL_NR'];
                        $gpreis = (request()->input('positionen') [$a] ['menge'] * $listenpreis) / (100 - $rabatt_satz);
                    }

                    $form->text_feld("Artikel/Leistung", "positionen[$a][artikel_nr]", "$artikel_nr", "15");
                    echo "</td><td>";
                    $form->text_feld("Bezeichnung:", "positionen[$a][bezeichnung]", "$bezeichnung", "40");

                    echo "</td><td>";
                    $listenpreis = nummer_punkt2komma($listenpreis);
                    $form->text_feld_id("Epreis:", "positionen[$a][preis]", "$listenpreis", "5", "epreis_feld");
                    echo "</td><td>";

                    $form->text_feld_id("Menge:", "positionen[$a][menge]", request()->input('positionen') [$a] ['menge'] . "", "2", "mengen_feld");

                    echo "</td><td>";
                    $form->text_feld_id("MWST %:", "positionen[$a][pos_mwst_satz]", "19", "5", "mwst_feld");
                    echo "</td><td>";
                    $form->text_feld_id("Rabatt %:", "positionen[$a][pos_rabatt]", "$rabatt_satz", "5", "rabatt_feld");
                    echo "</td><td>";
                    $gpreis = nummer_punkt2komma($gpreis);
                    $form->text_feld_id("Netto:", "positionen[$a][gpreis]", "$gpreis", "8", "netto_feld");
                    echo "</td></tr>";
                } // ende for
                echo "<tr><td colspan=7 align=right>";

                echo "</td></tr>";
                echo "<tr><td colspan=8><hr></td></tr>";
                echo "<tr><td colspan=7 align=right>Netto errechnet</td><td id=\"g_netto_errechnet\"></td></tr>";
                echo "<tr><td colspan=7 align=right>Durchschnittsrabatt</td><td id=\"durchschnitt_rabatt\"></td></tr>";

                echo "<tr><td colspan=3>";
                $form->hidden_feld("option", "send_positionen3");
                $form->send_button_disabled("senden_pos", "Speichern deaktiviert", "speichern_button1");
                $form->hidden_feld("belegnr", "" . $rechnung->belegnr . "");
                $form->hidden_feld("rechnungsnummer", "" . $rechnung->rechnungsnummer . "");
            } // end if !fehler
            echo "<td></tr>";
            echo "</table>";

            /* Anzeigen von Netto/Brutto werten der aktuellen Rechnung */
            $rechnung->rechnung_footer_tabelle_anzeigen();

            $form->ende_formular();
        } else {
            fehlermeldung_ausgeben("Bitte Rechnung auswählen!");
            weiterleiten_in_sec(route('web::rechnungen::legacy', ['option' => 'erfasste_rechnungen'], false), 2);
        }
        /* Block mit Artikeln und Leistungen des Rechnungsaustellers */
        $rechnung->artikel_leistungen_block($rechnung->rechnungs_aussteller_id);
        break;

    case "send_positionen3" :
        $form = new mietkonto ();
        $form->erstelle_formular("Rechnungspositionen speichern", NULL);
        $clean_arr = post_array_bereinigen();
        $rechnung = new rechnung ();
        $rechnung->positionen_speichern($clean_arr ['belegnr']);
        echo "<pre>";
        echo "</pre>";
        $form->ende_formular();
        break;

    case "rechnung_suchen" :
        $clean_arr = post_array_bereinigen();
        $rechnung = new rechnung ();
        $rechnung->suche_rechnung_form();
        break;

    case "rechnung_suchen1" :
        $rechnung = new rechnung ();
        $rechnung->suche_rechnung_form();
        $clean_arr = post_array_bereinigen();
        $form = new mietkonto ();
        $form->erstelle_formular("Ergebnis", NULL);
        $suchart = $clean_arr ['suchart'];
        if ($suchart == 'beleg_nr') {
            $ergebnis = $rechnung->rechnung_finden_nach_beleg($clean_arr ['beleg_nr_txt']);
            if (count($ergebnis) > 0) {
                $rechnung->rechnungen_aus_arr_anzeigen($ergebnis);
            } else {
                echo "Keine Rechnung mit dieser Belegnummer ($clean_arr[beleg_nr_txt])";
            }
        }
        if ($suchart == 'lieferschein') {
            $ergebnis = $rechnung->rechnung_finden_nach_lieferschein($clean_arr ['lieferschein_nr_txt']);
            if (is_array($ergebnis)) {
                $anzahl_rechnungen = count($ergebnis);
                for ($a = 0; $a < $anzahl_rechnungen; $a++) {
                    $beleg_nr = $ergebnis [$a] ['DETAIL_ZUORDNUNG_ID'];
                    $r = new rechnungen ();
                    $r->rechnung_grunddaten_holen($beleg_nr);
                    $link_rechnung = "<a href='" . route('web::rechnungen::legacy', ['option' => 'rechnungs_uebersicht', 'belegnr' => $beleg_nr]) . "'>$r->rechnungsdatum $r->rechnungs_aussteller_name Rechnungsnr: $r->rechnungsnummer WE: $r->empfaenger_eingangs_rnr WA: $r->aussteller_ausgangs_rnr</a>";
                    echo "$link_rechnung<br>";
                }
            } else {
                echo "Keine Rechnung mit dieser Lieferscheinnummer ($clean_arr[lieferschein_nr_txt])";
            }
        }

        if ($suchart == 'rechnungsnr') {
            $ergebnis = $rechnung->rechnung_finden_nach_rnr($clean_arr ['rechnungsnr_txt']);
            if (count($ergebnis) > 0) {
                $rechnung->rechnungen_aus_arr_anzeigen($ergebnis);
            } else {
                echo "Keine Rechnung mit der Rechnungsnummer $clean_arr[rechnungsnr_txt] gefunden!";
            }
        }
        if ($suchart == 'aussteller') {
            $ergebnis = $rechnung->rechnung_finden_nach_aussteller($clean_arr ['aussteller']);
            if (count($ergebnis) > 0) {
                $rechnung->rechnungen_aus_arr_anzeigen($ergebnis);
            } else {
                echo "Keine Rechnung von Austeller ($clean_arr[aussteller])";
            }
        }
        if ($suchart == 'empfaenger') {
            $ergebnis = $rechnung->rechnung_finden_nach_empfaenger('Partner', $clean_arr ['empfaenger']);
            if (count($ergebnis) > 0) {
                $rechnung->rechnungen_aus_arr_anzeigen($ergebnis);
            } else {
                echo "Keine Rechnungen für den Empfänger ($clean_arr[empfaenger])";
            }
        }
        if ($suchart == 'partner_paar') {
            $ergebnis = $rechnung->rechnung_finden_nach_paar($clean_arr ['partner_paar1'], $clean_arr ['partner_paar2']);
            if (count($ergebnis) > 0) {
                $rechnung->rechnungen_aus_arr_anzeigen($ergebnis);
            } else {
                echo "Keine Rechnungen für das Partnerpaar";
            }
        }
        $form->ende_formular();
        break;

    case "rechnung_kontieren" :
        $form = new mietkonto ();
        $form->erstelle_formular("Rechnungsübersicht", NULL);
        if (!request()->has('positionen_list')) {
            if (request()->has('belegnr')) {
                $rechnung = new rechnung ();
                $rechnung->rechnung_zum_kontieren_anzeigen(request()->input('belegnr'));
            }
        } else {
            $rechnung = new rechnung ();
            $rechnung->kontierungstabelle_anzeigen(request()->input('belegnr'), request()->input('positionen_list'), request()->input('kosten_traeger_typ'));
        }
        $form->ende_formular();
        break;

    case "rechnung_kontierung_aufheben" :
        if (request()->has('belegnr')) {
            $r = new rechnung ();
            if ($r->rechnung_kontierung_aufheben(request()->input('belegnr'))) {
                $belegnr = request()->input('belegnr');
                weiterleiten(route('web::rechnungen::legacy', ['option' => 'rechnung_kontieren', 'belegnr' => $belegnr], false));
            }
        } else {
            fehlermeldung_ausgeben("Rechnunt wählen x777");
        }
        break;

    case "KONTIERUNG_SENDEN" :
        $rechnung = new rechnung ();
        $error = $rechnung->kontierung_pruefen();
        if ($error) {
            fehlermeldung_ausgeben("KONTIERUNGSSUMME > URSPRUNGSSUMME");
        } else {
            $rechnung->kontierung_speichern();
        }
        break;

    case "AUTO_RECHNUNG_VORSCHAU_ALT" :
        $form = new mietkonto ();
        $form->erstelle_formular("Rechnungsvorschau", NULL);
        if (request()->isMethod('post')) {
            for ($a = 0; $a < count(request()->input('uebernehmen')); $a++) {

                $zeile_uebernehmen = request()->input('uebernehmen') [$a];
                $menge = request()->input('positionen') [$zeile_uebernehmen] ['menge'];
                $preis = request()->input('positionen') [$zeile_uebernehmen] ['preis'];
                $uebernahme_arr ['positionen'] [] = request()->input('positionen') [$zeile_uebernehmen];
            }
            $uebernahme_arr ['RECHNUNG_KOSTENTRAEGER_TYP'] = request()->input('RECHNUNG_KOSTENTRAEGER_TYP');
            $uebernahme_arr ['RECHNUNG_KOSTENTRAEGER_ID'] = request()->input('RECHNUNG_KOSTENTRAEGER_ID');
            $uebernahme_arr ['RECHNUNG_AUSSTELLER_TYP'] = request()->input('RECHNUNG_AUSSTELLER_TYP');
            $uebernahme_arr ['RECHNUNG_AUSSTELLER_ID'] = request()->input('RECHNUNG_AUSSTELLER_ID');
            $uebernahme_arr ['RECHNUNG_EMPFAENGER_TYP'] = request()->input('RECHNUNG_EMPFAENGER_TYP');
            $uebernahme_arr ['RECHNUNG_EMPFAENGER_ID'] = request()->input('RECHNUNG_EMPFAENGER_ID');
            $uebernahme_arr ['RECHNUNG_SKONTO'] = request()->input('skonto');
            $uebernahme_arr ['RECHNUNG_FAELLIG_AM'] = request()->input('faellig_am');
            $uebernahme_arr ['RECHNUNG_NETTO_BETRAG'] = request()->input('RECHNUNG_NETTO_BETRAG');
            $uebernahme_arr ['RECHNUNG_BRUTTO_BETRAG'] = request()->input('RECHNUNG_BRUTTO_BETRAG');
            $uebernahme_arr ['RECHNUNG_SKONTO_BETRAG'] = request()->input('RECHNUNG_SKONTO_BETRAG');
            $uebernahme_arr ['EMPFANGS_GELD_KONTO'] = request()->input('geld_konto');
            $uebernahme_arr ['RECHNUNGSDATUM'] = request()->input('rechnungsdatum');

            $objekt_info = new objekt ();
            $objekt_info->get_objekt_name(request()->input('RECHNUNG_KOSTENTRAEGER_ID'));
            $objekt_info->get_objekt_eigentuemer_partner(request()->input('RECHNUNG_KOSTENTRAEGER_ID'));
            $partner_info = new partner ();
            $rechnung_an = $partner_info->get_partner_name($objekt_info->objekt_eigentuemer_partner_id);
            if ($uebernahme_arr ['RECHNUNG_AUSSTELLER_TYP'] == 'Partner') {
                $rechnung_von = $partner_info->get_partner_name($uebernahme_arr ['RECHNUNG_AUSSTELLER_ID']);
            }
            if ($uebernahme_arr ['RECHNUNG_AUSSTELLER_TYP'] == 'Kasse') {
                $kassen_info = new kasse ();
                $kassen_info->get_kassen_info($uebernahme_arr ['RECHNUNG_AUSSTELLER_ID']);
                $rechnung_von = $kassen_info->kassen_name;
            }
            echo "<table><tr><td>Rechnung von <b>$rechnung_von</b> an $rechnung_an<br> für das Objekt " . $objekt_info->objekt_name . "</td></tr></table>";

            $clean_arr ['rechnungsdatum'] = $uebernahme_arr ['RECHNUNGSDATUM'];
            $clean_arr ['rechnungsnummer'] = '';
            $clean_arr ['Aussteller_typ'] = $uebernahme_arr ['RECHNUNG_AUSSTELLER_TYP'];
            $clean_arr ['Aussteller_id'] = $uebernahme_arr ['RECHNUNG_AUSSTELLER_ID'];
            $clean_arr ['Empfaenger_typ'] = $uebernahme_arr ['RECHNUNG_EMPFAENGER_TYP'];
            $clean_arr ['Empfaenger_id'] = $uebernahme_arr ['RECHNUNG_EMPFAENGER_ID'];
            $clean_arr ['eingangsdatum'] = $rechnungsdatum;
            $faellig_am = $uebernahme_arr ['RECHNUNG_FAELLIG_AM'];
            $clean_arr ['faellig_am'] = date_mysql2german($faellig_am);
            $kurzbeschreibung = request()->input('kurzbeschreibung');

            if ($clean_arr ['Empfaenger_typ'] == 'Objekt') {
                $clean_arr ['kurzbeschreibung'] = "Rechnung für $objekt_info->objekt_name<br>$kurzbeschreibung";
            }
            if ($clean_arr ['Empfaenger_typ'] == 'Haus') {
                $clean_arr ['kurzbeschreibung'] = "Rechnung für Haus im $objekt_info->objekt_name<br>$kurzbeschreibung";
            }
            if ($clean_arr ['Empfaenger_typ'] == 'Einheit') {

                $r = new rechnung ();
                $einheit = $r->kostentraeger_ermitteln('Einheit', $uebernahme_arr ['RECHNUNG_KOSTENTRAEGER_ID']);
                $clean_arr ['kurzbeschreibung'] = "Rechnung für Einheit $einheit<br>$kurzbeschreibung";
            }
            if ($clean_arr ['Empfaenger_typ'] == 'Lager') {
                $lager_info = new lager ();
                $lager_info->lager_name = $lager_info->lager_bezeichnung($clean_arr ['Empfaenger_id']);
                $clean_arr ['kurzbeschreibung'] = "Rechnung an Lager $lager_info->lager_name<br>$kurzbeschreibung";
            }
            if ($clean_arr ['Empfaenger_typ'] == 'Partner') {
                $clean_arr ['kurzbeschreibung'] = "Rechnung an Partner<br>$kurzbeschreibung";
            }

            $netto_betrag = 0;
            for ($b = 0; $b < count($uebernahme_arr ['positionen']); $b++) {
                $netto_betrag = $netto_betrag + ($uebernahme_arr ['positionen'] [$b] ['menge'] * $uebernahme_arr ['positionen'] [$b] ['preis']);
                $netto1 = $uebernahme_arr ['positionen'] [$b] ['menge'] * $uebernahme_arr ['positionen'] [$b] ['preis'];
                $uebernahme_arr ['positionen'] [$b] ['mwst_betrag'] = $mwst_betrag;
            }
            $clean_arr ['nettobetrag'] = $netto_betrag;
            $clean_arr ['skonto'] = $uebernahme_arr ['RECHNUNG_SKONTO'];
            $clean_arr ['faellig_am'] = $uebernahme_arr ['RECHNUNG_FAELLIG_AM'];
            $clean_arr ['netto_betrag'] = $uebernahme_arr ['RECHNUNG_NETTO_BETRAG'];
            $clean_arr ['brutto_betrag'] = $uebernahme_arr ['RECHNUNG_BRUTTO_BETRAG'];
            $clean_arr ['skonto_betrag'] = $uebernahme_arr ['RECHNUNG_SKONTO_BETRAG'];
            $clean_arr ['empfangs_geld_konto'] = $uebernahme_arr ['EMPFANGS_GELD_KONTO'];
            $rechnung = new rechnung ();
            echo "<pre>";
            echo "<hr>";
            $form->ende_formular();
        }
        break;

    case "AUTO_RECHNUNG_VORSCHAU" :
        $f = new formular ();
        $f->fieldset("Rechnung speichern", 'rechnung_speichern');
        $r = new rechnung ();
        if (request()->isMethod('post')) {
            for ($a = 0; $a < count(request()->input('uebernehmen')); $a++) {

                $zeile_uebernehmen = request()->input('uebernehmen') [$a];
                $menge = request()->input('positionen') [$zeile_uebernehmen] ['menge'];
                $preis = request()->input('positionen') [$zeile_uebernehmen] ['preis'];

                $uebernahme_arr ['positionen'] [] = request()->input('positionen') [$zeile_uebernehmen];
            }
            $uebernahme_arr ['RECHNUNG_AUSSTELLER_TYP'] = request()->input('RECHNUNG_AUSSTELLER_TYP');
            $uebernahme_arr ['RECHNUNG_AUSSTELLER_ID'] = request()->input('RECHNUNG_AUSSTELLER_ID');
            $uebernahme_arr ['RECHNUNG_EMPFAENGER_TYP'] = request()->input('RECHNUNG_KOSTENTRAEGER_TYP'); // objekt, Haus, Einheit, Partner, Lager
            $uebernahme_arr ['RECHNUNG_EMPFAENGER_ID'] = request()->input('RECHNUNG_KOSTENTRAEGER_ID');
            $uebernahme_arr ['RECHNUNG_FAELLIG_AM'] = request()->input('faellig_am');
            $uebernahme_arr ['EMPFANGS_GELD_KONTO'] = request()->input('geld_konto');
            $uebernahme_arr ['RECHNUNGSDATUM'] = request()->input('rechnungsdatum');

            $partner_info = new partner ();
            if ($uebernahme_arr ['RECHNUNG_AUSSTELLER_TYP'] == 'Partner') {
                $rechnung_von = $partner_info->get_partner_name($uebernahme_arr ['RECHNUNG_AUSSTELLER_ID']);
            }
            if ($uebernahme_arr ['RECHNUNG_AUSSTELLER_TYP'] == 'Kasse') {
                $kassen_info = new kasse ();
                $kassen_info->get_kassen_info($uebernahme_arr ['RECHNUNG_AUSSTELLER_ID']);
                $rechnung_von = $kassen_info->kassen_name;
            }

            $clean_arr ['RECHNUNGSDATUM'] = $uebernahme_arr ['RECHNUNGSDATUM'];
            $clean_arr ['RECHNUNG_AUSSTELLER_TYP'] = $uebernahme_arr ['RECHNUNG_AUSSTELLER_TYP'];
            $clean_arr ['RECHNUNG_AUSSTELLER_ID'] = $uebernahme_arr ['RECHNUNG_AUSSTELLER_ID'];
            $clean_arr ['RECHNUNG_EMPFAENGER_TYP'] = $uebernahme_arr ['RECHNUNG_EMPFAENGER_TYP'];
            $clean_arr ['RECHNUNG_EMPFAENGER_ID'] = $uebernahme_arr ['RECHNUNG_EMPFAENGER_ID'];
            $clean_arr ['RECHNUNG_FAELLIG_AM'] = $uebernahme_arr ['RECHNUNG_FAELLIG_AM'];
            $clean_arr ['EMPFANGS_GELD_KONTO'] = $uebernahme_arr ['EMPFANGS_GELD_KONTO'];

            $kurzbeschreibung = request()->input('kurzbeschreibung');

            $objekt_info = new objekt ();
            if ($clean_arr ['RECHNUNG_EMPFAENGER_TYP'] == 'Objekt') {
                $objekt_info->get_objekt_name($clean_arr ['RECHNUNG_EMPFAENGER_ID']);
                $objekt_info->get_objekt_eigentuemer_partner($clean_arr ['RECHNUNG_EMPFAENGER_ID']);
                $clean_arr ['kurzbeschreibung'] = "Rechnung für $objekt_info->objekt_name<br>$kurzbeschreibung";
            }
            if ($clean_arr ['RECHNUNG_EMPFAENGER_TYP'] == 'Haus') {
                $haus_info = $r->kostentraeger_ermitteln('Haus', $clean_arr ['RECHNUNG_EMPFAENGER_ID']);
                $clean_arr ['kurzbeschreibung'] = "Rechnung für Haus $haus_info<br>$kurzbeschreibung";
            }
            if ($clean_arr ['RECHNUNG_EMPFAENGER_TYP'] == 'Einheit') {
                $einheit = $r->kostentraeger_ermitteln('Einheit', $clean_arr ['RECHNUNG_EMPFAENGER_ID']);
                $clean_arr ['kurzbeschreibung'] = "Rechnung für Einheit $einheit<br>$kurzbeschreibung";
            }
            if ($clean_arr ['RECHNUNG_EMPFAENGER_TYP'] == 'Lager') {
                $lager_info = new lager ();
                $lager_info->lager_name = $lager_info->lager_bezeichnung($clean_arr ['RECHNUNG_EMPFAENGER_ID']);
                $clean_arr ['kurzbeschreibung'] = "Rechnung an Lager $lager_info->lager_name<br>$kurzbeschreibung";
            }
            if ($clean_arr ['RECHNUNG_EMPFAENGER_TYP'] == 'Partner') {
                $clean_arr ['kurzbeschreibung'] = "Rechnung an Partner<br>$kurzbeschreibung";
            }

            $netto_betrag = 0;
            $brutto_betrag = 0;
            /* Position Einzelnettopreis berechnen und Gesamtnetto bilden */
            for ($b = 0; $b < count($uebernahme_arr ['positionen']); $b++) {
                $preis = number_format($uebernahme_arr ['positionen'] [$b] ['preis'], 2, '.', '');
                // ($zahl,2, ",", ".");
                $netto_pos = (($uebernahme_arr ['positionen'] [$b] ['menge'] * $preis) / 100) * (100 - $uebernahme_arr ['positionen'] [$b] ['rabatt_satz']);
                $netto_betrag = $netto_betrag + $netto_pos;
                $beleg_nr = $uebernahme_arr ['positionen'] [$b] ['beleg_nr'];
                $position = $uebernahme_arr ['positionen'] [$b] ['position'];
                $mwst_satz = $r->mwst_satz_der_position($beleg_nr, $position);
                $pos_mwst = $uebernahme_arr ['positionen'] [$b] ['skonto'];

                echo "Bel$beleg_nr POS$position MWST$mwst_satz SKONTO $skonto<br>";
                $brutto_betrag = $brutto_betrag + ($netto_pos + ($netto_pos / 100) * ($mwst_satz));
            }

            $clean_arr ['nettobetrag'] = number_format($netto_betrag, 2, '.', '');
            $clean_arr ['bruttobetrag'] = number_format($brutto_betrag, 2, '.', '');
            // $clean_arr[skonto]= $uebernahme_arr[RECHNUNG_SKONTO]; //prozent

            $rechnung = new rechnung ();
            /*
			 * echo "<pre>";
			 * print_r($clean_arr);
			 * echo "<hr>";
			 * print_r($uebernahme_arr);
			 */
            $gespeicherte_belegnr = $rechnung->auto_rechnung_speichern($clean_arr);

            $rechnung->auto_positionen_speichern($gespeicherte_belegnr, $uebernahme_arr ['positionen']);
            $rechnung->rechnung_als_vollstaendig($gespeicherte_belegnr);
            hinweis_ausgeben("Rechnung wurde erstellt.<br>Sie werden gleich zur neuen Rechnung weitergeleitet.");
            $rr = new rechnungen ();
            $rr->update_skontobetrag($gespeicherte_belegnr);
            $f->fieldset_ende();
        }
        break;

    case "zahlung_freigeben" :
        $r = new rechnung ();
        $belegnr = request()->input('belegnr');
        $r->rechnung_als_freigegeben($belegnr);
        hinweis_ausgeben("Rechnung wurde zur Zahlung freigegeben!");
        weiterleiten_in_sec(route('web::rechnungen::legacy', ['option' => 'rechnungs_uebersicht', 'belegnr' => $in_belegnr]), 2);
        break;

    case "als_bezahlt_markieren" :
        $r = new rechnung ();
        $belegnr = request()->input('belegnr');
        $r->rechnung_als_freigegeben($belegnr);
        hinweis_ausgeben("Rechnung wurde zur Zahlung freigegeben!");
        weiterleiten_in_sec(route('web::rechnungen::legacy', ['option' => 'rechnungs_uebersicht', 'belegnr' => $in_belegnr]), 2);
        break;

    case "partner_wechseln" :
        $p = new partner ();
        $p->partner_auswahl();
        break;


    case "eingangsbuch" :
        $p = new partner ();
        if (request()->has('partner_id')) {
            session()->put('partner_id', request()->input('partner_id'));
        }
        $r = new rechnung ();

        $partner_id = session()->get('partner_id');

        if (request()->has('monat') && request()->has('jahr')) {
            if (request()->input('monat') != 'alle') {
                session()->put('monat', sprintf('%02d', request()->input('monat')));
            } else {
                session()->put('monat', request()->input('monat'));
            }
            session()->put('jahr', request()->input('jahr'));
        }

        if (empty ($partner_id) && !session()->has('lager_id')) {
            $p->partner_auswahl();
        } else {
            if (session()->has('monat')) {
                $monat = session()->get('monat');
            }
            if (session()->has('jahr')) {
                $jahr = session()->get('jahr');
            }
            if (empty ($monat)) {
                $monat = date("m");
            }

            if (empty ($jahr)) {
                $jahr = date("Y");
            }
            $rechnung = new rechnung ();

            if (session()->has('partner_id') && !session()->has('lager_id')) {
                $r->rechnungseingangsbuch('Partner', $partner_id, $monat, $jahr, 'Rechnung');
            }
            if (session()->has('partner_id') && session()->has('lager_id')) {
                $r->rechnungseingangsbuch('Partner', $partner_id, $monat, $jahr, 'Rechnung');
            }
            if (!session()->has('partner_id') && session()->has('lager_id')) {
                $r->rechnungseingangsbuch('Lager', session()->get('lager_id'), $monat, $jahr, 'Rechnung');
            }
            if (!session()->has('partner_id') && !session()->has('lager_id')) {
                echo "Für Eingangsrechungen einen Partner oder ein Lager wählen";
            }
        }

        break;

    case "ausgangsbuch" :
        $p = new partner ();
        if (request()->has('partner_wechseln')) {
            session()->forget('partner_id');
            $p->partner_auswahl();
        }

        if (request()->has('partner_id')) {
            session()->put('partner_id', request()->input('partner_id'));
        }
        $r = new rechnung ();

        $partner_id = session()->get('partner_id');

        if (request()->has('monat') && request()->has('jahr')) {
            if (request()->input('monat') != 'alle') {
                session()->put('monat', sprintf('%02d', request()->input('monat')));
            } else {
                session()->put('monat', request()->input('monat'));
            }
            session()->put('jahr', request()->input('jahr'));
        }

        if (empty ($partner_id) && !session()->has('lager_id')) {
            $p->partner_auswahl();
        } else {
            $monat = session()->get('monat');
            $jahr = session()->get('jahr');

            if (empty ($monat) or empty ($jahr)) {
                $monat = date("m");
                $jahr = date("Y");
            }
            $rechnung = new rechnung ();

            if (session()->has('partner_id') && !session()->has('lager_id')) {
                $r->rechnungsausgangsbuch('Partner', $partner_id, $monat, $jahr, 'Rechnung');
            }
            if (session()->has('partner_id') && session()->has('lager_id')) {
                $r->rechnungsausgangsbuch('Partner', $partner_id, $monat, $jahr, 'Rechnung');
            }
            if (!session()->has('partner_id') && session()->has('lager_id')) {
                $r->rechnungsausgangsbuch('Lager', session()->get('lager_id'), $monat, $jahr, 'Rechnung');
            }
            if (!session()->has('partner_id') && !session()->has('lager_id')) {
                echo "Für Ausgangsrechungen einen Partner oder ein Lager wählen";
            }
        }

        break;

    /* Rechnungspositionen erfassen Version 2 */
    case "positionen_erfassen_alt" :
        $r = new rechnungen ();
        $belegnr = request()->input('belegnr');
        if (!empty ($belegnr)) {
            $r->form_positionen_erfassen($belegnr);
        }
        break;

    /* Rechnungspositionen erfassen Version 2 mit Autovervollständigen */
    case "positionen_erfassen" :
        $r = new rechnungen ();
        $belegnr = request()->input('belegnr');
        if (!empty ($belegnr)) {
            $r->form_positionen_erfassen2($belegnr);
        }
        break;

    /* Rechnungsposition ändern */
    case "position_aendern" :
        $r = new rechnungen ();
        $pos = request()->input('pos');
        $belegnr = request()->input('belegnr');
        if (!empty ($belegnr) && !empty ($pos)) {
            $r->form_positionen_aendern($pos, $belegnr);
        }
        break;

    /* Rechnungsposition ändern */
    case "position_loeschen" :
        $r = new rechnung ();
        $pos = request()->input('pos');
        $belegnr = request()->input('belegnr');
        if (!empty ($belegnr) && !empty ($pos)) {
            $r->position_deaktivieren($pos, $belegnr);
            echo "POSITION GELÖSCHT";
            weiterleiten_in_sec(route('web::rechnungen::legacy', ['option' => 'positionen_erfassen', 'belegnr' => $belegnr], false), 1);
        }
        break;

    /* Rechnung buchen ALT KOMBI-Chronologisch */
    case "rechnung_buchen" :
        $r = new rechnungen ();
        $belegnr = request()->input('belegnr');
        if (!empty ($belegnr)) {
            session()->put('url.intended', URL::previous());
            $r->form_rechnung_buchen($belegnr);
        } else {
            hinweis_ausgeben('Keine Rechung gewählt!');
        }
        break;

    /* Rechnung Zahlung buchen */
    case "rechnung_zahlung_buchen" :
        $belegnr = request()->input('belegnr');

        $r = new rechnungen ();
        if (!empty ($belegnr)) {
            session()->put('url.intended', URL::previous());
            $r1 = new rechnung ();
            $r1->rechnung_als_freigegeben($belegnr);
            $r->form_rechnung_zahlung_buchen($belegnr);
        } else {
            hinweis_ausgeben('Keine Rechung gewählt!');
        }
        break;

    /* Rechnung durch Kontoauszug bestätigen und buchen */
    case "rechnung_empfang_buchen" :
        $r = new rechnungen ();
        $belegnr = request()->input('belegnr');

        if (!empty ($belegnr)) {
            session()->put('url.intended', URL::previous());
            $r1 = new rechnung ();
            $r1->rechnung_als_freigegeben($belegnr);
            $r->form_rechnung_empfang_buchen($belegnr);
        } else {
            hinweis_ausgeben('Keine Rechung gewählt!');
        }
        break;

    /* Rechnung buchen, daten gesendet */
    case "rechnung_buchen_gesendet" :
        $r = new rechnungen ();
        $b = new buchen ();
        $buchungsbetrag = request()->input('buchungsbetrag');
        $buchungs_art = request()->input('buchungsart');
        $belegnr = request()->input('belegnr');
        $r->rechnung_grunddaten_holen($belegnr);
        echo "<pre>";
        $datum = date_german2mysql(request()->input('datum'));
        $kto_auszugsnr = request()->input('kontoauszugsnr');
        $vzweck = request()->input('vzweck');
        $geldkonto_id = request()->input('geld_konto');
        $kostentraeger_typ = request()->input('kostentraeger_typ');
        $kostentraeger_id = request()->input('kostentraeger_id');
        $kostenkonto = request()->input('kostenkonto');
        session()->put('geldkonto_id', $geldkonto_id);
        session()->put('temp_kontoauszugsnummer', $kto_auszugsnr);

        /* Entscheidung ob Rechnung oder Gutschrift gebucht wird, daher + o. - als vorzeichen */
        if ($r->rechnungstyp == 'Rechnung' or $r->rechnungstyp == 'Buchungsbeleg') {
            /* Zahlung */
            if ($r->empfangs_geld_konto != $geldkonto_id) {
                $vorzeichen = '-';
            } else {
                /* Empfang */
                $vorzeichen = '';
            }
        }

        if ($r->rechnungstyp == 'Gutschrift' or $r->rechnungstyp == 'Stornorechnung') {
            /* Zahlung */
            if ($r->empfangs_geld_konto != $geldkonto_id) {
                $vorzeichen = '';
            } else {
                $vorzeichen = '-';
            }
        }

        /* Falls nur ein Betrag zu buchen ist */
        if ($buchungs_art == 'Gesamtbetrag') {
            if ($buchungsbetrag == 'Skontobetrag') {
                $proz = $r->rechnungs_mwst / ($r->rechnungs_brutto / 100);
                $skontiert_mwst = ($r->rechnungs_skontobetrag * $proz) / 100;
                $b->geldbuchung_speichern_rechnung($datum, $kto_auszugsnr, $belegnr, $vorzeichen . $r->rechnungs_skontobetrag, $vzweck, $geldkonto_id, $kostentraeger_typ, $kostentraeger_id, $kostenkonto, $vorzeichen . $skontiert_mwst);
            }
            if ($buchungsbetrag == 'Bruttobetrag') {
                $b->geldbuchung_speichern_rechnung($datum, $kto_auszugsnr, $belegnr, $vorzeichen . $r->rechnungs_brutto, $vzweck, $geldkonto_id, $kostentraeger_typ, $kostentraeger_id, $kostenkonto, $vorzeichen . $r->rechnungs_mwst);
            }
            if ($buchungsbetrag == 'Nettobetrag') {
                $b->geldbuchung_speichern_rechnung($datum, $kto_auszugsnr, $belegnr, $vorzeichen . $r->rechnungs_netto, $vzweck, $geldkonto_id, $kostentraeger_typ, $kostentraeger_id, $kostenkonto);
            }
        }

        /* Falls mehrere Beträge zu buchen sind, d.h wie kontiert */
        if ($buchungs_art == 'Teilbetraege') {
            $r->beleg_kontierungs_arr($datum, $kto_auszugsnr, $belegnr, $vorzeichen, $buchungsbetrag, $vzweck, $geldkonto_id);
        }

        if ($r->empfangs_geld_konto == $geldkonto_id) {
            if ($r->rechnungstyp == 'Gutschrift') {
                $r->rechnung_als_gezahlt($belegnr, $datum);
            } else {
                $r->rechnung_als_bestaetigt($belegnr, $datum);
            }
        } else {
            if ($r->rechnungstyp == 'Gutschrift') {
                $r->rechnung_als_bestaetigt($belegnr, $datum);
            } else {
                $r->rechnung_als_gezahlt($belegnr, $datum);
            }
        }

        weiterleiten(redirect()->intended()->getTargetUrl());
        break;

    case "pos_kontierung_aufheben" :
        $dat = request()->input('dat');
        $id = request()->input('id');
        $belegnr = request()->input('belegnr');
        if (!empty ($dat) && !empty ($id) && !empty ($belegnr)) {
            $r = new rechnung ();
            $r->pos_kontierung_aufheben($dat, $id);
            hinweis_ausgeben("Kontierung wurde aufgehoben");
            weiterleiten_in_sec(route('web::rechnungen::legacy', ['option' => 'rechnung_kontieren', 'belegnr' => $belegnr], false), 2);
        }
        break;

    case "rechnungsgrunddaten_aendern" :
        $belegnr = request()->input('belegnr');
        if (!empty ($belegnr)) {
            $r = new rechnungen ();
            $r->form_rechnungsgrunddaten_aendern($belegnr);
        } else {
            back();
        }
        break;

    // rechnung_gd_gesendet
    case "rechnung_gd_gesendet" :
        $form = new formular ();
        $form->fieldset("Grunddaten speichern", 'grunddaten speichern');
        $rechnung_dat = request()->input('rechnung_dat');
        $belegnr = request()->input('belegnr');
        $rechnungsnummer = request()->input('rechnungsnummer');
        $a_ausnr = request()->input('a_ausnr');
        $e_einnr = request()->input('e_einnr');
        $r_datum = request()->input('rechnungsdatum');
        $ein_datum = request()->input('eingangsdatum');
        $netto = request()->input('netto');
        $brutto = request()->input('brutto');
        $skontobetrag = nummer_komma2punkt(request()->input('skontobetrag'));
        $aussteller_typ = request()->input('aus_typ');
        $aussteller_id = request()->input('aus_id');
        $empfaenger_typ = request()->input('ein_typ');
        $empfaenger_id = request()->input('ein_id');
        $stat_erfasst = request()->input('status_erfasst');
        $stat_voll = request()->input('status_voll');
        $stat_zugew = request()->input('status_zugew');
        $stat_z_frei = request()->input('status_z_frei');
        $stat_bezahlt = request()->input('status_bezahlt');
        $faellig_am = request()->input('faellig_am');
        $bezahlt_am = request()->input('bezahlt_am');
        $kurzb = request()->input('kurzbeschreibung');
        $empfangs_gkonto = request()->input('empfangs_geldkonto');
        $rechnungs_typ = request()->input('rechnungstyp');

        $r = new rechnungen ();
        $r->rechnung_deaktivieren($rechnung_dat);
        $r->rechnungs_aenderungen_speichern($rechnung_dat, $belegnr, $rechnungsnummer, $a_ausnr, $e_einnr, $rechnungs_typ, $r_datum, $ein_datum, $netto, $brutto, $skontobetrag, $aussteller_typ, $aussteller_id, $empfaenger_typ, $empfaenger_id, $stat_erfasst, $stat_voll, $stat_zugew, $stat_z_frei, $stat_bezahlt, $faellig_am, $bezahlt_am, $kurzb, $empfangs_gkonto);
        weiterleiten_in_sec(route('web::rechnungen::legacy', ['option' => 'rechnungs_uebersicht', 'belegnr' => $belegnr], false), 2);
        $form->fieldset_ende();

        break;

    case "testr" :
        $form = new formular ();
        $form->fieldset("TEST", 'test');
        $r = new rechnungen ();
        $r->ursprungs_rechnungs_nr_arr(1667);
        echo "<pre>";

        $r->ursprungs_array_a = array_reverse($r->ursprungs_array, TRUE);

        $anzahl_el = count($r->ursprungs_array_a);
        print_r($r->ursprungs_array_a);

        $rechnungsnrs_arr = array_keys($r->ursprungs_array_a);
        $erste_rechnungsnr = $rechnungsnrs_arr [0];
        for ($a = 0; $a < $anzahl_el; $a++) {
            $erste_rechnungsnr = $rechnungsnrs_arr [0];
            $akt_rech_nr = $rechnungsnrs_arr [$a];

            $anzahl_vor_rechnungen = count($r->ursprungs_array_a [$akt_rech_nr]);
            $brojac = '-';
            for ($i = 0; $i < $anzahl_vor_rechnungen; $i++) {
                $brojac .= '-';
                $vorrechnung = $r->ursprungs_array_a [$akt_rech_nr] [$i] ['U_BELEG_NR'];
                if ($vorrechnung != $akt_rech_nr) {
                    echo $brojac . $vorrechnung;
                } else {
                    echo "<br>";
                }
            }
            echo "<b>|-$akt_rech_nr</b>";
        }
        $form->fieldset_ende();
        break;

    case "lieferschein_erfassen" :
        $beleg_nr = request()->input('beleg_nr');
        if (!empty ($beleg_nr)) {
            $r = new rechnungen ();
            $r->form_lieferschein_erfassen($beleg_nr);
        } else {
            echo "Belegnr fehlt";
        }
        break;

    case "buchungsbelege" :
        $monat = request()->input('monat');
        $jahr = request()->input('jahr');
        if (empty ($monat)) {
            $monat = date("m");
        }
        if (empty ($jahr)) {
            $jahr = date("Y");
        }
        $r = new rechnungen ();
        $buchungsbelege_arr = $r->buchungsbelege_arr($monat, $jahr);
        $bg = new berlussimo_global ();
        $link = route('web::rechnungen::legacy', ['option' => 'buchungsbelege'], false);
        $bg->monate_jahres_links($jahr, $link);
        $r->rechnungsbuch_anzeigen_ein_kurz($buchungsbelege_arr);
        break;

    default :
        echo "Rechnungen hauptseite";

        break;

    case "artikel_bau" :
        $r = new rechnungen ();
        $r->artikel_pro_kos_anzeigen('3', 'Einheit', '213', '1023');
        break;

    case "anzeigen_pdf" :
        if (request()->has('belegnr') && is_numeric(request()->input('belegnr'))) {
            $r = new rechnungen ();
            $r->rechnung_anzeigen(request()->input('belegnr'));
        } else {
            echo "Rechnung wählen " . request()->input('belegnr');
        }
        break;

    case "rechnungsbuch_ausgang" :
        $r = new rechnungen ();
        if (session()->has('partner_id')) {
            $r->rechnungsausgangsbuch_pdf('Partner', session()->get('partner_id'), request()->input('monat'), request()->input('jahr'), request()->input('r_typ'), request()->input('sort'));
        } else {
            if (session()->has('lager_id')) {
                $r->rechnungsausgangsbuch_pdf('Lager', session()->get('lager_id'), request()->input('monat'), request()->input('jahr'), request()->input('r_typ'), request()->input('sort'));
            } else {
                echo "Für Lagerrechnungen Lager wählen und für Partnerrechnungen den Partner";
            }
        }
        break;

    case "rechnungsbuch_eingang" :
        $r = new rechnungen ();
        if (session()->has('partner_id')) {
            $r->rechnungseingangsbuch_pdf('Partner', session()->get('partner_id'), request()->input('monat'), request()->input('jahr'), request()->input('r_typ'), request()->input('sort'));
        } else {
            if (session()->has('lager_id')) {
                $r->rechnungseingangsbuch_pdf('Lager', session()->get('lager_id'), request()->input('monat'), request()->input('jahr'), request()->input('r_typ'), request()->input('sort'));
            } else {
                echo "Für Lagerrechnungen Lager wählen und für Partnerrechnungen den Partner";
            }
        }

        break;

    case "rechnungsbuch_suche" :
        $r = new rechnungen ();
        $r->form_rbuecher_suchen();
        ?>
        <script>
            document.addEventListener("DOMContentLoaded", function (event) {
                var typ = document.getElementById("r_inhaber_t");
                list_kostentraeger('list_kostentraeger', typ.value);
            });
        </script>
        <?php
        break;

    case "rechnungsbuch_suche1" :
        if (request()->has('buchart') && request()->has('r_inhaber_t') && request()->has('r_inhaber') && request()->has('r_art') && request()->has('monat') && request()->has('jahr')) {
            $r = new rechnungen ();
            $buchart = request()->input('buchart');
            $r_inhaber_t = request()->input('r_inhaber_t');
            $r_inhaber_id = request()->input('r_inhaber');

            if (empty ($r_inhaber_id)) {
                throw new \App\Exceptions\MessageException(
                    new \App\Messages\ErrorMessage("Datenfehler - Rechnungsinhaber $r_inhaber_t unbekannt")
                );
            }

            $r_art = request()->input('r_art');
            $monat = request()->input('monat');
            if (is_numeric($monat)) {
                $monat = sprintf('%02d', $monat);
            }
            $jahr = request()->input('jahr');

            if ($buchart == 'ausgangsbuch') {
                $r->rechnungsausgangsbuch_pdf($r_inhaber_t, $r_inhaber_id, $monat, $jahr, $r_art, 'ASC');
            }
            if ($buchart == 'eingangsbuch') {
                echo "$r_inhaber_t,$r_inhaber_id, $monat, $jahr, $r_art, " . request()->input('sort');
                $r->rechnungseingangsbuch_pdf($r_inhaber_t, $r_inhaber_id, $monat, $jahr, $r_art, 'ASC');
            }
        } else {
            echo "Eingabe unvollständig";
        }
        break;

    /* Angebote */
    case "angebot_erfassen" : // Angebot anlegen maske
        $r = new rechnungen ();
        $r->form_angebot_erfassen();
        break;

    case "angebot_erfassen1" : // Angebot anlegen/speichern
        $r = new rechnungen ();
        if (request()->has('aussteller_typ')
            && request()->has('aussteller_id')
            && request()->has('empfaenger_typ')
            && request()->has('empfaenger_id')
        ) {
            $r->angebot_speichern(request()->input('aussteller_typ'),
                request()->input('aussteller_id'),
                request()->input('empfaenger_typ'),
                request()->input('empfaenger_id'),
                request()->input('kurzbeschreibung'));
        } else {
            fehlermeldung_ausgeben("Daten unvollständig");
        }
        break;

    case "meine_angebote" :
        $r = new rechnungen ();
        $r->meine_angebote_anzeigen();
        break;

    case "ang_bearbeiten" :
        if (request()->input('ang_id')) {
            $r = new rechnungen ();
            $r->form_angebot_bearbeiten(request()->input('ang_id'));
        } else {
            echo "Angebot wählen";
        }

        break;

    case "ang2beleg" :
        if (request()->has('belegnr')) {
            $r = new rechnungen ();
            $r->angebot2beleg(request()->has('belegnr'));
        } else {
            fehlermeldung_ausgeben("Angebot wählen!");
        }
        break;

    /*
	 * Aus noch unbekanntem Grund, tauchen 99.99% Rabatt oder 9.99% Skonti in neuen
	 * Rechnungen auf. Beim öffnen der Rechnung wird es erkannt und eine Option für die Autokorrektur angeboten
	 * Bei der Korrektur wird aus der Ursprungsrechnung der Rabatt und Skonti übernommen
	 */
    case "autokorrektur_pos" :
        if (request()->has('belegnr')) {
            $r = new rechnungen ();
            $r->autokorrektur_pos(request()->input('belegnr'));
        } else {
            fehlermeldung_ausgeben('Bitte Rechnung wählen!');
        }
        break;

    case "edisnp" :
        if (request()->has('belegnr')) {
            $r = new rechnungen ();
            $r->edisp(request()->input('belegnr'));
        }
        break;

    case "reg_pool" :
        session()->put('pool_id', request()->input('pool_id'));
        break;

    case "u_pool_liste" :
        $f = new formular ();
        $f->fieldset('Kostentraeger wählen', 'pool_tab2');
        $r = new rechnungen ();
        $r->pool_liste_wahl();
        $f->fieldset_ende();
        break;

    case "u_pool_edit" :
        $f = new formular ();
        $f->fieldset('POOL', 'pool_tab');
        $r = new rechnungen ();
        if (request()->has('kos_typ') && request()->has('kos_id')) {
            $r->u_pool_edit(request()->input('kos_typ'), request()->input('kos_id'), request()->input('aussteller_typ'), request()->input('aussteller_id'));
        } else {
            echo "Rechnungsempfänger wählen";
        }
        $f->fieldset_ende();
        break;

    case "u_pool_erstellen" :
        $f = new formular ();
        $f->fieldset('Unterpools erstellen', 'u_pool_tab');
        $r = new rechnungen ();
        $r->u_pools_erstellen();
        $f->fieldset_ende();
        break;

    /* Verbindlichkeiten */
    case "verbindlichkeiten" :
        $r = new rechnungen ();
        if (!request()->has('jahr')) {
            $jahr = date("Y");
        } else {
            $jahr = request()->input('jahr');
        }
        $r->verbindlichkeiten($jahr);
        break;

    /* Forderungen */
    case "forderungen" :
        $r = new rechnungen ();
        $jahr = date("Y");
        $r->forderungen($jahr);
        break;

    /* Importfunktion HWPWIN Projekttransfer XML */
    case "import_hwpwin_xml_rechnung" :
        $r = new rechnungen ();
        $r->test_xml();
        break;

    case "import_ugl" :
        $r = new rechnungen ();
        $r->test_ugl();
        break;

    case "form_ugl" :
        $r = new rechnungen ();
        $r->form_import_ugl();
        break;

    case "ugl_sent" :
        $r = new rechnungen ();
        $tmp_datei = request()->file('Datei')->getRealPath();
        $arr = $r->get_ugl_arr($tmp_datei);

        if (is_array($arr)) {
            @unlink($tmp_datei);
            $aussteller_typ = 'Partner';
            $aussteller_id = request()->input('aussteller_id');
            $empfaenger_typ = 'Partner';
            $empfaenger_id = request()->input('empfaenger_id');
            $rnr = request()->input('rnr');
            $r_datum = request()->input('r_datum');
            $faellig = request()->input('faellig');
            $eingangsdatum = request()->input('eingangsdatum');
            $kurzinfo = request()->input('kurzbeschreibung');
            $skonto = request()->input('skonto');
            $kurzinfo_ugl = $r->ibm850_encode($arr ['a_nr_hw'] . ' ' . $arr ['kundentext'] . ' ' . $arr ['vorgangsnr_gh'] . ' ' . $arr ['datum_d']);
            $kurzinfo .= '\n ' . $kurzinfo_ugl;

            echo "<b>$kurzinfo</b>";
            if ($arr ['a_art'] != 'PA' && $arr ['a_art'] != 'AB' && $arr ['a_art'] != 'RG' && $arr ['a_art'] != 'BE') {
                $aart = $arr ['a_art'];
                throw new \App\Exceptions\MessageException(
                    new \App\Messages\ErrorMessage("Abbruch!<br>Die Datei ist kein Angebot, sowie keine Rechnung!!! <b>TYP:$aart</b>")
                );
            }
            if ($arr ['a_art'] == 'PA') { // Preisangebot
                $r_typ = 'Angebot';
            }
            if ($arr ['a_art'] == 'AB' or $arr ['a_art'] == 'RG' or $arr ['a_art'] == 'BE') {
                $r_typ = 'Rechnung'; // Auftragsbestätigung
            }

            $beleg_nr = $r->rechnung_erstellen_ugl($rnr, $r_typ, $r_datum, $eingangsdatum, $aussteller_typ, $aussteller_id, $empfaenger_typ, $empfaenger_id, $faellig, $kurzinfo, 0, 0, 0);
            $anz = count($arr ['positionen_arr']);
            for ($a = 1; $a <= $anz; $a++) {
                $pos_typ = $arr ['positionen_arr'] [$a] ['POS_TYP'];
                $artikel_nr = ltrim(rtrim($arr ['positionen_arr'] [$a] ['ARTIKELNR']));
                $menge = $arr ['positionen_arr'] [$a] ['MENGE'] / 1000;
                $pos_netto = $arr ['positionen_arr'] [$a] ['POS_NETTO'] / 100;
                $e_preis = $pos_netto / $menge;
                $rabatt1 = $arr ['positionen_arr'] [$a] ['RABATT1'] / 100;
                $rabatt2 = $arr ['positionen_arr'] [$a] ['RABATT2'] / 100;
                $listenpreis = ($e_preis / (100 - $rabatt1)) * 100;
                $bezeichnung = $r->ibm850_encode($arr ['positionen_arr'] [$a] ['ARTBEZ1'] . ' ' . $arr ['positionen_arr'] [$a] ['ARTBEZ2']);
                $mwst = '19';

                $vpe = $arr ['positionen_arr'] [$a] ['PE'];
                if ($vpe == '0') {
                    $vpe = 'Stk';
                }
                if ($vpe == '2') {
                    $vpe = 'Stk';
                    // $vpe = '10Stk';
                }
                if ($vpe == '3') {
                    $vpe = 'Stk';
                    // $vpe = '100Stk';
                }
                if ($vpe == '4') {
                    $vpe = 'Stk';
                    // $vpe = '1000Stk';
                }

                $r1 = new rechnung ();
                if (empty($r1->artikel_info($aussteller_id, $artikel_nr))) {
                    $r1->artikel_leistung_mit_artikelnr_speichern($aussteller_id, $bezeichnung, $listenpreis, $artikel_nr, $rabatt1, $vpe, $mwst, $skonto);
                }
                echo "$a. $bezeichnung<br>";

                $r->position_speichern($beleg_nr, $beleg_nr, $aussteller_id, $artikel_nr, $menge, $listenpreis, $mwst, $skonto, $rabatt1, $pos_netto);
            }
            weiterleiten_in_sec(route('web::rechnungen::legacy', ['option' => 'rechnungs_uebersicht', 'belegnr' => $beleg_nr]), 3);
        }

        break;

    case "import_csv" :
        echo "CSV";
        $r = new rechnungen ();
        $r->form_import_csv();
        break;

    case "csv_sent" :
        $r = new rechnungen ();
        $tmp_datei = request()->file('Datei')->getRealPath();
        if ($handle = fopen($tmp_datei, "r")) {
            $zeilen = file($tmp_datei);
        } else {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\ErrorMessage('Datei konnte nicht gelesen werden!')
            );
        }
        echo '<pre>';
        $arr = $zeilen;
        if (is_array($arr)) {
            @unlink($tmp_datei);
            $aussteller_typ = 'Partner';
            $aussteller_id = request()->input('aussteller_id');
            $empfaenger_typ = 'Partner';
            $empfaenger_id = request()->input('empfaenger_id');
            $rnr = request()->input('rnr');
            $r_datum = request()->input('r_datum');
            $faellig = request()->input('faellig');
            $eingangsdatum = request()->input('eingangsdatum');
            $kurzinfo = request()->input('kurzbeschreibung');
            $skonto = request()->input('skonto');
            $beleg_typ = request()->input('beleg_typ');

            $beleg_nr = $r->rechnung_erstellen_csv($beleg_typ, $r_datum, $eingangsdatum, $aussteller_typ, $aussteller_id, $empfaenger_typ, $empfaenger_id, $faellig, $kurzinfo, 0, 0, 0);
            $anz = count($arr);
            $b_pos = 1;
            for ($a = 1; $a < $anz; $a++) {
                $zeile = explode(';', $arr [$a]);
                $pos_typ = $zeile [2]; // Einheit LV LG
                if ($pos_typ == 'Position') {
                    $artikel_nr = ltrim(rtrim($zeile [0])) . ltrim(rtrim($zeile [16]));
                    $menge = nummer_komma2punkt($zeile [3]);
                    $vpe = $zeile [4];
                    $pos_netto = nummer_komma2punkt($zeile [10]);
                    $e_preis = $pos_netto / $menge;
                    $rabatt1 = $zeile [6];
                    $listenpreis = ($pos_netto / (100 - $rabatt1) * 100) / $menge;
                    $bezeichnung = $zeile [1];
                    $mwst = $zeile [7];

                    $r1 = new rechnung ();
                    if (empty($r1->artikel_info($aussteller_id, $artikel_nr))) {
                        $r1->artikel_leistung_mit_artikelnr_speichern($aussteller_id, $bezeichnung, $listenpreis, $artikel_nr, $rabatt1, $vpe, $mwst, $skonto);
                    }
                    echo "$a. $bezeichnung<br>";

                    $r->position_speichern($beleg_nr, $beleg_nr, $aussteller_id, $artikel_nr, $menge, $listenpreis, $mwst, $skonto, $rabatt1, $pos_netto);
                    $b_pos++;
                }

                if ($pos_typ == 'LG') {
                    $pool_bez = $zeile [0] . ' ' . $zeile [1];
                    $rr = new rechnungen ();
                    $rr->insert_pool_bez_in_gruppe($pool_bez, $beleg_nr, $b_pos);
                }
            } // end for
            weiterleiten_in_sec(route('web::rechnungen::legacy', ['option' => 'rechnungs_uebersicht', 'belegnr' => $beleg_nr]), 3);
        }

        break;

    case "kosten_einkauf" :
        $r = new rechnungen ();
        $r->form_kosten_einkauf();
        break;

    case "kosten_einkauf_send" :
        if (request()->has('kostentraeger_typ') && request()->has('kostentraeger_id') && request()->has('empf_typ') && request()->has('empf_id')) {
            $r = new rechnungen ();
            $kos_typ = request()->input('kostentraeger_typ');
            $kos_bez = request()->input('kostentraeger_id');
            $b = new buchen ();
            $kos_id = $b->kostentraeger_id_ermitteln($kos_typ, $kos_bez);
            $empf_typ = request()->input('empf_typ');
            $empf_id = request()->input('empf_id');

            $r->kosten_einkauf($kos_typ, $kos_id, $empf_typ, $empf_id);
        } else {
            echo "Kostentraeger Koniertung wählen";
        }
        break;

    case "teil_rg_hinzu" :
        if (request()->has('beleg_id')) {
            $r = new rechnungen ();
            $beleg_id = request()->input('beleg_id');
            $r->form_teil_rg_hinzu($beleg_id);
        } else {
            echo "Schlussrechnung wählen";
        }
        break;

    case "send_teil_rg" :
        if (request()->has('beleg_id') && is_array(request()->input('tr_ids'))) {
            $r = new rechnungen ();
            $r->teilrechnungen_hinzu(request()->input('beleg_id'), request()->input('tr_ids'));
            $beleg_id = request()->input('beleg_id');
            weiterleiten(route('web::rechnungen::legacy', ['option' => 'teil_rg_hinzu', 'beleg_id' => $beleg_id], false));
        } else {
            echo "Auswahl unvollständig err:RGSJH2000";
        }
        break;

    case "teil_rg_loeschen" :
        if (request()->has('beleg_id') && request()->has('t_beleg_id')) {
            $r = new rechnungen ();
            $r->teilrechnungen_loeschen(request()->input('beleg_id'), request()->input('t_beleg_id'));
            $beleg_id = request()->input('beleg_id');
            weiterleiten(route('web::rechnungen::legacy', ['option' => 'teil_rg_hinzu', 'beleg_id' => $beleg_id], false));
        } else {
            echo "Auswahl unvollständig err:RGSJH3000";
        }
        break;

    case "seb" :
        $rr = new rechnungen ();
        $rr->seb_rgs_anzeigen();
        break;

    case "vg_rechnungen" :
        if (!session()->has('objekt_id') or !session()->has('partner_id')) {
            fehlermeldung_ausgeben("Partner (Hausverwalter) und Objekt wählen");
            return;
        }
        $rr = new rechnungen ();
        $rr->form_vg_rechnungen(session()->get('objekt_id'), session()->get('partner_id'));
        break;

    case "rgg" :
        if (!request()->has('check')) {
            echo "Bitte wählen Sie eine Einheit.";
            return;
        }
        $einheiten = request()->input('check');

        if (request()->has('kostenkonto')) {
            $kostenkonto = request()->input('kostenkonto');
        } else {
            echo "Bitte wählen Sie ein Kostenkonto.";
            return;
        }

        $anz_e = count($einheiten);
        $brutto_betrag = nummer_komma2punkt(request()->input('brutto'));
        $kurztext = request()->input('kurztext');
        for ($a = 0; $a < $anz_e; $a++) {
            $id = $einheiten [$a];
            $einheit_id = request()->input('EINHEITEN') [$id];
            $empf_typ = request()->input('EMPF_TYP') [$id];
            $empf_id = request()->input('EMPF_ID') [$id];
            $e = new einheit ();
            $e->get_einheit_info($einheit_id);
            $kurztext_neu = "$kurztext\nEinheit:$e->einheit_kurzname $e->einheit_lage, $e->haus_strasse $e->haus_nummer, $e->haus_plz $e->haus_stadt";
            echo "$einheit_id $empf_typ $empf_id $kurztext_neu<br>";
            $r = new rechnung ();
            $letzte_belegnr = $r->letzte_beleg_nr() + 1;

            $jahr = date("Y");
            $datum = date("Y-m-d");
            $letzte_aussteller_rnr = $r->letzte_aussteller_ausgangs_nr(session()->get('partner_id'), 'Partner', $jahr, 'Rechnung') + 1;
            $letzte_aussteller_rnr = sprintf('%03d', $letzte_aussteller_rnr);
            $r->rechnungs_kuerzel = $r->rechnungs_kuerzel_ermitteln('Partner', session()->get('partner_id'), $datum);
            $rechnungsnummer = $r->rechnungs_kuerzel . ' ' . $letzte_aussteller_rnr . '-' . $jahr;

            $letzte_empfaenger_rnr = $r->letzte_empfaenger_eingangs_nr($empf_id, $empf_typ, $jahr, 'Rechnung') + 1;

            $netto_betrag = $brutto_betrag / 1.19;
            $gk = new geldkonto_info ();
            $gk->geld_konto_ermitteln('Partner', session()->get('partner_id'), null, 'Kreditor');
            $faellig_am = tage_plus($datum, 10);
            $db_abfrage = "INSERT INTO RECHNUNGEN VALUES (NULL, '$letzte_belegnr', '$rechnungsnummer', '$letzte_aussteller_rnr', '$letzte_empfaenger_rnr', 'Rechnung', '$datum','$datum', '$netto_betrag','$brutto_betrag','0.00', 'Partner', '" . session()->get('partner_id') . "','$empf_typ', '$empf_id','1', '1', '0', '0', '0', '0', '0', '$faellig_am', '0000-00-00', '$kurztext_neu', '$gk->geldkonto_id')";
            DB::insert($db_abfrage);
            /* Protokollieren */
            $last_dat = DB::getPdo()->lastInsertId();
            protokollieren('RECHNUNGEN', $last_dat, '0');

            /* Positionen erfassen */
            $art_nr = "VG-" . $einheit_id;
            $r->artikel_leistung_mit_artikelnr_speichern(session()->get('partner_id'), "Verwaltergebähr $e->einheit_kurzname", '14.99', "$art_nr", '0', 'Stk', '19', '0');
            $letzte_rech_pos_id = $r->get_last_rechnung_pos_id() + 1;
            $p_id = session()->get('partner_id');
            $db_abfrage = "INSERT INTO RECHNUNGEN_POSITIONEN VALUES (NULL, '$letzte_rech_pos_id', '1', '$letzte_belegnr', '$letzte_belegnr','$p_id', '$art_nr', '1','$netto_betrag','19', '0','0', '$netto_betrag','1')";
            DB::insert($db_abfrage);
            /* Protokollieren */
            $last_dat = DB::getPdo()->lastInsertId();
            protokollieren('RECHNUNGEN_POSITIONEN', $last_dat, '0');

            /* Kontieren */
            $kontierung_id = $r->get_last_kontierung_id() + 1;

            $db_abfrage = "INSERT INTO KONTIERUNG_POSITIONEN VALUES (NULL, '$kontierung_id','$letzte_belegnr', '1','1', '$netto_betrag', '$netto_betrag', '19', '0', '0', '$kostenkonto', '$empf_typ', '$empf_id', '$datum', '$jahr', '0', '1')";
            DB::insert($db_abfrage);

            /* Protokollieren */
            $last_dat = DB::getPdo()->lastInsertId();
            protokollieren('KONTIERUNG_POSITIONEN', $last_dat, '0');

            /* In SEPA ÜBERWEISUNGEN bei Häkchen */
            if (request()->has('sepa')) {
                $r->rechnung_grunddaten_holen($letzte_belegnr);
                $vzweck = "$r->rechnungs_aussteller_name, Rg. $r->rechnungsnummer " . bereinige_string($kurztext);

                $sep = new sepa ();
                if ($sep->sepa_ueberweisung_speichern(session()->get('geldkonto_id'), $gk->geldkonto_id, $vzweck, 'Verwaltergebuehr', $empf_typ, $empf_id, $kostenkonto, $brutto_betrag) == false) {
                    fehlermeldung_ausgeben("ÜBERWEISUNG KONNTE NICHT GESPEICHERT WERDEN!");
                }
            } else {
                fehlermeldung_ausgeben("KEINE SEPA-ÜBERWEISUNG GEWÜNSCHT!");
            }
        } // END FOR

        break;

    case "rgg_ob" :
        if (request()->has('kostenkonto')) {
            $kostenkonto = request()->input('kostenkonto');
        } else {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\InfoMessage("Kostenkonto wählen")
            );
        }

        $empf_typ = request()->input('empf_typ');
        $empf_id = request()->input('empf_id');
        $kurztext = request()->input('kurztext');
        $typ_arr = request()->input('typ');
        $brutto_arr = request()->input('brutto');
        $mengen_arr = request()->input('mengen');

        $o = new objekt ();
        $o->get_objekt_infos(session()->get('objekt_id'));

        $kurztext_neu = "$kurztext\n<b>Objektname: $o->objekt_kurzname</b>";
        $r = new rechnung ();
        $letzte_belegnr = $r->letzte_beleg_nr() + 1;

        $jahr = date("Y");
        $datum = date("Y-m-d");
        $letzte_aussteller_rnr = $r->letzte_aussteller_ausgangs_nr(session()->get('partner_id'), 'Partner', $jahr, 'Rechnung') + 1;
        $letzte_aussteller_rnr = sprintf('%03d', $letzte_aussteller_rnr);
        $r->rechnungs_kuerzel = $r->rechnungs_kuerzel_ermitteln('Partner', session()->get('partner_id'), $datum);
        $rechnungsnummer = $r->rechnungs_kuerzel . ' ' . $letzte_aussteller_rnr . '-' . $jahr;

        $letzte_empfaenger_rnr = $r->letzte_empfaenger_eingangs_nr($empf_id, $empf_typ, $jahr, 'Rechnung') + 1;

        $netto_betrag = 0.00;
        $gk = new geldkonto_info ();
        $gk->geld_konto_ermitteln('Partner', session()->get('partner_id'), null, 'Kreditor');
        $faellig_am = tage_plus($datum, 10);
        $db_abfrage = "INSERT INTO RECHNUNGEN VALUES (NULL, '$letzte_belegnr', '$rechnungsnummer', '$letzte_aussteller_rnr', '$letzte_empfaenger_rnr', 'Rechnung', '$datum','$datum', '$netto_betrag','0.00','0.00', 'Partner', '" . session()->get('partner_id') . "','$empf_typ', '$empf_id','1', '1', '0', '0', '0', '0', '0', '$faellig_am', '0000-00-00', '$kurztext_neu', '$gk->geldkonto_id')";
        DB::insert($db_abfrage);
        /* Protokollieren */
        $last_dat = DB::getPdo()->lastInsertId();
        protokollieren('RECHNUNGEN', $last_dat, '0');

        $pos = 0;
        $g_sum = 0;
        for ($a = 0; $a < count($typ_arr); $a++) {
            $pos++;
            $brutto_bet = $brutto_arr [$a];

            $netto_betrag = nummer_komma2punkt($brutto_bet) / 1.19;
            $typ_bez = $typ_arr [$a];
            $menge = $mengen_arr [$a];
            $g_sum += nummer_komma2punkt($brutto_bet) * $menge;
            $g_netto = $netto_betrag * $menge;
            /* Positionen erfassen */
            $art_nr = "$o->objekt_kurzname-$typ_bez";
            $r->artikel_leistung_mit_artikelnr_speichern(session()->get('partner_id'), "Verwaltergebühr $typ_bez", $brutto_bet, "$art_nr", '0', 'Stk', '19', '0');
            $letzte_rech_pos_id = $r->get_last_rechnung_pos_id() + 1;
            $p_id = session()->get('partner_id');
            $db_abfrage = "INSERT INTO RECHNUNGEN_POSITIONEN VALUES (NULL, '$letzte_rech_pos_id', '$pos', '$letzte_belegnr', '$letzte_belegnr','$p_id', '$art_nr', $menge,'$netto_betrag','19', '0','0', '$g_netto','1')";
            DB::insert($db_abfrage);
            /* Protokollieren */
            $last_dat = DB::getPdo()->lastInsertId();
            protokollieren('RECHNUNGEN_POSITIONEN', $last_dat, '0');

            /* Kontieren */
            $kontierung_id = $r->get_last_kontierung_id() + 1;
            $db_abfrage = "INSERT INTO KONTIERUNG_POSITIONEN VALUES (NULL, '$kontierung_id','$letzte_belegnr', '$pos','$menge', '$netto_betrag', '$g_netto', '19', '0', '0', '$kostenkonto', 'Objekt', '" . session()->get('objekt_id') . "', '$datum', '$jahr', '0', '1')";
            $resultat = DB::insert($db_abfrage);

            /* Protokollieren */
            $last_dat = DB::getPdo()->lastInsertId();
            protokollieren('KONTIERUNG_POSITIONEN', $last_dat, '0');
        }

        /* In SEPA ÜBERWEISUNGEN bei Häckchen */
        if (request()->has('sepa')) {
            $r->rechnung_grunddaten_holen($letzte_belegnr);
            $vzweck = "$r->rechnungs_aussteller_name, Rg. $r->rechnungsnummer " . bereinige_string($kurztext_neu);
            $sep = new sepa ();
            if ($sep->sepa_ueberweisung_speichern(session()->get('geldkonto_id'), $gk->geldkonto_id, $vzweck, 'Verwaltergebuehr', $empf_typ, $empf_id, $kostenkonto, $g_sum) == false) {
                fehlermeldung_ausgeben("ÜBERWEISUNG KONNTE NICHT GESPEICHERT WERDEN!");
            }
        } else {
            fehlermeldung_ausgeben("KEINE SEPA-ÜBERWEISUNG GEWÜNSCHT!");
        }

        break;

    case "rg_aus_beleg" :
        if (!session()->has('partner_id')) {
            echo "Partner (Rechnungssteller) wählen!";
            return;
        }
        echo "<div class='row'>";
        echo "<div class='col-xs-12'>";
        $link_add = "<a href='" . route('web::rechnungen::legacy', ['option' => 'beleg2pool']) . "' class='btn waves-light waves-effect'><i class=\"mdi mdi-plus left\"></i>Vorlage</a>";
        echo $link_add;
        echo "</div>";
        echo "</div>";
        $r = new rechnungen ();
        $r->liste_beleg2rg();
        break;

    case "beleg2pool" :
        $r = new rechnungen ();
        $r->form_beleg2pool();
        break;

    case "beleg_sent" :
        $r = new rechnungen ();
        if (request()->has('beleg_nr') && request()->has('empf_p_id')) {
            $r->beleg2rg_db(request()->input('empf_p_id'), request()->input('beleg_nr'));
        }
        break;

    case "neue_rg" :
        if (request()->has('belegnr') && request()->has('empf_p_id') && session()->has('partner_id')) {
            $r = new rechnungen ();
            $r->rechnung_aus_beleg(session()->get('partner_id'), request()->input('belegnr'), request()->input('empf_p_id'));
        } else {
            fehlermeldung_ausgeben("FEHLER xo");
        }
        break;

    case "pdf_druckpool" :
        if (!session()->has('partner_id')) {
            fehlermeldung_ausgeben("Partner für das RA-Buch wählen!!!");
            return;
        }
        $re = new rechnungen ();
        if (!request()->has('monat')) {
            $monat = date("m");
        } else {
            $monat = request()->input('monat');
        }

        if (!request()->has('jahr')) {
            $jahr = date("Y");
        } else {
            $jahr = request()->input('jahr');
        }

        $arr = $re->ausgangsrechnungen_arr_sort('Partner', session()->get('partner_id'), $monat, $jahr, 'Rechnung', 'ASC');
        if (empty($arr)) {
            fehlermeldung_ausgeben("Keine Ausgangsrechnungen $monat / $jahr");
        } else {
            $anz = count($arr);
            $f = new formular ();
            $f->erstelle_formular("Sammeldruck als PDF", null);
            echo "<table>";
            echo "<tr><td>";
            $f->check_box_js_alle('uebernahme_alle[]', 'ue', '', 'Alle', '', '', 'uebernahme');
            echo "</td><td colspan=\"30\">RECHNUNGEN $monat/$jahr</td></tr>";
            $spalte = 0;
            echo "<tr>";
            for ($a = 0; $a < $anz; $a++) {
                $spalte++;
                $id = $arr [$a] ['BELEG_NR'];
                $rnr = $arr [$a] ['RECHNUNGSNUMMER'];
                $a_nr = $arr [$a] ['AUSTELLER_AUSGANGS_RNR'];
                echo "<td>";
                $f->check_box_js('uebernahme[]', $id, $rnr, '', 'checked');
                echo "</td>";
                if ($spalte == 30) {
                    echo "</tr><tr>";
                    $spalte = 0;
                }
            }
            echo "</tr>";
            echo "</table>";
            $f->hidden_feld('option', 'rg2pdf');
            $f->send_button('RG2PDF', 'PDF-Erstellen');
            $f->ende_formular();
        }

        break;

    case "sepa_druckpool" :
        if (!session()->has('partner_id')) {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\ErrorMessage("Partner für das RE-Buch wählen.")
            );
            
        }

        if (!session()->has('geldkonto_id')) {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\InfoMessage("Bitte wählen Sie ein Geldkonto.")
            );
        }

        $re = new rechnungen ();
        if (!request()->has('monat')) {
            $monat = date("m");
        } else {
            $monat = request()->input('monat');
        }

        if (!request()->has('jahr')) {
            $jahr = date("Y");
        } else {
            $jahr = request()->input('jahr');
        }

        $arr = $re->eingangsrechnungen_arr_sort('Partner', session()->get('partner_id'), $monat, $jahr, 'Rechnung', 'ASC');
        if (empty($arr)) {
            fehlermeldung_ausgeben("Keine Eingangsrechnungen $monat / $jahr");
        } else {
            $anz = count($arr);
            $f = new formular ();
            $f->erstelle_formular("Rg zahlen über SEPA $monat/$jahr", null);
            echo "<table>";
            echo "<tr><td>";
            $f->check_box_js_alle('uebernahme_alle[]', 'ue', '', 'Alle', '', '', 'uebernahme');
            $vormonat = sprintf('%02d', $monat - 1);
            $nachmonat = sprintf('%02d', $monat + 1);
            $link_vormonat = "<a href='" . route('web::rechnungen::legacy', ['option' => 'sepa_druckpool', 'monat' => $vormonat]) . "'>Rechnungen $vormonat/$jahr</a>";
            $link_nachmonat = "<a href='" . route('web::rechnungen::legacy', ['option' => 'sepa_druckpool', 'monat' => $nachmonat]) . "'>Rechnungen $nachmonat/$jahr</a>";
            echo "</td><td colspan=\"30\">$link_vormonat<br><b>RECHNUNGEN $monat/$jahr</b><br>$link_nachmonat</td></tr>";
            $spalte = 0;
            echo "<tr>";
            for ($a = 0; $a < $anz; $a++) {
                $spalte++;
                $id = $arr [$a] ['BELEG_NR'];
                $rnr = $arr [$a] ['RECHNUNGSNUMMER'];
                $e_nr = $arr [$a] ['WE_NR'];
                echo "<td>";
                $f->check_box_js('uebernahme[]', $id, "$e_nr:$rnr", '', 'checked');
                echo "</td>";
                if ($spalte == 30) {
                    echo "</tr><tr>";
                    $spalte = 0;
                }
            }
            echo "</tr>";
            echo "</table>";
            $f->hidden_feld('option', 'rg2sep');
            $f->send_button('RG2SEP', 'Rechnungen in SEPA-Sammler übernehmen');
            $f->ende_formular();
        }

        break;

    case "rg2sep" :
        if (!is_array(request()->input('uebernahme'))) {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\ErrorMessage("Rechnungen wählen!")
            );
        } else {
            $anz = count(request()->input('uebernahme'));
            for ($a = 0; $a < $anz; $a++) {

                $belegnr = request()->input('uebernahme')[$a];
                $re = new rechnungen ();
                $re->rechnung_grunddaten_holen($belegnr);
                $sep = new sepa ();
                if (preg_match("/$re->rechnungs_aussteller_name/i", "$re->kurzbeschreibung")) {
                    $vzweck = "$re->rechnungs_aussteller_name, Rg. $re->rechnungsnummer, $re->kurzbeschreibung";
                } else {
                    $vzweck = "Rg. $re->rechnungsnummer, $re->kurzbeschreibung";
                }

                $sep->sepa_ueberweisung_speichern(session()->get('geldkonto_id'), $re->empfangs_geld_konto, "$vzweck", 'RECHNUNGP', $re->rechnungs_aussteller_typ, $re->rechnungs_aussteller_id, '0', $re->rechnungs_skontobetrag);
            }
            weiterleiten(route('web::sepa::legacy', ['option' => 'sammler_anzeigen'], false));
        }
        break;

    case "rg2pdf" :
        if (!is_array(request()->input('uebernahme'))) {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\InfoMessage("Bitte wählen Sie eine Rechnung.")
            );
        } else {
            $anz = count(request()->input('uebernahme'));
            /* Neues PDF-Objekt erstellen */
            $pdf = new Cezpdf ('a4', 'portrait');
            /* Neue Instanz von b_pdf */
            $bpdf = new b_pdf ();
            /* Header und Footer des Rechnungsaustellers in alle PDF-Seiten laden */
            $bpdf->b_header($pdf, 'Partner', session()->get('partner_id'), 'portrait', 'Helvetica.afm', 6);

            $pdf->ezStopPageNumbers();

            for ($a = 0; $a < $anz; $a++) {
                $i = $pdf->ezStartPageNumbers(545, 715, 6, '', 'Seite {PAGENUM} von {TOTALPAGENUM}', 1);
                $id = request()->input('uebernahme') [$a];
                $re = new rechnungen ();
                $re->rechnung_2_pdf($pdf, $id);
                $pdf->ezStopPageNumbers(1, 1, $i);
                $pdf->ezNewPage();
            }

            ob_end_clean();
            /* PDF-Ausgabe */
            $pdf->ezStream();
        }
        break;
}
