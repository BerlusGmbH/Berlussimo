<?php

class rechnung
{
    /* Diese Variablen werden von rechnung_grunddaten_holen($rechnung_id) gesetzt */
    var $belegnr;
    var $rechnungsnummer;
    var $aussteller_ausgangs_rnr;
    var $empfaenger_eingangs_rnr;
    var $rechnungstyp;
    var $rechnungsdatum;
    var $eingangsdatum;
    var $faellig_am;
    var $bezahlt_am;
    var $rechnungs_netto;
    var $rechnungs_brutto;
    var $rechnungs_skontobetrag;
    var $rechnungs_aussteller_typ;
    var $rechnungs_aussteller_id;
    var $rechnungs_aussteller_name;
    var $rechnungs_empfaenger_typ;
    var $rechnungs_empfaenger_id;
    var $rechnungs_empfaenger_name;
    var $status_erfasst;
    var $status_vollstaendig;
    var $status_zugewiesen;
    var $status_zahlung_freigegeben;
    var $status_bezahlt;
    var $kurzbeschreibung;
    var $skonto;
    var $empfangs_geld_konto;
    var $rechnungs_aussteller_strasse;
    var $rechnungs_aussteller_hausnr;
    var $rechnungs_aussteller_plz;
    var $rechnungs_aussteller_ort;
    var $rechnungs_empfaenger_strasse;
    var $rechnungs_empfaenger_hausnr;
    var $rechnungs_empfaenger_plz;
    var $rechnungs_empfaenger_ort;
    var $anzahl_positionen;
    public $dat;
    public $beleg_nr;
    public $pos;
    public $menge;
    public $einzel_preis;
    public $g_summe;
    public $mwst_satz;
    public $rabatt_satz;
    public $kostenkonto;
    public $kos_typ;
    public $kos_id;
    public $summe_mwst;
    public $rechnungs_skontoabzug;
    public $rechnungs_kuerzel;
    public $status_bestaetigt;
    public $rechnung_aussteller_partner_id;
    public $rechnung_empfaenger_partner_id;
    public $rechnungs_typ_druck;
    public $artikel_nr;
    public $art_lieferant;
    public $objekt_name;
    public $k_kontierungs_menge;
    public $k_kontenrahmen_konto;
    public $k_kostentraeger_typ;
    public $k_kostentraeger_id;
    public $k_kostentraeger_bez;
    public $k_kostentraeger_anzahl_konten;
    public $rechnungs_mwst;
    public $rechnungsnummer_kuerzel;
    public $summe_mwst_komma;
    public $kostentraeger_id;
    public $kostentraeger_typ;
    public $verwendungs_jahr;
    public $kontierungs_menge;
    public $rechnungs_skontoabzug_a;
    public $rechnungs_brutto_a;
    public $rechnungs_brutto_ausgabe;
    public $rechnungs_skonto_ausgabe;

    function get_kontierung_obj($dat)
    {
        $result = DB::select("SELECT * FROM KONTIERUNG_POSITIONEN WHERE `KONTIERUNG_DAT` ='$dat'");
        if (!empty($result)) {
            $row = $result[0];
            $this->dat = $dat;
            $this->beleg_nr = $row ['BELEG_NR'];
            $this->pos = $row ['POSITION'];
            $this->menge = $row ['MENGE'];
            $this->einzel_preis = $row ['EINZEL_PREIS'];
            $this->g_summe = $row ['GESAMT_SUMME'];
            $this->mwst_satz = $row ['MWST_SATZ'];
            $this->skonto = $row ['SKONTO'];
            $this->rabatt_satz = $row ['RABATT_SATZ'];
            $this->kostenkonto = $row ['KONTENRAHMEN_KONTO'];
            $this->kos_typ = $row ['KOSTENTRAEGER_TYP'];
            $this->kos_id = $row ['KOSTENTRAEGER_ID'];
            $this->rechnung_grunddaten_holen($this->beleg_nr);
        }
    }

    function rechnung_grunddaten_holen($belegnr)
    {
        $result = DB::select("SELECT * FROM RECHNUNGEN WHERE BELEG_NR='$belegnr' && AKTUELL='1' ORDER BY BELEG_NR DESC LIMIT 0,1");
        if (!empty($result)) {
            $row = $result[0];
            $this->belegnr = $row ['BELEG_NR'];
            /* Skontogesamtbetrag updaten */
            $rr = new rechnungen ();
            $rr->update_skontobetrag($belegnr);
            $rr->update_nettobetrag($belegnr);
            $rr->update_bruttobetrag($belegnr);
            $result = DB::select("SELECT * FROM RECHNUNGEN WHERE BELEG_NR='$belegnr' && AKTUELL='1' ORDER BY BELEG_NR DESC LIMIT 0,1");
            $row = $result[0];

            $this->aussteller_ausgangs_rnr = $row ['AUSTELLER_AUSGANGS_RNR'];
            $this->empfaenger_eingangs_rnr = $row ['EMPFAENGER_EINGANGS_RNR'];
            $this->rechnungstyp = $row ['RECHNUNGSTYP'];
            $this->rechnungsdatum = date_mysql2german($row ['RECHNUNGSDATUM']);
            $this->eingangsdatum = date_mysql2german($row ['EINGANGSDATUM']);
            $this->faellig_am = date_mysql2german($row ['FAELLIG_AM']);
            $this->rechnungsnummer = $row ['RECHNUNGSNUMMER'];
            $this->rechnungs_netto = $row ['NETTO'];

            $this->rechnungs_brutto = $row ['BRUTTO'];

            $this->summe_mwst = $this->rechnungs_brutto - $this->rechnungs_netto;
            $this->rechnungs_mwst = $this->summe_mwst;
            $this->rechnungs_skontobetrag = $row ['SKONTOBETRAG'];
            $this->rechnungs_skontoabzug = $this->rechnungs_brutto - $this->rechnungs_skontobetrag;
            $this->rechnungs_aussteller_typ = $row ['AUSSTELLER_TYP'];
            $this->rechnungs_aussteller_id = $row ['AUSSTELLER_ID'];
            $this->rechnungs_empfaenger_typ = $row ['EMPFAENGER_TYP'];
            $this->rechnungs_empfaenger_id = $row ['EMPFAENGER_ID'];

            $this->rechnungs_kuerzel = $this->rechnungs_kuerzel_ermitteln($this->rechnungs_aussteller_typ, $this->rechnungs_aussteller_id, $row ['RECHNUNGSDATUM']);
            $this->rechnungsnummer_kuerzel = $this->rechnungs_kuerzel . $this->aussteller_ausgangs_rnr;
            /* Rechnungspartner finden und Rechnungstyp ändern falls Aussteller = Empfänger */
            $this->rechnungs_partner_ermitteln();

            $this->status_erfasst = $row ['STATUS_ERFASST'];
            $this->status_vollstaendig = $row ['STATUS_VOLLSTAENDIG'];
            $this->status_zugewiesen = $row ['STATUS_ZUGEWIESEN'];
            $this->kurzbeschreibung = $row ['KURZBESCHREIBUNG'];
            $this->status_bezahlt = $row ['STATUS_BEZAHLT'];
            $this->status_zahlung_freigegeben = $row ['STATUS_ZAHLUNG_FREIGEGEBEN'];
            $this->status_bestaetigt = $row ['STATUS_BESTAETIGT'];
            $this->bezahlt_am = date_mysql2german($row ['BEZAHLT_AM']);
            $this->empfangs_geld_konto = $row ['EMPFANGS_GELD_KONTO'];
        }
    }

    function rechnungs_kuerzel_ermitteln($austeller_typ, $aussteller_id, $datum)
    {
        $result = DB::select("SELECT KUERZEL FROM RECHNUNG_KUERZEL WHERE AKTUELL = '1' && AUSSTELLER_TYP = '$austeller_typ' && AUSSTELLER_ID = '$aussteller_id' && ( VON <= '$datum' OR BIS >= '$datum' ) ORDER BY RK_DAT DESC LIMIT 0,1");
        $row = $result[0];
        return $row ['KUERZEL'];
    }

    function rechnungs_partner_ermitteln()
    {
        if ($this->rechnungs_aussteller_typ == 'Partner') {

            /* Partnernamen holen */
            $this->rechnungs_aussteller_name = $this->get_partner_name($this->rechnungs_aussteller_id);
            /* Anschriften holen */
            $this->get_aussteller_info($this->rechnungs_aussteller_id);
            $this->rechnung_aussteller_partner_id = $this->rechnungs_aussteller_id;
        }

        if ($this->rechnungs_empfaenger_typ == 'Partner') {
            $this->rechnungs_empfaenger_name = $this->get_partner_name($this->rechnungs_empfaenger_id);
            /* Anschriften holen */
            $this->get_empfaenger_info($this->rechnungs_empfaenger_id);
            /* Ende Partnernamen holen */
            $this->rechnung_empfaenger_partner_id = $this->rechnungs_empfaenger_id;
        }

        if ($this->rechnungs_empfaenger_typ == 'Eigentuemer') {
            $weg = new weg ();
            $weg->get_eigentumer_id_infos3($this->rechnungs_empfaenger_id);
            $this->rechnungs_empfaenger_name = $weg->post_anschrift;
            /* Anschriften holen */
            // $this->get_empfaenger_info($this->rechnungs_empfaenger_id);
            /* Ende Partnernamen holen */
            // $this->rechnung_empfaenger_partner_id = $this->rechnungs_empfaenger_id;
        }

        if ($this->rechnungs_aussteller_typ == 'Kasse') {
            /* Kassennamen holen */
            $kassen_info = new kasse ();
            $kassen_info->get_kassen_info($this->rechnungs_aussteller_id);
            $this->rechnungs_aussteller_name = "" . $kassen_info->kassen_name . "<br><br>" . $kassen_info->kassen_verwalter . "";
            /* Kassen Partner finden */
            $this->rechnung_aussteller_partner_id = $kassen_info->kassen_partner_id;
        }

        if ($this->rechnungs_empfaenger_typ == 'Kasse') {
            /* Kassennamen holen */
            $kassen_info = new kasse ();
            $kassen_info->get_kassen_info($this->rechnungs_empfaenger_id);
            $this->rechnungs_empfaenger_name = "" . $kassen_info->kassen_name . "<br><br>" . $kassen_info->kassen_verwalter . "";
            /* Kassen Partner finden */
            $this->rechnung_empfaenger_partner_id = $kassen_info->kassen_partner_id;
        }

        if ($this->rechnungs_aussteller_typ == 'Lager') {
            $lager_info = new lager ();
            // $this->rechnungs_aussteller_name = $lager_info->lager_bezeichnung($this->rechnungs_aussteller_id);
            /*
			 * Liefert Lagernamen und Partner id
			 * $lager_info->lager_name
			 * $lager_info->lager_partner_id
			 */
            $lager_info->lager_name_partner($this->rechnungs_aussteller_id);
            /* Partnernamen holen */
            $this->rechnungs_aussteller_name = 'Lager ' . $this->get_partner_name($lager_info->lager_partner_id);
            /* Anschriften holen */
            $this->get_aussteller_info($lager_info->lager_partner_id);
            $this->rechnung_aussteller_partner_id = $lager_info->lager_partner_id;
        }

        if ($this->rechnungs_empfaenger_typ == 'Lager') {
            $lager_info1 = new lager ();
            // $this->rechnungs_empfaenger_name = $lager_info->lager_bezeichnung($this->rechnungs_empfaenger_id); //alt
            /*
			 * Liefert Lagernamen und Partner id
			 * $lager_info->lager_name
			 * $lager_info->lager_partner_id
			 */
            $lager_info1->lager_name_partner($this->rechnungs_empfaenger_id);
            /* Partnernamen finden */
            $this->rechnungs_empfaenger_name = 'Lager ' . $this->get_partner_name($lager_info1->lager_partner_id);
            /* Anschriften holen */
            $this->get_empfaenger_info($lager_info1->lager_partner_id);

            $this->rechnung_empfaenger_partner_id = $lager_info1->lager_partner_id;
        }

        if ($this->rechnung_empfaenger_partner_id === $this->rechnung_aussteller_partner_id) {
            $this->rechnungs_typ_druck = 'BUCHUNGSBELEG';
        } else {
            // $this->rechnungs_typ_druck = 'RECHNUNG';
            $this->rechnungs_typ_druck = $this->rechnungstyp;
        }
    }

    /* Alle Rechnungen werden angezeigt */

    function get_partner_name($partner_id)
    {
        $result = DB::select("SELECT PARTNER_NAME FROM PARTNER_LIEFERANT WHERE PARTNER_ID='$partner_id' && AKTUELL = '1'");
        $row = $result[0];
        return $row ['PARTNER_NAME'];
    }

    function get_aussteller_info($partner_id)
    {
        $result = DB::select("SELECT PARTNER_NAME, STRASSE, NUMMER, PLZ, ORT FROM PARTNER_LIEFERANT WHERE PARTNER_ID='$partner_id' && AKTUELL = '1'");
        $row = $result[0];
        $this->rechnungs_aussteller_name = $row ['PARTNER_NAME'];
        $this->rechnungs_aussteller_strasse = $row ['STRASSE'];
        $this->rechnungs_aussteller_hausnr = $row ['NUMMER'];
        $this->rechnungs_aussteller_plz = $row ['PLZ'];
        $this->rechnungs_aussteller_ort = $row ['ORT'];
    }

    function get_empfaenger_info($partner_id)
    {
        $result = DB::select("SELECT PARTNER_NAME, STRASSE, NUMMER, PLZ, ORT FROM PARTNER_LIEFERANT WHERE PARTNER_ID='$partner_id' && AKTUELL = '1'");
        $row = $result[0];
        $this->rechnungs_empfaenger_name = $row ['PARTNER_NAME'];
        $this->rechnungs_empfaenger_strasse = $row ['STRASSE'];
        $this->rechnungs_empfaenger_hausnr = $row ['NUMMER'];
        $this->rechnungs_empfaenger_plz = $row ['PLZ'];
        $this->rechnungs_empfaenger_ort = $row ['ORT'];
    }

    /* Alle vollständig erfasste d.h. mit Positionen erfasste Rechungen die auch vollständig kontiert worden sind */

    function form_rechnung_erfassen()
    {
        $form = new mietkonto ();
        $formular = new formular ();
        $partner = new partner ();
        $form->erstelle_formular("Bargeldlose Rechnung erfassen", NULL);
        $form->hidden_feld("aussteller_typ", "Partner");
        $partner->partner_dropdown('Rechnung ausgestellt von', 'aussteller_id', 'aussteller');
        $form->hidden_feld("empfaenger_typ", "Partner");
        $pp = new partners ();
        $pp->partner_dropdown('Rechnung ausgestellt an', 'empfaenger_id', 'empfaenger', session()->get('partner_id'));

        $datum_feld = 'document.getElementById("eingangsdatum").value';
        $js_datum = "onchange='check_datum($datum_feld)'";
        $formular->text_feld('Eingangsdatum:', 'eingangsdatum', '', '10', 'eingangsdatum', $js_datum);
        $form->text_feld("Rechnungsnummer:", "rechnungsnummer", "", "10");
        $form->hidden_feld("rechnungstyp", "Rechnung");
        $datum_feld1 = 'document.getElementById("rechnungsdatum").value';
        $js_datum = "onchange='check_datum($datum_feld1)'";
        $formular->text_feld('Rechnungsdatum:', 'rechnungsdatum', '', '10', 'rechnungsdatum', $js_datum);
        $form->hidden_feld("nettobetrag", "0,00");
        $form->hidden_feld("bruttobetrag", "0,00");
        $form->hidden_feld("skontobetrag", "0,00");
        $form->text_feld("Fällig am", "faellig_am", '', "10");
        $form->text_bereich("Kurzbeschreibung", "kurzbeschreibung", "", "50", "10");
        $form->send_button("submit_rechnung1", "Rechnung speichern");
        $form->hidden_feld("option", "rechnung_erfassen1");
        $form->ende_formular();
    }

    /* Alle erfassten Rechungen die noch nicht vollständig kontiert worden sind */
    /* Rechnungen die Positionen haben aber/und Rechnungen deren Kaufmenge <> Kontierungsmenge */

    function form_gutschrift_erfassen()
    {
        $form = new mietkonto ();
        $partner = new partner ();
        $formular = new formular ();
        $form->erstelle_formular("Bargeldlose Rechnung erfassen", NULL);
        $partner->partner_dropdown('Rechnung ausgestellt von', 'Aussteller', 'aussteller');
        $partner->partner_dropdown('Rechnung ausgestellt an', 'Empfaenger', 'empfaenger');
        $datum_heute = date("d.m.Y");
        $form->text_feld("Eingangsdatum:", "eingangsdatum", $datum_heute, "10");
        $form->text_feld("Rechnungsnummer:", "rechnungsnummer", "", "10");
        $form->hidden_feld("rechnungstyp", "Gutschrift");
        $form->text_feld("Rechnungsdatum:", "rechnungsdatum", $datum_heute, "10");
        $form->text_feld("Nettobetrag:", "nettobetrag", "0,00", "10");
        $formular->text_feld("Bruttobetrag:", "bruttobetrag", '', '10', 'bruttobetrag', 'onchange="skonto_berechnen()"');
        $form->text_feld("Betrag nach Abzug von Skonto:", "skontobetrag", "", "10");
        $formular->text_feld("Skonto in %:", "skonto", '3', '10', 'skonto', 'onchange="skonto_berechnen()"');
        $form->text_feld("Fällig am", "faellig_am", $datum_heute, "10");
        $form->text_bereich("Kurzbeschreibung", "kurzbeschreibung", "", "50", "10");
        $form->send_button("submit_rechnung1", "Rechnung speichern");
        $form->hidden_feld("option", "rechnung_erfassen1");
        $form->ende_formular();
    }

    function form_rechnung_erfassen_an_kasse()
    {
        $form = new mietkonto ();
        $formular = new formular ();
        $partner = new partner ();
        $kasse_info = new kasse ();
        $form->erstelle_formular("Kasse -> Ausgaben erfassen", NULL);
        echo "<br>\n";
        $form->hidden_feld("aussteller_typ", "Partner");
        $partner->partner_dropdown('Rechnung ausgestellt von', 'aussteller_id', 'aussteller');
        $form->hidden_feld("empfaenger_typ", "Kasse");
        $kasse_info->dropdown_kassen('Kasse als Empfänger', 'empfaenger_id', 'empfaenger');
        $form->text_feld("Eingangsdatum:", "eingangsdatum", '', "10");
        $form->text_feld("Rechnungsnummer:", "rechnungsnummer", "", "10");
        $form->hidden_feld("rechnungstyp", "Rechnung");
        $form->text_feld("Rechnungsdatum:", "rechnungsdatum", '', "10");
        $form->text_feld("Nettobetrag:", "nettobetrag", "", "10");
        $formular->text_feld("Bruttobetrag:", "bruttobetrag", '', '10', 'bruttobetrag', 'onchange="skonto_berechnen()"');
        $form->text_feld("Betrag nach Abzug von Skonto:", "skontobetrag", "", "10");
        $formular->text_feld("Skonto in %:", "skonto", '', '10', 'skonto', 'onchange="skonto_berechnen()"');
        $form->text_feld("Fällig am", "faellig_am", '', "10");
        $form->text_bereich("Kurzbeschreibung", "kurzbeschreibung", "", "50", "10");
        $form->send_button("submit_rechnung1", "Rechnung speichern");
        $form->hidden_feld("option", "rechnung_erfassen1");
        $form->ende_formular();
    }

    function erfasste_rechungen_anzeigen()
    {
        /* Zählen aller Zeilen */
        $result = DB::selectOne("SELECT count(*) FROM RECHNUNGEN WHERE AKTUELL = '1' ORDER BY BELEG_NR DESC");
        $numrows1 = $result['count(*)'];
        /* Seitennavigation mit Limit erstellen */
        echo "<table><tr><td>Anzahl aller Rechnungen: $numrows1</td></tr><tr><td>\n";
        $navi = new blaettern (0, $numrows1, 100, route('web::rechnungen::legacy', ['option' => 'erfasste_rechnungen'], false));
        echo "</td></tr></table>\n";
        $my_array = DB::select("SELECT * FROM RECHNUNGEN WHERE AKTUELL = '1'  ORDER BY BELEG_NR DESC " . $navi->limit);
        if (!empty($my_array)) {
            echo "<table class=sortable>\n";
            echo "<tr><th>Erfassungsnr</th><th>TYP</th><th>Rech.Nr</th><th>Fälig</th><th>Von</th><th>An</th><th>Netto</th><th>Brutto</th><th>Skonto</th></tr>\n";
            $numrows = count($my_array);
            for ($a = 0; $a < $numrows; $a++) {
                $belegnr = $my_array [$a] ['BELEG_NR'];
                $this->rechnung_grunddaten_holen($belegnr);

                $faellig_am = date_mysql2german($my_array [$a] ['FAELLIG_AM']);

                $beleg_link = "<a href='" . route('web::rechnungen::legacy', ['option' => 'rechnungs_uebersicht', 'belegnr' => $belegnr]) . "'>Ansehen</a>";
                $pdf_link = "<a href='" . route('web::rechnungen::legacy', ['option' => 'anzeigen_pdf', 'belegnr' => $belegnr]) . "'><img src=\"images/pdf_light.png\"></a>";
                $pdf_link1 = "<a href='" . route('web::rechnungen::legacy', ['option' => 'anzeigen_pdf', 'belegnr' => $belegnr, 'no_logo']) . "'><img src=\"images/pdf_dark.png\"></a>";

                $netto = nummer_punkt2komma($my_array [$a] ['NETTO']);
                $brutto = nummer_punkt2komma($my_array [$a] ['BRUTTO']);
                $skonto_betrag = nummer_punkt2komma($my_array [$a] ['SKONTOBETRAG']);
                $rechnungsnummer = $my_array [$a] ['RECHNUNGSNUMMER'];
                $rechnungstyp = $my_array [$a] ['RECHNUNGSTYP'];
                echo "<tr><td>$beleg_link $pdf_link $pdf_link1</td><td>$rechnungstyp</td><td>$rechnungsnummer</td><td><b>$faellig_am</b></td><td>" . $this->rechnungs_aussteller_name . "</td><td>" . $this->rechnungs_empfaenger_name . "</td><td align=right>$netto €</td><td align=right>$brutto €</td><td align=right>$skonto_betrag €</td></tr>\n";
            }

            echo "</table>\n";
        }
    }

