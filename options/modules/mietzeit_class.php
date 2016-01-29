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
 * @filesource   $HeadURL: http://192.168.2.52/svn/berlussimo_1/tags/21.09.2010 - Erstversion fuer Download/berlussimo_1/classes/mietzeit_class.php $
 * @version      $Revision: 6 $
 * @modifiedby   $LastChangedBy: sivac $
 * @lastmodified $Date: 2010-09-21 10:34:50 +0200 (Di, 21 Sep 2010) $
 * 
 */

/*
 * Das ist eine Test bzw. Spieledatei, aus der kann man viel lernen,
 * leider die Funktionen und vars nicht in Deutsch
 */
include_once ("config.inc.php");
class miete {
	var $jahr_aktuell;
	var $monat_aktuell;
	/* startdaten */
	var $start_m = 1;
	var $start_j = 2007;
	var $end_m;
	var $end_j;
	var $mietvertrag_id;
	var $saldo_vv;
	var $saldo_vormonat;
	var $temp_soll;
	var $temp_zb;
	var $erg = '0';
	var $bk_abrechnung = '0.00';
	var $hk_abrechnung = '0.00';
	var $daten_arr;
	function mietkonto_berechnung($mietvertrag_id) {
		$this->mietvertrag_id = $mietvertrag_id;
		$this->jahr_aktuell = date ( "Y" );
		$this->monat_aktuell = date ( "m" );
		$this->end_j = date ( "Y" );
		$this->end_m = date ( "m" );
		
		/* Include mietkonto_class */
		$buchung = new mietkonto ();
		
		$datum_saldo_vv = $buchung->datum_saldo_vortrag_vorverwaltung ( $mietvertrag_id );
		/* wenn datum vom saldovv */
		if (! empty ( $datum_saldo_vv )) {
			$datum_saldo_vv = explode ( "-", $datum_saldo_vv );
			$this->saldo_vv = $buchung->saldo_vortrag_vorverwaltung ( $mietvertrag_id );
			$this->saldo_vv = number_format ( $this->saldo_vv, 2, '.', '' );
			// $this->saldo_vormonat = $this->saldo_vv;
			// if($this->saldo_vv>0.00){
			// $this->saldo_vv = '-'.$this->saldo_vv;
			// }
		}
		
		/* Sonst datum aus mietdefinition oder einzugsdatum aus mietvertrag */
		$datum_mietdefinition = $buchung->datum_1_mietdefinition ( $mietvertrag_id );
		$datum_mietdefinition1 = explode ( '-', $datum_mietdefinition );
		$datum_mietdefinition1 = implode ( '', $datum_mietdefinition1 );
		
		$datum1_zahlung = $buchung->datum_1_zahlung ( $mietvertrag_id );
		$datum1_zahlung1 = explode ( '-', $datum1_zahlung );
		$datum1_zahlung1 = implode ( '', $datum1_zahlung1 );
		/* echo "<h1>$datum_mietdefinition1 $datum1_zahlung1</h1>"; */
		/* Entscheiden welches ab welchem tag gerechnet werden soll */
		if ($datum_mietdefinition1 > $datum1_zahlung1) {
			$datum_mietdefinition = $datum1_zahlung;
		}
		/* Wenn miete �ber mietentwicklung definiert ist */
		if (! empty ( $datum_mietdefinition )) {
			// echo "DEFINITION $datum_mietdefinition";
			$datum_mietdefinition = explode ( "-", $datum_mietdefinition );
			$this->start_m = $datum_mietdefinition [1];
			$this->start_j = $datum_mietdefinition [0];
			$buchung->ein_auszugsdatum_mietvertrag ( $mietvertrag_id );
			if ($buchung->mietvertrag_bis != '0000-00-00') {
				$auszugsdatum = explode ( "-", $buchung->mietvertrag_bis );
				/* Falls MV abgelaufen nur bis zum aktuellen Monat berechnen */
				// $this->end_j = $auszugsdatum[0];
				$this->end_m = $this->monat_aktuell;
				$this->end_j = $this->jahr_aktuell;
			}
			/* Wenn Auszugsjahr kleiner als aktuelles Jahr UND mehr als 2 Jahre in Vergangenheit, dann nur 1 Jahr nach Auszug berechnen. */
			/*
			 * if($auszugsdatum[0] < $this->jahr_aktuell && $this->jahr_aktuell - $auszugsdatum[0] >1){
			 * $this->end_m = 12;
			 * $this->end_j = $auszugsdatum[0] +1;
			 * }
			 */
		} 		/* Sonst einzugsdatum ermitteln */
		else {
			$buchung->ein_auszugsdatum_mietvertrag ( $mietvertrag_id );
			$einzugsdatum = explode ( "-", $buchung->mietvertrag_von );
			$this->start_m = $einzugsdatum [1];
			$this->start_j = $einzugsdatum [0];
			/* Wenn MV befristet dann enddatum auch setzen */
			if ($buchung->mietvertrag_bis != '0000-00-00') {
				$auszugsdatum = explode ( "-", $buchung->mietvertrag_bis );
				// $this->end_m = $auszugsdatum[1];
				// $this->end_j = $auszugsdatum[0];
				$this->end_j = date ( "Y" );
				$this->end_m = date ( "m" );
			}
		}
		
		/* jahresschleife */
		for($a = $this->start_j; $a <= $this->end_j; $a ++) {
			/* anfangs und endjahr gleich */
			if ($a == $this->start_j && $a == $this->end_j) {
				$start_m = $this->start_m;
				$end_m = $this->end_m;
			}
			/* voll jahre dazwischen */
			if ($a > $this->start_j && $a < $this->end_j) {
				$start_m = 1;
				$end_m = 12;
			}
			/* erstjahr */
			if ($a == $this->start_j && $a != $this->end_j) {
				$start_m = $this->start_m;
				$end_m = 12;
			}
			/* endjahr */
			if ($a == $this->end_j && $a != $this->start_j) {
				$start_m = 1;
				$end_m = $this->end_m;
			}
			/* monatsschleife */
			$m_zaehler = 0;
			if ($start_m < 10) {
				$start_m = substr ( $start_m, - 1 );
			}
			for($b = $start_m; $b <= $end_m; $b ++) {
				
				if ($m_zaehler == 0 && $a == $this->start_j) {
					$this->saldo_vormonat = $this->saldo_vv;
				}
				$this->daten_arr [$a] [monate] [$m_zaehler] [monat] = $b;
				$this->daten_arr [$a] [monate] [$m_zaehler] [saldo_vormonat] = $this->saldo_vormonat;
				
				if ($buchung->summe_betriebskostenabrechnung ( $mietvertrag_id, $b, $a ) && $buchung->summe_betriebskostenabrechnung ( $mietvertrag_id, $b, $a ) != '0.00') {
					$this->daten_arr [$a] [monate] [$m_zaehler] [bk_abrechnung] = $buchung->summe_betriebskostenabrechnung ( $mietvertrag_id, $b, $a );
					$this->daten_arr [$a] [monate] [$m_zaehler] [bk_abrechnung_datum] = $buchung->datum_betriebskostenabrechnung ( $mietvertrag_id, $b, $a );
					$this->bk_abrechnung = $this->daten_arr [$a] [monate] [$m_zaehler] [bk_abrechnung];
				}
				
				if ($buchung->summe_heizkostenabrechnung ( $mietvertrag_id, $b, $a ) && $buchung->summe_heizkostenabrechnung ( $mietvertrag_id, $b, $a ) != '0.00') {
					$this->daten_arr [$a] [monate] [$m_zaehler] [hk_abrechnung] = $buchung->summe_heizkostenabrechnung ( $mietvertrag_id, $b, $a );
					$this->daten_arr [$a] [monate] [$m_zaehler] [hk_abrechnung_datum] = $buchung->datum_heizkostenabrechnung ( $mietvertrag_id, $b, $a );
					$this->hk_abrechnung = $this->daten_arr [$a] [monate] [$m_zaehler] [hk_abrechnung];
				}
				
				if ($buchung->summe_wasserkostenabrechnung ( $mietvertrag_id, $b, $a ) && $buchung->summe_wasserkostenabrechnung ( $mietvertrag_id, $b, $a ) != '0.00') {
					$this->daten_arr [$a] [monate] [$m_zaehler] [wasser_abrechnung] = $buchung->summe_wasserkostenabrechnung ( $mietvertrag_id, $b, $a );
					$this->daten_arr [$a] [monate] [$m_zaehler] [wasser_abrechnung_datum] = $buchung->datum_wasserkostenabrechnung ( $mietvertrag_id, $b, $a );
					$this->wasser_abrechnung = $this->daten_arr [$a] [monate] [$m_zaehler] [wasser_abrechnung];
				}
				
				if ($buchung->summe_mahngebuehr_im_monat ( $mietvertrag_id, $b, $a ) && $buchung->summe_mahngebuehr_im_monat ( $mietvertrag_id, $b, $a ) != '0.00') {
					
					$this->daten_arr [$a] [monate] [$m_zaehler] [mahngebuehr] = $buchung->summe_mahngebuehr_im_monat ( $mietvertrag_id, $b, $a );
					$this->mahngebuehr = $this->daten_arr [$a] [monate] [$m_zaehler] [mahngebuehr];
					
					$this->daten_arr [$a] [monate] [$m_zaehler] [mahngebuehren] = $buchung->mahngebuehr_monatlich_arr ( $mietvertrag_id, $b, $a );
				}
				
				$this->daten_arr [$a] [monate] [$m_zaehler] [soll] = '-' . $buchung->summe_forderung_monatlich ( $this->mietvertrag_id, $b, $a );
				;
				$this->temp_soll = $this->daten_arr [$a] [monate] [$m_zaehler] [soll];
				$this->daten_arr [$a] [monate] [$m_zaehler] [zahlungen] = $buchung->zahlbetraege_im_monat_arr ( $mietvertrag_id, $b, $a );
				// echo "<h1> $b $a</h1><br>";
				/*
				 * $this->daten_arr[$a][monate][$m_zaehler][zahlungen][0][txt]= 'zb';
				 * $this->daten_arr[$a][monate][$m_zaehler][zahlungen][0][b]= $this->temp_zb;
				 * $this->daten_arr[$a][monate][$m_zaehler][zahlungen][1][txt]= 'zb';
				 * $this->daten_arr[$a][monate][$m_zaehler][zahlungen][1][b]= $this->temp_zb;
				 */
				$sum_mon = 0;
				$z_arr = $this->daten_arr [$a] [monate] [$m_zaehler] [zahlungen];
				for($c = 0; $c < count ( $z_arr ); $c ++) {
					$sum_mon = $sum_mon + $z_arr [$c] [BETRAG];
				}
				$sum_mon = number_format ( $sum_mon, 2, '.', '' );
				$this->daten_arr [$a] [monate] [$m_zaehler] [zb] = $sum_mon;
				
				// $this->erg = $this->saldo_vormonat + $this->temp_soll + $sum_mon + $this->hk_abrechnung + $this->bk_abrechnung + $this->wasser_abrechnung + $this->mahngebuehr;
				$this->erg = $this->saldo_vormonat + $this->temp_soll + $sum_mon + $this->hk_abrechnung + $this->bk_abrechnung + $this->wasser_abrechnung;
				
				$this->bk_abrechnung = '0.00';
				$this->hk_abrechnung = '0.00';
				$this->wasser_abrechnung = '0.00';
				$this->mahngebuehr = '0.00';
				$this->erg = number_format ( $this->erg, 2, '.', '' );
				$this->daten_arr [$a] [monate] [$m_zaehler] [erg] = $this->erg;
				$this->saldo_vormonat = $this->erg;
				$m_zaehler ++;
			}
		}
	}
	function mietkonto_berechnung_2007($mietvertrag_id) {
		$this->mietvertrag_id = $mietvertrag_id;
		$this->jahr_aktuell = date ( "Y" );
		$this->monat_aktuell = date ( "m" );
		$this->end_j = date ( "Y" );
		$this->end_m = date ( "m" );
		
		/* Include mietkonto_class */
		$buchung = new mietkonto ();
		
		$buchung->datum_saldo_vortrag_vorverwaltung ( $mietvertrag_id );
		/* wenn datum vom saldovv */
		if (! empty ( $this->datum_saldo_vv )) {
			$datum_saldo_vv = explode ( "-", $datum_saldo_vv );
			$this->saldo_vv = $buchung->saldo_vortrag_vorverwaltung ( $mietvertrag_id );
			$this->saldo_vv = number_format ( $this->saldo_vv, 2, '.', '' );
			// $this->saldo_vormonat = $this->saldo_vv;
			// if($this->saldo_vv>0.00){
			// $this->saldo_vv = '-'.$this->saldo_vv;
			// }
		}
		
		/* Sonst datum aus mietdefinition oder einzugsdatum aus mietvertrag */
		$datum_mietdefinition = $buchung->datum_1_mietdefinition ( $mietvertrag_id );
		$datum_mietdefinition1 = explode ( '-', $datum_mietdefinition );
		$datum_mietdefinition1 = implode ( '', $datum_mietdefinition1 );
		
		$datum1_zahlung = $buchung->datum_1_zahlung ( $mietvertrag_id );
		$datum1_zahlung1 = explode ( '-', $datum1_zahlung );
		$datum1_zahlung1 = implode ( '', $datum1_zahlung1 );
		/* echo "<h1>$datum_mietdefinition1 $datum1_zahlung1</h1>"; */
		/* Entscheiden welches ab welchem tag gerechnet werden soll */
		if ($datum_mietdefinition1 > $datum1_zahlung1) {
			$datum_mietdefinition = $datum1_zahlung;
		}
		/* Wenn miete �ber mietentwicklung definiert ist */
		if (! empty ( $datum_mietdefinition )) {
			// echo "DEFINITION $datum_mietdefinition";
			$datum_mietdefinition = explode ( "-", $datum_mietdefinition );
			$this->start_m = $datum_mietdefinition [1];
			$this->start_j = $datum_mietdefinition [0];
			$buchung->ein_auszugsdatum_mietvertrag ( $mietvertrag_id );
			if ($buchung->mietvertrag_bis != '0000-00-00') {
				$auszugsdatum = explode ( "-", $buchung->mietvertrag_bis );
				/* Falls MV abgelaufen nur bis zum aktuellen Monat berechnen */
				// $this->end_j = $auszugsdatum[0];
				$this->end_m = $this->monat_aktuell;
				$this->end_j = $this->jahr_aktuell;
			}
			/* Wenn Auszugsjahr kleiner als aktuelles Jahr UND mehr als 2 Jahre in Vergangenheit, dann nur 1 Jahr nach Auszug berechnen. */
			/*
			 * if($auszugsdatum[0] < $this->jahr_aktuell && $this->jahr_aktuell - $auszugsdatum[0] >1){
			 * $this->end_m = 12;
			 * $this->end_j = $auszugsdatum[0] +1;
			 * }
			 */
		} 		/* Sonst einzugsdatum ermitteln */
		else {
			$buchung->ein_auszugsdatum_mietvertrag ( $mietvertrag_id );
			$einzugsdatum = explode ( "-", $buchung->mietvertrag_von );
			$this->start_m = $einzugsdatum [1];
			$this->start_j = $einzugsdatum [0];
			/* Wenn MV befristet dann enddatum auch setzen */
			if ($buchung->mietvertrag_bis != '0000-00-00') {
				$auszugsdatum = explode ( "-", $buchung->mietvertrag_bis );
				// $this->end_m = $auszugsdatum[1];
				// $this->end_j = $auszugsdatum[0];
				$this->end_j = date ( "Y" );
				$this->end_m = date ( "m" );
			}
		}
		
		/* jahresschleife */
		for($a = $this->start_j; $a <= $this->end_j; $a ++) {
			/* anfangs und endjahr gleich */
			if ($a == $this->start_j && $a == $this->end_j) {
				$start_m = $this->start_m;
				$end_m = $this->end_m;
			}
			/* voll jahre dazwischen */
			if ($a > $this->start_j && $a < $this->end_j) {
				$start_m = 1;
				$end_m = 12;
			}
			/* erstjahr */
			if ($a == $this->start_j && $a != $this->end_j) {
				$start_m = $this->start_m;
				$end_m = 12;
			}
			/* endjahr */
			if ($a == $this->end_j && $a != $this->start_j) {
				$start_m = 1;
				$end_m = $this->end_m;
			}
			/* monatsschleife */
			$m_zaehler = 0;
			if ($start_m < 10) {
				$start_m = substr ( $start_m, - 1 );
			}
			for($b = $start_m; $b <= $end_m; $b ++) {
				
				if ($m_zaehler == 0 && $a == $this->start_j) {
					$this->saldo_vormonat = $this->saldo_vv;
				}
				// $this->daten_arr[$a][monate][$m_zaehler][monat]= $b;
				// $this->daten_arr[$a][monate][$m_zaehler][saldo_vormonat]= $this->saldo_vormonat;
				
				if ($buchung->summe_betriebskostenabrechnung ( $mietvertrag_id, $b, $a ) && $buchung->summe_betriebskostenabrechnung ( $mietvertrag_id, $b, $a ) != '0.00') {
					$this->bk_abrechnung = $buchung->summe_bk_abrechnung;
				}
				
				if ($buchung->summe_heizkostenabrechnung ( $mietvertrag_id, $b, $a ) && $buchung->summe_heizkostenabrechnung ( $mietvertrag_id, $b, $a ) != '0.00') {
					$this->hk_abrechnung = $buchung->summe_hk_abrechnung;
				}
				
				$this->temp_soll = '-' . $buchung->summe_forderung_monatlich ( $this->mietvertrag_id, $b, $a );
				;
				$buchung->summe_aller_zahlungen_monat ( $mietvertrag_id, $b, $a );
				$sum_mon = $buchung->summe_z_im_monat;
				$sum_mon = number_format ( $sum_mon, 2, '.', '' );
				
				$this->erg = $this->saldo_vormonat + $this->temp_soll + $sum_mon + $this->hk_abrechnung + $this->bk_abrechnung;
				
				$this->bk_abrechnung = '0.00';
				$this->hk_abrechnung = '0.00';
				$this->erg = number_format ( $this->erg, 2, '.', '' );
				// $this->daten_arr[$a][monate][$m_zaehler][erg]= $this->erg;
				$this->saldo_vormonat = $this->erg;
				$m_zaehler ++;
			}
		}
	}
	
