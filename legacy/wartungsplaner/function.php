<?php
function tage_plus_wp($datum, $tage)
{
    $dat_arr = explode('.', $datum);
    $j = $dat_arr[2];
    $m = $dat_arr[1];
    $d = $dat_arr[0];
    return date('d.m.Y', mktime(0, 0, 0, $m, $d + $tage, $j));
}

function tage_minus_wp($datum, $tage)
{
    $dat_arr = explode('.', $datum);
    $j = $dat_arr[2];
    $m = $dat_arr[1];
    $d = $dat_arr[0];
    return date('d.m.Y', mktime(0, 0, 0, $m, $d - $tage, $j));
}


function wochentag($datum)
{
    $dat_arr = explode('.', $datum);
    $j = $dat_arr[2];
    $m = $dat_arr[1];
    $d = $dat_arr[0];
    $timestamp = mktime(0, 0, 0, $m, $d, $j);
    $tage = array("Sonntag", "Montag", "Dienstag", "Mittwoch", "Donnerstag", "Freitag", "Samstag");
    $tag = $tage[date("w", $timestamp)];
    return $tag;
}

function datum_montag_kw($kw)
{
    $jahr = date("Y");
    echo date('d.m.Y', strtotime('Monday', kalenderwoche($kw, $jahr)));
}


function kw($datum)
{
    $dat_arr = explode('.', $datum);
    $j = $dat_arr[2];
    $m = $dat_arr[1];
    $d = $dat_arr[0];
    $timestamp = mktime(0, 0, 0, $m, $d, $j);
    $kw = date("W", $timestamp);
    return $kw;
}

/*berlussimo funktionen*/
function partner_in_array()
{
    $result = DB::select("SELECT * FROM PARTNER_LIEFERANT WHERE AKTUELL = '1' ORDER BY PARTNER_NAME ASC");
    if (!empty($result)) {
        return $result;
    } else {
        return false;
    }
}


function get_partner_info($partner_id)
{
    $result = DB::select("SELECT * FROM PARTNER_LIEFERANT WHERE PARTNER_ID='$partner_id' && AKTUELL = '1'");
    if(!empty($result)) {
        $row = $result[0];
        $this->partner_dat = $row['PARTNER_DAT'];
        $this->partner_name = $row['PARTNER_NAME'];
        $this->partner_strasse = $row['STRASSE'];
        $this->partner_hausnr = $row['NUMMER'];
        $this->partner_plz = $row['PLZ'];
        $this->partner_ort = $row['ORT'];
        $this->partner_land = $row['LAND'];
    }
}


function get_benutzername($benutzer_id)
{
    $user = \App\Models\User::find($benutzer_id);
    return isset($user) ? $user->name : null;
}

function get_gewerk_id($benutzer_id)
{
    $user = \App\Models\User::find($benutzer_id);
    return isset($user) ? $user->trade_id : null;
}


function get_lon_lat_osm($str, $nr, $plz, $ort, $w_datum)
{
    if (empty($w_datum)) {
        $w_datum = date("d.m.Y");
    }
    session()->put('w_datum', $w_datum);

    $lat_lon = get_lat_lon_db($str, $nr, $plz, $ort);
    if (!empty($lat_lon)) {
        echo $lat_lon;
        die();
    }

    $url = "http://maps.google.com/maps/api/directions/xml?origin=" . "$str $nr, $plz $ort " . " &destination=Sansibarstr 12, 13351 Berlin&sensor=false";
    $xml = simplexml_load_file($url);
    sleep(2);
    if (!$xml === FALSE) {
        $status = $xml->status;
        if ($status == 'OK') {
            $lat = $xml->route->leg->step->start_location->lat;
            $lon = $xml->route->leg->step->start_location->lng;
            echo "$lat, $lon, google";
        }
    }
    if (empty($lat) && empty($lon)) {

        $url = "http://nominatim.openstreetmap.org/search?q=$str $nr $plz $ort&format=xml";
        $xml = simplexml_load_file("$url");
        $vars = get_object_vars($xml->place);
        $lat = $vars['@attributes']['lat'];
        $lon = $vars['@attributes']['lon'];
        if (!empty($lat) && !empty($lon)) {
            echo "$lat, $lon, openstreetmap";
        }
    }

    if (!empty($lat) && !empty($lon)) {
        if (!check_str($str, $nr, $plz, $ort)) {
            $db_abfrage = "INSERT INTO GEO_LON_LAT VALUES (NULL, '$str', '$nr', '$plz', '$ort','$lon','$lat','1')";
            DB::insert($db_abfrage);
        }
    }

}

function check_str($str, $nr, $plz, $ort)
{
    $db_abfrage = "SELECT * FROM GEO_LON_LAT WHERE STR='$str' && NR='$nr' && PLZ='$plz' && ORT='$ort'";
    $result = DB::select($db_abfrage);
    return !empty($result);

}

