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
 * @filesource   $HeadURL: http://192.168.2.52/svn/berlussimo_1/tags/02.11.2010 - Downloadversion 0.27/ajax/ajax.js $
 * @version      $Revision: 15 $
 * @modifiedby   $LastChangedBy: sivac $
 * @lastmodified $Date: 2011-07-07 10:41:33 +0200 (Do, 07 Jul 2011) $
 *
 */

function chunkString(str, length) {
    var re = new RegExp('.{1,' + length + '}', 'g');
    return str.match(re);

}

function iban_format(id) {

    str = document.getElementById(id).value;
    //new_str = chunkString(str, 4);
    //document.getElementById(id).value = new_str;
    //document.getElementById(id).value =  'ssss ';

}

// encodeURIComponent für UMLAUTE benutzen
function ajax_check_art(lieferant, artikel_nr) {
    //   alert(lieferant + ' h ' + artikel_nr);
    //artikel_nr = encodeURI(artikel_nr);
    //alert('ANGEKOMMEN:' + artikel_nr);
    //artikel_nr = artikel_nr.toString();
    //alert('ANGEKOMMEN STRING:' + artikel_nr);
    //erstellen der anfrage an php/mysql
    var req = null;

    try {
        req = new XMLHttpRequest();
    } catch (ms) {
        try {
            req = new ActiveXObject("Msxml2.XMLHTTP");
        } catch (nonms) {
            try {
                req = new ActiveXObject("Microsoft.XMLHTTP");
            } catch (failed) {
                req = null;
            }
        }
    }

    if (req == null)
        alert("Konnte Ajax-Objekt nicht erzeugen!");

    //Anfrageurl zusammenstellen aus lieferant und artnr
    if (!artikel_nr) {
        alert('Artikelnummer eingeben');
    } else {
        //alert (lieferant + ' '+ artikel_nr);
        var my_url = 'ajax/ajax_info.php?option=check_artikels&lieferant_id=' + lieferant + '&artikel_nr=' + artikel_nr;
        req.open("GET", my_url, true);
        //alert(my_url);
        //Beim Abschliessen der Anfrage wird diese Funktion ausgeführt
        req.onreadystatechange = function () {

            switch (req.readyState) {

                case 4:
                    if (req.status != 200) {
                        // alert("Fehler:"+req.status);
                        // alert(req.responseText);
                    } else {
                        //alert(req.responseText);
                        if (req.responseText && req.responseText != '') {
                            //  alert(req.responseText);
                            var erg_arr = req.responseText.split('|');
                            var art_nr = erg_arr[0];
                            var bez = erg_arr[1];
                            var lp = erg_arr[2];
                            var lp = runde_kaufm(lp);
                            var rab = erg_arr[3];
                            var einheit = erg_arr[4];
                            var mwst_satz = erg_arr[5];
                            var pos_skonto = erg_arr[6];

                            document.getElementById("textf_artikelnr").value = art_nr;
                            document.getElementById("bezeichnung").value = bez;
                            document.getElementById("lp").value = lp.replace(".", ",");
                            document.getElementById("rabattsatz").value = rab.replace(".", ",");
                            document.getElementById("mwst_satz").value = mwst_satz.replace(".", ",");
                            document.getElementById("einheit").value = einheit;
                            document.getElementById("pos_skonto").value = pos_skonto;
                            refresh_preise();
                            anzahl_einheiten = document.getElementById("einheit").length;
                            for (var i = 0; i < anzahl_einheiten; i++) {
                                if (document.getElementById("einheit")[i].value == einheit) {
                                    document.getElementById("einheit")[i].focus();
                                }
                            }
                            Materialize.toast('Artikel übernommen, Menge & Preise prüfen', 4000);
                            document.getElementById('artikel_vorhanden').innerHTML = '';
                        } else {
                            Materialize.toast(artikel_nr + ' L: ' + lieferant + ' Artikel nicht vorhanden', 4000);
                            document.getElementById("textf_artikelnr").value = artikel_nr;
                            document.getElementById("bezeichnung").value = '';
                            document.getElementById("lp").value = '';
                            document.getElementById("rabattsatz").value = '0.00';
                            document.getElementById("nettopreis").value = '';
                            document.getElementById("bruttopreis").value = '';
                            document.getElementById("netto_gesamt").value = '';
                            document.getElementById("brutto_gesamt").value = '';
                            document.getElementById("menge").focus();
                        }
                        Materialize.updateTextFields();
                    }
                    break;

                default:
                    return false;
                    break;
            }
        };
        req.setRequestHeader("Content-Type", "application/x-www-form-urlencoded; charset=UTF-8");
        req.send(null);
    }//ende else keine artikel nr
}

function display_positionen(belegnr) {
    //erstellen der anfrage an php/mysql
    var req = null;

    try {
        req = new XMLHttpRequest();
    } catch (ms) {
        try {
            req = new ActiveXObject("Msxml2.XMLHTTP");
        } catch (nonms) {
            try {
                req = new ActiveXObject("Microsoft.XMLHTTP");
            } catch (failed) {
                req = null;
            }
        }
    }

    if (req == null)
        alert("Konnte Ajax-Objekt nicht erzeugen!");

    //Anfrageurl zusammenstellen aus lieferant und artnr
    //alert('BELEG:'+belegnr);
    var my_url = 'ajax/ajax_info.php?option=display_positionen&belegnr=' + belegnr;
    req.open("GET", my_url, true);

    //Beim Abschliessen der Anfrage wird diese Funktion ausgef�hrt
    req.onreadystatechange = function () {

        switch (req.readyState) {

            case 4:
                if (req.status != 200) {
                    //alert("Fehler:"+req.status);
                } else {
                    //     alert(req.responseText);
                    if (req.responseText) {
                        document.getElementById('positionen').innerHTML = req.responseText;
                    } else {
                        document.getElementById('positionen').innerHTML = 'Keine Positionen gespeichert';
                    }
                }
                break;

            default:
                return false;
                break;
        }
    };

    // req.setRequestHeader("Content-Type",
    // "application/x-www-form-urlencoded");
    req.send(null);
}

function position_speichern_testOK() {
    var http = new XMLHttpRequest();
    var url = "ajax/ajax_info.php";
    //var params = encodeURIComponent('option=insert_position&belegnr=6967&artikel_nr=routeajax&lieferant_id=1&menge=1000&einheit=Beutel&listenpreis=1000&rabatt=50&pos_mwst=19&g_netto=1515&bez=post&bezeichnung&pos_skonto=2');
    var params = decodeURI('option=insert_position&belegnr=6967&artikel_nr=routeajax&lieferant_id=1&menge=1000&einheit=Beutel&listenpreis=1000&rabatt=50&pos_mwst=19&g_netto=1515&bez=' + encodeURIComponent('post & bezeichnung & m&ms #+!2 �������') + '&pos_skonto=2');
    alert(params);
    http.open("POST", url, true);

    //Send the proper header information along with the request
    http.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    http.setRequestHeader("Content-length", params.length);
    http.setRequestHeader("Connection", "close");

    http.onreadystatechange = function () {//Call a function when the state changes.
        if (http.readyState == 4 && http.status == 200) {
            alert(http.responseText);
        }
    }
    http.send(params);

}

