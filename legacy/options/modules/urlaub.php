<?php

if (request()->has('option')) {
    $option = request()->input('option');
} else {
    $option = 'default';
}

/* Optionsschalter */
switch ($option) {

    // case "uebersicht":
    default :
        if (request()->has('jahr')) {
            $jahr = request()->input('jahr');
        }
        if (!isset ($jahr)) {
            $jahr = date("Y");
        }
        $vorjahr = $jahr - 1;
        $nachjahr = $jahr + 1;
        $link_vorjahr = "<a class='waves-effect waves-light btn' href='" . route('web::urlaub::legacy', ['option' => 'uebersicht', 'jahr' => $vorjahr]) . "'><i class=\"mdi mdi-arrow-left left\"></i>$vorjahr</a>";
        $link_nachjahr = "<a class='waves-effect waves-light btn' href='" . route('web::urlaub::legacy', ['option' => 'uebersicht', 'jahr' => $nachjahr]) . "'><i class=\"mdi mdi-arrow-right right\"></i>$nachjahr</a>";
        $pdf_link = "<a class='waves-effect waves-light btn' href='" . route('web::urlaub::legacy', ['option' => 'uebersicht_pdf', 'jahr' => $jahr]) . "'>PDF</a>";
        echo "<div class='left-align'>";
        echo "$link_vorjahr &nbsp;<b>Übersicht $jahr</b>&nbsp; $link_nachjahr $pdf_link";
        echo " </div>";
        $u = new urlaub ();
        $u->jahresuebersicht_anzeigen($jahr);
        break;

    case "uebersicht_pdf" :
        $u = new urlaub ();
        $jahr = request()->input('jahr');
        if (!$jahr) {
            $jahr = date("Y");
        }
        $u->jahresuebersicht_alle_pdf($jahr);
        break;

    case "urlaubsantrag" :
        $u = new urlaub ();
        $benutzer_id = request()->input('benutzer_id');
        if (empty ($benutzer_id)) {
            $benutzer_id = Auth::user()->id;
        }
        $u->form_urlaubsantrag($benutzer_id);
        break;

    case "urlaubsantrag_check" :

        $u = new urlaub ();
        $benutzer_id = request()->input('benutzer_id');
        $datum_a = date_german2mysql(request()->input('u_vom'));
        $datum_e = date_german2mysql(request()->input('u_bis'));
        $datum_a_arr = explode("-", $datum_a);
        $datum_e_arr = explode("-", $datum_e);
        $a_jahr = $datum_a_arr [0];
        $e_jahr = $datum_e_arr [0];
        // #echo "$a_jahr $e_jahr";
        if ($e_jahr < $a_jahr) {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\ErrorMessage("Enddatum kleiner als Anfangsdatum, bitte neu eingeben!")
            );
        }
        if ($e_jahr > $a_jahr) {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\ErrorMessage("Urlaub erstreckt sich über ein Jahr, bitte nur Urlaub innerhalb eines Kalenderjahres eingeben.")
            );
        } else {
            $art = request()->input('art');
            $u->tage_arr($benutzer_id, $datum_a, $datum_e, $art);
        }
        weiterleiten_in_sec(route('web::urlaub::legacy', ['option' => 'urlaubsantrag', 'benutzer_id' => $benutzer_id], false), 1);
        break;

    case "jahresansicht" :
        $u = new urlaub ();
        $benutzer_id = request()->input('benutzer_id');
        $jahr = request()->input('jahr');
        if (!empty ($benutzer_id) && !empty ($jahr)) {
            $u->jahres_ansicht($benutzer_id, $jahr);
        }
        break;

    case "jahresansicht_pdf" :
        $u = new urlaub ();
        $benutzer_id = request()->input('benutzer_id');
        $jahr = request()->input('jahr');
        if (!empty ($benutzer_id) && !empty ($jahr)) {
            $u->jahres_ansicht_pdf($benutzer_id, $jahr);
        }
        break;

    case "urlaubstag_loeschen" :
        $u = new urlaub ();
        $dat = request()->input('u_dat');
        $benutzer_id = request()->input('benutzer_id');
        $jahr = request()->input('jahr');
        if (!empty ($dat)) {
            $u->urlaubstag_loeschen($dat);
            weiterleiten_in_sec(route('web::urlaub::legacy', ['option' => 'jahresansicht', 'benutzer_id' => $benutzer_id, 'jahr' => $jahr], false), 1);
        } else {
            echo "Urlaubstag auswählen";
        }
        break;

    case "urlaubstag_loeschen_js" :
        $u = new urlaub ();
        $benutzer_id = request()->input('benutzer_id');
        $datum = date_german2mysql(request()->input('datum'));
        $u->urlaubstag_loeschen_datum($benutzer_id, $datum);
        break;

    case "monatsansicht" :
        $u = new urlaub ();
        if (request()->has('jahr')) {
            $jahr = request()->input('jahr');
        }
        if (request()->has('monat')) {
            $monat = request()->input('monat');
        }
        if (!isset ($monat)) {
            $monat = date("m");
        }
        if (!isset ($jahr)) {
            $jahr = date("Y");
        }
        $u->monatsansicht($monat, $jahr);
        break;

    case "monatsansicht_pdf" :
        $u = new urlaub ();
        $jahr = request()->input('jahr');
        $monat = request()->input('monat');
        if (empty ($monat)) {
            $monat = date("m");
        }
        if (empty ($jahr)) {
            $jahr = date("Y");
        }
        $u->monatsansicht_pdf($monat, $jahr);
        break;

    case "monatsansicht_pdf_mehrere" :
        $u = new urlaub ();
        $u->monatsansicht_pdf_mehrere(1, 12, 2010);
        break;

    case "monatsansicht_jahr" :
        $u = new urlaub ();
        $jahr = request()->input('jahr');

        if (empty ($jahr)) {
            $jahr = date("Y");
        }
        $vorjahr = $jahr - 1;
        $nachjahr = $jahr + 1;
        echo "<a href='" . route('web::urlaub::legacy', ['option' => 'monatsansicht_jahr', 'jahr' => $vorjahr], false) . "'> Übersicht $vorjahr </a> |  ";
        echo "<a href='" . route('web::urlaub::legacy', ['option' => 'monatsansicht_jahr', 'jahr' => $machjahr], false) . "'> Übersicht $nachjahr </a> ";
        for ($a = 1; $a <= 12; $a++) {
            $u->monatsansicht($a, $jahr);
        }
        break;

    case "urlaubsplan_jahr" :
        $u = new urlaub ();
        $jahr = request()->input('jahr');
        if (empty ($jahr)) {
            $jahr = date("Y");
        }
        $u->monatsansicht_pdf_mehrere(1, 12, $jahr);
        break;

    case "hochrechnung_mv" :
        $k = new kautionen ();
        $datum_bis = date_german2mysql(request()->input('datum_bis'));
        $mietvertrag_id = request()->input('mietvertrag_id');
        $k->kautionsberechnung('Mietvertrag', $mietvertrag_id, $datum_bis, 0.005, 25, 5.5);

        break;
}
