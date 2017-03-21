/**
 * BERLUSSIMO
 *
 * Hausverwaltungssoftware
 *
 *
 * @copyright Copyright (c) 2010, Berlus GmbH, Fontanestr. 1, 14193 Berlin
 * @link http://www.berlus.de
 * @author Sanel Sivac & Wolfgang Wehrheim
 * @contact software(@)berlus.de
 * @license http://www.gnu.org/licenses/agpl.html AGPL Version 3
 *
 * @filesource $HeadURL: http://192.168.2.52/svn/berlussimo_1/tags/02.11.2010 -
 *             Downloadversion 0.27/js/javascript.js $
 * @version $Revision: 15 $
 * @modifiedby $LastChangedBy: sivac $
 * @lastmodified $Date: 2011-07-07 10:41:33 +0200 (Do, 07 Jul 2011) $
 *
 */
function activate(id) {
    activate.aktivesElement = id;
}

function mwst_rechnen(q_feld, z_feld, prozent) {
    var wert_k = document.getElementById(q_feld).value;
    // alert(wert_k);
    var wert = wert_k.replace(",", ".");
    prozent_neu = parseInt(prozent) + parseInt(100);
    // alert(prozent_neu);
    var wert_neu = wert / prozent_neu * prozent;
    wert_neu_a = wert_neu.toFixed(2);
    wert_neu_a = wert_neu_a.replace(".", ",");
    document.getElementById(z_feld).value = wert_neu_a;
}

function pos_fuellen(art_nr, bezeichnung, preis) {

    var art_nr_feld = activate.aktivesElement;
    var bezeichnung_feld = art_nr_feld.replace(/artikel_nr/g, "bezeichnung");
    var preis_feld = art_nr_feld.replace(/artikel_nr/g, "preis");
    var menge_feld = art_nr_feld.replace(/artikel_nr/g, "menge");

    document.getElementById(art_nr_feld).value = art_nr;
    document.getElementById(bezeichnung_feld).value = bezeichnung;
    document.getElementById(preis_feld).value = preis;

}

function hk_diff(feld1, feld2, zielfeld) {

    // form.epreis_feld[i].value;

    wert1 = document.getElementById(feld1).value;
    wert2 = document.getElementById(feld2).value.replace(",", ".");

    wert3 = (wert1 - wert2).toFixed(2);
    ;
    document.getElementById(zielfeld).value = wert3.replace(".", ",");
    // alert(wert3);
}

function neu_berechnen() {

    var menge_feld = activate.aktivesElement;
    var preis_feld = menge_feld.replace(/menge/g, "preis");
    var gpreis_feld = menge_feld.replace(/menge/g, "gpreis");
    var brutto_feld = menge_feld.replace(/menge/g, "gpreis_brutto");
    var mwst_feld = menge_feld.replace(/menge/g, "pos_mwst");

    var aktuelle_menge = document.getElementById(menge_feld).value;
    var aktueller_preis = document.getElementById(preis_feld).value;
    var aktueller_preis = aktueller_preis.replace(",", ".");
    var aktueller_brutto_preis = document.getElementById(brutto_feld).value;
    var aktueller_mwst_satz = document.getElementById(mwst_feld).value;
    var neuer_gpreis = aktuelle_menge * aktueller_preis;
    var neuer_brutto_preis = (neuer_gpreis / 100) * aktueller_mwst_satz;

    neuer_gpreis = neuer_gpreis.toFixed(2);
    neuer_brutto_preis = neuer_gpreis.replace(".", ",");
    document.getElementById(gpreis_feld).value = neuer_gpreis;
    document.getElementById(brutto_feld).value = neuer_brutto_preis;
    gesamt_berechnen();

}

function neu_berechnen_neuer_preis() {

    var preis_feld = activate.aktivesElement;
    var gpreis_feld = preis_feld.replace(/preis/g, "gpreis");
    var menge_feld = preis_feld.replace(/preis/g, "menge");
    var aktuelle_menge = document.getElementById(menge_feld).value;
    var aktueller_preis = document.getElementById(preis_feld).value;
    var aktueller_preis = aktueller_preis.replace(",", ".");
    var neuer_gpreis = aktuelle_menge * aktueller_preis;
    neuer_gpreis = neuer_gpreis.toFixed(2);
    neuer_gpreis = neuer_gpreis.replace(".", ",");
    document.getElementById(gpreis_feld).value = neuer_gpreis;
    gesamt_berechnen();

}

