<?php

class mietvertraege
{
    var $datum_heute;
    var $akt_einheit_id;
    public $einheit_kurzname;
    public $mv_anrede;
    public $haus_stadt;
    public $haus_plz;
    public $haus_nr;
    public $haus_strasse;
    public $personen_name_string_u;
    public $postanschrift;
    public $mietvertrag_id;
    public $mietvertrag_aktuell;
    public $anz_verzugsanschriften;
    public $anz_zustellanschriften;
    public $einheit_typ;
    public $einheit_lage;
    public $personen_name_string;
    public $einheit_id;
    public $mietvertrag_bis_d;
    public $mietvertrag_von_d;
    public $ls_konto;
    public $ls_blz;
    public $objekt_id;
    public $objekt_kurzname;
    public $anzahl_personen;
    public $personen_ids;
    public $einheit_qm;
    public $mietvertrag_von;
    public $mietvertrag_dat;
    public $einheit_qm_d;
    public $haus_id;
    public $personen_name_string_u2;
    public $herr_frau;
    public $personen_anreden;
    public $mietvertrag_bis;
    public $alle_teilnehmer;
    public $ls_einzugsermaechtigung;
    public $ls_einzugsermaechtigung_dat;
    public $ls_autoeinzugsart;
    public $ls_autoeinzugsart_dat;
    public $ls_konto_inhaber;
    public $ls_konto_inhaber_dat;
    public $ls_konto_nummer;
    public $ls_konto_nummer_dat;
    public $ls_blz_dat;
    public $ls_bankname;
    public $ls_bankname_dat;
    public $ls_bankname_sep;
    public $ls_bankname_sep_k;
    public $ls_iban;
    public $ls_bic;
    public $serie;
    public $ls_iban1;
    public $einheit_zimmeranzahl;

    protected $namen;

    function neuer_mv_form()
    {
        $this->datum_heute = date("d.m.Y");
        $link = route('web::mietvertraege::legacy', ['mietvertrag_raus' => 'mietvertrag_neu'], false);
        $this->objekt_auswahl_liste($link);
        $form = new mietkonto ();
        $form->erstelle_formular("Neuen Mietvertrag erstellen", NULL);
        if (session()->has('objekt_id')) {
            $this->dropdown_leerstaende(session()->get('objekt_id'), 'einheit_id', 'Einheit auswählen', 'dropdown_leerstand');
            $form->text_feld('Einzugsdatum', 'datum_einzug', $this->datum_heute, '10');
            $form->text_feld('Auszugsdatum', 'datum_auszug', '', '10');
            $javaaction = 'onchange="mieter_auswaehlen()"';
            $this->dropdown_personen_liste('Mieter auswählen', 'alle_mieter_list', 'alle_mieter_list', $javaaction);
            $javaaction1 = 'onchange="mieter_entfernen()"';
            $this->ausgewahlte_mieter_liste('Ausgewählte Mieter', 'mieter_liste[]', 'mieter_liste', $javaaction1, '5');
            $form->text_feld('Miete kalt', 'miete_kalt', '', '10');
            $form->text_feld('Sollkaution', 'sollkaution', '', '10');
            $form->text_feld('Nebenkosten Vorauszahlung', 'nebenkosten', '', '10');
            $form->text_feld('Heizkosten Vorauszahlung', 'heizkosten', '', '10');
            $form->hidden_feld('mietvertrag_raus', 'mv_pruefen');
            $sendbutton_js = "onclick=\"alle_mieter_auswaehlen()\"";
            $form->send_button_js('btn_mv_erstellen', 'Mietvertrag erstellen', $sendbutton_js);
        } else {
            echo "<h1>KEINE LEERSTÄNDE IM OBJEKT</h1>";
        }
        $form->ende_formular();
    }

    function objekt_auswahl_liste($link)
    {
        $bg = new berlussimo_global ();
        $bg->objekt_auswahl_liste();
    }

    function dropdown_leerstaende($objekt_id, $name, $label, $id)
    {
        $leerstand = $this->leerstand_finden($objekt_id);
        if (!empty($leerstand)) {
            echo "<label for=\"$id\">$label</label><select name=\"$name\" id=\"$id\">";
            for ($a = 0; $a < count($leerstand); $a++) {
                $einheit_id = $leerstand [$a] ['EINHEIT_ID'];
                $einheit_kurzname = $leerstand [$a] ['EINHEIT_KURZNAME'];
                echo "<option value=\"$einheit_id\">$einheit_kurzname</option>";
            }
            echo "</select>";
            return true;
        } else {
            return false;
        }
    }

