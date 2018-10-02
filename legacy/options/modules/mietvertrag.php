<?php

$daten = request()->input('daten');
if (request()->has('mietvertrag_raus')) {
    $mietvertrag_raus = request()->input('mietvertrag_raus');
}
if (request()->has('einheit_id')) {
    $einheit_id = request()->input('einheit_id');
} else {
    $einheit_id = '';
}
if (request()->has('mietvertrag_raus')) {
    $mietvertrag_raus = request()->input('mietvertrag_raus');
} else {
    $mietvertrag_raus = 'default';
}

switch ($mietvertrag_raus) {

    default :
        break;

    case "mietvertrag_kurz" :
        $form = new mietkonto ();
        $form->erstelle_formular("Mietverträge", NULL);
        mietvertrag_kurz($einheit_id);
        $form->ende_formular();
        break;

    case "mietvertrag_aktuelle" :
        $form = new mietkonto ();
        $form->erstelle_formular("Aktuelle Mietverträge", NULL);
        mietvertrag_aktuelle($einheit_id);
        $form->ende_formular();
        break;

    case "mietvertrag_abgelaufen" :
        $form = new mietkonto ();
        $form->erstelle_formular("Abgelaufene Mietverträge", NULL);
        mietvertrag_abgelaufen($einheit_id);
        $form->ende_formular();
        break;

    case "ls_teilnehmer_neu" :
        $form = new mietkonto ();
        $form->erstelle_formular("Teilnehmer am Lastschriftverfahren hinzufügen", NULL);
        $mv_info = new mietvertraege ();
        $mv_info->neuer_ls_teilnehmer();
        $form->ende_formular();
        break;

    case "ls_teilnehmer" :
        $form = new mietkonto ();
        $form->erstelle_formular("Teilnehmer am Lastschriftverfahren", NULL);
        $mv_info = new mietvertraege ();
        $mv_info->ls_akt_teilnehmer();
        $form->ende_formular();
        break;

    case "ls_teilnehmer_inaktiv" :
        $form = new mietkonto ();
        $form->erstelle_formular("Ausgesetzte Teilnahmen am Lastschriftverfahren", NULL);
        $mv_info = new mietvertraege ();
        $mv_info->ls_akt_teilnehmer_ausgesetzt();
        $form->ende_formular();
        break;

    case "ls_teilnehmer_aktivieren" :
        $form = new mietkonto ();
        $form->erstelle_formular("Teilnehmer aktivieren", NULL);
        $mv_info = new mietvertraege ();
        $mv_info->teilnehmer_aktivieren(request()->input('mietvertrag_id'));
        weiterleiten_in_sec(route('web::mietvertraege::legacy', ['mietvertrag_raus' => 'ls_teilnehmer'], false), 1);
        $form->ende_formular();
        break;

    case "ls_teilnehmer_deaktivieren" :
        $form = new mietkonto ();
        $form->erstelle_formular("Teilnehmer deaktivieren", NULL);
        $mv_info = new mietvertraege ();
        $mv_info->teilnehmer_deaktivieren(request()->input('mietvertrag_id'));
        weiterleiten_in_sec(route('web::mietvertraege::legacy', ['mietvertrag_raus' => 'ls_teilnehmer_inaktiv'], false), 1);
        $form->ende_formular();
        break;

    case "ls_pruefen" :
        $form = new mietkonto ();
        $form->erstelle_formular("LS-Teilnehmer - Daten prüfen", NULL);
        /* Neuer LS-Teilnehmer */
        if (!request()->has('deaktiviere_dat')) {
            if (!request()->has('einzugsart') or !request()->has('konto_inhaber_autoeinzug') or !request()->has('konto_nummer_autoeinzug') or !request()->has('blz_autoeinzug') or !request()->has('geld_institut')) {
                $error = 'Daten unvollständig<br>';
            } else {
                if (!is_numeric(request()->input('konto_nummer_autoeinzug') or !is_numeric(request()->input('blz_autoeinzug')))) {
                    $error .= 'Kontonummer und BLZ prüfen<br>';
                }
                if (isset ($error)) {
                    echo $error;
                } else {
                    echo "Eingegebene Daten";
                    echo "<hr><b>Teilnahme am Einzugsverfahren: JA</b><br>Einzugsart: " . request()->input('einzugsart') . "<br>";
                    echo "Kontoinhaber: " . request()->input('konto_inhaber_autoeinzug') . "<br>";
                    echo "Kontonummer: " . request()->input('konto_nummer_autoeinzug') . "<br>";
                    echo "BLZ: " . request()->input('blz_autoeinzug') . "<br>";
                    echo "Geldinstitut: " . request()->input('geld_institut') . "<br>";
                    $form->hidden_feld('mietvertrag_id', request()->input('mietvertrag_id'));
                    $form->hidden_feld('einzugsart', request()->input('einzugsart'));
                    $form->hidden_feld('konto_inhaber_autoeinzug', request()->input('konto_inhaber_autoeinzug'));
                    $form->hidden_feld('konto_nummer_autoeinzug', request()->input('konto_nummer_autoeinzug'));
                    $form->hidden_feld('blz_autoeinzug', request()->input('blz_autoeinzug'));
                    $form->hidden_feld('geld_institut', request()->input('geld_institut'));

                    $form->hidden_feld('mietvertrag_raus', 'ls_neu_speichern');
                    $form->send_button('btn_ls_speichern_neu', 'Speichern');
                }
            }
        } else {
            /* Bearbeiten bzw. Daten ändern und vervollständigen */
            if (!request()->has('einzugsart') or !request()->has('konto_inhaber_autoeinzug') or !request()->has('konto_nummer_autoeinzug') or !request()->has('blz_autoeinzug') or !request()->has('geld_institut')) {
                $error = 'Daten unvollständig<br>';
            } else {
                if (!is_numeric(request()->input('konto_nummer_autoeinzug')) or !is_numeric(request()->input('blz_autoeinzug'))) {
                    $error .= 'Kontonummer und BLZ prüfen<br>';
                }
                if (isset ($error)) {
                    echo $error;
                } else {
                    echo "Eingegebene Daten";
                    echo "<hr><b>Teilnahme am Einzugsverfahren: " . request()->input('einzugsermaechtigung') . "</b><br>Einzugsart: " . request()->input('einzugsart') . "<br>";
                    echo "Kontoinhaber: " . request()->input('konto_inhaber_autoeinzug') . "<br>";
                    echo "Kontonummer: " . request()->input('konto_nummer_autoeinzug') . "<br>";
                    echo "BLZ: " . request()->input('blz_autoeinzug') . "<br>";
                    echo "Geldinstitut: " . request()->input('geld_institut') . "<br>";
                    $form->hidden_feld('mietvertrag_id', request()->input('mietvertrag_id'));
                    $form->hidden_feld('einzugsermeachtigung', request()->input('einzugsermaechtigung'));
                    $form->hidden_feld('einzugsart', request()->input('einzugsart'));
                    $form->hidden_feld('konto_inhaber_autoeinzug', request()->input('konto_inhaber_autoeinzug'));
                    $form->hidden_feld('konto_nummer_autoeinzug', request()->input('konto_nummer_autoeinzug'));
                    $form->hidden_feld('blz_autoeinzug', request()->input('blz_autoeinzug'));
                    $form->hidden_feld('geld_institut', request()->input('geld_institut'));
                    for ($a = 0; $a < count(request()->input('deaktiviere_dat')); $a++) {
                        $form->hidden_feld('deaktiviere_dat[]', request()->input('deaktiviere_dat') [$a]);
                    }
                    $form->hidden_feld('mietvertrag_raus', 'ls_bearbeitet_speichern');
                    $form->send_button('btn_ls_speichern_bb', 'Speichern');
                }
            }
        }
        $form->ende_formular();
        break;

    case "ls_neu_speichern" :
        $mv_info = new mietvertraege ();
        $mv_info->teilnahme_einzugsverfahren_eingeben(request()->input('mietvertrag_id'), request()->input('konto_inhaber_autoeinzug'), request()->input('konto_nummer_autoeinzug'), request()->input('blz_autoeinzug'), request()->input('geld_institut'), request()->input('einzugsart'), 'JA');
        weiterleiten_in_sec(route('web::mietvertraege::legacy', ['mietvertrag_raus' => 'ls_teilnehmer'], false), 2);
        break;

    case "ls_bearbeitet_speichern";
        $mv_info = new mietvertraege ();
        $mv_info->deaktiviere_detail_dats(request()->input('deaktiviere_dat'));
        $ja_nein = request()->input('einzugsermeachtigung');
        $mv_info->teilnahme_einzugsverfahren_eingeben(request()->input('mietvertrag_id'), request()->input('konto_inhaber_autoeinzug'), request()->input('konto_nummer_autoeinzug'), request()->input('blz_autoeinzug'), request()->input('geld_institut'), request()->input('einzugsart'), $ja_nein);
        weiterleiten_in_sec(route('web::mietvertraege::legacy', ['mietvertrag_raus' => 'ls_teilnehmer_neu', 'mietvertrag_id' => request()->input('mietvertrag_id')], false), 2);
        break;

    case "mietvertrag_neu" :
        $form = new mietkonto ();
        $form->erstelle_formular("Neuen Mietvertrag erstellen", NULL);
        iframe_start();
        $mv_info = new mietvertraege ();
        $mv_info->neuer_mv_form();
        iframe_end();
        $form->ende_formular();
        break;

    case "mv_pruefen" :
        $form = new mietkonto ();
        $form->erstelle_formular("Neuen Mietvertrag prüfen", NULL);
        /* Ob Mieter ausgewählt wurden */
        if (is_array(request()->input('mieter_liste'))) {
        } else {
            $error = 'Keine Mieter im Vertrag<br>';
        }
        /* Einzugsdatum */
        if (!check_datum(request()->input('datum_einzug'))) {
            $error .= 'Einzugsdatum prüfen<br>';
        }
        /* Auszugsdatum */
        if (request()->has('datum_auszug')) {
            if (!check_datum(request()->input('datum_auszug'))) {
                $error .= 'Auszugsdatum prüfen<br>';
            }
        } else {
            request()->request->add(['datum_auszug' => '0000-00-00']);
            //TODO: check parameter add
        }
        if (request()->has('miete_kalt')) {
            if (is_numeric(request()->input('miete_kalt'))) {
                $error .= 'Kaltmiete Betrag fehlerhaft<br>';
            }
        } else {
            $error .= 'Keine Kaltmiete eingegeben<br>';
        }
        if (request()->has('sollkaution')) {
            if (is_numeric(request()->input('sollkaution'))) {
                $error .= 'Sollkaution Betrag fehlerhaft<br>';
            }
        } else {
            $error .= 'Keine Sollkaution eingegeben<br>';
        }
        if (isset ($error)) {
            echo $error;
        } else {
            echo "<p><h1>VERTRAGSDATEN:</h1><br>";
            $einheit_kurzname = einheit_kurzname(request()->input('einheit_id'));
            $haus_id = haus_id(request()->input('einheit_id'));
            $anschrift = haus_strasse_nr($haus_id);
            echo "<b>Einheit:</b> $einheit_kurzname<br>$anschrift<br>";
            $mv_info = new mietvertraege ();
            echo "<hr><b>Mieter:</b><br>";
            $mv_info->mv_personen_anzeigen_form(request()->input('mieter_liste'));
            echo "<hr>Einzug: " . request()->input('datum_einzug') . "<br>";
            if (request()->input('datum_auszug') == '0000-00-00') {
                echo "Auszug: unbefristet<br>";
            } else {
                echo "Auszug: " . request()->input('datum_auszug') . "<br>";
            }
            echo "Miete kalt: " . request()->input('miete_kalt') . " €<br>";
            if (request()->has('sollkaution')) {
                echo "Sollkaution: " . request()->input('sollkaution') . " €<br>";
            }
            if (request()->has('nebenkosten')) {
                echo "Nebenkosten Vorauszahlung: " . request()->input('nebenkosten') . " €<br>";
            }
            if (request()->has('heizkosten')) {
                echo "Heizkosten Vorauszahlung: " . request()->input('heizkosten') . " €<br>";
            }
            $form->hidden_feld('einheit_id', request()->input('einheit_id'));
            $form->hidden_feld('einheit_name', $einheit_kurzname);
            $form->hidden_feld('datum_einzug', request()->input('datum_einzug'));
            $form->hidden_feld('datum_auszug', request()->input('datum_auszug'));
            for ($a = 0; $a < count(request()->input('mieter_liste')); $a++) {
                $person_id = request()->input('mieter_liste') [$a];
                $form->hidden_feld('mieter_liste[]', $person_id);
            }
            $form->hidden_feld('sollkaution', request()->input('sollkaution'));
            $form->hidden_feld('miete_kalt', request()->input('miete_kalt'));
            $form->hidden_feld('heizkosten', request()->input('heizkosten'));
            $form->hidden_feld('nebenkosten', request()->input('nebenkosten'));

            $form->hidden_feld('mietvertrag_raus', 'mv_speichern');
            $form->send_button('btn_mv_erstellen', 'Mietvertrag speichern');

            echo "</p>";
        }

        $form->ende_formular();
        break;

    case "mv_speichern" :
        $form = new mietkonto ();
        $form->erstelle_formular("Mietvertrag speichern", NULL);
        iframe_start();
        $zugewiesene_vertrags_id = mietvertrag_anlegen(request()->input('datum_einzug'), request()->input('datum_auszug'), request()->input('einheit_id'));
        $anzahl_partner = count(request()->input('mieter_liste'));
        for ($a = 0; $a < $anzahl_partner; $a++) {
            $person_id = request()->input('mieter_liste') [$a];
            person_zu_mietvertrag($person_id, $zugewiesene_vertrags_id);
        }

        hinweis_ausgeben("Mietvertrag wurde erstellt!");

        $mv_info = new mietvertraege ();
        $k = new kautionen ();
        $mv_info->mieten_speichern($zugewiesene_vertrags_id, request()->input('datum_einzug'), request()->input('datum_auszug'), 'Miete kalt', request()->input('miete_kalt'), 0);

        if (request()->has('sollkaution')) {
            $k->feld_wert_speichern($zugewiesene_vertrags_id, 'SOLL', request()->input('sollkaution'));
        }

        if (request()->has('heizkosten')) {
            $mv_info->mieten_speichern($zugewiesene_vertrags_id, request()->input('datum_einzug'), request()->input('datum_auszug'), 'Heizkosten Vorauszahlung', request()->input('heizkosten'), 0);
        }

        if (request()->has('nebenkosten')) {
            $mv_info->mieten_speichern($zugewiesene_vertrags_id, request()->input('datum_einzug'), request()->input('datum_auszug'), 'Nebenkosten Vorauszahlung', request()->input('nebenkosten'), 0);
        }

        weiterleiten_in_sec(route('web::uebersicht::legacy', ['anzeigen' => 'einheit', 'einheit_id' => request()->input('einheit_id')], false), "1");
        iframe_end();
        $form->ende_formular();
        break;

    case "mv_geaendert_speichern" :
        $form = new mietkonto ();
        $mv_info = new mietvertraege ();
        $form->erstelle_formular("Mietvertragsänderungen speichern", NULL);
        $mv_info->mv_aenderungen_speichern(request()->input('mietvertrag_dat'), request()->input('mietvertrag_id'), request()->input('datum_auszug'), request()->input('datum_einzug'), request()->input('einheit_id'), request()->input('mieter_liste'));
        $form->ende_formular();
        break;

    case "mietvertrag_beenden" :
        $form = new formular ();
        $form->erstelle_formular("Mietvertrag beenden", NULL);
        $m = new mietvertraege ();
        $m->mietvertrag_beenden_form(request()->input('mietvertrag_id'));
        $form->ende_formular();
        break;

    case "mietvertrag_beenden_gesendet" :
        $form = new formular ();
        $form->erstelle_formular("Mietvertrag beenden", NULL);
        $m = new mietvertraege ();
        $mietvertrag_bis = date_german2mysql(request()->input('mietvertrag_bis'));
        if (strpos($mietvertrag_bis, '-00') || strpos($mietvertrag_bis, '0000-')
            || new DateTime(request()->input('mietvertrag_von')) > new DateTime(request()->input('mietvertrag_bis'))
            || !empty(DateTime::getLastErrors()['warning_count'])
        ) {
            hinweis_ausgeben("Bitte Mietvertragsende überprüfen.");
            weiterleiten_in_sec($_SERVER['HTTP_REFERER'], 5);
            $form->ende_formular();
            return;
        }
        $m->mietvertrag_beenden_db(request()->input('mietvertrag_dat'), $mietvertrag_bis);
        hinweis_ausgeben("Mietvertrag von " . request()->input('einheit_kurzname') . " wird zum " . request()->input('mietvertrag_bis') . " beendet.<br>");
        $m->mietdefinition_beenden(request()->input('mietvertrag_id'), $mietvertrag_bis);
        hinweis_ausgeben("Unbefristete Mietdefinitionen werden zum " . request()->input('mietvertrag_bis') . " beendet.");
        $verzugsanschrift = request()->input('verzugsanschrift');

        /* Verzugsanschrift */
        if ($verzugsanschrift) {
            $d = new detail ();
            $d->detail_speichern_2('Mietvertrag', request()->input('mietvertrag_id'), 'Verzugsanschrift', $verzugsanschrift, Auth::user()->email);
        }
        /* Lastschrift beenden */
        $s = new sepa();
        if ($s->mandat_beenden(request()->input('mietvertrag_id'), request()->input('mietvertrag_bis'))) {
            hinweis_ausgeben("Teilnahme am SEPA-Lastschriftverfahren wurde beendet");
        }

        $einheit_id = request()->input('einheit_id');
        weiterleiten_in_sec(route('web::uebersicht::legacy', ['anzeigen' => 'einheit', 'einheit_id' => $einheit_id], false), 2);
        $form->ende_formular();
        break;

    /* aktuelle Mietverträge */
    case "mahnliste" :
        set_time_limit(240);
        $f = new formular ();
        $f->fieldset("Mahnliste aktuell", 'mahnliste');
        $bg = new berlussimo_global ();
        $bg->objekt_auswahl_liste();
        if (session()->has('objekt_id')) {
            $ma = new mahnungen ();
            if (!request()->exists('pdf')) {
                $obj_id = session()->get('objekt_id');
                $link_pdf = "<a href='" . route('web::mietvertraege::legacy', ['mietvertrag_raus' => 'mahnliste', 'objekt_id' => $obj_id, 'pdf'], false) . "'>Als PDF anzeigen</a>";
                echo $link_pdf;
                $ma->finde_schuldner('aktuelle');
            } else {
                $ma->finde_schuldner_pdf('aktuelle');
            }
        }
        $f->fieldset_ende();
        break;

    case "mahnliste_alle" :
        $f = new formular ();
        $f->fieldset("Mahnliste alle", 'mahnliste');
        $bg = new berlussimo_global ();
        $bg->objekt_auswahl_liste();
        if (session()->has('objekt_id')) {
            $ma = new mahnungen ();
            if (!request()->exists('pdf')) {
                $obj_id = session()->get('objekt_id');
                $link_pdf = "<a href='" . route('web::mietvertraege::legacy', ['mietvertrag_raus' => 'mahnliste_alle', 'objekt_id' => $obj_id, 'pdf'], false) . "'>Als PDF anzeigen</a>";
                echo $link_pdf;
                $ma->finde_schuldner('');
            } else {
                $ma->finde_schuldner_pdf('');
            }
        }
        $f->fieldset_ende();
        break;

    case "mahnliste_ausgezogene" :
        $f = new formular ();
        $f->fieldset("Mahnliste ex. Mieter", 'mahnliste');
        $bg = new berlussimo_global ();
        $bg->objekt_auswahl_liste();
        if (session()->has('objekt_id')) {
            $ma = new mahnungen ();
            if (!request()->exists('pdf')) {
                $obj_id = session()->get('objekt_id');
                $link_pdf = "<a href='" . route('web::mietvertraege::legacy', ['mietvertrag_raus' => 'mahnliste_ausgezogene', 'objekt_id' => $obj_id, 'pdf']) . "'>Als PDF anzeigen</a>";
                echo $link_pdf;
                $ma->finde_schuldner('ausgezogene');
            } else {
                $ma->finde_schuldner_pdf('ausgezogene');
            }
        }
        $f->fieldset_ende();
        break;

    case "guthaben_liste" :
        $f = new formular ();
        $f->fieldset("Guthaben aller Mieter", 'guthabenliste');
        $bg = new berlussimo_global ();
        $bg->objekt_auswahl_liste();
        if (session()->has('objekt_id')) {
            $ma = new mahnungen ();
            $ma->finde_guthaben_mvs();
        }
        $f->fieldset_ende();
        break;

    case "zahlungserinnerung" :
        if (request()->has('mietvertrag_id') && empty (request()->input('submit'))) {
            $mv_id = request()->input('mietvertrag_id');
            $f = new formular ();
            $f->erstelle_formular("Zahlungserinnerung für Mietvertrag $mv_id", '');
            // $f->fieldset("Zahlungserinnerung für Mietvertrag $mv_id", 'zahlungserinnerung');
            $datum_feld = 'document.getElementById("datum_zahlungsfrist").value';
            $js_datum = "onchange='check_datum($datum_feld)'";
            $f->text_feld('Datum Zahlungsfrist', 'datum_zahlungsfrist', '', '10', 'datum_zahlungsfrist', $js_datum);
            $g = new geldkonto_info ();
            $g->geld_konto_ermitteln('Mietvertrag', $mv_id);
            $f->send_button("submit", "Schreiben erstellen");
            $ma = new mahnungen ();
            // $f->fieldset_ende();
            $f->ende_formular();
        }
        if (request()->has('submit')) {
            $mv_id = request()->input('mietvertrag_id');
            $fristdatum = request()->input('datum_zahlungsfrist');
            $geldkonto_id = request()->input('geld_konto');
            $ma = new mahnungen ();
            $ma->zahlungserinnerung_pdf($mv_id, $fristdatum, $geldkonto_id);
        }
        break;

    case "mahnung" :
        if (request()->has('mietvertrag_id') && empty (request()->input('submit'))) {
            $mv_id = request()->input('mietvertrag_id');
            $f = new formular ();
            $f->erstelle_formular("Mahnung für Mietvertrag $mv_id", '');
            $datum_feld = 'document.getElementById("datum_zahlungsfrist").value';
            $js_datum = "onchange='check_datum($datum_feld)'";
            $f->text_feld('Datum Zahlungsfrist', 'datum_zahlungsfrist', '', '10', 'datum_zahlungsfrist', $js_datum);
            $f->text_feld('Mahngebühr', 'mahngebuehr', '', '10', 'mahngebuehr', '');
            $g = new geldkonto_info ();
            $g->geld_konto_ermitteln('Mietvertrag', $mv_id);
            $f->send_button("submit", "Schreiben erstellen");
            $ma = new mahnungen ();
            $f->ende_formular();
        }
        if (request()->has('submit')) {
            $mv_id = request()->input('mietvertrag_id');
            $fristdatum = request()->input('datum_zahlungsfrist');
            $geldkonto_id = request()->input('geld_konto');
            $mahngebuehr = request()->input('mahngebuehr');
            $ma = new mahnungen ();
            $ma->mahnung_pdf($mv_id, $fristdatum, $geldkonto_id, $mahngebuehr);
        }
        break;

    case "mietvertrag_aendern" :
        $form = new mietkonto ();
        $form->erstelle_formular("Mietvertrag ändern", NULL);
        if (request()->has('mietvertrag_id')) {
            $mv_info = new mietvertraege ();
            $mv_info->mv_aendern_formular(request()->input('mietvertrag_id'));
        } else {
            fehlermeldung_ausgeben("Mietvertrag zum ändern auswählen");
            weiterleiten_in_sec(route('web::mietvertraege::legacy', ['mietvertrag_raus' => 'mietvertrag_kurz'], false), '2');
        }
        $form->ende_formular();
        break;

    case "mv_aenderung_pruefen" :
        $form = new mietkonto ();
        $form->erstelle_formular("Mietvertrag prüfen/ändern", NULL);

        /* Ob Mieter ausgewählt wurden */
        if (!is_array(request()->input('mieter_liste'))) {
            $error = 'Keine Mieter im Vertrag<br>';
        }
        /* Einzugsdatum */
        if (!check_datum(request()->input('datum_einzug'))) {
            $error .= 'Einzugsdatum prüfen<br>';
        } else {
            // echo "Einzugsdatum OK";
        }
        /* Auszugsdatum */
        if (request()->has('datum_auszug')) {
            if (!check_datum(request()->input('datum_auszug'))) {
                $error .= 'Auszugsdatum prüfen<br>';
            }
        } else {
            request()->request->add(['datum_auszug' => '0000-00-00']);
            //TODO check request add
        }

        if (isset ($error)) {
            echo $error;
        } else {
            echo "<p><h5>Geänderte Vertragsdaten:</h5><br>";
            $einheit_kurzname = einheit_kurzname(request()->input('einheit_id'));
            $haus_id = haus_id(request()->input('einheit_id'));
            $anschrift = haus_strasse_nr($haus_id);
            echo "<b>Einheit:</b> $einheit_kurzname<br>$anschrift<br>";
            $mv_info = new mietvertraege ();
            echo "<hr><b>Mieter:</b><br>";
            $mv_info->mv_personen_anzeigen_form(request()->input('mieter_liste'));
            echo "<hr>Einzug: " . request()->input('datum_einzug') . "<br>";
            if (request()->input('datum_auszug') == '0000-00-00') {
                echo "Auszug: unbefristet<br>";
            } else {
                echo "Auszug: " . request()->input('datum_auszug') . "<br>";
            }

            $form->hidden_feld('einheit_id', request()->input('einheit_id'));
            $form->hidden_feld('mietvertrag_id', request()->input('mietvertrag_id'));
            $form->hidden_feld('mietvertrag_dat', request()->input('mietvertrag_dat'));
            $form->hidden_feld('datum_einzug', request()->input('datum_einzug'));
            $form->hidden_feld('datum_auszug', request()->input('datum_auszug'));

            for ($a = 0; $a < count(request()->input('mieter_liste')); $a++) {
                $person_id = request()->input('mieter_liste')[$a];
                $form->hidden_feld('mieter_liste[]', $person_id);
            }
            $form->hidden_feld('mietvertrag_raus', 'mv_geaendert_speichern');
            $form->send_button('btn_mv_aendern', 'Änderungen speichern');
        }
        $form->ende_formular();
        break;

    case "letzte_auszuege" :
        $f = new formular ();
        $link = route('web::mietvertraege::legacy', ['mietvertrag_raus' => 'letzte_auszuege'], false);
        $b = new berlussimo_global ();
        $b->objekt_auswahl_liste();
        $m = new mietvertraege ();
        $objekt_id = session()->get('objekt_id');
        $jahr = request()->input('jahr');
        $monat = request()->input('monat');
        $f->fieldset("Letzte Auszüge", 'l_auszuege');
        if (!empty ($objekt_id)) {
            if (empty ($jahr)) {
                $jahr = date("Y");
            }

            $b->monate_jahres_links($jahr, $link);
            $m->ausgezogene_mieter_anzeigen($objekt_id, $jahr, $monat);
        }
        $f->fieldset_ende();
        break;

    case "letzte_einzuege" :
        $f = new formular ();
        $link = route('web::mietvertraege::legacy', ['mietvertrag_raus' => 'letzte_einzuege'], false);
        $b = new berlussimo_global ();
        $b->objekt_auswahl_liste();
        $m = new mietvertraege ();
        $objekt_id = session()->get('objekt_id');
        $jahr = request()->input('jahr');
        $monat = request()->input('monat');
        $f->fieldset("Letzte Einzüge", 'l_einzuege');
        if (!empty ($objekt_id)) {
            if (empty ($jahr)) {
                $jahr = date("Y");
            }

            $b->monate_jahres_links($jahr, $link);
            $m->eingezogene_mieter_anzeigen($objekt_id, $jahr, $monat);
        }
        $f->fieldset_ende();
        break;

    case "alle_letzten_auszuege" :
        $f = new formular ();
        $b = new berlussimo_global ();
        $link = route('web::mietvertraege::legacy', ['mietvertrag_raus' => 'alle_letzten_auszuege'], false);
        $m = new mietvertraege ();
        $jahr = request()->input('jahr');
        $monat = request()->input('monat');
        $f->fieldset("Alle Auszüge", 'l_auszuege');
        if (empty ($jahr)) {
            $jahr = date("Y");
        }

        $b->monate_jahres_links($jahr, $link);
        $m->alle_ausgezogene_mieter_anzeigen($jahr, $monat);
        $f->fieldset_ende();
        break;

    case "alle_letzten_einzuege" :
        $f = new formular ();
        $b = new berlussimo_global ();
        $link = route('web::mietvertraege::legacy', ['mietvertrag_raus' => 'alle_letzten_einzuege'], false);
        $m = new mietvertraege ();
        $jahr = request()->input('jahr');
        $monat = request()->input('monat');
        $f->fieldset("Alle Einzüge", 'l_einzuege');
        if (empty ($jahr)) {
            $jahr = date("Y");
        }

        $b->monate_jahres_links($jahr, $link);
        $m->alle_eingezogene_mieter_anzeigen($jahr, $monat);
        $f->fieldset_ende();
        break;

    case "abnahmeprotokoll" :
        if (request()->has('mv_id')) {
            $pdf = new Cezpdf ('a4', 'portrait');
            $bpdf = new b_pdf ();
            $bpdf->b_header($pdf, 'Partner', session()->get('partner_id'), 'portrait', 'Helvetica.afm', 6);

            $mvv = new mietvertraege ();
            $mvv->get_mietvertrag_infos_aktuell(request()->input('mv_id'));

            if (request()->exists('einzug')) {
                $bpdf->pdf_abnahmeprotokoll($pdf, request()->input('mv_id'), 'einzug'); // EINZUG
                $dateiname = $mvv->einheit_kurzname . "_Einzug_Protokoll.pdf";
            } else {
                $bpdf->pdf_abnahmeprotokoll($pdf, request()->input('mv_id'), null); // AUSZUG
                $dateiname = $mvv->einheit_kurzname . "_Auszug_Protokoll.pdf";
            }

            if (request()->exists('einzug')) {
                $pdf->ezNewPage();
                $bpdf->pdf_heizungabnahmeprotokoll($pdf, request()->input('mv_id'), 'einzug');

                $pdf->ezNewPage();
                $bpdf->pdf_einauszugsbestaetigung($pdf, request()->input('mv_id'), 0);
            } else {
                $pdf->ezNewPage();
                $bpdf->pdf_heizungabnahmeprotokoll($pdf, request()->input('mv_id'));

                $pdf->ezNewPage();
                $bpdf->pdf_einauszugsbestaetigung($pdf, request()->input('mv_id'), 1);
            }

            ob_end_clean();
            $datum_h = date("Y-m-d");
            $pdf_opt ['Content-Disposition'] = $datum_h . "_" . $dateiname;
            $pdf->ezStream($pdf_opt);
        } else {
            fehlermeldung_ausgeben("Mietvertrag wählen!");
        }

        break;

    case "alle_letzten_auszuege_pdf" :
        $f = new formular();
        $m = new mietvertraege();
        $jahr = request()->input('jahr');
        $monat = request()->input('monat');
        if (empty ($jahr)) {
            $jahr = date("Y");
        }
        $m->alle_ausgezogenen_pdf($jahr, $monat);
        break;

    case "alle_letzten_einzuege_pdf" :
        $f = new formular ();
        $m = new mietvertraege ();
        $jahr = request()->input('jahr');
        $monat = request()->input('monat');
        if (empty ($jahr)) {
            $jahr = date("Y");
        }
        $m->alle_eingezogenen_pdf($jahr, $monat);
        break;

    case "saldenliste" :
        $form = new mietkonto ();
        $form->erstelle_formular("Saldenliste", NULL);
        $mv_info = new mietvertraege ();
        $monat = request()->input('monat');
        $jahr = request()->input('jahr');
        if (!$monat) {
            $monat = date("m");
        }
        if (!$jahr) {
            $jahr = date("Y");
        }
        $mv_info->saldenliste_mv($monat, $jahr);
        $form->ende_formular();
        break;

    case "nebenkosten_ok" :
        $form = new mietkonto ();
        $form->erstelle_formular("Nebenkosten", NULL);
        $mv_info = new mietvertraege ();
        $monat = request()->input('monat');
        $jahr = request()->input('jahr');
        if (!$monat) {
            $monat = date("m");
        }
        if (!$jahr) {
            $jahr = date("Y");
        }
        $mv_info->nebenkosten($monat, $jahr);
        $form->ende_formular();
        break;

    case "nebenkosten" :

        $form = new mietkonto ();
        $form->erstelle_formular("Nebenkosten", NULL);

        $jahr = request()->input('jahr');
        if (empty ($jahr)) {
            $jahr = date("Y");
        } else {
            if (strlen($jahr) < 4) {
                $jahr = date("Y");
            }
        }

        $bg = new berlussimo_global ();
        $link = route('web::mietvertraege::legacy', ['mietvertrag_raus' => 'nebenkosten'], false);
        $bg->objekt_auswahl_liste();
        $bg->jahres_links($jahr, $link);
        if (session()->has('objekt_id')) {
            $objekt_id = session()->get('objekt_id');
        }
        if (!session()->has('objekt_id')) {
            return;
        }

        $link_pdf = "<a href='" . route('web::mietvertraege::legacy', ['mietvertrag_raus' => 'nebenkosten_pdf', 'jahr' => $jahr]) . "'><b>PDF-Datei</b></a>";
        echo '<hr>' . $link_pdf . '<hr>';

        $mv_info = new mietvertraege ();
        $mv_info->nebenkosten(session()->get('objekt_id'), $jahr);
        $form->ende_formular();
        break;

    case "nebenkosten_pdf" :
        $jahr = request()->input('jahr');
        if (empty ($jahr)) {
            $jahr = date("Y");
        } else {
            if (strlen($jahr) < 4) {
                $jahr = date("Y");
            }
        }

        if (session()->has('objekt_id')) {
            $objekt_id = session()->get('objekt_id');
        } else {
            echo 'Bitte wählen Sie ein Objekt.';
            return;
        }

        $mv_info = new mietvertraege ();
        $mv_info->nebenkosten_pdf(session()->get('objekt_id'), $jahr);
        break;

    case "nebenkosten_pdf_zs" :
        $jahr = request()->input('jahr');
        if (empty ($jahr)) {
            $jahr = date("Y");
        } else {
            if (strlen($jahr) < 4) {
                $jahr = date("Y");
            }
        }

        if (session()->has('objekt_id')) {
            $objekt_id = session()->get('objekt_id');
        } else {
            echo 'Bitte wählen Sie ein Objekt.';
            return;
        }

        $mv_info = new mietvertraege ();
        $mv_info->nebenkosten_pdf_zs_ant(session()->get('objekt_id'), $jahr);
        break;

    case "nebenkosten_pdf_OK" :
        $mv_info = new mietvertraege ();
        $monat = request()->input('monat');
        $jahr = request()->input('jahr');
        if (!$monat) {
            $monat = date("m");
        }
        if (!$jahr) {
            $jahr = date("Y");
        }
        $mv_info->nebenkosten_pdf($monat, $jahr);
        break;

    case "saldenliste_pdf" :
        $mv_info = new mietvertraege ();
        $monat = request()->input('monat');
        $jahr = request()->input('jahr');
        if (!$monat) {
            $monat = date("m");
        }
        if (!$jahr) {
            $jahr = date("Y");
        }
        $mv_info->saldenliste_mv_pdf($monat, $jahr);
        break;

    case "mv_loeschen" :
        if (request()->has('mv_id')) {
            $mv_id = request()->input('mv_id');
            $mv = new mietvertraege ();
            $mv->form_mietvertrag_loeschen($mv_id);
        } else {
            echo "Mietvertrag wählen!";
        }
        break;

    case "erinnern_mehrere" :
        $mahnliste = request()->input('mahnliste');
        $fristdatum = request()->input('datum');
        $geldkonto_id = request()->input('geld_konto');
        $ma = new mahnungen ();
        $ma->zahlungserinnerung_pdf_mehrere($mahnliste, $fristdatum, $geldkonto_id);
        break;

    case "mahnen_mehrere" :
        $mahnliste = request()->input('mahnliste');
        $fristdatum = request()->input('datum');
        $geldkonto_id = request()->input('geld_konto');
        $mahngebuehr = request()->input('mahngebuehr');
        $ma = new mahnungen ();
        $ma->mahnung_pdf_mehrere($mahnliste, $fristdatum, $geldkonto_id, $mahngebuehr);
        break;
} // end switch
function objekt_auswahl_liste($link)
{
    if (request()->has('objekt_id')) {
        session()->put('objekt_id', request()->input('objekt_id'));
    }

    echo "<div class=\"objekt_auswahl\">";
    $mieten = new mietkonto ();
    $mieten->erstelle_formular("Objekt auswählen...", NULL);

    if (session()->has('objekt_id')) {
        $objekt_kurzname = new objekt ();
        $objekt_kurzname->get_objekt_name(session()->get('objekt_id'));
        echo "<p>&nbsp;<b>Ausgewähltes Objekt</b> -> $objekt_kurzname->objekt_name ->";
    } else {
        echo "<p>&nbsp;<b>Objekt auswählen</b>";
    }

    $objekte = new objekt ();
    $objekte_arr = $objekte->liste_aller_objekte();
    $anzahl_objekte = count($objekte_arr);

    for ($i = 0; $i <= $anzahl_objekte; $i++) {
        echo "<a class=\"objekt_auswahl_buchung\" href=\"$link&objekt_id=" . $objekte_arr [$i] ['OBJEKT_ID'] . "\">" . $objekte_arr [$i] ['OBJEKT_KURZNAME'] . "</a>&nbsp;";
        echo "</div>";
    }
}