function gesamt_berechnen() {
    var anzahl_positionen = 0;
    var gesamt_bisher_netto = 0;
    var gpreis_betrag = 0;

    for (var i = 1; i <= anzahl_positionen; i++) {
        var gpreis_feld = 'positionen[' + i + '][gpreis]';
        var gpreis_brutto_feld = 'positionen[' + i + '][gpreis_brutto]';
        var gpreis_betrag = document.getElementById(gpreis_feld).value;
        gpreis_betrag = gpreis_betrag.replace(",", ".");
        gesamt_bisher_netto = eval(gesamt_bisher_netto + "+" + gpreis_betrag);
        var gpreis_brutto = gpreis_betrag / 100 * 119;
        gpreis_brutto = gpreis_brutto.toFixed(2);
        gpreis_brutto = gpreis_brutto.replace(".", ",");
        document.getElementById(gpreis_brutto_feld).value = gpreis_brutto;
        pos_mwst_feld = 'positionen[' + i + '][pos_mwst]';
        pos_mwst = gpreis_betrag / 100 * 19;
        pos_mwst = pos_mwst.toFixed(2);
        document.getElementById(pos_mwst_feld).value = pos_mwst;
    }

    gesamt_bisher_netto = gesamt_bisher_netto.toFixed(2);
    gesamt_bisher_netto_komma = gesamt_bisher_netto.replace(".", ",");
    document.getElementById('gesamt_errechnet').innerHTML = 'Netto '
        + gesamt_bisher_netto_komma + ' €';

    gesamt_bisher_brutto = gesamt_bisher_netto / 100 * 119;
    gesamt_bisher_brutto = gesamt_bisher_brutto.toFixed(2);
    gesamt_bisher_brutto_komma = gesamt_bisher_brutto.replace(".", ",");
    document.getElementById('gesamt_errechnet_brutto').innerHTML = 'Brutto '
        + gesamt_bisher_brutto_komma + ' €';

    var rechnung_netto = document.getElementById('rechnung_netto').value;
    var rechnung_brutto = document.getElementById('rechnung_brutto').value;
    var rechnung_mwst_satz = document.getElementById('rechnung_mwst_satz').value;

    var differenz = eval(gesamt_bisher_netto + "-" + rechnung_netto);
    differenz = differenz.toFixed(2);
    differenz_komma = differenz.replace(".", ",");
    document.getElementById('differenz').innerHTML = 'Differenz '
        + differenz_komma + ' €';

    if (document.getElementById('differenz').innerHTML == 'Differenz 0,00 €') {
        // alert('JETZT WEITER');
        Abfrage = confirm("Die Summen der Positionsbeträge ist identisch mit der Rechnungssumme!\n Erst jetzt können Sie speichern, in dem Sie auf den Button Speichern klicken!");
        if (Abfrage == true) {
            document.getElementById('senden_pos').style.visibility = 'visible';
        }

    }
}

var checkflag = "false";

function check_on_off(field, field2) {
    // alert('hm');
    if (document.getElementById(field2).checked = true) {
        // alert('f2 ON');
        document.getElementById(field).checked = false;
    } else {
        document.getElementById(field).checked = true;
    }
}

function check(field) {
    var laenge = field.length;
    // alert(laenge);
    // alert('SANEL');
    // alert(field);
    if (checkflag == "false") {
        if (laenge == undefined) {
            field.checked = true;
        }
        for (i = 0; i < field.length; i++) {
            field[i].checked = true;
        }
        checkflag = "true";

    } else {
        if (laenge == undefined) {
            field.checked = false;
        }

        for (i = 0; i < field.length; i++) {
            field[i].checked = false;
        }
        checkflag = "false";

    }
}
/* Alle Checkboxen aktvieren */
/*
 * <input type="button" name="button"
 * onclick='activate(this.form.elements.["Stadt[]"]);' value="Ich habe alle
 * besucht">
 */
var marker = true;
function activate(field) {
    for (var i = 0; i < field.length; i++)
        field[i].checked = marker;
    marker = !marker;
}

function auswahl_alle(dropdown) {
    // alert(dropdown);
    var laenge = dropdown.length;
    var meine_auswahl = dropdown[0].selectedIndex;
    for (var i = 1; i < laenge; i++) {
        dropdown[i].options[meine_auswahl].selected = true;
    }
    $('select').material_select();
}

function check_all_boxes(check, prefix) {
    var $boxes = $("[id^='" + prefix + "']");
    $boxes.prop('checked', check);
}

function auswahl_alle2(form, dropdown) {
    // alert(dropdown);
    var laenge = form.dropdown.length;
    var meine_auswahl = form.dropdown[0].selectedIndex;
    for (var i = 1; i < laenge; i++) {
        form.dropdown[i].options[meine_auswahl].selected = true;
    }
}

function wert_uebertragen(text_feld) {
    var laenge = text_feld.length;
    var wert = text_feld[0].value;

    for (var i = 1; i < laenge; i++) {
        text_feld[i].value = wert;
        text_feld[i].focus();
    }

}