	/* Diese funktion lieferut uns den aktuellen stand und den stand des Vormonats, inkl geleistete Zahlungen usw f�r den MOnatsbericht */
	function mietkonto_berechnung_monatsgenau_OK($mietvertrag_id, $jahr, $monat) {
		$this->mietvertrag_id = $mietvertrag_id;
		$this->jahr_aktuell = date ( "Y" );
		$this->monat_aktuell = date ( "m" );
		$this->end_j = $jahr;
		$this->end_m = $monat; // zweistellig 03
		
		/* Include mietkonto_class */
		$buchung = new mietkonto ();
		
		$datum_saldo_vv = $buchung->datum_saldo_vortrag_vorverwaltung ( $mietvertrag_id );
		/* wenn datum vom saldovv */
		if (! empty ( $datum_saldo_vv )) {
			$datum_saldo_vv = explode ( "-", $datum_saldo_vv );
			$this->saldo_vv = $buchung->saldo_vortrag_vorverwaltung ( $mietvertrag_id );
			$this->saldo_vv = number_format ( $this->saldo_vv, 2, '.', '' );
			// $this->saldo_vormonat = $this->saldo_vv;
			// if($this->saldo_vv>0.00){
			// $this->saldo_vv = '-'.$this->saldo_vv;
			// }
		}
		
		/* Sonst datum aus mietdefinition oder einzugsdatum aus mietvertrag */
		$datum_mietdefinition = $buchung->datum_1_mietdefinition ( $mietvertrag_id );
		$datum_mietdefinition1 = explode ( '-', $datum_mietdefinition );
		$datum_mietdefinition1 = implode ( '', $datum_mietdefinition1 );
		
		$datum1_zahlung = $buchung->datum_1_zahlung ( $mietvertrag_id );
		$datum1_zahlung1 = explode ( '-', $datum1_zahlung );
		$datum1_zahlung1 = implode ( '', $datum1_zahlung1 );
		/* echo "<h1>$datum_mietdefinition1 $datum1_zahlung1</h1>"; */
		/* Entscheiden welches ab welchem tag gerechnet werden soll */
		if ($datum_mietdefinition1 > $datum1_zahlung1) {
			$datum_mietdefinition = $datum1_zahlung;
		}
		/* Wenn miete �ber mietentwicklung definiert ist */
		if (! empty ( $datum_mietdefinition )) {
			// echo "DEFINITION $datum_mietdefinition";
			$datum_mietdefinition = explode ( "-", $datum_mietdefinition );
			$this->start_m = $datum_mietdefinition [1];
			$this->start_j = $datum_mietdefinition [0];
			$buchung->ein_auszugsdatum_mietvertrag ( $mietvertrag_id );
			if ($buchung->mietvertrag_bis != '0000-00-00') {
				$auszugsdatum = explode ( "-", $buchung->mietvertrag_bis );
				/* Falls MV abgelaufen nur bis zum aktuellen Monat berechnen */
				// $this->end_j = $auszugsdatum[0];
				$this->end_m = $this->monat_aktuell;
				$this->end_j = $this->jahr_aktuell;
			}
			/* Wenn Auszugsjahr kleiner als aktuelles Jahr UND mehr als 2 Jahre in Vergangenheit, dann nur 1 Jahr nach Auszug berechnen. */
			/*
			 * if($auszugsdatum[0] < $this->jahr_aktuell && $this->jahr_aktuell - $auszugsdatum[0] >1){
			 * $this->end_m = 12;
			 * $this->end_j = $auszugsdatum[0] +1;
			 * }
			 */
		} 		/* Sonst einzugsdatum ermitteln */
		else {
			$buchung->ein_auszugsdatum_mietvertrag ( $mietvertrag_id );
			$einzugsdatum = explode ( "-", $buchung->mietvertrag_von );
			$this->start_m = $einzugsdatum [1];
			$this->start_j = $einzugsdatum [0];
			/* Wenn MV befristet dann enddatum auch setzen */
			if ($buchung->mietvertrag_bis != '0000-00-00') {
				$auszugsdatum = explode ( "-", $buchung->mietvertrag_bis );
				// $this->end_m = $auszugsdatum[1];
				// $this->end_j = $auszugsdatum[0];
				$this->end_j = date ( "Y" );
				$this->end_m = date ( "m" );
			}
		}
		
		/* jahresschleife */
		for($a = $this->start_j; $a <= $this->end_j; $a ++) {
			/* anfangs und endjahr gleich */
			if ($a == $this->start_j && $a == $this->end_j) {
				$start_m = $this->start_m;
				$end_m = $this->end_m;
			}
			/* voll jahre dazwischen */
			if ($a > $this->start_j && $a < $this->end_j) {
				
				$start_m = 1;
				$end_m = 12;
			}
			/* erstjahr */
			if ($a == $this->start_j && $a != $this->end_j) {
				$start_m = $this->start_m;
				$end_m = 12;
			}
			/* endjahr */
			if ($a == $this->end_j && $a != $this->start_j) {
				$start_m = 1;
				$end_m = $this->end_m;
			}
			/* monatsschleife */
			$m_zaehler = 0;
			if ($start_m < 10) {
				$start_m = substr ( $start_m, - 1 );
			}
			for($b = $start_m; $b <= $end_m; $b ++) {
				
				if ($m_zaehler == 0 && $a == $this->start_j) {
					$this->saldo_vormonat = $this->saldo_vv;
				}
				$this->daten_arr [$a] [monate] [$m_zaehler] [monat] = $b;
				$this->daten_arr [$a] [monate] [$m_zaehler] [saldo_vormonat] = $this->saldo_vormonat;
				
				if ($buchung->summe_betriebskostenabrechnung ( $mietvertrag_id, $b, $a ) && $buchung->summe_betriebskostenabrechnung ( $mietvertrag_id, $b, $a ) != '0.00') {
					$this->daten_arr [$a] [monate] [$m_zaehler] [bk_abrechnung] = $buchung->summe_betriebskostenabrechnung ( $mietvertrag_id, $b, $a );
					$this->daten_arr [$a] [monate] [$m_zaehler] [bk_abrechnung_datum] = $buchung->datum_betriebskostenabrechnung ( $mietvertrag_id, $b, $a );
					$this->bk_abrechnung = $this->daten_arr [$a] [monate] [$m_zaehler] [bk_abrechnung];
				}
				
				if ($buchung->summe_heizkostenabrechnung ( $mietvertrag_id, $b, $a ) && $buchung->summe_heizkostenabrechnung ( $mietvertrag_id, $b, $a ) != '0.00') {
					$this->daten_arr [$a] [monate] [$m_zaehler] [hk_abrechnung] = $buchung->summe_heizkostenabrechnung ( $mietvertrag_id, $b, $a );
					$this->daten_arr [$a] [monate] [$m_zaehler] [hk_abrechnung_datum] = $buchung->datum_heizkostenabrechnung ( $mietvertrag_id, $b, $a );
					$this->hk_abrechnung = $this->daten_arr [$a] [monate] [$m_zaehler] [hk_abrechnung];
				}
				$this->daten_arr [$a] [monate] [$m_zaehler] [soll] = '-' . $buchung->summe_forderung_monatlich ( $this->mietvertrag_id, $b, $a );
				;
				
				$ford_monatlich_arr = $buchung->forderung_monatlich ( $mietvertrag_id, $b, $a );
				$this->davon_umlagen = $buchung->summe_vorschuesse ( $ford_monatlich_arr );
				
				$this->temp_soll = $this->daten_arr [$a] [monate] [$m_zaehler] [soll];
				$this->daten_arr [$a] [monate] [$m_zaehler] [zahlungen] = $buchung->zahlbetraege_im_monat_arr ( $mietvertrag_id, $b, $a );
				// echo "<h1> $b $a</h1><br>";
				/*
				 * $this->daten_arr[$a][monate][$m_zaehler][zahlungen][0][txt]= 'zb';
				 * $this->daten_arr[$a][monate][$m_zaehler][zahlungen][0][b]= $this->temp_zb;
				 * $this->daten_arr[$a][monate][$m_zaehler][zahlungen][1][txt]= 'zb';
				 * $this->daten_arr[$a][monate][$m_zaehler][zahlungen][1][b]= $this->temp_zb;
				 */
				$sum_mon = 0;
				$z_arr = $this->daten_arr [$a] [monate] [$m_zaehler] [zahlungen];
				for($c = 0; $c < count ( $z_arr ); $c ++) {
					$sum_mon = $sum_mon + $z_arr [$c] [BETRAG];
				}
				$this->geleistete_zahlungen = $sum_mon;
				
				$sum_mon = number_format ( $sum_mon, 2, '.', '' );
				$this->daten_arr [$a] [monate] [$m_zaehler] [zb] = $sum_mon;
				
				$this->erg = $this->saldo_vormonat + $this->temp_soll + $sum_mon + $this->hk_abrechnung + $this->bk_abrechnung;
				
				$this->bk_abrechnung = '0.00';
				$this->hk_abrechnung = '0.00';
				$this->erg = number_format ( $this->erg, 2, '.', '' );
				$this->daten_arr [$a] [monate] [$m_zaehler] [erg] = $this->erg;
				$this->saldo_vormonat_stand = $this->saldo_vormonat;
				/* Daten f�r den Monatsbericht */
				$this->saldo_vormonat = $this->erg;
				$this->sollmiete_warm = $this->daten_arr [$a] [monate] [$m_zaehler] [soll];
				$m_zaehler ++;
			}
		}
	}
	
	/* Diese funktion liefert uns den aktuellen stand und den stand des Vormonats, inkl geleistete Zahlungen usw f�r den MOnatsbericht */
	function mietkonto_berechnung_monatsgenau($mietvertrag_id, $jahr, $monat) {
		$this->mietvertrag_id = $mietvertrag_id;
		$this->jahr_aktuell = $jahr;
		$this->monat_aktuell = $monat;
		$this->end_j = $jahr;
		$this->end_m = $monat; // zweistellig 03
		$this->erg = '0';
		
		/* Include mietkonto_class */
		$buchung = new mietkonto ();
		
		$datum_saldo_vv = $buchung->datum_saldo_vortrag_vorverwaltung ( $mietvertrag_id );
		/* wenn datum vom saldovv */
		if (! empty ( $datum_saldo_vv )) {
			$datum_saldo_vv = explode ( "-", $datum_saldo_vv );
			$this->saldo_vv = $buchung->saldo_vortrag_vorverwaltung ( $mietvertrag_id );
			$this->saldo_vv = number_format ( $this->saldo_vv, 2, '.', '' );
			// $this->saldo_vormonat = $this->saldo_vv;
			// if($this->saldo_vv>0.00){
			// $this->saldo_vv = '-'.$this->saldo_vv;
			// }
		}
		
		/* Sonst datum aus mietdefinition oder einzugsdatum aus mietvertrag */
		$datum_mietdefinition = $buchung->datum_1_mietdefinition ( $mietvertrag_id );
		$datum_mietdefinition1 = explode ( '-', $datum_mietdefinition );
		$datum_mietdefinition1 = implode ( '', $datum_mietdefinition1 );
		
		$datum1_zahlung = $buchung->datum_1_zahlung ( $mietvertrag_id );
		$datum1_zahlung1 = explode ( '-', $datum1_zahlung );
		$datum1_zahlung1 = implode ( '', $datum1_zahlung1 );
		/* echo "<h1>$datum_mietdefinition1 $datum1_zahlung1</h1>"; */
		/* Entscheiden welches ab welchem tag gerechnet werden soll */
		if ($datum_mietdefinition1 > $datum1_zahlung1) {
			$datum_mietdefinition = $datum1_zahlung;
		}
		/* Wenn miete �ber mietentwicklung definiert ist */
		if (! empty ( $datum_mietdefinition )) {
			// echo "DEFINITION $datum_mietdefinition";
			$datum_mietdefinition = explode ( "-", $datum_mietdefinition );
			$this->start_m = $datum_mietdefinition [1];
			$this->start_j = $datum_mietdefinition [0];
			$buchung->ein_auszugsdatum_mietvertrag ( $mietvertrag_id );
			if ($buchung->mietvertrag_bis != '0000-00-00') {
				$auszugsdatum = explode ( "-", $buchung->mietvertrag_bis );
				/* Falls MV abgelaufen nur bis zum aktuellen Monat berechnen */
				// $this->end_j = $auszugsdatum[0];
				$this->end_m = $this->monat_aktuell;
				$this->end_j = $this->jahr_aktuell;
			}
			/* Wenn Auszugsjahr kleiner als aktuelles Jahr UND mehr als 2 Jahre in Vergangenheit, dann nur 1 Jahr nach Auszug berechnen. */
			/*
			 * if($auszugsdatum[0] < $this->jahr_aktuell && $this->jahr_aktuell - $auszugsdatum[0] >1){
			 * $this->end_m = 12;
			 * $this->end_j = $auszugsdatum[0] +1;
			 * }
			 */
		} 		/* Sonst einzugsdatum ermitteln */
		else {
			$buchung->ein_auszugsdatum_mietvertrag ( $mietvertrag_id );
			$einzugsdatum = explode ( "-", $buchung->mietvertrag_von );
			$this->start_m = $einzugsdatum [1];
			$this->start_j = $einzugsdatum [0];
			/* Wenn MV befristet dann enddatum auch setzen */
			if ($buchung->mietvertrag_bis != '0000-00-00') {
				$auszugsdatum = explode ( "-", $buchung->mietvertrag_bis );
				// $this->end_m = $auszugsdatum[1];
				// $this->end_j = $auszugsdatum[0];
				$this->end_j = $jahr;
				$this->end_m = $monat;
			}
		}
		
		/* jahresschleife */
		for($a = $this->start_j; $a <= $this->end_j; $a ++) {
			/* anfangs und endjahr gleich */
			if ($a == $this->start_j && $a == $this->end_j) {
				$start_m = $this->start_m;
				$end_m = $this->end_m;
			}
			/* voll jahre dazwischen */
			if ($a > $this->start_j && $a < $this->end_j) {
				$start_m = 1;
				$end_m = 12;
			}
			/* erstjahr */
			if ($a == $this->start_j && $a != $this->end_j) {
				$start_m = $this->start_m;
				$end_m = 12;
			}
			/* endjahr */
			if ($a == $this->end_j && $a != $this->start_j) {
				$start_m = 1;
				$end_m = $this->end_m;
			}
			/* monatsschleife */
			$m_zaehler = 0;
			if ($start_m < 10) {
				$start_m = substr ( $start_m, - 1 );
			}
			for($b = $start_m; $b <= $end_m; $b ++) {
				
				if ($m_zaehler == 0 && $a == $this->start_j) {
					$this->saldo_vormonat = $this->saldo_vv;
				}
				
				$this->bk_abrechnung = $buchung->summe_betriebskostenabrechnung ( $mietvertrag_id, $b, $a );
				$this->hk_abrechnung = $buchung->summe_heizkostenabrechnung ( $mietvertrag_id, $b, $a );
				
				$this->temp_soll = '-' . $buchung->summe_forderung_monatlich ( $this->mietvertrag_id, $b, $a );
				
				$ford_monatlich_arr = $buchung->forderung_monatlich ( $mietvertrag_id, $b, $a );
				$this->davon_umlagen = $buchung->summe_vorschuesse ( $ford_monatlich_arr );
				
				// echo $mietvertrag_id.$b.$a.$this->temp_soll.' '.$this->saldo_vormonat.'<br>';
				
				$this->daten_arr [$a] [monate] [$m_zaehler] [zahlungen] = $buchung->zahlbetraege_im_monat_arr ( $mietvertrag_id, $b, $a );
				// echo "<h1> $b $a</h1><br>";
				/*
				 * $this->daten_arr[$a][monate][$m_zaehler][zahlungen][0][txt]= 'zb';
				 * $this->daten_arr[$a][monate][$m_zaehler][zahlungen][0][b]= $this->temp_zb;
				 * $this->daten_arr[$a][monate][$m_zaehler][zahlungen][1][txt]= 'zb';
				 * $this->daten_arr[$a][monate][$m_zaehler][zahlungen][1][b]= $this->temp_zb;
				 */
				$sum_mon = 0;
				$z_arr = $this->daten_arr [$a] [monate] [$m_zaehler] [zahlungen];
				for($c = 0; $c < count ( $z_arr ); $c ++) {
					$sum_mon = $sum_mon + $z_arr [$c] [BETRAG];
				}
				$this->geleistete_zahlungen = $sum_mon;
				
				$sum_mon = number_format ( $sum_mon, 2, '.', '' );
				
				$this->erg = $this->saldo_vormonat + $this->temp_soll + $sum_mon + $this->hk_abrechnung + $this->bk_abrechnung;
				
				$this->bk_abrechnung = '0.00';
				$this->hk_abrechnung = '0.00';
				$this->erg = number_format ( $this->erg, 2, '.', '' );
				$this->saldo_vormonat_stand = $this->saldo_vormonat;
				/* Daten f�r den Monatsbericht */
				$this->saldo_vormonat = $this->erg;
				$this->sollmiete_warm = substr ( $this->temp_soll, 1 );
				$m_zaehler ++;
			}
		}
	}
	
	/*
	 * function insert_saldos(){
	 * $result = mysql_query ("SELECT MIETVERTRAG_ID FROM MIETVERTRAG WHERE MIETVERTRAG_AKTUELL='1' ORDER BY MIETVERTRAG_ID ASC");
	 *
	 * while ($row = mysql_fetch_assoc($result)){
	 * $mv_id= $row[MIETVERTRAG_ID];
	 * $db_abfrage = "INSERT INTO MIETE_ZAHLBETRAG VALUES (NULL, '11111', '$mv_id', '2006-12-30', '500.00', 'Saldo Vortrag Vorverwaltung', '1', '11')";
	 * $resultat = mysql_query($db_abfrage) or
	 * die(mysql_error());
	 * }
	 *
	 * }
	 */
	
