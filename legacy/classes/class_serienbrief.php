<?php

class serienbrief
{
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

        /* Wenn keine Vorlagen, dann alle anzeigen */
        if (empty($result)) {
            $db_abfrage = "SELECT * FROM PDF_VORLAGEN ORDER BY KURZTEXT ASC";
            $result = DB::select($db_abfrage);
        }

        if (!empty($result)) {
            echo "<table class=\"striped\">\n";
            $link_kat = "<a href='" . route('legacy::weg::index', ['option' => 'serien_brief_vorlagenwahl']) . "'>Alle Kats anzeigen</a>";
            echo "<thead>";
            echo "<tr><th>Vorlage / Betreff</th><th>KAT</th><th>BEARBEITEN</th><th>ANSEHEN</th></tr>";
            echo "<tr><th><b>$empf_typ<b></th><th>$link_kat</th><th></th><th></th></tr>";
            echo "</thead>";

            foreach($result as $row) {
                $dat = $row ['DAT'];
                $kurztext = $row ['KURZTEXT'];
                $text = $row ['TEXT'];
                $kat = $row ['KAT'];

                if ($empf_typ == 'Eigentuemer') {
                    $link_ansehen = "<a href='" . route('legacy::weg::index', ['option' => 'serienbrief_pdf', 'vorlagen_dat' => $dat]) . "'>Serienbrief als PDF</a>";
                    $link_kat = "<a href='" . route('legacy::weg::index', ['option' => 'serien_brief_vorlagenwahl', 'kat' => $kat]) . "'>$kat</a>";
                }

                if ($empf_typ == 'Partner') {
                    $link_ansehen = "<a href='" . route('legacy::partner::index', ['option' => 'serienbrief_pdf', 'vorlagen_dat' => $dat]) . "'>Serienbrief als PDF</a>";
                    $link_kat = "<a href='" . route('legacy::partner::index', ['option' => 'serien_brief_vorlagenwahl', 'kat' => $kat]) . "'>$kat</a>";
                }

                $link_bearbeiten = "<a href='" . route('legacy::bk::index', ['option' => 'vorlage_bearbeiten', 'vorlagen_dat' => $dat]) . "'>Vorlage bearbeiten</a>";

                echo "<tr><td>$kurztext</td><td>$link_kat</td><td>$link_bearbeiten</td><td>$link_ansehen</td></tr>";
            }
            echo "</table>\n";
        } else {
            echo "Keine Vorlagen AA3";
        }
    } // end function

    function erstelle_brief_vorlage($v_dat, $empf_typ, $empf_id_arr, $option = '0')
    {
        $anz_empf = count($empf_id_arr);
        if ($anz_empf > 0) {

            if ($empf_typ == 'Eigentuemer') {

                $pdf = new Cezpdf ('a4', 'portrait');
                $bpdf = new b_pdf ();
                $bpdf->b_header($pdf, 'Partner', session()->get('partner_id'), 'portrait', 'Helvetica', 6);
                $pdf->ezStopPageNumbers(); // seitennummerierung beenden

                $anz_eigentuemer = count($empf_id_arr);
                for ($index = 0; $index < $anz_eigentuemer; $index++) {

                    $e_id = $empf_id_arr [$index];

                    $weg = new weg ();
                    $weg->get_eigentumer_id_infos3($e_id);
                    $monat = date("m");
                    $jahr = date("Y");
                    $this->hausgeld_monatlich_de = nummer_punkt2komma($weg->get_sume_hausgeld('Einheit', $weg->einheit_id, $monat, $jahr) * -1);
                    $this->hausgeld_monatlich_en = $weg->get_sume_hausgeld('Einheit', $weg->einheit_id, $monat, $jahr) * -1;

                    $dets = new detail ();

                    $gk = new geldkonto_info ();
                    $gk->geld_konto_ermitteln('Objekt', $weg->objekt_id);

                    $bpdf->get_texte($v_dat);

                    /* Faltlinie */
                    $pdf->setLineStyle(0.2);

                    $pdf->line(5, 542, 20, 542);

                    $pdf->ezText($weg->post_anschrift, 11);

                    // ##############################################################
                    $pdf->ezSetDy(-60);

                    if (!request()->has('druckdatum')) {
                        $datum_heute = date("d.m.Y");
                    } else {
                        $datum_heute = request()->input('druckdatum');
                    }
                    $p = new partners ();
                    $p->get_partner_info(session()->get('partner_id'));

                    $pdf->ezText("$p->partner_ort, $datum_heute", 10, array(
                        'justification' => 'right'
                    ));
                    $pdf->ezText("<b>Objekt: $weg->haus_strasse $weg->haus_nummer, $weg->haus_plz $weg->haus_stadt</b>", 10);

                    $pdf->ezText("<b>Einheit: $weg->einheit_kurzname</b>", 10);
                    $pdf->ezText("<b>$bpdf->v_kurztext</b>", 10);

                    $pdf->ezSetDy(-30);
                    $pdf->ezText("$weg->anrede_brief", 10);

                    eval ("\$bpdf->v_text = \"$bpdf->v_text\";");; // Variable ausm Text füllen

                    $pdf->ezText("$bpdf->v_text", 10, array(
                        'justification' => 'left'
                    ));

                    /* NEue Seite */
                    if ($index < sizeof($empf_id_arr) - 1) {
                        $pdf->ezNewPage();
                    }
                }
                ob_end_clean(); // ausgabepuffer leeren
                $dateiname = "$datum_heute - Serie - $bpdf->v_kurztext.pdf";
                $pdf_opt ['Content-Disposition'] = $dateiname;
                $pdf->ezStream($pdf_opt);
            }

            //
            // /SERIENBRIEF AN PARTNER
            //

            if ($empf_typ == 'Partner') {

                $pdf = new Cezpdf ('a4', 'portrait');
                $bpdf = new b_pdf ();
                $bpdf->b_header($pdf, 'Partner', session()->get('partner_id'), 'portrait', 'Helvetica.afm', 6);
                $pdf->ezStopPageNumbers(); // seitennummerierung beenden

                $anz_eigentuemer = count($empf_id_arr);
                for ($index = 0; $index < $anz_eigentuemer; $index++) {

                    $e_id = $empf_id_arr [$index];

                    $pp = new partners ();
                    $pp->get_partner_info($e_id);

                    $dets = new detail ();

                    $bpdf->get_texte($v_dat);

                    /* Faltlinie */
                    $pdf->setLineStyle(0.2);
                    $pdf->line(5, 542, 20, 542);

                    $pdf->ezText("$pp->partner_name\n$pp->partner_strasse $pp->partner_hausnr\n<b>$pp->partner_plz $pp->partner_ort</b>", 11);

                    // ##############################################################
                    $pdf->ezSetDy(-60);

                    $datum_heute = date("d.m.Y");
                    $p = new partners ();
                    $p->get_partner_info(session()->get('partner_id'));

                    $pdf->ezText("$p->partner_ort, $datum_heute", 10, array(
                        'justification' => 'right'
                    ));
                    $pdf->ezText("<b>$bpdf->v_kurztext</b>", 10);

                    $pdf->ezSetDy(-30);
                    $pdf->ezText("Sehr geehrte Damen und Herren,\n", 10);

                    eval ("\$bpdf->v_text = \"$bpdf->v_text\";");; // Variable ausm Text füllen

                    $pdf->ezText("$bpdf->v_text", 11, array(
                        'justification' => 'full'
                    ));

                    /* NEue Seite */
                    if ($index < sizeof($empf_id_arr) - 1) {
                        $pdf->ezNewPage();
                    }
                }
                ob_end_clean(); // ausgabepuffer leeren
                $dateiname = "$datum_heute - Serie - $bpdf->v_kurztext.pdf";
                $pdf_opt['Content-Disposition'] = $dateiname;
                $pdf->ezStream($pdf_opt);
            }
        } else {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\WarningMessage('Keine Empfänger gewählt')
            );
        }
    }
} // ENDE CLASS