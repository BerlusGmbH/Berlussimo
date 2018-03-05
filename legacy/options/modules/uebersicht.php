<?php

if (request()->has('einheit_id')) {
    $einheit_id = request()->input('einheit_id');
} else {
    echo "Bitte Einheit wählen.";
    return;
}

$anzeigen = request()->input('anzeigen');
switch ($anzeigen) {

    case "einheit" :
        $e = new einheit ();
        if (!empty($e->get_mietvertrag_ids($einheit_id))) {
            uebersicht_einheit($einheit_id);
        } else {
            echo "<h2>BISHER LEERSTAND</h2>";
            $e->uebersicht_einheit_leer($einheit_id);
        }
        break;

}

/* Neue Version zu Einheit oder Einheit und MV */
function uebersicht_einheit($einheit_id)
{
    if (request()->has('mietvertrag_id')) {
        $mietvertrag_id = request()->input('mietvertrag_id');
        $mv = new mietvertraege ();
        $mv->get_mietvertrag_infos_aktuell($mietvertrag_id);
        $einheit_id = $mv->einheit_id;
        $e = new einheit ();
        $e->get_einheit_info($einheit_id);
    } else {
        $e = new einheit ();
        $e->get_einheit_info($einheit_id);
        $mietvertrag_id = $e->get_last_mietvertrag_id($einheit_id);

        if (empty ($mietvertrag_id)) {
            echo 'Keine Informationen, weil keine Vormietverträge existieren.';
            return;
        }
        $mv = new mietvertraege ();
        $mv->get_mietvertrag_infos_aktuell($mietvertrag_id);
    }

    echo "<div class='fixed-action-btn'>
    <a href='" . route('web::todo::index', [
            'q' => '!auftrag(kostenträger(objekt(id='
                . $e->objekt_id . ') or haus(id='
                . $e->haus_id . ') or einheit(id='
                . $einheit_id . ')))[erstellt:desc]'
        ]) . "' target='_blank' class='btn-floating btn-large modal-trigger'>
      <i class='large mdi mdi-view-list'></i>
    </a>
  </div>";

    echo "<div id='terminate-contract' class='modal'>
    <div class='modal-content'>
      <h4>Vertrag beenden</h4>
      <p>Sind Sie sicher, dass Sie den Vertrag beenden möchten?</p>
    </div>
    <div class='modal-footer'>
      <a href='" . route('web::mietvertraege::legacy', ['mietvertrag_raus' => 'mietvertrag_beenden', 'mietvertrag_id' => $mietvertrag_id]) . "' class='modal-action modal-close waves-effect btn-flat white-text red'>Ja</a>
      <a href='#!' class='modal-action modal-close waves-effect btn-flat'>Nein</a>
    </div>
  </div>";

    // ################################## BALKEN EINHEIT---->

    $weg = new weg ();
    $weg->get_last_eigentuemer($einheit_id);
    if (isset ($weg->eigentuemer_id)) {
        $e_id = $weg->eigentuemer_id;
        $weg->get_eigentuemer_namen_str($e_id);
        $miteigentuemer_namen = "<b>WEG-ET</b>:<br>" . $weg->eigentuemer_name_str_u;

        /* ################Betreuer################## */
        $anz_p = count($weg->eigentuemer_person_ids);
        $betreuer_str = '';
        for ($be = 0; $be < $anz_p; $be++) {
            $et_p_id = $weg->eigentuemer_person_ids [$be];
            $d_k = new detail ();
            $dt_arr = $d_k->finde_alle_details_grup('Person', $et_p_id, 'INS-Kundenbetreuer');
            if (!empty($dt_arr)) {
                $anz_bet = count($dt_arr);
                for ($bet = 0; $bet < $anz_bet; $bet++) {
                    $bet_str = $dt_arr [$bet] ['DETAIL_INHALT'];
                    $betreuer_str .= "$bet_str<br>";
                    $betreuer_arr [] = $bet_str;
                }
            }
        }

        if (is_array($betreuer_arr)) {
            $betreuer_str = '';
            $betreuer_arr1 = array_unique($betreuer_arr);
            for ($bbb = 0; $bbb < count($betreuer_arr1); $bbb++) {
                $betreuer_str .= $betreuer_arr1 [$bbb];
            }
        }
    } else {
        $miteigentuemer_namen = "";
    }

    $details_info = new details ();
    $objekt_details_arr = $details_info->get_details('Objekt', $e->objekt_id);
    echo "<div class='yellow-page row'>";
    echo "<div class='col-xs-12 col-md-6 col-lg-3'>";
    echo "<div class='card'>";
    echo "<div class='card-content'>";
    echo "<div class='card-title'>Objekt: <a href='" . route('web::objekte.show', ['id' => $e->objekt_id]) . "'><b>$e->objekt_name</b></a></div>";
    echo "<div class='card-title' style='font-size: medium'>Haus: <a href='" . route('web::haeuser.show', ['id' => $e->haus_id]) . "'><b>$e->haus_strasse $e->haus_nummer</b></a></div>";
    for ($i = 0; $i < count($objekt_details_arr); $i++) {
        echo "<b>" . $objekt_details_arr [$i] ['DETAIL_NAME'] . "</b><br>" . $objekt_details_arr [$i] ['DETAIL_INHALT'] . "<br>";
    }
    $oo = new objekt ();
    $oo->get_objekt_infos($e->objekt_id);
    echo "<b>OBJEKT-ET</b>:<br>$oo->objekt_eigentuemer";
    $link_objekt_details = "<a href='" . route('web::details::legacy', ['option' => 'details_anzeigen', 'detail_tabelle' => 'Objekt', 'detail_id' => $e->objekt_id]) . "'>Detail hinzufügen</a>";
    echo "</div>";
    echo "<div class='card-action'>$link_objekt_details</div>";
    echo "</div>";
    echo "</div>";

    echo "<div class='col-xs-12 col-md-6 col-lg-3'>";
    echo "<div class='card'>";
    echo "<div class='card-content'>";
    echo "<div class='card-title'>Einheit: <a href='" . route('web::einheiten.show', ['id' => $einheit_id]) . "'><b>$e->einheit_kurzname</b></a></div>";
    echo "$miteigentuemer_namen";
    if (isset ($betreuer_str)) {
        echo "<b>Betreuer</b>:<br>$betreuer_str<br>";
    }
    echo "<b>Adresse</b>:<br>";
    echo "$e->haus_strasse $e->haus_nummer<br>";
    echo "$e->haus_plz $e->haus_stadt<br>";
    echo "<b>Lage</b>: $e->einheit_lage <b>QM</b>: $e->einheit_qm m² <b>TYP</b>: $e->typ<br>";
    $war = new wartung ();
    $war->wartungen_anzeigen($e->einheit_kurzname);

    $details_info = new details ();
    $einheit_details_arr = $details_info->get_details('Einheit', $einheit_id);
    if (count($einheit_details_arr) > 0) {
        for ($i = 0; $i < count($einheit_details_arr); $i++) {
            /* Expose bzw. Vermietungsdetails filtern */
            if (stripos($einheit_details_arr [$i] ['DETAIL_NAME'], 'Vermietung') === false) {
                if (stripos($einheit_details_arr [$i] ['DETAIL_NAME'], 'Expose') === false) {
                    echo "<b>" . $einheit_details_arr [$i] ['DETAIL_NAME'] . "</b>:<br>" . $einheit_details_arr [$i] ['DETAIL_INHALT'] . "<br>";
                }
            }
        }
    } else {
        echo "k.A zur Ausstattung";
    }
    $link_einheit_details = "<a href='" . route('web::details::legacy', ['option' => 'details_hinzu', 'detail_tabelle' => 'Einheit', 'detail_id' => $einheit_id]) . "'>Detail hinzufügen</a>";
    $link_einheit_alle_mietvertraege = "<a href='" . route('web::mietvertraege::legacy', ['mietvertrag_raus' => 'mietvertrag_kurz', 'einheit_id' => $einheit_id]) . "'>Alle Mietverträge</a>";
    echo "</div>";
    echo "<div class='card-action'>$link_einheit_details<br>$link_einheit_alle_mietvertraege</div>";
    echo "</div>";
    echo "</div>";

    // ######## balken 2 MIETER
    if ($mv->anzahl_personen < 1) {
        echo "leer";
    }
    // ####INFOS ÜBER PERSON/MIETER
    $person_info = new person ();
    echo "<div class='col-xs-12 col-md-4 col-lg-2'>";
    for ($i = 0; $i < $mv->anzahl_personen; $i++) {
        $person_info->get_person_infos($mv->personen_ids [$i] ['PERSON_MIETVERTRAG_PERSON_ID']);
        $akt_person_id = $mv->personen_ids [$i] ['PERSON_MIETVERTRAG_PERSON_ID'];
        $person_info->get_person_anzahl_mietvertraege_aktuell($mv->personen_ids [$i] ['PERSON_MIETVERTRAG_PERSON_ID']);
        $person_anzahl_mvs = $person_info->person_anzahl_mietvertraege;
        $person_nachname = $person_info->person_nachname;
        $person_vorname = $person_info->person_vorname;
        $person_geburtstag = $person_info->person_geburtstag;
        $person_mv_id_array = $person_info->get_vertrags_ids_von_person($mv->personen_ids [$i] ['PERSON_MIETVERTRAG_PERSON_ID']);
        $zeile = $i + 1;
        echo "<div class='card'>";
        echo "<div class='card-content'>";
        echo "<div class='card-title'>$zeile. Mieter</div>";
        $mieternamen_str = "<a href='" . route('web::personen.show', ['id' => $akt_person_id]) . "'><b>$person_nachname, $person_vorname</b></a>"
            . "<br>geb. am: " . date_mysql2german($person_geburtstag);
        $aktuelle_einheit_link = "";
        $alte_einheit_link = "";
        // ####DETAILS VOM MIETER
        $details_info_mieter = new details ();
        $mieter_details_arr = $details_info_mieter->get_details('Person', $mv->personen_ids [$i] ['PERSON_MIETVERTRAG_PERSON_ID']);
        $mieter_details = "";
        for ($p = 0; $p < count($mieter_details_arr); $p++) {
            $mieter_details .= "<b>" . $mieter_details_arr [$p] ['DETAIL_NAME'] . "</b><br>" . $mieter_details_arr [$p] ['DETAIL_INHALT'] . "<br>";
        }

        for ($a = 0; $a < count($person_mv_id_array); $a++) {
            $person_info2 = new person ();
            $mv_id = $person_mv_id_array [$a] ['PERSON_MIETVERTRAG_MIETVERTRAG_ID'];
            $mv_status = $person_info2->get_vertrags_status($mv_id);
            $mietvertrag_info2 = new mietvertrag ();
            $p_einheit_id = $mietvertrag_info2->get_einheit_id_von_mietvertrag($mv_id);
            $p_einheit_kurzname = $mietvertrag_info2->einheit_kurzname;

            if ($mv_status == TRUE) {
                $aktuelle_einheit_link .= "<a href='" . route('web::uebersicht::legacy', ['anzeigen' => 'einheit', 'einheit_id' => $p_einheit_id]) . "'>MV-$mv_id</a>"
                    . " (<a href='" . route('web::einheiten.show', ['id' => $p_einheit_id]) . "'><b>$p_einheit_kurzname</b></a>)&nbsp;";
            } else {
                $alte_einheit_link .= "<a href='" . route('web::uebersicht::legacy', ['anzeigen' => 'einheit', 'einheit_id' => $p_einheit_id]) . "'>MV-$mv_id</a>"
                    . " (<a href='" . route('web::einheiten.show', ['id' => $p_einheit_id]) . "'><b>$p_einheit_kurzname</b></a>)&nbsp;";
            }
        }
        echo "$mieternamen_str";
        if (!empty ($mieter_details)) {
            echo "<br>$mieter_details";
        }
        echo "<b>Verträge</b>: $person_anzahl_mvs<br>";
        echo "<b>Aktuelle Verträge</b>:<br>";
        echo "$aktuelle_einheit_link<br>";
        if (!empty ($alte_einheit_link)) {
            echo "<b>Alte Verträge</b>:<br>";
            echo "$alte_einheit_link<br>";
        }
        echo "</div>";
        $link_person_details = "<a href='" . route('web::details::legacy', ['option' => 'details_hinzu', 'detail_tabelle' => 'Person', 'detail_id' => $akt_person_id]) . "'>Detail hinzufügen</a>";
        echo "<div class='card-action'>$link_person_details</div>";
        echo "</div>";
    }

    // ######### LETZTER MIETER#########
    echo "<div class='card'>";
    echo "<div class='card-content'>";
    echo "<div class='card-title'>Vormieter</div>";
    $vormieter_ids_array = $e->letzter_vormieter($einheit_id);
    if (!empty ($vormieter_ids_array)) {
        for ($b = 0; $b < count($vormieter_ids_array); $b++) {
            $person_info->get_person_infos($vormieter_ids_array [$b] ['PERSON_MIETVERTRAG_PERSON_ID']);
            $person_nachname = $person_info->person_nachname;
            $person_vorname = $person_info->person_vorname;
            echo "$person_nachname $person_vorname<br>";
        }
    } else {
        echo "Keine Vormieter";
    }
    echo "</div>";
    echo "</div>";
    echo "</div>";

    // ###DETAILS ZUM VERTRAG
    $mv_details_info = new details ();
    $mv_details_arr = $mv_details_info->get_details('Mietvertrag', $mietvertrag_id);

    echo "<div class='col-xs-12 col-md-4 col-lg-2'>";
    echo "<div class='card'>";
    echo "<div class='card-content'>";
    echo "<div class='card-title'>Mietvertrag</div>";
    if (!empty($mv->mietvertrag_von)) {
        $mietvertrag_von_datum = date_mysql2german($mv->mietvertrag_von);
        echo "EINZUG: <b>$mietvertrag_von_datum</b><br>";
    }

    $link_vertrag_beenden = "";

    if (!empty($mv->mietvertrag_bis)) {
        $mietvertrag_bis_datum = date_mysql2german($mv->mietvertrag_bis);
        if ($mietvertrag_bis_datum == '00.00.0000') {
            echo "AUSZUG: <b>ungekündigt</b><br>";
            $link_vertrag_beenden =  "<a class='modal-trigger red-text' href='#terminate-contract'>Vertrag Beenden</a><br>";
        } else {
            echo "<p>AUSZUG: <span class='red-text'><b>$mietvertrag_bis_datum</b></span></p>";
        }
    }
    for ($i = 0; $i < count($mv_details_arr); $i++) {
        echo "<b>" . $mv_details_arr [$i] ['DETAIL_NAME'] . "</b>:<br>" . $mv_details_arr [$i] ['DETAIL_INHALT'] . "<br>";
    }
    $link_mv_details = "<a href='" . route('web::details::legacy', ['option' => 'details_hinzu', 'detail_tabelle' => 'Mietvertrag', 'detail_id' => $mietvertrag_id]) . "'>Detail hinzufügen</a>";
    echo "</div>";
    echo "<div class='card-action'>$link_mv_details<br>$link_vertrag_beenden</div>";
    echo "</div>";

    if (!empty ($mv->mietvertrag_bis)) {
        echo "<div class='card'>";
        echo "<div class='card-content'>";
        echo "<div class='card-title'>SEPA</div>";
        $sep = new sepa ();
        $m_ref = 'MV' . $mietvertrag_id;
        if ($sep->check_m_ref_alle($m_ref)) {
            $sep->get_mandat_infos_mref($m_ref);
            $d_heute = date("Ymd");
            $enddatum_mandat = str_replace('-', '', $sep->mand->M_EDATUM);
            if ($enddatum_mandat >= $d_heute) {
                //echo "<hr><p style=\"color:green;\"><b>Gültiges SEPA-Mandat</b><br>";
                $konto_inh = $sep->mand->NAME;
                echo "<b>Kto-Inhaber:</b> $konto_inh<br>";
                $iban = $iban_1 = chunk_split($sep->mand->IBAN, 4, ' ');
                $bic = $sep->mand->BIC;
                echo "<b>IBAN:</b> $iban<br>";
                echo "<b>BIC:</b> $bic<br>";
                $u_datum = date_mysql2german($sep->mand->M_UDATUM);
                $a_datum = date_mysql2german($sep->mand->M_ADATUM);
                $e_datum = date_mysql2german($sep->mand->M_EDATUM);
                echo "<b>Unterschrieben:</b> $u_datum<br>";
                echo "<b>Gültig ab:</b>      $a_datum<br>";
                echo "<b>Gültig bis:</b>     $e_datum<br>";
                $m_ein_art = $sep->mand->EINZUGSART;
                echo "<b>Einzugsart:</b>$m_ein_art<br>";
                echo "</p>";
            } else {
                $m_ende = date_mysql2german($sep->mand->M_EDATUM);
                echo "<p class=\"warnung\">SEPA-Mandat abgelaufen am $m_ende</p>";
            }
        } else {
            echo "Keine SEPA-Mandate";
        }
        echo "</div>";
        echo "</div>";
    }

    echo "</div>";

    echo "<div class='col-xs-12 col-md-4 col-lg-2'>";
    echo "<div class='card'>";
    echo "<div class='card-content'>";
    echo "<div class='card-title'>Miete</div>";

    $buchung = new mietkonto ();
    $monat = date("m");
    $jahr = date("Y");
    echo "<b>Mietdefinition</b><br>";
    $forderungen_arr = $buchung->aktuelle_forderungen_array($mietvertrag_id);
    for ($i = 0; $i < count($forderungen_arr); $i++) {
        echo $forderungen_arr [$i] ['KOSTENKATEGORIE'] . ":<br>" . $forderungen_arr [$i] ['BETRAG'] . " €<br>";
    }
    $summe_forderungen_aktuell = $buchung->summe_forderung_monatlich($mietvertrag_id, $monat, $jahr);
    $summe_forderungen_aktuell = explode("|", $summe_forderungen_aktuell)[0];
    echo "<b>Summe Forderungen</b>:<br>" . $summe_forderungen_aktuell . " €<br>";
    $summe_zahlungen = $buchung->summe_zahlung_monatlich($mietvertrag_id, $buchung->monat_heute, $buchung->jahr_heute);
    echo "<b>Summe Zahlungen</b>:<br>" . $summe_zahlungen . " €<br>";

    $a = new miete ();
    $a->mietkonto_berechnung($mietvertrag_id);
    echo "<b>Saldo</b>:<br>" . $a->erg . " €";

    echo "</div>";
    if (!empty ($mietvertrag_id)) {
        $link_mietkonto = "<a href='" . route('web::mietkontenblatt::legacy', ['anzeigen' => 'mk_pdf', 'mietvertrag_id' => $mietvertrag_id]) . "'>Mietkonto</a>";
        $link_mietkonto_ab = "<a href='" . route('web::mietkontenblatt::legacy', ['anzeigen' => 'mietkonto_ab', 'mietvertrag_id' => $mietvertrag_id]) . "'>Mietkonto ab</a>";
    }
    echo "<div class='card-action'>$link_mietkonto<br>$link_mietkonto_ab</div>";
    echo "</div>";

    $k = new kautionen ();
    if ($k->get_sollkaution($mietvertrag_id) !== "") {
        $soll_kaution = $k->get_sollkaution($mietvertrag_id);
    } else {
        $soll_kaution = nummer_punkt2komma(3 * $k->summe_mietekalt($mietvertrag_id));
    }

    echo "<div class='card'>";
    echo "<div class='card-content'>";
    echo "<div class='card-title'>Kaution</div>";
    echo "Soll: $soll_kaution €<br>";
    $k->kautionen_info('Mietvertrag', $mietvertrag_id, '13');
    if ($k->anzahl_zahlungen >= 1) {
        echo "<b>Kautionsbuchungen: ($k->anzahl_zahlungen)</b><br>";
        $buchung_zeile = 0;
        for ($a = 0; $a < $k->anzahl_zahlungen; $a++) {
            $buchung_zeile++;
            $datum = date_mysql2german($k->kautionszahlungen_array [$a] ['DATUM']);
            $betrag = nummer_punkt2komma($k->kautionszahlungen_array [$a] ['BETRAG']);
            $vzweck = $k->kautionszahlungen_array [$a] ['VERWENDUNGSZWECK'];
            echo "$buchung_zeile. $datum $betrag € $vzweck<br>";
        }
    } else {
        echo "Keine Kautionsbuchungen vorhanden";
    }
    echo "</div>";
    $link_kaution_buchen = "<a href='" . route('web::kautionen::legacy', ['option' => 'kautionen_buchen', 'mietvertrag_id' => $mietvertrag_id]) . "'>Buchen</a>";
    $link_kaution_hochrechnen = "<a href='" . route('web::kautionen::legacy', ['option' => 'hochrechner', 'mietvertrag_id' => $mietvertrag_id]) . "'>Hochrechnen</a>";
    echo "<div class='card-action'>$link_kaution_buchen<br>$link_kaution_hochrechnen</div>";

    echo "</div>";
    echo "</div>";
    echo "</div>";
}