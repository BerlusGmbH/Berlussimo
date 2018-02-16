<?php

class details {
	function get_details($tabelle, $id) {
		$result = DB::select( "SELECT DETAIL_NAME, DETAIL_INHALT FROM DETAIL WHERE DETAIL_ZUORDNUNG_TABELLE='$tabelle' && DETAIL_ZUORDNUNG_ID='$id' && DETAIL_AKTUELL='1' ORDER BY DETAIL_NAME ASC" );
		return $result;
	}
}
