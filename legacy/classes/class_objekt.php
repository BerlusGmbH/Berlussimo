<?php

class objekt
{

    public $geld_konten_arr;
    public $anzahl_geld_konten;
    public $objekt_name;
    public $objekt_kurzname;
    public $objekt_eigentuemer_id;
    public $objekt_id;
    public $objekt_kontonummer;
    public $objekt_dat;
    public $anzahl_objekte;
    public $objekt_eigentuemer;
    public $objekt_eigentuemer_pdf;
    public $objekt_eigentuemer_partner_id;
    public $anzahl_haeuser;
    public $zeilen_pro_seite;
    public $seiten_anzahl;

    function form_objekt_kopieren()
    {
        $f = new formular ();
        $f->erstelle_formular('Objekt kopieren', null);
        hinweis_ausgeben("Es werden alle Einheiten, Mietverträge (inkl. Personen) kopiert<br>");
        $this->dropdown_objekte('objekt_id', 'objekt_id');
        $f->text_feld('Neue Bezeichnung', 'objekt_kurzname', '', 50, 'objekt_kurzname', '');
        $f->text_feld('Vorzeichen für Einheiten z.B. E, GBN, II, III', 'vorzeichen', '', 10, 'vorzeichen', '');
        $p = new partners ();
        $p->partner_dropdown('Neuen Eigentümer wählen', 'eigentuemer_id', 'eigentuemer_id');
        $f->datum_feld('Datum Saldo VV (letzter Tag vor Verwalterwechsel)', 'datum_u', '', 'datum_u');
        $f->check_box_js('saldo_berechnen', '1', 'Saldo übernehmen?', '', '');
        $f->send_button('btn_snd_copy', 'Kopieren');
        $f->hidden_feld('objekte_raus', 'copy_sent');
        $f->ende_formular();
    }

    function dropdown_objekte($name, $id, $vorwahl = null)
    {
        $objekte_arr = $this->liste_aller_objekte();
        echo "<select name=\"$name\" size=1 id=\"$id\">\n";
        for ($a = 0; $a < count($objekte_arr); $a++) {
            $objekt_name = $objekte_arr [$a] ['OBJEKT_KURZNAME'];
            $objekt_id = $objekte_arr [$a] ['OBJEKT_ID'];
            if ($vorwahl == $objekt_name) {
                echo "<option value=\"$objekt_id\" selected>$objekt_name</option>\n";
            } else {
                echo "<option value=\"$objekt_id\">$objekt_name</option>\n";
            }
        }
        echo "</select>\n";
    }

    function liste_aller_objekte()
    {
        $objekte_array = DB::select("SELECT *, id AS OBJEKT_ID FROM OBJEKT WHERE OBJEKT_AKTUELL='1' ORDER BY OBJEKT_KURZNAME ASC");
        $this->anzahl_objekte = count($objekte_array);
        return $objekte_array;
    }

