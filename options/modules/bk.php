<?php
/**
 * BERLUSSIMO
 *
 * Hausverwaltungssoftware
 *
 *
 * @copyright    Copyright (c) 2010, Berlus GmbH, Fontanestr. 1, 14193 Berlin
 * @link         http://www.berlus.de
 * @author       Sanel Sivac & Wolfgang Wehrheim
 * @contact         software(@)berlus.de
 * @license     http://www.gnu.org/licenses/agpl.html AGPL Version 3
 *
 * @filesource   $HeadURL: http://192.168.2.52/svn/berlussimo_1/tags/02.11.2010 - Downloadversion 0.27/options/modules/bk.php $
 * @version      $Revision: 15 $
 * @modifiedby   $LastChangedBy: sivac $
 * @lastmodified $Date: 2011-07-07 10:41:33 +0200 (Do, 07 Jul 2011) $
 *
 */

/* Allgemeine Funktionsdatei laden */
include_once("includes/allgemeine_funktionen.php");
/* Wegen Serienbriefe */
include_once('classes/class_bpdf.php');

/* überprüfen ob Benutzer Zugriff auf das Modul hat */
if (!check_user_mod($_SESSION ['benutzer_id'], 'bk')) {
    echo '<script type="text/javascript">';
    echo "alert('Keine Berechtigung')";
    echo '</script>';
    die ();
}

/* Klasse "formular" für Formularerstellung laden */
include_once("classes/class_formular.php");

/* Modulabhängige Dateien d.h. Links und eigene Klasse */
include_once("classes/class_bk.php");

if (isset ($_REQUEST ['option']) && !empty ($_REQUEST ['option'])) {
    $option = $_REQUEST ['option'];
} else {
    $option = 'default';
}