function get_lat_lon_db($str, $nr, $plz, $ort)
{
    $db_abfrage = "SELECT LAT, LON FROM GEO_LON_LAT WHERE STR='$str' && NR='$nr' && PLZ='$plz' && ORT='$ort' LIMIT 0,1";
    $result = DB::select($db_abfrage);
    if (!empty($result)) {
        $row = $result[0];
        $lat = $row['LAT'];
        $lon = $row['LON'];
        return "$lat, $lon, DB";
    }

}

function get_tages_termine($datum, $benutzer_id)
{
    $datum_sql = date_german2mysql($datum);
    $db_abfrage = "SELECT * FROM GEO_TERMINE WHERE BENUTZER_ID='$benutzer_id' && DATUM>='$datum_sql' ORDER BY VON ASC, BIS ASC";
    $result = DB::select($db_abfrage);
    if (!empty($result)) {
        $ausgabe = "lat\tlon\ttitle\tdescription\ticon\ticonSize\ticonOffset\n";
        $bild = 1;
        $temp_datum = '';
        $farbe = 0;
        foreach ($result as $row) {
            $bild++;
            $datum = $row['DATUM'];
            if ($datum != $temp_datum) {
                $farbe++;
                $temp_datum = $datum;
                $bild = 1;
            }
            $text = $row['TEXT'];
            $hinweis = $row['HINWEIS'];
            $lonlat_id = $row['GEO_LONLAT_ID'];
            $lonlat_arr = explode(',', get_lonlat_values($lonlat_id));
            $lat = $lonlat_arr[0];
            $lon = $lonlat_arr[1];
            $icon = "http://berlussimo.berlus.de/cal/index_ajax?option=foto_anzeigen&zahl=$bild&h_farbe=$farbe";
            $iconsize = '16,16';
            $offset = '0,0';
            $desc = "$text Termin $bild";
            $title = $hinweis;
            $ausgabe .= "$lat\t$lon\t$title\t$desc\t$icon\t$iconsize\t$offset\n";
        }
        $ausgabe .= "\n";
        return $ausgabe;
    }
}


function get_km_osm($s_lon, $s_lat, $e_lon, $e_lat)
{
    $url = "http://open.mapquestapi.com/directions/v0/route?outFormat=xml&from=$s_lon,$s_lat&to=$e_lon,$e_lat&callback=renderNarrative";
    $xml = simplexml_load_file("$url");
    sleep(2);
    if ($xml === FALSE) {
        die();
    } else {
        return $xml->route->distance;
    }
}

function get_tages_termine_anzeigen($datum, $benutzer_id)
{
    $datum_plus = tage_plus_wp($datum, 6);
    echo $datum_plus;
    die();

    $db_abfrage = "SELECT * FROM GEO_TERMINE, GEO_LON_LAT WHERE BENUTZER_ID='$benutzer_id' && DATE_FORMAT(DATUM, '%d.%m.%Y') BETWEEN '$datum' AND $datum_plus && GEO_LONLAT_ID = GEO_LON_LAT.DAT ORDER BY VON ASC, BIS ASC";
    $result = DB::select($db_abfrage);
    if (!empty($result)) {
        $zeile = 0;
        foreach($result as $row) {
            $zeile++;
            $str = $row['STR'];
            $nr = $row['NR'];
            $von = $row['VON'];
            $bis = $row['BIS'];
            $text = $row['TEXT'];
            $hinweis = $row['HINWEIS'];

            echo "<tr class=\"termin$zeile\"><td>$datum</td><td>$von</td><td>$bis</td><td>$str, $nr<br>$text<br>$hinweis</td></tr>";
            if ($zeile == 2) {
                $zeile = 0;
            }

        }

    }
}

function get_lonlat_values($lonlatid)
{
    $db_abfrage = "SELECT LAT, LON FROM GEO_LON_LAT WHERE DAT='$lonlatid' LIMIT 0,1";
    $result = DB::select($db_abfrage);
    if (!empty($result)) {
        $row = $result[0];
        $lat = $row['LAT'];
        $lon = $row['LON'];
        return "$lat, $lon";
    }
}

function get_lonlat_id($lon, $lat)
{
    $db_abfrage = "SELECT DAT FROM GEO_LON_LAT WHERE LAT='$lat' && LON='$lon' LIMIT 0,1";
    $result = DB::select($db_abfrage);
    if (!empty($result)) {
        return $result[0]['DAT'];
    }
}

function erstelle_icon($zahl, $h_farbe)
{
    header("Content-Type: image/png");
    $bild = imagecreate(15, 15);
    $farben[0]['r'] = '3';
    $farben[0]['g'] = '70';
    $farben[0]['b'] = '252';
    $farben[1]['r'] = '126';
    $farben[1]['g'] = '3';
    $farben[1]['b'] = '252';
    $farben[2]['r'] = '252';
    $farben[2]['g'] = '3';
    $farben[2]['b'] = '126';
    $farben[3]['r'] = '3';
    $farben[3]['g'] = '252';
    $farben[3]['b'] = '144';
    $farben[4]['r'] = '185';
    $farben[4]['g'] = '252';
    $farben[4]['b'] = '3';
    $farben[5]['b'] = '252';
    $farben[5]['b'] = '120';
    $farben[5]['b'] = '3';

    $r = $farben[$h_farbe]['r'];
    $g = $farben[$h_farbe]['g'];
    $b = $farben[$h_farbe]['b'];

    imagecolorallocate($bild, $r, $g, $b);

    $text_farbe = imagecolorallocate($bild, 255, 255, 0);
    $textnr = 4;
    imagestring($bild, $textnr, 3, 0, $zahl, $text_farbe);
    imagepng($bild);
}


