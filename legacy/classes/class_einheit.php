<?php

class einheit extends haus {
    /*
     * var $objekt_id;
     * var $objekt_name;
     * var $haus_id;
     * var $haus_strasse;
     * var $haus_nummer;
     * var $einheit_kurzname;
     * var $einheit_qm;
     * var $einheit_lage;
     * var $anzahl_einheiten;
     * var $haus_plz;
     * var $haus_stadt;
     * var $datum_heute;
     * var $mietvertrag_id;
     */
    public $einheit_qm_gewerbe;
    public $einheit_qm;
    public $einheit_kurzname;

    function emails_mieter_arr($objekt_id) {
        if ($objekt_id == null) {
            $db_abfrage = "SELECT OBJEKT_KURZNAME, HAUS_STRASSE, HAUS_NUMMER, `EINHEIT_KURZNAME` ,EINHEIT_ID,  `EINHEIT_LAGE` , `EINHEIT_QM`, `TYP` FROM EINHEIT , HAUS, OBJEKT
WHERE `EINHEIT_AKTUELL` = '1' && EINHEIT.HAUS_ID = HAUS.HAUS_ID && HAUS.OBJEKT_ID=OBJEKT.OBJEKT_ID && HAUS_AKTUELL='1' && OBJEKT_AKTUELL='1'
ORDER BY HAUS_STRASSE, HAUS_NUMMER, OBJEKT_KURZNAME, EINHEIT_LAGE";
        } else {
            $db_abfrage = "SELECT OBJEKT_KURZNAME, HAUS_STRASSE, HAUS_NUMMER, `EINHEIT_KURZNAME` ,EINHEIT_ID,  `EINHEIT_LAGE` , `EINHEIT_QM`, TYP FROM EINHEIT , HAUS, OBJEKT
				WHERE `EINHEIT_AKTUELL` = '1' && EINHEIT.HAUS_ID = HAUS.HAUS_ID && HAUS.OBJEKT_ID=OBJEKT.OBJEKT_ID && HAUS_AKTUELL='1' && OBJEKT_AKTUELL='1' && OBJEKT.OBJEKT_ID='$objekt_id'
				ORDER BY EINHEIT_KURZNAME";
        }
        $result = DB::select( $db_abfrage );
        $numrows = count( $result );
        if ($numrows) {
            $z = 0;
            $emails_arr = '';
            foreach( $result as $row ) {

                $einheit_id = $row ['EINHEIT_ID'];
                $mv_id = $this->get_mietvertrag_id ( $einheit_id );
                if ($mv_id) {
                    $mvs = new mietvertraege ();
                    $mvs->get_mietvertrag_infos_aktuell ( $mv_id );

                    $anz_p = count ( $mvs->personen_ids );
                    for($pp = 0; $pp < $anz_p; $pp ++) {
                        $p_id = $mvs->personen_ids [$pp] ['PERSON_MIETVERTRAG_PERSON_ID'];
                        $detail = new detail ();
                        if (($detail->finde_detail_inhalt ( 'PERSON', $p_id, 'Email' ))) {
                            $email_arr = $detail->finde_alle_details_grup ( 'PERSON', $p_id, 'Email' );
                            for($ema = 0; $ema < count ( $email_arr ); $ema ++) {
                                $em_adr = $email_arr [$ema] ['DETAIL_INHALT'];
                                $emails_arr [] = $em_adr;
                            }
                        }
                    }
                }
            }

            $emails_arr_u = array_values ( array_unique ( $emails_arr ) );
            unset ( $email_arr );
            unset ( $emails_arr );
            return $emails_arr_u;
        }
    }
    function uebersicht_einheit_leer($einheit_id) {
        $e = new einheit ();
        $e->get_einheit_info ( $einheit_id );
        // ################################## BALKEN EINHEIT---->
        echo "<div class=\"div balken1\"><span class=\"font_balken_uberschrift\">EINHEIT</span><hr />";
        echo "<span class=\"font_balken_uberschrift\">$e->einheit_kurzname</span><hr/>";
        echo "$e->haus_strasse $e->haus_nummer<br/>";
        echo "$e->haus_plz $e->haus_stadt<br/>";
        echo "Lage: $e->einheit_lage QM: $e->einheit_qm m²<hr/>";
        $details_info = new details ();
        $einheit_details_arr = $details_info->get_details ( 'EINHEIT', $einheit_id );
        if (count ( $einheit_details_arr ) > 0) {
            echo "<b>AUSSTATTUNG</b><hr>";
            for($i = 0; $i < count ( $einheit_details_arr ); $i ++) {
                echo "<b>" . $einheit_details_arr [$i] ['DETAIL_NAME'] . "</b>:<br>" . $einheit_details_arr [$i] ['DETAIL_INHALT'] . "<br>";
            }
        } else {
            echo "k.A zur Ausstattung";
        }
        $link_einheit_details = "<a href='" . route('legacy::details::index', ['option' => 'details_hinzu', 'detail_tabelle' => 'EINHEIT', 'detail_id' => $einheit_id]) . "'>NEUES DETAIL ZUR EINHEIT $e->einheit_kurzname</a>";
        echo "<hr>$link_einheit_details<hr>";
        $details_info = new details ();
        $objekt_details_arr = $details_info->get_details ( 'OBJEKT', $e->objekt_id );
        echo "<hr /><b>OBJEKT</b>: $e->objekt_name<hr/>";
        for($i = 0; $i < count ( $objekt_details_arr ); $i ++) {
            echo "<b>" . $objekt_details_arr [$i] ['DETAIL_NAME'] . "</b><br>" . $objekt_details_arr [$i] ['DETAIL_INHALT'] . "<br>";
        }
        $link_objekt_details = "<a href='" . route('legacy::details::index', ['option' => 'details_hinzu', 'detail_tabelle' => 'OBJEKT', 'detail_id' => $e->objekt_id]) . "'>NEUES DETAIL ZUM OBJEKT $e->objekt_name</a>";
        echo "<hr>$link_objekt_details<hr>";
        echo "</div>";
        // #ende spalte objekt und einheit####
    }
    function pdf_mieterliste($aktuell = 1, $objekt_id = null) {
        if ($objekt_id == null) {
            $db_abfrage = "SELECT OBJEKT_KURZNAME, HAUS_STRASSE, HAUS_NUMMER, `EINHEIT_KURZNAME` ,EINHEIT_ID,  `EINHEIT_LAGE` , `EINHEIT_QM`, `TYP` FROM EINHEIT , HAUS, OBJEKT
WHERE `EINHEIT_AKTUELL` = '1' && EINHEIT.HAUS_ID = HAUS.HAUS_ID && HAUS.OBJEKT_ID=OBJEKT.OBJEKT_ID && HAUS_AKTUELL='1' && OBJEKT_AKTUELL='1'
ORDER BY HAUS_STRASSE, HAUS_NUMMER, OBJEKT_KURZNAME, EINHEIT_LAGE";
        } else {
            $db_abfrage = "SELECT OBJEKT_KURZNAME, HAUS_STRASSE, HAUS_NUMMER, `EINHEIT_KURZNAME` ,EINHEIT_ID,  `EINHEIT_LAGE` , `EINHEIT_QM`, TYP FROM EINHEIT , HAUS, OBJEKT
WHERE `EINHEIT_AKTUELL` = '1' && EINHEIT.HAUS_ID = HAUS.HAUS_ID && HAUS.OBJEKT_ID=OBJEKT.OBJEKT_ID && HAUS_AKTUELL='1' && OBJEKT_AKTUELL='1' && OBJEKT.OBJEKT_ID='$objekt_id' 
ORDER BY EINHEIT_KURZNAME";
        }
        $result = DB::select( $db_abfrage );
        $numrows = count($result);
        if ($numrows) {
            $z = 0;
            foreach( $result as $row ) {
                $my_arr [] = $row;
                $einheit_id = $row ['EINHEIT_ID'];
                $mv_id = $this->get_mietvertrag_id ( $einheit_id );
                if ($mv_id) {
                    $mvs = new mietvertraege ();
                    $mvs->get_mietvertrag_infos_aktuell ( $mv_id );
                    $kontaktdaten = $this->kontaktdaten_mieter ( $mv_id );
                    $my_arr [$z] ['MIETER'] = $mvs->personen_name_string_u;
                    $my_arr [$z] ['KONTAKT'] = $kontaktdaten;
                } else {
                    $my_arr [$z] ['MIETER'] = 'Leerstand';
                }
                $z ++;
            }
        } else {
            echo "NO!sdsd";
        }

        $pdf = new Cezpdf ( 'a4', 'portrait' );
        $bpdf = new b_pdf ();
        $bpdf->b_header ( $pdf, 'Partner', session()->get('partner_id'), 'portrait', 'Helvetica.afm', 6 );
        $db_abfrage = "SELECT OBJEKT_KURZNAME, HAUS_STRASSE, HAUS_NUMMER, `EINHEIT_KURZNAME` ,EINHEIT_ID,  `EINHEIT_LAGE` , `EINHEIT_QM`, TYP FROM EINHEIT , HAUS, OBJEKT
WHERE `EINHEIT_AKTUELL` = '1' && EINHEIT.HAUS_ID = HAUS.HAUS_ID && HAUS.OBJEKT_ID=OBJEKT.OBJEKT_ID && HAUS_AKTUELL='1' && OBJEKT_AKTUELL='1'
ORDER BY OBJEKT_KURZNAME, HAUS_STRASSE, HAUS_NUMMER, EINHEIT_LAGE";
        $cols = array (
            'OBJEKT_KURZNAME' => "Objekt",
            'HAUS_STRASSE' => "Strasse",
            'HAUS_NUMMER' => 'Nr',
            'EINHEIT_KURZNAME' => 'Einheit',
            'TYP' => 'Typ',
            'EINHEIT_LAGE' => 'Lage',
            'EINHEIT_QM' => 'Fläche m²',
            'MIETER' => 'Mieterinfos',
            'MIETER' => 'Mieter',
            'KONTAKT' => 'Kontakt'
        );
        $pdf->ezTable ( $my_arr, $cols, "Alle Einheiten", array (
            'showHeadings' => 1,
            'shaded' => 1,
            'titleFontSize' => 8,
            'fontSize' => 7,
            'xPos' => 50,
            'xOrientation' => 'right',
            'width' => 500,
            'cols' => array (
                'OBJEKT' => array (
                    'justification' => 'left',
                    'width' => 65
                ),
                'HAUS_NUMMER' => array (
                    'justification' => 'right',
                    'width' => 30
                ),
                'EINHEIT_QM' => array (
                    'justification' => 'right',
                    'width' => 30
                )
            )
        ) );

        ob_end_clean();
        $pdf->ezStream();
    }
    function kontaktdaten_mieter($mv_id) {
        $result = DB::select( "SELECT PERSON_MIETVERTRAG_PERSON_ID FROM PERSON_MIETVERTRAG WHERE PERSON_MIETVERTRAG_MIETVERTRAG_ID='$mv_id' && PERSON_MIETVERTRAG_AKTUELL='1' ORDER BY PERSON_MIETVERTRAG_ID ASC" );
        $numrows = count( $result );
        if ($numrows) {
            $kontaktdaten = '';
            foreach( $result as $row ) {
                $person_id = $row ['PERSON_MIETVERTRAG_PERSON_ID'];
                $arr = $this->finde_detail_kontakt_arr ( 'PERSON', $person_id );
                if (is_array ( $arr )) {
                    $anz = count ( $arr );
                    for($a = 0; $a < $anz; $a ++) {
                        $dname = $arr [$a] ['DETAIL_NAME'];
                        $dinhalt = $arr [$a] ['DETAIL_INHALT'];
                        $kontaktdaten .= "<b>$dname</b>:$dinhalt ";
                    }
                }
            }
            return $kontaktdaten;
        }
    }
    function finde_detail_kontakt_arr($tab, $id) {
        $db_abfrage = "SELECT DETAIL_NAME, DETAIL_INHALT FROM DETAIL WHERE DETAIL_ZUORDNUNG_TABELLE = '$tab' && (DETAIL_NAME LIKE '%tel%'or DETAIL_NAME LIKE '%fax%' or DETAIL_NAME LIKE '%mobil%' or DETAIL_NAME LIKE '%handy%' OR DETAIL_NAME LIKE '%mail%') && DETAIL_ZUORDNUNG_ID = '$id' && DETAIL_AKTUELL = '1' ORDER BY DETAIL_NAME ASC";
        $resultat = DB::select( $db_abfrage );
        $numrows = count( $resultat );
        if ($numrows) {
            return $resultat;
        }
    }
    function get_einheit_info($einheit_id) {
        unset ( $this->einheit_dat );
        unset ( $this->typ );
        $result = DB::select( "SELECT * FROM EINHEIT WHERE EINHEIT_AKTUELL='1' && EINHEIT_ID='$einheit_id' ORDER BY EINHEIT_DAT DESC LIMIT 0,1" );
        $row = $result[0];
        $this->einheit_dat = $row ['EINHEIT_DAT'];
        $this->haus_id = $row ['HAUS_ID'];
        $this->einheit_kurzname = ltrim ( rtrim ( $row ['EINHEIT_KURZNAME'] ) );
        $this->einheit_qm = ltrim ( rtrim ( $row ['EINHEIT_QM'] ) );
        $this->einheit_qm_d = nummer_punkt2komma ( $this->einheit_qm );
        $this->einheit_lage = ltrim ( rtrim ( $row ['EINHEIT_LAGE'] ) );
        $this->get_haus_info ( $this->haus_id );
        $this->typ = $row ['TYP'];
        if ($this->typ == 'Gewerbe') {
            $this->einheit_qm_gewerbe = $this->einheit_qm;
        } else {
            $this->einheit_qm_gewerbe = 0.00;
        }

        $d = new detail ();
        $this->aufzug_prozent_d = $d->finde_detail_inhalt ( 'Einheit', $einheit_id, 'WEG-Aufzugprozent' );
        $this->aufzug_prozent = nummer_komma2punkt ( $this->aufzug_prozent_d );
    }
    function get_mietvertrag_id($einheit_id) {
        $this->datum_heute = date ( "Y-m-d" );
        $result = DB::select( "SELECT MIETVERTRAG_ID FROM MIETVERTRAG WHERE EINHEIT_ID = '$einheit_id' && MIETVERTRAG_AKTUELL = '1'  && ( MIETVERTRAG_BIS >= '$this->datum_heute' OR MIETVERTRAG_BIS = '0000-00-00' ) ORDER BY MIETVERTRAG_VON DESC LIMIT 0 , 1 " );
        $numrows = count( $result );

        if ($numrows > 0) {
            $row = $result[0];
            $this->mietvertrag_id = $row ['MIETVERTRAG_ID'];
            return $this->mietvertrag_id;
        } else {
            return false;
        }
    }
    function get_last_mietvertrag_id($einheit_id) {
        $result = DB::select( "SELECT MIETVERTRAG_ID FROM MIETVERTRAG WHERE EINHEIT_ID = '$einheit_id' && MIETVERTRAG_AKTUELL = '1'  ORDER BY MIETVERTRAG_VON DESC LIMIT 0 , 1 " );
        $numrows = count( $result );
        if ($numrows > 0) {
            $row = $result[0];
            $this->mietvertrag_id = $row ['MIETVERTRAG_ID'];
            return $this->mietvertrag_id;
        } else {
            return false;
        }
    }

