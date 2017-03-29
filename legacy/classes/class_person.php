<?php

class person extends einheit {
    var $person_id;
    var $person_nachname;
    var $person_vorname;
    var $person_geburtstag;
    var $person_anzahl_mietvertraege;
    var $person_anzahl_mietvertraege_alt;
    function get_person_infos($person_id) {
        $result = DB::select( "SELECT * FROM persons WHERE id='$person_id'" );
        $row = $result[0];
        $this->person_nachname = ltrim ( rtrim ( $row ['name'] ) );
        $this->person_vorname = ltrim ( rtrim ( $row ['first_name'] ) );
        $this->person_geburtstag = ltrim ( rtrim ( $row ['birthday'] ) );
    }
    function get_person_anzahl_mietvertraege_aktuell($person_id) {
        $result = DB::select( "SELECT PERSON_MIETVERTRAG_MIETVERTRAG_ID FROM PERSON_MIETVERTRAG WHERE PERSON_MIETVERTRAG_PERSON_ID='$person_id' && PERSON_MIETVERTRAG_AKTUELL='1'" );
        $anzahl = count( $result );
        $this->person_anzahl_mietvertraege = $anzahl;
    }
    function get_vertrags_status($mietvertrag_id) {
        $datum_heute = date ( "Y-m-d" );
        $result = DB::select( "SELECT * FROM MIETVERTRAG WHERE MIETVERTRAG_ID = '$mietvertrag_id' && MIETVERTRAG_AKTUELL = '1' && ( (MIETVERTRAG_BIS >= '$datum_heute')
OR (MIETVERTRAG_BIS = '0000-00-00') ) " );
        return !empty($result);
    }
    function get_vertrags_ids_von_person($person_id) {
        $result = DB::select( "SELECT PERSON_MIETVERTRAG_MIETVERTRAG_ID FROM PERSON_MIETVERTRAG WHERE PERSON_MIETVERTRAG_PERSON_ID='$person_id' && PERSON_MIETVERTRAG_AKTUELL='1'" );
        return $result;
    }
}