function position_speichern() {
    //erstellen der anfrage an php/mysql
    var req = null;

    try {
        req = new XMLHttpRequest();
    } catch (ms) {
        try {
            req = new ActiveXObject("Msxml2.XMLHTTP");
        } catch (nonms) {
            try {
                req = new ActiveXObject("Microsoft.XMLHTTP");
            } catch (failed) {
                req = null;
            }
        }
    }

    if (req == null)
        alert("Konnte Ajax-Objekt nicht erzeugen!");

    //Anfrageurl zusammenstellen aus lieferant und artnr
    belegnr = document.getElementById("belegnr").value;
    lieferant_id = document.getElementById("lieferant_id").value;
    artikel_nr = document.getElementById("textf_artikelnr").value;

    menge = document.getElementById("menge").value;
    menge = menge.replace(",", ".");
    einheit = document.getElementById("einheit").value;
    bez = document.getElementById("bezeichnung").value;

    listenpreis = document.getElementById("lp").value;
    listenpreis = listenpreis.replace(",", ".");

    rabatt_satz = document.getElementById("rabattsatz").value;
    rabatt_satz = rabatt_satz.replace(",", ".");

    netto_gesamt = document.getElementById("netto_gesamt").value;
    netto_gesamt = netto_gesamt.replace(",", ".");

    mwst_satz = document.getElementById("mwst_satz").value;
    mwst_satz = mwst_satz.replace(",", ".");

    pos_skonto = document.getElementById("pos_skonto").value;
    pos_skonto = pos_skonto.replace(",", ".");

    if (artikel_nr == '') {
        alert('Artikelnummer fehlt');
        document.getElementById("textf_artikelnr").focus();
    } else if (menge == '') {
        alert('Menge fehlt');
        document.getElementById("menge").focus();
    } else if (bez == '') {
        alert('Bezeichnung fehlt');
        document.getElementById("bezeichnung").focus();
    } else if (listenpreis == '') {
        alert('Listenpreis fehlt');
        document.getElementById("lp").focus();
    } else if (mwst_satz == '') {
        alert('MwSt in % fehlt');
        document.getElementById("mwst_satz").focus();
    } else if (rabatt_satz == '') {
        alert('Rabatt in % fehlt');
        document.getElementById("rabattsatz").focus();
    } else if (pos_skonto == '') {
        alert('Skonto zum Artikel fehlt %');
        document.getElementById("pos_skonto").focus();
    } else if (netto_gesamt == '') {
        alert('Netto gesamt fehlt');
        document.getElementById("netto_gesamt").focus();
    } else {

        var my_url = 'ajax/ajax_info.php?option=insert_position&belegnr=' + encodeURIComponent(belegnr) + '&artikel_nr=' + encodeURIComponent(artikel_nr) + '&lieferant_id=' + encodeURIComponent(lieferant_id) + '&menge=' + encodeURIComponent(menge) + '&einheit=' + encodeURIComponent(einheit) + '&listenpreis=' + encodeURIComponent(listenpreis) + '&rabatt=' + encodeURIComponent(rabatt_satz) + '&pos_mwst=' + encodeURIComponent(mwst_satz) + '&g_netto=' + encodeURIComponent(netto_gesamt) + '&bez=' + encodeURIComponent(bez) + '&pos_skonto=' + encodeURIComponent(pos_skonto);
        //var my_url='ajax/ajax_info.php';
        //var daten = 'option=insert_position&belegnr=' + belegnr + '&artikel_nr=' + artikel_nr + '&lieferant_id=' + lieferant_id+ '&menge=' + menge+ '&einheit=' + einheit+ '&listenpreis=' + listenpreis+ '&rabatt=' + rabatt_satz+ '&pos_mwst=' + mwst_satz+ '&g_netto=' + netto_gesamt+'&bez='+bez.toString()+'&pos_skonto='+pos_skonto;
        //alert(my_url);
        req.open("GET", my_url, true);

        //Beim Abschliessen der Anfrage wird diese Funktion ausgeführt
        req.onreadystatechange = function () {

            switch (req.readyState) {

                case 4:
                    if (req.status != 200) {
                        //  alert("Fehler:"+req.status);

                    } else {

                        //     alert(req.responseText);
                        //document.getElementById('positionen').innerHTML = 'Nicht gespeichert';
                        display_positionen(belegnr);
                        document.getElementById("suche_artikelnr").value = '';
                        document.getElementById("suche_artikelnr").focus();
                        document.getElementById("textf_artikelnr").value = '';
                        document.getElementById("bezeichnung").value = '';
                        document.getElementById("lp").value = '';
                        document.getElementById("rabattsatz").value = '';
                        document.getElementById("nettopreis").value = '';
                        document.getElementById("bruttopreis").value = '';
                        document.getElementById("netto_gesamt").value = '';
                        document.getElementById("brutto_gesamt").value = '';
                        document.getElementById("menge").value = '';
                        document.getElementById("pos_skonto").value = '';
                        sprung_nach_unten();

                    }
                    break;

                default:
                    return false;
                    break;
            }
        };

        req.setRequestHeader("Content-Type", "application/x-www-form-urlencoded;charset=UTF-8");
        req.send(null);
        //      req.send(daten);
    }
    //ende else felder leer

}

function position_aendern() {
    //erstellen der anfrage an php/mysql
    var req = null;

    try {
        req = new XMLHttpRequest();
    } catch (ms) {
        try {
            req = new ActiveXObject("Msxml2.XMLHTTP");
        } catch (nonms) {
            try {
                req = new ActiveXObject("Microsoft.XMLHTTP");
            } catch (failed) {
                req = null;
            }
        }
    }

    if (req == null)
        alert("Konnte Ajax-Objekt nicht erzeugen!");

    //Anfrageurl zusammenstellen aus lieferant und artnr
    pos = document.getElementById("pos").value;
    belegnr = document.getElementById("belegnr").value;
    lieferant_id = document.getElementById("lieferant_id").value;
    artikel_nr = document.getElementById("textf_artikelnr").value;

    menge = document.getElementById("menge").value;
    menge = menge.replace(",", ".");
    einheit = document.getElementById("einheit").value;
    bez = document.getElementById("bezeichnung").value;

    listenpreis = document.getElementById("lp").value;
    listenpreis = listenpreis.replace(",", ".");

    rabatt_satz = document.getElementById("rabattsatz").value;
    rabatt_satz = rabatt_satz.replace(",", ".");

    netto_gesamt = document.getElementById("netto_gesamt").value;
    netto_gesamt = netto_gesamt.replace(",", ".");

    mwst_satz = document.getElementById("mwst_satz").value;
    mwst_satz = mwst_satz.replace(",", ".");

    pos_skonto = document.getElementById("pos_skonto").value;
    pos_skonto = pos_skonto.replace(",", ".");

    if (artikel_nr == '') {
        alert('Artikelnummer fehlt');
        document.getElementById("textf_artikelnr").focus();
    } else if (menge == '') {
        alert('Menge fehlt');
        document.getElementById("menge").focus();
    } else if (bez == '') {
        alert('Bezeichnung fehlt');
        document.getElementById("bezeichnung").focus();
    } else if (listenpreis == '') {
        alert('Listenpreis fehlt');
        document.getElementById("lp").focus();
    } else if (mwst_satz == '') {
        alert('MwSt in % fehlt');
        document.getElementById("mwst_satz").focus();
    } else if (rabatt_satz == '') {
        alert('Rabatt in % fehlt');
        document.getElementById("rabattsatz").focus();
    } else if (pos_skonto == '') {
        alert('Skonto in % fehlt');
        document.getElementById("pos_skonto").focus();
    } else if (netto_gesamt == '') {
        alert('Netto gesamt fehlt');
        document.getElementById("netto_gesamt").focus();
    } else {

        var my_url = 'ajax/ajax_info.php?option=aendern_position&belegnr=' + belegnr + '&pos=' + pos + '&artikel_nr=' + artikel_nr + '&lieferant_id=' + lieferant_id + '&menge=' + menge + '&einheit=' + einheit + '&listenpreis=' + listenpreis + '&rabatt=' + rabatt_satz + '&pos_mwst=' + mwst_satz + '&g_netto=' + netto_gesamt + '&bez=' + bez + '&pos_skonto=' + pos_skonto;
        req.open("GET", my_url, true);

        //Beim Abschliessen der Anfrage wird diese Funktion ausgef�hrt
        req.onreadystatechange = function () {

            switch (req.readyState) {

                case 4:
                    if (req.status != 200) {
                        //     alert("Fehler:"+req.status);
                    } else {
                        //     alert(req.responseText);
                        if (req.responseText) {
                            //document.getElementById('positionen').innerHTML = req.responseText;
                            //display_positionen(belegnr);
                            document.location.href = "/rechnungen?option=positionen_erfassen&belegnr=" + belegnr;
                        } else {
                            //document.getElementById('positionen').innerHTML = 'Nicht gespeichert';
                            display_positionen(belegnr);
                            document.getElementById("suche_artikelnr").value = '';
                            document.getElementById("suche_artikelnr").focus();
                            document.getElementById("textf_artikelnr").value = '';
                            document.getElementById("bezeichnung").value = '';
                            document.getElementById("lp").value = '';
                            document.getElementById("rabattsatz").value = '';
                            document.getElementById("nettopreis").value = '';
                            document.getElementById("bruttopreis").value = '';
                            document.getElementById("netto_gesamt").value = '';
                            document.getElementById("brutto_gesamt").value = '';
                            document.getElementById("menge").value = '';
                        }
                    }
                    break;

                default:
                    return false;
                    break;
            }
        };

        // req.setRequestHeader("Content-Type",
        //"application/x-www-form-urlencoded");
        req.send(null);
    }
    //ende else felder leer

}

