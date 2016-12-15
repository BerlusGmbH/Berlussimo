<?php

if (request()->has('option') && !empty (request()->input('option'))) {
    $option = request()->input('option');
} else {
    $option = 'default';
}

/* Optionsschalter */
switch ($option) {

    /* Kautionseinzahlung */
    case "kautionen_buchen" :
        if (request()->has('mietvertrag_id')) {
            $mv_id = request()->input('mietvertrag_id');
            $k = new kautionen ();
            $k->form_kautionsbuchung_mieter($mv_id);
        } else {
            echo "Mietvertrag auswählen";
        }
        break;

    case "kaution_gesendet" :
        if (request()->has('mietvertrag_id') && request()->has('datum') && request()->has('betrag') && request()->has('text')) {
            $mv_id = request()->input('mietvertrag_id');
            $betrag = nummer_komma2punkt(request()->input('betrag'));
            $datum = request()->input('datum');
            $datum = date_german2mysql($datum);
            $text = request()->input('text');
            $k = new kautionen ();
            $k->kaution_speichern($datum, 'MIETVERTRAG', $mv_id, $betrag, $text, '1000');
        } else {
            echo "Mietvertrag auswählen";
        }
        break;

    case "hochrechner" :
        $k = new kautionen ();
        if (request()->has('mietvertrag_id')) {
            $mv_id = request()->input('mietvertrag_id');
            $k->form_hochrechnung_mv($mv_id);
        } else {
            echo "Mietvertrag auswählen";
        }
        break;

    case "hochrechnung_mv" :
        $k = new kautionen ();
        $datum_bis = date_german2mysql(request()->input('datum_bis'));
        $mietvertrag_id = request()->input('mietvertrag_id');
        $k->kautionsberechnung('Mietvertrag', $mietvertrag_id, $datum_bis, 0.0025, 25, 5.5);
        $k->kautionsberechnung_2('250', '2014-01-06', '2014-04-30', 0.0025, 25, 5.5);
        $k->kautionsberechnung_2('250', '2014-01-21', '2014-04-30', 0.0025, 25, 5.5);
        $k->kautionsberechnung_2('500', '2014-02-17', '2014-04-30', 0.0025, 25, 5.5);
        $k->kautionsberechnung_2('250.15', '2014-04-30', '2014-07-31', 0.0015, 25, 5.5);
        $k->kautionsberechnung_2('250.13', '2014-04-30', '2014-07-31', 0.0015, 25, 5.5);
        $k->kautionsberechnung_2('500.19', '2014-04-30', '2014-07-31', 0.0015, 25, 5.5);
        $k->kautionsberechnung_2('1000.46', '2014-04-30', '2014-07-31', 0.0015, 25, 5.5);

        // $k->kautionsberechnung_2('536.929', '2014-05-01', '2014-08-30', 0.0015,25,5.5);
        break;

    case "hochrechner_pdf" :
        $k = new kautionen ();
        if (request()->has('datum_bis') && !empty (request()->input('mietvertrag_id'))) {
            $datum_bis = date_german2mysql(request()->input('datum_bis'));
            $mietvertrag_id = request()->input('mietvertrag_id');
            $k->kautionsberechnung_pdf('Mietvertrag', $mietvertrag_id, $datum_bis, 0.0025, 25, 5.5);
        } else {
            echo "Mietvertrag und Auszahlungsdatum eingeben";
        }
        break;

    case "kontohochrechnung" :
        $k = new kautionen ();

        if (!request()->has('datum_bis')) {
            $datum_bis = date("Y") . "-12-31";
        } else {
            $datum_bis = date_german2mysql(request()->input('datum_bis'));
        }

        if (request()->has('tag') && request()->has('monat') && request()->has('jahr')) {
        }

        $k->kontohochrechnung($datum_bis, 0.0025, 25, 5.5);
        break;

    /* Mieter ohne Kautionen */
    case "mv_ohne_k" :
        $k = new kautionen ();
        if (session()->has('geldkonto_id')) {
            $k->mieter_ohne_kaution_anzeigen(session()->get('geldkonto_id'), '1000');
        } else {
            hinweis_ausgeben('Kautionskonto wählen');
        }
        break;

    case "kautionsuebersicht" :
        $bk = new berlussimo_global();
        $bk->objekt_auswahl_liste();

        if (session()->has('ansicht_k')) {
            session()->forget('ansicht_k');
        }

        if (request()->has('ansicht_k')) {
            session()->put('ansicht_k', 'alle');
        }

        $k = new kautionen ();
        $f = new formular ();

        if (session()->has('ansicht_k')) {
            $k->kautions_uebersicht(session()->get('objekt_id'), session()->get('ansicht_k'));
        } else {
            $js = "onclick=\"window.location.href += '&ansicht_k=alle'\"";
            $f->button_js('BtN_alle', 'Alle Altmieter anzeigen', $js);

            $k->kautions_uebersicht(session()->get('objekt_id'), null);
        }
        break;

    case "kautionsfelder" :
        $k = new kautionen ();
        $arr = $k->get_felder_arr();
        $f = new formular ();
        $f->erstelle_formular("Neues Feld", null);
        $f->text_feld("Feld/Spaltenbezeichnung", 'feld', '', 50, 'feld', null);
        $f->hidden_feld("option", "feld_hinzu");
        $f->send_button("submit", "Feld hinzufügen");
        $f->ende_formular();
        if (!empty($arr)) {
            $anz = count($arr);

            $f->fieldset("Kautionsfelder", null);
            echo "<table class='sortable striped'>";
            echo "<thead>";
            echo "<tr><th>FELD</th></th><th>OPTION</th></tr>";
            echo "</thead>";
            $z = 0;

            for ($a = 0; $a < $anz; $a++) {
                $z++;
                $feld = $arr [$a] ['FELD'];
                $dat = $arr [$a] ['DAT'];
                $link_del = "<a href='" . route('legacy::kautionen::index', ['option' => 'feld_del', 'dat' => $dat]) . "'>Löschen</a>";
                echo "<tr><td>$z. $feld</td>";
                echo "<td>$link_del</td>";
                echo "</tr>";
            }
            echo "</table>";
            $f->fieldset_ende();
        } else {
            fehlermeldung_ausgeben("Keine Kautionsfelder in der Datenbank vorhanden!");
        }
        break;

    case "feld_hinzu" :
        if (request()->has('feld')) {
            $k = new kautionen ();
            $k->feld_speichern(request()->input('feld'));
        }
        weiterleiten(route('legacy::kautionen::index', ['option' => 'kautionsfelder'], false));
        break;

    case "feld_del" :
        if (request()->has('dat')) {
            $k = new kautionen ();
            $k->feld_del(request()->input('dat'));
            weiterleiten(route('legacy::kautionen::index', ['option' => 'kautionsfelder'], false));
        }

        break;
}
