<?php

class katalog
{

    /* Funktion zur Erstellung des Partnerauswahlmenues */
    public $erst_preis;
    public $last_preis_;
    public $vorzeichen;
    public $preis_diff;
    public $erst_preis_a;
    public $last_preis;
    public $last_preis_a;
    public $bez;
    public $listenpreis;
    public $rabattsatz;
    public $u_preis;
    public $u_brutto;
    public $u_skontiert;
    public $skonto;

    /* Funktion zur Darstellung der Artikel und Leistungen eines Partners */
    function katalog_artikel_anzeigen($partner_id)
    {
        $result = DB::select("SELECT ARTIKEL_NR, BEZEICHNUNG, LISTENPREIS, RABATT_SATZ, EINHEIT FROM POSITIONEN_KATALOG WHERE ART_LIEFERANT='$partner_id' && AKTUELL='1' GROUP BY ARTIKEL_NR ORDER BY KATALOG_DAT DESC");
        if (!empty($result)) {
            /* Katalogartikel und Leistungen Überschrift */
            echo "<div id=\"div_katalog\">";
            echo "<table id=\"katalog_tab\" class=\"sortable\">\n";
            echo "<thead>";
            echo "<tr>";
            echo "<th>Artikel/Leistung</th>";
            echo "<th>Bezeichnung</th>";
            echo "<th>Preis</th>";
            echo "<th>Rabatt</th>";
            echo "<th>Unser Preis</th>";
            echo "<th>Einheit</th>";
            echo "</tr>";
            echo "</thead>";

            foreach($result as $row) {
                $artnr = $row ['ARTIKEL_NR'];
                $bez = $row ['BEZEICHNUNG'];
                $lp = nummer_punkt2komma($row ['LISTENPREIS']);
                $rabatt = nummer_punkt2komma($row ['RABATT_SATZ']);
                $unser_preis = nummer_punkt2komma(($lp / 100) * (100 - $rabatt));
                $ve = $row ['EINHEIT']; // ve steht für verpackungseinheit
                $link_pe = "<a href='" . route('web::katalog::legacy', ['option' => 'preisentwicklung', 'artikel_nr' => $artnr]) . "'>$artnr</a>";

                echo "<tr><td valign=top>$link_pe</td><td valign=top>$bez</td><td valign=top>$lp</td><td valign=top>$rabatt %</td><td valign=top>$unser_preis</td><td valign=top>$ve</td></tr>";
            }
            echo "</table>";
        }
    }

    function form_preisentwicklung()
    {
        $f = new formular ();
        $f->erstelle_formular("Preisentwicklung anzeigen", null);
        $f->text_feld("Artikelnr", "artikel_nr", "", "30", 'artikel_nr', '');
        // $f->hidden_feld('option', "preisentwicklung_sent");
        $f->hidden_feld('partner_id', session()->get('partner_id'));
        $f->send_button("submit_art", "Suchen");
        $f->ende_formular();
    }

