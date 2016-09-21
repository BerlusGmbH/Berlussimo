<?php

class berlussimo_global
{
    public $vermietete_einheiten = [];
    public $unvermietete_einheiten = [];

    function berlussimo_global()
    {
        $this->datum_heute = date("Y-m-d");
    }

    function vermietete_einheiten_arr()
    {
        $db_abfrage = "SELECT MIETVERTRAG.MIETVERTRAG_ID, EINHEIT.EINHEIT_KURZNAME FROM MIETVERTRAG JOIN(EINHEIT) ON (EINHEIT.EINHEIT_ID = MIETVERTRAG.EINHEIT_ID) WHERE MIETVERTRAG_AKTUELL='1' && EINHEIT.EINHEIT_AKTUELL='1' && (MIETVERTRAG.MIETVERTRAG_BIS='0000-00-00' OR MIETVERTRAG.MIETVERTRAG_BIS>='$this->datum_heute') ORDER BY EINHEIT.EINHEIT_KURZNAME ASC,MIETVERTRAG.MIETVERTRAG_BIS  DESC";
        while ($row = mysql_fetch_assoc($db_abfrage)) {
            $this->vermietete_einheiten [] ['mv_id'] = $row ['MIETVERTRAG_ID'];
            $this->vermietete_einheiten [] ['einheit_kurzname'] = $row ['EINHEIT_KURZNAME'];
        }
    }

    function unvermietete_einheiten_arr()
    {
        $db_abfrage = "SELECT MIETVERTRAG.MIETVERTRAG_ID, EINHEIT.EINHEIT_KURZNAME FROM MIETVERTRAG JOIN EINHEIT ON (EINHEIT.EINHEIT_ID = MIETVERTRAG.EINHEIT_ID) WHERE MIETVERTRAG_AKTUELL='1' && EINHEIT.EINHEIT_AKTUELL='1' &&  MIETVERTRAG.MIETVERTRAG_BIS<'$this->datum_heute' ORDER BY EINHEIT.EINHEIT_KURZNAME ASC,MIETVERTRAG.MIETVERTRAG_BIS  DESC";
        $resultat = mysql_query($db_abfrage) or die (mysql_error());
        $numrows = mysql_numrows($resultat);
        if ($numrows > 0) {
            while ($row = mysql_fetch_assoc($resultat)) {
                $resultrow = ['einheit_kurzname' => $row ['EINHEIT_KURZNAME'], 'mv_id' => $row ['MIETVERTRAG_ID']];
                $this->unvermietete_einheiten [] = $resultrow;
            }
        }
    }

    function objekt_auswahl_liste()
    {
        session()->put('url.intended', URL::full());

        $mieten = new mietkonto ();
        if (session()->has('objekt_id')) {
            $objekt_kurzname = new objekt ();
            $objekt_kurzname->get_objekt_name(session()->get('objekt_id'));
            $mieten->erstelle_formular("Ausgewähltes Objekt: $objekt_kurzname->objekt_name", NULL);
        } else {
            $mieten->erstelle_formular("Objekt auswählen...", NULL);
        }
        echo "<div class='row'>";
        $objekte = new objekt ();
        $objekte_arr = $objekte->liste_aller_objekte();
        $anzahl_objekte = count($objekte_arr);
        for ($i = 0; $i < $anzahl_objekte; $i++) {
            $objekt_kurzname = ltrim(rtrim(htmlspecialchars($objekte_arr [$i] ["OBJEKT_KURZNAME"])));
            echo "<div class='col s6 m4 l2'>";
            echo "<a href='" . route('legacy::objekte::select', ['objekt_id' => $objekte_arr [$i] ['OBJEKT_ID']]) . "'>" . $objekt_kurzname . "</a>&nbsp;";
            echo "</div>";
        }
        echo "</div>";
        $mieten->ende_formular();
    }

