function start() {
    var datum = datum_heute();
    del_sess_vars();
    //daj2('ajax.php?option=main&target_id=navibox',document.getElementById('navibox'));
    daj3('/wartungsplaner/ajax?option=main&target_id=navibox', 'leftBox');
    document.getElementById('rightBox').innerHTML = '';
    document.getElementById('leftBox1').innerHTML = '';
    document.getElementById('rightBox1').innerHTML = '';
    //daj2('ajax.php?option=main&target_id=leftBox',document.getElementById('leftBox1'));
    //daj2('index_ajax.php?option=tages_termine&datum='+datum,document.getElementById('leftBox1'));

    //karte_anzeigen(document.getElementById('rightBox1'), 'ss.txt');
    //bing_init();
    //del_sess_vars();
}

function start_vorschlag() {
    start();
    vorschlag();
}

function start_vorschlag_chrono() {
    start();
    vorschlag_chrono();
}


function del_sess_vars() {
    daj3('/wartungsplaner/ajax?option=del_sess_vars', 'ohne_ziel');
}


function termin_speichern() {
    var datum = datum_heute();
    var beginn = document.getElementById('beginn').value;
    var geo_id = document.getElementById('geo_id').value;
    var datum_termin = document.getElementById('datum_termin').value;
    var ende = document.getElementById('ende').value;
    var leistung_id = document.getElementById('leistung_id').value;
    var hinweis = document.getElementById('hinweis').value;
    var textn = document.getElementById('textn').value;
    var benutzer_id = document.getElementById('benutzer_id').value;
    var partner_id = document.getElementById('partner_id').value;

    daj2('/wartungsplaner/index_ajax?option=suche_termine', document.getElementById('rightBox'));
    //daj2('index_ajax.php?option=tages_termine&datum='+datum_termin,document.getElementById('leftBox1'));
    //karte_anzeigen(document.getElementById('rightBox1'), 'ss.txt');
    daj2('/wartungsplaner/index_ajax?option=termin_speichern_db&datum=' + datum_termin + '&beginn=' + beginn + '&ende=' + ende + '&geo_id=' + geo_id + '&leistung_id=' + leistung_id + '&hinweis=' + hinweis + '&benutzer_id=' + benutzer_id + '&partner_id=' + partner_id + '&text=' + textn, '');

    daj2('/wartungsplaner/index_ajax?option=tages_termine&datum=' + datum_termin, document.getElementById('leftBox1'));

    //bing_init();
}

function datum_heute() {
    var jahr, monat, tag, stunden, minuten;
    var AktuellesDatum = new Date();
    jahr = AktuellesDatum.getYear() - 100 + 2000;
    monat = AktuellesDatum.getMonth() + 1;
    tag = AktuellesDatum.getDate();
    stunden = AktuellesDatum.getHours();
    minuten = AktuellesDatum.getMinutes();
    var heute = tag + '.' + monat + '.' + jahr;
    return heute;
}

function zentrieren(Lon, Lat, zoom) {

    var layers = map.getLayersByName('Termine');
    for (var layerIndex = 0; layerIndex < layers.length; layerIndex++) {
        map.removeLayer(layers[layerIndex]);
    }

    //index_ajax.php?option=termine_anzeigen_sql
    var pois = new OpenLayers.Layer.Text("Termine",
        {
            location: "/wartungsplaner/index_ajax?option=termine_anzeigen_sql",
            projection: map.displayProjection
        });


    map.addLayer(pois);

    //Set start centrepoint and zoom
    var lonLat = new OpenLayers.LonLat(Lon, Lat)
        .transform(
            new OpenLayers.Projection("EPSG:4326"), // transform from WGS 1984
            map.getProjectionObject() // to Spherical Mercator Projection
        );


    layerMarkers = new OpenLayers.Layer.Markers("Ziel");
    map.addLayer(layerMarkers);
    var size = new OpenLayers.Size(15, 35);
    var offset = new OpenLayers.Pixel(-(size.w / 2), -size.h);
    var icon = new OpenLayers.Icon('http://berlussimo.berlus.de/cal/pin.png', size, offset);
    layerMarkers.addMarker(new OpenLayers.Marker(lonLat, icon));
    map.setCenter(lonLat, zoom);
}


function newIcon(url, x, y) {
    var size = new OpenLayers.Size(x, y);
    var offset = new OpenLayers.Pixel(-(size.w / 2), -(size.h / 2));
    return new OpenLayers.Icon(url, size, offset);
}


function reload() {
    //map.addLayer(new OpenLayers.Layer.OSM());

    var pois = new OpenLayers.Layer.Text("My Points",
        {
            location: "/wartungsplaner/index_ajax?option=termine_anzeigen_sql",
            projection: map.displayProjection
        });

    map.addLayer(pois);

    //Set start centrepoint and zoom
    var lonLat = new OpenLayers.LonLat(13.41, 52.52)
        .transform(
            new OpenLayers.Projection("EPSG:4326"), // transform from WGS 1984
            map.getProjectionObject() // to Spherical Mercator Projection
        );
    var zoom = 11;
    map.setCenter(lonLat, zoom);
}