    /* Alle Mietverträge einer Einheit */
    function get_mietvertrag_ids($einheit_id) {
        $result = DB::select( "SELECT MIETVERTRAG_ID FROM MIETVERTRAG WHERE EINHEIT_ID = '$einheit_id' && MIETVERTRAG_AKTUELL = '1' ORDER BY MIETVERTRAG_VON ASC" );
        if (!empty($result)) {
            return $result;
        } else {
            return false;
        }
    }
    function get_einheit_as_array($einheit_id) {
        $result = DB::select( "SELECT * FROM EINHEIT WHERE EINHEIT_AKTUELL='1' && EINHEIT_ID='$einheit_id' ORDER BY EINHEIT_DAT LIMIT 0,1" );
        $this->anzahl_einheiten = count($result);
        return $result;
    }
    function get_einheit_typ_arr() {
        $result = DB::select( "SHOW COLUMNS FROM EINHEIT WHERE FIELD = 'TYP'" );
        $row = $result[0];
        preg_match("/^enum\(\'(.*)\'\)$/", $row['Type'], $matches);
        $typ_array = explode("','", $matches[1]);
        return $typ_array;
    }

    /* Alle Mietverträge einer Einheit bis Monat(zweistellig*) Jahr(vierstellig) */
    function get_mietvertraege_bis($einheit_id, $jahr, $monat) {
        if (strlen ( $monat ) < 2) {
            $monat = '0' . $monat;
        }
        $result = DB::select( "SELECT MIETVERTRAG_ID FROM MIETVERTRAG WHERE EINHEIT_ID = '$einheit_id' && MIETVERTRAG_AKTUELL = '1' && DATE_FORMAT( MIETVERTRAG_VON, '%Y-%m' ) <= '$jahr-$monat' ORDER BY MIETVERTRAG_VON ASC" );
        $numrows = count( $result );
        if ($numrows) {
            return $result;
        } else {
            return false;
        }
    }

