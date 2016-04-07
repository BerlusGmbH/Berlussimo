<?php
/**
 * BERLUSSIMO
 *
 * Hausverwaltungssoftware
 *
 *
 * @copyright    Copyright (c) 2010, Berlus GmbH, Fontanestr. 1, 14193 Berlin
 * @link         http://www.berlus.de
 * @author       Sanel Sivac & Wolfgang Wehrheim
 * @contact		 software(@)berlus.de
 * @license     http://www.gnu.org/licenses/agpl.html AGPL Version 3
 * 
 * @filesource   $HeadURL: http://192.168.2.52/svn/berlussimo_1/tags/02.11.2010 - Downloadversion 0.27/classes/mietkonto_class.php $
 * @version      $Revision: 15 $
 * @modifiedby   $LastChangedBy: sivac $
 * @lastmodified $Date: 2011-07-07 10:41:33 +0200 (Do, 07 Jul 2011) $
 * 
 */

/*
 * Das ist eine Test bzw. Spieledatei, aus der kann man viel lernen,
 * leider die Funktionen und vars nicht in Deutsch
 */

// include_once("config.inc.php");
if (file_exists ( 'config.inc.php' )) {
	include_once ('config.inc.php');
}
if (file_exists ( 'classes/berlussimo_class.php' )) {
	include_once ('classes/berlussimo_class.php');
}

if (file_exists ( 'classes/mietzeit_class.php' )) {
	include_once ('classes/mietzeit_class.php');
}
if (file_exists ( 'classes/class_buchen.php' )) {
	include_once ('classes/class_buchen.php');
}

/*
 * include_once("classes/berlussimo_class.php");
 * include_once("classes/mietzeit_class.php");
 * include_once("classes/class_buchen.php");
 */
class mietkonto {
	/* Allgemeine Variablen */
	/*
	 * var $datum_heute;
	 * var $tag_heute;
	 * var $monat_heute;
	 * var $jahr_heute;
	 *
	 *
	 *
	 * /* Diese Variablen werden von mietvertrag_grunddaten_holen($mv) gesetzt
	 */
	/*
	 * var $mietvertrag_von;
	 * var $mietvertrag_bis;
	 * var $anzahl_personen_im_vertrag;
	 * var $einheit_kurzname;
	 *
	 * /* Variablen aus Forderungen
	 */
	/*
	 * var $monatsname;
	 * var $ausgangs_kaltmiete;
	 * var $betriebskosten;
	 * var $heizkosten;
	 * var $miete;
	 *
	 * var $einheit_kurzname_von_mv;
	 * /*Testfunktion
	 *
	 */
	
	/* Funktion-Konstruktor der jedes Mal aufgerufen wird für db connect */
	function mietkonto() {
		// $this->connectToBase();
		$this->datum_heute = date ( "Y-m-d" );
		$this->tag_heute = date ( "d" );
		$this->monat_heute = date ( "m" );
		$this->jahr_heute = date ( "Y" );
	}
	
	/* DB Verbindung */
	function connectToBase() {
		mysql_connect ( DB_HOST, DB_USER, DB_PASS );
		mysql_set_charset('utf8',$con);
		mysql_select_db ( DB_NAME );
	}
	/* Datumsfunktionen */
	function date_mysql2german($date) {
		$d = explode ( "-", $date );
		return sprintf ( "%02d.%02d.%04d", $d [2], $d [1], $d [0] );
	}
	function date_german2mysql($date) {
		$d = explode ( ".", $date );
		return sprintf ( "%04d-%02d-%02d", $d [2], $d [1], $d [0] );
	}
	
	/* Funktion um Grunddaten aus dem Mietvertrag zu holen */
	function mietvertrag_grunddaten_holen($mietvertrag_id) {
		$result = mysql_query ( "SELECT * FROM MIETVERTRAG WHERE MIETVERTRAG_AKTUELL='1' && MIETVERTRAG_ID='$mietvertrag_id' ORDER BY MIETVERTRAG_DAT DESC LIMIT 0,1" );
		$row = mysql_fetch_assoc ( $result );
		// Setzen von Mietvertrags Vars bzw Einzugsdatum Auszugsdatum
		$this->mietvertrag_von = $row ['MIETVERTRAG_VON'];
		$this->mietvertrag_bis = $row ['MIETVERTRAG_BIS'];
		// Ermitteln von Anzahl Personen aus dem MV
		$this->get_anzahl_personen_zu_mietvertrag ( $mietvertrag_id );
		// Ermitteln und Übergabe von Array mit personen_ids im Mietvertrag
		// $personen_ids = $this->get_personen_ids_mietvertrag($mietvertrag_id);
		// return $personen_ids;
	}
	function ein_auszugsdatum_mietvertrag($mietvertrag_id) {
		$result = mysql_query ( "SELECT MIETVERTRAG_VON, MIETVERTRAG_BIS FROM MIETVERTRAG WHERE MIETVERTRAG_AKTUELL='1' && MIETVERTRAG_ID='$mietvertrag_id' ORDER BY MIETVERTRAG_DAT DESC LIMIT 0,1" );
		$row = mysql_fetch_assoc ( $result );
		// Setzen von Mietvertrags Vars bzw Einzugsdatum Auszugsdatum
		$this->mietvertrag_von = $row ['MIETVERTRAG_VON'];
		$this->mietvertrag_bis = $row ['MIETVERTRAG_BIS'];
	}
	
	/* Funktion um Personenanzahl im MV zu ermitteln */
	function get_anzahl_personen_zu_mietvertrag($mietvertrag_id) {
		$result = mysql_query ( "SELECT PERSON_MIETVERTRAG_ID FROM PERSON_MIETVERTRAG WHERE PERSON_MIETVERTRAG_MIETVERTRAG_ID='$mietvertrag_id' && PERSON_MIETVERTRAG_AKTUELL='1' ORDER BY PERSON_MIETVERTRAG_ID ASC" );
		$anzahl = mysql_numrows ( $result );
		$this->anzahl_personen_im_vertrag = $anzahl; // Anzahl aller Personen im MV
	}
	function get_person_infos($person_id) {
		$result = mysql_query ( "SELECT * FROM PERSON WHERE PERSON_AKTUELL='1' && PERSON_ID='$person_id' ORDER BY PERSON_DAT DESC LIMIT 0,1" );
		while ( $row = mysql_fetch_assoc ( $result ) )
			$my_array [] = $row;
			// Übergabe eines Arrays mit Personendaten
		return $my_array;
	}
	
	/* Ausgabe der Personen_ids als array die im MV stehen */
	function get_personen_ids_mietvertrag($mietvertrag_id) {
		$result = mysql_query ( "SELECT PERSON_MIETVERTRAG_PERSON_ID FROM PERSON_MIETVERTRAG WHERE PERSON_MIETVERTRAG_MIETVERTRAG_ID='$mietvertrag_id' && PERSON_MIETVERTRAG_AKTUELL='1' ORDER BY PERSON_MIETVERTRAG_ID ASC" );
		$my_array = array ();
		while ( $row = mysql_fetch_assoc ( $result ) )
			$my_array [] = $row;
		return $my_array;
	}
	
	/* Einheit_id aus dem MV auslesen */
	function get_einheit_id_von_mietvertrag($mietvertrag_id) {
		$result = mysql_query ( "SELECT EINHEIT_ID FROM MIETVERTRAG WHERE MIETVERTRAG_AKTUELL='1' && MIETVERTRAG_ID='$mietvertrag_id' ORDER BY MIETVERTRAG_DAT DESC LIMIT 0,1" );
		$row = mysql_fetch_assoc ( $result );
		// $this->get_einheit_info($row[EINHEIT_ID]);
		return $row ['EINHEIT_ID'];
	}
	
	/* Suche nach Positionen der BK und HK in forderungen */
	function suche_nach_vorschuessen($array) {
		for($i = 0; $i < count ( $array ); $i ++) {
			if (array_search ( 'Nebenkosten Vorauszahlung', $array [$i] )) {
				$vorschuesse [] = $i;
			}
			if (array_search ( 'Heizkosten Vorauszahlung', $array [$i] )) {
				$vorschuesse [] = $i;
			}
		}
		if (isset ( $vorschuesse )) {
			return $vorschuesse;
		}
	}
	function summe_vorschuesse($forderung_arr) {
		$vorschuesse_arr = $this->suche_nach_vorschuessen ( $forderung_arr );
		// $this->array_anzeigen($vorschuesse_arr);
		$summe_hk_bk = 0;
		for($a = 0; $a < count ( $vorschuesse_arr ); $a ++) {
			// Durchlauf von Array mit Betraegen und Summierung
			$summe_hk_bk += $forderung_arr [$vorschuesse_arr [$a]] ['BETRAG'];
		}
		if (isset ( $summe_hk_bk )) {
			return $summe_hk_bk;
		}
	}
	