function check_fields() {
    var str = document.getElementById('str').value;
    var nr = document.getElementById('nr').value;
    var plz = document.getElementById('plz').value;
    var ort = document.getElementById('ort').value;
    var w_datum = document.getElementById('w_datum').value;
    var kunde = document.getElementById('kunde').checked;
    if (str.length == 0) {
        alert('Strassennamen eingeben');
        document.getElementById('str').focus();
        document.getElementById('str').style.backgroundColor = "#FF9933";
        return false;
    }
    else if (nr.length == 0) {
        alert('Hausnummer eingeben');
        document.getElementById('nr').focus();
        document.getElementById('nr').style.backgroundColor = "#FF9933";
        return false;
    }
    else if (plz.length != 5 || isNaN(plz) == true) {
        alert('PLZ eingeben oder PLZ falsch');
        return false;
    }

    else if (ort.length == 0) {
        alert('Ort eingeben');
        return false;
    }


    else {
        if (w_datum.length == 0) {
            w_datum = datum_heute();
            document.getElementById('w_datum').value = w_datum;
        } else {
            var datum_arr = w_datum.split(".");
            var d = datum_arr[0];
            var m = datum_arr[1];
            var j = datum_arr[2];
            if (checkdate(m, d, j) == false) {
                alert('Datumsformat falsch');
                document.getElementById('w_datum').focus();
                document.getElementById('w_datum').style.backgroundColor = "#FF9933";
                return false;
            } else {
                document.getElementById('w_datum').style.backgroundColor = "#56D65C";
            }
        }
        var name = document.getElementById('name').value;
        var vorname = document.getElementById('vorname').value;

        if (kunde == true) {
            if (name.length == 0) {
                alert('Namen eingeben');
                document.getElementById('name').focus();
                document.getElementById('name').style.backgroundColor = "#FF9933";
                return false;
            }
            else if (vorname.length == 0) {
                alert('Vornamen eingeben');
                document.getElementById('vorname').focus();
                document.getElementById('vorname').style.backgroundColor = "#FF9933";
                return false;
            }

            if (isAlpha(name) == null) {
                alert('Namen bitte nur aus Buchstaben eingeben!');
                document.getElementById('name').focus();
                document.getElementById('name').style.backgroundColor = "#FF9933";
                return false;
            } else {
                document.getElementById('name').style.backgroundColor = "#56D65C";
            }

            if (isAlpha(vorname) == null) {
                alert('Vornamen bitte nur aus Buchstaben eingeben!');
                document.getElementById('vorname').focus();
                document.getElementById('vorname').style.backgroundColor = "#FF9933";
                return false;
            } else {
                document.getElementById('vorname').style.backgroundColor = "#56D65C";
            }
        }

        if (isALNum(str) == null) {
            alert('Strasse bitte nur aus Buchstaben eingeben, keine Zahlen und Sonderzeichen!');
            document.getElementById('str').focus();
            document.getElementById('str').style.backgroundColor = "#FF9933";
            return false;
        } else {
            document.getElementById('str').style.backgroundColor = "#56D65C";
        }

        if (isALNum(nr) == null) {
            alert('Hausnummer bitte nur aus Zahlen und Buchstaben eingeben, keine Sonderzeichen!');
            document.getElementById('nr').focus();
            document.getElementById('nr').style.backgroundColor = "#FF9933";
            return false;
        } else {
            document.getElementById('nr').style.backgroundColor = "#56D65C";
        }


        //alert(str+nr+plz+ort+w_datum+kunde);
        //daj2('index_ajax.php?option=reg_vars&str='+str+'nr='+nr+'plz='+plz+'ort='+ort+'w_datum='+w_datum+'kunde='+kunde+'name='+name+'vorname='+vorname,document.getElementById('rightBox1'));
        daj2('/wartungsplaner/index_ajax?option=tages_termine&datum=' + w_datum, document.getElementById('leftBox1'));
        //daj2('index_ajax.php?option=kartendaten_woche&datum='+w_datum,document.getElementById('rightBox1'));

    }
}

function get_ergebnis(url) {
    var xmlhttp1 = false;
    if (!xmlhttp1 && typeof XMLHttpRequest != 'undefined') {
        xmlhttp1 = new XMLHttpRequest();
    }
    xmlhttp1.open("GET", url);
    xmlhttp1.onreadystatechange = function () {
        if (xmlhttp1.readyState == 4 && xmlhttp1.status == 200) {
            //alert(xmlhttp1.responseText);
            //zentrieren('13.29','52.35', '12')
            var lat_arr = xmlhttp1.responseText.split(',');
            var lat = lat_arr[0];
            var lon = lat_arr[1];
            var quelle = lat_arr[2];
            //alert(xmlhttp1.responseText);

            var entf_url = '/wartungsplaner/index_ajax?option=get_entfernung&lat=' + lat + '&lon=' + lon;
            //alert(entf_url);
            ziel = document.getElementById('rightBox');
            daj2(entf_url, ziel);
            //return xmlhttp1.responseText;
            zentrieren(lon, lat, '15');
        }
        return true;
    };
    xmlhttp1.send(null);
}

function machRequest() {
    try {
        return new ActiveXObject('Msxml2.XMLHTTP');
    }
    catch (e) {
    }

    try {
        return new ActiveXObject('Microsoft.XMLHTTP');
    }
    catch (e) {
    }

    try {
        return new XMLHttpRequest();
    }
    catch (e) {
    }

    alert('XMLHttpRequest wird von Deinem Browser nicht unterstützt.');

    return false;
}


function get_from_ajax(url) {
    var request = machRequest();
    // uri = encodeURI(url);
    //alert('AJAX ' + uri);
    request.open('GET', url, false);
    request.send(null);
    if (request.status != 200) return '';
    //alert(request.responseText);
    return request.responseText;
}


function daj2(url, targ) {
    url = encodeURI(url);
    //alert(url+'  '+targ);
    if (url == '/wartungsplaner/index_ajax?option=suchen') {
        check_fields();
        //zentrieren('13.29','52.35', '12')
        //exit();
        var str = document.getElementById('str').value;
        var nr = document.getElementById('nr').value;
        var plz = document.getElementById('plz').value;
        var ort = document.getElementById('ort').value;
        var w_datum = document.getElementById('w_datum').value;
        get_ergebnis('/wartungsplaner/index_ajax?option=get_lon_lat_osm&str=' + str + '&nr=' + nr + '&plz=' + plz + '&ort=' + ort + '&w_datum=' + w_datum);
        // myrul = 'index_ajax.php?option=get_lon_lat_osm&str='+str+'&nr='+nr+'&plz='+plz+'&ort='+ort
        //alert('index_ajax.php?option=get_lon_lat_osm&str='+str+'&nr='+nr+'&plz='+plz+'&ort='+ort);

        //zentrieren('13.29','52.35', '12')
    } else {
        var xmlhttp = false;
        /*@cc_on @*/
        /*@if (@_jscript_version >= 5)
         try {
         xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
         } catch (e) {
         try {
         xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
         } catch (E) {
         xmlhttp = false;
         }
         }
         @end @*/
        if (!xmlhttp && typeof XMLHttpRequest != 'undefined') {
            xmlhttp = new XMLHttpRequest();
        }
        xmlhttp.open("GET", url);
        xmlhttp.onreadystatechange = function () {
            if (xmlhttp.readyState == 4 && xmlhttp.status == 200 && targ) {
                targ.innerHTML = xmlhttp.responseText;
            }
            return true;
        };
        xmlhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded;charset=UTF-8");
        xmlhttp.send(null);
    }
}

