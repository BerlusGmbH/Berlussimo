<?php

class listen
{
    public $gmon_obj;
    public $et_tab;
    public $gmon_et;
    public $pdf_tab_g;
    public $kurz_b;
    public $report_von_neu;
    public $report_bis_neu;
    public $kto_bez_en;
    public $kto_bez_de;
    public $saldo_et;
    public $kauf_leer;
    public $kauf_vermietet;
    public $gk_id;
    public $objekt_id;
    public $partner_id;
    public $report_von;
    public $report_von_neu_d;
    public $report_bis_neu_d;
    public $saldo_et_vm;

    function inspiration_pdf($ausgezogene = 0, $objekt_id, $monat, $jahr, $lang = 'de')
    {
        $monat_name = monat2name($monat);
        $pdf = new Cezpdf ('a4', 'portrait');
        $bpdf = new b_pdf ();
        $bpdf->b_header($pdf, 'Partner', session()->get('partner_id'), 'portrait', 'Helvetica.afm', 6);
        $pdf->ezStopPageNumbers(); // seitennummerirung beenden
        $gk = new geldkonto_info ();

        $gk->geld_konto_ermitteln('OBJEKT', $objekt_id);
        echo '<pre>';
        print_r($gk);

        $db_abfrage = "SELECT OBJEKT_KURZNAME, HAUS_STRASSE, HAUS_NUMMER, `EINHEIT_KURZNAME` , `EINHEIT_ID`,  ltrim(rtrim(EINHEIT_LAGE)) AS EINHEIT_LAGE, `EINHEIT_QM` FROM EINHEIT , HAUS, OBJEKT
WHERE `EINHEIT_AKTUELL` = '1' && EINHEIT.HAUS_ID = HAUS.HAUS_ID && HAUS.OBJEKT_ID=OBJEKT.OBJEKT_ID && HAUS_AKTUELL='1' && OBJEKT_AKTUELL='1' && OBJEKT.OBJEKT_ID='$objekt_id' 
ORDER BY EINHEIT_KURZNAME";

        $result = DB::select($db_abfrage);
        if (!empty($result)) {
            $z = 0;
            foreach ($result as $row) {
                $my_arr [] = $row;
                $einheit_id = $row ['EINHEIT_ID'];
                $e = new einheit ();
                $det = new detail ();
                $my_arr [$z] ['WEG-FLAECHE_A'] = $det->finde_detail_inhalt('EINHEIT', $einheit_id, 'WEG-Fläche'); // kommt als Kommazahl
                $my_arr [$z] ['WEG-FLAECHE'] = nummer_komma2punkt($my_arr [$z] ['WEG-FLAECHE_A']);
                $weg = new weg ();
                $weg->get_last_eigentuemer($einheit_id);
                if (isset ($weg->eigentuemer_name)) {
                    $weg->get_eigentuemer_namen($weg->eigentuemer_id);
                    $my_arr [$z] ['EIGENTUEMER_NAMEN'] = $weg->eigentuemer_name_str_u;
                    $my_arr [$z] ['EIGENTUEMER_ID'] = $weg->eigentuemer_id;
                } else {
                    $my_arr [$z] ['EIGENTUEMER'] = 'Unbekannt';
                }
                $mv_id = $e->get_mietvertrag_id($einheit_id);
                if ($mv_id) {
                    $mvs = new mietvertraege ();
                    $mvs->get_mietvertrag_infos_aktuell($mv_id);
                    $kontaktdaten = $e->kontaktdaten_mieter($mv_id);
                    $my_arr [$z] ['MIETER'] = $mvs->personen_name_string_u;
                    $my_arr [$z] ['MIETVERTRAG_ID'] = $mv_id;
                    $my_arr [$z] ['KONTAKT'] = $kontaktdaten;
                    $my_arr [$z] ['MIETVERTRAG_ID'] = $mv_id;
                    $mz = new miete ();
                    $mz->mietkonto_berechnung($mv_id);
                    $my_arr [$z] ['MIETER_SALDO'] = $mz->erg;
                } else {
                    $my_arr [$z] ['MIETER'] = 'Leerstand';
                }
                $z++;
            }

            unset ($e);
            unset ($mvs);
            unset ($weg);

            $anz = count($my_arr);
            /* Berechnung Abgaben */
            for ($a = 0; $a < $anz; $a++) {
                if (isset ($my_arr [$a] ['EIGENTUEMER_ID'])) {
                    $my_arr [$a] ['ABGABEN'] [] ['ABGABE_IHR'] = $my_arr [$a] ['WEG-FLAECHE'] * 0.4;
                    $my_arr [$a] ['ABGABEN'] [] ['VG'] = '30.00'; // Verwaltergebühr

                    /* Kosten 1023 Reparatur Einheit */
                    $my_arr [$a] ['AUSGABEN'] = $this->get_kosten_arr('EINHEIT', $my_arr [$a] ['EINHEIT_ID'], $monat, $jahr, $gk->geldkonto_id, 1023);
                    $anz_rep = count($my_arr [$a] ['AUSGABEN']);
                    $summe_rep = 0;
                    for ($b = 0; $b < $anz_rep; $b++) {
                        $summe_rep += $my_arr [$a] ['AUSGABEN'] [$b] ['BETRAG'];
                    }
                    $my_arr [$a] ['AUSGABEN'] [$anz_rep] ['BETRAG'] = '<b>' . nummer_punkt2komma_t($summe_rep) . '</b>';
                    $my_arr [$a] ['AUSGABEN'] [$anz_rep] ['VERWENDUNGSZWECK'] = '<b>Summe</b>';

                    // echo "'EINHEIT', $my_arr[$a]['EINHEIT_ID'], $monat, $jahr, $gk->geldkonto_id<br>";
                    // print_r($arr);
                    $mk = new mietkonto ();
                    $mk->kaltmiete_monatlich($my_arr [$a] ['MIETVERTRAG_ID'], $monat, $jahr);
                    $brutto_sollmiete_arr = explode('|', $mk->summe_forderung_monatlich($my_arr [$a] ['MIETVERTRAG_ID'], $monat, $jahr));
                    $brutto_sollmiete = $brutto_sollmiete_arr [0];
                    $my_arr [$a] ['NETTO_SOLL'] = $mk->ausgangs_kaltmiete;
                    $my_arr [$a] ['BRUTTO_SOLL'] = $brutto_sollmiete;
                    $my_arr [$a] ['IST_EINNAHMEN'] = $this->get_kosten_arr('MIETVERTRAG', $my_arr [$a] ['MIETVERTRAG_ID'], $monat, $jahr, $gk->geldkonto_id, 80001);
                    $anz_me = count($my_arr [$a] ['IST_EINNAHMEN']);
                    $summe_einnahmen = 0;
                    for ($b = 0; $b < $anz_me; $b++) {
                        $summe_einnahmen += $my_arr [$a] ['IST_EINNAHMEN'] [$b] ['BETRAG'];
                    }
                    $my_arr [$a] ['IST_EINNAHMEN'] [$anz_me] ['BETRAG'] = '<b>' . nummer_punkt2komma_t($summe_einnahmen) . '</b>';
                    $my_arr [$a] ['IST_EINNAHMEN'] [$anz_me] ['VERWENDUNGSZWECK'] = '<b>Summe</b>';
                    $my_arr [$a] ['BRUTTO_IST'] = $summe_einnahmen;
                    // $my_arr[$a]['SUM_EIN_AUS_MIETE'] =

                    $pdf_tab [$a] ['MIETER_SALDO'] = $my_arr [$a] ['MIETER_SALDO'];
                    $pdf_tab [$a] ['MV_ID'] = $my_arr [$a] ['MIETVERTRAG_ID'];
                    $pdf_tab [$a] ['EIGENTUEMER_NAMEN'] = $my_arr [$a] ['EIGENTUEMER_NAMEN'];
                    $pdf_tab [$a] ['EINHEIT_KURZNAME'] = $my_arr [$a] ['EINHEIT_KURZNAME'];
                    $pdf_tab [$a] ['EINHEIT_QM'] = $my_arr [$a] ['EINHEIT_QM'];
                    $pdf_tab [$a] ['WEG-FLAECHE'] = $my_arr [$a] ['WEG-FLAECHE'];
                    $pdf_tab [$a] ['WEG-FLAECHE_A'] = $my_arr [$a] ['WEG-FLAECHE_A'];

                    $pdf_tab [$a] ['MIETER'] = $my_arr [$a] ['MIETER'];
                    $pdf_tab [$a] ['BRUTTO_SOLL'] = $my_arr [$a] ['BRUTTO_SOLL'];
                    $pdf_tab [$a] ['BRUTTO_SOLL_A'] = nummer_punkt2komma_t($my_arr [$a] ['BRUTTO_SOLL']);
                    $pdf_tab [$a] ['BRUTTO_IST'] = $my_arr [$a] ['BRUTTO_IST'];
                    $pdf_tab [$a] ['BRUTTO_IST_A'] = nummer_punkt2komma_t($my_arr [$a] ['BRUTTO_IST']);
                    $pdf_tab [$a] ['DIFF'] = $my_arr [$a] ['BRUTTO_IST'] - $pdf_tab [$a] ['BRUTTO_SOLL'];
                    $pdf_tab [$a] ['DIFF_A'] = nummer_punkt2komma_t($my_arr [$a] ['BRUTTO_IST'] - $pdf_tab [$a] ['BRUTTO_SOLL']);

                    $pdf_tab [$a] ['NETTO_SOLL'] = $my_arr [$a] ['NETTO_SOLL'];
                    $pdf_tab [$a] ['NETTO_SOLL_A'] = nummer_punkt2komma_t($my_arr [$a] ['NETTO_SOLL']);
                    $pdf_tab [$a] ['ABGABE_IHR'] = $my_arr [$a] ['WEG-FLAECHE'] * -0.4;
                    $pdf_tab [$a] ['ABGABE_IHR_A'] = nummer_punkt2komma_t($my_arr [$a] ['WEG-FLAECHE'] * -0.4);
                    $pdf_tab [$a] ['ABGABE_HV'] = '-30.00';
                    $pdf_tab [$a] ['ABGABE_HV_A'] = '-30,00';
                    $pdf_tab [$a] ['ZWISCHENSUMME'] = $my_arr [$a] ['NETTO_SOLL'] + $pdf_tab [$a] ['ABGABE_IHR'] + $pdf_tab [$a] ['ABGABE_HV'];
                    $pdf_tab [$a] ['ZWISCHENSUMME_A'] = nummer_punkt2komma_t($my_arr [$a] ['NETTO_SOLL'] + $pdf_tab [$a] ['ABGABE_IHR'] + $pdf_tab [$a] ['ABGABE_HV']);
                    $pdf_tab [$a] ['SUMME_REP'] = $summe_rep;
                    $pdf_tab [$a] ['SUMME_REP_A'] = nummer_punkt2komma_t($summe_rep);
                    $pdf_tab [$a] ['ENDSUMME'] = $pdf_tab [$a] ['ZWISCHENSUMME'] + $summe_rep;
                    $pdf_tab [$a] ['ENDSUMME_A'] = '<b>' . nummer_punkt2komma_t($pdf_tab [$a] ['ZWISCHENSUMME'] + $summe_rep) . '</b>';

                    $ein_nam = $pdf_tab [$a] ['EINHEIT_KURZNAME'];
                    if ($lang == 'en') {
                        $cols = array(
                            'MIETER_SALDO' => "Saldo",
                            'EIGENTUEMER_NAMEN' => "owner",
                            'EINHEIT_KURZNAME' => "apart.No",
                            'MIETER' => 'tenant',
                            'WEG-FLAECHE_A' => 'size m²',
                            'BRUTTO_SOLL_A' => 'to cash g.',
                            'BRUTTO_IST_A' => 'paid g.',
                            'DIFF_A' => 'diff.',
                            'NETTO_SOLL_A' => 'net rent',
                            'ABGABE_IHR_A' => 'for maint.',
                            'ABGABE_HV_A' => 'mng. Fee',
                            'SUMME_REP_A' => 'maint. bills',
                            'ENDSUMME_A' => 'pay off'
                        );
                        $pdf->ezTable($pdf_tab, $cols, "<b>$monat_name $jahr - Overview - $ein_nam</b>", array(
                            'showHeadings' => 1,
                            'shaded' => 1,
                            'titleFontSize' => 8,
                            'fontSize' => 7,
                            'xPos' => 50,
                            'xOrientation' => 'right',
                            'width' => 500,
                            'cols' => array(
                                'ENDSUMME_A' => array(
                                    'justification' => 'right',
                                    'width' => 50
                                ),
                                'EIGENTUEMER_NAMEN' => array(
                                    'justification' => 'left',
                                    'width' => 50
                                )
                            )
                        ));
                    } else {
                        $cols = array(
                            'EIGENTUEMER_NAMEN' => "Eigentümer",
                            'EINHEIT_KURZNAME' => "EINHEIT",
                            'MIETER' => 'Mieter',
                            'WEG-FLAECHE_A' => 'Eig. m²',
                            'BRUTTO_SOLL_A' => 'Warm SOLL',
                            'BRUTTO_IST_A' => 'Warm IST',
                            'DIFF_A' => 'DIFF',
                            'NETTO_SOLL_A' => 'rent p.m.\n (actual)',
                            'ABGABE_IHR_A' => 'IHR',
                            'ABGABE_HV_A' => 'HV',
                            'SUMME_REP_A' => 'Rep.',
                            'ENDSUMME_A' => 'AUSZAHLUNG'
                        );
                        $pdf->ezTable($pdf_tab, $cols, "<b>$monat_name $jahr - Gesamtübersicht - $ein_nam</b>", array(
                            'showHeadings' => 1,
                            'shaded' => 1,
                            'titleFontSize' => 8,
                            'fontSize' => 7,
                            'xPos' => 30,
                            'xOrientation' => 'right',
                            'width' => 550,
                            'cols' => array(
                                'ENDSUMME_A' => array(
                                    'justification' => 'right',
                                    'width' => 50
                                ),
                                'EIGENTUEMER_NAMEN' => array(
                                    'justification' => 'left',
                                    'width' => 50
                                )
                            )
                        ));
                    }

                    if ($pdf_tab [$a] ['BRUTTO_IST'] < $pdf_tab [$a] ['ENDSUMME']) {
                        $pdf->setColor(1.0, 0.0, 0.0);
                        $pdf->ezSetDy(-20); // abstand
                        if ($lang == 'en') {
                            $pdf->ezText("payout not possible!", 12);
                        } else {
                            $pdf->ezText("Keine Auszahlung möglich!", 12);
                        }
                    }

                    $pdf->ezSetDy(-20); // abstand
                    if (is_array($my_arr [$a] ['AUSGABEN'])) {
                        if ($lang == 'en') {
                            $cols = array(
                                'DATUM' => "Date",
                                'VERWENDUNGSZWECK' => "Description",
                                'BETRAG' => "Amount"
                            );
                            $pdf->ezTable($my_arr [$a] ['AUSGABEN'], $cols, "<b>$monat_name $jahr - Maintenance bills 1023 - $ein_nam</b>", array(
                                'showHeadings' => 1,
                                'shaded' => 1,
                                'titleFontSize' => 8,
                                'fontSize' => 7,
                                'xPos' => 50,
                                'xOrientation' => 'right',
                                'width' => 500,
                                'cols' => array(
                                    'BETRAG' => array(
                                        'justification' => 'right',
                                        'width' => 65
                                    ),
                                    'DATUM' => array(
                                        'justification' => 'left',
                                        'width' => 50
                                    )
                                )
                            ));
                        } else {
                            $cols = array(
                                'DATUM' => "Datum",
                                'VERWENDUNGSZWECK' => "Buchungstext",
                                'BETRAG' => "Betrag"
                            );
                            $pdf->ezTable($my_arr [$a] ['AUSGABEN'], $cols, "<b>$monat_name $jahr - Reparaturen 1023 - $ein_nam</b>", array(
                                'showHeadings' => 1,
                                'shaded' => 1,
                                'titleFontSize' => 8,
                                'fontSize' => 7,
                                'xPos' => 50,
                                'xOrientation' => 'right',
                                'width' => 500,
                                'cols' => array(
                                    'BETRAG' => array(
                                        'justification' => 'right',
                                        'width' => 65
                                    ),
                                    'DATUM' => array(
                                        'justification' => 'left',
                                        'width' => 50
                                    )
                                )
                            ));
                        }
                    } else {
                        $pdf->ezText("Keine Reparaturen", 12);
                    }
                    $pdf->ezSetDy(-20); // abstand
                    if (is_array($my_arr [$a] ['IST_EINNAHMEN'])) {
                        if ($lang == 'en') {
                            $cols = array(
                                'DATUM' => "Date",
                                'VERWENDUNGSZWECK' => "Description",
                                'BETRAG' => "Amount"
                            );
                            $pdf->ezTable($my_arr [$a] ['IST_EINNAHMEN'], $cols, "<b>$monat_name $jahr - income overview 80001 - $ein_nam</b>", array(
                                'showHeadings' => 1,
                                'shaded' => 1,
                                'titleFontSize' => 8,
                                'fontSize' => 7,
                                'xPos' => 50,
                                'xOrientation' => 'right',
                                'width' => 500,
                                'cols' => array(
                                    'BETRAG' => array(
                                        'justification' => 'right',
                                        'width' => 65
                                    ),
                                    'DATUM' => array(
                                        'justification' => 'left',
                                        'width' => 50
                                    )
                                )
                            ));
                        } else {
                            $cols = array(
                                'DATUM' => "Datum",
                                'VERWENDUNGSZWECK' => "Buchungstext",
                                'BETRAG' => "Betrag"
                            );
                            $pdf->ezTable($my_arr [$a] ['IST_EINNAHMEN'], $cols, "<b>$monat_name $jahr - Einnahmen 80001 - $ein_nam</b>", array(
                                'showHeadings' => 1,
                                'shaded' => 1,
                                'titleFontSize' => 8,
                                'fontSize' => 7,
                                'xPos' => 50,
                                'xOrientation' => 'right',
                                'width' => 500,
                                'cols' => array(
                                    'BETRAG' => array(
                                        'justification' => 'right',
                                        'width' => 65
                                    ),
                                    'DATUM' => array(
                                        'justification' => 'left',
                                        'width' => 50
                                    )
                                )
                            ));
                        }
                    } else {
                        $pdf->ezText("Keine Mieteinnahmen", 12);
                    }

                    // $cols = array('DATUM'=>"Datum",'VERWENDUNGSZWECK'=>"Buchungstext", 'BETRAG'=>"Betrag");
                    // $pdf->ezTable($pdf_tab[$a]['EIG_AUSZAHLUNG'], $cols, "<b>$monat_name $jahr - Auszahlung an Eigentümer 80001 - $ein_nam</b>", array('showHeadings'=>1,'shaded'=>1, 'titleFontSize' => 8, 'fontSize' => 7, 'xPos'=>50,'xOrientation'=>'right', 'width'=>500, 'cols'=>array('BETRAG'=>array('justification'=>'right', 'width'=>65),'DATUM'=>array('justification'=>'left', 'width'=>50))));

                    if ($my_arr [$a] ['MIETVERTRAG_ID']) {
                        $pdf->ezNewPage();
                        $miete = new miete ();
                        $miete->mietkontenblatt2pdf($pdf, $my_arr [$a] ['MIETVERTRAG_ID']);
                    }

                    $pdf->ezNewPage();
                    unset ($pdf_tab);
                }
            }

            ob_end_clean(); // ausgabepuffer leeren
            $pdf->ezStream();
        } else {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\InfoMessage("Keine Einheiten im Objekt $objekt_id")
            );
        }
    }

    function get_kosten_arr($kos_typ, $kos_id, $monat, $jahr, $gk_id, $konto = null)
    {

        // echo "$kos_typ, $kos_id, $monat, $jahr, $gk_id, $konto=null <br>";
        if ($konto == null) {
            $db_abfrage = "SELECT * FROM `GELD_KONTO_BUCHUNGEN` WHERE `GELDKONTO_ID` = '$gk_id' AND DATE_FORMAT( `DATUM` , '%Y-%m' ) = '$jahr-$monat' AND `KOSTENTRAEGER_TYP` = '$kos_typ' AND `KOSTENTRAEGER_ID` = '$kos_id' AND `AKTUELL` = '1' ORDER BY KONTENRAHMEN_KONTO, DATUM";
        } else {
            $db_abfrage = "SELECT * FROM `GELD_KONTO_BUCHUNGEN` WHERE `GELDKONTO_ID` = '$gk_id' AND DATE_FORMAT( `DATUM` , '%Y-%m' ) = '$jahr-$monat' AND `KOSTENTRAEGER_TYP` = '$kos_typ' AND `KOSTENTRAEGER_ID` = '$kos_id' AND `AKTUELL` = '1' && KONTENRAHMEN_KONTO='$konto' ORDER BY KONTENRAHMEN_KONTO, DATUM";
        }
        // echo $db_abfrage;
        $result = DB::select($db_abfrage);
        return $result;
    }

    function inspiration_pdf_kurz($ausgezogene = 0, $objekt_id, $monat, $jahr, $lang = 'de')
    {
        $monat_name = monat2name($monat);
        $pdf = new Cezpdf ('a4', 'portrait');
        $bpdf = new b_pdf ();
        $bpdf->b_header($pdf, 'Partner', session()->get('partner_id'), 'portrait', 'Helvetica.afm', 6);
        $pdf->ezStopPageNumbers(); // seitennummerirung beenden
        $gk = new geldkonto_info ();
        $gk->geld_konto_ermitteln('OBJEKT', $objekt_id);
        echo '<pre>';
        print_r($gk);
        if (!$gk->geldkonto_id) {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\WarningMessage('Geldkonto zum Objekt hinzufügen.')
            );
        }
        $db_abfrage = "SELECT OBJEKT_KURZNAME, HAUS_STRASSE, HAUS_NUMMER, `EINHEIT_KURZNAME` , `EINHEIT_ID`,  ltrim(rtrim(EINHEIT_LAGE)) AS EINHEIT_LAGE, `EINHEIT_QM` FROM EINHEIT , HAUS, OBJEKT
WHERE `EINHEIT_AKTUELL` = '1' && EINHEIT.HAUS_ID = HAUS.HAUS_ID && HAUS.OBJEKT_ID=OBJEKT.OBJEKT_ID && HAUS_AKTUELL='1' && OBJEKT_AKTUELL='1' && OBJEKT.OBJEKT_ID='$objekt_id' 
ORDER BY EINHEIT_KURZNAME";

        $result = DB::select($db_abfrage);
        if (!empty($result)) {
            $z = 0;
            foreach ($result as $row) {
                $my_arr [] = $row;
                $einheit_id = $row ['EINHEIT_ID'];
                $einheit_qm = $row ['EINHEIT_QM'];
                $e = new einheit ();
                $det = new detail ();
                $my_arr [$z] ['WEG-FLAECHE_A'] = $det->finde_detail_inhalt('EINHEIT', $einheit_id, 'WEG-Fläche'); // kommt als Kommazahl
                if (empty ($my_arr [$z] ['WEG-FLAECHE_A'])) {
                    $my_arr [$z] ['WEG-FLAECHE_A'] = nummer_punkt2komma($einheit_qm);
                }
                $my_arr [$z] ['WEG-FLAECHE'] = nummer_komma2punkt($my_arr [$z] ['WEG-FLAECHE_A']);

                $my_arr [$z] ['WG_NR'] = $det->finde_detail_inhalt('EINHEIT', $einheit_id, 'Alte Nr'); // kommt als Kommazahl

                $weg = new weg ();
                $weg->get_last_eigentuemer($einheit_id);
                if (isset ($weg->eigentuemer_name)) {
                    $weg->get_eigentuemer_namen($weg->eigentuemer_id);
                    $my_arr [$z] ['EIGENTUEMER_NAMEN'] = $weg->eigentuemer_name_str_u;
                    $my_arr [$z] ['EIGENTUEMER_ID'] = $weg->eigentuemer_id;
                } else {
                    $my_arr [$z] ['EIGENTUEMER'] = 'Unbekannt';
                }
                $mv_id = $e->get_mietvertrag_id($einheit_id);
                if ($mv_id) {
                    $mvs = new mietvertraege ();
                    $mvs->get_mietvertrag_infos_aktuell($mv_id);
                    $kontaktdaten = $e->kontaktdaten_mieter($mv_id);
                    $my_arr [$z] ['MIETER'] = $mvs->personen_name_string_u;
                    $my_arr [$z] ['MIETVERTRAG_ID'] = $mv_id;
                    $my_arr [$z] ['KONTAKT'] = $kontaktdaten;
                    $my_arr [$z] ['EINHEIT_ID'] = $einheit_id;
                    $mz = new miete ();
                    $mz->mietkonto_berechnung($mv_id);
                    $my_arr [$z] ['MIETER_SALDO'] = $mz->erg;
                } else {
                    $my_arr [$z] ['MIETER'] = 'Leerstand';
                }
                $z++;
            }

            unset ($e);
            unset ($mvs);
            unset ($weg);

            $anz = count($my_arr);
            /* Berechnung Abgaben */
            for ($a = 0; $a < $anz; $a++) {
                $einheit_id = $my_arr [$a] ['EINHEIT_ID'];
                if (isset ($my_arr [$a] ['EIGENTUEMER_ID'])) {
                    $eige_id = $my_arr [$a] ['EIGENTUEMER_ID'];
                    $my_arr [$a] ['ABGABEN'] [] ['ABGABE_IHR'] = $my_arr [$a] ['WEG-FLAECHE'] * -0.4;
                    if (empty ($my_arr [$a] ['WEG-FLAECHE'])) {
                        $my_arr [$a] ['ABGABEN'] [] ['ABGABE_IHR'] = $einheit_qm * -0.4;
                    }
                    $my_arr [$a] ['ABGABEN'] [] ['VG'] = '30.00'; // Verwaltergebähr

                    /* Kosten 1023 Reparatur Einheit */
                    $my_arr [$a] ['AUSGABEN'] = $this->get_kosten_arr('EINHEIT', $my_arr [$a] ['EINHEIT_ID'], $monat, $jahr, $gk->geldkonto_id, 1023);
                    $anz_rep = count($my_arr [$a] ['AUSGABEN']);
                    $summe_rep = 0;
                    for ($b = 0; $b < $anz_rep; $b++) {
                        $summe_rep += $my_arr [$a] ['AUSGABEN'] [$b] ['BETRAG'];
                    }
                    $my_arr [$a] ['AUSGABEN'] [$anz_rep] ['BETRAG'] = '<b>' . nummer_punkt2komma_t($summe_rep) . '</b>';
                    $my_arr [$a] ['AUSGABEN'] [$anz_rep] ['VERWENDUNGSZWECK'] = '<b>Summe</b>';

                    $mk = new mietkonto ();
                    $mk->kaltmiete_monatlich($my_arr [$a] ['MIETVERTRAG_ID'], $monat, $jahr);
                    $brutto_sollmiete_arr = explode('|', $mk->summe_forderung_monatlich($my_arr [$a] ['MIETVERTRAG_ID'], $monat, $jahr));
                    $brutto_sollmiete = $brutto_sollmiete_arr [0];
                    $my_arr [$a] ['NETTO_SOLL_MV'] = $mk->ausgangs_kaltmiete;

                    /* Garantierte Miete abfragen */
                    $net_ren_garantie_a = $det->finde_detail_inhalt('EINHEIT', $einheit_id, 'WEG-KaltmieteINS'); // kommt als Kommazahl
                    $net_ren_garantie = nummer_komma2punkt($net_ren_garantie_a);
                    $my_arr [$a] ['NETTO_SOLL_G_A'] = $net_ren_garantie_a;
                    if ($net_ren_garantie > $mk->ausgangs_kaltmiete) {
                        $my_arr [$a] ['NETTO_SOLL'] = $net_ren_garantie;
                    } else {
                        $my_arr [$a] ['NETTO_SOLL'] = $mk->ausgangs_kaltmiete;
                    }

                    $my_arr [$a] ['BRUTTO_SOLL'] = $brutto_sollmiete;
                    $my_arr [$a] ['AUSZAHLUNG_ET'] = $this->get_kosten_arr('Eigentuemer', $eige_id, $monat, $jahr, $gk->geldkonto_id, 5020);
                    $my_arr [$a] ['IST_EINNAHMEN'] = $this->get_kosten_arr('MIETVERTRAG', $my_arr [$a] ['MIETVERTRAG_ID'], $monat, $jahr, $gk->geldkonto_id, 80001);
                    $anz_me = count($my_arr [$a] ['IST_EINNAHMEN']);
                    $summe_einnahmen = 0;
                    for ($b = 0; $b < $anz_me; $b++) {
                        $summe_einnahmen += $my_arr [$a] ['IST_EINNAHMEN'] [$b] ['BETRAG'];
                    }
                    $my_arr [$a] ['IST_EINNAHMEN'] [$anz_me] ['BETRAG'] = '<b>' . nummer_punkt2komma_t($summe_einnahmen) . '</b>';
                    $my_arr [$a] ['IST_EINNAHMEN'] [$anz_me] ['VERWENDUNGSZWECK'] = '<b>Summe</b>';
                    $my_arr [$a] ['BRUTTO_IST'] = $summe_einnahmen;

                    $pdf_tab [$a] ['MIETER_SALDO'] = $my_arr [$a] ['MIETER_SALDO'];
                    $pdf_tab [$a] ['EIGENTUEMER_NAMEN'] = $my_arr [$a] ['EIGENTUEMER_NAMEN'];
                    $pdf_tab [$a] ['EINHEIT_KURZNAME'] = $my_arr [$a] ['WG_NR'] . "\n(" . $my_arr [$a] ['EINHEIT_KURZNAME'] . ')';

                    $pdf_tab [$a] ['EINHEIT_QM'] = $my_arr [$a] ['EINHEIT_QM'];
                    $pdf_tab [$a] ['EINHEIT_QM_A'] = nummer_punkt2komma($my_arr [$a] ['EINHEIT_QM']);
                    $pdf_tab [$a] ['WEG-FLAECHE'] = $my_arr [$a] ['WEG-FLAECHE'];
                    $pdf_tab [$a] ['WEG-FLAECHE_A'] = $my_arr [$a] ['WEG-FLAECHE_A'];
                    $pdf_tab [$a] ['AUSZAHLUNG_ET'] = $my_arr [$a] ['AUSZAHLUNG_ET'];
                    $pdf_tab [$a] ['MIETER'] = $my_arr [$a] ['MIETER'];
                    /* Garantiemiete */
                    $pdf_tab [$a] ['NETTO_SOLL_G_A'] = $my_arr [$a] ['NETTO_SOLL_G_A'];

                    $pdf_tab [$a] ['BRUTTO_SOLL'] = $my_arr [$a] ['BRUTTO_SOLL'];
                    $pdf_tab [$a] ['BRUTTO_SOLL_A'] = nummer_punkt2komma_t($my_arr [$a] ['BRUTTO_SOLL']);
                    $pdf_tab [$a] ['BRUTTO_IST'] = $my_arr [$a] ['BRUTTO_IST'];
                    $pdf_tab [$a] ['BRUTTO_IST_A'] = nummer_punkt2komma_t($my_arr [$a] ['BRUTTO_IST']);
                    $pdf_tab [$a] ['DIFF'] = $my_arr [$a] ['BRUTTO_IST'] - $pdf_tab [$a] ['BRUTTO_SOLL'];
                    $pdf_tab [$a] ['DIFF_A'] = nummer_punkt2komma_t($my_arr [$a] ['BRUTTO_IST'] - $pdf_tab [$a] ['BRUTTO_SOLL']);

                    $pdf_tab [$a] ['NETTO_SOLL'] = $my_arr [$a] ['NETTO_SOLL'];
                    $pdf_tab [$a] ['NETTO_SOLL_MV'] = $my_arr [$a] ['NETTO_SOLL_MV'];
                    $pdf_tab [$a] ['NETTO_SOLL_DIFF'] = $pdf_tab [$a] ['NETTO_SOLL'] - $pdf_tab [$a] ['NETTO_SOLL_MV'];
                    $pdf_tab [$a] ['NETTO_SOLL_A'] = nummer_punkt2komma_t($my_arr [$a] ['NETTO_SOLL']);
                    if (empty ($my_arr [$a] ['WEG-FLAECHE'])) {
                        $pdf_tab [$a] ['ABGABE_IHR'] = $pdf_tab [$a] ['EINHEIT_QM'] * -0.4;
                    } else {
                        $pdf_tab [$a] ['ABGABE_IHR'] = $my_arr [$a] ['WEG-FLAECHE'] * -0.4;
                    }
                    $pdf_tab [$a] ['ABGABE_IHR_A'] = nummer_punkt2komma($pdf_tab [$a] ['ABGABE_IHR']);
                    $pdf_tab [$a] ['ABGABE_HV'] = '-30.00';
                    $pdf_tab [$a] ['ABGABE_HV_A'] = '-30,00';
                    $pdf_tab [$a] ['ZWISCHENSUMME'] = $my_arr [$a] ['NETTO_SOLL'] + $pdf_tab [$a] ['ABGABE_IHR'] + $pdf_tab [$a] ['ABGABE_HV'];

                    $pdf_tab [$a] ['ZWISCHENSUMME_A'] = nummer_punkt2komma($my_arr [$a] ['NETTO_SOLL'] + $pdf_tab [$a] ['ABGABE_IHR'] + $pdf_tab [$a] ['ABGABE_HV']);
                    if (nummer_komma2punkt($pdf_tab [$a] ['ZWISCHENSUMME_A']) < 0.00) {
                        $pdf_tab [$a] ['ZWISCHENSUMME_A'] = '0,00';
                    }

                    $pdf_tab [$a] ['SUMME_REP'] = $summe_rep;
                    $pdf_tab [$a] ['SUMME_REP_A'] = nummer_punkt2komma($summe_rep);
                    $pdf_tab [$a] ['ENDSUMME'] = $pdf_tab [$a] ['ZWISCHENSUMME'];
                    $pdf_tab [$a] ['ENDSUMME_A'] = $pdf_tab [$a] ['ZWISCHENSUMME_A'];

                    $e_nam = $pdf_tab [$a] ['EIGENTUEMER_NAMEN'];
                    $ein_nam = $pdf_tab [$a] ['EINHEIT_KURZNAME'];

                    /* Übersichtstabelle */
                    $uebersicht [$a] ['EINHEIT_KURZNAME'] = $ein_nam;
                    $uebersicht [$a] ['EIGENTUEMER_NAMEN'] = $e_nam;
                    $uebersicht [$a] ['MIETER'] = $pdf_tab [$a] ['MIETER'];
                    $uebersicht [$a] ['MIETER_SALDO'] = $pdf_tab [$a] ['MIETER_SALDO'];
                    $uebersicht [$a] ['MIETER_SALDO_A'] = nummer_punkt2komma($pdf_tab [$a] ['MIETER_SALDO']);
                    $uebersicht [$a] ['NETTO_SOLL_G_A'] = $pdf_tab [$a] ['NETTO_SOLL_G_A'];
                    $uebersicht [$a] ['NETTO_SOLL_A'] = nummer_punkt2komma($pdf_tab [$a] ['NETTO_SOLL_MV']);
                    $uebersicht [$a] ['NETTO_SOLL_DIFF_A'] = nummer_punkt2komma($pdf_tab [$a] ['NETTO_SOLL_DIFF']);
                    $uebersicht [$a] ['ABGABE_HV_A'] = $pdf_tab [$a] ['ABGABE_HV_A'];
                    $uebersicht [$a] ['ABGABE_IHR'] = $pdf_tab [$a] ['ABGABE_IHR_A'];

                    $uebersicht [$a] ['SUMME_REP'] = $pdf_tab [$a] ['SUMME_REP'];
                    $uebersicht [$a] ['SUMME_REP_A'] = $pdf_tab [$a] ['SUMME_REP_A'];

                    $uebersicht [$a] ['WEG-FLAECHE_A'] = $pdf_tab [$a] ['WEG-FLAECHE_A'];
                    $uebersicht [$a] ['EINHEIT_QM_A'] = $pdf_tab [$a] ['EINHEIT_QM_A'];

                    $uebersicht [$a] ['ENDSUMME_A'] = $pdf_tab [$a] ['ENDSUMME_A'];
                    // $summe_alle_eigentuemer += $pdf_tab[$a]['ENDSUMME'];
                    if ($pdf_tab [$a] ['ENDSUMME'] > 0) {
                        $summe_alle_eigentuemer += $pdf_tab [$a] ['ENDSUMME'];
                    } else {
                        $summe_nachzahler += $pdf_tab [$a] ['ENDSUMME'];
                    }

                    if ($lang == 'en') {
                        $cols = array(
                            'EIGENTUEMER_NAMEN' => "owner",
                            'EINHEIT_KURZNAME' => "apart.No",
                            'MIETER' => 'tenant',
                            'WEG-FLAECHE_A' => 'size m²',
                            'NETTO_SOLL_A' => 'net rent',
                            'ABGABE_IHR_A' => 'for maint.',
                            'ABGABE_HV_A' => 'mng. fee',
                            'ENDSUMME_A' => 'transfer'
                        );
                        $pdf->ezTable($pdf_tab, $cols, "<b>$monat_name $jahr - Overview - $ein_nam</b>", array(
                            'showHeadings' => 1,
                            'shaded' => 1,
                            'titleFontSize' => 8,
                            'fontSize' => 7,
                            'xPos' => 50,
                            'xOrientation' => 'right',
                            'width' => 500,
                            'cols' => array(
                                'ENDSUMME_A' => array(
                                    'justification' => 'right',
                                    'width' => 50
                                ),
                                'EIGENTUEMER_NAMEN' => array(
                                    'justification' => 'left',
                                    'width' => 50
                                )
                            )
                        ));
                    } else {
                        $cols = array(
                            'EIGENTUEMER_NAMEN' => "Eigentümer",
                            'EINHEIT_KURZNAME' => "EINHEIT",
                            'MIETER' => 'Mieter',
                            'WEG-FLAECHE_A' => 'Eig. m²',
                            'BRUTTO_SOLL_A' => 'Warm SOLL',
                            'BRUTTO_IST_A' => 'Warm IST',
                            'DIFF_A' => 'DIFF',
                            'NETTO_SOLL_A' => 'rent p.m.\n (actual)',
                            'ABGABE_IHR_A' => 'IHR',
                            'ABGABE_HV_A' => 'HV',
                            'SUMME_REP_A' => 'Rep.',
                            'ENDSUMME_A' => 'AUSZAHLUNG'
                        );
                        $pdf->ezTable($pdf_tab, $cols, "<b>$monat_name $jahr - Gesamtübersicht - $ein_nam</b>", array(
                            'showHeadings' => 1,
                            'shaded' => 1,
                            'titleFontSize' => 8,
                            'fontSize' => 7,
                            'xPos' => 30,
                            'xOrientation' => 'right',
                            'width' => 550,
                            'cols' => array(
                                'ENDSUMME_A' => array(
                                    'justification' => 'right',
                                    'width' => 50
                                ),
                                'EIGENTUEMER_NAMEN' => array(
                                    'justification' => 'left',
                                    'width' => 50
                                )
                            )
                        ));
                    }

                    $pdf->ezSetDy(-20); // abstand
                    if (is_array($my_arr [$a] ['AUSGABEN'])) {
                        if ($lang == 'en') {
                            $cols = array(
                                'DATUM' => "Date",
                                'VERWENDUNGSZWECK' => "Description",
                                'BETRAG' => "Amount"
                            );
                            // $pdf->ezTable($my_arr[$a]['AUSGABEN'], $cols, "<b>$monat_name $jahr - Maintenance bills 1023 - $ein_nam</b>", array('showHeadings'=>1,'shaded'=>1, 'titleFontSize' => 8, 'fontSize' => 7, 'xPos'=>50,'xOrientation'=>'right', 'width'=>500, 'cols'=>array('BETRAG'=>array('justification'=>'right', 'width'=>65), 'DATUM'=>array('justification'=>'left', 'width'=>50))));
                        } else {
                            $cols = array(
                                'DATUM' => "Datum",
                                'VERWENDUNGSZWECK' => "Buchungstext",
                                'BETRAG' => "Betrag"
                            );
                            $pdf->ezTable($my_arr [$a] ['AUSGABEN'], $cols, "<b>$monat_name $jahr - Reparaturen 1023 - $ein_nam</b>", array(
                                'showHeadings' => 1,
                                'shaded' => 1,
                                'titleFontSize' => 8,
                                'fontSize' => 7,
                                'xPos' => 50,
                                'xOrientation' => 'right',
                                'width' => 500,
                                'cols' => array(
                                    'BETRAG' => array(
                                        'justification' => 'right',
                                        'width' => 65
                                    ),
                                    'DATUM' => array(
                                        'justification' => 'left',
                                        'width' => 50
                                    )
                                )
                            ));
                        }
                    } else {
                        $pdf->ezText("Keine Reparaturen", 12);
                    }
                    $pdf->ezSetDy(-20); // abstand
                    /* Tabelle Auszahlung an Eigentümer */
                    if (is_array($my_arr [$a] ['AUSZAHLUNG_ET'])) {

                        if ($lang == 'en') {
                            $cols = array(
                                'DATUM' => "Date",
                                'VERWENDUNGSZWECK' => "Description",
                                'BETRAG' => "Amount"
                            );
                            // $pdf->ezTable($my_arr[$a]['AUSZAHLUNG_ET'], $cols, "<b>$monat_name $jahr - transfer 5020 - $ein_nam</b>", array('showHeadings'=>1,'shaded'=>1, 'titleFontSize' => 8, 'fontSize' => 7, 'xPos'=>50,'xOrientation'=>'right', 'width'=>500, 'cols'=>array('BETRAG'=>array('justification'=>'right', 'width'=>65),'DATUM'=>array('justification'=>'left', 'width'=>50))));
                        } else {
                            $cols = array(
                                'DATUM' => "Datum",
                                'VERWENDUNGSZWECK' => "Buchungstext",
                                'BETRAG' => "Betrag"
                            );
                            $pdf->ezTable($my_arr [$a] ['AUSZAHLUNG_ET'], $cols, "<b>$monat_name $jahr - Überweisung 80001 - $ein_nam</b>", array(
                                'showHeadings' => 1,
                                'shaded' => 1,
                                'titleFontSize' => 8,
                                'fontSize' => 7,
                                'xPos' => 50,
                                'xOrientation' => 'right',
                                'width' => 500,
                                'cols' => array(
                                    'BETRAG' => array(
                                        'justification' => 'right',
                                        'width' => 65
                                    ),
                                    'DATUM' => array(
                                        'justification' => 'left',
                                        'width' => 50
                                    )
                                )
                            ));
                        }
                    }
                    /*
					 * if(is_array($my_arr[$a]['IST_EINNAHMEN'])){
					 * if($lang=='en'){
					 * $cols = array('DATUM'=>"Date", 'VERWENDUNGSZWECK'=>"Description", 'BETRAG'=>"Amount");
					 * $pdf->ezTable($my_arr[$a]['IST_EINNAHMEN'], $cols, "<b>$monat_name $jahr - income overview 80001 - $ein_nam</b>", array('showHeadings'=>1,'shaded'=>1, 'titleFontSize' => 8, 'fontSize' => 7, 'xPos'=>50,'xOrientation'=>'right', 'width'=>500, 'cols'=>array('BETRAG'=>array('justification'=>'right', 'width'=>65),'DATUM'=>array('justification'=>'left', 'width'=>50))));
					 * }else{
					 * $cols = array('DATUM'=>"Datum", 'VERWENDUNGSZWECK'=>"Buchungstext", 'BETRAG'=>"Betrag");
					 * $pdf->ezTable($my_arr[$a]['IST_EINNAHMEN'], $cols, "<b>$monat_name $jahr - Einnahmen 80001 - $ein_nam</b>", array('showHeadings'=>1,'shaded'=>1, 'titleFontSize' => 8, 'fontSize' => 7, 'xPos'=>50,'xOrientation'=>'right', 'width'=>500, 'cols'=>array('BETRAG'=>array('justification'=>'right', 'width'=>65),'DATUM'=>array('justification'=>'left', 'width'=>50))));
					 * }
					 *
					 * }else{
					 * $pdf->ezText("Keine Mieteinnahmen", 12);
					 * }
					 */

                    // $cols = array('DATUM'=>"Datum",'VERWENDUNGSZWECK'=>"Buchungstext", 'BETRAG'=>"Betrag");
                    // $pdf->ezTable($pdf_tab[$a]['EIG_AUSZAHLUNG'], $cols, "<b>$monat_name $jahr - Auszahlung an Eigentümer 80001 - $ein_nam</b>", array('showHeadings'=>1,'shaded'=>1, 'titleFontSize' => 8, 'fontSize' => 7, 'xPos'=>50,'xOrientation'=>'right', 'width'=>500, 'cols'=>array('BETRAG'=>array('justification'=>'right', 'width'=>65),'DATUM'=>array('justification'=>'left', 'width'=>50))));

                    $pdf->ezNewPage();
                    unset ($pdf_tab);
                }
            }
            $uebersicht [$anz] ['EIGENTUEMER_NAMEN'] = 'Auszahlungssumme';
            $uebersicht [$anz] ['ENDSUMME_A'] = nummer_punkt2komma_t($summe_alle_eigentuemer);
            $uebersicht [$anz + 1] ['EIGENTUEMER_NAMEN'] = 'Zu erhalten';
            $uebersicht [$anz + 1] ['ENDSUMME_A'] = nummer_punkt2komma_t($summe_nachzahler);

            if ($lang == 'en') {
                $cols = array(
                    'EINHEIT_KURZNAME' => "Apt",
                    'EINHEIT_QM_A' => 'MVm²',
                    'WEG-FLAECHE_A' => 'm²',
                    'EIGENTUEMER_NAMEN' => "Own",
                    'MIETER' => "Tenant",
                    'MIETER_SALDO_A' => 'current',
                    'NETTO_SOLL_G_A' => "Garanty",
                    'NETTO_SOLL_A' => "net rent",
                    'NETTO_SOLL_DIFF_A' => "diff",
                    'ABGABE_HV_A' => "fee",
                    'ABGABE_IHR' => "for maint.",
                    'ENDSUMME_A' => "Amount",
                    'SUMME_REP_A' => 'Rep.'
                );
            }

            $pdf->ezTable($uebersicht, $cols, null, array(
                'showHeadings' => 1,
                'shaded' => 1,
                'titleFontSize' => 8,
                'fontSize' => 7,
                'xPos' => 50,
                'xOrientation' => 'right',
                'width' => 500
            ));

            ob_end_clean(); // ausgabepuffer leeren
            $pdf->ezStream();
        } else {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\InfoMessage("Keine Einheiten im Objekt $objekt_id.")
            );
        }
    }

    function form_sepa_ueberweisung_anzeigen($arr)
    {
        $gk = new geldkonto_info ();
        $gk->geld_konto_ermitteln('OBJEKT', session()->get('objekt_id'));
        if (!$gk->geldkonto_id) {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\ErrorMessage('Geldkonto vom Objekt nicht bekannt!')
            );
        }

        $monat = date("m");
        $jahr = date("Y");

        $f = new formular ();
        // echo '<pre>';
        // print_r($arr);
        if (is_array($arr)) {
            $anz = count($arr);
            echo "<table class=\"sortable\">";
            echo "<tr><th>EINHEIT</th><th>EIGENTÜMER</th><th>Mieter</th><th>SALDO AKT</th><th>KALTM</th><th>INS DIFF</th><th>HV</th><th>IHR</th><th>REP</th><th>TRANSFER</th><th>OPT</th><th>OPT2</th></tr>";
            for ($a = 0; $a < $anz; $a++) {
                $e_kn = $arr [$a] ['EINHEIT_KURZNAME'];
                $et = $arr [$a] ['EIGENTUEMER_NAMEN'];
                $eig_id = $arr [$a] ['EIG_ID'];

                $weg = new weg ();
                $weg->get_eigentumer_id_infos3($eig_id);

                $mieter = $arr [$a] ['MIETER'];
                $ms = $arr [$a] ['MIETER_SALDO'];
                $nkm = $arr [$a] ['NETTO_SOLL_A'];
                $diff = $arr [$a] ['NETTO_SOLL_DIFF_A'];
                $hv = $arr [$a] ['ABGABE_HV_A'];
                $ihr = $arr [$a] ['ABGABE_IHR'];
                $rep = $arr [$a] ['SUMME_REP'];
                $transfer = nummer_komma2punkt(nummer_punkt2komma($arr [$a] ['TRANSFER']));

                $sep = new sepa ();
                $betrag_in_sepa = $sep->get_summe_sepa_sammler($gk->geldkonto_id, 'ET-AUSZAHLUNG', 'Eigentuemer', $eig_id);
                if ($betrag_in_sepa < $transfer) {
                    $link_sepa_ueberweisen = "<a href='" . route('web::listen::legacy', ['option' => 'sepa_ueberweisen', 'eig_et' => $eig_id, 'betrag' => $transfer]) . "'>SEPA-Ü</a>";
                    /* Form */
                    echo "<form name=\"sepa_lg\" method=\"post\" action=\"\">";

                    if ($transfer > 0) {
                        echo "<tr class=\"zeile2\"><td>$e_kn</td><td>$et</td><td>$mieter</td><td>$ms</td><td>$nkm</td><td><b>$diff</b></td><td>$hv</td><td>$ihr</td><td>$rep</td><td style=\"color:white;\">";
                        // <b>$transfer</b>
                        $js_action = "onfocus=\"this.value='';\"";
                        $transfer_a = nummer_punkt2komma($transfer);
                        $f->text_feld('Betrag', 'betrag', $transfer_a, 10, 'betrag', $js_action);
                        echo "</td>";
                    } else {
                        echo "<tr class=\"zeile1\"><td>$e_kn</td><td>$et</td><td>$mieter</td><td>$ms</td><td>$nkm</td><td><b>$diff</b></td><td>$hv</td><td>$ihr</td><td>$rep</td><td style=\"color:white;\">";
                        // <b>$transfer</b>
                        $js_action = "onfocus=\"this.value='';\"";
                        $transfer_a = nummer_punkt2komma($transfer);
                        $f->text_feld('Betrag', 'betrag', $transfer_a, 10, 'betrag', $js_action);
                        echo "</td>";
                    }
                    echo "<td>$link_sepa_ueberweisen</td>";
                    /* Wenn Geldkontenvorhanden */
                    $sep = new sepa ();
                    echo "<td>";
                    if ($sep->dropdown_sepa_geldkonten('Überweisen an', 'empf_sepa_gk_id', 'empf_sepa_gk_id', 'Eigentuemer', $eig_id) == true) {
                        // $f->text_feld('VERWENDUNG', 'vzweck', "Eigentuemerentnahme $weg->einheit_kurzname Auszahlung $monat.$jahr", 100, 'vzweck', '');
                        $f->hidden_feld('option', 'sepa_sammler_hinzu');
                        $f->hidden_feld('vzweck', "$weg->einheit_kurzname $monat.$jahr / Transfer to owner / Auszahlung");
                        $f->hidden_feld('kat', 'ET-AUSZAHLUNG');
                        $f->hidden_feld('gk_id', $gk->geldkonto_id);
                        $f->hidden_feld('kos_typ', 'Eigentuemer');
                        $f->hidden_feld('kos_id', $eig_id);
                        $f->hidden_feld('konto', 5020);
                        if ($eig_id == '133' or $eig_id == '139' or $eig_id == '200') {
                            $f->send_button('btn_Sepa', 'Zahnärzte Aufpassen!!!!');
                        } else {
                            $f->send_button('btn_Sepa', 'inSEPA');
                        }
                    }
                    echo "</td>";
                    echo "</tr>";
                    $f->ende_formular();
                } else {
                    // echo "$betrag_in_sepa vorhanden<br>";
                }
            }

            echo "</table>";
        }
    }

    /* Mit Warmmiete */

    function inspiration_pdf_kurz_6($ausgezogene = 0, $objekt_id, $monat, $jahr, $lang = 'de')
    {
        /* Eingrenzung Kostenabragen */
        if (!request()->has('von') or !request()->has('bis')) {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\InfoMessage('Abfragedatum VON BIS in die URL hinzufügen')
            );
        }
        $von = date_german2mysql(request()->input('von'));
        $bis = date_german2mysql(request()->input('bis'));

        $monat_name = monat2name($monat);
        $pdf = new Cezpdf ('a4', 'portrait');
        $bpdf = new b_pdf ();
        $bpdf->b_header($pdf, 'Partner', session()->get('partner_id'), 'portrait', 'Helvetica.afm', 6);
        $pdf->ezStopPageNumbers(); // seitennummerirung beenden
        $gk = new geldkonto_info ();
        $gk->geld_konto_ermitteln('OBJEKT', $objekt_id);
        echo '<pre>';
        // print_r($gk);
        if (!$gk->geldkonto_id) {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\ErrorMessage('Geldkonto zum Objekt hinzufügen.')
            );
        }
        $db_abfrage = "SELECT OBJEKT_KURZNAME, HAUS_STRASSE, HAUS_NUMMER, `EINHEIT_KURZNAME` , `EINHEIT_ID`,  ltrim(rtrim(EINHEIT_LAGE)) AS EINHEIT_LAGE, `EINHEIT_QM` FROM EINHEIT , HAUS, OBJEKT