    function monate_jahres_links($jahr, $link)
    {
        $f = new formular ();
        $f->fieldset("Monats- und Jahresauswahl", 'monate_jahre');
        $vorjahr = $jahr - 1;
        $nachjahr = $jahr + 1;
        $link_vorjahr = "&nbsp;<a href=\"$link&jahr=$vorjahr&monat=12\"><b>$vorjahr</b></a>&nbsp;";
        $link_nach = "&nbsp;<a href=\"$link&jahr=$nachjahr&monat=01\"><b>$nachjahr</b></a>&nbsp;";
        echo $link_vorjahr;
        $link_alle = "<a href=\"$link&jahr=$jahr\">Alle von $jahr</a>&nbsp;";
        echo $link_alle;
        for ($a = 1; $a <= 12; $a++) {
            $monat_zweistellig = sprintf('%02d', $a);
            $link_neu = "<a href=\"$link&monat=$monat_zweistellig&jahr=$jahr\">$a/$jahr</a>&nbsp;";
            // echo "$a/$jahr<br>";
            echo "$link_neu";
        }
        echo $link_nach;
        $f->fieldset_ende();
    }

    function jahres_links($jahr, $link)
    {
        $f = new formular ();
        $f->fieldset("Jahr wählen", 'monate_jahre');
        $vorjahr = $jahr - 1;
        $nachjahr = $jahr + 1;
        $link_vorjahr = "&nbsp;<a href=\"$link&jahr=$vorjahr\"><b>$vorjahr</b></a>&nbsp;";
        $link_nach = "&nbsp;<a href=\"$link&jahr=$nachjahr\"><b>$nachjahr</b></a>&nbsp;";
        echo $link_vorjahr;
        echo $link_nach;
        $f->fieldset_ende();
    }
} // ende class global

//http://vwp0174.webpack.hosteurope.de/phpMyAdmin/sql.php?db=db1078767-berlus&table=GELD_KONTO_BUCHUNGEN&token=d228b9a8a8e6215d6a4953b26c215df3&goto=tbl_sql.php&back=tbl_sql.php&pos=0

/*
 * SELECT SUM( MIETE_ZAHLBETRAG.BETRAG ) AS SUMME_ALLER_MIETEN, GELD_KONTEN_ZUWEISUNG.KOSTENTRAEGER_ID
 * FROM `MIETE_ZAHLBETRAG` , GELD_KONTEN_ZUWEISUNG
 * WHERE MIETE_ZAHLBETRAG.KONTO = '7' && GELD_KONTEN_ZUWEISUNG.KONTO_ID = '7' && GELD_KONTEN_ZUWEISUNG.KOSTENTRAEGER_TYP = 'Partner'
 * GROUP BY GELD_KONTEN_ZUWEISUNG.KONTO_ID
 *
 * SELECT SUM( MIETE_ZAHLBETRAG.BETRAG ) AS SUMME_ALLER_MIETEN, SUM( RECHNUNGEN.SKONTOBETRAG ) AS SUMME_GEZAHLTER_RECHNUNGEN, GELD_KONTEN_ZUWEISUNG.KOSTENTRAEGER_ID
 * FROM `MIETE_ZAHLBETRAG` , GELD_KONTEN_ZUWEISUNG, RECHNUNGEN
 * WHERE MIETE_ZAHLBETRAG.KONTO = '7' && GELD_KONTEN_ZUWEISUNG.KONTO_ID = '7' && GELD_KONTEN_ZUWEISUNG.KOSTENTRAEGER_TYP = 'Partner' && GELD_KONTEN_ZUWEISUNG.KOSTENTRAEGER_ID = RECHNUNGEN.EMPFAENGER
 * GROUP BY GELD_KONTEN_ZUWEISUNG.KONTO_ID
 * LIMIT 0 , 30
 */