function schreibe_pos_in_div() {
    //schreibe die antwort in den div container mit der id content
    var inhalt = document.getElementById('positionen').innerHTML;
    pos = document.getElementById("pos").value;
    artikel_nr = document.getElementById("textf_artikelnr").value;
    bezeichnung = document.getElementById("bezeichnung").value;
    /*document.getElementById("lp").value = '';
     document.getElementById("rabattsatz").value = '';
     document.getElementById("nettopreis").value = '';
     document.getElementById("bruttopreis").value = '';
     document.getElementById("netto_gesamt").value = '';
     document.getElementById("brutto_gesamt").value = '';
     document.getElementById("menge").focus();
     */

    document.getElementById('positionen').innerHTML = inhalt + '<strong>' + pos + '. ' + artikel_nr + '   ' + bezeichnung + '</strong><br>';
}

function element_entfernen(element) {
    if (element) {
        var haupt_element = element.parentNode;
        if (haupt_element)
            haupt_element.removeChild(element);
    }
}

/*Rechnung buchen, aktiviert und deaktiviert kostenkonto_dropdown, sowie zeigt Kostenträger, je nach Auswahl der Buchungsart, an*/

function buchungs_infos(auswahl) {
    if (auswahl == '') {
        auswahl = 'Teilbetraege';
    }
    belegnr = document.getElementById("belegnr").value;
    var buchungsbetrag = document.getElementById("buchungsbetrag").value;
    info_feld = document.getElementById("info_feld_kostentraeger");
    typ = document.getElementById("kostentraeger_typ").value;
    id = document.getElementById("kostentraeger_id").value;
    //alert(buchungsbetrag);
    //alert (auswahl);
    if (auswahl == 'Teilbetraege') {
        /*label dropdownfeld*/
        //document.getElementById("label_kostenkonto").style.visibility = 'hidden';
        /*dropdownfeld*/
        document.getElementById("kostenkonto").style.visibility = 'hidden';
        get_kontierung_infos(belegnr, buchungsbetrag);
    } else {
        //document.getElementById("label_kostenkonto").style.visibility = 'visible';
        document.getElementById("kostenkonto").style.visibility = 'visible';
        //alert('Kostenkonto wählen');
        document.getElementById("kostenkonto").focus();
        kostentrager_finden(typ, id);
        info_feld_text = document.getElementById("info_feld_kostentraeger").innerHTML;
        //document.getElementById("info_feld_kostentraeger").innerHTML = info_feld_text + buchungsbetrag + 'wird gebucht';

        info_feld.innerHTML == 'wird gebucht';
        //alert(info_feld_text);
    }
}

function get_kontierung_infos(belegnr, buchungsbetrag) {
    var req = null;
    try {
        req = new XMLHttpRequest();
    } catch (ms) {
        try {
            req = new ActiveXObject("Msxml2.XMLHTTP");
        } catch (nonms) {
            try {
                req = new ActiveXObject("Microsoft.XMLHTTP");
            } catch (failed) {
                req = null;
            }
        }
    }

    if (req == null)
        alert("Konnte Ajax-Objekt nicht erzeugen!");

    //Anfrageurl zusammenstellen aus lieferant und artnr
    var my_url = 'ajax/ajax_info.php?option=get_kontierungs_infos&belegnr=' + belegnr + '&buchungsbetrag=' + buchungsbetrag;
    req.open("GET", my_url, true);

    //Beim Abschliessen der Anfrage wird diese Funktion ausgef�hrt
    req.onreadystatechange = function () {

        switch (req.readyState) {

            case 4:
                if (req.status != 200) {
                    //alert("Fehler:"+req.status);
                } else {
                    //alert(req.responseText);
                    if (req.responseText) {
                        info_feld = document.getElementById("info_feld_kostentraeger");
                        info_feld.innerHTML = req.responseText;
                    } else {
                        info_feld = document.getElementById("info_feld_kostentraeger");
                        info_feld.innerHTML = 'FEHLER AJAX.JS ZEILE 537';
                    }
                }
                break;

            default:
                return false;
                break;
        }
    };

    //req.setRequestHeader("Content-Type",
    //"application/x-www-form-urlencoded");
    req.send(null);

}

function kostentrager_finden(typ, id) {
    var req = null;
    try {
        req = new XMLHttpRequest();
    } catch (ms) {
        try {
            req = new ActiveXObject("Msxml2.XMLHTTP");
        } catch (nonms) {
            try {
                req = new ActiveXObject("Microsoft.XMLHTTP");
            } catch (failed) {
                req = null;
            }
        }
    }

    if (req == null)
        alert("Konnte Ajax-Objekt nicht erzeugen!");

    //Anfrageurl zusammenstellen
    var my_url = 'ajax/ajax_info.php?option=get_kostentraeger_name&typ=' + typ + '&id=' + id;
    req.open("GET", my_url, true);

    //Beim Abschliessen der Anfrage wird diese Funktion ausgeführt
    req.onreadystatechange = function () {

        switch (req.readyState) {

            case 4:
                if (req.status != 200) {
                    //alert("Fehler:"+req.status);
                } else {
                    //alert(req.responseText);
                    if (req.responseText) {
                        info_feld = document.getElementById("info_feld_kostentraeger");

                        info_feld.innerHTML = 'Kostenträger Rechnungsempfänger:<br><b>' + req.responseText + '</b>';
                    } else {
                        info_feld = document.getElementById("info_feld_kostentraeger");
                        info_feld.innerHTML = 'FEHLER AJAX.JS ZEILE 596';
                    }
                }
                break;

            default:
                return false;
                break;
        }
    };
    req.send(null);
}

function get_detail_ukats(kat_id) {
    xmlHttp = GetXmlHttpObject();
    if (xmlHttp == null) {
        alert("Browser does not support HTTP Request");
        return
    }
    var url = "ajax/ajax_info.php";
    url = url + "?option=get_detail_ukats" + "&kat_id=" + kat_id;
    xmlHttp.onreadystatechange = list_detail_ukats_drop;
    xmlHttp.open("GET", url, true);
    xmlHttp.send(null);
}

function list_detail_ukats_drop() {

    if (xmlHttp.readyState == 4 || xmlHttp.readyState == "complete") {

        var ukats_arr = xmlHttp.responseText.split(';');
        var ukats_feld = document.getElementById("detail_ukat");
        var detail_inhalt_feld = document.getElementById("inhalt");
        ukats_feld.length = 0;
        var anzahl = ukats_arr.length;
        if (anzahl > 1) {
            addOption(ukats_feld, 'Bitte wählen', 'nooption');
            for (var i = 0; i < anzahl - 1; i++) {
                var kd = ukats_arr[i];
                addOption(ukats_feld, kd, kd);
            }
            detail_inhalt_feld.style.visibility = 'hidden';

        } else {
            addOption(ukats_feld, 'Manuell eintragen', 'nooption');
            detail_inhalt_feld.style.visibility = 'visible';
        }
        $('select').material_select();
    }
}

var t_id;
function autovervoll_with_delay(lieferant_id, string) {
    clearTimeout(t_id);
    t_id = setTimeout(autovervoll, 500, lieferant_id, string);
}

