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
 * @filesource   $HeadURL: http://192.168.2.52/svn/berlussimo_1/tags/02.11.2010 - Downloadversion 0.27/options/modules/mietvertrag.php $
 * @version      $Revision: 15 $
 * @modifiedby   $LastChangedBy: sivac $
 * @lastmodified $Date: 2011-07-07 10:41:33 +0200 (Do, 07 Jul 2011) $
 *
 */

/* Modulabhängige Dateien d.h. Links und eigene Klasse */
?>
    <script type="text/javascript">
        function mieter_auswaehlen() {
            var mylist = document.getElementById("alle_mieter_list");
            var mieter_liste = document.getElementById("mieter_liste");
            var anzahl_mieter = mieter_liste.length;
            var selected_name = mylist.options[mylist.selectedIndex].text;
            var selected_value = mylist.options[mylist.selectedIndex].value;
            var neuer_mieter = document.createElement('option');
            neuer_mieter.text = selected_name;
            neuer_mieter.value = selected_value;
            for (a = 0; a < mieter_liste.length; a++) {
                if (mieter_liste[a].value == selected_value) {
                    var mieter_vorhanden = true;
                }
            }
            if (!mieter_vorhanden) {
                try {
                    mieter_liste.add(neuer_mieter, null); // nicht für iE
                    mieter_liste.style.visibility = "visible";
                }
                catch (ex) {
                    mieter_liste.add(neuer_mieter); // für iE
                    mieter_liste.style.visibility = "visible";
                }
            }
        }

        function mieter_entfernen() {
            var mieter_liste = document.getElementById("mieter_liste");
            mieter_liste.remove(mieter_liste.selectedIndex);
            var anzahl_mieter = mieter_liste.length;
            if (anzahl_mieter == 0) {
                mieter_liste.style.visibility = "hidden";
            }
        }

        function alle_mieter_auswaehlen() {
            var mieter_liste = document.getElementById("mieter_liste");
            for (var i = 0; i < mieter_liste.length; i++) {
                mieter_liste.options[i].selected = true;
            }
        }


    </script>
<?php
include_once("includes/allgemeine_funktionen.php");

/* überprüfen ob Benutzer Zugriff auf das Modul hat */
if (!check_user_mod($_SESSION ['benutzer_id'], 'mietvertrag_raus')) {
    echo '<script type="text/javascript">';
    echo "alert('Keine Berechtigung')";
    echo '</script>';
    die ();
}

include_once("includes/formular_funktionen.php");
include_once("classes/class_mietvertrag.php");
include_once("classes/class_mahnungen.php");
$daten = $_REQUEST ["daten"];
if (!empty ($_REQUEST ["mietvertrag_raus"])) {
    $mietvertrag_raus = $_REQUEST ["mietvertrag_raus"];
}
if (!empty ($_REQUEST ['einheit_id'])) {
    $einheit_id = $_REQUEST ['einheit_id'];
} else {
    $einheit_id = '';
}
//include ("options/links/links.mietvertrag.php");
if (isset ($_REQUEST ['mietvertrag_raus']) && !empty ($_REQUEST ['mietvertrag_raus'])) {
    $mietvertrag_raus = $_REQUEST ['mietvertrag_raus'];
} else {
    $mietvertrag_raus = 'default';
}

