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
            weiterleiten_in_sec(route('legacy::einheiten::index', ['einheit_raus' => 'einheit_kurz', 'haus_id' => $haus_id], false), 2);
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
        // $db_abfrage = "SELECT EINHEIT_ID, EINHEIT_DAT, EINHEIT_LAGE, HAUS_ID, EINHEIT_KURZNAME, EINHEIT_AUSSTATTUNG FROM EINHEIT where HAUS_ID='$haus_id' && EINHEIT_AKTUELL='1'";
    }

    $resultat = mysql_query($db_abfrage) or die (mysql_error());

    $numrows = mysql_numrows($resultat);
    if ($numrows < 1) {
        echo "<h1><b>Keine Einheiten vorhanden!!!</b></h1>";
    } else {
        // echo "<table class=\"tabelle_haus\" width=100%>\n";
        $objekt_kurzname = objekt_kurzname_of_haus($haus_id);
        $haus_kurzname = haus_strasse_nr($haus_id);

        iframe_start();
        echo "<table class=\"sortable striped\">\n";
        echo "<tr><th>EINHEIT</th><th>TYP</TH><th>KONTO</th><th>MIETER</th><th>Anschrift</th><th>Lage</th><th>Fläche</th><th>OPTION</th></tr>";
        $counter = 0;
        while (list ($EINHEIT_ID, $EINHEIT_KURZNAME, $EINHEIT_LAGE, $EINHEIT_QM, $HAUS_ID, $TYP) = mysql_fetch_row($resultat)) {
            $mieteranzahl = mieter_anzahl($EINHEIT_ID);
            $haus_kurzname = haus_strasse_nr($HAUS_ID);
            if ($TYP != 'Wohneigentum') {
                if ($mieteranzahl == "unvermietet") {
                    $mieter = "leer";
                    $mietkonto_link = "";
                } else {
                    $mieter = "Mieter:($mieteranzahl)";
                    $mietvertrags_id = vertrags_id($EINHEIT_ID);
                    if (!empty ($mietvertrags_id)) {
                        $mietkonto_link = "<a href='" . route('legacy::mietkontenblatt::index', ['anzeigen' => 'mietkonto_uebersicht_detailiert', 'mietvertrag_id' => $mietvertrags_id]) . "'>MIETKONTO</a>";
                    }
                }
                $einheit_link = "<a class=\"table_links\" href='" . route('legacy::uebersicht::index', ['anzeigen' => 'einheit', 'einheit_id' => $EINHEIT_ID]) . "'>$EINHEIT_KURZNAME</a>";
            } // end Mietswohnungen
            if ($TYP == 'Wohneigentum') {
                $einheit_link = "<a class=\"table_links\" href='" . route('legacy::weg::index' , ['option' => 'einheit_uebersicht', 'einheit_id' => $EINHEIT_ID]) . "'>$EINHEIT_KURZNAME</a>";
            }
            $EINHEIT_QM = nummer_punkt2komma($EINHEIT_QM);

            $detail_check = detail_check("EINHEIT", $EINHEIT_ID);
            if ($detail_check > 0) {
                $detail_link = "<a class=\"table_links\" href=\"?daten=details&option=details_anzeigen&detail_tabelle=EINHEIT&detail_id=$EINHEIT_ID\">Details</a>";
            } else {
                $detail_link = "<a class=\"table_links\" href=\"?daten=details&option=details_hinzu&detail_tabelle=EINHEIT&detail_id=$EINHEIT_ID\">Neues Detail</a>";
            }
            if ($TYP != 'Wohneigentum') {
                $counter++;
                if ($counter == 1) {
                    echo "<tr class=\"zeile1\"><td width=150>$einheit_link</td><td>$TYP</td><td> $mietkonto_link</td><td width=200>";
                    if ($mieter != "leer") {
                        echo mieterid_zum_vertrag($mietvertrags_id);
                    }
                    echo "</td><td width=200>$haus_kurzname</td><td width=100>$EINHEIT_LAGE</td><td width=40>$EINHEIT_QM</td><td>$detail_link</td></tr>\n";
                }
                if ($counter == 2) {
                    echo "<tr class=\"zeile2\"><td width=150>$einheit_link</td><td>$TYP</td><td> $mietkonto_link</td><td width=200>";
                    if ($mieter != "leer") {
                        echo mieterid_zum_vertrag($mietvertrags_id);
                    }
                    echo "</td><td width=200>$haus_kurzname</td><td width=100>$EINHEIT_LAGE</td><td width=40>$EINHEIT_QM</td><td>$detail_link</td></tr>\n";
                    $counter = 0;
                }
            }  // ende if WEG
            else {
                $counter++;
                if ($counter == 1) {
                    echo "<tr class=\"zeile1\"><td width=150>$einheit_link</td><td>$TYP</td><td></td><td width=200>";
                    echo "</td><td width=200>$haus_kurzname</td><td width=100>$EINHEIT_LAGE</td><td width=40>$EINHEIT_QM</td><td>$detail_link</td></tr>\n";
                }
                if ($counter == 2) {
                    echo "<tr class=\"zeile2\"><td width=150>$einheit_link</td><td>$TYP</td><td></td><td width=200>";
                    echo "</td><td width=200>$haus_kurzname</td><td width=100>$EINHEIT_LAGE</td><td width=40>$EINHEIT_QM</td><td>$detail_link</td></tr>\n";
                    $counter = 0;
                }
            }
        }
        echo "</table>";
    }
    iframe_end();
}