function autovervoll(lieferant_id, string) {
    artikelvorhanden_feld = document.getElementById('artikel_vorhanden');

    //erstellen der anfrage an php/mysql
    var req = null;

    try {
        req = new XMLHttpRequest();
    } catch (ms) {
        try {
            req = new ActiveXObject("Msxml2.XMLHTTP");
        } catch (nonms) {
            try {
                req = new ActiveXObject("Microsoft.XMLHTTP");
            } catch (failed) {
                req = null;
            }
        }
    }

    if (req == null)
        alert("Konnte Ajax-Objekt nicht erzeugen!");

    //Anfrageurl zusammenstellen aus lieferant und artnr
    if (!lieferant_id && !string) {
        alert('Lieferant oder Artikelnr nicht eingegeben, AJAX FEHLER 5633');
    } else {
        var my_url = 'ajax/ajax_info.php?option=autovervollst2&l_id=' + lieferant_id + '&string=' + encodeURIComponent(string);
        req.open("GET", my_url, true);

        //Beim Abschliessen der Anfrage wird diese Funktion ausgeführt
        req.onreadystatechange = function () {

            switch (req.readyState) {

                case 4:
                    if (req.status != 200) {
                        //alert("Fehler:"+req.status);
                        //alert(req.responseText);
                    } else {
                        // alert(req.responseText);
                        if (req.responseText) {
                            var erg_arr = req.responseText.split('||');
                            anzahl_ergebnisse = erg_arr.length;
                            var link = '<table>';

                            for (a = 0; a < (anzahl_ergebnisse - 1); a++) {
                                var artikelzeile = erg_arr[a];
                                var artikel_zeile_arr = artikelzeile.split('??');

                                var art_nr = artikel_zeile_arr[0];
                                art_nr = art_nr.toString();
                                art_nr = art_nr.replace(' ', '');
                                art_nr = art_nr.replace('\n', '');

                                bez = artikel_zeile_arr[1];
                                preis = artikel_zeile_arr[2];
                                lieferant_bez = artikel_zeile_arr[3];
                                n_lieferant_id = artikel_zeile_arr[4];

                                //art_link = "<tr class=\"zeile2\"><td><a class=\"artikel_vorhanden\"  onClick=\"ajax_check_art('" + n_lieferant_id + "','"+ art_nr + "');\">"+art_nr+"</a></td><td>"+ bez + "</td><td>"+ preis +"</td><td>" + lieferant_bez + '</td></tr>';
                                art_link = "<tr class=\"zeile2\"><td><a class=\"artikel_vorhanden\"  onClick=\"ajax_check_art('";
                                art_link += n_lieferant_id;
                                art_link += "','";
                                art_link += art_nr;
                                art_link += "');\">";
                                art_link += art_nr;
                                art_link += "</a></td><td>" + bez + "</td><td>" + preis + "</td><td>" + lieferant_bez + '</td></tr>';

                                link = link + art_link;

                                //alert('OK');
                            }
                            link = link + '</table>';
                            artikelvorhanden_feld.innerHTML = '';
                            artikelvorhanden_feld.innerHTML = link;

                        } else {
                            artikelvorhanden_feld.innerHTML = '<b>Keine übereinstimmung</b>';

                        }
                    }
                    break;

                default:
                    return false;
                    break;
            }
        };

        req.setRequestHeader("Content-Type", "application/x-www-form-urlencoded;charset=UTF-8");
        req.send(null);
    }//ende else keine artikel nr
}

function artikel2feld(feldname, wert) {
    document.getElementById("suche_artikelnr").value = wert;
}

function sprung_nach_unten() {
    window.scrollTo(0, 100000);
}

/*Betriebskosten/Nebenkosten*/
function buchung_hinzu(buchung_id, konto_id, profil_id) {
    var req = null;
    try {
        req = new XMLHttpRequest();
    } catch (ms) {
        try {
            req = new ActiveXObject("Msxml2.XMLHTTP");
        } catch (nonms) {
            try {
                req = new ActiveXObject("Microsoft.XMLHTTP");
            } catch (failed) {
                req = null;
            }
        }
    }

    if (req == null)
        alert("Konnte Ajax-Objekt nicht erzeugen!");

    //Anfrageurl zusammenstellen
    var my_url = 'ajax/ajax_info.php?option=buchung_hinzu&buchung_id=' + buchung_id + '&bk_konto_id=' + konto_id + '&profil_id=' + profil_id;
    req.open("GET", my_url, true);

    //Beim Abschliessen der Anfrage wird diese Funktion ausgeführt
    req.onreadystatechange = function () {

        switch (req.readyState) {

            case 4:
                if (req.status != 200) {
                    alert("Fehler:" + req.status);
                } else {
                    // alert(req.responseText);
                    if (req.responseText) {
                        reload_me();
                    } else {
                        reload_me();
                    }
                }
                break;

            default:
                return false;
                break;
        }
    };
    req.send(null);
}

function buchung_hinzu_still(buchung_id, konto_id, profil_id) {
    var req = null;
    try {
        req = new XMLHttpRequest();
    } catch (ms) {
        try {
            req = new ActiveXObject("Msxml2.XMLHTTP");
        } catch (nonms) {
            try {
                req = new ActiveXObject("Microsoft.XMLHTTP");
            } catch (failed) {
                req = null;
            }
        }
    }

    if (req == null)
        alert("Konnte Ajax-Objekt nicht erzeugen!");

    //Anfrageurl zusammenstellen
    var my_url = 'ajax/ajax_info.php?option=buchung_hinzu&buchung_id=' + buchung_id + '&bk_konto_id=' + konto_id + '&profil_id=' + profil_id;
    req.open("GET", my_url, true);

    //Beim Abschliessen der Anfrage wird diese Funktion ausgeführt
    req.onreadystatechange = function () {

        switch (req.readyState) {

            case 4:
                if (req.status != 200) {
                    alert("Request Status: " + req.status + " Request State: " + req.readyState);
                } else {
                    // alert(req.responseText);
                    if (req.responseText) {
                        //reload_me();
                    } else {
                        //reload_me();
                    }
                }
                break;

            default:
                return false;
                break;
        }
    };
    req.send(null);
}

function buchungen_hinzu(name, konto_id, profil_id) {
    //alert(name+konto_id,profil_id);
    var check_elem = document.getElementsByName(name);
    var anz = check_elem.length;
    //alert(anz);
    var gesetzt = 0;
    if (anz != null) {

        for (i = 0; i < anz; i++) {
            if (check_elem[i].checked) {
                var buchung_id = check_elem[i].value;
                buchung_hinzu_still(buchung_id, konto_id, profil_id);
                gesetzt++;
            }
        }//end for

    }

    if (gesetzt = 0) {
        alert("Häkchen setzen bitte!");
    }
}

/*Betriebskosten/Nebenkosten*/
function buchung_raus(bk_be_id, konto_id, profil_id) {
    var req = null;
    try {
        req = new XMLHttpRequest();
    } catch (ms) {
        try {
            req = new ActiveXObject("Msxml2.XMLHTTP");
        } catch (nonms) {
            try {
                req = new ActiveXObject("Microsoft.XMLHTTP");
            } catch (failed) {
                req = null;
            }
        }
    }

    if (req == null)
        alert("Konnte Ajax-Objekt nicht erzeugen!");

    //Anfrageurl zusammenstellen
    var my_url = 'ajax/ajax_info.php?option=buchung_raus&bk_be_id=' + bk_be_id + '&bk_konto_id=' + konto_id + '&profil_id=' + profil_id;
    req.open("GET", my_url, true);

    //Beim Abschliessen der Anfrage wird diese Funktion ausgeführt
    req.onreadystatechange = function () {

        switch (req.readyState) {

            case 4:
                if (req.status != 200) {
                    //alert("Fehler:"+req.status);
                } else {
                    //alert(req.responseText);
                    if (req.responseText) {
                        reload_me();
                    } else {
                        reload_me();
                    }
                }
                break;

            default:
                return false;
                break;
        }
    };
    req.send(null);
}

/*Betriebskosten/Nebenkosten*/
function konto_hinzu(konto_id, profil_id) {
    var req = null;
    try {
        req = new XMLHttpRequest();
    } catch (ms) {
        try {
            req = new ActiveXObject("Msxml2.XMLHTTP");
        } catch (nonms) {
            try {
                req = new ActiveXObject("Microsoft.XMLHTTP");
            } catch (failed) {
                req = null;
            }
        }
    }

    if (req == null)
        alert("Konnte Ajax-Objekt nicht erzeugen!");

    //Anfrageurl zusammenstellen
    var my_url = 'ajax/ajax_info.php?option=konto_hinzu' + '&bk_konto_id=' + konto_id + '&profil_id=' + profil_id;
    req.open("GET", my_url, true);

    //Beim Abschliessen der Anfrage wird diese Funktion ausgeführt
    req.onreadystatechange = function () {

        switch (req.readyState) {

            case 4:
                if (req.status != 200) {
                    //alert("Fehler:"+req.status);
                } else {
                    //alert(req.responseText);
                    if (req.responseText) {
                        //reload_me();   hier nicht, sonst sendet doppelt
                    } else {
                        reload_me();
                    }
                }
                break;

            default:
                return false;
                break;
        }
    };
    req.send(null);
}

