<?php

if (request()->has('option') && !empty (request()->input('option'))) {
    $option = request()->input('option');
} else {
    $option = 'default';
}

/* Optionsschalter */
switch ($option) {

    default :
        $b = new benutzer ();
        $b->benutzer_anzeigen();
        break;

    case "berechtigungen" :
        if (request()->has('b_id')) {
            $b_id = request()->input('b_id');
            $b = new benutzer ();
            $b->berechtigungen($b_id);
        } else {
            echo "Benutzer/Mitarbeiter wählen";
        }
        break;

    case "aendern" :
        if (request()->has('b_id')) {
            $b_id = request()->input('b_id');
            $b = new benutzer ();
            $b->form_benutzer_aendern($b_id);
        } else {
            echo "Benutzer/Mitarbeiter wählen";
        }
        break;

    case "benutzer_aendern_send" :
        if (request()->has('b_id') && !empty (request()->input('b_id'))) {
            $benutzer_name = request()->input('benutzername');
            $b_id = request()->input('b_id');
            $passwort = request()->input('passwort');
            $partner_id = request()->input('partner_id');
            $gewerk_id = request()->input('gewerk_id');
            $geburtstag = request()->input('geburtstag');
            $eintritt = request()->input('eintritt');
            $austritt = request()->input('austritt');

            $urlaub = request()->input('urlaub');
            $stunden_pw = request()->input('stunden_pw');
            $stundensatz = request()->input('stundensatz');
            $be = new benutzer ();
            $be->benutzer_aenderungen_speichern($b_id, $benutzer_name, $passwort, $partner_id, $stundensatz, $geburtstag, $gewerk_id, $eintritt, $austritt, $urlaub, $stunden_pw);
            fehlermeldung_ausgeben("Bitte warten...");
            weiterleiten_in_sec(route('legacy::benutzer::index', ['option' => 'aendern', 'b_id' => $b_id], false), 2);
        } else {
            fehlermeldung_ausgeben("Benutzerdaten unvollständig");
        }
        break;

    case "zugriff_send" :
        if (request()->has('b_id') && request()->has('modul_name')) {
            $b_id = request()->input('b_id');
            $modul_name = request()->input('modul_name');

            $b = new benutzer ();
            if (request()->has('submit_ja')) {
                $b->berechtigungen_speichern($b_id, $modul_name);
            }
            if (request()->has('submit_no')) {
                $b->berechtigungen_entziehen($b_id, $modul_name);
            }
            weiterleiten(route('legacy::benutzer::index', ['option' => 'berechtigungen', 'b_id' => $b_id], false));
        }
        break;

    case "neuer_benutzer" :
        $b = new benutzer ();
        $b->form_neuer_benutzer();
        break;

    case "benutzer_send" :
        if (request()->isMethod('post')) {
            if (request()->has('benutzername') && request()->has('passwort') && request()->has('partner_id') && request()->has('geburtstag') && request()->has('eintritt') && request()->has('urlaub') && request()->has('stunden_pw')) {
                $b = new benutzer ();
                $benutzername = request()->input('benutzername');
                $passwort = request()->input('passwort');
                $partner_id = request()->input('partner_id');
                $stundensatz = request()->input('stundensatz');
                $geb_dat = request()->input('geburtstag');
                $gewerk_id = request()->input('gewerk_id');
                $eintritt = request()->input('eintritt');
                $austritt = request()->input('austritt');
                $urlaub = request()->input('urlaub');
                $stunden_pw = request()->input('stunden_pw');
                if (check_datum($geb_dat) && check_datum($eintritt)) {
                    $geb_dat = date_german2mysql($geb_dat);
                    $eintritt = date_german2mysql($eintritt);
                    if (!empty ($austritt)) {
                        $austritt = date_german2mysql($austritt);
                    }
                    $stundensatz = nummer_komma2punkt($stundensatz);
                    $benutzer_id = $b->benutzer_speichern($benutzername, $passwort, $partner_id, $stundensatz, $geb_dat, $gewerk_id, $eintritt, $austritt, $urlaub, $stunden_pw);
                    weiterleiten(route('legacy::benutzer::index', ['option' => 'berechtigungen', 'b_id' => $benutzer_id], false));
                } else {
                    die ('Datumsangaben falsch');
                }
            } else {
                die ('Fehler xg763663 - Daten unvollständig');
            }
        } else {
            die ('Fehler xg763664');
        }
        break;

    case "werkzeuge" :
        $w = new werkzeug ();
        $w->werkzeugliste();
        break;

    case "werkzeuge_mitarbeiter" :
        $w = new werkzeug ();
        if (request()->has('b_id')) {
            $b_id = request()->input('b_id');
            $w->werkzeugliste($b_id);
        } else {
            echo "Mitarbeiter wählen!";
            $w->werkzeugliste();
        }

        // $w->werkzeuge_mitarbeiter();
        break;

    case "werkzeug_rueckgabe_alle_pdf" :
        if (request()->has('b_id')) {
            $b_id = request()->input('b_id');
            $w = new werkzeug ();
            $w->pdf_rueckgabeschein_alle($b_id, 'Werkzeugrückgabeschein ');
        } else {
            fehlermeldung_ausgeben('Mitarbeiter wählen');
        }
        break;

    case "werkzeug_ausgabe_alle_pdf" :
        if (request()->has('b_id')) {
            $b_id = request()->input('b_id');
            $w = new werkzeug ();
            $w->pdf_rueckgabeschein_alle($b_id, 'Werkzeugausgabegabeschein ');
        } else {
            fehlermeldung_ausgeben('Mitarbeiter wählen');
        }
        break;

    case "werkzeug_rueckgabe_alle" :
        if (request()->has('b_id')) {
            $b_id = request()->input('b_id');
            $w = new werkzeug ();
            $w->werkzeug_rueckgabe_alle($b_id); // änderung der DB
        } else {
            fehlermeldung_ausgeben('Mitarbeiter wählen');
        }
        break;

    case "werkzeug_zuweisen" :
        if (request()->has('w_id')) {
            $w_id = request()->input('w_id');
            $w = new werkzeug ();
            $w->form_werkzeug_zuweisen($w_id); // änderung der DB
        } else {
            fehlermeldung_ausgeben('Werkzeug wählen');
        }
        break;

    case "werkzeug_zuweisen_snd" :
        if (request()->has('w_id') && request()->has('b_id')) {
            $w_id = request()->input('w_id');
            $b_id = request()->input('b_id');
            $w = new werkzeug ();
            $w->werkzeug_zuweisen($b_id, $w_id);
            echo "Zugewiesen";
            weiterleiten_in_sec(route('legacy::benutzer::index', ['option' => 'werkzeuge']), 1);
        } else {
            fehlermeldung_ausgeben("Mitarbeiter und Werkzeug wählen!");
        }
        break;

    case "werkzeug_rueckgabe" :
        if (request()->has('w_id') && request()->has('b_id')) {
            $w_id = request()->input('w_id');
            $b_id = request()->input('b_id');
            $w = new werkzeug ();
            $w->pdf_werkzeug_rueckgabe_einzel($b_id, $w_id);
        }
        break;

    case "werkzeug_raus" :
        if (request()->has('w_id')) {
            $w_id = request()->input('w_id');
            $w = new werkzeug ();
            $w->werkzeug_loeschen($w_id);
        }
        break;

    case "werkzeugliste_nach_mitarbeiter" :
        $w = new werkzeug ();
        $w->werkzeugliste_nach_mitarbeiter();
        break;
} // END SWITCH