WHERE `EINHEIT_AKTUELL` = '1' && EINHEIT.HAUS_ID = HAUS.HAUS_ID && HAUS.OBJEKT_ID=OBJEKT.OBJEKT_ID && HAUS_AKTUELL='1' && OBJEKT_AKTUELL='1' && OBJEKT.OBJEKT_ID='$objekt_id' 
GROUP BY EINHEIT_ID ORDER BY EINHEIT_KURZNAME";

        $result = DB::select($db_abfrage);
        if (!empty($result)) {
            $z = 0;
            foreach ($result as $row) {
                $my_arr [] = $row;
                $einheit_id = $row ['EINHEIT_ID'];
                $einheit_qm = $row ['EINHEIT_QM'];
                $e = new einheit ();
                $e->get_einheit_info($einheit_id);
                $my_arr [$z] ['ANSCHRIFT'] = "$e->haus_strasse $e->haus_nummer, $e->haus_plz $e->haus_stadt";
                $det = new detail ();
                $my_arr [$z] ['WEG-FLAECHE_A'] = $det->finde_detail_inhalt('EINHEIT', $einheit_id, 'WEG-Fläche'); // kommt als Kommazahl
                if (empty ($my_arr [$z] ['WEG-FLAECHE_A'])) {
                    $my_arr [$z] ['WEG-FLAECHE_A'] = nummer_punkt2komma($einheit_qm);
                }
                $my_arr [$z] ['WEG-FLAECHE'] = nummer_komma2punkt($my_arr [$z] ['WEG-FLAECHE_A']);

                $my_arr [$z] ['WG_NR'] = $det->finde_detail_inhalt('EINHEIT', $einheit_id, 'Alte Nr'); // kommt als Kommazahl

                $weg = new weg ();
                $weg->get_last_eigentuemer($einheit_id);
                if (isset ($weg->eigentuemer_name)) {
                    $weg->get_eigentuemer_namen($weg->eigentuemer_id);
                    $my_arr [$z] ['EIGENTUEMER_NAMEN'] = $weg->eigentuemer_name_str_u;
                    $my_arr [$z] ['EIGENTUEMER_ID'] = $weg->eigentuemer_id;

                    /* Personenkontaktdaten Eigentümer */
                    $et_p_id = $weg->get_person_id_eigentuemer_arr($weg->eigentuemer_id);
                    if (!empty($et_p_id)) {
                        $anz_pp = count($et_p_id);
                        for ($pe = 0; $pe < $anz_pp; $pe++) {
                            $et_p_id_1 = $et_p_id [$pe] ['PERSON_ID'];
                            // echo $et_p_id_1;
                            $detail = new detail ();
                            if (($detail->finde_detail_inhalt('PERSON', $et_p_id_1, 'Email'))) {
                                $email_arr = $detail->finde_alle_details_grup('PERSON', $et_p_id_1, 'Email');
                                for ($ema = 0; $ema < count($email_arr); $ema++) {
                                    $em_adr = $email_arr [$ema] ['DETAIL_INHALT'];
                                    $my_arr [$z] ['EMAILS'] [] = $em_adr;
                                }
                            }
                        }
                    } else {
                        throw new \App\Exceptions\MessageException(
                            new \App\Messages\ErrorMessage("Personen/Eigentümer unbekannt! ET_ID: $weg->eigentuemer_id")
                        );
                    }
                } else {
                    $my_arr [$z] ['EIGENTUEMER'] = 'Unbekannt';
                }
                $mv_id = $e->get_mietvertrag_id($einheit_id);
                if ($mv_id) {
                    $mvs = new mietvertraege ();
                    $mvs->get_mietvertrag_infos_aktuell($mv_id);
                    $kontaktdaten = $e->kontaktdaten_mieter($mv_id);
                    $my_arr [$z] ['MIETER'] = $mvs->personen_name_string_u;
                    $my_arr [$z] ['MIETVERTRAG_ID'] = $mv_id;
                    $my_arr [$z] ['KONTAKT'] = $kontaktdaten;
                    $my_arr [$z] ['EINHEIT_ID'] = $einheit_id;
                    $mz = new miete ();
                    $mz->mietkonto_berechnung($mv_id);
                    $my_arr [$z] ['MIETER_SALDO'] = $mz->erg;
                    $my_arr [$z] ['MIETER_SALDO_VM'] = nummer_punkt2komma($mz->saldo_vormonat_end);
                } else {
                    $my_arr [$z] ['MIETER'] = 'Leerstand';
                }
                $z++;
            }

            unset ($e);
            unset ($mvs);
            unset ($weg);

            $anz = count($my_arr);
            /* Berechnung Abgaben */
            for ($a = 0; $a < $anz; $a++) {
                $einheit_id = $my_arr [$a] ['EINHEIT_ID'];
                if (isset ($my_arr [$a] ['EIGENTUEMER_ID'])) {
                    $eige_id = $my_arr [$a] ['EIGENTUEMER_ID'];
                    $weg1 = new weg ();
                    $ihr_hg = $weg1->get_summe_kostenkat_monat($monat, $jahr, 'Einheit', $einheit_id, '6030');
                    // $my_arr[$a]['ABGABEN'][]['ABGABE_IHR'] = $my_arr[$a]['WEG-FLAECHE'] * -0.4;
                    if ($ihr_hg) {
                        $my_arr [$a] ['ABGABEN'] [] ['ABGABE_IHR'] = -$ihr_hg;
                    } else {
                        // if(empty($my_arr[$a]['WEG-FLAECHE'])){
                        $my_arr [$a] ['ABGABEN'] [] ['ABGABE_IHR'] = $einheit_qm * -0.4;
                    }
                    $weg1 = new weg ();
                    $vg = $weg1->get_summe_kostenkat_monat($monat, $jahr, 'Einheit', $einheit_id, '6060');
                    if ($vg) {
                        $my_arr [$a] ['ABGABEN'] [] ['VG'] = $vg; // Verwaltergebühr
                    } else {
                        $my_arr [$a] ['ABGABEN'] [] ['VG'] = '30.00'; // Verwaltergebühr
                    }
                    $my_arr [$a] ['AUSGABEN'] = $this->get_kosten_von_bis('Einheit', $einheit_id, $von, $bis, $gk->geldkonto_id, 1023);
                    $anz_rep = count($my_arr [$a] ['AUSGABEN']);
                    $summe_rep = 0;
                    for ($b = 0; $b < $anz_rep - 1; $b++) {
                        $summe_rep += $my_arr [$a] ['AUSGABEN'] [$b] ['BETRAG'];
                    }

                    $mk = new mietkonto ();
                    $mk->kaltmiete_monatlich($my_arr [$a] ['MIETVERTRAG_ID'], $monat, $jahr);
                    $brutto_sollmiete_arr = explode('|', $mk->summe_forderung_monatlich($my_arr [$a] ['MIETVERTRAG_ID'], $monat, $jahr));
                    $brutto_sollmiete = $brutto_sollmiete_arr [0];
                    $my_arr [$a] ['NETTO_SOLL_MV'] = $mk->ausgangs_kaltmiete;

                    /* Garantierte Miete abfragen */
                    $net_ren_garantie_a = $det->finde_detail_inhalt('EINHEIT', $einheit_id, 'WEG-KaltmieteINS'); // kommt als Kommazahl
                    $net_ren_garantie = nummer_komma2punkt($net_ren_garantie_a);
                    $my_arr [$a] ['NETTO_SOLL_G_A'] = $net_ren_garantie_a;

                    if ($net_ren_garantie > $mk->ausgangs_kaltmiete) {
                        $my_arr [$a] ['NETTO_SOLL'] = $net_ren_garantie;
                    } else {
                        $my_arr [$a] ['NETTO_SOLL'] = $mk->ausgangs_kaltmiete;
                    }

                    $my_arr [$a] ['BRUTTO_SOLL'] = $brutto_sollmiete;
                    $my_arr [$a] ['AUSZAHLUNG_ET'] = $this->get_kosten_arr('Eigentuemer', $eige_id, $monat, $jahr, $gk->geldkonto_id, 5020);

                    /* Andere Kosten */
                    /* INS MAKLERGEBÜHR */
                    $my_arr [$a] ['5500'] = $this->get_kosten_von_bis('Einheit', $einheit_id, $von, $bis, $gk->geldkonto_id, 5500);
                    /* Andere Kosten */
                    $my_arr [$a] ['4180'] = $this->get_kosten_von_bis('Einheit', $einheit_id, $von, $bis, $gk->geldkonto_id, 4180);
                    $my_arr [$a] ['4280'] = $this->get_kosten_von_bis('Einheit', $einheit_id, $von, $bis, $gk->geldkonto_id, 4280);
                    // $my_arr[$a]['4280'] = $this->get_kosten_arr('Einheit', $einheit_id, $monat, $jahr, $gk->geldkonto_id,4280);
                    $my_arr [$a] ['4281'] = $this->get_kosten_von_bis('Einheit', $einheit_id, $von, $bis, $gk->geldkonto_id, 4281);
                    $my_arr [$a] ['4282'] = $this->get_kosten_von_bis('Einheit', $einheit_id, $von, $bis, $gk->geldkonto_id, 4282);
                    $my_arr [$a] ['5081'] = $this->get_kosten_von_bis('Eigentuemer', $eige_id, $von, $bis, $gk->geldkonto_id, 5081);
                    $my_arr [$a] ['5010'] = $this->get_kosten_von_bis('Eigentuemer', $eige_id, $von, $bis, $gk->geldkonto_id, 5010);

                    if (request()->has('von_a')) {
                        $von_a = date_german2mysql(request()->input('von_a'));
                    } else {
                        $von_a = "$jahr-$monat-01";
                    }
                    if (!request()->has('bis_a')) {
                        $lt = letzter_tag_im_monat($monat, $jahr);
                        $bis_a = "$jahr-$monat-$lt";
                    } else {
                        $bis_a = date_german2mysql(request()->input('bis_a'));
                    }
                    $my_arr [$a] ['5020'] = $this->get_kosten_von_bis('Eigentuemer', $eige_id, $von_a, $bis_a, $gk->geldkonto_id, 5020);

                    $my_arr [$a] ['IST_EINNAHMEN'] = $this->get_kosten_arr('MIETVERTRAG', $my_arr [$a] ['MIETVERTRAG_ID'], $monat, $jahr, $gk->geldkonto_id, 80001);
                    $anz_me = count($my_arr [$a] ['IST_EINNAHMEN']);
                    $summe_einnahmen = 0;
                    for ($b = 0; $b < $anz_me; $b++) {
                        $summe_einnahmen += $my_arr [$a] ['IST_EINNAHMEN'] [$b] ['BETRAG'];
                    }
                    $my_arr [$a] ['IST_EINNAHMEN'] [$anz_me] ['BETRAG'] = '<b>' . nummer_punkt2komma_t($summe_einnahmen) . '</b>';
                    $my_arr [$a] ['IST_EINNAHMEN'] [$anz_me] ['VERWENDUNGSZWECK'] = '<b>Summe</b>';
                    $my_arr [$a] ['BRUTTO_IST'] = $summe_einnahmen;

                    $pdf_tab [$a] ['MIETER_SALDO'] = $my_arr [$a] ['MIETER_SALDO'];
                    $pdf_tab [$a] ['MIETER_SALDO_VM'] = $my_arr [$a] ['MIETER_SALDO_VM'];
                    $pdf_tab [$a] ['EIGENTUEMER_NAMEN'] = $my_arr [$a] ['EIGENTUEMER_NAMEN'];
                    $pdf_tab [$a] ['EINHEIT_KURZNAME'] = $my_arr [$a] ['EINHEIT_KURZNAME'];
                    $pdf_tab [$a] ['EINHEIT_ID'] = $my_arr [$a] ['EINHEIT_ID'];
                    $pdf_tab [$a] ['ANSCHRIFT'] = $my_arr [$a] ['ANSCHRIFT'];
                    $pdf_tab [$a] ['EIGENTUEMER_ID'] = $my_arr [$a] ['EIGENTUEMER_ID'];
                    $pdf_tab [$a] ['EMAILS'] = array_unique($my_arr [$a] ['EMAILS']);
                    unset ($emails);

                    $pdf_tab [$a] ['EINHEIT_QM'] = $my_arr [$a] ['EINHEIT_QM'];
                    $pdf_tab [$a] ['EINHEIT_QM_A'] = nummer_punkt2komma($my_arr [$a] ['EINHEIT_QM']);
                    $pdf_tab [$a] ['WEG-FLAECHE'] = $my_arr [$a] ['WEG-FLAECHE'];
                    $pdf_tab [$a] ['WEG-FLAECHE_A'] = $my_arr [$a] ['WEG-FLAECHE_A'];
                    $pdf_tab [$a] ['AUSZAHLUNG_ET'] = $my_arr [$a] ['AUSZAHLUNG_ET'];
                    $pdf_tab [$a] ['MIETER'] = $my_arr [$a] ['MIETER'];
                    /* Garantiemiete */
                    $pdf_tab [$a] ['NETTO_SOLL_G_A'] = $my_arr [$a] ['NETTO_SOLL_G_A'];

                    $pdf_tab [$a] ['BRUTTO_SOLL'] = $my_arr [$a] ['BRUTTO_SOLL'];
                    $pdf_tab [$a] ['BRUTTO_SOLL_A'] = nummer_punkt2komma_t($my_arr [$a] ['BRUTTO_SOLL']);
                    $pdf_tab [$a] ['BRUTTO_IST'] = $my_arr [$a] ['BRUTTO_IST'];
                    $pdf_tab [$a] ['BRUTTO_IST_A'] = nummer_punkt2komma_t($my_arr [$a] ['BRUTTO_IST']);
                    $pdf_tab [$a] ['DIFF'] = $my_arr [$a] ['BRUTTO_IST'] - $pdf_tab [$a] ['BRUTTO_SOLL'];
                    $pdf_tab [$a] ['DIFF_A'] = nummer_punkt2komma_t($my_arr [$a] ['BRUTTO_IST'] - $pdf_tab [$a] ['BRUTTO_SOLL']);

                    $pdf_tab [$a] ['NETTO_SOLL'] = $my_arr [$a] ['NETTO_SOLL'];
                    $pdf_tab [$a] ['NETTO_SOLL_MV'] = $my_arr [$a] ['NETTO_SOLL_MV'];
                    $pdf_tab [$a] ['NETTO_SOLL_DIFF'] = $pdf_tab [$a] ['NETTO_SOLL'] - $pdf_tab [$a] ['NETTO_SOLL_MV'];
                    $pdf_tab [$a] ['NETTO_SOLL_A'] = nummer_punkt2komma_t($my_arr [$a] ['NETTO_SOLL']);
                    $pdf_tab [$a] ['ABGABE_IHR'] = $my_arr [$a] ['ABGABE_IHR'];

                    $weg1 = new weg ();
                    $vg = $weg1->get_summe_kostenkat_monat($monat, $jahr, 'Einheit', $einheit_id, '6060');
                    if (!empty ($vg)) {
                        $pdf_tab [$a] ['ABGABE_HV'] = -$vg; // Verwaltergebühr
                        $pdf_tab [$a] ['ABGABE_HV_A'] = nummer_punkt2komma(-$vg); // Verwaltergebühr
                    } else {
                        $pdf_tab [$a] ['ABGABE_HV'] = '-30.00';
                        $pdf_tab [$a] ['ABGABE_HV_A'] = '-30,00';
                    }

                    $weg1 = new weg ();
                    $ihr_hg = $weg1->get_summe_kostenkat_monat($monat, $jahr, 'Einheit', $einheit_id, '6030');

                    if (!empty ($ihr_hg)) {
                        $pdf_tab [$a] ['ABGABE_IHR'] = -$ihr_hg;
                    } else {
                        $pdf_tab [$a] ['ABGABE_IHR'] = $pdf_tab [$a] ['WEG-FLAECHE'] * -0.4;
                    }
                    $pdf_tab [$a] ['ABGABE_IHR_A'] = nummer_punkt2komma($pdf_tab [$a] ['ABGABE_IHR']);

                    $pdf_tab [$a] ['ZWISCHENSUMME'] = $my_arr [$a] ['NETTO_SOLL'] + $pdf_tab [$a] ['ABGABE_IHR'] + $pdf_tab [$a] ['ABGABE_HV'];

                    $pdf_tab [$a] ['ZWISCHENSUMME_A'] = nummer_punkt2komma($my_arr [$a] ['NETTO_SOLL'] + $pdf_tab [$a] ['ABGABE_IHR'] + $pdf_tab [$a] ['ABGABE_HV']);

                    /* Andere Kosten Summieren */
                    // ############################
                    $anz_kk = count($my_arr [$a] ['5500']);
                    if ($anz_kk > 1) {
                        $summe_rep += $my_arr [$a] ['5500'] [$anz_kk - 1] ['BETRAG'];
                    }

                    $anz_kk = count($my_arr [$a] ['4180']);
                    if ($anz_kk > 1) {
                        $summe_rep += $my_arr [$a] ['4180'] [$anz_kk - 1] ['BETRAG'];
                    }

                    $anz_kk = count($my_arr [$a] ['4280']);
                    if ($anz_kk > 1) {
                        $summe_rep += $my_arr [$a] ['4280'] [$anz_kk - 1] ['BETRAG'];
                    }

                    $anz_kk = count($my_arr [$a] ['4281']);
                    if ($anz_kk > 1) {
                        $summe_rep += $my_arr [$a] ['4281'] [$anz_kk - 1] ['BETRAG'];
                    }
                    $anz_kk = count($my_arr [$a] ['4282']);
                    if ($anz_kk > 1) {
                        $summe_rep += $my_arr [$a] ['4282'] [$anz_kk - 1] ['BETRAG'];
                    }

                    $anz_kk = count($my_arr [$a] ['5081']);
                    if ($anz_kk > 1) {
                        $summe_rep += $my_arr [$a] ['5081'] [$anz_kk - 1] ['BETRAG'];
                    }

                    $pdf_tab [$a] ['SUMME_REP'] = $summe_rep;
                    $pdf_tab [$a] ['SUMME_REP_A'] = nummer_punkt2komma($summe_rep);
                    $pdf_tab [$a] ['ENDSUMME'] = $pdf_tab [$a] ['ZWISCHENSUMME'];
                    $pdf_tab [$a] ['ENDSUMME_A'] = $pdf_tab [$a] ['ZWISCHENSUMME_A'];

                    $e_nam = $pdf_tab [$a] ['EIGENTUEMER_NAMEN'];
                    $ein_nam = $pdf_tab [$a] ['EINHEIT_KURZNAME'];

                    /* Übersichtstabelle */
                    $uebersicht [$a] ['EINHEIT_KURZNAME'] = $ein_nam;
                    $uebersicht [$a] ['EIGENTUEMER_NAMEN'] = $e_nam;
                    $uebersicht [$a] ['MIETER'] = $pdf_tab [$a] ['MIETER'];
                    $uebersicht [$a] ['MIETER_SALDO'] = $pdf_tab [$a] ['MIETER_SALDO'];
                    $uebersicht [$a] ['MIETER_SALDO_VM'] = $pdf_tab [$a] ['MIETER_SALDO_VM'];
                    $uebersicht [$a] ['MIETER_SALDO_A'] = nummer_punkt2komma($pdf_tab [$a] ['MIETER_SALDO']);
                    $uebersicht [$a] ['NETTO_SOLL_G_A'] = $pdf_tab [$a] ['NETTO_SOLL_G_A'];
                    $uebersicht [$a] ['NETTO_SOLL_A'] = nummer_punkt2komma($pdf_tab [$a] ['NETTO_SOLL_MV']);
                    $uebersicht [$a] ['NETTO_SOLL_DIFF_A'] = nummer_punkt2komma($pdf_tab [$a] ['NETTO_SOLL_DIFF']);
                    $uebersicht [$a] ['ABGABE_HV_A'] = $pdf_tab [$a] ['ABGABE_HV_A'];
                    $uebersicht [$a] ['ABGABE_IHR'] = $pdf_tab [$a] ['ABGABE_IHR_A'];

                    $uebersicht [$a] ['SUMME_REP'] = $pdf_tab [$a] ['SUMME_REP'];
                    $uebersicht [$a] ['SUMME_REP_A'] = $pdf_tab [$a] ['SUMME_REP_A'];

                    $uebersicht [$a] ['WEG-FLAECHE_A'] = $pdf_tab [$a] ['WEG-FLAECHE_A'];
                    $uebersicht [$a] ['EINHEIT_QM_A'] = $pdf_tab [$a] ['EINHEIT_QM_A'];

                    $uebersicht [$a] ['ENDSUMME_A'] = $pdf_tab [$a] ['ENDSUMME_A'];
                    // $summe_alle_eigentuemer += $pdf_tab[$a]['ENDSUMME'];
                    $uebersicht [$a] ['TRANSFER'] = $pdf_tab [$a] ['ENDSUMME'] + $pdf_tab [$a] ['SUMME_REP'];
                    $uebersicht [$a] ['TRANSFER_A'] = nummer_punkt2komma_t($pdf_tab [$a] ['ENDSUMME'] + $pdf_tab [$a] ['SUMME_REP']);
                    /*
					 * $trans_tab['ENDSUMME_A'] = $pdf_tab[$a]['ENDSUMME_A'];
					 * $trans_tab['SUMME_REP_A'] = $uebersicht[$a]['SUMME_REP_A'];
					 * $trans_tab['TRANSFER'] = $uebersicht[$a]['TRANSFER'];
					 * $trans_tab['TRANSFER_A'] = $uebersicht[$a]['TRANSFER_A'];
					 */

                    $summe_transfer += $uebersicht [$a] ['TRANSFER'];

                    if ($pdf_tab [$a] ['ENDSUMME'] > 0) {
                        $summe_alle_eigentuemer += $pdf_tab [$a] ['ENDSUMME'];
                    } else {
                        $summe_nachzahler += $pdf_tab [$a] ['ENDSUMME'];
                    }

                    if (request()->has('w_monat')) {
                        $w_monat = request()->input('w_monat');
                    } else {
                        $w_monat = $monat;
                    }

                    if (request()->has('w_jahr')) {
                        $w_jahr = request()->input('w_jahr');
                    } else {
                        $w_jahr = $jahr;
                    }

                    if ($lang == 'en') {
                        if (is_array($pdf_tab [$a] ['EMAILS'])) {

                            $anzemail = count($pdf_tab [$a] ['EMAILS']);
                            $pdf->setColor(255, 255, 255, 255); // Weiss
                            for ($em = 0; $em < $anzemail; $em++) {
                                $email = $pdf_tab [$a] ['EMAILS'] [$em];
                                $pdf->ezText("$email ", 10);
                                $pdf->ezSetDy(9); // abstand
                            }
                            $pdf->setColor(0, 0, 0, 1); // schwarz
                        }
                        $anschrift = $pdf_tab [$a] ['ANSCHRIFT'];
                        $cols = array(
                            'EIGENTUEMER_NAMEN' => "<b>owner</b>",
                            'EINHEIT_KURZNAME' => "<b>apart.No</b>",
                            'MIETER' => "<b>tenant</b>",
                            'WEG-FLAECHE_A' => "<b>size [m²]</b>",
                            'NETTO_SOLL_A' => "<b>net rent [€]</b>",
                            'ABGABE_IHR_A' => "<b>for maint. [€]</b>",
                            'ABGABE_HV_A' => "<b>mng. fee [€]</b>",
                            'ENDSUMME_A' => "<b>Amount [€]</b>"
                        );
                        $pdf->ezTable($pdf_tab, $cols, "<b>Monthly report $w_monat/$w_jahr    $ein_nam, $anschrift</b>", array(
                            'showHeadings' => 1,
                            'shaded' => 1,
                            'titleFontSize' => 8,
                            'fontSize' => 7,
                            'xPos' => 50,
                            'xOrientation' => 'right',
                            'width' => 500,
                            'cols' => array(
                                'ENDSUMME_A' => array(
                                    'justification' => 'right',
                                    'width' => 50
                                ),
                                'EIGENTUEMER_NAMEN' => array(
                                    'justification' => 'left',
                                    'width' => 50
                                )
                            )
                        ));
                    } else {
                        // $pdf->ezText($pdf_tab[$a]['ANSCHRIFT'], 11);
                        $anschrift = $pdf_tab [$a] ['ANSCHRIFT'];
                        $cols = array(
                            'EIGENTUEMER_NAMEN' => "<b>Eigentümer</b>",
                            'EINHEIT_KURZNAME' => "<b>apart.No</b>",
                            'MIETER' => "<b>tenant</b>",
                            'WEG-FLAECHE_A' => "<b>size [m²]</b>",
                            'NETTO_SOLL_A' => "<b>net rent [€]</b>",
                            'ABGABE_IHR_A' => "<b>for maint. [€]</b>",
                            'ABGABE_HV_A' => "<b>mng. fee [€]</b>",
                            'ENDSUMME_A' => "<b>Amount [€]</b>"
                        );
                        $pdf->ezTable($pdf_tab, $cols, "<b>$monat_name $jahr - Gesamtübersicht - $ein_nam, $anschrift</b>", array(
                            'showHeadings' => 1,
                            'shaded' => 1,
                            'titleFontSize' => 8,
                            'fontSize' => 7,
                            'xPos' => 30,
                            'xOrientation' => 'right',
                            'width' => 550,
                            'cols' => array(
                                'ENDSUMME_A' => array(
                                    'justification' => 'right',
                                    'width' => 50
                                ),
                                'EIGENTUEMER_NAMEN' => array(
                                    'justification' => 'left',
                                    'width' => 50
                                )
                            )
                        ));
                    }

                    $pdf->ezSetDy(-10); // abstand
                    if (is_array($my_arr [$a] ['AUSGABEN'])) {
                        array_unshift($my_arr [$a] ['AUSGABEN'], array(
                            'DATUM' => ' '
                        ));
                        array_unshift($my_arr [$a] ['AUSGABEN'], array(
                            'DATUM' => ' '
                        ));

                        if ($lang == 'en') {
                            $cols = array(
                                'DATUM' => "<b>Date</b>",
                                'VERWENDUNGSZWECK' => "<b>Description</b>",
                                'BETRAG' => "<b>Amount [€]</b>"
                            );
                            $pdf->ezTable($my_arr [$a] ['AUSGABEN'], $cols, "<b>Maintenance bills | cost account: [1023]</b>", array(
                                'showHeadings' => 1,
                                'shaded' => 1,
                                'titleFontSize' => 8,
                                'fontSize' => 7,
                                'xPos' => 50,
                                'xOrientation' => 'right',
                                'width' => 500,
                                'cols' => array(
                                    'BETRAG' => array(
                                        'justification' => 'right',
                                        'width' => 65
                                    ),
                                    'DATUM' => array(
                                        'justification' => 'left',
                                        'width' => 50
                                    )
                                )
                            ));

                            if (is_array($my_arr [$a] ['5500']) && count($my_arr [$a] ['5500']) > 1) {
                                $pdf->ezSetDy(-10); // abstand
                                $pdf->ezTable($my_arr [$a] ['5500'], $cols, "<b>broker fee | cost account: [5500]</b>", array(
                                    'showHeadings' => 1,
                                    'shaded' => 1,
                                    'titleFontSize' => 8,
                                    'fontSize' => 7,
                                    'xPos' => 50,
                                    'xOrientation' => 'right',
                                    'width' => 500,
                                    'cols' => array(
                                        'BETRAG' => array(
                                            'justification' => 'right',
                                            'width' => 65
                                        ),
                                        'DATUM' => array(
                                            'justification' => 'left',
                                            'width' => 50
                                        )
                                    )
                                ));
                            }

                            if (is_array($my_arr [$a] ['4180']) && count($my_arr [$a] ['4180']) > 1) {
                                $pdf->ezSetDy(-10); // abstand
                                $pdf->ezTable($my_arr [$a] ['4180'], $cols, "<b>allowed rent increase | cost account: [4180]</b>", array(
                                    'showHeadings' => 1,
                                    'shaded' => 1,
                                    'titleFontSize' => 8,
                                    'fontSize' => 7,
                                    'xPos' => 50,
                                    'xOrientation' => 'right',
                                    'width' => 500,
                                    'cols' => array(
                                        'BETRAG' => array(
                                            'justification' => 'right',
                                            'width' => 65
                                        ),
                                        'DATUM' => array(
                                            'justification' => 'left',
                                            'width' => 50
                                        )
                                    )
                                ));
                            }
                            if (is_array($my_arr [$a] ['4280']) && count($my_arr [$a] ['4280']) > 1) {
                                $pdf->ezSetDy(-10); // abstand
                                $pdf->ezTable($my_arr [$a] ['4280'], $cols, "<b>court fees | cost account: [4280]</b>", array(
                                    'showHeadings' => 1,
                                    'shaded' => 1,
                                    'titleFontSize' => 8,
                                    'fontSize' => 7,
                                    'xPos' => 50,
                                    'xOrientation' => 'right',
                                    'width' => 500,
                                    'cols' => array(
                                        'BETRAG' => array(
                                            'justification' => 'right',
                                            'width' => 65
                                        ),
                                        'DATUM' => array(
                                            'justification' => 'left',
                                            'width' => 50
                                        )
                                    )
                                ));
                            }
                            if (is_array($my_arr [$a] ['4281']) && count($my_arr [$a] ['4281']) > 1) {
                                $pdf->ezSetDy(-10); // abstand
                                $pdf->ezTable($my_arr [$a] ['4281'], $cols, "<b>payment for lawyer | cost account: [4281]</b>", array(
                                    'showHeadings' => 1,
                                    'shaded' => 1,
                                    'titleFontSize' => 8,
                                    'fontSize' => 7,
                                    'xPos' => 50,
                                    'xOrientation' => 'right',
                                    'width' => 500,
                                    'cols' => array(
                                        'BETRAG' => array(
                                            'justification' => 'right',
                                            'width' => 65
                                        ),
                                        'DATUM' => array(
                                            'justification' => 'left',
                                            'width' => 50
                                        )
                                    )
                                ));
                            }
                            if (is_array($my_arr [$a] ['4282']) && count($my_arr [$a] ['4282']) > 1) {
                                // print_r($my_arr[$a]['4180']);
                                $pdf->ezSetDy(-10); // abstand
                                $pdf->ezTable($my_arr [$a] ['4282'], $cols, "<b>payment for marshal | cost account: [4282]</b>", array(
                                    'showHeadings' => 1,
                                    'shaded' => 1,
                                    'titleFontSize' => 8,
                                    'fontSize' => 7,
                                    'xPos' => 50,
                                    'xOrientation' => 'right',
                                    'width' => 500,
                                    'cols' => array(
                                        'BETRAG' => array(
                                            'justification' => 'right',
                                            'width' => 65
                                        ),
                                        'DATUM' => array(
                                            'justification' => 'left',
                                            'width' => 50
                                        )
                                    )
                                ));
                            }

                            if (is_array($my_arr [$a] ['5081']) && count($my_arr [$a] ['5081']) > 1) {
                                // print_r($my_arr[$a]['4180']);
                                $pdf->ezSetDy(-10); // abstand
                                $pdf->ezTable($my_arr [$a] ['5081'], $cols, "<b>credit repayment | cost account: [5081]</b>", array(
                                    'showHeadings' => 1,
                                    'shaded' => 1,
                                    'titleFontSize' => 8,
                                    'fontSize' => 7,
                                    'xPos' => 50,
                                    'xOrientation' => 'right',
                                    'width' => 500,
                                    'cols' => array(
                                        'BETRAG' => array(
                                            'justification' => 'right',
                                            'width' => 65
                                        ),
                                        'DATUM' => array(
                                            'justification' => 'left',
                                            'width' => 50
                                        )
                                    )
                                ));
                            }

                            /*
							 * if(is_array($my_arr[$a]['5010']) && count($my_arr[$a]['5010'])>1){
							 * #print_r($my_arr[$a]['4180']);
							 * $pdf->ezSetDy(-10); //abstand
							 * $pdf->ezTable($my_arr[$a]['5010'], $cols, "<b>Actual transfer income | cost account: [5010]</b>", array('showHeadings'=>1,'shaded'=>1, 'titleFontSize' => 8, 'fontSize' => 7, 'xPos'=>50,'xOrientation'=>'right', 'width'=>500, 'cols'=>array('BETRAG'=>array('justification'=>'right', 'width'=>65), 'DATUM'=>array('justification'=>'left', 'width'=>50))));
							 * }
							 */

                            // ie("TEST");
                        } else {
                            $cols = array(
                                'DATUM' => "Datum",
                                'VERWENDUNGSZWECK' => "Buchungstext",
                                'BETRAG' => "Betrag"
                            );
                            $pdf->ezTable($my_arr [$a] ['AUSGABEN'], $cols, "<b>$monat_name $jahr - Reparaturen 1023 - $ein_nam</b>", array(
                                'showHeadings' => 1,
                                'shaded' => 1,
                                'titleFontSize' => 8,
                                'fontSize' => 7,
                                'xPos' => 50,
                                'xOrientation' => 'right',
                                'width' => 500,
                                'cols' => array(
                                    'BETRAG' => array(
                                        'justification' => 'right',
                                        'width' => 65
                                    ),
                                    'DATUM' => array(
                                        'justification' => 'left',
                                        'width' => 50
                                    )
                                )
                            ));
                        }
                    } else {
                        $pdf->ezText("Keine Reparaturen", 12);
                    }
                    $pdf->ezSetDy(-20); // abstand
                    // $cols = array('ENDSUMME_A'=>"Amount1", 'SUMME_REP_A'=>"Amount", 'TRANSFER_A'=>"Transfer");
                    // $pdf->ezTable($trans_tab, $cols);
                    // unset($trans_tab);
                    $trans_tab [0] ['TEXT'] = "Amount [€]";
                    $trans_tab [0] ['AM'] = $uebersicht [$a] ['ENDSUMME_A'];
                    $trans_tab [1] ['TEXT'] = "Bills [€]";
                    $trans_tab [1] ['AM'] = $uebersicht [$a] ['SUMME_REP_A'];
                    $trans_tab [2] ['TEXT'] = "<b>To transfer [€]</b>";
                    if ($uebersicht [$a] ['TRANSFER'] > 0) {
                        $trans_tab [2] ['TEXT'] = "<b>To transfer [€]</b>";
                        $trans_tab [2] ['AM'] = "<b>" . $uebersicht [$a] ['TRANSFER_A'] . "</b>";
                    } else {
                        $trans_tab [2] ['TEXT'] = "<b>Summary [€]</b>";
                        $trans_tab [2] ['AM'] = "<b>" . $uebersicht [$a] ['TRANSFER_A'] . "</b>";
                        $trans_tab [3] ['TEXT'] = "<b>To transfer [€]</b>";
                        $trans_tab [3] ['AM'] = "<b>0,00</b>";
                    }
                    /* Gebuchte Überweisung Kto: 5020 */
                    /*
					 * $trans_tab[3]['TEXT'] = "<b>Current Transfer [€]</b>";
					 * $trans_tab[3]['AM'] = "<b>xxx</b>";
					 */

                    /*
					 * if(is_array($my_arr[$a]['5020']) && count($my_arr[$a]['5020'])>1){
					 * $pdf->ezSetDy(-10); //abstand
					 * $pdf->ezTable($my_arr[$a]['5020'], $cols, "<b>Current Transfer | cost account: [5020]</b>", array('showHeadings'=>1,'shaded'=>1, 'titleFontSize' => 8, 'fontSize' => 7, 'xPos'=>50,'xOrientation'=>'right', 'width'=>500, 'cols'=>array('BETRAG'=>array('justification'=>'right', 'width'=>65), 'DATUM'=>array('justification'=>'left', 'width'=>50))));
					 * }
					 */

                    $cols = array(
                        'TEXT' => "",
                        'AM' => ""
                    );
                    $pdf->ezTable($trans_tab, $cols, "<b>Summary $w_monat/$jahr</b>", array(
                        'showHeadings' => 0,
                        'shaded' => 1,
                        'titleFontSize' => 8,
                        'fontSize' => 7,
                        'xPos' => 235,
                        'xOrientation' => 'right',
                        'width' => 500,
                        'cols' => array(
                            'TEXT' => array(
                                'justification' => 'right',
                                'width' => 250
                            ),
                            'AM' => array(
                                'justification' => 'right',
                                'width' => 65
                            )
                        )
                    ));
                    $cols = array(
                        'DATUM' => "<b>Date</b>",
                        'VERWENDUNGSZWECK' => "<b>Description</b>",
                        'BETRAG' => "<b>Amount [€]</b>"
                    );

                    // $pdf->setColor(1.0,0.0,0.0);
                    // $pdf->ezText("SANEL");

                    $pdf->options [] = array(
                        'textCol' => array(
                            1,
                            0,
                            0
                        )
                    );
                    if (is_array($my_arr [$a] ['5010']) && count($my_arr [$a] ['5010']) > 1) {
                        $anz_aus = count($my_arr [$a] ['5010']);

                        for ($aaa = 0; $aaa < $anz_aus; $aaa++) {
                            if ($aaa == $anz_aus - 1) {
                                $bbbb = $my_arr [$a] ['5010'] [$aaa] ['BETRAG'];
                                $my_arr [$a] ['5010'] [$aaa] ['BETRAG'] = "<b>$bbbb</b>";
                            } else {
                                $my_arr [$a] ['5010'] [$aaa] ['BETRAG'] = $my_arr [$a] ['5010'] [$aaa] ['BETRAG'];
                            }
                        }

                        $pdf->ezSetDy(-10); // abstand
                        // $pdf->options['titleCol'] =array(1,0,0);
                        $pdf->ezTable($my_arr [$a] ['5010'], $cols, "<b>Actual transfer income | cost account: [5010]</b>", array(
                            'showHeadings' => 1,
                            'shaded' => 1,
                            'titleFontSize' => 8,
                            'fontSize' => 7,
                            'xPos' => 50,
                            'xOrientation' => 'right',
                            'width' => 500,
                            'cols' => array(
                                'BETRAG' => array(
                                    'justification' => 'right',
                                    'width' => 65
                                ),
                                'DATUM' => array(
                                    'justification' => 'left',
                                    'width' => 50
                                )
                            )
                        ));
                        // $pdf->setColor(0.0,0.0,0.0);
                    }

                    $pdf->ezSetDy(-10); // abstand

                    if (is_array($my_arr [$a] ['5020']) && count($my_arr [$a] ['5020']) > 1) {
                        $anz_aus = count($my_arr [$a] ['5020']);

                        for ($aaa = 0; $aaa < $anz_aus; $aaa++) {
                            if ($aaa == $anz_aus - 1) {
                                $bbbb = $my_arr [$a] ['5020'] [$aaa] ['BETRAG'] * -1; // POSITIVIEREN
                                $my_arr [$a] ['5020'] [$aaa] ['BETRAG'] = "<b>$bbbb</b>";
                            } else {
                                $my_arr [$a] ['5020'] [$aaa] ['BETRAG'] = $my_arr [$a] ['5020'] [$aaa] ['BETRAG'] * -1; // POSITIVIEREN
                            }
                        }
                    } else {
                        $my_arr [$a] ['5020'] [0] ['BETRAG'] = "<b>0.00</b>";
                        $my_arr [$a] ['5020'] [0] ['VERWENDUNGSZWECK'] = "<b>No transfer</b>";
                    }

                    $pdf->ezTable($my_arr [$a] ['5020'], $cols, "<b>Actual transfer | cost account: [5020]</b>", array(
                        'showHeadings' => 1,
                        'shaded' => 1,
                        'titleFontSize' => 8,
                        'fontSize' => 7,
                        'xPos' => 50,
                        'xOrientation' => 'right',
                        'width' => 500,
                        'cols' => array(
                            'BETRAG' => array(
                                'justification' => 'right',
                                'width' => 65
                            ),
                            'DATUM' => array(
                                'justification' => 'left',
                                'width' => 50
                            )
                        )
                    ));

                    unset ($trans_tab);

                    $pdf->ezNewPage();

                    unset ($pdf_tab);
                }
            }
            $uebersicht [$anz] ['EIGENTUEMER_NAMEN'] = 'Soll';
            $uebersicht [$anz] ['ENDSUMME_A'] = nummer_punkt2komma_t($summe_alle_eigentuemer);
            $uebersicht [$anz + 1] ['EIGENTUEMER_NAMEN'] = 'Auszahlen';
            $uebersicht [$anz + 1] ['TRANSFER_A'] = nummer_punkt2komma_t($summe_transfer);
            if ($lang == 'en') {
                $cols = array(
                    'EINHEIT_KURZNAME' => "Apt",
                    'WEG-FLAECHE_A' => 'm²',
                    'EIGENTUEMER_NAMEN' => "Own",
                    'MIETER' => "Tenant",
                    'MIETER_SALDO_VM' => 'VM',
                    'MIETER_SALDO_A' => 'current',
                    'NETTO_SOLL_G_A' => "Garanty",
                    'NETTO_SOLL_A' => "net rent",
                    'NETTO_SOLL_DIFF_A' => "diff",
                    'ABGABE_HV_A' => "fee",
                    'ABGABE_IHR' => "for maint.",
                    'ENDSUMME_A' => "Amount",
                    'SUMME_REP_A' => 'Rep.',
                    'TRANSFER_A' => 'transfer'
                );
            } else {
                $cols = array(
                    'EINHEIT_KURZNAME' => "Apt",
                    'WEG-FLAECHE_A' => 'm²',
                    'EIGENTUEMER_NAMEN' => "Own",
                    'MIETER' => "Tenant",
                    'MIETER_SALDO_A' => 'current',
                    'NETTO_SOLL_G_A' => "Garanty",
                    'NETTO_SOLL_A' => "net rent",
                    'NETTO_SOLL_DIFF_A' => "diff",
                    'ABGABE_HV_A' => "fee",
                    'ABGABE_IHR' => "for maint.",
                    'ENDSUMME_A' => "Amount",
                    'SUMME_REP_A' => 'Rep.',
                    'TRANSFER_A' => 'transfer'
                );
            }
            $von_d = date_mysql2german($von);
            $bis_d = date_mysql2german($bis);
            $pdf->ezText("<b>Kostenabfrage von: $von_d bis: $bis_d</b>", 12);
            $pdf->ezSetDy(-20); // abstand
            $pdf->ezTable($uebersicht, $cols, null, array(
                'showHeadings' => 1,
                'shaded' => 1,
                'titleFontSize' => 8,
                'fontSize' => 7,
                'xPos' => 10,
                'xOrientation' => 'right',
                'width' => 550,
                'cols' => array(
                    'EINHEIT_KURZNAME' => array(
                        'justification' => 'left',
                        'width' => '40'
                    ),
                    'EIGENTUEMER_NAME' => array(
                        'justification' => 'right',
                        'width' => '40'
                    ),
                    'MIETER' => array(
                        'justification' => 'right',
                        'width' => '60'
                    )
                )
            ));

            $anz_m = count($my_arr);
            $z = 0;
            for ($mm = 0; $mm < $anz_m; $mm++) {
                $saldo_m_et = 0;
                $einheit_kn = $my_arr [$mm] ['EINHEIT_KURZNAME'];

                /* Ausgaben 1023 */
                if (is_array($my_arr [$mm] ['AUSGABEN'])) {

                    $anz_ab = count($my_arr [$mm] ['AUSGABEN']);
                    for ($ab = 0; $ab < $anz_ab; $ab++) {
                        if (isset ($my_arr [$mm] ['AUSGABEN'] [$ab] ['GELD_KONTO_BUCHUNGEN_DAT'])) {

                            $konto = $my_arr [$mm] ['AUSGABEN'] [$ab] ['KONTENRAHMEN_KONTO'];
                            $vzweck = $my_arr [$mm] ['AUSGABEN'] [$ab] ['VERWENDUNGSZWECK'];

                            $et_ue_tab [$einheit_kn] [$z] ['TXT'] = "Repairs $konto";
                            $et_ue_tab [$einheit_kn] [$z] ['BEZ'] = $vzweck;
                            $et_ue_tab [$einheit_kn] [$z] ['BET'] = $my_arr [$mm] ['AUSGABEN'] [$ab] ['BETRAG'];
                            $et_ue_tab [$einheit_kn] [$z] ['DATUM'] = $my_arr [$mm] ['AUSGABEN'] [$ab] ['DATUM'];
                            $saldo_m_et += $et_ue_tab [$einheit_kn] [$z] ['BET'];
                            $z++;
                        }
                    }
                }

                /* Ausgaben 5500 */
                if (is_array($my_arr [$mm] ['5500'])) {

                    $anz_ab = count($my_arr [$mm] ['5500']);
                    for ($ab = 0; $ab < $anz_ab; $ab++) {
                        if (isset ($my_arr [$mm] ['5500'] [$ab] ['GELD_KONTO_BUCHUNGEN_DAT'])) {
                            $vzweck = $my_arr [$mm] ['5500'] [$ab] ['VERWENDUNGSZWECK'];

                            $et_ue_tab [$einheit_kn] [$z] ['TXT'] = "Konto 5500";
                            $et_ue_tab [$einheit_kn] [$z] ['BEZ'] = $vzweck;
                            $et_ue_tab [$einheit_kn] [$z] ['BET'] = $my_arr [$mm] ['5500'] [$ab] ['BETRAG'];
                            $et_ue_tab [$einheit_kn] [$z] ['DATUM'] = $my_arr [$mm] ['5500'] [$ab] ['DATUM'];
                            $saldo_m_et += $et_ue_tab [$einheit_kn] [$z] ['BET'];
                            $z++;
                        }
                    }
                }

                /* Ausgaben 4180 */
                if (is_array($my_arr [$mm] ['4180'])) {

                    $anz_ab = count($my_arr [$mm] ['4180']);
                    for ($ab = 0; $ab < $anz_ab; $ab++) {
                        if (isset ($my_arr [$mm] ['4180'] [$ab] ['GELD_KONTO_BUCHUNGEN_DAT'])) {
                            $vzweck = $my_arr [$mm] ['4180'] [$ab] ['VERWENDUNGSZWECK'];

                            $et_ue_tab [$einheit_kn] [$z] ['TXT'] = "Konto 4180";
                            $et_ue_tab [$einheit_kn] [$z] ['BEZ'] = $vzweck;
                            $et_ue_tab [$einheit_kn] [$z] ['BET'] = $my_arr [$mm] ['4180'] [$ab] ['BETRAG'];
                            $et_ue_tab [$einheit_kn] [$z] ['DATUM'] = $my_arr [$mm] ['4180'] [$ab] ['DATUM'];
                            $saldo_m_et += $et_ue_tab [$einheit_kn] [$z] ['BET'];
                            $z++;
                        }
                    }
                }

                /* Ausgaben 4280 */
                if (is_array($my_arr [$mm] ['4280'])) {

                    $anz_ab = count($my_arr [$mm] ['4280']);
                    for ($ab = 0; $ab < $anz_ab; $ab++) {
                        if (isset ($my_arr [$mm] ['4280'] [$ab] ['GELD_KONTO_BUCHUNGEN_DAT'])) {
                            $vzweck = $my_arr [$mm] ['4280'] [$ab] ['VERWENDUNGSZWECK'];

                            $et_ue_tab [$einheit_kn] [$z] ['TXT'] = "Konto 4280";
                            $et_ue_tab [$einheit_kn] [$z] ['BEZ'] = $vzweck;
                            $et_ue_tab [$einheit_kn] [$z] ['BET'] = $my_arr [$mm] ['4280'] [$ab] ['BETRAG'];
                            $et_ue_tab [$einheit_kn] [$z] ['DATUM'] = $my_arr [$mm] ['4280'] [$ab] ['DATUM'];
                            $saldo_m_et += $et_ue_tab [$einheit_kn] [$z] ['BET'];
                            $z++;
                        }
                    }
                }

                /* Ausgaben 4281 */
                if (is_array($my_arr [$mm] ['4281'])) {

                    $anz_ab = count($my_arr [$mm] ['4281']);
                    for ($ab = 0; $ab < $anz_ab; $ab++) {
                        if (isset ($my_arr [$mm] ['4281'] [$ab] ['GELD_KONTO_BUCHUNGEN_DAT'])) {
                            $vzweck = $my_arr [$mm] ['4281'] [$ab] ['VERWENDUNGSZWECK'];

                            $et_ue_tab [$einheit_kn] [$z] ['TXT'] = "Konto 4281";
                            $et_ue_tab [$einheit_kn] [$z] ['BEZ'] = $vzweck;
                            $et_ue_tab [$einheit_kn] [$z] ['BET'] = $my_arr [$mm] ['4281'] [$ab] ['BETRAG'];
                            $et_ue_tab [$einheit_kn] [$z] ['DATUM'] = $my_arr [$mm] ['4281'] [$ab] ['DATUM'];
                            $saldo_m_et += $et_ue_tab [$einheit_kn] [$z] ['BET'];
                            $z++;
                        }
                    }
                }

                /* Ausgaben 4282 */
                if (is_array($my_arr [$mm] ['4282'])) {

                    $anz_ab = count($my_arr [$mm] ['4282']);
                    for ($ab = 0; $ab < $anz_ab; $ab++) {
                        if (isset ($my_arr [$mm] ['4282'] [$ab] ['GELD_KONTO_BUCHUNGEN_DAT'])) {
                            $vzweck = $my_arr [$mm] ['4282'] [$ab] ['VERWENDUNGSZWECK'];

                            $et_ue_tab [$einheit_kn] [$z] ['TXT'] = "Konto 4282";
                            $et_ue_tab [$einheit_kn] [$z] ['BEZ'] = $vzweck;
                            $et_ue_tab [$einheit_kn] [$z] ['BET'] = $my_arr [$mm] ['4282'] [$ab] ['BETRAG'];
                            $et_ue_tab [$einheit_kn] [$z] ['DATUM'] = $my_arr [$mm] ['4282'] [$ab] ['DATUM'];
                            $saldo_m_et += $et_ue_tab [$einheit_kn] [$z] ['BET'];
                            $z++;
                        }
                    }
                }

                /* Ausgaben 5081 */
                if (is_array($my_arr [$mm] ['5081'])) {

                    $anz_ab = count($my_arr [$mm] ['5081']);
                    for ($ab = 0; $ab < $anz_ab; $ab++) {
                        if (isset ($my_arr [$mm] ['5081'] [$ab] ['GELD_KONTO_BUCHUNGEN_DAT'])) {
                            $vzweck = $my_arr [$mm] ['5081'] [$ab] ['VERWENDUNGSZWECK'];

                            $et_ue_tab [$einheit_kn] [$z] ['TXT'] = "Konto 5081";
                            $et_ue_tab [$einheit_kn] [$z] ['BEZ'] = $vzweck;
                            $et_ue_tab [$einheit_kn] [$z] ['BET'] = $my_arr [$mm] ['5081'] [$ab] ['BETRAG'];
                            $et_ue_tab [$einheit_kn] [$z] ['DATUM'] = $my_arr [$mm] ['5081'] [$ab] ['DATUM'];
                            $saldo_m_et += $et_ue_tab [$einheit_kn] [$z] ['BET'];
                            $z++;
                        }
                    }
                }

                /* Ausgaben 5010 */
                if (is_array($my_arr [$mm] ['5010'])) {

                    $anz_ab = count($my_arr [$mm] ['5010']);
                    for ($ab = 0; $ab < $anz_ab; $ab++) {
                        if (isset ($my_arr [$mm] ['5010'] [$ab] ['GELD_KONTO_BUCHUNGEN_DAT'])) {
                            $vzweck = $my_arr [$mm] ['5010'] [$ab] ['VERWENDUNGSZWECK'];

                            $et_ue_tab [$einheit_kn] [$z] ['TXT'] = "Konto 5010";
                            $et_ue_tab [$einheit_kn] [$z] ['BEZ'] = $vzweck;
                            $et_ue_tab [$einheit_kn] [$z] ['BET'] = $my_arr [$mm] ['5010'] [$ab] ['BETRAG'];
                            $et_ue_tab [$einheit_kn] [$z] ['DATUM'] = $my_arr [$mm] ['5010'] [$ab] ['DATUM'];
                            $saldo_m_et += $et_ue_tab [$einheit_kn] [$z] ['BET'];
                            $z++;
                        }
                    }
                }

                /* Ausgaben 5020 */
                if (is_array($my_arr [$mm] ['5020'])) {

                    $anz_ab = count($my_arr [$mm] ['5020']);
                    for ($ab = 0; $ab < $anz_ab; $ab++) {
                        if (isset ($my_arr [$mm] ['5020'] [$ab] ['GELD_KONTO_BUCHUNGEN_DAT'])) {
                            $vzweck = $my_arr [$mm] ['5020'] [$ab] ['VERWENDUNGSZWECK'];

                            $et_ue_tab [$einheit_kn] [$z] ['TXT'] = "Konto 5020";
                            $et_ue_tab [$einheit_kn] [$z] ['BEZ'] = $vzweck;
                            $et_ue_tab [$einheit_kn] [$z] ['BET'] = $my_arr [$mm] ['5020'] [$ab] ['BETRAG'] * -1;
                            $et_ue_tab [$einheit_kn] [$z] ['DATUM'] = $my_arr [$mm] ['5020'] [$ab] ['DATUM'];
                            $saldo_m_et += $et_ue_tab [$einheit_kn] [$z] ['BET'];
                            $z++;
                        }
                    }
                }

                /* Saldo */
                if ($saldo_m_et != 0) {
                    $et_ue_tab [$einheit_kn] [$z] ['TXT'] = 'SALDO';
                    $et_ue_tab [$einheit_kn] [$z] ['BEZ'] = 'SALDO';
                    $et_ue_tab [$einheit_kn] [$z] ['BET'] = nummer_komma2punkt(nummer_punkt2komma($saldo_m_et));
                }

                $z = 0;
            }

            // echo "<hr>";
            // echo '<pre>';
            // print_r($et_ue_tab);
            // die();

            $w_keys = array_unique(array_keys($et_ue_tab));
            $colss = array(
                'DATUM' => "Date",
                'TXT' => "Description",
                'BEZ' => "Description1",
                'BET' => "Amount"
            );
            $pdf->ezNewPage();
            for ($p = 0; $p < count($w_keys); $p++) {
                $wohnung = $w_keys [$p];
                // $pdf->ezNewPage();
                // $pdf->eztable($et_ue_tab[$wohnung]);
                $pdf->ezTable($et_ue_tab [$wohnung], $colss, "<b>$wohnung $monat/$jahr</b>", array(
                    'showHeadings' => 1,
                    'shaded' => 1,
                    'titleFontSize' => 8,
                    'fontSize' => 7,
                    'xPos' => 50,
                    'xOrientation' => 'right',
                    'width' => 500,
                    'cols' => array(
                        'DATUM' => array(
                            'justification' => 'left',
                            'width' => 50
                        ),
                        'TXT' => array(
                            'justification' => 'right',
                            'width' => 100
                        ),
                        'BEZ' => array(
                            'justification' => 'left'
                        ),
                        'BET' => array(
                            'justification' => 'right',
                            'width' => 60
                        )
                    )
                ));
                $pdf->ezSetDy(-10); // abstand
            }

            ob_end_clean(); // ausgabepuffer leeren
            $pdf->ezStream();
        } else {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\InfoMessage("Keine Einheiten im Objekt $objekt_id")
            );
        }
    }

    function get_kosten_von_bis($kos_typ, $kos_id, $von, $bis, $gk_id, $konto = null)
    {
        if ($konto == null) {
            $db_abfrage = "SELECT * FROM `GELD_KONTO_BUCHUNGEN` WHERE `GELDKONTO_ID` = '$gk_id' AND DATUM BETWEEN '$von' and '$bis' AND `KOSTENTRAEGER_TYP` = '$kos_typ' AND `KOSTENTRAEGER_ID` = '$kos_id' AND `AKTUELL` = '1'  ORDER BY KONTENRAHMEN_KONTO, DATUM";
        } else {
            $db_abfrage = "SELECT * FROM `GELD_KONTO_BUCHUNGEN` WHERE `GELDKONTO_ID` = '$gk_id' AND DATUM BETWEEN '$von' and '$bis' AND `KOSTENTRAEGER_TYP` = '$kos_typ' AND `KOSTENTRAEGER_ID` = '$kos_id' AND `AKTUELL` = '1' && KONTENRAHMEN_KONTO='$konto' ORDER BY KONTENRAHMEN_KONTO, DATUM";
        }
        $result = DB::select($db_abfrage);
        $sum = 0.00;
        if (!empty($result)) {

            foreach ($result as $row) {
                $my_array [] = $row;
                $sum += $row ['BETRAG'];
            }
        }
        $my_array [] ['BETRAG'] = nummer_komma2punkt(nummer_punkt2komma($sum));
        return $my_array;
    }

    function inspiration_pdf_kurz_7($ausgezogene = 0, $objekt_id, $monat, $jahr, $lang = 'de')
    {
        /* Eingrenzung Kostenabragen */
        if (!request()->has('von') or !request()->has('bis')) {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\InfoMessage('Abfragedatum VON BIS in die URL hinzufügen')
            );
        }
        $von = date_german2mysql(request()->input('von'));
        $bis = date_german2mysql(request()->input('bis'));

        $monat_name = monat2name($monat);
        $pdf = new Cezpdf ('a4', 'portrait');
        $bpdf = new b_pdf ();
        $bpdf->b_header($pdf, 'Partner', session()->get('partner_id'), 'portrait', 'Helvetica.afm', 6);
        $pdf->ezStopPageNumbers(); // seitennummerirung beenden
        $gk = new geldkonto_info ();
        $gk->geld_konto_ermitteln('OBJEKT', $objekt_id);
        echo '<pre>';
        // print_r($gk);
        if (!$gk->geldkonto_id) {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\ErrorMessage('Geldkonto zum Objekt hinzufügen.')
            );
        }
        $db_abfrage = "SELECT OBJEKT_KURZNAME, HAUS_STRASSE, HAUS_NUMMER, `EINHEIT_KURZNAME` , `EINHEIT_ID`,  ltrim(rtrim(EINHEIT_LAGE)) AS EINHEIT_LAGE, `EINHEIT_QM` FROM EINHEIT , HAUS, OBJEKT
	WHERE `EINHEIT_AKTUELL` = '1' && EINHEIT.HAUS_ID = HAUS.HAUS_ID && HAUS.OBJEKT_ID=OBJEKT.OBJEKT_ID && HAUS_AKTUELL='1' && OBJEKT_AKTUELL='1' && OBJEKT.OBJEKT_ID='$objekt_id'
	GROUP BY EINHEIT_ID ORDER BY EINHEIT_KURZNAME";

        $result = DB::select($db_abfrage);
        if (!empty($result)) {
            $z = 0;
            foreach ($result as $row) {
                $my_arr [] = $row;
                $einheit_id = $row ['EINHEIT_ID'];
                $einheit_qm = $row ['EINHEIT_QM'];
                $e = new einheit ();
                $e->get_einheit_info($einheit_id);
                $my_arr [$z] ['ANSCHRIFT'] = "$e->haus_strasse $e->haus_nummer, $e->haus_plz $e->haus_stadt";
                $det = new detail ();
                $my_arr [$z] ['WEG-FLAECHE_A'] = $det->finde_detail_inhalt('EINHEIT', $einheit_id, 'WEG-Fläche'); // kommt als Kommazahl
                if (empty ($my_arr [$z] ['WEG-FLAECHE_A'])) {
                    $my_arr [$z] ['WEG-FLAECHE_A'] = nummer_punkt2komma($einheit_qm);
                }
                $my_arr [$z] ['WEG-FLAECHE'] = nummer_komma2punkt($my_arr [$z] ['WEG-FLAECHE_A']);

                $my_arr [$z] ['WG_NR'] = $det->finde_detail_inhalt('EINHEIT', $einheit_id, 'Alte Nr'); // kommt als Kommazahl

                $weg = new weg ();
                $weg->get_last_eigentuemer($einheit_id);
                if (isset ($weg->eigentuemer_name)) {
                    $weg->get_eigentuemer_namen($weg->eigentuemer_id);
                    $my_arr [$z] ['EIGENTUEMER_NAMEN'] = $weg->eigentuemer_name_str_u;
                    $my_arr [$z] ['EIGENTUEMER_ID'] = $weg->eigentuemer_id;

                    /* Personenkontaktdaten Eigentümer */
                    $et_p_id = $weg->get_person_id_eigentuemer_arr($weg->eigentuemer_id);
                    if (!empty($et_p_id)) {
                        $anz_pp = count($et_p_id);
                        for ($pe = 0; $pe < $anz_pp; $pe++) {
                            $et_p_id_1 = $et_p_id [$pe] ['PERSON_ID'];
                            // echo $et_p_id_1;
                            $detail = new detail ();
                            if (($detail->finde_detail_inhalt('PERSON', $et_p_id_1, 'Email'))) {
                                $email_arr = $detail->finde_alle_details_grup('PERSON', $et_p_id_1, 'Email');
                                for ($ema = 0; $ema < count($email_arr); $ema++) {
                                    $em_adr = $email_arr [$ema] ['DETAIL_INHALT'];
                                    $my_arr [$z] ['EMAILS'] [] = $em_adr;
                                }
                            }
                        }
                    } else {
                        throw new \App\Exceptions\MessageException(
                            new \App\Messages\ErrorMessage("Personen/Eigentümer unbekannt! ET_ID: $weg->eigentuemer_id")
                        );
                    }
                } else {
                    $my_arr [$z] ['EIGENTUEMER'] = 'Unbekannt';
                }
                $mv_id = $e->get_mietvertraege_zu($einheit_id, $jahr, $monat);

                if ($mv_id) {
                    $mvs = new mietvertraege ();
                    $mvs->get_mietvertrag_infos_aktuell($mv_id);
                    $kontaktdaten = $e->kontaktdaten_mieter($mv_id);
                    $my_arr [$z] ['MIETER'] = $mvs->personen_name_string_u;
                    $my_arr [$z] ['MIETVERTRAG_ID'] = $mv_id;
                    $my_arr [$z] ['KONTAKT'] = $kontaktdaten;
                    $my_arr [$z] ['EINHEIT_ID'] = $einheit_id;
                    $mz = new miete ();
                    $mz->mietkonto_berechnung($mv_id);
                    $my_arr [$z] ['MIETER_SALDO'] = $mz->erg;
                    $my_arr [$z] ['MIETER_SALDO_VM'] = nummer_punkt2komma($mz->saldo_vormonat_end);
                } else {
                    $my_arr [$z] ['MIETER'] = "<b>Leerstand</b>";
                }
                $z++;
            }

            unset ($e);
            unset ($mvs);
            unset ($weg);

            $anz = count($my_arr);
            /* Berechnung Abgaben */
            for ($a = 0; $a < $anz; $a++) {
                $einheit_id = $my_arr [$a] ['EINHEIT_ID'];
                if (isset ($my_arr [$a] ['EIGENTUEMER_ID'])) {
                    $eige_id = $my_arr [$a] ['EIGENTUEMER_ID'];
                    $weg1 = new weg ();
                    $ihr_hg = $weg1->get_summe_kostenkat_monat($monat, $jahr, 'Einheit', $einheit_id, '6030');
                    if ($ihr_hg) {
                        $my_arr [$a] ['ABGABEN'] [] ['ABGABE_IHR'] = -$ihr_hg;
                    } else {
                        $my_arr [$a] ['ABGABEN'] [] ['ABGABE_IHR'] = $einheit_qm * -0.4;
                    }
                    $weg1 = new weg ();
                    $vg = $weg1->get_summe_kostenkat_monat($monat, $jahr, 'Einheit', $einheit_id, '6060');
                    if ($vg) {
                        $my_arr [$a] ['ABGABEN'] [] ['VG'] = $vg; // Verwaltergebühr
                    } else {
                        $my_arr [$a] ['ABGABEN'] [] ['VG'] = '30.00'; // Verwaltergebühr
                    }

                    /* Kosten 1023 Reparatur Einheit */
                    $my_arr [$a] ['AUSGABEN'] = $this->get_kosten_von_bis('Einheit', $einheit_id, $von, $bis, $gk->geldkonto_id, 1023);
                    $anz_rep = count($my_arr [$a] ['AUSGABEN']);
                    $summe_rep = 0;
                    for ($b = 0; $b < $anz_rep - 1; $b++) {
                        $summe_rep += $my_arr [$a] ['AUSGABEN'] [$b] ['BETRAG'];
                    }
                    $mk = new mietkonto ();
                    $mk->kaltmiete_monatlich($my_arr [$a] ['MIETVERTRAG_ID'], $monat, $jahr);
                    $brutto_sollmiete_arr = explode('|', $mk->summe_forderung_monatlich($my_arr [$a] ['MIETVERTRAG_ID'], $monat, $jahr));
                    $brutto_sollmiete = $brutto_sollmiete_arr [0];
                    $my_arr [$a] ['NETTO_SOLL_MV'] = $brutto_sollmiete;

                    /* Garantierte Miete abfragen */
                    $net_ren_garantie_a = $det->finde_detail_inhalt('EINHEIT', $einheit_id, 'WEG-KaltmieteINS'); // kommt als Kommazahl
                    $net_ren_garantie = nummer_komma2punkt($net_ren_garantie_a);
                    $my_arr [$a] ['NETTO_SOLL_G_A'] = $net_ren_garantie_a;
                    if ($net_ren_garantie > $brutto_sollmiete) {
                        $my_arr [$a] ['NETTO_SOLL'] = $net_ren_garantie;
                    } else {
                        $my_arr [$a] ['NETTO_SOLL'] = $brutto_sollmiete;
                    }

                    $my_arr [$a] ['BRUTTO_SOLL'] = $brutto_sollmiete;
                    $my_arr [$a] ['AUSZAHLUNG_ET'] = $this->get_kosten_arr('Eigentuemer', $eige_id, $monat, $jahr, $gk->geldkonto_id, 5020);

                    /* Andere Kosten */
                    /* INS MAKLERGEBÜHR */
                    $my_arr [$a] ['5500'] = $this->get_kosten_von_bis('Einheit', $einheit_id, $von, $bis, $gk->geldkonto_id, 5500);
                    /* Andere Kosten */
                    $my_arr [$a] ['4180'] = $this->get_kosten_von_bis('Einheit', $einheit_id, $von, $bis, $gk->geldkonto_id, 4180);
                    $my_arr [$a] ['4280'] = $this->get_kosten_von_bis('Einheit', $einheit_id, $von, $bis, $gk->geldkonto_id, 4280);
                    $my_arr [$a] ['4281'] = $this->get_kosten_von_bis('Einheit', $einheit_id, $von, $bis, $gk->geldkonto_id, 4281);
                    $my_arr [$a] ['4282'] = $this->get_kosten_von_bis('Einheit', $einheit_id, $von, $bis, $gk->geldkonto_id, 4282);
                    $my_arr [$a] ['5081'] = $this->get_kosten_von_bis('Eigentuemer', $eige_id, $von, $bis, $gk->geldkonto_id, 5081);
                    $my_arr [$a] ['5010'] = $this->get_kosten_von_bis('Eigentuemer', $eige_id, $von, $bis, $gk->geldkonto_id, 5010);

                    if (request()->has('von_a')) {
                        $von_a = date_german2mysql(request()->input('von_a'));
                    } else {
                        $von_a = "$jahr-$monat-01";
                    }
                    if (!request()->has('bis_a')) {
                        $lt = letzter_tag_im_monat($monat, $jahr);
                        $bis_a = "$jahr-$monat-$lt";
                    } else {
                        $bis_a = date_german2mysql(request()->input('bis_a'));
                    }
                    $my_arr [$a] ['5020'] = $this->get_kosten_von_bis('Eigentuemer', $eige_id, $von_a, $bis_a, $gk->geldkonto_id, 5020);

                    $my_arr [$a] ['IST_EINNAHMEN'] = $this->get_kosten_arr('MIETVERTRAG', $my_arr [$a] ['MIETVERTRAG_ID'], $monat, $jahr, $gk->geldkonto_id, 80001);
                    $anz_me = count($my_arr [$a] ['IST_EINNAHMEN']);
                    $summe_einnahmen = 0;
                    for ($b = 0; $b < $anz_me; $b++) {
                        $summe_einnahmen += $my_arr [$a] ['IST_EINNAHMEN'] [$b] ['BETRAG'];
                    }
                    $my_arr [$a] ['IST_EINNAHMEN'] [$anz_me] ['BETRAG'] = '<b>' . nummer_punkt2komma_t($summe_einnahmen) . '</b>';
                    $my_arr [$a] ['IST_EINNAHMEN'] [$anz_me] ['VERWENDUNGSZWECK'] = '<b>Summe</b>';
                    $my_arr [$a] ['BRUTTO_IST'] = $summe_einnahmen;

                    $pdf_tab [$a] ['MIETER_SALDO'] = $my_arr [$a] ['MIETER_SALDO'];
                    $pdf_tab [$a] ['MIETER_SALDO_VM'] = $my_arr [$a] ['MIETER_SALDO_VM'];
                    $pdf_tab [$a] ['EIGENTUEMER_NAMEN'] = $my_arr [$a] ['EIGENTUEMER_NAMEN'];
                    $pdf_tab [$a] ['EINHEIT_KURZNAME'] = $my_arr [$a] ['EINHEIT_KURZNAME'];
                    $pdf_tab [$a] ['EINHEIT_ID'] = $my_arr [$a] ['EINHEIT_ID'];
                    $pdf_tab [$a] ['ANSCHRIFT'] = $my_arr [$a] ['ANSCHRIFT'];
                    $pdf_tab [$a] ['EIGENTUEMER_ID'] = $my_arr [$a] ['EIGENTUEMER_ID'];
                    $pdf_tab [$a] ['EMAILS'] = array_unique($my_arr [$a] ['EMAILS']);
                    unset ($emails);

                    $pdf_tab [$a] ['EINHEIT_QM'] = $my_arr [$a] ['EINHEIT_QM'];
                    $pdf_tab [$a] ['EINHEIT_QM_A'] = nummer_punkt2komma($my_arr [$a] ['EINHEIT_QM']);
                    $pdf_tab [$a] ['WEG-FLAECHE'] = $my_arr [$a] ['WEG-FLAECHE'];
                    $pdf_tab [$a] ['WEG-FLAECHE_A'] = $my_arr [$a] ['WEG-FLAECHE_A'];
                    $pdf_tab [$a] ['AUSZAHLUNG_ET'] = $my_arr [$a] ['AUSZAHLUNG_ET'];
                    $pdf_tab [$a] ['MIETER'] = $my_arr [$a] ['MIETER'];
                    /* Garantiemiete */
                    $pdf_tab [$a] ['NETTO_SOLL_G_A'] = $my_arr [$a] ['NETTO_SOLL_G_A'];

                    $pdf_tab [$a] ['BRUTTO_SOLL'] = $my_arr [$a] ['BRUTTO_SOLL'];
                    $pdf_tab [$a] ['BRUTTO_SOLL_A'] = nummer_punkt2komma_t($my_arr [$a] ['BRUTTO_SOLL']);
                    $pdf_tab [$a] ['BRUTTO_IST'] = $my_arr [$a] ['BRUTTO_IST'];
                    $pdf_tab [$a] ['BRUTTO_IST_A'] = nummer_punkt2komma_t($my_arr [$a] ['BRUTTO_IST']);
                    $pdf_tab [$a] ['DIFF'] = $my_arr [$a] ['BRUTTO_IST'] - $pdf_tab [$a] ['BRUTTO_SOLL'];
                    $pdf_tab [$a] ['DIFF_A'] = nummer_punkt2komma_t($my_arr [$a] ['BRUTTO_IST'] - $pdf_tab [$a] ['BRUTTO_SOLL']);

                    $pdf_tab [$a] ['NETTO_SOLL'] = $my_arr [$a] ['NETTO_SOLL'];
                    $pdf_tab [$a] ['NETTO_SOLL_MV'] = $my_arr [$a] ['NETTO_SOLL_MV'];
                    $pdf_tab [$a] ['NETTO_SOLL_DIFF'] = $pdf_tab [$a] ['NETTO_SOLL'] - $pdf_tab [$a] ['NETTO_SOLL_MV'];
                    $pdf_tab [$a] ['NETTO_SOLL_A'] = nummer_punkt2komma_t($my_arr [$a] ['NETTO_SOLL']);
                    $pdf_tab [$a] ['ABGABE_IHR'] = $my_arr [$a] ['ABGABE_IHR'];

                    $weg1 = new weg ();
                    $vg = $weg1->get_summe_kostenkat_monat($monat, $jahr, 'Einheit', $einheit_id, '6060');
                    if (!empty ($vg)) {
                        $pdf_tab [$a] ['ABGABE_HV'] = -$vg; // Verwaltergebühr
                        $pdf_tab [$a] ['ABGABE_HV_A'] = nummer_punkt2komma(-$vg); // Verwaltergebühr
                    } else {
                        $pdf_tab [$a] ['ABGABE_HV'] = '-30.00';
                        $pdf_tab [$a] ['ABGABE_HV_A'] = '-30,00';
                    }

                    $weg1 = new weg ();
                    $ihr_hg = $weg1->get_summe_kostenkat_monat($monat, $jahr, 'Einheit', $einheit_id, '6030');

                    if (!empty ($ihr_hg)) {
                        $pdf_tab [$a] ['ABGABE_IHR'] = -$ihr_hg;
                    } else {
                        $pdf_tab [$a] ['ABGABE_IHR'] = $pdf_tab [$a] ['WEG-FLAECHE'] * -0.4;
                    }
                    $pdf_tab [$a] ['ABGABE_IHR_A'] = nummer_punkt2komma($pdf_tab [$a] ['ABGABE_IHR']);

                    $pdf_tab [$a] ['ZWISCHENSUMME'] = $my_arr [$a] ['NETTO_SOLL'] + $pdf_tab [$a] ['ABGABE_IHR'] + $pdf_tab [$a] ['ABGABE_HV'];

                    $pdf_tab [$a] ['ZWISCHENSUMME_A'] = nummer_punkt2komma($my_arr [$a] ['NETTO_SOLL'] + $pdf_tab [$a] ['ABGABE_IHR'] + $pdf_tab [$a] ['ABGABE_HV']);

                    /* Andere Kosten Summieren */
                    // ############################
                    $anz_kk = count($my_arr [$a] ['5500']);
                    if ($anz_kk > 1) {
                        $summe_rep += $my_arr [$a] ['5500'] [$anz_kk - 1] ['BETRAG'];
                    }

                    $anz_kk = count($my_arr [$a] ['4180']);
                    if ($anz_kk > 1) {
                        $summe_rep += $my_arr [$a] ['4180'] [$anz_kk - 1] ['BETRAG'];
                    }

                    $anz_kk = count($my_arr [$a] ['4280']);
                    if ($anz_kk > 1) {
                        $summe_rep += $my_arr [$a] ['4280'] [$anz_kk - 1] ['BETRAG'];
                    }

                    $anz_kk = count($my_arr [$a] ['4281']);
                    if ($anz_kk > 1) {
                        $summe_rep += $my_arr [$a] ['4281'] [$anz_kk - 1] ['BETRAG'];
                    }
                    $anz_kk = count($my_arr [$a] ['4282']);
                    if ($anz_kk > 1) {
                        $summe_rep += $my_arr [$a] ['4282'] [$anz_kk - 1] ['BETRAG'];
                    }

                    $anz_kk = count($my_arr [$a] ['5081']);
                    if ($anz_kk > 1) {
                        $summe_rep += $my_arr [$a] ['5081'] [$anz_kk - 1] ['BETRAG'];
                    }

                    $pdf_tab [$a] ['SUMME_REP'] = $summe_rep;
                    $pdf_tab [$a] ['SUMME_REP_A'] = nummer_punkt2komma($summe_rep);
                    $pdf_tab [$a] ['ENDSUMME'] = $pdf_tab [$a] ['ZWISCHENSUMME'];
                    $pdf_tab [$a] ['ENDSUMME_A'] = $pdf_tab [$a] ['ZWISCHENSUMME_A'];

                    $e_nam = $pdf_tab [$a] ['EIGENTUEMER_NAMEN'];
                    $ein_nam = $pdf_tab [$a] ['EINHEIT_KURZNAME'];

                    /* Übersichtstabelle */
                    $uebersicht [$a] ['EINHEIT_KURZNAME'] = $ein_nam;
                    $uebersicht [$a] ['EIGENTUEMER_NAMEN'] = $e_nam;
                    $uebersicht [$a] ['MIETER'] = $pdf_tab [$a] ['MIETER'];
                    $uebersicht [$a] ['MIETER_SALDO'] = $pdf_tab [$a] ['MIETER_SALDO'];
                    $uebersicht [$a] ['MIETER_SALDO_VM'] = $pdf_tab [$a] ['MIETER_SALDO_VM'];
                    $uebersicht [$a] ['MIETER_SALDO_A'] = nummer_punkt2komma($pdf_tab [$a] ['MIETER_SALDO']);
                    $uebersicht [$a] ['NETTO_SOLL_G_A'] = $pdf_tab [$a] ['NETTO_SOLL_G_A'];
                    $uebersicht [$a] ['NETTO_SOLL_A'] = nummer_punkt2komma($pdf_tab [$a] ['NETTO_SOLL_MV']);
                    $uebersicht [$a] ['NETTO_SOLL_DIFF_A'] = nummer_punkt2komma($pdf_tab [$a] ['NETTO_SOLL_DIFF']);
                    $uebersicht [$a] ['ABGABE_HV_A'] = $pdf_tab [$a] ['ABGABE_HV_A'];
                    $uebersicht [$a] ['ABGABE_IHR'] = $pdf_tab [$a] ['ABGABE_IHR_A'];

                    $uebersicht [$a] ['SUMME_REP'] = $pdf_tab [$a] ['SUMME_REP'];
                    $uebersicht [$a] ['SUMME_REP_A'] = $pdf_tab [$a] ['SUMME_REP_A'];

                    $uebersicht [$a] ['WEG-FLAECHE_A'] = $pdf_tab [$a] ['WEG-FLAECHE_A'];
                    $uebersicht [$a] ['EINHEIT_QM_A'] = $pdf_tab [$a] ['EINHEIT_QM_A'];

                    $uebersicht [$a] ['ENDSUMME_A'] = $pdf_tab [$a] ['ENDSUMME_A'];
                    $uebersicht [$a] ['TRANSFER'] = $pdf_tab [$a] ['ENDSUMME'] + $pdf_tab [$a] ['SUMME_REP'];
                    $uebersicht [$a] ['TRANSFER_A'] = nummer_punkt2komma_t($pdf_tab [$a] ['ENDSUMME'] + $pdf_tab [$a] ['SUMME_REP']);

                    $summe_transfer += $uebersicht [$a] ['TRANSFER'];

                    if ($pdf_tab [$a] ['ENDSUMME'] > 0) {
                        $summe_alle_eigentuemer += $pdf_tab [$a] ['ENDSUMME'];
                    } else {
                        $summe_nachzahler += $pdf_tab [$a] ['ENDSUMME'];
                    }

                    if (request()->has('w_monat')) {
                        $w_monat = request()->input('w_monat');
                    } else {
                        $w_monat = $monat;
                    }

                    if (request()->has('w_jahr')) {
                        $w_jahr = request()->input('w_jahr');
                    } else {
                        $w_jahr = $jahr;
                    }

                    if ($lang == 'en') {
                        if (is_array($pdf_tab [$a] ['EMAILS'])) {

                            $anzemail = count($pdf_tab [$a] ['EMAILS']);
                            $pdf->setColor(255, 255, 255, 255); // Weiss
                            for ($em = 0; $em < $anzemail; $em++) {
                                $akt_seite = $pdf->ezOutput();
                                $email = $pdf_tab [$a] ['EMAILS'] [$em];
                                $pdf->ezText("$email ", 10);
                                $pdf->ezSetDy(9); // abstand
                            }
                            $pdf->setColor(0, 0, 0, 1); // schwarz
                        }
                        $anschrift = $pdf_tab [$a] ['ANSCHRIFT'];
                        $cols = array(
                            'EIGENTUEMER_NAMEN' => "<b>owner</b>",
                            'EINHEIT_KURZNAME' => "<b>apart.No</b>",
                            'MIETER' => "<b>tenant</b>",
                            'WEG-FLAECHE_A' => "<b>size [m²]</b>",
                            'NETTO_SOLL_A' => "<b>rent [€]</b>",
                            'ABGABE_IHR_A' => "<b>for maint. [€]</b>",
                            'ABGABE_HV_A' => "<b>mng. fee [€]</b>",
                            'ENDSUMME_A' => "<b>Amount [€]</b>"
                        );
                        $pdf->ezTable($pdf_tab, $cols, "<b>Monthly report $w_monat/$w_jahr    $ein_nam, $anschrift</b>", array(
                            'showHeadings' => 1,
                            'shaded' => 1,
                            'titleFontSize' => 8,
                            'fontSize' => 7,
                            'xPos' => 50,
                            'xOrientation' => 'right',
                            'width' => 500,
                            'cols' => array(
                                'ENDSUMME_A' => array(
                                    'justification' => 'right',
                                    'width' => 50
                                ),
                                'EIGENTUEMER_NAMEN' => array(
                                    'justification' => 'left',
                                    'width' => 50
                                )
                            )
                        ));
                    } else {
                        $anschrift = $pdf_tab [$a] ['ANSCHRIFT'];
                        $cols = array(
                            'EIGENTUEMER_NAMEN' => "<b>Eigentümer</b>",
                            'EINHEIT_KURZNAME' => "<b>apart.No</b>",
                            'MIETER' => "<b>tenant</b>",
                            'WEG-FLAECHE_A' => "<b>size [m²]</b>",
                            'NETTO_SOLL_A' => "<b>rent [€]</b>",
                            'ABGABE_IHR_A' => "<b>for maint. [€]</b>",
                            'ABGABE_HV_A' => "<b>mng. fee [€]</b>",
                            'ENDSUMME_A' => "<b>Amount [€]</b>"
                        );
                        $pdf->ezTable($pdf_tab, $cols, "<b>$monat_name $jahr - Gesamtübersicht - $ein_nam, $anschrift</b>", array(
                            'showHeadings' => 1,
                            'shaded' => 1,
                            'titleFontSize' => 8,
                            'fontSize' => 7,
                            'xPos' => 30,
                            'xOrientation' => 'right',
                            'width' => 550,
                            'cols' => array(
                                'ENDSUMME_A' => array(
                                    'justification' => 'right',
                                    'width' => 50
                                ),
                                'EIGENTUEMER_NAMEN' => array(
                                    'justification' => 'left',
                                    'width' => 50
                                )
                            )
                        ));
                    }

                    $pdf->ezSetDy(-10); // abstand
                    if (is_array($my_arr [$a] ['AUSGABEN'])) {
                        // $anzaa = count($my_arr[$a]['AUSGABEN']);
                        array_unshift($my_arr [$a] ['AUSGABEN'], array(
                            'DATUM' => ' '
                        ));
                        array_unshift($my_arr [$a] ['AUSGABEN'], array(
                            'DATUM' => ' '
                        ));

                        if ($lang == 'en') {
                            $cols = array(
                                'DATUM' => "<b>Date</b>",
                                'VERWENDUNGSZWECK' => "<b>Description</b>",
                                'BETRAG' => "<b>Amount [€]</b>"
                            );
                            $pdf->ezTable($my_arr [$a] ['AUSGABEN'], $cols, "<b>Maintenance bills | cost account: [1023]</b>", array(
                                'showHeadings' => 1,
                                'shaded' => 1,
                                'titleFontSize' => 8,
                                'fontSize' => 7,
                                'xPos' => 50,
                                'xOrientation' => 'right',
                                'width' => 500,
                                'cols' => array(
                                    'BETRAG' => array(
                                        'justification' => 'right',
                                        'width' => 65
                                    ),
                                    'DATUM' => array(
                                        'justification' => 'left',
                                        'width' => 50
                                    )
                                )
                            ));

                            if (is_array($my_arr [$a] ['5500']) && count($my_arr [$a] ['5500']) > 1) {
                                $pdf->ezSetDy(-10); // abstand
                                $pdf->ezTable($my_arr [$a] ['5500'], $cols, "<b>broker fee | cost account: [5500]</b>", array(
                                    'showHeadings' => 1,
                                    'shaded' => 1,
                                    'titleFontSize' => 8,
                                    'fontSize' => 7,
                                    'xPos' => 50,
                                    'xOrientation' => 'right',
                                    'width' => 500,
                                    'cols' => array(
                                        'BETRAG' => array(
                                            'justification' => 'right',
                                            'width' => 65
                                        ),
                                        'DATUM' => array(
                                            'justification' => 'left',
                                            'width' => 50
                                        )
                                    )
                                ));
                            }

                            if (is_array($my_arr [$a] ['4180']) && count($my_arr [$a] ['4180']) > 1) {
                                $pdf->ezSetDy(-10); // abstand
                                $pdf->ezTable($my_arr [$a] ['4180'], $cols, "<b>allowed rent increase | cost account: [4180]</b>", array(
                                    'showHeadings' => 1,
                                    'shaded' => 1,
                                    'titleFontSize' => 8,
                                    'fontSize' => 7,
                                    'xPos' => 50,
                                    'xOrientation' => 'right',
                                    'width' => 500,
                                    'cols' => array(
                                        'BETRAG' => array(
                                            'justification' => 'right',
                                            'width' => 65
                                        ),
                                        'DATUM' => array(
                                            'justification' => 'left',
                                            'width' => 50
                                        )
                                    )
                                ));
                            }
                            if (is_array($my_arr [$a] ['4280']) && count($my_arr [$a] ['4280']) > 1) {
                                $pdf->ezSetDy(-10); // abstand
                                $pdf->ezTable($my_arr [$a] ['4280'], $cols, "<b>court fees | cost account: [4280]</b>", array(
                                    'showHeadings' => 1,
                                    'shaded' => 1,
                                    'titleFontSize' => 8,
                                    'fontSize' => 7,
                                    'xPos' => 50,
                                    'xOrientation' => 'right',
                                    'width' => 500,
                                    'cols' => array(
                                        'BETRAG' => array(
                                            'justification' => 'right',
                                            'width' => 65
                                        ),
                                        'DATUM' => array(
                                            'justification' => 'left',
                                            'width' => 50
                                        )
                                    )
                                ));
                            }
                            if (is_array($my_arr [$a] ['4281']) && count($my_arr [$a] ['4281']) > 1) {
                                $pdf->ezSetDy(-10); // abstand
                                $pdf->ezTable($my_arr [$a] ['4281'], $cols, "<b>payment for lawyer | cost account: [4281]</b>", array(
                                    'showHeadings' => 1,
                                    'shaded' => 1,
                                    'titleFontSize' => 8,
                                    'fontSize' => 7,
                                    'xPos' => 50,
                                    'xOrientation' => 'right',
                                    'width' => 500,
                                    'cols' => array(
                                        'BETRAG' => array(
                                            'justification' => 'right',
                                            'width' => 65
                                        ),
                                        'DATUM' => array(
                                            'justification' => 'left',
                                            'width' => 50
                                        )
                                    )
                                ));
                            }
                            if (is_array($my_arr [$a] ['4282']) && count($my_arr [$a] ['4282']) > 1) {
                                $pdf->ezSetDy(-10); // abstand
                                $pdf->ezTable($my_arr [$a] ['4282'], $cols, "<b>payment for marshal | cost account: [4282]</b>", array(
                                    'showHeadings' => 1,
                                    'shaded' => 1,
                                    'titleFontSize' => 8,
                                    'fontSize' => 7,
                                    'xPos' => 50,
                                    'xOrientation' => 'right',
                                    'width' => 500,
                                    'cols' => array(
                                        'BETRAG' => array(
                                            'justification' => 'right',
                                            'width' => 65
                                        ),
                                        'DATUM' => array(
                                            'justification' => 'left',
                                            'width' => 50
                                        )
                                    )
                                ));
                            }

                            if (is_array($my_arr [$a] ['5081']) && count($my_arr [$a] ['5081']) > 1) {
                                $pdf->ezSetDy(-10); // abstand
                                $pdf->ezTable($my_arr [$a] ['5081'], $cols, "<b>credit repayment | cost account: [5081]</b>", array(
                                    'showHeadings' => 1,
                                    'shaded' => 1,
                                    'titleFontSize' => 8,
                                    'fontSize' => 7,
                                    'xPos' => 50,
                                    'xOrientation' => 'right',
                                    'width' => 500,
                                    'cols' => array(
                                        'BETRAG' => array(
                                            'justification' => 'right',
                                            'width' => 65
                                        ),
                                        'DATUM' => array(
                                            'justification' => 'left',
                                            'width' => 50
                                        )
                                    )
                                ));
                            }

                        } else {
                            $cols = array(
                                'DATUM' => "Datum",
                                'VERWENDUNGSZWECK' => "Buchungstext",
                                'BETRAG' => "Betrag"
                            );
                            $pdf->ezTable($my_arr [$a] ['AUSGABEN'], $cols, "<b>$monat_name $jahr - Reparaturen 1023 - $ein_nam</b>", array(
                                'showHeadings' => 1,
                                'shaded' => 1,
                                'titleFontSize' => 8,
                                'fontSize' => 7,
                                'xPos' => 50,
                                'xOrientation' => 'right',
                                'width' => 500,
                                'cols' => array(
                                    'BETRAG' => array(
                                        'justification' => 'right',
                                        'width' => 65
                                    ),
                                    'DATUM' => array(
                                        'justification' => 'left',
                                        'width' => 50
                                    )
                                )
                            ));
                        }
                    } else {
                        $pdf->ezText("Keine Reparaturen", 12);
                    }
                    $pdf->ezSetDy(-20); // abstand
                    $trans_tab [0] ['TEXT'] = "Amount [€]";
                    $trans_tab [0] ['AM'] = $uebersicht [$a] ['ENDSUMME_A'];
                    $trans_tab [1] ['TEXT'] = "Bills [€]";
                    $trans_tab [1] ['AM'] = $uebersicht [$a] ['SUMME_REP_A'];
                    $trans_tab [2] ['TEXT'] = "<b>To transfer [€]</b>";
                    if ($uebersicht [$a] ['TRANSFER'] > 0) {
                        $trans_tab [2] ['TEXT'] = "<b>To transfer [€]</b>";
                        $trans_tab [2] ['AM'] = "<b>" . $uebersicht [$a] ['TRANSFER_A'] . "</b>";
                    } else {
                        $trans_tab [2] ['TEXT'] = "<b>Summary [€]</b>";
                        $trans_tab [2] ['AM'] = "<b>" . $uebersicht [$a] ['TRANSFER_A'] . "</b>";
                        $trans_tab [3] ['TEXT'] = "<b>To transfer [€]</b>";
                        $trans_tab [3] ['AM'] = "<b>0,00</b>";
                    }

                    $cols = array(
                        'TEXT' => "",
                        'AM' => ""
                    );
                    $pdf->ezTable($trans_tab, $cols, "<b>Summary $w_monat/$jahr</b>", array(
                        'showHeadings' => 0,
                        'shaded' => 1,
                        'titleFontSize' => 8,
                        'fontSize' => 7,
                        'xPos' => 235,
                        'xOrientation' => 'right',
                        'width' => 500,
                        'cols' => array(
                            'TEXT' => array(
                                'justification' => 'right',
                                'width' => 250
                            ),
                            'AM' => array(
                                'justification' => 'right',
                                'width' => 65
                            )
                        )
                    ));
                    $cols = array(
                        'DATUM' => "<b>Date</b>",
                        'VERWENDUNGSZWECK' => "<b>Description</b>",
                        'BETRAG' => "<b>Amount [€]</b>"
                    );

                    $pdf->options [] = array(
                        'textCol' => array(
                            1,
                            0,
                            0
                        )
                    );
                    if (is_array($my_arr [$a] ['5010']) && count($my_arr [$a] ['5010']) > 1) {
                        $anz_aus = count($my_arr [$a] ['5010']);

                        for ($aaa = 0; $aaa < $anz_aus; $aaa++) {
                            if ($aaa == $anz_aus - 1) {
                                $bbbb = $my_arr [$a] ['5010'] [$aaa] ['BETRAG'];
                                $my_arr [$a] ['5010'] [$aaa] ['BETRAG'] = "<b>$bbbb</b>";
                            } else {
                                $my_arr [$a] ['5010'] [$aaa] ['BETRAG'] = $my_arr [$a] ['5010'] [$aaa] ['BETRAG'];
                            }
                        }

                        $pdf->ezSetDy(-10); // abstand
                        $pdf->ezTable($my_arr [$a] ['5010'], $cols, "<b>Actual transfer income | cost account: [5010]</b>", array(
                            'showHeadings' => 1,
                            'shaded' => 1,
                            'titleFontSize' => 8,
                            'fontSize' => 7,
                            'xPos' => 50,
                            'xOrientation' => 'right',
                            'width' => 500,
                            'cols' => array(
                                'BETRAG' => array(
                                    'justification' => 'right',
                                    'width' => 65
                                ),
                                'DATUM' => array(
                                    'justification' => 'left',
                                    'width' => 50
                                )
                            )
                        ));
                    }

                    $pdf->ezSetDy(-10); // abstand

                    if (is_array($my_arr [$a] ['5020']) && count($my_arr [$a] ['5020']) > 1) {
                        $anz_aus = count($my_arr [$a] ['5020']);

                        for ($aaa = 0; $aaa < $anz_aus; $aaa++) {
                            if ($aaa == $anz_aus - 1) {
                                $bbbb = $my_arr [$a] ['5020'] [$aaa] ['BETRAG'] * -1; // POSITIVIEREN
                                $my_arr [$a] ['5020'] [$aaa] ['BETRAG'] = "<b>$bbbb</b>";
                            } else {
                                $my_arr [$a] ['5020'] [$aaa] ['BETRAG'] = $my_arr [$a] ['5020'] [$aaa] ['BETRAG'] * -1; // POSITIVIEREN
                            }
                        }
                    } else {
                        $my_arr [$a] ['5020'] [0] ['BETRAG'] = "<b>0.00</b>";
                        $my_arr [$a] ['5020'] [0] ['VERWENDUNGSZWECK'] = "<b>No transfer</b>";
                    }

                    $pdf->ezTable($my_arr [$a] ['5020'], $cols, "<b>Actual transfer | cost account: [5020]</b>", array(
                        'showHeadings' => 1,
                        'shaded' => 1,
                        'titleFontSize' => 8,
                        'fontSize' => 7,
                        'xPos' => 50,
                        'xOrientation' => 'right',
                        'width' => 500,
                        'cols' => array(
                            'BETRAG' => array(
                                'justification' => 'right',
                                'width' => 65
                            ),
                            'DATUM' => array(
                                'justification' => 'left',
                                'width' => 50
                            )
                        )
                    ));

                    unset ($trans_tab);

                    if (is_array($my_arr [$a] ['AUSZAHLUNG_ET'])) {

                        if ($lang == 'en') {
                            // $cols = array('DATUM'=>"Date", 'VERWENDUNGSZWECK'=>"Description", 'BETRAG'=>"Amount");
                            // $pdf->ezTable($my_arr[$a]['AUSZAHLUNG_ET'], $cols, "<b>$monat_name $jahr - transfer 5020 - $ein_nam</b>", array('showHeadings'=>1,'shaded'=>1, 'titleFontSize' => 8, 'fontSize' => 7, 'xPos'=>50,'xOrientation'=>'right', 'width'=>500, 'cols'=>array('BETRAG'=>array('justification'=>'right', 'width'=>65),'DATUM'=>array('justification'=>'left', 'width'=>50))));
                        } else {
                            // $cols = array('DATUM'=>"Datum", 'VERWENDUNGSZWECK'=>"Buchungstext", 'BETRAG'=>"Betrag");
                            // $pdf->ezTable($my_arr[$a]['AUSZAHLUNG_ET'], $cols, "<b>$monat_name $jahr - Überweisung 80001 - $ein_nam</b>", array('showHeadings'=>1,'shaded'=>1, 'titleFontSize' => 8, 'fontSize' => 7, 'xPos'=>50,'xOrientation'=>'right', 'width'=>500, 'cols'=>array('BETRAG'=>array('justification'=>'right', 'width'=>65),'DATUM'=>array('justification'=>'left', 'width'=>50))));
                        }
                    }
                    $pdf->ezNewPage();

                    $my_arr [$a] ['ABGABE_IHR'] = $pdf_tab [$a] ['ABGABE_IHR'];
                    unset ($pdf_tab);
                }
            }
            $uebersicht [$anz] ['EIGENTUEMER_NAMEN'] = 'Soll';
            $uebersicht [$anz] ['ENDSUMME_A'] = nummer_punkt2komma_t($summe_alle_eigentuemer);
            $uebersicht [$anz + 1] ['EIGENTUEMER_NAMEN'] = 'Auszahlen';
            $uebersicht [$anz + 1] ['TRANSFER_A'] = nummer_punkt2komma_t($summe_transfer);

            if ($lang == 'en') {
                $cols = array(
                    'EINHEIT_KURZNAME' => "Apt",
                    'WEG-FLAECHE_A' => 'm²',
                    'EIGENTUEMER_NAMEN' => "Own",
                    'MIETER' => "Tenant",
                    'MIETER_SALDO_VM' => 'VM',
                    'MIETER_SALDO_A' => 'current',
                    'NETTO_SOLL_G_A' => "Garanty",
                    'NETTO_SOLL_A' => "rent",
                    'NETTO_SOLL_DIFF_A' => "diff",
                    'ABGABE_HV_A' => "fee",
                    'ABGABE_IHR' => "for maint.",
                    'ENDSUMME_A' => "Amount",
                    'SUMME_REP_A' => 'Rep.',
                    'TRANSFER_A' => 'transfer'
                );
            } else {
                $cols = array(
                    'EINHEIT_KURZNAME' => "Apt",
                    'WEG-FLAECHE_A' => 'm²',
                    'EIGENTUEMER_NAMEN' => "Own",
                    'MIETER' => "Tenant",
                    'MIETER_SALDO_A' => 'current',
                    'NETTO_SOLL_G_A' => "Garanty",
                    'NETTO_SOLL_A' => "rent",
                    'NETTO_SOLL_DIFF_A' => "diff",
                    'ABGABE_HV_A' => "fee",
                    'ABGABE_IHR' => "for maint.",
                    'ENDSUMME_A' => "Amount",
                    'SUMME_REP_A' => 'Rep.',
                    'TRANSFER_A' => 'transfer'
                );
            }
            $von_d = date_mysql2german($von);
            $bis_d = date_mysql2german($bis);
            $pdf->ezText("<b>Kostenabfrage von: $von_d bis: $bis_d</b>", 12);
            $pdf->ezSetDy(-20); // abstand
            $pdf->ezTable($uebersicht, $cols, null, array(
                'showHeadings' => 1,
                'shaded' => 1,
                'titleFontSize' => 8,
                'fontSize' => 7,
                'xPos' => 10,
                'xOrientation' => 'right',
                'width' => 550,
                'cols' => array(
                    'EINHEIT_KURZNAME' => array(
                        'justification' => 'left',
                        'width' => '40'
                    ),
                    'EIGENTUEMER_NAME' => array(
                        'justification' => 'right',
                        'width' => '40'
                    ),
                    'MIETER' => array(
                        'justification' => 'right',
                        'width' => '60'
                    )
                )
            ));
            echo '<pre>';

            $anz_m = count($my_arr);
            $z = 0;
            for ($mm = 0; $mm < $anz_m; $mm++) {
                $saldo_m_et = 0;
                $einheit_kn = $my_arr [$mm] ['EINHEIT_KURZNAME'];

                /* Soll Miete */

                /* Ausgaben 1023 */
                if (is_array($my_arr [$mm] ['AUSGABEN'])) {

                    $anz_ab = count($my_arr [$mm] ['AUSGABEN']);
                    for ($ab = 0; $ab < $anz_ab; $ab++) {
                        if (isset ($my_arr [$mm] ['AUSGABEN'] [$ab] ['GELD_KONTO_BUCHUNGEN_DAT'])) {

                            $konto = $my_arr [$mm] ['AUSGABEN'] [$ab] ['KONTENRAHMEN_KONTO'];
                            $vzweck = $my_arr [$mm] ['AUSGABEN'] [$ab] ['VERWENDUNGSZWECK'];

                            $et_ue_tab [$einheit_kn] [$z] ['TXT'] = "Repairs $konto";
                            $et_ue_tab [$einheit_kn] [$z] ['BEZ'] = $vzweck;
                            $et_ue_tab [$einheit_kn] [$z] ['BET'] = $my_arr [$mm] ['AUSGABEN'] [$ab] ['BETRAG'];
                            $et_ue_tab [$einheit_kn] [$z] ['DATUM'] = $my_arr [$mm] ['AUSGABEN'] [$ab] ['DATUM'];
                            $saldo_m_et += $et_ue_tab [$einheit_kn] [$z] ['BET'];
                            $z++;
                        }
                    }
                }

                /* Ausgaben 5500 */
                if (is_array($my_arr [$mm] ['5500'])) {

                    $anz_ab = count($my_arr [$mm] ['5500']);
                    for ($ab = 0; $ab < $anz_ab; $ab++) {
                        if (isset ($my_arr [$mm] ['5500'] [$ab] ['GELD_KONTO_BUCHUNGEN_DAT'])) {
                            $vzweck = $my_arr [$mm] ['5500'] [$ab] ['VERWENDUNGSZWECK'];

                            $et_ue_tab [$einheit_kn] [$z] ['TXT'] = "Konto 5500";
                            $et_ue_tab [$einheit_kn] [$z] ['BEZ'] = $vzweck;
                            $et_ue_tab [$einheit_kn] [$z] ['BET'] = $my_arr [$mm] ['5500'] [$ab] ['BETRAG'];
                            $et_ue_tab [$einheit_kn] [$z] ['DATUM'] = $my_arr [$mm] ['5500'] [$ab] ['DATUM'];
                            $saldo_m_et += $et_ue_tab [$einheit_kn] [$z] ['BET'];
                            $z++;
                        }
                    }
                }

                /* Ausgaben 4180 */
                if (is_array($my_arr [$mm] ['4180'])) {

                    $anz_ab = count($my_arr [$mm] ['4180']);
                    for ($ab = 0; $ab < $anz_ab; $ab++) {
                        if (isset ($my_arr [$mm] ['4180'] [$ab] ['GELD_KONTO_BUCHUNGEN_DAT'])) {
                            $vzweck = $my_arr [$mm] ['4180'] [$ab] ['VERWENDUNGSZWECK'];

                            $et_ue_tab [$einheit_kn] [$z] ['TXT'] = "Konto 4180";
                            $et_ue_tab [$einheit_kn] [$z] ['BEZ'] = $vzweck;
                            $et_ue_tab [$einheit_kn] [$z] ['BET'] = $my_arr [$mm] ['4180'] [$ab] ['BETRAG'];
                            $et_ue_tab [$einheit_kn] [$z] ['DATUM'] = $my_arr [$mm] ['4180'] [$ab] ['DATUM'];
                            $saldo_m_et += $et_ue_tab [$einheit_kn] [$z] ['BET'];
                            $z++;
                        }
                    }
                }

                /* Ausgaben 4280 */
                if (is_array($my_arr [$mm] ['4280'])) {

                    $anz_ab = count($my_arr [$mm] ['4280']);
                    for ($ab = 0; $ab < $anz_ab; $ab++) {
                        if (isset ($my_arr [$mm] ['4280'] [$ab] ['GELD_KONTO_BUCHUNGEN_DAT'])) {
                            $vzweck = $my_arr [$mm] ['4280'] [$ab] ['VERWENDUNGSZWECK'];

                            $et_ue_tab [$einheit_kn] [$z] ['TXT'] = "Konto 4280";
                            $et_ue_tab [$einheit_kn] [$z] ['BEZ'] = $vzweck;
                            $et_ue_tab [$einheit_kn] [$z] ['BET'] = $my_arr [$mm] ['4280'] [$ab] ['BETRAG'];
                            $et_ue_tab [$einheit_kn] [$z] ['DATUM'] = $my_arr [$mm] ['4280'] [$ab] ['DATUM'];
                            $saldo_m_et += $et_ue_tab [$einheit_kn] [$z] ['BET'];
                            $z++;
                        }
                    }
                }

                /* Ausgaben 4281 */
                if (is_array($my_arr [$mm] ['4281'])) {

                    $anz_ab = count($my_arr [$mm] ['4281']);
                    for ($ab = 0; $ab < $anz_ab; $ab++) {
                        if (isset ($my_arr [$mm] ['4281'] [$ab] ['GELD_KONTO_BUCHUNGEN_DAT'])) {
                            $vzweck = $my_arr [$mm] ['4281'] [$ab] ['VERWENDUNGSZWECK'];

                            $et_ue_tab [$einheit_kn] [$z] ['TXT'] = "Konto 4281";
                            $et_ue_tab [$einheit_kn] [$z] ['BEZ'] = $vzweck;
                            $et_ue_tab [$einheit_kn] [$z] ['BET'] = $my_arr [$mm] ['4281'] [$ab] ['BETRAG'];
                            $et_ue_tab [$einheit_kn] [$z] ['DATUM'] = $my_arr [$mm] ['4281'] [$ab] ['DATUM'];
                            $saldo_m_et += $et_ue_tab [$einheit_kn] [$z] ['BET'];
                            $z++;
                        }
                    }
                }

                /* Ausgaben 4282 */
                if (is_array($my_arr [$mm] ['4282'])) {

                    $anz_ab = count($my_arr [$mm] ['4282']);
                    for ($ab = 0; $ab < $anz_ab; $ab++) {
                        if (isset ($my_arr [$mm] ['4282'] [$ab] ['GELD_KONTO_BUCHUNGEN_DAT'])) {
                            $vzweck = $my_arr [$mm] ['4282'] [$ab] ['VERWENDUNGSZWECK'];

                            $et_ue_tab [$einheit_kn] [$z] ['TXT'] = "Konto 4282";
                            $et_ue_tab [$einheit_kn] [$z] ['BEZ'] = $vzweck;
                            $et_ue_tab [$einheit_kn] [$z] ['BET'] = $my_arr [$mm] ['4282'] [$ab] ['BETRAG'];
                            $et_ue_tab [$einheit_kn] [$z] ['DATUM'] = $my_arr [$mm] ['4282'] [$ab] ['DATUM'];
                            $saldo_m_et += $et_ue_tab [$einheit_kn] [$z] ['BET'];
                            $z++;
                        }
                    }
                }

                /* Ausgaben 5081 */
                if (is_array($my_arr [$mm] ['5081'])) {

                    $anz_ab = count($my_arr [$mm] ['5081']);
                    for ($ab = 0; $ab < $anz_ab; $ab++) {
                        if (isset ($my_arr [$mm] ['5081'] [$ab] ['GELD_KONTO_BUCHUNGEN_DAT'])) {
                            $vzweck = $my_arr [$mm] ['5081'] [$ab] ['VERWENDUNGSZWECK'];

                            $et_ue_tab [$einheit_kn] [$z] ['TXT'] = "Konto 5081";
                            $et_ue_tab [$einheit_kn] [$z] ['BEZ'] = $vzweck;
                            $et_ue_tab [$einheit_kn] [$z] ['BET'] = $my_arr [$mm] ['5081'] [$ab] ['BETRAG'];
                            $et_ue_tab [$einheit_kn] [$z] ['DATUM'] = $my_arr [$mm] ['5081'] [$ab] ['DATUM'];
                            $saldo_m_et += $et_ue_tab [$einheit_kn] [$z] ['BET'];
                            $z++;
                        }
                    }
                }

                /* Ausgaben 5010 */
                if (is_array($my_arr [$mm] ['5010'])) {

                    $anz_ab = count($my_arr [$mm] ['5010']);
                    for ($ab = 0; $ab < $anz_ab; $ab++) {
                        if (isset ($my_arr [$mm] ['5010'] [$ab] ['GELD_KONTO_BUCHUNGEN_DAT'])) {
                            $vzweck = $my_arr [$mm] ['5010'] [$ab] ['VERWENDUNGSZWECK'];

                            $et_ue_tab [$einheit_kn] [$z] ['TXT'] = "Konto 5010";
                            $et_ue_tab [$einheit_kn] [$z] ['BEZ'] = $vzweck;
                            $et_ue_tab [$einheit_kn] [$z] ['BET'] = $my_arr [$mm] ['5010'] [$ab] ['BETRAG'];
                            $et_ue_tab [$einheit_kn] [$z] ['DATUM'] = $my_arr [$mm] ['5010'] [$ab] ['DATUM'];
                            $saldo_m_et += $et_ue_tab [$einheit_kn] [$z] ['BET'];
                            $z++;
                        }
                    }
                }

                /* Ausgaben 5020 */
                if (is_array($my_arr [$mm] ['5020'])) {

                    $anz_ab = count($my_arr [$mm] ['5020']);
                    for ($ab = 0; $ab < $anz_ab; $ab++) {
                        if (isset ($my_arr [$mm] ['5020'] [$ab] ['GELD_KONTO_BUCHUNGEN_DAT'])) {
                            $vzweck = $my_arr [$mm] ['5020'] [$ab] ['VERWENDUNGSZWECK'];

                            $et_ue_tab [$einheit_kn] [$z] ['TXT'] = "Konto 5020";
                            $et_ue_tab [$einheit_kn] [$z] ['BEZ'] = $vzweck;
                            $et_ue_tab [$einheit_kn] [$z] ['BET'] = $my_arr [$mm] ['5020'] [$ab] ['BETRAG'] * -1;
                            $et_ue_tab [$einheit_kn] [$z] ['DATUM'] = $my_arr [$mm] ['5020'] [$ab] ['DATUM'];
                            $saldo_m_et += $et_ue_tab [$einheit_kn] [$z] ['BET'];
                            $z++;
                        }
                    }
                }

                /* Saldo */
                if ($saldo_m_et != 0) {
                    $et_ue_tab [$einheit_kn] [$z] ['TXT'] = 'SALDO';
                    $et_ue_tab [$einheit_kn] [$z] ['BEZ'] = 'SALDO';
                    $et_ue_tab [$einheit_kn] [$z] ['BET'] = nummer_komma2punkt(nummer_punkt2komma($saldo_m_et));
                }

                $z = 0;
            }

            $w_keys = array_unique(array_keys($et_ue_tab));
            $colss = array(
                'DATUM' => "Date",
                'TXT' => "Description",
                'BEZ' => "Description1",
                'BET' => "Amount"
            );
            $pdf->ezNewPage();
            for ($p = 0; $p < count($w_keys); $p++) {
                $wohnung = $w_keys [$p];
                $pdf->ezTable($et_ue_tab [$wohnung], $colss, "<b>$wohnung $monat/$jahr</b>", array(
                    'showHeadings' => 1,
                    'shaded' => 1,
                    'titleFontSize' => 8,
                    'fontSize' => 7,
                    'xPos' => 50,
                    'xOrientation' => 'right',
                    'width' => 500,
                    'cols' => array(
                        'DATUM' => array(
                            'justification' => 'left',
                            'width' => 50
                        ),
                        'TXT' => array(
                            'justification' => 'right',
                            'width' => 100
                        ),
                        'BEZ' => array(
                            'justification' => 'left'
                        ),
                        'BET' => array(
                            'justification' => 'right',
                            'width' => 60
                        )
                    )
                ));
                $pdf->ezSetDy(-10); // abstand
            }

            ob_end_clean(); // ausgabepuffer leeren
            $pdf->ezStream();
        } else {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\InfoMessage("Keine Einheiten im Objekt $objekt_id")
            );
        }
    }

    function inspiration_sepa_arr($ausgezogene = 0, $objekt_id, $monat, $jahr, $lang = 'de')
    {
        $gk = new geldkonto_info ();
        $gk->geld_konto_ermitteln('OBJEKT', $objekt_id);
        if (!$gk->geldkonto_id) {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\ErrorMessage('Geldkonto zum Objekt hinzufügen.')
            );
        }
        $db_abfrage = "SELECT OBJEKT_KURZNAME, HAUS_STRASSE, HAUS_NUMMER, `EINHEIT_KURZNAME` , `EINHEIT_ID`,  ltrim(rtrim(EINHEIT_LAGE)) AS EINHEIT_LAGE, `EINHEIT_QM` FROM EINHEIT , HAUS, OBJEKT
