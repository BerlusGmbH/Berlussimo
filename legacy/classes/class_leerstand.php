<?php

class leerstand
{
    public $name;
    public $anschrift;
    public $tel;
    public $email;
    public $zimmer;
    public $einzug_d;
    public $hinweis;
    public $einzug;

    function leerstand_objekt_pdf($objekt_id, $monat, $jahr)
    {
        ob_end_clean(); // ausgabepuffer leeren

        $pdf = new Cezpdf ('a4', 'portrait');
        $bpdf = new b_pdf ();
        $bpdf->b_header($pdf, 'Partner', session()->get('partner_id'), 'portrait', 'Helvetica.afm', 6);

        $monatsname = monat2name($monat);
        $l_tag = letzter_tag_im_monat($monat, $jahr);
        $datum = "$jahr-$monat-$l_tag";
        $table_arr = $this->leerstand_finden_monat($objekt_id, $datum);

        $cols = array(
            'OBJEKT_KURZNAME' => "Objekt",
            'EINHEIT_KURZNAME' => 'Einheit',
            'HAUS_STRASSE' => 'Strasse',
            'EINHEIT_QM' => 'Fläche m²',
            'EINHEIT_LAGE' => 'Lage/Typ'
        );

        $o = new objekt ();
        $objekt_name = $o->get_objekt_name($objekt_id);
        $anzahl_leer = count($table_arr);

        $pdf->ezTable($table_arr, $cols, "<b>Leerstandsübersicht $monatsname $jahr im $objekt_name,  Leerstand: $anzahl_leer</b>", array(
            'showHeadings' => 1,
            'shaded' => 0,
            'titleFontSize' => 8,
            'fontSize' => 7,
            'xPos' => 50,
            'xOrientation' => 'right',
            'width' => 500,
            'cols' => array(
                'DATUM' => array(
                    'justification' => 'right',
                    'width' => 65
                ),
                'G_BUCHUNGSNUMMER' => array(
                    'justification' => 'right',
                    'width' => 30
                ),
                'BETRAG' => array(
                    'justification' => 'right',
                    'width' => 75
                )
            )
        ));

        $pdf->ezStream();
    }

    function leerstand_finden_monat($objekt_id, $datum)
    {
        $result = DB::select("
          SELECT OBJEKT_KURZNAME, EINHEIT_ID, EINHEIT_KURZNAME,  EINHEIT_QM,  EINHEIT.HAUS_ID,  CONCAT(HAUS_STRASSE, HAUS_NUMMER) AS HAUS_STRASSE,  EINHEIT_QM, TRIM(EINHEIT_LAGE) AS EINHEIT_LAGE, EINHEIT.TYP 
          FROM `EINHEIT` RIGHT JOIN (HAUS, OBJEKT) ON ( EINHEIT.HAUS_ID = HAUS.HAUS_ID && HAUS.OBJEKT_ID = OBJEKT.OBJEKT_ID && OBJEKT.OBJEKT_ID='$objekt_id' )
          WHERE EINHEIT_AKTUELL='1' && EINHEIT_ID NOT IN (
            SELECT EINHEIT_ID
            FROM MIETVERTRAG
            WHERE MIETVERTRAG_AKTUELL = '1' && DATE_FORMAT( MIETVERTRAG_VON, '%Y-%m-%d' ) <= '$datum' && ( DATE_FORMAT( MIETVERTRAG_BIS, '%Y-%m-%d' ) >= '$datum' OR MIETVERTRAG_BIS = '0000-00-00' )
          )
          GROUP BY EINHEIT_ID ORDER BY EINHEIT_KURZNAME ASC
        ");
        return $result;
    }

    function pdf_projekt($einheit_id)
    {
        $e = new einheit ();
        $e->get_einheit_info($einheit_id);
        ob_clean(); // ausgabepuffer leeren

        $pdf = new Cezpdf ('a4', 'portrait');
        $bpdf = new b_pdf ();
        $bpdf->b_header($pdf, 'Partner', session()->get('partner_id'), 'portrait', 'Helvetica.afm', 6);
        $pdf->ezStopPageNumbers();
        $pdf->ezText("EINHEIT:", 14);
        $pdf->ezSetMargins(0, 0, 150, 0);
        $pdf->ezSetDy(16);
        $pdf->ezText("$e->einheit_kurzname", 14);

        $pdf->ezSetMargins(0, 0, 50, 0);
        $pdf->ezText("STRASSE:", 14);
        $pdf->ezSetMargins(0, 0, 150, 0);
        $pdf->ezSetDy(16);
        $pdf->ezText("$e->haus_strasse $e->haus_nummer", 14);

        $pdf->ezSetMargins(0, 0, 50, 0);
        $pdf->ezText("LAGE:", 14);
        $pdf->ezSetMargins(0, 0, 150, 0);
        $pdf->ezSetDy(16);
        $e->einheit_lage = ltrim(rtrim($e->einheit_lage));
        $pdf->ezText("$e->einheit_lage", 14);

        $pdf->ezSetMargins(0, 0, 50, 0);
        $pdf->ezText("FLÄCHE:", 14);
        $pdf->ezSetMargins(0, 0, 150, 0);
        $pdf->ezSetDy(16);
        $einheit_qm = nummer_punkt2komma($e->einheit_qm);
        $pdf->ezText("$einheit_qm m²", 14);

        $pdf->ezSetDy(-30);
        $pdf->ezSetMargins(0, 0, 100, 0);

        $pdf->ellipse(60, $pdf->y - 10, 10);
        $pdf->ezText("EMTRÜMPELUNG", 12);

        $pdf->ezSetDy(-20);
        $pdf->ellipse(60, $pdf->y - 10, 10);
        $pdf->ezText("ABRISS / BAUSTROM / BAUWASSER", 12);

        $pdf->ezSetDy(-20);
        $pdf->ellipse(60, $pdf->y - 10, 10);
        $pdf->ezText("SANITÄRROHINSTALLATION", 12);

        $pdf->ezSetDy(-20);
        $pdf->ellipse(60, $pdf->y - 10, 10);
        $pdf->ezText("SANITÄR - ENDMONTAGE", 12);

        $pdf->ezSetDy(-20);
        $pdf->ellipse(60, $pdf->y - 10, 10);
        $pdf->ezText("ELEKTROINSTALLATION", 12);

        $pdf->ezSetDy(-20);
        $pdf->ellipse(60, $pdf->y - 10, 10);
        $pdf->ezText("ELEKTRO - ENDMONTAGE", 12);

        $pdf->ezSetDy(-20);
        $pdf->ellipse(60, $pdf->y - 10, 10);
        $pdf->ezText("PUTZ / TROCKENBAU", 12);

        $pdf->ezSetDy(-20);
        $pdf->ellipse(60, $pdf->y - 10, 10);
        $pdf->ezText("TÜRE / BESCHLÄGE", 12);

        $pdf->ezSetDy(-20);
        $pdf->ellipse(60, $pdf->y - 10, 10);
        $pdf->ezText("FLIESENARBEITEN", 12);

        $pdf->ezSetDy(-20);
        $pdf->ellipse(60, $pdf->y - 10, 10);
        $pdf->ezText("MALERARBEITEN", 12);

        $pdf->ezSetDy(-20);
        $pdf->ellipse(60, $pdf->y - 10, 10);
        $pdf->ezText("FUSSBODENBELAG / LEISTEN", 12);

        $pdf->ezSetDy(-20);
        $pdf->ellipse(60, $pdf->y - 10, 10);
        $pdf->ezText("HERD:  JA / NEIN   <b>E-HERD</b>  |   GAS-HERD", 12);

        $pdf->ezSetDy(-20);
        $pdf->ellipse(60, $pdf->y - 10, 10);
        $pdf->ezText("SPÜLE MONTIEREN:     JA  |  NEIN", 12);

        $pdf->ezSetDy(-20);
        $pdf->ellipse(60, $pdf->y - 10, 10);
        $pdf->ezText("VERMIETET", 12);

        $pdf->ezSetDy(-20);
        $pdf->ellipse(60, $pdf->y - 10, 10);
        $pdf->ezText("FENSTER NEU:     JA / NEIN", 12);

        $pdf->ezSetMargins(0, 0, 50, 0);

        $pdf->ezSetDy(-20);
        $pdf->ezText("<u>SONSTIGE HINWEISE:</u>", 12);

        ob_end_clean(); // ausgabepuffer leeren
        $dateiname = $e->einheit_kurzname . "_Projekt.pdf";
        $pdf_opt ['Content-Disposition'] = $dateiname;
        $pdf->ezStream($pdf_opt);
    }

    function mieterselbstauskunft_besichtigung_pdf($einheit_id, $return = 0)
    {
        $e = new einheit ();
        $e->get_einheit_info($einheit_id);

        $pdf = new Cezpdf ('a4', 'portrait');
        $bpdf = new b_pdf ();
        $bpdf->b_header($pdf, 'Partner', session()->get('partner_id'), 'portrait', 'Helvetica.afm', 6);

        $y = $pdf->y;
        $pdf->ezText("<b>Mieterselbstauskunft zur Wohnungsbesichtigung</b>", 14);
        $pdf->ezSetY($y - 2);
        $pdf->ezText("Einh.-Nr.: <b>$e->einheit_kurzname</b>", 12, ['justification' => 'right']);
        $pdf->ezSetDy(-13);
        $d = new detail();

        $zimmer = $this->br2n(trim($d->finde_detail_inhalt('Einheit', $einheit_id, 'Zimmeranzahl')));
        $expose_km = nummer_punkt2komma_t(nummer_komma2punkt($this->br2n(ltrim(rtrim($d->finde_detail_inhalt('Einheit', $einheit_id, 'Vermietung-Kaltmiete'))))));
        $expose_bk = nummer_punkt2komma_t(nummer_komma2punkt($this->br2n(ltrim(rtrim($d->finde_detail_inhalt('Einheit', $einheit_id, 'Vermietung-BK'))))));
        $expose_hk = nummer_punkt2komma_t(nummer_komma2punkt($this->br2n(ltrim(rtrim($d->finde_detail_inhalt('Einheit', $einheit_id, 'Vermietung-HK'))))));
        $brutto_miete = nummer_punkt2komma_t(nummer_komma2punkt($expose_km) + nummer_komma2punkt($expose_bk) + nummer_komma2punkt($expose_hk));

        $pdf->ezText("Ich bin an der <b>Besichtigung</b> des Objektes in "
            . "der $e->haus_strasse $e->haus_nummer in $e->haus_plz $e->haus_stadt "
            . "(Wohnlage: $e->einheit_lage, Wohnfläche: " . nummer_punkt2komma($e->einheit_qm)
            . " m², Zimmeranzahl: $zimmer) "
            . "interessiert.", 10);

        $kaution = nummer_punkt2komma_t(3 * nummer_komma2punkt($expose_km));

        $pdf->ezSetDy(-10);

        $text = "Die monatliche Miete beträgt voraussichtlich "
            . "$brutto_miete € (Kaltmiete: $expose_km €";

        if ($expose_bk) {
            $text .= ", Betriebskostenvorschuss: $expose_bk €";
        }

        if ($expose_hk) {
            $text .= ", Heizkostenvorschuss: $expose_hk €";
        }

        $text .= "). Im Falle einer Anmietung wird eine Kaution in Höhe von "
            . $kaution . " € (drei Kaltmieten) fällig.";

        $pdf->ezText($text, 10);


        $pdf->ezSetDy(-10);

        $pdf->ezText("Mir ist bekannt, dass die Entscheidung über die Vermietung der Wohnung am mich zu einem "
            . "erheblichen Teil von meinem Einkommen abhängt. <b>Mir ist bewusst, dass "
            . "eine Vermietung an mich nur aussichtsreich ist, wenn der Anteil der Miete 50% des "
            . "Haushaltsnettoeinkommens (Einkommen nach allen Abzügen) nicht übersteigt, also mindestens "
            . nummer_punkt2komma_t(2 * nummer_komma2punkt($brutto_miete)) . " € beträgt.</b>", 10);

        $pdf->ezSetDy(-10);

        $pdf->ezText("<b>Es erfolgt keine Vermietung an Wohngemeinschaften.</b>", 10);

        $pdf->ezSetDy(-10);

        $y = $pdf->y;

        $pdf->ezText("<b>Information über die Datenerhebung</b>", 10);

        $pdf->ezSetDy(-8);

        if (session()->has('partner_id')) {
            $partner = \App\Models\Partner::findOrFail(session()->get('partner_id'));
        } else {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\ErrorMessage('Bitte wählen Sie einen Partner.')
            );
        }

        $text = "Mit der Ausfüllung der Selbstauskunft erheben wir personenbezogene Daten "
            . "von Ihnen. Verantwortlich hierfür ist:\n$partner->PARTNER_NAME, $partner->STRASSE $partner->NUMMER "
            . "in $partner->PLZ $partner->ORT";

        $ceo = $partner->rechtsvertreter()->first();

        if ($ceo) {
            $text .= ", $ceo->DETAIL_INHALT";
        }

        $register = $partner->handelsregister()->first();

        if ($register) {
            $text .= ", $register->DETAIL_INHALT";
        }

        $tel = $partner->phones()->first();

        if ($tel) {
            $text .= ", Telefon: $tel->DETAIL_INHALT";
        }

        $fax = $partner->faxs()->first();

        if ($fax) {
            $text .= ", Fax: $fax->DETAIL_INHALT";
        }

        $email = $partner->emails()->first();

        if ($email) {
            $text .= ", E-Mail: $email->DETAIL_INHALT";
        }

        $text .= ".";

        $pdf->ezText($text, 9);

        $pdf->ezSetDy(-8);

        $pdf->ezText("<u>Zweck und Rechtsgrundlage der Datenverarbeitung:</u>", 9);
        $pdf->ezText("Durchführung vorvertraglicher Maßnahmen zum Abschluss eines Mietvertrages "
            . "gem. Art. 6 Abs. 1 Satz 1 Pkt. b) der Datenschutzgrundverordnung (DSGVO).\n"
            . "Die von Ihnen bereitgestellten Daten sind zur Durchführung vorvertraglicher "
            . "Maßnahmen erforderlich. Ohne diese Daten können wir den Vertrag "
            . "mit Ihnen nicht abschließen.", 9);

        $pdf->ezSetDy(-8);

        $pdf->ezText("<u>Empfänger der Daten:</u>", 9);
        $pdf->ezText("Vermieter und die Hausverwaltung als Beauftragte "
            . "der/des Vermieter(s) (derzeit $partner->PARTNER_NAME)", 9);

        $pdf->ezSetDy(-8);

        $pdf->ezText("<u>Dauer der Speicherung Ihrer Daten:</u>", 9);
        $pdf->ezText("Ihre Daten werden innerhalb von drei Monaten ab Mietvertragsschluss "
            . "gelöscht, wenn die Wohnung an einen anderen Interessenten vermietet wird. Wird "
            . "der Mietvertrag mit Ihnen abgeschlossen, bleiben die Daten zur Durchführung des "
            . "Mietverhältnisses gespeichert, bis das Mietverhältnis beendet ist und sämtliche "
            . "etwaigen zivilrechtlichen Ansprüche verjährt sind, mindestens jedoch bis zum "
            . "Ablauf der gesetzliche Aufbewahrungspflichten.", 9);

        $pdf->ezSetDy(-8);

        $pdf->ezText("<u>Ihre Rechte:</u>", 9);
        $pdf->line(50, $pdf->y - 7, 55, $pdf->y - 7);
        $pdf->ezText("gem. Art. 15 DSGVO Auskunft über die von uns verarbeiteten personenbezogenen Daten zu verlangen. Insbesondere können Sie "
            . "Auskunft über die Verarbeitungszwecke, die Kategorie der personenbezogenen Daten, die Kategorien von Empfängern, gegenüber denen Ihre Daten offengelegt wurden oder werden, die geplante Speicherdauer, das Bestehende eines Rechtes auf Berichtigung, Löschung, Einschränkung der Verarbeitung oder Widerspruch, das Bestehen eines Beschwerderechtes sowie über das Bestehen einer automatisierten Entscheidungsfindung einschließlich Profiling und ggf. aussagekräftige Informationen zu deren Einzelheiten verlangen;", 9, ['left' => 9]);
        $pdf->line(50, $pdf->y - 7, 55, $pdf->y - 7);
        $pdf->ezText("gem. Art. 17 DSGVO die Löschung Ihrer bei uns gespeicherten personenbezogenen Daten zu verlangen, sofern diese nicht mehr für die "
            . "Zwecke notwendig sind, für die sie erhoben oder auf sonstige Weise verarbeitet wurden, soweit nicht die Verarbeitung zur Ausübung des Rechts auf freie Meinungsäußerung und Information, zur Erfüllung einer rechtlichen Verpflichtung, aus Gründen des öffentlichen Interesses oder zur Geltendmachung, Ausübung oder Verteidigung von Rechtsansprüche erforderlich ist;", 9, ['left' => 9]);
        $pdf->line(50, $pdf->y - 7, 55, $pdf->y - 7);
        $pdf->ezText("gem. Art. 18 DSGVO die Einschränkung der Verarbeitung Ihrer Personenbezogenen Daten zu verlangen, soweit die Richtigkeit der "
            . "Daten von Ihnen bestritten wird, die Verarbeitung unrechtmäßig ist, Sie aber deren Löschung ablehnen und wir die Daten nicht mehr benötigen, Sie jedoch diese zur Geltendmachung, Ausübung oder Verteidigung von Rechtsansprüchen benötigen oder Sie gem. Art. 21 DSGVO Widerspruch gegen die Verarbeitung eingelegt haben;", 9, ['left' => 9]);
        $pdf->line(50, $pdf->y - 7, 55, $pdf->y - 7);
        $pdf->ezText("gem. Art. 20 DSGVO Ihre personenbezogenen Daten, die Sie uns bereitgestellt haben, in einem strukturierten, gängigen "
            . "und maschinenlesbaren Format zu erhalten oder die Übermittlung an einen anderen Verantwortlichen zu verlangen;", 9, ['left' => 9]);
        $pdf->line(50, $pdf->y - 7, 55, $pdf->y - 7);
        $pdf->ezText("gem. Art. 77 DSGVO sich bei einer Aufsichtsbehörde zu beschweren. In der Regel können Sie sich hierfür an die Aufsichtsbehörde "
            . "Ihres üblichen Aufenthaltsortes oder Arbeitsplatzes oder unseres Geschäftssitzes wenden.", 9, ['left' => 9]);

        $pdf->rectangle(45, $pdf->y - 5, 500, $y - $pdf->y + 5);

        $pdf->ezNewPage();
        $pdf->ezText("Im Rahmen der Selbstauskunft gebe ich dem Vermieter die nachfolgenden Informationen in Bezug auf die mögliche <b>Besichtigung</b> des Mietobjektes:", 10);
        $pdf->ezSetDy(-10);
        $pdf->ezText("Vorname Name der/des Mietinteressentin/en: ____________________________________________________", 10);
        $pdf->ezSetDy(-8);
        $pdf->ezText("aktuelle Anschrift (Straße, PLZ, Ort): ___________________________________________________________", 10);

        $pdf->ezSetDy(-10);

        $pdf->ezText("Ich wünsche die Kontaktaufnahme zur Vereinbarung eines Besichtigungstermins", 10);
        $pdf->ezSetDy(-10);
        $pdf->rectangle(50, $pdf->y - 12, 7, 7);
        $pdf->ezText("per E-Mail    E-Mail: ______________________________________________________________________", 10, ['left' => 15]);
        $pdf->ezSetDy(-8);
        $pdf->rectangle(50, $pdf->y - 12, 7, 7);
        $pdf->ezText("per Telefon  Telefonnummer: ______________________________________________________________", 10, ['left' => 15]);

        $pdf->ezSetDy(-10);
        $y = $pdf->y;
        $pdf->rectangle(513, $pdf->y - 12, 7, 7);
        $pdf->rectangle(489, $pdf->y - 12, 7, 7);
        $pdf->ezText("Mit mir sollen weitere Personen als Hauptmieter in den Mietvertrag aufgenommen werden.", 10, ['aleft' => 50]);
        $pdf->ezSetY($y);
        $pdf->ezText("ja      nein", 10, ['aleft' => 500]);
        $pdf->ezSetDy(-8);
        $y = $pdf->y;
        $pdf->rectangle(513, $pdf->y - 12, 7, 7);
        $pdf->rectangle(489, $pdf->y - 12, 7, 7);
        $pdf->ezText("Mit mir sollen weitere Personen die Wohnung beziehen.", 10, ['aleft' => 50]);
        $pdf->ezSetY($y);
        $pdf->ezText("ja      nein", 10, ['aleft' => 500]);
        $pdf->ezSetDy(-8);
        $y = $pdf->y;
        $pdf->rectangle(513, $pdf->y - 12, 7, 7);
        $pdf->rectangle(489, $pdf->y - 12, 7, 7);
        $pdf->ezText("Ich halte derzeit mietrechtlich zustimmungspflichtige Tiere bzw. beabsichtige dies zu tun.", 10, ['aleft' => 50]);
        $pdf->ezSetY($y);
        $pdf->ezText("ja      nein", 10, ['aleft' => 500]);
        $pdf->ezSetDy(-10);

        $pdf->ezText("Mit mir werden weitere Personen die Wohnung besichtigen:", 10);
        $pdf->ezSetDy(-10);
        $pdf->ezText("1. Begleitung Vorname Name: ______________________________________________________________", 10);
        $pdf->ezSetDy(-8);
        $pdf->ezText("Anschrift: ______________________________________________________________", 10, ['left' => 92]);
        $pdf->ezSetDy(-10);
        $pdf->ezText("2. Begleitung Vorname Name: ______________________________________________________________", 10);
        $pdf->ezSetDy(-8);
        $pdf->ezText("Anschrift: ______________________________________________________________", 10, ['left' => 92]);

        $pdf->ezSetDy(-10);
        $pdf->ezText("Mit meiner Unterschrift erkläre ich, dass ich die auf Seite 1 stehenden Angaben nach Art. 13 Datenschutzgrundverordnung (DSGVO) erhalten habe und dass die von mir gemachten Angaben der Richtigkeit entsprechen.", 10);
        $pdf->ezSetDy(-30);
        $pdf->ezText("________________________________              ________________________________", 10);
        $pdf->ezSetDy(-3);
        $pdf->ezText("Ort, Datum                                                                                Unterschrift Mietinteressent", 8);

        if ($return == '0') {
            ob_end_clean();
            $dateiname = $e->einheit_kurzname . "_Besichtigung.pdf";
            $pdf_opt['Content-Disposition'] = $dateiname;
            $pdf->ezStream($pdf_opt);
        } else {
            return $pdf;
        }
    }

