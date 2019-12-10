<?php

$daten = request()->input('daten');
if (request()->has('einheit_raus')) {
    $einheit_raus = request()->input('einheit_raus');
} else {
    $einheit_raus = 'default';
}
if (request()->has('haus_id')) {
    $haus_id = request()->input('haus_id');
} else {
    $haus_id = '';
}
if (request()->has('objekt_id')) {
    $objekt_id = request()->input('objekt_id');
} else {
    $objekt_id = '';
}

switch ($einheit_raus) {

    default :
        break;

    case "einheit_kurz" :
        $form = new mietkonto ();
        $form->erstelle_formular("Liste der Einheiten", NULL);
        if (empty ($objekt_id)) {
            einheit_kurz($haus_id);
        }
        if (!empty ($objekt_id)) {
            einheit_kurz_objekt($objekt_id);
        }
        $form->ende_formular();
        break;

    case "einheit_neu" :
        $e = new einheit ();
        if (request()->has('haus_id') && !empty (request()->input('haus_id'))) {
            $e->form_einheit_neu(request()->input('haus_id'));
        } else {
            $e->form_einheit_neu('');
        }
        break;

    case "einheit_speichern" :
        if (request()->has('kurzname') && request()->has('lage') && request()->has('qm') && request()->has('haus_id') && request()->has('typ')) {
            $e = new einheit ();
            $kurzname = request()->input('kurzname');
            $lage = request()->input('lage');
            $qm = request()->input('qm');
            $haus_id = request()->input('haus_id');
            $typ = request()->input('typ');
            $e->einheit_speichern($kurzname, $lage, $qm, $haus_id, $typ);
            echo "Einheit $kurzname wurde erstellt.";
            weiterleiten_in_sec(route('web::einheiten::legacy', ['einheit_raus' => 'einheit_kurz', 'haus_id' => $haus_id], false), 2);
        } else {
            echo "Dateneingabe zur Einheit unvollständig";
        }
        break;

    case "einheit_aendern" :
        if (request()->has('einheit_id')) {
            $e = new einheit ();
            $e->form_einheit_aendern(request()->input('einheit_id'));
        } else {
            fehlermeldung_ausgeben("Einheit wählen!");
        }
        break;

    case "einheit_speichern_ae" :
        if (request()->has('dat') && request()->has('einheit_id') && request()->has('qm') && request()->has('kurzname') && request()->has('lage') && request()->has('haus_id') && request()->has('typ')) {
            $e = new einheit ();
            $e->einheit_update(request()->input('dat'), request()->input('einheit_id'), request()->input('kurzname'), request()->input('lage'), request()->input('qm'), request()->input('haus_id'), request()->input('typ'));
            hinweis_ausgeben("Einheit aktualisiert");
        } else {
            fehlermeldung_ausgeben("Daten unvollständig übermittelt!");
        }
        break;

    case "mieterliste_aktuell" :
        $e = new einheit ();
        if (request()->has('objekt_id') && !empty (request()->input('objekt_id'))) {
            $e->pdf_mieterliste(0, request()->input('objekt_id'));
        } else {
            $e->pdf_mieterliste(0);
        }
        break;

    case "mieteremail_aktuell" :
        $e = new einheit ();
        if (request()->input('objekt_id') && !empty (request()->input('objekt_id'))) {
            $o = new objekt ();
            $o->get_objekt_infos(request()->input('objekt_id'));
            echo "<h1>$o->objekt_kurzname</h1>";

            $emails_arr = $e->emails_mieter_arr(request()->input('objekt_id'));
            if (is_array($emails_arr)) {
                $emails_arr_u = array_values(array_unique($emails_arr));
                $anz = count($emails_arr_u);
                echo "<hr><a href=\"mailto:?bcc=";
                for ($a = 0; $a < $anz; $a++) {
                    $email = $emails_arr_u [$a];
                    echo "$email";
                    if ($a < $anz - 1) {
                        echo ",";
                    }
                }
                echo "\">Email an alle Mieter ($anz Emailadressen)</a>";
            } else {
                echo "Keine Emailadressen der Mieter";
            }
        } else {
            fehlermeldung_ausgeben("Objekt für Email wählen");
        }
        break;
}
function einheit_kurz($haus_id)
{
    // ORDER BY LPAD(inhalt,8,'0')
    if (empty ($haus_id)) {
        $db_abfrage = "SELECT EINHEIT_ID, EINHEIT_KURZNAME, EINHEIT_LAGE, EINHEIT_QM, HAUS_ID, TYP FROM EINHEIT WHERE EINHEIT_AKTUELL='1' ORDER BY LENGTH(EINHEIT_KURZNAME), EINHEIT_KURZNAME ASC";
    } else {
        $db_abfrage = "SELECT EINHEIT_ID, EINHEIT_KURZNAME, EINHEIT_LAGE, EINHEIT_QM, HAUS_ID, TYP FROM EINHEIT WHERE EINHEIT_AKTUELL='1' && HAUS_ID='$haus_id' ORDER BY LPAD(EINHEIT_KURZNAME, LENGTH(EINHEIT_KURZNAME), '1')  ASC";
    }

    $result = DB::select($db_abfrage);

    if (empty($result)) {
        echo "<h5><b>Keine Einheiten vorhanden.</b></h5>";
    } else {
        echo "<table class=\"sortable striped\">\n";
        echo "<tr><th style='width: 10%'>Einheit</th><th style='width: 5%'>Typ</th><th style='width: 5%'>Mietkonto</th><th style='width: 20%'>Mieter</th><th style='width: 20%'>Anschrift</th><th style='width: 5%'>Lage</th><th style='width: 5%'>Fläche</th><th style='width: 6%'>Optionen</th></tr>";
        foreach ($result as $row) {
            $mieteranzahl = mieter_anzahl($row['EINHEIT_ID']);
            $haus_kurzname = haus_strasse_nr($row['HAUS_ID']);
            if ($row['TYP'] != 'Wohneigentum') {
                if ($mieteranzahl == "unvermietet") {
                    $mieter = "leer";
                    $mietkonto_link = "";
                } else {
                    $mieter = "Mieter:($mieteranzahl)";
                    $mietvertrags_id = vertrags_id($row['EINHEIT_ID']);
                    if (!empty ($mietvertrags_id)) {
                        $mietkonto_link = "<a href='" . route('web::mietkontenblatt::legacy', ['anzeigen' => 'mietkonto_uebersicht_detailiert', 'mietvertrag_id' => $mietvertrags_id]) . "'>Mietkonto</a>";
                    }
                }
                $einheit_link = "<a class=\"table_links\" href='" . route('web::uebersicht::legacy', ['anzeigen' => 'einheit', 'einheit_id' => $row['EINHEIT_ID']]) . "'>$row[EINHEIT_KURZNAME]</a>";
            } // end Mietswohnungen
            if ($row['TYP'] == 'Wohneigentum') {
                $einheit_link = "<a class=\"table_links\" href='" . route('web::weg::legacy', ['option' => 'einheit_uebersicht', 'einheit_id' => $row['EINHEIT_ID']]) . "'>$row[EINHEIT_KURZNAME]</a>";
            }
            $EINHEIT_QM = nummer_punkt2komma($row['EINHEIT_QM']);

            $detail_check = detail_check("Einheit", $row['EINHEIT_ID']);
            if ($detail_check > 0) {
                $detail_link = "<a class=\"table_links\" href=\"?daten=details&option=details_anzeigen&detail_tabelle=EINHEIT&detail_id=$row[EINHEIT_ID]\">Details</a>";
            } else {
                $detail_link = "<a class=\"table_links\" href=\"?daten=details&option=details_hinzu&detail_tabelle=EINHEIT&detail_id=$row[EINHEIT_ID]\">Neues Detail</a>";
            }
            $link_aendern = "<a class=\"table_links\" href='" . route('web::einheiten::legacy', ['einheit_raus' => 'einheit_aendern', 'einheit_id' => $einheit_id]) . "'>Ändern</a>";
            if ($TYP != 'Wohneigentum') {
                echo "<tr><td>$einheit_link</td><td>$row[TYP]</td><td>$mietkonto_link</td><td>";
                if ($mieter != "leer") {
                    mieterid_zum_vertrag($mietvertrags_id);
                }
                echo "</td><td>$haus_kurzname</td><td>$row[EINHEIT_LAGE]</td><td>$EINHEIT_QM</td><td>$detail_link $link_aendern</td></tr>\n";
            }  // ende if WEG
            else {
                echo "<tr><td>$einheit_link</td><td>$TYP</td><td></td><td>";
                echo "</td><td>$haus_kurzname</td><td>$row[EINHEIT_LAGE]</td><td>$EINHEIT_QM</td><td>$detail_link $link_aendern</td></tr>\n";
            }
        }
        echo "</table>";
    }
}