/*
 * class kasse extends rechnung{
 * var $kassen_name;
 * var $kassen_verwalter;
 * var $kassen_id;
 * var $kasse_in_rechnung_gestellt;
 * var $kasse_aus_rechnung_erhalten;
 * var $kasse_direkt_gezahlt;
 * var $kassen_stand;
 * var $kassen_forderung_offen;
 *
 * function dropdown_kassen($label, $name, $id){
 * $result = mysql_query ("SELECT KASSEN_ID, KASSEN_NAME, KASSEN_VERWALTER FROM `KASSEN` WHERE AKTUELL = '1'");
 * $numrows = mysql_numrows($result);
 * if($numrows){
 * echo "<input type=\"hidden\" name=\"empfaenger_typ\" value=\"Kasse\">";
 * echo "<label for=\"$id\">$label</label>";
 * echo "<select name=\"$name\" id=\"$id\">";
 * while($row = mysql_fetch_assoc($result)){
 * echo "<option value=\"$row[KASSEN_ID]\">$row[KASSEN_NAME] - $row[KASSEN_VERWALTER]</option>";
 * }
 * echo "</select>";
 * }else{
 * return FALSE;
 * }
 * }
 *
 * function get_kassen_info($kassen_id){
 * $result = mysql_query ("SELECT KASSEN_NAME, KASSEN_VERWALTER FROM `KASSEN` WHERE AKTUELL = '1' && KASSEN_ID='$kassen_id' ORDER BY KASSEN_DAT DESC LIMIT 0,1");
 * $numrows = mysql_numrows($result);
 * if($numrows){
 * $row = mysql_fetch_assoc($result);
 * $this->kassen_name = $row[KASSEN_NAME];
 * $this->kassen_verwalter = $row[KASSEN_VERWALTER];
 * $this->kassen_id = $row[KASSEN_ID];
 * }else{
 * return FALSE;
 * }
 * }
 * function kassen_stand($kassen_id){
 * /*Abfrage der von der Kasse gestellten gesammtsumme
 * $result = mysql_query ("SELECT SUM(SKONTOBETRAG) AS kasse_in_rechnung_gestellt FROM RECHNUNGEN WHERE AUSSTELLER_TYP='Kasse' && AUSSTELLER_ID='$kassen_id' && AKTUELL = '1'");
 * $numrows = mysql_numrows($result);
 * if($numrows){
 * $row = mysql_fetch_assoc($result);
 * $this->get_kassen_info($kassen_id);
 * $this->kasse_in_rechnung_gestellt = $row[kasse_in_rechnung_gestellt];
 * }else{
 * return FALSE;
 * }
 * /*Abfrage der an die Kasse gezahlten Gesammtsumme
 * $result = mysql_query ("SELECT SUM(SKONTOBETRAG) AS kasse_aus_rechnung_erhalten FROM RECHNUNGEN WHERE AUSSTELLER_TYP='Kasse' && AUSSTELLER_ID='$kassen_id' && STATUS_BEZAHLT='1' && AKTUELL = '1'");
 * $numrows = mysql_numrows($result);
 * if($numrows){
 * $row = mysql_fetch_assoc($result);
 * $this->get_kassen_info($kassen_id);
 * $this->kasse_aus_rechnung_erhalten = $row[kasse_aus_rechnung_erhalten];
 * }else{
 * return FALSE;
 * }
 * /*Abfrage der aus der Kasse gezahlten Gesammtsumme
 * $result = mysql_query ("SELECT SUM(SKONTOBETRAG) AS kasse_direkt_gezahlt FROM RECHNUNGEN WHERE EMPFAENGER_TYP='Kasse' && EMPFAENGER_ID='$kassen_id' && STATUS_BEZAHLT='1' && AKTUELL = '1'");
 * $numrows = mysql_numrows($result);
 * if($numrows){
 * $row = mysql_fetch_assoc($result);
 * $this->get_kassen_info($kassen_id);
 * $this->kasse_direkt_gezahlt = $row[kasse_direkt_gezahlt];
 * $this->kassen_stand = $this->kasse_aus_rechnung_erhalten - $this->kasse_direkt_gezahlt;
 * $this->kassen_forderung_offen = $this->kasse_in_rechnung_gestellt - $this->kasse_aus_rechnung_erhalten;
 * }else{
 * return FALSE;
 * }
 * }
 * function kassen_ueberblick(){
 * $result = mysql_query ("SELECT KASSEN_ID FROM `KASSEN` WHERE AKTUELL = '1'");
 * $numrows = mysql_numrows($result);
 * if($numrows){
 * echo "<table>";
 * echo "<tr><td>Kasse</td><td>Verwalter</td><td>Kassenstand</td><td>Gezahlt</td><td>Erhalten</td><td>I.R. gestellt</td></tr>";
 * while($row = mysql_fetch_assoc($result)){
 * $this->kassen_stand($row[KASSEN_ID]);
 * echo "<tr><td>$this->kassen_name</td><td>$this->kassen_verwalter</td><td>$this->kassen_stand</td><td>$this->kasse_direkt_gezahlt</td><td>$this->kasse_aus_rechnung_erhalten</td><td>$this->kasse_in_rechnung_gestellt</td></tr>";
 * }
 * echo "</table>";
 * }else{
 * return FALSE;
 * }
 * }
 *
 * }//end class kasse
 */
