<?php

class details {
	function get_details($tabelle, $id) {
		$result = mysql_query ( "SELECT DETAIL_NAME, DETAIL_INHALT FROM DETAIL WHERE DETAIL_ZUORDNUNG_TABELLE='$tabelle' && DETAIL_ZUORDNUNG_ID='$id' && DETAIL_AKTUELL='1' ORDER BY DETAIL_NAME ASC" );
		$my_array = array ();
		while ( $row = mysql_fetch_assoc ( $result ) )
			$my_array [] = $row;
		return $my_array;
	}
}
