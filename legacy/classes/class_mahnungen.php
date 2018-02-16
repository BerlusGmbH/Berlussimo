<?php

class mahnungen
{

    /* Liefert Anzeige mit Mietern mit Schulden */
    public $datum_l_zahl_e;
    public $saldo_zahl_e;
    public $saldo_l_mahnung;
    public $mahn_geb;
    public $datum_l_mahnung;

    function finde_schuldner($schulder_typ)
    {
        $f = new formular ();
        $f->erstelle_formular('mahnen', '');
        $f->fieldset('Mahnungen und Zahlungserinnerungen', 'mze');
        if (request()->has('send_mahnen') or request()->has('send_erinnern')) {
            if (!is_array(request()->input('mahnliste'))) {
                throw new \App\Exceptions\MessageException(
                    new \App\Messages\ErrorMessage('Bitte wählen Sie einen Mieter.')
                );
            } else {
                $mahnliste_auswahl = request()->input('mahnliste');
                $anz = count($mahnliste_auswahl);
                for ($a = 0; $a < $anz; $a++) {
                    $mv_id = $mahnliste_auswahl [$a];
                    $f->hidden_feld('mahnliste[]', $mv_id);
                }
            }
            $this->form_datum_konto('Datum - Zahlungsfrist', 'datum', 'dz');
            if (request()->has('send_mahnen')) {
                $f->text_feld('Mahngebühr', 'mahngebuehr', '0,00', 10, 'mg', '');
                $f->hidden_feld('mietvertrag_raus', 'mahnen_mehrere');
                $f->send_button('send_mehrere', 'SERIENBRIEF MAHNUNGEN ERSTELLEN');
            }
            if (request()->has('send_erinnern')) {
                $f->hidden_feld('mietvertrag_raus', 'erinnern_mehrere');
                echo "<br>";
                $f->send_button('send_mehrere', 'SERIENBRIEF ZAHLUNGSERINNERUNG ERSTELLEN');
            }
        } else {

            $f = new formular ();
            echo "<table class='striped'>";
            echo "<thead><th>";
            $f->check_box_js_alle('mahnliste', 'mahnliste_alle', '', 'Alle', '', '', 'mahnliste');
            echo "</th><th></th><th>MIETER</th><th>SALDO</th><th>1. FRIST</th><th>2. FRIST</th><th>OPTIONEN</th></thead>";
            if ($schulder_typ == 'aktuelle') {
                $akt_mvs = $this->finde_aktuelle_mvs();
            }
            if ($schulder_typ == 'ausgezogene') {
                $akt_mvs = $this->finde_ausgezogene_mvs();
            }
            if ($schulder_typ == '') {
                $akt_mvs = $this->finde_alle_mvs();
            }
            if (!empty($akt_mvs)) {
                $anzahl_mvs = count($akt_mvs);

                $gesamt_verlust = 0;
                $zeile = 0;
                for ($a = 0; $a < $anzahl_mvs; $a++) {
                    $mz = new miete ();
                    $mv_id = $akt_mvs [$a] ['MIETVERTRAG_ID'];

                    if (!$this->check_berechnung_heute($mv_id)) {
                        $mz->mietkonto_berechnung($mv_id);
                        $saldo = $mz->erg;
                        $this->update_mahnliste_heute($mv_id, $saldo);
                    } else {
                        $saldo = $this->saldo_mahnliste_heute($mv_id);
                    }

                    $zeile = $zeile + 1;

                    if ($saldo < '0.00') {
                        $this->check_letzte_mahnung($mv_id);
                        $this->check_letzte_zahlungserinnerung($mv_id);
                        $saldo_a = nummer_punkt2komma($saldo);
                        $mvs = new mietvertraege ();
                        $mvs->get_mietvertrag_infos_aktuell($mv_id);

                        echo "<tr><td>";

                        /* Mahnsperre */
                        $dd = new detail ();
                        $mahnsperre = $dd->finde_detail_inhalt('MIETVERTRAG', $mv_id, 'Mahnsperre');
                        $link_mkb = "<a href='" . route('web::mietkontenblatt::legacy', ['anzeigen' => 'mk_pdf', 'mietvertrag_id' => $mv_id]) . "'>Mietkonto</a>";
                        $link_ue = "<a href='" . route('web::uebersicht::legacy', ['anzeigen' => 'einheit', 'einheit_id' => $mvs->einheit_id, 'mietvertrag_id' => $mv_id]) . "'>Übersicht</a>";

                        if (empty ($mahnsperre)) {

                            $f->check_box_js1('mahnliste[]', 'mahnliste' . $mv_id, $mv_id, "&nbsp;$mvs->einheit_kurzname&nbsp;", '', '');

                            $link_erinnerung = "<a href='" . route('web::mietvertraege::legacy', ['mietvertrag_raus' => 'zahlungserinnerung', 'mietvertrag_id' => $mv_id]) . "'>Erinnerung PDF</a>";
                            $link_mahnung = "<a href='" . route('web::mietvertraege::legacy', ['mietvertrag_raus' => 'mahnung', 'mietvertrag_id' => $mv_id]) . "'>Mahnung PDF</a>";
                        } else {
                            $mahnsperre = "<p style=\"color:red;\"><b>Mahnsperre:</b> $mahnsperre</p>";
                            $link_erinnerung = '';
                            $link_mahnung = "<b>Mahnsperre:</b> $mahnsperre";
                        }
                        echo "</td><td>$link_mkb<hr>$link_ue</td><td>$mvs->personen_name_string<br>$mahnsperre</td>";

                        if (isset ($this->datum_l_mahnung)) {
                            echo "<td>$saldo_a</td><td>$this->datum_l_zahl_e $this->saldo_zahl_e</td><td>$this->datum_l_mahnung $this->saldo_l_mahnung + $this->mahn_geb</td><td></td></tr>";
                        } else {
                            echo "<td>$saldo_a</td><td>$this->datum_l_zahl_e $this->saldo_zahl_e</td><td></td><td>$link_erinnerung $link_mahnung</td></tr>";
                        }
                        $gesamt_verlust = $gesamt_verlust + $saldo;
                    }

                    unset ($mz);
                }
                $gesamt_verlust_a = nummer_punkt2komma($gesamt_verlust);

                echo "<tr><td colspan=\"2\"><b>Summe Schulden</b></td><td><b>$gesamt_verlust_a €</td><td></td><td></td><td></td><td></td></tr>";
                echo "<tr><td colspan=\"3\">";
                $f->send_button_js('send_mahnen', 'Mahnen', '');
                echo "</td><td colspan=\"3\">";
                $f->send_button_js('send_erinnern', 'Erinnern', '');
                echo "</td></tr>";

                echo "</table>";
            } else {
                echo "Keine vermieteten Einheiten";
            }
        }
        $f->ende_formular();
        $f->fieldset_ende();
    }

    function form_datum_konto($label, $name, $id)
    {
        if (!session()->has('objekt_id')) {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\ErrorMessage('Bitte wählen Sie ein Objekt.')
            );
        }
        $f = new formular ();