/*Betriebskosten/Nebenkosten*/
function konto_raus(konto_id, profil_id) {
    if (bestaetigung()) {
        var req = null;
        try {
            req = new XMLHttpRequest();
        } catch (ms) {
            try {
                req = new ActiveXObject("Msxml2.XMLHTTP");
            } catch (nonms) {
                try {
                    req = new ActiveXObject("Microsoft.XMLHTTP");
                } catch (failed) {
                    req = null;
                }
            }
        }

        if (req == null)
            alert("Konnte Ajax-Objekt nicht erzeugen!");

        //Anfrageurl zusammenstellen
        var my_url = 'ajax/ajax_info.php?option=konto_raus' + '&bk_konto_id=' + konto_id + '&profil_id=' + profil_id;
        req.open("GET", my_url, true);

        //Beim Abschliessen der Anfrage wird diese Funktion ausgef�hrt
        req.onreadystatechange = function () {

            switch (req.readyState) {

                case 4:
                    if (req.status != 200) {
                        //alert("Fehler:"+req.status);
                    } else {
                        alert(req.responseText);
                        reload_me();
                        if (req.responseText) {
                            //reload_me();
                        } else {
                            reload_me();
                        }
                    }
                    break;

                default:
                    return false;
                    break;
            }
        };
        req.send(null);
    } else {
        alert('Keine Veränderung');
    }
}

function element_mini(id) {
    document.getElementById(id).style.display = "none";

}

function element_maxi(id) {
    document.getElementById(id).style.display = "";

}

function reload_me() {
    window.location.reload();
}

function register_var(vari, value) {
    var req = null;
    try {
        req = new XMLHttpRequest();
    } catch (ms) {
        try {
            req = new ActiveXObject("Msxml2.XMLHTTP");
        } catch (nonms) {
            try {
                req = new ActiveXObject("Microsoft.XMLHTTP");
            } catch (failed) {
                req = null;
            }
        }
    }

    if (req == null)
        alert("Konnte Ajax-Objekt nicht erzeugen!");

    //Anfrageurl zusammenstellen
    var my_url = 'ajax/ajax_info.php?option=register_var' + '&var=' + vari + '&value=' + value;
    req.open("GET", my_url, true);

    //Beim Abschliessen der Anfrage wird diese Funktion ausgeführt
    req.onreadystatechange = function () {

        switch (req.readyState) {

            case 4:
                if (req.status != 200) {
                    //alert("Fehler:"+req.status);
                } else {
                    // alert(req.responseText);
                    if (req.responseText) {
                        //alert(req.responseText);
                    } else {
                        //alert(req.responseText);
                    }
                }
                break;

            default:
                return false;
                break;
        }
    };
    req.send(null);

}

function get_eigentuemer(quelle, ziel) {
    q_liste = document.getElementById(quelle);
    z_liste = document.getElementById(ziel);
    var selected_text = q_liste.options[q_liste.selectedIndex].text;
    var selected_value = q_liste.options[q_liste.selectedIndex].value;
    z_liste.value = '';
    //alert(selected_value);
    var req = null;
    try {
        req = new XMLHttpRequest();
    } catch (ms) {
        try {
            req = new ActiveXObject("Msxml2.XMLHTTP");
        } catch (nonms) {
            try {
                req = new ActiveXObject("Microsoft.XMLHTTP");
            } catch (failed) {
                req = null;
            }
        }
    }

    if (req == null)
        alert("Konnte Ajax-Objekt nicht erzeugen!");

    //Anfrageurl zusammenstellen
    var my_url = 'ajax/ajax_info.php?option=get_eigentuemer' + '&einheit_id=' + selected_value;
    req.open("GET", my_url, true);

    //Beim Abschliessen der Anfrage wird diese Funktion ausgeführt
    req.onreadystatechange = function () {

        switch (req.readyState) {

            case 4:
                if (req.status == 200) {
                    if (req.responseText) {
                        z_liste.value = req.responseText;
                    }
                }
                break;

            default:
                return false;
                break;
        }
    };
    req.send(null);

}

function get_iban_bic(kto_feld, blz_feld) {
    kto = document.getElementById(kto_feld).value;
    blz = document.getElementById(blz_feld).value;

    var req = null;
    try {
        req = new XMLHttpRequest();
    } catch (ms) {
        try {
            req = new ActiveXObject("Msxml2.XMLHTTP");
        } catch (nonms) {
            try {
                req = new ActiveXObject("Microsoft.XMLHTTP");
            } catch (failed) {
                req = null;
            }
        }
    }

    if (req == null)
        alert("Konnte Ajax-Objekt nicht erzeugen!");

    //Anfrageurl zusammenstellen
    var my_url = 'ajax/ajax_info.php?option=get_iban_bic' + '&kto=' + kto + '&blz=' + blz;
    req.open("GET", my_url, true);

    //Beim Abschliessen der Anfrage wird diese Funktion ausgeführt
    req.onreadystatechange = function () {

        switch (req.readyState) {

            case 4:
                if (req.status == 200) {
                    if (req.responseText) {
                        iban_bic_arr = req.responseText.split('|');
                        iban = iban_bic_arr[0];
                        bic = iban_bic_arr[1];
                        bankname = iban_bic_arr[2];
                        document.getElementById('iban').value = iban;
                        document.getElementById('bic').value = bic;
                        document.getElementById('institut').value = bankname;
                        Materialize.updateTextFields();
                    }
                }
                break;

            default:
                return false;
                break;
        }
    };
    req.send(null);

}

function get_wp_vorjahr_wert(objekt_id, vorjahr, kostenkonto, ziel_feld) {
    //alert (objekt_id + ' ' + vorjahr + ' ' + kostenkonto + ' ' + ziel_feld);
    var req = null;
    try {
        req = new XMLHttpRequest();
    } catch (ms) {
        try {
            req = new ActiveXObject("Msxml2.XMLHTTP");
        } catch (nonms) {
            try {
                req = new ActiveXObject("Microsoft.XMLHTTP");
            } catch (failed) {
                req = null;
            }
        }
    }

    if (req == null)
        alert("Konnte Ajax-Objekt nicht erzeugen!");

    //Anfrageurl zusammenstellen
    var my_url = 'ajax/ajax_info.php?option=get_wp_vorjahr_wert' + '&objekt_id=' + objekt_id + '&vorjahr=' + vorjahr + '&kostenkonto=' + kostenkonto;
    req.open("GET", my_url, true);

    //Beim Abschliessen der Anfrage wird diese Funktion ausgeführt
    req.onreadystatechange = function () {

        switch (req.readyState) {

            case 4:
                if (req.status != 200) {
                    //  alert("Fehler:"+req.status);
                } else {

                    if (req.responseText) {
                        //alert (req.responseText);
                        document.getElementById(ziel_feld).value = req.responseText;
                        document.getElementById('summe_vj').value = req.responseText;
                    } else {

                    }
                }
                break;

            default:
                return false;
                break;
        }
    };
    req.send(null);
}

function zeitdiff(von_id, bis_id, ziel_id, hidd_ziel_id) {
    var von_elem = document.getElementById(von_id);
    var von = von_elem.options[von_elem.selectedIndex].value.toString();
    //alert(von);
    von_arr = von.split(':');
    v_std = parseInt(von_arr[0]);
    v_min = parseInt(von_arr[1]);
    von_min = (v_std * 60) + v_min;

    var bis_elem = document.getElementById(bis_id);
    var bis = bis_elem.options[bis_elem.selectedIndex].value.toString();
    //alert(bis);
    var bis_arr = bis.split(':');
    b_std = parseInt(bis_arr[0]);
    b_min = parseInt(bis_arr[1]);
    bis_min = (b_std * 60) + b_min;
    dauer_min = bis_min - von_min;
    dauer_std = (dauer_min / 60);
    //alert(dauer_min+' Min /' + dauer_std+ ' Std');
    document.getElementById(ziel_id).value = dauer_min + ' Min | ' + dauer_std + ' Std';
    document.getElementById(hidd_ziel_id).value = dauer_min;
}

function drop_kos_register(kos_typ_elem, kos_bez_elem) {
    //var url = 'ajax.php?option=kos_register&kos_typ='+ kos_typ +'&kos_bez'+kos_bez;
    var kos_typ_liste = document.getElementById(kos_typ_elem);
    var kos_typ = kos_typ_liste.options[kos_typ_liste.selectedIndex].value;

    var kos_bez_liste = document.getElementById(kos_bez_elem);
    var kos_bez = kos_bez_liste.options[kos_bez_liste.selectedIndex].value;

    /*Session Variable*/
    register_var('kos_typ', kos_typ);
    register_var('kos_bez', kos_bez);

    //alert(kos_typ+' '+kos_bez);
    //daj3()
}