    function mieterselbstauskunft_bewerbung_pdf($einheit_id, $return = 0)
    {
        $e = new einheit ();
        $e->get_einheit_info($einheit_id);

        $pdf = new Cezpdf ('a4', 'portrait');
        $bpdf = new b_pdf ();
        $bpdf->b_header($pdf, 'Partner', session()->get('partner_id'), 'portrait', 'Helvetica.afm', 6);

        $y = $pdf->y;
        $pdf->ezText("<b>Mieterselbstauskunft zur Wohnungsanmietung</b>", 14);
        $pdf->ezSetY($y - 2);
        $pdf->ezText("Einh.-Nr.: <b>$e->einheit_kurzname</b>", 12, ['justification' => 'right']);
        $pdf->ezSetDy(-13);
        $d = new detail();

        $zimmer = $this->br2n(trim($d->finde_detail_inhalt('Einheit', $einheit_id, 'Zimmeranzahl')));
        $expose_km = nummer_punkt2komma_t(nummer_komma2punkt($this->br2n(ltrim(rtrim($d->finde_detail_inhalt('Einheit', $einheit_id, 'Vermietung-Kaltmiete'))))));
        $expose_bk = nummer_punkt2komma_t(nummer_komma2punkt($this->br2n(ltrim(rtrim($d->finde_detail_inhalt('Einheit', $einheit_id, 'Vermietung-BK'))))));
        $expose_hk = nummer_punkt2komma_t(nummer_komma2punkt($this->br2n(ltrim(rtrim($d->finde_detail_inhalt('Einheit', $einheit_id, 'Vermietung-HK'))))));
        $brutto_miete = nummer_punkt2komma_t(nummer_komma2punkt($expose_km) + nummer_komma2punkt($expose_bk) + nummer_komma2punkt($expose_hk));

        $vermietungstermin_text = "";

        $vermietungstermin = \App\Models\Einheiten::findOrFail($einheit_id)
            ->details()
            ->where('DETAIL_NAME', 'Vermietung-Vertragsbeginn')
            ->first();
        if ($vermietungstermin && $vermietungstermin->DETAIL_INHALT) {
            $vermietungstermin_text = "ab dem $vermietungstermin->DETAIL_INHALT oder bereits/erst ";
        } else {
            $mietvertrag = \App\Models\Mietvertraege::whereHas('einheit', function ($query) use ($einheit_id) {
                $query->where('EINHEIT_ID', $einheit_id);
            })->orderBy('MIETVERTRAG_VON', 'DESC')
                ->first();

            if ($mietvertrag) {
                $vermietungstermin = (new \Carbon\Carbon($mietvertrag->MIETVERTRAG_BIS))->addDays(1);
                if ($vermietungstermin < \Carbon\Carbon::today()) {
                    $vermietungstermin = \Carbon\Carbon::today()->addMonths(1)->firstOfMonth();
                }
                $vermietungstermin_text = "ab dem " . $vermietungstermin->format('d.m.Y') . " oder bereits/erst ";
            }
        }

        $pdf->ezText("Ich/wir bin/sind an der Anmietung des Objektes in "
            . "der $e->haus_strasse $e->haus_nummer in $e->haus_plz $e->haus_stadt "
            . "(Wohnlage: $e->einheit_lage, Wohnfläche: " . nummer_punkt2komma($e->einheit_qm)
            . " m², Zimmeranzahl: $zimmer) "
            . $vermietungstermin_text
            . "ab dem __.__.____ interessiert.", 10);

        $kaution = nummer_punkt2komma_t(3 * nummer_komma2punkt($expose_km));

        $pdf->ezSetDy(-10);

        $text = "Die monatliche Miete beträgt voraussichtlich "
            . "$brutto_miete € (Kaltmiete: $expose_km €";

        if ($expose_bk) {
            $text .= ", Betriebskostenvorschuss: $expose_bk €";
        }

        if ($expose_hk) {
            $text .= ", Heizkostenvorschuss: $expose_hk €";
        }

        $text .= "). Im Falle einer Anmietung wird eine Kaution in Höhe von "
            . $kaution . " € (drei Kaltmieten) fällig.";

        $pdf->ezText($text, 10);

        $pdf->ezSetDy(-20);

        $y = $pdf->y;

        $pdf->ezText("<b>Information über die Datenerhebung</b>", 10);

        $pdf->ezSetDy(-8);

        if (session()->has('partner_id')) {
            $partner = \App\Models\Partner::findOrFail(session()->get('partner_id'));
        } else {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\ErrorMessage('Bitte wählen Sie einen Partner.')
            );
        }

        $text = "Mit der Ausfüllung der Selbstauskunft erheben wir personenbezogene Daten "
            . "von Ihnen. Verantwortlich hierfür ist:\n$partner->PARTNER_NAME, $partner->STRASSE $partner->NUMMER "
            . "in $partner->PLZ $partner->ORT";

        $ceo = $partner->rechtsvertreter()->first();

        if ($ceo) {
            $text .= ", $ceo->DETAIL_INHALT";
        }

        $register = $partner->handelsregister()->first();

        if ($register) {
            $text .= ", $register->DETAIL_INHALT";
        }

        $tel = $partner->phones()->first();

        if ($tel) {
            $text .= ", Telefon: $tel->DETAIL_INHALT";
        }

        $fax = $partner->faxs()->first();

        if ($fax) {
            $text .= ", Fax: $fax->DETAIL_INHALT";
        }

        $email = $partner->emails()->first();

        if ($email) {
            $text .= ", E-Mail: $email->DETAIL_INHALT";
        }

        $text .= ".";

        $pdf->ezText($text, 9);