WHERE `EINHEIT_AKTUELL` = '1' && EINHEIT.HAUS_ID = HAUS.HAUS_ID && HAUS.OBJEKT_ID=OBJEKT.OBJEKT_ID && HAUS_AKTUELL='1' && OBJEKT_AKTUELL='1' && OBJEKT.OBJEKT_ID='$objekt_id' 
ORDER BY EINHEIT_KURZNAME";

        $result = DB::select($db_abfrage);
        $numrows = count($result);
        if ($numrows) {
            $z = 0;
            foreach ($result as $row) {
                $my_arr [] = $row;
                $einheit_id = $row ['EINHEIT_ID'];
                $einheit_qm = $row ['EINHEIT_QM'];
                $e = new einheit ();
                $det = new detail ();
                $my_arr [$z] ['WEG-FLAECHE_A'] = $det->finde_detail_inhalt('EINHEIT', $einheit_id, 'WEG-Fläche'); // kommt als Kommazahl
                if (empty ($my_arr [$z] ['WEG-FLAECHE_A'])) {
                    $my_arr [$z] ['WEG-FLAECHE_A'] = nummer_punkt2komma($einheit_qm);
                }
                $my_arr [$z] ['WEG-FLAECHE'] = nummer_komma2punkt($my_arr [$z] ['WEG-FLAECHE_A']);

                $my_arr [$z] ['WG_NR'] = $det->finde_detail_inhalt('EINHEIT', $einheit_id, 'Alte Nr'); // kommt als Kommazahl

                $weg = new weg ();
                $weg->get_last_eigentuemer($einheit_id);
                if (isset ($weg->eigentuemer_name)) {
                    $weg->get_eigentuemer_namen($weg->eigentuemer_id);
                    $my_arr [$z] ['EIGENTUEMER_NAMEN'] = $weg->eigentuemer_name_str_u;
                    $my_arr [$z] ['EIGENTUEMER_ID'] = $weg->eigentuemer_id;
                } else {
                    $my_arr [$z] ['EIGENTUEMER'] = 'Unbekannt';
                }
                $mv_id = $e->get_mietvertrag_id($einheit_id);
                if ($mv_id) {
                    $mvs = new mietvertraege ();
                    $mvs->get_mietvertrag_infos_aktuell($mv_id);
                    $kontaktdaten = $e->kontaktdaten_mieter($mv_id);
                    $my_arr [$z] ['MIETER'] = $mvs->personen_name_string_u;
                    $my_arr [$z] ['MIETVERTRAG_ID'] = $mv_id;
                    $my_arr [$z] ['KONTAKT'] = $kontaktdaten;
                    $my_arr [$z] ['EINHEIT_ID'] = $einheit_id;
                    $mz = new miete ();
                    $mz->mietkonto_berechnung($mv_id);
                    $my_arr [$z] ['MIETER_SALDO'] = $mz->erg;
                } else {
                    $my_arr [$z] ['MIETER'] = 'Leerstand';
                }
                $z++;
            }
            unset ($e);
            unset ($mvs);
            unset ($weg);

            $anz = count($my_arr);
            /* Berechnung Abgaben */
            for ($a = 0; $a < $anz; $a++) {
                $einheit_id = $my_arr [$a] ['EINHEIT_ID'];
                if (isset ($my_arr [$a] ['EIGENTUEMER_ID'])) {
                    $eige_id = $my_arr [$a] ['EIGENTUEMER_ID'];
                    $my_arr [$a] ['ABGABEN'] [] ['ABGABE_IHR'] = $my_arr [$a] ['WEG-FLAECHE'] * -0.4;
                    if (empty ($my_arr [$a] ['WEG-FLAECHE'])) {
                        $my_arr [$a] ['ABGABEN'] [] ['ABGABE_IHR'] = $einheit_qm * -0.4;
                    }
                    $my_arr [$a] ['ABGABEN'] [] ['VG'] = '30.00'; // Verwaltergebühr

                    /* Kosten 1023 Reparatur Einheit */
                    $my_arr [$a] ['AUSGABEN'] = $this->get_kosten_arr('EINHEIT', $my_arr [$a] ['EINHEIT_ID'], $monat, $jahr, $gk->geldkonto_id, 1023);
                    $anz_rep = count($my_arr [$a] ['AUSGABEN']);
                    $summe_rep = 0;
                    for ($b = 0; $b < $anz_rep; $b++) {
                        $summe_rep += $my_arr [$a] ['AUSGABEN'] [$b] ['BETRAG'];
                    }
                    $my_arr [$a] ['AUSGABEN'] [$anz_rep] ['BETRAG'] = '<b>' . nummer_punkt2komma_t($summe_rep) . '</b>';
                    $my_arr [$a] ['AUSGABEN'] [$anz_rep] ['VERWENDUNGSZWECK'] = '<b>Summe</b>';

                    // echo "'EINHEIT', $my_arr[$a]['EINHEIT_ID'], $monat, $jahr, $gk->geldkonto_id<br>";
                    // print_r($arr);

                    $mk = new mietkonto ();
                    $mk->kaltmiete_monatlich($my_arr [$a] ['MIETVERTRAG_ID'], $monat, $jahr);
                    $brutto_sollmiete_arr = explode('|', $mk->summe_forderung_monatlich($my_arr [$a] ['MIETVERTRAG_ID'], $monat, $jahr));
                    $brutto_sollmiete = $brutto_sollmiete_arr [0];
                    $my_arr [$a] ['NETTO_SOLL_MV'] = $mk->ausgangs_kaltmiete;

                    /* Garantierte Miete abfragen */
                    $net_ren_garantie_a = $det->finde_detail_inhalt('EINHEIT', $einheit_id, 'WEG-KaltmieteINS'); // kommt als Kommazahl
                    $net_ren_garantie = nummer_komma2punkt($net_ren_garantie_a);
                    $my_arr [$a] ['NETTO_SOLL_G_A'] = $net_ren_garantie_a;
                    if ($net_ren_garantie > $mk->ausgangs_kaltmiete) {
                        $my_arr [$a] ['NETTO_SOLL'] = $net_ren_garantie;
                    } else {
                        $my_arr [$a] ['NETTO_SOLL'] = $mk->ausgangs_kaltmiete;
                    }

                    $my_arr [$a] ['BRUTTO_SOLL'] = $brutto_sollmiete;
                    $my_arr [$a] ['AUSZAHLUNG_ET'] = $this->get_kosten_arr('Eigentuemer', $eige_id, $monat, $jahr, $gk->geldkonto_id, 5020);
                    $my_arr [$a] ['IST_EINNAHMEN'] = $this->get_kosten_arr('MIETVERTRAG', $my_arr [$a] ['MIETVERTRAG_ID'], $monat, $jahr, $gk->geldkonto_id, 80001);
                    $anz_me = count($my_arr [$a] ['IST_EINNAHMEN']);
                    $summe_einnahmen = 0;
                    for ($b = 0; $b < $anz_me; $b++) {
                        $summe_einnahmen += $my_arr [$a] ['IST_EINNAHMEN'] [$b] ['BETRAG'];
                    }
                    $my_arr [$a] ['IST_EINNAHMEN'] [$anz_me] ['BETRAG'] = '<b>' . nummer_punkt2komma_t($summe_einnahmen) . '</b>';
                    $my_arr [$a] ['IST_EINNAHMEN'] [$anz_me] ['VERWENDUNGSZWECK'] = '<b>Summe</b>';
                    $my_arr [$a] ['BRUTTO_IST'] = $summe_einnahmen;

                    $pdf_tab [$a] ['MIETER_SALDO'] = $my_arr [$a] ['MIETER_SALDO'];
                    $pdf_tab [$a] ['EIGENTUEMER_NAMEN'] = $my_arr [$a] ['EIGENTUEMER_NAMEN'];
                    $pdf_tab [$a] ['EINHEIT_KURZNAME'] = $my_arr [$a] ['EINHEIT_KURZNAME'];

                    $pdf_tab [$a] ['EINHEIT_QM'] = $my_arr [$a] ['EINHEIT_QM'];
                    $pdf_tab [$a] ['EINHEIT_QM_A'] = nummer_punkt2komma($my_arr [$a] ['EINHEIT_QM']);
                    $pdf_tab [$a] ['WEG-FLAECHE'] = $my_arr [$a] ['WEG-FLAECHE'];
                    $pdf_tab [$a] ['WEG-FLAECHE_A'] = $my_arr [$a] ['WEG-FLAECHE_A'];
                    $pdf_tab [$a] ['AUSZAHLUNG_ET'] = $my_arr [$a] ['AUSZAHLUNG_ET'];
                    $pdf_tab [$a] ['MIETER'] = $my_arr [$a] ['MIETER'];
                    /* Garantiemiete */
                    $pdf_tab [$a] ['NETTO_SOLL_G_A'] = $my_arr [$a] ['NETTO_SOLL_G_A'];

                    $pdf_tab [$a] ['BRUTTO_SOLL'] = $my_arr [$a] ['BRUTTO_SOLL'];
                    $pdf_tab [$a] ['BRUTTO_SOLL_A'] = nummer_punkt2komma_t($my_arr [$a] ['BRUTTO_SOLL']);
                    $pdf_tab [$a] ['BRUTTO_IST'] = $my_arr [$a] ['BRUTTO_IST'];
                    $pdf_tab [$a] ['BRUTTO_IST_A'] = nummer_punkt2komma_t($my_arr [$a] ['BRUTTO_IST']);
                    $pdf_tab [$a] ['DIFF'] = $my_arr [$a] ['BRUTTO_IST'] - $pdf_tab [$a] ['BRUTTO_SOLL'];
                    $pdf_tab [$a] ['DIFF_A'] = nummer_punkt2komma_t($my_arr [$a] ['BRUTTO_IST'] - $pdf_tab [$a] ['BRUTTO_SOLL']);

                    $pdf_tab [$a] ['NETTO_SOLL'] = $my_arr [$a] ['NETTO_SOLL'];
                    $pdf_tab [$a] ['NETTO_SOLL_MV'] = $my_arr [$a] ['NETTO_SOLL_MV'];
                    $pdf_tab [$a] ['NETTO_SOLL_DIFF'] = $pdf_tab [$a] ['NETTO_SOLL'] - $pdf_tab [$a] ['NETTO_SOLL_MV'];
                    $pdf_tab [$a] ['NETTO_SOLL_A'] = nummer_punkt2komma_t($my_arr [$a] ['NETTO_SOLL']);
                    if (empty ($my_arr [$a] ['WEG-FLAECHE'])) {
                        $pdf_tab [$a] ['ABGABE_IHR'] = $pdf_tab [$a] ['EINHEIT_QM'] * -0.4;
                    } else {
                        $pdf_tab [$a] ['ABGABE_IHR'] = $my_arr [$a] ['WEG-FLAECHE'] * -0.4;
                    }
                    $pdf_tab [$a] ['ABGABE_IHR_A'] = nummer_punkt2komma($pdf_tab [$a] ['ABGABE_IHR']);
                    $pdf_tab [$a] ['ABGABE_HV'] = '-30.00';
                    $pdf_tab [$a] ['ABGABE_HV_A'] = '-30,00';
                    $pdf_tab [$a] ['ZWISCHENSUMME'] = $my_arr [$a] ['NETTO_SOLL'] + $pdf_tab [$a] ['ABGABE_IHR'] + $pdf_tab [$a] ['ABGABE_HV'];

                    $pdf_tab [$a] ['ZWISCHENSUMME_A'] = nummer_punkt2komma($my_arr [$a] ['NETTO_SOLL'] + $pdf_tab [$a] ['ABGABE_IHR'] + $pdf_tab [$a] ['ABGABE_HV']);
                    $pdf_tab [$a] ['SUMME_REP'] = $summe_rep;
                    $pdf_tab [$a] ['SUMME_REP_A'] = nummer_punkt2komma($summe_rep);
                    $pdf_tab [$a] ['ENDSUMME'] = $pdf_tab [$a] ['ZWISCHENSUMME'];
                    $pdf_tab [$a] ['ENDSUMME_A'] = $pdf_tab [$a] ['ZWISCHENSUMME_A'];

                    $e_nam = $pdf_tab [$a] ['EIGENTUEMER_NAMEN'];
                    $ein_nam = $pdf_tab [$a] ['EINHEIT_KURZNAME'];

                    /* Übersichtstabelle */
                    $uebersicht [$a] ['EINHEIT_KURZNAME'] = $ein_nam;
                    $uebersicht [$a] ['EIGENTUEMER_NAMEN'] = $e_nam;
                    $uebersicht [$a] ['EIG_ID'] = $eige_id;
                    $uebersicht [$a] ['MIETER'] = $pdf_tab [$a] ['MIETER'];
                    $uebersicht [$a] ['MIETER_SALDO'] = $pdf_tab [$a] ['MIETER_SALDO'];
                    $uebersicht [$a] ['MIETER_SALDO_A'] = nummer_punkt2komma($pdf_tab [$a] ['MIETER_SALDO']);
                    $uebersicht [$a] ['NETTO_SOLL_G_A'] = $pdf_tab [$a] ['NETTO_SOLL_G_A'];
                    $uebersicht [$a] ['NETTO_SOLL_A'] = nummer_punkt2komma($pdf_tab [$a] ['NETTO_SOLL_MV']);
                    $uebersicht [$a] ['NETTO_SOLL_DIFF_A'] = nummer_punkt2komma($pdf_tab [$a] ['NETTO_SOLL_DIFF']);
                    $uebersicht [$a] ['ABGABE_HV_A'] = $pdf_tab [$a] ['ABGABE_HV_A'];
                    $uebersicht [$a] ['ABGABE_IHR'] = $pdf_tab [$a] ['ABGABE_IHR_A'];

                    $uebersicht [$a] ['SUMME_REP'] = $pdf_tab [$a] ['SUMME_REP'];
                    $uebersicht [$a] ['SUMME_REP_A'] = $pdf_tab [$a] ['SUMME_REP_A'];

                    $uebersicht [$a] ['WEG-FLAECHE_A'] = $pdf_tab [$a] ['WEG-FLAECHE_A'];
                    $uebersicht [$a] ['EINHEIT_QM_A'] = $pdf_tab [$a] ['EINHEIT_QM_A'];

                    $uebersicht [$a] ['ENDSUMME_A'] = $pdf_tab [$a] ['ENDSUMME_A'];
                    $uebersicht [$a] ['TRANSFER'] = $pdf_tab [$a] ['ENDSUMME'] + $pdf_tab [$a] ['SUMME_REP'];
                    $uebersicht [$a] ['TRANSFER_A'] = nummer_punkt2komma_t($pdf_tab [$a] ['ENDSUMME'] + $pdf_tab [$a] ['SUMME_REP']);

                    if (is_array($my_arr [$a] ['AUSGABEN'])) {
                        array_unshift($my_arr [$a] ['AUSGABEN'], array(
                            'DATUM' => ' '
                        ));
                        array_unshift($my_arr [$a] ['AUSGABEN'], array(
                            'DATUM' => ' '
                        ));
                    }
                    $trans_tab [0] ['TEXT'] = "Amount [€]";
                    $trans_tab [0] ['AM'] = $uebersicht [$a] ['ENDSUMME_A'];
                    $trans_tab [1] ['TEXT'] = "Bills [€]";
                    $trans_tab [1] ['AM'] = $uebersicht [$a] ['SUMME_REP_A'];
                    $trans_tab [2] ['TEXT'] = "<b>Transfer [€]</b>";
                    if ($uebersicht [$a] ['TRANSFER'] > 0) {
                        $trans_tab [2] ['TEXT'] = "<b>Transfer [€]</b>";
                        $trans_tab [2] ['AM'] = "<b>" . $uebersicht [$a] ['TRANSFER_A'] . "</b>";
                    } else {
                        $trans_tab [2] ['TEXT'] = "<b>Summary [€]</b>";
                        $trans_tab [2] ['AM'] = "<b>" . $uebersicht [$a] ['TRANSFER_A'] . "</b>";
                        $trans_tab [3] ['TEXT'] = "<b>Transfer [€]</b>";
                        $trans_tab [3] ['AM'] = "<b>0,00</b>";
                    }

                    unset ($trans_tab);

                    unset ($pdf_tab);
                }
            }
            return $uebersicht;
        } else {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\InfoMessage("Keine Einheiten im Objekt $objekt_id")
            );
        }
    }

    function mieten_pdf($objekt_id, $datum_von, $datum_bis)
    {
        $mv = new mietvertraege ();
        $arr = $mv->mv_arr_zeitraum($objekt_id, $datum_von, $datum_bis);
        if (empty($arr)) {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\InfoMessage("Keine Mietverträge vorhanden.")
            );
        } else {
            echo "<pre>";
            $anz_mvs = count($arr);
            $mz = new miete ();
            $monate = $mz->diff_in_monaten($datum_von, $datum_bis);
            $datum_von_arr = explode('-', $datum_von);
            $start_m = $datum_von_arr [1];
            $start_j = $datum_von_arr [0];

            $datum_bis_arr = explode('-', $datum_bis);

            /* Schleife für jeden Monat */
            $monat = $start_m;
            $jahr = $start_j;
            $summe_g = 0.00;
            for ($a = 0; $a < $monate; $a++) {
                $monat = sprintf('%02d', $monat);
                for ($b = 0; $b < $anz_mvs; $b++) {
                    $mv_id = $arr [$b] ['MIETVERTRAG_ID'];
                    $mv = new mietvertraege ();
                    $mv->get_mietvertrag_infos_aktuell($mv_id);
                    $n_arr [$b] ['EINHEIT'] = $mv->einheit_kurzname;
                    $n_arr [$b] ['EINHEIT_ID'] = $mv->einheit_id;

                    $einheit_qm = $mv->einheit_qm;
                    $det = new detail ();
                    $weg_qm = $det->finde_detail_inhalt('EINHEIT', $mv->einheit_id, 'WEG-Fläche'); // kommt als Kommazahl
                    if (!empty ($weg_qm)) {
                        $einheit_qm = nummer_komma2punkt($weg_qm);
                    }

                    $n_arr [$b] ['TYP'] = $mv->einheit_typ;
                    $n_arr [$b] ['MIETER'] = $mv->personen_name_string;
                    if ($mv->mietvertrag_bis_d == '00.00.0000') {
                        $mv->mietvertrag_bis_d = '';
                    }
                    $n_arr [$b] ['MIETZEIT'] = "$mv->mietvertrag_von_d - $mv->mietvertrag_bis_d";
                    $mietsumme = $mv->summe_forderung_monatlich($mv_id, $monat, $jahr);
                    $n_arr [$b] ["$monat.$jahr"] = $mietsumme;

                    $n_arr [$b] ["$monat.$jahr" . '_IHR'] = $einheit_qm * 0.40;
                    $n_arr [$b] ["$monat.$jahr" . '_IHR_A'] = nummer_punkt2komma($einheit_qm * 0.40);

                    $n_arr [$b] ["$monat.$jahr" . '_HV'] = 30.00;
                    $n_arr [$b] ["$monat.$jahr" . '_HV_A'] = nummer_punkt2komma(30.00);
                    $n_arr [$b] ["$monat.$jahr" . '_AUS'] = $mietsumme - $n_arr [$b] ["$monat.$jahr" . '_IHR'] - $n_arr [$b] ["$monat.$jahr" . '_HV'];
                    $n_arr [$b] ["$monat.$jahr" . '_AUS_A'] = nummer_punkt2komma($n_arr [$b] ["$monat.$jahr" . '_AUS']);

                    $n_arr [$b] ["SUMME"] += $mietsumme;
                    $summe_g += $mietsumme;
                    $sum = $n_arr [$b] ["SUMME"];
                    $n_arr [$b] ["SUMME"] = number_format($sum, 2, '.', '');
                    $n_arr [$b] ["SUMME_A"] = nummer_punkt2komma_t($sum);
                }
                $cols ["$monat.$jahr"] = "$monat.$jahr";

                $monat++;
                $monat = sprintf('%02d', $monat);

                if ($monat > 12) {
                    $monat = 1;
                    $jahr++;
                }
            }
            ob_clean(); // ausgabepuffer leeren
            $pdf = new Cezpdf ('a4', 'landscape');
            $bpdf = new b_pdf ();
            $bpdf->b_header($pdf, 'Partner', session()->get('partner_id'), 'landscape', 'Helvetica.afm', 6);

            $n_arr [$anz_mvs] ['SUMME_A'] = "<b>" . nummer_punkt2komma_t($summe_g) . "</b>";
            $n_arr [$anz_mvs] ['MIETER'] = "<b>Gesamt Sollmieten Nettokalt</b>";

            ob_clean(); // ausgabepuffer leeren
            $cols1 ['EINHEIT'] = 'Einheit';
            $cols1 ['TYP'] = 'Typ';
            $cols1 ['MIETER'] = 'Mieter';
            $cols1 ['MIETZEIT'] = 'Mietzeit';

            $monat = $start_m;
            for ($a = 0; $a < $monate; $a++) {
                $monat = sprintf('%02d', $monat);
                $cols1 ["$monat.$start_j"] = "$monat.$start_j";
                $cols1 ["$monat.$start_j" . "_IHR_A"] = "IHR";
                $cols1 ["$monat.$start_j" . "_HV_A"] = "HV";
                $cols1 ["$monat.$start_j" . "_AUS_A"] = "AUS $monat";
                $monat++;
            }

            $cols1 ['SUMME_A'] = 'BETRAG';

            // $pdf->ezTable($n_arr,$cols1,"Nebenkostenhochrechnung für das Jahr $jahr vom $datum_h",array('showHeadings'=>1,'shaded'=>1, 'titleFontSize' => 8, 'fontSize' => 7, 'xPos'=>50,'xOrientation'=>'right', 'width'=>500,'cols'=>array('EINHEIT'=>array('justification'=>'left', 'width'=>75),'MIETER'=>array('justification'=>'left', 'width'=>175), 'EINZUG'=>array('justification'=>'right','width'=>50),'AUSZUG'=>array('justification'=>'right','width'=>50),'BETRIEBSKOSTEN'=>array('justification'=>'right','width'=>75), 'HEIZKOSTEN'=>array('justification'=>'right','width'=>75))));
            $datum_von_d = date_mysql2german($datum_von);
            $datum_bis_d = date_mysql2german($datum_bis);
            // $pdf->ezTable($n_arr, $cols1, "Vereinbarte Sollkaltmieten im Zeitraum: $datum_von_d - $datum_bis_d", array('showHeadings'=>1,'shaded'=>1, 'width'=>500, 'titleFontSize' => 8, 'fontSize' => 7, 'xPos'=>50,'xOrientation'=>'right', 'cols'=>array('SUMME_A'=>array('justification'=>'right'))));
            // sort($n_arr);
            $pdf->ezTable($n_arr, $cols1, "Vereinbarte Sollkaltmieten im Zeitraum: $datum_von_d - $datum_bis_d", array(
                'showHeadings' => 1,
                'shaded' => 1,
                'titleFontSize' => 8,
                'fontSize' => 6.5,
                'xPos' => 50,
                'xOrientation' => 'right',
                'cols' => array(
                    'SUMME_A' => array(
                        'justification' => 'right'
                    )
                )
            ));
            ob_end_clean(); // ausgabepuffer leeren
            $pdf->ezSetDy(-20);
            $pdf->ezText("     Druckdatum: " . date("d.m.Y"), 11);
            $pdf->ezStream();
        }
    }

    function bilanz($objekt_id = '41', $start_m = '01', $start_j = '2013', $garantie_m = '6', $hvg = '30.00', $ihr_m2 = '0.40', $akt_monat = null)
    {
        if ($akt_monat == null) {
            $akt_monat = date("m");
        }

        /* Alle Monate durchlaufen */
        $o = new objekt ();
        $einheit_arr = $o->einheiten_objekt_arr($objekt_id);
        echo '<pre>';
        print_r($einheit_arr);

        for ($a = 1; $a <= $akt_monat; $a++) {
            $einheit_kn = $einheit_arr [$a] ['EINHEIT_KURZNAME'];
            $einheit_qm = $einheit_arr [$a] ['EINHEIT_QM'];
            echo "$einheit_kn $einheit_qm<br>";
        }
    }

    function monats_array($von, $bis)
    {
        if ($bis == '0000-00-00') {
            $bis = date("Y-m-d");
        }

        $mz = new miete ();
        $monate = $mz->diff_in_monaten($von, $bis);

        $von_arr = explode('-', $von);
        $monat = $von_arr [1];
        $jahr = $von_arr [0];

        $monats_array = array();
        for ($m = 0; $m < $monate; $m++) {

            if ($monat < 12) {
                $monats_array [$m] ['MONAT'] = sprintf('%02d', $monat);
                $monats_array [$m] ['JAHR'] = $jahr;
                $monat++;
            } else {
                $monats_array [$m] ['MONAT'] = sprintf('%02d', $monat);
                $monats_array [$m] ['JAHR'] = $jahr;
                $monat = 1;
                $jahr++;
            }
        }
        return $monats_array;
    }

    function get_mv_et_zeitraum_arr($einheit_id, $datum_von, $datum_bis)
    {
        $db_abfrage = "SELECT * FROM MIETVERTRAG WHERE EINHEIT_ID = '$einheit_id' && MIETVERTRAG_AKTUELL = '1'  && MIETVERTRAG_VON<='$datum_bis' && ( MIETVERTRAG_BIS > '$datum_von' OR MIETVERTRAG_BIS = '0000-00-00' ) ORDER BY MIETVERTRAG_VON ASC";
        $result = DB::select($db_abfrage);
        return $result;
    }

    function get_kosten_summe_monat($kos_typ, $kos_id, $gk_id, $jahr, $monat, $konto = null)
    {
        if ($konto == null) {
            $db_abfrage = "SELECT SUM(BETRAG) AS SUMME FROM `GELD_KONTO_BUCHUNGEN` WHERE `GELDKONTO_ID` = '$gk_id' AND  `KOSTENTRAEGER_TYP` = '$kos_typ' AND `KOSTENTRAEGER_ID` = '$kos_id' AND `AKTUELL` = '1' AND DATE_FORMAT( `DATUM` , '%Y-%m' ) = '$jahr-$monat'";
        } else {
            $db_abfrage = "SELECT SUM(BETRAG) AS SUMME FROM `GELD_KONTO_BUCHUNGEN` WHERE `GELDKONTO_ID` = '$gk_id' AND  `KOSTENTRAEGER_TYP` = '$kos_typ' AND `KOSTENTRAEGER_ID` = '$kos_id' AND `AKTUELL` = '1' && KONTENRAHMEN_KONTO='$konto' AND DATE_FORMAT( `DATUM` , '%Y-%m' ) = '$jahr-$monat'";
        }
        $result = DB::select($db_abfrage);
        if (!empty($result)) {
            $row = $result[0];
            return $row ['SUMME'];
        } else {
            return '0.00';
        }
    }

    function saldo_berechnung_et_DOBARpravo_pdf(&$pdf, $einheit_id)
    {
        /* Infos zu Einheit */
        $e = new einheit ();
        $e->get_einheit_info($einheit_id);
        $gk = new geldkonto_info ();
        $gk->geld_konto_ermitteln('OBJEKT', $e->objekt_id);

        /* OBJEKTDATEN */
        /* Garantiemonate Objekt */
        $d = new detail ();
        $garantie_mon_obj = $d->finde_detail_inhalt('Objekt', $e->objekt_id, 'INS-Garantiemonate');
        if (!$garantie_mon_obj) {
            $garantie_mon_obj = 0;
        } else {
            $this->gmon_obj = $garantie_mon_obj;
        }

        /* Garantierte Miete */
        /* Garantiemiete */
        $garantie_miete = nummer_komma2punkt($d->finde_detail_inhalt('EINHEIT', $einheit_id, 'WEG-KaltmieteINS'));
        if (!$garantie_miete) {
            $garantie_miete = 0;
        }
        /* Nutzenlastenwechsel */
        $nl_datum = $d->finde_detail_inhalt('Objekt', $e->objekt_id, 'Nutzen-Lastenwechsel');
        $nl_datum_arr = explode('.', $nl_datum);
        $nl_tag = $nl_datum_arr [0];
        /* Verwaltungsübernahme */
        $vu_datum = $d->finde_detail_inhalt('Objekt', $e->objekt_id, 'Verwaltungsübernahme');
        $vu_datum_arr = explode('.', $vu_datum);

        echo "<h2>GMU: $garantie_mon_obj NLW: $nl_datum VU: $vu_datum</h2>";

        /* Alle Eigentümer */
        $weg = new weg ();
        $et_arr = $weg->get_eigentuemer_arr($einheit_id);

        if (empty($et_arr)) {
            fehlermeldung_ausgeben("Keine Eigentümer zu $e->einheit_kurzname");
        } else {
            $anz_et = count($et_arr);
            echo "Eigentümeranzahl : $anz_et<hr>";

            /* Schleife für die ET */
            for ($a = 0; $a < $anz_et; $a++) {
                $et_id = $et_arr [$a] ['ID'];
                $weg->get_eigentumer_id_infos4($et_id);

                /* Zeitraum ET */
                if ($weg->eigentuemer_bis = '0000-00-00') {
                    $datum_bis = date("Y-m-d");
                } else {
                    $datum_bis = $weg->eigentuemer_bis;
                }

                /* Garantiemonate OBJ und ET */
                $this->et_tab [$a] ['GMON_OBJ'] = $garantie_mon_obj;

                /* Garantiemonate Eigentuemer */
                $d_et = new detail ();
                $garantie_mon_et = $d_et->finde_detail_inhalt('Eigentuemer', $et_id, 'INS-Garantiemonate');
                /* Wenn Garantie für den ET hinterlegt, dann Anzahl GMONATE AUS DB */
                if ($garantie_mon_et != '') {
                    if ($garantie_mon_et != '0') {
                        $this->gmon_et = $garantie_mon_et;
                    }
                } else {
                    /* Wenn keine Garantie für den ET hinterlegt, dann objekt garantie */
                    if (!empty ($this->gmon_obj)) {
                        $this->gmon_et = $this->gmon_obj;
                    }
                }

                // if($garantie_mon_obj>$garantie_mon_et){
                // $this->et_tab[$a]['GMON'] = $garantie_mon_obj;
                // }else{
                $this->et_tab [$a] ['GMON'] = $garantie_mon_et;
                // $this->gmon_et = $garantie_mon_et;
                // }

                $this->et_tab [$a] ['G_KM'] = $garantie_miete;
                $this->et_tab [$a] ['ET_ID'] = $et_id;
                $this->et_tab [$a] ['ET_VON'] = $weg->eigentuemer_von;
                $this->et_tab [$a] ['ET_BIS'] = $weg->eigentuemer_bis;

                if ($a > 0) {
                    $this->et_tab [$a - 1] ['ET_BIS'] = $weg->eigentuemer_von;
                    if ($this->et_tab [$a] ['ET_BIS'] == '0000-00-00') {
                        $this->et_tab [$a] ['ET_BIS'] = $datum_bis;
                    }
                }

                /* Monate für den ET */
                $monats_arr = $this->monats_array($weg->eigentuemer_von, $datum_bis);

                /* Monate durchlaufen und Tage bestimmen */
                $anz_mon = count($monats_arr);
                for ($m = 0; $m < $anz_mon; $m++) {
                    $monat = $monats_arr [$m] ['MONAT'];
                    $jahr = $monats_arr [$m] ['JAHR'];
                    $tage_m = letzter_tag_im_monat($monat, $jahr);
                    $monats_arr [$m] ['TAGE'] = $tage_m;
                    /* Nutzungstage 1. ET */
                    if ($a == 0 && $m == 0) {
                        $monats_arr [$m] ['N_TAG'] = ($tage_m - $nl_tag + 1);
                    }
                    if ($a > 0 && $m == 0) {
                        $et_von_arr = explode('-', $weg->eigentuemer_von);
                        $et_von_tag = $et_von_arr [2];
                        $monats_arr [$m] ['N_TAG'] = ($tage_m - $et_von_tag + 1);
                    }
                    if ($m > 0) {
                        $monats_arr [$m] ['N_TAG'] = $tage_m;
                    }

                    if ($a == 0 && $m < $this->et_tab [$a] ['GMON']) {
                        $monats_arr [$m] ['G'] = 'J';
                        // ##########################$this->et_tab[$a]['G_KM']
                    } else {
                        $monats_arr [$m] ['G'] = 'N';
                    }
                }
                /* Monatsarray mit Nutzungstagen */
                $this->et_tab [$a] ['MONATE'] = $monats_arr;

                /* MV im ZEITRAUM */
                $mv_et_arr = $this->get_mv_et_zeitraum_arr($einheit_id, $weg->eigentuemer_von, $datum_bis);
                $this->et_tab [$a] ['MVS'] = $mv_et_arr;
                unset ($mv_et_arr);
            } // end for ET SCHLEIFE

            // #############################Vorbereitung PDF###########################
            for ($a = 0; $a < $anz_et; $a++) {
                $et_id = $this->et_tab [$a] ['ET_ID'];

                /* Garantiemonate Eigentuemer */
                $d_et = new detail ();
                $garantie_mon_et = $d_et->finde_detail_inhalt('Eigentuemer', $et_id, 'INS-Garantiemonate');
                /* Wenn Garantie für den ET hinterlegt, dann Anzahl GMONATE AUS DB */
                if ($garantie_mon_et != '') {
                    if ($garantie_mon_et != '0') {
                        $this->gmon_et = $garantie_mon_et;
                    }
                } else {
                    /* Wenn keine Garantie für den ET hinterlegt, dann objekt garantie */
                    if (!empty ($this->gmon_obj)) {
                        $this->gmon_et = $this->gmon_obj;
                    }
                }

                $anz_m = count($this->et_tab [$a] ['MONATE']);

                $zeile = 0;

                $sum_GM_D_S = 0;
                $sum_FIX_S = 0;
                $sum_INS_ANT = 0;
                $sum_INS_ANTR = 0;

                // ######MONATE##########
                for ($m = 0; $m < $anz_m; $m++) {
                    $monat = $this->et_tab [$a] ['MONATE'] [$m] ['MONAT'];
                    $jahr = $this->et_tab [$a] ['MONATE'] [$m] ['JAHR'];
                    $this->pdf_tab_g [$a] [$zeile] ['Z'] = $zeile;
                    $this->pdf_tab_g [$a] [$zeile] ['MMJJJJ'] = $this->et_tab [$a] ['MONATE'] [$m] ['MONAT'] . "." . $this->et_tab [$a] ['MONATE'] [$m] ['JAHR'];
                    $this->pdf_tab_g [$a] [$zeile] ['N_TAG'] = $this->et_tab [$a] ['MONATE'] [$m] ['N_TAG'];
                    $this->pdf_tab_g [$a] [$zeile] ['TAGE'] = $this->et_tab [$a] ['MONATE'] [$m] ['TAGE'];

                    if ($this->pdf_tab_g [$a] [$zeile] ['TAGE'] != $this->pdf_tab_g [$a] [$zeile] ['N_TAG']) {
                        $this->pdf_tab_g [$a] [$zeile] ['VOLLM'] = 'N';
                    } else {
                        $this->pdf_tab_g [$a] [$zeile] ['VOLLM'] = 'J';
                    }

                    $this->pdf_tab_g [$a] [$zeile] ['G'] = $this->et_tab [$a] ['MONATE'] [$m] ['G'];

                    /* FIXKOSTEN */
                    /* Fixkosten Hausgeld oder Formel */
                    $hg = new weg ();
                    $hg->get_wg_info($monat, $jahr, 'Einheit', $einheit_id, 'Hausgeld');
                    $hausgeld_soll = $hg->gruppe_erg / $this->pdf_tab_g [$a] [$zeile] ['TAGE'] * $this->pdf_tab_g [$a] [$zeile] ['N_TAG'];

                    /* Fixkosten nach Formel */
                    $hg->get_eigentumer_id_infos4($et_id);
                    $hausgeld_soll_f = (($hg->einheit_qm_weg * 0.4) + 30) / $this->pdf_tab_g [$a] [$zeile] ['TAGE'] * $this->pdf_tab_g [$a] [$zeile] ['N_TAG'];
                    if ($hausgeld_soll_f > $hausgeld_soll) {
                        $hausgeld_soll = $hausgeld_soll_f;
                    }

                    $this->pdf_tab_g [$a] [$zeile] ['FIX_S'] = nummer_komma2punkt(nummer_punkt2komma($hausgeld_soll));
                    $sum_FIX_S += $this->pdf_tab_g [$a] [$zeile] ['FIX_S'];
                    /* Garantiemiete */
                    $this->pdf_tab_g [$a] [$zeile] ['GM'] = nummer_komma2punkt(nummer_punkt2komma($garantie_miete / $this->pdf_tab_g [$a] [$zeile] ['TAGE'] * $this->pdf_tab_g [$a] [$zeile] ['N_TAG']));

                    /* 1. Et 1. prüfung ob leer, wegen Garantie */
                    $ltm = letzter_tag_im_monat($monat, $jahr);
                    $mv_et_arr_1_mon = $this->get_mv_et_zeitraum_arr($einheit_id, "$jahr-$monat-01", "$jahr-$monat-$ltm");
                    /* Wenn Wohnung VERMIETET war */
                    if (!empty($mv_et_arr_1_mon)) {
                        $this->pdf_tab_g [$a] [$zeile] ['LEER'] = 'N';
                        /* Wenn bei Kauf vermietet */
                        if ($a == 0 && $m == 0) {
                            $this->kauf_leer = 'N';
                            $this->kauf_vermietet = 'J';
                        }
                    } else {
                        /* Wenn Wohnung leer im Monat */
                        $this->pdf_tab_g [$a] [$zeile] ['LEER'] = 'J';
                        if ($a == 0 && $m == 0) {
                            $this->kauf_leer = 'J';
                            $this->kauf_vermietet = 'N';
                        }
                    }

                    /* Bei Leer */
                    if ($this->pdf_tab_g [$a] [$zeile] ['LEER'] == 'J') {
                        /* Leer in Garantiezeit */
                        if ($m < $this->gmon_et) {
                            if ($this->kauf_vermietet = 'N') {
                                $this->pdf_tab_g [$a] [$zeile] ['HINWEIS'] = 'G_LEER_KAUF';
                                $this->pdf_tab_g [$a] [$zeile] ['KM_S'] = nummer_komma2punkt(nummer_punkt2komma($garantie_miete / $this->pdf_tab_g [$a] [$zeile] ['TAGE'] * $this->pdf_tab_g [$a] [$zeile] ['N_TAG']));
                                $this->pdf_tab_g [$a] [$zeile] ['GM_D_S'] = $this->pdf_tab_g [$a] [$zeile] ['KM_S'];
                                $sum_GM_D_S += $this->pdf_tab_g [$a] [$zeile] ['GM_D_S'];
                            } else {
                                $this->pdf_tab_g [$a] [$zeile] ['HINWEIS'] = 'G_NO_GARANTY';
                                // $this->pdf_tab_g[$a][$zeile]['INS_ANT'] = 'G_NO_GARANTY';
                            }
                        }
                        // $zeile++;
                    }

                    /* Wenn vermietet - Neue Zeilen pro MV */
                    /* Alle MVS durchlaufen */
                    $anz_mvs = count($this->et_tab [$a] ['MVS']);
                    for ($mv = 0; $mv < $anz_mvs; $mv++) {
                        $mv_id = $this->et_tab [$a] ['MVS'] [$mv] ['MIETVERTRAG_ID'];

                        $mz = new miete ();
                        $mz->mietkonto_berechnung_monatsgenau($mv_id, $jahr, $monat);
                        if ((isset ($mz->saldo_vormonat_stand))) {
                            // $this->pdf_tab_g[$a][$zeile]['M_SOLL'] = $mk->ausgangs_kaltmiete;
                            $tmp_soll_arr = explode('|', $mz->sollmiete_warm);
                            if (is_array($tmp_soll_arr)) {
                                $wm = $tmp_soll_arr [0];
                                $mwst = $tmp_soll_arr [1];
                            } else {
                                $wm = $mz->sollmiete_warm;
                                $mwst = 0.00;
                            }

                            if ($wm != 0 or $mwst != 0 or $mz->geleistete_zahlungen != 0) {
                                $this->pdf_tab_g [$a] [$zeile] ['WM_SOLL'] = $wm;
                                $this->pdf_tab_g [$a] [$zeile] ['NK'] = nummer_komma2punkt(nummer_punkt2komma($mz->davon_umlagen));
                                $this->pdf_tab_g [$a] [$zeile] ['MWST_S'] = $mwst;
                                $this->pdf_tab_g [$a] [$zeile] ['M_ZB'] = $mz->geleistete_zahlungen;
                                $this->pdf_tab_g [$a] [$zeile] ['M_ERG'] = $mz->erg;
                                $this->pdf_tab_g [$a] [$zeile] ['M_SVM'] = $mz->saldo_vormonat_stand;
                                $this->pdf_tab_g [$a] [$zeile] ['MMJJJJ'] = $this->et_tab [$a] ['MONATE'] [$m] ['MONAT'] . "." . $this->et_tab [$a] ['MONATE'] [$m] ['JAHR'];
                                $this->pdf_tab_g [$a] [$zeile] ['MV_ID'] = $mv_id;
                                $mvs = new mietvertraege ();
                                $mvs->get_mietvertrag_infos_aktuell($mv_id);
                                $this->pdf_tab_g [$a] [$zeile] ['MIETER'] = $mvs->personen_name_string;

                                /* Kaltmiete */
                                $kalt_miete = $wm - nummer_komma2punkt(nummer_punkt2komma($mz->davon_umlagen));
                                $this->pdf_tab_g [$a] [$zeile] ['KM_S'] = nummer_komma2punkt(nummer_punkt2komma($kalt_miete));

                                /* Garantiemiete SOLL DIFF */
                                if ($this->pdf_tab_g [$a] [$zeile] ['GM'] > $kalt_miete) {
                                    $diff_mon_soll = nummer_komma2punkt(nummer_punkt2komma($this->pdf_tab_g [$a] [$zeile] ['GM'] - $kalt_miete));
                                } else {
                                    $diff_mon_soll = '0.00';
                                }

                                $this->pdf_tab_g [$a] [$zeile] ['GM_D_S'] = $diff_mon_soll;
                                $sum_GM_D_S += $this->pdf_tab_g [$a] [$zeile] ['GM_D_S'];
                                /* Garantiemiete IST DIFF */
                            }
                        }
                        // $zeile++;
                    } // end for MV

                    /* Garantiezeile */
                    /* Nur wenn Garantie festgelegt ist */
                    if (isset ($this->gmon_et)) {
                        if ($m == $this->gmon_et - 1) {
                            $zeile++;
                            $this->pdf_tab_g [$a] [$zeile] ['MMJJJJ'] = "-$monat-$jahr-";
                            $this->pdf_tab_g [$a] [$zeile] ['MIETER'] = 'SUMMEN';

                            $this->pdf_tab_g [$a] [$zeile] ['GM_D_S'] = "<b>" . nummer_punkt2komma_t($sum_GM_D_S) . "</b>";
                            $this->pdf_tab_g [$a] [$zeile] ['FIX_S'] = "<b>" . nummer_punkt2komma_t($sum_FIX_S) . "</b>";

                            $z_sum_GM_D_S = $sum_GM_D_S;
                            $z_sum_FIX_S = $sum_FIX_S;
                        }
                    }

                    // $this->pdf_tab_g[$a][$zeile]['MVS'] = $this->et_tab[$a]['MONATE'][$m]['MVS'];
                    // $zeile++;
                    // $this->pdf_tab_g[$a][$zeile]['Z'] = $monat;
                    $zeile++;
                }

                /* Vorletzte Zeile - Summe nach Garantie */
                $this->pdf_tab_g [$a] [$zeile] ['MMJJJJ'] = '----------';
                $this->pdf_tab_g [$a] [$zeile] ['MIETER'] = 'NACH GARANTIE';
                $et_sum_GM_D_S = nummer_punkt2komma_t($sum_GM_D_S - $z_sum_GM_D_S);
                $et_sum_FIX_S = nummer_punkt2komma_t($sum_FIX_S - $z_sum_FIX_S);

                $this->pdf_tab_g [$a] [$zeile] ['GM_D_S'] = "<b>$et_sum_GM_D_S</b>";
                $this->pdf_tab_g [$a] [$zeile] ['FIX_S'] = "<b>$et_sum_FIX_S</b>";

                /* Letzte ZEile */
                $zeile++;
                $this->pdf_tab_g [$a] [$zeile] ['MMJJJJ'] = '----------';
                $this->pdf_tab_g [$a] [$zeile] ['MIETER'] = 'GESAMT';
                $this->pdf_tab_g [$a] [$zeile] ['GM_D_S'] = "<b>" . nummer_punkt2komma_t($sum_GM_D_S) . "</b>";
                $this->pdf_tab_g [$a] [$zeile] ['FIX_S'] = "<b>" . nummer_punkt2komma_t($sum_FIX_S) . "</b>";
                $this->pdf_tab_g [$a] [$zeile] ['INS_ANTR'] = "<b>" . nummer_punkt2komma_t($sum_INS_ANTR) . "</b>";
            } // END FOR ET

            for ($et = 0; $et < $anz_et; $et++) {
                $zeilen = count($this->pdf_tab_g [$et]);
                $sum_KM_I = 0;
                for ($z = 0; $z < $zeilen; $z++) {
                    // ##NUR GARANTIEMONATE BERECHNEN#####

                    if ($z < $this->gmon_et) {
                        /* Mietgarantie diff */
                        if (isset ($this->pdf_tab_g [$et] [$z] ['GM_D_S']) && $this->pdf_tab_g [$et] [$z] ['GM_D_S'] > 0) {
                            $this->pdf_tab_g [$et] [$z] ['KM_I'] = $this->pdf_tab_g [$et] [$z] ['GM'];
                            $this->pdf_tab_g [$et] [$z] ['INS_ANT'] = '0.000F';
                            $this->pdf_tab_g [$et] [$z] ['HINWEIS'] = 'CODE_VG_GM';

                            /* Wenn Mieter gezahlt und in minus */
                            if ($this->pdf_tab_g [$et] [$z] ['M_ZB'] > 0 && $this->pdf_tab_g [$et] [$z] ['M_ERG'] < 0) {
                                $this->pdf_tab_g [$et] [$z] ['INS_ANT'] = nummer_komma2punkt(nummer_punkt2komma(($this->pdf_tab_g [$et] [$z] ['M_ERG'] - $this->pdf_tab_g [$et] [$z] ['M_SVM']) * -1));
                                $this->pdf_tab_g [$et] [$z] ['HINWEIS'] = 'CODE_VG_GM_1';
                                $this->pdf_tab_g [$et] [$z] ['INS_ANTR'] = '0.00';
                            }

                            /* Wenn Mieter nicht gezahlt und in minus */
                            if ($this->pdf_tab_g [$et] [$z] ['M_ZB'] <= 0 && $this->pdf_tab_g [$et] [$z] ['M_ERG'] < 0) {
                                $this->pdf_tab_g [$et] [$z] ['INS_ANT'] = $this->pdf_tab_g [$et] [$z] ['M_ERG'] * -1;
                                $this->pdf_tab_g [$et] [$z] ['HINWEIS'] = 'CODE_VG_GM_2';
                                $this->pdf_tab_g [$et] [$z] ['INS_ANTR'] = '0.00';
                            }

                            /* Wenn Mieter im PLUS */
                            if (isset ($this->pdf_tab_g [$et] [$z] ['M_ERG']) && $this->pdf_tab_g [$et] [$z] ['M_ERG'] >= 0) {
                                $this->pdf_tab_g [$et] [$z] ['INS_ANT'] = '0.000';
                                $this->pdf_tab_g [$et] [$z] ['HINWEIS'] = 'CODE_VG_GM_3';

                                $this->pdf_tab_g [$et] [$z] ['INS_ANTR'] = $sum_INS_ANT - $sum_INS_ANTR;
                                // $sum_INS_ANTR +=$this->pdf_tab_g[$et][$z]['INS_ANTR'];
                            }

                            /* Wenn LEER */
                            if (!isset ($this->pdf_tab_g [$et] [$z] ['M_ERG'])) {
                                $this->pdf_tab_g [$et] [$z] ['INS_ANT'] = $this->pdf_tab_g [$et] [$z] ['GM'];
                                $this->pdf_tab_g [$et] [$z] ['HINWEIS'] = 'CODE_VG_GM_L';

                                $this->pdf_tab_g [$et] [$z] ['INS_ANTR'] = '0000.0';
                                // $sum_INS_ANTR +=0.0;
                            }

                            $sum_INS_ANTR += $this->pdf_tab_g [$et] [$z] ['INS_ANTR'];
                            $sum_KM_I += $this->pdf_tab_g [$et] [$z] ['KM_I'];
                            $sum_INS_ANT += $this->pdf_tab_g [$et] [$z] ['INS_ANT'];
                        } else {
                            $this->pdf_tab_g [$et] [$z] ['KM_I'] = $this->pdf_tab_g [$et] [$z] ['KM_S'];
                            // $sum_KM_I +=$this->pdf_tab_g[$et][$z]['KM_I'];
                            /* Wenn Mieter gezahlt und in minus */
                            if ($this->pdf_tab_g [$et] [$z] ['M_ZB'] > 0 && $this->pdf_tab_g [$et] [$z] ['M_ERG'] < 0) {
                                $this->pdf_tab_g [$et] [$z] ['INS_ANT'] = nummer_komma2punkt(nummer_punkt2komma(($this->pdf_tab_g [$et] [$z] ['M_ERG'] - $this->pdf_tab_g [$et] [$z] ['M_SVM']) * -1));
                                $this->pdf_tab_g [$et] [$z] ['HINWEIS'] = 'CODE_NG_MM1';
                                $this->pdf_tab_g [$et] [$z] ['INS_ANTR'] = '0.00';
                            }
                            /* Wenn Mieter nicht gezahlt und in minus */
                            if ($this->pdf_tab_g [$et] [$z] ['M_ZB'] <= 0 && $this->pdf_tab_g [$et] [$z] ['M_ERG'] < 0) {
                                $this->pdf_tab_g [$et] [$z] ['INS_ANT'] = nummer_komma2punkt(nummer_punkt2komma($this->pdf_tab_g [$et] [$z] ['M_ERG'] * -1));
                                $this->pdf_tab_g [$et] [$z] ['HINWEIS'] = 'CODE_VGNM';
                            }

                            /* Wenn Mieter im PLUS */
                            if (isset ($this->pdf_tab_g [$et] [$z] ['M_ERG']) && $this->pdf_tab_g [$et] [$z] ['M_ERG'] >= 0) {
                                $this->pdf_tab_g [$et] [$z] ['INS_ANT'] = '0.000';
                                $this->pdf_tab_g [$et] [$z] ['HINWEIS'] = 'CODE_VG';

                                $this->pdf_tab_g [$et] [$z] ['INS_ANTR'] = $sum_INS_ANT - $sum_INS_ANTR;
                                $sum_INS_ANTR += $this->pdf_tab_g [$et] [$z] ['INS_ANTR'];
                            }
                            $sum_INS_ANT += $this->pdf_tab_g [$et] [$z] ['INS_ANT'];
                            $sum_KM_I += $this->pdf_tab_g [$et] [$z] ['KM_I'];
                        }
                    } // ##ende garantiemonate

                    /* Nur wenn Garantie festgelegt ist */
                    if (isset ($this->gmon_et)) {
                        if ($z == $this->gmon_et) {
                            $this->pdf_tab_g [$et] [$z] ['KM_I'] = "<b>" . nummer_punkt2komma_t($sum_KM_I) . "</b>";
                            $this->pdf_tab_g [$et] [$z] ['INS_ANT'] = "<b>" . nummer_punkt2komma_t($sum_INS_ANT) . "</b>";
                            $this->pdf_tab_g [$et] [$z] ['INS_ANTR'] = "<b>" . nummer_punkt2komma_t($sum_INS_ANTR) . "</b>";
                            $this->pdf_tab_g [$et] [$z] ['INS_GARANTY'] = "<b>" . nummer_punkt2komma_t($sum_INS_ANTR - $sum_INS_ANT) . "</b>";
                            $this->pdf_tab_g [$et] [$z] ['HINWEIS'] = 'ZSX';
                        }
                    }

                    /* Monate nach Garantie */

                    if ($z > $this->gmon_et) {
                        /* Mietgarantie diff */
                        if (isset ($this->pdf_tab_g [$et] [$z] ['GM_D_S']) && $this->pdf_tab_g [$et] [$z] ['GM_D_S'] > 0) {
                            $this->pdf_tab_g [$et] [$z] ['KM_I'] = $this->pdf_tab_g [$et] [$z] ['GM'];
                            $this->pdf_tab_g [$et] [$z] ['INS_ANT'] = '-';
                            $sum_KM_I += $this->pdf_tab_g [$et] [$z] ['KM_I'];
                            $this->pdf_tab_g [$et] [$z] ['HINWEIS'] = 'CODE_NG_GM';
                        } else {

                            /* Wenn Mieter gezahlt und in minus */
                            if ($this->pdf_tab_g [$et] [$z] ['M_ZB'] > 0 && $this->pdf_tab_g [$et] [$z] ['M_ERG'] < 0) {
                                $this->pdf_tab_g [$et] [$z] ['INS_ANT'] = '-';
                                $this->pdf_tab_g [$et] [$z] ['HINWEIS'] = 'CODE_NGBMa';
                                /* Saldo verändert sich zum Vormonat */
                                if ($this->pdf_tab_g [$et] [$z] ['M_SVM'] != $this->pdf_tab_g [$et] [$z] ['M_ERG']) {
                                    $this->pdf_tab_g [$et] [$z] ['KM_I'] = $this->pdf_tab_g [$et] [$z] ['M_ZB'] + $this->pdf_tab_g [$et] [$z] ['M_SVM'] - $this->pdf_tab_g [$et] [$z] ['NK'];
                                    $this->pdf_tab_g [$et] [$z] ['HINWEIS'] = 'CODE_NGBMx';
                                    $this->pdf_tab_g [$et] [$z] ['INS_ANTR'] = '0.00';
                                } else {
                                    $this->pdf_tab_g [$et] [$z] ['KM_I'] = $this->pdf_tab_g [$et] [$z] ['M_ZB'] - $this->pdf_tab_g [$et] [$z] ['NK'];
                                    $this->pdf_tab_g [$et] [$z] ['HINWEIS'] = 'CODE_NGBMy';
                                    $this->pdf_tab_g [$et] [$z] ['INS_ANTR'] = '0.00';
                                }
                            }
                            /* Wenn Mieter nicht gezahlt und in minus */
                            if ($this->pdf_tab_g [$et] [$z] ['M_ZB'] <= 0 && $this->pdf_tab_g [$et] [$z] ['M_ERG'] < 0) {
                                $this->pdf_tab_g [$et] [$z] ['INS_ANT'] = '-';
                                $this->pdf_tab_g [$et] [$z] ['HINWEIS'] = 'CODE_NGM';
                                $this->pdf_tab_g [$et] [$z] ['KM_I'] = $this->pdf_tab_g [$et] [$z] ['KM_S'] + $this->pdf_tab_g [$et] [$z] ['M_ERG'];
                            }

                            /* Wenn Mieter im PLUS */
                            if (isset ($this->pdf_tab_g [$et] [$z] ['M_ERG']) && $this->pdf_tab_g [$et] [$z] ['M_ERG'] >= 0) {
                                $this->pdf_tab_g [$et] [$z] ['INS_ANT'] = '-';
                                $this->pdf_tab_g [$et] [$z] ['HINWEIS'] = 'CODE_NGP';
                                $this->pdf_tab_g [$et] [$z] ['KM_I'] = $this->pdf_tab_g [$et] [$z] ['KM_S'];
                                $this->pdf_tab_g [$et] [$z] ['INS_ANTR'] = $sum_INS_ANT - $sum_INS_ANTR;
                                $sum_INS_ANTR += $this->pdf_tab_g [$et] [$z] ['INS_ANTR'];
                            }
                            // $this->pdf_tab_g[$et][$z]['KM_I'] = $this->pdf_tab_g[$et][$z]['KM_S'];
                            $sum_KM_I += $this->pdf_tab_g [$et] [$z] ['KM_I'];
                        }
                    } // ##ende garantiemonate
                }

                // echo '<pre>';
                // print_r($this);
                // die();

                /* Nach Schlüssel sortieren wegen PDF */
                ksort($this->pdf_tab_g [$et]);

                // unset($this);
                unset ($mv_et_arr);
                unset ($mv_et_arr_1_mon);
                unset ($mv_id);
                // unset($et_arr);
            }

            $cols = array(
                'Z' => Z,
                'MV_ID' => MV,
                'MMJJJJ' => MONAT2,
                'N_TAG' => N_TAG,
                'TAGE' => TAGE,
                'G' => G,
                'LEER' => LEER,
                'WM_SOLL' => WM_SOLL,
                'MWST_S' => MWST_S,
                'NK' => NK,
                'M_ZB' => M_ZB,
                'M_ERG' => M_ERG,
                'M_SVM' => M_SVM,
                'MIETER' => MIETER,
                'GM' => GM,
                'KM_S' => KM_S,
                'GM_D_S' => GM_D_S,
                'FIX_S' => FIX_S,
                'KM_I' => KM_I,
                'INS_ANT' => INS_ANT,
                'INS_ANTR' => INS_R,
                'INS_GARANTY' => INS_GARANTY,
                'HINWEIS' => CODE
            );

            for ($et = 0; $et < $anz_et; $et++) {
                $pdf->ezText("$e->einheit_kurzname", 14);
                $pdf->ezTable($this->pdf_tab_g [$et], $cols, EINNAHMEN_REPORT . " $weg->eigentuemer_von $datum_bis", array(
                    'showHeadings' => 1,
                    'shaded' => 1,
                    'titleFontSize' => 10,
                    'fontSize' => 7,
                    'xPos' => 50,
                    'xOrientation' => 'right',
                    'width' => 750,
                    'cols' => array(
                        'IHR' => array(
                            'justification' => 'right'
                        ),
                        'HV' => array(
                            'justification' => 'right'
                        ),
                        'REP' => array(
                            'justification' => 'right'
                        ),
                        'AUSZAHLUNG' => array(
                            'justification' => 'right'
                        )
                    )
                ));
            }
            // ob_clean(); //ausgabepuffer leeren
            // header("Content-type: application/pdf"); // wird von MSIE ignoriert
            // $pdf->ezStream();
        } // end if ET exist
    }
    
    function bebuchte_konten_brutto($gk_id, $einheit_id, $monat, $jahr, $et_id, $mv_arr = null)
    {
        // echo "$gk_id, $einheit_id, $et_id, $monat, $jahr, $mv_id<br>";
        if ($mv_arr != null) {
            $anz_mv = count($mv_arr);
            $mv_string = '';
            for ($m = 0; $m < $anz_mv; $m++) {
                $mv_id = $mv_arr [$m] ['MIETVERTRAG_ID'];
                $mv_string .= " OR (`KOSTENTRAEGER_TYP` = 'Mietvertrag' AND `KOSTENTRAEGER_ID` = '$mv_id') ";
            }

            $db_abfrage = "SELECT * FROM `GELD_KONTO_BUCHUNGEN` WHERE `GELDKONTO_ID` = '$gk_id' AND DATE_FORMAT( `DATUM` , '%Y-%m' ) = '$jahr-$monat' AND ((`KOSTENTRAEGER_TYP` = 'Einheit' AND `KOSTENTRAEGER_ID` = '$einheit_id')  OR (`KOSTENTRAEGER_TYP` = 'Eigentuemer' AND `KOSTENTRAEGER_ID` = '$et_id') $mv_string)    AND `AKTUELL` = '1' ORDER BY DATUM";
        } else {
            $db_abfrage = "SELECT * FROM `GELD_KONTO_BUCHUNGEN` WHERE `GELDKONTO_ID` = '$gk_id' AND DATE_FORMAT( `DATUM` , '%Y-%m' ) = '$jahr-$monat' AND ((`KOSTENTRAEGER_TYP` = 'Einheit' AND `KOSTENTRAEGER_ID` = '$einheit_id') OR  (`KOSTENTRAEGER_TYP` = 'Eigentuemer' AND `KOSTENTRAEGER_ID` = '$et_id'))    AND `AKTUELL` = '1' ORDER BY DATUM";
        }
        // echo $db_abfrage;
        $result = DB::select($db_abfrage);
        return $result;
    }

    function pdf_income_reports2015_3(Cezpdf $pdf, $objekt_id, $jahr)
    {
        $cols_num ['MONAT'] ['TXT'] = 'Month';

        $cols_num ['FIX'] ['TXT'] = 'Fixed costs';
        $cols_num ['FIX'] ['TXT1'] = 'Management fee, maintenance reserve';

        $cols_num ['NK'] ['TXT'] = 'Running Costs';
        $cols_num ['NK'] ['TXT1'] = 'Running service costs, cleaning, heating, housekeeping, etc..';

        /* Abzufragende Konten */
        $kokonten [] = '1023'; // Kosten zu Einheit
        $cols_num ['1023'] ['TXT'] = 'Repairs';
        $cols_num ['1023'] ['TXT1'] = 'Repairs and general expenses';

        $kokonten [] = '4180'; // Gewährte Minderungen
        $cols_num ['4180'] ['TXT'] = 'Rent decrease';
        $cols_num ['4180'] ['TXT1'] = '';

        $kokonten [] = '4280'; // Gerichtskosten
        $cols_num ['4280'] ['TXT'] = 'Legal';
        $cols_num ['4280'] ['TXT1'] = 'Legal costs - court fees, lawyers, execution';

        $kokonten [] = '4281'; // Anwaltskosten MEA
        $cols_num ['4281'] ['TXT'] = 'Legal';
        $cols_num ['4281'] ['TXT1'] = 'Legal costs - court fees, lawyers, execution';

        $kokonten [] = '4282'; // Gerichtsvollzieher
        $cols_num ['4282'] ['TXT'] = 'Legal';
        $cols_num ['4282'] ['TXT1'] = 'Legal costs - court fees, lawyers, execution';

        $kokonten [] = '5010'; // Eigentümereinlagen
        $cols_num ['5010'] ['TXT'] = 'Payment by owner';
        $cols_num ['5010'] ['TXT1'] = 'Money received by the owner';

        $kokonten [] = '5020'; // ET Entnahmen TRANSFER
        $cols_num ['5020'] ['TXT'] = 'Transfer';
        $cols_num ['5020'] ['TXT1'] = 'Money transfered to owner';

        $kokonten [] = '5081'; // ET Entnahmen TRANSFER DARLEHEN
        $cols_num ['5081'] ['TXT'] = 'Loan';
        $cols_num ['5081'] ['TXT1'] = 'Money transfered to banc';

        // $kokonten[] = '5021'; // Hausgeld
        // $kokonten[] = '5400'; // Durch INS zu Erstatten
        $kokonten [] = '5500'; // INS Maklergebühr
        $cols_num ['5500'] ['TXT'] = 'Brokerage fee';
        $cols_num ['5500'] ['TXT1'] = '';

        $kokonten [] = '5600'; // Mietaufhegungsvereinbarungen
        $cols_num ['5600'] ['TXT'] = 'Compensation';
        $cols_num ['5600'] ['TXT1'] = 'Compensation for evacuation';
        // $kokonten[] = '6000'; // Hausgeldzahlungen
        // $kokonten[] = '6010'; // Heizkosten
        // $kokonten[] = '6020'; // Nebenkosten / Hausgeld
        // $kokonten[] = '6030'; // IHR
        // $kokonten[] = '6060'; // Verwaltergebühr

        $kokonten [] = '80001'; // Mieteinnahme
        $cols_num ['80001'] ['TXT'] = 'Rental Income';
        $cols_num ['80001'] ['TXT1'] = 'Rent received by the tenant (Brutto, \'warm\'), including service costs, heating, etc.';

        define("EINNAHMEN_REPORT", "Income report");
        define("OBJEKT", "Object");
        define("WOHNUNG", "Flat");
        define("EIGENTUEMER", "<b>Owner</b>");
        define("LAGE", "Location");
        define("TYP", "Type");
        define("FLAECHE", "Living space");

        define("SUMMEN", "sum [€]");
        define("MONAT2", "month");
        define("IHR", "for maintenance [0,40€*m²]");
        define("HV", "managing fee [€]");
        define("REP", "repairs [€]");
        define("AUSZAHLUNG", "actual transfer [€]");
        define("DATUM", "Date");

        $oo = new objekt ();
        $oo->get_objekt_infos($objekt_id);
        $datum_von = "$jahr-01-01";
        $datum_bis = "$jahr-12-31";
        $weg = new weg ();
        $m_arr_jahr = $weg->monatsarray_erstellen($datum_von, $datum_bis);
        $gk = new geldkonto_info ();
        $gk_arr = $gk->geldkonten_arr('OBJEKT', $objekt_id);
        $anz_gk = count($gk_arr);

        $d = new detail ();
        /* Nutzenlastenwechsel */
        $nl_datum = $d->finde_detail_inhalt('Objekt', $objekt_id, 'Nutzen-Lastenwechsel');

        /* Verwaltungsübernahme */
        $vu_datum = $d->finde_detail_inhalt('Objekt', $objekt_id, 'Verwaltungsübernahme');

        $ein_arr = $weg->einheiten_weg_tabelle_arr($objekt_id);

        $anz_e = count($ein_arr);

        $cols ['MONAT'] = 'MONAT';
        $cols ['NT'] = 'NT';
        $cols ['IHR'] = IHR;
        $cols ['HV'] = HV;
        $cols ['FIX'] = 'FIX';
        $cols ['MV_NAME'] = 'MIETER';
        $cols ['KOS_BEZ'] = 'KOS_BEZ';
        $cols ['WM_S'] = 'WM_S';
        $cols ['MWST'] = 'MWST';
        $cols ['NK'] = 'NK';
        $cols ['KM_S'] = 'KM_S';
        $cols ['KM_SA'] = 'KM_SA';
        $cols ['M_ERG'] = 'M_ERG';
        $cols ['TXT'] = 'TXT';

        /* schleife Einheiten */
        for ($e = 0; $e < $anz_e; $e++) {
            $einheit_id = $ein_arr [$e] ['EINHEIT_ID'];
            $weg = new weg ();
            echo '<pre>';
            $et_arr = $weg->get_eigentuemer_arr_jahr($einheit_id, $jahr);
            $anz_et = count($et_arr);
            /* Schleife für ET */

            $sum_hv = 0;
            $sum_ihr = 0;
            $sum_fix = 0;
            $sum_km_ant = 0;
            $sum_wm_s = 0;
            $sum_nk = 0;
            $sum_mwst = 0;
            $sum_km_s = 0;

            $sum_konten = array();
            for ($et = 0; $et < $anz_et; $et++) {
                $et_id = $et_arr [$et] ['ID'];

                /* Personenkontaktdaten Eigentümer */
                $weg_nn = new weg ();
                $et_p_id = $weg_nn->get_person_id_eigentuemer_arr($et_id);
                $email_arr_a = array();
                if (!empty($et_p_id)) {
                    $anz_pp = count($et_p_id);
                    for ($pe = 0; $pe < $anz_pp; $pe++) {
                        $et_p_id_1 = $et_p_id [$pe] ['PERSON_ID'];
                        // echo $et_p_id_1;
                        $detail = new detail ();
                        if (($detail->finde_detail_inhalt('PERSON', $et_p_id_1, 'Email'))) {
                            $email_arr = $detail->finde_alle_details_grup('PERSON', $et_p_id_1, 'Email');
                            for ($ema = 0; $ema < count($email_arr); $ema++) {
                                $em_adr = $email_arr [$ema] ['DETAIL_INHALT'];
                                $email_arr_a [] = $em_adr;
                            }
                        }
                    }
                }

                $et_von_sql = $et_arr [$et] ['VON'];
                $et_bis_sql = $et_arr [$et] ['BIS'];
                $weg1 = new weg ();
                $weg1->get_eigentumer_id_infos4($et_id);
                $weg->get_eigentumer_id_infos4($et_id);
                echo "<b>$weg1->einheit_kurzname $weg1->empf_namen</b><br>";

                /* Zeitarray ET */
                $vond = $jahr . '0101';
                $bisd = $jahr . '1231';
                $et_von = str_replace('-', '', $et_von_sql);
                if ($et_bis_sql != '0000-00-00') {
                    $et_bis = str_replace('-', '', $et_bis_sql);
                } else {
                    $et_bis = str_replace('-', '', "$jahr-12-31");
                }

                if ($et_von > $vond) {
                    $datum_von = $et_von_sql;
                }

                if ($et_bis < $bisd) {
                    $datum_bis = $et_bis_sql;
                }

                if ($et_bis < $vond) {
                    $datum_von = '0000-00-00';
                    $datum_bis = '0000-00-00';
                }

                $m_arr = $weg->monatsarray_erstellen($datum_von, $datum_bis);

                $anz_mon_et = count($m_arr);
                $et_mon_arr = '';
                for ($me = 0; $me < $anz_mon_et; $me++) {
                    $et_mon_arr [] = $m_arr [$me] ['monat'];
                }
                /* Datum zurücksetzen auf Jahresanfang bzw. Ganzjahr */
                $datum_von = "$jahr-01-01";
                $datum_bis = "$jahr-12-31";

                $anz_m = count($m_arr_jahr);
                /* Schlife Monate */
                $zeile = 0;
                for ($m = 0; $m < $anz_m; $m++) {

                    $monat = $m_arr_jahr [$m] ['monat'];
                    $jahr = $m_arr_jahr [$m] ['jahr'];

                    /* Wenn der ET vom Monat */
                    if (in_array($monat, $et_mon_arr)) {

                        $key = array_search($monat, $et_mon_arr);
                        $et_monat = $m_arr [$key] ['monat'];
                        $et_jahr = $m_arr [$key] ['jahr'];

                        $tage = $m_arr [$key] ['tage_m'];
                        $n_tage = $m_arr [$key] ['tage_n'];

                        $pdf_tab [$e] [$et] [$monat] ['NT'] = $n_tage;

                        // ##########ANFANG FIXKOSTEN##########################
                        /* FIXKOSTEN */
                        /* Fixkosten Hausgeld oder Formel */
                        $hg = new weg ();
                        $hg->get_wg_info($monat, $jahr, 'Einheit', $einheit_id, 'Hausgeld');
                        $hausgeld_soll = $hg->gruppe_erg / $tage * $n_tage;

                        /* Fixkosten nach Formel */
                        $hausgeld_soll_f = (($weg->einheit_qm_weg * 0.4) + 30) / $tage * $n_tage;
                        // echo "$hausgeld_soll $hausgeld_soll_f<hr>";

                        if ($hausgeld_soll_f > $hausgeld_soll) {
                            $pdf_tab [$e] [$et] [$monat] ['MONAT'] = "<b>" . monat2name($et_monat, 'en') . " $et_jahr</b>";
                            $pdf_tab [$e] [$et] [$monat] ['IHR'] = nummer_punkt2komma(($weg->einheit_qm_weg * -0.4) / $tage * $n_tage);

                            $sum_ihr += nummer_komma2punkt(nummer_punkt2komma(($weg->einheit_qm_weg * -0.4) / $tage * $n_tage));
                            $pdf_tab [$e] [$et] [$monat] ['HV'] = nummer_punkt2komma(-30.00 / $tage * $n_tage);
                            $sum_hv += nummer_komma2punkt(nummer_punkt2komma(-30.00 / $tage * $n_tage));
                            $pdf_tab [$e] [$et] [$monat] ['FIX'] = nummer_komma2punkt(nummer_punkt2komma(((($weg->einheit_qm_weg * -0.4) + -30) / $tage * $n_tage)));
                            $sum_fix += nummer_komma2punkt(nummer_punkt2komma(((($weg->einheit_qm_weg * -0.4) + -30) / $tage * $n_tage)));
                        } else {
                            /* Wenn nicht der ET vom Monat */

                            $pdf_tab [$e] [$et] [$monat] ['MONAT'] = "<b>" . monat2name($et_monat) . " $et_jahr</b>";
                            $pdf_tab [$e] [$et] [$monat] ['IHR'] = '0.000';
                            $pdf_tab [$e] [$et] [$monat] ['HV'] = '0.000';
                            $pdf_tab [$e] [$et] [$monat] ['FIX'] = nummer_komma2punkt(nummer_punkt2komma(($hausgeld_soll * -1) / $tage * $n_tage));
                            $sum_fix += nummer_komma2punkt(nummer_punkt2komma(($hausgeld_soll * -1) / $tage * $n_tage));
                        }
                        // ##########ENDE FIXKOSTEN##########################
                        // ##########ANFANG LEERSTAND JA NEIN##########################
                        if (isset ($mv_arr)) {
                            unset ($mv_arr);
                        }
                        $ltm = letzter_tag_im_monat($et_monat, $et_jahr);
                        $mv_arr = $this->get_mv_et_zeitraum_arr($einheit_id, "$et_jahr-$et_monat-01", "$et_jahr-$et_monat-$ltm");

                        if (!empty($mv_arr)) {
                            $pdf_tab [$e] [$et] [$monat] ['LEER'] = 'N';
                            $anz_mv = count($mv_arr);
                            // #########MIETVERTRÄGE IM MONAT###########
                            for ($mva = 0; $mva < $anz_mv; $mva++) {
                                $mv_id = $mv_arr [$mva] ['MIETVERTRAG_ID'];
                                $mvv = new mietvertraege ();
                                $mvv->get_mietvertrag_infos_aktuell($mv_id);
                                $pdf_tab [$e] [$et] [$monat] ['MV_NAME'] = substr(bereinige_string($mvv->personen_name_string), 0, 30);
                                $mk = new mietkonto ();
                                $mk->kaltmiete_monatlich($mv_id, $et_monat, $et_jahr);
                                $sum_ford_m_inkl_mwst = $mk->summe_forderung_monatlich($mv_id, $et_monat, $et_jahr);
                                $sum_for_arr = explode('|', $sum_ford_m_inkl_mwst);
                                if (count($sum_for_arr) > 1) {
                                    $wm = $sum_for_arr [0];
                                    $mwst = $sum_for_arr [1];
                                } else {
                                    $wm = $sum_ford_m_inkl_mwst;
                                    $mwst = '0.00';
                                }

                                // $mk->summe_forderung_monatlich($mv_id, $monat, $jahr)
                                $pdf_tab [$e] [$et] [$monat] ['WM_S'] = $wm;
                                $sum_wm_s += $wm;
                                $pdf_tab [$e] [$et] [$monat] ['MWST'] = $mwst;
                                $sum_mwst += $mwst;
                                $pdf_tab [$e] [$et] [$monat] ['NK'] = nummer_komma2punkt(nummer_punkt2komma($wm - nummer_komma2punkt(nummer_punkt2komma($mk->ausgangs_kaltmiete / $tage * $n_tage))));
                                $pdf_tab [$e] [$et] [$monat] ['NK'] = nummer_komma2punkt(nummer_punkt2komma($pdf_tab [$e] [$et] [$monat] ['NK'] * -1));
                                // $sum_nk += $pdf_tab[$e][$et][$zeile]['NK'];
                                $sum_nk += $pdf_tab [$e] [$et] [$monat] ['NK'];
                                $pdf_tab [$e] [$et] [$monat] ['KM_S'] = $mk->ausgangs_kaltmiete;
                                $sum_km_s += $pdf_tab [$e] [$et] [$monat] ['KM_S'];
                                $pdf_tab [$e] [$et] [$monat] ['KM_SA'] = nummer_komma2punkt(nummer_punkt2komma($mk->ausgangs_kaltmiete / $tage * $n_tage));
                                $sum_km_ant += $pdf_tab [$e] [$et] [$monat] ['KM_SA'];
                                /* Saldoberechnung wegen SALDO VV nicht möglich */
                                $mz = new miete ();
                                $mz->mietkonto_berechnung_monatsgenau($mv_id, $et_jahr, $et_monat);
                                $pdf_tab [$e] [$et] [$monat] ['M_ERG'] = nummer_komma2punkt(nummer_punkt2komma($mz->erg));
                                $pdf_tab [$e] [$et] [$monat] ['M_ERGA'] = nummer_komma2punkt(nummer_punkt2komma($mz->erg / $tage * $n_tage));
                                if ($anz_mv > 0) {
                                    $zeile++;
                                }
                            }
                        } else {
                            $pdf_tab [$e] [$et] [$monat] ['LEER'] = 'J';
                            $pdf_tab [$e] [$et] [$monat] ['MV_NAME'] = 'LEER';
                            $mv_arr = '';
                        }
                    }  // end if monat!!!

                    else {
                        $pdf_tab [$e] [$et] [$monat] ['MONAT'] = "<b>" . monat2name($monat) . " $jahr</b>";
                        $pdf_tab [$e] [$et] [$monat] ['IHR'] = '---';
                        $pdf_tab [$e] [$et] [$monat] ['HV'] = '---';
                        $pdf_tab [$e] [$et] [$monat] ['FIX'] = '---';
                    }

                    if (in_array($monat, $et_mon_arr)) {
                        /* Schleife GELD-Konto */
                        for ($g = 0; $g < $anz_gk; $g++) {
                            $gk_id = $gk_arr [$g] ['KONTO_ID'];
                            if (isset ($buchungen)) {
                                unset ($buchungen);
                            }
                            if ($pdf_tab [$e] [$et] [$zeile] ['LEER'] != 'J') {
                                $buchungen = $this->bebuchte_konten_brutto($gk_id, $einheit_id, $monat, $jahr, $et_id, $mv_arr);
                            } else {
                                $buchungen = $this->bebuchte_konten_brutto($gk_id, $einheit_id, $monat, $jahr, $et_id);
                            }
                            if (!empty($buchungen)) {
                                $anz_bu = count($buchungen);
                                $gki1 = new geldkonto_info ();
                                $gki1->geld_konto_details($gk_id);

                                for ($b = 0; $b < $anz_bu; $b++) {
                                    $bkonto = $buchungen [$b] ['KONTENRAHMEN_KONTO'];
                                    if (!empty ($bkonto)) {
                                        $b_konten_arr [] = $bkonto;
                                        $betrag = nummer_komma2punkt(nummer_punkt2komma($buchungen [$b] ['BETRAG']));
                                        if ($bkonto == '5020') {
                                            $betrag = nummer_komma2punkt(nummer_punkt2komma($buchungen [$b] ['BETRAG'])) * -1;
                                        }
                                        $kos_typ = $buchungen [$b] ['KOSTENTRAEGER_TYP'];
                                        $kos_id = $buchungen [$b] ['KOSTENTRAEGER_ID'];
                                        $pdf_tab [$e] [$et] [$monat] [$bkonto] += nummer_komma2punkt(nummer_punkt2komma($betrag)); // NEU
                                        $betrag_p = $pdf_tab [$e] [$et] [$monat] [$bkonto];
                                        $pdf_tab [$e] [$et] [$monat] [$bkonto] = nummer_komma2punkt(nummer_punkt2komma($betrag_p));
                                        $r = new rechnung ();
                                        $sum_konten [$bkonto] += nummer_komma2punkt(nummer_punkt2komma($betrag));
                                        $sum_konten [$bkonto] = nummer_komma2punkt(nummer_punkt2komma($sum_konten [$bkonto]));
                                        $cols [$bkonto] = $bkonto;
                                        $zeile++;
                                    }
                                }
                            }
                        } // end for GK
                    }

                    $zeile++;
                } // end for MONATE
                /* Summe pro ET */
                $pdf_tab [$e] [$et] [$monat + 1] ['MONAT'] = "<b>SUMME</b>";
                $pdf_tab [$e] [$et] [$monat + 1] ['IHR'] = "<b>" . nummer_komma2punkt(nummer_punkt2komma($sum_ihr)) . "</b>";
                $pdf_tab [$e] [$et] [$monat + 1] ['HV'] = "<b>" . nummer_komma2punkt(nummer_punkt2komma($sum_hv)) . "</b>";
                $pdf_tab [$e] [$et] [$monat + 1] ['FIX'] = "<b>" . nummer_komma2punkt(nummer_punkt2komma($sum_fix)) . "</b>";
                $pdf_tab [$e] [$et] [$monat + 1] ['KM_S'] = "<b>" . nummer_komma2punkt(nummer_punkt2komma($sum_km_s)) . "</b>";
                $pdf_tab [$e] [$et] [$monat + 1] ['KM_SA'] = "<b>" . nummer_komma2punkt(nummer_punkt2komma($sum_km_ant)) . "</b>";
                $pdf_tab [$e] [$et] [$monat + 1] ['WM_S'] = "<b>" . nummer_komma2punkt(nummer_punkt2komma($sum_wm_s)) . "</b>";
                $pdf_tab [$e] [$et] [$monat + 1] ['MWST'] = "<b>" . nummer_komma2punkt(nummer_punkt2komma($sum_mwst)) . "</b>";
                $pdf_tab [$e] [$et] [$monat + 1] ['NK'] = "<b>" . nummer_komma2punkt(nummer_punkt2komma($sum_nk)) . "</b>";
                $pdf_tab [$e] [$et] [$monat + 1] ['EINHEIT'] = "<b>" . $weg1->einheit_kurzname . "</b>";;
                $pdf_tab [$e] [$et] [$monat + 1] ['ET'] = "<b>" . $weg1->empf_namen . "</b>";;

                $bb_keys = array_keys($sum_konten);
                for ($bb = 0; $bb < count($sum_konten); $bb++) {
                    $kto = $bb_keys [$bb];
                    $pdf_tab [$e] [$et] [$monat + 1] [$kto] = "<b>" . nummer_komma2punkt(nummer_punkt2komma($sum_konten [$kto])) . "</b>";
                }

                $email_arr_aus = array_unique($email_arr_a);
                $anz_email = count($email_arr_aus);
                $pdf->setColor(255, 255, 255, 255); // Weiss
                for ($ema = 0; $ema < $anz_email; $ema++) {
                    $email_adr = $email_arr_aus [$ema];
                    $pdf->ezText("$email_adr", 10);
                    $pdf->ezSetDy(10); // abstand
                }

                $pdf->setColor(0, 0, 0, 1); // schwarz
                $pdf->ezSetDy(10); // abstand

                $weg1->eigentuemer_von_d = date_mysql2german($weg1->eigentuemer_von);
                $weg1->eigentuemer_bis_d = date_mysql2german($weg1->eigentuemer_bis);

                $weg1->empf_namen = str_replace('Frau', 'Ms.', $weg1->empf_namen);
                $weg1->empf_namen = str_replace('Herr', 'Mr.', $weg1->empf_namen);
                $pdf->ezText(WOHNUNG . ": $weg1->einheit_kurzname\n" . LAGE . ": $weg1->einheit_lage\n$weg1->haus_strasse $weg1->haus_nummer, $weg1->haus_plz $weg1->haus_stadt\n\n" . EIGENTUEMER . ":\n$weg1->empf_namen", 10);

                echo '<pre>';
                $anz_kkk = count($pdf_tab [$e] [$et]);
                $cols_arr = array_keys($pdf_tab [$e] [$et] [$anz_kkk]);
                $cols = array();

                $colsnumkeys_arr = array_keys($cols_num);

                $cols_num1 ['MONAT'] = 'Month';
                $cols_num1 ['80001'] = $cols_num ['80001'] ['TXT'];
                $cols_num1 ['FIX'] = $cols_num ['FIX'] ['TXT'];
                $cols_num1 ['NK'] = $cols_num ['NK'] ['TXT'];
                foreach ($cols_arr as $kl => $vl) {
                    if (is_numeric($vl)) {
                        if (in_array($vl, $colsnumkeys_arr)) {

                            if ($vl != '80001' && $vl != '5020') {
                                $cols_num1 [$vl] = $cols_num [$vl] ['TXT'];
                            }
                        }
                    } else {
                        $cols_alpha [$vl] = $vl;
                    }
                }
                $cols_num1 ['5020'] = $cols_num ['5020'] ['TXT'];

                $anz_s = count($pdf_tab [$e] [$et]);
                $pdf->ezTable($pdf_tab [$e] [$et], $cols_num1, EINNAHMEN_REPORT . " $jahr  - $weg->haus_strasse $weg->haus_nummer in $weg->haus_plz $weg->haus_stadt", array(
                    'showHeadings' => 1,
                    'shaded' => 1,
                    'titleFontSize' => 10,
                    'fontSize' => 9,
                    'xPos' => 35,
                    'xOrientation' => 'right',
                    'width' => 760,
                    'cols' => array(
                        'TXT' => array(
                            'justification' => 'right'
                        ),
                        'IHR' => array(
                            'justification' => 'right'
                        ),
                        'HV' => array(
                            'justification' => 'right'
                        )
                    )
                ));

                $genutzte_ktos = array_keys($cols_num1);
                $pdf->ezSetDy(-15); // abstand
                foreach ($genutzte_ktos as $keyk) {
                    if ($keyk != 'MONAT' && $keyk != 'LEER' && $keyk != 'MV_NAME') {
                        $text_k = $cols_num [$keyk] ['TXT'];
                        $text_k1 = $cols_num [$keyk] ['TXT1'];
                        $pdf->ezText("<b>$text_k</b>: $text_k1", 9);
                    }
                }
                $cols_num1 = array();

                $sum_keys = array_keys($pdf_tab [$e] [$et]);
                $anz_etz = count($sum_keys);
                $last_z = $sum_keys [$anz_etz - 1];
                $pdf->ezSetDy(-30); // abstand
                /* Legende */
                $et_tab = array();
                $et_za = 0;

                $kosten_ko = array();
                $ko_z = 0;
                foreach ($pdf_tab [$e] [$et] [$last_z] as $el_key => $el_value) {
                    if ($el_key == 'FIX') {
                        $bez = 'Fixed owner costs (Mng. Fee and maintenance fund)';
                        $kosten_ko [$ko_z] ['BEZ'] = $bez;
                        $kosten_ko [$ko_z] ['BETRAG'] = nummer_komma2punkt(nummer_punkt2komma($el_value));
                        $ko_z++;
                    }

                    if ($el_key == 'KM_S') {
                        $el_key = 'Net rent only (debit side)';
                    }

                    if ($el_key == 'NK') {
                        $bez = 'Running Service Costs (debit side)';
                        $kosten_ko [$ko_z] ['BEZ'] = $bez;
                        $kosten_ko [$ko_z] ['BETRAG'] = nummer_komma2punkt(nummer_punkt2komma($el_value * -1));
                        $ko_z++;
                    }

                    if ($el_key == 'WM_S') {
                        $el_key = 'Total Rent Income (Brutto) (debit side)';
                    }

                    if (is_numeric($el_key)) {
                        if ($el_key == '80001') {
                            $bez = "$el_key - Total Rent Income (Brutto) - All payments by tenant, incl. Running service costs";
                        }

                        if ($el_key == '5020') {
                            $bez = "$el_key - Transfer to owner";
                        }

                        if ($el_key == '5021') {
                            $bez = "$el_key - Housing benefit";
                        }

                        if ($el_key == '1023') {
                            $bez = "$el_key - Costs/repairs apartment";
                        }

                        if ($el_key == '5101') {
                            $bez = "$el_key - Tenant security deposit";
                        }

                        if ($el_key == '5500') {
                            $bez = "$el_key - Broker fee";
                        }

                        if ($el_key == '5600') {
                            $bez = "$el_key - Tenant evacuation";
                        }

                        if ($el_key == '6000') {
                            $bez = "$el_key - Housing benefit";
                        }

                        if ($el_key == '6010') {
                            $bez = "$el_key - Heating costs";
                        }

                        if ($el_key == '6020') {
                            $bez = "$el_key - Running costs";
                        }

                        if ($el_key == '6030') {
                            $bez = "$el_key - Reserve";
                        }

                        if ($el_key == '6060') {
                            $bez = "$el_key - Management fee";
                        }

                        if (empty ($bez)) {
                            $bez = $el_key;
                        }

                        // $kosten_ko[$ko_z]['BEZ'] = $el_key;
                        if ($el_value != 0 && in_array($el_key, $kokonten)) {
                            $kosten_ko [$ko_z] ['BEZ'] = $bez;
                            $kosten_ko [$ko_z] ['BETRAG'] = nummer_komma2punkt(nummer_punkt2komma($el_value));
                            $bez = '';
                            $ko_z++;
                        }
                    }

                    if ($el_key != 'MONAT' && $el_key != 'IHR' && $el_key != 'NK' && $el_key != 'HV' && $el_key != 'FIX' && $el_key != 'MWST' && !is_numeric($el_key) && $el_key != 'KM_SA' && $el_key != 'ET' && $el_key != 'EINHEIT') {
                        $et_tab [$et_za] ['BEZ'] = $el_key;
                        $et_tab [$et_za] ['BETRAG'] = nummer_komma2punkt(nummer_punkt2komma($el_value));
                        // $pdf->ezTable($pdf_tab[$e][$et][$last_z]);
                        $et_za++;
                    }
                }
                ksort($et_tab);
                arsort($kosten_ko);

                $et_tab1 = array_sortByIndex($et_tab, 'BETRAG', 'SORT_DESC');
                $kosten_ko1 = array_sortByIndex($kosten_ko, 'BETRAG', 'SORT_DESC');

                $et_tab1 [] ['BEZ'] = ' ';
                // $pdf->ezTable($et_tab);
                // $pdf->ezTable($kosten_ko);

                $anz_oo = count($kosten_ko1);
                $amount_et = 0;
                for ($ooo = 0; $ooo < $anz_oo; $ooo++) {
                    $amount_et += $kosten_ko1 [$ooo] ['BETRAG'];
                }

                $kosten_ko1 [$anz_oo] ['BEZ'] = "<b>Balance</b>";
                $kosten_ko1 [$anz_oo] ['BETRAG'] = "<b>" . nummer_komma2punkt(nummer_punkt2komma($amount_et)) . "</b>";

                echo '<pre>';
                $cols_et = array(
                    'BEZ' => 'Description',
                    'BETRAG' => 'Amount'
                );

                if (is_array($sum_konten)) {

                    $gki = new geldkonto_info ();
                    $gki->geld_konto_ermitteln('OBJEKT', $objekt_id);
                    if ($gki->geldkonto_id) {
                        $kr = new kontenrahmen ();
                        $kr_id = $kr->get_kontenrahmen('GELDKONTO', $gki->geldkonto_id);

                        $string = '';
                        $bb_keys = array_keys($sum_konten);
                        for ($bb = 0; $bb < count($sum_konten); $bb++) {
                            $kto = $bb_keys [$bb];
                            $kr->konto_informationen2($kto, $kr_id);
                            $string .= "$kto - $kr->konto_bezeichnung\n";
                            unset ($cols [$kto]);
                        }
                    }
                }

                $pdf_last [$et_id] = $pdf_tab [$e] [$et] [$zeile + 1];

                $sum_konten = array();
                $pdf->ezNewPage();
                $sum_ihr = 0;
                $sum_hv = 0;
                $sum_fix = 0;
                $sum_km_ant = 0;
                $sum_km_s = 0;
                $sum_wm_s = 0;
                $sum_nk = 0;
                $sum_mwst = 0;
            } // end for ET
        } // end for Einheit

        // $pdf->ezTable($pdf_last);
        unset ($cols ['M_ERG']);
        unset ($cols ['TXT']);
        unset ($cols ['MV_NAME']);
        unset ($cols ['KOS_BEZ']);
        unset ($cols ['NT']);
        unset ($cols ['MONAT']);
        $cols ['EINHEIT'] = 'EINHEIT';
        $cols ['ET'] = 'ET';

        /* Legende */
        if (isset($b_konten_arr) && is_array($b_konten_arr)) {
            $b_konten_arr1 = array_unique($b_konten_arr);
            $gki = new geldkonto_info ();
            $gki->geld_konto_ermitteln('OBJEKT', $objekt_id);
            $string = '';
            if ($gki->geldkonto_id) {
                $kr = new kontenrahmen ();
                $kr_id = $kr->get_kontenrahmen('GELDKONTO', $gki->geldkonto_id);
                $bb_keys = array_keys($b_konten_arr1);
                for ($bb = 0; $bb < count($b_konten_arr1); $bb++) {
                    $ktokey = $bb_keys [$bb];
                    $kto = $b_konten_arr1 [$ktokey];
                    $cols [$kto] = $kto;
                    $kr->konto_informationen2($kto, $kr_id);
                    $string .= "<b>$kto</b> - $kr->konto_bezeichnung, ";
                }

                $anz_sumk = count($pdf_last);
                $sum_80001 = 0;
                $sum_5020 = 0;

                $id_keys = array_keys($pdf_last);

                for ($x = 0; $x < $anz_sumk; $x++) {
                    $key = $id_keys [$x];
                    $sum_80001 += $pdf_last [$key] ['80001'];
                    $sum_5020 += $pdf_last [$key] ['5020'];
                }

                $pdf_last [$anz_sumk + 1000] ['ET'] = 'SUMME';
                $pdf_last [$anz_sumk + 1000] ['80001'] = $sum_80001;
                $pdf_last [$anz_sumk + 1000] ['5020'] = $sum_5020;

                unset ($cols ['MONAT']);
                unset ($cols ['IHR']);
                unset ($cols ['HV']);
                unset ($cols ['MWST']);
            }
        }
    }
    
    function form_sepa_ueberweisung_et($e_id, $betrag)
    {
        $gk = new geldkonto_info ();
        $gk->geld_konto_ermitteln('OBJEKT', session()->get('objekt_id'));
        if (!$gk->geldkonto_id) {
            die ('Geldkonto vom Objekt nicht bekannt!');
        }

        $betrag = nummer_punkt2komma($betrag);
        $weg = new weg ();
        $weg->get_eigentumer_id_infos3($e_id);

        $f = new formular ();
        $f->erstelle_formular('SEPA ÜBERWEISUNG', null);
        $f->text_feld_inaktiv('KONTO', 'kto', $gk->bez, 100, 'kto');
        $f->text_feld_inaktiv('EINHEIT', 'eig', "$weg->einheit_kurzname", 25, 'eig');
        $f->text_feld_inaktiv("EIGENTÜMER ($weg->anz_personen)", 'eig', "$weg->empf_namen", 100, 'eig');
        $monat = date("m");
        $jahr = date("Y");

        $f->text_feld('VERWENDUNG', 'vzweck', "$weg->einheit_kurzname $monat.$jahr / Transfer to owner / Auszahlung", 100, 'vzweck', '');
        $f->text_feld('BETRAG', 'betrag', $betrag, 20, 'betrag', '');
        $sep = new sepa ();
        if ($sep->dropdown_sepa_geldkonten('Empfängerkonto', 'empf_sepa_gk_id', 'empf_sepa_gk_id', 'Eigentuemer', $e_id) != false) {
            $f->hidden_feld('option', 'sepa_sammler_hinzu');
            $f->hidden_feld('kat', 'ET-AUSZAHLUNG');
            $f->hidden_feld('gk_id', $gk->geldkonto_id);
            $f->hidden_feld('kos_typ', 'Eigentuemer');
            $f->hidden_feld('kos_id', $e_id);
            $kk = new kontenrahmen ();
            $kk->dropdown_kontorahmenkonten_vorwahl('Buchungskonto', 'konto', 'konto', 'GELDKONTO', session()->get('geldkonto_id'), '', '5020');
            $f->send_button('sndBtn', 'Hinzufügen');
        }
        $f->ende_formular();
    }
    
    function bebuchte_konten($gk_id, $einheit_id, $monat, $jahr, $et_id, $mv_id = null)
    {
        // echo "$gk_id, $einheit_id, $et_id, $monat, $jahr, $mv_id<br>";
        if ($mv_id != null) {
            $db_abfrage = "SELECT * FROM `GELD_KONTO_BUCHUNGEN` WHERE `GELDKONTO_ID` = '$gk_id' AND DATE_FORMAT( `DATUM` , '%Y-%m' ) = '$jahr-$monat' AND ((`KOSTENTRAEGER_TYP` = 'Einheit' AND `KOSTENTRAEGER_ID` = '$einheit_id') OR (`KOSTENTRAEGER_TYP` = 'Mietvertrag' AND `KOSTENTRAEGER_ID` = '$mv_id') OR (`KOSTENTRAEGER_TYP` = 'Eigentuemer' AND `KOSTENTRAEGER_ID` = '$et_id'))    AND `AKTUELL` = '1' ORDER BY DATUM";
        } else {
            $db_abfrage = "SELECT * FROM `GELD_KONTO_BUCHUNGEN` WHERE `GELDKONTO_ID` = '$gk_id' AND DATE_FORMAT( `DATUM` , '%Y-%m' ) = '$jahr-$monat' AND ((`KOSTENTRAEGER_TYP` = 'Einheit' AND `KOSTENTRAEGER_ID` = '$einheit_id') OR  (`KOSTENTRAEGER_TYP` = 'Eigentuemer' AND `KOSTENTRAEGER_ID` = '$et_id'))    AND `AKTUELL` = '1' ORDER BY DATUM";
        }
        // echo $db_abfrage;
        $result = DB::select($db_abfrage);
        if (!empty($result)) {
            foreach ($result as $row) {
                $kos_typ = $row ['KOSTENTRAEGER_TYP'];
                $kos_id = $row ['KOSTENTRAEGER_ID'];
                $betrag = $row ['BETRAG'];
                if ($kos_typ == 'Mietvertrag') {
                    /* Nebenkosten abziehen */
                    if ($mv_id != null) {
                        $mk = new mietkonto ();
                        $mk->kaltmiete_monatlich($kos_id, $monat, $jahr);

                        $row ['VERWENDUNGSZWECK'] = $row ['VERWENDUNGSZWECK'] . " Brutto " . $row ['BETRAG'];
                        if ($betrag > 0) {
                            $row ['BETRAG'] = $mk->ausgangs_kaltmiete;
                        }
                    }
                }
                $my_array [] = $row;
            }
            // print_r($my_array);
            return $my_array;
            // echo "<hr>";
        }
    }

    function kto_auszug_einheit($einheit_id)
    {
        $e = new einheit ();
        $e->get_einheit_info($einheit_id);
        $weg = new weg ();
        $weg->get_last_eigentuemer($einheit_id);

        $e_id = $weg->eigentuemer_id;
        $von = $weg->von;
        $bis = $weg->bis;
        if ($bis == '0000-00-00') {
            $bis = date("Y-m-d");
        }

        $weg->get_eigentumer_id_infos3($e_id);

        $monats_array = $this->monats_array($von, $bis);

        $anz_monate = count($monats_array);
        $buchungen_arr = array();
        for ($a = 0; $a < $anz_monate; $a++) {
            $monat = $monats_array [$a] ['MONAT'];
            $jahr = $monats_array [$a] ['JAHR'];

            $mv_id = $this->get_mv_monat($einheit_id, $monat, $jahr);

            $buchungen_arr [$a] = $this->bebuchte_konten(session()->get('geldkonto_id'), $einheit_id, $monat, $jahr, $e_id, $mv_id);
            $anz_b = count($buchungen_arr [$a]);
            $buchungen_arr [$a] [$anz_b] ['KONTENRAHMEN_KONTO'] = "6000";
            $buchungen_arr [$a] [$anz_b] ['KOSTENTRAEGER_TYP'] = "Einheit";
            $buchungen_arr [$a] [$anz_b] ['DATUM'] = "$jahr-$monat-01";
            $buchungen_arr [$a] [$anz_b] ['BETRAG'] = $weg->get_sume_hausgeld('Einheit', $einheit_id, $monat, $jahr);
            $buchungen_arr [$a] [$anz_b] ['VERWENDUNGSZWECK'] = 'HAUSGELD';
            /*
			 * $buchungen_arr[$a][$anz_b+1]['KONTENRAHMEN_KONTO'] = "6030";
			 * $buchungen_arr[$a][$anz_b+1]['KOSTENTRAEGER_TYP'] = "Einheit";
			 * $buchungen_arr[$a][$anz_b+1]['DATUM'] = "$jahr-$monat-01";
			 * $buchungen_arr[$a][$anz_b+1]['BETRAG'] = $weg->get_sume_hausgeld('EInheit', $einheit_id, $monat, $jahr);
			 * $buchungen_arr[$a][$anz_b+1]['VERWENDUNGSZWECK'] = 'IHR';
			 */
            $buchungen_arr [$a] ['MONAT'] = $monat; //
            $buchungen_arr [$a] ['JAHR'] = $jahr;
        }
        // print_r($buchungen_arr);
        // print_r($weg);

        $anz_mon = count($buchungen_arr);
        echo "<table class=\"sortable\">";
        echo "<tr><td>Datum</td><td>kos_typ</td><td>konto</td><td>text</td><td>Betrag</td></tr>";
        $sum = 0;
        for ($a = 0; $a < $anz_mon; $a++) {
            $monat = $buchungen_arr [$a];
            $anz_buch = count($monat);
            $akt_monat = $buchungen_arr [$a] ['MONAT'];
            $akt_jahr = $buchungen_arr [$a] ['JAHR'];
            echo "<tr><th colspan=\"5\">$akt_monat/$akt_jahr</th></tr>";
            for ($b = 0; $b < $anz_buch - 2; $b++) {
                $betrag = $monat [$b] ['BETRAG'];
                $konto = $monat [$b] ['KONTENRAHMEN_KONTO'];
                $datum = date_mysql2german($monat [$b] ['DATUM']);
                $kos_typ = $monat [$b] ['KOSTENTRAEGER_TYP'];
                $text = $monat [$b] ['VERWENDUNGSZWECK'];
                $sum += $betrag;
                echo "<tr><td>$datum</td><td>$kos_typ</td><td>$konto</td><td>$text</td><td>$betrag</td></tr>";
            }

            echo "<tr><td></td><td></td><td></td><th>MONATSSALDO</th><th>";
            if ($sum > 0) {
                echo "<b>$sum</b>";
            } else {
                fehlermeldung_ausgeben($sum);
            }
            echo "</th></tr>";
            echo "<tr><td></td><td></td><td></td><td></td><td></td></tr>";
        }
        echo "</table>";
    }

    function get_mv_monat($einheit_id, $monat, $jahr)
    {
        $datum_von = "$jahr-$monat-01";
        $ltag = letzter_tag_im_monat($monat, $jahr);
        $datum_bis = "$jahr-$monat-$ltag";
        $result = DB::select("SELECT MIETVERTRAG_ID FROM MIETVERTRAG WHERE EINHEIT_ID = '$einheit_id' && MIETVERTRAG_AKTUELL = '1'  && MIETVERTRAG_VON<='$datum_bis' && ( MIETVERTRAG_BIS >= '$datum_von' OR MIETVERTRAG_BIS = '0000-00-00' ) ORDER BY MIETVERTRAG_VON DESC LIMIT 0 , 1 ");
        if (!empty($result)) {
            $row = $result[0];
            return $row ['MIETVERTRAG_ID'];
        }
    }

    function form_profil_neu()
    {
        $f = new formular ();
        $f->erstelle_formular('Neues Profil für die Berichte erstellen', null);
        $f->text_feld('Kurzbeschreibung', 'kurz_b', '', 50, 'kurz_b', null);
        $o = new objekt ();
        $o->dropdown_objekte('objekt_id', 'objekt_id');
        $sep = new sepa ();
        if (session()->has('geldkonto_id')) {
            $gk = new geldkonto_info ();
            $gk->geld_konto_details(session()->has('geldkonto_id'));
            $filter_bez = $gk->geldkonto_bez;
        } else {
            $filter_bez = '';
        }
        $sep->dropdown_sepa_geldkonten_filter('Geldkonto wählen', 'gk_id', 'gk_id', $filter_bez);
        $p = new partner ();
        $p->partner_dropdown('Hausverwaltung wählen', 'p_id', 'p_id');
        $f->hidden_feld('option', 'step2');
        $f->send_button('snd_listenProf', 'Weiter zu Schritt 2');
        $f->ende_formular();
    }

    function report_profil_anlegen($kurz_b, $objekt_id, $gk_id, $p_id)
    {
        $last_id = last_id2('REPORT_PROFILE', 'ID') + 1;
        $db_abfrage = "INSERT INTO REPORT_PROFILE VALUES (NULL, '$last_id', '$kurz_b', '$objekt_id', '$gk_id', '$p_id', '1')";
        DB::insert($db_abfrage);
        /* Protokollieren */
        $last_dat = DB::getPdo()->lastInsertId();
        protokollieren('REPORT_PROFILE', $last_dat, '0');
        return $last_id;
    }

    function form_profil_step2($profil_id)
    {
        $this->get_r_profil_infos($profil_id);
        $f = new formular ();
        $f->erstelle_formular('Buchungskonten für das Profil wählen', null);
        $kr = new kontenrahmen ();
        $kr_id = $kr->get_kontenrahmen('GELDKONTO', $this->gk_id);
        $arr = $kr->konten_in_arr_rahmen($kr_id);
        if (empty($arr)) {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\ErrorMessage("Kontenrahmen unbekannt!")
            );
        } else {
            $anz = count($arr);
            $b_konten = $this->profil_liste_konten_arr($profil_id);
            for ($a = 0; $a < $anz; $a++) {
                $konto = $arr [$a] ['KONTO'];
                $bez = $arr [$a] ['BEZEICHNUNG'];
                if (!in_array($konto, $b_konten)) {
                    $f->check_box_js1("b_konten[$a]", 'b_konto' . $a, $konto, "$konto $bez", null, '');
                } else {
                    $f->check_box_js1("b_konten[$a]", 'b_konto' . $a, $konto, "$konto $bez", null, 'checked');
                }
                $f->hidden_feld("bez_arr[$a]", $bez);
            }
        }
        $f->send_button('Snd_konten', 'speichern');
        $f->hidden_feld('option', 'konten_bearbeiten');
        $f->hidden_feld('profil_id', $profil_id);
        $f->ende_formular();
    }

    function get_r_profil_infos($profil_id)
    {
        $db_abfrage = "SELECT * FROM REPORT_PROFILE WHERE ID='$profil_id' && AKTUELL='1' ORDER BY DAT DESC LIMIT 0,1";
        $result = DB::select($db_abfrage);
        if (!empty($result)) {
            $row = $result[0];
            $this->kurz_b = $row ['KURZ_B'];
            $this->objekt_id = $row ['OBJEKT_ID'];
            $this->gk_id = $row ['GK_ID'];
            $this->partner_id = $row ['PARTNER_ID'];
        } else {
            fehlermeldung_ausgeben("Profilinfos für Profil $profil_id unbekannt!");
        }
    }

    function profil_liste_konten_arr($profil_id)
    {
        $db_abfrage = "SELECT KONTO FROM REPORT_PROFILE_K WHERE PROFIL_ID='$profil_id' ORDER BY KONTO ASC";
        $result = DB::select($db_abfrage);
        $arr = array();
        foreach ($result as $row) {
            $arr [] = $row ['KONTO'];
        }
        return $arr;
    }

    function profil_liste()
    {
        if (session()->has('r_profil_id')) {
            $this->get_r_profil_infos(session()->get('r_profil_id'));
            fehlermeldung_ausgeben("Aktuelles Profil: $this->kurz_b");
            session()->put('partner_id', $this->partner_id);
        }
        $arr = $this->profil_liste_arr();
        if (!empty($arr)) {
            $anz = count($arr);
            echo "<table>";
            echo "<tr><th>NR</th><th>PROFIL</th><th>OBJEKT</th><th>GELDKONTO</th><th>HV LOGO</th><th>OPTIONEN</th></tr>";
            for ($a = 0; $a < $anz; $a++) {
                $text = $arr [$a] ['KURZ_B'];
                $profil_id = $arr [$a] ['ID'];
                $objekt_id = $arr [$a] ['OBJEKT_ID'];
                $gk_id = $arr [$a] ['GK_ID'];
                $gk_info = new geldkonto_info ();
                $gk_info->geld_konto_details($gk_id);
                $partner_id = $arr [$a] ['PARTNER_ID'];
                $pp = new partner ();
                $partner_name = $pp->get_partner_name($partner_id);
                $oo = new objekt ();
                $objekt_name = $oo->get_objekt_name($objekt_id);
                $link_profil_wahl = "<a href='" . route('web::listen::legacy', ['option' => 'profil_wahl', 'profil_id' => $profil_id]) . "'>$text</a>";
                $link_profil_edit = "<a href='" . route('web::listen::legacy', ['option' => 'profil_edit', 'profil_id' => $profil_id]) . "'>Konten ändern</a>";
                $link_bericht = "<a href='" . route('web::listen::legacy', ['option' => 'pruefung_bericht', 'profil_id' => $profil_id]) . "'>Bericht erstellen</a>";
                if (session()->has('r_profil_id') && session()->get('r_profil_id') == $profil_id) {
                    echo "<tr class=\"zeile2\"><td>$profil_id</td><td>$link_profil_wahl</td><td>$objekt_name</td><td>$gk_info->geldkonto_bezeichnung_kurz</td><td>$partner_name</td><td>$link_profil_edit $link_bericht</td></tr>";
                } else {
                    echo "<tr><td>$profil_id</td><td>$link_profil_wahl</td><td>$objekt_name</td><td>$gk_info->geldkonto_bezeichnung_kurz</td><td>$partner_name</td><td></td></tr>";
                }
            }
            echo "</table>";
        } else {
            die ('Keine Profile vorhanden!!!');
        }
    }

    function profil_liste_arr()
    {
        $db_abfrage = "SELECT * FROM REPORT_PROFILE WHERE AKTUELL='1' ORDER BY KURZ_B";
        $result = DB::select($db_abfrage);
        return $result;
    }

    function b_konten_edit($profil_id, $arr, $bez_arr)
    {
        $this->del_konten($profil_id);
        foreach ($arr as $key => $konto) {
            $bez_de = $bez_arr [$key];
            $bez_en = $bez_arr [$key];
            $db_abfrage = "INSERT INTO REPORT_PROFILE_K VALUES (NULL, '$profil_id', '$konto', '$bez_de', '$bez_en')";
            DB::insert($db_abfrage);
        }
    }

    function del_konten($profil_id)
    {
        $b_arr = $this->profil_liste_konten_arr($profil_id);
        if (!empty($b_arr)) {
            $db_abfrage = "DELETE FROM REPORT_PROFILE_K WHERE PROFIL_ID='$profil_id'";
            DB::delete($db_abfrage);
        }
    }

    function pruefung_bericht($profil_id, $monat = null)
    {
        if ($monat == null) {
            $monat = date("m");
        }
        $jahr = date("Y");

        $this->get_r_profil_infos($profil_id);
        $email_err = $this->pruefen_emails($this->objekt_id);
        if (is_array($email_err)) {
            echo "<pre>";
            print_r($email_err);
            $anz_e = count($email_err);
            fehlermeldung_ausgeben("FOlgende Eigentümer haben keine Emailadresse!!!");
            echo "<table>";
            for ($e = 0; $e < $anz_e; $e++) {
                $weg = new weg ();
                $e_id = $arr [$e] ['ET_ID'];
                $weg->get_eigentumer_id_infos3($e_id);
                echo "<tr><td>$weg->einheit_kurzname</td><td>$weg->empf_namen_u</td></tr>";
            }
            echo "</table>";
            die ();
        } else {
            fehlermeldung_ausgeben("Keine Email fehler!");
            $bk_konten_arr = $this->bk_konten_arr($profil_id);
            if (empty($bk_konten_arr)) {
                fehlermeldung_ausgeben("Keine Kostenkonten gewählt!!!");
            } else {
                // print_r($bk_konten_arr);
                $anz_k = count($bk_konten_arr);
                $f = new formular ();
                $f->erstelle_formular("Bericht erstellen", null);
                $this->get_r_profil_infos($profil_id);
                echo "<hr>$this->kurz_b<hr>";
                echo "<table>";
                for ($a = 0; $a < $anz_k; $a++) {
                    $kto = $bk_konten_arr [$a] ['KONTO'];
                    $bez_de = $bk_konten_arr [$a] ['BEZ_DE'];
                    $bez_en = $bk_konten_arr [$a] ['BEZ_EN'];
                    $this->get_last_zeitraum($profil_id, $kto);

                    if (!isset ($this->report_bis)) {
                        $this->report_von_neu = "$jahr-$monat-01";
                        $lt = letzter_tag_im_monat($monat, $jahr);
                        $this->report_bis_neu = "$jahr-$monat-$lt";
                    } else {
                        $this->report_von_neu = tage_plus($this->report_bis, 1);
                        $von_n_arr = explode('-', $this->report_von);
                        $lt_neu = letzter_tag_im_monat($monat, $jahr);
                        $this->report_bis_neu = "$jahr-$monat-$lt_neu";
                    }

                    $this->report_von_neu_d = date_mysql2german($this->report_von_neu);
                    $this->report_bis_neu_d = date_mysql2german($this->report_bis_neu);

                    echo "<tr><td>$kto</td><td>$bez_de</td><td>$bez_en</td><td>";
                    echo "ALT: $this->report_von<br>NEU:$this->report_von_neu<br>";
                    $f->datum_feld('VON', 'bericht_von[]', $this->report_von_neu_d, 'von');
                    echo "</td><td>";
                    echo "ALT: $this->report_bis<br>NEU:$this->report_bis_neu<br>";

                    $f->datum_feld('BIS', 'bericht_bis[]', $this->report_bis_neu_d, 'bis');
                    echo "</td></tr>";
                    $f->hidden_feld('bk_konten[]', $kto);
                }
                echo "</table>";
                // print_r($this);
                $f->hidden_feld('monat', $monat);
                $f->hidden_feld('jahr', $jahr);
                $f->hidden_feld('objekt_id', $this->objekt_id);
                $f->hidden_feld('option', 'dyn_pdf');
                $f->hidden_feld('lang', 'en');
                $this->dropdown_lang('Sprache', 'lang', 'lng');
                $f->hidden_feld('profil_id', $profil_id);
                $f->send_button('Bnt_Bericht', 'PDF-Anzeigen');
                $f->ende_formular();
            }
        }
    }

    function pruefen_emails($objekt_id)
    {
        // echo "PRÜFE EMAILS!!!";
        // echo $objekt_id;
        $weg = new weg ();
        $ein_arr = $weg->einheiten_weg_tabelle_arr($objekt_id);
        if (empty($ein_arr)) {
            fehlermeldung_ausgeben("Keine Einheiten im Objekt");
        } else {
            $anz_e = count($ein_arr);

            $et_arr;
            for ($a = 0; $a < $anz_e; $a++) {
                $einheit_id = $ein_arr [$a] ['EINHEIT_ID'];
                $weg1 = new weg ();
                $weg1->get_last_eigentuemer($einheit_id);
                // print_r($weg1);
                if (isset ($weg1->eigentuemer_id)) {
                    $error = 0;
                    $anz_p = count($weg1->eigentuemer_name);
                    for ($g = 0; $g < $anz_p; $g++) {
                        $person_id = $weg1->eigentuemer_name [$g] ['person_id'];

                        $dd = new detail ();
                        $email = $dd->finde_detail_inhalt('PERSON', $person_id, 'Email');
                        if (!$email) {
                            $error++;
                        } else {
                            $error--;
                        }
                    }
                    if ($error >= $anz_p) {
                        $et_arr [$a] ['ET_ID'] = $weg1->eigentuemer_id;
                    }
                    unset ($weg1->eigentuemer_id);
                }
            }
        }
        if (isset ($et_arr) && is_array($et_arr)) {
            return $et_arr;
        }
    }

    function bk_konten_arr($profil_id)
    {
        $db_abfrage = "SELECT * FROM REPORT_PROFILE_K WHERE PROFIL_ID='$profil_id' ORDER BY KONTO ASC";
        $result = DB::select($db_abfrage);
        return $result;
    }

    function get_last_zeitraum($profil_id, $konto)
    {
        if (isset ($this->report_von)) {
            unset ($this->report_von);
        }
        if (isset ($this->report_bis)) {
            unset ($this->report_bis);
        }

        $db_abfrage = "SELECT * FROM REPORT_ZEITRAUM WHERE PROFIL_ID='$profil_id' && KONTO='$konto' ORDER BY DAT DESC LIMIT 0,1";
        $result = DB::select($db_abfrage);
        $row = $result[0];
        $this->report_von = $row ['VON'];
        $this->report_bis = $row ['BIS'];
    }

    function dropdown_lang($label, $name, $id)
    {
        echo "<label for=\"$name\">$label</label>\n<select name=\"$name\" id=\"$id\" size=\"1\" >\n";
        echo "<option value=\"en\" >English</option>\n";
        echo "<option value=\"de\" >Deutsch</option>\n";
        echo "</select>\n";
    }

    function dyn_pdf($profil_id, $objekt_id, $monat, $jahr, $bericht_von_arr, $bericht_bis_arr, $b_konten_arr, $lang = 'de')
    {
        $this->get_r_profil_infos($profil_id);
        $gk_id = $this->gk_id;

        /* Eingrenzung Kostenabragen */
        if (!request()->has('von') or !request()->has('bis')) {
            $von = "01.$monat.$jahr";
            $lt = letzter_tag_im_monat($monat, $jahr);
            $bis = "$lt.$monat.$jahr";
        }
        $pdf = new Cezpdf ('a4', 'portrait');
        $bpdf = new b_pdf ();
        $bpdf->b_header($pdf, 'Partner', $this->partner_id, 'portrait', 'Helvetica.afm', 6);
        $pdf->ezStopPageNumbers(); // seitennummerirung beenden

        /* Schleife für jede Einheit */
        $weg = new weg ();
        $ein_arr = $weg->einheiten_weg_tabelle_arr($objekt_id);
        $anz_e = count($ein_arr);
        for ($e = 0; $e < $anz_e; $e++) {
            $weg = new weg ();
            $einheit_id = $ein_arr [$e] ['EINHEIT_ID'];
            $weg->get_last_eigentuemer($einheit_id);

            if (isset ($weg->eigentuemer_id)) {
                $ein_arr [$e] ['ET_ID'] = $weg->eigentuemer_id;
                $weg->get_eigentumer_id_infos3($weg->eigentuemer_id);
                $ein_arr [$e] ['ET_NAMEN'] = $weg->empf_namen_u;
            } else {
            }
            if (isset ($weg->versprochene_miete)) {
                $ein_arr [$e] ['V_MIETE'] = $weg->versprochene_miete;
            } else {
                $ein_arr [$e] ['V_MIETE'] = '0.00';
            }
            $ein_arr [$e] ['WEG-QM'] = $weg->einheit_qm_weg;

            /* Mieter */
            $ee = new einheit ();
            $mv_id = $ee->get_mietvertrag_id($einheit_id);
            if ($mv_id) {
                $mvs = new mietvertraege ();
                $mvs->get_mietvertrag_infos_aktuell($mv_id);
                $kontaktdaten = $ee->kontaktdaten_mieter($mv_id);
                $ein_arr [$e] ['MIETER'] = $mvs->personen_name_string_u;
                $ein_arr [$e] ['MIETVERTRAG_ID'] = $mv_id;
                $mk = new mietkonto ();
                $mk->kaltmiete_monatlich($mv_id, $monat, $jahr);
                $ein_arr [$e] ['KALTMIETE'] = $mk->ausgangs_kaltmiete;

                $ein_arr [$e] ['KONTAKT'] = $kontaktdaten;
                $ein_arr [$e] ['EINHEIT_ID'] = $einheit_id;
                $mz = new miete ();
                $mz->mietkonto_berechnung_monatsgenau($mv_id, $jahr, $monat);
                $ein_arr [$e] ['MIETER_SALDO'] = $mz->erg;
            } else {
                $ein_arr [$e] ['MIETER'] = 'Leerstand';
            }
            /* Differenz Kaltmiete und Versprochene */
            if ($ein_arr [$e] ['V_MIETE'] != '0.00') {
                $ein_arr [$e] ['DIFF_KW'] = $ein_arr [$e] ['KALTMIETE'] - $ein_arr [$e] ['V_MIETE'];
            } else {
                $ein_arr [$e] ['DIFF_KW'] = '0.00';
            }

            foreach ($b_konten_arr as $b_key => $b_konto) {
                $this->get_b_konto_bez($profil_id, $b_konto);

                $buchung_von_d = $bericht_von_arr [$b_key];
                $buchung_von = date_german2mysql($buchung_von_d);
                $buchung_bis_d = $bericht_bis_arr [$b_key];
                $buchung_bis = date_german2mysql($buchung_bis_d);

                $ein_arr [$e] [$b_konto] ['EINHEIT'] = $this->get_kosten_von_bis_o_sum('Einheit', $einheit_id, $buchung_von, $buchung_bis, $gk_id, $b_konto);
                $ein_arr [$e] [$b_konto] ['ET'] = $this->get_kosten_von_bis_o_sum('Eigentuemer', $weg->eigentuemer_id, $buchung_von, $buchung_bis, $gk_id, $b_konto);

                if (!empty($ein_arr [$e] [$b_konto] ['EINHEIT']) && !empty($ein_arr [$e] [$b_konto] ['ET'])) {
                    $ein_arr [$e] ['KONTEN'] [$b_konto] = array_merge($ein_arr [$e] [$b_konto] ['EINHEIT'], $ein_arr [$e] [$b_konto] ['ET']);
                }

                if (!empty($ein_arr [$e] [$b_konto] ['EINHEIT']) && empty($ein_arr [$e] [$b_konto] ['ET'])) {
                    $ein_arr [$e] ['KONTEN'] [$b_konto] = $ein_arr [$e] [$b_konto] ['EINHEIT'];
                }
                if (empty($ein_arr [$e] [$b_konto] ['EINHEIT']) && !empty($ein_arr [$e] [$b_konto] ['ET'])) {
                    $ein_arr [$e] ['KONTEN'] [$b_konto] = $ein_arr [$e] [$b_konto] ['ET'];
                }

                $ein_arr [$e] ['KONTEN_VB'] [$b_konto] ['VON'] = $buchung_von_d;
                $ein_arr [$e] ['KONTEN_VB'] [$b_konto] ['BIS'] = $buchung_bis_d;

                unset ($ein_arr [$e] [$b_konto]);
            } // END FOR BUCHUNGSKONTEN
            /* Kopf */
            $pdf->ezText($ein_arr [$e] ['EINHEIT_KURZNAME'], 11);
            $pdf->ezText($ein_arr [$e] ['HAUS_STRASSE'] . ' ' . $ein_arr [$e] ['HAUS_NUMMER'] . ' ' . $ein_arr [$e] ['HAUS_PLZ'] . ' ' . $ein_arr [$e] ['HAUS_STADT'], 11);
            $pdf->ezText($ein_arr [$e] ['ET_NAMEN'], 11);

            if (isset ($ein_arr [$e] ['KONTEN'])) {
                foreach ($ein_arr [$e] ['KONTEN'] as $b_key => $b_konto) {
                    $this->get_b_konto_bez($profil_id, $b_key);

                    /* Tabellen für Konten */
                    $tmp_b_arr = $this->summieren_arr($ein_arr [$e] ['KONTEN'] [$b_key]);
                    $anz_tmp = count($tmp_b_arr);
                    if ($lang == 'en') {
                        $cols = array(
                            'DATUM' => "<b>Date</b>",
                            'VERWENDUNGSZWECK' => "<b>Description</b>",
                            'BETRAG' => "<b>Amount [€]</b>"
                        );
                        $b_von = date_german2mysql($ein_arr [$e] ['KONTEN_VB'] [$b_key] ['VON']);
                        $b_bis = date_german2mysql($ein_arr [$e] ['KONTEN_VB'] [$b_key] ['BIS']);
                        $titel = $this->kto_bez_en;
                        $tab_ue = "<b>[cost account: $b_key] $titel Period:$b_von $b_bis</b>";
                        $tmp_b_arr [$anz_tmp - 1] ['VERWENDUNGSZWECK'] = "<b>SUM</b>";
                    }

                    if ($lang == 'de') {
                        $cols = array(
                            'DATUM' => "<b>Datum</b>",
                            'VERWENDUNGSZWECK' => "<b>Beschreibung</b>",
                            'BETRAG' => "<b>Betrag [€]</b>"
                        );
                        $b_von = $ein_arr [$e] ['KONTEN_VB'] [$b_key] ['VON'];
                        $b_bis = $ein_arr [$e] ['KONTEN_VB'] [$b_key] ['BIS'];
                        $titel = $this->kto_bez_de;
                        $tab_ue = "<b>[Konto: $b_key] $titel Zeitraum: $b_von  $b_bis</b>";
                        $tmp_b_arr [$anz_tmp - 1] ['VERWENDUNGSZWECK'] = "<b>SUMME</b>";
                    }
                    $pdf->ezTable($tmp_b_arr, $cols, "$tab_ue", array(
                        'showHeadings' => 1,
                        'shaded' => 1,
                        'titleFontSize' => 8,
                        'fontSize' => 7,
                        'xPos' => 50,
                        'xOrientation' => 'right',
                        'width' => 500,
                        'cols' => array(
                            'DATUM' => array(
                                'justification' => 'right',
                                'width' => 50
                            ),
                            'BETRAG' => array(
                                'justification' => 'right',
                                'width' => 50
                            )
                        )
                    ));
                    $pdf->ezSetDy(-5); // abstand
                } // end foreach
            } // Ende Konten

            // $pdf->ezText($ein_arr[$e],11);
            $pdf->ezNewPage();
        } // END FOR EINHEITEN

        ob_end_clean();
        $pdf->ezStream();
    }

    function get_b_konto_bez($profil_id, $konto)
    {
        if (isset ($this->kto_bez_de)) {
            unset ($this->kto_bez_de);
            unset ($this->kto_bez_en);
        }
        $db_abfrage = "SELECT * FROM REPORT_PROFILE_K WHERE PROFIL_ID='$profil_id' && KONTO='$konto' ORDER BY DAT DESC LIMIT 0,1";
        $result = DB::select($db_abfrage);
        $row = $result[0];
        $this->kto_bez_de = $row ['BEZ_DE'];
        $this->kto_bez_en = $row ['BEZ_EN'];
    }

    function get_kosten_von_bis_o_sum($kos_typ, $kos_id, $von, $bis, $gk_id, $konto, $sort = 'ASC')
    {
        $db_abfrage = "SELECT * FROM `GELD_KONTO_BUCHUNGEN` WHERE `GELDKONTO_ID` = '$gk_id' AND DATUM BETWEEN '$von' and '$bis' AND `KOSTENTRAEGER_TYP` = '$kos_typ' AND `KOSTENTRAEGER_ID` = '$kos_id' AND `AKTUELL` = '1' && KONTENRAHMEN_KONTO='$konto' ORDER BY KONTENRAHMEN_KONTO, DATUM $sort";

        $result = DB::select($db_abfrage);
        return $result;
    }

    function summieren_arr($arr)
    {
        if (is_array($arr)) {
            $anz = count($arr);
            $sum = 0;
            for ($a = 0; $a < $anz; $a++) {
                $sum += $arr [$a] ['BETRAG'];
            }
            $arr [$anz] ['BETRAG'] = $sum;
            return $arr;
        }
    }
    
    function auszugtest3($et_id, $von = null, $bis = null, $saldo_et = '0.00')
    {
        $this->saldo_et = $saldo_et;
        $weg = new weg ();
        $einheit_id = $weg->get_einheit_id_from_eigentuemer($et_id);
        // $e = new einheit();
        // $e->get_einheit_info($einheit_id);
        $weg_et = new weg ();
        $weg_et->get_eigentumer_id_infos4($et_id);
        // echo '<pre>';
        // print_r($e);

        $gk = new geldkonto_info ();
        $gk->geld_konto_ermitteln('Objekt', $weg_et->objekt_id);

        /* OBJEKTDATEN */
        /* Garantiemonate Objekt */
        $d = new detail ();
        $garantie_mon_obj = $d->finde_detail_inhalt('Objekt', $weg_et->objekt_id, 'INS-Garantiemonate');
        if (!$garantie_mon_obj) {
            $garantie_mon_obj = 0;
        }

        /* Garantiemonate Objekt */
        $d = new detail ();
        $garantie_mon_et = $d->finde_detail_inhalt('Eigentuemer', $et_id, 'INS-ET-Garantiemonate');
        if (!isset ($garantie_mon_et)) {
            $garantie_mon_et = $garantie_mon_obj;
        }

        if ($garantie_mon_et == 0) {
            $garantie = 0;
        }

        if ($garantie_mon_et != 0) {
            $garantie = $garantie_mon_et;
        }

        /* ET DATEN */
        if ($weg->eigentuemer_bis == '0000-00-00') {
            $weg->eigentuemer_bis = date("Y-m-d");
        }

        if ($von == null) {
            $von = $weg->eigentuemer_von;
        }
        if ($bis == null) {
            $bis = $weg->eigentuemer_bis;
        }

        /* MIETVERTRAEGE ZEITRAUM ET */
        $mv_arr = $this->get_mv_et_zeitraum_arr($einheit_id, $von, $bis);
        $anz_mv = count($mv_arr);
        if (empty($mv_arr)) {
            echo "NO MV - NUR KOSTEN";
        }

        $zeit_arr = $this->monats_array($von, $bis);
        /* Durchlauf alle Monate */
        if (is_array($zeit_arr)) {
            $anz_m = count($zeit_arr);
            for ($m = 0; $m < $anz_m; $m++) {
                /* Garantiemonat */
                if ($m < $garantie) {
                    $zeit_arr [$m] ['GAR_MON'] = 'JA';
                } else {
                    $zeit_arr [$m] ['GAR_MON'] = 'NEIN';
                }

                /* Saldo Vormonat */
                $this->saldo_et_vm = $this->saldo_et;
                $zeit_arr [$m] ['SALDO_VM'] = $this->saldo_et_vm;

                $monat = $zeit_arr [$m] ['MONAT'];
                $jahr = $zeit_arr [$m] ['JAHR'];

                $m_von = "$jahr-$monat-01";
                $ltm = letzter_tag_im_monat($monat, $jahr);
                $m_bis = "$jahr-$monat-$ltm";

                $zeit_arr [$m] ['MIETER_M_SOLL'] = 0;

                $zeit_arr [$m] ['MIETER_ERG_SUM'] = 0;
                $zeit_arr [$m] ['SUM_MIETER_ZB'] = 0;
                $zeit_arr [$m] ['SUM_MIETER_NK'] = 0;
                $zeit_arr [$m] ['SUM_ET_BUCHUNGEN'] = 0;
                $zeit_arr [$m] ['SUM_EINHEIT_BUCHUNGEN'] = 0;

                /* Mieteinnahmen */
                for ($a = 0; $a < $anz_mv; $a++) {
                    $mv_id = $mv_arr [$a] ['MIETVERTRAG_ID'];
                    $mv = new mietvertraege ();
                    $mv->get_mietvertrag_infos_aktuell($mv_id);
                    $mk = new mietkonto ();
                    // $mk->kaltmiete_monatlich($mv_id,$monat,$jahr);
                    $mk->kaltmiete_monatlich_ink_vz($mv_id, $monat, $jahr);

                    $mz = new miete ();
                    $m_arr = $mz->get_monats_ergebnis($mv_id, $monat, $jahr);

                    $m_soll_arr = explode('|', $m_arr ['soll']);
                    if (isset ($m_soll_arr [1])) {
                        $m_arr ['soll_wm'] = $m_soll_arr [0];
                        $m_arr ['soll_mwst'] = $m_soll_arr [1];
                    } else {
                        $m_arr ['soll_wm'] = $m_arr ['soll'];
                        $m_arr ['soll_mwst'] = '0.00';
                    }
                    $nk = ($m_arr ['soll_wm'] * -1) - $mk->ausgangs_kaltmiete;
                    $zeit_arr [$m] ['MIETER'] [$a] ['MV_ID'] = $mv_id;

                    $zeit_arr [$m] ['MIETER'] [$a] ['M_NAME'] = $mv->personen_name_string;
                    $zeit_arr [$m] ['MIETER'] [$a] ['KM_SOLL'] = $mk->ausgangs_kaltmiete;
                    $zeit_arr [$m] ['MIETER'] [$a] ['NK_SOLL'] = $nk;
                    $zeit_arr [$m] ['MIETER'] [$a] ['WM_SOLL'] = $m_arr ['soll_wm'] * -1;

                    $zeit_arr [$m] ['MIETER_M_SOLL'] += $m_arr ['soll_wm'] * -1;

                    $zeit_arr [$m] ['MIETER'] [$a] ['MI_ERG'] = $m_arr ['erg'];

                    $zeit_arr [$m] ['MIETER_ERG_SUM'] += $m_arr ['erg'];

                    $zeit_arr [$m] ['MIETER'] [$a] ['MI_ZB'] = $m_arr ['zb'];
                    $zeit_arr [$m] ['SUM_MIETER_ZB'] += $m_arr ['zb'];
                    $zeit_arr [$m] ['SUM_MIETER_NK'] += $nk;
                } // ende MV*S

                /* Hausgeld Fixkosten */
                $weg = new weg ();
                $kosten_arr = $weg->get_monatliche_def($monat, $jahr, 'Einheit', $einheit_id);
                $anz_buchungen = count($kosten_arr);
                $sum_fixkosten = 0;
                for ($k = 0; $k < $anz_buchungen; $k++) {
                    // $txt = $kosten_arr[$k]['KOSTENKAT'];
                    $betrag = $kosten_arr [$k] ['SUMME'] * -1;
                    // $auszahlen = $sum_auszahlen+$betrag;
                    // $saldo_et += $betrag;
                    // echo "$txt $betrag<br>";
                    // $zeit_arr[$m]['HAUSGELD'][$txt] = $betragx;
                    // $this->saldo_et+=$betragx;
                    // $zeit_arr[$m]['SALDO_MONAT'] = $this->saldo_et;
                    $sum_fixkosten += $betrag;
                }

                if ($sum_fixkosten != 0) {
                    $zeit_arr [$m] ['FIXKOSTEN'] = nummer_komma2punkt(nummer_punkt2komma($sum_fixkosten));
                } else {
                    $zeit_arr [$m] ['FIXKOSTEN'] = nummer_komma2punkt(nummer_punkt2komma(($weg_et->einheit_qm_weg * 0.4) + 30));
                }

                /* Abzufragende Konten */
                $kokonten [] = '1023'; // Kosten zu Einheit
                $kokonten [] = '4180'; // Gewährte Minderungen
                $kokonten [] = '4280'; // Gerichtskosten
                $kokonten [] = '4281'; // Anwaltskosten MEA
                $kokonten [] = '4282'; // Gerichtsvollzieher
                $kokonten [] = '5010'; // Eigentümereinlagen
                $kokonten [] = '5020'; // ET Entnahmen
                $kokonten [] = '5021'; // Hausgeld
                $kokonten [] = '5400'; // Durch INS zu Erstatten
                $kokonten [] = '5500'; // INS Maklergebühr
                $kokonten [] = '5600'; // Mietaufhegungsvereinbarungen
                $kokonten [] = '6000'; // Hausgeldzahlungen
                $kokonten [] = '6010'; // Heizkosten
                $kokonten [] = '6020'; // Nebenkosten / Hausgeld
                $kokonten [] = '6030'; // IHR
                $kokonten [] = '6060'; // Verwaltergebühr

                /* Buchungen zu Einheit */
                $kosten_arr = $this->get_kosten_von_bis('Einheit', $einheit_id, $m_von, $m_bis, $gk->geldkonto_id);
                // print_r($kosten_arr);
                if (is_array($kosten_arr)) {
                    $anz_buchungen = count($kosten_arr);
                    for ($k = 0; $k < $anz_buchungen - 1; $k++) {
                        $datum = $kosten_arr [$k] ['DATUM'];
                        $txt = bereinige_string($kosten_arr [$k] ['VERWENDUNGSZWECK']);
                        $betrag = $kosten_arr [$k] ['BETRAG'];
                        $kkonto = $kosten_arr [$k] ['KONTENRAHMEN_KONTO'];
                        if (in_array($kkonto, $kokonten)) {
                            $zeit_arr [$m] ['EINHEIT'] [$k] ['DATUM'] = $datum;
                            $zeit_arr [$m] ['EINHEIT'] [$k] ['KTO'] = $kkonto;
                            $zeit_arr [$m] ['EINHEIT'] [$k] ['TXT'] = $txt;
                            $zeit_arr [$m] ['EINHEIT'] [$k] ['BETRAG'] = $betrag;
                            $zeit_arr [$m] ['SUM_EINHEIT_BUCHUNGEN'] += $betrag;
                        }

                        // $this->saldo_et+=$betrag;
                        // $zeit_arr[$m]['SALDO_MONAT'] = $this->saldo_et;
                    }
                } else {
                    $zeit_arr [$m] ['EINHEIT'] = array();
                }

                /* Buchungen zum ET */

                $kosten_arr = $this->get_kosten_von_bis('Eigentuemer', $et_id, $m_von, $m_bis, $gk->geldkonto_id);
                if (is_array($kosten_arr)) {
                    $anz_buchungen = count($kosten_arr);
                    for ($k = 0; $k < $anz_buchungen - 1; $k++) {
                        $datum = $kosten_arr [$k] ['DATUM'];
                        $txt = bereinige_string($kosten_arr [$k] ['VERWENDUNGSZWECK']);
                        $betrag = $kosten_arr [$k] ['BETRAG'];
                        $kkonto = $kosten_arr [$k] ['KONTENRAHMEN_KONTO'];
                        if (in_array($kkonto, $kokonten)) {
                            $zeit_arr [$m] ['ET'] [$k] ['DATUM'] = $datum;
                            $zeit_arr [$m] ['ET'] [$k] ['KTO'] = $kkonto;
                            $zeit_arr [$m] ['ET'] [$k] ['TXT'] = $txt;
                            $zeit_arr [$m] ['ET'] [$k] ['BETRAG'] = $betrag;
                            $zeit_arr [$m] ['SUM_ET_BUCHUNGEN'] += $betrag;
                        }
                        // $this->saldo_et+=$betrag;
                        // $zeit_arr[$m]['SALDO_MONAT'] = $this->saldo_et;
                    }
                }

                $zeit_arr [$m] ['SALDO_MONAT_ET1'] = ($zeit_arr [$m] ['SUM_MIETER_ZB'] - $zeit_arr [$m] ['SUM_MIETER_NK'] - $zeit_arr [$m] ['FIXKOSTEN']) + ($zeit_arr [$m] ['SUM_EINHEIT_BUCHUNGEN'] + $zeit_arr [$m] ['SUM_ET_BUCHUNGEN']);
                $zeit_arr [$m] ['SALDO_MONAT_ET'] = $zeit_arr [$m] ['SALDO_VM'] + ($zeit_arr [$m] ['SUM_MIETER_ZB'] - $zeit_arr [$m] ['SUM_MIETER_NK'] - $zeit_arr [$m] ['FIXKOSTEN']) + ($zeit_arr [$m] ['SUM_EINHEIT_BUCHUNGEN'] + $zeit_arr [$m] ['SUM_ET_BUCHUNGEN']);
                $this->saldo_et = $zeit_arr [$m] ['SALDO_MONAT_ET'];
                // $zeit_arr[$m]['SALDO_MONAT_MATH'] = $this->saldo_et;

                /* letzter Monat */
                if ($m == $anz_m - 1 && $zeit_arr [$m] ['MIETER_ERG_SUM'] > 0) {
                    $zeit_arr [$m] ['SALDO_MONAT_ET'] = $zeit_arr [$m] ['SALDO_MONAT_ET'] - $zeit_arr [$m] ['MIETER_ERG_SUM'];
                    $this->saldo_et = $zeit_arr [$m] ['SALDO_MONAT_ET'];
                }

                if ($m < $garantie && $this->saldo_et < 0) {
                    $zeit_arr [$m] ['SALDO_MONAT_INS'] = $this->saldo_et;
                }

                if ($m + 1 == $garantie) {
                    $zeit_arr [$m] ['SALDO_MONAT_ET'] = 0;
                    $this->saldo_et = 0;
                }
            } // ende monat
        } else {
            die ("Zeitraum falsch $von $bis");
        }
        $this->ausgabe_saldo_et15($et_id, $zeit_arr);
    }

    function ausgabe_saldo_et15($et_id, $arr)
    {
        $wegg = new weg ();
        $wegg->get_eigentumer_id_infos4($et_id);
        $wegg->empf_namen;

        $mon = count($arr);
        echo "<table>";
        echo "<tr><th>$wegg->einheit_kurzname  - $wegg->empf_namen</th><th>SOLL</th><th>IST</th><th>SALDO M</th><th>SALDO ET</th><th>SALDO INS</th></tr>";
        for ($a = 0; $a < $mon; $a++) {
            $monatnr = $a + 1;
            $monat = $arr [$a] ['MONAT'];
            $jahr = $arr [$a] ['JAHR'];
            $gar = $arr [$a] ['GAR_MON'];
            $saldo_vm = nummer_punkt2komma_t($arr [$a] ['SALDO_VM']);
            $m_m_soll = nummer_punkt2komma_t($arr [$a] ['MIETER_M_SOLL'] * -1);
            $m_erg_sum = nummer_punkt2komma_t($arr [$a] ['MIETER_ERG_SUM']);
            $m_sum_zb = nummer_punkt2komma_t($arr [$a] ['SUM_MIETER_ZB']);
            $m_sum_nk = nummer_punkt2komma_t($arr [$a] ['SUM_MIETER_NK'] * -1);

            $ein_sum_buchungen = nummer_punkt2komma_t($arr [$a] ['SUM_EINHEIT_BUCHUNGEN']);
            $ein_et_buchungen = nummer_punkt2komma_t($arr [$a] ['SUM_ET_BUCHUNGEN']);
            $sum_fix = nummer_punkt2komma_t($arr [$a] ['FIXKOSTEN'] * -1);
            $saldo_et = nummer_punkt2komma_t($arr [$a] ['SALDO_MONAT_ET']);
            $saldo_et1 = nummer_punkt2komma_t($arr [$a] ['SALDO_MONAT_ET1']);
            $saldo_ins = nummer_punkt2komma_t($arr [$a] ['SALDO_MONAT_INS']);

            // $saldo_et_math = nummer_punkt2komma_t($arr[$a]['SALDO_MONAT_MATH']);
            if ($gar == 'JA') {
                $bgcolor = "#FFB6C1";
            } else {
                $bgcolor = "#8FBC8F";
            }
            echo "<tr><td colspan=\"5\" align=\"center\" bgcolor=\"$bgcolor\">($monatnr. GARANTIE:$gar) <b> $monat.$jahr</b></td></tr>";
            echo "<tr><td colspan=\"4\"><b>SALDO VM</b></td><td><b>$saldo_vm</b></td></tr>";
            // echo "<tr><td >MIETER</td><td>$m_m_soll</td><td>$m_sum_zb</td><td>$m_erg_sum</td><td></td></tr>";

            if (isset ($arr [$a] ['MIETER'])) {
                echo "<tr><td><details><summary>MIETER BBBB</summary><ul>";
                $anz_bu = count($arr [$a] ['MIETER']);
                echo "<table>";
                echo "<tr><th>MIETER</th><th>KM SOLL</th><th>NK</th><th>WM</th><th>ZB</th><th>ERG</th></tr>";
                for ($bu = 0; $bu < $anz_bu; $bu++) {
                    $mname = $arr [$a] ['MIETER'] [$bu] ['M_NAME'];
                    $mi_zb = $arr [$a] ['MIETER'] [$bu] ['MI_ZB'];

                    // if(!empty($mi_zb) && $mi_zb!='0.00'){
                    $km_soll = $arr [$a] ['MIETER'] [$bu] ['KM_SOLL'];
                    $nk_soll = $arr [$a] ['MIETER'] [$bu] ['NK_SOLL'];
                    $wm_soll = $arr [$a] ['MIETER'] [$bu] ['WM_SOLL'];
                    $mi_erg = $arr [$a] ['MIETER'] [$bu] ['MI_ERG'];;

                    echo "<tr><td>$mname</td><td>$km_soll</td><td>$nk_soll</td><td>$wm_soll</td><td>$mi_zb</td><td>$mi_erg</td></tr>";
                    // }
                }
                echo "</table>";
                echo "</ul></details>";
            } else {
                echo "<tr><td>BUCHUNG MIETER";
            }

            echo "</td><td>$m_m_soll</td><td>$m_sum_zb</td><td>$m_erg_sum</td><td></td></tr>";

            echo "<tr><td>NEBENKOSTEN</td><td></td><td>$m_sum_nk</td><td></td><td></td></tr>";
            echo "<tr><td>FIXKOSTEN</td><td></td><td>$sum_fix</td><td></td><td></td></tr>";
            // echo "<tr><td>BUCHUNG EINHEIT</td><td></td><td>$ein_sum_buchungen</td><td></td><td></td></tr>";

            if (isset ($arr [$a] ['EINHEIT'])) {
                echo "<tr><td><details><summary>BUCHUNG EINHEIT</summary><ul>";
                $anz_bu = count($arr [$a] ['EINHEIT']);
                echo "<table>";
                for ($bu = 0; $bu < $anz_bu; $bu++) {
                    $kto = $arr [$a] ['EINHEIT'] [$bu] ['KTO'];
                    $datum = $arr [$a] ['EINHEIT'] [$bu] ['DATUM'];
                    $txt = $arr [$a] ['EINHEIT'] [$bu] ['TXT'];
                    $b_betrag = $arr [$a] ['EINHEIT'] [$bu] ['BETRAG'];
                    echo "<tr><td>$datum</td><td>$kto</td><td>$txt</td><td>$b_betrag</td></tr>";
                }
                echo "</table>";
                echo "</ul></details>";
            } else {
                echo "<tr><td>BUCHUNG EINHEIT";
            }

            echo "</td><td></td><td>$ein_sum_buchungen</td><td></td><td></td></tr>";

            if (isset ($arr [$a] ['ET'])) {
                echo "<tr><td><details><summary>BUCHUNG ET</summary><ul>";
                $anz_bu = count($arr [$a] ['ET']);
                echo "<table>";
                for ($bu = 0; $bu < $anz_bu; $bu++) {
                    $kto = $arr [$a] ['ET'] [$bu] ['KTO'];
                    $datum = $arr [$a] ['ET'] [$bu] ['DATUM'];
                    $txt = $arr [$a] ['ET'] [$bu] ['TXT'];
                    $b_betrag = $arr [$a] ['ET'] [$bu] ['BETRAG'];
                    echo "<tr><td>$datum</td><td>$kto</td><td>$txt</td><td>$b_betrag</td></tr>";
                }
                echo "</table>";
                echo "</ul></details>";
            } else {
                echo "<tr><td>BUCHUNG ET";
            }

            echo "</td><td></td><td>$ein_et_buchungen</td><td></td><td></td></tr>";

            // echo "<tr><td>SALDEN MIETER</td><td>$m_erg_sum</td><td></td></tr>";
            echo "<tr><td><b>SALDO MONAT ET</b></td><td></td><td><b>$saldo_et1</b></td><td></td><td><b>$saldo_et</b></td></tr>";
            echo "<tr><td><b>SALDO MONAT INS</b></td><td></td><td><b></b></td><td></td><td></td><td><b>$saldo_ins</b></td></tr>";
            echo "<tr><td colspan=\"6\"><hr></td></tr>";
        }
        echo "</table>";

        if (request()->has('pdf')) {
            ob_clean(); // ausgabepuffer leeren
            $pdf = new Cezpdf ('a4', 'portrait');
            $bpdf = new b_pdf ();
            $bpdf->b_header($pdf, 'Partner', session()->get('partner_id'), 'portrait', 'Helvetica.afm', 6);

            $cols = array(
                'MONAT' => "Monat",
                'JAHR' => "Jahr",
                'GAR_MON' => "Gar.",
                'SUM_MIETER_ZB' => 'ZB',
                'SUM_MIETER_NK' => 'NK',
                'SUM_ET_BUCHUNGEN' => 'ET',
                'SUM_EINHEIT_BUCHUNGEN' => 'FLAT',
                'FIXKOSTEN' => 'FIX',
                'SALDO_MONAT_ET' => 'SALDOET',
                'SALDO_MONAT_ET1' => 'SALDOET1',
                'SALDO_MONAT_INS' => 'S_INS'
            );

            // $seit_monat = monat2name($drucken_m);
            // $pdf->ezTable($arr);
            $pdf->ezTable($arr, $cols, "Mietkontenblatt seit $seit_monat $drucken_j", array(
                'showHeadings' => 1,
                'shaded' => 0,
                'titleFontSize' => 8,
                'fontSize' => 7,
                'xPos' => 50,
                'xOrientation' => 'right',
                'width' => 500,
                'rowGap' => 1,
                'cols' => array(
                    'DATUM' => array(
                        'justification' => 'right',
                        'width' => 50
                    ),
                    'BEMERKUNG' => array(
                        'justification' => 'left',
                        'width' => 300
                    ),
                    'BETRAG' => array(
                        'justification' => 'right',
                        'width' => 75
                    ),
                    'SALDO' => array(
                        'justification' => 'right',
                        'width' => 75
                    )
                )
            ));
            ob_end_clean(); // ausgabepuffer leeren
            $pdf->ezStream();
        }
    }
    
    function parse_auszug($upload_file)
    {
        $file = file($upload_file);
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
        return $zeile1;
    }

    function form_export_objekte()
    {
        $o = new objekt ();
        $arr = $o->liste_aller_objekte_kurz();
        $anz = count($arr);
        $f = new formular ();
        $f->erstelle_formular('Objekte für Export wählen', null);
        $f->hidden_feld('option', 'exp_obj');
        $f->send_button('sndBtn', 'ALS CSV EXPORTIEREN');
        echo "<table>";
        echo "<tr>";
        $z = 1;
        for ($a = 0; $a < $anz; $a++) {
            $o_id = $arr [$a] ['OBJEKT_ID'];
            $o_kn = $arr [$a] ['OBJEKT_KURZNAME'];
            echo "<td>";
            $f->check_box_js('objekte_arr[]', $o_id, $o_kn, null, 'jhchecked');
            echo "</td>";
            if ($z == '15') {
                echo "</tr><tr>";
                $z = 0;
            }
            $z++;
        }
        echo "</tr></table>";
        $f->ende_formular();
    }
} // end class listen