function test($anfang, $ende)
{
    echo '<pre>';
    $wochencal = create_w_array(date("d.m.Y"));
    $datums = array_keys($wochencal);
    $anz_tage = count($datums);
    echo "<table>";
    echo "<tr>";
    for ($a = 0; $a < $anz_tage; $a++) {
        $akt_datum = $datums[$a];
        $wochentag = wochentag($akt_datum);
        echo "<td>$wochentag, $akt_datum</td>";
    }
    echo "</tr>";

    echo "<tr>";
    for ($a = 0; $a < $anz_tage; $a++) {
        $akt_datum = $datums[$a];
        echo "<td>";

        $m_termine = count($wochencal[$akt_datum]);

        for ($b = 0; $b < $m_termine; $b++) {
            echo "<table>";
            $zeit = $wochencal[$akt_datum][$b]['ZEIT'];
            echo "<tr><td><b>$zeit</b></td>";
            $db_termine = get_termine_von_1tag($benutzer_id, $akt_datum);
            $anz = count($db_termine);
            if ($anz > 0) {
                for ($d = 0; $d < $anz; $d++) {
                    $text = $db_termine[$d]['TEXT'];
                    $von_arr = explode(':', $db_termine[$d]['VON']);
                    $v_std = sprintf("%02d", $von_arr[0]);
                    $v_min = sprintf("%02d", $von_arr[1]);
                    $bis_arr = explode(':', $db_termine[$d]['BIS']);
                    $b_std = sprintf("%02d", $bis_arr[0]);
                    $b_min = sprintf("%02d", $bis_arr[1]);
                    $zeit_arr = explode(':', $zeit);

                    $von = "$v_std$v_min";
                    $bis = "$b_std$b_min";
                    $zeit1 = str_replace(':', "", $zeit);
                    if (($zeit1 >= $von) && ($bis >= $zeit1)) {
                        echo "<td>$text</td>";
                    } else {
                        echo "<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>";
                    }

                }
            }
            echo "</tr>";
            echo "</table>";
        }

        echo "</td>";
    }
    echo "</tr>";
    echo "</table>";
}

function create_time_array()
{
    for ($a = 6; $a <= 16; $a++) {
        $a = sprintf("%02d", $a);
        $t1 = "$a:00";
        $t2 = "$a:15";
        $t3 = "$a:30";
        $t4 = "$a:45";
        $arr[]['ZEIT'] = $t1;
        $arr[]['ZEIT'] = $t2;
        $arr[]['ZEIT'] = $t3;
        $arr[]['ZEIT'] = $t4;
    }
    return $arr;
}

function get_montag_vor_kw($datum)
{
    $wochentag = get_wochentag($datum);
    $tage_bis_montag = $wochentag - 1;
    $datum_montag = tage_minus_wp($datum, ($tage_bis_montag + 7));
    return $datum_montag;
}

function get_montag_nach_kw($datum)
{
    $wochentag = get_wochentag($datum);
    $tage_bis_montag = $wochentag - 1;
    $datum_montag = tage_minus_wp($datum, ($tage_bis_montag - 7));
    return $datum_montag;
}

function create_w_array($datum)
{
    $kw = kw($datum);
    echo "KW: $kw<br>";
    $wochentag = get_wochentag($datum);
    $tage_bis_montag = $wochentag - 1;
    $datum_montag = tage_minus_wp($datum, $tage_bis_montag);

    for ($a = 0; $a < 7; $a++) {
        $n_tag = tage_plus_wp($datum_montag, $a);
        $wochencal[$n_tag] = create_time_array();
    }
    return $wochencal;
}


function get_wochentag($datum)
{
    $datum_arr = explode('.', $datum);
    $d = $datum_arr[0];
    $m = $datum_arr[1];
    $j = $datum_arr[2];
    $tstamp = mktime(0, 0, 0, $m, $d, $j);
    $tdatum = getdate($tstamp);
    $wday = $tdatum["wday"];
    #print_r($tdatum);
    return $wday;
}

function get_datum_infos($datum)
{
    $datum_arr = explode('.', $datum);
    $d = $datum_arr[0];
    $m = $datum_arr[1];
    $j = $datum_arr[2];
    $tstamp = mktime(0, 0, 0, $m, $d, $j);
    $tdatum = getdate($tstamp);
    return $tdatum;
}

