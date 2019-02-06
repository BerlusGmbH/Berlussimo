<?php

class todo
{
    public $text;
    public $t_dat;
    public $t_id;
    public $ue_id;
    public $benutzer_typ;
    public $benutzer_id;
    public $verfasser_id;
    public $mitarbeiter_name;
    public $partner_ans;
    public $kos_typ;
    public $kos_id;
    public $partner_fax;
    public $partner_email;
    public $verfasst_von;
    public $erledigt;
    public $erledigt_text;
    public $anzeigen_ab;
    public $akut;
    public $kos_bez;

    function form_neue_baustelle($t_id = NULL)
    {
        $f = new formular ();
        $f->erstelle_formular('Neue Baustelle erstellen', '');
        $f->text_feld('Bezeichnung', 'bau_bez', '', 50, 'bau_bez', '');
        $f->hidden_feld('option', 'neue_baustelle');
        $p = new partners ();
        $p->partner_dropdown('Rechnungsempfänger wählen', 'p_id', 'p_id');
        $f->send_button('btn_sndb', 'Erstellen');
        $f->ende_formular();
    }

    function neue_baustelle_speichern($bau_bez, $p_id)
    {
        $last_id = last_id2('BAUSTELLEN_EXT', 'ID') + 1;
        $db_abfrage = "INSERT INTO BAUSTELLEN_EXT VALUES (NULL, '$last_id', '$bau_bez', '$p_id', '1','1')";
        DB::insert($db_abfrage);
        return true;
    }

    function baustellen_liste($aktiv = 1)
    {
        $arr = $this->baustellen_liste_arr($aktiv);
        if (!empty($arr)) {
            $anz = count($arr);
            echo "<table class=\"sortable striped\">";
            echo "<thead><tr><th>Baustelle</th><th>Rechnungsempfänger</th><th>Optionen</th></tr></thead>";
            for ($a = 0; $a < $anz; $a++) {
                $bau_id = $arr [$a] ['ID'];
                $bez = $arr [$a] ['BEZ'];
                $p_id = $arr [$a] ['PARTNER_ID'];
                $p = new partners ();
                $p->get_partner_name($p_id);
                $partner_name = $p->partner_name;
                if ($aktiv == '1') {
                    $link = "<a href='" . route('web::construction::legacy', ['option' => 'baustelle_deaktivieren', 'bau_id' => $bau_id]) . "'>Deaktivieren</a>";
                } else {
                    $link = "<a href='" . route('web::construction::legacy', ['option' => 'baustelle_aktivieren', 'bau_id' => $bau_id]) . "'>Aktivieren</a>";
                }
                echo "<tr><td>$bez</td><td>$partner_name</td><td>$link</td></tr>";
            }
            echo "</table>";
        }
    }

    function baustellen_liste_arr($aktiv = 1)
    {
        $db_abfrage = "SELECT * FROM BAUSTELLEN_EXT WHERE AKTUELL='1' && AKTIV='$aktiv' ORDER BY BEZ, PARTNER_ID";
        $result = DB::select($db_abfrage);
        return $result;
    }

    function baustelle_aktivieren($b_id, $aktiv = '1')
    {
        $db_abfrage = "UPDATE BAUSTELLEN_EXT SET AKTIV='$aktiv' WHERE ID='$b_id'";
        DB::update($db_abfrage);
    }

    function get_text($t_id)
    {
        $result = DB::select("SELECT TEXT FROM TODO_LISTE WHERE T_ID='$t_id' && AKTUELL='1' ORDER BY T_ID DESC LIMIT 0,1");
        $row = $result[0];
        return $row ['TEXT'];
    }

    function get_my_projekt_arr($benutzer_id, $erl = '0')
    {
        if ($erl == '0') {
            $db_abfrage = "SELECT T_ID AS PROJ_ID
FROM `TODO_LISTE`
WHERE (
`BENUTZER_ID` ='$benutzer_id'
OR `VERFASSER_ID` ='$benutzer_id'
)
AND `AKTUELL` = '1' && ERLEDIGT='0' && UE_ID='0'";
        } else {
            $db_abfrage = "SELECT T_ID AS PROJ_ID
FROM `TODO_LISTE`
WHERE (
`BENUTZER_ID` ='$benutzer_id'
OR `VERFASSER_ID` ='$benutzer_id'
)
AND `AKTUELL` = '1' && ERLEDIGT='1' && UE_ID='0'";
        }

        $result = DB::select($db_abfrage);
        return $result;
    }

