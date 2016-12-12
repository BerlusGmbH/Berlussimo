<?php

/*Probe*/
if (request()->has('g_id')) {
    session()->put('g_id', request()->input('g_id'));
}

include_once("funktionen.php");

#switch#####################################
if (request()->has('option')) {
    switch (request()->input('option')) {

        default:
            echo "..wartet";
            break;

        /*Nachricht schicken*/
        case "nachricht":
            $nachricht = request()->input('nachricht');
            if (!empty($nachricht)) {
                echo "$nachricht";
            } else {
                echo "Keine STATUS Meldung";
            }
            break;

        case "main":
            form_extern(request()->input('target_id'));
            break;

        /*Formular für Interne Wartungen*/
        case "form_intern":
            form_intern(request()->input('target_id'));
            break;

        /*Formular für Interne Wartungen*/
        case "form_extern":
            form_extern(request()->input('target_id'));
            break;


        /*Suche nach Kontakten, Partner, Mieter, USW*/
        case "suche_kontakt":
            if (request()->has('target_id')) {
                $targ = '';
            } else {
                $targ = request()->input('target_id');
            }
            if (strlen(request()->input('string')) >= 3) {
                kontakt_suche($targ, request()->input('string'));
            } else {
                echo "Mindestens 3 Buchstaben eingeben";
            }
            break;


        case "termin_dauer_aendern":
            if (request()->has('termin_dauer')) {
                session()->put('termin_dauer', request()->input('termin_dauer'));
            } else {
                session()->put('termin_dauer', 60);
            }
            echo "<p class=\"zeile_hinweis_rot\"><br>Termindauer auf " . session()->get('termin_dauer') . " Min geaendert!<br><br></p>";
            break;

        case "umkreissuche":
            session()->forget('kos_typ');
            session()->forget('kos_id');
            $str = request()->input('str');
            $nr = request()->input('nr');
            $plz = request()->input('plz');
            $ort = request()->input('ort');
            if (empty($str) or empty($nr) or empty($plz) or empty($ort)) {
                echo "Strasse nicht korrekt eingegeben!";
            } else {
                if (!session()->has('team_id')) {
                    termin_suchen2($str, $nr, $plz, $ort);
                } else {
                    termin_suchen2($str, $nr, $plz, $ort, session()->get('team_id'));
                }
            }
            break;

        /*Gewählten Kostentraeger übermitteln, in Session speichern*/
        case "kos_typ_register":
            session()->put('kos_typ', request()->input('kos_typ'));
            session()->put('kos_id', request()->input('kos_id'));
            break;

        case "einheit_register":
            session()->forget('einheit_id');
            session()->forget('einheit_bez');
            session()->put('einheit_id', request()->input('einheit_id'));
            session()->put('einheit_bez', request()->input('einheit_bez'));
            break;


        /*Formular mit dem gewählten Partner, ausgegraut*/
        case "partner_inaktiv_form":
            form_inaktiv(session()->get('kos_typ'), session()->get('kos_id'));
            break;

        /*Formular mit dem gewählten Partner, ausgegraut*/
        case "partner_waehlen":
            form_inaktiv(session()->get('kos_typ'), session()->get('kos_id'));
            break;

        /*Wartungsteile wählen*/
        case "wartungsteil_waehlen":
            if (session()->has('kos_typ') && session()->has('kos_id')) {
                form_wartungsteil(session()->get('kos_typ'), session()->get('kos_id'));
            } else {
                echo "Session nicht gestartet";
                form_wartungsteil(session()->get('kos_typ'), session()->get('kos_id'));
            }

            break;

        /*Neues Wartungsteil erfassen*/
        case "wartungsteil erfassen":
            form_wartungsteil_erfassen('ssssss', 'ssss');
            break;


        /*Neuen Partner speichern*/
        case "partner_save":
            $partner_name = request()->input('partner_name');
            $str = request()->input('str');
            $nr = request()->input('nr');
            $plz = request()->input('plz');
            $ort = request()->input('ort');
            $land = request()->input('land');
            $wohnlage = request()->input('wohnlage');
            $tel = request()->input('tel');
            $mobil = request()->input('mobil');
            $email = request()->input('email');
            $values = "'$partner_name','$str', '$nr', '$plz', '$ort', '$land', '1'";
            if (save_to_db('PARTNER_LIEFERANT', $values, 'PARTNER_ID') == true) {
                $partner_id = get_partner_id($partner_name, $str, $nr, $plz);
                session()->put('kos_typ', 'Partner');
                session()->put('kos_id', $partner_id);
                if (!empty($partner_id)) {
                    /*Telefon, Mobil, Email in Details speichern*/
                    if (!empty($tel)) {
                        detail_speichern_2('PARTNER_LIEFERANT', $partner_id, 'Telefon', $tel, date("d.m.Y"));
                    }
                    if (!empty($mobil)) {
                        detail_speichern_2('PARTNER_LIEFERANT', $partner_id, 'Handy', $mobil, date("d.m.Y"));
                    }
                    if (!empty($email)) {
                        detail_speichern_2('PARTNER_LIEFERANT', $partner_id, 'Email', $email, date("d.m.Y"));
                    }
                    if (!empty($wohnlage)) {
                        detail_speichern_2('PARTNER_LIEFERANT', $partner_id, 'Wohnlage', $wohnlage, date("d.m.Y"));
                    }
                    kos_typ_info_anzeigen(session()->get('kos_typ'), session()->get('kos_id'));
                    if (session()->has('reserve_lat') && session()->has('reserve_lon')) {
                        if (session()->get('ziel_lat') == session()->get('reserve_lat') && session()->get('ziel_lon') == session()->get('reserve_lon')) {
                            if (session()->has('reserve_datum') && session()->has('reserve_von') && session()->get('termin_dauer') && session()->get('reserve_b_id')) {
                                $values = "'1','unbekannt', 'unbekannt', '1900', 'Kueche', " . session()->get('kos_typ') . ", " . session()->get('kos_id') . ", $zustell_ans, '12', '1'";
                                if (save_to_db('W_GERAETE', $values, 'GERAETE_ID') == true) {
                                    $g_id = session()->get('GERAETE_ID');
                                    session()->put('g_id', session()->get('GERAETE_ID'));
                                    echo "<p class=\"zeile_hinweis\">Gerät angelegt $g_id</p>";
                                    $logged_user = session()->get('benutzer_id');
                                    $benutzer_id = session()->get('reserve_b_id');
                                    $datum_sql = date_german2mysql(session()->get('reserve_datum'));
                                    $von = session()->get('reserve_von');
                                    $end_zeit = zeit_plus_min($von, session()->get('termin_dauer'));
                                    $bis = session()->get('reserve_bis');
                                    $text = 'x';
                                    $hinweis = 'Gerätedaten prüfen';
                                    $values = "'$benutzer_id','$datum_sql', '$von', '$end_zeit', '$text', '$g_id', '$hinweis', NOW(), '$logged_user', NULL, NULL,'1'";
                                    if (check_termin_frei($benutzer_id, $datum_sql, $von, $end_zeit) == true) {

                                        if (!save_to_db('GEO_TERMINE', $values, 'x') == true) {
                                            echo "Termin konnte nicht gespeichert werden!";
                                        }
                                    } else {
                                        echo "Terminüberschneidung zwischen $von und $end_zeit Uhr!";
                                    }
                                }
                            }
                        }
                        session()->forget('reserve_lat');
                        session()->forget('reserve_lon');
                        session()->forget('reserve_datum');
                        session()->forget('reserve_von');
                        session()->forget('reserve_bis');
                        session()->forget('reserve_b_id');
                    }
                } else {
                    die("ABBRUCH / FEHLER: Konnte den Partner $partner_name nicht finden!");
                }
            }
            break;

        /*Partnerinfos anzeigen*/
        case "get_partner_info":
            if (session()->has('kos_typ') && session()->has('kos_id')) {
                kos_typ_info_anzeigen(session()->get('kos_typ'), session()->get('kos_id'));
                if (session()->get('kos_typ') == 'Partner') {
                    $g = new general();
                    $g->get_partner_info(session()->get('kos_id'));
                    get_lat_lon_db_osm($g->partner_strasse, $g->partner_hausnr, $g->partner_plz, $g->partner_ort);
                }
            } else {
                echo "Bitte um Geduld, Session wird gestartet!";
                kos_typ_info_anzeigen(session()->get('kos_typ'), session()->get('kos_id'));
            }

            break;

        /*Partnerinfos rechnung an anzeigen*/
        case "get_partner_info_r_an":
            echo "<p class=\"zeile_ueber\">&nbsp;Rechnung geht an:</p><br>";
            kos_typ_info_anzeigen(session()->get('kos_typ'), session()->get('kos_id'));
            break;

        /*Formular für die Eingabe einer abweichenden Rechnungsanschrift*/
        case "form_abweichende_r_anschrift":
            form_abweichende_r_anschrift();
            break;

        /*Auswahl der möglichen Gerätebezeichnungen, nach auswahl der Gerätegruppe*/
        case "get_wgeraete_bez":
            if (session()->has('param')) {
                get_wgeraete_bez(session()->get('param'));
            }
            break;

        /*Detail speichern*/
        case "detail_speichern":
            if (session()->has('detail_name') && session()->has('detail_inhalt')) {
                detail_speichern_2('PARTNER_LIEFERANT', session()->get('kos_id'), request()->input('detail_name'), request()->input('detail_inhalt'), date("d.m.Y"));
                if (session()->get('kos_typ') != 'Partner') {
                    kos_typ_info_anzeigen(session()->get('kos_typ'), session()->get('kos_id'));
                } else {
                    kos_typ_info_anzeigen('PARTNER_LIEFERANT', session()->get('kos_id'));
                }
            }
            break;

        case "detail_speichern2":
            if (request()->has('detail_name') && request()->has('detail_inhalt')) {
                detail_speichern_2(request()->input('tab'), request()->input('tab_id'), request()->input('detail_name'), request()->input('detail_inhalt'), date("d.m.Y"));
                echo "<p class=\"zeile_ueber\">DETAIL ZUM GERÄT</p>";
                alle_details_anzeigen(request()->input('tab'), request()->input('tab_id'));
                form_detail_hinzu2(request()->input('tab'), request()->input('tab_id'));
            }
            break;

        case "get_hersteller_gruppe":
            if (request()->has('param')) {
                get_hersteller_gruppe(request()->input('param'));
            }
            break;

        case "get_hersteller_modelle":
            if (request()->has('param')) {
                get_hersteller_modelle(request()->input('param'));
            }

            break;

        case "form_wartungsvertrag":
            echo "<b>MELDUNG aus CASE form_wartungsvertrag, wenn leer dann geraete_id nicht übermittelt</b>";
            if (request()->has('geraete_id')) {
                form_wartungsvertrag(request()->input('geraete_id'));
            }
            break;


        case "wgeraet_save":
            $gbez = request()->input('gbez');
            $gruppe_id = get_gruppe_id($gbez);
            if (!$gruppe_id) {
                echo "Keine gruppe<br>";
                $values = "'$gbez', '0', '1'";
                if (save_to_db('W_GRUPPE', $values, 'GRUPPE_ID') == true) {
                    echo "<h1>Gruppe $gbez erstellt<br>";
                    echo "Kein Gewerk der Gruppe zugewiesen!!!<h1><br>";
                    $gruppe_id = get_gruppe_id($gbez);
                } else {
                    die('Fehler beim Speichern der neuen Gruppe!');
                }
            }
            $her = request()->input('hersteller');
            $mod = request()->input('modell');
            $bj = request()->input('baujahr');
            $lr = request()->input('lage_raum');
            $wartungsintervall = request()->input('wartungsintervall');
            $zustell_ans = request()->input('zustell_ans');
            $values = "'$gruppe_id','$mod', '$her', '$bj', '$lr', " . session()->get('kos_typ') . ", " . session()->get('kos_id') . ", '$zustell_ans', '$wartungsintervall', '1'";
            if (save_to_db('W_GERAETE', $values, 'GERAETE_ID') == true) {
                #echo "Gerät gespeichert!";
                form_wartungsteil(session()->get('kos_typ'), session()->get('kos_id'));
            } else {
                echo "Fehler beim Speichern des Gerätes";
            }

            break;

        /*Bei Auswahl im DD, werden die Infos zum Gerät angezeigt*/
        case "geraete_info_anzeigen":
            if (request()->has('g_id')) {
                if (request()->input('g_id') == 'Bitte wählen' or !is_numeric(request()->input('g_id'))) {
                    echo "<br><p class=\"zeile_hinweis\">Wählen Sie bitte ein Gerät aus dem Dropdownmenü aus!!!</p>";
                } else {
                    geraete_info_anzeigen(request()->input('g_id'));
                }
            } else {
                echo "<br><p class=\"zeile_hinweis\">Wählen Sie bitte ein Gerät aus dem Dropdownmenü aus!!!</p>";
            }
            break;


        /*Bei Wahl eines Gerätes erfolgt TERMIN SUCHE*/
        case "termin_suchen":
            if (request()->has('g_id')) {
                termin_suchen(request()->input('g_id'));
            }
            break;

        case "termin_suchen_neu":
            if (request()->has('g_id')) {
                termin_suchen3(request()->input('g_id'));
            }
            break;

        case "termin_suchen4":
            if (request()->has('g_id')) {
                termin_suchen4(request()->input('g_id'));
            }
            break;


        case "termine_tag_tab":
            if (request()->has('g_id')) {
                session()->put('g_id', request()->input('g_id'));
            }
            if (request()->has('b_id') && request()->has('datum')) {
                termine_tag_tab(request()->input('b_id'), request()->input('datum'));
                session()->put('mitarbeiter_id', request()->input('b_id'));
                session()->put('datum_d', request()->input('datum'));
            } else {
                echo "Mitarbeiter und Tag wählen";
            }
            break;

        case "termine_tag_tab2":
            if (request()->has('g_id')) {
                session()->put('g_id', request()->input('g_id'));
            }
            if (request()->has('b_id') && request()->has('datum')) {
                termine_tag_tab2(request()->input('b_id'), request()->input('datum'));
                session()->put('mitarbeiter_id', request()->input('b_id'));
                session()->put('datum_d', request()->input('datum'));
            } else {
                echo "Mitarbeiter und Tag wählen";
            }
            break;

        case "route_anzeigen":
            $s_lon = request()->input('s_lon');
            $s_lat = request()->input('s_lat');
            $e_lon = request()->input('e_lon');
            $e_lat = request()->input('e_lat');
            $s_adresse = request()->input('s_adresse');
            $e_adresse = request()->input('e_adresse');
            echo "<h3>Route von $s_adresse nach $e_adresse</h3>";
            $g = new general();
            $g->get_strecken_route($s_lon, $s_lat, $e_lon, $e_lat);
            echo "<hr><b>Entfernung $g->km km</b><br>";
            echo "<b>Fahrzeit $g->zeit</b>";

            break;

        case "form_termin_eintragen":
            if (request()->has('b_id') && request()->has('datum')) {
                form_termin_eintragen(request()->input('b_id'), request()->input('datum'), urldecode(request()->input('von')), urldecode(request()->input('bis')));
            } else {
                echo "Mitarbeiter und Datum wählen!!!";
            }
            break;


        case "termin_speichern":
            $benutzer_id = request()->input('b_id');
            $datum_sql = date_german2mysql(request()->input('datum'));
            $von = request()->input('von');
            $bis = request()->input('bis');
            $text = nl2br(request()->input('text'));
            $hinweis = request()->input('hinweis');
            $g_id = session()->get('g_id');


            $logged_user = session()->get('benutzer_id');
            $values = "'$benutzer_id','$datum_sql', '$von', '$bis', '$text', '$g_id', '$hinweis', NOW(), '$logged_user', NULL, NULL,'1'";

            if (check_termin_frei($benutzer_id, $datum_sql, $von, $bis) == true) {
                if (!save_to_db('GEO_TERMINE', $values, 'x') == true) {
                    echo "<p style=\"color:red;font-size:20px;\"Termin konnte nicht gespeichert werden!!!!!<br>Termin bzw. Zeitraum nicht frei</br></p>";
                    die();
                }
            } else {
                echo "<p style=\"color:red;font-size:20px;\"Termin konnte nicht gespeichert werden!!!!!<br>Termin bzw. Zeitraum nicht frei</br></p>";
                die();
            }
            $datum_d = date_mysql2german($datum_sql);
            session()->put('datum_d', $datum_d);
            termine_tag_tab($benutzer_id, $datum_d);
            break;


        case "wochenkalender":
            ?>
            <head>
                <title>Wartungskalender Berlussimo</title>
                <link rel="stylesheet" type="text/css" href="<?php echo elixir('css/wp_form.css') ?>"/>
                <script type="text/javascript" src="<?php echo elixir('js/wartungsplaner.js') ?>"></script>
            </head>
            <?php
            if (request()->has('mitarbeiter_id')) {
                session()->put('mitarbeiter_id', request()->input('mitarbeiter_id'));
            }
            if (!session()->has('mitarbeiter_id')) {
                $g = new general();
                $link = route('web::wartungsplaner::ajax', ['option' => 'wochenkalender']);
                $g->get_team_liste($link);
                echo "<br>";
                if (request()->has('team_id')) {
                    $team_id = request()->input('team_id');
                    $g->get_wteam_info($team_id);
                    if (is_array($g->team_benutzer_ids)) {
                        $anz = count($g->team_benutzer_ids);
                        for ($a = 0; $a < $anz; $a++) {
                            $benutzer_id = $g->team_benutzer_ids[$a]['BENUTZER_ID'];
                            $benutzername = get_benutzername($benutzer_id);
                            echo "<a href=\"$link&mitarbeiter_id=$benutzer_id\">$benutzername</a><br>";
                        }
                    } else {
                        echo "Keine Mitarbeiter im Team $g->team_bez";
                    }
                }

            } else {
                if (request()->has('kw')) {
                    session()->put('kw', request()->input('kw'));
                } else {
                    $datum = date("d.m.Y");
                    session()->put('kw', get_kw($datum));
                }
                $kw = session()->get('kw');
                wochenkalender(session()->get('mitarbeiter_id'), $kw);
            }
            break;


        case "reg_team":
            if (request()->has('team_id')) {
                session()->put('team_id', request()->input('team_id'));
            }
            echo "Ein anderes Team wurde gewählt!";
            break;


        case "tageskalender":
            ?>
            <head>
                <title>Wartungskalender Berlussimo</title>
                <link rel="stylesheet" type="text/css" href="<?php echo elixir('css/wp_form.css') ?>"/>
                <script type="text/javascript" src="<?php echo elixir('js/wartungsplaner.js') ?>"></script>
            </head>
            <?php
            if (session()->has('mitarbeiter_id')) {
                echo "Liste der Mitarbeiter";
            } else {
                if (request()->has('datum')) {
                    session()->put('datum', request()->input('datum'));
                }
                $datum = session()->get('datum');
                tageskalender(session()->get('mitarbeiter_id'), $datum);
            }
            break;

        case "termin_vorschlaege":
            ?>
            <head>
                <title>Wartungskalender Berlussimo</title>
                <link rel="stylesheet" type="text/css" href="<?php echo elixir('css/wp_form.css') ?>"/>
                <script type="text/javascript" src="<?php echo elixir('js/wartungsplaner.js') ?>"></script>
            </head>
            <body>
            <?php
            echo "<div id=\"main\"><div id=\"TERMIN_BOX\">";
            vorschlag();
            echo "</div></div></body>";
            break;

        case "termin_vorschlaege_kurz":
            echo "<div id=\"main\"><div id=\"TERMIN_BOX\">";
            if (request()->has('vorschlag_gruppe_id')) {
                session()->put('vorschlag_gruppe_id', request()->input('vorschlag_gruppe_id'));
            } else {
                session()->put('vorschlag_gruppe_id', 1);
            }

            $arr = get_wartungsgruppen_arr();
            if (!empty($arr)) {
                $gruppen_bez = get_gruppen_bez(session()->get('vorschlag_gruppe_id'));
                echo "<p class=\"zeile_ueber\"><b>Terminvorschläge für die Wartungsgruppe $gruppen_bez</b></p>";

                $js = "onchange='daj3(\"/wartungsplaner/ajax?option=termin_vorschlaege_kurz&vorschlag_gruppe_id=\" + this.value, \"leftBox1\");'";
                dropdown_w_gruppen($arr, 'Wartungsgruppe wählen', 'vorschlag_gruppe_id', 'vorschlag_gruppe_id', $js);
                echo "<br>";


            } else {
                echo "Wartungsgruppen anlegen";
            }

            vorschlag_kurz(session()->get('vorschlag_gruppe_id'));

            echo "</div></div></body>";
            break;

        case "termin_vorschlaege_kurz_chrono":

            echo "<div id=\"main\"><div id=\"TERMIN_BOX\">";
            if (request()->has('vorschlag_gruppe_id')) {
                session()->put('vorschlag_gruppe_id', request()->input('vorschlag_gruppe_id'));
            } else {
                session()->put('vorschlag_gruppe_id', 1);
            }

            $arr = get_wartungsgruppen_arr();
            if (!empty($arr)) {
                $gruppen_bez = get_gruppen_bez(session()->get('vorschlag_gruppe_id'));
                echo "<p class=\"zeile_ueber\"><b>Terminvorschläge für die Wartungsgruppe $gruppen_bez</b></p>";

                $js = "onchange='daj3(\"/wartungsplaner/ajax?option=termin_vorschlaege_kurz_chrono&vorschlag_gruppe_id=\" + this.value, \"leftBox1\");'";
                dropdown_w_gruppen($arr, 'Wartungsgruppe wählen', 'vorschlag_gruppe_id', 'vorschlag_gruppe_id', $js);
                echo "<br>";
            } else {
                echo "Wartungsgruppen anlegen";
            }

            vorschlag_kurz_chrono(session()->get('vorschlag_gruppe_id'));

            echo "</div></div></body>";
            break;

        case "alle_gegen_alle":
            alle_gegen_alle();
            break;

        /*Datum Letzte Wartung*/
        case "get_datum_lw":
            if (request()->has('g_id')) {
                session()->put('g_id', request()->input('g_id'));
                echo get_datum_lw(request()->input('g_id'));
                echo get_datum_nw(request()->input('g_id'));
            }
            break;


        case "termin_loeschen":
            termin_loeschen_db(request()->input('termin_dat'));
            $m_id = session()->get('mitarbeiter_id');
            $datum_d = session()->get('datum_d');
            termine_tag_tab($m_id, $datum_d);
            break;

        case "wt_aendern":
            form_wt_aendern(request()->input('g_id'));
            break;

        /*Wartungsgerätänderung speichern*/
        case "wgeraet_aendern":
            $gbez = request()->input('gbez');
            $gruppe_id = get_gruppe_id($gbez);
            if (!$gruppe_id) {
                echo "Keine gruppe<br>";
                $values = "'$gbez', '0', '1'";
                if (save_to_db('W_GRUPPE', $values, 'GRUPPE_ID') == true) {
                    echo "<h1>Gruppe $gbez erstellt<br>";
                    echo "Kein Gewerk der Gruppe zugewiesen!!!<h1><br>";
                    $gruppe_id = get_gruppe_id($gbez);
                } else {
                    die('Fehler beim Speichern der neuen Gruppe!');
                }
            }
            $g_id = request()->input('g_id');
            $kos_typ = request()->input('kos_typ');
            $kos_id = request()->input('kos_id');
            $her = request()->input('hersteller');
            $mod = request()->input('modell');
            $bj = request()->input('baujahr');
            $lr = request()->input('lage_raum');
            $wartungsintervall = request()->input('wartungsintervall');
            $zustell_ans = request()->input('zustell_ans');
            $values = "'$g_id', '$gruppe_id','$mod', '$her', '$bj', '$lr', '$kos_typ', '$kos_id', '$zustell_ans', '$wartungsintervall', '1'";

            if (deactivate_wteil($g_id) == TRUE) {
                if (save_to_db('W_GERAETE', $values) == true) {
                    form_wartungsteil(session()->get('kos_typ'), session()->get('kos_id'));
                } else {
                    echo "Fehler beim Speichern des Gerätes";
                }
                echo "Wartungsteil geändert";
            } else {
                echo "Wartungsteil nicht  geändert FEHLER #xxxasdahf#";
            }

            break;

    case "geraete_liste":
        ?>
        <head>
            <title>Wartungskalender Berlussimo</title>
            <link rel="stylesheet" type="text/css" href="<?php echo elixir('css/wp_form.css') ?>"/>
            <script type="text/javascript" src="<?php echo elixir('js/wartungsplaner.js') ?>"></script>
        </head>
    <body>
        <?php
        echo "<div id=\"main\"><div id=\"TERMIN_BOX\">";
        echo "GERÄTE LISTE<br>";
        geraete_liste();
        echo "</div></div></body>";
        break;

    case "handy":
        ?>
        <head>
            <title>Wartungskalender Berlussimo</title>
            <link rel="stylesheet" type="text/css" href="<?php echo elixir('css/wp_form.css') ?>"/>
            <script type="text/javascript" src="<?php echo elixir('js/wartungsplaner.js') ?>"></script>
        </head>
    <body>
        <?php
        if (!request()->has('datum_d')) {
            $datum_d = date("d.m.Y");
        } else {
            $datum_d = request()->input('datum_d');
        }
        handy($datum_d);

        break;

    case "form_start_stop":
        ?>
        <head>
            <title>Wartungskalender Berlussimo</title>
            <link rel="stylesheet" type="text/css" href="<?php echo elixir('css/wp_form.css') ?>"/>
            <script type="text/javascript" src="<?php echo elixir('js/wartungsplaner.js') ?>"></script>
        </head>
    <body>
        <?php
        form_start_stop(request()->input('tab'), request()->input('tab_dat'));
        break;

    case "t_starten":
        ?>
        <head>
            <title>Wartungskalender Berlussimo</title>
            <link rel="stylesheet" type="text/css" href="<?php echo elixir('css/wp_form.css') ?>"/>
            <script type="text/javascript" src="<?php echo elixir('js/wartungsplaner.js') ?>"></script>
        </head>
    <body>
        <?php
        t_starten(request()->input('tab'), request()->input('tab_dat'));
        break;


    case "t_drucken":
        ?>
        <head>
            <title>Wartungskalender Berlussimo</title>
            <link rel="stylesheet" type="text/css" href="<?php echo elixir('css/wp_form.css') ?>"/>
            <script type="text/javascript" src="<?php echo elixir('js/wartungsplaner.js') ?>"></script>
        </head>
    <body>
        <?php
        die('druckansicht');
        t_drucken(request()->input('tab'), request()->input('tab_dat'));
        break;

    case "t_neustart":
        ?>
        <head>
            <title>Wartungskalender Berlussimo</title>
            <link rel="stylesheet" type="text/css" href="<?php echo elixir('css/wp_form.css') ?>"/>
            <script type="text/javascript" src="<?php echo elixir('js/wartungsplaner.js') ?>"></script>
        </head>
    <body>
        <?php
        die('Neustart');
        t_neustart(request()->input('tab'), request()->input('tab_dat'));
        break;

    case "t_beenden":
        ?>
        <head>
            <title>Wartungskalender Berlussimo</title>
            <link rel="stylesheet" type="text/css" href="<?php echo elixir('css/wp_form.css') ?>"/>
            <script type="text/javascript" src="<?php echo elixir('js/wartungsplaner.js') ?>"></script>
        </head>
    <body>
        <?php
        echo 'BEENDET';
        t_beenden(request()->input('tab'), request()->input('tab_dat'));
        break;

        case "t_abbruch":
            ?>
            <head>
                <title>Wartungskalender Berlussimo</title>
                <link rel="stylesheet" type="text/css" href="<?php echo elixir('css/wp_form.css') ?>"/>
                <script type="text/javascript" src="<?php echo elixir('js/wartungsplaner.js') ?>"></script>
            </head>
            <body>
            <?php
            echo 'ABGEBROCHEN';
            handy(date("d.m.Y"));
            break;

        case "detail_geraet":
            echo "<p class=\"zeile_ueber\">DETAIL ZUM GERÄT</p>";
            if (request()->has('tab') && request()->has('tab_id')) {
                alle_details_anzeigen(request()->input('tab'), request()->input('tab_id'));
                form_detail_hinzu2(request()->input('tab'), request()->input('tab_id'));
            }
            break;

        case "unset_g_id":
            session()->forget('g_id');
            break;


        case "pdf_wp":
            $g = new general();
            $datum_d = request()->input('datum_d');
            $b_id = request()->input('benutzer_id');
            if (!empty($datum_d) && !empty($b_id)) {
                $g->pdf_protokoll($datum_d, $b_id);
            } else {
                echo "Benutzer und Datum wählen!";
            }
            break;


        case "karte":
            $g = new general();
            $datum_d = request()->input('datum_d');
            $b_id = request()->input('b_id');
            if (!empty($datum_d) && !empty($b_id)) {
                $g->karte_anzeigen($b_id, $datum_d, 630, 270);
            } else {
                echo "Benutzer und Datum wählen!";
            }
            break;

        case "karte_gross":
            $g = new general();
            $datum_d = request()->input('datum_d');
            $b_id = request()->input('b_id');
            if (!empty($datum_d) && !empty($b_id)) {
                $g->karte_anzeigen($b_id, $datum_d, 700, 550, 10);
            } else {
                echo "Benutzer und Datum wählen!";
            }
            break;


        case "karte_gross_alle":
            $g = new general();
            $g->karte_anzeigen_alle_geraete();
            break;


        case "get_partner_daten":
            if (request()->has('p_id')) {
                $g = new general();
                $p_id = request()->input('p_id');
                $g->get_partner_info($p_id);
                $var = "$g->partner_name|$g->partner_strasse|$g->partner_hausnr|$g->partner_plz|$g->partner_ort";
                echo $var;
            }
            break;

        case "termin_reservieren":
            if (request()->input('datum') == '' && request()->input('von') == '' && request()->input('b_id') == '') {
                session()->forget('reserve_datum');
                session()->forget('reserve_von');
                session()->forget('reserve_bis');
                session()->forget('reserve_dauer');
                session()->forget('reserve_b_id');
                session()->forget('reserve_lon');
                session()->forget('reserve_lat');

                echo "<p class=\"zeile_hinweis_rot\">Reservierung aufgehoben!</p>";
            } else {
                session()->put('reserve_lon', session()->get('ziel_lon'));
                session()->put('reserve_lat', session()->get('ziel_lat'));
                session()->put('reserve_datum', request()->input('datum'));
                session()->put('reserve_von', request()->input('von'));
                session()->put('reserve_bis', request()->input('bis'));

                $dauer = getzeitdiff_min(session()->get('reserve_von'), session()->get('reserve_bis'));
                session()->put('reserve_dauer', $dauer);
                session()->put('reserve_b_id', request()->input('b_id'));
                $benutzername = get_benutzername(request()->input('b_id'));

                echo "<p class=\"zeile_detail\">RESERVIERUNG</p>";
                echo "<p class=\"zeile_hinweis\">Mitarbeiter: $benutzername</p>";
                echo "<p class=\"zeile_hinweis\">Datum: " . session()->get('reserve_datum') . "</p>";
                echo "<p class=\"zeile_hinweis\">Freie Zeit: " . session()->get('reserve_von') . '-' . session()->get('reserve_bis') . "</p>";

                echo "<p class=\"zeile_hinweis\">Gewählte Dauer: $dauer Min</p>";
                if ($dauer > session()->get('termin_dauer')) {
                    echo "<p class=\"zeile_hinweis_gruen\">Gewünschte Dauer: " . session()->get('termin_dauer') . "</p>";
                } else {
                    echo "<p class=\"zeile_hinweis_rot\">Gewünschte Dauer nicht ausreichend: $dauer</p>";
                }
                $b_id = session()->get('reserve_b_id');
                $datum_d = session()->get('reserve_datum');
                $js_t = "onclick=\"termin_reservieren('', '', '', '');";
                $js_t = $js_t . "setTimeout('daj3(\'/wartungsplaner/ajax?option=termine_tag_tab2&b_id=$b_id&datum=$datum_d\', \'rightBox1\')', 800);";
                $js_t .= "\"";

                button('btn_storno_res', 'btn_storno_res', 'Reservierung aufheben', $js_t);
            }
            break;


        case "zeige_reservierung":
            if (session()->has('reserve_lon') && session()->has('reserve_lat')) {
                echo '<pre>';
                print_r(session()->all());
            } else {
                echo "Kein Termin reserviert!";
            }
            break;

        case "phpinfo":
            phpinfo();
            break;

        case "pdf_anschreiben":
            /*echo "HIER PDF BRIEF SERIENBRIEF BERLUSSIMO<br>";*/
            if (request()->has('art') && request()->has('art_id')) {
                $gg = new general();
                $gg->pdf_einwurfzettel(request()->input('art'), request()->input('art_id'));
            } else {
                echo "Kunden wählen";
            }
            break;

        case "route_anzeigen_karte":
            $gg = new general();
            $datum_d = date("d.m.Y");
            $gg->route_anzeigen_karte(21, $datum_d);
            break;


        case "del_sess_vars":
            session()->forget('kos_typ');
            session()->forget('kos_id');
            session()->forget('gruppe_id');
            session()->forget('ziel_lon');
            session()->forget('ziel_lat');
            session()->forget('datum_d');
            session()->forget('einheit_id');
            session()->forget('einheit_bez');
            session()->forget('mitarbeiter_id');
            session()->forget('g_id');
            session()->forget('x');
            session()->forget('ziel_str');
            session()->forget('ziel_nr');
            session()->forget('ziel_ort');
            session()->forget('ziel_plz');
            break;

        case "reg_sortieren":
            session()->put('sortby', request()->input('sortby'));
            break;

        case "mitarbeiter":

            $g = new general();
            $link = route('web::wartungsplaner::ajax', ['option' => 'mitarbeiter'], false);
            $js = "onchange='daj3(\"/wartungsplaner/ajax?option=mitarbeiter_wahl&team_id=\" + this.value, \"rightBox\");daj3(\"/wartungsplaner/ajax?option=mitarbeiter_n_team&team_id=\" + this.value, \"rightBox1\");'";
            $g->dropdown_teams('Team wählen', 'team_id', 'team_id', '', $js);
            $js = "onclick='neues_team();";
            button('snd_teamneu', 'snd_teamneu', 'Neues team bilden', $js);
            break;

        case "mitarbeiter_wahl":
            $g = new general();
            if (request()->has('team_id')) {
                $team_id = request()->input('team_id');
                $js = "onclick='daj3(\"/wartungsplaner/ajax?option=mitarbeiter_profil&b_id=\" + this.value, \"leftBox1\");'";
                if ($g->dropdown_mitarbeiter($team_id, 'Mitarbeiter aus dem Team wählen', 'b_id', 'b_id', '', $js, $class_r = 'reihe', $class_f = 'feld') == true) {
                    $js = "onclick='entf_mitarbeiter_team(\"$team_id\", \"b_id\")'";
                    button('snd_entf', 'snd_entf', 'Entfernen', $js);
                }
            } else {
                echo "Team wählen bitte";
            }
            break;

        case "mitarbeiter_n_team":
            if (request()->has('team_id')) {
                $team_id = request()->input('team_id');
                $g = new general();
                $js = "onclick='daj3(\"/wartungsplaner/ajax?option=mitarbeiter_profil&b_id=\" + this.value, \"leftBox1\");'";
                if ($g->dropdown_mitarbeiter_n_team($team_id, 'Mitarbeiter zum Team hinzufügen', 'nb_id', 'nb_id', '', $js, $class_r = 'reihe', $class_f = 'feld') == true) {
                    $js = "onclick='hinzu_mitarbeiter_team(\"$team_id\", \"nb_id\")'";
                    button('snd_team', 'snd_team', 'Hinzufügen', $js);
                }
            } else {
                echo "Team wählen!!!!";
            }
            break;

        case "mitarbeiter_entfernen":
            if (request()->has('team_id') && request()->has('b_id')) {
                $g = new general();
                $g->mitarbeiter_entfernen(request()->input('team_id'), request()->input('b_id'));
                echo "Mitarbeiter aus dem Team entfernt!";
            }
            break;

        case "mitarbeiter_hinzu":
            if (request()->has('team_id') && request()->has('b_id')) {
                $g = new general();
                $g->mitarbeiter_hinzu(request()->input('team_id'), request()->input('b_id'));
                echo "Mitarbeiter zum Team hinzugefügt!";
            }
            break;

        case "mitarbeiter_profil":
            $b_id = request()->input('b_id');
            $benutzername = get_benutzername(request()->input('b_id'));
            echo "<p class=\"zeile_hinweis_rot\">Profil von $benutzername</p>";
            $g = new general();
            $profil_arr = $g->get_wteam_profil(request()->input('b_id'));
            if (is_array($profil_arr)) {
                $mo = $profil_arr['1'];
                $di = $profil_arr['2'];
                $mi = $profil_arr['3'];
                $do = $profil_arr['4'];
                $fr = $profil_arr['5'];
                $sa = $profil_arr['6'];
                $so = $profil_arr['7'];
                $von = $profil_arr['VON'];
                $bis = $profil_arr['BIS'];
                $termine = $profil_arr['TERMINE_TAG'];
                $start_adresse = $profil_arr['START_ADRESSE'];
                $aktiv = $profil_arr['AKTIV'];
                echo "<table>";
                echo "<tr><th>MO</th><th>DI</th><th>MI</th><th>DO</th><th>FR</th><th>SA</th><th>SO</th><th>VON</th><th>BIS</th><th>TERMINE</th><th>START</th><th>AKTIV</th>";
                echo "<tr class=\"zeile1\"><td>";


                if ($mo == '0') {
                    echo "Status AUS<br>";
                    $js = "onclick='auto_change_profil(\"$b_id\", \"1\", \"1\")'";
                    button('snd', 'btn_snd', 'EIN', $js);
                } else {
                    echo "Status<br>EIN<br>";
                    $js = "onclick='auto_change_profil(\"$b_id\", \"1\", \"0\")'";
                    button('snd', 'btn_snd', 'AUS', $js);
                }


                echo "</td><td>";

                if ($di == '0') {
                    echo "Status AUS<br>";
                    $js = "onclick='auto_change_profil(\"$b_id\", \"2\", \"1\")'";
                    button('snd', 'btn_snd', 'EIN', $js);
                } else {
                    echo "Status<br>EIN<br>";
                    $js = "onclick='auto_change_profil(\"$b_id\", \"2\", \"0\")'";
                    button('snd', 'btn_snd', 'AUS', $js);
                }

                echo "</td><td>";

                if ($mi == '0') {
                    echo "Status AUS<br>";
                    $js = "onclick='auto_change_profil(\"$b_id\", \"3\", \"1\")'";
                    button('snd', 'btn_snd', 'EIN', $js);
                } else {
                    echo "Status<br>EIN<br>";
                    $js = "onclick='auto_change_profil(\"$b_id\", \"3\", \"0\")'";
                    button('snd', 'btn_snd', 'AUS', $js);
                }

                echo "</td><td>";

                if ($do == '0') {
                    echo "Status AUS<br>";
                    $js = "onclick='auto_change_profil(\"$b_id\", \"4\", \"1\")'";
                    button('snd', 'btn_snd', 'EIN', $js);
                } else {
                    echo "Status<br>EIN<br>";
                    $js = "onclick='auto_change_profil(\"$b_id\", \"4\", \"0\")'";
                    button('snd', 'btn_snd', 'AUS', $js);
                }

                echo "</td><td>";

                if ($fr == '0') {
                    echo "Status AUS<br>";
                    $js = "onclick='auto_change_profil(\"$b_id\", \"5\", \"1\")'";
                    button('snd', 'btn_snd', 'EIN', $js);
                } else {
                    echo "Status<br>EIN<br>";
                    $js = "onclick='auto_change_profil(\"$b_id\", \"5\", \"0\")'";
                    button('snd', 'btn_snd', 'AUS', $js);
                }

                echo "</td><td>";

                if ($sa == '0') {
                    echo "Status AUS<br>";
                    $js = "onclick='auto_change_profil(\"$b_id\", \"6\", \"1\")'";
                    button('snd', 'btn_snd', 'EIN', $js);
                } else {
                    echo "Status<br>EIN<br>";
                    $js = "onclick='auto_change_profil(\"$b_id\", \"6\", \"0\")'";
                    button('snd', 'btn_snd', 'AUS', $js);
                }

                echo "</td><td>";

                if ($so == '0') {
                    echo "Status AUS<br>";
                    $js = "onclick='auto_change_profil(\"$b_id\", \"7\", \"1\")'";
                    button('snd', 'btn_snd', 'EIN', $js);
                } else {
                    echo "Status<br>EIN<br>";
                    $js = "onclick='auto_change_profil(\"$b_id\", \"7\", \"0\")'";
                    button('snd', 'btn_snd', 'AUS', $js);
                }

                echo "</td><td>$von</td><td>$bis</td><td>$termine</td><td>$start_adresse</td><td>";

                if ($aktiv == '0') {
                    echo "Status INAKTIV<br>";
                    $js = "onclick='auto_change_profil(\"$b_id\", \"AKTIV\", \"1\")'";
                    button('snd', 'btn_snd', 'EIN', $js);
                } else {
                    echo "Status<br>AKTIV<br>";
                    $js = "onclick='auto_change_profil(\"$b_id\", \"AKTIV\", \"0\")'";
                    button('snd', 'btn_snd', 'AUS', $js);
                }


                echo "</td></tr>";
                echo "</table>";
            } else {
                echo "Kein Profil vorhanden";
            }

            break;
        case "auto_change_profil":
            $b_id = request()->input('b_id');
            $spalte = request()->input('spalte');
            $wert = request()->input('wert');
            $g = new general();
            $g->update_profil($b_id, $spalte, $wert);
            break;

        case "neues_team":
            if (request()->has('team_bez')) {
                $g = new general();
                $g->team_hinzu(request()->input('team_bez'));
            }
            break;
    }//end switch

}//end if