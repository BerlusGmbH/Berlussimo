<?php
/* PDF KLASSE */

class b_pdf
{
    public $zahlungshinweis;
    public $aktuelle_g_miete;
    public $aktuelle_g_miete_arr;
    public $v_kurztext;
    public $v_text;
    public $header_zeile;
    public $zeile1;
    public $zeile2;
    public $footer_typ;
    public $footer_typ_id;
    public $zahlungshinweis_org;
    public $footer_partner;
    public $v_kat;
    public $v_empf_typ;

    function erstelle_brief_vorlage($v_dat, $empf_typ, $empf_id_arr, $option = '0')
    {
        $anz_empf = count($empf_id_arr);
        if ($anz_empf > 0) {

            $pdf = new Cezpdf ('a4', 'portrait');
            $bpdf = new b_pdf ();
            $bpdf->b_header($pdf, 'Partner', session()->get('partner_id'), 'portrait', 'Helvetica.afm', 6);
            $pdf->ezStopPageNumbers(); // seitennummerierung beenden

            for ($index = 0; $index < sizeof($empf_id_arr); $index++) {

                $mv_id = $empf_id_arr [$index];
                $mv = new mietvertraege ();
                unset ($mv->postanschrift);
                $mv->get_mietvertrag_infos_aktuell($mv_id);
                $jahr = date("Y");
                $monat = date("m");
                $mkk = new mietkonto ();
                $this->aktuelle_g_miete = 0.00;
                $this->aktuelle_g_miete_arr = explode('|', $mkk->summe_forderung_monatlich($mv_id, $monat, $jahr));
                $this->aktuelle_g_miete = nummer_punkt2komma($this->aktuelle_g_miete_arr [0]);

                $dets = new detail ();
                $mv_sepa = new sepa (); // SEPA LS Infos auf leer stellen
                // Infos nur von LS-teilnehmern
                if ($dets->finde_detail_inhalt('MIETVERTRAG', $mv_id, 'Einzugsermächtigung') == 'JA') {
                    $mv->ls_konto = $dets->finde_detail_inhalt('MIETVERTRAG', $mv_id, 'Kontonummer-AutoEinzug');
                    $mv->ls_blz = $dets->finde_detail_inhalt('MIETVERTRAG', $mv_id, 'BLZ-AutoEinzug');
                    $mv_sepa->get_iban_bic($mv->ls_konto, $mv->ls_blz);
                }

                $gk = new geldkonto_info ();
                $gk->geld_konto_ermitteln('Objekt', $mv->objekt_id);

                $o = new objekt ();
                $o->get_objekt_infos($mv->objekt_id);
                /* SEPA ERMITLUNG */
                $sepa = new sepa ();
                $sepa->get_iban_bic($gk->kontonummer, $gk->blz);
                $dets = new detail ();
                if (isset ($sepa->GLAEUBIGER_ID)) {
                    unset ($sepa->GLAEUBIGER_ID);
                }
                $sepa->GLAEUBIGER_ID = $dets->finde_detail_inhalt('GELD_KONTEN', $gk->geldkonto_id, 'GLAEUBIGER_ID');
                if (!isset ($sepa->GLAEUBIGER_ID)) {
                    throw new \App\Exceptions\MessageException(
                        new \App\Messages\ErrorMessage("Bei $gk->kontonummer $mv->objekt_kurzname fehlt die Gläubiger ID")
                    );
                }
                $this->get_texte($v_dat);

                // ##############################################################
                /* Normale Mieter ohne Verzug und Zustell */
                $add = 0;
                $pa_arr = array();
                if (count($mv->postanschrift) < 1) {
                    $pa_arr [$add] ['anschrift'] = "$mv->personen_name_string_u\n$mv->haus_strasse $mv->haus_nr\n<b>$mv->haus_plz $mv->haus_stadt</b>";
                    $pa_arr [$add] ['mv_id'] = $mv_id;
                    $add++;
                }
                /* Mieter mit Verzug oder Zustell */
                if (count($mv->postanschrift) == 1) {
                    $key_arr = array_keys($mv->postanschrift);
                    $key = $key_arr [0];
                    $pa = $mv->postanschrift [$key] ['adresse'];

                    $pa_arr [$add] ['anschrift'] = $pa;
                    $pa_arr [$add] ['mv_id'] = $mv_id;
                    $add++;
                }

                if (count($mv->postanschrift) > 1) {
                    $anz_ad = count($mv->postanschrift);
                    for ($pp = 0; $pp < $anz_ad; $pp++) {
                        $pa_arr [$add] ['anschrift'] = $mv->postanschrift [$pp] ['adresse'];
                        $pa_arr [$add] ['mv_id'] = $mv_id;
                        $add++;
                    }
                }

                $anz_ppa = count($pa_arr);
                for ($br = 0; $br < $anz_ppa; $br++) {

                    /* Kopf */

                    $pdf_einzeln = new Cezpdf ('a4', 'portrait');
                    $bpdf->b_header($pdf_einzeln, 'Partner', session()->get('partner_id'), 'portrait', 'Helvetica.afm', 6);
                    $pdf_einzeln->ezStopPageNumbers(); // seitennummerirung beenden

                    /* Faltlinie */
                    $pdf->setLineStyle(0.2);
                    $pdf_einzeln->setLineStyle(0.2);
                    $pdf->line(5, 542, 20, 542);
                    $pdf_einzeln->line(5, 542, 20, 542);

                    if (count($mv->postanschrift) < 1) {
                        // $pdf->addText(260,590,6,"$mv->einheit_lage",0);
                        // $pdf_einzeln->addText(260,590,6,$mv->einheit_lage,0);
                        // $pdf->ezText("$mv->einheit_lage",9);
                        // $pdf_einzeln->ezText("$mv->einheit_lage",9);
                    }

                    $pa_1 = $pa_arr [$br] ['anschrift'];
                    $mv_id_1 = $pa_arr [$br] ['mv_id'];
                    $mv->get_mietvertrag_infos_aktuell($mv_id_1);

                    $pdf->addText(250, $pdf->y, 6, "$mv->einheit_lage", 0);
                    $pdf_einzeln->addText(250, $pdf->y, 6, $mv->einheit_lage, 0);

                    $pdf->ezText("$pa_1", 10);
                    $pdf_einzeln->ezText("$pa_1", 10);

                    // ##############################################################
                    $pdf->ezSetDy(-80);
                    $pdf_einzeln->ezSetDy(-80);
                    if (!request()->has('datum')) {
                        $datum_heute = date("d.m.Y");
                    } else {
                        $datum_heute = request()->input('datum');
                    }
                    $p = new partners ();
                    $p->get_partner_info(session()->get('partner_id'));

                    $pdf->ezText("$p->partner_ort, $datum_heute", 9, array(
                        'justification' => 'right'
                    ));
                    $pdf->ezText("<b>Objekt: $mv->haus_strasse $mv->haus_nr, $mv->haus_plz $mv->haus_stadt</b>", 9);
                    if (!isset ($mv->postanschrift)) {
                        $pdf->ezText("<b>Einheit: $mv->einheit_kurzname</b>", 9);
                    } else {
                        $pdf->ezText("<b>Einheit: $mv->einheit_kurzname (Mieter: $mv->personen_name_string)</b>", 9);
                    }
                    $pdf->ezText("<b>$this->v_kurztext</b>", 9);
                    $pdf->ezSetDy(-30);
                    $pdf->ezText("$mv->mv_anrede", 9);
                    eval ("\$this->v_text = \"$this->v_text\";"); // Variable ausm Text füllen

                    $pdf->ezText("$this->v_text", 9);

                    $pdf_einzeln->ezText("$p->partner_ort, $datum_heute", 11, array(
                        'justification' => 'right'
                    ));
                    $pdf_einzeln->ezText("<b>Objekt: $mv->haus_strasse $mv->haus_nr, $mv->haus_plz $mv->haus_stadt</b>", 12);
                    $pdf_einzeln->ezText("<b>Einheit: $mv->einheit_kurzname</b>", 11);
                    $pdf_einzeln->ezText("<b>$this->v_kurztext</b>", 11);
                    $pdf_einzeln->ezSetDy(-30);
                    $pdf_einzeln->ezText("$mv->mv_anrede", 11);
                    $pdf_einzeln->ezText("$this->v_text", 11, array(
                        'justification' => 'full'
                    ));
                    $path = Storage::disk('serienbriefe')->fullPath(explode('@',Auth::user()->email)[0]);
                    $this->pdf_speichern($path, "$mv->einheit_kurzname - $this->v_kurztext vom $datum_heute" . '.pdf', $pdf_einzeln->output());

                    if ($index < sizeof($empf_id_arr) - 1) {
                        $pdf->ezNewPage();
                        $pdf_einzeln->ezNewPage();
                    }
                }
            }

            if (request()->exists('emailsend')) {
                /* erste packen und gz erstellen */
                $storage = Storage::disk('serienbriefe');
                $dir = $storage->basePath();
                $tar_dir_name = "$dir/" . explode('@', Auth::user()->email)[0];

                if (!$storage->exists(explode('@', Auth::user()->email)[0])) {
                    $storage->makeDirectory(explode('@', Auth::user()->email)[0]);
                }

                $tar_file_name = "Serienbrief - $mv->einheit_kurzname - $this->v_kurztext vom $datum_heute.tar.gz";

                exec("cd $tar_dir_name && tar cfvz '$tar_file_name' *.pdf");
                exec("rm $tar_dir_name/*.pdf");
                
                /* das Raus */
                ob_clean(); // ausgabepuffer leeren

                $file = "$tar_dir_name/Serienbrief - $mv->einheit_kurzname - $this->v_kurztext vom $datum_heute.tar.gz";
                if (file_exists($file)) {
                    header('Content-Disposition: attachment; filename="'.basename($file).'"');
                    readfile($file);
                    exec("rm '$tar_dir_name/$tar_file_name'");
                    exit;
                }
            } else { // emalsend
                /* Kein Emailversand angefordert, nur ansehen */
                /* Ausgabe */
                ob_end_clean(); // ausgabepuffer leeren
                $dateiname = "\"$datum_heute - Serie - $this->v_kurztext.pdf\"";
                $pdf_opt ['Content-Disposition'] = $dateiname;
                $pdf->ezStream($pdf_opt);
            }
        } else {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\WarningMessage('Keine Empfänger gewählt')
            );
        }
    }

    function b_header(Cezpdf &$pdf, $partner_typ, $partner_id, $orientation = 'portrait', $font_file, $f_size, $logo_file = '')
    {
        $diff = array(196 => 'Adieresis', 228 => 'adieresis',
            214 => 'Odieresis', 246 => 'odieresis',
            220 => 'Udieresis', 252 => 'udieresis',
            223 => 'germandbls');
        $pdf->selectFont('Helvetica'
            , array('encoding' => 'WinAnsiEncoding'
            , 'differences' => $diff));
        $all = $pdf->openObject();
        $pdf->saveState();
        $pdf->setStrokeColor(0, 0, 0, 1);

        if ($orientation == 'portrait') {
            $pdf->ezSetMargins(135, 70, 50, 50);
            if (!request()->has('no_logo')) {
                $logo_file = "$partner_typ/$partner_id" . "_logo.png";
                if (Storage::disk('logos')->exists($logo_file)) {
                    $pdf->addPngFromFile(Storage::disk('logos')->fullPath($logo_file), 200, 730, 200, 80);
                    $pdf->line(43, 725, 545, 725);
                    $pdf->line(42, 50, 550, 50);
                }
            }
            $pdf->setLineStyle(0.5);
            $this->footer_info($partner_typ, $partner_id);
            $pdf->addText(43, 718, $f_size, "$this->header_zeile");
            $pdf->ezStartPageNumbers(545, 715, $f_size, '', 'Seite {PAGENUM} von {TOTALPAGENUM}', 1);
            $pdf->setLineStyle(0.5);

            if (!request()->has('no_logo')) {
                $pdf->addText($pdf->ez['pageWidth'] / 2, 42, $f_size, "$this->zeile1", 0, 'center');
                $pdf->addText($pdf->ez['pageWidth'] / 2, 35, $f_size, "$this->zeile2", 0, 'center');
            }
        } else {
            $pdf->ezSetMargins(120, 40, 30, 30);
            $logo_file = "$partner_typ/$partner_id" . "_logo.png";
            if (Storage::disk('logos')->exists($logo_file)) {
                $pdf->addPngFromFile(Storage::disk('logos')->fullPath($logo_file), 320, 505, 200, 80);
            } else {
                $pdf->addText(370, 505, $f_size, "Vorschau / Druckansicht ");
            }
            $pdf->setLineStyle(0.5);
            $this->footer_info($partner_typ, $partner_id);
            $pdf->line(43, 500, 785, 500);
            $pdf->addText(43, 493, $f_size, "$this->header_zeile");
            $pdf->ezStartPageNumbers(783, 493, $f_size, '', 'Seite {PAGENUM} von {TOTALPAGENUM}', 1);
            $pdf->setLineStyle(0.5);
            $pdf->line(42, 30, 785, 30);

            $pdf->addText($pdf->ez['pageWidth'] / 2, 23, $f_size, "$this->zeile1", 0, 'center');
            $pdf->addText($pdf->ez['pageWidth'] / 2, 16, $f_size, "$this->zeile2", 0, 'center');
        }
        $pdf->restoreState();
        $pdf->closeObject();
        $pdf->addObject($all, 'all');
    }

    function footer_info($typ, $id)
    {
        $result = DB::select("SELECT * FROM FOOTER_ZEILE WHERE FOOTER_TYP='$typ' && FOOTER_TYP_ID='$id' && AKTUELL='1' ORDER BY  FOOTER_ID ASC LIMIT 0,1");
        $row = $result[0];
        $this->footer_typ = $row ['FOOTER_TYP'];
        $this->footer_typ_id = $row ['FOOTER_TYP_ID'];
        $this->zahlungshinweis = str_replace("<br>", "\n", $row ['ZAHLUNGSHINWEIS']);
        $this->zahlungshinweis_org = $row ['ZAHLUNGSHINWEIS'];
        $this->zeile1 = $row ['ZEILE1'];
        $this->zeile2 = $row ['ZEILE2'];
        $this->header_zeile = $row ['HEADER'];
        $r = new rechnung ();
        $this->footer_partner = $r->kostentraeger_ermitteln($this->footer_typ, $this->footer_typ_id);
    }

    function get_texte($v_dat)
    {
        $result = DB::select("SELECT * FROM PDF_VORLAGEN WHERE DAT='$v_dat'");
        $row = $result[0];
        $this->v_kurztext = $row ['KURZTEXT'];
        $this->v_text = $row ['TEXT'];
        $this->v_kat = $row ['KAT'];
        $this->v_empf_typ = $row ['EMPF_TYP'];
    }

    function pdf_speichern($dir, $dateiname, $pdfcode)
    {
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }
        $fp = fopen($dir . '/' . $dateiname, 'w');
        fwrite($fp, $pdfcode);
        fclose($fp);
    }

    function form_mieter2sess()
    {
        $f = new formular ();
        $f->erstelle_formular("Mieter wählen", NULL);
        $this->mieter_checkboxen();
        echo "<div class='input-field'>";
        $f->send_button("submit", "Hinzufügen");
        echo "</div>";
        $f->ende_formular();
    }

    function mieter_checkboxen()
    {
        $f = new formular ();
        if (request()->has('delete')) {
            session()->forget('serienbrief_mvs');
        }

        if (request()->has('vorlage') && is_array(session()->get('serienbrief_mvs'))) {
            echo "Vorlage wählen";
            if (request()->has('kat')) {
                $this->vorlage_waehlen(null, request()->input('kat'));
            } else {
                $this->vorlage_waehlen();
            }
        }

        if (request()->has('mv_ids') && is_array(request()->input('mv_ids'))) {
            for ($index = 0; $index < sizeof(request()->input('mv_ids')); $index++) {
                $mv_id_add = request()->input('mv_ids')[$index];
                if (is_array(session()->get('serienbrief_mvs'))) {
                    if (!in_array($mv_id_add, session()->get('serienbrief_mvs'))) {
                        session()->push('serienbrief_mvs', $mv_id_add);
                    }
                } else {
                    session()->push('serienbrief_mvs', $mv_id_add);
                }
            }
        }

        if (session()->has('serienbrief_mvs') && is_array(session()->get('serienbrief_mvs'))) {
            echo "<table class=\"sortable striped\"><thead>";
            echo "<tr><th>Einheit</th><th>Mieter</th></tr></thead>";
            for ($a = 0; $a < count(session()->get('serienbrief_mvs')); $a++) {
                $mv = new mietvertraege ();
                $mv_id = session()->get('serienbrief_mvs')[$a];
                $mv->get_mietvertrag_infos_aktuell($mv_id);
                echo "<tr><td>$mv->einheit_kurzname</td><td>$mv->personen_name_string</td></tr>";
            }
            echo "</table>";
            echo "<div class='input-field'>";
            $f->send_button("delete", "Alle Löschen");
            echo "&nbsp;";
            $f->send_button("vorlage", "Vorlage Wählen");
            echo "</div>";
        }

        $f = new formular ();
        $m = new mahnungen ();
        $aktuelle_mvs = $m->finde_aktuelle_mvs();
        if (!empty($aktuelle_mvs)) {
            echo "<div class='row input-field'>";
            echo "<div class='col s12 m6 l2'>";
            $f->check_box_js_alle('nn', 'nn', 'NN', 'Alle markieren', '', '', 'mv_ids');
            echo "</div>";
            for ($index = 0; $index < sizeof($aktuelle_mvs); $index++) {
                $mv_id = $aktuelle_mvs [$index] ['MIETVERTRAG_ID'];
                $mv = new mietvertraege ();
                $mv->get_mietvertrag_infos_aktuell($mv_id);
                if (session()->has('serienbrief_mvs')) {
                    if (!in_array($mv_id, session()->get('serienbrief_mvs'))) {
                        echo "<div class='col s12 m6 l2'>";
                        $f->check_box_js1('mv_ids[]', 'mv_id_' . $mv_id, $mv_id, "$mv->einheit_kurzname - $mv->personen_name_string", '', '');
                        echo "</div>";
                    }
                } else {
                    echo "<div class='col s12 m6 l2'>";
                    $f->check_box_js1('mv_ids[]', 'mv_id_' . $mv_id, $mv_id, "$mv->einheit_kurzname - $mv->personen_name_string", '', '');
                    echo "</div>";
                }
            }
            echo "</div>";
        } else {
            echo "Keine Mieter";
        }
    }

    function vorlage_waehlen($empf_typ = null, $kat = null)
    {
        if ($empf_typ == null && $kat == null) {
            $db_abfrage = "SELECT * FROM PDF_VORLAGEN ORDER BY KURZTEXT ASC";
        }

        if ($empf_typ && $kat == null) {
            $db_abfrage = "SELECT * FROM PDF_VORLAGEN WHERE EMPF_TYP='$empf_typ' ORDER BY KURZTEXT ASC";
        }

        if ($empf_typ == null && $kat) {
            $db_abfrage = "SELECT * FROM PDF_VORLAGEN WHERE KAT='$kat' ORDER BY KURZTEXT ASC";
        }

        if ($empf_typ != null && $kat != null) {
            $db_abfrage = "SELECT * FROM PDF_VORLAGEN WHERE KAT='$kat' && EMPF_TYP='$empf_typ' ORDER BY KURZTEXT ASC";
        }

        $result = DB::select($db_abfrage);
        if (!empty($result)) {
            echo "<table class=\"sortable striped\">\n";
            $link_kat = "<a href='" . route('legacy::bk::index', ['option' => 'serienbrief', 'vorlage' => 1]) . "'>Alle Kats anzeigen</a>";
            echo "<thead>";
            echo "<tr><th>Vorlage / Betreff</th><th>KAT</th><th>BEARBEITEN</th><th>EINZELPDF</th><th>MEHRERE PDFs</th></tr>";
            echo "</thead>";
            echo "<tr><td><b>$empf_typ<b></td><td>$link_kat</td><td></td><td></td><td></td></tr>";
            foreach($result as $row) {
                $dat = $row ['DAT'];
                $kurztext = $row ['KURZTEXT'];
                $kat = $row ['KAT'];
                $link_erstellen = "<a href='" . route('legacy::bk::index', ['option' => 'serienbrief_pdf', 'vorlagen_dat' => $dat, 'emailsend']) . "'>Serienbrief in mehreren PDFs</a>";
                $link_ansehen = "<a href='" . route('legacy::bk::index', ['option' => 'serienbrief_pdf', 'vorlagen_dat' => $dat]) . "'>Serienbrief in einem PDF</a>";
                $link_bearbeiten = "<a href='" . route('legacy::bk::index', ['option' => 'vorlage_bearbeiten', 'vorlagen_dat' => $dat]) . "'>Vorlage bearbeiten</a>";
                $link_kat = "<a href='" . route('legacy::bk::index', ['option' => 'serienbrief', 'kat' => $kat, 'vorlage' => 1]) . "'>$kat</a>";

                echo "<tr><td>$kurztext</td><td>$link_kat</td><td>$link_bearbeiten</td><td>$link_ansehen</td><td>$link_erstellen</td></tr>";
            }
            echo "</table>\n";
        } else {
            echo "Keine Vorlagen";
        }
    }

    function form_vorlage_neu()
    {
        $f = new formular ();
        $f->erstelle_formular("Neue Serienbriefvorlage erfassen", NULL);
        $this->dropdown_kats('Kategorie', 'kat', 'kat', '', '');
        $f = new formular ();
        $f->text_feld('Neue Kategorie', 'kat_man', null, 50, 'kat_man', '');
        $this->dropdown_typ('Empfängergruppe', 'empf_typ', 'empf_typ', '', '');
        $f->text_feld('Betreff', 'kurztext', '', 100, 'kurztext', '');
        $f->text_bereich('Text', 'text', '', 50, 50, 'text');
        $f->hidden_feld("option", "serienbrief_vorlage_send");
        $f->send_button("submit", "Speichern");
        $f->ende_formular();
    }

    function dropdown_kats($label, $name, $id, $js, $vorwahl = '')
    {
        $db_abfrage = "SELECT KAT FROM PDF_VORLAGEN GROUP BY KAT ORDER BY KAT ASC";
        $result = DB::select($db_abfrage);

        if (!empty($result)) {
            echo "<label for=\"$id\">$label</label>";
            echo "<select name=\"$name\" size=\"1\" id=\"$id\" $js>\n";
            echo "<option value=\"NEU\" selected>NEU</option>\n";

            foreach($result as $row) {
                $kat = $row ['KAT'];
                if ($vorwahl == $kat) {
                    echo "<option value=\"$kat\" selected>$kat</option>\n";
                } else {
                    echo "<option value=\"$kat\">$kat</option>\n";
                }
            }
            echo "</select>";
        } else {
            echo "Keine $label XX3";
        }
    }

    function dropdown_typ($label, $name, $id, $js, $vorwahl = '')
    {
        $db_abfrage = "SELECT EMPF_TYP AS KAT FROM PDF_VORLAGEN GROUP BY EMPF_TYP ORDER BY KAT ASC";
        $result = DB::select($db_abfrage);
        if (!empty($result)) {
            echo "<label for=\"$id\">$label</label>";
            echo "<select name=\"$name\" size=\"1\" id=\"$id\" $js>\n";

            foreach($result as $row) {
                $kat = $row ['KAT'];
                if ($vorwahl == $kat) {
                    echo "<option value=\"$kat\" selected>$kat</option>\n";
                } else {
                    echo "<option value=\"$kat\">$kat</option>\n";
                }
            }
            echo "</select>";
        } else {
            echo "Keine $label XX1";
        }
    }

    function vorlage_speichern($kurztext, $text, $kat = 'Alle', $empf_typ = 'Mieter')
    {
        if (!$this->check_v_exists($kurztext, $text)) {
            $db_abfrage = "INSERT INTO PDF_VORLAGEN VALUES (NULL, '$kurztext', '$text', '$empf_typ', '$kat', '1')";
            DB::insert($db_abfrage);
            /* Protokollieren */
            $last_dat = DB::getPdo()->lastInsertId();
            protokollieren('PDF_VORLAGEN', $last_dat, '0');
        }
    }

    function check_v_exists($kurztext, $text)
    {
        $result = DB::select("SELECT * FROM PDF_VORLAGEN WHERE KURZTEXT='$kurztext' && TEXT='$text' ");
        return !empty($result);
    }

    function vorlage_update($dat, $kurztext, $text, $kat = 'Alle', $empf_typ = 'Mieter')
    {
        $db_abfrage = "UPDATE PDF_VORLAGEN SET KURZTEXT= '$kurztext', TEXT= '$text', KAT='$kat', EMPF_TYP='$empf_typ' WHERE DAT='$dat'";
        DB::update($db_abfrage);
        /* Protokollieren */
        protokollieren('PDF_VORLAGEN', $dat, $dat);
    }

    function form_vorlage_edit($dat)
    {
        $this->get_texte($dat);
        $f = new formular ();
        $f->erstelle_formular("Serienbriefvorlage bearbeiten", NULL);
        $this->dropdown_kats('Kategorie', 'kat', 'kat', '', $this->v_kat);
        $this->dropdown_typ('Empfängergruppe', 'empf_typ', 'empf_typ', '', $this->v_empf_typ);
        $f->text_feld('Betreff', 'kurztext', $this->v_kurztext, 100, 'kurztext', '');
        $f->text_bereich('Text', 'text', $this->v_text, 50, 50, 'text');
        $f->hidden_feld("dat", "$dat");
        $f->hidden_feld("option", "serienbrief_vorlage_send1");
        $f->send_button("submit", "Speichern");
        $f->ende_formular();
    }

    function form_serienbrief_an($empfaenger)
    {
        $f = new formular ();
        $f->erstelle_formular("$empfaenger wählen", NULL);
        $this->checkboxen_auswahl($empfaenger);
        $f->hidden_feld("option", "empfaenger2sess");
        $f->send_button("submit", "Hinzufügen");
        $f->ende_formular();
    }

    function checkboxen_auswahl($empfaenger)
    {
        $f = new formular ();
        if ($empfaenger == 'Partner') {
            $p = new partners ();
            $arr = $p->partner_in_array();
            $anz = count($arr);
            if ($anz > 0) {

                for ($a = 0; $a < $anz; $a++) {
                    $p1 = ( object )$arr [$a];
                    if (is_array(session()->get('empfaenger_ids'))) {
                        if (!in_array($p1->PARTNER_ID, session()->get('empfaenger_ids'))) {
                            $f->check_box_js('empf_ids[]', $p1->PARTNER_ID, "$p1->PARTNER_NAME $p1->STRASSE $p1->NUMMER, $p1->PLZ $p1->ORT", '', '');
                        }
                    } else {
                        $f->check_box_js('empf_ids[]', $p1->PARTNER_ID, "$p1->PARTNER_NAME $p1->STRASSE $p1->NUMMER, $p1->PLZ $p1->ORT", '', '');
                    }
                }
            } else {
                throw new \App\Exceptions\MessageException(
                    new \App\Messages\WarningMessage('Keine Partner im System')
                );
            }
        }
        if ($empfaenger == 'Objekt') {
        }
        if ($empfaenger == 'Haus') {
            $f->hidden_feld("empfaenger_typ", "$empfaenger");
            $h = new haus ();
            $arr = $h->liste_aller_haeuser();
            $anz = count($arr);
            if ($anz > 0) {
                for ($a = 0; $a < $anz; $a++) {
                    $haus_str = $arr [$a] ['HAUS_STRASSE'];
                    $haus_nr = $arr [$a] ['HAUS_NUMMER'];
                    $haus_id = $arr [$a] ['HAUS_ID'];

                    if (is_array(session()->get('empfaenger_ids'))) {
                        if (!in_array($haus_id, session()->get('empfaenger_ids'))) {
                            $f->check_box_js('empf_ids[]', $haus_id, "$haus_str $haus_nr", '', '');
                        }
                    } else {
                        $f->check_box_js('empf_ids[]', $haus_id, "$haus_str $haus_nr", '', '');
                    }
                }
            }
        }
        if ($empfaenger == 'exMieter') {
        }
    }

    function pdf_heizungabnahmeprotokoll(Cezpdf $pdf, $mv_id, $einzug = null)
    {
        $mv = new mietvertraege ();
        $mv->get_mietvertrag_infos_aktuell($mv_id);
        $pdf->ezText("<b>Wohnungs-Nr:</b> $mv->einheit_kurzname", 10, array(
            'justification' => 'right'
        ));
        $pdf->ezText("<b>Mieter:</b> $mv->personen_name_string", 10);
        $pdf->ezText("<b>Wohnung:</b> $mv->haus_strasse $mv->haus_nr, $mv->haus_plz $mv->haus_stadt  <b>Wohnlage:</b> $mv->einheit_lage", 10);

        $pdf->ezSetDy(-15); // Abstand
        $tab [0] ['RAUM'] = 'Küche';
        $tab [1] ['RAUM'] = 'Bad';
        $tab [2] ['RAUM'] = '1. Zimmer';
        $tab [3] ['RAUM'] = '2. Zimmer';
        $tab [4] ['RAUM'] = '3. Zimmer';
        $tab [5] ['RAUM'] = '4. Zimmer';
        $tab [6] ['RAUM'] = '';
        $tab [7] ['RAUM'] = '';
        $tab [8] ['RAUM'] = '';
        $tab [9] ['RAUM'] = '';
        $tab [10] ['RAUM'] = '';
        $tab [11] ['RAUM'] = '';
        $tab [12] ['RAUM'] = '';

        $tabw [0] ['RAUM'] = 'Kaltwasser Bad';
        $tabw [1] ['RAUM'] = 'Warmwasser Bad';
        $tabw [2] ['RAUM'] = 'Kaltwasser Küche';
        $tabw [3] ['RAUM'] = 'Warmwasser Küche';
        $tabw [4] ['RAUM'] = '';
        $tabw [5] ['RAUM'] = '';
        $tabw [6] ['RAUM'] = '';
        $tabw [7] ['RAUM'] = '';
        $tabw [8] ['RAUM'] = '';

        $cols = array(
            'RAUM' => "Raum",
            'GERAET_NR' => "Geräte-Nr.",
            'ALT' => "M-WERT(alt)",
            'NEU' => "IST-WERT(neu)"
        );

        if ($einzug == null) {
            $title = "Anlage zum Wohnungsabnahmeprotokoll | Ablesung der Heizung";
        } else {
            $title = "Anlage zum Wohnungsübergabeprotokoll | Ablesung der Heizung";
        }
        $pdf->ezTable($tab, $cols, "$title", array(
            'showHeadings' => 1,
            'shaded' => 1,
            'titleFontSize' => 10,
            'fontSize' => 9,
            'xPos' => 50,
            'xOrientation' => 'right',
            'width' => 500,
            'cols' => array(
                'DATUM' => array(
                    'justification' => 'right',
                    'width' => 50
                ),
                'G_BUCHUNGSNUMMER' => array(
                    'justification' => 'right',
                    'width' => 30
                ),
                'BETRAG' => array(
                    'justification' => 'right',
                    'width' => 50
                ),
                'KOSTENTRAEGER_BEZ' => array(
                    'justification' => 'left',
                    'width' => 75
                ),
                'KONTO' => array(
                    'justification' => 'right',
                    'width' => 30
                ),
                'AUSZUG' => array(
                    'justification' => 'right',
                    'width' => 35
                ),
                'PLATZ' => array(
                    'justification' => 'left',
                    'width' => 50
                )
            )
        ));

        $pdf->ezSetDy(-40); // Abstand

        if ($einzug == null) {
            $title1 = "Anlage zum Wohnungsabnahmeprotokoll | Ablesung der Wasseruhren";
        } else {
            $title1 = "Anlage zum Wohnungsübergabeprotokoll | Ablesung der Wasseruhren";
        }

        $cols = array(
            'RAUM' => "Wasser",
            'GERAET_NR' => "Zähler-Nr.",
            'ALT' => "Stand",
            'NEU' => "Eichdatum !!!"
        );
        $pdf->ezTable($tabw, $cols, "$title1", array(
            'showHeadings' => 1,
            'shaded' => 1,
            'titleFontSize' => 10,
            'fontSize' => 9,
            'xPos' => 50,
            'xOrientation' => 'right',
            'width' => 500,
            'cols' => array(
                'DATUM' => array(
                    'justification' => 'right',
                    'width' => 50
                ),
                'G_BUCHUNGSNUMMER' => array(
                    'justification' => 'right',
                    'width' => 30
                ),
                'BETRAG' => array(
                    'justification' => 'right',
                    'width' => 50
                ),
                'KOSTENTRAEGER_BEZ' => array(
                    'justification' => 'left',
                    'width' => 75
                ),
                'KONTO' => array(
                    'justification' => 'right',
                    'width' => 30
                ),
                'AUSZUG' => array(
                    'justification' => 'right',
                    'width' => 35
                ),
                'PLATZ' => array(
                    'justification' => 'left',
                    'width' => 50
                )
            )
        ));

        /* Footer */
        $pdf->ezSetDy(-20); // Abstand
        $pdf->ezText("$mv->haus_stadt, __________________", 9, array(
            'justification' => 'left'
        ));
        $pdf->ezSetDy(-8); // Abstand
        $pdf->addText(125, $pdf->y, 6, "Datum");

        $pdf->ezSetDy(-20); // Abstand
        $pdf->ezText("____________________________________________      _____________________________________________", 9, array(
            'justification' => 'left'
        ));
        $pdf->ezSetDy(-8); // Abstand
        $pdf->addText(150, $pdf->y, 6, "Mieter");
        $pdf->addText(400, $pdf->y, 6, "Vermieter");
    }

    function pdf_abnahmeprotokoll(Cezpdf &$pdf, $mv_id, $einzug = null)
    {
        $mv = new mietvertraege ();
        $mv->get_mietvertrag_infos_aktuell($mv_id);

        $pdf->ezText("<b>Mieter:</b> $mv->personen_name_string", 10);

        $pdf->rectangle(530, $pdf->y, 10, 10);
        /**
         * Y bei Einzug
         */
        $pdf->addText(441, $pdf->y + 2, 10, 'EINZUG');
        if ($einzug != null) {

            $pdf->addText(531, $pdf->y + 2, 10, 'X');
        }
        $pdf->ezSetDy(-13); // Abstand

        $pdf->rectangle(530, $pdf->y + 1, 10, 10);
        $pdf->addText(440, $pdf->y + 2, 10, 'AUSZUG');

        if ($einzug == null) {

            $pdf->addText(531, $pdf->y + 2, 10, 'X');
        }

        $pdf->ezSetMargins(135, 70, 50, 50);
        $pdf->ezText("<b>Wohnung:</b> $mv->haus_strasse $mv->haus_nr, $mv->haus_plz $mv->haus_stadt <b>Wohnlage:</b> $mv->einheit_lage", 10);
        $pdf->ezSetDy(12); // Abstand zurück
        $pdf->ezText("<b>Wohnungs-Nr:</b> $mv->einheit_kurzname", 10, array(
            'justification' => 'right'
        ));
        $pdf->ezSetDy(-5); // Abstand
        $pdf->ezText('_____ Zimmer           Küche/Kochnische           Wannenbad/Dusche           extra WC           Abstellraum', 10, array(
            'justification' => 'left'
        ));
        $pdf->ezSetDy(-15); // Abstand
        $pdf->ezText('_____ Balkon/Loggia    _____ Keller-Nr: ___________________', 10, array(
            'justification' => 'left'
        ));
        $pdf->ezSetDy(-15); // Abstand
        $pdf->ezText("<b>Beheizung:</b>", 10, array(
            'justification' => 'left'
        ));

        $pdf->rectangle(120, $pdf->y - 2, 10, 10);
        $pdf->addText(135, $pdf->y - 1, 10, 'Zentral');

        $pdf->rectangle(185, $pdf->y - 2, 10, 10);
        $pdf->addText(200, $pdf->y - 1, 10, 'Elt-Heizung');

        $pdf->rectangle(285, $pdf->y - 2, 10, 10);
        $pdf->addText(300, $pdf->y - 1, 10, 'Ofen');

        $pdf->rectangle(350, $pdf->y - 2, 10, 10);
        $pdf->addText(365, $pdf->y - 1, 10, 'Gasetagenheizung');

        $pdf->ezSetDy(-15); // Abstand
        $pdf->ezText("<b>Warmwasser:</b>", 10, array(
            'justification' => 'left'
        ));
        $pdf->rectangle(120, $pdf->y - 2, 10, 10);
        $pdf->addText(135, $pdf->y - 1, 10, 'Elt     DE/Boiler');
        $pdf->rectangle(120, $pdf->y - 15, 10, 10);
        $pdf->addText(135, $pdf->y - 15, 10, 'Gas   DE/Boiler');

        $pdf->rectangle(350, $pdf->y, 10, 10);
        $pdf->addText(365, $pdf->y, 10, 'Zentral');
        $pdf->rectangle(350, $pdf->y - 15, 10, 10);
        $pdf->addText(365, $pdf->y - 15, 10, 'über Gasetagenheizung');

        $pdf->ezSetDy(-15); // Abstand
        $y_e = $pdf->y;
        $pdf->ezText("<b>Elektrik-Zähler:</b>", 10, array(
            'justification' => 'left'
        ));
        $pdf->ezSetDy(-5); // Abstand

        $pdf->ezText("<b>Zähler-Nr.:</b>", 10, array(
            'justification' => 'left'
        ));
        $pdf->ezSetDy(-5); // Abstand
        $this->kasten($pdf, 10, 120, 15, 15);

        $pdf->ezText("<b>Stand</b>", 10, array(
            'justification' => 'left'
        ));
        $pdf->ezSetDy(-10); // Abstand
        $this->kasten($pdf, 6, 120, 15, 15);
        $pdf->addText(215, $pdf->y, 15, "<b>,</b>");
        $this->kasten($pdf, 3, 225, 15, 15);

        $abstand = $pdf->y - $y_e;
        $pdf->ezSetDy(-$abstand); // Zurückhöhe Elektrozähler
        $pdf->ezSetMargins(135, 70, 280, 50);
        $pdf->ezText("<b>Gas-Zähler:</b>", 10, array(
            'justification' => 'left'
        ));
        $pdf->ezSetDy(-5); // Abstand

        $pdf->ezText("<b>Zähler-Nr.:</b>", 10, array(
            'justification' => 'left'
        ));
        $pdf->ezSetDy(-5); // Abstand
        $this->kasten($pdf, 14, 340, 15, 15);

        $pdf->ezText("<b>Stand</b>", 10, array(
            'justification' => 'left'
        ));
        $pdf->ezSetDy(-10); // Abstand
        $this->kasten($pdf, 6, 340, 15, 15);
        $pdf->addText(435, $pdf->y, 15, "<b>,</b>");
        $this->kasten($pdf, 3, 445, 15, 15);

        $pdf->ezSetMargins(135, 70, 50, 50);
        $pdf->ezSetDy(-10); // Abstand
        $pdf->ezText("Der Mieter stimmt zu, dass der Vermieter die Zählerstände unter Angabe von Vor- und Zuname, sowie der Verzugsanschrift an den regionalen Versorger meldet.", 9, array(
            'justification' => 'left'
        ));
        $pdf->ezSetDy(-10); // Abstand
        $pdf->ezText("<b>Bei der Wohnungsabnahme wurde festgestellt:</b>", 10, array(
            'justification' => 'left'
        ));
        $pdf->ezSetMargins(135, 70, 330, 50);
        $pdf->ezSetDy(12); // Zurück
        $pdf->ezText("<b>Beseitigung erfolgt durch:</b>", 10, array(
            'justification' => 'left'
        ));

        $pdf->ezSetMargins(135, 70, 50, 50);

        $pdf->ezSetDy(-5); // Abstand
        $pdf->ezText("<b>Wohnungsflur:</b>", 10, array(
            'justification' => 'left'
        ));
        $pdf->ezSetDy(-20); // Abstand

        $this->kasten($pdf, 4, 50, 15, 15, 125);
        $pdf->addText(70, $pdf->y + 3, 10, "keine Mängel");
        $pdf->addText(210, $pdf->y + 3, 10, "folgende Mängel");
        $pdf->addText(380, $pdf->y + 3, 10, "Mieter");
        $pdf->addText(490, $pdf->y + 3, 10, "Vermieter");
        $pdf->setLineStyle(1);
        $pdf->ezSetDy(-20); // Abstand
        $pdf->line(42, $pdf->y, 550, $pdf->y);
        $pdf->ezSetDy(-20); // Abstand
        $pdf->line(42, $pdf->y, 550, $pdf->y);
        $pdf->ezSetDy(-20); // Abstand
        $pdf->line(42, $pdf->y, 550, $pdf->y);

        $pdf->ezText("<b>Küche:</b>", 10, array(
            'justification' => 'left'
        ));
        $pdf->ezSetDy(-20); // Abstand

        $this->kasten($pdf, 4, 50, 15, 15, 125);
        $pdf->addText(70, $pdf->y + 3, 10, "keine Mängel");
        $pdf->addText(210, $pdf->y + 3, 10, "folgende Mängel");
        $pdf->addText(350, $pdf->y + 3, 10, "Mieter");
        $pdf->addText(490, $pdf->y + 3, 10, "Vermieter");
        $pdf->setLineStyle(1);
        $pdf->ezSetDy(-20); // Abstand
        $pdf->line(42, $pdf->y, 550, $pdf->y);
        $pdf->ezSetDy(-20); // Abstand
        $pdf->line(42, $pdf->y, 550, $pdf->y);
        $pdf->ezSetDy(-20); // Abstand
        $pdf->line(42, $pdf->y, 550, $pdf->y);

        $pdf->ezText("<b>Bad:</b>", 10, array(
            'justification' => 'left'
        ));
        $pdf->ezSetDy(-20); // Abstand

        $this->kasten($pdf, 4, 50, 15, 15, 125);
        $pdf->addText(70, $pdf->y + 3, 10, "keine Mängel");
        $pdf->addText(210, $pdf->y + 3, 10, "folgende Mängel");
        $pdf->addText(350, $pdf->y + 3, 10, "Mieter");
        $pdf->addText(490, $pdf->y + 3, 10, "Vermieter");
        $pdf->setLineStyle(1);
        $pdf->ezSetDy(-20); // Abstand
        $pdf->line(42, $pdf->y, 550, $pdf->y);
        $pdf->ezSetDy(-20); // Abstand
        $pdf->line(42, $pdf->y, 550, $pdf->y);
        $pdf->ezSetDy(-20); // Abstand
        $pdf->line(42, $pdf->y, 550, $pdf->y);

        $pdf->ezText("<b>Wohnzimmer:</b>", 10, array(
            'justification' => 'left'
        ));
        $pdf->ezSetDy(-20); // Abstand

        $this->kasten($pdf, 4, 50, 15, 15, 125);
        $pdf->addText(70, $pdf->y + 3, 10, "keine Mängel");
        $pdf->addText(210, $pdf->y + 3, 10, "folgende Mängel");
        $pdf->addText(350, $pdf->y + 3, 10, "Mieter");
        $pdf->addText(490, $pdf->y + 3, 10, "Vermieter");
        $pdf->setLineStyle(1);
        $pdf->ezSetDy(-20); // Abstand
        $pdf->line(42, $pdf->y, 550, $pdf->y);
        $pdf->ezSetDy(-20); // Abstand
        $pdf->line(42, $pdf->y, 550, $pdf->y);
        $pdf->ezSetDy(-20); // Abstand
        $pdf->line(42, $pdf->y, 550, $pdf->y);

        $pdf->ezNewPage();
        $pdf->ezText("<b>Schlafzimmer:</b>", 10, array(
            'justification' => 'left'
        ));
        $pdf->ezSetDy(-20); // Abstand

        $this->kasten($pdf, 4, 50, 15, 15, 125);
        $pdf->addText(70, $pdf->y + 3, 10, "keine Mängel");
        $pdf->addText(210, $pdf->y + 3, 10, "folgende Mängel");
        $pdf->addText(350, $pdf->y + 3, 10, "Mieter");
        $pdf->addText(490, $pdf->y + 3, 10, "Vermieter");
        $pdf->setLineStyle(1);
        $pdf->ezSetDy(-20); // Abstand
        $pdf->line(42, $pdf->y, 550, $pdf->y);
        $pdf->ezSetDy(-20); // Abstand
        $pdf->line(42, $pdf->y, 550, $pdf->y);
        $pdf->ezSetDy(-20); // Abstand
        $pdf->line(42, $pdf->y, 550, $pdf->y);

        $pdf->ezText("<b>1. Kinderzimmer:</b>", 10, array(
            'justification' => 'left'
        ));
        $pdf->ezSetDy(-20); // Abstand

        $this->kasten($pdf, 4, 50, 15, 15, 125);
        $pdf->addText(70, $pdf->y + 3, 10, "keine Mängel");
        $pdf->addText(210, $pdf->y + 3, 10, "folgende Mängel");
        $pdf->addText(350, $pdf->y + 3, 10, "Mieter");
        $pdf->addText(490, $pdf->y + 3, 10, "Vermieter");
        $pdf->setLineStyle(1);
        $pdf->ezSetDy(-20); // Abstand
        $pdf->line(42, $pdf->y, 550, $pdf->y);
        $pdf->ezSetDy(-20); // Abstand
        $pdf->line(42, $pdf->y, 550, $pdf->y);
        $pdf->ezSetDy(-20); // Abstand
        $pdf->line(42, $pdf->y, 550, $pdf->y);

        $pdf->ezText("<b>2. Kinderzimmer:</b>", 10, array(
            'justification' => 'left'
        ));
        $pdf->ezSetDy(-20); // Abstand

        $this->kasten($pdf, 4, 50, 15, 15, 125);
        $pdf->addText(70, $pdf->y + 3, 10, "keine Mängel");
        $pdf->addText(210, $pdf->y + 3, 10, "folgende Mängel");
        $pdf->addText(350, $pdf->y + 3, 10, "Mieter");
        $pdf->addText(490, $pdf->y + 3, 10, "Vermieter");
        $pdf->setLineStyle(1);
        $pdf->ezSetDy(-20); // Abstand
        $pdf->line(42, $pdf->y, 550, $pdf->y);
        $pdf->ezSetDy(-20); // Abstand
        $pdf->line(42, $pdf->y, 550, $pdf->y);
        $pdf->ezSetDy(-20); // Abstand
        $pdf->line(42, $pdf->y, 550, $pdf->y);

        $pdf->ezText("<b>3. Kinderzimmer:</b>", 10, array(
            'justification' => 'left'
        ));
        $pdf->ezSetDy(-20); // Abstand

        $this->kasten($pdf, 4, 50, 15, 15, 125);
        $pdf->addText(70, $pdf->y + 3, 10, "keine Mängel");
        $pdf->addText(210, $pdf->y + 3, 10, "folgende Mängel");
        $pdf->addText(350, $pdf->y + 3, 10, "Mieter");
        $pdf->addText(490, $pdf->y + 3, 10, "Vermieter");
        $pdf->setLineStyle(1);
        $pdf->ezSetDy(-20); // Abstand
        $pdf->line(42, $pdf->y, 550, $pdf->y);
        $pdf->ezSetDy(-20); // Abstand
        $pdf->line(42, $pdf->y, 550, $pdf->y);
        $pdf->ezSetDy(-20); // Abstand
        $pdf->line(42, $pdf->y, 550, $pdf->y);

        $pdf->ezText("<b>Balkon/Loggia/Sonstiges:</b>", 10, array(
            'justification' => 'left'
        ));
        $pdf->ezSetDy(-20); // Abstand

        $this->kasten($pdf, 4, 50, 15, 15, 125);
        $pdf->addText(70, $pdf->y + 3, 10, "keine Mängel");
        $pdf->addText(210, $pdf->y + 3, 10, "folgende Mängel");
        $pdf->addText(350, $pdf->y + 3, 10, "Mieter");
        $pdf->addText(490, $pdf->y + 3, 10, "Vermieter");
        $pdf->setLineStyle(1);
        $pdf->ezSetDy(-18); // Abstand
        $pdf->line(42, $pdf->y, 550, $pdf->y);
        $pdf->ezSetDy(-18); // Abstand
        $pdf->line(42, $pdf->y, 550, $pdf->y);
        $pdf->ezSetDy(-18); // Abstand
        $pdf->line(42, $pdf->y, 550, $pdf->y);
        $pdf->ezSetDy(-18); // Abstand
        $pdf->line(42, $pdf->y, 550, $pdf->y);

        $pdf->ezText("Folgende Schlüssel wurden übergeben:", 9, array(
            'justification' => 'left'
        ));
        $pdf->ezSetMargins(135, 70, 250, 50);
        $pdf->ezSetDy(10); // Zurück
        $pdf->ezText("______ Haustür-/ Zentralschlüssel", 9, array(
            'justification' => 'left'
        ));
        $pdf->ezSetMargins(135, 70, 50, 50);
        $pdf->ezText("______ Wohnungstür   ______ Briefkasten   ______ Keller   ______ Sonstige _____________________________________", 9, array(
            'justification' => 'left'
        ));
        $pdf->ezSetMargins(135, 70, 50, 50);
        $pdf->ezSetDy(-5); // Abstand
        $pdf->ezText("<b>Die gezahlte Kaution kann ausgezahlt werden</b>", 9, array(
            'justification' => 'left'
        ));
        $pdf->ezSetDy(-3); // Abstand
        $this->kasten($pdf, 2, 250, 10, 10, 50);
        $pdf->addText(270, $pdf->y + 2, 9, "<b>JA</b>");
        $pdf->addText(335, $pdf->y + 2, 9, "<b>NEIN</b>");
        $pdf->ezSetDy(-5); // Abstand
        $pdf->ezText("IBAN:__________________________________  BIC:_________________  BANKNAME:___________________________", 9, array(
            'justification' => 'left'
        ));
        $pdf->ezSetDy(-5); // Abstand
        $pdf->ezText("Verzugsanschrift: ____________________________________________________________________________________", 9, array(
            'justification' => 'left'
        ));
        $pdf->ezSetDy(-5); // Abstand
        $pdf->ezText("Telefon und E-Mail: __________________________________________________________________________________", 9, array(
            'justification' => 'left'
        ));
        $pdf->ezSetDy(-20); // Abstand
        $this->kasten($pdf, 1, 50, 10, 10, 50);
        if ($einzug == 'einzug') {
            $pdf->addText(65, $pdf->y + 2, 9, "<b>Der Mieter hat die Einzugsbestätigung erhalten.</b>");
        } else {
            $pdf->addText(65, $pdf->y + 2, 9, "<b>Der Mieter hat die Auszugsbestätigung erhalten.</b>");
        }
        $pdf->ezSetDy(-10); // Abstand
        $pdf->ezText("$mv->haus_stadt, __________________    ___________________________________    ____________________________________", 9, array(
            'justification' => 'left'
        ));
        $pdf->ezSetDy(-7); // Abstand
        $pdf->addText(112, $pdf->y, 6, "Datum");
        $pdf->addText(255, $pdf->y, 6, "Mieter");
        $pdf->addText(440, $pdf->y, 6, "Vermieter");
    }

    function kasten(Cezpdf &$pdf, $anz_felder, $startx, $h, $b, $abstand_zw = null)
    {
        for ($a = 1; $a <= $anz_felder; $a++) {

            if ($a == 1) {
                $pdf->rectangle($startx, $pdf->y, $b, $h);
            } else {
                if ($abstand_zw != null) {
                    $startx += $abstand_zw;
                }

                $startx += $b;
                $pdf->rectangle($startx, $pdf->y, $b, $h);
            }
        }
    }

    function pdf_einauszugsbestaetigung(Cezpdf $pdf, $mv_id, $einzug = 0)
    {
        $pdf->ezSetMargins(135, 70, 50, 50);
        $mv = new mietvertraege ();
        $mv->get_mietvertrag_infos_aktuell($mv_id);
        $oo = new objekt ();
        $oo->get_objekt_infos($mv->objekt_id);
        if ($mv->anzahl_personen > 1) {
            $ist_sind = 'sind';
        } else {
            $ist_sind = 'ist';
        }

        if ($einzug == '0') {
            $pdf->ezText("<b>Einzugsbestätigung</b>", 18, array(
                'justification' => 'left'
            ));
            $pdf->ezText("$mv->einheit_kurzname", 10, array(
                'justification' => 'right'
            ));
        } else {
            $pdf->ezText("<b>Auszugsbestätigung</b>", 18, array(
                'justification' => 'left'
            ));
            $pdf->ezText("$mv->einheit_kurzname", 10, array(
                'justification' => 'right'
            ));
        }
        $pdf->ezText("<b>Wohnungsgeberbescheinigung gemäß § 19 des Bundesmeldegesetzes (BMG)</b>", 11, array(
            'justification' => 'left'
        ));
        $pdf->ezSetDy(-35); // Abstand
        $pdf->ezText("Hiermit bestätige(n) ich/wir als Wohnungsgeber/Vermieter, dass", 10);
        $pdf->ezSetDy(-15); // Abstand
        $pdf->ezText("$mv->personen_name_string_u", 10);

        $pdf->ezSetDy(-15); // Abstand
        if ($einzug == '0') {
            $pdf->ezText("in die von mir/uns vermietete Wohnung", 10);
        } else {
            $pdf->ezText("aus der von mir/uns vermieteten Wohnung", 10);
        }
        $pdf->ezSetDy(-15); // Abstand
        $pdf->ezText("unter der Anschrift: $mv->haus_strasse $mv->haus_nr, $mv->haus_plz $mv->haus_stadt  (Wohnlage:</b> $mv->einheit_lage)", 10);
        $pdf->ezSetDy(-15); // Abstand
        if ($einzug == '0') {
            $pdf->ezText("am _______________________  eingezogen $ist_sind.", 10);
        } else {
            $pdf->ezText("am _______________________  ausgezogen $ist_sind.", 10);
        }

        $pdf->ezSetDy(-20); // Abstand

        if (empty ($oo->objekt_eigentuemer)) {
            $pdf->ezSetDy(-30); // Abstand
            $this->kasten($pdf, 1, 50, 10, 10);
            $pdf->addText(70, $pdf->y + 1, 10, 'Der Wohnungsgeber/Vermieter ist gleichzeitig <b>Eigentümer</b> der Wohnung oder');

            $pdf->ezSetDy(-20); // Abstand

            $this->kasten($pdf, 1, 50, 10, 10);

            $pdf->addText(70, $pdf->y + 1, 10, "Der Wohnungsgeber/Vermieter ist <b>nicht</b> Eigentümer der Wohnung");
            $pdf->ezSetDy(-15); // Abstand

            $pdf->ezSetDy(-25); // Abstand
            $pdf->line(50, $pdf->y, 550, $pdf->y);
            $pdf->ezSetDy(-25); // Abstand
            $pdf->line(50, $pdf->y, 550, $pdf->y);
        } else {
            $this->kasten($pdf, 1, 50, 10, 10);
            $pdf->addText(50, $pdf->y + 2, 10, 'X');

            $pdf->addText(70, $pdf->y + 1, 10, "Der Wohnungsgeber ist <b>nicht</b> Eigentümer der Wohnung");
            $pdf->ezSetDy(-15); // Abstand

            $pdf->ezText("Name und Anschrift des <b>Eigentümers</b> lauten:", 10);

            $pdf->ezText("$oo->objekt_eigentuemer", 10);
            $pp = new partners ();
            $pp->get_partner_info($oo->objekt_eigentuemer_id);
            $pdf->ezText("$pp->partner_strasse $pp->partner_hausnr, $pp->partner_plz $pp->partner_ort", 10);
        }

        $pdf->ezSetDy(-25); // Abstand

        $pdf->ezText("Ich bestätige mit meiner Unterschrift den Ein- bzw. Auszug der oben genannten Person(en) in die näher bezeichnete Wohnung und dass ich als Wohnungsgeber oder als beauftragte Person diese Bescheinigung ausstellen darf. Ich habe davon Kenntnis genommen, da ich ordnungswidrig handele, wenn ich hierzu nicht berechtigt bin und dass es verboten ist, eine Wohnanschrift für eine Anmeldung eines Wohnsitzes einem Dritten anzubieten oder zur Verfügung zu stellen, obwohl ein tatsächlicher Bezug der Wohnung durch einen Dritten weder stattfindet noch beabsichtigt ist. Ein Verstoß gegen das Verbot stellt auch einen Ordnungswidrigkeit dar.", 8);

        /* Footer */
        $pdf->ezSetDy(-25); // Abstand
        $pdf->ezText("$mv->haus_stadt, __________________", 9, array(
            'justification' => 'left'
        ));
        $pdf->ezSetDy(-7); // Abstand
        $pdf->addText(125, $pdf->y, 6, "Datum");

        $pdf->ezSetDy(-30); // Abstand
        $pdf->ezText("____________________________________________", 9, array(
            'justification' => 'left'
        ));
        $pdf->ezSetDy(-8); // Abstand
        $pdf->addText(57, $pdf->y, 6, "Unterschrift des Wohnungsgebers/Vermieters oder der beauftragten Person");

        $pdf->ezSetDy(-15); // Abstand
    }

    function addTable(Cezpdf &$pdf, &$data, $cols = '', $title = '', $options = '')
    {
        $pdf->transaction('start');
        $pageNumber = $pdf->ezGetCurrentPageNumber();
        $pdf->ezTable($data, $cols, $title, $options);
        if ($pageNumber < $pdf->ezGetCurrentPageNumber()) {
            $pdf->transaction('rewind');
        } else {
            $pdf->transaction('commit');
            return;
        }
        $pdf->ezNewPage();
        $pageNumber = $pdf->ezGetCurrentPageNumber();
        $pdf->ezTable($data, $cols, $title, $options);
        if ($pageNumber < $pdf->ezGetCurrentPageNumber()) {
            $pdf->transaction('rewind');
            $pdf->ezTable($data, $cols, $title, $options);
        }
        $pdf->transaction('commit');
    }
} // end class b_pdf