function get_termine_von_kw($benutzer_id, $kw)
{
    $result = DB::select("SELECT *, DATE_FORMAT(DATUM, '%d.%m.%Y') AS D_GER FROM GEO_TERMINE WHERE BENUTZER_ID='$benutzer_id' && DATE_FORMAT(DATUM, '%u') = '$kw' ORDER BY DATUM ASC, VON ASC, BIS ASC");
    if (!empty($result)) {
        return $result;
    }
}

function get_termine_von_1tag($benutzer_id, $datum)
{
    $result = DB::select("SELECT *, DATE_FORMAT(DATUM, '%d.%m.%Y') AS D_GER FROM GEO_TERMINE WHERE BENUTZER_ID='$benutzer_id' && DATE_FORMAT(DATUM, '%d.%m.%Y') = '$datum' ORDER BY DATUM ASC, VON ASC, BIS ASC");
    if (!empty($result)) {
        return $result;
    }
}


function get_entfernung($lon, $lat)
{
    if (session()->has('w_datum')) {
        $result = DB::select("SELECT name, GEO_TERMINE.*, GEO_LON_LAT.*, DATE_FORMAT(GEO_TERMINE.DATUM, '%d.%m.%Y') AS DATUM_G, DATEDIFF(DATUM, DATE(NOW())) AS DIFF,round( acos( sin( $lat * ( pi( ) /180 ) ) * sin( LAT * ( pi( ) /180 ) ) + cos( $lat * ( pi( ) /180 ) ) * cos( LAT * ( pi( ) /180 ) ) * cos( ( $lon * ( pi( ) /180 ) ) - ( LON * ( pi( ) /180 ) ) ) ) *6370, 2 ) AS ENTF_KM, DATEDIFF(DATUM, DATE(NOW())) * round( acos( sin( $lat * ( pi( ) /180 ) ) * sin( LAT * ( pi( ) /180 ) ) + cos( $lat * ( pi( ) /180 ) ) * cos( LAT * ( pi( ) /180 ) ) * cos( ( $lon * ( pi( ) /180 ) ) - ( LON * ( pi( ) /180 ) ) ) ) *6370, 2 ) AS WERTUNG FROM `GEO_TERMINE`, GEO_LON_LAT, users WHERE GEO_LONLAT_ID = GEO_LON_LAT.DAT && DATUM >= DATE(NOW()) && users.id=GEO_TERMINE.BENUTZER_ID GROUP BY DATUM ORDER BY WERTUNG ASC, `ENTF_KM` ASC, DATUM ASC");
    } else {
        $wunschdatum = date_german2mysql(session()->get('w_datum'));
        $result = DB::select("SELECT name, GEO_TERMINE.*, GEO_LON_LAT.*, DATE_FORMAT(GEO_TERMINE.DATUM, '%d.%m.%Y') AS DATUM_G, DATEDIFF(DATUM, '$wunschdatum') AS DIFF,round( acos( sin( $lat * ( pi( ) /180 ) ) * sin( LAT * ( pi( ) /180 ) ) + cos( $lat * ( pi( ) /180 ) ) * cos( LAT * ( pi( ) /180 ) ) * cos( ( $lon * ( pi( ) /180 ) ) - ( LON * ( pi( ) /180 ) ) ) ) *6370, 2 ) AS ENTF_KM, DATEDIFF(DATUM, '$wunschdatum') * round( acos( sin( $lat * ( pi( ) /180 ) ) * sin( LAT * ( pi( ) /180 ) ) + cos( $lat * ( pi( ) /180 ) ) * cos( LAT * ( pi( ) /180 ) ) * cos( ( $lon * ( pi( ) /180 ) ) - ( LON * ( pi( ) /180 ) ) ) ) *6370, 2 ) AS WERTUNG FROM `GEO_TERMINE`, GEO_LON_LAT, users WHERE GEO_LONLAT_ID = GEO_LON_LAT.DAT && DATUM >= '$wunschdatum' && users.id=GEO_TERMINE.BENUTZER_ID GROUP BY DATUM ORDER BY WERTUNG ASC, `ENTF_KM` ASC, DATUM ASC");
    }

    if (!empty($result)) {
        echo "<table>";
        $zeile = 0;
        foreach ($result as $row) {
            $zeile++;
            $str = $row['STR'];
            $nr = $row['NR'];
            $ort = $row['ORT'];
            $entf = $row['ENTF_KM'];
            $datum = $row['DATUM_G'];
            $diff_tage = $row['DIFF'];
            $bn = $row['name'];
            $b_id = $row['BENUTZER_ID'];

            echo "<tr class=\"termin$zeile\" onclick=\"daj2('" . route('web::wartungsplaner::legacyAjax', ['option' => 'tages_termine', 'datum' => $datum, 'benutzer_id' => $b_id], false) . "',document.getElementById('leftBox1'))\"><td>$diff_tage T</td><td>$datum</td><td>$entf km</td><td>$str</td><td>$nr</td><td>$plz $ort</td><td>$bn</td></tr>";
            if ($zeile == 2) {
                $zeile = 0;
            }
        }
        echo "</table>";
    } else {
        echo "<table>";
        echo "<tr class=\"termin1\"><td>Keine vereinbarten Termine am " . session()->get('w_datum') . "!</td></tr>";
        echo "</table>";
    }
}


