<?php

$daten = request()->input('daten');
$objekte_raus = request()->input('objekte_raus');

switch ($objekte_raus) {

    case "objekte_kurz" :
        $form = new formular ();
        $form->erstelle_formular("Liste bestehender Objekte", NULL);
        objekte_kurz();
        $form->ende_formular();
        break;

    case "objekt_anlegen" :
        $o = new objekt ();
        $o->form_objekt_anlegen();
        break;

    case "objekt_speichern" :
        if (request()->isMethod('post')) {
            if (request()->has('objekt_kurzname') && request()->has('eigentuemer')) {
                echo "ALLES OK";
                $o = new objekt ();
                $o->objekt_speichern(request()->input('objekt_kurzname'), request()->input('eigentuemer'));
                weiterleiten(route('legacy::objekte::index', ['objekte_raus' => 'objekt_kurz'], false));
            }
        } else {
            echo "DATEN UNVOLLSTÄNDIG";
        }
        break;

    case "objekt_aendern" :
        $o = new objekt ();
        $o->form_objekt_aendern(request()->input('objekt_id'));
        break;

    case "objekt_aendern_send" :
        if (request()->isMethod('post')) {
            if (request()->has('objekt_dat') && request()->has('objekt_id') && request()->has('objekt_kurzname') && request()->has('eigentuemer')) {
                echo "ALLES OK";
                $o = new objekt ();
                $o->objekt_aendern(request()->input('objekt_dat'), request()->input('objekt_id'), request()->input('objekt_kurzname'), request()->input('eigentuemer'));
                weiterleiten(route('legacy::objekte::index', ['objekte_raus' => 'objekt_kurz'], false));
            }
        } else {
            echo "DATEN UNVOLLSTÄNDIG";
        }
        break;

    case "checkliste" :
        if (request()->has('objekt_id')) {
            $o = new objekt ();
            $o->pdf_checkliste(request()->input('objekt_id'));
        } else {
            echo "Objekt auswählen";
        }
        break;

    case "mietaufstellung" :
        if (request()->has('objekt_id')) {
            $o = new objekt ();
            $o->pdf_mietaufstellung(request()->input('objekt_id'));
        } else {
            echo "Objekt auswählen";
        }
        break;

    case "mietaufstellung_m_j" :
        if (request()->has('objekt_id')) {
            $objekt_id = request()->input('objekt_id');
            if (request()->has('monat') && request()->has('jahr')) {
                $monat = request()->input('monat');
                $jahr = request()->input('jahr');
                $o = new objekt ();
                $o->pdf_mietaufstellung_m_j($objekt_id, $monat, $jahr);
            } else {
                echo "Monat und Jahr wählen";
            }
        }
        break;

    case "mietaufstellung_j" :
        if (request()->has('objekt_id')) {
            $objekt_id = request()->input('objekt_id');
            if (request()->has('jahr')) {
                $jahr = request()->input('jahr');
                $o = new objekt ();
                $o->pdf_mietaufstellung_j($objekt_id, $jahr);
            } else {
                echo "Monat und Jahr wählen";
            }
        }
        break;

    case "objekt_kopieren" :
        $o = new objekt ();
        $o->form_objekt_kopieren();
        break;

    case "copy_sent" :
        /*
         * Neues Objekt anlegen
         * alle einheiten kopieren und umbenennen mit vorzeichen
         */
        if (request()->has('objekt_id') && request()->has('objekt_kurzname') && request()->has('vorzeichen') && request()->has('eigentuemer_id') && request()->has('datum_u')) {
            $objekt_id = request()->input('objekt_id');
            $objekt_kurzname = request()->input('objekt_kurzname');
            $vorzeichen = request()->input('vorzeichen');
            $datum_u = request()->input('datum_u');
            $eigentuemer_id = request()->input('eigentuemer_id');
            $o = new objekt ();
            if (request()->has('saldo_berechnen')) {
                $o->objekt_kopieren($objekt_id, $eigentuemer_id, $objekt_kurzname, $vorzeichen, $datum_u, 1);
            } else {
                $o->objekt_kopieren($objekt_id, $eigentuemer_id, $objekt_kurzname, $vorzeichen, $datum_u, 0);
            }
        } else {
            fehlermeldung_ausgeben("Bitte alle felder ausfüllen!");
        }
        break;

    /* Sollmieten Zeitraum Formular */
    case "sollmieten_zeitraum_form" :
        $f = new formular ();
        $f->erstelle_formular('Vereinbarte Nettosollmieten für Zeitraum', null);
        break;

    /* Sollmieten Zeitraum */
    case "sollmieten_zeitraum" :
        $mv = new mietvertraege ();
        $mv->mieten_pdf(session()->get('objekt_id'), '2013-05-01', '2013-12-31');
        break;

    /* Ist,ieten Zeitraum */
    case "istmieten_zeitraum" :
        $mv = new mietvertraege ();
        $arr = $mv->istmieten_zeitraum(session()->get('geldkonto_id'), '2013-01-01', '2013-12-31', 80001);
        $mv->pdf_istmieten($arr, '01.01.2013', '31.12.2013');
        break;

    case "import" :
        $im = new import ();
        $im->import_arr('fot31.csv'); // fontane31
        break;

    /* IMPORT GFAD */
    case "einheit_speichern" :
        $e = new einheit ();
        $kurzname = request()->input('kurzname');
        $lage = request()->input('lage');
        $qm = request()->input('qm');
        $haus_id = request()->input('haus_id');
        $typ = request()->input('typ');
        $einheit_id = $e->einheit_speichern($kurzname, $lage, $qm, $haus_id, $typ);

        if (request()->has('weg_qm')) {
            $qm = request()->input('weg_qm');
            $d = new detail ();
            $d->detail_speichern_2('EINHEIT', $einheit_id, 'WEG-Fläche', request()->input('weg_qm'), 'Importiert');
        }

        if (request()->has('weg_mea')) {
            $d = new detail ();
            $d->detail_speichern_2('EINHEIT', $einheit_id, 'WEG-Anteile', request()->input('weg_mea'), 'Importiert');
        }

        $weg = new weg ();
        $ihr = nummer_punkt2komma(0.4 * nummer_komma2punkt($qm));
        $weg->wohngeld_def_speichern('01.01.2014', '00.00.0000', $ihr, 'Instandhaltungsrücklage', 6030, 'Hausgeld', 6000, $einheit_id);
        $weg->wohngeld_def_speichern('01.01.2014', '00.00.0000', 30, 'WEG-Verwaltergebühr', 6060, 'Hausgeld', 6000, $einheit_id);

        weiterleiten(route('legacy::objekte::index', ['objekte_raus' => 'import'], false));
        break;

    case "person_speichern" :
        $p = new personen ();
        $geb_dat = request()->input('geburtsdatum');
        $nachname = request()->input('nachname');
        $vorname = request()->input('vorname');
        $geschlecht = request()->input('geschlecht');
        $telefon = request()->input('telefon');
        $handy = request()->input('handy');
        $email = request()->input('email');
        $person_id = $p->save_person($nachname, $vorname, $geb_dat, $geschlecht, $telefon, $handy, $email);
        $p_typ = request()->input('p_typ'); // Mieter oder ET
        $einheit_id = request()->input('einheit_id');
        if ($p_typ == 'ET') {
            $et_seit = request()->input('et_seit');
            echo "ET $einheit_id $person_id $et_seit";
            $im = new import ();
            $von = date_german2mysql($et_seit);
            if ($im->get_last_eigentuemer_id($einheit_id) != false) {
                $et_id = $im->get_last_eigentuemer_id($einheit_id);
            } else {
                $et_id = $im->et_erstellen($einheit_id, $von);
            }
            if (!empty ($person_id) && $person_id != 0) {
                $im->et_person_hinzu($et_id, $person_id);
            }
        }

        weiterleiten(route('legacy::objekte::index', ['objekte_raus' => 'import'], false));
        break;

    case "person_et" :
        $p_typ = request()->input('p_typ'); // Mieter oder ET
        $einheit_id = request()->input('einheit_id');
        $person_id = request()->input('name_g');
        if ($p_typ == 'ET') {
            $et_seit = request()->input('et_seit');
            echo "ET $einheit_id $person_id $et_seit";
            $im = new import ();
            $von = date_german2mysql($et_seit);
            if ($im->get_last_eigentuemer_id($einheit_id) != false) {
                $et_id = $im->get_last_eigentuemer_id($einheit_id);
            } else {
                $et_id = $im->et_erstellen($einheit_id, $von);
            }
            $im->et_person_hinzu($et_id, $person_id);
        }
        weiterleiten(route('legacy::objekte::index', ['objekte_raus' => 'import'], false));
        break;

    case "stammdaten_pdf" :
        if (request()->has('objekt_id')) {
            session()->put('objekt_id', request()->input('objekt_id'));
            $pdf = new Cezpdf ('a4', 'portrait');
            $oo = new objekt ();
            $oo->get_objekt_infos(session()->get('objekt_id'));

            $bpdf = new b_pdf ();
            $bpdf->b_header($pdf, 'Partner', session()->get('partner_id'), 'portrait', 'Helvetica.afm', 6);

            $st = new stammdaten ();
            /* Objektstammdaten */
            $st->pdf_stamm_objekt($pdf, request()->input('objekt_id'));

            ob_end_clean();
            $pdf_opt ['Content-Disposition'] = "Stammdaten_" . $oo->objekt_kurzname . '_' . date("d.m.Y") . '.pdf';
            $pdf->ezStream($pdf_opt);
        } else {
            fehlermeldung_ausgeben("Objekt wählen");
        }
        break;

    case "mv_speichern" :
        $mv = new mietvertraege ();
        $einheit_id = request()->input('einheit_id');
        $von = request()->input('einzug');
        $bis = request()->input('auszug');
        $mv_id = $mv->mietvertrag_speichern($von, $bis, $einheit_id);

        $anz_p = count(request()->input('person_ids'));
        for ($a = 0; $a < $anz_p; $a++) {
            $person_id = request()->input('person_ids') [$a];
            $mv->person_zu_mietvertrag($person_id, $mv_id);
        }

        $me = new mietentwicklung ();
        $von = date_german2mysql($von);
        $bis = date_german2mysql($bis);
        if (request()->has('km')) {
            $km = nummer_komma2punkt(request()->input('km'));
            $me->me_speichern('MIETVERTRAG', $mv_id, 'Miete kalt', $von, $bis, $km, '0.00');
        }
        if (request()->has('nk')) {
            $nk = nummer_komma2punkt(request()->input('nk'));
            $me->me_speichern('MIETVERTRAG', $mv_id, 'Nebenkosten Vorauszahlung', $von, $bis, $nk, '0.00');
        }
        if (request()->has('hk')) {
            $hk = nummer_komma2punkt(request()->input('hk'));
            $me->me_speichern('MIETVERTRAG', $mv_id, 'Heizkosten Vorauszahlung', $von, $bis, $hk, '0.00');
        }

        if (request()->has('kabel_tv')) {
            $kabel_tv = nummer_komma2punkt(request()->input('kabel_tv'));
            $me->me_speichern('MIETVERTRAG', $mv_id, 'Kabel TV', $von, $bis, $kabel_tv, '0.00');
        }
        $jahr_3 = date("Y") - 3;
        $m_day = date("m-d");
        $datum_3 = "$jahr_3-$m_day";
        if (request()->has('km_3')) {
            $km_3 = nummer_komma2punkt(request()->input('km_3'));
            $me->me_speichern('MIETVERTRAG', $mv_id, 'Miete kalt', $datum_3, $datum_3, $kabel_tv, '0.00');
        }

        if (request()->has('kaution')) {
            $d = new detail ();
            $d->detail_speichern_2('MIETVERTRAG', $mv_id, 'Kautionshinweis', request()->input('kaution'), 'Importiert');
        }

        if (request()->has('klein_rep')) {
            $d = new detail ();
            $d->detail_speichern_2('MIETVERTRAG', $mv_id, 'Kleinreparaturen', request()->input('klein_rep'), 'Importiert');
        }

        if (request()->has('zusatzinfo')) {
            $d = new detail ();
            $d->detail_speichern_2('MIETVERTRAG', $mv_id, 'Zusatzinfo', request()->input('zusatzinfo'), 'Importiert');
        }
        weiterleiten(route('legacy::objekte::index', ['objekte_raus' => 'import'], false));
        break;
}
function objekte_kurz()
{
    $db_abfrage = "SELECT OBJEKT_ID, OBJEKT_KURZNAME FROM OBJEKT WHERE OBJEKT_AKTUELL='1' ORDER BY OBJEKT_KURZNAME";
    $resultat = mysql_query($db_abfrage) or die (mysql_error());

    $numrows = mysql_numrows($resultat);
    if ($numrows > 0) {
        iframe_start();
        echo "<table class=\"sortable striped\">\n";
        echo "<tr><th>Objekt</th><th>FLÄCHE</th><th>HÄUSER</th><th>Einheiten</th><th>INFOS</th><th colspan=\"9\"></th></tr>";
        $counter = 0;
        while (list ($OBJEKT_ID, $OBJEKT_KURZNAME) = mysql_fetch_row($resultat)) {
            $anzahl_haeuser = anzahl_haeuser_im_objekt($OBJEKT_ID);
            $counter++;
            $flaeche = nummer_punkt2komma(objekt_flaeche($OBJEKT_ID));
            $detail_check = detail_check("OBJEKT", $OBJEKT_ID);
            if ($detail_check > 0) {
                $detail_link = "<a  href='" . route('legacy::details::index', ['option' => 'details_anzeigen', 'detail_tabelle' => 'OBJEKT', 'detail_id' => $OBJEKT_ID]) . "'>Details</a>";
            } else {
                $detail_link = "<a href='" . route('legacy::details::index', ['option' => 'details_hinzu', 'detail_tabelle' => 'OBJEKT', 'detail_id' => $OBJEKT_ID]) . "'>Neues Detail</a>";
            }
            $aendern_link = "<a href='" . route('legacy::objekte::index', ['objekte_raus' => 'objekt_aendern', 'objekt_id' => $OBJEKT_ID]) . "'>Ändern</a>";
            $haus_neu_link = "<a href='" . route('legacy::haeuserform::index', ['daten_rein' => 'haus_neu', 'objekt_id' => $OBJEKT_ID]) . ">Haus erstellen</a>";
            $check_liste_link = "<a href='" . route('legacy::objekte::index', ['objekte_raus' => 'checkliste', 'objekt_id' => $OBJEKT_ID]) . "'>Checkliste HW</a>";
            $mietaufstellung_link = "<a href='" . route('legacy::objekte::index', ['objekte_raus' => 'mietaufstellung', 'objekt_id' => $OBJEKT_ID]) . "'>Mietaufstellung</a>";
            $monat = date("m");
            $jahr = date("Y");
            $mietaufstellung_link_m_j = "<a href='" . route('legacy::objekte::index', ['objekte_raus' => 'mietaufstellung_m_j', 'objekt_id' => $OBJEKT_ID, 'monat' => $monat, 'jahr' => $jahr]) . "'>Mietaufstellung MJ</a>";
            $mietaufstellung_link_m_j_xls = "<a href='" . route('legacy::objekte::index', ['objekte_raus' => 'mietaufstellung_m_j', 'objekt_id' => $OBJEKT_ID, 'monat' => $monat, 'jahr' => $jahr, 'XLS']) . "'>Mietaufstellung MJ-XLS</a>";

            $alle_mietkontenblatt_link = "<a href='" . route('legacy::mietkontenblatt::index', ['anzeigen' => 'alle_mkb', 'objekt_id' => $OBJEKT_ID]) . "'>Alle MKB-PDF</a>";
            $link_mieterliste = "<a href='" . route('legacy::einheiten::index', ['einheit_raus' => 'mieterliste_aktuell', 'objekt_id' => $OBJEKT_ID]) . "'>Mieterliste PDF</a>";
            $link_mieteremail = "<a href='" . route('legacy::einheiten::index', ['einheit_raus' => 'mieteremail_aktuell', 'objekt_id' => $OBJEKT_ID]) . "'>Mieter-Email</a>";
            $link_stammdaten = "<a href='" . route('legacy::objekte::index', ['objekte_raus' => 'stammdaten_pdf', 'objekt_id' => $OBJEKT_ID]) . "'><img src=\"images/pdf_light.png\"></a>";
            $vorjahr = date("Y") - 1;
            $link_sollist = "<a href='" . route('legacy::objekte::index', ['objekte_raus' => 'mietaufstellung_j', 'objekt_id' => $OBJEKT_ID, 'jahr' => $vorjahr]) . "'>SOLL/IST $vorjahr</a>";
            echo "<tr class=\"zeile$counter\"><td>$OBJEKT_KURZNAME<br>$link_stammdaten</td><td>$flaeche m²</td><td sorttable_customkey=\"$anzahl_haeuser\"><a  href='" . route('legacy::haeuser::index', ['haus_raus' => 'haus_kurz', 'objekt_id' => $OBJEKT_ID]) . "'>Häuserliste (<b>$anzahl_haeuser</b>)</a>  $haus_neu_link</td><td><a href='" . route('legacy::einheiten::index', ['einheit_raus' => 'einheit_kurz', 'objekt_id' => $OBJEKT_ID]) . "'>Einheitenliste</a></td><td>$detail_link</td><td>$aendern_link</td><td>$check_liste_link</td><td>$mietaufstellung_link</td><td>$mietaufstellung_link_m_j</td><td>$mietaufstellung_link_m_j_xls</td><td>$alle_mietkontenblatt_link</td><td>$link_mieterliste</td><td>$link_mieteremail</td><td>$link_sollist</td></tr>";

            if ($counter == 2) {
                $counter = 0;
            }
        }
        echo "</table>";
        iframe_end();
    }
}

function objekt_flaeche($objekt_id)
{
    $db_abfrage = "SELECT SUM(HAUS_QM) AS SUMME FROM HAUS WHERE OBJEKT_ID='$objekt_id'";
    $resultat = mysql_query($db_abfrage) or die (mysql_error());
    while (list ($SUMME) = mysql_fetch_row($resultat))
        return $SUMME;
}

function objekt_wohnflaeche($objekt_id)
{
    // $sql = 'SELECT SUM(HAUS_QM) AS Summe FROM HAUS';
    $db_abfrage = "SELECT SUM(HAUS_QM) AS SUMME FROM HAUS WHERE OBJEKT_ID='$objekt_id'";
    $resultat = mysql_query($db_abfrage) or die (mysql_error());
    while (list ($SUMME) = mysql_fetch_row($resultat))
        return $SUMME;
}