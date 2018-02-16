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
 * @filesource   $HeadURL: http://192.168.2.52/svn/berlussimo_1/tags/02.11.2010 - Downloadversion 0.27/ajax/dd_kostenkonto.js $
 * @version      $Revision: 6 $
 * @modifiedby   $LastChangedBy: sivac $
 * @lastmodified $Date: 2010-09-21 10:34:50 +0200 (Di, 21 Sep 2010) $
 *
 */
var xmlHttp;

function kostenkonto_infos(option, konto_id) {
    xmlHttp = GetXmlHttpObject();
    if (xmlHttp == null) {
        alert("Browser does not support HTTP Request");
        return;
    }
    var url = "ajax/ajax_info.php";
    url = url + "?option=" + option + "&konto_id=" + konto_id;
    xmlHttp.onreadystatechange = kostenkonto_infos_ausgeben;
    xmlHttp.open("GET", url, true);
    xmlHttp.send(null);
    kostenkonto_vorwahl(konto_id);
}

function kostenkonto_infos_ausgeben() {
    if (xmlHttp.readyState == 4 || xmlHttp.readyState == "complete") {
        //alert(xmlHttp.status)
        var mySplitResult = xmlHttp.responseText.split('|');
        var konto_bezeichnung = mySplitResult[0];
        var konto_gruppe = mySplitResult[1];
        var konto_art = mySplitResult[2];
        var size_konto_bezeichnung = parseInt(konto_bezeichnung.length) + parseInt(2);
        document.getElementById("kontobezeichnung").size = size_konto_bezeichnung;
        document.getElementById("kontobezeichnung").value = konto_bezeichnung;

        var size_konto_art = parseInt(konto_art.length) + parseInt(2);
        document.getElementById("kontoart").size = size_konto_art;
        document.getElementById("kontoart").value = konto_art;

        var size_konto_gruppe = parseInt(konto_gruppe.length) + parseInt(2);
        document.getElementById("kostengruppe").size = size_konto_gruppe;
        document.getElementById("kostengruppe").value = konto_gruppe;

    }
}

function list_kostentraeger(option, typ) {
    xmlHttp = GetXmlHttpObject();
    if (xmlHttp == null) {
        alert("Browser does not support HTTP Request");
        return;
    }
    var url = "ajax/ajax_info.php";
    url = url + "?option=" + option + "&typ=" + typ;
    xmlHttp.onreadystatechange = list_kostentraeger_drop;
    xmlHttp.open("GET", url, true);
    xmlHttp.send(null);
}

function list_kostentraeger_drop() {

    if (xmlHttp.readyState == 4 || xmlHttp.readyState == "complete") {
        var kostentraeger_arr = xmlHttp.responseText.split('|');
        var anzahl_kostentraeger = kostentraeger_arr.length;
        var dd_feld = document.getElementById("dd_kostentraeger_id");
        dd_feld.length = 1;
        for (var i = 0; i < anzahl_kostentraeger - 1; i++) {
            kd = kostentraeger_arr[i];
            //alert(kd);
            var bez_value_arr = kd.split('*');
            var bez = bez_value_arr[0];
            var bez_id = bez_value_arr[1];
            var bez_obj = bez_value_arr[2];
            //alert(bez_value);
            if (bez_id != undefined) {
                // alert(bez_value);
                addOption(dd_feld, bez + ' | ' + bez_obj, bez_id);
            } else {
                addOption(dd_feld, kd, kd);
            }
        }
        $('select').material_select();
    }
}

function addOption(selectbox, text, value) {
    var optn = document.createElement("OPTION");
    optn.text = text;
    optn.value = value;
    selectbox.options.add(optn);
}

function IsNumeric(sText) {
    var ValidChars = "0123456789.";
    var IsNumber = true;
    var Char;

    for (i = 0; i < sText.length && IsNumber == true; i++) {
        Char = sText.charAt(i);
        if (ValidChars.indexOf(Char) == -1) {
            IsNumber = false;
        }
    }
    return IsNumber;

}

function checkartikel(lieferant, artikel_nr) {
    xmlHttp = GetXmlHttpObject();
    if (xmlHttp == null) {
        alert("Browser does not support HTTP Request");
        return;
    }
    var url = "ajax/ajax_info.php";
    url = url + '?option=check_artikels' + '&lieferant_id=' + lieferant + '&artikel_nr=' + artikel_nr;
    alert(url);
    xmlHttp.onreadystatechange = artikel_infos_ausgeben;
    xmlHttp.open("GET", url, true);
    xmlHttp.send(null);
}

function artikel_infos_ausgeben() {

    alert(xmlHttp.responseText);
}

function kostenkonto_vorwahl(kostenkonto) {

    /*alert(kostenkonto);*/
    /*if(kostenkonto == '1023' || kostenkonto == '3000'){
     dd_kostentraegertyp =document.getElementById("kostentraeger_typ");

     dd_kostentraegertyp.selectedIndex = 3;
     dd_kostentraegertyp.options[3].selected = true;
     list_kostentraeger('list_kostentraeger', 'Einheit');
     }else{

     dd_kostentraegertyp =document.getElementById("kostentraeger_typ");

     dd_kostentraegertyp.selectedIndex = 1;
     dd_kostentraegertyp.options[1].selected = true;
     list_kostentraeger('list_kostentraeger', 'Objekt');
     }
     */

}

function GetXmlHttpObject() {
    var xmlHttp = null;
    try {
        // Firefox, Opera 8.0+, Safari
        xmlHttp = new XMLHttpRequest();
    } catch (e) {
        //Internet Explorer
        try {
            xmlHttp = new ActiveXObject("Msxml2.XMLHTTP");
        } catch (e) {
            xmlHttp = new ActiveXObject("Microsoft.XMLHTTP");
        }
    }
    return xmlHttp;
}

function mieter_auswaehlen() {
    var mylist = document.getElementById("alle_mieter_list");
    var mieter_liste = document.getElementById("mieter_liste");
    var anzahl_mieter = mieter_liste.length;
    var selected_name = mylist.options[mylist.selectedIndex].text;
    var selected_value = mylist.options[mylist.selectedIndex].value;
    var neuer_mieter = document.createElement('option');
    neuer_mieter.text = selected_name;
    neuer_mieter.value = selected_value;
    for (var a = 0; a < mieter_liste.length; a++) {
        if (mieter_liste[a].value == selected_value) {
            var mieter_vorhanden = true;
        }
    }
    if (!mieter_vorhanden) {
        mieter_liste.add(neuer_mieter, null); // nicht für iE
        mieter_liste.style.visibility = "visible";
        $('select').material_select();
    }
}