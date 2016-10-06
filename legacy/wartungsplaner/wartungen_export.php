<?php

require("classes/arr_multisort.class.php");

function wartungen($gruppen_id, $monate_plus_int)
{
    $db_abfrage = "SELECT (SELECT PARTNER_NAME FROM PARTNER_LIEFERANT WHERE PARTNER_ID=W_GERAETE.KOSTENTRAEGER_ID && AKTUELL='1' ORDER BY PARTNER_DAT DESC LIMIT 0,1) AS PART, `GERAETE_ID`, LAGE_RAUM AS EINBAUORT, HERSTELLER, BEZEICHNUNG, KOSTENTRAEGER_TYP, KOSTENTRAEGER_ID, `INTERVAL_M`, DATE_FORMAT(NOW(),'%Y-%m-%d') as HEUTE, DATE_FORMAT(DATE_ADD(DATE_FORMAT(NOW(),'%Y-%m-%d'), INTERVAL -INTERVAL_M MONTH),'%Y-%m-%d') AS L_WART_FAELLIG, (SELECT DATUM FROM GEO_TERMINE WHERE GERAETE_ID=W_GERAETE.GERAETE_ID && AKTUELL='1' ORDER BY DATUM DESC LIMIT 0,1) AS L_WART FROM `W_GERAETE` WHERE `AKTUELL`='1' && GRUPPE_ID='$gruppen_id' && `KOSTENTRAEGER_TYP`='Partner' && (`KOSTENTRAEGER_ID`='3' or `KOSTENTRAEGER_ID`='947' or `KOSTENTRAEGER_ID`='986' or `KOSTENTRAEGER_ID`='661' or `KOSTENTRAEGER_ID`='1148' or `KOSTENTRAEGER_ID`='974') &&
GERAETE_ID NOT IN
(SELECT GERAETE_ID FROM GEO_TERMINE WHERE AKTUELL='1' && (DATUM>=DATE_FORMAT(DATE_ADD(DATE_FORMAT(NOW(),'%Y-%m-%d'), INTERVAL -(INTERVAL_M-$monate_plus_int) MONTH),'%Y-%m-%d') AND DATUM <= DATE_FORMAT(NOW(),'%Y-%m-%d')) )
AND
GERAETE_ID NOT IN
(SELECT GERAETE_ID FROM GEO_TERMINE WHERE AKTUELL='1' && DATUM>DATE_FORMAT(NOW(),'%Y-%m-%d') GROUP BY GERAETE_ID) ORDER BY EINBAUORT ASC";
    $result = DB::select($db_abfrage);
    return $result;
}


function ausgabe($gruppen_id, $monate_plus_int, $format = 'tab')
{
    $monat = date("m");
    $jahr = date("Y");


    $thermen_arr = wartungen($gruppen_id, $monate_plus_int);
    if (is_array($thermen_arr)) {
        $anz = count($thermen_arr);
        for ($a = 0; $a < $anz; $a++) {
            $einheit_kn = ltrim(rtrim($thermen_arr[$a]['EINBAUORT']));
            $e = new einheit;
            $e->get_einheit_id($einheit_kn);
            $e->get_einheit_info($e->einheit_id);
            $thermen_arr[$a]['STR'] = "$e->haus_strasse $e->haus_nummer, $e->haus_plz $e->haus_stadt";
            $thermen_arr[$a]['LAGE'] = $e->einheit_lage;

            $mv_id = $e->get_mietvertraege_zu($e->einheit_id, $jahr, $monat, 'DESC'); // OK

            if ($mv_id) {
                $mvs = new mietvertraege();
                $mvs->get_mietvertrag_infos_aktuell($mv_id);
                $thermen_arr[$a]['KONTAKT'] = $e->kontaktdaten_mieter($mv_id);
                $thermen_arr[$a]['MIETER'] = $mvs->personen_name_string_u;
                $kontaktdaten = '';
            } else {
                $thermen_arr[$a]['KONTAKT'] = 'Hausverwaltung!!';
                $thermen_arr[$a]['MIETER'] = 'Leerstand';
            }
            unset($mv_id);
            unset($e);


        }//end for
    } else {
        echo "KEINE WARTUNGEN";
        die();
    }

    if ($format == 'PDF') {
        ob_clean();
        $pdf = new Cezpdf('a4', 'portrait');
        $bpdf = new b_pdf;
        $bpdf->b_header($pdf, 'Partner', session()->get('partner_id'), 'portrait', 'Helvetica.afm', 6);

        $anz = count($thermen_arr);

        for ($a = 0; $a < $anz; $a++) {
            $einbauort = $thermen_arr[$a]['EINBAUORT'];
            $pdf->eztext("$einbauort\n", 12);
        }

        $pdf->ezStream();
    }

}

ausgabe(1, 3, 'PDF');