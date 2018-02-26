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

    function form_interessent()
    {
        $f = new formular ();
        $f->erstelle_formular('Interessenten eingeben', '');
        $f->text_feld('Name', 'name', '', '50', 'name', '');
        $f->text_bereich('Anschrift', 'anschrift', '', 10, 5, 'anschrift');
        $f->text_feld('Telefonnr', 'tel', '', '50', 'tel', '');
        $f->text_feld('Email', 'email', '', '50', 'email', '');
        $f->datum_feld('Wunscheinzugsdatum', 'w_datum', '', 'w_datum');
        $f->text_feld('Zimmeranzahl', 'zimmer', '', '10', 'zimmer', '');
        $f->text_bereich('Hinweis', 'hinweis', '', 10, 5, 'hinweis');
        $f->hidden_feld('option', 'interessent_send');
        $f->send_button('btn_snd', 'Eintragen');
        $f->ende_formular();
    }

    function interessenten_speichern($name, $anschrift, $tel, $email, $w_datum, $zimmer, $hinweis)
    {
        $datum = date("Y-m-d");
        $w_datum_d = date_german2mysql($w_datum);
        $db_abfrage = "INSERT INTO LEERSTAND_INTERESSENT VALUES (NULL, '$name', '$anschrift', '$tel', '$email','$w_datum_d', '$datum', '$zimmer', '$hinweis','1')";
        DB::insert($db_abfrage);
        return true;
    }

    function pdf_interessentenliste($tab_arr = null)
    {
        $pdf = new Cezpdf ('a4', 'portrait');
        $bpdf = new b_pdf ();
        $bpdf->b_header($pdf, 'Partner', session()->get('partner_id'), 'portrait', 'Helvetica.afm', 6);
        if (empty ($tab_arr)) {
            $tab_arr = $this->interessenten_tab_arr();
        }
        if (empty($tab_arr)) {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\InfoMessage('Es sind keine Interessenten vorhanden.')
            );
        }
        $cols = array(
            'NAME' => "Namen",
            'ANSCHRIFT' => "Anschrift",
            'TEL' => "Telefon",
            'EMAIL' => "Email",
            'W_EINZUG' => "Wunscheinzug",
            'ZIMMER' => "Zimmer",
            'HINWEIS' => "Hinweise"
        );
        $pdf->ezTable($tab_arr, $cols, "<b>Kontaktliste Mietinteressenten</b>", array(
            'showHeadings' => 1,
            'shaded' => 1,
            'titleFontSize' => 7,
            'fontSize' => 7,
            'xPos' => 55,
            'xOrientation' => 'right',
            'width' => 500,
            'cols' => array(
                'SEITE' => array(
                    'justification' => 'left',
                    'width' => 27
                ),
                'EINHEIT' => array(
                    'justification' => 'left',
                    'width' => 50
                ),
                'ZEITRAUM' => array(
                    'justification' => 'left',
                    'width' => 90
                ),
                'EMPF' => array(
                    'justification' => 'left'
                )
            )
        ));
        ob_end_clean();
        $pdf->ezStream();
    }

    function interessenten_tab_arr()
    {
        $db_abfrage = "SELECT *, DATE_FORMAT(EINZUG, '%d.%m.%Y') AS W_EINZUG FROM LEERSTAND_INTERESSENT WHERE EINZUG>DATE(NOW()) && AKTUELL='1' ORDER BY ZIMMER, EINZUG ASC";
        $result = DB::select($db_abfrage);
        return $result;
    }

    function pdf_expose($einheit_id, $return = 0)
    {
        $e = new einheit ();
        $e->get_einheit_info($einheit_id);

        $storage = Storage::disk('fotos');
        $foto_links_arr = $storage->files("EINHEIT/$e->einheit_kurzname/ANZEIGE");
        $anz_fotos = count($foto_links_arr);
        /* wenn keine Fotos, Fotoarray leeren */
        if ($anz_fotos < 1) {
            $foto_links_arr = null;
        }

        $pdf = new Cezpdf ('a4', 'portrait');
        $bpdf = new b_pdf ();
        $bpdf->b_header($pdf, 'Partner', session()->get('partner_id'), 'portrait', 'Helvetica.afm', 6);
        $pdf->ezStopPageNumbers();
        if ($storage->exists($foto_links_arr[0])) {
            $pdf->addJpegFromFile($storage->fullPath($foto_links_arr[0]), 30, 455, 370, 250);
        } else {
            $pdf->ezText("Im Ordner " . $storage->fullPath("EINHEIT/$e->einheit_kurzname/ANZEIGE") . " fehlen Fotos", 10);
        }
        // $pdf->setColor(255/255,255/255,255/255);
        $pdf->addText(420, 700, 12, "Wohnung:  $e->einheit_kurzname");
        $pdf->addText(420, 685, 12, "$e->haus_strasse $e->haus_nummer");
        $pdf->addText(420, 670, 12, "$e->haus_plz $e->haus_stadt");
        $pdf->addText(420, 655, 12, "Lage:");
        $pdf->addText(510, 655, 12, "$e->einheit_lage");
        $pdf->addText(420, 640, 12, "Flaeche:");
        $e->einheit_qm_d = nummer_punkt2komma($e->einheit_qm);
        $pdf->addText(510, 640, 12, "$e->einheit_qm_d qm");
        $d = new detail ();
        $zimmer = $this->br2n(ltrim(rtrim($d->finde_detail_inhalt('Einheit', $einheit_id, 'Zimmeranzahl'))));
        $pdf->addText(420, 620, 12, "Zimmer:");
        $pdf->addText(510, 620, 12, "$zimmer");
        $balkon = $this->br2n(ltrim(rtrim($d->finde_detail_inhalt('Einheit', $einheit_id, 'Balkon'))));
        $pdf->addText(420, 605, 12, "Balkon:");
        $pdf->addText(510, 605, 12, "$balkon");
        $heizungsart = $this->br2n(ltrim(rtrim($d->finde_detail_inhalt('Einheit', $einheit_id, 'Heizungsart'))));
        $pdf->addText(420, 590, 12, "Heizungsart:");
        $pdf->addText(510, 590, 12, "$heizungsart");

        $expose_km = nummer_punkt2komma_t(nummer_komma2punkt($this->br2n(ltrim(rtrim($d->finde_detail_inhalt('Einheit', $einheit_id, 'Vermietung-Kaltmiete'))))));
        $expose_bk = nummer_punkt2komma_t(nummer_komma2punkt($this->br2n(ltrim(rtrim($d->finde_detail_inhalt('Einheit', $einheit_id, 'Vermietung-BK'))))));
        $expose_hk = nummer_punkt2komma_t(nummer_komma2punkt($this->br2n(ltrim(rtrim($d->finde_detail_inhalt('Einheit', $einheit_id, 'Vermietung-HK'))))));
        $brutto_miete = nummer_punkt2komma_t(nummer_komma2punkt($expose_km) + nummer_komma2punkt($expose_bk) + nummer_komma2punkt($expose_hk));

        if (!$expose_km) {
            $expose_km = '0,00';
        }

        if (!$expose_hk) {
            $expose_hk = '0,00';
        }

        if (!$expose_bk) {
            $expose_bk = '0,00';
        }

        $pdf->addText(420, 560, 12, "Miete kalt:");
        $pdf->addText(510, 560, 12, "$expose_km EUR");
        $pdf->addText(420, 545, 12, "Betriebskosten:");
        $pdf->addText(510, 545, 12, "$expose_bk EUR");
        $pdf->addText(420, 530, 12, "Heizkosten:");
        $pdf->addText(510, 530, 12, "$expose_hk EUR");
        $pdf->setStrokeColor(255 / 255, 255 / 255, 255 / 255);
        // $pdf->line(410,545,410,600);
        $pdf->line(420, 555, 545, 555);
        $pdf->addText(420, 515, 12, "Miete brutto:");
        $pdf->addText(510, 515, 12, "$brutto_miete EUR");

        // $pdf->ezSetY(450);// braucht man nicht
        // $pdf->setColor(64/255,42/255,27/255);
        // $pdf->addText(420, 500, 12, "Zustand:");
        // $pdf->addText(510, 500, 12, "Erstbezug nach Sanierung");
        $pdf->addText(420, 500, 12, "Baujahr:");
        $baujahr = $d->finde_detail_inhalt('Objekt', $e->objekt_id, 'Baujahr');
        $pdf->addText(510, 500, 12, "$baujahr");

        $expose_frei = $this->br2n(ltrim(rtrim($d->finde_detail_inhalt('Einheit', $einheit_id, 'Expose frei ab'))));
        if (!$expose_frei) {
            $expose_frei = 'sofort';
        }
        $pdf->addText(420, 485, 12, "Bezugsfrei ab:");
        $pdf->addText(510, 485, 12, "$expose_frei");

        $pdf->addText(420, 470, 12, "Kaution:");
        $pdf->addText(510, 470, 12, "3 Kaltmieten");
        $pdf->addText(420, 455, 12, "Provision:");
        $pdf->addText(510, 455, 12, "keine");

        $termin = $this->br2n(ltrim(rtrim($d->finde_detail_inhalt('Einheit', $einheit_id, 'Besichtigungstermin'))));

        $pdf->setColor(255 / 255, 0 / 255, 0 / 255);
        if ($termin) {

            $pdf->addText(420, 440, 11, "<b>Besichtigungstermin: $termin</b>");
        }
        $pdf->setColor(0 / 255, 0 / 255, 0 / 255);

        $pdf->ezSetDy(-275);

        /* ExposeText */
        // $pdf->ezSetMargins(135,430,50,50);
        $exposetext = $this->br2n(ltrim(rtrim($d->finde_detail_inhalt('Einheit', $einheit_id, 'Exposetext'))));
        $pdf->ezText($exposetext, 10, array(
            'justification' => 'full'
        ));

        $pdf->ezNewPage();
        $start = 50;
        $start_hoehe = 530;
        $anz_bild = 0;
        $akt_bild = 0;
        $seite = 1;

        for ($a = 1; $a < $anz_fotos; $a++) {

            if ($storage->exists($foto_links_arr[$a])) {
                $pdf->addJpegFromFile($storage->fullPath($foto_links_arr[$a]), $start, $start_hoehe, 200, 150);
                $start = $start + 300;
                $akt_bild++;
                $anz_bild++;

                if ($akt_bild == 2) {
                    $start = 50;
                    $start_hoehe -= 155;
                    $akt_bild = 0;
                }
                if ($anz_bild == 8) {
                    $seite++;
                    $anz_bild = 0;
                    /* Footer */

                    $pdf->ezNewPage();
                    $start = 50;
                    $start_hoehe = 530;
                }
            } else {
                $pdf->ezText($foto_links_arr[$a] . " fehlt");
            }
        }

        $pdf->setColor(0 / 255, 0 / 255, 0 / 255);
        $pdf->setStrokeColor(0 / 255, 0 / 255, 0 / 255);

        /* Planseite */

        if ($storage->exists("EINHEIT/$e->einheit_kurzname/plan.jpg")) {
            $pdf->ezNewPage();
            $pdf->addJpegFromFile($storage->fullPath("EINHEIT/$e->einheit_kurzname/plan.jpg"), 20, 20, 550, 800);
        }

        $pdf->ezSetMargins(135, 70, 50, 50);
        $pdf->setColor(0 / 255, 0 / 255, 0 / 255);

        /* Bewerbungbogen 1. Seite */
        $pdf->ezNewPage();

        $pdf->setStrokeColor(0 / 255, 0 / 255, 0 / 255);
        $pdf->addText(150, 710, 8, "Sprechzeiten:         Dienstag 9:00 - 12:00 Uhr und Donnerstag 14:00 - 17:00 Uhr");
        $pdf->addText(45, 660, 14, "<b>Fragebogen zur Wohnungsbewerbung</b>                    Wohnungs-Nr.: <b>$e->einheit_kurzname</b>");
        $pdf->addText(45, 620, 10, "Vor- und Zuname des Bewerbers     _______________________________________________________________");
        $pdf->ezSetDy(-100);
        $pdf->ezText("Ich/Wir sind bereit, nachstehende Wohnung in der $e->haus_strasse $e->haus_nummer ($e->einheit_lage), $e->haus_plz $e->haus_stadt\nab sofort/ab _________________________________ Wohngröße ca. $e->einheit_qm_d m² zu mieten.", 10);
        $pdf->ezSetDy(-10);
        $pdf->ezText("Die Miete beträgt voraussichtlich monatlich:", 10);

        $pdf->addText(300, 562, 10, "Kaltmiete");
        $pdf->addText(450, 562, 10, "$expose_km €");
        $pdf->addText(300, 542, 10, "Betriebskostenvorauszahlungen");
        $pdf->addText(450, 542, 10, "$expose_bk €");
        $pdf->addText(300, 522, 10, "Heizkostenvorauszahlungen");
        $pdf->addText(450, 522, 10, "$expose_hk €");
        $kaution = nummer_punkt2komma_t(3 * nummer_komma2punkt($expose_km));
        $pdf->ezSetDy(-50);
        $pdf->ezText("Bei Anmietung wird eine Kaution in Höhe von $kaution € (3 Kaltmieten) fällig.", 10);
        $pdf->ezSetDy(-10);
        $pdf->ezText("<b>Ich/Wir geben nachstehende Selbstauskunft:</b>", 10);
        $pdf->ezSetDy(-10);
        $pdf->ezText("1.) Vor- und Zuname: _______________________________________ Geburtsdatum: ___________________", 10);
        $pdf->ezSetDy(-10);
        $pdf->ezText("Derzeitige Anschrift: ________________________________________________________________________", 10);
        $pdf->ezSetDy(-10);
        $pdf->ezText("E-Mail: _______________________________________ Telefon: ____________________________________", 10);
        $pdf->ezSetDy(-10);
        $pdf->ezText("2.) Vor- und Zuname: _______________________________________ Geburtsdatum: ___________________", 10);
        $pdf->ezSetDy(-10);
        $pdf->ezText("Derzeitige Anschrift: ________________________________________________________________________", 10);
        $pdf->ezSetDy(-10);
        $pdf->ezText("E-Mail: _______________________________________ Telefon: ____________________________________", 10);
        $pdf->ezSetDy(-10);
        $pdf->ezText("<b>Folgende Personen gehören außerdem zum Haushalt:</b>", 10);
        $pdf->ezSetDy(-10);
        $pdf->ezText("_______________________________ geb. am ___________________ Familienstand __________________", 10);
        $pdf->ezSetDy(-10);
        $pdf->ezText("_______________________________ geb. am ___________________ Familienstand __________________", 10);
        $pdf->ezSetDy(-10);
        $pdf->ezText("_______________________________ geb. am ___________________ Familienstand __________________", 10);

        $pdf->ezSetDy(-20);
        $pdf->ezText("Zu 1. ausgeübter Beruf: _____________________________________________________________________", 10);
        $pdf->ezSetDy(-10);
        $pdf->ezText("Arbeitgeber: ___________________________________________ dort tätig seit ________________________", 10);
        $pdf->ezSetDy(-10);
        $pdf->ezText("mtl. Netto-Einkommen: ______________________ €, Verdienstbescheinigungen bitte beifügen", 10);

        $pdf->ezSetDy(-20);
        $pdf->ezText("Zu 2. ausgeübter Beruf: _____________________________________________________________________", 10);
        $pdf->ezSetDy(-10);
        $pdf->ezText("Arbeitgeber: ___________________________________________ dort tätig seit ________________________", 10);
        $pdf->ezSetDy(-10);
        $pdf->ezText("mtl. Netto-Einkommen: ______________________ €, Verdienstbescheinigungen bitte beifügen", 10);
        $pdf->ezSetDy(-30);
        $pdf->ezText("Beziehen Sie eine Rente/Pension/Sozialunterstützung? ____________________ Höhe ________________ €", 10);

        $pdf->setStrokeColor(0 / 255, 0 / 255, 0 / 255);
        $pdf->setColor(0 / 255, 0 / 255, 0 / 255);

        /* 2. Seite */
        $pdf->ezNewPage();
        $pdf->ezText("Bisherige Wohnung falls abweichend von Anschrift: _______________________________________________", 10);
        $pdf->ezSetDy(-10);
        $pdf->ezText("Bewohnt seit: ________________________________ als Hauptmieter/Untermieter ______________________", 10);
        $pdf->ezSetDy(-10);
        $pdf->ezText("Monatliche Miete _______________________ €", 10);
        $pdf->ezSetDy(-20);
        $pdf->ezText("Name und Anschrift des bisherigen Hauseigentümers/Hausverwalters:
________________________________________________________________________________________\n
________________________________________________________________________________________", 10);
        $pdf->ezSetDy(-20);
        $pdf->ezText("Ich/Wir wünsche(n) die Wohnung zu wechseln, weil:
________________________________________________________________________________________\n
________________________________________________________________________________________", 10);
        $pdf->ezSetDy(-10);
        $pdf->ezText("Ist die Miete für die letzten 12 Monate regelmäig bezahlt worden ? __________________________________", 10);
        $pdf->ezSetDy(-10);
        $pdf->ezText("Bestehen überfällige Verpflichtungen aus dem jetzigen oder früheren Mietverhältnissen ? _________________", 10);
        $pdf->ezSetDy(-10);
        $pdf->ezText("z.B. Eigentumsvorbehalten an der Einrichtung, Pfändung o.Ä. _______________________________________", 10);
        $pdf->ezSetDy(-10);
        $pdf->ezText("Bestehen Verpflichtungen aus Unterhaltszahlungen an Dritte oder Zahlungsverpflichtungen aus Kredit- oder
Darlehenstilgungen? _______________________________________________________________________", 10);
        $pdf->ezSetDy(-10);
        $pdf->ezText("Beabsichtigen Sie in der Wohnung ein Gewerbe oder eine freiberufliche Tätigkeit auszuüben ? Ja / Nein", 10);
        $pdf->ezSetDy(-10);
        $pdf->ezText("Wenn ja: ________________________________________________________________________________", 10);
        $pdf->ezSetDy(-10);
        $pdf->ezText("Welche Haustiere wollen Sie in der Wohnung halten ? _____________________________________________", 10);
        $pdf->ezSetDy(-10);
        $pdf->ezText("<b>Bitte folgende Unterlagen beilegen:", 10);

        $pdf->ezSetMargins(135, 70, 200, 50);
        $pdf->ezText("- letzten 3 Gehaltsbescheinigungen
- Bescheinigung des Arbeitgebers über ungekündigte Stellung
- Personalausweis/Reisepass in Kopie
- Mietschuldenfreiheitsbestätigung des jetzigen Vermieters
- Schufa-Auskunft</b>", 10);
        $pdf->ezSetMargins(135, 70, 50, 50);
        $pdf->ezSetDy(-10);
        $pdf->ezText("Ich/Wir versichere(n) ausdrücklich die Richtigkeit der vorstehend gemachten Angaben.
Mit einer Auskunftseinholung über mich/uns durch den Vermieter beim bisherigen Vermieter bin/sind ich/wir
einverstanden und sehe(n) die vorgeschriebene Benachrichtigung nach § 26 Bundesdatenschutzgesetzes hiermit als erfüllt an.", 10);
        $pdf->ezSetDy(-10);
        $pdf->ezText("Der Vermieter verpflichtet sich, die von ihm erfragten Daten des/der Bewerber(s) vertraulich zu behandeln.", 10);
        $pdf->ezSetDy(-30);
        $pdf->ezText("Berlin, den _________________________", 10);
        $pdf->ezSetDy(-30);
        $pdf->ezText("______________________________                                           _________________________________", 10);
        // $pdf->ezSetDy(-6);
        $pdf->ezText("            Unterschrift des/der Bewerber(s)                                                                                                     Unterschrift des/der Bewerber(s)", 7);
        /*
		 * $pdf->addText(160,35,6,"$bpdf->zeile1");
		 * $pdf->addText(130,25,6,"$bpdf->zeile2");
		 */

        if ($return == '0') {
            ob_end_clean();
            $dateiname = $e->einheit_kurzname . "_Expose.pdf";
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

    function liste_wohnungen_mit_termin($vor_nach = '>')
    {
        $e = new einheit ();
        // $arr = $this->einheiten_mit_termin_arr('',$vor_nach);//vor heute
        $arr = $this->einheiten_mit_termin_arr('', $vor_nach); // nach heute
        $anz = count($arr);

        echo "<table class=\"sortable\">";
        echo "<tr><th>EINHEIT</th><th>TERMIN</th><th>QM</th><th>ZIMMER</th><th>BALKON</th><th>OPTIONEN</th></tr>";
        for ($a = 0; $a < $anz; $a++) {
            $d = new detail ();
            $einheit_id = $arr [$a] ['EINHEIT_ID'];
            $termin = $arr [$a] ['DETAIL_INHALT'];

            $zimmer = $this->br2n(ltrim(rtrim($d->finde_detail_inhalt('Einheit', $einheit_id, 'Zimmeranzahl'))));
            $balkon = $this->br2n(ltrim(rtrim($d->finde_detail_inhalt('Einheit', $einheit_id, 'Balkon'))));
            $e->get_einheit_info($einheit_id);
            $link_einladen = "<a href='" . route('web::leerstand::legacy', ['option' => 'einladungen', 'einheit_id' => $einheit_id]) . "'>Einladen</a>";
            echo "<tr><td>$e->einheit_kurzname $e->haus_strasse $e->haus_nummer, $e->einheit_lage</td><td>$termin</td><td>$e->einheit_qm m²</td><td>$zimmer</td><td>$balkon</td>";
            if ($vor_nach == '>') {
                echo "<td>$link_einladen</td>";
            } else {
                echo "<td></td>";
            }
            echo "</tr>";
        }

        echo "</table>";
    }

    function einheiten_mit_termin_arr($objekt_id = '', $vor_nach = '>')
    {
        if (!$objekt_id) {
            $db_abfrage = "SELECT DETAIL_ZUORDNUNG_ID AS EINHEIT_ID, DETAIL_INHALT, DETAIL_BEMERKUNG, STR_TO_DATE(DETAIL_INHALT,'%d.%m.%Y') , DATE_FORMAT(NOW(), '%Y-%m-%d')  FROM `DETAIL` WHERE `DETAIL_NAME` = 'Besichtigungstermin' AND `DETAIL_AKTUELL` = '1' AND  (STR_TO_DATE(DETAIL_INHALT,'%d.%m.%Y') $vor_nach= CURDATE()) AND `DETAIL_ZUORDNUNG_TABELLE` = 'Einheit' && DETAIL_ZUORDNUNG_ID IN (SELECT EINHEIT_ID FROM `EINHEIT` WHERE `EINHEIT_AKTUELL` = '1')";
            $result = DB::select($db_abfrage);
            return $result;
        }
    }

    function einladungen($einheit_id)
    {
        $d = new detail ();
        $zimmer = $this->br2n(ltrim(rtrim($d->finde_detail_inhalt('Einheit', $einheit_id, 'Zimmeranzahl'))));
        if (!$zimmer) {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\ErrorMessage('Angaben zur Zimmeranzahl fehlen.')
            );
        }
        $int_arr = $this->interessenten_tab_arr();
        $anz = count($int_arr);
        $nz = 0;
        for ($a = 0; $a < $anz; $a++) {
            $zimmer_i = $int_arr [$a] ['ZIMMER'];
            if (nummer_komma2punkt($zimmer) == nummer_komma2punkt($zimmer_i) or nummer_komma2punkt($zimmer) + 0.5 == nummer_komma2punkt($zimmer_i) or nummer_komma2punkt($zimmer) - 0.5 == nummer_komma2punkt($zimmer_i)) {
                $name = $int_arr [$a] ['NAME'];
                $email = $int_arr [$a] ['EMAIL'];
                $tel = $int_arr [$a] ['TEL'];
                $einzug = $int_arr [$a] ['W_EINZUG'];
                $anschrift = $int_arr [$a] ['ANSCHRIFT'];
                $hinweis = $int_arr [$a] ['HINWEIS'];
                if (!empty ($email)) {
                    $emails_arr [] = $email;
                }

                if (empty ($email) && !empty ($tel)) {
                    $tel_arr [$nz] ['NAME'] = $name;
                    $tel_arr [$nz] ['TEL'] = $tel;
                    $tel_arr [$nz] ['ZIMMER'] = $zimmer_i;
                    $tel_arr [$nz] ['ANSCHRIFT'] = $anschrift;
                    $tel_arr [$nz] ['W_EINZUG'] = $einzug;
                    $tel_arr [$nz] ['HINWEIS'] = $hinweis;
                    $tel_arr [$nz] ['EMAIL'] = '';
                    $nz++;
                }
            }
        }
        // echo '<pre>';

        if (isset ($tel_arr)) {
            // print_r($tel_arr);
            // echo "<h2>Folgende Interessenten sollen per Telefon benachrichtigt werden:</h2>";
            $this->interessentenliste(1, $tel_arr);
            // $this->pdf_interessentenliste($tel_arr);
        }

        if (isset ($emails_arr)) {
            // print_r($emails_arr);
            $this->send_mails_pdf($einheit_id, $emails_arr);
        }
    }

    function interessentenliste($aktiv = 1, $tab_arr = '')
    {
        $f = new formular ();
        $f->fieldset('Interessenten Telefonliste', 'ia');
        if (empty ($tab_arr)) {
            echo "<a href='" . route('web::leerstand::legacy', ['option' => 'pdf_interessenten']) . "'>Interessenten PDF</a>&nbsp;";
            $tab_arr = $this->interessenten_tab_arr();
        }
        if (!empty($tab_arr)) {
            $anz = count($tab_arr);

            echo "<table class=\"sortable\">";
            echo "<tr><th>NAME</th><th>ANSCHRIFT</th><th>TEL</th><th>EMAIL</th><th>ZIMMER</th><th>WUNSCH</th><th>HINWEIS</th></tr>";
            for ($a = 0; $a < $anz; $a++) {
                $id = $tab_arr [$a] ['ID'];
                $name = $tab_arr [$a] ['NAME'];
                $link_edit = "<a href='" . route('web::leerstand::legacy', ['option' => 'interessenten_edit', 'id' => $id]) . "'>$name</a>";
                $ans = $tab_arr [$a] ['ANSCHRIFT'];
                $email = $tab_arr [$a] ['EMAIL'];
                $tel = $tab_arr [$a] ['TEL'];
                $zimmer = $tab_arr [$a] ['ZIMMER'];
                $einzug = $tab_arr [$a] ['W_EINZUG'];
                $hinweis = $tab_arr [$a] ['HINWEIS'];
                echo "<tr><td>$link_edit</td><td>$ans</td><td>$tel</td><td>$email</td><td>$zimmer</td><td>$einzug</td><td>$hinweis</td></tr>";
            }
            echo "</table>";
        } else {
            echo 'Keine Interessenten';
        }
        $f->fieldset_ende();
    }

    function send_mails_pdf($einheit_id, $arr)
    {
        $f = new formular ();
        $f->erstelle_formular('Einladungen per Email senden', '');
        $anz = count($arr);
        // echo "PDF-Expose wurde an folgende Emailadressen gesendet:<hr>";
        for ($a = 0; $a < $anz; $a++) {
            $email = $arr [$a];
            echo "$email<br>";
            $f->hidden_feld('emails[]', $email);
        }
        // print_r($arr);
        $f->hidden_feld('einheit_id', $einheit_id);
        $f->hidden_feld('option', 'sendpdfs');
        echo "<hr>";
        $f->send_button('btn_mail', 'Emails senden');
        $f->ende_formular();
    }

    function form_exposedaten($einheit_id)
    {
        $e = new einheit ();
        $e->get_einheit_info($einheit_id);
        $ma = new mietanpassung ();
        $ms_feld = $ma->get_ms_feld($einheit_id);
        $ms_jahr = $ma->get_ms_jahr();
        $ma->get_spiegel_werte($ms_jahr, $ms_feld);
        $miete_nach_ms = nummer_punkt2komma($e->einheit_qm * $ma->m_wert);
        $miete_nach_ms_max = nummer_punkt2komma($e->einheit_qm * $ma->o_wert);

        $d = new detail ();
        $f = new formular ();
        $f->erstelle_formular("Exposeeinstellungen für $e->einheit_kurzname vornehmen", '');
        fehlermeldung_ausgeben("Ausstattungsklasse $ma->ausstattungsklasse");
        $f->hidden_feld('einheit_id', $einheit_id);
        $zimmer = $this->br2n(ltrim(rtrim($d->finde_detail_inhalt('Einheit', $einheit_id, 'Zimmeranzahl'))));
        $f->text_feld('Zimmeranzahl', 'zimmer', $zimmer, 4, 'zimmer', '');
        $balkon = ltrim(rtrim($d->finde_detail_inhalt('Einheit', $einheit_id, 'Balkon')));
        // $f->text_feld('Balkon vorhanden (ja/nein)', 'balkon', $balkon, 10, 'balkon', '');
        if (empty ($balkon)) {
            $balkon = 'nein';
        }
        // $this->dropdown_ja_nein('Balkon vorhanden', 'balkon', 'balkon', $balkon);
        $d->dropdown_optionen('Balkon', 'balkon', 'balkon', 'Balkon', $balkon);
        /* Miete */
        $expose_km = $this->br2n(ltrim(rtrim($d->finde_detail_inhalt('Einheit', $einheit_id, 'Expose kaltmiete'))));
        $expose_bk = $this->br2n(ltrim(rtrim($d->finde_detail_inhalt('Einheit', $einheit_id, 'Expose BK'))));
        $expose_hk = $this->br2n(ltrim(rtrim($d->finde_detail_inhalt('Einheit', $einheit_id, 'Expose HK'))));

        $f->text_feld("Miete kalt | MSM:$miete_nach_ms € | MAX:$miete_nach_ms_max € | MS-FELD:$ms_feld, U:$ma->u_wert, M:$ma->m_wert, O:$ma->o_wert", 'expose_km', $expose_km, 8, 'expose_km', '');
        $f->text_feld('BK', 'expose_bk', $expose_bk, 8, 'expose_bk', '');

        $heizungsart = $this->br2n(ltrim(rtrim($d->finde_detail_inhalt('Einheit', $einheit_id, 'Heizungsart'))));
        $d->dropdown_optionen('Heizungsart', 'heizungsart', 'heizungsart', 'Heizungsart', $heizungsart);

        if (empty ($expose_hk)) {
            $expose_hk = '0,00';
        }
        $f->text_feld('HK', 'expose_hk', $expose_hk, 10, 'expose_hk', '');

        $f->hidden_feld('zustand', '');

        $expose_frei = $this->br2n(ltrim(rtrim($d->finde_detail_inhalt('Einheit', $einheit_id, 'Expose frei ab'))));
        $f->datum_feld('Bezugsfrei ab', 'expose_frei', $expose_frei, 'expose_frei', '');

        $termin = $this->br2n(ltrim(rtrim($d->finde_detail_inhalt('Einheit', $einheit_id, 'Besichtigungstermin'))));
        $f->datum_feld('Besichtigungsdatum', 'besichtigungsdatum', $termin, 'besichtigungsdatum', '');
        $termin_uhrzeit = $this->br2n(ltrim(rtrim($d->finde_detail_inhalt('Einheit', $einheit_id, 'Expose Besichtigungsuhrzeit'))));
        $f->text_bereich('Uhrzeit und Treffpunkt', 'uhrzeit', $termin_uhrzeit, 20, 5, 'uhrzeit');
        $f->hidden_feld('option', 'expose_speichern');
        $f->send_button('btn_snd', 'Speichern');
        $f->ende_formular();
        $f->fieldset('Fotos', 'fotos');
        for ($a = 1; $a < 9; $a++) {
            if (Storage::disk('fotos')->exists("EINHEIT/$e->einheit_kurzname/expose" . $a . ".jpg")) {
                $filename = Storage::disk('fotos')->url("EINHEIT/$e->einheit_kurzname/expose" . $a . ".jpg");
                echo "<img src='$filename' width='200'>";
            }
        }
        $f->fieldset_ende();
    }

    function expose_aktualisieren($einheit_id, $zimmer, $balkon, $expose_bk, $expose_km, $expose_hk, $heizungsart, $expose_frei, $besichtigungsdatum, $uhrzeit)
    {
        // echo "$einheit_id, $zimmer, $balkon, $expose_bk, $expose_km, $heizungsart, $expose_frei, $besichtigungsdatum, $uhrzeit";
        $d = new detail ();
        $d->detail_aktualisieren('Einheit', $einheit_id, 'Zimmeranzahl', $zimmer, '');
        $d->detail_aktualisieren('Einheit', $einheit_id, 'Balkon', $balkon, '');
        $d->detail_aktualisieren('Einheit', $einheit_id, 'Heizungsart', $heizungsart, '');
        $d->detail_aktualisieren('Einheit', $einheit_id, 'Besichtigungstermin', $besichtigungsdatum, '');
        $d->detail_aktualisieren('Einheit', $einheit_id, 'Expose Besichtigungsuhrzeit', $uhrzeit, '');
        $d->detail_aktualisieren('Einheit', $einheit_id, 'Expose BK', $expose_bk, '');
        $d->detail_aktualisieren('Einheit', $einheit_id, 'Expose HK', $expose_hk, '');
        $d->detail_aktualisieren('Einheit', $einheit_id, 'Expose frei ab', $expose_frei, '');
        $d->detail_aktualisieren('Einheit', $einheit_id, 'Expose Kaltmiete', $expose_km, '');
        weiterleiten(route('web::leerstand::legacy', ['option' => 'expose_pdf', 'einheit_id' => $einheit_id], false));
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

    function form_edit_interessent($id)
    {
        $f = new formular ();
        $f->erstelle_formular('Daten ändern', '');
        $this->get_interessenten_infos($id);
        $f->text_feld('Name', 'name', $this->name, 50, 'name', '');
        $f->text_feld('Anschrift', 'anschrift', $this->anschrift, 50, 'anschrift', '');
        $f->text_feld('Telefon', 'tel', $this->tel, 20, 'tel', '');
        $f->text_feld('Email', 'email', $this->email, 20, 'email', '');
        $f->text_feld('zimmer', 'zimmer', $this->zimmer, 8, 'zimmer', '');
        $f->datum_feld('Wunscheinzug', 'einzug', $this->einzug_d, 'einzug');
        $f->text_bereich('Hinweis', 'hinweis', $this->hinweis, 20, 10, 'hinweis');
        $f->check_box_js('delete', $id, 'Interessenten löschen', '', '');
        $f->hidden_feld('option', 'interessenten_update');
        $f->hidden_feld('id', $id);
        $f->send_button('btn_snd', 'Änderungen vornehmen');
        $f->ende_formular();
    }

    function get_interessenten_infos($id)
    {
        $db_abfrage = "SELECT * FROM `LEERSTAND_INTERESSENT` WHERE `ID` ='$id' AND `AKTUELL` = '1'";
        $result = DB::select($db_abfrage);
        $row = $result[0];
        $this->name = $row ['NAME'];
        $this->email = $row ['EMAIL'];
        $this->anschrift = $row ['ANSCHRIFT'];
        $this->zimmer = $row ['ZIMMER'];
        $this->tel = $row ['TEL'];
        $this->einzug = $row ['EINZUG'];
        $this->einzug_d = date_mysql2german($this->einzug);
        $this->hinweis = $row ['HINWEIS'];
    }

    function interessenten_deaktivieren($id)
    {
        $db_abfrage = "UPDATE `LEERSTAND_INTERESSENT` SET AKTUELL='0' WHERE `ID` ='$id'";
        DB::update($db_abfrage);
        return true;
    }

    function interessenten_updaten($id, $name, $anschrift, $tel, $email, $einzug, $zimmer, $hinweis)
    {
        $db_abfrage = "UPDATE `LEERSTAND_INTERESSENT` SET NAME='$name', ANSCHRIFT='$anschrift', TEL='$tel', EMAIL='$email', EINZUG='$einzug', ZIMMER='$zimmer', HINWEIS='$hinweis' WHERE `ID` ='$id'";
        DB::update($db_abfrage);
        return true;
    }

    function form_foto_upload($einheit_id)
    {
        echo '<form name="upload_form" method="post" enctype="multipart/form-data" action="">';
        echo '<table>';
        echo '<tr><td>Großfoto<input type="file" name="expose[]"></td></tr>';
        echo '<tr><td>1. Kleinfoto<input type="file" name="expose[]"></td></tr>';
        echo '<tr><td>2. Kleinfoto<input type="file" name="expose[]"></td></tr>';
        echo '<tr><td>3. Kleinfoto<input type="file" name="expose[]"></td></tr>';
        echo '<tr><td>4. Kleinfoto<input type="file" name="expose[]"></td></tr>';
        echo '<tr><td>5. Kleinfoto<input type="file" name="expose[]"></td></tr>';
        echo '<tr><td>6. Kleinfoto<input type="file" name="expose[]"></td></tr>';
        echo '<tr><td>Plan/Skizze<input type="file" name="expose[]"></td></tr>';
        echo '<tr><td><input name="btn_sbm" type="submit" value="Hochladen">';
        echo '</td></tr>';
        echo '</table>';
        echo '<input type="hidden" name="option" value="expose_foto_upload_check">';
        echo "<input type=\"hidden\" name=\"einheit_id\" value=\"$einheit_id\">";
        echo '</form>';
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
            echo "<thead><tr><th>EINHEITEN BIS $datum_d</th><th>AUSSTATTUNG</th><th>SANIER-<br>VERLAUF</th><th>JAHR DER<br>LETZTEN<br>SANIERUNG</th><th>ENERGIE<br>AUSWEIS</th><th>ENERGIE<br>AUSWEIS<br>BIS</th><th>REINIGEN<br>FOTOS</th></tr></thead>";
            $anz_e = count($arr);
            for ($a = 0; $a < $anz_e; $a++) {
                $einheit_id = $arr [$a] ['EINHEIT_ID'];
                $haus_id = $arr [$a] ['HAUS_ID'];
                $einheit_kurzname = $arr [$a] ['EINHEIT_KURZNAME'];
                /* FOTO ORDNER ANLEGEN */
                if (!Storage::disk('fotos')->exists("EINHEIT/$einheit_kurzname/ANZEIGE")) {
                    Storage::disk('fotos')->makeDirectory("EINHEIT/$einheit_kurzname/ANZEIGE");
                }

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

                $dir = Storage::disk('fotos')->fullPath("EINHEIT/$einheit_kurzname/ANZEIGE");
                $fotos_arr = scandir($dir);
                $anz_fotos = count($fotos_arr);
                $anz_fotos_ok = $anz_fotos - 2;

                $fotos_vorhanden = $anz_fotos_ok > 0 ? "JA" : "NEIN";

                echo "<div class='input-field'>
                            <input disabled value='" . $fotos_vorhanden . "' id='link_foto_" . $objekt_id . '_' . $a . "' type='text'>
                            <label class='active' for='link_foto_" . $objekt_id . '_' . $a . "'>Fotos vorhanden</label>
                      </div>";
                $link_foto_upload = "<a class='waves-effect waves-light btn' href='" . route('web::leerstand::legacy', ['option' => 'fotos_upload', 'einheit_id' => $einheit_id]) . "'><i class=\"mdi mdi-upload left\"></i>Hochladen</a>";
                echo $link_foto_upload;

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
        // $column_names = array('LEER VM', 'LEER NEU', 'IST WM','DIFF');
        $plot->SetShading(10);
        $plot->SetLegendReverse(True);
        // $plot->SetLegend($column_names);

        $oo = new objekt ();
        $oo->get_objekt_infos($objekt_id);
        $anz_einheiten_alle = $oo->anzahl_einheiten_objekt($objekt_id);

        $datum_heute = "$jahr-$monat-01";
        $mi = new miete ();
        $datum_vormonat = $mi->tage_minus($datum_heute, 30);

        $arr = $this->leerstand_finden_monat($objekt_id, $datum_vormonat);
        $anz_leer_vormonat = count($arr);
        // unset($arr);

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
        // print_r($vermietete);
        if ($anz__V > 0) {
            for ($ee = 0; $ee < $anz__V; $ee++) {
                $vermietet_akt_string .= $vermietete [$ee] . "\n";
            }
        }

        // unset($arr);

        /*
		 * $mvs = new mietvertraege;
		 * $anz_ausgezogene = $mvs->anzahl_ausgezogene_mieter($objekt_id, $jahr, $monat);
		 * $anz_eingezogene = $mvs->anzahl_eingezogene_mieter($objekt_id, $jahr, $monat);
		 */
        $bilanz_akt = $anz__V - $anz__L;

        // 0-1 = -1;

        $z = 0;
        /*
		 * $data[$z][] = "ALLE\nAKTUELL";
		 * $data[$z][] = $anz_einheiten_alle;
		 *
		 * $data[$z][] = 0;
		 * $data[$z][] = 0;
		 *
		 */
        // $z++;
        /*
		 * $data[$z][] = "LEER\nVERM.";
		 * $data[$z][] = 0;
		 * $data[$z][] = $anz_vermietet;
		 * $data[$z][] = $anz_leer_akt;
		 */

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

        // $z++;

        $plot->SetYDataLabelPos('plotstack');

        $plot->SetDataValues($data);

        // Main plot title:
        $plot->SetTitle("$oo->objekt_kurzname $monat/$jahr");

        // No 3-D shading of the bars:
        $plot->SetShading(0);

        // Make a legend for the 3 data sets plotted:
        // $plot->SetLegend(array('Mieteinnahmen', 'Leerstand'));

        // $plot->SetLegend(array('MIETE'));

        // Turn off X tick labels and ticks because they don't apply here:
        $plot->SetXTickLabelPos('none');
        $plot->SetXTickPos('none');

        // Draw it
        $plot->SetIsInline(true);
        $plot->DrawGraph();

        // echo "<hr>$plot->img ";
        // $plot->PrintImageFrame();
        // $ima = $plot->PrintImage();
        $ima = $plot->EncodeImage();
        // ob_clean();
        return $ima;

        // echo "<img src=\"$ima\"></img>";
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
            /*
			 * echo '<pre><hr>';
			 * print_r($new_arr);
			 * echo "<hr>";
			 */
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
        // echo '<pre>';

        $anz = count($arr);
        if ($anz > 0) {

            /* Filterwahl generieren */
            if (session()->has('filter')) {
                session()->forget('filter');
            }

            //session()->push('filter.zimmer', []);
            //session()->push('filter.balkon', []);
            //session()->push('filter.heizung', []);

            for ($a = 0; $a < $anz; $a++) {
                $zimmer = $arr [$a] ['ZIMMER'];
                $balkon = $arr [$a] ['BALKON'];
                $heizungsart = $arr [$a] ['HEIZUNGSART'];
                // echo "$zimmer $balkon $heizungsart";

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
                    // $name, $id, $wert, $label, $js, $checked
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
                    // $name, $id, $wert, $label, $js, $checked
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
            echo "<tr><th>EINHEIT</th><th>TYP</th><th>ANSCHRIFT</th><th>LAGE</th><th>ZI-<br>MM.</th><th>QM</th><th>BAL<br>KON</th><th>HEI-<br>ZUNG</th><th>LETZE\nSAN-<br>IERUNG</th><th>FERTIG</th><th>REIN-<br>IGUNG</th><th>BK<br>SCHN.</th><th>BK</th><th>HK<br>SCHN.</th><th>HK</th><th>KALT<br>m²</th><th>BRU-<br>TTO</th><th>TER-<br>MIN</th></tr>";
            for ($a = 0; $a < $anz; $a++) {
                $einheit_id = $arr [$a] ['EINHEIT_ID'];
                $ma = new mietanpassung ();
                $ms_feld = $ma->get_ms_feld($einheit_id);
                $ms_jahr = $ma->get_ms_jahr();
                $ma->get_spiegel_werte($ms_jahr, $ms_feld);
                $ms_20proz = nummer_komma2punkt(nummer_punkt2komma($ma->o_wert * 1.2));

                $einheit_kn = $arr [$a] ['EINHEIT_KURZNAME'];
                $link_einheit = "<a class=\"einheit\" href='" . route('web::uebersicht::legacy', ['anzeigen' => 'einheit', 'einheit_id' => $einheit_id]) . "'>$einheit_kn</a>";
                $link_expose_pdf = "<a href='" . route('web::leerstand::legacy', ['option' => 'expose_pdf', 'einheit_id' => $einheit_id]) . "'><img src=\"images/pdf_dark.png\">EXPOSE</a>";
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
                $anz_fotos = $arr [$a] ['FOTO_ANZ'];

                /* Besichtigungstermin für Vermietung aus Details */
                $b_termin = $arr [$a] ['B_TERMIN'];
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
                    $link_termin = "<a class=\"details\" onclick=\"change_detail('Besichtigungstermin', '$b_termin', '$b_termin_dat', 'Einheit', '$einheit_id')\">$b_termin</a>";
                    $link_fotos = "<a href='" . route('web::leerstand::legacy', ['option' => 'fotos_upload', 'einheit_id' => $einheit_id]) . "'>Fotos: $anz_fotos</a>";
                    $link_expose_text = "<a href='" . route('web::details::legacy', ['option' => 'details_hinzu', 'detail_tabelle' => 'Einheit', 'detail_id' => $einheit_id, 'vorauswahl' => 'Exposetext']) . "'>Exposetext</a>";

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
                    echo "<td>$link_einheit<br>Ex:$l_mieter<br>$link_fotos<hr>$link_expose_pdf<hr>$link_expose_text<hr>$link_reservierung</td><td>$typ</td><td>$str</td><td>$einheit_lage</td><td sorttable_customkey=\"$zimmer_p\">$zimmer</td><td>$einheit_qm_a</td><td>$balkon</td><td>$heizungsart</td><td>$jahr_s</td><td>$fertig_bau_bem</td><td>$gereinigt<hr>$gereinigt_bem</td><td>$nk</td><td>$link_bk</td><td>$hk_s</td><td>$link_hk</td><td><b>$link_kaltmiete<hr>m²-Kalt:$kalt_qm<br>(MAX20:$netto_miete_20)</b><hr>MSM-$ms_feld:$ma->m_wert<br>MSO-$ms_feld:$ma->o_wert<br>MSO20%:$ms_20proz<hr>$kaltmiete_bem</td><td><b>$brutto_miete</b></td><td>$link_termin</td></tr>";
                }
                // echo "$einheit_kn - $l_mieter ($typ) $str $einheit_lage Zimmer: $zimmer Balkon:$balkon Heizart:$heizungsart EA: $energieausweis JS:$jahr_s BAU:$fertig_bau ($fertig_bau_bem) REIN:$gereinigt ($gereinigt_bem) $nk € $hk €<br>";
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
                // print_r($datum_arr);
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

                $arr [$a] ['FOTO_PATH'] = Storage::disk('fotos')->fullPath("EINHEIT/$einheit_kurzname/ANZEIGE");
                $arr [$a] ['FOTO_LINKS'] = Storage::disk('fotos')->files("EINHEIT/$einheit_kurzname/ANZEIGE");
                // echo '<pre>';
                $anz_fotos = count($arr [$a] ['FOTO_LINKS']);
                $arr [$a] ['FOTO_ANZ'] = $anz_fotos;
                /* wenn keine Fotos, Fotoarray leeren */
                if ($anz_fotos < 1) {
                    $arr [$a] ['FOTO_LINKS'] = null;
                }

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
                $arr_details = $d->finde_detail_inhalt_last_arr('Einheit', $einheit_id, 'Besichtigungstermin');
                if (!empty($arr_details)) {
                    $arr [$a] ['B_TERMIN'] = $arr_details [0] ['DETAIL_INHALT'];
                    $arr [$a] ['B_TERMIN_DAT'] = $arr_details [0] ['DETAIL_DAT'];
                    $arr [$a] ['B_TERMIN_BEM'] = $arr_details [0] ['DETAIL_BEMERKUNG'];
                } else {
                    $arr [$a] ['B_TERMIN'] = '------';
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

        // echo '<pre>';
        // print_r($n_arr);
        // print_r($arr);
        return $n_arr;
    }

    function get_durchschnitt_nk($objekt_id, $art)
    {
        $monat = date("m");
        $jahr = date("Y");
        // echo '<pre>';

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
                // echo "$einheit_kn vermietet<br>";
                $e->get_last_mietvertrag_id($einheit_id);
                $mv_id = $e->mietvertrag_id;
                $me = new mietentwicklung ();

                $me_arr = $me->get_kostenkat_info_aktuell($mv_id, $monat, $jahr, $art);
                if (is_array($me_arr)) {
                    if ($me_arr ['BETRAG'] > 0) {
                        $anz_einheiten++;
                        $summe_g += $me_arr ['BETRAG'];
                        $summe_qm += $einheit_qm;
                        // print_r($me_arr);
                    }
                }
            }
        }
        if ($summe_qm > 0) {
            // echo "$summe_g/$summe_qm";
            return nummer_komma2punkt(nummer_punkt2komma($summe_g / $summe_qm));
        } else {
            return '0.00';
        }
    }

    function form_fotos_upload($einheit_id)
    {
        echo '<style>
		    #gallery .thumbnail{
                width:200px;
                height: 150px;
                float:left;
                margin:2px;
            }
            #gallery .thumbnail img{
                width:200px;
                height: 150px;
            }
        </style>';

        $e = new einheit ();
        $e->get_einheit_info($einheit_id);
        $f = new formular ();
        $f->fieldset("FotoUpload $e->einheit_kurzname", 'fs');
        $f->hidden_feld("einheit_id_foto", $einheit_id);
        //echo "<input type=\"file\" id=\"fileinput\" multiple=\"multiple\" accept=\"image/*\" />";
        echo "<div class='row'>";
        echo "<div class=\"file-field input-field col-xs-12 col-md-9 col-lg-9\">
                <div class=\"btn\">
                <span>Fotos</span>
                    <input type=\"file\" id=\"fileinput\" accept=\"image/*\" multiple>
                </div>
                <div class=\"file-path-wrapper\">
                    <input class=\"file-path validate\" type=\"text\" placeholder=\"Ein Foto oder mehrere Fotos hochladen\">
                </div>
            </div>";
        echo "<div class='input-field col-xs-12 col-md-5 col-lg-3'><a class='waves-effect waves-light btn' id='BTN_UPLOAD' onclick='upload_files()'><i class='mdi mdi-upload left'></i>Hochladen</a></div>";
        echo "</div>";
        echo "<div id=\"gallery\" class='row input-field'></div>";
        $f->fieldset_ende();
    }

    function fotos_anzeigen_wohnung($einheit_id, $unterordner = 'ANZEIGE', $anz_pro_zeile = '6')
    {
        $e = new einheit ();
        $e->get_einheit_info($einheit_id);
        $f = new formular ();
        // $f->fieldset("Vorhandene Fotos $e->einheit_kurzname", 'fs');
        $storage = Storage::disk('fotos');
        $fotos_arr = $storage->files("EINHEIT/$e->einheit_kurzname/$unterordner/");

        $anz_fotos = count($fotos_arr);
        $f->fieldset("Vorhandene Fotos $e->einheit_kurzname ($anz_fotos)", 'fs');
        echo "<div class='row'>";
        $counter = 0;
        for ($a = 0; $a < $anz_fotos; $a++) {
            $counter++;
            $url = $storage->url($fotos_arr[$a]);
            $path = $storage->fullPath($fotos_arr[$a]);
            echo "<div class='col-xs-12 col-md-6 col-lg-4'>";
            echo "<img class='materialboxed' width='250' height='188' src='$url' alt='Wohnungsbild $a'>";
            $url = asset('images/x.png');
            echo "<img onclick=\"del_file('$path');reload_me();\" src='$url'></div>\n";
        }
        echo "</div>";
        $f->fieldset_ende();
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