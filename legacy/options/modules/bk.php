<?php

if (request()->filled('option') && !empty (request()->input('option'))) {
    $option = request()->input('option');
} else {
    $option = 'default';
}

/* Optionsschalter */
switch ($option) {

    default :
        break;

    case "assistent" :
        $bk = new bk ();
        $bk->assistent();
        break;

    case "schritt2" :
        $bk = new bk ();
        $bk->buchungskonten_auswahl();
        break;

    case "profil_reset" :
        $bk = new bk ();
        $bk->profil_reset();
        weiterleiten_in_sec(route('web::bk::legacy', ['option' => 'assistent'], false), 1);
        break;

    case "profile" :
        $bk = new bk ();
        $bk->liste_bk_profile();
        break;

    case "profil_anpassen" :
        if (request()->filled('profil_id')) {
            session()->put('profil_id', request()->input('profil_id'));
            $bk = new bk ();
            $bk->form_bk_profil_anpassen(session()->get('profil_id'));
        } else {
            fehlermeldung_ausgeben("Profil wählen!");
        }
        break;

    case "profil_aendern" :
        $bk = new bk ();
        $profil_id = request()->input('profil_id');
        $bez = request()->input('profil_bez');
        $jahr = request()->input('jahr');
        $typ = 'Wirtschaftseinheit';
        $typ_id = request()->input('w_id');
        $b_datum = request()->input('berechnungsdatum');
        $v_datum = request()->input('verrechnungsdatum');
        $bk->profil_aendern_db($profil_id, $bez, $jahr, $typ, $typ_id, $b_datum, $v_datum);
        weiterleiten(route('web::bk::legacy', ['option' => 'profile']));
        break;

    case "profil_set" :
        session()->put('profil_id', request()->input('profil_id'));
        weiterleiten_in_sec(route('web::bk::legacy', ['option' => 'assistent'], false), 0);
        break;

    case "buchung_anpassen" :
        if (request()->filled('bk_be_id') && request()->filled('profil_id')) {
            $bk = new bk ();
            $bk->form_buchung_anpassen(request()->input('bk_be_id'), request()->input('profil_id'));
        } else {
            fehlermeldung_ausgeben("Buchung und/oder Berechnungsprofil nicht ausgewählt");
        }
        break;

    case "buchung_aendern" :
        if (request()->filled('buchung_id') && request()->filled('bk_be_id') && request()->filled('umlagebetrag') && request()->filled('kostentraeger_typ') && request()->filled('kostentraeger_id') && request()->filled('genkey') && request()->filled('hndl_betrag')) {
            $bk = new bk ();
            $bk->update_bk_buchung(request()->input('bk_be_id'), request()->input('umlagebetrag'), request()->input('kostentraeger_typ'), request()->input('kostentraeger_id'), request()->input('genkey'), nummer_komma2punkt(request()->input('hndl_betrag')));
            weiterleiten_in_sec(route('web::bk::legacy', ['option' => 'assistent'], false), 0);
        } else {
            echo "DATEN UNVOLLSTäNDIG ERROR 505e7";
        }
        break;

    case "eig_konto_anlegen" :
        if (request()->filled('kostenkonto') && request()->filled('konto_bez') && session()->has('profil_id')) {
            $bk = new bk ();
            $bk->bk_konto_speichern(session()->get('profil_id'), request()->input('kostenkonto'), request()->input('konto_bez'));
            session()->forget('genkey');
        } else {
            fehlermeldung_ausgeben("Fehler bk.php, 96");
        }
        header("Location: " . route('web::bk::legacy', ['option' => 'assistent'], false));
        break;

    case "neues_bk_konto" :
        if (session()->has('profil_id')) {
            $bk = new bk ();
            $bk->form_eigenes_konto_anlegen(session()->get('profil_id'));
        } else {
            fehlermeldung_ausgeben("Fehler bk.php, 105");
        }
        break;

    case "change_konto" :
        session()->forget('bk_konto'); // 1020
        session()->forget('bk_konto_id'); // 2
        session()->forget('genkey'); // 1
        weiterleiten_in_sec(route('web::bk::legacy', ['option' => 'assistent'], false), 0);
        break;

    case "konto_auswahl" :
        session()->put('bk_konto', request()->input('bk_konto')); // 1020
        session()->put('bk_konto_id', request()->input('bk_konto_id')); // 1
        session()->forget('genkey'); // 1
        session()->forget('anzeigen_von');
        session()->forget('anzeigen_bis');
        session()->forget('konto_anzeigen');
        weiterleiten(route('web::bk::legacy', ['option' => 'assistent'], false));
        break;

    case "zusammenfassung" :
        $bk = new bk ();
        $bk->zusammenfassung(session()->get('profil_id'));
        break;

    case "pdf_ausgabe" :
        $bk = new bk ();
        if (session()->has('profil_id')) {
            if (!session()->has('geldkonto_id')) {
                echo "Bitte Geldkonto auswählen.";
                return;
            }
            $bk->pdf_ausgabe_alle(session()->get('profil_id'));
        } else {
            echo "Kein Berechnungsprofil gewählt";
        }
        break;

    case "wirtschaftseinheiten" :
        $bk = new bk ();
        $bk->wirtschaftseinheiten();
        break;

    case "wirtschaftseinheit_neu" :
        $wirt = new wirt_e ();
        $wirt->form_new_we();
        break;

    case "new_we" :
        $wirt = new wirt_e ();

        if (request()->filled('w_name')) {
            $wirt->neue_we_speichern(request()->input('w_name'));
            weiterleiten(route('web::bk::legacy', ['option' => 'wirtschaftseinheiten'],false));
        } else {
            fehlermeldung_ausgeben("Fehler: Wirtschaftseinheit braucht eine Bezeichnung!");
        }
        break;

    case "wirt_hinzu" :
        $w_id = request()->input('w_id');
        $anzeigen = request()->input('anzeigen');
        $wirt = new wirt_e ();
        $wirt->einheit2_wirt($w_id, request()->input('IMPORT_AUS'), $anzeigen);
        break;

    case "wirt_delete" :
        if (request()->filled('submit_del_all') && request()->filled('w_id')) {
            $w_id = request()->input('w_id');
            $anzeigen = request()->input('anzeigen');
            $wirt = new wirt_e ();
            $wirt->del_all($w_id);
        }

        if (request()->filled('submit_del') && request()->filled('w_id')) {
            $w_id = request()->input('w_id');
            $anzeigen = request()->input('anzeigen');
            $wirt = new wirt_e ();
            foreach (request()->input('IMPORT_AUS') as $e_id) {
                $wirt->del_eine($w_id, $e_id);
            }
        }

        weiterleiten(route('web::bk::legacy', ['option' => 'wirt_einheiten_hinzu', 'w_id' => $w_id, 'anzeigen' => $anzeigen], false));
        break;

    case "wirt_einheiten_hinzu" :
        if (request()->filled('w_id')) {
            $wirt = new wirt_e ();
            $wirt->form_einheit_hinzu(request()->input('w_id'));
        } else {
            echo "Wirtschafseinheit wählen";
        }
        break;

    case "profil_pdf" :
        $bk = new bk ();
        ob_end_clean(); // ausgabepuffer leeren
        $pdf = new Cezpdf ('a4', 'portrait');
        $bpdf = new b_pdf ();
        $bpdf->b_header($pdf, 'Partner', session()->get('partner_id'), 'portrait', 'Helvetica.afm', 6);
        $bk->pdf_uebersicht_profil($pdf, session()->get('profil_id')); // mit pdf;
        $pdf->ezStream();

        break;

    case "konto_pro_anpassen" :
        $bk_konto = request()->input('bk_konto');
        $bk_konto_id = request()->input('bk_konto_id');
        if (!empty ($bk_konto) && !empty ($bk_konto_id)) {
            $bk = new bk ();
            if (session()->has('profil_id')) {
                $bk->form_konto_pro_anpassen(session()->get('profil_id'), $bk_konto, $bk_konto_id);
            } else {
                echo "fehler 645362";
            }
        }
        break;

    case "konto_pro_anpassen_send" :
        $prozent = request()->input('prozent');
        $profil_id = request()->input('profil_id');
        $bk_konto_id = request()->input('bk_konto_id');
        if (!empty ($prozent) && !empty ($profil_id) && !empty ($bk_konto_id)) {
            $bk = new bk ();
            $bk->update_prozent_umlage($profil_id, $bk_konto_id, $prozent);
            weiterleiten(route('web::bk::legacy', ['option' => 'assistent'], false));
        } else {
            echo "Daten unvollständig fehler 247832748";
        }
        break;

    case "anpassung_bk_hk" :
        if (session()->has('profil_id')) {
            $bk = new bk ();
            $bk->form_bk_hk_anpassung(session()->get('profil_id'));
        } else {
            echo "Bitte Profil wählen";
        }
        break;

    case "anpassung_send" :
        $profil_id = request()->input('profil_id');
        $kostenart = request()->input('kostenart');
        $betrag = request()->input('betrag');
        $genkey = request()->input('genkey');

        if (!empty ($profil_id) && !empty ($kostenart) && !empty ($betrag) && !empty ($genkey)) {
            $bk = new bk ();
            $bk->bk_hk_anpassung_speichern($profil_id, $kostenart, $betrag, $genkey);
            weiterleiten(route('web::bk::legacy', ['option' => 'anpassung_bk_hk'], false));
        } else {
            echo "Daten unvollständig Error:jk3434";
        }
        break;

    case "anpassung_bk_hk_del" :
        if (request()->filled('an_dat')) {
            $bk = new bk ();
            $bk->bk_hk_anpassung_loeschen(request()->input('an_dat'));
            weiterleiten(route('web::bk::legacy', ['option' => 'anpassung_bk_hk'], false));
        } else {
            echo "Anpassungszeile wählen";
        }
        break;

    case "test" :
        $bk = new bk ();
        $bk->test_res(session()->get('profil_id'));
        break;

    case "serienbrief" :
        $bpdf = new b_pdf ();
        $ber = new berlussimo_global ();
        $ber->objekt_auswahl_liste();
        if (session()->has('objekt_id')) {
            if (!request()->filled('empfaenger')) {
                $bpdf->form_mieter2sess();
            } else {
                $empfaenger = request()->input('empfaenger');
                $bpdf->form_serienbrief_an($empfaenger);
            }
        }
        break;

    case "empfaenger2sess" :
        if (request()->filled('empfaenger_typ')) {
            $anz = count(request()->input('empf_ids'));
            if ($anz) {
                $arr = request()->input('empf_ids');
            }
        }
        break;

    case "serienbrief_pdf" :
        $bpdf = new b_pdf ();
        $bpdf->erstelle_brief_vorlage(request()->input('vorlagen_dat'), 'Mietvertrag', session()->get('serienbrief_mvs'), $option = '0');
        break;

    case "serienbrief_vorlage_neu" :
        $bpdf = new b_pdf ();
        $bpdf->form_vorlage_neu();
        break;

    case "serienbrief_vorlage_send" :
        if (request()->filled('kurztext') && !empty (request()->input('text'))) {
            $bpdf = new b_pdf ();
            if (request()->input('kat') == 'NEU') {
                $kat = request()->input('kat_man');
            } else {
                $kat = request()->input('kat');
            }
            $bpdf->vorlage_speichern(request()->input('kurztext'), request()->input('text'), $kat, request()->input('empf_typ'));
            $bpdf->vorlage_waehlen(request()->input('empf_typ'));
        } else {
            echo "Eingabe unvollsändig Err. 7824998123jhs";
        }
        break;

    case "vorlage_bearbeiten" :
        if (request()->filled('vorlagen_dat')) {
            $bpdf = new b_pdf ();
            $bpdf->form_vorlage_edit(request()->input('vorlagen_dat'));
        } else {
            echo "Vorlage wählen";
        }
        break;

    case "serienbrief_vorlage_send1" :
        if (request()->filled('kurztext') && request()->filled('text') && request()->input('dat')) {
            $bpdf = new b_pdf ();
            $bpdf->vorlage_update(request()->input('dat'), request()->input('kurztext'), request()->input('text'), request()->input('kat'), request()->input('empf_typ'));
            $bpdf->vorlage_waehlen('Mieter');
        }
        break;

    case "form_profil_kopieren" :
        $bk = new bk ();
        $bk->form_profil_kopieren();
        break;

    case "profil_kopieren" :
        if (request()->filled('profil_id') && request()->filled('profil_bez')) {
            $bk = new bk ();
            $profil_id = request()->input('profil_id');
            $bezeichung = request()->input('profil_bez');
            if (request()->filled('buchungen_kopieren')) {
                $bk->bk_profil_kopieren($profil_id, $bezeichung, 1);
            } else {
                $bk->bk_profil_kopieren($profil_id, $bezeichung, 0);
            }
            echo "<br><br>Profil kopiert, bitte warten!";
            weiterleiten_in_sec(route('web::bk::legacy', ['option' => 'profile'], false), 2);
        } else {
            echo "Eingabe unvollständig Err. 72348724";
        }
        break;

    case "buchungen_hinzu" :
        $bk = new bk ();
        if (request()->filled('genkey')) {
            session()->put('genkey', request()->input('genkey'));
            session()->put('hndl', request()->input('hndl'));
            session()->put('kontierung', request()->input('kontierung'));
            if (request()->filled('submit_key') && request()->input('submit_key') == "Bestehende Ändern") {
                $bk->update_genkey(session()->get('bk_konto_id'), session()->get('profil_id'), session()->get('genkey'), session()->get('hndl'));
            }
        }
        if (request()->filled('uebernahme')) {
            $arr = request()->input('uebernahme');
            $anz = count($arr);

            for ($a = 0; $a < $anz; $a++) {
                $buchung_id = $arr [$a];
                $bk->buchungen_hinzu($buchung_id);
            }
        }
        weiterleiten(route('web::bk::legacy', ['option' => 'assistent'], false));
        break;

    case "energie" :
        $bk = new bk ();
        $bk->form_energie();
        break;

    case "energie_send" :
        $mvs = request()->input('mvs');
        if (is_array($mvs)) {

            $erg = request()->input('ergebnisse');
            $verbrauch = request()->input('verbrauch');

            $anz = count($mvs);
            for ($a = 0; $a < $anz; $a++) {
                $mv_id = $mvs [$a];
                $jahr = request()->input('jahr');
                $me = new mietentwicklung ();
                $datum = date_german2mysql(request()->input('v_datum'));

                $ergebnis_mv = nummer_komma2punkt($erg [$a]);
                $verbrauch_mv = nummer_komma2punkt($verbrauch [$a]);

                if ($ergebnis_mv != 0) {
                    if ($me->check_me('Mietvertrag', $mv_id, "Heizkostenabrechnung $jahr", $datum, $datum, 0) != true) {
                        $me->me_speichern('Mietvertrag', $mv_id, "Heizkostenabrechnung $jahr", $datum, $datum, $ergebnis_mv, 0);
                    }
                }

                if ($verbrauch_mv != 0) {
                    if ($me->check_me('Mietvertrag', $mv_id, "Energieverbrauch lt. Abr. $jahr", $datum, $datum, 0) != true) {
                        $me->me_speichern('Mietvertrag', $mv_id, "Energieverbrauch lt. Abr. $jahr", $datum, $datum, $verbrauch_mv, 0);
                    }
                }
            } // end for
        }
        weiterleiten(route('web::bk::legacy', ['option' => 'energie'], false));

        break;

    case "anpassung_bk_nk" :
        $bk = new bk ();
        $bk->form_bk_hk_anpassung_alle();
        break;

    case "me_send_hk_bk" :
        if (request()->filled('kat')) {
            if (is_array(request()->input('mvs'))) {
                $anz = count(request()->input('mvs'));
                $kat = request()->input('kat');
                $anfang = date_german2mysql(request()->input('v_datum'));
                $ende = date_german2mysql(request()->input('ende'));
                $ende_neu = "0000-00-00";
                for ($a = 0; $a < $anz; $a++) {
                    $mv_id = request()->input('mvs')[$a];
                    $mv = new mietvertraege ();
                    $mv->get_mietvertrag_infos_aktuell($mv_id);

                    $betrag_neu = nummer_komma2punkt(request()->input('vorschuss_neu')[$a]);
                    $dat = request()->input('dat')[$a];
                    $me = new mietentwicklung ();
                    if (($me->check_me('Mietvertrag', $mv_id, "$kat", $anfang, $ende_neu, 0) != true) && ($betrag_neu != 0)) {
                        if ($dat > 0) {
                            $me = new mietentwicklung ();
                            $me_dat_arr = $me->get_dat_info($dat);
                            if (is_array($me_dat_arr)) {
                                $anfang_alt = $me_dat_arr ['ANFANG'];
                                $kat_alt = $me_dat_arr ['KOSTENKATEGORIE'];
                                $betrag_alt = $me_dat_arr ['BETRAG'];
                                $mwst_alt = $me_dat_arr ['MWST_ANTEIL'];
                                $me->me_dat_aendern2($dat, 'Mietvertrag', $mv_id, $anfang_alt, $ende, $kat_alt, $betrag_alt, $mwst_alt);
                            }
                        }

                        /* Wenn Abrechnung Anfang Ende gleich */
                        if (stristr($kat, 'abrechnung') == FALSE) {
                            $me->me_speichern('Mietvertrag', $mv_id, "$kat", $anfang, $ende_neu, $betrag_neu, 0);
                        } else {
                            $me->me_speichern('Mietvertrag', $mv_id, "$kat", $anfang, $anfang, $betrag_neu, 0);
                        }

                        hinweis_ausgeben("$mv->einheit_kurzname - $mv->personen_name_string_u - $betrag_neu");
                    } else {

                        fehlermeldung_ausgeben("$mv->einheit_kurzname $mv->personen_name_string_u existiert oder keine Eingabe!!!");
                    }
                }
            }
            weiterleiten_in_sec(route('web::bk::legacy', ['option' => 'anpassung_bk_nk'], false), 3);
        }

        break;
} // end switch for cases