function getzeitdiff_min($anfangszeit, $endzeit)
{
    $anfangszeit_arr = explode(':', $anfangszeit);
    $a_std = $anfangszeit_arr[0];
    $a_min = $anfangszeit_arr[1];

    $endzeit_arr = explode(':', $endzeit);
    $e_std = $endzeit_arr[0];
    $e_min = $endzeit_arr[1];

    $t1 = mktime($a_std, $a_min, 0, 1, 12, 2000);
    $t2 = mktime($e_std, $e_min, 0, 1, 12, 2000);
    $diff_min = ($t2 - $t1) / 60;
    return $diff_min;
}


function zeitarray()
{
    $anfangszeit = '06:45';
    $endzeit = '15:15';
    $pause_min = '30';
    $diff = getzeitdiff_min($anfangszeit, $endzeit);
    $diff_a = $diff / 60;
    $a_zeit = $diff - $pause_min;
    $a_zeit_a = $a_zeit / 60;
    echo "Arbeitszeit = $a_zeit_a Std.<br>";
    echo "Pause = $pause_min<br>";
    echo "Arbeitszeit voll = $diff_a Std.<br>";

    $termine_arr[0]['VON'] = '06:45';
    $termine_arr[0]['BIS'] = '07:15';
    $termine_arr[1]['VON'] = '07:30';
    $termine_arr[1]['BIS'] = '08:30';
    $anz_termine = count($termine_arr);


    $max_quart = $diff / 15;
    $plus = 0;
    for ($a = 0; $a <= $max_quart; $a++) {
        $neue_zeit = zeit_plus_min($anfangszeit, $plus);
        $termin[$a]['zeit'] = $neue_zeit;
        echo "NEU $neue_zeit<br>";
        $plus += 15;
    }

    for ($a = 0; $a < $anz_termine; $a++) {
        $von = $termine_arr[$a]['VON'];
        $von_arr = explode(':', $von);
        $von_std = $von_arr[0];
        $von_min = $von_arr[1];
        $von_time = mktime($von_std, $von_min, 0, 1, 12, 2000);

        $bis = $termine_arr[$a]['BIS'];
        $bis_arr = explode(':', $bis);
        $bis_std = $bis_arr[0];
        $bis_min = $bis_arr[1];
        $bis_time = mktime($bis_std, $bis_min, 0, 1, 12, 2000);

        $dauer_zeilen = getzeitdiff_min($von, $bis) / 15;

        for ($b = 0; $b <= $max_quart; $b++) {
            $zeitfeld = $termin[$b]['zeit'];
            $zeitfeld_arr = explode(':', $zeitfeld);
            $zeitfeld_std = $zeitfeld_arr[0];
            $zeitfeld_min = $zeitfeld_arr[1];
            $zeitfeld_time = mktime($zeitfeld_std, $zeitfeld_min, 0, 1, 12, 2000);
            if ($zeitfeld_time >= $von_time AND $zeitfeld_time <= $bis_time) {
                $termin[$b]['termin'] = $a;
                $termin[$b]['zeilen'] = $dauer_zeilen;
            }
        }

    }
    echo '<pre>';
    print_r($termin);
}

function zeit_plus_min($zeit, $plusmin)
{
    $zeit_arr = explode(':', $zeit);
    $std = $zeit_arr[0];
    $min = $zeit_arr[1];
    $time = mktime($std, $min, 0, 1, 12, 2000);
    return date('H:i', $time + $plusmin * 60);
}


