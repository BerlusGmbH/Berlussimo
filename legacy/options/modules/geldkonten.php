<?php

if (request()->has('option')) {
    $option = request()->input('option');
} else {
    $option = 'default';
}
switch ($option) {

    case "uebersicht_ea" :
        $form = new mietkonto ();
        $form->erstelle_formular("Geldkontenübersicht", NULL);
        $geldkonten = new geldkonto_info ();
        $geldkonten->alle_geldkonten_tabelle();
        $form->ende_formular();
        break;

    case "gk_neu" :
        $gk = new gk ();
        $gk->form_geldkonto_neu();
        break;

    case "new_gk" :
        if (request()->has('g_bez') && request()->has('beguenstigter') && request()->has('kontonummer') && request()->has('blz') && request()->has('institut') && request()->has('kostentraeger_typ') && request()->has('kostentraeger_id')) {
            $gk = new gk ();
            $b = new buchen ();
            $g_bez = request()->input('g_bez');
            $beguenstigter = request()->input('beguenstigter');
            $kontonummer = request()->input('kontonummer');
            $blz = request()->input('blz');
            $institut = request()->input('institut');
            $iban = request()->input('iban');
            $bic = request()->input('bic');
            $sep = new sepa ();
            $iban_mysql = $sep->iban_convert($iban, 1);
            $kostentraeger_typ = request()->input('kostentraeger_typ');
            $kostentraeger_bez = request()->input('kostentraeger_id');
            $kos_id = $b->kostentraeger_id_ermitteln($kostentraeger_typ, $kostentraeger_bez);
            $gk->geldkonto_speichern($kostentraeger_typ, $kos_id, $g_bez, $beguenstigter, $kontonummer, $blz, $institut, $iban_mysql, $bic);
            weiterleiten(route('web::geldkonten::legacy', ['option' => 'uebersicht_zuweisung'], false));
        } else {
            echo "Eingabe unvollständig Error: 621ghp";
        }
        break;

    case "gk_aendern" :
        if (request()->has('gk_id')) {
            $gk_id = request()->input('gk_id');
            $gk = new gk ();
            $gk->form_geldkonto_edit($gk_id);
        } else {
            fehlermeldung_ausgeben("Geldkonto wählen");
        }
        break;

    case "gk_update" :
        if (request()->has('gk_id') && request()->has('g_bez') && request()->has('beguenstigter') && request()->has('kontonummer') && request()->has('blz') && request()->has('institut') && request()->has('iban') && request()->has('bic')) {
            $gk = new gk ();
            $b = new buchen ();
            $gk_id = request()->input('gk_id');
            $g_bez = request()->input('g_bez');
            $beguenstigter = request()->input('beguenstigter');
            $kontonummer = request()->input('kontonummer');
            $blz = request()->input('blz');
            $institut = request()->input('institut');
            $iban = request()->input('iban');
            $bic = request()->input('bic');
            $sep = new sepa();
            $iban_mysql = $sep->iban_convert($iban, 1);
            $gk->geldkonto_update($gk_id, $g_bez, $beguenstigter, $kontonummer, $blz, $institut, $iban_mysql, $bic);
            weiterleiten(route('web::geldkonten::legacy', false));
        } else {
            echo "Eingabe unvollständig Error: Modul GK 115";
        }

        break;

    case "update_iban_bic" :
        $gk = new gk ();
        $gk->update_iban_bic_alle();
        break;

    case "gk_zuweisen" :
        $gk = new gk ();
        $gk->form_geldkonto_zuweisen();
        break;

    case "uebersicht_zuweisung" :
        $gk = new gk ();
        $gk->uebersicht_zuweisung();
        break;

    case "zuweisen_gk" :
        if (request()->has('geldkonto_id')
            && request()->has('kostentraeger_typ')
            && request()->has('kostentraeger_id')
            && request()->has('von')
        ) {
            $gk = new gk ();
            $b = new buchen ();
            $geldkonto_id = request()->input('geldkonto_id');
            $kostentraeger_typ = request()->input('kostentraeger_typ');
            $kostentraeger_bez = request()->input('kostentraeger_id');
            $von = request()->input('von');
            $bis = request()->input('bis');
            $vwzk = request()->input('vwzk');
            $kos_id = $b->kostentraeger_id_ermitteln($kostentraeger_typ, $kostentraeger_bez);
            if ($gk->check_zuweisung_kos($geldkonto_id, $kostentraeger_typ, $kos_id)) {
                echo "Zuweisung existiert bereits.";
            } else {
                $gk->zuweisung_speichern($kostentraeger_typ, $kos_id, $geldkonto_id, $von, $bis, $vwzk);
                weiterleiten(route('web::geldkonten::legacy', ['option' => 'uebersicht_zuweisung'], false));
            }
        } else {
            echo "Eingabe unvollständig Error: 623gd";
        }
        break;

    case "aendern_gk" :
        if (request()->has('geldkonto_id')
            && request()->has('kostentraeger_typ')
            && request()->has('kostentraeger_id')
            && request()->has('von')
            && request()->has('zuweisung_id')
        ) {
            DB::table('GELD_KONTEN_ZUWEISUNG')
                ->where('ZUWEISUNG_ID', request()->input('zuweisung_id'))
                ->where('AKTUELL', '1')
                ->update([
                    'KONTO_ID' => request()->input('geldkonto_id'),
                    'KOSTENTRAEGER_TYP' => request()->input('kostentraeger_typ'),
                    'KOSTENTRAEGER_ID' => request()->input('kostentraeger_id'),
                    'VERWENDUNGSZWECK' => request()->input('vwzk'),
                    'VON' => request()->input('von'),
                    'BIS' => (request()->input('bis') !== '' ? request()->input('bis') : null)
                ]);
            //weiterleiten(route('web::geldkonten::legacy', ['option' => 'uebersicht_zuweisung'], false));
        } else {
            echo "Eingabe unvollständig Error: 623gd";
        }
        break;

    case "zuweisung_loeschen" :
        if (request()->has('geldkonto_id') && request()->has('kos_typ') && request()->input('kos_id')) {
            $gk = new gk ();
            $geldkonto_id = request()->input('geldkonto_id');
            $kos_typ = request()->input('kos_typ');
            $kos_id = request()->input('kos_id');
            $gk->zuweisung_aufheben($kos_typ, $kos_id, $geldkonto_id);
            weiterleiten(route('web::geldkonten::legacy', ['option' => 'uebersicht_zuweisung'], false));
        } else {
            echo "Eingabe unvollständig Error: 623gf1";
        }
        break;

    case "zuweisung_aendern" :
        if (request()->has('zuweisung_id')) {
            $gk = new gk ();
            $gk->form_geldkonto_zuweisen(request()->input('zuweisung_id'));
        }
        break;

    default :
        $form = new mietkonto ();
        $form->erstelle_formular("Geldkontostände AKTUELL", NULL);
        $geldkonten = new geldkonto_info ();
        $geldkonten->alle_geldkonten_tabelle_kontostand();
        $form->ende_formular();
        break;
}
