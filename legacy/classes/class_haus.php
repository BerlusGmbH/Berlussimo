<?php

class haus extends objekt {
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
    public $haus_stadt;
    public $haus_nummer;
    public $haus_strasse;

    function get_haus_info($haus_id) {
        $result = DB::select( "SELECT * FROM HAUS WHERE HAUS_AKTUELL='1' && HAUS_ID='$haus_id' ORDER BY HAUS_DAT DESC LIMIT 0,1" );
        $row = $result[0];
        $this->objekt_id = $row ['OBJEKT_ID'];
        $gg = new geldkonto_info ();
        $gg->geld_konto_ermitteln ( 'OBJEKT', $this->objekt_id );
        $this->get_objekt_name ( $this->objekt_id );
        $this->haus_strasse = $row ['HAUS_STRASSE'];
        $this->haus_nummer = $row ['HAUS_NUMMER'];
        $this->haus_plz = $row ['HAUS_PLZ'];
        $this->haus_stadt = $row ['HAUS_STADT'];
        $this->haus_qm = $row ['HAUS_QM'];
    }
    function liste_aller_haeuser() {
        $result = DB::select( "SELECT * FROM HAUS WHERE HAUS_AKTUELL='1' ORDER BY HAUS_STRASSE, HAUS_NUMMER ASC" );
        return $result;
    }
    function form_haus_aendern($haus_id) {
        $this->get_haus_info ( $haus_id );
        $f = new formular ();
        $f->erstelle_formular ( "Haus ändern - $this->objekt_name $this->haus_strasse $this->haus_nummer", NULL );

        $f->text_feld ( "Strasse", "strasse", "$this->haus_strasse", "50", 'strasse', '' );
        $f->text_feld ( "Hausnummer", "haus_nr", "$this->haus_nummer", "10", 'hausnr', '' );
        $f->text_feld ( "Ort", "ort", "$this->haus_stadt", "50", 'ort', '' );
        $f->text_feld ( "Plz", "plz", "$this->haus_plz", "10", 'plz', '' );
        $this->haus_qm_a = nummer_punkt2komma ( $this->haus_qm );
        $f->text_feld ( "Grösse in qm", "qm", "$this->haus_qm_a", "10", 'qm', '' );

        $o = new objekt ();
        $o->dropdown_objekte ( 'Objekt', objekt_id, $this->objekt_name );

        $f->hidden_feld ( "haus_id", "$haus_id" );
        $f->hidden_feld ( "haus_raus", "haus_aend_speichern" );
        $f->send_button ( "submit_haus", "Änderungen speichern" );

        $f->ende_formular ();
    }
    function form_haus_neu($objekt_id = '') {
        $f = new formular ();
        if ($objekt_id != '') {
            $o = new objekt ();
            $o->get_objekt_infos ( $objekt_id );
            if ($o->objekt_kurzname) {
                $f->erstelle_formular ( "Neues Haus im Objekt $o->objekt_kurzname erstellen", NULL );
                $f->text_feld ( "Strasse", "strasse", "", "50", 'strasse', '' );
                $f->text_feld ( "Hausnummer", "haus_nr", "", "10", 'hausnr', '' );
                $f->text_feld ( "Ort", "ort", "", "50", 'ort', '' );
                $f->text_feld ( "Plz", "plz", "", "10", 'plz', '' );
                $f->text_feld ( "Größe in m²", "qm", "", "10", 'qm', '' );
                $f->hidden_feld ( "objekt_id", "$objekt_id" );
                $f->hidden_feld ( "daten_rein", "haus_speichern" );
                $f->send_button ( "submit_haus", "Haus erstellen" );
            } else {
                echo "OBJEKT EXISTIERT NICHT";
            }
        } else {
            $f->erstelle_formular ( "Neues Haus erstellen", NULL );
            $f->text_feld ( "Strasse", "strasse", "", "50", 'strasse', '' );
            $f->text_feld ( "Hausnummer", "haus_nr", "", "10", 'hausnr', '' );
            $f->text_feld ( "Ort", "ort", "", "50", 'ort', '' );
            $f->text_feld ( "Plz", "plz", "", "10", 'plz', '' );
            $f->text_feld ( "Größe in m²", "qm", "", "10", 'qm', '' );
            $o = new objekt ();
            $this->dropdown_objekte ( 'objekt_id', 'objekt_id' );
            $f->hidden_feld ( "daten_rein", "haus_speichern" );
            $f->send_button ( "submit_haus", "Haus erstellen" );
        }
        $f->ende_formular ();
    }
    function haus_speichern($strasse, $haus_nr, $ort, $plz, $qm, $objekt_id) {
        $bk = new bk ();
        $last_id = $bk->last_id ( 'HAUS', 'HAUS_ID' ) + 1;
        /* Speichern */
        $db_abfrage = "INSERT INTO HAUS VALUES(NULL, '$last_id', '$strasse', '$haus_nr','$ort', '$plz', '$qm', '1', '$objekt_id')";
        DB::insert( $db_abfrage );

        /* Protokollieren */
        $last_dat = DB::getPdo()->lastInsertId();
        protokollieren ( 'HAUS', $last_dat, '0' );
        return $last_id;
    }
    function haus_deaktivieren($haus_id) {
        $db_abfrage = "UPDATE HAUS SET HAUS_AKTUELL='0' WHERE HAUS_ID='$haus_id'";
        DB::update( $db_abfrage );
        return true;
    }
    function haus_aenderung_in_db($strasse, $haus_nr, $ort, $plz, $qm, $objekt_id, $haus_id) {
        if ($this->haus_deaktivieren ( $haus_id ) == true) {

            /* Speichern */
            $db_abfrage = "INSERT INTO HAUS VALUES(NULL, '$haus_id', '$strasse', '$haus_nr','$ort', '$plz', '$qm', '1', '$objekt_id')";
            DB::insert( $db_abfrage );

            /* Protokollieren */
            $last_dat = DB::getPdo()->lastInsertId();
            protokollieren ( 'HAUS', $last_dat, '0' );
            return $last_id;
        } else {
            fehlermeldung_ausgeben ( "Haus konnte nicht geändert werden" );
        }
    }
    function get_qm_gesamt_gewerbe($haus_id) {
        $result = DB::select( "SELECT SUM(EINHEIT_QM) AS GESAMT_QM FROM `EINHEIT` WHERE `HAUS_ID` = '$haus_id' AND `EINHEIT_AKTUELL` ='1' && TYP='Gewerbe'" );
        $numrows = count( $result );
        if ($numrows) {
            $row = $result[0];
            if ($row ['GESAMT_QM'] != NULL) {
                return $row ['GESAMT_QM'];
            } else {
                return '0.00';
            }
        } else {
            return '0.00';
        }
    }
    function get_qm_gesamt($haus_id) {
        $result = DB::select( "SELECT SUM(EINHEIT_QM) AS GESAMT_QM FROM `EINHEIT` WHERE `HAUS_ID` = '$haus_id' AND `EINHEIT_AKTUELL` ='1' " );
        if (!empty($result)) {
            $row = $result[0];
            return $row ['GESAMT_QM'];
        } else {
            return '0.00';
        }
    }
    function dropdown_haeuser_2($label, $name, $id, $vorwahl = '') {
        $haus_arr = $this->liste_aller_haeuser ();
        echo "<div class=\"input-field\">";
        echo "<select name=\"$name\" size=1 id=\"$id\">\n";
        for($a = 0; $a < count ( $haus_arr ); $a ++) {
            $hh = new haus ();
            $haus_id = $haus_arr [$a] ['HAUS_ID'];
            $hh->get_haus_info ( $haus_id );
            $haus_str = $haus_arr [$a] ['HAUS_STRASSE'];
            $haus_nr = $haus_arr [$a] ['HAUS_NUMMER'];
            if ($vorwahl == $haus_id) {
                echo "<option value=\"$haus_id\" selected>$haus_str $haus_nr $hh->objekt_name</option>\n";
            } else {
                echo "<option value=\"$haus_id\">$haus_str $haus_nr $hh->objekt_name</option>\n";
            }
        }
        echo "</select><label for=\"$id\">$label</label>\n";
        echo "</div>";
    }
    function dropdown_haeuser($name, $id) {
        $haus_arr = $this->liste_aller_haeuser ();
        echo "<div class=\"input-field\">";
        echo "<select name=\"$name\" size=1 id=\"$id\">\n";
        for($a = 0; $a < count ( $haus_arr ); $a ++) {
            $hh = new haus ();
            $haus_id = $haus_arr [$a] ['HAUS_ID'];
            $hh->get_haus_info ( $haus_id );

            $haus_str = $haus_arr [$a] ['HAUS_STRASSE'];
            $haus_nr = $haus_arr [$a] ['HAUS_NUMMER'];
            echo "<option value=\"$haus_id\">$haus_str $haus_nr $hh->objekt_name</option>\n";
        }
        echo "</select><label for=\"$id\">Häuser</label>\n";
        echo "</div>";
    }
    function liste_aller_einheiten_im_haus($haus_id) {
        $result = DB::select( "SELECT * FROM EINHEIT WHERE EINHEIT_AKTUELL='1' && HAUS_ID='$haus_id' ORDER BY EINHEIT_KURZNAME ASC" );
        $this->anzahl_einheiten = count ( $result );
        return $result;
    }
    function get_haus_id($haus_name) {
        $haus_arr = explode ( ' ', $haus_name );
        $anzahl_el = count ( $haus_arr );
        $nr_el = $anzahl_el - 1;
        $hnr = $nr_el - 1;
        // settype($bar, "string");
        $haus_nr = $haus_arr [$nr_el];
        // $haus_nr = settype($haus_nr, "string");
        // ctype_digit($numeric_string); // true
        if (! ctype_digit ( $haus_nr )) {
            if (ctype_alnum ( $haus_nr )) {
                $haus_nr = $haus_arr [$hnr] . ' ' . $haus_nr;
                $nr_el = $nr_el - 1;
            } else {
                $haus_nr = $haus_arr [$nr_el];
            }
        }

        for($a = 0; $a < $nr_el; $a ++) {
            $haus_strasse = $haus_strasse . " $haus_arr[$a]";
        }
        $haus_strasse = ltrim ( rtrim ( $haus_strasse ) );
        $result = DB::select( "SELECT HAUS_ID FROM HAUS WHERE HAUS_AKTUELL='1' && HAUS_STRASSE='$haus_strasse' && HAUS_NUMMER='$haus_nr' ORDER BY HAUS_DAT DESC LIMIT 0,1" );

        $row = $result[0];
        $this->haus_id = $row ['HAUS_ID'];
    }
}