	/* Gegen�berdarstellung von intern und extern buchung */
	function mietbuchung_intern_extern_anzeigen($mietvertrag_id) {
		$result = mysql_query ( "SELECT ( SELECT sum(BETRAG) FROM MIETE_ZAHLBETRAG WHERE MIETVERTRAG_ID='$mietvertrag_id' ) AS ZB,  ( SELECT sum(BETRAG) FROM MIETBUCHUNGEN WHERE  MIETVERTRAG_ID='$mietvertrag_id' ) AS INTERN" );
		$row = mysql_fetch_assoc ( $result );
		// return $my_array;
		$miete_zahlbetrag_summe = number_format ( $row [ZB], 2, ",", "" );
		$miete_intern_summe = number_format ( $row [INTERN], 2, ",", "" );
		
		echo "<h1>ZB:$miete_zahlbetrag_summe � INT:$miete_intern_summe �</h1>";
	}
	function mietkonten_blatt_anzeigen($mv_id) {
		$a = new miete ();
		$a->mietkonto_berechnung ( $mv_id );
		/*
		 * echo "<pre>";
		 * print_r($a);
		 * echo "</pre>";
		 */
		$buchung = new mietkonto ();
		/* Mieterinfo anfang */
		$einheit_id = $buchung->get_einheit_id_von_mietvertrag ( $mv_id );
		$einheit_info = new einheit ();
		$einheit_info->get_einheit_info ( $einheit_id );
		$mieter_ids = $buchung->get_personen_ids_mietvertrag ( $mv_id );
		for($i = 0; $i < count ( $mieter_ids ); $i ++) {
			$mieter_daten_arr [] = $buchung->get_person_infos ( $mieter_ids [$i] [PERSON_MIETVERTRAG_PERSON_ID] );
		}
		/* Mieterinfo ende */
		
		/* MV INFO */
		$buchung->ein_auszugsdatum_mietvertrag ( $mv_id );
		$einzugsdatum = explode ( "-", $buchung->mietvertrag_von );
		$einzugs_jahr = $einzugsdatum ['0'];
		$einzugs_monat = $einzugsdatum ['1'];
		
		$auszugsdatum = explode ( "-", $buchung->mietvertrag_bis );
		$auszugs_jahr = $auszugsdatum ['0'];
		$auszugs_monat = $auszugsdatum ['1'];
		
		/* Regel wenn es ein Berechnungsergebnis gibt, d.h. miete definiert und berechnet, falls nicht auch nicht anzeigen, da in wahrscheinlich in Zukunft */
		if (! empty ( $a->erg )) {
			
			// $buchung->erstelle_formular("Mietkonten�bersicht...", NULL);
			echo "<div id=\"logo\"><img src=\"grafiken/logo43_19.png\"/></div>";
			
			$a->erg = number_format ( $a->erg, 2, ",", "" );
			// echo "<tr><td colspan=4><h1>SALDO $a->erg �</h1>";
			
			echo "<table class=aktuelle_buchungen>";
			echo "<thead>";
			echo "<tr>";
			echo "<th scopr=\"col\" colspan=4 align=left>Einheit $einheit_info->einheit_kurzname <br>";
			for($i = 0; $i < count ( $mieter_daten_arr ); $i ++) {
				$mieternr = $i + 1;
				echo "$mieternr. " . $mieter_daten_arr [$i] [0] [PERSON_VORNAME] . " " . $mieter_daten_arr [$i] [0] [PERSON_NACHNAME] . " ";
			}
			
			echo "</th>";
			
			echo "</tr><tr>";
			echo "<th scopr=\"col\">Datum</th>";
			echo "<th scopr=\"col\">Monatssoll</th>";
			echo "<th scopr=\"col\">Zahlung</th>";
			echo "<th scopr=\"col\">Saldo</th>";
			echo "</tr>";
			echo "</thead>";
			
			// echo "<tr><td><b>Datum</b></td><td><b>Monatssoll</b></td><td><b>Zahlung</b></td><td><b>Saldo</b></td></tr>";
			if (! empty ( $a->saldo_vv )) {
				$saldo_vv = number_format ( $a->saldo_vv, 2, ",", "" );
				echo "<tr><td></td><td colspan=2 align=left><h2 class=\"saldo_vv\">SALDO VORTRAG VORVERWALTUNG</h2></td><td><b>$saldo_vv �</b></td></tr>";
			}
			
			foreach ( $a->daten_arr as $key => $value ) {
				for($b = 0; $b < count ( $a->daten_arr [$key] [monate] ); $b ++) {
					// Miete Sollzeile
					$akt_monat = sprintf ( "%02d", $a->daten_arr [$key] [monate] [$b] [monat] );
					if ($a->daten_arr [$key] [monate] [$b] [soll] < '0.00') {
						$soll_aus_mv = number_format ( $a->daten_arr [$key] [monate] [$b] [soll], 2, ",", "" );
						echo "<tr><td>01.$akt_monat.$key</td><td>Soll aus Mietvertrag " . $a->daten_arr [$key] [monate] [$b] [monat] . ".$key </td><td><b>$soll_aus_mv �</b></td><td></td></tr>";
					}
					$bk_abrechnung = $a->daten_arr [$key] [monate] [$b] [bk_abrechnung];
					if (! empty ( $bk_abrechnung )) {
						/*
						 * if($bk_abrechnung < '0.00'){
						 * $bk_abrechnung = abs($bk_abrechnung);
						 * }
						 * else{
						 * $bk_abrechnung = "-$bk_abrechnung";
						 * }
						 */
						
						$bk_abrechnung = number_format ( $bk_abrechnung, 2, ",", "" );
						$datum_bk = $a->daten_arr [$key] [monate] [$b] [bk_abrechnung_datum];
						echo "<tr><td>$datum_bk</td><td><b>Betriebskostenabrechung</b> </td><td><b>$bk_abrechnung �</b></td><td></td></tr>";
					}
					$hk_abrechnung = $a->daten_arr [$key] [monate] [$b] [hk_abrechnung];
					if (! empty ( $hk_abrechnung )) {
						/*
						 * if($hk_abrechnung < '0.00'){
						 * $hk_abrechnung = abs($hk_abrechnung);
						 * }
						 * else{
						 * $hk_abrechnung = "-$hk_abrechnung";
						 * }
						 */
						$hk_abrechnung = number_format ( $hk_abrechnung, 2, ",", "" );
						$datum_hk = $a->daten_arr [$key] [monate] [$b] [hk_abrechnung_datum];
						echo "<tr><td>$datum_hk</td><td><b>Heizkostenabrechung</b> </td><td><b>$hk_abrechnung �</b></td><td></td></tr>";
					}
					/* Zeilen Zahlungen */
					if (! is_array ( $a->daten_arr [$key] [monate] [$b] [zahlungen] )) {
						if ($a->daten_arr [$key] [monate] [$b] [soll] != '-0.00') {
							if ($b > 1) {
								$cb = $b - 1;
							} else {
								$cb = 12;
							}
							// $a->saldo_vormonat +
							$akt_saldo_nz = nummer_punkt2komma ( $a->daten_arr [$key] [monate] [$cb] [erg] + $a->daten_arr [$key] [monate] [$b] [soll] );
							echo "<tr><td></td><td>Keine Zahlung</td><td></td><td><b>$akt_saldo_nz �</b></td></tr>\n";
						}
					} else {
						for($c = 0; $c < count ( $a->daten_arr [$key] [monate] [$b] [zahlungen] ); $c ++) {
							$datum = date_mysql2german ( $a->daten_arr [$key] [monate] [$b] [zahlungen] [$c] [DATUM] );
							$zahlbetrag_ausgabe = number_format ( $a->daten_arr [$key] [monate] [$b] [zahlungen] [$c] [BETRAG], 2, ",", "" );
							echo "<tr><td>$datum</td><td>" . $a->daten_arr [$key] [monate] [$b] [zahlungen] [$c] [BEMERKUNG] . "</td><td>$zahlbetrag_ausgabe �</td><td></td></tr>\n";
						} // end for
					}
					/* Saldo am ende des Monats */
					$saldo_aus = number_format ( $a->daten_arr [$key] [monate] [$b] [erg], 2, ",", "" );
					$letzter_tag = date ( "t", mktime ( 0, 0, 0, "" . $a->daten_arr [$key] [monate] [$b] [monat] . "", 1, $key ) );
					/* Letzter d.h. Aktueller Monat */
					if (is_array ( $a->daten_arr [$key] [monate] [$b] [zahlungen] )) {
						if ($a->daten_arr [$key] [monate] [$b] [monat] == date ( "m" ) && $b == date ( "Y" )) {
							$tag_heute = date ( "d" );
							echo "<tr><td>$tag_heute.$akt_monat.$key</td><td>SALDO " . $a->daten_arr [$key] [monate] [$b] [monat] . ".$key </td><td></td><td><b>$saldo_aus �</b></td></tr>";
						} else {
							echo "<tr><td>$letzter_tag.$akt_monat.$key</td><td>SALDO " . $a->daten_arr [$key] [monate] [$b] [monat] . ".$key </td><td></td><td><b>$saldo_aus �</b></td></tr>";
						}
					}
				} // ende for monate
			} // end foreach
			/* Letzte Zeile �berhaupt */
			$tag_heute = date ( "d" );
			echo "<tr><td><b>$tag_heute.$akt_monat.$key</b></td><td><b>Aktuell</b></td><td></td><td><b>$saldo_aus �</b></td></tr>";
			echo "</table>\n";
		}  // Ende if if(!empty($a->erg)){
else {
			echo "<h1>Keine Berechnungsgrundlage f�r das Mietkonto</h1>";
			echo "<h1>Einzugsdatum, Mietdefinition �berpr�fen</h1>";
		}
		
		// $buchung->ende_formular();
	}
	function mietkonten_blatt_balken($mv_id) {
		$a = new miete ();
		$a->mietkonto_berechnung ( $mv_id );
		/*
		 * echo "<pre>";
		 * print_r($a);
		 * echo "</pre>";
		 */
		$buchung = new mietkonto ();
		/* Mieterinfo anfang */
		$einheit_id = $buchung->get_einheit_id_von_mietvertrag ( $mv_id );
		$einheit_info = new einheit ();
		$einheit_info->get_einheit_info ( $einheit_id );
		$mieter_ids = $buchung->get_personen_ids_mietvertrag ( $mv_id );
		for($i = 0; $i < count ( $mieter_ids ); $i ++) {
			$mieter_daten_arr [] = $buchung->get_person_infos ( $mieter_ids [$i] [PERSON_MIETVERTRAG_PERSON_ID] );
		}
		/* Mieterinfo ende */
		
		/* Regel wenn es ein Berechnungsergebnis gibt, d.h. miete definiert und berechnet, falls nicht auch nicht anzeigen, da in wahrscheinlich in Zukunft */
		if (! empty ( $a->erg )) {
			
			// $buchung->erstelle_formular("Mietkonten�bersicht...", NULL);
			// echo "<div id=\"logo\"><img src=\"grafiken/logo43_19.png\"/></div>";
			
			$a->erg = number_format ( $a->erg, 2, ",", "" );
			
			if (! empty ( $a->saldo_vv )) {
				$saldo_vv = number_format ( $a->saldo_vv, 2, ",", "" );
				echo "SALDO VV - $saldo_vv �<br>";
			}
			foreach ( $a->daten_arr as $key => $value ) {
				for($b = 0; $b < count ( $a->daten_arr [$key] [monate] ); $b ++) {
					// Miete Sollzeile
					$akt_monat = sprintf ( "%02d", $a->daten_arr [$key] [monate] [$b] [monat] );
					$soll_aus_mv = number_format ( $a->daten_arr [$key] [monate] [$b] [soll], 2, ",", "" );
					echo "01.$akt_monat.$key | Soll " . $a->daten_arr [$key] [monate] [$b] [monat] . ".$key | $soll_aus_mv �<br>";
					$bk_abrechnung = $a->daten_arr [$key] [monate] [$b] [bk_abrechnung];
					if (! empty ( $bk_abrechnung )) {
						/*
						 * if($bk_abrechnung < '0.00'){
						 * $bk_abrechnung = abs($bk_abrechnung);
						 * }
						 * else{
						 * $bk_abrechnung = "-$bk_abrechnung";
						 * }
						 */
						
						$bk_abrechnung = number_format ( $bk_abrechnung, 2, ",", "" );
						$datum_bk = $a->daten_arr [$key] [monate] [$b] [bk_abrechnung_datum];
						echo "$datum_bk | BK | <b>$bk_abrechnung �</b><br>";
					}
					$hk_abrechnung = $a->daten_arr [$key] [monate] [$b] [hk_abrechnung];
					if (! empty ( $hk_abrechnung )) {
						/*
						 * if($hk_abrechnung < '0.00'){
						 * $hk_abrechnung = abs($hk_abrechnung);
						 * }
						 * else{
						 * $hk_abrechnung = "-$hk_abrechnung";
						 * }
						 */
						$hk_abrechnung = number_format ( $hk_abrechnung, 2, ",", "" );
						$datum_hk = $a->daten_arr [$key] [monate] [$b] [hk_abrechnung_datum];
						echo "$datum_hk | HK | $hk_abrechnung �<br>";
					}
					/* Zeilen Zahlungen */
					if (! is_array ( $a->daten_arr [$key] [monate] [$b] [zahlungen] )) {
						echo "<b>Keine Zahlung</b><br>\n";
					} else {
						for($c = 0; $c < count ( $a->daten_arr [$key] [monate] [$b] [zahlungen] ); $c ++) {
							$datum = date_mysql2german ( $a->daten_arr [$key] [monate] [$b] [zahlungen] [$c] [DATUM] );
							$zahlbetrag_ausgabe = number_format ( $a->daten_arr [$key] [monate] [$b] [zahlungen] [$c] [BETRAG], 2, ",", "" );
							echo " $datum | " . $a->daten_arr [$key] [monate] [$b] [zahlungen] [$c] [BEMERKUNG] . " | $zahlbetrag_ausgabe �<br>";
						}
					}
					/* Saldo am ende des Monats */
					$saldo_aus = number_format ( $a->daten_arr [$key] [monate] [$b] [erg], 2, ",", "" );
					$letzter_tag = date ( "t", mktime ( 0, 0, 0, "" . $a->daten_arr [$key] [monate] [$b] [monat] . "", 1, $key ) );
					/* Letzter d.h. Aktueller Monat */
					if ($a->daten_arr [$key] [monate] [$b] [monat] == date ( "m" )) {
						$tag_heute = date ( "d" );
						// echo "$tag_heute.$akt_monat.$key | SALDO ".$a->daten_arr[$key][monate][$b][monat].".$key |<b>$saldo_aus �";
						/* Linie zwischen den monaten */
						echo "<hr>";
					} else {
						// echo "$letzter_tag.$akt_monat.$key | SALDO ".$a->daten_arr[$key][monate][$b][monat].".$key | $saldo_aus �";
						/* Linie zwischen den monaten */
						echo "<hr>";
					}
				}
			}
		}  // Ende if if(!empty($a->erg)){
else {
			echo "<h1>Keine Berechnungsgrundlage f�r das Mietkonto</h1>";
			echo "<h1>Einzugsdatum, Mietdefinition �berpr�fen</h1>";
		}
		
		// $buchung->ende_formular();
	}
	function berechnen() {
		$mv_id = 3;
		$result = mysql_query ( "SELECT ANFANG, ENDE, KOSTENKATEGORIE, BETRAG FROM MIETENTWICKLUNG WHERE MIETENTWICKLUNG_AKTUELL='1' && KOSTENTRAEGER_TYP='MIETVERTRAG' && KOSTENTRAEGER_ID='$mv_id' " );
		
		$numrows = mysql_numrows ( $result );
		if ($numrows > 0) {
			while ( $row = mysql_fetch_assoc ( $result ) )
				$my_arr1 [] = $row;
		}
		
		echo "<pre>";
		// print_r($my_arr1);
		
		$einzug = '2008-03-01';
		$auszug = date ( "Y-m-d" );
		$monate = $this->diff_in_monaten ( $einzug, $auszug );
		// +$my_arr1 = test_arr(); //array aus ME
		
		$saldo_neu = 0;
		
		for($index = 0; $index < count ( $my_arr1 ); $index ++) {
			$anfang = $my_arr1 [$index] ['ANFANG'];
			$ende = $my_arr1 [$index] ['ENDE'];
			$betrag = $my_arr1 [$index] ['BETRAG'];
			$kostenkat = $my_arr1 [$index] ['KOSTENKATEGORIE'];
			
			if ($ende = '0000-00-00') {
				$ende = '';
			}
			$monate_fallig_me = $this->diff_in_monaten ( $anfang, $ende );
			if ($monate_fallig_me < $monate) {
				$gesamt_betrag = $monate_fallig_me * $betrag;
			} else {
				$gesamt_betrag = $monate * $betrag;
			}
			$saldo_neu = $saldo_neu + $gesamt_betrag;
		}
		
		echo "<br><h1><b>$saldo_neu</b></h1><br>";
		
		$result = mysql_query ( "SELECT SUM(BETRAG) AS SUMME FROM GELD_KONTO_BUCHUNGEN WHERE AKTUELL='1' && KOSTENTRAEGER_TYP='MIETVERTRAG' && KOSTENTRAEGER_ID='$mv_id' " );
		
		$numrows = mysql_numrows ( $result );
		if ($numrows > 0) {
			$row = mysql_fetch_assoc ( $result );
			$summe_eingezahlt = $row ['SUMME'];
		}
		$b = $summe_eingezahlt - $saldo_neu;
		echo "<h1>$b</h1>";
	}
	
	/*
	 *
	 * diff_in_monaten('2009-01-01', '');
	 *
	 */
	function diff_in_monaten($einzug, $auszug) {
		if ($auszug == '0000-00-00' or empty ( $auszug )) {
			$auszug = date ( "Y-m-d" );
		}
		
		$einzug = explode ( '-', $einzug );
		$monat_einzug = $einzug ['1'];
		$jahr_einzug = $einzug ['0'];
		
		$auszug = explode ( '-', $auszug );
		$monat_auszug = $auszug ['1'];
		$jahr_auszug = $auszug ['0'];
		
		$diff_jahr = $jahr_auszug - $jahr_einzug;
		
		if ($diff_jahr < 1) {
			$diff_monate = $monat_auszug - $monat_einzug + 1;
		} else {
			$diff_monate = (13 - $monat_einzug) + $monat_auszug;
			if ($diff_jahr > 1) {
				$diff_monate = $diff_monate + (($diff_jahr - 1) * 12);
			}
		}
		// echo $diff_monate;
		return $diff_monate;
	}
	function get_sum_kostenkat_jahr($kos_typ, $kos_id, $jahr, $kostenkat) {
		
		/*
		 * 'SELECT NEW_ANFANG, NEW_ENDE, PERIOD_DIFF(DATE_FORMAT(NEW_ENDE,'%Y%m'), DATE_FORMAT(NEW_ANFANG,'%Y%m'))+1 AS MONATE, BETRAG FROM (
		 *
		 * SELECT KOSTENKATEGORIE, BETRAG, ANFANG, ENDE,
		 * IF(ANFANG<'2009-01-01' && (ENDE >='2009-01-01' OR ENDE='0000-00-00') , '2009-01-01', ANFANG) AS NEW_ANFANG,
		 * IF(ENDE='0000-00-00', '2009-12-31', ENDE) AS NEW_ENDE
		 *
		 *
		 * FROM MIETENTWICKLUNG WHERE MIETENTWICKLUNG_AKTUELL='1' && `KOSTENTRAEGER_TYP`='MIETVERTRAG' && `KOSTENTRAEGER_ID`='1' && KOSTENKATEGORIE='Nebenkosten Vorauszahlung' && (ENDE>'2009-01-01' OR ENDE='0000-00-00')
		 * ORDER BY `NEW_ENDE` ASC) as t1
		 */
		
		/*
		 * SELECT SUM(PERIOD_DIFF(DATE_FORMAT(NEW_ENDE,'%Y%m'), DATE_FORMAT(NEW_ANFANG,'%Y%m')) * BETRAG) G_B,PERIOD_DIFF(DATE_FORMAT(NEW_ENDE,'%Y%m'), DATE_FORMAT(NEW_ANFANG,'%Y%m'))AS MONATE, BETRAG FROM (
		 *
		 * SELECT KOSTENKATEGORIE, BETRAG, ANFANG, ENDE,
		 * IF(ANFANG<'2009-01-01' && (ENDE >='2009-01-01' OR ENDE='0000-00-00') , '2009-01-01', ANFANG) AS NEW_ANFANG,
		 * IF(ENDE='0000-00-00', '2009-12-31', ENDE) AS NEW_ENDE
		 *
		 *
		 * FROM MIETENTWICKLUNG WHERE MIETENTWICKLUNG_AKTUELL='1' && `KOSTENTRAEGER_TYP`='MIETVERTRAG' && `KOSTENTRAEGER_ID`='1' && KOSTENKATEGORIE='Nebenkosten Vorauszahlung' && (ENDE>'2009-01-01' OR ENDE='0000-00-00')
		 * ORDER BY `NEW_ENDE` ASC) as t1 GROUP BY NEW_ANFANG
		 */
		
		/* Liefert nur Nur summe */
		/*
		 * SELECT SUM(t2.G_B) FROM (
		 * SELECT SUM(PERIOD_DIFF(DATE_FORMAT(NEW_ENDE,'%Y%m'), DATE_FORMAT(NEW_ANFANG,'%Y%m')) * BETRAG) G_B,PERIOD_DIFF(DATE_FORMAT(NEW_ENDE,'%Y%m'), DATE_FORMAT(NEW_ANFANG,'%Y%m'))AS MONATE, BETRAG FROM (
		 *
		 * SELECT KOSTENKATEGORIE, BETRAG, ANFANG, ENDE,
		 * IF(ANFANG<'2009-01-01' && (ENDE >='2009-01-01' OR ENDE='0000-00-00') , '2009-01-01', ANFANG) AS NEW_ANFANG,
		 * IF(ENDE='0000-00-00', '2009-12-31', ENDE) AS NEW_ENDE
		 *
		 *
		 * FROM MIETENTWICKLUNG WHERE MIETENTWICKLUNG_AKTUELL='1' && `KOSTENTRAEGER_TYP`='MIETVERTRAG' && `KOSTENTRAEGER_ID`='1' && KOSTENKATEGORIE='Nebenkosten Vorauszahlung' && (ENDE>'2009-01-01' OR ENDE='0000-00-00')
		 * ORDER BY `NEW_ENDE` ASC) as t1 GROUP BY NEW_ANFANG) as t2
		 */
	}
	