switch ($mietvertrag_raus) {

    default :
        hinweis_ausgeben("Bitte, weitere Wahl treffen!");
        break;

    case "mietvertrag_kurz" :
        $form = new mietkonto ();
        $form->erstelle_formular("Mietverträge", NULL);
        mietvertrag_kurz($einheit_id);
        $form->ende_formular();
        break;

    case "mietvertrag_aktuelle" :
        $form = new mietkonto ();
        $form->erstelle_formular("Aktuelle Mietverträge", NULL);
        mietvertrag_aktuelle($einheit_id);
        $form->ende_formular();
        break;

    case "mietvertrag_abgelaufen" :
        $form = new mietkonto ();
        $form->erstelle_formular("Abgelaufene Mietverträge", NULL);
        mietvertrag_abgelaufen($einheit_id);
        $form->ende_formular();
        break;

    case "mietvertrag_neu_alt" :
        $form = new mietkonto ();
        $form->erstelle_formular("Neuen Mietvertrag erstellen", NULL);
        iframe_start();
        mietvertrag_form_neu();
        iframe_end();
        $form->ende_formular();
        break;

    case "ls_teilnehmer_neu" :
        $form = new mietkonto ();
        $form->erstelle_formular("Teilnehmer am Lastschriftverfahren hinzufügen", NULL);
        $mv_info = new mietvertraege ();
        $mv_info->neuer_ls_teilnehmer();
        $form->ende_formular();
        break;

    case "ls_teilnehmer" :
        $form = new mietkonto ();
        $form->erstelle_formular("Teilnehmer am Lastschriftverfahren", NULL);
        $mv_info = new mietvertraege ();
        $mv_info->ls_akt_teilnehmer();
        $form->ende_formular();
        break;

    case "ls_teilnehmer_inaktiv" :
        $form = new mietkonto ();
        $form->erstelle_formular("Ausgesetzte Teilnahmen am Lastschriftverfahren", NULL);
        $mv_info = new mietvertraege ();
        $mv_info->ls_akt_teilnehmer_ausgesetzt();
        $form->ende_formular();
        break;

    case "ls_teilnehmer_aktivieren" :
        $form = new mietkonto ();
        $form->erstelle_formular("Teilnehmer aktivieren", NULL);
        $mv_info = new mietvertraege ();
        $mv_info->teilnehmer_aktivieren($_REQUEST ['mietvertrag_id']);
        weiterleiten_in_sec("?daten=mietvertrag_raus&mietvertrag_raus=ls_teilnehmer", 1);
        $form->ende_formular();
        break;

    case "ls_teilnehmer_deaktivieren" :
        $form = new mietkonto ();
        $form->erstelle_formular("Teilnehmer deaktivieren", NULL);
        $mv_info = new mietvertraege ();
        // $mv_info->get_mietvertrag_infos_aktuell($_REQUEST[mietvertrag_id]);
        $mv_info->teilnehmer_deaktivieren($_REQUEST ['mietvertrag_id']);
        weiterleiten_in_sec("?daten=mietvertrag_raus&mietvertrag_raus=ls_teilnehmer_inaktiv", 1);
        $form->ende_formular();
        break;

    case "ls_pruefen" :
        $form = new mietkonto ();
        $form->erstelle_formular("LS-Teilnehmer - Daten prüfen", NULL);
        /* Neuer LS-Teilnehmer */
        if (empty ($_POST ['deaktiviere_dat'])) {
            if (empty ($_POST ['einzugsart']) or empty ($_POST ['konto_inhaber_autoeinzug']) or empty ($_POST ['konto_nummer_autoeinzug']) or empty ($_POST ['blz_autoeinzug']) or empty ($_POST ['geld_institut'])) {
                $error = 'Daten unvollständig<br>';
            } else {
                if (!is_numeric($_POST ['konto_nummer_autoeinzug']) or !is_numeric($_POST ['blz_autoeinzug'])) {
                    $error .= 'Kontonummer und BLZ prüfen<br>';
                }
                if (isset ($error)) {
                    echo $error;
                } else {
                    echo "Eingegebene Daten";
                    echo "<hr><b>Teilnahme am Einzugsverfahren: JA</b><br>Einzugsart: $_POST[einzugsart]<br>";
                    echo "Kontoinhaber: $_POST[konto_inhaber_autoeinzug]<br>";
                    echo "Kontonummer: $_POST[konto_nummer_autoeinzug]<br>";
                    echo "BLZ: $_POST[blz_autoeinzug]<br>";
                    echo "Geldinstitut: $_POST[geld_institut]<br>";
                    $form->hidden_feld('mietvertrag_id', $_POST ['mietvertrag_id']);
                    $form->hidden_feld('einzugsart', $_POST ['einzugsart']);
                    $form->hidden_feld('konto_inhaber_autoeinzug', $_POST ['konto_inhaber_autoeinzug']);
                    $form->hidden_feld('konto_nummer_autoeinzug', $_POST ['konto_nummer_autoeinzug']);
                    $form->hidden_feld('blz_autoeinzug', $_POST ['blz_autoeinzug']);
                    $form->hidden_feld('geld_institut', $_POST ['geld_institut']);

                    $form->hidden_feld('mietvertrag_raus', 'ls_neu_speichern');
                    $form->send_button('btn_ls_speichern_neu', 'Speichern');
                }
            }
        } else {
            /* Bearbeiten bzw. Daten ändern und vervollständigen */
            if (empty ($_POST ['einzugsart']) or empty ($_POST ['konto_inhaber_autoeinzug']) or empty ($_POST ['konto_nummer_autoeinzug']) or empty ($_POST ['blz_autoeinzug']) or empty ($_POST ['geld_institut'])) {
                $error = 'Daten unvollständig<br>';
            } else {
                if (!is_numeric($_POST ['konto_nummer_autoeinzug']) or !is_numeric($_POST ['blz_autoeinzug'])) {
                    $error .= 'Kontonummer und BLZ prüfen<br>';
                }
                if (isset ($error)) {
                    echo $error;
                } else {
                    echo "Eingegebene Daten";
                    echo "<hr><b>Teilnahme am Einzugsverfahren: $_POST[einzugsermaechtigung]</b><br>Einzugsart: $_POST[einzugsart]<br>";
                    echo "Kontoinhaber: $_POST[konto_inhaber_autoeinzug]<br>";
                    echo "Kontonummer: $_POST[konto_nummer_autoeinzug]<br>";
                    echo "BLZ: $_POST[blz_autoeinzug]<br>";
                    echo "Geldinstitut: $_POST[geld_institut]<br>";
                    $form->hidden_feld('mietvertrag_id', $_POST ['mietvertrag_id']);
                    $form->hidden_feld('einzugsermeachtigung', $_POST ['einzugsermaechtigung']);
                    $form->hidden_feld('einzugsart', $_POST ['einzugsart']);
                    $form->hidden_feld('konto_inhaber_autoeinzug', $_POST ['konto_inhaber_autoeinzug']);
                    $form->hidden_feld('konto_nummer_autoeinzug', $_POST ['konto_nummer_autoeinzug']);
                    $form->hidden_feld('blz_autoeinzug', $_POST ['blz_autoeinzug']);
                    $form->hidden_feld('geld_institut', $_POST ['geld_institut']);
                    for ($a = 0; $a < count($_POST ['deaktiviere_dat']); $a++) {
                        $form->hidden_feld('deaktiviere_dat[]', $_POST ['deaktiviere_dat'] [$a]);
                    }
                    $form->hidden_feld('mietvertrag_raus', 'ls_bearbeitet_speichern');
                    $form->send_button('btn_ls_speichern_bb', 'Speichern');
                }
            }
        }
        $form->ende_formular();
        break;

    case "ls_neu_speichern" :
        $mv_info = new mietvertraege ();
        $mv_info->teilnahme_einzugsverfahren_eingeben($_POST ['mietvertrag_id'], $_POST ['konto_inhaber_autoeinzug'], $_POST ['konto_nummer_autoeinzug'], $_POST ['blz_autoeinzug'], $_POST ['geld_institut'], $_POST ['einzugsart'], 'JA');
        weiterleiten_in_sec("?daten=mietvertrag_raus&mietvertrag_raus=ls_teilnehmer", 2);
        break;

    case "ls_bearbeitet_speichern";
        $mv_info = new mietvertraege ();
        $mv_info->deaktiviere_detail_dats($_POST ['deaktiviere_dat']);
        $ja_nein = $_POST ['einzugsermeachtigung'];
        $mv_info->teilnahme_einzugsverfahren_eingeben($_POST ['mietvertrag_id'], $_POST ['konto_inhaber_autoeinzug'], $_POST ['konto_nummer_autoeinzug'], $_POST ['blz_autoeinzug'], $_POST ['geld_institut'], $_POST ['einzugsart'], $ja_nein);
        weiterleiten_in_sec("?daten=mietvertrag_raus&mietvertrag_raus=ls_teilnehmer_neu&mietvertrag_id=$_POST[mietvertrag_id]", 2);
        break;

    case "mietvertrag_neu" :
        $form = new mietkonto ();
        $form->erstelle_formular("Neuen Mietvertrag erstellen", NULL);
        iframe_start();
        $mv_info = new mietvertraege ();
        $mv_info->neuer_mv_form();
        iframe_end();
        $form->ende_formular();
        break;

    case "mv_pruefen" :
        $form = new mietkonto ();
        $form->erstelle_formular("Neuen Mietvertrag prüfen", NULL);
        /* Ob Mieter ausgewählt wurden */
        if (is_array($_POST ['mieter_liste'])) {
            // echo "MIETER OK";
        } else {
            $error = 'Keine Mieter im Vertrag<br>';
        }
        /* Einzugsdatum */
        if (!check_datum($_POST ['datum_einzug'])) {
            $error .= 'Einzugsdatum prüfen<br>';
        } else {
            // echo "Einzugsdatum OK";
        }
        /* Auszugsdatum */
        if (!empty ($_POST ['datum_auszug'])) {
            if (!check_datum($_POST ['datum_auszug'])) {
                $error .= 'Auszugsdatum prüfen<br>';
            } else {
                // echo "AUSZUGsdatum OK";
            }
        } else {
            $_POST ['datum_auszug'] = '0000-00-00';
        }
        if (!empty ($_POST ['miete_kalt'])) {
            if (is_numeric($_POST ['miete_kalt'])) {
                $error .= 'Kaltmiete Betrag fehlerhaft<br>';
            }
        } else {
            $error .= 'Keine Kaltmiete eingegeben<br>';
        }
        if (!empty ($_POST ['sollkaution'])) {
            if (is_numeric($_POST ['sollkaution'])) {
                $error .= 'Sollkaution Betrag fehlerhaft<br>';
            }
        } else {
            $error .= 'Keine Sollkaution eingegeben<br>';
        }
        if (isset ($error)) {
            echo $error;
        } else {
            echo "<p><h1>VERTRAGSDATEN:</h1><br>";
            $einheit_kurzname = einheit_kurzname($_POST ['einheit_id']);
            $haus_id = haus_id($_POST ['einheit_id']);
            $anschrift = haus_strasse_nr($haus_id);
            echo "<b>Einheit:</b> $einheit_kurzname<br>$anschrift<br>";
            $mv_info = new mietvertraege ();
            echo "<hr><b>Mieter:</b><br>";
            // print_r($_POST[mieter_liste]);
            $mv_info->mv_personen_anzeigen_form($_POST ['mieter_liste']);
            echo "<hr>Einzug: $_POST[datum_einzug]<br>";
            if ($_POST ['datum_auszug'] == '0000-00-00') {
                echo "Auszug: unbefristet<br>";
            } else {
                echo "Auszug: $_POST[datum_auszug]<br>";
            }
            echo "Miete kalt: $_POST[miete_kalt] €<br>";
            if (!empty ($_POST ['sollkaution'])) {
                echo "Sollkaution: $_POST[sollkaution] €<br>";
            }
            if (!empty ($_POST ['nebenkosten'])) {
                echo "Nebenkosten Vorauszahlung: $_POST[nebenkosten] €<br>";
            }
            if (!empty ($_POST ['heizkosten'])) {
                echo "Heizkosten Vorauszahlung: $_POST[heizkosten] €<br>";
            }
            $form->hidden_feld('einheit_id', $_POST ['einheit_id']);
            $form->hidden_feld('einheit_name', $einheit_kurzname);
            $form->hidden_feld('datum_einzug', $_POST ['datum_einzug']);
            $form->hidden_feld('datum_auszug', $_POST ['datum_auszug']);
            for ($a = 0; $a < count($_POST ['mieter_liste']); $a++) {
                $person_id = $_POST ['mieter_liste'] [$a];
                $form->hidden_feld('mieter_liste[]', $person_id);
            }
            $form->hidden_feld('sollkaution', $_POST ['sollkaution']);
            $form->hidden_feld('miete_kalt', $_POST ['miete_kalt']);
            $form->hidden_feld('heizkosten', $_POST ['heizkosten']);
            $form->hidden_feld('nebenkosten', $_POST ['nebenkosten']);

            $form->hidden_feld('mietvertrag_raus', 'mv_speichern');
            $form->send_button('btn_mv_erstellen', 'Mietvertrag speichern');

            echo "</p>";
        }

        $form->ende_formular();
        break;

    case "mv_speichern" :
        $form = new mietkonto ();
        $form->erstelle_formular("Mietvertrag speichern", NULL);
        iframe_start();
        $zugewiesene_vertrags_id = mietvertrag_anlegen($_POST ['datum_einzug'], $_POST ['datum_auszug'], $_POST ['einheit_id']);
        $anzahl_partner = count($_POST ['mieter_liste']);
        for ($a = 0; $a < $anzahl_partner; $a++) {
            $person_id = $_POST ['mieter_liste'] [$a];
            person_zu_mietvertrag($person_id, $zugewiesene_vertrags_id);
        }

        hinweis_ausgeben("Mietvertrag wurde erstellt!");

        $mv_info = new mietvertraege ();
        $k = new kautionen ();
        $mv_info->mieten_speichern($zugewiesene_vertrags_id, $_POST ['datum_einzug'], $_POST ['datum_auszug'], 'Miete kalt', $_POST ['miete_kalt'], 0);

        if (!empty ($_POST ['sollkaution'])) {
            $k->feld_wert_speichern($zugewiesene_vertrags_id, 'SOLL', $_POST ['sollkaution']);
        }

        if (!empty ($_POST ['heizkosten'])) {
            $mv_info->mieten_speichern($zugewiesene_vertrags_id, $_POST ['datum_einzug'], $_POST ['datum_auszug'], 'Heizkosten Vorauszahlung', $_POST ['heizkosten'], 0);
        }

        if (!empty ($_POST ['nebenkosten'])) {
            $mv_info->mieten_speichern($zugewiesene_vertrags_id, $_POST ['datum_einzug'], $_POST ['datum_auszug'], 'Nebenkosten Vorauszahlung', $_POST ['nebenkosten'], 0);
        }

        weiterleiten_in_sec("?daten=uebersicht&anzeigen=einheit&einheit_id=$_POST[einheit_id]", "1");
        iframe_end();
        $form->ende_formular();
        break;

    case "mv_geaendert_speichern" :
        $form = new mietkonto ();
        $mv_info = new mietvertraege ();
        $form->erstelle_formular("Mietvertragsänderungen speichern", NULL);
        $mv_info->mv_aenderungen_speichern($_POST ['mietvertrag_dat'], $_POST ['mietvertrag_id'], $_POST ['datum_auszug'], $_POST ['datum_einzug'], $_POST ['einheit_id'], $_POST ['mieter_liste']);
        $form->ende_formular();
        break;

    case "mietvertrag_beenden" :
        $form = new formular ();
        $form->erstelle_formular("Mietvertrag beenden", NULL);
        $m = new mietvertraege ();
        $m->mietvertrag_beenden_form($_REQUEST ['mietvertrag_id']);
        $form->ende_formular();
        break;

    case "mietvertrag_beenden_gesendet" :
        $form = new formular ();
        $form->erstelle_formular("Mietvertrag beenden", NULL);
        $m = new mietvertraege ();
        $mietvertrag_bis = date_german2mysql($_POST ['mietvertrag_bis']);
        if (strpos($mietvertrag_bis, '-00') || strpos($mietvertrag_bis, '0000-')
            || new DateTime($_POST['mietvertrag_von']) > new DateTime($_POST['mietvertrag_bis'])
            || !empty(DateTime::getLastErrors()['warning_count'])
        ) {
            hinweis_ausgeben("Bitte Mietvertragsende überprüfen.");
            weiterleiten_in_sec($_SERVER['HTTP_REFERER'], 5);
            $form->ende_formular();
            return;
        }
        $m->mietvertrag_beenden_db($_POST ['mietvertrag_dat'], $mietvertrag_bis);
        hinweis_ausgeben("Mietvertrag von $_POST[einheit_kurzname] wird zum $_POST[mietvertrag_bis] beendet.<br>");
        $m->mietdefinition_beenden($_POST ['mietvertrag_id'], $mietvertrag_bis);
        hinweis_ausgeben("Unbefristete Mietdefinitionen werden zum $_POST[mietvertrag_bis] beendet.");
        $verzugsanschrift = $_POST ['verzugsanschrift'];

        /* Verzugsanschrift */
        if ($verzugsanschrift) {
            $d = new detail ();
            $d->detail_speichern_2('MIETVERTRAG', $_POST ['mietvertrag_id'], 'Verzugsanschrift', $verzugsanschrift, $_SESSION ['username']);
        }
        /* Lastschrift beenden */
        //$m->lastschrift_beenden($_POST[mietvertrag_id]);
        $s = new sepa();
        if($s->mandat_beenden($_POST ['mietvertrag_id'], $_POST ['mietvertrag_bis'])) {
            hinweis_ausgeben("Teilnahme am SEPA-Lastschriftverfahren wurde beendet");
        }

        $einheit_id = $_POST ['einheit_id'];
        weiterleiten_in_sec("?daten=uebersicht&anzeigen=einheit&einheit_id=$einheit_id", 2);
        $form->ende_formular();
        break;

    case "mietvertrag_aendern_alt" :
        $form = new mietkonto ();
        $form->erstelle_formular("Mietvertrag ändern", NULL);
        iframe_start();
        mietvertrag_aendern_form($_REQUEST ['mietvertrag_id']);
        iframe_end();
        $form->ende_formular();
        break;

    /* aktuelle Mietverträge */
    case "mahnliste" :
        $f = new formular ();
        $f->fieldset("Mahnliste aktuell", 'mahnliste');
        $bg = new berlussimo_global ();
        $bg->objekt_auswahl_liste('?daten=mietvertrag_raus&mietvertrag_raus=mahnliste');
        if (isset ($_SESSION ['objekt_id'])) {
            $ma = new mahnungen ();
            if (!isset ($_REQUEST ['pdf'])) {
                $obj_id = $_SESSION ['objekt_id'];
                $link_pdf = "<a href=\"?daten=mietvertrag_raus&mietvertrag_raus=mahnliste&objekt_id=$obj_id&pdf\">Als PDF anzeigen</a>";
                echo $link_pdf;
                $ma->finde_schuldner('aktuelle');
            } else {
                $ma->finde_schuldner_pdf('aktuelle');
            }
        }
        $f->fieldset_ende();
        break;

    case "mahnliste_alle" :
        $f = new formular ();
        $f->fieldset("Mahnliste alle", 'mahnliste');
        $bg = new berlussimo_global ();
        $bg->objekt_auswahl_liste('?daten=mietvertrag_raus&mietvertrag_raus=mahnliste_alle');
        if (isset ($_SESSION ['objekt_id'])) {
            $ma = new mahnungen ();
            if (!isset ($_REQUEST ['pdf'])) {
                $obj_id = $_SESSION ['objekt_id'];
                $link_pdf = "<a href=\"?daten=mietvertrag_raus&mietvertrag_raus=mahnliste_alle&objekt_id=$obj_id&pdf\">Als PDF anzeigen</a>";
                echo $link_pdf;
                $ma->finde_schuldner('');
            } else {

                $ma->finde_schuldner_pdf('');
            }
        }
        $f->fieldset_ende();
        break;

    case "mahnliste_ausgezogene" :
        $f = new formular ();
        $f->fieldset("Mahnliste ex. Mieter", 'mahnliste');
        $bg = new berlussimo_global ();
        $bg->objekt_auswahl_liste('?daten=mietvertrag_raus&mietvertrag_raus=mahnliste_ausgezogene');
        if (isset ($_SESSION ['objekt_id'])) {
            $ma = new mahnungen ();
            if (!isset ($_REQUEST ['pdf'])) {
                $obj_id = $_SESSION ['objekt_id'];
                $link_pdf = "<a href=\"?daten=mietvertrag_raus&mietvertrag_raus=mahnliste_ausgezogene&objekt_id=$obj_id&pdf\">Als PDF anzeigen</a>";
                echo $link_pdf;
                $ma->finde_schuldner('ausgezogene');
            } else {
                $ma->finde_schuldner_pdf('ausgezogene');
            }
        }
        $f->fieldset_ende();
        break;

    case "guthaben_liste" :
        $f = new formular ();
        $f->fieldset("Guthaben aller Mieter", 'guthabenliste');
        $bg = new berlussimo_global ();
        $bg->objekt_auswahl_liste('?daten=mietvertrag_raus&mietvertrag_raus=guthaben_liste');

        $ma = new mahnungen ();
        $ma->finde_guthaben_mvs();
        $f->fieldset_ende();
        break;

    case "zahlungserinnerung" :
        if (!empty ($_REQUEST ['mietvertrag_id']) && empty ($_REQUEST ['submit'])) {
            $mv_id = $_REQUEST ['mietvertrag_id'];
            $f = new formular ();
            $f->erstelle_formular("Zahlungserinnerung für Mietvertrag $mv_id", '');
            // $f->fieldset("Zahlungserinnerung für Mietvertrag $mv_id", 'zahlungserinnerung');
            $datum_feld = 'document.getElementById("datum_zahlungsfrist").value';
            $js_datum = "onchange='check_datum($datum_feld)'";
            $f->text_feld('Datum Zahlungsfrist', 'datum_zahlungsfrist', '', '10', 'datum_zahlungsfrist', $js_datum);
            $g = new geldkonto_info ();
            $g->geld_konto_ermitteln('Mietvertrag', $mv_id);
            $f->send_button("submit", "Schreiben erstellen");
            $ma = new mahnungen ();
            // $f->fieldset_ende();
            $f->ende_formular();
        }
        if (!empty ($_REQUEST ['submit'])) {
            // print_r($_POST);
            $mv_id = $_REQUEST ['mietvertrag_id'];
            $fristdatum = $_REQUEST ['datum_zahlungsfrist'];
            $geldkonto_id = $_POST ['geld_konto'];
            $ma = new mahnungen ();
            $ma->zahlungserinnerung_pdf($mv_id, $fristdatum, $geldkonto_id);
        }
        break;

    case "mahnung" :
        if (!empty ($_REQUEST ['mietvertrag_id']) && empty ($_REQUEST ['submit'])) {
            $mv_id = $_REQUEST ['mietvertrag_id'];
            $f = new formular ();
            $f->erstelle_formular("Mahnung für Mietvertrag $mv_id", '');
            $datum_feld = 'document.getElementById("datum_zahlungsfrist").value';
            $js_datum = "onchange='check_datum($datum_feld)'";
            $f->text_feld('Datum Zahlungsfrist', 'datum_zahlungsfrist', '', '10', 'datum_zahlungsfrist', $js_datum);
            $f->text_feld('Mahngebühr', 'mahngebuehr', '', '10', 'mahngebuehr', '');
            $g = new geldkonto_info ();
            $g->geld_konto_ermitteln('Mietvertrag', $mv_id);
            $f->send_button("submit", "Schreiben erstellen");
            $ma = new mahnungen ();
            $f->ende_formular();
        }
        if (!empty ($_REQUEST ['submit'])) {
            // print_r($_POST);
            $mv_id = $_REQUEST ['mietvertrag_id'];
            $fristdatum = $_REQUEST ['datum_zahlungsfrist'];
            $geldkonto_id = $_POST ['geld_konto'];
            $mahngebuehr = $_POST ['mahngebuehr'];
            $ma = new mahnungen ();
            $ma->mahnung_pdf($mv_id, $fristdatum, $geldkonto_id, $mahngebuehr);
        }
        break;

    case "mietvertrag_aendern" :
        $form = new mietkonto ();
        $form->erstelle_formular("Mietvertrag ändern", NULL);
        if (!empty ($_REQUEST ['mietvertrag_id'])) {
            $mv_info = new mietvertraege ();
            $mv_info->mv_aendern_formular($_REQUEST ['mietvertrag_id']);
        } else {

            fehlermeldung_ausgeben("Mietvertrag zum ändern auswählen");
            weiterleiten_in_sec('?daten=mietvertrag_raus&mietvertrag_raus=mietvertrag_kurz"', '2');
        }
        $form->ende_formular();
        break;

    case "mv_aenderung_pruefen" :
        $form = new mietkonto ();
        $form->erstelle_formular("Mietvertrag prüfen/ändern", NULL);

        /* Ob Mieter ausgewählt wurden */
        if (is_array($_POST ['mieter_liste'])) {
            // echo "MIETER OK";
        } else {
            $error = 'Keine Mieter im Vertrag<br>';
        }
        /* Einzugsdatum */
        if (!check_datum($_POST ['datum_einzug'])) {
            $error .= 'Einzugsdatum prüfen<br>';
        } else {
            // echo "Einzugsdatum OK";
        }
        /* Auszugsdatum */
        if (!empty ($_POST ['datum_auszug'])) {
            if (!check_datum($_POST ['datum_auszug'])) {
                $error .= 'Auszugsdatum prüfen<br>';
            } else {
                // echo "AUSZUGsdatum OK";
            }
        } else {
            // echo "KEIN A DATUM";
            // $error .= 'Kein Auszugsdatum eingegeben<br>';
            $_POST ['datum_auszug'] = '0000-00-00';
        }

        if (isset ($error)) {
            echo $error;
        } else {
            echo "<p><h1>GEÄNDERTE VERTRAGSDATEN:</h1><br>";
            $einheit_kurzname = einheit_kurzname($_POST ['einheit_id']);
            $haus_id = haus_id($_POST ['einheit_id']);
            $anschrift = haus_strasse_nr($haus_id);
            echo "<b>Einheit:</b> $einheit_kurzname<br>$anschrift<br>";
            $mv_info = new mietvertraege ();
            echo "<hr><b>Mieter:</b><br>";
            $mv_info->mv_personen_anzeigen_form($_POST ['mieter_liste']);
            echo "<hr>Einzug: $_POST[datum_einzug]<br>";
            if ($_POST ['datum_auszug'] == '0000-00-00') {
                echo "Auszug: unbefristet<br>";
            } else {
                echo "Auszug: $_POST[datum_auszug]<br>";
            }

            $form->hidden_feld('einheit_id', $_POST ['einheit_id']);
            $form->hidden_feld('mietvertrag_id', $_POST ['mietvertrag_id']);
            $form->hidden_feld('mietvertrag_dat', $_POST ['mietvertrag_dat']);
            $form->hidden_feld('datum_einzug', $_POST ['datum_einzug']);
            $form->hidden_feld('datum_auszug', $_POST ['datum_auszug']);

            for ($a = 0; $a < count($_POST ['mieter_liste']); $a++) {
                $person_id = $_POST ['mieter_liste'] [$a];
                $form->hidden_feld('mieter_liste[]', $person_id);
            }
            $form->hidden_feld('mietvertrag_raus', 'mv_geaendert_speichern');
            $form->send_button('btn_mv_aendern', 'Änderungen speichern');
        }
        $form->ende_formular();
        break;

    case "letzte_auszuege" :
        $f = new formular ();
        $link = '?daten=mietvertrag_raus&mietvertrag_raus=letzte_auszuege';
        $b = new berlussimo_global ();
        $b->objekt_auswahl_liste($link);
        $m = new mietvertraege ();
        $objekt_id = $_SESSION ['objekt_id'];
        $jahr = $_REQUEST ['jahr'];
        $monat = $_REQUEST ['monat'];
        $f->fieldset("Letzte Auszüge", 'l_auszuege');
        if (!empty ($objekt_id)) {
            if (empty ($jahr)) {
                $jahr = date("Y");
            }

            $b->monate_jahres_links($jahr, $link);
            $m->ausgezogene_mieter_anzeigen($objekt_id, $jahr, $monat);
        }
        $f->fieldset_ende();
        break;

    case "letzte_einzuege" :
        $f = new formular ();
        $link = '?daten=mietvertrag_raus&mietvertrag_raus=letzte_einzuege';
        $b = new berlussimo_global ();
        $b->objekt_auswahl_liste($link);
        $m = new mietvertraege ();
        $objekt_id = $_SESSION ['objekt_id'];
        $jahr = $_REQUEST ['jahr'];
        $monat = $_REQUEST ['monat'];
        $f->fieldset("Letzte Einzüge", 'l_einzuege');
        if (!empty ($objekt_id)) {
            if (empty ($jahr)) {
                $jahr = date("Y");
            }

            $b->monate_jahres_links($jahr, $link);
            $m->eingezogene_mieter_anzeigen($objekt_id, $jahr, $monat);
        }
        $f->fieldset_ende();
        break;

    case "alle_letzten_auszuege" :
        $f = new formular ();
        $b = new berlussimo_global ();
        $link = '?daten=mietvertrag_raus&mietvertrag_raus=alle_letzten_auszuege';
        $m = new mietvertraege ();
        $jahr = $_REQUEST ['jahr'];
        $monat = $_REQUEST ['monat'];
        $f->fieldset("Alle Auszüge", 'l_auszuege');
        if (empty ($jahr)) {
            $jahr = date("Y");
        }

        $b->monate_jahres_links($jahr, $link);
        $m->alle_ausgezogene_mieter_anzeigen($jahr, $monat);
        $f->fieldset_ende();
        break;

    case "alle_letzten_einzuege" :
        $f = new formular ();
        $b = new berlussimo_global ();
        $link = '?daten=mietvertrag_raus&mietvertrag_raus=alle_letzten_einzuege';
        $m = new mietvertraege ();
        $jahr = $_REQUEST ['jahr'];
        $monat = $_REQUEST ['monat'];
        $f->fieldset("Alle Einzüge", 'l_einzuege');
        if (empty ($jahr)) {
            $jahr = date("Y");
        }

        $b->monate_jahres_links($jahr, $link);
        $m->alle_eingezogene_mieter_anzeigen($jahr, $monat);
        $f->fieldset_ende();
        break;

    case "abnahmeprotokoll" :
        if (isset ($_REQUEST ['mv_id']) && !empty ($_REQUEST ['mv_id'])) {
            $pdf = new Cezpdf ('a4', 'portrait');
            $bpdf = new b_pdf ();
            $bpdf->b_header($pdf, 'Partner', $_SESSION ['partner_id'], 'portrait', 'Helvetica.afm', 6);

            $mvv = new mietvertraege ();
            $mvv->get_mietvertrag_infos_aktuell($_REQUEST ['mv_id']);

            if (isset ($_REQUEST ['einzug'])) {
                $bpdf->pdf_abnahmeprotokoll($pdf, $_REQUEST ['mv_id'], 'einzug'); // EINZUG
                $dateiname = $mvv->einheit_kurzname . "_Einzug_Protokoll.pdf";
            } else {
                $bpdf->pdf_abnahmeprotokoll($pdf, $_REQUEST ['mv_id'], null); // AUSZUG
                $dateiname = $mvv->einheit_kurzname . "_Auszug_Protokoll.pdf";
            }

            if (isset ($_REQUEST ['einzug'])) {
                $pdf->ezNewPage();
                $bpdf->pdf_heizungabnahmeprotokoll($pdf, $_REQUEST ['mv_id'], 'einzug');

                $pdf->ezNewPage();
                $bpdf->pdf_einauszugsbestaetigung($pdf, $_REQUEST ['mv_id'], 0);
            } else {
                $pdf->ezNewPage();
                $bpdf->pdf_heizungabnahmeprotokoll($pdf, $_REQUEST ['mv_id']);

                $pdf->ezNewPage();
                $bpdf->pdf_einauszugsbestaetigung($pdf, $_REQUEST ['mv_id'], 1);
            }

            ob_clean();
            $datum_h = date("Y-m-d");
            $pdf_opt ['Content-Disposition'] = $datum_h . "_" . $dateiname;
            $pdf->ezStream($pdf_opt);
        } else {
            fehlermeldung_ausgeben("Mietvertrag wählen!");
        }

        break;

    case "alle_letzten_auszuege_pdf" :
        $f = new formular ();
        $m = new mietvertraege ();
        $jahr = $_REQUEST ['jahr'];
        $monat = $_REQUEST ['monat'];
        if (empty ($jahr)) {
            $jahr = date("Y");
        }
        $m->alle_ausgezogenen_pdf($jahr, $monat);
        break;

    case "alle_letzten_einzuege_pdf" :
        $f = new formular ();
        $m = new mietvertraege ();
        $jahr = $_REQUEST ['jahr'];
        $monat = $_REQUEST ['monat'];
        if (empty ($jahr)) {
            $jahr = date("Y");
        }
        $m->alle_eingezogenen_pdf($jahr, $monat);
        break;

    case "saldenliste" :
        $form = new mietkonto ();
        $form->erstelle_formular("Saldenliste", NULL);
        $mv_info = new mietvertraege ();
        $monat = $_REQUEST ['monat'];
        $jahr = $_REQUEST ['jahr'];
        if (!$monat) {
            $monat = date("m");
        }
        if (!$jahr) {
            $jahr = date("Y");
        }
        $mv_info->saldenliste_mv($monat, $jahr);
        $form->ende_formular();
        break;

    case "nebenkosten_ok" :
        $form = new mietkonto ();
        $form->erstelle_formular("Nebenkosten", NULL);
        $mv_info = new mietvertraege ();
        $monat = $_REQUEST ['monat'];
        $jahr = $_REQUEST ['jahr'];
        if (!$monat) {
            $monat = date("m");
        }
        if (!$jahr) {
            $jahr = date("Y");
        }
        $mv_info->nebenkosten($monat, $jahr);
        $form->ende_formular();
        break;

    case "nebenkosten" :

        $form = new mietkonto ();
        $form->erstelle_formular("Nebenkosten", NULL);

        $jahr = $_REQUEST ["jahr"];
        if (empty ($jahr)) {
            $jahr = date("Y");
        } else {
            if (strlen($jahr) < 4) {
                $jahr = date("Y");
            }
        }

        $bg = new berlussimo_global ();
        $link = "?daten=mietvertrag_raus&mietvertrag_raus=nebenkosten";
        $bg->objekt_auswahl_liste($link);
        $bg->jahres_links($jahr, $link);
        if (isset ($_SESSION ['objekt_id'])) {
            $objekt_id = $_SESSION ['objekt_id'];
        }
        if (empty ($_SESSION ['objekt_id'])) {
            die ('Objekt wählen');
        }

        $link_pdf = "<a href=\"?daten=mietvertrag_raus&mietvertrag_raus=nebenkosten_pdf&jahr=$jahr\"><b>PDF-Datei</b></a>";
        echo '<hr>' . $link_pdf . '<hr>';

        $mv_info = new mietvertraege ();
        $mv_info->nebenkosten($_SESSION ['objekt_id'], $jahr);
        $form->ende_formular();
        break;

    case "nebenkosten_pdf" :
        $jahr = $_REQUEST ["jahr"];
        if (empty ($jahr)) {
            $jahr = date("Y");
        } else {
            if (strlen($jahr) < 4) {
                $jahr = date("Y");
            }
        }

        if (isset ($_SESSION ['objekt_id'])) {
            $objekt_id = $_SESSION ['objekt_id'];
        }
        if (empty ($_SESSION ['objekt_id'])) {
            die ('Objekt wählen');
        }

        $mv_info = new mietvertraege ();
        $mv_info->nebenkosten_pdf($_SESSION ['objekt_id'], $jahr);
        break;

    case "nebenkosten_pdf_zs" :
        $jahr = $_REQUEST ["jahr"];
        if (empty ($jahr)) {
            $jahr = date("Y");
        } else {
            if (strlen($jahr) < 4) {
                $jahr = date("Y");
            }
        }

        if (isset ($_SESSION ['objekt_id'])) {
            $objekt_id = $_SESSION ['objekt_id'];
        }
        if (empty ($_SESSION ['objekt_id'])) {
            die ('Objekt wählen');
        }

        $mv_info = new mietvertraege ();
        $mv_info->nebenkosten_pdf_zs_ant($_SESSION ['objekt_id'], $jahr);
        break;

    case "nebenkosten_pdf_OK" :
        $mv_info = new mietvertraege ();
        $monat = $_REQUEST ['monat'];
        $jahr = $_REQUEST ['jahr'];
        if (!$monat) {
            $monat = date("m");
        }
        if (!$jahr) {
            $jahr = date("Y");
        }
        $mv_info->nebenkosten_pdf($monat, $jahr);
        break;

    case "saldenliste_pdf" :
        $mv_info = new mietvertraege ();
        $monat = $_REQUEST ['monat'];
        $jahr = $_REQUEST ['jahr'];
        if (!$monat) {
            $monat = date("m");
        }
        if (!$jahr) {
            $jahr = date("Y");
        }
        $mv_info->saldenliste_mv_pdf($monat, $jahr);
        break;

    case "mv_loeschen" :
        if (!empty ($_REQUEST ['mv_id'])) {
            $mv_id = $_REQUEST ['mv_id'];
            $mv = new mietvertraege ();
            $mv->form_mietvertrag_loeschen($mv_id);
        } else {
            echo "Mietvertrag wählen!";
        }
        break;

    case "erinnern_mehrere" :
        $mahnliste = $_REQUEST ['mahnliste'];
        $fristdatum = $_REQUEST ['datum'];
        $geldkonto_id = $_POST ['geld_konto'];
        $ma = new mahnungen ();
        $ma->zahlungserinnerung_pdf_mehrere($mahnliste, $fristdatum, $geldkonto_id);
        break;

    case "mahnen_mehrere" :
        $mahnliste = $_REQUEST ['mahnliste'];
        $fristdatum = $_REQUEST ['datum'];
        $geldkonto_id = $_POST ['geld_konto'];
        $mahngebuehr = $_POST ['mahngebuehr'];
        $ma = new mahnungen ();
        $ma->mahnung_pdf_mehrere($mahnliste, $fristdatum, $geldkonto_id, $mahngebuehr);
        break;
} // end switch
function objekt_auswahl_liste($link)
{
    if (isset ($_REQUEST ['objekt_id']) && !empty ($_REQUEST ['objekt_id'])) {
        $_SESSION ['objekt_id'] = $_REQUEST ['objekt_id'];
    }

    echo "<div class=\"objekt_auswahl\">";
    $mieten = new mietkonto ();
    $mieten->erstelle_formular("Objekt auswählen...", NULL);

    if (isset ($_SESSION ['objekt_id'])) {
        $objekt_kurzname = new objekt ();
        $objekt_kurzname->get_objekt_name($_SESSION ['objekt_id']);
        echo "<p>&nbsp;<b>Ausgewähltes Objekt</b> -> $objekt_kurzname->objekt_name ->";
    } else {
        echo "<p>&nbsp;<b>Objekt auswählen</b>";
    }

    $objekte = new objekt ();
    $objekte_arr = $objekte->liste_aller_objekte();
    $anzahl_objekte = count($objekte_arr);

    for ($i = 0; $i <= $anzahl_objekte; $i++) {
        echo "<a class=\"objekt_auswahl_buchung\" href=\"$link&objekt_id=" . $objekte_arr [$i] ['OBJEKT_ID'] . "\">" . $objekte_arr [$i] ['OBJEKT_KURZNAME'] . "</a>&nbsp;";
        echo "</div>";
    }
}