    function get_unteruafgaben_arr($t_id)
    {
        $db_abfrage = "SELECT * FROM TODO_LISTE WHERE UE_ID ='$t_id' && ANZEIGEN_AB <= DATE_FORMAT(NOW(), '%Y-%m-%d' ) && AKTUELL='1' ORDER BY ERLEDIGT ASC, AKUT ASC, ANZEIGEN_AB ASC";
        $result = DB::select($db_abfrage);
        return $result;
    }

    function kontaktdaten_anzeigen_mieter($einheit_id)
    {
        $ee = new einheit ();
        $einheit = \App\Models\Einheiten::findOrFail($einheit_id);
        $mietvertraege = $einheit->mietvertraege()->active()->get();
        if ($mietvertraege->isEmpty()) {
            /* Nie vermietet */
            $ee->get_einheit_info($einheit_id);
            return "$ee->haus_strasse $ee->haus_nummer, $ee->haus_plz $ee->haus_stadt\n<b>Lage:</b> $ee->einheit_lage\n<b>Leerstand</b>";
        } else {
            $mv_id = $mietvertraege->first()->MIETVERTRAG_ID;
            $m = new mietvertraege ();
            $m->get_mietvertrag_infos_aktuell($mv_id);
            $result = DB::select("SELECT PERSON_MIETVERTRAG_PERSON_ID FROM PERSON_MIETVERTRAG WHERE PERSON_MIETVERTRAG_MIETVERTRAG_ID='$mv_id' && PERSON_MIETVERTRAG_AKTUELL='1' ORDER BY PERSON_MIETVERTRAG_ID ASC");
            if (!empty($result)) {
                $kontaktdaten = "$m->haus_strasse $m->haus_nr, $m->haus_plz $m->haus_stadt<br><br><b>Lage:</b> $m->einheit_lage<br><b>Einheit:</b> $m->einheit_kurzname<br>";
                foreach ($result as $row) {
                    $person_id = $row ['PERSON_MIETVERTRAG_PERSON_ID'];
                    $kontaktdaten .= "<br><b>" . \App\Models\Person::find($person_id)->address_name . "</b>";
                    $arr = $this->finde_detail_kontakt_arr('Person', $person_id);
                    if (!empty($arr)) {
                        $anz = count($arr);
                        for ($a = 0; $a < $anz; $a++) {
                            $dname = $arr [$a] ['DETAIL_NAME'];
                            $dinhalt = $arr [$a] ['DETAIL_INHALT'];
                            $dbemerkung = $arr [$a] ['DETAIL_BEMERKUNG'];
                            $kontaktdaten .= "<br>    <b>$dname</b>: $dinhalt";
                            if ($dbemerkung) {
                                $kontaktdaten .= ", $dbemerkung";
                            }
                        }
                    }
                }
                return $kontaktdaten;
            }
        }
    }

    function finde_detail_kontakt_arr($tab, $id)
    {
        $db_abfrage = "SELECT DETAIL_NAME, DETAIL_INHALT, DETAIL_BEMERKUNG FROM DETAIL WHERE DETAIL_ZUORDNUNG_TABELLE = '$tab' && (DETAIL_NAME LIKE 'tel%' or DETAIL_NAME LIKE '%fax%' or DETAIL_NAME LIKE '%mobil%' or DETAIL_NAME LIKE '%handy%') && DETAIL_ZUORDNUNG_ID = '$id' && DETAIL_AKTUELL = '1' ORDER BY DETAIL_NAME ASC";
        $resultat = DB::select($db_abfrage);
        return $resultat;
    }

