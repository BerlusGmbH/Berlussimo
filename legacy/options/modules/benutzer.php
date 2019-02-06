<?php

if (request()->filled('option') && !empty (request()->input('option'))) {
    $option = request()->input('option');
} else {
    $option = 'default';
}

/* Optionsschalter */
switch ($option) {

    default :

    case "werkzeuge" :
        $w = new werkzeug ();
        $w->werkzeugliste();
        break;

    case "werkzeuge_mitarbeiter" :
        $w = new werkzeug ();
        if (request()->filled('b_id')) {
            $b_id = request()->input('b_id');
            $w->werkzeugliste($b_id);
        } else {
            echo "Mitarbeiter wählen!";
            $w->werkzeugliste();
        }

        // $w->werkzeuge_mitarbeiter();
        break;

    case "werkzeug_rueckgabe_alle_pdf" :
        if (request()->filled('b_id')) {
            $b_id = request()->input('b_id');
            $w = new werkzeug ();
            $w->pdf_rueckgabeschein_alle($b_id, 'Werkzeugrückgabeschein ');
        } else {
            fehlermeldung_ausgeben('Mitarbeiter wählen');
        }
        break;

    case "werkzeug_ausgabe_alle_pdf" :
        if (request()->filled('b_id')) {
            $b_id = request()->input('b_id');
            $w = new werkzeug ();
            $w->pdf_rueckgabeschein_alle($b_id, 'Werkzeugausgabegabeschein ');
        } else {
            fehlermeldung_ausgeben('Mitarbeiter wählen');
        }
        break;

    case "werkzeug_rueckgabe_alle" :
        if (request()->filled('b_id')) {
            $b_id = request()->input('b_id');
            $w = new werkzeug ();
            $w->werkzeug_rueckgabe_alle($b_id); // änderung der DB
        } else {
            fehlermeldung_ausgeben('Mitarbeiter wählen');
        }
        break;

    case "werkzeug_zuweisen" :
        if (request()->filled('w_id')) {
            $w_id = request()->input('w_id');
            $w = new werkzeug ();
            $w->form_werkzeug_zuweisen($w_id); // änderung der DB
        } else {
            fehlermeldung_ausgeben('Werkzeug wählen');
        }
        break;

    case "werkzeug_zuweisen_snd" :
        if (request()->filled('w_id') && request()->filled('b_id')) {
            $w_id = request()->input('w_id');
            $b_id = request()->input('b_id');
            $w = new werkzeug ();
            $w->werkzeug_zuweisen($b_id, $w_id);
            echo "Zugewiesen";
            weiterleiten_in_sec(route('web::benutzer::legacy', ['option' => 'werkzeuge']), 1);
        } else {
            fehlermeldung_ausgeben("Mitarbeiter und Werkzeug wählen!");
        }
        break;

    case "werkzeug_rueckgabe" :
        if (request()->filled('w_id') && request()->filled('b_id')) {
            $w_id = request()->input('w_id');
            $b_id = request()->input('b_id');
            $w = new werkzeug ();
            $w->pdf_werkzeug_rueckgabe_einzel($b_id, $w_id);
        }
        break;

    case "werkzeug_raus" :
        if (request()->filled('w_id')) {
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