function zusammenfassung_neuberechnen(form) {
    var epreis_feld = form.epreis_feld;
    var laenge = epreis_feld.length;

    var g_netto_errechnet = 0;
    var g_brutto_errechnet = 0;
    var durchschnitt_rabatt = 0;

    if (laenge > 0) {
        for (var i = 0; i < laenge; i++) {

            var epreis_wert = form.epreis_feld[i].value;
            var mengen_wert = form.mengen_feld[i].value;
            var rabatt_wert = form.rabatt_feld[i].value;
            var netto_wert = form.netto_feld[i].value;
            var mwst_wert = form.mwst_feld[i].value;
            epreis_wert = epreis_wert.replace(",", ".");
            mengen_wert = mengen_wert.replace(",", ".");
            rabatt_wert = rabatt_wert.replace(",", ".");
            rabatt_wert = parseInt(rabatt_wert);
            durchschnitt_rabatt = durchschnitt_rabatt + rabatt_wert;

            var netto = epreis_wert * mengen_wert;
            var rabatt = (netto / 100) * rabatt_wert;
            netto = netto - rabatt;
            g_netto_errechnet = g_netto_errechnet + netto;
            g_netto_errechnet_ausgabe = g_netto_errechnet.toFixed(2);
            g_netto_errechnet_ausgabe = g_netto_errechnet_ausgabe.replace(".",
                ",");
            var netto_new = netto.toFixed(2);
            netto_new = netto_new.replace(".", ",");
            form.netto_feld[i].value = netto_new;
        }
        durchschnitt_rabatt = durchschnitt_rabatt / laenge;
        var durchschnitt_rabatt = durchschnitt_rabatt.toFixed(2);

        document.getElementById('g_netto_errechnet').innerHTML = g_netto_errechnet_ausgabe
            + '&nbsp€';
        document.getElementById('durchschnitt_rabatt').innerHTML = durchschnitt_rabatt
            + '&nbsp%';

        var rechnungs_netto = document.getElementById('rechnungs_netto').innerHTML;
        var rechnungs_brutto = document.getElementById('rechnungs_brutto').innerHTML;

        if (rechnungs_netto == g_netto_errechnet_ausgabe) {
            document.getElementById('speichern_button1').value = 'Speichern möglich';
            document.getElementById('speichern_button1').disabled = false;
        } else {
            document.getElementById('speichern_button1').value = 'Speichern deaktiviert';
            document.getElementById('speichern_button1').disabled = true;
        }
    } else {
        var epreis_wert = form.epreis_feld.value;
        var mengen_wert = form.mengen_feld.value;
        var rabatt_wert = form.rabatt_feld.value;
        var netto_wert = form.netto_feld.value;
        var mwst_wert = form.mwst_feld.value;
        epreis_wert = epreis_wert.replace(",", ".");
        mengen_wert = mengen_wert.replace(",", ".");
        rabatt_wert = rabatt_wert.replace(",", ".");
        rabatt_wert = parseInt(rabatt_wert);
        durchschnitt_rabatt = durchschnitt_rabatt + rabatt_wert;

        var netto = epreis_wert * mengen_wert;
        var rabatt = (netto / 100) * rabatt_wert;
        netto = netto - rabatt;
        g_netto_errechnet = g_netto_errechnet + netto;
        g_netto_errechnet_ausgabe = g_netto_errechnet.toFixed(2);
        g_netto_errechnet_ausgabe = g_netto_errechnet_ausgabe.replace(".", ",");
        var netto_new = netto.toFixed(2);
        netto_new = netto_new.replace(".", ",");
        form.netto_feld.value = netto_new;
    }
    durchschnitt_rabatt = durchschnitt_rabatt / 1;
    var durchschnitt_rabatt = durchschnitt_rabatt.toFixed(2);

    document.getElementById('g_netto_errechnet').innerHTML = g_netto_errechnet_ausgabe
        + '&nbsp€';
    document.getElementById('durchschnitt_rabatt').innerHTML = durchschnitt_rabatt
        + '&nbsp%';

    var rechnungs_netto = document.getElementById('rechnungs_netto').innerHTML;
    var rechnungs_brutto = document.getElementById('rechnungs_brutto').innerHTML;

    if (rechnungs_netto == g_netto_errechnet_ausgabe) {
        document.getElementById('speichern_button1').value = 'Speichern möglich';
        document.getElementById('speichern_button1').disabled = false;
    } else {
        document.getElementById('speichern_button1').value = 'Speichern deaktiviert';
        document.getElementById('speichern_button1').disabled = true;
    }

}

function wert_uebertragen(text_feld) {
    var laenge = text_feld.length;
    var wert = text_feld[0].value;

    for (var i = 1; i < laenge; i++) {
        text_feld[i].value = wert;
        text_feld[i].focus();
    }

}