    function get_aufgabe_alles($t_id)
    {
        $result = DB::select("SELECT * FROM TODO_LISTE WHERE T_ID='$t_id' && AKTUELL='1' ORDER BY T_ID DESC LIMIT 0,1");
        $row = $result[0];
        $this->t_dat = $row ['T_DAT'];
        $this->t_id = $row ['T_ID'];
        $this->ue_id = $row ['UE_ID'];
        $this->benutzer_typ = $row ['BENUTZER_TYP'];
        $this->benutzer_id = $row ['BENUTZER_ID'];
        $this->verfasser_id = $row ['VERFASSER_ID'];
        $bb = new benutzer ();
        if (empty ($this->benutzer_typ) or ($this->benutzer_typ == 'Person')) {
            $this->benutzer_typ = 'Person';
            $bb->get_benutzer_infos($this->benutzer_id);
            $this->mitarbeiter_name = $bb->benutzername;
        }
        if ($this->benutzer_typ == 'Partner') {
            $pp = new partners ();
            $pp->get_partner_info($this->benutzer_id);
            $this->partner_ans = "$pp->partner_strasse $pp->partner_hausnr, $pp->partner_plz $pp->partner_ort";
            $dd = new detail ();
            $this->partner_fax = $dd->finde_detail_inhalt('Partner', $this->benutzer_id, 'Fax');
            $this->partner_email = $dd->finde_detail_inhalt('Partner', $this->benutzer_id, 'Email');

            $this->mitarbeiter_name = "$pp->partner_name";
        }

        $bb->get_benutzer_infos($this->verfasser_id);
        $this->verfasst_von = $bb->benutzername;

        $this->erledigt = $row ['ERLEDIGT'];
        if ($this->erledigt == '1') {
            $this->erledigt_text = "erledigt";
        } else {
            $this->erledigt_text = "offen";
        }
        $this->anzeigen_ab = date_mysql2german($row ['ANZEIGEN_AB']);
        $this->akut = $row ['AKUT'];
        $this->text = $row ['TEXT'];
        $this->kos_typ = $row ['KOS_TYP'];
        $this->kos_id = $row ['KOS_ID'];
        $r = new rechnung ();
        $this->kos_bez = $r->kostentraeger_ermitteln($this->kos_typ, $this->kos_id);
    }

    function pdf_auftrag($id)
    {
        $this->get_aufgabe_alles($id);

        $partner_id = null;

        try {
            $partner_id = \App\Models\Auftraege::find($id)->von->arbeitgeber()->first()->PARTNER_ID;
        } catch(Exception $e) {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\InfoMessage('Arbeitgeber des Verfassers kann nicht gefunden werden. Briefkopf kann nicht gewählt werden.')
            );
        }

        if ($this->kos_typ == 'Einheit') {
            $kontaktdaten_mieter = $this->kontaktdaten_anzeigen_mieter($this->kos_id);
            $kontaktdaten_mieter = str_replace('<br>', "\n", $kontaktdaten_mieter);
        }

        if ($this->kos_typ == 'Partner') {
            $p = new partners ();
            $p->get_partner_info($this->kos_id);
            $kontaktdaten_mieter = "$p->partner_name\n$p->partner_strasse $p->partner_hausnr\n$p->partner_plz $p->partner_ort\n";
            $det_arr = $this->finde_detail_kontakt_arr('Partner', $this->kos_id);
            if (!empty($det_arr)) {
                $anzd = count($det_arr);
                for ($a = 0; $a < $anzd; $a++) {
                    $dname = $this->html2txt($det_arr [$a] ['DETAIL_NAME']);
                    $dinhalt = $this->html2txt($det_arr [$a] ['DETAIL_INHALT']);
                    $dbemerkung = $this->html2txt($det_arr [$a] ['DETAIL_BEMERKUNG']);
                    $kontaktdaten_mieter .= "\n$dname: $dinhalt";
                    if ($dbemerkung) {
                        $kontaktdaten_mieter .= ", $dbemerkung";
                    }
                    $kontaktdaten_mieter .= "\n";
                }
            }
        }

        if ($this->kos_typ == 'Eigentuemer') {
            $weg = new weg ();
            $weg->get_eigentumer_id_infos2($this->kos_id);
            $kontaktdaten_mieter = "$weg->haus_strasse $weg->haus_nummer\n<b>$weg->haus_plz $weg->haus_stadt</b>\n\n";
            for ($pe = 0; $pe < count($weg->eigentuemer_name); $pe++) {
                $et_p_id = $weg->eigentuemer_name [$pe]['person_id'];
                $det_arr = $this->finde_detail_kontakt_arr('Person', $et_p_id);
                $kontaktdaten_mieter .= rtrim(ltrim($weg->eigentuemer_name [$pe] ['HRFRAU'])) . " ";
                $kontaktdaten_mieter .= rtrim(ltrim($weg->eigentuemer_name [$pe] ['Nachname'])) . " ";
                $kontaktdaten_mieter .= rtrim(ltrim($weg->eigentuemer_name [$pe] ['Vorname'])) . "\n";
                if (!empty($det_arr)) {
                    $anzd = count($det_arr);
                    for ($ad = 0; $ad < $anzd; $ad++) {
                        $dname = $this->html2txt($det_arr [$ad] ['DETAIL_NAME']);
                        $dinhalt = $this->html2txt($det_arr [$ad] ['DETAIL_INHALT']);
                        $dbemerkung = $this->html2txt($det_arr [$ad] ['DETAIL_BEMERKUNG']);
                        $kontaktdaten_mieter .= "$dname: $dinhalt";
                        if ($dbemerkung) {
                            $kontaktdaten_mieter .= ", $dbemerkung";
                        }
                        $kontaktdaten_mieter .= "\n";
                    }
                }
                $kontaktdaten_mieter .= "\n";
            }
        }

