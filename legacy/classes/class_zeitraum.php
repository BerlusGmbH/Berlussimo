<?php

// Klasse zur Erstellung eines Arrays mit Monaten und Jahren
// z.B. seit Einzug bis heute
class zeitraum
{
    function check_number($checkValue)
    {
        if (abs($checkValue) != $checkValue) {
            // in this case, the value is negative; return 1
            return 1;
        } else {
            // number is positive; return 0
            return 0;
        }
    }

    function zeitraum_generieren($monat_von, $jahr_von, $monat_bis, $jahr_bis)
    {
        $laenge_monat_von = strlen($monat_von);
        $laenge_monat_bis = strlen($monat_bis);
        if ($monat_von < 10 && $laenge_monat_von == 2) {
            $monat_von = substr($monat_von, 1, 1);
        }
        if ($monat_bis < 10 && $laenge_monat_von == 2) {
            $monat_bis = substr($monat_bis, 1, 1);
        }

        // Aktuelle Datumangaben
        $letztes_datum_monat = date("Y-m-t"); // letzter Tag im aktuellen Monat, dafür steht (t) z.B. 28 bzw 29 / 30. oder 31.
        $aktuelles_datum = explode("-", $letztes_datum_monat);
        $aktuelles_jahr = $aktuelles_datum [0];
        $aktueller_monat = $aktuelles_datum [1];
        $aktueller_tag = $aktuelles_datum [2];

        $diff_in_jahren = $jahr_bis - $jahr_von;

        // 1. Regel, falls Einzugs- und aktuelles Jahr identisch z.b. 1.1.2008 und heute 20.5.2008
        if ($diff_in_jahren == 0) {
            if ($monat_von > $monat_bis) {
                return false;
            } else {
                for ($monat = $monat_von; $monat <= $monat_bis; $monat++) {
                    if ($monat < 10) {
                        $datum_jahr_arr = array(
                            "monat" => "0$monat",
                            "jahr" => "$jahr_bis"
                        );
                    } else {
                        $datum_jahr_arr = array(
                            "monat" => "$monat",
                            "jahr" => "$jahr_bis"
                        );
                    }
                    $monate_arr [] = $datum_jahr_arr;
                } // end for
            } // end else
        } // end if diff=0

        // 2. Regel, falls Einzugs- und aktuelles Jahr identisch z.b. 1.1.2008 und heute 20.5.2008
        if ($diff_in_jahren > 0) {
            // Alle Jahre durchlaufen und hochzählen, Beginn bei Einzugsjahr bis aktuelles Jahr
            for ($jahr = $jahr_von; $jahr <= $jahr_bis; $jahr++) {

                // Wenn Jahr = Einzugsjahr d.h. erstes bzw Einzugsjahr
                if ($jahr == $jahr_von) {
                    for ($monat = $monat_von; $monat <= 12; $monat++) {
                        if ($monat < 10) {
                            $datum_jahr_arr = array(
                                "monat" => "0$monat",
                                "jahr" => "$jahr"
                            );
                        } else {
                            $datum_jahr_arr = array(
                                "monat" => "$monat",
                                "jahr" => "$jahr"
                            );
                        }
                        $monate_arr [] = $datum_jahr_arr;
                    } // end for $monat=$monat_einzug;$monat<=12;$monat++
                } // end if $jahr==$jahr_einzug

                // Wenn Jahr aktuelles Jahr z.b 2008 d.h letztes Jahr in der Schleife
                if ($jahr == $jahr_bis) {
                    for ($monat = 1; $monat <= $monat_bis; $monat++) {
                        if ($monat < 10) {
                            $datum_jahr_arr = array(
                                "monat" => "0$monat",
                                "jahr" => "$jahr"
                            );
                        } else {
                            $datum_jahr_arr = array(
                                "monat" => "$monat",
                                "jahr" => "$jahr"
                            );
                        }
                        $monate_arr [] = $datum_jahr_arr;
                    } // end for
                } // end if

                if ($jahr != $jahr_von && $jahr != $jahr_bis) {
                    for ($monat = 1; $monat <= 12; $monat++) {
                        if ($monat < 10) {
                            $datum_jahr_arr = array(
                                "monat" => "0$monat",
                                "jahr" => "$jahr"
                            );
                        } else {
                            $datum_jahr_arr = array(
                                "monat" => "$monat",
                                "jahr" => "$jahr"
                            );
                        }
                        $monate_arr [] = $datum_jahr_arr;
                    } // end for
                } // end if
            } // end for
        } // end if diff=0
        /*
		 * echo "<pre>";
		 * print_r($monate_arr);
		 * echo "</pre>";
		 */
        return $monate_arr;
    } // ende function "zeitraum_arr_seit_einzug""