function termine_frei($datum, $termin_dauer, $benutzer_id)
{
    $termin_dauer_m = $termin_dauer * 60;

    $arbeitsstunden_sec = 8 * 60 * 60; // in sec ohne pause

    $datum_heute = date_german2mysql($datum);
    $datum_heute_ger = $datum;
    $d_heute_arr = explode('.', $datum_heute_ger);
    $h_tag = $d_heute_arr[0];
    $h_monat = $d_heute_arr[1];
    $h_jahr = $d_heute_arr[2];

    $pause = 30; // min
    $pause_sec = 30 * 60;
    $pausen_anfang_std = 11;
    $pausen_anfang_min = 30;
    $pausen_anfang_m = mktime($pausen_anfang_std, $pausen_anfang_min, 0, $h_monat, $h_tag, $h_jahr);

    $datum_bis_arr = explode('.', $h_tag . $h_monat . $h_jahr);
    $bis_tag = $datum_bis_arr[0];
    $bis_monat = $datum_bis_arr[1];
    $bis_jahr = $datum_bis_arr[2];

    $start_std = '06';
    $start_min = '45';
    $end_std = '15';
    $end_min = '15';

    $startzeit_m = mktime($start_std, $start_min, 0, $h_monat, $h_tag, $h_jahr);

    $ende_heute = $startzeit_m + $arbeitsstunden_sec + $pause_sec;


    $zaehler = 0;
    if (!check_termin_of_day($datum_heute)) {
        while ($startzeit_m <= $ende_heute) {
            /*Wenn zwischen aAnfang Tag und Ende*/
            if ($startzeit_m + $termin_dauer_m < $ende_heute) {
                /*Wenn Termine vor Pause*/
                if ($startzeit_m + $termin_dauer_m <= $pausen_anfang_m) {
                    $t[$zaehler] = date("d.m.Y H:i", $startzeit_m) . ' bis ' . date("H:i", $startzeit_m + $termin_dauer_m) . ' ---1';
                    $t2a[$zaehler]['A'] = $startzeit_m;
                    $t2a[$zaehler]['E'] = $startzeit_m + $termin_dauer_m;
                    $t2a[$zaehler]['STATUS'] = 'FREI';
                    $t2a[$zaehler]['A1'] = date("d.m.Y H:i", $startzeit_m);
                    $t2a[$zaehler]['E1'] = date("d.m.Y H:i", $startzeit_m + $termin_dauer_m);

                }

            }

            $startzeit_m += $termin_dauer_m;

            /*Pausenzeit eintragen*/
            if ($startzeit_m > $pausen_anfang_m && $pause <> 1) {
                $t[$zaehler] = date("d.m.Y H:i", $pausen_anfang_m) . ' PAUSE bis ' . date("H:i", $pausen_anfang_m + $pause_sec);
                $t2a[$zaehler]['A'] = $pausen_anfang_m;
                $t2a[$zaehler]['E'] = $pausen_anfang_m + $pause_sec;
                $t2a[$zaehler]['STATUS'] = 'PAUSE';
                $t2a[$zaehler]['A1'] = date("d.m.Y H:i", $pausen_anfang_m);
                $t2a[$zaehler]['E1'] = date("d.m.Y H:i", $pausen_anfang_m + $pause_sec);
                $pause = 1;
                $startzeit_m = $pausen_anfang_m + $pause_sec;
                $zaehler++;
            }


            if ($startzeit_m + $termin_dauer_m < $ende_heute) {
                /*Wenn Termine nach Pause*/
                if ($startzeit_m >= $pausen_anfang_m + $pause_sec) {
                    $t[$zaehler] = date("d.m.Y H:i", $startzeit_m) . ' bis ' . date("H:i", $startzeit_m + $termin_dauer_m) . ' ---2';
                    $t2a[$zaehler]['A'] = $startzeit_m;
                    $t2a[$zaehler]['A1'] = date("d.m.Y H:i", $startzeit_m);
                    $t2a[$zaehler]['E'] = $startzeit_m + $termin_dauer_m;
                    $t2a[$zaehler]['E1'] = date("d.m.Y H:i", $startzeit_m + $termin_dauer_m);
                    $t2a[$zaehler]['STATUS'] = 'FREI';

                }
            }
            $zaehler++;
        }
        return $t2a;
    } else { //end if termine heute not exists
        $t2v = get_termine_of_day_arr($benutzer_id, $datum_heute);

        $anz_m_termine = count($t2v);
        for ($a = 0; $a < $anz_m_termine; $a++) {
            $t_anfang_temp_arr = explode(':', $t2v[$a]['VON']);
            $t_anfang_std = $t_anfang_temp_arr[0];
            $t_anfang_min = $t_anfang_temp_arr[1];
            $t_anfang = mktime($t_anfang_std, $t_anfang_min, 0, $h_monat, $h_tag, $h_jahr);
            $t_ende_temp_arr = explode(':', $t2v[$a]['BIS']);
            $t_ende_std = $t_ende_temp_arr[0];
            $t_ende_min = $t_ende_temp_arr[1];
            $t_ende = mktime($t_ende_std, $t_ende_min, 0, $h_monat, $h_tag, $h_jahr);

            $t2a[$a]['A'] = $t_anfang;
            $t2a[$a]['A1'] = date("d.m.Y H:i", $t_anfang);
            $t2a[$a]['E'] = $t_ende;
            $t2a[$a]['E1'] = date("d.m.Y H:i", $t_ende);

            $t2a[$a]['STATUS'] = 'TERMIN';
            $t2a[$a]['STR'] = $t2v[$a]['STR'];
            $t2a[$a]['PARTNER_ID'] = $t2v[$a]['PARTNER_ID'];
        }


        $anz_v_termine = count($t2a);
        unset($pause);
        $startzeit_m = mktime($start_std, $start_min, 0, $h_monat, $h_tag, $h_jahr);
        $zaehler = 0;
        for ($a = 0; $a < $anz_v_termine; $a++) {
            $anfang = $t2a[$a]['A'];
            $ende = $t2a[$a]['E'];


            /*Fall 1, nur 1 Termin vorhanden*/
            if ($anz_v_termine == 1) {
                /*Erste Zeile  = Diff bis Arbeitsbeginn am Stück*/
                if ($startzeit_m < $anfang) {
                    $zaehler++;
                    $t2ab[$zaehler]['A'] = $startzeit_m;
                    $t2ab[$zaehler]['A1'] = date("d.m.Y H:i", $startzeit_m);
                    $t2ab[$zaehler]['E'] = $anfang;
                    $t2ab[$zaehler]['E1'] = date("d.m.Y H:i", $anfang);
                    $t2ab[$zaehler]['STATUS'] = 'FREI';
                }

                if ($ende_heute > $ende) {
                    $zaehler++;
                    $t2ab[$zaehler]['A'] = $ende;
                    $t2ab[$zaehler]['A1'] = date("d.m.Y H:i", $ende);
                    $t2ab[$zaehler]['E'] = $ende_heute;
                    $t2ab[$zaehler]['E1'] = date("d.m.Y H:i", $ende_heute);
                    $t2ab[$zaehler]['STATUS'] = 'FREI';
                }

                $zaehler++;
                $t2ab[$zaehler]['A'] = $anfang;
                $t2ab[$zaehler]['A1'] = date("d.m.Y H:i", $anfang);
                $t2ab[$zaehler]['E'] = $ende;
                $t2ab[$zaehler]['E1'] = date("d.m.Y H:i", $ende);
                $t2ab[$zaehler]['STATUS'] = 'TERMIN';
                $t2ab[$zaehler]['STR'] = $t2v[$a]['STR'];
                $t2ab[$zaehler]['PARTNER_ID'] = $t2v[$a]['PARTNER_ID'];

            }//Ende Fall 1

            /*Fall 2, mehrere Termine vorhanden*/

            if ($anz_v_termine > '1') {
                /*Erste Zeile  = Diff bis Arbeitsbeginn am Stück*/
                if ($a == '0') {
                    if ($startzeit_m < $anfang) {
                        $zaehler++;
                        $t2ab[$zaehler]['A'] = $startzeit_m;
                        $t2ab[$zaehler]['A1'] = date("d.m.Y H:i", $startzeit_m);
                        $t2ab[$zaehler]['E'] = $anfang;
                        $t2ab[$zaehler]['E1'] = date("d.m.Y H:i", $anfang);
                        $t2ab[$zaehler]['STATUS'] = 'FREI';
                    }
                }

                if ($a == ($anz_v_termine - 1)) {
                    if ($ende_heute > $ende) {
                        $zaehler++;
                        $t2ab[$zaehler]['A'] = $ende;
                        $t2ab[$zaehler]['A1'] = date("d.m.Y H:i", $ende);
                        $t2ab[$zaehler]['E'] = $ende_heute;
                        $t2ab[$zaehler]['E1'] = date("d.m.Y H:i", $ende_heute);
                        $t2ab[$zaehler]['STATUS'] = 'FREI';
                    }
                }

                $zaehler++;
                $t2ab[$zaehler]['A'] = $anfang;
                $t2ab[$zaehler]['A1'] = date("d.m.Y H:i", $anfang);
                $t2ab[$zaehler]['E'] = $ende;
                $t2ab[$zaehler]['E1'] = date("d.m.Y H:i", $ende);
                $t2ab[$zaehler]['STATUS'] = 'TERMIN';
                $t2ab[$zaehler]['STR'] = $t2v[$a]['STR'];

                $t2ab[$zaehler]['PARTNER_ID'] = $t2v[$a]['PARTNER_ID'];


                /*Alle dazwischen*/
                if ($a < $anz_v_termine - 1) {
                    $n = $a + 1;
                    $anfang_n = $t2a[$n]['A'];
                    $zaehler++;
                    if ($ende != $anfang_n) {
                        $t2ab[$zaehler]['A'] = $ende;
                        $t2ab[$zaehler]['A1'] = date("d.m.Y H:i", $ende);
                        $t2ab[$zaehler]['E'] = $anfang_n;
                        $t2ab[$zaehler]['E1'] = date("d.m.Y H:i", $anfang_n);
                        $t2ab[$zaehler]['STATUS'] = 'FREI';
                    }
                }
            }//Ende Fall 2


        }


        $arrSXsorted = array_sortByIndex($t2ab, 'A');
        return $arrSXsorted;
    }
}

