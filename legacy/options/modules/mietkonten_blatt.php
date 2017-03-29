<?php

/*
 * Mietdefinition beginn (in MIETENTWICKLUNG)
 * Datum auf 1.3.2008 oder 1.1.2007 setzen
 */
/*
 * $me = new mietentwicklung;
 * $me->set_datum_block_e();
 * $me->set_datum_andere();
 * $me->update_bk_hk_datum();
 */

if (request()->has('mietvertrag_id')) {
    $mietvertrag_id = request()->input('mietvertrag_id');
}

// ##########SWITCH ANFANG##################################
if (request()->has('anzeigen')) {
    $anzeigen = request()->input('anzeigen');
}
if (isset ($anzeigen)) {
    switch ($anzeigen) {

        // ##########################################################
        case "buchung_aktuell" :
            iframe_start();
            echo "BUCHUNGS_FORM EINZELN";
            $buchung = new mietkonto ();
            $buchung->buchung_form($mietvertrag_id);
            // $buchung->buchung_zeitraum($mietvertrag_id, "2008-01-01", "2008-12-31");
            iframe_end();
            break;
        // ##########################################################
        case "mietkonto_drucken" :
            $a = new miete ();
            $form = new mietkonto ();
            $a->mietkonto_berechnung(request()->input('mietvertrag_id'));
            $form->erstelle_formular("Mietkonto $a->erg €", NULL);
            $a->mietkonten_blatt_anzeigen(request()->input('mietvertrag_id'));
            $form->ende_formular();
            break;
        // ########################
        /* Mietkonto als PDF */
        case "mk_pdf" :
            $mz = new miete ();
            $mv_id = request()->input('mietvertrag_id');
            if (!empty ($mv_id)) {
                $mz->mietkonten_blatt_pdf($mv_id);
            } else {
                echo "Mietvertrag auswählen";
            }
            break;

        case "mietkonto_uebersicht_detailiert" :
            $a = new miete ();
            $form = new mietkonto ();
            $a->mietkonto_berechnung(request()->input('mietvertrag_id'));
            $form->erstelle_formular("Mietkonto $a->erg €", NULL);
            echo "<a href=\"?daten=drucken&option=mietkonto_drucken_css&mietvertrag_id=" . request()->input('mietvertrag_id') . "\">Druckansicht</a>&nbsp;&nbsp;";
            echo "<a href='" . route('web::mietkontenblatt::legacy', ['anzeigen' => 'mk_pdf', 'mietvertrag_id' => request()->input('mietvertrag_id')]) . "'>PDF</a>&nbsp;&nbsp;";
            iframe_start();
            $a->mietkonten_blatt_anzeigen(request()->input('mietvertrag_id'));

            iframe_end();
            $form->ende_formular();
            break;

        case "mietkonto_ab" :
            if (request()->has('mietvertrag_id')) {
                $f = new formular ();
                $f->erstelle_formular('Mietkonto ausdrucken PDF ab ...', '');
                $f->hidden_feld('anzeigen', 'show_mkb2pdf');
                $f->hidden_feld('mv_id', request()->input('mietvertrag_id'));
                echo "<select name=\"monat\">";
                for ($a = 1; $a <= 12; $a++) {
                    echo "<option value=\"$a\">$a</option>";
                }
                echo "</select>";

                echo "<select name=\"jahr\">";
                for ($a = date("Y"); $a >= date("Y") - 5; $a--) {
                    echo "<option value=\"$a\">$a</option>";
                }
                echo "</select>";
                $f->send_button('submit', 'PDF-Anzeigen');
                $f->ende_formular();
            } else {
                echo "MIETVERTRAG WÄHLEN";
            }
            break;

        case "show_mkb2pdf" :
            if (request()->has('mv_id')) {
                $mz = new miete ();
                $mz->mkb2pdf(request()->input('mv_id'), request()->input('monat'), request()->input('jahr'));
            } else {
                echo "MIETVERTRAG WÄHLEN";
            }
            break;

        case "alle_buchungen" :
            iframe_start();
            echo "Alle Bisherigen Buchungen";
            $buchung = new mietkonto ();
            $buchung->alle_buchungen_anzeigen($mietvertrag_id);
            iframe_end();
            break;
        // ##########################################################
        case "forderung_seit_einzug" :
            iframe_start();
            echo "Alle Bisherigen Buchungen";
            $buchung = new mietkonto ();
            // $buchung->forderungen_seit_einzug($mietvertrag_id);
            $buchung->buchungen_forderungen_seit_einzug($mietvertrag_id);
            iframe_end();
            break;

        // ##########################################################
        case "buchung_zeitraum" :
            iframe_start();
            echo "BUCHUNG AUS ZEITRAUM";
            $buchung = new mietkonto ();
            $buchung->buchung_zeitraum($mietvertrag_id, "2008-01-01", "2008-12-31");
            iframe_end();
            break;

        // ##########################################################
        // Dieses berechnet alle Monate seit Einzug bis aktueller Monat/Jahr und zeigt Forderungen und Zahlungen als Mietkontenblatt an.
        case "mietkonto_uebersicht_detailiert_ALT" :
            $jahr_aktuell = date("Y");
            $monat_aktuell = date("m");
            // ###Grunddaten zum MV holen d.h. mietvertrag von, bis #########
            $buchung = new mietkonto ();
            $buchung->erstelle_formular("Mietkontenübersicht...", NULL);
            include_once("options/links/links.mietkonten_blatt_uebersicht.php");
            $buchung->mietvertrag_grunddaten_holen($mietvertrag_id);
            // $konto_stand = $buchung->mieter_mietkonto_stand($mietvertrag_id, 1, 2007);
            // echo "<h1>$konto_stand €</h1>";
            // ##Einzugsdatum in Array umwandeln
            $einzugs_datum = explode("-", $buchung->mietvertrag_von);
            $einzugs_monat = $einzugs_datum [1];
            $einzugs_jahr = $einzugs_datum [0];
            // ##Einzugsdatum in 01.01.1999 - Format umwandeln
            $mietvertrag_von = $buchung->date_mysql2german($buchung->mietvertrag_von);
            // ##Auszugsdatum in 01.01.1999 - Format umwandeln
            $mietvertrag_bis = $buchung->date_mysql2german($buchung->mietvertrag_bis);
            // ###Infos über die Einheit##################
            $einheit_id = $buchung->get_einheit_id_von_mietvertrag($mietvertrag_id);
            // $einheit_kurzname = $buchung->einheit_kurzname_finden($einheit_id);
            $einheit_info = new einheit ();
            $einheit_info->get_einheit_info($einheit_id);
            // ######Ermitteln von Personen_IDS vom MV
            $mieter_ids = $buchung->get_personen_ids_mietvertrag($mietvertrag_id);
            // $buchung->array_anzeigen($mieter_ids);
            // ####Personendaten zu Person_id holen#######
            for ($i = 0; $i < count($mieter_ids); $i++) {
                $mieter_daten_arr [] = $buchung->get_person_infos($mieter_ids [$i] ['PERSON_MIETVERTRAG_PERSON_ID']);
            }
            // ##Überschrift##############################
            $heute = date("Y-m-d");
            $heute_deutsch = $buchung->date_mysql2german($heute);
            echo "<p class=\"ueberschrift_mietkonto\"><a href=\"mietkonto_pdf.php?mietvertrag_id=$mietvertrag_id\"><img src=\"includes/logos/pdf_logo.gif\" width=30 align=left></a><b>Mietkonto  Einheit: $einheit_info->einheit_kurzname</b> Anschrift: $einheit_info->haus_strasse $einheit_info->haus_nummer $einheit_info->haus_plz $einheit_info->haus_stadt";
            echo "<br>Einzug: $mietvertrag_von ";
            // ####Ausgabe von Personendaten####################
            echo "<b>Mieter:</b>";
            for ($i = 0; $i < count($mieter_daten_arr); $i++) {
                echo " " . $mieter_daten_arr [$i] [0] ['name'] . " " . $mieter_daten_arr [$i] [0] ['first_name'] . " ";
            }
            echo " </p>";
            echo "<hr>";
            // ###########################################
            // $buchung->array_anzeigen($mieter_daten_arr);
            // ###########################################
            // ####Alle Zahlbetraege in Array ############
            // $alle_zahbetraege_arr = $buchung->alle_zahlbetraege_arr($mietvertrag_id);
            // $buchung->array_anzeigen($alle_zahbetraege_arr);
            // ###########ERMITTELN DES SALDOS BEI DER VORVERWALTUNG##############################
            $zeitraum = new zeitraum (); // Zeitraum Klasse für den Monatearray
            $saldo_vortrag_vorverwaltung = $buchung->saldo_vortrag_vorverwaltung($mietvertrag_id);

            if (empty ($saldo_vortrag_vorverwaltung)) {
                // $saldo_vortrag_vorverwaltung = '0';
                $datum_mietdefinition = $buchung->datum_1_mietdefinition($mietvertrag_id);
                if (!empty ($datum_mietdefinition)) {
                    // echo "DEFINITION $datum_mietdefinition";
                    $datum_mietdefinition = explode("-", $datum_mietdefinition);
                    $monat_mietdefintion = $datum_mietdefinition [1];
                    $jahr_mietdefinition = $datum_mietdefinition [0];
                    $monate_arr = $zeitraum->zeitraum_generieren($monat_mietdefintion, $jahr_mietdefinition, $monat_aktuell, $jahr_aktuell);
                    $einzugs_monat = $monat_mietdefintion;
                    $einzugs_jahr = $jahr_mietdefinition;
                } else {
                    // echo "EINZUG $einzugs_monat $einzugs_jahr";
                    $monate_arr = $zeitraum->zeitraum_generieren($einzugs_monat, $einzugs_jahr, $monat_aktuell, $jahr_aktuell);
                }
            } else {
                // echo "SALDO EXISTS";
                $datum_saldo_vv = $buchung->datum_saldo_vortrag_vorverwaltung($mietvertrag_id);

                $datum_saldo_vv = explode("-", $datum_saldo_vv);
                $monat_saldo_vv = $datum_saldo_vv [1];
                $jahr_saldo_vv = $datum_saldo_vv [0];
                // echo "1. $monat_saldo_vv / $jahr_saldo_vv<br>";

                if ($monat_saldo_vv < 12) {
                    $einzugs_monat = $monat_saldo_vv + 1;
                    $einzugs_jahr = $jahr_saldo_vv;
                    // echo "1. $monat_saldo_vv / $jahr_saldo_vv<br>";
                    // echo "2. $einzugs_monat / $einzugs_jahr<br>";
                }
                if ($monat_saldo_vv == 12) {
                    $einzugs_monat = 1;
                    $einzugs_jahr = $jahr_saldo_vv + 1;
                }
                // echo "$einzugs_monat / $einzugs_jahr";
                $monate_arr = $zeitraum->zeitraum_generieren($einzugs_monat, $einzugs_jahr, $monat_aktuell, $jahr_aktuell);
            }
            // $buchung->array_anzeigen($monate_arr);
            // ##########ENDE DER VORBEREITUNG DER NOTWENDIGEN DATEN FÜR DIE BERECHUNG##################################################

            // #######################tabelenkopf##############################
            echo "<table class=aktuelle_buchungen>";
            echo "<tr><td>Datum</td><td></td><td>Monatssoll</td><td>Zahlung</td><td>Differenz pro Monat</td><td><b>Saldo</b></td></tr>";
            // 2te zeile saldo vorverwaltung
            if ($saldo_vortrag_vorverwaltung) {
                $saldo_vortrag_vorverwaltung = number_format($saldo_vortrag_vorverwaltung, 2, ".", "");
                echo "<tr><td colspan=5 align=left><b>SALDO VORTRAG VORVERWALTUNG</td><td><b>$saldo_vortrag_vorverwaltung €</b></td></tr>";
            }
            // #################################################################
            // $summe_gesamt_forderung = $buchung->summe_forderungen_seit_einzug($mietvertrag_id);
            // $saldo = $summe_aller_zahlbetraege - $summe_gesamt_forderung;
            // #####################jeden monat durchlaufen####################
            for ($i = 0; $i < count($monate_arr); $i++) {
                $monat = $monate_arr [$i] ['monat'];
                $jahr = $monate_arr [$i] ['jahr'];
                $alle_zahlbetraege_monat_arr = $buchung->alle_zahlbetraege_monat_arr($mietvertrag_id, $monat, $jahr);
                // $buchung->array_anzeigen($alle_zahlbetraege_monat_arr);
                $summe_forderung_monatlich = $buchung->summe_forderung_monatlich($mietvertrag_id, $monat, $jahr);
                // zahlungen aus aktuellen monat zählen
                $anzahl_zahlungen_im_monat = count($alle_zahlbetraege_monat_arr);

                // ######################### EINZUGSMONAT #############################################
                // echo "<h1>$einzugs_monat $monat";
                if ($einzugs_monat == $monat && $einzugs_jahr == $jahr) {

                    // erste zahlung im einzugsmonat und jahr
                    $saldo_vormonat = $saldo_vortrag_vorverwaltung;
                    echo $saldo_vormonat;
                    if ($anzahl_zahlungen_im_monat > 0) {
                        for ($a = 0; $a < $anzahl_zahlungen_im_monat; $a++) {

                            $zahlungsnummer = $a + 1;
                            $zahlungsdatum = $buchung->date_mysql2german($alle_zahlbetraege_monat_arr [$a] ['DATUM']);
                            // ########1 MONAT NUR 1 ZAHLUNG ##############
                            if ($a < 1) {
                                // Miete Sollzeile
                                echo "<tr><td>01.$monat.$jahr</td><td>Soll Miete $monat/$jahr </td><td><b>$saldo_vortrag_vorverwaltung $summe_forderung_monatlich €</b></td><td></td><td></td><td></td></tr>";
                                $differenz_monatlich = $alle_zahlbetraege_monat_arr [$a] ['BETRAG'] - $summe_forderung_monatlich;
                                // AUSGABE 1 zahlung
                                // 1. Zahlung Einzugsmonat
                                if ($zahlungsnummer < $anzahl_zahlungen_im_monat) {
                                    $gesamt_soll = $saldo_vormonat + $differenz_monatlich;
                                    $saldo_vormonat = $gesamt_soll;
                                    $differenz_monatlich = number_format($differenz_monatlich, 2, ".", "");

                                    $gesamt_soll = number_format($gesamt_soll, 2, ".", "");
                                    echo "<tr><td>$zahlungsdatum</td><td>1.Zahlbetrag 1.mon</td><td></td><td>" . $alle_zahlbetraege_monat_arr [$a] ['BETRAG'] . " € </td><td>$differenz_monatlich €</td><td><b></b></td></tr>";
                                }
                                /*
                                 * //letzte Zahlung Einzugsmonat
                                 * if($zahlungsnummer==$anzahl_zahlungen_im_monat){
                                 * $gesamt_soll = $saldo_vormonat + $differenz_monatlich;
                                 * $saldo_vormonat = $gesamt_soll;
                                 * echo "<tr><td>$zahlungsdatum</td><td>letzter Zahlbetrag $monat</td><td></td><td>".$alle_zahlbetraege_monat_arr[$a][BETRAG]." € </td><td>$differenz_monatlich €</td><td><b>$gesamt_soll €</b></td></tr>";
                                 * }
                                 * }
                                 */
                            }
                            // ########1 MONAT NACH DER 1. ZAHLUNG ##############
                            if ($a > 0) {
                                // weitere/andere Zahlungen Einzugsmonat
                                if ($zahlungsnummer < $anzahl_zahlungen_im_monat) {
                                    $differenz_monatlich = $alle_zahlbetraege_monat_arr [$a] ['BETRAG'];
                                    $gesamt_soll = $gesamt_soll + $alle_zahlbetraege_monat_arr [$a] ['BETRAG'];
                                    $saldo_vormonat = $gesamt_soll;
                                    $differenz_monatlich = number_format($differenz_monatlich, 2, ".", "");
                                    echo "<tr><td>$zahlungsdatum</td><td>Zahlbetrag zwischen</td><td></td><td>" . $alle_zahlbetraege_monat_arr [$a] ['BETRAG'] . " € </td><td>$differenz_monatlich €</td><td><b></b></td></tr>";
                                }
                                // LETZTE ZAHLUNG EINZUGSMONAT
                                if ($zahlungsnummer == $anzahl_zahlungen_im_monat) {
                                    $differenz_monatlich = $alle_zahlbetraege_monat_arr [$a] ['BETRAG'];
                                    $gesamt_soll = $gesamt_soll + $alle_zahlbetraege_monat_arr [$a] ['BETRAG'];
                                    // $gesamt_soll = $gesamt_soll + $saldo_vortrag_vorverwaltung;
                                    // $gesamt_soll = $saldo_vormonat + $differenz_monatlich;
                                    // echo "GSOLL $gesamt_soll<br>";
                                    $saldo_vormonat = $gesamt_soll;
                                    $differenz_monatlich = number_format($differenz_monatlich, 2, ".", "");
                                    echo "<tr><td>$zahlungsdatum</td><td>" . $alle_zahlbetraege_monat_arr [$a] ['BEMERKUNG'] . "</td><td></td><td>" . $alle_zahlbetraege_monat_arr [$a] ['BETRAG'] . " € </td><td>$differenz_monatlich €</td><td><b>$gesamt_soll €</b></td></tr>";
                                }
                            }
                        }
                    }  // end if($anzahl_zahlungen_im_monat>0)
                    else {

                        // Fehlender Betrag da keine Zahlung im 1. Monat
                        echo "<tr><td>01.$monat.$jahr</td><td>Soll Miete $monat/$jahr </td><td><b>$summe_forderung_monatlich €</b></td><td></td><td></td><td></td></tr>";
                        // Keine Zahlung
                        $differenz_monatlich = 0 - $summe_forderung_monatlich;
                        $saldo_vormonat = $saldo_vortrag_vorverwaltung;
                        $gesamt_soll = $differenz_monatlich + $saldo_vormonat;
                        $saldo_vormonat = $gesamt_soll;
                        $differenz_monatlich = number_format($differenz_monatlich, 2, ".", "");
                        $gesamt_soll = number_format($gesamt_soll, 2, ".", "");
                        echo "<tr><td></td><td><b>Keine Zahlung im 1 Monat</b></td><td></td><td>0,00 € </td><td><b>$differenz_monatlich €</b></td><td><b>$gesamt_soll €</b></td></tr>";
                    } // ende else
                }  // ende einzugsmonat
                // ####################################################################################

                // ######################### ANDERE MONATE #############################################
                else {
                    echo "<tr><td colspan=6><hr></td></tr>"; // ZEILE ZWISCHEN MONATEN
                    // erste zahlung im einzugsmonat und jahr
                    if ($anzahl_zahlungen_im_monat > 0) {
                        for ($a = 0; $a < $anzahl_zahlungen_im_monat; $a++) {
                            $zahlungsnummer = $a + 1;
                            $zahlungsdatum = $buchung->date_mysql2german($alle_zahlbetraege_monat_arr [$a] ['DATUM']);
                            if ($a < 1) {

                                // Miete Sollzeile
                                echo "<tr><td>01.$monat.$jahr</td><td>Soll Miete $monat/$jahr </td><td><b>$summe_forderung_monatlich €</b></td><td></td><td></td><td></td></tr>";
                                $differenz_monatlich = $alle_zahlbetraege_monat_arr [$a] ['BETRAG'] - $summe_forderung_monatlich;
                                $differenz_monatlich = number_format($differenz_monatlich, 2, ".", "");
                                // AUSGABE 1 zahlung
                                // 1. Zahlung Einzugsmonat
                                if ($zahlungsnummer < $anzahl_zahlungen_im_monat) {
                                    $differenz_monatlich = number_format($differenz_monatlich, 2, ".", "");
                                    echo "<tr><td>$zahlungsdatum</td><td>" . $alle_zahlbetraege_monat_arr [$a] ['BEMERKUNG'] . "</td><td></td><td>" . $alle_zahlbetraege_monat_arr [$a] ['BETRAG'] . " € </td><td>$differenz_monatlich €</td><td><b></b></td></tr>";

                                    $gesamt_soll = $saldo_vormonat + $differenz_monatlich;
                                    $saldo_vormonat = $gesamt_soll;
                                    // echo "DDDDOLL $gesamt_soll<br>";
                                }
                                if ($zahlungsnummer == $anzahl_zahlungen_im_monat) {
                                    $gesamt_soll = $saldo_vormonat + $differenz_monatlich;
                                    $differenz_monatlich = number_format($differenz_monatlich, 2, ".", "");
                                    $gesamt_soll = number_format($gesamt_soll, 2, ".", "");
                                    echo "<tr><td>$zahlungsdatum</td><td>" . $alle_zahlbetraege_monat_arr [$a] ['BEMERKUNG'] . "</td><td></td><td>" . $alle_zahlbetraege_monat_arr [$a] ['BETRAG'] . " € </td><td>$differenz_monatlich €</td><td><b>$gesamt_soll €</b></td></tr>";

                                    $saldo_vormonat = $gesamt_soll;
                                    // echo "DDDDOLL $gesamt_soll<br>";
                                }
                            }
                            if ($a > 0) {
                                // 1. Zahlung Andere monate
                                if ($zahlungsnummer < $anzahl_zahlungen_im_monat) {
                                    $differenz_monatlich = $alle_zahlbetraege_monat_arr [$a] ['BETRAG'];
                                    $differenz_monatlich = number_format($differenz_monatlich, 2, ".", "");
                                    echo "<tr><td>$zahlungsdatum</td><td>" . $alle_zahlbetraege_monat_arr [$a] ['BEMERKUNG'] . "</td><td></td><td>" . $alle_zahlbetraege_monat_arr [$a] ['BETRAG'] . " € </td><td>$differenz_monatlich €</td><td><b></b></td></tr>";
                                    $gesamt_soll = $saldo_vormonat + $differenz_monatlich;
                                    $saldo_vormonat = $gesamt_soll;
                                    // echo "DDDDOLL111 $gesamt_soll<br>";
                                    $saldo_vormonat = $gesamt_soll;
                                }
                                if ($zahlungsnummer == $anzahl_zahlungen_im_monat) {
                                    $differenz_monatlich = $alle_zahlbetraege_monat_arr [$a] ['BETRAG'];
                                    $differenz_monatlich = number_format($differenz_monatlich, 2, ".", "");
                                    $gesamt_soll = $saldo_vormonat + $differenz_monatlich;
                                    $saldo_vormonat = $gesamt_soll;
                                    // echo "DDDDOLLeeee $gesamt_soll<br>";
                                    /* Letzte Zeile */
                                    $gesamt_soll = number_format($gesamt_soll, 2, ".", "");
                                    echo "<tr><td>$zahlungsdatum</td><td>" . $alle_zahlbetraege_monat_arr [$a] ['BEMERKUNG'] . "</td><td></td><td>" . $alle_zahlbetraege_monat_arr [$a] ['BETRAG'] . " € </td><td>$differenz_monatlich €</td><td><b>$gesamt_soll €</b></td></tr>";
                                    $saldo_vormonat = $gesamt_soll;
                                }
                            }
                        }
                    }  // end if($anzahl_zahlungen_im_monat>0)
                    else {
                        // Fehlender Betrag da keine Zahlung
                        echo "<tr><td>01.$monat.$jahr</td><td>Soll Miete $monat/$jahr </td><td><b>$summe_forderung_monatlich €</b></td><td></td><td></td><td></td></tr>";
                        // Keine Zahlung
                        $differenz_monatlich = 0 - $summe_forderung_monatlich;
                        $gesamt_soll = $differenz_monatlich + $saldo_vormonat;
                        $gesamt_soll = number_format($gesamt_soll, 2, ".", "");
                        echo "<tr><td></td><td><b>Keine Zahlung</b></td><td></td><td>0,00 € </td><td><b>$differenz_monatlich €</b></td><td><b>$gesamt_soll €</b></td></tr>";

                        $saldo_vormonat = $gesamt_soll;
                    } // ende else
                } // ende andere monate

                // ######################### ENDE ANDERE MONATE##########################################
            } // ende für (for $i) alle monate durchlaufen
            // ###########################################################
            // tabellenende
            /* Aktueller KOntostand Zeile */
            echo "<tr><td colspan=6><hr>Aktuell <b>$gesamt_soll €</b></td></tr>";
            echo "</table>";
            // ###########################################################
            // echo "</div>";
            // iframe_end();
            $buchung->ende_formular();
            break;
        // CASE ENDE
        // #####################################################################################
        // ##########################################################
        // Dieses berechnet alle Monate seit 1. Zahlung bis aktueller Monat/Jahr und zeigt Forderungen und Zahlungen als Mietkontenblatt an.
        case "mietkonto_detailiert_seit_1zahlung" :
            // ###Grunddaten zum MV holen d.h. mietvertrag von, bis #########
            $buchung = new mietkonto ();
            $buchung->erstelle_formular("Mietkontenübersicht...", NULL);
            include_once("options/links/links.mietkonten_blatt_uebersicht.php");
            $buchung->mietvertrag_grunddaten_holen($mietvertrag_id);
            // ##Einzugsdatum in Array umwandeln
            $einzugs_datum = explode("-", $buchung->mietvertrag_von);
            $einzugs_monat = $einzugs_datum [1];
            $einzugs_jahr = $einzugs_datum [0];
            // ##Einzugsdatum in 01.01.1999 - Format umwandeln
            $mietvertrag_von = $buchung->date_mysql2german($buchung->mietvertrag_von);
            // ##Auszugsdatum in 01.01.1999 - Format umwandeln
            $mietvertrag_bis = $buchung->date_mysql2german($buchung->mietvertrag_bis);
            // ###Infos über die Einheit##################
            $einheit_id = $buchung->get_einheit_id_von_mietvertrag($mietvertrag_id);
            $einheit_kurzname = $buchung->einheit_kurzname_finden($einheit_id);
            $einheit_info = new einheit ();
            $einheit_info->get_einheit_info($einheit_id);
            // ######Ermitteln von Personen_IDS vom MV
            $mieter_ids = $buchung->get_personen_ids_mietvertrag($mietvertrag_id);
            // $buchung->array_anzeigen($mieter_ids);
            // ####Personendaten zu Person_id holen#######
            for ($i = 0; $i < count($mieter_ids); $i++) {
                $mieter_daten_arr [] = $buchung->get_person_infos($mieter_ids [$i] ['PERSON_MIETVERTRAG_PERSON_ID']);
            }
            // ##Überschrift##############################
            $heute = date("Y-m-d");
            $heute_deutsch = $buchung->date_mysql2german($heute);
            echo "<p class=\"ueberschrift_mietkonto\"><b>Mietkonto  Einheit: $einheit_kurzname</b> Anschrift: $einheit_info->haus_strasse $einheit_info->haus_nummer $einheit_info->haus_plz $einheit_info->haus_stadt</p>";
            echo "<p>Einzug: $mietvertrag_von</p>";
            // ####Ausgabe von Personendaten####################
            echo "<p class=\"mieterdaten_mietkonto\"><b>Mieter:</b>";
            for ($i = 0; $i < count($mieter_daten_arr); $i++) {
                echo " " . $mieter_daten_arr [$i] [0] ['name'] . " " . $mieter_daten_arr [$i] [0] ['first_name'] . " ";
            }
            echo "</p>";
            echo "<p><a href=\"mietkonto_pdf.php?mietvertrag_id=$mietvertrag_id\"><img src=\"includes/logos/pdf_logo.gif\" width=30 ></a></p>";
            echo "<hr>";
            // ###########################################
            // $buchung->array_anzeigen($mieter_daten_arr);
            // ###########################################
            // ####Alle Zahlbetraege in Array ############
            $alle_zahbetraege_arr = $buchung->alle_zahlbetraege_arr($mietvertrag_id);
            // $buchung->array_anzeigen($alle_zahbetraege_arr);
            // ####Summe aller Zahlbetraege als String ############
            $summe_aller_zahlbetraege = $buchung->summe_aller_zahlbetraege($mietvertrag_id);
            echo "<p>Summe aller ZB $summe_aller_zahlbetraege €</p>";
            // ########Aufteilung der Zahlbetraege###############
            // $aufteilung_buchung_arr = $buchung->summe_uebersicht_aufteilung($mietvertrag_id, $buchung->mietvertrag_von, $buchung->datum_heute);
            // $buchung->array_anzeigen($aufteilung_buchung_arr);
            // #########Erstellung eines Arrays mit MONAT JAHR seit EINZUGSMONAT / JAHR###############
            // $monate_arr = $buchung->monate_seit_einzug_arr($mietvertrag_id);
            $zeitraum = new zeitraum ();
            $datum_erste_zahlung = $buchung->datum_1_zahlung($mietvertrag_id);
            if ($datum_erste_zahlung) {
                $datum_erste_zahlung_arr = explode("-", $datum_erste_zahlung);
                $erste_zahlung_monat = $datum_erste_zahlung_arr [1];
                $erste_zahlung_jahr = $datum_erste_zahlung_arr [0];
                $aktueller_monat = date("m");
                $aktuelles_jahr = date("Y");
                echo $erste_zahlung_monat;
                $monate_arr = $zeitraum->zeitraum_generieren($erste_zahlung_monat, $erste_zahlung_jahr, $aktueller_monat, $aktuelles_jahr);
                // $buchung->array_anzeigen($monate_arr);
            } else {
                echo "Keine Zahlung bisher";
            }

            // $buchung->array_anzeigen($monate_arr);
            // ###########################################
            // $buchung->forderungen_array_seit_einzug($mietvertrag_id);
            // ###########ERMITTELN DES SALDOS BEI DER VORVERWALTUNG##############################
            $saldo_vortrag_vorverwaltung = $buchung->saldo_vortrag_vorverwaltung($mietvertrag_id);
            if (!isset ($saldo_vortrag_vorverwaltung)) {
                $saldo_vortrag_vorverwaltung = '0';
            }
            // ##########ENDE DER VORBEREITUNG DER NOTWENDIGEN DATEN FÜR DIE BERECHUNG##################################################

            // #######################tabelenkopf##############################
            echo "<table class=aktuelle_buchungen>";
            echo "<tr><td>Datum</td><td></td><td>Monatssoll</td><td>Zahlung</td><td>Differenz pro Monat</td><td><b>Saldo</b></td></tr>";
            // 2te zeile saldo vorverwaltung
            echo "<tr><td colspan=5 align=left><b>SALDO VORTRAG VORVERWALTUNG</td><td><b>$saldo_vortrag_vorverwaltung €</b></td></tr>";
            // #################################################################
            $summe_gesamt_forderung = $buchung->summe_forderungen_seit_einzug($mietvertrag_id);
            $saldo = $summe_aller_zahlbetraege - $summe_gesamt_forderung;
            // #####################jeden monat durchlaufen####################
            for ($i = 0; $i < count($monate_arr); $i++) {
                $monat = $monate_arr [$i] ['monat'];
                $jahr = $monate_arr [$i] ['jahr'];
                $alle_zahlbetraege_monat_arr = $buchung->alle_zahlbetraege_monat_arr($mietvertrag_id, $monat, $jahr);
                // $buchung->array_anzeigen($alle_zahlbetraege_monat_arr);
                $summe_forderung_monatlich = $buchung->summe_forderung_monatlich($mietvertrag_id, $monat, $jahr);
                // zahlungen aus aktuellen monat zählen
                $anzahl_zahlungen_im_monat = count($alle_zahlbetraege_monat_arr);

                // ######################### EINZUGSMONAT #############################################
                if ($einzugs_monat == $monat && $einzugs_jahr == $jahr) {
                    // erste zahlung im einzugsmonat und jahr
                    $saldo_vormonat = $saldo_vortrag_vorverwaltung;
                    if ($anzahl_zahlungen_im_monat > 0) {
                        for ($a = 0; $a < $anzahl_zahlungen_im_monat; $a++) {

                            $zahlungsnummer = $a + 1;
                            $zahlungsdatum = $buchung->date_mysql2german($alle_zahlbetraege_monat_arr [$a] ['DATUM']);
                            // ########1 MONAT NUR 1 ZAHLUNG ##############
                            if ($a < 1) {
                                // Miete Sollzeile
                                echo "<tr><td>01.$monat.$jahr</td><td>Soll Miete $monat/$jahr </td><td><b>$summe_forderung_monatlich €</b></td><td></td><td></td><td></td></tr>";
                                $differenz_monatlich = $alle_zahlbetraege_monat_arr [$a] ['BETRAG'] - $summe_forderung_monatlich;
                                // AUSGABE 1 zahlung
                                // 1. Zahlung Einzugsmonat
                                if ($zahlungsnummer < $anzahl_zahlungen_im_monat) {
                                    $gesamt_soll = $saldo_vormonat + $differenz_monatlich;
                                    $saldo_vormonat = $gesamt_soll;
                                    echo "<tr><td>$zahlungsdatum</td><td>1.Zahlbetrag 1.mon</td><td></td><td>" . $alle_zahlbetraege_monat_arr [$a] ['BETRAG'] . " € </td><td>$differenz_monatlich €</td><td><b></b></td></tr>";
                                }
                                /*
                                 * //letzte Zahlung Einzugsmonat
                                 * if($zahlungsnummer==$anzahl_zahlungen_im_monat){
                                 * $gesamt_soll = $saldo_vormonat + $differenz_monatlich;
                                 * $saldo_vormonat = $gesamt_soll;
                                 * echo "<tr><td>$zahlungsdatum</td><td>letzter Zahlbetrag $monat</td><td></td><td>".$alle_zahlbetraege_monat_arr[$a][BETRAG]." € </td><td>$differenz_monatlich €</td><td><b>$gesamt_soll €</b></td></tr>";
                                 * }
                                 * }
                                 */
                            }
                            // ########1 MONAT NACH DER 1. ZAHLUNG ##############
                            if ($a > 0) {
                                // weitere/andere Zahlungen Einzugsmonat
                                if ($zahlungsnummer < $anzahl_zahlungen_im_monat) {
                                    $differenz_monatlich = $alle_zahlbetraege_monat_arr [$a] ['BETRAG'];
                                    $gesamt_soll = $gesamt_soll + $alle_zahlbetraege_monat_arr [$a] ['BETRAG'];
                                    $saldo_vormonat = $gesamt_soll;
                                    echo "<tr><td>$zahlungsdatum</td><td>Zahlbetrag zwischen</td><td></td><td>" . $alle_zahlbetraege_monat_arr [$a] ['BETRAG'] . " € </td><td>$differenz_monatlich €</td><td><b></b></td></tr>";
                                }
                                // LETZTE ZAHLUNG EINZUGSMONAT
                                if ($zahlungsnummer == $anzahl_zahlungen_im_monat) {
                                    $differenz_monatlich = $alle_zahlbetraege_monat_arr [$a] ['BETRAG'];
                                    $gesamt_soll = $gesamt_soll + $alle_zahlbetraege_monat_arr [$a] ['BETRAG'];
                                    // $gesamt_soll = $saldo_vormonat + $differenz_monatlich;
                                    // echo "GSOLL $gesamt_soll<br>";
                                    $saldo_vormonat = $gesamt_soll;
                                    echo "<tr><td>$zahlungsdatum</td><td>" . $alle_zahlbetraege_monat_arr [$a] ['BEMERKUNG'] . "</td><td></td><td>" . $alle_zahlbetraege_monat_arr [$a] ['BETRAG'] . " € </td><td>$differenz_monatlich €</td><td><b>$gesamt_soll €</b></td></tr>";
                                }
                            }
                        }
                    } else { // end if($anzahl_zahlungen_im_monat>0)
                        // Fehlender Betrag da keine Zahlung im 1. Monat
                        echo "<tr><td>01.$monat.$jahr</td><td>Soll Miete $monat/$jahr </td><td><b>$summe_forderung_monatlich €</b></td><td></td><td></td><td></td></tr>";
                        // Keine Zahlung
                        $differenz_monatlich = 0 - $summe_forderung_monatlich;
                        $gesamt_soll = $differenz_monatlich + $saldo_vormonat;
                        $saldo_vormonat = $gesamt_soll;
                        echo "<tr><td></td><td><b>Keine Zahlung im 1 Monat</b></td><td></td><td>0,00 € </td><td><b>$differenz_monatlich €</b></td><td><b>$gesamt_soll €</b></td></tr>";
                    } // ende else
                }  // ende einzugsmonat
                // ####################################################################################

                // ######################### ANDERE MONATE #############################################
                else {
                    echo "<tr><td colspan=6><hr></td></tr>"; // ZEILE ZWISCHEN MONATEN
                    // erste zahlung im einzugsmonat und jahr
                    if ($anzahl_zahlungen_im_monat > 0) {
                        for ($a = 0; $a < $anzahl_zahlungen_im_monat; $a++) {
                            $zahlungsnummer = $a + 1;
                            $zahlungsdatum = $buchung->date_mysql2german($alle_zahlbetraege_monat_arr [$a] ['DATUM']);
                            if ($a < 1) {

                                // Miete Sollzeile
                                echo "<tr><td>01.$monat.$jahr</td><td>Soll Miete $monat/$jahr </td><td><b>$summe_forderung_monatlich €</b></td><td></td><td></td><td></td></tr>";
                                $differenz_monatlich = $alle_zahlbetraege_monat_arr [$a] ['BETRAG'] - $summe_forderung_monatlich;
                                // AUSGABE 1 zahlung
                                // 1. Zahlung Einzugsmonat
                                if ($zahlungsnummer < $anzahl_zahlungen_im_monat) {
                                    echo "<tr><td>$zahlungsdatum</td><td>" . $alle_zahlbetraege_monat_arr [$a] ['BEMERKUNG'] . "</td><td></td><td>" . $alle_zahlbetraege_monat_arr [$a] ['BETRAG'] . " € </td><td>$differenz_monatlich €</td><td><b></b></td></tr>";

                                    $gesamt_soll = $saldo_vormonat + $differenz_monatlich;
                                    $saldo_vormonat = $gesamt_soll;
                                    // echo "DDDDOLL $gesamt_soll<br>";
                                }
                                if ($zahlungsnummer == $anzahl_zahlungen_im_monat) {
                                    $gesamt_soll = $saldo_vormonat + $differenz_monatlich;
                                    echo "<tr><td>$zahlungsdatum</td><td>" . $alle_zahlbetraege_monat_arr [$a] ['BEMERKUNG'] . "</td><td></td><td>" . $alle_zahlbetraege_monat_arr [$a] ['BETRAG'] . " € </td><td>$differenz_monatlich €</td><td><b>$gesamt_soll €</b></td></tr>";

                                    $saldo_vormonat = $gesamt_soll;
                                    // echo "DDDDOLL $gesamt_soll<br>";
                                }
                            }
                            if ($a > 0) {
                                // 1. Zahlung Andere monate
                                if ($zahlungsnummer < $anzahl_zahlungen_im_monat) {
                                    $differenz_monatlich = $alle_zahlbetraege_monat_arr [$a] ['BETRAG'];

                                    echo "<tr><td>$zahlungsdatum</td><td>" . $alle_zahlbetraege_monat_arr [$a] ['BEMERKUNG'] . "</td><td></td><td>" . $alle_zahlbetraege_monat_arr [$a] ['BETRAG'] . " € </td><td>$differenz_monatlich €</td><td><b></b></td></tr>";
                                    $gesamt_soll = $saldo_vormonat + $differenz_monatlich;
                                    $saldo_vormonat = $gesamt_soll;
                                    // echo "DDDDOLL111 $gesamt_soll<br>";
                                    $saldo_vormonat = $gesamt_soll;
                                }
                                if ($zahlungsnummer == $anzahl_zahlungen_im_monat) {
                                    $differenz_monatlich = $alle_zahlbetraege_monat_arr [$a] ['BETRAG'];
                                    $gesamt_soll = $saldo_vormonat + $differenz_monatlich;
                                    $saldo_vormonat = $gesamt_soll;
                                    // echo "DDDDOLLeeee $gesamt_soll<br>";
                                    echo "<tr><td>$zahlungsdatum</td><td>" . $alle_zahlbetraege_monat_arr [$a] ['BEMERKUNG'] . "</td><td></td><td>" . $alle_zahlbetraege_monat_arr [$a] ['BETRAG'] . " € </td><td>$differenz_monatlich €</td><td><b>$gesamt_soll €</b></td></tr>";
                                    $saldo_vormonat = $gesamt_soll;
                                }
                            }
                        }
                    }  // end if($anzahl_zahlungen_im_monat>0)
                    else {
                        // Fehlender Betrag da keine Zahlung
                        echo "<tr><td>01.$monat.$jahr</td><td>Soll Miete $monat/$jahr </td><td><b>$summe_forderung_monatlich €</b></td><td></td><td></td><td></td></tr>";
                        // Keine Zahlung
                        $differenz_monatlich = 0 - $summe_forderung_monatlich;
                        $gesamt_soll = $differenz_monatlich + $saldo_vormonat;
                        echo "<tr><td></td><td><b>Keine Zahlung</b></td><td></td><td>0,00 € </td><td><b>$differenz_monatlich €</b></td><td><b>$gesamt_soll €</b></td></tr>";

                        $saldo_vormonat = $gesamt_soll;
                    } // ende else
                } // ende andere monate

                // ######################### ENDE ANDERE MONATE##########################################
            } // ende für (for $i) alle monate durchlaufen
            // ###########################################################
            // tabellenende
            echo "</table>";
            // ###########################################################
            // echo "</div>";
            // iframe_end();
            $buchung->ende_formular();
            break;
        // CASE ENDE
        // #####################################################################################
        case "mietkonto_gesamt_pdf" :
            echo "PDF-ausgabe";
            // ###Grunddaten zum MV holen d.h. mietvertrag von, bis #########
            $buchung = new mietkonto ();
            $buchung->mietvertrag_grunddaten_holen($mietvertrag_id);
            // ##Einzugsdatum in Array umwandeln
            $einzugs_datum = explode("-", $buchung->mietvertrag_von);
            $einzugs_monat = $einzugs_datum [1];
            $einzugs_jahr = $einzugs_datum [0];
            // ##Einzugsdatum in 01.01.1999 - Format umwandeln
            $mietvertrag_von = $buchung->date_mysql2german($buchung->mietvertrag_von);
            // ##Auszugsdatum in 01.01.1999 - Format umwandeln
            $mietvertrag_bis = $buchung->date_mysql2german($buchung->mietvertrag_bis);
            // ###Infos über die Einheit##################
            $einheit_id = $buchung->get_einheit_id_von_mietvertrag($mietvertrag_id);
            $einheit_kurzname = $buchung->einheit_kurzname_finden($einheit_id);
            $einheit_info = new einheit ();
            $einheit_info->get_einheit_info($einheit_id);
            // ######Ermitteln von Personen_IDS vom MV
            $mieter_ids = $buchung->get_personen_ids_mietvertrag($mietvertrag_id);
            // $buchung->array_anzeigen($mieter_ids);
            // ####Personendaten zu Person_id holen#######
            for ($i = 0; $i < count($mieter_ids); $i++) {
                $mieter_daten_arr [] = $buchung->get_person_infos($mieter_ids [$i] ['PERSON_MIETVERTRAG_PERSON_ID']);
            }
            // ##überschrift##############################
            $heute = date("Y-m-d");
            $heute_deutsch = $buchung->date_mysql2german($heute);
            // ##erste seite
            $pdf = new Cezpdf ('a4', 'portrait');
            $pdf->ezSetCmMargins(4.3, 0, 1.5, 2.5);
            $berlus_schrift = 'Times-Roman.afm';
            $text_schrift = 'Helvetica.afm';
            // links,
            $pdf->addJpegFromFile('includes/logos/Slogo_78_31.jpg', 190, 730, 200, 80);
            $pdf->setLineStyle(0.5);
            // line(x1,y1,x2,y2) /links anfang hoehe weite hoehe
            $pdf->line(42, 722, 550, 722);
            $pdf->selectFont($berlus_schrift);
            $pdf->ezText("BERLUS HAUSVERWALTUNG * Fontanestr. 1 * 14193 Berlin", 7);
            $pdf->ezSetDy(-20);
            $pdf->selectFont($berlus_schrift);
            $pdf->addText(400, 680, 10, 'Telefon (030) 89784477');
            $pdf->addText(400, 665, 10, 'Telefax (030) 89784479');
            $pdf->addText(400, 650, 10, 'Email:	 info@berlus.de');
            $pdf->addText(400, 635, 10, '<c:uline><b>Sprechzeiten</b></c:uline>');
            $pdf->addText(400, 620, 10, 'Dienstag:');
            $pdf->addText(455, 620, 10, '9:00-12:00');
            $pdf->addText(400, 605, 10, 'Donnerstag:');
            $pdf->addText(450, 605, 10, '14:00-17:00');
            $pdf->addText(400, 590, 10, '<c:uline><b>Internet</b></c:uline>');
            $pdf->addText(400, 575, 10, 'Berlus.de');
            $pdf->addText(400, 560, 10, 'Hausverwaltung-Forum.de');
            $pdf->addText(400, 545, 10, 'Hausverwaltung-Magazin.de');

            // echo "<p class=\"ueberschrift_mietkonto\"><b>Mietkonto Einheit: $einheit_kurzname</b> Anschrift: $einheit_info->haus_strasse $einheit_info->haus_nummer $einheit_info->haus_plz $einheit_info->haus_stadt</p>";
            // ####Ausgabe von Personendaten####################
            // echo "<p class=\"mieterdaten_mietkonto\"><b>Mieter:</b>";
            for ($i = 0; $i < count($mieter_daten_arr); $i++) {
                // echo " ".$mieter_daten_arr[$i][0][PERSON_NACHNAME]." ".$mieter_daten_arr[$i][0][PERSON_VORNAME]." ";
            }
            // echo "</p>";
            // echo "<p><img src=\"pdfclass/pdf_logo.gif\" width=30 ></p>";
            // echo "<hr>";
            // ###########################################
            // $buchung->array_anzeigen($mieter_daten_arr);
            // ###########################################
            // ####Alle Zahlbetraege in Array ############
            $alle_zahbetraege_arr = $buchung->alle_zahlbetraege_arr($mietvertrag_id);
            // $buchung->array_anzeigen($alle_zahbetraege_arr);
            // ####Summe aller Zahlbetraege als String ############
            $summe_aller_zahlbetraege = $buchung->summe_aller_zahlbetraege($mietvertrag_id);
            // ########Aufteilung der Zahlbetraege###############
            $aufteilung_buchung_arr = $buchung->summe_uebersicht_aufteilung($mietvertrag_id, $buchung->mietvertrag_von, $buchung->datum_heute);
            // $buchung->array_anzeigen($aufteilung_buchung_arr);
            // #########Erstellung eines Arrays mit MONAT JAHR seit EINZUGSMONAT / JAHR###############
            // $monate_arr = $buchung->monate_seit_einzug_arr($mietvertrag_id);
            $zeitraum = new zeitraum ();
            $monate_arr = $zeitraum->zeitraum_arr_seit_uebernahme($mietvertrag_id);
            // $buchung->array_anzeigen($monate_arr);
            // ###########################################
            // $buchung->forderungen_array_seit_einzug($mietvertrag_id);
            // ###########ERMITTELN DES SALDOS BEI DER VORVERWALTUNG##############################
            $saldo_vortrag_vorverwaltung = $buchung->saldo_vortrag_vorverwaltung($mietvertrag_id);
            if ($saldo_vortrag_vorverwaltung = FALSE) {
                $saldo_vortrag_vorverwaltung == "0";
            }
            // ##########ENDE DER VORBEREITUNG DER NOTWENDIGEN DATEN FÜR DIE BERECHUNG##################################################

            // #######################tabelenkopf##############################
            // echo "<table class=aktuelle_buchungen>";
            // echo "<tr><td>Datum</td><td></td><td>Monatssoll</td><td>Zahlung</td><td>Differenz pro Monat</td><td><b>Saldo</b></td></tr>";
            // 2te zeile saldo vorverwaltung
            // echo "<tr><td colspan=5 align=left><b>SALDO VORTRAG VORVERWALTUNG</td><td><b>$saldo_vortrag_vorverwaltung €</b></td></tr>";
            // #################################################################
            $summe_gesamt_forderung = $buchung->summe_forderungen_seit_einzug($mietvertrag_id);
            $saldo = $summe_aller_zahlbetraege - $summe_gesamt_forderung;

            // #####################jeden monat durchlaufen####################
            for ($i = 0; $i < count($monate_arr); $i++) {
                $monat = $monate_arr [$i] ['monat'];
                $jahr = $monate_arr [$i] ['jahr'];
                $alle_zahlbetraege_monat_arr = $buchung->alle_zahlbetraege_monat_arr($mietvertrag_id, $monat, $jahr);
                // $buchung->array_anzeigen($alle_zahlbetraege_monat_arr);
                $summe_forderung_monatlich = $buchung->summe_forderung_monatlich($mietvertrag_id, $monat, $jahr);
                // zahlungen aus aktuellen monat zählen
                $anzahl_zahlungen_im_monat = count($alle_zahlbetraege_monat_arr);

                // ######################### EINZUGSMONAT #############################################
                if ($einzugs_monat == $monat && $einzugs_jahr == $jahr) {
                    // erste zahlung im einzugsmonat und jahr
                    $saldo_vormonat = $saldo_vortrag_vorverwaltung;
                    if ($anzahl_zahlungen_im_monat > 0) {
                        for ($a = 0; $a < $anzahl_zahlungen_im_monat; $a++) {

                            $zahlungsnummer = $a + 1;
                            $zahlungsdatum = $buchung->date_mysql2german($alle_zahlbetraege_monat_arr [$a] ['DATUM']);
                            if ($a < 1) {
                                // Miete Sollzeile
                                // echo "<tr><td>01.$monat.$jahr</td><td>Soll Miete $monat/$jahr </td><td><b>$summe_forderung_monatlich €</b></td><td></td><td></td><td></td></tr>";
                                $differenz_monatlich = $alle_zahlbetraege_monat_arr [$a] ['BETRAG'] - $summe_forderung_monatlich;
                                // AUSGABE 1 zahlung
                                // 1. Zahlung Einzugsmonat
                                if ($zahlungsnummer < $anzahl_zahlungen_im_monat) {
                                    $gesamt_soll = $saldo_vormonat + $differenz_monatlich;
                                    $saldo_vormonat = $gesamt_soll;
                                    // echo "<tr><td>$zahlungsdatum</td><td>Zahlbetrag</td><td></td><td>".$alle_zahlbetraege_monat_arr[$a][BETRAG]." € </td><td>$differenz_monatlich €</td><td><b></b></td></tr>";
                                }
                                if ($zahlungsnummer == $anzahl_zahlungen_im_monat) {
                                    $gesamt_soll = $saldo_vormonat + $differenz_monatlich;
                                    $saldo_vormonat = $gesamt_soll;
                                    // echo "<tr><td>$zahlungsdatum</td><td>Zahlbetrag</td><td></td><td>".$alle_zahlbetraege_monat_arr[$a][BETRAG]." € </td><td>$differenz_monatlich €</td><td><b>$gesamt_soll €</b></td></tr>";
                                }
                            }
                            if ($a > 0) {
                                // weitere. Zahlung Einzugsmonat
                                if ($zahlungsnummer < $anzahl_zahlungen_im_monat) {
                                    $differenz_monatlich = $alle_zahlbetraege_monat_arr [$a] ['BETRAG'];
                                    $gesamt_soll = $gesamt_soll + $alle_zahlbetraege_monat_arr [$a] ['BETRAG'];
                                    $saldo_vormonat = $gesamt_soll;
                                    // echo "<tr><td>$zahlungsdatum</td><td>Zahlbetrag</td><td></td><td>".$alle_zahlbetraege_monat_arr[$a][BETRAG]." € </td><td>$differenz_monatlich €</td><td><b></b></td></tr>";
                                }
                                if ($zahlungsnummer == $anzahl_zahlungen_im_monat) {
                                    $differenz_monatlich = $alle_zahlbetraege_monat_arr [$a] ['BETRAG'];
                                    $gesamt_soll = $gesamt_soll + $alle_zahlbetraege_monat_arr [$a] ['BETRAG'];
                                    // $gesamt_soll = $saldo_vormonat + $differenz_monatlich;
                                    // echo "GSOLL $gesamt_soll<br>";
                                    $saldo_vormonat = $gesamt_soll;
                                    // echo "<tr><td>$zahlungsdatum</td><td>$bemerkung bbb</td><td></td><td>".$alle_zahlbetraege_monat_arr[$a][BETRAG]." € </td><td>$differenz_monatlich €</td><td><b>$gesamt_soll €</b></td></tr>";
                                }
                            }

                            // $gesamt_soll = $saldo_vormonat + $differenz_monatlich;
                            // $saldo_vormonat = $gesamt_soll;
                            // echo "HHHHGSOLL $gesamt_soll<br>";
                        }
                    }  // end if($anzahl_zahlungen_im_monat>0)
                    else {
                        // Fehlender Betrag da keine Zahlung
                        // echo "<tr><td>01.$monat.$jahr</td><td>Soll Miete $monat/$jahr </td><td><b>$summe_forderung_monatlich €</b></td><td></td><td></td><td></td></tr>";
                        // Keine Zahlung
                        $differenz_monatlich = 0 - $summe_forderung_monatlich;
                        $gesamt_soll = $differenz_monatlich + $saldo_vormonat;
                        $saldo_vormonat = $gesamt_soll;
                        // echo "<tr><td></td><td><b>Keine Zahlung </b></td><td></td><td>0,00 € </td><td><b>$differenz_monatlich €</b></td><td><b>$gesamt_soll €</b></td></tr>";
                    } // ende else
                }  // ende einzugsmonat
                // ####################################################################################

                // ######################### ANDERE MONATE #############################################
                else {
                    // echo "<tr><td colspan=6><hr></td></tr>"; // ZEILE ZWISCHEN MONATEN
                    // erste zahlung im einzugsmonat und jahr
                    if ($anzahl_zahlungen_im_monat > 0) {
                        for ($a = 0; $a < $anzahl_zahlungen_im_monat; $a++) {
                            $zahlungsnummer = $a + 1;
                            $zahlungsdatum = $buchung->date_mysql2german($alle_zahlbetraege_monat_arr [$a] ['DATUM']);
                            if ($a < 1) {

                                // Miete Sollzeile
                                // echo "<tr><td>01.$monat.$jahr</td><td>Soll Miete $monat/$jahr </td><td><b>$summe_forderung_monatlich €</b></td><td></td><td></td><td></td></tr>";
                                $differenz_monatlich = $alle_zahlbetraege_monat_arr [$a] ['BETRAG'] - $summe_forderung_monatlich;
                                // AUSGABE 1 zahlung
                                // 1. Zahlung Einzugsmonat
                                if ($zahlungsnummer < $anzahl_zahlungen_im_monat) {
                                    // echo "<tr><td>$zahlungsdatum</td><td>Zahlbetrag</td><td></td><td>".$alle_zahlbetraege_monat_arr[$a][BETRAG]." € </td><td>$differenz_monatlich €</td><td><b></b></td></tr>";

                                    $gesamt_soll = $saldo_vormonat + $differenz_monatlich;
                                    $saldo_vormonat = $gesamt_soll;
                                    // echo "DDDDOLL $gesamt_soll<br>";
                                }
                                if ($zahlungsnummer == $anzahl_zahlungen_im_monat) {
                                    $gesamt_soll = $saldo_vormonat + $differenz_monatlich;
                                    // echo "<tr><td>$zahlungsdatum</td><td>Zahlbetrag</td><td></td><td>".$alle_zahlbetraege_monat_arr[$a][BETRAG]." € </td><td>$differenz_monatlich €</td><td><b>$gesamt_soll €</b></td></tr>";

                                    $saldo_vormonat = $gesamt_soll;
                                    // echo "DDDDOLL $gesamt_soll<br>";
                                }
                            }
                            if ($a > 0) {
                                // 1. Zahlung Andere monate
                                if ($zahlungsnummer < $anzahl_zahlungen_im_monat) {
                                    $differenz_monatlich = $alle_zahlbetraege_monat_arr [$a] ['BETRAG'];

                                    // echo "<tr><td>$zahlungsdatum</td><td>Zahlbetrag</td><td></td><td>".$alle_zahlbetraege_monat_arr[$a][BETRAG]." € </td><td>$differenz_monatlich €</td><td><b></b></td></tr>";
                                    $gesamt_soll = $saldo_vormonat + $differenz_monatlich;
                                    $saldo_vormonat = $gesamt_soll;
                                    // echo "DDDDOLL111 $gesamt_soll<br>";
                                    $saldo_vormonat = $gesamt_soll;
                                }
                                if ($zahlungsnummer == $anzahl_zahlungen_im_monat) {
                                    $differenz_monatlich = $alle_zahlbetraege_monat_arr [$a] ['BETRAG'];
                                    $gesamt_soll = $saldo_vormonat + $differenz_monatlich;
                                    $saldo_vormonat = $gesamt_soll;
                                    // echo "DDDDOLLeeee $gesamt_soll<br>";
                                    // echo "<tr><td>$zahlungsdatum</td><td>Zahlbetrag</td><td></td><td>".$alle_zahlbetraege_monat_arr[$a][BETRAG]." € </td><td>$differenz_monatlich €</td><td><b>$gesamt_soll €</b></td></tr>";
                                    $saldo_vormonat = $gesamt_soll;
                                }
                            }
                        }
                    } else { // end if($anzahl_zahlungen_im_monat>0)
                        // Fehlender Betrag da keine Zahlung
                        // echo "<tr><td>01.$monat.$jahr</td><td>Soll Miete $monat/$jahr </td><td><b>$summe_forderung_monatlich €</b></td><td></td><td></td><td></td></tr>";
                        // Keine Zahlung
                        $differenz_monatlich = 0 - $summe_forderung_monatlich;
                        $gesamt_soll = $differenz_monatlich + $saldo_vormonat;
                        // echo "<tr><td></td><td><b>Keine Zahlung</b></td><td></td><td>0,00 € </td><td><b>$differenz_monatlich €</b></td><td><b>$gesamt_soll €</b></td></tr>";

                        $saldo_vormonat = $gesamt_soll;
                    } // ende else
                } // ende andere monate

                // ######################### ENDE ANDERE MONATE##########################################
            } // ende für (for $i) alle monate durchlaufen
            // ###linie unten footer seite 1
            $pdf->line(42, 50, 550, 50);
            $pdf->ezSetDy(-80); // abstand
            $pdf->addText(150, 40, 7, "BERLUS HAUSVERWALTUNG * Fontanestr. 1 * 14193 Berlin * Inhaber Wolfgang Wehrheim");
            $pdf->addText(120, 30, 7, "Bankverbindung: Dresdner Bank Berlin * BLZ: 100 800 00 * Konto-Nr.: 05 804 000 00 * Steuernummer: 24/582/61188");
            $pdf->ezStream();
            // ###########################################################
            break;

        // #########################################################

        case "aufteilung_buchung_zeitraum" :
            iframe_start();
            echo "BUCHUNG AUS ZEITRAUM";
            $buchung = new mietkonto ();
            $buchung->summe_uebersicht_aufteilung($mietvertrag_id, "2008-01-01", "2008-12-31");
            iframe_end();
            break;
        // ##########################################################
        case "forderung_aus_monat" :
            iframe_start();
            echo "Forderung aus Monat " . request()->input('monat') . " " . request()->input('jahr');
            $buchung = new mietkonto ();
            $forderung_arr = $buchung->forderung_monatlich($mietvertrag_id, request()->input('monat'), request()->input('jahr'));
            $monat_jahr = request()->input('monat') . request()->input('jahr');
            $buchung->monatsforderungen_anzeigen($monat_jahr, $forderung_arr);
            iframe_end();
            break;
        // ##########################################################
        case "liste" :
            iframe_start();
            echo "MIETEN MANUEL BUCHEN";
            $buchung = new mietkonto ();
            $buchung->to_do_liste();
            iframe_end();
            break;
        // ##########################################################
        case "miete_manuell_buchen" :
            iframe_start();
            if (request()->has('mietvertrag_id')) {
                $mietvertrag_id = request()->input('mietvertrag_id');
                echo "BUCHUNGS_FORM FÜR $mietvertrag_id";
                $buchung = new mietkonto ();
                echo "BB$mietvertrag_id BB";
                $buchung->buchung_form($mietvertrag_id);
            } else {
                // fals keine MV_ID eingegeben wurde, weiterleiten
                weiterleiten(route('web::mietkontenblatt::legacy', ['anzeigen' => 'liste'], false));
            }
            iframe_end();
            break;

        case "alle_mkb" :
            if (request()->has('objekt_id')) {
                session()->put('objekt_id', request()->input('objekt_id'));
            }
            if (!session()->has('objekt_id')) {
                fehlermeldung_ausgeben("Objekt wählen!");
            } else {
                $mz = new miete ();
                $mz->pdf_alle_mietkontenblaetter(session()->get('objekt_id'));
            }
            break;
    } // end switch
} // end anfangs IF

