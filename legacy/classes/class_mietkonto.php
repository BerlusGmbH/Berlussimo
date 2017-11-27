<?php

class mietkonto
{
    /* Funktion-Konstruktor der jedes Mal aufgerufen wird für db connect */
    public $ausgangs_kaltmiete;
    public $ausgangs_kaltmiete_a;
    public $mietvertrag_von;
    public $mietvertrag_bis;
    public $einheit_kurzname_von_mv;
    public $anzahl_personen_im_vertrag;
    public $summe_bk_abrechnung;
    public $summe_wasser_abrechnung;
    public $datum_saldo_vv;
    public $monatsname;
    public $betriebskosten;
    public $heizkosten;

    function mietkonto()
    {
        $this->datum_heute = date("Y-m-d");
        $this->tag_heute = date("d");
        $this->monat_heute = date("m");
        $this->jahr_heute = date("Y");
    }

    /* Datumsfunktionen */

    function date_german2mysql($date)
    {
        $d = explode(".", $date);
        return sprintf("%04d-%02d-%02d", $d [2], $d [1], $d [0]);
    }

    function ein_auszugsdatum_mietvertrag($mietvertrag_id)
    {
        $result = DB::select("SELECT MIETVERTRAG_VON, MIETVERTRAG_BIS FROM MIETVERTRAG WHERE MIETVERTRAG_AKTUELL='1' && MIETVERTRAG_ID='$mietvertrag_id' ORDER BY MIETVERTRAG_DAT DESC LIMIT 0,1");
        $row = $result[0];
        // Setzen von Mietvertrags Vars bzw Einzugsdatum Auszugsdatum
        $this->mietvertrag_von = $row ['MIETVERTRAG_VON'];
        $this->mietvertrag_bis = $row ['MIETVERTRAG_BIS'];
    }

    /* Funktion um Grunddaten aus dem Mietvertrag zu holen */

    function get_person_infos($person_id)
    {
        $result = DB::select("SELECT * FROM PERSON WHERE PERSON_AKTUELL='1' && PERSON_ID='$person_id' ORDER BY PERSON_DAT DESC LIMIT 0,1");
        return $result;
    }

    function get_personen_ids_mietvertrag($mietvertrag_id)
    {
        $result = DB::select("SELECT PERSON_MIETVERTRAG_PERSON_ID FROM PERSON_MIETVERTRAG WHERE PERSON_MIETVERTRAG_MIETVERTRAG_ID='$mietvertrag_id' && PERSON_MIETVERTRAG_AKTUELL='1' ORDER BY PERSON_MIETVERTRAG_ID ASC");
        return $result;
    }

    /* Funktion um Personenanzahl im MV zu ermitteln */

    function get_einheit_id_von_mietvertrag($mietvertrag_id)
    {
        $result = DB::select("SELECT EINHEIT_ID FROM MIETVERTRAG WHERE MIETVERTRAG_AKTUELL='1' && MIETVERTRAG_ID='$mietvertrag_id' ORDER BY MIETVERTRAG_DAT DESC LIMIT 0,1");
        $row = $result[0];
        return $row ['EINHEIT_ID'];
    }