    function objekt_kopieren($objekt_id, $eigentuemer_id, $objekt_kurzname, $vorzeichen, $datum_u, $saldo_berechnen)
    {
        $this->objekt_speichern($objekt_kurzname, $eigentuemer_id);
        $n_objekt_id = $this->get_objekt_id($objekt_kurzname);
        if (!empty ($n_objekt_id)) {
            //echo "Objekt_id NEW $n_objekt_id";
            /* Details vom Objekt kopieren */
            $dd = new detail ();
            $o_det_arr = $dd->finde_alle_details_arr('Objekt', $objekt_id);
            // print_r($o_det_arr);
            if (!empty($o_det_arr)) {
                $anz_det = count($o_det_arr);
                for ($de = 0; $de < $anz_det; $de++) {
                    $o_det_name = $o_det_arr [$de] ['DETAIL_NAME'];
                    $o_det_inhalt = $o_det_arr [$de] ['DETAIL_INHALT'];
                    $o_det_bemerkung = $o_det_arr [$de] ['DETAIL_BEMERKUNG'];
                    $dd->detail_speichern_2('Objekt', $n_objekt_id, $o_det_name, $o_det_inhalt, $o_det_bemerkung);
                }
            }

            $haus_arr = $this->haeuser_objekt_in_arr($objekt_id);
            if (!empty($haus_arr)) {
                /* Alle Häuser durchlaufen und kopieren */
                $anz_h = count($haus_arr);
                for ($a = 0; $a < $anz_h; $a++) {
                    $haus_id = $haus_arr [$a] ['HAUS_ID'];
                    $str = $haus_arr [$a] ['HAUS_STRASSE'];
                    $nr = $haus_arr [$a] ['HAUS_NUMMER'];
                    $ort = $haus_arr [$a] ['HAUS_STADT'];
                    $plz = $haus_arr [$a] ['HAUS_PLZ'];
                    $qm = $haus_arr [$a] ['HAUS_QM'];
                    $h = new haus ();
                    $n_haus_id = $h->haus_speichern($str, $nr, $ort, $plz, $qm, $n_objekt_id);
                    //echo "$str $nr kopiert<br>";

                    /* Details vom Haus kopieren */
                    $dd = new detail ();
                    $h_det_arr = $dd->finde_alle_details_arr('Haus', $haus_id);
                    if (!empty($h_det_arr)) {
                        $anz_det_h = count($h_det_arr);
                        for ($deh = 0; $deh < $anz_det_h; $deh++) {
                            $h_det_name = $h_det_arr [$deh] ['DETAIL_NAME'];
                            $h_det_inhalt = $h_det_arr [$deh] ['DETAIL_INHALT'];
                            $h_det_bemerkung = $h_det_arr [$deh] ['DETAIL_BEMERKUNG'];
                            $dd->detail_speichern_2('Haus', $n_haus_id, $h_det_name, $h_det_inhalt, $h_det_bemerkung);
                        }
                    }

                    $einheiten_arr = $h->liste_aller_einheiten_im_haus($haus_id);
                    if (!empty($einheiten_arr)) {
                        $anz_e = count($einheiten_arr);
                        for ($e = 0; $e < $anz_e; $e++) {
                            $einheit_id = $einheiten_arr [$e] ['EINHEIT_ID'];
                            $einheit_qm = nummer_punkt2komma($einheiten_arr [$e] ['EINHEIT_QM']);
                            $einheit_lage = $einheiten_arr [$e] ['EINHEIT_LAGE'];
                            $einheit_kurzname = $einheiten_arr [$e] ['EINHEIT_KURZNAME'];
                            $einheit_typ = $einheiten_arr [$e] ['TYP'];
                            $ein = new einheit ();
                            $einheit_kn_arr = explode('-', $einheit_kurzname);
                            // print_r($einheit_kn_arr);
                            $l_elem = count($einheit_kn_arr) - 1;
                            $n_einheit_kurzname = $vorzeichen . '-' . $einheit_kn_arr [$l_elem];
                            //echo "$einheit_kurzname -> $n_einheit_kurzname<br>";
                            $n_einheit_id = $ein->einheit_speichern($n_einheit_kurzname, $einheit_lage, $einheit_qm, $n_haus_id, $einheit_typ);

                            /* Details von Einheiten kopieren */
                            $dd = new detail ();
                            $e_det_arr = $dd->finde_alle_details_arr('Einheit', $einheit_id);
                            if (!empty($e_det_arr)) {
                                $anz_det_e = count($e_det_arr);
                                for ($dee = 0; $dee < $anz_det_e; $dee++) {
                                    $e_det_name = $e_det_arr [$dee] ['DETAIL_NAME'];
                                    $e_det_inhalt = $e_det_arr [$dee] ['DETAIL_INHALT'];
                                    $e_det_bemerkung = $e_det_arr [$dee] ['DETAIL_BEMERKUNG'];
                                    $dd->detail_speichern_2('Einheit', $n_einheit_id, $e_det_name, $e_det_inhalt, $e_det_bemerkung);
                                }
                            }

                            /* Eigentümer kopieren */
                            $weget = new weg ();
                            $et_arr = $weget->get_eigentuemer_arr($einheit_id);
                            if (!empty($et_arr)) {
                                $anz_et = count($et_arr);
                                for ($eta = 0; $eta < $anz_et; $eta++) {
                                    $et_von = $et_arr [$eta] ['VON'];
                                    $et_bis = $et_arr [$eta] ['BIS'];
                                    $weg_et_id = $et_arr [$eta] ['ID'];
                                    $neu_et_id = $weget->eigentuemer_neu($n_einheit_id, $et_von, $et_bis);

                                    /* Personen zu ET eintragen */
                                    $p_id_arr = $weget->get_person_id_eigentuemer_arr($weg_et_id);
                                    if (!empty($p_id_arr)) {
                                        $anz_p_et = count($p_id_arr);
                                        for ($pp = 0; $pp < $anz_p_et; $pp++) {
                                            $tmp_p_id = $p_id_arr [$pp] ['PERSON_ID'];
                                            $weget->person_zu_et($neu_et_id, $tmp_p_id);
                                        }
                                    }

                                    /* Geldkonten finden und zuweisen */
                                    $gki = new geldkonto_info ();
                                    $gk_arr = $gki->geldkonten_arr('Eigentuemer', $weg_et_id);
                                    if (!empty($gk_arr)) {
                                        $anz_gk = count($gk_arr);
                                        for ($gka = 0; $gka < $anz_gk; $gka++) {
                                            $tmp_gk_id = $gk_arr [$gka] ['KONTO_ID'];
                                            /**
                                             * *Konto eintragen**
                                             */
                                            $gkk = new gk ();
                                            $gkk->zuweisung_speichern('Eigentuemer', $neu_et_id, $tmp_gk_id);
                                        }
                                    }
                                }
                            }

                            /* Mietverträge */
                            $mv_arr = $ein->get_mietvertrag_ids($einheit_id);
                            if (!empty($mv_arr)) {
                                $anz_mv = count($mv_arr);
                                for ($m = 0; $m < $anz_mv; $m++) {
                                    $mv_id = $mv_arr [$m] ['MIETVERTRAG_ID'];
                                    $mvs = new mietvertraege ();
                                    $mvs->get_mietvertrag_infos_aktuell($mv_id);
                                    $n_mv_id = $mvs->mietvertrag_speichern($mvs->mietvertrag_von_d, $mvs->mietvertrag_bis_d, $n_einheit_id);

                                    for ($pp = 0; $pp < $mvs->anzahl_personen; $pp++) {
                                        $person_id = $mvs->personen_ids [$pp] ['PERSON_MIETVERTRAG_PERSON_ID'];
                                        $mvs->person_zu_mietvertrag($person_id, $n_mv_id);
                                    }

                                    /* Details von MV's kopieren */
                                    $dd = new detail ();
                                    $mv_det_arr = $dd->finde_alle_details_arr('Mietvertrag', $mv_id);
                                    if (!empty($mv_det_arr)) {
                                        $anz_det_m = count($mv_det_arr);
                                        for ($dem = 0; $dem < $anz_det_m; $dem++) {
                                            $m_det_name = $mv_det_arr [$dem] ['DETAIL_NAME'];
                                            $m_det_inhalt = $mv_det_arr [$dem] ['DETAIL_INHALT'];
                                            $m_det_bemerkung = $mv_det_arr [$dem] ['DETAIL_BEMERKUNG'];
                                            $dd->detail_speichern_2('Mietvertrag', $n_mv_id, $m_det_name, $m_det_inhalt, $m_det_bemerkung);
                                        }
                                    }

                                    /* Mietentwicklung kopieren */
                                    $mit = new mietentwicklung ();
                                    $mit->get_mietentwicklung_infos($mv_id, '', '');
                                    if (is_array($mit->kostenkategorien)) {
                                        $anz_me = count($mit->kostenkategorien);
                                        for ($ko = 0; $ko < $anz_me; $ko++) {
                                            $kat = $mit->kostenkategorien [$ko] ['KOSTENKATEGORIE'];
                                            $anfang = $mit->kostenkategorien [$ko] ['ANFANG'];
                                            $ende = $mit->kostenkategorien [$ko] ['ENDE'];
                                            $betrag = $mit->kostenkategorien [$ko] ['BETRAG'];
                                            $mwst_anteil = $mit->kostenkategorien [$ko] ['MWST_ANTEIL'];
                                            $mit->me_speichern('Mietvertrag', $n_mv_id, $kat, $anfang, $ende, $betrag, $mwst_anteil);
                                        } // end for $ko
                                    }

                                    /* Saldo zum $datum_u ermitteln und den neuen Saldovortragvorverwaltung eingeben */
                                    $datum_saldo_vv = date_german2mysql($datum_u);
                                    $datum_saldo_vv_arr = explode('.', $datum_u);
                                    $datum_jahr = $datum_saldo_vv_arr [2];
                                    $datum_monat = $datum_saldo_vv_arr [1];
                                    $mzz = new miete ();

                                    if ($saldo_berechnen == 1) {
                                        $mzz->mietkonto_berechnung_monatsgenau($mv_id, $datum_jahr, $datum_monat);
                                        //echo "MIT SALDO<br>";
                                        $mit->me_speichern('Mietvertrag', $n_mv_id, 'Saldo Vortrag Vorverwaltung', $datum_saldo_vv, $datum_saldo_vv, $mzz->erg, ($mzz->erg / 119 * 19));
                                    } else {
                                        //echo "OHNE SALDO<br>";
                                        $mit->me_speichern('Mietvertrag', $n_mv_id, 'Saldo Vortrag Vorverwaltung', $datum_saldo_vv, $datum_saldo_vv, '0.00', '0.00');
                                    }

                                    /* ME 0000-00-00 auf $datum_u setzen */
                                } // end for alle MV'S
                            } else {
                                //echo "Mv zu $einheit_kurzname nicht gefunden - Leerstand";
                            }
                        } // end for einheit
                    } else {
                        //echo "Keine Einheiten kopiert";
                    }
                } // end for haus
            }
            return $n_objekt_id;
        } else {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\ErrorMessage('Objekt konnte nicht angelegt werden!')
            );
        }
    }

    function objekt_speichern($objekt_kurzname, $eigentuemer_id)
    {
        $bk = new bk ();
        $last_id = $bk->last_id('OBJEKT', 'id') + 1;
        /* Speichern */
        $db_abfrage = "INSERT INTO OBJEKT VALUES(NULL, '$last_id', '1', '$objekt_kurzname','$eigentuemer_id')";
        DB::insert($db_abfrage);

        /* Protokollieren */
        $last_dat = DB::getPdo()->lastInsertId();
        protokollieren('OBJEKT', $last_dat, '0');
    }

    function get_objekt_id($objekt_name)
    {
        $result = DB::select("SELECT id AS OBJEKT_ID FROM OBJEKT WHERE OBJEKT_AKTUELL='1' && OBJEKT_KURZNAME='$objekt_name' ORDER BY OBJEKT_DAT DESC LIMIT 0,1");
        $row = $result[0];
        $this->objekt_id = $row ['OBJEKT_ID'];
        return $this->objekt_id;
    }

    function haeuser_objekt_in_arr($objekt_id)
    {
        $result = DB::select("SELECT *, id AS HAUS_ID FROM HAUS WHERE HAUS_AKTUELL='1' && OBJEKT_ID='$objekt_id' ORDER BY HAUS_STRASSE, HAUS_NUMMER ASC");
        return $result;
    }

    function pdf_mietaufstellung($objekt_id)
    {
        $arr = $this->mietauftellung_arr($objekt_id);
        if (is_array($arr)) {
            $pdf = new Cezpdf ('a4', 'portrait');
            $bpdf = new b_pdf ();
            $bpdf->b_header($pdf, 'Partner', session()->get('partner_id'), 'portrait', 'Helvetica.afm', 6);

            $cols = array(
                'EINHEIT_KURZNAME1' => 'Einheit',
                'TYP' => "Nutzung",
                'MIETER' => 'Mieter',
                'MIETER_SEIT' => 'Mieter seit',
                'EINHEIT_QM_A' => 'Fläche m²',
                'MIETE_KALT_QM_A' => 'Kaltmiete m²',
                'MIETE_KALT_MON_A' => 'Kaltmiete Monat',
                'UMLAGEN_A' => 'Nebenkosten'
            );
            $monat = date("m");
            $jahr = date("Y");
            $monatsname = monat2name($monat);
            $pdf->ezTable($arr, $cols, "Mietaufstellung $monatsname $jahr", array(
                'showHeadings' => 1,
                'shaded' => 1,
                'titleFontSize' => 8,
                'fontSize' => 7,
                'xPos' => 50,
                'xOrientation' => 'right',
                'width' => 500,
                'cols' => array(
                    'EINHEIT_KURZNAME1' => array(
                        'justification' => 'left',
                        'width' => 55
                    ),
                    'TYP' => array(
                        'justification' => 'right',
                        'width' => 50
                    ),
                    'EINHEIT_QM_A' => array(
                        'justification' => 'right',
                        'width' => 50
                    ),
                    'MIETE_KALT_MON_A' => array(
                        'justification' => 'right',
                        'width' => 60
                    ),
                    'MIETER_SEIT' => array(
                        'justification' => 'right',
                        'width' => 50
                    ),
                    'MIETE_KALT_QM_A' => array(
                        'justification' => 'right',
                        'width' => 50
                    ),
                    'UMLAGEN_A' => array(
                        'justification' => 'right',
                        'width' => 55
                    )
                )
            ));

            ob_end_clean();
            $pdf->ezStream();
        } else {
            echo 'Keine Mietaufstellungsdaten';
        }
    }

    function mietauftellung_arr($objekt_id, $monat = null, $jahr = null)
    {
        if ($monat == null) {
            $monat = date("m");
        }
        if ($jahr == null) {
            $jahr = date("Y");
        }
        $monat = sprintf('%02d', $monat);
        $jahr = sprintf('%02d', $jahr);
        $db_abfrage = "SELECT OBJEKT_KURZNAME, HAUS_STRASSE, HAUS_NUMMER, `EINHEIT_KURZNAME` ,EINHEIT.id AS EINHEIT_ID,  `EINHEIT_LAGE` , `EINHEIT_QM`, EINHEIT.TYP FROM EINHEIT , HAUS, OBJEKT
WHERE OBJEKT.id='$objekt_id' && `EINHEIT_AKTUELL` = '1' && EINHEIT.HAUS_ID = HAUS.id && HAUS.OBJEKT_ID=OBJEKT.id && HAUS_AKTUELL='1' && OBJEKT_AKTUELL='1'
ORDER BY LPAD(EINHEIT_KURZNAME, LENGTH(EINHEIT_KURZNAME), '1') ASC ";

        $result = DB::select($db_abfrage);
        if (!empty($result)) {
            $z = 0;
            $g_flaeche = 0;
            $g_km_monat = 0;
            $g_nkosten = 0;
            $g_zahlung = 0;
            $g_brutto_m = 0;
            foreach ($result as $row) {
                $my_arr [] = $row;
                $einheit_id = $row ['EINHEIT_ID'];
                $einheit_qm = $row ['EINHEIT_QM'];
                $g_flaeche += $einheit_qm;
                $einheit_kn = $row ['EINHEIT_KURZNAME'];

                $my_arr [$z] ['EINHEIT_KURZNAME1'] = $einheit_kn . ' ' . $row ['EINHEIT_LAGE'];
                $my_arr [$z] ['EINHEIT_QM'] = $einheit_qm;
                $my_arr [$z] ['EINHEIT_QM_A'] = nummer_punkt2komma($einheit_qm);
                $e = new einheit ();
                $mv_id = $e->get_mietvertraege_zu($einheit_id, $jahr, $monat, 'DESC');

                if ($mv_id) {
                    $mvs = new mietvertraege ();
                    $mvs->get_mietvertrag_infos_aktuell($mv_id);
                    $my_arr [$z] ['MIETER'] = $mvs->personen_name_string_u;
                    $my_arr [$z] ['MIETER_SEIT'] = $mvs->mietvertrag_von_d;

                    if ($monat == null) {
                        $monat = date("m");
                    }

                    if ($jahr == null) {
                        $jahr = date("Y");
                    }
                    $miete = new miete ();
                    $miete->mietkonto_berechnung_monatsgenau($mv_id, $jahr, $monat);
                    $miete_brutto_arr = explode('|', $miete->sollmiete_warm);
                    if (is_array($miete_brutto_arr)) {
                        $miete_warm = $miete_brutto_arr [0];
                        $mwst = $miete_brutto_arr [1];
                    } else {
                        $miete_warm = $miete->sollmiete_warm;
                        $mwst = '0.00';
                    }
                    $miete_kalt = $miete_warm - $miete->davon_umlagen;

                    $my_arr [$z] ['MONAT'] = $monat;
                    $my_arr [$z] ['JAHR'] = $jahr;

                    $my_arr [$z] ['MIETE_BRUTTO'] = nummer_punkt2komma($miete_warm);
                    $g_brutto_m += $miete_warm;
                    $my_arr [$z] ['MWST'] = nummer_punkt2komma($mwst);
                    $my_arr [$z] ['UMLAGEN'] = nummer_punkt2komma($miete->davon_umlagen);

                    $my_arr [$z] ['ZAHLUNGEN'] = nummer_punkt2komma($miete->geleistete_zahlungen);
                    $my_arr [$z] ['SALDO'] = nummer_punkt2komma($miete->erg);
                    $my_arr [$z] ['SALDO_VM'] = nummer_punkt2komma($miete->saldo_vormonat);
                    $my_arr [$z] ['SALDO_VM1'] = nummer_punkt2komma($miete->saldo_vormonat_stand);

                    $g_nkosten += $miete->davon_umlagen;
                    $g_km_monat += $miete_kalt;
                    $g_zahlung += $miete->geleistete_zahlungen;

                    $my_arr [$z] ['UMLAGEN_A'] = nummer_punkt2komma($miete->davon_umlagen);
                    $my_arr [$z] ['MIETE_KALT_MON'] = nummer_punkt2komma($miete_kalt);
                    $my_arr [$z] ['MIETE_KALT_MON_A'] = nummer_punkt2komma($miete_kalt);

                    if ($einheit_qm != '0.00') {
                        $my_arr [$z] ['MIETE_KALT_QM'] = $miete_kalt / $einheit_qm;
                        $my_arr [$z] ['MIETE_KALT_QM_A'] = nummer_punkt2komma($miete_kalt / $einheit_qm);
                    } else {
                        $my_arr [$z] ['MIETE_KALT_QM'] = '0.00';
                    }
                } else {
                    $my_arr [$z] ['MIETER'] = 'Leerstand';
                }
                $z++;
            }
        } else {
            echo "Keine Daten xcjskskdds!";
        }
        $anz = count($my_arr);
        $my_arr [$anz] ['MONAT_JAHR'] = "$monat / $jahr";
        $my_arr [$anz] ['EINHEIT_QM_A'] = nummer_punkt2komma($g_flaeche) . 'm²';
        $my_arr [$anz] ['MIETE_KALT_MON_A'] = nummer_punkt2komma($g_km_monat) . '€';
        $my_arr [$anz] ['UMLAGEN_A'] = nummer_punkt2komma($g_nkosten) . '€';
        $my_arr [$anz] ['BRUTTOM_A'] = nummer_punkt2komma($g_brutto_m) . '€';
        $my_arr [$anz] ['ZAHLUNGEN_A'] = nummer_punkt2komma($g_zahlung) . '€';

        return $my_arr;
    }

    function pdf_mietaufstellung_m_j($objekt_id, $monat, $jahr)
    {
        $arr = $this->mietauftellung_arr($objekt_id, $monat, $jahr);
        if (is_array($arr)) {
            $pdf = new Cezpdf ('a4', 'landscape');
            $bpdf = new b_pdf ();
            $bpdf->b_header($pdf, 'Partner', session()->get('partner_id'), 'landscape', 'Helvetica.afm', 6);

            $cols = array(
                'EINHEIT_KURZNAME1' => 'Einheit',
                'TYP' => "Nutzung",
                'MIETER' => 'Mieter',
                'MIETER_SEIT' => 'Mieter seit',
                'EINHEIT_QM_A' => 'Fläche m²',
                'MIETE_KALT_QM_A' => 'Kaltmiete m²',
                'MIETE_KALT_MON_A' => 'Kaltmiete Monat',
                'UMLAGEN_A' => 'Nebenkosten',
                'MIETE_BRUTTO' => 'BruttoM',
                'MWST' => 'MWSt',
                'ZAHLUNGEN' => 'Zahlung'
            );
            $monatsname = monat2name($monat);
            $oo = new objekt ();
            $oo->get_objekt_infos($objekt_id);

            if (!request()->filled('xls')) {
                $pdf->ezTable($arr, $cols, "$oo->objekt_kurzname - Mietaufstellung $monatsname $jahr", array(
                    'showHeadings' => 1,
                    'shaded' => 1,
                    'titleFontSize' => 8,
                    'fontSize' => 7,
                    'xPos' => 50,
                    'xOrientation' => 'right',
                    'width' => 750,
                    'cols' => array(
                        'EINHEIT_KURZNAME1' => array(
                            'justification' => 'left',
                            'width' => 55
                        ),
                        'TYP' => array(
                            'justification' => 'right',
                            'width' => 50
                        ),
                        'EINHEIT_QM_A' => array(
                            'justification' => 'right',
                            'width' => 50
                        ),
                        'MIETE_KALT_MON_A' => array(
                            'justification' => 'right',
                            'width' => 60
                        ),
                        'MIETER_SEIT' => array(
                            'justification' => 'right',
                            'width' => 50
                        ),
                        'MIETE_KALT_QM_A' => array(
                            'justification' => 'right',
                            'width' => 50
                        ),
                        'UMLAGEN_A' => array(
                            'justification' => 'right',
                            'width' => 55
                        )
                    )
                ));

                ob_end_clean();
                $pdf->ezStream();
            } else {
                $anz_zeilen = count($arr);

                ob_clean();
                // ausgabepuffer leeren
                $fileName = "$oo->objekt_kurzname - Mietaufstellung $monat-$jahr" . '.xls';
                header("Content-Type: application/vnd.ms-excel");
                header("Content-Disposition: attachment; filename=$fileName");
                header("Content-Disposition: inline; filename=$fileName");
                ob_clean();
                // ausgabepuffer leeren
                echo "<table>";
                echo "<thead>";
                echo "<tr>";
                echo "<th>EINHEIT</th>";
                echo "<th>NUTZUNG</th>";
                echo "<th>MIETER</th>";
                echo "<th>EINZUG</th>";
                echo "<th>FLÄCHE</th>";
                echo "<th>KALTMIETE m²</th>";
                echo "<th>MIETE NETTO</th>";
                echo "<th>NK</th>";
                echo "<th>MIETE BRUTTO</th>";
                echo "<th>MWWST</th>";
                echo "<th>ZAHLUNG</th>";
                echo "</tr>";
                echo "</thead>";

                for ($z = 0; $z < $anz_zeilen - 1; $z++) {
                    $einheit_kn = $arr [$z] ['EINHEIT_KURZNAME'];
                    $nutzung = $arr [$z] ['TYP'];
                    $mieter = $arr [$z] ['MIETER'];
                    $einzug = $arr [$z] ['MIETER_SEIT'];
                    $qm = $arr [$z] ['EINHEIT_QM_A'];
                    $km_qm = $arr [$z] ['MIETE_KALT_QM_A'];
                    $km_mon = $arr [$z] ['MIETE_KALT_MON_A'];
                    $nk = $arr [$z] ['UMLAGEN'];
                    $wm = $arr [$z] ['MIETE_BRUTTO'];
                    $mwst = $arr [$z] ['MWST'];
                    $zahlung = $arr [$z] ['ZAHLUNGEN'];
                    echo "<tr><td>$einheit_kn</td><td>$nutzung</td><td>$mieter</td><td>$einzug</td><td>$qm</td><td>$km_qm</td><td>$km_mon</td><td>$nk</td><td>$wm</td><td>$mwst</td><td>$zahlung</td></tr>";
                }
                echo "</table>";
                return;
            }
        } else {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\InfoMessage('Keine Mietaufstellungsdaten')
            );
        }
    }

    function get_objekt_infos($objekt_id)
    {
        $result = DB::select("SELECT *, id AS OBJEKT_ID  FROM `OBJEKT` WHERE id = '$objekt_id' && OBJEKT_AKTUELL = '1' ORDER BY OBJEKT_DAT DESC LIMIT 0 , 1 ");
        $row = $result[0];
        $this->objekt_dat = $row ['OBJEKT_DAT'];
        $this->objekt_id = $row ['OBJEKT_ID'];
        $this->objekt_kurzname = $row ['OBJEKT_KURZNAME'];
        $this->objekt_eigentuemer_id = $row ['EIGENTUEMER_PARTNER'];
        $p = new partner ();
        $p->partner_grunddaten($this->objekt_eigentuemer_id);
        $this->objekt_eigentuemer = $p->partner_name;

        if (stristr($this->objekt_eigentuemer, 'c/o') == TRUE) {
            $rest = stristr($this->objekt_eigentuemer, 'c/o');
            $this->objekt_eigentuemer_pdf = trim(umbruch_entfernen(str_replace($rest, '', $this->objekt_eigentuemer)));
        } elseif (stristr($this->objekt_eigentuemer, 'vertreten durch') == TRUE) {
            $this->objekt_eigentuemer_pdf = umbruch_entfernen($this->objekt_eigentuemer);
            $rest = stristr($this->objekt_eigentuemer_pdf, ' vertreten durch');
            $this->objekt_eigentuemer_pdf = trim(str_replace($rest, '', $this->objekt_eigentuemer_pdf));
        } else {
            $this->objekt_eigentuemer_pdf = $p->partner_name;
        }
    }

    function pdf_mietaufstellung_j($objekt_id, $jahr)
    {
        for ($mo = 1; $mo <= 12; $mo++) {
            $monat = sprintf('%02d', $mo);

            $arr [$mo - 1] = $this->mietauftellung_arr($objekt_id, $monat, $jahr);

            if (is_array($arr)) {
                $pdf = new Cezpdf ('a4', 'landscape');
                $bpdf = new b_pdf ();
                $bpdf->b_header($pdf, 'Partner', session()->get('partner_id'), 'landscape', 'Helvetica.afm', 6);

                $oo = new objekt ();
                $oo->get_objekt_infos($objekt_id);

                if (!request()->filled('xls')) {

                    $anz_mo = count($arr [$mo - 1]) - 1;
                    $jtab [$mo - 1] = $arr [$mo - 1] [$anz_mo];
                    $jtab1 [0] ['MONAT_JAHR'] = 'SUMMEN';
                    $jtab1 [0] ['EINHEIT_QM_A'] = '--------';
                    $jtab1 [0] ['MIETE_KALT_MON_A'] += nummer_komma2punkt($arr [$mo - 1] [$anz_mo] ['MIETE_KALT_MON_A']);
                    $jtab1 [0] ['UMLAGEN_A'] += nummer_komma2punkt($arr [$mo - 1] [$anz_mo] ['UMLAGEN_A']);
                    $jtab1 [0] ['BRUTTOM_A'] += nummer_komma2punkt($arr [$mo - 1] [$anz_mo] ['BRUTTOM_A']);
                    $jtab1 [0] ['ZAHLUNGEN_A'] += nummer_komma2punkt($arr [$mo - 1] [$anz_mo] ['ZAHLUNGEN_A']);

                } else {
                    $anz_zeilen = count($arr);

                    ob_clean();
                    // ausgabepuffer leeren
                    $fileName = "$oo->objekt_kurzname - Mietaufstellung $monat-$jahr" . '.xls';
                    header("Content-Type: application/vnd.ms-excel");
                    header("Content-Disposition: attachment; filename=$fileName");
                    header("Content-Disposition: inline; filename=$fileName");
                    ob_clean();
                    // ausgabepuffer leeren
                    echo "<table>";
                    echo "<thead>";
                    echo "<tr>";
                    echo "<th>EINHEIT</th>";
                    echo "<th>NUTZUNG</th>";
                    echo "<th>MIETER</th>";
                    echo "<th>EINZUG</th>";
                    echo "<th>FLÄCHE</th>";
                    echo "<th>KALTMIETE m²</th>";
                    echo "<th>MIETE NETTO</th>";
                    echo "<th>NK</th>";
                    echo "<th>MIETE BRUTTO</th>";
                    echo "<th>MWWST</th>";
                    echo "<th>ZAHLUNG</th>";
                    echo "</tr>";
                    echo "</thead>";

                    for ($z = 0; $z < $anz_zeilen - 1; $z++) {
                        $einheit_kn = $arr [$z] ['EINHEIT_KURZNAME'];
                        $nutzung = $arr [$z] ['TYP'];
                        $mieter = $arr [$z] ['MIETER'];
                        $einzug = $arr [$z] ['MIETER_SEIT'];
                        $qm = $arr [$z] ['EINHEIT_QM_A'];
                        $km_qm = $arr [$z] ['MIETE_KALT_QM_A'];
                        $km_mon = $arr [$z] ['MIETE_KALT_MON_A'];
                        $nk = $arr [$z] ['UMLAGEN'];
                        $wm = $arr [$z] ['MIETE_BRUTTO'];
                        $mwst = $arr [$z] ['MWST'];
                        $zahlung = $arr [$z] ['ZAHLUNGEN'];
                        echo "<tr><td>$einheit_kn</td><td>$nutzung</td><td>$mieter</td><td>$einzug</td><td>$qm</td><td>$km_qm</td><td>$km_mon</td><td>$nk</td><td>$wm</td><td>$mwst</td><td>$zahlung</td></tr>";
                    }
                    echo "</table>";
                }
            } else {
                throw new \App\Exceptions\MessageException(
                    new \App\Messages\ErrorMessage('Keine Mietaufstellungsdaten')
                );
            }
        }

        ob_end_clean();
        // ausgabepuffer leeren

        $pdf->ezTable($jtab, null, "$oo->objekt_kurzname - Mietaufstellung  $jahr");

        $jtab1 [0] ['MONAT_JAHR'] = "$jahr";
        $jtab1 [0] ['EINHEIT_QM_A'] = '--------';
        $jtab1 [0] ['MIETE_KALT_MON_A'] = nummer_punkt2komma_t($jtab1 [0] ['MIETE_KALT_MON_A']);
        $jtab1 [0] ['UMLAGEN_A'] = nummer_punkt2komma_t($jtab1 [0] ['UMLAGEN_A']);
        $jtab1 [0] ['BRUTTOM_A'] = nummer_punkt2komma_t($jtab1 [0] ['BRUTTOM_A']);
        $jtab1 [0] ['ZAHLUNGEN_A'] = nummer_punkt2komma_t($jtab1 [0] ['ZAHLUNGEN_A']);

        $pdf->ezTable($jtab1, null, "$oo->objekt_kurzname - SUMMEN  $jahr");
        $pdf->ezStream();
    }

    function pdf_checkliste($objekt_id)
    {
        $this->get_objekt_infos($objekt_id);
        ob_clean();
        // ausgabepuffer leeren

        $pdf = new Cezpdf ('a4', 'portrait');
        $bpdf = new b_pdf ();
        $bpdf->b_header($pdf, 'Partner', session()->get('partner_id'), 'portrait', 'Helvetica.afm', 6);
        $pdf->ezStopPageNumbers();
        $pdf->ezSetDy(-20);
        $pdf->ezText("<b>CHECKLISTE</b>", 14);
        $pdf->ezText("OBJEKT:", 14);
        $pdf->ezSetMargins(0, 0, 150, 0);
        $pdf->ezSetDy(16);
        $pdf->ezText("$this->objekt_kurzname", 14);

        $haeuser_arr = $this->get_strassennamen($objekt_id);
        $anz_h = count($haeuser_arr);
        if ($anz_h > 0) {

            $strname = '';
            for ($a = 0; $a < $anz_h; $a++) {
                if ($anz_h == 1) {
                    $strname .= $haeuser_arr [$a] ['HAUS_STRASSE'];
                } else {
                    $strname .= $haeuser_arr [$a] ['HAUS_STRASSE'] . ' / ';
                }
            }
        }

        $pdf->ezSetMargins(0, 0, 50, 0);
        $pdf->ezText("STRASSE:", 14);
        $pdf->ezSetMargins(0, 0, 150, 0);
        $pdf->ezSetDy(16);
        $pdf->ezText("$strname", 14);

        $pdf->ezSetMargins(0, 0, 50, 0);
        $pdf->ezText("DATUM:             ________________", 14);
        $det = new detail ();
        $hw_name_tel = strip_tags($det->finde_detail_inhalt('Objekt', $objekt_id, 'Hauswart-Tel.'));
        if (!$hw_name_tel) {
            $pdf->ezText("MITARBEITER:  _____________________________________________", 14);
        } else {
            // $pdf->ezText("MITARBEITER: $hw_name_tel", 14);
            $pdf->addText(50, 700, 14, "<b>Hauswart: $hw_name_tel</b>", 0);
        }

        $pdf->ezSetDy(-30);
        $pdf->ezSetMargins(0, 0, 100, 0);

        $pdf->ellipse(60, $pdf->y - 10, 10);
        $pdf->ezText("TREPPENREINIGUNG", 12);

        $pdf->ezSetDy(-20);
        $pdf->ellipse(60, $pdf->y - 10, 10);
        $pdf->ezText("SPINNENGEWEBE ENTFERNEN", 12);

        $pdf->ezSetDy(-20);
        $pdf->ellipse(60, $pdf->y - 10, 10);
        $pdf->ezText("FENSTERBÄNKE UND BRIEFKÄSTEN", 12);

        $pdf->ezSetDy(-20);
        $pdf->ellipse(60, $pdf->y - 10, 10);
        $pdf->ezText("GELÄNDER / HANDLAUF", 12);

        $pdf->ezSetDy(-20);
        $pdf->ellipse(60, $pdf->y - 10, 10);
        $pdf->ezText("LAMPEN KONTROLLIEREN / GETAUSCHT", 12);

        $pdf->ezSetDy(-20);
        $pdf->ellipse(60, $pdf->y - 10, 10);
        $pdf->ezText("KELLER FEGEN", 12);

        $pdf->ezSetDy(-20);
        $pdf->ellipse(60, $pdf->y - 10, 10);
        $pdf->ezText("MÜLLPLATZ FEGEN", 12);

        $pdf->ezSetDy(-20);
        $pdf->ellipse(60, $pdf->y - 10, 10);
        $pdf->ezText("TÜRSCHLIESSER KONTROLLIEREN / EINSTELLEN", 12);

        $pdf->ezSetDy(-20);
        $pdf->ellipse(60, $pdf->y - 10, 10);
        $pdf->ezText("SPERMÜLLBESEITIGUNG", 12);

        $pdf->ezSetDy(-20);
        $pdf->ellipse(60, $pdf->y - 10, 10);
        $pdf->ezText("WINTERDIENST", 12);

        $pdf->ezSetDy(-20);
        $pdf->ellipse(60, $pdf->y - 10, 10);
        $pdf->ezText("LAUBBESEITIGUNG / GARTENARBEIT", 12);

        $pdf->ezSetMargins(0, 0, 50, 0);

        $pdf->ezSetDy(-20);
        $pdf->ezText("<u>SONSTIGE HINWEISE AN / VOM HAUSWART:</u>", 12);

        ob_end_clean();
        $pdf->ezStream();
    }

    function get_strassennamen($objekt_id)
    {
        $result = DB::select("SELECT HAUS_STRASSE FROM HAUS WHERE HAUS_AKTUELL='1' && OBJEKT_ID='$objekt_id' GROUP BY HAUS_STRASSE");
        return $result;
    }

    function form_objekt_anlegen()
    {
        $f = new formular ();
        $f->erstelle_formular("Neues Objekt erstellen", NULL);
        $f->text_feld("Objekt Kurzname", "objekt_kurzname", "", "30", 'objekt_kurzname', '');
        $partner = new partner ();
        $partner->partner_dropdown('Eigentümer', 'eigentuemer', 'eigentuemer');
        $f->hidden_feld("objekte_raus", "objekt_speichern");
        $f->send_button("submit_obj", "Objekt erstellen");
        $f->ende_formular();
    }

    function form_objekt_aendern($objekt_id)
    {
        $this->get_objekt_infos($objekt_id);
        $f = new formular ();
        $f->erstelle_formular("Objekt $this->objekt_kurzname ändern", NULL);
        $f->text_feld("Objekt Kurzname", "objekt_kurzname", "$this->objekt_kurzname", "30", 'objekt_kurzname', '');
        $partner = new partner ();
        $partner->partner_dropdown('Eigentümer', 'eigentuemer', 'eigentuemer');
        $f->hidden_feld("objekt_id", "$this->objekt_id");
        $f->hidden_feld("objekt_dat", "$this->objekt_dat");
        $f->hidden_feld("objekte_raus", "objekt_aendern_send");
        $f->send_button("submit_obj1", "Objekt ändern");
        $f->ende_formular();
    }

    function objekt_aendern($objekt_dat, $objekt_id, $objekt_kurzname, $eigentuemer_id)
    {
        /* Deaktivieren */
        $db_abfrage = "UPDATE OBJEKT SET OBJEKT_AKTUELL='0' WHERE OBJEKT_DAT='$objekt_dat'";
        DB::update($db_abfrage);

        /* Änderung Speichern */
        $db_abfrage = "INSERT INTO OBJEKT VALUES(NULL, '$objekt_id', '1', '$objekt_kurzname','$eigentuemer_id')";
        DB::insert($db_abfrage);

        /* Protokollieren */
        $last_dat = DB::getPdo()->lastInsertId();
        protokollieren('OBJEKT', $last_dat, $objekt_dat);
    }

    function date_mysql2german($date)
    {
        $d = explode("-", $date);
        return sprintf("%02d.%02d.%04d", $d [2], $d [1], $d [0]);
    }

    function date_german2mysql($date)
    {
        $d = explode(".", $date);
        return sprintf("%04d-%02d-%02d", $d [2], $d [1], $d [0]);
    }

    function datum_plus_tage($startdatum, $tage)
    {
        $db_datum = $startdatum;
        list ($db_y, $db_m, $db_t) = explode("-", $db_datum);
        $neues_datum = date("Y-m-d", mktime(0, 0, 0, $db_m, $db_t + $tage, $db_y));
        return $neues_datum;
    }

    /* Funktion zur Ermittlung allgemein/notwendiger Objektinformationen */

    function datum_minus_tage($startdatum, $tage)
    {
        $db_datum = $startdatum;
        list ($db_y, $db_m, $db_t) = explode("-", $db_datum);
        $neues_datum = date("Y-m-d", mktime(0, 0, 0, $db_m, $db_t - $tage, $db_y));
        return $neues_datum;
    }

    function tage_berechnen_bis_heute($start_datum)
    {
        $heute = mktime(date("h"), date("i"), date("s"), date("m"), date("d"), date("Y"));
        $start_datum_arr = explode(".", $start_datum);
        $tag = $start_datum_arr [0];
        $monat = $start_datum_arr [1];
        $jahr = $start_datum_arr [2];
        $beginn_datum = mktime(0, 0, 0, $monat, $tag, $jahr);
        $tage_vergangen = round(($heute - $beginn_datum) / (3600 * 24), 0);
        // echo "<h3>Seit ".$tag.".".$monat.".".$jahr." sind ".$tage_vergangen.
        " Tage vergangen</h3>";
        // $monate_vergangen = round(($tage_vergangen/30),0);
        // echo "Monate $monate_vergangen";
        return $tage_vergangen;
    }

    function monate_berechnen_bis_heute($start_datum)
    {
        $heute = time();
        $start_datum_arr = explode(".", $start_datum);
        $tag = $start_datum_arr [0];
        $monat = $start_datum_arr [1];
        $jahr = $start_datum_arr [2];
        $beginn_datum = mktime(0, 0, 0, $monat, $tag, $jahr);
        $tage_vergangen = round(($heute - $beginn_datum) / (3600 * 24), 0);
        // echo "<h3>Seit ".$tag.".".$monat.".".$jahr." sind ".$tage_vergangen.
        // " Tage vergangen</h3>\n";
        $monate_vergangen = floor($tage_vergangen / 30);
        // echo "Monate $monate_vergangen";
        return $monate_vergangen;
    }

    function get_objekt_eigentuemer_partner($objekt_id)
    {
        $result = DB::select("SELECT EIGENTUEMER_PARTNER FROM OBJEKT WHERE OBJEKT_AKTUELL='1' && id='$objekt_id' ORDER BY OBJEKT_DAT DESC LIMIT 0,1");
        $row = $result[0];
        $this->objekt_eigentuemer_partner_id = $row ['EIGENTUEMER_PARTNER'];
    }

    function objekt_informationen($objekt_id)
    {
        $this->objekt_name = $this->get_objekt_name($objekt_id);
        $this->objekt_id = $objekt_id;
        $geld_konto_info = new geldkonto_info ();
        $this->anzahl_geld_konten = $geld_konto_info->geldkonten_anzahl('Objekt', $objekt_id);
        if ($this->anzahl_geld_konten > 0) {
            $this->geld_konten_arr = $geld_konto_info->geldkonten_arr('Objekt', $objekt_id);
        }
    }

    function get_objekt_name($objekt_id)
    {
        $result = DB::select("SELECT OBJEKT_KURZNAME FROM OBJEKT WHERE OBJEKT_AKTUELL='1' && id='$objekt_id' ORDER BY OBJEKT_DAT DESC LIMIT 0,1");
        $row = $result[0];
        $this->objekt_name = $row ['OBJEKT_KURZNAME'];
        return $row ['OBJEKT_KURZNAME'];
    }

    function get_objekt_geldkonto_nr($objekt_id)
    {
        $result = DB::select("SELECT DETAIL_INHALT FROM `DETAIL` WHERE DETAIL_NAME = 'Geld Konto Nummer' && DETAIL_ZUORDNUNG_TABELLE = 'Objekt' && DETAIL_ZUORDNUNG_ID = '$objekt_id' ORDER BY DETAIL_DAT DESC LIMIT 0 , 1 ");
        $row = $result[0];
        $this->objekt_kontonummer = $row ['DETAIL_INHALT'];
    }

    function liste_aller_objekte_kurz()
    {
        $objekte_array = DB::select("SELECT OBJEKT.id AS OBJEKT_ID, OBJEKT_KURZNAME FROM OBJEKT WHERE OBJEKT_AKTUELL='1' ORDER BY OBJEKT_KURZNAME ASC");
        $this->anzahl_objekte = count($objekte_array);
        return $objekte_array;
    }

    function dropdown_haeuser_objekt($objekt_id, $label, $name, $id, $vorwahl = '')
    {
        $haus_arr = $this->haeuser_objekt_in_arr($objekt_id);
        echo "<label for=\"$id\">$label</label><select name=\"$name\" size=1 id=\"$id\">\n";
        for ($a = 0; $a < count($haus_arr); $a++) {
            $hh = new haus ();
            $haus_id = $haus_arr [$a] ['HAUS_ID'];
            $hh->get_haus_info($haus_id);
            $haus_str = $haus_arr [$a] ['HAUS_STRASSE'];
            $haus_nr = $haus_arr [$a] ['HAUS_NUMMER'];
            if ($vorwahl == $haus_id) {
                echo "<option value=\"$haus_id\" selected>$haus_str $haus_nr $hh->objekt_name</option>\n";
            } else {
                echo "<option value=\"$haus_id\">$haus_str $haus_nr $hh->objekt_name</option>\n";
            }
        }
        echo "</select>\n";
    }

    function anzahl_haeuser_objekt($objekt_id)
    {
        $result = DB::select("SELECT id FROM HAUS WHERE HAUS_AKTUELL='1' && OBJEKT_ID='$objekt_id' ORDER BY HAUS_STRASSE, HAUS_NUMMER ASC;");
        $this->anzahl_haeuser = count($result);
        $this->seiten_anzahl = ceil($this->anzahl_haeuser / $this->zeilen_pro_seite);
    }

    function get_qm_gesamt($objekt_id)
    {
        $result = DB::select("SELECT OBJEKT_KURZNAME, SUM(EINHEIT_QM) AS GESAMT_QM FROM `EINHEIT` RIGHT JOIN (HAUS, OBJEKT) ON ( EINHEIT.HAUS_ID = HAUS.id && HAUS.OBJEKT_ID = OBJEKT.id && OBJEKT.id = '$objekt_id' ) WHERE EINHEIT_AKTUELL='1' && HAUS_AKTUELL='1' && OBJEKT_AKTUELL='1' GROUP BY OBJEKT.id ORDER BY EINHEIT_KURZNAME ASC");
        if (!empty($result)) {
            $row = $result[0];
            return $row ['GESAMT_QM'];
        } else {
            return '0.00';
        }
    }

    function get_qm_gesamt_gewerbe($objekt_id)
    {
        $result = DB::select("SELECT OBJEKT_KURZNAME, SUM(EINHEIT_QM) AS GESAMT_QM FROM `EINHEIT` RIGHT JOIN (HAUS, OBJEKT) ON ( EINHEIT.HAUS_ID = HAUS.id && HAUS.OBJEKT_ID = OBJEKT.id && OBJEKT.id = '$objekt_id' ) WHERE EINHEIT_AKTUELL='1'  && EINHEIT.TYP = 'Gewerbe' GROUP BY OBJEKT.id ORDER BY EINHEIT_KURZNAME ASC");
        if (!empty($result)) {
            $row = $result[0];
            return $row ['GESAMT_QM'];
        } else {
            return '0.00';
        }
    }

    function einheiten_objekt_arr($objekt_id)
    {
        $result = DB::select("SELECT OBJEKT_KURZNAME, EINHEIT.id AS EINHEIT_ID, EINHEIT_KURZNAME, EINHEIT_LAGE, EINHEIT_QM,  HAUS_STRASSE, HAUS_NUMMER, HAUS_PLZ, HAUS_STADT, TYP FROM `EINHEIT` RIGHT JOIN (HAUS, OBJEKT
) ON ( EINHEIT.HAUS_ID = HAUS.id && HAUS.OBJEKT_ID = OBJEKT.id && OBJEKT.id = '$objekt_id' )
WHERE EINHEIT_AKTUELL='1' && HAUS_AKTUELL='1' && OBJEKT_AKTUELL='1' GROUP BY EINHEIT.id ORDER BY LPAD(EINHEIT_KURZNAME, LENGTH(EINHEIT_KURZNAME), '1') ASC");
        return $result;
    }

    function anzahl_einheiten_objekt($objekt_id)
    {
        $result = DB::select("SELECT OBJEKT_KURZNAME, EINHEIT.id AS EINHEIT_ID, EINHEIT_KURZNAME, EINHEIT_LAGE, EINHEIT_QM,  HAUS_STRASSE, HAUS_NUMMER FROM `EINHEIT`
RIGHT JOIN (HAUS, OBJEKT) ON ( EINHEIT.HAUS_ID = HAUS.id && HAUS.OBJEKT_ID = OBJEKT.id && OBJEKT.id = '$objekt_id' )
WHERE EINHEIT_AKTUELL='1' GROUP BY EINHEIT.id ORDER BY EINHEIT_KURZNAME ASC");
        $anzahl = count($result);
        return $anzahl;
    }
} // end class objekt
