<?php

if (request()->has('daten')) {
    $daten = request()->input('daten');
}
if (request()->has('schritt')) {
    $schritt = request()->input('schritt');
} else {
    $schritt = '';
}
/* Mieterinformationen über die Buchungsformulare anzeigen */
if (request()->has('mietvertrag_id') && !empty (request()->input('mietvertrag_id'))) {
    $mieter_info = new mietkonto ();
    $mieter_info->erstelle_formular("Mieterinformationen", NULL);
    $mieter_info->mieter_informationen_anzeigen(request()->input('mietvertrag_id'));
    $mieter_info->ende_formular();
}

switch ($schritt) {

    // ################
    case "buchungsauswahl" :
        $form = new mietkonto ();
        $form->erstelle_formular("Buchungsart auswählen", NULL);
        if (request()->has('mietvertrag_id')) {
            /* MAHNSPERRE */
            $dd = new detail ();
            $mahnsperre = $dd->finde_detail_inhalt('MIETVERTRAG', request()->input('mietvertrag_id'), 'Mahnsperre');
            if (!empty ($mahnsperre)) {
                hinweis_ausgeben("<h1>Mahnsperre: Grund: $mahnsperre Bitte unbedingt die Mahnungsabteilung über Zahlung mündlich informieren</h1>");
            }

            $mietvertrag_id = request()->input('mietvertrag_id');
            $buchung = new mietkonto ();
            /*
             * $geldkonto_info = new geldkonto_info;
             * $geldkonto_info->geld_konten_ermitteln('Mietvertrag', $mietvertrag_id);
             */
            $geld = new geldkonto_info ();
            $kontostand_aktuell = nummer_punkt2komma($geld->geld_konto_stand(session()->get('geldkonto_id')));
            if (session()->has('temp_kontostand') && session()->has('temp_kontoauszugsnummer')) {
                $kontostand_temp = nummer_punkt2komma(session()->get('temp_kontostand'));
                echo "<h3>Kontostand am " . session()->get('temp_datum') . " laut Kontoauszug " . session()->get('temp_kontoauszugsnummer') . " war $kontostand_temp €</h3>";
            } else {
                echo "<h3 style=\"color:red\">Kontrolldaten zum Kontoauszug fehlen</h3>";
                echo "<h3 style=\"color:red\">Weiterleitung erfolgt</h3>";
                weiterleiten_in_sec(route('legacy::buchen::index', ['option' => 'kontoauszug_form'], false), 1);
            }
            if ($kontostand_aktuell == $kontostand_temp) {
                echo "<h3>Kontostand aktuell: $kontostand_aktuell €</h3>";
            } else {
                echo "<h3 style=\"color:red\">Kontostand aktuell: $kontostand_aktuell €</h3>";
            }

            $buchung->buchungsauswahl($mietvertrag_id);
        } else {
            // fals keine MV_ID eingegeben wurde, weiterleiten
            warnung_ausgeben("Fehler : Bitte eine Einheit auswählen!");
            weiterleiten(route('legacy::miete_buchen::index', false));
        }
        $form->ende_formular();
        break;

    // ################
    case "auto_buchung":
        /*Automatisches Buchen der Miete wird
         * durch klicken auf Button suhbmit_buchen1 ausgelöst*/

        if (check_datum(request()->input('buchungsdatum'))) {
            $buchungsdatum = date_german2mysql(request()->input('buchungsdatum'));
            if (request()->input('ZAHLBETRAG') === 0) {
                //TODO: check if condition
                throw new \App\Exceptions\MessageException(
                    new \App\Messages\WarningMessage("Die Miete ist in der Mietentwicklung nicht definiert!")
                );
            } else {
                /* Buchungsprozedur */
                $buchen = new mietkonto ();
                $mwst_anteil = request()->input('MWST_ANTEIL');
                $buchen->miete_zahlbetrag_buchen(request()->input('kontoauszugsnr'), request()->input('MIETVERTRAG_ID'), $buchungsdatum, request()->input('ZAHLBETRAG'), request()->input('bemerkung'), request()->input('geld_konto'), $mwst_anteil);
            }
        } else {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\WarningMessage("Datumsformat nicht korrekt!")
            );
        }
        break;
    /* Ende Case */

    /* Case für die manuelle Buchung bzw. Buchung eines anderen Betrages */
    case
    "manuelle_buchung":
        $mietvertrag_id = request()->input('MIETVERTRAG_ID');
        $buchung = new mietkonto ();
        $zahlbetrag = $buchung->nummer_komma2punkt(request()->input('ZAHLBETRAG'));
        $geld_konto_id = request()->input('geld_konto');
        $zahlbetrag = number_format($zahlbetrag, 2, ".", "");
        $summe_forderung_monatlich = $buchung->summe_forderung_monatlich($mietvertrag_id, $buchung->monat_heute, $buchung->jahr_heute);

        if (!request()->has('ZAHLBETRAG')) {
            warnung_ausgeben("Bitte geben Sie einen Betrag bzw. Zahl ein!");
            warnung_ausgeben("Sie werden um einen Schritt zurückversetzt!");
            weiterleiten_in_sec('javascript:history.back();', 5);
        } elseif (!is_numeric($zahlbetrag)) {
            warnung_ausgeben("Bitte geben Sie eine Zahl als Betrag ein!");
            warnung_ausgeben("Sie werden um einen Schritt zurückversetzt!");
            weiterleiten_in_sec('javascript:history.back();', 5);
        } else {

            $zahlbetrag = $buchung->nummer_komma2punkt(request()->input('ZAHLBETRAG'));

            /* Den Zahlbetrag und die Summe der Forderungen auf zwei Nachkommastellen formatieren */
            $zahlbetrag = number_format($zahlbetrag, 2, ".", "");
            $summe_forderung_monatlich = number_format($summe_forderung_monatlich, 2, ".", "");
            if ($summe_forderung_monatlich == 0) {
                $summe_forderung_monatlich = $buchung->summe_forderung_aus_vertrag($mietvertrag_id);
            }
            /* Regelung für die Funktionsaufrufe abhängig vom eingegebenen Zahlbetrag */
            if ($zahlbetrag == $summe_forderung_monatlich) {
                $buchung->buchungsmaske_manuell_gleicher_betrag($mietvertrag_id, $geld_konto_id);
            }
            if ($zahlbetrag > $summe_forderung_monatlich) {
                $buchung->buchungsmaske_manuell_gross_betrag($mietvertrag_id, $geld_konto_id);
            }
            if ($zahlbetrag < $summe_forderung_monatlich && $zahlbetrag > 0) {
                $buchung->buchungsmaske_manuell_kleiner_betrag($mietvertrag_id, $geld_konto_id);
            }
            /* Negativ buchung */
            if ($zahlbetrag < $summe_forderung_monatlich && $zahlbetrag < 0) {
                $buchung->buchungsmaske_manuell_negativ_betrag($mietvertrag_id, $geld_konto_id);
            }
        }
        break;
    /* Ende Case */

    // ################
    case "manuelle_buchung3":
        /*Buchen der Miete wird durch klicken auf Button submit_buchen3 ausgelöst*/
        if (check_datum(request()->input('buchungsdatum'))) {
            $buchungsdatum = date_german2mysql(request()->input('buchungsdatum'));
            $buchen = new mietkonto ();
            /* Buchungsprozedur */
            $buchen = new mietkonto ();
            $buchen->miete_zahlbetrag_buchen(request()->input('kontoauszugsnr'), request()->input('MIETVERTRAG_ID'), $buchungsdatum, request()->input('ZAHLBETRAG'), request()->input('bemerkung'), request()->input('geld_konto'));
        } else {
            warnung_ausgeben("Datumsformat nicht korrekt!");
            warnung_ausgeben("Sie werden um einen Schritt zurückversetzt!");
            weiterleiten_in_sec('javascript:history.back();', 5);
        }
        break;

    // ################
    case "manuelle_buchung4":
        /*Kontonummer des Objektes finden, soll optimiert werden,  da die MV_ids in der Adresse geändert werden können, und die Kontonummer bleibt die gleiche, obwohl der MV vielleicht einem anderen Objekt gehört, erledigt, testen*/

        $mietvertrag_id = request()->input('MIETVERTRAG_ID');
        $buchung = new mietkonto ();
        $buchungsdatum = $buchung->date_german2mysql(request()->input('buchungsdatum'));
        $summe_forderung_monatlich = $buchung->summe_forderung_monatlich($mietvertrag_id, $buchung->monat_heute, $buchung->jahr_heute);
        $zahlbetrag = $buchung->nummer_komma2punkt(request()->input('ZAHLBETRAG'));
        /* Den Zahlbetrag und die Summe der Forderungen auf zwei Nachkommastellen formatieren */
        $zahlbetrag = number_format($zahlbetrag, 2, ".", "");
        $summe_forderung_monatlich = number_format($summe_forderung_monatlich, 2, ".", "");
        // echo "ZB: $zahlbetrag SUMME-F:$summe_forderung_monatlich";

        /* Buchungsprozedur inkl. interne Buchung */
        $buchen = new mietkonto ();
        $buchen->miete_zahlbetrag_buchen(request()->input('kontoauszugsnr'), request()->input('MIETVERTRAG_ID'), $buchungsdatum, request()->input('ZAHLBETRAG'), request()->input('bemerkung'), request()->input('geld_konto'));

        break;

    case "datum_aendern" :
        session()->forget('buchungsdatum');
        session()->forget('temp_kontoauszugsnummer');
        weiterleiten(route('legacy::miete_buchen::index', false));
        break;

    default :
        if (request()->has('objekt_id')) {
            session()->put('objekt_id', request()->input('objekt_id'));
        }
        $info = new mietkonto ();
        $info->letzte_buchungen_anzeigen_vormonat_monat();
        if (!session()->has('objekt_id')) {
            echo "<div class=\"info_feld_oben\">Objekt auswählen</div>";
        }

        if (request()->has('datum_setzen')) {
            session()->put('buchungsdatum', request()->input('tag') . "." . request()->input('monat') . "." . request()->input('jahr'));
            session()->put('temp_kontoauszugsnummer', request()->input('KONTOAUSZUGSNR'));
        }
        echo "<div class=\"datum_zeile\">";
        $datum_form = new mietkonto ();
        if (!session()->has('buchungsdatum') && !session()->has('kontoauszugsnr')) {
            $datum_form->datum_form();
        } else {

            if (session()->has('kontoauszugsnr')) {
                echo "<b>Kontoauszugsnummer:</b> " . session()->get('kontoauszugsnr');
            } else {
                echo "<b>Kontoauszugsnummer eingeben!</b>&nbsp;&nbsp;";
            }
            if (session()->has('buchungsdatum')) {

                echo "<b>Buchungsdatum:</b> " . session()->get('buchungsdatum');
                echo "&nbsp;<a href='" . route('legacy::miete_buchen::index', ['schritt' => 'datum_aendern']) . "'>Datum ändern</a>&nbsp;";
            } else {
                echo "<b>Datum eingeben !</b>";
            }
            $geld = new geldkonto_info ();
            $kontostand_aktuell = nummer_punkt2komma($geld->geld_konto_stand(session()->get('geldkonto_id')));
            if (session()->has('temp_kontostand') && session()->has('temp_kontoauszugsnummer')) {
                $kontostand_temp = nummer_punkt2komma(session()->get('temp_kontostand'));
                echo "<h3>Kontostand am " . session()->get('temp_datum') . " laut Kontoauszug " . session()->get('temp_kontoauszugsnummer') . " war $kontostand_temp €</h3>";
            } else {
                echo "<h3 style=\"color:red\">Kontrolldaten zum Kontoauszug fehlen</h3>";
                echo "<h3 style=\"color:red\">Weiterleitung erfolgt</h3>";
                weiterleiten_in_sec(route('legacy::buchen::index', ['option' => 'kontoauszug_form'], false), 1);
            }
            if ($kontostand_aktuell == $kontostand_temp) {
                echo "<h3>Kontostand aktuell: $kontostand_aktuell €</h3>";
            } else {
                echo "<h3 style=\"color:red\">Kontostand aktuell: $kontostand_aktuell €</h3>";
            }
        }
        echo "</div>";

        objekt_auswahl();
        if (session()->has('objekt_id')) {
            einheiten_liste();
        }
        break;

    case "stornieren" :
        $form = new mietkonto ();
        $form->erstelle_formular("Buchung stornieren", NULL);
        $buchungs_info = new mietkonto ();
        $buchungs_info->buchungsnummer_infos(request()->input('bnr'));
        $form->ende_formular();
        break;

    case "stornierung_in_db" :
        $form = new mietkonto ();
        $form->erstelle_formular("Sicherheitsabfrage", NULL);
        /* Falls NEIN gedrückt */
        if (request()->has('submit_storno_nein')) {
            weiterleiten_in_sec(route('legacy::miete_buchen::index', [], false), 2);
            warnung_ausgeben(("Der Vorgang wurde vom Benutzer abgebrochen. <br> Die Buchung wurde nicht storniert. <br>Bitte warten, Sie werden weitergeleitet."));
        }
        /* Sicherheitsabfrage vor dem Absenden oder Abbrechen */
        if (!request()->has('submit_storno_ja') && !request()->has('submit_storno_nein')) {
            warnung_ausgeben(("Sind Sie sicher, daß Sie die Buchungsnummer " . request()->input('BUCHUNGSNUMMER') . " stornieren möchten?"));
            $form->hidden_feld("BUCHUNGSNUMMER", request()->input('BUCHUNGSNUMMER'));
            for ($a = 0; $a < count(request()->input('MIETBUCHUNGEN')); $a++) {
                $form->hidden_feld("MIETBUCHUNGEN[]", request()->input('MIETBUCHUNGEN')[$a]);
            }
            $form->hidden_feld("schritt", "stornierung_in_db");
            $form->send_button("submit_storno_ja", "JA");
            $form->send_button("submit_storno_nein", "NEIN");
        }
        /* Falls JA gedrückt */
        if (request()->has('submit_storno_ja')) {
            $form->miete_zahlbetrag_stornieren(request()->input('BUCHUNGSNUMMER'));
            for ($a = 0; $a < count(request()->input('MIETBUCHUNGEN')); $a++) {
                $form->mietbuchung_stornieren_intern(request()->input('MIETBUCHUNGEN')[$a]);
            }
            /* Nach dem Stornieren weiterleiten */
            weiterleiten(route('legacy::miete_buchen::index', false), 3);
        }
        $form->ende_formular();
        break;

    case "monatsabschluss" :
        $mietkonto = new mietkonto ();

        if (session()->has('objekt_id')) {
            $objekt_id = session()->get('objekt_id');
            $mein_objekt = new objekt ();
            $liste_haeuser = $mein_objekt->haeuser_objekt_in_arr($objekt_id);

            for ($i = 0; $i < count($liste_haeuser); $i++) {
                $result = DB::select("SELECT * FROM EINHEIT WHERE EINHEIT_AKTUELL='1' && HAUS_ID='" . $liste_haeuser [$i] ['HAUS_ID'] . "' ORDER BY EINHEIT_KURZNAME ASC");
                foreach($result as $row)
                    $einheiten_array [] = $row;
            }

            $einheit_info = new einheit ();
            // ob_start(); //Ausgabepuffer Starten

            $zeile = 0;
            for ($i = 0; $i <= count($einheiten_array); $i++) {
                $einheit_info->get_mietvertrag_id("" . $einheiten_array [$i] ['EINHEIT_ID'] . "");
                $einheit_vermietet = $einheit_info->get_einheit_status("" . $einheiten_array [$i] ['EINHEIT_ID'] . "");
                if ($einheit_vermietet) {
                    $miete = new miete ();
                    $miete->mietkonto_berechnung($einheit_info->mietvertrag_id);
                    $zeile = $zeile + 1;
                    echo "$zeile . $mietkonto->datum_heute Mietvertrag: $einheit_info->mietvertrag_id Saldo: $miete->erg €<br>";
                    $mietkonto->monatsabschluesse_speichern($einheit_info->mietvertrag_id, $miete->erg);
                    $miete->erg = '0.00';
                }
            }
        }
        break;
} // end switch