function pool_berechnung(form) {
    var laenge = $("[id^='positionen_list_']").length;

    /* Alle Zeilen durchlaufen, prüfen was ausgewählt */
    /* SUMMENVARIABLEN DEFINIEREN */
    var g_netto_ausgewaehlt = 0.00;
    var g_brutto_ausgewaehlt = 0.00;
    var g_skonto_n = 0.00;
    var g_netto_errechnet = 0.00;
    var g_brutto_errechnet = 0.00;
    var g_skonto_n_errechnet = 0.00;
    var g_skonto_betrag_errechnet = 0.00;
    var durchschnitt_rabatt = 0.00;
    var ausgewaehlte_zeilen = 0;

    if (laenge > 0) {
        for (var i = 0; i < laenge; i++) {
            var check_box_status = form['positionen_list_' + i].checked;
            var epreis_wert = form['epreis_feld_' + i].value;
            var mengen_wert = form['mengen_feld_' + i].value;
            var rabatt_wert = form['rabatt_feld_' + i].value;
            var netto_wert = form['netto_feld_' + i].value;
            var mwst_wert = form['mwst_feld_' + i].value;
            var skonto_wert = form['skonto_feld_' + i].value;

            epreis_wert = epreis_wert.replace(",", ".");
            form['epreis_feld_' + i].value = epreis_wert.replace(".", ",");
            mengen_wert = mengen_wert.replace(",", ".");
            form['mengen_feld_' + i].value = mengen_wert.replace(".", ",");
            rabatt_wert = rabatt_wert.replace(",", ".");
            rabatt_wert = rabatt_wert.replace(",", ".");
            mwst_wert = parseInt(mwst_wert.replace(",", "."));
            skonto_wert = skonto_wert.replace(",", ".");

            pos_gesamt_netto = (mengen_wert * epreis_wert / 100)
                * (100 - rabatt_wert);
            pos_gesamt_br = (pos_gesamt_netto / 100) * (100 + mwst_wert);
            pos_gesamt_skontiert = (pos_gesamt_br / 100) * (100 - skonto_wert);
            pos_skonto_nachlass = (pos_gesamt_br / 100) * skonto_wert;

            /* Netto zeile updaten */
            pos_gesamt_netto_a = pos_gesamt_netto.toFixed(2);
            pos_gesamt_netto_a = pos_gesamt_netto_a.replace(".", ",");
            form['netto_feld_' + i].value = pos_gesamt_netto_a;

            /*
             * Summen aller Zeilen im Pool bilden, wenn alle ausgewählt =
             * summe_ausgewaehlt
             */
            g_netto_errechnet = g_netto_errechnet + pos_gesamt_netto;
            g_brutto_errechnet = g_brutto_errechnet + pos_gesamt_br;
            g_skonto_n_errechnet = g_skonto_n_errechnet + pos_skonto_nachlass;
            g_skonto_betrag_errechnet = g_brutto_errechnet - g_skonto_n;

            /* Durchschnitsrabatt */
            durchschnitt_rabatt = durchschnitt_rabatt + rabatt_wert;

            /* Wenn zeile ausgewählt, Gesamtsummen ausgewählter Zeilen anzeigen */
            if (check_box_status) {
                g_netto_ausgewaehlt = g_netto_ausgewaehlt + pos_gesamt_netto;
                g_brutto_ausgewaehlt = g_brutto_ausgewaehlt + pos_gesamt_br;
                g_skonto_n = g_skonto_n + pos_skonto_nachlass;
                ausgewaehlte_zeilen = ausgewaehlte_zeilen + 1;
            }

        }// end for

        g_netto_ausgewaehlt = g_netto_ausgewaehlt.toFixed(2);
        g_brutto_ausgewaehlt = g_brutto_ausgewaehlt.toFixed(2);
        g_skonto_n = g_skonto_n.toFixed(2);
        g_skonto_betrag = g_brutto_ausgewaehlt - g_skonto_n;

        /* Ausgabewerte ins Kommaformat */
        var g_netto_ausgewaehlt_a = nummer_punkt2komma(g_netto_ausgewaehlt);
        g_brutto_ausgewaehlt_a = nummer_punkt2komma(g_brutto_ausgewaehlt);
        g_skonto_n_a = nummer_punkt2komma(g_skonto_n);
        g_skonto_betrag_a = nummer_punkt2komma(g_brutto_ausgewaehlt);

        /* Update von Ausgewählten Summen */
        document.getElementById('g_netto_ausgewaehlt').innerHTML = g_netto_ausgewaehlt_a
            + '&nbsp€';
        document.getElementById('g_brutto_ausgewaehlt').innerHTML = g_brutto_ausgewaehlt_a
            + '&nbsp€';
        document.getElementById('g_skonto_nachlass').innerHTML = g_skonto_n_a
            + '&nbsp€';
        document.getElementById('g_skonto_betrag').innerHTML = g_skonto_betrag
            + '&nbsp€';

    }// ende if zeilen existieren
    if (ausgewaehlte_zeilen > 0) {
        document.getElementById('speichern_button2').disabled = false;
    } else {
        document.getElementById('speichern_button2').disabled = true;
    }

}// end function

function nummer_punkt2komma(zahl) {
    var meine_zahl = zahl;
    return meine_zahl.replace('.', ",");
}

function nummer_komma2punkt(zahl) {
    var meine_zahl = zahl;
    return meine_zahl.replace(',', ".");
}