// ##############FUNKTIONEN#############
function mietvertrags_grunddaten($mietvertrag_id, $monat, $jahr)
{
    $mietkonto_info = new mietkonto ();
    echo "Jahr $jahr Monat $monat<br>";
    echo $mietkonto_info->mietvertrag_von;
    echo "<br>";
    echo $mietkonto_info->mietvertrag_bis;
    echo "<br>";
    echo $mietkonto_info->datum_heute;
    echo "<br>";
    echo $mietkonto_info->tag_heute;
    echo "<br>";
    echo $mietkonto_info->monat_heute;
    echo "<br>pERSONEN IM mv";
    echo $mietkonto_info->anzahl_personen_im_vertrag;
    echo "<pre>";
    echo "</pre>";
    echo "<hr>";
}

function mietkonto_monats_uebersicht_ORG($mietvertrag_id, $monat, $jahr, $vormonat_stand)
{
    if ($vormonat_stand == "0") {
        $konto_vormonat = 0;
    } else {
        $konto_vormonat = $vormonat_stand;
    }
    $mietkonto_info = new mietkonto ();
    $forderungen_arr = $mietkonto_info->forderung_monatlich($mietvertrag_id, $monat, $jahr);
    $summe_forderungen = $mietkonto_info->summe_forderung_monatlich($mietvertrag_id, $monat, $jahr);
    echo "<pre>";
    echo "</pre>";
    $zahlungen_arr = $mietkonto_info->zahlungen_monatlich($mietvertrag_id, $monat, $jahr);
    $summe_zahlungen = $mietkonto_info->summe_zahlung_monatlich($mietvertrag_id, $monat, $jahr);
    echo "<pre>";
    echo "</pre>";
    echo "<table width=100% border=1>";
    echo "<tr><td colspan=6>$monat $jahr</td></tr>";
    echo "<tr><td><b>FORDERUNGEN</td><td><b>ZAHLUNGEN</td><td><b>AUFTEILUNG</td><td><b>BERECHNUNG</td><td><b>KONTOSTAND_VOR</td><td><b>KONTOSTAND_NACH</td></tr>";
    echo "<tr><td align=right valign=top>"; // Zelle1
    for ($i = 0; $i < count($forderungen_arr); $i++) {
        echo "" . $forderungen_arr [$i] ['KOSTENKATEGORIE'] . " = " . $forderungen_arr [$i] ['BETRAG'] . "€<br>";
        if ($forderungen_arr [$i] ['KOSTENKATEGORIE'] == "BK") {
            $BK_BETRAG = $forderungen_arr [$i] ['BETRAG'];
        }
        if ($forderungen_arr [$i] ['KOSTENKATEGORIE'] == "HK") {
            $HK_BETRAG = $forderungen_arr [$i] ['BETRAG'];
        }
    }
    echo "</td>"; // ende zell1
    echo "<td align=right valign=top>"; // Zelle2
    for ($i = 0; $i < count($zahlungen_arr); $i++) {
        echo "" . $zahlungen_arr [$i] ['KOSTENKATEGORIE'] . " = " . $zahlungen_arr [$i] ['BETRAG'] . "€<br>";
    }
    echo "</td>"; // ende zell2
    echo "<td>"; // Zelle3
    if ($summe_zahlungen == 0) {
        echo "Keine Zahlung im Monat $monat</td>";
    } else {

        if (($summe_zahlungen + $konto_vormonat) > $summe_forderungen) {
            for ($i = 0; $i < count($forderungen_arr); $i++) {
                echo "" . $forderungen_arr [$i] ['KOSTENKATEGORIE'] . " = " . $forderungen_arr [$i] ['BETRAG'] . "€<br>";
            }
        } else {
            if (($summe_zahlungen + $konto_vormonat) > $BK_BETRAG) {
                echo "BK = $BK_BETRAG €<br>";
                $rest = ($summe_zahlungen + $konto_vormonat) - $BK_BETRAG;
            }
            if (($rest) > $HK_BETRAG) {
                echo "HK = $HK_BETRAG €<br>";
                $rest = $rest - $HK_BETRAG;
                echo "REST = $rest €";
            }
        }
    }
    echo "</td><td>"; // Zelle 4
    $kontostand_nach = ($summe_zahlungen + $konto_vormonat) - $summe_forderungen;
    echo "($summe_zahlungen + $konto_vormonat) - $summe_forderungen = $kontostand_nach";
    echo "</td><td>"; // Zelle5
    echo "Kontostandvormonat: $konto_vormonat";
    echo "</td><td>"; // Zelle6
    echo "Kontostand aktuell: $kontostand_nach";
    echo "</td></tr>";
    echo "<tr><td><b>Summe: $summe_forderungen €</td<td>Summe: $summe_zahlungen €</td><td></td><td></td><td></td><td></td></tr>";
    echo "</table>";
    // ###

    return $kontostand_nach;
}