/* User Funktionen */
function objekt_auswahl()
{
    echo "<div class=\"objekt_auswahl\">";
    $mieten = new mietkonto ();
    $mieten->erstelle_formular("Objekt auswählen...", NULL);

    if (session()->has('objekt_id')) {
        $objekt_kurzname = new objekt ();
        $objekt_kurzname->get_objekt_name(session()->get('objekt_id'));
        echo "<p>&nbsp;<b>Ausgewähltes Objekt</b> -> $objekt_kurzname->objekt_name ->";
        echo "->&nbsp;<a href='" . route('legacy::miete_buchen::index', ['schritt' => 'monatsabschluss']) . "'>Monatsabschluss</a>";
        echo " </p>";
        echo "<div class=\"info_feld_oben\">Ausgewähltes Objekt " . $objekt_kurzname->objekt_name . "<br><b>Einheit auswählen</b><br>WEISS: keine Zahlung im aktuellen Monat.<br>GRAU: Zahlungen wurden gebucht.</div>";
    }

    $objekte = new objekt ();
    $objekte_arr = $objekte->liste_aller_objekte();

    $anzahl_objekte = count($objekte_arr);
    // print_r($objekte_arr);
    $c = 0;
    for ($i = 0; $i < $anzahl_objekte; $i++) {
        echo "<a class=\"objekt_auswahl_buchung\" href='" . route('legacy::miete_buchen::index', ['objekt_id' => $objekte_arr[$i]['OBJEKT_ID']]) . "'>" . $objekte_arr [$i] ['OBJEKT_KURZNAME'] . "</a>&nbsp;<b>|</b>&nbsp;";
        $c++;
        if ($c == 10) {
            echo "<br>";
            $c = 0;
        }
    }
    $mieten->ende_formular();
    echo "</div>";
}