        if ($this->kos_typ != 'Partner' && $this->kos_typ != 'Einheit' && $this->kos_typ != 'Eigentuemer') {
            if ($this->kos_typ == 'Haus') {
                $h = new haus ();
                $h->get_haus_info($this->kos_id);
                $kontaktdaten_mieter = "Haus:\n$h->haus_strasse $h->haus_nummer\n<b>$h->haus_plz $h->haus_stadt</b>";
            } else {
                $kontaktdaten_mieter = $this->kos_bez;
            }
        }

        ob_clean(); // ausgabepuffer leeren
        $pdf = new Cezpdf ('a4', 'portrait');
        $bpdf = new b_pdf ();
        $bpdf->b_header($pdf, 'Partner', $partner_id, 'portrait', 'Helvetica.afm', 6);

        $pdf->Rectangle(250, 630, 305, 80);
        $pdf->addText(252, 700, 10, "Arbeitsauftrag Nr: <b>$id</b> an");
        $pdf->addText(252, 685, 9, "<b>$this->benutzer_typ</b>: $this->mitarbeiter_name $this->partner_ans");
        if ($this->benutzer_typ == 'Partner') {
            $pdf->addText(252, 675, 9, "<b>Fax: $this->partner_fax</b>");
            $pdf->addText(375, 675, 9, "<b>Email: $this->partner_email</b>");
        }
        $pdf->addText(252, 665, 8, "<b>Datum</b>: $this->anzeigen_ab");

        if ($this->erledigt == '1') {
            $erledigt = 'JA';
        } else {
            $erledigt = 'NEIN';
        }

        $pdf->addText(252, 655, 8, "<b>AKUT</b>: $this->akut");
        $pdf->addText(252, 645, 8, "<b>Erfasst</b>: $this->verfasst_von");

        if ($this->kos_typ == 'Einheit') {
            $weg = new weg ();
            $weg->get_last_eigentuemer($this->kos_id);
            if (isset ($weg->eigentuemer_id)) {
                $e_id = $weg->eigentuemer_id;
                $weg->get_eigentuemer_namen($e_id);

                /* ################Betreuer################## */
                $anz_p = count($weg->eigentuemer_person_ids);
                $betreuer_str = '';
                $betreuer_arr = [];
                for ($be = 0; $be < $anz_p; $be++) {
                    $et_p_id = $weg->eigentuemer_person_ids [$be];
                    $d_k = new detail ();
                    $dt_arr = $d_k->finde_alle_details_grup('Person', $et_p_id, 'INS-Kundenbetreuer');
                    if (!empty($dt_arr)) {
                        $anz_bet = count($dt_arr);
                        for ($bet = 0; $bet < $anz_bet; $bet++) {
                            $bet_str = $dt_arr [$bet] ['DETAIL_INHALT'];
                            $betreuer_str .= "$bet_str<br>";
                            $betreuer_arr [] = $bet_str;
                        }
                    }
                }

                if (!empty($betreuer_arr)) {
                    $betreuer_str = '';
                    $betreuer_arr1 = array_unique($betreuer_arr);
                    for ($bbb = 0; $bbb < count($betreuer_arr1); $bbb++) {
                        $betreuer_str .= $betreuer_arr1 [$bbb];
                    }
                    $pdf->addText(252, 635, 8, "<b>Erledigt</b>:$erledigt");
                }
            }
        } else {
            $pdf->addText(252, 635, 8, "<b>Erledigt</b>: $erledigt");
        }
        $pdf->ezText($kontaktdaten_mieter);
        if ($pdf->y > 645) {
            $pdf->ezSetY(645);
        }
        $pdf->ezSetDy(-5); // abstand
        $pdf->ezText("<b>Auftragsbeschreibung:</b>", 12);