function update_rechnung_rabatt(belegnr, prozent) {
    if (!prozent || !belegnr) {
        alert('Fehler keine Prozente eingegeben');
        return;
    }
    prozent = prozent.replace(',', '.');
    var req = null;
    try {
        req = new XMLHttpRequest();
    } catch (ms) {
        try {
            req = new ActiveXObject("Msxml2.XMLHTTP");
        } catch (nonms) {
            try {
                req = new ActiveXObject("Microsoft.XMLHTTP");
            } catch (failed) {
                req = null;
            }
        }
    }

    if (req == null)
        alert("Konnte Ajax-Objekt nicht erzeugen!");

    //Anfrageurl zusammenstellen
    var my_url = 'ajax/ajax_info.php?option=update_rechnung_rabatt' + '&belegnr=' + belegnr + '&prozent=' + prozent;
    req.open("GET", my_url, true);

    //Beim Abschliessen der Anfrage wird diese Funktion ausgef�hrt
    req.onreadystatechange = function () {

        switch (req.readyState) {

            case 4:
                if (req.status != 200) {
                    //  alert("Fehler:"+req.status);
                } else {

                    if (req.responseText) {
                        //alert('Rabatt ge�ndert');
                        alert(req.responseText);

                        // display_positionen(belegnr) ;
                    } else {
                        display_positionen(belegnr);
                    }
                }
                break;

            default:
                return false;
                break;
        }
    };
    req.send(null);
}

function update_rechnung_skonti(belegnr, prozent) {
    if (!prozent || !belegnr) {
        alert('Fehler keine Prozente eingegeben');
        return;
    }
    if (prozent > 9.99) {
        alert('Skonti muss eine ganze Zahl sein und nicht größer als 9.99');
        return;
    }
    prozent = prozent.replace(',', '.');
    var req = null;
    try {
        req = new XMLHttpRequest();
    } catch (ms) {
        try {
            req = new ActiveXObject("Msxml2.XMLHTTP");
        } catch (nonms) {
            try {
                req = new ActiveXObject("Microsoft.XMLHTTP");
            } catch (failed) {
                req = null;
            }
        }
    }

    if (req == null)
        alert("Konnte Ajax-Objekt nicht erzeugen!");

    //Anfrageurl zusammenstellen
    var my_url = 'ajax/ajax_info.php?option=update_rechnung_skonti' + '&belegnr=' + belegnr + '&prozent=' + prozent;
    req.open("GET", my_url, true);

    //Beim Abschliessen der Anfrage wird diese Funktion ausgeführt
    req.onreadystatechange = function () {

        switch (req.readyState) {

            case 4:
                if (req.status != 200) {
                    //  alert("Fehler:"+req.status);
                } else {

                    if (req.responseText) {
                        // alert('Skonti geändert');
                        alert(req.responseText);
                        //beleg_feld = document.getElementById('belegnr').value;
                        //display_positionen(belegnr) ;
                    } else {
                        //  alert('Skonti nicht geändert');
                        display_positionen(belegnr);
                    }
                }
                break;

            default:
                return false;
                break;
        }
    };
    req.send(null);
}

function pool_wahl(ziel, check_list_id, kos_typ, kos_id) {
    var req = null;
    try {
        req = new XMLHttpRequest();
    } catch (ms) {
        try {
            req = new ActiveXObject("Msxml2.XMLHTTP");
        } catch (nonms) {
            try {
                req = new ActiveXObject("Microsoft.XMLHTTP");
            } catch (failed) {
                req = null;
            }
        }
    }

    if (req == null)
        alert("Konnte Ajax-Objekt nicht erzeugen!");

    //Anfrageurl zusammenstellen
    var dat_array = new Array();
    alert(document.getElementsByName(check_list_id).length);
    return;
    for (a = 0; a < check_list_id.length; a++) {
    }
    if (a = 1) {

        alert('Wählen bitte');
        return;
    } else {

        var my_url = 'ajax/ajax_info.php?option=pool_auswahl&kontierung_dat=' + dat + '&kos_typ=' + kos_typ + '&kos_id=' + kos_id;
        req.open("POST", my_url, true);

        //Beim Abschliessen der Anfrage wird diese Funktion ausgeführt
        req.onreadystatechange = function () {

            switch (req.readyState) {

                case 4:
                    if (req.status != 200) {
                        alert("Fehler:" + req.status);
                    } else {

                        if (req.responseText) {
                            // alert('Skonti geändert');
                            // alert(req.responseText);
                            document.getElementById(ziel).innerHTML = req.responseText;
                            //beleg_feld = document.getElementById('belegnr').value;
                            //display_positionen(belegnr) ;
                        } else {
                            alert('FEHLERss');
                            //	   display_positionen(belegnr) ;
                        }
                    }
                    break;

                default:
                    return false;
                    break;
            }
        };
        req.send(null);
    }
}

function pool_wahl1(ziel, dat, kos_typ, kos_id, pool_id, pos) {
    var tab = document.getElementById("pos_tabelle");
    //var rows = tab.getElementsByTagName("tr");
    //alert(rows.length)
    tab.deleteRow(document.getElementById(pos));
    //alert(rows.length);

}

hasLoaded = true;
var ROW_BASE = 1;

function zeile_entfernen(obj, dat, kos_typ, kos_id, pool_id) {
    //alert(obj+ dat+ kos_typ+ kos_id+ pool_id);
    var my_url = 'ajax/ajax_info.php?option=kont_pos_deactivate&kontierung_dat=' + dat + '&kos_typ=' + kos_typ + '&kos_id=' + kos_id + '&pool_id=' + pool_id;
    daj3(my_url, 'nix');
    deleteCurrentRow(obj);
}

function get_detail_inhalt(tab, id, det_name, ziel_id) {
    var my_url = 'ajax/ajax_info.php?option=get_detail_inhalt&tab=' + tab + '&id=' + id + '&det_name=' + det_name;
    daj3(my_url, ziel_id);
}

function deleteCurrentRow(obj) {
    if (hasLoaded) {
        var delRow = obj.parentNode.parentNode;
        var tbl = delRow.parentNode.parentNode;
        var rIndex = delRow.sectionRowIndex;
        var rowArray = new Array(delRow);
        deleteRows(rowArray);
        reorderRows(tbl, rIndex);
    }
}

function reorderRows(tbl, startingIndex) {
    if (hasLoaded) {
        if (tbl.tBodies[0].rows[startingIndex]) {
            var count = startingIndex + ROW_BASE;
            for (var i = startingIndex; i < tbl.tBodies[0].rows.length; i++) {

                // CONFIG: next line is affected by myRowObject settings
                tbl.tBodies[0].rows[i].myRow.one.data = count;
                // text

                // CONFIG: next line is affected by myRowObject settings
                tbl.tBodies[0].rows[i].myRow.two.name = INPUT_NAME_PREFIX + count;
                // input text

                // CONFIG: next line is affected by myRowObject settings
                var tempVal = tbl.tBodies[0].rows[i].myRow.two.value.split(' ');
                // for debug purposes
                tbl.tBodies[0].rows[i].myRow.two.value = count + ' was' + tempVal[0];
                // for debug purposes

                // CONFIG: next line is affected by myRowObject settings
                tbl.tBodies[0].rows[i].myRow.four.value = count;
                // input radio

                // CONFIG: requires class named classy0 and classy1
                tbl.tBodies[0].rows[i].className = 'classy' + (count % 2);

                count++;
            }
        }
    }
}

function deleteRows(rowObjArray) {
    if (hasLoaded) {
        for (var i = 0; i < rowObjArray.length; i++) {
            var rIndex = rowObjArray[i].sectionRowIndex;
            rowObjArray[i].parentNode.deleteRow(rIndex);
        }
    }
}

function daj3(url, targ) {
    // alert(url+'  '+targ);
    var xmlhttp = false;

    if (!xmlhttp && typeof XMLHttpRequest != 'undefined') {
        xmlhttp = new XMLHttpRequest();
    }

    //preloading(targ, 'Lade, bitte warten!!!' + url);
    preloading(targ, 'Lade, bitte warten!!!');

    xmlhttp.open("get", url);
    xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded;charset=UTF-8");

    xmlhttp.onreadystatechange = function () {
        if (xmlhttp.readyState == 4 && xmlhttp.status == 200 && targ) {
            if (document.getElementById(targ) != null) {
                document.getElementById(targ).innerHTML = xmlhttp.responseText;
                document.getElementById(targ).value = xmlhttp.responseText;
                //alert(xmlhttp.responseText);
            } else {
                //alert('Zielfenster unbekannt');
                //document.write(xmlhttp.responseText);
                //  document.getElementById("main").innerHTML = xmlhttp.responseText;

            }
            return false;
        } else {
            return false;
        }
        return false;
    };
    xmlhttp.send(null);

}

