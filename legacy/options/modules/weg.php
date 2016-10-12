<?php

if (request()->has('objekt_id')) {
    session()->put('objekt_id', request()->input('objekt_id'));
}

if (request()->has('option')) {
    $option = request()->input('option');
} else {
    $option = 'default';
}

/* Optionsschalter */
switch ($option) {

    default :
        if (session()->has('objekt_id')) {
            $weg = new weg ();
            $o = new objekt ();
            $o->get_objekt_infos(session()->get('objekt_id'));

            $einheiten_arr = $weg->einheiten_weg_tabelle_arr(session()->get('objekt_id'));
            $anz = count($einheiten_arr);

            for ($a = 0; $a < $anz; $a++) {
                $einheit_id = $einheiten_arr [$a] ['EINHEIT_ID'];
                $weg->get_last_eigentuemer($einheit_id);
                if (isset ($weg->eigentuemer_id)) {

                    $et_p_id = $weg->get_person_id_eigentuemer_arr($weg->eigentuemer_id);
                    if (!empty($et_p_id)) {
                        $anz_pp = count($et_p_id);
                        for ($pe = 0; $pe < $anz_pp; $pe++) {
                            $et_p_id_1 = $et_p_id [$pe] ['PERSON_ID'];
                            $detail = new detail ();
                            if (($detail->finde_detail_inhalt('PERSON', $et_p_id_1, 'Email'))) {
                                $email_arr = $detail->finde_alle_details_grup('PERSON', $et_p_id_1, 'Email');
                                for ($ema = 0; $ema < count($email_arr); $ema++) {
                                    $em_adr = $email_arr [$ema] ['DETAIL_INHALT'];
                                    $emails_arr [] = $em_adr;
                                }
                            }
                        }
                    }
                }
            }

            if (is_array($emails_arr)) {
                $emails_arr_u = array_values(array_unique($emails_arr));
                $anz = count($emails_arr_u);
                echo "<a href=\"mailto:?bcc=";
                for ($a = 0; $a < $anz; $a++) {
                    $email = $emails_arr_u [$a];
                    echo "$email";
                    if ($a < $anz - 1) {
                        echo ",";
                    }
                }
                echo "\">Email an alle Eigentümer ($anz Emailadressen)</a>";
            }
        }
        break;

    case "objekt_auswahl" :
        $weg = new weg ();
        $weg->liste_weg_objekte();
        break;

    case "einheiten" :
        if (session()->has('objekt_id')) {
            $weg = new weg ();
            $weg->einheiten_weg_tabelle_anzeigen(session()->get('objekt_id'));
        } else {
            weiterleiten(route('legacy::weg::index', ['option' => 'objekt_auswahl'], false));
        }
        break;

    case "einheit_uebersicht" :
        if (request()->has('einheit_id')) {
            $weg = new weg ();
            $weg->uebersicht_einheit(request()->input('einheit_id'));
        } else {
            echo 'Einheit wählen.';
        }
        break;

    case "eigentuemer_wechsel" :
        if (session()->has('objekt_id')) {
            $weg = new weg ();
            $weg->form_eigentuemer_einheit(session()->get('objekt_id'));
        } else {
            echo "'Bitte eine WEG wählen.'";
        }
        break;

    case "eigentuemer_aendern" :
        if (request()->has('eigentuemer_id')) {
            $weg = new weg ();
            $weg->form_eigentuemer_aendern(request()->input('eigentuemer_id'));
        } else {
            echo 'Bitte den Eigentümer wählen.';
        }
        break;

    case "eigentuemer_send_aendern" :
        $weg = new weg ();

        if (request()->has('et_id')) {
            $et_id = request()->input('et_id');
        } else {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\ErrorMessage('Eigentümer ID fehlt')
            );
        }

        if (request()->has('einheit_id')) {
            $einheit_id = request()->input('einheit_id');
        } else {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\ErrorMessage('Einheit nicht gewählt')
            );
        }

        if (request()->has('z_liste')) {
            $eigent_arr = request()->input('z_liste');
        } else {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\ErrorMessage('Personen nicht gewählt')
            );
        }

        if (request()->has('eigentuemer_seit')) {
            $eigentuemer_von = request()->input('eigentuemer_seit');
        } else {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\ErrorMessage('Datum Eigentümer SEIT fehlt!')
            );
        }

        if (request()->has('eigentuemer_bis')) {
            $eigentuemer_bis = request()->input('eigentuemer_bis');
        } else {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\ErrorMessage('Datum Eigentümer BIS fehlt!')
            );
        }

        $weg->eigentuemer_aendern_db($et_id, $einheit_id, $eigent_arr, $eigentuemer_von, $eigentuemer_bis);
        weiterleiten(route('legacy::weg::index', ['option' => 'einheiten'], false));
        break;

    case "eigentuemer_send" :
        if (is_array(request()->input('z_liste'))) {
            if (request()->has('einheit_id') && request()->has('eigentuemer_seit')) {
                $einheit_id = request()->input('einheit_id');
                $eigentuemer_seit = request()->input('eigentuemer_seit');
                $eigentuemer_bis = request()->input('eigentuemer_bis');
                $eigent_arr = request()->input('z_liste');
                $weg = new weg ();
                if (!isset ($eigentuemer_bis) or empty ($eigentuemer_bis)) {
                    $eigentuemer_bis = '00.00.0000';
                }
                $weg->eigentuemer_speichern($einheit_id, $eigent_arr, $eigentuemer_seit, $eigentuemer_bis);
                weiterleiten(route('legacy::weg::index', ['option' => 'einheiten'], false));
            }
        } else {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\ErrorMessage('Neue Eigentümer wählen!')
            );
        }
        break;

    case "wohngeld_buchen_auswahl_e" :
        if (session()->has('objekt_id')) {
            $w = new weg ();
            if (!request()->has('monat')) {
                $monat = date("m");
            }
            if (!request()->has('jahr')) {
                $jahr = date("Y");
            }
            $w->form_wg_einheiten($monat, $jahr, session()->get('objekt_id'));
        } else {
            echo "Objekt auswählen";
        }
        break;

    case "wohngeld_buchen_maske" :
        if (request()->has('einheit_id')) {
            $w = new weg ();
            if (!request()->has('monat')) {
                $monat = date("m");
            } else {
                $monat = request()->input('monat');
            }
            if (!request()->has('jahr')) {
                $jahr = date("Y");
            } else {
                $jahr = request()->input('jahr');
            }

            $w->form_wohngeld_buchen($monat, $jahr, request()->input('einheit_id'));
        } else {
            echo "Einheit wählen";
        }
        break;

    case "wohngeld_definieren" :
        if (request()->has('einheit_id')) {
            $w = new weg ();
            $w->form_wg_definition_neu(request()->input('einheit_id'));
        } else {
            echo "Einheit wählen";
        }
        break;

    case "wg_def_exists" :
        if (request()->has('einheit_id') && request()->has('von') && request()->has('betrag') && request()->has('kostenart')) {
            $w = new weg ();
            $von = request()->input('von');
            $bis = request()->input('bis');
            $betrag = request()->input('betrag');
            $e_konto_arr = explode('|', request()->input('kostenart'));
            $e_konto = $e_konto_arr [0];
            $kostenkat = $e_konto_arr [1];
            $gruppe = $e_konto_arr [2];
            $g_konto = $e_konto_arr [3];
            $einheit_id = request()->input('einheit_id');
            $w->wohngeld_def_speichern($von, $bis, $betrag, $kostenkat, $e_konto, $gruppe, $g_konto, $einheit_id);
            echo "Ihre Eingabe wurde gespeichert, sie werden zur Eingabemaske weitergeleitet.";
        } else {
            echo "Dateneingabe unvollständig";
        }
        if (request()->has('einheit_id')) {
            $einheit_id = request()->input('einheit_id');
            weiterleiten_in_sec(route('legacy::weg::index', ['option' => 'wohngeld_definieren', 'einheit_id' => $einheit_id], false), 2);
        }

        break;

    case "wg_def_neu" :

        if (request()->has('einheit_id')
            && request()->has('von')
            && request()->has('betrag')
            && request()->has('kostenkat')
            && request()->has('e_konto')
            && request()->has('gruppe')
            && request()->has('g_konto')
        ) {
            $w = new weg ();
            $von = request()->input('von');
            $bis = request()->input('bis');
            $betrag = request()->input('betrag');
            $kostenkat = request()->input('kostenkat');
            $e_konto = request()->input('e_konto');
            $gruppe = request()->input('gruppe');
            $g_konto = request()->input('g_konto');
            $einheit_id = request()->input('einheit_id');
            $w->wohngeld_def_speichern($von, $bis, $betrag, $kostenkat, $e_konto, $gruppe, $g_konto, $einheit_id);
            echo "Ihre Eingabe wurde gespeichert, sie werden zur Eingabemaske weitergeleitet.";
        } else {
            echo "Dateneingabe unvollständig";
        }

        if (request()->has('einheit_id')) {
            $einheit_id = request()->input('einheit_id');
            weiterleiten_in_sec(route('legacy::weg::index', ['option' => 'wohngeld_definieren', 'einheit_id' => $einheit_id], false), 2);
        }
        break;

    case "wg_buchen_send" :
        if (request()->has('eigentuemer_id')
            && request()->has('einheit_id')
            && request()->has('geld_konto')
            && request()->has('datum')
            && request()->has('kontoauszugsnr')
            && is_array(request()->input('def_array'))
            && is_array(request()->input('text_array'))
            && request()->has('wohngeld')
            && request()->has('g_konto')
            && request()->has('b_text')
        ) {
            $eigentuemer_id = request()->input('eigentuemer_id');
            $einheit_id = request()->input('einheit_id');
            $geldkonto_id = request()->input('geld_konto');
            $datum = request()->input('datum');
            $kontoauszugsnr = request()->input('kontoauszugsnr');
            $def_array = request()->input('def_array');
            $def_b_texte = request()->input('text_array');
            $wg_g_konto = request()->input('g_konto');
            $wg_g_betrag = request()->input('wohngeld');
            $b_text = request()->input('b_text');
            $w = new weg ();
            $w->wohngeld_buchung_speichern($eigentuemer_id, $einheit_id, $geldkonto_id, $datum, $kontoauszugsnr, $def_array, $def_b_texte, $wg_g_konto, $wg_g_betrag, $b_text);
        } else {
            echo "Buchungsdaten sind unvollständig";
        }
        break;

    case "hausgeld_kontoauszug" :
        if (request()->has('eigentuemer_id')) {
            $w = new weg ();
            $w->hausgeld_kontoauszug(request()->input('eigentuemer_id'));
        } else {
            echo "Einheit wählen";
        }
        break;

    case "wohngeld_def_del" :
        if (request()->has('dat') && request()->has('einheit_id')) {
            $w = new weg ();
            $w->wohngeld_def_delete(request()->input('dat'));
            $einheit_id = request()->input('einheit_id');
            weiterleiten(route('legacy::weg::index', ['option' => 'wohngeld_definieren', 'einheit_id' => $einheit_id], false));
        } else {
            echo "Hausgelddefintion wählen!";
        }
        break;

    case "wohngeld_def_aendern" :
        if (request()->has('dat')) {
            $w = new weg ();
            $w->form_wohngeld_def_edit(request()->input('dat'));
        } else {
            echo "Hausgelddefintion wählen!";
        }
        break;

    case "wg_def_edit" :
        if (request()->has('dat') && request()->input('kostenart') != 'Bitte wählen') {
            $dat = request()->input('dat');
            $id = request()->input('id');
            $kos_typ = request()->input('kos_typ');
            $kos_id = request()->input('kos_id');
            $von = request()->input('von');
            $bis = request()->input('bis');
            $betrag = request()->input('betrag');
            $koskat_arr = explode('|', request()->input('kostenart'));
            $e_konto = $koskat_arr [0];
            $kostenkat = $koskat_arr [1];
            $gruppe = $koskat_arr [2];
            $g_konto = $koskat_arr [3];
            /* Löschen */
            $w = new weg ();
            $w->wohngeld_def_delete(request()->input('dat'));
            /* Neu speichern */
            $w->wohngeld_def_speichern($von, $bis, $betrag, $kostenkat, $e_konto, $gruppe, $g_konto, $kos_id);
            weiterleiten(route('legacy::weg::index', ['option' => 'wohngeld_definieren', 'einheit_id' => $kos_id], false));
        } else {
            fehlermeldung_ausgeben("Eingabe unvollständig");
            weiterleiten_in_sec(route('legacy::weg::index', ['option' => 'wohngeld_definieren', 'einheit_id' => request()->input('kos_id')], false), 2);
        }
        break;

    case "einnahmen_ausgaben" :
        $f = new formular ();
        $f->fieldset('Einnahmen/Ausgaben', 'ein_aus');
        $w = new weg ();
        $w->einnahmen_ausgaben(19);
        $f->fieldset_ende();

        break;

    case "mahnliste" :
        if (session()->has('objekt_id')) {
            $w = new weg ();
            $w->mahnliste(session()->get('objekt_id'));
        } else {
            echo "Objekt wählen";
        }
        break;

    case "mahnen" :
        if (request()->has('eig')) {
            $w = new weg ();
            $w->form_mahnen(request()->input('eig'));
        } else {
            echo "Eigentümer wählen";
        }
        break;

    case "mahnung_sent" :
        if (request()->has('eig') && request()->has('datum') && request()->has('mahngebuehr')) {
            $w = new weg ();
            $anschrift = request()->input('anschriften');
            $w->pdf_mahnschreiben(request()->input('eig'), request()->input('datum'), request()->input('mahngebuehr'), $anschrift);
        } else {
            echo "Eingaben unvollständig für ein Mahnschreiben!";
        }
        break;

    case "hg_kontoauszug" :
        if (request()->has('eigentuemer_id')) {
            $w = new weg ();
            $w->hg_kontoauszug_anzeigen_pdf(request()->input('eigentuemer_id'));
        } else {
            echo "Eigentuemer wählen";
        }
        break;

    case "wpliste" :
        if (session()->has('objekt_id')) {
            $w = new weg ();
            $w->wp_liste(session()->get('objekt_id'));
        } else {
            echo "Objekt wählen!";
        }
        break;

    case "wp_neu" :
        $w = new weg ();
        $w->form_wplan_neu();
        break;

    case "wp_neu_send" :
        if (request()->has('wjahr') && request()->has('objekt_id')) {
            $w = new weg ();
            $w->wp_plan_speichern(request()->input('wjahr'), request()->input('objekt_id'));
        } else {
            echo "Wirtschaftjahr eingeben und Objekt wählen bitte!";
        }

        break;

    case "wp_zeile_neu" :
        if (request()->has('wp_id')) {
            session()->put('wp_id', request()->input('wp_id'));
            $w = new weg ();
            $w->form_wplan_zeile(session()->get('wp_id'));
        }

        break;

    case "wp_zeile_send" :
        if (request()->has('bkonto') && session()->has('wp_id') && request()->has('vsumme') && request()->has('formel') && request()->has('wirt_id')) {
            $weg = new weg ();
            $betrag_vj = request()->input('summe_vj');
            $formel = request()->input('formel');
            $wirt_id = request()->input('wirt_id');
            $weg->wp_zeile_speichern(session()->get('wp_id'), request()->input('bkonto'), request()->input('vsumme'), $betrag_vj, $formel, $wirt_id);
            weiterleiten(route('legacy::weg::index', ['option' => 'wp_zeile_neu', 'wp_id' => session()->get('wp_id')], false));
        }
        break;

    case "wplan_pdf" :
        if (request()->has('wp_id')) {
            $w = new weg ();
            $w->pdf_wplan(request()->input('wp_id'));
        } else {
            echo "Wirtschaftsplan wählen!";
        }
        break;

    case "hga_profile" :
        $w = new weg ();
        $w->tab_profile();
        break;

    case "hga_profile_del" :
        $w = new weg ();
        $w->hga_profil_del(request()->input('profil_id'));
        weiterleiten(route('legacy::weg::index', ['option' => 'hga_profile'], false));
        break;

    case "hga_profile_wahl" :
        $w = new weg ();
        $w->hga_profil_wahl(request()->input('profil_id'));
        break;

    case "grunddaten_profil" :
        if (request()->has('profil_id')) {
            session()->put('hga_profil_id', request()->input('profil_id'));
            $weg = new weg ();
            $weg->form_hga_profil_grunddaten(request()->input('profil_id'));
        } else {
            fehlermeldung_ausgeben("HGA Profil wählen!");
        }
        break;

    case "profil_send_gaendert" :
        if (request()->has('profil_id')
            && request()->has('profilbez')
            && request()->has('objekt_id')
            && request()->has('jahr')
            && request()->has('geldkonto_id')
            && request()->has('gk_id_ihr')
            && request()->has('wp_id')
            && request()->has('hg_konto')
            && request()->has('hk_konto')
            && request()->has('ihr_konto')
        ) {
            $weg = new weg ();
            $weg->hga_profil_aendern(request()->input('profil_id'),
                request()->input('objekt_id'),
                request()->input('geldkonto_id'),
                request()->input('jahr'),
                request()->input('profilbez'),
                request()->input('gk_id_ihr'),
                request()->input('wp_id'),
                request()->input('hg_konto'),
                request()->input('hk_konto'),
                request()->input('ihr_konto'),
                request()->input('p_von'),
                request()->input('p_bis')
            );
            fehlermeldung_ausgeben("Profil geändert, bitte warten!!!!");
            $profil_id = request()->input('profil_id');
            weiterleiten_in_sec(route('legacy::weg::index', ['option' => 'grunddaten_profil', 'profil_id' => $profil_id], false), 2);
        } else {
            fehlermeldung_ausgeben("Profil nicht geändert, Daten unvollständig!!!!");
        }
        break;

    case "hga_einzeln" :
        $w = new weg ();
        if(session()->has('hga_profil_id')) {
            $w->hga_einzeln(session()->get('hga_profil_id'));
        } else {
            echo "Bitte wählen Sie ein Profil.";
        }
        break;

    case "hga_gesamt" :
        $w = new weg ();
        if(session()->has('hga_profil_id')) {
            $w->hg_gesamtabrechnung(session()->get('hga_profil_id'));
        } else {
            echo "Bitte wählen Sie ein Profil.";
        }
        break;

    case "hga_gesamt_pdf" :
        $w = new weg ();
        if(session()->has('hga_profil_id')) {
            $w->hg_gesamtabrechnung_pdf(session()->get('hga_profil_id'));
        } else {
            echo "Bitte wählen Sie ein Profil.";
        }
        break;

    case "ihr" :
        $w = new weg ();
        if (session()->has('hga_profil_id')) {
            $w->ihr(session()->get('hga_profil_id'));
        } else {
            echo "Bitte wählen Sie ein Profil.";
        }
        break;

    case "pdf_ihr" :
        $w = new weg ();
        if (session()->has('hga_profil_id')) {
            $w->ihr_pdf(session()->get('hga_profil_id'));
        } else {
            echo "Bitte wählen Sie ein Profil.";
        }
        break;

    case "assistent" :
        $w = new weg ();
        $w->assistent();
        break;

    case "profil_send" :
        if (request()->has('profilbez')
            && request()->has('objekt_id')
            && request()->has('jahr')
            && request()->has('geldkonto_id')
            && request()->has('gk_id_ihr')
            && request()->has('wp_id')
            && request()->has('hg_konto')
            && request()->has('hk_konto')
            && request()->has('ihr_konto')
        ) {
            $w = new weg ();
            $w->hga_profil_speichern(
                request()->input('objekt_id'),
                request()->input('geldkonto_id'),
                request()->input('jahr'),
                request()->input('profilbez'),
                request()->input('gk_id_ihr'),
                request()->input('wp_id'),
                request()->input('hg_konto'),
                request()->input('hk_konto'),
                request()->input('ihr_konto')
            );
        } else {
            echo "Daten unvollständig";
        }
        break;

    /* Schritt 2 Ausgewählte Kontensummen zu HGA_ZEILEN */
    case "konto_hinzu" :
        $w = new weg ();
        $w->form_konto_hinzu(request()->input('konto'));
        break;

    /* Konto aus einem Profil entfernen */
    case "konto_del" :
        if (request()->has('profil_id') && request()->has('konto')) {
            $weg = new weg ();
            $weg->konto_loeschen(request()->input('profil_id'), request()->input('konto'));
            fehlermeldung_ausgeben("Konto $konto wurde gelöscht!");
        }
        weiterleiten_in_sec(route('legacy::weg::index', ['option' => 'assistent', 'schritt' => 2], false), 2);
        break;

    case "konto_zu_zeilen" :
        $w = new weg ();
        $w->hga_zeile_speichern(session()->get('hga_profil_id'), request()->input('konto'), request()->input('art'), request()->input('textbez'), request()->input('genkey'), request()->input('summe'), request()->input('summe_hndl'), 'Wirtschaftseinheit', request()->input('wirt_id'));
        weiterleiten(route('legacy::weg::index', ['option' => 'assistent', 'schritt' => 2], false));
        break;

    case "hk_verbrauch_send" :
        $w = new weg ();
        $anz_e = count(request()->input('eig_id'));
        $p_id = request()->input('p_id');

        for ($a = 0; $a < $anz_e; $a++) {
            $eig_id = request()->input('eig_id') [$a];
            $betrag = request()->input('hk_verbrauch') [$a];
            $w->hk_verbrauch_eintragen($p_id, $eig_id, $betrag);
        }
        weiterleiten(route('legacy::weg::index', ['option' => 'hk_verbrauch_tab'], false));
        break;

    case "hk_verbrauch_tab" :
        $w = new weg ();
        if (session()->has('hga_profil_id')) {
            $w->tab_hk_verbrauch(session()->get('hga_profil_id'));
        } else {
            echo "Hausgeldabrechnungsprofil wählen!";
        }
        break;

    case "kontostand_erfassen" :
        $w = new weg ();
        $w->form_kontostand_erfassen();
        break;

    case "kto_stand_send" :
        if (request()->has('datum') && request()->has('betrag') && session()->has('geldkonto_id')) {
            $w = new weg ();
            $datum = date_german2mysql(request()->input('datum'));
            $gk_id = session()->get('geldkonto_id');
            $betrag = request()->input('betrag');
            if ($w->kontostand_speichern($gk_id, $datum, $betrag)) {
                echo "Kontostand eingegeben!";
                weiterleiten_in_sec(route('legacy::weg::index', ['option' => 'kontostaende'], false), 3);
            }
        } else {
            echo "Daten unvollständig eingegeben";
        }
        break;

    case "kontostaende" :
        $w = new weg ();
        $w->kontostand_anzeigen(session()->get('geldkonto_id'));
        break;

    case "serienbrief" :
        if (!session()->has('objekt_id')) {
            $weg = new weg ();
            $weg->liste_weg_objekte();
        } else {
            $weg = new weg ();
            $weg->form_eigentuemer_checkliste(session()->get('objekt_id'));
        }
        break;

    case "serien_brief_vorlagenwahl" :
        if (request()->has('delete')) {
            session()->put('eig_ids', []);
            echo "Alle gelöscht!";
            break;
        }
        if (!session()->has('eig_ids')) {
            session()->put('eig_ids', []);
        }
        if (request()->has('eig_ids') && is_array(request()->input('eig_ids'))) {
            session()->put('eig_ids', array_merge(session()->get('eig_ids'), request()->input('eig_ids')));
            session()->put('eig_ids', array_unique(session()->get('eig_ids')));
        }
        if (session()->has('eig_ids')) {
            $s = new serienbrief ();
            if (request()->has('kat')) {
                $s->vorlage_waehlen('Eigentuemer', request()->input('kat'));
            } else {
                $s->vorlage_waehlen('Eigentuemer');
            }
        } else {
            fehlermeldung_ausgeben("Bitte Eigentümer aus Liste wählen!");
        }
        break;

    case "serienbrief_pdf" :
        $bpdf = new b_pdf ();
        $s = new serienbrief ();
        $s->erstelle_brief_vorlage(request()->input('vorlagen_dat'), 'Eigentuemer', session()->get('eig_ids'), $option = '0');
        break;

    case "hausgeld_zahlungen" :
        if (session()->has('objekt_id')) {
            $weg = new weg();
            $weg->form_hausgeldzahlungen(session()->get('objekt_id'));
        } else {
            fehlermeldung_ausgeben("Objekt wählen!!!");
        }
        break;

    case "hausgeld_zahlungen_xls" :
        if (session()->has('objekt_id')) {
            $weg = new weg ();
            $weg->form_hausgeldzahlungen_xls(session()->get('objekt_id'));
        } else {
            fehlermeldung_ausgeben("Objekt wählen!!!");
        }
        break;

    case "autokorrkto" :
        $profil_id = request()->input('profil_id');
        $konto = request()->input('konto');
        $betrag = request()->input('betrag');
        $weg = new weg ();
        $weg->autokorr_hga($profil_id, $konto, $betrag);
        weiterleiten_in_sec(route('legacy::weg::index', ['option' => 'assistent', 'schritt' => 2, 'profil_id' => $profil_id], false), 1);
        break;

    case "stammdaten_weg" :
        if (!session()->has('objekt_id')) {
            fehlermeldung_ausgeben("Objekt wählen!!!");
        } else {
            $weg = new weg ();
            $weg->stammdaten_weg(session()->get('objekt_id'));
        }
        break;

    case "pdf_et_liste_alle_kurz" :
        if (!session()->has('objekt_id')) {
            fehlermeldung_ausgeben("Objekt wählen!!!");
        } else {
            $weg = new weg ();
            $weg->pdf_et_liste_alle_kurz(session()->get('objekt_id'));
        }
        break;

    case "pdf_hausgelder" :
        $w = new weg ();
        if (!request()->has('jahr')) {
            $jahr = date("Y");
        } else {
            $jahr = request()->input('jahr');
        }
        if (!session()->has('objekt_id')) {
            fehlermeldung_ausgeben("Objekt wählen");
            return;
        }
        $w->pdf_hausgelder(session()->get('objekt_id'), $jahr);
        break;

    case "wp_zeile_del" :
        if (request()->has('dat')) {
            $weg = new weg ();
            if ($weg->wp_zeile_loeschen(request()->input('dat')) == true) {
                $wp_id = session()->get('wp_id');
                weiterleiten_in_sec(route('legacy::weg::index', ['option' => 'wp_zeile_neu', 'wp_id' => $wp_id], false), 0);
            }
        } else {
            fehlermeldung_ausgeben("Zeile aus dem WP wählen!!!");
        }
        break;
} // end switch