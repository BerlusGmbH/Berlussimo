<?php

class partner extends rechnung {
    var $rechnungs_aussteller_name;
    var $rechnungs_aussteller_strasse;
    var $rechnungs_aussteller_hausnr;
    var $rechnungs_aussteller_plz;
    var $rechnungs_aussteller_ort;
    var $rechnungs_empfaenger_name;
    var $rechnungs_empfaenger_strasse;
    var $rechnungs_empfaenger_hausnr;
    var $rechnungs_empfaenger_plz;
    var $rechnungs_empfaenger_ort;
    public $partner_id;
    public $partner_name;
    public $partner_str;
    public $partner_nr;
    public $partner_plz;
    public $partner_ort;
    public $partner_land;

    function get_aussteller_info($partner_id) {
        $result = DB::select( "SELECT PARTNER_NAME, STRASSE, NUMMER, PLZ, ORT FROM PARTNER_LIEFERANT WHERE PARTNER_ID='$partner_id' && AKTUELL = '1'" );
        $row = $result[0];
        $this->rechnungs_aussteller_name = $row ['PARTNER_NAME'];
        $this->rechnungs_aussteller_strasse = $row ['STRASSE'];
        $this->rechnungs_aussteller_hausnr = $row ['NUMMER'];
        $this->rechnungs_aussteller_plz = $row ['PLZ'];
        $this->rechnungs_aussteller_ort = $row ['ORT'];
    }
    function get_empfaenger_info($partner_id) {
        $result = DB::select( "SELECT PARTNER_NAME, STRASSE, NUMMER, PLZ, ORT FROM PARTNER_LIEFERANT WHERE PARTNER_ID='$partner_id' && AKTUELL = '1'" );
        $row = $result[0];
        $this->rechnungs_empfaenger_name = $row ['PARTNER_NAME'];
        $this->rechnungs_empfaenger_strasse = $row ['STRASSE'];
        $this->rechnungs_empfaenger_hausnr = $row ['NUMMER'];
        $this->rechnungs_empfaenger_plz = $row ['PLZ'];
        $this->rechnungs_empfaenger_ort = $row ['ORT'];
    }

    /* Partner erfassen Formular */
    function form_partner_erfassen() {
        $form = new mietkonto ();
        $form->erstelle_formular ( "Partner erfassen", NULL );
        $form->text_bereich ( "Partnername", "partnername", session()->get('partnername'), "20", "3" );
        $form->text_feld ( "Strasse:", "strasse", session()->get('strasse'), "50" );
        $form->text_feld ( "Nummer:", "hausnummer", session()->get('hausnummer'), "10" );
        $form->text_feld ( "Postleitzahl:", "plz", session()->get('plz'), "10" );
        $form->text_feld ( "Ort:", "ort", session()->get('ort'), "10" );
        $form->text_feld ( "Land:", "land", session()->get('land'), "10" );
        $form->text_feld ( "Kreditinstitut:", "kreditinstitut", "", "10" );
        $form->text_feld ( "Kontonummer:", "kontonummer", "", "10" );
        $form->text_feld ( "Bankleitzahl:", "blz", "", "10" );
        $form->send_button ( "submit_partner", "Partner speichern" );
        $form->hidden_feld ( "option", "partner_gesendet" );
        $form->ende_formular ();
    }