function rechnung_pool_neuberechnen(form) {
    var epreis_feld = form.epreis_feld;
    var laenge = epreis_feld.length;

    var g_netto_errechnet = 0;
    var g_brutto_errechnet = 0;
    var durchschnitt_rabatt = 0;

    var g_netto_ausgewaehlt = 0;
    var g_brutto_ausgewaehlt = 0;

    if (laenge > 0) {

        for (var i = 0; i < laenge; i++) {

            var check_box_status = form['positionen_list_' + i].checked;
            var epreis_wert = form.epreis_feld[i].value;
            var mengen_wert = form.mengen_feld[i].value;
            var rabatt_wert = form.rabatt_feld[i].value;
            var netto_wert = form.netto_feld[i].value;
            var mwst_wert = form.mwst_feld[i].value;
            var skonto = document.form.skonto[i].value;
            epreis_wert = epreis_wert.replace(",", ".");
            mengen_wert = mengen_wert.replace(",", ".");
            rabatt_wert = rabatt_wert.replace(",", ".");
            rabatt_wert = parseInt(rabatt_wert);
            mwst_wert = parseInt(mwst_wert);
            durchschnitt_rabatt = durchschnitt_rabatt + rabatt_wert;

            var netto = epreis_wert * mengen_wert;
            var rabatt = (netto / 100) * rabatt_wert;
            netto = netto - rabatt;
            prozent_1 = (netto / 100);
            brutto_prozent = 100 + mwst_wert;
            brutto_zeile = prozent_1 * brutto_prozent;
            g_brutto_errechnet = g_brutto_errechnet + brutto_zeile;
            g_brutto_errechnet_ausgabe = g_brutto_errechnet.toFixed(2);
            g_brutto_errechnet_ausgabe = g_brutto_errechnet_ausgabe.replace(
                ".", ",");

            g_netto_errechnet = g_netto_errechnet + netto;
            g_netto_errechnet_ausgabe = g_netto_errechnet.toFixed(2);
            g_netto_errechnet_ausgabe = g_netto_errechnet_ausgabe.replace(".",
                ",");
            var netto_new = netto.toFixed(2);
            netto_new = netto_new.replace(".", ",");
            form.netto_feld[i].value = netto_new;

            if (check_box_status == true) {
                g_netto_ausgewaehlt = g_netto_ausgewaehlt + netto;
                g_brutto_ausgewaehlt = g_brutto_ausgewaehlt + brutto_zeile;
            }
        }
        durchschnitt_rabatt = durchschnitt_rabatt / laenge;
        var durchschnitt_rabatt = durchschnitt_rabatt.toFixed(2);
        g_netto_ausgewaehlt_2stellig = g_netto_ausgewaehlt.toFixed(2);
        g_netto_ausgewaehlt_komma = g_netto_ausgewaehlt_2stellig.replace(".",
            ",");

        g_brutto_ausgewaehlt_2stellig = g_brutto_ausgewaehlt.toFixed(2);
        g_brutto_ausgewaehlt_komma = g_brutto_ausgewaehlt_2stellig.replace(".",
            ",");

        g_skonto_betrag_1prozent = (g_brutto_ausgewaehlt / 100);
        g_skonto_nachlass = skonto_wert * g_skonto_betrag_1prozent;
        g_skonto_nachlass_2stellig = g_skonto_nachlass.toFixed(2);
        g_skonto_nachlass_komma = g_skonto_nachlass_2stellig.replace(".", ",");

        g_skonto_betrag = g_brutto_ausgewaehlt - g_skonto_nachlass;
        g_skonto_betrag_2stellig = g_skonto_betrag.toFixed(2);
        g_skonto_betrag_komma = g_skonto_betrag_2stellig.replace(".", ",");

        document.getElementById('g_netto_errechnet').innerHTML = g_netto_errechnet_ausgabe
            + '&nbsp€';
        document.getElementById('g_brutto_errechnet').innerHTML = g_brutto_errechnet_ausgabe
            + '&nbsp€';
        document.getElementById('durchschnitt_rabatt').innerHTML = durchschnitt_rabatt
            + '&nbsp%';
        document.getElementById('g_netto_ausgewaehlt').innerHTML = g_netto_ausgewaehlt_komma
            + '&nbsp€';
        document.getElementById('g_brutto_ausgewaehlt').innerHTML = g_brutto_ausgewaehlt_komma
            + '&nbsp€';
        document.getElementById('g_skonto_nachlass').innerHTML = g_skonto_nachlass_komma
            + '&nbsp€' + '(' + skonto_wert + '%)';
        document.getElementById('g_skonto_betrag').innerHTML = g_skonto_betrag_komma
            + '&nbsp€';

        document.getElementById('RECHNUNG_NETTO_BETRAG').value = g_netto_ausgewaehlt;
        document.getElementById('RECHNUNG_BRUTTO_BETRAG').value = g_brutto_ausgewaehlt;
        document.getElementById('RECHNUNG_SKONTO_BETRAG').value = g_skonto_betrag;

    } // end if laenge undefined

    else {

        var epreis_wert = form.epreis_feld.value;
        var mengen_wert = form.mengen_feld.value;
        var rabatt_wert = form.rabatt_feld.value;
        var netto_wert = form.netto_feld.value;
        var mwst_wert = form.mwst_feld.value;
        epreis_wert = epreis_wert.replace(",", ".");
        mengen_wert = mengen_wert.replace(",", ".");
        rabatt_wert = rabatt_wert.replace(",", ".");
        rabatt_wert = parseInt(rabatt_wert);
        mwst_wert = parseInt(mwst_wert);
        durchschnitt_rabatt = durchschnitt_rabatt + rabatt_wert;
        var netto = epreis_wert * mengen_wert;
        var rabatt = (netto / 100) * rabatt_wert;
        netto = netto - rabatt;
        prozent_1 = (netto / 100);
        brutto_prozent = 100 + mwst_wert;
        brutto_zeile = prozent_1 * brutto_prozent;
        g_brutto_errechnet = g_brutto_errechnet + brutto_zeile;
        g_brutto_errechnet_ausgabe = g_brutto_errechnet.toFixed(2);
        g_brutto_errechnet_ausgabe = g_brutto_errechnet_ausgabe.replace(".",
            ",");

        g_netto_errechnet = g_netto_errechnet + netto;
        g_netto_errechnet_ausgabe = g_netto_errechnet.toFixed(2);
        g_netto_errechnet_ausgabe = g_netto_errechnet_ausgabe.replace(".", ",");
        var netto_new = netto.toFixed(2);
        netto_new = netto_new.replace(".", ",");
        form.netto_feld.value = netto_new;

        g_netto_ausgewaehlt = g_netto_ausgewaehlt + netto;
        g_brutto_ausgewaehlt = g_brutto_ausgewaehlt + brutto_zeile;

        durchschnitt_rabatt = durchschnitt_rabatt / laenge;
        var durchschnitt_rabatt = durchschnitt_rabatt.toFixed(2);
        g_netto_ausgewaehlt_2stellig = g_netto_ausgewaehlt.toFixed(2);
        g_netto_ausgewaehlt_komma = g_netto_ausgewaehlt_2stellig.replace(".",
            ",");

        g_brutto_ausgewaehlt_2stellig = g_brutto_ausgewaehlt.toFixed(2);
        g_brutto_ausgewaehlt_komma = g_brutto_ausgewaehlt_2stellig.replace(".",
            ",");

        g_skonto_betrag_1prozent = (g_brutto_ausgewaehlt / 100);
        g_skonto_nachlass = skonto_wert * g_skonto_betrag_1prozent;
        g_skonto_nachlass_2stellig = g_skonto_nachlass.toFixed(2);
        g_skonto_nachlass_komma = g_skonto_nachlass_2stellig.replace(".", ",");

        g_skonto_betrag = g_brutto_ausgewaehlt - g_skonto_nachlass;
        g_skonto_betrag_2stellig = g_skonto_betrag.toFixed(2);
        g_skonto_betrag_komma = g_skonto_betrag_2stellig.replace(".", ",");

        document.getElementById('g_netto_errechnet').innerHTML = g_netto_errechnet_ausgabe
            + '&nbsp€';
        document.getElementById('g_brutto_errechnet').innerHTML = g_brutto_errechnet_ausgabe
            + '&nbsp€';
        document.getElementById('durchschnitt_rabatt').innerHTML = durchschnitt_rabatt
            + '&nbsp%';
        document.getElementById('g_netto_ausgewaehlt').innerHTML = g_netto_ausgewaehlt_komma
            + '&nbsp€';
        document.getElementById('g_brutto_ausgewaehlt').innerHTML = g_brutto_ausgewaehlt_komma
            + '&nbsp€';
        document.getElementById('g_skonto_nachlass').innerHTML = g_skonto_nachlass_komma
            + '&nbsp€' + '(' + skonto_wert + '%)';
        document.getElementById('g_skonto_betrag').innerHTML = g_skonto_betrag_komma
            + '&nbsp€';

        document.getElementById('RECHNUNG_NETTO_BETRAG').value = g_netto_ausgewaehlt;
        document.getElementById('RECHNUNG_BRUTTO_BETRAG').value = g_brutto_ausgewaehlt;
        document.getElementById('RECHNUNG_SKONTO_BETRAG').value = g_skonto_betrag;

    }
}// end function