function leerstand_finden($objekt_id)
{
    $result = mysql_query("SELECT OBJEKT_KURZNAME, EINHEIT_ID, EINHEIT_KURZNAME, HAUS_STRASSE, HAUS_NUMMER
FROM `EINHEIT`
RIGHT JOIN (
HAUS, OBJEKT
) ON ( EINHEIT.HAUS_ID = HAUS.HAUS_ID && HAUS.OBJEKT_ID = OBJEKT.OBJEKT_ID && OBJEKT.OBJEKT_ID='$objekt_id' )
WHERE EINHEIT_ID NOT
IN (

SELECT EINHEIT_ID
FROM MIETVERTRAG
WHERE MIETVERTRAG_AKTUELL = '1' && ( MIETVERTRAG_BIS > CURdate( )
OR MIETVERTRAG_BIS = '0000-00-00' )
)
ORDER BY EINHEIT_KURZNAME ASC");

    while ($row = mysql_fetch_assoc($result))
        $my_arr [] = $row;
    return $my_arr;
}

function dropdown_leerstaende($objekt_id, $name, $label)
{
    $leerstand = leerstand_finden($objekt_id);
    // print_r($leerstand);
    echo "<label for=\"$name\">$label</label><select name=\"$name\" id=\"$name\">";
    for ($a = 0; $a < count($leerstand); $a++) {
        $einheit_id = $leerstand [$a] ['EINHEIT_ID'];
        $einheit_kurzname = $leerstand [$a] ['OBJEKT_KURZNAME'];
        echo "<option value=\"$einheit_id\">$einheit_kurzname</option>";
    }
    echo "</select>";
}

function mietvertrag_beenden_form_alt11($mietvertrag_id)
{
    if (!isset ($_REQUEST ['submit_mv_beenden'])) {
        $db_abfrage = "SELECT MIETVERTRAG_DAT, MIETVERTRAG_ID, MIETVERTRAG_VON, MIETVERTRAG_BIS, EINHEIT_ID FROM MIETVERTRAG where MIETVERTRAG_ID='$mietvertrag_id' && MIETVERTRAG_AKTUELL='1' ORDER BY MIETVERTRAG_DAT DESC LIMIT 0,1";
        $resultat = mysql_query($db_abfrage) or die (mysql_error());
        erstelle_formular(NULL, NULL); // name, action
        while (list ($MIETVERTRAG_DAT, $MIETVERTRAG_ID, $MIETVERTRAG_VON, $MIETVERTRAG_BIS, $EINHEIT_ID) = mysql_fetch_row($resultat)) {
            $MIETVERTRAG_VON = date_mysql2german($MIETVERTRAG_VON);
            $MIETVERTRAG_BIS = date_mysql2german($MIETVERTRAG_BIS);
            warnung_ausgeben("<tr><td colspan=2><h1>Vertrag für die Einheit $einheit_kurzname beenden:\n</h1></td></tr>\n");
            erstelle_eingabefeld("Mietvertragsende eintragen", "MIETVERTRAG_BIS", "", "10");
            erstelle_hiddenfeld("MIETVERTRAG_DAT", $MIETVERTRAG_DAT);
            erstelle_hiddenfeld("EINHEIT_ID", $EINHEIT_ID);
        } // while end
        erstelle_submit_button("submit_mv_beenden", "Endgültig Beenden"); // name, wert
        ende_formular();
    } // end if
    if (isset ($_REQUEST ['submit_mv_beenden'])) {
        if (empty ($_REQUEST ['MIETVERTRAG_BIS'])) {
            echo "datum eingeben";
        } else {
            // echo "$_REQUEST[MIETVERTRAG_BIS] datum prüfen, wenn ok ändern<br>$_REQUEST[MIETVERTRAG_DAT]";
            mietvertrag_beenden($_REQUEST ['MIETVERTRAG_DAT'], $_REQUEST ['MIETVERTRAG_BIS']);
            $einheit_name = einheit_kurzname($_REQUEST ['EINHEIT_ID']);
            hinweis_ausgeben("Mietvertrag für die Einheit $einheit_name wird zum $_REQUEST[MIETVERTRAG_BIS] beendet!");
        }
    }
}

function mietvertrag_beenden_form($mietvertrag_id)
{
    $m = new mietvertraege ();
    $m->mietvertrag_beenden_form($mietvertrag_id);
}

function mietvertrag_aendern_form($mietvertrag_id)
{
    if (!isset ($_REQUEST ['submit_mv_beenden']) && !isset ($_REQUEST ['submit_mv_aendern']) && !isset ($_REQUEST ['submit_mv_pruefen'])) {
        $db_abfrage = "SELECT MIETVERTRAG_DAT, MIETVERTRAG_ID, MIETVERTRAG_VON, MIETVERTRAG_BIS, EINHEIT_ID FROM MIETVERTRAG where MIETVERTRAG_ID='$mietvertrag_id' && MIETVERTRAG_AKTUELL='1' ORDER BY MIETVERTRAG_DAT DESC LIMIT 0,1";
        $resultat = mysql_query($db_abfrage) or die (mysql_error());
        erstelle_formular(NULL, NULL); // name, action
        while (list ($MIETVERTRAG_DAT, $MIETVERTRAG_ID, $MIETVERTRAG_VON, $MIETVERTRAG_BIS, $EINHEIT_ID) = mysql_fetch_row($resultat)) {
            $form = new mietkonto ();

            $MIETVERTRAG_VON = date_mysql2german($MIETVERTRAG_VON);
            $MIETVERTRAG_BIS = date_mysql2german($MIETVERTRAG_BIS);
            warnung_ausgeben("<tr><td colspan=2><h1>Mietvertrag ändern/korrigieren:\n</h1></td></tr>\n");
            $form->mieter_infos_vom_mv($mietvertrag_id);
            warnung_ausgeben("<tr><td colspan=2><b>Bitte wählen Sie die Personen aus!</b></td></tr>\n");
            erstelle_eingabefeld("Einzugsdatum ändern", "MIETVERTRAG_VON", "$MIETVERTRAG_VON", "10");
            erstelle_eingabefeld("Auszugsdatum ändern", "MIETVERTRAG_BIS", "$MIETVERTRAG_BIS", "10");
            erstelle_hiddenfeld("MIETVERTRAG_DAT", $MIETVERTRAG_DAT);
            erstelle_hiddenfeld("EINHEIT_ID", $EINHEIT_ID);
        } // while end
        personen_liste_multi();
        erstelle_submit_button("submit_mv_aendern", "ändern"); // name, wert
        ende_formular();
    } // end if
    if (isset ($_REQUEST ['submit_mv_aendern'])) {
        if (empty ($_REQUEST ['MIETVERTRAG_VON'])) {
            echo "Eihnzugsdatum eingeben";
        } elseif (empty ($_REQUEST ['MIETVERTRAG_BIS'])) {
            echo "Auszugsdatum eingeben";
        } elseif (empty ($_REQUEST ['PERSON_ID'])) {
            echo "Personen zum Vetrag auswählen!";
        } else {
            // echo "$_REQUEST[MIETVERTRAG_BIS] datum prüfen, wenn ok ändern<br>$_REQUEST[MIETVERTRAG_DAT]";
            erstelle_formular(NULL, NULL); // name, action
            $einheit_kurzname = einheit_kurzname($_REQUEST ['EINHEIT_ID']);
            $MIETVERTRAG_VON = $_REQUEST ['MIETVERTRAG_VON'];
            $MIETVERTRAG_BIS = $_REQUEST ['MIETVERTRAG_BIS'];
            warnung_ausgeben("<tr><td colspan=2><h1>Der Mietvertrag für die Einheit $einheit_kurzname wird wie folgt geändert:\n</h1></td></tr>\n");
            for ($i = 0; $i < count($_REQUEST ['PERSON_ID']); $i++) {
                $mietername = personen_name($_REQUEST ['PERSON_ID'] [$i]);
                echo "<tr><td>Mieter:</td><td><b>$mietername</b></td></tr>";
                erstelle_hiddenfeld("PERSON_ID[]", "" . $_REQUEST ['PERSON_ID'] [$i] . "");
            }
            echo "<tr><td>Einzugsdatum:</td><td><b>$_REQUEST[MIETVERTRAG_VON]</b></td></tr>";
            if ($_REQUEST ['MIETVERTRAG_BIS'] != '00.00.0000') {
                echo "<tr><td>Auszugsdatum:</td><td><b>$_REQUEST[MIETVERTRAG_BIS]</b></td></tr>";
            } else {
                echo "<tr><td>Auszugsdatum:</td><td><b>unbefristet</td></tr>";
            }

            // for($a=0;$a<$anzahl_partner;$a++){
            // erstelle_hiddenfeld("PERSON_ID[]", "".$_REQUEST[PERSON_ID][$a]."");
            // }

            erstelle_hiddenfeld("MIETVERTRAG_VON", $MIETVERTRAG_VON);
            erstelle_hiddenfeld("MIETVERTRAG_BIS", $MIETVERTRAG_BIS);
            erstelle_hiddenfeld("MIETVERTRAG_DAT", $_REQUEST ['MIETVERTRAG_DAT']);
            erstelle_hiddenfeld("EINHEIT_ID", $_REQUEST ['EINHEIT_ID']);
            echo "<tr><td>";
            hinweis_ausgeben("Möchten Sie die Vertragsänderungen übernehmen?");
            echo "</td></tr>";
            erstelle_submit_button("submit_mv_pruefen", "Speichern");
        }
    }
    if (isset ($_REQUEST ['submit_mv_pruefen'])) {
        mietvertrag_aktualisieren($_REQUEST ['MIETVERTRAG_DAT'], $_REQUEST ['MIETVERTRAG_BIS'], $_REQUEST ['MIETVERTRAG_VON']);
        weiterleiten("?daten=uebersicht&anzeigen=einheit&einheit_id=$_REQUEST[EINHEIT_ID]");
    }
}

function mietvertrag_beenden($mietvertrag_dat, $mietvertrag_bis)
{
    $mietvertrag_bis = date_german2mysql($mietvertrag_bis);
    $akt_einheit_id = einheit_id_by_mietvertrag($mietvertrag_dat);
    $dat_alt = $mietvertrag_dat;
    $db_abfrage = "UPDATE MIETVERTRAG SET MIETVERTRAG_AKTUELL='0' where MIETVERTRAG_DAT='$mietvertrag_dat'";
    $resultat = mysql_query($db_abfrage) or die (mysql_error()); // aktuell auf 0 gesetzt

    $db_abfrage1 = "SELECT MIETVERTRAG_ID, MIETVERTRAG_VON, EINHEIT_ID FROM MIETVERTRAG where MIETVERTRAG_DAT='$mietvertrag_dat' LIMIT 0,1";
    $resultat1 = mysql_query($db_abfrage1) or die (mysql_error());
    while (list ($MIETVERTRAG_ID, $MIETVERTRAG_VON, $EINHEIT_ID) = mysql_fetch_row($resultat1)) {
        $db_abfrage2 = "INSERT INTO MIETVERTRAG (`MIETVERTRAG_DAT`, `MIETVERTRAG_ID`, `MIETVERTRAG_VON`, `MIETVERTRAG_BIS`, `EINHEIT_ID`, `MIETVERTRAG_AKTUELL`) VALUES (NULL, '$MIETVERTRAG_ID', '$MIETVERTRAG_VON', '$mietvertrag_bis', '$EINHEIT_ID', '1')";
        $resultat2 = mysql_query($db_abfrage2) or die (mysql_error()); // Neuer Datensatz mit aktuellem Datum
    } // while end
    // protokollieren
    $db_abfrage3 = "SELECT MIETVERTRAG_DAT FROM MIETVERTRAG where MIETVERTRAG_BIS='$mietvertrag_bis' && MIETVERTRAG_AKTUELL='1' ORDER BY MIETVERTRAG_DAT DESC";
    $resultat3 = mysql_query($db_abfrage3) or die (mysql_error());
    while (list ($MIETVERTRAG_DAT) = mysql_fetch_row($resultat3)) {
        $dat_neu = $MIETVERTRAG_DAT;
        protokollieren('MIETVERTRAG', $dat_neu, $dat_alt);
    }
    weiterleiten("?daten=uebersicht&anzeigen=einheit&einheit_id=$akt_einheit_id");
}

function mietvertrag_aktualisieren($mietvertrag_dat, $mietvertrag_bis, $mietvertrag_von)
{
    $mietvertrag_bis = date_german2mysql($mietvertrag_bis);
    $mietvertrag_von = date_german2mysql($mietvertrag_von);
    $dat_alt = $mietvertrag_dat;
    $db_abfrage = "UPDATE MIETVERTRAG SET MIETVERTRAG_AKTUELL='0' where MIETVERTRAG_DAT='$mietvertrag_dat'";
    $resultat = mysql_query($db_abfrage) or die (mysql_error()); // aktuell auf 0 gesetzt

    $mietvertrag_id_alt = mietvertrag_id_by_dat($mietvertrag_dat);
    $db_abfrage = "UPDATE PERSON_MIETVERTRAG SET PERSON_MIETVERTRAG_AKTUELL='0' where PERSON_MIETVERTRAG_MIETVERTRAG_ID='$mietvertrag_id_alt'";
    echo "<br>UPDATE PERSON_MIETVERTRAG SET PERSON_MIETVERTRAG_AKTUELL='0' where PERSON_MIETVERTRAG_MIETVERTRAG_ID='$mietvertrag_id_alt'";
    $resultat = mysql_query($db_abfrage) or die (mysql_error()); // personen zu MV gelöscht bzw auf 0 gesetzt

    $db_abfrage1 = "SELECT MIETVERTRAG_ID, MIETVERTRAG_VON, EINHEIT_ID FROM MIETVERTRAG where MIETVERTRAG_DAT='$mietvertrag_dat' LIMIT 0,1";
    echo "<br>SELECT MIETVERTRAG_ID, MIETVERTRAG_VON, EINHEIT_ID FROM MIETVERTRAG where MIETVERTRAG_DAT='$mietvertrag_dat' LIMIT 0,1";
    $resultat1 = mysql_query($db_abfrage1) or die (mysql_error());
    while (list ($MIETVERTRAG_ID, $MIETVERTRAG_VON, $EINHEIT_ID) = mysql_fetch_row($resultat1)) {
        $db_abfrage2 = "INSERT INTO MIETVERTRAG (`MIETVERTRAG_DAT`, `MIETVERTRAG_ID`, `MIETVERTRAG_VON`, `MIETVERTRAG_BIS`, `EINHEIT_ID`, `MIETVERTRAG_AKTUELL`) VALUES (NULL, '$mietvertrag_id_alt', '$mietvertrag_von', '$mietvertrag_bis', '$EINHEIT_ID', '1')";
        echo "<br>INSERT INTO MIETVERTRAG (`MIETVERTRAG_DAT`, `MIETVERTRAG_ID`, `MIETVERTRAG_VON`, `MIETVERTRAG_BIS`, `EINHEIT_ID`, `MIETVERTRAG_AKTUELL`) VALUES (NULL, '$MIETVERTRAG_ID', '$mietvertrag_von', '$mietvertrag_bis', '$EINHEIT_ID', '1')";
        $resultat2 = mysql_query($db_abfrage2) or die (mysql_error()); // Neuer Datensatz mit aktuellem Datum
    } // while end
    // protokollieren
    $db_abfrage3 = "SELECT MIETVERTRAG_DAT FROM MIETVERTRAG where MIETVERTRAG_VON='$mietvertrag_von' && MIETVERTRAG_BIS='$mietvertrag_bis' && MIETVERTRAG_AKTUELL='1' ORDER BY MIETVERTRAG_DAT DESC";
    echo "<br>SELECT MIETVERTRAG_DAT FROM MIETVERTRAG where MIETVERTRAG_VON='$mietvertrag_von' && MIETVERTRAG_BIS='$mietvertrag_bis' && MIETVERTRAG_AKTUELL='1' ORDER BY MIETVERTRAG_DAT DESC";
    $resultat3 = mysql_query($db_abfrage3) or die (mysql_error());
    while (list ($MIETVERTRAG_DAT) = mysql_fetch_row($resultat3)) {
        $dat_neu = $MIETVERTRAG_DAT;
        protokollieren('MIETVERTRAG', $dat_neu, $dat_alt);
    }

    $zugewiesene_vetrags_id = mietvertrag_by_einheit($_REQUEST ['einheit_id']);
    $anzahl_partner = count($_REQUEST ['PERSON_ID']);
    for ($a = 0; $a < $anzahl_partner; $a++) {
        // echo "".$_REQUEST[PERSON_ID][$a]." <br>";
        // print_r($_REQUEST[PERSON_ID][$a]);
        person_zu_mietvertrag($_REQUEST ['PERSON_ID'] [$a], $zugewiesene_vetrags_id);
    }
}

function mietvertrag_kurz($einheit_id)
{
    if (empty ($einheit_id)) {
        // $db_abfrage = "SELECT DISTINCT MIETVERTRAG.MIETVERTRAG_ID, MIETVERTRAG.MIETVERTRAG_VON, MIETVERTRAG.MIETVERTRAG_BIS, MIETVERTRAG.EINHEIT_ID, EINHEIT.EINHEIT_KURZNAME FROM MIETVERTRAG, EINHEIT WHERE MIETVERTRAG_AKTUELL='1' && EINHEIT.EINHEIT_AKTUELL='1' && EINHEIT.EINHEIT_ID = MIETVERTRAG.EINHEIT_ID ORDER BY EINHEIT.EINHEIT_KURZNAME ASC";

        $db_abfrage = "SELECT MIETVERTRAG.MIETVERTRAG_ID, MIETVERTRAG.MIETVERTRAG_VON, MIETVERTRAG.MIETVERTRAG_BIS, MIETVERTRAG.EINHEIT_ID, EINHEIT.EINHEIT_KURZNAME FROM MIETVERTRAG JOIN(EINHEIT) ON (EINHEIT.EINHEIT_ID = MIETVERTRAG.EINHEIT_ID) WHERE MIETVERTRAG_AKTUELL='1' && EINHEIT.EINHEIT_AKTUELL='1' ORDER BY EINHEIT.EINHEIT_KURZNAME,MIETVERTRAG.MIETVERTRAG_VON  ASC";
    } else {
        $db_abfrage = "SELECT MIETVERTRAG_ID, MIETVERTRAG_VON, MIETVERTRAG_BIS, EINHEIT_ID FROM MIETVERTRAG where EINHEIT_ID='$einheit_id' && MIETVERTRAG_AKTUELL='1' ORDER BY MIETVERTRAG_VON DESC";
    }

    $resultat = mysql_query($db_abfrage) or die (mysql_error());

    $numrows = mysql_numrows($resultat);
    if ($numrows < 1) {
        echo "<h1><b>Keine Mietverträge zur Einheit $einheit_id vorhanden!!!</b></h1>";
    } else {
        echo "<table width=100%>\n";
        echo "<tr class=\"feldernamen\"><td colspan=5>Alle Mietverträge</td></tr>\n";
        echo "<tr class=\"feldernamen\"><td width=100>EINHEIT</td><td width=300>MIETER</td><td width=85>VON</td><td width=80>BIS</td><td>Optionen</td></tr>\n";
        echo "</table>\n";
        iframe_start();
        echo "<table width=100%>\n";
        $counter = 0;
        while (list ($MIETVERTRAG_ID, $MIETVERTRAG_VON, $MIETVERTRAG_BIS, $EINHEIT_ID) = mysql_fetch_row($resultat)) {
            $counter++;
            $datum_heute = date("Y-m-d");
            if (($MIETVERTRAG_BIS > $datum_heute) or ($MIETVERTRAG_BIS == "0000-00-00")) {
                $beenden_link = "<a href=\"?daten=mietvertrag_raus&mietvertrag_raus=mietvertrag_beenden&mietvertrag_id=$MIETVERTRAG_ID\">Beenden</a>";
                $aendern_link = "<a href=\"?daten=mietvertrag_raus&mietvertrag_raus=mietvertrag_aendern&mietvertrag_id=$MIETVERTRAG_ID\">Ändern</a>";
            } else {
                $beenden_link = "Abgelaufen";
                // $aendern_link = "k.Ä.";
                $aendern_link = "<a href=\"?daten=mietvertrag_raus&mietvertrag_raus=mietvertrag_aendern&mietvertrag_id=$MIETVERTRAG_ID\">Ändern</a>";
            }
            $MIETVERTRAG_BIS = date_mysql2german($MIETVERTRAG_BIS);
            $MIETVERTRAG_VON = date_mysql2german($MIETVERTRAG_VON);
            $mieter_im_vetrag = anzahl_mieter_im_vertrag($MIETVERTRAG_ID);
            $einheit_kurzname = einheit_kurzname($EINHEIT_ID);
            $detail_check = detail_check("MIETVERTRAG", $MIETVERTRAG_ID);
            $mietkonto_link = "<a href=\"?daten=mietkonten_blatt&anzeigen=mietkonto_uebersicht_detailiert&mietvertrag_id=$MIETVERTRAG_ID\">MIETKONTO</a>";
            $miete_aendern = "<a href=\"?daten=miete_definieren&option=miethoehe&mietvertrag_id=$MIETVERTRAG_ID\">MIETHÖHE</a>";
            $einheit_link = "<a href=\"?daten=uebersicht&anzeigen=einheit&einheit_id=$EINHEIT_ID\">$einheit_kurzname</a>";
            $mv_loeschen_link = "<a href=\"?daten=mietvertrag_raus&mietvertrag_raus=mv_loeschen&mv_id=$MIETVERTRAG_ID\">MV löschen</a>";

            if ($detail_check > 0) {
                $detail_link = "<a class=\"table_links\" href=\"?daten=details&option=details_anzeigen&detail_tabelle=MIETVERTRAG&detail_id=$MIETVERTRAG_ID\">Details</a>";
            } else {
                $detail_link = "<a class=\"table_links\" href=\"?daten=details&option=details_hinzu&detail_tabelle=MIETVERTRAG&detail_id=$MIETVERTRAG_ID\">Neues Detail</a>";
            }
            if ($counter == 1) {
                echo "<tr class=\"zeile1\"><td width=100>$einheit_link $mietkonto_link $miete_aendern</td><td width=300>($mieter_im_vetrag)";
                echo mieterid_zum_vertrag($MIETVERTRAG_ID);
                echo "</td><td width=80>$MIETVERTRAG_VON</td><td width=80>$MIETVERTRAG_BIS</td><td>$detail_link</td><td>$beenden_link $aendern_link $mv_loeschen_link</td></tr>";
            }
            if ($counter == 2) {
                echo "<tr class=\"zeile2\"><td width=100>$einheit_link $mietkonto_link $miete_aendern</td><td width=300>($mieter_im_vetrag)";
                echo mieterid_zum_vertrag($MIETVERTRAG_ID);
                echo "</td><td width=80>$MIETVERTRAG_VON</td><td width=80>$MIETVERTRAG_BIS</td><td>$detail_link</td><td>$beenden_link $aendern_link $mv_loeschen_link</td></tr>";
                $counter = 0;
            }
        }
        echo "</table>";
        iframe_end();
    }
}

function mietvertrag_abgelaufen($einheit_id)
{
    if (empty ($einheit_id)) {
        $datum_heute = date("Y-m-d");
        $db_abfrage = "SELECT MIETVERTRAG.MIETVERTRAG_ID, MIETVERTRAG.MIETVERTRAG_VON, MIETVERTRAG.MIETVERTRAG_BIS, MIETVERTRAG.EINHEIT_ID, EINHEIT.EINHEIT_KURZNAME FROM MIETVERTRAG JOIN(EINHEIT) ON (EINHEIT.EINHEIT_ID = MIETVERTRAG.EINHEIT_ID) WHERE MIETVERTRAG_AKTUELL='1' && EINHEIT.EINHEIT_AKTUELL='1' && (MIETVERTRAG.MIETVERTRAG_BIS!='0000-00-00' && MIETVERTRAG.MIETVERTRAG_BIS<'$datum_heute') ORDER BY EINHEIT.EINHEIT_KURZNAME ASC,MIETVERTRAG.MIETVERTRAG_BIS  DESC";
    } else {
        $db_abfrage = "SELECT MIETVERTRAG_ID, MIETVERTRAG_VON, MIETVERTRAG_BIS, EINHEIT_ID FROM MIETVERTRAG where EINHEIT_ID='$einheit_id' && MIETVERTRAG_AKTUELL='1'";
    }

    $resultat = mysql_query($db_abfrage) or die (mysql_error());

    $numrows = mysql_numrows($resultat);
    if ($numrows < 1) {
        echo "<h1><b>Keine Mietverträge zur Einheit $einheit_id vorhanden!!!</b></h1>";
    } else {
        echo "<table width=100%>\n";
        echo "<tr class=\"feldernamen\"><td colspan=5>Alle Mietverträge</td></tr>\n";
        echo "<tr class=\"feldernamen\"><td width=100>EINHEIT</td><td width=300>MIETER</td><td width=85>VON</td><td width=80>BIS</td><td>Optionen</td></tr>\n";
        echo "</table>\n";
        iframe_start();
        echo "<table width=100%>\n";
        $counter = 0;
        while (list ($MIETVERTRAG_ID, $MIETVERTRAG_VON, $MIETVERTRAG_BIS, $EINHEIT_ID) = mysql_fetch_row($resultat)) {
            $counter++;
            $datum_heute = date("Y-m-d");
            if (($MIETVERTRAG_BIS > $datum_heute) or ($MIETVERTRAG_BIS == "0000-00-00")) {
                $beenden_link = "<a href=\"?daten=mietvertrag_raus&mietvertrag_raus=mietvertrag_beenden&einheit_id=$EINHEIT_ID\">Beenden</a>";
                $aendern_link = "<a href=\"?daten=mietvertrag_raus&mietvertrag_raus=mietvertrag_aendern&einheit_id=$EINHEIT_ID\">Ändern</a>";
            } else {
                $beenden_link = "Abgelaufen";
                $aendern_link = "k.Ä.";
            }
            $MIETVERTRAG_BIS = date_mysql2german($MIETVERTRAG_BIS);
            $MIETVERTRAG_VON = date_mysql2german($MIETVERTRAG_VON);
            $mieter_im_vetrag = anzahl_mieter_im_vertrag($MIETVERTRAG_ID);
            $einheit_kurzname = einheit_kurzname($EINHEIT_ID);
            $detail_check = detail_check("MIETVERTRAG", $MIETVERTRAG_ID);
            $buchen_link = "<a href=\"?daten=miete_buchen&schritt=buchungsauswahl&mietvertrag_id=$MIETVERTRAG_ID\">BUCHEN</a>";
            $mietkonto_link = "<a href=\"?daten=mietkonten_blatt&anzeigen=mietkonto_uebersicht_detailiert&mietvertrag_id=$MIETVERTRAG_ID\">MIETKONTO</a>";
            $miete_aendern = "<a href=\"?daten=miete_definieren&option=miethoehe&mietvertrag_id=$MIETVERTRAG_ID\">MIETHÖHE</a>";
            $einheit_link = "<a href=\"?daten=uebersicht&anzeigen=einheit&einheit_id=$EINHEIT_ID\">$einheit_kurzname</a>";
            $kautionen_link = "<a href=\"?daten=kautionen&option=kautionen_buchen&mietvertrag_id=$MIETVERTRAG_ID\">KAUTION BUCHEN</a>";

            if ($detail_check > 0) {
                $detail_link = "<a class=\"table_links\" href=\"?daten=details&option=details_anzeigen&detail_tabelle=MIETVERTRAG&detail_id=$MIETVERTRAG_ID\">Details</a>";
            } else {
                $detail_link = "<a class=\"table_links\" href=\"?daten=details&option=details_hinzu&detail_tabelle=MIETVERTRAG&detail_id=$MIETVERTRAG_ID\">Neues Detail</a>";
            }
            if ($counter == 1) {
                echo "<tr class=\"zeile1\"><td>$einheit_link</td><td>($mieter_im_vetrag)";
                echo mieterid_zum_vertrag($MIETVERTRAG_ID);
                echo "</td><td>$mietkonto_link $buchen_link $miete_aendern  $kautionen_link</td><td>$MIETVERTRAG_VON</td><td>$MIETVERTRAG_BIS</td><td>$detail_link</td><td>$beenden_link $aendern_link</td></tr>";
            }
            if ($counter == 2) {
                echo "<tr class=\"zeile2\"><td>$einheit_link </td><td>($mieter_im_vetrag)";
                echo mieterid_zum_vertrag($MIETVERTRAG_ID);
                echo "</td><td>$mietkonto_link $buchen_link $miete_aendern  $kautionen_link</td><td>$MIETVERTRAG_VON</td><td>$MIETVERTRAG_BIS</td><td>$detail_link</td><td>$beenden_link $aendern_link</td></tr>";
                $counter = 0;
            }
        }
        echo "</table>";
        iframe_end();
    }
}

function mietvertrag_aktuelle($einheit_id)
{
    if (!isset ($einheit_id)) {
        $datum_heute = date("Y-m-d");
        $db_abfrage = "SELECT MIETVERTRAG.MIETVERTRAG_ID, MIETVERTRAG.MIETVERTRAG_VON, MIETVERTRAG.MIETVERTRAG_BIS, MIETVERTRAG.EINHEIT_ID, EINHEIT.EINHEIT_KURZNAME FROM MIETVERTRAG JOIN(EINHEIT) ON (EINHEIT.EINHEIT_ID = MIETVERTRAG.EINHEIT_ID) WHERE MIETVERTRAG_AKTUELL='1' && EINHEIT.EINHEIT_AKTUELL='1' && (MIETVERTRAG.MIETVERTRAG_BIS='0000-00-00' OR MIETVERTRAG.MIETVERTRAG_BIS>'$datum_heute') ORDER BY EINHEIT.EINHEIT_KURZNAME ASC,MIETVERTRAG.MIETVERTRAG_BIS  DESC";
    } else {
        $db_abfrage = "SELECT MIETVERTRAG_ID, MIETVERTRAG_VON, MIETVERTRAG_BIS, EINHEIT_ID FROM MIETVERTRAG where EINHEIT_ID='$einheit_id' && MIETVERTRAG_AKTUELL='1'";
    }

    $resultat = mysql_query($db_abfrage) or die (mysql_error());

    $numrows = mysql_numrows($resultat);
    if ($numrows < 1) {
        echo "<h1><b>Keine Mietverträge zur Einheit $einheit_id vorhanden!!!</b></h1>";
    } else {
        echo "<table width=100%>\n";
        echo "<tr class=\"feldernamen\"><td colspan=5>Alle Mietverträge</td></tr>\n";
        echo "<tr class=\"feldernamen\"><td width=100>EINHEIT</td><td width=300>MIETER</td><td width=85>VON</td><td width=80>BIS</td><td>Optionen</td></tr>\n";
        echo "</table>\n";
        iframe_start();
        echo "<table width=100%>\n";
        $counter = 0;
        while (list ($MIETVERTRAG_ID, $MIETVERTRAG_VON, $MIETVERTRAG_BIS, $EINHEIT_ID) = mysql_fetch_row($resultat)) {
            $counter++;
            $datum_heute = date("Y-m-d");
            if (($MIETVERTRAG_BIS > $datum_heute) or ($MIETVERTRAG_BIS == "0000-00-00")) {
                $beenden_link = "<a href=\"?daten=mietvertrag_raus&mietvertrag_raus=mietvertrag_beenden&mietvertrag_id=$MIETVERTRAG_ID\">Beenden</a>";
                $aendern_link = "<a href=\"?daten=mietvertrag_raus&mietvertrag_raus=mietvertrag_aendern&mietvertrag_id=$MIETVERTRAG_ID\">Ändern</a>";
            } else {
                $beenden_link = "Abgelaufen";
                // $aendern_link = "k.Ä.";
                $aendern_link = "<a href=\"?daten=mietvertrag_raus&mietvertrag_raus=mietvertrag_aendern&mietvertrag_id=$MIETVERTRAG_ID\">Ändern</a>";
            }
            $MIETVERTRAG_BIS = date_mysql2german($MIETVERTRAG_BIS);
            $MIETVERTRAG_VON = date_mysql2german($MIETVERTRAG_VON);
            $mieter_im_vetrag = anzahl_mieter_im_vertrag($MIETVERTRAG_ID);
            $einheit_kurzname = einheit_kurzname($EINHEIT_ID);
            $detail_check = detail_check("MIETVERTRAG", $MIETVERTRAG_ID);
            $einheit_link = "<a href=\"?daten=uebersicht&anzeigen=einheit&einheit_id=$EINHEIT_ID\">$einheit_kurzname</a>";
            $kautionen_link = "<a href=\"?daten=kautionen&option=kautionen_buchen&mietvertrag_id=$MIETVERTRAG_ID\">KAUTION BUCHEN</a>";
            $miete_aendern = "<a href=\"?daten=miete_definieren&option=miethoehe&mietvertrag_id=$MIETVERTRAG_ID\">MIETHÖHE</a>";
            if ($detail_check > 0) {
                $detail_link = "<a class=\"table_links\" href=\"?daten=details&option=details_anzeigen&detail_tabelle=MIETVERTRAG&detail_id=$MIETVERTRAG_ID\">Details</a>";
            } else {
                $detail_link = "<a class=\"table_links\" href=\"?daten=details&option=details_hinzu&detail_tabelle=MIETVERTRAG&detail_id=$MIETVERTRAG_ID\">Neues Detail</a>";
            }
            if ($counter == 1) {
                echo "<tr class=\"zeile1\"><td>$einheit_link $miete_aendern $kautionen_link </td><td>($mieter_im_vetrag)";
                echo mieterid_zum_vertrag($MIETVERTRAG_ID);
                echo "</td><td>$MIETVERTRAG_VON</td><td>$MIETVERTRAG_BIS</td><td>$detail_link</td><td>$beenden_link $aendern_link</td></tr>";
            }
            if ($counter == 2) {
                echo "<tr class=\"zeile2\"><td>$einheit_link $miete_aendern $kautionen_link</td><td>($mieter_im_vetrag)";
                echo mieterid_zum_vertrag($MIETVERTRAG_ID);
                echo "</td><td>$MIETVERTRAG_VON</td><td>$MIETVERTRAG_BIS</td><td>$detail_link</td><td>$beenden_link $aendern_link</td></tr>";
                $counter = 0;
            }
        }
        echo "</table>";
        iframe_end();
    }
}

function mietvertrag_objekt_links()
{
    $daten_rein = $_REQUEST ["daten_rein"];
    $db_abfrage = "SELECT OBJEKT_DAT, OBJEKT_ID, OBJEKT_KURZNAME FROM OBJEKT WHERE OBJEKT_AKTUELL='1' ORDER BY OBJEKT_KURZNAME ASC ";
    $resultat = mysql_query($db_abfrage) or die (mysql_error());
    echo "<b>Objekt auswählen:</b><br>\n ";
    while (list ($OBJEKT_DAT, $OBJEKT_ID, $OBJEKT_KURZNAME) = mysql_fetch_row($resultat)) {
        echo "<a class=\"objekt_links\" href=\"?daten=mietvertrag_raus&mietvertrag_raus=mietvertrag_neu&objekt_id=$OBJEKT_ID\">$OBJEKT_KURZNAME</a><br>\n";
    }
}