        $pdf->ezSetDy(-8);

        $pdf->ezText("<u>Zweck und Rechtsgrundlage der Datenverarbeitung:</u>", 9);
        $pdf->ezText("Maßnahmen zum Abschluss und ggf. Durchführung eines Mietvertrages gem. Art. 6 Abs. 1 "
            . "Satz 1 Pkt. b) der Datenschutzgrundverordnung (DSGVO).\nDie von Ihnen Ihnen bereitgestellten "
            . "Daten sind zur Durchführung vorvertraglicher Maßnahmen bzw. zur Vertragserfüllung erforderlich. "
            . "Ohne diese Daten können wir den Vertrag mit Ihnen nicht abschließen bzw. durchführen.", 9);

        $pdf->ezSetDy(-8);

        $pdf->ezText("<u>Empfänger der Daten:</u>", 9);
        $pdf->ezText("Vermieter(in), die Hausverwaltung als Beauftragte der/des Vermieterin/s "
            . "(derzeit $partner->PARTNER_NAME), Abrechnungsunternehmen (Kontaktdaten, Verbrauchswerte), "
            . "Handwerksunternehmen (Kontaktdaten), Banken (Name, Kontoverbindung), Kautionsbanken "
            . "(Namen, Anschrift, Geburtsdaten, Staatsangehörigkeit, Steuer-ID – gem. § 154 Abs. 2 der "
            . "Abgabenordnung (AO)), IT-Dienstleister, Steuerberater, Wirtschaftsprüfer, Rechtsanwälte, "
            . "Behörden", 9);

        $pdf->ezSetDy(-8);

        $pdf->ezText("<u>Dauer der Speicherung Ihrer Daten:</u>", 9);
        $pdf->ezText("Ihre Daten werden innerhalb von drei Monaten ab Mietvertragsschluss gelöscht, "
            . "wenn die Wohnung an einen anderen Interessenten vermietet wird. Wird der Mietvertrag mit Ihnen "
            . "abgeschlossen, bleiben die Daten zur Durchführung des Mietverhältnisses gespeichert, bis das "
            . "Mietverhältnis beendet ist und sämtliche etwaigen zivilrechtlichen Ansprüche verjährt sind, "
            . "mindestens jedoch bis zum Ablauf der gesetzliche Aufbewahrungspflichten.", 9);

        $pdf->ezSetDy(-8);

        $pdf->ezText("<u>Ihre Rechte:</u>", 9);
        $pdf->line(50, $pdf->y - 7, 55, $pdf->y - 7);
        $pdf->ezText("gem. Art. 15 DSGVO Auskunft über die von uns verarbeiteten personenbezogenen "
            . "Daten zu verlangen. Insbesondere können Sie Auskunft über die Verarbeitungszwecke, die Kategorie "
            . "der personenbezogenen Daten, die Kategorien von Empfängern, gegenüber denen Ihre Daten "
            . "offengelegt wurden oder werden, die geplante Speicherdauer, das Bestehende eines Rechtes auf "
            . "Berichtigung, Löschung, Einschränkung der Verarbeitung oder Widerspruch, das Bestehen eines "
            . "Beschwerderechtes sowie über das Bestehen einer automatisierten Entscheidungsfindung einschließlich "
            . "Profiling und ggf. aussagekräftige Informationen zu deren Einzelheiten verlangen;", 9, ['left' => 9]);
        $pdf->line(50, $pdf->y - 7, 55, $pdf->y - 7);
        $pdf->ezText("gem. Art. 17 DSGVO die Löschung Ihrer bei uns gespeicherten personenbezogenen "
            . "Daten zu verlangen, sofern diese nicht mehr für die Zwecke notwendig sind, für die sie erhoben "
            . "oder auf sonstige Weise verarbeitet wurden, soweit nicht die Verarbeitung zur Ausübung des Rechts "
            . "auf freie Meinungsäußerung und Information, zur Erfüllung einer rechtlichen Verpflichtung, aus "
            . "Gründen des öffentlichen Interesses oder zur Geltendmachung, Ausübung oder Verteidigung von "
            . "Rechtsansprüche erforderlich ist;", 9, ['left' => 9]);
        $pdf->line(50, $pdf->y - 7, 55, $pdf->y - 7);
        $pdf->ezText("gem. Art. 18 DSGVO die Einschränkung der Verarbeitung Ihrer Personenbezogenen "
            . "Daten zu verlangen, soweit die Richtigkeit der Daten von Ihnen bestritten wird, die "
            . "Verarbeitung unrechtmäßig ist, Sie aber deren Löschung ablehnen und wir die Daten nicht mehr "
            . "benötigen, Sie jedoch diese zur Geltendmachung, Ausübung oder Verteidigung von Rechtsansprüchen "
            . "benötigen oder Sie gem. Art. 21 DSGVO Widerspruch gegen die Verarbeitung "
            . "eingelegt haben;", 9, ['left' => 9]);
        $pdf->line(50, $pdf->y - 7, 55, $pdf->y - 7);
        $pdf->ezText("gem. Art. 20 DSGVO Ihre personenbezogenen Daten, die Sie uns bereitgestellt haben, "
            . "in einem strukturierten, gängigen und maschinenlesbaren Format zu erhalten oder die "
            . "Übermittlung an einen anderen Verantwortlichen zu verlangen;", 9, ['left' => 9]);
        $pdf->line(50, $pdf->y - 7, 55, $pdf->y - 7);
        $pdf->ezText("gem. Art. 77 DSGVO sich bei einer Aufsichtsbehörde zu beschweren. In der Regel "
            . "können Sie sich hierfür an die Aufsichtsbehörde Ihres üblichen Aufenthaltsortes oder "
            . "Arbeitsplatzes oder unseres Geschäftssitzes wenden.", 9, ['left' => 9]);

        $pdf->rectangle(45, $pdf->y - 7, 505, $y - $pdf->y + 7);

        $pdf->ezNewPage();

        $pdf->ezText("Im Rahmen der Selbstauskunft gebe(n) ich/wir dem Vermieter die nachfolgenden Informationen "
            . "in Bezug auf die mögliche Anmietung des Objektes:", 10);

        $pdf->ezSetDy(-10);

        $pdf->addText(52.5, $pdf->y - 103, 8, "Straße | Postleitzahl | Ort");

        $pdf->addText(52.5, $pdf->y - 203, 8, "nur bei mehreren Hauptmietern");

        $pdf->addText(52.5, $pdf->y - 255, 8, "Name | Straße | Postleitzahl | Ort");

        $pdf->setLineStyle(1, 'round', '', [0, 3]);

        $pdf->line(185, $pdf->y - 102, 355, $pdf->y - 102);
        $pdf->line(370, $pdf->y - 102, 540, $pdf->y - 102);

        $pdf->line(185, $pdf->y - 255, 355, $pdf->y - 255);
        $pdf->line(370, $pdf->y - 255, 540, $pdf->y - 255);

        $pdf->line(185, $pdf->y - 275, 355, $pdf->y - 275);
        $pdf->line(370, $pdf->y - 275, 540, $pdf->y - 275);

        $pdf->setLineStyle(1, '', '', []);

        $commonInformation = [
            [
                ' ' => 'Name',
                'Mietinteressent/in' => '',
                'Mitmieter/in' => ''
            ],
            [
                ' ' => 'Vorname',
                'Mietinteressent/in' => '',
                'Mitmieter/in' => ''
            ],
            [
                ' ' => "derzeitige Anschrift\n\n",
                'Mietinteressent/in' => '',
                'Mitmieter/in' => ''
            ],
            [
                ' ' => 'Geburtsdatum',
                'Mietinteressent/in' => '',
                'Mitmieter/in' => ''
            ],
            [
                ' ' => 'Telefonummer',
                'Mietinteressent/in' => '',
                'Mitmieter/in' => ''
            ],
            [
                ' ' => 'E-Mail',
                'Mietinteressent/in' => '',
                'Mitmieter/in' => ''
            ],
            [
                ' ' => "Familienstand\n",
                'Mietinteressent/in' => '',
                'Mitmieter/in' => ''
            ],
            [
                ' ' => 'ausgeübter Beruf',
                'Mietinteressent/in' => '',
                'Mitmieter/in' => ''
            ],
            [
                ' ' => "derzeitiger Arbeitgeber\n\n\n\n",
                'Mietinteressent/in' => '',
                'Mitmieter/in' => ''
            ],
        ];

        $pdf->ezTable($commonInformation,
            null,
            '<b>Allgemeine Auskünfte</b>',
            [
                'width' => 500,
                'rowGap' => 4,
                'cols' => [
                    ' ' => ['width' => 130],
                    'Mietinteressent/in' => ['width' => 185],
                    'Mitmieter/in' => ['width' => 185]
                ]
            ]
        );

        $pdf->ezSetDy(-30);

        $y = $pdf->y;
        $pdf->rectangle(515, $pdf->y - 12, 7, 7);
        $pdf->rectangle(487, $pdf->y - 12, 7, 7);
        $pdf->ezText("Außer mir/uns sollen <b>weitere Personen</b> die Wohnung beziehen?", 10);
        $pdf->ezSetY($y);
        $pdf->ezText("ja       nein", 10, ['justification' => 'right']);

        $additionalPersons = [
            [
                ' ' => '1.',
                'Vorname Name' => "\n\n",
                'derzeitige Anschrift' => '',
                'Geburtsdatum' => ''
            ],
            [
                ' ' => '2.',
                'Vorname Name' => "\n\n",
                'derzeitige Anschrift' => '',
                'Geburtsdatum' => ''
            ],
            [
                ' ' => '3.',
                'Vorname Name' => "\n\n",
                'derzeitige Anschrift' => '',
                'Geburtsdatum' => ''
            ],
            [
                ' ' => '4.',
                'Vorname Name' => "\n\n",
                'derzeitige Anschrift' => '',
                'Geburtsdatum' => ''
            ]
        ];

        $pdf->ezSetDy(-30);

        $pdf->ezTable($additionalPersons,
            null,
            null,
            [
                'width' => 500,
                'rowGap' => 4,
                'cols' => [' ' => ['width' => 18], 'Geburtsdatum' => ['width' => 100]]
            ]
        );

        $pdf->setLineStyle(1, 'round', '', [0, 3]);

        $pdf->line(255, $pdf->y + 145, 440, $pdf->y + 145);
        $pdf->line(255, $pdf->y + 105, 440, $pdf->y + 105);
        $pdf->line(255, $pdf->y + 60, 440, $pdf->y + 60);
        $pdf->line(255, $pdf->y + 20, 440, $pdf->y + 20);

        $pdf->setLineStyle(1, '', '', []);

        $enhancedInformation = [
            [
                ' ' => '1.',
                '  ' => "Wird die Miete <b><u>vollständig</u></b> von einer öffentlichen Stelle übernommen "
                    . "und soll diese <b><u>direkt</u></b> an den Vermieter geleistet werden?\n\n"
                    . "Falls die vorherige Frage mit <b><u>nein</u></b> beantwortet wurde;\n"
                    . "Höhe des monatlichen Betrages, der für die Tilgung des Mietzinses zur Verfügung steht.\n\n",
                'Mietinteressent/in' => 'ja      nein',
                'Mitmieter/in' => 'ja      nein'
            ],
            [
                ' ' => '2.',
                '  ' => "Eine <b>aktuelle</b> (nicht älter als sechs Monate)\nSCHUFA-<b>Bonitätsauskunft</b> "
                    . "kann vorgelegt werden.\nBitte beachten Sie, dass es sich hierbei <b>nicht</b> um "
                    . "die SCHUFA-<b>Selbst-\nauskunft</b> handelt. Diese ist ausdrücklich nicht für die "
                    . "Weitergabe an Dritte geeignet und wird auch von uns nicht verlangt.",
                'Mietinteressent/in' => 'ja      nein',
                'Mitmieter/in' => 'ja      nein'
            ],
            [
                ' ' => '3.',
                '  ' => "Ist die Miete in den letzten zwölf Monaten regelmäßig bezahlt worden?",
                'Mietinteressent/in' => 'ja      nein',
                'Mitmieter/in' => 'ja      nein'
            ],
            [
                ' ' => '4.',
                '  ' => "Haben Sie in den letzten 5 Jahren eine <b>eidesstattliche Versicherung</b> abgegeben?\nFalls ja, wann?",
                'Mietinteressent/in' => 'ja      nein',
                'Mitmieter/in' => 'ja      nein'
            ],
            [
                ' ' => '5.',
                '  ' => "Wurde in den letzten 7 Jahren ein <b>Verbraucherinsolvenzverfahren</b> gegen Sie eröffnet?\nFalls ja, wann?",
                'Mietinteressent/in' => 'ja      nein',
                'Mitmieter/in' => 'ja      nein'
            ],
            [
                ' ' => '6.',
                '  ' => "Wurde in den letzten 5 Jahren ein <b>Räumungstitel</b> gegen Sie erwirkt?\n\nFalls ja, wann?",
                'Mietinteressent/in' => 'ja      nein',
                'Mitmieter/in' => 'ja      nein'
            ],
            [
                ' ' => '7.',
                '  ' => "Ist eine <b>gewerbliche Nutzung</b> der Wohnung beabsichtigt?\n\nFalls ja, Zweck angeben.",
                'Mietinteressent/in' => 'ja      nein',
                'Mitmieter/in' => 'ja      nein'
            ],
            [
                ' ' => '8.',
                '  ' => "Beabsichtigen Sie eine <b>Wohngemeinschaft</b> zu gründen?",
                'Mietinteressent/in' => 'ja      nein',
                'Mitmieter/in' => 'ja      nein'
            ],
            [
                ' ' => '9.',
                '  ' => "Beabsichtigen Sie eine mietrechtlich zustimmungspflichtige\n<b>Tierhaltung</b>?\nFalls ja, was für Tiere?",
                'Mietinteressent/in' => 'ja      nein',
                'Mitmieter/in' => 'ja      nein'
            ],
        ];

        $pdf->ezNewPage();

        $y = $pdf->y;

