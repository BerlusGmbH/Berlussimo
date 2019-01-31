<?php

if (request()->filled('option')) {
    $option = request()->input('option');
} else {
    $option = 'default';
}
/* Optionsschalter */
switch ($option) {

    default :
        break;

    case "inspiration_pdf" :
        $li = new listen ();
        if (request()->filled('objekt_id')) {
            if (request()->filled('monat')) {
                $monat = request()->input('monat');
            } else {
                $monat = date("m");
            }

            if (request()->filled('jahr')) {
                $jahr = request()->input('jahr');
            } else {
                $jahr = date("Y");
            }

            if (request()->filled('lang')) {
                $lang = request()->input('lang');
            } else {
                $lang = 'de';
            }

            $li->inspiration_pdf(0, request()->input('objekt_id'), $monat, $jahr, $lang);
        } else {
            hinweis_ausgeben("Auswahl treffen!!!");
        }
        break;

    case "inspiration_pdf_6" :
        $li = new listen ();
        if (request()->filled('objekt_id')) {
            if (request()->filled('monat')) {
                $monat = request()->input('monat');
            } else {
                $monat = date("m");
            }

            if (request()->filled('jahr')) {
                $jahr = request()->input('jahr');
            } else {
                $jahr = date("Y");
            }

            if (request()->filled('lang')) {
                $lang = request()->input('lang');
            } else {
                $lang = 'de';
            }

            /* Heisst nach Wunsch von IG */
            $li->inspiration_pdf_kurz_6(0, request()->input('objekt_id'), $monat, $jahr, $lang);
        } else {
            hinweis_ausgeben("ObjektID fehlt");
        }
        break;

    case "inspiration_pdf_7" :
        $li = new listen ();
        if (request()->filled('objekt_id')) {
            if (request()->filled('monat')) {
                $monat = request()->input('monat');
            } else {
                $monat = date("m");
            }

            if (request()->filled('jahr')) {
                $jahr = request()->input('jahr');
            } else {
                $jahr = date("Y");
            }

            if (request()->filled('lang')) {
                $lang = request()->input('lang');
            } else {
                $lang = 'de';
            }

            /* Heisst nach Wunsch von IG */
            $li->inspiration_pdf_kurz_7(0, request()->input('objekt_id'), $monat, $jahr, $lang);
        } else {
            hinweis_ausgeben("ObjektID fehlt");
        }
        break;

    case "inspiration_sepa" :
        $li = new listen ();
        if (session()->has('objekt_id')) {
            if (request()->filled('monat')) {
                $monat = request()->input('monat');
            } else {
                $monat = date("m");
            }

            if (request()->filled('jahr')) {
                $jahr = request()->input('jahr');
            } else {
                $jahr = date("Y");
            }

            if (request()->filled('lang')) {
                $lang = request()->input('lang');
            } else {
                $lang = 'de';
            }

            /* Vorschlag zur überweisung */
            $uebersicht_arr = $li->inspiration_sepa_arr(0, session()->get('objekt_id'), $monat, $jahr, $lang);
            $li->form_sepa_ueberweisung_anzeigen($uebersicht_arr);
        } else {
            hinweis_ausgeben("Auswahl treffen!!!");
        }
        break;

    case "sepa_ueberweisen" :
        if (request()->filled('eig_et') && request()->filled('betrag')) {
            $e_id = request()->input('eig_et');
            $betrag = request()->input('betrag');
            $li = new listen ();
            $li->form_sepa_ueberweisung_et($e_id, $betrag);
        } else {
            fehlermeldung_ausgeben("Eigentümer und Betrag fehlen!!");
        }
        break;

    case "sepa_sammler_hinzu" :
        $sep = new sepa ();
        $vzweck = request()->input('vzweck');
        $von_gk_id = request()->input('gk_id');
        session()->put('geldkonto_id', $von_gk_id);
        $an_sepa_gk_id = request()->input('empf_sepa_gk_id');

        $gk_infos = new geldkonto_info ();
        $gk_infos->geld_konto_details($an_sepa_gk_id);
        $vzweck_new = "$gk_infos->beguenstigter, $vzweck";

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
        if ($sep->sepa_ueberweisung_speichern($von_gk_id, $an_sepa_gk_id, $vzweck_new, $kat, $kos_typ, $kos_id, $konto, $betrag) == false) {
            fehlermeldung_ausgeben("AUFTRAG KONNTE NICHT GESPEICHERT WERDEN!");
        } else {
            weiterleiten(route('web::listen::legacy', ['option' => 'inspiration_sepa'], false));
        }
        break;

    case "sammler_anzeigen" :
        if (session()->has('geldkonto_id')) {
            $sep = new sepa ();
            $sep->sepa_sammler_anzeigen(session()->get('geldkonto_id'), 'ET-AUSZAHLUNG');
        } else {
            fehlermeldung_ausgeben("Geldkonto wählen");
        }
        break;

    case "inspiration_pdf_kurz" :
        $li = new listen ();
        if (request()->filled('objekt_id')) {
            if (request()->filled('monat')) {
                $monat = request()->input('monat');
            } else {
                $monat = date("m");
            }

            if (request()->filled('jahr')) {
                $jahr = request()->input('jahr');
            } else {
                $jahr = date("Y");
            }

            if (request()->filled('lang')) {
                $lang = request()->input('lang');
            } else {
                $lang = 'de';
            }

            /* Heisst nach Wunsch von IG */
            $li->inspiration_pdf_kurz(0, request()->input('objekt_id'), $monat, $jahr, $lang);
        } else {
            hinweis_ausgeben("Auswahl treffen!!!");
        }
        break;

    case "bilanz" :
        $l = new listen ();
        $l->bilanz();
        break;

    /* Sollmieten Zeitraum */
    case "sollmieten_zeitraum" :
        $li = new listen ();
        $objekt_id = request()->input('objekt_id');
        $li->mieten_pdf($objekt_id, '2013-08-01', '2013-08-31');
        break;

    case "income_report" :
        $bg = new berlussimo_global();
        $bg->objekt_auswahl_liste();

        if (!request()->filled('jahr')) {
            $jahr = date("Y") - 1;
        } else {
            $jahr = request()->input('jahr');
        }

        $bg->jahres_links($jahr, route('web::listen::legacy', ['option' => 'income_report'], false));

        if (request()->filled('objekt_id')) {
            session()->put('objekt_id', request()->input('objekt_id'));
        }
        $pdf = new Cezpdf ('a4', 'landscape');
        $bpdf = new b_pdf ();
        $bpdf->b_header($pdf, 'Partner', session()->get('partner_id'), 'landscape', 'Helvetica.afm', 6);
        $pdf->ezStopPageNumbers();
        $li = new listen ();
        $li->pdf_income_reports2015_3($pdf, session()->get('objekt_id'), '2014');
        ob_end_clean(); // ausgabepuffer leeren
        $pdf->ezStream();
        break;

    case "saldenpdf" :

        $pdf = new Cezpdf ('a4', 'landscape');
        $bpdf = new b_pdf ();
        $bpdf->b_header($pdf, 'Partner', session()->get('partner_id'), 'landscape', 'Helvetica.afm', 6);
        $pdf->ezStopPageNumbers();
        $objekt_id = session()->get('objekt_id');
        $o = new objekt ();
        $arr = $o->einheiten_objekt_arr($objekt_id);
        $anz_e = count($arr);
        for ($a = 0; $a < $anz_e; $a++) {
            $einheit_id = $arr [$a] ['EINHEIT_ID'];
            // $this->saldo_berechnung_et_pdf(&$pdf, $einheit_id);
            $li = new listen ();
            $li->saldo_berechnung_et_DOBARpravo_pdf($pdf, $einheit_id);
            $pdf->ezNewPage();
        }
        // $li->salden_pdf_objekt($pdf, $objekt_id);

        // $li->saldo_berechnung_et_DOBARpravo_pdf($pdf, $einheit_id);
        ob_end_clean(); // ausgabepuffer leeren
        $pdf->ezStream();

        break;

    case "sepa" :
        $sep = new sepa ();
        $sep->test_sepa();
        break;

    case "pdf_bericht_se" :
        $li = new listen ();
        $objekt_id = request()->input('objekt_id');

        if (!request()->filled('jahr')) {
            $jahr = date("Y");
        } else {
            $jahr = request()->input('jahr');
        }

        if (request()->filled('monat')) {
            $monat = request()->input('monat');
        } else {
            $monat = date("m");
        }
        if (request()->filled('einheit_id')) {
            $einheit_id = request()->input('einheit_id');
        } else {
            //TODO: remove dependency on unit 914
            $einheit_id = 914;
        }
        $li->kto_auszug_einheit($einheit_id);

        break;

    /* Profil für ein Objekt anlegen */
    case "profil_neu" :
        $l = new listen ();
        $l->form_profil_neu();
        break;

    case "step2" :
        // echo '<pre>';
        if (!request()->isMethod('post')) {
            fehlermeldung_ausgeben('Profilformular ausfüllen!!!');
        } else {
            if (request()->filled('kurz_b') && request()->filled('objekt_id') && request()->filled('gk_id') && request()->filled('p_id')) {
                $kurz_b = request()->input('kurz_b');
                $obj_id = request()->input('objekt_id');
                $gk_id = request()->input('gk_id');
                $p_id = request()->input('p_id');
                $l = new listen ();
                $profil_id = $l->report_profil_anlegen($kurz_b, $obj_id, $gk_id, $p_id);
                if (!is_numeric($profil_id)) {
                    fehlermeldung_ausgeben("Profil nicht gespeichert!!");
                } else {
                    session()->put('r_profil_id', $profil_id);
                    $l->form_profil_step2($profil_id);
                }
            }
        }

        break;

    case "profil_liste" :
        $l = new listen ();
        $l->profil_liste();
        break;

    case "profil_wahl" :
        if (request()->filled('profil_id')) {
            session()->put('r_profil_id', request()->input('profil_id'));
        }
        weiterleiten(route('web::listen::legacy', ['option' => 'profil_liste'], false));
        break;

    case "profil_edit" :
        if (request()->filled('profil_id')) {
            session()->put('r_profil_id', request()->input('profil_id'));
            $l = new listen ();
            $l->form_profil_step2(request()->input('profil_id'));
        }

        break;

    case "konten_bearbeiten" :
        if (request()->filled('profil_id') && is_array(request()->input('b_konten'))) {
            $l = new listen ();
            $l->b_konten_edit(request()->input('profil_id'), request()->input('b_konten'), request()->input('bez_arr'));
            session()->put('r_profil_id', request()->input('profil_id'));
            $profil_id = session()->get('r_profil_id');
            weiterleiten(route('web::listen::legacy', ['option' => 'profil_edit', 'profil_id' => $profil_id], false));
        } else {
            fehlermeldung_ausgeben("Buchungskonten für den Bericht wählen!!!");
        }
        break;

    case "pruefung_bericht" :
        if (request()->filled('profil_id')) {
            session()->put('r_profil_id', request()->input('profil_id'));
            $li = new listen ();
            $li->pruefung_bericht(request()->input('r_profil_id'));
        } else {
            fehlermeldung_ausgeben("Profil wählen");
        }
        break;
    /* Neue PDF über Profile */
    case "dyn_pdf" :
        echo '<pre>';
        $li = new listen ();
        $li->dyn_pdf(session()->get('r_profil_id'), request()->input('objekt_id'), request()->input('monat'), request()->input('jahr'), request()->input('bericht_von'), request()->input('bericht_bis'), request()->input('bk_konten'), request()->input('lang'));

        break;

    case "auszugtest":
        /*MOSH*/
        $li = new listen ();
        $einheit_id = 1693;
        $et_id = 1003;
        $f = new formular ();
        $f->fieldset('ET-SALDO', 'ets');
        $arr = $li->auszugtest3($et_id, '2014-06-01');
        $f->fieldset_ende();

        break;

    case "LST" :
        $file = file('BOE.TXT');
        // print_r($file);
        $anz = count($file);
        $auszug = 0;
        $datum_temp = '';
        for ($a = 0; $a < $anz; $a++) {
            $zeile = explode('*', $file [$a]);
            if ($a == 0) {
                $zeile1 ['kto'] = $zeile [41];
                $zeile1 ['blz'] = $zeile [40];
            }

            $datum = $zeile [1];
            if ($datum != $datum_temp) {
                $auszug++;
                $datum_temp = $datum;
            }

            $z = $a + 1;

            $zeile [3] = $auszug;
            $vorzeichen = $zeile [6];
            if ($vorzeichen == '-') {
                $zeile [5] = $vorzeichen . $zeile [5];
            }
            $zeile1 [$a] ['datum'] = $zeile [1];
            $zeile1 [$a] ['auszug'] = $auszug;
            $zeile1 [$a] ['name'] = $zeile [20];
            $zeile1 [$a] ['betrag'] = $zeile [5];
            $zeile1 [$a] ['abs_kto'] = $zeile [14];
            $zeile1 [$a] ['abs_blz'] = $zeile [13];

            $zeile1 [$a] ['vzweck'] = str_replace('MREF+', ' ', str_replace('EREF+', '', str_replace('KREF+', '', str_replace('  ', ' ', str_replace('SVWZ+', ' ', str_replace('PURP+RINP', '', $zeile [10] . ', ' . ltrim(rtrim($zeile [22])) . ' ' . ltrim($zeile [23]) . $zeile [24] . $zeile [25] . $zeile [26] . $zeile [27] . $zeile [28] . ' ' . $zeile [29] . ' ' . $zeile [30] . ' ' . $zeile [31] . ' ' . $zeile [32]))))));
        }
        session()->put('kto_auszug', $zeile1);

        break;

    case "export_ins_objekte" :
        $li = new listen ();
        $li->form_export_objekte();
        break;

    case "exp_obj" :
        if (request()->filled('objekte_arr')) {
            $weg = new weg ();
            $anz = count(request()->input('objekte_arr'));
            $string = '';
            for ($a = 0; $a < $anz; $a++) {
                $obj_id = request()->input('objekte_arr')[$a];
                $str = $weg->stammdaten_weg($obj_id, 'export');
                if ($a == 0) {
                    $string .= $str;
                } else {
                    $pos = strpos($str, "\n"); // strpos($string, "\n");
                    if ($pos) {
                        $str_ohne_ue = substr($str, $pos + 1);
                        $string .= $str_ohne_ue;
                    }
                }
            }
            ob_clean();
            header("Content-Disposition: attachment; filename='OBJEKTE.CSV");
            echo $string;
        } else {
            fehlermeldung_ausgeben("Objekte wählen!");
        }
        break;
}