//function join_alles() {
/*
 * export mv einheit_id einheit_name objektname objekt_id
 * SELECT MIETVERTRAG.MIETVERTRAG_ID, MIETVERTRAG.EINHEIT_ID, EINHEIT.EINHEIT_KURZNAME, OBJEKT.OBJEKT_KURZNAME, OBJEKT.OBJEKT_ID
 * FROM `MIETVERTRAG`
 * RIGHT JOIN (
 * EINHEIT, HAUS, OBJEKT
 * ) ON ( MIETVERTRAG.EINHEIT_ID = EINHEIT.EINHEIT_ID && EINHEIT.HAUS_ID = HAUS.HAUS_ID && HAUS.OBJEKT_ID = OBJEKT.OBJEKT_ID )
 * WHERE MIETVERTRAG_AKTUELL = '1' && ( MIETVERTRAG_BIS = '0000-00-00'
 * OR MIETVERTRAG_BIS >= curdate( ) )
 * GROUP BY MIETVERTRAG.EINHEIT_ID
 * ORDER BY OBJEKT_KURZNAME, EINHEIT_KURZNAME ASC
 * LIMIT 0 , 30
 *
 * /* SELECT MIETVERTRAG.MIETVERTRAG_BIS, MIETVERTRAG.MIETVERTRAG_ID, MIETVERTRAG.EINHEIT_ID, EINHEIT.EINHEIT_KURZNAME, EINHEIT.EINHEIT_LAGE, EINHEIT.EINHEIT_QM, OBJEKT.OBJEKT_KURZNAME, OBJEKT.OBJEKT_ID, HAUS.HAUS_STRASSE, HAUS.HAUS_NUMMER, count( MIETVERTRAG.MIETVERTRAG_ID ) AS MVS
 * FROM `MIETVERTRAG`
 * RIGHT JOIN (
 * EINHEIT, HAUS, OBJEKT
 * ) ON (
 * MIETVERTRAG.EINHEIT_ID = EINHEIT.EINHEIT_ID && EINHEIT.HAUS_ID = HAUS.HAUS_ID && HAUS.OBJEKT_ID = OBJEKT.OBJEKT_ID
 * )
 * WHERE MIETVERTRAG_AKTUELL = '1' && ( MIETVERTRAG_BIS = '0000-00-00'
 * OR MIETVERTRAG_BIS >= curdate( ) )
 * GROUP BY EINHEIT.EINHEIT_ID
 * ORDER BY OBJEKT_KURZNAME ASC
 * LIMIT 30 , 30
 */
