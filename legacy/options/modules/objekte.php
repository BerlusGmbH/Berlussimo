<?php

$daten = request()->input('daten');
$objekte_raus = request()->input('objekte_raus');

switch ($objekte_raus) {

    case "checkliste" :
        if (request()->filled('objekt_id')) {
            $o = new objekt ();
            $o->pdf_checkliste(request()->input('objekt_id'));
        } else {
            echo "Objekt auswählen";
        }
        break;

    case "mietaufstellung" :
        if (request()->filled('objekt_id')) {
            $o = new objekt ();
            $o->pdf_mietaufstellung(request()->input('objekt_id'));
        } else {
            echo "Objekt auswählen";
        }
        break;

    case "mietaufstellung_m_j" :
        if (request()->filled('objekt_id')) {
            $objekt_id = request()->input('objekt_id');
            if (request()->filled('monat') && request()->filled('jahr')) {
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
        if (request()->filled('objekt_id')) {
            $objekt_id = request()->input('objekt_id');
            if (request()->filled('jahr')) {
                $jahr = request()->input('jahr');
                $o = new objekt ();
                $o->pdf_mietaufstellung_j($objekt_id, $jahr);
            } else {
                echo "Monat und Jahr wählen";
            }
        }
        break;

    case "stammdaten_pdf" :
        if (request()->filled('objekt_id')) {
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
}
function objekte_kurz()
{
    $db_abfrage = "SELECT id AS OBJEKT_ID, OBJEKT_KURZNAME FROM OBJEKT WHERE OBJEKT_AKTUELL='1' ORDER BY OBJEKT_KURZNAME";
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
