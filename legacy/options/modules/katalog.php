<?php

if (request()->has('option') && !empty (request()->input('option'))) {
    $option = request()->input('option');
} else {
    $option = 'default';
}

/* Optionsschalter */
switch ($option) {

    default :
        break;

    case "katalog_anzeigen" :
        ini_set('memory_limit', '256M');
        if (!session()->has('partner_id')) {
            weiterleiten(route('web::rechnungen::legacy', ['option' => 'partner_wechseln'], false));
            return;
        }
        $p_id = session()->get('partner_id');
        $p = new partners ();
        $p->get_partner_info($p_id);
        echo "<h5>Katalog von $p->partner_name</h5>";

        $k = new katalog ();
        $k->katalog_artikel_anzeigen($p_id);
        break;

    case "preisentwicklung" :
        if (!request()->has('lieferant') && !session()->has('partner_id') && !request()->has('artikel_nr')) {
            weiterleiten(route('web::rechnungen::legacy', ['option' => 'partner_wechseln'], false));
            return;
        }
        if (!request()->has('lieferant')) {
            $p_id = session()->get('partner_id');
        } else {
            $p_id = request()->input('lieferant');
        }

        $p = new partners ();
        $p->get_partner_info($p_id);
        echo "<h5>Preisentwicklung im Katalog von $p->partner_name</h5>";

        if (!request()->has('artikel_nr')) {
            $k = new katalog ();
            $k->form_preisentwicklung();
        } else {
            $k = new katalog ();
            $artikel_nr = request()->input('artikel_nr');
            $k->preisentwicklung_anzeigen($p_id, $artikel_nr);
        }
        break;

    case "artikelsuche" :
        $k = new katalog ();
        $k->artikel_suche_einkauf_form();
        break;

    case "artikel_suche" :
        if (request()->has('artikel_nr')) {
            $artikel_nr = request()->input('artikel_nr');
            $k = new katalog ();
            $k->artikel_suche_einkauf($artikel_nr);
        }
        break;

    case "artikelsuche_freitext" :
        $k = new katalog ();
        $k->artikel_suche_freitext_form();
        break;

    case "artikel_suche_freitext" :
        if (request()->has('artikel_nr')) {
            $artikel_nr = request()->input('artikel_nr');
            $k = new katalog ();
            $k->artikel_suche_freitext($artikel_nr);
        }
        break;

    case "zuletzt_gekauft" :

        if (session()->has('partner_id')) {
            $k = new katalog ();
            $k->form_zuletzt_gekauft(session()->get('partner_id'));

            if (request()->has('art_anz')) {
                $arr_pos = $k->get_positionen_arr(session()->get('partner_id'), request()->input('art_anz'));
            } else {
                $arr_pos = $k->get_positionen_arr(session()->get('partner_id'), 15);
            }
            $anz_pos = count($arr_pos);
            echo "<table class=\"sortable\">";
            echo "<tr><th>RG</th><th>ARTIKEL</th><th>BEZ</th><th>MENGE</th><th>VE</th><th>BISHER</th><th>PREIS</th></tr>";
            for ($a = 0; $a < $anz_pos; $a++) {
                $art_nr = $arr_pos [$a] ['ARTIKEL_NR'];
                $partner_id = $arr_pos [$a] ['ART_LIEFERANT'];
                $r = new rechnung ();
                $art_arr = $r->artikel_info($partner_id, $art_nr);
                $rg = $arr_pos [$a] ['BELEG_NR'];
                $menge = $arr_pos [$a] ['MENGE'];
                $preis = $arr_pos [$a] ['PREIS'];
                $ve = $art_arr [0] ['EINHEIT'];
                $bez = $art_arr [0] ['BEZEICHNUNG'];
                $link_rg = "<a href='" . route('web::rechnungen.show', ['id' => $rg]) . "'>zur Rg</a>";
                $anz_bisher = $k->get_anz_bisher($art_nr, $partner_id);
                echo "<tr><td>$link_rg</td><td>$art_nr</td><td>$bez</td><td>$menge</td><td>$ve</td><td>$anz_bisher</td><td>$preis</td></tr>";
            }
            echo "</table>";
        } else {
            fehlermeldung_ausgeben("Partner wählen!");
        }

        break;

    case "meist_gekauft" :
        $k = new katalog ();
        $arr_pos = $k->get_meistgekauft_arr(session()->get('partner_id'));
        $partner_id = session()->get('partner_id');

        $anz_pos = count($arr_pos);
        echo "<table class=\"sortable\">";
        echo "<tr><th>RG</th><th>ARTIKEL</th><th>BEZ</th><th>MENGE</th><th>VE</th><th>BISHER</th><th>LPREIS</th><th>rabatt</th><th>UPREIS</th><th>ENT.</tr>";
        for ($a = 0; $a < $anz_pos; $a++) {
            $art_nr = $arr_pos [$a] ['ARTIKEL_NR'];
            $menge = $arr_pos [$a] ['G_MENGE'];
            $rg = $arr_pos [$a] ['BELEG_NR'];
            $r = new rechnung ();
            $art_arr = $r->artikel_info($partner_id, $art_nr);
            $ve = $art_arr [0] ['EINHEIT'];
            $bez = $art_arr [0] ['BEZEICHNUNG'];
            $lp = $art_arr [0] ['LISTENPREIS'];
            $rabatt = $art_arr [0] ['RABATT_SATZ'];
            $up = nummer_punkt2komma_t(($lp / 100) * (100 - $rabatt));
            $anz_bisher = $k->get_anz_bisher($art_nr, $partner_id);

            /* Preisentwicklungsinfos */
            $ka = new katalog ();
            $ka->get_preis_entwicklung_infos(session()->get('partner_id'), $art_nr);

            $link_rg = "<a href='" . route('web::rechnungen.show', ['id' => $rg]) . "'>zur Rg</a>";
            echo "<tr><td>$link_rg</td><td>$art_nr</td><td>$bez</td><td>$menge</td><td>$ve</td><td>$anz_bisher</td><td>$lp</td><td>$rabatt%</td><td>$up</td><td>$ka->vorzeichen" . "$ka->preis_diff%</td></tr>";
        }
        echo "</table>";

        break;
} // end switch