var gemerkt = "nein";

function BoxenAktivieren(box) {
    if (gemerkt == "nein") {
        box.checked = true;
        gemerkt = "ja";
        document.getElementById('speichern_button2').value = 'Speichern und in Rechnung stellen';
        document.getElementById('speichern_button2').disabled = false;
    } else {

        box.checked = false;
        gemerkt = "nein";
        document.getElementById('speichern_button2').value = 'Eingabe unvollständig';
        document.getElementById('speichern_button2').disabled = true;
    }
}

pos_geweaehlt = "nein";
function check_ob_pos_gewaehlt(box, field) {

    var laenge = field.length;
    // alert(laenge);

    if (pos_geweaehlt == "nein") {

        box.checked = true;
        pos_geweaehlt = "ja";
        document.getElementById('speichern_button2').value = 'Speichern und in Rechnung stellen';
        document.getElementById('speichern_button2').disabled = false;
    } else {

        box.checked = false;
        gemerkt = "nein";
        document.getElementById('speichern_button2').value = 'Eingabe unvollständig';
        document.getElementById('speichern_button2').disabled = true;
    }
}

function skonto_berechnen() {
    brutto = document.getElementById('bruttobetrag').value
    brutto = brutto.replace(",", ".");
    ein_prozent = brutto / 100;
    prozente = document.getElementById('skonto').value;
    prozente = prozente.replace(",", ".");
    skonto = ein_prozent * prozente;
    skontobetrag = brutto - skonto;
    skontobetrag = runde_kaufm(skontobetrag);
    skontobetrag = skontobetrag.replace(".", ",");
    document.getElementById('skontobetrag').value = skontobetrag;
    prozente = runde_kaufm(prozente);
    document.getElementById('skonto').value = prozente.replace(".", ",");
}

function runde_kaufm(x) {
    var k = (Math.round(x * 100) / 100).toString();
    k += (k.indexOf('.') == -1) ? '.00' : '00';
    return k.substring(0, k.indexOf('.') + 3);
}