    /* Partner in Datenbank speichern */
    function partner_speichern($clean_arr) {
        foreach ( $clean_arr as $key => $value ) {
            $partnername = $clean_arr ['partnername'];
            $str = $clean_arr ['strasse'];
            $hausnr = $clean_arr ['hausnummer'];
            $plz = $clean_arr ['plz'];
            $ort = $clean_arr ['ort'];
            $land = $clean_arr ['land'];
            $kreditinstitut = $clean_arr ['kreditinstitut'];
            $kontonummer = $clean_arr ['KONTONUMMER'];
            $blz = $clean_arr ['BLZ'];

            print_r ( $clean_arr );
            if (empty ( $partnername ) or empty ( $str ) or empty ( $hausnr ) or empty ( $plz ) or empty ( $ort ) or empty ( $land )) {
                session()->put('partnername', $partnername);
                session()->put('strasse', $str);
                session()->put('hausnummer', $hausnr);
                session()->put('plz', $plz);
                session()->put('ort', $ort);
                session()->put('land', $land);
                session()->put('kreditinstitut', $kreditinstitut);
                session()->put('KONTONUMMER', $kontonummer);
                session()->put('BLZ', $blz);

                throw new \App\Exceptions\MessageException(
                    new \App\Messages\ErrorMessage("Dateneingabe unvollständig."),
                    0,
                    null,
                    route('legacy::rechnungen::index', ['option' => 'partner_erfassen'])
                );
            }
        } // Ende foreach

        /* Prüfen ob Partner/Liefernat vorhanden */
        $result_3 = DB::select( "SELECT * FROM PARTNER_LIEFERANT WHERE PARTNER_NAME = '$clean_arr[partnername]' && STRASSE='$clean_arr[strasse]' && NUMMER='$clean_arr[hausnummer]' && PLZ='$clean_arr[plz]' && AKTUELL = '1' ORDER BY PARTNER_NAME" );
        $numrows_3 = count($result_3);

        /* Wenn kein Fehler durch eingabe oder partner in db nicht vorhanden wird neuer datensatz gespeichert */

        if (! $fehler && $numrows_3 < 1) {
            /* Partnerdaten ohne Kontoverbindung */
            $partner_id = $this->letzte_partner_id ();
            $partner_id = $partner_id + 1;
            $db_abfrage = "INSERT INTO PARTNER_LIEFERANT VALUES (NULL, $partner_id, '$clean_arr[partnername]','$clean_arr[strasse]', '$clean_arr[hausnummer]','$clean_arr[plz]','$clean_arr[ort]','$clean_arr[land]','1')";
            DB::insert( $db_abfrage );
            /* Protokollieren */
            $last_dat = DB::getPdo()->lastInsertId();
            protokollieren ( 'PARTNER_LIEFERANT', $last_dat, '0' );

            if (! empty ( $kreditinstitut ) or ! empty ( $kontonummer ) or ! empty ( $blz )) {
                /* Kontodaten speichern */
                $konto_id = $this->letzte_geldkonto_id ();
                $konto_id = $konto_id + 1;
                $db_abfrage = "INSERT INTO GELD_KONTEN VALUES (NULL, '$konto_id','$clean_arr[partnername] - Konto','$clean_arr[partnername]', '$clean_arr[KONTONUMMER]','$clean_arr[BLZ]', '$clean_arr[kreditinstitut]','1')";
                DB::insert( $db_abfrage );
                /* Protokollieren */
                $last_dat = DB::getPdo()->lastInsertId();
                protokollieren ( 'GELD_KONTEN', $last_dat, '0' );
                /* Geldkonto dem Partner zuweisen */
                $letzte_zuweisung_id = $this->letzte_zuweisung_geldkonto_id ();
                $letzte_zuweisung_id = $letzte_zuweisung_id + 1;
                $db_abfrage = "INSERT INTO GELD_KONTEN_ZUWEISUNG VALUES (NULL, '$letzte_zuweisung_id','$konto_id', 'Partner','$partner_id', '1')";
                DB::insert( $db_abfrage );
            }
            if (isset ( $resultat )) {
                hinweis_ausgeben ( "Partner $clean_arr[partnername] wurde gespeichert." );
                weiterleiten_in_sec ( route('legacy::rechnungen::index', ['option' => 'partner_erfassen'], false), 2 );
            }
        } // ende fehler
        if ($numrows_3 > 0) {
            fehlermeldung_ausgeben ( "Partner $clean_arr[partnername] exisitiert bereits." );
            weiterleiten_in_sec ( route('legacy::rechnungen::index', ['option' => 'partner_erfassen'], false), 2 );
        }
        session()->forget('partnername');
        session()->forget('strasse');
        session()->forget('hausnummer');
        session()->forget('plz');
        session()->forget('ort');
        session()->forget('land');
        session()->forget('kreditinstitut');
        session()->forget('KONTONUMMER');
        session()->forget('BLZ');
    } // Ende funktion