        $pdf->ezTable($enhancedInformation,
            null,
            "<b>Zusätzliche Auskünfte</b>",
            [
                'width' => 500,
                'rowGap' => 4,
                'cols' => [
                    'Mietinteressent/in' => ['justification' => 'right'],
                    'Mitmieter/in' => ['justification' => 'right']
                ]
            ]
        );

        $pdf->addText(70, $y - 124, 8, "(Nettoeinkommen abzgl. Verpflichtungen gegenüber Dritten (Unterhaltszahlungen,");
        $pdf->addText(70, $y - 134, 8, "Raten aus Kredit- oder Darlehnstilgungen etc.)");

        $left1 = 427;
        $right1 = 452;
        $left2 = 490;
        $right2 = 514;
        $column1 = $y - 55;
        $pdf->rectangle($right1, $column1, 7, 7);
        $pdf->rectangle($left1, $column1, 7, 7);
        $pdf->rectangle($right2, $column1, 7, 7);
        $pdf->rectangle($left2, $column1, 7, 7);

        $column2 = $y - 155;
        $pdf->rectangle($right1, $column2, 7, 7);
        $pdf->rectangle($left1, $column2, 7, 7);
        $pdf->rectangle($right2, $column2, 7, 7);
        $pdf->rectangle($left2, $column2, 7, 7);

        $column3 = $y - 220;
        $pdf->rectangle($right1, $column3, 7, 7);
        $pdf->rectangle($left1, $column3, 7, 7);
        $pdf->rectangle($right2, $column3, 7, 7);
        $pdf->rectangle($left2, $column3, 7, 7);

        $column4 = $y - 241;
        $pdf->rectangle($right1, $column4, 7, 7);
        $pdf->rectangle($left1, $column4, 7, 7);
        $pdf->rectangle($right2, $column4, 7, 7);
        $pdf->rectangle($left2, $column4, 7, 7);

        $column5 = $y - 283;
        $pdf->rectangle($right1, $column5, 7, 7);
        $pdf->rectangle($left1, $column5, 7, 7);
        $pdf->rectangle($right2, $column5, 7, 7);
        $pdf->rectangle($left2, $column5, 7, 7);

        $column6 = $y - 326;
        $pdf->rectangle($right1, $column6, 7, 7);
        $pdf->rectangle($left1, $column6, 7, 7);
        $pdf->rectangle($right2, $column6, 7, 7);
        $pdf->rectangle($left2, $column6, 7, 7);

        $column7 = $y - 368;
        $pdf->rectangle($right1, $column7, 7, 7);
        $pdf->rectangle($left1, $column7, 7, 7);
        $pdf->rectangle($right2, $column7, 7, 7);
        $pdf->rectangle($left2, $column7, 7, 7);

        $column8 = $y - 411;
        $pdf->rectangle($right1, $column8, 7, 7);
        $pdf->rectangle($left1, $column8, 7, 7);
        $pdf->rectangle($right2, $column8, 7, 7);
        $pdf->rectangle($left2, $column8, 7, 7);

        $column9 = $y - 431;
        $pdf->rectangle($right1, $column9, 7, 7);
        $pdf->rectangle($left1, $column9, 7, 7);
        $pdf->rectangle($right2, $column9, 7, 7);
        $pdf->rectangle($left2, $column9, 7, 7);

        $pdf->setLineStyle(1, 'round', '', [0, 3]);

        $column1 = $y - 136;
        $pdf->line(407, $column1, 477, $column1);
        $pdf->line(493, $column1, 541, $column1);

        $column4 = $y - 265;
        $pdf->line(407, $column4, 477, $column4);
        $pdf->line(493, $column4, 541, $column4);

        $column5 = $y - 307;
        $pdf->line(407, $column5, 477, $column5);
        $pdf->line(493, $column5, 541, $column5);

        $column6 = $y - 349;
        $pdf->line(407, $column6, 477, $column6);
        $pdf->line(493, $column6, 541, $column6);

        $column7 = $y - 393;
        $pdf->line(188, $column7, 388, $column7);

        $column9 = $y - 455;
        $pdf->line(188, $column9, 388, $column9);

        $pdf->setLineStyle(1, '', '', []);

        $pdf->ezSetDy(-10);

        $pdf->addText(50, $pdf->y - 11.5, 10, "1.");

        $pdf->ezText("Ich/Wir erkläre(n), dass ich/wir in der Lage bin/sind, alle zu "
            . "übernehmenden finanziellen Verpflichtungen aus dem Mietvertrag, insbesondere die "
            . "Erbringung der Mietkaution sowie der Miete plus Nebenkosten, zu leisten.", 10, ['left' => 10]);

        $pdf->ezSetDy(-10);

        $pdf->addText(50, $pdf->y - 11.5, 10, "2.");

        $pdf->ezText("Ich/Wir erkläre(n), dass die vorgenannten Angaben vollständig und "
            . "wahrheitsgemäß gemacht wurden. ", 10, ['left' => 10]);

        $pdf->ezSetDy(-10);

        $pdf->addText(50, $pdf->y - 11.5, 10, "3.");

        $pdf->ezText("Mir/uns ist bekannt, dass ich/wir hinsichtlich der Angaben über mein/unser "
            . "<b>Einkommen</b> (zusätzliche Auskünfte – Frage 1), die <b>SCHUFA-Bonitätsauskunft</b> (zusätzliche Auskünfte "
            . "– Frage 2), sowie die Angaben zur <b>regelmäßigen Mietzahlung</b> (zusätzliche Auskünfte – Frage 3) "
            . "entsprechende <b>Nachweise</b> im <b>Original</b> zur\n<b>Vertragsunterzeichnung</b> vorzulegen habe. <b>Mir/uns ist "
            . "bekannt, dass andernfalls eine Vertragsunterzeichnung nicht zustande kommt</b>. Das sind insbesondere:", 10, ['left' => 10]);

        $pdf->ezSetDy(-8);

        $pdf->filledRectangle(60, $pdf->y - 10.5, 3, 3);

        $pdf->ezText("als Nachweise zu den <b>Einkommensverhältnissen</b> (zusätzliche Auskünfte – Frage 1)", 10, ['left' => 20]);

        $pdf->ezSetDy(-8);

        $pdf->line(70, $pdf->y - 8.5, 74, $pdf->y - 8.5);

        $pdf->ezText("Übernahmeerklärung der öffentlichen Stelle <b>inkl.</b> der Erklärung die Miete vollständig "
            . "an den Vermieter direkt zu überweisen <b>oder</b>", 10, ['left' => 30]);

        $pdf->ezSetDy(-8);

        $pdf->line(70, $pdf->y - 8.5, 74, $pdf->y - 8.5);

        $pdf->ezText("Lohn- oder Gehaltsabrechnung der letzten drei Monate <b>oder</b>", 10, ['left' => 30]);

        $pdf->ezSetDy(-8);

        $pdf->line(70, $pdf->y - 8.5, 74, $pdf->y - 8.5);

        $pdf->ezText("letzte Einkommenssteuerbescheid <u>und</u> Kontoauszüge der letzten sechs Monate,", 10, ['left' => 30]);

        $pdf->ezSetDy(-8);

        $pdf->filledRectangle(60, $pdf->y - 10.5, 3, 3);

        $pdf->ezText("die <b>SCHUFA-Bonitätsauskunft</b> (zusätzliche Auskünfte – Frage 2)", 10, ['left' => 20]);

        $pdf->ezSetDy(-8);

        $pdf->filledRectangle(60, $pdf->y - 10.5, 3, 3);

        $pdf->ezText("als Nachweis für die <b>regelmäßige Mietzahlungen</b> (zusätzliche Auskünfte – Frage 3)", 10, ['left' => 20]);

        $pdf->ezSetDy(-8);

        $pdf->line(70, $pdf->y - 8.5, 74, $pdf->y - 8.5);

        $pdf->ezText("Mietschuldenfreiheitsbescheinigung des aktuellen Vermieters <b>oder</b>", 10, ['left' => 30]);

        $pdf->ezSetDy(-8);

        $pdf->line(70, $pdf->y - 8.5, 74, $pdf->y - 8.5);

        $pdf->ezText("vom Vorvermieter gemäß § 368 BGB geschuldete Quittung über geleistete Zahlungen – üblicherweise ein Mietkontenauszug <b>oder</b>", 10, ['left' => 30]);

        $pdf->ezSetDy(-8);

        $pdf->line(70, $pdf->y - 8.5, 74, $pdf->y - 8.5);

        $pdf->ezText("Kontoauszüge als Beleg zu geleisteten Mietzahlungen sowie den Mietvertrag inkl. Mietanpassungen als Nachweis für die Höhe des zu leistenden Mietzinses", 10, ['left' => 30]);

        $pdf->ezSetDy(-10);

        $pdf->addText(50, $pdf->y - 11.5, 10, "4.");

        $pdf->ezText("Mir/uns ist bekannt, dass bei Abschluss eines Mietvertrages oben gemachte Falschangaben die Aufhebung oder fristlose Kündigung des Mietverhältnisses zur Folge haben können.", 10, ['left' => 10]);

        $pdf->ezSetDy(-10);

        $pdf->addText(50, $pdf->y - 11.5, 10, "5.");

        $pdf->ezText("Mit meiner Unterschrift erkläre ich, dass ich die auf Seite 1 stehenden Angaben nach Art 13 DSGVO erhalten habe und dass die von mir gemachten Angaben der Richtigkeit entsprechen.", 10, ['left' => 10]);

        $pdf->ezSetDy(-80);

        $pdf->ezText("________________________________              ________________________________", 10);
        $pdf->ezSetDy(-3);
        $pdf->ezText("Ort, Datum                                                                                Unterschrift Mietinteressent", 8);

        $pdf->ezSetDy(-40);

        $pdf->ezText("________________________________              ________________________________", 10);
        $pdf->ezSetDy(-3);
        $pdf->ezText("Ort, Datum                                                                                Unterschrift Mitmieter", 8);

