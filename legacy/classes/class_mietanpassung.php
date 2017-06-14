<?php
/*
 * 330, 663, 998 auf 12 stellen
 */

/* Klasse für die Mietanpassung an Mietspiegel */

class mietanpassung
{

    public $m_wert;
    public $o_wert;
    public $ausstattungsklasse;
    public $u_wert;
    public $erhoehungsbetrag;
    public $erhoehungsdatum;
    public $naechste_erhoehung_datum;
    public $naechste_erhoehung_betrag;
    public $abzug_wert;
    public $einheit_qm;
    public $objekt_baujahr;
    public $objekt_bauart;
    public $haus_wohnlage;
    public $u_wert_w;
    public $m_wert_w;
    public $o_wert_w;
    public $objekt_ost;
    public $monatlich_mehr_mieter;
    public $zuabschlag;
    public $zuabschlag_a;
    public $abzug_wert_a;
    public $spalte;
    public $footer_zahlungshinweis;

    function nettomieter_daten_arr($objekt_id, $mieterart = 'Nettomieter')
    {
        $o = new objekt ();
        $einheiten_arr = $o->einheiten_objekt_arr($objekt_id);
        $anzahl = count($einheiten_arr);
        $summe_m_mehr = 0;
        $ms_jahr = $this->get_ms_jahr();

        for ($a = 0; $a < $anzahl; $a++) {
            $einheit_id = $einheiten_arr [$a] ['EINHEIT_ID'];
            $einheit_qm = $einheiten_arr [$a] ['EINHEIT_QM'];
            $tab_arr = $this->get_einheit_daten($einheit_id, $ms_jahr);
            if (isset ($tab_arr ['MIETER_ART'])) {
                $m_mieter_art = $tab_arr ['MIETER_ART'];
                if ($einheit_qm > 0.00 && isset ($tab_arr ['MV_ID']) && $tab_arr ['MV_ID'] != '' && $mieterart == $m_mieter_art) {
                    $arr [] = $tab_arr;
                }
            }
        }
        if (isset ($arr)) {
            return $arr;
        }
    }

    function get_ms_jahr()
    {
        $db_abfrage = "SELECT JAHR FROM MIETSPIEGEL ORDER BY JAHR DESC LIMIT 0,1";
        $result = DB::select($db_abfrage);
        if (!empty($result)) {
            $row = $result[0];
            return $row ['JAHR'];
        }
    }

    function get_einheit_daten($einheit_id, $ms_jahr)
    {
        $tab_arr ['MS_JAHR'] = $ms_jahr;
        $this->abzug_wert = 0;

        $e = new einheit ();
        $e->get_einheit_info($einheit_id);
        $this->einheit_qm = $e->einheit_qm;
        $d = new detail ();
        $this->objekt_baujahr = $d->finde_detail_inhalt('OBJEKT', $e->objekt_id, 'Baujahr');
        if (empty ($this->objekt_baujahr)) {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\ErrorMessage("ABBRUCH: Einheit: $e->einheit_kurzname<br>Detail Baujahr zum Objekt $e->objekt_name hinzufügen")
            );
        }

        if ($this->objekt_baujahr <= 1918) {
            $this->objekt_bauart = 'Altbau1';
        }
        if ($this->objekt_baujahr > 1918 && $this->objekt_baujahr < 1950) {
            $this->objekt_bauart = 'Altbau2';
        }
        if ($this->objekt_baujahr > 1949) {
            $this->objekt_bauart = 'Neubau';
        }