	/*
	 * SELECT DATEDIFF( NEW_ENDE, NEW_ANFANG ) +1 AS TAGE, NEW_ANFANG, NEW_ENDE, KOSTENKATEGORIE, BETRAG, ANFANG, ENDE
	 * FROM (
	 *
	 * SELECT KOSTENKATEGORIE, BETRAG, ANFANG, ENDE, IF( ANFANG < '2009-01-01' && ( ENDE >= '2009-01-01'
	 * OR ENDE = '0000-00-00' ) , '2009-01-01', ANFANG ) AS NEW_ANFANG, IF( ENDE = '0000-00-00', '2009-12-31', ENDE ) AS NEW_ENDE
	 * FROM MIETENTWICKLUNG
	 * WHERE MIETENTWICKLUNG_AKTUELL = '1' && `KOSTENTRAEGER_TYP` = 'MIETVERTRAG' && `KOSTENTRAEGER_ID` = '8' && KOSTENKATEGORIE = 'Nebenkosten Vorauszahlung' && (
	 * ENDE > '2009-01-01'
	 * OR ENDE = '0000-00-00'
	 * )
	 * ORDER BY `NEW_ENDE` ASC
	 * ) AS tage
	 */
	
	/* Falsch wegen durchschnitt 30.417 (365/12) */
	function summe_nebenkosten_im_jahr_schnitt($kos_typ, $kos_id, $jahr) {
		$db_abfrage = " SELECT SUM( M_SUMME ) AS J_SUMME FROM ( SELECT ((DATEDIFF( NEW_ENDE, NEW_ANFANG ) +1) / '30.417') * BETRAG AS M_SUMME, NEW_ANFANG, NEW_ENDE, KOSTENKATEGORIE, BETRAG FROM (SELECT KOSTENKATEGORIE, BETRAG, ANFANG, ENDE, IF( ANFANG < '$jahr-01-01' && ( ENDE >= '$jahr-01-01' OR ENDE = '0000-00-00' ) , '$jahr-01-01', ANFANG ) AS NEW_ANFANG, IF( ENDE = '0000-00-00', '$jahr-12-31', ENDE ) AS NEW_ENDE FROM MIETENTWICKLUNG WHERE MIETENTWICKLUNG_AKTUELL = '1' && `KOSTENTRAEGER_TYP` = '$kos_typ' && `KOSTENTRAEGER_ID` = '$kos_id' && KOSTENKATEGORIE = 'Nebenkosten Vorauszahlung' && (
ENDE > '$jahr-01-01' OR ENDE = '0000-00-00') ORDER BY `NEW_ENDE` ASC) AS tage) AS J ";
		
		$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		
		$row = mysql_fetch_assoc ( $resultat );
		return $row ['J_SUMME'];
	}
	function summe_nebenkosten_im_jahr($kos_typ, $kos_id, $jahr) {
		$db_abfrage = " SELECT SUM( MONATLICH ) AS SUMME FROM (
SELECT NEW_ANFANG, NEW_ENDE, BETRAG, (
PERIOD_DIFF( DATE_FORMAT( NEW_ENDE, '%Y%m' ) , DATE_FORMAT( NEW_ANFANG, '%Y%m' ) ) +1
) * BETRAG AS MONATLICH
FROM (

SELECT KOSTENKATEGORIE, BETRAG, ANFANG, ENDE, IF( ANFANG < '$jahr-01-01' && ( ENDE >= '$jahr-01-01'
OR ENDE = '0000-00-00' ) , '$jahr-01-01', ANFANG ) AS NEW_ANFANG, IF( ENDE = '0000-00-00' OR ENDE>'$jahr-12-31', '$jahr-12-31', ENDE ) AS NEW_ENDE
FROM MIETENTWICKLUNG
WHERE MIETENTWICKLUNG_AKTUELL = '1' && `KOSTENTRAEGER_TYP` = '$kos_typ' && `KOSTENTRAEGER_ID` = '$kos_id' && KOSTENKATEGORIE = 'Nebenkosten Vorauszahlung' && ( ENDE > '$jahr-01-01'
OR ENDE = '0000-00-00' )
ORDER BY `NEW_ENDE` ASC
) AS t1
) AS t2 ";
		
		$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		
		$row = mysql_fetch_assoc ( $resultat );
		return $row ['SUMME'];
	}
	function letzte_hk_vorauszahlung($kos_typ, $kos_id, $jahr, $kostenkat) {
		$db_abfrage = "SELECT KOSTENKATEGORIE, BETRAG, ANFANG, ENDE, IF( ANFANG < '$jahr-01-01' && ( ENDE >= '$jahr-01-01'
OR ENDE = '0000-00-00' ) , '$jahr-01-01', ANFANG ) AS NEW_ANFANG, IF( ENDE = '0000-00-00' OR ENDE>'$jahr-12-31', '$jahr-12-31', ENDE ) AS NEW_ENDE
FROM MIETENTWICKLUNG
WHERE MIETENTWICKLUNG_AKTUELL = '1' && `KOSTENTRAEGER_TYP` = '$kos_typ' && `KOSTENTRAEGER_ID` = '$kos_id' && KOSTENKATEGORIE = '$kostenkat' && ( DATE_FORMAT(ENDE,'%Y') >= '$jahr'
OR ENDE = '0000-00-00' ) && DATE_FORMAT(ANFANG, '%Y') <= '$jahr'
 ORDER BY `NEW_ENDE` DESC LIMIT 0,1 ";
		
		$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		
		$row = mysql_fetch_assoc ( $resultat );
		return $row ['BETRAG'];
	}
	function summe_heizkosten_im_jahr($kos_typ, $kos_id, $jahr) {
		$db_abfrage = " SELECT SUM( MONATLICH ) AS SUMME FROM (
SELECT NEW_ANFANG, NEW_ENDE, BETRAG, (
PERIOD_DIFF( DATE_FORMAT( NEW_ENDE, '%Y%m' ) , DATE_FORMAT( NEW_ANFANG, '%Y%m' ) ) +1
) * BETRAG AS MONATLICH
FROM (

SELECT KOSTENKATEGORIE, BETRAG, ANFANG, ENDE, IF( ANFANG < '$jahr-01-01' && ( ENDE >= '$jahr-01-01'
OR ENDE = '0000-00-00' ) , '$jahr-01-01', ANFANG ) AS NEW_ANFANG, IF( ENDE = '0000-00-00' OR ENDE>'$jahr-12-31', '$jahr-12-31', ENDE ) AS NEW_ENDE
FROM MIETENTWICKLUNG
WHERE MIETENTWICKLUNG_AKTUELL = '1' && `KOSTENTRAEGER_TYP` = '$kos_typ' && `KOSTENTRAEGER_ID` = '$kos_id' && KOSTENKATEGORIE = 'Heizkosten Vorauszahlung' && ( ENDE > '$jahr-01-01'
OR ENDE = '0000-00-00' )
ORDER BY `NEW_ENDE` ASC
) AS t1
) AS t2 ";
		
		$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		
		$row = mysql_fetch_assoc ( $resultat );
		return $row ['SUMME'];
	}
	function sanel_neu_ok() {
		/* Z�hlt Monatsdiff zwischen anfang und ende der mietdefinition, falls ende =0000-00-00 dann bis akt monat */
		$this->saldo_vv = 0;
		$this->gesamt_soll = 0;
		$db_abfrage = "SELECT KOSTENKATEGORIE, BETRAG, ANFANG, ENDE, IF(ENDE='0000-00-00', CURDATE(), ENDE) AS NEW_ENDE, 

IF(ENDE!='0000-00-00',
period_diff(concat(year(ENDE),
if(month(ENDE)<10,'0',''),month(ENDE)),
concat(year(ANFANG), if(month(ANFANG)<10,'0',''),month(ANFANG)))+1, 

period_diff(concat(year(CURDATE()),
if(month(CURDATE())<10,'0',''), month(CURDATE())),
concat(year(ANFANG),if(month(ANFANG)<10,'0',''), month(ANFANG)))+1) AS MJESECI


FROM MIETENTWICKLUNG WHERE MIETENTWICKLUNG_AKTUELL='1' && `KOSTENTRAEGER_TYP`='MIETVERTRAG' && `KOSTENTRAEGER_ID`='97' 
   ORDER BY `MJESECI`  ASC";
		$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		
		while ( $row = mysql_fetch_assoc ( $resultat ) ) {
			print_r ( $row );
			if ($row ['KOSTENKATEGORIE'] == 'Saldo Vortrag Vorverwaltung') {
				$this->saldo_vv = $this->saldo_vv + $row ['BETRAG'];
			} else {
				$this->gesamt_soll = $this->gesamt_soll + ($row ['BETRAG'] * $row ['MJESECI']);
			}
			// print_r($my_arr);
		}
	}
	function saldo_berechnen($mv_id) {
		$db_abfrage = "SELECT KOSTENKATEGORIE, BETRAG * MJESECI AS GESAMT FROM (SELECT KOSTENKATEGORIE, BETRAG, ANFANG, ENDE, IF(ENDE='0000-00-00', CURDATE(), ENDE) AS NEW_ENDE, 
IF(ENDE!='0000-00-00',
period_diff(concat(year(ENDE),
if(month(ENDE)<10,'0',''),month(ENDE)),
concat(year(ANFANG), if(month(ANFANG)<10,'0',''),month(ANFANG)))+1, 

period_diff(concat(year(CURDATE()),
if(month(CURDATE())<10,'0',''), month(CURDATE())),
concat(year(ANFANG),if(month(ANFANG)<10,'0',''), month(ANFANG)))+1) AS MJESECI


FROM MIETENTWICKLUNG WHERE MIETENTWICKLUNG_AKTUELL='1' && `KOSTENTRAEGER_TYP`='MIETVERTRAG' && `KOSTENTRAEGER_ID`='$mv_id' 
   ORDER BY `MJESECI`  ASC) AS t1";
		$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		
		$g_forderung_summe = 0;
		while ( $row = mysql_fetch_assoc ( $resultat ) ) {
			$kostenkat = $row ['KOSTENKATEGORIE'];
			$betrag = $row ['GESAMT'];
			if (preg_match ( "/Betriebskostenabrechnung/i", $kostenkat )) {
				if ($betrag < 0) {
					$betrag = abs ( $betrag );
				} else {
					$betrag = '-' . $betrag;
				}
			}
			
			if (preg_match ( "/Heizkostenabrechnung/i", $kostenkat )) {
				if ($betrag < 0) {
					$betrag = abs ( $betrag );
				} else {
					$betrag = '-' . $betrag;
				}
			}
			
			if (preg_match ( "/Wasserkostenabrechnung/i", $kostenkat )) {
				if ($betrag < 0) {
					$betrag = abs ( $betrag );
				} else {
					$betrag = '-' . $betrag;
				}
			}
			
			if (preg_match ( "/Saldo Vortrag Vorverwaltung/i", $kostenkat )) {
				if ($betrag < 0) {
					$betrag = abs ( $betrag );
				} else {
					$betrag = '-' . $betrag;
				}
			}
			
			if (preg_match ( "/Mahngeb�hr/i", $kostenkat )) {
				if ($betrag < 0) {
					$betrag = abs ( $betrag );
				} else {
					$betrag = '-' . $betrag;
				}
			}
			
			$g_forderung_summe = $g_forderung_summe + $betrag;
		}
		
		// return $g_forderung_summe;
		// $b = new mietkonto;
		// $summe_zahlbetrag = $b->summe_aller_zahlbetraege($mv_id);
		$b = new buchen ();
		$mv = new mietvertraege ();
		$mv->get_mietvertrag_infos_aktuell ( $mv_id );
		$o = new objekt ();
		$o->objekt_informationen ( $mv->objekt_id );
		$geldkonto_id = $o->geld_konten_arr [0] ['KONTO_ID'];
		$b->summe_buchungen_kostenkonto_bis_heute ( $geldkonto_id, '80001', 'Mietvertrag', $mv_id );
		
		$summe_zahlbetrag = number_format ( $summe_zahlbetrag, 2, '.', '' );
		$g_forderung_summe = number_format ( $g_forderung_summe, 2, '.', '' );
		number_format ( $this->saldo_vv, 2, '.', '' );
		$end_saldo = $summe_zahlbetrag - $g_forderung_summe;
		return $end_saldo;
	}
	
	/* FALScH NOCH */
	function saldo_berechnen_monatsgenau($mv_id, $monat, $jahr) {
		$curdate = "$jahr-$monat-28";
		
		// $db_abfrage = "SELECT KOSTENKATEGORIE, BETRAG * MJESECI AS GESAMT FROM (SELECT KOSTENKATEGORIE, BETRAG, ANFANG, ENDE, IF( ENDE = '0000-00-00' OR ENDE >= '$curdate', '$curdate', ENDE ) AS NEW_ENDE, IF( ENDE <= '$curdate' && ENDE != '0000-00-00', period_diff( concat( year( ENDE ) , if( month( ENDE ) <10, '0', '' ) , month( ENDE ) ) , concat( year( ANFANG ) , if( month( ANFANG ) <10, '0', '' ), month( ANFANG ) ) ) +1, period_diff( concat( year( '$curdate' ) , if( month( '$curdate' ) <10, '0', '' ) , month( '$curdate' ) ) , concat( year( ANFANG ) , if( month( ANFANG ) <10, '0', '' ) , month( ANFANG ) ) ) +1 ) AS MJESECI FROM MIETENTWICKLUNG WHERE MIETENTWICKLUNG_AKTUELL = '1' && `KOSTENTRAEGER_TYP` = 'MIETVERTRAG' && `KOSTENTRAEGER_ID` = '$mv_id' ORDER BY `MJESECI` ASC) AS t1 ";
		
		$db_abfrage = "SELECT KOSTENKATEGORIE, BETRAG * MJESECI AS GESAMT FROM (SELECT KOSTENKATEGORIE, BETRAG, ANFANG, ENDE, IF( ENDE = '0000-00-00' OR ENDE >= '$curdate', '$curdate', ENDE ) AS NEW_ENDE, IF( ENDE <= '$curdate' && ENDE != '0000-00-00', period_diff( concat( year( ENDE ) , if( month( ENDE ) <10, '0', '' ) , month( ENDE ) ) , concat( year( ANFANG ) , if( month( ANFANG ) <10, '0', '' ), month( ANFANG ) ) ) +1, period_diff( concat( year( '$curdate' ) , if( month( '$curdate' ) <10, '0', '' ) , month( '$curdate' ) ) , concat( year( ANFANG ) , if( month( ANFANG ) <10, '0', '' ) , month( ANFANG ) ) ) +1 ) AS MJESECI FROM MIETENTWICKLUNG WHERE MIETENTWICKLUNG_AKTUELL = '1' && `KOSTENTRAEGER_TYP` = 'MIETVERTRAG' && `KOSTENTRAEGER_ID` = '$mv_id' && ANFANG<='$curdate' && KOSTENKATEGORIE !='Ratenzahlung' && KOSTENKATEGORIE NOT LIKE 'Mahngeb%'  ORDER BY `MJESECI` ASC) AS t1 ";
		
		$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		
		$g_forderung_summe = '0.00';
		while ( $row = mysql_fetch_assoc ( $resultat ) ) {
			$kostenkat = $row ['KOSTENKATEGORIE'];
			$betrag = $row ['GESAMT'];
			
			if (preg_match ( "/Betriebskostenabrechnung/i", $kostenkat )) {
				if ($betrag < 0) {
					$betrag = abs ( $betrag );
				} else {
					$betrag = '-' . $betrag;
				}
			}
			
			if (preg_match ( "/Heizkostenabrechnung/i", $kostenkat )) {
				if ($betrag < 0) {
					$betrag = abs ( $betrag );
				} else {
					$betrag = '-' . $betrag;
				}
			}
			
			if (preg_match ( "/Wasserkostenabrechnung/i", $kostenkat )) {
				if ($betrag < 0) {
					$betrag = abs ( $betrag );
				} else {
					$betrag = '-' . $betrag;
				}
			}
			
			if (preg_match ( "/Saldo Vortrag Vorverwaltung/i", $kostenkat )) {
				if ($betrag < 0) {
					$betrag = abs ( $betrag );
				} else {
					$betrag = '-' . $betrag;
				}
			}
			
			if (preg_match ( "/Mahngeb�hr/i", $kostenkat )) {
				if ($betrag < 0) {
					$betrag = abs ( $betrag );
				} else {
					$betrag = '-' . $betrag;
				}
			}
			
			$g_forderung_summe = $g_forderung_summe + $betrag;
		}
		
		// return $g_forderung_summe;
		$b = new mietkonto ();
		$summe_zahlbetrag = $b->summe_aller_zahlbetraege_bis_monat ( $mv_id, $monat, $jahr, '80001' );
		$summe_zahlbetrag = number_format ( $summe_zahlbetrag, 2, '.', '' );
		$g_forderung_summe = number_format ( $g_forderung_summe, 2, '.', '' );
		number_format ( $this->saldo_vv, 2, '.', '' );
		$end_saldo = $summe_zahlbetrag - $g_forderung_summe;
		// echo "$end_saldo = $summe_zahlbetrag - $g_forderung_summe;";
		return $end_saldo;
	}
	function saldo_berechnen_tagesgenau($mv_id, $monat, $jahr, $tag) {
		$curdate = "$jahr-$monat-$tag";
		
		// $db_abfrage = "SELECT KOSTENKATEGORIE, BETRAG * MJESECI AS GESAMT FROM (SELECT KOSTENKATEGORIE, BETRAG, ANFANG, ENDE, IF( ENDE = '0000-00-00' OR ENDE >= '$curdate', '$curdate', ENDE ) AS NEW_ENDE, IF( ENDE <= '$curdate' && ENDE != '0000-00-00', period_diff( concat( year( ENDE ) , if( month( ENDE ) <10, '0', '' ) , month( ENDE ) ) , concat( year( ANFANG ) , if( month( ANFANG ) <10, '0', '' ), month( ANFANG ) ) ) +1, period_diff( concat( year( '$curdate' ) , if( month( '$curdate' ) <10, '0', '' ) , month( '$curdate' ) ) , concat( year( ANFANG ) , if( month( ANFANG ) <10, '0', '' ) , month( ANFANG ) ) ) +1 ) AS MJESECI FROM MIETENTWICKLUNG WHERE MIETENTWICKLUNG_AKTUELL = '1' && `KOSTENTRAEGER_TYP` = 'MIETVERTRAG' && `KOSTENTRAEGER_ID` = '$mv_id' ORDER BY `MJESECI` ASC) AS t1 ";
		
		$db_abfrage = "SELECT KOSTENKATEGORIE, BETRAG * MJESECI AS GESAMT FROM (SELECT KOSTENKATEGORIE, BETRAG, ANFANG, ENDE, IF( ENDE = '0000-00-00' OR ENDE >= '$curdate', '$curdate', ENDE ) AS NEW_ENDE, IF( ENDE <= '$curdate' && ENDE != '0000-00-00', period_diff( concat( year( ENDE ) , if( month( ENDE ) <10, '0', '' ) , month( ENDE ) ) , concat( year( ANFANG ) , if( month( ANFANG ) <10, '0', '' ), month( ANFANG ) ) ) +1, period_diff( concat( year( '$curdate' ) , if( month( '$curdate' ) <10, '0', '' ) , month( '$curdate' ) ) , concat( year( ANFANG ) , if( month( ANFANG ) <10, '0', '' ) , month( ANFANG ) ) ) +1 ) AS MJESECI FROM MIETENTWICKLUNG WHERE MIETENTWICKLUNG_AKTUELL = '1' && `KOSTENTRAEGER_TYP` = 'MIETVERTRAG' && `KOSTENTRAEGER_ID` = '$mv_id' && ANFANG<='$curdate' && KOSTENKATEGORIE !='Ratenzahlung' && KOSTENKATEGORIE NOT LIKE 'Mahngeb%'  ORDER BY `MJESECI` ASC) AS t1 ";
		
		$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		
		$g_forderung_summe = '0.00';
		while ( $row = mysql_fetch_assoc ( $resultat ) ) {
			$kostenkat = $row ['KOSTENKATEGORIE'];
			$betrag = $row ['GESAMT'];
			
			if (preg_match ( "/Betriebskostenabrechnung/i", $kostenkat )) {
				if ($betrag < 0) {
					$betrag = abs ( $betrag );
				} else {
					$betrag = '-' . $betrag;
				}
			}
			
			if (preg_match ( "/Heizkostenabrechnung/i", $kostenkat )) {
				if ($betrag < 0) {
					$betrag = abs ( $betrag );
				} else {
					$betrag = '-' . $betrag;
				}
			}
			
			if (preg_match ( "/Wasserkostenabrechnung/i", $kostenkat )) {
				if ($betrag < 0) {
					$betrag = abs ( $betrag );
				} else {
					$betrag = '-' . $betrag;
				}
			}
			
			if (preg_match ( "/Saldo Vortrag Vorverwaltung/i", $kostenkat )) {
				if ($betrag < 0) {
					$betrag = abs ( $betrag );
				} else {
					$betrag = '-' . $betrag;
				}
			}
			
			if (preg_match ( "/Mahngeb�hr/i", $kostenkat )) {
				if ($betrag < 0) {
					$betrag = abs ( $betrag );
				} else {
					$betrag = '-' . $betrag;
				}
			}
			
			$g_forderung_summe = $g_forderung_summe + $betrag;
		}
		
		// return $g_forderung_summe;
		$b = new mietkonto ();
		$summe_zahlbetrag = $b->summe_aller_zahlbetraege_bis_monat ( $mv_id, $monat, $jahr, '80001' );
		$summe_zahlbetrag = number_format ( $summe_zahlbetrag, 2, '.', '' );
		$g_forderung_summe = number_format ( $g_forderung_summe, 2, '.', '' );
		number_format ( $this->saldo_vv, 2, '.', '' );
		$end_saldo = $summe_zahlbetrag - $g_forderung_summe;
		// echo "$end_saldo = $summe_zahlbetrag - $g_forderung_summe;";
		return $end_saldo;
	}
	function mietkonten_blatt_pdf($mv_id) {
		include_once ('pdfclass/class.ezpdf.php');
		$pdf = new Cezpdf ( 'a4', 'portrait' );
		$pdf->ezSetCmMargins ( 4.0, 2.5, 2.5, 2.5 );
		$berlus_schrift = 'pdfclass/fonts/Times-Roman.afm';
		$text_schrift = 'pdfclass/fonts/Arial.afm';
		$pdf->addJpegFromFile ( 'pdfclass/logo_hv_sw.jpg', 220, 750, 175, 100 );
		// $pdf->addJpgFromFile('pdfclass/logo_262_150_sw1.jpg', 300, 500, 250, 150);
		$pdf->setLineStyle ( 0.5 );
		$pdf->selectFont ( $berlus_schrift );
		$pdf->addText ( 86, 743, 6, "BERLUS HAUSVERWALTUNG  * Fontanestr. 1 * 14193 Berlin * Inhaber Wolfgang Wehrheim * Telefon: 89784477 * Fax: 89784479 * Email: info@berlus.de" );
		$pdf->line ( 42, 750, 550, 750 );
		$pdf->ezStartPageNumbers ( 125, 760, 8, '', 'Seite {PAGENUM} von {TOTALPAGENUM}', 1 );
		
		$a = new miete ();
		$a->mietkonto_berechnung ( $mv_id );
		
		$buchung = new mietkonto ();
		/* Mieterinfo anfang */
		$m = new mietvertraege ();
		$m->get_mietvertrag_infos_aktuell ( $mv_id );
		
		$pdf->ezText ( "Mietkonto Einheit: <b>$m->einheit_kurzname</b>", 10 );
		$pdf->ezText ( "Mieter: <b>$m->personen_name_string</b>", 10 );
		/* Mieterinfo ende */
		
		$pdf->ezSetDy ( - 10 );
		/* MV INFO */
		$buchung->ein_auszugsdatum_mietvertrag ( $mv_id );
		$einzugsdatum = explode ( "-", $buchung->mietvertrag_von );
		$einzugs_jahr = $einzugsdatum ['0'];
		$einzugs_monat = $einzugsdatum ['1'];
		
		$auszugsdatum = explode ( "-", $buchung->mietvertrag_bis );
		$auszugs_jahr = $auszugsdatum ['0'];
		$auszugs_monat = $auszugsdatum ['1'];
		/* Status setzen wenn Mieter ausgezogen oder nicht */
		$datum_heute = date ( "Y-m-d" );
		if ($buchung->mietvertrag_bis == '0000-00-00' or $buchung->mietvertrag_bis >= $datum_heute) {
			$mieter_ausgezogen = '0';
		}
		if ($buchung->mietvertrag_bis < $datum_heute) {
			$mieter_ausgezogen = '1';
		}
		
		$pdf->selectFont ( $text_schrift );
		/* Regel wenn es ein Berechnungsergebnis gibt, d.h. miete definiert und berechnet, falls nicht auch nicht anzeigen, da in wahrscheinlich in Zukunft */
		if (! empty ( $a->erg )) {
			$a->erg = number_format ( $a->erg, 2, ",", "" );
			
			if (! empty ( $a->saldo_vv )) {
				$saldo_vv = number_format ( $a->saldo_vv, 2, ",", "" );
				/* Zeile Saldovortragvorverwaltung */
				$pdf->ezText ( "Saldovortragvorverwaltung", 9 );
				$pdf->ezSetDy ( 10 );
				$pdf->ezText ( "$saldo_vv �", 9, array (
						'justification' => 'right' 
				) );
				$pdf->ezSetDy ( - 3 );
				$pdf->line ( 70, $pdf->y, 530, $pdf->y );
			}
			
			/* Version f�r aktuelle Mieter */
			if ($mieter_ausgezogen == '0') {
				foreach ( $a->daten_arr as $key => $value ) {
					for($b = 0; $b < count ( $a->daten_arr [$key] [monate] ); $b ++) {
						
						/* Wenn weniger als 50punkte zum rand, neue Seite und Footer */
						
						if ($pdf->y < '120') {
							$pdf->setLineStyle ( 0.5 );
							$pdf->line ( 42, 50, 550, 50 );
							$pdf->selectFont ( $berlus_schrift );
							$pdf->addText ( 170, 42, 6, "BERLUS HAUSVERWALTUNG �  Fontanestr. 1 � 14193 Berlin � Inhaber Wolfgang Wehrheim" );
							$pdf->addText ( 150, 35, 6, "Bankverbindung: Dresdner Bank Berlin � BLZ: 100  800  00 � Konto-Nr.: 05 804 000 00 � Steuernummer: 24/582/61188" );
							$pdf->ezNewPage ();
							$pdf->addJpegFromFile ( 'pdfclass/logo_hv_sw.jpg', 220, 750, 175, 100 );
							// $pdf->addJpgFromFile('pdfclass/logo_262_150_sw1.jpg', 300, 500, 250, 150);
							$pdf->setLineStyle ( 0.5 );
							$pdf->selectFont ( $berlus_schrift );
							$pdf->addText ( 86, 743, 6, "BERLUS HAUSVERWALTUNG * Fontanestr. 1 * 14193 Berlin * Inhaber Wolfgang Wehrheim * Telefon: 89784477 * Fax: 89784479 * Email: info@berlus.de" );
							$pdf->line ( 42, 750, 550, 750 );
							$num = $pdf->ezWhatPageNumber ( $pdf->ezGetCurrentPageNumber () );
							$pdf->addText ( 86, 760, 8, "Seite $num von $pdf->ezPageCount" );
						}
						
						/* Miete Sollzeile */
						$akt_monat = sprintf ( "%02d", $a->daten_arr [$key] [monate] [$b] [monat] );
						
						if ($a->daten_arr [$key] [monate] [$b] [soll] == '-') {
							$a->daten_arr [$key] [monate] [$b] [soll] = '0.00';
						}
						if ($a->daten_arr [$key] [monate] [$b] [soll] <= '0.00') {
							$monat_name = monat2name ( $akt_monat );
							$soll_aus_mv = number_format ( $a->daten_arr [$key] [monate] [$b] [soll], 2, ",", "" );
							$pdf->ezText ( "01.$akt_monat.$key Soll aus Mietvertrag $monat_name $key", 9 );
							$pdf->ezSetDy ( 10 );
							$pdf->ezSetCmMargins ( 4.0, 2.5, 4.0, 4.5 );
							$pdf->ezText ( "$soll_aus_mv �", 9, array (
									'justification' => 'right' 
							) );
							$pdf->ezSetCmMargins ( 4.0, 2.5, 2.5, 2.5 );
						}
						if ($a->daten_arr [$key] [monate] [$b] [soll] == '0.00') {
							/*
							 * $pdf->ezSetDy(-3);
							 * $pdf->line(70,$pdf->y,530,$pdf->y);
							 * $pdf->ezText("01.$akt_monat.$key NICHT ANZEIGEN $monat_name $key",9);
							 * $pdf->ezSetDy(10);
							 * $pdf->ezSetCmMargins(4.0,2.5,4.0,4.5);
							 * $pdf->ezText("$soll_aus_mv $t �",9, array('justification'=>'right'));
							 * $pdf->ezSetCmMargins(4.0,2.5,2.5,2.5);
							 */
						}
						
						/* Zeile Summe der Mahnungen */
						
						$summe_mahnungen = $a->daten_arr [$key] [monate] [$b] [mahngebuehr];
						if (! empty ( $summe_mahnungen )) {
							$anzahl_mahnungen = count ( $a->daten_arr [$key] [monate] [$b] [mahngebuehren] );
							
							for($g = 0; $g < $anzahl_mahnungen; $g ++) {
								$datum = $a->daten_arr [$key] [monate] [$akt_monat] [mahngebuehren] [$g] [ANFANG];
								
								// $pdf->ezText("print_r($a->daten_arr[$key][monate][$b][mahngebuehren]);",9);
								$pdf->ezText ( "$anzahl_mahnungen JAHR $key MONAT $b AKT $akt_monat COUNT: $g $datum $zahlbetrag_ausgabe", 9 );
								$pdf->ezSetDy ( 10 );
								$pdf->ezSetCmMargins ( 4.0, 2.5, 4.0, 4.5 );
								$pdf->ezText ( "$zahlbetrag_ausgabe � $sanel", 9, array (
										'justification' => 'right' 
								) );
								$pdf->ezSetCmMargins ( 4.0, 2.5, 2.5, 2.5 );
							} // end for
						}
						
						/* Zeile Wasser Abrechnung */
						$wasser_abrechnung = $a->daten_arr [$key] [monate] [$b] [wasser_abrechnung];
						if (! empty ( $wasser_abrechnung )) {
							
							$wasser_abrechnung = nummer_punkt2komma ( $wasser_abrechnung );
							$datum_wasser = $a->daten_arr [$key] [monate] [$b] [wasser_abrechnung_datum];
							$pdf->ezText ( "$datum_wasser Wasserabrechnung ", 9 );
							$pdf->ezSetDy ( 10 );
							$pdf->ezSetCmMargins ( 4.0, 2.5, 4.0, 4.5 );
							$pdf->ezText ( "$wasser_abrechnung �", 9, array (
									'justification' => 'right' 
							) );
							$pdf->ezSetCmMargins ( 4.0, 2.5, 2.5, 2.5 );
						}
						
						/* Zeile BK Abrechnung */
						$bk_abrechnung = $a->daten_arr [$key] [monate] [$b] [bk_abrechnung];
						if (! empty ( $bk_abrechnung )) {
							
							$bk_abrechnung = nummer_punkt2komma ( $bk_abrechnung );
							$datum_bk = $a->daten_arr [$key] [monate] [$b] [bk_abrechnung_datum];
							$pdf->ezText ( "$datum_bk Betriebskostenabrechnung", 9 );
							$pdf->ezSetDy ( 10 );
							$pdf->ezSetCmMargins ( 4.0, 2.5, 4.0, 4.5 );
							$pdf->ezText ( "$bk_abrechnung �", 9, array (
									'justification' => 'right' 
							) );
							$pdf->ezSetCmMargins ( 4.0, 2.5, 2.5, 2.5 );
						}
						
						/* Zeile HK Abrechnung */
						$hk_abrechnung = $a->daten_arr [$key] [monate] [$b] [hk_abrechnung];
						if (! empty ( $hk_abrechnung )) {
							
							$hk_abrechnung = nummer_punkt2komma ( $hk_abrechnung );
							$datum_hk = $a->daten_arr [$key] [monate] [$b] [hk_abrechnung_datum];
							$pdf->ezText ( "$datum_hk Heizkostenabrechnung", 9 );
							$pdf->ezSetDy ( 10 );
							$pdf->ezSetCmMargins ( 4.0, 2.5, 4.0, 4.5 );
							$pdf->ezText ( "$hk_abrechnung �", 9, array (
									'justification' => 'right' 
							) );
							$pdf->ezSetCmMargins ( 4.0, 2.5, 2.5, 2.5 );
						}
						
						/* Zeilen Zahlungen */
						$s_vm = $a->daten_arr [$key] [monate] [$b] [saldo_vormonat];
						if (! is_array ( $a->daten_arr [$key] [monate] [$b] [zahlungen] )) {
							if ($a->daten_arr [$key] [monate] [$b] [soll] != '-0.00') {
								// $a->saldo_vormonat +
								
								if (empty ( $bk_abrechnung ) && empty ( $hk_abrechnung )) {
									$akt_saldo_nz = nummer_punkt2komma ( $s_vm + $a->daten_arr [$key] [monate] [$b] [soll] );
								} else {
									$akt_saldo_nz = nummer_punkt2komma ( $a->daten_arr [$key] [monate] [$b] [erg] );
								}
								// #hier keine zahlung
								
								$pdf->ezText ( "<b>Keine Zahlung</b", 9 );
								$pdf->ezSetDy ( 10 );
								$pdf->ezSetCmMargins ( 4.0, 2.5, 2.5, 2.5 );
								$pdf->ezText ( "<b>$akt_saldo_nz �</b>", 9, array (
										'justification' => 'right' 
								) );
								$pdf->ezSetDy ( - 3 );
								$pdf->line ( 70, $pdf->y, 530, $pdf->y );
							}
						} else {
							for($c = 0; $c < count ( $a->daten_arr [$key] [monate] [$b] [zahlungen] ); $c ++) {
								$datum = date_mysql2german ( $a->daten_arr [$key] [monate] [$b] [zahlungen] [$c] [DATUM] );
								$zahlbetrag_ausgabe = number_format ( $a->daten_arr [$key] [monate] [$b] [zahlungen] [$c] [BETRAG], 2, ",", "" );
								$pdf->ezText ( "$datum " . $a->daten_arr [$key] [monate] [$b] [zahlungen] [$c] [BEMERKUNG] . "", 9 );
								$pdf->ezSetDy ( 10 );
								$pdf->ezSetCmMargins ( 4.0, 2.5, 4.0, 4.5 );
								$pdf->ezText ( "$zahlbetrag_ausgabe �", 9, array (
										'justification' => 'right' 
								) );
								$pdf->ezSetCmMargins ( 4.0, 2.5, 2.5, 2.5 );
							} // end for
						}
						
						/* Saldo am ende des Monats */
						$saldo_aus = number_format ( $a->daten_arr [$key] [monate] [$b] [erg], 2, ",", "" );
						$letzter_tag = date ( "t", mktime ( 0, 0, 0, "" . $a->daten_arr [$key] [monate] [$b] [monat] . "", 1, $key ) );
						/* Letzter d.h. Aktueller Monat */
						if (is_array ( $a->daten_arr [$key] [monate] [$b] [zahlungen] )) {
							if ($a->daten_arr [$key] [monate] [$b] [monat] == date ( "m" ) && $key == date ( "Y" )) {
								$tag_heute = date ( "d" );
								$monat_name = monat2name ( $akt_monat );
								$pdf->ezText ( "$tag_heute.$akt_monat.$key $monat_name $key ", 9 );
								$pdf->ezSetDy ( 10 );
								$pdf->ezSetCmMargins ( 4.0, 2.5, 2.5, 2.5 );
								$pdf->ezText ( "$saldo_aus  �", 9, array (
										'justification' => 'right' 
								) );
								$pdf->ezSetCmMargins ( 4.0, 2.5, 2.5, 2.5 );
							} else {
								
								$monat_name = monat2name ( $akt_monat );
								$pdf->ezText ( "<b>$letzter_tag.$akt_monat.$key Saldo $monat_name $key</b>", 9 );
								$pdf->ezSetDy ( 10 );
								$pdf->ezSetCmMargins ( 4.0, 2.5, 2.5, 2.5 );
								$pdf->ezText ( "<b>$saldo_aus  �</b>", 9, array (
										'justification' => 'right' 
								) );
								$pdf->ezSetCmMargins ( 4.0, 2.5, 2.5, 2.5 );
								$pdf->ezSetDy ( - 3 );
								$pdf->line ( 70, $pdf->y, 530, $pdf->y );
							}
						}
					} // ende for monate
				} // end foreach
			} // ENDE VERSION F�R AKTUELLE MIETER###########################################################################
			
			/* VERSION F�R MIETER DIE AUSGEZOGEN SIND */
			if ($mieter_ausgezogen == '1') {
				foreach ( $a->daten_arr as $key => $value ) {
					for($b = 0; $b < count ( $a->daten_arr [$key] [monate] ); $b ++) {
						
						/* Wenn weniger als 50punkte zum rand, neue Seite und Footer */
						if ($pdf->y < '120') {
							$pdf->setLineStyle ( 0.5 );
							$pdf->line ( 42, 50, 550, 50 );
							$pdf->selectFont ( $berlus_schrift );
							$pdf->addText ( 170, 42, 6, "BERLUS HAUSVERWALTUNG �  Fontanestr. 1 � 14193 Berlin � Inhaber Wolfgang Wehrheim" );
							$pdf->addText ( 150, 35, 6, "Bankverbindung: Dresdner Bank Berlin � BLZ: 100  800  00 � Konto-Nr.: 05 804 000 00 � Steuernummer: 24/582/61188" );
							$pdf->ezNewPage ();
							$pdf->addJpegFromFile ( 'pdfclass/logo_hv_sw.jpg', 220, 750, 175, 100 );
							// $pdf->addJpgFromFile('pdfclass/logo_262_150_sw1.jpg', 300, 500, 250, 150);
							$pdf->setLineStyle ( 0.5 );
							$pdf->selectFont ( $berlus_schrift );
							$pdf->addText ( 86, 743, 6, "BERLUS HAUSVERWALTUNG * Fontanestr. 1 * 14193 Berlin * Inhaber Wolfgang Wehrheim * Telefon: 89784477 * Fax: 89784479 * Email: info@berlus.de" );
							$pdf->line ( 42, 750, 550, 750 );
						}
						
						$akt_monat = sprintf ( "%02d", $a->daten_arr [$key] [monate] [$b] [monat] );
						/* Miete Sollzeile */
						
						if ($a->daten_arr [$key] [monate] [$b] [soll] == '-') {
							$a->daten_arr [$key] [monate] [$b] [soll] = '0.00';
						}
						if ($a->daten_arr [$key] [monate] [$b] [soll] < '0.00') {
							$monat_name = monat2name ( $akt_monat );
							$soll_aus_mv = number_format ( $a->daten_arr [$key] [monate] [$b] [soll], 2, ",", "" );
							$pdf->ezText ( "01.$akt_monat.$key Soll aus Mietvertrag $monat_name $key", 9 );
							$pdf->ezSetDy ( 10 );
							$pdf->ezSetCmMargins ( 4.0, 2.5, 4.0, 4.5 );
							$pdf->ezText ( "$soll_aus_mv �", 9, array (
									'justification' => 'right' 
							) );
							$pdf->ezSetCmMargins ( 4.0, 2.5, 2.5, 2.5 );
						}
						if ($a->daten_arr [$key] [monate] [$b] [soll] == '0.00') {
							/*
							 * $pdf->ezSetDy(-3);
							 * $pdf->line(70,$pdf->y,530,$pdf->y);
							 * $pdf->ezText("01.$akt_monat.$key NICHT ANZEIGEN $monat_name $key",9);
							 * $pdf->ezSetDy(10);
							 * $pdf->ezSetCmMargins(4.0,2.5,4.0,4.5);
							 * $pdf->ezText("$soll_aus_mv $t �",9, array('justification'=>'right'));
							 * $pdf->ezSetCmMargins(4.0,2.5,2.5,2.5);
							 */
						}
						
						/* Zeile Wasser Abrechnung */
						$wasser_abrechnung = $a->daten_arr [$key] [monate] [$b] [wasser_abrechnung];
						if (! empty ( $wasser_abrechnung )) {
							
							$wasser_abrechnung = nummer_punkt2komma ( $wasser_abrechnung );
							$datum_wasser = $a->daten_arr [$key] [monate] [$b] [wasser_abrechnung_datum];
							$pdf->ezText ( "$datum_wasser Wasserabrechnung ", 9 );
							$pdf->ezSetDy ( 10 );
							$pdf->ezSetCmMargins ( 4.0, 2.5, 4.0, 4.5 );
							$pdf->ezText ( "$wasser_abrechnung �", 9, array (
									'justification' => 'right' 
							) );
							$pdf->ezSetCmMargins ( 4.0, 2.5, 2.5, 2.5 );
						}
						
						$bk_abrechnung = $a->daten_arr [$key] [monate] [$b] [bk_abrechnung];
						/* Zeile BK Abrechnung */
						if (! empty ( $bk_abrechnung )) {
							$bk_abrechnung = number_format ( $bk_abrechnung, 2, ",", "" );
							$datum_bk = $a->daten_arr [$key] [monate] [$b] [bk_abrechnung_datum];
							$pdf->ezText ( "$datum_bk Betriebskostenabrechnung", 9 );
							$pdf->ezSetDy ( 10 );
							$pdf->ezSetCmMargins ( 4.0, 2.5, 4.0, 4.5 );
							$pdf->ezText ( "$bk_abrechnung �", 9, array (
									'justification' => 'right' 
							) );
							$pdf->ezSetCmMargins ( 4.0, 2.5, 2.5, 2.5 );
						}
						
						/* Zeile HK Abrechnung */
						$hk_abrechnung = $a->daten_arr [$key] [monate] [$b] [hk_abrechnung];
						if (! empty ( $hk_abrechnung )) {
							
							$hk_abrechnung = number_format ( $hk_abrechnung, 2, ",", "" );
							$datum_hk = $a->daten_arr [$key] [monate] [$b] [hk_abrechnung_datum];
							$pdf->ezText ( "$datum_hk Heizkostenabrechnung", 9 );
							$pdf->ezSetDy ( 10 );
							$pdf->ezSetCmMargins ( 4.0, 2.5, 4.0, 4.5 );
							$pdf->ezText ( "$hk_abrechnung �", 9, array (
									'justification' => 'right' 
							) );
							$pdf->ezSetCmMargins ( 4.0, 2.5, 2.5, 2.5 );
						}
						
						/* Zeilen Zahlungen */
						$s_vm = $a->daten_arr [$key] [monate] [$b] [saldo_vormonat];
						if (! is_array ( $a->daten_arr [$key] [monate] [$b] [zahlungen] )) {
							if ($a->daten_arr [$key] [monate] [$b] [soll] != '-0.00') {
								// $a->saldo_vormonat +
								
								if (empty ( $bk_abrechnung ) && empty ( $hk_abrechnung )) {
									$akt_saldo_nz = nummer_punkt2komma ( $s_vm + $a->daten_arr [$key] [monate] [$b] [soll] );
								} else {
									$akt_saldo_nz = nummer_punkt2komma ( $a->daten_arr [$key] [monate] [$b] [erg] );
								}
								
								// #hier keine zahlung
								$pdf->ezText ( "<b>Keine Zahlung</b>", 9 );
								$pdf->ezSetDy ( 10 );
								$pdf->ezSetCmMargins ( 4.0, 2.5, 2.5, 2.5 );
								$pdf->ezText ( "<b>$akt_saldo_nz �</b>", 9, array (
										'justification' => 'right' 
								) );
								$pdf->ezSetDy ( - 3 );
								$pdf->line ( 70, $pdf->y, 530, $pdf->y );
							}
						} else {
							for($c = 0; $c < count ( $a->daten_arr [$key] [monate] [$b] [zahlungen] ); $c ++) {
								$datum = date_mysql2german ( $a->daten_arr [$key] [monate] [$b] [zahlungen] [$c] [DATUM] );
								$zahlbetrag_ausgabe = number_format ( $a->daten_arr [$key] [monate] [$b] [zahlungen] [$c] [BETRAG], 2, ",", "" );
								$pdf->ezText ( "$datum " . $a->daten_arr [$key] [monate] [$b] [zahlungen] [$c] [BEMERKUNG] . "", 9 );
								$pdf->ezSetDy ( 10 );
								$pdf->ezSetCmMargins ( 4.0, 2.5, 4.0, 4.5 );
								$pdf->ezText ( "$zahlbetrag_ausgabe �", 9, array (
										'justification' => 'right' 
								) );
								$pdf->ezSetCmMargins ( 4.0, 2.5, 2.5, 2.5 );
							} // end for
						}
						
						/* Saldo am Ende des Monats */
						$saldo_aus = number_format ( $a->daten_arr [$key] [monate] [$b] [erg], 2, ",", "" );
						$letzter_tag = date ( "t", mktime ( 0, 0, 0, "" . $a->daten_arr [$key] [monate] [$b] [monat] . "", 1, $key ) );
						/* Letzter d.h. Aktueller Monat */
						if (is_array ( $a->daten_arr [$key] [monate] [$b] [zahlungen] )) {
							if ($a->daten_arr [$key] [monate] [$b] [monat] == date ( "m" ) && $key == date ( "Y" )) {
								$tag_heute = date ( "d" );
								$monat_name = monat2name ( $akt_monat );
								$pdf->ezText ( "<b>$tag_heute.$akt_monat.$key Saldo $monat_name $key</b>", 9 );
								$pdf->ezSetDy ( 10 );
								$pdf->ezSetCmMargins ( 4.0, 2.5, 2.5, 2.5 );
								$pdf->ezText ( "$saldo_aus �", 9, array (
										'justification' => 'right' 
								) );
								$pdf->ezSetDy ( - 3 );
								$pdf->line ( 70, $pdf->y, 530, $pdf->y );
								$pdf->ezSetCmMargins ( 4.0, 2.5, 2.5, 2.5 );
							} else {
								$monat_name = monat2name ( $akt_monat );
								$pdf->ezText ( "<b>$letzter_tag.$akt_monat.$key Saldo $monat_name $key</b>", 9 );
								$pdf->ezSetDy ( 10 );
								$pdf->ezSetCmMargins ( 4.0, 2.5, 2.5, 2.5 );
								$pdf->ezText ( "<b>$saldo_aus �</b>", 9, array (
										'justification' => 'right' 
								) );
								$pdf->ezSetDy ( - 3 );
								$pdf->line ( 70, $pdf->y, 530, $pdf->y );
								$pdf->ezSetCmMargins ( 4.0, 2.5, 2.5, 2.5 );
							}
						}
						
						/* AUSZUGSZEILE */
						if ($key == $auszugs_jahr && $akt_monat == $auszugs_monat) {
							$auszugsdatum_a = date_mysql2german ( $buchung->mietvertrag_bis );
							$pdf->setColor ( 1.0, 0.0, 0.0 );
							$pdf->ezText ( "<b><i>$auszugsdatum_a Ende der Mietzeit</b></i>", 9 );
							$pdf->ezSetDy ( 10 );
							$pdf->ezSetCmMargins ( 4.0, 2.5, 2.5, 2.5 );
							$pdf->ezText ( "<b>$saldo_aus �</b>", 9, array (
									'justification' => 'right' 
							) );
							$pdf->setColor ( 0.0, 0.0, 0.0 );
							
							$pdf->ezSetCmMargins ( 4.0, 2.5, 2.5, 2.5 );
							$pdf->ezSetDy ( - 3 );
							$pdf->line ( 70, $pdf->y, 530, $pdf->y );
						}
					} // ende for monate
				} // end foreach
			} // ende version ausgezogene Mieter
			
			/* Letzte Zeile �berhaupt */
			$tag_heute = date ( "d" );
			// echo "<tr><td><b>$tag_heute.$akt_monat.$key</b></td><td><b>Aktuell</b></td><td></td><td><b>$saldo_aus �</b></td></tr>";
			// echo "</table>\n";
			$pdf->ezSetDy ( - 2 );
			$pdf->line ( 70, $pdf->y, 530, $pdf->y );
			$pdf->ezText ( "<b>$tag_heute.$akt_monat.$key Saldo Aktuell</b>", 9 );
			$pdf->ezSetCmMargins ( 4.0, 2.5, 2.5, 2.5 );
			$pdf->ezSetDy ( 10 );
			$pdf->ezText ( "<b>$saldo_aus �</b>", 9, array (
					'justification' => 'right' 
			) );
			$pdf->ezSetCmMargins ( 4.0, 2.5, 2.5, 2.5 );
		}  // Ende if if(!empty($a->erg)){
else {
			// echo "<h1>Keine Berechnungsgrundlage f�r das Mietkonto</h1>";
			// echo "<h1>Einzugsdatum, Mietdefinition �berpr�fen</h1>";
		}
		
		$pdf->setLineStyle ( 0.5 );
		$pdf->line ( 42, 50, 550, 50 );
		$pdf->selectFont ( $berlus_schrift );
		$pdf->addText ( 170, 42, 6, "BERLUS HAUSVERWALTUNG �  Fontanestr. 1 � 14193 Berlin � Inhaber Wolfgang Wehrheim" );
		$pdf->addText ( 150, 35, 6, "Bankverbindung: Dresdner Bank Berlin � BLZ: 100  800  00 � Konto-Nr.: 05 804 000 00 � Steuernummer: 24/582/61188" );
		$pdf->addInfo ( 'Title', "Mietkontenblatt $mv->personen_name_string" );
		$pdf->addInfo ( 'Author', $_SESSION [username] );
		ob_clean (); // ausgabepuffer leeren
		header ( "Content-type: application/pdf" ); // wird von MSIE ignoriert
		$pdf->ezStopPageNumbers ();
		$pdf->ezStream ();
		// return $pdf->Output();
	}
	function mkb2pdf($mv_id, $drucken_m, $drucken_j) {
		if ($drucken_m == '') {
			$drucken_m = '01';
		}
		
		if ($drucken_j == '') {
			$drucken_j = date ( "Y" );
		}
		
		$mv = new mietvertraege ();
		$mv->get_mietvertrag_infos_aktuell ( $mv_id );
		
		$this->mietkonto_berechnung ( $mv_id );
		/*
		 * $drucken_m = 6;
		 * $drucken_j = 2008;
		 */
		
		/* Druckstartpunkt festlegen, davor alles l�schen */
		foreach ( $this->daten_arr as $key => $value ) {
			/* Volle Jahre */
			if ($key < $drucken_j) {
				unset ( $this->daten_arr [$key] );
			}
		}
		
		/* Monate davor weg */
		$jahre = array_keys ( $this->daten_arr );
		$anzahl_jahre = count ( $jahre );
		$erstes_jahr = $jahre [0];
		$anz_monate = count ( $this->daten_arr [$erstes_jahr] [monate] );
		for($a = 0; $a < $anz_monate; $a ++) {
			if ($this->daten_arr [$erstes_jahr] [monate] [$a] [monat] < $drucken_m) {
				unset ( $this->daten_arr [$erstes_jahr] [monate] [$a] );
			}
		}
		/* Neu nummerieren */
		$this->daten_arr [$erstes_jahr] [monate] = array_values ( $this->daten_arr [$erstes_jahr] [monate] );
		
		/* Ab hier ist das Array ab dem gewollten monat verf�gbar */
		
		$zeile = 0;
		$jahr1 = 0;
		foreach ( $this->daten_arr as $key => $value ) {
			
			for($b = 0; $b < count ( $this->daten_arr [$key] [monate] ); $b ++) {
				$jahr1 ++;
				$akt_monat = sprintf ( "%02d", $this->daten_arr [$key] [monate] [$b] [monat] );
				
				if ($jahr1 == 1) {
					$table_arr [$zeile] [DATUM] = "01.$akt_monat.$key";
					$table_arr [$zeile] [SALDO_VM] = nummer_punkt2komma ( $this->daten_arr [$key] [monate] [$b] [saldo_vormonat] );
					$table_arr [$zeile] [BEMERKUNG] = "Saldo Vormonat";
					$table_arr [$zeile] [BETRAG] = nummer_punkt2komma ( $this->daten_arr [$key] [monate] [$b] [saldo_vormonat] );
					$zeile ++;
				}
				
				/* Miete Sollzeile */
				if ($this->daten_arr [$key] [monate] [$b] [soll] != '-') {
					$monatsname = monat2name ( $akt_monat );
					$table_arr [$zeile] [DATUM] = "01.$akt_monat.$key";
					$table_arr [$zeile] [SALDO_VM] = nummer_punkt2komma ( $this->daten_arr [$key] [monate] [$b] [saldo_vormonat] );
					$table_arr [$zeile] [BEMERKUNG] = "Soll aus Mietvertrag $monatsname $key";
					$table_arr [$zeile] [BETRAG] = nummer_punkt2komma ( $this->daten_arr [$key] [monate] [$b] [soll] );
				}
				
				/* BK */
				if (isset ( $this->daten_arr [$key] [monate] [$b] [bk_abrechnung] )) {
					$zeile ++;
					$table_arr [$zeile] [DATUM] = $this->daten_arr [$key] [monate] [$b] [bk_abrechnung_datum];
					$table_arr [$zeile] [BEMERKUNG] = "Betriebskostenabrechnung";
					$table_arr [$zeile] [BETRAG] = nummer_punkt2komma ( $this->daten_arr [$key] [monate] [$b] [bk_abrechnung] );
				}
				
				/* HK */
				if (isset ( $this->daten_arr [$key] [monate] [$b] [hk_abrechnung] )) {
					$zeile ++;
					$table_arr [$zeile] [DATUM] = $this->daten_arr [$key] [monate] [$b] [hk_abrechnung_datum];
					$table_arr [$zeile] [BEMERKUNG] = "Heizkostenabrechnung";
					$table_arr [$zeile] [BETRAG] = nummer_punkt2komma ( $this->daten_arr [$key] [monate] [$b] [hk_abrechnung] );
				}
				
				/* Zahlungen */
				$zahlungen = count ( $this->daten_arr [$key] [monate] [$b] [zahlungen] );
				
				if (is_array ( $this->daten_arr [$key] [monate] [$b] [zahlungen] )) {
					for($c = 0; $c < $zahlungen; $c ++) {
						$zeile ++;
						$table_arr [$zeile] [DATUM] = date_mysql2german ( $this->daten_arr [$key] [monate] [$b] [zahlungen] [$c] [DATUM] );
						$table_arr [$zeile] [BETRAG] = nummer_punkt2komma ( $this->daten_arr [$key] [monate] [$b] [zahlungen] [$c] [BETRAG] );
						$table_arr [$zeile] [BEMERKUNG] = $this->daten_arr [$key] [monate] [$b] [zahlungen] [$c] [BEMERKUNG];
					}
				} /*
				   * else{
				   * $zeile++;
				   * $l_tag = letzter_tag_im_monat($b,$key);
				   * $table_arr[$zeile][DATUM] = "$l_tag.$akt_monat.$key";
				   * $table_arr[$zeile][BETRAG] = '0.00';
				   * $table_arr[$zeile][BEMERKUNG] = 'Keine Zahlung';
				   * }
				   */
				
				// if($this->daten_arr[$key][monate][$b][soll] != '-' && isset())
				
				if (is_array ( $this->daten_arr [$key] [monate] [$b] [zahlungen] ) or $this->daten_arr [$key] [monate] [$b] [soll] != '-' or isset ( $this->daten_arr [$key] [monate] [$b] [bk_abrechnung] ) or isset ( $this->daten_arr [$key] [monate] [$b] [hk_abrechnung] ) or ($akt_monat == date ( "m" ) && $key == date ( "Y" ))) {
					$zeile ++;
					$l_tag = letzter_tag_im_monat ( $akt_monat, $key );
					if ($akt_monat == date ( "m" ) && $key == date ( "Y" )) {
						$table_arr [$zeile] [DATUM] = "<b>" . date ( "d.m.Y" ) . "</b>";
					} else {
						$table_arr [$zeile] [DATUM] = "<b>$l_tag.$akt_monat.$key</b>";
					}
					$monatsname = monat2name ( $akt_monat );
					$table_arr [$zeile] [BEMERKUNG] = "<b>Saldo $monatsname $key</b>";
					$table_arr [$zeile] [SALDO] = '<b>' . nummer_punkt2komma ( $this->daten_arr [$key] [monate] [$b] [erg] ) . '</b>';
					
					/* LEER F�R LINIE */
					if ("$akt_monat.$key" != date ( "m.Y" )) {
						$zeile ++;
						$table_arr [$zeile] [DATUM] = "__________";
						$monatsname = monat2name ( $akt_monat );
						$table_arr [$zeile] [BEMERKUNG] = "__________________________________________________________________________";
						$table_arr [$zeile] [BETRAG] = '________________';
						$table_arr [$zeile] [SALDO] = '________________';
					}
				}
				$zeile ++;
				
				if ($mv->mietvertrag_bis != '0000-00-00') {
					$auszugs_datum = explode ( '-', $mv->mietvertrag_bis );
					$auszugs_monat = $auszugs_datum [1];
					$auszugs_jahr = $auszugs_datum [0];
					// die("$akt_monat.$key $auszugs_monat.$auszugs_jahr");
					if ("$akt_monat.$key" == "$auszugs_monat.$auszugs_jahr") {
						$table_arr [$zeile] [DATUM] = '<b>' . date_mysql2german ( $mv->mietvertrag_bis ) . '</b>';
						$table_arr [$zeile] [BEMERKUNG] = "<b>Ende der Mietzeit</b>";
						$table_arr [$zeile] [SALDO] = '<b>' . nummer_punkt2komma ( $this->daten_arr [$key] [monate] [$b] [erg] ) . '</b>';
						$zeile ++;
						$table_arr [$zeile] [DATUM] = "__________";
						$table_arr [$zeile] [BEMERKUNG] = "__________________________________________________________________________";
						$table_arr [$zeile] [BETRAG] = '________________';
						$table_arr [$zeile] [SALDO] = '________________';
						$zeile ++;
					}
				}
			}
		}
		
		ob_clean (); // ausgabepuffer leeren
		include_once ('pdfclass/class.ezpdf.php');
		include_once ('classes/class_bpdf.php');
		$pdf = new Cezpdf ( 'a4', 'portrait' );
		$bpdf = new b_pdf ();
		$bpdf->b_header ( $pdf, 'Partner', $_SESSION [partner_id], 'portrait', 'pdfclass/fonts/Helvetica.afm', 6 );
		
		$pdf->ezText ( "Mietkonto Einheit : <b>$mv->einheit_kurzname</b>", 10 );
		$pdf->ezText ( "Mieter: <b>$mv->personen_name_string</b>", 10 );
		$pdf->ezSetDy ( - 12 );
		
		$cols = array (
				'DATUM' => "Datum",
				'BEMERKUNG' => "Bezeichnung",
				'BETRAG' => "Betrag",
				'SALDO' => 'Saldo' 
		);
		$seit_monat = monat2name ( $drucken_m );
		$pdf->ezTable ( $table_arr, $cols, "Mietkontenblatt seit $seit_monat $drucken_j", array (
				'showHeadings' => 1,
				'shaded' => 0,
				'titleFontSize' => 8,
				'fontSize' => 7,
				'xPos' => 50,
				'xOrientation' => 'right',
				'width' => 500,
				'cols' => array (
						'DATUM' => array (
								'justification' => 'right',
								'width' => 50 
						),
						'BEMERKUNG' => array (
								'justification' => 'left',
								'width' => 300 
						),
						'BETRAG' => array (
								'justification' => 'right',
								'width' => 75 
						),
						'SALDO' => array (
								'justification' => 'right',
								'width' => 75 
						) 
				) 
		) );
		
		$pdf->ezStream ();
	}
	function mkb2pdf_mahnung($pdf, $mv_id) {
		$mv = new mietvertraege ();
		$mv->get_mietvertrag_infos_aktuell ( $mv_id );
		
		$this->mietkonto_berechnung ( $mv_id );
		/*
		 * $drucken_m = 6;
		 * $drucken_j = 2008;
		 */
		
		$zeile = 0;
		$jahr1 = 0;
		foreach ( $this->daten_arr as $key => $value ) {
			
			for($b = 0; $b < count ( $this->daten_arr [$key] [monate] ); $b ++) {
				$jahr1 ++;
				$akt_monat = sprintf ( "%02d", $this->daten_arr [$key] [monate] [$b] [monat] );
				
				if ($jahr1 == 1) {
					$table_arr [$zeile] [DATUM] = "01.$akt_monat.$key";
					$table_arr [$zeile] [SALDO_VM] = nummer_punkt2komma ( $this->daten_arr [$key] [monate] [$b] [saldo_vormonat] );
					$table_arr [$zeile] [BEMERKUNG] = "Saldo Vormonat";
					$table_arr [$zeile] [BETRAG] = nummer_punkt2komma ( $this->daten_arr [$key] [monate] [$b] [saldo_vormonat] );
					$zeile ++;
				}
				
				/* Miete Sollzeile */
				if ($this->daten_arr [$key] [monate] [$b] [soll] != '-') {
					$monatsname = monat2name ( $akt_monat );
					$table_arr [$zeile] [DATUM] = "01.$akt_monat.$key";
					$table_arr [$zeile] [SALDO_VM] = nummer_punkt2komma ( $this->daten_arr [$key] [monate] [$b] [saldo_vormonat] );
					$table_arr [$zeile] [BEMERKUNG] = "Soll aus Mietvertrag $monatsname $key";
					$table_arr [$zeile] [BETRAG] = nummer_punkt2komma ( $this->daten_arr [$key] [monate] [$b] [soll] );
				}
				
				/* BK */
				if (isset ( $this->daten_arr [$key] [monate] [$b] [bk_abrechnung] )) {
					$zeile ++;
					$table_arr [$zeile] [DATUM] = $this->daten_arr [$key] [monate] [$b] [bk_abrechnung_datum];
					$table_arr [$zeile] [BEMERKUNG] = "Betriebskostenabrechnung";
					$table_arr [$zeile] [BETRAG] = nummer_punkt2komma ( $this->daten_arr [$key] [monate] [$b] [bk_abrechnung] );
				}
				
				/* HK */
				if (isset ( $this->daten_arr [$key] [monate] [$b] [hk_abrechnung] )) {
					$zeile ++;
					$table_arr [$zeile] [DATUM] = $this->daten_arr [$key] [monate] [$b] [hk_abrechnung_datum];
					$table_arr [$zeile] [BEMERKUNG] = "Heizkostenabrechnung";
					$table_arr [$zeile] [BETRAG] = nummer_punkt2komma ( $this->daten_arr [$key] [monate] [$b] [hk_abrechnung] );
				}
				
				/* Zahlungen */
				$zahlungen = count ( $this->daten_arr [$key] [monate] [$b] [zahlungen] );
				
				if (is_array ( $this->daten_arr [$key] [monate] [$b] [zahlungen] )) {
					for($c = 0; $c < $zahlungen; $c ++) {
						$zeile ++;
						$table_arr [$zeile] [DATUM] = date_mysql2german ( $this->daten_arr [$key] [monate] [$b] [zahlungen] [$c] [DATUM] );
						$table_arr [$zeile] [BETRAG] = nummer_punkt2komma ( $this->daten_arr [$key] [monate] [$b] [zahlungen] [$c] [BETRAG] );
						$table_arr [$zeile] [BEMERKUNG] = $this->daten_arr [$key] [monate] [$b] [zahlungen] [$c] [BEMERKUNG];
					}
				} /*
				   * else{
				   * $zeile++;
				   * $l_tag = letzter_tag_im_monat($b,$key);
				   * $table_arr[$zeile][DATUM] = "$l_tag.$akt_monat.$key";
				   * $table_arr[$zeile][BETRAG] = '0.00';
				   * $table_arr[$zeile][BEMERKUNG] = 'Keine Zahlung';
				   * }
				   */
				
				// if($this->daten_arr[$key][monate][$b][soll] != '-' && isset())
				
				if (is_array ( $this->daten_arr [$key] [monate] [$b] [zahlungen] ) or $this->daten_arr [$key] [monate] [$b] [soll] != '-' or isset ( $this->daten_arr [$key] [monate] [$b] [bk_abrechnung] ) or isset ( $this->daten_arr [$key] [monate] [$b] [hk_abrechnung] ) or ($akt_monat == date ( "m" ) && $key == date ( "Y" ))) {
					$zeile ++;
					$l_tag = letzter_tag_im_monat ( $akt_monat, $key );
					if ($akt_monat == date ( "m" ) && $key == date ( "Y" )) {
						$table_arr [$zeile] [DATUM] = "<b>" . date ( "d.m.Y" ) . "</b>";
					} else {
						$table_arr [$zeile] [DATUM] = "<b>$l_tag.$akt_monat.$key</b>";
					}
					$monatsname = monat2name ( $akt_monat );
					$table_arr [$zeile] [BEMERKUNG] = "<b>Saldo $monatsname $key</b>";
					$table_arr [$zeile] [SALDO] = '<b>' . nummer_punkt2komma ( $this->daten_arr [$key] [monate] [$b] [erg] ) . '</b>';
					
					/* LEER F�R LINIE */
					if ("$akt_monat.$key" != date ( "m.Y" )) {
						$zeile ++;
						$table_arr [$zeile] [DATUM] = "<b><u><i>__________</i></u></b>";
						$monatsname = monat2name ( $akt_monat );
						$table_arr [$zeile] [BEMERKUNG] = "<b><u><i>__________________________________________________________________________</i></u></b>";
						$table_arr [$zeile] [BETRAG] = '<b><u><i>________________</i></u></b>';
						$table_arr [$zeile] [SALDO] = '<b><u><i>________________</i></u></b>';
					}
				}
				$zeile ++;
				
				if ($mv->mietvertrag_bis != '0000-00-00') {
					$auszugs_datum = explode ( '-', $mv->mietvertrag_bis );
					$auszugs_monat = $auszugs_datum [1];
					$auszugs_jahr = $auszugs_datum [0];
					// die("$akt_monat.$key $auszugs_monat.$auszugs_jahr");
					if ("$akt_monat.$key" == "$auszugs_monat.$auszugs_jahr") {
						$table_arr [$zeile] [DATUM] = '<b>' . date_mysql2german ( $mv->mietvertrag_bis ) . '</b>';
						$table_arr [$zeile] [BEMERKUNG] = "<b>Ende der Mietzeit</b>";
						$table_arr [$zeile] [SALDO] = '<b>' . nummer_punkt2komma ( $this->daten_arr [$key] [monate] [$b] [erg] ) . '</b>';
						$zeile ++;
						$table_arr [$zeile] [DATUM] = "<b><u><i>__________</i></u></b>";
						$table_arr [$zeile] [BEMERKUNG] = "<b><u><i>__________________________________________________________________________</i></u></b>";
						$table_arr [$zeile] [BETRAG] = '<b><u><i>________________</i></u></b>';
						$table_arr [$zeile] [SALDO] = '<b><u><i>________________</i></u></b>';
						$zeile ++;
					}
				}
			}
		}
		
		$anz_zeilen = count ( $table_arr );
		$tab_keys = array_keys ( $table_arr );
		echo '<pre>';
		// print_r($tab_keys);
		for($a = 0; $a < $anz_zeilen; $a ++) {
			$alt_key = $tab_keys [$a];
			$neu_key = $a;
			$tab_neu [$neu_key] = $table_arr [$alt_key];
		}
		
		// print_r($tab_neu);
		$start_zeile = $this->finde_start ( $tab_neu );
		
		$tab_neu1 = $this->start_neu ( $tab_neu, $start_zeile );
		
		$tab_neu1 = array_values ( $tab_neu1 );
		print_r ( $tab_neu1 );
		// die();
		
		ob_clean (); // ausgabepuffer leeren
		            // include_once('pdfclass/class.ezpdf.php');
		            // include_once('classes/class_bpdf.php');
		            // $pdf = new Cezpdf('a4', 'portrait');
		            // $bpdf = new b_pdf;
		            // $bpdf->b_header($pdf, 'Partner', $_SESSION[partner_id], 'portrait', 'pdfclass/fonts/Helvetica.afm', 6);
		
		$pdf->ezText ( "Mietkonto Einheit : <b>$mv->einheit_kurzname</b>", 10 );
		$pdf->ezText ( "Mieter: <b>$mv->personen_name_string</b>", 10 );
		$pdf->ezSetDy ( - 12 );
		
		$cols = array (
				'DATUM' => "Datum",
				'BEMERKUNG' => "Bezeichnung",
				'BETRAG' => "Betrag",
				'SALDO' => 'Saldo' 
		);
		$seit_datum = $tab_neu1 [0] ['DATUM'];
		$pdf->ezTable ( $tab_neu1, $cols, "Mietkontenblatt seit $seit_datum", array (
				'showHeadings' => 1,
				'shaded' => 0,
				'titleFontSize' => 8,
				'fontSize' => 7,
				'xPos' => 50,
				'xOrientation' => 'right',
				'width' => 500,
				'cols' => array (
						'DATUM' => array (
								'justification' => 'right',
								'width' => 50 
						),
						'BEMERKUNG' => array (
								'justification' => 'left',
								'width' => 300 
						),
						'BETRAG' => array (
								'justification' => 'right',
								'width' => 75 
						),
						'SALDO' => array (
								'justification' => 'right',
								'width' => 75 
						) 
				) 
		) );
		
		// $pdf->ezStream();
	}
	function finde_start($tab_neu) {
		$anz_zeilen = count ( $tab_neu );
		for($a = $anz_zeilen; $a >= 0; $a --) {
			$bemerkung = $tab_neu [$a] ['BEMERKUNG'];
			$saldo = $tab_neu [$a] ['SALDO'];
			
			if (preg_match ( '/Saldo/i', $bemerkung )) {
				
				if (! preg_match ( '/-/i', $saldo )) {
					echo "$a = $bemerkung $saldo<br>";
					return $a;
				}
			}
		}
	}
	function start_neu($tab_neu, $start) {
		$anz_zeilen = count ( $tab_neu );
		for($a = $anz_zeilen; $a >= 0; $a --) {
			if ($a < $start) {
				unset ( $tab_neu [$a] );
			}
		}
		return $tab_neu;
	}
	
	/* Mit dieser Funktion f�gt man ein Mitkontenblatt in das vorhandene PDF-Dokument hinzu */
	function mietkontenblatt2pdf($pdf, $mv_id) {
		include_once ('pdfclass/class.ezpdf.php');
		$a = new miete ();
		$a->mietkonto_berechnung ( $mv_id );
		
		$buchung = new mietkonto ();
		/* Mieterinfo anfang */
		$m = new mietvertraege ();
		$m->get_mietvertrag_infos_aktuell ( $mv_id );
		
		$pdf->ezSetMargins ( 140, 70, 50, 50 );
		
		$pdf->ezText ( "Mietkonto Einheit : <b>$m->einheit_kurzname</b>", 10 );
		$pdf->ezText ( "Mieter: <b>$m->personen_name_string</b>", 10 );
		/* Mieterinfo ende */
		
		$pdf->ezSetDy ( - 10 );
		/* MV INFO */
		$buchung->ein_auszugsdatum_mietvertrag ( $mv_id );
		$einzugsdatum = explode ( "-", $buchung->mietvertrag_von );
		$einzugs_jahr = $einzugsdatum ['0'];
		$einzugs_monat = $einzugsdatum ['1'];
		
		$auszugsdatum = explode ( "-", $buchung->mietvertrag_bis );
		$auszugs_jahr = $auszugsdatum ['0'];
		$auszugs_monat = $auszugsdatum ['1'];
		/* Status setzen wenn Mieter ausgezogen oder nicht */
		$datum_heute = date ( "Y-m-d" );
		if ($buchung->mietvertrag_bis == '0000-00-00' or $buchung->mietvertrag_bis >= $datum_heute) {
			$mieter_ausgezogen = false;
		}
		if ($buchung->mietvertrag_bis < $datum_heute && $buchung->mietvertrag_bis != '0000-00-00') {
			$mieter_ausgezogen = true;
		}
		
		/* Regel wenn es ein Berechnungsergebnis gibt, d.h. miete definiert und berechnet, falls nicht auch nicht anzeigen, da in wahrscheinlich in Zukunft */
		if (! empty ( $a->erg )) {
			$a->erg = number_format ( $a->erg, 2, ",", "" );
			
			if (! empty ( $a->saldo_vv )) {
				$saldo_vv = number_format ( $a->saldo_vv, 2, ",", "" );
				/* Zeile Saldovortragvorverwaltung */
				
				$pdf->ezText ( "Saldovortragvorverwaltung ", 9, array (
						'justification' => 'left' 
				) );
				$pdf->ezSetDy ( 10 );
				$pdf->ezText ( "$saldo_vv �", 9, array (
						'justification' => 'right' 
				) );
				$pdf->ezSetDy ( - 3 );
				$pdf->line ( 50, $pdf->y, 550, $pdf->y );
			}
			
			/* Version f�r aktuelle Mieter */
			if ($mieter_ausgezogen == false) {
				
				foreach ( $a->daten_arr as $key => $value ) {
					for($b = 0; $b < count ( $a->daten_arr [$key] [monate] ); $b ++) {
						
						/* Miete Sollzeile */
						$akt_monat = sprintf ( "%02d", $a->daten_arr [$key] [monate] [$b] [monat] );
						
						if ($a->daten_arr [$key] [monate] [$b] [soll] == '-') {
							$a->daten_arr [$key] [monate] [$b] [soll] = '0.00';
						}
						if ($a->daten_arr [$key] [monate] [$b] [soll] <= '0.00') {
							$monat_name = monat2name ( $akt_monat );
							$soll_aus_mv = number_format ( $a->daten_arr [$key] [monate] [$b] [soll], 2, ",", "" );
							
							$pdf->ezText ( "01.$akt_monat.$key Soll aus Mietvertrag $monat_name $key", 9, array (
									'justification' => 'left' 
							) );
							
							$pdf->ezSetMargins ( 140, 70, 50, 100 );
							$pdf->ezSetDy ( 10 );
							$pdf->ezText ( "$soll_aus_mv �", 9, array (
									'justification' => 'right' 
							) );
						}
						if ($a->daten_arr [$key] [monate] [$b] [soll] == '0.00') {
							/*
							 * $pdf->ezSetDy(-3);
							 * $pdf->line(70,$pdf->y,530,$pdf->y);
							 * $pdf->ezText("01.$akt_monat.$key NICHT ANZEIGEN $monat_name $key",9);
							 * $pdf->ezSetDy(10);
							 * $pdf->ezSetCmMargins(4.0,2.5,4.0,4.5);
							 * $pdf->ezText("$soll_aus_mv $t �",9, array('justification'=>'right'));
							 * $pdf->ezSetCmMargins(4.0,2.5,2.5,2.5);
							 */
						}
						
						/* Zeile Summe der Mahnungen */
						
						$summe_mahnungen = $a->daten_arr [$key] [monate] [$b] [mahngebuehr];
						if (! empty ( $summe_mahnungen )) {
							$anzahl_mahnungen = count ( $a->daten_arr [$key] [monate] [$b] [mahngebuehren] );
							
							for($g = 0; $g < $anzahl_mahnungen; $g ++) {
								$datum = $a->daten_arr [$key] [monate] [$akt_monat] [mahngebuehren] [$g] [ANFANG];
								
								// $pdf->ezText("print_r($a->daten_arr[$key][monate][$b][mahngebuehren]);",9);
								$pdf->ezSetMargins ( 140, 70, 50, 100 );
								$pdf->ezText ( "$anzahl_mahnungen JAHR $key MONAT $b AKT $akt_monat COUNT: $g $datum $zahlbetrag_ausgabe", 9 );
								$pdf->ezSetDy ( 10 );
								$pdf->ezText ( "$zahlbetrag_ausgabe � $sanel", 9, array (
										'justification' => 'right' 
								) );
							} // end for
						}
						
						/* Zeile Wasser Abrechnung */
						$wasser_abrechnung = $a->daten_arr [$key] [monate] [$b] [wasser_abrechnung];
						if (! empty ( $wasser_abrechnung )) {
							
							$wasser_abrechnung = nummer_punkt2komma ( $wasser_abrechnung );
							$datum_wasser = $a->daten_arr [$key] [monate] [$b] [wasser_abrechnung_datum];
							$pdf->ezText ( "$datum_wasser Wasserabrechnung ", 9 );
							$pdf->ezSetDy ( 10 );
							$pdf->ezSetMargins ( 140, 70, 50, 100 );
							$pdf->ezText ( "$wasser_abrechnung �", 9, array (
									'justification' => 'right' 
							) );
						}
						
						/* Zeile BK Abrechnung */
						$bk_abrechnung = $a->daten_arr [$key] [monate] [$b] [bk_abrechnung];
						if (! empty ( $bk_abrechnung )) {
							
							$bk_abrechnung = nummer_punkt2komma ( $bk_abrechnung );
							$datum_bk = $a->daten_arr [$key] [monate] [$b] [bk_abrechnung_datum];
							$pdf->ezText ( "$datum_bk Betriebskostenabrechnung", 9 );
							$pdf->ezSetDy ( 10 );
							$pdf->ezSetMargins ( 140, 70, 50, 100 );
							$pdf->ezText ( "$bk_abrechnung �", 9, array (
									'justification' => 'right' 
							) );
						}
						
						/* Zeile HK Abrechnung */
						$hk_abrechnung = $a->daten_arr [$key] [monate] [$b] [hk_abrechnung];
						if (! empty ( $hk_abrechnung )) {
							
							$hk_abrechnung = nummer_punkt2komma ( $hk_abrechnung );
							$datum_hk = $a->daten_arr [$key] [monate] [$b] [hk_abrechnung_datum];
							$pdf->ezText ( "$datum_hk Heizkostenabrechnung", 9 );
							$pdf->ezSetDy ( 10 );
							$pdf->ezSetMargins ( 140, 70, 50, 100 );
							$pdf->ezText ( "$hk_abrechnung �", 9, array (
									'justification' => 'right' 
							) );
						}
						
						/* Zeilen Zahlungen */
						$s_vm = $a->daten_arr [$key] [monate] [$b] [saldo_vormonat];
						if (! is_array ( $a->daten_arr [$key] [monate] [$b] [zahlungen] )) {
							if ($a->daten_arr [$key] [monate] [$b] [soll] != '-0.00') {
								// $a->saldo_vormonat +
								
								if (empty ( $bk_abrechnung ) && empty ( $hk_abrechnung )) {
									$akt_saldo_nz = nummer_punkt2komma ( $s_vm + $a->daten_arr [$key] [monate] [$b] [soll] );
								} else {
									$akt_saldo_nz = nummer_punkt2komma ( $a->daten_arr [$key] [monate] [$b] [erg] );
								}
								// #hier keine zahlung
								
								$pdf->ezSetMargins ( 140, 70, 50, 50 );
								$pdf->ezText ( "<b>Keine Zahlung</b>", 9 );
								$pdf->ezSetDy ( 10 );
								$pdf->ezText ( "<b>$akt_saldo_nz �</b>", 9, array (
										'justification' => 'right' 
								) );
								$pdf->ezSetDy ( - 3 );
								$pdf->line ( 50, $pdf->y, 550, $pdf->y );
							}
						} else {
							for($c = 0; $c < count ( $a->daten_arr [$key] [monate] [$b] [zahlungen] ); $c ++) {
								$datum = date_mysql2german ( $a->daten_arr [$key] [monate] [$b] [zahlungen] [$c] [DATUM] );
								$zahlbetrag_ausgabe = number_format ( $a->daten_arr [$key] [monate] [$b] [zahlungen] [$c] [BETRAG], 2, ",", "" );
								$pdf->ezSetMargins ( 140, 70, 50, 50 );
								$pdf->ezText ( "$datum " . $a->daten_arr [$key] [monate] [$b] [zahlungen] [$c] [BEMERKUNG] . "", 9 );
								$pdf->ezSetDy ( 10 );
								
								$pdf->ezText ( "$zahlbetrag_ausgabe �", 9, array (
										'justification' => 'right' 
								) );
							} // end for
						}
						
						/* Saldo am ende des Monats */
						$saldo_aus = ltrim ( rtrim ( nummer_punkt2komma ( $a->daten_arr [$key] [monate] [$b] [erg] ) ) );
						$letzter_tag = date ( "t", mktime ( 0, 0, 0, "" . $a->daten_arr [$key] [monate] [$b] [monat] . "", 1, $key ) );
						/* Letzter d.h. Aktueller Monat */
						if (is_array ( $a->daten_arr [$key] [monate] [$b] [zahlungen] )) {
							if ($a->daten_arr [$key] [monate] [$b] [monat] == date ( "m" ) && $key == date ( "Y" )) {
								$tag_heute = date ( "d" );
								$monat_name = monat2name ( $akt_monat );
								$pdf->ezSetMargins ( 140, 70, 50, 50 );
								$pdf->ezText ( "$tag_heute.$akt_monat.$key $monat_name $key ", 9 );
								$pdf->ezSetDy ( 10 );
								
								$pdf->ezText ( "$saldo_aus �", 9, array (
										'justification' => 'right' 
								) );
							} else {
								$pdf->ezSetMargins ( 140, 70, 50, 50 );
								$monat_name = monat2name ( $akt_monat );
								$pdf->ezText ( "<b>$letzter_tag.$akt_monat.$key Saldo $monat_name $key</b>", 9 );
								$pdf->ezSetDy ( 10 );
								
								$pdf->ezText ( "<b>$saldo_aus �</b>", 9, array (
										'justification' => 'right' 
								) );
								
								$pdf->ezSetDy ( - 3 );
								$pdf->line ( 50, $pdf->y, 550, $pdf->y );
							}
						}
					} // ende for monate
				} // end foreach
			} // ENDE VERSION F�R AKTUELLE MIETER###########################################################################
			
			/* VERSION F�R MIETER DIE AUSGEZOGEN SIND */
			if ($mieter_ausgezogen == true) {
				foreach ( $a->daten_arr as $key => $value ) {
					for($b = 0; $b < count ( $a->daten_arr [$key] [monate] ); $b ++) {
						
						$akt_monat = sprintf ( "%02d", $a->daten_arr [$key] [monate] [$b] [monat] );
						/* Miete Sollzeile */
						
						if ($a->daten_arr [$key] [monate] [$b] [soll] == '-') {
							$a->daten_arr [$key] [monate] [$b] [soll] = '0.00';
						}
						if ($a->daten_arr [$key] [monate] [$b] [soll] < '0.00') {
							$monat_name = monat2name ( $akt_monat );
							$soll_aus_mv = number_format ( $a->daten_arr [$key] [monate] [$b] [soll], 2, ",", "" );
							$pdf->ezText ( "01.$akt_monat.$key Soll aus Mietvertrag $monat_name $key", 9 );
							$pdf->ezSetDy ( 10 );
							$pdf->ezSetMargins ( 140, 70, 50, 100 );
							$pdf->ezText ( "$soll_aus_mv �", 9, array (
									'justification' => 'right' 
							) );
						}
						if ($a->daten_arr [$key] [monate] [$b] [soll] == '0.00') {
							/*
							 * $pdf->ezSetDy(-3);
							 * $pdf->line(70,$pdf->y,530,$pdf->y);
							 * $pdf->ezText("01.$akt_monat.$key NICHT ANZEIGEN $monat_name $key",9);
							 * $pdf->ezSetDy(10);
							 * $pdf->ezSetCmMargins(4.0,2.5,4.0,4.5);
							 * $pdf->ezText("$soll_aus_mv $t �",9, array('justification'=>'right'));
							 * $pdf->ezSetCmMargins(4.0,2.5,2.5,2.5);
							 */
						}
						
						/* Zeile Wasser Abrechnung */
						$wasser_abrechnung = $a->daten_arr [$key] [monate] [$b] [wasser_abrechnung];
						if (! empty ( $wasser_abrechnung )) {
							
							$wasser_abrechnung = nummer_punkt2komma ( $wasser_abrechnung );
							$datum_wasser = $a->daten_arr [$key] [monate] [$b] [wasser_abrechnung_datum];
							$pdf->ezText ( "$datum_wasser Wasserabrechnung ", 9 );
							$pdf->ezSetDy ( 10 );
							$pdf->ezSetMargins ( 140, 70, 50, 100 );
							$pdf->ezText ( "$wasser_abrechnung �", 9, array (
									'justification' => 'right' 
							) );
						}
						
						$bk_abrechnung = $a->daten_arr [$key] [monate] [$b] [bk_abrechnung];
						/* Zeile BK Abrechnung */
						if (! empty ( $bk_abrechnung )) {
							$bk_abrechnung = number_format ( $bk_abrechnung, 2, ",", "" );
							$datum_bk = $a->daten_arr [$key] [monate] [$b] [bk_abrechnung_datum];
							$pdf->ezText ( "$datum_bk Betriebskostenabrechnung", 9 );
							$pdf->ezSetDy ( 10 );
							$pdf->ezSetMargins ( 140, 70, 50, 100 );
							$pdf->ezText ( "$bk_abrechnung �", 9, array (
									'justification' => 'right' 
							) );
						}
						
						/* Zeile HK Abrechnung */
						$hk_abrechnung = $a->daten_arr [$key] [monate] [$b] [hk_abrechnung];
						if (! empty ( $hk_abrechnung )) {
							
							$hk_abrechnung = number_format ( $hk_abrechnung, 2, ",", "" );
							$datum_hk = $a->daten_arr [$key] [monate] [$b] [hk_abrechnung_datum];
							$pdf->ezText ( "$datum_hk Heizkostenabrechnung", 9 );
							$pdf->ezSetDy ( 10 );
							$pdf->ezSetMargins ( 140, 70, 50, 100 );
							$pdf->ezText ( "$hk_abrechnung �", 9, array (
									'justification' => 'right' 
							) );
						}
						
						/* Zeilen Zahlungen */
						$s_vm = $a->daten_arr [$key] [monate] [$b] [saldo_vormonat];
						if (! is_array ( $a->daten_arr [$key] [monate] [$b] [zahlungen] )) {
							if ($a->daten_arr [$key] [monate] [$b] [soll] != '-0.00') {
								// $a->saldo_vormonat +
								
								if (empty ( $bk_abrechnung ) && empty ( $hk_abrechnung )) {
									$akt_saldo_nz = nummer_punkt2komma ( $s_vm + $a->daten_arr [$key] [monate] [$b] [soll] );
								} else {
									$akt_saldo_nz = nummer_punkt2komma ( $a->daten_arr [$key] [monate] [$b] [erg] );
								}
								
								// #hier keine zahlung
								$pdf->ezText ( "<b>Keine Zahlung</b>", 9 );
								$pdf->ezSetDy ( 10 );
								$pdf->ezSetMargins ( 140, 70, 50, 50 );
								$pdf->ezText ( "<b>$akt_saldo_nz �</b>", 9, array (
										'justification' => 'right' 
								) );
								$pdf->ezSetDy ( - 3 );
								$pdf->line ( 50, $pdf->y, 550, $pdf->y );
							}
						} else {
							for($c = 0; $c < count ( $a->daten_arr [$key] [monate] [$b] [zahlungen] ); $c ++) {
								$datum = date_mysql2german ( $a->daten_arr [$key] [monate] [$b] [zahlungen] [$c] [DATUM] );
								$zahlbetrag_ausgabe = number_format ( $a->daten_arr [$key] [monate] [$b] [zahlungen] [$c] [BETRAG], 2, ",", "" );
								$pdf->ezText ( "$datum " . $a->daten_arr [$key] [monate] [$b] [zahlungen] [$c] [BEMERKUNG] . "", 9 );
								$pdf->ezSetDy ( 10 );
								$pdf->ezSetMargins ( 140, 70, 50, 50 );
								$pdf->ezText ( "$zahlbetrag_ausgabe �", 9, array (
										'justification' => 'right' 
								) );
							} // end for
						}
						
						/* Saldo am Ende des Monats */
						$saldo_aus = number_format ( $a->daten_arr [$key] [monate] [$b] [erg], 2, ",", "" );
						$letzter_tag = date ( "t", mktime ( 0, 0, 0, "" . $a->daten_arr [$key] [monate] [$b] [monat] . "", 1, $key ) );
						/* Letzter d.h. Aktueller Monat */
						if (is_array ( $a->daten_arr [$key] [monate] [$b] [zahlungen] )) {
							if ($a->daten_arr [$key] [monate] [$b] [monat] == date ( "m" ) && $key == date ( "Y" )) {
								$tag_heute = date ( "d" );
								$monat_name = monat2name ( $akt_monat );
								$pdf->ezText ( "<b>$tag_heute.$akt_monat.$key Saldo $monat_name $key</b>", 9 );
								$pdf->ezSetDy ( 10 );
								$pdf->ezSetMargins ( 140, 70, 50, 50 );
								$pdf->ezText ( "$saldo_aus �", 9, array (
										'justification' => 'right' 
								) );
								$pdf->ezSetDy ( - 3 );
								$pdf->line ( 50, $pdf->y, 550, $pdf->y );
							} else {
								$monat_name = monat2name ( $akt_monat );
								$pdf->ezText ( "<b>$letzter_tag.$akt_monat.$key Saldo $monat_name $key</b>", 9 );
								$pdf->ezSetDy ( 10 );
								$pdf->ezSetMargins ( 140, 70, 50, 50 );
								$pdf->ezText ( "<b>$saldo_aus �</b>", 9, array (
										'justification' => 'right' 
								) );
								$pdf->ezSetDy ( - 3 );
								$pdf->line ( 50, $pdf->y, 550, $pdf->y );
							}
						}
						
						/* AUSZUGSZEILE */
						if ($key == $auszugs_jahr && $akt_monat == $auszugs_monat) {
							$auszugsdatum_a = date_mysql2german ( $buchung->mietvertrag_bis );
							$pdf->setColor ( 1.0, 0.0, 0.0 );
							$pdf->ezText ( "<b><i>$auszugsdatum_a Ende der Mietzeit</b></i>", 9 );
							$pdf->ezSetDy ( 10 );
							$pdf->ezSetMargins ( 140, 70, 50, 50 );
							$pdf->ezText ( "<b>$saldo_aus �</b>", 9, array (
									'justification' => 'right' 
							) );
							$pdf->setColor ( 0.0, 0.0, 0.0 );
							
							$pdf->ezSetDy ( - 3 );
							$pdf->line ( 50, $pdf->y, 550, $pdf->y );
						}
					} // ende for monate
				} // end foreach
			} // ende version ausgezogene Mieter
			
			/* Letzte Zeile �berhaupt */
			$tag_heute = date ( "d" );
			// echo "<tr><td><b>$tag_heute.$akt_monat.$key</b></td><td><b>Aktuell</b></td><td></td><td><b>$saldo_aus �</b></td></tr>";
			// echo "</table>\n";
			$pdf->ezSetDy ( - 2 );
			$pdf->line ( 50, $pdf->y, 550, $pdf->y );
			$pdf->ezText ( "<b>$tag_heute.$akt_monat.$key Saldo Aktuell</b>", 9 );
			$pdf->ezSetMargins ( 140, 70, 50, 50 );
			$pdf->ezSetDy ( 10 );
			$pdf->ezText ( "<b>$saldo_aus �</b>", 9, array (
					'justification' => 'right' 
			) );
		}  // Ende if if(!empty($a->erg)){
else {
			// echo "<h1>Keine Berechnungsgrundlage f�r das Mietkonto</h1>";
			// echo "<h1>Einzugsdatum, Mietdefinition �berpr�fen</h1>";
		}
		
		$pdf->addInfo ( 'Title', "Mietkontenblatt $mv->personen_name_string" );
		$pdf->addInfo ( 'Author', $_SESSION [username] );
		ob_clean (); // ausgabepuffer leeren
		
		return $pdf;
	}
	function hv_pdf_kopf() {
		include_once ('pdfclass/class.ezpdf.php');
		$pdf = new Cezpdf ( 'a4', 'portrait' );
		$pdf->ezSetCmMargins ( 4.5, 1, 1, 1 );
		$berlus_schrift = 'pdfclass/fonts/Times-Roman.afm';
		$text_schrift = 'pdfclass/fonts/Arial.afm';
		$pdf->addJpegFromFile ( 'pdfclass/logo_hv_sw.jpg', 220, 750, 175, 100 );
		// $pdf->addJpgFromFile('pdfclass/logo_262_150_sw1.jpg', 300, 500, 250, 150);
		$pdf->setLineStyle ( 0.5 );
		$pdf->selectFont ( $berlus_schrift );
		$pdf->addText ( 86, 743, 6, "BERLUS HAUSVERWALTUNG * Fontanestr. 1 * 14193 Berlin * Inhaber Wolfgang Wehrheim * Telefon: 89784477 * Fax: 89784479 * Email: info@berlus.de" );
		$pdf->line ( 42, 750, 550, 750 );
	}
	function hv_pdf_footer() {
		$berlus_schrift = 'pdfclass/fonts/Times-Roman.afm';
		$text_schrift = 'pdfclass/fonts/Arial.afm';
		$pdf->setLineStyle ( 0.5 );
		$pdf->line ( 42, 50, 550, 50 );
		$pdf->selectFont ( $berlus_schrift );
		$pdf->addText ( 170, 42, 6, "BERLUS HAUSVERWALTUNG �  Fontanestr. 1 � 14193 Berlin � Inhaber Wolfgang Wehrheim" );
		$pdf->addText ( 150, 35, 6, "Bankverbindung: Dresdner Bank Berlin � BLZ: 100  800  00 � Konto-Nr.: 05 804 000 00 � Steuernummer: 24/582/61188" );
		$pdf->addInfo ( 'Title', "Mietkontenblatt $mv->personen_name_string" );
		$pdf->addInfo ( 'Author', $_SESSION [username] );
	}
} // end class

