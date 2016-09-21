<?php

class person extends einheit {
    var $person_id;
    var $person_nachname;
    var $person_vorname;
    var $person_geburtstag;
    var $person_anzahl_mietvertraege;
    var $person_anzahl_mietvertraege_alt;
    function get_person_infos($person_id) {
        $result = mysql_query ( "SELECT * FROM PERSON WHERE PERSON_AKTUELL='1' && PERSON_ID='$person_id' ORDER BY PERSON_DAT DESC LIMIT 0,1" ) or die ( mysql_error () );
        // echo "SELECT * FROM PERSON WHERE PERSON_AKTUELL='1' && PERSON_ID='$person_id' ORDER BY PERSON_DAT DESC LIMIT 0,1";
        $row = mysql_fetch_assoc ( $result );
        $this->person_nachname = ltrim ( rtrim ( $row ['PERSON_NACHNAME'] ) );
        $this->person_vorname = ltrim ( rtrim ( $row ['PERSON_VORNAME'] ) );
        $this->person_geburtstag = ltrim ( rtrim ( $row ['PERSON_GEBURTSTAG'] ) );
    }
    function get_person_anzahl_mietvertraege_aktuell($person_id) {
        $result = mysql_query ( "SELECT PERSON_MIETVERTRAG_MIETVERTRAG_ID FROM PERSON_MIETVERTRAG WHERE PERSON_MIETVERTRAG_PERSON_ID='$person_id' && PERSON_MIETVERTRAG_AKTUELL='1'" );
        $anzahl = mysql_numrows ( $result );
        $this->person_anzahl_mietvertraege = $anzahl;
        // Wieviel MV hat die Person (nur aktuelle)
    }
    function get_vertrags_status($mietvertrag_id) {
        $datum_heute = date ( "Y-m-d" );
        $result = mysql_query ( "SELECT * FROM MIETVERTRAG WHERE MIETVERTRAG_ID = '$mietvertrag_id' && MIETVERTRAG_AKTUELL = '1' && ( (MIETVERTRAG_BIS >= '$datum_heute')
OR (MIETVERTRAG_BIS = '0000-00-00') ) " );
        $anzahl = mysql_numrows ( $result );
        if ($anzahl < 1) {
            return FALSE;
        } else {
            return TRUE;
        }
    }
    function get_vertrags_ids_von_person($person_id) {
        $result = mysql_query ( "SELECT PERSON_MIETVERTRAG_MIETVERTRAG_ID FROM PERSON_MIETVERTRAG WHERE PERSON_MIETVERTRAG_PERSON_ID='$person_id' && PERSON_MIETVERTRAG_AKTUELL='1'" );
        $my_array = array ();
        while ( $row = mysql_fetch_assoc ( $result ) )
            $my_array [] = $row;
        return $my_array;
    }
}