function mietkonto_monats_uebersicht($mietvertrag_id, $monat, $jahr, $vormonat_stand)
{
    if ($vormonat_stand == "0") {
        $konto_vormonat = 0;
    } else {
        $konto_vormonat = $vormonat_stand;
    }
    $mietkonto_info = new mietkonto ();

    $forderungen_arr = $mietkonto_info->forderung_monatlich($mietvertrag_id, $monat, $jahr);
    $summe_forderungen = $mietkonto_info->summe_forderung_monatlich($mietvertrag_id, $monat, $jahr);
    $ausgangs_kaltmiete = $mietkonto_info->ausgangs_kaltmiete;
    $betriebskosten = $mietkonto_info->betriebskosten;
    $heizkosten = $mietkonto_info->heizkosten;
    $extras = $summe_forderungen - $ausgangs_kaltmiete - $betriebskosten - $heizkosten;
    echo "<pre>";
    echo "</pre>";

    $zahlungen_arr = $mietkonto_info->zahlungen_monatlich($mietvertrag_id, $monat, $jahr);
    $summe_zahlungen = $mietkonto_info->summe_zahlung_monatlich($mietvertrag_id, $monat, $jahr);
    $anzahl_zahlungen_im_monat = count($zahlungen_arr);
    echo "<pre>";
    echo "</pre>";
    echo "<table width=100% class=table_form >";
    echo "<tr class=\"zeile1\"><td colspan=5>$monat $jahr</td></tr>";
    echo "<tr class=\"zeile1\"><td><b>FORDERUNGEN</td><td><b>ZAHLUNGEN</td><td><b>AUFTEILUNG</td><td><b>BERECHNUNG</td><td><b>KONTOSTAND</td></tr>";
    echo "<tr class=\"zeile1\"><td align=right valign=top>"; // Zelle1

    for ($i = 0; $i < count($forderungen_arr); $i++) {
        echo "" . $forderungen_arr [$i] ['KOSTENKATEGORIE'] . " = " . $forderungen_arr [$i] ['BETRAG'] . "€<br>";
    }

    echo "</td>"; // ende zell1

    echo "<td align=right valign=top>"; // Zelle2
    if ($anzahl_zahlungen_im_monat > 0) {
        for ($i = 0; $i < count($zahlungen_arr); $i++) {
            $zeile = $i + 1;
            $zahlungs_datum = $mietkonto_info->date_mysql2german($zahlungen_arr [$i] ['DATUM']);
            echo "<b>$zeile. " . $zahlungs_datum . "</b><br>" . $zahlungen_arr [$i] ['KOSTENKATEGORIE'] . " = " . $zahlungen_arr [$i] ['BETRAG'] . "€<br>";
        }
    } else {
        echo "Keine Zahlungen im Monat $monat $jahr";
    }

    echo "</td>"; // ende zell2

    echo "<td valign=top align=right>"; // Zelle3

    if ($anzahl_zahlungen_im_monat == 0) {
        echo "Keine Aufteilung da keine Zahlungen im Monat $monat $jahr";
    }

    if ($anzahl_zahlungen_im_monat == 1 && $zahlungen_arr [0] ['KOSTENKATEGORIE'] == "ZAHLBETRAG") {

        if (($summe_zahlungen) >= $summe_forderungen) {
            for ($i = 0; $i < count($forderungen_arr); $i++) {
                echo "" . $forderungen_arr [$i] ['KOSTENKATEGORIE'] . " = " . $forderungen_arr [$i] ['BETRAG'] . "€<br>";
            }
            $rest = ($summe_zahlungen + $konto_vormonat) - $summe_forderungen;
            if ($rest > 0) {
                // echo "<br>REST von $rest € wird gutgeschrieben";
                $ueberschuss = $rest;
            }
        }
    }

    if ($anzahl_zahlungen_im_monat == 1 && $zahlungen_arr [0] ['KOSTENKATEGORIE'] != "ZAHLBETRAG") {

        if (($summe_zahlungen) >= $summe_forderungen) {
            for ($i = 0; $i < count($forderungen_arr); $i++) {
                echo "F" . $forderungen_arr [$i] ['KOSTENKATEGORIE'] . " = " . $forderungen_arr [$i] ['BETRAG'] . "€<br>";
            }
            $rest = ($summe_zahlungen + $konto_vormonat) - $summe_forderungen;
            if ($rest > 0) {
                // echo "REST von $rest € wird gutgeschrieben";
                $ueberschuss = $rest;
            }
        } else {
            echo "Z" . $zahlungen_arr [0] ['KOSTENKATEGORIE'] . " = " . $zahlungen_arr [0] ['BETRAG'] . "€<br>";
        }
    }

    if ($anzahl_zahlungen_im_monat > 1) {
        $gesamt_zahlung_und_konto = $summe_zahlungen + $konto_vormonat;
        if ($gesamt_zahlung_und_konto < $summe_forderungen) {
            if (($gesamt_zahlung_und_konto) > $betriebskosten) {
                echo "BK = $betriebskosten €<br>";
                $rest = ($summe_zahlungen + $konto_vormonat) - $betriebskosten;
            }
            if (($rest) > $heizkosten) {
                echo "HK = $heizkosten €<br>";
                $rest = $rest - $heizkosten;
                echo "KALTMIETE = $rest €";
                $offen = $summe_forderungen - $betriebskosten - $heizkosten - $rest;
                // echo "<hr><hr><b>Offen = $offen €</b>";
                $ueberschuss = $offen;
            }
        }

        if ($gesamt_zahlung_und_konto >= $summe_forderungen) {
            if (($gesamt_zahlung_und_konto) > $betriebskosten) {
                echo "BK = -$betriebskosten €<br>";
                $rest = ($summe_zahlungen + $konto_vormonat) - $betriebskosten;
            }
            if (($rest) > $heizkosten) {
                echo "Übertrag:\n $konto_vormonat  €<br>";
                echo "HK = -$heizkosten €<br>";
                $rest = $rest - $heizkosten;
                echo "KALTMIETE = -$ausgangs_kaltmiete €<br>";
                echo "Extras: -$extras €";
                $ueberschuss = $summe_zahlungen - $betriebskosten - $heizkosten - $ausgangs_kaltmiete - $extras + $konto_vormonat;
            }
        }
    }
    echo "</td><td>"; // Zelle 4
    $kontostand_nach = ($summe_zahlungen + $konto_vormonat) - $summe_forderungen;
    echo "($summe_zahlungen + $konto_vormonat) - $summe_forderungen = $kontostand_nach";
    echo "</td><td>"; // Zelle5
    echo "Kontostandvormonat: $konto_vormonat<br>";
    echo "Kontostand aktuell: $kontostand_nach<br>";
    echo "</td></tr>";
    echo "<tr class=\"zeile1\"><td><b>Summe: $summe_forderungen €</td<td>Summe: $summe_zahlungen €</td><td><b>$ueberschuss €</b></td><td></td><td></td></tr>";
    echo "</table>";
    // ###

    return $kontostand_nach;
}