    function letzte_buchungen_anzeigen_vormonat_monat()
    {
        /* Wenn ein Objekt ausgewählt wurde, Kontonummer suchen */
        if (session()->has('objekt_id')) {
            $objekt_info = new objekt ();
            $objekt_info->get_objekt_geldkonto_nr(session()->get('objekt_id'));
            $objekt_kontonummer = $objekt_info->objekt_kontonummer;
            /* Wenn ein Objekt ausgewählt wurde das eine Kontonummer hat, alle Zahlbeträge von diesem Konto selektieren */
            if (isset ($objekt_kontonummer)) {
                $my_array = DB::select("SELECT *
FROM MIETE_ZAHLBETRAG WHERE AKTUELL = '1' && KONTO='$objekt_kontonummer' && DATUM >= DATE_SUB( DATE_FORMAT( CURDATE( ) , '%Y-%m-1' ) , INTERVAL 1 MONTH )
ORDER BY BUCHUNGSNUMMER DESC");
            }            /* Wenn ein Objekt ausgewählt wurde das KEINE Kontonummer hat, werden wie oben alle Zahlbeträge der letzten 2 Monate selektiert */
            else {
                echo "<div class=\"info_feld_oben\">";
                warnung_ausgeben("Das Objekt verfügt über KEINE Geldkontonummer.<br>Keine Zahlungsvorgänge vorhanden.<br>Bitte Geldkontonummer in den Details vom Objekt anlegen.");
                echo "</div>";
            }
        }

        echo "<div class=\"tabelle\">";
        $this->erstelle_formular("Buchungen 2 Mon.", NULL);
        if (!empty($my_array)) {
            echo "<table>";
            echo "<tr class=\"feldernamen\"><td>BNR</td><td>AUSZUG</td><td>DATUM</td><td>EINHEIT</td><td>BETRAG</td><td>BEMERKUNG</td><td>Option</td></tr>\n";
            $numrows = count($my_array);
            for ($i = 0; $i < $numrows; $i++) {

                $datum = date_mysql2german($my_array [$i] ['DATUM']);
                $einheit_info = new mietvertrag ();
                $einheit_id = $einheit_info->get_einheit_id_von_mietvertrag($my_array [$i] ['mietvertrag_id']);
                $einheit_info = new einheit ();
                $einheit_info->get_einheit_info($einheit_id);
                $einheit_kurzname = $einheit_info->einheit_kurzname;
                $buchungsnummer = $my_array [$i] ['BUCHUNGSNUMMER'];
                $bemerkung = $my_array [$i] ['BEMERKUNG'];

                $buchungsdatum = $my_array [$i] ['DATUM'];
                $buchungsmonat_arr = explode('-', $buchungsdatum);
                $buchungsmonat = $buchungsmonat_arr [1];
                $kontoauszugsnr = $my_array [$i] ['KONTOAUSZUGSNR'];
                /* Prüfen ob diesen Monat gebucht wurde */
                $anzahl_zahlbetraege_diesen_monat = $this->anzahl_zahlungsvorgaenge_objekt_konto(session()->get('objekt_id'));
                if ($anzahl_zahlbetraege_diesen_monat > 0) {
                    if ($this->monat_heute > $buchungsmonat) {
                        $storno_link = '';
                    } else {

                        $storno_link = "<a href='" . route('web::miete_buchen::legacy', ['schritt' => 'stornieren', 'bnr' => $buchungsnummer]) . "'>Stornieren</a>";
                    }
                } else {
                    $storno_link = "<a href='" . route('web::miete_buchen::legacy', ['schritt' => 'stornieren', 'bnr' => $buchungsnummer]) . "'>Stornieren</a>";
                }

                echo "<tr class=\"zeile1\"><td>$buchungsnummer</td><td>$kontoauszugsnr</td><td>$datum</td><td>$einheit_kurzname</td><td>" . $my_array [$i] ['BETRAG'] . " €</td><td>$bemerkung</td><td>$storno_link</td></tr>\n";
            }
            echo "</table>";
        } else {
            echo "Keine Buchungen auf dem Objektgeldkonto $objekt_kontonummer!";
        }
        $this->ende_formular();
        echo "</div>";
    }

    /* Ausgabe der Personen_ids als array die im MV stehen */

    function erstelle_formular($name, $action)
    {
        echo "<fieldset class=\"$name\" >";
        echo "<legend>$name</legend>";
        $scriptname = $_SERVER ['REQUEST_URI'];

        if (!isset ($action)) {
            echo "<form class=\"$name\" name=\"$name\" action=\"$scriptname\"  method=\"post\">\n";
        } else {
            echo "<form name=\"$name\" action=\"$action\" method=\"post\">\n";
        }
        echo "\n";
    }

    /* Einheit_id aus dem MV auslesen */

    function anzahl_zahlungsvorgaenge_objekt_konto($objekt_id)
    {
        $objekt_info = new objekt ();
        $objekt_info->get_objekt_geldkonto_nr($objekt_id);
        $objekt_kontonummer = $objekt_info->objekt_kontonummer;

        $result = DB::select("SELECT DATUM, BETRAG, MIETVERTRAG_ID, BEMERKUNG FROM MIETE_ZAHLBETRAG WHERE KONTO='$objekt_kontonummer' && AKTUELL='1' && DATE_FORMAT( DATUM, '%Y-%m' ) = '" . $this->jahr_heute . "-" . $this->monat_heute . "'");

        return count($result);
    }

    /* Suche nach Positionen der BK und HK in forderungen */

    function ende_formular()
    {
        echo "</fieldset></form>\n";
    }

    /* Funktion zur Darstellung der letzten Buchungen objektunabhängig aus dem Vormonat und dem aktuellen Monat */

    function mieter_informationen_anzeigen($mietvertrag_id)
    {
        $einheit_info = new mietvertrag ();
        $einheit_id = $einheit_info->get_einheit_id_von_mietvertrag($mietvertrag_id);
        $einheit_info = new einheit ();
        $einheit_info->get_einheit_info($einheit_id);
        $person_info = new person ();
        $mietvertrag_info = new mietvertrag ();
        $personen_ids_arr = $mietvertrag_info->get_personen_ids_mietvertrag($mietvertrag_id);
        echo "<p class=\"hinweis\">Mieter: ";

        for ($i = 0; $i < count($personen_ids_arr); $i++) {
            $person_info->get_person_infos($personen_ids_arr [$i] ['PERSON_MIETVERTRAG_PERSON_ID']);
            $vorname = $person_info->person_vorname;
            $nachname = $person_info->person_nachname;
            echo "$nachname $vorname ";
        }
        echo "<a href='" . route('web::mietkontenblatt::legacy', ['anzeigen' => 'mietkonto_uebersicht_detailiert', 'mietvertrag_id' => $mietvertrag_id]) . "'>Mietkontenblatt für $einheit_info->einheit_kurzname</a>";
        echo "</p>";
        $einheit_kurzname = $einheit_info->einheit_kurzname;
        $this->einheit_kurzname_von_mv = $einheit_kurzname;
        $objekt_id = $einheit_info->objekt_id;
        session()->put('objekt_id', $objekt_id);
        $objekt_info = new objekt ();
        $objekt_info->get_objekt_geldkonto_nr($objekt_id);

        $mietkonto = new mietkonto ();
        $saldo_mietkonto = $mietkonto->mietkontostand_anzeigen($mietvertrag_id);

        $zeitraum = new zeitraum ();
        $saldo_status = $zeitraum->check_number($saldo_mietkonto);
        if ($saldo_status == 1) {
            $saldo_mietkonto = $mietkonto->nummer_punkt2komma($saldo_mietkonto);
            echo "<p class=\"negativ\">Saldo Mietkonto: $saldo_mietkonto €</p>";
        } else {
            $saldo_mietkonto = $mietkonto->nummer_punkt2komma($saldo_mietkonto);
            echo "<p class=\"positiv\">Saldo Mietkonto: $saldo_mietkonto €</p>";
        }
    }

    /* Funktion zur Berechnung der Summen der gebuchten Zahlbeträge alle Kontoauszüge aus dem aktuellen Monat */

    function mietkontostand_anzeigen($mietvertrag_id)
    {
        $a = new miete ();
        $a->mietkonto_berechnung(request()->input('mietvertrag_id'));
        return $a->erg;
    }

    function nummer_punkt2komma($nummer)
    {
        $nummer_arr = explode(".", $nummer);
        if (!isset ($nummer_arr [1])) {
            $nummer = "" . $nummer_arr [0] . ",00";
        } else {
            $nummer = "" . $nummer_arr [0] . "," . $nummer_arr [1] . "";
        }
        return $nummer;
    }

    function mieter_infos_vom_mv($mietvertrag_id)
    {
        $einheit_info = new mietvertrag ();
        $einheit_id = $einheit_info->get_einheit_id_von_mietvertrag($mietvertrag_id);
        $einheit_info = new einheit ();
        $einheit_info->get_einheit_info($einheit_id);
        $person_info = new person ();
        $mietvertrag_info = new mietvertrag ();
        $personen_ids_arr = $mietvertrag_info->get_personen_ids_mietvertrag($mietvertrag_id);
        // print_r($personen_ids_arr);
        echo "<p>Objekt: $einheit_info->objekt_name -> <b>Einheit: $einheit_info->einheit_kurzname</b><br>Mieter im Mietvertrag:\n<br> ";

        for ($i = 0; $i < count($personen_ids_arr); $i++) {
            $person_info->get_person_infos($personen_ids_arr [$i] ['PERSON_MIETVERTRAG_PERSON_ID']);
            $vorname = $person_info->person_vorname;
            $nachname = $person_info->person_nachname;
            echo "<b>$nachname $vorname</b>\n<br> ";
        }
        echo "</p>";
    }

    function buchungsauswahl($mietvertrag_id)
    {
        $this->letzte_buchungen_zu_mietvertrag($mietvertrag_id);
        $mv = new mietvertraege ();
        $mv->get_mietvertrag_infos_aktuell($mietvertrag_id);
        if ($mv->mietvertrag_bis != '0000-00-00') {
            echo "<p class=\"warnung\">Auzug zum $mv->mietvertrag_bis_d";
        }
        $einheit_info = new mietvertrag ();
        $summe_forderung_monatlich = $this->summe_forderung_monatlich($mietvertrag_id, $this->monat_heute, $this->jahr_heute);
        if ($summe_forderung_monatlich == 0) {
            $summe_forderung_monatlich = $this->summe_forderung_aus_vertrag($mietvertrag_id);
        }
        $summe_forderung_monatlich_arr = explode('|', $summe_forderung_monatlich);
        $summe_forderung_monatlich = $summe_forderung_monatlich_arr [0];
        $summe_forderung_mwst = $summe_forderung_monatlich_arr [1];
        /* Buchungsmaske für die automatische Buchung */
        $this->erstelle_formular("Buchungsvorschlag für die Einheit: <b>" . $einheit_info->einheit_kurzname . " </b>", NULL);
        // dropdown geldkonten
        $geldkonto_info = new geldkonto_info ();
        $geldkonto_info->geld_konten_ermitteln('Mietvertrag', $mietvertrag_id);

        // ##########kommentar im infofeld oben
        echo "<div class=\"info_feld_oben\"><b>Buchungsoptionen:<br></b>Buchungsvorschlag 1 für die Einheit: $einheit_info->einheit_kurzname. <br>Das Program errrechet den monatlichen Gesamtbetrag anhand der Mietentwicklung und diesen kann man als solchen buchen. <hr>Sie können aber auch einen anderen Betrag für die Einheit: $einheit_info->einheit_kurzname buchen, in dem Sie den Betrag eingeben und auf <b>Diesen Betrag buchen</b> klicken.</div>";

        if (!session()->has('buchungsdatum')) {
            $tag = date("d");
            $monat = date("m");
            $jahr = date("Y");
            session()->put('buchungsdatum', "$tag.$monat.$jahr");
        }
        $this->text_feld("Kontoauszugsnr.:", "kontoauszugsnr", session()->get('temp_kontoauszugsnummer'), "10");
        echo "<br>";
        $this->text_feld("Buchungsdatum:", "buchungsdatum", session()->get('buchungsdatum'), "10");
        echo "<br>";
        $summe_forderung_monatlich1 = $this->nummer_punkt2komma($summe_forderung_monatlich);
        $this->text_feld_inaktiv("Zahlbetrag (davon MWST:$summe_forderung_mwst)", "zahlbetrag", "$summe_forderung_monatlich1", "6");
        echo "<br>";
        $this->hidden_feld("ZAHLBETRAG", "$summe_forderung_monatlich");
        $this->hidden_feld("MWST_ANTEIL", "$summe_forderung_mwst");
        $this->hidden_feld("MIETVERTRAG_ID", "$mietvertrag_id");
        // ####aufteilung als array senden
        $forderung_arr = $this->aktuelle_forderungen_array($mietvertrag_id);
        if (empty($forderung_arr)) {
            $forderung_arr = $this->forderung_aus_vertrag($mietvertrag_id);
        }
        for ($i = 0; $i < count($forderung_arr); $i++) {
            // $this->text_feld_inaktiv("".$forderung_arr[$i]['KOSTENKATEGORIE']." (€)", "".$forderung_arr[$i]['KOSTENKATEGORIE']."", "".$forderung_arr[$i]['BETRAG']."", "5");
            $this->hidden_feld("AUFTEILUNG[" . $forderung_arr [$i] ['KOSTENKATEGORIE'] . "]", "" . $forderung_arr [$i] ['BETRAG'] . "");
        }
        echo "<p>";
        $this->text_bereich("Bemerkung / Hinweis", "bemerkung", "Zahlbetrag", "50", "3");
        echo "</p>";
        /* Schritt bezeichnet den Aufruf des Cases nach Betätigung des Sendbuttons */
        $this->hidden_feld("schritt", "auto_buchung");
        $this->send_button("submit_buchen1", "Akzeptieren und Buchen");
        $this->ende_formular();
        /* ENDE - Buchungsmaske für die automatische Buchung */

        echo "<br>";

        /* Buchungsmaske für andere Beträge */
        $this->erstelle_formular("Anderen Betrag für die Einheit: <b>" . $einheit_info->einheit_kurzname . "</b> buchen", NULL);
        // dropdown geldkonten
        $geldkonto_info = new geldkonto_info ();
        $geldkonto_info->geld_konten_ermitteln('Mietvertrag', $mietvertrag_id);
        $this->text_feld("Buchungsdatum:", "buchungsdatum", session()->get('buchungsdatum'), "10");
        echo "<br>";
        $this->hidden_feld("MIETVERTRAG_ID", "$mietvertrag_id");
        $this->text_feld("Anderer Zahlbetrag (€):", "ZAHLBETRAG", "$summe_forderung_monatlich1", "6");
        echo "<br>";
        $this->hidden_feld("schritt", "manuelle_buchung");
        $this->send_button("submit_buchen2", "Anderen Betrag buchen");
        $this->ende_formular();
        /* ENDE - Buchungsmaske für andere Beträge */
    }

    /* Funktion zur Darstellung der Grundinformationen zum Mietvertrag wie z.B. Mieternamen, Saldo, Einheit_kurzname ... */

    function letzte_buchungen_zu_mietvertrag($mietvertrag_id)
    {
        if (session()->has('buchungsanzahl')) {
            $buchungsanzahl = session()->get('buchungsanzahl');
            $my_array = DB::select("SELECT * FROM `GELD_KONTO_BUCHUNGEN` WHERE AKTUELL='1' && KOSTENTRAEGER_ID='$mietvertrag_id' && KOSTENTRAEGER_TYP='Mietvertrag' ORDER BY DATUM DESC LIMIT 0,$buchungsanzahl");
        } else {
            $my_array = DB::select("SELECT * FROM `GELD_KONTO_BUCHUNGEN` WHERE AKTUELL='1' && KOSTENTRAEGER_ID='$mietvertrag_id' && KOSTENTRAEGER_TYP='Mietvertrag' ORDER BY GELD_KONTO_BUCHUNGEN_ID DESC");
        }
        echo "<div class=\"tabelle\">";
        if (!empty($result)) {
            echo "<p class=\"letzte_buchungen_ueberschrift\">LETZTE BUCHUNGEN</p>";
            echo "<table>";
            echo "<tr class=\"feldernamen\"><td>BNR</td><td>AUSZUG</td><td>DATUM</td><td>EINHEIT</td><td>BETRAG</td><td>BEMERKUNG</td><td>Optionen</td></tr>\n";
            $numrows = count($my_array);
            for ($i = 0; $i < $numrows; $i++) {
                $datum = date_mysql2german($my_array [$i] ['DATUM']);
                $einheit_info = new mietvertrag ();
                $einheit_id = $einheit_info->get_einheit_id_von_mietvertrag($mietvertrag_id);
                $einheit_info = new einheit ();
                $einheit_info->get_einheit_info($einheit_id);
                $einheit_kurzname = $einheit_info->einheit_kurzname;
                $buchungsnummer = $my_array [$i] ['BUCHUNGSNUMMER'];
                $bemerkung = $my_array [$i] ['BEMERKUNG'];
                $buchungsdatum = $my_array [$i] ['DATUM'];
                $buchungsmonat_arr = explode('-', $buchungsdatum);
                $buchungsmonat = $buchungsmonat_arr [1];
                $kontoauszugsnr = $my_array [$i] ['KONTOAUSZUGSNR'];
                /* Prüfen ob diesen Monat gebucht wurde */
                $anzahl_zahlbetraege_diesen_monat = $this->anzahl_zahlungsvorgaenge($my_array [$i] ['mietvertrag_id']);
                if ($anzahl_zahlbetraege_diesen_monat) {
                    if ($this->monat_heute > $buchungsmonat) {
                        $storno_link = '';
                    } else {
                        $storno_link = "<a href='" . route('web::miete_buchen::legacy', ['schritt' => 'stornieren', 'bnr' => $buchungsnummer]) . "'>Stornieren</a>";
                    }
                } else {
                    $storno_link = "<a href='" . route('web::miete_buchen::legacy', ['schritt' => 'stornieren', 'bnr' => $buchungsnummer]) . "'>Stornieren</a>";
                }
                echo "<tr class=\"zeile1\"><td>$buchungsnummer</td><td>$kontoauszugsnr</td><td>$datum</td><td>$einheit_kurzname</td><td>" . $my_array [$i] ['BETRAG'] . " €</td><td>$bemerkung</td><td>$storno_link</td></tr>\n";
            }
            echo "</table>";
        } else {
            echo "Keine Buchungen";
        }
        echo "</div>";
    }

    /* Funktion (KURZFORM) zur Darstellung der Grundinformationen zum Mietvertrag wie z.B. Mieternamen, Saldo, Einheit_kurzname ... */

    function anzahl_zahlungsvorgaenge($mietvertrag_id)
    {
        $result = DB::select("SELECT DATUM FROM GELD_KONTO_BUCHUNGEN WHERE KOSTENTRAEGER_TYP='Mietvertrag' && KOSTENTRAEGER_ID='$mietvertrag_id' && AKTUELL='1' && DATE_FORMAT( DATUM, '%Y-%m' ) = '" . $this->jahr_heute . "-" . $this->monat_heute . "'");
        return count($result);
    }

    /* Funktion mit der ersten Buchungsmaske, d.h. Automatisches Buchen oder manuelle Buchung */

    function summe_forderung_monatlich($mietvertrag_id, $monat, $jahr)
    {
        //TODO: change return value
        $laenge = strlen($monat);
        if ($laenge == 1) {
            $monat = '0' . $monat;
        }
        $result = DB::select("SELECT SUM(BETRAG) AS SUMME_FORDERUNG, SUM(MWST_ANTEIL) AS MWST_ANTEIL FROM MIETENTWICKLUNG WHERE KOSTENTRAEGER_TYP='MIETVERTRAG' && KOSTENTRAEGER_ID = '$mietvertrag_id' && MIETENTWICKLUNG_AKTUELL = '1' && ( ENDE = '0000-00-00' OR DATE_FORMAT( ENDE, '%Y-%m' ) >= '$jahr-$monat') && DATE_FORMAT( ANFANG, '%Y-%m' ) <= '$jahr-$monat'  && KOSTENKATEGORIE NOT LIKE '%rate%' && KOSTENKATEGORIE NOT LIKE '%abrechnung%' && KOSTENKATEGORIE NOT LIKE '%mahngebühr%' && KOSTENKATEGORIE NOT LIKE 'Saldo Vortrag Vorverwaltung' && KOSTENKATEGORIE NOT LIKE '%energie%' ORDER BY ANFANG ASC");
        if (empty($result)) {
            return '0.00';
        } else {
            $row = $result[0];
            if ($row ['SUMME_FORDERUNG'] != null) {
                return $row ['SUMME_FORDERUNG'] . '|' . $row ['MWST_ANTEIL'];
            } else {
                return 0.00;
            }
        }
    }

    function summe_forderung_aus_vertrag($mietvertrag_id)
    {

        $result = DB::select("SELECT SUM(BETRAG) AS SUMME_FORDERUNG, SUM(MWST_ANTEIL) AS MWST_ANTEIL FROM MIETENTWICKLUNG WHERE KOSTENTRAEGER_TYP='MIETVERTRAG' && KOSTENTRAEGER_ID = '$mietvertrag_id' && MIETENTWICKLUNG_AKTUELL = '1'  && KOSTENKATEGORIE NOT LIKE '%rate%' && KOSTENKATEGORIE NOT LIKE '%abrechnung%' &&  KOSTENKATEGORIE NOT LIKE '%mahngebühr%' && KOSTENKATEGORIE NOT LIKE 'Saldo Vortrag Vorverwaltung'");
        if (empty($result)) {
            return false;
        } else {
            $row = $result[0];
            $summe = $row ['SUMME_FORDERUNG'];
            $summe = number_format($summe, 2, ".", "");
            $summe_mwst = number_format($summe, 2, ".", "");
            return "$summe|$summe_mwst";
        }
    }

    function text_feld($beschreibung, $name, $wert, $size)
    {
        echo "<div class='input-field'>";
        echo "<input type=\"text\" id=\"$name\" name=\"$name\" value=\"$wert\" size=\"$size\" onblur=\"javascript:activate(this.id)\" >\n";
        echo "<label for=\"$name\">$beschreibung</label>\n";
        echo "</div>";
    }

    function text_feld_inaktiv($beschreibung, $name, $wert, $size)
    {
        echo "<div class='input-field'>";
        echo "<input type=\"text\" id=\"$beschreibung.$name\" name=\"$beschreibung.$name\" value=\"$wert\" size=\"$size\" disabled>\n";
        echo "<label for=\"$beschreibung.$name\">$beschreibung</label>\n";
        echo "</div>";
    }

    function hidden_feld($name, $wert)
    {
        echo "<input type=\"hidden\" id=\"$name\" name=\"$name\" value=\"$wert\" >\n";
    }

    function aktuelle_forderungen_array($mietvertrag_id)
    {
        $this->datum_heute;
        $this->tag_heute;
        $this->monat_heute;
        $this->jahr_heute;
        $forderungen_diesen_monat_arr = $this->forderung_monatlich($mietvertrag_id, $this->monat_heute, $this->jahr_heute);
        return $forderungen_diesen_monat_arr;
    }

    function forderung_monatlich($mietvertrag_id, $monat, $jahr)
    {
        /* Neu ohne ratenzahlung */
        if (strlen($monat) < 2) {
            $monat = '0' . $monat;
        }
        $result = DB::select("SELECT KOSTENTRAEGER_ID, ANFANG, ENDE, KOSTENKATEGORIE, BETRAG FROM MIETENTWICKLUNG WHERE KOSTENTRAEGER_TYP='MIETVERTRAG' && KOSTENTRAEGER_ID = '$mietvertrag_id' && MIETENTWICKLUNG_AKTUELL = '1' && ( ENDE = '0000-00-00' OR DATE_FORMAT( ENDE, '%Y-%m' ) >= '$jahr-$monat') && DATE_FORMAT( ANFANG, '%Y-%m' ) <= '$jahr-$monat'  && KOSTENKATEGORIE NOT LIKE 'RATENZAHLUNG' ORDER BY ANFANG ASC");
        return $result;
    }

    function forderung_aus_vertrag($mietvertrag_id)
    {
        /* Neu ohne ratenzahlung */
        $result = DB::select("SELECT KOSTENTRAEGER_ID, ANFANG, ENDE, KOSTENKATEGORIE, BETRAG FROM MIETENTWICKLUNG WHERE KOSTENTRAEGER_ID = '$mietvertrag_id' && KOSTENTRAEGER_TYP='MIETVERTRAG' && MIETENTWICKLUNG_AKTUELL = '1'  && KOSTENKATEGORIE NOT LIKE '%rate%' && KOSTENKATEGORIE NOT LIKE '%abrechnung%' ORDER BY ANFANG ASC");
        return $result;
    }

    function text_bereich($beschreibung, $name, $wert, $cols, $rows)
    {
        ?>
        <div class="input-field col-xs-12">
            <textarea id="<?php echo $name ?>" class="materialize-textarea" name="<?php echo $name ?>"
                      cols="<?php echo $cols ?>" rows="<?php echo $rows ?>"><?php echo $wert ?></textarea>
            <label for="<?php echo $name ?>"><?php echo $beschreibung ?></label>
        </div>
        <?php
    }

    /* Letzte Buchungsnummer vom gebuchten Zahlbetrag zu Mietvertrag finden */

    function send_button($name, $wert)
    {
        echo "<button type=\"submit\" name=\"$name\" value=\"$wert\" class=\"btn waves-effect waves-light\" id=\"$name\"><i class=\"mdi mdi-send right\"></i>$wert</button>";
    }

    function buchungsmaske_manuell_gross_betrag($mietvertrag_id, $geld_konto_id)
    {
        $summe_forderung_monatlich = $this->summe_forderung_monatlich($mietvertrag_id, $this->monat_heute, $this->jahr_heute);
        if ($summe_forderung_monatlich == 0) {
            $summe_forderung_monatlich = $this->summe_forderung_aus_vertrag($mietvertrag_id);
        }
        /* Datumsformat prüfen, falls i.O wie folgt weiter */
        if (check_datum(request()->input('buchungsdatum'))) {
            /* Variante 1 - Anfang */
            $this->erstelle_formular("Anderen Betrag teilen und buchen ...", NULL);
            /* Ein Array mit aktuellen Forderungen für aktuellen Monat zusammenstellen */
            $forderung_arr = $this->aktuelle_forderungen_array($mietvertrag_id);
            if (empty($forderung_arr)) {
                $forderung_arr = $this->forderung_aus_vertrag($mietvertrag_id);
            }
            /* Zahlbetrag aus Komma in Punktformat wandeln */
            $zahlbetrag = $this->nummer_komma2punkt(request()->input('ZAHLBETRAG'));
            echo "<b>Automatische Aufteilung:</b><hr>";
            /* Zahlbetrag aus Punkt in Kommaformat wandeln */
            $zahlbetrag_komma = $this->nummer_punkt2komma($zahlbetrag);
            $this->hidden_feld("MIETVERTRAG_ID", "$mietvertrag_id");
            $this->hidden_feld("buchungsdatum", request()->input('buchungsdatum'));
            $this->text_feld("Kontoauszugsnr.:", "kontoauszugsnr", session()->get('temp_kontoauszugsnummer'), "10");
            echo "<br>";
            $this->text_feld_inaktiv("Zahlbetrag (€):", "ZAHLBETRAG", "$zahlbetrag_komma", "5");
            $this->hidden_feld("ZAHLBETRAG", "$zahlbetrag");
            echo "<table>";
            for ($i = 0; $i < count($forderung_arr); $i++) {
                $forderung_arr [$i] ['BETRAG'] = $this->nummer_punkt2komma($forderung_arr [$i] ['BETRAG']);
                echo "<tr><td><b>" . $forderung_arr [$i] ['KOSTENKATEGORIE'] . "</b></td><td align=\"right\"><b> " . $forderung_arr [$i] ['BETRAG'] . " €</b></td></tr>";
                /* Zahlbetrag aus Komma in Punkt format wandeln $betrag_4_db */
                $betrag_4_db = $this->nummer_komma2punkt($forderung_arr [$i] ['BETRAG']);
                /* Versteckten Array namens AUFTEILUNG ins Formular hinzufügen, wichtig für die interne Verbuchung nach Kostenkategorie */
                $this->hidden_feld("AUFTEILUNG[" . $forderung_arr [$i] ['KOSTENKATEGORIE'] . "]", "$betrag_4_db");
            }
            echo "</table>";
            $ueberschuss = $zahlbetrag - $summe_forderung_monatlich;
            echo "<hr>";
            $this->text_bereich("Bemerkung / Hinweis", "bemerkung", "Zahlbetrag", "10", "3");
            /* Auswahl der Kostenkategorie für den Überschuß bzw. Restbetrag */
            warnung_ausgeben("Wählen Sie bitte eine Kostenkategorie für die Überschußbuchung aus!");
            $ueberschuss = number_format($ueberschuss, 2, ".", "");
            $ueberschuss = $this->nummer_punkt2komma($ueberschuss);
            $this->text_feld_inaktiv("Überschuss (€):", "ueberschuss", "$ueberschuss", "5");
            $ueberschuss = $this->nummer_komma2punkt($ueberschuss);
            $this->hidden_feld("ueberschuss", "$ueberschuss");
            $this->hidden_feld("geld_konto", "$geld_konto_id");
            $this->dropdown_kostenkategorien('Kostenkategorie auswählen', 'kostenkategorie');

            /* Kommentar im info_feld_oben */
            echo "<div class=\"info_feld_oben\"><b>Buchungsoptionen:<br></b>Der von Ihnen eingegebene Betrag ist größer oder gleich als die monatliche Gesamtforderung. Treffen Sie bitte die Auswahl, wie der Betrag zu buchen ist.</div>";
            echo "<br>";
            /* Ende Kommentar */
            /* Schritt bezeichnet den Aufruf des Cases nach Betätigung des Sendbuttons */
            $this->hidden_feld("schritt", "manuelle_buchung3");
            $this->send_button("submit_buchen3", "Akzeptieren und buchen");
            $this->ende_formular();
            /* Ende Formular */
            echo "<br>";
            /* Hier endet die vorgeschlagene Buchungsart bzw. Variante 1 */

            /* Variante 2 - Anfang */
            /* Buchungsformular für die manuelle Eingabe der Beträge */
            $this->erstelle_formular("Betrag manuell teilen / buchen ...", NULL);
            echo "<b>Manuelle Teilung / Buchung</b><hr>";
            warnung_ausgeben("Tragen Sie bitte einzelne Beträge ein!");
            $this->text_feld("Kontoauszugsnr.:", "kontoauszugsnr", session()->get('temp_kontoauszugsnummer'), "10");
            echo "<br>";
            $this->text_feld_inaktiv("Zahlbetrag (€):", "ZAHLBETRAG", "$zahlbetrag_komma", "5");
            $this->hidden_feld("MIETVERTRAG_ID", "$mietvertrag_id");
            $this->hidden_feld("buchungsdatum", request()->input('buchungsdatum'));
            $this->hidden_feld("ZAHLBETRAG", "$zahlbetrag");
            echo "<br>";
            for ($i = 0; $i < count($forderung_arr); $i++) {
                $this->text_feld("" . $forderung_arr [$i] ['KOSTENKATEGORIE'] . " (" . $forderung_arr [$i] ['BETRAG'] . " €)", "AUFTEILUNG[" . $forderung_arr [$i] ['KOSTENKATEGORIE'] . "]", "", "5");
                echo "<br>";
            }
            echo "<p>";
            $this->text_bereich("Bemerkung / Hinweis", "bemerkung", "Zahlbetrag", "50", "3");
            echo "</p>";
            /* Schritt bezeichnet den Aufruf des Cases nach Betätigung des Sendbuttons */
            $this->hidden_feld("schritt", "manuelle_buchung4");
            $this->hidden_feld("geld_konto", $geld_konto_id);
            $this->send_button("submit_buchen4", "Manuel buchen");
            $this->ende_formular();
            /* ENDE -Buchungsformular für die manuelle Eingabe der Beträge */
        } else {
            /* Falls das Datumsformat nicht i.O,. um einen Schritt zurücksetzen */
            warnung_ausgeben("Datumsformat nicht korrekt!");
            warnung_ausgeben("Sie werden um einen Schritt zurückversetzt!");
            weiterleiten_in_sec('javascript:history.back();', 5);
        }
    }

    function nummer_komma2punkt($nummer)
    {
        $nummer_arr = explode(",", $nummer);
        if (!isset ($nummer_arr [1])) {
            $nummer = "" . $nummer_arr [0] . ".00";
        } else {
            $nummer = "" . $nummer_arr [0] . "." . $nummer_arr [1] . "";
        }
        return $nummer;
    }

    function dropdown_kostenkategorien($beschreibung, $name)
    {
        echo "<label for=\"$name\">$beschreibung</label><select name=\"$name\" id=\"$name\" \n";
        echo "<option value=\"Miete kalt\">Miete kalt</option>\n";
        echo "<option value=\"Heizkosten Vorauszahlung\">Heizkosten Vorauszahlung</option>\n";
        echo "<option value=\"Nebenkosten Vorauszahlung\">Nebenkosten Vorauszahlung</option>\n";
        echo "</select>";
    }

    function buchungsmaske_manuell_kleiner_betrag($mietvertrag_id, $geld_konto_id)
    {
        $summe_forderung_monatlich = $this->summe_forderung_monatlich($mietvertrag_id, $this->monat_heute, $this->jahr_heute);
        if ($summe_forderung_monatlich == 0) {
            $summe_forderung_monatlich = $this->summe_forderung_aus_vertrag($mietvertrag_id);
        }
        // ######anfang
        if (check_datum(request()->input('buchungsdatum'))) {
            $forderung_arr = $this->aktuelle_forderungen_array($mietvertrag_id);
            if (empty($forderung_arr)) {
                $forderung_arr = $this->forderung_aus_vertrag($mietvertrag_id);
            }
            $summe_vorschuesse = $this->summe_vorschuesse($forderung_arr);
            /* Keys sind fest definiert in suche_nach_vorschuessen als BETRIEBSKOSTEN HEIZKOSTEN */
            $vorschuesse_keys = $this->suche_nach_vorschuessen($forderung_arr);
            $summe_vorschuesse = number_format($summe_vorschuesse, 2, ".", "");
            $zahlbetrag = $this->nummer_komma2punkt(request()->input('ZAHLBETRAG'));
            $zahlbetrag_komma = $this->nummer_punkt2komma($zahlbetrag);
            // echo "ZB: $zahlbetrag F:$summe_forderung_monatlich SV:$summe_vorschuesse";

            /* Prüfen ob der Zahlbetrag die Vorschüsse decken kann */
            if ($zahlbetrag >= $summe_vorschuesse) {
                $rest = $zahlbetrag - $summe_vorschuesse;
                $rest_komma = number_format($rest, 2, ",", "");

                /* Formularanfang falls Zahlbetrag > Vorschüsse */
                $this->erstelle_formular("Kleineren Betrag teilen und buchen -> Berlussimo schlägt vor...", NULL);
                echo "<b>Der Zahlbetrag reicht für die Vorschüsse!</b> <br>\n Nach dem Buchen der Vorschüsse bleiben <b>$rest_komma €</b> für weitere Buchungen.";
                $this->text_feld("Kontoauszugsnr.:", "kontoauszugsnr", session()->get('temp_kontoauszugsnummer'), "10");
                $this->hidden_feld("geld_konto", "$geld_konto_id");
                $this->hidden_feld("buchungsdatum", request()->input('buchungsdatum'));
                $this->hidden_feld("ZAHLBETRAG", "$zahlbetrag");
                $this->hidden_feld("MIETVERTRAG_ID", "$mietvertrag_id");
                for ($i = 0; $i < count($vorschuesse_keys); $i++) {

                    /* Versteckten Array namens AUFTEILUNG ins Formular hinzufügen, wichtig für die interne Verbuchung nach Kostenkategorie */
                    $this->hidden_feld("AUFTEILUNG[" . $forderung_arr [$vorschuesse_keys [$i]] ['KOSTENKATEGORIE'] . "]", "" . $forderung_arr [$vorschuesse_keys [$i]] ['BETRAG'] . "");
                    /* Anzeige der inaktiven Felder der KOSTENKATEGORIEN VORSCHÜSSE */
                    $betrag_ausgabe = $this->nummer_punkt2komma($forderung_arr [$vorschuesse_keys [$i]] ['BETRAG']);
                    $this->text_feld_inaktiv("" . $forderung_arr [$vorschuesse_keys [$i]] ['KOSTENKATEGORIE'] . "", "" . $forderung_arr [$vorschuesse_keys [$i]] ['KOSTENKATEGORIE'] . "", "$betrag_ausgabe", "5");
                    echo "<br>";
                }

                if ($rest > 0) {
                    $rest_komma = number_format($rest, 2, ",", "");
                    $this->text_feld_inaktiv("KALTMIETE €", "KALTMIETE", "$rest_komma", "5");
                    $mk_bez = ' Miete kalt';
                    $this->hidden_feld("AUFTEILUNG[$mk_bez]", "$rest");
                }
                echo "<br>";
                echo "<p>";
                $this->text_bereich("Bemerkung / Hinweis", "bemerkung", "Zahlbetrag", "50", "3");
                echo "</p>";
                /* Schritt bezeichnet den Aufruf des Cases nach Betätigung des Sendbuttons */
                $this->hidden_feld("schritt", "manuelle_buchung3");
                $this->hidden_feld("geld_konto", $geld_konto_id);
                $this->send_button("submit_buchen5", "Akzeptieren und buchen");
                $this->ende_formular();
                echo "<hr>";
                // #############################

                /* Variante 2 - Anfang */
                /* Buchungsformular für die manuelle Eingabe der Beträge */
                $this->erstelle_formular("Kleineren Betrag manuell teilen / buchen ...", NULL);
                echo "<b>Manuelle Teilung / Buchung</b><hr>";
                warnung_ausgeben("Tragen Sie bitte einzelne Beträge ein!");
                $this->text_feld("Kontoauszugsnr.:", "kontoauszugsnr", session()->get('temp_kontoauszugsnummer'), "10");
                $this->text_feld_inaktiv("Zahlbetrag (€):", "ZAHLBETRAG", "$zahlbetrag_komma", "5");
                $this->hidden_feld("MIETVERTRAG_ID", "$mietvertrag_id");
                $this->hidden_feld("buchungsdatum", request()->input('buchungsdatum'));
                $this->hidden_feld("ZAHLBETRAG", "$zahlbetrag");
                echo "<br>";
                for ($i = 0; $i < count($forderung_arr); $i++) {
                    $this->text_feld("" . $forderung_arr [$i] ['KOSTENKATEGORIE'] . " ( " . $forderung_arr [$i] ['BETRAG'] . " €)", "AUFTEILUNG[" . $forderung_arr [$i] ['KOSTENKATEGORIE'] . "]", "", "5");
                    echo "<br>";
                }
                echo "<p>";
                $this->text_bereich("Bemerkung / Hinweis", "bemerkung", "Zahlbetrag", "50", "3");
                echo "</p>";
                /* Schritt bezeichnet den Aufruf des Cases nach Betätigung des Sendbuttons */
                $this->hidden_feld("schritt", "manuelle_buchung4");
                $this->hidden_feld("geld_konto", $geld_konto_id);
                $this->send_button("submit_buchen4", "Manuel buchen");
                $this->ende_formular();
                /* ENDE -Buchungsformular für die manuelle Eingabe der Beträge */
            }
            // ################### ab hier FALL 2 #############################
            /* Falls Zahlbetrag kleiner als Vorschüsse */
            if ($zahlbetrag < $summe_vorschuesse) {
                echo "Der Zahlbetrag reicht nicht für die Vorschüsse! Prozentuale aufteilung.";

                /* Formularanfang wenn Zahlbetrag kleiner als Vorschüsse */
                $this->erstelle_formular("Kleineren Betrag prozentual teilen / buchen", NULL);
                $this->hidden_feld("ZAHLBETRAG", "$zahlbetrag");
                $this->hidden_feld("MIETVERTRAG_ID", "$mietvertrag_id");
                $this->hidden_feld("buchungsdatum", request()->input('buchungsdatum'));
                echo "Buchungsdatum: " . request()->input('buchungsdatum') . "<br>";
                $rest = $zahlbetrag - $summe_vorschuesse;
                $prozentsatz = $zahlbetrag / ($summe_vorschuesse / 100);
                $prozentsatz_gerundet = number_format($prozentsatz, 0, ",", "");
                echo "Der Zahlbetrag reicht <b>nicht</b> für die Vorschüsse! Es fehlen $rest €! Eine Deckung der Vorschüsse mit maximal $prozentsatz_gerundet % möglich<br>";
                echo "<hr><b>Buchungsvorschlag wenn Betrag kleiner als Nebenkosten:</b><br><br>";
                $this->text_feld("Kontoauszugsnr.:", "kontoauszugsnr", session()->get('temp_kontoauszugsnummer'), "10");
                $this->text_feld_inaktiv("Zahlbetrag:", "ZAHLBETRAG", request()->input('ZAHLBETRAG') . " €", "5");
                echo "<br>";
                $vorschuesse_keys = $this->suche_nach_vorschuessen($forderung_arr);
                $rundungsfehler = 0;
                for ($i = 0; $i < count($vorschuesse_keys); $i++) {
                    $prozentsatz = $zahlbetrag / ($summe_vorschuesse / 100);
                    $prozentualer_anteil = (($forderung_arr [$vorschuesse_keys [$i]] ['BETRAG'] / 100) * $prozentsatz);
                    $prozentualer_anteil = (($forderung_arr [$vorschuesse_keys [$i]] ['BETRAG'] / 100) * $prozentsatz) + $rundungsfehler;
                    $prozentualer_anteil = round($prozentualer_anteil, 2);
                    $rundungsfehler = (($forderung_arr [$vorschuesse_keys [$i]] ['BETRAG'] / 100) * $prozentsatz) - $prozentualer_anteil;
                    // echo " PRO nach rundung: $prozentualer_anteil €";
                    $prozentualer_anteil_gerundet = number_format($prozentualer_anteil, 2, ",", "");
                    $this->text_feld_inaktiv("" . $forderung_arr [$vorschuesse_keys [$i]] ['KOSTENKATEGORIE'] . "", "" . $forderung_arr [$vorschuesse_keys [$i]] ['KOSTENKATEGORIE'] . "", "" . $prozentualer_anteil_gerundet . " €", "15");

                    /* Versteckten Array namens AUFTEILUNG ins Formular hinzufügen, wichtig für die interne Verbuchung nach Kostenkategorie */

                    $this->hidden_feld("AUFTEILUNG[" . $forderung_arr [$vorschuesse_keys [$i]] ['KOSTENKATEGORIE'] . "]", "" . $prozentualer_anteil_gerundet . "");
                    echo "<br>";
                } // ende for
                echo "<p>";
                $this->text_bereich("Bemerkung / Hinweis", "bemerkung", "Zahlbetrag", "50", "3");
                echo "</p>";
                $this->hidden_feld("schritt", "manuelle_buchung3");
                $this->hidden_feld("geld_konto", "$geld_konto_id");
                $this->send_button("submit_buchen6", "Manuel buchen");
                $this->ende_formular();

                echo "<hr>";
                /* Ende Buchungsvorschlag */
                /* Anfang Manuelle Aufteilung */
                /* Buchungsformular für die manuelle Eingabe der Beträge */
                $this->erstelle_formular("Betrag manuell teilen / buchen ...", NULL);
                echo "<b>Manuelle Teilung / Buchung</b><hr>";
                warnung_ausgeben("Tragen Sie bitte einzelne Beträge ein!");
                $this->text_feld("Kontoauszugsnr.:", "kontoauszugsnr", session()->get('temp_kontoauszugsnummer'), "10");
                $this->text_feld_inaktiv("Zahlbetrag (€):", "ZAHLBETRAG", "$zahlbetrag_komma", "5");
                $this->hidden_feld("MIETVERTRAG_ID", "$mietvertrag_id");
                $this->hidden_feld("buchungsdatum", request()->input('buchungsdatum'));
                $this->hidden_feld("ZAHLBETRAG", "$zahlbetrag");
                echo "<br>";
                for ($i = 0; $i < count($forderung_arr); $i++) {
                    $this->text_feld("" . $forderung_arr [$i] ['KOSTENKATEGORIE'] . " (€)", "AUFTEILUNG[" . $forderung_arr [$i] ['KOSTENKATEGORIE'] . "]", "", "5");
                    echo "<br>";
                }
                echo "<p>";
                $this->text_bereich("Bemerkung / Hinweis", "bemerkung", "Zahlbetrag", "50", "3");
                echo "</p>";
                /* Schritt bezeichnet den Aufruf des Cases nach Betätigung des Sendbuttons */
                $this->hidden_feld("schritt", "manuelle_buchung4");
                $this->hidden_feld("geld_konto", "$geld_konto_id");
                $this->send_button("submit_buchen4", "Manuel buchen");
                $this->ende_formular();
                /* ENDE -Buchungsformular für die manuelle Eingabe der Beträge */
                /* Ende manuelle Aufteilung */
                // ################## ende FALL 2 #################################
            }
        } else {
            /* Falls das Datumsformat nicht i.O,. um einen Schritt zurücksetzen */
            warnung_ausgeben("Datumsformat nicht korrekt!");
            warnung_ausgeben("Sie werden um einen Schritt zurückversetzt!");
            weiterleiten_in_sec('javascript:history.back();', 5);
        }
    }

    function summe_vorschuesse($forderung_arr)
    {
        $vorschuesse_arr = $this->suche_nach_vorschuessen($forderung_arr);
        // $this->array_anzeigen($vorschuesse_arr);
        $summe_hk_bk = 0;
        for ($a = 0; $a < count($vorschuesse_arr); $a++) {
            // Durchlauf von Array mit Betraegen und Summierung
            $summe_hk_bk += $forderung_arr [$vorschuesse_arr [$a]] ['BETRAG'];
        }
        if (isset ($summe_hk_bk)) {
            return $summe_hk_bk;
        }
    }

    function suche_nach_vorschuessen($array)
    {
        for ($i = 0; $i < count($array); $i++) {
            if (array_search('Nebenkosten Vorauszahlung', $array [$i])) {
                $vorschuesse [] = $i;
            }
            if (array_search('Heizkosten Vorauszahlung', $array [$i])) {
                $vorschuesse [] = $i;
            }
        }
        if (isset ($vorschuesse)) {
            return $vorschuesse;
        }
    }

    // ########### FORDERUNGEN AUS MIETENTWICKLUNG

    function buchungsmaske_manuell_gleicher_betrag($mietvertrag_id, $geld_konto_id)
    {
        $summe_forderung_monatlich = $this->summe_forderung_monatlich($mietvertrag_id, $this->monat_heute, $this->jahr_heute);
        /* Datumsformat prüfen, falls i.O wie folgt weiter */
        if (check_datum(request()->input('buchungsdatum'))) {
            /* Ein Array mit aktuellen Forderungen für aktuellen Monat zusammenstellen */
            $forderung_arr = $this->aktuelle_forderungen_array($mietvertrag_id);
            if (empty($forderung_arr)) {
                $forderung_arr = $this->forderung_aus_vertrag($mietvertrag_id);
            }
            /* Zahlbetrag aus Komma in Punktformat wandeln */
            $zahlbetrag = $this->nummer_komma2punkt(request()->input('ZAHLBETRAG'));
            /* Zahlbetrag aus Punkt in Kommaformat wandeln */
            $zahlbetrag_komma = $this->nummer_punkt2komma($zahlbetrag);

            /* Buchungsformular für die manuelle Eingabe der Beträge */
            $this->erstelle_formular("Betrag manuell teilen / buchen ...", NULL);
            echo "<b>Manuelle Teilung / Buchung</b><hr>";
            warnung_ausgeben("Tragen Sie bitte einzelne Beträge ein!");
            $this->text_feld("Kontoauszugsnr.:", "kontoauszugsnr", session()->get('temp_kontoauszugsnummer'), "10");
            echo "<br>";
            $this->text_feld_inaktiv("Zahlbetrag (€):", "ZAHLBETRAG", "$zahlbetrag_komma", "5");
            $this->hidden_feld("MIETVERTRAG_ID", "$mietvertrag_id");
            $this->hidden_feld("buchungsdatum", request()->input('buchungsdatum'));
            $this->hidden_feld("ZAHLBETRAG", "$zahlbetrag");
            echo "<br>";
            for ($i = 0; $i < count($forderung_arr); $i++) {
                $f_betrag = nummer_punkt2komma($forderung_arr [$i] ['BETRAG']);
                $this->text_feld("" . $forderung_arr [$i] ['KOSTENKATEGORIE'] . " ( " . $forderung_arr [$i] ['BETRAG'] . " €)", "AUFTEILUNG[" . $forderung_arr [$i] ['KOSTENKATEGORIE'] . "]", "$f_betrag", "5");
                echo "<br>";
            }
            echo "<p>";
            $this->text_bereich("Bemerkung / Hinweis", "bemerkung", "Zahlbetrag", "50", "3");
            echo "</p>";
            /* Schritt bezeichnet den Aufruf des Cases nach Betätigung des Sendbuttons */
            $this->hidden_feld("geld_konto", "$geld_konto_id");
            $this->hidden_feld("schritt", "manuelle_buchung4");
            $this->send_button("submit_buchen4", "Manuel buchen");
            $this->ende_formular();
            /* ENDE -Buchungsformular für die manuelle Eingabe der Beträge */
        } else {
            /* Falls das Datumsformat nicht i.O,. um einen Schritt zurücksetzen */
            warnung_ausgeben("Datumsformat nicht korrekt!");
            warnung_ausgeben("Sie werden um einen Schritt zurückversetzt!");
            weiterleiten_in_sec('javascript:history.back();', 5);
        }
    }

    function buchungsmaske_manuell_negativ_betrag($mietvertrag_id, $geld_konto_id)
    {
        $summe_forderung_monatlich = $this->summe_forderung_monatlich($mietvertrag_id, $this->monat_heute, $this->jahr_heute);
        /* Datumsformat prüfen, falls i.O wie folgt weiter */
        if (check_datum(request()->input('buchungsdatum'))) {
            /* Ein Array mit aktuellen Forderungen für aktuellen Monat zusammenstellen */
            $forderung_arr = $this->aktuelle_forderungen_array($mietvertrag_id);
            /* Zahlbetrag aus Komma in Punktformat wandeln */
            $zahlbetrag = $this->nummer_komma2punkt(request()->input('ZAHLBETRAG'));
            /* Zahlbetrag aus Punkt in Kommaformat wandeln */
            $zahlbetrag_komma = $this->nummer_punkt2komma($zahlbetrag);

            /* Buchungsformular für die manuelle Eingabe der NEGATIVEN Beträge */
            $this->erstelle_formular("Negativen Betrag manuell teilen / buchen ...", NULL);
            echo "<b>Manuelle Teilung / Buchung</b><hr>";
            // warnung_ausgeben("Tragen Sie bitte einzelne Beträge ein!");
            $this->text_feld("Kontoauszugsnr.:", "kontoauszugsnr", session()->get('temp_kontoauszugsnummer'), "10");
            echo "<br>";
            $this->text_feld_inaktiv("Zahlbetrag (€):", "ZAHLBETRAG", "$zahlbetrag_komma", "10");
            $this->hidden_feld("MIETVERTRAG_ID", "$mietvertrag_id");
            $this->hidden_feld("buchungsdatum", request()->input('buchungsdatum'));
            $this->hidden_feld("ZAHLBETRAG", "$zahlbetrag");
            echo "<br>";
            echo "<p>";
            $this->text_bereich("Bemerkung / Hinweis", "bemerkung", "Zahlbetrag", "50", "3");
            echo "</p>";
            /* Schritt bezeichnet den Aufruf des Cases nach Betätigung des Sendbuttons */
            $this->hidden_feld("geld_konto", "$geld_konto_id");
            $this->hidden_feld("schritt", "manuelle_buchung4");
            $this->send_button("submit_buchen4", "Manuel buchen");
            $this->ende_formular();
            /* ENDE -Buchungsformular für die manuelle Eingabe der Beträge */
        } else {
            /* Falls das Datumsformat nicht i.O,. um einen Schritt zurücksetzen */
            warnung_ausgeben("Datumsformat nicht korrekt!");
            warnung_ausgeben("Sie werden um einen Schritt zurückversetzt!");
            weiterleiten_in_sec('javascript:history.back();', 5);
        }
    }

    function miete_zahlbetrag_buchen($kontoauszugsnr, $mietvertrag_id, $buchungsdatum, $betrag, $bemerkung, $geld_konto_id, $mwst_anteil = '0.00')
    {
        /* Datum und Kontoauszug in Session übernehmen */
        $sess_datum = $this->date_mysql2german($buchungsdatum);
        session()->put('buchungsdatum', $sess_datum);
        session()->put('temp_kontoauszugsnummer', $kontoauszugsnr);
        /* Buchen und protokollieren */

        $this->insert_geldbuchung($geld_konto_id, '80001', $kontoauszugsnr, 'MIETE', $bemerkung, $buchungsdatum, 'Mietvertrag', $mietvertrag_id, $betrag, $mwst_anteil);

        /* Interne Buchung */
        // $buchungsnummer = $last_dat;
        // $this->intern_buchen($mietvertrag_id, $buchungsnummer);

        /* Ausgabe am Bildschirm */
        $betrag = $this->nummer_punkt2komma($betrag);
        echo "<p><b>Zahlbetrag $betrag € wurde auf das Konto $geld_konto_id gebucht.<br></b></p>";

        weiterleiten_in_sec(route('web::miete_buchen::legacy', [], false), 2);
    }

    function date_mysql2german($date)
    {
        $d = explode("-", $date);
        return sprintf("%02d.%02d.%04d", $d [2], $d [1], $d [0]);
    }

    function insert_geldbuchung($geldkonto_id, $buchungskonto, $auszugsnr, $rechnungsnr, $v_zweck, $datum, $kostentraeger_typ, $kostentraeger_id, $betrag, $mwst_anteil = '0.00')
    {
        $last_id = last_id('GELD_KONTO_BUCHUNGEN');
        $last_id = $last_id + 1;

        /* neu */
        $datum_arr = explode('-', $datum);
        $jahr = $datum_arr ['0'];
        $b = new buchen ();
        $g_buchungsnummer = $b->get_last_buchungsnummer_konto($geldkonto_id, $jahr);
        $g_buchungsnummer = $g_buchungsnummer + 1;

        DB::insert("INSERT INTO GELD_KONTO_BUCHUNGEN VALUES(NULL, '$last_id', '$g_buchungsnummer', '$auszugsnr', '$rechnungsnr', '$betrag', '$mwst_anteil', '$v_zweck', '$geldkonto_id', '$buchungskonto', '$datum', '$kostentraeger_typ', '$kostentraeger_id', '1')");
        /* Protokollieren */
        $last_dat = DB::getPdo()->lastInsertId();
        protokollieren('GELD_KONTO_BUCHUNGEN', $last_dat, '0');
    }

    function import_miete_zahlbetrag_buchen($kontoauszugsnr, $kostentraeger_typ, $kostentraeger_id, $buchungsdatum, $betrag, $bemerkung, $geldkonto_id, $buchungskonto)
    {
        $buchungsdatum = date_german2mysql($buchungsdatum);
        $this->insert_geldbuchung($geldkonto_id, $buchungskonto, $kontoauszugsnr, $kontoauszugsnr, $bemerkung, $buchungsdatum, $kostentraeger_typ, $kostentraeger_id, $betrag);
    }

function check_zahlbetrag($kontoauszugsnr, $kostentraeger_typ, $kostentraeger_id, $buchungsdatum, $betrag, $v_zweck, $geld_konto_id, $kontenrahmen_konto)
    {
        $result = DB::select("SELECT * FROM GELD_KONTO_BUCHUNGEN WHERE KONTO_AUSZUGSNUMMER = '$kontoauszugsnr' && KOSTENTRAEGER_TYP='$kostentraeger_typ' && KOSTENTRAEGER_ID='$kostentraeger_id' && DATUM= '$buchungsdatum' && BETRAG= '$betrag' && VERWENDUNGSZWECK= '$v_zweck' && AKTUELL= '1' && GELDKONTO_ID= '$geld_konto_id' && KONTENRAHMEN_KONTO='$kontenrahmen_konto'");
        return !empty($result);
    }

    // Funktion zur Erstellung eines Arrays mit Monaten und Jahren seit Einzug bis aktuelles Jahr/Monat

        function monatsabschluesse_speichern($mietvertrag_id, $betrag)
    {
        $datum = $this->datum_heute;
        $db_abfrage = "INSERT INTO MONATSABSCHLUSS VALUES (NULL, '$mietvertrag_id', '$datum', '$betrag', '1', NULL)";
        $resultat = DB::insert($db_abfrage);
        if (!$resultat) {
            echo "Monatsabschluss von $betrag für MV $mietvertrag_id wurde nicht gespeichert!";
        }
    } // end function

    function mietentwicklung_speichern($kostentraeger_typ, $kostentrager_id, $kostenkategorie, $betrag, $anfang, $ende)
    {
        $me_exists = $this->check_mietentwicklung($kostentraeger_typ, $kostentrager_id, $kostenkategorie, $betrag, $anfang, $ende);
        if (!$me_exists) {
            $last_id = $this->get_mietentwicklung_last_id();
            $last_id = $last_id + 1;
            $db_abfrage = "INSERT INTO MIETENTWICKLUNG VALUES (NULL, '$last_id', '$kostentraeger_typ', '$kostentrager_id', '$kostenkategorie', '$anfang', '$ende', '$betrag', '1')";
            DB::insert($db_abfrage);
            /* Zugewiesene MIETBUCHUNG_DAT auslesen */
            $last_dat = DB::getPdo()->lastInsertId();
            protokollieren('MIETENTWICKLUNG', $last_dat, '0');
        }
    }

    function check_mietentwicklung($kostentraeger_typ, $kostentrager_id, $kostenkategorie, $betrag, $anfang, $ende)
    {
        $result = DB::select("SELECT * FROM MIETENTWICKLUNG WHERE KOSTENTRAEGER_TYP='$kostentraeger_typ' && KOSTENTRAEGER_ID = '$kostentrager_id' && KOSTENKATEGORIE='$kostenkategorie' && ANFANG='$anfang' && ENDE='$ende' && BETRAG='$betrag' ");
        return !empty($result);
    }

    function get_mietentwicklung_last_id()
    {
        $result = DB::select("SELECT MIETENTWICKLUNG_ID FROM MIETENTWICKLUNG WHERE MIETENTWICKLUNG_AKTUELL='1' ORDER BY MIETENTWICKLUNG_ID DESC LIMIT 0,1");
        return $result[0]['MIETENTWICKLUNG_ID'];
    }

    function datum_1_mietdefinition($mietvertrag_id)
    {
        $result = DB::select("SELECT ANFANG FROM MIETENTWICKLUNG WHERE KOSTENTRAEGER_TYP='Mietvertrag' && KOSTENTRAEGER_ID = '$mietvertrag_id' && MIETENTWICKLUNG_AKTUELL = '1' && KOSTENKATEGORIE NOT LIKE 'Saldo Vortrag Vorverwaltung' ORDER BY ANFANG ASC LIMIT 0,1");
        if (empty($result)) {
            return false;
        } else {
            return $result[0]['ANFANG'];
        }
    }

    function summe_forderungen_seit_einzug($mietvertrag_id)
    {
        $monate_arr = $this->monate_seit_einzug_arr($mietvertrag_id);
        // $this->array_anzeigen($monate_arr);
        $summe = "0";

        for ($i = 0; $i < count($monate_arr); $i++) {
            $forderungen = $this->forderung_monatlich($mietvertrag_id, $monate_arr [$i] ['monat'], $monate_arr [$i] ['jahr']);
            for ($a = 0; $a < count($forderungen); $a++) {
                $summe = $summe + $forderungen [$a] ['BETRAG'];
            }
        }
        // $this->array_anzeigen($forderungen);
        unset ($forderungen);
        unset ($monate_arr);
        return $summe;
    }

    /* Ausgabe aller Mahngebühren für gewünschten Monat, Jahr als Array */

    function monate_seit_einzug_arr($mietvertrag_id)
    {
        $zeitraum = new zeitraum ();
        $monate_arr = $zeitraum->zeitraum_arr_seit_einzug($mietvertrag_id);
        return $monate_arr;
    }

    /* Ausgabe der Summe aller Kostenkategorien für gewünschten Monat, Jahr als String */

    function mietvertrag_grunddaten_holen($mietvertrag_id)
    {
        $result = DB::select("SELECT * FROM MIETVERTRAG WHERE MIETVERTRAG_AKTUELL='1' && MIETVERTRAG_ID='$mietvertrag_id' ORDER BY MIETVERTRAG_DAT DESC LIMIT 0,1");
        $row = $result[0];
        // Setzen von Mietvertrags Vars bzw Einzugsdatum Auszugsdatum
        $this->mietvertrag_von = $row ['MIETVERTRAG_VON'];
        $this->mietvertrag_bis = $row ['MIETVERTRAG_BIS'];
        // Ermitteln von Anzahl Personen aus dem MV
        $this->get_anzahl_personen_zu_mietvertrag($mietvertrag_id);
    }

    /* Datum der Wasserkostenabrechnung */

    function get_anzahl_personen_zu_mietvertrag($mietvertrag_id)
    {
        $result = DB::select("SELECT PERSON_MIETVERTRAG_ID FROM PERSON_MIETVERTRAG WHERE PERSON_MIETVERTRAG_MIETVERTRAG_ID='$mietvertrag_id' && PERSON_MIETVERTRAG_AKTUELL='1' ORDER BY PERSON_MIETVERTRAG_ID ASC");
        $anzahl = count($result);
        $this->anzahl_personen_im_vertrag = $anzahl; // Anzahl aller Personen im MV
    }

    /* Summe der Betriebskostenabrechnung */

    function datum_1_zahlung($mietvertrag_id)
    {
        $mv = new mietvertraege ();
        $mv->get_mietvertrag_infos_aktuell($mietvertrag_id);
        $o = new objekt ();
        $o->objekt_informationen($mv->objekt_id);
        $geldkonto_id = $o->geld_konten_arr [0] ['KONTO_ID'];

        $result = DB::select("SELECT DATUM FROM GELD_KONTO_BUCHUNGEN WHERE KOSTENTRAEGER_TYP='Mietvertrag' && KOSTENTRAEGER_ID = '$mietvertrag_id' && GELDKONTO_ID='$geldkonto_id' && AKTUELL = '1' ORDER BY DATUM ASC LIMIT 0,1");
        return $result[0]['DATUM'];
    }

    /* Summe der Heizkostenabrechnung */

    function buchungen_forderungen_seit_einzug($mietvertrag_id)
    {
        $monate_arr = $this->monate_seit_einzug_arr($mietvertrag_id);
        for ($i = 0; $i < count($monate_arr); $i++) {
            echo "<table>";
            $forderung_arr = $this->forderung_monatlich($mietvertrag_id, $monate_arr [$i] ['monat'], $monate_arr [$i] ['jahr']);
            $zahlung_arr = $this->zahlungen_monatlich($mietvertrag_id, $monate_arr [$i] ['monat'], $monate_arr [$i] ['jahr']);
            $monat_jahr = "" . $monate_arr [$i] ['monat'] . "-" . $monate_arr [$i] ['jahr'] . "";
            echo "<tr>";
            echo "<td>";
            $this->monatsbuchungen_anzeigen($monat_jahr, $zahlung_arr);
            echo "</td>";
            echo "<td>";
            $this->monatsforderungen_anzeigen($monat_jahr, $forderung_arr);
            echo "</td>";
            echo "</tr>";
        }
    }

    function zahlungen_monatlich($mietvertrag_id, $monat, $jahr)
    {
        $result = DB::select("SELECT * FROM GELD_KONTO_BUCHUNGEN WHERE KOSTENTRAEGER_TYP='MIETVERTRAG' &&  KOSTENTRAEGER_ID='$mietvertrag_id' && KONTENRAHMEN_KONTO='80001' && DATE_FORMAT( DATUM, '%Y-%m' ) = '$jahr-$monat'");
        return $result;
    }

    function monatsbuchungen_anzeigen($monat_jahr, $zahlungen_diesen_monat_arr)
    {
        echo "<table class=aktuelle_buchungen>";
        $monat_jahr_arr = explode("-", $monat_jahr);
        $monat = $monat_jahr_arr [0];
        $jahr = $monat_jahr_arr [1];
        echo "<tr><td colspan=6><b>BUCHUNGEN $monat $jahr</b></td></tr>";
        echo "<tr class=tabelle_ueberschrift_mietkontenblatt><td>B-NR</td><td>ÜBERWIESEN AM</td><td>ZAHLBETRAG</td><td>GEBUCHT AM</td><td>TEILBETRAG</td><td>KOSTENKATEGORIE</td></tr>";
        for ($i = 0; $i < count($zahlungen_diesen_monat_arr); $i++) {
            echo "<tr>";
            $buchungsdatum = $this->date_mysql2german($zahlungen_diesen_monat_arr [$i] ['DATUM']);
            echo "<td><b>" . $zahlungen_diesen_monat_arr [$i] ['BUCHUNGSNUMMER'] . "</b></td>";
            $zahldatum = $this->date_mysql2german($zahlungen_diesen_monat_arr [$i] ['ZAHLDATUM']);

            if ($i == 0) {
                echo "<td>" . $zahldatum . "</td>";
                echo "<td><b>" . $zahlungen_diesen_monat_arr [$i] ['ZAHLBETRAG'] . " €</b></td>";
                echo "<td>" . $buchungsdatum . "</td>";
            } else {
                echo "<td></td><td></td><td></td>";
            }

            echo "<td><b>" . $zahlungen_diesen_monat_arr [$i] ['BETRAG'] . " €</b></td>";
            echo "<td>" . $zahlungen_diesen_monat_arr [$i] ['KOSTENKATEGORIE'] . "</td>";
            echo "</tr>";
        }
        $summe_zahlungen = $this->summe_zahlung_monatlich($mietvertrag_id, $monat, $jahr);
        echo "<tr><td colspan=5><b> Summe: $summe_zahlungen €</b></td></tr>";
        echo "</table>";
        echo "<br>";
        return $zahlungen_diesen_monat_arr;
    }

    function summe_zahlung_monatlich($mietvertrag_id, $monat, $jahr)
    {
        $zahlungen = $this->zahlungen_monatlich($mietvertrag_id, $monat, $jahr);
        $anzahl_elemente = count($zahlungen);
        $summe = 0;
        for ($i = 0; $i < $anzahl_elemente; $i++) {
            $summe = $summe + $zahlungen [$i] ['BETRAG'];
        }
        return $summe;
    }

    function monatsforderungen_anzeigen($monat_jahr, $forderungen_diesen_monat_arr)
    {
        if (empty($forderungen_diesen_monat_arr)) {
            echo "Keine Forderungen in diesem Monat!";
        } else {
            echo "<table class=aktuelle_forderungen>";
            echo "<tr class=tabelle_ueberschrift_mietkontenblatt><td>MV</td><td>ANFANG</td><td>ENDE</td><td>KOSTENKATEGORIE</td><td>BETRAG</td></tr>";
            for ($i = 0; $i < count($forderungen_diesen_monat_arr); $i++) {
                echo "<tr>";
                echo "<td>" . $forderungen_diesen_monat_arr [$i] ['mietvertrag_id'] . "</td>";
                $anfangsdatum = $this->date_mysql2german($forderungen_diesen_monat_arr [$i] ['ANFANG']);
                echo "<td>" . $anfangsdatum . "</td>";
                $endedatum = $this->date_mysql2german($forderungen_diesen_monat_arr [$i] ['ENDE']);
                if ($endedatum == "00.00.0000") {
                    echo "<td>unbefristet</td>";
                } else {
                    echo "<td>" . $endedatum . "</td>";
                }
                echo "<td>" . $forderungen_diesen_monat_arr [$i] ['KOSTENKATEGORIE'] . "</td>";
                echo "<td><b>" . $forderungen_diesen_monat_arr [$i] ['BETRAG'] . " €</b></td>";
                echo "</tr>";
            }
            echo "<tr><td colspan=6><b> Summe: $summe_forderungen €</b></td></tr>";
            echo "</table>";
            echo "<br>";
            // echo "</div>";
            return $forderungen_diesen_monat_arr;
        }
    }

    function summe_rate_monatlich($mietvertrag_id, $monat, $jahr)
    {
        $result = DB::select("SELECT SUM(BETRAG) AS SUMME_RATE FROM MIETENTWICKLUNG WHERE KOSTENTRAEGER_TYP='MIETVERTRAG' && KOSTENTRAEGER_ID = '$mietvertrag_id' && MIETENTWICKLUNG_AKTUELL = '1' && ( ENDE = '0000-00-00' OR DATE_FORMAT( ENDE, '%Y-%m' ) >= '$jahr-$monat' && DATE_FORMAT( ANFANG, '%Y-%m' ) <= '$jahr-$monat' ) && DATE_FORMAT( ANFANG, '%Y-%m' ) <= '$jahr-$monat' && KOSTENKATEGORIE LIKE 'RATENZAHLUNG' ORDER BY ANFANG ASC");
        $row = $result[0];
        $summe = $row ['SUMME_RATE'];
        return $summe;
    }

    /* Prüfen ob diesen Monat Zahlbetrag zum MV gebucht wurde */

    function summe_mahngebuehr_im_monat($mietvertrag_id, $monat, $jahr)
    {
        $laenge = strlen($monat);
        if ($laenge == 1) {
            $monat = '0' . $monat;
        }
        $result = DB::select("SELECT SUM(BETRAG) SUMME_MAHNUNG FROM MIETENTWICKLUNG WHERE KOSTENTRAEGER_TYP='MIETVERTRAG' && KOSTENTRAEGER_ID = '$mietvertrag_id' && MIETENTWICKLUNG_AKTUELL = '1' && ( ENDE = '0000-00-00' OR DATE_FORMAT( ENDE, '%Y-%m' ) >= '$jahr-$monat') && DATE_FORMAT( ANFANG, '%Y-%m' ) <= '$jahr-$monat'  && KOSTENKATEGORIE='Mahngebühr' ORDER BY ANFANG ASC");
        if (empty($result)) {
            return false;
        } else {
            return $result[0]['SUMME_MAHNUNG'];
        }
    }

    /* Prüfen ob diesen Monat Zahlbetrag aufs Geldkonto des Objektes gebucht wurde */

    function mahngebuehr_monatlich_arr($mietvertrag_id, $monat, $jahr)
    {
        $result = DB::select("SELECT * FROM MIETENTWICKLUNG WHERE KOSTENTRAEGER_TYP='MIETVERTRAG' && KOSTENTRAEGER_ID = '$mietvertrag_id' && MIETENTWICKLUNG_AKTUELL = '1' && ( ENDE = '0000-00-00' OR DATE_FORMAT( ENDE, '%Y-%m' ) >= '$jahr-$monat') && DATE_FORMAT( ANFANG, '%Y-%m' ) <= '$jahr-$monat'  && KOSTENKATEGORIE='Mahngebühr' ORDER BY ANFANG ASC");
        if (!empty($result)) {
            return $result;
        } else {
            return false;
        }
    }

    /* Prüfen ob diesen Monat überhaupt Zahlbeträge gebucht worden sind. */

    function datum_betriebskostenabrechnung($mietvertrag_id, $monat, $jahr)
    {
        $laenge = strlen($monat);
        if ($laenge == 1 && $monat < 10) {
            $monat = "0" . $monat;
        }
        $result = DB::select("SELECT ANFANG FROM MIETENTWICKLUNG WHERE KOSTENTRAEGER_TYP='MIETVERTRAG' && KOSTENTRAEGER_ID = '$mietvertrag_id' && MIETENTWICKLUNG_AKTUELL = '1' && ( ENDE = '0000-00-00' OR DATE_FORMAT( ENDE, '%Y-%m' ) >= '$jahr-$monat' && DATE_FORMAT( ANFANG, '%Y-%m' ) <= '$jahr-$monat' ) && DATE_FORMAT( ANFANG, '%Y-%m' ) <= '$jahr-$monat' && KOSTENKATEGORIE LIKE '%Betriebskostenabrechnung%'");
        if (empty($result)) {
            return false;
        } else {
            $datum = $this->date_mysql2german($result [0]['ANFANG']);
            return $datum;
        }
    }

    /* Geldkontostand vom ausgewählten Objekt anzeigen */

    function datum_heizkostenabrechnung($mietvertrag_id, $monat, $jahr)
    {
        $laenge = strlen($monat);
        if ($laenge == 1 && $monat < 10) {
            $monat = "0" . $monat;
        }
        $result = DB::select("SELECT ANFANG FROM MIETENTWICKLUNG WHERE KOSTENTRAEGER_TYP='MIETVERTRAG' && KOSTENTRAEGER_ID = '$mietvertrag_id' && MIETENTWICKLUNG_AKTUELL = '1' && ( ENDE = '0000-00-00' OR DATE_FORMAT( ENDE, '%Y-%m' ) >= '$jahr-$monat' && DATE_FORMAT( ANFANG, '%Y-%m' ) <= '$jahr-$monat' ) && DATE_FORMAT( ANFANG, '%Y-%m' ) <= '$jahr-$monat' && KOSTENKATEGORIE LIKE '%Heizkostenabrechnung%'");
        if (empty($result)) {
            return false;
        } else {
            $datum = $this->date_mysql2german($result[0]['ANFANG']);
            return $datum;
        }
    }

    /* Anzeigen des Mietkontostandes seit Einzug */

    function datum_wasserkostenabrechnung($mietvertrag_id, $monat, $jahr)
    {
        $laenge = strlen($monat);
        if ($laenge == 1 && $monat < 10) {
            $monat = "0" . $monat;
        }
        $result = DB::select("SELECT ANFANG FROM MIETENTWICKLUNG WHERE KOSTENTRAEGER_TYP='MIETVERTRAG' && KOSTENTRAEGER_ID = '$mietvertrag_id' && MIETENTWICKLUNG_AKTUELL = '1' && ( ENDE = '0000-00-00' OR DATE_FORMAT( ENDE, '%Y-%m' ) >= '$jahr-$monat' && DATE_FORMAT( ANFANG, '%Y-%m' ) <= '$jahr-$monat' ) && DATE_FORMAT( ANFANG, '%Y-%m' ) <= '$jahr-$monat' && KOSTENKATEGORIE LIKE '%Wasserkostenabrechnung%'");
        if (empty($result)) {
            return false;
        } else {
            $datum = $this->date_mysql2german($result[0] ['ANFANG']);
            return $datum;
        }
    }

    /* Anzeigen des Mietkontostandes seit Einzug neu */

    function summe_betriebskostenabrechnung($mietvertrag_id, $monat, $jahr)
    {
        unset ($this->summe_bk_abrechnung);
        $laenge = strlen($monat);
        if ($laenge == 1 && $monat < 10) {
            $monat = "0" . $monat;
        }
        $result = DB::select("SELECT SUM(BETRAG) SUMME_BETRIEBSKOSTEN FROM MIETENTWICKLUNG WHERE KOSTENTRAEGER_TYP='Mietvertrag' && KOSTENTRAEGER_ID = '$mietvertrag_id' && MIETENTWICKLUNG_AKTUELL = '1' && ( ENDE = '0000-00-00' OR DATE_FORMAT( ENDE, '%Y-%m' ) >= '$jahr-$monat' && DATE_FORMAT( ANFANG, '%Y-%m' ) <= '$jahr-$monat' ) && DATE_FORMAT( ANFANG, '%Y-%m' ) <= '$jahr-$monat' && KOSTENKATEGORIE LIKE 'Betriebskostenabrechnung%'");
        if (empty($result)) {
            return false;
        } else {
            $summe = $result[0]['SUMME_BETRIEBSKOSTEN'];
            $summe = number_format($summe, 2, ".", "");
            $this->summe_bk_abrechnung = $summe;
            return $summe;
        }
    }

    /* ermitteln des Kontostandes Mietkonto mit zeitraum */

    function summe_heizkostenabrechnung($mietvertrag_id, $monat, $jahr)
    {
        unset ($this->summe_hk_abrechnung);
        $laenge = strlen($monat);
        if ($laenge == 1 && $monat < 10) {
            $monat = "0" . $monat;
        }
        $result = DB::select("SELECT SUM(BETRAG) SUMME_HEIZKOSTEN FROM MIETENTWICKLUNG WHERE KOSTENTRAEGER_TYP='Mietvertrag' && KOSTENTRAEGER_ID = '$mietvertrag_id' && MIETENTWICKLUNG_AKTUELL = '1' && ( ENDE = '0000-00-00' OR DATE_FORMAT( ENDE, '%Y-%m' ) >= '$jahr-$monat' && DATE_FORMAT( ANFANG, '%Y-%m' ) <= '$jahr-$monat' ) && DATE_FORMAT( ANFANG, '%Y-%m' ) <= '$jahr-$monat' && KOSTENKATEGORIE LIKE 'Heizkostenabrechnung%'");
        if (empty($result)) {
            return false;
        } else {
            $summe = $result[0]['SUMME_HEIZKOSTEN'];
            $summe = number_format($summe, 2, ".", "");
            $this->summe_hk_abrechnung = $summe;
            return $summe;
        }
    }

    /* Ausgabe der Summe aller Zahlungen */

    function summe_wasserkostenabrechnung($mietvertrag_id, $monat, $jahr)
    {
        unset ($this->summe_wasser_abrechnung);
        $laenge = strlen($monat);
        if ($laenge == 1 && $monat < 10) {
            $monat = "0" . $monat;
        }
        $result = DB::select("SELECT SUM(BETRAG) SUMME_WASSERKOSTEN FROM MIETENTWICKLUNG WHERE KOSTENTRAEGER_TYP='Mietvertrag' && KOSTENTRAEGER_ID = '$mietvertrag_id' && MIETENTWICKLUNG_AKTUELL = '1' && ( ENDE = '0000-00-00' OR DATE_FORMAT( ENDE, '%Y-%m' ) >= '$jahr-$monat' && DATE_FORMAT( ANFANG, '%Y-%m' ) <= '$jahr-$monat' ) && DATE_FORMAT( ANFANG, '%Y-%m' ) <= '$jahr-$monat' && KOSTENKATEGORIE LIKE 'Wasserkostenabrechnung%'");
        if (empty($result)) {
            return false;
        } else {
            $summe = $result[0]['SUMME_WASSERKOSTEN'];
            $summe = number_format($summe, 2, ".", "");
            $this->summe_wasser_abrechnung = $summe;
            return $summe;
        }
    }

    /* Ausgabe des Kontostandes */

    function kaltmiete_monatlich($mietvertrag_id, $monat, $jahr)
    {
        $this->ausgangs_kaltmiete = 0.00;
        $result = DB::select("SELECT SUM(BETRAG) AS SUMME_RATE FROM MIETENTWICKLUNG WHERE KOSTENTRAEGER_TYP='MIETVERTRAG' && KOSTENTRAEGER_ID = '$mietvertrag_id' && MIETENTWICKLUNG_AKTUELL = '1' && ( ENDE = '0000-00-00' OR DATE_FORMAT( ENDE, '%Y-%m' ) >= '$jahr-$monat' && DATE_FORMAT( ANFANG, '%Y-%m' ) <= '$jahr-$monat' ) && DATE_FORMAT( ANFANG, '%Y-%m' ) <= '$jahr-$monat' && (KOSTENKATEGORIE LIKE 'Miete kalt' OR KOSTENKATEGORIE LIKE 'MOD' OR KOSTENKATEGORIE LIKE 'MHG') ORDER BY ANFANG ASC");
        $summe = $result[0]['SUMME_RATE'];
        $this->ausgangs_kaltmiete = $summe;
    }

    /* Ausgabe des Kontostandes aller Geldkonten */

    function check_vz_anteilig($mietvertrag_id, $monat, $jahr)
    {
        $monat = sprintf('%02d', $monat);
        $result = DB::select("SELECT SUM(BETRAG) AS SUMME_RATE FROM MIETENTWICKLUNG WHERE KOSTENTRAEGER_TYP='MIETVERTRAG' && KOSTENTRAEGER_ID = '$mietvertrag_id' && MIETENTWICKLUNG_AKTUELL = '1' && ( ENDE = '0000-00-00' OR DATE_FORMAT( ENDE, '%Y-%m' ) >= '$jahr-$monat' && DATE_FORMAT( ANFANG, '%Y-%m' ) <= '$jahr-$monat' ) && DATE_FORMAT( ANFANG, '%Y-%m' ) <= '$jahr-$monat' && KOSTENKATEGORIE = 'Nebenkosten VZ - Anteilig' ORDER BY ANFANG ASC");
        if (!empty($result)) {
            $row = $result[0];
            $summe = nummer_komma2punkt(nummer_punkt2komma($row ['SUMME_RATE']));
            if ($summe >= 0) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /* Ausgabe der Summe aller Zahlbeträge */

    function kaltmiete_monatlich_ink_vz($mietvertrag_id, $monat, $jahr)
    {
        $this->ausgangs_kaltmiete = 0.00;
        $result = DB::select("SELECT SUM(BETRAG) AS SUMME_RATE FROM MIETENTWICKLUNG WHERE KOSTENTRAEGER_TYP='MIETVERTRAG' && KOSTENTRAEGER_ID = '$mietvertrag_id' && MIETENTWICKLUNG_AKTUELL = '1' && ( ENDE = '0000-00-00' OR DATE_FORMAT( ENDE, '%Y-%m' ) >= '$jahr-$monat' && DATE_FORMAT( ANFANG, '%Y-%m' ) <= '$jahr-$monat' ) && DATE_FORMAT( ANFANG, '%Y-%m' ) <= '$jahr-$monat' && (KOSTENKATEGORIE LIKE 'Miete kalt' OR KOSTENKATEGORIE LIKE 'MOD' OR KOSTENKATEGORIE LIKE 'MHG' OR KOSTENKATEGORIE LIKE 'Nebenkosten VZ - Anteilig') ORDER BY ANFANG ASC");
        $summe = $result[0]['SUMME_RATE'];
        $this->ausgangs_kaltmiete = $summe;
    }

    /* Ausgabe der Summe aller Zahlbeträge bis monat */

    function kaltmiete_monatlich_ohne_mod($mietvertrag_id, $monat, $jahr)
    {
        $this->ausgangs_kaltmiete = 0.00;
        $result = DB::select("SELECT SUM(BETRAG) AS SUMME_RATE FROM MIETENTWICKLUNG WHERE KOSTENTRAEGER_TYP='MIETVERTRAG' && KOSTENTRAEGER_ID = '$mietvertrag_id' && MIETENTWICKLUNG_AKTUELL = '1' && ( ENDE = '0000-00-00' OR DATE_FORMAT( ENDE, '%Y-%m' ) >= '$jahr-$monat' && DATE_FORMAT( ANFANG, '%Y-%m' ) <= '$jahr-$monat' ) && DATE_FORMAT( ANFANG, '%Y-%m' ) <= '$jahr-$monat' && (KOSTENKATEGORIE LIKE 'Miete kalt' OR KOSTENKATEGORIE LIKE 'MHG') ORDER BY ANFANG ASC");
        $summe = $result[0]['SUMME_RATE'];
        $this->ausgangs_kaltmiete = $summe;
    }

    /* Alle Zahlbeträge in Array */

    function summe_aller_zahlbetraege_bis_monat($mietvertrag_id, $monat, $jahr, $kostenkonto)
    {
        $result = DB::select("SELECT SUM(BETRAG) AS SUMME_ZAHLBETRAG FROM GELD_KONTO_BUCHUNGEN WHERE KOSTENTRAEGER_TYP='Mietvertrag' && KOSTENTRAEGER_ID='$mietvertrag_id' && KONTENRAHMEN_KONTO='$kostenkonto' && AKTUELL='1' && DATE_FORMAT(DATUM, '%Y-%m')<='$jahr-$monat'");
        return $result[0]['SUMME_ZAHLBETRAG'];
    }

    // DATUM ablauf Mietdefinition

    function alle_zahlbetraege_arr($mietvertrag_id)
    {
        $result = DB::select("SELECT DATUM, BETRAG, MIETVERTRAG_ID, BEMERKUNG FROM GELD_KONTO_BUCHUNGEN WHERE KOSTENTRAEGER_TYP='Mietvertrag' && KOSTENTRAEGER_ID='$mietvertrag_id' && AKTUELL='1' ORDER BY DATUM ASC");
        return $result;
    }

    function alle_zahlbetraege_monat_arr($mietvertrag_id, $monat, $jahr)
    {
        if ($monat < 10) {
            $monat = "0" . $monat;
        }
        $result = DB::select("SELECT DATUM, BETRAG, MIETVERTRAG_ID, BEMERKUNG FROM GELD_KONTO_BUCHUNGEN WHERE KOSTENTRAEGER_TYP='Mietvertrag' && KOSTENTRAEGER_ID='$mietvertrag_id' && AKTUELL='1' && DATE_FORMAT( DATUM, '%Y-%m' ) = '$jahr-$monat' && BEMERKUNG NOT LIKE 'Saldo Vortrag Vorverwaltung' ORDER BY DATUM ASC");
        return $result;
    }

    function zahlbetraege_im_monat_arr($mietvertrag_id, $monat, $jahr, $kostenkonto = '80001')
    {
        if ($kostenkonto == '') {
            $ko_string = '';
        } else {
            $ko_string = " && KONTENRAHMEN_KONTO='$kostenkonto'";
        }

        $laenge = strlen($monat);
        if ($laenge == 1 && $monat < 10) {
            $monat = "0" . $monat;
        }
        $mv = new mietvertraege ();
        $mv->get_mietvertrag_infos_aktuell($mietvertrag_id);
        $gk = new geldkonto_info ();
        $gk->geld_konto_ermitteln('Objekt', $mv->objekt_id, $jahr . '-' . $monat . '-01', 'Hausgeld');
        if (!empty ($gk->geldkonto_id)) {
            $result = DB::select("SELECT DATUM, BETRAG, VERWENDUNGSZWECK AS BEMERKUNG, MWST_ANTEIL FROM GELD_KONTO_BUCHUNGEN WHERE  GELDKONTO_ID='$gk->geldkonto_id' $ko_string && KOSTENTRAEGER_TYP='Mietvertrag' && KOSTENTRAEGER_ID='$mietvertrag_id' && AKTUELL='1' && DATE_FORMAT( DATUM, '%Y-%m' ) = '$jahr-$monat' ORDER BY DATUM ASC");
        } else {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\ErrorMessage('Kein Geldkonto für das Objekt hinterlegt')
            );
        }
        if (!empty($result)) {
            return $result;
        } else {
            return false;
        }
    }

    function saldo_vortrag_vorverwaltung($mietvertrag_id)
    {
        $result = DB::select("SELECT BETRAG AS SALDO_VORVERWALTUNG FROM MIETENTWICKLUNG WHERE KOSTENTRAEGER_TYP='Mietvertrag' && KOSTENTRAEGER_ID = '$mietvertrag_id' && KOSTENKATEGORIE = 'Saldo Vortrag Vorverwaltung' && MIETENTWICKLUNG_AKTUELL = '1' ORDER BY ANFANG DESC LIMIT 0,1");
        if (!empty($result)) {
            $saldo = $result[0]['SALDO_VORVERWALTUNG'];
            return $saldo;
        }
    }

    function datum_saldo_vortrag_vorverwaltung($mietvertrag_id)
    {
        unset ($this->datum_vv);
        $result = DB::select("SELECT ANFANG AS DATUM FROM MIETENTWICKLUNG WHERE KOSTENTRAEGER_TYP='Mietvertrag' && KOSTENTRAEGER_ID = '$mietvertrag_id' && KOSTENKATEGORIE = 'Saldo Vortrag Vorverwaltung' && MIETENTWICKLUNG_AKTUELL = '1' ORDER BY ANFANG DESC LIMIT 0,1");

        $row = $result[0];
        $this->datum_saldo_vv = $row ['DATUM'];
        return $row ['DATUM'];
    }

    /* Funktion zur Ermittlung von Infos und Details zu einer Buchungsnummer */

    function alle_buchungen_anzeigen($mietvertrag_id)
    {
        $this->datum_heute;
        $this->tag_heute;
        $this->monat_heute;
        $this->jahr_heute;
        $zahlungen_diesen_monat_arr = $this->alle_zahlungen_bisher($mietvertrag_id);
        if (empty($zahlungen_diesen_monat_arr)) {
            echo "<div class=aktuelle_buchungen><b>ALLE BISHERIGEN BUCHUNGEN UND ZAHLUNGEN ZUM MV: $mietvertrag_id<br>";
            echo "Keine Zahlungen und Buchungen bezogen auf MV: $mietvertrag_id!";
            echo "</div>";
        } else {
            echo "<div class=aktuelle_buchungen><b>ALLE BISHERIGEN BUCHUNGEN UND ZAHLUNGEN ZUM MV: $mietvertrag_id</b><br>";
            echo "<table class=aktuelle_buchungen>";
            echo "<tr class=tabelle_ueberschrift_mietkontenblatt><td>B-NR</td><td>ÜBERWIESEN AM</td><td>ZAHLBETRAG</td><td>GEBUCHT AM</td><td>TEILBETRAG</td><td>KOSTENKATEGORIE</td></tr>";
            for ($i = 0; $i < count($zahlungen_diesen_monat_arr); $i++) {
                echo "<tr>";
                $buchungsdatum = $this->date_mysql2german($zahlungen_diesen_monat_arr [$i] ['DATUM']);
                echo "<td><b>" . $zahlungen_diesen_monat_arr [$i] ['BUCHUNGSNUMMER'] . "</b></td>";
                $zahldatum = $this->date_mysql2german($zahlungen_diesen_monat_arr [$i] ['ZAHLDATUM']);
                echo "<td>" . $zahldatum . "</td>";
                echo "<td><b>" . $zahlungen_diesen_monat_arr [$i] ['ZAHLBETRAG'] . " €</b></td>";
                echo "<td>" . $buchungsdatum . "</td>";
                echo "<td><b>" . $zahlungen_diesen_monat_arr [$i] ['BETRAG'] . " €</b></td>";
                echo "<td>" . $zahlungen_diesen_monat_arr [$i] ['KOSTENKATEGORIE'] . "</td>";
                echo "</tr>";
            }
            $summe_zahlungen = $this->summe_aller_buchungen($mietvertrag_id);
            $summe_zahlbetraege = $this->summe_aller_zahlbetraege($mietvertrag_id);
            $summe_ueberschusse = $this->summer_aller_ueberschuesse($mietvertrag_id);
            echo "<tr><td colspan=3><b>Summe Zahlbeträge: $summe_zahlbetraege €</b><td colspan=2><b> Gebuchte Summe: $summe_zahlungen €</b></td><td><b>Summe Überschuss: $summe_ueberschusse €</b></td></tr>";
            echo "</table>";
            echo "</div>";
            return $zahlungen_diesen_monat_arr;
        }
    }

    /* Interne Buchungen der Zahlbetragaufteilung stornieren */

    function alle_zahlungen_bisher($mietvertrag_id)
    {
        $result = DB::select("SELECT DISTINCT MIETBUCHUNGEN.MIETVERTRAG_ID, MIETBUCHUNGEN.DATUM, MIETBUCHUNGEN.KOSTENKATEGORIE, MIETBUCHUNGEN.BETRAG, MIETBUCHUNGEN.BUCHUNGSNUMMER, MIETE_ZAHLBETRAG.BETRAG AS ZAHLBETRAG, MIETE_ZAHLBETRAG.DATUM AS ZAHLDATUM
FROM MIETBUCHUNGEN, MIETE_ZAHLBETRAG
WHERE MIETBUCHUNGEN.MIETVERTRAG_ID = '$mietvertrag_id' && MIETBUCHUNGEN.MIETBUCHUNGEN_AKTUELL = '1' && MIETBUCHUNGEN.BUCHUNGSNUMMER = MIETE_ZAHLBETRAG.BUCHUNGSNUMMER
ORDER BY DATUM ASC ");
        return $result;
    }

    /* Letzte Buchungsnummer vom gebuchten Zahlbetrag zu Mietvertrag finden */

    function summe_aller_buchungen($mietvertrag_id)
    {
        $zahlungen = $this->alle_zahlungen_bisher($mietvertrag_id);
        $anzahl_elemente = count($zahlungen);
        $summe = 0;
        for ($i = 0; $i < $anzahl_elemente; $i++) {
            $summe = $summe + $zahlungen [$i] ['BETRAG'];
        }
        return $summe;
    }

    function summe_aller_zahlbetraege($mietvertrag_id)
    {
        $result = DB::select("SELECT SUM(BETRAG) AS SUMME_ZAHLBETRAG FROM GELD_KONTO_BUCHUNGEN WHERE KOSTENTRAEGER_TYP='Mietvertrag' && KOSTENTRAEGER_ID='$mietvertrag_id' && AKTUELL='1'");
        return $result[0]['SUMME_ZAHLBETRAG'];
    }

    function summer_aller_ueberschuesse($mietvertrag_id)
    {
        $result = DB::select("SELECT SUM(BETRAG) AS SUMME_UEBERSCHUSS FROM MIETBUCHUNGEN WHERE MIETVERTRAG_ID='$mietvertrag_id' && KOSTENKATEGORIE='UEBERSCHUSS' && MIETBUCHUNGEN_AKTUELL='1'");
        return $result[0] ['SUMME_UEBERSCHUSS'];
    }

    function buchungsnummer_infos($bnr)
    {
        $miete_zahlbetrag_arr = $this->details_von_buchungsnummer($bnr);
        $mietvertrag_id = $miete_zahlbetrag_arr [0] ['mietvertrag_id'];
        $zahlbetrag = $miete_zahlbetrag_arr [0] ['BETRAG'];
        $buchungsdatum = $miete_zahlbetrag_arr [0] ['DATUM'];
        $bemerkung = $miete_zahlbetrag_arr [0] ['BEMERKUNG'];
        $konto = $miete_zahlbetrag_arr [0] ['KONTO'];
        $mietvertrag_info = new mietvertrag ();
        $personen_ids_mieter = $mietvertrag_info->get_personen_ids_mietvertrag($mietvertrag_id);
        $einheit_id = $mietvertrag_info->get_einheit_id_von_mietvertrag($mietvertrag_id);

        $einheit_kurzname = $this->einheit_kurzname_finden($einheit_id);
        $haus_objekt_info = new einheit ();
        $haus_objekt_info->get_einheit_haus($einheit_id);
        echo "<h1>Objekt " . $haus_objekt_info->objekt_name . " " . $haus_objekt_info->haus_strasse . " " . $haus_objekt_info->haus_nummer . "</h1> ";
        echo "<b>Mieter: ";
        $person_infos = new person ();
        for ($a = 0; $a < count($personen_ids_mieter); $a++) {
            $person_infos->get_person_infos($personen_ids_mieter [$a] ['PERSON_MIETVERTRAG_PERSON_ID']);
            echo "" . $person_infos->person_vorname . " " . $person_infos->person_nachname . " ";
        }
        echo "</b><br>";

        echo "<b>Einheit:$einheit_kurzname</b><br>";
        echo "Buchungsnummer:$bnr<br>";
        $zahlbetrag = $this->nummer_punkt2komma($zahlbetrag);
        echo "Zahlbetrag: $zahlbetrag €<br>";
        $buchungsdatum = $this->date_mysql2german($buchungsdatum);
        echo "Buchungsdatum $buchungsdatum<br>";
        echo "Konto: $konto<br>";
        echo "Buchungsnotiz:<br> $bemerkung<br>";
        $aufteilung_arr = $this->buchungsaufteilung_als_array($bnr);
        $this->erstelle_formular("Folgende interne Buchungen werden auch storniert", NULL);
        $this->hidden_feld("BUCHUNGSNUMMER", "$bnr");
        for ($a = 0; $a < count($aufteilung_arr); $a++) {
            $betrag = $this->nummer_punkt2komma($aufteilung_arr [$a] ['BETRAG']);
            echo "<br>";
            echo "<b>" . $aufteilung_arr [$a] ['KOSTENKATEGORIE'] . " ";
            echo "$betrag €</b>";
            $this->hidden_feld("MIETBUCHUNGEN[]", "" . $aufteilung_arr [$a] ['MIETBUCHUNG_DAT'] . "");
        }
        echo "<br><br>";
        $this->hidden_feld("schritt", "stornierung_in_db");
        $this->send_button("BUCHUNG_STORNIEREN", "Stornieren");
        $this->ende_formular();
    }

    function details_von_buchungsnummer($bnr)
    {
        $result = DB::select("SELECT * FROM MIETE_ZAHLBETRAG WHERE   BUCHUNGSNUMMER='$bnr' && AKTUELL='1'  ORDER BY BUCHUNGSNUMMER DESC LIMIT 0,1");
        return $result;
    }

    function einheit_kurzname_finden($einheit_id)
    {
        $db_abfrage = "SELECT EINHEIT_KURZNAME FROM EINHEIT where EINHEIT_ID='$einheit_id' && EINHEIT_AKTUELL='1'";
        $resultat = DB::select($db_abfrage);
        if (!empty($resultat))
            return $resultat['EINHEIT_KURZNAME'];
    }

    function buchungsaufteilung_als_array($bnr)
    {
        $result = DB::select("SELECT MIETBUCHUNG_DAT, BETRAG, KOSTENKATEGORIE FROM MIETBUCHUNGEN WHERE   BUCHUNGSNUMMER='$bnr' && MIETBUCHUNGEN_AKTUELL='1' ORDER BY BUCHUNGSNUMMER ASC");
        return $result;
    }

    function miete_zahlbetrag_stornieren($bnr)
    {
        DB::update("UPDATE MIETE_ZAHLBETRAG SET AKTUELL='0' WHERE BUCHUNGSNUMMER='$bnr' && AKTUELL='1'");

        /* Da nur Aktuell von 1 auf 0 gesetzt, ergibt es im Protokoll die gleiche Zeilennummer bzw. Buchungsnummer */
        protokollieren('MIETE_ZAHLBETRAG', $bnr, $bnr);
        echo "Buchung $bnr - $last_dat storniert <br>";
    }

    function mietbuchung_stornieren_intern($mietbuchung_dat)
    {
        DB::update("UPDATE MIETBUCHUNGEN SET MIETBUCHUNGEN_AKTUELL='0' WHERE MIETBUCHUNG_DAT='$mietbuchung_dat' && MIETBUCHUNGEN_AKTUELL='1'");
        /* Da nur Aktuell von 1 auf 0 gesetzt, ergibt es im Protokoll die gleiche Zeilennummer bzw. Mietbuchungsdat */
        protokollieren('MIETBUCHUNGEN', $mietbuchung_dat, $mietbuchung_dat);
        echo "Interne Buchung $mietbuchung_dat inaktiv <br>";
    }

    function buchung_zeitraum($mietvertrag_id, $von_datum, $bis_datum)
    {
        $this->datum_heute;
        $buchungen_arr = DB::select("SELECT MIETVERTRAG_ID, DATUM, KOSTENKATEGORIE, BETRAG FROM MIETBUCHUNGEN WHERE MIETVERTRAG_ID='$mietvertrag_id' && MIETBUCHUNGEN_AKTUELL = '1' && DATUM BETWEEN '$von_datum' AND '$bis_datum' ORDER BY DATUM ASC");

        $this->array_anzeigen($buchungen_arr);

        if (empty($buchungen_arr)) {
            echo "Keine aktuellen Zahlungen in diesem Monat!";
        } else {
            echo "<div class=aktuelle_buchungen><b>AKTUELLE BUCHUNGEN 				$von_datum bis $bis_datum</b><br>";
            echo "<table class=aktuelle_buchungen>";
            echo "<tr class=tabelle_ueberschrift><td>DATUM</td><td>KOSTENKATEGORIE</td><td>BETRAG</td></tr>";
            $summe_zahlungen = 0;
            for ($i = 0; $i < count($buchungen_arr); $i++) {
                echo "<tr>";
                $buchungsdatum = $this->date_mysql2german($buchungen_arr [$i] ['DATUM']);
                echo "<td>" . $buchungsdatum . "</td>";
                echo "<td>" . $buchungen_arr [$i] ['KOSTENKATEGORIE'] . "</td>";
                echo "<td>" . $buchungen_arr [$i] ['BETRAG'] . " €</td>";
                $summe_zahlungen = $summe_zahlungen + $buchungen_arr [$i] ['BETRAG'];
                echo "</tr>";
            }
            echo "<tr><td colspan=3><b> Summe: $summe_zahlungen €</b></td></tr>";
            echo "</table>";
        }
    }

    function array_anzeigen($array)
    {
        echo "<pre>";
        print_r($array);
        echo "</pre>";
    }

    function summe_uebersicht_aufteilung($mietvertrag_id, $von_datum, $bis_datum)
    {
        $result = DB::select("SELECT MIETVERTRAG_ID, KOSTENKATEGORIE, SUM(BETRAG) AS GESAMT_BETRAG FROM MIETBUCHUNGEN WHERE MIETVERTRAG_ID='$mietvertrag_id' && MIETBUCHUNGEN_AKTUELL = '1' && DATUM BETWEEN '$von_datum' AND '$bis_datum' GROUP BY KOSTENKATEGORIE ORDER BY GESAMT_BETRAG DESC");
        return $result;
    }

    function to_do_liste()
    {
        $mv_arr = DB::select("SELECT MIETVERTRAG_ID, EINHEIT_ID FROM MIETVERTRAG WHERE MIETVERTRAG_BIS>='$this->datum_heute' OR MIETVERTRAG_BIS='0000-00-00' && MIETVERTRAG_AKTUELL = '1' ORDER BY EINHEIT_ID ASC");
        $numrows = count($mv_arr);
        for ($i = 0; $i < $numrows; $i++) {
            $mietvertrag_id = $mv_arr [$i] ['mietvertrag_id'];
            $buchungen_existieren = $this->buchung_exists($mietvertrag_id, $this->monat_heute, $this->jahr_heute);
            if ($buchungen_existieren == NULL) {
                $einheit_kurzname = $this->einheit_kurzname_finden($mv_arr [$i] ['EINHEIT_ID']);
                $mietvertrag_id = $mv_arr [$i] ['mietvertrag_id'];
                $link = "<a href='" . route('web::mietkontenblatt::legacy', ['anzeigen' => 'miete_manuell_buchen', 'mietvertrag_id' => $mietvertrag_id]) . "'>$einheit_kurzname</a>";
                echo "<br>$link";
            }
        }
    }

    function buchung_exists($mietvertrag_id, $monat, $jahr)
    {
        $result = DB::select("SELECT MIETVERTRAG_ID, DATUM, KOSTENKATEGORIE, BETRAG FROM MIETBUCHUNGEN WHERE MIETVERTRAG_ID='$mietvertrag_id' && MIETBUCHUNGEN_AKTUELL = '1' && DATE_FORMAT( DATUM, '%Y-%m' ) = '$jahr-$monat' ORDER BY DATUM ASC");
        return count($result);
    }

    function text_feld_id($beschreibung, $name, $wert, $size, $id)
    {
        echo "<div class=\"input-field\">";
        echo "<input type=\"text\" id=\"$id\" name=\"$name\" value=\"$wert\" size=\"$size\" style=\"text-align:right\"  onblur=\"zusammenfassung_neuberechnen(this.form)\" onchange=\"zusammenfassung_neuberechnen(this.form)\" ><label for=\"$id\">$beschreibung</label>\n";
        echo "</div>";
    }

    function text_feld_js($beschreibung, $name, $wert, $size, $id, $js_action)
    {
        echo "<div class=\"input-field\">";
        echo "<input type=\"text\" id=\"$id\" name=\"$name\" value=\"$wert\" size=\"$size\" $js_action >\n";
        echo "<label for=\"$id\">$beschreibung</label>\n";
        echo "</div>";
    }

    function radio_button($name, $wert, $label)
    {
        //echo "<div class=\"input-field\">";
        echo "<input type=\"radio\" id=\"$wert\" name=\"$name\" value=\"$wert\"><label for=\"$wert\">$label</label>\n";
        //echo "</div>";
    }

    function radio_button_checked($name, $wert, $label)
    {
        //echo "<div class=\"input-field\">";
        echo "<input type=\"radio\" id=\"$wert\" name=\"$name\" value=\"$wert\" checked><label for=\"$wert\">$label</label>\n";
        //echo "</div>";
    }

    function send_button_js($name, $wert, $js)
    {
        echo "<button type=\"submit\" name=\"$name\" value=\"$wert\" class=\"btn waves-effect waves-light\" id=\"$name\" $js><i class=\"mdi mdi-send right\"></i>$wert</button>";
    }

    function send_button_disabled($name, $wert, $id)
    {
        echo "<button type=\"submit\" name=\"$name\" value=\"$wert\" class=\"btn waves-effect waves-light\" id=\"$id\" disabled><i class=\"mdi mdi-send right\"></i>$wert</button>";
    }

    function dropdown_me_kostenkategorien($beschreibung, $name, $kostenkategorie)
    {
        echo "<div class=\"input-field\">";
        echo "<select name=\"$name\" id=\"$name\"> \n";

        $jahr = date("Y");
        $vorjahr = $jahr - 5;

        $kostenkategorien_arr [] = 'Miete kalt';
        $kostenkategorien_arr [] = 'Heizkosten Vorauszahlung';
        $kostenkategorien_arr [] = 'Nebenkosten Vorauszahlung';
        $kostenkategorien_arr [] = 'Nebenkosten VZ - Anteilig';
        $kostenkategorien_arr [] = 'Kabel TV';
        $kostenkategorien_arr [] = 'Untermieter Zuschlag';
        $kostenkategorien_arr [] = 'MOD';
        $kostenkategorien_arr [] = 'MHG';
        $kostenkategorien_arr [] = 'Ratenzahlung';
        $kostenkategorien_arr [] = 'Saldo Vortrag Vorverwaltung';
        $kostenkategorien_arr [] = 'Mietminderung';
        $kostenkategorien_arr [] = 'Stellplatzmiete';

        for ($a = $jahr; $a >= $vorjahr; $a--) {
            $kostenkategorien_arr [] = "Betriebskostenabrechnung $a";
            $kostenkategorien_arr [] = "Heizkostenabrechnung $a";
            $kostenkategorien_arr [] = "Kaltwasserabrechnung $a";
            $kostenkategorien_arr [] = "Kabel TV $a";
            $kostenkategorien_arr [] = "Thermenwartung $a";
        }

        for ($a = $jahr; $a >= $vorjahr; $a--) {
            $kostenkategorien_arr [] = "Energieverbrauch lt. Abr. $a";
        }

        $anzahl_kats = count($kostenkategorien_arr);
        for ($a = 0; $a < $anzahl_kats; $a++) {
            $katname_value = $kostenkategorien_arr [$a];
            echo $katname_value;
            if ($katname_value == $kostenkategorie) {
                echo "<option value=\"$katname_value\" selected>$katname_value</option>\n";
            } else {
                echo "<option value=\"$katname_value\">$katname_value</option>\n";
            }
        }
        echo "</select><label for=\"$name\">$beschreibung</label>";
        echo "</div>";
    }

    function datum_form()
    {
        $this->erstelle_formular("Buchungsdatum und Kontoauszugsnr eingeben...", NULL);
        $tag_heute = date("d");
        $monat_heute = date("m");
        $jahr_heute = date("Y");
        echo "<table>";
        echo "<tr><td>";
        $this->text_feld('Kontoauszugsnr.', 'KONTOAUSZUGSNR', session()->get('temp_kontoauszugsnummer'), 5);
        echo "</td>";
        echo "<td>Buchungsdatum</td><td>";
        echo "<select id=\"tag\" name=\"tag\"  class=\"datum\">\n";
        for ($i = 1; $i <= 31; $i++) {
            if ($i == $tag_heute) {
                echo "<option value=\"$i\" selected>$i</option>\n";
            } else {
                echo "<option value=\"$i\" >$i</option>\n";
            }
        }
        echo "</select>\n";
        echo "<select id=\"monat\" name=\"monat\" class=\"datum\">\n";
        for ($i = 1; $i <= 12; $i++) {
            if ($i == $monat_heute) {
                echo "<option value=\"$i\" selected>$i</option>\n";
            } else {
                echo "<option value=\"$i\" >$i</option>\n";
            }
        }
        echo "</select>\n";
        echo "<select name=\"jahr\" id=\"jahr\" class=\"datum\">\n";
        $vorjahr = $jahr_heute - 1;
        echo "<option value=\"$vorjahr\" selected>$vorjahr</option>\n";
        echo "<option value=\"$jahr_heute\" selected>$jahr_heute</option>\n";
        echo "</select>\n";
        echo "</td>";
        echo "<td>";
        $this->send_button("datum_setzen", "Datum setzen");
        echo "</td>";

        echo "</tr></table>";
        $this->ende_formular();
    }

    // ############ ende formular ###########
} // end class