/* SELECT MIETVERTRAG.MIETVERTRAG_ID, MIETVERTRAG.EINHEIT_ID, EINHEIT.EINHEIT_KURZNAME, EINHEIT.EINHEIT_LAGE, EINHEIT.EINHEIT_QM, OBJEKT.OBJEKT_KURZNAME, HAUS.HAUS_STRASSE, HAUS.HAUS_NUMMER FROM `MIETVERTRAG` RIGHT JOIN (EINHEIT, HAUS, OBJEKT) ON (MIETVERTRAG.EINHEIT_ID=EINHEIT.EINHEIT_ID && EINHEIT.HAUS_ID=HAUS.HAUS_ID && HAUS.OBJEKT_ID=OBJEKT.OBJEKT_ID) WHERE MIETVERTRAG_AKTUELL='1' && (MIETVERTRAG_BIS='0000-00-00' OR MIETVERTRAG_BIS > '2008-10-31') */

/*
 * alle vermieteten einheiten
 * SELECT MIETVERTRAG.MIETVERTRAG_ID, MIETVERTRAG.EINHEIT_ID, EINHEIT.EINHEIT_KURZNAME FROM `MIETVERTRAG` RIGHT JOIN (EINHEIT) ON (MIETVERTRAG.EINHEIT_ID=EINHEIT.EINHEIT_ID) WHERE MIETVERTRAG_AKTUELL='1' && (MIETVERTRAG_BIS='0000-00-00' OR MIETVERTRAG_BIS > '2008-10-31')
 */

/*
 * Leerstände nach objekt beispiel für objekt 4
 * SELECT OBJEKT_KURZNAME, EINHEIT_KURZNAME, HAUS_STRASSE, HAUS_NUMMER
 * FROM `EINHEIT`
 * RIGHT JOIN (
 * HAUS, OBJEKT
 * ) ON ( EINHEIT.HAUS_ID = HAUS.HAUS_ID && HAUS.OBJEKT_ID = OBJEKT.OBJEKT_ID )
 * WHERE EINHEIT_ID NOT
 * IN (
 *
 * SELECT EINHEIT_ID
 * FROM MIETVERTRAG
 * WHERE MIETVERTRAG_AKTUELL = '1' && MIETVERTRAG_BIS = '0000-00-00'
 * )
 * ORDER BY EINHEIT_KURZNAME ASC
 * LIMIT 0 , 30
 */

/*
 * Mietvertrag Einheit, einheitname, objektname und id
 *
 * SELECT MIETVERTRAG.MIETVERTRAG_ID, MIETVERTRAG.EINHEIT_ID, EINHEIT.EINHEIT_KURZNAME, OBJEKT.OBJEKT_KURZNAME, OBJEKT.OBJEKT_ID FROM `MIETVERTRAG` RIGHT JOIN (EINHEIT, HAUS, OBJEKT) ON (MIETVERTRAG.EINHEIT_ID=EINHEIT.EINHEIT_ID && EINHEIT.HAUS_ID=HAUS.HAUS_ID && HAUS.OBJEKT_ID=OBJEKT.OBJEKT_ID) WHERE MIETVERTRAG_AKTUELL='1' && EINHEIT_AKTUELL='1' && HAUS_AKTUELL='1' && OBJEKT_AKTUELL='1' && (MIETVERTRAG_BIS='0000-00-00' OR MIETVERTRAG_BIS > '2008-10-31')
 *
 * SELECT MIETVERTRAG.MIETVERTRAG_ID, MIETVERTRAG.EINHEIT_ID, EINHEIT.EINHEIT_KURZNAME, OBJEKT.OBJEKT_KURZNAME, OBJEKT.OBJEKT_ID
 * FROM `MIETVERTRAG`
 * RIGHT JOIN (
 * EINHEIT, HAUS, OBJEKT
 * ) ON ( MIETVERTRAG.EINHEIT_ID = EINHEIT.EINHEIT_ID && EINHEIT.HAUS_ID = HAUS.HAUS_ID && HAUS.OBJEKT_ID = OBJEKT.OBJEKT_ID )
 * WHERE MIETVERTRAG_AKTUELL = '1' && EINHEIT_AKTUELL = '1' && HAUS_AKTUELL = '1' && OBJEKT_AKTUELL = '1' && ( MIETVERTRAG_BIS = '0000-00-00'
 * OR MIETVERTRAG_BIS > '2008-10-31' )
 */