/* Neue Funktion */
function check_datum(id) {
    // alert(id);
    datum = document.getElementById(id).value;
    var punkt1 = datum.substr(2, 1);
    var punkt2 = datum.substr(5, 1);
    var rest = datum.substr(6, datum.length);

    if (punkt1 != "." || punkt2 != "." || rest.length < 4) {
        alert("Ihre Datumseingabe ist fehlerhaft!\n\nBitte diese Formatierung einhalten TT.MM.YYYY");
        document.getElementById(id).value = '';
        document.getElementById(id).focus();
    }

}

function check_datum1(datum) {
    var l = datum.length;
    if (l != 10) {
        alert('Datumsformat überprüfen (dd.mm.yyyy)');
    } else {
        datum_arr = datum.split(".");
        var tag = datum_arr[0];
        var monat = datum_arr[1];
        var jahr = datum_arr[2];

        var AktuellesDatum = new Date();
        var akt_jahr = AktuellesDatum.getFullYear();

        diff = akt_jahr - jahr;
        if (diff >= 1) {
            alert('Datum prüfen - Jahreseingabe:' + jahr);

        }
        if (diff == 0) {
            /* alert('Datum ok') */
        }
        if (diff > 10) {
            document.getElementById("datum").value = '';
        }
    }
}

/* Positionen erfassen - Berechnung der Preise */
function refresh_preise() {
    menge = document.getElementById("menge").value;
    menge = menge.replace(",", ".");
    listenpreis = document.getElementById("lp").value;
    // listenpreis = runde_kaufm(listenpreis);
    listenpreis = listenpreis.replace(",", ".");
    rabatt_satz = document.getElementById("rabattsatz").value;
    rabatt_satz = rabatt_satz.replace(",", ".");
    mwst_satz = document.getElementById("mwst_satz").value;

    nettopreis = ((listenpreis / parseInt(100)) * (parseInt(100) - rabatt_satz));
    netto_gesamt = menge * nettopreis;
    netto_gesamt = netto_gesamt.toFixed(2);
    bruttopreis = (nettopreis / parseInt(100))
        * (parseInt(100) + parseInt(mwst_satz));
    bruttopreis = bruttopreis.toFixed(3);

    gesamt_brutto = menge * bruttopreis;
    gesamt_brutto = runde_kaufm(gesamt_brutto);

    /* Ausgabe */
    nettopreis = nettopreis.toFixed(3);
    document.getElementById("nettopreis").value = nettopreis;
    document.getElementById("bruttopreis").value = bruttopreis;
    document.getElementById("netto_gesamt").value = netto_gesamt;

    gesamt_brutto = gesamt_brutto.replace(".", ",");
    document.getElementById("brutto_gesamt").value = gesamt_brutto + ' EUR';

}

function listen_stueckpreis() {
    menge = document.getElementById("menge").value;
    menge = menge.replace(",", ".");
    netto_gesamt = document.getElementById("netto_gesamt").value;
    netto_gesamt = netto_gesamt.replace(",", ".");
    listenpreis = netto_gesamt / menge;
    listenpreis = runde_kaufm(listenpreis);
    listenpreis = listenpreis.replace(".", ",");
    document.getElementById("lp").value = listenpreis;

}

function listen_stueckpreis_rabatt() {
    nettopreis = document.getElementById("nettopreis").value;
    nettopreis = nettopreis.replace(",", ".");
    rabatt_satz = document.getElementById("rabattsatz").value;
    rabatt_satz = rabatt_satz.replace(",", ".");
    rabatt_satz = runde_kaufm(rabatt_satz);

    // prozent = nettopreis / parseInt(100);
    nachlass = parseInt(100) - rabatt_satz;
    prozent = nettopreis / nachlass;

    listenpreis = prozent * parseInt(100);
    // listenpreis = runde_kaufm(listenpreis);
    listenpreis = listenpreis.replace(".", ",");
    document.getElementById("lp").value = listenpreis;
}

// Rechnungsbuchung felder prüfen, kontoauszugsnummer, datum...
function felder_pruefen(myform) {
    datum = document.getElementById("datum").value;
    kontoauszugsnr = document.getElementById("kontoauszugsnr").value;
    kostenkonto = document.getElementById("kostenkonto").value;
    buchungsart = document.getElementById("buchungsart").value;

    if (buchungsart != 'Teilbetraege') {
        if (kostenkonto.length == '0') {
            alert('Kostenkonto wählen');
            document.getElementById("kostenkonto").focus();
        }
    }

    if (datum == '') {
        alert('Datum fehlt');
        document.getElementById("datum").focus();
    }

    else if (datum.length != '10') {
        alert('Datum prüfen');
        document.getElementById("datum").focus();
    }

    else if (buchungsart.length == '0') {
        alert('Buchungsbetrag wählen');
        document.getElementById("buchungsart").focus();
    }

    else if (kontoauszugsnr == '') {
        alert('Kontoauszugsnr fehlt');
        document.getElementById("kontoauszugsnr").focus();
    }

    else {
        document.myform.submit();
    }
}

function seite_aktualisieren(zeit) {
    setTimeout("location.reload(true);", zeit);
}

/* Element nach id löschen */
function kill_js_id(id) {
    var berlus_element = document.getElementById(id);
    berlus_element.parentNode.removeChild(berlus_element);
}

