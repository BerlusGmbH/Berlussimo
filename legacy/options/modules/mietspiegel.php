<?php

if (request()->has('option')) {
    $option = request()->input('option');
} else {
    $option = 'default';
}

/* Optionsschalter */
switch ($option) {

    default :
        break;

    case "mietspiegelliste" :
        $ms = new mietspiegel();
        $ms->liste_mietspiegel();
        break;

    case "mietspiegel_anzeigen" :
        $ms = new mietspiegel();
        $jahr = request()->input('jahr');
        if (request()->has('ort')) {
            $ort = request()->input('ort');
        } else {
            $ort = null;
        }
        $ms->mietspiegel_anzeigen($jahr, $ort);
        $ms->abzuege_anzeigen($jahr, $ort);
        break;

    case "neuer_mietspiegel" :
        $ms = new mietspiegel ();
        $ms->form_neuer_mietspiegel();
        break;

    case "ms_speichern" :
        if (request()->has('jahr')) {
            $jahr = request()->input('jahr');
            if (request()->has('ort')) {
                $ort = request()->input('ort');
                $ms = new mietspiegel();
                $ms->ms_speichern($jahr, $ort);
                weiterleiten(route('legacy::mietspiegel::index', ['option' => 'mietspiegel_anzeigen', 'jahr' => $jahr, 'ort' => $ort], false));
            } else {
                $ms = new mietspiegel();
                $ms->ms_speichern($jahr, null);
                weiterleiten(route('legacy::mietspiegel::index', ['option' => 'mietspiegel_anzeigen', 'jahr' => $jahr], false));
            }
        }
        break;

    case "ms_wert_speichern" :
        if (request()->has('jahr')) {
            $jahr = request()->input('jahr');
            if (request()->has('ort')) {
                $ort = request()->input('ort');

                if (request()->has('feld') && request()->has('u_wert') && request()->has('m_wert') && request()->has('o_wert')) {
                    $ms = new mietspiegel();
                    $ms->ms_speichern($jahr, $ort, request()->input('feld'), request()->input('u_wert'), request()->input('m_wert'), request()->input('o_wert'));
                    weiterleiten(route('legacy::mietspiegel::index', ['option' => 'mietspiegel_anzeigen', 'jahr' => $jahr, 'ort' => $ort], false));
                } else {
                    fehlermeldung_ausgeben("Alle Felder ausfüllen");
                }
            } else {
                if (request()->has('feld') && request()->has('u_wert') && request()->has('m_wert') && request()->has('o_wert')) {
                    $ms = new mietspiegel();
                    $ms->ms_speichern($jahr, null, request()->input('feld'), request()->input('u_wert'), request()->input('m_wert'), request()->input('o_wert'));
                } else {
                    fehlermeldung_ausgeben("Alle Felder ausfüllen");
                }
                weiterleiten(route('legacy::mietspiegel::index', ['option' => 'mietspiegel_anzeigen', 'jahr' => $jahr], false));
            }
        }

        break;

    case "abzug_speichern" :
        if (request()->has('jahr') && request()->has('merkmal') && request()->has('wert') && request()->has('a_klasse')) {
            $ms = new mietspiegel();
            if (request()->has('ort')) {
                $betrag = nummer_komma2punkt(request()->input('wert'));
                $ms->sonderabzug_speichern(request()->input('jahr'), request()->input('merkmal'), $betrag, request()->input('a_klasse'), request()->input('ort'));
                $jahr = request()->input('jahr');
                $ort = request()->input('ort');
                weiterleiten(route('legacy::mietspiegel::index', ['option' => 'mietspiegel_anzeigen', 'jahr' => $jahr, 'ort' => $ort], false));
            } else {
                $betrag = nummer_komma2punkt(request()->input('wert'));
                $ms->sonderabzug_speichern(request()->input('jahr'), request()->input('merkmal'), $betrag, request()->input('a_klasse'), null);
                $jahr = request()->input('jahr');
                weiterleiten(route('legacy::mietspiegel::index', ['option' => 'mietspiegel_anzeigen', 'jahr' => $jahr], false));
            }
        }
        break;

    case "ms_wert_del" :
        if (request()->has('dat')) {
            $ms = new mietspiegel();
            $dat = request()->input('dat');
            $ms->ms_wert_loeschen($dat);
            weiterleiten(route('legacy::mietspiegel::index', ['option' => 'mietspiegelliste'], false));
        }
        break;

    case "del_sonderabzug" :
        if (request()->has('dat')) {
            $ms = new mietspiegel();
            $dat = request()->input('dat');
            $ms->ms_sonderabzug_loeschen($dat);
            weiterleiten(route('legacy::mietspiegel::index', ['option' => 'mietspiegelliste'], false));
        }
        break;
}