        $pdf->ezText($this->text);
        $pdf->ezSetDy(-10); // abstand
        if ($this->benutzer_typ == 'Person') {
            $pdf->ezText("<b>Durchgeführte Arbeiten:</b>");
            $pdf->ezText("_________________________________________________________________________");
            $pdf->ezText("_________________________________________________________________________");
            $pdf->ezText("_________________________________________________________________________");
            $pdf->ezText("_________________________________________________________________________");
            $pdf->ezText("_________________________________________________________________________");
            $pdf->ezText("_________________________________________________________________________");
            $pdf->ezText("_________________________________________________________________________");
            $pdf->ezSetDy(-15); // abstand
            $pdf->ezText("<b>Material:</b>");
            $pdf->ezText("_________________________________________________________________________");
            $pdf->ezText("_________________________________________________________________________");
            $pdf->ezText("_________________________________________________________________________");
            $pdf->ezText("_________________________________________________________________________");

            $pdf->ezSetDy(-10); // abstand
            $pdf->Rectangle(50, $pdf->y - 20, 10, 10);
            $pdf->addText(65, $pdf->y - 18, 8, "<b>Arbeit abgeschlossen</b>");
            $pdf->ezSetDy(-15); // abstand
            $pdf->Rectangle(50, $pdf->y - 20, 10, 10);
            $pdf->addText(65, $pdf->y - 18, 8, "<b>Arbeit nicht abgeschlossen</b>");
            $pdf->addText(200, $pdf->y - 18, 8, "<b>Neuer Termin: _______________/____________ Uhr</b>");
            $pdf->ezSetDy(-50); // abstand

            $pdf->Rectangle(50, $pdf->y - 20, 10, 10);
            $pdf->addText(65, $pdf->y - 18, 8, "<b>Fahrzeit:______________ Std:Min</b>");
            $pdf->addText(200, $pdf->y - 18, 8, "<b>Ankunftszeit: _______________ Uhr</b>");
            $pdf->addText(375, $pdf->y - 18, 8, "<b>Fertigstellunsgszeit: _______________ Uhr</b>");
            $pdf->ezSetDy(-100); // abstand
            $pdf->addText(50, $pdf->y - 18, 8, "_______________________");
            $pdf->addText(200, $pdf->y - 18, 8, "_______________________________");
            $pdf->addText(375, $pdf->y - 18, 8, "___________________________________");
            $pdf->ezSetDy(-10); // abstand
            $pdf->addText(90, $pdf->y - 18, 6, "Datum");
            $pdf->addText(240, $pdf->y - 18, 6, "Unterschrift Kunde");
            $pdf->addText(425, $pdf->y - 18, 6, "Unterschrift Monteur");
        }
        if ($this->benutzer_typ == 'Partner') {

            $rr = new rechnung ();
            if ($this->kos_typ == 'Eigentuemer') {
                $rr->get_empfaenger_infos('Objekt', $weg->objekt_id);
            } else {
                $rr->get_empfaenger_infos($this->kos_typ, $this->kos_id);
            }
            $dd = new detail ();
            $rep_eur = $dd->finde_detail_inhalt('Partner', $rr->rechnungs_empfaenger_id, 'Rep-Freigabe');
            $rr->get_empfaenger_info($rr->rechnungs_empfaenger_id);
            $pdf->ezSetDy(-10); // abstand
            if (empty ($rep_eur)) {
                $pdf->ezText("<b>Freigabe bis: ______ € Netto</b>");
            } else {
                $pdf->ezText("<b>Freigabe bis: $rep_eur € Netto</b>");
            }
            $dd = new detail ();
            $b_tel = $dd->finde_detail_inhalt('Partner', $partner_id, 'Telefon');
            $pdf->ezSetDy(-10); // abstand
            $pdf->ezText("<b>Bei Kosten über Freigabesumme bitten wir um Rückmeldung unter $b_tel.</b>");
            $pdf->ezSetDy(-10); // abstand
            $pdf->ezText("Rechnung bitte unter Angabe der <u><b>Auftragsnummer $id</b></u> und <u><b>$this->kos_typ $this->kos_bez</b></u> an:", 10);
            $pdf->ezSetDy(-10); // abstand
            $pdf->ezText("<b>$rr->rechnungs_empfaenger_name\n$rr->rechnungs_empfaenger_strasse  $rr->rechnungs_empfaenger_hausnr\n$rr->rechnungs_empfaenger_plz  $rr->rechnungs_empfaenger_ort</b>", 12);
            $pdf->ezSetDy(-25); // abstand
            $pdf->ezText("Mit freundlichen Grüßen", 10);
            $pdf->ezSetDy(-25); // abstand
            $pdf->ezText("i.A. $this->verfasst_von", 10);
        }