        $this->haus_wohnlage = $d->finde_detail_inhalt('HAUS', $e->haus_id, 'Wohnlage');
        /* Wenn keine, dann bei Objekt schauen */
        if (!$this->haus_wohnlage) {
            $this->haus_wohnlage = $d->finde_detail_inhalt('OBJEKT', $e->objekt_id, 'Wohnlage');
        }
        if (!$this->haus_wohnlage) {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\ErrorMessage("Keine Wohnlage zum Haus $e->haus_strasse $e->haus_nummer oder $e->objekt_name")
            );
        }

        if ($this->einheit_qm < 40.00) {
            if ($this->haus_wohnlage == 'einfach') {
                $buchstabe = 'A';
            }
            if ($this->haus_wohnlage == 'mittel') {
                $buchstabe = 'B';
            }
            if ($this->haus_wohnlage == 'gut') {
                $buchstabe = 'C';
            }
        }

        if (($this->einheit_qm >= 40.00) && ($this->einheit_qm < 60.00)) {
            if ($this->haus_wohnlage == 'einfach') {
                $buchstabe = 'D';
            }
            if ($this->haus_wohnlage == 'mittel') {
                $buchstabe = 'E';
            }
            if ($this->haus_wohnlage == 'gut') {
                $buchstabe = 'F';
            }
        }

        if ($this->einheit_qm >= 60.00 && $this->einheit_qm < 90.00) {
            if ($this->haus_wohnlage == 'einfach') {
                $buchstabe = 'G';
            }
            if ($this->haus_wohnlage == 'mittel') {
                $buchstabe = 'H';
            }
            if ($this->haus_wohnlage == 'gut') {
                $buchstabe = 'I';
            }
        }

        if ($this->einheit_qm >= 90.00) {
            if ($this->haus_wohnlage == 'einfach') {
                $buchstabe = 'J';
            }
            if ($this->haus_wohnlage == 'mittel') {
                $buchstabe = 'K';
            }
            if ($this->haus_wohnlage == 'gut') {
                $buchstabe = 'L';
            }
        }

        /* Es können nur Mieten von vermieteten Einheiten angepasst werden und ... */
        /* Wenn Fläche größer als 0, weil sonst Parkplatz bzw Freiflächen */
        if ($e->einheit_qm > 0.00) {

            /* Schon mal vermietet */
            if ($e->get_einheit_status($einheit_id)) {
                $mv_id = $e->get_last_mietvertrag_id($einheit_id);
                /*
				 * Wenn aktuell vermietet
				 * hier spielt sich alles ab
				 */
                if (!empty ($mv_id)) {
                    $ausstattungsklasse = $d->finde_detail_inhalt('EINHEIT', $einheit_id, 'Ausstattungsklasse');
                    if (empty ($ausstattungsklasse)) {
                        throw new \App\Exceptions\MessageException(
                            new \App\Messages\ErrorMessage("ABBRUCH - Einheit: $e->einheit_kurzname hat keine Ausstattungsklasse in den Details.")
                        );
                    }
                    $tab_arr ['AUSSTATTUNGSKLASSE'] = $ausstattungsklasse;
                    $mietspiegel_feld = $this->get_ms_feld($einheit_id);

                    /* Mietvertragsinfos sammeln */
                    $mv = new mietvertraege ();
                    $mv->get_mietvertrag_infos_aktuell($mv_id);
                    /* Prüfen ob Bruttomieter */
                    if ($this->check_bruttomieter('MIETVERTRAG', $mv_id) == true) {
                        $tab_arr ['MIETER_ART'] = 'Bruttomieter';
                        $mieter_art = 'Bruttomieter';
                        // echo "$mv_id $einheit_id $mieter_art<br>";
                    } else {
                        $tab_arr ['MIETER_ART'] = 'Nettomieter';
                        $mieter_art = 'Nettomieter';
                    }

                    /* Notwendige Datumsvars setzen */
                    $o = new objekt ();
                    $datum_n_monat = $o->datum_plus_tage(date("Y-m-d"), 90);
                    $datum_n_monat_arr = explode('-', $datum_n_monat);
                    $tag_n = '01';
                    $monat_n = $datum_n_monat_arr [1];
                    $jahr_n = $datum_n_monat_arr [0];

                    $monat = date("m");
                    $jahr = date("Y");
                    $jahr_minus_3 = $jahr_n - 3;

                    $datum_miete_v_3_j = "$jahr_minus_3-$monat_n-$tag_n";
                    $datum_miete_v_3_j_a = date_mysql2german($datum_miete_v_3_j);

                    $mk = new mietkonto ();
                    /* Aktuelle Miete kalt */
                    $mk->kaltmiete_monatlich_ohne_mod($mv_id, $monat, $jahr);
                    $aktuelle_miete = $mk->ausgangs_kaltmiete;
                    if ($mieter_art == 'Bruttomieter' && request()->has('nk_anteil')) {
                        $aktuelle_miete = $mk->ausgangs_kaltmiete - nummer_komma2punkt(request()->input('nk_anteil')) / 12;
                        $aktuelle_miete_brutto = $mk->ausgangs_kaltmiete;
                    }
                    /* Miete kalt vor 3 Jahren */
                    $mk->kaltmiete_monatlich_ohne_mod($mv_id, $monat_n, $jahr_minus_3);
                    // $this->kosten_monatlich($mv_id,$monat,$jahr_minus_3, 'Miete kalt');
                    $miete_vor_3_jahren = $mk->ausgangs_kaltmiete;
                    if ($mieter_art == 'Bruttomieter' && request()->has('nk_anteil')) {
                        $miete_vor_3_jahren = $mk->ausgangs_kaltmiete - nummer_komma2punkt(request()->input('nk_anteil')) / 12;
                    }

                    /*
					 * Wenn MV nicht älter als 3 Jahre dann Erste Miete kalt
					 * aus Mietdefinition d.h. Miete beim Einzug
					 */
                    if ($miete_vor_3_jahren <= 0.00) {
                        $einzugsdatum_arr = explode('-', $mv->mietvertrag_von);
                        $einzugs_jahr = $einzugsdatum_arr [0];
                        $einzugs_monat = $einzugsdatum_arr [1];
                        /* Bei Einzug mitten im Monat ist es nur die Hälfte */
                        $mk->kaltmiete_monatlich($mv_id, $einzugs_monat, $einzugs_jahr);
                        // echo "$mv_id,$einzugs_monat,$einzugs_jahr";
                        $miete_beim_einzug = $mk->ausgangs_kaltmiete;
                        $miete_vor_3_jahren = $miete_beim_einzug;
                        /* Wenn keine Mietdefinition zum MV Anfang dann Miete aus der Mietdefinition 2. Monat */
                        if ($miete_vor_3_jahren <= 0.00) {
                            $datum_1_kmiete = $this->datum_1_mietdefinition($mv_id);
                            $datum_1_kmiete_arr = explode('-', $datum_1_kmiete);
                            $datum_1_kmiete_jahr = $datum_1_kmiete_arr [0];
                            $datum_1_kmiete_monat = $datum_1_kmiete_arr [1];
                            $mk->kaltmiete_monatlich($mv_id, $datum_1_kmiete_monat, $datum_1_kmiete_jahr);
                            $erste_kalt_miete = $mk->ausgangs_kaltmiete;
                            $miete_vor_3_jahren = $erste_kalt_miete;
                        }
                    }

                    $tab_arr ['EINHEIT'] = $e->einheit_kurzname;
                    $tab_arr ['EINHEIT_ID'] = $einheit_id;
                    $tab_arr ['EINHEIT_QM'] = $e->einheit_qm;
                    $tab_arr ['WOHNLAGE'] = $this->haus_wohnlage;
                    $tab_arr ['MIETER'] = $mv->personen_name_string;
                    $tab_arr ['MV_ID'] = $mv->mietvertrag_id;
                    $tab_arr ['EINZUG'] = $mv->mietvertrag_von;
                    $tab_arr ['MIETE_3_JAHRE'] = $miete_vor_3_jahren;
                    $tab_arr ['DATUM_3_JAHRE'] = $datum_miete_v_3_j_a;
                    $tab_arr ['MIETE_AKTUELL'] = $aktuelle_miete;
                    if ($mieter_art == 'Bruttomieter' && request()->has('nk_anteil')) {
                        $tab_arr ['MIETE_AKTUELL_BRUTTO'] = $aktuelle_miete_brutto;
                    }
                    // echo "$ms_jahr $mietspiegel_feld";
                    $this->get_spiegel_werte($ms_jahr, $mietspiegel_feld);
                    $tab_arr ['MS_FELD'] = $mietspiegel_feld;
                    $tab_arr ['U_WERT'] = $this->u_wert;
                    $tab_arr ['M_WERT'] = $this->m_wert;
                    $tab_arr ['O_WERT'] = $this->o_wert;
                    $untere_spanne = $this->m_wert - $this->u_wert;
                    $obere_spanne = $this->o_wert - $this->m_wert;
                    $tab_arr ['U_SPANNE'] = $untere_spanne;
                    $tab_arr ['O_SPANNE'] = $obere_spanne;

                    /* Erdgeschoss aus Lage erkennen */
                    $m_buchstabe = substr($e->einheit_lage, 1, 1);

                    /* Sondermerkmale finden */
                    $sondermerkmale_arr = $this->get_sondermerkmale_arr($ausstattungsklasse, $jahr);
                    // echo "<pre>";
                    // print_r($sondermerkmale_arr);

                    $anz_sm = count($sondermerkmale_arr);
                    if ($anz_sm > 0) {
                        $abzug_zaehler = 0;
                        $this->abzug_wert = 0;

                        for ($s = 0; $s < $anz_sm; $s++) {
                            $merkmal = $sondermerkmale_arr [$s] ['MERKMAL'];
                            $wert = $sondermerkmale_arr [$s] ['WERT'];
                            $a_klasse = $sondermerkmale_arr [$s] ['A_KLASSE'];
                            if ($a_klasse == NULL or $ausstattungsklasse == $a_klasse) {
                                /* Wenn z.B. Erdgeschoss, dann Abzug */
                                $sonder_abzug = $d->finde_detail_inhalt('EINHEIT', $einheit_id, $merkmal);
                                if ($sonder_abzug) {
                                    $tab_arr ['ABZUEGE'] [$abzug_zaehler] ['MERKMAL'] = $merkmal;
                                    $tab_arr ['ABZUEGE'] [$abzug_zaehler] ['MERKMAL_GRUND'] = $sonder_abzug;
                                    $tab_arr ['ABZUEGE'] [$abzug_zaehler] ['MERKMAL_WERT'] = $wert;
                                    $this->abzug_wert = $this->abzug_wert + $wert;
                                    $abzug_zaehler++;
                                }
                            }
                        } // end for
                    } // end wenn Sondermerkmale vorhanden

                    /* Wenn ABZUEGE vorhanden, dann MS werte anpassen */
                    if (isset ($tab_arr ['ABZUEGE'])) {
                        $this->u_wert_w = $this->u_wert + $this->abzug_wert;
                        $this->m_wert_w = $this->m_wert + $this->abzug_wert;
                        $this->o_wert_w = $this->o_wert + $this->abzug_wert;
                        $tab_arr ['ABZUG_PRO_M2'] = $this->abzug_wert;
                    } else {
                        /* Sonst sind die MS-Werte maßgeblich */
                        $tab_arr ['ABZUG_PRO_M2'] = '0.00';
                        $this->u_wert_w = $this->u_wert;
                        $this->m_wert_w = $this->m_wert;
                        $this->o_wert_w = $this->o_wert;
                    }

                    $tab_arr ['U_WERT_W'] = $this->u_wert_w;
                    $tab_arr ['M_WERT_W'] = $this->m_wert_w;
                    $tab_arr ['O_WERT_W'] = $this->o_wert_w;
                    /* Preisspanne nach Abzügen ermitteln */
                    $untere_spanne_w = $this->m_wert_w - $this->u_wert_w;
                    $obere_spanne_w = $this->o_wert_w - $this->m_wert_w;
                    $tab_arr ['U_SPANNE_W'] = $untere_spanne_w;
                    $tab_arr ['O_SPANNE_W'] = $obere_spanne_w;

                    $tab_arr ['GESAMT_ABZUG'] = $e->einheit_qm * $this->abzug_wert;

                    /* Berechnung */
                    $m2_mietpreis = $aktuelle_miete / $e->einheit_qm;
                    $tab_arr ['M2_AKTUELL'] = $m2_mietpreis;
                    $anstieg_in_3_jahren = $aktuelle_miete / ($miete_vor_3_jahren / 100) - 100;
                    if ($miete_vor_3_jahren == 0.00) {
                        $ee = new einheit ();
                        $ee->get_einheit_info($einheit_id);
                        echo "<h5>MIETE VOR 3 Jahren = 0 , bitte prüfen!!!...<br>EINHEIT: $ee->einheit_kurzname MV:ID$mv_id</h5>";
                    }
                    $tab_arr ['ANSTIEG_3J'] = $anstieg_in_3_jahren;
                    $max_rest_prozent = 15 - $anstieg_in_3_jahren;
                    $tab_arr ['MAX_ANSTIEG_PROZ'] = $max_rest_prozent;
                    $anstieg_euro = ($miete_vor_3_jahren / 100) * $max_rest_prozent;
                    $tab_arr ['MAX_ANSTIEG_EURO'] = $anstieg_euro;
                    $kappungsgrenze_miete = $aktuelle_miete + $anstieg_euro;
                    $tab_arr ['MAXIMALE_MIETE'] = $kappungsgrenze_miete;

                    /* Letzte Erhöhung */
                    $this->datum_letzte_m_erhoehung($mv_id);
                    $o = new objekt ();
                    $monate_seit_l_erhoehung = $o->monate_berechnen_bis_heute($this->erhoehungsdatum);

                    $tab_arr ['L_ANSTIEG_MONATE'] = $monate_seit_l_erhoehung;
                    $tab_arr ['L_ANSTIEG_DATUM'] = $this->erhoehungsdatum;
                    $tab_arr ['L_ANSTIEG_BETRAG'] = $this->erhoehungsbetrag;

                    $datum_vor_3_jahren = $datum_miete_v_3_j;
                    $erhoehungen_arr = $this->get_erhoehungen_arr($datum_vor_3_jahren, 'MIETVERTRAG', $mv_id);
                    $tab_arr ['ERHOEHUNGEN_ARR'] = $erhoehungen_arr;
                    /* Maximal möglich rechnerisch nur */

                    $n_erhoehungsdatum_arr = explode('-', $o->datum_plus_tage(date("Y-m-d"), 90));
                    $n_erhoehungsdatum = '01.' . $n_erhoehungsdatum_arr [1] . '.' . $n_erhoehungsdatum_arr [0];
                    if (empty ($this->m_wert_w)) {
                        $n_miete_mwert = $e->einheit_qm * $this->m_wert;
                        $n_miete_mwert_w = $e->einheit_qm * $this->m_wert;
                    } else {
                        $n_miete_mwert = $e->einheit_qm * $this->m_wert;
                        $n_miete_mwert_w = $e->einheit_qm * $this->m_wert_w;
                    }

                    $tab_arr ['N_ANSTIEG_DATUM'] = $n_erhoehungsdatum;
                    $tab_arr ['NEUE_MIETE_M_WERT'] = $n_miete_mwert;
                    $tab_arr ['NEUE_MIETE_M_WERT_W'] = $n_miete_mwert_w;

                    $this->check_erhoehung($mv_id);

                    /* Wenn Letzte Erhöhung vor mehr als 12 Monaten */
                    if ($monate_seit_l_erhoehung > 12) {
                        /* Wenn Mittelwert größer als Kappungsgrenze, dann mit Kappung rechnen */
                        if ($n_miete_mwert_w > $kappungsgrenze_miete) {
                            $n_preis_pro_qm = $kappungsgrenze_miete / $e->einheit_qm;
                            $monatliche_diff = $kappungsgrenze_miete - $aktuelle_miete;
                            $tab_arr ['NEUE_MIETE'] = $kappungsgrenze_miete;
                            $tab_arr ['ANSTIEG_UM_PROZENT'] = $max_rest_prozent;
                            $tab_arr ['M2_PREIS_NEU'] = $n_preis_pro_qm;
                            $tab_arr ['MONATLICH_MEHR'] = $monatliche_diff;
                        } else {
                            $n_preis_pro_qm = $n_miete_mwert_w / $e->einheit_qm;
                            $monatliche_diff = $n_miete_mwert_w - $aktuelle_miete;
                            $tab_arr ['NEUE_MIETE'] = $n_miete_mwert_w;
                            if ($aktuelle_miete > 0) {
                                $anstieg_in_prozent = ($n_miete_mwert_w / ($aktuelle_miete / 100)) - 100;
                            } else {
                                $anstieg_in_prozent = 0.00;
                            }
                            $tab_arr ['ANSTIEG_UM_PROZENT'] = $anstieg_in_prozent;
                            $tab_arr ['M2_PREIS_NEU'] = $n_preis_pro_qm;
                            $tab_arr ['MONATLICH_MEHR'] = $monatliche_diff;
                        }
                    } else {
                        /* Sonst gesetzlich nicht möglich die Miete anzupassen */
                        $tab_arr ['NEUE_MIETE'] = 'nicht möglich';
                        $tab_arr ['ANSTIEG_UM_PROZENT'] = 'nicht möglich';
                        $tab_arr ['M2_PREIS_NEU'] = 'nicht möglich';
                        $tab_arr ['MONATLICH_MEHR'] = 'nicht möglich';
                        $tab_arr ['N_ANSTIEG_DATUM'] = 'nicht möglich';
                    }

                    /* Wenn eine Erhöhung schon definiert wurde */
                    if (isset ($this->naechste_erhoehung_datum)) {
                        $this->naechste_erhoehung_datum = date_mysql2german($this->naechste_erhoehung_datum);
                        $tab_arr ['STATUS'] = 'erledigt';
                        $tab_arr ['STATUS_DATUM'] = $this->naechste_erhoehung_datum;
                        $tab_arr ['STATUS_BETRAG'] = $this->naechste_erhoehung_betrag;
                    } else {
                        $tab_arr ['STATUS'] = 'offen';
                        $tab_arr ['STATUS_DATUM'] = '';
                        $tab_arr ['STATUS_BETRAG'] = '';
                    }
                } // end if vermietet jetzt
            } // end if schon mal vermietet, danach ende der Funktion
        } // wenn fläche >0.00
        return $tab_arr;
    }

    function get_ms_feld($einheit_id)
    {
        $e = new einheit ();
        $e->get_einheit_info($einheit_id);
        $this->einheit_qm = $e->einheit_qm;
        $d = new detail ();
        $this->objekt_baujahr = $d->finde_detail_inhalt('OBJEKT', $e->objekt_id, 'Baujahr');
        if (empty ($this->objekt_baujahr)) {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\ErrorMessage("<b>ABBRUCH: Einheit: $e->einheit_kurzname <br>Detail Baujahr zum Objekt $e->objekt_name hinzufügen</b>")
            );
        }

        if ($this->objekt_baujahr <= 1918) {
            $this->objekt_bauart = 'Altbau1';
        }
        if ($this->objekt_baujahr > 1918 && $this->objekt_baujahr < 1950) {
            $this->objekt_bauart = 'Altbau2';
        }
        if ($this->objekt_baujahr > 1949) {
            $this->objekt_bauart = 'Neubau';
        }

        $this->haus_wohnlage = ltrim(rtrim($d->finde_detail_inhalt('HAUS', $e->haus_id, 'Wohnlage')));
        /* Wenn keine, dann bei Objekt schauen */
        if (!$this->haus_wohnlage) {
            $this->haus_wohnlage = $d->finde_detail_inhalt('OBJEKT', $e->objekt_id, 'Wohnlage');
        }
        if (!$this->haus_wohnlage) {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\ErrorMessage("Keine Wohnlage zum Haus $e->haus_strasse $e->haus_nummer oder $e->objekt_name")
            );
        }

        if ($this->einheit_qm < 40.00) {
            if (ltrim(rtrim($this->haus_wohnlage)) == 'einfach') {
                $buchstabe = 'A';
            }
            if (ltrim(rtrim($this->haus_wohnlage)) == 'mittel') {
                $buchstabe = 'B';
            }
            if (ltrim(rtrim($this->haus_wohnlage)) == 'gut') {
                $buchstabe = 'C';
            }
        }

        if (($this->einheit_qm >= 40.00) && ($this->einheit_qm < 60.00)) {
            if (ltrim(rtrim($this->haus_wohnlage)) == 'einfach') {
                $buchstabe = 'D';
            }
            if (ltrim(rtrim($this->haus_wohnlage)) == 'mittel') {
                $buchstabe = 'E';
            }
            if (ltrim(rtrim($this->haus_wohnlage)) == 'gut') {
                $buchstabe = 'F';
            }
        }

        if ($this->einheit_qm >= 60.00 && $this->einheit_qm < 90.00) {
            if (ltrim(rtrim($this->haus_wohnlage)) == 'einfach') {
                $buchstabe = 'G';
            }
            if (ltrim(rtrim($this->haus_wohnlage)) == 'mittel') {
                $buchstabe = 'H';
            }
            if (ltrim(rtrim($this->haus_wohnlage)) == 'gut') {
                $buchstabe = 'I';
            }
        }

        if ($this->einheit_qm >= 90.00) {
            if (ltrim(rtrim($this->haus_wohnlage)) == 'einfach') {
                $buchstabe = 'J';
            }
            if (ltrim(rtrim($this->haus_wohnlage)) == 'mittel') {
                $buchstabe = 'K';
            }
            if (ltrim(rtrim($this->haus_wohnlage)) == 'gut') {
                $buchstabe = 'L';
            }
        }

        /* Es können nur Mieten von vermieteten Einheiten angepasst werden und ... */
        /* Wenn Fläche größer als 0, weil sonst Parkplatz bzw Freiflächen */

        if ($e->einheit_qm > 0.00) {
            if ($this->objekt_baujahr <= 1918) {
                $spalte = 1;
            }
            if ($this->objekt_baujahr > 1918 && $this->objekt_baujahr < 1950) {
                $spalte = 2;
            }
            if ($this->objekt_baujahr > 1949 && $this->objekt_baujahr < 1965) {
                $spalte = 3;
            }
            if ($this->objekt_baujahr > 1964 && $this->objekt_baujahr < 1973) {
                $spalte = 4;
            }

            if ($this->objekt_baujahr > 1972 && $this->objekt_baujahr < 1991) {

                $this->check_objekt_ost($e->objekt_id);
                if (ltrim(rtrim($this->objekt_ost)) == 'JA') {
                    $spalte = 6;
                } else {
                    $spalte = 5;
                }
            }

            if ($this->objekt_baujahr > 1990 && $this->objekt_baujahr < 2003) {
                $spalte = 7;
            }

            if ($this->objekt_baujahr > 2002 && $this->objekt_baujahr < 2014) {
                $spalte = 8;
            }

            $mietspiegel_feld = "$buchstabe$spalte";

            $this->spalte = $spalte;
            // $this->ausstattungsklasse = $ausstattungsklasse;
            // echo '<pre>';
            // print_r($this);
            // print_r($e);
            return $mietspiegel_feld;
        }
    }

    function check_objekt_ost($objekt_id)
    {
        $d = new detail ();
        $this->objekt_ost = $d->finde_detail_inhalt('OBJEKT', $objekt_id, 'MS-Objekt-OST');
    }

    function check_bruttomieter($kos_typ, $kos_id)
    {
        $db_abfrage = "SELECT BETRAG FROM MIETENTWICKLUNG WHERE KOSTENTRAEGER_TYP='$kos_typ' && KOSTENTRAEGER_ID = '$kos_id' && MIETENTWICKLUNG_AKTUELL = '1'  && KOSTENKATEGORIE='Nebenkosten Vorauszahlung' && BETRAG!='0' ORDER BY MIETENTWICKLUNG_DAT DESC LIMIT 0,1";
        $result = DB::select($db_abfrage);
        if (!empty($result)) {
            foreach ($result as $row) {
                // Kein Bruttomieter, da Betrag > 0.00
                $betrag = $row ['BETRAG'];
                if ($betrag > 0.00) {
                    return false;
                }
                // Bruttomieter, da Betrag == 0.00
                if ($betrag == 0.00) {
                    return true;
                }
            }
        } else {
            return true; // Bruttomieter, da keine Mietdefinition für Nebenkosten
        }
    }

    function datum_1_mietdefinition($mietvertrag_id)
    {
        /* Zweite Mietdefinition */
        $result1 = DB::select("SELECT ANFANG, BETRAG FROM MIETENTWICKLUNG WHERE KOSTENTRAEGER_TYP='Mietvertrag' && KOSTENTRAEGER_ID = '$mietvertrag_id' && MIETENTWICKLUNG_AKTUELL = '1' && KOSTENKATEGORIE LIKE 'Miete kalt' ORDER BY ANFANG ASC LIMIT 1,1");
        if (empty($result1)) {
            /* Erste Mietdefinition */
            $result = DB::select("SELECT ANFANG, BETRAG FROM MIETENTWICKLUNG WHERE KOSTENTRAEGER_TYP='Mietvertrag' && KOSTENTRAEGER_ID = '$mietvertrag_id' && MIETENTWICKLUNG_AKTUELL = '1' && KOSTENKATEGORIE LIKE 'Miete kalt' ORDER BY ANFANG ASC LIMIT 0,1");
            if (!empty($result)) {
                $row = $result[0];
                return $row ['ANFANG'];
            }
        } else {
            /* Beide Mietdefinitionen und vergleichen */
            $result = DB::select("SELECT ANFANG, BETRAG FROM MIETENTWICKLUNG WHERE KOSTENTRAEGER_TYP='Mietvertrag' && KOSTENTRAEGER_ID = '$mietvertrag_id' && MIETENTWICKLUNG_AKTUELL = '1' && KOSTENKATEGORIE LIKE 'Miete kalt' ORDER BY ANFANG ASC LIMIT 0,1");
            $row = $result[0];

            $result1 = DB::select("SELECT ANFANG, BETRAG FROM MIETENTWICKLUNG WHERE KOSTENTRAEGER_TYP='Mietvertrag' && KOSTENTRAEGER_ID = '$mietvertrag_id' && MIETENTWICKLUNG_AKTUELL = '1' && KOSTENKATEGORIE LIKE 'Miete kalt' ORDER BY ANFANG ASC LIMIT 1,1");
            $row1 = $result1[0];
            $_miete1 = $row ['BETRAG'];
            $_miete2 = $row1 ['BETRAG'];
            if ($_miete1 >= $_miete2) {
                return $row ['ANFANG'];
            } else {
                return $row1 ['ANFANG'];
            }
        }
    }

    function get_spiegel_werte($jahr, $feld)
    {
        unset ($this->u_wert);
        unset ($this->m_wert);
        unset ($this->o_wert);
        $db_abfrage = "SELECT U_WERT, M_WERT, O_WERT FROM MIETSPIEGEL WHERE JAHR='$jahr' && FELD='$feld' ORDER BY DAT DESC LIMIT 0,1";
        $result = DB::select($db_abfrage);
        if (!empty($result)) {
            $row = $result[0];
            $this->u_wert = $row ['U_WERT'];
            $this->m_wert = $row ['M_WERT'];
            $this->o_wert = $row ['O_WERT'];
        }
    }

    function get_sondermerkmale_arr($a_klasse, $jahr)
    {
        $db_abfrage = "SELECT MERKMAL, WERT, A_KLASSE FROM `MS_SONDERMERKMALE` WHERE (A_KLASSE IS NULL OR A_KLASSE='$a_klasse') && JAHR='$jahr' ORDER BY A_KLASSE ASC";
        $result = DB::select($db_abfrage);
        return $result;
    }

    function datum_letzte_m_erhoehung($mv_id)
    {
        unset ($this->erhoehungsbetrag);
        unset ($this->erhoehungsdatum);
        $jahr = date("Y");
        $monat = date("m");
        $result = DB::select("SELECT ANFANG, BETRAG FROM MIETENTWICKLUNG WHERE KOSTENTRAEGER_TYP='MIETVERTRAG' && KOSTENTRAEGER_ID = '$mv_id' && MIETENTWICKLUNG_AKTUELL = '1' && ( ENDE = '0000-00-00' OR DATE_FORMAT( ENDE, '%Y-%m' ) >= '$jahr-$monat' && DATE_FORMAT( ANFANG, '%Y-%m' ) <= '$jahr-$monat' ) && DATE_FORMAT( ANFANG, '%Y-%m' ) <= '$jahr-$monat' && (KOSTENKATEGORIE LIKE 'Miete kalt%' or KOSTENKATEGORIE LIKE 'MHG%') ORDER BY ANFANG DESC LIMIT 0,1");
        $row = $result[0];
        $this->erhoehungsbetrag = $row ['BETRAG'];
        $this->erhoehungsdatum = date_mysql2german($row ['ANFANG']);
    }

    function get_erhoehungen_arr($ab_datum, $kos_typ, $kos_id)
    {
        $db_abfrage = "SELECT ANFANG, KOSTENKATEGORIE, ENDE, BETRAG FROM MIETENTWICKLUNG WHERE KOSTENTRAEGER_TYP='$kos_typ' && KOSTENTRAEGER_ID = '$kos_id' && MIETENTWICKLUNG_AKTUELL = '1'  && (KOSTENKATEGORIE LIKE 'Miete kalt%' or KOSTENKATEGORIE LIKE 'MOD') && ANFANG >='$ab_datum' ORDER BY ANFANG ASC";
        $result = DB::select($db_abfrage);
        return $result;
    }

    function check_erhoehung($mv_id)
    {
        unset ($this->naechste_erhoehung_datum);
        unset ($this->naechste_erhoehung_betrag);
        $jahr = date("Y");
        $monat = date("m");

        $db_abfrage = "SELECT ANFANG, BETRAG FROM MIETENTWICKLUNG WHERE KOSTENTRAEGER_TYP='MIETVERTRAG' && KOSTENTRAEGER_ID = '$mv_id' && MIETENTWICKLUNG_AKTUELL = '1' && ( ENDE = '0000-00-00' OR DATE_FORMAT( ENDE, '%Y-%m' ) >= '$jahr-$monat') && DATE_FORMAT( ANFANG, '%Y-%m' ) >= '$jahr-$monat'  && (KOSTENKATEGORIE LIKE 'MHG' or KOSTENKATEGORIE LIKE 'Miete kalt') ORDER BY ANFANG ASC LIMIT 0,1";
        $result = DB::select($db_abfrage);
        if (!empty($result)) {
            $row = $result[0];
            $this->naechste_erhoehung_datum = $row ['ANFANG'];
            $this->naechste_erhoehung_betrag = $row ['BETRAG'];
            return true;
        } else {
            return false;
        }
    }

    function liste_anzeigen($objekt_id)
    {
        $o = new objekt ();
        $f = new formular ();
        $f->fieldset('Mieterhöhungstabelle', 'me');
        $einheiten_arr = $o->einheiten_objekt_arr($objekt_id);
        $anzahl = count($einheiten_arr);
        echo "<table class=\"sortable striped\">";
        echo "<tr><th>EIN.</th><th>Klasse</th><th>MIETER</th><th>STR</th><th>MS</th><th>U WERT</th><th>M WERT</th><th>O WERT</th><th>m²</th><th>EINZUG</th><th>AN- STIEG in 3 J %</th><th>L. ER- HÖHUNG</th><th>MAX %</th><th>MAX MEHR €</th><th>MIETE vor 3 Jahren</th><th>MIETE AKT.</th><th>MAX MIETE KAPP</th><th>NEUE MIETE MWERT</th><th>ABZUG m²</th><th>ABZUG GESAMT</th><th>MW NACH ABZUG (ANGEMESSEN)</th><th>MEHR IM MONAT</th><th>ABZÜGE</th><th>m² AKT.</th><th>NEU m²/€</th><th>STATUS</th><th>Optionen</th><th>Bruttomiete AKT</th><th>Neue Bruttomiete</th></tr>";
        $summe_m_mehr = 0;
        $ms_jahr = $this->get_ms_jahr();

        for ($a = 0; $a < $anzahl; $a++) {
            $einheit_id = $einheiten_arr [$a] ['EINHEIT_ID'];
            $einheit_qm = $einheiten_arr [$a] ['EINHEIT_QM'];
            $tab_arr = $this->get_einheit_daten($einheit_id, $ms_jahr);
            if ($einheit_qm > 0.00 && isset ($tab_arr ['MV_ID']) && $tab_arr ['MV_ID'] != '') {
                $this->zeile_anzeigen($tab_arr, '');
                /* Summe aller die mehr als 1 Euro haben */
                if ($this->monatlich_mehr_mieter > 1) {
                    $summe_m_mehr += $this->monatlich_mehr_mieter;
                }
            }
        }
        $summe_m_mehr_a = nummer_punkt2komma($summe_m_mehr);
        echo "<tfoot><tr><th colspan=\"25\">Monatlich mehr Einnahmen $summe_m_mehr_a €</th></tr></tfoot>";
        echo "</table>";
        $f->fieldset_ende();
    }

    function zeile_anzeigen($array, $format)
    {
        if (is_array($array)) {
            $mieter_art = $array ['MIETER_ART'];
            $einheit_name = $array ['EINHEIT'];
            $einheit_id = $array ['EINHEIT_ID'];

            $einheit_qm = $array ['EINHEIT_QM'];
            $mieter = $array ['MIETER'];
            $mv_id = $array ['MV_ID'];

            $mvv = new mietvertraege ();
            $mvv->get_mietvertrag_infos_aktuell($mv_id);
            $einzug = date_mysql2german($array ['EINZUG']);
            $miete_aktuell = $array ['MIETE_AKTUELL'];
            $miete_3_jahre = $array ['MIETE_3_JAHRE'];
            $ms_feld = $array ['MS_FELD'];
            $u_wert = $array ['U_WERT'];
            $m_wert = $array ['M_WERT'];
            $o_wert = $array ['O_WERT'];
            $abzug_pro_qm = $array ['ABZUG_PRO_M2'];

            if (!empty ($array ['ABZUEGE'])) {
                $abzuege_arr = $array ['ABZUEGE'];
            } else {
                $abzuege_arr = '';
            }
            $gesamt_abzug = $array ['GESAMT_ABZUG'];
            $m2_aktuell = nummer_punkt2komma($array ['M2_AKTUELL']);
            $anstieg_3_jahre = nummer_punkt2komma($array ['ANSTIEG_3J']);
            $max_anstieg_prozentual = nummer_punkt2komma($array ['MAX_ANSTIEG_PROZ']);
            $max_anstieg_euro = nummer_punkt2komma($array ['MAX_ANSTIEG_EURO']);

            $letzter_anstieg_datum = $array ['L_ANSTIEG_DATUM'];

            $maximale_miete = nummer_punkt2komma($array ['MAXIMALE_MIETE']);
            $neue_miete_m_wert = $array ['NEUE_MIETE_M_WERT'];
            $neue_miete_m_wert_nach_abzug = $array ['NEUE_MIETE_M_WERT_W'];

            $anstiegs_datum = $array ['N_ANSTIEG_DATUM'];
            $m2_preis_neu = $array ['M2_PREIS_NEU'];
            $m2_preis_neu_a = nummer_punkt2komma($m2_preis_neu);
            $monatlich_mehr = $array ['MONATLICH_MEHR'];
            $monatlich_mehr_a = nummer_punkt2komma($monatlich_mehr);

            $status = $array ['STATUS'];
            $ausstattungsklasse = $array ['AUSSTATTUNGSKLASSE'];

            if ($format == '') {
                if ($m2_aktuell > $m_wert) {
                    $trstyle = "style=\"border-color:red; border-style: solid;\"";
                } else {
                    $trstyle = "style=\"border-color:green; border-style: solid;\"";
                }

                if ($maximale_miete < $neue_miete_m_wert) {

                    $tdstyle = "style=\"background-color:red; \"";
                } else {
                    $tdstyle = "style=\"background-color:green; \"";
                }

                if ($mieter_art == 'Nettomieter') {
                    echo "<tr $trstyle>";
                } else {
                    echo "<tr  class=\"zeile2\" $trstyle>";
                }
                $this->monatlich_mehr_mieter = $monatlich_mehr;

                $datum_erh_arr = explode('.', $anstiegs_datum);

                if (is_array($datum_erh_arr) && $datum_erh_arr [0] != "nicht möglich") {
                    $monat_erhoehung = $datum_erh_arr [1];
                    $jahr_erhoehung = $datum_erh_arr [2];
                } else {
                    $datum_erh_arr = explode('.', $mvv->mietvertrag_von_d);
                    $monat_erhoehung = $datum_erh_arr [1];
                    $jahr_erhoehung = $datum_erh_arr [2];
                }

                $nk_vorauszahlung = $this->kosten_monatlich($mv_id, $monat_erhoehung, $jahr_erhoehung, 'Nebenkosten Vorauszahlung');
                $hk_vorauszahlung = $this->kosten_monatlich($mv_id, $monat_erhoehung, $jahr_erhoehung, 'Heizkosten Vorauszahlung');
                $stellplatzmiete = $this->kosten_monatlich($mv_id, $monat_erhoehung, $jahr_erhoehung, 'Stellplatzmiete');
                $brutto_miete = $miete_aktuell + $nk_vorauszahlung + $hk_vorauszahlung + $stellplatzmiete;
                $neue_brutto_miete = $brutto_miete + $monatlich_mehr;
                $brutto_miete_a = nummer_punkt2komma($brutto_miete);
                $neue_brutto_miete_a = nummer_punkt2komma($neue_brutto_miete);
                echo "<td>$einheit_name</td><td>$ausstattungsklasse</td><td>$mieter</td><td>$mvv->haus_strasse $mvv->haus_nr<td>$ms_feld</td><td>$u_wert</td><td>$m_wert</td><td>$o_wert</td><td>$einheit_qm</td><td>$einzug</td><td>$anstieg_3_jahre</td><td>$letzter_anstieg_datum</td><td>$max_anstieg_prozentual</td><td>$max_anstieg_euro</td><td>$miete_3_jahre</td><td>$miete_aktuell</td><td $tdstyle>$maximale_miete</td><td>$neue_miete_m_wert</td><td>$abzug_pro_qm</td><td>$gesamt_abzug</td><td>$neue_miete_m_wert_nach_abzug</td>";
                if ($monatlich_mehr_a < 0) {
                    $style = "style=\"background:red\"";
                } else {
                    $style = "style=\"background:green\"";
                }
                echo "<td $style>$monatlich_mehr_a</td>";
                $style = '';
                if (is_array($abzuege_arr)) {
                    $anz = count($abzuege_arr);
                    echo "<td>";
                    for ($i = 0; $i < $anz; $i++) {
                        $merkm = $abzuege_arr [$i] ['MERKMAL'];
                        $merkmw = $abzuege_arr [$i] ['MERKMAL_WERT'];
                        echo "$merkm $merkmw<br>";
                    }
                    echo "</td>";
                } else {
                    echo "<td>keine</td>";
                }
                $link = "<a href='" . route('web::mietanpassungen::legacy', ['option' => 'miete_anpassen_mw', 'einheit_id' => $einheit_id]) . "'>Anpassen</a>";
                if ($m2_aktuell > $m_wert) {
                    // $style = "style=\"background:red\"";
                } else {
                    // $style = "style=\"background:green\"";
                }

                echo "<td $style>$m2_aktuell</td><td>$m2_preis_neu_a</td>";
                echo "<td>$status</td><td>$link</td><td>$brutto_miete_a</td><td>$neue_brutto_miete_a</td></tr>";
            }

            /*
			 * echo "<tr>";
			 * foreach ($array as $k => $v) {
			 * $wert = $array[$k];
			 * echo "<td>$wert</td>";
			 * }
			 * echo "</tr>";
			 */
        }
    }

    function kosten_monatlich($mietvertrag_id, $monat, $jahr, $kostenkat)
    {
        $result = DB::select("SELECT SUM(BETRAG) AS SUMME_RATE FROM MIETENTWICKLUNG WHERE KOSTENTRAEGER_TYP='MIETVERTRAG' && KOSTENTRAEGER_ID = '$mietvertrag_id' && MIETENTWICKLUNG_AKTUELL = '1' && ( ENDE = '0000-00-00' OR DATE_FORMAT( ENDE, '%Y-%m' ) >= '$jahr-$monat' && DATE_FORMAT( ANFANG, '%Y-%m' ) <= '$jahr-$monat' ) && DATE_FORMAT( ANFANG, '%Y-%m' ) <= '$jahr-$monat' && KOSTENKATEGORIE LIKE '$kostenkat' ORDER BY ANFANG ASC");
        $row = $result[0];
        $summe = $row ['SUMME_RATE'];
        return $summe;
    }

    function update_klassen($objekt_id)
    {
        echo "UPDATE AK4 für OBJEKT $objekt_id<br>";
        $o = new objekt ();
        $einheiten_arr = $o->einheiten_objekt_arr($objekt_id);
        $anzahl = count($einheiten_arr);
        for ($a = 0; $a < $anzahl; $a++) {
            $einheit_id = $einheiten_arr [$a] ['EINHEIT_ID'];
            $d = new detail ();
            if (!$d->check_detail_exist('EINHEIT', $einheit_id, 'Ausstattungsklasse')) {
                $d->detail_speichern_2('EINHEIT', $einheit_id, 'Ausstattungsklasse', '4', 'mit SH, Bad und IWC');
                echo "$einheit_id - AK4<br>";
            } else {
                echo "$einheit_id - AK existiert!<br>";
            }
        }
    }
    
    function form_mietanpassung($einheit_id, $ms_jahr)
    {
        $array = $this->get_einheit_daten($einheit_id, $ms_jahr);
        if (is_array($array)) {
            /* Berechnungsarray für das Versenden vorbereiten */
            $f = new formular ();
            $f->erstelle_formular('Mieterhöhung', null);
            $keys = array_keys($array);
            $anzahl_keys = count($keys);
            for ($z = 0; $z < $anzahl_keys; $z++) {
                $feld_keyname = $keys [$z];
                $feld_keyvalue = $array [$feld_keyname];
                $f->hidden_feld("ber_array[$feld_keyname]", "$feld_keyvalue");
            }

            $mieter_art = $array ['MIETER_ART'];
            $einheit_name = $array ['EINHEIT'];
            $einheit_id = $array ['EINHEIT_ID'];
            $einheit_qm = $array ['EINHEIT_QM'];
            $wohnlage = $array ['WOHNLAGE'];
            $mieter = $array ['MIETER'];
            $mv_id = $array ['MV_ID'];
            $einzug = date_mysql2german($array ['EINZUG']);
            $miete_aktuell = $array ['MIETE_AKTUELL'];
            $miete_3_jahre = $array ['MIETE_3_JAHRE'];
            $ms_feld = $array ['MS_FELD'];
            $u_wert = $array ['U_WERT'];
            $m_wert = $array ['M_WERT'];
            $o_wert = $array ['O_WERT'];
            $abzuege_arr = $array ['ABZUEGE'];
            $abzug_pro_qm = $array ['ABZUG_PRO_M2'];
            $gesamt_abzug = $array ['GESAMT_ABZUG'];
            $m2_aktuell = nummer_punkt2komma($array ['M2_AKTUELL']);
            $anstieg_3_jahre = nummer_punkt2komma($array ['ANSTIEG_3J']);
            $max_anstieg_prozentual = nummer_punkt2komma($array ['MAX_ANSTIEG_PROZ']);
            $max_anstieg_euro = nummer_punkt2komma($array ['MAX_ANSTIEG_EURO']);

            $letzter_anstieg_monate = $array ['L_ANSTIEG_MONATE'];
            $letzter_anstieg_datum = $array ['L_ANSTIEG_DATUM'];
            $erhoehungen_arr = $array ['ERHOEHUNGEN_ARR'];

            $maximale_miete = nummer_punkt2komma($array ['MAXIMALE_MIETE']);
            $neue_miete_m_wert = $array ['NEUE_MIETE_M_WERT'];
            $neue_miete_m_wert_nach_abzug = $array ['NEUE_MIETE_M_WERT_W'];

            $anstiegs_datum = $array ['N_ANSTIEG_DATUM'];
            $angemessene_neue_miete = $array ['NEUE_MIETE'];
            $m2_preis_neu = $array ['M2_PREIS_NEU'];
            $monatlich_mehr = $array ['MONATLICH_MEHR'];

            if ($letzter_anstieg_monate <= 1) {
                fehlermeldung_ausgeben("Nicht möglich<br>Letzte Mietdefinition vor weniger als 12 Monaten.");
            }

            echo "<table>";
            echo "<tr><th colspan=\"2\">Grunddaten Mieteinheit</th></tr>";
            echo "<tr><td>Einheit</td><td>$einheit_name</td></tr>";
            $einheit_qm_a = nummer_punkt2komma($einheit_qm);
            echo "<tr><td>Fläche</td><td>$einheit_qm_a m²</td></tr>";
            echo "<tr><td>Wohnlage</td><td>$wohnlage</td></tr>";
            echo "<tr><td>Ausstattungsklasse</td><td>";
            $d = new detail ();
            echo $d->finde_detail_inhalt('EINHEIT', $einheit_id, 'Ausstattungsklasse');
            echo "</td></tr>";

            $e = new einheit ();
            $e->get_einheit_info($einheit_id);
            echo "<tr><th colspan=\"2\">Wohnobjektdaten</th></tr>";
            echo "<tr><td>Objekt</td><td>$e->objekt_name</td></tr>";
            echo "<tr><td>Anschrift</td><td>$e->haus_strasse $e->haus_nummer, $e->haus_plz $e->haus_stadt</td></tr>";

            echo "<tr><th colspan=\"2\">Mietspiegeldaten $ms_jahr</th></tr>";
            echo "<tr><td>Mietspiegelfeld</td><td><b>$ms_feld</b></td></tr>";
            echo "<tr><td>Unterer Wert</td><td><b>$u_wert</b></td></tr>";
            echo "<tr><td>Mittlerer Wert</td><td><b>$m_wert</b></td></tr>";
            echo "<tr><td>Oberer Wert</td><td><b>$o_wert</b></td></tr>";
            echo "<tr><td style=\"background:yellow;color:red\">Miete kalt pro m²</td><td style=\"background:yellow;color:red\">$m2_aktuell €</td></tr>";
            echo "</table>";

            echo "<table><tr><th colspan=\"2\" style=\"background:yellow;color:red\">MERKMALE +</th><th style=\"background:green;color:red\">AUSWAHL +</th><th style=\"background:red;color:white\">AUSWAHL -</th></tr>";

            $diff_mw_ow = $o_wert - $m_wert;
            $_1_5tel = nummer_punkt2komma($diff_mw_ow / 5);
            $_2_5tel = nummer_punkt2komma($diff_mw_ow / 5 * 2);
            $_3_5tel = nummer_punkt2komma($diff_mw_ow / 5 * 3);
            $_4_5tel = nummer_punkt2komma($diff_mw_ow / 5 * 4);
            $_5_5tel = nummer_punkt2komma($diff_mw_ow / 5 * 5);

            echo "<tr><td>Differenz Mittelwert - Oberwert</td><td><b>$diff_mw_ow</b></td><td></td></tr>";

            echo "<tr><td>20% pro Merkmalsgruppe (1/5) BAD/WC</td><td><b>$_1_5tel</b></td><td>";
            $f->check_box_js1('MG1', 'MG1_P', '+', 'BAD_WC + 20 %', "onclick=\"check_on_off('MG1_M', 'MG1_P')\"", '');
            echo "</td><td>";
            $f->check_box_js1('MG1', 'MG1_M', '-', 'BAD_WC - 20 %', "onclick=\"check_on_off('MG1_P', 'MG1_M')\"", '');
            echo "</td></tr>";

            echo "<tr><td>20% pro Merkmalsgruppe (1/5) Küche</td><td><b>$_2_5tel</b></td><td>";
            $f->check_box_js1('MG2', 'MG2_P', '+', 'Küche + 20 %', "onclick=\"check_on_off('MG2_M', 'MG2_P')\"", '');
            echo "</td><td>";
            $f->check_box_js1('MG2', 'MG2_M', '-', 'Küche - 20 %', "onclick=\"check_on_off('MG2_P', 'MG2_M')\"", '');
            echo "</td></tr>";

            echo "<tr><td>20% pro Merkmalsgruppe (1/5) Wohnung</td><td><b>$_3_5tel</b></td><td>";
            $f->check_box_js1('MG3', 'MG3_P', '+', 'Wohnung + 20 %', "onclick=\"check_on_off('MG3_M', 'MG3_P')\"", '');
            echo "</td><td>";
            $f->check_box_js1('MG3', 'MG3_M', '-', 'Wohnung - 20 %', "onclick=\"check_on_off('MG3_P', 'MG3_M')\"", '');
            echo "</td></tr>";

            echo "<tr><td>20% pro Merkmalsgruppe (1/5) Gebäude</td><td><b>$_4_5tel</b></td><td>";
            $f->check_box_js1('MG4', 'MG4_P', '+', 'Gebäude + 20 %', "onclick=\"check_on_off('MG4_M', 'MG4_P')\"", '');
            echo "</td><td>";
            $f->check_box_js1('MG4', 'MG4_M', '-', 'Gebäude - 20 %', "onclick=\"check_on_off('MG4_P', 'MG4_M')\"", '');
            echo "</td></tr>";

            echo "<tr><td>20% pro Merkmalsgruppe (1/5) Wohnumfeld</td><td><b>$_5_5tel</b></td><td>";
            $f->check_box_js1('MG5', 'MG5_P', '+', 'Wohnumfeld + 20 %', "onclick=\"check_on_off('MG5_M', 'MG5_P')\"", '');
            echo "</td><td>";
            $f->check_box_js1('MG5', 'MG5_M', '-', 'Wohnumfeld - 20 %', "onclick=\"check_on_off('MG5_P', 'MG5_M')\"", '');
            echo "</td></tr>";
            echo "</table><table>";

            echo "<tr><th colspan=\"2\">Mieterinfos</th></tr>";
            echo "<tr><td>Mieter</td><td>$mieter</td></tr>";
            echo "<tr><td>Mieterart</td><td>$mieter_art</td></tr>";
            echo "<tr><td>Einzug</td><td>$einzug</td></tr>";

            echo "<tr><td>Letzte Mieterhöhung</td><td>$letzter_anstieg_datum</td></tr>";
            $miete_3_jahre_a = nummer_punkt2komma($miete_3_jahre);
            echo "<tr><td>Miete vor 3 Jahren / Einzug</td><td>$miete_3_jahre_a €</td></tr>";

            if (is_array($erhoehungen_arr)) {
                $anz_e = count($erhoehungen_arr);
                echo "<tr><th colspan=\"2\">Mieterhöhungen seit 3 Jahren</th></tr>";
                for ($j = 0; $j < $anz_e; $j++) {
                    $k_kat = $erhoehungen_arr [$j] ['KOSTENKATEGORIE'];
                    $anf_kat = date_mysql2german($erhoehungen_arr [$j] ['ANFANG']);
                    $ende_kat = date_mysql2german($erhoehungen_arr [$j] ['ENDE']);
                    if ($ende_kat == '00.00.0000') {
                        $ende_kat = 'unbefristet';
                    }
                    $betrag_kat = nummer_punkt2komma($erhoehungen_arr [$j] ['BETRAG']);
                    echo "<tr><td><b>Von: $anf_kat Bis: $ende_kat - $k_kat</b></td><td>$betrag_kat €</td></tr>";
                }
            }

            $miete_aktuell_a = nummer_punkt2komma($miete_aktuell);
            /* Ausgabe nur für Nettomieter */
            if ($mieter_art == 'Nettomieter') {
                echo "<tr><td>Miete kalt aktuell</td><td>$miete_aktuell_a €</td></tr>";
                echo "<tr><td>Miete kalt pro m²</td><td>$m2_aktuell €</td></tr>";
                echo "<tr><td>Kappungsgrenze für 3 Jahre in %</td><td>15,00 %</td></tr>";
                echo "<tr><td>Mieterhöhung in den letzten 3 Jahren in %</td><td>$anstieg_3_jahre %</td></tr>";
                echo "<tr><td>Max. mögliche Mieterhöhung in %</td><td>$max_anstieg_prozentual %</td></tr>";
                echo "<tr><td>Max. mögliche Mieterhöhung in Euro</td><td><b>$max_anstieg_euro €</b></td></tr>";
                echo "<tr><td>Max. mögliche Miete kalt in Euro</td><td><b>$maximale_miete €</b></td></tr>";

                echo "<tr><th colspan=\"2\">Berechnung der Miete nach Mietspiegelmittelwert</th></tr>";
                echo "<tr><td>Berechnung nach Mietspiegelfeld</td><td>$ms_feld</td></tr>";
                echo "<tr><td>Formel</td><td>Fläche * Mittelwert = Miete nach Mietspiegel</td></tr>";
                echo "<tr><td>Berechnung</td><td>$einheit_qm * $m_wert  = $neue_miete_m_wert €</td></tr>";
                $neue_miete_m_wert_a = nummer_punkt2komma($neue_miete_m_wert);
                echo "<tr><td>Neue Miete nach Mietspiegel</td><td><b>$neue_miete_m_wert_a €</b></td></tr>";

                echo "<tr><th colspan=\"2\">Wertmindernde Faktoren pro m²</th></tr>";
                if (is_array($abzuege_arr)) {
                    $anz = count($abzuege_arr);
                    for ($i = 0; $i < $anz; $i++) {
                        $merkm = $abzuege_arr [$i] ['MERKMAL'];
                        $merkmw = $abzuege_arr [$i] ['MERKMAL_WERT'];
                        echo "<tr><td>$merkm</td><td>$merkmw</td></tr>";
                    }

                    echo "<tr><td>Berechnung</td><td><b>$einheit_qm m² * $abzug_pro_qm = $gesamt_abzug</b></td></tr>";
                    $gesamt_abzug_a = nummer_punkt2komma($gesamt_abzug);
                    echo "<tr><td>Gesamtminderung</td><td><b>$gesamt_abzug_a</b></td></tr>";
                } else {
                    echo "<tr><td>keine</td><td>0,00</td></tr>";
                }
                $angemessene_neue_miete_a = nummer_punkt2komma($angemessene_neue_miete);

                echo "<tr><th colspan=\"2\">Mietspiegelmiete nach Abzug von wertmindernden Faktoren</th></tr>";
                echo "<tr><td>Formel</td><td>x = Miete nach Mittelwert - Gesamtminderung</td></tr>";
                echo "<tr><td>Berechnung</td><td>$neue_miete_m_wert_nach_abzug = $neue_miete_m_wert - $gesamt_abzug</td></tr>";
                $neue_miete_m_wert_nach_abzug_a = nummer_punkt2komma($neue_miete_m_wert_nach_abzug);
                echo "<tr><td>Mietspiegelmiete nach Minderung</td><td>$neue_miete_m_wert_nach_abzug_a<br>$neue_miete_m_wert_nach_abzug_a < $miete_aktuell_a</td></tr>";

                if ($neue_miete_m_wert_nach_abzug < $miete_aktuell) {
                    throw new \App\Exceptions\MessageException(
                        new \App\Messages\ErrorMessage("Erhöhung nicht möglich, da Miete abzüglich Minderung kleiner als aktuelle Miete $neue_miete_m_wert_nach_abzug_a € < $miete_aktuell_a €")
                    );
                }

                echo "<tr><th colspan=\"2\">Neue angemessene Miete kalt ab $anstiegs_datum</th></tr>";
                echo "<tr><td>Miete kalt aktuell</td><td>$miete_aktuell_a €</td></tr>";

                echo "<tr><td>Neue Miete kalt pro m²</td><td>$m2_preis_neu €</td></tr>";
                $monatlich_mehr_a = nummer_punkt2komma($monatlich_mehr);
                echo "<tr><td>Monatliche Erhöhung</td><td>$monatlich_mehr_a €</td></tr>";
                echo "<tr><td>Neue Miete kalt</td><td>$angemessene_neue_miete_a €</td></tr>";

                $prozent_erh = ($angemessene_neue_miete / ($miete_aktuell / 100)) - 100;
                $prozent_erh_a = nummer_punkt2komma($prozent_erh);
                echo "<tr><td>Erhöhung prozentual</td><td>$prozent_erh_a %</td></tr>";

                $datum_erh_arr = explode('.', $anstiegs_datum);
                $monat_erhoehung = $datum_erh_arr [1];
                $jahr_erhoehung = $datum_erh_arr [2];
                $nk_vorauszahlung = $this->kosten_monatlich($mv_id, $monat_erhoehung, $jahr_erhoehung, 'Nebenkosten Vorauszahlung');
                $nk_vorauszahlung_a = nummer_punkt2komma($nk_vorauszahlung);
                $hk_vorauszahlung = $this->kosten_monatlich($mv_id, $monat_erhoehung, $jahr_erhoehung, 'Heizkosten Vorauszahlung');
                $hk_vorauszahlung_a = nummer_punkt2komma($hk_vorauszahlung);
                $stellplatzmiete = $this->kosten_monatlich($mv_id, $monat_erhoehung, $jahr_erhoehung, 'Stellplatzmiete');
                $stellplatzmiete_a = nummer_punkt2komma($stellplatzmiete);
                echo "<tr><td>Nebenkosten Vorauszahlung</td><td>$nk_vorauszahlung_a €</td></tr>";
                echo "<tr><td>Heizkosten Vorauszahlung</td><td>$hk_vorauszahlung_a €</td></tr>";
                if ($stellplatzmiete != 0) {
                    echo "<tr><td>Stellplatzmiete</td><td>$stellplatzmiete_a €</td></tr>";
                }
                $f->hidden_feld("ber_array[B_AKT_NK]", "$nk_vorauszahlung_a");
                $f->hidden_feld("ber_array[B_AKT_HK]", "$hk_vorauszahlung_a");
                $f->hidden_feld("ber_array[B_AKT_SP]", "$stellplatzmiete_a");
                $aktuelle_end_miete = $miete_aktuell + $nk_vorauszahlung + $hk_vorauszahlung + $stellplatzmiete;
                $aktuelle_end_miete_a = nummer_punkt2komma($aktuelle_end_miete);
                $f->hidden_feld("ber_array[B_AKT_ENDMIETE]", "$aktuelle_end_miete_a");
                echo "<tr><td><b>Aktuelle Endmiete</b></td><td>$aktuelle_end_miete_a €</td></tr>";
                echo "<tr><td>Monatliche Erhöhung</td><td>$monatlich_mehr_a €</td></tr>";
                $end_miete = $angemessene_neue_miete + $nk_vorauszahlung + $hk_vorauszahlung + $stellplatzmiete;
                $end_miete_a = nummer_punkt2komma($end_miete);
                $f->hidden_feld("ber_array[B_NEUE_ENDMIETE]", "$end_miete_a");
                echo "<tr><td><b>Neue Endmiete</b></td><td>$end_miete_a €</td></tr>";
                echo "<tr><td><b>Diese Berechnung übernehmen?</b></td><td><br>";
                $f->hidden_feld("option", "ber_uebernehmen_netto");
                $f->datum_feld('Druckdatum PDF', 'druckdatum', '', 'druckdatum');
                $f->send_button("ber_uebernehmen_netto", "Übernehmen->PDF");
                echo "</td></tr>";
            }  // ende Nettomieter

            /* Bruttomieter */
            else {
                $datum_erh_arr = explode('.', $anstiegs_datum);
                $monat_erhoehung = $datum_erh_arr [1];
                $jahr_erhoehung = $datum_erh_arr [2];
                $hk_vorauszahlung = $this->kosten_monatlich($mv_id, $monat_erhoehung, $jahr_erhoehung, 'Heizkosten Vorauszahlung');
                $hk_vorauszahlung_a = nummer_punkt2komma($hk_vorauszahlung);

                echo "<tr><td>HK VORSCHUSS</td><td>$hk_vorauszahlung_a €</td></tr>";
                $f->hidden_feld("ber_array[B_AKT_HK]", "$hk_vorauszahlung_a");
                echo "<tr><td>Miete kalt aktuell</td><td>$miete_aktuell_a €</td></tr>";
                echo "<tr><td>Miete kalt pro m²</td><td>$m2_aktuell €</td></tr>";
                echo "<tr><td>Kappungsgrenze für 3 Jahre in %</td><td>15,00 %</td></tr>";
                echo "<tr><td>Mieterhöhung in den letzten 3 Jahren in %</td><td>$anstieg_3_jahre %</td></tr>";
                if (!request()->has('nk_anteil')) {
                    echo "<tr><td colspan=\"2\">";
                    $f = new formular ();
                    $f->hidden_feld("einheit_id", "$einheit_id");
                    $f->hidden_feld("option", "miete_anpassen_mw");
                    $f->text_feld("Tatsächliche Nebenkosten jährlich", "nk_anteil", "", "10", 'nk_anteil', '');
                    $f->send_button("submit_detail", "Berechnen");
                    echo "</td></tr>";
                } else {
                    $nk_anteil_j = request()->input('nk_anteil');
                    $nk_anteil = nummer_punkt2komma(nummer_komma2punkt($nk_anteil_j) / 12);
                    $f->hidden_feld("ber_array[TAT_KOST_M]", "$nk_anteil");
                    $f->hidden_feld("ber_array[TAT_KOST_J]", "$nk_anteil_j");
                    echo "<tr><td>Tatsächliche Nebenkosten monatlich</td><td>$nk_anteil €</td></tr>";
                    echo "<tr><td>Max. mögliche Mieterhöhung in %</td><td>$max_anstieg_prozentual %</td></tr>";
                    echo "<tr><td>Max. mögliche Mieterhöhung in Euro</td><td><b>$max_anstieg_euro €</b></td></tr>";
                    echo "<tr><td>Max. mögliche Miete kalt in Euro</td><td><b>$maximale_miete €</b></td></tr>";
                    echo "<tr><th colspan=\"2\">Berechnung der Miete nach Mietspiegelmittelwert</th></tr>";
                    echo "<tr><td>Berechnung nach Mietspiegelfeld</td><td>$ms_feld</td></tr>";
                    echo "<tr><td>Formel</td><td>Fläche * Mittelwert = Miete nach Mietspiegel</td></tr>";
                    echo "<tr><td>Berechnung</td><td>$einheit_qm * $m_wert  = $neue_miete_m_wert €</td></tr>";
                    $neue_miete_m_wert_a = nummer_punkt2komma($neue_miete_m_wert);
                    echo "<tr><td>Neue Miete nach Mietspiegel</td><td><b>$neue_miete_m_wert_a €</b></td></tr>";

                    echo "<tr><th colspan=\"2\">Wertmindernde Faktoren pro m²</th></tr>";

                    if (is_array($abzuege_arr)) {
                        $anz = count($abzuege_arr);
                        for ($i = 0; $i < $anz; $i++) {
                            $merkm = $abzuege_arr [$i] ['MERKMAL'];
                            $merkmw = $abzuege_arr [$i] ['MERKMAL_WERT'];
                            echo "<tr><td>$merkm</td><td>$merkmw</td></tr>";
                        }

                        echo "<tr><td>Berechnung</td><td><b>$einheit_qm m² * $abzug_pro_qm = $gesamt_abzug</b></td></tr>";
                        $gesamt_abzug_a = nummer_punkt2komma($gesamt_abzug);
                        echo "<tr><td>Gesamtminderung</td><td><b>$gesamt_abzug_a</b></td></tr>";
                    } else {
                        echo "<tr><td>keine</td><td>0,00</td></tr>";
                    }
                    $angemessene_neue_miete_a = nummer_punkt2komma($angemessene_neue_miete);

                    echo "<tr><th colspan=\"2\">Mietspiegelmiete nach Abzug von wertmindernden Faktoren</th></tr>";
                    echo "<tr><td>Formel</td><td>x = Miete nach Mittelwert - Gesamtminderung</td></tr>";
                    echo "<tr><td>Berechnung</td><td>$neue_miete_m_wert_nach_abzug = $neue_miete_m_wert - $gesamt_abzug</td></tr>";
                    $neue_miete_m_wert_nach_abzug_a = nummer_punkt2komma($neue_miete_m_wert_nach_abzug);
                    echo "<tr><td>Mietspiegelmiete nach Minderung</td><td>$neue_miete_m_wert_nach_abzug_a <br>$neue_miete_m_wert_nach_abzug<$miete_aktuell</td></tr>";

                    if ($neue_miete_m_wert_nach_abzug < $miete_aktuell) {
                        throw new \App\Exceptions\MessageException(
                            new \App\Messages\ErrorMessage("Erhöhung nicht möglich, da Miete abzüglich Minderung kleiner als aktuelle Miete $neue_miete_m_wert_nach_abzug_a € < $miete_aktuell_a €")
                        );
                    }

                    echo "<tr><th colspan=\"2\">Neue angemessene Miete kalt ab $anstiegs_datum</th></tr>";
                    echo "<tr><td>Miete kalt aktuell</td><td>$miete_aktuell_a €</td></tr>";

                    // echo "<hr><h3>Neue Miete ab $anstiegs_datum $angemessene_neue_miete</h3>";
                    echo "<tr><td>Neue Miete kalt pro m²</td><td>$m2_preis_neu €</td></tr>";
                    $monatlich_mehr_a = nummer_punkt2komma($monatlich_mehr);
                    echo "<tr><td>Monatliche Erhöhung</td><td>$monatlich_mehr_a €</td></tr>";

                    echo "<tr><td>Neue Miete kalt</td><td>$angemessene_neue_miete_a €</td></tr>";
                    $datum_erh_arr = explode('.', $anstiegs_datum);
                    $nk_vorauszahlung = nummer_komma2punkt(request()->input('nk_anteil')) / 12;
                    $nk_vorauszahlung_a = nummer_punkt2komma($nk_vorauszahlung);
                    echo "<tr><td>Tatsächliche Kosten</td><td>$nk_vorauszahlung_a €</td></tr>";
                    $aktuelle_end_miete = $miete_aktuell + $nk_vorauszahlung + $hk_vorauszahlung;
                    $aktuelle_end_miete_a = nummer_punkt2komma($aktuelle_end_miete);
                    echo "<tr><td><b>Aktuelle Endmiete</b></td><td>$aktuelle_end_miete_a €</td></tr>";
                    echo "<tr><td>Monatliche Erhöhung</td><td>$monatlich_mehr_a €</td></tr>";
                    $end_miete = $angemessene_neue_miete + $nk_vorauszahlung + $hk_vorauszahlung;
                    $end_miete_a = nummer_punkt2komma($end_miete);
                    echo "<tr><td><b>Neue Endmiete</b></td><td>$end_miete_a €</td></tr>";
                    echo "<tr><td><b>QUATSCH</b></td><td>$end_miete_a €</td></tr>";
                    $f->hidden_feld("ber_array[NEUE_BRUTTO_MIETE]", "$end_miete_a");
                    $f->hidden_feld("ber_array[ERHÖHUNG]", "$monatlich_mehr_a");
                    echo "<tr><td>";
                    $f->hidden_feld("option", "ber_uebernehmen_brutto");
                    $f->send_button("pdf_brutto", "Bruttomieter PDF");
                    echo "</td></tr>";
                }
            } // ENDE BRUTTOMIETER

            echo "</table>";
            $f->ende_formular();
            // $f->ende_formular();
            // $f->erstelle_formular('Bruttomieter PDF', '');

            // $f->send_button("ber_prozent", "Manuelle Prozenteingabe");
        } else {
            echo "Keine Berechnungsdaten";
        }
    }
    
    function pdf_anschreiben_MW_stapel($pdf, $ber_array, $datum)
    {
        $ber = ( object )$ber_array;
        $ber->MIETE_AKTUELL_A = nummer_punkt2komma($ber->MIETE_AKTUELL);
        $ber->EINHEIT_QM_A = nummer_punkt2komma($ber->EINHEIT_QM);
        $ber->M2_AKTUELL_A = nummer_punkt2komma($ber->M2_AKTUELL);
        $ber->M_WERT_A = nummer_punkt2komma($ber->M_WERT);
        $ber->NEUE_MIETE_A = nummer_punkt2komma($ber->NEUE_MIETE);
        $ber->MONATLICH_MEHR_A = nummer_punkt2komma($ber->MONATLICH_MEHR);
        $ber->L_ANSTIEG_BETRAG_A = nummer_punkt2komma($ber->L_ANSTIEG_BETRAG);
        $ber->ANSTIEG_3J_A = nummer_punkt2komma($ber->ANSTIEG_3J);
        $ber->MIETE_3_JAHRE_A = nummer_punkt2komma($ber->MIETE_3_JAHRE);

        $p = new partners();
        $p->get_partner_info(session()->get('partner_id'));
        $pdf->addText(480, 697, 8, "$p->partner_ort, $datum");
        $mv = new mietvertraege ();
        $mv->get_mietvertrag_infos_aktuell($ber->MV_ID);

        if ($mv->anz_zustellanschriften == '0') {
            $pdf->ezText("$mv->personen_name_string_u\n$mv->haus_strasse $mv->haus_nr\n\n$mv->haus_plz $mv->haus_stadt", 12);
            $pdf->addText(250, $pdf->y, 6, "$mv->einheit_lage", 0);
        } else {
            /* Zustelanschrift */
            $postanschrift = $mv->postanschrift [0] ['adresse'];
            $pdf->ezText("$postanschrift", 12);
        }
        $pdf->ezSetDy(-60);
        /* Betreff */
        $pdf->ezText("<b>Mieterhöhungsverlangen zum $ber->N_ANSTIEG_DATUM gemäß §§ 558 BGB ff. des Bürgerlichen Gesetzbuches (BGB) Mieter-Nr.: $mv->einheit_kurzname</b>", 11);
        // $pdf->ezText("Einheit: $mv->einheit_kurzname",12);
        $pdf->ezSetDy(-10);
        /* Faltlinie */
        $pdf->setLineStyle(0.2);
        $pdf->line(5, 542, 20, 542);
        /* Anrede */
        $pdf->ezText("$anrede", 11);
        $pdf->ezText("$mv->mv_anrede", 11);
        $brief_text = "wie Ihnen bekannt ist, vertreten wir die rechtlichen Interessen der Eigentümer. Eine auf uns lautende Vollmacht ist beigefügt.";
        $pdf->ezText("$brief_text", 11, array(
            'justification' => 'full'
        ));
        $brief_text = "Namens und in Vollmacht der Eigentümer werden Sie hiermit gebeten, der Erhöhung der Netto-Kaltmiete gemäß § 558 BGB zuzustimmen. Gemäß der mietvertraglichen Vereinbarung zahlen Sie gegenwärtig eine Nettomiete in Höhe von $ber->MIETE_AKTUELL_A €. Die jeweiligen Angaben beziehen sich auf den monatlichen Mietzins.
		";
        $pdf->ezText("$brief_text", 11, array(
            'justification' => 'full'
        ));

        $tab_arr [0] ['BEZ'] = '<b>Ihre derzeitige Netto-Kaltmiete beträgt:</b>';
        $tab_arr [0] ['BETRAG'] = '<b>' . $ber->MIETE_AKTUELL_A . ' €</b>';
        $tab_arr [1] ['BEZ'] = '<b>Erhöhungsbetrag:</b>';
        $tab_arr [1] ['BETRAG'] = '<b>' . $ber->MONATLICH_MEHR_A . ' €</b>';
        $tab_arr [2] ['BEZ'] = "<b>Neue Nettokaltmiete ab $ber->N_ANSTIEG_DATUM:</b>";
        $tab_arr [2] ['BETRAG'] = '<b>' . $ber->NEUE_MIETE_A . ' €</b>';
        $cols = array(
            'BEZ' => "BEZ",
            'BETRAG' => "BETRAG"
        );
        $pdf->ezTable($tab_arr, $cols, "", array(
            'showHeadings' => 0,
            'shaded' => 0,
            'showLines' => 0,
            'titleFontSize' => 11,
            'fontSize' => 11,
            'xPos' => 55,
            'xOrientation' => 'right',
            'width' => 400,
            'cols' => array(
                'BEZ' => array(
                    'justification' => 'left',
                    'width' => 350
                ),
                'BETRAG' => array(
                    'justification' => 'right',
                    'width' => 50
                )
            )
        ));
        $pdf->ezSetDy(-10);
        $brief_text = "Gemäß § 558 BGB kann der Vermieter die Zustimmung zur Mieterhöhung von Ihnen verlangen, wenn der Mietzins, zu dem die Erhöhung eintreten soll, seit 15 Monaten unverändert und mindestens 1 Jahr nach der letzten Mieterhöhung verstrichen ist. Weiterhin darf sich der Mietzins innerhalb von 3 Jahren um nicht mehr als 15 % erhöhen.";
        $pdf->ezText("$brief_text", 11, array(
            'justification' => 'full'
        ));
        $pdf->ezSetDy(-10);
        $brief_text = "Die mietvertraglich vereinbarte Fläche Ihrer Wohnung beträgt $ber->EINHEIT_QM_A m². Sie zahlen gegenwärtig eine Netto-Kaltmiete in Höhe von $ber->MIETE_AKTUELL_A €. Hieraus errechnet sich eine Miete netto kalt je qm in Höhe von $ber->M2_AKTUELL_A €.";
        $pdf->ezText("$brief_text", 11, array(
            'justification' => 'full'
        ));

        $brief_text = "\nBei der Berechnung der zulässigen Erhöhung gemäß § 558 BGB ist von der gezahlten Netto-Kaltmiete von vor drei Jahren auszugehen.";
        $pdf->ezText("$brief_text", 11, array(
            'justification' => 'full'
        ));
        $datum_vor_3_jahren_a = $ber->DATUM_3_JAHRE;
        $datum_vor_3_jahren = date_german2mysql($datum_vor_3_jahren_a);
        $ber->EINZUG_A = date_mysql2german($ber->EINZUG);
        $t1 = strtotime("$datum_vor_3_jahren");
        $t2 = strtotime("$ber->EINZUG");
        if ($t2 > $t1) {
            $brief_text = "\nDie Netto-Kaltmiete (ohne Umlagen und Zuschläge) betrug bei Vertragsbeginn am $ber->EINZUG_A $ber->MIETE_3_JAHRE_A €. ";
        } else {
            $brief_text = "\nDie Netto-Kaltmiete (ohne Umlagen und Zuschläge) betrug  am $datum_vor_3_jahren_a $ber->MIETE_3_JAHRE_A €. ";
        }
        $pdf->ezText("$brief_text", 11, array(
            'justification' => 'full'
        ));

        $brief_text = "\nAuf diesen Netto-Kaltmietzins erfolgten innerhalb der letzten drei Jahre Erhöhungen von insgesamt $ber->ANSTIEG_3J_A %.";
        $pdf->ezText("$brief_text", 11, array(
            'justification' => 'full'
        ));

        /* Zweite Seite */
        $pdf->ezNewPage();

        $brief_text = "\nAuf Grundlage des Berliner Mietspiegel für $ber->MS_JAHR (in Kopie beigefügt) ist eine Erhöhung auf $ber->M_WERT_A €/m² und unter der Berücksichtigung von Sondermerkmalen auf $ber->M_WERT_A € / m² für Ihre Wohnung möglich.";
        $pdf->ezText("$brief_text", 11, array(
            'justification' => 'full'
        ));

        $brief_text = "\nBei der Ermittlung des ortsüblichen Vergleichmietzinses aufgrund des qualifizierten Mietspiegels gemäß § 558d BGB sind hierbei folgende wohnungsbezogenen Merkmale zu berücksichtigen.\n";
        $pdf->ezText("$brief_text", 11, array(
            'justification' => 'full'
        ));
        $brief_text = "Merkmalgruppe 1:  Bad/WC";
        $pdf->ezText("$brief_text", 11, array(
            'justification' => 'full'
        ));
        $brief_text = "Merkmalgruppe 2:  Küche";
        $pdf->ezText("$brief_text", 11, array(
            'justification' => 'full'
        ));
        $brief_text = "Merkmalgruppe 3:  Wohnung";
        $pdf->ezText("$brief_text", 11, array(
            'justification' => 'full'
        ));
        $brief_text = "Merkmalgruppe 4:  Gebäude";
        $pdf->ezText("$brief_text", 11, array(
            'justification' => 'full'
        ));
        $brief_text = "Merkmalgruppe 5:  Wohnumfeld";
        $pdf->ezText("$brief_text", 11, array(
            'justification' => 'full'
        ));

        // $pdf->eztext("Als Anlage erhalten Sie die Online-Berechnung der Stadtentwicklung Berlin.", 12);

        $brief_text = "\nAufgrund dieser Merkmalsgruppen ergibt sich ein Zu-/Abschlag für Ihre Wohnung in Höhe von 0,00 %.";
        $pdf->ezText("$brief_text", 11, array(
            'justification' => 'full'
        ));
        $brief_text = "Die von Ihnen genutzte Wohnung ist dem Mietspiegelfeld <b>$ber->MS_FELD </b>zuzuordnen.";
        $pdf->ezText("$brief_text", 11, array(
            'justification' => 'full'
        ));
        $ber->M_WERT_A = nummer_punkt2komma($ber->M_WERT);

        /* Sondermerkmale finden */
        $sondermerkmale_arr = $this->get_sondermerkmale_arr($ber->AUSSTATTUNGSKLASSE, $ber->MS_JAHR);
        $anz_sm = count($sondermerkmale_arr);
        if ($anz_sm > 0) {
            $d = new detail ();
            $abzug_zaehler = 0;
            $this->abzug_wert = 0;

            for ($s = 0; $s < $anz_sm; $s++) {
                $merkmal = $sondermerkmale_arr [$s] ['MERKMAL'];
                $wert = $sondermerkmale_arr [$s] ['WERT'];
                $a_klasse = $sondermerkmale_arr [$s] ['A_KLASSE'];
                if ($a_klasse == NULL or $ber->AUSSTATTUNGSKLASSE == $a_klasse) {
                    /* Wenn z.B. Erdgeschoss, dann Abzug */
                    $sonder_abzug = $d->finde_detail_inhalt('EINHEIT', $ber->EINHEIT_ID, $merkmal);
                    if ($sonder_abzug) {
                        $abzuege_arr [$abzug_zaehler] ['MERKMAL'] = $merkmal;
                        $abzuege_arr [$abzug_zaehler] ['MERKMAL_GRUND'] = $sonder_abzug;
                        $abzuege_arr [$abzug_zaehler] ['MERKMAL_WERT'] = $wert;
                        $this->abzug_wert = $this->abzug_wert + $wert;
                        $abzug_zaehler++;
                    }
                }
            } // end for
        } // end wenn Sondermerkmale vorhanden

        if (is_array($abzuege_arr)) {
            $brief_text = "\nBei Ihrer Wohnung wurden bei der Berechnung folgende wertmindernde Faktoren berücksichtigt:\n";
            $pdf->ezText("$brief_text", 11, array(
                'justification' => 'full'
            ));

            $anz = count($abzuege_arr);
            for ($i = 0; $i < $anz; $i++) {
                $merkm = $abzuege_arr [$i] ['MERKMAL'];
                $merkmw = $abzuege_arr [$i] ['MERKMAL_WERT'];
                $merkmw_a = nummer_punkt2komma($merkmw);
                // echo "<tr><td>$merkm</td><td>$merkmw</td></tr>";
                $pdf->ezText("$merkm          $merkmw_a €/m²", 11);
            }
            $ber->GESAMT_ABZUG_A = nummer_punkt2komma($ber->GESAMT_ABZUG);
            $pdf->ezText("<b>Gesamtminderung              $ber->GESAMT_ABZUG_A €/monatlich</b>", 11);
        }

        $ber->ANSTIEG_UM_PROZENT_A = nummer_punkt2komma($ber->ANSTIEG_UM_PROZENT);
        $ber->MONATLICH_MEHR_A = nummer_punkt2komma($ber->MONATLICH_MEHR);
        $brief_text = "\nGemäß § 558 Absatz 3 BGB wird hiermit die Miete um $ber->ANSTIEG_UM_PROZENT_A %, ausgehend vom Netto-Kaltmietzins, also um insgesamt $ber->MONATLICH_MEHR_A €, erhöht.";
        $pdf->ezText("$brief_text", 11, array(
            'justification' => 'full'
        ));
        $ber->M2_PREIS_NEU_A = nummer_punkt2komma($ber->M2_PREIS_NEU);
        $brief_text = "\nNach der Erhöhung beträgt die Nettokaltmiete $ber->M2_PREIS_NEU_A €/m². Unter Berücksichtigung der wohnungsbezogenen Merkmale ist der geforderte Mietzins ortsüblich.";
        $pdf->ezText("$brief_text", 11, array(
            'justification' => 'full'
        ));
        // $brief_text = "\n<b>Ihre Neue Gesamtmiete beträgt ab dem $ber->N_ANSTIEG_DATUM insgesamt $ber->B_NEUE_ENDMIETE €.</b>";
        // $pdf->ezText("$brief_text",11, array('justification'=>'full'));

        $brief_text = "\n<b>Diese setzt sich wie folgt zusammen (EURO):</b>";
        $pdf->ezText("$brief_text", 11, array(
            'justification' => 'full'
        ));
        /*
		 * $brief_text = "Kaltmiete: $ber->NEUE_MIETE_A";
		 * $pdf->ezText("$brief_text",11, array('justification'=>'full'));
		 * $brief_text = "Nebenkosten Vorauszahlung: $ber->B_AKT_NK";
		 * $pdf->ezText("$brief_text",11, array('justification'=>'full'));
		 * $brief_text = "Heizkosten Vorauszahlung: $ber->B_AKT_HK";
		 * $pdf->ezText("$brief_text",11, array('justification'=>'full'));
		 */

        $tab_arr [0] ['BEZ'] = '<b>Ihre derzeitige Netto-Kaltmiete beträgt';
        $tab_arr [0] ['BETRAG'] = "$ber->MIETE_AKTUELL_A €";
        $tab_arr [0] ['M2'] = "$ber->M2_AKTUELL_A €";
        $tab_arr [1] ['BEZ'] = 'Erhöhungsbetrag:';
        $tab_arr [1] ['BETRAG'] = "$ber->MONATLICH_MEHR_A €";
        $erh_m2 = nummer_punkt2komma($ber->MONATLICH_MEHR / $ber->EINHEIT_QM);
        $tab_arr [1] ['M2'] = "$erh_m2 €";
        $tab_arr [2] ['BEZ'] = "Neue Nettokaltmiete ab dem $ber->N_ANSTIEG_DATUM";
        $tab_arr [2] ['BETRAG'] = "$ber->NEUE_MIETE_A €";
        $tab_arr [2] ['M2'] = "$ber->M2_PREIS_NEU_A €";
        $tab_arr [3] ['BEZ'] = 'Nebenkosten Vorauszahlung';
        $tab_arr [3] ['BETRAG'] = "$ber->B_AKT_NK €";
        $tab_arr [4] ['BEZ'] = 'Heizkosten Vorauszahlung';
        $tab_arr [4] ['BETRAG'] = "$ber->B_AKT_HK €";

        $nr = 4;

        /* Zuschläge Mietentwicklung wie MOD sonstiges */
        $zuabschlag_arr = $this->get_zuabschlag_arr($ber->MV_ID);
        $this->zuabschlag = 0.00;
        if (is_array($zuabschlag_arr)) {
            $anz_za = count($zuabschlag_arr);

            for ($zz = 0; $zz < $anz_za; $zz++) {
                $nr++;
                $bez = $zuabschlag_arr [$zz] ['KOSTENKATEGORIE'];
                $betrag_za = $zuabschlag_arr [$zz] ['BETRAG'];
                $anfang_za = date_mysql2german($zuabschlag_arr [$zz] ['ANFANG']);
                $this->zuabschlag += $betrag_za;
                $tab_arr [$nr] ['BEZ'] = "$bez seit $anfang_za";
                $tab_arr [$nr] ['BETRAG'] = nummer_punkt2komma($betrag_za) . " €";
                $ber->B_AKT_ENDMIETE = nummer_komma2punkt($ber->B_AKT_ENDMIETE) + $betrag_za;
                $ber->B_NEUE_ENDMIETE = nummer_komma2punkt($ber->B_NEUE_ENDMIETE) + $betrag_za;
                $ber->B_AKT_ENDMIETE = nummer_punkt2komma($ber->B_AKT_ENDMIETE);
                $ber->B_NEUE_ENDMIETE = nummer_punkt2komma($ber->B_NEUE_ENDMIETE);
            }
        }

        if (!isset ($ber->B_NEUE_ENDMIETE)) {
            $ber->B_NEUE_ENDMIETE = nummer_punkt2komma(nummer_komma2punkt($ber->B_AKT_ENDMIETE) + $ber->MONATLICH_MEHR);
        }

        $tab_arr [$nr + 1] ['BEZ'] = 'Bisherige Endmiete';
        $tab_arr [$nr + 1] ['BETRAG'] = "$ber->B_AKT_ENDMIETE €";
        $tab_arr [$nr + 2] ['BEZ'] = "Neue Endmiete ab $ber->N_ANSTIEG_DATUM";
        $tab_arr [$nr + 2] ['BETRAG'] = "$ber->B_NEUE_ENDMIETE €</b>";

        $pdf->ezSetDy(-3);
        $cols = array(
            'BEZ' => "",
            'BETRAG' => "Euro/monatlich",
            'M2' => "Euro/m²"
        );
        $pdf->ezTable($tab_arr, $cols, "", array(
            'showHeadings' => 1,
            'shaded' => 0,
            'showLines' => 0,
            'titleFontSize' => 11,
            'fontSize' => 11,
            'xPos' => 55,
            'xOrientation' => 'right',
            'width' => 500,
            'cols' => array(
                'BEZ' => array(
                    'justification' => 'left',
                    'width' => 300
                ),
                'BETRAG' => array(
                    'justification' => 'right',
                    'width' => 100
                ),
                'M2' => array(
                    'justification' => 'right',
                    'width' => 100
                )
            )
        ));
        $o = new objekt ();
        $mysql_date_anstieg = date_german2mysql($ber->N_ANSTIEG_DATUM);
        $datum_zustimmung_frist = date_mysql2german($mysql_date_anstieg);
        $brief_text = "\nGemäß § 558b BGB sind wir berechtigt, gegen Sie Klage auf Zustimmung zur Mieterhöhung zu erheben, falls Sie nicht bis zum Ablauf des zweiten Kalendermonats nach Zugang dieses Erhöhungsverlangens die Zustimmung erteilen. Die Klage muss hierbei innerhalb einer Frist von weiteren drei Monaten erhoben werden. Wir sehen daher Ihrer Zustimmung zur Mieterhöhung gemäß diesem Schreiben bis zum $datum_zustimmung_frist entgegen.\n";
        $pdf->ezText("$brief_text", 11, array(
            'justification' => 'full'
        ));

        /* Dritte Seite */
        $pdf->ezNewPage();
        $brief_text = "Sie schulden den erhöhten Mietzins von Beginn des dritten Monats ab, der auf den Zugang des Erhöhungsverlangens folgt, falls die Zustimmung erteilt wird oder Sie vom Gericht zur Zustimmung verurteilt werden.\n";
        $pdf->ezText("$brief_text", 11, array(
            'justification' => 'full'
        ));
        $brief_text = "Gemäß § 561 BGB steht Ihnen ein Sonderkündigungsrecht für den Ablauf des zweiten Monats nach Zugang der Erklärung zu.\n";
        $pdf->ezText("$brief_text", 11, array(
            'justification' => 'full'
        ));
        $brief_text = "Wir bitten Sie, uns bis spätestens $datum_zustimmung_frist Ihre Zustimmung zu dieser Mieterhöhung schriftlich zu bestätigen und uns die letzte Seite des rechtsverbindlich unterschriebenen Exemplars der Erklärung zurückzusenden.\n";
        $pdf->ezText("$brief_text", 11, array(
            'justification' => 'full'
        ));
        $brief_text = "Dieses Schreiben wurde maschinell erstellt und ist ohne Unterschrift gültig.\n";
        $pdf->ezText("$brief_text", 11, array(
            'justification' => 'full'
        ));

        // $brief_text = "$bpdf->zahlungshinweis";
        // $pdf->ezText("$brief_text",11, array('justification'=>'full'));

        $brief_text = "\n\nAnlagen wie im Text angegeben";
        $pdf->ezText("$brief_text", 8, array(
            'justification' => 'full'
        ));

        /* Vierte Seite ZUSTIMMUNG */
        $pdf->ezNewPage();
        $pdf->ezText("$p->partner_name\n$p->partner_strasse $p->partner_hausnr\n\n$p->partner_plz $p->partner_ort", 12);
        $pdf->ezSetDy(-60);
        // y=ezText(text,[size],[array options])
        $pdf->ezText("<b>ERKLÄRUNG</b>", 14, array(
            'justification' => 'center'
        ));
        $pdf->ezSetDy(-20);
        $pdf->ezText("$mv->personen_name_string_u\n$mv->haus_strasse $mv->haus_nr\n\n$mv->haus_plz $mv->haus_stadt", 12);
        $pdf->ezSetDy(-20);
        $pdf->ezText("<b>Mieter-Nr.: $mv->einheit_kurzname</b>", 12);
        $pdf->ezSetDy(-20);
        $pdf->ezText("Ihrem Mieterhöhungsverlangen für eine neue Gesamtmiete von <b>$ber->B_NEUE_ENDMIETE €\n", 12);
        unset ($tab_arr);
        $tab_arr [0] ['BEZ'] = "Kaltmiete";
        $tab_arr [0] ['BETRAG'] = "$ber->NEUE_MIETE_A €";
        $tab_arr [1] ['BEZ'] = "Bisherige Zu- oder Abschläge (Moderniserung, etc..)";
        $this->zuabschlag_a = nummer_punkt2komma($this->zuabschlag);
        $tab_arr [1] ['BETRAG'] = "$this->zuabschlag_a €";

        $tab_arr [2] ['BEZ'] = 'Nebenkosten Vorauszahlung';
        $tab_arr [2] ['BETRAG'] = "$ber->B_AKT_NK €";
        $tab_arr [3] ['BEZ'] = 'Heizkosten Vorauszahlung';
        $tab_arr [3] ['BETRAG'] = "$ber->B_AKT_HK €</b>";
        $cols = array(
            'BEZ' => "BEZ",
            'BETRAG' => "BETRAG"
        );
        $pdf->ezTable($tab_arr, $cols, "", array(
            'showHeadings' => 0,
            'shaded' => 0,
            'showLines' => 0,
            'titleFontSize' => 11,
            'fontSize' => 11,
            'xPos' => 55,
            'xOrientation' => 'right',
            'width' => 450,
            'cols' => array(
                'BEZ' => array(
                    'justification' => 'left',
                    'width' => 350
                ),
                'BETRAG' => array(
                    'justification' => 'right',
                    'width' => 100
                )
            )
        ));
        $pdf->ezText("\nab dem $ber->N_ANSTIEG_DATUM stimme/en ich/wir zu.\n", 12);
        $pdf->ezSetDy(-30);
        $hoehe = $pdf->y;
        $pdf->ezText("_________________________", 11, array(
            'aleft' => '55'
        ));
        $pdf->ezText("Unterschrift", 10, array(
            'aleft' => '100'
        ));
        $pdf->ezSety($hoehe);
        $pdf->ezText("____________________", 11, array(
            'left' => '320'
        ));
        $pdf->ezText("Datum", 10, array(
            'left' => '370'
        ));
        $pdf->ezSetDy(-60);
        $hoehe = $pdf->y;
        $pdf->ezText("_________________________", 11, array(
            'aleft' => '55'
        ));
        $pdf->ezText("Unterschrift", 10, array(
            'aleft' => '100'
        ));
        $pdf->ezSety($hoehe);
        $pdf->ezText("____________________", 11, array(
            'left' => '320'
        ));
        $pdf->ezText("Datum", 10, array(
            'left' => '370'
        ));
        /* Fünfte Seite ZUSTIMMUNG - Die der Mieter uterschreibt und zurücksendet */
        $pdf->ezNewPage();
        $pdf->ezText("$p->partner_name\n$p->partner_strasse $p->partner_hausnr\n\n$p->partner_plz $p->partner_ort", 12);
        $pdf->ezSetDy(-60);
        // y=ezText(text,[size],[array options])
        $pdf->ezText("<b>ERKLÄRUNG</b>", 14, array(
            'justification' => 'center'
        ));
        $pdf->ezSetDy(-20);
        $pdf->ezText("$mv->personen_name_string_u\n$mv->haus_strasse $mv->haus_nr\n\n$mv->haus_plz $mv->haus_stadt", 12);
        $pdf->ezSetDy(-20);
        $pdf->ezText("<b>Mieter-Nr.: $mv->einheit_kurzname</b>", 12);
        $pdf->ezSetDy(-20);
        $pdf->ezText("Ihrem Mieterhöhungsverlangen für eine neue Gesamtmiete von <b>$ber->B_NEUE_ENDMIETE €\n", 12);
        unset ($tab_arr);
        $tab_arr [0] ['BEZ'] = "Kaltmiete";
        $tab_arr [0] ['BETRAG'] = "$ber->NEUE_MIETE_A €";
        $tab_arr [1] ['BEZ'] = "Bisherige Zu- oder Abschläge (Moderniserung, etc..)";
        $this->zuabschlag_a = nummer_punkt2komma($this->zuabschlag);
        $tab_arr [1] ['BETRAG'] = "$this->zuabschlag_a €";

        $tab_arr [2] ['BEZ'] = 'Nebenkosten Vorauszahlung';
        $tab_arr [2] ['BETRAG'] = "$ber->B_AKT_NK €";
        $tab_arr [3] ['BEZ'] = 'Heizkosten Vorauszahlung';
        $tab_arr [3] ['BETRAG'] = "$ber->B_AKT_HK €</b>";
        $cols = array(
            'BEZ' => "BEZ",
            'BETRAG' => "BETRAG"
        );
        $pdf->ezTable($tab_arr, $cols, "", array(
            'showHeadings' => 0,
            'shaded' => 0,
            'showLines' => 0,
            'titleFontSize' => 11,
            'fontSize' => 11,
            'xPos' => 55,
            'xOrientation' => 'right',
            'width' => 450,
            'cols' => array(
                'BEZ' => array(
                    'justification' => 'left',
                    'width' => 350
                ),
                'BETRAG' => array(
                    'justification' => 'right',
                    'width' => 100
                )
            )
        ));
        $pdf->ezText("\nab dem $ber->N_ANSTIEG_DATUM stimme/en ich/wir zu.\n", 12);
        $pdf->ezSetDy(-30);
        $hoehe = $pdf->y;
        $pdf->ezText("_________________________", 11, array(
            'aleft' => '55'
        ));
        $pdf->ezText("Unterschrift", 10, array(
            'aleft' => '100'
        ));
        $pdf->ezSety($hoehe);
        $pdf->ezText("____________________", 11, array(
            'left' => '320'
        ));
        $pdf->ezText("Datum", 10, array(
            'left' => '370'
        ));
        $pdf->ezSetDy(-60);
        $hoehe = $pdf->y;
        $pdf->ezText("_________________________", 11, array(
            'aleft' => '55'
        ));
        $pdf->ezText("Unterschrift", 10, array(
            'aleft' => '100'
        ));
        $pdf->ezSety($hoehe);
        $pdf->ezText("____________________", 11, array(
            'left' => '320'
        ));
        $pdf->ezText("Datum", 10, array(
            'left' => '370'
        ));

        $this->widerrufsseite($pdf);

        // $pdf->ezNewPage();
        /*
		 * $im = new imagick();
		 * $im->setResolution(600,600);
		 * $im->readImage('Mietspiegeltabelle2009.pdf[0]');
		 * $im->setImageFormat(�png�);
		 * $im->setImageDepth(8);
		 * $im->setImageCompressionQuality(90);
		 * $im->scaleImage(500,0);
		 */
        //

        /* Ausgabe */
        // ob_clean(); //ausgabepuffer leeren
        /*
		 * header("Content-type: application/pdf"); // wird von MSIE ignoriert
		 * $dateiname = $mv->einheit_kurzname."_MHG_zum_".$ber->N_ANSTIEG_DATUM."_vom_".$datum.".pdf";
		 * $pdf_opt['Content-Disposition'] = $dateiname;
		 * $pdf->ezStream($pdf_opt);
		 */
    }

    function get_zuabschlag_arr($mv_id)
    {
        $db_abfrage = "SELECT * FROM `MIETENTWICKLUNG` WHERE `KOSTENKATEGORIE` LIKE 'MOD' AND KOSTENTRAEGER_TYP='MIETVERTRAG' AND KOSTENTRAEGER_ID='$mv_id'";
        $result = DB::select($db_abfrage);
        return $result;
    }

    function widerrufsseite($pdf)
    {
        $p = new partners ();
        $p->get_partner_info(session()->get('partner_id'));
        $pdf->ezNewPage();
        $pdf->ezText("<b>Widerrufsbelehrung</b>\n", 16, array(
            'left' => '150'
        ));
        $pdf->ezText("<b>Widerrufsrecht</b>\n", 9, array(
            'left' => '195'
        ));

        $pdf->ezText("Sie haben das Recht, binnen vierzehn Tagen ohne Angabe von Gründen diesen Vertrag bzw. die abgeschlossene Vereinbarung zu widerrufen.	Die Widerrufsfrist beträgt 14 Tage ab dem Tag des Vertragsabschlusses. Der Vertrag kommt mit Zugang Ihrer Zustimmungserklärung bei uns zustande.\n\n", 9);
        $pdf->ezText("Um Ihr Widerrufsrecht auszuüben, müssen Sie an $p->partner_name $p->partner_strasse $p->partner_hausnr, $p->partner_plz $p->partner_ort mittels einer eindeutigen Erklärung (z. B. ein mit der Post versandter Brief, Telefax oder E-Mail) über Ihren Entschluss, diesen Vertrag zu widerrufen, informieren. Sie können dafür das beigefügte Muster-Widerrufsformular verwenden, das jedoch nicht vorgeschrieben ist.\n", 9);
        $pdf->ezText("Zur Wahrung der Widerrufsfrist reicht es aus, dass Sie die Mitteilung über die Ausübung des Widerrufsrechts vor Ablauf der Widerrufsfrist absenden.", 9);

        $pdf->ezText("\n<b>Folgen des Widerrufs</b>", 9);
        $pdf->ezText("Wenn Sie diesen Vertrag widerrufen, haben wir Ihnen alle Zahlungen, die wir von Ihnen erhalten haben, einschließlich der Lieferkosten (mit Ausnahme der zusätzlichen Kosten, die sich daraus ergeben, dass sie eine andere Art der Lieferung als die von uns angebotene günstigste Standardlieferung gewählt haben), unverzüglich und spätestens binnen vierzehn Tagen ab dem Tag zurückzuzahlen, an dem die Mitteilung über Ihren Widerruf dieses Vertrags bei uns eingegangen ist. Für diese Rückzahlung verwenden wir dasselbe Zahlungsmittel, das Sie bei der ursprünglichen Transaktion eingesetzt haben, es sei denn, mit Ihnen wurde ausdrücklich etwas anderes vereinbart; in keinem Fall werden Ihnen wegen dieser Rückzahlung Entgelte berechnet.", 9);

        $pdf->ezText("<b>Ende der Widerrufsbelehrung</b>", 9);
        $pdf->ezText("\n---------------------------------------------------------------------------------------------------------------------------------------------------------------------", 9);
        $pdf->ezText("\n\n<b>Muster-Widerrufsformular</b>\n", 10);
        $pdf->ezText("Wenn Sie die Vereinbarung widerrufen wollen, dann füllen Sie bitte dieses Formular aus und senden Sie es zurück.\nAn <b>$p->partner_name $p->partner_strasse $p->partner_hausnr, $p->partner_plz $p->partner_ort</b>
	
			Hiermit widerrufe/n ich/wir die von mir/uns abgegebene Zustimmungserklärung zur Mieterhöhung vom _________________
	
	
			- Name des Mieters/der Mieter (Verbraucher):
	
	
			-------------------------------------------------------------------------------
	
	
			- Anschrift des Mieters/der Mieter (Verbraucher):
	
	
			---------------------------------------------------------------------------------------------------------------------------
	
	
			Unterschriften des Mieters/der Mieter (Verbraucher):
	
	
	
			------------------------------------------------  (bitte Datum einfügen)
		", 9, array(
            'justification' => 'left'
        ));
    }
    
    function pdf_anschreiben($ber_array, $datum)
    {
        $ber = ( object )$ber_array;

        /* Merkmalsgruppen */
        $plus = 0;
        $minus = 0;

        if (request()->has('MG1')) {
            if (request()->input('MG1') == '+') {
                $plus++;
                $ber->MG1 = 'Bad/WC|wohnwerterhöhende Merkmale|+20,00 %';
            } else {
                $minus++;
                $ber->MG1 = 'Bad/WC|wohnwertermindernde Merkmale|-20,00 %';
            }
        } else {
            $ber->MG1 = 'Bad/WC|keine Merkmale|00,00 %';
        }

        if (request()->has('MG2')) {
            if (request()->input('MG2') == '+') {
                $plus++;
                $ber->MG2 = 'Küche|wohnwerterhöhende Merkmale|+20,00 %';
            } else {
                $minus++;
                $ber->MG2 = 'Küche|wohnwertermindernde Merkmale|-20,00 %';
            }
        } else {
            $ber->MG2 = 'Küche|keine Merkmale|00,00 %';
        }

        if (request()->has('MG3')) {
            if (request()->input('MG3') == '+') {
                $plus++;
                $ber->MG3 = 'Wohnung|wohnwerterhöhende Merkmale|+20,00 %';
            } else {
                $minus++;
                $ber->MG3 = 'Wohnung|wohnwertermindernde Merkmale|-20,00 %';
            }
        } else {
            $ber->MG3 = 'Wohnung|keine Merkmale|00,00 %';
        }

        if (request()->has('MG4')) {
            if (request()->input('MG4') == '+') {
                $plus++;
                $ber->MG4 = 'Gebäude|wohnwerterhöhende Merkmale|+20,00 %';
            } else {
                $minus++;
                $ber->MG4 = 'Gebäude|wohnwertermindernde Merkmale|-20,00 %';
            }
        } else {
            $ber->MG4 = 'Gebäude|keine Merkmale|00,00 %';
        }

        if (request()->has('MG5')) {
            if (request()->input('MG5') == '+') {
                $plus++;
                $ber->MG5 = 'Wohnumfeld|wohnwerterhöhende Merkmale|+20,00 %';
            } else {
                $minus++;
                $ber->MG5 = 'Wohnumfeld|wohnwertermindernde Merkmale|-20,00 %';
            }
        } else {
            $ber->MG5 = 'Wohnumfeld|keine Merkmale|00,00 %';
        }

        $ber->PLUS = $plus;
        $ber->MINUS = $minus;
        $ber->MG_SALDO = $plus - $minus;
        $ber->MG_SALDO_PROZ = ($plus - $minus) * 20;

        if ($ber->MG_SALDO_PROZ > 0) {
            $ber->MG_20_proz = $ber->O_SPANNE_W / 5;
        } else {
            $ber->MG_20_proz = $ber->U_SPANNE_W / 5;
        }

        $ber->MG_ZUSCHLAG_MAX = $ber->MG_SALDO * ($ber->EINHEIT_QM * $ber->MG_20_proz);
        $ber->MG_ZUSCHLAG_MAX_QM = $ber->MG_SALDO * $ber->MG_20_proz;

        $ber->MG_MIETE_MAX = ($ber->EINHEIT_QM * $ber->M_WERT) + $ber->MG_ZUSCHLAG_MAX;
        $ber->MG_M2_PREIS = $ber->MG_MIETE_MAX / $ber->EINHEIT_QM;
        $ber->ANSTIGEN_MG_PROZ_MAX = ($ber->MG_MIETE_MAX / ($ber->MIETE_3_JAHRE / 100)) - 100;

        $ber->MAX_M2_PREIS_KAPP = nummer_komma2punkt(nummer_punkt2komma($ber->MAXIMALE_MIETE / $ber->EINHEIT_QM));

        /* Wenn MG m2 größer als Kappungsgrenze */
        if ($ber->MG_M2_PREIS < $ber->MAX_M2_PREIS_KAPP) {
            $ber->M2_PREIS_NEU2 = $ber->MG_M2_PREIS;
            // $ber->MONATLICH_MEHR2 = ($ber->M2_PREIS_NEU2 * $ber->EINHEIT_QM) - $ber->MIETE_3_JAHRE;
            $ber->NEUE_MIETE2 = $ber->M2_PREIS_NEU2 * $ber->EINHEIT_QM;
            $ber->MONATLICH_MEHR2 = $ber->NEUE_MIETE2 - $ber->MIETE_AKTUELL;
            $ber->ERH_QM2 = nummer_komma2punkt(nummer_punkt2komma($ber->M2_PREIS_NEU2 - $ber->M2_AKTUELL));
            // $ber->NEUE_MIETE2 = $ber->MIETE_AKTUELL + $ber->MONATLICH_MEHR2;
            $ber->SG_MAX = $ber->M2_PREIS_NEU2;
            $ber->B_NEUE_ENDMIETE = nummer_komma2punkt($ber->B_AKT_ENDMIETE) + $ber->MONATLICH_MEHR2;
            $ber->PROZ_ERH2 = (($ber->NEUE_MIETE2 / ($ber->MIETE_3_JAHRE / 100)) - 100) - $ber->ANSTIEG_3J;
            // echo "SANEL";
        } else {
            $ber->M2_PREIS_NEU2 = $ber->MAX_M2_PREIS_KAPP;
            $ber->NEUE_MIETE2 = $ber->MAXIMALE_MIETE;
            $ber->MONATLICH_MEHR2 = $ber->NEUE_MIETE2 - $ber->MIETE_AKTUELL;
            // $ber->MONATLICH_MEHR2 = ($ber->M2_PREIS_NEU2 * $ber->EINHEIT_QM) - $ber->MIETE_3_JAHRE;
            $ber->ERH_QM2 = nummer_komma2punkt(nummer_punkt2komma($ber->M2_PREIS_NEU2 - $ber->M2_AKTUELL));
            // $ber->NEUE_MIETE2 = $ber->MIETE_AKTUELL + $ber->MONATLICH_MEHR2;
            $ber->SG_MAX = $ber->M2_PREIS_NEU2;
            $ber->B_NEUE_ENDMIETE = nummer_komma2punkt($ber->B_AKT_ENDMIETE) + $ber->MONATLICH_MEHR2;
            $ber->PROZ_ERH2 = (($ber->NEUE_MIETE2 / ($ber->MIETE_3_JAHRE / 100)) - 100) - $ber->ANSTIEG_3J;
        }

        $ber->MIETE_AKTUELL_A = nummer_punkt2komma($ber->MIETE_AKTUELL);
        $ber->EINHEIT_QM_A = nummer_punkt2komma($ber->EINHEIT_QM);
        $ber->M2_AKTUELL_A = nummer_punkt2komma($ber->M2_AKTUELL);
        $ber->M_WERT_A = nummer_punkt2komma($ber->M_WERT);
        $ber->NEUE_MIETE_A = nummer_punkt2komma($ber->NEUE_MIETE2);
        $ber->MONATLICH_MEHR_A = nummer_punkt2komma($ber->MONATLICH_MEHR2);
        $ber->L_ANSTIEG_BETRAG_A = nummer_punkt2komma($ber->L_ANSTIEG_BETRAG);
        $ber->ANSTIEG_3J_A = nummer_punkt2komma($ber->ANSTIEG_3J);
        $ber->MIETE_3_JAHRE_A = nummer_punkt2komma($ber->MIETE_3_JAHRE);
        $ber->SG_MAX_A = nummer_punkt2komma($ber->SG_MAX);
        $ber->MG_SALDO_PROZ_A = nummer_punkt2komma($ber->MG_SALDO_PROZ);
        $ber->MG_ZUSCHLAG_MAX_QM_A = nummer_punkt2komma($ber->MG_ZUSCHLAG_MAX_QM);
        $ber->O_WERT_A = nummer_punkt2komma($ber->O_WERT);
        $ber->M_WERT_A = nummer_punkt2komma($ber->M_WERT);
        $ber->U_WERT_A = nummer_punkt2komma($ber->U_WERT);

        $ber->O_SPANNE_W_A = nummer_punkt2komma($ber->O_SPANNE_W);
        $ber->U_SPANNE_W_A = nummer_punkt2komma($ber->U_SPANNE_W);

        $ber->PROZ_ERH2_A = nummer_punkt2komma($ber->PROZ_ERH2);
        $ber->MONATLICH_MEHR2_A = nummer_punkt2komma($ber->MONATLICH_MEHR2);
        $ber->M2_PREIS_NEU2_A = nummer_punkt2komma($ber->M2_PREIS_NEU2);

        $ber->B_NEUE_ENDMIETE_A = nummer_punkt2komma($ber->B_NEUE_ENDMIETE);

        ob_clean(); // ausgabepuffer leeren
        $pdf = new Cezpdf ('a4', 'portrait');
        $bpdf = new b_pdf ();
        $bpdf->b_header($pdf, 'Partner', session()->get('partner_id'), 'portrait', 'Helvetica.afm', 6);
        $this->footer_zahlungshinweis = $bpdf->zahlungshinweis;

        $pdf->ezStopPageNumbers(); // seitennummerirung beenden
        $p = new partners ();
        $p->get_partner_info(session()->get('partner_id'));
        $pdf->addText(480, 697, 8, "$p->partner_ort, $datum");
        $mv = new mietvertraege ();
        $mv->get_mietvertrag_infos_aktuell($ber->MV_ID);
        if ($mv->anz_zustellanschriften == '0') {
            $pdf->ezText("$mv->personen_name_string_u\n$mv->haus_strasse $mv->haus_nr\n\n$mv->haus_plz $mv->haus_stadt", 12);
            $pdf->addText(250, $pdf->y, 6, "$mv->einheit_lage", 0);
        } else {
            /* Zustelanschrift */
            $postanschrift = $mv->postanschrift [0] ['adresse'];
            $pdf->ezText("$postanschrift", 12);
        }

        $pdf->ezSetDy(-60);
        /* Betreff */
        $pdf->ezText("<b>Mieterhöhungsverlangen zum $ber->N_ANSTIEG_DATUM gemäß §§ 558 BGB ff. des Bürgerlichen Gesetzbuches (BGB) Mieter-Nr.: $mv->einheit_kurzname</b>", 11);
        // $pdf->ezText("Einheit: $mv->einheit_kurzname",12);
        $pdf->ezSetDy(-10);
        /* Faltlinie */
        $pdf->setLineStyle(0.2);
        $pdf->line(5, 542, 20, 542);
        /* Anrede */
        $pdf->ezText("$anrede", 11);
        $pdf->ezText("$mv->mv_anrede", 11);
        $brief_text = "wie Ihnen bekannt ist, vertreten wir die rechtlichen Interessen der Eigentümer. Eine auf uns lautende Vollmacht ist beigefügt.";
        $pdf->ezText("$brief_text", 11, array(
            'justification' => 'full'
        ));
        $brief_text = "Namens und in Vollmacht der Eigentümer werden Sie hiermit gebeten, der Erhöhung der Netto-Kaltmiete gemäß § 558 BGB zuzustimmen. Gemäß der mietvertraglichen Vereinbarung zahlen Sie gegenwärtig eine Nettomiete in Höhe von $ber->MIETE_AKTUELL_A €. Die jeweiligen Angaben beziehen sich auf den monatlichen Mietzins.
	";
        $pdf->ezText("$brief_text", 11, array(
            'justification' => 'full'
        ));

        $tab_arr [0] ['BEZ'] = '<b>Ihre derzeitige Netto-Kaltmiete beträgt:</b>';
        $tab_arr [0] ['BETRAG'] = '<b>' . $ber->MIETE_AKTUELL_A . ' €</b>';
        $tab_arr [1] ['BEZ'] = '<b>Erhöhungsbetrag:</b>';
        $tab_arr [1] ['BETRAG'] = '<b>' . $ber->MONATLICH_MEHR2_A . ' €</b>';
        $tab_arr [2] ['BEZ'] = "<b>Neue Nettokaltmiete ab $ber->N_ANSTIEG_DATUM:</b>";
        $tab_arr [2] ['BETRAG'] = '<b>' . $ber->NEUE_MIETE_A . ' €</b>';
        /*
		 * $tab_arr[3][BEZ] = 'Nebenkosten Vorauszahlung';
		 * $tab_arr[3][BETRAG] = "+ $ber->B_AKT_NK";
		 * $tab_arr[4][BEZ] = 'Heizkosten Vorauszahlung';
		 * $tab_arr[4][BETRAG] = "+ $ber->B_AKT_HK";
		 * $tab_arr[5][BEZ] = 'Alte Endmiete';
		 * $tab_arr[5][BETRAG] = $ber->B_AKT_ENDMIETE;
		 * $tab_arr[6][BEZ] = '<b>Neue Endmiete</b>';
		 * $tab_arr[6][BETRAG] = "<b>$ber->B_NEUE_ENDMIETE</b>";
		 */
        // $pdf->ezSetDy(-10);
        $cols = array(
            'BEZ' => "BEZ",
            'BETRAG' => "BETRAG"
        );
        $pdf->ezTable($tab_arr, $cols, "", array(
            'showHeadings' => 0,
            'shaded' => 0,
            'showLines' => 0,
            'titleFontSize' => 11,
            'fontSize' => 11,
            'xPos' => 55,
            'xOrientation' => 'right',
            'width' => 400,
            'cols' => array(
                'BEZ' => array(
                    'justification' => 'left',
                    'width' => 350
                ),
                'BETRAG' => array(
                    'justification' => 'right',
                    'width' => 50
                )
            )
        ));
        $pdf->ezSetDy(-10);
        $brief_text = "Gemäß § 558 BGB kann der Vermieter die Zustimmung zur Mieterhöhung von Ihnen verlangen, wenn der Mietzins, zu dem die Erhöhung eintreten soll, seit 15 Monaten unverändert und mindestens 1 Jahr nach der letzten Mieterhöhung verstrichen ist. Weiterhin darf sich der Mietzins innerhalb von 3 Jahren um nicht mehr als 15 % erhöhen.";
        $pdf->ezText("$brief_text", 11, array(
            'justification' => 'full'
        ));
        $pdf->ezSetDy(-10);
        $brief_text = "Die mietvertraglich vereinbarte Fläche Ihrer Wohnung beträgt $ber->EINHEIT_QM_A m². Sie zahlen gegenwärtig eine Netto-Kaltmiete in Höhe von $ber->MIETE_AKTUELL_A €. Hieraus errechnet sich eine Miete netto kalt je qm in Höhe von $ber->M2_AKTUELL_A €.";
        $pdf->ezText("$brief_text", 11, array(
            'justification' => 'full'
        ));

        $brief_text = "\nBei der Berechnung der zulässigen Erhöhung gemäß § 558 BGB ist von der gezahlten Netto-Kaltmiete von vor drei Jahren auszugehen.";
        $pdf->ezText("$brief_text", 11, array(
            'justification' => 'full'
        ));
        // $jahr_minus_3 = date("Y")-3;
        // $monat = date("m");
        // $tag = date("d");

        // $datum_vor_3_jahren = "$jahr_minus_3-$monat-$tag";
        // DATUM_3_JAHRE
        $datum_vor_3_jahren_a = $ber->DATUM_3_JAHRE;
        $datum_vor_3_jahren = date_german2mysql($datum_vor_3_jahren_a);
        $ber->EINZUG_A = date_mysql2german($ber->EINZUG);
        $t1 = strtotime("$datum_vor_3_jahren");
        $t2 = strtotime("$ber->EINZUG");
        if ($t2 > $t1) {
            $brief_text = "\nDie Netto-Kaltmiete (ohne Umlagen und Zuschläge) betrug bei Vertragsbeginn am $ber->EINZUG_A $ber->MIETE_3_JAHRE_A €. ";
        } else {
            $brief_text = "\nDie Netto-Kaltmiete (ohne Umlagen und Zuschläge) betrug  am $datum_vor_3_jahren_a $ber->MIETE_3_JAHRE_A €. ";
        }
        $pdf->ezText("$brief_text", 11, array(
            'justification' => 'full'
        ));

        $brief_text = "\nAuf diesen Netto-Kaltmietzins erfolgten innerhalb der letzten drei Jahre Erhöhungen von insgesamt $ber->ANSTIEG_3J_A %.";
        $pdf->ezText("$brief_text", 11, array(
            'justification' => 'full'
        ));

        /*
		 * $erhoehungen_arr = $this->get_erhoehungen_arr($datum_vor_3_jahren, 'MIETVERTRAG', $ber->MV_ID);
		 * if(is_array($erhoehungen_arr)){
		 * $anz_e = count($erhoehungen_arr);
		 * #echo "<tr><th colspan=\"2\">Mieterhöhungen seit 3 Jahren</th></tr>";
		 * $pdf->ezText("\nMieterhöhungen seit 3 Jahren",11, array('justification'=>'full'));
		 * for ($j = 0; $j < $anz_e;$j++){
		 * $k_kat = $erhoehungen_arr[$j]['KOSTENKATEGORIE'];
		 * $anf_kat = date_mysql2german($erhoehungen_arr[$j]['ANFANG']);
		 * $ende_kat = date_mysql2german($erhoehungen_arr[$j]['ENDE']);
		 * if($ende_kat == '00.00.0000'){
		 * $ende_kat = 'unbefristet';
		 * }
		 * $betrag_kat = nummer_punkt2komma($erhoehungen_arr[$j]['BETRAG']);
		 * # echo "<tr><td><b>Von: $anf_kat Bis: $ende_kat - $k_kat</b></td><td>$betrag_kat €</td></tr>";
		 * $pdf->ezText("Vom $anf_kat bis $ende_kat - $k_kat - $betrag_kat €",11, array('justification'=>'full'));
		 * }
		 * }
		 */
        /* Zweite Seite */
        $pdf->ezNewPage();

        // $brief_text = "\nAuf Grundlage des Berliner Mietspiegel für $ber->MS_JAHR (in Kopie beigefügt) ist eine Erhöhung auf $ber->M_WERT_A /m² und unter der Berücksichtigung von Sondermerkmalen und der Kappungsgrenze nach § 558 BGB auf $ber->SG_MAX_A € / m² für Ihre Wohnung möglich.";
        $brief_text = "\nAuf Grundlage des Berliner Mietspiegel für $ber->MS_JAHR (in Kopie beigefügt) ist eine Erhöhung unter der Berücksichtigung von Sondermerkmalen und der Kappungsgrenze nach § 558 BGB auf $ber->SG_MAX_A € / m² für Ihre Wohnung möglich.";
        $pdf->ezText("$brief_text", 11, array(
            'justification' => 'full'
        ));

        $mg1_arr = explode('|', $ber->MG1);
        $mg_tab [0] ['BEZ'] = $mg1_arr [0];
        $mg_tab [0] ['TXT'] = $mg1_arr [1];
        $mg_tab [0] ['PROZ'] = $mg1_arr [2];
        $mg2_arr = explode('|', $ber->MG2);
        $mg_tab [1] ['BEZ'] = $mg2_arr [0];
        $mg_tab [1] ['TXT'] = $mg2_arr [1];
        $mg_tab [1] ['PROZ'] = $mg2_arr [2];
        $mg3_arr = explode('|', $ber->MG3);
        $mg_tab [2] ['BEZ'] = $mg3_arr [0];
        $mg_tab [2] ['TXT'] = $mg3_arr [1];
        $mg_tab [2] ['PROZ'] = $mg3_arr [2];
        $mg4_arr = explode('|', $ber->MG4);
        $mg_tab [3] ['BEZ'] = $mg4_arr [0];
        $mg_tab [3] ['TXT'] = $mg4_arr [1];
        $mg_tab [3] ['PROZ'] = $mg4_arr [2];
        $mg5_arr = explode('|', $ber->MG5);
        $mg_tab [4] ['BEZ'] = $mg5_arr [0];
        $mg_tab [4] ['TXT'] = $mg5_arr [1];
        $mg_tab [4] ['PROZ'] = $mg5_arr [2];
        /* Saldo Tabelle SM */
        $mg_tab [5] ['BEZ'] = '';
        $mg_tab [5] ['TXT'] = 'Saldo der Merkmalgruppen';
        $mg_tab [5] ['PROZ'] = "$ber->MG_SALDO_PROZ_A %";

        $brief_text = "\nBei der Ermittlung des ortsüblichen Vergleichmietzinses aufgrund des qualifizierten Mietspiegels gemäß § 558d BGB sind hierbei folgende wohnungsbezogenen Merkmale zu berücksichtigen.\n";
        $pdf->ezText("$brief_text", 11, array(
            'justification' => 'full'
        ));
        /*
		 * $brief_text = "1.) $ber->MG1";
		 * $pdf->ezText("$brief_text",11, array('justification'=>'full'));
		 * $brief_text = "2.) $ber->MG2";
		 * $pdf->ezText("$brief_text",11, array('justification'=>'full'));
		 * $brief_text = "3.) $ber->MG3";
		 * $pdf->ezText("$brief_text",11, array('justification'=>'full'));
		 * $brief_text = "4.) $ber->MG4";
		 * $pdf->ezText("$brief_text",11, array('justification'=>'full'));
		 * $brief_text = "5.) $ber->MG5";
		 * $pdf->ezText("$brief_text",11, array('justification'=>'full'));
		 * $pdf->ezText("===============================================",11, array('justification'=>'full'));
		 * $pdf->ezText("Saldo der Merkmalgruppen: $ber->MG_SALDO_PROZ_A %\n",11, array('justification'=>'full'));
		 */

        $pdf->ezSetDy(-5);
        $cols = array(
            'BEZ' => "SMG",
            'TXT' => "MERKMALE",
            'PROZ' => "Zu-/Abschlag"
        );
        $pdf->ezTable($mg_tab, $cols, "", array(
            'showHeadings' => 0,
            'shaded' => 1,
            'titleFontSize' => 8,
            'fontSize' => 9,
            'xPos' => 55,
            'xOrientation' => 'right',
            'width' => 525,
            'cols' => array(
                'PROZ' => array(
                    'justification' => 'right',
                    'width' => 70
                )
            )
        ));

        // $pdf->ezTable($mg_tab);

        $pdf->ezSetDy(-10);
        if ($ber->MG_SALDO > 0) {
            $pdf->ezText("Als Anlage erhalten Sie die Online-Berechnung der Stadtentwicklung Berlin.", 12);
            $brief_text = "\nAufgrund dieser Merkmalsgruppen ergibt sich ein Zu-/Abschlag für Ihre Wohnung in Höhe von $ber->MG_SALDO_PROZ_A % bzw. $ber->MG_ZUSCHLAG_MAX_QM_A EUR von $ber->O_SPANNE_W_A EUR/m² -von der Differenz Mittel-/Oberwert ($ber->O_WERT_A € - $ber->M_WERT_A €).";
            $pdf->ezText("$brief_text", 11, array(
                'justification' => 'full'
            ));
        }

        if ($ber->MG_SALDO <= 0) {
            $brief_text = "\nAufgrund dieser Merkmalsgruppen ergibt sich ein Zu-/Abschlag für Ihre Wohnung in Höhe von $ber->MG_SALDO_PROZ_A % bzw. $ber->MG_ZUSCHLAG_MAX_QM_A EUR von $ber->U_SPANNE_W_A EUR/m² -von der Differenz Mittel-/Unterwert ($ber->M_WERT_A € - $ber->U_WERT_A €).";
            $pdf->ezText("$brief_text", 11, array(
                'justification' => 'full'
            ));
        }

        $brief_text = "Die von Ihnen genutzte Wohnung ist dem Mietspiegelfeld <b>$ber->MS_FELD </b>zuzuordnen.";
        $pdf->ezText("$brief_text", 11, array(
            'justification' => 'full'
        ));
        $ber->M_WERT_A = nummer_punkt2komma($ber->M_WERT);

        /* Sondermerkmale finden */
        $sondermerkmale_arr = $this->get_sondermerkmale_arr($ber->AUSSTATTUNGSKLASSE, $ber->MS_JAHR);
        $anz_sm = count($sondermerkmale_arr);
        if ($anz_sm > 0) {
            $d = new detail ();
            $abzug_zaehler = 0;
            $this->abzug_wert = 0;

            for ($s = 0; $s < $anz_sm; $s++) {
                $merkmal = $sondermerkmale_arr [$s] ['MERKMAL'];
                $wert = $sondermerkmale_arr [$s] ['WERT'];
                $a_klasse = $sondermerkmale_arr [$s] ['A_KLASSE'];
                if ($a_klasse == NULL or $ber->AUSSTATTUNGSKLASSE == $a_klasse) {
                    /* Wenn z.B. Erdgeschoss, dann Abzug */
                    $sonder_abzug = $d->finde_detail_inhalt('EINHEIT', $ber->EINHEIT_ID, $merkmal);
                    if ($sonder_abzug) {
                        $abzuege_arr [$abzug_zaehler] ['MERKMAL'] = $merkmal;
                        $abzuege_arr [$abzug_zaehler] ['MERKMAL_GRUND'] = $sonder_abzug;
                        $abzuege_arr [$abzug_zaehler] ['MERKMAL_WERT'] = $wert;
                        $this->abzug_wert = $this->abzug_wert + $wert;
                        $abzug_zaehler++;
                    }
                }
            } // end for
        } // end wenn Sondermerkmale vorhanden

        if (is_array($abzuege_arr)) {
            $this->abzug_wert_a = nummer_punkt2komma($this->abzug_wert);
            $brief_text = "\n<b>Bei Ihrer Wohnung wurden bei der Berechnung folgende wertmindernde Faktoren berücksichtigt:</b>\n";
            $pdf->ezText("$brief_text", 11, array(
                'justification' => 'full'
            ));
            $anz = count($abzuege_arr);
            for ($i = 0; $i < $anz; $i++) {
                $merkm = $abzuege_arr [$i] ['MERKMAL'];
                $merkmw = $abzuege_arr [$i] ['MERKMAL_WERT'];
                $merkmw_a = nummer_punkt2komma($merkmw);
                // echo "<tr><td>$merkm</td><td>$merkmw</td></tr>";
                $pdf->ezText("$merkm          $merkmw_a €/m²", 11);
            }
            $ber->GESAMT_ABZUG_A = nummer_punkt2komma($ber->GESAMT_ABZUG);
            $pdf->ezText("<b>Gesamtminderung              $ber->GESAMT_ABZUG_A €/monatlich</b> (Ihre Fläche: $ber->EINHEIT_QM_A m² X Abzug pro m²: $this->abzug_wert_a €)", 11);
            $neuer_mw = nummer_komma2punkt($ber->M_WERT_A) + $this->abzug_wert;
            $neuer_mw_a = nummer_punkt2komma($neuer_mw);

            $pdf->ezText("Berechnung des Mietspiegelmittelwertes für Ihre Wohnung: $ber->M_WERT_A € $this->abzug_wert_a € = <b>$neuer_mw_a € pro m²</b>", 11, array(
                'justification' => 'full'
            ));
        }

        if ($neuer_mw >= $ber->M2_PREIS_NEU2) {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\ErrorMessage("MIETERHÖHUNG NICHT MÖGLICH: $neuer_mw_a < $ber->M2_PREIS_NEU2_A")
            );
        }

        $ber->ANSTIEG_UM_PROZENT_A = nummer_punkt2komma($ber->ANSTIEG_UM_PROZENT);
        $ber->MONATLICH_MEHR_A = nummer_punkt2komma($ber->MONATLICH_MEHR);
        $brief_text = "\nGemäß § 558 Absatz 3 BGB wird hiermit die Miete um $ber->PROZ_ERH2_A %, ausgehend vom Netto-Kaltmietzins, also um insgesamt $ber->MONATLICH_MEHR2_A €, erhöht.";
        $pdf->ezText("$brief_text", 11, array(
            'justification' => 'full'
        ));
        $ber->M2_PREIS_NEU_A = nummer_punkt2komma($ber->M2_PREIS_NEU);
        $brief_text = "\nNach der Erhöhung beträgt die Nettokaltmiete <b>$ber->M2_PREIS_NEU2_A €/m²</b>. Unter Berücksichtigung der wohnungsbezogenen Merkmale ist der geforderte Mietzins ortsüblich.";
        $pdf->ezText("$brief_text", 11, array(
            'justification' => 'full'
        ));
        // $brief_text = "\n<b>Ihre Neue Gesamtmiete beträgt ab dem $ber->N_ANSTIEG_DATUM insgesamt $ber->B_NEUE_ENDMIETE €.</b>";
        // $pdf->ezText("$brief_text",11, array('justification'=>'full'));

        $brief_text = "\n<b>Diese setzt sich wie folgt zusammen (EURO):</b>";
        $pdf->ezText("$brief_text", 11, array(
            'justification' => 'full'
        ));
        /*
		 * $brief_text = "Kaltmiete: $ber->NEUE_MIETE_A";
		 * $pdf->ezText("$brief_text",11, array('justification'=>'full'));
		 * $brief_text = "Nebenkosten Vorauszahlung: $ber->B_AKT_NK";
		 * $pdf->ezText("$brief_text",11, array('justification'=>'full'));
		 * $brief_text = "Heizkosten Vorauszahlung: $ber->B_AKT_HK";
		 * $pdf->ezText("$brief_text",11, array('justification'=>'full'));
		 */

        $tab_arr [0] ['BEZ'] = '<b>Ihre derzeitige Netto-Kaltmiete beträgt';
        $tab_arr [0] ['BETRAG'] = "$ber->MIETE_AKTUELL_A €";
        $tab_arr [0] ['M2'] = "$ber->M2_AKTUELL_A €";
        $tab_arr [1] ['BEZ'] = 'Erhöhungsbetrag:';
        $tab_arr [1] ['BETRAG'] = "$ber->MONATLICH_MEHR2_A €";
        $erh_m2 = nummer_punkt2komma($ber->MONATLICH_MEHR2 / $ber->EINHEIT_QM);
        $tab_arr [1] ['M2'] = "$erh_m2 €";
        $tab_arr [2] ['BEZ'] = "Neue Nettokaltmiete ab dem $ber->N_ANSTIEG_DATUM";
        $tab_arr [2] ['BETRAG'] = "$ber->NEUE_MIETE_A €";
        $tab_arr [2] ['M2'] = "$ber->M2_PREIS_NEU2_A €";
        $tab_arr [3] ['BEZ'] = 'Nebenkosten Vorauszahlung';
        $tab_arr [3] ['BETRAG'] = "$ber->B_AKT_NK €";
        $tab_arr [4] ['BEZ'] = 'Heizkosten Vorauszahlung';
        $tab_arr [4] ['BETRAG'] = "$ber->B_AKT_HK €";
        if (nummer_komma2punkt($ber->B_AKT_SP) != 0) {
            $tab_arr [5] ['BEZ'] = 'Stellplatzmiete';
            $tab_arr [5] ['BETRAG'] = "$ber->B_AKT_SP €";
            $nr = 5;
        } else {
            $nr = 4;
        }

        /* Zuschläge Mietentwicklung wie MOD sonstiges */
        $zuabschlag_arr = $this->get_zuabschlag_arr($ber->MV_ID);
        $this->zuabschlag = 0.00;
        if (is_array($zuabschlag_arr)) {
            $anz_za = count($zuabschlag_arr);

            for ($zz = 0; $zz < $anz_za; $zz++) {
                $nr++;
                $bez = $zuabschlag_arr [$zz] ['KOSTENKATEGORIE'];
                $betrag_za = $zuabschlag_arr [$zz] ['BETRAG'];
                $anfang_za = date_mysql2german($zuabschlag_arr [$zz] ['ANFANG']);
                $this->zuabschlag += $betrag_za;
                $tab_arr [$nr] ['BEZ'] = "$bez seit $anfang_za";
                $tab_arr [$nr] ['BETRAG'] = nummer_punkt2komma($betrag_za) . " €";
                $ber->B_AKT_ENDMIETE = nummer_komma2punkt($ber->B_AKT_ENDMIETE) + $betrag_za;
                $ber->B_NEUE_ENDMIETE = nummer_komma2punkt($ber->B_NEUE_ENDMIETE) + $betrag_za;
                $ber->B_AKT_ENDMIETE = nummer_punkt2komma($ber->B_AKT_ENDMIETE);
                $ber->B_NEUE_ENDMIETE = nummer_punkt2komma($ber->B_NEUE_ENDMIETE);
            }
        }

        $tab_arr [$nr + 1] ['BEZ'] = 'Bisherige Endmiete';
        $tab_arr [$nr + 1] ['BETRAG'] = "$ber->B_AKT_ENDMIETE €";
        $tab_arr [$nr + 2] ['BEZ'] = "Neue Endmiete ab $ber->N_ANSTIEG_DATUM";
        $tab_arr [$nr + 2] ['BETRAG'] = "$ber->B_NEUE_ENDMIETE_A €</b>";

        $pdf->ezSetDy(-3);
        $cols = array(
            'BEZ' => "",
            'BETRAG' => "Euro/monatlich",
            'M2' => "Euro/m²"
        );
        $pdf->ezTable($tab_arr, $cols, "", array(
            'showHeadings' => 1,
            'shaded' => 0,
            'showLines' => 0,
            'titleFontSize' => 11,
            'fontSize' => 11,
            'xPos' => 55,
            'xOrientation' => 'right',
            'width' => 500,
            'cols' => array(
                'BEZ' => array(
                    'justification' => 'left',
                    'width' => 300
                ),
                'BETRAG' => array(
                    'justification' => 'right',
                    'width' => 100
                ),
                'M2' => array(
                    'justification' => 'right',
                    'width' => 100
                )
            )
        ));
        // $pdf->ezSetDy(-10);
        $o = new objekt ();
        $mysql_date_anstieg = date_german2mysql($ber->N_ANSTIEG_DATUM);
        $datum_zustimmung_frist = date_mysql2german($mysql_date_anstieg);
        $brief_text = "\nGemäß § 558b BGB sind wir berechtigt, gegen Sie Klage auf Zustimmung zur Mieterhöhung zu erheben, falls Sie nicht bis zum Ablauf des zweiten Kalendermonats nach Zugang dieses Erhöhungsverlangens die Zustimmung erteilen. Die Klage muss hierbei innerhalb einer Frist von weiteren drei Monaten erhoben werden. Wir sehen daher Ihrer Zustimmung zur Mieterhöhung gemäß diesem Schreiben bis zum $datum_zustimmung_frist entgegen.\n";
        $pdf->ezText("$brief_text", 11, array(
            'justification' => 'full'
        ));

        /* Dritte Seite */
        $pdf->ezNewPage();
        $brief_text = "Sie schulden den erhöhten Mietzins von Beginn des dritten Monats ab, der auf den Zugang des Erhöhungsverlangens folgt, falls die Zustimmung erteilt wird oder Sie vom Gericht zur Zustimmung verurteilt werden.\n";
        $pdf->ezText("$brief_text", 11, array(
            'justification' => 'full'
        ));
        $brief_text = "Gemäß § 561 BGB steht Ihnen ein Sonderkündigungsrecht für den Ablauf des zweiten Monats nach Zugang der Erklärung zu.\n";
        $pdf->ezText("$brief_text", 11, array(
            'justification' => 'full'
        ));
        $brief_text = "Wir bitten Sie, uns bis spätestens $datum_zustimmung_frist Ihre Zustimmung zu dieser Mieterhöhung schriftlich zu bestätigen und uns die letzte Seite des rechtsverbindlich unterschriebenen Exemplars der Erklärung zurückzusenden.\n";
        $pdf->ezText("$brief_text", 11, array(
            'justification' => 'full'
        ));
        $brief_text = "Dieses Schreiben wurde maschinell erstellt und ist ohne Unterschrift gültig.\n";
        $pdf->ezText("$brief_text", 11, array(
            'justification' => 'full'
        ));

        // $brief_text = "$bpdf->zahlungshinweis";
        // $pdf->ezText("$brief_text",11, array('justification'=>'full'));

        $brief_text = "\n\nAnlagen wie im Text angegeben";
        $pdf->ezText("$brief_text", 8, array(
            'justification' => 'full'
        ));

        /* Vierte Seite ZUSTIMMUNG */
        $pdf->ezNewPage();
        $pdf->ezText("$p->partner_name\n$p->partner_strasse $p->partner_hausnr\n\n$p->partner_plz $p->partner_ort", 12);
        $pdf->ezSetDy(-60);
        $pdf->ezText("<b>ERKLÄRUNG</b>", 14, array(
            'justification' => 'center'
        ));
        $pdf->ezSetDy(-20);
        $pdf->ezText("$mv->personen_name_string_u\n$mv->haus_strasse $mv->haus_nr\n\n$mv->haus_plz $mv->haus_stadt", 12);
        $pdf->ezSetDy(-20);
        $pdf->ezText("<b>Mieter-Nr.: $mv->einheit_kurzname</b>", 12);
        $pdf->ezSetDy(-20);
        $pdf->ezText("Ihrem Mieterhöhungsverlangen für eine neue Gesamtmiete von <b>$ber->B_NEUE_ENDMIETE_A €\n", 12);
        unset ($tab_arr);
        $tab_arr [0] ['BEZ'] = "Kaltmiete";
        $tab_arr [0] ['BETRAG'] = "$ber->NEUE_MIETE_A €";
        $tab_arr [1] ['BEZ'] = "Bisherige Zu- oder Abschläge (Moderniserung, etc..)";
        $this->zuabschlag_a = nummer_punkt2komma($this->zuabschlag);
        $tab_arr [1] ['BETRAG'] = "$this->zuabschlag_a €";

        $tab_arr [2] ['BEZ'] = 'Nebenkosten Vorauszahlung';
        $tab_arr [2] ['BETRAG'] = "$ber->B_AKT_NK €";
        $tab_arr [3] ['BEZ'] = 'Heizkosten Vorauszahlung';
        $tab_arr [3] ['BETRAG'] = "$ber->B_AKT_HK €</b>";
        if (nummer_komma2punkt($ber->B_AKT_SP) != 0) {
            $tab_arr [4] ['BEZ'] = '<b>Stellplatzmiete</b>';
            $tab_arr [4] ['BETRAG'] = "<b>$ber->B_AKT_SP €</b>";
        }
        $cols = array(
            'BEZ' => "BEZ",
            'BETRAG' => "BETRAG"
        );
        $pdf->ezTable($tab_arr, $cols, "", array(
            'showHeadings' => 0,
            'shaded' => 0,
            'showLines' => 0,
            'titleFontSize' => 11,
            'fontSize' => 11,
            'xPos' => 55,
            'xOrientation' => 'right',
            'width' => 450,
            'cols' => array(
                'BEZ' => array(
                    'justification' => 'left',
                    'width' => 350
                ),
                'BETRAG' => array(
                    'justification' => 'right',
                    'width' => 100
                )
            )
        ));
        $pdf->ezText("\nab dem $ber->N_ANSTIEG_DATUM stimme/en ich/wir zu.\n", 12);
        $pdf->ezSetDy(-30);
        $hoehe = $pdf->y;
        $pdf->ezText("_________________________", 11, array(
            'aleft' => '55'
        ));
        $pdf->ezText("Unterschrift", 10, array(
            'aleft' => '100'
        ));
        $pdf->ezSety($hoehe);
        $pdf->ezText("____________________", 11, array(
            'left' => '320'
        ));
        $pdf->ezText("Datum", 10, array(
            'left' => '370'
        ));
        $pdf->ezSetDy(-60);
        $hoehe = $pdf->y;
        $pdf->ezText("_________________________", 11, array(
            'aleft' => '55'
        ));
        $pdf->ezText("Unterschrift", 10, array(
            'aleft' => '100'
        ));
        $pdf->ezSety($hoehe);
        $pdf->ezText("____________________", 11, array(
            'left' => '320'
        ));
        $pdf->ezText("Datum", 10, array(
            'left' => '370'
        ));
        /* Fünfte Seite ZUSTIMMUNG - Die der Mieter uterschreibt und zurücksendet */
        $pdf->ezNewPage();
        $pdf->ezText("$p->partner_name\n$p->partner_strasse $p->partner_hausnr\n\n$p->partner_plz $p->partner_ort", 12);
        $pdf->ezSetDy(-60);
        // y=ezText(text,[size],[array options])
        $pdf->ezText("<b>ERKLÄRUNG</b>", 14, array(
            'justification' => 'center'
        ));
        $pdf->ezSetDy(-20);
        $pdf->ezText("$mv->personen_name_string_u\n$mv->haus_strasse $mv->haus_nr\n\n$mv->haus_plz $mv->haus_stadt", 12);
        $pdf->ezSetDy(-20);
        $pdf->ezText("<b>Mieter-Nr.: $mv->einheit_kurzname</b>", 12);
        $pdf->ezSetDy(-20);
        $pdf->ezText("Ihrem Mieterhöhungsverlangen für eine neue Gesamtmiete von <b>$ber->B_NEUE_ENDMIETE_A €\n", 12);
        unset ($tab_arr);
        $tab_arr [0] ['BEZ'] = "Kaltmiete";
        $tab_arr [0] ['BETRAG'] = "$ber->NEUE_MIETE_A €";
        $tab_arr [1] ['BEZ'] = "Bisherige Zu- oder Abschläge (Moderniserung, etc..)";
        $this->zuabschlag_a = nummer_punkt2komma($this->zuabschlag);
        $tab_arr [1] ['BETRAG'] = "$this->zuabschlag_a €";

        $tab_arr [2] ['BEZ'] = 'Nebenkosten Vorauszahlung';
        $tab_arr [2] ['BETRAG'] = "$ber->B_AKT_NK €";
        $tab_arr [3] ['BEZ'] = 'Heizkosten Vorauszahlung';
        $tab_arr [3] ['BETRAG'] = "$ber->B_AKT_HK €</b>";
        if (nummer_komma2punkt($ber->B_AKT_SP) != 0) {
            $tab_arr [4] ['BEZ'] = '<b>Stellplatzmiete</b>';
            $tab_arr [4] ['BETRAG'] = "<b>$ber->B_AKT_SP €</b>";
        }
        $cols = array(
            'BEZ' => "BEZ",
            'BETRAG' => "BETRAG"
        );
        $pdf->ezTable($tab_arr, $cols, "", array(
            'showHeadings' => 0,
            'shaded' => 0,
            'showLines' => 0,
            'titleFontSize' => 11,
            'fontSize' => 11,
            'xPos' => 55,
            'xOrientation' => 'right',
            'width' => 450,
            'cols' => array(
                'BEZ' => array(
                    'justification' => 'left',
                    'width' => 350
                ),
                'BETRAG' => array(
                    'justification' => 'right',
                    'width' => 100
                )
            )
        ));
        $pdf->ezText("\nab dem $ber->N_ANSTIEG_DATUM stimme/en ich/wir zu.\n", 12);
        $pdf->ezSetDy(-30);
        $hoehe = $pdf->y;
        $pdf->ezText("_________________________", 11, array(
            'aleft' => '55'
        ));
        $pdf->ezText("Unterschrift", 10, array(
            'aleft' => '100'
        ));
        $pdf->ezSety($hoehe);
        $pdf->ezText("____________________", 11, array(
            'left' => '320'
        ));
        $pdf->ezText("Datum", 10, array(
            'left' => '370'
        ));
        $pdf->ezSetDy(-60);
        $hoehe = $pdf->y;
        $pdf->ezText("_________________________", 11, array(
            'aleft' => '55'
        ));
        $pdf->ezText("Unterschrift", 10, array(
            'aleft' => '100'
        ));
        $pdf->ezSetY($hoehe);
        $pdf->ezText("____________________", 11, array(
            'left' => '320'
        ));
        $pdf->ezText("Datum", 10, array(
            'left' => '370'
        ));

        $pdf->ezNewPage();
        $this->widerrufsseite($pdf);

        /* Ausgabe */
        ob_end_clean(); // ausgabepuffer leeren
        $dateiname = $mv->einheit_kurzname . "_MHG_zum_" . $ber->N_ANSTIEG_DATUM . "_vom_" . $datum . ".pdf";
        $pdf_opt ['Content-Disposition'] = $dateiname;
        $pdf->ezStream($pdf_opt);
    }

    function pdf_anschreiben_bruttomieter($ber_array, $datum)
    {
        $ber = ( object )$ber_array;
        $ber->MIETE_AKTUELL_A = nummer_punkt2komma($ber->MIETE_AKTUELL);
        $ber->MIETE_AKTUELL_BRUTTO_A = nummer_punkt2komma($ber->MIETE_AKTUELL_BRUTTO);
        $ber->EINHEIT_QM_A = nummer_punkt2komma($ber->EINHEIT_QM);
        $ber->M2_AKTUELL_A = nummer_punkt2komma($ber->M2_AKTUELL);
        $ber->M_WERT_A = nummer_punkt2komma($ber->M_WERT);
        $ber->NEUE_MIETE_A = nummer_punkt2komma($ber->NEUE_MIETE);
        $ber->NEUE_BRUTTO_MIETE_A = $ber->NEUE_BRUTTO_MIETE;
        $ber->MONATLICH_MEHR_A = nummer_punkt2komma($ber->MONATLICH_MEHR);
        $ber->L_ANSTIEG_BETRAG_A = nummer_punkt2komma($ber->L_ANSTIEG_BETRAG);
        $ber->ANSTIEG_3J_A = nummer_punkt2komma($ber->ANSTIEG_3J);

        $ber->TAT_KOST_J_A = $ber->TAT_KOST_J;
        $ber->TAT_KOST_M_A = $ber->TAT_KOST_M;

        ob_clean(); // ausgabepuffer leeren
        $pdf = new Cezpdf ('a4', 'portrait');
        $bpdf = new b_pdf ();
        $bpdf->b_header($pdf, 'Partner', session()->get('partner_id'), 'portrait', 'Helvetica.afm', 6);
        $this->footer_zahlungshinweis = $bpdf->zahlungshinweis;

        $pdf->ezStopPageNumbers(); // seitennummerirung beenden
        $p = new partners ();
        $p->get_partner_info(session()->get('partner_id'));
        $pdf->addText(480, 697, 8, "$p->partner_ort, $datum");
        $mv = new mietvertraege ();
        $mv->get_mietvertrag_infos_aktuell($ber->MV_ID);
        if ($mv->anz_zustellanschriften == '0') {
            $pdf->ezText("$mv->personen_name_string_u\n$mv->haus_strasse $mv->haus_nr\n\n$mv->haus_plz $mv->haus_stadt", 12);
            $pdf->addText(250, $pdf->y, 6, "$mv->einheit_lage", 0);
        } else {
            /* Zustelanschrift */
            $postanschrift = $mv->postanschrift [0] ['adresse'];
            $pdf->ezText("$postanschrift", 12);
        }
        $pdf->ezSetDy(-60);
        /* Betreff */
        $pdf->ezText("<b>Mieterhöhungsverlangen zum $ber->N_ANSTIEG_DATUM gemäß §§ 558 BGB ff. des Bürgerlichen Gesetzbuches (BGB) Mieter-Nr.: $mv->einheit_kurzname</b>", 11);
        // $pdf->ezText("Einheit: $mv->einheit_kurzname",12);
        $pdf->ezSetDy(-10);
        /* Faltlinie */
        $pdf->setLineStyle(0.2);
        $pdf->line(5, 542, 20, 542);
        /* Anrede */
        $pdf->ezText("$anrede", 11);
        $pdf->ezText("$mv->mv_anrede", 11);
        $brief_text = "wie Ihnen bekannt ist, vertreten wir die rechtlichen Interessen der Eigentümer. Eine auf uns lautende Vollmacht ist beigefügt.";
        $pdf->ezText("$brief_text", 11, array(
            'justification' => 'full'
        ));
        $brief_text = "Namens und in Vollmacht der Eigentümer werden Sie hiermit gebeten, der Erhöhung der Miete gemäß § 558 BGB zuzustimmen. Gemäß der mietvertraglichen Vereinbarung zahlen Sie gegenwärtig eine Bruttomiete in Höhe von $ber->MIETE_AKTUELL_BRUTTO_A €. Die jeweiligen Angaben beziehen sich auf den monatlichen Mietzins.";
        $brief_text .= " Die tatsächlichen Nebenkosten für Ihre Wohnung betragen im Jahr <b>$ber->TAT_KOST_J_A €</b>. Als Kostennachweis legen wir die aktuelle Betriebs- und Nebenkostenabrechnung für Ihre Wohnung bei.\n";
        $pdf->ezText("$brief_text", 11, array(
            'justification' => 'full'
        ));

        $tab_arr [0] ['BEZ'] = '<b>Ihre derzeitige Brutto-Kaltmiete beträgt:</b>';
        $tab_arr [0] ['BETRAG'] = '<b>' . $ber->MIETE_AKTUELL_BRUTTO_A . ' €</b>';
        $tab_arr [2] ['BEZ'] = "<b>Tatsächliche Nebenkosten im Monat ($ber->TAT_KOST_J_A € / 12 M):</b>";
        $tab_arr [2] ['BETRAG'] = '<b>' . $ber->TAT_KOST_M_A . ' €</b>';
        $tab_arr [3] ['BEZ'] = '<b>Errechnete Nettokaltmiete:</b>';
        $tab_arr [3] ['BETRAG'] = '<b>' . $ber->MIETE_AKTUELL_A . ' €</b>';

        $tab_arr [4] ['BEZ'] = '<b>Errechneter Preis pro m²:</b>';
        $tab_arr [4] ['BETRAG'] = '<b>' . $ber->M2_AKTUELL_A . ' €</b>';

        $tab_arr [5] ['BEZ'] = '<b>Erhöhungsbetrag im Monat:</b>';
        $tab_arr [5] ['BETRAG'] = '<b>' . $ber->MONATLICH_MEHR_A . ' €</b>';

        $tab_arr [6] ['BEZ'] = "<b>Neue Bruttokaltmiete ab $ber->N_ANSTIEG_DATUM:</b>";
        $tab_arr [6] ['BETRAG'] = '<b>' . $ber->NEUE_BRUTTO_MIETE_A . ' €</b>';
        
        $cols = array(
            'BEZ' => "BEZ",
            'BETRAG' => "BETRAG"
        );
        $pdf->ezTable($tab_arr, $cols, "", array(
            'showHeadings' => 0,
            'shaded' => 0,
            'showLines' => 0,
            'titleFontSize' => 11,
            'fontSize' => 11,
            'xPos' => 55,
            'xOrientation' => 'right',
            'width' => 400,
            'cols' => array(
                'BEZ' => array(
                    'justification' => 'left',
                    'width' => 350
                ),
                'BETRAG' => array(
                    'justification' => 'right',
                    'width' => 50
                )
            )
        ));
        $pdf->ezSetDy(-10);
        $brief_text = "Gemäß § 558 BGB kann der Vermieter die Zustimmung zur Mieterhöhung von Ihnen verlangen, wenn der Mietzins, zu dem Zeitpunkt, an dem die Erhöhung eintreten soll, seit 15 Monaten unverändert und mindestens 1 Jahr nach der letzten Mieterhöhung verstrichen ist. Weiterhin darf sich der Mietzins innerhalb von 3 Jahren um nicht mehr als 15 % erhöhen.";
        $pdf->ezText("$brief_text", 11, array(
            'justification' => 'full'
        ));
        $pdf->ezSetDy(-10);
        $brief_text = "Die mietvertraglich vereinbarte Fläche Ihrer Wohnung beträgt $ber->EINHEIT_QM_A m². Sie zahlen gegenwärtig eine Netto-Kaltmiete in Höhe von $ber->MIETE_AKTUELL_A €. Hieraus errechnet sich eine Miete netto kalt je qm in Höhe von $ber->M2_AKTUELL_A €.";
        $pdf->ezText("$brief_text", 11, array(
            'justification' => 'full'
        ));

        $brief_text = "\nBei der Berechnung der zulässigen Erhöhung gemäß § 558 BGB ist von der gezahlten Netto-Kaltmiete von vor drei Jahren auszugehen.";
        $pdf->ezText("$brief_text", 11, array(
            'justification' => 'full'
        ));
        $datum_vor_3_jahren_a = $ber->DATUM_3_JAHRE;
        $datum_vor_3_jahren = date_german2mysql($datum_vor_3_jahren_a);
        $ber->EINZUG_A = date_mysql2german($ber->EINZUG);
        $t1 = strtotime("$datum_vor_3_jahren");
        $t2 = strtotime("$ber->EINZUG");
        if ($t2 > $t1) {
            $brief_text = "\nDie Bruttokaltmiete betrug bei Vertragsbeginn am $ber->EINZUG_A $ber->L_ANSTIEG_BETRAG_A €. ";
        } else {
            $brief_text = "\nDie Bruttokaltmiete betrug am $datum_vor_3_jahren_a $ber->L_ANSTIEG_BETRAG_A €. ";
        }
        $pdf->ezText("$brief_text", 11, array(
            'justification' => 'full'
        ));

        $brief_text = "\nAuf diesen Bruttokaltmietzins erfolgten innerhalb der letzten drei Jahre Erhöhungen von insgesamt $ber->ANSTIEG_3J_A %.";
        $pdf->ezText("$brief_text", 11, array(
            'justification' => 'full'
        ));

        /*
		 * $erhoehungen_arr = $this->get_erhoehungen_arr($datum_vor_3_jahren, 'MIETVERTRAG', $ber->MV_ID);
		 * if(is_array($erhoehungen_arr)){
		 * $anz_e = count($erhoehungen_arr);
		 * #echo "<tr><th colspan=\"2\">Mieterhöhungen seit 3 Jahren</th></tr>";
		 * $pdf->ezText("\nMieterhöhungen seit 3 Jahren",11, array('justification'=>'full'));
		 * for ($j = 0; $j < $anz_e;$j++){
		 * $k_kat = $erhoehungen_arr[$j]['KOSTENKATEGORIE'];
		 * $anf_kat = date_mysql2german($erhoehungen_arr[$j]['ANFANG']);
		 * $ende_kat = date_mysql2german($erhoehungen_arr[$j]['ENDE']);
		 * if($ende_kat == '00.00.0000'){
		 * $ende_kat = 'unbefristet';
		 * }
		 * $betrag_kat = nummer_punkt2komma($erhoehungen_arr[$j]['BETRAG']);
		 * # echo "<tr><td><b>Von: $anf_kat Bis: $ende_kat - $k_kat</b></td><td>$betrag_kat €</td></tr>";
		 * $pdf->ezText("Vom $anf_kat bis $ende_kat - $k_kat - $betrag_kat €",11, array('justification'=>'full'));
		 * }
		 * }
		 */
        /* Zweite Seite */
        $pdf->ezNewPage();

        $brief_text = "\nAuf Grundlage des Berliner Mietspiegel für $ber->MS_JAHR (in Kopie beigefügt) und unter der Berücksichtigung von Sondermerkmalen ist eine Erhöhung auf $ber->M_WERT_A € / m² für Ihre Wohnung möglich.";
        $pdf->ezText("$brief_text", 11, array(
            'justification' => 'full'
        ));

        $brief_text = "\nBei der Ermittlung des ortsüblichen Vergleichmietzinses aufgrund des qualifizierten Mietspiegels gemäß § 558d BGB sind hierbei folgende wohnungsbezogenen Merkmale zu berücksichtigen.\n";
        $pdf->ezText("$brief_text", 11, array(
            'justification' => 'full'
        ));
        $brief_text = "Merkmalgruppe 1:  Bad/WC";
        $pdf->ezText("$brief_text", 11, array(
            'justification' => 'full'
        ));
        $brief_text = "Merkmalgruppe 2:  Küche";
        $pdf->ezText("$brief_text", 11, array(
            'justification' => 'full'
        ));
        $brief_text = "Merkmalgruppe 3:  Wohnung";
        $pdf->ezText("$brief_text", 11, array(
            'justification' => 'full'
        ));
        $brief_text = "Merkmalgruppe 4:  Gebäude";
        $pdf->ezText("$brief_text", 11, array(
            'justification' => 'full'
        ));
        $brief_text = "Merkmalgruppe 5:  Wohnumfeld";
        $pdf->ezText("$brief_text", 11, array(
            'justification' => 'full'
        ));

        $brief_text = "\nAufgrund dieser Merkmalsgruppen ergibt sich ein Zu-/Abschlag für Ihre Wohnung in Höhe von 0,00 %.";
        $pdf->ezText("$brief_text", 11, array(
            'justification' => 'full'
        ));
        $brief_text = "Die von Ihnen genutzte Wohnung ist dem Mietspiegelfeld <b>$ber->MS_FELD </b>zuzuordnen.";
        $pdf->ezText("$brief_text", 11, array(
            'justification' => 'full'
        ));
        $ber->M_WERT_A = nummer_punkt2komma($ber->M_WERT);

        /* Sondermerkmale finden */
        $sondermerkmale_arr = $this->get_sondermerkmale_arr($ber->AUSSTATTUNGSKLASSE, $ber->MS_JAHR);
        $anz_sm = count($sondermerkmale_arr);
        if ($anz_sm > 0) {
            $d = new detail ();
            $abzug_zaehler = 0;
            $this->abzug_wert = 0;

            for ($s = 0; $s < $anz_sm; $s++) {
                $merkmal = $sondermerkmale_arr [$s] ['MERKMAL'];
                $wert = $sondermerkmale_arr [$s] ['WERT'];
                $a_klasse = $sondermerkmale_arr [$s] ['A_KLASSE'];
                if ($a_klasse == NULL or $ber->AUSSTATTUNGSKLASSE == $a_klasse) {
                    /* Wenn z.B. Erdgeschoss, dann Abzug */
                    $sonder_abzug = $d->finde_detail_inhalt('EINHEIT', $ber->EINHEIT_ID, $merkmal);
                    if ($sonder_abzug) {
                        $abzuege_arr [$abzug_zaehler] ['MERKMAL'] = $merkmal;
                        $abzuege_arr [$abzug_zaehler] ['MERKMAL_GRUND'] = $sonder_abzug;
                        $abzuege_arr [$abzug_zaehler] ['MERKMAL_WERT'] = $wert;
                        $this->abzug_wert = $this->abzug_wert + $wert;
                        $abzug_zaehler++;
                    }
                }
            } // end for
        } // end wenn Sondermerkmale vorhanden

        if (is_array($abzuege_arr)) {
            $this->abzug_wert_a = nummer_punkt2komma($this->abzug_wert);
            $brief_text = "\n<b>Bei Ihrer Wohnung wurden bei der Berechnung folgende wertmindernde Faktoren berücksichtigt:</b>\n";
            $pdf->ezText("$brief_text", 11, array(
                'justification' => 'full'
            ));
            $anz = count($abzuege_arr);
            for ($i = 0; $i < $anz; $i++) {
                $merkm = $abzuege_arr [$i] ['MERKMAL'];
                $merkmw = $abzuege_arr [$i] ['MERKMAL_WERT'];
                $merkmw_a = nummer_punkt2komma($merkmw);
                // echo "<tr><td>$merkm</td><td>$merkmw</td></tr>";
                $pdf->ezText("$merkm          $merkmw_a €/m²", 11);
            }
            $ber->GESAMT_ABZUG_A = nummer_punkt2komma($ber->GESAMT_ABZUG);
            $pdf->ezText("<b>Gesamtminderung              $ber->GESAMT_ABZUG_A €/monatlich</b> (Ihre Fläche: $ber->EINHEIT_QM_A m² X Abzug pro m²: $this->abzug_wert_a €)", 11);
            $neuer_mw = nummer_komma2punkt($ber->M_WERT_A) + $this->abzug_wert;
            $neuer_mw_a = nummer_punkt2komma($neuer_mw);

            $pdf->ezText("Berechnung des Mietspiegelmittelwertes für Ihre Wohnung: $ber->M_WERT_A € $this->abzug_wert_a € = <b>$neuer_mw_a € pro m²</b>", 11, array(
                'justification' => 'full'
            ));
        }

        $ber->ANSTIEG_UM_PROZENT_A = nummer_punkt2komma($ber->ANSTIEG_UM_PROZENT);
        $ber->MONATLICH_MEHR_A = nummer_punkt2komma($ber->MONATLICH_MEHR);
        $brief_text = "\nGemäß § 558 Absatz 3 BGB wird hiermit die Miete um $ber->ANSTIEG_UM_PROZENT_A %, ausgehend vom Netto-Kaltmietzins, also um insgesamt $ber->MONATLICH_MEHR_A €, erhöht.";
        $pdf->ezText("$brief_text", 11, array(
            'justification' => 'full'
        ));
        $ber->M2_PREIS_NEU_A = nummer_punkt2komma($ber->M2_PREIS_NEU);
        $brief_text = "\nNach der Erhöhung beträgt die Nettokaltmiete $ber->M2_PREIS_NEU_A €/m². Unter Berücksichtigung der wohnungsbezogenen Merkmale ist der geforderte Mietzins ortsüblich.";
        $pdf->ezText("$brief_text", 11, array(
            'justification' => 'full'
        ));
        $brief_text = "\n<b>Ihre neue Bruttokaltmiete beträgt ab dem $ber->N_ANSTIEG_DATUM insgesamt $ber->NEUE_BRUTTO_MIETE_A €.</b>";
        $pdf->ezText("$brief_text", 11, array(
            'justification' => 'full'
        ));

        $brief_text = "\n<b>Diese setzt sich wie folgt zusammen:</b>";
        $pdf->ezText("$brief_text", 11, array(
            'justification' => 'full'
        ));
        /*
		 * $brief_text = "Kaltmiete: $ber->NEUE_MIETE_A";
		 * $pdf->ezText("$brief_text",11, array('justification'=>'full'));
		 * $brief_text = "Nebenkosten Vorauszahlung: $ber->B_AKT_NK";
		 * $pdf->ezText("$brief_text",11, array('justification'=>'full'));
		 * $brief_text = "Heizkosten Vorauszahlung: $ber->B_AKT_HK";
		 * $pdf->ezText("$brief_text",11, array('justification'=>'full'));
		 */
        $tab_arr = Array();
        $tab_arr [0] ['BEZ'] = '<b>Ihre derzeitige Netto-Kaltmiete beträgt';
        $tab_arr [0] ['BETRAG'] = "$ber->MIETE_AKTUELL_A €";
        $tab_arr [0] ['M2'] = "$ber->M2_AKTUELL_A €";
        $tab_arr [1] ['BEZ'] = 'Erhöhungsbetrag:';
        $tab_arr [1] ['BETRAG'] = "$ber->MONATLICH_MEHR_A €";
        $erh_m2 = nummer_punkt2komma($ber->MONATLICH_MEHR / $ber->EINHEIT_QM);
        $tab_arr [1] ['M2'] = "$erh_m2 €";
        $tab_arr [2] ['BEZ'] = "Neue Nettokaltmiete ab dem $ber->N_ANSTIEG_DATUM";
        $tab_arr [2] ['BETRAG'] = "$ber->NEUE_MIETE_A €";
        $tab_arr [2] ['M2'] = "$ber->M2_PREIS_NEU_A €";
        $tab_arr [4] ['BEZ'] = 'Tatsächliche Nebenkosten';
        $tab_arr [4] ['BETRAG'] = "$ber->TAT_KOST_M_A €";
        $tab_arr [5] ['BEZ'] = 'Bisherige Endmiete';
        $tab_arr [5] ['BETRAG'] = "$ber->MIETE_AKTUELL_BRUTTO_A €";
        $tab_arr [6] ['BEZ'] = "Neue Bruttokaltmiete ab $ber->N_ANSTIEG_DATUM";
        $tab_arr [6] ['BETRAG'] = "$ber->NEUE_BRUTTO_MIETE_A €</b>";

        $pdf->ezSetDy(-3);
        $cols = array(
            'BEZ' => "",
            'BETRAG' => "€/monatlich",
            'M2' => "€/m²"
        );
        $pdf->ezTable($tab_arr, $cols, "", array(
            'showHeadings' => 1,
            'shaded' => 0,
            'showLines' => 0,
            'titleFontSize' => 11,
            'fontSize' => 11,
            'xPos' => 55,
            'xOrientation' => 'right',
            'width' => 500,
            'cols' => array(
                'BEZ' => array(
                    'justification' => 'left',
                    'width' => 300
                ),
                'BETRAG' => array(
                    'justification' => 'right',
                    'width' => 100
                ),
                'M2' => array(
                    'justification' => 'right',
                    'width' => 100
                )
            )
        ));
        $mysql_date_anstieg = date_german2mysql($ber->N_ANSTIEG_DATUM);
        $datum_zustimmung_frist = date_mysql2german($mysql_date_anstieg);

        /* Dritte Seite */
        $pdf->ezNewPage();
        $brief_text = "Sie schulden den erhöhten Mietzins von Beginn des dritten Monats ab, der auf den Zugang des Erhöhungsverlangens folgt, falls die Zustimmung erteilt wird oder Sie vom Gericht zur Zustimmung verurteilt werden.\n";
        $pdf->ezText("$brief_text", 11, array(
            'justification' => 'full'
        ));
        // $brief_text = "Gemäß § 561 BGB steht Ihnen ein Sonderkündigungsrecht für den Ablauf des zweiten Monats nach Zugang der Erklärung zu.\n";
        $brief_text = "Gemäß § 561 BGB haben Sie ein Sonderkündigungsrecht. Sie können bis zum Ablauf des zweiten Monats nach dem Zugang der Erklärung das Mietverhältnis außerordentlich zum Ablauf des übernächsten Monats kündigen.\n";
        $pdf->ezText("$brief_text", 11, array(
            'justification' => 'full'
        ));
        $brief_text = "Wir bitten Sie, uns bis spätestens $datum_zustimmung_frist Ihre Zustimmung zu dieser Mieterhöhung schriftlich zu bestätigen und uns die letzte Seite des rechtsverbindlich unterschriebenen Exemplars der Erklärung zurückzusenden.\n";
        $pdf->ezText("$brief_text", 11, array(
            'justification' => 'full'
        ));
        // $brief_text = "Dieses Schreiben wurde maschinell erstellt und ist ohne Unterschrift gültig.\n";
        // $pdf->ezText("$brief_text",11, array('justification'=>'full'));

        $brief_text = "\nGemäß § 558b BGB sind wir berechtigt, gegen Sie Klage auf Zustimmung zur Mieterhöhung zu erheben, falls Sie nicht bis zum Ablauf des zweiten Kalendermonats nach Zugang dieses Erhöhungsverlangens die Zustimmung erteilen. Die Klage muss hierbei innerhalb einer Frist von weiteren drei Monaten erhoben werden. Wir sehen daher Ihrer Zustimmung zur Mieterhöhung gemäß diesem Schreiben bis zum $datum_zustimmung_frist entgegen.\n";
        $pdf->ezText("$brief_text", 11, array(
            'justification' => 'full'
        ));

        // $brief_text = "$bpdf->zahlungshinweis";
        // $pdf->ezText("$brief_text",11, array('justification'=>'full'));

        $brief_text = "Dieses Schreiben wurde maschinell erstellt und ist ohne Unterschrift gültig.\n\n\nIhre Hausverwaltung";
        $pdf->ezText("$brief_text", 11, array(
            'justification' => 'full'
        ));

        $brief_text = "\n\nAnlagen wie im Text angegeben";
        $pdf->ezText("$brief_text", 8, array(
            'justification' => 'full'
        ));

        /* Vierte Seite ZUSTIMMUNG */
        $pdf->ezNewPage();
        $pdf->ezText("$p->partner_name\n$p->partner_strasse $p->partner_hausnr\n\n$p->partner_plz $p->partner_ort", 11);
        $pdf->ezSetDy(-60);
        // y=ezText(text,[size],[array options])
        $pdf->ezText("<b>ERKLÄRUNG</b>", 14, array(
            'justification' => 'center'
        ));
        $pdf->ezSetDy(-20);
        $pdf->ezText("$mv->personen_name_string_u\n$mv->haus_strasse $mv->haus_nr\n\n$mv->haus_plz $mv->haus_stadt", 11);
        $pdf->ezSetDy(-20);
        $pdf->ezText("<b>Mieter-Nr.: $mv->einheit_kurzname</b>", 12);
        $pdf->ezSetDy(-20);
        $pdf->ezText("Ihrem Mieterhöhungsverlangen für eine neue Bruttokaltmiete von <b>$ber->NEUE_BRUTTO_MIETE_A €</b>\n", 11);
        unset ($tab_arr);
        $tab_arr [0] ['BEZ'] = "Kaltmiete";
        $tab_arr [0] ['BETRAG'] = "$ber->NEUE_MIETE_A €";
        $tab_arr [1] ['BEZ'] = 'Tatsächlicher Nebenkostenanteil';
        $tab_arr [1] ['BETRAG'] = "$ber->TAT_KOST_M €";
        $tab_arr [2] ['BEZ'] = 'Heizkosten Vorauszahlung';
        $tab_arr [2] ['BETRAG'] = "$ber->B_AKT_HK €";
        if (nummer_komma2punkt($ber->B_AKT_SP) != 0) {
            $tab_arr [3] ['BEZ'] = 'Stellplatzmiete';
            $tab_arr [3] ['BETRAG'] = "$ber->B_AKT_SP €";
        }

        $cols = array(
            'BEZ' => "BEZ",
            'BETRAG' => "BETRAG"
        );
        $pdf->ezTable($tab_arr, $cols, "", array(
            'showHeadings' => 0,
            'shaded' => 0,
            'showLines' => 0,
            'titleFontSize' => 11,
            'fontSize' => 11,
            'xPos' => 55,
            'xOrientation' => 'right',
            'width' => 450,
            'cols' => array(
                'BEZ' => array(
                    'justification' => 'left',
                    'width' => 350
                ),
                'BETRAG' => array(
                    'justification' => 'right',
                    'width' => 100
                )
            )
        ));
        $pdf->ezText("\nab dem $ber->N_ANSTIEG_DATUM stimme/en ich/wir zu.\n", 11);
        $pdf->ezSetDy(-30);
        $hoehe = $pdf->y;
        $pdf->ezText("_________________________", 11, array(
            'aleft' => '55'
        ));
        $pdf->ezText("Unterschrift", 10, array(
            'aleft' => '100'
        ));
        $pdf->ezSety($hoehe);
        $pdf->ezText("____________________", 11, array(
            'left' => '320'
        ));
        $pdf->ezText("Datum", 10, array(
            'left' => '370'
        ));
        $pdf->ezSetDy(-60);
        $hoehe = $pdf->y;
        $pdf->ezText("_________________________", 11, array(
            'aleft' => '55'
        ));
        $pdf->ezText("Unterschrift", 10, array(
            'aleft' => '100'
        ));
        $pdf->ezSety($hoehe);
        $pdf->ezText("____________________", 11, array(
            'left' => '320'
        ));
        $pdf->ezText("Datum", 10, array(
            'left' => '370'
        ));
        /* Fünfte Seite ZUSTIMMUNG - Die der Mieter uterschreibt und zurücksendet */
        $pdf->ezNewPage();
        $pdf->ezText("$p->partner_name\n$p->partner_strasse $p->partner_hausnr\n\n$p->partner_plz $p->partner_ort", 12);
        $pdf->ezSetDy(-60);
        // y=ezText(text,[size],[array options])
        $pdf->ezText("<b>ERKLÄRUNG</b>", 14, array(
            'justification' => 'center'
        ));
        $pdf->ezSetDy(-20);
        $pdf->ezText("$mv->personen_name_string_u\n$mv->haus_strasse $mv->haus_nr\n\n$mv->haus_plz $mv->haus_stadt", 12);
        $pdf->ezSetDy(-20);
        $pdf->ezText("<b>Mieter-Nr.: $mv->einheit_kurzname</b>", 12);
        $pdf->ezSetDy(-20);
        $pdf->ezText("Ihrem Mieterhöhungsverlangen für eine neue Bruttokaltmiete von <b>$ber->NEUE_BRUTTO_MIETE_A €</b>\n", 12);
        unset ($tab_arr);
        $tab_arr [0] ['BEZ'] = "Kaltmiete";
        $tab_arr [0] ['BETRAG'] = "$ber->NEUE_MIETE_A €";
        $tab_arr [1] ['BEZ'] = 'Tatsächlicher Nebenkostenanteil';
        $tab_arr [1] ['BETRAG'] = "$ber->TAT_KOST_M €";
        $tab_arr [2] ['BEZ'] = 'Heizkosten Vorauszahlung';
        $tab_arr [2] ['BETRAG'] = "$ber->B_AKT_HK €";

        $cols = array(
            'BEZ' => "BEZ",
            'BETRAG' => "BETRAG"
        );
        $pdf->ezTable($tab_arr, $cols, "", array(
            'showHeadings' => 0,
            'shaded' => 0,
            'showLines' => 0,
            'titleFontSize' => 11,
            'fontSize' => 11,
            'xPos' => 55,
            'xOrientation' => 'right',
            'width' => 450,
            'cols' => array(
                'BEZ' => array(
                    'justification' => 'left',
                    'width' => 350
                ),
                'BETRAG' => array(
                    'justification' => 'right',
                    'width' => 100
                )
            )
        ));
        $pdf->ezText("\nab dem $ber->N_ANSTIEG_DATUM stimme/en ich/wir zu.\n", 12);
        $pdf->ezSetDy(-30);
        $hoehe = $pdf->y;
        $pdf->ezText("_________________________", 11, array(
            'aleft' => '55'
        ));
        $pdf->ezText("Unterschrift", 10, array(
            'aleft' => '100'
        ));
        $pdf->ezSety($hoehe);
        $pdf->ezText("____________________", 11, array(
            'left' => '320'
        ));
        $pdf->ezText("Datum", 10, array(
            'left' => '370'
        ));
        $pdf->ezSetDy(-60);
        $hoehe = $pdf->y;
        $pdf->ezText("_________________________", 11, array(
            'aleft' => '55'
        ));
        $pdf->ezText("Unterschrift", 10, array(
            'aleft' => '100'
        ));
        $pdf->ezSety($hoehe);
        $pdf->ezText("____________________", 11, array(
            'left' => '320'
        ));
        $pdf->ezText("Datum", 10, array(
            'left' => '370'
        ));

        // $pdf->ezNewPage();
        /*
		 * $im = new imagick();
		 * $im->setResolution(600,600);
		 * $im->readImage('Mietspiegeltabelle2009.pdf[0]');
		 * $im->setImageFormat(�png�);
		 * $im->setImageDepth(8);
		 * $im->setImageCompressionQuality(90);
		 * $im->scaleImage(500,0);
		 */
        //

        // $this->widerrufsseite($pdf);

        /* Ausgabe */
        ob_end_clean(); // ausgabepuffer leeren
        $dateiname = $mv->einheit_kurzname . "_MHG_zum_" . $ber->N_ANSTIEG_DATUM . "_vom_" . $datum . ".pdf";
        $pdf_opt ['Content-Disposition'] = $dateiname;
        $pdf->ezStream($pdf_opt);
    }

    function pdf_anschreiben_prozent($ber_array, $datum)
    {
        $ber = ( object )$ber_array;
        $ber->MIETE_AKTUELL_A = nummer_punkt2komma($ber->MIETE_AKTUELL);
        $ber->EINHEIT_QM_A = nummer_punkt2komma($ber->EINHEIT_QM);
        $ber->M2_AKTUELL_A = nummer_punkt2komma($ber->M2_AKTUELL);
        $ber->M_WERT_A = nummer_punkt2komma($ber->M_WERT);
        $ber->NEUE_MIETE_A = nummer_punkt2komma($ber->NEUE_MIETE);
        $ber->MONATLICH_MEHR_A = nummer_punkt2komma($ber->MONATLICH_MEHR);
        $ber->L_ANSTIEG_BETRAG_A = nummer_punkt2komma($ber->L_ANSTIEG_BETRAG);
        $ber->ANSTIEG_3J_A = nummer_punkt2komma($ber->ANSTIEG_3J);
        ob_clean(); // ausgabepuffer leeren
        $pdf = new Cezpdf ('a4', 'portrait');
        $bpdf = new b_pdf ();
        $bpdf->b_header($pdf, 'Partner', session()->get('partner_id'), 'portrait', 'Helvetica.afm', 6);
        $this->footer_zahlungshinweis = $bpdf->zahlungshinweis;

        $pdf->ezStopPageNumbers(); // seitennummerirung beenden
        $p = new partners ();
        $p->get_partner_info(session()->get('partner_id'));
        $pdf->addText(480, 697, 8, "$p->partner_ort, $datum");
        $mv = new mietvertraege ();
        $mv->get_mietvertrag_infos_aktuell($ber->MV_ID);
        $pdf->ezText("$mv->personen_name_string_u\n$mv->haus_strasse $mv->haus_nr\n\n$mv->haus_plz $mv->haus_stadt", 12);
        $pdf->ezSetDy(-60);
        /* Betreff */
        $pdf->ezText("<b>Mieterhöhungsverlangen zum $ber->N_ANSTIEG_DATUM gemäß §§ 558 BGB ff. des Bürgerlichen Gesetzbuches (BGB) Mieter-Nr.: $mv->einheit_kurzname</b>", 11);
        // $pdf->ezText("Einheit: $mv->einheit_kurzname",12);
        $pdf->ezSetDy(-10);
        /* Faltlinie */
        $pdf->setLineStyle(0.2);
        $pdf->line(5, 542, 20, 542);
        /* Anrede */
        $pdf->ezText("$anrede", 11);
        $pdf->ezText("$mv->mv_anrede", 11);
        $brief_text = "wie Ihnen bekannt ist, vertreten wir die rechtlichen Interessen der Eigentümer. Eine auf uns lautende Vollmacht ist beigefügt.";
        $pdf->ezText("$brief_text", 11, array(
            'justification' => 'full'
        ));
        $brief_text = "Namens und in Vollmacht der Eigentümer werden Sie hiermit gebeten, der Erhöhung der Netto-Kaltmiete gemäß § 558 BGB zuzustimmen. Gemäß der mietvertraglichen Vereinbarung zahlen Sie gegenwärtig eine Nettomiete in Höhe von $ber->MIETE_AKTUELL_A €. Die jeweiligen Angaben beziehen sich auf den monatlichen Mietzins.
		";
        $pdf->ezText("$brief_text", 11, array(
            'justification' => 'full'
        ));

        $tab_arr [0] ['BEZ'] = '<b>Ihre derzeitige Netto-Kaltmiete beträgt:</b>';
        $tab_arr [0] ['BETRAG'] = '<b>' . $ber->MIETE_AKTUELL_A . ' €</b>';
        $tab_arr [1] ['BEZ'] = '<b>Erhöhungsbetrag:</b>';
        $tab_arr [1] ['BETRAG'] = '<b>' . $ber->MONATLICH_MEHR_A . ' €</b>';
        $tab_arr [2] ['BEZ'] = "<b>Neue Nettokaltmiete ab $ber->N_ANSTIEG_DATUM:</b>";
        $tab_arr [2] ['BETRAG'] = '<b>' . $ber->NEUE_MIETE_A . ' €</b>';
        /*
		 * $tab_arr[3][BEZ] = 'Nebenkosten Vorauszahlung';
		 * $tab_arr[3][BETRAG] = "+ $ber->B_AKT_NK";
		 * $tab_arr[4][BEZ] = 'Heizkosten Vorauszahlung';
		 * $tab_arr[4][BETRAG] = "+ $ber->B_AKT_HK";
		 * $tab_arr[5][BEZ] = 'Alte Endmiete';
		 * $tab_arr[5][BETRAG] = $ber->B_AKT_ENDMIETE;
		 * $tab_arr[6][BEZ] = '<b>Neue Endmiete</b>';
		 * $tab_arr[6][BETRAG] = "<b>$ber->B_NEUE_ENDMIETE</b>";
		 */
        // $pdf->ezSetDy(-10);
        $cols = array(
            'BEZ' => "BEZ",
            'BETRAG' => "BETRAG"
        );
        $pdf->ezTable($tab_arr, $cols, "", array(
            'showHeadings' => 0,
            'shaded' => 0,
            'showLines' => 0,
            'titleFontSize' => 11,
            'fontSize' => 11,
            'xPos' => 55,
            'xOrientation' => 'right',
            'width' => 400,
            'cols' => array(
                'BEZ' => array(
                    'justification' => 'left',
                    'width' => 350
                ),
                'BETRAG' => array(
                    'justification' => 'right',
                    'width' => 50
                )
            )
        ));
        $pdf->ezSetDy(-10);
        $brief_text = "Gemäß § 558 BGB kann der Vermieter die Zustimmung zur Mieterhöhung von Ihnen verlangen, wenn der Mietzins, zu dem die Erhöhung eintreten soll, seit 15 Monaten unverändert und mindestens 1 Jahr nach der letzten Mieterhöhung verstrichen ist. Weiterhin darf sich der Mietzins innerhalb von 3 Jahren um nicht mehr als 15 % erhöhen.";
        $pdf->ezText("$brief_text", 11, array(
            'justification' => 'full'
        ));
        $pdf->ezSetDy(-10);
        $brief_text = "Die mietvertraglich vereinbarte Fläche Ihrer Wohnung beträgt $ber->EINHEIT_QM_A m². Sie zahlen gegenwärtig eine Netto-Kaltmiete in Höhe von $ber->MIETE_AKTUELL_A €. Hieraus errechnet sich eine Miete netto kalt je qm in Höhe von $ber->M2_AKTUELL_A €.";
        $pdf->ezText("$brief_text", 11, array(
            'justification' => 'full'
        ));

        $brief_text = "\nBei der Berechnung der zulässigen Erhöhung gemäß § 558 BGB ist von der gezahlten Netto-Kaltmiete von vor drei Jahren auszugehen.";
        $pdf->ezText("$brief_text", 11, array(
            'justification' => 'full'
        ));
        // $jahr_minus_3 = date("Y")-3;
        // $monat = date("m");
        // $tag = date("d");

        // $datum_vor_3_jahren = "$jahr_minus_3-$monat-$tag";
        // DATUM_3_JAHRE
        $datum_vor_3_jahren_a = $ber->DATUM_3_JAHRE;
        $datum_vor_3_jahren = date_german2mysql($datum_vor_3_jahren_a);
        $ber->EINZUG_A = date_mysql2german($ber->EINZUG);
        $t1 = strtotime("$datum_vor_3_jahren");
        $t2 = strtotime("$ber->EINZUG");
        if ($t2 > $t1) {
            $brief_text = "\nDie Netto-Kaltmiete (ohne Umlagen und Zuschläge) betrug bei Vertragsbeginn am $ber->EINZUG_A $ber->L_ANSTIEG_BETRAG_A €. ";
        } else {
            $brief_text = "\nDie Netto-Kaltmiete (ohne Umlagen und Zuschläge) betrug  am $datum_vor_3_jahren_a $ber->L_ANSTIEG_BETRAG_A €. ";
        }
        $pdf->ezText("$brief_text", 11, array(
            'justification' => 'full'
        ));

        $brief_text = "\nAuf diesen Netto-Kaltmietzins erfolgten innerhalb der letzten drei Jahre Erhöhungen von insgesamt $ber->ANSTIEG_3J_A %.";
        $pdf->ezText("$brief_text", 11, array(
            'justification' => 'full'
        ));

        /*
		 * $erhoehungen_arr = $this->get_erhoehungen_arr($datum_vor_3_jahren, 'MIETVERTRAG', $ber->MV_ID);
		 * if(is_array($erhoehungen_arr)){
		 * $anz_e = count($erhoehungen_arr);
		 * #echo "<tr><th colspan=\"2\">Mieterhöhungen seit 3 Jahren</th></tr>";
		 * $pdf->ezText("\nMieterhöhungen seit 3 Jahren",11, array('justification'=>'full'));
		 * for ($j = 0; $j < $anz_e;$j++){
		 * $k_kat = $erhoehungen_arr[$j]['KOSTENKATEGORIE'];
		 * $anf_kat = date_mysql2german($erhoehungen_arr[$j]['ANFANG']);
		 * $ende_kat = date_mysql2german($erhoehungen_arr[$j]['ENDE']);
		 * if($ende_kat == '00.00.0000'){
		 * $ende_kat = 'unbefristet';
		 * }
		 * $betrag_kat = nummer_punkt2komma($erhoehungen_arr[$j]['BETRAG']);
		 * # echo "<tr><td><b>Von: $anf_kat Bis: $ende_kat - $k_kat</b></td><td>$betrag_kat €</td></tr>";
		 * $pdf->ezText("Vom $anf_kat bis $ende_kat - $k_kat - $betrag_kat €",11, array('justification'=>'full'));
		 * }
		 * }
		 */
        /* Zweite Seite */
        $pdf->ezNewPage();

        $brief_text = "\nAuf Grundlage des Berliner Mietspiegel für $ber->MS_JAHR (in Kopie beigefügt) und unter der Berücksichtigung von Sondermerkmalen ist eine Erhöhung auf $ber->M_WERT_A € / m² für Ihre Wohnung möglich.";
        $pdf->ezText("$brief_text", 11, array(
            'justification' => 'full'
        ));

        $brief_text = "\nBei der Ermittlung des ortsüblichen Vergleichmietzinses aufgrund des qualifizierten Mietspiegels gemäß § 558d BGB sind hierbei folgende wohnungsbezogenen Merkmale zu berücksichtigen.\n";
        $pdf->ezText("$brief_text", 11, array(
            'justification' => 'full'
        ));
        $brief_text = "Merkmalgruppe 1:  Bad/WC";
        $pdf->ezText("$brief_text", 11, array(
            'justification' => 'full'
        ));
        $brief_text = "Merkmalgruppe 2:  Küche";
        $pdf->ezText("$brief_text", 11, array(
            'justification' => 'full'
        ));
        $brief_text = "Merkmalgruppe 3:  Wohnung";
        $pdf->ezText("$brief_text", 11, array(
            'justification' => 'full'
        ));
        $brief_text = "Merkmalgruppe 4:  Gebäude";
        $pdf->ezText("$brief_text", 11, array(
            'justification' => 'full'
        ));
        $brief_text = "Merkmalgruppe 5:  Wohnumfeld";
        $pdf->ezText("$brief_text", 11, array(
            'justification' => 'full'
        ));

        $brief_text = "\nAufgrund dieser Merkmalsgruppen ergibt sich ein Zu-/Abschlag für Ihre Wohnung in Höhe von 0,00 %.";
        $pdf->ezText("$brief_text", 11, array(
            'justification' => 'full'
        ));
        $brief_text = "Die von Ihnen genutzte Wohnung ist dem Mietspiegelfeld <b>$ber->MS_FELD </b>zuzuordnen.";
        $pdf->ezText("$brief_text", 11, array(
            'justification' => 'full'
        ));
        $ber->M_WERT_A = nummer_punkt2komma($ber->M_WERT);

        /* Sondermerkmale finden */
        $sondermerkmale_arr = $this->get_sondermerkmale_arr($ber->AUSSTATTUNGSKLASSE, $ber->MS_JAHR);
        $anz_sm = count($sondermerkmale_arr);
        if ($anz_sm > 0) {
            $d = new detail ();
            $abzug_zaehler = 0;
            $this->abzug_wert = 0;

            for ($s = 0; $s < $anz_sm; $s++) {
                $merkmal = $sondermerkmale_arr [$s] ['MERKMAL'];
                $wert = $sondermerkmale_arr [$s] ['WERT'];
                $a_klasse = $sondermerkmale_arr [$s] ['A_KLASSE'];
                if ($a_klasse == NULL or $ber->AUSSTATTUNGSKLASSE == $a_klasse) {
                    /* Wenn z.B. Erdgeschoss, dann Abzug */
                    $sonder_abzug = $d->finde_detail_inhalt('EINHEIT', $ber->EINHEIT_ID, $merkmal);
                    if ($sonder_abzug) {
                        $abzuege_arr [$abzug_zaehler] ['MERKMAL'] = $merkmal;
                        $abzuege_arr [$abzug_zaehler] ['MERKMAL_GRUND'] = $sonder_abzug;
                        $abzuege_arr [$abzug_zaehler] ['MERKMAL_WERT'] = $wert;
                        $this->abzug_wert = $this->abzug_wert + $wert;
                        $abzug_zaehler++;
                    }
                }
            } // end for
        } // end wenn Sondermerkmale vorhanden

        if (is_array($abzuege_arr)) {
            $this->abzug_wert_a = nummer_punkt2komma($this->abzug_wert);
            $brief_text = "\n<b>Bei Ihrer Wohnung wurden bei der Berechnung folgende wertmindernde Faktoren berücksichtigt:</b>\n";
            $pdf->ezText("$brief_text", 11, array(
                'justification' => 'full'
            ));
            $anz = count($abzuege_arr);
            for ($i = 0; $i < $anz; $i++) {
                $merkm = $abzuege_arr [$i] ['MERKMAL'];
                $merkmw = $abzuege_arr [$i] ['MERKMAL_WERT'];
                $merkmw_a = nummer_punkt2komma($merkmw);
                // echo "<tr><td>$merkm</td><td>$merkmw</td></tr>";
                $pdf->ezText("$merkm          $merkmw_a €/m²", 11);
            }
            $ber->GESAMT_ABZUG_A = nummer_punkt2komma($ber->GESAMT_ABZUG);
            $pdf->ezText("<b>Gesamtminderung              $ber->GESAMT_ABZUG_A €/monatlich</b> (Ihre Fläche: $ber->EINHEIT_QM_A m² X Abzug pro m²: $this->abzug_wert_a €)", 11);
            $neuer_mw = nummer_komma2punkt($ber->M_WERT_A) + $this->abzug_wert;
            $neuer_mw_a = nummer_punkt2komma($neuer_mw);

            $pdf->ezText("Berechnung des Mietspiegelmittelwertes für Ihre Wohnung: $ber->M_WERT_A € $this->abzug_wert_a € = <b>$neuer_mw_a € pro m²</b>", 11, array(
                'justification' => 'full'
            ));
        }

        $ber->ANSTIEG_UM_PROZENT_A = nummer_punkt2komma($ber->ANSTIEG_UM_PROZENT);
        $ber->MONATLICH_MEHR_A = nummer_punkt2komma($ber->MONATLICH_MEHR);
        $brief_text = "\nGemäß § 558 Absatz 3 BGB wird hiermit die Miete um $ber->ANSTIEG_UM_PROZENT_A %, ausgehend vom Netto-Kaltmietzins, also um insgesamt $ber->MONATLICH_MEHR_A €, erhöht.";
        $pdf->ezText("$brief_text", 11, array(
            'justification' => 'full'
        ));
        $ber->M2_PREIS_NEU_A = nummer_punkt2komma($ber->M2_PREIS_NEU);
        $brief_text = "\nNach der Erhöhung beträgt die Nettokaltmiete $ber->M2_PREIS_NEU_A €/m². Unter Berücksichtigung der wohnungsbezogenen Merkmale ist der geforderte Mietzins ortsüblich.";
        $pdf->ezText("$brief_text", 11, array(
            'justification' => 'full'
        ));
        $brief_text = "\n<b>Ihre neue Gesamtmiete beträgt ab dem $ber->N_ANSTIEG_DATUM insgesamt $ber->B_NEUE_ENDMIETE €.</b>";
        $pdf->ezText("$brief_text", 11, array(
            'justification' => 'full'
        ));

        $brief_text = "\n<b>Diese setzt sich wie folgt zusammen (EURO):</b>";
        $pdf->ezText("$brief_text", 11, array(
            'justification' => 'full'
        ));
        /*
		 * $brief_text = "Kaltmiete: $ber->NEUE_MIETE_A";
		 * $pdf->ezText("$brief_text",11, array('justification'=>'full'));
		 * $brief_text = "Nebenkosten Vorauszahlung: $ber->B_AKT_NK";
		 * $pdf->ezText("$brief_text",11, array('justification'=>'full'));
		 * $brief_text = "Heizkosten Vorauszahlung: $ber->B_AKT_HK";
		 * $pdf->ezText("$brief_text",11, array('justification'=>'full'));
		 */

        $tab_arr [0] ['BEZ'] = '<b>Ihre derzeitige Netto-Kaltmiete beträgt';
        $tab_arr [0] ['BETRAG'] = "$ber->MIETE_AKTUELL_A €";
        $tab_arr [0] ['M2'] = "$ber->M2_AKTUELL_A €";
        $tab_arr [1] ['BEZ'] = 'Erhöhungsbetrag:';
        $tab_arr [1] ['BETRAG'] = "$ber->MONATLICH_MEHR_A €";
        $erh_m2 = nummer_punkt2komma(nummer_komma2punkt($ber->M2_PREIS_NEU_A) - nummer_komma2punkt($ber->M2_AKTUELL));
        $tab_arr [1] ['M2'] = "$erh_m2 €";
        $tab_arr [2] ['BEZ'] = "Neue Nettokaltmiete ab dem $ber->N_ANSTIEG_DATUM";
        $tab_arr [2] ['BETRAG'] = "$ber->NEUE_MIETE_A €";
        $tab_arr [2] ['M2'] = "$ber->M2_PREIS_NEU_A €";
        $tab_arr [3] ['BEZ'] = 'Nebenkosten Vorauszahlung';
        $tab_arr [3] ['BETRAG'] = "$ber->B_AKT_NK €";
        $tab_arr [4] ['BEZ'] = 'Heizkosten Vorauszahlung';
        $tab_arr [4] ['BETRAG'] = "$ber->B_AKT_HK €";
        $tab_arr [5] ['BEZ'] = 'Bisherige Endmiete';
        $tab_arr [5] ['BETRAG'] = "$ber->B_AKT_ENDMIETE €";
        $tab_arr [6] ['BEZ'] = "Neue Endmiete ab $ber->N_ANSTIEG_DATUM";
        $tab_arr [6] ['BETRAG'] = "$ber->B_NEUE_ENDMIETE €</b>";

        $pdf->ezSetDy(-3);
        $cols = array(
            'BEZ' => "",
            'BETRAG' => "Euro/monatlich",
            'M2' => "Euro/m²"
        );
        $pdf->ezTable($tab_arr, $cols, "", array(
            'showHeadings' => 1,
            'shaded' => 0,
            'showLines' => 0,
            'titleFontSize' => 11,
            'fontSize' => 11,
            'xPos' => 55,
            'xOrientation' => 'right',
            'width' => 500,
            'cols' => array(
                'BEZ' => array(
                    'justification' => 'left',
                    'width' => 300
                ),
                'BETRAG' => array(
                    'justification' => 'right',
                    'width' => 100
                ),
                'M2' => array(
                    'justification' => 'right',
                    'width' => 100
                )
            )
        ));
        $o = new objekt ();
        $mysql_date_anstieg = date_german2mysql($ber->N_ANSTIEG_DATUM);
        $datum_zustimmung_frist = date_mysql2german($mysql_date_anstieg);
        $brief_text = "\nGemäß § 558b BGB sind wir berechtigt, gegen Sie Klage auf Zustimmung zur Mieterhöhung zu erheben, falls Sie nicht bis zum Ablauf des zweiten Kalendermonats nach Zugang dieses Erhöhungsverlangens die Zustimmung erteilen. Die Klage muss hierbei innerhalb einer Frist von weiteren drei Monaten erhoben werden. Wir sehen daher Ihrer Zustimmung zur Mieterhöhung gemäß diesem Schreiben bis zum $datum_zustimmung_frist entgegen.\n";
        $pdf->ezText("$brief_text", 11, array(
            'justification' => 'full'
        ));

        /* Dritte Seite */
        $pdf->ezNewPage();
        $brief_text = "Sie schulden den erhöhten Mietzins von Beginn des dritten Monats ab, der auf den Zugang des Erhöhungsverlangens folgt, falls die Zustimmung erteilt wird oder Sie vom Gericht zur Zustimmung verurteilt werden.\n";
        $pdf->ezText("$brief_text", 11, array(
            'justification' => 'full'
        ));
        $brief_text = "Gemäß § 561 BGB steht Ihnen ein Sonderkündigungsrecht für den Ablauf des zweiten Monats nach Zugang der Erklärung zu.\n";
        $pdf->ezText("$brief_text", 11, array(
            'justification' => 'full'
        ));
        $brief_text = "Dieses Schreiben wurde maschinell erstellt und ist ohne Unterschrift gültig.\n";
        $pdf->ezText("$brief_text", 11, array(
            'justification' => 'full'
        ));

        $brief_text = "Wir bitten Sie, uns bis spätestens $datum_zustimmung_frist Ihre Zustimmung zu dieser Mieterhöhung schriftlich zu bestätigen und uns die letzte Seite des rechtsverbindlich unterschriebenen Exemplars der Erklärung zurückzusenden.\n";
        $pdf->ezText("$brief_text", 11, array(
            'justification' => 'full'
        ));

        $brief_text = "$bpdf->zahlungshinweis";
        $pdf->ezText("$brief_text", 11, array(
            'justification' => 'full'
        ));

        $brief_text = "\n\nAnlagen wie im Text angegeben";
        $pdf->ezText("$brief_text", 8, array(
            'justification' => 'full'
        ));

        /* Vierte Seite ZUSTIMMUNG */
        $pdf->ezNewPage();
        $pdf->ezText("$p->partner_name\n$p->partner_strasse $p->partner_hausnr\n\n$p->partner_plz $p->partner_ort", 12);
        $pdf->ezSetDy(-60);
        // y=ezText(text,[size],[array options])
        $pdf->ezText("<b>ERKLÄRUNG</b>", 14, array(
            'justification' => 'center'
        ));
        $pdf->ezSetDy(-20);
        $pdf->ezText("$mv->personen_name_string_u\n$mv->haus_strasse $mv->haus_nr\n\n$mv->haus_plz $mv->haus_stadt", 12);
        $pdf->ezSetDy(-20);
        $pdf->ezText("<b>Mieter-Nr.: $mv->einheit_kurzname</b>", 12);
        $pdf->ezSetDy(-20);
        $pdf->ezText("Ihrem Mieterhöhungsverlangen für eine neue Gesamtmiete von <b>$ber->B_NEUE_ENDMIETE €\n", 12);
        unset ($tab_arr);
        $tab_arr [0] ['BEZ'] = "Kaltmiete";
        $tab_arr [0] ['BETRAG'] = "$ber->NEUE_MIETE_A €";
        $tab_arr [1] ['BEZ'] = 'Nebenkosten Vorauszahlung';
        $tab_arr [1] ['BETRAG'] = "$ber->B_AKT_NK €";
        $tab_arr [2] ['BEZ'] = 'Heizkosten Vorauszahlung';
        $tab_arr [2] ['BETRAG'] = "$ber->B_AKT_HK €</b>";
        $cols = array(
            'BEZ' => "BEZ",
            'BETRAG' => "BETRAG"
        );
        $pdf->ezTable($tab_arr, $cols, "", array(
            'showHeadings' => 0,
            'shaded' => 0,
            'showLines' => 0,
            'titleFontSize' => 11,
            'fontSize' => 11,
            'xPos' => 55,
            'xOrientation' => 'right',
            'width' => 450,
            'cols' => array(
                'BEZ' => array(
                    'justification' => 'left',
                    'width' => 350
                ),
                'BETRAG' => array(
                    'justification' => 'right',
                    'width' => 100
                )
            )
        ));
        $pdf->ezText("\nab dem $ber->N_ANSTIEG_DATUM stimme/en ich/wir zu.\n", 12);
        $pdf->ezSetDy(-30);
        $hoehe = $pdf->y;
        $pdf->ezText("_________________________", 11, array(
            'aleft' => '55'
        ));
        $pdf->ezText("Unterschrift", 10, array(
            'aleft' => '100'
        ));
        $pdf->ezSetY($hoehe);
        $pdf->ezText("____________________", 11, array(
            'left' => '320'
        ));
        $pdf->ezText("Datum", 10, array(
            'left' => '370'
        ));
        $pdf->ezSetDy(-60);
        $hoehe = $pdf->y;
        $pdf->ezText("_________________________", 11, array(
            'aleft' => '55'
        ));
        $pdf->ezText("Unterschrift", 10, array(
            'aleft' => '100'
        ));
        $pdf->ezSetY($hoehe);
        $pdf->ezText("____________________", 11, array(
            'left' => '320'
        ));
        $pdf->ezText("Datum", 10, array(
            'left' => '370'
        ));
        /* Fünfte Seite ZUSTIMMUNG - Die der Mieter uterschreibt und zurücksendet */
        $pdf->ezNewPage();
        $pdf->ezText("$p->partner_name\n$p->partner_strasse $p->partner_hausnr\n\n$p->partner_plz $p->partner_ort", 12);
        $pdf->ezSetDy(-60);
        // y=ezText(text,[size],[array options])
        $pdf->ezText("<b>ERKLÄRUNG</b>", 14, array(
            'justification' => 'center'
        ));
        $pdf->ezSetDy(-20);
        $pdf->ezText("$mv->personen_name_string_u\n$mv->haus_strasse $mv->haus_nr\n\n$mv->haus_plz $mv->haus_stadt", 12);
        $pdf->ezSetDy(-20);
        $pdf->ezText("<b>Mieter-Nr.: $mv->einheit_kurzname</b>", 12);
        $pdf->ezSetDy(-20);
        $pdf->ezText("Ihrem Mieterhöhungsverlangen für eine neue Gesamtmiete von <b>$ber->B_NEUE_ENDMIETE €\n", 12);
        unset ($tab_arr);
        $tab_arr [0] ['BEZ'] = "Kaltmiete";
        $tab_arr [0] ['BETRAG'] = "$ber->NEUE_MIETE_A €";
        $tab_arr [1] ['BEZ'] = 'Nebenkosten Vorauszahlung';
        $tab_arr [1] ['BETRAG'] = "$ber->B_AKT_NK €";
        $tab_arr [2] ['BEZ'] = 'Heizkosten Vorauszahlung';
        $tab_arr [2] ['BETRAG'] = "$ber->B_AKT_HK €</b>";
        $cols = array(
            'BEZ' => "BEZ",
            'BETRAG' => "BETRAG"
        );
        $pdf->ezTable($tab_arr, $cols, "", array(
            'showHeadings' => 0,
            'shaded' => 0,
            'showLines' => 0,
            'titleFontSize' => 11,
            'fontSize' => 11,
            'xPos' => 55,
            'xOrientation' => 'right',
            'width' => 450,
            'cols' => array(
                'BEZ' => array(
                    'justification' => 'left',
                    'width' => 350
                ),
                'BETRAG' => array(
                    'justification' => 'right',
                    'width' => 100
                )
            )
        ));
        $pdf->ezText("\nab dem $ber->N_ANSTIEG_DATUM stimme/en ich/wir zu.\n", 12);
        $pdf->ezSetDy(-30);
        $hoehe = $pdf->y;
        $pdf->ezText("_________________________", 11, array(
            'aleft' => '55'
        ));
        $pdf->ezText("Unterschrift", 10, array(
            'aleft' => '100'
        ));
        $pdf->ezSetY($hoehe);
        $pdf->ezText("____________________", 11, array(
            'left' => '320'
        ));
        $pdf->ezText("Datum", 10, array(
            'left' => '370'
        ));
        $pdf->ezSetDy(-60);
        $hoehe = $pdf->y;
        $pdf->ezText("_________________________", 11, array(
            'aleft' => '55'
        ));
        $pdf->ezText("Unterschrift", 10, array(
            'aleft' => '100'
        ));
        $pdf->ezSetY($hoehe);
        $pdf->ezText("____________________", 11, array(
            'left' => '320'
        ));
        $pdf->ezText("Datum", 10, array(
            'left' => '370'
        ));

        /* Ausgabe */
        ob_end_clean(); // ausgabepuffer leeren
        $pdf->ezStream();
    }
}//end class