/* Optionsschalter */
switch ($option) {

    default :
        break;

    case "assistent" :
        $bk = new bk ();
        $bk->assistent();
        break;

    case "schritt2" :
        $bk = new bk ();
        $bk->buchungskonten_auswahl();
        break;

    case "profil_reset" :
        $bk = new bk ();
        $bk->profil_reset();
        weiterleiten_in_sec("?daten=bk&option=assistent", 1);
        break;

    case "profile" :
        $bk = new bk ();
        $bk->liste_bk_profile();
        break;

    case "profil_anpassen" :
        if (isset ($_REQUEST ['profil_id']) && !empty ($_REQUEST ['profil_id'])) {
            $_SESSION ['profil_id'] = $_REQUEST ['profil_id'];
            $bk = new bk ();
            $bk->form_bk_profil_anpassen($_SESSION ['profil_id']);
        } else {
            fehlermeldung_ausgeben("Profil wählen!");
        }
        break;

    case "profil_aendern" :
        $bk = new bk ();
        $profil_id = $_POST ['profil_id'];
        $bez = $_POST ['profil_bez'];
        $jahr = $_POST ['jahr'];
        $typ = 'Wirtschaftseinheit';
        $typ_id = $_POST ['w_id'];
        $b_datum = $_POST ['berechnungsdatum'];
        $v_datum = $_POST ['verrechnungsdatum'];
        $bk->profil_aendern_db($profil_id, $bez, $jahr, $typ, $typ_id, $b_datum, $v_datum);
        break;

    case "profil_set" :
        $_SESSION ['profil_id'] = $_REQUEST ['profil_id'];
        weiterleiten_in_sec("?daten=bk&option=assistent", 0);
        break;

    case "buchung_anpassen" :
        if ($_REQUEST ['bk_be_id'] && $_REQUEST ['profil_id']) {
            $bk = new bk ();
            $bk->form_buchung_anpassen($_REQUEST ['bk_be_id'], $_REQUEST ['profil_id']);
        } else {
            fehlermeldung_ausgeben("Buchung und/oder Berechnungsprofil nicht ausgewählt");
        }
        break;

    case "buchung_aendern" :
        if ($_POST ['buchung_id'] && $_POST ['bk_be_id'] && $_POST ['umlagebetrag'] && $_POST ['kostentraeger_typ'] && $_POST ['kostentraeger_id'] && $_POST ['genkey'] && $_POST ['hndl_betrag']) {
            $bk = new bk ();
            $bk->update_bk_buchung($_POST ['bk_be_id'], $_POST ['umlagebetrag'], $_POST ['kostentraeger_typ'], $_POST ['kostentraeger_id'], $_POST ['genkey'], nummer_komma2punkt($_POST ['hndl_betrag']));
            weiterleiten_in_sec("?daten=bk&option=assistent", 0);
        } else {
            echo "DATEN UNVOLLSTäNDIG ERROR 505e7";
        }
        break;

    case "eig_konto_anlegen" :
        if (!empty ($_REQUEST ['kostenkonto']) && !empty ($_REQUEST ['konto_bez']) && $_SESSION ['profil_id']) {
            $bk = new bk ();
            $bk->bk_konto_speichern($_SESSION ['profil_id'], $_REQUEST ['kostenkonto'], $_REQUEST ['konto_bez']);
            unset ($_SESSION ['genkey']);
        } else {
            fehlermeldung_ausgeben("Fehler bk.php, 96");
        }
        header("Location: ?daten=bk&option=assistent");
        break;

    case "neues_bk_konto" :
        if (!empty ($_SESSION ['profil_id'])) {
            $bk = new bk ();
            $bk->form_eigenes_konto_anlegen($_SESSION ['profil_id']);
        } else {
            fehlermeldung_ausgeben("Fehler bk.php, 105");
        }
        break;

    case "change_konto" :
        unset ($_SESSION ['bk_konto']); // 1020
        unset ($_SESSION ['bk_konto_id']); // 2
        unset ($_SESSION ['genkey']); // 1
        weiterleiten_in_sec("?daten=bk&option=assistent", 0);
        break;

    case "konto_auswahl" :
        $_SESSION ['bk_konto'] = $_REQUEST ['bk_konto']; // 1020
        $_SESSION ['bk_konto_id'] = $_REQUEST ['bk_konto_id']; // 1
        unset ($_SESSION ['genkey']); // 1
        weiterleiten_in_sec("?daten=bk&option=assistent", 0);
        break;

    case "zusammenfassung" :
        $bk = new bk ();
        $bk->zusammenfassung($_SESSION ['profil_id']);
        break;

    case "pdf_ausgabe" :
        $bk = new bk ();
        if (isset ($_SESSION ['profil_id'])) {
            if (empty ($_REQUEST [$einheit_name . ' ' . $zeitraum])) {
                $bk->pdf_ausgabe_alle($_SESSION ['profil_id']);
            } else {
                $bk->pdf_ausgabe_bk($einheit_name . ' ' . $zeitraum);
            }
        } else {
            echo "Kein Berechnungsprofil gewählt";
        }
        break;

    case "wirtschaftseinheiten" :
        $bk = new bk ();
        $bk->wirtschaftseinheiten();
        break;

    case "wirtschaftseinheit_neu" :
        $wirt = new wirt_e ();
        $wirt->form_new_we();
        break;

    case "new_we" :
        $wirt = new wirt_e ();

        if (!empty ($_POST ['w_name'])) {
            $wirt->neue_we_speichern($_POST ['w_name']);
            header("Location: ?daten=bk&option=wirtschaftseinheiten");
        } else {
            fehlermeldung_ausgeben("Fehler: Wirtschaftseinheit braucht eine Bezeichnung!");
        }
        break;

    case "wirt_hinzu" :
        $w_id = $_REQUEST ['w_id'];
        $wirt = new wirt_e ();
        $wirt->einheit2_wirt($w_id, $_POST ['IMPORT_AUS'], $_REQUEST ['anzeigen']);
        $anzeigen = $_REQUEST ['anzeigen'];
        break;

    case "wirt_delete" :
        if (isset ($_POST ['submit_del_all']) && isset ($_REQUEST ['w_id'])) {
            $w_id = $_REQUEST ['w_id'];
            $anzeigen = $_REQUEST ['anzeigen'];
            $wirt = new wirt_e ();
            $wirt->del_all($w_id);
        }

        if (isset ($_POST ['submit_del']) && isset ($_REQUEST ['w_id'])) {
            $w_id = $_REQUEST ['w_id'];
            $anzeigen = $_REQUEST ['anzeigen'];
            $wirt = new wirt_e ();
            $wirt->del_eine($w_id, $_POST ['IMPORT_AUS']);
        }

        weiterleiten("?daten=bk&option=wirt_einheiten_hinzu&w_id=$w_id&anzeigen=$anzeigen");
        break;

    case "wirt_einheiten_hinzu" :
        if (!empty ($_REQUEST ['w_id'])) {
            $wirt = new wirt_e ();
            $wirt->form_einheit_hinzu($_REQUEST ['w_id']);
        } else {
            echo "Wirtschafseinheit wählen";
        }
        break;

    case "profil_pdf" :
        $bk = new bk ();
        ob_clean(); // ausgabepuffer leeren
        include_once('classes/class_bpdf.php');
        $pdf = new Cezpdf ('a4', 'portrait');
        $bpdf = new b_pdf ();
        $bpdf->b_header($pdf, 'Partner', $_SESSION ['partner_id'], 'portrait', 'Helvetica.afm', 6);
        $bk->pdf_uebersicht_profil($pdf, $_SESSION ['profil_id']); // mit pdf;
        $pdf->ezStream();

        break;

    case "konto_pro_anpassen" :
        $bk_konto = $_REQUEST ['bk_konto'];
        $bk_konto_id = $_REQUEST ['bk_konto_id'];
        if (!empty ($bk_konto) && !empty ($bk_konto_id)) {
            $bk = new bk ();
            if (!empty ($_SESSION ['profil_id'])) {
                $bk->form_konto_pro_anpassen($_SESSION ['profil_id'], $bk_konto, $bk_konto_id);
            } else {
                echo "fehler 645362";
            }
        }
        break;

    case "konto_pro_anpassen_send" :
        $prozent = $_REQUEST ['prozent'];
        $profil_id = $_REQUEST ['profil_id'];
        $bk_konto_id = $_REQUEST ['bk_konto_id'];
        print_req();
        if (!empty ($prozent) && !empty ($profil_id) && !empty ($bk_konto_id)) {
            $bk = new bk ();
            $bk->update_prozent_umlage($profil_id, $bk_konto_id, $prozent);
            weiterleiten('?daten=bk&option=assistent');
        } else {
            echo "Daten unvollständig fehler 247832748";
        }
        break;

    case "anpassung_bk_hk" :
        if (!empty ($_SESSION ['profil_id'])) {
            $bk = new bk ();
            $bk->form_bk_hk_anpassung($_SESSION ['profil_id']);
        } else {
            echo "Bitte Profil wählen";
        }
        break;

    case "anpassung_send" :
        $profil_id = $_REQUEST ['profil_id'];
        $kostenart = $_REQUEST ['kostenart'];
        $betrag = $_REQUEST ['betrag'];
        $genkey = $_REQUEST ['genkey'];

        if (!empty ($profil_id) && !empty ($kostenart) && !empty ($betrag) && !empty ($genkey)) {
            $bk = new bk ();
            $bk->bk_hk_anpassung_speichern($profil_id, $kostenart, $betrag, $genkey);
            weiterleiten('?daten=bk&option=anpassung_bk_hk');
        } else {
            echo "Daten unvollständig Error:jk3434";
        }
        break;

    case "anpassung_bk_hk_del" :
        if (!empty ($_REQUEST ['an_dat'])) {
            $bk = new bk ();
            $bk->bk_hk_anpassung_loeschen($_REQUEST ['an_dat']);
            weiterleiten('?daten=bk&option=anpassung_bk_hk');
        } else {
            echo "Anpassungszeile wählen";
        }
        break;

    case "test" :
        $bk = new bk ();
        $bk->test_res($_SESSION ['profil_id']);
        break;

    case "serienbrief" :
        include_once('classes/class_bpdf.php');
        $bpdf = new b_pdf ();
        $ber = new berlussimo_global ();
        $ber->objekt_auswahl_liste("?daten=bk&option=serienbrief");
        if (!isset ($_REQUEST ['empfaenger'])) {
            $bpdf->form_mieter2sess();
        } else {
            $empfaenger = $_REQUEST ['empfaenger'];
            $bpdf->form_serienbrief_an($empfaenger);
        }
        break;

    case "empfaenger2sess" :

        echo "empf2sess";
        echo '<pre>';
        print_r($_SESSION);

        print_r($_POST);

        if (!empty ($_REQUEST ['empfaenger_typ'])) {
            $anz = count($_REQUEST ['empf_ids']);
            if ($anz) {
                $arr = $_REQUEST ['empf_ids'];
            }
        }

        echo '<pre>';
        print_r($_SESSION ['empfaengerliste']);
        break;

    case "serienbrief_pdf" :
        include_once('classes/class_bpdf.php');
        $bpdf = new b_pdf ();
        $bpdf->erstelle_brief_vorlage($_REQUEST ['vorlagen_dat'], 'Mietvertrag', $_SESSION ['serienbrief_mvs'], $option = '0');
        break;

    case "serienbrief_vorlage_neu" :
        include_once('classes/class_bpdf.php');
        $bpdf = new b_pdf ();
        $bpdf->form_vorlage_neu();
        break;

    case "serienbrief_vorlage_send" :
        if (!empty ($_REQUEST ['kurztext']) && !empty ($_REQUEST ['text'])) {
            include_once('classes/class_bpdf.php');
            $bpdf = new b_pdf ();
            if ($_REQUEST ['kat'] == 'NEU') {
                $kat = $_REQUEST ['kat_man'];
            } else {
                $kat = $_REQUEST ['kat'];
            }
            $bpdf->vorlage_speichern($_REQUEST ['kurztext'], $_REQUEST ['text'], $kat, $_REQUEST ['empf_typ']);
            $bpdf->vorlage_waehlen($_REQUEST ['empf_typ']);
        } else {
            echo "Eingabe unvollsändig Err. 7824998123jhs";
        }
        break;

    case "vorlage_bearbeiten" :
        if (!empty ($_REQUEST ['vorlagen_dat'])) {
            include_once('classes/class_bpdf.php');
            $bpdf = new b_pdf ();
            $bpdf->form_vorlage_edit($_REQUEST ['vorlagen_dat']);
        } else {
            echo "Vorlage wählen";
        }
        break;

    case "serienbrief_vorlage_send1" :
        if (!empty ($_REQUEST ['kurztext']) && !empty ($_REQUEST ['text']) && !empty ($_REQUEST ['dat'])) {
            $bpdf = new b_pdf ();
            $bpdf->vorlage_update($_REQUEST ['dat'], $_REQUEST ['kurztext'], $_REQUEST ['text'], $_REQUEST ['kat'], $_REQUEST ['empf_typ']);
            $bpdf->vorlage_waehlen('Mieter');
        }
        break;

    case "form_profil_kopieren" :
        $bk = new bk ();
        $bk->form_profil_kopieren();
        break;

    case "profil_kopieren" :
        if (!empty ($_POST ['profil_id']) && !empty ($_POST ['profil_bez'])) {
            $bk = new bk ();
            $profil_id = $_POST ['profil_id'];
            $bezeichung = $_POST ['profil_bez'];
            if (isset ($_POST ['buchungen_kopieren'])) {
                $bk->bk_profil_kopieren($profil_id, $bezeichung, 1);
            } else {
                $bk->bk_profil_kopieren($profil_id, $bezeichung, 0);
            }
            echo "<br><br>Profil kopiert, bitte warten!";
            weiterleiten_in_sec('?daten=bk&option=profile', 2);
        } else {
            echo "Eingabe unvollständig Err. 72348724";
        }
        break;

    case "buchungen_hinzu" :
        $bk = new bk ();
        if (isset ($_POST ['genkey'])) {
            $_SESSION ['genkey'] = $_POST ['genkey'];
            $_SESSION ['hndl'] = $_POST ['hndl'];
            if ($_POST ['kontierung'] == '1') {
                $_SESSION ['kontierung'] = '1';
            } else {
                $_SESSION ['kontierung'] = '0';
            }
            if(isset($_POST['submit_key']) && $_POST['submit_key'] == "Bestehende Ändern") {
                $bk->update_genkey($_SESSION ['bk_konto_id'], $_SESSION ['profil_id'], $_SESSION ['genkey'], $_SESSION ['hndl']);
            }
        }
        if (isset ($_POST ['uebernahme'])) {
            $arr = $_POST ['uebernahme'];
            $anz = count($arr);

            for ($a = 0; $a < $anz; $a++) {
                $buchung_id = $arr [$a];
                $bk->buchungen_hinzu($buchung_id);
            }
        }
        weiterleiten("?daten=bk&option=assistent");
        break;

    case "energie" :
        $bk = new bk ();
        $bk->form_energie();
        break;

    case "energie_send" :
        $mvs = $_POST ['mvs'];
        if (is_array($mvs)) {

            $erg = $_POST ['ergebnisse'];
            $verbrauch = $_POST ['verbrauch'];

            $anz = count($mvs);
            for ($a = 0; $a < $anz; $a++) {
                $mv_id = $mvs [$a];
                $jahr = $_POST ['jahr'];
                $me = new mietentwicklung ();
                $datum = date_german2mysql($_POST ['v_datum']);

                $ergebnis_mv = nummer_komma2punkt($erg [$a]);
                $verbrauch_mv = nummer_komma2punkt($verbrauch [$a]);

                if ($ergebnis_mv != 0) {
                    if ($me->check_me('MIETVERTRAG', $mv_id, "Heizkostenabrechnung $jahr", $datum, $datum, 0) != true) {
                        $me->me_speichern('MIETVERTRAG', $mv_id, "Heizkostenabrechnung $jahr", $datum, $datum, $ergebnis_mv, 0);
                    }
                }

                if ($verbrauch_mv != 0) {
                    if ($me->check_me('MIETVERTRAG', $mv_id, "Energieverbrauch lt. Abr. $jahr", $datum, $datum, 0) != true) {
                        $me->me_speichern('MIETVERTRAG', $mv_id, "Energieverbrauch lt. Abr. $jahr", $datum, $datum, $verbrauch_mv, 0);
                    }
                }
            } // end for
        }
        weiterleiten('?daten=bk&option=energie');

        break;

    case "anpassung_bk_nk" :
        $bk = new bk ();
        $bk->form_bk_hk_anpassung_alle();
        break;

    case "me_send_hk_bk" :
        if (isset ($_POST ['kat'])) {
            if (is_array($_POST ['mvs'])) {
                $anz = count($_POST ['mvs']);
                $kat = $_POST ['kat'];
                $anfang = date_german2mysql($_POST ['v_datum']);
                $ende = date_german2mysql($_POST ['ende']);
                $ende_neu = "0000-00-00";
                for ($a = 0; $a < $anz; $a++) {
                    $mv_id = $_POST ['mvs'] [$a];
                    $mv = new mietvertraege ();
                    $mv->get_mietvertrag_infos_aktuell($mv_id);

                    $betrag_neu = nummer_komma2punkt($_POST ['vorschuss_neu'] [$a]);
                    $dat = $_POST ['dat'] [$a];
                    $me = new mietentwicklung ();
                    if (($me->check_me('MIETVERTRAG', $mv_id, "$kat", $anfang, $ende_neu, 0) != true) && ($betrag_neu != 0)) {
                        if ($dat > 0) {
                            $me = new mietentwicklung ();
                            $me_dat_arr = $me->get_dat_info($dat);
                            if (is_array($me_dat_arr)) {
                                $anfang_alt = $me_dat_arr ['ANFANG'];
                                $kat_alt = $me_dat_arr ['KOSTENKATEGORIE'];
                                $betrag_alt = $me_dat_arr ['BETRAG'];
                                $mwst_alt = $me_dat_arr ['MWST_ANTEIL'];
                                $me->me_dat_aendern2($dat, 'MIETVERTRAG', $mv_id, $anfang_alt, $ende, $kat_alt, $betrag_alt, $mwst_alt);
                            }
                        }

                        /* Wenn Abrechnung Anfang Ende gleich */
                        if (stristr($kat, 'abrechnung') == FALSE) {
                            $me->me_speichern('MIETVERTRAG', $mv_id, "$kat", $anfang, $ende_neu, $betrag_neu, 0);
                        } else {
                            $me->me_speichern('MIETVERTRAG', $mv_id, "$kat", $anfang, $anfang, $betrag_neu, 0);
                        }

                        hinweis_ausgeben("$mv->einheit_kurzname - $mv->personen_name_string_u - $betrag_neu");
                    } else {

                        fehlermeldung_ausgeben("$mv->einheit_kurzname $mv->personen_name_string_u existiert oder keine Eingabe!!!");
                    }
                }
            }
            weiterleiten_in_sec('?daten=bk&option=anpassung_bk_nk', 3);
        }

        break;
} // end switch for cases