/*
 * $h = new miete('97');
 * $h->insert_saldos();
 * $a = new miete('97');
 *
 * echo "STAND: $a->erg";
 * echo "<pre>";
 * print_r($a);
 * echo "</pre>";
 * /*
 * echo "<table>\n";
 * if(!empty($a->saldo_vv)){
 * echo "<tr><td>SVV ".$a->saldo_vv."</td></tr>\n";
 * }
 * foreach($a->daten_arr as $key=>$value){
 * #echo "$key<br>\n";
 *
 * for($b=0;$b<count($a->daten_arr[$key][monate]);$b++){
 * echo "<tr><td>".$a->daten_arr[$key][monate][$b][monat]." / $key</td><td>Soll ".$a->daten_arr[$key][monate][$b][soll]."</td></tr>\n";
 *
 * for($c=0;$c<count($a->daten_arr[$key][monate][$b][zahlungen]);$c++){
 * echo "<tr><td>ZB</td><td>".$a->daten_arr[$key][monate][$b][zahlungen][$c][BEMERKUNG]." ".$a->daten_arr[$key][monate][$b][zahlungen][$c][BETRAG]."</td></tr>\n";
 * }
 * if(count($a->daten_arr[$key][monate][$b][zahlungen])<1){
 * echo "<tr><td>Keine</td><td>zahlung</td></tr>\n";
 * }
 * echo "<tr><td>Saldo </td><td>".$a->daten_arr[$key][monate][$b][erg]."</td></tr>\n";
 * }
 *
 * }
 * echo "</table>\n";
 */
?>