<?php

// ///////////////////////////////////////////////////////
// // DTAUS PHP KLASSE V1.2f ////
// // Fixed for g*Sales ////
// // on 20.08.2006 by Daniel Odoj and Torsten Urbas ////
// // www.glowfish.de ////
// // ////
// // based on version 1.0 from ////
// // 05/2004 - GÜKHAN SIRIN ////
// // WWW.G82.DE ////
// // madg /at/ g82 /./ de ////
// ///////////////////////////////////////////////////////
class dtaus {
	
	// Initialisierung
	
	// Daten des Lastschrifteinreichenden
	var $meinkonto;
	var $meineblz;
	var $meinname;
	// Checksummen
	var $check_anzahl;
	var $check_blz;
	var $check_konto;
	var $check_summe;
	// Lastschriften (Array)
	var $lastschrift;
	function meineDaten($name, $blz, $konto) {
		$name = $this->umlautundgross ( $name );
		$blz = $this->remLeerzeichen ( $blz );
		$konto = $this->remLeerzeichen ( $konto );
		$this->meinkonto = $konto;
		$this->meineblz = $blz;
		$this->meinname = $name;
	}
	function lastschrift($name, $blz, $konto, $betrag, $zweck1 = '', $zweck2 = '', $zweck3 = '') {
		$name = $this->umlautundgross ( $name );
		$blz = $this->remLeerzeichen ( $blz );
		$konto = $this->remLeerzeichen ( $konto );
		$betrag = $this->betrag ( $betrag );
		$zweck1 = $this->leerzeichen ( 27, $zweck1 );
		$zweck2 = $this->leerzeichen ( 27, $zweck2 );
		$zweck3 = $this->leerzeichen ( 27, $zweck3 );
		$this->lastschrift [] = array (
				$name,
				$blz,
				$konto,
				$betrag,
				$zweck1,
				$zweck2,
				$zweck3 
		);
		
		// checksummen hochaddieren
		$this->check_anzahl ++;
		$this->check_blz = $this->check_blz + $blz;
		$this->check_konto = $this->check_konto + $konto;
		$this->check_summe = $this->check_summe + $betrag;
	}
	function doSome() {
		// Datensatz A
		// Header
		$o = '0128ALK';
		$o .= $this->meineblz;
		$o .= $this->nullen ( 8 );
		$o .= $this->leerzeichen ( 27, $this->meinname );
		$o .= date ( "dmy" );
		// Die Date Funktion muss ("dmy") sein und nicht (dmy)
		$o .= $this->leerzeichen ( 4 );
		$o .= $this->nullen ( 10, $this->meinkonto );
		$o .= $this->nullen ( 10 );
		$o .= $this->leerzeichen ( 15 );
		$o .= $this->leerzeichen ( 8 );
		$o .= $this->leerzeichen ( 24 );
		$o .= '1';
		
		// Datensatz C
		// Lastschriften
		foreach ( $this->lastschrift as $value ) {
			$anz_zweck = 1;
			if (trim ( $value [5] ))
				$anz_zweck ++;
			if (trim ( $value [6] ))
				$anz_zweck ++;
			$o .= '0';
			$o .= 158 + ($anz_zweck * 29) . 'C';
			// Satzlaenge ^^^ muss insgesamt 245 ergeben. 158 + (3*29) = 245! passt. 29 mal 3 Verwendungszwecke = 87..
			$o .= $this->meineblz;
			$o .= $value [1];
			$o .= $this->nullen ( 10, $value [2] );
			$o .= $this->nullen ( 13 );
			$o .= '05000';
			$o .= $this->leerzeichen ( 1 );
			// Betrag in DM, muss nach Einführung des Euro 00000000000 sein. Feld C9
			// $o .= $this->nullen(11, $value[3]);
			$o .= $this->nullen ( 11 );
			$o .= $this->meineblz;
			$o .= $this->nullen ( 10, $this->meinkonto );
			// Betrag in Euro, Feld C12
			$o .= $this->nullen ( 11, $value [3] );
			$o .= $this->leerzeichen ( 3 );
			$o .= $this->leerzeichen ( 27, $value [0] );
			$o .= $this->leerzeichen ( 8 );
			// soweit in Ordnung
			$o .= $this->leerzeichen ( 27, $this->meinname );
			$o .= $this->leerzeichen ( 27, $value [4] );
			$o .= '1';
			$o .= $this->leerzeichen ( 2 );
			$o .= $this->nullen ( 2, $anz_zweck - 1 );
			
			if ($anz_zweck > 1) {
				$o .= '02';
				$o .= $this->leerzeichen ( 27, $value [5] );
			} else {
				$o .= $this->leerzeichen ( 2 );
				$o .= $this->leerzeichen ( 27 );
			}
			
			if ($anz_zweck > 2) {
				$o .= '02';
				$o .= $this->leerzeichen ( 27, $value [6] );
			} else {
				$o .= $this->leerzeichen ( 2 );
				$o .= $this->leerzeichen ( 27 );
			}
			
			$o .= $this->leerzeichen ( 11 );
		}
		
		// Datensatz E
		// Footer
		$o .= '0128E';
		$o .= $this->leerzeichen ( 5 );
		$o .= $this->nullen ( 7, $this->check_anzahl );
		$o .= $this->nullen ( 13 );
		$o .= $this->nullen ( 17, $this->check_konto );
		$o .= $this->nullen ( 17, $this->check_blz );
		$o .= $this->nullen ( 13, $this->check_summe );
		$o .= $this->leerzeichen ( 51 );
		
		return $o;
	}
	function doSome1($kennzeichen, $zahlungsart) {
		// Datensatz A
		// Header
		$anfang = '0128A' . $kennzeichen;
		
		// $o = '0128ALK';
		$o = $anfang;
		$o .= $this->meineblz;
		$o .= $this->nullen ( 8 );
		$o .= $this->leerzeichen ( 27, $this->meinname );
		$o .= date ( "dmy" );
		// Die Date Funktion muss ("dmy") sein und nicht (dmy)
		$o .= $this->leerzeichen ( 4 );
		$o .= $this->nullen ( 10, $this->meinkonto );
		$o .= $this->nullen ( 10 );
		$o .= $this->leerzeichen ( 15 );
		$o .= $this->leerzeichen ( 8 );
		$o .= $this->leerzeichen ( 24 );
		$o .= '1';
		
		// Datensatz C
		// Lastschriften
		foreach ( $this->lastschrift as $value ) {
			$anz_zweck = 1;
			if (trim ( $value [5] ))
				$anz_zweck ++;
			if (trim ( $value [6] ))
				$anz_zweck ++;
			$o .= '0';
			$o .= 158 + ($anz_zweck * 29) . 'C';
			// Satzlaenge ^^^ muss insgesamt 245 ergeben. 158 + (3*29) = 245! passt. 29 mal 3 Verwendungszwecke = 87..
			$o .= $this->meineblz;
			$o .= $value [1];
			$o .= $this->nullen ( 10, $value [2] );
			$o .= $this->nullen ( 13 );
			// $o .= '05000';
			$o .= $zahlungsart;
			$o .= $this->leerzeichen ( 1 );
			// Betrag in DM, muss nach Einführung des Euro 00000000000 sein. Feld C9
			// $o .= $this->nullen(11, $value[3]);
			$o .= $this->nullen ( 11 );
			$o .= $this->meineblz;
			$o .= $this->nullen ( 10, $this->meinkonto );
			// Betrag in Euro, Feld C12
			$o .= $this->nullen ( 11, $value [3] );
			$o .= $this->leerzeichen ( 3 );
			$o .= $this->leerzeichen ( 27, $value [0] );
			$o .= $this->leerzeichen ( 8 );
			// soweit in Ordnung
			$o .= $this->leerzeichen ( 27, $this->meinname );
			$o .= $this->leerzeichen ( 27, $value [4] );
			$o .= '1';
			$o .= $this->leerzeichen ( 2 );
			$o .= $this->nullen ( 2, $anz_zweck - 1 );
			
			if ($anz_zweck > 1) {
				$o .= '02';
				$o .= $this->leerzeichen ( 27, $value [5] );
			} else {
				$o .= $this->leerzeichen ( 2 );
				$o .= $this->leerzeichen ( 27 );
			}
			
			if ($anz_zweck > 2) {
				$o .= '02';
				$o .= $this->leerzeichen ( 27, $value [6] );
			} else {
				$o .= $this->leerzeichen ( 2 );
				$o .= $this->leerzeichen ( 27 );
			}
			
			$o .= $this->leerzeichen ( 11 );
		}
		
		// Datensatz E
		// Footer
		$o .= '0128E';
		$o .= $this->leerzeichen ( 5 );
		$o .= $this->nullen ( 7, $this->check_anzahl );
		$o .= $this->nullen ( 13 );
		$o .= $this->nullen ( 17, $this->check_konto );
		$o .= $this->nullen ( 17, $this->check_blz );
		$o .= $this->nullen ( 13, $this->check_summe );
		$o .= $this->leerzeichen ( 51 );
		
		return $o;
	}
	