function check_termin_of_day($datum)
{
    $db_abfrage = "SELECT * FROM GEO_TERMINE, GEO_LON_LAT WHERE DATUM='$datum' && GEO_LONLAT_ID = GEO_LON_LAT.DAT ORDER BY VON ASC, BIS ASC";
    $result = DB::select($db_abfrage);
    return !empty($result);
}

function get_termine_of_day_arr($benutzer_id, $datum)
{
    $db_abfrage = "SELECT * FROM GEO_TERMINE, GEO_LON_LAT WHERE DATUM='$datum' && GEO_LONLAT_ID = GEO_LON_LAT.DAT && BENUTZER_ID='$benutzer_id' ORDER BY VON ASC, BIS ASC";
    $result = DB::select($db_abfrage);
    if (!empty($result)) {
        return $result;
    }
}

function array_sortByIndex($array, $index, $order = SORT_ASC, $natsort = FALSE, $case_sensitive = FALSE)
{
    if (is_array($array) AND (count($array) > 0)) {
        foreach (array_keys($array) AS $key) {
            $temp[$key] = $array[$key][$index];
        }
        if (!$natsort) {
            if ($order == SORT_ASC) {
                asort($temp);
            } else {
                arsort($temp);
            }
        } else {
            if ($case_sensitive) {
                natsort($temp);
            } else {
                natcasesort($temp);
            }
            if ($order != SORT_ASC) {
                $temp = array_reverse($temp, TRUE);
            }
        }
        foreach (array_keys($temp) AS $key) {
            if (is_numeric($key)) {
                $sorted[] = $array[$key];
            } else {
                $sorted[$key] = $array[$key];
            }
        }
        return $sorted;
    }
    return $array;
}

