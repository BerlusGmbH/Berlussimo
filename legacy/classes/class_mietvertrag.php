<?php

class mietvertrag extends einheit {
    var $einheit_id;
    var $anzahl_mietvertraege_gesamt;
    var $mietvertrag_id;
    var $mietvertrag_von;
    var $mietvertrag_bis;
    var $anzahl_personen_im_vertrag;
    var $einheit_id_of_mietvertrag;
    function get_mietvertrag_infos_aktuell($einheit_id) {
        $datum_heute = date ( "Y-m-d" );
        $result = DB::select( "SELECT * FROM MIETVERTRAG WHERE MIETVERTRAG_AKTUELL='1' && EINHEIT_ID='$einheit_id' && ((MIETVERTRAG_BIS>='$datum_heute') OR (MIETVERTRAG_BIS = '0000-00-00')) ORDER BY MIETVERTRAG_DAT DESC LIMIT 0,1" );

        $row = $result[0];

        $this->mietvertrag_von = $row ['MIETVERTRAG_VON'];
        $this->mietvertrag_bis = $row ['MIETVERTRAG_BIS'];
        $this->mietvertrag_id = $row ['MIETVERTRAG_ID'];
    }
    function get_anzahl_personen_zu_mietvertrag($mietvertrag_id) {
        $result = DB::select( "SELECT PERSON_MIETVERTRAG_ID FROM PERSON_MIETVERTRAG WHERE PERSON_MIETVERTRAG_MIETVERTRAG_ID='$mietvertrag_id' && PERSON_MIETVERTRAG_AKTUELL='1' ORDER BY PERSON_MIETVERTRAG_ID ASC" );
        $this->anzahl_personen_im_vertrag = count($result);
    }
    function get_personen_ids_mietvertrag($mietvertrag_id) {
        $result = DB::select( "SELECT PERSON_MIETVERTRAG_PERSON_ID FROM PERSON_MIETVERTRAG WHERE PERSON_MIETVERTRAG_MIETVERTRAG_ID='$mietvertrag_id' && PERSON_MIETVERTRAG_AKTUELL='1' ORDER BY PERSON_MIETVERTRAG_ID ASC" );
        return $result;
    }
    function get_einheit_id_von_mietvertrag($mietvertrag_id) {
        $result = DB::select( "SELECT EINHEIT_ID FROM MIETVERTRAG WHERE MIETVERTRAG_AKTUELL='1' && MIETVERTRAG_ID='$mietvertrag_id' ORDER BY MIETVERTRAG_DAT DESC LIMIT 0,1" );
        $row = $result[0];
        $this->get_einheit_info ( $row ['EINHEIT_ID'] );
        return $row ['EINHEIT_ID'];
    }
    function get_mietvertrag_einzugs_datum($mietvertrag_id) {
        $result = DB::select( "SELECT MIETVERTRAG_VON FROM MIETVERTRAG WHERE MIETVERTRAG_AKTUELL='1' && MIETVERTRAG_ID='$mietvertrag_id' ORDER BY MIETVERTRAG_DAT DESC LIMIT 0,1" );
        $row = $result[0];
        return $row ['MIETVERTRAG_VON'];
    }
    function liste_der_forderungen($mietvertrag_id) {
        $einzugsdatum = $this->get_mietvertrag_einzugs_datum ( $mietvertrag_id );
        $einzugsdatum = $this->date_mysql2german ( $einzugsdatum );
        $this->monate_berechnen_bis_heute ( $einzugsdatum );

        echo "$einzugsmonat   $monate_vergangen";
    }
    function alle_zahlungen($mietvertrag_id) {
        $alle_zahlungen = DB::select( "SELECT DATUM, BETRAG, KOSTENKATEGORIE FROM MIETBUCHUNGEN WHERE MIETVERTRAG_ID='$mietvertrag_id' && MIETBUCHUNGEN_AKTUELL='1'" );
        $anzahl_zahlungen = count ( $alle_zahlungen );
        $ausgabe = "<div align=right>\n";
        for($i = 0; $i < $anzahl_zahlungen; $i ++) {
            $datum = $this->date_mysql2german ( $alle_zahlungen [$i] ['DATUM'] );
            $ausgabe .= "" . $datum . " " . $alle_zahlungen [$i] ['KOSTENKATEGORIE'] . " " . $alle_zahlungen [$i] ['BETRAG'] . " €<hr>\n";
        }
        $ausgabe .= "</div>\n";
        return $ausgabe;
    }
    function summe_aller_zahlungen($mietvertrag_id) {
        $alle_zahlungen = DB::select( "SELECT BETRAG FROM MIETBUCHUNGEN WHERE MIETVERTRAG_ID='$mietvertrag_id' && MIETBUCHUNGEN_AKTUELL='1'" );
        $anzahl_zahlungen = count ( $alle_zahlungen );
        $ausgabe = "<div align=right>\n";
        $summe = 0;
        for($i = 0; $i < $anzahl_zahlungen; $i ++) {
            $summe = $summe + $alle_zahlungen [$i] ['BETRAG'];
        }
        return $summe;
    }
}