    function leerstand_finden($objekt_id)
    {
        $result = DB::select("SELECT OBJEKT_KURZNAME, EINHEIT_ID, EINHEIT_KURZNAME, HAUS_STRASSE, HAUS_NUMMER
FROM `EINHEIT`
RIGHT JOIN (
HAUS, OBJEKT
) ON ( EINHEIT.HAUS_ID = HAUS.HAUS_ID && HAUS.objekt_id = OBJEKT.objekt_id && OBJEKT.objekt_id='$objekt_id' )
WHERE EINHEIT_ID NOT
IN (

SELECT EINHEIT_ID
FROM MIETVERTRAG
WHERE MIETVERTRAG_AKTUELL = '1' && ( MIETVERTRAG_BIS > CURdate( )
OR MIETVERTRAG_BIS = '0000-00-00' )
) && EINHEIT_AKTUELL='1'
GROUP BY EINHEIT_ID ORDER BY EINHEIT_KURZNAME ASC");
        return $result;
    }

    function dropdown_personen_liste($label, $name, $id, $javaaction)
    {
        $db_abfrage = "SELECT PERSON_ID, PERSON_NACHNAME, PERSON_VORNAME, PERSON_GEBURTSTAG FROM PERSON WHERE PERSON_AKTUELL='1' ORDER BY PERSON_NACHNAME, PERSON_VORNAME ASC";
        $personen = DB::select($db_abfrage);
        $numrows = count($personen);
        echo "<div class='input-field'>";
        echo "<select name=\"$name\" id=\"$id\" $javaaction>";
        for ($a = 0; $a < $numrows; $a++) {
            $person_id = $personen [$a] ['PERSON_ID'];
            $vorname = $personen [$a] ['PERSON_VORNAME'];
            $nachname = $personen [$a] ['PERSON_NACHNAME'];
            echo "<option value=\"$person_id\">$nachname $vorname</option>";
        }
        echo "</select><label>$label</label>";
        echo "</div>";
    }

    function ausgewahlte_mieter_liste($label, $name, $id, $javaaction, $size)
    {
        echo "<div class='input-field'>";
        echo "<select name=\"$name\" id=\"$id\" $javaaction size=\"$size\" multiple>";
        echo "</select><label>$label</label>";
        echo "</div>";
    }

    function mv_aendern_formular($mietvertrag_id)
    {
        $form = new mietkonto ();
        $form->erstelle_formular("Mietvertrag ändern", NULL);
        $this->datum_heute = date("d.m.Y");

        $this->get_mietvertrag_infos_aktuell($mietvertrag_id);
        $this->mietvertrag_von = date_mysql2german($this->mietvertrag_von);
        $this->mietvertrag_bis = date_mysql2german($this->mietvertrag_bis);

        $form->hidden_feld('einheit_id', $this->akt_einheit_id);
        $form->hidden_feld('mietvertrag_id', $mietvertrag_id);
        $form->hidden_feld('mietvertrag_dat', $this->mietvertrag_dat);
        $einheit_name = einheit_kurzname($this->akt_einheit_id);
        $form->text_feld_inaktiv('Einheit', 'einheit_name', $einheit_name, '10');
        $form->text_feld('Einzugsdatum', 'datum_einzug', $this->mietvertrag_von, '10');
        if ($this->mietvertrag_bis == '00.00.0000') {
            $form->text_feld('Auszugsdatum', 'datum_auszug', '', '10');
        } else {
            $form->text_feld('Auszugsdatum', 'datum_auszug', $this->mietvertrag_bis, '10');
        }
        $javaaction = 'onchange="mieter_auswaehlen()"';
        $this->dropdown_personen_liste('Mieter auswählen', 'alle_mieter_list', 'alle_mieter_list', $javaaction);
        $mieter_arr = $form->get_personen_ids_mietvertrag($mietvertrag_id);
        $this->ausgewahlte_mieter_liste_aendern('Ausgewählte Mieter', 'mieter_liste[]', 'mieter_liste', null, '5', $mieter_arr);

        $form->hidden_feld('mietvertrag_raus', 'mv_aenderung_pruefen');
        $form->send_button_js('btn_mv_updaten', 'Mietvertrag ändern', null);
        $form->ende_formular();
    }

    function get_mietvertrag_infos_aktuell($mietvertrag_id)
    {
        unset ($this->mietvertrag_aktuell);
        $datum_heute = date("Y-m-d");
        $result = DB::select("SELECT MIETVERTRAG_VON, MIETVERTRAG_BIS, MIETVERTRAG_DAT, MIETVERTRAG.EINHEIT_ID, EINHEIT_KURZNAME, OBJEKT.OBJEKT_ID, OBJEKT_KURZNAME, HAUS.HAUS_ID, HAUS_STRASSE, HAUS_NUMMER, HAUS_PLZ,HAUS_STADT FROM MIETVERTRAG RIGHT JOIN(EINHEIT,HAUS,OBJEKT) ON (MIETVERTRAG.EINHEIT_ID = EINHEIT.EINHEIT_ID && EINHEIT.HAUS_ID=HAUS.HAUS_ID && HAUS.OBJEKT_ID=OBJEKT.OBJEKT_ID) WHERE MIETVERTRAG_AKTUELL='1' && EINHEIT_AKTUELL='1' &&  HAUS_AKTUELL='1' && OBJEKT_AKTUELL='1' && MIETVERTRAG_ID='$mietvertrag_id'  ORDER BY MIETVERTRAG_DAT DESC LIMIT 0,1");
        if (!empty($result)) {
            $row = $result[0];

            $this->akt_einheit_id = $row ['EINHEIT_ID'];
            $ee = new einheit ();
            $ee->get_einheit_info($this->akt_einheit_id);
            $this->einheit_lage = $ee->einheit_lage;
            $this->einheit_typ = $ee->typ;
            $this->einheit_qm = $ee->einheit_qm;
            $this->einheit_qm_d = $ee->einheit_qm_d;
            $this->einheit_id = $row ['EINHEIT_ID'];
            $d = new detail();
            $this->einheit_zimmeranzahl = $d->finde_detail_inhalt('EINHEIT', $this->einheit_id, 'Zimmeranzahl');
            $this->mietvertrag_von = $row ['MIETVERTRAG_VON'];
            $this->mietvertrag_von_d = date_mysql2german($this->mietvertrag_von);
            $this->mietvertrag_bis = $row ['MIETVERTRAG_BIS'];
            $this->mietvertrag_bis_d = date_mysql2german($this->mietvertrag_bis);
            $this->mietvertrag_dat = $row ['MIETVERTRAG_DAT'];
            $this->mietvertrag_id = $mietvertrag_id;
            $this->einheit_kurzname = $row ['EINHEIT_KURZNAME'];
            $this->objekt_id = $row ['OBJEKT_ID'];
            $this->objekt_kurzname = $row ['OBJEKT_KURZNAME'];
            $this->haus_id = $row ['HAUS_ID'];
            $this->haus_strasse = $row ['HAUS_STRASSE'];
            $this->haus_nr = $row ['HAUS_NUMMER'];
            $this->haus_plz = $row ['HAUS_PLZ'];
            $this->haus_stadt = $row ['HAUS_STADT'];
            $m = new mietvertrag ();
            $this->personen_ids = $m->get_personen_ids_mietvertrag($mietvertrag_id);
            $this->anzahl_personen = count($this->personen_ids);
            $this->personen_name_string = ltrim(rtrim($this->mv_personen_als_string($this->personen_ids)));
            $this->personen_name_string_u = ltrim(rtrim($this->mv_personen_als_string_u($this->personen_ids)));
            $this->personen_name_string_u2 = ltrim(rtrim($this->mv_personen_als_string_u2($this->personen_ids)));

            if (isset ($this->mietvertrag_aktuell)) {
                unset ($this->mietvertrag_aktuell);
            }

            if ($this->mietvertrag_bis == '0000-00-00' or datum_kleiner($datum_heute, $this->mietvertrag_bis)) {
                $this->mietvertrag_aktuell = '1'; // hiermiet ist nicht die Tabelle gemeint, sondern aktueller Mieter oder ex.Mieter
            } else {
                $this->mietvertrag_aktuell = '0';
            }

            /* Array für Verzugsanschriften der Mieter löschen */
            if (isset ($this->postanschrift)) {
                unset ($this->postanschrift);
            }

            /* ANREDE */
            $d = new detail ();
            $p = new person ();
            if ($this->anzahl_personen == 1) {
                $p->get_person_infos($this->personen_ids ['0'] ['PERSON_MIETVERTRAG_PERSON_ID']);
                $geschlecht = $d->finde_person_geschlecht($this->personen_ids [0] ['PERSON_MIETVERTRAG_PERSON_ID']);
                if ($geschlecht == 'weiblich') {
                    $anrede_p = 'geehrte Frau';
                    $namen = "Frau\n$p->person_nachname $p->person_vorname";
                    $this->herr_frau = 'Frau';
                }
                if ($geschlecht == 'männlich') {
                    $anrede_p = 'geehrter Herr';
                    $namen = "Herr\n$p->person_nachname $p->person_vorname";
                    $this->herr_frau = 'Herr';
                }
                if (!empty ($anrede_p)) {
                    $this->mv_anrede = "Sehr $anrede_p $p->person_nachname,\n";
                } else {
                    $this->mv_anrede = "Sehr geehrte Damen und Herren,\n";
                }

                if ($geschlecht != 'männlich' && $geschlecht != 'weiblich') {
                    $anrede_p = 'geehrte Damen und Herren';
                    $this->mv_anrede = "Sehr $anrede_p,\n";
                    $namen = "PPPPP";
                    $this->herr_frau = '';
                }

                $personen_anrede [0] ['anrede'] = $this->mv_anrede;
                $personen_anrede [0] ['geschlecht'] = $geschlecht;
                $personen_anrede [0] ['namen'] = $namen;
                // prinr_r($mv->personen_ids);
            }

            if ($this->anzahl_personen > 1) {

                for ($a = 0; $a < $this->anzahl_personen; $a++) {
                    $p->get_person_infos($this->personen_ids [$a] ['PERSON_MIETVERTRAG_PERSON_ID']);
                    $geschlecht = $d->finde_person_geschlecht($this->personen_ids [$a] ['PERSON_MIETVERTRAG_PERSON_ID']);
                    if ($geschlecht == 'weiblich') {
                        $anrede_p = 'geehrte Frau';
                        $namen = "Frau $p->person_nachname $p->person_vorname";
                        $this->mv_anrede = "$anrede_p $p->person_nachname,";
                    }
                    if ($geschlecht == 'männlich') {
                        $anrede_p = 'geehrter Herr';
                        $namen = "Herr $p->person_nachname $p->person_vorname";
                        $this->mv_anrede = "$anrede_p $p->person_nachname,";
                    }

                    if ($geschlecht != 'männlich' && $geschlecht != 'weiblich') {
                        $anrede_p = 'geehrte Damen und Herren';
                        $this->mv_anrede = "$anrede_p,\n";
                        $namen = "";
                    }

                    // $this->mv_anrede = "$anrede_p $p->person_nachname,";
                    $personen_anrede [$a] ['anrede'] = $this->mv_anrede;
                    $personen_anrede [$a] ['geschlecht'] = $geschlecht;
                    $personen_anrede [$a] ['namen'] = $namen;
                }
            }

            if ($this->anzahl_personen > 1) {
                $personen_anreden = array_sortByIndex($personen_anrede, 'geschlecht', SORT_DESC);
                $this->personen_anreden = $personen_anreden;

                for ($b = 0; $b < $this->anzahl_personen; $b++) {
                    $anrede_p = $personen_anreden [$b] ['anrede'];
                    $namen_n = $personen_anreden [$b] ['namen'];
                    $this->namen .= "$namen_n\n";
                    if ($b < 1) {
                        $this->mv_anrede = "Sehr $anrede_p\n";
                    } else {
                        $this->mv_anrede .= "sehr $anrede_p\n"; // \n neue zeile in pdf
                    }
                }
            } else {
                $this->namen = "$namen";
            }
        } else {
            $this->personen_name_string = 'UNBEKANNT';
            // echo "Mietvertrag ID:$mietvertrag_id exisitiert nicht";
        }

        /* VERZUGS und ZUSTELLADRESSEN FELD Anschrift aus Detail */
        /* Wenn ex Mieter, dann Verzugsanschrift suchen */
        // if($this->mietvertrag_aktuell == '0'){
        $this->anz_zustellanschriften = 0;
        $this->anz_verzugsanschriften = 0;
        for ($a = 0; $a < $this->anzahl_personen; $a++) {
            $p = new personen ();
            $de = new detail ();
            $p->get_person_infos($this->personen_ids [$a] ['PERSON_MIETVERTRAG_PERSON_ID']);
            // $geschlecht = $de->finde_person_geschlecht($this->personen_ids[$a]['PERSON_MIETVERTRAG_PERSON_ID']);
            $geschlecht = $p->geschlecht;

            if ($geschlecht == 'weiblich') {
                // $this->postanschrift[$a]['anrede'] = "Sehr geehrte Frau $p->person_nachname,\n";
                // $this->postanschrift[$a]['name'] = "Frau\n$p->person_vorname $p->person_nachname";
            }

            if ($geschlecht == 'männlich') {
                // $this->postanschrift[$a]['anrede'] = "Sehr geehrter Herr $p->person_nachname,\n";
                // $this->postanschrift[$a]['name'] = "Herr\n$p->person_vorname $p->person_nachname";
            }

            if ($geschlecht != 'männlich' && $geschlecht != 'weiblich') {
                // $this->postanschrift[$a]['anrede'] = "Sehr geehrte Damen und Herren,\n";
                // $this->postanschrift[$a]['name'] = "$p->person_vorname $p->person_nachname";
                // $this->herr_frau = '';
            }

            // $namen = $this->postanschrift[$a]['name'];

            /* Wenn ausgezogener Mieter, dann nach Verzugsanschrift suchen, sonst Zustellanschrift */
            if ($this->mietvertrag_aktuell == '0') {
                if ($de->finde_detail_inhalt('PERSON', $this->personen_ids [$a] ['PERSON_MIETVERTRAG_PERSON_ID'], 'Verzugsanschrift') == true) {
                    $this->postanschrift [$a] ['anschrift'] = str_replace('<br />', "\n", $de->finde_detail_inhalt('PERSON', $this->personen_ids [$a] ['PERSON_MIETVERTRAG_PERSON_ID'], 'Verzugsanschrift'));
                    $this->postanschrift [$a] ['adresse'] = str_replace('<br />', "\n", $de->finde_detail_inhalt('PERSON', $this->personen_ids [$a] ['PERSON_MIETVERTRAG_PERSON_ID'], 'Verzugsanschrift'));
                    $this->anz_verzugsanschriften++;
                } else {
                    if ($de->finde_detail_inhalt('PERSON', $this->personen_ids [$a] ['PERSON_MIETVERTRAG_PERSON_ID'], 'Zustellanschrift') == true) {
                        $this->postanschrift [$a] ['anschrift'] = str_replace('<br />', "\n", $de->finde_detail_inhalt('PERSON', $this->personen_ids [$a] ['PERSON_MIETVERTRAG_PERSON_ID'], 'Zustellanschrift'));
                        $this->postanschrift [$a] ['adresse'] = str_replace('<br />', "\n", $de->finde_detail_inhalt('PERSON', $this->personen_ids [$a] ['PERSON_MIETVERTRAG_PERSON_ID'], 'Zustellanschrift'));
                        $this->anz_zustellanschriften++;
                    } else {
                        // $this->postanschrift[$a]['anschrift'] = "$namen";
                    }
                }
            } else {

                if ($de->finde_detail_inhalt('PERSON', $this->personen_ids [$a] ['PERSON_MIETVERTRAG_PERSON_ID'], 'Zustellanschrift') == true) {
                    $this->postanschrift [$a] ['anschrift'] = str_replace('<br />', "\n", $de->finde_detail_inhalt('PERSON', $this->personen_ids [$a] ['PERSON_MIETVERTRAG_PERSON_ID'], 'Zustellanschrift'));
                    $this->postanschrift [$a] ['adresse'] = str_replace('<br />', "\n", $de->finde_detail_inhalt('PERSON', $this->personen_ids [$a] ['PERSON_MIETVERTRAG_PERSON_ID'], 'Zustellanschrift'));
                    $this->anz_zustellanschriften++;
                } else {
                    // $this->postanschrift[$a]['anschrift'] = "$namen\n$this->haus_strasse $this->haus_nr\n<b>$this->haus_plz $this->haus_stadt</b>";
                    // $this->postanschrift[$a]['adresse'] = "$namen\n$this->haus_strasse $this->haus_nr\n<b>$this->haus_plz $this->haus_stadt</b>";
                    $this->serie = 1;
                }
            }
        } // end for
        // }

        /* Lastschriftdaten holen */
        // $this->ls_daten_holen($mietvertrag_id);
        // echo '<pre>';
        // print_r($this);
    }

    function mv_personen_als_string($arr)
    {
        // print_r($arr);
        $mieter_anzahl = count($arr);
        $mystring = "";
        for ($a = 0; $a < $mieter_anzahl; $a++) {
            $person_id = $arr [$a] ['PERSON_MIETVERTRAG_PERSON_ID'];
            $db_abfrage = "SELECT PERSON_NACHNAME, PERSON_VORNAME FROM PERSON WHERE PERSON_AKTUELL='1' && PERSON_ID='$person_id' LIMIT 0,1";
            $result = DB::select($db_abfrage);

            if ($mieter_anzahl == '1') {
                $row = $result[0];
                $nn = ltrim(rtrim($row ['PERSON_NACHNAME']));
                $vn = ltrim(rtrim($row ['PERSON_VORNAME']));
                $mystring = "$vn $nn";
            }
            if ($mieter_anzahl > '1') {
                $l_mieter = $mieter_anzahl - 1;
                $row = $result[0];
                $nn = ltrim(rtrim($row ['PERSON_NACHNAME']));
                $vn = ltrim(rtrim($row ['PERSON_VORNAME']));
                if ($a == $l_mieter) {
                    $mystring .= "$vn $nn";
                }
                if ($a < $l_mieter) {
                    $mystring .= "$vn $nn, ";
                }
            }
        }
        return $mystring;
    }

    function mv_personen_als_string_u($arr)
    {
        $mieter_anzahl = count($arr);
        $mystring = "";
        for ($a = 0; $a < $mieter_anzahl; $a++) {
            $person_id = $arr [$a] ['PERSON_MIETVERTRAG_PERSON_ID'];
            $db_abfrage = "SELECT PERSON_NACHNAME, PERSON_VORNAME FROM PERSON WHERE PERSON_AKTUELL='1' && PERSON_ID='$person_id' LIMIT 0,1";
            $result = DB::select($db_abfrage);

            if ($mieter_anzahl == '1') {
                $row = $result[0];
                $nn = ltrim(rtrim($row ['PERSON_NACHNAME']));
                $vn = ltrim(rtrim($row ['PERSON_VORNAME']));
                $d = new detail ();
                $geschlecht = $d->finde_person_geschlecht($person_id);
                if ($geschlecht == 'weiblich') {
                    $herr_frau = "Frau";
                }
                if ($geschlecht == 'männlich') {
                    $herr_frau = "Herr";
                }
                if (!empty ($herr_frau)) {
                    $mystring = "$herr_frau \n$vn $nn";
                } else {
                    $mystring = "$vn $nn";
                }
            }
            if ($mieter_anzahl > '1') {
                $l_mieter = $mieter_anzahl - 1;
                $row = $result[0];
                $nn = ltrim(rtrim($row ['PERSON_NACHNAME']));
                $vn = ltrim(rtrim($row ['PERSON_VORNAME']));

                $d = new detail ();
                $geschlecht = $d->finde_person_geschlecht($person_id);
                if ($geschlecht == 'weiblich') {
                    $herr_frau = 'Frau ';
                }
                if ($geschlecht == 'männlich') {
                    $herr_frau = 'Herr ';
                }

                if (!isset ($herr_frau)) {
                    $herr_frau = '';
                }

                if ($a == $l_mieter) {
                    $mystring .= "$herr_frau$vn $nn";
                }
                if ($a < $l_mieter) {
                    $mystring .= "$herr_frau$vn $nn\n";
                }
            }
        }
        return $mystring;
    }

    function mv_personen_als_string_u2($arr)
    {
        $mieter_anzahl = count($arr);
        $mystring = "";
        for ($a = 0; $a < $mieter_anzahl; $a++) {
            $person_id = $arr [$a] ['PERSON_MIETVERTRAG_PERSON_ID'];
            $db_abfrage = "SELECT PERSON_NACHNAME, PERSON_VORNAME FROM PERSON WHERE PERSON_AKTUELL='1' && PERSON_ID='$person_id' LIMIT 0,1";
            $result = DB::select($db_abfrage);

            if ($mieter_anzahl == '1') {
                $row = $result[0];
                $nn = ltrim(rtrim($row ['PERSON_NACHNAME']));
                $vn = ltrim(rtrim($row ['PERSON_VORNAME']));
                $d = new detail ();
                $geschlecht = $d->finde_person_geschlecht($person_id);
                if ($geschlecht == 'weiblich') {
                    $herr_frau = "Frau";
                }
                if ($geschlecht == 'männlich') {
                    $herr_frau = "Herr";
                }
                if (!empty ($herr_frau)) {
                    $mystring = "$herr_frau \n$vn, $nn";
                } else {
                    $mystring = "$vn, $nn";
                }
            }

            if ($mieter_anzahl > '1') {
                $l_mieter = $mieter_anzahl - 1;
                $row = $result[0];
                $nn = ltrim(rtrim($row ['PERSON_NACHNAME']));
                $vn = ltrim(rtrim($row ['PERSON_VORNAME']));

                $d = new detail ();
                $geschlecht = $d->finde_person_geschlecht($person_id);
                if ($geschlecht == 'weiblich') {
                    $herr_frau = 'Frau ';
                }
                if ($geschlecht == 'männlich') {
                    $herr_frau = 'Herr ';
                }

                if (!isset ($herr_frau)) {
                    $herr_frau = '';
                }

                if ($a == $l_mieter) {
                    $mystring .= "$herr_frau$vn, $nn";
                }
                if ($a < $l_mieter) {
                    $mystring .= "$herr_frau$vn, $nn - ";
                }
            }
        }
        return $mystring;
    }

    /* Kommagetrent */

    function ausgewahlte_mieter_liste_aendern($label, $name, $id, $javaaction, $size, $mieter_arr)
    {
        $person_info = new person ();
        echo "<label for=\"$id\">$label</label><select name=\"$name\" id=\"$id\" $javaaction size=\"$size\" style='visibility:visible;' MULTIPLE>";
        for ($a = 0; $a < count($mieter_arr); $a++) {
            $person_id = $mieter_arr [$a] ['PERSON_MIETVERTRAG_PERSON_ID'];
            $person_info->get_person_infos($person_id);

            echo "<option selected value=\"$person_id\">$person_info->person_nachname $person_info->person_vorname</option>";
        }
        echo "</select>";
    }

    /* Untereinander */

    function mietvertrag_beenden_form($mietvertrag_id)
    {
        $form = new formular ();
        $this->get_mietvertrag_infos_aktuell($mietvertrag_id);
        echo "Mieter: $this->personen_name_string_u";
        $form->hidden_feld('mietvertrag_id', $mietvertrag_id);
        $form->hidden_feld('mietvertrag_dat', $this->mietvertrag_dat);
        $form->hidden_feld('einheit_id', $this->einheit_id);
        $form->hidden_feld('einheit_kurzname', $this->einheit_kurzname);
        $form->hidden_feld('mietvertrag_von', $this->mietvertrag_von);

        $form->text_feld_inaktiv('Einheit', 'einheit_name', $this->einheit_kurzname, '10', '');
        $form->text_feld('Mietvertragsende eintragen', 'mietvertrag_bis', '', '10', 'mietvertrag_bis', '');
        $form->text_bereich('Verzugsanschrift', 'verzugsanschrift', '', '10', '10', 'verzugsanschrift', '');
        $form->hidden_feld("mietvertrag_raus", "mietvertrag_beenden_gesendet");
        $form->send_button_js('btn_mv_beenden', 'Mietvertrag beenden', '');
        // text_bereich($beschreibung, $name, $wert, $cols, $rows, $id)
    }

    /* Untereinander */

    function mv_personen_anzeigen_form($arr)
    {
        for ($a = 0; $a < count($arr); $a++) {
            $person_id = $arr [$a];
            $db_abfrage = "SELECT PERSON_NACHNAME, PERSON_VORNAME FROM PERSON WHERE PERSON_AKTUELL='1' && PERSON_ID='$person_id' LIMIT 0,1";
            $result = DB::select($db_abfrage);
            if (!empty($result)) {
                $row = $result[0];
                $nn = $row ['PERSON_NACHNAME'];
                $vn = $row ['PERSON_VORNAME'];
                echo "$nn $vn<br>";
            }
        }
    }

    function mieten_speichern($mv_id, $anfang, $ende, $kostenkat, $betrag, $mwst = 0)
    {
        /* Neue Zeile */
        $form = new mietkonto ();
        $anfang = $form->date_german2mysql($anfang);
        $ende = $form->date_german2mysql($ende);
        $betrag = $form->nummer_komma2punkt($betrag);
        $me_id = $form->get_mietentwicklung_last_id();
        $me_id = $me_id + 1;
        if ($mwst == 1) {
            $mwst_betrag = $betrag / 119 * 19;
        } else {
            $mwst_betrag = 0.00;
        }
        $db_abfrage = "INSERT INTO MIETENTWICKLUNG VALUES (NULL, '$me_id', 'MIETVERTRAG', '$mv_id', '$kostenkat', '$anfang', '$ende', '$mwst_betrag', '$betrag', '1')";
        DB::insert($db_abfrage);
        /* Zugewiesene MIETBUCHUNG_DAT auslesen */
        $last_dat = DB::getPdo()->lastInsertId();
        protokollieren('MIETENTWICKLUNG', '0', $last_dat);
    }

    function teilnahme_einzugsverfahren_eingeben($mv_id, $konto_inh, $konto_nr, $blz, $bankname, $art, $ja_nein)
    {
        // echo "<h1>JA NEIN $ja_nein</h1>";

        /* Einzugsermächtigung */
        $last_id = last_id('DETAIL');
        $last_id = $last_id + 1;
        $db_abfrage = "INSERT INTO DETAIL VALUES (NULL, '$last_id', 'Einzugsermächtigung', '$ja_nein', '', '1', 'MIETVERTRAG', '$mv_id')";
        DB::insert($db_abfrage);
        $last_dat = DB::getPdo()->lastInsertId();
        protokollieren('DETAIL', $last_dat, '0');

        /* Einzugsart */
        $last_id = last_id('DETAIL');
        $last_id = $last_id + 1;
        $db_abfrage = "INSERT INTO DETAIL VALUES (NULL, '$last_id', 'Autoeinzugsart', '$art', '', '1', 'MIETVERTRAG', '$mv_id')";
        DB::insert($db_abfrage);
        $last_dat = DB::getPdo()->lastInsertId();
        protokollieren('DETAIL', $last_dat, '0');

        /* Kontoinhaber-AutoEinzug */
        $last_id = last_id('DETAIL');
        $last_id = $last_id + 1;
        $db_abfrage = "INSERT INTO DETAIL VALUES (NULL, '$last_id', 'Kontoinhaber-AutoEinzug', '$konto_inh', '', '1', 'MIETVERTRAG', '$mv_id')";
        DB::insert($db_abfrage);
        $last_dat = DB::getPdo()->lastInsertId();
        protokollieren('DETAIL', $last_dat, '0');

        /* Kontonummer-AutoEinzug */
        $last_id = last_id('DETAIL');
        $last_id = $last_id + 1;
        $db_abfrage = "INSERT INTO DETAIL VALUES (NULL, '$last_id', 'Kontonummer-AutoEinzug', '$konto_nr', '', '1', 'MIETVERTRAG', '$mv_id')";
        DB::insert($db_abfrage);
        $last_dat = DB::getPdo()->lastInsertId();
        protokollieren('DETAIL', $last_dat, '0');

        /* BLZ-AutoEinzug */
        $last_id = last_id('DETAIL');
        $last_id = $last_id + 1;
        $db_abfrage = "INSERT INTO DETAIL VALUES (NULL, '$last_id', 'BLZ-AutoEinzug', '$blz', '', '1', 'MIETVERTRAG', '$mv_id')";
        DB::insert($db_abfrage);
        $last_dat = DB::getPdo()->lastInsertId();
        protokollieren('DETAIL', $last_dat, '0');

        /* Bankname-AutoEinzug */
        $last_id = last_id('DETAIL');
        $last_id = $last_id + 1;
        $db_abfrage = "INSERT INTO DETAIL VALUES (NULL, '$last_id', 'Bankname-AutoEinzug', '$bankname', '', '1', 'MIETVERTRAG', '$mv_id')";
        DB::insert($db_abfrage);
        $last_dat = DB::getPdo()->lastInsertId();
        protokollieren('DETAIL', $last_dat, '0');
        echo "Teilnahme am Lastschriftverfahren wurde vermerkt";
    }

    function person_zu_mietvertrag($person_id, $mietvertrag_id)
    {
        $letzte_pm_id = letzte_person_mietvertrag_id();
        $letzte_pm_id = $letzte_pm_id + 1;
        $dat_alt = letzte_person_mietvertrag_dat_by_mietvertrags_id($person_id, $mietvertrag_id);
        $db_abfrage = "INSERT INTO PERSON_MIETVERTRAG (`PERSON_MIETVERTRAG_DAT`, `PERSON_MIETVERTRAG_ID`, `PERSON_MIETVERTRAG_PERSON_ID`, `PERSON_MIETVERTRAG_MIETVERTRAG_ID`, `PERSON_MIETVERTRAG_AKTUELL`) VALUES (NULL, '$letzte_pm_id', '$person_id', '$mietvertrag_id', '1')";
        DB::insert($db_abfrage);
        $dat_neu = letzte_person_mietvertrag_dat_by_mietvertrags_id($person_id, $mietvertrag_id);
        protokollieren('PERSON_MIETVERTRAG', $dat_neu, $dat_alt);
        return $letzte_pm_id;
    }

    function mietvertrag_speichern($von, $bis, $einheit_id)
    {
        $akt_mietvertrag_id = mietvertrag_id_letzte();
        $akt_mietvertrag_id = $akt_mietvertrag_id + 1;
        $von = date_german2mysql($von);
        $bis = date_german2mysql($bis);
        $dat_alt = letzte_mietvertrag_dat_of_mietvertrag_id($akt_mietvertrag_id);
        $db_abfrage = "INSERT INTO MIETVERTRAG (`MIETVERTRAG_DAT`, `MIETVERTRAG_ID`, `MIETVERTRAG_VON`, `MIETVERTRAG_BIS`, `EINHEIT_ID`, `MIETVERTRAG_AKTUELL`) VALUES (NULL, '$akt_mietvertrag_id', '$von', '$bis', '$einheit_id', '1')";
        DB::insert($db_abfrage);
        $dat_neu = letzte_mietvertrag_dat_of_mietvertrag_id($akt_mietvertrag_id);
        protokollieren('MIETVERTRAG', $dat_neu, $dat_alt);
        return $akt_mietvertrag_id;
    }

    function check_auszug($mv_id)
    {
        $this->get_mietvertrag_infos_aktuell($mv_id);

        if ($this->mietvertrag_aktuell == '1') {
            if ($this->mietvertrag_bis == '0000-00-00') {
                return false;
            } else {
                $bis_arr = explode('-', $this->mietvertrag_bis);
                $jahr = $bis_arr [0];
                $monat = $bis_arr [1];
                $tag = $bis_arr [2];
                $serial_bis = "$jahr$monat$tag";
                $serial_heute = date("Ymd");

                if ($serial_bis >= $serial_heute) {
                    return true;
                } else {
                    return false;
                }
            }
        } else {
            return true;
        }
    }

    /* Prüft ob der Mietvertrag beendet wird/wurde, bzw. der Mieter auszieht */

    function mv_aenderungen_speichern($mietvertrag_dat, $mietvertrag_id_alt, $mietvertrag_bis, $mietvertrag_von, $einheit_id, $person_arr)
    {
        $mietvertrag_bis = date_german2mysql($mietvertrag_bis);
        $mietvertrag_von = date_german2mysql($mietvertrag_von);
        $db_abfrage = "UPDATE MIETVERTRAG SET MIETVERTRAG_AKTUELL='0' where MIETVERTRAG_DAT='$mietvertrag_dat'";
        DB::update($db_abfrage); // aktuell auf 0 gesetzt
        protokollieren('MIETVERTRAG', $mietvertrag_dat, $mietvertrag_dat);

        $db_abfrage = "UPDATE PERSON_MIETVERTRAG SET PERSON_MIETVERTRAG_AKTUELL='0' where PERSON_MIETVERTRAG_MIETVERTRAG_ID='$mietvertrag_id_alt'";
        DB::update($db_abfrage); // personen zu MV gelöscht bzw auf 0 gesetzt

        // ####################ende der deaktivierung mv und person->mv############
        $db_abfrage = "INSERT INTO MIETVERTRAG (`MIETVERTRAG_DAT`, `MIETVERTRAG_ID`, `MIETVERTRAG_VON`, `MIETVERTRAG_BIS`, `EINHEIT_ID`, `MIETVERTRAG_AKTUELL`) VALUES (NULL, '$mietvertrag_id_alt', '$mietvertrag_von', '$mietvertrag_bis', '$einheit_id', '1')";
        DB::insert($db_abfrage);
        // protokollieren
        $last_dat = DB::getPdo()->lastInsertId();
        protokollieren('MIETVERTRAG', $last_dat, $mietvertrag_dat);

        $anzahl_partner = count($person_arr);
        for ($a = 0; $a < $anzahl_partner; $a++) {
            $person_id = $person_arr [$a];
            person_zu_mietvertrag($person_id, $mietvertrag_id_alt);
        }
        hinweis_ausgeben("Mietvertrag wurde geändert");
        weiterleiten_in_sec(route('web::uebersicht::legacy', ['anzeigen' => 'einheit', 'einheit_id' => $einheit_id], false), "2");
    }

    function ls_akt_teilnehmer()
    {
        $link = route('web::mietvertraege::legacy', ['mietvertrag_raus' => 'ls_teilnehmer'], false);
        $this->objekt_auswahl_liste($link);
        $form = new mietkonto ();
        $form->erstelle_formular("Aktuelle Teilnehmer am Lastschriftverfahren", NULL);

        $teilnehmer_arr = $this->ls_akt_teilnehmer_arr();
        $anzahl_tln = count($teilnehmer_arr);
        if ($anzahl_tln > 0) {
            echo "<table>";
            echo "<tr><th>Objekt $teilnehmer_arr[OBJEKT_KURZNAME]</th></tr>";
            echo "</table>";

            echo "<table class=\"sortable\">";
            echo "<tr><th>EINHEIT</th><th>MIETER</th><th>OPTIONEN</th></tr>";

            $zaehler = 0;
            for ($a = 0; $a < $anzahl_tln; $a++) {
                $zaehler++;
                $mv_id = $teilnehmer_arr [$a] ['MV_ID'];
                $einheit_kurzname = $teilnehmer_arr [$a] ['EINHEIT_KURZNAME'];
                $anzahl_mieter = $teilnehmer_arr [$a] ['MIETER_ANZAHL'];
                $erster_mieter = $teilnehmer_arr [$a] ['MIETER'] [0] ['NACHNAME'] . ' ' . $teilnehmer_arr [$a] ['MIETER'] [0] ['VORNAME'];
                $link_deaktivieren = "<a href='" . route('web::mietvertraege::legacy', ['mietvertrag_raus' => 'ls_teilnehmer_deaktivieren', 'mietvertrag_id' => $mv_id]) . "'>Deaktivieren</a>";

                if ($anzahl_mieter == 1) {
                    echo "<tr class=\"zeile$zaehler\"><td>$einheit_kurzname</td><td>$erster_mieter</td><td>$link_deaktivieren</td></tr>";
                }
                if ($anzahl_mieter > 1) {
                    echo "<tr class=\"zeile$zaehler\"><td>$einheit_kurzname</td><td>";
                    for ($b = 0; $b < $anzahl_mieter; $b++) {
                        echo $teilnehmer_arr [$a] ['MIETER'] [$b] ['NACHNAME'] . ' ' . $teilnehmer_arr [$a] ['MIETER'] [$b] ['VORNAME'] . '<br>';
                    }
                    echo "</td><td>$link_deaktivieren</td></tr>";
                }
                if ($zaehler == 2) {
                    $zaehler = 0;
                }
            } // end for
            echo "</table>";
        } else {
            echo "Keine LS Teilnehmer";
        }
        $form->ende_formular();
    }

    function ls_akt_teilnehmer_arr()
    {
        $this->alle_teilnehmer = $this->mietvertrag_einzugsverfahren_arr();
        if (session()->has('objekt_id')) {
            $teilnehmer_arr_z = 0;
            for ($a = 0; $a < count($this->alle_teilnehmer); $a++) {
                $mietvertrag_id = $this->alle_teilnehmer [$a] ['MIETVERTRAG_ID'];
                $this->get_mietvertrag_infos_aktuell($mietvertrag_id);
                if ($this->objekt_id == session()->get('objekt_id')) {
                    $mietvertrag_info = new mietvertrag();
                    $teilnehmer_objekt_arr ['objekt_id'] = $this->objekt_id;
                    $teilnehmer_objekt_arr ['OBJEKT_KURZNAME'] = $this->objekt_kurzname;

                    $teilnehmer_objekt_arr [$teilnehmer_arr_z] ['MV_ID'] = $this->mietvertrag_id;
                    $teilnehmer_objekt_arr [$teilnehmer_arr_z] ['EINHEIT_KURZNAME'] = $this->einheit_kurzname;
                    $teilnehmer_objekt_arr [$teilnehmer_arr_z] ['EINHEIT_ID'] = $this->einheit_id;

                    $teilnehmer_objekt_arr [$teilnehmer_arr_z] ['PERSONEN_IDS'] = $mietvertrag_info->get_personen_ids_mietvertrag($mietvertrag_id);
                    $teilnehmer_objekt_arr [$teilnehmer_arr_z] ['MIETER_ANZAHL'] = count($teilnehmer_objekt_arr [$teilnehmer_arr_z] ['PERSONEN_IDS']);
                    for ($i = 0; $i < count($teilnehmer_objekt_arr [$teilnehmer_arr_z] ['PERSONEN_IDS']); $i++) {
                        $d = new detail ();
                        $konto_inhaber_autoeinzug = $d->finde_detail_inhalt('MIETVERTRAG', $mietvertrag_id, 'Kontoinhaber-AutoEinzug');
                        $teilnehmer_objekt_arr [$teilnehmer_arr_z] ['MIETER'] [$i] ['VORNAME'] = $konto_inhaber_autoeinzug;
                    }
                    $teilnehmer_arr_z++;
                }
            }
        }  // ende if !empty SESSION objekt_id
        else {
            hinweis_ausgeben("Objekt auswählen");
        }
        /* Nach Einheit sortieren */
        if (isset ($teilnehmer_objekt_arr)) {
            $teilnehmer_objekt_arr = array_sortByIndex($teilnehmer_objekt_arr, 'EINHEIT_KURZNAME');
            // echo "<pre>";
            // print_r($teilnehmer_objekt_arr);
            return $teilnehmer_objekt_arr;
        }
    }

    function mietvertrag_einzugsverfahren_arr()
    {
        $result = DB::select("SELECT DETAIL_ZUORDNUNG_ID AS MIETVERTRAG_ID FROM `DETAIL` LEFT JOIN(MIETVERTRAG) ON (DETAIL_ZUORDNUNG_ID=MIETVERTRAG_ID) 
WHERE DETAIL_NAME = 'Einzugsermächtigung' && DETAIL_INHALT='JA' && DETAIL_ZUORDNUNG_TABELLE = 'MIETVERTRAG' && DETAIL_AKTUELL = '1' && MIETVERTRAG_AKTUELL = '1' && (MIETVERTRAG_BIS='0000-00-00' OR MIETVERTRAG_BIS>=CURDATE())");
        if (!empty($result)) {
            return $result;
        } else {
            return false;
        }
    }

    /* Funktion zur Ermittlung der Mietverträge die am Einzugsverfahren teilnehmen */

    function ls_akt_teilnehmer_ausgesetzt()
    {
        $link = route('web::mietvertraege::legacy', ['mietvertrag_raus' => 'ls_teilnehmer_inaktiv', false]);
        $this->objekt_auswahl_liste($link);
        $form = new mietkonto ();
        $form->erstelle_formular("Inaktive Teilnehmer am Lastschriftverfahren", NULL);

        $teilnehmer_arr = $this->ls_akt_teilnehmer_arr_ausgesetzt();
        $anzahl_tln = count($teilnehmer_arr);
        if ($anzahl_tln > 0) {
            echo "<table>";
            echo "<tr><th>Objekt $teilnehmer_arr[OBJEKT_KURZNAME]</th></tr>";
            echo "</table>";

            echo "<table class=\"sortable\">";
            echo "<tr><th>EINHEIT</th><th>MIETER</th><th>OPTIONEN</th></tr>";

            $zaehler = 0;
            for ($a = 0; $a < $anzahl_tln; $a++) {
                $mv_id = $teilnehmer_arr [$a] ['MV_ID'];
                $einheit_kurzname = $teilnehmer_arr [$a] ['EINHEIT_KURZNAME'];
                $anzahl_mieter = $teilnehmer_arr [$a] ['MIETER_ANZAHL'];
                $erster_mieter = $teilnehmer_arr [$a] ['MIETER'] [0] ['NACHNAME'] . ' ' . $teilnehmer_arr [$a] ['MIETER'] [0] ['VORNAME'];
                $link_aktivieren = "<a href='" . route('web::mietvertraege::legacy', ['mietvertrag_raus' => 'ls_teilnehmer_aktivieren', 'mietvertrag_id' => $mv_id]) . "'>Aktivieren</a>";
                $zaehler++;

                if ($anzahl_mieter == 1) {
                    echo "<tr class=\"zeile$zaehler\"><td>$einheit_kurzname</td><td>$erster_mieter</td><td>$link_aktivieren</td></tr>";
                }
                if ($anzahl_mieter > 1) {
                    echo "<tr class=\"zeile$zaehler\"><td>$einheit_kurzname</td><td>";
                    for ($b = 0; $b < $anzahl_mieter; $b++) {
                        echo $teilnehmer_arr [$a] ['MIETER'] [$b] ['NACHNAME'] . ' ' . $teilnehmer_arr [$a] ['MIETER'] [$b] ['VORNAME'] . '<br>';
                    }
                    echo "</td><td>$link_aktivieren</td></tr>";
                }
                if ($zaehler == 2) {
                    $zaehler = 0;
                }
            } // end for
            echo "</table>";
        } else {
            echo "Keine LS teilnehmer";
        }
        $form->ende_formular();
    }

    /* Funktion zur Ermittlung der Mietverträge die am Einzugsverfahren teilnahmen bzw. momentan auf NEIN stehen */

    function ls_akt_teilnehmer_arr_ausgesetzt()
    {
        $this->alle_teilnehmer = $this->mietvertrag_einzugsverfahren_arr_ausgesetzt();
        if (session()->has('objekt_id')) {
            $teilnehmer_arr_z = 0;
            for ($a = 0; $a < count($this->alle_teilnehmer); $a++) {
                $mietvertrag_id = $this->alle_teilnehmer [$a] ['MIETVERTRAG_ID'];
                $this->get_mietvertrag_infos_aktuell($mietvertrag_id);
                if ($this->objekt_id == session()->get('objekt_id')) {
                    $mietvertrag_info = new mietvertrag ();
                    $person_info = new person ();

                    $teilnehmer_objekt_arr ['objekt_id'] = $this->objekt_id;
                    $teilnehmer_objekt_arr ['OBJEKT_KURZNAME'] = $this->objekt_kurzname;

                    // $teilnehmer_arr_z ist der arrayzähler
                    $teilnehmer_objekt_arr [$teilnehmer_arr_z] ['MV_ID'] = $this->mietvertrag_id;
                    $teilnehmer_objekt_arr [$teilnehmer_arr_z] ['EINHEIT_KURZNAME'] = $this->einheit_kurzname;
                    $teilnehmer_objekt_arr [$teilnehmer_arr_z] ['EINHEIT_ID'] = $this->einheit_id;

                    $teilnehmer_objekt_arr [$teilnehmer_arr_z] ['PERSONEN_IDS'] = $mietvertrag_info->get_personen_ids_mietvertrag($mietvertrag_id);
                    $teilnehmer_objekt_arr [$teilnehmer_arr_z] ['MIETER_ANZAHL'] = count($teilnehmer_objekt_arr [$teilnehmer_arr_z] ['PERSONEN_IDS']);
                    for ($i = 0; $i < count($teilnehmer_objekt_arr [$teilnehmer_arr_z] ['PERSONEN_IDS']); $i++) {
                        $person_info->get_person_infos($teilnehmer_objekt_arr [$teilnehmer_arr_z] ['PERSONEN_IDS'] [$i] ['PERSON_MIETVERTRAG_PERSON_ID']);
                        $teilnehmer_objekt_arr [$teilnehmer_arr_z] ['MIETER'] [] ['NACHNAME'] = $person_info->person_nachname;
                        $teilnehmer_objekt_arr [$teilnehmer_arr_z] ['MIETER'] [$i] ['VORNAME'] = $person_info->person_vorname;
                    }
                    $teilnehmer_arr_z++;
                }
            }
        }  // ende if !empty SESSION objekt_id
        else {
            hinweis_ausgeben("Objekt auswählen");
        }
        /* Nach Einheit sortieren */

        $teilnehmer_objekt_arr = array_sortByIndex($teilnehmer_objekt_arr, 'EINHEIT_KURZNAME');
        // echo "<pre>";
        // print_r($teilnehmer_objekt_arr);
        return $teilnehmer_objekt_arr;
    }

    /* Liste als Array der aktuellen LS-Teilnehmer d.h. nur aktuelle Mietverträge */

    function mietvertrag_einzugsverfahren_arr_ausgesetzt()
    {
        $result = DB::select("SELECT DETAIL_ZUORDNUNG_ID AS MIETVERTRAG_ID FROM `DETAIL` LEFT JOIN(MIETVERTRAG) ON (DETAIL_ZUORDNUNG_ID=MIETVERTRAG_ID) 
WHERE DETAIL_NAME = 'Einzugsermächtigung' && DETAIL_INHALT='NEIN' && DETAIL_ZUORDNUNG_TABELLE = 'MIETVERTRAG' && DETAIL_AKTUELL = '1' && MIETVERTRAG_AKTUELL = '1' && (MIETVERTRAG_BIS='0000-00-00' OR MIETVERTRAG_BIS>=CURDATE())");

        if (!empty($result)) {
            return $result;
        } else {
            return false;
        }
    }

    /* Ausgabe der LS-Teilnehmer */

    function teilnehmer_aktivieren($mv_id)
    {
        $db_abfrage = "UPDATE DETAIL SET DETAIL_INHALT='JA' where DETAIL_NAME='Einzugsermächtigung' && DETAIL_ZUORDNUNG_TABELLE='MIETVERTRAG' && DETAIL_ZUORDNUNG_ID='$mv_id'";
        DB::update($db_abfrage);
        hinweis_ausgeben("Teilnahme zum Lastschriftverfahren aufgenommen.");
    } // end function

    // ################### ANFANG AUSGESETZTE TLN (NEIN)####################
    /* Liste als Array der aktuell ausgesetzten LS-Teilnehmer d.h. nur aktuelle Mietverträge mit NEIN */

    function teilnehmer_deaktivieren($mv_id)
    {
        $db_abfrage = "UPDATE DETAIL SET DETAIL_INHALT='NEIN' where DETAIL_NAME='Einzugsermächtigung' && DETAIL_ZUORDNUNG_TABELLE='MIETVERTRAG' && DETAIL_ZUORDNUNG_ID='$mv_id'";
        DB::update($db_abfrage);
        hinweis_ausgeben("Teilnahme zum Lastschriftverfahren ausgesetzt.");
    }

    /* Ausgabe der ausgesetzten LS-Teilnehmer bzw alle mit NEIN in DETAIL */

    function neuer_ls_teilnehmer()
    {
        $link = route('web::mietvertraege::legacy', ['mietvertrag_raus' => 'ls_teilnehmer_neu'], false);
        $this->objekt_auswahl_liste($link);

        if (!request()->has('mietvertrag_id')) {
            $this->einheiten_liste($link);
        } else {

            $form = new mietkonto ();

            $form->mieter_infos_vom_mv(request()->input('mietvertrag_id'));

            if ($this->ls_daten_vorhanden(request()->input('mietvertrag_id') == false)) {
                $form->erstelle_formular("Teilnehmer hinzufügen", NULL);
                $form->hidden_feld('mietvertrag_id', request()->input('mietvertrag_id'));
                $this->autoeinzugsarten('Einzugsart', 'einzugsart', 'einzugsart');
                $form->text_feld('Kontoinhaber', 'konto_inhaber_autoeinzug', '', '40');
                $form->text_feld('Kontonummer', 'konto_nummer_autoeinzug', '', '20');
                $form->text_feld('BLZ', 'blz_autoeinzug', '', '20');
                $form->text_feld('Geldinstitut', 'geld_institut', '', '20');
                $form->hidden_feld('mietvertrag_raus', 'ls_pruefen');
                $form->send_button('btn_ls_daten_neu', 'Weiter');
                $form->ende_formular();
            } else {
                $form->erstelle_formular("Teilnehmer bearbeiten", NULL);
                $this->ls_daten_holen(request()->input('mietvertrag_id'));
                $form->hidden_feld('mietvertrag_id', request()->input('mietvertrag_id'));
                $this->dropdown_ja_nein('Einzugsermächtigung erteilt', 'einzugsermaechtigung', $this->ls_einzugsermaechtigung);
                $form->hidden_feld('deaktiviere_dat[]', $this->ls_einzugsermaechtigung_dat);

                $this->dropdown_autoeinzug_selected('Einzugsart', 'einzugsart', $this->ls_autoeinzugsart);
                $form->hidden_feld('deaktiviere_dat[]', $this->ls_autoeinzugsart_dat);

                $form->text_feld('Kontoinhaber', 'konto_inhaber_autoeinzug', $this->ls_konto_inhaber, '40');
                $form->hidden_feld('deaktiviere_dat[]', $this->ls_konto_inhaber_dat);

                $form->text_feld('Kontonummer', 'konto_nummer_autoeinzug', $this->ls_konto_nummer, '20');
                $form->hidden_feld('deaktiviere_dat[]', $this->ls_konto_nummer_dat);

                $form->text_feld('BLZ', 'blz_autoeinzug', $this->ls_blz, '20');
                $form->hidden_feld('deaktiviere_dat[]', $this->ls_blz_dat);

                $form->text_feld('Geldinstitut', 'geld_institut', $this->ls_bankname, '20');
                $form->hidden_feld('deaktiviere_dat[]', $this->ls_bankname_dat);

                $form->hidden_feld('mietvertrag_raus', 'ls_pruefen');
                $form->send_button('btn_ls_daten_neu', 'Weiter');
                $form->ende_formular();
            }
        }
    } // end function

    function einheiten_liste($link)
    {
        $mieten = new mietkonto ();
        echo "<div class=\"einheit_auswahl\">";
        $mieten->erstelle_formular("Vermietete Einheit auswählen...", NULL);

        /* Liste der Einheiten falls Objekt ausgewählt wurde */
        if (session()->has('objekt_id')) {
            $objekt_id = session()->get('objekt_id');
            $mein_objekt = new objekt ();
            $liste_haeuser = $mein_objekt->haeuser_objekt_in_arr($objekt_id);

            for ($i = 0; $i < count($liste_haeuser); $i++) {
                $result = DB::select("SELECT * FROM EINHEIT WHERE EINHEIT_AKTUELL='1' && HAUS_ID='" . $liste_haeuser [$i] ['HAUS_ID'] . "' ORDER BY EINHEIT_KURZNAME ASC");
                foreach ($result as $row)
                    $einheiten_array [] = $row;
            }
        } else {
            /* Liste aller Einheiten da kein Objekt ausgewählt wurde */
            $meine_einheiten = new einheit ();
            $einheiten_array = $meine_einheiten->liste_aller_einheiten();
        }
        // Beispiel für ein Array $sx mit den Spalten $sx['dat'], $sx['name'], $sx['id'].

        $einheiten_array = array_sortByIndex($einheiten_array, 'EINHEIT_KURZNAME');
        $counter = 0;
        $spaltencounter = 0;
        echo "<table>";
        echo "<tr><td valign=\"top\">";
        $einheit_info = new einheit ();
        for ($i = 0; $i <= count($einheiten_array); $i++) {
            $einheit_info->get_mietvertrag_id("" . $einheiten_array [$i] ['EINHEIT_ID'] . "");
            $einheit_vermietet = $einheit_info->get_einheit_status("" . $einheiten_array [$i] ['EINHEIT_ID'] . "");
            if ($einheit_vermietet) {
                $intern_link = "<a href=\"$link&mietvertrag_id=" . $einheit_info->mietvertrag_id . "\" class=\"nicht_gebucht_links\">" . $einheiten_array [$i] ['EINHEIT_KURZNAME'] . "</a>&nbsp;";
                echo "$intern_link";
                echo "<br>"; // Nach jeder Einheit Neuzeile
                $counter++;
            }
            if ($counter == 10) {
                echo "</td><td valign=\"top\">";
                $counter = 0;
                $spaltencounter++;
            }
            if ($spaltencounter == 4) {
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

    function ls_daten_vorhanden($mv_id)
    {
        $result = DB::select("SELECT * FROM DETAIL WHERE DETAIL_NAME LIKE '%AutoEinzug' && DETAIL_ZUORDNUNG_TABELLE='MIETVERTRAG' && DETAIL_ZUORDNUNG_ID='$mv_id' && DETAIL_AKTUELL='1'");
        return !empty($result);
    }

    function autoeinzugsarten($label, $name, $id)
    {
        echo "<div class='input-field'>";
        echo "<select name=\"$name\" id=\"$id\">";
        echo "<option value=\"Aktuelles Saldo komplett\">Aktuelles Saldo komplett</option>";
        echo "<option value=\"Nur die Summe aus Vertrag\">Nur die Summe aus Vertrag</option>";
        echo "<option value=\"Ratenzahlung\">Ratenzahlung</option>";
        echo "</select><label for=\"$id\">$label</label>";
        echo "</div>";
    }

    function ls_daten_holen($mv_id)
    {
        unset ($this->ls_konto_inhaber_dat);
        unset ($this->ls_konto_inhaber);
        unset ($this->ls_konto_nummer_dat);
        unset ($this->ls_blz_dat);
        unset ($this->ls_blz);
        unset ($this->ls_bankname_dat);
        unset ($this->ls_bankname);
        unset ($this->ls_autoeinzugsart_dat);
        unset ($this->ls_autoeinzugsart);
        unset ($this->ls_einzugsermaechtigung_dat);
        unset ($this->ls_einzugsermaechtigung);
        unset ($this->ls_iban);
        unset ($this->ls_iban1);
        unset ($this->ls_bic);
        unset ($this->ls_bankname_sep);
        unset ($this->ls_bankname_sep_k);

        /* Kontoinhaber holen */
        $result = DB::select("SELECT DETAIL_DAT, DETAIL_INHALT FROM DETAIL WHERE DETAIL_NAME='Kontoinhaber-AutoEinzug' && DETAIL_ZUORDNUNG_TABELLE='MIETVERTRAG' && DETAIL_ZUORDNUNG_ID='$mv_id' && DETAIL_AKTUELL='1'");
        $row = $result[0];
        $this->ls_konto_inhaber_dat = $row ['DETAIL_DAT'];
        $this->ls_konto_inhaber = $row ['DETAIL_INHALT'];
        /* Kontonummer holen */
        $result = DB::select("SELECT DETAIL_DAT, DETAIL_INHALT FROM DETAIL WHERE DETAIL_NAME='Kontonummer-AutoEinzug' && DETAIL_ZUORDNUNG_TABELLE='MIETVERTRAG' && DETAIL_ZUORDNUNG_ID='$mv_id' && DETAIL_AKTUELL='1'");
        $row = $result[0];
        $this->ls_konto_nummer_dat = $row ['DETAIL_DAT'];
        $this->ls_konto_nummer = $row ['DETAIL_INHALT'];
        /* BLZ holen */
        $result = DB::select("SELECT DETAIL_DAT, DETAIL_INHALT FROM DETAIL WHERE DETAIL_NAME='BLZ-AutoEinzug' && DETAIL_ZUORDNUNG_TABELLE='MIETVERTRAG' && DETAIL_ZUORDNUNG_ID='$mv_id' && DETAIL_AKTUELL='1'");
        $row = $result[0];
        $this->ls_blz_dat = $row ['DETAIL_DAT'];
        $this->ls_blz = $row ['DETAIL_INHALT'];
        /* Bankname holen */
        $result = DB::select("SELECT DETAIL_DAT, DETAIL_INHALT FROM DETAIL WHERE DETAIL_NAME='Bankname-AutoEinzug' && DETAIL_ZUORDNUNG_TABELLE='MIETVERTRAG' && DETAIL_ZUORDNUNG_ID='$mv_id' && DETAIL_AKTUELL='1'");
        $row = $result[0];
        $this->ls_bankname_dat = $row ['DETAIL_DAT'];
        $this->ls_bankname = $row ['DETAIL_INHALT'];
        /* Autoeinzugsart holen */
        $result = DB::select("SELECT DETAIL_DAT, DETAIL_INHALT FROM DETAIL WHERE DETAIL_NAME='Autoeinzugsart' && DETAIL_ZUORDNUNG_TABELLE='MIETVERTRAG' && DETAIL_ZUORDNUNG_ID='$mv_id' && DETAIL_AKTUELL='1'");
        $row = $result[0];
        $this->ls_autoeinzugsart_dat = $row ['DETAIL_DAT'];
        $this->ls_autoeinzugsart = $row ['DETAIL_INHALT'];
        /* Einzugsermächtigungt holen */
        $result = DB::select("SELECT DETAIL_DAT, DETAIL_INHALT FROM DETAIL WHERE DETAIL_NAME='Einzugsermächtigung' && DETAIL_ZUORDNUNG_TABELLE='MIETVERTRAG' && DETAIL_ZUORDNUNG_ID='$mv_id' && DETAIL_AKTUELL='1'");
        $row = $result[0];
        $this->ls_einzugsermaechtigung_dat = $row ['DETAIL_DAT'];
        $this->ls_einzugsermaechtigung = $row ['DETAIL_INHALT'];
        if (!empty ($this->ls_konto_nummer) && !empty ($this->ls_blz)) {
            if (file_exists("classes/class_sepa.php")) {
                $sep = new sepa ();
                $sep->get_iban_bic($this->ls_konto_nummer, $this->ls_blz);
                $this->ls_iban = $sep->IBAN;
                $this->ls_iban1 = $sep->IBAN1;
                $this->ls_bic = $sep->BIC;
                $this->ls_bankname_sep = $sep->BANKNAME;
                $this->ls_bankname_sep_k = $sep->BANKNAME_K;
            }
        }
    }

    function dropdown_ja_nein($beschreibung, $name, $auswahl)
    {
        echo "<label for=\"$name\">$beschreibung</label><select name=\"$name\" id=\"$name\">\n";
        $auswahl_arr [] = 'JA';
        $auswahl_arr [] = 'NEIN';
        $anzahl_kats = count($auswahl_arr);
        for ($a = 0; $a < $anzahl_kats; $a++) {
            $auswahl_value = $auswahl_arr [$a];
            if ($auswahl_value == $auswahl) {
                echo "<option value=\"$auswahl_value\" selected>$auswahl_value</option>\n";
            } else {
                echo "<option value=\"$auswahl_value\">$auswahl_value</option>\n";
            }
        }
        echo "</select>";
    }

    function dropdown_autoeinzug_selected($beschreibung, $name, $einzugsart)
    {
        echo "<label for=\"$name\">$beschreibung</label><select name=\"$name\" id=\"$name\">\n";
        $einzugsarten_arr [] = 'Aktuelles Saldo komplett';
        $einzugsarten_arr [] = 'Nur die Summe aus Vertrag';
        $einzugsarten_arr [] = 'Ratenzahlung';
        $anzahl_kats = count($einzugsarten_arr);
        for ($a = 0; $a < $anzahl_kats; $a++) {
            $einzug_value = $einzugsarten_arr [$a];
            if ($einzug_value == $einzugsart) {
                echo "<option value=\"$einzug_value\" selected>$einzug_value</option>\n";
            } else {
                echo "<option value=\"$einzug_value\">$einzug_value</option>\n";
            }
        }
        echo "</select>";
    }

    function deaktiviere_detail_dats($arr)
    {
        $anzahl_dat = count($arr);
        for ($a = 0; $a < $anzahl_dat; $a++) {
            $dat = $arr [$a];
            $this->deaktiviere_detail_dat($dat);
        }
    }

    function deaktiviere_detail_dat($dat)
    {
        DB::update("UPDATE DETAIL SET DETAIL_AKTUELL='0' WHERE DETAIL_DAT='$dat'");
    }

    function mietvertrag_beenden_db($mv_dat, $mietvertrag_bis)
    {
        DB::update("UPDATE MIETVERTRAG SET MIETVERTRAG_BIS='$mietvertrag_bis' WHERE MIETVERTRAG_DAT='$mv_dat'");
    }

    function mietdefinition_beenden($mv_id, $mietvertrag_bis)
    {
        DB::update("UPDATE MIETENTWICKLUNG SET ENDE='$mietvertrag_bis' WHERE KOSTENTRAEGER_TYP='MIETVERTRAG' && KOSTENTRAEGER_ID='$mv_id' && (ENDE='0000-00-00' OR ENDE>='$mietvertrag_bis') && MIETENTWICKLUNG_AKTUELL='1'");
    }

    function ausgezogene_mieter_anzeigen($objekt_id, $jahr, $monat)
    {
        $auszug_arr = $this->ausgezogene_mieter_arr($objekt_id, $jahr, $monat);
        $o = new objekt ();
        $o->get_objekt_name($objekt_id);
        echo "Objekt: $o->objekt_name<br>";
        if (!empty($auszug_arr)) {
            $anzahl_auszuege = count($auszug_arr);
            $e = new einheit ();
            $ka = new kautionen ();
            echo "<table class=\"sortable\" >";
            echo "<tr><th>Auszug</th><th>Einheit</th><th>Mieter</th><th>Kautionsbetrag</th></tr>";
            for ($a = 0; $a < $anzahl_auszuege; $a++) {
                $einheit_id = $auszug_arr [$a] ['EINHEIT_ID'];
                $mv_id = $auszug_arr [$a] ['MIETVERTRAG_ID'];
                $auszug = date_mysql2german($auszug_arr [$a] ['MIETVERTRAG_BIS']);
                $e->get_einheit_info($einheit_id);
                $this->get_mietvertrag_infos_aktuell($mv_id);
                $personen_string = $this->personen_name_string;
                $ka->get_kautionsbetrag($mv_id);
                $auszugs_dat_arr = explode('.', $auszug);
                $m = $auszugs_dat_arr [1];
                $j = $auszugs_dat_arr [2];

                echo "<tr class=\"zeile2\"><td sorttable_customkey=\"$j$m$t\">$auszug</td><td>$e->einheit_kurzname</td><td>$personen_string</td><td>  $ka->kautions_betrag</td></tr>";
                // echo "<h3>$auszug $e->einheit_kurzname $personen_string Kautionsbetrag:$ka->kautions_betrag</h3>";
                unset ($personen_string);
            }
            echo "</table>";
        } else {
            hinweis_ausgeben("Keine Auszüge im $monat/$jahr");
        }
    }

    function ausgezogene_mieter_arr($objekt_id, $jahr, $monat)
    {
        $mon_laenge = strlen($monat);
        if ($mon_laenge == 1) {
            $monat = '0' . $monat;
        }
        if (empty ($monat)) {
            $result = DB::select("SELECT MIETVERTRAG_ID, MIETVERTRAG_VON, MIETVERTRAG_BIS, EINHEIT_ID FROM `MIETVERTRAG` WHERE DATE_FORMAT( MIETVERTRAG_BIS, '%Y' ) = '$jahr' && MIETVERTRAG_AKTUELL='1' ORDER BY MIETVERTRAG_BIS DESC");
        } else {

            $result = DB::select("SELECT MIETVERTRAG_ID, MIETVERTRAG_VON, MIETVERTRAG_BIS, EINHEIT_ID FROM `MIETVERTRAG` WHERE DATE_FORMAT( MIETVERTRAG_BIS, '%Y-%m' ) = '$jahr-$monat' && MIETVERTRAG_AKTUELL='1' ORDER BY MIETVERTRAG_BIS DESC");
        }
        $e = new einheit ();
        if (!empty($result)) {
            foreach ($result as $row) {
                $einheit_id = $row ['EINHEIT_ID'];
                $e->get_einheit_info($einheit_id);
                if ($e->objekt_id == $objekt_id) {
                    $my_array [] = $row;
                }
            }
            unset ($e);
            return $my_array;
        } else {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\InfoMessage("Keine Auszüge im $monat/$jahr")
            );
        }
    }

    function eingezogene_mieter_anzeigen($objekt_id, $jahr, $monat)
    {
        $einzug_arr = $this->eingezogene_mieter_arr($objekt_id, $jahr, $monat);
        $o = new objekt ();
        $o->get_objekt_name($objekt_id);
        echo "Objekt: $o->objekt_name<br>";
        if (!empty($einzug_arr)) {
            $anzahl_einzuege = count($einzug_arr);
            $e = new einheit ();

            $ka = new kautionen ();
            echo "<table class=\"sortable\" >";
            // echo "<tr class=\"feldernamen\"><td>Auzug</td><td>Einheit</td><td>Mieter</td><td>Kautionsbetrag</td></tr>";
            echo "<tr><th>Einzug</th><th>Auszug</th><th>Einheit</th><th>Mieter</th><th>Kautionsbetrag</th></tr>";
            for ($a = 0; $a < $anzahl_einzuege; $a++) {
                $einheit_id = $einzug_arr [$a] ['EINHEIT_ID'];
                $mv_id = $einzug_arr [$a] ['MIETVERTRAG_ID'];
                $einzug = date_mysql2german($einzug_arr [$a] ['MIETVERTRAG_VON']);
                $auszug = date_mysql2german($einzug_arr [$a] ['MIETVERTRAG_BIS']);
                $e->get_einheit_info($einheit_id);
                $this->get_mietvertrag_infos_aktuell($mv_id);
                $personen_string = $this->personen_name_string;
                $ka->get_kautionsbetrag($mv_id);
                $auszugs_dat_arr = explode('.', $einzug);
                // echo '<pre>';
                // print_r($auszugs_dat_arr);
                $t = $auszugs_dat_arr [0];
                $m = $auszugs_dat_arr [1];
                $j = $auszugs_dat_arr [2];

                echo "<tr class=\"zeile2\"><td sorttable_customkey=\"$j$m$t\">$einzug</td><td>$auszug</td><td>$e->einheit_kurzname</td><td>$personen_string</td><td>  $ka->kautions_betrag</td></tr>";
                // echo "<h3>$auszug $e->einheit_kurzname $personen_string Kautionsbetrag:$ka->kautions_betrag</h3>";
                unset ($personen_string);
            }
            echo "</table>";
        } else {
            hinweis_ausgeben("Keine Auszüge im $monat/$jahr");
        }
    }

    function eingezogene_mieter_arr($objekt_id, $jahr, $monat)
    {
        $mon_laenge = strlen($monat);
        if ($mon_laenge == 1) {
            $monat = '0' . $monat;
        }
        if (empty ($monat)) {
            $result = DB::select("SELECT MIETVERTRAG_ID, MIETVERTRAG_VON, MIETVERTRAG_BIS, EINHEIT_ID FROM `MIETVERTRAG` WHERE DATE_FORMAT( MIETVERTRAG_VON, '%Y' ) = '$jahr' && MIETVERTRAG_AKTUELL='1' ORDER BY MIETVERTRAG_BIS DESC");
        } else {

            $result = DB::select("SELECT MIETVERTRAG_ID, MIETVERTRAG_VON, MIETVERTRAG_BIS, EINHEIT_ID FROM `MIETVERTRAG` WHERE DATE_FORMAT( MIETVERTRAG_VON, '%Y-%m' ) = '$jahr-$monat' && MIETVERTRAG_AKTUELL='1' ORDER BY MIETVERTRAG_BIS DESC");
        }
        $e = new einheit ();
        if (!empty($result)) {
            foreach ($result as $row) {
                $einheit_id = $row ['EINHEIT_ID'];
                $e->get_einheit_info($einheit_id);
                if ($e->objekt_id == $objekt_id) {
                    $my_array [] = $row;
                }
            }
            unset ($e);
            return $my_array;
        } else {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\ErrorMessage("Keine Einzüge im $monat/$jahr")
            );
        }
    }

    function alle_ausgezogene_mieter_anzeigen($jahr, $monat)
    {
        $auszug_arr = $this->alle_ausgezogene_mieter_arr($jahr, $monat);
        $link = "<a href='" . route('web::mietvertraege::legacy', ['mietvertrag_raus' => 'alle_letzten_auszuege_pdf', 'monat' => $monat, 'jahr' => $jahr]) . "'>Ansicht als PDF</a>";
        echo $link;
        if (!empty($auszug_arr)) {
            $anzahl_auszuege = count($auszug_arr);
            $e = new einheit ();
            $m = new mietvertrag ();
            $ka = new kautionen ();
            echo "<table class=\"sortable\">";
            // echo "<tr class=\"feldernamen\"><td>Auzug</td><td>Einheit</td><td>Mieter</td><td>Kautionsbetrag</td></tr>";

            echo "<tr><th>Auszug</th><th>Einheit</th><th>Mieter</th><th>KONTAKT</th><th>Kautionsbetrag</th><th>Abnahme</th><th>PROTOKOLL</th></tr>";
            for ($a = 0; $a < $anzahl_auszuege; $a++) {
                $einheit_id = $auszug_arr [$a] ['EINHEIT_ID'];
                $mv_id = $auszug_arr [$a] ['MIETVERTRAG_ID'];
                $e->get_einheit_info($einheit_id);

                $link_pdf = "<a href='" . route('web::mietvertraege::legacy', ['mietvertrag_raus' => 'abnahmeprotokoll', 'einheit_id' => $einheit_id, 'mv_id' => $mv_id]) . "'><img src=\"images/pdf_light.png\"></a>";
                $link_einheit = "<a href='" . route('web::uebersicht::legacy', ['anzeigen' => 'einheit', 'einheit_id' => $einheit_id, 'mietvertrag_id' => $mv_id]) . "'>$e->einheit_kurzname</a>";
                $link_abnahme = "<a href='" . route('web::details::legacy', ['option' => 'details_hinzu', 'detail_tabelle' => 'MIETVERTRAG', 'detail_id' => $mv_id, 'vorauswahl' => 'Abnahmetermin']) . "'><b>Termin eingeben</b></a>";

                $det = new detail ();
                $abnahme_termin = $det->finde_detail_inhalt('MIETVERTRAG', $mv_id, 'Abnahmetermin');
                $auszug = date_mysql2german($auszug_arr [$a] ['MIETVERTRAG_BIS']);

                $personen_arr = $m->get_personen_ids_mietvertrag($mv_id);
                $personen_string = $this->mv_personen_als_string($personen_arr);
                $ka->get_kautionsbetrag($mv_id);

                $kontaktdaten = $e->kontaktdaten_mieter($mv_id);

                echo "<tr class=\"zeile2\"><td>$auszug</td><td>$link_einheit</td><td>$personen_string</td><td>$kontaktdaten</td><td>  $ka->kautions_betrag</td><td>";
                if (empty ($abnahme_termin)) {
                    echo $link_abnahme;
                } else {
                    echo $abnahme_termin;
                }
                echo "</td><td>$link_pdf</td></tr>";
                // echo "<h3>$auszug $e->einheit_kurzname $personen_string Kautionsbetrag:$ka->kautions_betrag</h3>";
                unset ($personen_string);
            }
            echo "</table>";
        } else {
            hinweis_ausgeben("Keine Auszüge im $monat/$jahr");
        }
    }

    function alle_ausgezogene_mieter_arr($jahr, $monat)
    {
        $mon_laenge = strlen($monat);
        if ($mon_laenge == 1) {
            $monat = '0' . $monat;
        }
        if (empty ($monat)) {
            $result = DB::select("SELECT MIETVERTRAG_ID, MIETVERTRAG_VON, MIETVERTRAG_BIS, EINHEIT_ID FROM `MIETVERTRAG` WHERE DATE_FORMAT( MIETVERTRAG_BIS, '%Y' ) = '$jahr' && MIETVERTRAG_AKTUELL='1' ORDER BY MIETVERTRAG_BIS DESC");
        } else {

            $result = DB::select("SELECT MIETVERTRAG_ID, MIETVERTRAG_VON, MIETVERTRAG_BIS, EINHEIT_ID FROM `MIETVERTRAG` WHERE DATE_FORMAT( MIETVERTRAG_BIS, '%Y-%m' ) = '$jahr-$monat' && MIETVERTRAG_AKTUELL='1' ORDER BY MIETVERTRAG_BIS DESC");
        }
        $e = new einheit ();
        if (!empty($result)) {
            foreach ($result as $row) {
                $einheit_id = $row ['EINHEIT_ID'];
                $e->get_einheit_info($einheit_id);
                $my_array [] = $row;
            }
            unset ($e);
            return $my_array;
        } else {
            return [];
        }
    }

    function alle_eingezogene_mieter_anzeigen($jahr, $monat)
    {
        $auszug_arr = $this->alle_eingezogene_mieter_arr($jahr, $monat);
        $link = "<a href='" . route('web::mietvertraege::legacy', ['mietvertrag_raus' => 'alle_letzten_einzuege_pdf', 'monat' => $monat, 'jahr' => $jahr]) . "'>Ansicht als PDF</a>";
        echo $link;
        if (!empty($auszug_arr)) {
            $anzahl_auszuege = count($auszug_arr);
            $e = new einheit ();
            $m = new mietvertrag ();
            $ka = new kautionen ();
            echo "<table class=\"sortable\">";

            echo "<tr><th>Einzug</th><th>Einheit</th><th>Mieter</th><th>KONTAKT</th><th>Kautionsbetrag</th><th>Abnahme</th><th>PROTOKOLL</th></tr>";
            for ($a = 0; $a < $anzahl_auszuege; $a++) {
                $einheit_id = $auszug_arr [$a] ['EINHEIT_ID'];
                $mv_id = $auszug_arr [$a] ['MIETVERTRAG_ID'];
                $e->get_einheit_info($einheit_id);

                $link_pdf = "<a href='" . route('web::mietvertraege::legacy', ['mietvertrag_raus' => 'abnahmeprotokoll', 'einheit_id' => $einheit_id, 'mv_id' => $mv_id, 'einzug' => 'JA']) . "'><img src=\"images/pdf_light.png\"></a>";
                $link_einheit = "<a href='" . route('web::uebersicht::legacy', ['anzeigen' => 'einheit', 'einheit_id' => $einheit_id, 'mietvertrag_id' => $mv_id]) . "'>$e->einheit_kurzname</a>";
                $link_abnahme = "<a href='" . route('web::details::legacy', ['option' => 'details_hinzu', 'detail_tabelle' => 'MIETVERTRAG', 'detail_id' => $mv_id, 'vorauswahl' => 'Abnahmetermin']) . "'><b>Termin eingeben</b></a>";
                $det = new detail ();
                $abnahme_termin = $det->finde_detail_inhalt('MIETVERTRAG', $mv_id, 'Abnahmetermin');
                $einzug = date_mysql2german($auszug_arr [$a] ['MIETVERTRAG_VON']);

                $personen_arr = $m->get_personen_ids_mietvertrag($mv_id);
                $personen_string = $this->mv_personen_als_string($personen_arr);
                $ka->get_kautionsbetrag($mv_id);

                $kontaktdaten = $e->kontaktdaten_mieter($mv_id);

                echo "<tr class=\"zeile2\"><td>$einzug</td><td>$link_einheit</td><td>$personen_string</td><td>$kontaktdaten</td><td>$ka->kautions_betrag</td><td>";
                if (empty ($abnahme_termin)) {
                    echo $link_abnahme;
                } else {
                    echo $abnahme_termin;
                }
                echo "</td><td>$link_pdf</td></tr>";
                // echo "<h3>$auszug $e->einheit_kurzname $personen_string Kautionsbetrag:$ka->kautions_betrag</h3>";
                unset ($personen_string);
            }
            echo "</table>";
        } else {
            hinweis_ausgeben("Keine Auszüge im $monat/$jahr");
        }
    }

    function alle_eingezogene_mieter_arr($jahr, $monat)
    {
        $mon_laenge = strlen($monat);
        if ($mon_laenge == 1) {
            $monat = '0' . $monat;
        }
        if (empty ($monat)) {
            $result = DB::select("SELECT MIETVERTRAG_ID, MIETVERTRAG_VON, MIETVERTRAG_BIS, EINHEIT_ID FROM `MIETVERTRAG` WHERE DATE_FORMAT( MIETVERTRAG_VON, '%Y' ) = '$jahr' && MIETVERTRAG_AKTUELL='1' ORDER BY MIETVERTRAG_VON DESC");
        } else {

            $result = DB::select("SELECT MIETVERTRAG_ID, MIETVERTRAG_VON, MIETVERTRAG_BIS, EINHEIT_ID FROM `MIETVERTRAG` WHERE DATE_FORMAT( MIETVERTRAG_VON, '%Y-%m' ) = '$jahr-$monat' && MIETVERTRAG_AKTUELL='1' ORDER BY MIETVERTRAG_VON DESC");
        }
        $e = new einheit ();
        if (!empty($result)) {
            foreach ($result as $row) {
                $einheit_id = $row ['EINHEIT_ID'];
                $e->get_einheit_info($einheit_id);
                $my_array [] = $row;
            }
            unset ($e);
            return $my_array;
        }
    }

    function alle_ausgezogenen_pdf($jahr, $monat)
    {
        $pdf = new Cezpdf ('a4', 'portrait');
        $pdf->addInfo('Author', Auth::user()->email);

        $monat_name = monat2name($monat);
        $auszug_arr = $this->alle_ausgezogene_mieter_arr($jahr, $monat);
        $pdf->selectFont($text_schrift);
        $pdf->ezSetCmMargins(1.0, 2.0, 2.0, 1.0);
        $pdf->ezText("<b>Auszüge $monat_name $jahr</b> inkl. Kautionshöhe", 11);
        $pdf->ezSetDy(-20);
        if (!empty($auszug_arr)) {
            $anzahl_auszuege = count($auszug_arr);
            $e = new einheit ();
            $m = new mietvertrag ();
            $ka = new kautionen ();
            for ($a = 0; $a < $anzahl_auszuege; $a++) {
                $einheit_id = $auszug_arr [$a] ['EINHEIT_ID'];
                $mv_id = $auszug_arr [$a] ['MIETVERTRAG_ID'];
                $auszug = date_mysql2german($auszug_arr [$a] ['MIETVERTRAG_BIS']);
                $e->get_einheit_info($einheit_id);
                $personen_arr = $m->get_personen_ids_mietvertrag($mv_id);
                $personen_string = $this->mv_personen_als_string($personen_arr);
                $personen_string = str_replace("\n", " ", htmlspecialchars($personen_string));
                $ka->get_kautionsbetrag($mv_id);
                $det = new detail ();
                $abnahme_termin = bereinige_string($det->finde_detail_inhalt('MIETVERTRAG', $mv_id, 'Abnahmetermin'));
                $pdf_tab [$a] ['EINHEIT'] = $e->einheit_kurzname;
                $pdf_tab [$a] ['MIETER'] = $personen_string;
                $pdf_tab [$a] ['AUSZUG'] = $auszug;
                $pdf_tab [$a] ['KAUTION'] = $ka->kautions_betrag;
                $pdf_tab [$a] ['ABNAHME'] = $abnahme_termin;

                unset ($personen_string);
            }
            $cols = array(
                'EINHEIT' => "EINHEIT",
                'MIETER' => "MIETER",
                'AUSZUG' => "AUSZUG",
                'ABNAHME' => "ABNAHME",
                'KAUTION' => "KAUTION"
            );
            $pdf->ezTable($pdf_tab, $cols, "Auszüge $monat_name $jahr", array(
                'rowGap' => 1.5,
                'showLines' => 1,
                'showHeadings' => 1,
                'shaded' => 1,
                'shadeCol' => array(
                    0.9,
                    0.9,
                    0.9
                ),
                'titleFontSize' => 9,
                'fontSize' => 7,
                'xPos' => 30,
                'xOrientation' => 'right',
                'width' => 550,
                'cols' => array(
                    'EINHEIT' => array(
                        'justification' => 'right',
                        'width' => 50
                    ),
                    'AUSZUG' => array(
                        'justification' => 'right',
                        'width' => 50
                    )
                )
            ));
        } else {
            hinweis_ausgeben("Keine Auszüge im $monat/$jahr");
        }
        ob_end_clean(); // ausgabepuffer leeren
        $dateiname = $monat . "_" . $jahr . "_Auszüge.pdf";
        $pdf_opt ['Content-Disposition'] = $dateiname;
        $pdf->ezStream($pdf_opt);
    }

    function alle_eingezogenen_pdf($jahr, $monat)
    {
        $pdf = new Cezpdf ('a4', 'portrait');
        $pdf->addInfo('Author', Auth::user()->email);

        $monat_name = monat2name($monat);
        $auszug_arr = $this->alle_eingezogene_mieter_arr($jahr, $monat);
        $pdf->selectFont($text_schrift);
        $pdf->ezSetCmMargins(1.0, 2.0, 2.0, 1.0);
        $pdf->ezText("<b>Einzüge $monat_name $jahr</b> inkl. Kautionshöhe", 11);
        $pdf->ezSetDy(-20);
        if (!empty($auszug_arr)) {
            $anzahl_auszuege = count($auszug_arr);
            $e = new einheit ();
            $m = new mietvertrag ();
            $ka = new kautionen ();
            for ($a = 0; $a < $anzahl_auszuege; $a++) {
                $einheit_id = $auszug_arr [$a] ['EINHEIT_ID'];
                $mv_id = $auszug_arr [$a] ['MIETVERTRAG_ID'];
                $einzug = date_mysql2german($auszug_arr [$a] ['MIETVERTRAG_VON']);
                $auszug = date_mysql2german($auszug_arr [$a] ['MIETVERTRAG_BIS']);
                $e->get_einheit_info($einheit_id);
                $personen_arr = $m->get_personen_ids_mietvertrag($mv_id);
                $personen_string = $this->mv_personen_als_string($personen_arr);
                $personen_string = str_replace("\n", " ", htmlspecialchars($personen_string));
                $ka->get_kautionsbetrag($mv_id);
                $det = new detail ();
                $abnahme_termin = bereinige_string($det->finde_detail_inhalt('MIETVERTRAG', $mv_id, 'Abnahmetermin'));

                $pdf_tab [$a] ['EINHEIT'] = $e->einheit_kurzname;
                $pdf_tab [$a] ['MIETER'] = $personen_string;
                $pdf_tab [$a] ['EINZUG'] = $einzug;
                $pdf_tab [$a] ['AUSZUG'] = $auszug;
                $pdf_tab [$a] ['KAUTION'] = $ka->kautions_betrag;
                $pdf_tab [$a] ['ABNAHME'] = $abnahme_termin;

                unset ($personen_string);
            }
            $cols = array(
                'EINHEIT' => "EINHEIT",
                'MIETER' => "MIETER",
                'EINZUG' => "EINZUG",
                'ABNAHME' => "ABNAHME",
                'KAUTION' => "KAUTION"
            );
            $pdf->ezTable($pdf_tab, $cols, "Auszüge $monat_name $jahr", array(
                'rowGap' => 1.5,
                'showLines' => 1,
                'showHeadings' => 1,
                'shaded' => 1,
                'shadeCol' => array(
                    0.9,
                    0.9,
                    0.9
                ),
                'titleFontSize' => 9,
                'fontSize' => 7,
                'xPos' => 30,
                'xOrientation' => 'right',
                'width' => 550,
                'cols' => array(
                    'EINHEIT' => array(
                        'justification' => 'right',
                        'width' => 50
                    ),
                    'AUSZUG' => array(
                        'justification' => 'right',
                        'width' => 50
                    )
                )
            ));
        } else {
            hinweis_ausgeben("Keine Auszüge im $monat/$jahr");
        }
        ob_end_clean(); // ausgabepuffer leeren
        $dateiname = $monat . "_" . $jahr . "_Einzüge.pdf";
        $pdf_opt ['Content-Disposition'] = $dateiname;
        $pdf->ezStream($pdf_opt);
    }

    function saldenliste_mv_pdf($monat, $jahr)
    {
        ob_clean(); // ausgabepuffer leeren
        /* PDF AUSGABE */
        $pdf = new Cezpdf ('a4', 'portrait');
        $pdf->selectFont('Helvetica.afm');
        $pdf->ezSetCmMargins(4.5, 0, 0, 0);
        /* Kopfzeile */
        $pdf->addJpegFromFile('includes/logos/logo_hv_sw.jpg', 220, 750, 175, 100);
        $pdf->setLineStyle(0.5);
        $pdf->addText(86, 743, 6, "BERLUS HAUSVERWALTUNG * Fontanestr. 1 * 14193 Berlin * Inhaber Wolfgang Wehrheim * Telefon: 89784477 * Fax: 89784479 * Email: info@berlus.de");
        $pdf->line(42, 750, 550, 750);
        /* Footer */
        $pdf->line(42, 50, 550, 50);
        $pdf->addText(170, 42, 6, "BERLUS HAUSVERWALTUNG *  Fontanestr. 1 * 14193 Berlin * Inhaber Wolfgang Wehrheim");
        $pdf->addText(150, 35, 6, "Bankverbindung: Dresdner Bank Berlin * BLZ: 100  800  00 * Konto-Nr.: 05 804 000 00 * Steuernummer: 24/582/61188");
        $pdf->addInfo('Title', "Saldenliste $objekt_name $monatname $jahr");
        $pdf->addInfo('Author', Auth::user()->email);
        $pdf->ezStartPageNumbers(550, 755, 7, '', "Seite {PAGENUM} von {TOTALPAGENUM}");

        $jahr = request()->input('jahr');
        if (empty ($jahr)) {
            $jahr = date("Y");
        } else {
            if (strlen($jahr) < 4) {
                $jahr = date("Y");
            }
        }

        $monat = request()->input('monat');
        if (empty ($monat)) {
            $monat = date("m");
        } else {
            if (strlen($monat) < 2) {
                $monat = '0' . $monat;
            }
        }
        $bg = new berlussimo_global ();
        $link = route('web::mietvertraege::legacy', ['mietvertrag_raus' => 'saldenliste'], false);
        $bg->objekt_auswahl_liste();
        $bg->monate_jahres_links($jahr, $link);
        if (session()->has('objekt_id')) {
            $objekt_id = session()->get('objekt_id');
            $einheit_info = new einheit ();
            $o = new objekt ();
            $objekt_name = $o->get_objekt_name($objekt_id);
            $monatname = monat2name($monat);
            $pdf->addText(70, 755, 10, "Saldenliste $objekt_name $monatname $jahr");

            $pdf->ezSetDy(25);
            $pdf->ezSetCmMargins(3, 3, 3, 3);
            $text_options = array(
                'left' => 0,
                'justification' => 'left'
            );
            $pdf->ezText("<b>Einheit</b>", 8, $text_options);
            $pdf->ezSetDy(9);
            $text_options = array(
                'left' => 100,
                'justification' => 'left'
            );
            $pdf->ezText("<b>Mieter</b>", 8, $text_options);
            $pdf->ezSetDy(9);
            $text_options = array(
                'left' => 270,
                'justification' => 'left'
            );
            $pdf->ezText("<b>Einzug</b>", 8, $text_options);
            $pdf->ezSetDy(9);
            $text_options = array(
                'left' => 320,
                'justification' => 'left'
            );
            $pdf->ezText("<b>Auszug</b>", 8, $text_options);
            $pdf->ezSetDy(9);
            $text_options = array(
                'right' => 0,
                'justification' => 'right'
            );
            $pdf->ezText("<b>SALDO EUR</b>", 8, $text_options);

            /* Aktuell bzw. gewünschten Monat berechnen */
            $ob = new objekt ();

            $einheiten_array = $ob->einheiten_objekt_arr($objekt_id);

            $anzahl_aktuell = count($einheiten_array);
            $miete = new miete ();

            $zeilen_pro_seite = 60;
            $aktuelle_zeile = 0;

            for ($i = 0; $i < $anzahl_aktuell; $i++) {

                $mv_array = $einheit_info->get_mietvertraege_bis("" . $einheiten_array [$i] ['EINHEIT_ID'] . "", $jahr, $monat);
                $mv_anzahl = count($mv_array);

                if (!empty($mv_array)) {

                    for ($b = 0; $b < $mv_anzahl; $b++) {
                        $mv_id = $mv_array [$b] ['MIETVERTRAG_ID'];

                        $mk = new mietkonto ();
                        $mieter_ids = $mk->get_personen_ids_mietvertrag($mv_id);
                        for ($a = 0; $a < count($mieter_ids); $a++) {
                            $mieter_daten_arr [] = $mk->get_person_infos($mieter_ids [$a] ['PERSON_MIETVERTRAG_PERSON_ID']);
                        }

                        $end_saldoo = $miete->saldo_berechnen_monatsgenau($mv_id, $monat, $jahr);
                        $zeile = $zeile + 1;
                        $einheit_kurzname = $einheiten_array [$i] ['EINHEIT_KURZNAME'];
                        $vn = rtrim(ltrim($mieter_daten_arr ['0'] ['0'] ['PERSON_VORNAME']));
                        $nn = rtrim(ltrim($mieter_daten_arr ['0'] ['0'] ['PERSON_NACHNAME']));

                        $this->get_mietvertrag_infos_aktuell($mv_id);
                        $l_tag_akt_monat = letzter_tag_im_monat($monat, $jahr);
                        $l_datum = "$jahr-$monat-$l_tag_akt_monat";

                        if ($this->mietvertrag_bis == '0000-00-00' or $this->mietvertrag_bis > $l_datum) {
                            $mv_bis = 'aktuell';
                        } else {
                            $mv_bis = date_mysql2german($this->mietvertrag_bis);
                        }

                        $mv_von = date_mysql2german($this->mietvertrag_von);

                        $end_saldoo = nummer_punkt2komma($end_saldoo);
                        if ($mv_bis == 'aktuell') {
                            $pdf->ezSetCmMargins(3, 3, 3, 3);
                            $text_options = array(
                                'left' => 0,
                                'justification' => 'left'
                            );
                            $pdf->ezText("$einheit_kurzname", 8, $text_options);
                            $pdf->ezSetDy(9);
                            $text_options = array(
                                'left' => 100,
                                'justification' => 'left'
                            );
                            $pdf->ezText("$nn $vn", 8, $text_options);
                            $pdf->ezSetDy(9);
                            $text_options = array(
                                'left' => 270,
                                'justification' => 'left'
                            );
                            $pdf->ezText("$mv_von", 8, $text_options);
                            $pdf->ezSetDy(9);
                            $text_options = array(
                                'left' => 320,
                                'justification' => 'left'
                            );
                            $pdf->ezText("", 8, $text_options);
                            $pdf->ezSetDy(9);
                            $text_options = array(
                                'right' => 0,
                                'justification' => 'right'
                            );
                            $pdf->ezText("$end_saldoo", 8, $text_options);

                            $aktuelle_zeile++;
                        } else {
                            // echo "<b>$zeile. $einheit_kurzname $nn $vn SALDO NEU: $end_saldoo € BEENDET AM :$mv_bis €</b><br>";

                            $pdf->ezSetCmMargins(3, 3, 3, 3);
                            $text_options = array(
                                'left' => 0,
                                'justification' => 'left'
                            );
                            $pdf->ezText("$einheit_kurzname", 8, $text_options);
                            $pdf->ezSetDy(9);
                            $text_options = array(
                                'left' => 100,
                                'justification' => 'left'
                            );
                            $pdf->ezText("$nn $vn", 8, $text_options);
                            $pdf->ezSetDy(9);
                            $text_options = array(
                                'left' => 270,
                                'justification' => 'left'
                            );
                            $pdf->ezText("$mv_von", 8, $text_options);
                            $pdf->ezSetDy(9);
                            $text_options = array(
                                'left' => 320,
                                'justification' => 'left'
                            );
                            $pdf->ezText("$mv_bis", 8, $text_options);
                            $pdf->ezSetDy(9);
                            $text_options = array(
                                'right' => 0,
                                'justification' => 'right'
                            );
                            $pdf->ezText("$end_saldoo", 8, $text_options);

                            $aktuelle_zeile++;
                        }

                        if ($zeilen_pro_seite == $aktuelle_zeile) {
                            $pdf->ezNewPage();
                            /* Kopfzeile */
                            $pdf->addJpegFromFile('includes/logos/logo_hv_sw.jpg', 220, 750, 175, 100);
                            $pdf->setLineStyle(0.5);
                            $pdf->addText(86, 743, 6, "BERLUS HAUSVERWALTUNG * Fontanestr. 1 * 14193 Berlin * Inhaber Wolfgang Wehrheim * Telefon: 89784477 * Fax: 89784479 * Email: info@berlus.de");
                            $pdf->line(42, 750, 550, 750);
                            /* Footer */
                            $pdf->line(42, 50, 550, 50);
                            $pdf->addText(170, 42, 6, "BERLUS HAUSVERWALTUNG *  Fontanestr. 1 * 14193 Berlin * Inhaber Wolfgang Wehrheim");
                            $pdf->addText(150, 35, 6, "Bankverbindung: Dresdner Bank Berlin * BLZ: 100  800  00 * Konto-Nr.: 05 804 000 00 * Steuernummer: 24/582/61188");
                            $pdf->addInfo('Title', "Saldenliste $objekt_name $monatname $jahr");
                            $pdf->addText(70, 755, 10, "Saldenliste  $objekt_name $monatname $jahr");
                            $pdf->ezStartPageNumbers(550, 755, 7, '', "Seite {PAGENUM} von {TOTALPAGENUM}");

                            /* Überschriftzeile */
                            $pdf->ezSetDy(-18);
                            $pdf->ezSetCmMargins(3, 3, 3, 3);
                            $text_options = array(
                                'left' => 0,
                                'justification' => 'left'
                            );
                            $pdf->ezText("<b>Einheit</b>", 8, $text_options);
                            $pdf->ezSetDy(9);
                            $text_options = array(
                                'left' => 100,
                                'justification' => 'left'
                            );
                            $pdf->ezText("<b>Mieter</b>", 8, $text_options);
                            $pdf->ezSetDy(9);
                            $text_options = array(
                                'left' => 270,
                                'justification' => 'left'
                            );
                            $pdf->ezText("<b>Einzug</b>", 8, $text_options);
                            $pdf->ezSetDy(9);
                            $text_options = array(
                                'left' => 320,
                                'justification' => 'left'
                            );
                            $pdf->ezText("<b>Auszug</b>", 8, $text_options);
                            $pdf->ezSetDy(9);
                            $text_options = array(
                                'right' => 0,
                                'justification' => 'right'
                            );
                            $pdf->ezText("<b>SALDO EUR</b>", 8, $text_options);

                            $aktuelle_zeile = 0;
                        }

                        unset ($mieter_daten_arr);
                        unset ($nn);
                        unset ($vn);
                    } // end if is_array mv_ids
                }
            }

            // hinweis_ausgeben("Saldenliste mit Vormieter für $objekt_name wurde erstellt<br>");

            ob_end_clean(); // ausgabepuffer leeren
            $pdf->ezStopPageNumbers();
            $pdf->ezStream();

            /* Falls kein Objekt ausgewählt */
        } else {
            echo "Objekt auswählen";
        }
    }

    function saldenliste_mv($monat, $jahr)
    {
        $jahr = request()->input('jahr');
        if (empty ($jahr)) {
            $jahr = date("Y");
        } else {
            if (strlen($jahr) < 4) {
                $jahr = date("Y");
            }
        }

        $monat = request()->input('monat');
        if (empty ($monat)) {
            $monat = date("m");
        } else {
            if (strlen($monat) < 2) {
                $monat = '0' . $monat;
            }
        }
        $bg = new berlussimo_global ();
        $link = route('web::mietvertraege::legacy', ['mietvertrag_raus' => 'saldenliste'], false);
        $bg->objekt_auswahl_liste();
        $bg->monate_jahres_links($jahr, $link);
        if (session()->has('objekt_id')) {
            $objekt_id = session()->get('objekt_id');
            $einheit_info = new einheit ();
            $o = new objekt ();

            $link_pdf = "<a href='" . route('web::mietvertraege::legacy', ['mietvertrag_raus' => 'saldenliste_pdf', 'monat' => $monat, 'jahr' => $jahr], false) . "'><b>PDF-Datei</b></a>";
            echo '<hr>' . $link_pdf . '<hr>';
            /* Aktuell bzw. gewünschten Monat berechnen */
            $ob = new objekt ();

            $einheiten_array = $ob->einheiten_objekt_arr($objekt_id);

            $anzahl_aktuell = count($einheiten_array);
            $miete = new miete ();

            $zeilen_pro_seite = 47;
            $aktuelle_zeile = 0;

            for ($i = 0; $i < $anzahl_aktuell; $i++) {

                $mv_array = $einheit_info->get_mietvertraege_bis("" . $einheiten_array [$i] ['EINHEIT_ID'] . "", $jahr, $monat);
                $mv_anzahl = count($mv_array);

                if (!empty($mv_array)) {

                    for ($b = 0; $b < $mv_anzahl; $b++) {
                        $mv_id = $mv_array [$b] ['MIETVERTRAG_ID'];

                        $mk = new mietkonto ();
                        $mieter_ids = $mk->get_personen_ids_mietvertrag($mv_id);
                        for ($a = 0; $a < count($mieter_ids); $a++) {
                            $mieter_daten_arr [] = $mk->get_person_infos($mieter_ids [$a] ['PERSON_MIETVERTRAG_PERSON_ID']);
                        }

                        // $miete->mietkonto_berechnung_monatsgenau($mv_id, $jahr, $monat);
                        $end_saldoo = $miete->saldo_berechnen_monatsgenau($mv_id, $monat, $jahr);
                        $zeile = $zeile + 1;
                        $einheit_kurzname = $einheiten_array [$i] ['EINHEIT_KURZNAME'];
                        $vn = rtrim(ltrim($mieter_daten_arr ['0'] ['0'] ['PERSON_VORNAME']));
                        $nn = rtrim(ltrim($mieter_daten_arr ['0'] ['0'] ['PERSON_NACHNAME']));

                        $this->get_mietvertrag_infos_aktuell($mv_id);
                        $l_tag_akt_monat = letzter_tag_im_monat($monat, $jahr);
                        $l_datum = "$jahr-$monat-$l_tag_akt_monat";

                        if ($this->mietvertrag_bis == '0000-00-00' or $this->mietvertrag_bis > $l_datum) {
                            $mv_bis = 'aktuell';
                        } else {
                            $mv_bis = date_mysql2german($this->mietvertrag_bis);
                        }

                        $end_saldoo = nummer_punkt2komma($end_saldoo);

                        $mv_von = date_mysql2german($this->mietvertrag_von);

                        if ($mv_bis == 'aktuell') {
                            echo "$zeile. $einheit_kurzname $nn $vn $mv_von $mv_bis   SALDO NEU: $end_saldoo <br>";

                            $aktuelle_zeile++;
                        } else {
                            echo "<b>$zeile. $einheit_kurzname $nn $vn $mv_von $mv_bis   SALDO NEU: $end_saldoo </b><br>";
                            $aktuelle_zeile++;
                        }

                        if ($zeilen_pro_seite == $aktuelle_zeile) {
                            echo "<hr>";

                            $aktuelle_zeile = 0;
                        }

                        unset ($mieter_daten_arr);
                        unset ($nn);
                        unset ($vn);
                    } // end if is_array mv_ids
                }
            }

            // hinweis_ausgeben("Saldenliste mit Vormieter für $objekt_name wurde erstellt<br>");

            /* Falls kein Objekt ausgewählt */
        } else {
            echo "Objekt auswählen";
        }
    }

    function nebenkosten($objekt_id, $jahr)
    {
        $ob = new objekt ();
        $einheiten_array = $ob->einheiten_objekt_arr($objekt_id);
        $anz = count($einheiten_array);
        for ($a = 0; $a < $anz; $a++) {
            $bk = new bk ();
            $einheit_id = $einheiten_array [$a] ['EINHEIT_ID'];
            $einheit_kn = $einheiten_array [$a] ['EINHEIT_KURZNAME'];
            $arr [$a] ['MVS'] = $bk->mvs_und_leer_jahr($einheit_id, $jahr);
            $arr [$a] ['EINHEIT_KURZNAME'] = $einheit_kn;
        }

        $anz = count($arr);
        echo "<table class=\"sortable\">";
        echo "<tr><th>EINHEIT</th><th>MIETER</th><th>VON</th><th>BIS</th><th>TAGE</th><th>SUMME BK</th><th>SUMME HK</th><th>Kaltmiete</th></tr>";
        for ($a = 0; $a < $anz; $a++) {
            $anz1 = count($arr [$a] ['MVS']);
            for ($b = 0; $b < $anz1; $b++) {

                $mv_id = $arr [$a] ['MVS'] [$b] ['KOS_ID'];
                $b_von = date_mysql2german($arr [$a] ['MVS'] [$b] ['BERECHNUNG_VON']);
                $b_bis = date_mysql2german($arr [$a] ['MVS'] [$b] ['BERECHNUNG_BIS']);
                $tage = $arr [$a] ['MVS'] [$b] ['TAGE'];
                if ($mv_id != 'Leerstand') {
                    $mv = new mietvertraege ();
                    $mv->get_mietvertrag_infos_aktuell($mv_id);
                    $mz = new miete ();
                    $summe_nebenkosten_jahr = $mz->summe_nebenkosten_im_jahr('MIETVERTRAG', $mv_id, $jahr);
                    $summe_hk_jahr = $mz->summe_heizkosten_im_jahr('MIETVERTRAG', $mv_id, $jahr);

                    /* Kaltmiete */
                    $li = new listen ();
                    $b_von_2 = date_german2mysql($b_von);
                    $b_bis_2 = date_german2mysql($b_bis);
                    $km_mon_array = $li->monats_array($b_von_2, $b_bis_2);
                    // echo "$b_bis $b_bis_2 $b_von $b_von_2";

                    $anz_m = count($km_mon_array);
                    $sm_kalt = 0;
                    for ($m = 0; $m < $anz_m; $m++) {
                        $sm = $km_mon_array [$m] ['MONAT'];
                        $sj = $km_mon_array [$m] ['JAHR'];
                        $mk = new mietkonto ();
                        $mk->kaltmiete_monatlich_ink_vz($mv_id, $sm, $sj);
                        $sm_kalt += $mk->ausgangs_kaltmiete;
                    }

                    $sm_kalt_a = nummer_punkt2komma($sm_kalt);

                    if ($tage < 365) {
                        echo "<tr><td class=\"rot\">$mv->einheit_kurzname</td><td class=\"rot\">$mv->personen_name_string</td><td class=\"rot\">$b_von</td><td class=\"rot\">$b_bis</td><td class=\"rot\">$tage</td><td class=\"rot\">$summe_nebenkosten_jahr</td><td class=\"rot\">$summe_hk_jahr</td><td>$sm_kalt_a</td></tr>";
                    } else {

                        echo "<tr ><td>$mv->einheit_kurzname</td><td>$mv->personen_name_string</td><td>$b_von</td><td>$b_bis</td><td>$tage</td><td>$summe_nebenkosten_jahr</td><td>$summe_hk_jahr</td><td>$sm_kalt_a</td></tr>";
                    }
                } else {
                    $einheit_kn = $arr [$a] ['EINHEIT_KURZNAME'];
                    echo "<tr><td class=\"gruen\">$einheit_kn</td><td class=\"gruen\"><b>LEERSTAND</b></td><td>$b_von</td><td>$b_bis</td><td>$tage</td><td></td><td></td><td></td></tr>";
                }
            }
        }
        echo "</table>";
    }

    function nebenkosten_pdf($objekt_id, $jahr)
    {
        $ob = new objekt ();
        $einheiten_array = $ob->einheiten_objekt_arr($objekt_id);
        $anz = count($einheiten_array);
        for ($a = 0; $a < $anz; $a++) {
            $bk = new bk ();
            $einheit_id = $einheiten_array [$a] ['EINHEIT_ID'];
            $einheit_kn = $einheiten_array [$a] ['EINHEIT_KURZNAME'];
            $einheit_qm = $einheiten_array [$a] ['EINHEIT_QM'];
            $einheit_lage = $einheiten_array [$a] ['EINHEIT_LAGE'];
            $arr [$a] ['MVS'] = $bk->mvs_und_leer_jahr($einheit_id, $jahr);
            $arr [$a] ['EINHEIT_KURZNAME'] = $einheit_kn;
            $arr [$a] ['EINHEIT_LAGE'] = $einheit_lage;
            $arr [$a] ['EINHEIT_QM'] = $einheit_qm;
        }

        $anz = count($arr);
        echo "<table class=\"sortable\">";
        echo "<tr><th>EINHEIT</th><th>MIETER</th><th>VON</th><th>BIS</th><th>TAGE</th><th>SUMME BK</th><th>SUMME HK</th></tr>";
        $z = 0;
        $summe_nebenkosten_jahr_alle = 0;
        $summe_hk_jahr_alle = 0;
        $summe_km_jahr_alle = 0;

        for ($a = 0; $a < $anz; $a++) {
            $anz1 = count($arr [$a] ['MVS']);
            for ($b = 0; $b < $anz1; $b++) {
                $mz = new miete ();
                $mv_id = $arr [$a] ['MVS'] [$b] ['KOS_ID'];
                $b_von = date_mysql2german($arr [$a] ['MVS'] [$b] ['BERECHNUNG_VON']);
                $b_bis = date_mysql2german($arr [$a] ['MVS'] [$b] ['BERECHNUNG_BIS']);
                $tage = $arr [$a] ['MVS'] [$b] ['TAGE'];
                if ($mv_id != 'Leerstand') {
                    $mv = new mietvertraege ();
                    $mv->get_mietvertrag_infos_aktuell($mv_id);
                    $summe_nebenkosten_jahr = $mz->summe_nebenkosten_im_jahr('MIETVERTRAG', $mv_id, $jahr);
                    $summe_hk_jahr = $mz->summe_heizkosten_im_jahr('MIETVERTRAG', $mv_id, $jahr);
                    $summe_nebenkosten_jahr_a = nummer_punkt2komma_t($summe_nebenkosten_jahr);
                    $summe_hk_jahr_a = nummer_punkt2komma_t($summe_hk_jahr);

                    /* Kaltmiete */
                    $li = new listen ();
                    $b_von_2 = date_german2mysql($b_von);
                    $b_bis_2 = date_german2mysql($b_bis);
                    $km_mon_array = $li->monats_array($b_von_2, $b_bis_2);
                    // echo "$b_bis $b_bis_2 $b_von $b_von_2";

                    $anz_m = count($km_mon_array);
                    $sm_kalt = 0;
                    for ($m = 0; $m < $anz_m; $m++) {
                        $sm = $km_mon_array [$m] ['MONAT'];
                        $sj = $km_mon_array [$m] ['JAHR'];
                        $mk = new mietkonto ();
                        $mk->kaltmiete_monatlich_ink_vz($mv_id, $sm, $sj);
                        $sm_kalt += $mk->ausgangs_kaltmiete;
                    }

                    $summe_km_jahr_alle += $sm_kalt;
                    $sm_kalt_a = nummer_punkt2komma_t($sm_kalt);

                    if ($tage < 365) {
                        $table_arr [$z] ['EINHEIT'] = "<b>$mv->einheit_kurzname</b>";
                        $table_arr [$z] ['MIETER'] = "<b>$mv->personen_name_string</b>";
                        $table_arr [$z] ['LAGE'] = ltrim(rtrim($arr [$a] ['EINHEIT_LAGE']));
                        $table_arr [$z] ['QM'] = $arr [$a] ['EINHEIT_QM'];
                        $table_arr [$z] ['EINZUG'] = "<b>$b_von</b>";
                        $table_arr [$z] ['AUSZUG'] = "<b>$b_bis</b>";
                        $table_arr [$z] ['BETRIEBSKOSTEN'] = "<b>$summe_nebenkosten_jahr_a</b>";
                        $table_arr [$z] ['HEIZKOSTEN'] = "<b>$summe_hk_jahr_a</b>";
                        $table_arr [$z] ['KM'] = "<b>$sm_kalt_a</b>";
                        // echo "<tr><td class=\"rot\">$mv->einheit_kurzname</td><td class=\"rot\">$mv->personen_name_string</td><td class=\"rot\">$b_von</td><td class=\"rot\">$b_bis</td><td class=\"rot\">$tage</td><td class=\"rot\">$summe_nebenkosten_jahr</td><td class=\"rot\">$summe_hk_jahr</td></tr>";
                        $z++;
                    } else {
                        // echo "<tr ><td>$mv->einheit_kurzname</td><td>$mv->personen_name_string</td><td>$b_von</td><td>$b_bis</td><td>$tage</td><td>$summe_nebenkosten_jahr</td><td>$summe_hk_jahr</td></tr>";
                        $table_arr [$z] ['EINHEIT'] = $mv->einheit_kurzname;
                        $table_arr [$z] ['MIETER'] = $mv->personen_name_string;
                        $table_arr [$z] ['LAGE'] = ltrim(rtrim($arr [$a] ['EINHEIT_LAGE']));
                        $table_arr [$z] ['QM'] = $arr [$a] ['EINHEIT_QM'];
                        $table_arr [$z] ['EINZUG'] = $b_von;
                        $table_arr [$z] ['AUSZUG'] = $b_bis;
                        $table_arr [$z] ['BETRIEBSKOSTEN'] = $summe_nebenkosten_jahr_a;
                        $table_arr [$z] ['HEIZKOSTEN'] = $summe_hk_jahr_a;
                        $table_arr [$z] ['KM'] = "<b>$sm_kalt_a</b>";
                        $z++;
                    }
                } else {
                    $einheit_kn = $arr [$a] ['EINHEIT_KURZNAME'];
                    // echo "<tr><td class=\"gruen\">$einheit_kn</td><td class=\"gruen\"><b>LEERSTAND</b></td><td>$b_von</td><td>$b_bis</td><td>$tage</td><td></td><td></td></tr>";
                    $table_arr [$z] ['EINHEIT'] = "<u><b>$einheit_kn</b></u>";
                    $table_arr [$z] ['MIETER'] = "<u><b>LEERSTAND</b></u>";
                    $table_arr [$z] ['LAGE'] = ltrim(rtrim($arr [$a] ['EINHEIT_LAGE']));
                    $table_arr [$z] ['QM'] = $arr [$a] ['EINHEIT_QM'];
                    $table_arr [$z] ['EINZUG'] = "<u><b>$b_von</b></u>";
                    $table_arr [$z] ['AUSZUG'] = "<u><b>$b_bis</b></u>";
                    $summe_nebenkosten_jahr = $mz->summe_nebenkosten_im_jahr('MIETVERTRAG', $mv_id, $jahr);
                    $summe_nebenkosten_jahr_a = nummer_punkt2komma_t($summe_nebenkosten_jahr);
                    $summe_hk_jahr = $mz->summe_heizkosten_im_jahr('MIETVERTRAG', $mv_id, $jahr);
                    $summe_hk_jahr_a = nummer_punkt2komma_t($summe_hk_jahr);
                    $table_arr [$z] ['BETRIEBSKOSTEN'] = "<u><b>$summe_nebenkosten_jahr_a</b></u>";
                    $table_arr [$z] ['HEIZKOSTEN'] = "<u><b>$summe_hk_jahr_a</b></u>";
                    $z++;
                }

                $summe_nebenkosten_jahr_alle += $summe_nebenkosten_jahr;
                $summe_hk_jahr_alle += $summe_hk_jahr;
            }
        }
        $summe_nebenkosten_jahr_alle_a = nummer_punkt2komma_t($summe_nebenkosten_jahr_alle);
        $summe_hk_jahr_alle_a = nummer_punkt2komma_t($summe_hk_jahr_alle);
        $summe_km_jahr_alle_a = nummer_punkt2komma_t($summe_km_jahr_alle);
        $table_arr [$z] ['BETRIEBSKOSTEN'] = "<u><b>$summe_nebenkosten_jahr_alle_a</b></u>";
        $table_arr [$z] ['HEIZKOSTEN'] = "<u><b>$summe_hk_jahr_alle_a</b></u>";
        $table_arr [$z] ['KM'] = "<u><b>$summe_km_jahr_alle_a</b></u>";

        ob_end_clean(); // ausgabepuffer leeren
        $pdf = new Cezpdf ('a4', 'landscape');
        $bpdf = new b_pdf ();
        $bpdf->b_header($pdf, 'Partner', session()->get('partner_id'), 'landscape', 'Helvetica.afm', 6);

        $cols = array(
            'EINHEIT' => "Einheit",
            'MIETER' => "Mieter",
            'LAGE' => "Lage",
            'QM' => "m²",
            'EINZUG' => "Einzug",
            'AUSZUG' => "Auszug",
            'BETRIEBSKOSTEN' => "BK",
            'HEIZKOSTEN' => "HK",
            'KM' => "Kaltmiete"
        );
        $datum_h = date("d.m.Y");

        $pdf->ezTable($table_arr, $cols, "Nebenkostenhochrechnung für das Jahr $jahr vom $datum_h", array(
            'showHeadings' => 1,
            'shaded' => 1,
            'titleFontSize' => 8,
            'fontSize' => 7,
            'xPos' => 50,
            'xOrientation' => 'right',
            'width' => 740,
            'cols' => array(
                'EINHEIT' => array(
                    'justification' => 'left',
                    'width' => 70
                ),
                'MIETER' => array(
                    'justification' => 'left'
                ),
                'EINZUG' => array(
                    'justification' => 'right',
                    'width' => 46
                ),
                'AUSZUG' => array(
                    'justification' => 'right',
                    'width' => 46
                ),
                'BETRIEBSKOSTEN' => array(
                    'justification' => 'right',
                    'width' => 50
                ),
                'HEIZKOSTEN' => array(
                    'justification' => 'right',
                    'width' => 50
                ),
                'KM' => array(
                    'justification' => 'right',
                    'width' => 50
                )
            )
        ));

        $pdf->ezStream();
    }

    function nebenkosten_pdf_zs_ant($objekt_id, $jahr)
    {
        $deta = new detail ();
        /* Nutzenlastenwechsel */
        $nl_datum = $deta->finde_detail_inhalt('Objekt', $objekt_id, 'Nutzen-Lastenwechsel');
        $nl_datum_arr = explode('.', strip_tags($nl_datum));

        if (!empty($nl_datum_arr)) {
            $nl_jahr = $nl_datum_arr [2];
        }

        if ($nl_jahr == $jahr) {
            echo "NLBBB $nl_datum $nl_jahr<br>";
            $datum_von_ber = date_german2mysql($nl_datum);
            $bkk = new bk ();
            $wegg = new weg ();
            $ob = new objekt ();
            $einheiten_array = $ob->einheiten_objekt_arr($objekt_id);
            $anz = count($einheiten_array);
            for ($a = 0; $a < $anz; $a++) {
                $bk = new bk ();
                $einheit_id = $einheiten_array [$a] ['EINHEIT_ID'];
                $einheit_kn = $einheiten_array [$a] ['EINHEIT_KURZNAME'];
                $arr [$a] ['MVS'] = $bk->mvs_und_leer_jahr_zeitraum($einheit_id, $datum_von_ber, $jahr);
                $arr [$a] ['EINHEIT_KURZNAME'] = $einheit_kn;
            }

            $li = new listen ();
            $b_von_2 = $datum_von_ber;
            $b_bis_2 = "$jahr-12-31";
            $km_mon_array = $li->monats_array($b_von_2, $b_bis_2);
            $anz_m = count($km_mon_array);

            $anz = count($einheiten_array);
            $z = 0;

            $summe_nebenkosten_jahr_alle = 0;
            $summe_km_jahr_alle = 0;;

            for ($a = 0; $a < $anz; $a++) {
                $anz1 = count($arr [$a] ['MVS']);
                $sum_bk_einheit_jahr = 0;
                $sum_hk_einheit_jahr = 0;
                $sum_km_einheit_jahr = 0;

                $einheit_kn = $arr [$a] ['EINHEIT_KURZNAME'];

                for ($b = 0; $b < $anz1; $b++) {
                    $summe_hk_jahr = 0;
                    $mv_id = $arr [$a] ['MVS'] [$b] ['KOS_ID'];
                    $b_von = date_mysql2german($arr [$a] ['MVS'] [$b] ['BERECHNUNG_VON']);
                    $b_bis = date_mysql2german($arr [$a] ['MVS'] [$b] ['BERECHNUNG_BIS']);
                    $tage = $arr [$a] ['MVS'] [$b] ['TAGE'];
                    if ($mv_id != 'Leerstand') {

                        $mv = new mietvertraege ();
                        $mv->get_mietvertrag_infos_aktuell($mv_id);

                        $sm_kalt = 0;
                        $summe_nk_einheit = 0;
                        for ($m = 0; $m < $anz_m; $m++) {
                            $sm = $km_mon_array [$m] ['MONAT'];
                            $sj = $km_mon_array [$m] ['JAHR'];
                            $mk = new mietkonto ();
                            $mk->kaltmiete_monatlich_ink_vz($mv_id, $sm, $sj);
                            $m_soll = $mk->summe_forderung_monatlich($mv_id, $sm, $sj);
                            $miete_arr = explode('|', $m_soll);
                            $wm = $miete_arr [0];
                            $nebenkosten_m = $wm - $mk->ausgangs_kaltmiete;
                            $sm_kalt += $mk->ausgangs_kaltmiete;
                            $summe_nk_einheit += $nebenkosten_m;
                            $summe_nebenkosten_jahr_alle += $nebenkosten_m;
                            $summe_km_jahr_alle += $mk->ausgangs_kaltmiete;
                        }

                        $sm_kalt_a = nummer_punkt2komma_t($sm_kalt);
                        $summe_nk_einheit_a = nummer_punkt2komma_t($summe_nk_einheit);

                        if ($tage < 365) {
                            $table_arr [$z] ['EINHEIT'] = "<b>$mv->einheit_kurzname</b>";
                            $table_arr [$z] ['MIETER'] = "$mv->personen_name_string";
                            $table_arr [$z] ['EINZUG'] = "$b_von";
                            $table_arr [$z] ['AUSZUG'] = "$b_bis";
                            $table_arr [$z] ['BETRIEBSKOSTEN'] = "<b>$summe_nk_einheit_a</b>";
                            $table_arr [$z] ['HEIZKOSTEN'] = "";
                            $table_arr [$z] ['KM'] = "<b>$sm_kalt_a</b>";
                            $z++;
                        } else {
                            $table_arr [$z] ['EINHEIT'] = $mv->einheit_kurzname;
                            $table_arr [$z] ['MIETER'] = $mv->personen_name_string;
                            $table_arr [$z] ['EINZUG'] = $b_von;
                            $table_arr [$z] ['AUSZUG'] = $b_bis;
                            $table_arr [$z] ['BETRIEBSKOSTEN'] = $summe_nk_einheit_a;
                            $table_arr [$z] ['HEIZKOSTEN'] = '';
                            $table_arr [$z] ['KM'] = "<b>$sm_kalt_a</b>";
                            $z++;
                        }
                        $sum_km_einheit_jahr += $sm_kalt;
                        $sum_bk_einheit_jahr += $summe_nk_einheit;
                        $sum_hk_einheit_jahr += $summe_hk_jahr;
                    } else {
                        $einheit_kn = $arr [$a] ['EINHEIT_KURZNAME'];
                        // echo "<tr><td class=\"gruen\">$einheit_kn</td><td class=\"gruen\"><b>LEERSTAND</b></td><td>$b_von</td><td>$b_bis</td><td>$tage</td><td></td><td></td></tr>";
                        $table_arr [$z] ['EINHEIT'] = "<u><b>$einheit_kn</b></u>";
                        $table_arr [$z] ['MIETER'] = "<u><b>LEERSTAND</b></u>";
                        $table_arr [$z] ['EINZUG'] = "<u><b>$b_von</b></u>";
                        $table_arr [$z] ['AUSZUG'] = "<u><b>$b_bis</b></u>";
                        $summe_nebenkosten_jahr = 0;
                        $summe_nebenkosten_jahr_a = nummer_punkt2komma_t($summe_nebenkosten_jahr);
                        $summe_hk_jahr = 0;
                        $summe_hk_jahr_a = nummer_punkt2komma_t($summe_hk_jahr);
                        $table_arr [$z] ['BETRIEBSKOSTEN'] = "<u><b>$summe_nebenkosten_jahr_a</b></u>";
                        $table_arr [$z] ['HEIZKOSTEN'] = "<u><b>$summe_hk_jahr_a</b></u>";
                        $z++;
                    }

                    // $summe_nebenkosten_jahr_alle += $summe_nk_einheit;
                    $summe_hk_jahr_alle += $summe_hk_jahr;
                }
                /* Zwischensumme */
                if ($anz1 > 1) {
                    $sum_bk_einheit_jahr_a = nummer_punkt2komma_t($sum_bk_einheit_jahr);
                    $sum_hk_einheit_jahr_a = nummer_punkt2komma_t($sum_hk_einheit_jahr);
                    $sum_km_einheit_jahr_a = nummer_punkt2komma_t($sum_km_einheit_jahr);
                    $table_arr [$z] ['EINHEIT'] = "<i><b>$einheit_kn</b></i>";
                    $table_arr [$z] ['MIETER'] = "<i><b>JAHRESSUMME für $einheit_kn</b></i>";
                    $table_arr [$z] ['BETRIEBSKOSTEN'] = "<u><b>$sum_bk_einheit_jahr_a</b></u>";
                    $table_arr [$z] ['HEIZKOSTEN'] = "<u><b>$sum_hk_einheit_jahr_a</b></u>";
                    $table_arr [$z] ['KM'] = "<u><b>$sum_km_einheit_jahr_a</b></u>";
                    $z++;
                }
            }
            $summe_nebenkosten_jahr_alle_a = nummer_punkt2komma_t($summe_nebenkosten_jahr_alle);
            $summe_hk_jahr_alle_a = nummer_punkt2komma_t($summe_hk_jahr_alle);
            $summe_km_jahr_alle_a = nummer_punkt2komma_t($summe_km_jahr_alle);
            $table_arr [$z] ['BETRIEBSKOSTEN'] = "<u><b>$summe_nebenkosten_jahr_alle_a</b></u>";
            $table_arr [$z] ['HEIZKOSTEN'] = "<u><b>$summe_hk_jahr_alle_a</b></u>";
            $table_arr [$z] ['KM'] = "<u><b>$summe_km_jahr_alle_a</b></u>";

            ob_clean(); // ausgabepuffer leeren
            $pdf = new Cezpdf ('a4', 'portrait');
            $bpdf = new b_pdf ();
            $bpdf->b_header($pdf, 'Partner', session()->get('partner_id'), 'portrait', 'Helvetica.afm', 6);

            ob_clean(); // ausgabepuffer leeren
            $cols = array(
                'EINHEIT' => "Einheit",
                'MIETER' => "Mieter",
                'EINZUG' => "Von",
                'AUSZUG' => "Bis",
                'BETRIEBSKOSTEN' => "Nebenkosten",
                'KM' => "Kaltmiete"
            );
            $datum_von_ber_d = date_mysql2german($datum_von_ber);
            $pdf->ezText("<b>Lastenutzenwechsel efolgte  am $datum_von_ber_d</b>", 8);
            $pdf->ezTable($table_arr, $cols, "Soll - Nebenkosten/Kaltmiete $datum_von_ber_a bis 31.12.$jahr", array(
                'showHeadings' => 1,
                'shaded' => 1,
                'titleFontSize' => 8,
                'fontSize' => 7,
                'xPos' => 50,
                'xOrientation' => 'right',
                'width' => 500,
                'cols' => array(
                    'EINHEIT' => array(
                        'justification' => 'left',
                        'width' => 75
                    ),
                    'MIETER' => array(
                        'justification' => 'left',
                        'width' => 175
                    ),
                    'EINZUG' => array(
                        'justification' => 'right',
                        'width' => 46
                    ),
                    'AUSZUG' => array(
                        'justification' => 'right',
                        'width' => 46
                    ),
                    'BETRIEBSKOSTEN' => array(
                        'justification' => 'right',
                        'width' => 60
                    ),
                    'HEIZKOSTEN' => array(
                        'justification' => 'right',
                        'width' => 60
                    ),
                    'KM' => array(
                        'justification' => 'right',
                        'width' => 60
                    )
                )
            ));

            if (!request()->has('xls')) {
                ob_end_clean(); // ausgabepuffer leeren
                $pdf->ezStream();
            } else {
                ob_end_clean(); // ausgabepuffer leeren
                // echo '<pre>';
                // print_r($table_arr);

                $oo = new objekt ();
                $oo->get_objekt_infos($objekt_id);
                $fileName = "$oo->objekt_kurzname Sollhochrechnung $jahr" . '.xls';
                header("Content-Type: application/vnd.ms-excel");
                // header("Content-Disposition: attachment; filename=$fileName");
                header("Content-Disposition: inline; filename=$fileName");
                ob_clean(); // ausgabepuffer leeren
                echo "<table class=\"sortable\" id=\"positionen_tab\">";
                echo "<thead>";
                echo "<tr>";
                echo "<th>EINHEIT</th>";
                echo "<th>MIETER</th>";
                echo "<th>EINZUG</th>";
                echo "<th>AUSZUG</th>";
                echo "<th>BK-SOLL</th>";
                echo "<th>HK-SOLL</th>";
                echo "<th>KALTMIETE-SOLL</th>";
                echo "</tr>";
                echo "</thead>";

                // $cols = array('EINHEIT'=>"Einheit", 'MIETER'=>"Mieter",'EINZUG'=>"Einzug",'AUSZUG'=>"Auszug",'BETRIEBSKOSTEN'=>"BK", 'HEIZKOSTEN'=>"HK", 'KM'=>"Kaltmiete");
                $anz_zeilen = count($table_arr);
                for ($ze = 0; $ze < $anz_zeilen; $ze++) {
                    if (isset ($table_arr [$ze] ['EINHEIT'])) {
                        $einheit_kn = $table_arr [$ze] ['EINHEIT'];
                    } else {
                        $einheit_kn = '';
                    }
                    if (isset ($table_arr [$ze] ['MIETER'])) {
                        $mieter_n = $table_arr [$ze] ['MIETER'];
                    } else {
                        $mieter_n = '';
                    }
                    if (isset ($table_arr [$ze] ['EINZUG'])) {
                        $von = $table_arr [$ze] ['EINZUG'];
                    } else {
                        $von = '';
                    }

                    if (isset ($table_arr [$ze] ['AUSZUG'])) {
                        $bis = $table_arr [$ze] ['AUSZUG'];
                    } else {
                        $bis = '';
                    }
                    if (isset ($table_arr [$ze] ['BETRIEBSKOSTEN'])) {
                        $bk = $table_arr [$ze] ['BETRIEBSKOSTEN'];
                    } else {
                        $bk = '';
                    }
                    if (isset ($table_arr [$ze] ['HEIZKOSTEN'])) {
                        $hk = $table_arr [$ze] ['HEIZKOSTEN'];
                    } else {
                        $hk = '';
                    }
                    if (isset ($table_arr [$ze] ['KM'])) {
                        $km = $table_arr [$ze] ['KM'];
                    } else {
                        $km = '';
                    }

                    echo "<tr><td>$einheit_kn</td><td>$mieter_n</td><td>$von</td><td>$bis</td><td>$bk</td><td>$hk</td><td>$km</td></tr>";
                }
                echo "</table>";
            }
        } else {
            /* Ganzes Jahr ohne NLW */
            $this->nebenkosten_pdf_zs($objekt_id, $jahr);
        }
    }

    function nebenkosten_pdf_zs($objekt_id, $jahr)
    {
        $ob = new objekt ();
        $einheiten_array = $ob->einheiten_objekt_arr($objekt_id);
        $anz = count($einheiten_array);
        for ($a = 0; $a < $anz; $a++) {
            $bk = new bk ();
            $einheit_id = $einheiten_array [$a] ['EINHEIT_ID'];
            $einheit_kn = $einheiten_array [$a] ['EINHEIT_KURZNAME'];
            $arr [$a] ['MVS'] = $bk->mvs_und_leer_jahr($einheit_id, $jahr);
            $arr [$a] ['EINHEIT_KURZNAME'] = $einheit_kn;
        }

        $anz = count($arr);
        $z = 0;
        $summe_nebenkosten_jahr_alle = 0;
        $summe_hk_jahr_alle = 0;
        $summe_km_jahr_alle = 0;

        for ($a = 0; $a < $anz; $a++) {
            $anz1 = count($arr [$a] ['MVS']);
            $sum_bk_einheit_jahr = 0;
            $sum_hk_einheit_jahr = 0;
            $sum_km_einheit_jahr = 0;

            $einheit_kn = $arr [$a] ['EINHEIT_KURZNAME'];

            for ($b = 0; $b < $anz1; $b++) {
                $mz = new miete ();
                $mv_id = $arr [$a] ['MVS'] [$b] ['KOS_ID'];
                $b_von = date_mysql2german($arr [$a] ['MVS'] [$b] ['BERECHNUNG_VON']);
                $b_bis = date_mysql2german($arr [$a] ['MVS'] [$b] ['BERECHNUNG_BIS']);
                $tage = $arr [$a] ['MVS'] [$b] ['TAGE'];
                if ($mv_id != 'Leerstand') {
                    $mv = new mietvertraege ();
                    $mv->get_mietvertrag_infos_aktuell($mv_id);
                    $summe_nebenkosten_jahr = $mz->summe_nebenkosten_im_jahr('MIETVERTRAG', $mv_id, $jahr);
                    $summe_hk_jahr = $mz->summe_heizkosten_im_jahr('MIETVERTRAG', $mv_id, $jahr);
                    $summe_nebenkosten_jahr_a = nummer_punkt2komma_t($summe_nebenkosten_jahr);
                    $summe_hk_jahr_a = nummer_punkt2komma_t($summe_hk_jahr);

                    /* Kaltmiete */
                    $li = new listen ();
                    /* Anteilig */

                    $b_von_2 = date_german2mysql($b_von);
                    $b_bis_2 = date_german2mysql($b_bis);
                    $km_mon_array = $li->monats_array($b_von_2, $b_bis_2);
                    // echo "$b_bis $b_bis_2 $b_von $b_von_2";

                    $anz_m = count($km_mon_array);
                    $sm_kalt = 0;
                    for ($m = 0; $m < $anz_m; $m++) {
                        $sm = $km_mon_array [$m] ['MONAT'];
                        $sj = $km_mon_array [$m] ['JAHR'];
                        $mk = new mietkonto ();
                        $mk->kaltmiete_monatlich_ink_vz($mv_id, $sm, $sj);
                        $sm_kalt += $mk->ausgangs_kaltmiete;
                    }

                    $summe_km_jahr_alle += $sm_kalt;
                    $sm_kalt_a = nummer_punkt2komma_t($sm_kalt);

                    if ($tage < 365) {
                        $table_arr [$z] ['EINHEIT'] = "<b>$mv->einheit_kurzname</b>";
                        $table_arr [$z] ['MIETER'] = "<b>$mv->personen_name_string</b>";
                        $table_arr [$z] ['EINZUG'] = "<b>$b_von</b>";
                        $table_arr [$z] ['AUSZUG'] = "<b>$b_bis</b>";
                        $table_arr [$z] ['BETRIEBSKOSTEN'] = "<b>$summe_nebenkosten_jahr_a</b>";
                        $table_arr [$z] ['HEIZKOSTEN'] = "<b>$summe_hk_jahr_a</b>";
                        $table_arr [$z] ['KM'] = "<b>$sm_kalt_a</b>";
                        // echo "<tr><td class=\"rot\">$mv->einheit_kurzname</td><td class=\"rot\">$mv->personen_name_string</td><td class=\"rot\">$b_von</td><td class=\"rot\">$b_bis</td><td class=\"rot\">$tage</td><td class=\"rot\">$summe_nebenkosten_jahr</td><td class=\"rot\">$summe_hk_jahr</td></tr>";
                        $z++;
                    } else {
                        // echo "<tr ><td>$mv->einheit_kurzname</td><td>$mv->personen_name_string</td><td>$b_von</td><td>$b_bis</td><td>$tage</td><td>$summe_nebenkosten_jahr</td><td>$summe_hk_jahr</td></tr>";
                        $table_arr [$z] ['EINHEIT'] = $mv->einheit_kurzname;
                        $table_arr [$z] ['MIETER'] = $mv->personen_name_string;
                        $table_arr [$z] ['EINZUG'] = $b_von;
                        $table_arr [$z] ['AUSZUG'] = $b_bis;
                        $table_arr [$z] ['BETRIEBSKOSTEN'] = $summe_nebenkosten_jahr_a;
                        $table_arr [$z] ['HEIZKOSTEN'] = $summe_hk_jahr_a;
                        $table_arr [$z] ['KM'] = "<b>$sm_kalt_a</b>";
                        $z++;
                    }
                    $sum_km_einheit_jahr += $sm_kalt;
                    $sum_bk_einheit_jahr += $summe_nebenkosten_jahr;
                    $sum_hk_einheit_jahr += $summe_hk_jahr;
                } else {
                    $einheit_kn = $arr [$a] ['EINHEIT_KURZNAME'];
                    // echo "<tr><td class=\"gruen\">$einheit_kn</td><td class=\"gruen\"><b>LEERSTAND</b></td><td>$b_von</td><td>$b_bis</td><td>$tage</td><td></td><td></td></tr>";
                    $table_arr [$z] ['EINHEIT'] = "<u><b>$einheit_kn</b></u>";
                    $table_arr [$z] ['MIETER'] = "<u><b>LEERSTAND</b></u>";
                    $table_arr [$z] ['EINZUG'] = "<u><b>$b_von</b></u>";
                    $table_arr [$z] ['AUSZUG'] = "<u><b>$b_bis</b></u>";
                    $summe_nebenkosten_jahr = $mz->summe_nebenkosten_im_jahr('MIETVERTRAG', $mv_id, $jahr);
                    $summe_nebenkosten_jahr_a = nummer_punkt2komma_t($summe_nebenkosten_jahr);
                    $summe_hk_jahr = $mz->summe_heizkosten_im_jahr('MIETVERTRAG', $mv_id, $jahr);
                    $summe_hk_jahr_a = nummer_punkt2komma_t($summe_hk_jahr);
                    $table_arr [$z] ['BETRIEBSKOSTEN'] = "<u><b>$summe_nebenkosten_jahr_a</b></u>";
                    $table_arr [$z] ['HEIZKOSTEN'] = "<u><b>$summe_hk_jahr_a</b></u>";
                    $z++;
                }

                $summe_nebenkosten_jahr_alle += $summe_nebenkosten_jahr;
                $summe_hk_jahr_alle += $summe_hk_jahr;
            }
            /* Zwischensumme */
            if ($anz1 > 1) {
                $sum_bk_einheit_jahr_a = nummer_punkt2komma_t($sum_bk_einheit_jahr);
                $sum_hk_einheit_jahr_a = nummer_punkt2komma_t($sum_hk_einheit_jahr);
                $sum_km_einheit_jahr_a = nummer_punkt2komma_t($sum_km_einheit_jahr);
                $table_arr [$z] ['EINHEIT'] = "<i><b>$einheit_kn</b></i>";
                $table_arr [$z] ['MIETER'] = "<i><b>JAHRESSUMME für $einheit_kn</b></i>";
                $table_arr [$z] ['BETRIEBSKOSTEN'] = "<u><b>$sum_bk_einheit_jahr_a</b></u>";
                $table_arr [$z] ['HEIZKOSTEN'] = "<u><b>$sum_hk_einheit_jahr_a</b></u>";
                $table_arr [$z] ['KM'] = "<u><b>$sum_km_einheit_jahr_a</b></u>";

                $z++;
            }

            $table_arr [$z] ['EINHEIT'] = '        ';
            $z++;
        }
        $summe_nebenkosten_jahr_alle_a = nummer_punkt2komma_t($summe_nebenkosten_jahr_alle);
        $summe_hk_jahr_alle_a = nummer_punkt2komma_t($summe_hk_jahr_alle);
        $summe_km_jahr_alle_a = nummer_punkt2komma_t($summe_km_jahr_alle);
        $table_arr [$z] ['BETRIEBSKOSTEN'] = "<u><b>$summe_nebenkosten_jahr_alle_a</b></u>";
        $table_arr [$z] ['HEIZKOSTEN'] = "<u><b>$summe_hk_jahr_alle_a</b></u>";
        $table_arr [$z] ['KM'] = "<u><b>$summe_km_jahr_alle_a</b></u>";

        ob_clean(); // ausgabepuffer leeren
        $pdf = new Cezpdf ('a4', 'portrait');
        $bpdf = new b_pdf ();
        $bpdf->b_header($pdf, 'Partner', session()->get('partner_id'), 'portrait', 'Helvetica.afm', 6);

        ob_clean(); // ausgabepuffer leeren
        $cols = array(
            'EINHEIT' => "Einheit",
            'MIETER' => "Mieter",
            'EINZUG' => "Einzug",
            'AUSZUG' => "Auszug",
            'BETRIEBSKOSTEN' => "BK",
            'HEIZKOSTEN' => "HK",
            'KM' => "Kaltmiete"
        );
        $pdf->ezTable($table_arr, $cols, "Soll - Nebenkosten/Kaltmiete für das Jahr $jahr", array(
            'showHeadings' => 1,
            'shaded' => 1,
            'titleFontSize' => 8,
            'fontSize' => 7,
            'xPos' => 50,
            'xOrientation' => 'right',
            'width' => 500,
            'cols' => array(
                'EINHEIT' => array(
                    'justification' => 'left',
                    'width' => 75
                ),
                'MIETER' => array(
                    'justification' => 'left',
                    'width' => 175
                ),
                'EINZUG' => array(
                    'justification' => 'right',
                    'width' => 46
                ),
                'AUSZUG' => array(
                    'justification' => 'right',
                    'width' => 46
                ),
                'BETRIEBSKOSTEN' => array(
                    'justification' => 'right',
                    'width' => 60
                ),
                'HEIZKOSTEN' => array(
                    'justification' => 'right',
                    'width' => 60
                ),
                'KM' => array(
                    'justification' => 'right',
                    'width' => 60
                )
            )
        ));

        if (!request()->exists('xls')) {
            ob_end_clean(); // ausgabepuffer leeren
            $pdf->ezStream();
        } else {
            ob_clean(); // ausgabepuffer leeren
            $oo = new objekt ();
            $oo->get_objekt_infos($objekt_id);
            $fileName = "$oo->objekt_kurzname Sollhochrechnung $jahr" . '.xls';
            header("Content-Type: application/vnd.ms-excel");
            header("Content-Disposition: inline; filename=$fileName");
            ob_clean(); // ausgabepuffer leeren
            echo "<table class=\"sortable\" id=\"positionen_tab\">";
            echo "<thead>";
            echo "<tr>";
            echo "<th>EINHEIT</th>";
            echo "<th>MIETER</th>";
            echo "<th>EINZUG</th>";
            echo "<th>AUSZUG</th>";
            echo "<th>BK-SOLL</th>";
            echo "<th>HK-SOLL</th>";
            echo "<th>KALTMIETE-SOLL</th>";
            echo "</tr>";
            echo "</thead>";

            $anz_zeilen = count($table_arr);
            for ($ze = 0; $ze < $anz_zeilen; $ze++) {
                if (isset ($table_arr [$ze] ['EINHEIT'])) {
                    $einheit_kn = $table_arr [$ze] ['EINHEIT'];
                } else {
                    $einheit_kn = '';
                }
                if (isset ($table_arr [$ze] ['MIETER'])) {
                    $mieter_n = $table_arr [$ze] ['MIETER'];
                } else {
                    $mieter_n = '';
                }
                if (isset ($table_arr [$ze] ['EINZUG'])) {
                    $von = $table_arr [$ze] ['EINZUG'];
                } else {
                    $von = '';
                }

                if (isset ($table_arr [$ze] ['AUSZUG'])) {
                    $bis = $table_arr [$ze] ['AUSZUG'];
                } else {
                    $bis = '';
                }
                if (isset ($table_arr [$ze] ['BETRIEBSKOSTEN'])) {
                    $bk = $table_arr [$ze] ['BETRIEBSKOSTEN'];
                } else {
                    $bk = '';
                }
                if (isset ($table_arr [$ze] ['HEIZKOSTEN'])) {
                    $hk = $table_arr [$ze] ['HEIZKOSTEN'];
                } else {
                    $hk = '';
                }
                if (isset ($table_arr [$ze] ['KM'])) {
                    $km = $table_arr [$ze] ['KM'];
                } else {
                    $km = '';
                }

                echo "<tr><td>$einheit_kn</td><td>$mieter_n</td><td>$von</td><td>$bis</td><td>$bk</td><td>$hk</td><td>$km</td></tr>";
            }
            echo "</table>";
        }
    }

    function mieten_pdf($objekt_id, $datum_von, $datum_bis)
    {
        echo "VARS: $objekt_id, $datum_von, $datum_bis";
        $arr = $this->mv_arr_zeitraum($objekt_id, $datum_von, $datum_bis);
        if (empty($arr)) {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\ErrorMessage("Bitte wählen Sie ein Objekt und einen Zeitraum.")
            );
        } else {
            echo "<pre>";
            $anz_mvs = count($arr);
            $mz = new miete ();
            $monate = $mz->diff_in_monaten($datum_von, $datum_bis);
            $datum_von_arr = explode('-', $datum_von);
            $start_m = $datum_von_arr [1];
            $start_j = $datum_von_arr [0];

            $datum_bis_arr = explode('-', $datum_bis);

            /* Schleife für jeden Monat */
            $monat = $start_m;
            $jahr = $start_j;
            $summe_g = 0.00;
            for ($a = 0; $a < $monate; $a++) {
                $monat = sprintf('%02d', $monat);
                for ($b = 0; $b < $anz_mvs; $b++) {
                    $mv_id = $arr [$b] ['MIETVERTRAG_ID'];
                    // echo "$monat.$jahr = $mv_id<br>";
                    // $n_arr[$b]['MV_ID']=$mv_id;
                    // $mk = new mietkonto();
                    $this->get_mietvertrag_infos_aktuell($mv_id);
                    $n_arr [$b] ['EINHEIT'] = $this->einheit_kurzname;
                    $n_arr [$b] ['TYP'] = $this->einheit_typ;
                    $n_arr [$b] ['MIETER'] = $this->personen_name_string;
                    if ($this->mietvertrag_bis_d == '00.00.0000') {
                        $this->mietvertrag_bis_d = '';
                    }
                    $n_arr [$b] ['MIETZEIT'] = "$this->mietvertrag_von_d - $this->mietvertrag_bis_d";
                    $mietsumme = $this->summe_forderung_monatlich($mv_id, $monat, $jahr);
                    $n_arr [$b] ["$monat.$jahr"] = $mietsumme;
                    $n_arr [$b] ["SUMME"] += $mietsumme;
                    $summe_g += $mietsumme;
                    $sum = $n_arr [$b] ["SUMME"];
                    $n_arr [$b] ["SUMME"] = number_format($sum, 2, '.', '');
                    $n_arr [$b] ["SUMME_A"] = nummer_punkt2komma_t($sum);

                    // 1234.57
                }
                // $n_arr[$anz_mvs]["$monat.$jahr"] += $n_arr[$a]["$monat.$jahr"];
                $cols ["$monat.$jahr"] = "$monat.$jahr";

                $monat++;
                $monat = sprintf('%02d', $monat);

                if ($monat > 12) {
                    $monat = 1;
                    $jahr++;
                }
            }
            // print_r($n_arr);

            ob_clean(); // ausgabepuffer leeren
            $pdf = new Cezpdf ('a4', 'landscape');
            $bpdf = new b_pdf ();
            $bpdf->b_header($pdf, 'Partner', session()->get('partner_id'), 'landscape', 'Helvetica.afm', 6);

            $n_arr [$anz_mvs] ['SUMME_A'] = "<b>" . nummer_punkt2komma_t($summe_g) . "</b>";
            $n_arr [$anz_mvs] ['MIETER'] = "<b>Gesamt Sollmieten Nettokalt</b>";

            ob_clean(); // ausgabepuffer leeren

            $cols1 ['EINHEIT'] = 'Einheit';
            $cols1 ['TYP'] = 'Typ';
            $cols1 ['MIETER'] = 'Mieter';
            $cols1 ['MIETZEIT'] = 'Mietzeit';
            $cols1 ['01.2013'] = '01.2013';
            $cols1 ['02.2013'] = '02.2013';
            $cols1 ['03.2013'] = '03.2013';
            $cols1 ['04.2013'] = '04.2013';
            $cols1 ['05.2013'] = '05.2013';
            $cols1 ['06.2013'] = '06.2013';
            $cols1 ['07.2013'] = '07.2013';
            $cols1 ['08.2013'] = '08.2013';
            $cols1 ['09.2013'] = '09.2013';
            $cols1 ['10.2013'] = '10.2013';
            $cols1 ['11.2013'] = '11.2013';
            $cols1 ['12.2013'] = '12.2013';
            $cols1 ['SUMME_A'] = 'BETRAG';

            // $pdf->ezTable($n_arr,$cols1,"Nebenkostenhochrechnung für das Jahr $jahr vom $datum_h",array('showHeadings'=>1,'shaded'=>1, 'titleFontSize' => 8, 'fontSize' => 7, 'xPos'=>50,'xOrientation'=>'right', 'width'=>500,'cols'=>array('EINHEIT'=>array('justification'=>'left', 'width'=>75),'MIETER'=>array('justification'=>'left', 'width'=>175), 'EINZUG'=>array('justification'=>'right','width'=>50),'AUSZUG'=>array('justification'=>'right','width'=>50),'BETRIEBSKOSTEN'=>array('justification'=>'right','width'=>75), 'HEIZKOSTEN'=>array('justification'=>'right','width'=>75))));
            $datum_von_d = date_mysql2german($datum_von);
            $datum_bis_d = date_mysql2german($datum_bis);
            // $pdf->ezTable($n_arr, $cols1, "Vereinbarte Sollkaltmieten im Zeitraum: $datum_von_d - $datum_bis_d", array('showHeadings'=>1,'shaded'=>1, 'width'=>500, 'titleFontSize' => 8, 'fontSize' => 7, 'xPos'=>50,'xOrientation'=>'right', 'cols'=>array('SUMME_A'=>array('justification'=>'right'))));
            // sort($n_arr);
            $pdf->ezTable($n_arr, $cols1, "Vereinbarte Sollkaltmieten im Zeitraum: $datum_von_d - $datum_bis_d", array(
                'showHeadings' => 1,
                'shaded' => 1,
                'titleFontSize' => 8,
                'fontSize' => 6.5,
                'xPos' => 50,
                'xOrientation' => 'right',
                'cols' => array(
                    'SUMME_A' => array(
                        'justification' => 'right'
                    )
                )
            ));
            ob_end_clean(); // ausgabepuffer leeren
            $pdf->ezSetDy(-20);
            $pdf->ezText("     Druckdatum: " . date("d.m.Y"), 11);
            $pdf->ezStream();
        }
    }

    function mv_arr_zeitraum($objekt_id, $datum_von, $datum_bis)
    {
        $db_abfrage = "SELECT * FROM `MIETVERTRAG` WHERE (MIETVERTRAG_VON<='$datum_bis' && (MIETVERTRAG_BIS='0000-00-00' OR (MIETVERTRAG_BIS >= '$datum_von'))) && `MIETVERTRAG_AKTUELL` = '1' && MIETVERTRAG.EINHEIT_ID IN (SELECT EINHEIT_ID FROM EINHEIT, HAUS, OBJEKT WHERE EINHEIT.HAUS_ID=HAUS.HAUS_ID && HAUS.OBJEKT_ID='$objekt_id' && EINHEIT.EINHEIT_AKTUELL='1') ORDER BY EINHEIT_ID, MIETVERTRAG_VON ASC";
        $result = DB::select($db_abfrage);
        return $result;
    }

    function summe_forderung_monatlich($mietvertrag_id, $monat, $jahr)
    {
        $monat = sprintf('%02d', $monat);
        $result = DB::select("SELECT SUM(BETRAG) AS SUMME FROM MIETENTWICKLUNG WHERE KOSTENTRAEGER_TYP='MIETVERTRAG' && KOSTENTRAEGER_ID = '$mietvertrag_id' && MIETENTWICKLUNG_AKTUELL = '1' && ( ENDE = '0000-00-00' OR DATE_FORMAT( ENDE, '%Y-%m' ) >= '$jahr-$monat') && DATE_FORMAT( ANFANG, '%Y-%m' ) <= '$jahr-$monat'  && (KOSTENKATEGORIE = 'Miete kalt' OR KOSTENKATEGORIE = 'MOD' OR KOSTENKATEGORIE = 'MHG')");
        if (empty($result)) {
            return false;
        } else {
            $row = $result[0];
            return $row ['SUMME'];
        }
    }

    function istmieten_zeitraum($gk_id, $von, $bis, $kto = '80001')
    {
        $db_abfrage = "SELECT  `KOSTENTRAEGER_TYP` ,  `KOSTENTRAEGER_ID` , SUM( BETRAG ) AS SUMME 
FROM  `GELD_KONTO_BUCHUNGEN` 
WHERE  `GELDKONTO_ID` =614
AND  `KONTENRAHMEN_KONTO` =80001
AND  `DATUM` 
BETWEEN  '$von'
AND  '$bis'
AND  `AKTUELL` =  '1'
GROUP BY  `KOSTENTRAEGER_TYP` ,  `KOSTENTRAEGER_ID` ";
        $result = DB::select($db_abfrage);
        if (!empty($result)) {
            $z = 0;
            $summe = 0;
            foreach ($result as $row) {
                $kos_id = $row ['KOSTENTRAEGER_ID'];
                $summe += $row ['SUMME'];
                $this->get_mietvertrag_infos_aktuell($kos_id);
                print_r($this);

                $arr [$z] = $row;
                $arr [$z] ['EINHEIT'] = $this->einheit_kurzname;
                $arr [$z] ['MIETER'] = $this->personen_name_string_u;
                $arr [$z] ['EINZUG'] = $this->mietvertrag_von_d;
                $arr [$z] ['AUSZUG'] = $this->mietvertrag_bis_d;

                $z++;
            }
            $arr [$z + 1] ['SUMME'] = nummer_punkt2komma_t($summe);
            return $arr;
        }
    }

    function pdf_istmieten($arr, $von, $bis)
    {
        ob_clean(); // ausgabepuffer leeren
        $pdf = new Cezpdf ('a4', 'portrait');
        $bpdf = new b_pdf ();
        $bpdf->b_header($pdf, 'Partner', session()->get('partner_id'), 'portrait', 'Helvetica.afm', 6);

        $cols = array(
            'EINHEIT' => "EINHEIT",
            'MIETER' => "MIETER",
            'EINZUG' => "EINZUG",
            'AUSZUG' => "AUSZUG",
            'SUMME' => 'SUMME'
        );

        // $pdf->ezTable($arr, null, "IST-Einnahmen Kaltmieten im Zeitraum: $datum_von_d - $datum_bis_d", array('showHeadings'=>1,'shaded'=>1, 'titleFontSize' => 8, 'fontSize' => 6.5, 'xPos'=>50,'xOrientation'=>'right', 'cols'=>array('SUMME_A'=>array('justification'=>'right'))));
        $pdf->ezTable($arr, $cols, "Bruttomieteinnahmen $von-$bis", array(
            'showHeadings' => 1,
            'shaded' => 0,
            'titleFontSize' => 8,
            'fontSize' => 7,
            'xPos' => 50,
            'xOrientation' => 'right',
            'width' => 500,
            'cols' => array(
                'SUMME' => array(
                    'justification' => 'right',
                    'width' => 70
                )
            )
        ));

        ob_end_clean(); // ausgabepuffer leeren
        $pdf->ezSetDy(-20);
        $pdf->ezText("     Druckdatum: " . date("d.m.Y"), 9);
        $pdf->ezStream();
    }
    
    function form_mietvertrag_loeschen($mv_id)
    {
        if (!request()->has('send_ja') && !request()->has('send_nein')) {
            $this->get_mietvertrag_infos_aktuell($mv_id);
            $f = new formular ();
            $f->fieldset('Mietvertrag löschen', 'mvl');
            echo "<div>";
            echo "<br><b>Sind Sie sicher, dass Sie den Mietvertrag $mv_id für die Einheit $this->einheit_kurzname löschen wollen?</b><br>";
            echo "<br>Einheit: $this->einheit_kurzname";
            echo "<br>Personen: $this->personen_name_string_u";
            echo "<br>Einzug: $this->mietvertrag_von_d";
            echo "<br>Auszug: $this->mietvertrag_bis_d";
            echo "<br><br>";
            $f->hidden_feld('mv_id', $mv_id);
            $f->send_button('send_ja', 'Mietvertrag löschen');
            $f->send_button('send_nein', 'Abbrechen und zurück');
            echo "</div>";
            $f->fieldset_ende();
        }
        if (request()->has('send_nein')) {
            weiterleiten(route('web::mietvertraege::legacy', ['mietvertrag_raus' => 'mietvertrag_kurz'], false));
        }
        if (request()->has('send_ja')) {
            $this->mv_loeschen_alles($mv_id);
        }
    }

    function mv_loeschen_alles($mv_id)
    {
        DB::update("UPDATE MIETVERTRAG SET MIETVERTRAG_AKTUELL='0' WHERE MIETVERTRAG_ID='$mv_id'");
        DB::update("UPDATE PERSON_MIETVERTRAG SET PERSON_MIETVERTRAG_AKTUELL='0' WHERE PERSON_MIETVERTRAG_MIETVERTRAG_ID='$mv_id'");
        DB::update("UPDATE MIETENTWICKLUNG SET MIETENTWICKLUNG_AKTUELL='0' WHERE KOSTENTRAEGER_TYP LIKE 'MIETVERTRAG' && KOSTENTRAEGER_ID = '$mv_id'");
        DB::update("UPDATE DETAIL SET DETAIL_AKTUELL='0' WHERE DETAIL_ZUORDNUNG_TABELLE LIKE 'MIETVERTRAG' && DETAIL_ZUORDNUNG_ID = '$mv_id'");
        echo "Mietvertrag wurde gelöscht!";
    }
} // end classs