function check_pflicht_text(id) {

    text = document.getElementById(id).value;
    feldname = document.getElementById(id).name;
    if (text == '') {
        alert('Bitte füllen Sie das Pflichtfeld ' + feldname + ' aus!!!');
        document.getElementById(id).focus();
    }
}

function prozentbetrag(vollbetrag, prozente_feld, feld) {
    teilbetrag = (vollbetrag.replace(",", ".") / 100)
        * document.getElementById(prozente_feld).value.replace(",", ".");
    teilbetrag = teilbetrag.toFixed(2);
    var teilbetrag_netto = teilbetrag.replace(".", ",");
    document.getElementById(feld).value = teilbetrag_netto;
    // alert(teilbetrag);
}

function prozente(vollbetrag, teilbetrag, feld) {
    prozent = teilbetrag.replace(",", ".")
        / (vollbetrag.replace(",", ".") / 100)
    prozent_a = prozent.toFixed(4);
    var prozent_netto = prozent_a.replace(".", ",");
    document.getElementById(feld).value = prozent_netto;
    // alert(teilbetrag);
}

function check_felder_pflicht(str) {
    /*
     * arr = str.split("|"); laenge = arr.length; if laenge>0 { for(a=0;a<length;a++){
     * id = arr[a]; alert(id); } }
     */
}

var intAnzahl = 0; // Anzahl gesetzter Checkboxen
function count_auswahl(obj, max) {
    var intGesamt = max; // Gesamtanzahl Checkboxen, die gesetzt werden
    // dürfen
    // Falls die Checkbox angewählt wurde
    if (obj.checked == true) {
        intAnzahl++;
        // Falls die Gesamtanzahl überschritten wurde
        if (intAnzahl > intGesamt) {
            alert("Maximal " + intGesamt + "  Tage auswählen!");
            intAnzahl--; // Anzahl wieder zurücksetzen
            obj.checked = false; // Checkbox wieder abwählen
        }
        // Falls eine Checkbox wieder abgewählt wird
    } else {
        intAnzahl--; // Anzahl dekrementieren
    }

}

function bestaetigung() {
    abfrage = confirm("Sind Sie sicher???");
    return abfrage;
}

function remove_from_dd(list_id) {
    var liste = document.getElementById(list_id);
    for (var a = 0; a < liste.length; a++) {
        if (!liste[a].selected) {
            liste[a].remove();
        }
    }
    $('select').material_select();
}

function add2list(q_liste, z_liste) {
    q_liste = document.getElementById(q_liste);
    z_liste = document.getElementById(z_liste);
    var selected_text = q_liste.options[q_liste.selectedIndex].text;
    var selected_value = q_liste.options[q_liste.selectedIndex].value;

    var neuer_eintrag = document.createElement('option');
    neuer_eintrag.text = selected_text;
    neuer_eintrag.value = selected_value;
    neuer_eintrag.selected = true;

    if (z_liste.length > 0) {
        for (var a = 0; a < z_liste.length; a++) {
            z_liste[a].selected = true;
            if (z_liste[a].value == selected_value) {
                var vorhanden = true;
            }
        }
    }
    if (!vorhanden) {
        z_liste.add(neuer_eintrag, null); // nicht für iE
        z_liste.style.visibility = "visible";
        $('select').material_select();
    }

}

function staffel_berechnen(a_datum_feld, endjahr_feld, betrag_feld,
                           prozent_betrag, mwst) {
    // alert(a_datum, endjahr, betrag, prozent_betrag, mwst);
    a_datum = document.getElementById(a_datum_feld).value;
    a_datum_arr = a_datum.split('.');
    var a_jahr = a_datum_arr[2];
    // alert(a_jahr);
    endjahr = document.getElementById(endjahr_feld).value;
    jahre = endjahr - a_jahr;

    betrag = nummer_komma2punkt(document.getElementById(betrag_feld).value);

    proz_betrag = nummer_komma2punkt(document.getElementById(prozent_betrag).value);
    zeichen = proz_betrag.length;
    zeichen_l = proz_betrag.length - 1;
    // alert(zeichen);
    var prozent = proz_betrag.charAt(zeichen_l);
    var temp_betrag = betrag;
    for (a = a_jahr; a <= endjahr; a++) {

        if (prozent == '%') {
            nur_proz = proz_betrag.substring(0, zeichen_l);
            // alert(nur_proz);
            temp_betrag1 = parseFloat((temp_betrag) / 100)
                * (100 + parseFloat(nur_proz));
            temp_betrag = round(temp_betrag1, 2);
            alert(temp_betrag);
        } else {
            temp_betrag1 = parseFloat((temp_betrag) + parseFloat(proz_betrag));
            temp_betrag = round(temp_betrag1, 2);
            alert(temp_betrag);
        }
    }

    // alert(prozent);

    // alert(a_jahr+' '+ endjahr+ ' '+jahre+' '+betrag+' '+prozent);

}

function round(number, decimals) {
    // Rundet eine Zahl auf eine bestimmte Nachkommastelle
    return Math.round(number * Math.pow(10, decimals)) / Math.pow(10, decimals);
}
