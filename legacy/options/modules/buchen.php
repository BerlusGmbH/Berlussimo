<?php

/* Prüfen ob es Buchungen vor einem Jahr gibt und ausgeben */
$bu = new buchen ();
if ($bu->get_buchungen_vor(2005) != false) {
    fehlermeldung_ausgeben("Buchungen vor 2005 gefunden (DATUM FALSCH?!?: ANZAHL: " . $bu->get_buchungen_vor(2005));
}

if (request()->has('option')) {
    $option = request()->input('option');
} else {
    $option = 'default';
}

/* Optionsschalter */
switch ($option) {

    /*
     * Aufruf des Formulars für die
     * Buchung der Zahlbeträge
     */
    case "zahlbetrag_buchen" :
        $buchung = new buchen ();
        if (session()->has('geldkonto_id')) {
            if (session()->has('temp_datum') && session()->has('temp_kontoauszugsnummer') && session()->has('temp_kontostand')) {
                $form = new formular ();
                $form->erstelle_formular("Buchungsmaske für Zahlbeträge", NULL);

                $buchung->geldkonto_header();
                $buchung->zb_buchen_form(session()->get('geldkonto_id'));
                $form->ende_formular();
            } else {
                weiterleiten(route('legacy::buchen::index', ['option' => 'kontoauszug_form'], false));
            }
        } else {
            $buchung->geldkonto_auswahl();
            session()->put('url.intended', URL::full());
        }
        break;

    case "geldkonto_aendern" :
        $form = new formular ();
        $form->erstelle_formular("Geldkonto ändern", NULL);

        session()->forget('geldkonto_id');
        session()->forget('temp_datum');
        session()->forget('temp_kontoauszugsnummer');
        session()->forget('temp_kontostand');
        session()->forget('kos_typ');
        session()->forget('kos_id');

        $buchung = new buchen ();
        $buchung->geldkonto_auswahl();
        $form->ende_formular();
        break;

    case "buchung_gesendet" :

        $link_kontoauszug = "<a href='" . route('legacy::buchen::index', ['option' => 'kontoauszug_form'], false) . "'>Kontrolldaten zum Kontoauszug eingeben</a>";
        $form = new formular ();
        $form->erstelle_formular("Buchungsinformationen prüfen", NULL);
        $kostentraeger_typ = request()->input('kostentraeger_typ');
        $kostentraeger_id = request()->input('kostentraeger_id');
        if (empty ($kostentraeger_typ) or empty ($kostentraeger_id)) {
            $error = "Fehler - Kostenträgertyp und Kostenträger wählen";
        }
        $kto_auszugsnr = request()->input('kontoauszugsnummer');
        if (empty ($kto_auszugsnr)) {
            $error = "Fehler - Kontoauszugsnummer";
        }
        if ($kto_auszugsnr != session()->get('temp_kontoauszugsnummer')) {

            $error = "Sie beginnen mit einem neuen Kontoauszug.<br>";
            $error .= "Bitte die Kontrolldaten zur Kontoauszugsnummer " . session()->get('temp_kontoauszugsnummer') . " eingeben";
            $error .= "<br>$link_kontoauszug";
        }
        if (!is_numeric($kto_auszugsnr)) {
            $error = "Fehler - Kontoauszugsnummer - NUR ZAHLEN";
        }
        $datum = request()->input('datum');
        if (empty ($datum)) {
            $error = "Fehler - Datum fehlt";
        }
        $rechnungsnr = request()->input('rechnungsnr');
        if (empty ($rechnungsnr)) {
            $error = "Fehler - Rechnungsnummer";
        }
        if ($datum != session()->get('temp_datum')) {
            $link_kontoauszug = "<a href='" . route('legacy::buchen::index', ['option' => 'kontoauszug_form'], false) . "'>Kontrolldaten zum Kontoauszug eingeben</a>";
            $error = "Sie haben das Buchungsdatum verändert.<br>";
            $error .= "Bitte die Kontrolldaten zur Kontoauszugsnummer " . session()->get('temp_kontoauszugsnummer') . " verändern.";
            $error .= "<br>$link_kontoauszug";
        }
        if (!check_datum($datum)) {
            $error = "Fehler - Datumsformat überprüfen";
        }
        $betrag = request()->input('betrag');
        if (empty ($betrag)) {
            $error = "Fehler - Betrag";
        }

        $kostenkonto = request()->input('kostenkonto');
        $vzweck = request()->input('vzweck');
        if (empty ($vzweck)) {
            $error = "Fehler - Buchungstext fehlt";
        }

        $geldkonto_id = request()->input('geldkonto_id');
        if (empty ($geldkonto_id)) {
            $error = "Fehler - Kein Geldkonto wurde gewählt";
        }
        if (!isset ($error)) {
            echo "$kostentraeger_typ - $kostentraeger_id";

            echo "<h3>Datum: $datum<br>";
            echo "Kontoauszugsnr: $kto_auszugsnr<br>";
            echo "Betrag: $betrag<br>";
            echo "Kostenkonto: $kostenkonto<br>";
            echo "Kostenträgertyp $kostentraeger_typ<br>";
            echo "Kostenträger $kostentraeger_id<br>";
            echo "Buchungstext $vzweck<br></h3>";

            $datum = date_german2mysql($datum);
            $betrag = nummer_komma2punkt($betrag);
            $buchung = new buchen ();
            if (request()->has('mwst')) {
                $mwst = nummer_komma2punkt(request()->input('mwst'));
            } else {
                $mwst = '0.00';
            }

            $buchung->geldbuchung_speichern($datum, $kto_auszugsnr, $rechnungsnr, $betrag, $vzweck, $geldkonto_id, $kostentraeger_typ, $kostentraeger_id, $kostenkonto, $mwst);
        } else {
            echo $error;
        }
        $form->ende_formular();
        break;

    case "buchung_speichern" :
        $kostentraeger_typ = request()->input('kostentraeger_typ');
        $kostentraeger_id = request()->input('kostentraeger_id');
        $kto_auszugsnr = request()->input('kontoauszugsnummer');
        $datum = request()->input('datum');
        $datum = date_german2mysql($datum);
        $betrag = request()->input('betrag');
        $betrag = nummer_komma2punkt($betrag);
        $kostenkonto = request()->input('kostenkonto');
        $vzweck = request()->input('vzweck');
        $geldkonto_id = request()->input('geldkonto_id');
        $rechnungsnr = request()->input('rechnungsnr');
        $buchung = new buchen ();
        $buchung->geldbuchung_speichern($datum, $kto_auszugsnr, $rechnungsnr, $betrag, $vzweck, $geldkonto_id, $kostentraeger_typ, $kostentraeger_id, $kostenkonto);

        break;

    case "geldbuchung_aendern" :
        $form = new formular ();
        $form->erstelle_formular("Geldbuchung ändern", NULL);
        $buchung = new buchen ();
        $geldbuchung_dat = request()->input('geldbuchung_dat');
        $buchung->buchungsmaske_buchung_aendern($geldbuchung_dat);
        $form->ende_formular();
        break;

    case "geldbuchung_aendern1" :
        $form = new formular ();
        $form->erstelle_formular("Geldbuchung ändern", NULL);
        $buchung = new buchen ();
        $geldbuchung_dat_alt = request()->input('buch_dat_alt');
        $geldbuchung_id = request()->input('akt_buch_id');
        $g_buchungsnummer = request()->input('g_buchungsnummer');
        $betrag = request()->input('betrag');
        $datum = request()->input('datum');
        $kostentraeger_typ = request()->input('kostentraeger_typ');
        $kostentraeger_bez = request()->input('kostentraeger_id');
        $vzweck = request()->input('vzweck');
        $kostenkonto = request()->input('kostenkonto');
        $geldkonto_id = request()->input('geldkonto_id');
        $kontoauszugsnr = request()->input('kontoauszugsnr');
        $erfass_nr = request()->input('erfassungsnr');
        $mwst_anteil = request()->input('mwst');
        $buchung->geldbuchungs_dat_deaktivieren($geldbuchung_dat_alt);
        $buchung->speichern_in_geldbuchungen($geldbuchung_id, $g_buchungsnummer, $betrag, $datum, $kostentraeger_typ, $kostentraeger_bez, $vzweck, $kostenkonto, $geldkonto_id, $kontoauszugsnr, $erfass_nr, $mwst_anteil, $geldbuchung_dat_alt);
        $form->ende_formular();
        break;

    case "kontoauszug_form" :
        $form = new formular ();
        $form->erstelle_formular("Kontoauszug bearbeiten", NULL);
        $buchung = new buchen ();
        $buchung->kontoauszug_form();
        $form->ende_formular();
        break;

    case "kontoauszug_gesendet" :
        $form = new formular ();
        $form->erstelle_formular("Kontoauszug temporär gespeichert", NULL);
        session()->put('temp_kontoauszugsnummer', request()->input('kontoauszugsnummer'));
        session()->put('temp_datum', request()->input('datum'));
        session()->put('buchungsdatum', request()->input('datum'));
        $kontostand_punkt = nummer_komma2punkt(request()->input('kontostand'));
        session()->put('temp_kontostand', $kontostand_punkt);
        echo "Kontoauszugsdaten wurden temporär gespeichert.<br>";
        echo "Sie werden weitergeleitet.";
        weiterleiten_in_sec(route('legacy::buchen::index', ['option' => 'zahlbetrag_buchen'], false), 1);
        $form->ende_formular();
        break;

    case "buchungs_journal" :
        $form = new formular ();
        echo "<body onload=\"JavaScript:seite_aktualisieren(10000);\">";
        if (session()->has('temp_kontoauszugsnummer') && session()->has('geldkonto_id')) {
            $buchung = new buchen ();
            $buchung->akt_konto_bezeichnung = $buchung->geld_konto_bezeichung(session()->get('geldkonto_id'));
            $form->erstelle_formular("$buchung->akt_konto_bezeichnung -> Buchungsjournal vom Kontoauszug " . session()->get('temp_kontoauszugsnummer'), NULL);
            $buchung->buchungsjournal_auszug(session()->get('geldkonto_id'), session()->get('temp_kontoauszugsnummer'));
            $form->ende_formular();
        }
        if (!session()->has('temp_kontoauszugsnummer')) {
            $buchung = new buchen ();
            $buchung->akt_konto_bezeichnung = $buchung->geld_konto_bezeichung(session()->get('geldkonto_id'));
            if (request()->has('jahr') && request()->has('monat')) {
                $jahr = request()->input('jahr');
                $monat = sprintf("%02d", request()->input('monat'));
                $datum = "$jahr-$monat-01";
            } else {
                $jahr = date("Y");
                $monat = sprintf("%02d", date("m"));
                $datum = "$jahr-$monat-01";
            }
            $form->erstelle_formular("$buchung->akt_konto_bezeichnung -> Buchungsjournal seit $datum", NULL);
            $bg = new berlussimo_global ();
            $link = route('legacy::buchen::index', ['option' => 'buchungs_journal'], false);
            $bg->monate_jahres_links($jahr, $link);
            echo "<a href='" . route('legacy::buchen::index', ['option' => 'buchungs_journal_druckansicht']) . "'>Druckansicht</a>&nbsp;";
            if (request()->has('monat')) {
                $aktueller_monat = request()->input('monat');
            } else {
                $aktueller_monat = date("m");
            }
            echo "<a href='" . route('legacy::buchen::index', ['option' => 'buchungs_journal_pdf', 'monat' => $aktueller_monat, 'jahr' => $jahr]) . "'>PDF-Ansicht</a>&nbsp;";
            $buchung->buchungsjournal_startzeit(session()->get('geldkonto_id'), $datum);
            $form->ende_formular();
        }
        break;

    case "buchungs_journal_druckansicht" :

        $form = new formular ();
        if (session()->has('temp_kontoauszugsnummer') && session()->has('geldkonto_id')) {
            $buchung = new buchen ();
            $buchung->akt_konto_bezeichnung = $buchung->geld_konto_bezeichung(session()->get('geldkonto_id'));
            $form->erstelle_formular("$buchung->akt_konto_bezeichnung -> Buchungsjournal vom Kontoauszug " . session()->get('temp_kontoauszugsnummer'), NULL);
            $buchung->buchungsjournal_auszug(session()->get('geldkonto_id'), session()->get('temp_kontoauszugsnummer'));
            $form->ende_formular();
        }
        if (!session()->has('temp_kontoauszugsnummer')) {
            $buchung = new buchen ();
            $buchung->akt_konto_bezeichnung = $buchung->geld_konto_bezeichung(session()->get('geldkonto_id'));
            if (request()->has('jahr') && request()->input('monat')) {
                $jahr = request()->input('jahr');
                $monat = sprintf("%02d", request()->input('monat'));
                $datum = "$jahr-$monat-01";
            } else {
                $jahr = date("Y");
                $monat = sprintf("%02d", date("m"));
                $datum = "$jahr-$monat-01";
            }
            $form->erstelle_formular("$buchung->akt_konto_bezeichnung -> Buchungsjournal seit $datum", NULL);
            $bg = new berlussimo_global ();
            $link = route('legacy::buchen::index', ['option' => 'buchungs_journal_druckansicht'], false);
            $bg->monate_jahres_links($jahr, $link);
            $buchung->buchungsjournal_startzeit_druck(session()->get('geldkonto_id'), $datum);
            $form->ende_formular();
        }
        break;

    case "buchungs_journal_pdf" :
        if (request()->has('jahr') && request()->has('monat')) {
            $jahr = request()->input('jahr');
            $monat = sprintf("%02d", request()->input('monat'));
            $datum = "$jahr-$monat-01";
        } else {
            $jahr = date("Y");
            $monat = sprintf("%02d", date("m"));
            $datum = "$jahr-$monat-01";
        }
        if (session()->has('geldkonto_id')) {
            $b = new buchen ();
            $b->buchungsjournal_startzeit_pdf(session()->get('geldkonto_id'), $datum);
        } else {
            echo "Geldkonto auswählen";
        }

        break;

    case "buchungs_journal_jahr_pdf" :
        if (request()->has('jahr')) {
            $jahr = request()->input('jahr');
        } else {
            $jahr = date("Y");
        }
        if (session()->has('geldkonto_id')) {
            $b = new buchen ();
            $b->buchungsjournal_jahr_pdf(session()->get('geldkonto_id'), $jahr);
        } else {
            echo "Geldkonto auswählen";
        }

        break;

    case "reset_kontoauszug" :
        session()->forget('temp_kontoauszugsnummer');
        echo "Temporäre Kontoauszugsnummer wurde gelöscht.<br>";
        echo "Sie werden weitergeleitet.";
        weiterleiten_in_sec(route('legacy::buchen::index', ['option' => 'buchungs_journal'], false), 1);
        break;

    case "buchungsbeleg_ansicht" :
        $buchungsnr = request()->input('buchungsnr');
        $form = new formular ();
        $form->erstelle_formular("Ansicht Buchungsbeleg für Buchungsnummer $buchungsnr", NULL);
        $b = new buchen ();
        $b->buchungsbeleg_ansicht($buchungsnr);
        $form->ende_formular();
        break;

    case "konten_uebersicht" :
        $form = new formular ();
        $form->fieldset("Buchungen -> Kostenkontenübersicht", 'kostenkonten');
        $geldkonto_id = session()->get('geldkonto_id');
        if (!empty ($geldkonto_id)) {
            $b = new buchen ();
            $b->buchungskonten_uebersicht($geldkonto_id);
        } else {
            echo "Geldkonto auswählen";
        }
        $form->fieldset_ende();
        break;

    case "konten_uebersicht_pdf" :
        $link = route('legacy::buchen::index', ['option' => 'konten_uebersicht'], false);
        $form = new formular ();
        $form->fieldset("Buchungen -> Kostenkontenübersicht als PDF", 'kostenkonten');
        $geldkonto_id = session()->get('geldkonto_id');
        if (!empty ($geldkonto_id)) {
            $b = new buchen ();
            $b->buchungskonten_uebersicht_pdf($geldkonto_id);
        } else {
            echo "Geldkonto auswählen";
        }
        $form->fieldset_ende();
        break;

    case "buchungskonto_summiert_xls" :
        if (session()->has('geldkonto_id')) {
            if (request()->has('jahr')) {
                $jahr = request()->input('jahr');
            } else {
                $jahr = date("Y");
            }

            $weg = new weg ();
            $weg->kontobuchungen_anzeigen_jahr_xls(session()->get('geldkonto_id'), $jahr);
        } else {
            fehlermeldung_ausgeben("Geldkonto wählen!!!");
        }
        break;

    case "konto_uebersicht" :
        $form = new formular ();
        $form->erstelle_formular('Buchungen -> Kostenkontenübersicht dynamisch', null);
        $geldkonto_id = session()->get('geldkonto_id');
        if (!empty ($geldkonto_id)) {
            $b = new buchen ();
            $b->form_kontouebersicht();
        } else {
            echo "Geldkonto auswählen";
        }
        $form->fieldset_ende();
        break;

    case "kostenkonto_suchen" :
        $b = new buchen ();
        $link = route('legacy::buchen::index', ['option' => 'konto'], false);
        $form = new formular ();
        $form->fieldset("Buchungen -> Kostenkontenübersicht dynamisch", 'kostenkonten_dyn');
        $kostentraeger_typ = request()->input('kostentraeger_typ');
        $kostentraeger_id = request()->input('kostentraeger_id');
        $kostenkonto = request()->input('kostenkonto');
        $geldkonto_id = request()->input('geldkonto_id');
        $anfangsdatum = request()->input('anfangsdatum');
        $enddatum = request()->input('enddatum');
        $b->kontobuchungen_anzeigen_dyn($geldkonto_id, $kostenkonto, $anfangsdatum, $enddatum, $kostentraeger_typ, $kostentraeger_id);
        $form->fieldset_ende();
        break;

    /* Einsicht aller Buchungen zu einem Kostenkonto unabhängig vom Geldkonto */
    case "buchungen_zu_kostenkonto" :
        $b = new buchen ();
        $link = route('legacy::buchen::index', ['option' => 'konto'], false);
        $f = new formular ();
        $f->erstelle_formular("Buchungen -> Buchungen zu einem Kostenkonto finden", null);
        if (!request()->isMethod('post')) {
            $b->form_buchungen_zu_kostenkonto();
        } else {
            $kostenkonto = request()->input('kostenkonto');
            $anfang = date_german2mysql(request()->input('anfangsdatum'));
            $ende = date_german2mysql(request()->input('enddatum'));
            $b->finde_buchungen_zu_kostenkonto($kostenkonto, $anfang, $ende);
        }
        $f->ende_formular();
        break;

    case "eingangsbuch_kurz" :
        if (request()->has('partner_wechseln')) {
            session()->forget('partner_id');
        }
        if (request()->has('partner_id')) {
            session()->put('partner_id', request()->input('partner_id'));
        }
        $r = new rechnungen ();
        $p = new partner ();
        $partner_id = session()->get('partner_id');

        if (request()->has('monat') && request()->has('jahr')) {
            if (request()->input('monat') != 'alle') {
                session()->put('monat', sprintf('%02d', request()->input('monat')));
            } else {
                session()->put('monat', request()->input('monat'));
            }
            session()->put('jahr', request()->input('jahr'));
        }

        if (empty ($partner_id)) {
            $p->partner_auswahl();
        } else {
            $monat = session()->get('monat');
            $jahr = session()->get('jahr');

            if (empty ($monat) or empty ($jahr)) {
                $monat = date("m");
                $jahr = date("Y");
            }
            $r->rechnungseingangsbuch_kurz('Partner', $partner_id, $monat, $jahr, 'Rechnung');
        }
        $fragez = strpos($_SERVER ['REQUEST_URI'], '?');
        $last_url = substr($_SERVER ['REQUEST_URI'], $fragez);
        session()->put('last_url', $last_url);
        break;

    case "ausgangsbuch_kurz" :
        if (request()->has('partner_wechseln')) {
            session()->forget('partner_id');
        }
        if (request()->has('partner_id')) {
            session()->put('partner_id', request()->input('partner_id'));
        }
        $r = new rechnungen ();
        $p = new partner ();
        $partner_id = session()->get('partner_id');

        if (request()->has('monat') && request()->has('jahr')) {
            if (request()->input('monat') != 'alle') {
                session()->put('monat', sprintf('%02d', request()->input('monat')));
            } else {
                session()->put('monat', request()->input('monat'));
            }
            session()->put('jahr', request()->input('jahr'));
        }

        if (empty ($partner_id)) {
            $p->partner_auswahl();
        } else {
            $monat = session()->get('monat');
            $jahr = session()->get('jahr');

            if (empty ($monat) or empty ($jahr)) {
                $monat = date("m");
                $jahr = date("Y");
            }
            $r->rechnungsausgangsbuch_kurz('Partner', $partner_id, $monat, $jahr, 'Rechnung');
        }

        $fragez = strpos($_SERVER ['REQUEST_URI'], '?');
        // echo "FFF $fragez";
        $last_url = substr($_SERVER ['REQUEST_URI'], $fragez);
        // echo $last_url;
        session()->put('last_url', $last_url);
        break;

    /* Monatsbericht ohne ausgezogene Mietern */
    case "monatsbericht_o_a" :
        $b = new buchen ();
        $link = route('legacy::buchen::index', ['option' => 'konto'], false);
        $form = new formular ();
        $form->fieldset("Monatsbericht", 'monatsbericht');
        $b->monatsbericht_ohne_ausgezogene();
        $form->fieldset_ende();
        break;
    /* Monatsbericht mit ausgezogenen Mietern */
    case "monatsbericht_m_a" :
        $b = new buchen ();
        $link = route('legacy::buchen::index', ['option' => 'konto'], false);
        $form = new formular ();
        $form->fieldset("Monatsbericht", 'monatsbericht');
        $b->monatsbericht_mit_ausgezogenen();
        $form->fieldset_ende();
        break;

    case "test" :
        ob_end_clean(); // ausgabepuffer leeren
        $pdf = new Cezpdf ('a4', 'portrait');
        $pdf->ezSetCmMargins(4.5, 2.5, 2.5, 2.5);
        $berlus_schrift = 'pdfclass/fonts/Times-Roman.afm';
        $text_schrift = 'pdfclass/fonts/Arial.afm';
        $pdf->addJpegFromFile('includes/logos/hv_logo198_80.jpg', 450, 780, 100, 42);
        $pdf->setLineStyle(0.5);
        $pdf->selectFont($berlus_schrift);
        $pdf->addText(42, 743, 6, "BERLUS HAUSVERWALTUNG - Fontanestr. 1 - 14193 Berlin");
        $pdf->line(42, 750, 550, 750);
        $seite = $pdf->ezGetCurrentPageNumber();
        $alle_seiten = $pdf->ezPageCount;
        $data55 = array(
            array(
                'num' => 1,
                'name' => 'gandalf',
                'type' => 'wizard'
            ),
            array(
                'num' => 2,
                'name' => 'bilbo',
                'type' => 'hobbit',
                'url' => 'http://www.ros.co.nz/pdf/'
            ),
            array(
                'num' => 3,
                'name' => 'frodo',
                'type' => 'hobbit'
            ),
            array(
                'num' => 4,
                'name' => 'saruman',
                'type' => 'bad dude',
                'url' => 'http://sourceforge.net/projects/pdf-php'
            ),
            array(
                'num' => 5,
                'name' => 'sauron',
                'type' => 'really bad dude'
            )
        );
        $pdf->ezTable($data55);
        $pdf->ezStream();
        break;

    default :
        if (request()->has('geldkonto_id')) {
            session()->put('geldkonto_id', request()->input('geldkonto_id'));
        }

        break;

    case "kosten_einnahmen" :
        $f = new formular ();
        $b = new buchen ();
        $f->fieldset("Kosten & Einnahmen", 'kosten_einnahmen');
        $b->form_kosten_einnahmen();
        $f->fieldset_ende();
        break;

    case "kosten_einnahmen_pdf" :

        $f = new formular ();
        $b = new buchen ();
        $f->fieldset("Kosten & Einnahmen", 'kosten_einnahmen');
        $arr [0] ['GELDKONTO_ID'] = '4';
        $arr [0] ['OBJEKT_NAME'] = 'II';
        $arr [1] ['GELDKONTO_ID'] = '5';
        $arr [1] ['OBJEKT_NAME'] = 'III';
        $arr [2] ['GELDKONTO_ID'] = '6';
        $arr [2] ['OBJEKT_NAME'] = 'V';
        $arr [3] ['GELDKONTO_ID'] = '11';
        $arr [3] ['OBJEKT_NAME'] = 'E';
        $arr [4] ['GELDKONTO_ID'] = '8';
        $arr [4] ['OBJEKT_NAME'] = 'GBN';
        $arr [5] ['GELDKONTO_ID'] = '7';
        $arr [5] ['OBJEKT_NAME'] = 'HW';
        $arr [6] ['GELDKONTO_ID'] = '10';
        $arr [6] ['OBJEKT_NAME'] = 'FON';
        $arr [7] ['GELDKONTO_ID'] = '12';
        $arr [7] ['OBJEKT_NAME'] = 'LAGER';

        if (request()->has('monat') && request()->has('jahr')) {
            if (request()->input('monat') != 'alle') {
                session()->put('monat', sprintf('%02d', request()->input('monat')));
            } else {
                session()->put('monat', request()->input('monat'));
            }
            session()->put('jahr', request()->input('jahr'));
            $jahr = session()->get('jahr');
            $monat = session()->get('monat');
        }
        if (empty ($monat) or empty ($jahr)) {
            $monat = date("m");
            $jahr = date("Y");
        }
        $b->kosten_einnahmen_pdf($arr, $monat, $jahr);
        $f->fieldset_ende();
        break;

    case "buchung_suchen" :
        $f = new formular ();
        $b = new buchen ();
        //$f->fieldset("Buchung suchen", 'buchung_suchen');
        $b->form_buchung_suchen();
        //$f->fieldset_ende();
        break;

    case "buchung_suchen_1" :
        $f = new formular ();
        $b = new buchen ();
        $b->form_buchung_suchen();
        $f->fieldset("Suchergebnis", 'buchung_suchen');
        $geld_konto_id = request()->input('geld_konto');
        $betrag = request()->input('betrag');
        $ausdruck = request()->input('ausdruck');
        $anfangsdatum = request()->input('anfangsdatum');
        $enddatum = request()->input('enddatum');
        $kontoauszug = request()->input('kontoauszug');
        $kostenkonto = request()->input('kostenkonto');
        $kostentraeger_typ = request()->input('kostentraeger_typ');
        $kostentraeger_bez = request()->input('kostentraeger_id');

        $kostenkonto = request()->input('kostenkonto');

        // echo "$geld_konto_id, $betrag, $ausdruck, $anfangsdatum, $enddatum, $kostenkonto";

        // ##########
        if ($geld_konto_id != 'alle') {
            $where [] = " GELDKONTO_ID='$geld_konto_id' ";
        }

        if ($betrag) {
            $betrag = nummer_komma2punkt($betrag);
            $where [] = " BETRAG='$betrag' ";
        }

        if ($ausdruck) {
            $ausdruck_arr = explode('|', $ausdruck);
            $anz_aus = count($ausdruck_arr);
            for ($ss = 0; $ss < $anz_aus; $ss++) {
                $ausdruck_n = $ausdruck_arr [$ss];
                if ($anz_aus == 1) {
                    $where_or = " VERWENDUNGSZWECK LIKE '%$ausdruck_n%' ";
                } else {
                    if ($ss < $anz_aus - 1) {
                        $where_or .= " VERWENDUNGSZWECK LIKE '%$ausdruck_n%' OR ";
                    } else {
                        $where_or .= " VERWENDUNGSZWECK LIKE '%$ausdruck_n%' ";
                    }
                }
            }
            $where [] = $where_or;
        }

        if ($anfangsdatum) {
            $anfangsdatum = date_german2mysql($anfangsdatum);
        }

        if ($enddatum) {
            $enddatum = date_german2mysql($enddatum);
        }

        if ($anfangsdatum && $enddatum) {
            $where [] = " DATUM BETWEEN '$anfangsdatum' AND '$enddatum' ";
        }

        if ($anfangsdatum && !$enddatum) {
            $where [] = " DATUM = '$anfangsdatum'";
        }

        if ($enddatum && !$anfangsdatum) {
            $where [] = " DATUM = '$enddatum'";
        }

        if ($kontoauszug) {
            $where [] = " KONTO_AUSZUGSNUMMER='$kontoauszug' ";
        }

        if (!empty ($kostenkonto)) {
            $where [] = " KONTENRAHMEN_KONTO='$kostenkonto' ";
        }

        if ($kostentraeger_typ) {
            $where [] = " KOSTENTRAEGER_TYP='$kostentraeger_typ' ";
        }

        if ($kostentraeger_bez) {
            $kostentraeger_id = $b->kostentraeger_id_ermitteln($kostentraeger_typ, $kostentraeger_bez);
            $where [] = " KOSTENTRAEGER_ID='$kostentraeger_id' ";
        }

        if (!$betrag && !$ausdruck && !$anfangsdatum && !$enddatum && !$kontoauszug && !$kostenkonto && !$kostentraeger_typ) {
            echo "FEHLER KEINE AUSWAHL GETROFFEN";
        } else {
            //echo '<pre>';
            $anzahl_kriterien = count($where);
            $abfrage = "SELECT * FROM GELD_KONTO_BUCHUNGEN WHERE";
            for ($a = 0; $a < $anzahl_kriterien; $a++) {
                if ($a == 0) {
                    $abfrage .= $where [$a];
                } else {
                    // $teil = $where[$a];
                    // if(strstr($teil, ' OR ', true)){
                    // $abfrage .= $where[$a];
                    // }else{
                    $abfrage .= '&&' . $where [$a];
                    // }
                }
            }
            $abfrage .= " && AKTUELL='1' ORDER BY DATUM ASC, KOSTENTRAEGER_TYP, KOSTENTRAEGER_ID";
            if (request()->has('submit_php')) {
                if ($ausdruck != '' or $betrag != '' or $kostenkonto != '') {
                    $b->finde_buchungen($abfrage);
                } else {
                    echo "Bitte geben Sie den gesuchten Betrag, Ausdruck oder ein Kostenkonto ein.";
                }
            }
            /* PDF-Ausgabe */
            if (request()->has('submit_pdf')) {
                if ($ausdruck != '' or $betrag != '' or $kostenkonto != '') {
                    $b->finde_buchungen_pdf($abfrage);
                } else {
                    echo "Bitte geben Sie den gesuchten Betrag, Ausdruck oder ein Kostenkonto ein.";
                }
            }
        }

        $f->fieldset_ende();
        break;

    case "kostenkonto_pdf" :
        $b = new buchen ();
        $b->form_kostenkonto_pdf();
        break;

    /* mt940 Kontoauszug test */
    case "mt940" :
        $mt = new mt940 ();
        // $mt->import('mt940.txt');
        // $mt->daten_anzeigen();
        $mt->feld_definition();
        break;

    /* CSV Kontoauszug */
    case "kontoauszug_csv" :
        $datei = "temp/berlussimo_auszug.csv.csv"; // DATEINAME
        $tabelle_in_gross = strtoupper($datei); // Tabelle in GROßBUCHSTABEN
        $array = file($datei); // DATEI IN ARRAY EINLESEN
        // echo $array[0]; //ZEILE 0 mit Überschriften
        $anz_zeilen = count($array);
        /* Ausgabe ab 1. zeile */
        echo "<table class=\"sortable\">";
        echo "<tr><th>Datum</th><th>VZweck</th><th>Auftraggeber</th><th>Betrag</th><th>KONTO</th><th>BELEG</th><th>OPTION</th></tr>";
        $bb = new buchen ();
        if (session()->has('geldkonto_id')) {
            $geldkonto_id = session()->get('geldkonto_id');
        }
        if (session()->has('partner_id')) {
            $partner_id = session()->get('partner_id');
        }

        if (empty ($geldkonto_id) or empty ($partner_id)) {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\InfoMessage('Geldkonto und Partner wählen')
            );
            
        }
        if (request()->has('start')) {
            $start = request()->input('start');
        } else {
            $start = 1;
        }
        echo '<pre>';
        for ($a = $start; $a < $anz_zeilen; $a++) {
            $feld = explode(';', $array [$a]);
            $auszugsnr = $feld [0];
            $buch_datum = $feld [1];
            $val_datum = $feld [2];
            $v_zweck = $feld [3] . ' ' . $feld [4] . ' ' . $feld [5];

            $btext = $feld [6];
            $betrag = nummer_komma2punkt($feld [7]);

            $auftraggeber_text = $feld [8] . ' ' . $feld [9];
            $auftraggeber_ktonr = $feld [10];
            $auftraggeber_blz = $feld [11];

            // echo $array[$a].'<br>';
            // echo "<tr><td>$auszugsnr</td><td>$buch_datum</td><td>$val_datum</td><td>$v_zweck</td><td>$btext</td><td>$betrag</td></tr>";
            echo "<tr><td>$val_datum</td><td>$v_zweck</td><td>$auftraggeber_text<br>$auftraggeber_ktonr<br>$auftraggeber_blz<td>$betrag</td><td>";
            $bb->dropdown_kostenrahmen_nr('Kostenkonto', 'kostenkonto', 'GELDKONTO', $geldkonto_id, '');
            echo "</td>";
            if ($a == $start) {

                $_zahlen_arr = gib_zahlen($v_zweck);
                if (is_array($_zahlen_arr)) {
                    $anz_m = count($_zahlen_arr);
                    for ($m = 0; $m < $anz_m; $m++) {
                        $re = new rechnung ();
                        if (!empty($re->rechnung_finden_nach_rnr($_zahlen_arr [$m]))) {
                            $rnr_kurz = $_zahlen_arr [$m];
                        }
                    }
                }
                // echo $rnr_kurz;
                if (empty ($rnr_kurz)) {
                    $rnr_kurz = null;
                }
                echo "<td>";
                if ($betrag > 0) {
                    $bb->dropdown_ra_buch('Partner', $partner_id, 200, $rnr_kurz);
                } else {
                    $bb->dropdown_re_buch('Partner', $partner_id, 200, $rnr_kurz);
                }
                echo "</td><td>";
                $f = new formular ();
                $f->button_js('snd_buchen', 'buchen', null);
                echo "</td>";
            }

            echo "</tr>";
        }
        break;

    case "einnahmen_ausgaben" :
        $b = new buchen ();
        $b->einnahmen_ausgaben(555, 2013);

        break;

    case "excel_buchen" :
        session()->put('umsatz_id_temp', 0);
        $sep = new sepa ();

        if (request()->exists('upload')) {
            session()->forget('umsaetze_nok');
            session()->forget('umsaetze_ok');
            session()->forget('umsatz_konten');
            session()->forget('umsatz_stat');
            session()->forget('umsatz_konten_start');
            session()->forget('umsatz_id_temp');
            session()->forget('temp_kontostand');
            session()->forget('temp_datum');
            session()->forget('geldkonto_id');
            session()->forget('temp_kontoauszugsnummer');
            session()->forget('kos_typ');
            session()->forget('kos_id');

            $sep->form_upload_excel_ktoauszug();
        }

        if (request()->hasFile('file')) {
            $xlsx = new SimpleXLSX(request()->file('file')->getRealPath());

            /* Kontostände abrufen */
            $arr_konten = $xlsx->rows(4);
            $anz_konten = count($arr_konten);
            for ($a = 2; $a < $anz_konten; $a++) {
                $kto_auszug_1 = $arr_konten [$a] [3];
                if (!empty ($kto_auszug_1)) {
                    $kto_nr = $arr_konten [$a] [1];
                    $ksa = str_replace('.', '', $arr_konten [$a] [7]);
                    $kse = str_replace('.', '', $arr_konten [$a] [9]);
                    $ktnr_arr = explode('/', $arr_konten [$a] [1]); // KTO BLZ
                    $blz = $ktnr_arr [0];
                    $kto_full = $ktnr_arr [1];

                    if (strpos($kto_full, 'EUR')) {
                        $kto_arr = explode('EUR', $kto_full);
                        $kto = $kto_arr [0];
                    } else {
                        $kto = substr($kto_full, 0, -3);
                    }

                    /* Suche nach KTO und BLZ */
                    $gk = new gk ();
                    $gk_id = $gk->get_geldkonto_id2($kto, $blz);
                    /* Suche nach generierte IBAN */
                    if (!$gk_id) {
                        $sep = new sepa ();
                        $IBAN = $sep->get_iban_bic($kto, $blz);
                        $gk_id = $gk->get_geldkonto_id2($kto, $blz, $IBAN);
                    }
                    /* Nach Bezeichnung */
                    if (!$gk_id) {
                        $gk_id = $gk->get_geldkonto_id($arr_konten [$a] [0]);
                    }

                    if ($gk_id) {
                        session()->put("umsatz_stat.$gk_id.kontonr", $kto_nr);
                        session()->put("umsatz_stat.$gk_id.auszug", $kto_auszug_1);
                        session()->put("umsatz_stat.$gk_id.ksa", $ksa);
                        session()->put("umsatz_stat.$gk_id.kse", $kse);

                        // #########

                        if (!session()->has('umsatz_konten')) {
                            session()->push('umsatz_konten', $gk_id);
                        } else {
                            if (!in_array($gk_id, session()->get('umsatz_konten'))) {
                                session()->push('umsatz_konten', $gk_id);
                            }
                        }
                    } else {
                        $bez = $arr_konten [$a] [0];
                        throw new \App\Exceptions\MessageException(
                            new \App\Messages\WarningMessage("$bez $kto $blz $IBAN nicht gefunden.<br>Schreibweise prüfen.")
                        );
                    }
                }
            }

            $arr = $xlsx->rows(5);
            if (is_array($arr)) {
                $anz = count($arr);

                $tmp_konto_nr = '';
                for ($a = 2; $a < $anz; $a++) {
                    if (!empty ($arr [$a] [3])) { // Kontoauszug

                        $ktnr_arr = explode('/', $arr [$a] [1]); // KTO BLZ
                        $blz = $ktnr_arr [0];

                        $kto_full = $ktnr_arr [1];

                        if (strpos($kto_full, 'EUR')) {
                            $kto_arr = explode('EUR', $kto_full);
                            $kto = $kto_arr [0];
                        } else {
                            $kto = substr($kto_full, 0, -3);
                        }

                        /* Suche nach KTO und BLZ */
                        $gk = new gk ();
                        $gk_id = $gk->get_geldkonto_id2($kto, $blz);
                        /* Suche nach generierte IBAN */
                        if (!$gk_id) {
                            $sep = new sepa ();
                            $IBAN = $sep->get_iban_bic($kto, $blz);
                            $gk_id = $gk->get_geldkonto_id2($kto, $blz, $IBAN);
                        }
                        /* Nach Bezeichnung */
                        if (!$gk_id) {
                            $gk_id = $gk->get_geldkonto_id($arr [$a] [0]);
                        }

                        if ($gk_id) {
                            $arr [$a] ['GK_ID'] = $gk_id;
                            session()->push('umsaetze_ok', $arr [$a]);

                            /* Startdatensätze */
                            if ($arr [$a] [1] != $tmp_konto_nr) {
                                $tmp_konto_nr = $arr [$a] [1];
                                session()->put("umsatz_konten_start.$gk_id", count(session()->get('umsaetze_ok')));
                            }
                        } else {
                            session()->push('umsaetze_nok', $arr [$a]);
                        }
                    }
                }
                if (is_array(session()->get('umsaetze_nok')) or is_array(session()->get('umsaetze_ok'))) {
                    weiterleiten(route('legacy::buchen::index', ['option' => 'excel_buchen_session'], false));
                } else {
                    fehlermeldung_ausgeben("Keine Daten aus der Importdatei übernommen!");
                }
            } else {
                fehlermeldung_ausgeben("Keine Daten in der Importdatei");
            }
        }
        break;

    case "uebersicht_excel_konten" :
        $sep = new sepa ();
        $sep->uebersicht_excel_konten();
        break;

    case "excel_buchen_session" :
        if (request()->has('next')) {
            session()->put('umsatz_id_temp', session()->get('umsatz_id_temp') + 1);
        }

        if (request()->has('vor')) {
            session()->put('umsatz_id_temp', session()->get('umsatz_id_temp') - 1);
        }

        $anz_ok = count(session()->get('umsaetze_ok'));
        if (session()->get('umsatz_id_temp') >= $anz_ok or session()->get('umsatz_id_temp') < 0) {
            session()->put('umsatz_id_temp', 0);
        }

        if (request()->has('ds_id') && is_numeric(request()->input('ds_id'))) {
            if (request()->input('ds_id') > 0 && request()->input('ds_id') <= count(session()->get('umsaetze_ok'))) {
                session()->put('umsatz_id_temp', request()->input('ds_id') - 1);
            } else {
                session()->put('umsatz_id_temp', 0);
            }
            ob_clean();
            weiterleiten(route('legacy::buchen::index', ['option' => 'excel_buchen_session'], false));
        }

        if (!session()->has('umsatz_id_temp')) {
            session()->put('umsatz_id_temp', '0');
        }

        $sep = new sepa ();
        $sep->status_excelsession();
        $sep->form_excel_ds(session()->get('umsatz_id_temp'));
        $bu = new buchen ();
        $bu->buchungsjournal_auszug(session()->get('geldkonto_id'), session()->get('temp_kontoauszugsnummer'));

        break;

    case "excel_buchen_ALT" :
        if (!request()->hasFile('file')) {
            echo '<h1>Upload</h1>
		<form method="post" enctype="multipart/form-data">
		*.XLSX <input type="file" name="file"  />&nbsp;&nbsp;<input type="submit" value="Parse" />
		</form>';
        } else {
            $xlsx = new SimpleXLSX(request()->file('file')->getRealPath());

            echo '<pre>';
            // print_r($xlsx->rows(5));

            echo '<h1>Parsing Result</h1>';

            echo "<table border=\"1\" cellpadding=\"3\" style=\"border-collapse: collapse\">";
            $arr = $xlsx->rows(5);
            $anz = count($arr);
            for ($a = 2; $a < $anz; $a++) {
                if (empty ($arr [$a] [3])) { // Kontoauszug
                    // echo $arr[$a][0]."<br>";
                } else {
                    echo "<tr><td>";
                    echo $arr [$a] [0] . "<br>"; // Kontobez
                    $gk = new gk ();
                    // $gk_id = $gk->get_geldkonto_id($arr[$a][0]);
                    // echo "<b>$gk_id</b>";
                    echo "</td><td>";
                    $ktnr_arr = explode('/', $arr [$a] [1]); // KTO BLZ
                    $blz = $ktnr_arr [0];

                    $kto_full = $ktnr_arr [1];
                    if (strpos($kto_full, 'EUR')) {
                        $kto_arr = explode('EUR', $kto_full);
                        $kto = $kto_arr [0];
                    } else {
                        $kto = substr($kto_full, 0, -3);
                    }

                    $gk_id = $gk->get_geldkonto_id2($kto, $blz);
                    if (!$gk_id) {
                        $sep = new sepa ();
                        $IBAN = $sep->get_iban_bic($kto, $blz);
                        // $kto = substr($ktnr_arr[1],0,-3);
                        $gk_id = $gk->get_geldkonto_id2($kto, $blz, $IBAN);
                    }

                    if (!$gk_id) {
                        $gk_id = $gk->get_geldkonto_id($arr [$a] [0]);
                        if (!$gk_id) {
                            echo "Kein Konto mit BEZ " . $arr [$a] [0] . "<br>";
                            echo "$kto $blz " . $arr [$a] [0] . " prüfen!!!";
                        }
                    }
                    if ($gk_id) {
                        echo $gk_id;
                    }

                    // echo "</td><td>";
                    // echo "$kto $blz<br>";
                    echo "</td><td>";
                    echo $arr [$a] [6] . "<br>"; // DatumVALUTE
                    $datum = $arr [$a] [6];
                    echo "</td><td>";
                    $auszug = sprintf('%01d', $arr [$a] [3]);
                    echo "$auszug<br>"; // Auszug
                    echo "</td><td>";

                    $betrag = str_replace('.', '', $arr [$a] [7]);
                    echo $betrag . "<br>"; // BETRAG
                    echo "</td><td>";
                    // echo $arr[$a][13]."<br>";//Buchungstext
                    echo "</td><td>";
                    // echo $arr[$a][14]."<br>";//vZweck###############################################

                    $pos_svwz = strpos(strtoupper($arr [$a] [14]), 'SVWZ+');
                    if ($pos_svwz == true) {

                        // echo $arr[$a][14]."<br>";//vZweck###############################################
                        // $arr[$a][14] = substr($arr[$a][14],$pos_svwz+5);
                    }

                    echo $arr [$a] [14] . "<br>"; // vZweck###############################################
                    // SVWZ+

                    /* Suche nach SEPA-Dateien */

                    /* LASTSCHRIFTEN MIETEN UND HAUSGELD */
                    if ($gk_id) {

                        if ($arr [$a] [13] == 'SEPA-LS SAMMLER-HABEN') {
                            echo "<b>SEPA-LASTSCHRIFT-SAMMLER</b>";
                            $sep = new sepa ();
                            $ls_arr = $sep->get_sepa_lsfiles_arr_gk($gk_id);
                            if (!empty($ls_arr)) {
                                $anz_ls = count($ls_arr);
                                $z = 0;
                                for ($ls = 0; $ls < $anz_ls; $ls++) {
                                    $summe = nummer_punkt2komma($ls_arr [$ls] ['SUMME']);
                                    $datei = $ls_arr [$ls] ['DATEI'];
                                    if ($summe == $betrag) {
                                        $z++;
                                        echo "<hr>$z. <a href='" . route('legacy::sepa::index', ['option' => 'ls_auto_buchen_file', 'datei' => $datei]) . "'>AUTOBUCHEN $datei $summe</a>";
                                    }
                                }
                            }
                        }
                        /* ÜBERWEISUNGSAMMLER SEPA */
                        if ($arr [$a] [13] == 'SEPA-UEBERWEIS.SAMMLER-SOLL') {
                            echo "<b>SEPA-ÜBERWEISUNG SAMMLER</b>";
                            $sep = new sepa ();
                            $ue_arr = $sep->sepa_files_arr($gk_id);
                            if (!empty($ue_arr)) {
                                $anz_ue = count($ue_arr);
                                $z = 0;
                                for ($ls = 0; $ls < $anz_ue; $ls++) {
                                    $summe = nummer_punkt2komma($ue_arr [$ls] ['SUMME']);
                                    $datei = $ue_arr [$ls] ['FILE'];
                                    if ($summe == ($betrag * -1)) {
                                        $z++;
                                        echo "<hr>$z. <a href='" . route('legacy::sepa::index', ['option' => 'excel_ue_autobuchen', 'datei' => $datei, 'auszug' => $auszug, 'gk_id' => $gk_id, 'datum' => $datum]) . "'>AUTOBUCHEN $datei $summe</a>";
                                    }
                                }
                            }
                        }

                        /* EINZELÜBERWEISUNGEN HABEN */
                        if ($arr [$a] [13] == 'SEPA-UEBERWEIS.HABEN EINZEL') {
                            echo "<b>EINNAHME EINZELBUCHUNG</b><br>";
                            /* Mietzahlungen */
                            if (strpos(strtolower($arr [$a] [14]), 'miete')) {
                                echo "--MIETE--";
                            }

                            if (strpos(strtolower($arr [$a] [14]), 'hausgeld') or strpos(strtolower($arr [$a] [14]), 'wohngeld')) {
                                echo "--HAUSGELD--";
                            }

                            $pos_svwz = strpos(strtoupper($arr [$a] [14]), 'SVWZ+');
                            if ($pos_svwz == true) {

                                echo $arr [$a] [14] . "<br>"; // vZweck###############################################
                                $arr [$a] [14] = substr($arr [$a] [14], $pos_svwz + 5);
                            }
                            echo $arr [$a] [14] . "<br>"; // vZweck###############################################
                        }

                        /* EINZELÜBERWEISUNGEN DAUERAUFTRAG HABEN */
                        if ($arr [$a] [13] == 'SEPA Dauerauftragsgutschrift') {
                            echo "<b>DAUERAUFTRAG EINNAHME EINZELBUCHUNG</b>";
                        }

                        /* EINZELABBUCHUNG LASTSCHRIFFT SOLL */
                        if ($arr [$a] [13] == 'SEPA DIRECT DEBIT (EINZELBUCHUNG-SOLL, B2B)') {
                            echo "<b>B2B ABBUCHUNG EINZELN</b>";
                        }

                        /* EINZELABBUCHUNG LASTSCHRIFFT SOLL */
                        if ($arr [$a] [13] == 'SEPA-LS EINZELBUCHUNG SOLL') {
                            echo "<b>SEPA-LS ABBUCHUNG EINZELN</b>";
                        }
                    } // ENDE IF GK VORHANDEN

                    echo "</td><td>";
                    echo $arr [$a] [25] . "<br>"; // Ktoinh
                    echo "</td><td>";
                    echo $arr [$a] [26] . "<br>"; // IBAN
                    echo $arr [$a] [27] . "<br>"; // BIC
                    echo "</td><td>";

                    echo "</td><tr>";
                }
            }
            echo "</table>";
        }

        break;

    case "excel_einzelbuchung" :
        $kostentraeger_typ = request()->input('kostentraeger_typ');
        $kostentraeger_id = request()->input('kostentraeger_id');
        $kto_auszugsnr = session()->get('temp_kontoauszugsnummer');
        $datum = date_german2mysql(session()->get('temp_datum'));
        $betrag = nummer_komma2punkt(request()->input('betrag'));
        $kostenkonto = request()->input('kostenkonto');
        $vzweck = request()->input('text');
        $geldkonto_id = session()->get('geldkonto_id');
        $rechnungsnr = $kto_auszugsnr;

        if (request()->has('mwst')) {
            $mwst = $betrag / 119 * 19;
        } else {
            $mwst = '0.00';
        }

        $bu = new buchen ();
        $bu->geldbuchung_speichern($datum, $kto_auszugsnr, $rechnungsnr, $betrag, $vzweck, $geldkonto_id, $kostentraeger_typ, $kostentraeger_id, $kostenkonto, $mwst);

        weiterleiten(route('legacy::buchen::index', ['option' => 'excel_buchen_session'], false));
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
                    new \App\Messages\ErrorMessage("Bitte geben Sie die Kontrolldaten ein.")
                );
            }
            if (request()->has('mwst')) {
                $mwst = 1;
            } else {
                $mwst = '0';
            }
            $file = request()->input('file');
            $sep = new sepa ();
            $sep->sepa_file_autobuchen($file, session()->get('temp_datum'), session()->get('geldkonto_id'), session()->get('temp_kontoauszugsnummer'), $mwst);
            weiterleiten(route('legacy::buchen::index', ['option' => 'excel_buchen_session'], false));
        } else {
            fehlermeldung_ausgeben("Fehler beim Verbuchen EC232");
        }
        break;

    case "excel_ls_sammler_buchung" :
        hinweis_ausgeben("Bitte warten...3...2...1...");
        $ls_file = request()->input('ls_file');
        $s = new sepa ();
        $s->form_ls_datei_ab($ls_file);

        weiterleiten_in_sec(route('legacy::buchen::index', ['option' => 'excel_buchen_session'], false), 3);
        break;

    case "excel_nok" :
        $gesamt = count(session()->get('umsaetze_nok'));
        for ($a = 0; $a < $gesamt; $a++) {
            $kto_bez = session()->get('umsaetze_nok')[$a][0];
            $kto = session()->get('umsaetze_nok')[$a][1];
            echo "$kto_bez - $kto<br>";
        }
        break;

    case "objekte_anz_einh" :
        $o = new objekt ();
        $o_arr = $o->liste_aller_objekte_kurz();
        $anz = count($o_arr);
        echo "<table class=\"sortable\">";
        echo "<tr><td>OBJEKT</td><td>ANZAHL EINHEITEN</td></tr>";
        for ($a = 0; $a < $anz; $a++) {
            $objekt_id = $o_arr [$a] ['OBJEKT_ID'];
            $objekt_kn = $o_arr [$a] ['OBJEKT_KURZNAME'];
            $anz_einheiten = $o->anzahl_einheiten_objekt($objekt_id);

            echo "<tr><td>$objekt_kn</td><td>$anz_einheiten</td></tr>";
        }
        echo "</table>";
        break;
} // end switch