function date_mysql2german($date)
{
    $d = explode("-", $date);
    return sprintf("%02d.%02d.%04d", $d[2], $d[1], $d[0]);
}

function date_german2mysql($date)
{
    $d = explode(".", $date);
    return sprintf("%04d-%02d-%02d", $d[2], $d[1], $d[0]);
}

function form_termin_erstellen($datum, $benutzer_id)
{
    echo "<form method=\"post\">";
    echo "<input type=\"hidden\" name=\"benutzer_id\" id=\"benutzer_id\" value=\"$benutzer_id\">";
    echo "<input type=\"hidden\" name=\"geo_id\" id=\"geo_id\" value=\"" . session()->get('geo_id') . "\">";
    echo "<input type=\"hidden\" name=\"datum_termin\" id='datum_termin' value=\"$datum\">";
    dropdown_zeiten('Beginn', 'beginn', 'beginn', request()->input('von'), '');
    dropdown_zeiten('Ende', 'ende', 'ende', request()->input('bis'), '');
    $gewerk_id = get_gewerk_id($benutzer_id);

    echo "<br /><br />";
    dropdown_leistungen($gewerk_id);
    echo "<br/>";

    echo "<label for=\"hinweis\">Hinweis</label>";
    echo "<textarea name=\"hinweis\" id=\"hinweis\"></textarea>";
    echo "<label for=\"text\">Textnachricht</label>";
    echo "<textarea name=\"textn\" id=\"textn\"></textarea>";

    echo "<input type=\"hidden\" name=\"option\" value=\"termin_speichern_db\">";
    ?>
    <input type="button" id="submit" value="Termin eintragen" onclick="termin_speichern()"/>
    <?php
    echo "</form>";
}

function zahl_zweistellig($zahl)
{
    return sprintf("%02d", $zahl);
}

function dropdown_zeiten($label, $name, $id, $von, $js)
{

    $std = 0;
    $min = '00';
    echo "<label for=\"$id\">$label</label><select name=\"$name\" id=\"$id\" size=\"1\" $js>";
    for ($a = 0; $a < 97; $a++) {
        $std = zahl_zweistellig($std);
        $zeit = "$std:$min";
        if ($von == $zeit) {
            $treffer = 1;
            echo "<option value=\"$zeit\" selected><b>$zeit</b></option>";
        } else {
            echo "<option value=\"$zeit\">$zeit</option>";
        }

        $min += 15;
        if ($min == 60) {
            $std += 1;
            $min = '00';
            #echo "$std : $min<br>";
        }
    }
    if ($treffer != 1) {
        echo "<option value=\"$von\" selected>$von</option>";
    }
    echo "</select>";
}

function dropdown_leistungen($gewerk_id)
{
    $result = DB::select("SELECT LK_ID, BEZEICHNUNG FROM `LEISTUNGSKATALOG` WHERE (`GEWERK` ='$gewerk_id' OR `GEWERK` IS NULL) AND `AKTUELL` ='1' ORDER BY BEZEICHNUNG ASC");
    echo "<label for=\"leistung_id\">Leistung</label><select name=\"leistung_id\" id=\"leistung_id\" size=1>\n";
    echo "<option value=\"\">Bitte wählen</option>\n";
    if (!empty($result)) {
        foreach($result as $row) {
            $leistung_id = $row['LK_ID'];
            $beschreibung = $row['BEZEICHNUNG'];
            echo "<option value=\"$leistung_id\">$beschreibung</option>\n";
        }
    }
    echo "</select>\n";
}

function termin_speichern_db($benutzer_id, $datum, $von, $bis, $hinweis, $geo_id, $partner_id, $text)
{
    $datum_sql = date_german2mysql($datum);
    DB::insert("INSERT INTO GEO_TERMINE VALUES (NULL, '$benutzer_id', '$datum_sql', '$von', '$bis','$text','$geo_id','$partner_id', '$hinweis')");
}