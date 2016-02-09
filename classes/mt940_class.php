<?php

/* MT940 Klasse */
class mt940 {
	var $import_filename;
	function feld_definition($datensatz = '') {
		$datensatz = ':20:00U7A2I9W8LC4GWA
:25:10080000/0580400002888
:28C:00039/00001
:60F:C130227EUR19921,68
:61:1302280228D1,5NMSCNONREF//97190/003
/OCMT/EUR1,5/
:86:805?00ABSCHLUSS?1097190?20AKTO:10080000 0580400002EUR?21ABZR: 01.
02.2013-28.02.2013?22SPES: 1,5 S?23SALD: 1,5 S
:61:1302280228D1263,NMSC001000003326//97186/034
/OCMT/EUR1263,/
:86:005?00LASTSCHRIFT EINZUGSERM.?1097186?20IHRE KONTONR. 0001508733 
?21AVIS VOM 27.02.13 ZU ?22ZAHLUNGSBELEG 1000003326 ?23KDN-REF 00
1000003326 BERLUS?24HV INH.WOLFGANG WEHR?3010010111?311264852500?
32ALBA CONSULTING GMBH?34000
:61:1302280228C15,NTRFNONREF//97261/039
/OCMT/EUR15,/
:86:052?00DAUERAUFTRAGSGUTSCHRIFT?1097261?20MIETE WOHNUNGSNUMMER II/9
04?21WOLFGANG WEHRHEIM ODER ?22WILFRIED WEHRHEIM?3010040000?31700
240500?32STEPHAN DIENECK UND?33BEATE DIENECK?34000
:61:1302280228C35,NTRFNONREF//97261/039
/OCMT/EUR35,/
:86:052?00DAUERAUFTRAGSGUTSCHRIFT?1097261?20MIETE KELLER MIETERNR. ?2
1II-908 WOLFGANG WEHRHEIM ?22ODER WILFRIED WEHRHEIM?3010040000?31
700240500?32STEPHAN DIENECK UND?33BEATE DIENECK?34000
:61:1302280228C123,33NTRF600000000000//97261/039
/OCMT/EUR123,33/
:86:051?00UEBERWEISUNGSGUTSCHRIFT?1097261?20MIETE MIETERNR-2/265 ?21O
NB-REF 600000000000 BLZ ?2270020270 BERLUS ?23HAUSVERWALTUNG?3010
020890?31601816920?32MIX GUENTER?34000
:61:1302280228C151,56NTRF038010531606//97261/039
/OCMT/EUR151,56/
:86:051?00UEBERWEISUNGSGUTSCHRIFT?1097261?20WHG II-235 S.BRAETZ ?2138
010531606/1702056892644 ?22KDN-REF 038010531606 ?23BERLUS, HV?307
6000000?3176001601?32BUNDESAGENTUR FUER ARBEIT?34000
:61:1302280228C301,47NTRFNONREF//97261/039
/OCMT/EUR301,47/
:86:052?00DAUERAUFTRAGSGUTSCHRIFT?1097261?20MIETE WOHNUNGSNUMMER II/2
57?21WOLFGANG WEHRHEIM ODER ?22WILFRIED WEHRHEIM?3010040000?31700
240500?32STEPHAN DIENECK UND?33BEATE DIENECK?34000
:62M:C130228EUR19283,54
:64:C130228EUR20537,36
:20:00U7A2I9W8LC4GWA
:25:10080000/0580400002888
:28C:00039/00002
:60M:C130228EUR19283,54
:61:1302280228C366,7NTRFNONREF//97261/039
/OCMT/EUR366,7/
:86:052?00DAUERAUFTRAGSGUTSCHRIFT?1097261?20MIETE WOHNUNGSNUMMER II/2
55?21WOLFGANG WEHRHEIM ODER ?22WILFRIED WEHRHEIM?3010040000?31700
240500?32STEPHAN DIENECK UND?33BEATE DIENECK?34000
:61:1302280228C418,62NTRF021310269614//97261/039
/OCMT/EUR418,62/
:86:051?00UEBERWEISUNGSGUTSCHRIFT?1097261?20II-262, ASANI 418,62 ?21S
1320110269614 KDN-REF ?22021310269614 BERLUS ?23HAUSVERWALTUNG?30
10000000?3110001520?32S13?34000
:61:1302280228C467,NTRF032010084608//97261/039
/OCMT/EUR467,/
:86:051?00UEBERWEISUNGSGUTSCHRIFT?1097261?20II-272 HAMZIBEGANOVIC ?21
32010084608/1702051760344 ?22KDN-REF 032010084608 BERLUS?23HAUSVE
RWALTUNG?3076000000?3176001601?32BUNDESAGENTUR FUER ARBEIT?34000
:62F:C130228EUR20535,86
:64:C130228EUR20537,36';
		
		$this->anzahl_61 = substr_count ( $datensatz, ':61:' );
		$this->anzahl_86 = substr_count ( $datensatz, ':86:' );
		if ($this->anzahl_61 != $this->anzahl_86) {
			echo "Datensatz fehlerhaft 61!=86";
			die ();
		}
		echo "Anzahl 61 = $this->anzahl_61<br>";
		echo "Anzahl 86 = $this->anzahl_86<br>";
		
		$this->pos_20 = strpos ( $datensatz, ':20:' );
		$this->pos_25 = strpos ( $datensatz, ':25:' );
		$this->laenge_20 = $this->pos_25 - $this->pos_20 - 4;
		// echo "Poz. :20: $this->pos_20<br>";
		// echo "Poz. :25: $this->pos_25<br>";
		// echo "Laenge. :20: $this->laenge_20<br>";
		$daten_20 = substr ( $datensatz, 4, $this->laenge_20 );
		// echo ":20: = $daten_20<br>";
		// $daten[':20:'] = $daten_20;
		$erg [':20:'] = $daten_20;
		
		$this->pos_28c = strpos ( $datensatz, ':28C:' );
		$this->laenge_25 = $this->pos_28c - $this->pos_25 - 4;
		$daten_25 = substr ( $datensatz, $this->pos_25 + 4, $this->laenge_25 );
		// echo ":25: = $daten_25<br>";
		$erg [':25:'] [':25:'] = $daten_25;
		$daten_25_arr = explode ( '/', $daten_25 );
		$konto_nr = $daten_25_arr ['1'];
		$blz = $daten_25_arr ['0'];
		// echo "K: $konto_nr, BLZ: $blz<br>";
		$erg [':25:'] ['KONTONR'] = $konto_nr;
		$erg [':25:'] ['BLZ'] = $blz;
		
		$this->pos_60 = strpos ( $datensatz, ':60' );
		$this->laenge_28c = $this->pos_60 - $this->pos_28c - 5;
		$daten_28c = substr ( $datensatz, $this->pos_28c + 5, $this->laenge_28c );
		$daten_28c_arr = explode ( '/', $daten_28c );
		$daten_28c_auszugsnr = $daten_28c_arr [0];
		$daten_28c_blatt = $daten_28c_arr [1];
		echo "Auszugsnummer $daten_28c_auszugsnr Blatt $daten_28c_blatt<br>";
		$erg [':28C:'] [':28C'] = $daten_28c;
		$erg [':28C:'] ['A_NR'] = $daten_28c_auszugsnr;
		$erg [':28C:'] ['BLATT'] = $daten_28c_blatt;
		
		$this->pos_61 = strpos ( $datensatz, ':61' );
		$this->laenge_60 = $this->pos_61 - $this->pos_60 - 15;
		$_60_typ = substr ( $datensatz, $this->pos_60 + 3, 1 );
		// echo "60-TYP $_60_typ<br>";
		$erg [':60:'] ['TYP'] = $_60_typ;
		/* ':60F:D090928EUR493,38' */
		$_60_sub1 = substr ( $datensatz, $this->pos_60 + 5, 1 );
		// echo "60-SUB1 $_60_sub1<br>";
		$erg [':60:'] ['SUB1'] = $_60_sub1;
		$_60_sub2 = substr ( $datensatz, $this->pos_60 + 6, 6 );
		// echo "60-SUB2 $_60_sub2<br>";
		$erg [':60:'] ['SUB2'] = $_60_sub2;
		$_60_sub3 = substr ( $datensatz, $this->pos_60 + 12, 3 );
		// echo "60-SUB3 $_60_sub3<br>";
		$erg [':60:'] ['SUB3'] = $_60_sub3;
		$_60_sub4 = substr ( $datensatz, $this->pos_60 + 15, $this->laenge_60 );
		// echo "60-SUB4 $_60_sub4<br>";
		$erg [':60:'] ['SUB4'] = $_60_sub4;
		
		/* Wenn nur eine 61 Zeile */
		if ($this->anzahl_61 == 1) {
			$this->pos_86 = strpos ( $datensatz, ':86:' );
			$this->laenge_61 = $this->pos_86 - $this->pos_61;
			// echo "Laenge 61 = $this->laenge_61<br>";
			$_61_sub1 = substr ( $datensatz, $this->pos_61 + 4, 6 );
			// echo "61-SUB1 $_61_sub1<br>";
			$erg [':61:'] ['SUB1'] = $_61_sub1;
			$_61_sub2 = substr ( $datensatz, $this->pos_61 + 10, 4 );
			// echo "61-SUB2 $_61_sub2<br>";
			$erg [':61:'] ['SUB2'] = $_61_sub2;
			$_61_sub3 = substr ( $datensatz, $this->pos_61 + 14, 3 );
			
			/* RDR */
			if (ctype_alpha ( $_61_sub3 )) {
				$_61_sub3_wert = substr ( $datensatz, $this->pos_61 + 14, 2 );
				$_61_sub4_wert = substr ( $datensatz, $this->pos_61 + 16, 1 );
				// echo "1: $_61_sub3_wert $_61_sub4_wert<br>";
				// $erg[':61']['SUB3'] = $_61_sub3_wert;
				// $erg[':61']['SUB4'] = $_61_sub4_wert;
				$pos = 17;
			}
			
			if (! ctype_alpha ( $_61_sub3 )) {
				// echo "2: $_61_sub3<br>";
				$_61_sub3_wert = substr ( $datensatz, $this->pos_61 + 14, 2 );
				// echo "3: $_61_sub3_wert<br>";
				// $erg[':61']['SUB3'] = $_61_sub3_wert;
				if ($_61_sub3_wert == 'RC' || $_61_sub3_wert == 'RD') {
					$_61_sub3_wert = substr ( $datensatz, $this->pos_61 + 14, 2 );
					$_61_sub4_wert = substr ( $datensatz, $this->pos_61 + 16, 1 );
					// $erg[':61']['SUB3'] = $_61_sub3_wert;
					// $erg[':61']['SUB4'] = $_61_sub4_wert;
					
					$pos = 17;
					if (ctype_digit ( $_61_sub4_wert )) {
						$_61_sub4_wert = 'NICHT GESETZT';
						// $erg[':61']['SUB4'] = $_61_sub4_wert;
						$pos = 16;
					}
				} else {
					$_61_sub3_wert = substr ( $datensatz, $this->pos_61 + 14, 1 );
					$_61_sub4_wert = substr ( $datensatz, $this->pos_61 + 15, 1 );
					// $erg[':61:']['SUB3'] = $_61_sub3_wert;
					// $erg[':61:']['SUB4'] = $_61_sub4_wert;
					$pos = 16;
				}
			}
			
			$erg [':61:'] ['SUB3'] = $_61_sub3_wert;
			$erg [':61:'] ['SUB4'] = $_61_sub4_wert;
			
			// echo "61-SUB3 $_61_sub3_wert <br>";
			// echo "61-SUB4 $_61_sub4_wert <br>";
			
			$_61_sub5_ALL = substr ( $datensatz, $this->pos_61 + $pos, 15 );
			$this->pos_N = strpos ( $_61_sub5_ALL, 'N' );
			// echo "$this->pos_N<br>";
			$pos = $pos + $this->pos_N;
			$_61_sub5 = substr ( $_61_sub5_ALL, 0, $this->pos_N );
			$erg [':61:'] ['SUB5'] = $_61_sub5;
			// echo "61_sub5 $_61_sub5<br>";
			$_61_sub6 = substr ( $_61_sub5_ALL, $this->pos_N, 4 );
			$pos = $pos + 4;
			$erg [':61:'] ['SUB6'] = $_61_sub6;
			// echo "61_sub6 $_61_sub6<br>";
			
			$_61_ZEILE = substr ( $datensatz, $this->pos_61, $this->laenge_61 );
			$akt_pos = $this->pos_61 + $pos;
			// echo "<h1>$_61_ZEILE ###AKT POS $pos####</h1><br>";
			$rest = substr ( $_61_ZEILE, $pos, $this->laenge_61 );
			
			$this->pos_trenn8 = strpos ( $rest, '//' );
			if ($this->pos_trenn8) {
				$_61_sub7 = substr ( $rest, 0, $this->pos_trenn8 );
				// echo "Postoji // sub7 = $_61_sub7<br>";
				$erg [':61:'] ['SUB7'] = $_61_sub7;
				// echo "REST $rest<br>";
			} else {
				$_61_sub7 = 'NULL';
				// echo "NE Postoji // sub7 = $_61_sub7<br>";
				$erg [':61:'] ['SUB7'] = '';
			}
			
			$laenge_rest = strlen ( $rest );
			$rest = substr ( $rest, $this->pos_trenn8, $laenge_rest );
			// echo "REST $rest<br>";
			$this->pos_betrag = strpos ( $rest, '/OCMT/' );
			
			if (! $this->pos_betrag) {
				$this->pos_betrag = strpos ( $rest, '/CHGS/' );
			}
			$_61_sub8 = substr ( $rest, 2, $this->pos_betrag - 2 ); // 2 wegen // trennzeichen
			                                                   // echo "sub8 = $_61_sub8 P betrag = $this->pos_betrag<br>";
			
			$rest = substr ( $rest, $this->pos_betrag, 34 );
			// echo "REST3 $rest<br>";
			$waehrung_iso = substr ( $rest, 6, 3 );
			// echo "WÄHRUNG $waehrung_iso<br>";
			$laenge_rest = strlen ( $rest );
			$rest = substr ( $rest, 9, $laenge_rest );
			// echo "REST3 $rest<br>";
			$laenge_rest = strlen ( $rest );
			$betrag = substr ( $rest, 0, $laenge_rest );
			// echo "BETRAG $betrag<br>";
			$erg [':61:'] ['SUB8'] = $betrag . ' ' . $waehrung_iso;
			
			// ########86###########
			
			$this->pos_62 = strpos ( $datensatz, ':62' );
			$laenge_86 = $this->pos_62 - $this->pos_86;
			$_86_ZEILE = substr ( $datensatz, $this->pos_86, $laenge_86 );
			// echo "Z86 = <b>$_86_ZEILE</b><br>";
			$gvc = substr ( $_86_ZEILE, 4, 3 );
			// echo "GVC = $gvc<br>";
			$erg [':86:'] ['GVC'] = $gvc;
			$trennzeichen = substr ( $_86_ZEILE, 7, 1 );
			// echo "TRENNZEICHEN $trennzeichen<br>";
			$_86_array = explode ( "$trennzeichen", $_86_ZEILE );
			// echo '<pre>';
			// print_r($_86_array);
			$anzahl_el = count ( $_86_array );
			for($a = 0; $a < $anzahl_el; $a ++) {
				$len = strlen ( $_86_array [$a] );
				$key = substr ( $_86_array [$a], 0, 2 );
				$_86_keys_arr [$a] [$key] ['KEYNAME'] = $key;
				$_86_keys_arr [$a] [$key] ['VALUE'] = substr ( $_86_array [$a], 2, $len - 2 );
			}
			unset ( $_86_keys_arr [0] );
			$_86_keys_arr = array_values ( $_86_keys_arr );
			// print_r($_86_keys_arr);
			$erg [':86:'] ['KEYS'] = $_86_keys_arr;
		}  // #end if 1x61 und 1x86
else {
			echo "anzahl mehr";
			
			$datensatz_neu = $datensatz;
			echo "<b>$datensatz_neu</b><br><br>";
			for($c = 0; $c < $this->anzahl_61; $c ++) {
				$this->pos_61 = strpos ( $datensatz_neu, ':61' ); // 0
				$this->pos_61_end = strpos ( $datensatz_neu, ':86:' ); // 10
				$this->laenge_61 = $this->pos_61_end - $this->pos_61 - 4; // 10 - 0 - 4 = 6
				$_61_sub1 = substr ( $datensatz_neu, $this->pos_61 + 4, $this->laenge_61 ); // 4 +6 = 10
				                                                                         // $erg[':61:'][$c]['SUB1'] = $_61_sub1;
				$erg [':61:'] [$c] = $this->extract_61 ( $datensatz_neu );
				$datensatz_neu = substr ( $datensatz_neu, $this->pos_61_end );
				
				// ###
				$this->pos_86 = strpos ( $datensatz_neu, ':86:' ); // 0
				unset ( $this->pos_86_end );
				$this->pos_86_end = strpos ( $datensatz_neu, ':61' ); // 10
				if (! $this->pos_86_end) {
					$this->pos_86_end = strpos ( $datensatz_neu, ':62' ); // 10
				}
				$this->laenge_86 = $this->pos_86_end - $this->pos_86 - 4; // 10 - 0 - 4 = 6
				$_86_sub1 = substr ( $datensatz_neu, $this->pos_86 + 4, $this->laenge_86 ); // 4 +6 = 10
				                                                                         // $erg[':86:'][$c]['SUB1'] = $_86_sub1;
				$erg [':86:'] [$c] = $this->extract_86 ( $datensatz_neu );
				$datensatz_neu = substr ( $datensatz_neu, $this->pos_86_end );
			}
		}
		
		// ######62###############
		$this->pos_64 = strpos ( $datensatz, ':64:' );
		// echo "<b>P1 $this->pos_end62</b>";
		if (! $this->pos_64) {
			$this->pos_end62 = strlen ( $datensatz );
			$this->pos_62 = strpos ( $datensatz, ':62' );
			// echo "<b>P2 $this->pos_end62 $this->pos_62</b>";
		}
		$this->laenge_62 = $this->pos_end62 - $this->pos_62 - 15;
		$_62_typ = substr ( $datensatz, $this->pos_62 + 3, 1 );
		$erg [':62:'] ['TYP'] = $_62_typ;
		// echo "62-TYP $_62_typ<br>";
		$_62_sub1 = substr ( $datensatz, $this->pos_62 + 5, 1 );
		// echo "62-SUB1 $_62_sub1<br>";
		$erg [':62:'] ['SUB1'] = $_62_sub1;
		$_62_sub2 = substr ( $datensatz, $this->pos_62 + 6, 6 );
		// echo "62-SUB2 $_62_sub2<br>";
		$erg [':62:'] ['SUB2'] = $_62_sub2;
		$_62_sub3 = substr ( $datensatz, $this->pos_62 + 12, 3 );
		// echo "62-SUB3 $_62_sub3<br>";
		$erg [':62:'] ['SUB3'] = $_62_sub3;
		$_62_sub4 = substr ( $datensatz, $this->pos_62 + 15, 15 );
		// echo "62-SUB4 $_62_sub4<br>";
		$erg [':62:'] ['SUB4'] = $_62_sub4;
		
		// ########64#############
		
		if ($this->pos_64) {
			$this->pos_end64 = strlen ( $datensatz );
			$this->laenge_64 = pos_end64 - $this->pos_64 - 15;
			$_64_sub1 = substr ( $datensatz, $this->pos_64 + 4, 1 );
			echo "64-SUB1 $_64_sub1<br>";
			$_64_sub2 = substr ( $datensatz, $this->pos_64 + 5, 6 );
			echo "64-SUB2 $_64_sub2<br>";
			$_64_sub3 = substr ( $datensatz, $this->pos_64 + 11, 3 );
			echo "64-SUB3 $_64_sub3<br>";
			$_64_sub4 = substr ( $datensatz, $this->pos_64 + 14, 15 );
			echo "64-SUB4 $_64_sub4<br>";
		}
		
		echo '<pre>';
		// print_r($erg);
		$keys = array_keys ( $erg );
		// print_r($keys);
		$anz = count ( $erg [':86:'] );
		
		// print_r($erg[':86:']);
		for($a = 0; $a < $anz; $a ++) {
			// print_r($erg[':86:'][$a]['KEYS']);
			$paket = $erg [':86:'] [$a] ['KEYS'];
			$anz1 = count ( $paket );
			for($b = 0; $b < $anz1; $b ++) {
				if (! empty ( $paket [$b] ['20'] )) {
					echo $paket [$b] ['20'] ['VALUE'] . ' ' . $paket [$b] ['32'] ['VALUE'] . '<hr>';
				}
			}
			// echo "<hr>";
		}
	} // end function
	function extract_61($datensatz) {
		$this->pos_86 = strpos ( $datensatz, ':86:' );
		$this->laenge_61 = $this->pos_86 - $this->pos_61;
		// echo "Laenge 61 = $this->laenge_61<br>";
		$_61_sub1 = substr ( $datensatz, $this->pos_61 + 4, 6 );
		// echo "61-SUB1 $_61_sub1<br>";
		$erg ['SUB1'] = $_61_sub1;
		$_61_sub2 = substr ( $datensatz, $this->pos_61 + 10, 4 );
		// echo "61-SUB2 $_61_sub2<br>";
		$erg ['SUB2'] = $_61_sub2;
		$_61_sub3 = substr ( $datensatz, $this->pos_61 + 14, 3 );
		
		/* RDR */
		if (ctype_alpha ( $_61_sub3 )) {
			$_61_sub3_wert = substr ( $datensatz, $this->pos_61 + 14, 2 );
			$_61_sub4_wert = substr ( $datensatz, $this->pos_61 + 16, 1 );
			// echo "1: $_61_sub3_wert $_61_sub4_wert<br>";
			// $erg[':61']['SUB3'] = $_61_sub3_wert;
			// $erg[':61']['SUB4'] = $_61_sub4_wert;
			$pos = 17;
		}
		
		if (! ctype_alpha ( $_61_sub3 )) {
			// echo "2: $_61_sub3<br>";
			$_61_sub3_wert = substr ( $datensatz, $this->pos_61 + 14, 2 );
			// echo "3: $_61_sub3_wert<br>";
			// $erg[':61']['SUB3'] = $_61_sub3_wert;
			if ($_61_sub3_wert == 'RC' || $_61_sub3_wert == 'RD') {
				$_61_sub3_wert = substr ( $datensatz, $this->pos_61 + 14, 2 );
				$_61_sub4_wert = substr ( $datensatz, $this->pos_61 + 16, 1 );
				// $erg[':61']['SUB3'] = $_61_sub3_wert;
				// $erg[':61']['SUB4'] = $_61_sub4_wert;
				
				$pos = 17;
				if (ctype_digit ( $_61_sub4_wert )) {
					$_61_sub4_wert = 'NICHT GESETZT';
					// $erg[':61']['SUB4'] = $_61_sub4_wert;
					$pos = 16;
				}
			} else {
				$_61_sub3_wert = substr ( $datensatz, $this->pos_61 + 14, 1 );
				$_61_sub4_wert = substr ( $datensatz, $this->pos_61 + 15, 1 );
				// $erg[':61:']['SUB3'] = $_61_sub3_wert;
				// $erg[':61:']['SUB4'] = $_61_sub4_wert;
				$pos = 16;
			}
		}
		
		$erg ['SUB3'] = $_61_sub3_wert;
		$erg ['SUB4'] = $_61_sub4_wert;
		
		// echo "61-SUB3 $_61_sub3_wert <br>";
		// echo "61-SUB4 $_61_sub4_wert <br>";
		
		$_61_sub5_ALL = substr ( $datensatz, $this->pos_61 + $pos, 15 );
		$this->pos_N = strpos ( $_61_sub5_ALL, 'N' );
		// echo "$this->pos_N<br>";
		$pos = $pos + $this->pos_N;
		$_61_sub5 = substr ( $_61_sub5_ALL, 0, $this->pos_N );
		$erg ['SUB5'] = $_61_sub5;
		// echo "61_sub5 $_61_sub5<br>";
		$_61_sub6 = substr ( $_61_sub5_ALL, $this->pos_N, 4 );
		$pos = $pos + 4;
		$erg ['SUB6'] = $_61_sub6;
		// echo "61_sub6 $_61_sub6<br>";
		
		$_61_ZEILE = substr ( $datensatz, $this->pos_61, $this->laenge_61 );
		$akt_pos = $this->pos_61 + $pos;
		// echo "<h1>$_61_ZEILE ###AKT POS $pos####</h1><br>";
		$rest = substr ( $_61_ZEILE, $pos, $this->laenge_61 );
		
		$this->pos_trenn8 = strpos ( $rest, '//' );
		if ($this->pos_trenn8) {
			$_61_sub7 = substr ( $rest, 0, $this->pos_trenn8 );
			// echo "Postoji // sub7 = $_61_sub7<br>";
			$erg ['SUB7'] = $_61_sub7;
			// echo "REST $rest<br>";
		} else {
			$_61_sub7 = 'NULL';
			// echo "NE Postoji // sub7 = $_61_sub7<br>";
			$erg ['SUB7'] = '';
		}
		
		$laenge_rest = strlen ( $rest );
		$rest = substr ( $rest, $this->pos_trenn8, $laenge_rest );
		// echo "REST $rest<br>";
		$this->pos_betrag = strpos ( $rest, '/OCMT/' );
		
		if (! $this->pos_betrag) {
			$this->pos_betrag = strpos ( $rest, '/CHGS/' );
		}
		$_61_sub8 = substr ( $rest, 2, $this->pos_betrag - 2 ); // 2 wegen // trennzeichen
		                                                   // echo "sub8 = $_61_sub8 P betrag = $this->pos_betrag<br>";
		
		$rest = substr ( $rest, $this->pos_betrag, 34 );
		// echo "REST3 $rest<br>";
		$waehrung_iso = substr ( $rest, 6, 3 );
		// echo "WÄHRUNG $waehrung_iso<br>";
		$laenge_rest = strlen ( $rest );
		$rest = substr ( $rest, 9, $laenge_rest );
		// echo "REST3 $rest<br>";
		$laenge_rest = strlen ( $rest );
		$betrag = substr ( $rest, 0, $laenge_rest );
		// echo "BETRAG $betrag<br>";
		$erg ['SUB8'] = $betrag . ' ' . $waehrung_iso;
		return $erg;
	}
	function extract_86($datensatz) {
		unset ( $this->pos_62 );
		$this->pos_62 = strpos ( $datensatz, ':62' );
		$laenge_86 = $this->pos_62 - $this->pos_86;
		$_86_ZEILE = substr ( $datensatz, $this->pos_86, $laenge_86 );
		// echo "Z86 = <b>$_86_ZEILE</b><br>";
		$gvc = substr ( $_86_ZEILE, 4, 3 );
		// echo "GVC = $gvc<br>";
		$erg ['GVC'] = $gvc;
		$trennzeichen = substr ( $_86_ZEILE, 7, 1 );
		// echo "TRENNZEICHEN $trennzeichen<br>";
		$_86_array = explode ( "$trennzeichen", $_86_ZEILE );
		// echo '<pre>';
		// print_r($_86_array);
		$anzahl_el = count ( $_86_array );
		for($a = 0; $a < $anzahl_el; $a ++) {
			$len = strlen ( $_86_array [$a] );
			$key = substr ( $_86_array [$a], 0, 2 );
			$_86_keys_arr [$a] [$key] ['KEYNAME'] = $key;
			$_86_keys_arr [$a] [$key] ['VALUE'] = substr ( $_86_array [$a], 2, $len - 2 );
		}
		unset ( $_86_keys_arr [0] );
		$_86_keys_arr = array_values ( $_86_keys_arr );
		// print_r($_86_keys_arr);
		$erg ['KEYS'] = $_86_keys_arr;
		return $erg;
	}
} // end class

?>