	/* Funktion zur Darstellung der letzten Buchungen objektunabhängig aus dem Vormonat und dem aktuellen Monat */
	function letzte_buchungen_anzeigen_vormonat_monat() {
		/* Wenn kein Objekt ausgewählt wurde, alle Zahlungen in letzten 2 Monaten anzeigen */
		
		/*
		 * $result = mysql_query ("SELECT *
		 * FROM MIETE_ZAHLBETRAG WHERE AKTUELL = '1' && DATUM >= DATE_SUB( DATE_FORMAT( CURDATE( ) , '%Y-%m-%d' ) , INTERVAL 2 MONTH )
		 * ORDER BY BUCHUNGSNUMMER DESC");
		 * }
		 */
		
		/* Wenn ein Objekt ausgewählt wurde, Kontonummer suchen */
		if (isset ( $_SESSION ['objekt_id'] )) {
			$objekt_info = new objekt ();
			$objekt_info->get_objekt_geldkonto_nr ( $_SESSION ['objekt_id'] );
			$objekt_kontonummer = $objekt_info->objekt_kontonummer;
			/* Wenn ein Objekt ausgewählt wurde das eine Kontonummer hat, alle Zahlbeträge von diesem Konto selektieren */
			if (isset ( $objekt_kontonummer )) {
				$result = mysql_query ( "SELECT *
FROM MIETE_ZAHLBETRAG WHERE AKTUELL = '1' && KONTO='$objekt_kontonummer' && DATUM >= DATE_SUB( DATE_FORMAT( CURDATE( ) , '%Y-%m-1' ) , INTERVAL 1 MONTH )
ORDER BY BUCHUNGSNUMMER DESC" );
			} 			/* Wenn ein Objekt ausgewählt wurde das KEINE Kontonummer hat, werden wie oben alle Zahlbeträge der letzten 2 Monate selektiert */
			else {
				echo "<div class=\"info_feld_oben\">";
				warnung_ausgeben ( "Das Objekt verfügt über KEINE Geldkontonummer.<br>Keine Zahlungsvorgänge vorhanden.<br>Bitte Geldkontonummer in den Details vom Objekt anlegen." );
				echo "</div>";
			}
		}
		
		if (isset ( $result )) {
			$numrows = mysql_numrows ( $result );
			echo "<div class=\"tabelle\">";
			$this->erstelle_formular ( "Buchungen 2 Mon.", NULL );
			if ($numrows > 0) {
				while ( $row = mysql_fetch_assoc ( $result ) )
					$my_array [] = $row;
				echo "<table>";
				echo "<tr class=\"feldernamen\"><td>BNR</td><td>AUSZUG</td><td>DATUM</td><td>EINHEIT</td><td>BETRAG</td><td>BEMERKUNG</td><td>Option</td></tr>\n";
				for($i = 0; $i < count ( $my_array ); $i ++) {
					
					$datum = date_mysql2german ( $my_array [$i] [DATUM] );
					$einheit_info = new mietvertrag ();
					$einheit_id = $einheit_info->get_einheit_id_von_mietvertrag ( $my_array [$i] ['mietvertrag_id'] );
					$einheit_info = new einheit ();
					$einheit_info->get_einheit_info ( $einheit_id );
					$einheit_kurzname = $einheit_info->einheit_kurzname;
					$buchungsnummer = $my_array [$i] ['BUCHUNGSNUMMER'];
					$bemerkung = $my_array [$i] [BEMERKUNG];
					$kontoauszugsnr = $my_array [$i] [KONTOAUSZUGSNR];
					
					$buchungsdatum = $my_array [$i] [DATUM];
					$buchungsmonat_arr = explode ( '-', $buchungsdatum );
					$buchungsmonat = $buchungsmonat_arr [1];
					$buchungsjahr = $buchungsmonat_arr [0];
					$kontoauszugsnr = $my_array [$i] [KONTOAUSZUGSNR];
					/* Prüfen ob diesen Monat gebucht wurde */
					// $this->array_anzeigen($my_array);
					// $anzahl_zahlbetraege_diesen_monat = $this->anzahl_zahlungen_diesen_monat();
					// $anzahl_zahlbetraege_diesen_monat = $this->anzahl_zahlungsvorgaenge($my_array[$i]['mietvertrag_id']);
					$anzahl_zahlbetraege_diesen_monat = $this->anzahl_zahlungsvorgaenge_objekt_konto ( $_SESSION ['objekt_id'] );
					// echo $anzahl_zahlbetraege_diesen_monat;
					if ($anzahl_zahlbetraege_diesen_monat > 0) {
						// echo "Diesen Monat wurde gebucht $this->monat_heute $buchungsmonat $buchungsjahr";
						if ($this->monat_heute > $buchungsmonat) {
							$storno_link = '';
						} else {
							
							$storno_link = "<a href=\"?daten=miete_buchen&schritt=stornieren&bnr=$buchungsnummer\">Stornieren</a>";
						}
					} else {
						$storno_link = "<a href=\"?daten=miete_buchen&schritt=stornieren&bnr=$buchungsnummer\">Stornieren</a>";
					}
					
					echo "<tr class=\"zeile1\"><td>$buchungsnummer</td><td>$kontoauszugsnr</td><td>$datum</td><td>$einheit_kurzname</td><td>" . $my_array [$i] ['BETRAG'] . " €</td><td>$bemerkung</td><td>$storno_link</td></tr>\n";
				}
				echo "</table>";
			} else {
				echo "Keine Buchungen auf dem Objektgeldkonto $objekt_kontonummer!";
			}
			$this->ende_formular ();
			echo "</div>";
		}
	}
	
	/* Funktion zur Berechnung der Summen der gebuchten Zahlbeträge alle Kontoauszüge aus dem aktuellen Monat */
	function summen_kontoauszuege_aktuell($konto) {
		$result = mysql_query ( "SELECT KONTOAUSZUGSNR , SUM( BETRAG ) AS SUMME
, COUNT(*) AS ZAHLUNGSVORGANG FROM MIETE_ZAHLBETRAG
WHERE AKTUELL = '1' && KONTO = '$konto' && DATUM >= DATE_SUB( DATE_FORMAT( CURDATE( ) , '%Y-%m-%d' ) , INTERVAL 1 MONTH )
GROUP BY KONTOAUSZUGSNR
ORDER BY BUCHUNGSNUMMER DESC" );
		$numrows = mysql_numrows ( $result );
		if ($numrows > 0) {
			while ( $row = mysql_fetch_assoc ( $result ) )
				$my_array [] = $row;
		}
		$this->array_anzeigen ( $my_array );
	}
	function letzte_buchungen_anzeigen() {
		if (isset ( $_SESSION ['objekt_id'] )) {
			$objekt_info = new objekt ();
			$objekt_info->get_objekt_geldkonto_nr ( $_SESSION ['objekt_id'] );
			$objekt_kontonummer = $objekt_info->objekt_kontonummer;
			if (isset ( $objekt_kontonummer )) {
				$result = mysql_query ( " SELECT * FROM `MIETE_ZAHLBETRAG` WHERE AKTUELL='1' && KONTO='$objekt_kontonummer' ORDER BY BUCHUNGSNUMMER DESC" );
				// echo "KN OK";
			}
		} else {
			$result = mysql_query ( " SELECT * FROM `MIETE_ZAHLBETRAG` WHERE AKTUELL='1' ORDER BY BUCHUNGSNUMMER DESC" );
		}
		
		$numrows = mysql_numrows ( $result );
		echo "<div class=\"tabelle\">";
		$this->erstelle_formular ( "Letzte Buchungen", NULL );
		if ($numrows > 0) {
			while ( $row = mysql_fetch_assoc ( $result ) )
				$my_array [] = $row;
			echo "<table>";
			echo "<tr class=\"feldernamen\"><td>BNR</td><td>AUSZUG</td><td>DATUM</td><td>EINHEIT</td><td>BETRAG</td><td>BEMERKUNG</td><td>Optionen</td></tr>\n";
			for($i = 0; $i < count ( $my_array ); $i ++) {
				
				$datum = date_mysql2german ( $my_array [$i] [DATUM] );
				$einheit_info = new mietvertrag ();
				$einheit_id = $einheit_info->get_einheit_id_von_mietvertrag ( $my_array [$i] ['mietvertrag_id'] );
				
				$einheit_info = new einheit ();
				$einheit_info->get_einheit_info ( $einheit_id );
				$einheit_kurzname = $einheit_info->einheit_kurzname;
				$buchungsnummer = $my_array [$i] ['BUCHUNGSNUMMER'];
				$bemerkung = $my_array [$i] [BEMERKUNG];
				$buchungsdatum = $my_array [$i] [DATUM];
				$buchungsmonat_arr = explode ( '-', $buchungsdatum );
				$buchungsmonat = $buchungsmonat_arr [1];
				$buchungsjahr = $buchungsmonat_arr [0];
				$kontoauszugsnr = $my_array [$i] [KONTOAUSZUGSNR];
				/* Prüfen ob diesen Monat gebucht wurde */
				// $this->array_anzeigen($my_array);
				// $anzahl_zahlbetraege_diesen_monat = $this->anzahl_zahlungen_diesen_monat();
				// $anzahl_zahlbetraege_diesen_monat = $this->anzahl_zahlungsvorgaenge($my_array[$i]['mietvertrag_id']);
				$anzahl_zahlbetraege_diesen_monat = $this->anzahl_zahlungsvorgaenge_objekt_konto ( $_SESSION ['objekt_id'] );
				// echo $anzahl_zahlbetraege_diesen_monat;
				if ($anzahl_zahlbetraege_diesen_monat > 0) {
					// echo "Diesen Monat wurde gebucht $this->monat_heute $buchungsmonat $buchungsjahr";
					if ($this->monat_heute > $buchungsmonat) {
						$storno_link = '';
					} else {
						
						$storno_link = "<a href=\"?daten=miete_buchen&schritt=stornieren&bnr=$buchungsnummer\">Stornieren</a>";
					}
				} else {
					$storno_link = "<a href=\"?daten=miete_buchen&schritt=stornieren&bnr=$buchungsnummer\">Stornieren</a>";
				}
				echo "<tr class=\"zeile1\"><td>$buchungsnummer</td><td>$kontoauszugsnr</td><td>$datum</td><td>$einheit_kurzname</td><td>" . $my_array [$i] ['BETRAG'] . " €</td><td>$bemerkung</td><td>$storno_link</td></tr>\n";
			}
			echo "</table>";
		} else {
			echo "Keine Buchungen";
		}
		$this->ende_formular ();
		echo "</div>";
	}
	function letzte_buchungen_zu_mietvertrag($mietvertrag_id) {
		if (isset ( $_SESSION ['buchungsanzahl'] )) {
			$buchungsanzahl = $_SESSION [buchungsanzahl];
			$result = mysql_query ( "SELECT * FROM `GELD_KONTO_BUCHUNGEN` WHERE AKTUELL='1' && KOSTENTRAEGER_ID='$mietvertrag_id' && KOSTENTRAEGER_TYP='Mietvertrag' ORDER BY DATUM DESC LIMIT 0,$buchungsanzahl" );
		} else {
			$result = mysql_query ( "SELECT * FROM `GELD_KONTO_BUCHUNGEN` WHERE AKTUELL='1' && KOSTENTRAEGER_ID='$mietvertrag_id' && KOSTENTRAEGER_TYP='Mietvertrag' ORDER BY GELD_KONTO_BUCHUNGEN_ID DESC" );
		}
		$numrows = mysql_numrows ( $result );
		while ( $row = mysql_fetch_assoc ( $result ) )
			$my_array [] = $row;
		echo "<div class=\"tabelle\">";
		if ($numrows > 0) {
			echo "<p class=\"letzte_buchungen_ueberschrift\">LETZTE BUCHUNGEN</p>";
			echo "<table>";
			// echo "<tr class=\"feldernamen\"><td>BNR</td><td>DATUM</td><td>EINHEIT</td><td>BETRAG</td><td>BEMERKUNG</td></tr>\n";
			echo "<tr class=\"feldernamen\"><td>BNR</td><td>AUSZUG</td><td>DATUM</td><td>EINHEIT</td><td>BETRAG</td><td>BEMERKUNG</td><td>Optionen</td></tr>\n";
			for($i = 0; $i < count ( $my_array ); $i ++) {
				$datum = date_mysql2german ( $my_array [$i] [DATUM] );
				// $datum = date("d"."m"."y", $datum);
				$einheit_info = new mietvertrag ();
				$einheit_id = $einheit_info->get_einheit_id_von_mietvertrag ( $mietvertrag_id );
				$einheit_info = new einheit ();
				$einheit_info->get_einheit_info ( $einheit_id );
				$einheit_kurzname = $einheit_info->einheit_kurzname;
				$buchungsnummer = $my_array [$i] ['BUCHUNGSNUMMER'];
				$bemerkung = $my_array [$i] [BEMERKUNG];
				$buchungsdatum = $my_array [$i] [DATUM];
				$buchungsmonat_arr = explode ( '-', $buchungsdatum );
				$buchungsmonat = $buchungsmonat_arr [1];
				$buchungsjahr = $buchungsmonat_arr [0];
				$kontoauszugsnr = $my_array [$i] [KONTOAUSZUGSNR];
				/* Prüfen ob diesen Monat gebucht wurde */
				// $this->array_anzeigen($my_array);
				// $anzahl_zahlbetraege_diesen_monat = $this->anzahl_zahlungen_diesen_monat();
				$anzahl_zahlbetraege_diesen_monat = $this->anzahl_zahlungsvorgaenge ( $my_array [$i] ['mietvertrag_id'] );
				// echo $anzahl_zahlbetraege_diesen_monat;
				if ($anzahl_zahlbetraege_diesen_monat) {
					// echo "Diesen Monat wurde gebucht $this->monat_heute $buchungsmonat $buchungsjahr";
					if ($this->monat_heute > $buchungsmonat) {
						$storno_link = '';
					} else {
						
						$storno_link = "<a href=\"?daten=miete_buchen&schritt=stornieren&bnr=$buchungsnummer\">Stornieren</a>";
					}
				} else {
					$storno_link = "<a href=\"?daten=miete_buchen&schritt=stornieren&bnr=$buchungsnummer\">Stornieren</a>";
				}
				// echo "<tr class=\"zeile1\"><td>$buchungsnummer</td><td>$datum</td><td>$einheit_kurzname</td><td>".$my_array[$i]['BETRAG']." €</td><td>$bemerkung</td></tr>\n";
				echo "<tr class=\"zeile1\"><td>$buchungsnummer</td><td>$kontoauszugsnr</td><td>$datum</td><td>$einheit_kurzname</td><td>" . $my_array [$i] ['BETRAG'] . " €</td><td>$bemerkung</td><td>$storno_link</td></tr>\n";
			}
			echo "</table>";
		} else {
			echo "Keine Buchungen";
		}
		echo "</div>";
	}
	function nummer_komma2punkt($nummer) {
		$nummer_arr = explode ( ",", $nummer );
		if (! isset ( $nummer_arr [1] )) {
			$nummer = "" . $nummer_arr [0] . ".00";
		} else {
			$nummer = "" . $nummer_arr [0] . "." . $nummer_arr [1] . "";
		}
		return $nummer;
	}
	function nummer_punkt2komma($nummer) {
		// $nummer = number_format($nummer, 2, ",", "");
		$nummer_arr = explode ( ".", $nummer );
		// print_r($nummer_arr);
		if (! isset ( $nummer_arr [1] )) {
			$nummer = "" . $nummer_arr [0] . ",00";
		} else {
			$nummer = "" . $nummer_arr [0] . "," . $nummer_arr [1] . "";
		}
		return $nummer;
	}
	function aufteilung_pruefen() {
		$summe = 0;
		foreach ( $_POST [AUFTEILUNG] as $key => $value ) {
			$value = $this->nummer_komma2punkt ( $value );
			$summe = $summe + $value;
		}
		if ($summe == $_POST ['ZAHLBETRAG']) {
			return true;
		} else {
			return false;
		}
	}
	
	/* Funktion zur Darstellung der Grundinformationen zum Mietvertrag wie z.B. Mieternamen, Saldo, Einheit_kurzname ... */
	function mieter_informationen_anzeigen($mietvertrag_id) {
		$einheit_info = new mietvertrag ();
		$einheit_id = $einheit_info->get_einheit_id_von_mietvertrag ( $mietvertrag_id );
		$einheit_info = new einheit ();
		$einheit_info->get_einheit_info ( $einheit_id );
		$person_info = new person ();
		$mietvertrag_info = new mietvertrag ();
		$personen_ids_arr = $mietvertrag_info->get_personen_ids_mietvertrag ( $mietvertrag_id );
		// print_r($personen_ids_arr);
		echo "<p class=\"hinweis\">Mieter: ";
		
		for($i = 0; $i < count ( $personen_ids_arr ); $i ++) {
			$person_info->get_person_infos ( $personen_ids_arr [$i] ['PERSON_MIETVERTRAG_PERSON_ID'] );
			$vorname = $person_info->person_vorname;
			$nachname = $person_info->person_nachname;
			echo "$nachname $vorname ";
		}
		echo "<a href=\"?daten=mietkonten_blatt&anzeigen=mietkonto_uebersicht_detailiert&mietvertrag_id=$mietvertrag_id\">Mietkontenblatt für $einheit_info->einheit_kurzname</a>";
		echo "</p>";
		$einheit_kurzname = $einheit_info->einheit_kurzname;
		$this->einheit_kurzname_von_mv = $einheit_kurzname;
		$objekt_kurzname = $einheit_info->objekt_name;
		$objekt_id = $einheit_info->objekt_id;
		$_SESSION ['objekt_id'] = $objekt_id;
		$summe_forderung_monatlich = $this->summe_forderung_monatlich ( $mietvertrag_id, $this->monat_heute, $this->jahr_heute );
		// $summe_forderung_monatlich = number_format($summe_forderung_monatlich, 2, ".", "");
		$objekt_info = new objekt ();
		$objekt_info->get_objekt_geldkonto_nr ( $objekt_id );
		$objekt_kontonummer = $objekt_info->objekt_kontonummer;
		
		// $kontostand_gesamt = $this->kontostand_abfragen_gesamt();
		// echo "Summe aller Konten: $kontostand_gesamt €</p>";
		$mietkonto = new mietkonto ();
		$saldo_mietkonto = $mietkonto->mietkontostand_anzeigen ( $mietvertrag_id );
		
		$zeitraum = new zeitraum ();
		$saldo_status = $zeitraum->check_number ( $saldo_mietkonto );
		if ($saldo_status == 1) {
			$saldo_mietkonto = $mietkonto->nummer_punkt2komma ( $saldo_mietkonto );
			echo "<p class=\"negativ\">Saldo Mietkonto: $saldo_mietkonto €</p>";
		} else {
			$saldo_mietkonto = $mietkonto->nummer_punkt2komma ( $saldo_mietkonto );
			echo "<p class=\"positiv\">Saldo Mietkonto: $saldo_mietkonto €</p>";
		}
	}
	
	/* Funktion (KURZFORM) zur Darstellung der Grundinformationen zum Mietvertrag wie z.B. Mieternamen, Saldo, Einheit_kurzname ... */
	function mieter_infos_vom_mv($mietvertrag_id) {
		$einheit_info = new mietvertrag ();
		$einheit_id = $einheit_info->get_einheit_id_von_mietvertrag ( $mietvertrag_id );
		$einheit_info = new einheit ();
		$einheit_info->get_einheit_info ( $einheit_id );
		$person_info = new person ();
		$mietvertrag_info = new mietvertrag ();
		$personen_ids_arr = $mietvertrag_info->get_personen_ids_mietvertrag ( $mietvertrag_id );
		// print_r($personen_ids_arr);
		echo "<p>Objekt: $einheit_info->objekt_name -> <b>Einheit: $einheit_info->einheit_kurzname</b><br>Mieter im Mietvertrag:\n<br> ";
		
		for($i = 0; $i < count ( $personen_ids_arr ); $i ++) {
			$person_info->get_person_infos ( $personen_ids_arr [$i] [PERSON_MIETVERTRAG_PERSON_ID] );
			$vorname = $person_info->person_vorname;
			$nachname = $person_info->person_nachname;
			echo "<b>$nachname $vorname</b>\n<br> ";
		}
		echo "</p>";
	}
	
	/* Funktion mit der ersten Buchungsmaske, d.h. Automatisches Buchen oder manuelle Buchung */
	function buchungsauswahl($mietvertrag_id) {
		$this->letzte_buchungen_zu_mietvertrag ( $mietvertrag_id );
		$mv = new mietvertraege ();
		$mv->get_mietvertrag_infos_aktuell ( $mietvertrag_id );
		if ($mv->mietvertrag_bis != '0000-00-00') {
			echo "<p class=\"warnung\">Auzug zum $mv->mietvertrag_bis_d";
		}
		// $this->array_anzeigen($this);
		$einheit_info = new mietvertrag ();
		$einheit_id = $einheit_info->get_einheit_id_von_mietvertrag ( $mietvertrag_id );
		$summe_forderung_monatlich = $this->summe_forderung_monatlich ( $mietvertrag_id, $this->monat_heute, $this->jahr_heute );
		if ($summe_forderung_monatlich == 0) {
			$summe_forderung_monatlich = $this->summe_forderung_aus_vertrag ( $mietvertrag_id );
		}
		$summe_forderung_monatlich_arr = explode ( '|', $summe_forderung_monatlich );
		$summe_forderung_monatlich = $summe_forderung_monatlich_arr [0];
		$summe_forderung_mwst = $summe_forderung_monatlich_arr [1];
		/* Buchungsmaske für die automatische Buchung */
		$this->erstelle_formular ( "Buchungsvorschlag für die Einheit: <b>" . $einheit_info->einheit_kurzname . " </b>", NULL );
		// dropdown geldkonten
		$geldkonto_info = new geldkonto_info ();
		$geldkonto_info->geld_konten_ermitteln ( 'Mietvertrag', $mietvertrag_id );
		
		// ##########kommentar im infofeld oben
		echo "<div class=\"info_feld_oben\"><b>Buchungsoptionen:<br></b>Buchungsvorschlag 1 für die Einheit: $einheit_info->einheit_kurzname. <br>Das Program errrechet den monatlichen Gesamtbetrag anhand der Mietentwicklung und diesen kann man als solchen buchen. <hr>Sie können aber auch einen anderen Betrag für die Einheit: $einheit_info->einheit_kurzname buchen, in dem Sie den Betrag eingeben und auf <b>Diesen Betrag buchen</b> klicken.</div>";
		
		if (! isset ( $_SESSION ['buchungsdatum'] )) {
			$tag = date ( "d" );
			$monat = date ( "m" );
			$jahr = date ( "Y" );
			$_SESSION [buchungsdatum] = "$tag.$monat.$jahr";
		}
		$this->text_feld ( "Kontoauszugsnr.:", "kontoauszugsnr", "$_SESSION[temp_kontoauszugsnummer]", "10" );
		echo "<br>";
		$this->text_feld ( "Buchungsdatum:", "buchungsdatum", "$_SESSION[buchungsdatum]", "10" );
		echo "<br>";
		$summe_forderung_monatlich1 = $this->nummer_punkt2komma ( $summe_forderung_monatlich );
		$this->text_feld_inaktiv ( "Zahlbetrag (davon MWST:$summe_forderung_mwst)", "zahlbetrag", "$summe_forderung_monatlich1", "6" );
		echo "<br>";
		$this->hidden_feld ( "ZAHLBETRAG", "$summe_forderung_monatlich" );
		$this->hidden_feld ( "MWST_ANTEIL", "$summe_forderung_mwst" );
		$this->hidden_feld ( "MIETVERTRAG_ID", "$mietvertrag_id" );
		// ####aufteilung als array senden
		$forderung_arr = $this->aktuelle_forderungen_array ( $mietvertrag_id );
		if (! is_array ( $forderung_arr )) {
			$forderung_arr = $this->forderung_aus_vertrag ( $mietvertrag_id );
		}
		for($i = 0; $i < count ( $forderung_arr ); $i ++) {
			// $this->text_feld_inaktiv("".$forderung_arr[$i]['KOSTENKATEGORIE']." (€)", "".$forderung_arr[$i]['KOSTENKATEGORIE']."", "".$forderung_arr[$i]['BETRAG']."", "5");
			$this->hidden_feld ( "AUFTEILUNG[" . $forderung_arr [$i] ['KOSTENKATEGORIE'] . "]", "" . $forderung_arr [$i] ['BETRAG'] . "" );
		}
		echo "<p>";
		$this->text_bereich ( "Bemerkung / Hinweis", "bemerkung", "Zahlbetrag", "50", "3" );
		echo "</p>";
		/* Schritt bezeichnet den Aufruf des Cases nach Betätigung des Sendbuttons */
		$this->hidden_feld ( "schritt", "auto_buchung" );
		$this->send_button ( "submit_buchen1", "Akzeptieren und Buchen" );
		$this->ende_formular ();
		/* ENDE - Buchungsmaske für die automatische Buchung */
		
		echo "<br>";
		
		/* Buchungsmaske für andere Beträge */
		$this->erstelle_formular ( "Anderen Betrag für die Einheit: <b>" . $einheit_info->einheit_kurzname . "</b> buchen", NULL );
		// dropdown geldkonten
		$geldkonto_info = new geldkonto_info ();
		$geldkonto_info->geld_konten_ermitteln ( 'Mietvertrag', $mietvertrag_id );
		$this->text_feld ( "Buchungsdatum:", "buchungsdatum", "$_SESSION[buchungsdatum]", "10" );
		echo "<br>";
		$this->hidden_feld ( "MIETVERTRAG_ID", "$mietvertrag_id" );
		$this->text_feld ( "Anderer Zahlbetrag (€):", "ZAHLBETRAG", "$summe_forderung_monatlich1", "6" );
		echo "<br>";
		$this->hidden_feld ( "schritt", "manuelle_buchung" );
		$this->send_button ( "submit_buchen2", "Anderen Betrag buchen" );
		$this->ende_formular ();
		/* ENDE - Buchungsmaske für andere Beträge */
	}
	function buchungsmaske_manuell_gross_betrag($mietvertrag_id, $geld_konto_id) {
		$summe_forderung_monatlich = $this->summe_forderung_monatlich ( $mietvertrag_id, $this->monat_heute, $this->jahr_heute );
		if ($summe_forderung_monatlich == 0) {
			$summe_forderung_monatlich = $this->summe_forderung_aus_vertrag ( $mietvertrag_id );
		}
		/* Datumsformat prüfen, falls i.O wie folgt weiter */
		if (check_datum ( $_POST [buchungsdatum] )) {
			
			$buchungsdatum = date_german2mysql ( $_POST [buchungsdatum] );
			/* Variante 1 - Anfang */
			$this->erstelle_formular ( "Anderen Betrag teilen und buchen ...", NULL );
			/* Ein Array mit aktuellen Forderungen für aktuellen Monat zusammenstellen */
			$forderung_arr = $this->aktuelle_forderungen_array ( $mietvertrag_id );
			if (! is_array ( $forderung_arr )) {
				$forderung_arr = $this->forderung_aus_vertrag ( $mietvertrag_id );
			}
			/* Zahlbetrag aus Komma in Punktformat wandeln */
			$zahlbetrag = $this->nummer_komma2punkt ( $_REQUEST ['ZAHLBETRAG'] );
			echo "<b>Automatische Aufteilung:</b><hr>";
			/* Zahlbetrag aus Punkt in Kommaformat wandeln */
			$zahlbetrag_komma = $this->nummer_punkt2komma ( $zahlbetrag );
			$this->hidden_feld ( "MIETVERTRAG_ID", "$mietvertrag_id" );
			$this->hidden_feld ( "buchungsdatum", "$_POST[buchungsdatum]" );
			$this->text_feld ( "Kontoauszugsnr.:", "kontoauszugsnr", "$_SESSION[temp_kontoauszugsnummer]", "10" );
			echo "<br>";
			$this->text_feld_inaktiv ( "Zahlbetrag (€):", "ZAHLBETRAG", "$zahlbetrag_komma", "5" );
			$this->hidden_feld ( "ZAHLBETRAG", "$zahlbetrag" );
			echo "<table>";
			for($i = 0; $i < count ( $forderung_arr ); $i ++) {
				// $this->text_feld_inaktiv("".$forderung_arr[$i]['KOSTENKATEGORIE']." (€)", "".$forderung_arr[$i]['KOSTENKATEGORIE']."", "".$forderung_arr[$i]['BETRAG']."", "5");
				$forderung_arr [$i] ['BETRAG'] = $this->nummer_punkt2komma ( $forderung_arr [$i] ['BETRAG'] );
				echo "<tr><td><b>" . $forderung_arr [$i] ['KOSTENKATEGORIE'] . "</b></td><td align=\"right\"><b> " . $forderung_arr [$i] ['BETRAG'] . " €</b></td></tr>";
				/* Zahlbetrag aus Komma in Punkt format wandeln $betrag_4_db */
				$betrag_4_db = $this->nummer_komma2punkt ( $forderung_arr [$i] ['BETRAG'] );
				/* Versteckten Array namens AUFTEILUNG ins Formular hinzufügen, wichtig für die interne Verbuchung nach Kostenkategorie */
				$this->hidden_feld ( "AUFTEILUNG[" . $forderung_arr [$i] ['KOSTENKATEGORIE'] . "]", "$betrag_4_db" );
			}
			echo "</table>";
			$ueberschuss = $zahlbetrag - $summe_forderung_monatlich;
			echo "<hr>";
			$this->text_bereich ( "Bemerkung / Hinweis", "bemerkung", "Zahlbetrag", "10", "3" );
			/* Auswahl der Kostenkategorie für den Überschuß bzw. Restbetrag */
			warnung_ausgeben ( "Wählen Sie bitte eine Kostenkategorie für die Überschußbuchung aus!" );
			$ueberschuss = number_format ( $ueberschuss, 2, ".", "" );
			$ueberschuss = $this->nummer_punkt2komma ( $ueberschuss );
			$this->text_feld_inaktiv ( "Überschuss (€):", "ueberschuss", "$ueberschuss", "5" );
			$ueberschuss = $this->nummer_komma2punkt ( $ueberschuss );
			$this->hidden_feld ( "ueberschuss", "$ueberschuss" );
			$this->hidden_feld ( "geld_konto", "$geld_konto_id" );
			$this->dropdown_kostenkategorien ( 'Kostenkategorie auswählen', 'kostenkategorie' );
			
			/* Kommentar im info_feld_oben */
			echo "<div class=\"info_feld_oben\"><b>Buchungsoptionen:<br></b>Der von Ihnen eingegebene Betrag ist größer oder gleich als die monatliche Gesamtforderung. Treffen Sie bitte die Auswahl, wie der Betrag zu buchen ist.</div>";
			echo "<br>";
			/* Ende Kommentar */
			/* Schritt bezeichnet den Aufruf des Cases nach Betätigung des Sendbuttons */
			$this->hidden_feld ( "schritt", "manuelle_buchung3" );
			$this->send_button ( "submit_buchen3", "Akzeptieren und buchen" );
			$this->ende_formular ();
			/* Ende Formular */
			echo "<br>";
			/* Hier endet die vorgeschlagene Buchungsart bzw. Variante 1 */
			
			/* Variante 2 - Anfang */
			/* Buchungsformular für die manuelle Eingabe der Beträge */
			$this->erstelle_formular ( "Betrag manuell teilen / buchen ...", NULL );
			echo "<b>Manuelle Teilung / Buchung</b><hr>";
			warnung_ausgeben ( "Tragen Sie bitte einzelne Beträge ein!" );
			$this->text_feld ( "Kontoauszugsnr.:", "kontoauszugsnr", "$_SESSION[temp_kontoauszugsnummer]", "10" );
			echo "<br>";
			$this->text_feld_inaktiv ( "Zahlbetrag (€):", "ZAHLBETRAG", "$zahlbetrag_komma", "5" );
			$this->hidden_feld ( "MIETVERTRAG_ID", "$mietvertrag_id" );
			$this->hidden_feld ( "buchungsdatum", "$_POST[buchungsdatum]" );
			$this->hidden_feld ( "ZAHLBETRAG", "$zahlbetrag" );
			echo "<br>";
			for($i = 0; $i < count ( $forderung_arr ); $i ++) {
				$this->text_feld ( "" . $forderung_arr [$i] ['KOSTENKATEGORIE'] . " (" . $forderung_arr [$i] ['BETRAG'] . " €)", "AUFTEILUNG[" . $forderung_arr [$i] ['KOSTENKATEGORIE'] . "]", "", "5" );
				echo "<br>";
			}
			echo "<p>";
			$this->text_bereich ( "Bemerkung / Hinweis", "bemerkung", "Zahlbetrag", "50", "3" );
			echo "</p>";
			/* Schritt bezeichnet den Aufruf des Cases nach Betätigung des Sendbuttons */
			$this->hidden_feld ( "schritt", "manuelle_buchung4" );
			$this->hidden_feld ( "geld_konto", $geld_konto_id );
			$this->send_button ( "submit_buchen4", "Manuel buchen" );
			$this->ende_formular ();
			/* ENDE -Buchungsformular für die manuelle Eingabe der Beträge */
		} else {
			/* Falls das Datumsformat nicht i.O,. um einen Schritt zurücksetzen */
			warnung_ausgeben ( "Datumsformat nicht korrekt!" );
			warnung_ausgeben ( "Sie werden um einen Schritt zurückversetzt!" );
			weiterleiten_in_sec ( 'javascript:history.back();', 5 );
		}
	}
	function buchungsmaske_manuell_kleiner_betrag($mietvertrag_id, $geld_konto_id) {
		// echo "kleiner";
		$summe_forderung_monatlich = $this->summe_forderung_monatlich ( $mietvertrag_id, $this->monat_heute, $this->jahr_heute );
		if ($summe_forderung_monatlich == 0) {
			$summe_forderung_monatlich = $this->summe_forderung_aus_vertrag ( $mietvertrag_id );
		}
		// $this->array_anzeigen($_POST);
		// ######anfang
		if (check_datum ( $_POST [buchungsdatum] )) {
			$buchungsdatum = date_german2mysql ( $_POST [buchungsdatum] );
			$forderung_arr = $this->aktuelle_forderungen_array ( $mietvertrag_id );
			if (! is_array ( $forderung_arr )) {
				$forderung_arr = $this->forderung_aus_vertrag ( $mietvertrag_id );
			}
			$summe_vorschuesse = $this->summe_vorschuesse ( $forderung_arr );
			/* Keys sind fest definiert in suche_nach_vorschuessen als BETRIEBSKOSTEN HEIZKOSTEN */
			$vorschuesse_keys = $this->suche_nach_vorschuessen ( $forderung_arr );
			$summe_forderung_monatlich = number_format ( $summe_forderung_monatlich, 2, ".", "" );
			$summe_vorschuesse = number_format ( $summe_vorschuesse, 2, ".", "" );
			$zahlbetrag = $this->nummer_komma2punkt ( $_POST ['ZAHLBETRAG'] );
			$zahlbetrag_komma = $this->nummer_punkt2komma ( $zahlbetrag );
			// echo "ZB: $zahlbetrag F:$summe_forderung_monatlich SV:$summe_vorschuesse";
			
			/* Prüfen ob der Zahlbetrag die Vorschüsse decken kann */
			if ($zahlbetrag >= $summe_vorschuesse) {
				$rest = $zahlbetrag - $summe_vorschuesse;
				$rest_komma = number_format ( $rest, 2, ",", "" );
				
				/* Formularanfang falls Zahlbetrag > Vorschüsse */
				$this->erstelle_formular ( "Kleineren Betrag teilen und buchen -> Berlussimo schlägt vor...", NULL );
				echo "<b>Der Zahlbetrag reicht für die Vorschüsse!</b> <br>\n Nach dem Buchen der Vorschüsse bleiben <b>$rest_komma €</b> für weitere Buchungen.";
				$this->text_feld ( "Kontoauszugsnr.:", "kontoauszugsnr", "$_SESSION[temp_kontoauszugsnummer]", "10" );
				$this->hidden_feld ( "geld_konto", "$geld_konto_id" );
				$this->hidden_feld ( "buchungsdatum", "$_POST[buchungsdatum]" );
				$this->hidden_feld ( "ZAHLBETRAG", "$zahlbetrag" );
				$this->hidden_feld ( "MIETVERTRAG_ID", "$mietvertrag_id" );
				for($i = 0; $i < count ( $vorschuesse_keys ); $i ++) {
					
					/* Versteckten Array namens AUFTEILUNG ins Formular hinzufügen, wichtig für die interne Verbuchung nach Kostenkategorie */
					$this->hidden_feld ( "AUFTEILUNG[" . $forderung_arr [$vorschuesse_keys [$i]] ['KOSTENKATEGORIE'] . "]", "" . $forderung_arr [$vorschuesse_keys [$i]] ['BETRAG'] . "" );
					/* Anzeige der inaktiven Felder der KOSTENKATEGORIEN VORSCHÜSSE */
					$betrag_ausgabe = $this->nummer_punkt2komma ( $forderung_arr [$vorschuesse_keys [$i]] ['BETRAG'] );
					$this->text_feld_inaktiv ( "" . $forderung_arr [$vorschuesse_keys [$i]] ['KOSTENKATEGORIE'] . "", "" . $forderung_arr [$vorschuesse_keys [$i]] ['KOSTENKATEGORIE'] . "", "$betrag_ausgabe", "5" );
					echo "<br>";
				}
				
				if ($rest > 0) {
					$rest_komma = number_format ( $rest, 2, ",", "" );
					$this->text_feld_inaktiv ( "KALTMIETE €", "KALTMIETE", "$rest_komma", "5" );
					$mk_bez = ' Miete kalt';
					$this->hidden_feld ( "AUFTEILUNG[$mk_bez]", "$rest" );
				}
				echo "<br>";
				echo "<p>";
				$this->text_bereich ( "Bemerkung / Hinweis", "bemerkung", "Zahlbetrag", "50", "3" );
				echo "</p>";
				/* Schritt bezeichnet den Aufruf des Cases nach Betätigung des Sendbuttons */
				$this->hidden_feld ( "schritt", "manuelle_buchung3" );
				$this->hidden_feld ( "geld_konto", $geld_konto_id );
				$this->send_button ( "submit_buchen5", "Akzeptieren und buchen" );
				$this->ende_formular ();
				echo "<hr>";
				// #############################
				
				/* Variante 2 - Anfang */
				/* Buchungsformular für die manuelle Eingabe der Beträge */
				$this->erstelle_formular ( "Kleineren Betrag manuell teilen / buchen ...", NULL );
				echo "<b>Manuelle Teilung / Buchung</b><hr>";
				warnung_ausgeben ( "Tragen Sie bitte einzelne Beträge ein!" );
				$this->text_feld ( "Kontoauszugsnr.:", "kontoauszugsnr", "$_SESSION[temp_kontoauszugsnummer]", "10" );
				$this->text_feld_inaktiv ( "Zahlbetrag (€):", "ZAHLBETRAG", "$zahlbetrag_komma", "5" );
				$this->hidden_feld ( "MIETVERTRAG_ID", "$mietvertrag_id" );
				$this->hidden_feld ( "buchungsdatum", "$_POST[buchungsdatum]" );
				$this->hidden_feld ( "ZAHLBETRAG", "$zahlbetrag" );
				echo "<br>";
				for($i = 0; $i < count ( $forderung_arr ); $i ++) {
					$this->text_feld ( "" . $forderung_arr [$i] ['KOSTENKATEGORIE'] . " ( " . $forderung_arr [$i] ['BETRAG'] . " €)", "AUFTEILUNG[" . $forderung_arr [$i] ['KOSTENKATEGORIE'] . "]", "", "5" );
					echo "<br>";
				}
				echo "<p>";
				$this->text_bereich ( "Bemerkung / Hinweis", "bemerkung", "Zahlbetrag", "50", "3" );
				echo "</p>";
				/* Schritt bezeichnet den Aufruf des Cases nach Betätigung des Sendbuttons */
				$this->hidden_feld ( "schritt", "manuelle_buchung4" );
				$this->hidden_feld ( "geld_konto", $geld_konto_id );
				$this->send_button ( "submit_buchen4", "Manuel buchen" );
				$this->ende_formular ();
				/* ENDE -Buchungsformular für die manuelle Eingabe der Beträge */
			}
			// ################### ab hier FALL 2 #############################
			/* Falls Zahlbetrag kleiner als Vorschüsse */
			if ($zahlbetrag < $summe_vorschuesse) {
				echo "Der Zahlbetrag reicht nicht für die Vorschüsse! Prozentuale aufteilung.";
				
				/* Formularanfang wenn Zahlbetrag kleiner als Vorschüsse */
				$this->erstelle_formular ( "Kleineren Betrag prozentual teilen / buchen", NULL );
				$this->hidden_feld ( "ZAHLBETRAG", "$zahlbetrag" );
				$this->hidden_feld ( "MIETVERTRAG_ID", "$mietvertrag_id" );
				$buchungsdatum = date_german2mysql ( $_POST [buchungsdatum] );
				$this->hidden_feld ( "buchungsdatum", "$_POST[buchungsdatum]" );
				echo "Buchungsdatum: $_POST[buchungsdatum]<br>";
				$rest = $zahlbetrag - $summe_vorschuesse;
				$prozentsatz = $zahlbetrag / ($summe_vorschuesse / 100);
				$prozentsatz_gerundet = number_format ( $prozentsatz, 0, ",", "" );
				echo "Der Zahlbetrag reicht <b>nicht</b> für die Vorschüsse! Es fehlen $rest €! Eine Deckung der Vorschüsse mit maximal $prozentsatz_gerundet % möglich<br>";
				echo "<hr><b>Buchungsvorschlag wenn Betrag kleiner als Nebenkosten:</b><br><br>";
				$this->text_feld ( "Kontoauszugsnr.:", "kontoauszugsnr", "$_SESSION[temp_kontoauszugsnummer]", "10" );
				$this->text_feld_inaktiv ( "Zahlbetrag:", "ZAHLBETRAG", "" . $_REQUEST ['ZAHLBETRAG'] . " €", "5" );
				echo "<br>";
				// echo "<b>SUMME $summe_vorschuesse = rest $rest</b>";
				$vorschuesse_keys = $this->suche_nach_vorschuessen ( $forderung_arr );
				// $this->array_anzeigen($vorschuesse_keys);
				$rundungsfehler = 0;
				for($i = 0; $i < count ( $vorschuesse_keys ); $i ++) {
					$prozentsatz = $zahlbetrag / ($summe_vorschuesse / 100);
					$prozentualer_anteil = (($forderung_arr [$vorschuesse_keys [$i]] ['BETRAG'] / 100) * $prozentsatz);
					
					// echo " PRO VOR rundung OHNE FEHLER: $prozentualer_anteil €";
					
					$prozentualer_anteil = (($forderung_arr [$vorschuesse_keys [$i]] ['BETRAG'] / 100) * $prozentsatz) + $rundungsfehler;
					// echo " PRO VOR rundung INKL FEHLER: $prozentualer_anteil €";
					
					$prozentualer_anteil = round ( $prozentualer_anteil, 2 );
					$rundungsfehler = (($forderung_arr [$vorschuesse_keys [$i]] ['BETRAG'] / 100) * $prozentsatz) - $prozentualer_anteil;
					// echo " PRO nach rundung: $prozentualer_anteil €";
					$prozentualer_anteil_gerundet = number_format ( $prozentualer_anteil, 2, ",", "" );
					$this->text_feld_inaktiv ( "" . $forderung_arr [$vorschuesse_keys [$i]] ['KOSTENKATEGORIE'] . "", "" . $forderung_arr [$vorschuesse_keys [$i]] ['KOSTENKATEGORIE'] . "", "" . $prozentualer_anteil_gerundet . " €", "15" );
					
					/* Versteckten Array namens AUFTEILUNG ins Formular hinzufügen, wichtig für die interne Verbuchung nach Kostenkategorie */
					
					$this->hidden_feld ( "AUFTEILUNG[" . $forderung_arr [$vorschuesse_keys [$i]] ['KOSTENKATEGORIE'] . "]", "" . $prozentualer_anteil_gerundet . "" );
					echo "<br>";
				} // ende for
				echo "<p>";
				$this->text_bereich ( "Bemerkung / Hinweis", "bemerkung", "Zahlbetrag", "50", "3" );
				echo "</p>";
				$this->hidden_feld ( "schritt", "manuelle_buchung3" );
				$this->hidden_feld ( "geld_konto", "$geld_konto_id" );
				$this->send_button ( "submit_buchen6", "Manuel buchen" );
				$this->ende_formular ();
				
				echo "<hr>";
				/* Ende Buchungsvorschlag */
				/* Anfang Manuelle Aufteilung */
				/* Buchungsformular für die manuelle Eingabe der Beträge */
				$this->erstelle_formular ( "Betrag manuell teilen / buchen ...", NULL );
				echo "<b>Manuelle Teilung / Buchung</b><hr>";
				warnung_ausgeben ( "Tragen Sie bitte einzelne Beträge ein!" );
				$this->text_feld ( "Kontoauszugsnr.:", "kontoauszugsnr", "$_SESSION[temp_kontoauszugsnummer]", "10" );
				$this->text_feld_inaktiv ( "Zahlbetrag (€):", "ZAHLBETRAG", "$zahlbetrag_komma", "5" );
				$this->hidden_feld ( "MIETVERTRAG_ID", "$mietvertrag_id" );
				$this->hidden_feld ( "buchungsdatum", "$_POST[buchungsdatum]" );
				$this->hidden_feld ( "ZAHLBETRAG", "$zahlbetrag" );
				echo "<br>";
				for($i = 0; $i < count ( $forderung_arr ); $i ++) {
					$this->text_feld ( "" . $forderung_arr [$i] ['KOSTENKATEGORIE'] . " (€)", "AUFTEILUNG[" . $forderung_arr [$i] ['KOSTENKATEGORIE'] . "]", "", "5" );
					echo "<br>";
				}
				echo "<p>";
				$this->text_bereich ( "Bemerkung / Hinweis", "bemerkung", "Zahlbetrag", "50", "3" );
				echo "</p>";
				/* Schritt bezeichnet den Aufruf des Cases nach Betätigung des Sendbuttons */
				$this->hidden_feld ( "schritt", "manuelle_buchung4" );
				$this->hidden_feld ( "geld_konto", "$geld_konto_id" );
				$this->send_button ( "submit_buchen4", "Manuel buchen" );
				$this->ende_formular ();
				/* ENDE -Buchungsformular für die manuelle Eingabe der Beträge */
				/* Ende manuelle Aufteilung */
				// ################## ende FALL 2 #################################
			}
		} else {
			/* Falls das Datumsformat nicht i.O,. um einen Schritt zurücksetzen */
			warnung_ausgeben ( "Datumsformat nicht korrekt!" );
			warnung_ausgeben ( "Sie werden um einen Schritt zurückversetzt!" );
			weiterleiten_in_sec ( 'javascript:history.back();', 5 );
		}
	}
	function buchungsmaske_manuell_gleicher_betrag($mietvertrag_id, $geld_konto_id) {
		$summe_forderung_monatlich = $this->summe_forderung_monatlich ( $mietvertrag_id, $this->monat_heute, $this->jahr_heute );
		if ($summe_forderung_monatlich == 0) {
			$summe_forderung_monatlich = $this->summe_forderung_aus_vertrag ( $mietvertrag_id );
		}
		/* Datumsformat prüfen, falls i.O wie folgt weiter */
		if (check_datum ( $_POST [buchungsdatum] )) {
			
			$buchungsdatum = date_german2mysql ( $_POST [buchungsdatum] );
			/* Ein Array mit aktuellen Forderungen für aktuellen Monat zusammenstellen */
			$forderung_arr = $this->aktuelle_forderungen_array ( $mietvertrag_id );
			if (! is_array ( $forderung_arr )) {
				$forderung_arr = $this->forderung_aus_vertrag ( $mietvertrag_id );
			}
			/* Zahlbetrag aus Komma in Punktformat wandeln */
			$zahlbetrag = $this->nummer_komma2punkt ( $_REQUEST ['ZAHLBETRAG'] );
			/* Zahlbetrag aus Punkt in Kommaformat wandeln */
			$zahlbetrag_komma = $this->nummer_punkt2komma ( $zahlbetrag );
			
			/* Buchungsformular für die manuelle Eingabe der Beträge */
			$this->erstelle_formular ( "Betrag manuell teilen / buchen ...", NULL );
			echo "<b>Manuelle Teilung / Buchung</b><hr>";
			warnung_ausgeben ( "Tragen Sie bitte einzelne Beträge ein!" );
			$this->text_feld ( "Kontoauszugsnr.:", "kontoauszugsnr", "$_SESSION[temp_kontoauszugsnummer]", "10" );
			echo "<br>";
			$this->text_feld_inaktiv ( "Zahlbetrag (€):", "ZAHLBETRAG", "$zahlbetrag_komma", "5" );
			$this->hidden_feld ( "MIETVERTRAG_ID", "$mietvertrag_id" );
			$this->hidden_feld ( "buchungsdatum", "$_POST[buchungsdatum]" );
			$this->hidden_feld ( "ZAHLBETRAG", "$zahlbetrag" );
			echo "<br>";
			for($i = 0; $i < count ( $forderung_arr ); $i ++) {
				$f_betrag = nummer_punkt2komma ( $forderung_arr [$i] ['BETRAG'] );
				$this->text_feld ( "" . $forderung_arr [$i] ['KOSTENKATEGORIE'] . " ( " . $forderung_arr [$i] ['BETRAG'] . " €)", "AUFTEILUNG[" . $forderung_arr [$i] ['KOSTENKATEGORIE'] . "]", "$f_betrag", "5" );
				echo "<br>";
			}
			echo "<p>";
			$this->text_bereich ( "Bemerkung / Hinweis", "bemerkung", "Zahlbetrag", "50", "3" );
			echo "</p>";
			/* Schritt bezeichnet den Aufruf des Cases nach Betätigung des Sendbuttons */
			$this->hidden_feld ( "geld_konto", "$geld_konto_id" );
			$this->hidden_feld ( "schritt", "manuelle_buchung4" );
			$this->send_button ( "submit_buchen4", "Manuel buchen" );
			$this->ende_formular ();
			/* ENDE -Buchungsformular für die manuelle Eingabe der Beträge */
		} else {
			/* Falls das Datumsformat nicht i.O,. um einen Schritt zurücksetzen */
			warnung_ausgeben ( "Datumsformat nicht korrekt!" );
			warnung_ausgeben ( "Sie werden um einen Schritt zurückversetzt!" );
			weiterleiten_in_sec ( 'javascript:history.back();', 5 );
		}
	}
	function buchungsmaske_manuell_negativ_betrag($mietvertrag_id, $geld_konto_id) {
		$summe_forderung_monatlich = $this->summe_forderung_monatlich ( $mietvertrag_id, $this->monat_heute, $this->jahr_heute );
		if ($summe_forderung_monatlich == 0) {
			$summe_forderung_monatlich = $this->summe_forderung_aus_vertrag ( $mietvertrag_id );
		}
		/* Datumsformat prüfen, falls i.O wie folgt weiter */
		if (check_datum ( $_POST [buchungsdatum] )) {
			
			$buchungsdatum = date_german2mysql ( $_POST [buchungsdatum] );
			/* Ein Array mit aktuellen Forderungen für aktuellen Monat zusammenstellen */
			$forderung_arr = $this->aktuelle_forderungen_array ( $mietvertrag_id );
			if (! is_array ( $forderung_arr )) {
				$forderung_arr = $this->forderung_aus_vertrag ( $mietvertrag_id );
			}
			/* Zahlbetrag aus Komma in Punktformat wandeln */
			$zahlbetrag = $this->nummer_komma2punkt ( $_REQUEST ['ZAHLBETRAG'] );
			/* Zahlbetrag aus Punkt in Kommaformat wandeln */
			$zahlbetrag_komma = $this->nummer_punkt2komma ( $zahlbetrag );
			
			/* Buchungsformular für die manuelle Eingabe der NEGATIVEN Beträge */
			$this->erstelle_formular ( "Negativen Betrag manuell teilen / buchen ...", NULL );
			echo "<b>Manuelle Teilung / Buchung</b><hr>";
			// warnung_ausgeben("Tragen Sie bitte einzelne Beträge ein!");
			$this->text_feld ( "Kontoauszugsnr.:", "kontoauszugsnr", "$_SESSION[temp_kontoauszugsnummer]", "10" );
			echo "<br>";
			$this->text_feld_inaktiv ( "Zahlbetrag (€):", "ZAHLBETRAG", "$zahlbetrag_komma", "10" );
			$this->hidden_feld ( "MIETVERTRAG_ID", "$mietvertrag_id" );
			$this->hidden_feld ( "buchungsdatum", "$_POST[buchungsdatum]" );
			$this->hidden_feld ( "ZAHLBETRAG", "$zahlbetrag" );
			echo "<br>";
			/*
			 * for($i=0;$i<count($forderung_arr);$i++){
			 * $this->text_feld("".$forderung_arr[$i]['KOSTENKATEGORIE']." (- ".$forderung_arr[$i]['BETRAG']." €)", "AUFTEILUNG[".$forderung_arr[$i]['KOSTENKATEGORIE']."]", "", "5");
			 * echo "<br>";
			 * }
			 */
			echo "<p>";
			$this->text_bereich ( "Bemerkung / Hinweis", "bemerkung", "Zahlbetrag", "50", "3" );
			echo "</p>";
			/* Schritt bezeichnet den Aufruf des Cases nach Betätigung des Sendbuttons */
			$this->hidden_feld ( "geld_konto", "$geld_konto_id" );
			$this->hidden_feld ( "schritt", "manuelle_buchung4" );
			$this->send_button ( "submit_buchen4", "Manuel buchen" );
			$this->ende_formular ();
			/* ENDE -Buchungsformular für die manuelle Eingabe der Beträge */
		} else {
			/* Falls das Datumsformat nicht i.O,. um einen Schritt zurücksetzen */
			warnung_ausgeben ( "Datumsformat nicht korrekt!" );
			warnung_ausgeben ( "Sie werden um einen Schritt zurückversetzt!" );
			weiterleiten_in_sec ( 'javascript:history.back();', 5 );
		}
	}
	function miete_zahlbetrag_buchen($kontoauszugsnr, $mietvertrag_id, $buchungsdatum, $betrag, $bemerkung, $geld_konto_id, $mwst_anteil = '0.00') {
		/* Datum und Kontoauszug in Session übernehmen */
		$sess_datum = $this->date_mysql2german ( $buchungsdatum );
		$_SESSION [buchungsdatum] = $sess_datum;
		$_SESSION [temp_kontoauszugsnummer] = $kontoauszugsnr;
		/* Buchen und protokollieren */
		
		$this->insert_geldbuchung ( $geld_konto_id, '80001', $kontoauszugsnr, 'MIETE', $bemerkung, $buchungsdatum, 'Mietvertrag', $mietvertrag_id, $betrag, $mwst_anteil );
		
		/* Interne Buchung */
		// $buchungsnummer = $last_dat;
		// $this->intern_buchen($mietvertrag_id, $buchungsnummer);
		
		/* Ausgabe am Bildschirm */
		$betrag = $this->nummer_punkt2komma ( $betrag );
		echo "<p><b>Zahlbetrag $betrag € wurde auf das Konto $geld_konto_id gebucht.<br></b></p>";
		
		weiterleiten_in_sec ( '?daten=miete_buchen', 2 );
	}
	function check_zahlbetrag($kontoauszugsnr, $kostentraeger_typ, $kostentraeger_id, $buchungsdatum, $betrag, $v_zweck, $geld_konto_id, $kontenrahmen_konto) {
		$result = mysql_query ( "SELECT * FROM GELD_KONTO_BUCHUNGEN WHERE KONTO_AUSZUGSNUMMER = '$kontoauszugsnr' && KOSTENTRAEGER_TYP='$kostentraeger_typ' && KOSTENTRAEGER_ID='$kostentraeger_id' && DATUM= '$buchungsdatum' && BETRAG= '$betrag' && VERWENDUNGSZWECK= '$v_zweck' && AKTUELL= '1' && GELDKONTO_ID= '$geld_konto_id' && KONTENRAHMEN_KONTO='$kontenrahmen_konto'" );
		
		$numrows = mysql_numrows ( $result );
		if ($numrows < 1) {
			return false;
		} else {
			return true;
		}
	}
	// $this->insert_geldbuchung($geld_konto_id, '80001', $kontoauszugsnr, 'MIETE', $bemerkung, $buchungsdatum, 'Mietvertrag', $mietvertrag_id, $betrag);
	function insert_geldbuchung($geldkonto_id, $buchungskonto, $auszugsnr, $rechnungsnr, $v_zweck, $datum, $kostentraeger_typ, $kostentraeger_id, $betrag, $mwst_anteil = '0.00') {
		$last_id = last_id ( 'GELD_KONTO_BUCHUNGEN' );
		$last_id = $last_id + 1;
		
		/* neu */
		$datum_arr = explode ( '-', $datum );
		$jahr = $datum_arr ['0'];
		$b = new buchen ();
		$g_buchungsnummer = $b->get_last_buchungsnummer_konto ( $geldkonto_id, $jahr );
		$g_buchungsnummer = $g_buchungsnummer + 1;
		
		mysql_query ( "INSERT INTO GELD_KONTO_BUCHUNGEN VALUES(NULL, '$last_id', '$g_buchungsnummer', '$auszugsnr', '$rechnungsnr', '$betrag', '$mwst_anteil', '$v_zweck', '$geldkonto_id', '$buchungskonto', '$datum', '$kostentraeger_typ', '$kostentraeger_id', '1')" );
		/* Protokollieren */
		$last_dat = mysql_insert_id ();
		protokollieren ( 'GELD_KONTO_BUCHUNGEN', $last_dat, '0' );
	}
	function import_miete_zahlbetrag_buchen($kontoauszugsnr, $kostentraeger_typ, $kostentraeger_id, $buchungsdatum, $betrag, $bemerkung, $geldkonto_id, $buchungskonto) {
		// echo "<b>$betrag</b>";
		$buchungsdatum = date_german2mysql ( $buchungsdatum );
		$zahlbetrag_exists = $this->check_zahlbetrag ( $kontoauszugsnr, $kostentraeger_typ, $kostentraeger_id, $buchungsdatum, $betrag, $bemerkung, $geldkonto_id, $buchungskonto );
		// if(!$zahlbetrag_exists){
		// echo "<h1>NICHT EXIST</h1>";
		/* Buchen */
		$this->insert_geldbuchung ( $geldkonto_id, $buchungskonto, $kontoauszugsnr, $kontoauszugsnr, $bemerkung, $buchungsdatum, $kostentraeger_typ, $kostentraeger_id, $betrag );
		
		/* Interne Buchung */
		// $this->import_intern_buchen($buchungsdatum, $mietvertrag_id, $buchungsnummer, $betrag, $bemerkung);
		
		/* Ausgabe am Bildschirm */
		// $betrag = $this->nummer_punkt2komma($betrag);
		// echo "<b>Zahlbetrag $betrag € wurde auf das Konto $geld_konto_id gebucht.<br></b>";
		// }else{
		// echo "$mietvertrag_id $betrag Zahlung existiert<br>";
		// }
	}
	
	/* Letzte Buchungsnummer vom gebuchten Zahlbetrag zu Mietvertrag finden */
	function letzte_buchungsnummer($mietvertrag_id) {
		$result = mysql_query ( "SELECT BUCHUNGSNUMMER FROM MIETE_ZAHLBETRAG WHERE MIETVERTRAG_ID = '$mietvertrag_id' && AKTUELL = '1' ORDER BY BUCHUNGSNUMMER DESC LIMIT 0,1" );
		$row = mysql_fetch_assoc ( $result );
		return $row ['BUCHUNGSNUMMER'];
	}
	function intern_buchen($mietvertrag_id, $buchungsnummer) {
		foreach ( $_POST [AUFTEILUNG] as $key => $value ) {
			// echo "KOSTENKAT ".$key." ".$value." €<br>";
			
			if (! empty ( $value )) {
				$value = $this->nummer_komma2punkt ( $value );
				$value = number_format ( $value, 2, ".", "" );
				$db_abfrage = "INSERT INTO MIETBUCHUNGEN VALUES (NULL, '$mietvertrag_id', '$this->datum_heute', '$value', '$buchungsnummer', '$key', '1')";
				$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
				/* Zugewiesene MIETBUCHUNG_DAT auslesen */
				$last_dat = mysql_insert_id ();
				protokollieren ( 'MIETBUCHUNGEN', $last_dat, '0' );
				$wert = $this->nummer_punkt2komma ( $value );
				echo "Teilbetrag $wert € für $key wurde intern gebucht<br>";
			}
		}
		if (isset ( $_POST ['KOSTENKATEGORIE'] )) {
			$ueberschuss = $this->nummer_komma2punkt ( $_POST [ueberschuss] );
			$ueberschuss = number_format ( $ueberschuss, 2, ".", "" );
			$db_abfrage = "INSERT INTO MIETBUCHUNGEN VALUES (NULL, '$mietvertrag_id', '$this->datum_heute', '$ueberschuss', '$buchungsnummer', '$_POST[KOSTENKATEGORIE]', '1')";
			$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		}
	}
	function import_intern_buchen($buchungsdatum, $mietvertrag_id, $buchungsnummer, $betrag, $bemerkung) {
		$datum = explode ( "-", $buchungsdatum );
		$monat = $datum [1];
		$jahr = $datum [0];
		/* Wenn Saldovv dann auf Kaltmiete */
		// #####################################IF 1
		if (preg_match ( "/Saldo Vortrag Vorverwaltung/", $bemerkung )) {
			$db_abfrage = "INSERT INTO MIETBUCHUNGEN VALUES (NULL, '$mietvertrag_id', '$buchungsdatum', '$betrag', '$buchungsnummer', 'Miete kalt - VV', '1')";
			$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
			/* Zugewiesene MIETBUCHUNG_DAT auslesen */
			$last_dat = mysql_insert_id ();
			protokollieren ( 'MIETBUCHUNGEN', $last_dat, '0' );
			echo "$mietvertrag_id - SALDO AUF KM GEBUCHT<br>";
		} 		// #####################################END IF 1
		/* Sonstige Beträge die keine Saldovortrag sind */
		// #######################################IF 2 ELSE
		else {
			/* Ende Saldo VV */
			/* liefert true oder false */
			/* $bkhk_faellig = $this->betriebskosten_heizkosten_fallig($mietvertrag_id, $monat, $jahr); */
			$hk_info = $this->hk_abrechnung_arr ( $mietvertrag_id, $jahr );
			$hk_datum = $hk_info [ENDE];
			$hk_datum_arr = explode ( "-", $hk_datum );
			$hk_monat = $hk_datum_arr [1];
			$hk_betrag = $hk_info ['BETRAG'];
			$hk_bezeichnung = $hk_info ['KOSTENKATEGORIE'];
			// print_r($hk_info);
			$bk_info = $this->bk_abrechnung_arr ( $mietvertrag_id, $jahr );
			$bk_datum = $bk_info [ENDE];
			$bk_datum_arr = explode ( "-", $bk_datum );
			$bk_monat = $bk_datum_arr [1];
			$bk_betrag = $bk_info ['BETRAG'];
			$bk_bezeichnung = $bk_info ['KOSTENKATEGORIE'];
			$hk_und_bk = $hk_betrag + $bk_betrag;
			if ($hk_und_bk > '0.00') {
				$hk_und_bk = $hk_betrag + $bk_betrag;
			}
			if ($hk_und_bk < '0.00') {
				$hk_und_bk = '0.00';
			}
			
			$gebucht = false;
			
			/* Wenn Positivbetrag */
			$forderung_monatlich = $this->summe_forderung_monatlich ( $mietvertrag_id, $monat, $jahr );
			echo "<h1>BETRAG $betrag BK $bk_bezeichnung:$bk_betrag HK$hk_bezeichnung:$hk_betrag WM:$forderung_monatlich $mietvertrag_id, $monat, $jahr</h1>";
			// ############################1
			
			/* Monate nach der Betriebs- und Heizkostenabrechnung */
			/*
			 * if($monat >= $hk_monat OR $monat >= $bk_monat){
			 *
			 * #######/*Regelfall Zahlbetrag = geforderter Betrag
			 */
			if ($betrag == $forderung_monatlich && ! $gebucht) {
				echo "$buchungsnummer - betrag == forderung_monatlich<br>";
				$aktuelle_forderung = $this->import_forderung_monatlich ( $mietvertrag_id, $monat, $jahr );
				foreach ( $aktuelle_forderung as $key => $value ) {
					$db_abfrage = "INSERT INTO MIETBUCHUNGEN VALUES (NULL, '$mietvertrag_id', '$buchungsdatum', '$value', '$buchungsnummer', '$key', '1')";
					$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
					/* Zugewiesene MIETBUCHUNG_DAT auslesen */
					$last_dat = mysql_insert_id ();
					protokollieren ( 'MIETBUCHUNGEN', $last_dat, '0' );
				} /* End foreach */
				$gebucht = true;
			} /* End regelfall */
			// ########
			/* Zahlbetrag = BK Nachzahlung */
			if ($betrag == $bk_betrag && ! $gebucht) {
				echo "$buchungsnummer - betrag == bk_betrag<br>";
				$db_abfrage = "INSERT INTO MIETBUCHUNGEN VALUES (NULL, '$mietvertrag_id', '$buchungsdatum', '$betrag', '$buchungsnummer', 'Betriebskostennachzahlung', '1')";
				$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
				/* Zugewiesene MIETBUCHUNG_DAT auslesen */
				$last_dat = mysql_insert_id ();
				protokollieren ( 'MIETBUCHUNGEN', $last_dat, '0' );
				$gebucht = true;
			} /* Zahlbetrag = BK Nachzahlung */
			
			/* Zahlbetrag = HK Nachzahlung */
			if ($betrag == $hk_betrag && ! $gebucht) {
				echo "$buchungsnummer - betrag == hk_betrag<br>";
				$db_abfrage = "INSERT INTO MIETBUCHUNGEN VALUES (NULL, '$mietvertrag_id', '$buchungsdatum', '$betrag', '$buchungsnummer', 'Heizkostennachzahlung', '1')";
				$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
				/* Zugewiesene MIETBUCHUNG_DAT auslesen */
				$last_dat = mysql_insert_id ();
				protokollieren ( 'MIETBUCHUNGEN', $last_dat, '0' );
				$gebucht = true;
			} /* Zahlbetrag = HK Nachzahlung */
			// ############
			
			/* Zahlbetrag = HK+BK Nachzahlung */
			if ($betrag == $hk_und_bk && ! $gebucht) {
				echo "$buchungsnummer - betrag == hk_und_bk<br>";
				$db_abfrage = "INSERT INTO MIETBUCHUNGEN VALUES (NULL, '$mietvertrag_id', '$buchungsdatum', '$hk_betrag', '$buchungsnummer', 'Heizkostennachzahlung', '1')";
				$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
				/* Zugewiesene MIETBUCHUNG_DAT auslesen */
				$last_dat = mysql_insert_id ();
				protokollieren ( 'MIETBUCHUNGEN', $last_dat, '0' );
				$db_abfrage = "INSERT INTO MIETBUCHUNGEN VALUES (NULL, '$mietvertrag_id', '$buchungsdatum', '$bk_betrag', '$buchungsnummer', 'Betriebskostennachzahlung', '1')";
				$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
				/* Zugewiesene MIETBUCHUNG_DAT auslesen */
				$last_dat = mysql_insert_id ();
				protokollieren ( 'MIETBUCHUNGEN', $last_dat, '0' );
				$gebucht = true;
			} /* Zahlbetrag = BK+HK Nachzahlung */
			
			// #############
			$wm_und_bkhk = $forderung_monatlich + $hk_und_bk;
			/* Zahlbetrag = BK+HK+WARMMIETE Nachzahlung */
			if ($betrag == $wm_und_bkhk && ! $gebucht) {
				echo "$buchungsnummer - betrag == wm_und_bkhk<br>";
				$db_abfrage = "INSERT INTO MIETBUCHUNGEN VALUES (NULL, '$mietvertrag_id', '$buchungsdatum', '$hk_betrag', '$buchungsnummer', '$hk_bezeichnung', '1')";
				$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
				/* Zugewiesene MIETBUCHUNG_DAT auslesen */
				$last_dat = mysql_insert_id ();
				protokollieren ( 'MIETBUCHUNGEN', $last_dat, '0' );
				$db_abfrage = "INSERT INTO MIETBUCHUNGEN VALUES (NULL, '$mietvertrag_id', '$buchungsdatum', '$bk_betrag', '$buchungsnummer', '$bk_bezeichnung', '1')";
				$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
				/* Zugewiesene MIETBUCHUNG_DAT auslesen */
				$last_dat = mysql_insert_id ();
				protokollieren ( 'MIETBUCHUNGEN', $last_dat, '0' );
				$aktuelle_forderung = $this->import_forderung_monatlich ( $mietvertrag_id, $monat, $jahr );
				foreach ( $aktuelle_forderung as $key => $value ) {
					$db_abfrage = "INSERT INTO MIETBUCHUNGEN VALUES (NULL, '$mietvertrag_id', '$buchungsdatum', '$value', '$buchungsnummer', '$key', '1')";
					$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
					/* Zugewiesene MIETBUCHUNG_DAT auslesen */
					$last_dat = mysql_insert_id ();
					protokollieren ( 'MIETBUCHUNGEN', $last_dat, '0' );
				} /* End foreach */
				$gebucht = true;
			} /* Zahlbetrag = BK+HK+WARMMIETE Nachzahlung */
			/* wenn bisher nicht gebucht */
			
			if (! $gebucht) {
				/* wenn betrag kleiner als wm größer NULL */
				if ($betrag < $forderung_monatlich && $betrag > 0) {
					echo "$buchungsnummer - betrag < forderung_monatlich NK & HK & REST AUF KM<br>";
					$nebenkosten_vz = $this->betriebskosten_monatlich ( $mietvertrag_id, $monat, $jahr );
					$heizkosten_vz = $this->heizkosten_monatlich ( $mietvertrag_id, $monat, $jahr );
					$rest = $betrag - $nebenkosten_vz - $heizkosten_vz;
					
					/* Buchung der Einzelnen Positionen, wie manuell */
					/* Nebenkosten vorauszahlung */
					$db_abfrage = "INSERT INTO MIETBUCHUNGEN VALUES (NULL, '$mietvertrag_id', '$buchungsdatum', '$nebenkosten_vz', '$buchungsnummer', 'Nebenkosten Vorauszahlung111', '1')";
					$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
					/* Zugewiesene MIETBUCHUNG_DAT auslesen */
					$last_dat = mysql_insert_id ();
					protokollieren ( 'MIETBUCHUNGEN', $last_dat, '0' );
					
					/* Heizkosten vorauszahlung */
					$db_abfrage = "INSERT INTO MIETBUCHUNGEN VALUES (NULL, '$mietvertrag_id', '$buchungsdatum', '$heizkosten_vz', '$buchungsnummer', 'Heizkosten Vorauszahlung111', '1')";
					$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
					/* Zugewiesene MIETBUCHUNG_DAT auslesen */
					$last_dat = mysql_insert_id ();
					protokollieren ( 'MIETBUCHUNGEN', $last_dat, '0' );
					
					/* Rest auf Kaltmiete */
					$db_abfrage = "INSERT INTO MIETBUCHUNGEN VALUES (NULL, '$mietvertrag_id', '$buchungsdatum', '$rest', '$buchungsnummer', 'Miete kalt REST', '1')";
					$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
					/* Zugewiesene MIETBUCHUNG_DAT auslesen */
					$last_dat = mysql_insert_id ();
					protokollieren ( 'MIETBUCHUNGEN', $last_dat, '0' );
					$gebucht = true;
				} /* ENDE Betrag < Forderung && > 0 */
				
				/* Betrag > Forderung */
				if ($betrag > $forderung_monatlich && ! $gebucht) {
					$rest = $betrag - $forderung_monatlich;
					echo "$buchungsnummer - betrag > forderung_monatlich NORMAL + REST AUF KM<br>";
					$aktuelle_forderung = $this->import_forderung_monatlich ( $mietvertrag_id, $monat, $jahr );
					
					/* Buchung der regulären Forderung */
					foreach ( $aktuelle_forderung as $key => $value ) {
						$db_abfrage = "INSERT INTO MIETBUCHUNGEN VALUES (NULL, '$mietvertrag_id', '$buchungsdatum', '$value', '$buchungsnummer', '$key', '1')";
						$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
						/* Zugewiesene MIETBUCHUNG_DAT auslesen */
						$last_dat = mysql_insert_id ();
						protokollieren ( 'MIETBUCHUNGEN', $last_dat, '0' );
					} /* End foreach */
					
					/* Buchung des Restes auf Kaltmiete */
					$db_abfrage = "INSERT INTO MIETBUCHUNGEN VALUES (NULL, '$mietvertrag_id', '$buchungsdatum', '$rest', '$buchungsnummer', 'Miete kalt ÜBERSCHUSS', '1')";
					$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
					/* Zugewiesene MIETBUCHUNG_DAT auslesen */
					$last_dat = mysql_insert_id ();
					protokollieren ( 'MIETBUCHUNGEN', $last_dat, '0' );
					$gebucht = true;
				} /* End Betrag > Forderung */
				if ($betrag < 0 && ! $gebucht) {
					
					$diff = $betrag + $forderung_monatlich;
					/* Wenn Minusbetrag = monatliche Forderung */
					if ($diff == 0) {
						$aktuelle_forderung = $this->import_forderung_monatlich ( $mietvertrag_id, $monat, $jahr );
						echo "MINUS $buchungsnummer - $betrag === $forderung_monatlich DIFF $diff<br>";
						/* Buchung der regulären Forderung als MINUS */
						foreach ( $aktuelle_forderung as $key => $value ) {
							$db_abfrage = "INSERT INTO MIETBUCHUNGEN VALUES (NULL, '$mietvertrag_id', '$this->datum_heute', '-$value', '$buchungsnummer', '$key', '1')";
							$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
							/* Zugewiesene MIETBUCHUNG_DAT auslesen */
							$last_dat = mysql_insert_id ();
							protokollieren ( 'MIETBUCHUNGEN', $last_dat, '0' );
							$gebucht = true;
						} /* End foreach */
					}
					
					if ($diff > 0) {
						/* Von der Kaltmiete abziehen */
						echo "MINUS $buchungsnummer - $betrag > $forderung_monatlich DIFF $diff<br>";
						$db_abfrage = "INSERT INTO MIETBUCHUNGEN VALUES (NULL, '$mietvertrag_id', '$this->datum_heute', '$betrag', '$buchungsnummer', 'Miete kalt', '1')";
						$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
						/* Zugewiesene MIETBUCHUNG_DAT auslesen */
						$last_dat = mysql_insert_id ();
						protokollieren ( 'MIETBUCHUNGEN', $last_dat, '0' );
						$gebucht = true;
					} /* ENDE Von der Kaltmiete abziehen */
					
					if ($diff < 0) {
						echo "$buchungsnummer - $betrag < $forderung_monatlich DIFF $diff<br>";
						/* Erstmal wie gewöhnlich komplette Forderung abziehen, danach rest von Kaltmiete */
						$aktuelle_forderung = $this->import_forderung_monatlich ( $mietvertrag_id, $monat, $jahr );
						/* Buchung der regulären Forderung als MINUS */
						foreach ( $aktuelle_forderung as $key => $value ) {
							$db_abfrage = "INSERT INTO MIETBUCHUNGEN VALUES (NULL, '$mietvertrag_id', '$this->datum_heute', '-$value', '$buchungsnummer', '$key', '1')";
							$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
							/* Zugewiesene MIETBUCHUNG_DAT auslesen */
							$last_dat = mysql_insert_id ();
							protokollieren ( 'MIETBUCHUNGEN', $last_dat, '0' );
						} /* End foreach */
						/* Rest Minus ($diff) Von der Kaltmiete abziehen */
						$db_abfrage = "INSERT INTO MIETBUCHUNGEN VALUES (NULL, '$mietvertrag_id', '$this->datum_heute', '$diff', '$buchungsnummer', 'Miete kalt ', '1')";
						$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
						/* Zugewiesene MIETBUCHUNG_DAT auslesen */
						$last_dat = mysql_insert_id ();
						protokollieren ( 'MIETBUCHUNGEN', $last_dat, '0' );
						$gebucht = true;
					} /* ENDE Von der Kaltmiete abziehen */
				} // ende if betrag < 0
			} // end if !$gebucht
				  // print_r($aktuelle_forderung);
			/*
			 * }else
			 * /*Monate vor der Betriebs- und Heizkostenabrechnung
			 */
			/*
			 * {
			 * echo "kein bk hk monat";
			 * }
			 */
		} // end function
		
		/* Regelfall Zahlbetrag = geforderter Betrag */
		/*
		 * if($betrag == $forderung_monatlich){
		 * echo "$buchungsnummer - $betrag == $forderung_monatlich<br>";
		 * $aktuelle_forderung = $this->import_forderung_monatlich($mietvertrag_id, $monat, $jahr);
		 * foreach($aktuelle_forderung as $key => $value){
		 * $db_abfrage = "INSERT INTO MIETBUCHUNGEN VALUES (NULL, '$mietvertrag_id', '$this->datum_heute', '$value', '$buchungsnummer', '$key', '1')";
		 * $resultat = mysql_query($db_abfrage) or die(mysql_error());
		 * /*Zugewiesene MIETBUCHUNG_DAT auslesen
		 */
		/*
		 * $last_dat = mysql_insert_id();
		 * protokollieren('MIETBUCHUNGEN', $last_dat, '0');
		 * }/*End foreach
		 */
	} /* End regelfall */
	// ##############################END IF 3
	// ##############################2
	/* Betrag > Forderung */
	/*
	 * if($betrag > $forderung_monatlich){
	 * $rest = $betrag - $forderung_monatlich;
	 * echo "$buchungsnummer - $betrag > $forderung_monatlich NORMAL + REST AUF KM<br>";
	 * $aktuelle_forderung = $this->import_forderung_monatlich($mietvertrag_id, $monat, $jahr);
	 *
	 * /*Buchung der regulären Forderung
	 */
	/*
	 * foreach($aktuelle_forderung as $key => $value){
	 * $db_abfrage = "INSERT INTO MIETBUCHUNGEN VALUES (NULL, '$mietvertrag_id', '$this->datum_heute', '$value', '$buchungsnummer', '$key', '1')";
	 * $resultat = mysql_query($db_abfrage) or
	 * die(mysql_error());
	 * /*Zugewiesene MIETBUCHUNG_DAT auslesen
	 */
	/*
	 * $last_dat = mysql_insert_id();
	 * protokollieren('MIETBUCHUNGEN', $last_dat, '0');
	 * }/*End foreach
	 */
	
	/* Buchung des Restes auf Kaltmiete */
	/*
	 * $db_abfrage = "INSERT INTO MIETBUCHUNGEN VALUES (NULL, '$mietvertrag_id', '$this->datum_heute', '$rest', '$buchungsnummer', 'Miete kalt ÜBERSCHUSS R $rest B: $betrag F: $forderung_monatlich', '1')";
	 * $resultat = mysql_query($db_abfrage) or
	 * die(mysql_error());
	 * /*Zugewiesene MIETBUCHUNG_DAT auslesen
	 */
	/*
	 * $last_dat = mysql_insert_id();
	 * protokollieren('MIETBUCHUNGEN', $last_dat, '0');
	 * }/*End Betrag > Forderung
	 */
	// #############################3
	/* Betrag < Forderung && > 0 */
	/*
	 * if($betrag < $forderung_monatlich && $betrag > 0){
	 * echo "$buchungsnummer - $betrag < $forderung_monatlich NK & HK & REST AUF KM<br>";
	 * $nebenkosten_vz = $this->betriebskosten_monatlich($mietvertrag_id,$monat,$jahr);
	 * $heizkosten_vz = $this->heizkosten_monatlich($mietvertrag_id,$monat,$jahr);
	 * $rest = $betrag - $nebenkosten_vz - $heizkosten_vz;
	 *
	 * /*Buchung der Einzelnen Positionen, wie manuell
	 */
	/* Nebenkosten vorauszahlung */
	/*
	 * $db_abfrage = "INSERT INTO MIETBUCHUNGEN VALUES (NULL, '$mietvertrag_id', '$this->datum_heute', '$nebenkosten_vz', '$buchungsnummer', 'Nebenkosten Vorauszahlung', '1')";
	 * $resultat = mysql_query($db_abfrage) or
	 * die(mysql_error());
	 * /*Zugewiesene MIETBUCHUNG_DAT auslesen
	 */
	/*
	 * $last_dat = mysql_insert_id();
	 * protokollieren('MIETBUCHUNGEN', $last_dat, '0');
	 *
	 * /*Heizkosten vorauszahlung
	 */
	/*
	 * $db_abfrage = "INSERT INTO MIETBUCHUNGEN VALUES (NULL, '$mietvertrag_id', '$this->datum_heute', '$heizkosten_vz', '$buchungsnummer', 'Heizkosten Vorauszahlung', '1')";
	 * $resultat = mysql_query($db_abfrage) or
	 * die(mysql_error());
	 * /*Zugewiesene MIETBUCHUNG_DAT auslesen
	 */
	/*
	 * $last_dat = mysql_insert_id();
	 * protokollieren('MIETBUCHUNGEN', $last_dat, '0');
	 *
	 * /*Rest auf Kaltmiete
	 */
	/*
	 * $db_abfrage = "INSERT INTO MIETBUCHUNGEN VALUES (NULL, '$mietvertrag_id', '$this->datum_heute', '$rest', '$buchungsnummer', 'Miete kalt REST', '1')";
	 * $resultat = mysql_query($db_abfrage) or
	 * die(mysql_error());
	 * /*Zugewiesene MIETBUCHUNG_DAT auslesen
	 */
	/*
	 * $last_dat = mysql_insert_id();
	 * protokollieren('MIETBUCHUNGEN', $last_dat, '0');
	 * }/* ENDE Betrag < Forderung && > 0
	 */
	
	/* Wenn Rückläufer oder Negativzahlung bzw Auszahlung */
	/*
	 * if($betrag<0){
	 *
	 * $diff = $betrag + $forderung_monatlich;
	 * /*Wenn Minusbetrag = monatliche Forderung
	 */
	/*
	 * if($diff == 0){
	 * $aktuelle_forderung = $this->import_forderung_monatlich($mietvertrag_id, $monat, $jahr);
	 * echo "MINUS $buchungsnummer - $betrag === $forderung_monatlich DIFF $diff<br>";
	 * /*Buchung der regulären Forderung als MINUS
	 */
	/*
	 * foreach($aktuelle_forderung as $key => $value){
	 * $db_abfrage = "INSERT INTO MIETBUCHUNGEN VALUES (NULL, '$mietvertrag_id', '$this->datum_heute', '-$value', '$buchungsnummer', '$key', '1')";
	 * $resultat = mysql_query($db_abfrage) or
	 * die(mysql_error());
	 * /*Zugewiesene MIETBUCHUNG_DAT auslesen
	 */
	/*
	 * $last_dat = mysql_insert_id();
	 * protokollieren('MIETBUCHUNGEN', $last_dat, '0');
	 * }/*End foreach
	 */
	/*
	 * }
	 * /*
	 * if($diff>0){
	 * /*Von der Kaltmiete abziehen
	 */
	/*
	 * echo "MINUS $buchungsnummer - $betrag > $forderung_monatlich DIFF $diff<br>";
	 * $db_abfrage = "INSERT INTO MIETBUCHUNGEN VALUES (NULL, '$mietvertrag_id', '$this->datum_heute', '$betrag', '$buchungsnummer', 'Miete kalt MINUS DIF > 0', '1')";
	 * $resultat = mysql_query($db_abfrage) or
	 * die(mysql_error());
	 * /*Zugewiesene MIETBUCHUNG_DAT auslesen
	 */
	/*
	 * $last_dat = mysql_insert_id();
	 * protokollieren('MIETBUCHUNGEN', $last_dat, '0');
	 * }/* ENDE Von der Kaltmiete abziehen
	 */
	/*
	 * if($diff<0){
	 * echo "$buchungsnummer - $betrag < $forderung_monatlich DIFF $diff<br>";
	 * /*Erstmal wie gewöhnlich komplette Forderung abziehen, danach rest von Kaltmiete
	 */
	/*
	 * $aktuelle_forderung = $this->import_forderung_monatlich($mietvertrag_id, $monat, $jahr);
	 * /*Buchung der regulären Forderung als MINUS
	 */
	/*
	 * foreach($aktuelle_forderung as $key => $value){
	 * $db_abfrage = "INSERT INTO MIETBUCHUNGEN VALUES (NULL, '$mietvertrag_id', '$this->datum_heute', '-$value', '$buchungsnummer', '$key', '1')";
	 * $resultat = mysql_query($db_abfrage) or
	 * die(mysql_error());
	 * /*Zugewiesene MIETBUCHUNG_DAT auslesen
	 */
	/*
	 * $last_dat = mysql_insert_id();
	 * protokollieren('MIETBUCHUNGEN', $last_dat, '0');
	 * }/*End foreach
	 */
	/* Rest Minus ($diff) Von der Kaltmiete abziehen */
	/*
	 * $db_abfrage = "INSERT INTO MIETBUCHUNGEN VALUES (NULL, '$mietvertrag_id', '$this->datum_heute', '$diff', '$buchungsnummer', 'Miete kalt Dif < 0', '1')";
	 * $resultat = mysql_query($db_abfrage) or
	 * die(mysql_error());
	 * /*Zugewiesene MIETBUCHUNG_DAT auslesen
	 */
	/*
	 * $last_dat = mysql_insert_id();
	 * protokollieren('MIETBUCHUNGEN', $last_dat, '0');
	 * }/* ENDE Von der Kaltmiete abziehen
	 */
	
	/*
	 * }//ende if betrag < 0
	 * }//end else
	 */
	function monatsabschluesse_speichern($mietvertrag_id, $betrag) {
		$datum = $this->datum_heute;
		$db_abfrage = "INSERT INTO MONATSABSCHLUSS VALUES (NULL, '$mietvertrag_id', '$datum', '$betrag', '1', NULL)";
		$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		if (! $resultat) {
			echo "Monatsabschluss von $betrag für MV $mietvertrag_id wurde nicht gespeichert!";
		}
	}
	function check_mietentwicklung($kostentraeger_typ, $kostentrager_id, $kostenkategorie, $betrag, $anfang, $ende) {
		$result = mysql_query ( "SELECT * FROM MIETENTWICKLUNG WHERE KOSTENTRAEGER_TYP='$kostentraeger_typ' && KOSTENTRAEGER_ID = '$kostentrager_id' && KOSTENKATEGORIE='$kostenkategorie' && ANFANG='$anfang' && ENDE='$ende' && BETRAG='$betrag' " );
		$numrows = mysql_numrows ( $result );
		if ($numrows < 1) {
			return false;
		} else {
			return true;
		}
	}
	function mietentwicklung_speichern($kostentraeger_typ, $kostentrager_id, $kostenkategorie, $betrag, $anfang, $ende) {
		$me_exists = $this->check_mietentwicklung ( $kostentraeger_typ, $kostentrager_id, $kostenkategorie, $betrag, $anfang, $ende );
		if (! $me_exists) {
			
			$last_id = $this->get_mietentwicklung_last_id ();
			$last_id = $last_id + 1;
			$datum = $this->datum_heute;
			$db_abfrage = "INSERT INTO MIETENTWICKLUNG VALUES (NULL, '$last_id', '$kostentraeger_typ', '$kostentrager_id', '$kostenkategorie', '$anfang', '$ende', '$betrag', '1')";
			$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
			
			/* Zugewiesene MIETBUCHUNG_DAT auslesen */
			$last_dat = mysql_insert_id ();
			protokollieren ( 'MIETENTWICKLUNG', $last_dat, '0' );
		} else {
			// echo "ME definiert";
		}
	}
	function get_mietentwicklung_last_id() {
		$result = mysql_query ( "SELECT MIETENTWICKLUNG_ID FROM MIETENTWICKLUNG WHERE MIETENTWICKLUNG_AKTUELL='1' ORDER BY MIETENTWICKLUNG_ID DESC LIMIT 0,1" );
		$row = mysql_fetch_assoc ( $result );
		return $row ['MIETENTWICKLUNG_ID'];
	}
	
	// ########### FORDERUNGEN AUS MIETENTWICKLUNG
	function datum_1_mietdefinition($mietvertrag_id) {
		$result = mysql_query ( "SELECT ANFANG FROM MIETENTWICKLUNG WHERE KOSTENTRAEGER_TYP='Mietvertrag' && KOSTENTRAEGER_ID = '$mietvertrag_id' && MIETENTWICKLUNG_AKTUELL = '1' && KOSTENKATEGORIE NOT LIKE 'Saldo Vortrag Vorverwaltung' ORDER BY ANFANG ASC LIMIT 0,1" );
		$numrows = mysql_numrows ( $result );
		if ($numrows < 1) {
			return false;
		} else {
			$row = mysql_fetch_assoc ( $result );
			return $row ['ANFANG'];
		}
	}
	function monate_berechnen_bis_heute($start_datum) {
		echo "SD: $start_datum<br>";
		
		$letztes_datum_monat = date ( "Y-m-t" );
		// echo "LDATUM: $letztes_datum_monat<br>";
		$letztes_datum_monat = explode ( "-", $letztes_datum_monat );
		// echo "B $letztes_datum_monat[1] $letztes_datum_monat[2] $letztes_datum_monat[0] B";
		$letztes_datum_monat = mktime ( 0, 0, 0, $letztes_datum_monat [1], $letztes_datum_monat [2], $letztes_datum_monat [0] );
		
		$start_datum_arr = explode ( "-", $start_datum );
		$tag = $start_datum_arr [2];
		$monat = $start_datum_arr [1];
		$jahr = $start_datum_arr [0];
		echo "$tag $monat $jahr";
		$beginn_datum = mktime ( 0, 0, 0, $monat, $tag, $jahr );
		$tage_vergangen = round ( ($letztes_datum_monat - $beginn_datum) / (3600 * 24), 0 );
		// echo "<h3>Seit ".$tag.".".$monat.".".$jahr." sind ".$tage_vergangen.
		// " Tage vergangen</h3>";
		$monate_vergangen = floor ( $tage_vergangen / 30 );
		return $monate_vergangen;
	}
	function monate_seit_1buchung_arr($mietvertrag_id) {
		$this->mietvertrag_grunddaten_holen ( $mietvertrag_id );
		// $this->datum_heute;
		// $this->mietvertrag_von;
		echo "MV $this->datum_heute	$this->mietvertrag_von";
		
		$letztes_datum_monat = date ( "Y-m-t" );
		$aktuelles_datum = explode ( "-", $letztes_datum_monat );
		$aktuelles_jahr = $aktuelles_datum [0];
		$aktueller_monat = $aktuelles_datum [1];
		$aktueller_tag = $aktuelles_datum [2];
		
		$datum_erste_zahlung = $this->datum_1_zahlung ( $mietvertrag_id );
		$datum_letzte_zahlung = $this->datum_letzte_zahlung ( $mietvertrag_id );
		$datum_letzte_zahlung_arr = explode ( "-", "$datum_letzte_zahlung" );
		$monat_letzte_zahlung = $datum_letzte_zahlung_arr [1];
		
		$datum_einzug = explode ( "-", "$datum_erste_zahlung" );
		// $datum_einzug = explode("-","2006-06-01");
		$tag_einzug = $datum_einzug [2];
		$monat_einzug = $datum_einzug [1];
		$monat_einzug = substr ( $monat_einzug, - 1 );
		$jahr_einzug = $datum_einzug [0];
		
		$diff_in_jahren = $aktuelles_jahr - $jahr_einzug;
		// 2008-2007 = 1
		
		// if($diff_in_jahren>0){
		// for($i=0;$i<=$diff_in_jahren;$i++){
		for($i = $diff_in_jahren; $i >= 0; $i --) {
			$jahr = $aktuelles_jahr - $i;
			if ($jahr == $jahr_einzug) {
				for($a = $monat_einzug; $a <= $monat_letzte_zahlung; $a ++) {
					if ($a < 10) {
						$datum_jahr_arr = array (
								"monat" => "0$a",
								"jahr" => "$jahr" 
						);
					} else {
						$datum_jahr_arr = array (
								"monat" => "$a",
								"jahr" => "$jahr" 
						);
					}
					$monate_arr [] = $datum_jahr_arr;
				}
			} else {
				for($a = 1; $a <= 12; $a ++) {
					if ($a < 10) {
						$datum_jahr_arr = array (
								"monat" => "0$a",
								"jahr" => "$jahr" 
						);
					} else {
						$datum_jahr_arr = array (
								"monat" => "$a",
								"jahr" => "$jahr" 
						);
					}
					$monate_arr [] = $datum_jahr_arr;
				}
			}
			// echo "JAHR $jahr";
		}
		$this->array_anzeigen ( $monate_arr );
		
		return $monate_arr;
	}
	function datum_1_zahlung($mietvertrag_id) {
		$mv = new mietvertraege ();
		$mv->get_mietvertrag_infos_aktuell ( $mietvertrag_id );
		$o = new objekt ();
		$o->objekt_informationen ( $mv->objekt_id );
		$geldkonto_id = $o->geld_konten_arr [0] ['KONTO_ID'];
		
		$result = mysql_query ( "SELECT DATUM FROM GELD_KONTO_BUCHUNGEN WHERE KOSTENTRAEGER_TYP='Mietvertrag' && KOSTENTRAEGER_ID = '$mietvertrag_id' && GELDKONTO_ID='$geldkonto_id' && AKTUELL = '1' ORDER BY DATUM ASC LIMIT 0,1" );
		// echo "SELECT DATUM FROM GELD_KONTO_BUCHUNGEN WHERE KOSTENTRAEGER_TYP='Mietvertrag' && KOSTENTRAEGER_ID = '$mietvertrag_id' && AKTUELL = '1' ORDER BY DATUM ASC LIMIT 0,1";
		$row = mysql_fetch_assoc ( $result );
		return $row ['DATUM'];
	}
	function datum_letzte_zahlung($mietvertrag_id) {
		$result = mysql_query ( "SELECT DATUM FROM MIETBUCHUNGEN WHERE MIETVERTRAG_ID = '$mietvertrag_id' && MIETBUCHUNGEN_AKTUELL = '1' ORDER BY DATUM DESC LIMIT 0,1" );
		$row = mysql_fetch_assoc ( $result );
		return $row ['DATUM'];
	}
	function forderungen_array_seit_einzug($mietvertrag_id) {
		$monate_arr = $this->monate_seit_einzug_arr ( $mietvertrag_id );
		$this->array_anzeigen ( $monate_arr );
		for($i = 0; $i < count ( $monate_arr ); $i ++) {
			$forderungen [] = $this->forderung_monatlich ( $mietvertrag_id, $monate_arr [$i] [monat], $monate_arr [$i] [jahr] );
		}
		$this->array_anzeigen ( $forderungen );
	}
	function summe_forderungen_seit_einzug($mietvertrag_id) {
		$monate_arr = $this->monate_seit_einzug_arr ( $mietvertrag_id );
		// $this->array_anzeigen($monate_arr);
		$summe = "0";
		
		for($i = 0; $i < count ( $monate_arr ); $i ++) {
			$forderungen = $this->forderung_monatlich ( $mietvertrag_id, $monate_arr [$i] [monat], $monate_arr [$i] [jahr] );
			for($a = 0; $a < count ( $forderungen ); $a ++) {
				$summe = $summe + $forderungen [$a] ['BETRAG'];
			}
		}
		// $this->array_anzeigen($forderungen);
		unset ( $forderungen );
		unset ( $monate_arr );
		return $summe;
	}
	
	// Funktion zur Erstellung eines Arrays mit Monaten und Jahren seit Einzug bis aktuelles Jahr/Monat
	function monate_seit_einzug_arr($mietvertrag_id) {
		$zeitraum = new zeitraum ();
		$monate_arr = $zeitraum->zeitraum_arr_seit_einzug ( $mietvertrag_id );
		return $monate_arr;
	} // end function
	function forderungen_seit_einzug($mietvertrag_id) {
		$monate_arr = $this->monate_seit_einzug_arr ( $mietvertrag_id );
		$this->array_anzeigen ( $monate_arr );
		// for($i=0;$i<=count($monate_arr);$i++){
		// $forderung_arr[] = $this->forderung_monatlich($mietvertrag_id, $monate_arr[$i][monat], $monate_arr[$i][jahr]);
		// }
		// $this->array_anzeigen($forderung_arr);
		echo "#####################################";
		$this->monate_seit_1buchung_arr ( $mietvertrag_id );
	}
	function buchungen_forderungen_seit_einzug($mietvertrag_id) {
		$monate_arr = $this->monate_seit_einzug_arr ( $mietvertrag_id );
		for($i = 0; $i < count ( $monate_arr ); $i ++) {
			echo "<table>";
			$forderung_arr = $this->forderung_monatlich ( $mietvertrag_id, $monate_arr [$i] [monat], $monate_arr [$i] [jahr] );
			$zahlung_arr = $this->zahlungen_monatlich ( $mietvertrag_id, $monate_arr [$i] [monat], $monate_arr [$i] [jahr] );
			$monat_jahr = "" . $monate_arr [$i] [monat] . "-" . $monate_arr [$i] [jahr] . "";
			echo "<tr>";
			echo "<td>";
			$this->monatsbuchungen_anzeigen ( $monat_jahr, $zahlung_arr );
			echo "</td>";
			echo "<td>";
			$this->monatsforderungen_anzeigen ( $monat_jahr, $forderung_arr );
			echo "</td>";
			echo "</tr>";
		}
	}
	function monatsbuchungen_anzeigen($monat_jahr, $zahlungen_diesen_monat_arr) {
		echo "<table class=aktuelle_buchungen>";
		$monat_jahr_arr = explode ( "-", $monat_jahr );
		$monat = $monat_jahr_arr [0];
		$jahr = $monat_jahr_arr [1];
		echo "<tr><td colspan=6><b>BUCHUNGEN $monat $jahr</b></td></tr>";
		echo "<tr class=tabelle_ueberschrift_mietkontenblatt><td>B-NR</td><td>ÜBERWIESEN AM</td><td>ZAHLBETRAG</td><td>GEBUCHT AM</td><td>TEILBETRAG</td><td>KOSTENKATEGORIE</td></tr>";
		for($i = 0; $i < count ( $zahlungen_diesen_monat_arr ); $i ++) {
			echo "<tr>";
			$buchungsdatum = $this->date_mysql2german ( $zahlungen_diesen_monat_arr [$i] [DATUM] );
			echo "<td><b>" . $zahlungen_diesen_monat_arr [$i] ['BUCHUNGSNUMMER'] . "</b></td>";
			$zahldatum = $this->date_mysql2german ( $zahlungen_diesen_monat_arr [$i] [ZAHLDATUM] );
			
			if ($i == 0) {
				echo "<td>" . $zahldatum . "</td>";
				echo "<td><b>" . $zahlungen_diesen_monat_arr [$i] ['ZAHLBETRAG'] . " €</b></td>";
				echo "<td>" . $buchungsdatum . "</td>";
			} else {
				echo "<td></td><td></td><td></td>";
			}
			
			echo "<td><b>" . $zahlungen_diesen_monat_arr [$i] ['BETRAG'] . " €</b></td>";
			echo "<td>" . $zahlungen_diesen_monat_arr [$i] ['KOSTENKATEGORIE'] . "</td>";
			echo "</tr>";
		}
		$summe_zahlungen = $this->summe_zahlung_monatlich ( $mietvertrag_id, $monat, $jahr );
		echo "<tr><td colspan=5><b> Summe: $summe_zahlungen €</b></td></tr>";
		echo "</table>";
		echo "<br>";
		return $zahlungen_diesen_monat_arr;
	}
	function monatsforderungen_anzeigen($monat_jahr, $forderungen_diesen_monat_arr) {
		// $this->array_anzeigen($forderungen_diesen_monat_arr);
		if (! is_array ( $forderungen_diesen_monat_arr )) {
			// echo "<div class=aktuelle_forderungen><b>AKTUELLE FORDERUNGEN AUS $mietvertrag_id<br>";
			echo "Keine Forderungen in diesem Monat!";
			// echo "</div>";
			$error = TRUE;
		} else {
			// $this->array_anzeigen($forderungen_diesen_monat_arr);
			
			// echo "<div class=aktuelle_forderungen><b>AKTUELLE FORDERUNGEN aus $mietvertrag_id $this->monat_heute $this->jahr_heute</b><br>";
			echo "<table class=aktuelle_forderungen>";
			echo "<tr class=tabelle_ueberschrift_mietkontenblatt><td>MV</td><td>ANFANG</td><td>ENDE</td><td>KOSTENKATEGORIE</td><td>BETRAG</td></tr>";
			for($i = 0; $i < count ( $forderungen_diesen_monat_arr ); $i ++) {
				echo "<tr>";
				echo "<td>" . $forderungen_diesen_monat_arr [$i] ['mietvertrag_id'] . "</td>";
				$anfangsdatum = $this->date_mysql2german ( $forderungen_diesen_monat_arr [$i] [ANFANG] );
				echo "<td>" . $anfangsdatum . "</td>";
				$endedatum = $this->date_mysql2german ( $forderungen_diesen_monat_arr [$i] [ENDE] );
				if ($endedatum == "00.00.0000") {
					echo "<td>unbefristet</td>";
				} else {
					echo "<td>" . $endedatum . "</td>";
				}
				echo "<td>" . $forderungen_diesen_monat_arr [$i] ['KOSTENKATEGORIE'] . "</td>";
				echo "<td><b>" . $forderungen_diesen_monat_arr [$i] ['BETRAG'] . " €</b></td>";
				echo "</tr>";
			}
			// $summe_forderungen = $this->summe_forderung_monatlich($mietvertrag_id, $this->monat_heute, $this->jahr_heute);
			echo "<tr><td colspan=6><b> Summe: $summe_forderungen €</b></td></tr>";
			echo "</table>";
			echo "<br>";
			// echo "</div>";
			return $forderungen_diesen_monat_arr;
		}
	}
	
	/* Ausgabe aller Kostenkategorien für gewünschten Monat, Jahr als Array */
	function import_forderung_monatlich($mietvertrag_id, $monat, $jahr) {
		$result = mysql_query ( "SELECT KOSTENKATEGORIE, BETRAG FROM MIETENTWICKLUNG WHERE MIETVERTRAG_ID = '$mietvertrag_id' && MIETENTWICKLUNG_AKTUELL = '1' && ( ENDE = '0000-00-00' OR DATE_FORMAT( ENDE, '%Y-%m' ) >= '$jahr-$monat' && DATE_FORMAT( ANFANG, '%Y-%m' ) <= '$jahr-$monat' ) && DATE_FORMAT( ANFANG, '%Y-%m' ) <= '$jahr-$monat' && KOSTENKATEGORIE NOT LIKE '%abrechnung%' && KOSTENKATEGORIE NOT LIKE '%rate%' ORDER BY ANFANG ASC" );
		
		while ( $row = mysql_fetch_assoc ( $result ) ) {
			$aktuelle_forderung [$row ['KOSTENKATEGORIE']] = $row ['BETRAG'];
		}
		return $aktuelle_forderung;
	}
	function forderung_monatlich($mietvertrag_id, $monat, $jahr) {
		/* Alt mit ratenzahlung */
		// $result = mysql_query ("SELECT MIETVERTRAG_ID, ANFANG, ENDE, KOSTENKATEGORIE, BETRAG FROM MIETENTWICKLUNG WHERE MIETVERTRAG_ID = '$mietvertrag_id' && MIETENTWICKLUNG_AKTUELL = '1' && ( ENDE = '0000-00-00' OR DATE_FORMAT( ENDE, '%Y-%m' ) >= '$jahr-$monat' && DATE_FORMAT( ANFANG, '%Y-%m' ) <= '$jahr-$monat' ) && DATE_FORMAT( ANFANG, '%Y-%m' ) <= '$jahr-$monat' ORDER BY ANFANG ASC");
		/* Neu ohne ratenzahlung */
		if (strlen ( $monat ) < 2) {
			$monat = '0' . $monat;
		}
		$result = mysql_query ( "SELECT KOSTENTRAEGER_ID, ANFANG, ENDE, KOSTENKATEGORIE, BETRAG FROM MIETENTWICKLUNG WHERE KOSTENTRAEGER_TYP='MIETVERTRAG' && KOSTENTRAEGER_ID = '$mietvertrag_id' && MIETENTWICKLUNG_AKTUELL = '1' && ( ENDE = '0000-00-00' OR DATE_FORMAT( ENDE, '%Y-%m' ) >= '$jahr-$monat') && DATE_FORMAT( ANFANG, '%Y-%m' ) <= '$jahr-$monat'  && KOSTENKATEGORIE NOT LIKE 'RATENZAHLUNG' ORDER BY ANFANG ASC" );
		
		/* echo "SELECT MIETVERTRAG_ID, ANFANG, ENDE, KOSTENKATEGORIE, BETRAG FROM MIETENTWICKLUNG WHERE MIETVERTRAG_ID = '$mietvertrag_id' && MIETENTWICKLUNG_AKTUELL = '1' && ( ENDE = '0000-00-00' OR DATE_FORMAT( ENDE, '%Y-%m' ) >= '$jahr-$monat' && DATE_FORMAT( ANFANG, '%Y-%m' ) <= '$jahr-$monat' ) && DATE_FORMAT( ANFANG, '%Y-%m' ) <= '$jahr-$monat'ORDER BY ANFANG ASC"; */
		
		while ( $row = mysql_fetch_assoc ( $result ) )
			$aktuelle_forderung [] = $row;
			
			// $this->betriebskosten_monatlich($mietvertrag_id,$monat,$jahr);
			// $this->heizkosten_monatlich($mietvertrag_id,$monat,$jahr);
			// $this->kaltmiete_monatlich($mietvertrag_id,$monat,$jahr);
		if (isset ( $aktuelle_forderung )) {
			return $aktuelle_forderung;
		}
	}
	function forderung_aus_vertrag($mietvertrag_id) {
		/* Alt mit ratenzahlung */
		// $result = mysql_query ("SELECT MIETVERTRAG_ID, ANFANG, ENDE, KOSTENKATEGORIE, BETRAG FROM MIETENTWICKLUNG WHERE MIETVERTRAG_ID = '$mietvertrag_id' && MIETENTWICKLUNG_AKTUELL = '1' && ( ENDE = '0000-00-00' OR DATE_FORMAT( ENDE, '%Y-%m' ) >= '$jahr-$monat' && DATE_FORMAT( ANFANG, '%Y-%m' ) <= '$jahr-$monat' ) && DATE_FORMAT( ANFANG, '%Y-%m' ) <= '$jahr-$monat' ORDER BY ANFANG ASC");
		/* Neu ohne ratenzahlung */
		$result = mysql_query ( "SELECT KOSTENTRAEGER_ID, ANFANG, ENDE, KOSTENKATEGORIE, BETRAG FROM MIETENTWICKLUNG WHERE KOSTENTRAEGER_ID = '$mietvertrag_id' && KOSTENTRAEGER_TYP='MIETVERTRAG' && MIETENTWICKLUNG_AKTUELL = '1'  && KOSTENKATEGORIE NOT LIKE '%rate%' && KOSTENKATEGORIE NOT LIKE '%abrechnung%' ORDER BY ANFANG ASC" );
		
		/* echo "SELECT MIETVERTRAG_ID, ANFANG, ENDE, KOSTENKATEGORIE, BETRAG FROM MIETENTWICKLUNG WHERE MIETVERTRAG_ID = '$mietvertrag_id' && MIETENTWICKLUNG_AKTUELL = '1' && ( ENDE = '0000-00-00' OR DATE_FORMAT( ENDE, '%Y-%m' ) >= '$jahr-$monat' && DATE_FORMAT( ANFANG, '%Y-%m' ) <= '$jahr-$monat' ) && DATE_FORMAT( ANFANG, '%Y-%m' ) <= '$jahr-$monat'ORDER BY ANFANG ASC"; */
		
		while ( $row = mysql_fetch_assoc ( $result ) )
			$aktuelle_forderung [] = $row;
			
			// $this->betriebskosten_monatlich($mietvertrag_id,$monat,$jahr);
			// $this->heizkosten_monatlich($mietvertrag_id,$monat,$jahr);
			// $this->kaltmiete_monatlich($mietvertrag_id,$monat,$jahr);
		return $aktuelle_forderung;
	}
	function betriebskosten_heizkosten_fallig($mietvertrag_id, $monat, $jahr) {
		$result = mysql_query ( "SELECT MIETVERTRAG_ID, ANFANG, ENDE, KOSTENKATEGORIE, BETRAG FROM MIETENTWICKLUNG WHERE MIETVERTRAG_ID = '$mietvertrag_id' && MIETENTWICKLUNG_AKTUELL = '1' && ( ENDE = '0000-00-00' OR DATE_FORMAT( ENDE, '%Y-%m' ) >= '$jahr-$monat' && DATE_FORMAT( ANFANG, '%Y-%m' ) <= '$jahr-$monat' ) && DATE_FORMAT( ANFANG, '%Y-%m' ) <= '$jahr-$monat' && KOSTENKATEGORIE LIKE '%abrechnung%' ORDER BY ANFANG ASC" );
		$numrows = mysql_num_rows ( $result );
		return $numrows;
	}
	function bk_abrechnung_monat($mietvertrag_id, $jahr) {
		$result = mysql_query ( "SELECT ENDE FROM MIETENTWICKLUNG WHERE MIETVERTRAG_ID = '1' && MIETENTWICKLUNG_AKTUELL = '1' && ( ENDE = '0000-00-00' OR DATE_FORMAT( ENDE, '%Y' ) >= '$jahr' && DATE_FORMAT( ANFANG, '%Y' ) <= '$jahr' ) && DATE_FORMAT( ANFANG, '%Y' ) <= '$jahr' && KOSTENKATEGORIE LIKE '%Betriebskostenabrechnung%' ORDER BY ANFANG ASC LIMIT 0,1" );
		$row = mysql_fetch_assoc ( $result );
		$datum = explode ( "-", $row [ENDE] );
		$monat = $datum [1];
		return $monat;
	}
	function bk_abrechnung_arr($mietvertrag_id, $jahr) {
		$result = mysql_query ( "SELECT ENDE, BETRAG, KOSTENKATEGORIE FROM MIETENTWICKLUNG WHERE MIETVERTRAG_ID = '$mietvertrag_id' && MIETENTWICKLUNG_AKTUELL = '1' && ( ENDE = '0000-00-00' OR DATE_FORMAT( ENDE, '%Y' ) >= '$jahr' && DATE_FORMAT( ANFANG, '%Y' ) <= '$jahr' ) && DATE_FORMAT( ANFANG, '%Y' ) <= '$jahr' && KOSTENKATEGORIE LIKE '%Betriebskostenabrechnung%' ORDER BY ANFANG ASC LIMIT 0,1" );
		$numrows = mysql_num_rows ( $result );
		if ($numrows < 1) {
			return FALSE;
		} else {
			$row = mysql_fetch_assoc ( $result );
			return $row;
		}
	}
	function hk_abrechnung_arr($mietvertrag_id, $jahr) {
		$result = mysql_query ( "SELECT ENDE, BETRAG, KOSTENKATEGORIE FROM MIETENTWICKLUNG WHERE MIETVERTRAG_ID = '$mietvertrag_id' && MIETENTWICKLUNG_AKTUELL = '1' && ( ENDE = '0000-00-00' OR DATE_FORMAT( ENDE, '%Y' ) >= '$jahr' && DATE_FORMAT( ANFANG, '%Y' ) <= '$jahr' ) && DATE_FORMAT( ANFANG, '%Y' ) <= '$jahr' && KOSTENKATEGORIE LIKE '%Heizkostenabrechnung%' ORDER BY ANFANG ASC LIMIT 0,1" );
		$numrows = mysql_num_rows ( $result );
		if ($numrows < 1) {
			return FALSE;
		} else {
			$row = mysql_fetch_assoc ( $result );
			return $row;
		}
	}
	function hk_abrechnung_monat($mietvertrag_id, $jahr) {
		$result = mysql_query ( "SELECT ENDE FROM MIETENTWICKLUNG WHERE MIETVERTRAG_ID = '1' && MIETENTWICKLUNG_AKTUELL = '1' && ( ENDE = '0000-00-00' OR DATE_FORMAT( ENDE, '%Y' ) >= '$jahr' && DATE_FORMAT( ANFANG, '%Y' ) <= '$jahr' ) && DATE_FORMAT( ANFANG, '%Y' ) <= '$jahr' && KOSTENKATEGORIE LIKE '%Heizkostenabrechnung%' ORDER BY ANFANG ASC LIMIT 0,1" );
		$row = mysql_fetch_assoc ( $result );
		$datum = explode ( "-", $row [ENDE] );
		$monat = $datum [1];
		return $monat;
	}
	
	/*
	 * function rate_monatlich($mietvertrag_id, $monat, $jahr){
	 * /*Aktuelle Ratenzahlung
	 */
	/*
	 * $result = mysql_query ("SELECT MIETVERTRAG_ID, ANFANG, ENDE, KOSTENKATEGORIE, BETRAG FROM MIETENTWICKLUNG WHERE MIETVERTRAG_ID = '$mietvertrag_id' && MIETENTWICKLUNG_AKTUELL = '1' && ( ENDE = '0000-00-00' OR DATE_FORMAT( ENDE, '%Y-%m' ) >= '$jahr-$monat' && DATE_FORMAT( ANFANG, '%Y-%m' ) <= '$jahr-$monat' ) && DATE_FORMAT( ANFANG, '%Y-%m' ) <= '$jahr-$monat' && KOSTENKATEGORIE LIKE 'RATENZAHLUNG' ORDER BY ANFANG ASC");
	 * while ($row = mysql_fetch_assoc($result)) $aktuelle_forderung[] = $row;
	 * return $aktuelle_forderung;
	 * }
	 */
	function summe_rate_monatlich($mietvertrag_id, $monat, $jahr) {
		$result = mysql_query ( "SELECT SUM(BETRAG) AS SUMME_RATE FROM MIETENTWICKLUNG WHERE KOSTENTRAEGER_TYP='MIETVERTRAG' && KOSTENTRAEGER_ID = '$mietvertrag_id' && MIETENTWICKLUNG_AKTUELL = '1' && ( ENDE = '0000-00-00' OR DATE_FORMAT( ENDE, '%Y-%m' ) >= '$jahr-$monat' && DATE_FORMAT( ANFANG, '%Y-%m' ) <= '$jahr-$monat' ) && DATE_FORMAT( ANFANG, '%Y-%m' ) <= '$jahr-$monat' && KOSTENKATEGORIE LIKE 'RATENZAHLUNG' ORDER BY ANFANG ASC" );
		$row = mysql_fetch_assoc ( $result );
		$summe = $row ['SUMME_RATE'];
		return $summe;
	}
	
	/* Ausgabe der Summe aller Kostenkategorien für gewünschten Monat, Jahr als String */
	function summe_forderung_monatlich($mietvertrag_id, $monat, $jahr) {
		$laenge = strlen ( $monat );
		if ($laenge == 1) {
			$monat = '0' . $monat;
		}
		$result = mysql_query ( "SELECT SUM(BETRAG) AS SUMME_FORDERUNG, SUM(MWST_ANTEIL) AS MWST_ANTEIL FROM MIETENTWICKLUNG WHERE KOSTENTRAEGER_TYP='MIETVERTRAG' && KOSTENTRAEGER_ID = '$mietvertrag_id' && MIETENTWICKLUNG_AKTUELL = '1' && ( ENDE = '0000-00-00' OR DATE_FORMAT( ENDE, '%Y-%m' ) >= '$jahr-$monat') && DATE_FORMAT( ANFANG, '%Y-%m' ) <= '$jahr-$monat'  && KOSTENKATEGORIE NOT LIKE '%rate%' && KOSTENKATEGORIE NOT LIKE '%abrechnung%' && KOSTENKATEGORIE NOT LIKE '%mahngebühr%' && KOSTENKATEGORIE NOT LIKE 'Saldo Vortrag Vorverwaltung' && KOSTENKATEGORIE NOT LIKE '%energie%' ORDER BY ANFANG ASC" );
		// echo "SELECT SUM(BETRAG) AS SUMME_FORDERUNG, SUM(MWST_ANTEIL) AS MWST_ANTEIL FROM MIETENTWICKLUNG WHERE KOSTENTRAEGER_TYP='MIETVERTRAG' && KOSTENTRAEGER_ID = '$mietvertrag_id' && MIETENTWICKLUNG_AKTUELL = '1' && ( ENDE = '0000-00-00' OR DATE_FORMAT( ENDE, '%Y-%m' ) >= '$jahr-$monat') && DATE_FORMAT( ANFANG, '%Y-%m' ) <= '$jahr-$monat' && KOSTENKATEGORIE NOT LIKE '%rate%' && KOSTENKATEGORIE NOT LIKE '%abrechnung%' && KOSTENKATEGORIE NOT LIKE '%mahngebühr%' && KOSTENKATEGORIE NOT LIKE 'Saldo Vortrag Vorverwaltung' ORDER BY ANFANG ASC";
		// die();
		$numrows = mysql_numrows ( $result );
		if (! $numrows) {
			return '0.00';
		} else {
			$row = mysql_fetch_assoc ( $result );
			if ($row ['SUMME_FORDERUNG'] != null) {
				return $row ['SUMME_FORDERUNG'] . '|' . $row ['MWST_ANTEIL'];
			} else {
				return 0.00;
			}
			// $summe = number_format($summe, 2, ".", "");
			// return $summe;
		}
	}
	function summe_mahngebuehr_im_monat($mietvertrag_id, $monat, $jahr) {
		$laenge = strlen ( $monat );
		if ($laenge == 1) {
			$monat = '0' . $monat;
		}
		$result = mysql_query ( "SELECT SUM(BETRAG) SUMME_MAHNUNG FROM MIETENTWICKLUNG WHERE KOSTENTRAEGER_TYP='MIETVERTRAG' && KOSTENTRAEGER_ID = '$mietvertrag_id' && MIETENTWICKLUNG_AKTUELL = '1' && ( ENDE = '0000-00-00' OR DATE_FORMAT( ENDE, '%Y-%m' ) >= '$jahr-$monat') && DATE_FORMAT( ANFANG, '%Y-%m' ) <= '$jahr-$monat'  && KOSTENKATEGORIE='Mahngebühr' ORDER BY ANFANG ASC" );
		
		$numrows = mysql_numrows ( $result );
		if (! $numrows) {
			return false;
		} else {
			$row = mysql_fetch_assoc ( $result );
			return $row ['SUMME_MAHNUNG'];
		}
	}
	
	/*
	 * Rechnungspos updaten zu Skonto von Rechnunggrunddaten
	 * UPDATE `RECHNUNGEN_POSITIONEN` AS t1 LEFT JOIN `RECHNUNGEN` AS t2 ON( t1.`BELEG_NR` = t2.`BELEG_NR`) SET t1.`SKONTO`=t2.`SKONTO`
	 */
	
	/* Ausgabe aller Mahngebühren für gewünschten Monat, Jahr als Array */
	function mahngebuehr_monatlich_arr($mietvertrag_id, $monat, $jahr) {
		$result = mysql_query ( "SELECT * FROM MIETENTWICKLUNG WHERE KOSTENTRAEGER_TYP='MIETVERTRAG' && KOSTENTRAEGER_ID = '$mietvertrag_id' && MIETENTWICKLUNG_AKTUELL = '1' && ( ENDE = '0000-00-00' OR DATE_FORMAT( ENDE, '%Y-%m' ) >= '$jahr-$monat') && DATE_FORMAT( ANFANG, '%Y-%m' ) <= '$jahr-$monat'  && KOSTENKATEGORIE='Mahngebühr' ORDER BY ANFANG ASC" );
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			while ( $row = mysql_fetch_assoc ( $result ) )
				$my_arr [] = $row;
			$my_arr [100] [sanel] = 'SANEL';
			return $my_arr;
		} else {
			return false;
		}
	}
	
	/* Ausgabe der Summe aller Kostenkategorien für gewünschten Monat, Jahr als String */
	function summe_forderung_aus_vertrag($mietvertrag_id) {
		
		// $result = mysql_query ("SELECT MIETVERTRAG_ID, ANFANG, ENDE, KOSTENKATEGORIE, BETRAG FROM MIETENTWICKLUNG WHERE MIETVERTRAG_ID = '$mietvertrag_id' && MIETENTWICKLUNG_AKTUELL = '1' && KOSTENKATEGORIE NOT LIKE '%rate%' && KOSTENKATEGORIE NOT LIKE '%abrechnung%' ORDER BY ANFANG ASC");
		$result = mysql_query ( "SELECT SUM(BETRAG) AS SUMME_FORDERUNG, SUM(MWST_ANTEIL) AS MWST_ANTEIL FROM MIETENTWICKLUNG WHERE KOSTENTRAEGER_TYP='MIETVERTRAG' && KOSTENTRAEGER_ID = '$mietvertrag_id' && MIETENTWICKLUNG_AKTUELL = '1'  && KOSTENKATEGORIE NOT LIKE '%rate%' && KOSTENKATEGORIE NOT LIKE '%abrechnung%' &&  KOSTENKATEGORIE NOT LIKE '%mahngebühr%' && KOSTENKATEGORIE NOT LIKE 'Saldo Vortrag Vorverwaltung'" );
		$numrows = mysql_numrows ( $result );
		if ($numrows < 1) {
			return false;
		} else {
			$row = mysql_fetch_assoc ( $result );
			$summe = $row ['SUMME_FORDERUNG'];
			$summe_mwst = $row ['MWST_ANTEIL'];
			
			$summe = number_format ( $summe, 2, ".", "" );
			$summe_mwst = number_format ( $summe, 2, ".", "" );
			return "$summe|$summe_mwst";
		}
	}
	
	/* Datum der Betriebskostenabrechnung */
	function datum_betriebskostenabrechnung($mietvertrag_id, $monat, $jahr) {
		$laenge = strlen ( $monat );
		if ($laenge == 1 && $monat < 10) {
			$monat = "0" . $monat;
		}
		$result = mysql_query ( "SELECT ANFANG FROM MIETENTWICKLUNG WHERE KOSTENTRAEGER_TYP='MIETVERTRAG' && KOSTENTRAEGER_ID = '$mietvertrag_id' && MIETENTWICKLUNG_AKTUELL = '1' && ( ENDE = '0000-00-00' OR DATE_FORMAT( ENDE, '%Y-%m' ) >= '$jahr-$monat' && DATE_FORMAT( ANFANG, '%Y-%m' ) <= '$jahr-$monat' ) && DATE_FORMAT( ANFANG, '%Y-%m' ) <= '$jahr-$monat' && KOSTENKATEGORIE LIKE '%Betriebskostenabrechnung%'" );
		$numrows = mysql_numrows ( $result );
		if ($numrows < 1) {
			return false;
		} else {
			$row = mysql_fetch_assoc ( $result );
			$datum = $this->date_mysql2german ( $row ['ANFANG'] );
			return $datum;
		}
	}
	
	/* Datum der Heizkostenabrechnung */
	function datum_heizkostenabrechnung($mietvertrag_id, $monat, $jahr) {
		$laenge = strlen ( $monat );
		if ($laenge == 1 && $monat < 10) {
			$monat = "0" . $monat;
		}
		$result = mysql_query ( "SELECT ANFANG FROM MIETENTWICKLUNG WHERE KOSTENTRAEGER_TYP='MIETVERTRAG' && KOSTENTRAEGER_ID = '$mietvertrag_id' && MIETENTWICKLUNG_AKTUELL = '1' && ( ENDE = '0000-00-00' OR DATE_FORMAT( ENDE, '%Y-%m' ) >= '$jahr-$monat' && DATE_FORMAT( ANFANG, '%Y-%m' ) <= '$jahr-$monat' ) && DATE_FORMAT( ANFANG, '%Y-%m' ) <= '$jahr-$monat' && KOSTENKATEGORIE LIKE '%Heizkostenabrechnung%'" );
		$numrows = mysql_numrows ( $result );
		if ($numrows < 1) {
			return false;
		} else {
			$row = mysql_fetch_assoc ( $result );
			$datum = $this->date_mysql2german ( $row ['ANFANG'] );
			return $datum;
		}
	}
	
	/* Datum der Wasserkostenabrechnung */
	function datum_wasserkostenabrechnung($mietvertrag_id, $monat, $jahr) {
		$laenge = strlen ( $monat );
		if ($laenge == 1 && $monat < 10) {
			$monat = "0" . $monat;
		}
		$result = mysql_query ( "SELECT ANFANG FROM MIETENTWICKLUNG WHERE KOSTENTRAEGER_TYP='MIETVERTRAG' && KOSTENTRAEGER_ID = '$mietvertrag_id' && MIETENTWICKLUNG_AKTUELL = '1' && ( ENDE = '0000-00-00' OR DATE_FORMAT( ENDE, '%Y-%m' ) >= '$jahr-$monat' && DATE_FORMAT( ANFANG, '%Y-%m' ) <= '$jahr-$monat' ) && DATE_FORMAT( ANFANG, '%Y-%m' ) <= '$jahr-$monat' && KOSTENKATEGORIE LIKE '%Wasserkostenabrechnung%'" );
		$numrows = mysql_numrows ( $result );
		if ($numrows < 1) {
			return false;
		} else {
			$row = mysql_fetch_assoc ( $result );
			$datum = $this->date_mysql2german ( $row ['ANFANG'] );
			return $datum;
		}
	}
	
	/* Summe der Betriebskostenabrechnung */
	function summe_betriebskostenabrechnung($mietvertrag_id, $monat, $jahr) {
		unset ( $this->summe_bk_abrechnung );
		$laenge = strlen ( $monat );
		if ($laenge == 1 && $monat < 10) {
			$monat = "0" . $monat;
		}
		$result = mysql_query ( "SELECT SUM(BETRAG) SUMME_BETRIEBSKOSTEN FROM MIETENTWICKLUNG WHERE KOSTENTRAEGER_TYP='Mietvertrag' && KOSTENTRAEGER_ID = '$mietvertrag_id' && MIETENTWICKLUNG_AKTUELL = '1' && ( ENDE = '0000-00-00' OR DATE_FORMAT( ENDE, '%Y-%m' ) >= '$jahr-$monat' && DATE_FORMAT( ANFANG, '%Y-%m' ) <= '$jahr-$monat' ) && DATE_FORMAT( ANFANG, '%Y-%m' ) <= '$jahr-$monat' && KOSTENKATEGORIE LIKE 'Betriebskostenabrechnung%'" );
		$numrows = mysql_numrows ( $result );
		if ($numrows < 1) {
			return false;
		} else {
			$row = mysql_fetch_assoc ( $result );
			$summe = $row ['SUMME_BETRIEBSKOSTEN'];
			$summe = number_format ( $summe, 2, ".", "" );
			$this->summe_bk_abrechnung = $summe;
			return $summe;
		}
	}
	
	/* Summe der Heizkostenabrechnung */
	function summe_heizkostenabrechnung($mietvertrag_id, $monat, $jahr) {
		unset ( $this->summe_hk_abrechnung );
		$laenge = strlen ( $monat );
		if ($laenge == 1 && $monat < 10) {
			$monat = "0" . $monat;
		}
		$result = mysql_query ( "SELECT SUM(BETRAG) SUMME_HEIZKOSTEN FROM MIETENTWICKLUNG WHERE KOSTENTRAEGER_TYP='Mietvertrag' && KOSTENTRAEGER_ID = '$mietvertrag_id' && MIETENTWICKLUNG_AKTUELL = '1' && ( ENDE = '0000-00-00' OR DATE_FORMAT( ENDE, '%Y-%m' ) >= '$jahr-$monat' && DATE_FORMAT( ANFANG, '%Y-%m' ) <= '$jahr-$monat' ) && DATE_FORMAT( ANFANG, '%Y-%m' ) <= '$jahr-$monat' && KOSTENKATEGORIE LIKE 'Heizkostenabrechnung%'" );
		$numrows = mysql_numrows ( $result );
		if ($numrows < 1) {
			return false;
		} else {
			$row = mysql_fetch_assoc ( $result );
			$summe = $row ['SUMME_HEIZKOSTEN'];
			$summe = number_format ( $summe, 2, ".", "" );
			$this->summe_hk_abrechnung = $summe;
			return $summe;
		}
	}
	
	/* Summe der Wasserkostenabrechnung */
	function summe_wasserkostenabrechnung($mietvertrag_id, $monat, $jahr) {
		unset ( $this->summe_wasser_abrechnung );
		$laenge = strlen ( $monat );
		if ($laenge == 1 && $monat < 10) {
			$monat = "0" . $monat;
		}
		$result = mysql_query ( "SELECT SUM(BETRAG) SUMME_WASSERKOSTEN FROM MIETENTWICKLUNG WHERE KOSTENTRAEGER_TYP='Mietvertrag' && KOSTENTRAEGER_ID = '$mietvertrag_id' && MIETENTWICKLUNG_AKTUELL = '1' && ( ENDE = '0000-00-00' OR DATE_FORMAT( ENDE, '%Y-%m' ) >= '$jahr-$monat' && DATE_FORMAT( ANFANG, '%Y-%m' ) <= '$jahr-$monat' ) && DATE_FORMAT( ANFANG, '%Y-%m' ) <= '$jahr-$monat' && KOSTENKATEGORIE LIKE 'Wasserkostenabrechnung%'" );
		$numrows = mysql_numrows ( $result );
		if ($numrows < 1) {
			return false;
		} else {
			$row = mysql_fetch_assoc ( $result );
			$summe = $row ['SUMME_WASSERKOSTEN'];
			$summe = number_format ( $summe, 2, ".", "" );
			$this->summe_wasser_abrechnung = $summe;
			return $summe;
		}
	}
	function betriebskosten_monatlich($mietvertrag_id, $monat, $jahr) {
		$this->betriebskosten = 0.00;
		$result = mysql_query ( "SELECT BETRAG FROM `MIETENTWICKLUNG` WHERE `KOSTENTRAEGER_TYP` = 'Mietvertrag'  AND `KOSTENTRAEGER_ID` ='$mietvertrag_id' AND `KOSTENKATEGORIE` =  'Betriebskosten Vorauszahlung'  AND `MIETENTWICKLUNG_AKTUELL` = '1'  ORDER BY ANFANG DESC LIMIT 0 , 1) <= '$jahr-$monat'" );
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			$row = mysql_fetch_assoc ( $result );
			// echo "<hr><pre>";
			// print_r($row);
			// echo "</pre><hr>";
			$this->betriebskosten = $row ['BETRAG'];
		}
	}
	function heizkosten_monatlich($mietvertrag_id, $monat, $jahr) {
		$this->heizkosten = 0.00;
		$result = mysql_query ( "SELECT BETRAG  FROM `MIETENTWICKLUNG` WHERE `KOSTENTRAEGER_TYP` = 'Mietvertrag'  AND `KOSTENTRAEGER_ID` ='$mietvertrag_id' AND `KOSTENKATEGORIE` =  'Heizkosten Vorauszahlung'  AND `MIETENTWICKLUNG_AKTUELL` = '1'  ORDER BY ANFANG DESC LIMIT 0 , 1" );
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			$row = mysql_fetch_assoc ( $result );
			// echo "<hr><pre>";
			// print_r($row);
			// echo "</pre><hr>";
			$this->heizkosten = $row ['BETRAG'];
		}
	}
	function kaltmiete_monatlich($mietvertrag_id, $monat, $jahr) {
		$this->ausgangs_kaltmiete = 0.00;
		$result = mysql_query ( "SELECT SUM(BETRAG) AS SUMME_RATE FROM MIETENTWICKLUNG WHERE KOSTENTRAEGER_TYP='MIETVERTRAG' && KOSTENTRAEGER_ID = '$mietvertrag_id' && MIETENTWICKLUNG_AKTUELL = '1' && ( ENDE = '0000-00-00' OR DATE_FORMAT( ENDE, '%Y-%m' ) >= '$jahr-$monat' && DATE_FORMAT( ANFANG, '%Y-%m' ) <= '$jahr-$monat' ) && DATE_FORMAT( ANFANG, '%Y-%m' ) <= '$jahr-$monat' && (KOSTENKATEGORIE LIKE 'Miete kalt' OR KOSTENKATEGORIE LIKE 'MOD' OR KOSTENKATEGORIE LIKE 'MHG') ORDER BY ANFANG ASC" );
		// if($mietvertrag_id=='1379'){
		// echo "SELECT SUM(BETRAG) AS SUMME_RATE FROM MIETENTWICKLUNG WHERE KOSTENTRAEGER_TYP='MIETVERTRAG' && KOSTENTRAEGER_ID = '$mietvertrag_id' && MIETENTWICKLUNG_AKTUELL = '1' && ( ENDE = '0000-00-00' OR DATE_FORMAT( ENDE, '%Y-%m' ) >= '$jahr-$monat' && DATE_FORMAT( ANFANG, '%Y-%m' ) <= '$jahr-$monat' ) && DATE_FORMAT( ANFANG, '%Y-%m' ) <= '$jahr-$monat' && (KOSTENKATEGORIE LIKE 'Miete kalt' OR KOSTENKATEGORIE LIKE 'MOD' OR KOSTENKATEGORIE LIKE 'MHG') ORDER BY ANFANG ASC<br>";
		// die();
		// }
		$row = mysql_fetch_assoc ( $result );
		$summe = $row ['SUMME_RATE'];
		$this->ausgangs_kaltmiete = $summe;
	}
	function check_vz_anteilig($mietvertrag_id, $monat, $jahr) {
		$monat = sprintf ( '%02d', $monat );
		$result = mysql_query ( "SELECT SUM(BETRAG) AS SUMME_RATE FROM MIETENTWICKLUNG WHERE KOSTENTRAEGER_TYP='MIETVERTRAG' && KOSTENTRAEGER_ID = '$mietvertrag_id' && MIETENTWICKLUNG_AKTUELL = '1' && ( ENDE = '0000-00-00' OR DATE_FORMAT( ENDE, '%Y-%m' ) >= '$jahr-$monat' && DATE_FORMAT( ANFANG, '%Y-%m' ) <= '$jahr-$monat' ) && DATE_FORMAT( ANFANG, '%Y-%m' ) <= '$jahr-$monat' && KOSTENKATEGORIE = 'Nebenkosten VZ - Anteilig' ORDER BY ANFANG ASC" );
		// echo "SELECT SUM(BETRAG) AS SUMME_RATE FROM MIETENTWICKLUNG WHERE KOSTENTRAEGER_TYP='MIETVERTRAG' && KOSTENTRAEGER_ID = '$mietvertrag_id' && MIETENTWICKLUNG_AKTUELL = '1' && ( ENDE = '0000-00-00' OR DATE_FORMAT( ENDE, '%Y-%m' ) >= '$jahr-$monat' && DATE_FORMAT( ANFANG, '%Y-%m' ) <= '$jahr-$monat' ) && DATE_FORMAT( ANFANG, '%Y-%m' ) <= '$jahr-$monat' && KOSTENKATEGORIE = 'Nebenkosten VZ - Anteilig' ORDER BY ANFANG ASC";
		// die();
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			$row = mysql_fetch_assoc ( $result );
			$summe = nummer_komma2punkt ( nummer_punkt2komma ( $row ['SUMME_RATE'] ) );
			// die('SANEL '.$summe);
			if ($summe >= 0) {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
	function kaltmiete_monatlich_ink_vz($mietvertrag_id, $monat, $jahr) {
		$this->ausgangs_kaltmiete = 0.00;
		$result = mysql_query ( "SELECT SUM(BETRAG) AS SUMME_RATE FROM MIETENTWICKLUNG WHERE KOSTENTRAEGER_TYP='MIETVERTRAG' && KOSTENTRAEGER_ID = '$mietvertrag_id' && MIETENTWICKLUNG_AKTUELL = '1' && ( ENDE = '0000-00-00' OR DATE_FORMAT( ENDE, '%Y-%m' ) >= '$jahr-$monat' && DATE_FORMAT( ANFANG, '%Y-%m' ) <= '$jahr-$monat' ) && DATE_FORMAT( ANFANG, '%Y-%m' ) <= '$jahr-$monat' && (KOSTENKATEGORIE LIKE 'Miete kalt' OR KOSTENKATEGORIE LIKE 'MOD' OR KOSTENKATEGORIE LIKE 'MHG' OR KOSTENKATEGORIE LIKE 'Nebenkosten VZ - Anteilig') ORDER BY ANFANG ASC" );
		// echo "SELECT SUM(BETRAG) AS SUMME_RATE FROM MIETENTWICKLUNG WHERE KOSTENTRAEGER_TYP='MIETVERTRAG' && KOSTENTRAEGER_ID = '$mietvertrag_id' && MIETENTWICKLUNG_AKTUELL = '1' && ( ENDE = '0000-00-00' OR DATE_FORMAT( ENDE, '%Y-%m' ) >= '$jahr-$monat' && DATE_FORMAT( ANFANG, '%Y-%m' ) <= '$jahr-$monat' ) && DATE_FORMAT( ANFANG, '%Y-%m' ) <= '$jahr-$monat' && (KOSTENKATEGORIE LIKE 'Miete kalt' OR KOSTENKATEGORIE LIKE 'MOD' OR KOSTENKATEGORIE LIKE 'MHG') ORDER BY ANFANG ASC<br>";
		// die();
		$row = mysql_fetch_assoc ( $result );
		$summe = $row ['SUMME_RATE'];
		$this->ausgangs_kaltmiete = $summe;
	}
	function kaltmiete_monatlich_ohne_mod($mietvertrag_id, $monat, $jahr) {
		$this->ausgangs_kaltmiete = 0.00;
		$result = mysql_query ( "SELECT SUM(BETRAG) AS SUMME_RATE FROM MIETENTWICKLUNG WHERE KOSTENTRAEGER_TYP='MIETVERTRAG' && KOSTENTRAEGER_ID = '$mietvertrag_id' && MIETENTWICKLUNG_AKTUELL = '1' && ( ENDE = '0000-00-00' OR DATE_FORMAT( ENDE, '%Y-%m' ) >= '$jahr-$monat' && DATE_FORMAT( ANFANG, '%Y-%m' ) <= '$jahr-$monat' ) && DATE_FORMAT( ANFANG, '%Y-%m' ) <= '$jahr-$monat' && (KOSTENKATEGORIE LIKE 'Miete kalt' OR KOSTENKATEGORIE LIKE 'MHG') ORDER BY ANFANG ASC" );
		$row = mysql_fetch_assoc ( $result );
		$summe = $row ['SUMME_RATE'];
		$this->ausgangs_kaltmiete = $summe;
	}
	// ############## ZAHLUNGEN AUS MIETBUCHUNGEN
	// OK ZAHLUNGEN $result = mysql_query ("SELECT DATUM, KOSTENKATEGORIE, BETRAG FROM MIETBUCHUNGEN WHERE MIETVERTRAG_ID='$mietvertrag_id' && MIETBUCHUNGEN_AKTUELL = '1' && DATE_FORMAT( DATUM, '%Y-%m' ) = '$jahr-$monat'");
	
	/* Ausgabe aller Mietzahlungen für gewünschten Monat, Jahr als Array */
	function zahlungen_monatlich($mietvertrag_id, $monat, $jahr) {
		$result = mysql_query ( "SELECT * FROM GELD_KONTO_BUCHUNGEN WHERE KOSTENTRAEGER_TYP='MIETVERTRAG' &&  KOSTENTRAEGER_ID='$mietvertrag_id' && KONTENRAHMEN_KONTO='80001' && DATE_FORMAT( DATUM, '%Y-%m' ) = '$jahr-$monat'" );
		$my_arr = array ();
		while ( $row = mysql_fetch_assoc ( $result ) )
			$my_arr [] = $row;
		return $my_arr;
	}
	
	/* Ausgabe der Summe aller Zahlungen für gewünschten Monat, Jahr als String */
	function summe_zahlung_monatlich($mietvertrag_id, $monat, $jahr) {
		$zahlungen = $this->zahlungen_monatlich ( $mietvertrag_id, $monat, $jahr );
		$anzahl_elemente = count ( $zahlungen );
		$summe = 0;
		for($i = 0; $i < $anzahl_elemente; $i ++) {
			$summe = $summe + $zahlungen [$i] ['BETRAG'];
		}
		return $summe;
	}
	
	/* Prüfen ob diesen Monat Zahlbetrag zum MV gebucht wurde */
	function anzahl_zahlungsvorgaenge($mietvertrag_id) {
		$result = mysql_query ( "SELECT DATUM FROM GELD_KONTO_BUCHUNGEN WHERE KOSTENTRAEGER_TYP='Mietvertrag' && KOSTENTRAEGER_ID='$mietvertrag_id' && AKTUELL='1' && DATE_FORMAT( DATUM, '%Y-%m' ) = '" . $this->jahr_heute . "-" . $this->monat_heute . "'" );
		
		$numrows = mysql_numrows ( $result );
		return $numrows;
	}
	
	/* Prüfen ob diesen Monat Zahlbetrag aufs Geldkonto des Objektes gebucht wurde */
	function anzahl_zahlungsvorgaenge_objekt_konto($objekt_id) {
		$objekt_info = new objekt ();
		$objekt_info->get_objekt_geldkonto_nr ( $objekt_id );
		$objekt_kontonummer = $objekt_info->objekt_kontonummer;
		
		$result = mysql_query ( "SELECT DATUM, BETRAG, MIETVERTRAG_ID, BEMERKUNG FROM MIETE_ZAHLBETRAG WHERE KONTO='$objekt_kontonummer' && AKTUELL='1' && DATE_FORMAT( DATUM, '%Y-%m' ) = '" . $this->jahr_heute . "-" . $this->monat_heute . "'" );
		
		$numrows = mysql_numrows ( $result );
		return $numrows;
	}
	
	/* Prüfen ob diesen Monat überhaupt Zahlbeträge gebucht worden sind. */
	function anzahl_zahlungen_diesen_monat() {
		$result = mysql_query ( "SELECT DATUM, BETRAG, MIETVERTRAG_ID, BEMERKUNG FROM MIETE_ZAHLBETRAG WHERE AKTUELL='1' && DATE_FORMAT( DATUM, '%Y-%m' ) = '" . $this->jahr_heute . "-" . $this->monat_heute . "'" );
		$numrows = mysql_numrows ( $result );
		return $numrows;
	}
	
	/* Geldkontostand vom ausgewählten Objekt anzeigen */
	function geldkonto_stand_anzeigen($objekt_id) {
		$objekt_info = new objekt ();
		$objekt_info->get_objekt_geldkonto_nr ( $objekt_id );
		$objekt_kontonummer = $objekt_info->objekt_kontonummer;
		
		if ($objekt_kontonummer) {
			$kontostand = $this->kontostand_abfragen ( $objekt_kontonummer );
			if ($kontostand) {
				$kontostand = $this->nummer_punkt2komma ( $kontostand );
				echo " <b>Kontostand</b> $kontostand €</p> ";
			} else {
				echo "<b>Kontostand</b> 0,00 €</p> ";
			}
		}
	}
	
	/* Anzeigen des Mietkontostandes seit Einzug */
	function mietkontostand_anzeigen($mietvertrag_id) {
		$a = new miete ();
		$a->mietkonto_berechnung ( $_REQUEST ['mietvertrag_id'] );
		return $a->erg;
	}
	
	/* Anzeigen des Mietkontostandes seit Einzug neu */
	function mietkontostand_anzeigen_neu($mietvertrag_id) {
		$forderungen_gesamt = 0;
		$stand = 0;
		
		$monate_arr = $this->monate_seit_einzug_arr ( $mietvertrag_id );
		
		for($a = 0; $a < count ( $monate_arr ); $a ++) {
			$stand_aktuell = $this->monatsstand ( $mietvertrag_id, $monate_arr [$a] [jahr], $monate_arr [$a] [monat] );
			$stand = $stand + $stand_aktuell;
			// ######################
		}
		// $zahlungen_gesamt = monatlich_gezahlt($mietvertrag_id, $jahr, $monat);
		$stand = number_format ( $stand, 2, ".", "" );
		return $stand;
	}
	
	/* ermitteln des Kontostandes Mietkonto mit zeitraum */
	function mietkontostand_ausrechnen($mietvertrag_id) {
		include_once ("classes/mietzeit_class.php");
		$a = new miete ();
		$a->mietkonto_berechnung ( $mietvertrag_id );
		return $a->erg;
	}
	
	/* Ausgabe der Summe aller Zahlungen */
	function summe_aller_buchungen($mietvertrag_id) {
		$zahlungen = $this->alle_zahlungen_bisher ( $mietvertrag_id );
		$anzahl_elemente = count ( $zahlungen );
		$summe = 0;
		for($i = 0; $i < $anzahl_elemente; $i ++) {
			$summe = $summe + $zahlungen [$i] ['BETRAG'];
		}
		return $summe;
	}
	
	/* Ausgabe des Kontostandes */
	function kontostand_abfragen($geld_konto_nr) {
		$result = mysql_query ( "SELECT SUM(BETRAG) AS KONTOSTAND FROM GELD_KONTO_BUCHUNGEN WHERE GELDKONTO_ID='$geld_konto_nr' && AKTUELL='1'" );
		
		while ( $row = mysql_fetch_assoc ( $result ) )
			$my_arr [] = $row;
		return $my_arr [0] [KONTOSTAND];
	}
	
	/* Ausgabe des Kontostandes aller Geldkonten */
	function kontostand_abfragen_gesamt() {
		$result = mysql_query ( "SELECT SUM(BETRAG) AS KONTOSTAND FROM GELD_KONTO_BUCHUNGEN WHERE AKTUELL='1'" );
		
		while ( $row = mysql_fetch_assoc ( $result ) )
			$my_arr [] = $row;
		return $my_arr [0] [KONTOSTAND];
	}
	
	/* Ausgabe der Summe aller Zahlbeträge */
	function summe_aller_zahlbetraege($mietvertrag_id) {
		$result = mysql_query ( "SELECT SUM(BETRAG) AS SUMME_ZAHLBETRAG FROM GELD_KONTO_BUCHUNGEN WHERE KOSTENTRAEGER_TYP='Mietvertrag' && KOSTENTRAEGER_ID='$mietvertrag_id' && AKTUELL='1'" );
		
		$row = mysql_fetch_assoc ( $result );
		return $row ['SUMME_ZAHLBETRAG'];
	}
	
	/* Ausgabe der Summe aller Zahlbeträge bis monat */
	function summe_aller_zahlbetraege_bis_monat($mietvertrag_id, $monat, $jahr, $kostenkonto) {
		$result = mysql_query ( "SELECT SUM(BETRAG) AS SUMME_ZAHLBETRAG FROM GELD_KONTO_BUCHUNGEN WHERE KOSTENTRAEGER_TYP='Mietvertrag' && KOSTENTRAEGER_ID='$mietvertrag_id' && KONTENRAHMEN_KONTO='$kostenkonto' && AKTUELL='1' && DATE_FORMAT(DATUM, '%Y-%m')<='$jahr-$monat'" );
		
		$row = mysql_fetch_assoc ( $result );
		return $row ['SUMME_ZAHLBETRAG'];
	}
	
	/* Alle Zahlbeträge in Array */
	function alle_zahlbetraege_arr($mietvertrag_id) {
		$result = mysql_query ( "SELECT DATUM, BETRAG, MIETVERTRAG_ID, BEMERKUNG FROM GELD_KONTO_BUCHUNGEN WHERE KOSTENTRAEGER_TYP='Mietvertrag' && KOSTENTRAEGER_ID='$mietvertrag_id' && AKTUELL='1' ORDER BY DATUM ASC" );
		while ( $row = mysql_fetch_assoc ( $result ) )
			$my_arr [] = $row;
		return $my_arr;
	}
	
	/* Alle Zahlbeträge vom Monat in Array */
	function alle_zahlbetraege_monat_arr($mietvertrag_id, $monat, $jahr) {
		if ($monat < 10) {
			$monat = "0" . $monat;
		}
		$result = mysql_query ( "SELECT DATUM, BETRAG, MIETVERTRAG_ID, BEMERKUNG FROM GELD_KONTO_BUCHUNGEN WHERE KOSTENTRAEGER_TYP='Mietvertrag' && KOSTENTRAEGER_ID='$mietvertrag_id' && AKTUELL='1' && DATE_FORMAT( DATUM, '%Y-%m' ) = '$jahr-$monat' && BEMERKUNG NOT LIKE 'Saldo Vortrag Vorverwaltung' ORDER BY DATUM ASC" );
		
		while ( $row = mysql_fetch_assoc ( $result ) )
			$my_arr [] = $row;
		return $my_arr;
	}
	
	/* Alle Zahlbeträge vom Monat in Array */
	function zahlbetraege_im_monat_arr($mietvertrag_id, $monat, $jahr, $kostenkonto = '80001') {
		if ($kostenkonto == '') {
			$ko_string = '';
		} else {
			$ko_string = " && KONTENRAHMEN_KONTO='$kostenkonto '";
		}
		
		$laenge = strlen ( $monat );
		if ($laenge == 1 && $monat < 10) {
			$monat = "0" . $monat;
		}
		$mv = new mietvertraege ();
		$mv->get_mietvertrag_infos_aktuell ( $mietvertrag_id );
		$gk = new geldkonto_info ();
		$gk->geld_konto_ermitteln ( 'Objekt', $mv->objekt_id );
		if (! empty ( $gk->geldkonto_id )) {
			$result = mysql_query ( "SELECT DATUM, BETRAG, VERWENDUNGSZWECK AS BEMERKUNG FROM GELD_KONTO_BUCHUNGEN WHERE  GELDKONTO_ID='$gk->geldkonto_id' $ko_string && KOSTENTRAEGER_TYP='Mietvertrag' && KOSTENTRAEGER_ID='$mietvertrag_id' && AKTUELL='1' && DATE_FORMAT( DATUM, '%Y-%m' ) = '$jahr-$monat' ORDER BY DATUM ASC" );
		} else {
			die ( 'Kein Geldkonto für das Objekt hinterlegt' );
			$result = mysql_query ( "SELECT DATUM, BETRAG, VERWENDUNGSZWECK AS BEMERKUNG FROM GELD_KONTO_BUCHUNGEN && KOSTENTRAEGER_TYP='Mietvertrag' && KOSTENTRAEGER_ID='$mietvertrag_id' && AKTUELL='1' && DATE_FORMAT( DATUM, '%Y-%m' ) = '$jahr-$monat'" );
		}
		$numrows = mysql_numrows ( $result );
		if ($numrows > 0) {
			while ( $row = mysql_fetch_assoc ( $result ) )
				$my_arr [] = $row;
			return $my_arr;
		} else {
			return false;
		}
		unset ( $mv );
	}
	function summe_aller_zahlungen_monat($mietvertrag_id, $monat, $jahr) {
		unset ( $this->summe_z_im_monat );
		$laenge = strlen ( $monat );
		if ($laenge == 1 && $monat < 10) {
			$monat = "0" . $monat;
		}
		
		$result = mysql_query ( "SELECT SUM(BETRAG) AS BETRAG FROM GELD_KONTO_BUCHUNGEN WHERE KOSTENTRAEGER_TYP='Mietvertrag' && KOSTENTRAEGER_ID='$mietvertrag_id' && AKTUELL='1' && DATE_FORMAT( DATUM, '%Y-%m' ) = '$jahr-$monat'" );
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			$row = mysql_fetch_assoc ( $result );
			// $betrag = $row['BETRAG'];
			$this->summe_z_im_monat = $row ['BETRAG'];
			// return $this->summe_z_im_monat;
		} else {
			return '0.00';
		}
	}
	function summer_aller_ueberschuesse($mietvertrag_id) {
		$result = mysql_query ( "SELECT SUM(BETRAG) AS SUMME_UEBERSCHUSS FROM MIETBUCHUNGEN WHERE MIETVERTRAG_ID='$mietvertrag_id' && KOSTENKATEGORIE='UEBERSCHUSS' && MIETBUCHUNGEN_AKTUELL='1'" );
		
		while ( $row = mysql_fetch_assoc ( $result ) )
			$my_arr [] = $row;
		return $my_arr [0] [SUMME_UEBERSCHUSS];
	}
	
	// Summe Vortrag Vorverwaltung
	function saldo_vortrag_vorverwaltung($mietvertrag_id) {
		$my_arr = '';
		$result = mysql_query ( "SELECT BETRAG AS SALDO_VORVERWALTUNG FROM MIETENTWICKLUNG WHERE KOSTENTRAEGER_TYP='Mietvertrag' && KOSTENTRAEGER_ID = '$mietvertrag_id' && KOSTENKATEGORIE = 'Saldo Vortrag Vorverwaltung' && MIETENTWICKLUNG_AKTUELL = '1' ORDER BY ANFANG DESC LIMIT 0,1" );
		while ( $row = mysql_fetch_assoc ( $result ) )
			$my_arr [] = $row;
		if (is_array ( $my_arr )) {
			$saldo = $my_arr [0] ['SALDO_VORVERWALTUNG'];
			return $saldo;
		}
	}
	
	// DATUM Vortrag Vorverwaltung
	function datum_saldo_vortrag_vorverwaltung($mietvertrag_id) {
		unset ( $this->datum_vv );
		$result = mysql_query ( "SELECT ANFANG AS DATUM FROM MIETENTWICKLUNG WHERE KOSTENTRAEGER_TYP='Mietvertrag' && KOSTENTRAEGER_ID = '$mietvertrag_id' && KOSTENKATEGORIE = 'Saldo Vortrag Vorverwaltung' && MIETENTWICKLUNG_AKTUELL = '1' ORDER BY ANFANG DESC LIMIT 0,1" );
		
		$row = mysql_fetch_assoc ( $result );
		$this->datum_saldo_vv = $row ['DATUM'];
		return $row ['DATUM'];
	}
	
	// DATUM ablauf Mietdefinition
	function datum_ablauf_mietdefinition($mietvertrag_id) {
		$result = mysql_query ( "SELECT ENDE AS DATUM FROM MIETENTWICKLUNG WHERE KOSTENTRAEGER_TYP='Mietvertrag' && KOSTENTRAEGER_ID = '$mietvertrag_id'  && MIETENTWICKLUNG_AKTUELL = '1' ORDER BY DATUM DESC LIMIT 0,1" );
		
		$row = mysql_fetch_assoc ( $result );
		return $row ['DATUM'];
	}
	
	/* NEUE FUNKTIONEN OPTIMIERTE MYSQL ABFRAGEN */
	/*
	 * function check_betriebskosten($mietvertrag_id, $jahr, $monat){
	 * if($monat < 10){
	 * $monat = "0".$monat;
	 * }
	 *
	 * $result = mysql_qu
	 *
	 *
	 * ery ("SELECT SUM(BETRAG) AS BETRAG FROM MIETENTWICKLUNG WHERE MIETVERTRAG_ID = '$mietvertrag_id' && MIETENTWICKLUNG_AKTUELL = '1' && ( ENDE = '0000-00-00' OR DATE_FORMAT( ENDE, '%Y-%m' ) >= '$jahr-$monat' && DATE_FORMAT( ANFANG, '%Y-%m' ) <= '$jahr-$monat' ) && DATE_FORMAT( ANFANG, '%Y-%m' ) <= '$jahr-$monat'");
	 *
	 * $row = mysql_fetch_assoc($result);
	 * return $row['BETRAG'];
	 *
	 * }
	 *
	 *
	 * /* NEUE FUNKTIONEN OPTIMIERTE MYSQL ABFRAGEN
	 */
	function monatliche_miete($mietvertrag_id, $jahr, $monat) {
		
		// echo "JAHR $jahr MONAT: $monat $mietvertrag_id";
		$result = mysql_query ( "SELECT SUM(BETRAG) AS BETRAG FROM MIETENTWICKLUNG WHERE MIETVERTRAG_ID = '$mietvertrag_id' && MIETENTWICKLUNG_AKTUELL = '1' && ( ENDE = '0000-00-00' OR DATE_FORMAT( ENDE, '%Y-%m' ) >= '$jahr-$monat' && DATE_FORMAT( ANFANG, '%Y-%m' ) <= '$jahr-$monat' ) && DATE_FORMAT( ANFANG, '%Y-%m' ) <= '$jahr-$monat'" );
		$row = mysql_fetch_assoc ( $result );
		return $row ['BETRAG'];
	}
	function monatlich_gezahlt($mietvertrag_id, $jahr, $monat) {
		$result = "SELECT SUM(BETRAG) AS BETRAG FROM MIETE_ZAHLBETRAG WHERE MIETVERTRAG_ID = '$mietvertrag_id' && AKTUELL = '1' && DATE_FORMAT( DATUM, '%Y-%m' ) = '$jahr-$monat'";
		$row1 = mysql_fetch_assoc ( $result );
		return $row1 ['BETRAG'];
	}
	function array_anzeigen($array) {
		echo "<pre>";
		print_r ( $array );
		echo "</pre>";
	}
	function alle_zahlungen_bisher($mietvertrag_id) {
		$result = mysql_query ( "SELECT DISTINCT MIETBUCHUNGEN.MIETVERTRAG_ID, MIETBUCHUNGEN.DATUM, MIETBUCHUNGEN.KOSTENKATEGORIE, MIETBUCHUNGEN.BETRAG, MIETBUCHUNGEN.BUCHUNGSNUMMER, MIETE_ZAHLBETRAG.BETRAG AS ZAHLBETRAG, MIETE_ZAHLBETRAG.DATUM AS ZAHLDATUM
FROM MIETBUCHUNGEN, MIETE_ZAHLBETRAG
WHERE MIETBUCHUNGEN.MIETVERTRAG_ID = '$mietvertrag_id' && MIETBUCHUNGEN.MIETBUCHUNGEN_AKTUELL = '1' && MIETBUCHUNGEN.BUCHUNGSNUMMER = MIETE_ZAHLBETRAG.BUCHUNGSNUMMER
ORDER BY DATUM ASC " );
		
		while ( $row = mysql_fetch_assoc ( $result ) )
			$my_arr [] = $row;
		return $my_arr;
	}
	function alle_buchungen_anzeigen($mietvertrag_id) {
		$this->datum_heute;
		$this->tag_heute;
		$this->monat_heute;
		$this->jahr_heute;
		$zahlungen_diesen_monat_arr = $this->alle_zahlungen_bisher ( $mietvertrag_id );
		if (! is_array ( $zahlungen_diesen_monat_arr )) {
			echo "<div class=aktuelle_buchungen><b>ALLE BISHERIGEN BUCHUNGEN UND ZAHLUNGEN ZUM MV: $mietvertrag_id<br>";
			echo "Keine Zahlungen und Buchungen bezogen auf MV: $mietvertrag_id!";
			echo "</div>";
			$error = TRUE;
		} else {
			// $this->array_anzeigen($zahlungen_diesen_monat_arr);
			
			echo "<div class=aktuelle_buchungen><b>ALLE BISHERIGEN BUCHUNGEN UND ZAHLUNGEN ZUM MV: $mietvertrag_id</b><br>";
			echo "<table class=aktuelle_buchungen>";
			echo "<tr class=tabelle_ueberschrift_mietkontenblatt><td>B-NR</td><td>ÜBERWIESEN AM</td><td>ZAHLBETRAG</td><td>GEBUCHT AM</td><td>TEILBETRAG</td><td>KOSTENKATEGORIE</td></tr>";
			for($i = 0; $i < count ( $zahlungen_diesen_monat_arr ); $i ++) {
				echo "<tr>";
				$buchungsdatum = $this->date_mysql2german ( $zahlungen_diesen_monat_arr [$i] [DATUM] );
				echo "<td><b>" . $zahlungen_diesen_monat_arr [$i] ['BUCHUNGSNUMMER'] . "</b></td>";
				$zahldatum = $this->date_mysql2german ( $zahlungen_diesen_monat_arr [$i] [ZAHLDATUM] );
				echo "<td>" . $zahldatum . "</td>";
				echo "<td><b>" . $zahlungen_diesen_monat_arr [$i] ['ZAHLBETRAG'] . " €</b></td>";
				echo "<td>" . $buchungsdatum . "</td>";
				echo "<td><b>" . $zahlungen_diesen_monat_arr [$i] ['BETRAG'] . " €</b></td>";
				echo "<td>" . $zahlungen_diesen_monat_arr [$i] ['KOSTENKATEGORIE'] . "</td>";
				echo "</tr>";
			}
			$summe_zahlungen = $this->summe_aller_buchungen ( $mietvertrag_id );
			$summe_zahlbetraege = $this->summe_aller_zahlbetraege ( $mietvertrag_id );
			$summe_ueberschusse = $this->summer_aller_ueberschuesse ( $mietvertrag_id );
			echo "<tr><td colspan=3><b>Summe Zahlbeträge: $summe_zahlbetraege €</b><td colspan=2><b> Gebuchte Summe: $summe_zahlungen €</b></td><td><b>Summe Überschuss: $summe_ueberschusse €</b></td></tr>";
			echo "</table>";
			echo "</div>";
			return $zahlungen_diesen_monat_arr;
		}
	}
	
	/* Funktion zur Ermittlung von Infos und Details zu einer Buchungsnummer */
	function buchungsnummer_infos($bnr) {
		$miete_zahlbetrag_arr = $this->details_von_buchungsnummer ( $bnr );
		$mietvertrag_id = $miete_zahlbetrag_arr [0] ['mietvertrag_id'];
		$zahlbetrag = $miete_zahlbetrag_arr [0] ['BETRAG'];
		$buchungsdatum = $miete_zahlbetrag_arr [0] [DATUM];
		$bemerkung = $miete_zahlbetrag_arr [0] [BEMERKUNG];
		$konto = $miete_zahlbetrag_arr [0] [KONTO];
		$mietvertrag_info = new mietvertrag ();
		$personen_ids_mieter = $mietvertrag_info->get_personen_ids_mietvertrag ( $mietvertrag_id );
		// $this->array_anzeigen($personen_ids_mieter);
		$einheit_id = $mietvertrag_info->get_einheit_id_von_mietvertrag ( $mietvertrag_id );
		
		$einheit_kurzname = $this->einheit_kurzname_finden ( $einheit_id );
		$haus_objekt_info = new einheit ();
		$haus_objekt_info->get_einheit_haus ( $einheit_id );
		echo "<h1>Objekt " . $haus_objekt_info->objekt_name . " " . $haus_objekt_info->haus_strasse . " " . $haus_objekt_info->haus_nummer . "</h1> ";
		echo "<b>Mieter: ";
		$person_infos = new person ();
		for($a = 0; $a < count ( $personen_ids_mieter ); $a ++) {
			$person_infos->get_person_infos ( $personen_ids_mieter [$a] [PERSON_MIETVERTRAG_PERSON_ID] );
			echo "" . $person_infos->person_vorname . " " . $person_infos->person_nachname . " ";
		}
		echo "</b><br>";
		
		echo "<b>Einheit:$einheit_kurzname</b><br>";
		echo "Buchungsnummer:$bnr<br>";
		$zahlbetrag = $this->nummer_punkt2komma ( $zahlbetrag );
		echo "Zahlbetrag: $zahlbetrag €<br>";
		$buchungsdatum = $this->date_mysql2german ( $buchungsdatum );
		echo "Buchungsdatum $buchungsdatum<br>";
		echo "Konto: $konto<br>";
		echo "Buchungsnotiz:<br> $bemerkung<br>";
		$aufteilung_arr = $this->buchungsaufteilung_als_array ( $bnr );
		$this->erstelle_formular ( "Folgende interne Buchungen werden auch storniert", NULL );
		$this->hidden_feld ( "BUCHUNGSNUMMER", "$bnr" );
		for($a = 0; $a < count ( $aufteilung_arr ); $a ++) {
			$betrag = $this->nummer_punkt2komma ( $aufteilung_arr [$a] ['BETRAG'] );
			echo "<br>";
			echo "<b>" . $aufteilung_arr [$a] ['KOSTENKATEGORIE'] . " ";
			echo "$betrag €</b>";
			$this->hidden_feld ( "MIETBUCHUNGEN[]", "" . $aufteilung_arr [$a] [MIETBUCHUNG_DAT] . "" );
		}
		echo "<br><br>";
		$this->hidden_feld ( "schritt", "stornierung_in_db" );
		$this->send_button ( "BUCHUNG_STORNIEREN", "Stornieren" );
		$this->ende_formular ();
	}
	
	/* Funktion Buchungsnummerdetails als Array */
	function details_von_buchungsnummer($bnr) {
		$result = mysql_query ( "SELECT * FROM MIETE_ZAHLBETRAG WHERE   BUCHUNGSNUMMER='$bnr' && AKTUELL='1'  ORDER BY BUCHUNGSNUMMER DESC LIMIT 0,1" );
		while ( $row = mysql_fetch_assoc ( $result ) )
			$my_array [] = $row;
		return $my_array;
	}
	
	/* Buchung Zahlbetrag stornieren */
	function miete_zahlbetrag_stornieren($bnr) {
		mysql_query ( "UPDATE MIETE_ZAHLBETRAG SET AKTUELL='0' WHERE   BUCHUNGSNUMMER='$bnr' && AKTUELL='1'" );
		
		/* Da nur Aktuell von 1 auf 0 gesetzt, ergibt es im Protokoll die gleiche Zeilennummer bzw. Buchungsnummer */
		protokollieren ( 'MIETE_ZAHLBETRAG', $bnr, $bnr );
		echo "Buchung $bnr - $last_dat storniert <br>";
	}
	
	/* Funktion Aufteilung einer Buchung als Array */
	function buchungsaufteilung_als_array($bnr) {
		$result = mysql_query ( "SELECT MIETBUCHUNG_DAT, BETRAG, KOSTENKATEGORIE FROM MIETBUCHUNGEN WHERE   BUCHUNGSNUMMER='$bnr' && MIETBUCHUNGEN_AKTUELL='1' ORDER BY BUCHUNGSNUMMER ASC" );
		while ( $row = mysql_fetch_assoc ( $result ) )
			$my_array [] = $row;
		return $my_array;
	}
	
	/* Interne Buchungen der Zahlbetragaufteilung stornieren */
	function mietbuchung_stornieren_intern($mietbuchung_dat) {
		mysql_query ( "UPDATE MIETBUCHUNGEN SET MIETBUCHUNGEN_AKTUELL='0' WHERE   MIETBUCHUNG_DAT='$mietbuchung_dat' && MIETBUCHUNGEN_AKTUELL='1'" );
		/* Da nur Aktuell von 1 auf 0 gesetzt, ergibt es im Protokoll die gleiche Zeilennummer bzw. Mietbuchungsdat */
		protokollieren ( 'MIETBUCHUNGEN', $mietbuchung_dat, $mietbuchung_dat );
		echo "Interne Buchung $mietbuchung_dat inaktiv <br>";
	}
	
	/* Letzte Buchungsnummer vom gebuchten Zahlbetrag zu Mietvertrag finden */
	function letzte_dat_miete_zahlbetrag($mietvertrag_id) {
		$result = mysql_query ( "SELECT BUCHUNGSNUMMER FROM MIETE_ZAHLBETRAG WHERE MIETVERTRAG_ID = '$mietvertrag_id' && AKTUELL = '1' ORDER BY BUCHUNGSNUMMER DESC LIMIT 0,1" );
		$row = mysql_fetch_assoc ( $result );
		return $row ['BUCHUNGSNUMMER'];
	}
	function aktuelle_buchungen_anzeigen($mietvertrag_id) {
		$this->datum_heute;
		$this->tag_heute;
		$this->monat_heute;
		$this->jahr_heute;
		$zahlungen_diesen_monat_arr = $this->zahlungen_monatlich ( $mietvertrag_id, $this->monat_heute, $this->jahr_heute );
		if (! is_array ( $zahlungen_diesen_monat_arr )) {
			echo "<div class=aktuelle_buchungen><b>AKTUELLE BUCHUNGEN AUS $mietvertrag_id<br>";
			echo "Keine aktuellen Zahlungen in diesem Monat!";
			echo "</div>";
			$error = TRUE;
		} else {
			// $this->array_anzeigen($zahlungen_diesen_monat_arr);
			
			echo "<div class=aktuelle_buchungen><b>AKTUELLE BUCHUNGEN 				$this->monat_heute $this->jahr_heute</b><br>";
			echo "<table class=aktuelle_buchungen>";
			echo "<tr class=tabelle_ueberschrift_mietkontenblatt><td>B-NR</td><td>ÜBERWIESEN AM</td><td>ZAHLBETRAG</td><td>GEBUCHT AM</td><td>TEILBETRAG</td><td>KOSTENKATEGORIE</td></tr>";
			for($i = 0; $i < count ( $zahlungen_diesen_monat_arr ); $i ++) {
				echo "<tr>";
				$buchungsdatum = $this->date_mysql2german ( $zahlungen_diesen_monat_arr [$i] [DATUM] );
				echo "<td><b>" . $zahlungen_diesen_monat_arr [$i] ['BUCHUNGSNUMMER'] . "</b></td>";
				$zahldatum = $this->date_mysql2german ( $zahlungen_diesen_monat_arr [$i] [ZAHLDATUM] );
				echo "<td>" . $zahldatum . "</td>";
				echo "<td><b>" . $zahlungen_diesen_monat_arr [$i] ['ZAHLBETRAG'] . " €</b></td>";
				echo "<td>" . $buchungsdatum . "</td>";
				echo "<td><b>" . $zahlungen_diesen_monat_arr [$i] ['BETRAG'] . " €</b></td>";
				echo "<td>" . $zahlungen_diesen_monat_arr [$i] ['KOSTENKATEGORIE'] . "</td>";
				echo "</tr>";
			}
			$summe_zahlungen = $this->summe_zahlung_monatlich ( $mietvertrag_id, $this->monat_heute, $this->jahr_heute );
			echo "<tr><td colspan=5><b> Summe: $summe_zahlungen €</b></td></tr>";
			echo "</table>";
			echo "</div>";
			return $zahlungen_diesen_monat_arr;
		}
	}
	function aktuelle_buchungen_array($mietvertrag_id) {
		$this->datum_heute;
		$this->tag_heute;
		$this->monat_heute;
		$this->jahr_heute;
		$zahlungen_diesen_monat_arr = $this->zahlungen_monatlich ( $mietvertrag_id, $this->monat_heute, $this->jahr_heute );
		return $zahlungen_diesen_monat_arr;
	}
	function aktuelle_forderungen_anzeigen($mietvertrag_id) {
		$this->datum_heute;
		$this->tag_heute;
		$this->monat_heute;
		$this->jahr_heute;
		$forderungen_diesen_monat_arr = $this->forderung_monatlich ( $mietvertrag_id, $this->monat_heute, $this->jahr_heute );
		if (! is_array ( $forderungen_diesen_monat_arr )) {
			echo "<div class=aktuelle_forderungen><b>AKTUELLE FORDERUNGEN AUS  $mietvertrag_id<br>";
			echo "Keine Forderungen in diesem Monat!";
			echo "</div>";
			$error = TRUE;
		} else {
			// $this->array_anzeigen($forderungen_diesen_monat_arr);
			
			echo "<div class=aktuelle_forderungen><b>AKTUELLE FORDERUNGEN aus $mietvertrag_id 				$this->monat_heute $this->jahr_heute</b><br>";
			echo "<table class=aktuelle_forderungen>";
			echo "<tr class=tabelle_ueberschrift_mietkontenblatt><td>MV</td><td>ANFANG</td><td>ENDE</td><td>KOSTENKATEGORIE</td><td>BETRAG</td></tr>";
			for($i = 0; $i < count ( $forderungen_diesen_monat_arr ); $i ++) {
				echo "<tr>";
				echo "<td>" . $forderungen_diesen_monat_arr [$i] ['mietvertrag_id'] . "</td>";
				$anfangsdatum = $this->date_mysql2german ( $forderungen_diesen_monat_arr [$i] [ANFANG] );
				echo "<td>" . $anfangsdatum . "</td>";
				$endedatum = $this->date_mysql2german ( $forderungen_diesen_monat_arr [$i] [ENDE] );
				echo "<td>" . $endedatum . "</td>";
				
				echo "<td>" . $forderungen_diesen_monat_arr [$i] ['KOSTENKATEGORIE'] . "</td>";
				echo "<td><b>" . $forderungen_diesen_monat_arr [$i] ['BETRAG'] . " €</b></td>";
				echo "</tr>";
			}
			$summe_forderungen = $this->summe_forderung_monatlich ( $mietvertrag_id, $this->monat_heute, $this->jahr_heute );
			echo "<tr><td colspan=6><b> Summe: $summe_forderungen €</b></td></tr>";
			echo "</table>";
			echo "</div>";
			return $forderungen_diesen_monat_arr;
		}
	}
	function aktuelle_forderungen_array($mietvertrag_id) {
		$this->datum_heute;
		$this->tag_heute;
		$this->monat_heute;
		$this->jahr_heute;
		$forderungen_diesen_monat_arr = $this->forderung_monatlich ( $mietvertrag_id, $this->monat_heute, $this->jahr_heute );
		// echo "$this->monat_heute $this->jahr_heute";
		return $forderungen_diesen_monat_arr;
	}
	function buchung_zeitraum($mietvertrag_id, $von_datum, $bis_datum) {
		$this->datum_heute;
		$result = mysql_query ( "SELECT MIETVERTRAG_ID, DATUM, KOSTENKATEGORIE, BETRAG FROM MIETBUCHUNGEN WHERE MIETVERTRAG_ID='$mietvertrag_id' && MIETBUCHUNGEN_AKTUELL = '1' && DATUM BETWEEN '$von_datum' AND '$bis_datum' ORDER BY DATUM ASC" );
		while ( $row = mysql_fetch_assoc ( $result ) )
			$buchungen_arr [] = $row;
		
		$this->array_anzeigen ( $buchungen_arr );
		
		if (! is_array ( $buchungen_arr )) {
			echo "Keine aktuellen Zahlungen in diesem Monat!";
			$error = TRUE;
		} else {
			// $this->array_anzeigen($zahlungen_diesen_monat_arr);
			
			echo "<div class=aktuelle_buchungen><b>AKTUELLE BUCHUNGEN 				$von_datum bis $bis_datum</b><br>";
			echo "<table class=aktuelle_buchungen>";
			echo "<tr class=tabelle_ueberschrift><td>DATUM</td><td>KOSTENKATEGORIE</td><td>BETRAG</td></tr>";
			$summe_zahlungen = 0;
			for($i = 0; $i < count ( $buchungen_arr ); $i ++) {
				echo "<tr>";
				$buchungsdatum = $this->date_mysql2german ( $buchungen_arr [$i] [DATUM] );
				echo "<td>" . $buchungsdatum . "</td>";
				echo "<td>" . $buchungen_arr [$i] ['KOSTENKATEGORIE'] . "</td>";
				echo "<td>" . $buchungen_arr [$i] ['BETRAG'] . " €</td>";
				$summe_zahlungen = $summe_zahlungen + $buchungen_arr [$i] ['BETRAG'];
				echo "</tr>";
			}
			echo "<tr><td colspan=3><b> Summe: $summe_zahlungen €</b></td></tr>";
			echo "</table>";
		}
	}
	function mieter_mietkonto_stand($mietvertrag_id, $einzugs_monat, $einzugs_jahr) {
		$summe_aller_zahlbetraege = $this->summe_aller_zahlbetraege ( $mietvertrag_id );
		$zeitraum = new zeitraum ();
		$aktueller_monat = date ( "m" );
		$aktuelles_jahr = date ( "Y" );
		$monate_arr = $zeitraum->zeitraum_generieren ( $einzugs_monat, $einzugs_jahr, $aktueller_monat, $aktuelles_jahr );
		
		$forderungen_insgesamt = 0;
		for($a = 0; $a < count ( $monate_arr ); $a ++) {
			$berechnungs_monat = $monate_arr [$a] [monat];
			$berechnungs_jahr = $monate_arr [$a] [jahr];
			$summe_forderung_monatlich = $this->summe_forderung_monatlich ( $mietvertrag_id, $berechnungs_monat, $berechnungs_jahr );
			$forderungen_insgesamt = $forderungen_insgesamt + $summe_forderung_monatlich;
		}
		
		$mietkonto_stand = $summe_aller_zahlbetraege - $forderungen_insgesamt;
		return $mietkonto_stand;
	}
	function summe_uebersicht_aufteilung($mietvertrag_id, $von_datum, $bis_datum) {
		$result = mysql_query ( "SELECT MIETVERTRAG_ID, KOSTENKATEGORIE, SUM(BETRAG) AS GESAMT_BETRAG FROM MIETBUCHUNGEN WHERE MIETVERTRAG_ID='$mietvertrag_id' && MIETBUCHUNGEN_AKTUELL = '1' && DATUM BETWEEN '$von_datum' AND '$bis_datum' GROUP BY KOSTENKATEGORIE ORDER BY GESAMT_BETRAG DESC" );
		while ( $row = mysql_fetch_assoc ( $result ) )
			$buchungen_arr [] = $row;
			// $this->array_anzeigen($buchungen_arr);
			/*
		 * echo "<div class=aktuelle_buchungen><b>AKTUELLE AUFTEILUNG $von_datum bis $bis_datum</b><br>";
		 * echo "<table class=aktuelle_buchungen>";
		 * echo "<tr class=tabelle_ueberschrift><td>KOSTENKATEGORIE</td><td>BETRAG</td></tr>";
		 * $summe_zahlungen = 0;
		 * for($i=0;$i<count($buchungen_arr);$i++){
		 * echo "<tr>";
		 * $buchungsdatum = $this->date_mysql2german($buchungen_arr[$i][DATUM]);
		 * echo "<td>".$buchungen_arr[$i]['KOSTENKATEGORIE']."</td>";
		 * echo "<td>".$buchungen_arr[$i][GESAMT_BETRAG]." €</td>";
		 * $summe_zahlungen = $summe_zahlungen + $buchungen_arr[$i][GESAMT_BETRAG];
		 * echo "</tr>";
		 * }
		 * echo "<tr><td colspan=2><b> Summe: $summe_zahlungen €</b></td></tr>";
		 * echo "</table>";
		 * echo "</div>";
		 */
		return $buchungen_arr;
	}
	function einheit_kurzname_finden($einheit_id) {
		$db_abfrage = "SELECT EINHEIT_KURZNAME FROM EINHEIT where EINHEIT_ID='$einheit_id' && EINHEIT_AKTUELL='1'";
		$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		while ( list ( $EINHEIT_KURZNAME ) = mysql_fetch_row ( $resultat ) )
			return $EINHEIT_KURZNAME;
	}
	function buchung_exists($mietvertrag_id, $monat, $jahr) {
		$result = mysql_query ( "SELECT MIETVERTRAG_ID, DATUM, KOSTENKATEGORIE, BETRAG FROM MIETBUCHUNGEN WHERE MIETVERTRAG_ID='$mietvertrag_id' && MIETBUCHUNGEN_AKTUELL = '1' && DATE_FORMAT( DATUM, '%Y-%m' ) = '$jahr-$monat' ORDER BY DATUM ASC" );
		
		$numrows = mysql_numrows ( $result );
		return $numrows;
	}
	function to_do_liste() {
		$result = mysql_query ( "SELECT MIETVERTRAG_ID, EINHEIT_ID FROM MIETVERTRAG WHERE MIETVERTRAG_BIS>='$this->datum_heute' OR MIETVERTRAG_BIS='0000-00-00' && MIETVERTRAG_AKTUELL = '1' ORDER BY EINHEIT_ID ASC" );
		while ( $row = mysql_fetch_assoc ( $result ) )
			$mv_arr [] = $row;
			// $mv_arr = array_unique($mv_arr);
			
		// $this->array_anzeigen($mv_arr);
		for($i = 0; $i < count ( $mv_arr ); $i ++) {
			$mietvertrag_id = $mv_arr [$i] ['mietvertrag_id'];
			$buchungen_existieren = $this->buchung_exists ( $mietvertrag_id, $this->monat_heute, $this->jahr_heute );
			if ($buchungen_existieren == NULL) {
				$einheit_kurzname = $this->einheit_kurzname_finden ( $mv_arr [$i] [EINHEIT_ID] );
				$mietvertrag_id = $mv_arr [$i] ['mietvertrag_id'];
				$link = "<a href=\"?daten=mietkonten_blatt&anzeigen=miete_manuell_buchen&mietvertrag_id=$mietvertrag_id\">$einheit_kurzname</a>";
				echo "<br>$link";
			}
		}
	}
	
	// ########## formular funktionen
	function text_feld($beschreibung, $name, $wert, $size) {
		echo "<label for=\"$name\">$beschreibung</label> <input type=\"text\" id=\"$name\" name=\"$name\" value=\"$wert\" size=\"$size\" style=\"text-align:right\" onblur=\"javascript:activate(this.id)\" >\n";
	}
	function text_feld_id($beschreibung, $name, $wert, $size, $id) {
		echo "<label for=\"$name\">$beschreibung</label> <input type=\"text\" id=\"$id\" name=\"$name\" value=\"$wert\" size=\"$size\" style=\"text-align:right\"  onblur=\"javascript:zusammenfassung_neuberechnen(this.form)\" onchange=\"javascript:zusammenfassung_neuberechnen(this.form)\" >\n";
	}
	function text_feld_js($beschreibung, $name, $wert, $size, $id, $js_action) {
		echo "<label for=\"$name\">$beschreibung</label> <input type=\"text\" id=\"$id\" name=\"$name\" value=\"$wert\" size=\"$size\" style=\"text-align:right\"  $js_action >\n";
	}
	function mwst_feld($beschreibung, $name, $wert, $size) {
		echo "<label for=\"$name\">$beschreibung</label> <input type=\"text\" id=\"mwst_feld\" name=\"$name\" value=\"$wert\" size=\"$size\" >\n";
	}
	function rabatt_feld($beschreibung, $name, $wert, $size) {
		echo "<label for=\"$name\">$beschreibung</label> <input type=\"text\" id=\"rabatt_feld\" name=\"$name\" value=\"$wert\" size=\"$size\" >\n";
	}
	function gesamt_errechnet_feld($beschreibung, $name, $wert, $size) {
		echo "<label for=\"$name\">$beschreibung</label> <input type=\"text\" id=\"$name\" name=\"$name\" value=\"$wert\" size=\"$size\"  onfocus=\"javascript:gesamt_berechnen()\">\n";
	}
	function mengen_feld($beschreibung, $name, $wert, $size) {
		echo "<label for=\"$name\">$beschreibung</label> <input type=\"text\" id=\"$name\" name=\"$name\" value=\"$wert\" size=\"$size\" onfocus=\"javascript:activate(this.id)\"  onChange=\"javascript:neu_berechnen()\" style=\"text-align:right\">\n";
	}
	function preis_feld($beschreibung, $name, $wert, $size) {
		echo "<label for=\"$name\">$beschreibung</label> <input type=\"text\" id=\"$name\" name=\"$name\" value=\"$wert\" size=\"$size\" onfocus=\"javascript:activate(this.id)\"  onChange=\"javascript:neu_berechnen_neuer_preis()\" style=\"text-align:right\">\n";
	}
	function text_feld_inaktiv($beschreibung, $name, $wert, $size) {
		echo "<label for=\"$name\">$beschreibung</label> <input type=\"text\" id=\"$beschreibung.$name\" name=\"$beschreibung.$name\" value=\"$wert\" size=\"$size\" disabled>\n";
	}
	function hidden_feld($name, $wert) {
		echo "<input type=\"hidden\" id=\"$name\" name=\"$name\" value=\"$wert\" >\n";
	}
	function radio_button($name, $wert, $label) {
		echo "<label for=\"$name\">$label</label><input type=\"radio\" id=\"$name\" name=\"$name\" value=\"$wert\">\n";
	}
	function radio_button_checked($name, $wert, $label) {
		echo "<label for=\"$name\">$label</label><input type=\"radio\" id=\"$name\" name=\"$name\" value=\"$wert\" checked>\n";
	}
	function radio_button_js($name, $wert, $label, $js, $checked) {
		echo "<label for=\"$name\">$label</label><input type=\"radio\" id=\"$name\" name=\"$name\" value=\"$wert\" $js $checked>\n";
	}
	function text_bereich($beschreibung, $name, $wert, $cols, $rows) {
		echo "<label for=\"$name\">$beschreibung</label><textarea id=\"$name\" name=\"$name\"  cols=\"$cols\" rows=\"$rows\">$wert</textarea>\n";
	}
	function send_button($name, $wert) {
		echo "<input type=submit name=\"$name\" value=\"$wert\" class=\"submit\" id=\"$name\">";
	}
	function send_button_js($name, $wert, $js) {
		echo "<input type=submit name=\"$name\" value=\"$wert\" class=\"submit\" id=\"$name\" $js>";
	}
	function send_button_hidden($name, $wert) {
		echo "<input type=submit name=\"$name\"  value=\"$wert\" class=\"submit\" id=\"$name\"  disabled>";
	}
	function send_button_disabled($name, $wert, $id) {
		echo "<input type=submit name=\"$name\" id=\"$id\" value=\"$wert\" class=\"submit\" id=\"$name\"  disabled>";
	}
	function dropdown_kostenkategorien($beschreibung, $name) {
		echo "<label for=\"$name\">$beschreibung</label><select name=\"$name\" id=\"$name\" \n";
		echo "<option value=\"Miete kalt\">Miete kalt</option>\n";
		echo "<option value=\"Heizkosten Vorauszahlung\">Heizkosten Vorauszahlung</option>\n";
		echo "<option value=\"Nebenkosten Vorauszahlung\">Nebenkosten Vorauszahlung</option>\n";
		echo "</select>";
	}
	function dropdown_me_kostenkategorien($beschreibung, $name, $kostenkategorie) {
		echo "<label for=\"$name\">$beschreibung</label><select name=\"$name\" id=\"$name\"> \n";
		
		$jahr = date ( "Y" ) - 1;
		$vorjahr = $jahr - 4;
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
		$kostenkategorien_arr [] = 'Miete kalt';
		$kostenkategorien_arr [] = 'Heizkosten Vorauszahlung';
		$kostenkategorien_arr [] = 'Nebenkosten Vorauszahlung';
		$kostenkategorien_arr [] = 'Nebenkosten VZ - Anteilig';
		$kostenkategorien_arr [] = 'Kabel TV';
		$kostenkategorien_arr [] = 'Untermieter Zuschlag';
		$kostenkategorien_arr [] = 'MOD';
		$kostenkategorien_arr [] = 'MHG';
		// $kostenkategorien_arr[] = 'Mahngebühr';
		$kostenkategorien_arr [] = 'Ratenzahlung';
		$kostenkategorien_arr [] = 'Saldo Vortrag Vorverwaltung';
		$kostenkategorien_arr [] = 'Mietminderung';
		
		// echo '<pre>';
		// print_r($kostenkategorien_arr);
		for($a = $jahr; $a >= $vorjahr; $a --) {
			$kostenkategorien_arr [] = "Betriebskostenabrechnung $a";
			$kostenkategorien_arr [] = "Heizkostenabrechnung $a";
			$kostenkategorien_arr [] = "Kaltwasserabrechnung $a";
		}
		
		for($a = $jahr; $a >= $vorjahr; $a --) {
			$kostenkategorien_arr [] = "Energieverbrauch lt. Abr. $a";
		}
		
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
		echo "</select>";
	}
	function dropdown_tag() {
		$tag = date ( "d" );
		$monat = date ( "m" );
		$jahr = date ( "Y" );
		echo "<select name=\"tag\" width=20\n";
		for($i = 1; $i <= 31; $i ++) {
			if ($tag == $i) {
				echo "<option value=\"$i\" selected >$i</option>\n";
			} else {
				echo "<option value=\"$i\">$i</option>\n";
			}
		}
		echo "</select>";
	}
	function ende_formular() {
		echo "</fieldset></form>\n";
	}
	function datum_form() {
		$this->erstelle_formular ( "Buchungsdatum und Kontoauszugsnr eingeben...", NULL );
		$tag_heute = date ( "d" );
		$monat_heute = date ( "m" );
		$jahr_heute = date ( "Y" );
		echo "<table>";
		echo "<tr><td>";
		$this->text_feld ( 'Kontoauszugsnr.', 'KONTOAUSZUGSNR', $_SESSION [temp_kontoauszugsnummer], 5 );
		echo "</td>";
		echo "<td>Buchungsdatum</td><td>";
		echo "<select id=\"tag\" name=\"tag\"  class=\"datum\">\n";
		for($i = 1; $i <= 31; $i ++) {
			if ($i == $tag_heute) {
				echo "<option value=\"$i\" selected>$i</option>\n";
			} else {
				echo "<option value=\"$i\" >$i</option>\n";
			}
		}
		echo "</select>\n";
		echo "<select id=\"monat\" name=\"monat\" class=\"datum\">\n";
		for($i = 1; $i <= 12; $i ++) {
			if ($i == $monat_heute) {
				echo "<option value=\"$i\" selected>$i</option>\n";
			} else {
				echo "<option value=\"$i\" >$i</option>\n";
			}
		}
		echo "</select>\n";
		echo "<select name=\"jahr\" id=\"jahr\" class=\"datum\">\n";
		$vorjahr = $jahr_heute - 1;
		echo "<option value=\"$vorjahr\" selected>$vorjahr</option>\n";
		echo "<option value=\"$jahr_heute\" selected>$jahr_heute</option>\n";
		echo "</select>\n";
		echo "</td>";
		echo "<td>";
		$this->send_button ( "datum_setzen", "Datum setzen" );
		echo "</td>";
		
		echo "</tr></table>";
		$this->ende_formular ();
	}
	function erstelle_formular($name, $action) {
		echo "<fieldset class=\"$name\" >";
		echo "<legend>$name</legend>";
		// $self = $_SERVER['PHP_SELF'];
		$scriptname = $_SERVER ['REQUEST_URI'];
		$servername = $_SERVER ['SERVER_NAME'];
		$serverport = $_SERVER ['SERVER_PORT'];
		$https = $_SERVER ['HTTPS'];

		if(isset($https) && $https !== 'off') {
			$self = "https://$servername:$serverport$scriptname";
		} else {
			$self = "http://$servername:$serverport$scriptname";
		}
		
		if (! isset ( $action )) {
			echo "<form class=\"$name\" name=\"$name\" action=\"$self\"  method=\"post\">\n";
		} else {
			echo "<form name=\"$name\" action=\"$action\" method=\"post\">\n";
		}
		echo "\n";
	}
	
	// ############ ende formular ###########
} // end class
  
// Klasse zur Erstellung eines Arrays mit Monaten und Jahren
  // z.B. seit Einzug bis heute
class zeitraum {
	function check_number($checkValue) {
		if (abs ( $checkValue ) != $checkValue) {
			// in this case, the value is negative; return 1
			return 1;
		} else {
			// number is positive; return 0
			return 0;
		}
	}
	function zeitraum_generieren($monat_von, $jahr_von, $monat_bis, $jahr_bis) {
		$laenge_monat_von = strlen ( $monat_von );
		$laenge_monat_bis = strlen ( $monat_bis );
		if ($monat_von < 10 && $laenge_monat_von == 2) {
			$monat_von = substr ( $monat_von, 1, 1 );
		}
		if ($monat_bis < 10 && $laenge_monat_von == 2) {
			$monat_bis = substr ( $monat_bis, 1, 1 );
		}
		
		// Aktuelle Datumangaben
		$letztes_datum_monat = date ( "Y-m-t" ); // letzter Tag im aktuellen Monat, dafür steht (t) z.B. 28 bzw 29 / 30. oder 31.
		$aktuelles_datum = explode ( "-", $letztes_datum_monat );
		$aktuelles_jahr = $aktuelles_datum [0];
		$aktueller_monat = $aktuelles_datum [1];
		$aktueller_tag = $aktuelles_datum [2];
		
		$diff_in_jahren = $jahr_bis - $jahr_von;
		
		// 1. Regel, falls Einzugs- und aktuelles Jahr identisch z.b. 1.1.2008 und heute 20.5.2008
		if ($diff_in_jahren == 0) {
			if ($monat_von > $monat_bis) {
				return false;
			} else {
				for($monat = $monat_von; $monat <= $monat_bis; $monat ++) {
					if ($monat < 10) {
						$datum_jahr_arr = array (
								"monat" => "0$monat",
								"jahr" => "$jahr_bis" 
						);
					} else {
						$datum_jahr_arr = array (
								"monat" => "$monat",
								"jahr" => "$jahr_bis" 
						);
					}
					$monate_arr [] = $datum_jahr_arr;
				} // end for
			} // end else
		} // end if diff=0
		  
		// 2. Regel, falls Einzugs- und aktuelles Jahr identisch z.b. 1.1.2008 und heute 20.5.2008
		if ($diff_in_jahren > 0) {
			// Alle Jahre durchlaufen und hochzählen, Beginn bei Einzugsjahr bis aktuelles Jahr
			for($jahr = $jahr_von; $jahr <= $jahr_bis; $jahr ++) {
				
				// Wenn Jahr = Einzugsjahr d.h. erstes bzw Einzugsjahr
				if ($jahr == $jahr_von) {
					for($monat = $monat_von; $monat <= 12; $monat ++) {
						if ($monat < 10) {
							$datum_jahr_arr = array (
									"monat" => "0$monat",
									"jahr" => "$jahr" 
							);
						} else {
							$datum_jahr_arr = array (
									"monat" => "$monat",
									"jahr" => "$jahr" 
							);
						}
						$monate_arr [] = $datum_jahr_arr;
					} // end for $monat=$monat_einzug;$monat<=12;$monat++
				} // end if $jahr==$jahr_einzug
				  
				// Wenn Jahr aktuelles Jahr z.b 2008 d.h letztes Jahr in der Schleife
				if ($jahr == $jahr_bis) {
					for($monat = 1; $monat <= $monat_bis; $monat ++) {
						if ($monat < 10) {
							$datum_jahr_arr = array (
									"monat" => "0$monat",
									"jahr" => "$jahr" 
							);
						} else {
							$datum_jahr_arr = array (
									"monat" => "$monat",
									"jahr" => "$jahr" 
							);
						}
						$monate_arr [] = $datum_jahr_arr;
					} // end for
				} // end if
				
				if ($jahr != $jahr_von && $jahr != $jahr_bis) {
					for($monat = 1; $monat <= 12; $monat ++) {
						if ($monat < 10) {
							$datum_jahr_arr = array (
									"monat" => "0$monat",
									"jahr" => "$jahr" 
							);
						} else {
							$datum_jahr_arr = array (
									"monat" => "$monat",
									"jahr" => "$jahr" 
							);
						}
						$monate_arr [] = $datum_jahr_arr;
					} // end for
				} // end if
			} // end for
		} // end if diff=0
		/*
		 * echo "<pre>";
		 * print_r($monate_arr);
		 * echo "</pre>";
		 */
		return $monate_arr;
	} // ende function "zeitraum_arr_seit_einzug""
	function zeitraum_arr_seit_einzug($mietvertrag_id) {
		// Mietvertragsdaten ermitteln
		$mv_info = new mietkonto ();
		$mv_info->mietvertrag_grunddaten_holen ( $mietvertrag_id );
		$mietvertrag_von = $mv_info->mietvertrag_von;
		$mietvertrag_bis = $mv_info->mietvertrag_bis;
		$datum_einzug = explode ( "-", "$mietvertrag_von" );
		$tag_einzug = $datum_einzug [2];
		$monat_einzug = $datum_einzug [1];
		if ($monat_einzug < 10) { // bei 01 02 03 die Null abschneiden
			$monat_einzug = substr ( $monat_einzug, - 1 );
		}
		$jahr_einzug = $datum_einzug [0];
		
		// Aktuelle Datumangaben
		$letztes_datum_monat = date ( "Y-m-t" ); // letzter Tag im aktuellen Monat, dafür steht (t) z.B. 28 bzw 29 / 30. oder 31.
		$aktuelles_datum = explode ( "-", $letztes_datum_monat );
		$aktuelles_jahr = $aktuelles_datum [0];
		$aktueller_monat = $aktuelles_datum [1];
		$aktueller_tag = $aktuelles_datum [2];
		$diff_in_jahren = $aktuelles_jahr - $jahr_einzug;
		
		// 1. Regel, falls Einzugs- und aktuelles Jahr identisch z.b. 1.1.2008 und heute 20.5.2008
		if ($diff_in_jahren == "0") {
			for($monat = $monat_einzug; $monat <= $aktueller_monat; $monat ++) {
				if ($monat < 10) {
					$datum_jahr_arr = array (
							"monat" => "0$monat",
							"jahr" => "$aktuelles_jahr" 
					);
				} else {
					$datum_jahr_arr = array (
							"monat" => "$monat",
							"jahr" => "$aktuelles_jahr" 
					);
				}
				$monate_arr [] = $datum_jahr_arr;
			} // end for
		} // end if diff=0
		  
		// 2. Regel, falls Einzugs- und aktuelles Jahr identisch z.b. 1.1.2008 und heute 20.5.2008
		if ($diff_in_jahren > "0") {
			// Alle Jahre durchlaufen und hochzählen, Beginn bei Einzugsjahr bis aktuelles Jahr
			for($jahr = $jahr_einzug; $jahr <= $aktuelles_jahr; $jahr ++) {
				
				// Wenn Jahr = Einzugsjahr d.h. erstes bzw Einzugsjahr
				if ($jahr == $jahr_einzug) {
					for($monat = $monat_einzug; $monat <= 12; $monat ++) {
						if ($monat < 10) {
							$datum_jahr_arr = array (
									"monat" => "0$monat",
									"jahr" => "$jahr" 
							);
						} else {
							$datum_jahr_arr = array (
									"monat" => "$monat",
									"jahr" => "$jahr" 
							);
						}
						$monate_arr [] = $datum_jahr_arr;
					} // end for $monat=$monat_einzug;$monat<=12;$monat++
				} // end if $jahr==$jahr_einzug
				  
				// Wenn Jahr aktuelles Jahr z.b 2008 d.h letztes Jahr in der Schleife
				if ($jahr == $aktuelles_jahr) {
					for($monat = 1; $monat <= $aktueller_monat; $monat ++) {
						if ($monat < 10) {
							$datum_jahr_arr = array (
									"monat" => "0$monat",
									"jahr" => "$jahr" 
							);
						} else {
							$datum_jahr_arr = array (
									"monat" => "$monat",
									"jahr" => "$jahr" 
							);
						}
						$monate_arr [] = $datum_jahr_arr;
					} // end for
				} // end if
				
				if ($jahr != $jahr_einzug && $jahr != $aktuelles_jahr) {
					for($monat = 1; $monat <= 12; $monat ++) {
						if ($monat < 10) {
							$datum_jahr_arr = array (
									"monat" => "0$monat",
									"jahr" => "$jahr" 
							);
						} else {
							$datum_jahr_arr = array (
									"monat" => "$monat",
									"jahr" => "$jahr" 
							);
						}
						$monate_arr [] = $datum_jahr_arr;
					} // end for
				} // end if
			} // end for
		} // end if diff=0
		/*
		 * echo "<pre>";
		 * print_r($monate_arr);
		 * echo "</pre>";
		 */
		return $monate_arr;
	} // ende function "zeitraum_arr_seit_einzug""
	function datum_saldo_vorverwaltung($mietvertrag_id) {
		$result = mysql_query ( "SELECT DATUM FROM MIETE_ZAHLBETRAG WHERE BEMERKUNG = 'Saldo Vortrag Vorverwaltung' && MIETVERTRAG_ID ='$mietvertrag_id' && AKTUELL = '1'" );
		$row = mysql_fetch_assoc ( $result );
		return $row ['DATUM'];
	}
	function zeitraum_arr_seit_uebernahme($mietvertrag_id) {
		// Mietvertragsdaten ermitteln
		$mv_info = new mietkonto ();
		$mv_info->mietvertrag_grunddaten_holen ( $mietvertrag_id );
		$mietvertrag_von = $mv_info->mietvertrag_von;
		$mietvertrag_bis = $mv_info->mietvertrag_bis;
		$datum_saldo_vorwervaltung = $this->datum_saldo_vorverwaltung ( $mietvertrag_id );
		if (! isset ( $datum_saldo_vorwervaltung )) {
			$datum_einzug = explode ( "-", "$mietvertrag_von" );
		} else {
			$datum_einzug = explode ( "-", "$datum_saldo_vorwervaltung" );
		}
		$tag_einzug = $datum_einzug [2];
		$monat_einzug = $datum_einzug [1];
		if ($monat_einzug < 10) { // bei 01 02 03 die Null abschneiden
			$monat_einzug = substr ( $monat_einzug, - 1 );
		}
		$jahr_einzug = $datum_einzug [0];
		
		// Aktuelle Datumangaben
		$letztes_datum_monat = date ( "Y-m-t" ); // letzter Tag im aktuellen Monat, dafür steht (t) z.B. 28 bzw 29 / 30. oder 31.
		$aktuelles_datum = explode ( "-", $letztes_datum_monat );
		$aktuelles_jahr = $aktuelles_datum [0];
		$aktueller_monat = $aktuelles_datum [1];
		$aktueller_tag = $aktuelles_datum [2];
		$diff_in_jahren = $aktuelles_jahr - $jahr_einzug;
		
		// 1. Regel, falls Einzugs- und aktuelles Jahr identisch z.b. 1.1.2008 und heute 20.5.2008
		if ($diff_in_jahren == "0") {
			for($monat = $monat_einzug; $monat <= $aktueller_monat; $monat ++) {
				if ($monat < 10) {
					$datum_jahr_arr = array (
							"monat" => "0$monat",
							"jahr" => "$aktuelles_jahr" 
					);
				} else {
					$datum_jahr_arr = array (
							"monat" => "$monat",
							"jahr" => "$aktuelles_jahr" 
					);
				}
				$monate_arr [] = $datum_jahr_arr;
			} // end for
		} // end if diff=0
		  
		// 2. Regel, falls Einzugs- und aktuelles Jahr identisch z.b. 1.1.2008 und heute 20.5.2008
		if ($diff_in_jahren > 0) {
			// Alle Jahre durchlaufen und hochzählen, Beginn bei Einzugsjahr bis aktuelles Jahr
			for($jahr == $jahr_einzug; $jahr <= $aktuelles_jahr; $jahr ++) {
				
				// Wenn Jahr = Einzugsjahr d.h. erstes bzw Einzugsjahr
				if ($jahr == $jahr_einzug) {
					for($monat == $monat_einzug; $monat <= 12; $monat ++) {
						if ($monat < 10) {
							$datum_jahr_arr = array (
									"monat" => "0$monat",
									"jahr" => "$jahr" 
							);
						} else {
							$datum_jahr_arr = array (
									"monat" => "$monat",
									"jahr" => "$jahr" 
							);
						}
						$monate_arr [] = $datum_jahr_arr;
					} // end for $monat=$monat_einzug;$monat<=12;$monat++
				} // end if $jahr==$jahr_einzug
				  
				// Wenn Jahr aktuelles Jahr z.b 2008 d.h letztes Jahr in der Schleife
				if ($jahr == $aktuelles_jahr) {
					for($monat = 1; $monat <= $aktueller_monat; $monat ++) {
						if ($monat < 10) {
							$datum_jahr_arr = array (
									"monat" => "0$monat",
									"jahr" => "$jahr" 
							);
						} else {
							$datum_jahr_arr = array (
									"monat" => "$monat",
									"jahr" => "$jahr" 
							);
						}
						$monate_arr [] = $datum_jahr_arr;
					} // end for
				} // end if
				
				if ($jahr != $jahr_einzug && $jahr != $aktuelles_jahr) {
					for($monat = 1; $monat <= 12; $monat ++) {
						if ($monat < 10) {
							$datum_jahr_arr = array (
									"monat" => "0$monat",
									"jahr" => "$jahr" 
							);
						} else {
							$datum_jahr_arr = array (
									"monat" => "$monat",
									"jahr" => "$jahr" 
							);
						}
						$monate_arr [] = $datum_jahr_arr;
					} // end for
				} // end if
			} // end for
		} // end if diff=0
		/*
		 * echo "<pre>";
		 * print_r($monate_arr);
		 * echo "</pre>";
		 */
		return $monate_arr;
	} // ende function "zeitraum_arr_seit_einzug""
} // end class zeitraum

?>