        if (session()->has('mahn_datum')) {
            $datum = session()->get('mahn_datum');
        } else {
            $datum = '';
        }
        $f->datum_feld($label, $name, $datum, $id);
        $g = new geldkonto_info ();
        $objekt_id = session()->get('objekt_id');
        $g->geld_konten_ermitteln('Objekt', $objekt_id);
    }

    function finde_aktuelle_mvs()
    {
        if (session()->has('objekt_id')) {
            $objekt_id = session()->get('objekt_id');
            $my_arr = DB::select("SELECT MIETVERTRAG_ID, EINHEIT.EINHEIT_ID, HAUS.HAUS_ID, OBJEKT.OBJEKT_ID FROM MIETVERTRAG 
LEFT JOIN EINHEIT ON (MIETVERTRAG.EINHEIT_ID=EINHEIT.EINHEIT_ID)
LEFT JOIN HAUS ON (EINHEIT.HAUS_ID=HAUS.HAUS_ID)
LEFT JOIN OBJEKT ON (HAUS.OBJEKT_ID=OBJEKT.OBJEKT_ID)

WHERE MIETVERTRAG_AKTUELL = '1' && EINHEIT_AKTUELL = '1' && HAUS_AKTUELL = '1' && OBJEKT_AKTUELL = '1'  && OBJEKT.OBJEKT_ID='$objekt_id' && ( MIETVERTRAG_BIS > CURdate( )
OR MIETVERTRAG_BIS = '0000-00-00' ) ORDER BY EINHEIT_KURZNAME ASC");
        } else {
            $my_arr = DB::select("SELECT MIETVERTRAG_ID, EINHEIT.EINHEIT_ID FROM MIETVERTRAG LEFT JOIN EINHEIT ON (MIETVERTRAG.EINHEIT_ID=EINHEIT.EINHEIT_ID)
WHERE MIETVERTRAG_AKTUELL = '1' && EINHEIT_AKTUELL = '1' && ( MIETVERTRAG_BIS > CURdate()
OR MIETVERTRAG_BIS = '0000-00-00' ) ORDER BY EINHEIT_KURZNAME ASC");
        }
        return $my_arr;
    }

    function finde_ausgezogene_mvs()
    {
        if (session()->has('objekt_id')) {
            $objekt_id = session()->get('objekt_id');
            $result = DB::select("SELECT MIETVERTRAG_ID, EINHEIT.EINHEIT_ID, HAUS.HAUS_ID, OBJEKT.OBJEKT_ID  FROM MIETVERTRAG  LEFT JOIN EINHEIT ON (MIETVERTRAG.EINHEIT_ID=EINHEIT.EINHEIT_ID)
LEFT JOIN HAUS ON (EINHEIT.HAUS_ID=HAUS.HAUS_ID) LEFT JOIN OBJEKT ON (HAUS.OBJEKT_ID=OBJEKT.OBJEKT_ID) WHERE MIETVERTRAG_AKTUELL = '1' && EINHEIT_AKTUELL = '1' && HAUS_AKTUELL = '1' && OBJEKT_AKTUELL = '1'  && OBJEKT.OBJEKT_ID='$objekt_id'  && MIETVERTRAG_BIS < CURdate() && MIETVERTRAG_BIS != '0000-00-00' ORDER BY EINHEIT_ID ASC");
        } else {
            $result = DB::select("SELECT MIETVERTRAG_ID, EINHEIT_ID FROM MIETVERTRAG WHERE MIETVERTRAG_AKTUELL = '1' && MIETVERTRAG_BIS < CURdate() && MIETVERTRAG_BIS != '0000-00-00' ORDER BY EINHEIT_ID ASC");
        }
        return $result;
    }

    function finde_alle_mvs()
    {
        if (session()->has('objekt_id')) {
            $objekt_id = session()->get('objekt_id');
            $result = DB::select("SELECT MIETVERTRAG_ID, EINHEIT.EINHEIT_ID, HAUS.HAUS_ID, OBJEKT.OBJEKT_ID  FROM MIETVERTRAG LEFT JOIN EINHEIT ON (MIETVERTRAG.EINHEIT_ID=EINHEIT.EINHEIT_ID) LEFT JOIN HAUS ON (EINHEIT.HAUS_ID=HAUS.HAUS_ID) LEFT JOIN OBJEKT ON (HAUS.OBJEKT_ID=OBJEKT.OBJEKT_ID) WHERE MIETVERTRAG_AKTUELL = '1' && EINHEIT_AKTUELL = '1' && HAUS_AKTUELL = '1' && OBJEKT_AKTUELL = '1'  && OBJEKT.OBJEKT_ID='$objekt_id'   ORDER BY EINHEIT_ID ASC");
        } else {
            $result = DB::select("SELECT MIETVERTRAG_ID, EINHEIT_ID FROM MIETVERTRAG WHERE MIETVERTRAG_AKTUELL = '1' ORDER BY EINHEIT_ID ASC");
        }
        return $result;
    }

    function check_berechnung_heute($mietvertrag_id)
    {
        $datum = date("Y-m-d");
        $result = DB::select("SELECT * FROM `MIETER_MAHNLISTEN`  WHERE DATUM='$datum' && MIETVERTRAG_ID='$mietvertrag_id' && AKTUELL='1'");
        return !empty($result);
    }

    function update_mahnliste_heute($mv_id, $saldo)
    {
        $datum = date("Y-m-d");
        $db_abfrage = "INSERT INTO MIETER_MAHNLISTEN VALUES (NULL,'$datum', '$mv_id', '$saldo','0000-00-00','0000-00-00','0.00', '1')";
        DB::insert($db_abfrage);
    }

    function saldo_mahnliste_heute($mietvertrag_id)
    {
        $datum = date("Y-m-d");
        $result = DB::select("SELECT SALDO FROM `MIETER_MAHNLISTEN`  WHERE DATUM='$datum' && MIETVERTRAG_ID='$mietvertrag_id' ORDER BY DATUM DESC LIMIT 0,1");
        if (!empty($result)) {
            $row = $result[0];
            return $row ['SALDO'];
        }
    }

    function check_letzte_mahnung($mietvertrag_id)
    {
        unset ($this->datum_l_mahnung);
        unset ($this->saldo_l_mahnung);
        unset ($this->mahn_geb);
        $result = DB::select("SELECT ZAHLUNGSFRIST_M, SALDO, MAHN_GEB  FROM `MIETER_MAHNLISTEN` WHERE MIETVERTRAG_ID='$mietvertrag_id' && ZAHLUNGSFRIST_M!='0000-00-00' ORDER BY DATUM DESC LIMIT 0,1");
        if (!empty($result)) {
            $row = $result[0];
            $this->datum_l_mahnung = date_mysql2german($row ['ZAHLUNGSFRIST_M']);
            $this->saldo_l_mahnung = nummer_punkt2komma(abs($row ['SALDO']));
            $this->mahn_geb = nummer_punkt2komma(abs($row ['MAHN_GEB']));
        } else {
            $this->datum_l_mahnung = ' ';
            $this->saldo_l_mahnung = ' ';
            $this->mahn_geb = 0.00;
        }
    }

    /* Liefert Anzeige mit Mietern mit Schulden */

    function check_letzte_zahlungserinnerung($mietvertrag_id)
    {
        unset ($this->datum_l_zahl_e);
        unset ($this->saldo_zahl_e);

        $result = DB::select("SELECT ZAHLUNGSFRIST_Z, SALDO  FROM `MIETER_MAHNLISTEN` WHERE MIETVERTRAG_ID='$mietvertrag_id' && ZAHLUNGSFRIST_Z!='0000-00-00' ORDER BY DATUM DESC LIMIT 0,1");
        if (!empty($result)) {
            $row = $result[0];
            $this->datum_l_zahl_e = date_mysql2german($row ['ZAHLUNGSFRIST_Z']);
            $this->saldo_zahl_e = nummer_punkt2komma($row ['SALDO']);
        } else {
            $this->datum_l_zahl_e = ' ';
            $this->saldo_zahl_e = '';
            return false;
        }
    }

    // ###########

    function finde_schuldner_pdf($schulder_typ)
    {
        ob_clean();
        $pdf = new Cezpdf ('a4', 'portrait');
        $bpdf = new b_pdf ();
        $bpdf->b_header($pdf, 'Partner', session()->get('partner_id'), 'portrait', 'Helvetica.afm', 6);

        if ($schulder_typ == 'aktuelle') {
            $akt_mvs = $this->finde_aktuelle_mvs();
        }
        if ($schulder_typ == 'ausgezogene') {
            $akt_mvs = $this->finde_ausgezogene_mvs();
        }
        if ($schulder_typ == '') {
            $akt_mvs = $this->finde_alle_mvs();
        }
        if (!empty($akt_mvs)) {
            $anzahl_mvs = count($akt_mvs);
            $monat = date("m");

            $gesamt_verlust = 0;
            $zaehler = 0;
            for ($a = 0; $a < $anzahl_mvs; $a++) {
                $mz = new miete ();
                $mv_id = $akt_mvs [$a] ['MIETVERTRAG_ID'];

                $mz->mietkonto_berechnung($mv_id);
                $zeile = $zeile + 1;
                $saldo = $mz->erg;

                if ($saldo < '0.00') {

                    $saldo_a = nummer_punkt2komma($saldo);
                    $table_arr [$zaehler] ['SALDO'] = "$saldo_a €";

                    $mvs = new mietvertraege ();
                    $mvs->get_mietvertrag_infos_aktuell($mv_id);
                    $table_arr [$zaehler] ['EINHEIT'] = $mvs->einheit_kurzname;
                    $table_arr [$zaehler] ['MIETER'] = $mvs->personen_name_string_u;

                    $dd = new detail ();
                    $mahnsperre = $dd->finde_detail_inhalt('MIETVERTRAG', $mv_id, 'Mahnsperre');
                    $table_arr [$zaehler] ['MAHNEN'] = bereinige_string($mahnsperre);

                    $gesamt_verlust = $gesamt_verlust + $saldo;
                    $zaehler++;
                }

                unset ($mz);
            }
            $gesamt_verlust_a = nummer_punkt2komma($gesamt_verlust);
            $anzahl_zeilen = count($table_arr);
            $datum_h = date("d.m.Y");
            $table_arr [$anzahl_zeilen] ['EINHEIT'] = "<b>$datum_h</b>";
            $table_arr [$anzahl_zeilen] ['MIETER'] = "<b>Summe </b>";
            $table_arr [$anzahl_zeilen] ['SALDO'] = "<b>$gesamt_verlust_a €</b>";
            /* PDF AUSGABE */
            $cols = array(
                'EINHEIT' => "Einheit",
                'MIETER' => "Mieter",
                'SALDO' => "Saldo",
                'MAHNEN' => "Mahnsperre"
            );
            $pdf->ezTable($table_arr, $cols, "Mahnliste $mvs->objekt_kurzname vom $datum_h ", array(
                'showHeadings' => 1,
                'shaded' => 0,
                'titleFontSize' => 8,
                'fontSize' => 7,
                'xPos' => 50,
                'xOrientation' => 'right',
                'width' => 500,
                'cols' => array(
                    'EINHEIT' => array(
                        'justification' => 'right',
                        'width' => 50
                    ),
                    'MIETER' => array(
                        'justification' => 'left',
                        'width' => 200
                    ),
                    'SALDO' => array(
                        'justification' => 'right',
                        'width' => 50
                    )
                )
            ));
            ob_end_clean(); // ausgabepuffer leeren
            $pdf->ezStream();
        }
    }

    /* finde aktuelle MIetverträge */

    function finde_guthaben_mvs()
    {
        $akt_mvs = $this->finde_alle_mvs();

        if (!empty($akt_mvs)) {
            $anzahl_mvs = count($akt_mvs);
            $jahr = date("Y");
            $monat = date("m");

            $e = new einheit ();
            $m = new mietvertrag (); // class mietvertrag aus berlussimo_class.php;
            $m1 = new mietvertraege (); // class mietvertraege NEUE KLASSE;
            $gesamt_guthaben = 0;
            for ($a = 0; $a < $anzahl_mvs; $a++) {
                $mv_id = $akt_mvs [$a] ['MIETVERTRAG_ID'];
                $e_id = $akt_mvs [$a] ['EINHEIT_ID'];
                $mz = new miete ();
                $mz->mietkonto_berechnung_monatsgenau($mv_id, $jahr, $monat);
                $saldo = $mz->erg;
                if ($saldo > 0) {
                    $saldo_a = nummer_punkt2komma($saldo);
                    $e->get_einheit_info($e_id);
                    $mv_ids_arr = $m->get_personen_ids_mietvertrag($mv_id);
                    $personen_namen_string = $m1->mv_personen_als_string($mv_ids_arr);
                    echo "<b>$e->einheit_kurzname</b> ";
                    echo $personen_namen_string . ' ';
                    echo " Saldo: $saldo_a €<br>";
                    echo "<hr>";
                    $gesamt_guthaben = $gesamt_guthaben + $saldo;
                }
            }
            unset ($mz->erg);

            echo "<h1>Summe Guthaben: $gesamt_guthaben €</h1>";
        } else {
            echo "Keine vermieteten Einheiten";
        }
    }

    /* finde aktuelle MIetverträge */

    function zahlungserinnerung_pdf($mv_id, $fristdatum, $geldkonto_id)
    {
        ob_end_clean(); // ausgabepuffer leeren
        $pdf = new Cezpdf ('a4', 'portrait');
        $bpdf = new b_pdf ();
        $bpdf->b_header($pdf, 'Partner', session()->get('partner_id'), 'portrait', 'Helvetica.afm', 6);

        // ###ANSCHREIBEN#####
        $text_schrift = 'pdfclass/fonts/Arial.afm';

        $mv = new mietvertraege ();
        $mz = new miete ();
        $d = new detail ();
        $e = new einheit ();

        $mz->mietkonto_berechnung($mv_id);
        $saldo = $mz->erg;

        $mv->get_mietvertrag_infos_aktuell($mv_id);
        $e->get_einheit_info($mv->einheit_id);
        $p = new person ();
        if ($mv->anzahl_personen == 1) {
            $p->get_person_infos($mv->personen_ids ['0'] ['PERSON_MIETVERTRAG_PERSON_ID']);
            $geschlecht = $d->finde_person_geschlecht($mv->personen_ids [0] ['PERSON_MIETVERTRAG_PERSON_ID']);
            if ($geschlecht == 'weiblich') {
                $anrede_p = 'geehrte Frau';
            }
            if ($geschlecht == 'männlich') {
                $anrede_p = 'geehrter Herr';
            }
            $anrede = $anrede . "$anrede_p $p->person_nachname,";
            $personen_anrede [0] ['anrede'] = $anrede;
            $personen_anrede [0] ['geschlecht'] = $geschlecht;

        }
        if ($mv->anzahl_personen > 1) {

            for ($a = 0; $a < $mv->anzahl_personen; $a++) {
                // $anrede_p = $d->finde_person_anrede($mv->personen_ids[$a]['PERSON_MIETVERTRAG_PERSON_ID']);
                $p->get_person_infos($mv->personen_ids [$a] ['PERSON_MIETVERTRAG_PERSON_ID']);
                $geschlecht = $d->finde_person_geschlecht($mv->personen_ids [$a] ['PERSON_MIETVERTRAG_PERSON_ID']);
                if ($geschlecht == 'weiblich') {
                    $anrede_p = 'geehrte Frau';
                }
                if ($geschlecht == 'männlich') {
                    $anrede_p = 'geehrter Herr';
                }
                $anrede = "$anrede_p $p->person_nachname,";
                $personen_anrede [$a] ['anrede'] = $anrede;
                $personen_anrede [$a] ['geschlecht'] = $geschlecht;
            }
        }
        // echo '<pre>';
        $personen_anreden = array_sortByIndex($personen_anrede, 'geschlecht', SORT_DESC);
        // print_r($personen_anreden);

        $pdf->selectFont($text_schrift);
        for ($b = 0; $b < $mv->anzahl_personen; $b++) {
            $anrede_p = $personen_anreden [$b] ['anrede'];
            if ($b < 1) {
                $anrede = "Sehr $anrede_p\n";
            } else {
                $anrede = $anrede . "sehr $anrede_p\n"; // \n neue zeile in pdf
            }
        }

        /*
		 * $pdf->addText(42, 680,12,"Objekt: $mv->objekt_kurzname");
		 * $pdf->addText(42, 665,12,"Einheit: $mv->einheit_kurzname");
		 * $pdf->addText(42, 650,12,"Mietvertragsnr: $mv->mietvertrag_id");
		 * $pdf->addText(42, 635,12,"Mieter: $mv->personen_name_string");
		 * #$pdf->addText(42, 615,12,"$anrede");
		 */
        $pdf->ezSetDy(-15);
        $pdf->ezSetCmMargins(3, 3, 3, 3);
        $pdf->ezText("$mv->personen_name_string_u\n$e->haus_strasse $e->haus_nummer\n\n$e->haus_plz $e->haus_stadt", 12);
        $pdf->ezSetDy(-60);
        $pdf->ezText("<b>Zahlungserinnerung!!!</b>", 12);
        $pdf->ezSetDy(13);
        $datum_heute = date("d.m.Y");
        $pdf->ezText("Berlin, $datum_heute", 12, array(
            'justification' => 'right'
        ));
        $pdf->ezText("Objekt: $e->haus_strasse $e->haus_nummer, $e->haus_plz $e->haus_stadt", 12);
        $pdf->ezText("Einheit/Mieternummer: $mv->einheit_kurzname", 12);
        $pdf->ezSetDy(-12);
        /* Faltlinie */
        $pdf->setLineStyle(0.2);
        $pdf->line(5, 542, 20, 542);
        $pdf->ezText("$anrede", 12);
        $saldo = abs($saldo);
        $saldo_a = nummer_punkt2komma($saldo);

        $pdf->ezText("nach Durchsicht unserer Buchhaltungsunterlagen haben wir festgestellt, dass Ihr Mietkonto folgenden Rückstand in Höhe von <b>$saldo_a €</b> aufweist. Die konkreten Fehlbeträge entnehmen Sie bitte dem beigefügten Mietkonto.\n\nWir bitten Sie den genannten Betrag unter Angabe der bei uns geführten Mieternummer umgehend, spätestens jedoch bis zum\n", 12);
        $pdf->ezSetCmMargins(3, 3, 9, 3);
        $pdf->ezText("<b>$fristdatum</b>\n", 12);
        $pdf->ezSetCmMargins(3, 3, 3, 3);
        $pdf->setColor(0.0, 0.0, 0.0);
        $g = new geldkonto_info ();
        $g->geld_konto_details($geldkonto_id);

        $pdf->ezText("auf das Konto der $g->kredit_institut zu überweisen.\n\n", 12);
        $pdf->ezText("IBAN: $g->IBAN1\n", 12);
        $pdf->ezText("IBAN: $g->BIC\n\n", 12);
        $pdf->ezText("Für Rückfragen stehen wir Ihnen gerne zur Verfügung.\n\n", 12);
        $pdf->ezText("Mit freundlichen Grüßen\n", 12);
        $pdf->ezSetDy(15);
        $pdf->ezText("Wolfgang Wehrheim\n\n", 11);
        $pdf->ezText("Dieses Schreiben wurde maschinell erstellt und ist daher ohne Unterschrift gültig.\n", 12);
        $pdf->addInfo('Title', "Zahlungserinnerung $mv->personen_name_string");
        $pdf->addInfo('Author', Auth::user()->email);
        // ### MIETKONTENBLATT####
        $pdf->ezNewPage();
        $pdf->ezSetMargins(135, 70, 50, 50);
        $mz->mkb2pdf_mahnung_letzter_nullstand($pdf, $mv_id);
        $fristdatum = date_german2mysql($fristdatum);
        $this->update_zahlungsfrist_z($mv_id, $fristdatum, '-' . $saldo);
        /* PDF AUSGABE */
        $pdf->ezStream();
    }

    /* finde aktuelle MIetverträge */

    function update_zahlungsfrist_z($mv_id, $datum, $saldo)
    {
        $db_abfrage = "UPDATE MIETER_MAHNLISTEN SET ZAHLUNGSFRIST_Z='$datum' WHERE MIETVERTRAG_ID='$mv_id' && SALDO='$saldo' && ZAHLUNGSFRIST_Z='0000-00-00'";
        DB::update($db_abfrage);
    }

    function zahlungserinnerung_pdf_mehrere($mahnliste, $fristdatum, $geldkonto_id)
    {
        ob_clean(); // ausgabepuffer leeren
        $pdf = new Cezpdf ('a4', 'portrait');
        $bpdf = new b_pdf ();
        $bpdf->b_header($pdf, 'Partner', session()->get('partner_id'), 'portrait', 'Helvetica.afm', 6);
        $pdf->ezStopPageNumbers();
        // ###ANSCHREIBEN#####
        $anz_empfaenger = count($mahnliste);
        for ($ma = 0; $ma < $anz_empfaenger; $ma++) {
            $mv_id = $mahnliste [$ma];
            $mz = new miete ();
            $e = new einheit ();

            $mz->mietkonto_berechnung($mv_id);
            $saldo = $mz->erg;

            $mv = new mietvertraege ();
            $mv->get_mietvertrag_infos_aktuell($mv_id);
            $e->get_einheit_info($mv->einheit_id);

            /* Verzogene Mieter, abgelaufene MVS */
            if ($mv->mietvertrag_aktuell == '0') {
                /*
				 * Bei 2 Mietern überprüfen ob verzugsadresse gleich ist,
				 * wenn gleich, dann nur 1 Brief an beide, sonst getrennte Personen anschreiben
				 */

                /* Wenn Verzugsanschriften von beiden gleich */
                if ($this->mieter2_verzug_pruefen($mv_id)) {
                    /* Wie aktuelle Mieter behandeln, weil zur gleichen Adresse verzogen */

                    if ($mv->anzahl_personen == 1) {
                        $anschrift_pdf = ltrim($mv->postanschrift [0] ['anschrift']);
                    } else {
                        $anschrift_alle = $mv->postanschrift [0] ['adresse'];
                        $anschrift_pdf = "$mv->personen_name_string_u\n$anschrift_alle";
                    }

                    $pdf->ezSetMargins(135, 70, 50, 50);
                    $anrede = $mv->mv_anrede;

                    $pdf->ezSetDy(-15);
                    $pdf->ezText("$anschrift_pdf", 12);
                    $pdf->ezSetDy(-60);
                    $pdf->ezText("<b>Zahlungserinnerung</b>", 12);
                    $pdf->ezSetDy(13);
                    $datum_heute = date("d.m.Y");
                    $pdf->ezText("Berlin, $datum_heute", 12, array(
                        'justification' => 'right'
                    ));
                    $pdf->ezText("Objekt: $e->haus_strasse $e->haus_nummer, $e->haus_plz $e->haus_stadt", 12);
                    $pdf->ezText("Einheit/Mieternummer: $mv->einheit_kurzname", 12);
                    $pdf->ezSetDy(-12);
                    /* Faltlinie */
                    $pdf->setLineStyle(0.2);
                    $pdf->line(5, 542, 20, 542);
                    $pdf->ezText("$anrede", 12);
                    $saldo = abs($saldo);
                    $saldo_a = nummer_punkt2komma($saldo);

                    $pdf->ezText("nach Durchsicht unserer Buchhaltungsunterlagen mussten wir feststellen, dass Ihr Mietkonto einen Rückstand in Höhe von <b>$saldo_a €</b> aufweist. Die konkreten Fehlbeträge entnehmen Sie bitte dem beigefügten Mietkonto.\n\nBitte überweisen Sie den genannten Betrag unter Angabe der bei uns geführten Mieternummer umgehend, spätestens jedoch bis zum\n", 12);
                    $pdf->ezSetCmMargins(3, 3, 9, 3);
                    $pdf->ezText("<b>$fristdatum</b>\n", 12);
                    $pdf->ezSetMargins(135, 70, 50, 50);
                    $pdf->setColor(0.0, 0.0, 0.0);
                    $g = new geldkonto_info ();
                    $g->geld_konto_details($geldkonto_id);

                    $pdf->ezText("auf das Konto bei der $g->kredit_institut zu überweisen.", 12);
                    $pdf->ezSetDy(-10);
                    $pdf->ezText("<b>IBAN</b> $g->IBAN1\n", 12);
                    $pdf->ezText("<b>BIC</b> $g->BIC\n", 12);
                    $pdf->ezText("Für Rückfragen stehen wir Ihnen gerne zur Verfügung.\n\n", 12);
                    $pdf->ezText("Mit freundlichen Grüßen\n\n\n", 12);
                    $pdf->ezSetDy(15);
                    $pdf->ezText("Wolfgang Wehrheim\n\n", 11);
                    $pdf->ezText("Dieses Schreiben wurde maschinell erstellt und ist daher ohne Unterschrift gültig.\n", 12);
                    $pdf->addInfo('Title', "Zahlungserinnerung $mv->personen_name_string");
                    $pdf->addInfo('Author', Auth::user()->email);
                    $pdf->addInfo('Title', "Zahlungserinnerung $mv->personen_name_string");
                    $pdf->addInfo('Author', Auth::user()->email);
                    // ### MIETKONTENBLATT####
                    $pdf->ezNewPage();
                    $pdf->ezSetMargins(135, 70, 50, 50);
                    $mz->mkb2pdf_mahnung_letzter_nullstand($pdf, $mv_id);
                    $fristdatum_sql = date_german2mysql($fristdatum);
                    $this->update_zahlungsfrist_z($mv_id, $fristdatum_sql, '-' . $saldo);
                    /* PDF AUSGABE */
                    if ($ma < $anz_empfaenger - 1) {
                        $pdf->ezNewPage();
                    }
                }  // end mehrere zu einer Verzugsadresse verzogen aktueller MV

                else {

                    for ($mm = 0; $mm < $mv->anzahl_personen; $mm++) {
                        $anschrift_pdf = $mv->postanschrift [$mm] ['anschrift'];
                        $pdf->ezSetMargins(135, 70, 50, 50);
                        $mz = new miete ();
                        $e = new einheit ();

                        $jahr = date("Y");
                        $monat = date("m");
                        $mz->mietkonto_berechnung_monatsgenau($mv_id, $jahr, $monat);
                        $saldo = $mz->erg;
                        $e->get_einheit_info($mv->einheit_id);

                        $anrede = $mv->postanschrift [$mm] ['anrede'];
                        $pdf->ezSetDy(-15);
                        $pdf->ezText("$anschrift_pdf", 12);
                        $pdf->ezSetDy(-60);
                        $pdf->ezText("<b>Zahlungserinnerung</b>", 12);
                        $pdf->ezSetDy(13);
                        $datum_heute = date("d.m.Y");
                        $pdf->ezText("Berlin, $datum_heute", 12, array(
                            'justification' => 'right'
                        ));
                        $pdf->ezText("Objekt: $e->haus_strasse $e->haus_nummer, $e->haus_plz $e->haus_stadt", 12);
                        $pdf->ezText("Einheit/Mieternummer: $mv->einheit_kurzname", 12);
                        $pdf->ezSetDy(-12);
                        /* Faltlinie */
                        $pdf->setLineStyle(0.2);
                        $pdf->line(5, 542, 20, 542);
                        $pdf->ezText("$anrede", 12);
                        $saldo = abs($saldo);
                        $saldo_a = nummer_punkt2komma($saldo);

                        $pdf->ezText("nach Durchsicht unserer Buchhaltungsunterlagen mussten wir feststellen, dass Ihr Mietkonto einen Rückstand in Höhe von <b>$saldo_a €</b> aufweist. Die konkreten Fehlbeträge entnehmen Sie bitte dem beigefügten Mietkonto.\n\nBitte überweisen Sie den genannten Betrag unter Angabe der bei uns geführten Mieternummer umgehend, spätestens jedoch bis zum\n", 12);
                        $pdf->ezSetCmMargins(3, 3, 9, 3);
                        $pdf->ezText("<b>$fristdatum</b>\n", 12);
                        $pdf->ezSetMargins(135, 70, 50, 50);
                        $pdf->setColor(0.0, 0.0, 0.0);
                        $g = new geldkonto_info ();
                        $g->geld_konto_details($geldkonto_id);

                        $pdf->ezText("auf das Konto $g->kontonummer  bei der $g->kredit_institut, BLZ $g->blz\n", 12);
                        $pdf->ezText("zu überweisen.\n", 12);
                        $pdf->ezText("Für Rückfragen stehen wir Ihnen gerne zur Verfügung.\n\n", 12);
                        $pdf->ezText("Mit freundlichen Grüßen\n\n\n", 12);
                        $pdf->ezSetDy(15);
                        $pdf->ezText("Wolfgang Wehrheim\n\n", 11);
                        $pdf->ezText("Dieses Schreiben wurde maschinell erstellt und ist daher ohne Unterschrift gültig.\n", 12);
                        $pdf->addInfo('Title', "Zahlungserinnerung $mv->personen_name_string");
                        $pdf->addInfo('Author', Auth::user()->email);
                        // ### MIETKONTENBLATT####
                        $pdf->ezNewPage();
                        $pdf->ezSetMargins(135, 70, 50, 50);
                        $mz->mkb2pdf_mahnung_letzter_nullstand($pdf, $mv_id);
                        $fristdatum_sql = date_german2mysql($fristdatum);
                        $this->update_zahlungsfrist_z($mv_id, $fristdatum_sql, '-' . $saldo);
                        /* PDF AUSGABE */
                        $pdf->ezNewPage();
                    }
                }
            }
            /* Aktuelle Mieter */
            if ($mv->mietvertrag_aktuell == '1') {
                if (!$mv->postanschrift [0] ['anschrift']) {
                    $anschrift_pdf = "$mv->personen_name_string_u\n$e->haus_strasse $e->haus_nummer\n\n$e->haus_plz $e->haus_stadt";
                } else {
                    $anschrift_pdf = ltrim($mv->postanschrift [0] ['anschrift']);
                }

                $pdf->ezSetMargins(135, 70, 50, 50);
                $anrede = $mv->mv_anrede;

                $pdf->ezSetDy(-15);
                $pdf->ezText("$anschrift_pdf", 12);
                $pdf->ezSetDy(-60);
                $pdf->ezText("<b>Zahlungserinnerung</b>", 12);
                $pdf->ezSetDy(13);
                $datum_heute = date("d.m.Y");
                $pdf->ezText("Berlin, $datum_heute", 12, array(
                    'justification' => 'right'
                ));
                $pdf->ezText("Objekt: $e->haus_strasse $e->haus_nummer, $e->haus_plz $e->haus_stadt", 12);
                $pdf->ezText("Einheit/Mieternummer: $mv->einheit_kurzname", 12);
                $pdf->ezSetDy(-12);
                /* Faltlinie */
                $pdf->setLineStyle(0.2);
                $pdf->line(5, 542, 20, 542);
                $pdf->ezText("$anrede", 12);
                $saldo = abs($saldo);
                $saldo_a = nummer_punkt2komma($saldo);

                $pdf->ezText("nach Durchsicht unserer Buchhaltungsunterlagen haben wir festgestellt, dass Ihr Mietkonto folgenden Rückstand aufweist", 11);
                $pdf->ezSetCmMargins(3, 3, 8, 3);
                $pdf->ezText("<b>Mietrückstand: $saldo_a €</b>\n", 12);
                $pdf->ezSetMargins(135, 70, 50, 50);
                $pdf->setColor(0.0, 0.0, 0.0);
                $pdf->ezText("Die konkreten Fehlbeträge entnehmen Sie bitte dem beigefügten Mietkonto.\nWir bitten Sie, den genannten Betrag unter Angabe der bei uns geführten Mieternummer bis zum\n", 11);
                $pdf->ezSetCmMargins(3, 3, 9, 3);
                $pdf->ezText("<b>$fristdatum</b>\n", 12);
                $pdf->ezSetMargins(135, 70, 50, 50);
                $pdf->setColor(0.0, 0.0, 0.0);

                $g = new geldkonto_info ();
                $g->geld_konto_details($geldkonto_id);
                $pdf->ezSetMargins(135, 70, 50, 50);
                $pdf->ezText("auf das Konto bei der $g->kredit_institut zu überweisen.", 11);
                $pdf->ezText("\n<b>IBAN:</b> $g->IBAN1", 12);
                $pdf->ezText("<b>BIC:</b>   $g->BIC\n", 12);
                $pdf->ezText("Sollten Sie den Betrag in der Zwischenzeit bereits ausgegelichen haben, bitten wir Sie dieses Schreiben als gegenstandslos zu betrachten.\n\n", 11);

                $pdf->ezText("Für Rückfragen stehen wir Ihnen gerne zur Verfügung.\n", 11);
                $pdf->ezText("Mit freundlichen Grüßen\n\n\n", 11);

                if (session()->has('partner_id')) {
                    $pp = new partners ();
                    $pp->get_partner_name(session()->get('partner_id'));
                    $pdf->ezText("$pp->partner_name\nHausverwaltung\n", 11);
                } else {
                    $pdf->ezText("Ihre Hausverwaltung\n", 11);
                }
                $pdf->ezText("Dieses Schreiben wurde maschinell erstellt und ist daher ohne Unterschrift gültig.", 11);
                $pdf->addInfo('Title', "Zahlungserinnerung $mv->personen_name_string");
                $pdf->addInfo('Author', Auth::user()->email);
                // ### MIETKONTENBLATT####
                $pdf->ezNewPage();
                $pdf->ezSetMargins(135, 70, 50, 50);
                $mz->mkb2pdf_mahnung_letzter_nullstand($pdf, $mv_id);
                $fristdatum_sql = date_german2mysql($fristdatum);
                $this->update_zahlungsfrist_z($mv_id, $fristdatum_sql, '-' . $saldo);
                /* PDF AUSGABE */
                if ($ma < $anz_empfaenger - 1) {
                    $pdf->ezNewPage();
                }
            } // end aktueller MV
        } // end for
        ob_end_clean(); // ausgabepuffer leeren
        $pdf->ezStream();

        session()->put('mahn_datum', $fristdatum);
    }

    function mieter2_verzug_pruefen($mv_id)
    {
        $mv = new mietvertraege ();
        $mv->get_mietvertrag_infos_aktuell($mv_id);
        $arr = Array();
        for ($a = 0; $a < $mv->anzahl_personen; $a++) {
            $anschrift = $mv->postanschrift [$a] ['adresse'];
            $arr [] = $anschrift;
        }
        $arr_new = array_unique($arr);
        if (count($arr_new) == 1) {
            return true;
        }
    }

    function mahnung_pdf_mehrere($mahnliste, $fristdatum, $geldkonto_id, $mahngebuehr)
    {
        ob_clean(); // ausgabepuffer leeren
        $pdf = new Cezpdf ('a4', 'portrait');
        $bpdf = new b_pdf ();
        $bpdf->b_header($pdf, 'Partner', session()->get('partner_id'), 'portrait', 'Helvetica.afm', 6);
        $pdf->ezStopPageNumbers();
        // ###ANSCHREIBEN#####

        $anz_empfaenger = count($mahnliste);
        for ($ma = 0; $ma < $anz_empfaenger; $ma++) {
            $mv_id = $mahnliste [$ma];

            $mz = new miete ();
            $e = new einheit ();

            $mz->mietkonto_berechnung($mv_id);
            $saldo = $mz->erg;

            $mv = new mietvertraege ();
            $mv->get_mietvertrag_infos_aktuell($mv_id);
            $e->get_einheit_info($mv->einheit_id);

            /* Verzogene Mieter, abgelaufene MVS */
            if ($mv->mietvertrag_aktuell == '0') {
                /*
				 * Bei 2 Mietern überprüfen ob verzugsadresse gleich ist,
				 * wenn gleich, dann nur 1 Brief an beide, sonst getrennte Personen anschreiben
				 */

                /* Wenn Verzugsanschriften von beiden gleich */
                if ($this->mieter2_verzug_pruefen($mv_id)) {
                    /* Wie aktuelle Mieter behandeln, weil zur gleichen Adresse verzogen */

                    if ($mv->anzahl_personen == 1) {
                        $anschrift_pdf = ltrim($mv->postanschrift [0] ['anschrift']);
                    } else {
                        $anschrift_alle = $mv->postanschrift [0] ['adresse'];
                        $anschrift_pdf = "$mv->personen_name_string_u\n$anschrift_alle";
                    }

                    $pdf->ezSetMargins(135, 70, 50, 50);
                    $anrede = $mv->mv_anrede;

                    $pdf->ezSetDy(-15);
                    $pdf->ezText("$anschrift_pdf", 12);
                    $pdf->ezSetDy(-60);
                    $pdf->ezText("<b>Mahnung</b>", 12);
                    $pdf->ezSetDy(13);
                    $datum_heute = date("d.m.Y");
                    $pdf->ezText("Berlin, $datum_heute", 12, array(
                        'justification' => 'right'
                    ));
                    $pdf->ezText("Objekt: $e->haus_strasse $e->haus_nummer, $e->haus_plz $e->haus_stadt", 12);
                    $pdf->ezText("Einheit/Mieternummer: $mv->einheit_kurzname", 12);
                    $pdf->ezSetDy(-12);
                    /* Faltlinie */
                    $pdf->setLineStyle(0.2);
                    $pdf->line(5, 542, 20, 542);
                    $pdf->ezText("$anrede", 12);
                    $saldo = abs($saldo);
                    $saldo_a = nummer_punkt2komma($saldo);

                    $pdf->ezText("nach Durchsicht unserer Buchhaltungsunterlagen mussten wir feststellen, dass Ihr Mietkonto folgenden Rückstand aufweist:\n", 11);

                    $pdf->ezSetCmMargins(3, 3, 6, 7);
                    $pdf->ezText("<b>Mietrückstand</b>", 11);

                    $pdf->ezSetDy(11);
                    $pdf->ezText("<b>$saldo_a €</b>", 12, array(
                        'justification' => 'right'
                    ));

                    $pdf->setColor(0.0, 0.0, 0.0);
                    $pdf->ezText("<b>zzgl. Mahngebühr</b>", 11);
                    $pdf->ezSetDy(11);

                    $pdf->ezText("<b>$mahngebuehr €</b>", 11, array(
                        'justification' => 'right'
                    ));
                    /* Linier über Gesamtrückstand */
                    $pdf->ezSetDy(-5);
                    $pdf->line(170, $pdf->y, 403, $pdf->y);
                    $pdf->setColor(0.0, 0.0, 0.0);
                    $pdf->ezText("<b>Gesamtrückstand</b>", 11);
                    $pdf->ezSetDy(11);
                    $mahngebuehr_r = nummer_komma2punkt($mahngebuehr);
                    $gesamt_rueckstand = $saldo + $mahngebuehr_r;
                    $gesamt_rueckstand = nummer_punkt2komma($gesamt_rueckstand);
                    $pdf->ezText("<b>$gesamt_rueckstand €</b>\n", 11, array(
                        'justification' => 'right'
                    ));

                    $pdf->ezSetMargins(135, 70, 50, 50);

                    $g = new geldkonto_info ();
                    $g->geld_konto_details($geldkonto_id);

                    $pdf->setColor(0.0, 0.0, 0.0);
                    $pdf->ezText("Die konkreten Fehlbeträge entnehmen Sie bitte dem beigefügten Mietkonto.", 11);
                    $pdf->ezText("Wir fordern Sie auf, den genannten Betrag unter Angabe der bei uns geführten Mieternummer bis zum", 11);
                    $pdf->ezSetCmMargins(3, 3, 9, 3);
                    $pdf->ezSetDy(-10);
                    $pdf->ezText("<b>$fristdatum</b>\n", 11);
                    $pdf->ezSetMargins(135, 70, 50, 50);
                    $pdf->ezText("<b>auf das Konto bei der $g->kredit_institut zu überweisen.</b>\n", 11);
                    $pdf->setColor(0.0, 0.0, 0.0);
                    $pdf->ezSetDy(-10);
                    $pdf->ezText("<b>IBAN:</b> $g->IBAN1\n", 12);
                    $pdf->ezText("<b>BIC:</b> $g->BIC\n", 12);
                    $pdf->ezText("<b>Wir weisen vorsorglich darauf hin, dass wir bei einer Nichtzahlung bis zum oben genannten Termin, berechtigt sind eine Zahlungsklage gegen Sie einzureichen.</b>\n", 12);

                    $pdf->ezText("Für Rückfragen stehen wir Ihnen gerne zur Verfügung\n\n", 11);
                    $pdf->ezText("Mit freundlichen Grüßen\n\n", 11);
                    $pdf->ezSetDy(15);
                    $pdf->ezText("Wolfgang Wehrheim\n\n", 11);
                    $pdf->ezText("Dieses Schreiben wurde maschinell erstellt und ist daher ohne Unterschrift gültig.\n", 11);
                    $pdf->addInfo('Title', "Mahnung $mv->personen_name_string");
                    $pdf->addInfo('Author', Auth::user()->email);

                    // ### MIETKONTENBLATT####
                    $pdf->ezNewPage();
                    $pdf->ezSetMargins(135, 70, 50, 50);
                    $mz->mkb2pdf_mahnung_letzter_nullstand($pdf, $mv_id);
                    $fristdatum_sql = date_german2mysql($fristdatum);
                    $this->update_zahlungsfrist_z($mv_id, $fristdatum_sql, '-' . $saldo);
                    /* PDF AUSGABE */
                    if ($ma < $anz_empfaenger - 1) {
                        $pdf->ezNewPage();
                    }
                }  // end mehrere zu einer Verzugsadresse verzogen aktueller MV

                else {

                    for ($mm = 0; $mm < $mv->anzahl_personen; $mm++) {
                        $anschrift_pdf = $mv->postanschrift [$mm] ['anschrift'];
                        $pdf->ezSetMargins(135, 70, 50, 50);
                        $mz = new miete ();
                        $e = new einheit ();

                        $jahr = date("Y");
                        $monat = date("m");
                        $mz->mietkonto_berechnung_monatsgenau($mv_id, $jahr, $monat);
                        $saldo = $mz->erg;
                        $e->get_einheit_info($mv->einheit_id);

                        $anrede = $mv->postanschrift [$mm] ['anrede'];

                        $pdf->ezSetDy(-15);
                        $pdf->ezText("$anschrift_pdf", 12);
                        $pdf->ezSetDy(-60);
                        $pdf->ezText("<b>Mahnung</b>", 12);
                        $pdf->ezSetDy(13);
                        $datum_heute = date("d.m.Y");
                        $pdf->ezText("Berlin, $datum_heute", 12, array(
                            'justification' => 'right'
                        ));
                        $pdf->ezText("Objekt: $e->haus_strasse $e->haus_nummer, $e->haus_plz $e->haus_stadt", 12);
                        $pdf->ezText("Einheit/Mieternummer: $mv->einheit_kurzname", 12);
                        $pdf->ezSetDy(-12);
                        /* Faltlinie */
                        $pdf->setLineStyle(0.2);
                        $pdf->line(5, 542, 20, 542);
                        $pdf->ezText("$anrede", 12);
                        $saldo = abs($saldo);
                        $saldo_a = nummer_punkt2komma($saldo);

                        $pdf->ezText("nach Durchsicht unserer Buchhaltungsunterlagen mussten wir feststellen, dass Ihr Mietkonto folgenden Rückstand aufweist:\n", 11);

                        $pdf->ezSetCmMargins(3, 3, 6, 7);
                        $pdf->ezText("<b>Mietrückstand</b>", 11);

                        $pdf->ezSetDy(11);
                        $pdf->ezText("<b>$saldo_a €</b>", 12, array(
                            'justification' => 'right'
                        ));

                        $pdf->setColor(0.0, 0.0, 0.0);
                        $pdf->ezText("<b>zzgl. Mahngebühr</b>", 11);
                        $pdf->ezSetDy(11);

                        $pdf->ezText("<b>$mahngebuehr €</b>", 11, array(
                            'justification' => 'right'
                        ));
                        /* Linier über Gesamtrückstand */
                        $pdf->ezSetDy(-5);
                        $pdf->line(170, $pdf->y, 403, $pdf->y);
                        $pdf->setColor(0.0, 0.0, 0.0);
                        $pdf->ezText("<b>Gesamtrückstand</b>", 11);
                        $pdf->ezSetDy(11);
                        // $pdf->setColor(1.0,0.0,0.0);
                        $mahngebuehr_r = nummer_komma2punkt($mahngebuehr);
                        $gesamt_rueckstand = $saldo + $mahngebuehr_r;
                        $gesamt_rueckstand = nummer_punkt2komma($gesamt_rueckstand);
                        $pdf->ezText("<b>$gesamt_rueckstand €</b>\n", 11, array(
                            'justification' => 'right'
                        ));

                        $pdf->ezSetMargins(135, 70, 50, 50);

                        $g = new geldkonto_info ();
                        $g->geld_konto_details($geldkonto_id);

                        $pdf->setColor(0.0, 0.0, 0.0);
                        $pdf->ezText("Die konkreten Fehlbeträge entnehmen Sie bitte dem beigefügten Mietkonto.", 11);
                        $pdf->ezText("Wir fordern Sie auf, den genannten Betrag unter Angabe der bei uns geführten Mieternummer bis zum", 11);
                        $pdf->ezSetCmMargins(3, 3, 9, 3);
                        $pdf->ezText("<b>$fristdatum</b>\n", 11);
                        $pdf->ezSetMargins(135, 70, 50, 50);
                        $pdf->ezText("<b>auf das Konto bei der $g->kredit_institut zu überweisen.\n", 11);
                        $pdf->ezText("<b>IBAN</b> $g->IBAN1\n", 12);
                        $pdf->ezText("<b>BIC</b>  $g->BIC\n", 12);
                        $pdf->ezText("Wir weisen vorsorglich darauf hin, dass wir bei einem Rückstand von zwei Kaltmieten berechtigt sind, die Wohnung fristlos zu kündigen.\n", 11);
                        $pdf->setColor(0.0, 0.0, 0.0);
                        $pdf->ezText("zu überweisen.\n", 11);
                        $pdf->ezText("Für Rückfragen stehen wir Ihnen gerne zur Verfügung.\n\n", 11);
                        $pdf->ezText("Mit freundlichen Grüßen\n\n", 11);
                        $pdf->ezText("Wolfgang Wehrheim\n\n", 11);
                        $pdf->ezText("Dieses Schreiben wurde maschinell erstellt und ist daher ohne Unterschrift gültig.\n", 11);
                        $pdf->addInfo('Title', "Mahnung $mv->personen_name_string");
                        $pdf->addInfo('Author', Auth::user()->email);

                        // ### MIETKONTENBLATT####
                        $pdf->ezNewPage();
                        $pdf->ezSetMargins(135, 70, 50, 50);
                        $mz->mkb2pdf_mahnung_letzter_nullstand($pdf, $mv_id);
                        $fristdatum_sql = date_german2mysql($fristdatum);
                        $this->update_zahlungsfrist_z($mv_id, $fristdatum_sql, '-' . $saldo);
                        /* PDF AUSGABE */
                        $pdf->ezNewPage();
                    }
                }
            }
            /* Aktuelle Mieter */
            if ($mv->mietvertrag_aktuell == '1') {
                if (!$mv->postanschrift [0] ['anschrift']) {
                    $anschrift_pdf = "$mv->personen_name_string_u\n$e->haus_strasse $e->haus_nummer\n\n$e->haus_plz $e->haus_stadt";
                } else {
                    $anschrift_pdf = ltrim($mv->postanschrift [0] ['anschrift']);
                }

                $pdf->ezSetMargins(135, 70, 50, 50);
                $anrede = $mv->mv_anrede;

                $pdf->ezSetDy(-15);
                $pdf->ezText("$anschrift_pdf", 12);
                $pdf->ezSetDy(-60);
                $pdf->ezText("<b>Mahnung</b>", 12);
                $pdf->ezSetDy(13);
                $datum_heute = date("d.m.Y");
                $pdf->ezText("Berlin, $datum_heute", 12, array(
                    'justification' => 'right'
                ));
                $pdf->ezText("Objekt: $e->haus_strasse $e->haus_nummer, $e->haus_plz $e->haus_stadt", 12);
                $pdf->ezText("Einheit/Mieternummer: $mv->einheit_kurzname", 12);
                $pdf->ezSetDy(-12);
                /* Faltlinie */
                $pdf->setLineStyle(0.2);
                $pdf->line(5, 542, 20, 542);
                $pdf->ezText("$anrede", 12);
                $saldo = abs($saldo);
                $saldo_a = nummer_punkt2komma($saldo);

                $pdf->ezText("nach Durchsicht unserer Buchhaltungsunterlagen mussten wir feststellen, dass Ihr Mietkonto folgenden Rückstand aufweist:\n", 11);

                $pdf->ezSetCmMargins(3, 3, 6, 7);
                $pdf->ezText("<b>Mietrückstand</b>", 11);

                $pdf->ezSetDy(11);
                $pdf->ezText("<b>$saldo_a €</b>", 12, array(
                    'justification' => 'right'
                ));

                $pdf->setColor(0.0, 0.0, 0.0);
                $pdf->ezText("<b>zzgl. Mahngebühr</b>", 11);
                $pdf->ezSetDy(11);

                $pdf->ezText("<b>$mahngebuehr €</b>", 11, array(
                    'justification' => 'right'
                ));
                /* Linier über Gesamtrückstand */
                $pdf->ezSetDy(-5);
                $pdf->line(170, $pdf->y, 403, $pdf->y);
                $pdf->setColor(0.0, 0.0, 0.0);
                $pdf->ezText("<b>Gesamtrückstand</b>", 11);
                $pdf->ezSetDy(11);
                $mahngebuehr_r = nummer_komma2punkt($mahngebuehr);
                $gesamt_rueckstand = $saldo + $mahngebuehr_r;
                $gesamt_rueckstand = nummer_punkt2komma($gesamt_rueckstand);
                $pdf->ezText("<b>$gesamt_rueckstand €</b>\n", 11, array(
                    'justification' => 'right'
                ));

                $pdf->ezSetMargins(135, 70, 50, 50);

                $g = new geldkonto_info ();
                $g->geld_konto_details($geldkonto_id);

                $pdf->setColor(0.0, 0.0, 0.0);
                $pdf->ezText("Die konkreten Fehlbeträge entnehmen Sie bitte dem beigefügten Mietkonto.", 11);
                $pdf->ezText("Wir fordern Sie auf, den genannten Betrag unter Angabe der bei uns geführten Mieternummer bis zum\n", 11);
                $pdf->ezSetCmMargins(3, 3, 9, 3);
                $pdf->ezText("<b>$fristdatum</b>\n", 11);
                $pdf->ezSetMargins(135, 70, 50, 50);
                $pdf->ezText("auf das Konto bei der $g->kredit_institut zu überweisen.\n", 11);
                $pdf->setColor(0.0, 0.0, 0.0);
                $pdf->ezText("<b>IBAN:</b> $g->IBAN1", 12);
                $pdf->ezText("<b>BIC:</b>   $g->BIC\n", 12);
                $pdf->ezText("Wir weisen vorsorglich darauf hin, dass wir bei einem Rückstand von zwei Kaltmieten berechtigt sind, die Wohnung fristlos zu kündigen.\n", 11);
                $pdf->ezText("Für Rückfragen stehen wir Ihnen gerne zur Verfügung.\n\n", 11);
                $pdf->ezText("Mit freundlichen Grüßen\n\n", 11);

                if (session()->has('partner_id')) {
                    $pp = new partners ();
                    $pp->get_partner_name(session()->get('partner_id'));
                    $pdf->ezText("$pp->partner_name\nHausverwaltung\n", 11);
                } else {
                    $pdf->ezText("Ihre Hausverwaltung\n", 11);
                }

                $pdf->ezText("Dieses Schreiben wurde maschinell erstellt und ist daher ohne Unterschrift gültig.", 11);
                $pdf->addInfo('Title', "Mahnung $mv->personen_name_string");
                $pdf->addInfo('Author', Auth::user()->email);

                // ### MIETKONTENBLATT####
                $pdf->ezNewPage();
                $pdf->ezSetMargins(135, 70, 50, 50);
                $mz->mkb2pdf_mahnung_letzter_nullstand($pdf, $mv_id);
                $fristdatum_sql = date_german2mysql($fristdatum);
                $this->update_zahlungsfrist_z($mv_id, $fristdatum_sql, '-' . $saldo);
                /* PDF AUSGABE */
                if ($ma < $anz_empfaenger - 1) {
                    $pdf->ezNewPage();
                }
            } // end aktueller MV
        } // end for
        ob_end_clean(); // ausgabepuffer leeren
        $pdf->ezStream();

        session()->put('mahn_datum', $fristdatum);
    }

    function mahnung_pdf($mv_id, $fristdatum, $geldkonto_id, $mahngebuehr)
    {
        ob_clean(); // ausgabepuffer leeren
        $pdf = new Cezpdf ('a4', 'portrait');
        $bpdf = new b_pdf ();
        $bpdf->b_header($pdf, 'Partner', session()->get('partner_id'), 'portrait', 'Helvetica.afm', 6);

        $pdf->ezSetCmMargins(4.5, 1, 1, 1);
        $mv = new mietvertraege ();
        $mz = new miete ();
        $mz->mietkonto_berechnung($mv_id);
        $mv->get_mietvertrag_infos_aktuell($mv_id);
        echo '<pre>';
        print_r($mv);
        die ();
        $e->get_einheit_info($mv->einheit_id);
        $p = new person ();
        if ($mv->anzahl_personen == 1) {
            $p->get_person_infos($mv->personen_ids ['0'] ['PERSON_MIETVERTRAG_PERSON_ID']);
            $geschlecht = $d->finde_person_geschlecht($mv->personen_ids [0] ['PERSON_MIETVERTRAG_PERSON_ID']);
            if ($geschlecht == 'weiblich') {
                $anrede_p = 'geehrte Frau';
            }
            if ($geschlecht == 'männlich') {
                $anrede_p = 'geehrter Herr';
            }
            $anrede = $anrede . "$anrede_p $p->person_nachname,";
            $personen_anrede [0] ['anrede'] = $anrede;
            $personen_anrede [0] ['geschlecht'] = $geschlecht;
        }
        if ($mv->anzahl_personen > 1) {

            for ($a = 0; $a < $mv->anzahl_personen; $a++) {
                $p->get_person_infos($mv->personen_ids [$a] ['PERSON_MIETVERTRAG_PERSON_ID']);
                $geschlecht = $d->finde_person_geschlecht($mv->personen_ids [$a] ['PERSON_MIETVERTRAG_PERSON_ID']);
                if ($geschlecht == 'weiblich') {
                    $anrede_p = 'geehrte Frau';
                }
                if ($geschlecht == 'männlich') {
                    $anrede_p = 'geehrter Herr';
                }
                $anrede = "$anrede_p $p->person_nachname,";
                $personen_anrede [$a] ['anrede'] = $anrede;
                $personen_anrede [$a] ['geschlecht'] = $geschlecht;
            }
        }
        $personen_anreden = array_sortByIndex($personen_anrede, 'geschlecht', SORT_DESC);

        $pdf->selectFont($text_schrift);
        for ($b = 0; $b < $mv->anzahl_personen; $b++) {
            $anrede_p = $personen_anreden [$b] ['anrede'];
            if ($b < 1) {
                $anrede = "Sehr $anrede_p\n";
            } else {
                $anrede = $anrede . "sehr $anrede_p\n"; // \n neue zeile in pdf
            }
        }

        $pdf->ezSetDy(-15);
        $pdf->ezSetCmMargins(3, 3, 3, 3);
        $pdf->ezText("$mv->personen_name_string_u\n$e->haus_strasse $e->haus_nummer\n\n$e->haus_plz $e->haus_stadt", 12);
        $pdf->ezSetDy(-60);
        $pdf->ezText("<b>Mahnung</b>", 12);

        $pdf->ezSetDy(13);
        $datum_heute = date("d.m.Y");
        $pdf->ezText("Berlin, $datum_heute", 11, array(
            'justification' => 'right'
        ));

        $pdf->ezSetCmMargins(3, 3, 3, 3);
        $pdf->ezText("Objekt: $e->haus_strasse $e->haus_nummer, $e->haus_plz $e->haus_stadt", 11);
        $pdf->ezText("Einheit/Mieternummer: $mv->einheit_kurzname", 11);
        $pdf->ezSetDy(-11);
        /* Faltlinie */
        $pdf->setLineStyle(0.2);
        $pdf->line(5, 542, 20, 542);
        $pdf->ezText("$anrede", 11);
        $saldo = abs($saldo);
        $saldo_a = nummer_punkt2komma($saldo);

        $pdf->ezText("nach Durchsicht unserer Buchhaltungsunterlagen mussten wir feststellen, dass Ihr Mietkonto folgenden Rückstand aufweist:\n", 11);

        $pdf->ezSetCmMargins(3, 3, 6, 7);
        $pdf->ezText("<b>Mietrückstand</b>", 11);

        $pdf->ezSetDy(11);
        $pdf->ezText("<b>$saldo_a €</b>", 12, array(
            'justification' => 'right'
        ));

        $pdf->setColor(0.0, 0.0, 0.0);
        $pdf->ezText("<b>zzgl. Mahngebühr</b>", 11);
        $pdf->ezSetDy(11);

        $pdf->ezText("<b>$mahngebuehr €</b>", 11, array(
            'justification' => 'right'
        ));
        /* Linier über Gesamtrückstand */
        $pdf->ezSetDy(-5);
        $pdf->line(170, $pdf->y, 403, $pdf->y);
        $pdf->setColor(0.0, 0.0, 0.0);
        $pdf->ezText("<b>Gesamtrückstand</b>", 11);
        $pdf->ezSetDy(11);
        $mahngebuehr_r = nummer_komma2punkt($mahngebuehr);
        $gesamt_rueckstand = $saldo + $mahngebuehr_r;
        $gesamt_rueckstand = nummer_punkt2komma($gesamt_rueckstand);
        $pdf->ezText("<b>$gesamt_rueckstand €</b>\n", 11, array(
            'justification' => 'right'
        ));

        $pdf->ezSetCmMargins(3, 3, 3, 3);

        $g = new geldkonto_info ();
        $g->geld_konto_details($geldkonto_id);

        $pdf->setColor(0.0, 0.0, 0.0);
        $pdf->ezText("Die konkreten Fehlbeträge entnehmen Sie bitte dem beigefügten Mietkonto.", 11);
        $pdf->ezText("Wir fordern Sie auf, den genannten Betrag unter Angabe der bei uns geführten Mieternummer bis zum", 11);
        $pdf->ezSetCmMargins(3, 3, 9, 3);
        $pdf->ezText("<b>$fristdatum</b>\n", 11);
        $pdf->ezSetCmMargins(3, 3, 3, 3);
        $pdf->ezText("<b>auf das Konto $g->kontonummer  bei der $g->kredit_institut, BLZ $g->blz</b>\n", 11);
        $pdf->setColor(0.0, 0.0, 0.0);
        $pdf->ezText("zu überweisen.\n\n", 11);
        $pdf->ezText("Für Rückfragen stehen wir Ihnen gerne zur Verfügung.\n\n", 11);
        $pdf->ezText("Mit freundlichen Grüßen\n\n", 11);
        $pdf->ezText("Wolfgang Wehrheim\n\n", 11);
        $pdf->ezText("Dieses Schreiben wurde maschinell erstellt und ist daher ohne Unterschrift gültig.\n", 11);
        $pdf->addInfo('Title', "Mahnung $mv->personen_name_string");
        $pdf->addInfo('Author', Auth::user()->email);

        // ### MIETKONTENBLATT####
        $pdf->ezNewPage();
        $pdf->ezSetMargins(135, 70, 50, 50);
        $mz->mkb2pdf_mahnung_letzter_nullstand($pdf, $mv_id);

        $fristdatum_sql = date_german2mysql($fristdatum);
        $minus_saldo = '-' . $saldo;
        $this->update_zahlungsfrist_m($mv_id, $fristdatum_sql, $minus_saldo, '-' . $mahngebuehr_r);

        /* PDF AUSGABE */
        // $pdf->ezStream();
    }

    function update_zahlungsfrist_m($mv_id, $datum, $saldo, $mahn_geb)
    {
        $db_abfrage = "UPDATE MIETER_MAHNLISTEN SET ZAHLUNGSFRIST_M='$datum', MAHN_GEB='$mahn_geb'  WHERE MIETVERTRAG_ID='$mv_id' && SALDO='$saldo' ";
        DB::update($db_abfrage);
    }
} // end class