    function preisentwicklung_anzeigen($p_id, $artikel_nr)
    {
        $result = DB::select("SELECT ARTIKEL_NR, BEZEICHNUNG, LISTENPREIS, RABATT_SATZ, SKONTO, ((LISTENPREIS/100)*(100-RABATT_SATZ)) AS UNSER_NETTO, ((LISTENPREIS/100)*(100-RABATT_SATZ)) /100 * (100+MWST_SATZ) AS UNSER_BRUTTO, ((LISTENPREIS/100)*(100-RABATT_SATZ)) /100 * (100+MWST_SATZ) /100 *(100-SKONTO) AS U_SKONTIERT FROM POSITIONEN_KATALOG WHERE ART_LIEFERANT='$p_id'  && ARTIKEL_NR='$artikel_nr' && AKTUELL='1'   ORDER BY KATALOG_ID ASC");

        $numrows = count($result);
        if ($numrows > 0) {
            $zeile = 0;
            echo "<table>";
            echo "<tr><th>Zeile</th><th>Artikelnr</th><th>Bezeichnung</th><th>LP</th><th>RABATT</th><th>NETTO</th><th>BRUTTO</th><th>SKONTO</th><th>SKONTIERT</th></tr>";
            foreach($result as $row) {
                if ($zeile == 0) {
                    $erst_preis = nummer_punkt2komma($row ['UNSER_NETTO']);
                }

                if ($zeile == $numrows - 1) {
                    $last_preis = nummer_punkt2komma($row ['UNSER_NETTO']);
                }

                $zeile++;
                $bez = $row ['BEZEICHNUNG'];
                $listenpreis = nummer_punkt2komma($row ['LISTENPREIS']);
                $rabattsatz = nummer_punkt2komma($row ['RABATT_SATZ']);
                $u_preis = nummer_punkt2komma($row ['UNSER_NETTO']);
                $u_brutto = nummer_punkt2komma($row ['UNSER_BRUTTO']);
                $u_skontiert = nummer_punkt2komma($row ['U_SKONTIERT']);
                $skonto = nummer_punkt2komma($row ['SKONTO']);
                // echo "$zeile.| $artikel_nr | $bez | $listenpreis | $rabattsatz | $u_preis <br>";
                echo "<tr><td>$zeile</td><td>$artikel_nr</td><td>$bez</td><td>$listenpreis</td><td>$rabattsatz</td><td>$u_preis</td><td>$u_brutto</td><td>$skonto</td><td>$u_skontiert</td></tr>";
            }
            echo "<hr>";
            if ($erst_preis > $last_preis) {
                // echo "<b>Preis für $bez ist gesunken ($erst_preis>$last_preis)</b>";
                echo "<tr><td colspan=\"9\">Preis für $bez ist <b>gesunken</b> ($erst_preis>$last_preis)</td></tr>";
            }

            if ($erst_preis < $last_preis) {
                // echo "<b>Preis für $bez ist gestiegen ($erst_preis<$last_preis)</b>";
                echo "<tr><td colspan=\"9\">Preis für $bez ist <b>gestiegen</b>  ($erst_preis<$last_preis)</td></tr>";
                $erst_preis_p = nummer_komma2punkt($erst_preis);
                $last_preis_p = nummer_komma2punkt($last_preis);
                $preis_diff = Nummer_punkt2komma(($last_preis_p / ($erst_preis_p / 100)) - 100);
                echo "<tr><td colspan=\"9\" style=\"color:red\">ANSTIEG IN PROZENT $preis_diff %</td></tr>";
            }

            if ($erst_preis == $last_preis) {
                // echo "<b>Preis für $bez ist unverändert ($erst_preis=$last_preis)</b>";
                echo "<tfoot><tr><td colspan=\"9\">Preis für $bez ist unverändert  ($erst_preis=$last_preis)</td></tr></tfoot>";
            }
            echo "</table>";
        } else {
            fehlermeldung_ausgeben("Artikel nicht gefunden");
        }
    }

