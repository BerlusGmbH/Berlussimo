<?php

class mietentwicklung {
	var $kostenkategorien;
	var $mietvertrag_von;
	var $forderungs_summe_aktuell = 0;
	function get_energieverbrauch($kos_typ, $kos_id, $jahr) {
		$string = "Energieverbrauch lt. Abr. $jahr";
		$result = mysql_query ( "SELECT * FROM MIETENTWICKLUNG WHERE KOSTENTRAEGER_TYP='$kos_typ' && KOSTENTRAEGER_ID = '$kos_id' && MIETENTWICKLUNG_AKTUELL = '1' && KOSTENKATEGORIE='$string' ORDER BY ANFANG ASC" );
		// die("SELECT * FROM MIETENTWICKLUNG WHERE KOSTENTRAEGER_TYP='$kos_typ' && KOSTENTRAEGER_ID = '$kos_id' && MIETENTWICKLUNG_AKTUELL = '1' && KOSTENKATEGORIE='$string' ORDER BY ANFANG ASC");
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			$row = mysql_fetch_assoc ( $result );
			return $row ['BETRAG'];
		} else {
			return '0.00';
		}
	}
	function get_mietentwicklung_infos($mietvertrag_id, $jahr, $monat) {
		$this->get_einzugsdatum ( $mietvertrag_id );
		$this->anzahl_me = 0;
		$result = mysql_query ( "SELECT * FROM MIETENTWICKLUNG WHERE KOSTENTRAEGER_TYP='Mietvertrag' && KOSTENTRAEGER_ID = '$mietvertrag_id' && MIETENTWICKLUNG_AKTUELL = '1' ORDER BY ANFANG ASC" );
		$numrows = mysql_numrows ( $result );
		if (! $numrows) {
			return false;
		} else {
			$a = 0;
			while ( $row = mysql_fetch_assoc ( $result ) ) {
				// $aktuelle_forderung[] = $row;
				
				$this->kostenkategorien [$a] ['KOSTENKATEGORIE'] = $row ['KOSTENKATEGORIE'];
				
				$this->kostenkategorien [$a] ['ANFANG'] = $row ['ANFANG'];
				;
				$this->kostenkategorien [$a] ['ENDE'] = $row ['ENDE'];
				$betrag = $row ['BETRAG'];
				$betrag = number_format ( $betrag, 2, ".", "" );
				$this->kostenkategorien [$a] ['BETRAG'] = $betrag;
				
				$mwst_anteil = $row ['MWST_ANTEIL'];
				$this->kostenkategorien [$a] ['MWST_ANTEIL'] = $mwst_anteil;
				
				$this->forderungs_summe_aktuell = $this->forderungs_summe_aktuell + $betrag;
				$this->forderungs_summe_aktuell = number_format ( $this->forderungs_summe_aktuell, 2, ".", "" );
				$this->anzahl_me ++;
				$a ++;
			}
			/*
			 * echo '<pre>';
			 * print_r($this);
			 * die('SIVAC');
			 */
		}
	}
	function nebenkosten_summe($mv_id, $jahr, $monat) {
		/*
		 * SELECT KOSTENKATEGORIE, BETRAG * MJESECI AS GESAMT
		 * FROM (
		 *
		 * SELECT KOSTENKATEGORIE, BETRAG, ANFANG, ENDE, IF( ENDE = '0000-00-00', '2009-12-31', ENDE ) AS NEW_ENDE, IF( ENDE != '0000-00-00', period_diff( concat( year( ENDE ) , if( month( ENDE ) <10, '0', '' ) , month( ENDE ) ) , concat( year( ANFANG ) , if( month( ANFANG ) <10, '0', '' ) , month( ANFANG ) ) ) +1, period_diff( concat( year( '2009-12-31' ) , if( month( '2009-12-31' ) <10, '0', '' ) , month( '2009-12-31' ) ) , concat( year( ANFANG ) , if( month( ANFANG ) <10, '0', '' ) , month( ANFANG ) ) ) +1 ) AS MJESECI
		 * FROM MIETENTWICKLUNG
		 * WHERE MIETENTWICKLUNG_AKTUELL = '1' && `KOSTENTRAEGER_TYP` = 'MIETVERTRAG' && `KOSTENTRAEGER_ID` = '685'
		 * ORDER BY `MJESECI` ASC
		 * ) AS t1
		 *
		 *
		 *
		 * SELECT NEW_ANFANG, NEW_ENDE, KOSTENKATEGORIE, BETRAG FROM(
		 * SELECT KOSTENKATEGORIE, BETRAG, ANFANG, ENDE,
		 * IF( ANFANG <= '2009-01-01', '2009-01-01', ANFANG) AS NEW_ANFANG,
		 * IF( ENDE = '0000-00-00' OR ENDE >='2009-12-31', '2009-12-31', ENDE) AS NEW_ENDE
		 *
		 *
		 * FROM MIETENTWICKLUNG
		 * WHERE MIETENTWICKLUNG_AKTUELL = '1' && `KOSTENTRAEGER_TYP` = 'MIETVERTRAG' && `KOSTENTRAEGER_ID` = '4') as t1 WHERE DATE_FORMAT(NEW_ANFANG, '%Y') = '2009' && DATE_FORMAT(NEW_ENDE, '%Y') = '2009' && NEW_ANFANG!=NEW_ENDE
		 *
		 *
		 *
		 * SELECT NEW_ANFANG, NEW_ENDE, KOSTENKATEGORIE, BETRAG, DATE_FORMAT(NEW_ENDE,'%m') - DATE_FORMAT(NEW_ANFANG,'%m') + 1 AS MONATE, SUM((DATE_FORMAT(NEW_ENDE,'%m') - DATE_FORMAT(NEW_ANFANG,'%m') + 1) * BETRAG) AS GESAMT
		 *
		 *
		 * FROM(
		 * SELECT KOSTENKATEGORIE, BETRAG, ANFANG, ENDE,
		 * IF( ANFANG <= '2009-01-01', '2009-01-01', ANFANG) AS NEW_ANFANG,
		 * IF( ENDE = '0000-00-00' OR ENDE >='2009-12-31', '2009-12-31', ENDE) AS NEW_ENDE
		 *
		 *
		 * FROM MIETENTWICKLUNG
		 * WHERE MIETENTWICKLUNG_AKTUELL = '1' && `KOSTENTRAEGER_TYP` = 'MIETVERTRAG' && `KOSTENTRAEGER_ID` = '4') as t1 WHERE DATE_FORMAT(NEW_ANFANG, '%Y') = '2009' && DATE_FORMAT(NEW_ENDE, '%Y') = '2009' && NEW_ANFANG!=NEW_ENDE && KOSTENKATEGORIE != 'Miete kalt' && KOSTENKATEGORIE != 'Ratenzahlung' GROUP BY KOSTENKATEGORIE
		 *
		 *
		 *
		 *
		 * SELECT NEW_ANFANG, NEW_ENDE, KOSTENKATEGORIE, BETRAG, DATE_FORMAT(NEW_ENDE,'%m') - DATE_FORMAT(NEW_ANFANG,'%m') + 1 AS MONATE
		 *
		 *
		 * FROM(
		 * SELECT KOSTENKATEGORIE, BETRAG, ANFANG, ENDE,
		 * IF( ANFANG <= '2009-01-01', '2009-01-01', ANFANG) AS NEW_ANFANG,
		 * IF( ENDE = '0000-00-00' OR ENDE >='2009-12-31', '2009-12-31', ENDE) AS NEW_ENDE
		 *
		 *
		 * FROM MIETENTWICKLUNG
		 * WHERE MIETENTWICKLUNG_AKTUELL = '1' && `KOSTENTRAEGER_TYP` = 'MIETVERTRAG' && `KOSTENTRAEGER_ID` = '4') as t1 WHERE DATE_FORMAT(NEW_ANFANG, '%Y') = '2009' && DATE_FORMAT(NEW_ENDE, '%Y') = '2009' && NEW_ANFANG!=NEW_ENDE && KOSTENKATEGORIE != 'Miete kalt' && KOSTENKATEGORIE != 'Ratenzahlung'
		 *
		 *
		 *
		 */
	}
	
	/* Liefert ein Array und $this->nebenkosten_gesamt_jahr */
	function nebenkosten_aufstellung_jahr_arr($mv_id, $jahr) {
		$result = mysql_query ( "	SELECT NEW_ANFANG, NEW_ENDE, KOSTENKATEGORIE, BETRAG, DATE_FORMAT(NEW_ENDE,'%m') - DATE_FORMAT(NEW_ANFANG,'%m') + 1  AS MONATE


 FROM(
SELECT KOSTENKATEGORIE, BETRAG, ANFANG, ENDE, 
IF( ANFANG <= '$jahr-01-01', '$jahr-01-01', ANFANG) AS NEW_ANFANG, 
 IF( ENDE = '0000-00-00' OR ENDE >='$jahr-12-31', '$jahr-12-31', ENDE) AS NEW_ENDE


FROM MIETENTWICKLUNG
WHERE MIETENTWICKLUNG_AKTUELL = '1' && `KOSTENTRAEGER_TYP` = 'MIETVERTRAG' && `KOSTENTRAEGER_ID` = '$mv_id') as t1 WHERE DATE_FORMAT(NEW_ANFANG, '%Y') = '$jahr' && DATE_FORMAT(NEW_ENDE, '%Y') = '$jahr' && NEW_ANFANG!=NEW_ENDE && KOSTENKATEGORIE != 'Miete kalt' && KOSTENKATEGORIE != 'Ratenzahlung' && KOSTENKATEGORIE != 'MOD'" );
		
		$this->nebenkosten_gesamt_jahr = 0.00;
		while ( $row = mysql_fetch_assoc ( $result ) )
			($my_arr [] = $row);
		return $my_arr;
	}
	
	/* Liefert ein Array und $this->nebenkosten_gesamt_jahr */
	function nebenkosten_aufstellung_jahr_arr_kat($mv_id, $jahr, $kostenkat) {
		$result = mysql_query ( "	SELECT NEW_ANFANG, NEW_ENDE, KOSTENKATEGORIE, BETRAG, DATE_FORMAT(NEW_ENDE,'%m') - DATE_FORMAT(NEW_ANFANG,'%m') + 1  AS MONATE


 FROM(
SELECT KOSTENKATEGORIE, BETRAG, ANFANG, ENDE, 
IF( ANFANG <= '$jahr-01-01', '$jahr-01-01', ANFANG) AS NEW_ANFANG, 
 IF( ENDE = '0000-00-00' OR ENDE >='$jahr-12-31', '$jahr-12-31', ENDE) AS NEW_ENDE


FROM MIETENTWICKLUNG
WHERE MIETENTWICKLUNG_AKTUELL = '1' && `KOSTENTRAEGER_TYP` = 'MIETVERTRAG' && `KOSTENTRAEGER_ID` = '$mv_id') as t1 WHERE DATE_FORMAT(NEW_ANFANG, '%Y') = '$jahr' && DATE_FORMAT(NEW_ENDE, '%Y') = '$jahr' && NEW_ANFANG!=NEW_ENDE && KOSTENKATEGORIE = '$kostenkat'" );
		
		$this->nebenkosten_gesamt_jahr = 0.00;
		while ( $row = mysql_fetch_assoc ( $result ) )
			($my_arr [] = $row);
		return $my_arr;
	}
	function gesamtsumme_nebenkosten_jahr($mv_id, $jahr, $kostenkat) {
		$this->nebenkosten_gesamt_jahr = '0.00';
		$nebenkosten_arr = $this->nebenkosten_aufstellung_jahr_arr_kat ( $mv_id, $jahr, $kostenkat );
		$anzahl_zeilen = count ( $nebenkosten_arr );
		for($a = 0; $a < $anzahl_zeilen; $a ++) {
			$betrag = $nebenkosten_arr [$a] ['BETRAG'];
			$monate = $nebenkosten_arr [$a] ['MONATE'];
			$b = $betrag * $monate;
			$this->nebenkosten_gesamt_jahr = $this->nebenkosten_gesamt_jahr + $b;
		}
	}
	function nebenkosten_monatlich($mietvertrag_id, $monat, $jahr) {
		$result = mysql_query ( "SELECT BETRAG FROM MIETENTWICKLUNG WHERE KOSTENTRAEGER_TYP='MIETVERTRAG' && KOSTENTRAEGER_ID = '$mietvertrag_id' && MIETENTWICKLUNG_AKTUELL = '1' && ( ENDE = '0000-00-00' OR DATE_FORMAT( ENDE, '%Y-%m' ) >= '$jahr-$monat') && DATE_FORMAT( ANFANG, '%Y-%m' ) <= '$jahr-$monat'  && KOSTENKATEGORIE='Nebenkosten Vorauszahlung' ORDER BY ANFANG DESC LIMIT 0,1" );
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			$row = mysql_fetch_assoc ( $result );
			return $row ['BETRAG'];
		}
	}
	function get_einzugsdatum($mietvertrag_id) {
		$result = mysql_query ( "SELECT MIETVERTRAG_VON FROM MIETVERTRAG WHERE MIETVERTRAG_AKTUELL='1' && MIETVERTRAG_ID='$mietvertrag_id' ORDER BY MIETVERTRAG_DAT DESC LIMIT 0,1" );
		$row = mysql_fetch_assoc ( $result );
		$this->mietvertrag_von = $row ['MIETVERTRAG_VON'];
	}
	function set_datum_block_e() {
		$result = mysql_query ( "SELECT ANFANG, EINHEIT_KURZNAME, MIETENTWICKLUNG.KOSTENTRAEGER_ID FROM `MIETENTWICKLUNG` JOIN (MIETVERTRAG, EINHEIT) ON (MIETENTWICKLUNG.MIETVERTRAG_ID = MIETVERTRAG.MIETVERTRAG_ID && MIETVERTRAG.EINHEIT_ID = EINHEIT.EINHEIT_ID) WHERE ANFANG<'2008-03-01' && EINHEIT.EINHEIT_KURZNAME LIKE '%E-%' && KOSTENTRAEGER_TYP='Mietvertrag'" );
		while ( $row = mysql_fetch_assoc ( $result ) )
			$my_arr [] = $row;
		
		for($a = 0; $a < count ( $my_arr ); $a ++) {
			echo $my_arr [$a] ['KOSTENTRAEGER_ID'];
			$e_mv_id = $my_arr [$a] ['KOSTENTRAEGER_ID'];
			mysql_query ( "UPDATE MIETENTWICKLUNG SET ANFANG='2008-03-01' WHERE MIETVERTRAG_ID='$e_mv_id'" );
		}
	}
	function update_bk_hk_datum() {
		mysql_query ( "UPDATE MIETENTWICKLUNG SET ANFANG=ENDE WHERE KOSTENKATEGORIE LIKE '%abrechnung%' && KOSTENTRAEGER_TYP='Mietvertrag'" );
	}
	function set_datum_andere() {
		$result = mysql_query ( "SELECT ANFANG, EINHEIT_KURZNAME, MIETENTWICKLUNG.KOSTENTRAEGER_ID FROM `MIETENTWICKLUNG` JOIN (MIETVERTRAG, EINHEIT) ON (MIETENTWICKLUNG.MIETVERTRAG_ID = MIETVERTRAG.MIETVERTRAG_ID && MIETVERTRAG.EINHEIT_ID = EINHEIT.EINHEIT_ID) WHERE ANFANG<'2007-01-01' && EINHEIT.EINHEIT_KURZNAME NOT LIKE '%E-%'" );
		while ( $row = mysql_fetch_assoc ( $result ) )
			$my_arr [] = $row;
		
		for($a = 0; $a < count ( $my_arr ); $a ++) {
			echo $my_arr [$a] ['KOSTENTRAEGER_ID'];
			$e_mv_id = $my_arr [$a] ['KOSTENTRAEGER_ID'];
			mysql_query ( "UPDATE MIETENTWICKLUNG SET ANFANG='2007-01-01' WHERE MIETVERTRAG_ID='$e_mv_id'" );
		}
	}
	function mietentwicklung_anzeigen_OK($mietvertrag_id) {
		/*
		 * OKmysql_query("SET OPTION SQL_BIG_SELECTS=1");
		 *
		 *
		 * $db_abfrage = "SELECT MIETENTWICKLUNG.MIETENTWICKLUNG_DAT, MIETENTWICKLUNG.KOSTENTRAEGER_ID, EINHEIT.EINHEIT_KURZNAME, MIETENTWICKLUNG.KOSTENKATEGORIE, MIETENTWICKLUNG.ANFANG, MIETENTWICKLUNG.ENDE, MIETENTWICKLUNG.BETRAG FROM `MIETENTWICKLUNG` JOIN (MIETVERTRAG, EINHEIT) ON (MIETENTWICKLUNG.KOSTENTRAEGER_ID = MIETVERTRAG.MIETVERTRAG_ID && MIETVERTRAG.EINHEIT_ID = EINHEIT.EINHEIT_ID ) WHERE MIETENTWICKLUNG.KOSTENTRAEGER_ID = '$mietvertrag_id' && KOSTENKATEGORIE NOT LIKE '%abrechnung%' && MIETENTWICKLUNG_AKTUELL = '1' && KOSTENTRAEGER_TYP='Mietvertrag' GROUP BY MIETENTWICKLUNG_DAT ORDER BY KOSTENKATEGORIE, ENDE, ANFANG ASC LIMIT 0,30";
		 *
		 */
		$db_abfrage = "SELECT MIETENTWICKLUNG.MIETENTWICKLUNG_DAT, MIETENTWICKLUNG.KOSTENTRAEGER_ID, EINHEIT.EINHEIT_KURZNAME, MIETENTWICKLUNG.KOSTENKATEGORIE, MIETENTWICKLUNG.ANFANG, MIETENTWICKLUNG.ENDE, MIETENTWICKLUNG.BETRAG, MIETENTWICKLUNG.MWST_ANTEIL FROM `MIETENTWICKLUNG` JOIN (MIETVERTRAG, EINHEIT) ON (MIETENTWICKLUNG.KOSTENTRAEGER_ID = MIETVERTRAG.MIETVERTRAG_ID && MIETVERTRAG.EINHEIT_ID = EINHEIT.EINHEIT_ID ) WHERE MIETENTWICKLUNG.KOSTENTRAEGER_ID = '$mietvertrag_id' && MIETENTWICKLUNG_AKTUELL = '1' && KOSTENTRAEGER_TYP='Mietvertrag' GROUP BY MIETENTWICKLUNG_DAT ORDER BY KOSTENKATEGORIE ASC, ANFANG ASC LIMIT 0,30";
		
		// $db_abfrage = "SELECT MIETENTWICKLUNG.MIETENTWICKLUNG_DAT, MIETENTWICKLUNG.KOSTENTRAEGER_ID, EINHEIT.EINHEIT_KURZNAME, MIETENTWICKLUNG.KOSTENKATEGORIE, MIETENTWICKLUNG.ANFANG, MIETENTWICKLUNG.ENDE, MIETENTWICKLUNG.BETRAG FROM `MIETENTWICKLUNG` JOIN (MIETVERTRAG, EINHEIT) ON (MIETENTWICKLUNG.KOSTENTRAEGER_ID = MIETVERTRAG.MIETVERTRAG_ID && MIETVERTRAG.EINHEIT_ID = EINHEIT.EINHEIT_ID ) WHERE MIETENTWICKLUNG.KOSTENTRAEGER_ID = '$mietvertrag_id' && MIETENTWICKLUNG_AKTUELL = '1' && KOSTENTRAEGER_TYP='Mietvertrag' ORDER BY KOSTENKATEGORIE ASC LIMIT 0,30";
		
		$result = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		
		while ( $row = mysql_fetch_assoc ( $result ) )
			$my_arr [] = $row;
			
			// print_r($my_arr);
		$buchung = new mietkonto ();
		echo "<table class=\"sortable\">";
		echo "<tr><th>Einheit</th><th>Beginn</th><th>Ende</th><th>Kostenkategorie</th><th align=right>Betrag</th><th>MWST-Anteil</th><th>Optionen</th>";
		// echo "<pre>";
		// print_r($my_arr);
		// echo "</pre>";
		for($a = 0; $a < count ( $my_arr ); $a ++) {
			$me_dat = $my_arr [$a] ['MIETENTWICKLUNG_DAT'];
			$e_mv_id = $my_arr [$a] ['KOSTENTRAEGER_ID'];
			$e_kurzname = $my_arr [$a] ['EINHEIT_KURZNAME'];
			$kostenkat = $my_arr [$a] ['KOSTENKATEGORIE'];
			$betrag = $my_arr [$a] ['BETRAG'];
			$betrag = number_format ( $betrag, 2, ",", "" );
			$mwst_anteil = $my_arr [$a] ['MWST_ANTEIL'];
			$mwst_anteil_a = nummer_punkt2komma ( $mwst_anteil );
			$anfang = $my_arr [$a] ['ANFANG'];
			$anfang = $buchung->date_mysql2german ( $anfang );
			$ende = $my_arr [$a] ['ENDE'];
			$ende = $buchung->date_mysql2german ( $ende );
			$aendern_link = "<a href='" . route('legacy::miete_definieren::index', ['option' => 'aendern', 'mietvertrag_id' => $e_mv_id, 'aendern_dat' => $me_dat]) . "'>Ändern</a>";
			$loeschen_link = "<a href='" . route('legacy::miete_definieren::index', ['option' => 'me_loeschen', 'mietvertrag_id' => $e_mv_id, 'me_dat' => $me_dat]) . "'>Löschen</a>";
			if ($ende == '00.00.0000') {
				$ende = 'unbefristet';
			}
			echo "<tr class=\"zeile1\"><td>$e_kurzname</td><td>$anfang</td><td>$ende </td><td>$kostenkat</td><td align=right>$betrag €</td><td>$mwst_anteil_a</td><td>$aendern_link $loeschen_link</td></tr>";
		}
		echo "</table>";
	}
	function mietentwicklung_anzeigen_n1($mietvertrag_id) {
		$db_abfrage = "SELECT KOSTENKATEGORIE FROM `MIETENTWICKLUNG`  WHERE KOSTENTRAEGER_TYP='MIETVERTRAG' && KOSTENTRAEGER_ID='$mietvertrag_id' && MIETENTWICKLUNG_AKTUELL='1' && ENDE='0000-00-00' GROUP BY KOSTENKATEGORIE ORDER BY KOSTENKATEGORIE ";
		$result = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		
		while ( $row = mysql_fetch_assoc ( $result ) ) {
			$kos_kat = $row ['KOSTENKATEGORIE'];
			echo "<table class=\"sortable\">";
			echo "<thead><tr><th colspan=\"5\">$kos_kat</th></tr></thead>";
			echo "<tbody><tr><th>Beginn</th><th>Ende</th><th align=\"right\">Betrag</th><th>MWST-Anteil</th><th>Optionen</th></tr>";
			
			$db_abfrage1 = "SELECT * FROM `MIETENTWICKLUNG`  WHERE KOSTENTRAEGER_TYP='MIETVERTRAG' && KOSTENTRAEGER_ID='$mietvertrag_id' && MIETENTWICKLUNG_AKTUELL='1' && KOSTENKATEGORIE='$kos_kat' ORDER BY ANFANG";
			$result1 = mysql_query ( $db_abfrage1 ) or die ( mysql_error () );
			
			while ( $row1 = mysql_fetch_assoc ( $result1 ) ) {
				$me_dat = $row1 ['MIETENTWICKLUNG_DAT'];
				$e_mv_id = $row1 ['KOSTENTRAEGER_ID'];
				$kostenkat = $row1 ['KOSTENKATEGORIE'];
				$betrag = $row1 ['BETRAG'];
				$betrag = number_format ( $betrag, 2, ",", "" );
				$mwst_anteil = $row1 ['MWST_ANTEIL'];
				$mwst_anteil_a = nummer_punkt2komma ( $mwst_anteil );
				$anfang = $row1 ['ANFANG'];
				$anfang = date_mysql2german ( $anfang );
				$ende = $row1 ['ENDE'];
				$ende = date_mysql2german ( $ende );
				
				$aendern_link = "<a href='" . route('legacy::miete_definieren::index', ['option' => 'aendern', 'mietvertrag_id' => $e_mv_id, 'aendern_dat' => $me_dat]) . "'>Ändern</a>";
				$loeschen_link = "<a href='" . route('legacy::miete_definieren::index', ['option' => 'me_loeschen', 'mietvertrag_id' => $e_mv_id, 'me_dat' => $me_dat]) . "'>Löschen</a>";
				if ($ende == '00.00.0000') {
					$ende = 'unbefristet';
					$css_class = "zeile2";
				} else {
					$css_class = "zeile1";
				}
				echo "<tr class=\"$css_class\"><td>$anfang</td><td>$ende </td><td align=right>$betrag €</td><td>$mwst_anteil_a</td><td>$aendern_link $loeschen_link</td></tr>";
			} // end while 2
			
			echo "</tbody></table>";
		} // end while 1
	}
	function mietentwicklung_anzeigen($mietvertrag_id) {
		$mvs = new mietvertraege ();
		$mvs->get_mietvertrag_infos_aktuell ( $mietvertrag_id );
		echo "<table id=\"t12\" class=\"sortable\">";
		echo "<tr><th colspan=\"6\">AKTUELL $mvs->einheit_kurzname - $mvs->personen_name_string</th></tr>";
		echo "<tr><th>BEZEICHNUNG</th><th>Beginn</th><th>Ende</th><th align=\"right\">Betrag</th><th>MWST-Anteil</th><th>Optionen</th></tr>";
		
		$heute = date ( "Y-m-d" );
		$db_abfrage1 = "SELECT * FROM `MIETENTWICKLUNG`  WHERE KOSTENTRAEGER_TYP='MIETVERTRAG' && KOSTENTRAEGER_ID='$mietvertrag_id' && MIETENTWICKLUNG_AKTUELL='1' && (ENDE='0000-00-00' OR ENDE>='$heute') && ANFANG <= '$heute' ORDER BY KOSTENKATEGORIE, ANFANG, ENDE ASC";
		// echo $db_abfrage1;
		$result1 = mysql_query ( $db_abfrage1 ) or die ( mysql_error () );
		$summe_aktuell = 0;
		$summe_mwst = 0;
		while ( $row1 = mysql_fetch_assoc ( $result1 ) ) {
			$me_dat = $row1 ['MIETENTWICKLUNG_DAT'];
			$e_mv_id = $row1 ['KOSTENTRAEGER_ID'];
			$kostenkat = $row1 ['KOSTENKATEGORIE'];
			$betrag = $row1 ['BETRAG'];
			$betrag_a = nummer_punkt2komma_t ( $betrag );
			
			$mwst_anteil = $row1 ['MWST_ANTEIL'];
			$summe_mwst += $mwst_anteil;
			$mwst_anteil_a = nummer_punkt2komma ( $mwst_anteil );
			$anfang = $row1 ['ANFANG'];
			$anfang = date_mysql2german ( $anfang );
			$ende = $row1 ['ENDE'];
			$ende = date_mysql2german ( $ende );
			
			$anfang_ser = str_replace ( '-', '', date_german2mysql ( $anfang ) );
			$ende_ser = str_replace ( '-', '', date_german2mysql ( $ende ) );
			$heute_ser = date ( "Ymd" );
			
			$aendern_link = "<a href='" . route('legacy::miete_definieren::index', ['option' => 'aendern', 'mietvertrag_id' => $e_mv_id, 'aendern_dat' => $me_dat]) . "'>Ändern</a>";
			$loeschen_link = "<a href='" . route('legacy::miete_definieren::index', ['option' => 'me_loeschen', 'mietvertrag_id' => $e_mv_id, 'me_dat' => $me_dat]) . "'>Löschen</a>";
			if (($anfang_ser <= $heute_ser) && ($ende == '00.00.0000' or ($ende_ser > $heute_ser))) {
				// $ende = 'unbefristet';
				$css_class = "zeile2";
				$summe_aktuell += $betrag;
			} else {
				$css_class = "zeile1";
			}
			echo "<tr class=\"$css_class\"><td>$kostenkat</td><td>$anfang</td><td>$ende </td><td align=right>$betrag_a €</td><td>$mwst_anteil_a</td><td>$aendern_link $loeschen_link</td></tr>";
		} // end while 2
		$summe_aktuell_a = nummer_punkt2komma_t ( $summe_aktuell );
		$summe_mwst_a = nummer_punkt2komma_t ( $summe_mwst );
		echo "<tr class=\"zeile1\"><td colspan=\"3\"><b>Gesamt aktuell</b></td><td align=right><b>$summe_aktuell_a €</b></td><td><b>$summe_mwst_a €</b></td><td></td></tr>";
		echo "</table>";
		
		echo "<table id=\"t2\" class=\"sortable\">";
		echo "<tr><th colspan=\"6\">ANSTEHEND $mvs->einheit_kurzname - $mvs->personen_name_string</th></tr>";
		echo "<tr><th>BEZEICHNUNG</th><th>Beginn</th><th>Ende</th><th align=\"right\">Betrag</th><th>MWST-Anteil</th><th>Optionen</th></tr>";
		
		$db_abfrage1 = "SELECT * FROM `MIETENTWICKLUNG`  WHERE KOSTENTRAEGER_TYP='MIETVERTRAG' && KOSTENTRAEGER_ID='$mietvertrag_id' && MIETENTWICKLUNG_AKTUELL='1' && (ENDE='0000-00-00' OR ENDE>'$heute') && ANFANG>='$heute' ORDER BY KOSTENKATEGORIE, ANFANG, ENDE ASC";
		$result1 = mysql_query ( $db_abfrage1 ) or die ( mysql_error () );
		
		$temp_kat = '';
		$z = 1;
		while ( $row1 = mysql_fetch_assoc ( $result1 ) ) {
			$me_dat = $row1 ['MIETENTWICKLUNG_DAT'];
			$e_mv_id = $row1 ['KOSTENTRAEGER_ID'];
			$kostenkat = $row1 ['KOSTENKATEGORIE'];
			if ($kostenkat != $temp_kat) {
				$temp_kat = $kostenkat;
				if ($z == 2) {
					$z = 1;
				} else {
					$z = 2;
				}
			}
			$betrag = $row1 ['BETRAG'];
			$betrag = number_format ( $betrag, 2, ",", "" );
			$mwst_anteil = $row1 ['MWST_ANTEIL'];
			$mwst_anteil_a = nummer_punkt2komma ( $mwst_anteil );
			$anfang = $row1 ['ANFANG'];
			$anfang = date_mysql2german ( $anfang );
			$ende = $row1 ['ENDE'];
			$ende = date_mysql2german ( $ende );
			
			$aendern_link = "<a href='" . route('legacy::miete_definieren::index', ['option' => 'aendern', 'mietvertrag_id' => $e_mv_id, 'aendern_dat' => $me_dat]) . "'>Ändern</a>";
			$loeschen_link = "<a href='" . route('legacy::miete_definieren::index', ['option' => 'me_loeschen', 'mietvertrag_id' => $e_mv_id, 'me_dat' => $me_dat]) . "'>Löschen</a>";
			if ($ende == '00.00.0000') {
				$ende = 'unbefristet';
			}
			echo "<tr class=\"zeile$z\"><td>$kostenkat</td><td>$anfang</td><td>$ende </td><td align=right>$betrag €</td><td>$mwst_anteil_a</td><td>$aendern_link $loeschen_link</td></tr>";
		} // end while 2
		
		echo "</table>";
		
		echo "<table id=\"t2\" class=\"sortable\">";
		echo "<tr><th colspan=\"6\">ABGELAUFEN $mvs->einheit_kurzname - $mvs->personen_name_string</th></tr>";
		echo "<tr><th>BEZEICHNUNG</th><th>Beginn</th><th>Ende</th><th align=\"right\">Betrag</th><th>MWST-Anteil</th><th>Optionen</th></tr>";
		
		$db_abfrage1 = "SELECT * FROM `MIETENTWICKLUNG`  WHERE KOSTENTRAEGER_TYP='MIETVERTRAG' && KOSTENTRAEGER_ID='$mietvertrag_id' && MIETENTWICKLUNG_AKTUELL='1' && (ENDE!='0000-00-00' && ENDE<'$heute') ORDER BY ENDE DESC";
		$result1 = mysql_query ( $db_abfrage1 ) or die ( mysql_error () );
		
		$temp_kat = '';
		$z = 1;
		while ( $row1 = mysql_fetch_assoc ( $result1 ) ) {
			$me_dat = $row1 ['MIETENTWICKLUNG_DAT'];
			$e_mv_id = $row1 ['KOSTENTRAEGER_ID'];
			$kostenkat = $row1 ['KOSTENKATEGORIE'];
			if ($kostenkat != $temp_kat) {
				$temp_kat = $kostenkat;
				if ($z == 2) {
					$z = 1;
				} else {
					$z = 2;
				}
			}
			$betrag = $row1 ['BETRAG'];
			$betrag = number_format ( $betrag, 2, ",", "" );
			$mwst_anteil = $row1 ['MWST_ANTEIL'];
			$mwst_anteil_a = nummer_punkt2komma ( $mwst_anteil );
			$anfang = $row1 ['ANFANG'];
			$anfang = date_mysql2german ( $anfang );
			$ende = $row1 ['ENDE'];
			$ende = date_mysql2german ( $ende );
			
			$aendern_link = "<a href='" . route('legacy::miete_definieren::index', ['option' => 'aendern', 'mietvertrag_id' => $e_mv_id, 'aendern_dat' => $me_dat]) . "'>Ändern</a>";
			$loeschen_link = "<a href='" . route('legacy::miete_definieren::index', ['option' => 'me_loeschen', 'mietvertrag_id' => $e_mv_id, 'me_dat' => $me_dat]) . "'>Löschen</a>";
			echo "<tr class=\"zeile1\"><td>$kostenkat</td><td>$anfang</td><td>$ende </td><td align=right>$betrag €</td><td>$mwst_anteil_a</td><td>$aendern_link $loeschen_link</td></tr>";
		} // end while 2
		
		echo "</table>";
	}
	function pdf_mietentwicklung($pdf, $mietvertrag_id) {
		$mvs = new mietvertraege ();
		$mvs->get_mietvertrag_infos_aktuell ( $mietvertrag_id );
		
		echo "<table id=\"t12\" class=\"sortable\">";
		echo "<tr><th colspan=\"6\">AKTUELL $mvs->einheit_kurzname - $mvs->personen_name_string</th></tr>";
		echo "<tr><th>BEZEICHNUNG</th><th>Beginn</th><th>Ende</th><th align=\"right\">Betrag</th><th>MWST-Anteil</th><th>Optionen</th></tr>";
		
		$heute = date ( "Y-m-d" );
		$db_abfrage1 = "SELECT * FROM `MIETENTWICKLUNG`  WHERE KOSTENTRAEGER_TYP='MIETVERTRAG' && KOSTENTRAEGER_ID='$mietvertrag_id' && MIETENTWICKLUNG_AKTUELL='1' && (ENDE='0000-00-00' OR ENDE>='$heute') && ANFANG <= '$heute' ORDER BY KOSTENKATEGORIE, ANFANG, ENDE ASC";
		// echo $db_abfrage1;
		$result1 = mysql_query ( $db_abfrage1 ) or die ( mysql_error () );
		$anz_zeilen = mysql_numrows ( $result1 );
		
		$summe_aktuell = 0;
		$summe_mwst = 0;
		while ( $row1 = mysql_fetch_assoc ( $result1 ) ) {
			$me_dat = $row1 ['MIETENTWICKLUNG_DAT'];
			$e_mv_id = $row1 ['KOSTENTRAEGER_ID'];
			$kostenkat = $row1 ['KOSTENKATEGORIE'];
			$betrag = $row1 ['BETRAG'];
			$betrag_a = nummer_punkt2komma_t ( $betrag );
			
			$mwst_anteil = $row1 ['MWST_ANTEIL'];
			$summe_mwst += $mwst_anteil;
			$mwst_anteil_a = nummer_punkt2komma ( $mwst_anteil );
			$anfang = $row1 ['ANFANG'];
			$anfang = date_mysql2german ( $anfang );
			$ende = $row1 ['ENDE'];
			$ende = date_mysql2german ( $ende );
			
			$anfang_ser = str_replace ( '-', '', date_german2mysql ( $anfang ) );
			$ende_ser = str_replace ( '-', '', date_german2mysql ( $ende ) );
			$heute_ser = date ( "Ymd" );
			
			$aendern_link = "<a href='" . route('legacy::miete_definieren::index', ['option' => 'aendern', 'mietvertrag_id' => $e_mv_id, 'aendern_dat' => $me_dat]) . "'>Ändern</a>";
			$loeschen_link = "<a href='" . route('legacy::miete_definieren::index', ['option' => 'me_loeschen', 'mietvertrag_id' => $e_mv_id, 'me_dat' => $me_dat]) . "'>Löschen</a>";
			if (($anfang_ser <= $heute_ser) && ($ende == '00.00.0000' or ($ende_ser > $heute_ser))) {
				// $ende = 'unbefristet';
				$css_class = "zeile2";
				$summe_aktuell += $betrag;
			} else {
				$css_class = "zeile1";
			}
			echo "<tr class=\"$css_class\"><td>$kostenkat</td><td>$anfang</td><td>$ende </td><td align=right>$betrag_a €</td><td>$mwst_anteil_a</td><td>$aendern_link $loeschen_link</td></tr>";
			$pdf_aktuell [] = $row1;
		} // end while 2
		$summe_aktuell_a = nummer_punkt2komma_t ( $summe_aktuell );
		$summe_mwst_a = nummer_punkt2komma_t ( $summe_mwst );
		echo "<tr class=\"zeile1\"><td colspan=\"3\"><b>Gesamt aktuell</b></td><td align=right><b>$summe_aktuell_a €</b></td><td><b>$summe_mwst_a €</b></td><td></td></tr>";
		echo "</table>";
		
		$pdf_aktuell [$anz_zeilen] ['KOSTENKATEGORIE'] = "<b>SUMMEN</b>";
		$pdf_aktuell [$anz_zeilen] ['BETRAG'] = "<b>$summe_aktuell_a</b>";
		;
		$pdf_aktuell [$anz_zeilen] ['MWST_ANTEIL'] = "<b>$summe_mwst_a</b>";
		
		// $pdf->ezTable($pdf_aktuell);
		if (count ( $pdf_aktuell ) > 1) {
			$cols = array (
					'KOSTENKATEGORIE' => "Bezeichnung",
					'ANFANG' => "VON",
					'ENDE' => "BIS",
					'BETRAG' => "BETRAG",
					'MWST_ANTEIL' => "MWSt" 
			);
			$heute_d = date ( "d.m.Y" );
			$pdf->ezTable ( $pdf_aktuell, $cols, "Miete aktuell - Druckdatum: $heute_d - $mvs->einheit_kurzname - $mvs->personen_name_string</b>", array (
					'showHeadings' => 1,
					'shaded' => 1,
					'titleFontSize' => 8,
					'fontSize' => 8,
					'xPos' => 50,
					'xOrientation' => 'right',
					'width' => 500,
					'cols' => array (
							'BEZ' => array (
									'justification' => 'left',
									'width' => 140 
							) 
					) 
			) );
			$pdf->ezSetDy ( - 5 ); // abstand
		}
		
		echo "<table id=\"t2\" class=\"sortable\">";
		echo "<tr><th colspan=\"6\">ANSTEHEND $mvs->einheit_kurzname - $mvs->personen_name_string</th></tr>";
		echo "<tr><th>BEZEICHNUNG</th><th>Beginn</th><th>Ende</th><th align=\"right\">Betrag</th><th>MWST-Anteil</th><th>Optionen</th></tr>";
		
		$db_abfrage1 = "SELECT * FROM `MIETENTWICKLUNG`  WHERE KOSTENTRAEGER_TYP='MIETVERTRAG' && KOSTENTRAEGER_ID='$mietvertrag_id' && MIETENTWICKLUNG_AKTUELL='1' && (ENDE='0000-00-00' OR ENDE>'$heute') && ANFANG>='$heute' ORDER BY KOSTENKATEGORIE, ANFANG, ENDE ASC";
		$result1 = mysql_query ( $db_abfrage1 ) or die ( mysql_error () );
		
		$temp_kat = '';
		$z = 1;
		while ( $row1 = mysql_fetch_assoc ( $result1 ) ) {
			$me_dat = $row1 ['MIETENTWICKLUNG_DAT'];
			$e_mv_id = $row1 ['KOSTENTRAEGER_ID'];
			$kostenkat = $row1 ['KOSTENKATEGORIE'];
			if ($kostenkat != $temp_kat) {
				$temp_kat = $kostenkat;
				if ($z == 2) {
					$z = 1;
				} else {
					$z = 2;
				}
			}
			$betrag = $row1 ['BETRAG'];
			$betrag = number_format ( $betrag, 2, ",", "" );
			$mwst_anteil = $row1 ['MWST_ANTEIL'];
			$mwst_anteil_a = nummer_punkt2komma ( $mwst_anteil );
			$anfang = $row1 ['ANFANG'];
			$anfang = date_mysql2german ( $anfang );
			$ende = $row1 ['ENDE'];
			$ende = date_mysql2german ( $ende );
			
			$aendern_link = "<a href='" . route('legacy::miete_definieren::index', ['option' => 'aendern', 'mietvertrag_id' => $e_mv_id, 'aendern_dat' => $me_dat]) . "'>Ändern</a>";
			$loeschen_link = "<a href='" . route('legacy::miete_definieren::index', ['option' => 'me_loeschen', 'mietvertrag_id' => $e_mv_id, 'me_dat' => $me_dat]) . "'>Löschen</a>";
			if ($ende == '00.00.0000') {
				$ende = 'unbefristet';
			}
			echo "<tr class=\"zeile$z\"><td>$kostenkat</td><td>$anfang</td><td>$ende </td><td align=right>$betrag €</td><td>$mwst_anteil_a</td><td>$aendern_link $loeschen_link</td></tr>";
			$pdf_anstehend [] = $row1;
		} // end while 2
		
		if (count ( $pdf_anstehend ) > 0) {
			$cols = array (
					'KOSTENKATEGORIE' => "Bezeichnung",
					'ANFANG' => "VON",
					'ENDE' => "BIS",
					'BETRAG' => "BETRAG",
					'MWST_ANTEIL' => "MWSt" 
			);
			$heute_d = date ( "d.m.Y" );
			$pdf->ezTable ( $pdf_anstehend, $cols, "Anstehende Änderungen der Miete - Druckdatum: $heute_d - $mvs->einheit_kurzname - $mvs->personen_name_string</b>", array (
					'showHeadings' => 1,
					'shaded' => 1,
					'titleFontSize' => 8,
					'fontSize' => 8,
					'xPos' => 50,
					'xOrientation' => 'right',
					'width' => 500,
					'cols' => array (
							'BEZ' => array (
									'justification' => 'left',
									'width' => 140 
							) 
					) 
			) );
			$pdf->ezSetDy ( - 5 ); // abstand
		}
		
		echo "</table>";
		
		echo "<table id=\"t2\" class=\"sortable\">";
		echo "<tr><th colspan=\"6\">ABGELAUFEN $mvs->einheit_kurzname - $mvs->personen_name_string</th></tr>";
		echo "<tr><th>BEZEICHNUNG</th><th>Beginn</th><th>Ende</th><th align=\"right\">Betrag</th><th>MWST-Anteil</th><th>Optionen</th></tr>";
		
		$db_abfrage1 = "SELECT * FROM `MIETENTWICKLUNG`  WHERE KOSTENTRAEGER_TYP='MIETVERTRAG' && KOSTENTRAEGER_ID='$mietvertrag_id' && MIETENTWICKLUNG_AKTUELL='1' && (ENDE!='0000-00-00' && ENDE<'$heute') ORDER BY ENDE DESC";
		$result1 = mysql_query ( $db_abfrage1 ) or die ( mysql_error () );
		
		$temp_kat = '';
		$z = 1;
		while ( $row1 = mysql_fetch_assoc ( $result1 ) ) {
			$me_dat = $row1 ['MIETENTWICKLUNG_DAT'];
			$e_mv_id = $row1 ['KOSTENTRAEGER_ID'];
			$kostenkat = $row1 ['KOSTENKATEGORIE'];
			if ($kostenkat != $temp_kat) {
				$temp_kat = $kostenkat;
				if ($z == 2) {
					$z = 1;
				} else {
					$z = 2;
				}
			}
			$betrag = $row1 ['BETRAG'];
			$betrag = number_format ( $betrag, 2, ",", "" );
			$mwst_anteil = $row1 ['MWST_ANTEIL'];
			$mwst_anteil_a = nummer_punkt2komma ( $mwst_anteil );
			$anfang = $row1 ['ANFANG'];
			$anfang = date_mysql2german ( $anfang );
			$ende = $row1 ['ENDE'];
			$ende = date_mysql2german ( $ende );
			
			$aendern_link = "<a href='" . route('legacy::miete_definieren::index', ['option' => 'aendern', 'mietvertrag_id' => $e_mv_id, 'aendern_dat' => $me_dat]) . "'>Ändern</a>";
			$loeschen_link = "<a href='" . route('legacy::miete_definieren::index', ['option' => 'me_loeschen', 'mietvertrag_id' => $e_mv_id, 'me_dat' => $me_dat]) . "'>Löschen</a>";
			echo "<tr class=\"zeile1\"><td>$kostenkat</td><td>$anfang</td><td>$ende </td><td align=right>$betrag €</td><td>$mwst_anteil_a</td><td>$aendern_link $loeschen_link</td></tr>";
			
			$pdf_abgelaufen [] = $row1;
		} // end while 2
		
		if (count ( $pdf_abgelaufen ) > 0) {
			$cols = array (
					'KOSTENKATEGORIE' => "Bezeichnung",
					'ANFANG' => "VON",
					'ENDE' => "BIS",
					'BETRAG' => "BETRAG",
					'MWST_ANTEIL' => "MWSt" 
			);
			$heute_d = date ( "d.m.Y" );
			$pdf->ezTable ( $pdf_abgelaufen, $cols, "Abgelaufene Mietdefinitionen - Druckdatum: $heute_d - $mvs->einheit_kurzname - $mvs->personen_name_string</b>", array (
					'showHeadings' => 1,
					'shaded' => 1,
					'titleFontSize' => 8,
					'fontSize' => 8,
					'xPos' => 50,
					'xOrientation' => 'right',
					'width' => 500,
					'cols' => array (
							'BEZ' => array (
									'justification' => 'left',
									'width' => 140 
							) 
					) 
			) );
		}
		
		echo "</table>";
	}
	function me_dat_aendern_form($dat) {
		$form = new mietkonto ();
		$db_abfrage = "SELECT MIETENTWICKLUNG_ID, KOSTENTRAEGER_ID, KOSTENKATEGORIE, BETRAG, MWST_ANTEIL, ANFANG, ENDE FROM MIETENTWICKLUNG where MIETENTWICKLUNG_DAT='$dat'";
		$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		while ( list ( $MIETENTWICKLUNG_ID, $KOSTENTRAEGER_ID, $KOSTENKATEGORIE, $BETRAG, $MWST_ANTEIL, $ANFANG, $ENDE ) = mysql_fetch_row ( $resultat ) ) {
			$ANFANG = $form->date_mysql2german ( $ANFANG );
			$form->dropdown_me_kostenkategorien ( 'Kostenkategorie auswählen', 'kostenkategorie', $KOSTENKATEGORIE );
			// $form->text_feld_inaktiv('Kostenkategorie', 'kostenkategorie', $KOSTENKATEGORIE, strlen($KOSTENKATEGORIE));
			$form->text_feld ( 'Anfang', 'anfang', $ANFANG, strlen ( $ANFANG ) );
			// $form->text_feld_inaktiv('Anfang', 'anfang', $ANFANG, strlen($ANFANG));
			// $form->hidden_feld('anfang', $ANFANG);
			if ($ENDE == '0000-00-00') {
				$ENDE = '';
				$form->text_feld ( 'Ende', 'ende', $ENDE, '10' );
			} else {
				$ENDE = date_mysql2german ( $ENDE );
				$form->text_feld ( 'Ende', 'ende', $ENDE, strlen ( $ENDE ) );
			}
			$BETRAG = $form->nummer_punkt2komma ( $BETRAG );
			$form->text_feld ( 'Betrag', 'betrag', $BETRAG, strlen ( $BETRAG ) );
			$js_mwst = "onclick=\"mwst_rechnen('betrag','mwst', '19')\" ondblclick=\"mwst_rechnen('betrag','mwst', '7')\"";
			$form->text_feld_js ( 'MWST-Anteil', 'mwst', nummer_punkt2komma ( $MWST_ANTEIL ), strlen ( $MWST_ANTEIL ), 'mwst', $js_mwst );
			// $form->hidden_feld('betrag', $BETRAG);
			$form->hidden_feld ( 'dat', $dat );
			$form->hidden_feld ( 'me_id', $MIETENTWICKLUNG_ID );
			$form->hidden_feld ( 'mv_id', $KOSTENTRAEGER_ID );
			$form->hidden_feld ( 'option', 'andern_dat_speichern' );
			$form->send_button ( 'btn_aendern_dat', 'Ändern' );
			// echo "$KOSTENKATEGORIE, $BETRAG";
		}
	}
	function me_dat_aendern() {
		$me_dat =request()->input('dat');
		/* Deaktivieren von ME-zeile */
		$db_abfrage = "UPDATE MIETENTWICKLUNG SET MIETENTWICKLUNG_AKTUELL='0' where MIETENTWICKLUNG_DAT='$me_dat'";
		$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		
		/* Neue Zeile */
		$me_id = request()->input('me_id');
		$mv_id = request()->input('mv_id');
		$anfang = request()->input('anfang');
		$anfang = date_german2mysql ( $anfang );
		$ende = request()->input('ende');
		if (! request()->has('ende')) {
			$ende = '00.00.0000';
		}
		$ende = date_german2mysql ( $ende );
		$kostenkat = request()->input('kostenkategorie');
		$betrag = request()->input('betrag');
		$betrag = nummer_komma2punkt ( $betrag );
		
		$mwst_anteil = request()->input('mwst');
		$mwst_anteil = nummer_komma2punkt ( $mwst_anteil );
		
		$db_abfrage = "INSERT INTO MIETENTWICKLUNG VALUES (NULL, '$me_id', 'MIETVERTRAG', '$mv_id', '$kostenkat', '$anfang', '$ende', '$mwst_anteil', '$betrag', '1')";
		$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		/* Zugewiesene MIETBUCHUNG_DAT auslesen */
		$last_dat = mysql_insert_id ();
		protokollieren ( 'MIETENTWICKLUNG', $last_dat, $me_dat );
	}
	function me_dat_aendern2($dat, $kos_typ, $kos_id, $anfang, $ende, $kat, $betrag, $mwst) {
		/* Deaktivieren von ME-zeile */
		$db_abfrage = "UPDATE MIETENTWICKLUNG SET MIETENTWICKLUNG_AKTUELL='0' where MIETENTWICKLUNG_DAT='$dat'";
		$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		
		/* Neue Zeile */
		$anfang = $anfang;
		$ende = $ende;
		$betrag = $betrag;
		$mwst_anteil = $mwst;
		$last_id = last_id2 ( 'MIETENTWICKLUNG', 'MIETENTWICKLUNG_ID' ) + 1;
		
		$db_abfrage = "INSERT INTO MIETENTWICKLUNG VALUES (NULL, '$last_id', '$kos_typ', '$kos_id', '$kat', '$anfang', '$ende', '$mwst_anteil', '$betrag', '1')";
		$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		
		/* Zugewiesene MIETBUCHUNG_DAT auslesen */
		$last_dat = mysql_insert_id ();
		protokollieren ( 'MIETENTWICKLUNG', $last_dat, $dat );
		return true;
	}
	function me_dat_neu_form($mv_id) {
		echo "<a href='" . route('legacy::miete_definieren::index', ['option' => 'staffel_eingabe', 'mv_id' => $mv_id]) . "'>Staffel eingeben</a><hr>";
		$form = new mietkonto ();
		$f = new formular ();
		$form->dropdown_me_kostenkategorien ( 'Kostenkategorie auswählen', 'kostenkategorie', session()->get('me_kostenkat'));
		// $form->text_feld('Anfang', 'anfang', '', '10');
		// $form->text_feld('Ende', 'ende', '', '10');
		// datum_feld($beschreibung, $name, $wert, $id)
		if (session()->has('a_datum')) {
			$a_datum = session()->get('a_datum');
		} else {
			$a_datum = '';
		}
		
		if (session()->has('e_datum')) {
			$e_datum = session()->get('e_datum');
		} else {
			$e_datum = '';
		}
		
		$f->datum_feld ( 'Anfang', 'anfang', $a_datum, 'anfang' );
		$f->datum_feld ( 'Ende', 'ende', $e_datum, 'ende' );
		$form->text_feld ( 'Betrag', 'betrag', '', '10' );
		$js_mwst = "onclick=\"mwst_rechnen('betrag','mwst', '19')\" ondblclick=\"mwst_rechnen('betrag','mwst', '7')\"";
		$form->text_feld_js ( 'MWST-Anteil', 'mwst', '0,00', 10, 'mwst', $js_mwst );
		$form->hidden_feld ( 'mv_id', $mv_id );
		$form->hidden_feld ( 'option', 'me_neu_speichern' );
		$form->send_button ( 'btn_hinzu_dat', 'Hinzufügen' );
	}
	function me_dat_neu_speichern() {
		/* Neue Zeile */
		$form = new mietkonto ();
		
		$mv_id = request()->input('mv_id');
		$anfang = request()->input('anfang');
		$anfang = $form->date_german2mysql ( $anfang );
		$ende = request()->input('ende');
		if (!request()->has('ende')) {
			$ende = '00.00.0000';
		}
		$ende = $form->date_german2mysql ( $ende );
		$kostenkat = request()->input('kostenkategorie');
		$betrag = request()->input('betrag');
		$betrag = $form->nummer_komma2punkt ( $betrag );
		$mwst_anteil = request()->input('mwst');
		$mwst_anteil = $form->nummer_komma2punkt ( $mwst_anteil );
		
		$me_id = $form->get_mietentwicklung_last_id ();
		$me_id = $me_id + 1;
		$db_abfrage = "INSERT INTO MIETENTWICKLUNG VALUES (NULL, '$me_id', 'MIETVERTRAG', '$mv_id', '$kostenkat', '$anfang', '$ende', '$mwst_anteil', '$betrag', '1')";
		$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		/* Zugewiesene MIETBUCHUNG_DAT auslesen */
		$last_dat = mysql_insert_id ();
		protokollieren ( 'MIETENTWICKLUNG', '0', $last_dat );
	}
	function me_speichern($kos_typ, $kos_id, $kat, $anfang, $ende, $betrag, $mwst_anteil) {
		$last_id = last_id2 ( 'MIETENTWICKLUNG', 'MIETENTWICKLUNG_ID' ) + 1;
		
		$db_abfrage = "INSERT INTO MIETENTWICKLUNG VALUES (NULL, '$last_id', '$kos_typ', '$kos_id', '$kat', '$anfang', '$ende', '$mwst_anteil', '$betrag', '1')";
		$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		/* Zugewiesene MIETBUCHUNG_DAT auslesen */
		$last_dat = mysql_insert_id ();
		protokollieren ( 'MIETENTWICKLUNG', '0', $last_dat );
	}
	function me_dat_loeschen($me_dat) {
		$db_abfrage = "UPDATE MIETENTWICKLUNG SET MIETENTWICKLUNG_AKTUELL='0' WHERE MIETENTWICKLUNG_DAT='$me_dat'";
		$result = mysql_query ( $db_abfrage ) or die ( mysql_error () );
	}
	function alle_letzten_kaltmieten_arr() {
		mysql_query ( "SET OPTION SQL_BIG_SELECTS=1" );
		$db_abfrage = "SELECT ANFANG, ENDE, EINHEIT_KURZNAME, EINHEIT.EINHEIT_ID, MIETENTWICKLUNG.MIETVERTRAG_ID, KOSTENKATEGORIE, BETRAG FROM `MIETENTWICKLUNG` JOIN (MIETVERTRAG, EINHEIT) ON (MIETENTWICKLUNG.MIETVERTRAG_ID = MIETVERTRAG.MIETVERTRAG_ID && MIETVERTRAG.EINHEIT_ID = EINHEIT.EINHEIT_ID) WHERE KOSTENKATEGORIE='Miete kalt' && MIETENTWICKLUNG_AKTUELL='1' GROUP BY EINHEIT_KURZNAME ORDER BY `EINHEIT`.`EINHEIT_KURZNAME` ASC";
		
		$result = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		while ( $row = mysql_fetch_assoc ( $result ) )
			$my_arr [] = $row;
		return $my_arr;
	}
	function alle_letzten_nebenkosten_arr() {
		mysql_query ( "SET OPTION SQL_BIG_SELECTS=1" );
		$db_abfrage = "SELECT ANFANG, ENDE, EINHEIT_KURZNAME, EINHEIT.EINHEIT_ID, MIETENTWICKLUNG.MIETVERTRAG_ID, KOSTENKATEGORIE, BETRAG FROM `MIETENTWICKLUNG` JOIN (MIETVERTRAG, EINHEIT) ON (MIETENTWICKLUNG.MIETVERTRAG_ID = MIETVERTRAG.MIETVERTRAG_ID && MIETVERTRAG.EINHEIT_ID = EINHEIT.EINHEIT_ID) WHERE KOSTENKATEGORIE='Nebenkosten Vorauszahlung' && MIETENTWICKLUNG_AKTUELL='1' GROUP BY EINHEIT_KURZNAME ORDER BY `EINHEIT`.`EINHEIT_KURZNAME` ASC";
		
		$result = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		while ( $row = mysql_fetch_assoc ( $result ) )
			$my_arr [] = $row;
		return $my_arr;
	}
	function alle_letzten_heizkosten_arr() {
		mysql_query ( "SET OPTION SQL_BIG_SELECTS=1" );
		$db_abfrage = 'SELECT ANFANG, ENDE, EINHEIT_KURZNAME, EINHEIT.EINHEIT_ID, MIETENTWICKLUNG.MIETVERTRAG_ID, KOSTENKATEGORIE, BETRAG FROM `MIETENTWICKLUNG` JOIN (MIETVERTRAG, EINHEIT) ON (MIETENTWICKLUNG.MIETVERTRAG_ID = MIETVERTRAG.MIETVERTRAG_ID && MIETVERTRAG.EINHEIT_ID = EINHEIT.EINHEIT_ID) WHERE KOSTENKATEGORIE=\'Heizkosten Vorauszahlung\' && MIETENTWICKLUNG_AKTUELL=\'1\' GROUP BY EINHEIT_KURZNAME ORDER BY `EINHEIT`.`EINHEIT_KURZNAME` ASC';
		
		$result = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		while ( $row = mysql_fetch_assoc ( $result ) )
			$my_arr [] = $row;
		return $my_arr;
	}
	function mietdefinition_zu_details() {
		// $daten_arr = $this->alle_letzten_kaltmieten_arr();
		// print_r($daten_arr);
		// $daten_arr = $this->alle_letzten_nebenkosten_arr();
		// $daten_arr = $this->alle_letzten_heizkosten_arr();
		for($a = 0; $a < count ( $daten_arr ); $a ++) {
			$anfang = $daten_arr [$a] ['ANFANG'];
			$ende = $daten_arr [$a] ['ENDE'];
			$einheit_id = $daten_arr [$a] ['EINHEIT_ID'];
			$einheit_kurzname = $daten_arr [$a] ['EINHEIT_KURZNAME'];
			$kosten_kat = $daten_arr [$a] ['KOSTENKATEGORIE'];
			$einheit_betrag = $daten_arr [$a] ['BETRAG'];
			$last_detail_id = last_id ( 'DETAIL' );
			$last_detail_id = $last_detail_id + 1;
			$sql = "INSERT INTO DETAIL VALUES(NULL, '$last_detail_id', '$kosten_kat', '$einheit_betrag', '$einheit_kurzname $anfang bis $ende', '1', 'EINHEIT','$einheit_id')";
			echo " '$last_detail_id', '$kosten_kat', '$einheit_betrag', '$einheit_kurzname $anfang bis $ende', '1', 'EINHEIT','$einheit_id'<br>";
			
			// $resultat = mysql_query($sql) or
			die ( mysql_error () );
			/* Zugewiesene MIETBUCHUNG_DAT auslesen */
			$last_dat = mysql_insert_id ();
			protokollieren ( 'DETAIL', $last_dat, '0' );
		}
	}
	function mieterlisten_kostenkat($kosten_kat) {
		$db_abfrage = "SELECT KOSTENKATEGORIE, ANFANG, ENDE, BETRAG, KOSTENTRAEGER_TYP, KOSTENTRAEGER_ID, MIETVERTRAG.EINHEIT_ID, EINHEIT_KURZNAME
FROM `MIETENTWICKLUNG` , MIETVERTRAG, EINHEIT
WHERE `KOSTENKATEGORIE` LIKE '$kosten_kat'
AND `ENDE` = '0000-00-00'
AND `MIETENTWICKLUNG_AKTUELL` = '1' && `MIETVERTRAG_AKTUELL` = '1' && `EINHEIT_AKTUELL` = '1' && KOSTENTRAEGER_TYP = 'MIETVERTRAG' && KOSTENTRAEGER_ID = MIETVERTRAG_ID && MIETVERTRAG.EINHEIT_ID = EINHEIT.EINHEIT_ID";
		$result = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			echo "<table class=\"sortable\">";
			echo "<tr><th>Kostenkategorie</th><th>ANFANG</th><th>ENDE</th><th>BETRAG</th><th>MV_ID</th><th>EINHEIT</th></tr>";
			while ( $row = mysql_fetch_assoc ( $result ) ) {
				$kostenkat = $row ['KOSTENKATEGORIE'];
				$anfang = $row ['ANFANG'];
				$ende = $row ['ENDE'];
				$mv_id = $row ['KOSTENTRAEGER_ID'];
				$einheit_kurzname = $row ['EINHEIT_KURZNAME'];
				$betrag = $row ['BETRAG'];
				echo "<tr><td>$kostenkat</td><td>$anfang</td><td>$ende</td><td>$betrag</td><td>$mv_id</td><td>$einheit_kurzname</td></tr>";
			}
			echo "</table>";
		} else {
			echo "Keine Mietdefinition zu $kosten_kat";
		}
	}
	function form_staffel($mv_id) {
		$f = new formular ();
		echo "<table>";
		echo "<tr><th>MIETE</th><th>Anfang</th><th>Endjahr</th><th>Betrag</th><th>Prozent oder Betrag</th><th>MWST</th></tr>";
		echo "<tr><th colspan=\"6\">";
		$js_staffel = "onclick=\"staffel_berechnen('anfang', 'endjahr', 'betrag', 'prozent_betrag', 'mwst')\"";
		// staffel_berechnen(a_datum, endjahr, betrag, prozent_betrag, mwst)
		$f->button_js ( 'berechnen_stf', 'Staffel berechnen', $js_staffel );
		echo "</th></tr>";
		echo "<tr>";
		$form = new mietkonto ();
		
		echo "<td>";
		if (! session()->has('me_kostenkat')) {
			session()->put('me_kostenkat', 'Miete kalt');
		}
		$form->dropdown_me_kostenkategorien ( 'Kostenkategorie auswählen', 'kostenkategorie[]', session()->get('me_kostenkat'));
		echo "</td>";
		if (session()->has('a_datum')) {
			$a_datum = session()->get('a_datum');
		} else {
			$a_datum = date ( "d.m.Y" );
		}
		
		if (session()->has('e_datum')) {
			$e_datum = session()->get('e_datum');
		} else {
			$e_datum = '';
		}
		
		echo "<td>";
		$f->datum_feld ( 'Anfang', 'anfang[]', $a_datum, 'anfang' );
		echo "</td><td>";
		// $f->datum_feld('Ende', 'ende[]', $e_datum, 'ende');
		$jj10 = date ( "Y" ) + 10;
		
		$form->text_feld ( 'Endjahr', 'endjahr', $jj10, '4' );
		echo "</td><td>";
		$form->text_feld ( 'Betrag', 'betrag', '500,00', '10' );
		echo "</td><td>";
		$form->text_feld ( 'prozent_betrag', 'prozent_betrag', '3%', '10' );
		echo "</td><td>";
		
		$js_mwst = "onclick=\"mwst_rechnen('betrag','mwst', '19')\" ondblclick=\"mwst_rechnen('betrag[0]','mwst', '7')\"";
		$form->text_feld_js ( 'MWST-Anteil', 'mwst', '0,00', 10, 'mwst', $js_mwst );
		echo "</td>";
		echo "</tr>";
		$form->hidden_feld ( 'mv_id', $mv_id );
		$form->hidden_feld ( 'option', 'me_neu_speichern1' );
		// $form->send_button('btn_hinzu_dat', 'Hinzufügen');
	}
	function check_me($kos_typ, $kos_id, $kat, $anfang, $ende) {
		$result = mysql_query ( "SELECT *
			FROM  `MIETENTWICKLUNG`
			WHERE  `KOSTENTRAEGER_TYP` =  '$kos_typ'
			AND `KOSTENTRAEGER_ID` =  '$kos_id'
			AND  `KOSTENKATEGORIE` =  '$kat'
			AND  `ANFANG` =  '$anfang'
			AND  `ENDE` =  '$ende'
			AND  `MIETENTWICKLUNG_AKTUELL` =  '1'
			LIMIT 0 , 1" );
		
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			return true;
		}
	}
	function get_kostenkat_info_aktuell($mietvertrag_id, $monat, $jahr, $kostenkat = 'Nebenkosten Vorauszahlung') {
		$result = mysql_query ( "SELECT * FROM MIETENTWICKLUNG WHERE KOSTENTRAEGER_TYP='MIETVERTRAG' && KOSTENTRAEGER_ID = '$mietvertrag_id' && MIETENTWICKLUNG_AKTUELL = '1' && ( ENDE = '0000-00-00' OR DATE_FORMAT( ENDE, '%Y-%m' ) >= '$jahr-$monat') && DATE_FORMAT( ANFANG, '%Y-%m' ) <= '$jahr-$monat'  && KOSTENKATEGORIE='$kostenkat' ORDER BY ANFANG DESC LIMIT 0,1" );
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			$row = mysql_fetch_assoc ( $result );
			return $row;
		} else {
			return false;
		}
	}
	function dropdown_me_bk_hk($beschreibung, $name, $kostenkategorie) {
		echo "<div class='input-field'>";
		echo "<select name=\"$name\" id=\"$name\" onchange=\"this.form.submit()\"> \n";
		
		$jahr = date ( "Y" ) - 1;
		$vorjahr = $jahr;
		// $kostenkategorien_arr = array();
		
		// print_r($kostenkategorien_arr);
		/*
		 * $kostenkategorien_arr[] = 'Kaltwasserabrechnung 2013';
		 * $kostenkategorien_arr[] = 'Kaltwasserabrechnung 2012';
		 * $kostenkategorien_arr[] = 'Kaltwasserabrechnung 2011';
		 * $kostenkategorien_arr[] = 'Betriebskostenabrechnung 2011';
		 * $kostenkategorien_arr[] = 'Heizkostenabrechnung 2011';
		 * $kostenkategorien_arr[] = 'Wasserkostenabrechnung 2011';
		 * $kostenkategorien_arr[] = 'Betriebskostenabrechnung 2012';
		 * $kostenkategorien_arr[] = 'Heizkostenabrechnung 2012';
		 * $kostenkategorien_arr[] = 'Wasserkostenabrechnung 2012';
		 * $kostenkategorien_arr[] = 'Betriebskostenabrechnung 2010';
		 * $kostenkategorien_arr[] = 'Heizkostenabrechnung 2010';
		 * #$kostenkategorien_arr[] = 'Nebenkostenabrechnung 2010';
		 * $kostenkategorien_arr[] = 'Wasserkostenabrechnung 2010';
		 * $kostenkategorien_arr[] = 'Betriebskostenabrechnung 2009';
		 * $kostenkategorien_arr[] = 'Heizkostenabrechnung 2009';
		 * $kostenkategorien_arr[] = 'Wasserkostenabrechnung 2009';
		 */
		/*
		 * $kostenkategorien_arr[] = 'Betriebskostenabrechnung Korr. 2007';
		 * $kostenkategorien_arr[] = 'Heizkostenabrechnung Korr. 2007';
		 * $kostenkategorien_arr[] = 'Wasserkostenabrechnung Korr. 2007';
		 */
		// $kostenkategorien_arr[] = 'Miete kalt';
		$kostenkategorien_arr [] = 'Heizkosten Vorauszahlung';
		$kostenkategorien_arr [] = 'Nebenkosten Vorauszahlung';
		$kostenkategorien_arr [] = 'Nebenkosten VZ - Anteilig';
		// $kostenkategorien_arr[] = 'Kabel TV';
		// $kostenkategorien_arr[] = 'Untermieter Zuschlag';
		// $kostenkategorien_arr[] = 'MOD';
		// $kostenkategorien_arr[] = 'MHG';
		// $kostenkategorien_arr[] = 'Mahngebühr';
		// $kostenkategorien_arr[] = 'Ratenzahlung';
		// $kostenkategorien_arr[] = 'Saldo Vortrag Vorverwaltung';
		// $kostenkategorien_arr[] = 'Mietminderung';
		
		// echo '<pre>';
		// print_r($kostenkategorien_arr);
		for($a = $jahr; $a >= $vorjahr; $a --) {
			$kostenkategorien_arr [] = "Betriebskostenabrechnung $a";
			$kostenkategorien_arr [] = "Heizkostenabrechnung $a";
			$kostenkategorien_arr [] = "Kaltwasserabrechnung $a";
		}
		
		/*
		 * for ($a=$jahr;$a>=$vorjahr;$a--){
		 * $kostenkategorien_arr[] = "Energieverbrauch lt. Abr. $a";
		 * }
		 */
		
		// $kk = sort($kostenkategorien_arr);
		// echo '<pre>';
		// print_r($kostenkategorien_arr);
		
		$anzahl_kats = count ( $kostenkategorien_arr );
		for($a = 0; $a < $anzahl_kats; $a ++) {
			$katname_value = $kostenkategorien_arr [$a];
			echo $katname_value;
			if ($katname_value == $kostenkategorie) {
				echo "<option value=\"$katname_value\" selected>$katname_value</option>\n";
			} else {
				echo "<option value=\"$katname_value\">$katname_value</option>\n";
			}
		}
		echo "</select><label for=\"$name\">$beschreibung</label>";
		echo "<div>";
	}
	function get_dat_info($dat) {
		$db_abfrage = "SELECT * FROM MIETENTWICKLUNG where MIETENTWICKLUNG_DAT='$dat'";
		$result = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			$row = mysql_fetch_assoc ( $result );
			return $row;
		}
	}
} // end class ME