	// //////////////////////////////////////////////////
	// Sub Funktionen zur Verarbeitung
	// //////////////////////////////////////////////////
	function umlautundgross($wort) {
		$tmp = strtoupper ( $wort );
		$suche = array (
				'Ä',
				'Ö',
				'Ü',
				'ß',
				'ä',
				'ö',
				'ü',
				'ß' 
		);
		$ersetze = array (
				'AE',
				'OE',
				'UE',
				'SS',
				'AE',
				'OE',
				'UE',
				'SS' 
		);
		$ret = str_replace ( $suche, $ersetze, $tmp );
		return $ret;
	}
	function leerzeichen($anzahl, $wort = '') {
		$count = 1;
		$wort = $this->umlautundgross ( $wort );
		$count = $count + strlen ( $wort );
		if ($count > $anzahl) {
			// zu lang -> beschneiden
			$wort = substr ( $wort, 0, $anzahl );
		} else {
			while ( $count <= $anzahl ) {
				$count ++;
				$wort = $wort . ' ';
			}
		}
		return $wort;
	}
	function nullen($anzahl, $wort = '') {
		$count = strlen ( $wort );
		while ( $count < $anzahl ) {
			$count ++;
			$wort = '0' . $wort;
		}
		return $wort;
	}
	function remLeerzeichen($wort) {
		$tmp = str_replace ( ' ', '', $wort );
		return $tmp;
	}
	function betrag($betrag) {
		$betrag = number_format ( $betrag, 2, "", "" );
		return $betrag;
	}
}

?> 