function leerstand_finden($objekt_id)
{
    $result = DB::select("SELECT OBJEKT_KURZNAME, EINHEIT_ID, EINHEIT_KURZNAME, HAUS_STRASSE, HAUS_NUMMER
FROM `EINHEIT`
RIGHT JOIN (
HAUS, OBJEKT
) ON ( EINHEIT.HAUS_ID = HAUS.HAUS_ID && HAUS.OBJEKT_ID = OBJEKT.OBJEKT_ID && OBJEKT.OBJEKT_ID='$objekt_id' )
WHERE EINHEIT_ID NOT
IN (

SELECT EINHEIT_ID
FROM MIETVERTRAG
WHERE MIETVERTRAG_AKTUELL = '1' && ( MIETVERTRAG_BIS > CURdate( )
OR MIETVERTRAG_BIS = '0000-00-00' )
)
ORDER BY EINHEIT_KURZNAME ASC");
    return $result;
}

function dropdown_leerstaende($objekt_id, $name, $label)
{
    $leerstand = leerstand_finden($objekt_id);
    echo "<select name=\"$name\" id=\"$name\">";
    for ($a = 0; $a < count($leerstand); $a++) {
        $einheit_id = $leerstand [$a] ['EINHEIT_ID'];
        $einheit_kurzname = $leerstand [$a] ['OBJEKT_KURZNAME'];
        echo "<option value=\"$einheit_id\">$einheit_kurzname</option>";
    }
    echo "</select><label for=\"$name\">$label</label>";
}

function mietvertrag_beenden_form($mietvertrag_id)
{
    $m = new mietvertraege ();
    $m->mietvertrag_beenden_form($mietvertrag_id);
}

function mietvertrag_beenden($mietvertrag_dat, $mietvertrag_bis)
{
    $mietvertrag_bis = date_german2mysql($mietvertrag_bis);
    $akt_einheit_id = einheit_id_by_mietvertrag($mietvertrag_dat);
    $dat_alt = $mietvertrag_dat;
    $db_abfrage = "UPDATE MIETVERTRAG SET MIETVERTRAG_AKTUELL='0' where MIETVERTRAG_DAT='$mietvertrag_dat'";
    DB::update($db_abfrage); // aktuell auf 0 gesetzt

    $db_abfrage = "SELECT MIETVERTRAG_ID, MIETVERTRAG_VON, EINHEIT_ID FROM MIETVERTRAG where MIETVERTRAG_DAT='$mietvertrag_dat' LIMIT 0,1";
    $result = DB::select($db_abfrage);
    foreach ($result as $row) {
        DB::insert("INSERT INTO MIETVERTRAG (`MIETVERTRAG_DAT`, `MIETVERTRAG_ID`, `MIETVERTRAG_VON`, `MIETVERTRAG_BIS`, `EINHEIT_ID`, `MIETVERTRAG_AKTUELL`) VALUES (NULL, '$row[MIETVERTRAG_ID]', '$row[MIETVERTRAG_VON]', '$mietvertrag_bis', '$row[EINHEIT_ID]', '1')");
    } // while end
    // protokollieren
    $result = DB::select("SELECT MIETVERTRAG_DAT FROM MIETVERTRAG where MIETVERTRAG_BIS='$mietvertrag_bis' && MIETVERTRAG_AKTUELL='1' ORDER BY MIETVERTRAG_DAT DESC");
    foreach ($result as $row) {
        $dat_neu = $row['MIETVERTRAG_DAT'];
        protokollieren('MIETVERTRAG', $dat_neu, $dat_alt);
    }
    weiterleiten(route('web::uebersicht::legacy', ['anzeigen' => 'einheit', 'einheit_id' => $akt_einheit_id], false));
}

function mietvertrag_aktualisieren($mietvertrag_dat, $mietvertrag_bis, $mietvertrag_von)
{
    $mietvertrag_bis = date_german2mysql($mietvertrag_bis);
    $mietvertrag_von = date_german2mysql($mietvertrag_von);
    $dat_alt = $mietvertrag_dat;
    $db_abfrage = "UPDATE MIETVERTRAG SET MIETVERTRAG_AKTUELL='0' where MIETVERTRAG_DAT='$mietvertrag_dat'";
    DB::update($db_abfrage); // aktuell auf 0 gesetzt

    $mietvertrag_id_alt = mietvertrag_id_by_dat($mietvertrag_dat);
    DB::update("UPDATE PERSON_MIETVERTRAG SET PERSON_MIETVERTRAG_AKTUELL='0' where PERSON_MIETVERTRAG_MIETVERTRAG_ID='$mietvertrag_id_alt'");

    $db_abfrage = "SELECT MIETVERTRAG_ID, MIETVERTRAG_VON, EINHEIT_ID FROM MIETVERTRAG where MIETVERTRAG_DAT='$mietvertrag_dat' LIMIT 0,1";
    $result = DB::select($db_abfrage);
    foreach ($result as $row) {
        DB::insert("INSERT INTO MIETVERTRAG (`MIETVERTRAG_DAT`, `MIETVERTRAG_ID`, `MIETVERTRAG_VON`, `MIETVERTRAG_BIS`, `EINHEIT_ID`, `MIETVERTRAG_AKTUELL`) VALUES (NULL, '$mietvertrag_id_alt', '$mietvertrag_von', '$mietvertrag_bis', '$row[EINHEIT_ID]', '1')");
    } // while end
    // protokollieren
    $db_abfrage = "SELECT MIETVERTRAG_DAT FROM MIETVERTRAG where MIETVERTRAG_VON='$mietvertrag_von' && MIETVERTRAG_BIS='$mietvertrag_bis' && MIETVERTRAG_AKTUELL='1' ORDER BY MIETVERTRAG_DAT DESC";
    $result = DB::select($db_abfrage);
    foreach ($result as $row) {
        $dat_neu = $row['MIETVERTRAG_DAT'];
        protokollieren('MIETVERTRAG', $dat_neu, $dat_alt);
    }

    $zugewiesene_vetrags_id = mietvertrag_by_einheit(request()->input('einheit_id'));
    $anzahl_partner = count(request()->input('PERSON_ID'));
    for ($a = 0; $a < $anzahl_partner; $a++) {
        person_zu_mietvertrag(request()->input('PERSON_ID')[$a], $zugewiesene_vetrags_id);
    }
}

function mietvertrag_kurz($einheit_id)
{
    if (empty ($einheit_id)) {
        $db_abfrage = "SELECT MIETVERTRAG.MIETVERTRAG_ID, MIETVERTRAG.MIETVERTRAG_VON, MIETVERTRAG.MIETVERTRAG_BIS, MIETVERTRAG.EINHEIT_ID, EINHEIT.EINHEIT_KURZNAME FROM MIETVERTRAG JOIN(EINHEIT) ON (EINHEIT.EINHEIT_ID = MIETVERTRAG.EINHEIT_ID) WHERE MIETVERTRAG_AKTUELL='1' && EINHEIT.EINHEIT_AKTUELL='1' ORDER BY EINHEIT.EINHEIT_KURZNAME,MIETVERTRAG.MIETVERTRAG_VON  ASC";
    } else {
        $db_abfrage = "SELECT MIETVERTRAG_ID, MIETVERTRAG_VON, MIETVERTRAG_BIS, EINHEIT_ID FROM MIETVERTRAG where EINHEIT_ID='$einheit_id' && MIETVERTRAG_AKTUELL='1' ORDER BY MIETVERTRAG_VON DESC";
    }

    $result = DB::select($db_abfrage);

    if (empty($result)) {
        echo "<h5><b>Keine Mietverträge zur Einheit $einheit_id vorhanden.</b></h5>";
    } else {
        echo "<table width=100%>\n";
        echo "<tr class=\"feldernamen\"><td colspan=5>Alle Mietverträge</td></tr>\n";
        echo "<tr class=\"feldernamen\"><td width=100>EINHEIT</td><td width=300>MIETER</td><td width=85>VON</td><td width=80>BIS</td><td>Optionen</td></tr>\n";
        echo "</table>\n";
        iframe_start();
        echo "<table width=100%>\n";
        $counter = 0;
        foreach ($result as $row) {
            $counter++;
            $datum_heute = date("Y-m-d");
            if (($row['MIETVERTRAG_BIS'] > $datum_heute) or ($row['MIETVERTRAG_BIS'] == "0000-00-00")) {
                $beenden_link = "<a href='" . route('web::mietvertraege::legacy', ['mietvertrag_raus' => 'mietvertrag_beenden', 'mietvertrag_id' => $row['MIETVERTRAG_ID']]) . "'>Beenden</a>";
                $aendern_link = "<a href='" . route('web::mietvertraege::legacy', ['mietvertrag_raus' => 'mietvertrag_aendern', 'mietvertrag_id' => $row['MIETVERTRAG_ID']]) . "'>Ändern</a>";
            } else {
                $beenden_link = "Abgelaufen";
                $aendern_link = "<a href='" . route('web::mietvertraege::legacy', ['mietvertrag_raus' => 'mietvertrag_aendern', 'mietvertrag_id' => $row['MIETVERTRAG_ID']]) . "'>Ändern</a>";
            }
            $MIETVERTRAG_BIS = date_mysql2german($row['MIETVERTRAG_BIS']);
            $MIETVERTRAG_VON = date_mysql2german($row['MIETVERTRAG_VON']);
            $mieter_im_vetrag = anzahl_mieter_im_vertrag($row['MIETVERTRAG_ID']);
            $einheit_kurzname = einheit_kurzname($row['EINHEIT_ID']);
            $detail_check = detail_check("Mietvertrag", $row['MIETVERTRAG_ID']);
            $mietkonto_link = "<a href='" . route('web::mietkontenblatt::legacy', ['anzeigen' => 'mietkonto_uebersicht_detailiert', 'mietvertrag_id' => $row['MIETVERTRAG_ID']]) . "'>MIETKONTO</a>";
            $miete_aendern = "<a href='" . route('web::miete_definieren::legacy', ['option' => 'miethoehe', 'mietvertrag_id' => $row['MIETVERTRAG_ID']]) . "'>MIETHÖHE</a>";
            $einheit_link = "<a href='" . route('web::uebersicht::legacy', ['anzeigen' => 'einheit', 'einheit_id' => $row['EINHEIT_ID']]) . "'>$einheit_kurzname</a>";
            $mv_loeschen_link = "<a href='" . route('web::mietvertraege::legacy', ['mietvertrag_raus' => 'mv_loeschen', 'mv_id' => $row['MIETVERTRAG_ID']]) . "'>MV löschen</a>";

            if ($detail_check > 0) {
                $detail_link = "<a class=\"table_links\" href='" . route('web::details::legacy', ['option' => 'details_anzeigen', 'detail_tabelle' => 'Mietvertrag', 'detail_id' => $row['MIETVERTRAG_ID']]) . "'>Details</a>";
            } else {
                $detail_link = "<a class=\"table_links\" href='" . route('web::details::legacy', ['option' => 'details_hinzu', 'detail_tabelle' => 'Mietvertrag', 'detail_id' => $row['MIETVERTRAG_ID']]) . "'>Neues Detail</a>";
            }
            if ($counter == 1) {
                echo "<tr class=\"zeile1\"><td width=100>$einheit_link $mietkonto_link $miete_aendern</td><td width=300>($mieter_im_vetrag)";
                mieterid_zum_vertrag($row['MIETVERTRAG_ID']);
                echo "</td><td width=80>$MIETVERTRAG_VON</td><td width=80>$MIETVERTRAG_BIS</td><td>$detail_link</td><td>$beenden_link $aendern_link $mv_loeschen_link</td></tr>";
            }
            if ($counter == 2) {
                echo "<tr class=\"zeile2\"><td width=100>$einheit_link $mietkonto_link $miete_aendern</td><td width=300>($mieter_im_vetrag)";
                mieterid_zum_vertrag($row['MIETVERTRAG_ID']);
                echo "</td><td width=80>$MIETVERTRAG_VON</td><td width=80>$MIETVERTRAG_BIS</td><td>$detail_link</td><td>$beenden_link $aendern_link $mv_loeschen_link</td></tr>";
                $counter = 0;
            }
        }
        echo "</table>";
        iframe_end();
    }
}

function mietvertrag_abgelaufen($einheit_id)
{
    if (empty ($einheit_id)) {
        $datum_heute = date("Y-m-d");
        $db_abfrage = "SELECT MIETVERTRAG.MIETVERTRAG_ID, MIETVERTRAG.MIETVERTRAG_VON, MIETVERTRAG.MIETVERTRAG_BIS, MIETVERTRAG.EINHEIT_ID, EINHEIT.EINHEIT_KURZNAME FROM MIETVERTRAG JOIN(EINHEIT) ON (EINHEIT.EINHEIT_ID = MIETVERTRAG.EINHEIT_ID) WHERE MIETVERTRAG_AKTUELL='1' && EINHEIT.EINHEIT_AKTUELL='1' && (MIETVERTRAG.MIETVERTRAG_BIS!='0000-00-00' && MIETVERTRAG.MIETVERTRAG_BIS<'$datum_heute') ORDER BY EINHEIT.EINHEIT_KURZNAME ASC,MIETVERTRAG.MIETVERTRAG_BIS  DESC";
    } else {
        $db_abfrage = "SELECT MIETVERTRAG_ID, MIETVERTRAG_VON, MIETVERTRAG_BIS, EINHEIT_ID FROM MIETVERTRAG where EINHEIT_ID='$einheit_id' && MIETVERTRAG_AKTUELL='1'";
    }

    $result = DB::select($db_abfrage);

    if (empty($result)) {
        echo "<h5><b>Keine Mietverträge zur Einheit $einheit_id vorhanden.</b></h5>";
    } else {
        echo "<table width=100%>\n";
        echo "<tr class=\"feldernamen\"><td colspan=5>Alle Mietverträge</td></tr>\n";
        echo "<tr class=\"feldernamen\"><td width=100>Einheit</td><td width=300>Mieter</td><td width=85>Von</td><td width=80>Bis</td><td>Optionen</td></tr>\n";
        echo "</table>\n";
        iframe_start();
        echo "<table width=100%>\n";
        $counter = 0;
        foreach ($result as $row) {
            $counter++;
            $datum_heute = date("Y-m-d");
            if (($row['MIETVERTRAG_BIS'] > $datum_heute) or ($row['MIETVERTRAG_BIS'] == "0000-00-00")) {
                $beenden_link = "<a href='" . route('web::mietvertraege::legacy', ['mietvertrag_raus' => 'mietvertrag_beenden', 'einheit_id' => $row['EINHEIT_ID']]) . "'>Beenden</a>";
                $aendern_link = "<a href='" . route('web::mietvertraege::legacy', ['mietvertrag_raus' => 'mietvertrag_aendern', 'einheit_id' => $row['EINHEIT_ID']]) . "'>Ändern</a>";
            } else {
                $beenden_link = "Abgelaufen";
                $aendern_link = "k.Ä.";
            }
            $MIETVERTRAG_BIS = date_mysql2german($row['MIETVERTRAG_BIS']);
            $MIETVERTRAG_VON = date_mysql2german($row['MIETVERTRAG_VON']);
            $mieter_im_vetrag = anzahl_mieter_im_vertrag($row['MIETVERTRAG_ID']);
            $einheit_kurzname = einheit_kurzname($row['EINHEIT_ID']);
            $detail_check = detail_check("Mietvertrag", $row['MIETVERTRAG_ID']);
            $buchen_link = "<a href='" . route('web::miete_buchen::legacy', ['schritt' => 'buchungsauswahl', 'mietvertrag_id' => $row['MIETVERTRAG_ID']]) . "'>BUCHEN</a>";
            $mietkonto_link = "<a href='" . route('web::mietkontenblatt::legacy', ['anzeigen' => 'mietkonto_uebersicht_detailiert', 'mietvertrag_id' => $row['MIETVERTRAG_ID']]) . "'>MIETKONTO</a>";
            $miete_aendern = "<a href='" . route('web::miete_definieren::legacy', ['option' => 'miethoehe', 'mietvertrag_id' => $row['MIETVERTRAG_ID']]) . "'>MIETHÖHE</a>";
            $einheit_link = "<a href='" . route('web::uebersicht::legacy', ['anzeigen' => 'einheit', 'einheit_id' => $row['EINHEIT_ID']]) . "'>$einheit_kurzname</a>";
            $kautionen_link = "<a href='" . route('web::kautionen::legacy', ['option' => 'kautionen_buchen', 'mietvertrag_id' => $row['MIETVERTRAG_ID']]) . "'>KAUTION BUCHEN</a>";

            if ($detail_check > 0) {
                $detail_link = "<a class=\"table_links\" href='" . route('web::details::legacy', ['option' => 'details_anzeigen', 'detail_tabelle' => 'Mietvertrag', 'detail_id' => $row['MIETVERTRAG_ID']]) . "'>Details</a>";
            } else {
                $detail_link = "<a class=\"table_links\" href='" . route('web::details::legacy', ['option' => 'details_hinzu', 'detail_tabelle' => 'Mietvertrag', 'detail_id' => $row['MIETVERTRAG_ID']]) . "'>Neues Detail</a>";
            }
            if ($counter == 1) {
                echo "<tr class=\"zeile1\"><td>$einheit_link</td><td>($mieter_im_vetrag)";
                mieterid_zum_vertrag($row['MIETVERTRAG_ID']);
                echo "</td><td>$mietkonto_link $buchen_link $miete_aendern  $kautionen_link</td><td>$MIETVERTRAG_VON</td><td>$MIETVERTRAG_BIS</td><td>$detail_link</td><td>$beenden_link $aendern_link</td></tr>";
            }
            if ($counter == 2) {
                echo "<tr class=\"zeile2\"><td>$einheit_link </td><td>($mieter_im_vetrag)";
                mieterid_zum_vertrag($row['MIETVERTRAG_ID']);
                echo "</td><td>$mietkonto_link $buchen_link $miete_aendern  $kautionen_link</td><td>$MIETVERTRAG_VON</td><td>$MIETVERTRAG_BIS</td><td>$detail_link</td><td>$beenden_link $aendern_link</td></tr>";
                $counter = 0;
            }
        }
        echo "</table>";
        iframe_end();
    }
}

function mietvertrag_aktuelle($einheit_id)
{
    if (!isset ($einheit_id)) {
        $datum_heute = date("Y-m-d");
        $db_abfrage = "SELECT MIETVERTRAG.MIETVERTRAG_ID, MIETVERTRAG.MIETVERTRAG_VON, MIETVERTRAG.MIETVERTRAG_BIS, MIETVERTRAG.EINHEIT_ID, EINHEIT.EINHEIT_KURZNAME FROM MIETVERTRAG JOIN(EINHEIT) ON (EINHEIT.EINHEIT_ID = MIETVERTRAG.EINHEIT_ID) WHERE MIETVERTRAG_AKTUELL='1' && EINHEIT.EINHEIT_AKTUELL='1' && (MIETVERTRAG.MIETVERTRAG_BIS='0000-00-00' OR MIETVERTRAG.MIETVERTRAG_BIS>'$datum_heute') ORDER BY EINHEIT.EINHEIT_KURZNAME ASC,MIETVERTRAG.MIETVERTRAG_BIS  DESC";
    } else {
        $db_abfrage = "SELECT MIETVERTRAG_ID, MIETVERTRAG_VON, MIETVERTRAG_BIS, EINHEIT_ID FROM MIETVERTRAG where EINHEIT_ID='$einheit_id' && MIETVERTRAG_AKTUELL='1'";
    }

    $result = DB::select($db_abfrage);

    if (empty($result)) {
        echo "<h5><b>Keine Mietverträge zur Einheit $einheit_id vorhanden.</b></h5>";
    } else {
        echo "<table width=100%>\n";
        echo "<tr class=\"feldernamen\"><td colspan=5>Alle Mietverträge</td></tr>\n";
        echo "<tr class=\"feldernamen\"><td width=100>Einheit</td><td width=300>Mieter</td><td width=85>Von</td><td width=80>Bis</td><td>Optionen</td></tr>\n";
        echo "</table>\n";
        iframe_start();
        echo "<table width=100%>\n";
        $counter = 0;
        foreach ($result as $row) {
            $counter++;
            $datum_heute = date("Y-m-d");
            if (($row['MIETVERTRAG_BIS'] > $datum_heute) or ($row['MIETVERTRAG_BIS'] == "0000-00-00")) {
                $beenden_link = "<a href='" . route('web::mietvertraege::legacy', ['mietvertrag_raus' => 'mietvertrag_beenden', 'mietvertrag_id' => $row['MIETVERTRAG_ID']]) . "'>Beenden</a>";
                $aendern_link = "<a href='" . route('web::mietvertraege::legacy', ['mietvertrag_raus' => 'mietvertrag_aendern', 'mietvertrag_id' => $row['MIETVERTRAG_ID']]) . "'>Ändern</a>";
            } else {
                $beenden_link = "Abgelaufen";
                $aendern_link = "<a href='" . route('web::mietvertraege::legacy', ['mietvertrag_raus' => 'mietvertrag_aendern', 'mietvertrag_id' => $row['MIETVERTRAG_ID']]) . "'>Ändern</a>";
            }
            $MIETVERTRAG_BIS = date_mysql2german($row['MIETVERTRAG_BIS']);
            $MIETVERTRAG_VON = date_mysql2german($row['MIETVERTRAG_VON']);
            $mieter_im_vetrag = anzahl_mieter_im_vertrag($row['MIETVERTRAG_ID']);
            $einheit_kurzname = einheit_kurzname($row['EINHEIT_ID']);
            $detail_check = detail_check("Mietvertrag", $row['MIETVERTRAG_ID']);
            $einheit_link = "<a href='" . route('web::uebersicht::legacy', ['anzeigen' => 'einheit', 'einheit_id' => $row['EINHEIT_ID']]) . "'>$einheit_kurzname</a>";
            $kautionen_link = "<a href='" . route('web::kautionen::legacy', ['option' => 'kautionen_buchen', 'mietvertrag_id' => $row['MIETVERTRAG_ID']]) . "'>KAUTION BUCHEN</a>";
            $miete_aendern = "<a href='" . route('web::miete_definieren::legacy', ['option' => 'miethoehe', 'mietvertrag_id' => $row['MIETVERTRAG_ID']]) . "'>MIETHÖHE</a>";
            if ($detail_check > 0) {
                $detail_link = "<a class=\"table_links\" href='" . route('web::details::legacy', ['option' => 'details_anzeigen', 'detail_tabelle' => 'Mietvertrag', 'detail_id' => $row['MIETVERTRAG_ID']]) . "'>Details</a>";
            } else {
                $detail_link = "<a class=\"table_links\" href='" . route('web::details::legacy', ['option' => 'details_hinzu', 'detail_tabelle' => 'Mietvertrag', 'detail_id' => $row['MIETVERTRAG_ID']]) . "'>Neues Detail</a>";
            }
            if ($counter == 1) {
                echo "<tr class=\"zeile1\"><td>$einheit_link $miete_aendern $kautionen_link </td><td>($mieter_im_vetrag)";
                mieterid_zum_vertrag($row['MIETVERTRAG_ID']);
                echo "</td><td>$MIETVERTRAG_VON</td><td>$MIETVERTRAG_BIS</td><td>$detail_link</td><td>$beenden_link $aendern_link</td></tr>";
            }
            if ($counter == 2) {
                echo "<tr class=\"zeile2\"><td>$einheit_link $miete_aendern $kautionen_link</td><td>($mieter_im_vetrag)";
                mieterid_zum_vertrag($row['MIETVERTRAG_ID']);
                echo "</td><td>$MIETVERTRAG_VON</td><td>$MIETVERTRAG_BIS</td><td>$detail_link</td><td>$beenden_link $aendern_link</td></tr>";
                $counter = 0;
            }
        }
        echo "</table>";
        iframe_end();
    }
}

function mietvertrag_objekt_links()
{
    $db_abfrage = "SELECT OBJEKT_DAT, OBJEKT_ID, OBJEKT_KURZNAME FROM OBJEKT WHERE OBJEKT_AKTUELL='1' ORDER BY OBJEKT_KURZNAME ASC ";
    $result = DB::select($db_abfrage);
    echo "<b>Objekt auswählen:</b><br>\n ";
    foreach ($result as $row) {
        echo "<a class=\"objekt_links\" href='" . route('web::mietvertraege::legacy', ['mietvertrag_raus' => 'mietvertrag_neu', 'objekt_id' => $row['OBJEKT_ID']]) . "'>$row[OBJEKT_KURZNAME]</a><br>\n";
    }
}