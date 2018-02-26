<?php

if (request()->has('option')) {
    $option = request()->input('option');
} else {
    $option = 'default';
}

/* Optionsschalter */
switch ($option) {

    default :
        $p = new partners ();
        $p->form_such_partner();
        break;

    case "partner_suchen1" :
        $p = new partners ();
        $partner_arr = $p->suche_partner_in_array(request()->input('suchtext'));
        if (is_array($partner_arr)) {
            $p->partner_liste_filter($partner_arr);
        } else {
            fehlermeldung_ausgeben("Keine Partner gefunden!");
        }
        break;

    /*
     * Aufruf des Formulars für die
     * Partner/Lieferantenerfassung
     */
    case "partner_erfassen" :
        $form = new formular ();
        $form->erstelle_formular("Partner / Lieferanten / Eigentümer anlegen", NULL);
        $partners = new partners ();
        // $partners->partner_rechts_anzeigen();
        $partners->form_partner_erfassen();
        $form->ende_formular();
        break;

    case "partner_gesendet" :
        //$partners = new partners ();
        //$partners->partner_rechts_anzeigen();
        $form = new formular ();
        $form->erstelle_formular("Partnerdaten überprüfen", route('web::partner::legacy', ['option' => 'partner_gesendet1'], false));
        $clean_arr = $form->post_array_bereinigen();
        foreach ($clean_arr as $key => $value) {
            if (($key != 'submit_partner') and ($key != 'option')) {
                echo $key . ": " . $value . "<br>";
                $form->hidden_feld($key, $value);
            }
        }
        if (!$fehler) {
            $form->send_button("submit_partner1", "Speichern");
        } else {
            echo "Daten unvollständig";
        }
        $form->ende_formular();
        break;

    case "partner_gesendet1" :
        $form = new formular();
        request()->flash();
        $clean_arr = $form->post_array_bereinigen();
        $partners = new partners ();
        $partners->partner_speichern($clean_arr);
        weiterleiten(route('web::partner::legacy', ['option' => 'partner_liste'], false));
        break;

    case "partner_liste" :
        $form = new formular ();
        $form->erstelle_formular("Partnerliste", NULL);
        $partner = new partners ();
        $partner->partner_liste();
        $form->ende_formular();
        break;

    case "partner_stichwort" :
        if (request()->has('partner_id')) {
            $pp = new partners ();
            $pp->form_partner_stichwort_neu(request()->input('partner_id'));
            $pp->form_partner_stichwort(request()->input('partner_id'));
        }
        break;

    case "partner_stich_sent" :
        if (request()->has('stichworte')) {
            $anz_stich = count(request()->input('stichworte'));
            $partner_id = request()->input('partner_id');
            $pp = new partners ();
            $pp->stichworte_speichern($partner_id, request()->input('stichworte'));
            weiterleiten(route('web::partner::legacy', ['option' => 'partner_stichwort', 'partner_id' => $partner_id], false));
        }
        break;

    case "partner_stich_sent_neu" :
        if (request()->has('partner_id') && request()->has('stichwort') && request()->has('stichwort')) {
            $stichwort = request()->input('stichwort');
            $partner_id = request()->input('partner_id');
            $pp = new partners ();
            $pp->stichwort_speichern($partner_id, $stichwort);
            weiterleiten(route('web::partner::legacy', ['option' => 'partner_stichwort', 'partner_id' => $partner_id], false));
        }
        break;

    case "partner_umsatz" :
        $form = new formular ();
        $form->erstelle_formular("Partnerliste nach Umsatz", NULL);
        $partner = new partners ();
        $arr = $partner->partner_nach_umsatz();
        echo "<pre>";
        $anz = count($arr);
        if ($anz) {
            echo "<table class=\"sortable\">";
            echo "<tr><th>PARTNER</th><th>NETTO</th><th>BRUTTO</th></tr>";
            for ($a = 0; $a < $anz; $a++) {
                $p_name = $arr [$a] ['PARTNER_NAME'];
                $netto = nummer_punkt2komma_t($arr [$a] ['NETTO']);
                $brutto = nummer_punkt2komma_t($arr [$a] ['BRUTTO']);
                echo "<tr><td>$p_name</td><td>$netto</td><td>$brutto</td>";
            }
            echo "</table>";
        }
        $form->ende_formular();
        break;

    case "partner_im_detail" :
        $form = new formular ();
        $form->erstelle_formular("Partnerdetails", NULL);
        $partner = new partner ();
        $partner_id = request()->input('partner_id');
        $partner->partnerdaten_anzeigen($partner_id);
        $d = new detail ();
        $d->detailsanzeigen('Partner', $partner_id);
        $form->ende_formular();

        break;

    case "partner_aendern" :
        if (request()->has('partner_id')) {
            $partner = new partners ();
            $partner->form_partner_aendern(request()->input('partner_id'));
        } else {
            echo "Bitte den Partner zum Ändern wählen.";
        }
        break;

    case "partner_aendern_send" :
        if (request()->isMethod('post')) {
            if (request()->has('partner_dat') && request()->has('partner_id') && request()->has('partnername') && request()->has('strasse') && request()->has('hausnummer') && request()->has('plz') && request()->has('ort') && request()->has('land')) {
                echo "alles OK";
                $p = new partners ();
                $p->partner_aendern(request()->input('partner_dat'), request()->input('partner_id'), request()->input('partnername'), request()->input('strasse'), request()->input('hausnummer'), request()->input('plz'), request()->input('ort'), request()->input('land'));
                weiterleiten(route('web::partner::legacy', ['option' => 'partner_im_detail', 'partner_id' => request()->input('partner_id')], false));
            } else {
                echo "DATEN UNVOLLSTÄNDIG";
            }
        } else {
            echo "Daten unvollständig";
        }
        break;

    /* Auswahlmaske Empfänger */
    case "serienbrief" :
        $pp = new partners ();
        $pp->form_partner_serienbrief();
        break;

    case "serien_brief_vorlagenwahl" :
        if (request()->has('delete')) {
            session()->put('p_ids', []);
            echo "Alle gelöscht!";
            break;
        }
        if (!session()->has('p_ids')) {
            session()->put('p_ids', []);
        }
        if (request()->has('p_ids') && is_array(request()->input('p_ids'))) {
            session()->put('p_ids', array_merge(session()->get('p_ids'), request()->input('p_ids')));
            session()->put('p_ids', array_unique(session()->get('p_ids')));
            $s = new serienbrief ();
            if (request()->has('kat')) {
                $s->vorlage_waehlen('Partner', request()->input('kat'));
            } else {
                $s->vorlage_waehlen('Partner');
            }
        } else {
            fehlermeldung_ausgeben("Bitte Partner aus Liste wählen!");
        }
        break;

    case "serienbrief_pdf" :
        $bpdf = new b_pdf ();
        $s = new serienbrief ();
        $s->erstelle_brief_vorlage(request()->input('vorlagen_dat'), 'Partner', session()->get('p_ids'), $option = '0');
        break;
}
