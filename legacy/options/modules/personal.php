<?php

if (request()->has('option')) {
    $option = request()->input('option');
} else {
    $option = 'default';
}
/* Optionsschalter */
switch ($option) {

    default :
        echo "WEITERE WAHL TREFFEN!";
        break;

    case "lohn_gehalt_sepa" :
        $pe = new personal ();
        $sep = new sepa ();
        $sep->sepa_sammler_anzeigen(session()->get('geldkonto_id'), 'LOHN');
        $pe->form_lohn_gehalt_sepa();
        break;

    case "sepa_sammler_hinzu" :
        $sep = new sepa ();
        $vzweck = request()->input('vzweck');
        $von_gk_id = request()->input('gk_id');
        session()->put('geldkonto_id', $von_gk_id);
        $an_sepa_gk_id = request()->input('empf_sepa_gk_id');
        $kat = request()->input('kat');
        $kos_typ = request()->input('kos_typ');
        $kos_id = request()->input('kos_id');
        $konto = request()->input('konto');
        $betrag = nummer_komma2punkt(request()->input('betrag'));
        if ($betrag < 0) {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\ErrorMessage('ABBRUCH MINUSBETRAG')
            );
        }
        if ($sep->sepa_ueberweisung_speichern($von_gk_id, $an_sepa_gk_id, $vzweck, $kat, $kos_typ, $kos_id, $konto, $betrag) == false) {
            fehlermeldung_ausgeben("AUFTRAG KONNTE NICHT GESPEICHERT WERDEN!");
        } else {
            if ($kat == 'RECHNUNG') {
                weiterleiten(route('web::sepa::legacy', ['option' => 'sammler_anzeigen'], false));
            }
            if ($kat == 'ET-AUSZAHLUNG') {
                weiterleiten(route('web::listen::legacy', ['option' => 'sammler_anzeigen'], false));
            }
            if ($kat == 'LOHN') {
                weiterleiten(route('web::personal::legacy', ['option' => 'lohn_gehalt_sepa'],false));
            }
            if ($kat == 'KK') {
                weiterleiten(route('web::personal::legacy', ['option' => 'kk'],false));
            }
            if ($kat == 'STEUERN') {
                weiterleiten(route('web::personal::legacy', ['option' => 'steuern'],false));
            }
        }
        break;

    case "kk" :
        $pe = new personal ();
        $sep = new sepa ();
        $sep->sepa_sammler_anzeigen(session()->get('geldkonto_id'), 'KK');
        $pe->form_krankenkassen();
        break;

    case "steuern" :
        $pe = new personal ();
        $sep = new sepa ();
        $sep->sepa_sammler_anzeigen(session()->get('geldkonto_id'), 'STEUERN');
        $pe->form_finanzamt();
        break;
}