        if ($return == '0') {
            ob_end_clean();
            $dateiname = $e->einheit_kurzname . "_Bewerbung.pdf";
            $pdf_opt['Content-Disposition'] = $dateiname;
            $pdf->ezStream($pdf_opt);
        } else {
            return $pdf;
        }
    }

    function br2n($str)
    {
        $brs = array(
            "<br>",
            "</br>",
            "<br/>",
            "<br />"
        );
        $string = str_replace($brs, ' ', "$str");
        return $string;
    }

    /* Email mit Attachment */
    function multi_attach_mail($to, $files, $sendermail, $thema, $nachricht, $name)
    {
        // email fields: to, from, subject, and so on
        $from = "$name <" . $sendermail . ">";
        // $subject = date("d.M H:i")." F=".count($files);
        $subject = $thema;
        // $message = date("Y.m.d H:i:s")."\n".count($files)." attachments";
        $message = $nachricht;
        $headers = "From: $from";

        // boundary
        $semi_rand = md5(time());
        $mime_boundary = "==Multipart_Boundary_x{$semi_rand}x";

        // headers for attachment
        $headers .= "\nMIME-Version: 1.0\n" . "Content-Type: multipart/mixed;\n" . " boundary=\"{$mime_boundary}\"";

        // multipart boundary
        $message = "--{$mime_boundary}\n" . "Content-Type: text/plain; charset=\"UTF-8\"\n" . "Content-Transfer-Encoding: 7bit\n\n" . $message . "\n\n";

        // preparing attachments
        for ($i = 0; $i < count($files); $i++) {
            if (is_file($files [$i])) {
                $message .= "--{$mime_boundary}\n";
                $fp = @fopen($files [$i], "rb");
                $data = @fread($fp, filesize($files [$i]));
                @fclose($fp);
                $data = chunk_split(base64_encode($data));
                $message .= "Content-Type: application/octet-stream; name=\"" . basename($files [$i]) . "\"\n" . "Content-Description: " . basename($files [$i]) . "\n" . "Content-Disposition: attachment;\n" . " filename=\"" . basename($files [$i]) . "\"; size=" . filesize($files [$i]) . ";\n" . "Content-Transfer-Encoding: base64\n\n" . $data . "\n\n";
            }
        }
        $message .= "--{$mime_boundary}--";
        $returnpath = "-f" . $sendermail;
        $ok = @mail($to, $subject, $message, $headers, $returnpath);
        if ($ok) {
            return $i;
        } else {
            return 0;
        }
    }

    function sanierungsliste($objekt_id = null, $monate = null, $w = 250, $h = 200)
    {
        if ($objekt_id == null) {
            fehlermeldung_ausgeben("Objekt wählen");
        } else {

            $f = new formular ();

            if ($monate == null) {
                $datum = date("Y-m-d");
            } else {
                $mi = new miete ();
                $datum_heute = date("Y-m-d");

                $datum = $mi->tage_plus($datum_heute, $monate * 31);
                $datum_arr = explode('-', $datum);
                $jahr_neu = $datum_arr [0];
                $monat_neu = $datum_arr [1];

                $ltm = letzter_tag_im_monat($monat_neu, $jahr_neu);
                $datum = "$jahr_neu-$monat_neu-$ltm";
            }

            $datum_d = date_mysql2german($datum);

            $arr = $this->leerstand_finden_monat($objekt_id, $datum);
            /* Array vervollständigen */
            $anz_e = count($arr);
            for ($a = 0; $a < $anz_e; $a++) {
                $einheit_id = $arr [$a] ['EINHEIT_ID'];

                $d = new detail ();
                // $arr[$a]['ZIMMER'] = $d->finde_detail_inhalt('Einheit', $einheit_id, 'Zimmeranzahl');

                /* Zimmeranzahl */
                $arr_details = $d->finde_detail_inhalt_last_arr('Einheit', $einheit_id, 'Zimmeranzahl');
                if (!empty($arr_details)) {
                    $arr [$a] ['ZIMMER'] = $arr_details [0] ['DETAIL_INHALT'];
                    $arr [$a] ['ZIMMER_DAT'] = $arr_details [0] ['DETAIL_DAT'];
                    $arr [$a] ['ZIMMER_BEM'] = $arr_details [0] ['DETAIL_BEMERKUNG'];
                } else {
                    $arr [$a] ['ZIMMER'] = '';
                    $arr [$a] ['ZIMMER_DAT'] = 0;
                    $arr [$a] ['ZIMMER_BEM'] = '';
                }
                unset ($arr_details);

                /* Balkon aus Details */
                $arr_details = $d->finde_detail_inhalt_last_arr('Einheit', $einheit_id, 'Balkon');
                if (!empty($arr_details)) {
                    $arr [$a] ['BALKON'] = $arr_details [0] ['DETAIL_INHALT'];
                    $arr [$a] ['BALKON_DAT'] = $arr_details [0] ['DETAIL_DAT'];
                    $arr [$a] ['BALKON_BEM'] = $arr_details [0] ['DETAIL_BEMERKUNG'];
                } else {
                    $arr [$a] ['BALKON'] = '------';
                    $arr [$a] ['BALKON_DAT'] = 0;
                    $arr [$a] ['BALKON_BEM'] = '';
                }
                unset ($arr_details);

                /* Heizungsart aus Details */
                $arr_details = $d->finde_detail_inhalt_last_arr('Einheit', $einheit_id, 'Heizungsart');
                if (!empty($arr_details)) {
                    $arr [$a] ['HEIZUNGSART'] = $arr_details [0] ['DETAIL_INHALT'];
                    $arr [$a] ['HEIZUNGSART_DAT'] = $arr_details [0] ['DETAIL_DAT'];
                    $arr [$a] ['HEIZUNGSART_BEM'] = $arr_details [0] ['DETAIL_BEMERKUNG'];
                } else {
                    $arr [$a] ['HEIZUNGSART'] = '-----';
                    $arr [$a] ['HEIZUNGSART_DAT'] = 0;
                    $arr [$a] ['HEIZUNGSART_BEM'] = '';
                }
                unset ($arr_details);

                /* Energieausweis aus Details vom Haus */
                $arr_details = $d->finde_detail_inhalt_last_arr('Haus', $arr [$a] ['HAUS_ID'], 'Energieausweis vorhanden');
                if (!empty($arr_details)) {
                    $arr [$a] ['ENERGIEAUS'] = $arr_details [0] ['DETAIL_INHALT'];
                    $arr [$a] ['ENERGIEAUS_DAT'] = $arr_details [0] ['DETAIL_DAT'];
                    $arr [$a] ['ENERGIEAUS_BEM'] = $arr_details [0] ['DETAIL_BEMERKUNG'];
                } else {
                    $arr [$a] ['ENERGIEAUS'] = '-----';
                    $arr [$a] ['ENERGIEAUS_DAT'] = 0;
                    $arr [$a] ['ENERGIEAUS_BEM'] = '';
                }
                unset ($arr_details);

                /* Energieausweis Gültigkeit aus Details vom Haus */
                $arr_details = $d->finde_detail_inhalt_last_arr('Haus', $arr [$a] ['HAUS_ID'], 'Energieausweis bis');
                if (!empty($arr_details)) {
                    $arr [$a] ['ENERGIEAUS_BIS'] = $arr_details [0] ['DETAIL_INHALT'];
                    $arr [$a] ['ENERGIEAUS_BIS_DAT'] = $arr_details [0] ['DETAIL_DAT'];
                    $arr [$a] ['ENERGIEAUS_BIS_BEM'] = $arr_details [0] ['DETAIL_BEMERKUNG'];
                } else {
                    $arr [$a] ['ENERGIEAUS_BIS'] = '-----';
                    $arr [$a] ['ENERGIEAUS_BIS_DAT'] = 0;
                    $arr [$a] ['ENERGIEAUS_BIS_BEM'] = '';
                }
                unset ($arr_details);

                /* Letztes Sanierungsjahr */
                $arr_details = $d->finde_detail_inhalt_last_arr('Einheit', $einheit_id, 'Jahr der letzten Sanierung');
                if (!empty($arr_details)) {
                    $arr [$a] ['JAHR_S'] = $arr_details [0] ['DETAIL_INHALT'];
                    $arr [$a] ['JAHR_S_DAT'] = $arr_details [0] ['DETAIL_DAT'];
                    $arr [$a] ['JAHR_S_BEM'] = $arr_details [0] ['DETAIL_BEMERKUNG'];
                } else {
                    $arr [$a] ['JAHR_S'] = '-----';
                    $arr [$a] ['JAHR_S_DAT'] = 0;
                    $arr [$a] ['JAHR_S_BEM'] = '';
                }
                unset ($arr_details);

                /* Fortschritt Bauphase */
                $arr_details = $d->finde_detail_inhalt_last_arr('Einheit', $einheit_id, 'Fertigstellung in Prozent');
                if (!empty($arr_details)) {
                    $arr [$a] ['FERTIG_BAU'] = $arr_details [0] ['DETAIL_INHALT'];
                    $arr [$a] ['FERTIG_BAU_DAT'] = $arr_details [0] ['DETAIL_DAT'];
                    $arr [$a] ['FERTIG_BAU_BEM'] = $arr_details [0] ['DETAIL_BEMERKUNG'];
                } else {
                    $arr [$a] ['FERTIG_BAU'] = '-1';
                    $arr [$a] ['FERTIG_BAU_DAT'] = 0;
                    $arr [$a] ['FERTIG_BAU_BEM'] = '';
                }
                unset ($arr_details);

                /* Notiz */
                $arr_details = $d->finde_detail_inhalt_last_arr('Einheit', $einheit_id, 'Sanierung Notiz');
                if (!empty($arr_details)) {
                    $arr [$a] ['NOTIZ'] = $arr_details [0] ['DETAIL_INHALT'];
                    $arr [$a] ['NOTIZ_DAT'] = $arr_details [0] ['DETAIL_DAT'];
                    $arr [$a] ['NOTIZ_BEM'] = $arr_details [0] ['DETAIL_BEMERKUNG'];
                } else {
                    $arr [$a] ['NOTIZ'] = '';
                    $arr [$a] ['NOTIZ_DAT'] = 0;
                    $arr [$a] ['NOTIZ_BEM'] = '';
                }
                unset ($arr_details);

                /* Gereinigt am */
                $arr_details = $d->finde_detail_inhalt_last_arr('Einheit', $einheit_id, 'Gereinigt am');
                if (!empty($arr_details)) {
                    $arr [$a] ['GEREINIGT'] = $arr_details [0] ['DETAIL_INHALT'];
                    $arr [$a] ['GEREINIGT_DAT'] = $arr_details [0] ['DETAIL_DAT'];
                    $arr [$a] ['GEREINIGT_BEM'] = $arr_details [0] ['DETAIL_BEMERKUNG'];
                } else {
                    $arr [$a] ['GEREINIGT'] = '';
                    $arr [$a] ['GEREINIGT_DAT'] = 0;
                    $arr [$a] ['GEREINIGT_BEM'] = '';
                }
                unset ($arr_details);

                $arr [$a] ['EINHEIT_LAGE'] = ltrim(rtrim($arr [$a] ['EINHEIT_LAGE']));

                $e = new einheit ();

                $l_mv_id = $e->get_last_mietvertrag_id($einheit_id);
                $arr [$a] ['L_MV_ID'] = $l_mv_id;

                if (isset ($l_mv_id) && !empty ($l_mv_id)) {
                    $mvs = new mietvertraege ();
                    $mvs->get_mietvertrag_infos_aktuell($l_mv_id);
                    $arr [$a] ['L_VON'] = $mvs->mietvertrag_von_d;
                    $arr [$a] ['L_BIS'] = $mvs->mietvertrag_bis_d;
                    $d1 = new DateTime ($mvs->mietvertrag_von_d);
                    $d2 = new DateTime ($mvs->mietvertrag_bis_d);
                    $diff = $d2->diff($d1);
                    // print_r( $diff ) ;
                    $arr [$a] ['L_MIETJAHRE'] = "$diff->y";
                    $arr [$a] ['L_MIETMONATE'] = "$diff->m";
                    $arr [$a] ['L_MIETER'] = $mvs->personen_name_string;
                } else {
                    $arr [$a] ['L_VON'] = '';
                    $arr [$a] ['L_BIS'] = '';
                    $arr [$a] ['L_MIETJAHRE'] = '';
                    $arr [$a] ['L_MIETMONATE'] = "";
                    $arr [$a] ['L_MIETER'] = 'LEER';
                }
            }

            $oo = new objekt ();
            $oo->get_objekt_infos($objekt_id);

            $f->fieldset('Sanierungsliste für ' . $oo->objekt_kurzname, 'sani');

            // echo "<h2>SANIERUNGSLISTE $oo->objekt_kurzname - Leerstände bis $datum_d (heute + $monate Monate)</h2>";
            echo "<table class='striped'>";
            echo "<thead><tr><th>EINHEITEN BIS $datum_d</th><th>AUSSTATTUNG</th><th>SANIER-<br>VERLAUF</th><th>JAHR DER<br>LETZTEN<br>SANIERUNG</th><th>ENERGIE<br>AUSWEIS</th><th>ENERGIE<br>AUSWEIS<br>BIS</th><th>REINIGEN</th></tr></thead>";
            $anz_e = count($arr);
            for ($a = 0; $a < $anz_e; $a++) {
                $einheit_id = $arr [$a] ['EINHEIT_ID'];
                $haus_id = $arr [$a] ['HAUS_ID'];
                $einheit_kurzname = $arr [$a] ['EINHEIT_KURZNAME'];
                $fertig_bau = ltrim(rtrim($arr [$a] ['FERTIG_BAU']));
                $anschrift = $arr [$a] ['HAUS_STRASSE'];
                $einheit_qm = nummer_punkt2komma($arr [$a] ['EINHEIT_QM']);
                $einheit_lage = $arr [$a] ['EINHEIT_LAGE'];
                $einheit_typ = $arr [$a] ['TYP'];
                $energieausweis = $arr [$a] ['ENERGIEAUS'];
                $energieausweis_bis = $arr [$a] ['ENERGIEAUS_BIS'];
                $energieausweis_bis_dat = $arr [$a] ['ENERGIEAUS_BIS_DAT'];

                $zimmer = $arr [$a] ['ZIMMER'];
                $zimmer_dat = $arr [$a] ['ZIMMER_DAT'];
                $balkon = $arr [$a] ['BALKON'];
                $balkon_dat = $arr [$a] ['BALKON_DAT'];

                $heizart = $arr [$a] ['HEIZUNGSART'];
                $heizart_dat = $arr [$a] ['HEIZUNGSART_DAT'];

                $l_von = $arr [$a] ['L_VON'];
                $l_bis = $arr [$a] ['L_BIS'];
                $l_mieter = $arr [$a] ['L_MIETER'];

                /* echo "<tr><td width=\"50px\">$link_einheit</td>"; */
                echo "<td><table class=\"details\">";
                echo "<tr><td width=\"125px\">Einheit:</td><td><label>$einheit_kurzname</label></td></tr>";
                echo "<tr><td>Anschrift:</td><td>$anschrift</td></tr>";
                echo "<tr><td>Fläche:</td><td>$einheit_qm m²</td></tr>";
                echo "<tr><td>Lage:</td><td>$einheit_lage</td></tr>";
                echo "<tr><td>Typ:</td><td>$einheit_typ</td></tr>";
                echo "<tr><td>Letzter Mieter:</td><td>$l_mieter</td></tr>";
                echo "<tr><td>Mietzeit:</td><td>$l_von - $l_bis</td></tr>";
                $l_jahr = $arr [$a] ['L_MIETJAHRE'];
                $l_monate = $arr [$a] ['L_MIETMONATE'];

                if ($l_jahr <= 10) {
                    $max = 10;
                }

                if ($l_jahr > 10 && $l_jahr <= 20) {
                    $max = 20;
                }

                if ($l_jahr > 20 && $l_jahr <= 30) {
                    $max = 30;
                }
                if ($l_jahr > 30 && $l_jahr <= 50) {
                    $max = 50;
                }
                if ($l_jahr > 50) {
                    $max = 100;
                }

                echo "<tr><td>Mietdauer:</td><td><progress max=\"$max\" value=\"$l_jahr\"></progress> $l_jahr" . "J:$l_monate" . "M</td></tr>";

                echo "</table></td>";

                echo "<td>";
                echo "<table class=\"details\">";
                echo "<tr><td>";
                $link_zimmer = "<div class=\"input-field\">
                                    <input id=\"lnk_zimmer$objekt_id.'_'.$a\" value='$zimmer' type=\"text\" onchange=\"change_detail_no_prompt('Zimmeranzahl', this.value, '$zimmer_dat', 'Einheit', '$einheit_id')\">
                                    <label for=\"lnk_zimmer$objekt_id.'_'.$a\">Zimmeranzahl</label>
                                </div>";
                echo $link_zimmer;
                echo "</td></tr>";
                echo "<tr><td>";
                $js = " onchange=\"change_detail_no_prompt('Balkon', this.value, '$balkon_dat', 'Einheit', '$einheit_id')\"";
                $d->dropdown_optionen('Balkon', 'dd_balkon' . $objekt_id . '_' . $a, 'dd_balkon' . $objekt_id . '_' . $a, 'Balkon', $balkon, $js);
                echo "</td></tr>";

                echo "<tr><td>";
                $js = " onchange=\"change_detail_no_prompt('Heizungsart', this.value, '$heizart_dat', 'Einheit', '$einheit_id')\"";
                $d->dropdown_optionen('Heizungsart', 'dd_heizart' . $objekt_id . '_' . $a, 'dd_heizart' . $objekt_id . '_' . $a, 'Heizungsart', $heizart, $js);
                echo "</td></tr>";

                echo "</table>";
                echo "</td>";
                // change_detail(anzeige_text, wert, detail_dat)*/
                $fertig_bau_dat = $arr [$a] ['FERTIG_BAU_DAT'];
                $notiz_dat = $arr [$a] ['NOTIZ_DAT'];
                $notiz = $arr [$a] ['NOTIZ'];
                echo "<td width=\"200px\"><div style=\"height: 100%;\"><progress onclick=\"change_detail('Fertigstellung in Prozent', '$fertig_bau', '$fertig_bau_dat', 'Einheit', '$einheit_id')\" max=\"100\" value=\"";
                echo $fertig_bau;
                echo "\"></progress>$fertig_bau</div>";
                echo "<div class=\"input-field\">
                        <textarea id=\"textarea1\" class=\"materialize-textarea\" onchange=\"change_detail_no_prompt('Sanierung Notiz', this . value, '$notiz_dat', 'Einheit', '$einheit_id')\">" . $notiz . "</textarea>
                        <label for=\"textarea1\">Notiz</label>
                      </div>";
                echo "</td>";

                $sanierungs_jahr = $arr [$a] ['JAHR_S'];
                $sanierungs_jahr_dat = $arr [$a] ['JAHR_S_DAT'];
                $link_san_jahr = "<a class=\"details\" onclick=\"change_detail('Jahr der letzten Sanierung', '$sanierungs_jahr', '$sanierungs_jahr_dat', 'Einheit', '$einheit_id')\">&nbsp;$sanierungs_jahr</a>";
                echo "<td><center>$link_san_jahr</center></td>";
                echo "<td>";
                $d = new detail ();
                $energieausweis_dat = $arr [$a] ['ENERGIEAUS_DAT'];
                $js = " onchange=\"change_detail_no_prompt('Energieausweis vorhanden', this.value, '$energieausweis_dat', 'Haus', '$haus_id')\"";
                $d->dropdown_optionen('Energieausweis', 'dd_ea' . $objekt_id . '_' . $a, 'dd_ea' . $objekt_id . '_' . $a, 'Energieausweis vorhanden', $energieausweis, $js);

                echo "</td>";

                echo "<td>";
                $link_eausweis_bis = "<a class=\"details\" onclick=\"change_detail('Energieausweis bis', '$energieausweis_bis', '$energieausweis_bis_dat', 'Haus', '$haus_id')\">&nbsp;$energieausweis_bis</a>";
                echo "$link_eausweis_bis";

                echo "</td>";

                echo "<td>";
                $reinigen = $arr [$a] ['GEREINIGT'];
                $reinigen_dat = $arr [$a] ['GEREINIGT_DAT'];
                echo "<div class='input-field'>
                            <input class='datepicker' value='" . $reinigen . "' id='link_reinigen_" . $objekt_id . '_' . $a . "' type='date' onchange=\"change_detail_no_prompt('Gereinigt am', this.value, '$reinigen_dat', 'Einheit', '$einheit_id')\"/>
                            <label for='link_reinigen_" . $objekt_id . '_' . $a . "'>Gereinigt am</label>
                      </div>";
                echo "</td>";

                echo "</tr>";
            }

            echo "</table>";

            /* Statistik */
            $this->stat_sanierung($objekt_id, $monate, $w, $h);
            $f->fieldset_ende();
        }
    }

    function stat_sanierung($objekt_id = null, $monate = null, $w = 300, $h = 500)
    {
        if ($objekt_id == null) {
            fehlermeldung_ausgeben("Objekt wählen");
        } else {
            $li = new listen ();
            $f = new formular ();
            $oo = new objekt ();
            $oo->get_objekt_infos($objekt_id);
            $datum_heute = date("Y-m-d");
            $mi = new miete ();
            $datum_bis = $mi->tage_plus($datum_heute, $monate * 31);
            $monat_array = $li->monats_array($datum_heute, $datum_bis);

            $f->fieldset("LEERSTANDSÜBERSICHT $oo->objekt_kurzname", 'vue');
            echo "<div class='row'>";
            for ($a = 0; $a < $monate; $a++) {
                $monat = $monat_array [$a] ['MONAT'];
                $jahr = $monat_array [$a] ['JAHR'];
                $ima1 = $this->get_png($objekt_id, $monat, $jahr, 800, 600);
                echo "<div class='col-xs-12 col-md-4 col-lg-3'>";
                echo "<img class='materialboxed' width='100%' src=\"$ima1\" alt=\"Leerstandsübersicht $a\">";
                echo "</div>";
            }
            echo "</div>";
            $f->fieldset_ende();
        }
    }

    /* Liefert den Leerstands-array, wenn Baustellenfortschritt mehr als 99% */

    function get_png($objekt_id, $monat, $jahr, $w = 300, $h = 200)
    {
        $monat = sprintf('%02d', $monat);

        $plot = new PHPlot ($w, $h, "/tmp/plot_sanierung.png");
        $plot->SetImageBorderType('plain');
        $plot->SetPlotType('stackedbars');
        $plot->SetDataType('text-data');
        $plot->SetShading(10);
        $plot->SetLegendReverse(True);

        $oo = new objekt ();
        $oo->get_objekt_infos($objekt_id);
        $anz_einheiten_alle = $oo->anzahl_einheiten_objekt($objekt_id);

        $datum_heute = "$jahr-$monat-01";
        $mi = new miete ();
        $datum_vormonat = $mi->tage_minus($datum_heute, 30);

        $arr = $this->leerstand_finden_monat($objekt_id, $datum_vormonat);
        $anz_leer_vormonat = count($arr);

        $arr_leer = $this->leerstand_finden_monat($objekt_id, $datum_heute);
        $anz_leer_akt = count($arr_leer);

        $leere = $this->array_intersect_recursive($arr_leer, $arr, 'EINHEIT_KURZNAME');
        $vermietete = $this->array_intersect_recursive($arr, $arr_leer, 'EINHEIT_KURZNAME');

        $leer_akt_string = '';
        $anz__L = count($leere);
        if ($anz__L > 0) {
            for ($ee = 0; $ee < $anz__L; $ee++) {
                $leer_akt_string .= $leere [$ee] . "\n";
            }
        }

        $vermietet_akt_string = '';
        $anz__V = count($vermietete);
        if ($anz__V > 0) {
            for ($ee = 0; $ee < $anz__V; $ee++) {
                $vermietet_akt_string .= $vermietete [$ee] . "\n";
            }
        }

        $bilanz_akt = $anz__V - $anz__L;

        $z = 0;

        $data [$z] [] = "VOR-\nMONAT";
        $data [$z] [] = 0;
        $data [$z] [] = $anz_leer_vormonat;

        $z++;

        $data [$z] [] = "LEER-\nAKTUELL";
        $data [$z] [] = 0;
        $data [$z] [] = 0;
        $data [$z] [] = $anz_leer_akt;

        $z++;
        $data [$z] [] = "LEER\n\n$leer_akt_string";
        $data [$z] [] = '0';
        $data [$z] [] = '0';
        $data [$z] [] = $anz__L;

        $z++;
        $data [$z] [] = "VERM.\n\n$vermietet_akt_string";
        $data [$z] [] = '0';
        $data [$z] [] = $anz__V;

        $z++;
        $data [$z] [] = "BILANZ\nEIN/AUS";

        if ($bilanz_akt < 0) {
            $data [$z] [] = 0;
            $data [$z] [] = 0;
            $data [$z] [] = 0;
            $data [$z] [] = 0;
            $data [$z] [] = $bilanz_akt;
        } else {
            $data [$z] [] = 0;
            $data [$z] [] = $bilanz_akt;
        }

        $plot->SetYDataLabelPos('plotstack');

        $plot->SetDataValues($data);

        // Main plot title:
        $plot->SetTitle("$oo->objekt_kurzname $monat/$jahr");

        $plot->SetShading(0);

        $plot->SetXTickLabelPos('none');
        $plot->SetXTickPos('none');

        // Draw it
        $plot->SetIsInline(true);
        $plot->DrawGraph();

        $ima = $plot->EncodeImage();
        return $ima;
    }

    function array_intersect_recursive($arr_new, $arr_old, $field)
    {
        $anz_new = count($arr_new);
        $anz_old = count($arr_old);

        for ($a = 0; $a < $anz_new; $a++) {
            $arr_new_tmp [] = $arr_new [$a] [$field];
        }

        for ($a = 0; $a < $anz_old; $a++) {
            $arr_old_tmp [] = $arr_old [$a] [$field];
        }

        $new_arr = array_merge(array_unique(array_diff($arr_new_tmp, $arr_old_tmp)), array());
        if (count($new_arr) > 0) {
            return $new_arr;
        }
    }

    function vermietungsliste($objekt_id = null, $monate = null, $w = 250, $h = 200)
    {
        /* Abrufen des Leerstands-array, wenn Baustellenfortschritt mehr als 99% */
        $o = new objekt ();
        $o_name = $o->get_objekt_name($objekt_id);
        $f = new formular ();
        $f->fieldset("Vermietungsliste der fertiggestellten Einheiten in $o_name", 'vliste');

        $arr = $this->vermietungsliste_arr($objekt_id, $monate);

        $anz = count($arr);
        if ($anz > 0) {

            /* Filterwahl generieren */
            if (session()->has('filter')) {
                session()->forget('filter');
            }

            for ($a = 0; $a < $anz; $a++) {
                $zimmer = $arr [$a] ['ZIMMER'];
                $balkon = $arr [$a] ['BALKON'];
                $heizungsart = $arr [$a] ['HEIZUNGSART'];

                if (!empty ($zimmer)
                    && $zimmer != '------'
                ) {
                    if (session()->has('filter.zimmer')) {
                        if (!in_array($zimmer, session()->get('filter')['zimmer'])) {
                            session()->push('filter.zimmer', $zimmer);
                        }
                    } else {
                        session()->push('filter.zimmer', $zimmer);
                    }
                }

                if (!empty ($balkon)
                    && $balkon != '------'
                ) {
                    if (session()->has('filter.balkon')) {
                        if (!in_array($balkon, session()->get('filter')['balkon'])) {
                            session()->push('filter.balkon', $balkon);
                        }
                    } else {
                        session()->push('filter.balkon', $balkon);
                    }
                }

                if (!empty ($heizungsart)
                    && $heizungsart != '------'
                ) {
                    if (session()->has('filter.heizung')) {
                        if (!in_array($heizungsart, session()->get('filter')['heizung'])) {
                            session()->push('filter.heizung', $heizungsart);
                        }
                    } else {
                        session()->push('filter.heizung', $heizungsart);
                    }
                }
            }
            $f->erstelle_formular("Mögliche Filterung", null);
            /* Filter bereinigen */
            if (session()->has('filter.zimmer')) {
                $filter_zimmer = array_unique(session()->get('filter')['zimmer']);
                natsort($filter_zimmer);
            } else {
                $filter_zimmer = '';
            }

            if (session()->has('filter.balkon')) {
                $filter_balkon = array_unique(session()->get('filter')['balkon']);
                natsort($filter_balkon);
            } else {
                $filter_balkon = '';
            }

            if (session()->has('filter.heizung')) {
                $filter_heizung = array_unique(session()->get('filter')['heizung']);
                natsort($filter_heizung);
            } else {
                $filter_heizung = '';
            }
            /* Sortierung der Optionen */

            /* Darstellung der Filter */
            echo "<table>";
            echo "<tr><th>Zimmer</th><th>Balkon</th><th>Heizung</th></tr>";
            echo "<tr>";
            echo "<td>";
            if (is_array($filter_zimmer)) {
                $anz_fi = count($filter_zimmer);
                for ($fo = 0; $fo < $anz_fi; $fo++) {
                    $wert = $filter_zimmer [$fo];
                    if (session()->has('aktive_filter.zimmer')) {
                        if (!in_array($wert, session()->get('aktive_filter')['zimmer'])) {
                            $f->check_box_js1("Zimmer[]", $objekt_id . "_" . $wert, $wert, "$wert Zimmer", null, null);
                        } else {
                            $f->check_box_js1("Zimmer[]", $objekt_id . "_" . $wert, $wert, "$wert Zimmer", null, 'checked');
                        }
                    } else {
                        $f->check_box_js1("Zimmer[]", $objekt_id . "_" . $wert, $wert, "$wert Zimmer", null, null);
                    }
                }
            }
            echo "</td>";

            echo "<td>";
            if (is_array($filter_balkon)) {
                $anz_fi = count($filter_balkon);
                for ($fo = 0; $fo < $anz_fi; $fo++) {
                    $wert = $filter_balkon [$fo];
                    if (session()->has('aktive_filter.balkon')) {
                        if (!in_array($wert, session()->get('aktive_filter')['balkon'])) {
                            $f->check_box_js1("Balkon[]", $objekt_id . "_" . $wert, $wert, "$wert", null, null);
                        } else {
                            $f->check_box_js1("Balkon[]", $objekt_id . "_" . $wert, $wert, "$wert", null, 'checked');
                        }
                    } else {
                        $f->check_box_js1("Balkon[]", $objekt_id . "_" . $wert, $wert, "$wert", null, null);
                    }
                }
            }
            echo "</td>";

            echo "<td>";
            if (is_array($filter_heizung)) {
                $anz_fi = count($filter_heizung);
                for ($fo = 0; $fo < $anz_fi; $fo++) {
                    $wert = $filter_heizung [$fo];
                    if (session()->has('aktive_filter.heizung')) {
                        if (!in_array($wert, session()->get('aktive_filter')['heizung'])) {
                            $f->check_box_js1("Heizung[]", $objekt_id . "_" . $wert, $wert, "$wert", null, null);
                        } else {
                            $f->check_box_js1("Heizung[]", $objekt_id . "_" . $wert, $wert, "$wert", null, 'checked');
                        }
                    } else {
                        $f->check_box_js1("Heizung[]", $objekt_id . "_" . $wert, $wert, "$wert", null, null);
                    }
                }
            }
            echo "</td>";

            echo "</tr></table>";
            $f->send_button('BTN_filters', 'FILTER ANWENDEN');
            $f->hidden_feld('option', 'filter_setzen');
            $f->ende_formular();

            $f->fieldset('Suchergebnis', 'se');

            echo "<table class=\"sortable\">";
            echo "<tr><th>EINHEIT</th><th>TYP</th><th>ANSCHRIFT</th><th>LAGE</th><th>ZI.</th><th>QM</th><th>BAL-<br>KON</th><th>HEI-<br>ZUNG</th><th>LETZE<br>SANIERUNG</th><th>FERTIG</th><th>REIN-<br>IGUNG</th><th>BK<br>SCHN.</th><th>BK</th><th>HK<br>SCHN.</th><th>HK</th><th>KALT</th><th>BRU-<br>TTO</th><th>VERTRAGS-<br>BEGINN</th></tr>";
            for ($a = 0; $a < $anz; $a++) {
                $einheit_id = $arr [$a] ['EINHEIT_ID'];
                $ma = new mietanpassung ();
                $ms_feld = $ma->get_ms_feld($einheit_id);
                $ms_jahr = $ma->get_ms_jahr();
                $ma->get_spiegel_werte($ms_jahr, $ms_feld);
                $ms_20proz = nummer_komma2punkt(nummer_punkt2komma($ma->o_wert * 1.2));

                $einheit_kn = $arr [$a] ['EINHEIT_KURZNAME'];
                $link_einheit = "<a class=\"einheit\" href='" . route('web::uebersicht::legacy', ['anzeigen' => 'einheit', 'einheit_id' => $einheit_id]) . "'>$einheit_kn</a>";
                $link_besichtigung_pdf = "<a href='" . route('web::leerstand::legacy', ['option' => 'besichtigung_pdf', 'einheit_id' => $einheit_id]) . "'><img src=\"images/pdf_dark.png\">Besichtigung</a>";
                $link_bewerbung_pdf = "<a href='" . route('web::leerstand::legacy', ['option' => 'bewerbung_pdf', 'einheit_id' => $einheit_id]) . "'><img src=\"images/pdf_dark.png\">Bewerbung</a>";
                $einheit_qm = $arr [$a] ['EINHEIT_QM'];
                $einheit_qm_a = nummer_punkt2komma($arr [$a] ['EINHEIT_QM']);
                $einheit_lage = $arr [$a] ['EINHEIT_LAGE'];
                $l_mieter = $arr [$a] ['L_MIETER'];

                $typ = $arr [$a] ['TYP'];
                $str = $arr [$a] ['HAUS_STRASSE'];
                $zimmer = $arr [$a] ['ZIMMER'];
                $zimmer_p = nummer_komma2punkt($arr [$a] ['ZIMMER']);

                $balkon = $arr [$a] ['BALKON'];
                $heizungsart = $arr [$a] ['HEIZUNGSART'];
                $jahr_s = $arr [$a] ['JAHR_S'];
                $fertig_bau_bem = $arr [$a] ['FERTIG_BAU_BEM'];
                $gereinigt = $arr [$a] ['GEREINIGT'];
                $gereinigt_bem = $arr [$a] ['GEREINIGT_BEM'];
                $kaltmiete = $arr [$a] ['KALTMIETE'];
                $kaltmiete_a = nummer_punkt2komma_t(nummer_komma2punkt($arr [$a] ['KALTMIETE']));
                if (isset ($kaltmiete) && !empty ($kaltmiete) && $kaltmiete > 0) {
                    $kalt_qm = nummer_punkt2komma(nummer_komma2punkt($kaltmiete) / $einheit_qm);
                } else {
                    $kalt_qm = 0;
                }
                $kaltmiete_dat = $arr [$a] ['KALTMIETE_DAT'];
                $kaltmiete_bem = $arr [$a] ['KALTMIETE_BEM'];

                /* BK für vermietung aus Details */
                $bk = $arr [$a] ['BK'];
                $bk_dat = $arr [$a] ['BK_DAT'];
                /* NK SCHNITT */
                $nk = $arr [$a] ['NK_D'];

                /* HK für vermietung aus Details */
                $hk = $arr [$a] ['HK'];
                $hk_dat = $arr [$a] ['HK_DAT'];

                /* HK SCHNITT */
                $hk_s = $arr [$a] ['HK_D'];

                $brutto_miete = nummer_punkt2komma(nummer_komma2punkt($kaltmiete) + nummer_komma2punkt($bk) + nummer_komma2punkt($hk));
                $netto_miete_20 = $einheit_qm * $ms_20proz;

                $mietvertrag = \App\Models\Mietvertraege::whereHas('einheit', function ($query) use ($einheit_id) {
                    $query->where('EINHEIT_ID', $einheit_id);
                })->orderBy('MIETVERTRAG_VON', 'DESC')
                    ->first();

                if ($mietvertrag && $mietvertrag->MIETVERTRAG_BIS) {
                    $mietvertragsende = '<br>Ende: ' . (new \Carbon\Carbon($mietvertrag->MIETVERTRAG_BIS))->format('d.m.Y');
                } else {
                    $mietvertragsende = '';
                }

                /* Besichtigungstermin für Vermietung aus Details */
                if ($arr [$a] ['B_TERMIN']) {
                    $b_termin = $arr [$a] ['B_TERMIN'];
                    $b_termin_text = $arr [$a] ['B_TERMIN'];
                } else {
                    if ($mietvertrag
                        && $mietvertrag->MIETVERTRAG_BIS
                        && ((new \Carbon\Carbon($mietvertrag->MIETVERTRAG_BIS)) > \Carbon\Carbon::today())
                    ) {
                        $b_termin = (new \Carbon\Carbon($mietvertrag->MIETVERTRAG_BIS))->addDays(1)->format('d.m.Y');
                    } else {
                        $b_termin = \Carbon\Carbon::today()->addMonths(1)->firstOfMonth()->format('d.m.Y');
                    }
                    $b_termin_text = '<i>' . $b_termin . '</i>';
                }
                $b_termin_dat = $arr [$a] ['B_TERMIN_DAT'];

                /* Reservierung aus Details */
                $b_reservierung = $arr [$a] ['B_RESERVIERUNG'];
                $b_reservierung_dat = $arr [$a] ['B_RESERVIERUNG_DAT'];
                $b_reservierung_bem = $arr [$a] ['B_RESERVIERUNG_BEM'];

                $anzeigen_zimmer = false;
                $anzeigen_balkon = false;
                $anzeigen_heizung = false;

                /* gesetzte Filter */
                if (session()->has('aktive_filter.zimmer')) {
                    if (in_array($zimmer, session()->get('aktive_filter')['zimmer'])) {
                        $anzeigen_zimmer = true;
                    }
                } else {
                    $anzeigen_zimmer = true;
                }

                if (session()->has('aktive_filter.balkon')) {
                    if (in_array($balkon, session()->get('aktive_filter')['balkon'])) {
                        $anzeigen_balkon = true;
                    }
                } else {
                    $anzeigen_balkon = true;
                }

                if (session()->has('aktive_filter.heizung')) {
                    if (in_array($heizungsart, session()->get('aktive_filter')['heizung'])) {
                        $anzeigen_heizung = true;
                    }
                } else {
                    $anzeigen_heizung = true;
                }

                if ($anzeigen_balkon == true && $anzeigen_zimmer == true && $anzeigen_heizung == true) {
                    $link_kaltmiete = "<a class=\"details\" onclick=\"change_detail('Vermietung-Kaltmiete', '$kaltmiete', '$kaltmiete_dat', 'Einheit', '$einheit_id')\">$kaltmiete_a</a>";
                    $link_bk = "<a class=\"details\" onclick=\"change_detail('Vermietung-BK', '$bk', '$bk_dat', 'Einheit', '$einheit_id')\">$bk</a>";
                    $link_hk = "<a class=\"details\" onclick=\"change_detail('Vermietung-HK', '$hk', '$hk_dat', 'Einheit', '$einheit_id')\">$hk</a>";
                    $link_termin = "<a class=\"details\" onclick=\"change_detail('Vermietung-Vertragsbeginn', '$b_termin', '$b_termin_dat', 'Einheit', '$einheit_id')\">$b_termin_text</a>";

                    if ($b_reservierung != '') {
                        $link_reservierung = "<a class=\"details\" onclick=\"change_detail('Vermietung-Reserviert', '$b_reservierung', '$b_reservierung_dat', 'Einheit', '$einheit_id')\">$b_reservierung<hr>$b_reservierung_bem</a>";
                    } else {
                        $link_reservierung = "<a class=\"details\" onclick=\"change_detail('Vermietung-Reserviert', '$b_reservierung', '$b_reservierung_dat', 'Einheit', '$einheit_id')\">Reservieren</a>";
                    }

                    if ($b_reservierung == '') {
                        echo "<tr class=\"green darken-4\">";
                    } else {
                        echo "<tr class=\"red darken-2\">";
                    }
                    echo "<td>$link_einheit<br>Ex: $l_mieter$mietvertragsende<hr>$link_besichtigung_pdf<hr>$link_bewerbung_pdf<hr>$link_reservierung</td><td>$typ</td><td>$str</td><td>$einheit_lage</td><td sorttable_customkey=\"$zimmer_p\">$zimmer</td><td>$einheit_qm_a</td><td>$balkon</td><td>$heizungsart</td><td>$jahr_s</td><td>$fertig_bau_bem</td><td>$gereinigt<hr>$gereinigt_bem</td><td>$nk</td><td>$link_bk</td><td>$hk_s</td><td>$link_hk</td><td><b>$link_kaltmiete<hr>m²-Kalt:$kalt_qm<br>(MAX20:$netto_miete_20)</b><hr>MSM-$ms_feld:$ma->m_wert<br>MSO-$ms_feld:$ma->o_wert<br>MSO20%:$ms_20proz<hr>$kaltmiete_bem</td><td><b>$brutto_miete</b></td><td>$link_termin</td></tr>";
                }
            }
            echo "</table>";
            $f->fieldset_ende();
        } else {
            fehlermeldung_ausgeben("Keine fertiggestellten Einheiten im Objekt $o_name");
        }

        $f->fieldset_ende();
    }

    function vermietungsliste_arr($objekt_id = null, $monate = null)
    {
        if ($objekt_id == null) {
            fehlermeldung_ausgeben("Objekt wählen");
        } else {

            if ($monate == null) {
                $datum = date("Y-m-d");
            } else {
                $mi = new miete ();
                $datum_heute = date("Y-m-d");

                $datum = $mi->tage_plus($datum_heute, $monate * 31);
                $datum_arr = explode('-', $datum);
                $jahr_neu = $datum_arr [0];
                $monat_neu = $datum_arr [1];

                $ltm = letzter_tag_im_monat($monat_neu, $jahr_neu);
                $datum = "$jahr_neu-$monat_neu-$ltm";
            }

            $arr = $this->leerstand_finden_monat($objekt_id, $datum);
            /* Array vervollständigen */
            $anz_e = count($arr);
            for ($a = 0; $a < $anz_e; $a++) {
                $einheit_id = $arr [$a] ['EINHEIT_ID'];
                $einheit_kurzname = $arr [$a] ['EINHEIT_KURZNAME'];

                $d = new detail ();
                /* Fortschritt Bauphase */
                $arr_details = $d->finde_detail_inhalt_last_arr('Einheit', $einheit_id, 'Fertigstellung in Prozent');
                if (!empty($arr_details)) {
                    $arr [$a] ['FERTIG_BAU'] = $arr_details [0] ['DETAIL_INHALT'];
                    $arr [$a] ['FERTIG_BAU_DAT'] = $arr_details [0] ['DETAIL_DAT'];
                    $arr [$a] ['FERTIG_BAU_BEM'] = $arr_details [0] ['DETAIL_BEMERKUNG'];
                } else {
                    $arr [$a] ['FERTIG_BAU'] = '-1';
                    $arr [$a] ['FERTIG_BAU_DAT'] = 0;
                    $arr [$a] ['FERTIG_BAU_BEM'] = '';
                }
                if ($arr_details [0] ['DETAIL_INHALT'] < 100) {
                    continue;
                }
                unset ($arr_details);

                /* Zimmeranzahl */
                $arr_details = $d->finde_detail_inhalt_last_arr('Einheit', $einheit_id, 'Zimmeranzahl');
                if (!empty($arr_details)) {
                    $arr [$a] ['ZIMMER'] = $arr_details [0] ['DETAIL_INHALT'];
                    $arr [$a] ['ZIMMER_DAT'] = $arr_details [0] ['DETAIL_DAT'];
                    $arr [$a] ['ZIMMER_BEM'] = $arr_details [0] ['DETAIL_BEMERKUNG'];
                } else {
                    $arr [$a] ['ZIMMER'] = '';
                    $arr [$a] ['ZIMMER_DAT'] = 0;
                    $arr [$a] ['ZIMMER_BEM'] = '';
                }
                unset ($arr_details);

                /* Balkon aus Details */
                $arr_details = $d->finde_detail_inhalt_last_arr('Einheit', $einheit_id, 'Balkon');
                if (!empty($arr_details)) {
                    $arr [$a] ['BALKON'] = $arr_details [0] ['DETAIL_INHALT'];
                    $arr [$a] ['BALKON_DAT'] = $arr_details [0] ['DETAIL_DAT'];
                    $arr [$a] ['BALKON_BEM'] = $arr_details [0] ['DETAIL_BEMERKUNG'];
                } else {
                    $arr [$a] ['BALKON'] = '------';
                    $arr [$a] ['BALKON_DAT'] = 0;
                    $arr [$a] ['BALKON_BEM'] = '';
                }
                unset ($arr_details);

                /* Heizungsart aus Details */
                $arr_details = $d->finde_detail_inhalt_last_arr('Einheit', $einheit_id, 'Heizungsart');
                if (!empty($arr_details)) {
                    $arr [$a] ['HEIZUNGSART'] = $arr_details [0] ['DETAIL_INHALT'];
                    $arr [$a] ['HEIZUNGSART_DAT'] = $arr_details [0] ['DETAIL_DAT'];
                    $arr [$a] ['HEIZUNGSART_BEM'] = $arr_details [0] ['DETAIL_BEMERKUNG'];
                } else {
                    $arr [$a] ['HEIZUNGSART'] = '------';
                    $arr [$a] ['HEIZUNGSART_DAT'] = 0;
                    $arr [$a] ['HEIZUNGSART_BEM'] = '';
                }
                unset ($arr_details);

                /* Energieausweis aus Details vom Haus */
                $arr_details = $d->finde_detail_inhalt_last_arr('Haus', $arr [$a] ['HAUS_ID'], 'Energieausweis vorhanden');
                if (!empty($arr_details)) {
                    $arr [$a] ['ENERGIEAUS'] = $arr_details [0] ['DETAIL_INHALT'];
                    $arr [$a] ['ENERGIEAUS_DAT'] = $arr_details [0] ['DETAIL_DAT'];
                    $arr [$a] ['ENERGIEAUS_BEM'] = $arr_details [0] ['DETAIL_BEMERKUNG'];
                } else {
                    $arr [$a] ['ENERGIEAUS'] = '------';
                    $arr [$a] ['ENERGIEAUS_DAT'] = 0;
                    $arr [$a] ['ENERGIEAUS_BEM'] = '';
                }
                unset ($arr_details);

                /* Energieausweis Gültigkeit aus Details vom Haus */
                $arr_details = $d->finde_detail_inhalt_last_arr('Haus', $arr [$a] ['HAUS_ID'], 'Energieausweis bis');
                if (!empty($arr_details)) {
                    $arr [$a] ['ENERGIEAUS_BIS'] = $arr_details [0] ['DETAIL_INHALT'];
                    $arr [$a] ['ENERGIEAUS_BIS_DAT'] = $arr_details [0] ['DETAIL_DAT'];
                    $arr [$a] ['ENERGIEAUS_BIS_BEM'] = $arr_details [0] ['DETAIL_BEMERKUNG'];
                } else {
                    $arr [$a] ['ENERGIEAUS_BIS'] = '------';
                    $arr [$a] ['ENERGIEAUS_BIS_DAT'] = 0;
                    $arr [$a] ['ENERGIEAUS_BIS_BEM'] = '';
                }
                unset ($arr_details);

                /* Letztes Sanierungsjahr */
                $arr_details = $d->finde_detail_inhalt_last_arr('Einheit', $einheit_id, 'Jahr der letzten Sanierung');
                if (!empty($arr_details)) {
                    $arr [$a] ['JAHR_S'] = $arr_details [0] ['DETAIL_INHALT'];
                    $arr [$a] ['JAHR_S_DAT'] = $arr_details [0] ['DETAIL_DAT'];
                    $arr [$a] ['JAHR_S_BEM'] = $arr_details [0] ['DETAIL_BEMERKUNG'];
                } else {
                    $arr [$a] ['JAHR_S'] = '------';
                    $arr [$a] ['JAHR_S_DAT'] = 0;
                    $arr [$a] ['JAHR_S_BEM'] = '';
                }
                unset ($arr_details);

                /* Gereinigt am */
                $arr_details = $d->finde_detail_inhalt_last_arr('Einheit', $einheit_id, 'Gereinigt am');
                if (!empty($arr_details)) {
                    $arr [$a] ['GEREINIGT'] = $arr_details [0] ['DETAIL_INHALT'];
                    $arr [$a] ['GEREINIGT_DAT'] = $arr_details [0] ['DETAIL_DAT'];
                    $arr [$a] ['GEREINIGT_BEM'] = $arr_details [0] ['DETAIL_BEMERKUNG'];
                } else {
                    $arr [$a] ['GEREINIGT'] = '------';
                    $arr [$a] ['GEREINIGT_DAT'] = 0;
                    $arr [$a] ['GEREINIGT_BEM'] = '';
                }
                unset ($arr_details);

                /* Kaltmiete */
                $arr_details = $d->finde_detail_inhalt_last_arr('Einheit', $einheit_id, 'Vermietung-Kaltmiete');
                if (!empty($arr_details)) {
                    $arr [$a] ['KALTMIETE'] = $arr_details [0] ['DETAIL_INHALT'];
                    $arr [$a] ['KALTMIETE_DAT'] = $arr_details [0] ['DETAIL_DAT'];
                    $arr [$a] ['KALTMIETE_BEM'] = $arr_details [0] ['DETAIL_BEMERKUNG'];
                } else {
                    $arr [$a] ['KALTMIETE'] = '0.00';
                    $arr [$a] ['KALTMIETE_DAT'] = 0;
                    $arr [$a] ['KALTMIETE_BEM'] = '';
                }
                unset ($arr_details);

                /* Nebenkosten */
                $arr_details = $d->finde_detail_inhalt_last_arr('Einheit', $einheit_id, 'Vermietung-BK');
                if (!empty($arr_details)) {
                    $arr [$a] ['BK'] = $arr_details [0] ['DETAIL_INHALT'];
                    $arr [$a] ['BK_DAT'] = $arr_details [0] ['DETAIL_DAT'];
                    $arr [$a] ['BK_BEM'] = $arr_details [0] ['DETAIL_BEMERKUNG'];
                } else {
                    $arr [$a] ['BK'] = '0.00';
                    $arr [$a] ['BK_DAT'] = 0;
                    $arr [$a] ['BK_BEM'] = '';
                }
                unset ($arr_details);

                /* Heizkostenkosten */
                $arr_details = $d->finde_detail_inhalt_last_arr('Einheit', $einheit_id, 'Vermietung-HK');
                if (!empty($arr_details)) {
                    $arr [$a] ['HK'] = $arr_details [0] ['DETAIL_INHALT'];
                    $arr [$a] ['HK_DAT'] = $arr_details [0] ['DETAIL_DAT'];
                    $arr [$a] ['HK_BEM'] = $arr_details [0] ['DETAIL_BEMERKUNG'];
                } else {
                    $arr [$a] ['HK'] = '0.00';
                    $arr [$a] ['HK_DAT'] = 0;
                    $arr [$a] ['HK_BEM'] = '';
                }
                unset ($arr_details);

                /* Besichtigunstermin und Zeit aus Details */
                $arr_details = $d->finde_detail_inhalt_last_arr('Einheit', $einheit_id, 'Vermietung-Vertragsbeginn');
                if (!empty($arr_details)) {
                    $arr [$a] ['B_TERMIN'] = $arr_details [0] ['DETAIL_INHALT'];
                    $arr [$a] ['B_TERMIN_DAT'] = $arr_details [0] ['DETAIL_DAT'];
                    $arr [$a] ['B_TERMIN_BEM'] = $arr_details [0] ['DETAIL_BEMERKUNG'];
                } else {
                    $arr [$a] ['B_TERMIN'] = '';
                    $arr [$a] ['B_TERMIN_DAT'] = 0;
                    $arr [$a] ['B_TERMIN_BEM'] = '';
                }
                unset ($arr_details);

                /* Reservierung */
                $arr_details = $d->finde_detail_inhalt_last_arr('Einheit', $einheit_id, 'Vermietung-Reserviert');
                if (!empty($arr_details)) {
                    $arr [$a] ['B_RESERVIERUNG'] = $arr_details [0] ['DETAIL_INHALT'];
                    $arr [$a] ['B_RESERVIERUNG_DAT'] = $arr_details [0] ['DETAIL_DAT'];
                    $arr [$a] ['B_RESERVIERUNG_BEM'] = $arr_details [0] ['DETAIL_BEMERKUNG'];
                } else {
                    $arr [$a] ['B_RESERVIERUNG'] = '';
                    $arr [$a] ['B_RESERVIERUNG_DAT'] = 0;
                    $arr [$a] ['B_RESERVIERUNG_BEM'] = '';
                }
                unset ($arr_details);

                $arr [$a] ['EINHEIT_LAGE'] = ltrim(rtrim($arr [$a] ['EINHEIT_LAGE']));

                $e = new einheit ();

                $l_mv_id = $e->get_last_mietvertrag_id($einheit_id);
                $arr [$a] ['L_MV_ID'] = $l_mv_id;

                if (isset ($l_mv_id) && !empty ($l_mv_id)) {
                    $mvs = new mietvertraege ();
                    $mvs->get_mietvertrag_infos_aktuell($l_mv_id);
                    $arr [$a] ['L_VON'] = $mvs->mietvertrag_von_d;
                    $arr [$a] ['L_BIS'] = $mvs->mietvertrag_bis_d;
                    $d1 = new DateTime ($mvs->mietvertrag_von_d);
                    $d2 = new DateTime ($mvs->mietvertrag_bis_d);
                    $diff = $d2->diff($d1);
                    $arr [$a] ['L_MIETJAHRE'] = "$diff->y";
                    $arr [$a] ['L_MIETMONATE'] = "$diff->m";
                    $arr [$a] ['L_MIETER'] = $mvs->personen_name_string;
                } else {
                    $arr [$a] ['L_VON'] = '';
                    $arr [$a] ['L_BIS'] = '';
                    $arr [$a] ['L_MIETJAHRE'] = '';
                    $arr [$a] ['L_MIETMONATE'] = "";
                    $arr [$a] ['L_MIETER'] = 'LEER';
                }
            }
        }

        $durchschnitt_nk = $this->get_durchschnitt_nk($objekt_id, 'Nebenkosten Vorauszahlung');
        $durchschnitt_hk = $this->get_durchschnitt_nk($objekt_id, 'Heizkosten Vorauszahlung');

        $anz = count($arr);
        for ($a = 0; $a < $anz; $a++) {
            /* Sanierungsstatus - Baustellenfortschritt in 50% */
            $sanierungsstatus = $arr [$a] ['FERTIG_BAU'];
            $sanierungsstatus_p = nummer_komma2punkt($sanierungsstatus);
            if ($sanierungsstatus_p > 99) {
                $einheit_qm = $arr [$a] ['EINHEIT_QM'];
                if ($einheit_qm > 0) {
                    $arr [$a] ['NK_D'] = nummer_komma2punkt(nummer_punkt2komma(($durchschnitt_nk * $einheit_qm)));
                    $arr [$a] ['HK_D'] = nummer_komma2punkt(nummer_punkt2komma(($durchschnitt_hk * $einheit_qm)));
                } else {
                    $arr [$a] ['NK_D'] = '0.00';
                    $arr [$a] ['HK_D'] = '0.00';
                }

                $n_arr [] = $arr [$a];
            }
        }
        return $n_arr;
    }

    function get_durchschnitt_nk($objekt_id, $art)
    {
        $monat = date("m");
        $jahr = date("Y");

        /* Vermietete Einheiten aus objekt */
        $o = new objekt ();
        $einheiten_alle = $o->einheiten_objekt_arr($objekt_id);
        $anz = count($einheiten_alle);

        $summe_g = 0;
        $summe_qm = 0;
        $anz_einheiten = 0;

        for ($a = 0; $a < $anz; $a++) {
            $einheit_id = $einheiten_alle [$a] ['EINHEIT_ID'];
            $einheit_qm = $einheiten_alle [$a] ['EINHEIT_QM'];

            $e = new einheit ();
            if ($e->get_einheit_status($einheit_id) == true) {
                $e->get_last_mietvertrag_id($einheit_id);
                $mv_id = $e->mietvertrag_id;
                $me = new mietentwicklung ();

                $me_arr = $me->get_kostenkat_info_aktuell($mv_id, $monat, $jahr, $art);
                if (is_array($me_arr)) {
                    if ($me_arr ['BETRAG'] > 0) {
                        $anz_einheiten++;
                        $summe_g += $me_arr ['BETRAG'];
                        $summe_qm += $einheit_qm;
                    }
                }
            }
        }
        if ($summe_qm > 0) {
            return nummer_komma2punkt(nummer_punkt2komma($summe_g / $summe_qm));
        } else {
            return '0.00';
        }
    }

    function kontrolle_preise()
    {
        $d = new detail ();
        $arr = $d->finde_detail_inhalt_arr('Vermietung-Kaltmiete');
        if (empty($arr)) {
            echo "Keine Wohnungen mit Detail Vermietung-Kaltmiete";
        } else {
            $anz = count($arr);
            echo "<table class=\"sortable\">";
            echo "<tr><th>Einheit</th><th>SOLL KM</th><th>EINTRAG</th><th>SOLL KM m²</th><th>IST KM m²</th><th>EINZUG</th><th>IST KM</th></tr>";
            for ($a = 0; $a < $anz; $a++) {
                $kos_typ = $arr [$a] ['DETAIL_ZUORDNUNG_TABELLE'];
                $kos_id = $arr [$a] ['DETAIL_ZUORDNUNG_ID'];
                $kaltmiete_soll = nummer_punkt2komma(nummer_komma2punkt($arr [$a] ['DETAIL_INHALT']));
                $bemerkung = $arr [$a] ['DETAIL_BEMERKUNG'];
                if (strtoupper($kos_typ) == strtoupper('Einheit')) {
                    $e = new einheit ();
                    $e->get_einheit_info($kos_id);
                    echo "<tr><td>$e->einheit_kurzname</td><td>$kaltmiete_soll</td><td>($bemerkung)</td>";
                    if ($e->get_einheit_status($kos_id) == true) {

                        $e->get_last_mietvertrag_id($kos_id);
                        $mv_id = $e->mietvertrag_id;

                        if (!empty ($mv_id)) {
                            $mvs = new mietvertraege ();
                            $mvs->get_mietvertrag_infos_aktuell($mv_id);
                            $einzugsdatum_arr = explode('-', $mvs->mietvertrag_von);

                            $jahr = $einzugsdatum_arr [0];
                            $monat = $einzugsdatum_arr [1];
                            $tag = $einzugsdatum_arr [2];

                            $mk = new mietkonto ();
                            $mk->kaltmiete_monatlich($mv_id, $monat, $jahr);

                            if ($tag > 1) {
                                $qm_preis = nummer_punkt2komma(nummer_komma2punkt($kaltmiete_soll) / $mvs->einheit_qm);
                                $qm_preis_ist = nummer_punkt2komma($mk->ausgangs_kaltmiete / $mvs->einheit_qm / $tag * 30);
                            } else {
                                $qm_preis = nummer_punkt2komma(nummer_komma2punkt($kaltmiete_soll) / $mvs->einheit_qm);
                                $qm_preis_ist = nummer_punkt2komma($mk->ausgangs_kaltmiete / $mvs->einheit_qm);
                            }
                            echo "<td><b>$qm_preis</b></td>";
                            if (nummer_komma2punkt($qm_preis) > nummer_komma2punkt($qm_preis_ist)) {
                                echo "<td style=\"color:red;\">$qm_preis_ist</td>";
                            } else {

                                echo "<td style=\"color:green;\">$qm_preis_ist</td>";
                            }
                            if ($tag != '01') {
                                echo "<td style=\"color:red;\">";
                            } else {
                                echo "<td style=\"color:green;\">";
                            }
                            echo "$mvs->mietvertrag_von_d</td><td>$mk->ausgangs_kaltmiete</td></tr>";
                        }
                    } else {
                        $qm_preis = nummer_punkt2komma(nummer_komma2punkt($kaltmiete_soll) / $e->einheit_qm);
                        echo "<td>$qm_preis</td></tr>";
                    }
                }
            }
            echo "</table>";
        }
    }
} // end class