function ajax(url, callback) {
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function () {
        if (xmlhttp.readyState == 4) {
            callback(xmlhttp);
        }
    };
    xmlhttp.open("get", url);
    xmlhttp.send(null);
}

function preloading(target, msg) {
    if (document.getElementById(target) != null) {
        document.getElementById(target).innerHTML = msg;
    } else {
        /*document.write('LADE');*/
    }
}

function reg_pool() {
    var pool_id = document.getElementById("z_pool").value;
    daj3('/rechnungen?option=reg_pool&pool_id=' + pool_id, 'nix');
}

function up(pp_dat, virt_pos, ziel, kos_typ, kos_id, pool_id) {
    //alert(virt_pos);
    daj3('ajax/ajax_info.php?option=pool_up&kos_typ=' + kos_typ + '&kos_id=' + kos_id + '&pp_dat=' + pp_dat + '&virt_pos=' + virt_pos + '&pool_id=' + pool_id, 'ohne_ziel');
    setTimeout("reload_me();", 1000);
}

function down(pp_dat, virt_pos, ziel, kos_typ, kos_id, pool_id) {
    //alert(pp_dat+ziel);
    daj3('ajax/ajax_info.php?option=pool_down&kos_typ=' + kos_typ + '&kos_id=' + kos_id + '&pp_dat=' + pp_dat + '&virt_pos=' + virt_pos + '&pool_id=' + pool_id, 'ohne_ziel');
    setTimeout("reload_me();", 1000);
}

function change_zeile(spalte, wert, pp_dat) {
    //	alert(spalte+' '+pp_dat);
    var wert_neu = prompt(spalte, wert);
    if (wert_neu != null) {
        //var ziel = "pool_tab";
        daj3('ajax/ajax_info.php?option=change_wert&pp_dat=' + pp_dat + '&wert=' + wert_neu + '&spalte=' + spalte, 'ohne_ziel');
        setTimeout("reload_me();", 1000);
    }

}

function change_detail(anzeige_text, wert, detail_dat, kos_typ, kos_id) {
    var wert_neu = prompt(anzeige_text, wert);
    if (wert_neu != null) {
        //var ziel = "pool_tab";
        daj3('ajax/ajax_info.php?option=change_details&dat=' + detail_dat + '&wert=' + wert_neu + '&kos_typ=' + kos_typ + '&kos_id=' + kos_id + '&det_name=' + anzeige_text, null);
        //alert(anzeige_text+wert_neu+detail_dat);
        setTimeout("reload_me();", 500);
    }
}

function change_kautionsfeld(feld, wert, mv_id) {
    var wert_neu = prompt(feld, wert);

    if (wert_neu != null) {
        //alert(feld + wert_neu);
        //var ziel = "pool_tab";
        daj3('ajax/ajax_info.php?option=change_kautionsfeld&feld=' + feld + '&wert=' + wert_neu + '&mv_id=' + mv_id, null);
        //alert(anzeige_text+wert_neu+detail_dat);
        setTimeout("reload_me();", 1000);
    }
}

function change_hk_wert_et(bez, et_id, wert, profil_id) {
    var wert_neu = prompt(bez, wert);

    if (wert_neu != null) {
        //alert(feld + wert_neu);
        //var ziel = "pool_tab";
        daj3('ajax/ajax_info.php?option=change_hk_wert_et&et_id=' + et_id + '&wert=' + wert_neu + '&profil_id=' + profil_id, null);
        //alert(anzeige_text+wert_neu+detail_dat);
        setTimeout("reload_me();", 1000);
    }
}

function change_detail_no_prompt(anzeige_text, wert, detail_dat, kos_typ, kos_id) {
    if (wert != null) {
        ajax('ajax/ajax_info.php?option=change_details&dat=' + detail_dat + '&wert=' + wert + '&kos_typ=' + kos_typ + '&kos_id=' + kos_id + '&det_name=' + anzeige_text, function (request) {
            if (request.status == 200) {
                Materialize.toast('<i class="material-icons">done</i> Eintrag gespeichert.', 3000, 'rounded');
            } else {
                Materialize.toast('<i class="material-icons">clear</i> Fehler beim speichern. Bitte versuchen Sie es erneut.', 3000, 'rounded');
            }
        });
    }
}

function change_detail_dd(anzeige_text, wert, detail_dat, kos_typ, kos_id) {
    //alert(anzeige_text+wert+detail_dat+kos_typ+kos_id);
    //alert('ajax/ajax_info.php?option=change_details&dat='+detail_dat+'&wert='+wert+'&kos_typ='+kos_typ+'&kos_id='+kos_id+'&det_name='+anzeige_text);
    daj3('ajax/ajax_info.php?option=change_details&dat=' + detail_dat + '&wert=' + wert + '&kos_typ=' + kos_typ + '&kos_id=' + kos_id + '&det_name=' + anzeige_text, null);

    setTimeout("reload_me();", 500);

}

function change_text(art_nr, lieferant_id, text, sprung) {
    var text_neu = prompt('Neuen Text eingeben', text);
    if (text_neu != null) {

        if (text_neu == '') {
            alert('Bitte den Text eingeben!');
        } else {
            daj3('ajax/ajax_info.php?option=change_text&art_nr=' + art_nr + '&lieferant_id=' + lieferant_id + '&text_neu=' + text_neu, 'ohne_ziel');
        }
    }
    setTimeout("reload_me();", 1000);

}

function redirect_to(destination) {
    window.location.href = destination;
}

function u_pool_rechnung(kos_typ, kos_id, aussteller_typ, aussteller_id, pool_ids_string) {
    var Heute = new Date();
    var Tage = zweistellig(Heute.getDate());
    var Wochentag = Heute.getDay();
    var Monate = zweistellig(Heute.getMonth() + 1);
    var Jahre = Heute.getFullYear();
    var heute_datum = Tage + "." + Monate + "." + Jahre;
    var wert_heute = prompt('Rechnungsdatum', heute_datum);
    var d = new Date();
    d.setDate(d.getDate() + 14);
    // 10 Tage später
    var faellig_tag = zweistellig(d.getDate());
    var faellig_mon = zweistellig(d.getMonth() + 1);
    var faellig_jahr = d.getFullYear();
    var faellig = faellig_tag + '.' + faellig_mon + '.' + faellig_jahr;

    var wert_faellig = prompt('Fällig am (+14T)', faellig);
    var d_error = '';

    /*if(!checkdate(wert_heute)){
     d_error = 'Rechnungsdatum nicht korrekt!'+wert_heute;
     }
     if(!checkdate(wert_faellig)){
     d_error = 'F�lligkeitsdatum nicht korrekt!'+wert_faellig;
     }

     if(error!=''){
     alert('Abbruch: '+d_error);
     }else{
     alert('Rechnung wird erstellt');
     }*/
    var kurzinfo = prompt('Kurzinfo zur Rechnung eingeben z.B. Bauvorhaben', '');
    var gk_liste = document.getElementById('gk_id');
    var gk_id = gk_liste.options[gk_liste.selectedIndex].value;
    //alert(gk_id);
    if (wert_faellig != null && wert_heute != null && kurzinfo != null && wert_faellig != '' && wert_heute != '' && kurzinfo != '') {
        //alert('ajax/ajax_info.php?option=u_pool_rechnung_erstellen&kos_typ='+kos_typ+'&kos_id='+kos_id+'&r_datum='+wert_heute+'&f_datum='+wert_faellig+'&kurzinfo='+kurzinfo+'&aussteller_typ='+aussteller_typ+'&aussteller_id='+aussteller_id+'&gk_id='+gk_id+'&pool_ids_string='+pool_ids_string);
        daj3('ajax/ajax_info.php?option=u_pool_rechnung_erstellen&kos_typ=' + kos_typ + '&kos_id=' + kos_id + '&r_datum=' + wert_heute + '&f_datum=' + wert_faellig + '&kurzinfo=' + kurzinfo + '&aussteller_typ=' + aussteller_typ + '&aussteller_id=' + aussteller_id + '&gk_id=' + gk_id + '&pool_ids_string=' + pool_ids_string, 'pool_tab');
        var url = '/rechnungen?option=ausgangsbuch&partner_id=' + aussteller_id;
        setTimeout('Redirect("' + url + '")', 2000);
    } else {
        alert('Dateneingabe unvollständig\nDATUM ODER KURZINFO prüfen!');
    }
    //reload_me();
}

