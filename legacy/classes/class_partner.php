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
        $result = DB::select("SELECT PARTNER_NAME, STRASSE, NUMMER, PLZ, ORT FROM PARTNER_LIEFERANT WHERE id='$partner_id' && AKTUELL = '1'");
        $row = $result[0];
        $this->rechnungs_aussteller_name = $row ['PARTNER_NAME'];
        $this->rechnungs_aussteller_strasse = $row ['STRASSE'];
        $this->rechnungs_aussteller_hausnr = $row ['NUMMER'];
        $this->rechnungs_aussteller_plz = $row ['PLZ'];
        $this->rechnungs_aussteller_ort = $row ['ORT'];
    }
    function get_empfaenger_info($partner_id) {
        $result = DB::select("SELECT PARTNER_NAME, STRASSE, NUMMER, PLZ, ORT FROM PARTNER_LIEFERANT WHERE id='$partner_id' && AKTUELL = '1'");
        $row = $result[0];
        $this->rechnungs_empfaenger_name = $row ['PARTNER_NAME'];
        $this->rechnungs_empfaenger_strasse = $row ['STRASSE'];
        $this->rechnungs_empfaenger_hausnr = $row ['NUMMER'];
        $this->rechnungs_empfaenger_plz = $row ['PLZ'];
        $this->rechnungs_empfaenger_ort = $row ['ORT'];
    }

    /* Letzte Partner ID */
    function letzte_partner_id() {
        $result = DB::select("SELECT id FROM PARTNER_LIEFERANT  ORDER BY PARTNER_ID DESC LIMIT 0,1");
        $row = $result[0];
        return $row ['id'];
    }

    /* Letzte Partnergeldkonto ID */
    function letzte_geldkonto_id() {
        $result = DB::select( "SELECT KONTO_ID FROM GELD_KONTEN ORDER BY KONTO_ID DESC LIMIT 0,1" );
        $row = $result[0];
        return $row ['KONTO_ID'];
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
        $result = DB::select("SELECT * FROM PARTNER_LIEFERANT WHERE id='$partner_id' && AKTUELL = '1' ORDER BY PARTNER_DAT DESC LIMIT 0,1");
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
        $result = DB::select("SELECT id FROM PARTNER_LIEFERANT WHERE REPLACE(PARTNER_NAME, '<br>', '') ='$partner_name' && AKTUELL = '1' ORDER BY PARTNER_DAT DESC LIMIT 0,1");
        if (!empty($result)) {
            $row = $result[0];
            $this->partner_id = $row ['id'];
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
            $partner_id = $partner_arr [$a] ['id'];
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
SELECT PARTNER_NAME, PARTNER_LIEFERANT.id, RECHNUNGEN
FROM PARTNER_LIEFERANT LEFT JOIN (
	SELECT id, SUM(RECHNUNGEN) AS RECHNUNGEN
	FROM ((
			SELECT AUSSTELLER_ID AS id, COUNT(*) AS RECHNUNGEN
			FROM RECHNUNGEN
			WHERE AKTUELL = '1'
			GROUP BY AUSSTELLER_ID
		)
		UNION
		(
			SELECT EMPFAENGER_ID AS id, COUNT(*) AS RECHNUNGEN
			FROM RECHNUNGEN
			WHERE AKTUELL = '1'
			GROUP BY EMPFAENGER_ID
	)) AS RECHNUNGEN
	GROUP BY id
) AS RECHNUNGEN ON (RECHNUNGEN.id=PARTNER_LIEFERANT.id)
WHERE PARTNER_LIEFERANT.AKTUELL = '1'
ORDER BY RECHNUNGEN DESC, PARTNER_NAME ASC;
" );
        echo "<p class=\"objekt_auswahl\">";
        if (!empty($result)) {
            foreach( $result as $row ) {
                $partner_link = "<a class=\"objekt_auswahl_buchung\" href='" . route('web::partner::select', [$row['id']]) . "'>$row[PARTNER_NAME]</a>";
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
