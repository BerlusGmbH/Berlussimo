<?php

/*
 * Das ist eine Test bzw. Spieledatei, aus der kann man viel lernen,
 *
 */

class ids {
	var $id;
	var $mietvertrag_von;
	public function last_id($tabelle) {
		$spaltenname_in_gross = strtoupper ( $tabelle );
		$zusatz = "_ID";
		$select_spaltenname = "$spaltenname_in_gross$zusatz";
		$db_abfrage = "SELECT $select_spaltenname FROM $spaltenname_in_gross ORDER BY $select_spaltenname DESC LIMIT 0,1";
		$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		while ( list ( $select_spaltenname ) = mysql_fetch_row ( $resultat ) )
			$this->id = $select_spaltenname;
	}
	function get_einzugsdatum($mietvertrag_id) {
		$result = mysql_query ( "SELECT MIETVERTRAG_VON FROM MIETVERTRAG WHERE MIETVERTRAG_AKTUELL='1' && MIETVERTRAG_ID='$mietvertrag_id' ORDER BY MIETVERTRAG_DAT DESC LIMIT 0,1" );
		$row = mysql_fetch_assoc ( $result );
		$this->mietvertrag_von = $row ['MIETVERTRAG_VON'];
	}
}
function import_me($tabelle) {
	$tabelle_in_gross = strtoupper ( $tabelle ); // Tabelle in GROßBUCHSTABEN
	$datei = "$tabelle.csv"; // DATEINAME
	$array = file ( $datei ); // DATEI IN ARRAY EINLESEN
	echo $array [0]; // ZEILE 0 mit Überschriften
	$feldernamen [] = explode ( ":", $array [0] ); // FELDNAMEN AUS ZEILE 0 IN ARRAY EINLESEN
	$anzahl_felder = count ( $feldernamen [0] ); // ANZAHL DER IMPORT FELDER
	$feld1 = $feldernamen [0] [0]; // FELD1 - IMPORT nur zur info
	echo "<h1>$feld1</h1>";
	
	echo "<b>Importiere daten aus $datei nach MYSQL $tabelle_in_gross:</b><br><br>";
	$zeile [1] = explode ( ":", $array [1] ); // Zeile in Array einlesen
	$zeile [2] = explode ( ":", $array [2] ); // Zeile in Array einlesen
	/*
	 * echo "<pre>";
	 * print_r($zeile);
	 * echo "</pre>";
	 */
	for($i = 1; $i < count ( $array ); $i ++) // Datei ab Zeile1 einlesen, weil Zeile 0 Überschrift ist
{
		
		$zeile [$i] = explode ( ":", $array [$i] ); // Zeile in Array einlesen
		/* MV begin */
		$akt_id = new ids ();
		$mv_id = $zeile [$i] [2];
		$akt_id->get_einzugsdatum ( $mv_id );
		$mietvertrag_von = $akt_id->mietvertrag_von;
		$mietekalt_beschriftung = $feldernamen [0] [3];
		$miete_kalt = $zeile [$i] [3];
		$miete_kalt = nummer_komma2punkt ( $miete_kalt );
		$nebenkosten_beschriftung = $feldernamen [0] [4];
		$nebenkosten_summe = $zeile [$i] [4];
		$nebenkosten_summe = nummer_komma2punkt ( $nebenkosten_summe );
		$heizkosten_beschriftung = $feldernamen [0] [5];
		$heizkosten_summe = $zeile [$i] [5];
		$heizkosten_summe = nummer_komma2punkt ( $heizkosten_summe );
		$bankname = $zeile [$i] [7];
		$blz = $zeile [$i] [8];
		$kontonummer = $zeile [$i] [9];
		$kontoinhaber = $zeile [$i] [10];
		$einzugsart = $zeile [$i] [12];
		$einzugsart = ltrim ( $einzugsart );
		$einzugsart = rtrim ( $einzugsart );
		$kautionsbetrag = $zeile [$i] [11];
		/*
		 * if(!empty($kautionsbetrag)){
		 * kaution_als_detail($mv_id, $kautionsbetrag);
		 * }
		 *
		 * /*if(!empty($kontonummer)){
		 * echo "<br>".$i."-".$kontonummer.$blz.$bankname.$einzugsart."<br>";
		 * teilnahme_einzug_hinzu($mv_id, $kontoinhaber, $kontonummer, $blz, $bankname, $einzugsart);---------- Thorsten Backhaus fehlte
		 */
		// }
		
		/* MIETENTWICKLUNG */
		if (! empty ( $miete_kalt ) && $miete_kalt != '0,00' && $miete_kalt != '.00') {
			// ####letzte id der tabelle
			$akt_id->last_id ( $tabelle ); // Objektwert zuweisen
			$letzte_tab_id = $akt_id->id; // Letzte id
			$letzte_tab_id = $letzte_tab_id + 1; // Letzte id um 1 erhöhen
			                                   // ####letzte id der tabelle
			$vorhanden = zeile_vorhanden ( $mv_id, $mietekalt_beschriftung );
			if (! $vorhanden) {
				
				$db_abfrage = "INSERT INTO $tabelle_in_gross  VALUES (NULL, '$letzte_tab_id', '$mv_id', '$mietekalt_beschriftung', '$mietvertrag_von', '0000-00-00', '$miete_kalt', '1')";
				echo "<br>DB = $db_abfrage<br>";
				$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
			}
		}
		
		if (! empty ( $nebenkosten_summe ) && $nebenkosten_summe != '0,00' && $nebenkosten_summe != '.00') {
			// ####letzte id der tabelle
			
			$akt_id->last_id ( $tabelle ); // Objektwert zuweisen
			$letzte_tab_id = $akt_id->id; // Letzte id
			$letzte_tab_id = $letzte_tab_id + 1; // Letzte id um 1 erhöhen
			                                   // ####letzte id der tabelle
			$vorhanden = zeile_vorhanden ( $mv_id, $nebenkosten_beschriftung );
			if (! $vorhanden) {
				$db_abfrage = "INSERT INTO $tabelle_in_gross VALUES (NULL, '$letzte_tab_id', '$mv_id', '$nebenkosten_beschriftung', '$mietvertrag_von', '0000-00-00', '$nebenkosten_summe', '1')";
				echo "<br>DB = $db_abfrage<br>";
				$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
			}
		}
		if (! empty ( $heizkosten_summe ) && $heizkosten_summe != '0,00' && $heizkosten_summe != '.00') {
			// ####letzte id der tabelle
			
			$akt_id->last_id ( $tabelle ); // Objektwert zuweisen
			$letzte_tab_id = $akt_id->id; // Letzte id
			$letzte_tab_id = $letzte_tab_id + 1; // Letzte id um 1 erhöhen
			                                   // ####letzte id der tabelle
			$vorhanden = zeile_vorhanden ( $mv_id, $heizkosten_beschriftung );
			if (! $vorhanden) {
				$db_abfrage = "INSERT INTO $tabelle_in_gross VALUES (NULL, '$letzte_tab_id', '$mv_id', '$heizkosten_beschriftung', '$mietvertrag_von', '0000-00-00', '$heizkosten_summe', '1')";
				echo "<br>DB = $db_abfrage<br>";
				$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
			}
		}
		
		echo "zeile $i aus $tabelle importiert<br>";
	}
}