function Redirect(url) {
    window.location = url;
}

function zweistellig(d) {
    return (d < 10 ? '0' + d : d);
}

function spalte_prozent(op, spalte) {
    if (op == '-') {
        var prozent = prompt('Nachlassprozente eingeben!', 0);
    }
    if (op == '+') {
        var prozent = prompt('Erhöhungsprozente eingeben!', 0);
    }

    if (prozent != null) {
        //alert('eingegeben'+prozent);
        daj3('ajax/ajax_info.php?option=spalte_prozent&spalte=' + spalte + '&op=' + op + '&prozent=' + prozent, 'pool_tabff');
        setTimeout("reload_me();", 1000);
    }

}

function aufpreis(spalte, pp_dat) {
    //alert(spalte+pp_dat);
    var prozent = prompt('Prozente +/- eingeben!', 0);
    daj3('ajax/ajax_info.php?option=aufpreis&spalte=' + spalte + '&pp_dat=' + pp_dat + '&prozent=' + prozent, 'pool_tabff');
    setTimeout("reload_me();", 1000);
}

function spalte_prozent_pool(pool_id, spalte) {
    var prozent = prompt('Prozente für Poolpreise eingeben!', 0);

    if (prozent != null) {

        daj3('ajax/ajax_info.php?option=spalte_prozent_pool&spalte=' + spalte + '&prozent=' + prozent + '&pool_id=' + pool_id, 'pool_tabff');
        setTimeout("reload_me();", 1000);
    }
}

function spalte_einheitspreis_pool(pool_id, spalte) {
    var preis = prompt('Einheitspreis für den Pool eingeben!', 0);

    if (preis != null) {

        daj3('ajax/ajax_info.php?option=spalte_einheitspreis_pool&spalte=' + spalte + '&preis=' + preis + '&pool_id=' + pool_id, 'pool_tabff');
        setTimeout("reload_me();", 1000);
    }
}

function u_pool_rechnung_pool_wahl(name, kos_typ, kos_id, aussteller_typ, aussteller_id) {

    var check_liste = document.getElementsByName(name);

    if (check_liste.length != undefined) {
        var anz = check_liste.length;
        var pool_ids = new Array();
        var z = 0;
        for (i = 0; i < anz; i++) {
            if (check_liste[i].checked == true) {
                //	alert(i);
                pool_ids[z] = check_liste[i].value;
                z++;
            }
        }
        if (pool_ids.length < 1) {
            alert('Pools für die Rechnung wählen');
            return;
        } else {
            //	alert(pool_ids.length);
            var anz = pool_ids.length;
            var pool_ids_string = '';
            for (a = 0; a < anz; a++) {
                pool_ids_string = pool_ids_string + pool_ids[a] + '|P|';
            }
            //alert(pool_ids_string);
            u_pool_rechnung(kos_typ, kos_id, aussteller_typ, aussteller_id, pool_ids_string);
        }
    }

}

function list_u_pools(kos_typ, kos_id) {

    var kostyp_liste = document.getElementById(kos_typ);
    var kos_typ_value = kostyp_liste.options[kostyp_liste.selectedIndex].value;

    var kosid_liste = document.getElementById(kos_id);
    var kos_bez_value = kosid_liste.options[kosid_liste.selectedIndex].value;
    if (kos_bez_value != '') {
        daj3('ajax/ajax_info.php?option=u_pools_anzeigen&kos_typ=' + encodeURIComponent(kos_typ_value) + '&kos_bez=' + encodeURIComponent(kos_bez_value), 'pools');
    }
}

function act_deacivate(pool_id, kos_typ, kos_bez, kos_id) {
    daj3('ajax/ajax_info.php?option=pool_act_deactivate&kos_typ=' + kos_typ + '&kos_id=' + kos_id + '&pool_id=' + pool_id, 'poolssss');

    /*Refresh*/
    var fun = "daj3('" + 'ajax/ajax_info.php?option=u_pools_anzeigen&kos_typ=' + encodeURIComponent(kos_typ) + '&kos_bez=' + encodeURIComponent(kos_bez) + "'," + "'" + 'pools' + "');";
    var field = 'pools';
    setTimeout(fun, 500);
}

function u_pool_erstellen(textfeld, kos_typ, kos_id) {

    var kostyp_liste = document.getElementById(kos_typ);
    var kos_typ_value = kostyp_liste.options[kostyp_liste.selectedIndex].value;

    var kosid_liste = document.getElementById(kos_id);
    var kos_bez_value = kosid_liste.options[kosid_liste.selectedIndex].value;

    var pool_bez = document.getElementById(textfeld).value;

    if (pool_bez != '') {
        daj3('ajax/ajax_info.php?option=u_pool_erstellen&kos_typ=' + encodeURIComponent(kos_typ_value) + '&kos_bez=' + encodeURIComponent(kos_bez_value) + '&pool_bez=' + encodeURIComponent(pool_bez), 'pools');

        var fun = "daj3('" + 'ajax/ajax_info.php?option=u_pools_anzeigen&kos_typ=' + encodeURIComponent(kos_typ_value) + '&kos_bez=' + encodeURIComponent(kos_bez_value) + "'," + "'" + 'pools' + "');";
        var field = 'pools';
        setTimeout(fun, 500);
    } else {
        alert('Unterpoolbezeichnng eingeben');
        document.getElementById(textfeld).focus();
    }
}

function back2pool(pp_dat) {
    daj3('ajax/ajax_info.php?option=back2pool&pp_dat=' + pp_dat, 'ohne_ziel');
    setTimeout("reload_me();", 1000);
}

function wb_hinzufuegen(beleg_id, pos) {
    //alert(beleg_id+''+ pos);
    daj3('ajax/ajax_info.php?option=wb_hinzufuegen&beleg_id=' + beleg_id + '&pos=' + pos, 'poolssss');
    alert('Position in die Werkzeugliste hinzugefügt!');
}

/*urlaubs buttons*/
function urlaub_buttons(feld_id, benutzer_id, datum) {
    //alert(feld_id);

    var buttons = '<input type="button" name=\"urlaub_butt\" value=\"Urlaub\" class=\"submit\" id=\"urlaub\" onclick=\"urlaub_eintragen(\'' + benutzer_id + '\',\'' + datum + '\',' + '\'Urlaub\');\"' + '><input type="button" name=\"urlaub_butt\" value=\"Krank\" class=\"submit\" id=\"krank\" onclick=\"urlaub_eintragen(\'' + benutzer_id + '\',\'' + datum + '\',' + '\'Krank\');\"' + '><input type="button" name=\"urlaub_butt\" value=\"oK\" class=\"submit\" id=\"oK\" onclick=\"urlaub_eintragen(\'' + benutzer_id + '\',\'' + datum + '\',' + '\'oK\');\"' + '><input type="button" name=\"urlaub_butt\" value=\"Auszahlung\" class=\"submit\" id=\"auszahlung\" onclick=\"urlaub_eintragen(\'' + benutzer_id + '\',\'' + datum + '\',' + '\'Auszahlung\');\"' + '><input type="button" name=\"urlaub_butt\" value=\"Unbezahlt\" class=\"submit\" id=\"unbezahlt\" onclick=\"urlaub_eintragen(\'' + benutzer_id + '\',\'' + datum + '\',' + '\'Unbezahlt\');\"' + '>';
    document.getElementById(feld_id).innerHTML = buttons;
}

function urlaub_eintragen(benutzer_id, datum, art) {
    daj3('/urlaub?option=urlaubsantrag_check&u_vom=' + datum + '&u_bis=' + datum + '&benutzer_id=' + benutzer_id + '&art=' + art, 'nix');
    alert(art + ' wurde zum ' + datum + 'eingetragen');
    reload_me();

}

function urlaub_del_button(feld_id, benutzer_id, datum) {
    var buttons = '<input type="button" name=\"urlaub_del\" value=\"Storno\" class=\"submit\" id=\"storno\" onclick=\"urlaub_loeschen(\'' + benutzer_id + '\',\'' + datum + '\',' + '\'Urlaub\');\"' + '>';
    document.getElementById(feld_id).innerHTML = buttons;
}

function urlaub_loeschen(benutzer_id, datum, art) {
    daj3('/urlaub?option=urlaubstag_loeschen_js&datum=' + datum + '&benutzer_id=' + benutzer_id, 'nix');
    alert(art + ' vom ' + datum + ' wurde gelöscht!');
    reload_me();

}