<?php

include_once('function.php');

if (request()->has('option')) {
    switch (request()->input('option')) {

        default:
            echo "DEFAULT AUSGABE";
            break;

        case "tages_termine":
            $datum = request()->input('datum');
            if (!request()->has('benutzer_id')) {
                $benutzer_id = session()->get('benutzer_id');
            } else {
                $benutzer_id = request()->input('benutzer_id');
            }
            $benutzername = get_benutzername($benutzer_id);
            if (empty($datum)) {
                $datum = date("d.m.Y");
            }

            $gestern = tage_minus_wp($datum, 1);
            $morgen = tage_plus_wp($datum, 1);
            $wochentag = wochentag($datum);
            $kw = kw($datum);
            session()->put('kw', $kw);
            $vor_kw = $kw - 1;
            $nach_kw = $kw + 1;
            $montag_vor_kw = get_montag_vor_kw($datum);
            $montag_nach_kw = get_montag_nach_kw($datum);
            $link_vor_kw = "<a href=\"javascript:daj2('" . route('web::wartungsplaner::legacyAjax', ['option' => 'tages_termine', 'datum' => $montag_vor_kw, 'benutzer_id' => $benutzer_id], false) . "',document.getElementById('leftBox1'))\"><img src=\"images/wartungsplaner/vor.png\" border=\"0\"></a>";
            $link_nach_kw = "<a href=\"javascript:daj2('" . route('web::wartungsplaner::legacyAjax', ['option' => 'tages_termine', 'datum' => $montag_nach_kw, 'benutzer_id' => $benutzer_id], false) . "',document.getElementById('leftBox1'))\"><img src=\"images/wartungsplaner/nach.png\" border=\"0\"></a>";
            $link_tag_vor = "<a href=\"javascript:daj2('" . route('web::wartungsplaner::legacyAjax', ['option' => 'tages_termine', 'datum' => $gestern, 'benutzer_id' => $benutzer_id], false) . "',document.getElementById('leftBox1'))\"><img src=\"images/wartungsplaner/vor.png\" border=\"0\"></a>";
            $link_tag_nach = "<a href=\"javascript:daj2('" . route('web::wartungsplaner::legacyAjax', ['option' => 'tages_termine', 'datum' => $morgen, 'benutzer_id' => $benutzer_id], false) . "',document.getElementById('leftBox1'))\"><img src=\"images/wartungsplaner/nach.png\" border=\"0\"></a>";
            echo "<table>";
            echo "<tr class=\"tag_datum\" valign=\"center\"><td colspan=\"4\"><center>KW:$vor_kw $link_vor_kw | $link_tag_vor $wochentag, $datum (KW:$kw) - $benutzername $link_tag_nach | $link_nach_kw KW:$nach_kw</center></td></tr>";
            echo "</table>";
            echo "<table class=\"woche\"><tr valign=\"top\">";

            for ($cc = 1; $cc < 6; $cc++) {
                echo "<td>";
                $tages_termine_arr = termine_frei($datum, 90, $benutzer_id);
                $anz = count($tages_termine_arr);
                if ($anz) {
                    $z = 0;
                    echo "<table class=\"wochentag\">";
                    $wochentag = wochentag($datum);
                    if ($wochentag == 'Sonntag' or $wochentag == 'Samstag') {
                        $class = 'sonntag';
                    } else {
                        $class = 'termin';
                    }
                    echo "<tr><th colspan=\"3\">$wochentag $datum</th></tr>";
                    for ($a = 0; $a < $anz; $a++) {
                        $z++;
                        $an = substr($tages_termine_arr[$a]['A1'], -5);
                        $en = substr($tages_termine_arr[$a]['E1'], -5);
                        $status = $tages_termine_arr[$a]['STATUS'];
                        $str = $tages_termine_arr[$a]['STR'];
                        $partner_name = $tages_termine_arr[$a]['PARTNER_NAME'];
                        if ($status == 'FREI' or $status == 'PAUSE') {
                            $class = 'frei';
                            $link_neu = "<a href=\"javascript:daj2('" . route('web::wartungsplaner::legacyAjax', ['option' => 'termin_eintragen', 'datum' => $datum, 'benutzer_id' => $benutzer_id, 'von' => $an, 'bis' => $en], false) . "',document.getElementById('leftBox1'))\">NEU</a>";
                            echo "<tr class=\"$class$z\"><td>$an<br>$en</td><td>$link_neu</td><td>$status</td></tr>";
                        } else {
                            echo "<tr class=\"$class$z\"><td>$an<br>$en</td><td>BEARBEITEN</td><td>$str $partner_name</td></tr>";
                        }
                        if ($z == 2) {
                            $z = 0;
                        }
                    }
                    echo "</table>";
                }
                $datum = tage_plus_wp($datum, 1);
                echo "</td>";
            }

            echo "</tr></table>";
            break;


        case "kartendaten_woche":
            echo "Kartendaten Woche";
            echo session()->get('kw');
            break;

        case "kartendaten_tag":
            echo "Kartendaten am Wochentag";
            break;

        case "suche_termine":
            echo "<table>";
            echo "<tr class=\"tag_datum\"><td colspan=\"4\">Terminvorschläge</td></tr>";
            echo "<tr class=\"termin1\" onclick=\"daj2('" . route('web::wartungsplaner::legacyAjax', ['option' => 'tages_termine', 'datum' => '24.12.2010'], false) . "',document.getElementById('leftBox1'))\"><td>2,5 km</td><td>24.12.2010</td><td>9:00</td><td>HIER CODE Erdmann</td></tr>";
            echo "<tr class=\"termin2\" onclick=\"daj2('" . route('web::wartungsplaner::legacyAjax', ['option' => 'tages_termine', 'datum' => '25.12.2010'], false) . "',document.getElementById('leftBox1'))\"><td>2,6 km</td><td>25.12.2010</td><td>9:00</td><td>HIER CODE Erdmann</td></tr>";
            echo "<tr class=\"termin1\" onclick=\"daj2('" . route('web::wartungsplaner::legacyAjax', ['option' => 'test_vars'], false) . "',document.getElementById('rightBox1'))\"><td>2,6 km</td><td>25.12.2010</td><td>9:00</td><td>HIER CODE Erdmann</td></tr>";
            echo "<tr class=\"termin2\"><td>6,5 km</td><td>18.11.2010</td><td>9:00</td><td>Erdmann</td></tr>";
            echo "<tr class=\"termin1\"><td>7,2 km</td><td>13.11.2010</td><td>9:00</td><td>Sivac</td></tr>";
            echo "<tr class=\"termin2\"><td>8,5 km</td><td>13.11.2010</td><td>9:00</td><td>Erdmann</td></tr>";
            echo "<tr class=\"termin1\"><td>9,2 km</td><td>13.11.2010</td><td>9:00</td><td>Sivac</td></tr>";
            echo "<tr class=\"termin2\"><td>11,5 km</td><td>14.11.2010</td><td>9:00</td><td>Erdmann</td></tr>";
            echo "<tr class=\"termin1\"><td>15,2 km</td><td>15.11.2010</td><td>9:00</td><td>Sivac</td></tr>";
            echo "<tr class=\"termin2\"><td>16,5 km</td><td>18.11.2010</td><td>9:00</td><td>Erdmann</td></tr>";
            echo "<tr class=\"termin1\"><td>7,2 km</td><td>13.11.2010</td><td>9:00</td><td>Sivac</td></tr>";
            echo "<tr class=\"termin2\"><td>8,5 km</td><td>13.11.2010</td><td>9:00</td><td>Erdmann</td></tr>";
            echo "<tr class=\"termin1\"><td>9,2 km</td><td>13.11.2010</td><td>9:00</td><td>Sivac</td></tr>";
            echo "<tr class=\"termin2\"><td>11,5 km</td><td>14.11.2010</td><td>9:00</td><td>Erdmann</td></tr>";
            echo "<tr class=\"termin1\"><td>15,2 km</td><td>15.11.2010</td><td>9:00</td><td>Sivac</td></tr>";
            echo "<tr class=\"termin2\"><td>16,5 km</td><td>18.11.2010</td><td>9:00</td><td>Erdmann</td></tr>";
            echo "<tr class=\"termin1\"><td>7,2 km</td><td>13.11.2010</td><td>9:00</td><td>Sivac</td></tr>";
            echo "<tr class=\"termin2\"><td>8,5 km</td><td>13.11.2010</td><td>9:00</td><td>Erdmann</td></tr>";
            echo "<tr class=\"termin1\"><td>9,2 km</td><td>13.11.2010</td><td>9:00</td><td>Sivac</td></tr>";
            echo "<tr class=\"termin2\"><td>11,5 km</td><td>14.11.2010</td><td>9:00</td><td>Erdmann</td></tr>";
            echo "<tr class=\"termin1\"><td>15,2 km</td><td>15.11.2010</td><td>9:00</td><td>Sivac</td></tr>";
            echo "<tr class=\"termin2\"><td>16,5 km</td><td>18.11.2010</td><td>9:00</td><td>Erdmann</td></tr>";
            echo "</table></body></html>";
            break;


        case "test_vars":
            echo "SITZUNG";
            print_r(session()->all());
            break;

        case "reg_vars":
            print_r(array_keys(request()->all()));
            foreach (request()->all() as $key => $val) {
                $this->$key = $val;
                session()->put($key, $val);
            }
            break;

        case "reg_ziel_lonlat":
            $str = request()->input('str');
            $nr = request()->input('nr');
            $plz = request()->input('plz');
            $ort = request()->input('ort');
            session()->put('partner_id', request()->input('partner_id'));
            if (check_str($str, $nr, $plz, $ort)) {
                $lon_lat_arr = explode(',', get_lat_lon_db($str, $nr, $plz, $ort));
            } else {
                $lon_lat_arr = explode(',', get_lat_lon_osm($str, $nr, $plz, $ort));
            }
            $lon = ltrim(rtrim($lon_lat_arr[1]));
            $lat = ltrim(rtrim($lon_lat_arr[0]));
            session()->put('z_lat', $lat);
            session()->put('z_lon', $lon);
            print_r(session()->all());
            break;


        case "suche_strasse":
            ob_clean(); //ausgabepuffer leeren
            $gesuchte_str = request()->input('str');
            $gesuchte_namen = request()->input('name');
            $partner_arr = partner_in_array();
            $anz = count($partner_arr);
            if ($anz > 0) {
                echo "<table>";
                $zaehler = 0;
                for ($index = 0; $index < $anz; $index++) {
                    $partner_id = $partner_arr[$index]['PARTNER_ID'];
                    $str = $partner_arr[$index]['STRASSE'];
                    $nr = $partner_arr[$index]['NUMMER'];
                    $p_name = ltrim(rtrim($partner_arr[$index]['PARTNER_NAME']));
                    $plz = $partner_arr[$index]['PLZ'];
                    $ort = $partner_arr[$index]['ORT'];
                    if ((!empty($gesuchte_str) && preg_match("/$gesuchte_str/i", "$str")) or (!empty($gesuchte_namen) && preg_match("/$gesuchte_namen/i", "$p_name"))) {
                        $zaehler++;
                        $namen_arr = explode(' ', $p_name);
                        $p_vorname = $namen_arr[0];
                        $p_nachname = $namen_arr[1];
                        echo "<tr class=\"termin$zaehler\" onclick=\"form_fuellen('$p_nachname', '$p_vorname', '$str', '$nr', '$plz', '$ort', '$partner_id')\" ondblclick=\"get_ergebnis('" . route('web::wartungsplaner::legacyAjax', ['option' => 'get_lon_lat_osm', 'str' => $str, 'nr' => $nr, 'plz' => $plz, 'ort' => $ort], false) . "');\"><td>$p_nachname $p_vorname</td><td>$str</td><td>$nr</td><td>$plz</td><td>$ort</td></tr>";
                    }
                    if ($zaehler == 2) {
                        $zaehler = 0;
                    }
                }
                echo "</table>";
            }
            break;


        case "get_lon_lat_osm":
            $str = request()->input('str');
            $nr = request()->input('nr');
            $plz = request()->input('plz');
            $ort = request()->input('ort');
            $w_datum = request()->input('w_datum');
            session()->put('w_datum', $w_datum);
            get_lon_lat_osm($str, $nr, $plz, $ort, $w_datum);
            break;


        case "termine_anzeigen_sql":

            $ausgabe = get_tages_termine(session()->get('w_datum'), session()->get('benutzer_id'));
            $doc_r = $_SERVER['DOCUMENT_ROOT'];
            $fp = fopen("$doc_r/cal/textfile2.txt", "w");
            fputs($fp, "$ausgabe");
            fclose($fp);
            ob_clean(); //ausgabepuffer leeren
            header("Content-Type: text/plain; charset=UTF-8");
            readfile("$doc_r/cal/textfile2.txt");
            break;

        case "foto_anzeigen":
            $zahl = request()->input('zahl');
            $h_farbe = request()->input('h_farbe');
            ob_clean(); //ausgabepuffer leeren
            erstelle_icon($zahl, $h_farbe);
            break;


        case "test":
            zeitarray();
            test(0, 23);
            getzeitdiff_min('06:45', '07:45');
            getzeitdiff_min('06:45', '15:15');
            break;

        case "get_entfernung":
            $lon = request()->input('lon');
            $lat = request()->input('lat');
            get_entfernung($lon, $lat);
            break;


        case "t1":
            $datum = tage_plus_wp('23.12.2010', 1);
            $datum_in_3_w = tage_plus_wp('23.12.2010', 30);
            while ($datum != $datum_in_3_w) {
                $arr[] = termine_frei($datum, 90, 21);
                $datum = tage_plus_wp($datum, 1);
            }
            $anz_tage = count($arr);
            for ($a = 0; $a < $anz_tage; $a++) {
                $anz_t = count($arr[$a]);
                for ($b = 0; $b < $anz_t; $b++) {
                    $t_arr[] = $arr[$a][$b];
                }
            }
            echo '<pre>';
            print_r($t_arr);
            break;


        case "termin_eintragen":
            $benutzer_id = request()->input('benutzer_id');
            $datum = request()->input('datum');
            $gestern = tage_minus_wp($datum, 1);
            $morgen = tage_plus_wp($datum, 1);
            $wochentag = wochentag($datum);
            $kw = kw($datum);
            session()->put('kw', $kw);
            $link_tag_vor = "<a href=\"javascript:daj2('" . route('web::wartungsplaner::legacyAjax', ['option' => 'tages_termine', 'datum' => $gestern, 'benutzer_id' => $benutzer_id], false) . "',document.getElementById('leftBox1'))\"><img src=\"images/wartungsplaner/vor.png\" border=\"0\"></a>";
            $link_tag_nach = "<a href=\"javascript:daj2('" . route('web::wartungsplaner::legacyAjax', ['option' => 'tages_termine', 'datum' => $morgen, 'benutzer_id' => $benutzer_id], false) . "',document.getElementById('leftBox1'))\"><img src=\"images/wartungsplaner/nach.png\" border=\"0\"></a>";
            echo "<table>";
            echo "<tr class=\"tag_datum\"><td colspan=\"4\"><center>$link_tag_vor $wochentag, $datum (KW:$kw) - Sivac $link_tag_nach</center></td></tr>";
            $geo_lonlat_id = get_lonlat_id(session()->put('z_lon'), session()->put('z_lat'));
            session()->put('geo_id', $geo_lonlat_id);
            echo "</table>";
            form_termin_erstellen($datum, $benutzer_id);
            break;


        case "termin_speichern_db":
            $benutzer_id = request()->input('benutzer_id');
            $datum = request()->input('datum');
            $von = request()->input('beginn');
            $bis = request()->input('ende');
            $hinweis = request()->input('hinweis');
            $geo_id = request()->input('geo_id');
            $partner_id = request()->input('partner_id');
            $text = request()->input('text');
            termin_speichern_db($benutzer_id, $datum, $von, $bis, $hinweis, $geo_id, $partner_id, $text);
            break;
    }//end switch
}