import_me ( mietentwicklung );

// import_me(objekt);
// import_me(haus);
// import_me(einheit);
// import_me(person);
function nummer_komma2punkt($nummer) {
	$nummer_arr = explode ( ",", $nummer );
	if (! isset ( $nummer_arr [1] )) {
		$nummer = "" . $nummer_arr [0] . ".00";
	} else {
		$nummer = "" . $nummer_arr [0] . "." . $nummer_arr [1] . "";
	}
	return $nummer;
}
function zeile_vorhanden($mietvertrag_id, $kostenkat) {
	$result = mysql_query ( "SELECT * FROM MIETENTWICKLUNG WHERE MIETENTWICKLUNG_AKTUELL='1' && MIETVERTRAG_ID='$mietvertrag_id' && KOSTENKATEGORIE='$kostenkat' " );
	$numrows = mysql_numrows ( $result );
	if ($numrows < 1) {
		return false;
	} else {
		return true;
	}
}
function kaution_als_detail($mv_id, $betrag) {
	$akt_id = new ids ();
	$akt_id->last_id ( 'detail' ); // Objektwert zuweisen
	$letzte_tab_id = $akt_id->id; // Letzte id
	$letzte_tab_id = $letzte_tab_id + 1; // Letzte id um 1 erhöhen
	/* KAUTION */
	$db_abfrage = "INSERT INTO DETAIL VALUES (NULL, '$letzte_tab_id', 'Kaution', '$betrag', '', '1', 'MIETVERTRAG', '$mv_id')";
	echo "<br>DB = $db_abfrage<br>";
	$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
}
function teilnahme_einzug_hinzu($mv_id, $kontoinhaber, $kontonummer, $blz, $bankname, $einzugsart) {
	$akt_id = new ids ();
	$akt_id->last_id ( 'detail' ); // Objektwert zuweisen
	$letzte_tab_id = $akt_id->id; // Letzte id
	$letzte_tab_id = $letzte_tab_id + 1; // Letzte id um 1 erhöhen
	
	/* Einzugserm JA */
	$db_abfrage = "INSERT INTO DETAIL VALUES (NULL, '$letzte_tab_id', 'Einzugsermächtigung', 'JA', '', '1', 'MIETVERTRAG', '$mv_id')";
	echo "<br>DB = $db_abfrage<br>";
	$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
	
	/* Kontoinhaber */
	$akt_id->last_id ( 'detail' ); // Objektwert zuweisen
	$letzte_tab_id = $akt_id->id; // Letzte id
	$letzte_tab_id = $letzte_tab_id + 1; // Letzte id um 1 erhöhen
	$db_abfrage = "INSERT INTO DETAIL VALUES (NULL, '$letzte_tab_id', 'Kontoinhaber-AutoEinzug', '$kontoinhaber', '', '1', 'MIETVERTRAG', '$mv_id')";
	echo "<br>DB = $db_abfrage<br>";
	$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
	
	/* Kontonummer */
	$akt_id->last_id ( 'detail' ); // Objektwert zuweisen
	$letzte_tab_id = $akt_id->id; // Letzte id
	$letzte_tab_id = $letzte_tab_id + 1; // Letzte id um 1 erhöhen
	$db_abfrage = "INSERT INTO DETAIL VALUES (NULL, '$letzte_tab_id', 'Kontonummer-AutoEinzug', '$kontonummer', '', '1', 'MIETVERTRAG', '$mv_id')";
	echo "<br>DB = $db_abfrage<br>";
	$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
	
	/* BLZ */
	$akt_id->last_id ( 'detail' ); // Objektwert zuweisen
	$letzte_tab_id = $akt_id->id; // Letzte id
	$letzte_tab_id = $letzte_tab_id + 1; // Letzte id um 1 erhöhen
	$db_abfrage = "INSERT INTO DETAIL VALUES (NULL, '$letzte_tab_id', 'BLZ-AutoEinzug', '$blz', '', '1', 'MIETVERTRAG', '$mv_id')";
	echo "<br>DB = $db_abfrage<br>";
	$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
	
	/* Bankname */
	$akt_id->last_id ( 'detail' ); // Objektwert zuweisen
	$letzte_tab_id = $akt_id->id; // Letzte id
	$letzte_tab_id = $letzte_tab_id + 1; // Letzte id um 1 erhöhen
	$db_abfrage = "INSERT INTO DETAIL VALUES (NULL, '$letzte_tab_id', 'Bankname-AutoEinzug', '$bankname', '', '1', 'MIETVERTRAG', '$mv_id')";
	echo "<br>DB = $db_abfrage<br>";
	$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
	
	/* Einzugsart */
	$akt_id->last_id ( 'detail' ); // Objektwert zuweisen
	$letzte_tab_id = $akt_id->id; // Letzte id
	$letzte_tab_id = $letzte_tab_id + 1; // Letzte id um 1 erhöhen
	$db_abfrage = "INSERT INTO DETAIL VALUES (NULL, '$letzte_tab_id', 'Autoeinzugsart', '$einzugsart', '', '1', 'MIETVERTRAG', '$mv_id')";
	echo "<br>DB = $db_abfrage<br>";
	$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
}

?>