    function zeitraum_arr_seit_einzug($mietvertrag_id)
    {
        // Mietvertragsdaten ermitteln
        $mv_info = new mietkonto ();
        $mv_info->mietvertrag_grunddaten_holen($mietvertrag_id);
        $mietvertrag_von = $mv_info->mietvertrag_von;
        $mietvertrag_bis = $mv_info->mietvertrag_bis;
        $datum_einzug = explode("-", "$mietvertrag_von");
        $tag_einzug = $datum_einzug [2];
        $monat_einzug = $datum_einzug [1];
        if ($monat_einzug < 10) { // bei 01 02 03 die Null abschneiden
            $monat_einzug = substr($monat_einzug, -1);
        }
        $jahr_einzug = $datum_einzug [0];

        // Aktuelle Datumangaben
        $letztes_datum_monat = date("Y-m-t"); // letzter Tag im aktuellen Monat, dafür steht (t) z.B. 28 bzw 29 / 30. oder 31.
        $aktuelles_datum = explode("-", $letztes_datum_monat);
        $aktuelles_jahr = $aktuelles_datum [0];
        $aktueller_monat = $aktuelles_datum [1];
        $aktueller_tag = $aktuelles_datum [2];
        $diff_in_jahren = $aktuelles_jahr - $jahr_einzug;

        // 1. Regel, falls Einzugs- und aktuelles Jahr identisch z.b. 1.1.2008 und heute 20.5.2008
        if ($diff_in_jahren == "0") {
            for ($monat = $monat_einzug; $monat <= $aktueller_monat; $monat++) {
                if ($monat < 10) {
                    $datum_jahr_arr = array(
                        "monat" => "0$monat",
                        "jahr" => "$aktuelles_jahr"
                    );
                } else {
                    $datum_jahr_arr = array(
                        "monat" => "$monat",
                        "jahr" => "$aktuelles_jahr"
                    );
                }
                $monate_arr [] = $datum_jahr_arr;
            } // end for
        } // end if diff=0

        // 2. Regel, falls Einzugs- und aktuelles Jahr identisch z.b. 1.1.2008 und heute 20.5.2008
        if ($diff_in_jahren > "0") {
            // Alle Jahre durchlaufen und hochzählen, Beginn bei Einzugsjahr bis aktuelles Jahr
            for ($jahr = $jahr_einzug; $jahr <= $aktuelles_jahr; $jahr++) {

                // Wenn Jahr = Einzugsjahr d.h. erstes bzw Einzugsjahr
                if ($jahr == $jahr_einzug) {
                    for ($monat = $monat_einzug; $monat <= 12; $monat++) {
                        if ($monat < 10) {
                            $datum_jahr_arr = array(
                                "monat" => "0$monat",
                                "jahr" => "$jahr"
                            );
                        } else {
                            $datum_jahr_arr = array(
                                "monat" => "$monat",
                                "jahr" => "$jahr"
                            );
                        }
                        $monate_arr [] = $datum_jahr_arr;
                    } // end for $monat=$monat_einzug;$monat<=12;$monat++
                } // end if $jahr==$jahr_einzug

                // Wenn Jahr aktuelles Jahr z.b 2008 d.h letztes Jahr in der Schleife
                if ($jahr == $aktuelles_jahr) {
                    for ($monat = 1; $monat <= $aktueller_monat; $monat++) {
                        if ($monat < 10) {
                            $datum_jahr_arr = array(
                                "monat" => "0$monat",
                                "jahr" => "$jahr"
                            );
                        } else {
                            $datum_jahr_arr = array(
                                "monat" => "$monat",
                                "jahr" => "$jahr"
                            );
                        }
                        $monate_arr [] = $datum_jahr_arr;
                    } // end for
                } // end if

                if ($jahr != $jahr_einzug && $jahr != $aktuelles_jahr) {
                    for ($monat = 1; $monat <= 12; $monat++) {
                        if ($monat < 10) {
                            $datum_jahr_arr = array(
                                "monat" => "0$monat",
                                "jahr" => "$jahr"
                            );
                        } else {
                            $datum_jahr_arr = array(
                                "monat" => "$monat",
                                "jahr" => "$jahr"
                            );
                        }
                        $monate_arr [] = $datum_jahr_arr;
                    } // end for
                } // end if
            } // end for
        } // end if diff=0
        /*
		 * echo "<pre>";
		 * print_r($monate_arr);
		 * echo "</pre>";
		 */
        return $monate_arr;
    } // ende function "zeitraum_arr_seit_einzug""

