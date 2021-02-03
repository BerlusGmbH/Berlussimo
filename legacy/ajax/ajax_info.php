<?php

require __DIR__.'/../../public/index.php';

$option = request()->input('option');
/* Optionsschalter */
switch ($option) {

    case "wb_hinzufuegen" :
        $beleg_id = request()->input('beleg_id');
        $pos = request()->input('pos');
        $rr = new rechnungen ();
        $pos_info = $rr->get_position($beleg_id, $pos);
        $art_nr = $pos_info ['ARTIKEL_NR'];
        $menge = $pos_info ['MENGE'];
        $wb = new werkzeug ();
        $menge_erl = $wb->get_anzahl_werkzeuge($art_nr, $beleg_id);
        $menge_hinzu = $menge - $menge_erl;
        for ($a = 0; $a < $menge_hinzu; $a++) {
            $l_id = last_id2('WERKZEUGE', 'ID') + 1;
            DB::insert("INSERT INTO WERKZEUGE VALUES(NULL, ?, ?, ?, ?, '1', '', NULL, NULL, '1')", [$l_id, $beleg_id, $pos, $art_nr]);
        }

        break;

    case "update_rechnung_rabatt" :
        $rabatt = nummer_komma2punkt(request()->input('prozent'));
        $belegnr = request()->input('belegnr');
        if (empty ($rabatt) or empty ($belegnr)) {
            //Kein Beleg oder Rabattprozente
            return;
        }
        $rr = new rechnungen ();
        $pos_arr = $rr->rechnungs_positionen_arr($belegnr);
        if (!empty($pos_arr)) {
            $anz = $rr->anzahl_positionen;
            for ($a = 0; $a < $anz; $a++) {
                $pos = $pos_arr [$a] ['POSITION'];
                $preis = $pos_arr [$a] ['PREIS'];
                $menge = $pos_arr [$a] ['MENGE'];
                $gpreis = ($menge * $preis / 100) * (100 - $rabatt);

                /* Update Rechnung Positionen */
                $rabatt = nummer_punkt2komma($rabatt);
                DB::update("UPDATE RECHNUNGEN_POSITIONEN SET GESAMT_NETTO=?, RABATT_SATZ=? WHERE POSITION=? && BELEG_NR=? && AKTUELL='1'", [$gpreis, $rabatt, $pos, $belegnr]);
            }
        } else {
            echo "error:Keine Position verändert, da keine Pos im Beleg vorhanden!";
        }

        break;

    case "update_rechnung_skonti" :
        $skonto = request()->input('prozent');
        $belegnr = request()->input('belegnr');
        if (empty ($skonto) or empty ($belegnr)) {
            //Kein Beleg oder Skontiprozente
            return;
        }
        $rr = new rechnungen ();
        $pos_arr = $rr->rechnungs_positionen_arr($belegnr);
        if (!empty($pos_arr)) {
            $anz = $rr->anzahl_positionen;
            for ($a = 0; $a < $anz; $a++) {
                $pos = $pos_arr [$a] ['POSITION'];
                $preis = $pos_arr [$a] ['PREIS'];
                $menge = $pos_arr [$a] ['MENGE'];
                $rabatt = $pos_arr [$a] ['RABATT_SATZ'];
                $gpreis = number_format(($menge * $preis / 100) * (100 - $rabatt), 2);

                /* Update Rechnung Positionen */
                DB::update("UPDATE RECHNUNGEN_POSITIONEN SET GESAMT_NETTO=?, SKONTO=? WHERE POSITION=? && BELEG_NR=? && AKTUELL='1'", [$gpreis, $skonto, $pos, $belegnr]);
            }
        } else {
            echo "error:Keine Position verändert, da keine Pos im Beleg vorhanden!";
        }

        break;

    case "register_var" :
        $key = request()->input('var');
        $value = request()->input('value');
        session()->put($key, $value);
        break;

    case "kostenkonto" :
        $konto_id = request()->input('konto_id');
        $result = DB::select("SELECT KONTENRAHMEN_KONTEN.BEZEICHNUNG, KONTENRAHMEN_GRUPPEN.BEZEICHNUNG AS GRUPPE, KONTENRAHMEN_KONTOARTEN.KONTOART
FROM KONTENRAHMEN_KONTEN
RIGHT JOIN (
KONTENRAHMEN_GRUPPEN, KONTENRAHMEN_KONTOARTEN
) ON ( KONTENRAHMEN_KONTEN.GRUPPE = KONTENRAHMEN_GRUPPEN.KONTENRAHMEN_GRUPPEN_ID && KONTENRAHMEN_KONTOARTEN.KONTENRAHMEN_KONTOART_ID = KONTENRAHMEN_KONTEN.KONTO_ART )
WHERE KONTO = ?
ORDER BY KONTENRAHMEN_KONTEN_DAT DESC
LIMIT 0 , 1", [$konto_id]);

        foreach ($result as $row)
            echo $row['BEZEICHNUNG'] . "|" . $row['GRUPPE'] . "|" . $row['KONTOART'];
        break;

    case "finde_partner" :
        if (request()->has('suchstring')) {
            $suchstring = request()->input('suchstring');
            if (strlen($suchstring) > 2) {
                $result = DB::select("SELECT PARTNER_NAME, PARTNER_ID, STRASSE, NUMMER, PLZ, ORT, LAND FROM PARTNER_LIEFERANT WHERE AKTUELL='1' && PARTNER_NAME LIKE ? ORDER BY PARTNER_NAME ASC", ['%' . $suchstring . '%']);
                if (!empty($result)) {
                    echo "<h5>Bereits vorhandene ähnliche Einträge.</h5>";
                    echo "<table class='striped'>";
                    foreach ($result as $row) {
                        $PARTNER_NAME1 = str_replace('<br>', ' ', $row['PARTNER_NAME']);
                        echo "<tr><td>$PARTNER_NAME1</td><td>$row[STRASSE]</td><td>$row[NUMMER]</td><td>$row[PLZ]</td><td>$row[ORT]</td></tr>";
                    }
                    echo "</table>";
                }
            }
        }
        break;

    case "list_kostentraeger" :
        $typ = request()->input('typ');
        if ($typ == 'Objekt') {
            if (!session()->has('geldkonto_id')) {
                $result = DB::select("SELECT OBJEKT_KURZNAME, OBJEKT_ID FROM OBJEKT WHERE OBJEKT_AKTUELL='1' ORDER BY OBJEKT_KURZNAME ASC");
                foreach ($result as $row) {
                    echo "$row[OBJEKT_KURZNAME]*$row[OBJEKT_ID]*|";
                }
            } else {
                $result = DB::select("SELECT OBJEKT_KURZNAME, OBJEKT_ID FROM OBJEKT WHERE OBJEKT_AKTUELL='1' ORDER BY OBJEKT_KURZNAME ASC");
                foreach ($result as $row) {
                    $gk = new gk ();
                    if ($gk->check_zuweisung_kos_typ(session()->get('geldkonto_id'), 'Objekt', $row['OBJEKT_ID'])) {
                        echo "$row[OBJEKT_KURZNAME]*$row[OBJEKT_ID]*|";
                    }
                }
            }
        }

        if ($typ == 'Wirtschaftseinheit') {
            $result = DB::select("SELECT LTRIM(RTRIM(W_NAME)) AS W_NAME FROM WIRT_EINHEITEN WHERE AKTUELL='1' ORDER BY W_NAME ASC");
            foreach ($result as $row) {
                echo "$row[W_NAME]|";
            }
        }

        if ($typ == 'Haus') {
            $haeuser = \App\Models\Haeuser::with('objekt')->defaultOrder();

            if (session()->has('geldkonto_id')) {
                $haeuser->whereHas('objekt.bankkonten', function ($query) {
                    //todo check if fixed 'GELD_KONTEN.KONTO_ID' <-> 'KONTO_ID'
                    $query->where('GELD_KONTEN.KONTO_ID', session()->get('geldkonto_id'));
                });
            }

            $haeuser = $haeuser->get();
            foreach ($haeuser as $haus) {
                echo trim($haus->HAUS_STRASSE) . " " . trim($haus->HAUS_NUMMER) . "*$haus->HAUS_ID*" . $haus->objekt->OBJEKT_KURZNAME . "|";
            }
        }

        if ($typ == 'Einheit') {
            $result = DB::select("SELECT EINHEIT_ID, EINHEIT_KURZNAME, HAUS.OBJEKT_ID AS OBJEKT_ID
FROM  `EINHEIT` 
RIGHT JOIN (
HAUS, OBJEKT
) ON ( EINHEIT.HAUS_ID = HAUS.HAUS_ID && HAUS.OBJEKT_ID = OBJEKT.OBJEKT_ID ) 
WHERE EINHEIT_AKTUELL =  '1'
GROUP BY EINHEIT_ID
ORDER BY LPAD( EINHEIT_KURZNAME, LENGTH( EINHEIT_KURZNAME ) ,  '1' ) ASC ");

            foreach ($result as $row)
                if (!session()->has('geldkonto_id')) {
                    echo "$row[EINHEIT_KURZNAME]*$row[EINHEIT_ID]*|";
                } else {
                    $gk = new gk ();
                    if ($gk->check_zuweisung_kos_typ(session()->get('geldkonto_id'), 'Objekt', $row['OBJEKT_ID'])) {
                        echo "$row[EINHEIT_KURZNAME]*$row[EINHEIT_ID]*|";
                    }
                }
        }

        if ($typ == 'Partner') {
            $result = DB::select("SELECT PARTNER_NAME, PARTNER_ID FROM PARTNER_LIEFERANT WHERE AKTUELL='1' ORDER BY PARTNER_NAME ASC");
            foreach ($result as $row) {
                $PARTNER_NAME1 = str_replace('<br>', ' ', $row['PARTNER_NAME']);
                echo "$PARTNER_NAME1*$row[PARTNER_ID]*|";
            }
        }

        if ($typ == 'Mietvertrag') {
            $einheiten = \App\Models\Einheiten::defaultOrder()
                ->has('mietvertraege')
                ->with(['mietvertraege' => function ($query) {
                    $query->defaultOrder();
                }, 'mietvertraege.mieter' => function ($query) {
                    $query->defaultOrder();
                }]);
            if(session()->has('geldkonto_id')) {
                $einheiten->whereHas('haus.objekt.bankkonten', function ($query) {
                    //todo check if fixed 'GELD_KONTEN.KONTO_ID' <-> 'KONTO_ID'
                    $query->where('GELD_KONTEN.KONTO_ID', session()->get('geldkonto_id'));
                });
            }
            $einheiten = $einheiten->get();
            foreach ($einheiten as $einheit) {
                foreach ($einheit->mietvertraege as $mietvertrag) {
                    $prefix = '';
                    if (!$mietvertrag->isActive('<=')) {
                        $prefix = 'NEUMIETER: ';
                    } elseif (!$mietvertrag->isActive('>=')) {
                        $prefix = 'ALTMIETER: ';
                    }
                    echo $prefix . "$einheit->EINHEIT_KURZNAME*$mietvertrag->MIETVERTRAG_ID*$mietvertrag->mieter_namen|";
                }
            }
        }

        if ($typ == 'GELDKONTO') {
            $result = DB::select("SELECT KONTO_ID, BEZEICHNUNG  FROM `GELD_KONTEN`  WHERE AKTUELL='1' ORDER BY BEZEICHNUNG ASC");
            foreach ($result as $row) {
                echo "$row[BEZEICHNUNG]*$row[KONTO_ID]*|";
            }
        }

        if ($typ == 'Lager') {
            $result = DB::select("SELECT LAGER_ID, LAGER_NAME  FROM `LAGER` WHERE AKTUELL='1' ORDER BY LAGER_NAME ASC");
            foreach ($result as $row) {
                echo "$row[LAGER_NAME]*$row[LAGER_ID]*|";
            }
        }

        if ($typ == 'Baustelle_ext') {
            $result = DB::select("SELECT ID, BEZ  FROM `BAUSTELLEN_EXT`  WHERE AKTUELL='1' && AKTIV='1' ORDER BY BEZ ASC");
            foreach ($result as $row) {
                echo "$row[BEZ]*$row[ID]*|";
            }
        }

        if ($typ == 'Eigentuemer') {
            if (!session()->has('geldkonto_id')) {
                $result = DB::select(
                    "SELECT WEG_MITEIGENTUEMER.ID, WEG_MITEIGENTUEMER.EINHEIT_ID, EINHEIT_KURZNAME, GROUP_CONCAT(CONCAT(persons.name, ', ', persons.first_name) SEPARATOR '; ') AS PERSONEN 
                    FROM persons JOIN WEG_EIGENTUEMER_PERSON ON(persons.id = WEG_EIGENTUEMER_PERSON.PERSON_ID AND persons.deleted_at IS NULL) 
	                  JOIN WEG_MITEIGENTUEMER ON(WEG_EIGENTUEMER_PERSON.WEG_EIG_ID = WEG_MITEIGENTUEMER.ID) 
	                  JOIN EINHEIT ON(EINHEIT.EINHEIT_ID = WEG_MITEIGENTUEMER.EINHEIT_ID) 
                    WHERE EINHEIT.EINHEIT_AKTUELL = '1' && WEG_MITEIGENTUEMER.AKTUELL = '1'  && WEG_EIGENTUEMER_PERSON.AKTUELL = '1'
                    GROUP BY WEG_MITEIGENTUEMER.ID 
                    ORDER BY EINHEIT_KURZNAME ASC"
                );
                foreach ($result as $row)
                    echo "$row[EINHEIT_KURZNAME]*$row[ID]*$row[PERSONEN]|";
            } else {
                $result = DB::select("SELECT ID, WEG_MITEIGENTUEMER.EINHEIT_ID, EINHEIT_KURZNAME FROM `WEG_MITEIGENTUEMER` , EINHEIT WHERE EINHEIT_AKTUELL = '1' && AKTUELL = '1' && EINHEIT.EINHEIT_ID = WEG_MITEIGENTUEMER.EINHEIT_ID GROUP BY ID ORDER BY EINHEIT_KURZNAME ASC");
                foreach ($result as $row) {
                    $weg = new weg ();
                    $ID = $row ['ID'];
                    $einheit_id = $row ['EINHEIT_ID'];
                    $einheit_kn = $row ['EINHEIT_KURZNAME'];
                    $eee = new einheit ();
                    $eee->get_einheit_info($einheit_id);
                    $gk = new gk ();
                    if ($gk->check_zuweisung_kos_typ(session()->get('geldkonto_id'), 'Objekt', $eee->objekt_id)) {
                        $weg->get_eigentuemer_namen($row ['ID']);
                        echo "$einheit_kn*$ID*$weg->eigentuemer_name_str|";
                    }
                }
            }
        }

        if ($typ == 'ALLE') {
            echo "ALLE|";
        }

        if ($typ == 'Person') {
            $users = \App\Models\Person::has('jobsAsEmployee')->defaultOrder()->get();
            foreach ($users as $user) {
                echo "$user->full_name*$user->id*|";
            }
        }

        break;

    case "get_iban_bic" :
        $kto = request()->input('kto');
        $blz = request()->input('blz');
        $sep = new sepa ();
        $sep->get_iban_bic($kto, $blz);
        echo "$sep->IBAN1|$sep->BIC|$sep->BANKNAME_K";
        break;

    case "check_artikels" :
        $artikel_nr = request()->input('artikel_nr');

        $lieferant_id = request()->input('lieferant_id');
        $result = DB::select("SELECT ARTIKEL_NR, BEZEICHNUNG, LISTENPREIS, RABATT_SATZ, EINHEIT, MWST_SATZ, SKONTO FROM POSITIONEN_KATALOG WHERE AKTUELL='1' && ART_LIEFERANT=? && ARTIKEL_NR=? ORDER BY KATALOG_DAT DESC LIMIT 0,1", [$lieferant_id, $artikel_nr]);
        foreach ($result as $row)
            echo "$row[ARTIKEL_NR]|$row[BEZEICHNUNG]|$row[LISTENPREIS]|$row[RABATT_SATZ]|$row[EINHEIT]|$row[MWST_SATZ]|$row[SKONTO]";
        break;

    case "display_positionen" :
        $belegnr = request()->input('belegnr');
        $result = DB::select("SELECT * FROM RECHNUNGEN_POSITIONEN WHERE BELEG_NR=? && AKTUELL='1' ORDER BY POSITION ASC", [$belegnr]);
        if (!empty($result)) {
            $rechnungs_positionen_arr = $result;
            echo "<table id='positionen_tab'>\n";
            echo "<tr>";
            echo "<th>Ändern</th>";
            echo "<th>Pos</th>";
            echo "<th>Art.</th>";
            echo "<th>Bezeichnung</th>";
            echo "<th>Menge</th>";
            echo "<th>Einheit</th>";
            echo "<th>LP</th>";
            echo "<th>EP</th>";
            echo "<th>Rabatt</th>";
            echo "<th>MWSt</th>";
            echo "<th>Skonto</th>";
            echo "<th>Netto</th>";
            echo "</tr>";
            $g_netto = 0;
            $g_mwst = 0;
            $g_brutto = 0;
            for ($a = 0; $a < count($rechnungs_positionen_arr); $a++) {

                $position = $rechnungs_positionen_arr [$a] ['POSITION'];
                $menge = $rechnungs_positionen_arr [$a] ['MENGE'];
                $einzel_preis = $rechnungs_positionen_arr [$a] ['PREIS'];
                $mwst_satz = $rechnungs_positionen_arr [$a] ['MWST_SATZ'];
                $rabatt = $rechnungs_positionen_arr [$a] ['RABATT_SATZ'];
                $gesamt_netto = $rechnungs_positionen_arr [$a] ['GESAMT_NETTO'];
                $gesamt_netto_ausgabe = nummer_punkt2komma($gesamt_netto, 2, '.', '');
                $art_lieferant = $rechnungs_positionen_arr [$a] ['ART_LIEFERANT'];
                $artikel_nr = $rechnungs_positionen_arr [$a] ['ARTIKEL_NR'];
                $pos_skonto = $rechnungs_positionen_arr [$a] ['SKONTO'];

                /* Infos aus Katalog zu Artikelnr */
                $artikel_info_arr = artikel_info($art_lieferant, $rechnungs_positionen_arr [$a] ['ARTIKEL_NR']);
                for ($i = 0; $i < count($artikel_info_arr); $i++) {
                    if (!empty ($artikel_info_arr [$i] ['BEZEICHNUNG'])) {
                        $bezeichnung = $artikel_info_arr [$i] ['BEZEICHNUNG'];
                        $listenpreis = $artikel_info_arr [$i] ['LISTENPREIS'];
                        $v_einheit = $artikel_info_arr [$i] ['EINHEIT'];
                    } else {
                        $bezeichnung = 'Unbekannt';
                        $listenpreis = '0,00';
                    }
                    $menge = nummer_punkt2komma($menge);
                    $einzel_preis = nummer_punkt2komma($einzel_preis);
                    $listenpreis = nummer_punkt2komma($listenpreis);
                    // $rabatt = nummer_punkt2komma($rabatt);
                    $gesamt_preis = nummer_punkt2komma($gesamt_preis);
                    $aendern_link = "<a href='" . route('web::rechnungen::legacy', ['option' => 'position_aendern', 'pos' => $position, 'belegnr' => $belegnr]) . "'>Ändern</a>";
                    $loeschen_link = "<a href='" . route('web::rechnungen::legacy', ['option' => 'position_loeschen', 'pos' => $position, 'belegnr' => $belegnr]) . "'>Löschen</a>";
                    echo "<tr><td valign=top>$aendern_link $loeschen_link</td><td valign=top>$position.</td><td valign=top>$artikel_nr</td><td valign=top>$bezeichnung</td><td align=right valign=top>$menge</td><td>$v_einheit</td><td align=right valign=top>$listenpreis €</td><td align=right valign=top>$einzel_preis €</td><td align=right valign=top>$rabatt %</td><td align=right valign=top>$mwst_satz %</td><td align=right valign=top>$pos_skonto %</td><td align=right valign=top>$gesamt_netto_ausgabe €</td></tr>\n";
                    $g_netto = $g_netto + $gesamt_netto;
                    $g_mwst1 = ($gesamt_netto / 100) * (100 + $mwst_satz);
                    $g_mwst2 = $g_mwst1 - $gesamt_netto;

                    $g_brutto = $g_brutto + $g_mwst1;
                    $g_mwst = $g_mwst + $g_mwst2;
                } // end for 2
            } // end for 1

            $g_netto = sprintf("%0.2f", $g_netto);
            $g_mwst = sprintf("%0.2f", $g_mwst);
            $g_brutto = sprintf("%0.2f", $g_brutto);
            $g_netto = nummer_punkt2komma($g_netto, 2, '.', '');
            $g_mwst = nummer_punkt2komma($g_mwst, 2, '.', '');
            $g_brutto = nummer_punkt2komma($g_brutto, 2, '.', '');

            echo "<tr><td valign=top colspan=12><hr></td></tr>\n";
            echo "<tr><td></td><td valign=top></td><td valign=top></td><td valign=top></td><td valign=top></td><td align=right valign=top></td><td align=right valign=top></td><td align=right valign=top></td><td align=right valign=top></td><td valign=top></td><td align=right valign=top>ERRECHNET</td><td align=right valign=top>Netto: $g_netto €</td></tr>\n";
            echo "<tr><td></td><td valign=top></td><td valign=top></td><td valign=top></td><td valign=top></td><td valign=top></td><td align=right valign=top></td><td align=right valign=top></td><td align=right valign=top></td><td align=right valign=top></td><td align=right valign=top></td><td align=right valign=top>Mwst: $g_mwst €</td></tr>\n";
            echo "<tr><td valign=top></td><td valign=top></td><td valign=top></td><td valign=top></td><td valign=top></td><td align=right valign=top></td><td align=right valign=top></td><td align=right valign=top></td><td valign=top></td><td align=right valign=top></td><td align=right valign=top></td><td align=right valign=top>Brutto: $g_brutto €</td></tr>\n";

            /*
             * $ges_brutto = ($ges_netto / 100) * (100+$mwst_satz);
             * $ges_brutto = number_format($ges_brutto,2, ",", "");
             */
            echo "</table>";
        }
        break;

    case
    "insert_position" :
        $belegnr = request()->input('belegnr');
        $position = request()->input('pos');
        $artikel_nr = request()->input('artikel_nr');
        $bez = trim(addslashes(htmlspecialchars(rawurldecode(request()->input('bez')))));
        $lieferant_id = request()->input('lieferant_id');
        $menge = request()->input('menge');
        $einheit = request()->input('einheit');
        $preis = request()->input('listenpreis');
        $rabatt = request()->input('rabatt');
        $pos_mwst = request()->input('pos_mwst');
        $pos_skonto = request()->input('pos_skonto');
        $g_netto = request()->input('g_netto');

        $r = new rechnung ();
        $letzte_rech_pos_id = $r->get_last_rechnung_pos_id();
        $letzte_rech_pos_id = $letzte_rech_pos_id + 1;

        if (preg_match("/,/i", "$pos_skonto")) {
            $pos_skonto = nummer_komma2punkt($pos_skonto);
        }

        if (preg_match("/,/i", "$pos_mwst")) {
            $pos_mwst = nummer_komma2punkt($pos_mwst);
        }

        if (preg_match("/,/i", "$preis")) {
            $preis = nummer_komma2punkt($preis);
        }

        if (preg_match("/,/i", "$g_netto")) {
            $g_netto = nummer_komma2punkt($g_netto);
        }

        if (preg_match("/,/i", "$menge")) {
            $menge = nummer_komma2punkt($menge);
        }

        $result = DB::select("SELECT * FROM POSITIONEN_KATALOG WHERE ART_LIEFERANT=? && ARTIKEL_NR=? && AKTUELL='1' && LISTENPREIS=? && RABATT_SATZ=? && SKONTO=? && BEZEICHNUNG=?", [$lieferant_id, $artikel_nr, $preis, $rabatt, $pos_skonto, $bez]);
        if (empty($result)) {
            $r->artikel_leistung_mit_artikelnr_speichern($lieferant_id, $bez, $preis, $artikel_nr, $rabatt, $einheit, $pos_mwst, $pos_skonto);
        }

        $r2 = new rechnungen ();
        $last_pos = $r2->rechnung_last_position($belegnr);
        $last_pos = $last_pos + 1;

        $result = DB::insert("INSERT INTO RECHNUNGEN_POSITIONEN VALUES (NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, '1')",
            [$letzte_rech_pos_id, $last_pos, $belegnr, $belegnr, $lieferant_id, $artikel_nr, $menge, $preis, $pos_mwst, $rabatt, $pos_skonto, $g_netto]);
        break;

    case "aendern_position" :
        $belegnr = request()->input('belegnr');
        $pos = request()->input('pos');
        $artikel_nr = request()->input('artikel_nr');
        $bez = request()->input('bez');
        $lieferant_id = request()->input('lieferant_id');
        $menge = request()->input('menge');
        $einheit = request()->input('einheit');
        $preis = request()->input('listenpreis');
        $rabatt = request()->input('rabatt');
        $pos_mwst = request()->input('pos_mwst');
        $g_netto = request()->input('g_netto');
        $pos_skonto = request()->input('pos_skonto');

        $r = new rechnung ();
        $letzte_rech_pos_id = $r->get_last_rechnung_pos_id();
        $letzte_rech_pos_id = $letzte_rech_pos_id + 1;

        /* Abfragen ob Artikel im Katalog "so" vorhanden */
        $result = DB::select("SELECT * FROM POSITIONEN_KATALOG WHERE ART_LIEFERANT=? && ARTIKEL_NR=? && AKTUELL='1' && LISTENPREIS=? && RABATT_SATZ=? && BEZEICHNUNG=? && EINHEIT=? && MWST_SATZ=? && SKONTO=?",
            [$lieferant_id, $artikel_nr, $preis, $rabatt, $bez, $einheit, $pos_mwst, $pos_skonto]);

        /* Falls nicht so vorhanden, artikel speichern */
        if (empty($result)) {
            $r->artikel_leistung_mit_artikelnr_speichern($lieferant_id, $bez, $preis, $artikel_nr, $rabatt, $einheit, $pos_mwst, $pos_skonto);
            /* Falls vorhanden, deaktivieren und als neuen Datensatz speichern */
        }

        $r2 = new rechnungen ();
        /* Alte Position aus der Rechnung deaktivieren */
        $r->position_deaktivieren($pos, $belegnr);
        /* Psition neu speichern */
        DB::insert("INSERT INTO RECHNUNGEN_POSITIONEN VALUES (NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,'1')",
            [$letzte_rech_pos_id, $pos, $belegnr, $belegnr, $lieferant_id, $artikel_nr, $menge, $preis, $pos_mwst, $rabatt, $pos_skonto, $g_netto]);
        break;

    case "get_kontierungs_infos" :
        $r = new rechnungen ();
        $belegnr = request()->input('belegnr');
        $r->rechnung_grunddaten_holen($belegnr);
        $buchungsbetrag = request()->input('buchungsbetrag');
        // netto, brutto, skonto, keine summe oder betrag
        $result = DB::select("SELECT KONTIERUNG_ID, sum( GESAMT_SUMME - ( GESAMT_SUMME /100 * RABATT_SATZ ) ) AS NETTO, sum( (
(
GESAMT_SUMME - ( GESAMT_SUMME /100 * RABATT_SATZ ) ) /100
) * ( 100 + MWST_SATZ )
) AS BRUTTO, sum( (
(
(
GESAMT_SUMME - ( GESAMT_SUMME /100 * RABATT_SATZ ) ) /100
) * ( 100 + MWST_SATZ ) /100
) * ( 100 - SKONTO )
) AS SKONTO_BETRAG, MENGE, POSITION, KONTENRAHMEN_KONTO, KOSTENTRAEGER_TYP, KOSTENTRAEGER_ID
FROM `KONTIERUNG_POSITIONEN`
WHERE BELEG_NR = ? && AKTUELL = '1'
GROUP BY KOSTENTRAEGER_TYP, KOSTENTRAEGER_ID, KONTENRAHMEN_KONTO", [$belegnr]);

        if (!empty($result)) {
            $str = "<b>Kontierung:</b><br>";
            $g_betrag = 0;
            foreach ($result as $row) {

                $netto = $row ['NETTO'];
                $brutto = $row ['BRUTTO'];
                $skonto = $row ['SKONTO_BETRAG'];
                $netto_a = nummer_punkt2komma($row ['NETTO']);
                $brutto_a = nummer_punkt2komma($row ['BRUTTO']);
                $skonto_a = nummer_punkt2komma($row ['SKONTO_BETRAG']);

                $kostenkonto = $row ['KONTENRAHMEN_KONTO'];
                $k_typ = $row ['KOSTENTRAEGER_TYP'];
                $k_id = $row ['KOSTENTRAEGER_ID'];
                $k_bez = get_kostentraeger_infos($k_typ, $k_id);

                if ($buchungsbetrag == 'Nettobetrag') {
                    $str = $str . "$netto_a €| $kostenkonto | $k_bez<br>";
                    $g_betrag = $g_betrag + $netto;
                }
                if ($buchungsbetrag == 'Bruttobetrag') {
                    $str = $str . "$brutto_a €| $kostenkonto | $k_bez<br>";
                    $g_betrag = $g_betrag + $brutto;
                }
                if ($buchungsbetrag == 'Skontobetrag') {
                    $str = $str . "$skonto_a €| $kostenkonto | $k_bez<br>";
                    $g_betrag = $g_betrag + $skonto;
                }
            }
            $g_betrag = nummer_punkt2komma($g_betrag);
            $str = $str . "<br><b>Gesamtbetrag $g_betrag €</b>";
            echo $str;
        }  // end if $numrows
        else {
            echo "Keine Kontierung";
        }

        break;

    case "get_kostentraeger_name" :
        $id = request()->input('id');
        $typ = request()->input('typ');
        $kostentraeger_bez = get_kostentraeger_infos($typ, $id);
        echo $kostentraeger_bez;
        break;

    case "get_detail_inhalt" :
        $tab = request()->input('tab');
        $id = request()->input('id');
        $det_name = request()->input('det_name');
        $d = new detail ();
        $inhalt = $d->finde_detail_inhalt($tab, $id, $det_name);
        if ($inhalt) {
            echo $inhalt;
        } else {
            echo "Detail $det_name in $tab fehlt!";
        }
        break;

    case "get_mv_infos" :
        $mv_id = request()->input('mv_id');
        $mvs = new mietvertraege ();
        $mvs->get_mietvertrag_infos_aktuell($mv_id);
        echo "<pre>";
        echo "Einheit-TYP: $mvs->einheit_typ<br>";
        echo "Einheit: $mvs->einheit_kurzname<br>";
        echo "Anschrift: $mvs->haus_strasse $mvs->haus_nr, $mvs->haus_plz $mvs->haus_stadt<br>";
        echo "Mieter:<br>$mvs->personen_name_string_u<br>";
        echo "Einzug: $mvs->mietvertrag_von_d<br>";
        echo "Auszug: $mvs->mietvertrag_bis_d<br>";
        echo "</pre>";
        break;

    case "get_gk_infos" :
        $gk_id = request()->input('gk_id');
        $var = request()->input('var');
        $geld_konto_info = new geldkonto_info ();
        $geld_konto_info->geld_konto_details($gk_id);
        $value = eval ('return $geld_konto_info->' . $var . ';');
        echo $value;
        break;

    case "get_detail_ukats" :
        $kat_id = request()->input('kat_id');
        if (isset ($kat_id)) {
            $result = DB::select("SELECT UNTERKATEGORIE_NAME FROM `DETAIL_UNTERKATEGORIEN` WHERE `KATEGORIE_ID` = ? AND `AKTUELL` = '1' ORDER BY UNTERKATEGORIE_NAME ASC", [$kat_id]);

            foreach ($result as $row) {
                echo "$row[UNTERKATEGORIE_NAME];";
            }
        } else {
            echo "AJAX FEHLER 2004";
        }
        break;

    case "autovervollst" :
        $string = request()->input('string');
        $lieferant_id = request()->input('l_id');
        if (isset ($string) && strlen($string) > 0) {
            $result = DB::select("SELECT LTRIM(RTRIM(ARTIKEL_NR)), BEZEICHNUNG, LISTENPREIS FROM `POSITIONEN_KATALOG`
WHERE `ART_LIEFERANT` = ? AND (`ARTIKEL_NR` LIKE ? OR `BEZEICHNUNG` LIKE ?) GROUP BY ARTIKEL_NR ORDER BY ARTIKEL_NR ASC LIMIT 0,5", [$lieferant_id, $string . '%', $string . '%']);

            foreach ($result as $row) {
                echo "$row[ARTIKEL_NR]??$row[BEZEICHNUNG]??$row[LISTENPREIS]||";
            }
        } else {
            echo "AJAX FEHLER 20041";
        }
        break;

    case "autovervollst2" :
        $string = request()->input('string');
        $lieferant_id = request()->input('l_id');
        // aktueller partner d.h. eigener preis
        if (isset ($string) && strlen($string) > 0) {
            $result = DB::select("SELECT * FROM (SELECT ARTIKEL_NR, BEZEICHNUNG, LISTENPREIS, ART_LIEFERANT, FORMAT((LISTENPREIS - (LISTENPREIS/100)* RABATT_SATZ)/100*(100+MWST_SATZ),2) AS BRUTTO FROM `POSITIONEN_KATALOG`
WHERE (`ARTIKEL_NR` LIKE ? OR `BEZEICHNUNG` LIKE ?) ORDER BY ART_LIEFERANT ASC, LISTENPREIS DESC, KATALOG_ID DESC) AS AB1 GROUP BY ART_LIEFERANT, ARTIKEL_NR ORDER BY ARTIKEL_NR ASC", [$string . '%', $string . '%']);

            foreach ($result as $row) {
                $p = new partners ();
                $p->get_partner_name($row['ART_LIEFERANT']);
                echo "$row[ARTIKEL_NR]??$row[BEZEICHNUNG]??$row[BRUTTO]??$p->partner_name??$row[ART_LIEFERANT]||";
            }
        } else {
            echo "AJAX FEHLER 20041";
        }

        break;

    /* Betriebskostenabrechnung - Hinzuf�gen von Buchungen zum Profil */
    case "buchung_hinzu" :
        $bk = new bk ();
        $buchung_id = request()->input('buchung_id');
        $profil_id = request()->input('profil_id');
        $bk_konto_id = request()->input('bk_konto_id');
        $bk_genkey_id = session()->get('genkey');
        $bk_hndl = session()->get('hndl');

        if ($bk_hndl == '1') {
            $bk->bk_buchungen_details($buchung_id);
            $hndl_betrag = $bk->buchung_betrag;
        } else {
            $hndl_betrag = 0.00;
        }

        $kontierung_uebernehmen = session()->get('kontierung');

        if ($buchung_id && $profil_id && $bk_konto_id) {

            $bk->bk_profil_infos($profil_id);
            $gesamt_anteil = $bk->gesamt_anteil($buchung_id, $profil_id, $bk_konto_id);
            $max_anteil = 100 - $gesamt_anteil;

            if ($kontierung_uebernehmen == '1') {
                $bk->bk_buchungen_details($buchung_id);
                $bk->bk_kos_typ = $bk->b_kos_typ;
                $bk->bk_kos_id = $bk->b_kos_id;
            }

            $last_bk_be_id = last_id_ajax('BK_BERECHNUNG_BUCHUNGEN', 'BK_BE_ID') + 1;
            DB::insert("INSERT INTO BK_BERECHNUNG_BUCHUNGEN VALUES(NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?,'1')",
                [$last_bk_be_id, $buchung_id, $bk_konto_id, $profil_id, $bk_genkey_id, $max_anteil, $bk->bk_kos_typ, $bk->bk_kos_id, $hndl_betrag]);
        } else {
            echo "Fehler 67765213";
        }
        break;

    /* Betriebskostenabrechnung - Löschen von Buchungen zum Profil */
    case "buchung_raus" :
        $bk_be_id = request()->input('bk_be_id');
        $profil_id = request()->input('profil_id');
        $bk_konto_id = request()->input('bk_konto_id');

        if ($bk_be_id && $profil_id && $bk_konto_id) {
            DB::delete("DELETE FROM BK_BERECHNUNG_BUCHUNGEN WHERE BK_BE_ID=? && BK_K_ID=? && BK_PROFIL_ID=?", [$bk_be_id, $bk_konto_id, $profil_id]);
        }
        break;

    /* Betriebskostenabrechnung - Löschen von Konten aus dem Profil */
    case "konto_hinzu" :
        $profil_id = request()->input('profil_id');
        $bk_konto_id = request()->input('bk_konto_id');

        if ($profil_id && $bk_konto_id) {
            if (!check_konto_exists($bk_konto_id, $profil_id)) {
                $last_id = last_id_ajax('BK_KONTEN', 'BK_K_ID') + 1;
                DB::insert("INSERT INTO BK_KONTEN VALUES (NULL, '$last_id', '$bk_konto_id', '$profil_id','0','0','1')");
                session()->put('bk_konto', $bk_konto_id);
            }
        }
        break;

    /* Betriebskostenabrechnung - Löschen von Konten aus dem Profil */
    case "konto_raus" :
        $profil_id = request()->input('profil_id');
        $bk_konto_id = request()->input('bk_konto_id');

        if ($profil_id && $bk_konto_id) {
            DB::delete("DELETE FROM BK_KONTEN WHERE  BK_K_ID=? && BK_PROFIL_ID=?", [$bk_konto_id, $profil_id]);
            DB::delete("DELETE FROM BK_BERECHNUNG_BUCHUNGEN WHERE  BK_K_ID='$bk_konto_id' && BK_PROFIL_ID= '$profil_id'", [$bk_konto_id, $profil_id]);

            session()->forget('bk_konto');
            session()->forget('bk_konto_id');

            echo "Konto und Buchungen aus Profil entfernt";
        }
        break;

    case "get_eigentuemer" :
        if (request()->has('einheit_id')) {
            echo get_eigentuemer(request()->input('einheit_id'));
        } else {
            echo "Einheit wählen - Fehler 4554as";
        }
        break;

    case "get_wp_vorjahr_wert" :
        $wert = get_wp_vorjahr_wert(request()->input('objekt_id'), request()->input('vorjahr'), request()->input('kostenkonto'));
        echo $wert;
        break;

    case "zeitdiff" :
        $von = request()->input('von');
        $bis = request()->input('bis');
        if (!empty ($von) && !empty ($bis)) {
            $von_arr = explode(':', $von);
            $v_std = $von_arr [0];
            $v_min = $von_arr [1];
            $von_min = ($v_std * 60) + $v_min;

            $bis_arr = explode(':', $bis);
            $b_std = $bis_arr [0];
            $b_min = $bis_arr [1];
            $bis_min = ($b_std * 60) + $b_min;
            $dauer_min = $bis_min - $von_min;
            $dauer_std = ($dauer_min / 60);
            echo "$dauer_min|$dauer_std";
        } else {
            echo "FEHLER|FEHLER";
        }

        break;

    case "pool_auswahl" :
        $dat = request()->input('kontierung_dat');
        $kos_typ = request()->input('kos_typ');
        $kos_id = request()->input('kos_id');
        $js_reg_pool = "onclick=\"reg_pool()\", 'nix')\"";
        dropdown_pools('Zielpool wählen', 'z_pool', 'z_pool', $js_reg_pool, $kos_typ, $kos_id);
        $js = "onclick=\"setTimeout('reg_pool()', 5);";
        $js = $js . "setTimeout('daj3(\'ajax/ajax_info.php?option=kont_pos_deactivate&kontierung_dat=$dat\', \'Rechnung aus Pool zusammenstellen\')', 400);";
        $js = $js . "setTimeout('location.reload()', 1000);\"";
        echo "<input type=button name=\"_snd\" value=\"Eintragen\" class=\"submit\" id=\"_snd\" $js>";

        break;

    case "kont_pos_deactivate" :
        $dat = request()->input('kontierung_dat');
        kontierung_pos_deaktivieren($dat);
        $r_obj = new rechnung ();
        $r_obj->get_kontierung_obj($dat);
        $pool_id = request()->input('pool_id');
        insert_in_u_pool($r_obj, $pool_id);
        break;

    case "pool_up" :
        $kos_typ = request()->input('kos_typ');
        $kos_id = request()->input('kos_id');
        $pp_dat = request()->input('pp_dat');
        $pos = request()->input('virt_pos');
        $pool_id = request()->input('pool_id');
        up($pp_dat, $pos, $pool_id);
        break;

    case "pool_down" :
        $kos_typ = request()->input('kos_typ');
        $kos_id = request()->input('kos_id');
        $pp_dat = request()->input('pp_dat');
        $pos = request()->input('virt_pos');
        $pool_id = request()->input('pool_id');
        down($pp_dat, $pos, $pool_id);
        break;

    case "change_wert" :
        $spalte = request()->input('spalte');
        $pp_dat = request()->input('pp_dat');
        $wert = nummer_komma2punkt(request()->input('wert'));
        update_spalte($pp_dat, $spalte, $wert);
        update_g_preis($pp_dat);
        $rr = new rechnungen ();
        $rr->u_pool_edit($kos_typ, $kos_id);
        break;

    case "change_details" :
        $dat = request()->input('dat');
        $wert = request()->input('wert');
        $det_name = request()->input('det_name');
        $kos_typ = request()->input('kos_typ');
        $kos_id = request()->input('kos_id');
        detail_update($dat, $wert, $det_name, $kos_typ, $kos_id);
        break;

    case "aufpreis" :
        $spalte = request()->input('spalte');
        $pp_dat = request()->input('pp_dat');
        $prozent = nummer_komma2punkt(request()->input('prozent'));
        update_v_preis($spalte, $pp_dat, $prozent);
        break;

    case "spalte_prozent" :
        $spalte = request()->input('spalte');
        $prozent = nummer_komma2punkt(request()->input('prozent'));
        update_spalte_2($spalte, $prozent);
        break;

    case "spalte_prozent_pool" :
        $spalte = request()->input('spalte');
        $prozent = nummer_komma2punkt(request()->input('prozent'));
        $pool_id = request()->input('pool_id');
        spalte_prozent_pool($spalte, $prozent, $pool_id);
        break;

    case "spalte_einheitspreis_pool" :
        $spalte = request()->input('spalte');
        $preis = nummer_komma2punkt(request()->input('preis'));
        $pool_id = request()->input('pool_id');
        spalte_einheitspreis_pool($spalte, $preis, $pool_id);
        break;

    case "u_pool_rechnung_erstellen" :
        $kos_typ = request()->input('kos_typ');
        $kos_id = request()->input('kos_id');

        $aussteller_typ = request()->input('aussteller_typ');
        $aussteller_id = request()->input('aussteller_id');
        $r_datum = request()->input('r_datum');
        $f_datum = request()->input('f_datum');
        $kurzinfo = request()->input('kurzinfo');
        $gk_id = request()->input('gk_id');
        $pool_ids_string = request()->input('pool_ids_string');

        $servicetime_from = request()->input('servicetime_from');
        $servicetime_to = request()->input('servicetime_to');

        $r = new rechnungen ();
        $r->erstelle_rechnung_u_pool($kos_typ, $kos_id, $aussteller_typ, $aussteller_id, $r_datum, $f_datum, $kurzinfo, $gk_id, $pool_ids_string, $servicetime_from, $servicetime_to);
        break;

    case "u_pools_anzeigen" :
        $kos_typ = request()->input('kos_typ');
        $kos_bez = request()->input('kos_bez');
        $r = new rechnungen ();
        echo "$kos_typ $kos_bez";
        $r->u_pools_anzeigen($kos_typ, $kos_bez);
        break;

    case "pool_act_deactivate" :
        $kos_typ = request()->input('kos_typ');
        $kos_id = request()->input('kos_id');
        $pool_id = request()->input('pool_id');
        session()->put('pool_id', $pool_id);
        $r = new rechnungen ();
        $r->pool_act_deactivate($pool_id, $kos_typ, $kos_id);
        break;

    case "u_pool_erstellen" :
        $kos_typ = request()->input('kos_typ');
        $kos_bez = request()->input('kos_bez');
        $pool_bez = request()->input('pool_bez');
        $r = new rechnungen ();
        $r->u_pool_erstellen($pool_bez, $kos_typ, $kos_bez);
        break;

    case "change_text" :
        $art_nr = request()->input('art_nr');
        $lieferant_id = request()->input('lieferant_id');
        $text_neu = request()->input('text_neu');
        $r = new rechnungen ();
        if (!empty ($art_nr) && !empty ($lieferant_id) && !empty ($text_neu)) {
            $r->artikel_text_update($art_nr, $lieferant_id, $text_neu);
        }
        break;

    case "back2pool" :
        $pp_dat = request()->input('pp_dat');

        if (!empty ($pp_dat)) {
            $r = new rechnungen ();
            $r->back2pool($pp_dat);
        }
        break;

    case "change_kautionsfeld" :
        $feld = request()->input('feld');
        $wert = request()->input('wert');
        $mv_id = request()->input('mv_id');
        $k = new kautionen ();
        $k->feld_wert_speichern($mv_id, $feld, $wert);
        break;

    case "change_hk_wert_et" :
        $eig_id = request()->input('et_id');
        $betrag = request()->input('wert');
        $p_id = request()->input('profil_id');

        $w = new weg ();
        $w->hk_verbrauch_eintragen($p_id, $eig_id, $betrag);
        break;
} // END SWITCH

function update_spalte_2($spalte, $prozent)
{
    DB::update("UPDATE POS_POOL SET `$spalte`=(EINZEL_PREIS+((EINZEL_PREIS/100)*$prozent)) WHERE AKTUELL='1'");
    update_g_preis();
}

function spalte_prozent_pool($spalte, $prozent, $pool_id)
{
    DB::update("UPDATE POS_POOL SET `$spalte`=(EINZEL_PREIS+((EINZEL_PREIS/100)*$prozent)) WHERE POOL_ID='$pool_id' && AKTUELL='1'");
    update_g_preis();
}

function spalte_einheitspreis_pool($spalte, $preis, $pool_id)
{
    DB::update("UPDATE POS_POOL SET `$spalte`=$preis WHERE POOL_ID='$pool_id' && AKTUELL='1'");
    update_g_preis();
}

function update_spalte($pp_dat, $spalte, $wert)
{
    DB::update("UPDATE POS_POOL SET `$spalte`='$wert' WHERE PP_DAT='$pp_dat'");
}

function up($pp_dat, $pos, $pool_id)
{
    $pos_new = $pos - 1;

    DB::update("UPDATE POS_POOL SET POS=? WHERE PP_DAT=?", [$pos_new, $pp_dat]);
    DB::update("UPDATE POS_POOL SET POS=? WHERE POS=? && POOL_ID=? && PP_DAT!=?", [$pos, $pos_new, $pool_id, $pp_dat]);

    update_g_preis($pp_dat);
}

function down($pp_dat, $pos, $pool_id)
{
    $pos_new = $pos + 1;
    DB::update("UPDATE POS_POOL SET POS=? WHERE PP_DAT=?", [$pos_new, $pp_dat]);
    DB::update("UPDATE POS_POOL SET POS=? WHERE POS=? && POOL_ID=? && PP_DAT!=?", [$pos, $pos_new, $pool_id, $pp_dat]);

    update_g_preis($pp_dat);
}

function get_last_pos($kos_typ, $kos_id, $aussteller_typ, $aussteller_id, $pool_id)
{
    $result = DB::select("SELECT POS FROM POS_POOL WHERE POOL_ID=? && KOS_TYP=? && KOS_ID=? && AUSSTELLER_TYP=? && AUSSTELLER_ID=? && AKTUELL='1' ORDER BY POS DESC LIMIT 0,1",
        [$pool_id, $kos_typ, $kos_id, $aussteller_typ, $aussteller_id]);
    if (!empty($result)) {
        return $result[0]->POS;
    } else {
        return 0;
    }
}

function insert_in_u_pool($obj, $pool_id)
{
    $pos = get_last_pos($obj->kos_typ, $obj->kos_id, $obj->rechnungs_empfaenger_typ, $obj->rechnungs_empfaenger_id, $pool_id) + 1;
    DB::insert("INSERT INTO POS_POOL VALUES(NULL, '$obj->beleg_nr', '$obj->pos', '$pool_id', '$pos', '$obj->menge', '$obj->einzel_preis','$obj->einzel_preis', '$obj->g_summe', '$obj->mwst_satz', '$obj->skonto', '$obj->rabatt_satz', '$obj->kostenkonto', '$obj->kos_typ', '$obj->kos_id','$obj->rechnungs_empfaenger_typ','$obj->rechnungs_empfaenger_id', '1')");
}

function update_g_preis()
{
    DB::update("UPDATE POS_POOL SET G_SUMME=(MENGE*V_PREIS)/100*(100-RABATT_SATZ) WHERE AKTUELL='1'");
}

function update_v_preis($spalte, $pp_dat, $prozent)
{
    DB::update("UPDATE POS_POOL SET V_PREIS=(EINZEL_PREIS+((EINZEL_PREIS/100)*$prozent)) WHERE PP_DAT='$pp_dat' && AKTUELL='1'");
    update_g_preis();
}

function kontierung_pos_deaktivieren($dat)
{
    DB::update("UPDATE KONTIERUNG_POSITIONEN SET WEITER_VERWENDEN='0' WHERE KONTIERUNG_DAT='$dat'");
    protokollieren('KONTIERUNG_POSITIONEN', $dat, $dat);
}

function dropdown_pools($label, $name, $id, $js, $kos_typ, $kos_id)
{
    $result = DB::select("SELECT * FROM POS_POOLS WHERE KOS_TYP='$kos_typ' && KOS_ID='$kos_id' && AKTUELL='1' ORDER BY POOL_NAME ASC");
    if (!empty($result)) {
        echo "<select name=\"$name\" id=\"$id\" size=\"1\" $js>\n";
        foreach ($result as $row) {
            $pool_id = $row['ID'];
            $pool_name = $row['POOL_NAME'];

            if (session()->has('pool_id') && session()->get('pool_id') == $pool_id) {
                echo "<option value=\"$pool_id\" selected>$pool_name</option>\n";
            } else {
                echo "<option value=\"$pool_id\" >$pool_name</option>\n";
            }
        } // end for
        echo "</select><label for=\"$id\">$label</label>\n";
    } else {
        echo "<b>Keine Unterpools hinterlegt oder aktiviert</b>";
        $link = "<br><a href='" . route('web::rechnungen::legacy', ['option' => 'u_pool_erstellen']) . "'>Hier Pools erstellen</a>";
        echo $link;
        return false;
    }
}

function get_wp_vorjahr_wert($objekt_id, $vorjahr, $kostenkonto)
{
    $result = DB::select("SELECT PLAN_ID FROM WEG_WPLAN WHERE AKTUELL='1' && JAHR='$vorjahr' && OBJEKT_ID='$objekt_id' LIMIT 0,1");
    if (!empty($result)) {
        $vorplan_id = $result[0]['PLAN_ID'];
        $result = DB::select("SELECT BETRAG FROM WEG_WPLAN_ZEILEN WHERE WPLAN_ID='$vorplan_id' && AKTUELL='1' && KOSTENKONTO='$kostenkonto' LIMIT 0,1");
        return nummer_punkt2komma($result[0]['BETRAG']);
    } else {
        return '0,00';
    }
}

/* Artikelinformationen aus dem Katalog holen */
function artikel_info($partner_id, $artikel_nr)
{
    $result = DB::select("SELECT * FROM POSITIONEN_KATALOG WHERE ART_LIEFERANT=?  && ARTIKEL_NR=? && AKTUELL='1' ORDER BY KATALOG_DAT DESC LIMIT 0,1", [$partner_id, $artikel_nr]);
    if (empty($result)) {
        return false;
    } else {
        return $result;
    }
}

/*
 * function nummer_punkt2komma($zahl){
 * $zahl= sprintf("%01.2f", $zahl);
 * return $zahl;
 * }
 */
function check_konto_exists($konto, $profil_id)
{
    $result = DB::select("SELECT * FROM BK_KONTEN WHERE KONTO='$konto' && BK_PROFIL_ID='$profil_id' && AKTUELL='1' LIMIT 0,1");
    return !empty($result);
}

/* Ermitteln der letzten katalog_id */
function get_last_katalog_id()
{
    $result = DB::select("SELECT KATALOG_ID FROM POSITIONEN_KATALOG WHERE AKTUELL='1' ORDER BY KATALOG_ID DESC LIMIT 0,1");
    if (!empty($result)) {
        return $result[0]['KATALOG_ID'];
    } else {
        return 0;
    }

}

function get_kostentraeger_infos($typ, $id)
{
    if ($typ == 'Objekt') {
        $result = DB::select("SELECT OBJEKT_KURZNAME FROM OBJEKT WHERE OBJEKT_AKTUELL='1' && OBJEKT_ID='$id'");
        foreach ($result as $row) {
            return $row['OBJEKT_KURZNAME'];
        }
    }

    if ($typ == 'Haus') {
        $result = DB::select("SELECT HAUS_STRASSE, HAUS_NUMMER FROM HAUS WHERE HAUS_AKTUELL='1' && HAUS_ID='$id'");
        foreach ($result as $row)
            return "$row[HAUS_STRASSE] $row[HAUS_NUMMER]";
    }

    if ($typ == 'Einheit') {
        $result = DB::select("SELECT EINHEIT_KURZNAME FROM EINHEIT WHERE EINHEIT_AKTUELL='1' && EINHEIT_ID='$id'");
        foreach ($result as $row)
            return "$row[EINHEIT_KURZNAME]";
    }

    if ($typ == 'Partner') {
        $result = DB::select("SELECT PARTNER_NAME FROM PARTNER_LIEFERANT WHERE AKTUELL='1' && PARTNER_ID='$id'");
        $PARTNER_NAME1 = '';
        foreach ($result as $row)
            $PARTNER_NAME1 = str_replace('<br>', '', $row['PARTNER_NAME']);
        return "$PARTNER_NAME1";
    }
    if ($typ == 'Lager') {
        $result = DB::select("SELECT LAGER_NAME FROM LAGER WHERE AKTUELL='1' && LAGER_ID='$id'");
        $LAGER_NAME1 = '';
        foreach ($result as $row)
            $LAGER_NAME1 = str_replace('<br>', '', $row['LAGER_NAME']);
        return "$LAGER_NAME1";
    }
}

function last_id_ajax($tab, $spalte)
{
    $result = DB::select("SELECT $spalte FROM `$tab` ORDER BY $spalte DESC LIMIT 0,1");
    if (!empty($result)) {
        return $result[0][$spalte];
    } else {
        return 0;
    }
}

function get_eigentuemer($einheit_id)
{
    $weg = new weg ();
    $weg->get_last_eigentuemer_namen($einheit_id);
    $eigentuemer = strip_tags($weg->eigentuemer_namen);
    if (!empty ($eigentuemer)) {
        return $eigentuemer;
    } else {
        return 'Kein Eigentümer';
    }
}

function get_objekt_arr_gk($geldkonto_id)
{
    $result = DB::select("SELECT KOSTENTRAEGER_ID FROM GELD_KONTEN_ZUWEISUNG WHERE AKTUELL = '1' && KONTO_ID='$geldkonto_id' && KOSTENTRAEGER_TYP='Objekt'");
    return $result;
}

function detail_update($detail_dat, $wert_neu, $det_name, $kos_typ, $kos_id)
{
    $d = new detail ();
    if ($detail_dat != 0) {
        $row = $d->get_detail_info($detail_dat);
        if (is_array($row)) {
            $det_name = $row ['DETAIL_NAME'];
            $tabelle = $row ['DETAIL_ZUORDNUNG_TABELLE'];
            $tabelle_id = $row ['DETAIL_ZUORDNUNG_ID'];
            $det_bemerkung = Auth::user()->email . '-' . date("d.m.Y H:i");

            DB::update("UPDATE DETAIL SET DETAIL_AKTUELL='0' WHERE DETAIL_DAT='$detail_dat'");
            $d->detail_speichern_2($tabelle, $tabelle_id, $det_name, $wert_neu, $det_bemerkung);
        }
    } else {
        $det_bemerkung = Auth::user()->email . '-' . date("d.m.Y H:i");
        $d->detail_speichern_2($kos_typ, $kos_id, $det_name, $wert_neu, $det_bemerkung);
    }
}