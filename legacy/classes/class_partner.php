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
    function get_partner_konto_id($partner_id) {
        return $partner_id;
    }

    /*
	 * function get_partner_name($partner_id){
	 * $result = mysql_query ("SELECT PARTNER_NAME FROM PARTNER_LIEFERANT WHERE PARTNER_ID='$partner_id' && AKTUELL = '1'");
	 * $row = mysql_fetch_assoc($result);
	 * return $row['PARTNER_NAME'];
	 * }
	 */
    function get_aussteller_info($partner_id) {
        $result = mysql_query ( "SELECT PARTNER_NAME, STRASSE, NUMMER, PLZ, ORT FROM PARTNER_LIEFERANT WHERE PARTNER_ID='$partner_id' && AKTUELL = '1'" );
        $row = mysql_fetch_assoc ( $result );
        // return
        $this->rechnungs_aussteller_name = $row ['PARTNER_NAME'];
        $this->rechnungs_aussteller_strasse = $row ['STRASSE'];
        $this->rechnungs_aussteller_hausnr = $row ['NUMMER'];
        $this->rechnungs_aussteller_plz = $row ['PLZ'];
        $this->rechnungs_aussteller_ort = $row ['ORT'];
    }
    function get_empfaenger_info($partner_id) {
        $result = mysql_query ( "SELECT PARTNER_NAME, STRASSE, NUMMER, PLZ, ORT FROM PARTNER_LIEFERANT WHERE PARTNER_ID='$partner_id' && AKTUELL = '1'" );
        $row = mysql_fetch_assoc ( $result );
        // return
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
        // $form->text_feld("Partnername:", "partnername", "", "10");
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
                fehlermeldung_ausgeben ( "Dateneingabe unvollständig!!!<br>Sie werden weitergeleitet." );
                session()->put('partnername', $partnername);
                session()->put('strasse', $str);
                session()->put('hausnummer', $hausnr);
                session()->put('plz', $plz);
                session()->put('ort', $ort);
                session()->put('land', $land);
                session()->put('kreditinstitut', $kreditinstitut);
                session()->put('KONTONUMMER', $kontonummer);
                session()->put('BLZ', $blz);

                $fehler = true;
                weiterleiten_in_sec ( route('legacy::rechnungen::index', ['option' => 'partner_erfassen']), 3 );
                die ();
            }
        } // Ende foreach

        /* Prüfen ob Partner/Liefernat vorhanden */
        $result_3 = mysql_query ( "SELECT * FROM PARTNER_LIEFERANT WHERE PARTNER_NAME = '$clean_arr[partnername]' && STRASSE='$clean_arr[strasse]' && NUMMER='$clean_arr[hausnummer]' && PLZ='$clean_arr[plz]' && AKTUELL = '1' ORDER BY PARTNER_NAME" );
        $numrows_3 = mysql_numrows ( $result_3 );

        /* Wenn kein Fehler durch eingabe oder partner in db nicht vorhanden wird neuer datensatz gespeichert */

        if (! $fehler && $numrows_3 < 1) {
            /* Partnerdaten ohne Kontoverbindung */
            $partner_id = $this->letzte_partner_id ();
            $partner_id = $partner_id + 1;
            $db_abfrage = "INSERT INTO PARTNER_LIEFERANT VALUES (NULL, $partner_id, '$clean_arr[partnername]','$clean_arr[strasse]', '$clean_arr[hausnummer]','$clean_arr[plz]','$clean_arr[ort]','$clean_arr[land]','1')";
            $resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
            /* Protokollieren */
            $last_dat = mysql_insert_id ();
            protokollieren ( 'PARTNER_LIEFERANT', $last_dat, '0' );

            if (! empty ( $kreditinstitut ) or ! empty ( $kontonummer ) or ! empty ( $blz )) {
                /* Kontodaten speichern */
                $konto_id = $this->letzte_geldkonto_id ();
                $konto_id = $konto_id + 1;
                $db_abfrage = "INSERT INTO GELD_KONTEN VALUES (NULL, '$konto_id','$clean_arr[partnername] - Konto','$clean_arr[partnername]', '$clean_arr[KONTONUMMER]','$clean_arr[BLZ]', '$clean_arr[kreditinstitut]','1')";
                $resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
                /* Protokollieren */
                $last_dat = mysql_insert_id ();
                protokollieren ( 'GELD_KONTEN', $last_dat, '0' );
                /* Geldkonto dem Partner zuweisen */
                $letzte_zuweisung_id = $this->letzte_zuweisung_geldkonto_id ();
                $letzte_zuweisung_id = $letzte_zuweisung_id + 1;
                $db_abfrage = "INSERT INTO GELD_KONTEN_ZUWEISUNG VALUES (NULL, '$letzte_zuweisung_id','$konto_id', 'Partner','$partner_id', '1')";
                $resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
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
        $result = mysql_query ( "SELECT PARTNER_ID FROM PARTNER_LIEFERANT  ORDER BY PARTNER_ID DESC LIMIT 0,1" );
        $row = mysql_fetch_assoc ( $result );
        return $row ['PARTNER_ID'];
    }

    /* Letzte Partnergeldkonto ID */
    function letzte_geldkonto_id() {
        $result = mysql_query ( "SELECT KONTO_ID FROM GELD_KONTEN ORDER BY KONTO_ID DESC LIMIT 0,1" );
        $row = mysql_fetch_assoc ( $result );
        return $row ['KONTO_ID'];
    }

    /* Letzte Zuweisunggeldkonto ID */
    function letzte_zuweisung_geldkonto_id() {
        $result = mysql_query ( "SELECT ZUWEISUNG_ID FROM GELD_KONTEN_ZUWEISUNG ORDER BY ZUWEISUNG_ID DESC LIMIT 0,1" );
        $row = mysql_fetch_assoc ( $result );
        return $row ['ZUWEISUNG_ID'];
    }
    function partner_rechts_anzeigen() {
        $result = mysql_query ( "SELECT * FROM PARTNER_LIEFERANT WHERE AKTUELL = '1' ORDER BY PARTNER_NAME" );
        $numrows = mysql_numrows ( $result );
        if ($numrows > 0) {
            $form = new mietkonto ();
            $form->erstelle_formular ( "Partner", NULL );
            echo "<div class=\"tabelle\">\n";
            while ( $row = mysql_fetch_assoc ( $result ) )
                $my_array [] = $row;
            echo "<table>\n";
            // echo "<tr class=\"feldernamen\"><td>Partner</td></tr>\n";
            echo "<tr><th>Partner</th></tr>";
            for($i = 0; $i < count ( $my_array ); $i ++) {
                echo "<tr><td>" . $my_array [$i] ['PARTNER_NAME'] . "</td></tr>\n";
            }
            echo "</table></div>\n";
            $form->ende_formular ();
        } else {
            echo "Keine Partner";
        }
    }
    function partner_in_array() {
        $result = mysql_query ( "SELECT * FROM PARTNER_LIEFERANT WHERE AKTUELL = '1' ORDER BY PARTNER_NAME ASC" );
        $numrows = mysql_numrows ( $result );
        if ($numrows) {
            while ( $row = mysql_fetch_assoc ( $result ) )
                $my_array [] = $row;
            return $my_array;
        } else {
            return false;
        }
    }
    function partner_grunddaten($partner_id) {
        $result = mysql_query ( "SELECT * FROM PARTNER_LIEFERANT WHERE PARTNER_ID='$partner_id' && AKTUELL = '1' ORDER BY PARTNER_DAT DESC LIMIT 0,1" );
        $numrows = mysql_numrows ( $result );
        if ($numrows > 0) {
            $row = mysql_fetch_assoc ( $result );
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
        $result = mysql_query ( "SELECT PARTNER_ID FROM PARTNER_LIEFERANT WHERE REPLACE(PARTNER_NAME, '<br>', '') ='$partner_name' && AKTUELL = '1' ORDER BY PARTNER_DAT DESC LIMIT 0,1" );
        $numrows = mysql_numrows ( $result );
        if ($numrows > 0) {
            $row = mysql_fetch_assoc ( $result );
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
    function partner_liste() {
        $partner_arr = $this->partner_in_array ();
        echo "<table class=\"sortable\">";
        // echo "<tr class=\"feldernamen\"><td width=\"200px\">Name</td><td>Anschrift</td><td>Details</td></tr>";
        echo "<tr><th>Partner</th><th>Anschrift</th><th>Details</th></tr>";
        $zaehler = 0;
        for($a = 0; $a < count ( $partner_arr ); $a ++) {
            $zaehler ++;
            $partner_id = $partner_arr [$a] ['PARTNER_ID'];
            $partner_name = $partner_arr [$a] ['PARTNER_NAME'];
            $partner_link_detail = "<a href='" . route('legacy::partner::index', ['option' => 'partner_im_detail', 'partner_id' => $partner_id]) . "'>$partner_name</a>";
            $link_detail_hinzu = "<a href='" . route('legacy::details::index', ['option' => 'details_hinzu', 'detail_tabelle' => 'PARTNER_LIEFERANT', 'detail_id' => $partner_id]) . "'>Details</a>";
            $partner_strasse = $partner_arr [$a] ['STRASSE'];
            $partner_nr = $partner_arr [$a] ['NUMMER'];
            $partner_plz = $partner_arr [$a] ['PLZ'];
            $partner_ort = $partner_arr [$a] ['ORT'];
            $anschrift = "$partner_strasse $partner_nr, $partner_plz $partner_ort";
            if ($zaehler == 1) {
                echo "<tr valign=\"top\" class=\"zeile1\"><td>$partner_link_detail</td><td>$anschrift</td><td>$link_detail_hinzu</td></tr>";
            }
            if ($zaehler == 2) {
                echo "<tr valign=\"top\" class=\"zeile2\"><td>$partner_link_detail</td><td>$anschrift</td><td>$link_detail_hinzu</td></tr>";
                $zaehler = 0;
            }
        }
        echo "</table><br>\n";
    }
    function partner_auswahl() {
        session()->put('url.intended', URL::previous());
        session()->forget('partner_id');
        $form = new formular ();
        $form->erstelle_formular ( "Partner wählen", NULL );
        $result = mysql_query ( "
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
        $numrows = mysql_numrows ( $result );
        echo "<p class=\"objekt_auswahl\">";
        if ($numrows) {
            while ( $row = mysql_fetch_assoc ( $result ) ) {
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
	function anzahl_rechnungen($p_id) {
		$result = mysql_query ( "SELECT COUNT(BELEG_NR) FROM RECHNUNGEN WHERE AKTUELL = '1' && (AUSSTELLER_ID='$p_id' OR EMPFAENGER_ID='$p_id')  ORDER BY PARTNER_NAME ASC" );
		$numrows = mysql_numrows ( $result );
	}
} // Ende Klasse Partner