/*
 * SELECT EINHEIT_ID, OBJEKT_KURZNAME, EINHEIT_KURZNAME
 * FROM `EINHEIT`
 * RIGHT JOIN (
 * HAUS, OBJEKT
 * ) ON ( EINHEIT.HAUS_ID = HAUS.HAUS_ID && HAUS.OBJEKT_ID = OBJEKT.OBJEKT_ID )
 * WHERE EINHEIT_ID NOT
 * IN (
 *
 * SELECT EINHEIT_ID
 * FROM MIETVERTRAG
 * WHERE MIETVERTRAG_AKTUELL = '1' && ( MIETVERTRAG_BIS = '0000-00-00'
 * OR MIETVERTRAG_BIS > '2008-10-31' )
 * ) && EINHEIT_AKTUELL = '1' && HAUS_AKTUELL = '1' && OBJEKT_AKTUELL = '1'
 * ORDER BY EINHEIT_KURZNAME ASC
 * LIMIT 0 , 30
 */
/*
 * VERMIETETE EINHEITEN
 * SELECT EINHEIT.EINHEIT_ID, EINHEIT.EINHEIT_KURZNAME, MIETVERTRAG_ID, COUNT(MIETVERTRAG_ID) AS MVS FROM EINHEIT right join (MIETVERTRAG) ON (EINHEIT.EINHEIT_ID=MIETVERTRAG.EINHEIT_ID)
 * WHERE EINHEIT_AKTUELL='1' && MIETVERTRAG_AKTUELL='1' && (MIETVERTRAG_BIS = '0000-00-00' OR MIETVERTRAG_BIS > '2008-10-31' ) GROUP BY EINHEIT_ID ORDER BY `MVS` ASC
 *
 */

/*
 * SELECT MIETVERTRAG.MIETVERTRAG_BIS, MIETVERTRAG.MIETVERTRAG_ID, MIETVERTRAG.EINHEIT_ID, EINHEIT.EINHEIT_KURZNAME, PERSON_MIETVERTRAG.PERSON_MIETVERTRAG_PERSON_ID
 * FROM `MIETVERTRAG`
 * RIGHT JOIN (
 * EINHEIT,PERSON_MIETVERTRAG) ON (
 * MIETVERTRAG.EINHEIT_ID = EINHEIT.EINHEIT_ID && MIETVERTRAG.MIETVERTRAG_ID=PERSON_MIETVERTRAG.PERSON_MIETVERTRAG_MIETVERTRAG_ID
 * )
 * WHERE MIETVERTRAG_AKTUELL = '1' && ( MIETVERTRAG_BIS = '0000-00-00'
 * OR MIETVERTRAG_BIS >= curdate( ) )
 * GROUP BY EINHEIT.EINHEIT_KURZNAME
 * ORDER BY `MIETVERTRAG`.`MIETVERTRAG_ID` ASC
 */

/*
 * vergleichen des Zahlbetrages und des internverbuchten betrages
 * select a.summe_gezahlt, b.summe_intern from
 * ( select sum(BETRAG) as summe_gezahlt from MIETE_ZAHLBETRAG WHERE MIETVERTRAG_ID='2') a,
 *
 * ( select sum(BETRAG) as summe_intern from MIETBUCHUNGEN WHERE MIETVERTRAG_ID='2') b
 *
 */

/*
 *
 * SELECT a.summe_mieteinnahmen, b.summe_anderezahlbetrage, a.summe_mieteinnahmen + b.summe_anderezahlbetrage AS KONTOSTAND
 * FROM (
 *
 * SELECT sum( BETRAG ) AS summe_mieteinnahmen
 * FROM MIETE_ZAHLBETRAG
 * WHERE KONTO = '2'
 * )a, (
 *
 * SELECT sum( BETRAG ) AS summe_anderezahlbetrage
 * FROM GELD_KONTO_BUCHUNGEN
 * WHERE GELDKONTO_ID = '2'
 * )b
 */
//}