function mietkonto_uebersicht($mietvertrag_id)
{

    // #####BALKEN 4 MIETE
    echo "<div class=\"div balken5\" align=\"right\"><span class=\"font_balken_uberschrift\">MIETE</span><hr />";

    $mietvertrag_info = new mietvertrag ();
    $mietvertrag_info->get_anzahl_personen_zu_mietvertrag($mietvertrag_id);

    $mietvertrag_info->alle_zahlungen($mietvertrag_id);
    $mietvertrag_info->liste_der_forderungen($mietvertrag_id);
    echo "$aktuelle_miete";
    $alle_zahlungen = $mietvertrag_info->alle_zahlungen($mietvertrag_id);
    echo "<br><b>Zahlungen</b><hr>";
    echo $alle_zahlungen;
    $summe_aller_zahlungen = $mietvertrag_info->summe_aller_zahlungen($mietvertrag_id);
    echo "<b>Summe aller Zahlungen: $summe_aller_zahlungen €</b>";
    $mietvertrag_info->tage_berechnen_bis_heute("01.03.2008");
    echo "</div>"; // ende balken4
} // end funktion
function mietkonto_uebersicht_test($mietvertrag_id)
{

    // #####BALKEN 5 MIETE
    echo "<div class=\"div balken5\" align=\"right\"><span class=\"font_balken_uberschrift\">MIETE</span><hr />";

    $mietvertrag_info = new mietvertrag ();
    $einzugsdatum = $mietvertrag_info->get_mietvertrag_einzugs_datum($mietvertrag_id);
    $einzugsdatum_arr = explode("-", $einzugsdatum);
    $jahr = $einzugsdatum_arr [0];
    $monat = $einzugsdatum_arr [1];

    $datum = getdate();
    $aktuelles_jahr = $datum ['year'];
    $differenz_in_jahren = $aktuelles_jahr - $jahr;

    if ($differenz_in_jahren > 0) {
        for ($i = 0; $i <= $differenz_in_jahren; $i++) {
            $my_jahr = $aktuelles_jahr - $i;
            echo "<br><b>$my_jahr</b><br>";

            if ($my_jahr == $jahr) {
                for ($a = $monat; $a <= 12; $a++) {
                    echo "Jahr: $a . $my_jahr<br>";
                }
            } else {
                for ($a = 1; $a <= 12; $a++) {
                    echo "Aktuell im Jahr: $my_jahr   $a . $my_jahr<br>";
                }
            }
        }
    }

    if ($differenz_in_jahren == "0") {
        echo "Einzugsmonat $monat $jahr<br>";
        for ($a = $monat; $a <= 12; $a++) {
            echo ": $a . $aktuelles_jahr<br>";
        }
    }

    echo "</div>"; // ende balken5
} // end funktion