    /* Letzte Partner ID */
    function letzte_partner_id() {
        $result = DB::select( "SELECT PARTNER_ID FROM PARTNER_LIEFERANT  ORDER BY PARTNER_ID DESC LIMIT 0,1" );
        $row = $result[0];
        return $row ['PARTNER_ID'];
    }

    /* Letzte Partnergeldkonto ID */
    function letzte_geldkonto_id() {
        $result = DB::select( "SELECT KONTO_ID FROM GELD_KONTEN ORDER BY KONTO_ID DESC LIMIT 0,1" );
        $row = $result[0];
        return $row ['KONTO_ID'];
    }

    /* Letzte Zuweisunggeldkonto ID */
    function letzte_zuweisung_geldkonto_id() {
        $result = DB::select( "SELECT ZUWEISUNG_ID FROM GELD_KONTEN_ZUWEISUNG ORDER BY ZUWEISUNG_ID DESC LIMIT 0,1" );
        $row = $result[0];
        return $row ['ZUWEISUNG_ID'];
    }
    function partner_rechts_anzeigen() {
        $result = DB::select( "SELECT * FROM PARTNER_LIEFERANT WHERE AKTUELL = '1' ORDER BY PARTNER_NAME" );
        if (!empty($result)) {
            $form = new mietkonto ();
            $form->erstelle_formular ( "Partner", NULL );
            echo "<div class=\"tabelle\">\n";
            echo "<table>\n";
            echo "<tr><th>Partner</th></tr>";
            $numrows = count($result);
            for($i = 0; $i < $numrows; $i ++) {
                echo "<tr><td>" . $result[$i] ['PARTNER_NAME'] . "</td></tr>\n";
            }
            echo "</table></div>\n";
            $form->ende_formular ();
        } else {
            echo "Keine Partner";
        }
    }
    function partner_in_array() {
        $result = DB::select( "SELECT * FROM PARTNER_LIEFERANT WHERE AKTUELL = '1' ORDER BY PARTNER_NAME ASC" );
        if (!empty($result)) {
            return $result;
        } else {
            return false;
        }
    }
    function partner_grunddaten($partner_id) {
        $result = DB::select( "SELECT * FROM PARTNER_LIEFERANT WHERE PARTNER_ID='$partner_id' && AKTUELL = '1' ORDER BY PARTNER_DAT DESC LIMIT 0,1" );
        if (!empty($result)) {
            $row = $result[0];
            $this->partner_id = $partner_id;
            $this->partner_name = $row ['PARTNER_NAME'];
            $this->partner_str = $row ['STRASSE'];
            $this->partner_nr = $row ['NUMMER'];
            $this->partner_plz = $row ['PLZ'];
            $this->partner_ort = $row ['ORT'];
            $this->partner_land = $row ['LAND'];
        } else {
            return false;
        }
    }
    function getpartner_id_name($partner_name) {
        $result = DB::select( "SELECT PARTNER_ID FROM PARTNER_LIEFERANT WHERE REPLACE(PARTNER_NAME, '<br>', '') ='$partner_name' && AKTUELL = '1' ORDER BY PARTNER_DAT DESC LIMIT 0,1" );
        if (!empty($result)) {
            $row = $result[0];
            $this->partner_id = $row ['PARTNER_ID'];
        } else {
            return false;
        }
    }
    function partnerdaten_anzeigen($partner_id) {
        $this->partner_grunddaten ( $partner_id );
        echo "<b>Partnername:</b><br>$this->partner_name<br>";
        echo "<br><b>Anschrift:</b><br>$this->partner_str $this->partner_nr<br>";
        echo "$this->partner_plz $this->partner_ort<br>";
        echo "$this->partner_land";

        $g = new geldkonto_info ();
        $anzahl_konten = $g->geldkonten_anzahl ( 'Partner', $partner_id );
        echo "<hr><b>Anzahl Geldkonten: $anzahl_konten</b><hr>";
        $this->geldkonten_anzeigen ( $partner_id );
    }
    function geldkonten_anzeigen($partner_id) {
        $g = new geldkonto_info ();
        $anzahl_konten = $g->geldkonten_anzahl ( 'Partner', $partner_id );
        $geldkonten_arr = $g->geldkonten_arr ( 'Partner', $partner_id );

        for($a = 0; $a < $anzahl_konten; $a ++) {
            $beguenstigter = $geldkonten_arr [$a] ['BEGUENSTIGTER'];
            $kontonr = $geldkonten_arr [$a] ['KONTONUMMER'];
            $blz = $geldkonten_arr [$a] ['BLZ'];
            $bank = $geldkonten_arr [$a] ['INSTITUT'];
            $i = $a + 1;
            echo "<b>Konto $i:</b><br><br>";
            echo "Begünstigter: $beguenstigter<br>";
            echo "Bankinstitut: $bank<br>";
            echo "Kontonummer: $kontonr<br>";
            echo "BLZ: $blz<hr>";
        }
    }
    function partner_dropdown($label, $name, $id, $vorwahl_id = null) {
        $partner_arr = $this->partner_in_array ();
        echo "<div class='input-field'>";
        echo "<select name=\"$name\" size=\"1\" id=\"$id\">";
        echo "<option value=\"0\">Bitte wählen</option>\n";
        for($a = 0; $a < count ( $partner_arr ); $a ++) {
            $partner_id = $partner_arr [$a] ['PARTNER_ID'];
            $partner_name = $partner_arr [$a] ['PARTNER_NAME'];
            if ($vorwahl_id == $partner_id) {
                echo "<option value=\"$partner_id\" selected>$partner_name</OPTION>\n";
            } else {
                echo "<option value=\"$partner_id\">$partner_name</OPTION>\n";
            }
        }
        echo "</select><label for=\"$id\">$label</label>";
        echo "<div><br>\n";
    }
    function partner_auswahl() {
        session()->put('url.intended', URL::previous());
        session()->forget('partner_id');
        $form = new formular ();
        $form->erstelle_formular ( "Partner wählen", NULL );
        $result = DB::select( "
SELECT PARTNER_NAME, PARTNER_LIEFERANT.PARTNER_ID, RECHNUNGEN
FROM PARTNER_LIEFERANT LEFT JOIN (
	SELECT PARTNER_ID, SUM(RECHNUNGEN) AS RECHNUNGEN
	FROM ((
			SELECT AUSSTELLER_ID AS PARTNER_ID, COUNT(*) AS RECHNUNGEN
			FROM RECHNUNGEN
			WHERE AKTUELL = '1'
			GROUP BY AUSSTELLER_ID
		)
		UNION
		(
			SELECT EMPFAENGER_ID AS PARTNER_ID, COUNT(*) AS RECHNUNGEN
			FROM RECHNUNGEN
			WHERE AKTUELL = '1'
			GROUP BY EMPFAENGER_ID
	)) AS RECHNUNGEN
	GROUP BY PARTNER_ID
) AS RECHNUNGEN ON (RECHNUNGEN.PARTNER_ID=PARTNER_LIEFERANT.PARTNER_ID)
WHERE PARTNER_LIEFERANT.AKTUELL = '1'
ORDER BY RECHNUNGEN DESC, PARTNER_NAME ASC;
" );
        echo "<p class=\"objekt_auswahl\">";
        if (!empty($result)) {
            foreach( $result as $row ) {
                $partner_link = "<a class=\"objekt_auswahl_buchung\" href='" . route('legacy::partner::select', [$row['PARTNER_ID']]) . "'>$row[PARTNER_NAME]</a>";
				echo "$partner_link<hr>";
			}
			echo "</p>";
		} else {
			echo "Kein Partner vorhanden";
			return FALSE;
		}
		$form->ende_formular ();
	}
} // Ende Klasse Partner