function einheit_kurz_objekt($objekt_id)
{
    $my_arr = DB::select("SELECT OBJEKT_KURZNAME, EINHEIT_ID, EINHEIT_KURZNAME, EINHEIT_LAGE, EINHEIT_QM,  HAUS_STRASSE, HAUS_NUMMER, TYP
FROM `EINHEIT`
RIGHT JOIN (
HAUS, OBJEKT
) ON ( EINHEIT.HAUS_ID = HAUS.HAUS_ID && HAUS.OBJEKT_ID = OBJEKT.OBJEKT_ID && OBJEKT.OBJEKT_ID = '$objekt_id' )
WHERE EINHEIT_AKTUELL='1' GROUP BY EINHEIT_ID ORDER BY LPAD(EINHEIT_KURZNAME, LENGTH(EINHEIT_KURZNAME), '1') ASC");

    if (empty($my_arr)) {
        echo "<h5>Keine Einheiten vorhanden.</h5>";
    } else {
        echo "<table class=\"striped sortable\" width=100%>\n";
        $objekt_kurzname = $my_arr [0] ['OBJEKT_KURZNAME'];
        echo "<thead>";
        echo "<tr><th style='width: 10%'>Einheit</th><th style='width: 5%'>Typ</th><th style='width: 5%'>Mietkonto</th><th style='width: 20%'>Mieter</th><th style='width: 20%'>Anschrift</th><th style='width: 5%'>Lage</th><th style='width: 5%'>Fläche</th><th style='width: 6%'>Optionen</th></tr></thead>\n";
        $numrows = count($my_arr);
        for ($a = 0; $a < $numrows; $a++) {
            $einheit_id = $my_arr [$a] ['EINHEIT_ID'];
            $einheit_kurzname = $my_arr [$a] ['EINHEIT_KURZNAME'];
            $einheit_lage = $my_arr [$a] ['EINHEIT_LAGE'];
            $einheit_qm = $my_arr [$a] ['EINHEIT_QM'];
            $mieteranzahl = mieter_anzahl($einheit_id);
            $haus_kurzname = $my_arr [$a] ['HAUS_STRASSE'] . $my_arr [$a] ['HAUS_NUMMER'];
            $TYP = $my_arr [$a] ['TYP'];
            if ($mieteranzahl == "unvermietet") {
                $mieter = "leer";
                $mietkonto_link = "";
                /*
                 * Prüfen ob es einen Eigentümer gibt und wenn ja, zahlt er nebenkosten heizkostenvorschüssen,
                 * falls ja dann SELBSTNUTZER";
                 */
                $weg = new weg ();
                $weg->get_last_eigentuemer($einheit_id);
                if (isset ($weg->eigentuemer_id)) {
                    // $hg_arr = $weg->get_moegliche_def('Einheit', $einheit_id);
                    $monat = date("m");
                    $jahr = date("Y");
                    $nk_vorschuss_sn = $weg->get_summe_kostenkat_monat($monat, $jahr, 'Einheit', $einheit_id, '6020');
                    if ($nk_vorschuss_sn) {
                        $weg->get_eigentumer_id_infos3($weg->eigentuemer_id);
                        $mieter_name = "<b>WEG-SELBSTNUTZER:</b><br> $weg->empf_namen_u";
                        $eig_link = "<a href='" . route('web::weg::legacy', ['option' => 'einheit_uebersicht', 'einheit_id' => $einheit_id]) . "'>$mieter_name</a>";
                    } else {
                        /* Eigentpmer zahlt keine Vorschüsse, dann vermietet er und ist kein Selbstnutzer */
                        $mieter = "leer";
                    }
                }
            } else {
                $mieter = "Mieter:($mieteranzahl)";
                $mietvertrags_id = vertrags_id($einheit_id);
                if (!empty ($mietvertrags_id)) {
                    $mietkonto_link = "<a href='" . route('web::mietkontenblatt::legacy', ['anzeigen' => 'mietkonto_uebersicht_detailiert', 'mietvertrag_id' => $mietvertrags_id]) . "'>Mietkonto</a>";
                }
            }
            if ($TYP != 'Wohneigentum') {
                $einheit_link = "<a class=\"table_links\" href='" . route('web::uebersicht::legacy', ['anzeigen' => 'einheit', 'einheit_id' => $einheit_id]) . "'>$einheit_kurzname</a>";
            } else {
                $einheit_link = "<a class=\"table_links\" href='" . route('web::weg::legacy', ['option' => 'einheit_uebersicht', 'einheit_id' => $einheit_id]) . "'>$einheit_kurzname</a>";
            }

            $link_aendern = "<a class=\"table_links\" href='" . route('web::einheiten::legacy', ['einheit_raus' => 'einheit_aendern', 'einheit_id' => $einheit_id]) . "'>Ändern</a>";

            $detail_check = detail_check("Einheit", $einheit_id);
            if ($detail_check > 0) {
                $detail_link = "<a class=\"table_links\" href='" . route('web::details::legacy', ['option' => 'details_anzeigen', 'detail_tabelle' => 'Einheit', 'detail_id' => $einheit_id]) . "'>Details</a>";
            } else {
                $detail_link = "<a class=\"table_links\" href='" . route('web::details::legacy', ['option' => 'details_hinzu', 'detail_tabelle' => 'Einheit', 'detail_id' => $einheit_id]) . "'>Neues Detail</a>";
            }

            echo "<tr><td>$einheit_link</td><td>$TYP</td><td>$mietkonto_link</td><td>";
            if ($mieter != "leer" && !preg_match("/WEG-SELBSTNUTZER/i", $mieter)) {
                mieterid_zum_vertrag($mietvertrags_id);
            }
            if (isset ($eig_link)) {
                echo $eig_link;
            } else {
                echo $mieter;
            }
            echo "</td><td>$haus_kurzname</td><td>$einheit_lage</td><td>$einheit_qm</td><td>$detail_link $link_aendern</td></tr>\n";

            unset ($mieter);
            unset ($eig_link);
            unset ($link_aendern);
        }
        echo "</table>";
    }
}