    function vollstaendig_erfasste_rechungen_anzeigen()
    {
        /* Zählen aller Zeilen */
        $result = DB::select("SELECT RECHNUNGEN. * , COUNT( RECHNUNGEN_POSITIONEN.POSITION ) AS ANZAHL_POSITIONEN
FROM RECHNUNGEN, RECHNUNGEN_POSITIONEN
WHERE RECHNUNGEN.BELEG_NR = RECHNUNGEN_POSITIONEN.BELEG_NR && RECHNUNGEN.AKTUELL='1' && RECHNUNGEN_POSITIONEN.AKTUELL='1' GROUP BY RECHNUNGEN.BELEG_NR ASC");
        $numrows = count($result);
        if ($numrows > 0) {
            /* Seitennavigation mit Limit erstellen */
            echo "<table><tr><td>Anzahl vollständige Rechnungen: $numrows</td></tr><tr><td>\n";
            $navi = new blaettern (0, $numrows, 10, route('web::rechnungen::legacy', ['option' => 'vollstaendige_rechnungen'], false));
            echo "</td><tr></table>\n";
            $my_array = DB::select("SELECT RECHNUNGEN. * , COUNT( RECHNUNGEN_POSITIONEN.POSITION ) AS ANZAHL_POSITIONEN
FROM RECHNUNGEN, RECHNUNGEN_POSITIONEN
WHERE RECHNUNGEN.BELEG_NR = RECHNUNGEN_POSITIONEN.BELEG_NR && RECHNUNGEN.AKTUELL='1' && RECHNUNGEN_POSITIONEN.AKTUELL='1' AND RECHNUNGSTYP='Rechnung' OR RECHNUNGSTYP='Gutschrift'
GROUP BY RECHNUNGEN.BELEG_NR ORDER BY BELEG_NR DESC $navi->limit");
            echo "<table class=rechnungen>\n";
            echo "<tr class=feldernamen><td>BNr</td><td>R-Datum</td><td>E-Datum</td><td>Fällig</td><td>Von</td><td>An</td><td width=80>Netto</td><td width=80>Brutto</td><td width=80>Skonto</td></tr>\n";

            $numrows = count($my_array);
            for ($a = 0; $a < $numrows; $a++) {
                $belegnr = $my_array [$a] ['BELEG_NR'];
                $this->rechnung_grunddaten_holen($belegnr);

                $faellig_am = date_mysql2german($my_array [$a] ['FAELLIG_AM']);
                $beleg_link = "<a href='" . route('web::rechnungen::legacy', ['option' => 'rechnungs_uebersicht', 'belegnr' => $my_array [$a] ['BELEG_NR']]) . "'>" . $my_array [$a] ['BELEG_NR'] . "</>\n";
                $netto = nummer_punkt2komma($my_array [$a] ['NETTO']);
                $brutto = nummer_punkt2komma($my_array [$a] ['BRUTTO']);
                $skonto_betrag = nummer_punkt2komma($my_array [$a] ['SKONTOBETRAG']);
                $rechnungsnummer = $my_array [$a] ['RECHNUNGSNUMMER'];
                $rechnungstyp = $my_array [$a] ['RECHNUNGSTYP'];
                echo "<tr><td>$beleg_link</td><td>$rechnungstyp</td><td>$rechnungsnummer</td><td><b>$faellig_am</b></td><td>" . $this->rechnungs_aussteller_name . "</td><td>" . $this->rechnungs_empfaenger_name . "</td><td align=right>$netto €</td><td align=right>$brutto €</td><td align=right>$skonto_betrag €</td></tr>\n";
            }
        }

        echo "</table>\n";
    }

    function unvollstaendig_erfasste_rechungen_anzeigen()
    {
        /* Zählen aller Zeilen */
        $result = DB::select(" SELECT BELEG_NR FROM RECHNUNGEN WHERE BELEG_NR NOT IN (  SELECT BELEG_NR FROM RECHNUNGEN_POSITIONEN WHERE AKTUELL='1') && AKTUELL='1'");

        $numrows1 = count($result);
        echo "<table><tr><td>Anzahl aller unvollständig erfassten Rechnungen: $numrows1</td></tr><tr><td>\n";
        /* Seitennavigation mit Limit erstellen */
        $navi = new blaettern (0, $numrows1, 10, route('web::rechnungen::legacy', ['option' => 'unvollstaendige_rechnungen']));
        echo "</td></tr></table>\n";
        $my_array = DB::select(" SELECT * FROM RECHNUNGEN WHERE BELEG_NR NOT IN (  SELECT BELEG_NR FROM RECHNUNGEN_POSITIONEN WHERE AKTUELL='1') && AKTUELL='1' ORDER BY BELEG_NR DESC  $navi->limit");
        $numrows = count($result);
        if ($numrows > 0) {
            echo "<table class=rechnungen>\n";
            echo "<tr class=feldernamen><td>BNr</td><td>R-Datum</td><td>E-Datum</td><td>Fällig</td><td>Von</td><td>An</td><td width=80>Netto</td><td width=80>Brutto</td><td width=80>Skonto</td></tr>\n";

            for ($a = 0; $a < $numrows; $a++) {
                $belegnr = $my_array [$a] ['BELEG_NR'];
                $this->rechnung_grunddaten_holen($belegnr);
                $e_datum = date_mysql2german($my_array [$a] ['EINGANGSDATUM']);
                $r_datum = date_mysql2german($my_array [$a] ['RECHNUNGSDATUM']);
                $faellig_am = date_mysql2german($my_array [$a] ['FAELLIG_AM']);
                $beleg_link = "<a href='" . route('web::rechnungen::legacy', ['option' => 'rechnungs_uebersicht', 'belegnr' => $my_array [$a] ['BELEG_NR']]) . "'>" . $my_array [$a] ['BELEG_NR'] . "</>\n";
                $netto = nummer_punkt2komma($my_array [$a] ['NETTO']);
                $brutto = nummer_punkt2komma($my_array [$a] ['BRUTTO']);
                $skonto_betrag = nummer_punkt2komma($my_array [$a] ['SKONTOBETRAG']);
                echo "<tr><td>$beleg_link</td><td>$r_datum</td><td>$e_datum</td><td><b>$faellig_am</b></td><td>" . $this->rechnungs_aussteller_name . "</td><td>" . $this->rechnungs_empfaenger_name . "</td><td align=right>$netto €</td><td align=right>$brutto €</td><td align=right>$skonto_betrag €</td></tr>\n";
            }
            echo "</table>\n";
        }
    }

    function vollstaendig_kontierte_rechungen_anzeigen()
    {
        /* Zählen aller Zeilen */
        $rechnungen_mit_positionen = DB::select("SELECT RECHNUNGEN. * , COUNT( RECHNUNGEN_POSITIONEN.POSITION ) AS ANZAHL_POSITIONEN
FROM RECHNUNGEN, RECHNUNGEN_POSITIONEN
WHERE RECHNUNGEN.BELEG_NR = RECHNUNGEN_POSITIONEN.BELEG_NR && RECHNUNGEN.AKTUELL='1' && RECHNUNGEN_POSITIONEN.AKTUELL='1' GROUP BY RECHNUNGEN.BELEG_NR ASC");
        $numrows = count($rechnungen_mit_positionen);
        for ($vv = 0; $vv < $numrows; $vv++) {
            $status_kontierung = $this->rechnung_auf_kontierung_pruefen($rechnungen_mit_positionen [$vv] ['BELEG_NR']);
            if ($status_kontierung == 'vollstaendig') {
                $kontierte_belege [] = $rechnungen_mit_positionen [$vv];
            }
        }

        $my_array = $kontierte_belege;
        echo "<table class=rechnungen>\n";
        echo "<tr class=feldernamen><td>BNr</td><td>R-Datum</td><td>E-Datum</td><td>Fällig</td><td>Von</td><td>An</td><td width=80>Netto</td><td width=80>Brutto</td><td width=80>Skonto</td></tr>\n";

        $numrows = count($my_array);
        for ($a = 0; $a < $numrows; $a++) {
            $belegnr = $my_array [$a] ['BELEG_NR'];
            $this->rechnung_grunddaten_holen($belegnr);

            $e_datum = date_mysql2german($my_array [$a] ['EINGANGSDATUM']);
            $r_datum = date_mysql2german($my_array [$a] ['RECHNUNGSDATUM']);
            $faellig_am = date_mysql2german($my_array [$a] ['FAELLIG_AM']);
            $beleg_link = "<a href='" . route('web::rechnungen::legacy', ['option' => 'rechnung_kontieren', 'belegnr' => $my_array [$a] ['BELEG_NR']]) . "'>" . $my_array [$a] ['BELEG_NR'] . "</>\n";
            $netto = nummer_punkt2komma($my_array [$a] ['NETTO']);
            $brutto = nummer_punkt2komma($my_array [$a] ['BRUTTO']);
            $skonto_betrag = nummer_punkt2komma($my_array [$a] ['SKONTOBETRAG']);
            echo "<tr><td>$beleg_link</td><td>$r_datum</td><td>$e_datum</td><td><b>$faellig_am</b></td><td>" . $this->rechnungs_aussteller_name . "</td><td>" . $this->rechnungs_empfaenger_name . "</td><td align=right>$netto €</td><td align=right>$brutto €</td><td align=right>$skonto_betrag €</td></tr>\n";
        }
        echo "</table>\n";
    }

    function rechnung_auf_kontierung_pruefen($belegnr)
    {
        $erfasste_menge = $this->erfasste_menge($belegnr);
        $kontierte_menge = $this->kontierte_menge($belegnr);

        if (empty ($kontierte_menge) or empty ($erfasste_menge)) {
            return 'unvollstaendig';
        }
        if ($kontierte_menge == $erfasste_menge) {
            return 'vollstaendig';
        }
        if ($kontierte_menge < $erfasste_menge) {
            return 'unvollstaendig';
        }
        if ($kontierte_menge > $erfasste_menge) {
            return 'falsch';
        }
    }

    function erfasste_menge($belegnr)
    {
        $result = DB::select(" SELECT SUM( MENGE ) AS ERFASSTE_MENGE FROM `RECHNUNGEN_POSITIONEN` WHERE BELEG_NR = '$belegnr' && AKTUELL='1' ");
        if (!empty($result)) {
            $row = $result[0];
            return $row ['ERFASSTE_MENGE'];
        } else {
            return 0;
        }
    }

    function kontierte_menge($belegnr)
    {
        $result = DB::select(" SELECT SUM( MENGE ) AS KONTIERTE_MENGE FROM `KONTIERUNG_POSITIONEN` WHERE BELEG_NR = '$belegnr' && AKTUELL='1'");
        if (!empty($result)) {
            $row = $result[0];
            return $row ['KONTIERTE_MENGE'];
        } else {
            return 0;
        }
    }

    function unvollstaendig_kontierte_rechungen_anzeigen()
    {
        /* Zählen aller Zeilen */
        $rechnungen_mit_positionen = DB::select("SELECT * FROM RECHNUNGEN WHERE AKTUELL='1' ORDER BY BELEG_NR ASC");

        $numrows = count($rechnungen_mit_positionen);
        for ($vv = 0; $vv < $numrows; $vv++) {
            $status_kontierung = $this->rechnung_auf_kontierung_pruefen($rechnungen_mit_positionen [$vv] ['BELEG_NR']);
            if ($status_kontierung == 'unvollstaendig') {
                $unkontierte_belege [] = $rechnungen_mit_positionen [$vv];
            }
        }

        $my_array = $unkontierte_belege;
        $numrows = count($my_array);
        echo "<table class=rechnungen>\n";
        echo "<tr class=feldernamen><td>BNr</td><td>R-Datum</td><td>E-Datum</td><td>Fällig</td><td>Von</td><td>An</td><td width=80>Netto</td><td width=80>Brutto</td><td width=80>Skonto</td></tr>\n";

        for ($a = 0; $a < $numrows; $a++) {
            $belegnr = $my_array [$a] ['BELEG_NR'];
            $this->rechnung_grunddaten_holen($belegnr);
            $e_datum = date_mysql2german($my_array [$a] ['EINGANGSDATUM']);
            $r_datum = date_mysql2german($my_array [$a] ['RECHNUNGSDATUM']);
            $faellig_am = date_mysql2german($my_array [$a] ['FAELLIG_AM']);
            $beleg_link = "<a href='" . route('web::rechnungen::legacy', ['option' => 'rechnung_kontieren', 'belegnr' => $my_array [$a] ['BELEG_NR']]) . "'>" . $my_array [$a] ['BELEG_NR'] . "</>\n";
            $netto = nummer_punkt2komma($my_array [$a] ['NETTO']);
            $brutto = nummer_punkt2komma($my_array [$a] ['BRUTTO']);
            $skonto_betrag = nummer_punkt2komma($my_array [$a] ['SKONTOBETRAG']);
            echo "<tr><td>$beleg_link</td><td>$r_datum</td><td>$e_datum</td><td><b>$faellig_am</b></td><td>" . $this->rechnungs_aussteller_name . "</td><td align=right>$netto €</td><td align=right>$brutto €</td><td align=right>$skonto_betrag €</td></tr>\n";
        }
        echo "</table>\n";
    }

    /* Authoreninformationen d.h. Ersteller der neuen Rechnung aus Pool */

    function rechnung_kontierung_aufheben($belegnr)
    {
        $success = DB::update("UPDATE `KONTIERUNG_POSITIONEN` SET AKTUELL='0' WHERE `BELEG_NR` = '$belegnr'");
        if ($success == 0) {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\ErrorMessage('Kontierungsaufhebung nicht möglich.')
            );
        }
        return true;
    }

    function positions_pool_anzeigen()
    {
        // #########OBJEKTE###################
        /* Ein Array mit Objekten erstellen, dieser wird nachher mit Unterarrays gefällt */
        $objekte = new objekt ();
        $objekte_arr = $objekte->liste_aller_objekte_kurz();
        /* Aus dem Kontierungspool werden alle Positionen aller Objekte in ein Array geschoben */
        $positionen_arr = $this->pool_durchsuchen('Objekt');

        /* Array mit Objektpositionen durchlaufen und die Objekt_id als KOSTENTRAEGER_ID finden */
        for ($a = 0; $a < count($positionen_arr); $a++) {
            $kostentraeger_id = $positionen_arr [$a] ['KOSTENTRAEGER_ID'];
            /* Mit der $kostentrager_id wird der Objektarraydurchlaufen und beim Treffer, die Position dem Unterarray von Objekte zugewiesen */
            for ($i = 0; $i < count($objekte_arr); $i++) {
                if (in_array($kostentraeger_id, $objekte_arr [$i])) {
                    // echo "vorhanden $i<br>";
                    $objekte_arr [$i] ['OBJEKT_KOSTEN'] [] = $positionen_arr [$a];
                } // end if
            } // end for 2
        } // end for 1
        // ################HÄUSER######################
        /* Aus dem Kontierungspool werden alle Positionen aller Häuser in ein Array geschoben */
        $positionen_arr = $this->pool_durchsuchen('Haus');

        /* Array mit Objektpositionen durchlaufen und die Objekt_id als KOSTENTRAEGER_ID finden */
        for ($a = 0; $a < count($positionen_arr); $a++) {
            // echo $positionen_arr[$a]['KOSTENTRAEGER_ID']."<br>";
            $kostentraeger_id = $positionen_arr [$a] ['KOSTENTRAEGER_ID'];
            $haus_info = new haus ();
            $haus_info->get_haus_info($kostentraeger_id);
            $kostentraeger_id = $haus_info->objekt_id;
            /* Mit der $kostentrager_id wird der Objektarraydurchlaufen und beim Treffer, die Position dem Unterarray von Objekte zugewiesen */
            for ($i = 0; $i < count($objekte_arr); $i++) {
                if (in_array($kostentraeger_id, $objekte_arr [$i])) {
                    $objekte_arr [$i] ['HAUS_KOSTEN'] [] = $positionen_arr [$a];
                } // end if
            } // end for 2
        } // end for 1
        // ############EINHEITEN###########################
        /* Aus dem Kontierungspool werden alle Positionen aller Einheiten in ein Array geschoben */
        $positionen_arr = $this->pool_durchsuchen('Einheit');

        /* Array mit Objektpositionen durchlaufen und die Objekt_id als KOSTENTRAEGER_ID finden */
        for ($a = 0; $a < count($positionen_arr); $a++) {
            // echo $positionen_arr[$a]['KOSTENTRAEGER_ID']."<br>";
            $kostentraeger_id = $positionen_arr [$a] ['KOSTENTRAEGER_ID'];
            $einheit_info = new einheit ();
            $einheit_info->get_einheit_haus($kostentraeger_id);
            $kostentraeger_id = $einheit_info->objekt_id;
            /* Mit der $kostentrager_id wird der Objektarraydurchlaufen und beim Treffer, die Position dem Unterarray von Objekte zugewiesen */
            for ($i = 0; $i < count($objekte_arr); $i++) {
                if (in_array($kostentraeger_id, $objekte_arr [$i])) {
                    // echo "vorhanden $i<br>";
                    $objekte_arr [$i] ['EINHEIT_KOSTEN'] [] = $positionen_arr [$a];
                } // end if
            } // end for 2
        } // end for 1

        // echo "<pre>";
        // print_r($positionen_arr);
        // echo "</pre>";

        /*
		 * echo "<hr><pre>";
		 * print_r($objekte_arr);
		 * echo "</pre>";
		 */
        return $objekte_arr;
    }

    function pool_durchsuchen($kostentraeger_typ)
    {
        $result = DB::select("SELECT KONTIERUNG_DAT, KONTIERUNG_ID, KONTENRAHMEN_KONTO, KOSTENTRAEGER_TYP, KOSTENTRAEGER_ID FROM `KONTIERUNG_POSITIONEN` WHERE KOSTENTRAEGER_TYP = '$kostentraeger_typ' && AKTUELL='1' && WEITER_VERWENDEN='1' ORDER BY KOSTENTRAEGER_TYP, KOSTENTRAEGER_ID ASC");
        return $result;
    }

    /* Funkt. zur Auswahl der Positionen für eine neue Rechnung aus dem Pool */

    function rechnung_an_objekt_zusammenstellen($objekt_id)
    {
        /* Positionen der objektbezogenen Kosten */
        $objekt_rechnung_arr = $this->rechnung_aus_pool_zusammenstellen('Objekt', $objekt_id);
        /* Alle hausbezogenen Kosten */
        $haeuser_im_pool = $this->pool_durchsuchen('Haus');
        $haus_info = new haus ();
        /* Alle einheitsbezogenen Kosten */
        $einheiten_im_pool = $this->pool_durchsuchen('Einheit');
        $einheit_info = new einheit ();

        for ($a = 0; $a < count($haeuser_im_pool); $a++) {
            $haus_id = $haeuser_im_pool [$a] ['KOSTENTRAEGER_ID'];
            $haus_info->get_haus_info($haus_id);
            $haus_objekt_id = $haus_info->objekt_id;
            /* Falls Haus zum gewählten Objekt gehört, Pos in Objektrechnung stellen */
            // echo $haus_objekt_id;
            if ($haus_objekt_id == $objekt_id) {
                $objekt_rechnung_arr [] = $this->pool_position_holen($haeuser_im_pool [$a] ['KONTIERUNG_ID']);
            }
        }

        for ($i = 0; $i < count($einheiten_im_pool); $i++) {
            $einheit_id = $einheiten_im_pool [$i] ['KOSTENTRAEGER_ID'];
            $einheit_info->get_einheit_haus($einheit_id);
            $einheit_objekt_id = $einheit_info->objekt_id;
            /* Falls Einheit zum gewählten Haus gehört, Pos in Hausrechnung stellen */
            if ($einheit_objekt_id == $objekt_id) {
                $objekt_rechnung_arr [] = $this->pool_position_holen($einheiten_im_pool [$i] ['KONTIERUNG_ID']);
            }
        }

        return ($objekt_rechnung_arr);
    }

    function rechnung_aus_pool_zusammenstellen($kostentraeger_typ, $kostentraeger_id)
    {
        $result = DB::select("SELECT KONTIERUNG_DAT, KONTIERUNG_ID, KONTENRAHMEN_KONTO, KOSTENTRAEGER_TYP, KOSTENTRAEGER_ID FROM `KONTIERUNG_POSITIONEN` WHERE KOSTENTRAEGER_TYP = '$kostentraeger_typ' && KOSTENTRAEGER_ID='$kostentraeger_id' && WEITER_VERWENDEN='1' && AKTUELL='1' ORDER BY BELEG_NR DESC, POSITION ASC");
        if (!empty($result)) {
            $numrows = count($result);
            $positionen_detailiert = [];
            for ($a = 0; $a < $numrows; $a++) {
                $positionen_detailiert [] = $this->pool_position_holen($result [$a] ['KONTIERUNG_ID']);
            }
            return $positionen_detailiert;
        } else {
            return false;
        }
    }

    /* Funkt. zur Auswahl der Positionen für eine neue Rechnung aus dem Pool */

    function pool_position_holen($kontierung_id)
    {
        $result = DB::select("SELECT * FROM `KONTIERUNG_POSITIONEN` WHERE KONTIERUNG_ID= '$kontierung_id' && AKTUELL='1' ORDER BY KONTIERUNG_DAT DESC LIMIT 0,1");
        if (!empty($result)) {
            $row = $result[0];
            return $row;
        }
    }

    function rechnung_schreiben_positionen_wahl($kostentraeger_typ, $kostentraeger_id, $positionen, $aussteller_typ, $aussteller_id)
    {
        if (request()->has('csv')) {
            $this->pool_csv($kos_typ, $kos_id, $positionen, $aussteller_typ, $aussteller_id);
            die ();
        }
        $f = new formular ();
        $f->erstelle_formular("Rechnung aus Pool zusammenstellen", NULL);
        $f->hidden_feld('option', 'AUTO_RECHNUNG_VORSCHAU');

        $js_action = 'onmouseover="javascript:pool_berechnung(this.form)" onkeyup="javascript:pool_berechnung(this.form)" onmousedown="javascript:pool_berechnung(this.form)" onmouseup="javascript:pool_berechnung(this.form)" onmousemove="javascript:pool_berechnung(this.form)"';
        $objekt_info = new objekt ();
        if ($kostentraeger_typ == 'Objekt') {
            $objekt_info->get_objekt_eigentuemer_partner($kostentraeger_id);
            $rechnungs_empfaenger_id = $objekt_info->objekt_eigentuemer_partner_id;
        }

        if ($kostentraeger_typ == 'Einheit') {
            $this->get_empfaenger_infos($kostentraeger_typ, $kostentraeger_id);
            $rechnungs_empfaenger_id = $this->rechnungs_empfaenger_id;
        }

        if ($kostentraeger_typ == 'Lager') {
            $rechnungs_empfaenger_id = $kostentraeger_id;
        }

        if ($kostentraeger_typ == 'Partner') {
            $rechnungs_empfaenger_id = $kostentraeger_id;
        }

        $positionen = array_msort($positionen, array(
            'BELEG_NR' => array(
                SORT_ASC
            ),
            'POSITION' => SORT_STRING
        ));
        $this->rechnungs_kopf_zusammenstellung($kostentraeger_typ, $kostentraeger_id, $aussteller_typ, $aussteller_id);

        $self = $_SERVER ['QUERY_STRING'];
        echo "<a href=\"?$self&csv\">Als Excel</a>";

        echo "<table id=\"pos_tabelle\" class=rechnungen>";

        echo "<tr><td colspan=3>";
        $faellig_am = date("Y-m-t");
        $faellig_am = date_mysql2german($faellig_am);
        $d_heute = date("d.m.Y");
        $f->datum_feld('Rechnungsdatum', 'rechnungsdatum', "$d_heute", 'rechnungsdatum');
        $f->datum_feld('Faellig am', 'faellig_am', "$faellig_am", 'faellig_am');

        echo "</td><td colspan=6>";
        echo "</td></tr>";
        echo "<tr><td colspan=\"6\">";
        $geld_konto_info = new geldkonto_info ();
        $geld_konto_info->dropdown_geldkonten($aussteller_typ, $aussteller_id);
        echo "</td></tr>";
        echo "<div id=\"pool_tabelle\" $js_action>";
        echo "<tr><th>POOL</th><th><input type=\"checkbox\" class='filled-in' id='alle' onClick=\"check_all_boxes(this.checked, 'positionen_list_')\" $js_action><label for='alle'>Alle</label></th><th>Rechnung</th><th>UPos</th><th>Pos</th><th>Menge</th><th>Bezeichnung</th><th>Einzelpreis</th><th>Netto</th><th>Rabatt %</th><th>Skonto</th><th>MWSt</th><th>Kostentraeger</th></tr>";
        $f->hidden_feld('RECHNUNG_EMPFAENGER_TYP', "$kostentraeger_typ");
        $f->hidden_feld('RECHNUNG_EMPFAENGER_ID', "$rechnungs_empfaenger_id");
        $f->hidden_feld('RECHNUNG_AUSSTELLER_TYP', "$aussteller_typ");
        $f->hidden_feld('RECHNUNG_AUSSTELLER_ID', "$aussteller_id");
        $f->hidden_feld('RECHNUNG_KOSTENTRAEGER_ID', "$kostentraeger_id");
        $f->hidden_feld('RECHNUNG_KOSTENTRAEGER_TYP', "$kostentraeger_typ");

        $rechnungs_summe = 0;
        $start = 3;
        // nummer of <tr>
        for ($a = 0; $a < count($positionen); $a++) {
            $start++;
            $zeile = $a + 1;

            $belegnr = $positionen [$a] ['BELEG_NR'];
            $this->rechnung_grunddaten_holen($belegnr);
            $f->hidden_feld("positionen[$a][beleg_nr]", "$belegnr");
            $position = $positionen [$a] ['POSITION'];
            $f->hidden_feld("positionen[$a][position]", "$position");
            $artikel_bezeichnung = $this->kontierungsartikel_holen($belegnr, $position);
            $pos_kostentraeger_typ = $positionen [$a] ['KOSTENTRAEGER_TYP'];
            $pos_kostentraeger_id = $positionen [$a] ['KOSTENTRAEGER_ID'];
            $kostentraeger = $this->kostentraeger_ermitteln($pos_kostentraeger_typ, $pos_kostentraeger_id);
            $menge = nummer_punkt2komma($positionen [$a] ['MENGE']);
            $epreis = nummer_punkt2komma($positionen [$a] ['EINZEL_PREIS']);
            $gpreis = nummer_punkt2komma($positionen [$a] ['GESAMT_SUMME']);
            $rabatt_satz = nummer_punkt2komma($positionen [$a] ['RABATT_SATZ']);
            $skonto = nummer_punkt2komma($positionen [$a] ['SKONTO']);
            $rechnungs_summe = $rechnungs_summe + (nummer_komma2punkt($menge) * nummer_komma2punkt($epreis));
            $mwst_satz_in_prozent = nummer_punkt2komma($this->mwst_satz_der_position($belegnr, $position));
            $kontierung_dat = $positionen [$a] ['KONTIERUNG_DAT'];
            $f->hidden_feld("positionen[$a][kontierung_dat]", "$kontierung_dat");
            $link_rechnung_ansehen = "<a href='" . route('web::rechnungen::legacy', ['option' => 'rechnungs_uebersicht', 'belegnr' => $belegnr]) . "'>$this->rechnungsnummer</a>";

            echo "<tr id=\"tr_zeile.$start\"><td>";

            $rrr = new rechnungen ();
            $rrr->btn_pool($kostentraeger_typ, $kostentraeger_id, $kontierung_dat, 'this');

            echo "</td><td><input type=\"checkbox\" class='filled-in' name=uebernehmen[] id=\"positionen_list_$a\" value=\"$a\" $js_action><label for='positionen_list_$a'>$zeile</label></td><td>$link_rechnung_ansehen</td><td>$position</td><td>$zeile.</td><td>";

            $f->text_feld("Menge:", "positionen[$a][menge]", "$menge", "5", "mengen_feld_" . $a, $js_action);
            echo "</td><td>$artikel_bezeichnung</td><td>";
            $f->text_feld("Einzelpreis:", "positionen[$a][preis]", "$epreis", "8", "epreis_feld_" . $a, $js_action);
            echo "</td><td>";
            $f->text_feld_inaktiv("Netto:", "", "$gpreis", "8", "netto_feld_" . $a, $js_action);
            echo "</td><td>";
            $gpreis_brutto = ($gpreis / 100) * (100 + $mwst_satz_in_prozent);
            $gpreis_brutto = ($gpreis_brutto * 100) / 100;

            $f->text_feld("Rabatt:", "positionen[$a][rabatt_satz]", "$rabatt_satz", "5", "rabatt_feld_" . $a, $js_action);
            echo "</td><td>";
            $f->text_feld("Skonto:", "positionen[$a][skonto]", "$skonto", "5", "skonto_feld_" . $a, $js_action);
            echo "</td><td>";
            $f->text_feld("Mwst:", "mwst_satz", "$mwst_satz_in_prozent", "3", "mwst_feld_" . $a, $js_action);
            echo "</td><td valign=bottom>$kostentraeger</td></tr>";
        }

        echo "<tr><td colspan=10><hr></td></tr></table>";
        echo "<table>";

        echo "<tr><td>";

        $f->text_bereich('Kurzbeschreibung', 'kurzbeschreibung', '', 30, 30, 'kurzbeschreibung');
        echo "<br>";
        $f->send_button_disabled("senden_pos", "Speichern", "speichern_button2");
        echo "</td></tr>";

        echo "<tr><td colspan=9><hr></td></tr>";
        echo "<tr><td colspan=8 align=right>Netto ausgewählte Positionen</td><td id=\"g_netto_ausgewaehlt\"></td></tr>";
        echo "<tr><td colspan=8 align=right>Brutto ausgewählte Positionen</td><td id=\"g_brutto_ausgewaehlt\"></td></tr>";
        echo "<tr><td colspan=8 align=right>Skontonachlass</td><td id=\"g_skonto_nachlass\"></td></tr>";
        echo "<tr><td colspan=8 align=right>Skontobetrag</td><td id=\"g_skonto_betrag\"></td></tr>";
        echo "<tr><td colspan=9><hr></td></tr>";
        echo "</table>";
        echo "</div>";
        $f->ende_formular();
    }

    /* Häuser_ids eines Objekt holen */

    function pool_csv($kos_typ, $kos_id, $positionen, $aussteller_typ, $aussteller_id)
    {
        ob_clean();
        // ausgabepuffer leeren
        $fileName = 'pool' . date("d-m-Y") . '.xls';
        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=$fileName");
        header("Content-Disposition: inline; filename=$fileName");

        echo "ARTNR\tBEZEICHNUNG\tMENGE\tPREIS\tRABATT_SATZ\tMWST_SATZ\tSKONTO\tGESAMT NETTO" . "\n";
        $anz = count($positionen);
        for ($a = 0; $a < $anz; $a++) {
            $position = $positionen [$a] ['POSITION'];
            $belegnr = $positionen [$a] ['BELEG_NR'];
            $artikelnr = $this->art_nr_from_beleg($belegnr, $position);
            $menge = nummer_punkt2komma($positionen [$a] ['MENGE']);
            $bez = $this->kontierungsartikel_holen($belegnr, $position);
            $preis = nummer_punkt2komma($positionen [$a] ['EINZEL_PREIS']);
            $rabatt_satz = nummer_punkt2komma($positionen [$a] ['RABATT_SATZ']);
            $mwst_satz = $positionen [$a] ['MWST_SATZ'];
            $skonto = nummer_punkt2komma($positionen [$a] ['SKONTO']);
            $g_preis_n = nummer_punkt2komma((nummer_komma2punkt($menge) * nummer_komma2punkt($preis)) / 100 * (100 - nummer_komma2punkt($rabatt_satz)));
            echo "$artikelnr\t$bez\t$menge\t$preis\t$rabatt_satz\t$mwst_satz\t$skonto\t$g_preis_n\n";
        }
    }

    /* Einheiten_ids eines Objekt holen */

    function art_nr_from_beleg($belegnr, $pos)
    {
        $result = DB::select("SELECT ARTIKEL_NR FROM RECHNUNGEN_POSITIONEN WHERE BELEG_NR='$belegnr' && POSITION='$pos' && AKTUELL='1' ORDER BY RECHNUNGEN_POS_DAT DESC LIMIT 0,1");
        $row = $result[0];
        return $row ['ARTIKEL_NR'];
    }

    function kontierungsartikel_holen($beleg_nr, $position)
    {
        $result = DB::select("SELECT ARTIKEL_NR, ART_LIEFERANT  FROM RECHNUNGEN_POSITIONEN WHERE BELEG_NR='$beleg_nr' && POSITION='$position' && AKTUELL='1' ORDER BY RECHNUNGEN_POS_DAT DESC LIMIT 0,1");
        if (!empty($result)) {
            $row = $result[0];
            $artikel_nr = $row ['ARTIKEL_NR'];
            $art_lieferant = $row ['ART_LIEFERANT'];
            $artikel_info = $this->artikel_info($art_lieferant, $artikel_nr);
            $this->artikel_nr = $row ['ARTIKEL_NR'];
            $this->art_lieferant = $row ['ART_LIEFERANT'];
            return $artikel_info [0] ['BEZEICHNUNG'];
        }
    }

    /* Kontierungspool array nach Austellern filtern */

    function artikel_info($partner_id, $artikel_nr)
    {
        $result = DB::select("SELECT * FROM POSITIONEN_KATALOG WHERE ART_LIEFERANT='$partner_id' && ARTIKEL_NR = '$artikel_nr' && AKTUELL='1' ORDER BY KATALOG_DAT DESC LIMIT 0,1");
        return $result;
    }

    function get_empfaenger_infos($typ, $id)
    {
        if ($typ == 'Lager') {
            $lager_info = new lager ();
            return $lager_bezeichnung = $lager_info->lager_bezeichnung($id);
        }
        if ($typ == 'Kasse') {
            $kassen_info = new kasse ();
            $kassen_info->get_kassen_info($id);
            return $kassen_info->kassen_name;
        }
        // #######
        if ($typ == 'Einheit') {
            $einheit_info = new einheit ();
            $einheit_info->get_einheit_info($id);
            $id = $einheit_info->haus_id;
            $typ = 'Haus';
        }
        if ($typ == 'Haus') {
            $haus_info = new haus ();
            $haus_info->get_haus_info($id);
            $id = $haus_info->objekt_id;
            $typ = 'Objekt';
        }
        if ($typ == 'Objekt') {
            $objekt_info = new objekt ();
            $objekt_info->get_objekt_name($id);
            $objekt_info->get_objekt_eigentuemer_partner($id);
            $id = $objekt_info->objekt_eigentuemer_partner_id;
            $typ = 'Partner';
            $this->objekt_name = $objekt_info->objekt_name;
            $this->rechnungs_empfaenger_typ = $typ;
            $this->rechnungs_empfaenger_id = $id;
        }
        if ($typ == 'Partner') {
            $partner_info = new partners ();
            $partner_info->get_partner_name($id);
            return $partner_info->partner_name;
        }
    }

    /* automatisch erstellte rechnung speichern */

    function rechnungs_kopf_zusammenstellung($kostentraeger_typ, $kostentraeger_id, $aussteller_typ, $aussteller_id)
    {
        $rechnung_von = $this->get_author_infos($aussteller_typ, $aussteller_id);
        $rechnung_an = $this->get_empfaenger_infos($kostentraeger_typ, $kostentraeger_id);
        echo "<table>";
        if ($kostentraeger_typ == 'Lager') {
            $rechnung_vzweck = "Rechnung an das Lager <b>$rechnung_an</b>";
        }
        if ($kostentraeger_typ == 'Kasse') {
            $rechnung_vzweck = "Rechnung an die Kasse <b>$rechnung_an</b>";
        }
        // #######
        if ($kostentraeger_typ == 'Einheit') {
            $einheit = $this->kostentraeger_ermitteln($kostentraeger_typ, $kostentraeger_id);
            $rechnung_vzweck = "Rechnung für Einheit $einheit";
        }
        if ($kostentraeger_typ == 'Haus') {
            $rechnung_vzweck = "Rechnung an den Eigentümer für ein Haus";
        }
        if ($kostentraeger_typ == 'Objekt') {
            $rechnung_vzweck = "Rechnung an den Eigentümer für das Objekt $this->objekt_name";
        }
        if ($kostentraeger_typ == 'Partner') {
            $rechnung_vzweck = "Rechnung an den Partner <b>$rechnung_an</b>";
        }
        echo "<tr><td colspan=2>$rechnung_vzweck</td></tr>";
        echo "<tr><td>Rechnung von</td><td>Rechnung an</td></tr>";
        echo "<tr><td>$rechnung_von</td><td>$rechnung_an</td></tr>";
        echo "</table>";
    }

    function get_author_infos($typ, $id)
    {
        if ($typ == 'Lager') {
            $lager_info = new lager ();
            return $lager_bezeichnung = $lager_info->lager_bezeichnung($id);
        }
        if ($typ == 'Kasse') {
            $kassen_info = new kasse ();
            $kassen_info->get_kassen_info($id);
            return $kassen_info->kassen_name;
        }
        if ($typ == 'Partner') {
            $partner_info = new partners ();
            $partner_info->get_partner_name($id);
            return $partner_info->partner_name;
        }
    }

    /* Letzte Rechnung ID */

    function kostentraeger_ermitteln($kostentraeger_typ, $kostentraeger_id)
    {
        if ($kostentraeger_typ == 'Objekt') {
            $objekte = new objekt ();
            $objekt_name = $objekte->get_objekt_name($kostentraeger_id);
            $kostentraeger_string = "$objekt_name";
            // echo $kostentraeger_string;
            return $kostentraeger_string;
            // return $objekte->objekt_name;
        }
        if ($kostentraeger_typ == 'Haus') {
            $haeuser = new haus ();
            /*
			 * var $objekt_id;
			 * var $objekt_name;
			 * var $haus_strasse;
			 * var $haus_nummer;
			 * var $anzahl_haeuser;
			 * var $anzahl_einheiten;
			 * var $haus_plz;
			 * var $haus_stadt;
			 */
            $haeuser->get_haus_info($kostentraeger_id);
            $kostentraeger_string = "" . $haeuser->haus_strasse . " " . $haeuser->haus_nummer . "";
            return $kostentraeger_string;
        }
        if ($kostentraeger_typ == 'Einheit') {
            $einheiten = new einheit ();
            $einheiten->get_einheit_info($kostentraeger_id);
            // $kostentraeger_string = "<b>".$einheiten->einheit_kurzname."</b>&nbsp;".$einheiten->objekt_name."&nbsp;".$einheiten->haus_strasse."".$einheiten->haus_nummer."";
            $kostentraeger_string = "" . $einheiten->einheit_kurzname . "";
            return $kostentraeger_string;
        }

        if ($kostentraeger_typ == 'Partner') {
            $partner_info = new partner ();
            $partner_name = $partner_info->get_partner_name($kostentraeger_id);
            // $partner_name = substr($partner_name, 0, 20);
            return $partner_name;
        }
        if ($kostentraeger_typ == 'Lager') {
            $lager_info = new lager ();
            $lager_bezeichnung = $lager_info->lager_bezeichnung($kostentraeger_id);
            return $lager_bezeichnung;
        }

        if ($kostentraeger_typ == 'Mietvertrag') {
            $mv = new mietvertraege ();
            $mv->get_mietvertrag_infos_aktuell($kostentraeger_id);
            $kostentraeger_bez = $mv->personen_name_string_u;
            return $kostentraeger_bez;
        }

        if ($kostentraeger_typ == 'GELDKONTO') {
            $gk = new geldkonto_info ();
            $gk->geld_konto_details($kostentraeger_id);
            $kostentraeger_bez = $gk->geldkonto_bezeichnung_kurz;
            return $kostentraeger_bez;
        }

        if ($kostentraeger_typ == 'ALLE') {
            return 'ALLE';
        }

        if ($kostentraeger_typ == 'Wirtschaftseinheit') {
            $w = new wirt_e ();
            $w->get_wirt_e_infos($kostentraeger_id);
            return $w->w_name;
        }

        if ($kostentraeger_typ == 'Wirtschaftseinheit') {
            $w = new wirt_e ();
            $w->get_wirt_e_infos($kostentraeger_id);
            return $w->w_name;
        }

        if ($kostentraeger_typ == 'Baustelle_ext') {
            $s = new statistik ();
            $s->get_baustelle_ext_infos($kostentraeger_id);
            return 'BV*' . $s->bez;
        }

        if ($kostentraeger_typ == 'Eigentuemer') {
            $weg = new weg ();
            $bez = substr($weg->get_eigentumer_id_infos2($kostentraeger_id), 0, -2);
            return $bez;
        }

        if ($kostentraeger_typ == 'Benutzer') {
            $be = new benutzer ();
            $be->get_benutzer_infos($kostentraeger_id);
            return $be->benutzername;
        }
    }

    /* Letzte Rechnung ID */

    function mwst_satz_der_position($belegnr, $position)
    {
        $result = DB::select("SELECT MWST_SATZ FROM RECHNUNGEN_POSITIONEN WHERE BELEG_NR='$belegnr' && POSITION='$position' && AKTUELL='1' ORDER BY RECHNUNGEN_POS_DAT DESC LIMIT 0,1");
        $row = $result[0];
        return $row ['MWST_SATZ'];
    }

    /* Letzte Rechnung ID */

    function objekte_im_pool()
    {
        $einheiten_im_pool = $this->pool_durchsuchen('Einheit');
        $haus_im_pool = $this->pool_durchsuchen('Haus');
        $objekte_im_pool = $this->pool_durchsuchen('Objekt');

        $einheit_info = new einheit ();
        for ($a = 0; $a < count($einheiten_im_pool); $a++) {
            $einheit_id = $einheiten_im_pool [$a] ['KOSTENTRAEGER_ID'];
            $einheit_info->get_einheit_haus($einheit_id);
            $kostentraeger_id = $einheit_info->objekt_id;
            $objekte [] = $kostentraeger_id;
        }
        /* Doppelte entfernen */
        $objekte = array_unique($objekte);

        /* Häuser */
        $haus_info = new haus ();
        for ($a = 0; $a < count($haus_im_pool); $a++) {
            $haus_id = $haus_im_pool [$a] ['KOSTENTRAEGER_ID'];
            $haus_info->get_haus_info($haus_id);
            $kostentraeger_id = $haus_info->objekt_id;
            $objekte [] = $kostentraeger_id;
        }
        /* Doppelte entfernen */
        $objekte = array_unique($objekte);

        /* Objekte */
        for ($a = 0; $a < count($objekte_im_pool); $a++) {
            $kostentraeger_id = $objekte_im_pool [$a] ['KOSTENTRAEGER_ID'];
            $objekte [] = $kostentraeger_id;
        }
        /* Doppelte entfernen */
        $objekte_im_pool = array_unique($objekte);

        foreach ($objekte_im_pool as $key => $value) {
            $objekte_sortiert [] = $value;
        }

        /*
		 * echo "<pre>";
		 * print_r($objekte_sortiert);
		 * echo "</pre>";
		 */
        return $objekte_sortiert;
    }

    /* Letzte Belegnummer */

    function haeuser_vom_objekt_im_pool($objekt_id)
    {
        $haus_im_pool = $this->pool_durchsuchen('Haus');
        /* Häuser */
        $haus_info = new haus ();
        for ($a = 0; $a < count($haus_im_pool); $a++) {
            $haus_id = $haus_im_pool [$a] ['KOSTENTRAEGER_ID'];
            $haus_info->get_haus_info($haus_id);
            $kostentraeger_id = $haus_info->objekt_id;
            if ($kostentraeger_id == $objekt_id) {
                $haeuser_arr [] = $haus_id;
            }
        }
        /* Doppelte entfernen */
        if (is_array($haeuser_arr)) {
            $haeuser_arr = array_unique($haeuser_arr);
            foreach ($haeuser_arr as $key => $value) {
                $haeuser_arr_sortiert [] = $value;
            }
            return $haeuser_arr_sortiert;
        }
    }

    /* Rechnungsgrunddaten holen */

    function einheiten_vom_objekt_im_pool($objekt_id)
    {
        $einheiten_im_pool = $this->pool_durchsuchen('Einheit');
        /* Einheiten */
        $einheit_info = new einheit ();
        for ($a = 0; $a < count($einheiten_im_pool); $a++) {
            $einheit_id = $einheiten_im_pool [$a] ['KOSTENTRAEGER_ID'];
            $einheit_info->get_einheit_haus($einheit_id);
            $kostentraeger_id = $einheit_info->objekt_id;
            if ($kostentraeger_id == $objekt_id) {
                $einheiten_arr [] = $einheit_id;
            }
        }
        /* Doppelte entfernen */
        if (is_array($einheiten_arr)) {
            $einheiten_arr = array_unique($einheiten_arr);
            foreach ($einheiten_arr as $key => $value) {
                $einheiten_arr_sortiert [] = $value;
            }
            return $einheiten_arr_sortiert;
        }
    } // end function

    function elemente_im_pool_baum()
    {
        $einheiten_im_pool = $this->pool_durchsuchen('Einheit');
        $haus_im_pool = $this->pool_durchsuchen('Haus');
        $objekte_im_pool = $this->pool_durchsuchen('Objekt');
        /* Lager ids zum neuer Array hinzu, danach dopplete löschen */
        $lager_im_pool = $this->pool_durchsuchen('Lager');
        if (!empty($lager_im_pool)) {
            for ($a = 0; $a < count($lager_im_pool); $a++) {
                $lager_id = $lager_im_pool [$a] ['KOSTENTRAEGER_ID'];
                $elemente ['LAGER'] [] = $lager_id;
            }
            /* Doppelte entfernen */
            if (is_array($elemente ['LAGER'])) {
                $elemente ['LAGER'] = array_unique($elemente ['LAGER']);
                foreach ($elemente ['LAGER'] as $key => $value) {
                    $elemente_sortiert ['LAGER'] [] = $value;
                }
            }
        } // end if

        /* Partner oder Mieter */
        $partner_im_pool = $this->pool_durchsuchen('Partner');
        if (!empty($partner_im_pool)) {
            for ($a = 0; $a < count($partner_im_pool); $a++) {
                $partner_id = $partner_im_pool [$a] ['KOSTENTRAEGER_ID'];
                $elemente ['PARTNER'] [] = $partner_id;
            }
            /* Doppelte entfernen */
            if (is_array($elemente ['PARTNER'])) {
                $elemente ['PARTNER'] = array_unique($elemente ['PARTNER']);
                foreach ($elemente ['PARTNER'] as $key => $value) {
                    $elemente_sortiert ['PARTNER'] [] = $value;
                }
            }
        } // end if

        /* Einheiten Häuser Objekte anhand von Einheitszugehörigkeit */
        $einheit_info = new einheit ();
        for ($a = 0; $a < count($einheiten_im_pool); $a++) {
            $einheit_id = $einheiten_im_pool [$a] ['KOSTENTRAEGER_ID'];
            $einheit_info->get_einheit_haus($einheit_id);
            $objekt_id = $einheit_info->objekt_id;
            $haus_id = $einheit_info->haus_id;
            $elemente ['OBJEKTE'] [] = $objekt_id;
            $elemente ['HAUS'] [] = $haus_id;
            $elemente ['EINHEITEN'] [] = $einheit_id;
        }

        //Häuser
        $haus_info = new haus ();
        for ($a = 0; $a < count($haus_im_pool); $a++) {
            $haus_id = $haus_im_pool [$a] ['KOSTENTRAEGER_ID'];
            $haus_info->get_haus_info($haus_id);
            $objekt_id = $haus_info->objekt_id;
            $elemente ['OBJEKTE'] [] = $objekt_id;
            $elemente ['HAUS'] [] = $haus_id;
        }

        /*Objekte*/
        for ($a = 0; $a < count($objekte_im_pool); $a++) {
            $objekt_id = $objekte_im_pool [$a] ['KOSTENTRAEGER_ID'];
            $elemente ['OBJEKTE'] [] = $objekt_id;
        }
        // print_r($elemente);
        if (is_array($elemente)) {
            /* Doppelte entfernen */
            if (isset ($elemente ['OBJEKTE'])) {
                $elemente ['OBJEKTE'] = array_unique($elemente ['OBJEKTE']);
                foreach ($elemente ['OBJEKTE'] as $key => $value) {
                    $elemente_sortiert ['OBJEKTE'] [] = $value;
                }
            }
            if (isset ($elemente ['HAUS'])) {
                $elemente ['HAUS'] = array_unique($elemente ['HAUS']);
                foreach ($elemente ['HAUS'] as $key => $value) {
                    $elemente_sortiert ['HAUS'] [] = $value;
                }
            }
            if (isset ($elemente ['EINHEITEN'])) {
                $elemente ['EINHEITEN'] = array_unique($elemente ['EINHEITEN']);
                foreach ($elemente ['EINHEITEN'] as $key => $value) {
                    $elemente_sortiert ['EINHEITEN'] [] = $value;
                }
            }
        }

        if (isset ($elemente ['OBJEKTE']) or isset ($elemente ['HAUS']) or isset ($elemente ['EINHEITEN']) or isset ($elemente ['LAGER']) or isset ($elemente ['PARTNER'])) {
            return $elemente_sortiert;
        }  // end if is_array $elemente
        else {
            echo "Keine objektbezogene Daten im Pool";
        }
    }

    function filtern_nach_austeller($kontierung_id_arr, $aussteller_typ, $aussteller_id)
    {
        for ($a = 0; $a < count($kontierung_id_arr); $a++) {
            $beleg_nr = $kontierung_id_arr [$a] ['BELEG_NR'];
            $this->rechnung_grunddaten_holen($beleg_nr);
            /* Empfänger der Rechnung wird zum Austeller der Auto...Rechnung */
            $rechnungs_empfaenger_id = $this->rechnungs_empfaenger_id;
            if ($aussteller_id == $rechnungs_empfaenger_id && $aussteller_typ == $this->rechnungs_empfaenger_typ) {
                $neuer_kontierungs_array [] = $kontierung_id_arr [$a];
            } // end if
        } // end for

        return $neuer_kontierungs_array;
    }

    function rechnung_speichern($clean_arr)
    {
        $rechnungs_aussteller_typ = $clean_arr ['aussteller_typ'];
        $rechnungs_aussteller_id = $clean_arr ['aussteller_id'];
        $rechnungs_empfaenger_typ = $clean_arr ['empfaenger_typ'];
        $rechnungs_empfaenger_id = $clean_arr ['empfaenger_id'];

        if ($rechnungs_empfaenger_id == $rechnungs_aussteller_id && $rechnungs_empfaenger_typ == $rechnungs_aussteller_typ) {
            $rechnungs_typ_druck = 'Buchungsbeleg';
        } else {
            $rechnungs_typ_druck = 'Rechnung';
        }

        $datum_arr = explode('.', $clean_arr ['rechnungsdatum']);
        $jahr = $datum_arr [2];

        if (empty ($clean_arr ['rechnungsnummer'])) {

            $letzte_aussteller_rnr = $this->letzte_aussteller_ausgangs_nr($rechnungs_aussteller_id, $rechnungs_aussteller_typ, $jahr, $rechnungs_typ_druck);
            $letzte_aussteller_rnr = $letzte_aussteller_rnr + 1;
            $letzte_aussteller_rnr = sprintf('%03d', $letzte_aussteller_rnr);
            $this->rechnungs_kuerzel = $this->rechnungs_kuerzel_ermitteln($rechnungs_aussteller_typ, $rechnungs_aussteller_id, $clean_arr ['rechnungsdatum']);
            $rechnungsnummer = $this->rechnungs_kuerzel . ' ' . $letzte_aussteller_rnr . '-' . $jahr;
        } else {
            $letzte_aussteller_rnr = $this->letzte_aussteller_ausgangs_nr($rechnungs_aussteller_id, $rechnungs_aussteller_typ, $jahr, $rechnungs_typ_druck);
            $letzte_aussteller_rnr = $letzte_aussteller_rnr + 1;
            $rechnungsnummer = $clean_arr ['rechnungsnummer'];
        }

        /* Prüfen ob Rechnung vorhanden */

        $rechnungsdatum = date_german2mysql($clean_arr ['rechnungsdatum']);
        $result_3 = DB::select("SELECT * FROM RECHNUNGEN WHERE RECHNUNGSNUMMER = '$clean_arr[rechnungsnummer]' && RECHNUNGSDATUM = '$rechnungsdatum' && AUSSTELLER_TYP='$rechnungs_aussteller_typ' && AUSSTELLER_ID='$rechnungs_aussteller_id' && EMPFAENGER_TYP='$rechnungs_empfaenger_typ' && EMPFAENGER_ID='$rechnungs_empfaenger_id' && AKTUELL = '1'");
        if (!empty($result_3)) {
            $partner_info = new partner ();
            $von = $partner_info->get_partner_name($rechnungs_aussteller_id);
            throw new \App\Exceptions\MessageException(
                new \App\Messages\ErrorMessage("Rechnung von $von mit der Rechnungsnummer $clean_arr[rechnungsnummer] vom $clean_arr[rechnungsdatum] existiert bereits.")
            );
        } else {
            /* Letzte Belegnummer holen */

            $letzte_belegnr = $this->letzte_beleg_nr();
            $letzte_belegnr = $letzte_belegnr + 1;
            /* Letzte Rechnungsid holen */

            /* Rechnungsdaten speichern */
            $rechnungsdatum = date_german2mysql($clean_arr ['rechnungsdatum']);
            $eingangsdatum = date_german2mysql($clean_arr ['eingangsdatum']);
            $faellig_am = date_german2mysql($clean_arr ['faellig_am']);
            $kurzbeschreibung = $clean_arr ['kurzbeschreibung'];
            $netto_betrag = nummer_komma2punkt($clean_arr ['nettobetrag']);
            $brutto_betrag = $clean_arr ['bruttobetrag'];
            $brutto_betrag = nummer_komma2punkt($brutto_betrag);

            $rechnungs_typ = $rechnungs_typ_druck;

            $letzte_empfaenger_rnr = $this->letzte_empfaenger_eingangs_nr($rechnungs_empfaenger_id, $rechnungs_empfaenger_typ, $jahr, $rechnungs_typ_druck);
            $letzte_empfaenger_rnr = $letzte_empfaenger_rnr + 1;

            if ($rechnungs_empfaenger_typ == 'Kasse') {
                $status_bezahlt = '1';
                $bezahlt_am = $eingangsdatum;
            } else {
                $status_bezahlt = '0';
                $bezahlt_am = '0000-00-00';
            }

            $db_abfrage = "INSERT INTO RECHNUNGEN VALUES (NULL, '$letzte_belegnr', '$rechnungsnummer', '$letzte_aussteller_rnr', '$letzte_empfaenger_rnr', '$rechnungs_typ', '$rechnungsdatum','$eingangsdatum', '$netto_betrag','$brutto_betrag','0.00', '$rechnungs_aussteller_typ', '$rechnungs_aussteller_id','$rechnungs_empfaenger_typ', '$rechnungs_empfaenger_id','1', '1', '0', '0', '1', '$status_bezahlt', '0', '$faellig_am', '$bezahlt_am', '$kurzbeschreibung', '$clean_arr[geld_konto]')";
            DB::insert($db_abfrage);

            /* Protokollieren */
            $last_dat = DB::getPdo()->lastInsertId();
            protokollieren('RECHNUNGEN', $last_dat, '0');
            /* Ausgabe weil speichern erfolgreich */
            echo "Rechnung/Beleg $letzte_belegnr wurde erfasst.";

            if ($rechnungs_empfaenger_typ == 'Kasse') {
                $kasse = new kasse ();
                $kassen_id = $rechnungs_empfaenger_id;
                $datum = date_mysql2german($eingangsdatum);
                $kasse->rechnung_in_kassenbuch($kassen_id, $brutto_betrag, $datum, 'Ausgaben', $kurzbeschreibung, 'Rechnung', $letzte_belegnr);
            }

            /* Weiterleiten auf die Rechnungserfassung */

            weiterleiten_in_sec(route('web::rechnungen::legacy', ['option' => 'positionen_erfassen', 'belegnr' => $letzte_belegnr]), 2);
        }
    }

    function letzte_aussteller_ausgangs_nr($aussteller_id, $typ, $jahr, $rechnungs_typ)
    {
        if ($rechnungs_typ == 'Rechnung' or $rechnungs_typ == 'Stornorechnung' or $rechnungs_typ == 'Gutschrift' or $rechnungs_typ == 'Schlussrechnung' or $rechnungs_typ == 'Teilrechnung') {
            $rechnungs_typ == 'Rechnung';
            $result = DB::select("SELECT AUSTELLER_AUSGANGS_RNR FROM RECHNUNGEN WHERE AUSSTELLER_ID='$aussteller_id' && AUSSTELLER_TYP='$typ' && RECHNUNGSDATUM BETWEEN '$jahr-01-01' AND '$jahr-12-31' && (RECHNUNGSTYP='$rechnungs_typ' OR RECHNUNGSTYP='Gutschrift' OR RECHNUNGSTYP='Stornorechnung'  OR RECHNUNGSTYP='Schlussrechnung'  OR RECHNUNGSTYP='Teilrechnung') && AKTUELL='1' ORDER BY AUSTELLER_AUSGANGS_RNR DESC LIMIT 0,1");
        } else {
            $result = DB::select("SELECT AUSTELLER_AUSGANGS_RNR FROM RECHNUNGEN WHERE AUSSTELLER_ID='$aussteller_id' && AUSSTELLER_TYP='$typ' && RECHNUNGSDATUM BETWEEN '$jahr-01-01' AND '$jahr-12-31' && RECHNUNGSTYP='$rechnungs_typ' && AKTUELL='1' ORDER BY AUSTELLER_AUSGANGS_RNR DESC LIMIT 0,1");
        }
        $row = $result[0];
        return $row ['AUSTELLER_AUSGANGS_RNR'];
    }

    function letzte_beleg_nr()
    {
        $result = DB::select("SELECT BELEG_NR FROM RECHNUNGEN ORDER BY BELEG_NR DESC LIMIT 0,1");
        $row = $result[0];
        return $row ['BELEG_NR'];
    }

    /* Rechnung mit Positionen anzeigen */

    function letzte_empfaenger_eingangs_nr($empfaenger_id, $typ, $jahr, $rechnungs_typ)
    {
        if ($rechnungs_typ == 'Rechnung' or $rechnungs_typ == 'Stornorechnung' or $rechnungs_typ == 'Gutschrift' or $rechnungs_typ == 'Schlussrechnung' or $rechnungs_typ == 'Teilrechnung') {
            $rechnungs_typ == 'Rechnung';
            $result = DB::select("SELECT EMPFAENGER_EINGANGS_RNR FROM RECHNUNGEN WHERE EMPFAENGER_ID='$empfaenger_id' && EMPFAENGER_TYP='$typ' && DATE_FORMAT(RECHNUNGSDATUM, '%Y') = '$jahr' && (RECHNUNGSTYP='$rechnungs_typ' OR RECHNUNGSTYP='Gutschrift' OR RECHNUNGSTYP='Stornorechnung' OR RECHNUNGSTYP='Schlussrechnung'  OR RECHNUNGSTYP='Teilrechnung') && AKTUELL='1' ORDER BY BELEG_NR DESC LIMIT 0,1");
        } else {
            $result = DB::select("SELECT EMPFAENGER_EINGANGS_RNR FROM RECHNUNGEN WHERE EMPFAENGER_ID='$empfaenger_id' && EMPFAENGER_TYP='$typ' && DATE_FORMAT(RECHNUNGSDATUM, '%Y') = '$jahr' && RECHNUNGSTYP='$rechnungs_typ'  && AKTUELL='1' ORDER BY BELEG_NR DESC LIMIT 0,1");
        }
        $row = $result[0];
        return $row ['EMPFAENGER_EINGANGS_RNR'];
    }

    function auto_rechnung_speichern($clean_arr)
    {
        /*
		 * echo "<pre>";
		 * print_r($clean_arr);
		 * echo "</pre>";
		 */
        $r_e_id = $clean_arr ['RECHNUNG_EMPFAENGER_ID'];
        // #######################
        if ($clean_arr ['RECHNUNG_EMPFAENGER_TYP'] == 'Partner') {
            $this->rechnungs_empfaenger_typ = 'Partner';
            $this->rechnungs_empfaenger_id = $r_e_id;
        }
        if ($clean_arr ['RECHNUNG_EMPFAENGER_TYP'] == 'Objekt') {
            $this->rechnungs_empfaenger_typ = 'Partner';
            $this->rechnungs_empfaenger_id = $this->eigentuemer_ermitteln('Objekt', $r_e_id);
        }
        if ($clean_arr ['RECHNUNG_EMPFAENGER_TYP'] == 'Haus') {
            $this->rechnungs_empfaenger_typ = 'Partner';
            $this->rechnungs_empfaenger_id = $this->eigentuemer_ermitteln('Haus', $r_e_id);
        }
        if ($clean_arr ['RECHNUNG_EMPFAENGER_TYP'] == 'Einheit') {
            $this->rechnungs_empfaenger_typ = 'Partner';
            $this->rechnungs_empfaenger_id = $this->eigentuemer_ermitteln('Einheit', $r_e_id);
        }

        if ($clean_arr ['RECHNUNG_EMPFAENGER_TYP'] == 'Lager') {
            $this->rechnungs_empfaenger_typ = 'Lager';
            $this->rechnungs_empfaenger_id = $r_e_id;
        }

        if ($clean_arr ['RECHNUNG_EMPFAENGER_TYP'] == 'Kasse') {
            /* Kassen Partner finden */
            $kasse = new kasse ();
            $kasse->get_kassen_info($r_e_id);
            $this->rechnungs_empfaenger_typ = 'Kasse';
            $this->rechnungs_empfaenger_id = $kasse->kassen_partner_id;
        }

        // #######################

        $this->rechnungs_aussteller_typ = $clean_arr ['RECHNUNG_AUSSTELLER_TYP'];
        $this->rechnungs_aussteller_id = $clean_arr ['RECHNUNG_AUSSTELLER_ID'];

        /* Wenn Austeller = Empfänger - GmbH an Gmbh = Buchungsbeleg */
        if ($this->rechnungs_empfaenger_id == $this->rechnungs_aussteller_id && $this->rechnungs_aussteller_typ == $this->rechnungs_empfaenger_typ) {
            $this->rechnungs_typ_druck = 'Buchungsbeleg';
        } else {
            $this->rechnungs_typ_druck = 'Rechnung';
        }

        $rechnungsdatum = $clean_arr ['RECHNUNGSDATUM'];
        $datum_arr = explode('.', $rechnungsdatum);
        $jahr = $datum_arr [2];
        $rechnungsdatum_sql = date_german2mysql($rechnungsdatum);
        /* Ausgangsnr */
        $letzte_aussteller_rnr = $this->letzte_aussteller_ausgangs_nr($this->rechnungs_aussteller_id, $this->rechnungs_aussteller_typ, $jahr, $this->rechnungs_typ_druck);
        $letzte_aussteller_rnr = $letzte_aussteller_rnr + 1;
        $letzte_aussteller_rnr1 = sprintf('%03d', $letzte_aussteller_rnr);
        /* Kürzel */
        $this->rechnungs_kuerzel = $this->rechnungs_kuerzel_ermitteln($this->rechnungs_aussteller_typ, $this->rechnungs_aussteller_id, $rechnungsdatum_sql);

        $rechnungsnummer = $this->rechnungs_kuerzel . ' ' . $letzte_aussteller_rnr1 . '-' . $jahr;

        // echo "<h1> $rechnunsgnummer $this->rechnungs_kuerzel $letzte_aussteller_rnr</h1>";

        /* Prüfen ob Rechnung vorhanden */
        $check_rechnung = $this->check_rechnung_vorhanden($rechnungsnummer, $rechnungsdatum_sql, $this->rechnungs_aussteller_typ, $this->rechnungs_aussteller_id, $this->rechnungs_empfaenger_typ, $this->rechnungs_empfaenger_id, $this->rechnungs_typ_druck);

        /* Wenn rechnung existiert */
        if ($check_rechnung) {
            $partner_info = new partner ();
            $von = $partner_info->get_partner_name($this->rechnungs_aussteller_id);
            throw new \App\Exceptions\MessageException(
                new \App\Messages\ErrorMessage("$this->rechnungs_typ_druck von $von mit der Nummer $this->rechnungs_typ_druck  $rechnungsnummer vom $rechnungsdatum existiert bereits.")
            );
        } else {

            /* Rechnungsdaten speichern */
            $eingangsdatum = $rechnungsdatum_sql;
            $faellig_am = date_german2mysql($clean_arr ['RECHNUNG_FAELLIG_AM']);
            $kurzbeschreibung = $clean_arr ['kurzbeschreibung'];

            $netto_betrag = $clean_arr ['nettobetrag'];
            $brutto_betrag = $clean_arr ['bruttobetrag'];

            // $skonto = $clean_arr[skonto];
            // $skonto = nummer_komma2punkt($skonto);

            // $skonto_betrag = ($brutto_betrag/100) * (100-$skonto);
            // $skonto_betrag = nummer_komma2punkt($skonto_betrag);
            $letzte_empfaenger_rnr = $this->letzte_empfaenger_eingangs_nr($this->rechnungs_empfaenger_id, $this->rechnungs_empfaenger_typ, $jahr, $this->rechnungs_typ_druck);
            $letzte_empfaenger_rnr = $letzte_empfaenger_rnr + 1;

            $empfangs_geld_konto = $clean_arr ['EMPFANGS_GELD_KONTO'];

            /* Sonst Letzte Belegnummer holen */
            $letzte_belegnr = $this->letzte_beleg_nr();
            $letzte_belegnr = $letzte_belegnr + 1;

            $db_abfrage = "INSERT INTO RECHNUNGEN VALUES (NULL, '$letzte_belegnr', '$rechnungsnummer', '$letzte_aussteller_rnr', '$letzte_empfaenger_rnr', '$this->rechnungs_typ_druck', '$rechnungsdatum_sql','$eingangsdatum', '$netto_betrag','$brutto_betrag','$skonto_betrag', '$this->rechnungs_aussteller_typ', '$this->rechnungs_aussteller_id','$this->rechnungs_empfaenger_typ', '$this->rechnungs_empfaenger_id','1', '1', '1', '0', '1', '0', '0', '$faellig_am', '0000-00-00', '$kurzbeschreibung', '$empfangs_geld_konto')";
            DB::insert($db_abfrage);
            /* Protokollieren */
            $last_dat = DB::getPdo()->lastInsertId();
            protokollieren('RECHNUNGEN', $last_dat, '0');
            /* Ausgabe weil speichern erfolgreich */
            echo "$this->rechnungs_typ_druck $letzte_belegnr wurde erfasst.";
            /* Weiterleiten auf die Rechnungserfassung */

            if ($this->rechnungs_empfaenger_typ == 'Kasse') {
                $kasse = new kasse ();
                $kassen_id = $clean_arr [$this->rechnungs_empfaenger_id];
                $datum = date_mysql2german($eingangsdatum);
                $kasse->speichern_in_kassenbuch($kassen_id, $brutto_betrag, $datum, 'Ausgaben', $kurzbeschreibung, 'Rechnung', $letzte_belegnr);
            }

            return $letzte_belegnr;
        }
    }

    function eigentuemer_ermitteln($kostentraeger_typ, $kostentraeger_id)
    {
        if ($kostentraeger_typ == 'Haus') {
            $haeuser = new haus ();
            $haeuser->get_haus_info($kostentraeger_id);
            $kostentraeger_id = $haeuser->objekt_id;
            $kostentraeger_typ = 'Objekt';
        }
        if ($kostentraeger_typ == 'Einheit') {
            $einheiten = new einheit ();
            $einheiten->get_einheit_info($kostentraeger_id);
            $kostentraeger_id = $einheiten->objekt_id;
            $kostentraeger_typ = 'Objekt';
        }
        if ($kostentraeger_typ == 'Objekt') {
            $o = new objekt ();
            $o->get_objekt_eigentuemer_partner($kostentraeger_id);
            return $o->objekt_eigentuemer_partner_id;
        }
    }

    function check_rechnung_vorhanden($rechnungsnummer, $rechnungsdatum, $aussteller_typ, $aussteller_id, $empfaenger_typ, $empfaenger_id, $rechnungs_typ)
    {
        $result_3 = DB::select("SELECT * FROM RECHNUNGEN WHERE RECHNUNGSNUMMER = '$rechnungsnummer' && RECHNUNGSDATUM = '$rechnungsdatum' && AUSSTELLER_TYP='$aussteller_typ' && AUSSTELLER_ID='$aussteller_id' && EMPFAENGER_TYP='$empfaenger_typ' && EMPFAENGER_ID='$empfaenger_id' && AKTUELL = '1' && RECHNUNGSTYP='$rechnungs_typ'");
        return !empty($result_3);
    }

    // ######
    /* Rechnung zum Kontieren mit Positionen anzeigen */

    function letzte_rechnung_id($empfaenger_id, $typ)
    {
        $result = DB::select("SELECT RECHNUNG_ID FROM RECHNUNGEN WHERE EMPFAENGER_ID='$empfaenger_id' && EMPFAENGER_TYP='$typ' ORDER BY RECHNUNG_ID DESC LIMIT 0,1");
        $row = $result[0];
        return $row ['RECHNUNG_ID'];
    }

    function summe_netto_positionen($beleg_nr)
    {
        $rr = new rechnungen ();
        return $rr->summe_netto_positionen($beleg_nr);
    } // end function

    function rechnung_inkl_positionen_anzeigen($belegnr)
    {
        /* Rechnungskopf mit Grunddaten */
        $this->rechnungs_kopf($belegnr);

        $rechnungs_positionen_arr = $this->rechnungs_positionen_arr($belegnr);
        /* Rechnungspositionen Überschrift */
        echo "<div id=\"div_positionen\">";
        echo "<table id=\"positionen_tab\">\n";
        echo "<thead>";
        echo "<tr>";
        echo "<th scopr=\"col\" id=\"aus\">Aus</th>";
        echo "<th scopr=\"col\" id=\"aus\">Nach</th>";
        echo "<th scopr=\"col\">Pos</th>";
        echo "<th scopr=\"col\">Art.</th>";
        echo "<th scopr=\"col\">Bezeichnung</th>";
        if ($this->rechnungstyp == 'Buchungsbeleg') {
            echo "<th scopr=\"col\">Kontierung</th>";
        }
        echo "<th scopr=\"col\">Menge</th>";
        echo "<th scopr=\"col\">EP</th>";
        echo "<th scopr=\"col\">Rab.</th>";
        echo "<th scopr=\"col\">MWSt</th>";
        echo "<th scopr=\"col\">Skonto</th>";
        echo "<th scopr=\"col\" align=right>Netto</th>";
        echo "<th scopr=\"col\" align=right>WB</th>";
        echo "</tr>";
        echo "</thead>";
        if (count($rechnungs_positionen_arr) > 0) {
            /* Rechnungspositionen */
            for ($a = 0; $a < count($rechnungs_positionen_arr); $a++) {

                $u_beleg_nr = $rechnungs_positionen_arr [$a] ['U_BELEG_NR'];
                $position = $rechnungs_positionen_arr [$a] ['POSITION'];
                $menge = $rechnungs_positionen_arr [$a] ['MENGE'];
                $einzel_preis = $rechnungs_positionen_arr [$a] ['PREIS'];
                $mwst_satz = $rechnungs_positionen_arr [$a] ['MWST_SATZ'];
                $rabatt = $rechnungs_positionen_arr [$a] ['RABATT_SATZ'];

                $gesamt_netto = $rechnungs_positionen_arr [$a] ['GESAMT_NETTO'];
                $gesamt_netto = nummer_punkt2komma($gesamt_netto);
                $art_lieferant = $rechnungs_positionen_arr [$a] ['ART_LIEFERANT'];
                $artikel_nr = $rechnungs_positionen_arr [$a] ['ARTIKEL_NR'];
                $pos_skonto = $rechnungs_positionen_arr [$a] ['SKONTO'];
                if ($rabatt == '99.99' or $rabatt == '9.99' or $rabatt == '999.99') {
                    fehlermeldung_ausgeben("Rabatt 99.99% oder Skonti 9.99%, Rechnung korrigieren!!!<br><br>");
                    $link_autokorrektur_pos = "<a href='" . route('web::rechnungen::legacy', ['option' => 'autokorrektur_pos', 'belegnr' => $belegnr]) . "'>Autokorrektur vornehmen</a>";
                    warnung_ausgeben($link_autokorrektur_pos);
                    echo "<br>";
                }
                $pos_skonto = nummer_punkt2komma($pos_skonto);

                /* Infos aus Katalog zu Artikelnr */
                $artikel_info_arr = $this->artikel_info($art_lieferant, $rechnungs_positionen_arr [$a] ['ARTIKEL_NR']);
                for ($i = 0; $i < count($artikel_info_arr); $i++) {
                    if (!empty ($artikel_info_arr [$i] ['BEZEICHNUNG'])) {
                        $bezeichnung = $artikel_info_arr [$i] ['BEZEICHNUNG'];
                    } else {
                        $bezeichnung = 'Unbekannt';
                    }

                    $menge = nummer_punkt2komma($menge);
                    $einzel_preis = sprintf("%01.3f", $einzel_preis);
                    $einzel_preis = nummer_punkt2komma($einzel_preis);

                    $r2 = new rechnungen ();
                    $u_rechnungsnummer = $r2->get_rechnungsnummer($u_beleg_nr);

                    $u_link = "<a href='" . route('web::rechnungen::legacy', ['option' => 'rechnungs_uebersicht', 'belegnr' => $u_beleg_nr]) . "'>$u_rechnungsnummer</a>";
                    $ae_link = "<a href='" . route('web::rechnungen::legacy', ['option' => 'position_aendern', 'belegnr' => $belegnr, 'pos' => $position]) . "'>Ändern</a>";
                    $f_link = $this->nach_link($belegnr, $artikel_nr, $art_lieferant);
                    echo "<tr><td valign=top id=\"aus\">$ae_link $u_link</td><td valign=top id=\"aus\">$f_link</td><td valign=top>$position.</td><td valign=top>$artikel_nr&nbsp;</td><td valign=top>$bezeichnung</td>";

                    if ($this->rechnungstyp == 'Buchungsbeleg') {
                        echo "<td valign=top>";
                        $this->position_kontierung_infos($belegnr, $position);
                        echo "<b>$this->k_kontenrahmen_konto $this->k_kostentraeger_bez</b>";
                        echo "</td>";
                    }
                    $js_wb = "onclick=\"wb_hinzufuegen($belegnr, $position)\"";
                    $wb = "<img src=\"images/wb.png\" $js_wb>";
                    echo "<td align=right valign=top>&nbsp;&nbsp;$menge&nbsp;</td><td align=right valign=top>$einzel_preis&nbsp;</td><td align=left valign=top>&nbsp;&nbsp;$rabatt%</td><td align=left valign=top>&nbsp;&nbsp;$mwst_satz%&nbsp;</td><td align=right valign=top>$pos_skonto%&nbsp;&nbsp;</td><td align=right valign=top>$gesamt_netto €</td><td>$wb</td></tr>\n\n";
                } // end for 2
            } // end for 1

            /* Tabelle geht weiter in footertabelle_anzeigen und DIV element endet auch dort */
        }  // ende if $this->anzahl_positionen >0 d.h. Rechnung wurde nur kurz erfasst, positionen fehlen
        /* Positionen erfassen */
        else {
            echo "<tr><td><a href='" . route('web::rechnungen::legacy', ['option' => 'positionen_erfassen', 'belegnr' => $belegnr]) . "'>Positioneneigabe hier</a></td><td></td></tr>\n\n";
        }

        /* Rechnungsfooter d.h. Netto Brutto usw. */
        $this->rechnung_footer_tabelle_anzeigen();

        $this->footer_zahlungshinweis($belegnr);
        /* Footerzeile */
        $this->footer_zeilen_anzeigen($belegnr);
    }

    function rechnungs_kopf($belegnr)
    {
        $this->rechnung_grunddaten_holen($belegnr);
        $we_nummer = $this->empfaenger_eingangs_rnr;
        $wa_nummer = $this->aussteller_ausgangs_rnr;

        echo "<table id=rechnung width=\"100%\">\n";
        if ($this->status_bezahlt == '1') {
            $status_gezahlt = 'JA';
            $link_zahlung_freigeben = "";
        } else {
            $status_gezahlt = 'NEIN';
            if ($this->status_zahlung_freigegeben == '0') {
                $link_zahlung_freigeben = "<a href='" . route('web::rechnungen::legacy', ['option' => 'zahlung_freigeben', 'belegnr' => $belegnr]) . "'><b>Zur Zahlung freigeben</b></a>";
            } else {
                $link_zahlung_freigeben = "Zur Zahlung freigegeben";
            }
        }

        $link_grunddaten_aendern = "<a href='" . route('web::rechnungen::legacy', ['option' => 'rechnungsgrunddaten_aendern', 'belegnr' => $belegnr]) . "'><b>Grunddaten ändern</b></a>";

        $status_kontierung = $this->rechnung_auf_kontierung_pruefen($belegnr);
        // vollständig, unvollständig oder falsch
        if ($status_kontierung == 'vollstaendig' && $this->status_zugewiesen == '0') {
            $this->rechnung_als_zugewiesen($belegnr);
        }
        if ($status_kontierung == 'vollstaendig' && $this->status_vollstaendig == '0') {
            $this->rechnung_als_vollstaendig($belegnr);
        }
        if ($status_kontierung == 'unvollstaendig' or $status_kontierung == 'unvollstaendig') {
            // $this->rechnung_als_unvollstaendig($belegnr); //unvollständig und nicht zugewiesen, 2xstatus aufgehoben hat auswirkungen auf buchung, da keine buchung eine unvollständigen rechnung möglich ist
        }

        $kontierungsstatus_link = "<a href='" . route('web::rechnungen::legacy', ['option' => 'rechnung_kontieren', 'belegnr' => $belegnr]) . "' class=\"kontierungs_link\">Kontierung $status_kontierung</a>\n";
        $kontierung_aufheben_link = "<a href='" . route('web::rechnungen::legacy', ['option' => 'rechnung_kontierung_aufheben', 'belegnr' => $belegnr]) . "' class=\"kontierungs_link\">Gesamte Kontierung aufheben</a>\n";

        if ($this->rechnungstyp == "Rechnung") {
            echo "<tr class=feldernamen><td>Rechnung von</td><td>Rechnung an</td><td colspan=2 class=\"zusatz_info\">Zusatzinfo</td></tr>\n";
        }
        if ($this->rechnungstyp == "Buchungsbeleg") {
            echo "<tr class=feldernamen><td>Buchungsbeleg von</td><td>Buchungsbeleg an</td><td colspan=2 class=\"zusatz_info\">Zusatzinfo</td></tr>\n";
        }
        if ($this->rechnungstyp == "Gutschrift") {
            echo "<tr class=feldernamen><td>Gutschrift von</td><td>Gutschrift an</td><td colspan=2 class=\"zusatz_info\">Zusatzinfo</td></tr>\n";
        }
        // if($this->rechnungstyp == "Gutschrift"){
        // echo "<tr class=feldernamen><td>Gutschrift von</td><td>Gutschrift an</td><td colspan=2>Zusatzinfo</td></tr>\n";
        // }
        echo "<tr><td style='vertical-align: top'>" . $this->rechnungs_aussteller_name . "<br>" . $this->rechnungs_aussteller_strasse . " " . $this->rechnungs_aussteller_hausnr . "<br><br>" . $this->rechnungs_aussteller_plz . " " . $this->rechnungs_aussteller_ort . " </td><td style='vertical-align: top'><b>" . $this->rechnungs_empfaenger_name . "</b><br>" . $this->rechnungs_empfaenger_strasse . " " . $this->rechnungs_empfaenger_hausnr . "<br><br>" . $this->rechnungs_empfaenger_plz . " " . $this->rechnungs_empfaenger_ort . "</td>";

        echo "<td style='vertical-align: top'><b>ERFASSUNGSNR:</b><br>Rechnungsnr:<br>Rechnungsdatum:<br>Eingangsdatum:<br>Fällig am:";
        $link_pdf = "<a href='" . route('web::rechnungen::legacy', ['option' => 'anzeigen_pdf', 'belegnr' => $belegnr]) . "'><img src=\"images/pdf_light.png\"></a>";
        $link_pdf1 = "<a href='" . route('web::rechnungen::legacy', ['option' => 'anzeigen_pdf', 'belegnr' => $belegnr, 'no_logo']) . "'><img src=\"images/pdf_dark.png\"></a>";
        if ($this->status_bezahlt == '1') {
            echo "<br><b>Bezahlt am:</b>";
        }

        echo "<hr>$link_pdf $link_pdf1<hr>";

        if ($this->status_bezahlt == '0') {
            $link_zahlung_buchen = "<a href='" . route('web::rechnungen::legacy', ['option' => 'rechnung_zahlung_buchen', 'belegnr' => $belegnr]) . "'><b>Zahlung buchen</b></a>";
        } else {
            $link_zahlung_buchen = "";
        }
        if ($this->status_bestaetigt == '0') {
            $link_empfang_buchen = "<a href='" . route('web::rechnungen::legacy', ['option' => 'rechnung_empfang_buchen', 'belegnr' => $belegnr]) . "'><b>Geldempfang buchen</b></a>";
        }

        echo "</td><td valign=top align=\"left\"><b>$this->belegnr</b><br>$this->rechnungsnummer<br>$this->rechnungsdatum<br>$this->eingangsdatum<br>$this->faellig_am";
        if ($this->status_bezahlt == '1') {
            echo "<br><b>$this->bezahlt_am</b>";
        }
        echo "<br>Gezahlt:  $status_gezahlt";

        $link_details_hinzu = "<a href='" . route('web::details::legacy', ['option' => 'details_hinzu', 'detail_tabelle' => 'RECHNUNGEN', 'detail_id' => $belegnr]) . "'>Lieferschein hinzufügen</a>";

        if ($this->rechnungstyp == 'Schlussrechnung') {
            $link_teilrg_hinzu = "<a href='" . route('web::rechnungen::legacy', ['option' => 'teil_rg_hinzu', 'beleg_id' => $belegnr]) . "'>Teilrechnung hinzufügen</a>";
        } else {
            $link_teilrg_hinzu = '';
        }
        echo "<hr>$link_zahlung_freigeben<br>$link_grunddaten_aendern<br><hr>$link_zahlung_buchen<br>$link_empfang_buchen<br>WE-Nummer: $we_nummer<br>WA-Nummer: $wa_nummer<br>$link_details_hinzu<br><b>$link_teilrg_hinzu";

        echo "</div></td></tr>\n";

        echo "<tr><td colspan=4><div id=\"rechnung_beschreibung\">$this->kurzbeschreibung</div><b>$kontierungsstatus_link<hr>$kontierung_aufheben_link</b></td></tr>\n";
        echo "</table>\n";
        /* Div Firma */
        $r_rechnungs_aussteller_name = preg_replace('/<br>/', ' ', $this->rechnungs_aussteller_name);
        echo "<div id=\"div_firma\">$r_rechnungs_aussteller_name $this->rechnungs_aussteller_strasse $this->rechnungs_aussteller_hausnr - $this->rechnungs_aussteller_plz $this->rechnungs_aussteller_ort</div>";
        /* Rechnungskopf mit Grunddaten */
        /* DIV Adressfeld */
        echo "<div id=\"div_adressfeld\">$this->rechnungs_empfaenger_name<br>$this->rechnungs_empfaenger_strasse $this->rechnungs_empfaenger_hausnr<br><br>$this->rechnungs_empfaenger_plz $this->rechnungs_empfaenger_ort</div>\n";
        /* Markierung für die Brieffaltung */
        echo "<div id=\"div_faltlinie\">______";
        echo "</div>\n";

        /* DIV ADRRESSFELD */
        echo "\n<div id=\"div_rechnungsdaten\">\n";
        /* Links Überschriften/Titel */
        echo "<div id=\"rechnungsdaten_links\">\n";
        echo "<p id=\"rechnungsnummer_u\">$this->rechnungs_typ_druck:<br>$this->rechnungsnummer</p>\n";
        echo "</div>\n";
        /* Rechts daten */
        echo "<div id=\"rechnungsdaten_rechts\">\n";
        echo "<p id=\"rechnungsdatum_u\">Datum: $this->rechnungsdatum</p>\n";
        echo "<p id=\"rechnungsfaellig_u\">Fällig: $this->faellig_am</p>\n";
        // echo "<p id=\"skonto_u\">Skonto: $this->skonto %</p>\n";
        echo "</div>\n";
        echo "</div>\n";
        echo "<div id=\"div_kurzbeschreibung\">\n";
        echo "<p id=\"beschreibung_u\">$this->kurzbeschreibung</p>\n";
        echo "</div>\n";
        /* weiter geht es in function rechnung_anzeigen inkl positionen */
    }

    function rechnung_als_zugewiesen($belegnr)
    {
        $db_abfrage = "UPDATE RECHNUNGEN SET STATUS_ZUGEWIESEN='1' WHERE BELEG_NR='$belegnr' && AKTUELL='1'";
        DB::update($db_abfrage);
    }

    function rechnung_als_vollstaendig($belegnr)
    {
        $db_abfrage = "UPDATE RECHNUNGEN SET STATUS_VOLLSTAENDIG='1' WHERE BELEG_NR='$belegnr' && AKTUELL='1'";
        DB::update($db_abfrage);
    }

    /* Ermitteln der letzten Kontierungs_id */

    function rechnungs_positionen_arr($belegnr)
    {
        $result = DB::select("SELECT * FROM RECHNUNGEN_POSITIONEN WHERE BELEG_NR='$belegnr' && AKTUELL='1' ORDER BY POSITION ASC");
        $numrows = count($result);
        $this->anzahl_positionen = $numrows;
        return $result;
    }

    /* Ermitteln der letzten Kontierungs_position eines Beleges */

    function nach_link($u_beleg_nr, $art_nr, $partner_id)
    {
        $arr = $this->nach_link_arr($u_beleg_nr, $art_nr, $partner_id);
        if (!empty($arr)) {
            $anz = count($arr);
            $link = '';
            for ($a = 0; $a < $anz; $a++) {
                $beleg_nr = $arr [$a] ['BELEG_NR'];
                $menge = $arr [$a] ['MENGE'];
                $g_netto = nummer_punkt2komma_t($arr [$a] ['GESAMT_NETTO']);

                $rr = new rechnungen ();
                $rr->rechnung_grunddaten_holen($beleg_nr);
                $rr->rechnungs_empfaenger_name = substr($rr->rechnungs_empfaenger_name, 0, 30);
                $link .= "<a href='" . route('web::rechnungen::legacy', ['option' => 'rechnungs_uebersicht', 'belegnr' => $beleg_nr]) . "'><b>$rr->rechnungstyp" . ":" . "$rr->rechnungsnummer</b></a><br>$rr->rechnungs_empfaenger_name<br>$menge = $g_netto €<hr>";
            }
            return $link;
        }
    }

    function nach_link_arr($u_beleg_nr, $art_nr, $partner_id)
    {
        $result = DB::select("SELECT BELEG_NR, MENGE, GESAMT_NETTO  FROM `RECHNUNGEN_POSITIONEN` WHERE `U_BELEG_NR` = '$u_beleg_nr' AND `BELEG_NR` != '$u_beleg_nr' && ARTIKEL_NR='$art_nr' && `ART_LIEFERANT`='$partner_id' && AKTUELL='1' ORDER BY BELEG_NR ASC");
        return $result;
    }

    function position_kontierung_infos($beleg_nr, $position)
    {
        $my_array = DB::select("SELECT KONTIERUNG_ID, MENGE, EINZEL_PREIS, GESAMT_SUMME, KONTENRAHMEN_KONTO, KOSTENTRAEGER_TYP, KOSTENTRAEGER_ID FROM `KONTIERUNG_POSITIONEN` WHERE BELEG_NR = '$beleg_nr' && POSITION = '$position' && AKTUELL='1'");
        unset ($this->k_kontenrahmen_konto);
        unset ($this->k_kostentraeger_bez);
        if (!empty($result)) {
            $g = new geldkonto_info ();
            for ($a = 0; $a < 1; $a++) {
                $menge = $my_array [$a] ['MENGE'];
                $kontenrahmen_konto = $my_array [$a] ['KONTENRAHMEN_KONTO'];
                $kostentraeger_typ = $my_array [$a] ['KOSTENTRAEGER_TYP'];
                $kostentraeger_id = $my_array [$a] ['KOSTENTRAEGER_ID'];
                $kostentraeger = $this->kostentraeger_ermitteln($kostentraeger_typ, $kostentraeger_id);
                $menge = nummer_punkt2komma($menge);
                $this->k_kontierungs_menge = $menge;
                $this->k_kontenrahmen_konto = $kontenrahmen_konto;
                $this->k_kostentraeger_typ = $kostentraeger_typ;
                $this->k_kostentraeger_id = $kostentraeger_id;
                $this->k_kostentraeger_bez = $kostentraeger;

                $this->k_kostentraeger_anzahl_konten = $g->geldkonten_anzahl($kostentraeger_typ, $kostentraeger_id);
                if ($this->k_kostentraeger_anzahl_konten == '1') {
                }
            }
        }
    }

    function rechnung_footer_tabelle_anzeigen()
    {
        $skonto_in_eur = $this->rechnungs_skontoabzug;

        $skontobetrag = $this->rechnungs_skontobetrag;
        $skontobetrag = sprintf("%01.2f", $skontobetrag);
        $skontobetrag = nummer_punkt2komma($skontobetrag);

        $rechnungs_netto = nummer_punkt2komma($this->rechnungs_netto);
        $rechnungs_mwst = $this->rechnungs_brutto - $rechnungs_netto;

        $rechnungs_brutto = nummer_punkt2komma($this->rechnungs_brutto);

        $skonto_in_eur = sprintf("%01.2f", $skonto_in_eur);
        $skonto_in_eur = nummer_punkt2komma($skonto_in_eur);

        /* rechnungsfooter */
        if ($this->rechnungstyp == "Rechnung" or $this->rechnungstyp == "Buchungsbeleg" or $this->rechnungstyp == "Gutschrift" or $this->rechnungstyp == "Stornorechnung") {
            $geld_konto_info = new geldkonto_info ();
            $geld_konto_info->geld_konto_details($this->empfangs_geld_konto);
            /* Falls rechnung bezahlt */
            if ($this->status_bezahlt == "1") {
                $msg = '';
                // $msg = "Rechnungsbetrag wurde am $this->bezahlt_am gezahlt.";
            } else {
                /* Falls rechnung unbezahlt */
                $msg = '';
                // $msg = "Bitte Rechnungbetrag auf folgendes Konto ".$geld_konto_info->kontonummer." bei ".$geld_konto_info->kredit_institut." BLZ: ".$geld_konto_info->blz." überwiesen.";
            }
            if ($this->rechnungstyp == "Rechnung" or $this->rechnungstyp == "Stornorechnung") {
                $msg = 'Den Rechnungsbetrag  bitten wir auf das unten genannte Konto zu überweisen.';
                $colspan = 12;
                $colspan1 = $colspan - 2;
            }
            if ($this->rechnungstyp == "Gutschrift") {
                $msg = 'Den Rechnungsbetrag werden wir auf Ihr Konto überweisen.';
                $colspan = 10;
                $colspan1 = $colspan - 2;
            }
            if ($this->rechnungstyp == "Buchungsbeleg") {
                $colspan = 12;
                $colspan1 = $colspan - 2;
                $msg = "Den Buchungsbetrag bitten wir auf folgendes Konto zu überweisen:<br><br>";
                $msg .= "Empfänger: $geld_konto_info->konto_beguenstigter<br>";
                $msg .= "Kontonr.: $geld_konto_info->kontonummer<br>";
                $msg .= "BLZ: $geld_konto_info->blz<br>";
                $msg .= "Kreditinstitut: $geld_konto_info->kredit_institut<br>";
            }

            echo "<tr><td colspan=$colspan><hr></td></tr>";
            echo "<tr><td colspan=$colspan1 style='text-align: right'><b>Netto:</b></td><td colspan=2 style='text-align: right'>$rechnungs_netto €</td></tr>";
            $this->summe_mwst_komma = nummer_punkt2komma($this->summe_mwst);
            echo "<tr><td colspan=$colspan1 style='text-align: right'><b>MwSt:</b></td><td colspan=2 style='text-align: right'>$this->summe_mwst_komma €</td></tr>";
            echo "<tr><td colspan=$colspan1 style='text-align: right'><b>Brutto:</b></td><td colspan=2 style='text-align: right'>$rechnungs_brutto €</td></tr>";
            echo "<tr><td colspan=$colspan1 style='text-align: right'><b>Skonto:</b></td><td colspan=2 style='text-align: right'>$skonto_in_eur €</td></tr>";
            echo "<tr><td colspan=$colspan1 style='text-align: right'><b>Nach Abzug Skontobetrag:</b></td><td colspan=2 style='text-align: right'>$skontobetrag €</td></tr>";

            echo "<tr><td  colspan=$colspan id=\"footer_msg\"><br>$msg</td></tr>";
        }
        echo "</table></div>";
        // ende div_positionen für druck
    }

    function footer_zahlungshinweis($belegnr)
    {
        $this->rechnung_grunddaten_holen($belegnr);
        echo "<div id=\"div_z_hinweis\">";

        $result = DB::select("SELECT ZAHLUNGSHINWEIS FROM FOOTER_ZEILE WHERE AKTUELL = '1' && FOOTER_TYP = '$this->rechnungs_aussteller_typ' && FOOTER_TYP_ID = '$this->rechnungs_aussteller_id' ORDER BY FOOTER_DAT DESC LIMIT 0,1");
        if (!empty($result)) {
            $row = $result[0];
            echo "<p id=\"pzahlungs_hinweis\">$row[ZAHLUNGSHINWEIS]</p>";
        }
        echo "</div>";
    }

    /* Kostenträger ermitteln */

    function footer_zeilen_anzeigen($belegnr)
    {
        $this->rechnung_grunddaten_holen($belegnr);
        echo "<div id=\"div_footer_zeile\">";

        $result = DB::select("SELECT ZEILE1, ZEILE2 FROM FOOTER_ZEILE WHERE AKTUELL = '1' && FOOTER_TYP = '$this->rechnungs_aussteller_typ' && FOOTER_TYP_ID = '$this->rechnungs_aussteller_id' ORDER BY FOOTER_DAT DESC LIMIT 0,1");
        if (!empty($result)) {
            $row = $result;
            echo "<p id=\"footer_zeilenx\"><hr><center>$row[ZEILE1]<br>$row[ZEILE2]</center></p>";
        }
        echo "</div>";
    }

    /* Rechnungsfooter */

    function rechnung_zum_kontieren_anzeigen($belegnr)
    {
        $this->rechnung_grunddaten_holen($belegnr);

        /* Partnernamen holen */
        $partner_info = new partner ();
        /* Anschriften holen */
        $partner_info->get_aussteller_info($this->rechnungs_aussteller_id);
        $partner_info->get_empfaenger_info($this->rechnungs_empfaenger_id);
        /* Ende Partnernamen holen */

        $this->rechnungs_kopf($belegnr);

        $rechnungs_positionen_arr = $this->rechnungs_positionen_arr($belegnr);
        /* Rechnungspositionen Überschrift */
        if ($this->anzahl_positionen > 0) {
            echo "<table class=positionen>\n";
            //echo "<form method=\"post\" name=\"myform\">\n";
            echo "<tr>\n";
            $this->dropdown_kostentreager_typen();
            echo "</tr>\n";
            $kt = new kontenrahmen ();
            echo "<tr>\n";
            $kt->dropdown_kontenrahmen('Kontenrahmen', 'kontenrahmen', 'kontenrahmen', '');
            echo "</tr>\n";
            echo "<tr><td colspan=9><b>Für die Kontierung wählen Sie bitte alle zusammenhängenden Positionen aus!!!</b></td></tr>\n";
            echo "<tr class=feldernamen><td><input type=\"checkbox\" class='filled-in' id='alle' onClick=\"check_all_boxes(this.checked, 'positionen_list_')\"><label for='alle'>Alle</label></td><td>Pos</td><td>Artikelnr</td><td>Bezeichnung</td><td>Menge</td><td>Restmenge</td><td width=80>LP</td><td width=80>EP</td><td>Rabatt</td><td>Skonto</td><td align=right>MWSt %</td><td width=80>Netto</td></tr>\n";

            /* Rechnungspositionen */
            for ($a = 0; $a < count($rechnungs_positionen_arr); $a++) {
                $position = $rechnungs_positionen_arr [$a] ['POSITION'];
                $menge = $rechnungs_positionen_arr [$a] ['MENGE'];
                $einzel_preis = $rechnungs_positionen_arr [$a] ['PREIS'];
                $mwst_satz = $rechnungs_positionen_arr [$a] ['MWST_SATZ'];
                $rabatt_satz = $rechnungs_positionen_arr [$a] ['RABATT_SATZ'];
                $skonto = $rechnungs_positionen_arr [$a] ['SKONTO'];
                $gesamt_preis = $rechnungs_positionen_arr [$a] ['GESAMT_NETTO'];
                $artikel_nr = $rechnungs_positionen_arr [$a] ['ARTIKEL_NR'];
                $art_lieferant = $rechnungs_positionen_arr [$a] ['ART_LIEFERANT'];
                $kontierte_menge = $this->position_auf_kontierung_pruefen($belegnr, $position);
                $restmenge = $menge - $kontierte_menge;

                /* Infos aus Katalog zu Artikelnr */
                $artikel_info_arr = $this->artikel_info($art_lieferant, $rechnungs_positionen_arr [$a] ['ARTIKEL_NR']);
                for ($i = 0; $i < count($artikel_info_arr); $i++) {
                    if (!empty ($artikel_info_arr [$i] ['BEZEICHNUNG'])) {
                        $bezeichnung = $artikel_info_arr [$i] ['BEZEICHNUNG'];
                        $listenpreis = $artikel_info_arr [$i] ['LISTENPREIS'];
                    } else {
                        $bezeichnung = 'Unbekannt';
                        $listenpreis = '0,00';
                        $rabatt_satz = '0';
                    }
                    $menge = nummer_punkt2komma($menge);

                    $einzel_preis = nummer_punkt2komma($einzel_preis);
                    $listenpreis = nummer_punkt2komma($listenpreis);
                    $mwst_satz = nummer_punkt2komma($mwst_satz);
                    $gesamt_preis = nummer_punkt2komma($gesamt_preis);
                    echo "<tr border=1><td>\n";
                    if ($restmenge > 0) {
                        echo "<input type=\"checkbox\" class='filled-in' id='positionen_list_$position' name=\"positionen_list[]\" value=\"$position\"><label for='positionen_list_$position'>$position</label>\n";
                        $send_button_anzeigen = true;
                    }
                    $restmenge = nummer_punkt2komma($restmenge);
                    echo "</td><td valign=top><b>$position.</td><td valign=top>$artikel_nr</td><td valign=top>$bezeichnung</td><td align=right valign=top>$menge</td><td align=right valign=top>$restmenge</td><td align=right valign=top>$listenpreis €</td><td align=right valign=top>$einzel_preis €</td><td align=right valign=top>$rabatt_satz %</td><td align=right valign=top>$skonto %</td><td align=right valign=top>$mwst_satz</td><td width=90 align=right valign=top>$gesamt_preis €</td></tr>\n";
                    if ($kontierte_menge > 0) {
                        echo "<tr><td><b>K</b><td><td colspan=10>\n";
                        $this->position_kontierung_anzeigen($belegnr, $position);
                        echo "</td></tr>\n";
                    }
                } // end for 2
            } // end for 1
            if (isset ($send_button_anzeigen)) {
                echo "<input type=\"hidden\" name=\"beleg_nr\" value=\"$this->belegnr\">\n";
                echo "<tr><td colspan='4'><button class='btn waves-effect waves-light' type='submit' name='action'>Kontieren
                <i class='mdi mdi-send right'></i>
                </button></td></tr>\n";
            }
        }  // ende if $this->anzahl_positionen >0 d.h. Rechnung wurde nur kurz erfasst, positionen fehlen
        /* Positionen erfassen */
        else {
            echo "<table class=rechnung><tr><td>\n";
            $rechnung_info = new rechnung ();
            $rechnung_info->positionen_eingabe_form($belegnr);
            echo "</td></tr></table>";
        }
        /* Rechnungsfooter */
        $this->rechnung_footer_tabelle_anzeigen();
    } /* ende rechnungsfootoer */

    /* Rechnungsfooter bei Positionseingabe */

    function dropdown_kostentreager_typen()
    {
        echo "<div class='input-field'>";
        echo "<select id='kosten_traeger_typ' name=\"kosten_traeger_typ\" size=1>\n";
        echo "<option value=\"Objekt\">Objekt</option>\n";
        echo "<option value=\"Haus\">Haus</option>\n";
        echo "<option value=\"Einheit\">Einheit</option>\n";
        echo "<option value=\"Partner\">Partner/Mieter</option>\n";
        echo "<option value=\"Lager\">Lager</option>\n";
        echo "</select><label for='kosten_traeger_typ'>Kostenträger-Typ</label>\n";
        echo "</div>";
    } /* ende rechnungsfootoer */

    /* Rechnungspositionen finden */

    function position_auf_kontierung_pruefen($beleg_nr, $position)
    {
        $result = DB::select("SELECT SUM( MENGE ) AS KONTIERTE_MENGE FROM `KONTIERUNG_POSITIONEN` WHERE BELEG_NR = '$beleg_nr' && POSITION = '$position' && AKTUELL='1'");
        $row = $result[0];
        $kontierte_menge = $row ['KONTIERTE_MENGE'];
        return $kontierte_menge;
    }

    /* Rechnung Position löschen bzw. deaktivieren */

    function position_kontierung_anzeigen($beleg_nr, $position)
    {
        $my_array = DB::select("SELECT KONTIERUNG_DAT, KONTIERUNG_ID, MENGE, EINZEL_PREIS, GESAMT_SUMME, KONTENRAHMEN_KONTO, KOSTENTRAEGER_TYP, KOSTENTRAEGER_ID FROM `KONTIERUNG_POSITIONEN` WHERE BELEG_NR = '$beleg_nr' && POSITION = '$position' && AKTUELL='1'");
        $numrows = count($my_array);
        if ($numrows > 0) {
            echo "<hr>\n";
            for ($a = 0; $a < $numrows; $a++) {
                $dat = $my_array [$a] ['KONTIERUNG_DAT'];
                $id = $my_array [$a] ['KONTIERUNG_ID'];
                $menge = $my_array [$a] ['MENGE'];
                $kontenrahmen_konto = $my_array [$a] ['KONTENRAHMEN_KONTO'];
                $kostentraeger_typ = $my_array [$a] ['KOSTENTRAEGER_TYP'];
                $kostentraeger_id = $my_array [$a] ['KOSTENTRAEGER_ID'];
                $kostentraeger = $this->kostentraeger_ermitteln($kostentraeger_typ, $kostentraeger_id);
                $menge = nummer_punkt2komma($menge);
                $link_aufhebung = "<a href='" . route('web::rechnungen::legacy', ['option' => 'pos_kontierung_aufheben', 'belegnr' => $beleg_nr, 'dat' => $dat, 'id' => $id]) . "''>Kontierung aufheben</a>";
                echo "<p id=\"pos_kontierung\">$menge $kontenrahmen_konto $kostentraeger_typ $kostentraeger $link_aufhebung</p>\n";
            }
            echo "<hr>\n";
        }
    }

    /* Artikelinformationen aus dem Katalog holen */

    function positionen_eingabe_form($belegnr)
    {
        $this->rechnung_grunddaten_holen($belegnr);
        $form = new mietkonto ();
        // echo "$rechnung_id $partner";
        // echo "<table border=1><tr><td>\n";
        $form->erstelle_formular("Positionsanzahl eingeben", NULL);
        // echo "Geben Sie bitte die Anzahl der Positionen für die Rechnung $this->rechnungsnummer.<br>\n";
        $form->text_feld("Anzahl der Positionen:", "anzahl_positionen", "", "3");
        $form->hidden_feld("option", "send_positionen");
        $form->send_button("submit_position", "Senden");
        $form->ende_formular();
        // echo "</td></tr></table>\n";
    }

    /* Artikelnummern aus dem Katalog des Partner/Lieferanten holen */

    function kontierungstabelle_anzeigen($beleg_nr, $positionen_arr, $kostentraeger_typ)
    {
        $this->rechnung_grunddaten_holen($beleg_nr);
        $form = new mietkonto ();
        $rechnung = new rechnung ();
        $this->rechnungs_kopf($beleg_nr, $kostentraeger_typ);
        $rechnungs_positionen_arr = $this->rechnungs_positionen_arr($beleg_nr);
        $anzahl_pos_beleg = count($rechnungs_positionen_arr);
        $anzahl_pos_zu_kontierung = count($positionen_arr);
        echo "<table>\n";
        echo "<tr class=feldernamen><td>Pos</td><td>Artikelnr</td><td>Bezeichnung</td><td>Menge</td><td>LP </td><td>EP</td><td align=right>Rabatt</td><td align=right>MWSt</td><td width=90>Gesamt</td><td>Konto</td><td>Kostenträger</td><td>Weiter verwenden</td><td>Verwendung im Jahr</td></tr>\n";

        echo "<tr class=feldernamen><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td align=right></td><td width=90></td><td><a class='waves-effect waves-teal btn' onclick=\"auswahl_alle(document.getElementsByClassName('Rechnungsübersicht')[1]['kontenrahmen_konto'])\">Alle</a>  
	</td><td><a class='waves-effect waves-teal btn' onclick=\"auswahl_alle(document.getElementsByClassName('Rechnungsübersicht')[1]['kostentraeger'])\">Alle</a></td><td><a class='waves-effect waves-teal btn' onclick=\"auswahl_alle(document.getElementsByClassName('Rechnungsübersicht')[1]['weiter_verwenden'])\">Alle</a></td><td><a class='waves-effect waves-teal btn' onclick=\"auswahl_alle(document.getElementsByClassName('Rechnungsübersicht')[1]['verwendungs_jahr'])\">Alle</a>
	</td></tr>\n";

        for ($a = 0; $a < $anzahl_pos_zu_kontierung; $a++) {
            $zeilennr = $a;
            $kontierungs_position = $positionen_arr [$a];

            for ($i = 0; $i < $anzahl_pos_beleg; $i++) {
                if ($kontierungs_position == $rechnungs_positionen_arr [$i] ['POSITION']) {
                    $position = $rechnungs_positionen_arr [$i] ['POSITION'];
                    $ursprungs_menge = $rechnungs_positionen_arr [$i] ['MENGE'];
                    $kontierte_menge = $this->position_auf_kontierung_pruefen($beleg_nr, $position);
                    $menge = $ursprungs_menge - $kontierte_menge;
                    $menge = nummer_punkt2komma($menge);
                    $einzel_preis = $rechnungs_positionen_arr [$i] ['PREIS'];
                    $einzel_preis = nummer_punkt2komma($einzel_preis);
                    $mwst_satz = $rechnungs_positionen_arr [$i] ['MWST_SATZ'];
                    $rabatt_satz = $rechnungs_positionen_arr [$i] ['RABATT_SATZ'];
                    $skonto = $rechnungs_positionen_arr [$i] ['SKONTO'];
                    $skonto = nummer_punkt2komma($skonto);

                    $gesamt_preis = $rechnungs_positionen_arr [$i] ['GESAMT_NETTO'];
                    $gesamt_preis = nummer_punkt2komma($gesamt_preis);
                    $artikel_nr = $rechnungs_positionen_arr [$i] ['ARTIKEL_NR'];

                    /* Infos aus Katalog zu Artikelnr */
                    $artikel_info_arr = $this->artikel_info($this->rechnungs_aussteller_id, $artikel_nr);
                    if (isset ($artikel_info_arr [0] ['BEZEICHNUNG'])) {
                        $bezeichnung = $artikel_info_arr [0] ['BEZEICHNUNG'];
                        $listenpreis = $artikel_info_arr [0] ['LISTENPREIS'];
                        $listenpreis = nummer_punkt2komma($listenpreis);
                    } else {
                        $bezeichnung = 'Unbekannt';
                        $listenpreis = '0,00';
                    }
                    $neue_position = $a + 1;
                    echo "<tr><td valign=top>$neue_position.$kontierungs_position</td><td valign=top>$artikel_nr</td><td valign=top>$bezeichnung</td><td align=right valign=top>\n";
                    $form->text_feld("Menge ($menge)", "gesendet[$neue_position][KONTIERUNGS_MENGE]=>'$neue_position'", $menge, 5);
                    echo "</td><td align=right valign=top>$listenpreis €</td><td align=right valign=top>$einzel_preis €</td><td align=right valign=top>$rabatt_satz %</td><td align=right valign=top>$mwst_satz %</td><td width=90 align=right valign=top>$gesamt_preis €</td><td>\n";

                    /* Wegen der Rechnungskontierung muss hier der Kontenrahmen für alle angezeigt werden */
                    $bu = new buchen ();
                    $kontenrahmen_id = request()->input('kontenrahmen');
                    if (!empty ($kontenrahmen_id)) {
                        $kt = new kontenrahmen ();
                        $kt->dropdown_konten_vom_rahmen('Kostenkonto', "gesendet[$neue_position][KONTENRAHMEN_KONTO]=>'$neue_position", "kontenrahmen_konto", '', $kontenrahmen_id);
                    } else {
                        $bu->dropdown_kostenrahmen_nr('Kostenkonto', "gesendet[$neue_position][KONTENRAHMEN_KONTO]=>'$neue_position", '', '', '');
                    }
                    echo "</td><td>\n";
                    $rechnung->dropdown_kostentreager_liste($kostentraeger_typ, "gesendet[$neue_position][KOSTENTRAEGER_ID]=>'$neue_position'", $this->rechnungs_aussteller_id);
                    $form->hidden_feld("gesendet[$neue_position][KOSTENTRAEGER_TYP]=>'$neue_position'", $kostentraeger_typ);
                    $form->hidden_feld("gesendet[$neue_position][KONTIERUNGS_POSITION]=>'$neue_position'", $kontierungs_position);
                    $form->hidden_feld("gesendet[$neue_position][URSPRUNG_MENGE]=>'$neue_position'", $menge);
                    $form->hidden_feld("gesendet[$neue_position][MWST_SATZ]=>'$neue_position'", $mwst_satz);
                    $form->hidden_feld("gesendet[$neue_position][RABATT_SATZ]=>'$neue_position'", $rabatt_satz);
                    $form->hidden_feld("gesendet[$neue_position][SKONTO]=>'$neue_position'", $skonto);
                    $form->hidden_feld("gesendet[$neue_position][EINZEL_PREIS]=>'$neue_position'", $einzel_preis);
                    $form->hidden_feld("gesendet[$neue_position][GESAMT_PREIS]=>'$neue_position'", $gesamt_preis);
                    echo "</td><td>";
                    $this->weiter_verwenden_dropdown("gesendet[$neue_position][WEITER_VERWENDEN]=>'$neue_position'");
                    echo "</td><td>";
                    $this->verwendungs_jahr_dropdown("gesendet[$neue_position][VERWENDUNGS_JAHR]=>'$neue_position'");
                    echo "</td></tr>\n";
                } // end if
            } // end for $i
        } // end for $a

        echo "<tr><td colspan='3'>\n";
        $form->hidden_feld('BELEG_NR', $beleg_nr);
        $form->hidden_feld('option', 'KONTIERUNG_SENDEN');
        $form->send_button('', 'Kontierung übernehmen');
        echo "</td></tr>\n";
        echo "</table>\n";
        echo "<table>\n";
        echo "<tr><td>Im Beleg $beleg_nr befinden sich $anzahl_pos_beleg Positionen.</td></tr>\n";
        echo "<tr><td>$anzahl_pos_zu_kontierung von $anzahl_pos_beleg Positionen aus Beleg $beleg_nr haben Sie ausgewählt.</td></tr>\n";
        echo "</table>\n";
    }

    /* Artikelnummer, Lieferant aus einem Beleg holen, darauf die Bezeichnung aus dem Katalog des Partner/Lieferanten holen */

    function dropdown_kostentreager_liste($kostentraeger_typ, $name, $vorwahl_id = null)
    {
        if ($kostentraeger_typ == 'Objekt') {
            $objekte = new objekt ();
            $objekte->dropdown_objekte($name, 'kostentraeger');
        }
        if ($kostentraeger_typ == 'Haus') {
            $haeuser = new haus ();
            $haeuser->dropdown_haeuser($name, 'kostentraeger');
        }
        if ($kostentraeger_typ == 'Einheit') {
            $einheiten = new einheit ();
            $einheiten->dropdown_einheiten($name, 'kostentraeger');
        }
        if ($kostentraeger_typ == 'Partner') {
            $partner_info = new partner ();
            $partner_info->partner_dropdown('Kostenträger', $name, 'kostentraeger', $vorwahl_id);
        }
        if ($kostentraeger_typ == 'Lager') {
            $lager_info = new lager ();
            $lager_info->lager_dropdown("Lager", $name, 'kostentraeger');
        }
    }

    /* Ermitteln der letzten Artikel_nr/Leistungnr eines Lieferanten */

    function weiter_verwenden_dropdown($name)
    {
        echo "<select name=\"$name\" size=\"1\" id=\"weiter_verwenden\">\n";

        echo "<option name=\"$name\" value=\"1\" selected>JA</OPTION>\n";
        echo "<option name=\"$name\" value=\"0\">NEIN</OPTION>\n";
        echo "</select>\n";
    }

    /* Ermitteln der letzten Artikel_nr/Leistungnr eines Lieferanten nach Bezeichnung */

    function verwendungs_jahr_dropdown($name)
    {
        echo "<select name=\"$name\" size=\"1\" id=\"verwendungs_jahr\">\n";
        $akt_jahr = date("Y");
        $anfangs_jahr = $akt_jahr - 3;
        $end_jahr = $akt_jahr + 2;
        for ($a = $anfangs_jahr; $a <= $end_jahr; $a++) {
            if ($a == $akt_jahr) {
                echo "<option name=\"$name\" value=\"$a\" selected>$a</OPTION>\n";
            } else {
                echo "<option name=\"$name\" value=\"$a\">$a</OPTION>\n";
            }
        }
        echo "</select>\n";
    }

    /* Ermitteln der letzten katalog_id */

    function kontierung_pruefen()
    {
        for ($a = 1; $a <= count(request()->input('gesendet')); $a++) {
            $kontierungs_menge = nummer_komma2punkt(request()->input('gesendet') [$a] ['KONTIERUNGS_MENGE']);
            $ursprung_menge = nummer_komma2punkt(request()->input('gesendet') [$a] ['URSPRUNG_MENGE']);
            if ($kontierungs_menge > $ursprung_menge) {
                $error = true;
            } else {
                $error = false;
            }
        }
        return $error;
    }

    /* Neuen Artikel/Leistung zum Lieferanten hinzufügen, wenn keine Artikelnummer eingegeben wurde, es wird eine neue vergeben */

    function kontierung_speichern()
    {
        $datum = date("Y-m-d");

        for ($a = 1; $a <= count(request()->input('gesendet')); $a++) {
            $kontierung_id = $this->get_last_kontierung_id();
            $kontierung_id = $kontierung_id + 1;

            $beleg_nr = request()->input('BELEG_NR');
            $kontierungs_menge = request()->input('gesendet') [$a] ['KONTIERUNGS_MENGE'];
            $kontierungs_menge = nummer_komma2punkt($kontierungs_menge);
            $kontenrahmen_konto = request()->input('gesendet') [$a] ['KONTENRAHMEN_KONTO'];
            $kostentraeger_id = request()->input('gesendet') [$a] ['KOSTENTRAEGER_ID'];
            $kostentraeger_typ = request()->input('gesendet') [$a] ['KOSTENTRAEGER_TYP'];
            $kontierungs_pos = request()->input('gesendet') [$a] ['KONTIERUNGS_POSITION'];
            $einzel_preis = request()->input('gesendet') [$a] ['EINZEL_PREIS'];
            $einzel_preis = nummer_komma2punkt($einzel_preis);
            $gesamt_preis = $kontierungs_menge * $einzel_preis;
            // $gesamt_preis = nummer_komma2punkt($gesamt_preis);
            $verwendungs_jahr = request()->input('gesendet') [$a] ['VERWENDUNGS_JAHR'];
            $weiter_verwenden = request()->input('gesendet') [$a] ['WEITER_VERWENDEN'];
            $mwst_satz = request()->input('gesendet') [$a] ['MWST_SATZ'];
            $rabatt_satz = request()->input('gesendet') [$a] ['RABATT_SATZ'];
            $skonto = nummer_komma2punkt(request()->input('gesendet') [$a] ['SKONTO']);
            $db_abfrage = "INSERT INTO KONTIERUNG_POSITIONEN VALUES (NULL, '$kontierung_id','$beleg_nr', '$kontierungs_pos','$kontierungs_menge', '$einzel_preis', '$gesamt_preis', '$mwst_satz', '$skonto', '$rabatt_satz', '$kontenrahmen_konto', '$kostentraeger_typ', '$kostentraeger_id', '$datum', '$verwendungs_jahr', '$weiter_verwenden', '1')";
            DB::insert($db_abfrage);
            /* Protokollieren */
            $last_dat = DB::getPdo()->lastInsertId();
            protokollieren('KONTIERUNG_POSITIONEN', $last_dat, '0');
        }
        $anzahl_positionen = count(request()->input('gesendet'));
        hinweis_ausgeben("$anzahl_positionen Position (-en) wurde (-n) kontiert");
        weiterleiten_in_sec(route('web::rechnungen::legacy', ['option' => 'rechnung_kontieren', 'belegnr' => $beleg_nr], false), 1);
    }

    /* Neuen Artikel/Leistung zum Lieferanten hinzufügen, wenn eine Artikelnummer eingegeben wurde, es wird mit der eingegebenen artikel_nr gespeichert */

    function get_last_kontierung_id()
    {
        $result = DB::select("SELECT KONTIERUNG_ID FROM KONTIERUNG_POSITIONEN WHERE  AKTUELL='1' ORDER BY KONTIERUNG_ID DESC LIMIT 0,1");
        $row = $result[0];
        return $row ['KONTIERUNG_ID'];
    }

    /* Funktion zur Darstellung der Artikel bzw. (Leistungen) eines Lieferanten/Partners in einer Tabelle */

    function rechnung_footer_tabelle_anzeigen_pe()
    {
        $skonto_in_eur = $this->rechnungs_skontoabzug;

        $skontobetrag = $this->rechnungs_skontobetrag;
        $skontobetrag = sprintf("%01.2f", $skontobetrag);
        $skontobetrag = nummer_punkt2komma($skontobetrag);

        $rechnungs_netto = nummer_punkt2komma($this->rechnungs_netto);
        $rechnungs_mwst = $this->rechnungs_brutto - $rechnungs_netto;
        $rechnungs_mwst = nummer_punkt2komma($rechnungs_mwst);

        $rechnungs_brutto = nummer_punkt2komma($this->rechnungs_brutto);

        $skonto_in_eur = sprintf("%01.2f", $skonto_in_eur);
        $skonto_in_eur = nummer_punkt2komma($skonto_in_eur);

        /* rechnungsfooter */
        if ($this->rechnungstyp == "Rechnung" or $this->rechnungstyp == "Buchungsbeleg") {
            $geld_konto_info = new geldkonto_info ();
            $geld_konto_info->geld_konto_details($this->empfangs_geld_konto);
            $msg = 'Den Rechnungsbetrag  bitten wir auf das unten genannte Konto zu überweisen.';
            echo "</table><table width=100% >";
            echo "<tr><td align=right valign=top><b>Netto:</b></td><td align=right valign=top>$rechnungs_netto €</td></tr>";
            $this->summe_mwst_komma = nummer_punkt2komma($this->summe_mwst);
            echo "<tr><td  align=right valign=top><b>MwSt:</b></td><td align=right valign=top>$this->summe_mwst_komma €</td></tr>";
            echo "<tr><td  align=right valign=top><b>Brutto:</b></td><td align=right valign=top>$rechnungs_brutto €</td></tr>";
            echo "<tr><td  align=right valign=top><b>Skonto:</b></td><td align=right valign=top>$skonto_in_eur €</td></tr>";
            echo "<tr><td  align=right valign=top><b>Nach Abzug Skontobetrag:</b></td><td valign=top align=right>$skontobetrag €</td></tr>";

            echo "<tr><td   valign=top id=\"footer_msg\"><br>$msg</td></tr></table>";
        }
    }

    /* Maske zum Vervollständigen von Rechnungen d.h. Eingabe von Positionen */

    function position_deaktivieren($pos, $belegnr)
    {
        DB::update("UPDATE RECHNUNGEN_POSITIONEN SET AKTUELL='0' WHERE BELEG_NR='$belegnr' && AKTUELL='1' && POSITION='$pos'");
    }

    /* Positionen einer Rechnung speichern */

    function artikel_leistung_mit_artikelnr_speichern($partner_id, $bezeichnung, $listenpreis, $artikel_nr, $rabatt, $einheit, $mwst, $pos_skonto)
    {
        $letzte_kat_id = $this->get_last_katalog_id();
        $letzte_kat_id = $letzte_kat_id + 1;

        $bezeichnung = stripslashes($bezeichnung);
        $db_abfrage = "INSERT INTO POSITIONEN_KATALOG VALUES (NULL, '$letzte_kat_id','$partner_id', '$artikel_nr','$bezeichnung', '$listenpreis', '$rabatt', '$einheit', '$mwst', '$pos_skonto','1')";
        DB::insert($db_abfrage);
        /* Protokollieren */
        $last_dat = DB::getPdo()->lastInsertId();
        protokollieren('POSITIONEN_KATALOG', $last_dat, '0');
        return $this->get_last_artikelnr($partner_id);
    }

    /* Positionen einer automatisch erstellten Rechnung speichern */

    function get_last_katalog_id()
    {
        $result = DB::select("SELECT KATALOG_ID FROM POSITIONEN_KATALOG WHERE AKTUELL='1' ORDER BY KATALOG_ID DESC LIMIT 0,1");
        $row = $result[0];
        return $row ['KATALOG_ID'];
    }

    function get_last_artikelnr($partner_id)
    {
        $result = DB::select("SELECT ARTIKEL_NR FROM POSITIONEN_KATALOG WHERE ART_LIEFERANT='$partner_id' && AKTUELL='1' ORDER BY KATALOG_DAT DESC LIMIT 0,1");
        $row = $result[0];
        return $row ['ARTIKEL_NR'];
    }

    function artikel_leistungen_block($partner_id)
    {
        $partner_info = new partner ();
        $partner_name = $partner_info->get_partner_name($partner_id);
        $katalog_arr = $this->artikel_leistungen_arr($partner_id);
        if (!empty($katalog_arr)) {
            echo "<div class=\"tabelle\">\n";
            echo $partner_name;
            echo "<table>\n";
            echo "<tr><td>ArtNr</td><td>Bezeichnung</td><td>LP</td><td>UP</td><td>Rabatt</td></tr>\n";
            for ($a = 0; $a < count($katalog_arr); $a++) {
                $listenpreis = nummer_punkt2komma($katalog_arr [$a] ['LISTENPREIS']);
                $rabatt_satz = $katalog_arr [$a] ['RABATT_SATZ'];
                $unser_preis = $listenpreis - (($listenpreis / 100) * $rabatt_satz);
                $javascript_link = "<a href=\"javascript:pos_fuellen('" . $katalog_arr [$a] ['ARTIKEL_NR'] . "','" . $katalog_arr [$a] ['BEZEICHNUNG'] . "', '" . $listenpreis . "');\">" . $katalog_arr [$a] ['ARTIKEL_NR'] . "</a>\n";
                echo "<tr><td>$javascript_link</td><td>" . $katalog_arr [$a] ['BEZEICHNUNG'] . "</td><td>$listenpreis €</td><td>$unser_preis</td><td><b>$rabatt_satz %</b></td></tr>\n";
            }
            // echo "<tr><td>".$katalog_arr[$a][ARTIKEL_NR]."</td><td>".$katalog_arr[$a][BEZEICHNUNG]."</td><td>".$katalog_arr[$a][LISTENPREIS]."</td></tr>\n";

            echo "</table>\n";
            echo "</div>\n";
        } else {
            echo "<div class=\"tabelle\">\n";
            echo "$partner_name <br>Keine Artikel / Leistungen vorhanden";
            echo "</div>\n";
        }
    }

    function artikel_leistungen_arr($partner_id)
    {
        $result = DB::select("SELECT ARTIKEL_NR, BEZEICHNUNG, LISTENPREIS, RABATT_SATZ FROM POSITIONEN_KATALOG WHERE ART_LIEFERANT='$partner_id'  && AKTUELL='1' ORDER BY ARTIKEL_NR ASC");
        return $result;
    }

    function positionen_speichern($belegnr)
    {
        $this->rechnung_grunddaten_holen($belegnr);
        $this->rechnung_grunddaten_holen(request()->input('rechnung_id'));
        if ($this->rechnungs_empfaenger_typ != 'Kasse') {
            $empfangs_geld_konto = request()->input('geld_konto');
        } else {
            $empfangs_geld_konto = '0';
        }
        if (!isset ($empfangs_geld_konto)) {
            echo "Kein Geldkonto ausgewählt";
        } else {

            /* Update der erfassten Rechung um die ausgewählte Kontonummer des rechnungaustellers mitzuteilen */
            if ($this->rechnungs_empfaenger_typ != 'Kasse') {
                $db_abfrage = "UPDATE RECHNUNGEN SET EMPFANGS_GELD_KONTO='$empfangs_geld_konto' WHERE BELEG_NR='$belegnr' && AKTUELL='1' ";
            } else {
                $zahlungs_datum = date_german2mysql($this->bezahlt_am);
                $db_abfrage = "UPDATE RECHNUNGEN SET EMPFANGS_GELD_KONTO='$empfangs_geld_konto', STATUS_ZAHLUNG_FREIGEGEBEN='1', STATUS_BEZAHLT='1', BEZAHLT_AM='$zahlungs_datum'  WHERE BELEG_NR='$belegnr' && AKTUELL='1' ";
            }
            DB::update($db_abfrage);
            /* Protokollieren von update */
            $last_dat = DB::getPdo()->lastInsertId();
            protokollieren('RECHNUNGEN', $last_dat, $last_dat);
            echo "Dem Beleg $belegnr wurde die Kontonummer des Rechnungsausteller hinzugefügt<br>\n";

            /* Durchlauf von positionen */
            for ($a = 1; $a <= count(request()->input('positionen')); $a++) {
                $letzte_rech_pos_id = $this->get_last_rechnung_pos_id();
                $letzte_rech_pos_id = $letzte_rech_pos_id + 1;

                /* Wenn Artikelnr eingegeben */
                if (request()->has('positionen.' . $a . '.artikel_nr')) {
                    $pos_preis = nummer_komma2punkt(request()->input('positionen') [$a] ['preis']);
                    $pos_menge = nummer_komma2punkt(request()->input('positionen') [$a] ['menge']);
                    $pos_mwst_satz = nummer_komma2punkt(request()->input('positionen') [$a] ['pos_mwst_satz']);
                    $pos_rabatt = request()->input('positionen') [$a] ['pos_rabatt'];
                    $pos_gesamt_netto = nummer_komma2punkt(request()->input('positionen') [$a] ['gpreis']);

                    $db_abfrage = "INSERT INTO RECHNUNGEN_POSITIONEN VALUES (NULL, '$letzte_rech_pos_id', '$a', '$belegnr','$this->rechnungs_aussteller_id','" . request()->input('positionen') [$a] ['artikel_nr'] . "', '$pos_menge','$pos_preis','$pos_mwst_satz', '$pos_rabatt', '$pos_gesamt_netto','1')";

                    DB::insert($db_abfrage);
                    /* Protokollieren */
                    $last_dat = DB::getPdo()->lastInsertId();
                    protokollieren('RECHNUNGEN_POSITIONEN', $last_dat, '0');
                    echo "Position $a wurde gespeichert <br>\n";
                }  // end if

                /* Wenn keine Artikelnummer eingegeben ->Artikel anlegen */
                else {
                    $pos_rabatt = nummer_komma2punkt(request()->input('positionen') [$a] ['pos_rabatt']);
                    $this->artikel_leistung_speichern($this->rechnungs_aussteller_id, request()->input('positionen') [$a] ['bezeichnung'], request()->input('positionen') [$a] ['preis'], $pos_rabatt);
                    $neue_artikel_nr = $this->get_last_artikelnr_nach_bezeichnung($this->rechnungs_aussteller_id, request()->input('positionen') [$a] ['bezeichnung']);

                    $pos_preis = nummer_komma2punkt(request()->input('positionen') [$a] ['preis']);
                    $pos_mwst_satz = nummer_komma2punkt(request()->input('positionen') [$a] ['pos_mwst_satz']);
                    $pos_rabatt = request()->input('positionen') [$a] ['pos_rabatt'];
                    $pos_gesamt_netto = nummer_komma2punkt(request()->input('positionen') [$a] ['gpreis']);

                    $db_abfrage = "INSERT INTO RECHNUNGEN_POSITIONEN VALUES (NULL, '$letzte_rech_pos_id', '$a', '$belegnr','$neue_artikel_nr', '" . request()->input('positionen') [$a] ['menge'] . "','$pos_preis','$pos_mwst_satz', '$pos_rabatt', '$pos_gesamt_netto','1')";

                    DB::insert($db_abfrage);
                    /* Protokollieren */
                    $last_dat = DB::getPdo()->lastInsertId();
                    protokollieren('RECHNUNGEN_POSITIONEN', $last_dat, '0');
                    echo "Position $a ($neue_artikel_nr) " . request()->input('positionen') [$a] ['bezeichnung'] . " wurde gespeichert<br>\n";
                }
            } // end for
            /* Rechnung als vollständig markieren */
            $this->rechnung_als_vollstaendig($belegnr);
            weiterleiten_in_sec(route('web::rechnungen::legacy', ['option' => 'rechnungs_uebersicht', 'belegnr' => $belegnr], false), 2);
        } // end else kein konto
    }

    function get_last_rechnung_pos_id()
    {
        $result = DB::select("SELECT RECHNUNGEN_POS_ID FROM RECHNUNGEN_POSITIONEN ORDER BY RECHNUNGEN_POS_ID DESC LIMIT 0,1");
        $row = $result[0];
        return $row ['RECHNUNGEN_POS_ID'];
    }

    function artikel_leistung_speichern($partner_id, $bezeichnung, $listenpreis, $rabatt, $einheit, $mwst)
    {
        $letzte_kat_id = $this->get_last_katalog_id();
        $letzte_kat_id = $letzte_kat_id + 1;
        $letzte_artikel_nr = $this->get_last_artikelnr($partner_id);
        $letzte_artikel_nr = $letzte_artikel_nr + 1;

        $db_abfrage = "INSERT INTO POSITIONEN_KATALOG VALUES (NULL, '$letzte_kat_id','$partner_id', '$letzte_artikel_nr','$bezeichnung', '$listenpreis', '$rabatt', '$einheit', '$mwst', '1')";
        DB::insert($db_abfrage);
        return $this->get_last_artikelnr($partner_id);
    }

    function get_last_artikelnr_nach_bezeichnung($partner_id, $bezeichnung)
    {
        $result = DB::select("SELECT ARTIKEL_NR FROM POSITIONEN_KATALOG WHERE PARTNER_ID='$partner_id' && BEZEICHNUNG='$bezeichnung' && AKTUELL='1' ORDER BY ARTIKEL_NR DESC LIMIT 0,1");
        $row = $result[0];
        return $row ['ARTIKEL_NR'];
    }

    function auto_positionen_speichern($belegnr, $positionen)
    {
        $this->rechnung_grunddaten_holen($belegnr);

        for ($a = 0; $a < count($positionen); $a++) {
            $letzte_rech_pos_id = $this->get_last_rechnung_pos_id();
            $letzte_rech_pos_id = $letzte_rech_pos_id + 1;
            $zeile = $a + 1;

            $einzel_preis = $positionen [$a] ['preis'];
            $menge = $positionen [$a] ['menge'];
            $skonto = $positionen [$a] ['skonto'];

            $menge = nummer_komma2punkt($menge);
            $einzel_preis = nummer_komma2punkt($einzel_preis);
            $skonto = nummer_komma2punkt($skonto);

            $u_beleg_nr = $positionen [$a] ['beleg_nr'];
            $u_position = $positionen [$a] ['position'];
            $pos_rabatt_satz = $positionen [$a] ['rabatt_satz'];
            $pos_rabatt_satz = nummer_komma2punkt($pos_rabatt_satz);

            $gpreis = $einzel_preis * $menge;
            $gpreis = ($gpreis / 100) * (100 - $pos_rabatt_satz);
            $ursprungs_artikel_nr = $this->art_nr_from_beleg($u_beleg_nr, $u_position);
            $ursprungs_art_lieferant = $this->art_lieferant_from_beleg($u_beleg_nr, $u_position);
            $mwst_satz = $this->mwst_satz_der_position($u_beleg_nr, $u_position);

            $db_abfrage = "INSERT INTO RECHNUNGEN_POSITIONEN VALUES (NULL, '$letzte_rech_pos_id', '$zeile', '$belegnr', '$u_beleg_nr','$ursprungs_art_lieferant','$ursprungs_artikel_nr', '$menge','$einzel_preis','$mwst_satz', '$pos_rabatt_satz', '$skonto', '$gpreis','1')";
            DB::insert($db_abfrage);
            /* Protokollieren */
            $last_dat = DB::getPdo()->lastInsertId();
            protokollieren('RECHNUNGEN_POSITIONEN', $last_dat, '0');
            /* Autokontierung der Position */
            $position = $zeile;
            $u_position = $positionen [$a] ['position'];
            $dat = $positionen [$a] ['kontierung_dat'];
            $this->position_kontierung_infos_n($dat);
            echo "UBELEG $u_beleg_nr POS $u_position ";
            /* in rechnung gestellte menge */
            $kontierungs_menge = $positionen [$a] ['menge'];
            $kontierungs_menge = nummer_komma2punkt($kontierungs_menge);
            /* ursprüngliche kontierungsmenge */
            $u_kontierungs_menge = $this->kontierungs_menge_von_dat($dat);

            $kontenrahmen_konto = $this->kostenkonto;
            $kostentraeger_id = $this->kostentraeger_id;
            $kostentraeger_typ = $this->kostentraeger_typ;
            $einzel_preis = $positionen [$a] ['preis'];
            $einzel_preis = nummer_komma2punkt($einzel_preis);

            $verwendungs_jahr = $this->verwendungs_jahr;
            $mwst_satz = $this->mwst_satz;
            $mwst_satz = nummer_komma2punkt($mwst_satz);

            $rabatt_satz = $positionen [$a] ['rabatt_satz'];
            $rabatt_satz = nummer_komma2punkt($rabatt_satz);

            $skonto = $positionen [$a] ['skonto'];
            $skonto = nummer_komma2punkt($skonto);

            if ($this->rechnungs_empfaenger_typ != 'Lager') {
                $this->automatisch_kontieren($belegnr, $kontierungs_menge, $kontenrahmen_konto, $kostentraeger_id, $kostentraeger_typ, $position, $einzel_preis, $mwst_satz, $rabatt_satz, $skonto, $verwendungs_jahr);
            }
            /* Wenn nicht die gesamte Menge in Rechnung gestellt wurde */
            if ($kontierungs_menge < $u_kontierungs_menge) {
                echo "KONTIERUNGSMENGE NICHT URSPRUNGSMENGE";
                $this->kontierungs_menge_anpassen_dat($dat, $kontierungs_menge);
                // menge die in Rechnung gestellt wurde
            }

            /* Deaktivieren der Position im Pool!!!! */
            if ($kontierungs_menge == $u_kontierungs_menge) {
                echo "KONTIERUNGSMENGE = URSPRUNGSMENGE";
                $this->kontierung_dat_deaktivieren($dat);
            }
        } // end for
        /* Rechnung als vollständig markieren */
        $this->rechnung_als_vollstaendig($belegnr);
        $this->rechnung_als_zugewiesen($belegnr);
        weiterleiten_in_sec(route('web::rechnungen::legacy', ['option' => 'rechnungs_uebersicht', 'belegnr' => $belegnr], false), 2);
    }

    // function rechnung

    /* Prüfen ob ein Artikel nach Beschreibung exisitiert */

    function art_lieferant_from_beleg($belegnr, $pos)
    {
        $result = DB::select("SELECT ART_LIEFERANT FROM RECHNUNGEN_POSITIONEN WHERE BELEG_NR='$belegnr' && POSITION='$pos' && AKTUELL='1' ORDER BY RECHNUNGEN_POS_DAT DESC LIMIT 0,1");
        $row = $result[0];
        return $row ['ART_LIEFERANT'];
    }

    /* Nach in Rechnungsstellung einer Konierungsposition, Tabelle WEITER_VERWENDEN auf 0 setzen */

    function position_kontierung_infos_n($dat)
    {
        unset ($this->kontierungs_menge);
        unset ($this->kostenkonto);
        unset ($this->kostentraeger_typ);
        unset ($this->kostentraeger_id);
        unset ($this->einzel_preis);
        unset ($this->verwendungs_jahr);
        unset ($this->mwst_satz);
        unset ($this->rabatt_satz);
        $result = DB::select("SELECT * FROM KONTIERUNG_POSITIONEN WHERE KONTIERUNG_DAT='$dat' && AKTUELL='1' ORDER BY KONTIERUNG_DAT DESC LIMIT 0,1");
        $row = $result[0];
        $this->kontierungs_menge = $row ['MENGE'];
        $this->kostenkonto = $row ['KONTENRAHMEN_KONTO'];
        $this->kostentraeger_typ = $row ['KOSTENTRAEGER_TYP'];
        $this->kostentraeger_id = $row ['KOSTENTRAEGER_ID'];
        $this->einzel_preis = $row ['EINZEL_PREIS'];
        $this->verwendungs_jahr = $row ['VERWENDUNGS_JAHR'];
        $this->mwst_satz = $row ['MWST_SATZ'];
        $this->rabatt_satz = $row ['RABATT_SATZ'];
    }

    /* Nach in Rechnungsstellung einer Konierungsposition, Tabelle WEITER_VERWENDEN auf 0 setzen */

    function kontierungs_menge_von_dat($dat)
    {
        $result = DB::select("SELECT MENGE FROM KONTIERUNG_POSITIONEN WHERE KONTIERUNG_DAT='$dat' && AKTUELL='1'");
        $row = $result[0];
        return $row ['MENGE'];
    }

    /* Nach in Rechnungsstellung einer Konierungsposition, Tabelle WEITER_VERWENDEN auf 0 setzen */

    function automatisch_kontieren($beleg_nr, $kontierungs_menge, $kontenrahmen_konto, $kostentraeger_id, $kostentraeger_typ, $kontierungs_pos, $einzel_preis, $mwst_satz, $rabatt_satz, $skonto, $verwendungs_jahr)
    {
        $kontierung_id = $this->get_last_kontierung_id();
        $kontierung_id = $kontierung_id + 1;

        $kontierungs_pos = $this->get_last_position_of_beleg($beleg_nr);
        $kontierungs_pos = $kontierungs_pos + 1;
        $gesamt_preis = $kontierungs_menge * $einzel_preis;
        $weiter_verwenden = '0';

        $datum = date("Y-m-d");

        $db_abfrage = "INSERT INTO KONTIERUNG_POSITIONEN VALUES (NULL, '$kontierung_id','$beleg_nr', '$kontierungs_pos','$kontierungs_menge', '$einzel_preis', '$gesamt_preis', '$mwst_satz', '$skonto' , '$rabatt_satz', '$kontenrahmen_konto', '$kostentraeger_typ', '$kostentraeger_id', '$datum', '$verwendungs_jahr', '$weiter_verwenden', '1')";
        DB::insert($db_abfrage);
        /* Protokollieren */
        $last_dat = DB::getPdo()->lastInsertId();
        protokollieren('KONTIERUNG_POSITIONEN', $last_dat, '0');
    }

    /* Kontierung einer Position aufheben */

    function get_last_position_of_beleg($belegnr)
    {
        $result = DB::select("SELECT POSITION FROM KONTIERUNG_POSITIONEN WHERE  BELEG_NR='$belegnr' && AKTUELL='1' ORDER BY KONTIERUNG_DAT DESC LIMIT 0,1");
        $row = $result[0];
        return $row ['POSITION'];
    }

    /* Kontierung einer Position aufheben */

    function kontierungs_menge_anpassen_dat($dat, $neue_menge)
    {
        $result = DB::select("SELECT * FROM KONTIERUNG_POSITIONEN WHERE KONTIERUNG_DAT='$dat' && AKTUELL='1'");
        $row = $result[0];
        $beleg_nr = $row ['BELEG_NR'];
        $kontierungs_pos = $row ['POSITION'];
        $kontierungs_menge = $row ['MENGE'];
        $einzel_preis = $row ['EINZEL_PREIS'];
        $mwst_satz = $row ['MWST_SATZ'];
        $rabatt_satz = $row ['RABATT_SATZ'];
        $kontenrahmen_konto = $row ['KONTENRAHMEN_KONTO'];
        $kostentraeger_typ = $row ['KOSTENTRAEGER_TYP'];
        $kostentraeger_id = $row ['KOSTENTRAEGER_ID'];
        $verwendungs_jahr = $row ['VERWENDUNGS_JAHR'];

        $diff_menge = $kontierungs_menge - $neue_menge;

        if ($diff_menge > 0) {

            /* Ursprungsmenge um Diffmenge Anpassen, dh. wenn vorher 3 und nur 2 in Rechnung dann 3 auf 2 setzen und rest als neue kontierungszeile einfügen, siehe drunter */
            $db_abfrage = "UPDATE KONTIERUNG_POSITIONEN SET MENGE='$neue_menge', WEITER_VERWENDEN='0' WHERE KONTIERUNG_DAT='$dat'";
            DB::update($db_abfrage);
            $datum = date("Y-m-d");
            $kontierung_id = $this->get_last_kontierung_id();
            $kontierung_id = $kontierung_id + 1;
            /* Differenzmenge / Restmenge für Pool für Weiterverwendung */
            $gesamt_preis = $diff_menge * $einzel_preis;
            $db_abfrage = "INSERT INTO KONTIERUNG_POSITIONEN VALUES (NULL, '$kontierung_id','$beleg_nr', '$kontierungs_pos','$diff_menge', '$einzel_preis', '$gesamt_preis', '$mwst_satz', '$rabatt_satz', '$kontenrahmen_konto', '$kostentraeger_typ', '$kostentraeger_id', '$datum', '$verwendungs_jahr', '1', '1')";
            DB::insert($db_abfrage);
        } else {
            echo "KEINE DIFFERENZMENGE ERSICHTLICH";
        }
    }

    /* Ermitteln der Menge einer Kontierungsposition */

    function kontierung_dat_deaktivieren($dat)
    {
        $db_abfrage = "UPDATE KONTIERUNG_POSITIONEN SET WEITER_VERWENDEN='0' WHERE KONTIERUNG_DAT='$dat' ";
        DB::update($db_abfrage);
        /* Protokollieren */
        protokollieren('KONTIERUNG_POSITIONEN', $dat, $dat);
    }

    /* Ermitteln der Gesamtmenge einer Kontierungsposition */

    function rechnung_als_unvollstaendig($belegnr)
    {
        $db_abfrage = "UPDATE RECHNUNGEN SET STATUS_VOLLSTAENDIG='0', STATUS_ZUGEWIESEN='0' WHERE BELEG_NR='$belegnr' && AKTUELL='1'";
        DB::update($db_abfrage);
    }

    /* Ermitteln der Gesamtmenge einer Kontierungsposition */

    function rechnung_als_freigegeben($belegnr)
    {
        $db_abfrage = "UPDATE RECHNUNGEN SET STATUS_ZAHLUNG_FREIGEGEBEN='1' WHERE BELEG_NR='$belegnr' && AKTUELL='1'";
        DB::update($db_abfrage);
    }

    /* Nach in Rechnungsstellung einer Konierungsposition mit veränderter Menge kontierungs_position anpassen um die Differenz */

    function rechnung_als_gezahlt($belegnr, $datum)
    {
        $db_abfrage = "UPDATE RECHNUNGEN SET STATUS_BEZAHLT='1', BEZAHLT_AM='$datum' WHERE BELEG_NR='$belegnr' && AKTUELL='1'";
        DB::update($db_abfrage);
    }

    /* Menge einer Kontierungsposition ändern bzw anpassen */

    function artikel_exists($partner_id, $artikel_bezeichnung)
    {
        $result = DB::select("SELECT ARTIKEL_NR FROM POSITIONEN_KATALOG WHERE ART='$partner_id' && BEZEICHNUNG='$artikel_bezeichnung' && AKTUELL='1' ORDER BY KATALOG_DAT DESC LIMIT 0,1");
        $row = $result[0];
        return $row ['ARTIKEL_NR'];
    }

    /* Menge einer Kontierungsposition ändern bzw anpassen */

    function kontierung_dat_anpassen($dat, $neue_menge)
    {
        $db_abfrage = "UPDATE KONTIERUNG_POSITIONEN SET WEITER_VERWENDEN='0', MENGE='$neue_menge' WHERE KONTIERUNG_DAT='$dat'";
        DB::update($db_abfrage);
        /* Protokollieren */
        protokollieren('RECHNUNGEN_POSITIONEN', $dat, $dat);
    }

    /* Autokontierung einer Position */

    function pos_kontierung_aufheben($dat, $id)
    {
        $db_abfrage = "UPDATE KONTIERUNG_POSITIONEN SET AKTUELL='0' WHERE KONTIERUNG_DAT='$dat' && KONTIERUNG_ID='$id' && AKTUELL='1'";
        DB::update($db_abfrage);
        /* Protokollieren */
        protokollieren('KONTIERUNG_POSITIONEN', $dat, $dat);
    }

    /* Ermitteln der letzten RECHNUNGEN_POS_ID */

    function pos_kontierung_aufheben_dat($dat)
    {
        $db_abfrage = "UPDATE KONTIERUNG_POSITIONEN SET AKTUELL='0' WHERE KONTIERUNG_DAT='$dat'";
        DB::update($db_abfrage);
        /* Protokollieren */
        protokollieren('KONTIERUNG_POSITIONEN', $dat, $dat);
    }

    /* Funktion für's Suchen von rechnungen */

    function kontierungs_menge_gesamt($belegnr, $pos)
    {
        $result = DB::select("SELECT SUM(MENGE) AS GESAMT_KONTIERT FROM KONTIERUNG_POSITIONEN WHERE BELEG_NR='$belegnr' && POSITION='$pos' && AKTUELL='1'");
        $row = $result[0];
        return $row ['GESAMT_KONTIERT'];
    }

    function kontierung_dat_id_andern($dat, $id, $aktuelle_menge)
    {
        $ursprungs_menge = $this->kontierungs_menge($dat, $id);
        if ($ursprungs_menge == $aktuelle_menge) {
            $this->kontierung_dat_id_deaktivieren($dat, $id);
        }
        if ($ursprungs_menge > $aktuelle_menge) {
            $result = DB::select("SELECT MENGE FROM KONTIERUNG_POSITIONEN WHERE KONTIERUNG_DAT='$dat' && KONTIERUNG_ID='$id' && AKTUELL='1'");
            $row = $result[0];
            return $row ['MENGE'];

            $this->kontierung_dat_id_deaktivieren($dat, $id);
            $differenz_menge = $ursprungs_menge - $aktuelle_menge;

            $db_abfrage = "INSERT INTO KONTIERUNG_POSITIONEN VALUES (NULL, '$kontierung_id','$beleg_nr', '$kontierungs_pos','$kontierungs_menge', '$einzel_preis', '$gesamt_preis', '$kontenrahmen_konto', '$kostentraeger_typ', '$kostentraeger_id', '$datum', '$verwendungs_jahr', '$weiter_verwenden', '1')";
            DB::insert($db_abfrage);
        }

        if ($ursprungs_menge > $aktuelle_menge) {
            $this->kontierung_dat_id_deaktivieren($dat, $id);
        }
    }

    function kontierungs_menge($dat, $id)
    {
        $result = DB::select("SELECT MENGE FROM KONTIERUNG_POSITIONEN WHERE KONTIERUNG_DAT='$dat' && KONTIERUNG_ID='$id' && AKTUELL='1'");
        $row = $result[0];
        return $row ['MENGE'];
    }

    function kontierung_dat_id_deaktivieren($dat, $id)
    {
        $db_abfrage = "UPDATE KONTIERUNG_POSITIONEN SET WEITER_VERWENDEN='0' WHERE KONTIERUNG_DAT='$dat' && KONTIERUNG_ID='$id' && AKTUELL='1'";
        DB::update($db_abfrage);
        /* Protokollieren */
        protokollieren('RECHNUNGEN_POSITIONEN', $dat, $dat);
    }

    function kontierungs_menge_anpassen($dat, $id, $neue_menge)
    {
        $result = DB::select("SELECT * FROM KONTIERUNG_POSITIONEN WHERE KONTIERUNG_DAT='$dat' && KONTIERUNG_ID='$id' && AKTUELL='1'");
        $row = $result[0];
        $beleg_nr = $row ['BELEG_NR'];
        $kontierungs_pos = $row ['POSITION'];
        $einzel_preis = $row ['EINZEL_PREIS'];
        $gesamt_preis = $row ['GESAMT_SUMME'];
        $kontenrahmen_konto = $row ['KONTENRAHMEN_KONTO'];
        $kostentraeger_typ = $row ['KOSTENTRAEGER_TYP'];
        $kostentraeger_id = $row ['KOSTENTRAEGER_ID'];
        $verwendungs_jahr = $row ['VERWENDUNGS_JAHR'];
        $weiter_verwenden = $row ['WEITER_VERWENDEN'];

        $this->kontierung_dat_id_deaktivieren($dat, $id);

        $db_abfrage = "INSERT INTO KONTIERUNG_POSITIONEN VALUES (NULL, '$kontierung_id','$beleg_nr', '$kontierungs_pos','$neue_menge', '$einzel_preis', '$gesamt_preis', '$kontenrahmen_konto', '$kostentraeger_typ', '$kostentraeger_id', '$datum', '$verwendungs_jahr', '$weiter_verwenden', '1')";
        DB::insert($db_abfrage);
    }

    function suche_rechnung_form()
    {
        $form = new mietkonto ();
        $partner = new partner ();

        $form->erstelle_formular("Rechnung suchen", null);
        echo "<table><tr><td>\n";
        $form->radio_button_checked("suchart", "lieferschein", 'Lieferschein');
        echo "</td><td>\n";
        $form->text_feld("Lieferscheinnummer eingeben", "lieferschein_nr_txt", '', "10");
        echo "</td></tr><tr><td>\n";
        $form->radio_button("suchart", "beleg_nr", 'Erfassungsnummer');
        echo "</td><td>\n";
        $form->text_feld("Erfassungsnummer eingeben", "beleg_nr_txt", '', "10");
        echo "</td></tr><tr><td>\n";
        $form->radio_button("suchart", "rechnungsnr", 'Rechnungsnummer');
        echo "</td><td>\n";
        $form->text_feld("Rechnungsnummer eingeben", "rechnungsnr_txt", '', "10");
        echo "</td></tr><tr><td>\n";
        $form->radio_button("suchart", "aussteller", 'Ausgestellt von');
        echo "</td><td>\n";
        $partner->partner_dropdown('Aussteller wählen', 'aussteller', 'aussteller');
        echo "</td></tr><tr><td>\n";
        $form->radio_button("suchart", "empfaenger", 'Ausgestellt an');
        echo "</td><td>\n";
        $partner->partner_dropdown('Empfänger wählen', 'empfaenger', 'empfaenger');
        echo "</td></tr><tr><td>\n";
        $form->radio_button("suchart", "partner_paar", 'Partnerpaar auswählen');
        echo "</td><td>\n";
        $partner->partner_dropdown('Von', 'partner_paar1', 'partner_paar1');
        echo "<br>";
        $partner->partner_dropdown('An', 'partner_paar2', 'partner_paar2');
        echo "</td><td></td></tr><tr><td>\n";
        $form->send_button("submit_rechnungssuche", "Rechnung finden");
        echo "</td></tr></table>\n";
        $form->hidden_feld("option", "rechnung_suchen1");
        $form->ende_formular();
    }

    function rechnung_finden_nach_beleg($beleg_nr)
    {
        $result = DB::select("SELECT * FROM RECHNUNGEN WHERE BELEG_NR='$beleg_nr' && AKTUELL = '1' ORDER BY BELEG_NR DESC");
        return $result;
    }

    function rechnung_finden_nach_rnr($rnr)
    {
        $result = DB::select("SELECT * FROM RECHNUNGEN WHERE LTRIM(RTRIM(RECHNUNGSNUMMER)) = '$rnr' && AKTUELL = '1' ORDER BY BELEG_NR DESC");
        return $result;
    }

    /* Kostenträgerliste als dropdown */

    function rechnung_finden_nach_aussteller($aussteller)
    {
        $result = DB::select("SELECT * FROM RECHNUNGEN WHERE AUSSTELLER_ID='$aussteller' && AUSSTELLER_TYP='Partner' && AKTUELL = '1' ORDER BY BELEG_NR DESC");
        return $result;
    }

    /* Kostenträgerliste als dropdown */

    function rechnung_finden_nach_empfaenger($empfaengertyp, $id)
    {
        $result = DB::select("SELECT * FROM RECHNUNGEN WHERE EMPFAENGER_TYP='$empfaengertyp' && EMPFAENGER_ID='$id' && AKTUELL = '1' ORDER BY EMPFAENGER_EINGANGS_RNR DESC");
        return $result;
    }

    function rechnung_finden_nach_paar($aussteller, $empfaenger)
    {
        $result = DB::select("SELECT * FROM RECHNUNGEN WHERE EMPFAENGER_ID='$empfaenger' && EMPFAENGER_TYP='Partner' && AUSSTELLER_ID='$aussteller' && AUSSTELLER_TYP='Partner' && AKTUELL = '1' ORDER BY BELEG_NR DESC");
        return $result;
    }

    function rechnung_finden_nach_lieferschein($lieferschein)
    {
        $d = new detail ();
        $rechnungen_arr = $d->finde_detail_inhalt_arr('RECHNUNGEN', 'Lieferschein', $lieferschein);
        if (!empty($rechnungen_arr)) {
            return $rechnungen_arr;
        } else {
            return false;
        }
    }

    function rechnungen_aus_arr_anzeigen($my_array)
    {
        echo "<table class=rechnungen>\n";
        echo "<tr class=feldernamen><td>ErfNr</td><td>RNr</td><td>TYP</td><td>R-Datum</td><td>Fällig</td><td>Von</td><td>An</td><td width=60>Netto</td><td width=60>Brutto</td></tr>\n";
        for ($a = 0; $a < count($my_array); $a++) {
            $belegnr = $my_array [$a] ['BELEG_NR'];
            $this->rechnung_grunddaten_holen($belegnr);

            $r_datum = date_mysql2german($my_array [$a] ['RECHNUNGSDATUM']);
            $faellig_am = date_mysql2german($my_array [$a] ['FAELLIG_AM']);
            $beleg_link = "<a href='" . route('web::rechnungen::legacy', ['option' => 'rechnungs_uebersicht', 'belegnr' => $my_array [$a] ['BELEG_NR']]) . "'>Ansehen</a>\n";
            $netto = nummer_punkt2komma($my_array [$a] ['NETTO']);
            $brutto = nummer_punkt2komma($my_array [$a] ['BRUTTO']);
            $rechnungstyp = $my_array [$a] ['RECHNUNGSTYP'];
            $rechnungsnummer = $my_array [$a] ['RECHNUNGSNUMMER'];

            $link_pdf = "<a href='" . route('web::rechnungen::legacy', ['option' => 'anzeigen_pdf', 'belegnr' => $belegnr]) . "'><img src=\"images/pdf_light.png\"></a>";
            $link_pdf1 = "<a href='" . route('web::rechnungen::legacy', ['option' => 'anzeigen_pdf', 'belegnr' => $belegnr, 'no_logo']) . "'><img src=\"images/pdf_light.png\"></a>";

            echo "<tr><td valign=\"top\">$beleg_link $link_pdf $link_pdf1</td><td valign=\"top\">$rechnungsnummer</td><td>$rechnungstyp</td><td valign=\"top\">$r_datum</td><td valign=\"top\"><b>$faellig_am</b></td><td valign=\"top\">$this->rechnungs_aussteller_name</td><td valign=\"top\">$this->rechnungs_empfaenger_name</td><td align=right valign=\"top\">$netto €</td><td align=right valign=\"top\">$brutto €</td></tr>\n";
        }

        echo "</table>\n";
    }

    function rechnungseingangsbuch($typ, $partner_id, $monat, $jahr, $rechnungstyp)
    {
        $monatname = monat2name($monat);
        $form = new formular ();
        $p = new partner ();
        if (session()->has('partner_id')) {
            $p->partner_grunddaten(session()->get('partner_id'));
            $form->erstelle_formular("Ausgewählt: $p->partner_name", NULL);
            $form->erstelle_formular("Rechnungseingangsbuch $monatname $jahr - $p->partner_name", NULL);
        } else {
            $form->erstelle_formular("Ausgewählt: Lager", NULL);
            $form->erstelle_formular("Rechnungseingangsbuch $monatname $jahr - Lager", NULL);
        }
        echo "<table id=\"monate_links\"><tr><td>";
        $this->r_eingang_monate_links($monat, $jahr);
        echo "</td></tr>";
        $pdf_link = "<a href='" . route('web::rechnungen::legacy', ['option' => 'rechnungsbuch_eingang', 'monat' => $monat, 'jahr' => $jahr, 'r_typ' => 'Rechnung']) . "'>Als PDF</a>";
        $self = $_SERVER ['QUERY_STRING'];
        echo "<tr><td>$pdf_link <a href='?$self&xls'>Als Excel</a></td></tr>";
        echo "</table>";
        $rechnungen_arr = $this->eingangsrechnungen_arr($typ, $partner_id, $monat, $jahr, $rechnungstyp);

        $this->rechnungsbuch_anzeigen_ein($rechnungen_arr);
        $form->ende_formular();
        $form->ende_formular();
    }

    function r_eingang_monate_links($monat, $jahr)
    {
        $link_p_wechseln = "<a href='" . route('web::rechnungen::legacy', ['option' => 'partner_wechseln']) . "'>Partner wechseln</a>&nbsp;";

        echo $link_p_wechseln;
        $link_alle = "<a href='" . route('web::rechnungen::legacy', ['option' => 'eingangsbuch', 'monat' => 'alle', 'jahr' => $jahr]) . "'>Alle von $jahr</a>&nbsp;";

        echo $link_alle;

        $bg = new berlussimo_global ();
        $link = route('web::rechnungen::legacy', ['option' => 'eingangsbuch'], false);
        $bg->monate_jahres_links($jahr, $link);
    }

    function eingangsrechnungen_arr($empfaenger_typ, $empfaenger_id, $monat, $jahr, $rechnungstyp)
    {
        if ($rechnungstyp == 'Rechnung') {
            $r_sql = "(RECHNUNGSTYP='$rechnungstyp' OR RECHNUNGSTYP='Stornorechnung' OR RECHNUNGSTYP='Gutschrift' OR RECHNUNGSTYP='Teilrechnung' OR RECHNUNGSTYP='Schlussrechnung')";
        } else {
            $r_sql = "RECHNUNGSTYP='$rechnungstyp'";
        }
        if ($monat == 'alle') {
            $result = DB::select("SELECT * FROM RECHNUNGEN WHERE EMPFAENGER_ID='$empfaenger_id' && EMPFAENGER_TYP='$empfaenger_typ' && DATE_FORMAT( RECHNUNGSDATUM, '%Y' ) = '$jahr' && $r_sql && AKTUELL = '1' GROUP BY BELEG_NR ORDER BY EMPFAENGER_EINGANGS_RNR ASC");
        } else {
            $result = DB::select("SELECT * FROM RECHNUNGEN WHERE EMPFAENGER_ID='$empfaenger_id' && EMPFAENGER_TYP='$empfaenger_typ' && DATE_FORMAT( RECHNUNGSDATUM, '%Y-%m' ) = '$jahr-$monat' && $r_sql && AKTUELL = '1' GROUP BY BELEG_NR ORDER BY EMPFAENGER_EINGANGS_RNR ASC");
        }
        return $result;
    }

    function rechnungsbuch_anzeigen_ein($arr)
    {
        if (request()->exists('xls')) {
            ob_clean();
            // ausgabepuffer leeren
            $fileName = 'rechnungseingangsbuch' . date("d-m-Y") . '.xls';
            header("Content-Type: application/vnd.ms-excel");
            header("Content-Disposition: attachment; filename=$fileName");
            $beleg_link = '';
        }
        echo "<table class=\"sortable\">";
        echo "<thead>";
        echo "<tr>";
        echo "<th id=\"tr_ansehen\">Ansehen</th>";
        echo "<th >LFDNR</th>";
        echo "<th >R-Datum</th>";
        echo "<th >Rechnungssteller</th>";
        echo "<th >RECHUNGSNR</th>";
        echo "<th >Leistung/Ware</th>";
        echo "<th >Brutto</th>";
        echo "<th >Skonto</th>";
        echo "<th >Gutschriften<br>Returen</th>";
        echo "<th >WEITERB.</th>";
        echo "<th >SALDO</th>";

        echo "</tr>";
        echo "</thead>";

        $r = new rechnung ();

        $anzahl = count($arr);
        $sum_weiterberechnet = 0;

        $g_brutto_r = 0;
        $g_brutto_g = 0;
        $g_netto = 0;
        $g_skonto = 0;
        $g_mwst = 0;

        if ($anzahl > 0) {
            for ($a = 0; $a < $anzahl; $a++) {

                $belegnr = $arr [$a] ['BELEG_NR'];

                if (!isset ($fileName)) {
                    $beleg_link = "<a href='" . route('web::rechnungen::legacy', ['option' => 'rechnungs_uebersicht', 'belegnr' => $belegnr]) . "'>Ansehen</a>";
                    $pdf_link = "<a href='" . route('web::rechnungen::legacy', ['option' => 'anzeigen_pdf', 'belegnr' => $belegnr]) . "'><img src=\"images/pdf_light.png\"></a>";
                    $pdf_link1 = "<a href='" . route('web::rechnungen::legacy', ['option' => 'anzeigen_pdf', 'belegnr' => $belegnr, 'no_logo']) . "'><img src=\"images/pdf_dark.png\"></a>";
                }

                $r->rechnung_grunddaten_holen($belegnr);
                $r->rechnungs_aussteller_name = substr($r->rechnungs_aussteller_name, 0, 48);
                $status_kontierung = $r->rechnung_auf_kontierung_pruefen($belegnr);
                // echo $status_kontierung;

                if ($status_kontierung == 'unvollstaendig') {
                    echo "<tr style=\"background-color:#ff778c\">";
                }

                if ($status_kontierung == 'vollstaendig') {
                    echo "<tr style=\"background-color:#bcd59f\">";
                }

                echo "<td id=\"td_ansehen\">$beleg_link<br>$pdf_link $pdf_link1</td><td>$r->empfaenger_eingangs_rnr</td><td>$r->rechnungsdatum</td>";
                /* Prüfen ob die rechnung temporär zur Buchungszwecken an Rechnungsausstellr kontiert */
                if ($this->check_kontierung_rg($belegnr, $r->rechnungs_aussteller_typ, $r->rechnungs_aussteller_id) == true) {
                    echo "<td style=\"background-color:#f8ffbb\">$r->rechnungs_aussteller_name</td>";
                } else {
                    echo "<td>$r->rechnungs_aussteller_name</td>";
                }

                echo "<td><b>$r->rechnungsnummer</b></td>";
                echo "<td>$r->kurzbeschreibung</td>";

                $r->rechnungs_skontoabzug_a = nummer_punkt2komma($r->rechnungs_skontoabzug);

                if ($r->rechnungstyp == 'Rechnung' or $r->rechnungstyp == 'Teilrechnung' or $r->rechnungstyp == 'Schlussrechnung') {
                    $r->rechnungs_brutto_a = nummer_punkt2komma($r->rechnungs_brutto);
                    echo "<td align=\"right\" valign=\"top\">$r->rechnungs_brutto_a </td><td align=\"right\" valign=\"top\">$r->rechnungs_skontoabzug_a</td><td></td>";
                    $g_brutto_r += $r->rechnungs_brutto;
                    // $g_brutto_r= sprintf("%01.2f", $g_brutto_r);
                }

                if ($r->rechnungstyp == 'Gutschrift' or $r->rechnungstyp == 'Stornorechnung') {
                    $r->rechnungs_brutto_a = nummer_punkt2komma($r->rechnungs_brutto);
                    echo "<td></td><td align=\"right\" valign=\"top\">$r->rechnungs_skontoabzug_a</td><td align=\"right\" valign=\"top\">$r->rechnungs_brutto_a </td>";
                    $g_brutto_g += $r->rechnungs_brutto;
                    // $g_brutto_g= sprintf("%01.2f", $g_brutto_g);
                }

                $summe_weiterbelastung_a = nummer_punkt2komma($this->get_weiterbelastung($belegnr));
                $summe_weiterbelastung = nummer_komma2punkt($summe_weiterbelastung_a);
                $sum_weiterberechnet += $summe_weiterbelastung;
                echo "<td>$summe_weiterbelastung_a</td>";
                $saldo_rg = $summe_weiterbelastung - $r->rechnungs_brutto;
                $saldo_rg_a = nummer_punkt2komma($saldo_rg);
                if ($saldo_rg >= 0) {
                    echo "<td style=\"background-color:#bcd59f\">";
                } else {

                    // braun ==c48b7c
                    if ($this->check_kontierung_rg($belegnr, $r->rechnungs_empfaenger_typ, $r->rechnungs_empfaenger_id) == true) {
                        echo "<td style=\"background-color:#c48b7c\">";
                    } else {
                        echo "<td style=\"background-color:#ff778c\">";
                    }
                }
                echo "$saldo_rg_a</td>";

                echo "</tr>";

                $g_netto += $r->rechnungs_netto;
                // $g_netto= sprintf("%01.2f", $g_netto);
                $g_mwst += $r->rechnungs_mwst;
                // $g_mwst= sprintf("%01.2f", $g_mwst);

                $g_skonto += $r->rechnungs_skontoabzug;
                // $g_skonto= sprintf("%01.2f", $g_skonto);
            }
            // echo "<tr><td colspan=\"9\"><hr></td></tr>";
            $g_brutto_r = nummer_punkt2komma_t($g_brutto_r);
            $g_brutto_g = nummer_punkt2komma_t($g_brutto_g);
            $g_skonto = nummer_punkt2komma_t($g_skonto);
            $sum_weiterberechnet_a = nummer_punkt2komma_t($sum_weiterberechnet);
            echo "<tfoot><tr><td id=\"td_ansehen\"></td><td></td><td></td><td></td><td></td><td></td><td align=\"right\"><b>$g_brutto_r</b></td><td align=\"right\"><b>$g_skonto</b></td><td><b>$g_brutto_g</b></td><td><b>$sum_weiterberechnet_a</b></td><td align=\"right\"></td></tr></tfoot>";
        } else {
            echo "<tr><td colspan=9>Keine Rechnungen in diesem Monat</td></tr>";
        }
        echo "</table>";
    }

    function check_kontierung_rg($beleg_nr, $kos_typ, $kos_id)
    {
        $result = DB::select("SELECT * FROM `KONTIERUNG_POSITIONEN` WHERE BELEG_NR = '$beleg_nr' && KOSTENTRAEGER_TYP = '$kos_typ' && KOSTENTRAEGER_ID = '$kos_id' && AKTUELL='1'");
        return !empty($result);
    }

    function get_weiterbelastung($belegnr)
    {
        $result = DB::select("SELECT SUM((GESAMT_NETTO/100)*(100+MWST_SATZ)) AS SUMME FROM `RECHNUNGEN_POSITIONEN` WHERE `U_BELEG_NR`!=`BELEG_NR` && `U_BELEG_NR` = '$belegnr' AND `AKTUELL` = '1'");
        $row = $result[0];
        return $row ['SUMME'];
    }

    function rechnungsausgangsbuch($typ, $partner_id, $monat, $jahr, $rechnungstyp)
    {
        $monatname = monat2name($monat);
        $form = new formular ();
        $p = new partner ();
        if (session()->has('partner_id')) {
            $p->partner_grunddaten(session()->get('partner_id'));
            $form->erstelle_formular("Ausgewählt: $p->partner_name", NULL);
            $form->erstelle_formular("Rechnungsausgangsbuch $monatname $jahr - $p->partner_name", NULL);
        } else {
            $form->erstelle_formular("Ausgewählt: Lager", NULL);
            $form->erstelle_formular("Rechnungsausgangsbuch $monatname $jahr - Lager", NULL);
        }
        echo "<table id=\"monate_links\"><tr><td>";
        $this->r_ausgang_monate_links($monat, $jahr);
        echo "</td></tr>";
        $pdf_link = "<a href='" . route('web::rechnungen::legacy', ['option' => 'rechnungsbuch_ausgang', 'monat' => $monat, 'jahr' => $jahr, 'r_typ' => 'Rechnung']) . "'>Als PDF</a>";
        $self = $_SERVER ['QUERY_STRING'];
        echo "<tr><td>$pdf_link <a href='?$self&xls'>Als Excel</a></td></tr>";
        echo "</table>";
        $rechnungen_arr = $this->ausgangsrechnungen_arr($typ, $partner_id, $monat, $jahr, $rechnungstyp);

        $this->rechnungsbuch_anzeigen_aus($rechnungen_arr);
        $form->ende_formular();
        $form->ende_formular();
    }

    function r_ausgang_monate_links($monat, $jahr)
    {
        $link_p_wechseln = "<a href='" . route('web::rechnungen::legacy', ['option' => 'partner_wechseln']) . "'>Partner wechseln</a>&nbsp;";
        echo $link_p_wechseln;
        $link_alle = "<a href='" . route('web::rechnungen::legacy', ['option' => 'eingangsbuch', 'monat' => 'alle', 'jahr' => $jahr]) . "'>Alle von $jahr</a>&nbsp;";
        echo $link_alle;
        $bg = new berlussimo_global ();
        $link = route('web::rechnungen::legacy', ['option' => 'ausgangsbuch'], false);
        $bg->monate_jahres_links($jahr, $link);
    }

    function ausgangsrechnungen_arr($aussteller_typ, $aussteller_id, $monat, $jahr, $rechnungstyp)
    {
        if ($rechnungstyp == 'Rechnung') {
            $r_sql = "(RECHNUNGSTYP='$rechnungstyp' OR RECHNUNGSTYP='Stornorechnung' OR RECHNUNGSTYP='Gutschrift' OR RECHNUNGSTYP='Teilrechnung' OR RECHNUNGSTYP='Schlussrechnung')";
        } else {
            $r_sql = "RECHNUNGSTYP='$rechnungstyp'";
        }

        if ($monat == 'alle') {
            $result = DB::select("SELECT * FROM RECHNUNGEN WHERE AUSSTELLER_ID='$aussteller_id' && AUSSTELLER_TYP='$aussteller_typ' && DATE_FORMAT( RECHNUNGSDATUM, '%Y' ) = '$jahr' && $r_sql && AKTUELL = '1' ORDER BY AUSTELLER_AUSGANGS_RNR ASC");
        } else {
            $result = DB::select("SELECT * FROM RECHNUNGEN WHERE AUSSTELLER_ID='$aussteller_id' && AUSSTELLER_TYP='$aussteller_typ' && DATE_FORMAT( RECHNUNGSDATUM, '%Y-%m' ) = '$jahr-$monat' && $r_sql && AKTUELL = '1' ORDER BY AUSTELLER_AUSGANGS_RNR DESC");
        }
        return $result;
    }

    function rechnungsbuch_anzeigen_aus($arr)
    {
        if (request()->exists('xls')) {
            ob_clean();
            // ausgabepuffer leeren
            $fileName = 'rechnungsausgangsbuch' . date("d-m-Y") . '.xls';
            header("Content-Type: application/vnd.ms-excel");
            header("Content-Disposition: inline; filename=$fileName");
            $beleg_link = '';
        }

        echo "<table class=\"sortable\" id=\"positionen_tab\">\n";
        echo "<thead>";
        echo "<tr >";
        echo "<th scopr=\"col\" id=\"tr_ansehen\">Ansehen</th>";
        echo "<th >LFDNR</th>";
        echo "<th scopr=\"col\">Rechnungsempfänger</th>";
        echo "<th scopr=\"col\">Leistung/Ware</th>";
        echo "<th scopr=\"col\">Brutto</th>";
        // echo "<th scopr=\"col\">Skontobetrag</th>";
        echo "<th scopr=\"col\">Gutschriften und Returen</th>";
        echo "<th scopr=\"col\">R-Nr</th>";
        echo "<th scopr=\"col\">R-Datum</th>";
        echo "<th scopr=\"col\">Skonto</th>";
        echo "</tr>";

        echo "</thead>";

        $r = new rechnung ();

        $anzahl = count($arr);

        if ($anzahl) {
            $g_skonto = 0;
            for ($a = 0; $a < $anzahl; $a++) {

                $belegnr = $arr [$a] ['BELEG_NR'];
                if (!isset ($fileName)) {
                    $beleg_link = "<a href='" . route('web::rechnungen::legacy', ['option' => 'rechnungs_uebersicht', 'belegnr' => $belegnr]) . "'>Ansehen</a>";
                    $pdf_link = "<a href='" . route('web::rechnungen::legacy', ['option' => 'anzeigen_pdf', 'belegnr' => $belegnr]) . "'><img src=\"images/pdf_light.png\"></a>";
                    $pdf_link1 = "<a href='" . route('web::rechnungen::legacy', ['option' => 'anzeigen_pdf', 'belegnr' => $belegnr, 'no_logo']) . "'><img src=\"images/pdf_dark.png\"></a>";
                }
                $r->rechnung_grunddaten_holen($belegnr);
                $r->rechnungs_empfaenger_name = bereinige_string($r->rechnungs_empfaenger_name);
                $r->rechnungs_empfaenger_name = substr($r->rechnungs_empfaenger_name, 0, 48);
                echo "<tr><td id=\"td_ansehen\">$beleg_link $pdf_link $pdf_link1</td><td valign=\"top\">$r->aussteller_ausgangs_rnr</td><td valign=\"top\">$r->rechnungs_empfaenger_name</td>";
                // $r->kurzbeschreibung =bereinige_string($r->kurzbeschreibung);
                echo "<td valign=\"top\">$r->kurzbeschreibung</td>";

                $r->rechnungs_brutto_ausgabe = nummer_punkt2komma($r->rechnungs_brutto);
                $r->rechnungs_skonto_ausgabe = nummer_punkt2komma($r->rechnungs_skontobetrag);

                if ($r->rechnungstyp == 'Rechnung' or $r->rechnungstyp == 'Teilrechnung') {
                    // echo "<td align=\"right\">$r->rechnungs_brutto_ausgabe</td><td align=\"right\">$r->rechnungs_skonto_ausgabe</td><td></td>";
                    echo "<td align=\"right\" valign=\"top\">$r->rechnungs_brutto_ausgabe</td><td></td>";
                    $g_brutto_r = 0;
                    $g_brutto_r = $g_brutto_r + $r->rechnungs_brutto;
                    $g_brutto_r = sprintf("%01.2f", $g_brutto_r);
                    $g_skonto_rg = 0;
                    $g_skonto_rg = $g_skonto_rg + $r->rechnungs_skontobetrag;
                    $g_skonto_rg = sprintf("%01.2f", $g_skonto_rg);

                    $g_skonto = $g_skonto + $r->rechnungs_skontoabzug;
                    $g_skonto = sprintf("%01.2f", $g_skonto);

                    $g_netto = 0;
                    $g_netto = $g_netto + $r->rechnungs_netto;
                    $g_netto = sprintf("%01.2f", $g_netto);

                    $g_mwst = 0;
                    $g_mwst = $g_mwst + $r->rechnungs_mwst;

                    $g_brutto_g = 0;
                    $g_brutto = $g_brutto_g + $r->rechnungs_brutto;
                    $g_brutto = sprintf("%01.2f", $g_brutto);
                }

                if ($r->rechnungstyp == 'Schlussrechnung') {
                    $rrr = new rechnungen ();
                    $rrr->get_summen_schlussrechnung($belegnr);

                    /* Sicherheitseinbehalt */
                    $rrr->get_sicherheitseinbehalt($belegnr);
                    if ($rrr->rg_betrag > '0.00') {
                        // $this->rechnungs_brutto = ($row['BRUTTO'] - $rs->rg_betrag);
                        // echo $this->rechnungs_brutto;
                        $rrr->rechnungs_brutto_schluss = $rrr->rechnungs_brutto_schluss - $rrr->rg_betrag;
                        $rrr->rechnungs_brutto_schluss_a = nummer_punkt2komma_t($rrr->rechnungs_brutto_schluss);
                    }

                    echo "<td align=\"right\" valign=\"top\">$rrr->rechnungs_brutto_schluss_a</td><td></td>";

                    $g_brutto_r = $g_brutto_r + $rrr->rechnungs_brutto_schluss;
                    $g_brutto_r = sprintf("%01.2f", $g_brutto_r);

                    $g_skonto_rg = $g_skonto_rg + $r->rechnungs_skontobetrag;
                    $g_skonto_rg = sprintf("%01.2f", $g_skonto_rg);

                    $g_skonto = $g_skonto + $rrr->rechnungs_skontoabzug_schluss;
                    $g_skonto = sprintf("%01.2f", $g_skonto);

                    $g_netto = $g_netto + $rrr->rechnungs_netto_schluss;
                    $g_netto = sprintf("%01.2f", $g_netto);

                    $g_mwst = 0;
                    $g_mwst = $g_mwst + $rrr->rechnungs_mwst_schluss;
                    $g_mwst = sprintf("%01.2f", $g_mwst);

                    $g_brutto_g = 0;
                    $g_brutto = $g_brutto_g + $rrr->rechnungs_brutto_schluss;
                    $g_brutto = sprintf("%01.2f", $g_brutto);
                }

                if ($r->rechnungstyp == 'Gutschrift' or $r->rechnungstyp == 'Stornorechnung') {
                    // echo "<td></td><td></td><td align=\"right\">$r->rechnungs_skonto_ausgabe</td>";
                    echo "<td></td><td align=\"right\" valign=\"top\">$r->rechnungs_brutto_ausgabe</td>";
                    $g_brutto_g = 0;
                    $g_brutto_g = $g_brutto_g + $r->rechnungs_brutto;
                    $g_brutto_g = sprintf("%01.2f", $g_brutto_g);
                }
                $r->rechnungs_skontoabzug_a = nummer_punkt2komma($r->rechnungs_skontoabzug);
                echo "<td valign=\"top\"><b>$r->rechnungsnummer</b></td><td valign=\"top\">$r->rechnungsdatum</td><td align=\"right\" valign=\"top\">$r->rechnungs_skontoabzug_a</td></tr>";
            } // end for
            $g_brutto = nummer_punkt2komma($g_brutto);
            $g_brutto_g = nummer_punkt2komma($g_brutto_g);
            $g_skonto = nummer_punkt2komma($g_skonto);
            echo "<tfoot><tr><td colspan=\"9\"><hr></td></tr>";
            echo "<tr><td id=\"td_ansehen\"></td><td></td><td></td><td></td><td align=\"right\"><b>$g_brutto</b></td><td align=\"right\"><b>$g_brutto_g</b></td><td></td><td></td><td align=\"right\"><b>$g_skonto</b></td></tr></tfoot>";
        } else {
            echo "<tr><td colspan=10>Keine Rechnungen in diesem Monat</td></tr>";
        }
        echo "</table>";
    }
} // Ende Klasse Rechnung