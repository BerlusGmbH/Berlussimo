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
                weiterleiten(route('web::objekte::legacy', ['objekte_raus' => 'objekt_kurz'], false));
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
                weiterleiten(route('web::objekte::legacy', ['objekte_raus' => 'objekt_kurz'], false));
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
            $me->me_speichern('Mietvertrag', $mv_id, 'Miete kalt', $von, $bis, $km, '0.00');
        }
        if (request()->has('nk')) {
            $nk = nummer_komma2punkt(request()->input('nk'));
            $me->me_speichern('Mietvertrag', $mv_id, 'Nebenkosten Vorauszahlung', $von, $bis, $nk, '0.00');
        }
        if (request()->has('hk')) {
            $hk = nummer_komma2punkt(request()->input('hk'));
            $me->me_speichern('Mietvertrag', $mv_id, 'Heizkosten Vorauszahlung', $von, $bis, $hk, '0.00');
        }

        if (request()->has('kabel_tv')) {
            $kabel_tv = nummer_komma2punkt(request()->input('kabel_tv'));
            $me->me_speichern('Mietvertrag', $mv_id, 'Kabel TV', $von, $bis, $kabel_tv, '0.00');
        }
        $jahr_3 = date("Y") - 3;
        $m_day = date("m-d");
        $datum_3 = "$jahr_3-$m_day";
        if (request()->has('km_3')) {
            $km_3 = nummer_komma2punkt(request()->input('km_3'));
            $me->me_speichern('Mietvertrag', $mv_id, 'Miete kalt', $datum_3, $datum_3, $kabel_tv, '0.00');
        }

        if (request()->has('kaution')) {
            $d = new detail ();
            $d->detail_speichern_2('Mietvertrag', $mv_id, 'Kautionshinweis', request()->input('kaution'), 'Importiert');
        }

        if (request()->has('klein_rep')) {
            $d = new detail ();
            $d->detail_speichern_2('Mietvertrag', $mv_id, 'Kleinreparaturen', request()->input('klein_rep'), 'Importiert');
        }

        if (request()->has('zusatzinfo')) {
            $d = new detail ();
            $d->detail_speichern_2('Mietvertrag', $mv_id, 'Zusatzinfo', request()->input('zusatzinfo'), 'Importiert');
        }
        weiterleiten(route('web::objekte::legacy', ['objekte_raus' => 'import'], false));
        break;
}
function objekte_kurz()
{
    $db_abfrage = "SELECT OBJEKT_ID, OBJEKT_KURZNAME FROM OBJEKT WHERE OBJEKT_AKTUELL='1' ORDER BY OBJEKT_KURZNAME";
    $result = DB::select($db_abfrage);

    if (!empty($result)) {
        iframe_start();
        echo "<table class=\"sortable striped\">\n";
        echo "<tr><th>Objekt</th><th>Fläche</th><th>Häuser</th><th>Einheiten</th><th>Infos</th><th colspan=\"9\"></th></tr>";
        $counter = 0;
        foreach($result as $row) {
            $anzahl_haeuser = anzahl_haeuser_im_objekt($row['OBJEKT_ID']);
            $anzahl_einheiten = anzahl_einheiten_im_objekt($row['OBJEKT_ID']);
            $counter++;
            $flaeche = nummer_punkt2komma(objekt_flaeche($row['OBJEKT_ID']));
            $detail_check = detail_check("Objekt", $row['OBJEKT_ID']);
            if ($detail_check > 0) {
                $detail_link = "<a  href='" . route('web::details::legacy', ['option' => 'details_anzeigen', 'detail_tabelle' => 'Objekt', 'detail_id' => $row['OBJEKT_ID']]) . "'>Details</a>";
            } else {
                $detail_link = "<a href='" . route('web::details::legacy', ['option' => 'details_hinzu', 'detail_tabelle' => 'Objekt', 'detail_id' => $row['OBJEKT_ID']]) . "'>Neues Detail</a>";
            }
            $aendern_link = "<a href='" . route('web::objekte::legacy', ['objekte_raus' => 'objekt_aendern', 'objekt_id' => $row['OBJEKT_ID']]) . "'>Ändern</a>";
            $check_liste_link = "<a href='" . route('web::objekte::legacy', ['objekte_raus' => 'checkliste', 'objekt_id' => $row['OBJEKT_ID']]) . "'>Checkliste HW</a>";
            $mietaufstellung_link = "<a href='" . route('web::objekte::legacy', ['objekte_raus' => 'mietaufstellung', 'objekt_id' => $row['OBJEKT_ID']]) . "'>Mietaufstellung</a>";
            $monat = date("m");
            $jahr = date("Y");
            $mietaufstellung_link_m_j = "<a href='" . route('web::objekte::legacy', ['objekte_raus' => 'mietaufstellung_m_j', 'objekt_id' => $row['OBJEKT_ID'], 'monat' => $monat, 'jahr' => $jahr]) . "'>Mietaufstellung MJ</a>";
            $mietaufstellung_link_m_j_xls = "<a href='" . route('web::objekte::legacy', ['objekte_raus' => 'mietaufstellung_m_j', 'objekt_id' => $row['OBJEKT_ID'], 'monat' => $monat, 'jahr' => $jahr, 'XLS']) . "'>Mietaufstellung MJ-XLS</a>";

            $alle_mietkontenblatt_link = "<a href='" . route('web::mietkontenblatt::legacy', ['anzeigen' => 'alle_mkb', 'objekt_id' => $row['OBJEKT_ID']]) . "'>Alle MKB-PDF</a>";
            $link_mieterliste = "<a href='" . route('web::einheiten::legacy', ['einheit_raus' => 'mieterliste_aktuell', 'objekt_id' => $row['OBJEKT_ID']]) . "'>Mieterliste PDF</a>";
            $link_mieteremail = "<a href='" . route('web::einheiten::legacy', ['einheit_raus' => 'mieteremail_aktuell', 'objekt_id' => $row['OBJEKT_ID']]) . "'>Mieter-Email</a>";
            $link_stammdaten = "<a href='" . route('web::objekte::legacy', ['objekte_raus' => 'stammdaten_pdf', 'objekt_id' => $row['OBJEKT_ID']]) . "'><img src=\"images/pdf_light.png\"></a>";
            $vorjahr = date("Y") - 1;
            $link_sollist = "<a href='" . route('web::objekte::legacy', ['objekte_raus' => 'mietaufstellung_j', 'objekt_id' => $row['OBJEKT_ID'], 'jahr' => $vorjahr]) . "'>SOLL/IST $vorjahr</a>";
            echo "<tr class=\"zeile$counter\">
                    <td>$row[OBJEKT_KURZNAME] $link_stammdaten</td>
                    <td>$flaeche m²</td>
                    <td sorttable_customkey=\"$anzahl_haeuser\"><a  href='" . route('web::haeuser::legacy', ['haus_raus' => 'haus_kurz', 'objekt_id' => $row['OBJEKT_ID']]) . "'>Häuserliste&nbsp;($anzahl_haeuser)</a></td>
                    <td><a href='" . route('web::einheiten::legacy', ['einheit_raus' => 'einheit_kurz', 'objekt_id' => $row['OBJEKT_ID']]) . "'>Einheitenliste ($anzahl_einheiten)</a></td>
                    <td>$detail_link</td><td>$aendern_link</td>
                    <td>$check_liste_link</td>
                    <td>$mietaufstellung_link</td>
                    <td>$mietaufstellung_link_m_j</td>
                    <td>$mietaufstellung_link_m_j_xls</td>
                    <td>$alle_mietkontenblatt_link</td>
                    <td>$link_mieterliste</td>
                    <td>$link_mieteremail</td>
                    <td>$link_sollist</td>
                  </tr>";

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
    $result = DB::select($db_abfrage);
    foreach($result as $row)
        return $row['SUMME'];
}

function objekt_wohnflaeche($objekt_id)
{
    $db_abfrage = "SELECT SUM(HAUS_QM) AS SUMME FROM HAUS WHERE OBJEKT_ID='$objekt_id'";
    $result = DB::select($db_abfrage);
    foreach($result as $row)
        return $row['SUMME'];
}