function einheiten_liste()
{
    $mieten = new mietkonto ();
    // $mieten->letzte_buchungen_anzeigen();
    echo "<div class=\"einheit_auswahl\">";
    $mieten->erstelle_formular("Einheit auswählen...", NULL);

    /* Liste der Einheiten falls Objekt ausgewählt wurde */
    if (session()->has('objekt_id')) {
        $objekt_id = session()->get('objekt_id');
        $mein_objekt = new objekt ();
        $liste_haeuser = $mein_objekt->haeuser_objekt_in_arr($objekt_id);

        for ($i = 0; $i < count($liste_haeuser); $i++) {
            $hh_id = $liste_haeuser [$i] ['HAUS_ID'];
            $result = DB::select("SELECT * FROM EINHEIT WHERE EINHEIT_AKTUELL='1' && HAUS_ID='$hh_id' ORDER BY EINHEIT_KURZNAME ASC");
            foreach($result as $row)
                $einheiten_array [] = $row;
        }
    } else {
        /* Liste aller Einheiten da kein Objekt ausgewählt wurde */
        $meine_einheiten = new einheit ();
        $einheiten_array = $meine_einheiten->liste_aller_einheiten();
    }
    // Beispiel für ein Array $sx mit den Spalten $sx['dat'], $sx['name'], $sx['id'].

    $einheiten_array = array_sortByIndex($einheiten_array, 'EINHEIT_KURZNAME');
    // echo "<pre>";
    // print_r($einheiten_array);
    // echo "</pre>";
    $counter = 0;
    $spaltencounter = 0;
    echo "<table>";
    echo "<tr><td valign=\"top\">";
    $einheit_info = new einheit ();
    // $mietkonto2 = new mietkonto;
    // $zeitraum = new zeitraum;
    // foreach ( $[ 'element' ] as $value ) {
    for ($i = 0; $i < count($einheiten_array); $i++) {

        $ee_id = $einheiten_array [$i] ['EINHEIT_ID'];
        $einheit_vermietet = $einheit_info->get_einheit_status($ee_id);
        if ($einheit_vermietet) {
            $einheit_info->get_mietvertrag_id($ee_id);
            /*
             * $mi = new miete;
             * $saldo = $mi->saldo_berechnen($einheit_info->mietvertrag_id);
             *
             * if($saldo==0){
             * $mietkonto_status = "<font id=\"status_neutral\">(0)</font>";
             * }
             * if($saldo>0){
             * $mietkonto_status = "<font id=\"status_positiv\">(+)</font>";
             * }
             * if($saldo<0){
             * $mietkonto_status = "<font id=\"status_negativ\">(-)</font>";
             * }
             */
            $mietkonto_status = '';
            // if(isset($einheit_info->mietvertrag_id)){
            $anzahl_zahlungsvorgaenge = $mieten->anzahl_zahlungsvorgaenge($einheit_info->mietvertrag_id);
            $ekn = $einheiten_array [$i] ['EINHEIT_KURZNAME'];
            if ($anzahl_zahlungsvorgaenge < 1) {

                echo "<a href='" . route('legacy::miete_buchen::index', ['schritt' => 'buchungsauswahl', 'mietvertrag_id' => $einheit_info->mietvertrag_id]) . "' class=\"nicht_gebucht_links\">$ekn</a> $mietkonto_status&nbsp;";
            } else {
                echo "<a href='" . route('legacy::miete_buchen::index', ['schritt' => 'buchungsauswahl', 'mietvertrag_id' => $einheit_info->mietvertrag_id]) . "' class=\"gebucht_links\">$ekn</a> $mietkonto_status&nbsp;";
            }
            echo "<br>"; // Nach jeder Einheit Neuzeile
            $m = new mietvertrag (); // class mietvertrag aus berlussimo_class.php;
            $m1 = new mietvertraege (); // class mietvertraege NEUE KLASSE;
            $mv_ids_arr = $m->get_personen_ids_mietvertrag($einheit_info->mietvertrag_id);
            // $m1->mv_personen_anzeigen($mv_ids_arr); //$mv_ids_arr Array mit personan Ids
            $mieternamen_str = $m1->mv_personen_als_string($mv_ids_arr);
            echo $mieternamen_str . '<br>';
            // echo "<br>"; // Nach jeder Einheit Neuzeile

            // echo "$mietkonto_status";
            // ######mietkonto status ende

            $counter++;
        }
        if ($counter == 10) {
            echo "</td><td valign=\"top\">";
            $counter = 0;
            $spaltencounter++;
        }

        if ($spaltencounter == 5) {
            echo "</td></tr>";
            echo "<tr><td colspan=\"$spaltencounter\"><hr></td></tr>";
            echo "<tr><td valign=\"top\">";

            $spaltencounter = 0;
        }
    }
    echo "</td></tr></table>";
    // echo "<pre>";
    // print_r($einheiten_array);
    // echo "</pre>";
    $mieten->ende_formular();
    echo "</div>";
}

?>