    function get_preis_entwicklung_infos($p_id, $artikel_nr)
    {
        $result = DB::select("SELECT ARTIKEL_NR, BEZEICHNUNG, LISTENPREIS, RABATT_SATZ, SKONTO, ((LISTENPREIS/100)*(100-RABATT_SATZ)) AS UNSER_NETTO, ((LISTENPREIS/100)*(100-RABATT_SATZ)) /100 * (100+MWST_SATZ) AS UNSER_BRUTTO, ((LISTENPREIS/100)*(100-RABATT_SATZ)) /100 * (100+MWST_SATZ) /100 *(100-SKONTO) AS U_SKONTIERT FROM POSITIONEN_KATALOG WHERE ART_LIEFERANT='$p_id'  && ARTIKEL_NR='$artikel_nr' && AKTUELL='1'   ORDER BY KATALOG_ID ASC");
        $numrows = count($result);
        if ($numrows > 0) {
            $zeile = 0;
            if ($numrows == '1') {
                $row = $result[0];
                $this->erst_preis = $row ['UNSER_NETTO'];
                $this->last_preis_ = $row ['UNSER_NETTO'];
                $this->vorzeichen = "+";
                $this->preis_diff = '0.00';
            } else {
                foreach($result as $row) {
                    if ($zeile == 0) {
                        $this->erst_preis = $row ['UNSER_NETTO'];
                        $this->erst_preis_a = nummer_punkt2komma($row ['UNSER_NETTO']);
                    }
                    if ($zeile == $numrows - 1) {
                        $this->last_preis = $row ['UNSER_NETTO'];
                        $this->last_preis_a = nummer_punkt2komma($row ['UNSER_NETTO']);
                    }

                    $zeile++;
                    $this->bez = $row ['BEZEICHNUNG'];
                    $this->listenpreis = nummer_punkt2komma($row ['LISTENPREIS']);
                    $this->rabattsatz = nummer_punkt2komma($row ['RABATT_SATZ']);
                    $this->u_preis = nummer_punkt2komma($row ['UNSER_NETTO']);
                    $this->u_brutto = nummer_punkt2komma($row ['UNSER_BRUTTO']);
                    $this->u_skontiert = nummer_punkt2komma($row ['U_SKONTIERT']);
                    $this->skonto = nummer_punkt2komma($row ['SKONTO']);
                }

                if ($this->erst_preis > $this->last_preis) {
                    $this->vorzeichen = "-";
                    $this->preis_diff = '0.00';
                }

                if ($this->erst_preis < $this->last_preis) {
                    $this->vorzeichen = "+";
                    $this->preis_diff = nummer_punkt2komma(($this->last_preis / ($this->erst_preis / 100)) - 100);
                }

                if ($this->erst_preis == $this->last_preis) {
                    $this->vorzeichen = "+";
                    $this->preis_diff = '0.00';
                }
            }
        } else {
            fehlermeldung_ausgeben("Artikel nicht gefunden");
        }
    }

    function artikel_suche_einkauf_form()
    {
        $f = new formular ();
        $f->erstelle_formular("Artikel in allen Rechnungen suchen", NULL);
        $f->text_feld("Artikelnummer oder ein Teil davon eingeben", "artikel_nr", "", "35", 'artikel_nr', '');
        $f->hidden_feld("option", "artikel_suche");
        $f->send_button("submit", "Suchen");
        $f->ende_formular();
    }

    function artikel_suche_freitext_form()
    {
        $f = new formular ();
        $f->erstelle_formular("Artikel in allen Katalogen suchen", NULL);
        $f->text_feld("Artikelnummer oder ein Teil davon eingeben oder Teilbezeichnung", "artikel_nr", "", "35", 'artikel_nr', '');
        $f->hidden_feld("option", "artikel_suche_freitext");
        $f->send_button("submit", "Suchen");
        $f->ende_formular();
    }

