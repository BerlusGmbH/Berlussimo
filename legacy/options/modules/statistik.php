<?php

$option = request()->input('option');
switch ($option) {

    case "leer_vermietet_jahr" :
        $s = new statistik ();
        // $s->create_moths('2009');
        $s->get_stat('2010', '1', '');
        $s->get_stat('2011', '1', '');
        $s->get_stat('2012', '1', '');
        $s->get_stat('2013', '1', '');
        $s->get_stat('2014', '1', '');
        $s->get_stat('2015', '1', '');

        $s->get_stat('2010', '2', '');
        $s->get_stat('2011', '2', '');
        $s->get_stat('2012', '2', '');
        $s->get_stat('2013', '2', '');
        $s->get_stat('2014', '2', '');
        $s->get_stat('2015', '2', '');

        $s->get_stat('2010', '3', '');
        $s->get_stat('2011', '3', '');
        $s->get_stat('2012', '3', '');
        $s->get_stat('2013', '3', '');
        $s->get_stat('2014', '3', '');
        $s->get_stat('2015', '3', '');

        $s->get_stat('2010', '13', '');
        $s->get_stat('2011', '13', '');
        $s->get_stat('2012', '13', '');
        $s->get_stat('2013', '13', '');
        $s->get_stat('2014', '13', '');
        $s->get_stat('2015', '13', '');

        $s->get_stat('2010', '14', '');
        $s->get_stat('2011', '14', '');
        $s->get_stat('2012', '14', '');
        $s->get_stat('2013', '14', '');
        $s->get_stat('2014', '14', '');
        $s->get_stat('2015', '14', '');

        break;

    case "leer_haus_stat" :
        $s = new statistik ();
        $s->form_haus_leer_stat();

        break;

    case "leer_haus_stat1" :
        $s = new statistik ();
        $akt_jahr = date("Y");
        $start_j = $akt_jahr - 4;

        $h = new haus ();
        $h->get_haus_info(request()->input('haus_id'));
        echo "<h1>$h->haus_strasse $h->haus_nummer</h1>";
        for ($a = $start_j; $a <= $akt_jahr; $a++) {
            $s->stat_haus_anzeigen(request()->input('haus_id'), $a);
        }
        break;

    case "stellplaetze" :
        $objekt_id = request()->input('objekt_id');
        if (empty ($objekt_id)) {
            $objekt_id = 4; // Block E
        }
        $s = new statistik ();
        $s->get_stat('2008', $objekt_id, 'Stellplatz'); // Block E
        $s->get_stat('2009', $objekt_id, 'Stellplatz'); // Block E
        $s->get_stat('2010', $objekt_id, 'Stellplatz'); // Block E
        break;

    case "garage" :
        $objekt_id = request()->input('objekt_id');
        if (empty ($objekt_id)) {
            $objekt_id = 13; // GBN
        }
        $s = new statistik ();
        $s->get_stat('2008', $objekt_id, 'Garage'); // GBN
        $s->get_stat('2009', $objekt_id, 'Garage'); // GBN
        $s->get_stat('2010', $objekt_id, 'Garage'); // GBN
        break;

    case "keller" :
        $objekt_id = request()->input('objekt_id');
        if (empty ($objekt_id)) {
            $objekt_id = 1; // II
        }
        $s = new statistik ();
        $s->get_stat('2008', $objekt_id, 'Keller'); // II
        $s->get_stat('2009', $objekt_id, 'Keller'); // II
        $s->get_stat('2010', $objekt_id, 'Keller'); // II
        break;

    case "leer_gesamt" :

        for ($a = 2007; $a <= date("Y"); $a++) {

            $jahr = $a;
            for ($b = 1; $b < 13; $b++) {
                $monat = $b;
                if (strlen($monat) < 2) {
                    $monat = '0' . $monat;
                }
                $monatsname = monat2name($monat);
                echo "Leerstand gesamt $monatsname $jahr<br>";
                $s = new statistik ();
                echo $s->leerstand_alle("$jahr-$monat", '');
                echo "<hr>";
            }
        }
        break;

    case "sollmieten_aktuell" :
        $objekt_id = session()->get('objekt_id');
        if (empty ($objekt_id)) {
            $objekt_id = 4; // Block E
        }
        $s = new statistik ();
        $s->summe_sollmiete_alle();
        break;

    case "verwaltergebuehr_objekt" :
        $objekt_id = request()->input('objekt_id');
        if (empty ($objekt_id)) {
            $objekt_id = 4; // Block E
        }
        $s = new statistik ();
        $s->summe_sollmieten($objekt_id);
        break;

    case "verwaltergebuehr_objekt_pdf" :
        $objekt_id = request()->input('objekt_id');
        if (empty ($objekt_id)) {
            $objekt_id = 4; // Block E
        }
        $s = new statistik ();
        $s->summe_sollmieten_pdf($objekt_id);
        break;

    case "sollmieten_haeuser" :
        $s = new statistik ();
        $s->form_haeuser_auswahl();
        break;

    default :
        break;

    case "me_k" :
        $s = new statistik ();
        $jahr = 2010;
        session()->forget('daten_arr');
        // $s->stat_kosten_me_jahr($geldkonto_id, $jahr);
        $s->kosten_einnahmen_k('4', $jahr, 'II', 'Euro');
        $s->kosten_einnahmen_k('5', $jahr, 'III', 'Euro');
        $s->kosten_einnahmen_k('6', $jahr, 'V', 'Euro');
        $s->kosten_einnahmen_k('11', $jahr, 'Block E', 'Euro');
        $s->kosten_einnahmen_k('7', $jahr, 'GBN', 'Euro');
        $s->kosten_einnahmen_k('8', $jahr, 'HW', 'Euro');
        $s->kosten_einnahmen_k('10', $jahr, 'FON', 'Euro');
        break;

    case "testen" :
        $s = new statistik ();
        $bg = new berlussimo_global ();
        $link = route('web::statistik::legacy', ['option' => 'testen'], false);
        if (request()->has('jahr')) {
            $jahr = request()->input('jahr');
        } else {
            $jahr = date("Y");
        }
        if (request()->has('monat')) {
            $monat = request()->input('monat');
        } else {
            $monat = date("m");
        }
        $bg->monate_jahres_links($jahr, $link);
        $s->vermietete_monat_jahr_neu($jahr, $monat);
        break;

    case "baustelle_manuell" :
        $s = new statistik ();
        $f = new formular ();
        $f->erstelle_formular("Baustelenübersicht", NULL);
        $s->baustellen_leistung('Einheit', '166', 25, '2009-11-01', '2009-11-31', 'Plötzer Sansi 53');
        $s->baustellen_leistung('Einheit', '368', 25, '2010-04-12', '2010-05-30', 'Laßnack Badsanierung MODs');
        $f->ende_formular();

    case "baustelle" :
        $s = new statistik ();
        $f = new formular ();
        $f->erstelle_formular("Baustelenübersicht", NULL);
        $s->baustellen_uebersicht2(600);
        $s->baustellen_uebersicht();
        $f->ende_formular();
        break;

    case "fenster" :
        $s = new statistik ();
        $f = new formular ();
        $f->erstelle_formular("Fensterübersicht", NULL);
        $s->fenster_uebersicht();
        $f->ende_formular();
        break;

    case "fenster_zuweisen" :
        if (request()->exists('sndBtn')) {
            if (request()->input('rest') < request()->input('anz_fenster')) {
                throw new \App\Exceptions\MessageException(
                    new \App\Messages\ErrorMessage("Eingegebene Menge größer als Restmenge!")
                );
            } else {
                $s = new statistik ();
                if ($s->fenster_zuweisen(request()->input('beleg_id'), request()->input('pos'), request()->input('anz_fenster'), request()->input('Einheit'))) {
                    weiterleiten(route('web::statistik::legacy', ['option' => 'fenster'], false));
                }
            }
        }
        break;

    case "lieferung_eingeben" :
        if (request()->exists('lsndBtn')) {
            if (request()->has('beleg_id_l') && request()->has('pos_l')) {
                $s = new statistik ();
                if ($s->lieferung_speichern(request()->input('beleg_id_l'), request()->input('pos_l'))) {
                    weiterleiten(route('web::statistik::legacy', ['option' => 'fenster'], false));
                } else {
                    weiterleiten_in_sec(route('web::statistik::legacy', ['option' => 'fenster'], false), 3);
                }
            } else {
                fehlermeldung_ausgeben("BelegID und Position eingeben");
            }
        }
        break;

    case "lieferung_loeschen" :
        if (request()->has('beleg_id') && request()->has('pos')) {
            $s = new statistik ();
            if ($s->lieferung_loeschen(request()->input('beleg_id'), request()->input('pos'))) {
                weiterleiten(route('web::statistik::legacy', ['option' => 'fenster'], false));
            } else {
                weiterleiten_in_sec(route('web::statistik::legacy', ['option' => 'fenster'], false), 3);
            }
        } else {
            fehlermeldung_ausgeben("Eingabe unvollständig Z261");
            weiterleiten_in_sec(route('web::statistik::legacy', ['option' => 'fenster'], false), 3);
        }
        break;

    case "zuweisung_loeschen" :
        if (request()->has('beleg_id') && request()->has('pos') && request()->has('einheit_id')) {
            $s = new statistik ();
            if ($s->zuweisung_loeschen(request()->input('beleg_id'), request()->input('pos'), request()->input('einheit_id'))) {
                weiterleiten(route('web::statistik::legacy', ['option' => 'fenster'], false));
            } else {
                weiterleiten_in_sec(route('web::statistik::legacy', ['option' => 'fenster'], false), 3);
            }
        } else {
            fehlermeldung_ausgeben("Eingabe unvollständig Z262");
            weiterleiten_in_sec(route('web::statistik::legacy', ['option' => 'fenster'], false), 3);
        }
        break;

    case "bau_stat_menu" :
        $s = new statistik ();
        $s->form_einheit_suche();
        break;

    case "einheit_suche_bau" :
        if (request()->has('einheit_bez')) {
            $e = new einheit ();
            $e->get_einheit_id(request()->input('einheit_bez'));
            if (isset ($e->einheit_id)) {
                $s = new statistik ();
                $s->kontrolle_bau_tab('Einheit', $e->einheit_id);
            } else {
                echo "nicht gefunden";
            }
        }
        break;
}