function daj3(url, targ) {
    url = encodeURI(url);
    //alert(url+'  '+targ);
    var xmlhttp = false;

    if (!xmlhttp && typeof XMLHttpRequest != 'undefined') {
        xmlhttp = new XMLHttpRequest();
    }

    preloading(targ, 'Lade, bitte warten!!!!!!' + url);
    //preloading(targ, 'Lade, bitte warten!!!');

    xmlhttp.open("get", url);
    xmlhttp.onreadystatechange = function () {
        // alert(xmlhttp.readyState);
        if (xmlhttp.readyState == 4 && xmlhttp.status == 200 && targ) {
            if (document.getElementById(targ) != null) {
                document.getElementById(targ).innerHTML = xmlhttp.responseText;
                returnValue = xmlhttp.responseText;
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
    xmlhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded;charset=UTF-8");
    xmlhttp.send(null);

}

function transitionState(transitions) {
    transitionPromises(transitions);
}

function transitionPromise(transition) {
    if (transition.type === 'Function') {
        return new Promise(function (resolve) {
            new Function(transition.value)();
            resolve();
        });
    } else if (transition.type === 'Request') {
        let promise = fetch(transition.value);
        if (transition.target) {
            let target = document.getElementById(transition.target);
            target.innerHTML = "Lade " + transition.value;
            promise.then((response) => {
                response.text().then(text => {
                    target.innerHTML = text;
                });
            });
        }
        return promise;
    }
}

function transitionPromises(transitions) {
    if (Array.isArray(transitions)) {
        let previous = null, first = null;
        transitions.forEach(function (subTransitions) {
            if (previous) {
                previous = previous.then(function () {
                    return transitionPromises(subTransitions);
                });
            } else {
                first = previous = transitionPromises(subTransitions);
            }
        });
        return first;
    } else {
        return transitionPromise(transitions);
    }
}

function Pause(Zeit) {
    var Dauer = new Date();
    Dauer = Dauer.getTime() + Zeit;
    do {
        var Dauer2 = new Date();
        Dauer2 = Dauer2.getTime();

    }
    while (Dauer2 <= Dauer);
}

function check_datum(id) {
//		alert(id);
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


function checkdatum(datum_d) {
    if (datum_d != '') {
        var dat_arr = datum_d.split('.');
        var y = dat_arr[2];
        var m = dat_arr[1];
        var d = dat_arr[0];
        return checkdate(m, d, y);
    } else {
        return false;
    }
}

function checkdate(m, d, y) {
    // Returns true(1) if it is a valid date in gregorian calendar
    //
    // version: 1008.1718
    // discuss at: http://phpjs.org/functions/checkdate    // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   improved by: Pyerre
    // +   improved by: Theriault
    // *     example 1: checkdate(12, 31, 2000);
    // *     returns 1: true    // *     example 2: checkdate(2, 29, 2001);
    // *     returns 2: false
    // *     example 3: checkdate(03, 31, 2008);
    // *     returns 3: true
    // *     example 4: checkdate(1, 390, 2000);    // *     returns 4: false
    return m > 0 && m < 13 && y > 0 && y < 32768 && d > 0 && d <= (new Date(y, m, 0)).getDate();
}

function isAlpha(xStr) {
    var regEx = /^[a-zA-Z\-]+$/;
    //alert(xStr.match(regEx));
    return xStr.match(regEx);
}

function isNum(xStr) {
    var regEx = /^[0-9\-]+$/;
    //alert(xStr.match(regEx));
    return xStr.match(regEx);
}

function isALNum(xStr) {
    var regEx = /^[a-zA-Z0-9\-]+$/;
    //alert(xStr.match(regEx));
    return xStr.match(regEx);
}

function form_fuellen(name, vorname, str, nr, plz, ort, partner_id) {
    document.getElementById('name').value = name;
    document.getElementById('vorname').value = vorname;
    document.getElementById('str').value = str;
    document.getElementById('nr').value = nr;
    document.getElementById('plz').value = plz;
    document.getElementById('ort').value = ort;
    document.getElementById('kunde').checked = true;

    document.getElementById('partner_id').value = partner_id;

    daj2('/wartungsplaner/index_ajax?option=reg_ziel_lonlat&str=' + encodeURIComponent(str) + '&nr=' + nr + '&plz=' + plz + '&ort=' + encodeURIComponent(ort) + '&partner_id=' + partner_id, '');
    //alert ('index_ajax.php?option=reg_ziel_lonlat&str='+str+'&nr='+nr+'&plz='+plz+'&ort='+ort);
}

function karte_anzeigen(target, poi_url) {
    //alert(target, poi_url);
    map = OpenLayers.Map(document.getElementById(target));
    map.addLayer(new OpenLayers.Layer.OSM());

    var pois = new OpenLayers.Layer.Text("My Points",
        {
            location: "/wartungsplaner/index_ajax?option=termine_anzeigen_sql",
            projection: map.displayProjection
        });

    map.addLayer(pois);

    //Set start centrepoint and zoom
    var lonLat = new OpenLayers.LonLat(13.41, 52.52)
        .transform(
            new OpenLayers.Projection("EPSG:4326"), // transform from WGS 1984
            map.getProjectionObject() // to Spherical Mercator Projection
        );
    var zoom = 11;
    map.setCenter(lonLat, zoom);
}


function bing_init() {

    map = new OpenLayers.Map("rightBox1");
    map.addControl(new OpenLayers.Control.LayerSwitcher());

    var shaded = new OpenLayers.Layer.VirtualEarth("Shaded", {
        type: VEMapStyle.Shaded
    });
    var hybrid = new OpenLayers.Layer.VirtualEarth("Hybrid", {
        type: VEMapStyle.Hybrid
    });
    var aerial = new OpenLayers.Layer.VirtualEarth("Aerial", {
        type: VEMapStyle.Aerial
    });

    map.addLayers([shaded, hybrid, aerial]);

    map.setCenter(new OpenLayers.LonLat(13.2637400, 52.4866100), 10);


}


function yes_no(on_yes, on_no, frage) {
    // alert(on_yes, on_no, frage);
    if (frage == null) {
        var frage = "Sind Sie sicher?";
    }
    Check = confirm(frage);
    if (Check == false) {
        var on_no_arr = on_no.split("|");

        var funktion = on_no_arr[0];
        var zahl_no = on_no_arr.length;
        var parameter = '';
        for (i = 1; i < zahl_no; i++) {
            var param = "'" + on_no_arr[i] + "'";
            parameter = parameter + ', ' + param;
        }
        parameter = parameter.substr(1);

        var ausfuehren = funktion + "(" + parameter + ");";

        if (typeof window[funktion] == 'function') {
            var ret = eval(ausfuehren);
        }
        else {
            alert("FEHLER -> Funktion: " + funktion + " existiert nicht!");
        }
    }
    else {

        var on_yes_arr = on_yes.split("|");
        var funktion = on_yes_arr[0];
        var zahl_yes = on_yes_arr.length;
        var parameter = '';
        for (i = 1; i < zahl_yes; i++) {
            var param = "'" + on_yes_arr[i] + "'";
            parameter = parameter + ', ' + param;
        }
        parameter = parameter.substr(1);

        var ausfuehren = funktion + "(" + parameter + ");";

        if (typeof window[funktion] == 'function') {
            eval(ausfuehren);
        }
        else {
            alert("FEHLER -> Funktion: " + funktion + " existiert nicht!");
        }
    }


}

function neues_team() {
    var team_bez = prompt('Teambezeichnung bitte eingeben', 'Team');
    daj3('/wartungsplaner/ajax?option=neues_team&team_bez=' + encodeURIComponent(team_bez), 'leftBox1');
    daj3('/wartungsplaner/ajax?option=mitarbeiter', 'leftBox');
}


function yes_no_frage(on_yes, on_no, frage) {

    Check = confirm(frage);
    if (Check == false) {
        var on_no_arr = on_no.split("|");

        var funktion = on_no_arr[0];
        var zahl_no = on_no_arr.length;
        var parameter = '';
        for (i = 1; i < zahl_no; i++) {
            var param = "'" + on_no_arr[i] + "'";
            parameter = parameter + ', ' + param;
        }
        parameter = parameter.substr(1);

        var ausfuehren = funktion + "(" + parameter + ");";

        if (typeof window[funktion] == 'function') {
            var ret = eval(ausfuehren);
        }
        else {
            alert("FEHLER -> Funktion: " + funktion + " existiert nicht!");
        }
    }
    else {

        var on_yes_arr = on_yes.split("|");
        var funktion = on_yes_arr[0];
        var zahl_yes = on_yes_arr.length;
        var parameter = '';
        for (i = 1; i < zahl_yes; i++) {
            var param = "'" + on_yes_arr[i] + "'";
            parameter = parameter + ', ' + param;
        }
        parameter = parameter.substr(1);

        var ausfuehren = funktion + "(" + parameter + ");";

        if (typeof window[funktion] == 'function') {
            eval(ausfuehren);
        }
        else {
            alert("FEHLER -> Funktion: " + funktion + " existiert nicht!");
        }
    }


}


function partner_pruefen(url, target) {
    var merror = '';
    if (!check_value('partner_name')) {
        merror = 'Partnernamen eingeben!\n';
    }
    if (!check_value('strasse')) {
        merror += 'Strassennamen eingeben!\n';
    }
    if (!check_value('nr')) {
        merror += 'Hausnummer eingeben!\n';
    }

    if (!check_value('plz')) {
        merror += 'PLZ eingeben!\n';
    }


    if (check_value('plz')) {
        if (!isNum(document.getElementById('plz').value)) {
            merror += 'PLZ ist keine Zahl!\n';
        }
    }

    if (!check_value('ort')) {
        merror += 'Ort eingeben!\n';
    }
    if (!check_value('land')) {
        merror += 'Land eingeben!\n';
    }

    if (!check_value('tel') && !check_value('mobil')) {
        merror += 'Telefonnummer oder Handynummer erforderlich!\n';
    }

    if (merror == '') {
        //alert('alles OK');
        partner_name = document.getElementById('partner_name').value;
        str = document.getElementById('strasse').value;
        nr = document.getElementById('nr').value;
        plz = document.getElementById('plz').value;
        ort = document.getElementById('ort').value;
        land = document.getElementById('land').value;
        wohnlage = document.getElementById('wohnlage').value;
        tel = document.getElementById('tel').value;
        mobil = document.getElementById('mobil').value;
        email = document.getElementById('email').value;
        var new_url = url + '&partner_name=' + partner_name + '&str=' + str + '&nr=' + nr + '&plz=' + plz + '&ort=' + ort + '&land=' + land + '&tel=' + tel + '&mobil=' + mobil + '&email=' + email + '&wohnlage=' + wohnlage;
        daj3(new_url, target);
        daj3('/wartungsplaner/ajax?option=nachricht&nachricht=' + foto, 'leftBox');
        daj3('/wartungsplaner/ajax?option=partner_waehlen', 'leftBox');
        daj3('/wartungsplaner/ajax?option=get_partner_info', 'leftBox1');
        daj3('/wartungsplaner/ajax?option=wartungsteil_waehlen', 'leftBox');
        daj3('/wartungsplaner/ajax?option=zeige_reservierung', 'rightBox1');
    } else {
        alert(merror);
    }
}


function partner_form_del_value() {
    del_value('partner_name');
    del_value('strasse');
    del_value('nr');
    del_value('plz');
    del_value('ort');
    del_value('land');
}

function del_value(targ) {
    document.getElementById(targ).value = '';
}

function check_value(targ) {
    if (document.getElementById(targ) == null) {
        alert('Feld <b>' + targ + '</b> existiert nicht');
        return false;
    } else {
        if (document.getElementById(targ).value == '') {
            return false;
        } else {
            return true;
        }
    }
}

function drop_change_check(drop_id, target_deactivate) {
    var element = document.getElementById(drop_id);
    var selected_index = document.getElementById(drop_id).selectedIndex;
    // alert(selected_index);

    if (element.options[selected_index].value) {
        //alert(element.options[selected_index].value);
        document.getElementById(target_deactivate).disabled = true;
        document.getElementById(target_deactivate).value = element.options[selected_index].text;
    } else {
        document.getElementById(target_deactivate).disabled = false;
        document.getElementById(target_deactivate).value = '';
        document.getElementById(target_deactivate).focus();
    }
}

function lade_dropdown2(drop_id, drop_new, url_param) {
    //alert(drop_id + drop_new + url_param);
    var element = document.getElementById(drop_id);
    var element1 = document.getElementById(drop_new);

    var selected_index = element.selectedIndex;

    if (!element.options[selected_index].value) {
        element1.disabled = true;
        document.getElementById('bez').value = '';
        document.getElementById('bez').disabled = false;
    }


    if (element.options[selected_index].value) {
        /*Laden*/
        var gewaehlt = element.options[selected_index].text;
        var gewaehlt_wert = element.options[selected_index].value;
        //alert(url_param+'&param='+gewaehlt_wert);
        antwort = get_from_ajax(url_param + '&param=' + gewaehlt_wert);
        antwort_arr = antwort.split('|');
        var anz = antwort_arr.length;
        my_arr = new Array(anz - 1);
        for (i = 0; i < anz - 1; i++) {
            zeile_arr = antwort_arr[i].split(',');
            anz_spalten = zeile_arr.length;
            my_arr[i] = new Array(i < anz - 1);
            for (a = 0; a < anz_spalten; a++) {
                zwert = zeile_arr[a];
                my_arr[i][a] = zwert;
            }

        }
        if (my_arr.length > 0) {
            //alert('array ok');
            anz = my_arr.length;
            //alert(anz);
            for (e = 0; e < anz + 1; e++) {
                if (e == 0) {
                    element1.options[e].value = null;
                    element1.options[e].text = 'Bitte wählen';
                } else {
                    element1.options[e].value = my_arr[e - 1][0];
                    element1.options[e].text = my_arr[e - 1][1];
                }
            }
            element1.disabled = false;
            document.getElementById(element1).focus();
        }

        if (my_arr.length == 0) {
            anz = my_arr.length;
            //alert(anz);
            element1.disabled = true;
            document.getElementById('bez').disabled = false;
            document.getElementById('bez').focus();
            alert('Keine Daten zur Auswahl!');
        }

    } else {

    }

}

function lade_dropdown(drop_id, drop_text, drop_new, url_param) {
    // alert(drop_id + drop_text + drop_new + url_param);
    var element = document.getElementById(drop_id);
    var element1 = document.getElementById(drop_new);
    var selected_index = element.selectedIndex;

    if (!element.options[selected_index].value) {
        element1.disabled = true;
        document.getElementById(drop_text).value = '';
        document.getElementById(drop_text).disabled = false;
    }


    if (element.options[selected_index].value) {
        /*Laden*/
        var gewaehlt = element.options[selected_index].text;
        var gewaehlt_wert = element.options[selected_index].value;
        //alert(url_param+'&param='+gewaehlt_wert);
        //var uri_new = encodeURI(url_param+'&param='+gewaehlt_wert);
        antwort = get_from_ajax(url_param + '&param=' + gewaehlt_wert);
//			antwort = get_from_ajax(uri_new);
        //alert(escape(uri_new));

        //alert(url_param+'&param='+gewaehlt_wert);
        antwort_arr = antwort.split('|');
        var anz = antwort_arr.length;
        my_arr = new Array(anz - 1);
        for (i = 0; i < anz - 1; i++) {
            zeile_arr = antwort_arr[i].split(',');
            anz_spalten = zeile_arr.length;
            my_arr[i] = new Array(i < anz - 1);
            for (a = 0; a < anz_spalten; a++) {
                zwert = zeile_arr[a];
                my_arr[i][a] = zwert;
            }

        }
        if (my_arr.length > 0) {
            //alert('array ok');
            anz = my_arr.length;
            //alert(anz);
            element1.options.length = anz + 1;
            for (e = 0; e < anz + 1; e++) {
                if (e == 0) {
                    element1.options[e].value = null;
                    element1.options[e].text = 'Bitte wählen';
                } else {
                    element1.options[e].value = my_arr[e - 1][0];
                    element1.options[e].text = my_arr[e - 1][1];

                }
            }
            element1.disabled = false;
            //document.getElementById(element1).focus();
        }

        if (my_arr.length == 0) {
            anz = my_arr.length;
            //alert(anz);
            element1.disabled = true;
            document.getElementById(drop_text).disabled = false;
            document.getElementById(drop_text).focus();
            alert('Keine Daten zur Auswahl!');
        }

    } else {

    }

}


function detail_speichern(url, target) {
    var det_name = 'detail_name';
    var det_inhalt = 'detail_inhalt';
    var m_error = '';
    if (!check_value(det_name)) {
        m_error += 'Geben Sie bitte die Detailbezeichnung ein!\n';
    }

    if (!check_value(det_inhalt)) {
        m_error += 'Geben Sie bitte den Detailinhalt ein!';
    }


    if (m_error != '') {
        alert(m_error);
    } else {
        var detail_name_value = document.getElementById(det_name).value;
        var detail_inhalt_value = document.getElementById(det_inhalt).value;
        //alert(detail_name_value + detail_inhalt_value);
        url += '&detail_name=' + detail_name_value + '&detail_inhalt=' + detail_inhalt_value;
        daj3(url, target);
        // alert (url+target);
    }


}

function detail_speichern2(url, target, prefix) {
    var det_name = prefix + 'detail_name';
    var det_inhalt = prefix + 'detail_inhalt';
    var m_error = '';
    if (!check_value(det_name)) {
        m_error += 'Geben Sie bitte die Detailbezeichnung ein!\n';
    }

    if (!check_value(det_inhalt)) {
        m_error += 'Geben Sie bitte den Detailinhalt ein!';
    }


    if (m_error != '') {
        alert(m_error);
    } else {
        var detail_name_value = document.getElementById(det_name).value;
        var detail_inhalt_value = document.getElementById(det_inhalt).value;
        //alert(detail_name_value + detail_inhalt_value);
        url += '&detail_name=' + detail_name_value + '&detail_inhalt=' + detail_inhalt_value;
        daj3(url, target);
        // alert (url+target);
    }


}

function detail_form_del_value(url, targ) {
    del_value('detail_name');
    del_value('detail_inhalt');
}

function text_kuerzen(feld_id, max_zeichen) {
    if (document.getElementById(feld_id).value.length > max_zeichen) {
        document.getElementById(feld_id).value = document.getElementById(feld_id).value.substr(0, max_zeichen);
        alert('Eingabe auf ' + max_zeichen + ' Zeichen begrenzt!');
    }
}

function wgeraet_pruefen(url, target) {
    //alert(url + target);
    var m_error = '';
    if (!check_value('gbez')) {
        m_error += 'Geben Sie bitte die Gruppenbezeichnung ein!\n';
    }
    if (!check_value('hersteller')) {
        m_error += 'Geben Sie bitte den Hersteller ein!\n';
    }

    if (!check_value('modell_bez')) {
        m_error += 'Geben Sie bitte das Model bzw. die Bezeichnung ein!\n';
    }

    if (!check_value('baujahr')) {
        m_error += 'Geben Sie bitte das Baujahr ein!';
        var AktuellesDatum = new Date();
        var jahr = AktuellesDatum.getYear() - 100 + 2000;
        document.getElementById('baujahr').value = jahr;
    }

    if (!check_value('lage_raum')) {
        m_error += 'Geben Sie bitte die Lage des Gerätes ein!\n';
        m_error += 'LAGE BEI EXTERNEN IST RAUM, BEI INTERNEN WOHNUNGSNUMMER!\n';
    }


    if (document.getElementById('rech_ansch_ab_ja').checked) {
        if (!check_value('zustell_ans')) {
            m_error += 'Geben Sie bitte die Rechnungsanschrift ein!\n';
            document.getElementById('zustell_ans').focus();
        } else {
            var zustell_anschrift = nltobr(document.getElementById('zustell_ans').value);
        }
    } else {
        var zustell_anschrift = '';
    }

    if (m_error != '') {
        alert(m_error);
    } else {
        var gbez = document.getElementById('gbez').value;
        var her = document.getElementById('hersteller').value;
        var mod = document.getElementById('modell_bez').value;
        var bj = document.getElementById('baujahr').value;
        var lr = document.getElementById('lage_raum').value;
        var wartungsint = document.getElementById("wartungsintervall");
        var selected_index = wartungsint.selectedIndex;
        var monate = wartungsint.options[selected_index].value;
        url += '&gbez=' + gbez + '&hersteller=' + her + '&modell=' + mod + '&baujahr=' + bj + '&lage_raum=' + lr + '&zustell_ans=' + zustell_anschrift + '&wartungsintervall=' + monate;
        daj3(url, target);

    }
}

function reservieren_php(ziel) {
    daj3('/wartungsplaner/ajax?option=zeige_reservierung', ziel);

}


function preloading(target, msg) {
    if (document.getElementById(target) != null) {
        document.getElementById(target).innerHTML = msg;
    } else {
        /*document.write('LADE');*/
    }
}


function nltobr(string) {
    if (typeof(string) == "string") return string.replace(/(\r\n)|(\n\r)|\r|\n/g, "<BR>");
    else return string;
}


function set_height(id, wert) {
    document.getElementById(id).style.height = wert + "px";
}

function termin_suchen_btn(datum_ab) {
    //termin_reservieren('', '', '', '');

    var err = '';
    if (!datum_ab) {
        var datum_ab = document.getElementById("datum_ab").value;
        if (datum_ab == '') {
            err += 'Bitte Datum eingeben\n';
        } else {
            if (!checkdatum(datum_ab)) {
                err += 'Datumseingabe fehlerhaft\n';
            }
        }
    }

    var element1 = document.getElementById("g_id");
    var selected_index = element1.selectedIndex;
    //alert(element1.options[selected_index].text);
    if (element1.options[selected_index].text == 'Bitte wählen') {
        err += 'Wartungsteil wählen\n';
    }

    if (err != '') {
        alert(err);

    } else {
        //alert('Termin wird gesucht' + element1.options[selected_index].text + document.getElementById("datum_ab").value );
        var g_id = element1.options[selected_index].value;
        document.getElementById("leftBox1").innerHTML = '';
        document.getElementById("rightBox1").innerHTML = '';
        //daj3('ajax.php?option=geraete_info_anzeigen&g_id='+g_id,'rightBox');
        daj3('/wartungsplaner/ajax?option=termin_suchen&g_id=' + g_id + '&datum_ab=' + datum_ab, 'leftBox1');
        daj3('/wartungsplaner/ajax?option=get_datum_lw&g_id=' + g_id, 'lw_datum');
    }
}

function termin_suchen_btn1(datum_ab) {
    //termin_reservieren('', '', '', '');
    var err = '';
    if (!datum_ab) {
        var datum_ab = document.getElementById("datum_ab").value;
        if (datum_ab == '') {
            err += 'Bitte Datum eingeben\n';
        } else {
            if (!checkdatum(datum_ab)) {
                err += 'Datumseingabe fehlerhaft\n';
            }
        }
    }

    var element1 = document.getElementById("g_id");
    var selected_index = element1.selectedIndex;
    //alert(element1.options[selected_index].text);
    if (element1.options[selected_index].text == 'Bitte wählen') {
        err += 'Wartungsteil wählen\n';
    }

    if (err != '') {
        alert(err);

    } else {
        //alert('Termin wird gesucht' + element1.options[selected_index].text + document.getElementById("datum_ab").value );
        var g_id = element1.options[selected_index].value;
        document.getElementById("leftBox1").innerHTML = '';
        document.getElementById("rightBox1").innerHTML = '';
        //daj3('ajax.php?option=geraete_info_anzeigen&g_id='+g_id,'rightBox');
        daj3('/wartungsplaner/ajax?option=termin_suchen_neu&g_id=' + g_id + '&datum_ab=' + datum_ab, 'leftBox1');
        daj3('/wartungsplaner/ajax?option=get_datum_lw&g_id=' + g_id, 'lw_datum');
    }
}


function termin_suchen_btn2(datum_ab) {
    //termin_reservieren('', '', '', '');
    var err = '';
    if (!datum_ab) {
        var datum_ab = document.getElementById("datum_ab").value;
        if (datum_ab == '') {
            err += 'Bitte Datum eingeben\n';
        } else {
            if (!checkdatum(datum_ab)) {
                err += 'Datumseingabe fehlerhaft\n';
            }
        }
    }

    var element1 = document.getElementById("g_id");
    var selected_index = element1.selectedIndex;
    //alert(element1.options[selected_index].text);
    if (element1.options[selected_index].text == 'Bitte wählen') {
        err += 'Wartungsteil wählen\n';
    }

    if (err != '') {
        alert(err);

    } else {
        //alert('Termin wird gesucht' + element1.options[selected_index].text + document.getElementById("datum_ab").value );
        var g_id = element1.options[selected_index].value;
        document.getElementById("leftBox1").innerHTML = '';
        document.getElementById("rightBox1").innerHTML = '';
        //daj3('ajax.php?option=geraete_info_anzeigen&g_id='+g_id,'rightBox');
        daj3('/wartungsplaner/ajax?option=termin_suchen4&g_id=' + g_id + '&datum_ab=' + datum_ab, 'leftBox1');
        daj3('/wartungsplaner/ajax?option=get_datum_lw&g_id=' + g_id, 'lw_datum');
    }
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
    dauer_std = (dauer_min / 60).toFixed(2);
    //alert(dauer_min+' Min /' + dauer_std+ ' Std');
    document.getElementById(ziel_id).value = dauer_min + ' Min | ' + dauer_std + ' Std';
    document.getElementById(hidd_ziel_id).value = dauer_min;
}


function termin_pruefen(url, target) {
    var merror = '';
    if (!check_value('text')) {
        merror = 'Geben Sie bitte Ihren Text ein !\n';
    }
    if (!check_value('hinweis')) {
        merror += 'Geben Sie bitte Ihren Hinweis ein !\n';
    }


    if (merror == '') {
        var von_elem = document.getElementById("von");
        var von = von_elem.options[von_elem.selectedIndex].value.toString();

        var bis_elem = document.getElementById("bis");
        var bis = bis_elem.options[bis_elem.selectedIndex].value.toString();

        var text = document.getElementById('text').value;
        var hinweis = document.getElementById('hinweis').value;

        var new_url = url + '&von=' + von + '&bis=' + bis + '&text=' + text + '&hinweis=' + hinweis;
        daj3(new_url, target);

        if (document.getElementById('leftBox1')) {
            //  js = "setTimeout('daj3(\'ajax.php?option=get_partner_info\', \'rightBox\')', 1000);\"";
            //setTimeout('daj3(\'ajax.php?option=termin_vorschlaege_kurz\', \'leftBox1\')', 1000);
            // setTimeout('vorschlag()','1500');
        }
    } else {
        alert(merror);
    }


}

function wochenkalender() {
    window.open('/wartungsplaner/ajax?option=wochenkalender', 'Wochenkalender');
}

function vorschlag() {
    document.getElementById('rightBox1').innerHTML = '';
    document.getElementById('leftBox1').innerHTML = '';
    daj3('/wartungsplaner/ajax?option=termin_vorschlaege_kurz', 'leftBox1');
}


function vorschlag_chrono() {
    document.getElementById('rightBox1').innerHTML = '';
    document.getElementById('leftBox1').innerHTML = '';
    daj3('/wartungsplaner/ajax?option=termin_vorschlaege_kurz_chrono', 'leftBox1');
}


function mitarbeiter() {
    document.getElementById('rightBox1').innerHTML = '';
    document.getElementById('leftBox1').innerHTML = '';
    daj3('/wartungsplaner/ajax?option=mitarbeiter', 'leftBox');
    document.getElementById('rightBox').innerHTML = '';
}


function auto_change_profil(b_id, spalte, wert) {
    //alert(b_id+ spalte+ wert);
    daj3('/wartungsplaner/ajax?option=auto_change_profil&b_id=' + b_id + '&spalte=' + spalte + '&wert=' + wert, 'rightBox1');
    daj3('/wartungsplaner/ajax?option=mitarbeiter_profil&b_id=' + b_id + '&spalte=' + spalte + '&wert=' + wert, 'leftBox1');

}

function entf_mitarbeiter_team(team_id, id) {
    var element = document.getElementById(id);
    var selected_index = element.selectedIndex;
    if (selected_index > -1) {
        var b_id = element.options[selected_index].value;
        daj3('/wartungsplaner/ajax?option=mitarbeiter_entfernen&b_id=' + b_id + '&team_id=' + team_id, 'leftBox1');
        daj3('/wartungsplaner/ajax?option=mitarbeiter_wahl&team_id=' + team_id, 'rightBox');
        daj3('/wartungsplaner/ajax?option=mitarbeiter_n_team&team_id=' + team_id, 'rightBox1');
    } else {
        alert('Mitarbeiter zum Entfernen wählen!');
    }
}

function hinzu_mitarbeiter_team(team_id, id) {
    var element = document.getElementById(id);
    var selected_index = element.selectedIndex;
    if (selected_index > -1) {
        var b_id = element.options[selected_index].value;
        daj3('/wartungsplaner/ajax?option=mitarbeiter_hinzu&b_id=' + b_id + '&team_id=' + team_id, 'leftBox1');
        daj3('/wartungsplaner/ajax?option=mitarbeiter_n_team&team_id=' + team_id, 'rightBox1');
        daj3('/wartungsplaner/ajax?option=mitarbeiter_wahl&team_id=' + team_id, 'rightBox');

    } else {
        alert('Mitarbeiter zum Hinzufügen wählen!');
    }
}


function termin_loeschen(termin_dat) {
    //alert(termin_id);
    yes_no_frage('daj3|/wartungsplaner/ajax?option=termin_loeschen&termin_dat=' + termin_dat + '|rightBox1', 'alert|Termin wurde nicht gelöscht|rightBox1', 'Sind Sie wirklich sicher???');
    /*daj3('ajax.php?option=termin_vorschlaege_kurz', 'leftBox1');
     daj3('ajax.php?option=termin_vorschlaege_kurz', 'leftBox1');*/

}

function geraete_listen() {
    /*daj3('ajax.php?option=geraete_liste', 'leftBox');*/
    window.open('/wartungsplaner/ajax?option=geraete_liste', 'Geräteliste');
}

function kundschaft() {
    /*daj3('ajax.php?option=geraete_liste', 'leftBox');*/
    window.open('/wartungsplaner/ajax?option=karte_gross_alle', 'Karte Berlin');
}

function wopen(url) {
    /*daj3('ajax.php?option=geraete_liste', 'leftBox');*/
    window.location.href = url;
}

function umkreissuche(ziel) {
    var m_error = '';
    if (!check_value('strasse')) {
        m_error += 'Geben Sie bitte die Strasse ein!\n';
    }
    if (!check_value('nr')) {
        m_error += 'Geben Sie bitte die Strassennummer ein!\n';
    }
    if (!check_value('plz')) {
        m_error += 'Geben Sie bitte die PLZ ein!\n';
    }
    if (!check_value('ort')) {
        m_error += 'Geben Sie bitte den Ort ein!\n';
    }
    if (m_error == '') {
        // alert('alles OK');
        var str = document.getElementById('strasse').value;
        var nr = document.getElementById('nr').value;
        var plz = document.getElementById('plz').value;
        var ort = document.getElementById('ort').value;
        daj3('/wartungsplaner/ajax?option=umkreissuche&str=' + str + '&nr=' + nr + '&plz=' + plz + '&ort=' + ort, ziel);
    } else {
        alert(m_error);
    }


}

function uebernahme_p_daten(p_id) {
    var erg = get_from_ajax('/wartungsplaner/ajax?option=get_partner_daten&p_id=' + p_id);
    //alert(erg);
    if (erg != null) {
        daj3('/wartungsplaner/ajax?option=kos_typ_register&kos_typ=Partner&kos_id=' + p_id, 'ohne_ziel');
        var erg_arr = erg.split('|');
        document.getElementById('partner_name').value = unescape(erg_arr[0]);
        document.getElementById('strasse').value = erg_arr[1];
        document.getElementById('nr').value = erg_arr[2];
        document.getElementById('plz').value = erg_arr[3];
        document.getElementById('ort').value = erg_arr[4];
        //umkreissuche('rightBox');
    }
}

function termin_dauer_aendern(drop_id) {
    if (drop_id != null) {
        var element = document.getElementById(drop_id);
        var selected_index = element.selectedIndex;

        var dauer = element.options[selected_index].value;
    } else {
        var dauer = 60;
    }
    daj3('/wartungsplaner/ajax?option=termin_dauer_aendern&termin_dauer=' + dauer, 'ohne_ziel');

}

function zumAnker(anker) {
    //alert(anker);
    //window.location.hash=anker;

}

function termin_reservieren(datum, von, bis, b_id) {
    /*if(document.getElementById('btn_reserve'+von+bis).value == 'Reserviert'){
     document.getElementById('btn_reserve'+von+bis).value = 'Termin vormerken';
     document.getElementById('btn_reserve'+von+bis).style.color = 'black';
     }else{
     document.getElementById('btn_reserve'+von+bis).value = 'Reserviert';
     document.getElementById('btn_reserve'+von+bis).style.color = 'red';
     daj3('ajax.php?option=termin_reservieren&datum='+datum+'&von='+von+'&bis='+bis, 'rightBox1');
     }*/
    daj3('/wartungsplaner/ajax?option=termin_reservieren&datum=' + datum + '&von=' + von + '&bis=' + bis + '&b_id=' + b_id, 'rightBox1');
    if (document.getElementById('partner_name').value != null) {
        document.getElementById('partner_name').focus();
    } else {

        if (document.getElementById('wohnlage').value != null) {
            document.getElementById('wohnlage').focus();
        }
    }

}