    function artikel_suche_einkauf($artikel_nr)
    {
        $result = DB::select(" SELECT RECHNUNGSNUMMER, RECHNUNGEN_POSITIONEN.BELEG_NR, U_BELEG_NR, POSITION, ART_LIEFERANT, ARTIKEL_NR, MENGE, PREIS, SKONTO, MWST_SATZ, GESAMT_NETTO, (GESAMT_NETTO/100)*(100+MWST_SATZ) AS BRUTTO, (GESAMT_NETTO/100)*(100+MWST_SATZ) /100 * (100-SKONTO) AS SKONTIERT
FROM `RECHNUNGEN_POSITIONEN` , RECHNUNGEN
WHERE `ARTIKEL_NR` LIKE '$artikel_nr'
AND RECHNUNGEN.AKTUELL = '1'
AND RECHNUNGEN_POSITIONEN.AKTUELL = '1'
AND RECHNUNGEN_POSITIONEN.BELEG_NR = RECHNUNGEN.BELEG_NR");
        if (!empty($result)) {
            echo "<h3>Suchergebnis in allen Rechnungen  zu: $artikel_nr</h3>";
            echo "<table class=\"sortable\">";
            echo "<tr><th>LIEFERANT</th><th>ARTIKELNR</th><th>RNR</th><th>POSITION</th><th>BEZEICHNUNG</th><th>MENGE EINGANG</th><th>MENGE RAUS</th><th>RESTMENGE</th><th>NETTO</th><th>MWST %</th><th>BRUTTO</th><th>SKONTO</th><th>SKONTIERT</th></tr>";
            $g_menge = 0;
            $g_kontiert = 0;
            foreach($result as $row) {
                $p = new partners ();
                $r_nr = $row ['RECHNUNGSNUMMER'];
                $beleg_nr = $row ['BELEG_NR'];
                $position = $row ['POSITION'];
                $art_lieferant = $row ['ART_LIEFERANT'];
                $p->get_partner_name($art_lieferant);
                $art_nr = $row ['ARTIKEL_NR'];
                $menge = $row ['MENGE'];
                $r = new rechnung ();
                $artikel_info_arr = $r->artikel_info($art_lieferant, $art_nr);
                $artikel_bez = $artikel_info_arr [0] ['BEZEICHNUNG'];
                $kontierte_menge = nummer_punkt2komma($r->position_auf_kontierung_pruefen($beleg_nr, $position));
                $g_kontiert += nummer_komma2punkt($kontierte_menge);
                $g_menge += $menge;
                $rest_menge_pos = nummer_punkt2komma($menge - nummer_komma2punkt($kontierte_menge));

                $preis = nummer_punkt2komma($row ['PREIS']);
                $brutto = nummer_punkt2komma($row ['BRUTTO']);
                $skonto = nummer_punkt2komma($row ['SKONTO']);
                $skontiert = nummer_punkt2komma($row ['SKONTIERT']);
                $mwst_satz = nummer_punkt2komma($row ['MWST_SATZ']);

                $r_link = "<a href='" . route('web::rechnungen.show', ['id' => $beleg_nr]) . "'>$r_nr</a>";
                echo "<tr><td>$p->partner_name</td><td>$art_nr</td><td>$r_link</td><td>$position</td><td>$artikel_bez</td><td>$menge</td><td>$kontierte_menge</td><td>$rest_menge_pos</td><td>$preis</td><td>$mwst_satz</td><td>$brutto</td><td>$skonto</td><td>$skontiert</td></tr>";
            }
            $g_rest = nummer_punkt2komma($g_menge - $g_kontiert);
            $g_menge = nummer_punkt2komma($g_menge);
            $g_kontiert = nummer_punkt2komma($g_kontiert);
            echo "<tfoot><tr ><td colspan=\"5\"><b>BESTAND</b></td><td><b>$g_menge</b></td><td><b>$g_kontiert</b></td><td><b>$g_rest</b></td><td colspan=\"5\"></td></tr></tfoot>";
            echo "</table>";
        } else {
            echo "KEINE ARTIKEL GEFUNDEN $artikel_nr";
        }
    }

    function artikel_suche_freitext($artikel_nr)
    {
        $result = DB::select("SELECT * FROM POSITIONEN_KATALOG WHERE AKTUELL='1' && ARTIKEL_NR LIKE '%$artikel_nr%' OR BEZEICHNUNG LIKE '%$artikel_nr%' GROUP BY ARTIKEL_NR, ART_LIEFERANT ORDER BY ART_LIEFERANT DESC, BEZEICHNUNG ASC");
        if (!empty($result)) {
            echo "<h5>Suchergebnis in allen Katalogen  zu: $artikel_nr</h5>";
            echo "<table class=\"sortable\">";
            echo "<tr><th>LIEFERANT</th><th>ARTIKELNR</th><th>BEZEICHNUNG</th></tr>";

            foreach($result as $row) {
                $p = new partners ();

                $art_lieferant = $row ['ART_LIEFERANT'];
                $p->get_partner_name($art_lieferant);
                $art_nr = $row ['ARTIKEL_NR'];

                $r = new rechnung ();
                $artikel_info_arr = $r->artikel_info($art_lieferant, $art_nr);
                $artikel_bez = $artikel_info_arr [0] ['BEZEICHNUNG'];
                $link_preis_info1 = "<a href='" . route('web::katalog::legacy', ['option' => 'artikel_suche', 'artikel_nr' => $art_nr, 'lieferant' => $art_lieferant]) . "'>$art_nr</a>";
                echo "<tr><td>$p->partner_name</td><td>$link_preis_info1</td><td>$artikel_bez</td></tr>";
            }
            echo "</table>";
        } else {
            echo "KEINE ARTIKEL GEFUNDEN $artikel_nr";
        }
    }

    function form_zuletzt_gekauft($partner_id)
    {
        $p = new partner ();
        $partner_name = $p->get_partner_name(session()->get('partner_id'));

        $f = new formular ();
        $f->erstelle_formular("Zuletzt gekauft bei $partner_name", null);
        $f->text_feld('Anzahl zuletzt gekaufter Artikel', 'art_anz', 100, 50, 'art_anz', null);
        $f->send_button('BTN_ANZ', 'Anzeigen');
        $f->ende_formular();
    }

    function get_positionen_arr($partner_id, $limit = null)
    {
        if ($limit == null) {
            $result = DB::select("SELECT * FROM  `RECHNUNGEN_POSITIONEN`	WHERE  `ART_LIEFERANT` ='$partner_id' AND  `AKTUELL` =  '1' && 
			BELEG_NR IN(SELECT BELEG_NR FROM  `RECHNUNGEN` WHERE  `AUSSTELLER_TYP` =  'Partner' AND  `AUSSTELLER_ID` ='$partner_id' AND  `AKTUELL` =  '1')
			  GROUP BY ARTIKEL_NR, BELEG_NR ORDER BY BELEG_NR DESC, POSITION ASC");
        } else {
            $result = DB::select("SELECT * FROM  `RECHNUNGEN_POSITIONEN`	WHERE   `ART_LIEFERANT` ='$partner_id' AND  `AKTUELL` =  '1' 	&& 
		BELEG_NR IN(SELECT BELEG_NR FROM  `RECHNUNGEN` WHERE  `AUSSTELLER_TYP` =  'Partner' AND  `AUSSTELLER_ID` ='$partner_id' AND  `AKTUELL` =  '1')
				GROUP BY ARTIKEL_NR,  BELEG_NR  ORDER BY BELEG_NR DESC,  POSITION ASC LIMIT 0,$limit");
        }
        return $result;
    }

    function get_anz_bisher($art_nr, $partner_id)
    {
        $result = DB::select("SELECT SUM(MENGE) AS G_MENGE FROM  `RECHNUNGEN_POSITIONEN`	WHERE ARTIKEL_NR='$art_nr' &&  `ART_LIEFERANT` ='$partner_id' AND  `AKTUELL` =  '1' &&
			BELEG_NR IN(SELECT BELEG_NR FROM  `RECHNUNGEN` WHERE  `AUSSTELLER_TYP` = 'Partner' AND  `AUSSTELLER_ID` ='$partner_id' AND  `AKTUELL` =  '1')");
        if (!empty($result)) {
            $row = $result[0];
            return $row ['G_MENGE'];
        } else {
            return '0.00';
        }
    }

    function get_meistgekauft_arr($partner_id)
    {
        $result = DB::select("SELECT BELEG_NR, SUM(MENGE) AS G_MENGE, ARTIKEL_NR  FROM  `RECHNUNGEN_POSITIONEN`	WHERE  `ART_LIEFERANT` ='$partner_id' AND  `AKTUELL` =  '1' &&
			BELEG_NR IN(SELECT BELEG_NR FROM  `RECHNUNGEN` WHERE  `AUSSTELLER_TYP` = 'Partner' AND  `AUSSTELLER_ID` ='$partner_id' AND  `AKTUELL` =  '1') GROUP BY ARTIKEL_NR ORDER BY G_MENGE DESC");
        return $result;
    }
} // end class