    /* Mietverträge einer Einheit im Monat(zweistellig*) Jahr(vierstellig) */
    function get_mietvertraege_zu($einheit_id, $jahr, $monat, $asc = 'ASC') {
        if (isset ( $this->mietvertrag_id )) {
            unset ( $this->mietvertrag_id );
        }

        if (strlen ( $monat ) < 2) {
            $monat = '0' . $monat;
        }
        $result = DB::select( "SELECT MIETVERTRAG_ID FROM MIETVERTRAG WHERE EINHEIT_ID = '$einheit_id' && MIETVERTRAG_AKTUELL = '1' && DATE_FORMAT( MIETVERTRAG_VON, '%Y-%m' ) <= '$jahr-$monat' && (DATE_FORMAT( MIETVERTRAG_BIS, '%Y-%m' ) >= '$jahr-$monat' OR MIETVERTRAG_BIS='0000-00-00') ORDER BY MIETVERTRAG_VON $asc LIMIT 0,1" );
        $numrows = count( $result );
        if ($numrows) {
            $row = $result[0];
            $this->mietvertrag_id = $row ['MIETVERTRAG_ID'];
            return $this->mietvertrag_id;
        } else {
            return false;
        }
    }
    function get_einheit_haus($einheit_id) {
        $result = DB::select( "SELECT HAUS_ID, EINHEIT_KURZNAME FROM EINHEIT WHERE EINHEIT_AKTUELL='1' && EINHEIT_ID='$einheit_id' ORDER BY EINHEIT_DAT DESC LIMIT 0,1" );
        $row = $result[0];
        $this->haus_id = $row ['HAUS_ID'];
        $this->get_haus_info ( $row ['HAUS_ID'] );
        $this->einheit_kurzname = $row ['EINHEIT_KURZNAME'];
        $this->get_einheit_info ( $einheit_id );
    }
    function get_einheit_id($einheit_name) {
        $result = DB::select( "SELECT EINHEIT_ID FROM EINHEIT WHERE EINHEIT_AKTUELL='1' && EINHEIT_KURZNAME='$einheit_name' ORDER BY EINHEIT_DAT DESC LIMIT 0,1" );
        $row = $result[0];
        $this->einheit_id = $row ['EINHEIT_ID'];
    }
    function get_einheit_status($einheit_id) {
        $this->datum_heute = date ( "Y-m-d" );
        $result = DB::select( "SELECT * FROM MIETVERTRAG WHERE EINHEIT_ID = '$einheit_id' && MIETVERTRAG_AKTUELL = '1' && ( MIETVERTRAG_BIS >= '$this->datum_heute' OR MIETVERTRAG_BIS = '0000-00-00' ) LIMIT 0 , 1 " );
        $numrows = count( $result );
        return !empty($result);
    }
    function liste_aller_einheiten() {
        $result = DB::select( "SELECT * FROM EINHEIT WHERE EINHEIT_AKTUELL='1' ORDER BY LENGTH(EINHEIT_KURZNAME), EINHEIT_KURZNAME" );
        $this->anzahl_einheiten = count ( $result );
        return $result;
    }
    function finde_einheit_id_by_kurz($anfang) {
        $result = DB::select( "SELECT EINHEIT_ID FROM EINHEIT WHERE EINHEIT_AKTUELL='1' && EINHEIT_KURZNAME LIKE '$anfang%' ORDER BY EINHEIT_DAT DESC LIMIT 0,1" );
        $row = $result[0];
        return $row ['EINHEIT_ID'];
    }
    function dropdown_einheiten($name, $id) {
        $einheiten_arr = $this->liste_aller_einheiten ();
        echo "<select name=\"$name\" size=1 id=\"$id\">\n";
        for($a = 0; $a < count ( $einheiten_arr ); $a ++) {
            $einheit_kurzname = $einheiten_arr [$a] ['EINHEIT_KURZNAME'];
            $einheit_id = $einheiten_arr [$a] ['EINHEIT_ID'];
            echo "<option value=\"$einheit_id\">$einheit_kurzname</option>\n";
        }
        echo "</select>\n";
    }
    function dropdown_einheit_typen($label, $name, $id, $vorwahl) {
        $arr = $this->get_einheit_typ_arr ();
        // print_r($arr);
        if (is_array ( $arr )) {
            echo "<label for=\"$id\">$label</label><select name=\"$name\" size=1 id=\"$id\">\n";
            $anz = count ( $arr );
            for($a = 0; $a < $anz; $a ++) {
                $typ = $arr [$a];
                if ($typ == $vorwahl) {
                    echo "<option value=\"$typ\" selected>$typ</option>\n";
                } else {
                    echo "<option value=\"$typ\">$typ</option>\n";
                }
            } // end for
            echo "</select>\n";
        } else {
            fehlermeldung_ausgeben ( "Keine Einheiten erfasst!" );
        }
    }
    function letzter_vormieter($einheit_id) {
        $datum_heute = date ( "Y-m-d" );
        $result = DB::select( "SELECT MIETVERTRAG_ID FROM `MIETVERTRAG` WHERE EINHEIT_ID = '$einheit_id' && MIETVERTRAG_AKTUELL='1' && ((MIETVERTRAG_BIS<'$datum_heute') && (MIETVERTRAG_BIS!='0000-00-00')) ORDER BY MIETVERTRAG_BIS DESC LIMIT 0,1" );
        $row = $result[0];
        $mietvertrag_id = $row ['MIETVERTRAG_ID'];
        $mv_info = new mietvertrag ();
        $vormieter_array = $mv_info->get_personen_ids_mietvertrag ( $mietvertrag_id );
        return $vormieter_array;
    }
    function form_einheit_neu($haus_id = '') {
        $f = new formular ();
        if ($haus_id != '') {
            $h = new haus ();
            $h->get_haus_info ( $haus_id );
            if ($h->haus_strasse) {
                $f->erstelle_formular ( "Neue Einheit im Haus $h->haus_strasse $h->haus_nummer erstellen", NULL );
                $f->text_feld ( "Kurzname", "kurzname", "", "50", 'kurzname', '' );
                $f->text_feld ( "Lage", "lage", "", "10", 'lage', '' );
                $f->text_feld ( "m²", "qm", "", "10", 'qm', '' );
                $this->dropdown_einheit_typen ( 'Typ', 'typ', 'typ', 'Wohnraum' );
                $f->hidden_feld ( "einheit_raus", "einheit_speichern" );
                $f->send_button ( "submit_einheit", "Einheit erstellen" );
                $f->hidden_feld ( "haus_id", "$haus_id" );
            } else {
                echo "OBJEKT EXISTIERT NICHT";
            }
        } else {
            $f->erstelle_formular ( "Neue Einheit erstellen", NULL );
            $f->text_feld ( "Kurzname", "kurzname", "", "50", 'kurzname', '' );
            $f->text_feld ( "Lage", "lage", "", "10", 'lage', '' );
            $f->text_feld ( "m²", "qm", "", "10", 'qm', '' );
            $h = new haus ();
            echo "<br>";
            $h = new haus ();
            $h->dropdown_haeuser_2 ( 'Haus wählen', 'haus_id', 'haus_id' );
            $this->dropdown_einheit_typen ( 'Typ', 'typ', 'typ', 'Wohnraum' );
            $f->hidden_feld ( "einheit_raus", "einheit_speichern" );
            $f->send_button ( "submit_einheit", "Einheit erstellen" );
        }
        $f->ende_formular ();
    }
    function form_einheit_aendern($einheit_id) {
        $e = new einheit ();
        $e->get_einheit_info ( $einheit_id );
        if (isset ( $e->einheit_dat )) {
            $f = new formular ();
            $f->erstelle_formular ( "Einheit ändern", NULL );
            $f->hidden_feld ( 'dat', $e->einheit_dat );
            $f->text_feld ( "Kurzname", "kurzname", "$e->einheit_kurzname", "50", 'kurzname', '' );
            $f->text_feld ( "Lage", "lage", "$e->einheit_lage", "30", 'lage', '' );
            $e->einheit_qm_k = nummer_punkt2komma ( $e->einheit_qm );
            $f->text_feld ( "m²", "qm", "$e->einheit_qm_k", "10", 'qm', '' );
            $h = new haus ();
            echo "<br>";
            $h = new haus ();
            $h->dropdown_haeuser_2 ( 'Haus wählen', 'haus_id', 'haus_id', $e->haus_id );
            $this->dropdown_einheit_typen ( 'Typ', 'typ', 'typ', $e->typ );
            // dropdown_einheit_typen($label, $name, $id, $vorwahl)
            $f->hidden_feld ( "einheit_raus", "einheit_speichern_ae" );
            $f->send_button ( "submit_einheit", "Änderung speichern" );
            $f->ende_formular ();
        } else {
            fehlermeldung_ausgeben ( "Einheit nicht vorhanden!" );
        }
    }
    function einheit_speichern($kurzname, $lage, $qm, $haus_id, $typ) {
        $last_id = last_id2 ( 'EINHEIT', 'EINHEIT_ID' ) + 1;
        $qm = nummer_komma2punkt ( $qm );
        $db_abfrage = "INSERT INTO EINHEIT VALUES (NULL, '$last_id', '$qm', '$lage', '$haus_id', '1', '$kurzname', '$typ')";
        DB::insert( $db_abfrage );
        /* Protokollieren */
        $last_dat = DB::getPdo()->lastInsertId();
        protokollieren ( 'EINHEIT', $last_dat, '0' );
        return $last_id;
    }
    function einheit_update($einheit_dat, $einheit_id, $kurzname, $lage, $qm, $haus_id, $typ) {
        $this->einheit_deaktivieren ( $einheit_dat );
        $last_id = $einheit_id;
        $qm = nummer_komma2punkt ( $qm );
        $db_abfrage = "INSERT INTO EINHEIT VALUES (NULL, '$last_id', '$qm', '$lage', '$haus_id', '1', '$kurzname', '$typ')";
        DB::insert( $db_abfrage );
        /* Protokollieren */
        $last_dat = DB::getPdo()->lastInsertId();
        protokollieren ( 'EINHEIT', $last_dat, $einheit_dat );
        return $last_dat;
    }
    function einheit_deaktivieren($einheit_dat) {
        $db_abfrage = "UPDATE EINHEIT SET EINHEIT_AKTUELL='0' WHERE EINHEIT_DAT='$einheit_dat'";
        DB::update( $db_abfrage );
        /* Protokollieren */
        protokollieren ( 'EINHEIT', $einheit_dat, $einheit_dat );
        return $einheit_dat;
    }
} // end class einheit