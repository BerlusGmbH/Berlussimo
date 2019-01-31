<?php

$option = request()->input('option');
switch ($option) {

    default :
        $f = new formular ();
        $f->fieldset("Kontenrahmenübersicht", 'kontenrahmen');
        $konten_info = new k_rahmen ();
        $konten_info->kontenrahmen_liste_anzeigen();
        $f->fieldset_ende();
        break;

    case "kontenrahmen_uebersicht" :
        $f = new formular ();
        $f->fieldset("Kontenrahmenübersicht", 'kontenrahmen');
        $konten_info = new k_rahmen ();
        $konten_info->kontenrahmen_liste_anzeigen();
        $f->fieldset_ende();
        break;

    case "konten_anzeigen" :
        if (request()->filled('k_id')) {
            $konten_info = new k_rahmen ();
            if (!request()->exists('pdf')) {
                $f = new formular ();
                $f->fieldset("Kostenkontenübersicht", 'kostenkonten');
                $konten_info->konten_liste_anzeigen(request()->input('k_id'));
                $f->fieldset_ende();
            } else {
                $konten_info->konten_liste_anzeigen_pdf(request()->input('k_id'));
            }
        } else {
            echo "Keine Kostenkonten im Kontenrahmen erstellt";
        }
        break;

    case "kontenrahmen_neu" :
        $konten_info = new k_rahmen ();
        $konten_info->form_kontenrahmen_neu();
        break;

    case "k_bez_neu" :
        if (request()->filled('k_bez')) {
            $k_bez = request()->input('k_bez');
            $k = new k_rahmen ();
            if ($k->kontenrahmen_speichern($k_bez)) {
                weiterleiten(route('web::kontenrahmen::legacy', [], false));
            }
        } else {
            echo "Geben Sie bitte eine Kontenrahmenbezeichnung ein.";
        }
        break;

    case "kostenkonto_neu" :
        $k = new k_rahmen ();
        $k->form_kostenkonto_neu();
        break;

    case "konto_neu" :
        if (request()->filled('konto')
            && request()->filled('bez')
            && request()->filled('kontenrahmen_id')
        ) {
            $k = new k_rahmen ();
            session()->put('kontenrahmen_id', request()->input('kontenrahmen_id'));
            session()->put('k_gruppen_id', request()->input('k_gruppe'));
            session()->put('k_kontoart_id', request()->input('kontoart_id'));
            $k->kostenkonto_speichern(request()->input('kontenrahmen_id'), request()->input('konto'), request()->input('bez'), request()->input('kontoart_id'), request()->input('k_gruppe'), request()->filled('su'));
            weiterleiten(route('web::kontenrahmen::legacy', ['option' => 'konten_anzeigen', 'k_id' => session()->get('kontenrahmen_id')], false));
        } else {
            echo "Eingabe unvollständig. Error: S562q357";
        }
        break;

    case "kostenkonto_ae" :
        if (request()->filled('k_dat')) {
            $k = new k_rahmen ();
            $k->form_kostenkonto_aendern(request()->input('k_dat'));
        }
        break;

    case "konto_ae_send" :
        if (request()->filled('dat')
            && request()->filled('konto')
            && request()->filled('bez')
            && session()->has('kontenrahmen_id')
        ) {
            $k = new k_rahmen ();
            session()->put('kontenrahmen_id', request()->input('kontenrahmen_id'));
            session()->put('k_gruppen_id', request()->input('k_gruppe'));
            session()->put('k_kontoart_id', request()->input('kontoart_id'));
            $k->kostenkonto_aendern(request()->input('dat'), request()->input('kontenrahmen_id'), request()->input('konto'), request()->input('bez'), request()->input('kontoart_id'), request()->input('k_gruppe'), request()->filled('su'));
            weiterleiten(route('web::kontenrahmen::legacy', ['option' => 'konten_anzeigen', 'k_id' => session()->get('kontenrahmen_id')], false));
        } else {
            echo "Eingabe unvollständig. Error: S56sdf7";
        }

        break;

    case "gruppen" :
        $k = new k_rahmen ();
        $k->gruppen_anzeigen();
        break;

    case "gruppe_neu" :
        $k = new k_rahmen ();
        $k->form_gruppe_neu();
        $k->gruppen_anzeigen();
        break;

    case "g_bez_neu" :
        if (request()->filled('g_bez')) {
            $k = new k_rahmen ();
            $k->gruppe_speichern(request()->input('g_bez'));
            weiterleiten(route('web::kontenrahmen::legacy', ['option' => 'gruppe_neu'], false));
        } else {
            echo "Eingabe unvollständig. Error: 123sdf7";
        }
        break;

    case "kontoarten" :
        $k = new k_rahmen ();
        $k->kontoarten_anzeigen();
        break;

    case "kontoart_neu" :
        $k = new k_rahmen ();
        $k->form_kontoart_neu();
        $k->kontoarten_anzeigen();
        break;

    case "kontoart_neu1" :
        if (request()->filled('kontoart')) {
            $k = new k_rahmen ();
            $k->kontoart_speichern(request()->input('kontoart'));
            weiterleiten(route('web::kontenrahmen::legacy', ['option' => 'kontoart_neu'], false));
        } else {
            echo "Eingabe unvollständig. Error: 94555f7";
        }
        break;

    case "kontenrahmen_zuweisen" :
        $k = new k_rahmen ();
        $k->form_kontenrahmen_zuweisen();
        break;

    case "zuweisen_kr" :
        if (request()->filled('kostentraeger_typ') && request()->filled('kostentraeger_id') && request()->filled('kontenrahmen_id')) {
            $k = new k_rahmen ();
            $k->zuweisung_speichern(request()->input('kostentraeger_typ'), request()->input('kostentraeger_id'), request()->input('kontenrahmen_id'));
            weiterleiten(route('web::kontenrahmen::legacy', [], false));
        } else {
            echo "Eingabe unvollständig. Error: 42gsbx3f7";
        }
        break;

    case "zuweisung_del" :
        if (request()->filled('dat')) {
            $k = new k_rahmen ();
            $k->zuweisung_loeschen(request()->input('dat'));
            weiterleiten(route('web::kontenrahmen::legacy', [], false));
        } else {
            echo "Eingabe unvollständig. Error: 42gsjklasd7";
        }
        break;
}