    function zeitraum_arr_seit_uebernahme($mietvertrag_id)
    {
        // Mietvertragsdaten ermitteln
        $mv_info = new mietkonto ();
        $mv_info->mietvertrag_grunddaten_holen($mietvertrag_id);
        $mietvertrag_von = $mv_info->mietvertrag_von;
        $mietvertrag_bis = $mv_info->mietvertrag_bis;
        $datum_saldo_vorwervaltung = $this->datum_saldo_vorverwaltung($mietvertrag_id);
        if (!isset ($datum_saldo_vorwervaltung)) {
            $datum_einzug = explode("-", "$mietvertrag_von");
        } else {
            $datum_einzug = explode("-", "$datum_saldo_vorwervaltung");
        }
        $tag_einzug = $datum_einzug [2];
        $monat_einzug = $datum_einzug [1];
        if ($monat_einzug < 10) { // bei 01 02 03 die Null abschneiden
            $monat_einzug = substr($monat_einzug, -1);
        }
        $jahr_einzug = $datum_einzug [0];

        // Aktuelle Datumangaben
        $letztes_datum_monat = date("Y-m-t"); // letzter Tag im aktuellen Monat, dafür steht (t) z.B. 28 bzw 29 / 30. oder 31.
        $aktuelles_datum = explode("-", $letztes_datum_monat);
        $aktuelles_jahr = $aktuelles_datum [0];
        $aktueller_monat = $aktuelles_datum [1];
        $aktueller_tag = $aktuelles_datum [2];
        $diff_in_jahren = $aktuelles_jahr - $jahr_einzug;

        // 1. Regel, falls Einzugs- und aktuelles Jahr identisch z.b. 1.1.2008 und heute 20.5.2008
        if ($diff_in_jahren == "0") {
            for ($monat = $monat_einzug; $monat <= $aktueller_monat; $monat++) {
                if ($monat < 10) {
                    $datum_jahr_arr = array(
                        "monat" => "0$monat",
                        "jahr" => "$aktuelles_jahr"
                    );
                } else {
                    $datum_jahr_arr = array(
                        "monat" => "$monat",
                        "jahr" => "$aktuelles_jahr"
                    );
                }
                $monate_arr [] = $datum_jahr_arr;
            } // end for
        } // end if diff=0

        // 2. Regel, falls Einzugs- und aktuelles Jahr identisch z.b. 1.1.2008 und heute 20.5.2008
        if ($diff_in_jahren > 0) {
            // Alle Jahre durchlaufen und hochzählen, Beginn bei Einzugsjahr bis aktuelles Jahr
            for ($jahr == $jahr_einzug; $jahr <= $aktuelles_jahr; $jahr++) {

                // Wenn Jahr = Einzugsjahr d.h. erstes bzw Einzugsjahr
                if ($jahr == $jahr_einzug) {
                    for ($monat == $monat_einzug; $monat <= 12; $monat++) {
                        if ($monat < 10) {
                            $datum_jahr_arr = array(
                                "monat" => "0$monat",
                                "jahr" => "$jahr"
                            );
                        } else {
                            $datum_jahr_arr = array(
                                "monat" => "$monat",
                                "jahr" => "$jahr"
                            );
                        }
                        $monate_arr [] = $datum_jahr_arr;
                    } // end for $monat=$monat_einzug;$monat<=12;$monat++
                } // end if $jahr==$jahr_einzug

                // Wenn Jahr aktuelles Jahr z.b 2008 d.h letztes Jahr in der Schleife
                if ($jahr == $aktuelles_jahr) {
                    for ($monat = 1; $monat <= $aktueller_monat; $monat++) {
                        if ($monat < 10) {
                            $datum_jahr_arr = array(
                                "monat" => "0$monat",
                                "jahr" => "$jahr"
                            );
                        } else {
                            $datum_jahr_arr = array(
                                "monat" => "$monat",
                                "jahr" => "$jahr"
                            );
                        }
                        $monate_arr [] = $datum_jahr_arr;
                    } // end for
                } // end if

                if ($jahr != $jahr_einzug && $jahr != $aktuelles_jahr) {
                    for ($monat = 1; $monat <= 12; $monat++) {
                        if ($monat < 10) {
                            $datum_jahr_arr = array(
                                "monat" => "0$monat",
                                "jahr" => "$jahr"
                            );
                        } else {
                            $datum_jahr_arr = array(
                                "monat" => "$monat",
                                "jahr" => "$jahr"
                            );
                        }
                        $monate_arr [] = $datum_jahr_arr;
                    } // end for
                } // end if
            } // end for
        } // end if diff=0
        /*
		 * echo "<pre>";
		 * print_r($monate_arr);
		 * echo "</pre>";
		 */
        return $monate_arr;
    }

    function datum_saldo_vorverwaltung($mietvertrag_id)
    {
        $result = DB::select("SELECT DATUM FROM MIETE_ZAHLBETRAG WHERE BEMERKUNG = 'Saldo Vortrag Vorverwaltung' && MIETVERTRAG_ID ='$mietvertrag_id' && AKTUELL = '1'");
        return $result[0]['DATUM'];
    } // ende function "zeitraum_arr_seit_einzug""
} // end class zeitraum