function einheit_kurz_objekt($objekt_id)
{
    $result = mysql_query("SELECT OBJEKT_KURZNAME, EINHEIT_ID, EINHEIT_KURZNAME, EINHEIT_LAGE, EINHEIT_QM,  HAUS_STRASSE, HAUS_NUMMER, TYP
FROM `EINHEIT`
RIGHT JOIN (
HAUS, OBJEKT
) ON ( EINHEIT.HAUS_ID = HAUS.HAUS_ID && HAUS.OBJEKT_ID = OBJEKT.OBJEKT_ID && OBJEKT.OBJEKT_ID = '$objekt_id' )
WHERE EINHEIT_AKTUELL='1' GROUP BY EINHEIT_ID ORDER BY LPAD(EINHEIT_KURZNAME, LENGTH(EINHEIT_KURZNAME), '1') ASC");

    while ($row = mysql_fetch_assoc($result))
        $my_arr [] = $row;

    $numrows = mysql_numrows($result);
    if ($numrows < 1) {
        echo "<h1><b>Keine Einheiten vorhanden!!!</b></h1>";
    } else {
        echo "<table class=\"tabelle_haus\" width=100%>\n";
        $objekt_kurzname = $my_arr ['0'] ['OBJEKT_KURZNAME'];
        echo "<tr class=\"feldernamen\"><td colspan=7>Einheiten im Objekt $objekt_kurzname</td></tr>\n";
        echo "<tr class=\"feldernamen\"><td width=150>Kurzname</td><td>OPTION</td><td width=200>Mieter</td><td width=200>Anschrift</td><td width=100>Lage</td><td width=40>m²</td><td>Details</td></tr>\n";
        echo "</table>";
        iframe_start();
        echo "<table width=100%>\n";
        $counter = 0;
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
                        $eig_link = "<a href='" . route('legacy::weg::index' , ['option' => 'einheit_uebersicht', 'einheit_id' => $einheit_id]) . "'>$mieter_name</a>";
                    } else {
                        /* Eigentpmer zahlt keine Vorschüsse, dann vermietet er und ist kein Selbstnutzer */
                        $mieter = "leer";
                    }
                }
            } else {
                $mieter = "Mieter:($mieteranzahl)";
                $mietvertrags_id = vertrags_id($einheit_id);
                if (!empty ($mietvertrags_id)) {
                    $mietkonto_link = "<a href='" . route('legacy::mietkontenblatt::index', ['anzeigen' => 'mietkonto_uebersicht_detailiert', 'mietvertrag_id' => $mietvertrags_id]) . "'>MIETKONTO</a>";
                }
            }
            if ($TYP != 'Wohneigentum') {
                $einheit_link = "<a class=\"table_links\" href='" . route('legacy::uebersicht::index', ['anzeigen' => 'einheit', 'einheit_id' => $einheit_id]) . "'>$einheit_kurzname</a>";
            } else {
                $einheit_link = "<a class=\"table_links\" href='" . route('legacy::weg::index' , ['option' => 'einheit_uebersicht', 'einheit_id' => $einheit_id]) . "'>$einheit_kurzname</a>";
            }

            $link_aendern = "<a class=\"table_links\" href='" . route('legacy::einheiten::index', ['einheit_raus' => 'einheit_aendern' , 'einheit_id' => $einheit_id]) . "'>ÄNDERN</a>";

            $detail_check = detail_check("EINHEIT", $einheit_id);
            if ($detail_check > 0) {
                $detail_link = "<a class=\"table_links\" href='" . route('legacy::details::index', ['option' => 'details_anzeigen', 'detail_tabelle' => 'EINHEIT', 'detail_id' => $einheit_id]) . "'>Details</a>";
            } else {
                $detail_link = "<a class=\"table_links\" href='" . route('legacy::details::index', ['option' => 'details_hinzu', 'detail_tabelle' => 'EINHEIT', 'detail_id' => $einheit_id]) . "'>Neues Detail</a>";
            }

            $counter++;
            if ($counter == 1) {
                echo "<tr class=\"zeile1\"><td width=150>$einheit_link $mietkonto_link</td><td>$TYP</td><td>$link_aendern</td><td width=200>";
                if ($mieter != "leer" && !preg_match("/WEG-SELBSTNUTZER/i", $mieter)) {
                    echo mieterid_zum_vertrag($mietvertrags_id);
                }
                if (isset ($eig_link)) {
                    echo $eig_link;
                } else {
                    echo $mieter;
                }
                echo "</td><td width=200>$haus_kurzname</td><td width=100>$einheit_lage</td><td width=40>$einheit_qm</td><td>$detail_link</td></tr>\n";
            }
            if ($counter == 2) {
                echo "<tr class=\"zeile2\"><td width=150>$einheit_link $mietkonto_link</td><td>$TYP</td><td>$link_aendern</td><td width=200>";
                if ($mieter != "leer" && !preg_match("/WEG-SELBSTNUTZER/i", $mieter)) {
                    echo mieterid_zum_vertrag($mietvertrags_id);
                    // echo $eig_link;
                    if (isset ($eig_link)) {
                        echo $eig_link;
                    } else {
                        echo $mieter;
                    }
                    // echo $eig_link;
                } else {
                    // echo $eig_link;
                }
                echo "</td><td width=200>$haus_kurzname</td><td width=100>$einheit_lage</td><td width=40>$einheit_qm</td><td>$detail_link</td></tr>\n";
                $counter = 0;
            }
            // echo $counter;
            unset ($mieter);
            unset ($eig_link);
            unset ($link_aendern);
        }
        echo "</table>";
    }
    iframe_end();
}

?>
