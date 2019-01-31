<?php

if (request()->filled('option')) {
    $option = request()->input('option');
} else {
    $option = 'default';
}
/* Optionsschalter */
switch ($option) {

    default :
        $sep = new sepa ();
        // $sep->alle_mandate_anzeigen();
        $sep->sepa_sammler_alle();

        break;

    case "mandate_mieter_kurz" :
        $sep = new sepa ();
        $sep->alle_mandate_anzeigen_kurz('MIETZAHLUNG');
        break;

    case "mandate_mieter" :
        $sep = new sepa ();
        $sep->alle_mandate_anzeigen('MIETZAHLUNG');
        break;

    case "mandate_rechnungen" :
        $sep = new sepa ();
        $sep->alle_mandate_anzeigen('RECHNUNGEN');
        break;

    case "mandate_hausgeld" :
        $sep = new sepa ();
        $sep->alle_mandate_anzeigen('HAUSGELD');
        break;

    case "mandate_hausgeld_kurz" :
        $sep = new sepa ();
        $sep->alle_mandate_anzeigen_kurz('HAUSGELD');
        break;

    case "mandat_mieter_neu" :
        $sep = new sepa ();
        if (session()->has('geldkonto_id')) {
            $sep->form_mandat_mieter_neu(session()->get('geldkonto_id'));
        } else {
            fehlermeldung_ausgeben("Erst Geldkonto wählen!!!");
        }
        break;

    case "mandat_hausgeld_neu" :
        $sep = new sepa ();
        if (session()->has('geldkonto_id')) {
            $sep->form_mandat_hausgeld_neu(session()->get('geldkonto_id'));
        } else {
            fehlermeldung_ausgeben("Erst Geldkonto wählen!!!");
        }
        break;

    case "mandat_mieter_neu_send" :
        echo "<hr><br><br><br><br><br><br><br><br><br><br>";
        if (request()->exists('Button')) {
            if (request()->filled('mv_id')) {

                $kos_typ = request()->input('M_KOS_TYP');

                if ($kos_typ == 'Mietvertrag') {
                    $mref = 'MV' . request()->input('mv_id');
                    $n_art = 'MIETZAHLUNG';
                }
                if ($kos_typ == 'Eigentuemer') {
                    $mref = 'WEG-ET' . request()->input('mv_id');
                    $n_art = 'HAUSGELD';
                }

                $sep = new sepa ();
                if ($sep->check_m_ref($mref)) {
                    fehlermeldung_ausgeben("Mandat $mref existiert schon!!!");
                } else {
                    if (request()->filled('einzugsart')
                        && request()->filled('BEGUENSTIGTER')
                        && request()->filled('NAME')
                        && request()->filled('ANSCHRIFT')
                        && request()->filled('IBAN')
                        && request()->filled('BIC')
                        && request()->filled('BANK')
                        && request()->filled('M_UDATUM')
                        && request()->filled('M_ADATUM')
                        && request()->filled('GK_ID')
                        && request()->filled('GLAEUBIGER_ID')
                    ) {
                        $glaeubiger_id = request()->input('GLAEUBIGER_ID');
                        $gk_id = request()->input('GK_ID');
                        $empf = request()->input('BEGUENSTIGTER');
                        $name = request()->input('NAME');
                        $anschrift = request()->input('ANSCHRIFT');
                        $kto = '';
                        $blz = '';
                        $iban = request()->input('IBAN');
                        $bic = request()->input('BIC');
                        $bankname = request()->input('BANK');
                        $udatum = request()->input('M_UDATUM');
                        $adatum = request()->input('M_ADATUM');
                        $m_art = 'WIEDERKEHREND';
                        $e_art = request()->input('einzugsart');

                        $kos_id = request()->input('mv_id');
                        $edatum = '31.12.9999';
                        $sep = new sepa ();
                        $sep->mandat_speichern($mref, $glaeubiger_id, $gk_id, $empf, $name, $anschrift, $kto, $blz, $iban, $bic, $bankname, $udatum, $adatum, $edatum, $m_art, $n_art, $e_art, $kos_typ, $kos_id);
                    } else {
                        fehlermeldung_ausgeben("Eingabe unvollständig, bitte alle Felder ausfüllen!");
                    }
                }
            }
        }

        break;

    case "mandat_edit_mieter" :
        $sep = new sepa ();
        $sep->form_mandat_mieter_edit(request()->input('mref_dat'));
        break;

    case "mandat_mieter_edit_send" :
        echo "<hr><br><br><br><br><br><br><br><br><br><br>";

        if (request()->exists('btn_edit_mieter') && request()->filled('mref_dat')) {
            if (request()->filled('mv_id')) {

                if (request()->filled('einzugsart') && request()->filled('BEGUENSTIGTER') && request()->filled('NAME') && request()->filled('ANSCHRIFT') && request()->filled('IBAN') && request()->filled('BIC') && request()->filled('BANK') && request()->filled('M_UDATUM') && request()->filled('M_ADATUM') && request()->filled('GK_ID') && request()->filled('GLAEUBIGER_ID')) {

                    $kos_typ = request()->input('M_KOS_TYP');

                    if ($kos_typ == 'Mietvertrag') {
                        $mref = 'MV' . request()->input('mv_id');
                        $n_art = 'MIETZAHLUNG';
                    }
                    if ($kos_typ == 'Eigentuemer') {
                        $mref = 'WEG-ET' . request()->input('mv_id');
                        $n_art = 'HAUSGELD';
                    }
                    $glaeubiger_id = request()->input('GLAEUBIGER_ID');
                    $gk_id = request()->input('GK_ID');
                    $empf = request()->input('BEGUENSTIGTER');
                    $name = request()->input('NAME');
                    $anschrift = request()->input('ANSCHRIFT');
                    $kto = request()->input('KTO');
                    $blz = request()->input('BLZ');
                    $iban = request()->input('IBAN');
                    $bic = request()->input('BIC');
                    $bankname = request()->input('BANK');
                    $udatum = request()->input('M_UDATUM');
                    $adatum = request()->input('M_ADATUM');
                    $edatum = request()->input('M_EDATUM');
                    $m_art = 'WIEDERKEHREND';
                    $e_art = request()->input('einzugsart');
                    $kos_typ = request()->input('M_KOS_TYP');
                    $kos_id = request()->input('mv_id');
                    $sep = new sepa ();
                    $dat = request()->input('mref_dat');
                    $sep->mandat_aendern($dat, $mref, $glaeubiger_id, $gk_id, $empf, $name, $anschrift, $kto, $blz, $iban, $bic, $bankname, $udatum, $adatum, $edatum, $m_art, $n_art, $e_art, $kos_typ, $kos_id);
                } else {
                    fehlermeldung_ausgeben("Eingabe unvollständig, bitte alle Felder ausfüllen!");
                }
            }
        }
        break;

    case "sepa" :
        $sep = new sepa ();
        $sep->test_sepa();
        break;

    case "import_dtaus" :
        $sep = new sepa ();
        $sep->import_dtaustn(31, '', '2013-11-15');
        break;

    case "sepa_download" :
        if (request()->filled('Btn-SEPApdf')) {
            $pdf = '1';
        } else {
            $pdf = '0';
        }
        $dateiname_msgid = session()->get('geldkonto_id') . '-' . str_limit(umlautundgross(Auth::user()->name),35) . '-' . microtime(1) . '.xml';
        $sep = new sepa ();
        if (request()->filled('sammelbetrag')) {
            $sammelbetrag = request()->input('sammelbetrag');
            $nutzungsart = request()->input('nutzungsart');
            $sep->sepa_datei_erstellen(1, $dateiname_msgid, $nutzungsart, $pdf); // als Sammelbetrag auf dem Kontoauszug!
        } else {
            $sep->sepa_datei_erstellen(0, $dateiname_msgid, $nutzungsart, $pdf); // Einzelbeträge auf dem Kontoauszug
        }
        break;

    case "test_ls" :
        $sep = new sepa ();
        $sep->test_fremd_sepa_ls();
        break;

    case "mandat_nutzungen_anzeigen" :
        if (request()->filled('m_ref')) {
            $sep = new sepa ();
            $sep->mandat_nutzungen_anzeigen(request()->input('m_ref'));
        } else {
            fehlermeldung_ausgeben("Mandat wählen");
        }
        break;

    case "sammler_anzeigen" :
        if (session()->has('geldkonto_id')) {
            $gk = new geldkonto_info ();
            $gk->geld_konto_details(session()->get('geldkonto_id'));
            $sep = new sepa ();
            $sep->get_iban_bic($gk->kontonummer, $gk->blz);
            $gk_id = session()->get('geldkonto_id');
            echo "<h5>$gk->geldkonto_bezeichnung - $sep->IBAN1 - $sep->BIC</h5>";
            $sep->sepa_alle_sammler_anzeigen();
        } else {
            fehlermeldung_ausgeben("Geldkonto wählen");
        }
        break;

    case "sammler2sepa" :
        $sep = new sepa ();
        if (request()->filled('gk_id') && request()->filled('kat')) {
            $von_gk_id = request()->input('gk_id');
            $kat = request()->input('kat');
            if ($kat == 'ET_AUSZAHLUNG') {
                $sammler = '0'; // Einzelbeträge
            } else {
                $sammler = '1'; // Nur einen Betrag
            }
            $sep->sammler2sepa($von_gk_id, $kat, $sammler);
        } else {
            fehlermeldung_ausgeben("Geldkonto und Kategorie wählen!!!");
        }
        break;

    case "re_zahlen" :
        if (!session()->has('geldkonto_id')) {
            hinweis_ausgeben("Bitte Geldkonto auswählen!");
        } else {
            $g = new geldkonto_info ();
            $g->geld_konto_details(session()->get('geldkonto_id'));
            echo "<b>Ausgewähltes Konto $g->geldkonto_bezeichnung_kurz</b><br>";
        }

        if (request()->filled('partner_wechseln')) {
            session()->forget('partner_id');
        }

        if (request()->filled('partner_id')) {
            session()->put('partner_id', request()->input('partner_id'));
        }

        $r = new rechnungen ();
        $p = new partner ();

        if (request()->filled('monat') && request()->filled('jahr')) {
            if (request()->input('monat') != 'alle') {
                session()->put('monat', sprintf('%02d', request()->input('monat')));
            } else {
                session()->put('monat', request()->input('monat'));
            }
            session()->put('jahr', request()->input('jahr'));
        }

        if (!session()->has('partner_id')) {
            $p->partner_auswahl();
        } else {
            if (!session()->has('monat') or !session()->has('jahr')) {
                $monat = date("m");
                $jahr = date("Y");
            } else {
                $monat = session()->get('monat');
                $jahr = session()->get('jahr');
            }

            if (!request()->filled('belegnr')) {
                $r->rechnungseingangsbuch_kurz_zahlung_sepa('Partner', session()->get('partner_id'), $monat, $jahr, 'Rechnung');
            } else {
                $u = new ueberweisung ();
                $belegnr = request()->input('belegnr');
                $u->form_rechnung_dtaus_sepa($belegnr);
            }
        }
        break;

    case "ra_zahlen" :
        if (!session()->has('geldkonto_id')) {
            hinweis_ausgeben("Bitte Geldkonto auswählen!");
        } else {
            $g = new geldkonto_info ();
            $g->geld_konto_details(session()->get('geldkonto_id'));
            echo "<b>Ausgewähltes Konto $g->geldkonto_bezeichnung_kurz</b><br>";
        }

        if (request()->filled('partner_wechseln')) {
            session()->forget('partner_id');
        }

        if (request()->filled('partner_id')) {
            session()->put('partner_id', request()->input('partner_id'));
        }

        $r = new rechnungen ();
        $p = new partner ();

        if (request()->filled('monat') && request()->filled('jahr')) {
            if (request()->input('monat') != 'alle') {
                session()->put('monat', sprintf('%02d', request()->input('monat')));
            } else {
                session()->put('monat', request()->input('monat'));
            }
            session()->put('jahr', request()->input('jahr'));
        }

        if (!session()->has('partner_id')) {
            $p->partner_auswahl();
        } else {
            if (!session()->has('monat') or !session()->has('jahr')) {
                $monat = date("m");
                $jahr = date("Y");
            } else {
                $monat = session()->get('monat');
                $jahr = session()->get('jahr');
            }

            if (!request()->filled('belegnr')) {
                $r->rechnungsausgangsbuch_kurz_zahlung_sepa('Partner', session()->get('partner_id'), $monat, $jahr, 'Rechnung');
            } else {
                $u = new ueberweisung ();
                $belegnr = request()->input('belegnr');
                $u->form_rechnung_dtaus_sepa($belegnr);
            }
        }
        break;

    /* Gleiche Option gibbt es in modul listen.php */
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
        if ($betrag <= 0) {
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
        }
        break;

    case "sepa_files" :
        if (!session()->has('geldkonto_id')) {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\InfoMessage("Bitte wählen Sie ein Geldkonto.")
            );
        } else {
            $sep = new sepa ();
            $sep->sepa_files(session()->get('geldkonto_id'));
        }
        break;

    case "sepa_files_fremd" :
        if (!session()->has('geldkonto_id')) {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\InfoMessage("Geldkonto wählen")
            );
        } else {
            $sep = new sepa ();
            $sep->sepa_files(null);
        }
        break;

    case "sepa_file_buchen_fremd" :
        if (!request()->filled('sepa_file')) {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\ErrorMessage("SEPA-DATEI wählen.")
            );
        } else {
            $sep = new sepa ();
            $sep->sepa_file_buchen_fremd(request()->input('sepa_file'));
        }
        break;

    case "sepa_ue_buchen_fremd" :
        if (is_array(request()->input('betrag'))) {
            $anz = count(request()->input('betrag'));
            for ($a = 0; $a < $anz; $a++) {
                $datum = request()->input('datum');
                $betrag = request()->input('betrag') [$a];
                if (request()->filled('mwst')) {
                    $mwst = $betrag / 119 * 19;
                } else {
                    $mwst = '0';
                }
                $kos_typ = request()->input('kos_typ') [$a];
                $kos_id = request()->input('kos_id') [$a];
                $geldkonto_id = request()->input('gk_id');
                $kostenkonto = request()->input('konto') [$a];
                $m_ref = request()->input('m_ref');
                $vzweck = request()->input('vzweck') [$a];
                $kto_auszugsnr = request()->input('auszug');
                if (!empty ($kostenkonto)) {
                    $s = new sepa ();
                    $s->betrag_buchen($datum, $kto_auszugsnr, $m_ref, $betrag, $vzweck, $geldkonto_id, $kos_typ, $kos_id, $kostenkonto, $mwst);
                    hinweis_ausgeben("$vzweck $betrag gebucht.");
                } else {
                    fehlermeldung_ausgeben("$vzweck $betrag nicht gebucht, Kostenkonto fehlt!!!!!");
                }
            }
        }
        break;

    case "sepa_file_anzeigen" :
        if (!request()->filled('sepa_file')) {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\ErrorMessage("SEPA-DATEI wählen")
            );
        } else {
            $sep = new sepa ();
            $sep->sepa_file_anzeigen(request()->input('sepa_file'));
        }
        break;

    /* Sepafile Inhalt in Pool schieben, als Vorlage nutzen */
    case "sepa_file_kopieren" :
        if (!request()->filled('sepa_file')) {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\ErrorMessage("SEPA-DATEI wählen.")
            );
        } else {
            $sep = new sepa ();
            if ($sep->sepa_file_kopieren(request()->input('sepa_file'))) {
                weiterleiten(route('web::sepa::legacy', ['option' => 'sammler_anzeigen'], false));
            }
        }
        break;

    case "sepa_file_buchen" :
        if (!request()->filled('sepa_file')) {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\ErrorMessage("SEPA-DATEI wählen.")
            );
        } else {
            $sep = new sepa ();
            $sep->sepa_file_buchen(request()->input('sepa_file'));
        }
        break;

    case "sepa_ue_buchen" :
        $datum = request()->input('datum');
        $betrag = request()->input('betrag');
        if (request()->filled('mwst')) {
            $mwst = $betrag / 119 * 19;
        } else {
            $mwst = '0';
        }
        $kos_typ = request()->input('kos_typ');
        $kos_id = request()->input('kos_id');
        $geldkonto_id = request()->input('gk_id');
        $kostenkonto = request()->input('konto');

        $m_ref = request()->input('m_ref');
        $vzweck = request()->input('vzweck');
        $kto_auszugsnr = request()->input('auszug');
        $s = new sepa ();
        $s->betrag_buchen($datum, $kto_auszugsnr, $m_ref, $betrag, $vzweck, $geldkonto_id, $kos_typ, $kos_id, $kostenkonto, $mwst);
        $datei = request()->input('sepa_file');
        weiterleiten(route('web::sepa::legacy', ['option' => 'sepa_file_buchen', 'sepa_file' => $datei], false));
        break;

    case "sepa_file_pdf" :
        if (request()->filled('sepa_file')) {
            $filename = request()->input('sepa_file');
            $sep = new sepa ();
            $sep->sepa_file2pdf($filename);
        }
        break;

    case "sammel_ue" :
        $sep = new sepa ();
        $sep->form_sammel_ue();
        $sep->sepa_alle_sammler_anzeigen();
        break;

    case "sammel_ue_IBAN" :
        $sep = new sepa ();
        $sep->form_sammel_ue_IBAN();
        $sep->sepa_alle_sammler_anzeigen();
        break;

    case "sepa_sammler_hinzu_ue" :
        $sep = new sepa ();
        $vzweck = request()->input('vzweck');
        $von_gk_id = request()->input('gk_id');
        session()->put('geldkonto_id', $von_gk_id);
        $an_sepa_gk_id = request()->input('empf_sepa_gk_id');
        $kat = request()->input('kat');
        $kos_typ = request()->input('kos_typ');
        $kos_bez = request()->input('kos_id');
        $bu = new buchen ();
        $kos_id = $bu->kostentraeger_id_ermitteln($kos_typ, $kos_bez);
        $konto = request()->input('konto');
        $betrag = nummer_komma2punkt(request()->input('betrag'));

        if (empty ($vzweck)) {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\WarningMessage('Verwendungszweck eingeben.')
            );
        }
        if ($betrag <= 0) {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\WarningMessage('ABBRUCH BETRAG NULL ODER KLEINER')
            );
        }
        if ($sep->sepa_ueberweisung_speichern($von_gk_id, $an_sepa_gk_id, $vzweck, $kat, $kos_typ, $kos_id, $konto, $betrag) == false) {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\ErrorMessage("AUFTRAG KONNTE NICHT GESPEICHERT WERDEN!")
            );
        } else {
            session()->put('kos_typ', $kos_typ);
            session()->put('kos_bez', $kos_id);
            weiterleiten(route('web::sepa::legacy', ['option' => 'sammel_ue'], false));
        }
        break;

    case "sepa_sammler_hinzu_ue_IBAN" :
        $sep = new sepa ();
        $vzweck = request()->input('empfaenger') . ', ' . request()->input('vzweck');
        $von_gk_id = request()->input('gk_id');
        session()->put('geldkonto_id', $von_gk_id);
        $iban = request()->input('iban');
        $bic = request()->input('bic');
        $empfaenger = request()->input('empfaenger');
        $bank = request()->input('bank');
        $kat = request()->input('kat');
        $kos_typ = request()->input('kos_typ');
        $kos_bez = request()->input('kos_id');
        $bu = new buchen ();
        $kos_id = $bu->kostentraeger_id_ermitteln($kos_typ, $kos_bez);
        $konto = request()->input('konto');
        $betrag = nummer_komma2punkt(request()->input('betrag'));

        if (empty ($vzweck)) {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\WarningMessage('Verwendungszweck eingeben.')
            );
        }
        if ($betrag <= 0) {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\ErrorMessage('ABBRUCH BETRAG NULL ODER KLEINER')
            );
        }
        if ($sep->sepa_ueberweisung_speichern_IBAN($von_gk_id, $iban, $bic, $empfaenger, $bank, $vzweck, $kat, $kos_typ, $kos_id, $konto, $betrag) == false) {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\ErrorMessage("AUFTRAG KONNTE NICHT GESPEICHERT WERDEN!")
            );
        } else {
            session()->put('kos_typ', $kos_typ);
            session()->put('kos_bez', $kos_id);
            weiterleiten(route('web::sepa::legacy', ['option' => 'sammel_ue_IBAN'], false));
        }
        break;

    case "sepa_datensatz_del" :
        if (request()->filled('dat')) {
            $sep = new sepa ();
            if ($sep->datensatz_entfernen(request()->input('dat'))) {
                weiterleiten(route('web::sepa::legacy', ['option' => 'sammler_anzeigen'], false));
            }
        }
        break;

    case "ls_auto_buchen" :
        $s = new sepa ();
        $arr = $s->get_sepa_lsfiles_arr();
        if (!empty($arr)) {
            $anz = count($arr);
            echo "<table class=\"sortable striped\">";
            echo "<tr><th>DATUM</th><th>DATEI</th><th>ANZAHL LS</th><th>SUMME</th><th>OPTIONEN</th></tr>";
            $z = 0;
            for ($a = 0; $a < $anz; $a++) {
                $z++;
                $anzahl = $arr [$a] ['ANZ'];
                $datei = $arr [$a] ['DATEI'];
                $summe_a = nummer_punkt2komma_t($arr [$a] ['SUMME']);
                $datum = date_mysql2german($arr [$a] ['DATUM']);
                $link_ab = "<a href='" . route('web::sepa::legacy', ['option' => 'ls_auto_buchen_file', 'datei' => $datei]) . "'>Autobuchen</a>";
                echo "<tr class=\"zeile$z\"><td>$datum</td><td>$datei</td><td>$anzahl</td><td>$summe_a</td><td>$link_ab</td></tr>";
                if ($z == 2) {
                    $z = 0;
                }
            }
            echo "</table>";
        } else {
            fehlermeldung_ausgeben("Keine Lastschriftdateien vorhanden!");
        }
        break;

    case "ls_auto_buchen_file" :
        if (request()->filled('datei')) {
            $datei = request()->input('datei');
            $s = new sepa ();
            $s->form_ls_datei_ab($datei);
        }
        break;

    case "ls_zeile_buchen" :
        $datum = request()->input('datum');
        $betrag = nummer_komma2punkt(request()->input('betrag'));
        if (request()->filled('mwst')) {
            $mwst = $betrag / 119 * 19;
        } else {
            $mwst = '0';
        }
        $kos_typ = request()->input('kos_typ');
        $kos_id = request()->input('kos_id');
        $geldkonto_id = request()->input('gk_id');

        $m_ref = request()->input('m_ref');
        if (stristr($m_ref, 'MV') == TRUE) {
            $kostenkonto = '80001';
        }

        if (stristr($m_ref, 'WEG-ET') == TRUE) {
            $kostenkonto = '6020';
        }
        if (!$kostenkonto) {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\ErrorMessage('Kein Kostenkonto gewählt.')
            );
        }

        $vzweck = "SEPA-LS $m_ref";
        $vzweck .= " " . request()->input('vzweck');
        $datei = request()->input('datei');
        $kto_auszugsnr = request()->input('kontoauszug');
        $s = new sepa ();
        $s->betrag_buchen($datum, $kto_auszugsnr, $m_ref, $betrag, $vzweck, $geldkonto_id, $kos_typ, $kos_id, $kostenkonto, $mwst);
        weiterleiten(route('web::sepa::legacy', ['option' => 'ls_auto_buchen_file', 'datei' => $datei], false));
        break;

    case "sepa_ue_autobuchen" :
        if (request()->isMethod('post')) {
            if (!session()->has('geldkonto_id')) {
                throw new \App\Exceptions\MessageException(
                    new \App\Messages\InfoMessage("Bitte wählen Sie ein Geldkonto.")
                );
            }
            if (!session()->has('temp_kontoauszugsnummer') || !session()->has('temp_datum')) {
                throw new \App\Exceptions\MessageException(
                    new \App\Messages\WarningMessage("Bitte geben Sie die Kontrolldaten ein.")
                );
            }
            if (request()->filled('mwst')) {
                $mwst = 1;
            } else {
                $mwst = '0';
            }
            $file = request()->input('file');
            $sep = new sepa ();
            $sep->sepa_file_autobuchen($file, session()->get('temp_datum'), session()->get('geldkonto_id'), session()->get('temp_kontoauszugsnummer'), $mwst);
        } else {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\ErrorMessage("Fehler beim Verbuchen EC232.")
            );
        }
        break;

    case "excel_ue_autobuchen" :
        session()->put('temp_datum', request()->input('datum'));
        session()->put('geldkonto_id', request()->input('gk_id'));
        session()->put('temp_kontoauszugsnummer', request()->input('auszug'));
        if (request()->filled('mwst')) {
            $mwst = 1;
        } else {
            $mwst = '0';
        }

        $file = request()->filled('datei');
        $sep = new sepa ();
        $sep->sepa_file_autobuchen($file, session()->get('temp_datum'), session()->get('geldkonto_id'), session()->get('temp_kontoauszugsnummer'), $mwst);
        break;
}
?>