<?php

if (request()->has('daten')) {
    $daten = request()->input('daten');
}
if (request()->has('option')) {
    $option = request()->input('option');
}
if (request()->has('objekt_id')) {
    $objekt_id = request()->input('objekt_id');
}
if (request()->has('haus_id')) {
    $haus_id = request()->input('haus_id');
}

if (isset ($option)) {
    switch ($option) {

        case "objekt" :
            $bg = new berlussimo_global();
            $bg->objekt_auswahl_liste();
            if (session()->has('objekt_id')) {
                leerstand_objekt(session()->get('objekt_id'));
            }
            break;

        case "objekt_pdf" :
            if (session()->has('objekt_id')) {
                $objekt_id = session()->get('objekt_id');
                if (request()->has('monat')) {
                    $monat = request()->input('monat');
                } else {
                    $monat = date("m");
                }

                if (request()->has('jahr')) {
                    $jahr = request()->input('jahr');
                } else {
                    $jahr = date("Y");
                }
                $l = new leerstand ();
                $l->leerstand_objekt_pdf($objekt_id, $monat, $jahr);
            }
            break;

        default :
            break;

        case "projekt_pdf" :
            if (request()->has('einheit_id')) {
                $l = new leerstand ();
                $l->pdf_projekt(request()->input('einheit_id'));
            } else {
                echo "Einheit wählen";
            }
            break;

        case "besichtigung_pdf" :
            $einheit_id = request()->input('einheit_id');
            if ($einheit_id) {
                $l = new leerstand ();
                $l->mieterselbstauskunft_besichtigung_pdf($einheit_id);
            } else {
                fehlermeldung_ausgeben('Einheit wählen');
            }
            break;

        case "bewerbung_pdf" :
            $einheit_id = request()->input('einheit_id');
            if ($einheit_id) {
                $l = new leerstand ();
                $l->mieterselbstauskunft_bewerbung_pdf($einheit_id);
            } else {
                fehlermeldung_ausgeben('Einheit wählen');
            }
            break;

        case "sanierung" :
            $bg = new berlussimo_global();
            $bg->objekt_auswahl_liste();
            if (session()->has('objekt_id')) {
                $le = new leerstand ();
                $le->sanierungsliste(session()->get('objekt_id'), 11, 250, 200);
            }
            break;

        case "sanierung_wedding" :
            $le = new leerstand ();
            $le->sanierungsliste(1, 11, 250, 200); // BLOCK II
            $le->sanierungsliste(2, 11, 250, 200); // BLOCK III
            $le->sanierungsliste(3, 11, 250, 200); // BLOCK V

            break;

        case "vermietung_wedding" :
            $le = new leerstand ();
            $le->vermietungsliste(40, 11);
            echo "<br><br><hr><br><br>";
            $le->vermietungsliste(1, 11);
            echo "<br><br><hr><br><br>";
            $le->vermietungsliste(2, 11);
            echo "<br><br><hr><br><br>";
            $le->vermietungsliste(3, 11);

            break;

        case "vermietung" :
            $bg = new berlussimo_global();
            $bg->objekt_auswahl_liste();
            if (session()->has('objekt_id')) {
                $le = new leerstand ();
                $le->vermietungsliste(session()->get('objekt_id'), 11);
            }

            break;

        case "filter_setzen" :
            session()->forget('aktive_filter');

            if (request()->has('Zimmer')) {
                $anz = count(request()->input('Zimmer'));
                for ($a = 0; $a < $anz; $a++) {
                    $wert = request()->input('Zimmer')[$a];
                    session()->push('aktive_filter.zimmer', $wert);
                }
            }

            if (request()->has('Balkon')) {
                $anz = count(request()->input('Balkon'));
                for ($a = 0; $a < $anz; $a++) {
                    $wert = request()->input('Balkon')[$a];
                    session()->push('aktive_filter.balkon', $wert);
                }
            }

            if (request()->has('Heizung')) {
                $anz = count(request()->input('Heizung'));
                for ($a = 0; $a < $anz; $a++) {
                    $wert = request()->input('Heizung')[$a];
                    session()->push('aktive_filter.heizung', $wert);
                }
            }
            $url = parse_url(URL::previous());
            weiterleiten($url['path'].'?'.$url['query']);
            break;

        case "kontrolle_preise" :
            $l = new leerstand ();
            $l->kontrolle_preise();
            break;
    }
}
function leerstand_finden($objekt_id)
{
    $result = DB::select("SELECT OBJEKT_KURZNAME, EINHEIT_ID, EINHEIT_KURZNAME, HAUS_STRASSE, HAUS_NUMMER, EINHEIT_QM, EINHEIT_LAGE
FROM `EINHEIT`
RIGHT JOIN (
HAUS, OBJEKT
) ON ( EINHEIT.HAUS_ID = HAUS.HAUS_ID && HAUS.OBJEKT_ID = OBJEKT.OBJEKT_ID && OBJEKT.OBJEKT_ID='$objekt_id' )
WHERE EINHEIT_AKTUELL='1' && EINHEIT_ID NOT
IN (

SELECT EINHEIT_ID
FROM MIETVERTRAG
WHERE MIETVERTRAG_AKTUELL = '1' && ( MIETVERTRAG_BIS > CURdate( )
OR MIETVERTRAG_BIS = '0000-00-00' )
)
ORDER BY EINHEIT_KURZNAME ASC");
    return $result;
}

function leerstand_finden_monat($objekt_id, $datum)
{
    $result = DB::select("SELECT OBJEKT_KURZNAME, EINHEIT_ID, EINHEIT_KURZNAME, HAUS_STRASSE, HAUS_NUMMER, EINHEIT_QM, EINHEIT_LAGE, TYP
FROM `EINHEIT`
RIGHT JOIN (
HAUS, OBJEKT
) ON ( EINHEIT.HAUS_ID = HAUS.HAUS_ID && HAUS.OBJEKT_ID = OBJEKT.OBJEKT_ID && OBJEKT.OBJEKT_ID='$objekt_id' )
WHERE EINHEIT_AKTUELL='1' && EINHEIT_ID NOT
IN (

SELECT EINHEIT_ID
FROM MIETVERTRAG
WHERE MIETVERTRAG_AKTUELL = '1' && DATE_FORMAT( MIETVERTRAG_VON, '%Y-%m-%d' ) <= '$datum' && ( DATE_FORMAT( MIETVERTRAG_BIS, '%Y-%m-%d' ) >= '$datum'
OR MIETVERTRAG_BIS = '0000-00-00' )
)
GROUP BY EINHEIT_ID ORDER BY EINHEIT_KURZNAME ASC");
    return $result;
}

function leerstand_objekt($objekt_id)
{
    $form = new formular ();
    $form->erstelle_formular("Leerstände", NULL);
    $b = new berlussimo_global ();
    $link = route('web::leerstand::legacy', ['option' => 'objekt', 'objekt_id' => $objekt_id], false);

    if (request()->has('monat')) {
        $monat = request()->input('monat');
    } else {
        $monat = date("m");
    }
    if (request()->has('jahr')) {
        $jahr = request()->input('jahr');
    } else {
        $jahr = date("Y");
    }
    if ($monat && $jahr) {
        $l_tag = letzter_tag_im_monat($monat, $jahr);
        $datum = "$jahr-$monat-$l_tag";
    }

    $b->monate_jahres_links($jahr, $link);

    if (empty ($datum)) {
        $leerstand = leerstand_finden($objekt_id);
    } else {
        $leerstand = leerstand_finden_monat($objekt_id, $datum);
    }
    $monat_name = monat2name($monat);
    echo "<table class=\"sortable\">";
    $link_pdf = "<a href='" . route('web::leerstand::legacy', ['option' => 'objekt_pdf', 'objekt_id' => $objekt_id, 'monat' => $monat, 'jahr' => $jahr]) . "'>PDF-Ansicht</a>";
    echo "<tr><td colspan=\"6\">$link_pdf</td></tr>";
    echo "<tr><td colspan=\"6\">Leerstand $monat_name $jahr</td></tr>";
    echo "</table>";
    echo "<table class=\"sortable\">";
    echo "<tr><th>Objekt</th><th>Einheit</th><th>TYP</th><th>Lage</th><th>Fläche</th><th>Link</th><th>Anschrift</th><th>PDF</th></tr>";

    $anzahl_leer = count($leerstand);
    $summe_qm = 0;
    for ($a = 0; $a < $anzahl_leer; $a++) {
        $einheit_id = $leerstand [$a] ['EINHEIT_ID'];
        $lage = $leerstand [$a] ['EINHEIT_LAGE'];
        $qm = $leerstand [$a] ['EINHEIT_QM'];
        $typ = $leerstand [$a] ['TYP'];
        $link_einheit = "<a href='" . route('web::uebersicht::legacy', ['anzeigen' => 'einheit', 'einheit_id' => $einheit_id]) . "'>" . $leerstand [$a] ['EINHEIT_KURZNAME'] . "</a>";
        $link_projekt_pdf = "<a href='" . route('web::leerstand::legacy', ['option' => 'projekt_pdf', 'einheit_id' => $einheit_id]) . "'><img src=\"images/pdf_light.png\"></a>";
        $link_besichtigung_pdf = "<a href='" . route('web::leerstand::legacy', ['option' => 'besichtigung_pdf', 'einheit_id' => $einheit_id]) . "'><img src=\"images/pdf_dark.png\"></a>";
        echo "<tr><td>" . $leerstand [$a] ['OBJEKT_KURZNAME'] . "</td><td>$link_einheit</td><td>$typ</td><td>$lage</td><td>$qm m²</td><td><a href='" . route('web::mietvertraege::legacy', ['mietvertrag_raus' => 'mietvertrag_neu']) . "'>Vermieten</td></td><td>" . $leerstand [$a] ['HAUS_STRASSE'] . " " . $leerstand [$a] ['HAUS_NUMMER'] . "</td><td>$link_projekt_pdf Projekt<br>$link_besichtigung_pdf Expose</td></tr>";
        $summe_qm += $qm;
    }
    echo "<tr><td></td><td></td><td></td><td></td><td>$summe_qm m²</td><td></td><td></td><td></td></tr>";
    echo "</table>";
    $form->ende_formular();
}