        ob_end_clean();
        $gk_bez = date("Y_m_d") . '_' . substr(str_replace('.', '_', str_replace(',', '', str_replace(' ', '_', ltrim(rtrim($this->kos_bez))))), 0, 30) . '_Auftrag-Nr._' . $id . '.pdf';
        $pdf_opt ['Content-Disposition'] = $gk_bez;

        $pdf->ezStream($pdf_opt);
    }

    function html2txt($document)
    {
        $search = array(
            '@<script[^>]*?>.*?</script>@si', // Strip out javascript
            '@<[\/\!]*?[^<>]*?>@si', // Strip out HTML tags
            '@<style[^>]*?>.*?</style>@siU', // Strip style tags properly
            '@<![\s\S]*?--[ \t\n\r]*>@'
        ) // Strip multi-line comments including CDATA
        ;
        $text = preg_replace($search, '', $document);
        return $text;
    }

    function auftraege_an_haus($haus_id)
    {
        if (request()->filled('einheit_id')) {
            $arr = $this->get_auftraege_einheit('Einheit', request()->input('einheit_id'));
            $e = new einheit ();
            $e->get_einheit_info(request()->input('einheit_id'));
            if (!empty($arr)) {
                echo "<table>";
                echo "<tr><th colspan=\"4\">EINHEIT $e->einheit_kurzname</th></tr>";
                echo "<tr><th>TEXT</th><th>VON/AN</th><th>ERLEDIGT</th><th>DATUM</th></tr>";
                $anz = count($arr);
                for ($a = 0; $a < $anz; $a++) {
                    $t_id = $arr [$a] ['T_ID'];
                    $text = $arr [$a] ['TEXT'];
                    $verfasser_id = $arr [$a] ['VERFASSER_ID'];
                    $bb = new benutzer ();
                    $bb->get_benutzer_infos($verfasser_id);
                    $verfasser_name = $bb->benutzername;
                    $benutzer_typ = $arr [$a] ['BENUTZER_TYP'];
                    $benutzer_id = $arr [$a] ['BENUTZER_ID'];

                    if ($benutzer_typ == 'Person') {
                        $bb->get_benutzer_infos($benutzer_id);
                        $benutzer_name = $bb->benutzername;
                    }
                    if ($benutzer_typ == 'Partner') {
                        $p = new partners ();
                        $p->get_partner_info($benutzer_id);
                        $benutzer_name = "$p->partner_name";
                    }
                    $erledigt = $arr [$a] ['ERLEDIGT'];
                    if ($erledigt == '1') {
                        $erl = 'JA';
                    } else {
                        $erl = 'NEIN';
                    }
                    $erstellt = $arr [$a] ['ERSTELLT'];
                    $link_pdf = "<a href='" . route('web::construction::legacy', ['option' => 'pdf_auftrag', 'proj_id' => $t_id]) . "'><img src=\"images/pdf_dark.png\"></a>";
                    $link_txt = "<a href='" . route('web::construction::legacy', ['option' => 'edit', 't_id' => $t_id]) . "'>$text</a>";

                    echo "<tr><td>$link_txt $link_pdf</td><td>$verfasser_name<br>$benutzer_name</td><td>$erl</td><td>$erstellt</td></tr>";
                }
                echo "</table>";
            }
        }
        if (isset ($arr)) {
            unset ($arr);
        }

        $h = new haus ();
        $h->get_haus_info($haus_id);
        $haus_ids = $this->get_haus_ids($h->haus_strasse, $h->haus_nummer, $h->haus_plz);
        $anz_h = count($haus_ids);
        $arr = Array();
        $obj_arr = array();
        for ($b = 0; $b < $anz_h; $b++) {
            $haus_id = $haus_ids [$b] ['HAUS_ID'];
            $ha = new haus ();
            $ha->get_haus_info($haus_id);
            $obj_arr [] = $ha->objekt_id;
            $tmp_arr = $this->get_auftraege_einheit('Haus', $haus_id);
            if (!empty($tmp_arr)) {
                $arr = array_merge($arr, $tmp_arr);
            }
        }
        if (empty($arr)) {
            fehlermeldung_ausgeben("Keine Aufträge an Haus $h->haus_strasse $h->haus_nummer");
        } else {
            array_unique($obj_arr);
            $anz = count($arr);
            echo "<table>";
            echo "<tr><th colspan=\"4\">HAUS</th></tr>";
            echo "<tr><th>TEXT</th><th>VON/AN</th><th>ERLEDIGT</th><th>DATUM</th></tr>";
            for ($a = 0; $a < $anz; $a++) {
                $t_id = $arr [$a] ['T_ID'];
                $text = $arr [$a] ['TEXT'];
                $verfasser_id = $arr [$a] ['VERFASSER_ID'];
                $bb = new benutzer ();
                $bb->get_benutzer_infos($verfasser_id);
                $verfasser_name = $bb->benutzername;
                $benutzer_typ = $arr [$a] ['BENUTZER_TYP'];
                $benutzer_id = $arr [$a] ['BENUTZER_ID'];

                if ($benutzer_typ == 'Person') {
                    $bb->get_benutzer_infos($benutzer_id);
                    $benutzer_name = $bb->benutzername;
                }
                if ($benutzer_typ == 'Partner') {
                    $p = new partners ();
                    $p->get_partner_info($benutzer_id);
                    $benutzer_name = "$p->partner_name";
                }
                $erledigt = $arr [$a] ['ERLEDIGT'];
                if ($erledigt == '1') {
                    $erl = 'JA';
                } else {
                    $erl = 'NEIN';
                }
                $erstellt = $arr [$a] ['ERSTELLT'];
                $link_pdf = "<a href='" . route('web::construction::legacy', ['option' => 'pdf_auftrag', 'proj_id' => $t_id]) . "'><img src=\"images/pdf_dark.png\"></a>";
                $link_txt = "<a href='" . route('web::construction::legacy', ['option' => 'edit', 't_id' => $t_id]) . "'>$text</a>";
                echo "<tr><td>$link_txt $link_pdf</td><td>$verfasser_name<br>$benutzer_name</td><td>$erl</td><td>$erstellt</td></tr>";
            }
            echo "</table>";
            $anz_o = count($obj_arr);
            $obj_auf = array();
            for ($o = 0; $o < $anz_o; $o++) {
                $objekt_id = $obj_arr [$o];
                $tmp_arr = $this->get_auftraege_einheit('Objekt', $objekt_id);
                if (!empty($tmp_arr)) {
                    $obj_auf = array_merge($obj_auf, $tmp_arr);
                }
            }
            $arr = $obj_auf;
            $anz = count($arr);
            if ($anz > 0) {
                echo "<table>";
                echo "<tr><th colspan=\"4\">OBJEKT</th></tr>";
                echo "<tr><th>TEXT</th><th>VON/AN</th><th>ERLEDIGT</th><th>DATUM</th></tr>";

                for ($a = 0; $a < $anz; $a++) {
                    $t_id = $arr [$a] ['T_ID'];
                    $text = $arr [$a] ['TEXT'];
                    $verfasser_id = $arr [$a] ['VERFASSER_ID'];
                    $bb = new benutzer ();
                    $bb->get_benutzer_infos($verfasser_id);
                    $verfasser_name = $bb->benutzername;
                    $benutzer_typ = $arr [$a] ['BENUTZER_TYP'];
                    $benutzer_id = $arr [$a] ['BENUTZER_ID'];

                    if ($benutzer_typ == 'Person') {
                        $bb->get_benutzer_infos($benutzer_id);
                        $benutzer_name = $bb->benutzername;
                    }
                    if ($benutzer_typ == 'Partner') {
                        $p = new partners ();
                        $p->get_partner_info($benutzer_id);
                        $benutzer_name = "$p->partner_name";
                    }
                    $erledigt = $arr [$a] ['ERLEDIGT'];
                    if ($erledigt == '1') {
                        $erl = 'JA';
                    } else {
                        $erl = 'NEIN';
                    }
                    $erstellt = $arr [$a] ['ERSTELLT'];
                    $link_pdf = "<a href='" . route('web::construction::legacy', ['option' => 'pdf_auftrag', 'proj_id' => $t_id]) . "'><img src=\"images/pdf_dark.png\"></a>";
                    $link_txt = "<a href='" . route('web::construction::legacy', ['option' => 'edit', 't_id' => $t_id]) . "'>$text</a>";
                    echo "<tr><td>$link_txt $link_pdf</td><td>$verfasser_name<br>$benutzer_name</td><td>$erl</td><td>$erstellt</td></tr>";
                }
                echo "</table>";
            }
        }
    }

    function get_auftraege_einheit($kos_typ, $kos_id, $erledigt = '0')
    {
        if ($erledigt == '0') {
            $db_abfrage = "SELECT *, DATE_FORMAT(ERSTELLT, '%d.%m.%Y') AS ERSTELLT_D FROM  `TODO_LISTE` WHERE  ERLEDIGT='$erledigt' &&`KOS_TYP` = '$kos_typ' && KOS_ID='$kos_id'  ORDER BY ERSTELLT DESC";
        }
        if ($erledigt == '1') {
            $db_abfrage = "SELECT *, DATE_FORMAT(ERSTELLT, '%d.%m.%Y') AS ERSTELLT_D FROM  `TODO_LISTE` WHERE  ERLEDIGT='$erledigt' &&`KOS_TYP` = '$kos_typ' && KOS_ID='$kos_id'  ORDER BY ERSTELLT DESC";
        }

        if ($erledigt == 'alle') {
            $db_abfrage = "SELECT *, DATE_FORMAT(ERSTELLT, '%d.%m.%Y') AS ERSTELLT_D FROM  `TODO_LISTE` WHERE  `KOS_TYP` = '$kos_typ' && KOS_ID='$kos_id'  ORDER BY ERSTELLT DESC";
        }

        $result = DB::select($db_abfrage);
        return $result;
    }

    function get_haus_ids($haus_str, $haus_nr, $haus_plz)
    {
        $db_abfrage = "SELECT HAUS_ID FROM HAUS WHERE HAUS_AKTUELL='1' && HAUS_STRASSE='$haus_str' && HAUS_NUMMER='$haus_nr' && HAUS_PLZ='$haus_plz'";
        $resultat = DB::select($db_abfrage);
        if (!empty($resultat)) {
            return $resultat;
        }
    }

    function liste_auftrage_an($typ, $id, $erl = 0)
    {
        $arr = $this->liste_auftrage_an_arr($typ, $id, $erl);
        if (empty($arr)) {
            fehlermeldung_ausgeben("Keine Auftrage an $typ $id");
        } else {
            $anz = count($arr);
            $f = new formular ();
            if ($erl == 0) {
                $f->erstelle_formular("Aufträge OFFEN", null);
            } else {
                $f->erstelle_formular("Aufträge ERLEDIGT", null);
            }

            echo "<table class=\"sortable\">";
            echo "<thead><tr><th>NR</th><th>ERL</th><th>DATUM</th><th>PROJEKT</th><th>VERFASSER</th><th>VERANTWORTLICH</th><th>ZUORDNUNG</th><th>STATUS</th></tr></thead>";
            $z = 0;
            for ($a = 0; $a < $anz; $a++) {
                $z++;
                $t_dat = $arr [$a] ['T_DAT'];
                $t_id = $arr [$a] ['T_ID'];
                $this->get_aufgabe_alles($t_id);
                $u_link_pdf = "<a href='" . route('web::construction::legacy', ['option' => 'pdf_auftrag', 'proj_id' => $t_id]) . "'><img src=\"images/pdf_light.png\"></a>";

                echo "<tr><td>$z.<br>$u_link_pdf</td><td>";
                if ($this->erledigt == '0') {
                    $f->check_box_js('t_dats[]', $t_dat, 'Erledigt', null, null);
                }

                echo "</td><td>$this->anzeigen_ab</td><td><b>Auftragsnr.:$this->text</b></td>";
                echo "<td>$this->verfasst_von</td><td>$this->mitarbeiter_name</td><td>$this->kos_bez</td><td>$this->erledigt_text</td></tr>";
            }
            echo "</table>";
            $f->hidden_feld('option', 'erledigt_alle');
            $f->send_button_js('BTN_alle_erl', 'Markierte als ERLDIGT kennzeichnen!!!', null);
            $f->ende_formular();
        }
    }

    function liste_auftrage_an_arr($typ, $id, $erl = 0)
    {
        $db_abfrage = "SELECT * FROM `TODO_LISTE` WHERE BENUTZER_TYP='$typ' && `BENUTZER_ID` ='$id' AND `AKTUELL` = '1' AND ERLEDIGT='$erl' ORDER BY ERSTELLT DESC";
        $result = DB::select($db_abfrage);
        return $result;
    }
} // end class todo