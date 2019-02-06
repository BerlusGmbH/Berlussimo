<?php

if (request()->input('option')) {
    $option = request()->input('option');
} else {
    $option = '';
}
/* Optionsschalter */
switch ($option) {

    default :
        $t = new todo ();
        $t->baustellen_liste();
        break;

    case "pdf_auftrag" :
        if (request()->filled('proj_id')) {
            $t = new todo ();
            $t->pdf_auftrag(intval(request()->input('proj_id')));
        } else {
            echo "Projekt wählen";
        }
        break;

    case "form_neue_baustelle" :
        $t = new todo ();
        $t->form_neue_baustelle();
        break;

    case "neue_baustelle" :
        if (request()->filled('bau_bez') && request()->filled('p_id')) {
            $t = new todo ();
            if ($t->neue_baustelle_speichern(request()->input('bau_bez'), request()->input('p_id'))) {
                $bau_bez = request()->input('bau_bez');
                hinweis_ausgeben("Baustelle $bau_bez wurde erstellt");
            }
        } else {
            fehlermeldung_ausgeben('Ihre Eingabe zur Baustelle war unvollständig!');
        }
        break;

    case "baustellen_liste" :
        $t = new todo ();
        $t->baustellen_liste();
        break;

    case "baustellen_liste_inaktiv" :
        $t = new todo ();
        $t->baustellen_liste('0');
        break;

    case "baustelle_aktivieren" :
        $bau_id = request()->input('bau_id');
        $t = new todo ();
        $t->baustelle_aktivieren($bau_id, '1');
        weiterleiten(route('web::construction::legacy', ['option' => 'baustellen_liste'], false));
        break;

    case "baustelle_deaktivieren" :
        $bau_id = request()->input('bau_id');
        $t = new todo ();
        $t->baustelle_aktivieren($bau_id, '0');
        weiterleiten(route('web::construction::legacy', ['option' => 'baustellen_liste_inaktiv'], false));
        break;

    case "auftrag_haus" :
        if (request()->filled('haus_id')) {
            $t = new todo ();
            $t->auftraege_an_haus(request()->input('haus_id'));
        } else {
            fehlermeldung_ausgeben("Haus wählen");
        }
        break;

    case "auftraege_an" :

        if (request()->filled('typ') && request()->filled('id')) {
            $typ = request()->input('typ');
            $id = request()->input('id');
            $to = new todo ();
            /* Offene */
            $to->liste_auftrage_an($typ, $id, 0);
            /* Erledigte */
            $to->liste_auftrage_an($typ, $id, 1);
        }
        break;
} // end switch