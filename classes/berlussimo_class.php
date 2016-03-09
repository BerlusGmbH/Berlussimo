<?php
/**
 * BERLUSSIMO
 *
 * Hausverwaltungssoftware
 *
 *
 * @copyright    Copyright (c) 2010, Berlus GmbH, Eichkampstraße 161, 14055 Berlin
 * @link         http://www.berlus.de
 * @author       Sanel Sivac & Wolfgang Wehrheim
 * @contact		 software(@)berlus.de
 * @license     http://www.gnu.org/licenses/agpl.html AGPL Version 3
 *
 * @filesource   $HeadURL: http://192.168.2.52/svn/berlussimo_1/tags/02.11.2010 - Downloadversion 0.27/classes/berlussimo_class.php $
 * @version      $Revision: 15 $
 * @modifiedby   $LastChangedBy: sivac $
 * @lastmodified $Date: 2011-07-07 10:41:33 +0200 (Do, 07 Jul 2011) $
 *
 */

// include_once("config.inc.php");
if (file_exists ( "classes/kasse_class.php" )) {
	include_once ("classes/kasse_class.php");
}

if (file_exists ( "classes/class_rechnungen.php" )) {
	include_once ("classes/class_rechnungen.php");
}

if (file_exists ( "classes/class_weg.php" )) {
	include_once ("classes/class_weg.php");
}

if (file_exists ( "classes/class_statistik.php" )) {
	include_once ("classes/class_statistik.php");
}

if (file_exists ( "classes/class_sepa.php" )) {
	include_once ("classes/class_sepa.php");
}

class berlussimo_global {
	function berlussimo_global() {
		$this->datum_heute = date ( "Y-m-d" );
	}
	function vermietete_einheiten_arr() {
		$db_abfrage = "SELECT MIETVERTRAG.MIETVERTRAG_ID, EINHEIT.EINHEIT_KURZNAME FROM MIETVERTRAG JOIN(EINHEIT) ON (EINHEIT.EINHEIT_ID = MIETVERTRAG.EINHEIT_ID) WHERE MIETVERTRAG_AKTUELL='1' && EINHEIT.EINHEIT_AKTUELL='1' && (MIETVERTRAG.MIETVERTRAG_BIS='0000-00-00' OR MIETVERTRAG.MIETVERTRAG_BIS>='$this->datum_heute') ORDER BY EINHEIT.EINHEIT_KURZNAME ASC,MIETVERTRAG.MIETVERTRAG_BIS  DESC";
		while ( $row = mysql_fetch_assoc ( $db_abfrage ) ) {
			$this->vermietete_einheiten [] [mv_id] = $row ['MIETVERTRAG_ID'];
			$this->vermietete_einheiten [] [einheit_kurzname] = $row [EINHEIT_KURZNAME];
		}
	}
	function unvermietete_einheiten_arr() {
		$db_abfrage = "SELECT MIETVERTRAG.MIETVERTRAG_ID, EINHEIT.EINHEIT_KURZNAME FROM MIETVERTRAG JOIN(EINHEIT) ON (EINHEIT.EINHEIT_ID = MIETVERTRAG.EINHEIT_ID) WHERE MIETVERTRAG_AKTUELL='1' && EINHEIT.EINHEIT_AKTUELL='1' &&  MIETVERTRAG.MIETVERTRAG_BIS<'$this->datum_heute' ORDER BY EINHEIT.EINHEIT_KURZNAME ASC,MIETVERTRAG.MIETVERTRAG_BIS  DESC";
		while ( $row = mysql_fetch_assoc ( $db_abfrage ) ) {
			$this->unvermietete_einheiten [] [mv_id] = $row ['MIETVERTRAG_ID'];
			$this->unvermietete_einheiten [] [einheit_kurzname] = $row [EINHEIT_KURZNAME];
		}
	}
	
	/*
	 * Funktion zur Erstellung des Objektauswahlmenues
	 * $link="?daten=leerstand&option=objekt";
	 * objekt_auswahl_liste($link);
	 */
	function objekt_auswahl_liste($link) {
		if (isset ( $_REQUEST ["objekt_id"] ) && ! empty ( $_REQUEST ["objekt_id"] )) {
			$_SESSION ["objekt_id"] = $_REQUEST ["objekt_id"];
		}
		
		$mieten = new mietkonto ();
		// $mieten->erstelle_formular("Objekt auswählen...", NULL);
		$fo = new formular ();
		$fo->fieldset ( 'Objekt wählen', 'obw' );
		if (isset ( $_SESSION ["objekt_id"] )) {
			$objekt_kurzname = new objekt ();
			$objekt_kurzname->get_objekt_name ( $_SESSION ["objekt_id"] );
			echo "<p>&nbsp;<b>Ausgewähltes Objekt</b> -> $objekt_kurzname->objekt_name ->";
		} else {
			echo "<p>&nbsp;<b>Objekt auswählen</b>";
		}
		echo "<div class=\"objekt_auswahl\"  style=\"text-align: justify;width=auto;\">";
		$objekte = new objekt ();
		$objekte_arr = $objekte->liste_aller_objekte ();
		$anzahl_objekte = count ( $objekte_arr );
		$c = 0;
		for($i = 0; $i < $anzahl_objekte; $i ++) {
			$objekt_kurzname = ltrim ( rtrim ( htmlspecialchars ( $objekte_arr [$i] ["OBJEKT_KURZNAME"] ) ) );
			echo "<a class=\"objekt_auswahl_buchung\" href=\"$link&objekt_id=" . $objekte_arr [$i] ["OBJEKT_ID"] . "\">" . $objekt_kurzname . "</a>&nbsp;&nbsp;&nbsp;";
			$c ++;
			if ($c == 17) {
				echo "<br>";
				$c = 0;
			}
		}
		echo "</div>";
		// $mieten->ende_formular();
		$fo->fieldset_ende ();
	}
	function monate_jahres_links($jahr, $link) {
		$f = new formular ();
		$f->fieldset ( "Monats- und Jahresauswahl", 'monate_jahre' );
		$vorjahr = $jahr - 1;
		$nachjahr = $jahr + 1;
		$link_vorjahr = "&nbsp;<a href=\"$link&jahr=$vorjahr&monat=12\"><b>$vorjahr</b></a>&nbsp;";
		$link_nach = "&nbsp;<a href=\"$link&jahr=$nachjahr&monat=01\"><b>$nachjahr</b></a>&nbsp;";
		echo $link_vorjahr;
		$link_alle = "<a href=\"$link&jahr=$jahr\">Alle von $jahr</a>&nbsp;";
		echo $link_alle;
		for($a = 1; $a <= 12; $a ++) {
			$monat_zweistellig = sprintf ( '%02d', $a );
			$link_neu = "<a href=\"$link&monat=$monat_zweistellig&jahr=$jahr\">$a/$jahr</a>&nbsp;";
			// echo "$a/$jahr<br>";
			echo "$link_neu";
		}
		echo $link_nach;
		$f->fieldset_ende ();
	}
	function jahres_links($jahr, $link) {
		$f = new formular ();
		$f->fieldset ( "Jahr wählen", 'monate_jahre' );
		$vorjahr = $jahr - 1;
		$nachjahr = $jahr + 1;
		$link_vorjahr = "&nbsp;<a href=\"$link&jahr=$vorjahr\"><b>$vorjahr</b></a>&nbsp;";
		$link_nach = "&nbsp;<a href=\"$link&jahr=$nachjahr\"><b>$nachjahr</b></a>&nbsp;";
		echo $link_vorjahr;
		echo $link_nach;
		$f->fieldset_ende ();
	}
} // ende class global
class objekt {
	/*
	 * var $sortby;
	 * var $counter;
	 * var $objekt_id;
	 * var $objekt_name;
	 * var $objekt_eigentuemer_partner_id;
	 * var $objekt_kontonummer;
	 * var $anzahl_objekte;
	 * var $anzahl_haeuser;
	 * var $objekt_geld_konto_id;
	 *
	 * ###############
	 * var $anzahl_geld_konten;
	 * var $geld_konten_arr;
	 * ###############
	 *
	 * ####
	 * var $zeilen_pro_seite;
	 * var $aktuelle_seite;
	 * var $start;
	 * var $seiten_anzahl;
	 * ####
	 * var $datum_heute;
	 */
	/*
	 * function objekt() {
	 * connectToBase();
	 * $this->zeilen_pro_seite = "5";
	 * if(!isset($_REQUEST['page'])){
	 * $this->aktuelle_seite = "1";
	 * $this->start = "0";
	 * }
	 * if(isset($_REQUEST['page']) && ($_REQUEST['page'] >= "1")){
	 * $this->aktuelle_seite = $_REQUEST['page'];
	 * $this->start = ($this->aktuelle_seite - 1) * $this->zeilen_pro_seite;
	 * }
	 * $this->datum_heute = date("Y-m-d");
	 * }
	 */
	function form_objekt_kopieren() {
		$f = new formular ();
		$f->erstelle_formular ( 'Objekt kopieren', null );
		hinweis_ausgeben ( "Es werden alle Einheiten, Mietverträge (inkl. Personen) kopiert<br>" );
		$this->dropdown_objekte ( 'objekt_id', 'objekt_id' );
		$f->text_feld ( 'Neue Bezeichnung', 'objekt_kurzname', '', 50, 'objekt_kurzname', '' );
		$f->text_feld ( 'Vorzeichen für Einheiten z.B. E, GBN, II, III', 'vorzeichen', '', 10, 'vorzeichen', '' );
		$p = new partners ();
		$p->partner_dropdown ( 'Neuen Eigentümer wählen', 'eigentuemer_id', 'eigentuemer_id' );
		$f->datum_feld ( 'Datum Saldo VV (letzter Tag vor Verwalterwechsel)', 'datum_u', '', 'datum_u' );
		$f->check_box_js ( 'saldo_berechnen', '1', 'Saldo übernehmen?', '', '' );
		$f->send_button ( 'btn_snd_copy', 'Kopieren' );
		$f->hidden_feld ( 'objekte_raus', 'copy_sent' );
		$f->ende_formular ();
	}
	function objekt_kopieren($objekt_id, $eigentuemer_id, $objekt_kurzname, $vorzeichen, $datum_u, $saldo_berechnen) {
		$this->objekt_speichern ( $objekt_kurzname, $eigentuemer_id );
		$n_objekt_id = $this->get_objekt_id ( $objekt_kurzname );
		if (! empty ( $n_objekt_id )) {
			echo "Objekt_id NEW $n_objekt_id";
			/* Details vom Objekt kopieren */
			$dd = new detail ();
			$o_det_arr = $dd->finde_alle_details_arr ( 'OBJEKT', $objekt_id );
			// print_r($o_det_arr);
			if (is_array ( $o_det_arr )) {
				$anz_det = count ( $o_det_arr );
				for($de = 0; $de < $anz_det; $de ++) {
					$o_det_name = $o_det_arr [$de] ['DETAIL_NAME'];
					$o_det_inhalt = $o_det_arr [$de] ['DETAIL_INHALT'];
					$o_det_bemerkung = $o_det_arr [$de] ['DETAIL_BEMERKUNG'];
					$dd->detail_speichern_2 ( 'OBJEKT', $n_objekt_id, $o_det_name, $o_det_inhalt, $o_det_bemerkung );
				}
			}
			
			$h = new haus ();
			$haus_arr = $this->haeuser_objekt_in_arr ( $objekt_id );
			if (! is_array ( $haus_arr )) {
				fehlermeldung_ausgeben ( "Keine Häuser im Objekt" );
			} else {
				// print_r($haus_arr);
				// die();
				/* Alle Häuser durchlaufen und kopieren */
				$anz_h = count ( $haus_arr );
				for($a = 0; $a < $anz_h; $a ++) {
					$haus_id = $haus_arr [$a] ['HAUS_ID'];
					$str = $haus_arr [$a] ['HAUS_STRASSE'];
					$nr = $haus_arr [$a] ['HAUS_NUMMER'];
					$ort = $haus_arr [$a] ['HAUS_STADT'];
					$plz = $haus_arr [$a] ['HAUS_PLZ'];
					$qm = $haus_arr [$a] ['HAUS_QM'];
					$h = new haus ();
					$n_haus_id = $h->haus_speichern ( $str, $nr, $ort, $plz, $qm, $n_objekt_id );
					echo "$str $nr kopiert<br>";
					
					/* Details vom Haus kopieren */
					$dd = new detail ();
					$h_det_arr = $dd->finde_alle_details_arr ( 'HAUS', $haus_id );
					// print_r($h_det_arr);
					if (is_array ( $h_det_arr )) {
						$anz_det_h = count ( $h_det_arr );
						for($deh = 0; $deh < $anz_det_h; $deh ++) {
							$h_det_name = $h_det_arr [$deh] ['DETAIL_NAME'];
							$h_det_inhalt = $h_det_arr [$deh] ['DETAIL_INHALT'];
							$h_det_bemerkung = $h_det_arr [$deh] ['DETAIL_BEMERKUNG'];
							$dd->detail_speichern_2 ( 'HAUS', $n_haus_id, $h_det_name, $h_det_inhalt, $h_det_bemerkung );
						}
					}
					
					$einheiten_arr = $h->liste_aller_einheiten_im_haus ( $haus_id );
					if (is_array ( $einheiten_arr )) {
						// print_r($einheiten_arr);
						
						$anz_e = count ( $einheiten_arr );
						for($e = 0; $e < $anz_e; $e ++) {
							$einheit_id = $einheiten_arr [$e] ['EINHEIT_ID'];
							$einheit_qm = nummer_punkt2komma ( $einheiten_arr [$e] ['EINHEIT_QM'] );
							$einheit_lage = $einheiten_arr [$e] ['EINHEIT_LAGE'];
							$einheit_kurzname = $einheiten_arr [$e] ['EINHEIT_KURZNAME'];
							$einheit_typ = $einheiten_arr [$e] ['TYP'];
							$ein = new einheit ();
							$einheit_kn_arr = explode ( '-', $einheit_kurzname );
							// print_r($einheit_kn_arr);
							$l_elem = count ( $einheit_kn_arr ) - 1;
							$n_einheit_kurzname = $vorzeichen . '-' . $einheit_kn_arr [$l_elem];
							echo "$einheit_kurzname -> $n_einheit_kurzname<br>";
							$n_einheit_id = $ein->einheit_speichern ( $n_einheit_kurzname, $einheit_lage, $einheit_qm, $n_haus_id, $einheit_typ );
							
							/* Details von Einheiten kopieren */
							$dd = new detail ();
							$e_det_arr = $dd->finde_alle_details_arr ( 'EINHEIT', $einheit_id );
							// print_r($e_det_arr);
							if (is_array ( $e_det_arr )) {
								$anz_det_e = count ( $e_det_arr );
								for($dee = 0; $dee < $anz_det_e; $dee ++) {
									$e_det_name = $e_det_arr [$dee] ['DETAIL_NAME'];
									$e_det_inhalt = $e_det_arr [$dee] ['DETAIL_INHALT'];
									$e_det_bemerkung = $e_det_arr [$dee] ['DETAIL_BEMERKUNG'];
									$dd->detail_speichern_2 ( 'EINHEIT', $n_einheit_id, $e_det_name, $e_det_inhalt, $e_det_bemerkung );
								}
							}
							
							/* Eigentümer kopieren */
							$weget = new weg ();
							$et_arr = $weget->get_eigentuemer_arr ( $einheit_id );
							if (is_array ( $et_arr )) {
								$anz_et = count ( $et_arr );
								for($eta = 0; $eta < $anz_et; $eta ++) {
									$et_von = $et_arr [$eta] ['VON'];
									$et_bis = $et_arr [$eta] ['BIS'];
									$weg_et_id = $et_arr [$eta] ['ID'];
									$neu_et_id = $weget->eigentuemer_neu ( $n_einheit_id, $et_von, $et_bis );
									
									/* Personen zu ET eintragen */
									$p_id_arr = $weget->get_person_id_eigentuemer_arr ( $weg_et_id );
									if (is_array ( $p_id_arr )) {
										$anz_p_et = count ( $p_id_arr );
										for($pp = 0; $pp < $anz_p_et; $pp ++) {
											$tmp_p_id = $p_id_arr [$pp] ['PERSON_ID'];
											$weget->person_zu_et ( $neu_et_id, $tmp_p_id );
										}
									}
									
									/* Geldkonten finden und zuweisen */
									$gki = new geldkonto_info ();
									$gk_arr = $gki->geldkonten_arr ( 'Eigentuemer', $weg_et_id );
									if (is_array ( $gk_arr )) {
										$anz_gk = count ( $gk_arr );
										for($gka = 0; $gka < $anz_gk; $gka ++) {
											$tmp_gk_id = $gk_arr [$gka] ['KONTO_ID'];
											/**
											 * *Konto eintragen**
											 */
											$gkk = new gk ();
											$gkk->zuweisung_speichern ( 'Eigentuemer', $neu_et_id, $tmp_gk_id );
										}
									}
								}
							}
							
							/* Mietverträge */
							$mv_arr = $ein->get_mietvertrag_ids ( $einheit_id );
							if (is_array ( $mv_arr )) {
								$anz_mv = count ( $mv_arr );
								// print_r($mv_arr);
								for($m = 0; $m < $anz_mv; $m ++) {
									$mv_id = $mv_arr [$m] ['MIETVERTRAG_ID'];
									$mvs = new mietvertraege ();
									$mvs->get_mietvertrag_infos_aktuell ( $mv_id );
									// print_r($mvs);
									$n_mv_id = $mvs->mietvertrag_speichern ( $mvs->mietvertrag_von_d, $mvs->mietvertrag_bis_d, $n_einheit_id );
									
									for($pp = 0; $pp < $mvs->anzahl_personen; $pp ++) {
										$person_id = $mvs->personen_ids [$pp] ['PERSON_MIETVERTRAG_PERSON_ID'];
										$mvs->person_zu_mietvertrag ( $person_id, $n_mv_id );
									}
									
									/* Details von MV's kopieren */
									$dd = new detail ();
									$mv_det_arr = $dd->finde_alle_details_arr ( 'MIETVERTRAG', $mv_id );
									// print_r($e_det_arr);
									if (is_array ( $mv_det_arr )) {
										$anz_det_m = count ( $mv_det_arr );
										for($dem = 0; $dem < $anz_det_m; $dem ++) {
											$m_det_name = $mv_det_arr [$dem] ['DETAIL_NAME'];
											$m_det_inhalt = $mv_det_arr [$dem] ['DETAIL_INHALT'];
											$m_det_bemerkung = $mv_det_arr [$dem] ['DETAIL_BEMERKUNG'];
											$dd->detail_speichern_2 ( 'MIETVERTRAG', $n_mv_id, $m_det_name, $m_det_inhalt, $m_det_bemerkung );
										}
									}
									
									/* Mietentwicklung kopieren */
									$mit = new mietentwicklung ();
									$mit->get_mietentwicklung_infos ( $mv_id, '', '' );
									// print_r($mit);
									if (is_array ( $mit->kostenkategorien )) {
										$anz_me = count ( $mit->kostenkategorien );
										for($ko = 0; $ko < $anz_me; $ko ++) {
											$kat = $mit->kostenkategorien [$ko] ['KOSTENKATEGORIE'];
											$anfang = $mit->kostenkategorien [$ko] ['ANFANG'];
											$ende = $mit->kostenkategorien [$ko] ['ENDE'];
											$betrag = $mit->kostenkategorien [$ko] ['BETRAG'];
											$mwst_anteil = $mit->kostenkategorien [$ko] ['MWST_ANTEIL'];
											$mit->me_speichern ( 'MIETVERTRAG', $n_mv_id, $kat, $anfang, $ende, $betrag, $mwst_anteil );
										} // end for $ko
									}
									
									/* Saldo zum $datum_u ermitteln und den neuen Saldovortragvorverwaltung eingeben */
									$datum_saldo_vv = date_german2mysql ( $datum_u );
									$datum_saldo_vv_arr = explode ( '.', $datum_u );
									$datum_jahr = $datum_saldo_vv_arr [2];
									$datum_monat = $datum_saldo_vv_arr [1];
									$mzz = new miete ();
									
									if ($saldo_berechnen == 1) {
										$mzz->mietkonto_berechnung_monatsgenau ( $mv_id, $datum_jahr, $datum_monat );
										echo "MIT SALDO<br>";
										$mit->me_speichern ( 'MIETVERTRAG', $n_mv_id, 'Saldo Vortrag Vorverwaltung', $datum_saldo_vv, $datum_saldo_vv, $mzz->erg, ($mzz->erg / 119 * 19) );
									} else {
										echo "OHNE SALDO<br>";
										$mit->me_speichern ( 'MIETVERTRAG', $n_mv_id, 'Saldo Vortrag Vorverwaltung', $datum_saldo_vv, $datum_saldo_vv, '0.00', '0.00' );
									}
									
									/* ME 0000-00-00 auf $datum_u setzen */
								} // end for alle MV'S
							} else {
								echo "Mv zu $einheit_kurzname nicht gefunden - Leerstand";
							}
							
							// die('ENDE');
						} // end for einheit
					} else {
						echo "Keine Einheiten kopiert";
					}
				} // end for haus
			}
		} else {
			die ( 'Objekt konnte nicht angelegt werden!' );
		}
	}
	function mietauftellung_arr($objekt_id, $monat = null, $jahr = null) {
		if ($monat == null) {
			$monat = date ( "m" );
		}
		if ($jahr == null) {
			$jahr = date ( "Y" );
		}
		$monat = sprintf ( '%02d', $monat );
		$jahr = sprintf ( '%02d', $jahr );
		
		// ini_set('display_errors','On');
		// error_reporting(E_ALL|E_STRICT);
		$db_abfrage = "SELECT OBJEKT_KURZNAME, HAUS_STRASSE, HAUS_NUMMER, `EINHEIT_KURZNAME` ,EINHEIT_ID,  `EINHEIT_LAGE` , `EINHEIT_QM`, EINHEIT.TYP FROM EINHEIT , HAUS, OBJEKT
WHERE OBJEKT.OBJEKT_ID='$objekt_id' && `EINHEIT_AKTUELL` = '1' && EINHEIT.HAUS_ID = HAUS.HAUS_ID && HAUS.OBJEKT_ID=OBJEKT.OBJEKT_ID && HAUS_AKTUELL='1' && OBJEKT_AKTUELL='1'
ORDER BY LPAD(EINHEIT_KURZNAME, LENGTH(EINHEIT_KURZNAME), '1') ASC ";
		
		// echo $db_abfrage;
		$result = mysql_query ( $db_abfrage );
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			$z = 0;
			$g_flaeche = 0;
			$g_km_monat = 0;
			$g_nkosten = 0;
			$g_zahlung = 0;
			$g_brutto_m = 0;
			while ( $row = mysql_fetch_assoc ( $result ) ) {
				$my_arr [] = $row;
				$einheit_id = $row ['EINHEIT_ID'];
				$einheit_qm = $row ['EINHEIT_QM'];
				$g_flaeche += $einheit_qm;
				$einheit_kn = $row ['EINHEIT_KURZNAME'];
				
				$my_arr [$z] ['EINHEIT_KURZNAME1'] = $einheit_kn . ' ' . $row ['EINHEIT_LAGE'];
				$my_arr [$z] ['EINHEIT_QM'] = $einheit_qm;
				$my_arr [$z] ['EINHEIT_QM_A'] = nummer_punkt2komma ( $einheit_qm );
				$e = new einheit ();
				// $mv_id = $e->get_mietvertrag_id($einheit_id);
				// $mv_id = $e->get_last_mietvertrag_id($einheit_id); // OK
				$mv_id = $e->get_mietvertraege_zu ( $einheit_id, $jahr, $monat, 'DESC' );
				// OK
				
				if ($mv_id) {
					$mvs = new mietvertraege ();
					$mvs->get_mietvertrag_infos_aktuell ( $mv_id );
					// $kontaktdaten = $e->kontaktdaten_mieter($mv_id);
					// $my_arr[$z]['MIETER'] = $mvs->personen_name_string_u."\n".$kontaktdaten;
					$my_arr [$z] ['MIETER'] = $mvs->personen_name_string_u;
					// $my_arr[$z]['KONTAKT'] = $kontaktdaten;
					$my_arr [$z] ['MIETER_SEIT'] = $mvs->mietvertrag_von_d;
					
					if ($monat == null) {
						$monat = date ( "m" );
					}
					
					if ($jahr == null) {
						$jahr = date ( "Y" );
					}
					$miete = new miete ();
					$miete->mietkonto_berechnung_monatsgenau ( $mv_id, $jahr, $monat );
					// echo '<pre>';
					// print_r($miete);
					// die();
					// $miete_warm = $miete->sollmiete_warm;
					// $umlagen = $miete->davon_umlagen;
					$miete_brutto_arr = explode ( '|', $miete->sollmiete_warm );
					if (is_array ( $miete_brutto_arr )) {
						$miete_warm = $miete_brutto_arr [0];
						$mwst = $miete_brutto_arr [1];
					} else {
						$miete_warm = $miete->sollmiete_warm;
						$mwst = '0.00';
					}
					$miete_kalt = $miete_warm - $miete->davon_umlagen;
					
					$my_arr [$z] ['MONAT'] = $monat;
					$my_arr [$z] ['JAHR'] = $jahr;
					
					$my_arr [$z] ['MIETE_BRUTTO'] = nummer_punkt2komma ( $miete_warm );
					$g_brutto_m += $miete_warm;
					$my_arr [$z] ['MWST'] = nummer_punkt2komma ( $mwst );
					$my_arr [$z] ['UMLAGEN'] = nummer_punkt2komma ( $miete->davon_umlagen );
					
					$my_arr [$z] ['ZAHLUNGEN'] = nummer_punkt2komma ( $miete->geleistete_zahlungen );
					$my_arr [$z] ['SALDO'] = nummer_punkt2komma ( $miete->erg );
					$my_arr [$z] ['SALDO_VM'] = nummer_punkt2komma ( $miete->saldo_vormonat );
					$my_arr [$z] ['SALDO_VM1'] = nummer_punkt2komma ( $miete->saldo_vormonat_stand );
					
					$g_nkosten += $miete->davon_umlagen;
					$g_km_monat += $miete_kalt;
					$g_zahlung += $miete->geleistete_zahlungen;
					
					$my_arr [$z] ['UMLAGEN_A'] = nummer_punkt2komma ( $miete->davon_umlagen );
					$my_arr [$z] ['MIETE_KALT_MON'] = nummer_punkt2komma ( $miete_kalt );
					$my_arr [$z] ['MIETE_KALT_MON_A'] = nummer_punkt2komma ( $miete_kalt );
					
					if ($einheit_qm != '0.00') {
						$my_arr [$z] ['MIETE_KALT_QM'] = $miete_kalt / $einheit_qm;
						$my_arr [$z] ['MIETE_KALT_QM_A'] = nummer_punkt2komma ( $miete_kalt / $einheit_qm );
					} else {
						$my_arr [$z] ['MIETE_KALT_QM'] = '0.00';
					}
				} else {
					$my_arr [$z] ['MIETER'] = 'Leerstand';
				}
				$z ++;
			}
		} else {
			echo "Keine Daten xcjskskdds!";
		}
		$anz = count ( $my_arr );
		$my_arr [$anz] ['MONAT_JAHR'] = "$monat / $jahr";
		$my_arr [$anz] ['EINHEIT_QM_A'] = nummer_punkt2komma ( $g_flaeche ) . 'm²';
		$my_arr [$anz] ['MIETE_KALT_MON_A'] = nummer_punkt2komma ( $g_km_monat ) . '€';
		$my_arr [$anz] ['UMLAGEN_A'] = nummer_punkt2komma ( $g_nkosten ) . '€';
		$my_arr [$anz] ['BRUTTOM_A'] = nummer_punkt2komma ( $g_brutto_m ) . '€';
		$my_arr [$anz] ['ZAHLUNGEN_A'] = nummer_punkt2komma ( $g_zahlung ) . '€';
		
		return $my_arr;
	}
	function pdf_mietaufstellung($objekt_id) {
		$arr = $this->mietauftellung_arr ( $objekt_id );
		if (is_array ( $arr )) {
			// echo '<pre>';
			// print_r($arr);
			// die();
			//include_once ('pdfclass/class.ezpdf.php');
			include_once ('classes/class_bpdf.php');
			$pdf = new Cezpdf ( 'a4', 'portrait' );
			$bpdf = new b_pdf ();
			$bpdf->b_header ( $pdf, 'Partner', $_SESSION [partner_id], 'portrait', 'Helvetica.afm', 6 );
			
			$cols = array (
					'EINHEIT_KURZNAME1' => 'Einheit',
					'TYP' => "Nutzung",
					'MIETER' => 'Mieter',
					'MIETER_SEIT' => 'Mieter seit',
					'EINHEIT_QM_A' => 'Fläche m²',
					'MIETE_KALT_QM_A' => 'Kaltmiete m²',
					'MIETE_KALT_MON_A' => 'Kaltmiete Monat',
					'UMLAGEN_A' => 'Nebenkosten' 
			);
			// print_r($table_arr);
			// die();
			
			$monat = date ( "m" );
			$jahr = date ( "Y" );
			$monatsname = monat2name ( $monat );
			$pdf->ezTable ( $arr, $cols, "Mietaufstellung $monatsname $jahr", array (
					'showHeadings' => 1,
					'shaded' => 1,
					'titleFontSize' => 8,
					'fontSize' => 7,
					'xPos' => 50,
					'xOrientation' => 'right',
					'width' => 500,
					'cols' => array (
							'EINHEIT_KURZNAME1' => array (
									'justification' => 'left',
									'width' => 55 
							),
							'TYP' => array (
									'justification' => 'right',
									'width' => 50 
							),
							'EINHEIT_QM_A' => array (
									'justification' => 'right',
									'width' => 50 
							),
							'MIETE_KALT_MON_A' => array (
									'justification' => 'right',
									'width' => 60 
							),
							'MIETER_SEIT' => array (
									'justification' => 'right',
									'width' => 50 
							),
							'MIETE_KALT_QM_A' => array (
									'justification' => 'right',
									'width' => 50 
							),
							'UMLAGEN_A' => array (
									'justification' => 'right',
									'width' => 55 
							) 
					) 
			) );
			
			ob_clean ();
			// ausgabepuffer leeren
			header ( "Content-type: application/pdf" );
			// wird von MSIE ignoriert
			$pdf->ezStream ();
		} else {
			die ( 'Keine Mietaufstellungsdaten' );
		}
	}
	function pdf_mietaufstellung_m_j($objekt_id, $monat, $jahr) {
		$arr = $this->mietauftellung_arr ( $objekt_id, $monat, $jahr );
		if (is_array ( $arr )) {
			// echo '<pre>';
			// print_r($arr);
			// die();
			//include_once ('pdfclass/class.ezpdf.php');
			include_once ('classes/class_bpdf.php');
			$pdf = new Cezpdf ( 'a4', 'landscape' );
			$bpdf = new b_pdf ();
			$bpdf->b_header ( $pdf, 'Partner', $_SESSION [partner_id], 'landscape', 'Helvetica.afm', 6 );
			
			$cols = array (
					'EINHEIT_KURZNAME1' => 'Einheit',
					'TYP' => "Nutzung",
					'MIETER' => 'Mieter',
					'MIETER_SEIT' => 'Mieter seit',
					'EINHEIT_QM_A' => 'Fläche m²',
					'MIETE_KALT_QM_A' => 'Kaltmiete m²',
					'MIETE_KALT_MON_A' => 'Kaltmiete Monat',
					'UMLAGEN_A' => 'Nebenkosten',
					'MIETE_BRUTTO' => 'BruttoM',
					'MWST' => 'MWSt',
					'ZAHLUNGEN' => 'Zahlung' 
			);
			// print_r($table_arr);
			// die();
			
			// $monat = date("m");
			// $jahr = date("Y");
			$monatsname = monat2name ( $monat );
			$oo = new objekt ();
			$oo->get_objekt_infos ( $objekt_id );
			
			if (! isset ( $_REQUEST ['xls'] )) {
				$pdf->ezTable ( $arr, $cols, "$oo->objekt_kurzname - Mietaufstellung $monatsname $jahr", array (
						'showHeadings' => 1,
						'shaded' => 1,
						'titleFontSize' => 8,
						'fontSize' => 7,
						'xPos' => 50,
						'xOrientation' => 'right',
						'width' => 750,
						'cols' => array (
								'EINHEIT_KURZNAME1' => array (
										'justification' => 'left',
										'width' => 55 
								),
								'TYP' => array (
										'justification' => 'right',
										'width' => 50 
								),
								'EINHEIT_QM_A' => array (
										'justification' => 'right',
										'width' => 50 
								),
								'MIETE_KALT_MON_A' => array (
										'justification' => 'right',
										'width' => 60 
								),
								'MIETER_SEIT' => array (
										'justification' => 'right',
										'width' => 50 
								),
								'MIETE_KALT_QM_A' => array (
										'justification' => 'right',
										'width' => 50 
								),
								'UMLAGEN_A' => array (
										'justification' => 'right',
										'width' => 55 
								) 
						) 
				) );
				
				ob_clean ();
				// ausgabepuffer leeren
				header ( "Content-type: application/pdf" );
				// wird von MSIE ignoriert
				$pdf->ezStream ();
			} else {
				// echo '<pre>';
				// print_r($arr);
				// die();
				$anz_zeilen = count ( $arr );
				
				ob_clean ();
				// ausgabepuffer leeren
				$fileName = "$oo->objekt_kurzname - Mietaufstellung $monat-$jahr" . '.xls';
				header ( "Content-type: application/vnd.ms-excel" );
				header ( "Content-Disposition: attachment; filename=$fileName" );
				header ( "Content-Disposition: inline; filename=$fileName" );
				ob_clean ();
				// ausgabepuffer leeren
				echo "<table>";
				echo "<thead>";
				echo "<tr>";
				echo "<th>EINHEIT</th>";
				echo "<th>NUTZUNG</th>";
				echo "<th>MIETER</th>";
				echo "<th>EINZUG</th>";
				echo "<th>FLÄCHE</th>";
				echo "<th>KALTMIETE m²</th>";
				echo "<th>MIETE NETTO</th>";
				echo "<th>NK</th>";
				echo "<th>MIETE BRUTTO</th>";
				echo "<th>MWWST</th>";
				echo "<th>ZAHLUNG</th>";
				echo "</tr>";
				echo "</thead>";
				
				for($z = 0; $z < $anz_zeilen - 1; $z ++) {
					$einheit_kn = $arr [$z] ['EINHEIT_KURZNAME'];
					$nutzung = $arr [$z] ['TYP'];
					$mieter = $arr [$z] ['MIETER'];
					$einzug = $arr [$z] ['MIETER_SEIT'];
					$qm = $arr [$z] ['EINHEIT_QM_A'];
					$km_qm = $arr [$z] ['MIETE_KALT_QM_A'];
					$km_mon = $arr [$z] ['MIETE_KALT_MON_A'];
					$nk = $arr [$z] ['UMLAGEN'];
					$wm = $arr [$z] ['MIETE_BRUTTO'];
					$mwst = $arr [$z] ['MWST'];
					$zahlung = $arr [$z] ['ZAHLUNGEN'];
					echo "<tr><td>$einheit_kn</td><td>$nutzung</td><td>$mieter</td><td>$einzug</td><td>$qm</td><td>$km_qm</td><td>$km_mon</td><td>$nk</td><td>$wm</td><td>$mwst</td><td>$zahlung</td></tr>";
				}
				echo "</table>";
				die ();
			}
		} else {
			die ( 'Keine Mietaufstellungsdaten' );
		}
	}
	function pdf_mietaufstellung_j($objekt_id, $jahr) {
		for($mo = 1; $mo <= 12; $mo ++) {
			$monat = sprintf ( '%02d', $mo );
			
			$arr [$mo - 1] = $this->mietauftellung_arr ( $objekt_id, $monat, $jahr );
			
			if (is_array ( $arr )) {
				// echo '<pre>';
				// print_r($arr);
				// die();
				//include_once ('pdfclass/class.ezpdf.php');
				include_once ('classes/class_bpdf.php');
				$pdf = new Cezpdf ( 'a4', 'landscape' );
				$bpdf = new b_pdf ();
				$bpdf->b_header ( $pdf, 'Partner', $_SESSION [partner_id], 'landscape', 'Helvetica.afm', 6 );
				
				// print_r($table_arr);
				// die();
				
				// $monat = date("m");
				// $jahr = date("Y");
				$monatsname = monat2name ( $monat );
				$oo = new objekt ();
				$oo->get_objekt_infos ( $objekt_id );
				
				if (! isset ( $_REQUEST ['xls'] )) {
					// $pdf->ezTable($arr[$mo-1],$cols,"$oo->objekt_kurzname - Mietaufstellung $monatsname $jahr",
					// array('showHeadings'=>1,'shaded'=>1, 'titleFontSize' => 8, 'fontSize' => 7, 'xPos'=>50,'xOrientation'=>'right', 'width'=>750,'cols'=>array('EINHEIT_KURZNAME1'=>array('justification'=>'left', 'width'=>55),'TYP'=>array('justification'=>'right', 'width'=>50), 'EINHEIT_QM_A'=>array('justification'=>'right', 'width'=>50), 'MIETE_KALT_MON_A'=>array('justification'=>'right', 'width'=>60), 'MIETER_SEIT'=>array('justification'=>'right', 'width'=>50), 'MIETE_KALT_QM_A'=>array('justification'=>'right', 'width'=>50), 'UMLAGEN_A'=>array('justification'=>'right', 'width'=>55))));
					
					$anz_mo = count ( $arr [$mo - 1] ) - 1;
					$jtab [$mo - 1] = $arr [$mo - 1] [$anz_mo];
					$jtab1 [0] ['MONAT_JAHR'] = 'SUMMEN';
					$jtab1 [0] ['EINHEIT_QM_A'] = '--------';
					$jtab1 [0] ['MIETE_KALT_MON_A'] += nummer_komma2punkt ( $arr [$mo - 1] [$anz_mo] ['MIETE_KALT_MON_A'] );
					$jtab1 [0] ['UMLAGEN_A'] += nummer_komma2punkt ( $arr [$mo - 1] [$anz_mo] ['UMLAGEN_A'] );
					$jtab1 [0] ['BRUTTOM_A'] += nummer_komma2punkt ( $arr [$mo - 1] [$anz_mo] ['BRUTTOM_A'] );
					$jtab1 [0] ['ZAHLUNGEN_A'] += nummer_komma2punkt ( $arr [$mo - 1] [$anz_mo] ['ZAHLUNGEN_A'] );
					
					// $cols = array( 'MIETE_KALT_MON_A'=>'Kaltmiete Monat', 'UMLAGEN_A'=>'Nebenkosten', 'MIETE_BRUTTO'=>'BruttoM', 'ZAHLUNGEN'=>'Zahlung');
					// $pdf->ezTable($jtab,$cols,"$oo->objekt_kurzname - Mietaufstellung $monatsname $jahr",
					// array('showHeadings'=>1,'shaded'=>1, 'titleFontSize' => 8, 'fontSize' => 7, 'xPos'=>50,'xOrientation'=>'right', 'width'=>750,'cols'=>array('EINHEIT_KURZNAME1'=>array('justification'=>'left', 'width'=>55),'TYP'=>array('justification'=>'right', 'width'=>50), 'EINHEIT_QM_A'=>array('justification'=>'right', 'width'=>50), 'MIETE_KALT_MON_A'=>array('justification'=>'right', 'width'=>60), 'MIETER_SEIT'=>array('justification'=>'right', 'width'=>50), 'MIETE_KALT_QM_A'=>array('justification'=>'right', 'width'=>50), 'UMLAGEN_A'=>array('justification'=>'right', 'width'=>55))));
					
					// ob_clean(); //ausgabepuffer leeren
					// header("Content-type: application/pdf"); // wird von MSIE ignoriert
					// $pdf->ezStream();
				} else {
					// echo '<pre>';
					// print_r($arr);
					// die();
					$anz_zeilen = count ( $arr );
					
					ob_clean ();
					// ausgabepuffer leeren
					$fileName = "$oo->objekt_kurzname - Mietaufstellung $monat-$jahr" . '.xls';
					header ( "Content-type: application/vnd.ms-excel" );
					header ( "Content-Disposition: attachment; filename=$fileName" );
					header ( "Content-Disposition: inline; filename=$fileName" );
					ob_clean ();
					// ausgabepuffer leeren
					echo "<table>";
					echo "<thead>";
					echo "<tr>";
					echo "<th>EINHEIT</th>";
					echo "<th>NUTZUNG</th>";
					echo "<th>MIETER</th>";
					echo "<th>EINZUG</th>";
					echo "<th>FLÄCHE</th>";
					echo "<th>KALTMIETE m²</th>";
					echo "<th>MIETE NETTO</th>";
					echo "<th>NK</th>";
					echo "<th>MIETE BRUTTO</th>";
					echo "<th>MWWST</th>";
					echo "<th>ZAHLUNG</th>";
					echo "</tr>";
					echo "</thead>";
					
					for($z = 0; $z < $anz_zeilen - 1; $z ++) {
						$einheit_kn = $arr [$z] ['EINHEIT_KURZNAME'];
						$nutzung = $arr [$z] ['TYP'];
						$mieter = $arr [$z] ['MIETER'];
						$einzug = $arr [$z] ['MIETER_SEIT'];
						$qm = $arr [$z] ['EINHEIT_QM_A'];
						$km_qm = $arr [$z] ['MIETE_KALT_QM_A'];
						$km_mon = $arr [$z] ['MIETE_KALT_MON_A'];
						$nk = $arr [$z] ['UMLAGEN'];
						$wm = $arr [$z] ['MIETE_BRUTTO'];
						$mwst = $arr [$z] ['MWST'];
						$zahlung = $arr [$z] ['ZAHLUNGEN'];
						echo "<tr><td>$einheit_kn</td><td>$nutzung</td><td>$mieter</td><td>$einzug</td><td>$qm</td><td>$km_qm</td><td>$km_mon</td><td>$nk</td><td>$wm</td><td>$mwst</td><td>$zahlung</td></tr>";
					}
					echo "</table>";
					die ();
				}
			} else {
				die ( 'Keine Mietaufstellungsdaten' );
			}
		}
		
		ob_clean ();
		// ausgabepuffer leeren
		
		$pdf->ezTable ( $jtab, null, "$oo->objekt_kurzname - Mietaufstellung  $jahr" );
		
		$jtab1 [0] ['MONAT_JAHR'] = "$jahr";
		$jtab1 [0] ['EINHEIT_QM_A'] = '--------';
		$jtab1 [0] ['MIETE_KALT_MON_A'] = nummer_punkt2komma_t ( $jtab1 [0] ['MIETE_KALT_MON_A'] );
		$jtab1 [0] ['UMLAGEN_A'] = nummer_punkt2komma_t ( $jtab1 [0] ['UMLAGEN_A'] );
		$jtab1 [0] ['BRUTTOM_A'] = nummer_punkt2komma_t ( $jtab1 [0] ['BRUTTOM_A'] );
		$jtab1 [0] ['ZAHLUNGEN_A'] = nummer_punkt2komma_t ( $jtab1 [0] ['ZAHLUNGEN_A'] );
		
		$pdf->ezTable ( $jtab1, null, "$oo->objekt_kurzname - SUMMEN  $jahr" );
		header ( "Content-type: application/pdf" );
		// wird von MSIE ignoriert
		$pdf->ezStream ();
	}
	function get_strassennamen($objekt_id) {
		$result = mysql_query ( "SELECT HAUS_STRASSE FROM HAUS WHERE HAUS_AKTUELL='1' && OBJEKT_ID='$objekt_id' GROUP BY HAUS_STRASSE" );
		while ( $row = mysql_fetch_assoc ( $result ) )
			$haeuser_array [] = $row;
		return $haeuser_array;
	}
	function pdf_checkliste($objekt_id) {
		$this->get_objekt_infos ( $objekt_id );
		ob_clean ();
		// ausgabepuffer leeren
		//include_once ('pdfclass/class.ezpdf.php');
		include_once ('classes/class_bpdf.php');
		
		$pdf = new Cezpdf ( 'a4', 'portrait' );
		$bpdf = new b_pdf ();
		$bpdf->b_header ( $pdf, 'Partner', $_SESSION [partner_id], 'portrait', 'Helvetica.afm', 6 );
		$pdf->ezStopPageNumbers ();
		$pdf->ezSetDy ( - 20 );
		$pdf->ezText ( "<b>CHECKLISTE</b>", 14 );
		$pdf->ezText ( "OBJEKT:", 14 );
		$pdf->ezSetMargins ( 0, 0, 150, 0 );
		$pdf->ezSetDy ( 16 );
		$pdf->ezText ( "$this->objekt_kurzname", 14 );
		
		$haeuser_arr = $this->get_strassennamen ( $objekt_id );
		$anz_h = count ( $haeuser_arr );
		if ($anz_h > 0) {
			
			$strname = '';
			for($a = 0; $a < $anz_h; $a ++) {
				if ($anz_h == 1) {
					$strname .= $haeuser_arr [$a] [HAUS_STRASSE];
				} else {
					$strname .= $haeuser_arr [$a] [HAUS_STRASSE] . ' / ';
				}
			}
		}
		
		$pdf->ezSetMargins ( 0, 0, 50, 0 );
		$pdf->ezText ( "STRASSE:", 14 );
		$pdf->ezSetMargins ( 0, 0, 150, 0 );
		$pdf->ezSetDy ( 16 );
		$pdf->ezText ( "$strname", 14 );
		
		$pdf->ezSetMargins ( 0, 0, 50, 0 );
		$pdf->ezText ( "DATUM:             ________________", 14 );
		$det = new detail ();
		$hw_name_tel = strip_tags ( $det->finde_detail_inhalt ( 'OBJEKT', $objekt_id, 'Hauswart-Tel.' ) );
		if (! $hw_name_tel) {
			$pdf->ezText ( "MITARBEITER:  _____________________________________________", 14 );
		} else {
			// $pdf->ezText("MITARBEITER: $hw_name_tel", 14);
			$pdf->addText ( 50, 700, 14, "<b>Hauswart: $hw_name_tel</b>", 0 );
		}
		
		$pdf->ezSetDy ( - 30 );
		$pdf->ezSetMargins ( 0, 0, 100, 0 );
		
		$pdf->ellipse ( 60, $pdf->y - 10, 10 );
		$pdf->ezText ( "TREPPENREINIGUNG", 12 );
		
		$pdf->ezSetDy ( - 20 );
		$pdf->ellipse ( 60, $pdf->y - 10, 10 );
		$pdf->ezText ( "SPINNENGEWEBE ENTFERNEN", 12 );
		
		$pdf->ezSetDy ( - 20 );
		$pdf->ellipse ( 60, $pdf->y - 10, 10 );
		$pdf->ezText ( "FENSTERBÄNKE UND BRIEFKÄSTEN", 12 );
		
		$pdf->ezSetDy ( - 20 );
		$pdf->ellipse ( 60, $pdf->y - 10, 10 );
		$pdf->ezText ( "GELÄNDER / HANDLAUF", 12 );
		
		$pdf->ezSetDy ( - 20 );
		$pdf->ellipse ( 60, $pdf->y - 10, 10 );
		$pdf->ezText ( "LAMPEN KONTROLLIEREN / GETAUSCHT", 12 );
		
		$pdf->ezSetDy ( - 20 );
		$pdf->ellipse ( 60, $pdf->y - 10, 10 );
		$pdf->ezText ( "KELLER FEGEN", 12 );
		
		$pdf->ezSetDy ( - 20 );
		$pdf->ellipse ( 60, $pdf->y - 10, 10 );
		$pdf->ezText ( "MÜLLPLATZ FEGEN", 12 );
		
		$pdf->ezSetDy ( - 20 );
		$pdf->ellipse ( 60, $pdf->y - 10, 10 );
		$pdf->ezText ( "TÜRSCHLIESSER KONTROLLIEREN / EINSTELLEN", 12 );
		
		$pdf->ezSetDy ( - 20 );
		$pdf->ellipse ( 60, $pdf->y - 10, 10 );
		$pdf->ezText ( "SPERMÜLLBESEITIGUNG", 12 );
		
		$pdf->ezSetDy ( - 20 );
		$pdf->ellipse ( 60, $pdf->y - 10, 10 );
		$pdf->ezText ( "WINTERDIENST", 12 );
		
		$pdf->ezSetDy ( - 20 );
		$pdf->ellipse ( 60, $pdf->y - 10, 10 );
		$pdf->ezText ( "LAUBBESEITIGUNG / GARTENARBEIT", 12 );
		
		$pdf->ezSetMargins ( 0, 0, 50, 0 );
		
		$pdf->ezSetDy ( - 20 );
		$pdf->ezText ( "<u>SONSTIGE HINWEISE AN / VOM HAUSWART:</u>", 12 );
		
		ob_clean ();
		// ausgabepuffer leeren
		header ( "Content-type: application/pdf" );
		// wird von MSIE ignoriert
		$pdf->ezStream ();
	}
	function form_objekt_anlegen() {
		$f = new formular ();
		$f->erstelle_formular ( "Neues Objekt erstellen", NULL );
		echo "<h3>Vor der Objekteingabe muss ein Partner (Eigentümer) erstellt worden sein.</h3>";
		$f->text_feld ( "Objekt Kurzname", "objekt_kurzname", "", "30", 'objekt_kurzname', '' );
		$partner = new partner ();
		$partner_arr = $partner->partner_dropdown ( 'Eigentümer', 'eigentuemer', 'eigentuemer' );
		$f->hidden_feld ( "objekte_raus", "objekt_speichern" );
		$f->send_button ( "submit_obj", "Objekt erstellen" );
		$f->ende_formular ();
	}
	function objekt_speichern($objekt_kurzname, $eigentuemer_id) {
		include_once ('classes/class_bk.php');
		$bk = new bk ();
		$last_id = $bk->last_id ( 'OBJEKT', 'OBJEKT_ID' ) + 1;
		/* Speichern */
		$db_abfrage = "INSERT INTO OBJEKT VALUES(NULL, '$last_id', '1', '$objekt_kurzname','$eigentuemer_id')";
		$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		
		/* Protokollieren */
		$last_dat = mysql_insert_id ();
		protokollieren ( 'OBJEKT', $last_dat, '0' );
	}
	function form_objekt_aendern($objekt_id) {
		$this->get_objekt_infos ( $objekt_id );
		$f = new formular ();
		$f->erstelle_formular ( "Objekt $this->objekt_kurzname ändern", NULL );
		$f->text_feld ( "Objekt Kurzname", "objekt_kurzname", "$this->objekt_kurzname", "30", 'objekt_kurzname', '' );
		$partner = new partner ();
		$partner_arr = $partner->partner_dropdown ( 'Eigentümer', 'eigentuemer', 'eigentuemer' );
		$f->hidden_feld ( "objekt_id", "$this->objekt_id" );
		$f->hidden_feld ( "objekt_dat", "$this->objekt_dat" );
		$f->hidden_feld ( "objekte_raus", "objekt_aendern_send" );
		$f->send_button ( "submit_obj1", "Objekt ändern" );
		$f->ende_formular ();
	}
	function objekt_aendern($objekt_dat, $objekt_id, $objekt_kurzname, $eigentuemer_id) {
		/* Deaktivieren */
		$db_abfrage = "UPDATE OBJEKT SET OBJEKT_AKTUELL='0' WHERE OBJEKT_DAT='$objekt_dat'";
		$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		
		/* Änderung Speichern */
		$db_abfrage = "INSERT INTO OBJEKT VALUES(NULL, '$objekt_id', '1', '$objekt_kurzname','$eigentuemer_id')";
		$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		
		/* Protokollieren */
		$last_dat = mysql_insert_id ();
		protokollieren ( 'OBJEKT', $last_dat, $objekt_dat );
	}
	function date_mysql2german($date) {
		$d = explode ( "-", $date );
		return sprintf ( "%02d.%02d.%04d", $d [2], $d [1], $d [0] );
	}
	function date_german2mysql($date) {
		$d = explode ( ".", $date );
		return sprintf ( "%04d-%02d-%02d", $d [2], $d [1], $d [0] );
	}
	function datum_plus_tage($startdatum, $tage) {
		$db_datum = $startdatum;
		list ( $db_y, $db_m, $db_t ) = explode ( "-", $db_datum );
		$neues_datum = date ( "Y-m-d", mktime ( 0, 0, 0, $db_m, $db_t + $tage, $db_y ) );
		return $neues_datum;
	}
	function datum_minus_tage($startdatum, $tage) {
		$db_datum = $startdatum;
		list ( $db_y, $db_m, $db_t ) = explode ( "-", $db_datum );
		$neues_datum = date ( "Y-m-d", mktime ( 0, 0, 0, $db_m, $db_t - $tage, $db_y ) );
		return $neues_datum;
	}
	function tage_berechnen_bis_heute($start_datum) {
		$heute = mktime ( date ( "h" ), date ( "i" ), date ( "s" ), date ( "m" ), date ( "d" ), date ( "Y" ) );
		$start_datum_arr = explode ( ".", $start_datum );
		$tag = $start_datum_arr [0];
		$monat = $start_datum_arr [1];
		$jahr = $start_datum_arr [2];
		$beginn_datum = mktime ( 0, 0, 0, $monat, $tag, $jahr );
		$tage_vergangen = round ( ($heute - $beginn_datum) / (3600 * 24), 0 );
		// echo "<h3>Seit ".$tag.".".$monat.".".$jahr." sind ".$tage_vergangen.
		" Tage vergangen</h3>";
		// $monate_vergangen = round(($tage_vergangen/30),0);
		// echo "Monate $monate_vergangen";
		return $tage_vergangen;
	}
	function monate_berechnen_bis_heute($start_datum) {
		$heute = time ();
		$start_datum_arr = explode ( ".", $start_datum );
		$tag = $start_datum_arr [0];
		$monat = $start_datum_arr [1];
		$jahr = $start_datum_arr [2];
		$beginn_datum = mktime ( 0, 0, 0, $monat, $tag, $jahr );
		$tage_vergangen = round ( ($heute - $beginn_datum) / (3600 * 24), 0 );
		// echo "<h3>Seit ".$tag.".".$monat.".".$jahr." sind ".$tage_vergangen.
		// " Tage vergangen</h3>\n";
		$monate_vergangen = floor ( $tage_vergangen / 30 );
		// echo "Monate $monate_vergangen";
		return $monate_vergangen;
	}
	function get_objekt_name($objekt_id) {
		$result = mysql_query ( "SELECT OBJEKT_KURZNAME FROM OBJEKT WHERE OBJEKT_AKTUELL='1' && OBJEKT_ID='$objekt_id' ORDER BY OBJEKT_DAT DESC LIMIT 0,1" );
		$row = mysql_fetch_assoc ( $result );
		$this->objekt_name = $row ['OBJEKT_KURZNAME'];
		return $row ['OBJEKT_KURZNAME'];
	}
	function get_objekt_eigentuemer_partner($objekt_id) {
		$result = mysql_query ( "SELECT EIGENTUEMER_PARTNER FROM OBJEKT WHERE OBJEKT_AKTUELL='1' && OBJEKT_ID='$objekt_id' ORDER BY OBJEKT_DAT DESC LIMIT 0,1" );
		$row = mysql_fetch_assoc ( $result );
		$this->objekt_eigentuemer_partner_id = $row ['EIGENTUEMER_PARTNER'];
	}
	
	/* Funktion zur Ermittlung allgemein/notwendiger Objektinformationen */
	function objekt_informationen($objekt_id) {
		$this->objekt_name = $this->get_objekt_name ( $objekt_id );
		$this->objekt_id = $objekt_id;
		$geld_konto_info = new geldkonto_info ();
		$this->anzahl_geld_konten = $geld_konto_info->geldkonten_anzahl ( 'Objekt', $objekt_id );
		if ($this->anzahl_geld_konten > 0) {
			$this->geld_konten_arr = $geld_konto_info->geldkonten_arr ( 'Objekt', $objekt_id );
		}
	}
	function get_objekt_infos($objekt_id) {
		$result = mysql_query ( "SELECT *  FROM `OBJEKT` WHERE OBJEKT_ID = '$objekt_id' && OBJEKT_AKTUELL = '1' ORDER BY OBJEKT_DAT DESC LIMIT 0 , 1 " );
		$row = mysql_fetch_assoc ( $result );
		$this->objekt_dat = $row ['OBJEKT_DAT'];
		$this->objekt_id = $row ['OBJEKT_ID'];
		$this->objekt_kurzname = $row ['OBJEKT_KURZNAME'];
		$this->objekt_eigentuemer_id = $row ['EIGENTUEMER_PARTNER'];
		$p = new partner ();
		$p->partner_grunddaten ( $this->objekt_eigentuemer_id );
		$this->objekt_eigentuemer = $p->partner_name;
		
		// if (preg_match("/c/o/i", "$this->objekt_eigentuemer")) {
		if (stristr ( $this->objekt_eigentuemer, 'c/o' ) == TRUE) {
			$rest = stristr ( $this->objekt_eigentuemer, 'c/o' );
			$this->objekt_eigentuemer_pdf = umbruch_entfernen ( str_replace ( $rest, '', $this->objekt_eigentuemer ) );
		} else {
			// die("nOT FOUND $this->objekt_eigentuemer SIVAC");
			$this->objekt_eigentuemer_pdf = $p->partner_name;
		}
	}
	function get_objekt_geldkonto_nr($objekt_id) {
		$result = mysql_query ( "SELECT DETAIL_INHALT FROM `DETAIL` WHERE DETAIL_NAME = 'Geld Konto Nummer' && DETAIL_ZUORDNUNG_TABELLE = 'OBJEKT' && DETAIL_ZUORDNUNG_ID = '$objekt_id' ORDER BY DETAIL_DAT DESC LIMIT 0 , 1 " );
		$row = mysql_fetch_assoc ( $result );
		$this->objekt_kontonummer = $row ['DETAIL_INHALT'];
	}
	function get_objekt_id($objekt_name) {
		$result = mysql_query ( "SELECT OBJEKT_ID FROM OBJEKT WHERE OBJEKT_AKTUELL='1' && OBJEKT_KURZNAME='$objekt_name' ORDER BY OBJEKT_DAT DESC LIMIT 0,1" );
		$row = mysql_fetch_assoc ( $result );
		// print_r($row);
		$this->objekt_id = $row ['OBJEKT_ID'];
		return $this->objekt_id;
	}
	function get_objekt_anzahl_haeuser($objekt_id) {
		$result = mysql_query ( "SELECT HAUS_ID FROM HAUS WHERE HAUS_AKTUELL='1' && OBJEKT_ID='$objekt_id' ORDER BY HAUS_STRASSE, HAUS_NUMMER ASC" );
		$this->anzahl_haeuser = mysql_numrows ( $result );
	}
	function liste_aller_objekte() {
		$result = mysql_query ( "SELECT * FROM OBJEKT WHERE OBJEKT_AKTUELL='1' ORDER BY OBJEKT_KURZNAME ASC" );
		$objekte_array = array ();
		while ( $row = mysql_fetch_assoc ( $result ) )
			$objekte_array [] = $row;
		$this->anzahl_objekte = count ( $objekte_array );
		return $objekte_array;
	}
	function liste_aller_objekte_kurz() {
		$result = mysql_query ( "SELECT OBJEKT_ID, OBJEKT_KURZNAME FROM OBJEKT WHERE OBJEKT_AKTUELL='1' ORDER BY OBJEKT_KURZNAME ASC" );
		$objekte_array = array ();
		while ( $row = mysql_fetch_assoc ( $result ) )
			$objekte_array [] = $row;
		$this->anzahl_objekte = count ( $objekte_array );
		return $objekte_array;
	}
	function dropdown_objekte($name, $id, $vorwahl = null) {
		$objekte_arr = $this->liste_aller_objekte ();
		echo "<select name=\"$name\" size=1 id=\"$id\">\n";
		for($a = 0; $a < count ( $objekte_arr ); $a ++) {
			$objekt_name = $objekte_arr [$a] ['OBJEKT_KURZNAME'];
			$objekt_id = $objekte_arr [$a] ['OBJEKT_ID'];
			if ($vorwahl == $objekt_name) {
				echo "<option value=\"$objekt_id\" selected>$objekt_name</option>\n";
			} else {
				echo "<option value=\"$objekt_id\">$objekt_name</option>\n";
			}
		}
		echo "</select>\n";
	}
	function dropdown_haeuser_objekt($objekt_id, $label, $name, $id, $vorwahl = '') {
		$haus_arr = $this->haeuser_objekt_in_arr ( $objekt_id );
		echo "<label for=\"$id\">$label</label><select name=\"$name\" size=1 id=\"$id\">\n";
		for($a = 0; $a < count ( $haus_arr ); $a ++) {
			$hh = new haus ();
			$haus_id = $haus_arr [$a] ['HAUS_ID'];
			$hh->get_haus_info ( $haus_id );
			$haus_str = $haus_arr [$a] ['HAUS_STRASSE'];
			$haus_nr = $haus_arr [$a] ['HAUS_NUMMER'];
			if ($vorwahl == $haus_id) {
				echo "<option value=\"$haus_id\" selected>$haus_str $haus_nr $hh->objekt_name</option>\n";
			} else {
				echo "<option value=\"$haus_id\">$haus_str $haus_nr $hh->objekt_name</option>\n";
			}
		}
		echo "</select>\n";
	}
	function liste_haeuser_objekt($objekt_id) {
		$result = mysql_query ( "SELECT * FROM HAUS WHERE HAUS_AKTUELL='1' && OBJEKT_ID='$objekt_id' ORDER BY HAUS_STRASSE, HAUS_NUMMER DESC LIMIT $this->start,$this->zeilen_pro_seite;" );
		
		$haeuser_array = array ();
		for($i = 0; $i < $this->zeilen_pro_seite; $i ++) {
			$row = mysql_fetch_assoc ( $result );
			$haeuser_array [] = $row;
		}
		return $haeuser_array;
	}
	function haeuser_objekt_in_arr($objekt_id) {
		$result = mysql_query ( "SELECT * FROM HAUS WHERE HAUS_AKTUELL='1' && OBJEKT_ID='$objekt_id' ORDER BY HAUS_STRASSE, HAUS_NUMMER ASC" );
		while ( $row = mysql_fetch_assoc ( $result ) )
			$haeuser_array [] = $row;
		return $haeuser_array;
	}
	function anzahl_haeuser_objekt($objekt_id) {
		$result = mysql_query ( "SELECT HAUS_ID FROM HAUS WHERE HAUS_AKTUELL='1' && OBJEKT_ID='$objekt_id' ORDER BY HAUS_STRASSE, HAUS_NUMMER ASC;" );
		$this->anzahl_haeuser = mysql_numrows ( $result );
		$this->seiten_anzahl = ceil ( $this->anzahl_haeuser / $this->zeilen_pro_seite );
	}
	function get_qm_gesamt($objekt_id) {
		$result = mysql_query ( "SELECT OBJEKT_KURZNAME, SUM(EINHEIT_QM) AS GESAMT_QM FROM `EINHEIT` RIGHT JOIN (HAUS, OBJEKT) ON ( EINHEIT.HAUS_ID = HAUS.HAUS_ID && HAUS.OBJEKT_ID = OBJEKT.OBJEKT_ID && OBJEKT.OBJEKT_ID = '$objekt_id' ) WHERE EINHEIT_AKTUELL='1' && HAUS_AKTUELL='1' && OBJEKT_AKTUELL='1' GROUP BY OBJEKT.OBJEKT_ID ORDER BY EINHEIT_KURZNAME ASC" );
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			$row = mysql_fetch_assoc ( $result );
			return $row ['GESAMT_QM'];
		} else {
			return '0.00';
		}
	}
	function get_qm_gesamt_gewerbe($objekt_id) {
		$result = mysql_query ( "SELECT OBJEKT_KURZNAME, SUM(EINHEIT_QM) AS GESAMT_QM FROM `EINHEIT` RIGHT JOIN (HAUS, OBJEKT) ON ( EINHEIT.HAUS_ID = HAUS.HAUS_ID && HAUS.OBJEKT_ID = OBJEKT.OBJEKT_ID && OBJEKT.OBJEKT_ID = '$objekt_id' ) WHERE EINHEIT_AKTUELL='1'  && EINHEIT.TYP = 'Gewerbe' GROUP BY OBJEKT.OBJEKT_ID ORDER BY EINHEIT_KURZNAME ASC" );
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			$row = mysql_fetch_assoc ( $result );
			return $row ['GESAMT_QM'];
		} else {
			return '0.00';
		}
	}
	function einheiten_objekt_arr($objekt_id) {
		$result = mysql_query ( "SELECT OBJEKT_KURZNAME, EINHEIT_ID, EINHEIT_KURZNAME, EINHEIT_LAGE, EINHEIT_QM,  HAUS_STRASSE, HAUS_NUMMER, HAUS_PLZ, HAUS_STADT, TYP FROM `EINHEIT` RIGHT JOIN (HAUS, OBJEKT
) ON ( EINHEIT.HAUS_ID = HAUS.HAUS_ID && HAUS.OBJEKT_ID = OBJEKT.OBJEKT_ID && OBJEKT.OBJEKT_ID = '$objekt_id' )
WHERE EINHEIT_AKTUELL='1' && HAUS_AKTUELL='1' && OBJEKT_AKTUELL='1' GROUP BY EINHEIT_ID ORDER BY LPAD(EINHEIT_KURZNAME, LENGTH(EINHEIT_KURZNAME), '1') ASC" );
		
		/*
		 * echo "SELECT OBJEKT_KURZNAME, EINHEIT_ID, EINHEIT_KURZNAME, EINHEIT_LAGE, EINHEIT_QM, HAUS_STRASSE, HAUS_NUMMER, TYP FROM `EINHEIT` RIGHT JOIN (HAUS, OBJEKT
		 * ) ON ( EINHEIT.HAUS_ID = HAUS.HAUS_ID && HAUS.OBJEKT_ID = OBJEKT.OBJEKT_ID && OBJEKT.OBJEKT_ID = '$objekt_id' )
		 * WHERE EINHEIT_AKTUELL='1' GROUP BY EINHEIT_ID ORDER BY LENGTH(EINHEIT_KURZNAME), EINHEIT_KURZNAME ASC";
		 */
		while ( $row = mysql_fetch_assoc ( $result ) )
			$my_arr [] = $row;
		if (is_array ( $my_arr )) {
			// print_r($my_arr);
			return $my_arr;
		}
	}
	function anzahl_einheiten_objekt($objekt_id) {
		$result = mysql_query ( "SELECT OBJEKT_KURZNAME, EINHEIT_ID, EINHEIT_KURZNAME, EINHEIT_LAGE, EINHEIT_QM,  HAUS_STRASSE, HAUS_NUMMER FROM `EINHEIT`
RIGHT JOIN (HAUS, OBJEKT) ON ( EINHEIT.HAUS_ID = HAUS.HAUS_ID && HAUS.OBJEKT_ID = OBJEKT.OBJEKT_ID && OBJEKT.OBJEKT_ID = '$objekt_id' )
WHERE EINHEIT_AKTUELL='1' GROUP BY EINHEIT_ID ORDER BY EINHEIT_KURZNAME ASC" );
		$anzahl = mysql_numrows ( $result );
		return $anzahl;
	}
	function leerstand_vom_objekt($objekt_id) {
		
		// objektnamen holen
		$this->get_objekt_name ( $objekt_id );
		$objekt_name = $this->objekt_name;
		// echo "OBJEKTNAME $objekt_name SANEL";
		// liste der häuser als array
		$haeuser_arr = $this->haeuser_objekt_in_arr ( $objekt_id );
		if (is_array ( $haeuser_arr )) {
			for($a = 0; $a < count ( $haeuser_arr ); $a ++) {
				$haus_info = new haus ();
				$einheiten_arr [] = $haus_info->liste_aller_einheiten_im_haus ( $haeuser_arr [$a] ['HAUS_ID'] );
			} // end for
		} // end if
		return $einheiten_arr;
	}
	function navi_links() {
		$self = "" . $_SERVER ['PHP_SELF'] . "?objekt_id=" . $_REQUEST [objekt_id] . "";
		$nav = '';
		if (isset ( $_GET ['page'] )) {
			$this->aktuelle_seite = $_GET ['page'];
		}
		for($page = 1; $page <= $this->seiten_anzahl; $page ++) {
			if ($page == $this->aktuelle_seite) {
				$nav .= " $page ";
				// no need to create a link to current page
			} else {
				$nav .= " <a href=\"$self&page=$page\">$page</a> ";
			}
		}
		
		if ($this->aktuelle_seite > 1) {
			$page = $this->aktuelle_seite - 1;
			$prev = " <a href=\"$self&page=$page\">[Prev]</a> ";
			
			$first = " <a href=\"$self&page=1\">[First Page]</a> ";
		} else {
			$prev = '&nbsp;';
			// we're on page one, don't print previous link
			$first = '&nbsp;';
			// nor the first page link
		}
		
		if ($this->aktuelle_seite < $this->seiten_anzahl) {
			$page = $this->aktuelle_seite + 1;
			$next = " <a href=\"$self&page=$page\">[Next]</a> ";
			
			$last = " <a href=\"$self&page=$this->seiten_anzahl\">[Last Page]</a> ";
		} else {
			$next = '&nbsp;';
			// we're on the last page, don't print next link
			$last = '&nbsp;';
			// nor the last page link
		}
		echo $first . $prev . $nav . $next . $last;
	}
} // end class objekt
class haus extends objekt {
	/*
	 * var $objekt_id;
	 * var $objekt_name;
	 * var $haus_strasse;
	 * var $haus_nummer;
	 * var $anzahl_haeuser;
	 * var $anzahl_einheiten;
	 * var $haus_plz;
	 * var $haus_stadt;
	 */
	function get_haus_info($haus_id) {
		$result = mysql_query ( "SELECT * FROM HAUS WHERE HAUS_AKTUELL='1' && HAUS_ID='$haus_id' ORDER BY HAUS_DAT DESC LIMIT 0,1" );
		$row = mysql_fetch_assoc ( $result );
		// print_r($row);
		$this->objekt_id = $row ['OBJEKT_ID'];
		$gg = new geldkonto_info ();
		$gg->geld_konto_ermitteln ( 'OBJEKT', $this->objekt_id );
		$this->get_objekt_name ( $this->objekt_id );
		$this->haus_strasse = $row ['HAUS_STRASSE'];
		$this->haus_nummer = $row ['HAUS_NUMMER'];
		$this->haus_plz = $row ['HAUS_PLZ'];
		$this->haus_stadt = $row ['HAUS_STADT'];
		$this->haus_qm = $row ['HAUS_QM'];
	}
	function liste_aller_haeuser() {
		$result = mysql_query ( "SELECT * FROM HAUS WHERE HAUS_AKTUELL='1' ORDER BY HAUS_STRASSE, HAUS_NUMMER ASC" );
		while ( $row = mysql_fetch_assoc ( $result ) )
			$haeuser_array [] = $row;
		return $haeuser_array;
	}
	function form_haus_aendern($haus_id) {
		$this->get_haus_info ( $haus_id );
		// print_r($this);
		$f = new formular ();
		$f->erstelle_formular ( "Haus ändern - $this->objekt_name $this->haus_strasse $this->haus_nummer", NULL );
		
		$f->text_feld ( "Strasse", "strasse", "$this->haus_strasse", "50", 'strasse', '' );
		$f->text_feld ( "Hausnummer", "haus_nr", "$this->haus_nummer", "10", 'hausnr', '' );
		$f->text_feld ( "Ort", "ort", "$this->haus_stadt", "50", 'ort', '' );
		$f->text_feld ( "Plz", "plz", "$this->haus_plz", "10", 'plz', '' );
		$this->haus_qm_a = nummer_punkt2komma ( $this->haus_qm );
		$f->text_feld ( "Grösse in qm", "qm", "$this->haus_qm_a", "10", 'qm', '' );
		
		$o = new objekt ();
		$o->dropdown_objekte ( 'Objekt', objekt_id, $this->objekt_name );
		
		$f->hidden_feld ( "haus_id", "$haus_id" );
		$f->hidden_feld ( "haus_raus", "haus_aend_speichern" );
		$f->send_button ( "submit_haus", "Änderungen speichern" );
		
		$f->ende_formular ();
	}
	function form_haus_neu($objekt_id = '') {
		$f = new formular ();
		if ($objekt_id != '') {
			$o = new objekt ();
			$o->get_objekt_infos ( $objekt_id );
			if ($o->objekt_kurzname) {
				$f->erstelle_formular ( "Neues Haus im Objekt $o->objekt_kurzname erstellen", NULL );
				$f->text_feld ( "Strasse", "strasse", "", "50", 'strasse', '' );
				$f->text_feld ( "Hausnummer", "haus_nr", "", "10", 'hausnr', '' );
				$f->text_feld ( "Ort", "ort", "", "50", 'ort', '' );
				$f->text_feld ( "Plz", "plz", "", "10", 'plz', '' );
				$f->text_feld ( "Größe in m²", "qm", "", "10", 'qm', '' );
				$f->hidden_feld ( "objekt_id", "$objekt_id" );
				$f->hidden_feld ( "daten_rein", "haus_speichern" );
				$f->send_button ( "submit_haus", "Haus erstellen" );
			} else {
				echo "OBJEKT EXISTIERT NICHT";
			}
		} else {
			$f->erstelle_formular ( "Neues Haus erstellen", NULL );
			$f->text_feld ( "Strasse", "strasse", "", "50", 'strasse', '' );
			$f->text_feld ( "Hausnummer", "haus_nr", "", "10", 'hausnr', '' );
			$f->text_feld ( "Ort", "ort", "", "50", 'ort', '' );
			$f->text_feld ( "Plz", "plz", "", "10", 'plz', '' );
			$f->text_feld ( "Größe in m²", "qm", "", "10", 'qm', '' );
			$o = new objekt ();
			$this->dropdown_objekte ( 'objekt_id', 'objekt_id' );
			$f->hidden_feld ( "daten_rein", "haus_speichern" );
			$f->send_button ( "submit_haus", "Haus erstellen" );
		}
		$f->ende_formular ();
	}
	function haus_speichern($strasse, $haus_nr, $ort, $plz, $qm, $objekt_id) {
		include_once ('classes/class_bk.php');
		$bk = new bk ();
		$last_id = $bk->last_id ( 'HAUS', 'HAUS_ID' ) + 1;
		/* Speichern */
		$db_abfrage = "INSERT INTO HAUS VALUES(NULL, '$last_id', '$strasse', '$haus_nr','$ort', '$plz', '$qm', '1', '$objekt_id')";
		$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		
		/* Protokollieren */
		$last_dat = mysql_insert_id ();
		protokollieren ( 'HAUS', $last_dat, '0' );
		return $last_id;
	}
	function haus_deaktivieren($haus_id) {
		$db_abfrage = "UPDATE HAUS SET HAUS_AKTUELL='0' WHERE HAUS_ID='$haus_id'";
		$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		return true;
	}
	function haus_aenderung_in_db($strasse, $haus_nr, $ort, $plz, $qm, $objekt_id, $haus_id) {
		if ($this->haus_deaktivieren ( $haus_id ) == true) {
			
			/* Speichern */
			$db_abfrage = "INSERT INTO HAUS VALUES(NULL, '$haus_id', '$strasse', '$haus_nr','$ort', '$plz', '$qm', '1', '$objekt_id')";
			$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
			
			/* Protokollieren */
			$last_dat = mysql_insert_id ();
			protokollieren ( 'HAUS', $last_dat, '0' );
			return $last_id;
		} else {
			fehlermeldung_ausgeben ( "Haus konnte nicht geändert werden" );
		}
	}
	function get_qm_gesamt_gewerbe($haus_id) {
		$result = mysql_query ( "SELECT SUM(EINHEIT_QM) AS GESAMT_QM FROM `EINHEIT` WHERE `HAUS_ID` = '$haus_id' AND `EINHEIT_AKTUELL` ='1' && TYP='Gewerbe'" );
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			$row = mysql_fetch_assoc ( $result );
			if ($row ['GESAMT_QM'] != NULL) {
				return $row ['GESAMT_QM'];
			} else {
				return '0.00';
			}
		} else {
			return '0.00';
		}
	}
	function get_qm_gesamt($haus_id) {
		$result = mysql_query ( "SELECT SUM(EINHEIT_QM) AS GESAMT_QM FROM `EINHEIT` WHERE `HAUS_ID` = '$haus_id' AND `EINHEIT_AKTUELL` ='1' " );
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			$row = mysql_fetch_assoc ( $result );
			return $row ['GESAMT_QM'];
		} else {
			return '0.00';
		}
	}
	function dropdown_haeuser_2($label, $name, $id, $vorwahl = '') {
		$haus_arr = $this->liste_aller_haeuser ();
		echo "<label for=\"$id\">$label</label><select name=\"$name\" size=1 id=\"$id\">\n";
		for($a = 0; $a < count ( $haus_arr ); $a ++) {
			$hh = new haus ();
			$haus_id = $haus_arr [$a] ['HAUS_ID'];
			$hh->get_haus_info ( $haus_id );
			$haus_str = $haus_arr [$a] ['HAUS_STRASSE'];
			$haus_nr = $haus_arr [$a] ['HAUS_NUMMER'];
			if ($vorwahl == $haus_id) {
				echo "<option value=\"$haus_id\" selected>$haus_str $haus_nr $hh->objekt_name</option>\n";
			} else {
				echo "<option value=\"$haus_id\">$haus_str $haus_nr $hh->objekt_name</option>\n";
			}
		}
		echo "</select>\n";
	}
	function dropdown_haeuser($name, $id) {
		$haus_arr = $this->liste_aller_haeuser ();
		echo "<select name=\"$name\" size=1 id=\"$id\">\n";
		for($a = 0; $a < count ( $haus_arr ); $a ++) {
			$hh = new haus ();
			$haus_id = $haus_arr [$a] ['HAUS_ID'];
			$hh->get_haus_info ( $haus_id );
			
			$haus_str = $haus_arr [$a] ['HAUS_STRASSE'];
			$haus_nr = $haus_arr [$a] ['HAUS_NUMMER'];
			echo "<option value=\"$haus_id\">$haus_str $haus_nr $hh->objekt_name</option>\n";
		}
		echo "</select>\n";
	}
	function liste_aller_einheiten_im_haus($haus_id) {
		$result = mysql_query ( "SELECT * FROM EINHEIT WHERE EINHEIT_AKTUELL='1' && HAUS_ID='$haus_id' ORDER BY EINHEIT_KURZNAME ASC" );
		$einheiten_array = array ();
		while ( $row = mysql_fetch_assoc ( $result ) )
			$einheiten_array [] = $row;
		$this->anzahl_einheiten = count ( $einheiten_array );
		return $einheiten_array;
	}
	function get_haus_id($haus_name) {
		$haus_arr = explode ( ' ', $haus_name );
		$anzahl_el = count ( $haus_arr );
		$nr_el = $anzahl_el - 1;
		$hnr = $nr_el - 1;
		// settype($bar, "string");
		$haus_nr = $haus_arr [$nr_el];
		// $haus_nr = settype($haus_nr, "string");
		// ctype_digit($numeric_string); // true
		if (! ctype_digit ( $haus_nr )) {
			if (ctype_alnum ( $haus_nr )) {
				$haus_nr = $haus_arr [$hnr] . ' ' . $haus_nr;
				$nr_el = $nr_el - 1;
			} else {
				$haus_nr = $haus_arr [$nr_el];
			}
		}
		
		for($a = 0; $a < $nr_el; $a ++) {
			$haus_strasse = $haus_strasse . " $haus_arr[$a]";
		}
		$haus_strasse = ltrim ( rtrim ( $haus_strasse ) );
		$result = mysql_query ( "SELECT HAUS_ID FROM HAUS WHERE HAUS_AKTUELL='1' && HAUS_STRASSE='$haus_strasse' && HAUS_NUMMER='$haus_nr' ORDER BY HAUS_DAT DESC LIMIT 0,1" );
		
		$row = mysql_fetch_assoc ( $result );
		$this->haus_id = $row ['HAUS_ID'];
	}
}
class einheit extends haus {
	/*
	 * var $objekt_id;
	 * var $objekt_name;
	 * var $haus_id;
	 * var $haus_strasse;
	 * var $haus_nummer;
	 * var $einheit_kurzname;
	 * var $einheit_qm;
	 * var $einheit_lage;
	 * var $anzahl_einheiten;
	 * var $haus_plz;
	 * var $haus_stadt;
	 * var $datum_heute;
	 * var $mietvertrag_id;
	 */
	function emails_mieter_arr($objekt_id) {
		if ($objekt_id == null) {
			$db_abfrage = "SELECT OBJEKT_KURZNAME, HAUS_STRASSE, HAUS_NUMMER, `EINHEIT_KURZNAME` ,EINHEIT_ID,  `EINHEIT_LAGE` , `EINHEIT_QM`, `TYP` FROM EINHEIT , HAUS, OBJEKT
WHERE `EINHEIT_AKTUELL` = '1' && EINHEIT.HAUS_ID = HAUS.HAUS_ID && HAUS.OBJEKT_ID=OBJEKT.OBJEKT_ID && HAUS_AKTUELL='1' && OBJEKT_AKTUELL='1'
ORDER BY HAUS_STRASSE, HAUS_NUMMER, OBJEKT_KURZNAME, EINHEIT_LAGE";
		} else {
			$db_abfrage = "SELECT OBJEKT_KURZNAME, HAUS_STRASSE, HAUS_NUMMER, `EINHEIT_KURZNAME` ,EINHEIT_ID,  `EINHEIT_LAGE` , `EINHEIT_QM`, TYP FROM EINHEIT , HAUS, OBJEKT
				WHERE `EINHEIT_AKTUELL` = '1' && EINHEIT.HAUS_ID = HAUS.HAUS_ID && HAUS.OBJEKT_ID=OBJEKT.OBJEKT_ID && HAUS_AKTUELL='1' && OBJEKT_AKTUELL='1' && OBJEKT.OBJEKT_ID='$objekt_id'
				ORDER BY EINHEIT_KURZNAME";
		}
		$result = mysql_query ( $db_abfrage );
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			$z = 0;
			$emails_arr = '';
			while ( $row = mysql_fetch_assoc ( $result ) ) {
				
				$einheit_id = $row ['EINHEIT_ID'];
				$mv_id = $this->get_mietvertrag_id ( $einheit_id );
				if ($mv_id) {
					$mvs = new mietvertraege ();
					$mvs->get_mietvertrag_infos_aktuell ( $mv_id );
					
					$anz_p = count ( $mvs->personen_ids );
					for($pp = 0; $pp < $anz_p; $pp ++) {
						$p_id = $mvs->personen_ids [$pp] ['PERSON_MIETVERTRAG_PERSON_ID'];
						$detail = new detail ();
						if (($detail->finde_detail_inhalt ( 'PERSON', $p_id, 'Email' ))) {
							$email_arr = $detail->finde_alle_details_grup ( 'PERSON', $p_id, 'Email' );
							for($ema = 0; $ema < count ( $email_arr ); $ema ++) {
								$em_adr = $email_arr [$ema] ['DETAIL_INHALT'];
								$emails_arr [] = $em_adr;
							}
						}
					}
					
					// die();
				}
			}
			
			$emails_arr_u = array_values ( array_unique ( $emails_arr ) );
			unset ( $email_arr );
			unset ( $emails_arr );
			return $emails_arr_u;
		}
	}
	function uebersicht_einheit_leer($einheit_id) {
		$e = new einheit ();
		$e->get_einheit_info ( $einheit_id );
		// ################################## BALKEN EINHEIT---->
		echo "<div class=\"div balken1\"><span class=\"font_balken_uberschrift\">EINHEIT</span><hr />";
		echo "<span class=\"font_balken_uberschrift\">$e->einheit_kurzname</span><hr/>";
		echo "$e->haus_strasse $e->haus_nummer<br/>";
		echo "$e->haus_plz $e->haus_stadt<br/>";
		echo "Lage: $e->einheit_lage QM: $e->einheit_qm m²<hr/>";
		$details_info = new details ();
		$einheit_details_arr = $details_info->get_details ( 'EINHEIT', $einheit_id );
		if (count ( $einheit_details_arr ) > 0) {
			echo "<b>AUSSTATTUNG</b><hr>";
			for($i = 0; $i < count ( $einheit_details_arr ); $i ++) {
				echo "<b>" . $einheit_details_arr [$i] ['DETAIL_NAME'] . "</b>:<br>" . $einheit_details_arr [$i] ['DETAIL_INHALT'] . "<br>";
			}
		} else {
			echo "k.A zur Ausstattung";
		}
		$link_einheit_details = "<a href=\"?daten=details&option=details_hinzu&detail_tabelle=EINHEIT&detail_id=$einheit_id\">NEUES DETAIL ZUR EINHEIT $e->einheit_kurzname</a>";
		echo "<hr>$link_einheit_details<hr>";
		$details_info = new details ();
		$objekt_details_arr = $details_info->get_details ( 'OBJEKT', $e->objekt_id );
		echo "<hr /><b>OBJEKT</b>: $e->objekt_name<hr/>";
		for($i = 0; $i < count ( $objekt_details_arr ); $i ++) {
			echo "<b>" . $objekt_details_arr [$i] ['DETAIL_NAME'] . "</b><br>" . $objekt_details_arr [$i] ['DETAIL_INHALT'] . "<br>";
		}
		$link_objekt_details = "<a href=\"?daten=details&option=details_hinzu&detail_tabelle=OBJEKT&detail_id=$e->objekt_id\">NEUES DETAIL ZUM OBJEKT $e->objekt_name</a>";
		echo "<hr>$link_objekt_details<hr>";
		echo "</div>";
		// #ende spalte objekt und einheit####
	}
	function pdf_mieterliste($aktuell = 1, $objekt_id = null) {
		ini_set ( 'display_errors', 'On' );
		error_reporting ( E_ALL | E_STRICT );
		if ($objekt_id == null) {
			$db_abfrage = "SELECT OBJEKT_KURZNAME, HAUS_STRASSE, HAUS_NUMMER, `EINHEIT_KURZNAME` ,EINHEIT_ID,  `EINHEIT_LAGE` , `EINHEIT_QM`, `TYP` FROM EINHEIT , HAUS, OBJEKT
WHERE `EINHEIT_AKTUELL` = '1' && EINHEIT.HAUS_ID = HAUS.HAUS_ID && HAUS.OBJEKT_ID=OBJEKT.OBJEKT_ID && HAUS_AKTUELL='1' && OBJEKT_AKTUELL='1'
ORDER BY HAUS_STRASSE, HAUS_NUMMER, OBJEKT_KURZNAME, EINHEIT_LAGE";
		} else {
			$db_abfrage = "SELECT OBJEKT_KURZNAME, HAUS_STRASSE, HAUS_NUMMER, `EINHEIT_KURZNAME` ,EINHEIT_ID,  `EINHEIT_LAGE` , `EINHEIT_QM`, TYP FROM EINHEIT , HAUS, OBJEKT
WHERE `EINHEIT_AKTUELL` = '1' && EINHEIT.HAUS_ID = HAUS.HAUS_ID && HAUS.OBJEKT_ID=OBJEKT.OBJEKT_ID && HAUS_AKTUELL='1' && OBJEKT_AKTUELL='1' && OBJEKT.OBJEKT_ID='$objekt_id' 
ORDER BY EINHEIT_KURZNAME";
		}
		$result = mysql_query ( $db_abfrage );
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			$z = 0;
			while ( $row = mysql_fetch_assoc ( $result ) ) {
				$my_arr [] = $row;
				$einheit_id = $row ['EINHEIT_ID'];
				$mv_id = $this->get_mietvertrag_id ( $einheit_id );
				if ($mv_id) {
					$mvs = new mietvertraege ();
					$mvs->get_mietvertrag_infos_aktuell ( $mv_id );
					$kontaktdaten = $this->kontaktdaten_mieter ( $mv_id );
					// $my_arr[$z]['MIETER'] = $mvs->personen_name_string_u."\n".$kontaktdaten;
					$my_arr [$z] ['MIETER'] = $mvs->personen_name_string_u;
					$my_arr [$z] ['KONTAKT'] = $kontaktdaten;
				} else {
					$my_arr [$z] ['MIETER'] = 'Leerstand';
				}
				$z ++;
			}
		} else {
			echo "NO!sdsd";
		}
		
		// echo '<pre>';
		// print_r($my_arr);
		/*
		 * $pdf->ezTable($table_arr,array('OBJEKT_NAME'=>'Objekt','KONTOSTAND1_1'=>"Kontostand $datum_jahresanfang",'ME_MONAT'=>"Mieten Einnahmen $monatname",'ME_JAHR'=>"Mieten Einnahmen $jahr", 'KOSTEN_MONAT'=>"Kosten $monatname",'KOSTEN_JAHR'=>"Kosten $jahr", 'KONTOSTAND_AKTUELL'=>"Kontostand")
		 * ,'<b>Kosten & Einnahmen / Objekt (Tabellarische Übersicht)</b>', array('shaded'=>0, 'width'=>'500', 'justification'=>'right', 'cols'=>array(
		 * 'KONTOSTAND1_1'=>array('justification'=>'right'),'ME_MONAT'=>array('justification'=>'right'), 'ME_MONAT'=>array('justification'=>'right'),'ME_JAHR'=>array('justification'=>'right'),'KOSTEN_MONAT'=>array('justification'=>'right'),'KOSTEN_JAHR'=>array('justification'=>'right'), 'KONTOSTAND_AKTUELL'=>array('justification'=>'right'))));
		 */
		
		//include_once ('pdfclass/class.ezpdf.php');
		include_once ('classes/class_bpdf.php');
		$pdf = new Cezpdf ( 'a4', 'portrait' );
		$bpdf = new b_pdf ();
		$bpdf->b_header ( $pdf, 'Partner', $_SESSION [partner_id], 'portrait', 'Helvetica.afm', 6 );
		$db_abfrage = "SELECT OBJEKT_KURZNAME, HAUS_STRASSE, HAUS_NUMMER, `EINHEIT_KURZNAME` ,EINHEIT_ID,  `EINHEIT_LAGE` , `EINHEIT_QM`, TYP FROM EINHEIT , HAUS, OBJEKT
WHERE `EINHEIT_AKTUELL` = '1' && EINHEIT.HAUS_ID = HAUS.HAUS_ID && HAUS.OBJEKT_ID=OBJEKT.OBJEKT_ID && HAUS_AKTUELL='1' && OBJEKT_AKTUELL='1'
ORDER BY OBJEKT_KURZNAME, HAUS_STRASSE, HAUS_NUMMER, EINHEIT_LAGE";
		$cols = array (
				'OBJEKT_KURZNAME' => "Objekt",
				'HAUS_STRASSE' => "Strasse",
				'HAUS_NUMMER' => 'Nr',
				'EINHEIT_KURZNAME' => 'Einheit',
				'TYP' => 'Typ',
				'EINHEIT_LAGE' => 'Lage',
				'EINHEIT_QM' => 'Fläche m²',
				'MIETER' => 'Mieterinfos',
				'MIETER' => 'Mieter',
				'KONTAKT' => 'Kontakt' 
		);
		// print_r($table_arr);
		// die();
		$pdf->ezTable ( $my_arr, $cols, "Alle Einheiten", array (
				'showHeadings' => 1,
				'shaded' => 1,
				'titleFontSize' => 8,
				'fontSize' => 7,
				'xPos' => 50,
				'xOrientation' => 'right',
				'width' => 500,
				'cols' => array (
						'OBJEKT' => array (
								'justification' => 'left',
								'width' => 65 
						),
						'HAUS_NUMMER' => array (
								'justification' => 'right',
								'width' => 30 
						),
						'EINHEIT_QM' => array (
								'justification' => 'right',
								'width' => 30 
						) 
				) 
		) );
		
		ob_clean ();
		// ausgabepuffer leeren
		header ( "Content-type: application/pdf" );
		// wird von MSIE ignoriert
		$pdf->ezStream ();
	}
	function kontaktdaten_mieter($mv_id) {
		$result = mysql_query ( "SELECT PERSON_MIETVERTRAG_PERSON_ID FROM PERSON_MIETVERTRAG WHERE PERSON_MIETVERTRAG_MIETVERTRAG_ID='$mv_id' && PERSON_MIETVERTRAG_AKTUELL='1' ORDER BY PERSON_MIETVERTRAG_ID ASC" );
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			$kontaktdaten = '';
			while ( $row = mysql_fetch_assoc ( $result ) ) {
				$person_id = $row ['PERSON_MIETVERTRAG_PERSON_ID'];
				$arr = $this->finde_detail_kontakt_arr ( 'PERSON', $person_id );
				if (is_array ( $arr )) {
					$anz = count ( $arr );
					for($a = 0; $a < $anz; $a ++) {
						$dname = $arr [$a] ['DETAIL_NAME'];
						$dinhalt = $arr [$a] ['DETAIL_INHALT'];
						$kontaktdaten .= "<b>$dname</b>:$dinhalt ";
					}
				}
			}
			return $kontaktdaten;
		}
	}
	function finde_detail_kontakt_arr($tab, $id) {
		$db_abfrage = "SELECT DETAIL_NAME, DETAIL_INHALT FROM DETAIL WHERE DETAIL_ZUORDNUNG_TABELLE = '$tab' && (DETAIL_NAME LIKE '%tel%'or DETAIL_NAME LIKE '%fax%' or DETAIL_NAME LIKE '%mobil%' or DETAIL_NAME LIKE '%handy%' OR DETAIL_NAME LIKE '%mail%') && DETAIL_ZUORDNUNG_ID = '$id' && DETAIL_AKTUELL = '1' ORDER BY DETAIL_NAME ASC";
		$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		$numrows = mysql_numrows ( $resultat );
		if ($numrows) {
			while ( $row = mysql_fetch_assoc ( $resultat ) )
				$my_array [] = $row;
			return $my_array;
		}
	}
	function get_einheit_info($einheit_id) {
		unset ( $this->einheit_dat );
		unset ( $this->typ );
		$result = mysql_query ( "SELECT * FROM EINHEIT WHERE EINHEIT_AKTUELL='1' && EINHEIT_ID='$einheit_id' ORDER BY EINHEIT_DAT DESC LIMIT 0,1" );
		$row = mysql_fetch_assoc ( $result );
		// print_r($row);
		$this->einheit_dat = $row ['EINHEIT_DAT'];
		$this->haus_id = $row ['HAUS_ID'];
		$this->einheit_kurzname = ltrim ( rtrim ( $row ['EINHEIT_KURZNAME'] ) );
		$this->einheit_qm = ltrim ( rtrim ( $row ['EINHEIT_QM'] ) );
		$this->einheit_qm_d = nummer_punkt2komma ( $this->einheit_qm );
		// $this->einheit_qm_d = number_format($this->einheit_qm, 2,","," ");
		$this->einheit_lage = ltrim ( rtrim ( $row ['EINHEIT_LAGE'] ) );
		$this->get_haus_info ( $this->haus_id );
		$this->typ = $row ['TYP'];
		if ($this->typ == 'Gewerbe') {
			$this->einheit_qm_gewerbe = $this->einheit_qm;
		} else {
			$this->einheit_qm_gewerbe = 0.00;
		}
		
		$d = new detail ();
		$this->aufzug_prozent_d = $d->finde_detail_inhalt ( 'Einheit', $einheit_id, 'WEG-Aufzugprozent' );
		$this->aufzug_prozent = nummer_komma2punkt ( $this->aufzug_prozent_d );
	}
	function get_mietvertrag_id($einheit_id) {
		$this->datum_heute = date ( "Y-m-d" );
		$result = mysql_query ( "SELECT MIETVERTRAG_ID FROM MIETVERTRAG WHERE EINHEIT_ID = '$einheit_id' && MIETVERTRAG_AKTUELL = '1'  && ( MIETVERTRAG_BIS >= '$this->datum_heute' OR MIETVERTRAG_BIS = '0000-00-00' ) ORDER BY MIETVERTRAG_VON DESC LIMIT 0 , 1 " );
		$numrows = mysql_numrows ( $result );
		
		if ($numrows > 0) {
			$row = mysql_fetch_assoc ( $result );
			$this->mietvertrag_id = $row ['MIETVERTRAG_ID'];
			return $this->mietvertrag_id;
		} else {
			return false;
		}
	}
	function get_last_mietvertrag_id($einheit_id) {
		$result = mysql_query ( "SELECT MIETVERTRAG_ID FROM MIETVERTRAG WHERE EINHEIT_ID = '$einheit_id' && MIETVERTRAG_AKTUELL = '1'  ORDER BY MIETVERTRAG_VON DESC LIMIT 0 , 1 " );
		$numrows = mysql_numrows ( $result );
		if ($numrows > 0) {
			$row = mysql_fetch_assoc ( $result );
			$this->mietvertrag_id = $row ['MIETVERTRAG_ID'];
			return $this->mietvertrag_id;
		} else {
			return false;
		}
	}
	
	/* Alle Mietverträge einer Einheit */
	function get_mietvertrag_ids($einheit_id) {
		$result = mysql_query ( "SELECT MIETVERTRAG_ID FROM MIETVERTRAG WHERE EINHEIT_ID = '$einheit_id' && MIETVERTRAG_AKTUELL = '1' ORDER BY MIETVERTRAG_VON ASC" );
		// echo "SELECT MIETVERTRAG_ID FROM MIETVERTRAG WHERE EINHEIT_ID = '$einheit_id' && MIETVERTRAG_AKTUELL = '1' ORDER BY MIETVERTRAG_VON ASC";
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			while ( $row = mysql_fetch_assoc ( $result ) )
				$my_arr [] = $row;
			return $my_arr;
		} else {
			return false;
		}
	}
	function get_einheit_as_array($einheit_id) {
		$result = mysql_query ( "SELECT * FROM EINHEIT WHERE EINHEIT_AKTUELL='1' && EINHEIT_ID='$einheit_id' ORDER BY EINHEIT_DAT LIMIT 0,1" );
		$einheiten_array = array ();
		while ( $row = mysql_fetch_assoc ( $result ) )
			$einheiten_array [] = $row;
		$this->anzahl_einheiten = count ( $einheiten_array );
		return $einheiten_array;
	}
	function get_einheit_typ_arr() {
		$result = mysql_query ( "SHOW COLUMNS FROM EINHEIT WHERE FIELD = 'TYP'" );
        $row = mysql_fetch_assoc ( $result );
        preg_match("/^enum\(\'(.*)\'\)$/", $row['Type'], $matches);
		$typ_array = explode("','", $matches[1]);
		return $typ_array;
	}
	
	/* Alle Mietverträge einer Einheit bis Monat(zweistellig*) Jahr(vierstellig) */
	function get_mietvertraege_bis($einheit_id, $jahr, $monat) {
		if (strlen ( $monat ) < 2) {
			$monat = '0' . $monat;
		}
		$result = mysql_query ( "SELECT MIETVERTRAG_ID FROM MIETVERTRAG WHERE EINHEIT_ID = '$einheit_id' && MIETVERTRAG_AKTUELL = '1' && DATE_FORMAT( MIETVERTRAG_VON, '%Y-%m' ) <= '$jahr-$monat' ORDER BY MIETVERTRAG_VON ASC" );
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			while ( $row = mysql_fetch_assoc ( $result ) )
				$my_arr [] = $row;
			return $my_arr;
		} else {
			return false;
		}
	}
	
	/* Mietverträge einer Einheit im Monat(zweistellig*) Jahr(vierstellig) */
	function get_mietvertraege_zu($einheit_id, $jahr, $monat, $asc = 'ASC') {
		if (isset ( $this->mietvertrag_id )) {
			unset ( $this->mietvertrag_id );
		}
		
		if (strlen ( $monat ) < 2) {
			$monat = '0' . $monat;
		}
		$result = mysql_query ( "SELECT MIETVERTRAG_ID FROM MIETVERTRAG WHERE EINHEIT_ID = '$einheit_id' && MIETVERTRAG_AKTUELL = '1' && DATE_FORMAT( MIETVERTRAG_VON, '%Y-%m' ) <= '$jahr-$monat' && (DATE_FORMAT( MIETVERTRAG_BIS, '%Y-%m' ) >= '$jahr-$monat' OR MIETVERTRAG_BIS='0000-00-00') ORDER BY MIETVERTRAG_VON $asc LIMIT 0,1" );
		// echo "SELECT MIETVERTRAG_ID FROM MIETVERTRAG WHERE EINHEIT_ID = '$einheit_id' && MIETVERTRAG_AKTUELL = '1' && DATE_FORMAT( MIETVERTRAG_VON, '%Y-%m' ) <= '$jahr-$monat' && (DATE_FORMAT( MIETVERTRAG_BIS, '%Y-%m' ) >= '$jahr-$monat' OR MIETVERTRAG_BIS='0000-00-00') ORDER BY MIETVERTRAG_VON ASC LIMIT 0,1";
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			$row = mysql_fetch_assoc ( $result );
			$this->mietvertrag_id = $row ['MIETVERTRAG_ID'];
			return $this->mietvertrag_id;
		} else {
			return false;
		}
	}
	function get_mietvertraege_zu2($einheit_id, $jahr, $monat) {
		if (isset ( $this->mietvertrag_id )) {
			unset ( $this->mietvertrag_id );
		}
		
		if (strlen ( $monat ) < 2) {
			$monat = '0' . $monat;
		}
		$result = mysql_query ( "SELECT MIETVERTRAG_ID FROM MIETVERTRAG WHERE EINHEIT_ID = '$einheit_id' && MIETVERTRAG_AKTUELL = '1' && DATE_FORMAT( MIETVERTRAG_VON, '%Y-%m' ) <= '$jahr-$monat' && (DATE_FORMAT( MIETVERTRAG_BIS, '%Y-%m' ) >= '$jahr-$monat' OR MIETVERTRAG_BIS='0000-00-00') " );
		// echo "SELECT MIETVERTRAG_ID FROM MIETVERTRAG WHERE EINHEIT_ID = '$einheit_id' && MIETVERTRAG_AKTUELL = '1' && DATE_FORMAT( MIETVERTRAG_VON, '%Y-%m' ) <= '$jahr-$monat' && (DATE_FORMAT( MIETVERTRAG_BIS, '%Y-%m' ) >= '$jahr-$monat' OR MIETVERTRAG_BIS='0000-00-00') ORDER BY MIETVERTRAG_VON ASC LIMIT 0,1";
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			while ( $row = mysql_fetch_assoc ( $result ) )
				$my_array [] = $row;
			return $my_array;
		} else {
			return false;
		}
	}
	function get_einheit_haus($einheit_id) {
		$result = mysql_query ( "SELECT HAUS_ID, EINHEIT_KURZNAME FROM EINHEIT WHERE EINHEIT_AKTUELL='1' && EINHEIT_ID='$einheit_id' ORDER BY EINHEIT_DAT DESC LIMIT 0,1" );
		$row = mysql_fetch_assoc ( $result );
		// print_r($row);
		$this->haus_id = $row ['HAUS_ID'];
		$this->get_haus_info ( $row ['HAUS_ID'] );
		$this->einheit_kurzname = $row ['EINHEIT_KURZNAME'];
		$this->get_einheit_info ( $einheit_id );
	}
	function get_einheit_id($einheit_name) {
		$result = mysql_query ( "SELECT EINHEIT_ID FROM EINHEIT WHERE EINHEIT_AKTUELL='1' && EINHEIT_KURZNAME='$einheit_name' ORDER BY EINHEIT_DAT DESC LIMIT 0,1" );
		// echo "SELECT EINHEIT_ID FROM EINHEIT WHERE EINHEIT_AKTUELL='1' && EINHEIT_KURZNAME='$einheit_name' ORDER BY EINHEIT_DAT DESC LIMIT 0,1";
		
		$row = mysql_fetch_assoc ( $result );
		$this->einheit_id = $row ['EINHEIT_ID'];
	}
	function get_einheit_status($einheit_id) {
		$this->datum_heute = date ( "Y-m-d" );
		$result = mysql_query ( "SELECT * FROM MIETVERTRAG WHERE EINHEIT_ID = '$einheit_id' && MIETVERTRAG_AKTUELL = '1' && ( MIETVERTRAG_BIS >= '$this->datum_heute' OR MIETVERTRAG_BIS = '0000-00-00' ) LIMIT 0 , 1 " );
		// echo "SELECT * FROM MIETVERTRAG WHERE EINHEIT_ID = '$einheit_id' && MIETVERTRAG_AKTUELL = '1' && ( MIETVERTRAG_BIS >= '$this->datum_heute' OR MIETVERTRAG_BIS = '0000-00-00' ) LIMIT 0 , 1 ";
		$numrows = mysql_numrows ( $result );
		if ($numrows > 0) {
			return true;
		} else {
			return false;
		}
	}
	function liste_aller_einheiten() {
		$result = mysql_query ( "SELECT * FROM EINHEIT WHERE EINHEIT_AKTUELL='1' ORDER BY LENGTH(EINHEIT_KURZNAME), EINHEIT_KURZNAME" );
		while ( $row = mysql_fetch_assoc ( $result ) )
			$einheiten_array [] = $row;
		$this->anzahl_einheiten = count ( $einheiten_array );
		return $einheiten_array;
	}
	function finde_einheit_id_by_kurz($anfang) {
		$result = mysql_query ( "SELECT EINHEIT_ID FROM EINHEIT WHERE EINHEIT_AKTUELL='1' && EINHEIT_KURZNAME LIKE '$anfang%' ORDER BY EINHEIT_DAT DESC LIMIT 0,1" );
		$einheiten_array = array ();
		$row = mysql_fetch_assoc ( $result );
		return $row ['EINHEIT_ID'];
	}
	function dropdown_einheiten($name, $id) {
		$einheiten_arr = $this->liste_aller_einheiten ();
		echo "<select name=\"$name\" size=1 id=\"$id\">\n";
		for($a = 0; $a < count ( $einheiten_arr ); $a ++) {
			$einheit_kurzname = $einheiten_arr [$a] [EINHEIT_KURZNAME];
			$einheit_id = $einheiten_arr [$a] [EINHEIT_ID];
			echo "<option value=\"$einheit_id\">$einheit_kurzname</option>\n";
		}
		echo "</select>\n";
	}
	function dropdown_einheit_typen($label, $name, $id, $vorwahl) {
		$arr = $this->get_einheit_typ_arr ();
		// print_r($arr);
		if (is_array ( $arr )) {
			echo "<label for=\"$id\">$label</label><select name=\"$name\" size=1 id=\"$id\">\n";
			$anz = count ( $arr );
			for($a = 0; $a < $anz; $a ++) {
				$typ = $arr [$a];
				if ($typ == $vorwahl) {
					echo "<option value=\"$typ\" selected>$typ</option>\n";
				} else {
					echo "<option value=\"$typ\">$typ</option>\n";
				}
			} // end for
			echo "</select>\n";
		} else {
			fehlermeldung_ausgeben ( "Keine Einheiten erfasst!" );
		}
	}
	function letzter_vormieter($einheit_id) {
		$datum_heute = date ( "Y-m-d" );
		$result = mysql_query ( "SELECT MIETVERTRAG_ID FROM `MIETVERTRAG` WHERE EINHEIT_ID = '$einheit_id' && MIETVERTRAG_AKTUELL='1' && ((MIETVERTRAG_BIS<'$datum_heute') && (MIETVERTRAG_BIS!='0000-00-00')) ORDER BY MIETVERTRAG_BIS DESC LIMIT 0,1" );
		$row = mysql_fetch_assoc ( $result );
		$mietvertrag_id = $row ['MIETVERTRAG_ID'];
		$mv_info = new mietvertrag ();
		$vormieter_array = $mv_info->get_personen_ids_mietvertrag ( $mietvertrag_id );
		return $vormieter_array;
	}
	function liste_aller_leerstaende() {
		$alle_einheiten_array = $this->liste_aller_einheiten ();
	}
	function liste_vermieteter_einheiten() {
		$datum_heute = date ( "Y-m-d" );
		$alle_einheiten_array = $this->liste_aller_einheiten ();
		for($a = 0; $a < count ( $alle_einheiten_array ); $a ++) {
			
			$result = mysql_query ( "SELECT MIETVERTRAG_ID FROM MIETVERTRAG WHERE MIETVERTRAG_AKTUELL='1' && EINHEIT_ID='$alle_einheiten_array[$a][EINHEIT_ID]' && (MIETVERTRAG_BIS>='$datum_heute' OR MIETVERTRAG_BIS = '0000-00-00') ORDER BY MIETVERTRAG_DAT DESC LIMIT 0,1" );
		}
	}
	function form_einheit_neu($haus_id = '') {
		$f = new formular ();
		if ($haus_id != '') {
			$h = new haus ();
			$h->get_haus_info ( $haus_id );
			if ($h->haus_strasse) {
				$f->erstelle_formular ( "Neue Einheit im Haus $h->haus_strasse $h->haus_nummer erstellen", NULL );
				$f->text_feld ( "Kurzname", "kurzname", "", "50", 'kurzname', '' );
				$f->text_feld ( "Lage", "lage", "", "10", 'lage', '' );
				$f->text_feld ( "m²", "qm", "", "10", 'qm', '' );
				$this->dropdown_einheit_typen ( 'Typ', 'typ', 'typ', 'Wohnraum' );
				$f->hidden_feld ( "einheit_raus", "einheit_speichern" );
				$f->send_button ( "submit_einheit", "Einheit erstellen" );
				$f->hidden_feld ( "haus_id", "$haus_id" );
			} else {
				echo "OBJEKT EXISTIERT NICHT";
			}
		} else {
			$f->erstelle_formular ( "Neue Einheit erstellen", NULL );
			$f->text_feld ( "Kurzname", "kurzname", "", "50", 'kurzname', '' );
			$f->text_feld ( "Lage", "lage", "", "10", 'lage', '' );
			$f->text_feld ( "m²", "qm", "", "10", 'qm', '' );
			$h = new haus ();
			echo "<br>";
			$h = new haus ();
			$h->dropdown_haeuser_2 ( 'Haus wählen', 'haus_id', 'haus_id' );
			$this->dropdown_einheit_typen ( 'Typ', 'typ', 'typ', 'Wohnraum' );
			$f->hidden_feld ( "einheit_raus", "einheit_speichern" );
			$f->send_button ( "submit_einheit", "Einheit erstellen" );
		}
		$f->ende_formular ();
	}
	function form_einheit_aendern($einheit_id) {
		$e = new einheit ();
		$e->get_einheit_info ( $einheit_id );
		if (isset ( $e->einheit_dat )) {
			$f = new formular ();
			$f->erstelle_formular ( "Einheit ändern", NULL );
			$f->hidden_feld ( 'dat', $e->einheit_dat );
			$f->text_feld ( "Kurzname", "kurzname", "$e->einheit_kurzname", "50", 'kurzname', '' );
			$f->text_feld ( "Lage", "lage", "$e->einheit_lage", "30", 'lage', '' );
			$e->einheit_qm_k = nummer_punkt2komma ( $e->einheit_qm );
			$f->text_feld ( "m²", "qm", "$e->einheit_qm_k", "10", 'qm', '' );
			$h = new haus ();
			echo "<br>";
			$h = new haus ();
			$h->dropdown_haeuser_2 ( 'Haus wählen', 'haus_id', 'haus_id', $e->haus_id );
			$this->dropdown_einheit_typen ( 'Typ', 'typ', 'typ', $e->typ );
			// dropdown_einheit_typen($label, $name, $id, $vorwahl)
			$f->hidden_feld ( "einheit_raus", "einheit_speichern_ae" );
			$f->send_button ( "submit_einheit", "Änderung speichern" );
			$f->ende_formular ();
		} else {
			fehlermeldung_ausgeben ( "Einheit nicht vorhanden!" );
		}
	}
	function form_einheit_session() {
		$f = new formular ();
		$f->erstelle_formular ( "Einheit ändern", NULL );
		$f->send_button ( "submit_einheit", "Änderung speichern" );
		$f->ende_formular ();
	}
	function einheit_speichern($kurzname, $lage, $qm, $haus_id, $typ) {
		$last_id = last_id2 ( 'EINHEIT', 'EINHEIT_ID' ) + 1;
		$qm = nummer_komma2punkt ( $qm );
		$db_abfrage = "INSERT INTO EINHEIT VALUES (NULL, '$last_id', '$qm', '$lage', '$haus_id', '1', '$kurzname', '$typ')";
		$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		/* Protokollieren */
		$last_dat = mysql_insert_id ();
		protokollieren ( 'EINHEIT', $last_dat, '0' );
		return $last_id;
	}
	function einheit_update($einheit_dat, $einheit_id, $kurzname, $lage, $qm, $haus_id, $typ) {
		$this->einheit_deaktivieren ( $einheit_dat );
		$last_id = $einheit_id;
		$qm = nummer_komma2punkt ( $qm );
		$db_abfrage = "INSERT INTO EINHEIT VALUES (NULL, '$last_id', '$qm', '$lage', '$haus_id', '1', '$kurzname', '$typ')";
		$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		/* Protokollieren */
		$last_dat = mysql_insert_id ();
		protokollieren ( 'EINHEIT', $last_dat, $einheit_dat );
		return $last_dat;
	}
	function einheit_deaktivieren($einheit_dat) {
		$db_abfrage = "UPDATE EINHEIT SET EINHEIT_AKTUELL='0' WHERE EINHEIT_DAT='$einheit_dat'";
		$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		/* Protokollieren */
		protokollieren ( 'EINHEIT', $einheit_dat, $einheit_dat );
		return $einheit_dat;
	}
} // end class einheit
class mietvertrag extends einheit {
	var $einheit_id;
	var $anzahl_mietvertraege_gesamt;
	var $mietvertrag_id;
	var $mietvertrag_von;
	var $mietvertrag_bis;
	var $anzahl_personen_im_vertrag;
	var $einheit_id_of_mietvertrag;
	function get_anzahl_mietvertrag_id_zu_einheit($einheit_id) {
		$result = mysql_query ( "SELECT * FROM MIETVERTRAG WHERE EINHEIT_ID='$einheit_id' && MIETVERTRAG_AKTUELL='1' ORDER BY EINHEIT_ID ASC" );
		$anzahl = mysql_numrows ( $result );
		$this->anzahl_mietvertraege_gesamt = $anzahl;
		// auch abgelaufene MV
	}
	function get_mietvertrag_infos_aktuell($einheit_id) {
		$datum_heute = date ( "Y-m-d" );
		$result = mysql_query ( "SELECT * FROM MIETVERTRAG WHERE MIETVERTRAG_AKTUELL='1' && EINHEIT_ID='$einheit_id' && ((MIETVERTRAG_BIS>='$datum_heute') OR (MIETVERTRAG_BIS = '0000-00-00')) ORDER BY MIETVERTRAG_DAT DESC LIMIT 0,1" );
		
		$row = mysql_fetch_assoc ( $result );
		
		$this->mietvertrag_von = $row ['MIETVERTRAG_VON'];
		$this->mietvertrag_bis = $row ['MIETVERTRAG_BIS'];
		$this->mietvertrag_id = $row ['MIETVERTRAG_ID'];
	}
	function get_mv_infos($mv_id) {
		$mvs = new mietvertraege ();
		$mvs->get_mietvertrag_infos_aktuell ( $mv_id );
		return $mvs;
	}
	function get_anzahl_personen_zu_mietvertrag($mietvertrag_id) {
		$result = mysql_query ( "SELECT PERSON_MIETVERTRAG_ID FROM PERSON_MIETVERTRAG WHERE PERSON_MIETVERTRAG_MIETVERTRAG_ID='$mietvertrag_id' && PERSON_MIETVERTRAG_AKTUELL='1' ORDER BY PERSON_MIETVERTRAG_ID ASC" );
		$anzahl = mysql_numrows ( $result );
		$this->anzahl_personen_im_vertrag = $anzahl;
		// Alle Personen im MV
	}
	function get_personen_ids_mietvertrag($mietvertrag_id) {
		$result = mysql_query ( "SELECT PERSON_MIETVERTRAG_PERSON_ID FROM PERSON_MIETVERTRAG WHERE PERSON_MIETVERTRAG_MIETVERTRAG_ID='$mietvertrag_id' && PERSON_MIETVERTRAG_AKTUELL='1' ORDER BY PERSON_MIETVERTRAG_ID ASC" );
		$my_array = array ();
		while ( $row = mysql_fetch_assoc ( $result ) )
			$my_array [] = $row;
		if (is_array ( $my_array )) {
			return $my_array;
		}
	}
	function get_einheit_id_von_mietvertrag($mietvertrag_id) {
		$result = mysql_query ( "SELECT EINHEIT_ID FROM MIETVERTRAG WHERE MIETVERTRAG_AKTUELL='1' && MIETVERTRAG_ID='$mietvertrag_id' ORDER BY MIETVERTRAG_DAT DESC LIMIT 0,1" );
		$row = mysql_fetch_assoc ( $result );
		$this->get_einheit_info ( $row ['EINHEIT_ID'] );
		return $row ['EINHEIT_ID'];
	}
	function get_mietvertrag_einzugs_datum($mietvertrag_id) {
		$result = mysql_query ( "SELECT MIETVERTRAG_VON FROM MIETVERTRAG WHERE MIETVERTRAG_AKTUELL='1' && MIETVERTRAG_ID='$mietvertrag_id' ORDER BY MIETVERTRAG_DAT DESC LIMIT 0,1" );
		$row = mysql_fetch_assoc ( $result );
		return $row ['MIETVERTRAG_VON'];
	}
	function get_aktuelle_miete($mietvertrag_id) {
		$result = mysql_query ( "SELECT KOSTENKATEGORIE, BETRAG FROM MIETENTWICKLUNG WHERE KOSTENTRAEGER_TYP='MIETVERTRAG' && KOSTENTRAEGER_ID='$mietvertrag_id' && MIETENTWICKLUNG_AKTUELL='1' && KOSTENKATEGORIE='KALTMIETE' OR KOSTENKATEGORIE='BK' OR KOSTENKATEGORIE='HK' OR KOSTENKATEGORIE='ME'" );
		$aktuelle_mietdaten = array ();
		while ( $row = mysql_fetch_assoc ( $result ) )
			$aktuelle_mietdaten [] = $row;
		$anzahl_elemente = count ( $aktuelle_mietdaten );
		$ausgabe_str = "<div name=\"miete\" align=right>\n";
		$ausgabe_str .= "<b>Aktuelle Miete:</b><br>\n";
		$summe = 0;
		for($i = 0; $i < $anzahl_elemente; $i ++) {
			$summe = $summe + $aktuelle_mietdaten [$i] ['BETRAG'];
			$ausgabe_str .= "" . $aktuelle_mietdaten [$i] ['KOSTENKATEGORIE'] . " " . $aktuelle_mietdaten [$i] ['BETRAG'] . " €<br>\n";
		}
		$ausgabe_str .= "<hr><hr>Monatlich fällig: <b>$summe €</b></div>\n";
		// return $summe;
		return $ausgabe_str;
	}
	function liste_der_forderungen($mietvertrag_id) {
		$einzugsdatum = $this->get_mietvertrag_einzugs_datum ( $mietvertrag_id );
		$einzugsdatum = $this->date_mysql2german ( $einzugsdatum );
		$this->monate_berechnen_bis_heute ( $einzugsdatum );
		
		echo "$einzugsmonat   $monate_vergangen";
		// for($i=$einzugsmonat;$i<=$aktueller_monat;$i++){
		// echo "Monat $i <br>\n";
		// }
	}
	function get_zu_zahlen_aktuell($mietvertrag_id, $monat) {
		$result = mysql_query ( "SELECT KOSTENKATEGORIE, BETRAG, sum(BETRAG) as SUMME FROM MIETENTWICKLUNG WHERE MIETVERTRAG_ID='$mietvertrag_id' && MIETENTWICKLUNG_AKTUELL='1' &&" );
		$aktuelle_mietdaten = array ();
		while ( $row = mysql_fetch_assoc ( $result ) )
			$aktuelle_mietdaten [] = $row;
		$anzahl_elemente = count ( $aktuelle_mietdaten );
		$ausgabe_str = "<div name=\"miete\" align=right>\n";
		$ausgabe_str .= "<b>Aktuelle Miete:</b><br>\n";
		$summe = 0;
		for($i = 0; $i < $anzahl_elemente; $i ++) {
			$summe = $summe + $aktuelle_mietdaten [$i] ['BETRAG'];
			$ausgabe_str .= "" . $aktuelle_mietdaten [$i] ['KOSTENKATEGORIE'] . " " . $aktuelle_mietdaten [$i] ['BETRAG'] . " €<br>\n";
		}
		$ausgabe_str .= "<hr><hr>Fällig: <b>$summe €</b></div>\n";
		// return $summe;
		return $ausgabe_str;
	}
	function alle_zahlungen($mietvertrag_id) {
		$result = mysql_query ( "SELECT DATUM, BETRAG, KOSTENKATEGORIE FROM MIETBUCHUNGEN WHERE MIETVERTRAG_ID='$mietvertrag_id' && MIETBUCHUNGEN_AKTUELL='1'" );
		$alle_zahlungen = array ();
		while ( $row = mysql_fetch_assoc ( $result ) )
			$alle_zahlungen [] = $row;
		$anzahl_zahlungen = count ( $alle_zahlungen );
		$ausgabe = "<div align=right>\n";
		for($i = 0; $i < $anzahl_zahlungen; $i ++) {
			$datum = $this->date_mysql2german ( $alle_zahlungen [$i] ['DATUM'] );
			$ausgabe .= "" . $datum . " " . $alle_zahlungen [$i] ['KOSTENKATEGORIE'] . " " . $alle_zahlungen [$i] [BETRAG] . " €<hr>\n";
		}
		$ausgabe .= "</div>\n";
		return $ausgabe;
	}
	function summe_aller_zahlungen($mietvertrag_id) {
		$result = mysql_query ( "SELECT BETRAG FROM MIETBUCHUNGEN WHERE MIETVERTRAG_ID='$mietvertrag_id' && MIETBUCHUNGEN_AKTUELL='1'" );
		$alle_zahlungen = array ();
		while ( $row = mysql_fetch_assoc ( $result ) )
			$alle_zahlungen [] = $row;
		$anzahl_zahlungen = count ( $alle_zahlungen );
		$ausgabe = "<div align=right>\n";
		$summe = 0;
		for($i = 0; $i < $anzahl_zahlungen; $i ++) {
			$summe = $summe + $alle_zahlungen [$i] [BETRAG];
		}
		
		return $summe;
	}
	function mitbuchungen_saldo($mietvertrag_id) {
		$result = mysql_query ( "SELECT DATUM, BETRAG, KOSTENKATEGORIE FROM MIETBUCHUNGEN WHERE MIETVERTRAG_ID='$mietvertrag_id' && MIETBUCHUNGEN_AKTUELL='1'" );
		$alle_zahlungen = array ();
		while ( $row = mysql_fetch_assoc ( $result ) )
			$alle_zahlungen [] = $row;
		$anzahl_zahlungen = count ( $alle_zahlungen );
		$ausgabe = "<div align=right>\n";
		
		for($i = 0; $i < $anzahl_zahlungen; $i ++) {
			$datum = $this->date_mysql2german ( $alle_zahlungen [$i] ['DATUM'] );
			$ausgabe .= "" . $datum . " " . $alle_zahlungen [$i] ['KOSTENKATEGORIE'] . " " . $alle_zahlungen [$i] [BETRAG] . " €<hr>\n";
		}
		$ausgabe .= "</div>\n";
		return $ausgabe;
	}
}
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
class details {
	function get_details($tabelle, $id) {
		$result = mysql_query ( "SELECT DETAIL_NAME, DETAIL_INHALT FROM DETAIL WHERE DETAIL_ZUORDNUNG_TABELLE='$tabelle' && DETAIL_ZUORDNUNG_ID='$id' && DETAIL_AKTUELL='1' ORDER BY DETAIL_NAME ASC" );
		$my_array = array ();
		while ( $row = mysql_fetch_assoc ( $result ) )
			$my_array [] = $row;
		return $my_array;
	}
}
class partner extends rechnung {
	var $rechnungs_aussteller_name;
	var $rechnungs_aussteller_strasse;
	var $rechnungs_aussteller_hausnr;
	var $rechnungs_aussteller_plz;
	var $rechnungs_aussteller_ort;
	var $rechnungs_empfaenger_name;
	var $rechnungs_empfaenger_strasse;
	var $rechnungs_empfaenger_hausnr;
	var $rechnungs_empfaenger_plz;
	var $rechnungs_empfaenger_ort;
	function get_partner_konto_id($partner_id) {
		return $partner_id;
	}
	
	/*
	 * function get_partner_name($partner_id){
	 * $result = mysql_query ("SELECT PARTNER_NAME FROM PARTNER_LIEFERANT WHERE PARTNER_ID='$partner_id' && AKTUELL = '1'");
	 * $row = mysql_fetch_assoc($result);
	 * return $row['PARTNER_NAME'];
	 * }
	 */
	function get_aussteller_info($partner_id) {
		$result = mysql_query ( "SELECT PARTNER_NAME, STRASSE, NUMMER, PLZ, ORT FROM PARTNER_LIEFERANT WHERE PARTNER_ID='$partner_id' && AKTUELL = '1'" );
		$row = mysql_fetch_assoc ( $result );
		// return
		$this->rechnungs_aussteller_name = $row ['PARTNER_NAME'];
		$this->rechnungs_aussteller_strasse = $row ['STRASSE'];
		$this->rechnungs_aussteller_hausnr = $row ['NUMMER'];
		$this->rechnungs_aussteller_plz = $row ['PLZ'];
		$this->rechnungs_aussteller_ort = $row ['ORT'];
	}
	function get_empfaenger_info($partner_id) {
		$result = mysql_query ( "SELECT PARTNER_NAME, STRASSE, NUMMER, PLZ, ORT FROM PARTNER_LIEFERANT WHERE PARTNER_ID='$partner_id' && AKTUELL = '1'" );
		$row = mysql_fetch_assoc ( $result );
		// return
		$this->rechnungs_empfaenger_name = $row ['PARTNER_NAME'];
		$this->rechnungs_empfaenger_strasse = $row ['STRASSE'];
		$this->rechnungs_empfaenger_hausnr = $row ['NUMMER'];
		$this->rechnungs_empfaenger_plz = $row ['PLZ'];
		$this->rechnungs_empfaenger_ort = $row ['ORT'];
	}
	
	/* Partner erfassen Formular */
	function form_partner_erfassen() {
		$form = new mietkonto ();
		$form->erstelle_formular ( "Partner erfassen", NULL );
		// $form->text_feld("Partnername:", "partnername", "", "10");
		$form->text_bereich ( "Partnername", "partnername", $_SESSION ['partnername'], "20", "3" );
		$form->text_feld ( "Strasse:", "strasse", $_SESSION ['strasse'], "50" );
		$form->text_feld ( "Nummer:", "hausnummer", $_SESSION ['hausnummer'], "10" );
		$form->text_feld ( "Postleitzahl:", "plz", $_SESSION ['plz'], "10" );
		$form->text_feld ( "Ort:", "ort", $_SESSION ['ort'], "10" );
		$form->text_feld ( "Land:", "land", $_SESSION ['land'], "10" );
		$form->text_feld ( "Kreditinstitut:", "kreditinstitut", "", "10" );
		$form->text_feld ( "Kontonummer:", "kontonummer", "", "10" );
		$form->text_feld ( "Bankleitzahl:", "blz", "", "10" );
		$form->send_button ( "submit_partner", "Partner speichern" );
		$form->hidden_feld ( "option", "partner_gesendet" );
		$form->ende_formular ();
	}
	
	/* Partner in Datenbank speichern */
	function partner_speichern($clean_arr) {
		foreach ( $clean_arr as $key => $value ) {
			$partnername = $clean_arr [partnername];
			$str = $clean_arr [strasse];
			$hausnr = $clean_arr [hausnummer];
			$plz = $clean_arr [plz];
			$ort = $clean_arr [ort];
			$land = $clean_arr [land];
			$kreditinstitut = $clean_arr [kreditinstitut];
			$kontonummer = $clean_arr ['KONTONUMMER'];
			$blz = $clean_arr ['BLZ'];
			
			print_r ( $clean_arr );
			if (empty ( $partnername ) or empty ( $str ) or empty ( $hausnr ) or empty ( $plz ) or empty ( $ort ) or empty ( $land )) {
				fehlermeldung_ausgeben ( "Dateneingabe unvollständig!!!<br>Sie werden weitergeleitet." );
				$_SESSION [partnername] = $partnername;
				$_SESSION [strasse] = $str;
				$_SESSION [hausnummer] = $hausnr;
				$_SESSION [plz] = $plz;
				$_SESSION [ort] = $ort;
				$_SESSION [land] = $land;
				$_SESSION [kreditinstitut] = $kreditinstitut;
				$_SESSION ['KONTONUMMER'] = $kontonummer;
				$_SESSION ['BLZ'] = $blz;
				
				$fehler = true;
				weiterleiten_in_sec ( "?daten=rechnungen&option=partner_erfassen", 3 );
				die ();
			}
		} // Ende foreach
		
		/* Prüfen ob Partner/Liefernat vorhanden */
		$result_3 = mysql_query ( "SELECT * FROM PARTNER_LIEFERANT WHERE PARTNER_NAME = '$clean_arr[partnername]' && STRASSE='$clean_arr[strasse]' && NUMMER='$clean_arr[hausnummer]' && PLZ='$clean_arr[plz]' && AKTUELL = '1' ORDER BY PARTNER_NAME" );
		$numrows_3 = mysql_numrows ( $result_3 );
		
		/* Wenn kein Fehler durch eingabe oder partner in db nicht vorhanden wird neuer datensatz gespeichert */
		
		if (! $fehler && $numrows_3 < 1) {
			/* Partnerdaten ohne Kontoverbindung */
			$partner_id = $this->letzte_partner_id ();
			$partner_id = $partner_id + 1;
			$db_abfrage = "INSERT INTO PARTNER_LIEFERANT VALUES (NULL, $partner_id, '$clean_arr[partnername]','$clean_arr[strasse]', '$clean_arr[hausnummer]','$clean_arr[plz]','$clean_arr[ort]','$clean_arr[land]','1')";
			$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
			/* Protokollieren */
			$last_dat = mysql_insert_id ();
			protokollieren ( 'PARTNER_LIEFERANT', $last_dat, '0' );
			
			if (! empty ( $kreditinstitut ) or ! empty ( $kontonummer ) or ! empty ( $blz )) {
				/* Kontodaten speichern */
				$konto_id = $this->letzte_geldkonto_id ();
				$konto_id = $konto_id + 1;
				$db_abfrage = "INSERT INTO GELD_KONTEN VALUES (NULL, '$konto_id','$clean_arr[partnername] - Konto','$clean_arr[partnername]', '$clean_arr[KONTONUMMER]','$clean_arr[BLZ]', '$clean_arr[kreditinstitut]','1')";
				$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
				/* Protokollieren */
				$last_dat = mysql_insert_id ();
				protokollieren ( 'GELD_KONTEN', $last_dat, '0' );
				/* Geldkonto dem Partner zuweisen */
				$letzte_zuweisung_id = $this->letzte_zuweisung_geldkonto_id ();
				$letzte_zuweisung_id = $letzte_zuweisung_id + 1;
				$db_abfrage = "INSERT INTO GELD_KONTEN_ZUWEISUNG VALUES (NULL, '$letzte_zuweisung_id','$konto_id', 'Partner','$partner_id', '1')";
				$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
			}
			if (isset ( $resultat )) {
				hinweis_ausgeben ( "Partner $clean_arr[partnername] wurde gespeichert." );
				weiterleiten_in_sec ( "?daten=rechnungen&option=partner_erfassen", 2 );
			}
		} // ende fehler
		if ($numrows_3 > 0) {
			fehlermeldung_ausgeben ( "Partner $clean_arr[partnername] exisitiert bereits." );
			weiterleiten_in_sec ( "?daten=rechnungen&option=partner_erfassen", 2 );
		}
		unset ( $_SESSION [partnername] );
		unset ( $_SESSION [strasse] );
		unset ( $_SESSION [hausnummer] );
		unset ( $_SESSION [plz] );
		unset ( $_SESSION [ort] );
		unset ( $_SESSION [land] );
		unset ( $_SESSION [kreditinstitut] );
		unset ( $_SESSION [KONTONUMMER] );
		unset ( $_SESSION [BLZ] );
	} // Ende funktion
	
	/* Letzte Partner ID */
	function letzte_partner_id() {
		$result = mysql_query ( "SELECT PARTNER_ID FROM PARTNER_LIEFERANT  ORDER BY PARTNER_ID DESC LIMIT 0,1" );
		$row = mysql_fetch_assoc ( $result );
		return $row ['PARTNER_ID'];
	}
	
	/* Letzte Partnergeldkonto ID */
	function letzte_geldkonto_id() {
		$result = mysql_query ( "SELECT KONTO_ID FROM GELD_KONTEN ORDER BY KONTO_ID DESC LIMIT 0,1" );
		$row = mysql_fetch_assoc ( $result );
		return $row ['KONTO_ID'];
	}
	
	/* Letzte Zuweisunggeldkonto ID */
	function letzte_zuweisung_geldkonto_id() {
		$result = mysql_query ( "SELECT ZUWEISUNG_ID FROM GELD_KONTEN_ZUWEISUNG ORDER BY ZUWEISUNG_ID DESC LIMIT 0,1" );
		$row = mysql_fetch_assoc ( $result );
		return $row ['ZUWEISUNG_ID'];
	}
	function partner_rechts_anzeigen() {
		$result = mysql_query ( "SELECT * FROM PARTNER_LIEFERANT WHERE AKTUELL = '1' ORDER BY PARTNER_NAME" );
		$numrows = mysql_numrows ( $result );
		if ($numrows > 0) {
			$form = new mietkonto ();
			$form->erstelle_formular ( "Partner", NULL );
			echo "<div class=\"tabelle\">\n";
			while ( $row = mysql_fetch_assoc ( $result ) )
				$my_array [] = $row;
			echo "<table>\n";
			// echo "<tr class=\"feldernamen\"><td>Partner</td></tr>\n";
			echo "<tr><th>Partner</th></tr>";
			for($i = 0; $i < count ( $my_array ); $i ++) {
				echo "<tr><td>" . $my_array [$i] ['PARTNER_NAME'] . "</td></tr>\n";
			}
			echo "</table></div>\n";
			$form->ende_formular ();
		} else {
			echo "Keine Partner";
		}
	}
	function partner_in_array() {
		$result = mysql_query ( "SELECT * FROM PARTNER_LIEFERANT WHERE AKTUELL = '1' ORDER BY PARTNER_NAME ASC" );
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			while ( $row = mysql_fetch_assoc ( $result ) )
				$my_array [] = $row;
			return $my_array;
		} else {
			return false;
		}
	}
	function partner_grunddaten($partner_id) {
		$result = mysql_query ( "SELECT * FROM PARTNER_LIEFERANT WHERE PARTNER_ID='$partner_id' && AKTUELL = '1' ORDER BY PARTNER_DAT DESC LIMIT 0,1" );
		$numrows = mysql_numrows ( $result );
		if ($numrows > 0) {
			$row = mysql_fetch_assoc ( $result );
			$this->partner_id = $partner_id;
			$this->partner_name = $row ['PARTNER_NAME'];
			$this->partner_str = $row ['STRASSE'];
			$this->partner_nr = $row ['NUMMER'];
			$this->partner_plz = $row ['PLZ'];
			$this->partner_ort = $row ['ORT'];
			$this->partner_land = $row ['LAND'];
		} else {
			return false;
		}
	}
	function getpartner_id_name($partner_name) {
		$result = mysql_query ( "SELECT PARTNER_ID FROM PARTNER_LIEFERANT WHERE REPLACE(PARTNER_NAME, '<br>', '') ='$partner_name' && AKTUELL = '1' ORDER BY PARTNER_DAT DESC LIMIT 0,1" );
		$numrows = mysql_numrows ( $result );
		if ($numrows > 0) {
			$row = mysql_fetch_assoc ( $result );
			$this->partner_id = $row ['PARTNER_ID'];
		} else {
			return false;
		}
	}
	function partnerdaten_anzeigen($partner_id) {
		$this->partner_grunddaten ( $partner_id );
		echo "<b>Partnername:</b><br>$this->partner_name<br>";
		echo "<br><b>Anschrift:</b><br>$this->partner_str $this->partner_nr<br>";
		echo "$this->partner_plz $this->partner_ort<br>";
		echo "$this->partner_land";
		
		$g = new geldkonto_info ();
		$anzahl_konten = $g->geldkonten_anzahl ( 'Partner', $partner_id );
		echo "<hr><b>Anzahl Geldkonten: $anzahl_konten</b><hr>";
		$this->geldkonten_anzeigen ( $partner_id );
	}
	function geldkonten_anzeigen($partner_id) {
		$g = new geldkonto_info ();
		$anzahl_konten = $g->geldkonten_anzahl ( 'Partner', $partner_id );
		$geldkonten_arr = $g->geldkonten_arr ( 'Partner', $partner_id );
		
		for($a = 0; $a < $anzahl_konten; $a ++) {
			$beguenstigter = $geldkonten_arr [$a] ['BEGUENSTIGTER'];
			$kontonr = $geldkonten_arr [$a] ['KONTONUMMER'];
			$blz = $geldkonten_arr [$a] ['BLZ'];
			$bank = $geldkonten_arr [$a] ['INSTITUT'];
			$i = $a + 1;
			echo "<b>Konto $i:</b><br><br>";
			echo "Begünstigter: $beguenstigter<br>";
			echo "Bankinstitut: $bank<br>";
			echo "Kontonummer: $kontonr<br>";
			echo "BLZ: $blz<hr>";
		}
	}
	function partner_dropdown($label, $name, $id, $vorwahl_id = null) {
		$partner_arr = $this->partner_in_array ();
		echo "<label for=\"$id\">$label</label><select name=\"$name\" size=\"1\" id=\"$id\">";
		for($a = 0; $a < count ( $partner_arr ); $a ++) {
			$partner_id = $partner_arr [$a] ['PARTNER_ID'];
			$partner_name = $partner_arr [$a] ['PARTNER_NAME'];
			if ($vorwahl_id == $partner_id) {
				echo "<option value=\"$partner_id\" selected>$partner_name</OPTION>\n";
			} else {
				echo "<option value=\"$partner_id\">$partner_name</OPTION>\n";
			}
		}
		echo "</select><br>\n";
	}
	function partner_liste() {
		$partner_arr = $this->partner_in_array ();
		echo "<table class=\"sortable\">";
		// echo "<tr class=\"feldernamen\"><td width=\"200px\">Name</td><td>Anschrift</td><td>Details</td></tr>";
		echo "<tr><th>Partner</th><th>Anschrift</th><th>Details</th></tr>";
		$zaehler = 0;
		for($a = 0; $a < count ( $partner_arr ); $a ++) {
			$zaehler ++;
			$partner_id = $partner_arr [$a] [PARTNER_ID];
			$partner_name = $partner_arr [$a] [PARTNER_NAME];
			$partner_link_detail = "<a href=\"?daten=partner&option=partner_im_detail&partner_id=$partner_id\">$partner_name</a>";
			$link_detail_hinzu = "<a href=\"?daten=details&option=details_hinzu&detail_tabelle=PARTNER_LIEFERANT&detail_id=$partner_id\">Details</a>";
			$partner_strasse = $partner_arr [$a] [STRASSE];
			$partner_nr = $partner_arr [$a] [NUMMER];
			$partner_plz = $partner_arr [$a] [PLZ];
			$partner_ort = $partner_arr [$a] [ORT];
			$anschrift = "$partner_strasse $partner_nr, $partner_plz $partner_ort";
			if ($zaehler == 1) {
				echo "<tr valign=\"top\" class=\"zeile1\"><td>$partner_link_detail</td><td>$anschrift</td><td>$link_detail_hinzu</td></tr>";
			}
			if ($zaehler == 2) {
				echo "<tr valign=\"top\" class=\"zeile2\"><td>$partner_link_detail</td><td>$anschrift</td><td>$link_detail_hinzu</td></tr>";
				$zaehler = 0;
			}
		}
		echo "</table><br>\n";
	}
	function partner_auswahl($link) {
		if (isset ( $_REQUEST ['partner_id'] ) && ! empty ( $_REQUEST ['partner_id'] )) {
			$_SESSION ['partner_id'] = $_REQUEST ['partner_id'];
		}
		
		$form = new formular ();
		if (! isset ( $_SESSION ['partner_id'] )) {
			$form->erstelle_formular ( "Partner wählen", NULL );
		} else {
			$form->erstelle_formular ( "Partner ausgewählt - Aktuell: Partner $_SESSION[partner_id]", NULL );
		}
		// $result = mysql_query ("SELECT PARTNER_ID, PARTNER_NAME FROM PARTNER_LIEFERANT WHERE AKTUELL = '1' ORDER BY PARTNER_NAME ASC");
		/* Sortiert nach Anzahl der Belege */
		$result = mysql_query ( "SELECT PARTNER_ID, PARTNER_NAME, COUNT(BELEG_NR) AS BELEGE FROM PARTNER_LIEFERANT JOIN RECHNUNGEN ON(PARTNER_LIEFERANT.PARTNER_ID=RECHNUNGEN.AUSSTELLER_ID OR PARTNER_LIEFERANT.PARTNER_ID=RECHNUNGEN.EMPFAENGER_ID) WHERE PARTNER_LIEFERANT.AKTUELL = '1'  && RECHNUNGEN.AKTUELL = '1' GROUP BY PARTNER_ID ORDER BY COUNT(BELEG_NR)  DESC, PARTNER_NAME ASC" );
		$numrows = mysql_numrows ( $result );
		echo "<p class=\"objekt_auswahl\">";
		if ($numrows) {
			while ( $row = mysql_fetch_assoc ( $result ) ) {
				$partner_link = "<a class=\"objekt_auswahl_buchung\" href=\"$link&partner_id=$row[PARTNER_ID]\">$row[PARTNER_NAME]</a>";
				echo "$partner_link<hr>";
			}
			echo "</p>";
		} else {
			echo "Kein Partner vorhanden";
			return FALSE;
		}
		$form->ende_formular ();
	}
	function anzahl_rechnungen($p_id) {
		$result = mysql_query ( "SELECT COUNT(BELEG_NR) FROM RECHNUNGEN WHERE AKTUELL = '1' && (AUSSTELLER_ID='$p_id' OR EMPFAENGER_ID='$p_id')  ORDER BY PARTNER_NAME ASC" );
		$numrows = mysql_numrows ( $result );
	}
} // Ende Klasse Partner
class rechnung {
	/* Diese Variablen werden von rechnung_grunddaten_holen($rechnung_id) gesetzt */
	var $belegnr;
	var $rechnungsnummer;
	var $aussteller_ausgangs_rnr;
	var $empfaenger_eingangs_rnr;
	var $rechnungstyp;
	var $rechnungsdatum;
	var $eingangsdatum;
	var $faellig_am;
	var $bezahlt_am;
	var $rechnungs_netto;
	var $rechnungs_brutto;
	var $rechnungs_skontobetrag;
	var $rechnungs_aussteller_typ;
	var $rechnungs_aussteller_id;
	var $rechnungs_aussteller_name;
	var $rechnungs_empfaenger_typ;
	var $rechnungs_empfaenger_id;
	var $rechnungs_empfaenger_name;
	var $status_erfasst;
	var $status_vollstaendig;
	var $status_zugewiesen;
	var $status_zahlung_freigegeben;
	var $status_bezahlt;
	var $kurzbeschreibung;
	var $skonto;
	var $empfangs_geld_konto;
	var $rechnungs_aussteller_strasse;
	var $rechnungs_aussteller_hausnr;
	var $rechnungs_aussteller_plz;
	var $rechnungs_aussteller_ort;
	var $rechnungs_empfaenger_strasse;
	var $rechnungs_empfaenger_hausnr;
	var $rechnungs_empfaenger_plz;
	var $rechnungs_empfaenger_ort;
	
	/* Ende rechnung_grunddaten_holen */
	
	/* Infos über Positionen */
	var $anzahl_positionen;
	function get_kontierung_obj($dat) {
		$result = mysql_query ( "SELECT * FROM KONTIERUNG_POSITIONEN WHERE `KONTIERUNG_DAT` ='$dat'" );
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			$row = mysql_fetch_assoc ( $result );
			$this->dat = $dat;
			$this->beleg_nr = $row ['BELEG_NR'];
			$this->pos = $row ['POSITION'];
			$this->menge = $row ['MENGE'];
			$this->einzel_preis = $row ['EINZEL_PREIS'];
			$this->g_summe = $row ['GESAMT_SUMME'];
			$this->mwst_satz = $row ['MWST_SATZ'];
			$this->skonto = $row ['SKONTO'];
			$this->rabatt_satz = $row ['RABATT_SATZ'];
			$this->kostenkonto = $row ['KONTENRAHMEN_KONTO'];
			$this->kos_typ = $row ['KOSTENTRAEGER_TYP'];
			$this->kos_id = $row ['KOSTENTRAEGER_ID'];
			$this->rechnung_grunddaten_holen ( $this->beleg_nr );
		}
	}
	function form_rechnung_erfassen() {
		$form = new mietkonto ();
		$formular = new formular ();
		$partner = new partner ();
		$form->erstelle_formular ( "Bargeldlose Rechnung erfassen", NULL );
		// echo "Rechnung erfassen<br>\n";
		$form->hidden_feld ( "aussteller_typ", "Partner" );
		$partner_arr = $partner->partner_dropdown ( 'Rechnung ausgestellt von', 'aussteller_id', 'aussteller' );
		$form->hidden_feld ( "empfaenger_typ", "Partner" );
		// $partner_arr=$partner->partner_dropdown('Rechnung ausgestellt an','empfaenger_id', 'empfaenger');
		$pp = new partners ();
		$pp->partner_dropdown ( 'Rechnung ausgestellt an', 'empfaenger_id', 'empfaenger', $_SESSION ['partner_id'] );
		
		$datum_heute = date ( "d.m.Y" );
		$datum_feld = 'document.getElementById("eingangsdatum").value';
		$js_datum = "onchange='check_datum($datum_feld)'";
		$formular->text_feld ( 'Eingangsdatum:', 'eingangsdatum', '', '10', 'eingangsdatum', $js_datum );
		// $form->text_feld("Eingangsdatum:", "eingangsdatum", '', "10");
		$form->text_feld ( "Rechnungsnummer:", "rechnungsnummer", "", "10" );
		$form->hidden_feld ( "rechnungstyp", "Rechnung" );
		$datum_feld1 = 'document.getElementById("rechnungsdatum").value';
		$js_datum = "onchange='check_datum($datum_feld1)'";
		$formular->text_feld ( 'Rechnungsdatum:', 'rechnungsdatum', '', '10', 'rechnungsdatum', $js_datum );
		// $form->text_feld("Rechnungsdatum:", "rechnungsdatum", '', "10");
		// $form->text_feld("Nettobetrag:", "nettobetrag", "", "10");
		$form->hidden_feld ( "nettobetrag", "0,00" );
		// $formular->text_feld("Bruttobetrag:", "bruttobetrag", '', '10', 'bruttobetrag', '');
		$form->hidden_feld ( "bruttobetrag", "0,00" );
		$form->hidden_feld ( "skontobetrag", "0,00" );
		// $form->text_feld("Betrag nach Abzug von Skonto:", "skontobetrag", "", "10");
		// $formular->text_feld("Skonto in %:", "skonto", '', '10', 'skonto', 'onchange="skonto_berechnen()"');
		$form->text_feld ( "Fällig am", "faellig_am", '', "10" );
		$form->text_bereich ( "Kurzbeschreibung", "kurzbeschreibung", "", "50", "10" );
		$form->send_button ( "submit_rechnung1", "Rechnung speichern" );
		$form->hidden_feld ( "option", "rechnung_erfassen1" );
		$form->ende_formular ();
	}
	function form_gutschrift_erfassen() {
		$form = new mietkonto ();
		$partner = new partner ();
		$formular = new formular ();
		$form->erstelle_formular ( "Bargeldlose Rechnung erfassen", NULL );
		// echo "Rechnung erfassen<br>\n";
		$partner_arr = $partner->partner_dropdown ( 'Rechnung ausgestellt von', 'Aussteller', 'aussteller' );
		
		$partner_arr = $partner->partner_dropdown ( 'Rechnung ausgestellt an', 'Empfaenger', 'empfaenger' );
		$datum_heute = date ( "d.m.Y" );
		$form->text_feld ( "Eingangsdatum:", "eingangsdatum", $datum_heute, "10" );
		$form->text_feld ( "Rechnungsnummer:", "rechnungsnummer", "", "10" );
		$form->hidden_feld ( "rechnungstyp", "Gutschrift" );
		$form->text_feld ( "Rechnungsdatum:", "rechnungsdatum", $datum_heute, "10" );
		$form->text_feld ( "Nettobetrag:", "nettobetrag", "0,00", "10" );
		// $form->text_feld("Bruttobetrag:", "bruttobetrag", "0,00", "10");
		// $form->text_feld("Skontobetrag:", "skontobetrag", "0,00", "10");
		// $form->text_feld("Skonto in %:", "skonto", "3", "3");
		$formular->text_feld ( "Bruttobetrag:", "bruttobetrag", '', '10', 'bruttobetrag', 'onchange="skonto_berechnen()"' );
		$form->text_feld ( "Betrag nach Abzug von Skonto:", "skontobetrag", "", "10" );
		$formular->text_feld ( "Skonto in %:", "skonto", '3', '10', 'skonto', 'onchange="skonto_berechnen()"' );
		
		$form->text_feld ( "Fällig am", "faellig_am", $datum_heute, "10" );
		$form->text_bereich ( "Kurzbeschreibung", "kurzbeschreibung", "", "50", "10" );
		$form->send_button ( "submit_rechnung1", "Rechnung speichern" );
		$form->hidden_feld ( "option", "rechnung_erfassen1" );
		$form->ende_formular ();
	}
	function form_rechnung_erfassen_an_kasse() {
		$form = new mietkonto ();
		$formular = new formular ();
		$partner = new partner ();
		$kasse_info = new kasse ();
		$form->erstelle_formular ( "Kasse -> Ausgaben erfassen", NULL );
		echo "<br>\n";
		$form->hidden_feld ( "aussteller_typ", "Partner" );
		$partner_arr = $partner->partner_dropdown ( 'Rechnung ausgestellt von', 'aussteller_id', 'aussteller' );
		$form->hidden_feld ( "empfaenger_typ", "Kasse" );
		$kasse_info->dropdown_kassen ( 'Kasse als Empfänger', 'empfaenger_id', 'empfaenger' );
		// $partner_arr=$partner->partner_dropdown('Empfaenger', 'empfaenger');
		$datum_heute = date ( "d.m.Y" );
		$form->text_feld ( "Eingangsdatum:", "eingangsdatum", '', "10" );
		$form->text_feld ( "Rechnungsnummer:", "rechnungsnummer", "", "10" );
		$form->hidden_feld ( "rechnungstyp", "Rechnung" );
		$form->text_feld ( "Rechnungsdatum:", "rechnungsdatum", '', "10" );
		$form->text_feld ( "Nettobetrag:", "nettobetrag", "", "10" );
		$formular->text_feld ( "Bruttobetrag:", "bruttobetrag", '', '10', 'bruttobetrag', 'onchange="skonto_berechnen()"' );
		$form->text_feld ( "Betrag nach Abzug von Skonto:", "skontobetrag", "", "10" );
		$formular->text_feld ( "Skonto in %:", "skonto", '', '10', 'skonto', 'onchange="skonto_berechnen()"' );
		$form->text_feld ( "Fällig am", "faellig_am", '', "10" );
		$form->text_bereich ( "Kurzbeschreibung", "kurzbeschreibung", "", "50", "10" );
		$form->send_button ( "submit_rechnung1", "Rechnung speichern" );
		$form->hidden_feld ( "option", "rechnung_erfassen1" );
		$form->ende_formular ();
	}
	
	/* Alle Rechnungen werden angezeigt */
	function erfasste_rechungen_anzeigen() {
		/* Zählen aller Zeilen */
		$result = mysql_query ( "SELECT * FROM RECHNUNGEN WHERE AKTUELL = '1' ORDER BY BELEG_NR DESC" );
		/*
		 * $result = mysql_query ("SELECT RECHNUNGEN. * , COUNT( RECHNUNGEN_POSITIONEN.POSITION ) AS ANZAHL_POSITIONEN
		 * FROM RECHNUNGEN, RECHNUNGEN_POSITIONEN
		 * WHERE RECHNUNGEN.BELEG_NR = '1' && RECHNUNGEN_POSITIONEN.BELEG_NR = '1'
		 * GROUP BY RECHNUNGEN.BELEG_NR DESC");
		 */
		$numrows1 = mysql_numrows ( $result );
		/* Seitennavigation mit Limit erstellen */
		echo "<table><tr><td>Anzahl aller Rechnungen: $numrows1</td></tr><tr><td>\n";
		$navi = new blaettern ( 0, $numrows1, 100, '?daten=rechnungen&option=erfasste_rechnungen' );
		echo "</td></tr></table>\n";
		$result = mysql_query ( "SELECT * FROM RECHNUNGEN WHERE AKTUELL = '1'  ORDER BY BELEG_NR DESC " . $navi->limit . "" );
		$numrows = mysql_numrows ( $result );
		
		if ($numrows > 0) {
			while ( $row = mysql_fetch_assoc ( $result ) )
				$my_array [] = $row;
			echo "<table class=sortable>\n";
			// echo "<tr class=feldernamen><td>Erfassungsnr</td><td>TYP</td><td>Rech.Nr</td><td>Fälig</td><td>Von</td><td>An</td><td width=80>Netto</td><td width=80>Brutto</td><td width=80>Skonto</td></tr>\n";
			echo "<tr><th>Erfassungsnr</th><th>TYP</th><th>Rech.Nr</th><th>Fälig</th><th>Von</th><th>An</th><th>Netto</th><th>Brutto</th><th>Skonto</th></tr>\n";
			for($a = 0; $a < count ( $my_array ); $a ++) {
				$belegnr = $my_array [$a] ['BELEG_NR'];
				$this->rechnung_grunddaten_holen ( $belegnr );
				
				$e_datum = date_mysql2german ( $my_array [$a] ['EINGANGSDATUM'] );
				$r_datum = date_mysql2german ( $my_array [$a] ['RECHNUNGSDATUM'] );
				$faellig_am = date_mysql2german ( $my_array [$a] ['FAELLIG_AM'] );
				
				$beleg_link = "<a href=\"?daten=rechnungen&option=rechnungs_uebersicht&belegnr=$belegnr\">Ansehen</a>";
				$pdf_link = "<a href=\"?daten=rechnungen&option=anzeigen_pdf&belegnr=$belegnr\"><img src=\"css/pdf.png\"></a>";
				$pdf_link1 = "<a href=\"?daten=rechnungen&option=anzeigen_pdf&belegnr=$belegnr&no_logo\"><img src=\"css/pdf2.png\"></a>";
				
				$netto = nummer_punkt2komma ( $my_array [$a] ['NETTO'] );
				$brutto = nummer_punkt2komma ( $my_array [$a] ['BRUTTO'] );
				$skonto_betrag = nummer_punkt2komma ( $my_array [$a] ['SKONTOBETRAG'] );
				$rechnungsnummer = $my_array [$a] ['RECHNUNGSNUMMER'];
				$rechnungstyp = $my_array [$a] ['RECHNUNGSTYP'];
				echo "<tr><td>$beleg_link $pdf_link $pdf_link1</td><td>$rechnungstyp</td><td>$rechnungsnummer</td><td><b>$faellig_am</b></td><td>" . $this->rechnungs_aussteller_name . "</td><td>" . $this->rechnungs_empfaenger_name . "</td><td align=right>$netto €</td><td align=right>$brutto €</td><td align=right>$skonto_betrag €</td></tr>\n";
			}
			
			echo "</table>\n";
		}
		/* Seitennavigation mit Limit erstellen */
		/*
		 * echo "<table><tr><td>Anzahl aller Rechnungen: $numrows1</td></tr><tr><td>\n";
		 * $navi = new blaettern(0,$numrows1,30, '?daten=rechnungen&option=erfasste_rechnungen');
		 * echo "</td></tr></table>\n";
		 */
	}
	function vollstaendig_erfasste_rechungen_anzeigen() {
		/* Zählen aller Zeilen */
		// $result = mysql_query ("SELECT * FROM RECHNUNGEN WHERE AKTUELL = '1' ORDER BY BELEG_NR DESC ");
		$result = mysql_query ( "SELECT RECHNUNGEN. * , COUNT( RECHNUNGEN_POSITIONEN.POSITION ) AS ANZAHL_POSITIONEN
FROM RECHNUNGEN, RECHNUNGEN_POSITIONEN
WHERE RECHNUNGEN.BELEG_NR = RECHNUNGEN_POSITIONEN.BELEG_NR && RECHNUNGEN.AKTUELL='1' && RECHNUNGEN_POSITIONEN.AKTUELL='1' GROUP BY RECHNUNGEN.BELEG_NR ASC" );
		$numrows = mysql_numrows ( $result );
		if ($numrows > 0) {
			/* Seitennavigation mit Limit erstellen */
			echo "<table><tr><td>Anzahl vollständige Rechnungen: $numrows</td></tr><tr><td>\n";
			$navi = new blaettern ( 0, $numrows, 10, '?daten=rechnungen&option=vollstaendige_rechnungen' );
			echo "</td><tr></table>\n";
			$result = mysql_query ( "SELECT RECHNUNGEN. * , COUNT( RECHNUNGEN_POSITIONEN.POSITION ) AS ANZAHL_POSITIONEN
FROM RECHNUNGEN, RECHNUNGEN_POSITIONEN
WHERE RECHNUNGEN.BELEG_NR = RECHNUNGEN_POSITIONEN.BELEG_NR && RECHNUNGEN.AKTUELL='1' && RECHNUNGEN_POSITIONEN.AKTUELL='1' AND RECHNUNGSTYP='Rechnung' OR RECHNUNGSTYP='Gutschrift'
GROUP BY RECHNUNGEN.BELEG_NR ORDER BY BELEG_NR DESC $navi->limit" );
			$numrows = mysql_numrows ( $result );
			while ( $row = mysql_fetch_assoc ( $result ) )
				$my_array [] = $row;
			
			echo "<table class=rechnungen>\n";
			echo "<tr class=feldernamen><td>BNr</td><td>R-Datum</td><td>E-Datum</td><td>Fällig</td><td>Von</td><td>An</td><td width=80>Netto</td><td width=80>Brutto</td><td width=80>Skonto</td></tr>\n";
			
			for($a = 0; $a < count ( $my_array ); $a ++) {
				$belegnr = $my_array [$a] [BELEG_NR];
				$anzahl_positionen = $my_array [$a] [ANZAHL_POSITIONEN];
				$this->rechnung_grunddaten_holen ( $belegnr );
				
				$e_datum = date_mysql2german ( $my_array [$a] [EINGANGSDATUM] );
				$r_datum = date_mysql2german ( $my_array [$a] [RECHNUNGSDATUM] );
				$faellig_am = date_mysql2german ( $my_array [$a] [FAELLIG_AM] );
				$beleg_link = "<a href=\"?daten=rechnungen&option=rechnungs_uebersicht&belegnr=" . $my_array [$a] ['BELEG_NR'] . "\">" . $my_array [$a] ['BELEG_NR'] . "</>\n";
				$netto = nummer_punkt2komma ( $my_array [$a] [NETTO] );
				$brutto = nummer_punkt2komma ( $my_array [$a] [BRUTTO] );
				$skonto_betrag = nummer_punkt2komma ( $my_array [$a] [SKONTOBETRAG] );
				$rechnungsnummer = $my_array [$a] [RECHNUNGSNUMMER];
				$rechnungstyp = $my_array [$a] [RECHNUNGSTYP];
				echo "<tr><td>$beleg_link</td><td>$rechnungstyp</td><td>$rechnungsnummer</td><td><b>$faellig_am</b></td><td>" . $this->rechnungs_aussteller_name . "</td><td>" . $this->rechnungs_empfaenger_name . "</td><td align=right>$netto €</td><td align=right>$brutto €</td><td align=right>$skonto_betrag €</td></tr>\n";
			}
		}
		
		echo "</table>\n";
	}
	function unvollstaendig_erfasste_rechungen_anzeigen() {
		/* Zählen aller Zeilen */
		$result = mysql_query ( " SELECT BELEG_NR FROM RECHNUNGEN WHERE BELEG_NR NOT IN (  SELECT BELEG_NR FROM RECHNUNGEN_POSITIONEN WHERE AKTUELL='1') && AKTUELL='1'" );
		
		$numrows1 = mysql_numrows ( $result );
		echo "<table><tr><td>Anzahl aller unvollständig erfassten Rechnungen: $numrows1</td></tr><tr><td>\n";
		/* Seitennavigation mit Limit erstellen */
		$navi = new blaettern ( 0, $numrows1, 10, '?daten=rechnungen&option=unvollstaendige_rechnungen' );
		echo "</td></tr></table>\n";
		$result = mysql_query ( " SELECT * FROM RECHNUNGEN WHERE BELEG_NR NOT IN (  SELECT BELEG_NR FROM RECHNUNGEN_POSITIONEN WHERE AKTUELL='1') && AKTUELL='1' ORDER BY BELEG_NR DESC  $navi->limit" );
		$numrows = mysql_numrows ( $result );
		
		if ($numrows > 0) {
			while ( $row = mysql_fetch_assoc ( $result ) )
				$my_array [] = $row;
			echo "<table class=rechnungen>\n";
			echo "<tr class=feldernamen><td>BNr</td><td>R-Datum</td><td>E-Datum</td><td>Fällig</td><td>Von</td><td>An</td><td width=80>Netto</td><td width=80>Brutto</td><td width=80>Skonto</td></tr>\n";
			
			for($a = 0; $a < count ( $my_array ); $a ++) {
				$belegnr = $my_array [$a] [BELEG_NR];
				$this->rechnung_grunddaten_holen ( $belegnr );
				
				$e_datum = date_mysql2german ( $my_array [$a] [EINGANGSDATUM] );
				$r_datum = date_mysql2german ( $my_array [$a] [RECHNUNGSDATUM] );
				$faellig_am = date_mysql2german ( $my_array [$a] [FAELLIG_AM] );
				$beleg_link = "<a href=\"?daten=rechnungen&option=rechnungs_uebersicht&belegnr=" . $my_array [$a] ['BELEG_NR'] . "\">" . $my_array [$a] ['BELEG_NR'] . "</>\n";
				$netto = nummer_punkt2komma ( $my_array [$a] [NETTO] );
				$brutto = nummer_punkt2komma ( $my_array [$a] [BRUTTO] );
				$skonto_betrag = nummer_punkt2komma ( $my_array [$a] [SKONTOBETRAG] );
				echo "<tr><td>$beleg_link</td><td>$r_datum</td><td>$e_datum</td><td><b>$faellig_am</b></td><td>" . $this->rechnungs_aussteller_name . "</td><td>" . $this->rechnungs_empfaenger_name . "</td><td align=right>$netto €</td><td align=right>$brutto €</td><td align=right>$skonto_betrag €</td></tr>\n";
			}
			
			echo "</table>\n";
		}
	}
	
	/* Alle vollständig erfasste d.h. mit Positionen erfasste Rechungen die auch vollständig kontiert worden sind */
	function vollstaendig_kontierte_rechungen_anzeigen() {
		/* Zählen aller Zeilen */
		$result = mysql_query ( "SELECT RECHNUNGEN. * , COUNT( RECHNUNGEN_POSITIONEN.POSITION ) AS ANZAHL_POSITIONEN
FROM RECHNUNGEN, RECHNUNGEN_POSITIONEN
WHERE RECHNUNGEN.BELEG_NR = RECHNUNGEN_POSITIONEN.BELEG_NR && RECHNUNGEN.AKTUELL='1' && RECHNUNGEN_POSITIONEN.AKTUELL='1' GROUP BY RECHNUNGEN.BELEG_NR ASC" );
		while ( $row = mysql_fetch_assoc ( $result ) )
			$rechnungen_mit_positionen [] = $row;
		for($vv = 0; $vv < count ( $rechnungen_mit_positionen ); $vv ++) {
			$status_kontierung = $this->rechnung_auf_kontierung_pruefen ( $rechnungen_mit_positionen [$vv] [BELEG_NR] );
			// echo $rechnungen_mit_positionen[$vv][BELEG_NR].$status_kontierung."<br>\n";
			if ($status_kontierung == 'vollstaendig') {
				$kontierte_belege [] = $rechnungen_mit_positionen [$vv];
			}
		}
		
		$numrows = count ( $kontierte_belege );
		$my_array = $kontierte_belege;
		echo "<table class=rechnungen>\n";
		echo "<tr class=feldernamen><td>BNr</td><td>R-Datum</td><td>E-Datum</td><td>Fällig</td><td>Von</td><td>An</td><td width=80>Netto</td><td width=80>Brutto</td><td width=80>Skonto</td></tr>\n";
		
		for($a = 0; $a < count ( $my_array ); $a ++) {
			$belegnr = $my_array [$a] [BELEG_NR];
			$this->rechnung_grunddaten_holen ( $belegnr );
			
			$e_datum = date_mysql2german ( $my_array [$a] [EINGANGSDATUM] );
			$r_datum = date_mysql2german ( $my_array [$a] [RECHNUNGSDATUM] );
			$faellig_am = date_mysql2german ( $my_array [$a] [FAELLIG_AM] );
			$beleg_link = "<a href=\"?daten=rechnungen&option=rechnung_kontieren&belegnr=" . $my_array [$a] ['BELEG_NR'] . "\">" . $my_array [$a] ['BELEG_NR'] . "</>\n";
			$netto = nummer_punkt2komma ( $my_array [$a] [NETTO] );
			$brutto = nummer_punkt2komma ( $my_array [$a] [BRUTTO] );
			$skonto_betrag = nummer_punkt2komma ( $my_array [$a] [SKONTOBETRAG] );
			echo "<tr><td>$beleg_link</td><td>$r_datum</td><td>$e_datum</td><td><b>$faellig_am</b></td><td>" . $this->rechnungs_aussteller_name . "</td><td>" . $this->rechnungs_empfaenger_name . "</td><td align=right>$netto €</td><td align=right>$brutto €</td><td align=right>$skonto_betrag €</td></tr>\n";
		}
		echo "</table>\n";
	}
	
	/* Alle erfassten Rechungen die noch nicht vollständig kontiert worden sind */
	/* Rechnungen die Positionen haben aber/und Rechnungen deren Kaufmenge <> Kontierungsmenge */
	function unvollstaendig_kontierte_rechungen_anzeigen() {
		/* Zählen aller Zeilen */
		/*
		 * $result = mysql_query ("SELECT RECHNUNGEN. * , COUNT( RECHNUNGEN_POSITIONEN.POSITION ) AS ANZAHL_POSITIONEN
		 * FROM RECHNUNGEN, RECHNUNGEN_POSITIONEN
		 * WHERE RECHNUNGEN.BELEG_NR = RECHNUNGEN_POSITIONEN.BELEG_NR && RECHNUNGEN.AKTUELL='1' && RECHNUNGEN_POSITIONEN.AKTUELL='1' GROUP BY RECHNUNGEN.BELEG_NR ASC");
		 */
		$result = mysql_query ( "SELECT * FROM RECHNUNGEN WHERE AKTUELL='1' ORDER BY BELEG_NR ASC" );
		
		while ( $row = mysql_fetch_assoc ( $result ) )
			$rechnungen_mit_positionen [] = $row;
		for($vv = 0; $vv < count ( $rechnungen_mit_positionen ); $vv ++) {
			$status_kontierung = $this->rechnung_auf_kontierung_pruefen ( $rechnungen_mit_positionen [$vv] [BELEG_NR] );
			// echo $rechnungen_mit_positionen[$vv][BELEG_NR].$status_kontierung."<br>\n";
			if ($status_kontierung == 'unvollstaendig') {
				$unkontierte_belege [] = $rechnungen_mit_positionen [$vv];
			}
		}
		
		$numrows = count ( $unkontierte_belege );
		$my_array = $unkontierte_belege;
		echo "<table class=rechnungen>\n";
		echo "<tr class=feldernamen><td>BNr</td><td>R-Datum</td><td>E-Datum</td><td>Fällig</td><td>Von</td><td>An</td><td width=80>Netto</td><td width=80>Brutto</td><td width=80>Skonto</td></tr>\n";
		
		for($a = 0; $a < count ( $my_array ); $a ++) {
			$belegnr = $my_array [$a] [BELEG_NR];
			$this->rechnung_grunddaten_holen ( $belegnr );
			$e_datum = date_mysql2german ( $my_array [$a] [EINGANGSDATUM] );
			$r_datum = date_mysql2german ( $my_array [$a] [RECHNUNGSDATUM] );
			$faellig_am = date_mysql2german ( $my_array [$a] [FAELLIG_AM] );
			$beleg_link = "<a href=\"?daten=rechnungen&option=rechnung_kontieren&belegnr=" . $my_array [$a] ['BELEG_NR'] . "\">" . $my_array [$a] ['BELEG_NR'] . "</>\n";
			$netto = nummer_punkt2komma ( $my_array [$a] [NETTO] );
			$brutto = nummer_punkt2komma ( $my_array [$a] [BRUTTO] );
			$skonto_betrag = nummer_punkt2komma ( $my_array [$a] [SKONTOBETRAG] );
			echo "<tr><td>$beleg_link</td><td>$r_datum</td><td>$e_datum</td><td><b>$faellig_am</b></td><td>" . $this->rechnungs_aussteller_name . "</td><td align=right>$netto €</td><td align=right>$brutto €</td><td align=right>$skonto_betrag €</td></tr>\n";
		}
		echo "</table>\n";
	}
	function erfasste_menge($belegnr) {
		$result = mysql_query ( " SELECT SUM( MENGE ) AS ERFASSTE_MENGE FROM `RECHNUNGEN_POSITIONEN` WHERE BELEG_NR = '$belegnr' && AKTUELL='1' " );
		$numrows = mysql_numrows ( $result );
		if ($numrows > 0) {
			$row = mysql_fetch_assoc ( $result );
			return $row ['ERFASSTE_MENGE'];
		} else {
			return $numrows;
		}
	}
	function kontierte_menge($belegnr) {
		$result = mysql_query ( " SELECT SUM( MENGE ) AS KONTIERTE_MENGE FROM `KONTIERUNG_POSITIONEN` WHERE BELEG_NR = '$belegnr' && AKTUELL='1'" );
		$numrows = mysql_numrows ( $result );
		if ($numrows > 0) {
			$row = mysql_fetch_assoc ( $result );
			return $row ['KONTIERTE_MENGE'];
		} else {
			return $numrows;
		}
	}
	function rechnung_kontierung_aufheben($belegnr) {
		mysql_query ( "UPDATE  `KONTIERUNG_POSITIONEN` SET AKTUELL='0' WHERE `BELEG_NR` ='$belegnr'" ) or die ( 'Kontierungsaufhebung nicht möglich' );
		return true;
	}
	function rechnung_auf_kontierung_pruefen($belegnr) {
		$erfasste_menge = $this->erfasste_menge ( $belegnr );
		$kontierte_menge = $this->kontierte_menge ( $belegnr );
		
		if (empty ( $kontierte_menge ) or empty ( $erfasste_menge )) {
			return 'unvollstaendig';
		}
		if ($kontierte_menge == $erfasste_menge) {
			return 'vollstaendig';
		}
		if ($kontierte_menge < $erfasste_menge) {
			return 'unvollstaendig';
		}
		if ($kontierte_menge > $erfasste_menge) {
			return 'falsch';
		}
	}
	function positions_pool_anzeigen() {
		// #########OBJEKTE###################
		/* Ein Array mit Objekten erstellen, dieser wird nachher mit Unterarrays gefällt */
		$objekte = new objekt ();
		$objekte_arr = $objekte->liste_aller_objekte_kurz ();
		/*
		 * echo "<pre>";
		 * print_r($objekte_arr);
		 * echo "</pre>";
		 */
		/* Aus dem Kontierungspool werden alle Positionen aller Objekte in ein Array geschoben */
		$positionen_arr = $this->pool_durchsuchen ( 'Objekt' );
		
		/* Array mit Objektpositionen durchlaufen und die Objekt_id als KOSTENTRAEGER_ID finden */
		for($a = 0; $a < count ( $positionen_arr ); $a ++) {
			// echo $positionen_arr[$a][KOSTENTRAEGER_ID]."<br>";
			$kostentraeger_id = $positionen_arr [$a] [KOSTENTRAEGER_ID];
			/* Mit der $kostentrager_id wird der Objektarraydurchlaufen und beim Treffer, die Position dem Unterarray von Objekte zugewiesen */
			for($i = 0; $i < count ( $objekte_arr ); $i ++) {
				if (in_array ( $kostentraeger_id, $objekte_arr [$i] )) {
					// echo "vorhanden $i<br>";
					$objekte_arr [$i] [OBJEKT_KOSTEN] [] = $positionen_arr [$a];
				} // end if
			} // end for 2
		} // end for 1
		  // ################HÄUSER######################
		/* Aus dem Kontierungspool werden alle Positionen aller Häuser in ein Array geschoben */
		$positionen_arr = $this->pool_durchsuchen ( 'Haus' );
		
		/* Array mit Objektpositionen durchlaufen und die Objekt_id als KOSTENTRAEGER_ID finden */
		for($a = 0; $a < count ( $positionen_arr ); $a ++) {
			// echo $positionen_arr[$a][KOSTENTRAEGER_ID]."<br>";
			$kostentraeger_id = $positionen_arr [$a] [KOSTENTRAEGER_ID];
			$haus_info = new haus ();
			$haus_info->get_haus_info ( $kostentraeger_id );
			$kostentraeger_id = $haus_info->objekt_id;
			/* Mit der $kostentrager_id wird der Objektarraydurchlaufen und beim Treffer, die Position dem Unterarray von Objekte zugewiesen */
			for($i = 0; $i < count ( $objekte_arr ); $i ++) {
				if (in_array ( $kostentraeger_id, $objekte_arr [$i] )) {
					// echo "vorhanden $i<br>";
					$objekte_arr [$i] [HAUS_KOSTEN] [] = $positionen_arr [$a];
				} // end if
			} // end for 2
		} // end for 1
		  // ############EINHEITEN###########################
		/* Aus dem Kontierungspool werden alle Positionen aller Einheiten in ein Array geschoben */
		$positionen_arr = $this->pool_durchsuchen ( 'Einheit' );
		
		/* Array mit Objektpositionen durchlaufen und die Objekt_id als KOSTENTRAEGER_ID finden */
		for($a = 0; $a < count ( $positionen_arr ); $a ++) {
			// echo $positionen_arr[$a][KOSTENTRAEGER_ID]."<br>";
			$kostentraeger_id = $positionen_arr [$a] [KOSTENTRAEGER_ID];
			$einheit_info = new einheit ();
			$einheit_info->get_einheit_haus ( $kostentraeger_id );
			$kostentraeger_id = $einheit_info->objekt_id;
			/* Mit der $kostentrager_id wird der Objektarraydurchlaufen und beim Treffer, die Position dem Unterarray von Objekte zugewiesen */
			for($i = 0; $i < count ( $objekte_arr ); $i ++) {
				if (in_array ( $kostentraeger_id, $objekte_arr [$i] )) {
					// echo "vorhanden $i<br>";
					$objekte_arr [$i] [EINHEIT_KOSTEN] [] = $positionen_arr [$a];
				} // end if
			} // end for 2
		} // end for 1
		  
		// echo "<pre>";
		  // print_r($positionen_arr);
		  // echo "</pre>";
		
		/*
		 * echo "<hr><pre>";
		 * print_r($objekte_arr);
		 * echo "</pre>";
		 */
		return $objekte_arr;
	}
	function objekt_kosten_positionen($objekt_id) {
		echo "<hr>OBJEKTKOSTEN";
		$objekte_arr = $this->positions_pool_anzeigen ();
		for($i = 0; $i < count ( $objekte_arr ); $i ++) {
			if (in_array ( $objekt_id, $objekte_arr [$i] )) {
				echo "vorhanden $i<br>";
				for($a = 0; $a < count ( $objekte_arr [$i] [OBJEKT_KOSTEN] ); $a ++) {
					$objekt_kosten [] = $objekte_arr [$i] [OBJEKT_KOSTEN] [$a];
				} // end for 1
			} // end if
		} // end for 1
		/*
		 * echo "<pre>";
		 * print_r($objekt_kosten);
		 * echo "</pre>";
		 */
		for($b = 0; $b < count ( $objekt_kosten ); $b ++) {
			$kontierung_id = $objekt_kosten [$b] [KONTIERUNG_ID];
			$objekt_kosten_positionen [] = $this->pool_position_holen ( $kontierung_id );
		}
		/*
		 * echo "<pre>";
		 * print_r($objekt_kosten_positionen);
		 * echo "</pre>";
		 */
		echo "OBJEKT_ENDE<hr>";
	}
	function haus_kosten_positionen($objekt_id) {
		echo "<hr>HAUSKOSTEN";
		$objekte_arr = $this->positions_pool_anzeigen ();
		for($i = 0; $i < count ( $objekte_arr ); $i ++) {
			if (in_array ( $objekt_id, $objekte_arr [$i] )) {
				echo "vorhanden $i<br>";
				for($a = 0; $a < count ( $objekte_arr [$i] [HAUS_KOSTEN] ); $a ++) {
					$haus_kosten [] = $objekte_arr [$i] [HAUS_KOSTEN] [$a];
				} // end for 1
			} // end if
		} // end for 1
		/*
		 * echo "<pre>";
		 * print_r($haus_kosten);
		 * echo "</pre>";
		 */
		for($b = 0; $b < count ( $haus_kosten ); $b ++) {
			$kontierung_id = $haus_kosten [$b] [KONTIERUNG_ID];
			$haus_kosten_positionen [] = $this->pool_position_holen ( $kontierung_id );
		}
		/*
		 * echo "<pre>";
		 * print_r($haus_kosten_positionen);
		 * echo "</pre>";
		 */
		echo "HAUSENDE<hr>";
	}
	function einheit_kosten_positionen($objekt_id) {
		echo "<hr>EINHEITKOSTEN";
		$objekte_arr = $this->positions_pool_anzeigen ();
		for($i = 0; $i < count ( $objekte_arr ); $i ++) {
			if (in_array ( $objekt_id, $objekte_arr [$i] )) {
				echo "vorhanden $i<br>";
				for($a = 0; $a < count ( $objekte_arr [$i] [EINHEIT_KOSTEN] ); $a ++) {
					$einheit_kosten [] = $objekte_arr [$i] [EINHEIT_KOSTEN] [$a];
				} // end for 1
			} // end if
		} // end for 1
		/*
		 * echo "<pre>";
		 * print_r($einheit_kosten);
		 * echo "</pre>";
		 */
		for($b = 0; $b < count ( $einheit_kosten ); $b ++) {
			$kontierung_id = $einheit_kosten [$b] [KONTIERUNG_ID];
			$einheit_kosten_positionen [] = $this->pool_position_holen ( $kontierung_id );
		}
		/*
		 * echo "<pre>";
		 * print_r($einheit_kosten_positionen);
		 * echo "</pre>";
		 */
		echo "EINHEITENDE<hr>";
	}
	function pool_position_holen($kontierung_id) {
		$result = mysql_query ( "SELECT * FROM `KONTIERUNG_POSITIONEN` WHERE KONTIERUNG_ID= '$kontierung_id' && AKTUELL='1' ORDER BY KONTIERUNG_DAT DESC LIMIT 0,1" );
		$numrows = mysql_numrows ( $result );
		if ($numrows > 0) {
			$row = mysql_fetch_assoc ( $result );
			return $row;
		}
	}
	function pool_durchsuchen($kostentraeger_typ) {
		$result = mysql_query ( "SELECT KONTIERUNG_DAT, KONTIERUNG_ID, KONTENRAHMEN_KONTO, KOSTENTRAEGER_TYP, KOSTENTRAEGER_ID FROM `KONTIERUNG_POSITIONEN` WHERE KOSTENTRAEGER_TYP = '$kostentraeger_typ' && AKTUELL='1' && WEITER_VERWENDEN='1' ORDER BY KOSTENTRAEGER_TYP, KOSTENTRAEGER_ID ASC" );
		$numrows = mysql_numrows ( $result );
		if ($numrows > 0) {
			while ( $row = mysql_fetch_assoc ( $result ) )
				$my_array [] = $row;
			return $my_array;
		}
	}
	function rechnung_aus_pool_zusammenstellen($kostentraeger_typ, $kostentraeger_id) {
		$result = mysql_query ( "SELECT KONTIERUNG_DAT, KONTIERUNG_ID, KONTENRAHMEN_KONTO, KOSTENTRAEGER_TYP, KOSTENTRAEGER_ID FROM `KONTIERUNG_POSITIONEN` WHERE KOSTENTRAEGER_TYP = '$kostentraeger_typ' && KOSTENTRAEGER_ID='$kostentraeger_id' && WEITER_VERWENDEN='1' && AKTUELL='1' ORDER BY BELEG_NR DESC, POSITION ASC" );
		$numrows = mysql_numrows ( $result );
		if ($numrows > 0) {
			while ( $row = mysql_fetch_assoc ( $result ) )
				$my_array [] = $row;
				/*
			 * echo "<pre>";
			 * print_r($my_array);
			 * echo "</pre>";
			 */
			for($a = 0; $a < count ( $my_array ); $a ++) {
				$positionen_detailiert [] = $this->pool_position_holen ( $my_array [$a] ['KONTIERUNG_ID'] );
			}
			// return $my_array;
			return $positionen_detailiert;
		} else {
			return false;
		}
	}
	function rechnung_an_haus_zusammenstellen($haus_id) {
		
		/* Positionen der hausbezogenen Kosten */
		$haus_rechnung_arr = $this->rechnung_aus_pool_zusammenstellen ( 'Haus', $haus_id );
		/* Alle einheitsbezogenen Kosten */
		$einheiten_im_pool = $this->pool_durchsuchen ( 'Einheit' );
		
		$einheit_info = new einheit ();
		for($a = 0; $a < count ( $einheiten_im_pool ); $a ++) {
			$einheit_id = $einheiten_im_pool [$a] [KOSTENTRAEGER_ID];
			$einheit_info->get_einheit_haus ( $einheit_id );
			$einheit_haus_id = $einheit_info->haus_id;
			/* Falls Einheit zum gewählten Haus gehört, Pos in Hausrechnung stellen */
			if ($einheit_haus_id == $haus_id) {
				$haus_rechnung_arr [] = $this->pool_position_holen ( $einheiten_im_pool [$a] [KONTIERUNG_ID] );
			}
		}
		/* Array bestehend aus Haus- und Einheitskosten */
		return ($haus_rechnung_arr);
	}
	function rechnung_an_objekt_zusammenstellen($objekt_id) {
		/* Positionen der objektbezogenen Kosten */
		$objekt_rechnung_arr = $this->rechnung_aus_pool_zusammenstellen ( 'Objekt', $objekt_id );
		/* Alle hausbezogenen Kosten */
		$haeuser_im_pool = $this->pool_durchsuchen ( 'Haus' );
		$haus_info = new haus ();
		/* Alle einheitsbezogenen Kosten */
		$einheiten_im_pool = $this->pool_durchsuchen ( 'Einheit' );
		$einheit_info = new einheit ();
		
		for($a = 0; $a < count ( $haeuser_im_pool ); $a ++) {
			$haus_id = $haeuser_im_pool [$a] ['KOSTENTRAEGER_ID'];
			$haus_info->get_haus_info ( $haus_id );
			$haus_objekt_id = $haus_info->objekt_id;
			/* Falls Haus zum gewählten Objekt gehört, Pos in Objektrechnung stellen */
			// echo $haus_objekt_id;
			if ($haus_objekt_id == $objekt_id) {
				$objekt_rechnung_arr [] = $this->pool_position_holen ( $haeuser_im_pool [$a] ['KONTIERUNG_ID'] );
			}
		}
		
		for($i = 0; $i < count ( $einheiten_im_pool ); $i ++) {
			$einheit_id = $einheiten_im_pool [$i] ['KOSTENTRAEGER_ID'];
			$einheit_info->get_einheit_haus ( $einheit_id );
			$einheit_objekt_id = $einheit_info->objekt_id;
			/* Falls Einheit zum gewählten Haus gehört, Pos in Hausrechnung stellen */
			if ($einheit_objekt_id == $objekt_id) {
				$objekt_rechnung_arr [] = $this->pool_position_holen ( $einheiten_im_pool [$i] ['KONTIERUNG_ID'] );
			}
		}
		
		/* Doppelte entfernen */
		// $objekt_rechnung_arr = array_unique($objekt_rechnung_arr);
		
		/* Array bestehend aus Haus- und Einheitskosten */
		// $positionen = array_sortByIndex($positionen,'BELEG_NR');
		// $objekt_rechnung_arr = array_sortByIndex($objekt_rechnung_arr,'BELEG_NR');
		return ($objekt_rechnung_arr);
	}
	
	/* Authoreninformationen d.h. Ersteller der neuen Rechnung aus Pool */
	function get_author_infos($typ, $id) {
		if ($typ == 'Lager') {
			$lager_info = new lager ();
			return $lager_bezeichnung = $lager_info->lager_bezeichnung ( $id );
		}
		if ($typ == 'Kasse') {
			$kassen_info = new kasse ();
			$kassen_info->get_kassen_info ( $id );
			return $kassen_info->kassen_name;
		}
		if ($typ == 'Partner') {
			$partner_info = new partners ();
			$partner_info->get_partner_name ( $id );
			return $partner_info->partner_name;
		}
	}
	
	/*
	 * Empfängerinformationen d.h. Empfänger der neuen Rechnung aus Pool
	 * bei Objekt -> Eigentümer
	 * Haus-Eigentümer ...usw
	 */
	function get_empfaenger_infos($typ, $id) {
		if ($typ == 'Lager') {
			$lager_info = new lager ();
			return $lager_bezeichnung = $lager_info->lager_bezeichnung ( $id );
		}
		if ($typ == 'Kasse') {
			$kassen_info = new kasse ();
			$kassen_info->get_kassen_info ( $id );
			return $kassen_info->kassen_name;
		}
		// #######
		if ($typ == 'Einheit') {
			$einheit_info = new einheit ();
			$einheit_info->get_einheit_info ( $id );
			$id = $einheit_info->haus_id;
			$typ = 'Haus';
		}
		if ($typ == 'Haus') {
			$haus_info = new haus ();
			$haus_info->get_haus_info ( $id );
			$id = $haus_info->objekt_id;
			$typ = 'Objekt';
		}
		if ($typ == 'Objekt') {
			$objekt_info = new objekt ();
			$objekt_info->get_objekt_name ( $id );
			$objekt_info->get_objekt_eigentuemer_partner ( $id );
			$id = $objekt_info->objekt_eigentuemer_partner_id;
			$typ = 'Partner';
			$this->objekt_name = $objekt_info->objekt_name;
			$this->rechnungs_empfaenger_typ = $typ;
			$this->rechnungs_empfaenger_id = $id;
		}
		if ($typ == 'Partner') {
			$partner_info = new partners ();
			$partner_info->get_partner_name ( $id );
			return $partner_info->partner_name;
		}
	}
	function rechnungs_kopf_zusammenstellung($kostentraeger_typ, $kostentraeger_id, $aussteller_typ, $aussteller_id) {
		$rechnung_von = $this->get_author_infos ( $aussteller_typ, $aussteller_id );
		$rechnung_an = $this->get_empfaenger_infos ( $kostentraeger_typ, $kostentraeger_id );
		echo "<table>";
		if ($kostentraeger_typ == 'Lager') {
			$rechnung_vzweck = "Rechnung an das Lager <b>$rechnung_an</b>";
		}
		if ($kostentraeger_typ == 'Kasse') {
			$rechnung_vzweck = "Rechnung an die Kasse <b>$rechnung_an</b>";
		}
		// #######
		if ($kostentraeger_typ == 'Einheit') {
			$einheit = $this->kostentraeger_ermitteln ( $kostentraeger_typ, $kostentraeger_id );
			$rechnung_vzweck = "Rechnung für Einheit $einheit";
		}
		if ($kostentraeger_typ == 'Haus') {
			$rechnung_vzweck = "Rechnung an den Eigentümer für ein Haus";
		}
		if ($kostentraeger_typ == 'Objekt') {
			$rechnung_vzweck = "Rechnung an den Eigentümer für das Objekt $this->objekt_name";
		}
		if ($kostentraeger_typ == 'Partner') {
			$rechnung_vzweck = "Rechnung an den Partner <b>$rechnung_an</b>";
		}
		echo "<tr><td colspan=2>$rechnung_vzweck</td></tr>";
		echo "<tr><td>Rechnung von</td><td>Rechnung an</td></tr>";
		echo "<tr><td>$rechnung_von</td><td>$rechnung_an</td></tr>";
		echo "</table>";
	}
	function rechnung_schreiben_positionen_wahl_ok($kostentraeger_typ, $kostentraeger_id, $positionen, $aussteller_typ, $aussteller_id) {
		$f = new formular ();
		$f->erstelle_formular ( "Rechnung aus Pool zusammenstellen TEST", NULL );
		$f->hidden_feld ( 'option', 'AUTO_RECHNUNG_VORSCHAU' );
		$f->send_button ( "senden_pos", "Speichern" );
		$f->ende_formular ();
	}
	
	/* Funkt. zur Auswahl der Positionen für eine neue Rechnung aus dem Pool */
	function rechnung_schreiben_positionen_wahl($kostentraeger_typ, $kostentraeger_id, $positionen, $aussteller_typ, $aussteller_id) {
		if (isset ( $_REQUEST ['csv'] )) {
			
			$this->pool_csv ( $kos_typ, $kos_id, $positionen, $aussteller_typ, $aussteller_id );
			die ();
		}
		// echo "<pre>";
		// print_r($positionen);
		// echo "</pre>";
		// die();
		$f = new formular ();
		$f->erstelle_formular ( "Rechnung aus Pool zusammenstellen", NULL );
		$f->hidden_feld ( 'option', 'AUTO_RECHNUNG_VORSCHAU' );
		// $js_action = 'onblur="javascript:rechnung_pool_neuberechnen(this.form)" onchange="javascript:rechnung_pool_neuberechnen(this.form)" onfocus="javascript:rechnung_pool_neuberechnen(this.form)" onmouseover="javascript:rechnung_pool_neuberechnen(this.form)"';
		
		$js_action = 'onmouseover="javascript:pool_berechnung(this.form)" onkeyup="javascript:pool_berechnung(this.form)" onmousedown="javascript:pool_berechnung(this.form)" onmouseup="javascript:pool_berechnung(this.form)" onmousemove="javascript:pool_berechnung(this.form)"';
		$objekt_info = new objekt ();
		if ($kostentraeger_typ == 'Objekt') {
			$objekt_info->get_objekt_eigentuemer_partner ( $kostentraeger_id );
			$rechnungs_empfaenger_id = $objekt_info->objekt_eigentuemer_partner_id;
		}
		
		if ($kostentraeger_typ == 'Einheit') {
			$this->get_empfaenger_infos ( $kostentraeger_typ, $kostentraeger_id );
			$rechnungs_empfaenger_typ = $this->rechnungs_empfaenger_typ;
			$rechnungs_empfaenger_id = $this->rechnungs_empfaenger_id;
		}
		
		if ($kostentraeger_typ == 'Lager') {
			$rechnungs_empfaenger_id = $kostentraeger_id;
			/*
			 * $l = new lager;
			 * $l->lager_name_partner($kostentraeger_id);
			 * $rechnungs_empfaenger_typ = 'Partner';
			 * $rechnungs_empfaenger_id = $l->lager_partner_id;
			 */
		}
		
		if ($kostentraeger_typ == 'Partner') {
			$rechnungs_empfaenger_id = $kostentraeger_id;
		}
		
		// $positionen = array_sortByIndex($positionen,'BELEG_NR');
		// $positionen = array_sortByIndex($positionen,'POSITION');
		// $positionen = array_orderby($positionen, 'BELEG_NR', SORT_DESC, 'POSITION', SORT_DESC);
		$positionen = array_msort ( $positionen, array (
				'BELEG_NR' => array (
						SORT_ASC 
				),
				'POSITION' => SORT_STRING 
		) );
		$this->rechnungs_kopf_zusammenstellung ( $kostentraeger_typ, $kostentraeger_id, $aussteller_typ, $aussteller_id );
		// echo '<pre>';
		// print_r($positionen1);
		// die();
		
		$self = $_SERVER ['QUERY_STRING'];
		echo "<a href=\"?$self&csv\">Als Excel</a>";
		
		echo "<table id=\"pos_tabelle\" class=rechnungen>";
		
		echo "<tr><td colspan=3>";
		$faellig_am = date ( "Y-m-t" );
		$faellig_am = date_mysql2german ( $faellig_am );
		$d_heute = date ( "d.m.Y" );
		$f->datum_feld ( 'Rechnungsdatum', 'rechnungsdatum', "$d_heute", 'rechnungsdatum' );
		$f->datum_feld ( 'Faellig am', 'faellig_am', "$faellig_am", 'faellig_am' );
		
		// $f->text_feld("Skonto in %:", "skonto", "0", "5", "skonto_feld", $js_action);
		
		echo "</td><td colspan=6>";
		echo "</td></tr>";
		echo "<tr><td colspan=\"6\">";
		// if($aussteller_typ == 'Partner'){
		$geld_konto_info = new geldkonto_info ();
		$geld_konto_info->dropdown_geldkonten ( $aussteller_typ, $aussteller_id );
		// }else{
		// $form->hidden_feld('geld_konto', '0');
		// }
		echo "</td></tr>";
		// onMouseover=\"BoxenAktivieren(this);
		echo "<div id=\"pool_tabelle\" $js_action>";
		// echo "<tr class=feldernamen><td width=\"30px\"><input type=\"checkbox\" onClick=\"this.value=check(this.form.positionen_list)\" $js_action>Alle</td><td>Rechnung</td><td>Position</td><td>Menge</td><td>Bezeichnung</td><td>Einzelpreis</td><td>Netto</td><td>Rabatt %</td><td>Skonto</td><td>MWSt</td><td>Kostentraeger</td></tr>";
		echo "<tr ><th>POOL</th><th><input type=\"checkbox\" onClick=\"this.value=check(this.form.positionen_list)\" $js_action>Alle</th><th>Rechnung</th><th>UPos</th><th>Pos</th><th>Menge</th><th>Bezeichnung</th><th>Einzelpreis</th><th>Netto</th><th>Rabatt %</th><th>Skonto</th><th>MWSt</th><th>Kostentraeger</th></tr>";
		$f->hidden_feld ( 'RECHNUNG_EMPFAENGER_TYP', "$kostentraeger_typ" );
		$f->hidden_feld ( 'RECHNUNG_EMPFAENGER_ID', "$rechnungs_empfaenger_id" );
		$f->hidden_feld ( 'RECHNUNG_AUSSTELLER_TYP', "$aussteller_typ" );
		$f->hidden_feld ( 'RECHNUNG_AUSSTELLER_ID', "$aussteller_id" );
		$f->hidden_feld ( 'RECHNUNG_KOSTENTRAEGER_ID', "$kostentraeger_id" );
		$f->hidden_feld ( 'RECHNUNG_KOSTENTRAEGER_TYP', "$kostentraeger_typ" );
		
		$rechnungs_summe = 0;
		$start = 3;
		// nummer of <tr>
		for($a = 0; $a < count ( $positionen ); $a ++) {
			$start ++;
			$zeile = $a + 1;
			
			$belegnr = $positionen [$a] ['BELEG_NR'];
			$this->rechnung_grunddaten_holen ( $belegnr );
			$f->hidden_feld ( "positionen[$a][beleg_nr]", "$belegnr" );
			$position = $positionen [$a] ['POSITION'];
			$f->hidden_feld ( "positionen[$a][position]", "$position" );
			$artikel_bezeichnung = $this->kontierungsartikel_holen ( $belegnr, $position );
			$pos_kostentraeger_typ = $positionen [$a] ['KOSTENTRAEGER_TYP'];
			$pos_kostentraeger_id = $positionen [$a] ['KOSTENTRAEGER_ID'];
			$kostentraeger = $this->kostentraeger_ermitteln ( $pos_kostentraeger_typ, $pos_kostentraeger_id );
			$menge = nummer_punkt2komma ( $positionen [$a] ['MENGE'] );
			$epreis = nummer_punkt2komma ( $positionen [$a] ['EINZEL_PREIS'] );
			$gpreis = nummer_punkt2komma ( $positionen [$a] ['GESAMT_SUMME'] );
			$rabatt_satz = nummer_punkt2komma ( $positionen [$a] ['RABATT_SATZ'] );
			$skonto = nummer_punkt2komma ( $positionen [$a] ['SKONTO'] );
			$rechnungs_summe = $rechnungs_summe + (nummer_komma2punkt ( $menge ) * nummer_komma2punkt ( $epreis ));
			$mwst_satz_in_prozent = nummer_punkt2komma ( $this->mwst_satz_der_position ( $belegnr, $position ) );
			// aus Beleg infos holen //
			$kontierung_id = $positionen [$a] ['KONTIERUNG_ID'];
			$kontierung_dat = $positionen [$a] ['KONTIERUNG_DAT'];
			$f->hidden_feld ( "positionen[$a][kontierung_dat]", "$kontierung_dat" );
			$link_rechnung_ansehen = "<a href=\"index.php?daten=rechnungen&option=rechnungs_uebersicht&belegnr=$belegnr\">$this->rechnungsnummer</a>";
			
			echo "<tr id=\"tr_zeile.$start\"><td>";
			
			$rrr = new rechnungen ();
			/*
			 * $pool_bez = substr($rrr->get_pool_bez($_SESSION['pool_id'],0,5));
			 * $pool_bez = $rrr->get_pool_bez($_SESSION['pool_id']);
			 */
			// $js_weiter = "onclick=\"pool_wahl('Rechnung aus Pool zusammenstellen', '$kontierung_dat', '$kostentraeger_typ', '$kostentraeger_id')\"";
			// $js_weiter = "onclick=\"pool_wahl('Rechnung aus Pool zusammenstellen', 'this.form.positionen', '$kostentraeger_typ', '$kostentraeger_id')\"";
			// $idid = "tr_zeile".$start;
			// $js_weiter = "onclick=\"deleteCurrentRow(this)\"";
			// $js_weiter = "onclick=\"zeile_entfernen(this, 'dat', 'kos_typ', 'kos_id', 'pool_id')\"";
			// $f->button_js('ip', "$pool_bez", $js_weiter);
			/*
			 * $f->button_js('ip', "$pool_bez", $js_weiter);
			 * $f->button_js('ip', "$pool_bez", $js_weiter);
			 */
			// echo $pool_bez;
			$rrr->btn_pool ( $kostentraeger_typ, $kostentraeger_id, $kontierung_dat, 'this' );
			
			echo "</td><td>$zeile<input type=\"checkbox\" name=uebernehmen[] id=\"positionen_list\" value=\"$a\" $js_action></td><td>$link_rechnung_ansehen</td><td>$position</td><td>$zeile.</td><td>";
			
			$f->text_feld ( "Menge:", "positionen[$a][menge]", "$menge", "5", "mengen_feld", $js_action );
			// $f->hidden_feld("positionen[$a][bezeichnung]", "$artikel_bezeichnung");
			echo "</td><td>$artikel_bezeichnung</td><td>";
			$f->text_feld ( "Einzelpreis:", "positionen[$a][preis]", "$epreis", "8", "epreis_feld", $js_action );
			echo "</td><td>";
			$f->text_feld_inaktiv ( "Netto:", "", "$gpreis", "8", "netto_feld", $js_action );
			echo "</td><td>";
			// $gpreis_brutto = ($gpreis / 100) * (100 + $rechnung->rechnungs_mwst_satz);
			$gpreis_brutto = ($gpreis / 100) * (100 + $mwst_satz_in_prozent);
			$gpreis_brutto = ($gpreis_brutto * 100) / 100;
			$gpreis_brutto = nummer_punkt2komma ( $gpreis_brutto );
			// $form->text_feld("Brutto:", "positionen[$a][gpreis_brutto]", "$gpreis_brutto", "5");
			
			$f->text_feld ( "Rabatt:", "positionen[$a][rabatt_satz]", "$rabatt_satz", "5", "rabatt_feld", $js_action );
			// $f->hidden_feld("positionen[$a][pos_mwst]", "$mwst_satz_in_prozent");
			echo "</td><td>";
			$f->text_feld ( "Skonto:", "positionen[$a][skonto]", "$skonto", "5", "skonto_feld", $js_action );
			// $f->hidden_feld("positionen[$a][pos_mwst]", "$mwst_satz_in_prozent");
			echo "</td><td>";
			$f->text_feld ( "Mwst:", "mwst_satz", "$mwst_satz_in_prozent", "3", "mwst_feld", $js_action );
			echo "</td><td valign=bottom>$kostentraeger</td></tr>";
			// $f->hidden_feld("positionen[$a][kontierung_id]", "$kontierung_id");
		}
		
		echo "<tr><td colspan=10><hr></td></tr></table>";
		echo "<table>";
		
		// echo "<tr><td><input type=\"checkbox\" name=\"in_rechung_stellen\" id=\"in_rechung_stellen\" onclick=\"check_ob_pos_gewaehlt(this, this.form.positionen_list)\"><b>Eingabe beenden</b></td>\n</tr>";
		
		echo "<tr><td>";
		
		$f->text_bereich ( 'Kurzbeschreibung', 'kurzbeschreibung', '', 30, 30, 'kurzbeschreibung' );
		echo "<br>";
		$f->send_button_disabled ( "senden_pos", "Speichern deaktiviert", "speichern_button2" );
		echo "</td></tr>";
		
		echo "<tr><td colspan=9><hr></td></tr>";
		echo "<tr><td colspan=8 align=right>Netto ausgewählte Positionen</td><td id=\"g_netto_ausgewaehlt\"></td></tr>";
		echo "<tr><td colspan=8 align=right>Brutto ausgewählte Positionen</td><td id=\"g_brutto_ausgewaehlt\"></td></tr>";
		echo "<tr><td colspan=8 align=right>Skontonachlass</td><td id=\"g_skonto_nachlass\"></td></tr>";
		echo "<tr><td colspan=8 align=right>Skontobetrag</td><td id=\"g_skonto_betrag\"></td></tr>";
		echo "<tr><td colspan=9><hr></td></tr>";
		/*
		 * echo "<tr><td colspan=8 align=right>Gesamt Netto errechnet</td><td id=\"g_netto_errechnet\"></td></tr>";
		 * echo "<tr><td colspan=8 align=right>Gesamt Brutto errechnet</td><td id=\"g_brutto_errechnet\"></td></tr>";
		 * echo "<tr><td colspan=8 align=right>Durchschnittsrabatt im Pool</td><td id=\"durchschnitt_rabatt\"></td></tr>";
		 */
		echo "</table>";
		echo "</div>";
		$f->ende_formular ();
	}
	function pool_csv($kos_typ, $kos_id, $positionen, $aussteller_typ, $aussteller_id) {
		/*
		 * echo '<pre>';
		 * print_r($positionen);
		 * die();
		 */
		ob_clean ();
		// ausgabepuffer leeren
		$fileName = 'pool' . date ( "d-m-Y" ) . '.xls';
		header ( "Content-type: application/vnd.ms-excel" );
		header ( "Content-Disposition: attachment; filename=$fileName" );
		header ( "Content-Disposition: inline; filename=$fileName" );
		
		echo "ARTNR\tBEZEICHNUNG\tMENGE\tPREIS\tRABATT_SATZ\tMWST_SATZ\tSKONTO\tGESAMT NETTO" . "\n";
		$anz = count ( $positionen );
		for($a = 0; $a < $anz; $a ++) {
			$position = $positionen [$a] ['POSITION'];
			$belegnr = $positionen [$a] ['BELEG_NR'];
			$artikelnr = $this->art_nr_from_beleg ( $belegnr, $position );
			$menge = nummer_punkt2komma ( $positionen [$a] ['MENGE'] );
			$bez = $this->kontierungsartikel_holen ( $belegnr, $position );
			$preis = nummer_punkt2komma ( $positionen [$a] ['EINZEL_PREIS'] );
			$rabatt_satz = nummer_punkt2komma ( $positionen [$a] ['RABATT_SATZ'] );
			$mwst_satz = $positionen [$a] ['MWST_SATZ'];
			$skonto = nummer_punkt2komma ( $positionen [$a] ['SKONTO'] );
			$g_preis_n = nummer_punkt2komma ( (nummer_komma2punkt ( $menge ) * nummer_komma2punkt ( $preis )) / 100 * (100 - nummer_komma2punkt ( $rabatt_satz )) );
			echo "$artikelnr\t$bez\t$menge\t$preis\t$rabatt_satz\t$mwst_satz\t$skonto\t$g_preis_n\n";
		}
	}
	
	/* Funkt. zur Auswahl der Positionen für eine neue Rechnung aus dem Pool */
	function rechnung_schreiben_positionen_wahl_LETZTE($kostentraeger_typ, $kostentraeger_id, $positionen, $aussteller_typ, $aussteller_id) {
		// echo "<pre>";
		// print_r($positionen);
		// echo "</pre>";
		$f = new formular ();
		$f->erstelle_formular ( "Rechnung aus Pool zusammenstellen", NULL );
		$f->hidden_feld ( 'option', 'AUTO_RECHNUNG_VORSCHAU' );
		$js_action = 'onblur="javascript:rechnung_pool_neuberechnen(this.form)" onchange="javascript:rechnung_pool_neuberechnen(this.form)" onfocus="javascript:rechnung_pool_neuberechnen(this.form)" onmouseover="javascript:rechnung_pool_neuberechnen(this.form)"';
		
		$objekt_info = new objekt ();
		if ($kostentraeger_typ == 'Objekt') {
			$objekt_info->get_objekt_eigentuemer_partner ( $kostentraeger_id );
			$rechnungs_empfaenger_id = $objekt_info->objekt_eigentuemer_partner_id;
		}
		
		if ($kostentraeger_typ == 'Einheit') {
			$this->get_empfaenger_infos ( $kostentraeger_typ, $kostentraeger_id );
			$rechnungs_empfaenger_typ = $this->rechnungs_empfaenger_typ;
			$rechnungs_empfaenger_id = $this->rechnungs_empfaenger_id;
		}
		
		if ($kostentraeger_typ == 'Lager') {
			$rechnungs_empfaenger_id = $kostentraeger_id;
			/*
			 * $l = new lager;
			 * $l->lager_name_partner($kostentraeger_id);
			 * $rechnungs_empfaenger_typ = 'Partner';
			 * $rechnungs_empfaenger_id = $l->lager_partner_id;
			 */
		}
		
		if ($kostentraeger_typ == 'Partner') {
			$rechnungs_empfaenger_id = $kostentraeger_id;
		}
		
		$positionen = array_sortByIndex ( $positionen, 'BELEG_NR' );
		
		$this->rechnungs_kopf_zusammenstellung ( $kostentraeger_typ, $kostentraeger_id, $aussteller_typ, $aussteller_id );
		
		echo "<table class=rechnungen>";
		echo "<tr><td colspan=3>";
		$faellig_am = date ( "Y-m-t" );
		$faellig_am = date_mysql2german ( $faellig_am );
		$datum_feld = 'document.getElementById("rechnungsdatum").value';
		$js_datum = "onchange='check_datum($datum_feld)'";
		$f->text_feld ( 'Rechnungsdatum:', 'rechnungsdatum', '', '20', 'rechnungsdatum', $js_datum );
		
		$datum_feld = 'document.getElementById("faellig_am").value';
		$js_datum = "onchange='check_datum($datum_feld)'";
		$f->text_feld ( 'Faellig am:', 'faellig_am', "$faellig_am", '20', 'faellig_am', $js_datum );
		$f->text_feld ( "Skonto in %:", "skonto", "0", "5", "skonto_feld", $js_action );
		
		echo "</td><td colspan=6>";
		echo "</td></tr>";
		// onMouseover=\"BoxenAktivieren(this);
		echo "<tr class=feldernamen><td width=\"30px\"><input type=\"checkbox\" onClick=\"this.value=check(this.form.positionen_list)\">Alle</td><td>Position</td><td>Menge</td><td>Bezeichnung</td><td>Einzelpreis</td><td>Netto</td><td>Rabatt %</td><td>MWSt</td><td>Kostentraeger</td></tr>";
		$f->hidden_feld ( 'RECHNUNG_EMPFAENGER_TYP', "$kostentraeger_typ" );
		$f->hidden_feld ( 'RECHNUNG_EMPFAENGER_ID', "$rechnungs_empfaenger_id" );
		$f->hidden_feld ( 'RECHNUNG_AUSSTELLER_TYP', "$aussteller_typ" );
		$f->hidden_feld ( 'RECHNUNG_AUSSTELLER_ID', "$aussteller_id" );
		$f->hidden_feld ( 'RECHNUNG_KOSTENTRAEGER_ID', "$kostentraeger_id" );
		$f->hidden_feld ( 'RECHNUNG_KOSTENTRAEGER_TYP', "$kostentraeger_typ" );
		$f->hidden_feld ( 'RECHNUNG_NETTO_BETRAG', NULL );
		$f->hidden_feld ( 'RECHNUNG_BRUTTO_BETRAG', NULL );
		$f->hidden_feld ( 'RECHNUNG_SKONTO_BETRAG', NULL );
		$rechnungs_summe = 0;
		for($a = 0; $a < count ( $positionen ); $a ++) {
			$zeile = $a + 1;
			
			$belegnr = $positionen [$a] ['BELEG_NR'];
			$f->hidden_feld ( "positionen[$a][beleg_nr]", "$belegnr" );
			$position = $positionen [$a] [POSITION];
			$f->hidden_feld ( "positionen[$a][position]", "$position" );
			$artikel_bezeichnung = $this->kontierungsartikel_holen ( $belegnr, $position );
			$f->hidden_feld ( "positionen[$a][artikel_nr]", "$this->artikel_nr" );
			$f->hidden_feld ( "positionen[$a][art_lieferant]", "$this->art_lieferant" );
			
			$pos_kostentraeger_typ = $positionen [$a] [KOSTENTRAEGER_TYP];
			$f->hidden_feld ( "positionen[$a][position_kostentraeger_typ]", "$pos_kostentraeger_typ" );
			$pos_kostentraeger_id = $positionen [$a] [KOSTENTRAEGER_ID];
			$f->hidden_feld ( "positionen[$a][position_kostentraeger_id]", "$pos_kostentraeger_id" );
			$verwendungs_jahr = $positionen [$a] [VERWENDUNGS_JAHR];
			$f->hidden_feld ( "positionen[$a][verwendungs_jahr]", "$verwendungs_jahr" );
			$kontenrahmen_konto = $positionen [$a] [KONTENRAHMEN_KONTO];
			$f->hidden_feld ( "positionen[$a][kontenrahmen_konto]", "$kontenrahmen_konto" );
			$kostentraeger = $this->kostentraeger_ermitteln ( $pos_kostentraeger_typ, $pos_kostentraeger_id );
			// echo "$menge $kontenrahmen_konto $kostentraeger_typ $kostentraeger<br>\n";
			$menge = $positionen [$a] [MENGE];
			$f->hidden_feld ( "positionen[$a][ursprungs_menge]", "$menge" );
			$epreis = $positionen [$a] [EINZEL_PREIS];
			$gpreis = $positionen [$a] [GESAMT_SUMME];
			$rabatt_satz = $positionen [$a] [RABATT_SATZ];
			$rechnungs_summe = $rechnungs_summe + ($menge * $epreis);
			$mwst_satz_in_prozent = $this->mwst_satz_der_position ( $belegnr, $position );
			// aus Beleg infos holen //
			$kontierung_id = $positionen [$a] [KONTIERUNG_ID];
			$kontierung_dat = $positionen [$a] [KONTIERUNG_DAT];
			$f->hidden_feld ( "positionen[$a][kontierung_dat]", "$kontierung_dat" );
			
			echo "<tr><td><input type=\"checkbox\" name=uebernehmen[] id=\"positionen_list\" value=\"$a\" $js_action></td><td>$zeile. ERF:$belegnr</td><td>";
			
			$f->text_feld ( "Menge:", "positionen[$a][menge]", "$menge", "5", "mengen_feld", $js_action );
			// $f->hidden_feld("positionen[$a][bezeichnung]", "$artikel_bezeichnung");
			echo "</td><td>$artikel_bezeichnung</td><td>";
			$f->text_feld ( "Einzelpreis:", "positionen[$a][preis]", "$epreis", "8", "epreis_feld", $js_action );
			echo "</td><td>";
			$f->text_feld ( "Netto:", "positionen[$a][gpreis]", "$gpreis", "8", "netto_feld", $js_action );
			echo "</td><td>";
			// $gpreis_brutto = ($gpreis / 100) * (100 + $rechnung->rechnungs_mwst_satz);
			$gpreis_brutto = ($gpreis / 100) * (100 + $mwst_satz_in_prozent);
			$gpreis_brutto = ($gpreis_brutto * 100) / 100;
			$gpreis_brutto = nummer_punkt2komma ( $gpreis_brutto );
			// $form->text_feld("Brutto:", "positionen[$a][gpreis_brutto]", "$gpreis_brutto", "5");
			
			$f->text_feld ( "Rabatt:", "positionen[$a][rabatt_satz]", "$rabatt_satz", "5", "rabatt_feld", $js_action );
			$f->hidden_feld ( "positionen[$a][pos_mwst]", "$mwst_satz_in_prozent" );
			echo "</td><td>";
			$f->text_feld ( "Mwst:", "mwst_satz", "$mwst_satz_in_prozent", "3", "mwst_feld", $js_action );
			echo "$mwst_satz</td><td valign=bottom>$kostentraeger</td></tr>";
			$f->hidden_feld ( "positionen[$a][kontierung_id]", "$kontierung_id" );
		}
		
		echo "<tr><td colspan=9><hr></td></tr></table>";
		echo "<table><tr><td colspan=\"9\">";
		// if($aussteller_typ == 'Partner'){
		$geld_konto_info = new geldkonto_info ();
		$geld_konto_info->dropdown_geldkonten ( $aussteller_typ, $aussteller_id );
		// }else{
		// $form->hidden_feld('geld_konto', '0');
		// }
		echo "</td></tr>";
		
		// echo "<tr><td><input type=\"checkbox\" name=\"in_rechung_stellen\" id=\"in_rechung_stellen\" onclick=\"check_ob_pos_gewaehlt(this, this.form.positionen_list)\"><b>Eingabe beenden</b></td>\n</tr>";
		
		echo "<tr><td>";
		
		$f->text_bereich ( 'Kurzbeschreibung', 'kurzbeschreibung', '', 30, 30, 'kurzbeschreibung' );
		echo "<br>";
		$f->send_button_disabled ( "senden_pos", "Speichern deaktiviert", "speichern_button2" );
		echo "</td></tr>";
		
		echo "<tr><td colspan=9><hr></td></tr>";
		echo "<tr><td colspan=8 align=right>Netto ausgewählte Positionen</td><td id=\"g_netto_ausgewaehlt\"></td></tr>";
		echo "<tr><td colspan=8 align=right>Brutto ausgewählte Positionen</td><td id=\"g_brutto_ausgewaehlt\"></td></tr>";
		echo "<tr><td colspan=8 align=right>Skontonachlass</td><td id=\"g_skonto_nachlass\"></td></tr>";
		echo "<tr><td colspan=8 align=right>Skontobetrag</td><td id=\"g_skonto_betrag\"></td></tr>";
		echo "<tr><td colspan=9><hr></td></tr>";
		echo "<tr><td colspan=8 align=right>Gesamt Netto errechnet</td><td id=\"g_netto_errechnet\"></td></tr>";
		echo "<tr><td colspan=8 align=right>Gesamt Brutto errechnet</td><td id=\"g_brutto_errechnet\"></td></tr>";
		echo "<tr><td colspan=8 align=right>Durchschnittsrabatt im Pool</td><td id=\"durchschnitt_rabatt\"></td></tr>";
		echo "</table>";
		$f->ende_formular ();
	}
	function rechnung_schreiben_positionen_wahl_ALT($kostentraeger_typ, $kostentraeger_id, $positionen, $aussteller_typ, $aussteller_id) {
		// echo "<pre>";
		// print_r($positionen);
		// echo "</pre>";
		$form = new mietkonto ();
		$form->erstelle_formular ( "Rechnung aus Pool zusammenstellen", NULL );
		$js_action = 'onblur="javascript:rechnung_pool_neuberechnen(this.form)" onchange="javascript:rechnung_pool_neuberechnen(this.form)" onfocus="javascript:rechnung_pool_neuberechnen(this.form)" onmouseover="javascript:rechnung_pool_neuberechnen(this.form)"';
		
		$objekt_info = new objekt ();
		if ($kostentraeger_typ == 'Objekt') {
			$objekt_info->get_objekt_eigentuemer_partner ( $kostentraeger_id );
			$rechnungs_empfaenger_id = $objekt_info->objekt_eigentuemer_partner_id;
		}
		
		if ($kostentraeger_typ == 'Einheit') {
			$this->get_empfaenger_infos ( $kostentraeger_typ, $kostentraeger_id );
			$rechnungs_empfaenger_typ = $this->rechnungs_empfaenger_typ;
			$rechnungs_empfaenger_id = $this->rechnungs_empfaenger_id;
		}
		
		if ($kostentraeger_typ == 'Lager') {
			$rechnungs_empfaenger_id = $kostentraeger_id;
		}
		
		if ($kostentraeger_typ == 'Partner') {
			$rechnungs_empfaenger_id = $kostentraeger_id;
		}
		
		/*
		 * $form->hidden_feld("Empfaenger", "$rechnungs_empfaenger_id");
		 * $form->hidden_feld("empfaenger_typ", "Partner");
		 * $form->hidden_feld("Aussteller", "$aussteller_id");
		 * $form->hidden_feld("aussteller_typ", $aussteller_typ);
		 */
		/*
		 * echo "<pre>";
		 * print_r($positionen);
		 * echo "</pre>";
		 */
		$positionen = array_sortByIndex ( $positionen, 'BELEG_NR' );
		/*
		 * echo "<pre>";
		 * print_r($positionen);
		 * echo "</pre>";
		 */
		// echo "<h1>$kostentraeger_typ, $kostentraeger_id, $aussteller_typ, $aussteller_id</h1>";
		
		$this->rechnungs_kopf_zusammenstellung ( $kostentraeger_typ, $kostentraeger_id, $aussteller_typ, $aussteller_id );
		
		echo "<table class=rechnungen>";
		echo "<tr><td colspan=3>";
		$faellig_am = date ( "Y-m-t" );
		$faellig_am = date_mysql2german ( $faellig_am );
		$datum_feld = 'document.getElementById("rechnungsdatum").value';
		$formular = new formular ();
		$js_datum = "onchange='check_datum($datum_feld)'";
		$formular->text_feld ( 'Rechnungsdatum:', 'rechnungsdatum', '', '20', 'rechnungsdatum', $js_datum );
		
		// $form->text_feld_js("Rechnungsdatum", "rechnungsdatum", "", "20", "rechnungsdatum", '');
		$datum_feld = 'document.getElementById("faellig_am").value';
		$js_datum = "onchange='check_datum($datum_feld)'";
		$formular->text_feld ( 'Faellig am:', 'faellig_am', "$faellig_am", '20', 'faellig_am', $js_datum );
		// $form->text_feld_js("Faellig am:", "faellig_am", "$faellig_am", "20", "faellig_am", $js_action);
		
		// $form->text_feld_js("Skonto in %:", "skonto", "0", "5", "skonto_feld", "$js_action");
		$form->text_feld_js ( "Skonto in %:", "skonto", "0", "5", "skonto_feld", "" );
		
		echo "</td><td colspan=6>";
		echo "</td></tr>";
		// onMouseover=\"BoxenAktivieren(this);
		echo "<tr class=feldernamen><td width=\"30px\"><input type=\"checkbox\" onClick=\"this.value=check(this.form.positionen_list)\" $js_action>Alle</td><td>Position</td><td>Menge</td><td>Bezeichnung</td><td>Einzelpreis</td><td>Netto</td><td>Rabatt %</td><td>MWSt</td><td>Kostentraeger</td></tr>";
		
		$rechnungs_summe = 0;
		for($a = 0; $a < count ( $positionen ); $a ++) {
			$zeile = $a + 1;
			
			$belegnr = $positionen [$a] [BELEG_NR];
			$form->hidden_feld ( "positionen[$a][beleg_nr]", "$belegnr" );
			$position = $positionen [$a] [POSITION];
			$form->hidden_feld ( "positionen[$a][position]", "$position" );
			$artikel_bezeichnung = $this->kontierungsartikel_holen ( $belegnr, $position );
			$form->hidden_feld ( "positionen[$a][artikel_nr]", "$this->artikel_nr" );
			$form->hidden_feld ( "positionen[$a][art_lieferant]", "$this->art_lieferant" );
			
			$pos_kostentraeger_typ = $positionen [$a] [KOSTENTRAEGER_TYP];
			$form->hidden_feld ( "positionen[$a][position_kostentraeger_typ]", "$pos_kostentraeger_typ" );
			$pos_kostentraeger_id = $positionen [$a] [KOSTENTRAEGER_ID];
			$form->hidden_feld ( "positionen[$a][position_kostentraeger_id]", "$pos_kostentraeger_id" );
			$verwendungs_jahr = $positionen [$a] [VERWENDUNGS_JAHR];
			$form->hidden_feld ( "positionen[$a][verwendungs_jahr]", "$verwendungs_jahr" );
			$kontenrahmen_konto = $positionen [$a] [KONTENRAHMEN_KONTO];
			$form->hidden_feld ( "positionen[$a][kontenrahmen_konto]", "$kontenrahmen_konto" );
			$kostentraeger = $this->kostentraeger_ermitteln ( $pos_kostentraeger_typ, $pos_kostentraeger_id );
			// echo "$menge $kontenrahmen_konto $kostentraeger_typ $kostentraeger<br>\n";
			$menge = $positionen [$a] [MENGE];
			$form->hidden_feld ( "positionen[$a][ursprungs_menge]", "$menge" );
			$epreis = $positionen [$a] [EINZEL_PREIS];
			$gpreis = $positionen [$a] [GESAMT_SUMME];
			$rabatt_satz = $positionen [$a] [RABATT_SATZ];
			$rechnungs_summe = $rechnungs_summe + ($menge * $epreis);
			$mwst_satz_in_prozent = $this->mwst_satz_der_position ( $belegnr, $position );
			// aus Beleg infos holen //
			$kontierung_id = $positionen [$a] [KONTIERUNG_ID];
			$kontierung_dat = $positionen [$a] [KONTIERUNG_DAT];
			$form->hidden_feld ( "positionen[$a][kontierung_dat]", "$kontierung_dat" );
			
			echo "<tr><td><input type=\"checkbox\" name=uebernehmen[] id=\"positionen_list\" value=\"$a\" $js_action></td><td>$zeile.</td><td>ERF $belegnr</td><td>";
			
			$form->text_feld_js ( "Menge:", "positionen[$a][menge]", "$menge", "5", "mengen_feld", $js_action );
			$form->hidden_feld ( "positionen[$a][bezeichnung]", "$artikel_bezeichnung" );
			echo "</td><td>$artikel_bezeichnung</td><td>";
			$form->text_feld_js ( "Einzelpreis:", "positionen[$a][preis]", "$epreis", "8", "epreis_feld", $js_action );
			echo "</td><td>";
			$form->text_feld_js ( "Netto:", "positionen[$a][gpreis]", "$gpreis", "8", "netto_feld", $js_action );
			echo "</td><td>";
			// $gpreis_brutto = ($gpreis / 100) * (100 + $rechnung->rechnungs_mwst_satz);
			$gpreis_brutto = ($gpreis / 100) * (100 + $mwst_satz_in_prozent);
			$gpreis_brutto = ($gpreis_brutto * 100) / 100;
			$gpreis_brutto = nummer_punkt2komma ( $gpreis_brutto );
			// $form->text_feld("Brutto:", "positionen[$a][gpreis_brutto]", "$gpreis_brutto", "5");
			
			$form->text_feld_js ( "Rabatt:", "positionen[$a][rabatt_satz]", "$rabatt_satz", "5", "rabatt_feld", $js_action );
			$form->hidden_feld ( "positionen[$a][pos_mwst]", "$mwst_satz_in_prozent" );
			echo "</td><td>";
			$form->text_feld_js ( "Mwst:", "mwst_satz", "$mwst_satz_in_prozent", "3", "mwst_feld", $js_action );
			echo "$mwst_satz</td><td valign=bottom>$kostentraeger</td></tr>";
			$form->hidden_feld ( "positionen[$a][kontierung_id]", "$kontierung_id" );
		}
		
		// $form->hidden_feld('option', 'AUTO_RECHNUNG_VORSCHAU');
		$form->hidden_feld ( "option", "AUTO_RECHNUNG_VORSCHAU" );
		$form->hidden_feld ( 'RECHNUNG_EMPFAENGER_TYP', "$kostentraeger_typ" );
		$form->hidden_feld ( 'RECHNUNG_EMPFAENGER_ID', "$rechnungs_empfaenger_id" );
		$form->hidden_feld ( 'RECHNUNG_AUSSTELLER_TYP', "$aussteller_typ" );
		$form->hidden_feld ( 'RECHNUNG_AUSSTELLER_ID', "$aussteller_id" );
		$form->hidden_feld ( 'RECHNUNG_KOSTENTRAEGER_ID', "$kostentraeger_id" );
		$form->hidden_feld ( 'RECHNUNG_KOSTENTRAEGER_TYP', "$kostentraeger_typ" );
		$form->hidden_feld ( 'RECHNUNG_NETTO_BETRAG', NULL );
		$form->hidden_feld ( 'RECHNUNG_BRUTTO_BETRAG', NULL );
		$form->hidden_feld ( 'RECHNUNG_SKONTO_BETRAG', NULL );
		echo "<tr><td colspan=9><hr></td></tr>";
		echo "<tr><td colspan=\"9\">";
		// if($aussteller_typ == 'Partner'){
		$geld_konto_info = new geldkonto_info ();
		$geld_konto_info->dropdown_geldkonten ( $aussteller_typ, $aussteller_id );
		// }else{
		// $form->hidden_feld('geld_konto', '0');
		// }
		echo "</td></tr>";
		
		echo "<tr><td><input type=\"checkbox\" name=in_rechung_stellen id=\"in_rechung_stellen\"  onclick=\"check_ob_pos_gewaehlt(this)\"><b>Eingabe beenden</b></td><td>";
		
		echo "<tr><td>";
		$form->text_bereich ( 'Kurzbeschreibung', 'kurzbeschreibung', '', 30, 10 );
		echo "<br>";
		// $form->send_button_disabled("senden_pos", "Speichern deaktiviert", "speichern_button2");
		$form->send_button ( "senden_pos", "Speichern" );
		echo "</td></tr>";
		
		echo "<tr><td colspan=9><hr></td></tr>";
		echo "<tr><td colspan=8 align=right>Netto ausgewählte Positionen</td><td id=\"g_netto_ausgewaehlt\"></td></tr>";
		echo "<tr><td colspan=8 align=right>Brutto ausgewählte Positionen</td><td id=\"g_brutto_ausgewaehlt\"></td></tr>";
		echo "<tr><td colspan=8 align=right>Skontonachlass</td><td id=\"g_skonto_nachlass\"></td></tr>";
		echo "<tr><td colspan=8 align=right>Skontobetrag</td><td id=\"g_skonto_betrag\"></td></tr>";
		echo "<tr><td colspan=9><hr></td></tr>";
		echo "<tr><td colspan=8 align=right>Gesamt Netto errechnet</td><td id=\"g_netto_errechnet\"></td></tr>";
		echo "<tr><td colspan=8 align=right>Gesamt Brutto errechnet</td><td id=\"g_brutto_errechnet\"></td></tr>";
		echo "<tr><td colspan=8 align=right>Durchschnittsrabatt im Pool</td><td id=\"durchschnitt_rabatt\"></td></tr>";
		echo "</table>";
		$form->ende_formular ();
	}
	function mwst_satz_der_position($belegnr, $position) {
		$result = mysql_query ( "SELECT MWST_SATZ FROM RECHNUNGEN_POSITIONEN WHERE BELEG_NR='$belegnr' && POSITION='$position' && AKTUELL='1' ORDER BY RECHNUNGEN_POS_DAT DESC LIMIT 0,1" );
		$row = mysql_fetch_assoc ( $result );
		return $row ['MWST_SATZ'];
	}
	function objekte_im_pool() {
		$einheiten_im_pool = $this->pool_durchsuchen ( 'Einheit' );
		$haus_im_pool = $this->pool_durchsuchen ( 'Haus' );
		$objekte_im_pool = $this->pool_durchsuchen ( 'Objekt' );
		
		$einheit_info = new einheit ();
		for($a = 0; $a < count ( $einheiten_im_pool ); $a ++) {
			$einheit_id = $einheiten_im_pool [$a] [KOSTENTRAEGER_ID];
			$einheit_info->get_einheit_haus ( $einheit_id );
			$kostentraeger_id = $einheit_info->objekt_id;
			$objekte [] = $kostentraeger_id;
		}
		/* Doppelte entfernen */
		$objekte = array_unique ( $objekte );
		
		/* Häuser */
		$haus_info = new haus ();
		for($a = 0; $a < count ( $haus_im_pool ); $a ++) {
			$haus_id = $haus_im_pool [$a] [KOSTENTRAEGER_ID];
			$haus_info->get_haus_info ( $haus_id );
			$kostentraeger_id = $haus_info->objekt_id;
			$objekte [] = $kostentraeger_id;
		}
		/* Doppelte entfernen */
		$objekte = array_unique ( $objekte );
		
		/* Objekte */
		for($a = 0; $a < count ( $objekte_im_pool ); $a ++) {
			$kostentraeger_id = $objekte_im_pool [$a] [KOSTENTRAEGER_ID];
			$objekte [] = $kostentraeger_id;
		}
		/* Doppelte entfernen */
		$objekte_im_pool = array_unique ( $objekte );
		
		foreach ( $objekte_im_pool as $key => $value ) {
			$objekte_sortiert [] = $value;
		}
		
		/*
		 * echo "<pre>";
		 * print_r($objekte_sortiert);
		 * echo "</pre>";
		 */
		return $objekte_sortiert;
	}
	
	/* Häuser_ids eines Objekt holen */
	function haeuser_vom_objekt_im_pool($objekt_id) {
		$haus_im_pool = $this->pool_durchsuchen ( 'Haus' );
		/* Häuser */
		$haus_info = new haus ();
		for($a = 0; $a < count ( $haus_im_pool ); $a ++) {
			$haus_id = $haus_im_pool [$a] [KOSTENTRAEGER_ID];
			$haus_info->get_haus_info ( $haus_id );
			$kostentraeger_id = $haus_info->objekt_id;
			if ($kostentraeger_id == $objekt_id) {
				$haeuser_arr [] = $haus_id;
			}
		}
		/* Doppelte entfernen */
		if (is_array ( $haeuser_arr )) {
			$haeuser_arr = array_unique ( $haeuser_arr );
			foreach ( $haeuser_arr as $key => $value ) {
				$haeuser_arr_sortiert [] = $value;
			}
			return $haeuser_arr_sortiert;
		}
		/*
		 * echo "<pre>";
		 * print_r($haeuser_arr_sortiert);
		 * echo "</pre>";
		 */
	}
	
	/* Einheiten_ids eines Objekt holen */
	function einheiten_vom_objekt_im_pool($objekt_id) {
		$einheiten_im_pool = $this->pool_durchsuchen ( 'Einheit' );
		/* Einheiten */
		$einheit_info = new einheit ();
		for($a = 0; $a < count ( $einheiten_im_pool ); $a ++) {
			$einheit_id = $einheiten_im_pool [$a] [KOSTENTRAEGER_ID];
			$einheit_info->get_einheit_haus ( $einheit_id );
			$kostentraeger_id = $einheit_info->objekt_id;
			if ($kostentraeger_id == $objekt_id) {
				$einheiten_arr [] = $einheit_id;
			}
		}
		/* Doppelte entfernen */
		if (is_array ( $einheiten_arr )) {
			$einheiten_arr = array_unique ( $einheiten_arr );
			foreach ( $einheiten_arr as $key => $value ) {
				$einheiten_arr_sortiert [] = $value;
			}
			return $einheiten_arr_sortiert;
		}
	}
	function elemente_im_pool_baum() {
		$einheiten_im_pool = $this->pool_durchsuchen ( 'Einheit' );
		$haus_im_pool = $this->pool_durchsuchen ( 'Haus' );
		$objekte_im_pool = $this->pool_durchsuchen ( 'Objekt' );
		/* Lager ids zum neuer Array hinzu, danach dopplete löschen */
		$lager_im_pool = $this->pool_durchsuchen ( 'Lager' );
		if (is_array ( $lager_im_pool )) {
			for($a = 0; $a < count ( $lager_im_pool ); $a ++) {
				$lager_id = $lager_im_pool [$a] ['KOSTENTRAEGER_ID'];
				$elemente ['LAGER'] [] = $lager_id;
			}
			/* Doppelte entfernen */
			if (is_array ( $elemente ['LAGER'] )) {
				$elemente ['LAGER'] = array_unique ( $elemente ['LAGER'] );
				foreach ( $elemente ['LAGER'] as $key => $value ) {
					$elemente_sortiert ['LAGER'] [] = $value;
				}
			}
		} // end if
		  
		// echo "<pre>";
		  // print_r($lager_im_pool);
		/* Partner oder Mieter */
		$partner_im_pool = $this->pool_durchsuchen ( 'Partner' );
		if (is_array ( $partner_im_pool )) {
			for($a = 0; $a < count ( $partner_im_pool ); $a ++) {
				$partner_id = $partner_im_pool [$a] ['KOSTENTRAEGER_ID'];
				$elemente ['PARTNER'] [] = $partner_id;
			}
			/* Doppelte entfernen */
			if (is_array ( $elemente ['PARTNER'] )) {
				$elemente ['PARTNER'] = array_unique ( $elemente ['PARTNER'] );
				foreach ( $elemente ['PARTNER'] as $key => $value ) {
					$elemente_sortiert ['PARTNER'] [] = $value;
				}
			}
		} // end if
		
		/* Einheiten Häuser Objekte anhand von Einheitszugehörigkeit */
		$einheit_info = new einheit ();
		for($a = 0; $a < count ( $einheiten_im_pool ); $a ++) {
			$einheit_id = $einheiten_im_pool [$a] ['KOSTENTRAEGER_ID'];
			$einheit_info->get_einheit_haus ( $einheit_id );
			$objekt_id = $einheit_info->objekt_id;
			$haus_id = $einheit_info->haus_id;
			$elemente ['OBJEKTE'] [] = $objekt_id;
			$elemente ['HAUS'] [] = $haus_id;
			$elemente ['EINHEITEN'] [] = $einheit_id;
		}
		/* Doppelte entfernen */
		/*
		 * if(is_array($elemente)){
		 * $elemente[OBJEKTE] = array_unique($elemente[OBJEKTE]);
		 * $elemente[HAUS] = array_unique($elemente[HAUS]);
		 * $elemente[EINHEITEN] = array_unique($elemente[EINHEITEN]);
		 * }
		 * /*Häuser
		 */
		$haus_info = new haus ();
		for($a = 0; $a < count ( $haus_im_pool ); $a ++) {
			$haus_id = $haus_im_pool [$a] ['KOSTENTRAEGER_ID'];
			$haus_info->get_haus_info ( $haus_id );
			$objekt_id = $haus_info->objekt_id;
			$elemente ['OBJEKTE'] [] = $objekt_id;
			$elemente ['HAUS'] [] = $haus_id;
		}
		
		/* Doppelte entfernen */
		/*
		 * if(is_array($elemente)){
		 * $elemente[OBJEKTE] = array_unique($elemente[OBJEKTE]);
		 * $elemente[HAUS] = array_unique($elemente[HAUS]);
		 * $elemente[EINHEITEN] = array_unique($elemente[EINHEITEN]);
		 * }
		 * /*Objekte
		 */
		for($a = 0; $a < count ( $objekte_im_pool ); $a ++) {
			$objekt_id = $objekte_im_pool [$a] ['KOSTENTRAEGER_ID'];
			$elemente ['OBJEKTE'] [] = $objekt_id;
		}
		// print_r($elemente);
		if (is_array ( $elemente )) {
			/* Doppelte entfernen */
			if (isset ( $elemente ['OBJEKTE'] )) {
				$elemente ['OBJEKTE'] = array_unique ( $elemente ['OBJEKTE'] );
				foreach ( $elemente ['OBJEKTE'] as $key => $value ) {
					$elemente_sortiert ['OBJEKTE'] [] = $value;
				}
			}
			if (isset ( $elemente ['HAUS'] )) {
				$elemente ['HAUS'] = array_unique ( $elemente ['HAUS'] );
				foreach ( $elemente ['HAUS'] as $key => $value ) {
					$elemente_sortiert ['HAUS'] [] = $value;
				}
			}
			if (isset ( $elemente ['EINHEITEN'] )) {
				$elemente ['EINHEITEN'] = array_unique ( $elemente ['EINHEITEN'] );
				foreach ( $elemente ['EINHEITEN'] as $key => $value ) {
					$elemente_sortiert ['EINHEITEN'] [] = $value;
				}
			}
		}
		
		// echo "<pre>";
		// print_r($elemente_sortiert);
		// echo "</pre>";
		if (isset ( $elemente ['OBJEKTE'] ) or isset ( $elemente ['HAUS'] ) or isset ( $elemente ['EINHEITEN'] ) or isset ( $elemente ['LAGER'] ) or isset ( $elemente ['PARTNER'] )) {
			return $elemente_sortiert;
		}  // end if is_array $elemente
else {
			echo "Keine objektbezogene Daten im Pool";
		}
	}
	
	/* Kontierungspool array nach Austellern filtern */
	function filtern_nach_austeller($kontierung_id_arr, $aussteller_typ, $aussteller_id) {
		for($a = 0; $a < count ( $kontierung_id_arr ); $a ++) {
			$beleg_nr = $kontierung_id_arr [$a] ['BELEG_NR'];
			$this->rechnung_grunddaten_holen ( $beleg_nr );
			/* Empfänger der Rechnung wird zum Austeller der Auto...Rechnung */
			$rechnungs_empfaenger_id = $this->rechnungs_empfaenger_id;
			if ($aussteller_id == $rechnungs_empfaenger_id && $aussteller_typ == $this->rechnungs_empfaenger_typ) {
				$neuer_kontierungs_array [] = $kontierung_id_arr [$a];
			} // end if
		} // end for
		
		return $neuer_kontierungs_array;
	}
	function rechnung_auf_positionen_pruefen($belegnr) {
		$result = mysql_query ( "SELECT * FROM RECHNUNGEN_POSITIONEN WHERE BELEG_NR='$belegnr' && AKTUELL='1'" );
		$numrows = mysql_numrows ( $result );
		return $numrows;
	}
	function rechnung_speichern($clean_arr) {
		// echo '<pre>';
		// print_r($clean_arr);
		$rechnungs_aussteller_typ = $clean_arr [aussteller_typ];
		$rechnungs_aussteller_id = $clean_arr [aussteller_id];
		$rechnungs_empfaenger_typ = $clean_arr [empfaenger_typ];
		$rechnungs_empfaenger_id = $clean_arr [empfaenger_id];
		
		if ($rechnungs_empfaenger_id == $rechnungs_aussteller_id && $rechnungs_empfaenger_typ == $rechnungs_aussteller_typ) {
			$rechnungs_typ_druck = 'Buchungsbeleg';
		} else {
			$rechnungs_typ_druck = 'Rechnung';
		}
		
		$datum_arr = explode ( '.', $clean_arr [rechnungsdatum] );
		$jahr = $datum_arr [2];
		
		if (empty ( $clean_arr ['rechnungsnummer'] )) {
			
			$letzte_aussteller_rnr = $this->letzte_aussteller_ausgangs_nr ( $rechnungs_aussteller_id, $rechnungs_aussteller_typ, $jahr, $rechnungs_typ_druck );
			$letzte_aussteller_rnr = $letzte_aussteller_rnr + 1;
			$letzte_aussteller_rnr = sprintf ( '%03d', $letzte_aussteller_rnr );
			$this->rechnungs_kuerzel = $this->rechnungs_kuerzel_ermitteln ( $rechnungs_aussteller_typ, $rechnungs_aussteller_id, $clean_arr [rechnungsdatum] );
			$rechnungsnummer = $this->rechnungs_kuerzel . ' ' . $letzte_aussteller_rnr . '-' . $jahr;
		} else {
			$letzte_aussteller_rnr = $this->letzte_aussteller_ausgangs_nr ( $rechnungs_aussteller_id, $rechnungs_aussteller_typ, $jahr, $rechnungs_typ_druck );
			$letzte_aussteller_rnr = $letzte_aussteller_rnr + 1;
			$rechnungsnummer = $clean_arr ['rechnungsnummer'];
		}
		
		/* Prüfen ob Rechnung vorhanden */
		
		$rechnungsdatum = date_german2mysql ( $clean_arr [rechnungsdatum] );
		$result_3 = mysql_query ( "SELECT * FROM RECHNUNGEN WHERE RECHNUNGSNUMMER = '$clean_arr[rechnungsnummer]' && RECHNUNGSDATUM = '$rechnungsdatum' && AUSSTELLER_TYP='$rechnungs_aussteller_typ' && AUSSTELLER_ID='$rechnungs_aussteller_id' && EMPFAENGER_TYP='$rechnungs_empfaenger_typ' && EMPFAENGER_ID='$rechnungs_empfaenger_id' && AKTUELL = '1'" );
		$numrows_3 = mysql_numrows ( $result_3 );
		
		if ($numrows_3 > 0) {
			$partner_info = new partner ();
			$von = $partner_info->get_partner_name ( $rechnungs_aussteller_id );
			fehlermeldung_ausgeben ( "Rechnung von $von mit der Rechnungsnummer $clean_arr[rechnungsnummer] vom $clean_arr[rechnungsdatum] existiert bereits." );
			die ();
		} else {
			/* Letzte Belegnummer holen */
			
			$letzte_belegnr = $this->letzte_beleg_nr ();
			$letzte_belegnr = $letzte_belegnr + 1;
			/* Letzte Rechnungsid holen */
			
			/* Rechnungsdaten speichern */
			$rechnungsdatum = date_german2mysql ( $clean_arr [rechnungsdatum] );
			$eingangsdatum = date_german2mysql ( $clean_arr [eingangsdatum] );
			$faellig_am = date_german2mysql ( $clean_arr [faellig_am] );
			$kurzbeschreibung = $clean_arr [kurzbeschreibung];
			$netto_betrag = nummer_komma2punkt ( $clean_arr [nettobetrag] );
			$brutto_betrag = $clean_arr [bruttobetrag];
			$brutto_betrag = nummer_komma2punkt ( $brutto_betrag );
			
			$rechnungs_typ = $rechnungs_typ_druck;
			
			$letzte_empfaenger_rnr = $this->letzte_empfaenger_eingangs_nr ( $rechnungs_empfaenger_id, $rechnungs_empfaenger_typ, $jahr, $rechnungs_typ_druck );
			$letzte_empfaenger_rnr = $letzte_empfaenger_rnr + 1;
			
			if ($rechnungs_empfaenger_typ == 'Kasse') {
				$status_bezahlt = '1';
				$bezahlt_am = $eingangsdatum;
			} else {
				$status_bezahlt = '0';
				$bezahlt_am = '0000-00-00';
			}
			
			$db_abfrage = "INSERT INTO RECHNUNGEN VALUES (NULL, '$letzte_belegnr', '$rechnungsnummer', '$letzte_aussteller_rnr', '$letzte_empfaenger_rnr', '$rechnungs_typ', '$rechnungsdatum','$eingangsdatum', '$netto_betrag','$brutto_betrag','0.00', '$rechnungs_aussteller_typ', '$rechnungs_aussteller_id','$rechnungs_empfaenger_typ', '$rechnungs_empfaenger_id','1', '1', '0', '0', '1', '$status_bezahlt', '0', '$faellig_am', '$bezahlt_am', '$kurzbeschreibung', '$clean_arr[geld_konto]')";
			$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
			
			/* Protokollieren */
			$last_dat = mysql_insert_id ();
			protokollieren ( 'RECHNUNGEN', $last_dat, '0' );
			/* Ausgabe weil speichern erfolgreich */
			echo "Rechnung/Beleg $letzte_belegnr wurde erfasst.";
			
			if ($rechnungs_empfaenger_typ == 'Kasse') {
				$kasse = new kasse ();
				$kassen_id = $rechnungs_empfaenger_id;
				$datum = date_mysql2german ( $eingangsdatum );
				$kasse->rechnung_in_kassenbuch ( $kassen_id, $brutto_betrag, $datum, 'Ausgaben', $kurzbeschreibung, 'Rechnung', $letzte_belegnr );
			}
			
			/* Weiterleiten auf die Rechnungserfassung */
			
			// weiterleiten_in_sec("?daten=rechnungen&option=rechnung_erfassen", 2); // Rechnungsliste
			weiterleiten_in_sec ( "?daten=rechnungen&option=positionen_erfassen&belegnr=" . $letzte_belegnr . "", 2 );
			// Positionseingabe
			// weiterleiten_in_sec("?daten=rechnungen&option=lieferschein_erfassen&beleg_nr=".$letzte_belegnr."", 2);//Positionseingabe
		}
	}
	
	/* automatisch erstellte rechnung speichern */
	function auto_rechnung_speichern($clean_arr) {
		/*
		 * echo "<pre>";
		 * print_r($clean_arr);
		 * echo "</pre>";
		 */
		$r_e_id = $clean_arr ['RECHNUNG_EMPFAENGER_ID'];
		// #######################
		if ($clean_arr [RECHNUNG_EMPFAENGER_TYP] == 'Partner') {
			$this->rechnungs_empfaenger_typ = 'Partner';
			$this->rechnungs_empfaenger_id = $r_e_id;
		}
		if ($clean_arr [RECHNUNG_EMPFAENGER_TYP] == 'Objekt') {
			$this->rechnungs_empfaenger_typ = 'Partner';
			$this->rechnungs_empfaenger_id = $this->eigentuemer_ermitteln ( 'Objekt', $r_e_id );
		}
		if ($clean_arr [RECHNUNG_EMPFAENGER_TYP] == 'Haus') {
			$this->rechnungs_empfaenger_typ = 'Partner';
			$this->rechnungs_empfaenger_id = $this->eigentuemer_ermitteln ( 'Haus', $r_e_id );
		}
		if ($clean_arr [RECHNUNG_EMPFAENGER_TYP] == 'Einheit') {
			$this->rechnungs_empfaenger_typ = 'Partner';
			$this->rechnungs_empfaenger_id = $this->eigentuemer_ermitteln ( 'Einheit', $r_e_id );
		}
		
		if ($clean_arr [RECHNUNG_EMPFAENGER_TYP] == 'Lager') {
			$this->rechnungs_empfaenger_typ = 'Lager';
			$this->rechnungs_empfaenger_id = $r_e_id;
		}
		
		if ($clean_arr [RECHNUNG_EMPFAENGER_TYP] == 'Kasse') {
			/* Kassen Partner finden */
			$kasse = new kasse ();
			$kasse->get_kassen_info ( $r_e_id );
			$this->rechnungs_empfaenger_typ = 'Kasse';
			$this->rechnungs_empfaenger_id = $kasse->kassen_partner_id;
		}
		
		// #######################
		
		$this->rechnungs_aussteller_typ = $clean_arr ['RECHNUNG_AUSSTELLER_TYP'];
		$this->rechnungs_aussteller_id = $clean_arr ['RECHNUNG_AUSSTELLER_ID'];
		
		/* Wenn Austeller = Empfänger - GmbH an Gmbh = Buchungsbeleg */
		if ($this->rechnungs_empfaenger_id == $this->rechnungs_aussteller_id && $this->rechnungs_aussteller_typ == $this->rechnungs_empfaenger_typ) {
			$this->rechnungs_typ_druck = 'Buchungsbeleg';
		} else {
			$this->rechnungs_typ_druck = 'Rechnung';
		}
		
		$rechnungsdatum = $clean_arr ['RECHNUNGSDATUM'];
		$datum_arr = explode ( '.', $rechnungsdatum );
		$jahr = $datum_arr [2];
		$rechnungsdatum_sql = date_german2mysql ( $rechnungsdatum );
		/* Ausgangsnr */
		$letzte_aussteller_rnr = $this->letzte_aussteller_ausgangs_nr ( $this->rechnungs_aussteller_id, $this->rechnungs_aussteller_typ, $jahr, $this->rechnungs_typ_druck );
		$letzte_aussteller_rnr = $letzte_aussteller_rnr + 1;
		$letzte_aussteller_rnr1 = sprintf ( '%03d', $letzte_aussteller_rnr );
		/* Kürzel */
		$this->rechnungs_kuerzel = $this->rechnungs_kuerzel_ermitteln ( $this->rechnungs_aussteller_typ, $this->rechnungs_aussteller_id, $rechnungsdatum_sql );
		
		$rechnungsnummer = $this->rechnungs_kuerzel . ' ' . $letzte_aussteller_rnr1 . '-' . $jahr;
		
		// echo "<h1> $rechnunsgnummer $this->rechnungs_kuerzel $letzte_aussteller_rnr</h1>";
		
		/* Prüfen ob Rechnung vorhanden */
		$check_rechnung = $this->check_rechnung_vorhanden ( $rechnungsnummer, $rechnungsdatum_sql, $this->rechnungs_aussteller_typ, $this->rechnungs_aussteller_id, $this->rechnungs_empfaenger_typ, $this->rechnungs_empfaenger_id, $this->rechnungs_typ_druck );
		
		/* Wenn rechnung existiert */
		if ($check_rechnung) {
			$partner_info = new partner ();
			$von = $partner_info->get_partner_name ( $this->rechnungs_aussteller_id );
			fehlermeldung_ausgeben ( "$this->rechnungs_typ_druck von $von mit der Nummer $this->rechnungs_typ_druck  $rechnungsnummer vom $rechnungsdatum existiert bereits." );
			die ();
		} else {
			
			/* Rechnungsdaten speichern */
			$eingangsdatum = $rechnungsdatum_sql;
			$faellig_am = date_german2mysql ( $clean_arr ['RECHNUNG_FAELLIG_AM'] );
			$kurzbeschreibung = $clean_arr ['kurzbeschreibung'];
			
			$netto_betrag = $clean_arr ['nettobetrag'];
			$brutto_betrag = $clean_arr ['bruttobetrag'];
			
			// $skonto = $clean_arr[skonto];
			// $skonto = nummer_komma2punkt($skonto);
			
			// $skonto_betrag = ($brutto_betrag/100) * (100-$skonto);
			// $skonto_betrag = nummer_komma2punkt($skonto_betrag);
			$letzte_empfaenger_rnr = $this->letzte_empfaenger_eingangs_nr ( $this->rechnungs_empfaenger_id, $this->rechnungs_empfaenger_typ, $jahr, $this->rechnungs_typ_druck );
			$letzte_empfaenger_rnr = $letzte_empfaenger_rnr + 1;
			
			$empfangs_geld_konto = $clean_arr ['EMPFANGS_GELD_KONTO'];
			
			/* Sonst Letzte Belegnummer holen */
			$letzte_belegnr = $this->letzte_beleg_nr ();
			$letzte_belegnr = $letzte_belegnr + 1;
			
			$db_abfrage = "INSERT INTO RECHNUNGEN VALUES (NULL, '$letzte_belegnr', '$rechnungsnummer', '$letzte_aussteller_rnr', '$letzte_empfaenger_rnr', '$this->rechnungs_typ_druck', '$rechnungsdatum_sql','$eingangsdatum', '$netto_betrag','$brutto_betrag','$skonto_betrag', '$this->rechnungs_aussteller_typ', '$this->rechnungs_aussteller_id','$this->rechnungs_empfaenger_typ', '$this->rechnungs_empfaenger_id','1', '1', '1', '0', '1', '0', '0', '$faellig_am', '0000-00-00', '$kurzbeschreibung', '$empfangs_geld_konto')";
			$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
			/* Protokollieren */
			$last_dat = mysql_insert_id ();
			protokollieren ( 'RECHNUNGEN', $last_dat, '0' );
			/* Ausgabe weil speichern erfolgreich */
			echo "$this->rechnungs_typ_druck $letzte_belegnr wurde erfasst.";
			/* Weiterleiten auf die Rechnungserfassung */
			
			if ($this->rechnungs_empfaenger_typ == 'Kasse') {
				$kasse = new kasse ();
				$kassen_id = $clean_arr [$this->rechnungs_empfaenger_id];
				$datum = date_mysql2german ( $eingangsdatum );
				$kasse->speichern_in_kassenbuch ( $kassen_id, $brutto_betrag, $datum, 'Ausgaben', $kurzbeschreibung, 'Rechnung', $letzte_belegnr );
			}
			
			return $letzte_belegnr;
		}
	}
	function check_rechnung_vorhanden($rechnungsnummer, $rechnungsdatum, $aussteller_typ, $aussteller_id, $empfaenger_typ, $empfaenger_id, $rechnungs_typ) {
		$result_3 = mysql_query ( "SELECT * FROM RECHNUNGEN WHERE RECHNUNGSNUMMER = '$rechnungsnummer' && RECHNUNGSDATUM = '$rechnungsdatum' && AUSSTELLER_TYP='$aussteller_typ' && AUSSTELLER_ID='$aussteller_id' && EMPFAENGER_TYP='$empfaenger_typ' && EMPFAENGER_ID='$empfaenger_id' && AKTUELL = '1' && RECHNUNGSTYP='$rechnungs_typ'" );
		$numrows = mysql_numrows ( $result_3 );
		
		if ($numrows) {
			return true;
		} else {
			return false;
		}
	}
	
	/* automatisch erstellte rechnung speichern */
	function auto_rechnung_speichern_alt($clean_arr) {
		echo "<pre>";
		print_r ( $clean_arr );
		echo "</pre>";
		
		// #######################
		if ($clean_arr [Empfaenger_typ] == 'Objekt') {
			$this->rechnungs_empfaenger_typ = 'Partner';
		}
		if ($clean_arr [Empfaenger_typ] == 'Haus') {
			$this->rechnungs_empfaenger_typ = 'Partner';
		}
		if ($clean_arr [Empfaenger_typ] == 'Einheit') {
			$this->rechnungs_empfaenger_typ = 'Partner';
		}
		if ($clean_arr [Empfaenger_typ] == 'Lager') {
			$this->rechnungs_empfaenger_typ = 'Lager';
		}
		// #######################
		
		$this->rechnungs_aussteller_typ = $clean_arr [Aussteller_typ];
		$this->rechnungs_aussteller_id = $clean_arr [Aussteller_id];
		$this->rechnungs_empfaenger_id = $clean_arr [Empfaenger_id];
		
		if ($this->rechnungs_aussteller_typ == 'Partner') {
			$this->rechnung_aussteller_partner_id = $this->rechnungs_aussteller_id;
		}
		
		if ($this->rechnungs_empfaenger_typ == 'Partner') {
			$this->rechnung_empfaenger_partner_id = $this->rechnungs_empfaenger_id;
		}
		
		if ($this->rechnungs_aussteller_typ == 'Lager') {
			$lager_info = new lager ();
			/*
			 * Liefert Lagernamen und Partner id
			 * $lager_info->lager_name
			 * $lager_info->lager_partner_id
			 */
			$lager_info->lager_name_partner ( $this->rechnungs_aussteller_id );
			$this->rechnung_aussteller_partner_id = $lager_info->lager_partner_id;
		}
		
		if ($this->rechnungs_empfaenger_typ == 'Lager') {
			$lager_info1 = new lager ();
			/*
			 * Liefert Lagernamen und Partner id
			 * $lager_info->lager_name
			 * $lager_info->lager_partner_id
			 */
			$lager_info1->lager_name_partner ( $this->rechnungs_empfaenger_id );
			$this->rechnung_empfaenger_partner_id = $lager_info1->lager_partner_id;
		}
		
		if ($this->rechnungs_aussteller_typ == 'Kasse') {
			/* Kassennamen holen */
			$kassen_info = new kasse ();
			$kassen_info->get_kassen_info ( $this->rechnungs_aussteller_id );
			/* Kassen Partner finden */
			$this->rechnung_aussteller_partner_id = $kassen_info->kassen_partner_id;
		}
		
		if ($this->rechnungs_empfaenger_typ == 'Kasse') {
			/* Kassennamen holen */
			$kassen_info = new kasse ();
			$kassen_info->get_kassen_info ( $this->rechnungs_empfaenger_id );
			/* Kassen Partner finden */
			$this->rechnung_empfaenger_partner_id = $kassen_info->kassen_partner_id;
		}
		
		if ($this->rechnung_empfaenger_partner_id == $this->rechnung_aussteller_partner_id) {
			$this->rechnungs_typ_druck = 'Buchungsbeleg';
		} else {
			$this->rechnungs_typ_druck = 'Rechnung';
		}
		echo "$this->rechnung_empfaenger_partner_id == $this->rechnung_aussteller_partner_id";
		
		$datum_arr = explode ( '.', $clean_arr [rechnungsdatum] );
		$jahr = $datum_arr [2];
		if (empty ( $clean_arr ['rechnungsnummer'] )) {
			$letzte_aussteller_rnr = $this->letzte_aussteller_ausgangs_nr ( $clean_arr ['Aussteller_id'], $clean_arr ['Aussteller_typ'], $jahr, $this->rechnungs_typ_druck );
			$letzte_aussteller_rnr = $letzte_aussteller_rnr + 1;
			$letzte_aussteller_rnr1 = sprintf ( '%03d', $letzte_aussteller_rnr );
			$this->rechnungs_kuerzel = $this->rechnungs_kuerzel_ermitteln ( $clean_arr ['Aussteller_typ'], $clean_arr ['Aussteller_id'], $clean_arr [rechnungsdatum] );
			$rechnungsnummer = $this->rechnungs_kuerzel . ' ' . $letzte_aussteller_rnr1 . '-' . $jahr;
		} else {
			$letzte_aussteller_rnr = $this->letzte_aussteller_ausgangs_nr ( $clean_arr [Aussteller_id], $clean_arr [Aussteller_typ], $jahr, $this->rechnungs_typ_druck );
			$letzte_aussteller_rnr = $letzte_aussteller_rnr + 1;
			$rechnungsnummer = $clean_arr ['rechnungsnummer'];
		}
		
		// #######################
		if ($clean_arr [Empfaenger_typ] == 'Objekt') {
			$clean_arr [Empfaenger_typ] = 'Partner';
		}
		if ($clean_arr [Empfaenger_typ] == 'Haus') {
			$clean_arr [Empfaenger_typ] = 'Partner';
		}
		if ($clean_arr [Empfaenger_typ] == 'Einheit') {
			$clean_arr [Empfaenger_typ] = 'Partner';
		}
		// #######################
		
		/* Prüfen ob Rechnung vorhanden */
		$rechnungsdatum = date_german2mysql ( $clean_arr [rechnungsdatum] );
		$result_3 = mysql_query ( "SELECT * FROM RECHNUNGEN WHERE RECHNUNGSNUMMER = '$clean_arr[rechnungsnummer]' && RECHNUNGSDATUM = '$rechnungsdatum' && AUSSTELLER_TYP='$clean_arr[Aussteller_typ]' && AUSSTELLER_ID='$clean_arr[Aussteller]' && EMPFAENGER_TYP='$clean_arr[Empfaenger_typ]' && EMPFAENGER_ID='$clean_arr[Empfaenger]' && AKTUELL = '1' && RECHNUNGSTYP='$this->rechnungs_typ_druck'" );
		$numrows_3 = mysql_numrows ( $result_3 );
		
		if ($numrows_3 > 0) {
			$partner_info = new partner ();
			$von = $partner_info->get_partner_name ( $clean_arr [Aussteller] );
			fehlermeldung_ausgeben ( "$this->rechnungs_typ_druck von $von mit der $this->rechnungs_typ_druck.snummer $clean_arr[rechnungsnummer] vom $clean_arr[rechnungsdatum] existiert bereits." );
			die ();
		} else {
			/* Letzte Belegnummer holen */
			$letzte_belegnr = $this->letzte_beleg_nr ();
			$letzte_belegnr = $letzte_belegnr + 1;
			/* Letzte Rechnungsid holen */
			if (empty ( $clean_arr [empfaenger_typ] )) {
				$clean_arr [empfaenger_typ] = 'Partner';
			}
			
			/* Rechnungsdaten speichern */
			$rechnungsdatum = date_german2mysql ( $clean_arr [rechnungsdatum] );
			// $eingangsdatum = date_german2mysql($clean_arr[eingangsdatum]);
			$eingangsdatum = $rechnungsdatum;
			$faellig_am = date_german2mysql ( $clean_arr [faellig_am] );
			$kurzbeschreibung = $clean_arr [kurzbeschreibung];
			$netto_betrag = nummer_komma2punkt ( $clean_arr [netto_betrag] );
			$brutto_betrag = $clean_arr [brutto_betrag];
			$brutto_betrag = number_format ( $brutto_betrag, 2, ".", "" );
			$skonto = $clean_arr [skonto];
			$skonto = nummer_komma2punkt ( $skonto );
			// $skonto_betrag = $clean_arr[skonto_betrag];
			// $skonto_betrag = ($brutto_betrag/100) * (100-$skonto);
			// $skonto_betrag = nummer_komma2punkt($skonto_betrag);
			$letzte_empfaenger_rnr = $this->letzte_empfaenger_eingangs_nr ( $clean_arr [Empfaenger_id], $clean_arr [Empfaenger_typ], $jahr, $this->rechnungs_typ_druck );
			$letzte_empfaenger_rnr = $letzte_empfaenger_rnr + 1;
			
			/*
			 * if($clean_arr[Aussteller_typ] == 'Lager'){
			 * $rechnungstyp = 'Rechnung';
			 * }else{
			 * $rechnungstyp = 'Rechnung';
			 * }
			 */
			
			$db_abfrage = "INSERT INTO RECHNUNGEN VALUES (NULL, '$letzte_belegnr', '$rechnungsnummer', '$letzte_aussteller_rnr', '$letzte_empfaenger_rnr', '$this->rechnungs_typ_druck', '$rechnungsdatum','$eingangsdatum', '$netto_betrag','$brutto_betrag','$brutto_betrag', '$skonto', '$clean_arr[Aussteller_typ]', '$clean_arr[Aussteller_id]','$clean_arr[Empfaenger_typ]', '$clean_arr[Empfaenger_id]','1', '1', '1', '0', '1', '0', '0', '$faellig_am', '0000-00-00', '$kurzbeschreibung', '$clean_arr[empfangs_geld_konto]')";
			$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
			/* Protokollieren */
			$last_dat = mysql_insert_id ();
			protokollieren ( 'RECHNUNGEN', $last_dat, '0' );
			/* Ausgabe weil speichern erfolgreich */
			echo "$this->rechnungs_typ_druck $letzte_belegnr wurde erfasst.";
			/* Weiterleiten auf die Rechnungserfassung */
			
			if ($clean_arr [Empfaenger_typ] == 'Kasse') {
				$kasse = new kasse ();
				$kassen_id = $clean_arr [Empfaenger_id];
				$datum = date_mysql2german ( $eingangsdatum );
				$kasse->speichern_in_kassenbuch ( $kassen_id, $brutto_betrag, $datum, 'Ausgaben', $kurzbeschreibung, 'Rechnung', $letzte_belegnr );
			}
			
			weiterleiten_in_sec ( "?daten=rechnungen&option=rechnungs_uebersicht&belegnr=" . $letzte_belegnr . "", 2 );
			return $letzte_belegnr;
		}
	}
	
	/* Letzte Rechnung ID */
	function letzte_rechnung_id($empfaenger_id, $typ) {
		$result = mysql_query ( "SELECT RECHNUNG_ID FROM RECHNUNGEN WHERE EMPFAENGER_ID='$empfaenger_id' && EMPFAENGER_TYP='$typ' ORDER BY RECHNUNG_ID DESC LIMIT 0,1" );
		$row = mysql_fetch_assoc ( $result );
		return $row [RECHNUNG_ID];
	}
	
	/* Letzte Rechnung ID */
	function letzte_aussteller_ausgangs_nr($aussteller_id, $typ, $jahr, $rechnungs_typ) {
		if ($rechnungs_typ == 'Rechnung' or $rechnungs_typ == 'Stornorechnung' or $rechnungs_typ == 'Gutschrift' or $rechnungs_typ == 'Schlussrechnung' or $rechnungs_typ == 'Teilrechnung') {
			$rechnungs_typ == 'Rechnung';
			$result = mysql_query ( "SELECT AUSTELLER_AUSGANGS_RNR FROM RECHNUNGEN WHERE AUSSTELLER_ID='$aussteller_id' && AUSSTELLER_TYP='$typ' && RECHNUNGSDATUM BETWEEN '$jahr-01-01' AND '$jahr-12-31' && (RECHNUNGSTYP='$rechnungs_typ' OR RECHNUNGSTYP='Gutschrift' OR RECHNUNGSTYP='Stornorechnung'  OR RECHNUNGSTYP='Schlussrechnung'  OR RECHNUNGSTYP='Teilrechnung') && AKTUELL='1' ORDER BY AUSTELLER_AUSGANGS_RNR DESC LIMIT 0,1" );
		} else {
			$result = mysql_query ( "SELECT AUSTELLER_AUSGANGS_RNR FROM RECHNUNGEN WHERE AUSSTELLER_ID='$aussteller_id' && AUSSTELLER_TYP='$typ' && RECHNUNGSDATUM BETWEEN '$jahr-01-01' AND '$jahr-12-31' && RECHNUNGSTYP='$rechnungs_typ' && AKTUELL='1' ORDER BY AUSTELLER_AUSGANGS_RNR DESC LIMIT 0,1" );
		}
		/*
		 * if($rechnungs_typ == 'Rechnung'){
		 * $result = mysql_query ("SELECT AUSTELLER_AUSGANGS_RNR FROM RECHNUNGEN WHERE AUSSTELLER_ID='$aussteller_id' && AUSSTELLER_TYP='$typ' && RECHNUNGSDATUM BETWEEN '$jahr-01-01' AND '$jahr-12-31' && (RECHNUNGSTYP='$rechnungs_typ' OR RECHNUNGSTYP='Gutschrift') && AKTUELL='1' ORDER BY AUSTELLER_AUSGANGS_RNR DESC LIMIT 0,1");
		 * }else{
		 */
		// $result = mysql_query ("SELECT AUSTELLER_AUSGANGS_RNR FROM RECHNUNGEN WHERE AUSSTELLER_ID='$aussteller_id' && AUSSTELLER_TYP='$typ' && RECHNUNGSDATUM BETWEEN '$jahr-01-01' AND '$jahr-12-31' && RECHNUNGSTYP='$rechnungs_typ' && AKTUELL='1' ORDER BY AUSTELLER_AUSGANGS_RNR DESC LIMIT 0,1");
		
		// }
		
		$row = mysql_fetch_assoc ( $result );
		return $row ['AUSTELLER_AUSGANGS_RNR'];
	}
	
	/* Letzte Rechnung ID */
	function letzte_empfaenger_eingangs_nr($empfaenger_id, $typ, $jahr, $rechnungs_typ) {
		if ($rechnungs_typ == 'Rechnung' or $rechnungs_typ == 'Stornorechnung' or $rechnungs_typ == 'Gutschrift' or $rechnungs_typ == 'Schlussrechnung' or $rechnungs_typ == 'Teilrechnung') {
			$rechnungs_typ == 'Rechnung';
			$result = mysql_query ( "SELECT EMPFAENGER_EINGANGS_RNR FROM RECHNUNGEN WHERE EMPFAENGER_ID='$empfaenger_id' && EMPFAENGER_TYP='$typ' && DATE_FORMAT(RECHNUNGSDATUM, '%Y') = '$jahr' && (RECHNUNGSTYP='$rechnungs_typ' OR RECHNUNGSTYP='Gutschrift' OR RECHNUNGSTYP='Stornorechnung' OR RECHNUNGSTYP='Schlussrechnung'  OR RECHNUNGSTYP='Teilrechnung') && AKTUELL='1' ORDER BY BELEG_NR DESC LIMIT 0,1" );
		} else {
			// $result = mysql_query ("SELECT EMPFAENGER_EINGANGS_RNR FROM RECHNUNGEN WHERE EMPFAENGER_ID='$empfaenger_id' && EMPFAENGER_TYP='$typ' && DATE_FORMAT(RECHNUNGSDATUM, '%Y') = '$jahr' && RECHNUNGSTYP='$rechnungs_typ' && AKTUELL='1' ORDER BY BELEG_NR DESC LIMIT 0,1");
			$result = mysql_query ( "SELECT EMPFAENGER_EINGANGS_RNR FROM RECHNUNGEN WHERE EMPFAENGER_ID='$empfaenger_id' && EMPFAENGER_TYP='$typ' && DATE_FORMAT(RECHNUNGSDATUM, '%Y') = '$jahr' && RECHNUNGSTYP='$rechnungs_typ'  && AKTUELL='1' ORDER BY BELEG_NR DESC LIMIT 0,1" );
		}
		$row = mysql_fetch_assoc ( $result );
		return $row ['EMPFAENGER_EINGANGS_RNR'];
	}
	
	/* Letzte Belegnummer */
	function letzte_beleg_nr() {
		$result = mysql_query ( "SELECT BELEG_NR FROM RECHNUNGEN ORDER BY BELEG_NR DESC LIMIT 0,1" );
		$row = mysql_fetch_assoc ( $result );
		return $row ['BELEG_NR'];
	}
	
	/* Letzte Belegnummer */
	function letzte_beleg_nr_auto($aussteller_id, $empfaenger_id) {
		$result = mysql_query ( "SELECT BELEG_NR FROM RECHNUNGEN WHERE AUSSTELLER_TYP='Partner' && AUSSTELLER_ID='$aussteller_id' && EMPFAENGER_ID='$empfaenger_id' ORDER BY BELEG_NR DESC LIMIT 0,1" );
		$row = mysql_fetch_assoc ( $result );
		return $row [BELEG_NR];
	}
	
	/* Rechnungsgrunddaten holen */
	function rechnung_grunddaten_holen_NOK($belegnr) {
		$result = mysql_query ( "SELECT * FROM RECHNUNGEN WHERE BELEG_NR='$belegnr' && AKTUELL='1' ORDER BY BELEG_NR DESC LIMIT 0,1" );
		$numrows = mysql_numrows ( $result );
		if ($numrows > 0) {
			$row = mysql_fetch_assoc ( $result );
			$this->belegnr = $row [BELEG_NR];
			/* Skontogesamtbetrag updaten */
			$rr = new rechnungen ();
			$rr->update_skontobetrag ( $belegnr );
			$rr->update_nettobetrag ( $belegnr );
			$rr->update_bruttobetrag ( $belegnr );
			
			$this->rechnung_dat = $row [RECHNUNG_DAT];
			
			$this->aussteller_ausgangs_rnr = $row [AUSTELLER_AUSGANGS_RNR];
			$this->empfaenger_eingangs_rnr = $row [EMPFAENGER_EINGANGS_RNR];
			$this->rechnungstyp = $row [RECHNUNGSTYP];
			$this->rechnungsdatum = date_mysql2german ( $row [RECHNUNGSDATUM] );
			$this->eingangsdatum = date_mysql2german ( $row [EINGANGSDATUM] );
			$this->faellig_am = date_mysql2german ( $row [FAELLIG_AM] );
			$this->rechnungsnummer = $row [RECHNUNGSNUMMER];
			$this->rechnungs_netto = $row [NETTO];
			$this->rechnungs_brutto = $row [BRUTTO];
			$this->rechnungs_mwst = $this->rechnungs_brutto - $this->rechnungs_netto;
			
			$this->rechnungs_skontobetrag = $row [SKONTOBETRAG];
			
			$this->rechnungs_skontoabzug = $this->rechnungs_brutto - $this->rechnungs_skontobetrag;
			$this->rechnungs_aussteller_typ = $row [AUSSTELLER_TYP];
			$this->rechnungs_aussteller_id = $row [AUSSTELLER_ID];
			$this->rechnungs_empfaenger_typ = $row [EMPFAENGER_TYP];
			$this->rechnungs_empfaenger_id = $row [EMPFAENGER_ID];
			
			/* Rechnungspartner finden und Rechnungstyp ändern falls Aussteller = Empfänger */
			$this->rechnungs_partner_ermitteln ();
			
			$this->rechnungs_kuerzel = $this->rechnungs_kuerzel_ermitteln ( $this->rechnungs_aussteller_typ, $this->rechnungs_aussteller_id, $row [RECHNUNGSDATUM] );
			$this->rechnungsnummer_kuerzel = $this->rechnungs_kuerzel . $this->aussteller_ausgangs_rnr;
			$this->rechnungs_partner_ermitteln ();
			$this->status_erfasst = $row [STATUS_ERFASST];
			$this->status_vollstaendig = $row [STATUS_VOLLSTAENDIG];
			$this->status_zugewiesen = $row [STATUS_ZUGEWIESEN];
			$this->kurzbeschreibung = $row [KURZBESCHREIBUNG];
			$this->status_bezahlt = $row [STATUS_BEZAHLT];
			$this->status_zahlung_freigegeben = $row [STATUS_ZAHLUNG_FREIGEGEBEN];
			$this->status_bestaetigt = $row [STATUS_BESTAETIGT];
			$this->bezahlt_am = date_mysql2german ( $row [BEZAHLT_AM] );
			$this->empfangs_geld_konto = $row [EMPFANGS_GELD_KONTO];
			
			/* Infos über Positionen */
			$rr->rechnung_auf_positionen_pruefen ( $belegnr );
		} // end if rows>1
	} // end function rechnung_grunddaten_holen
	function rechnung_grunddaten_holen($belegnr) {
		// echo "BERLUSSIMO $belegnr<br>";
		$result = mysql_query ( "SELECT * FROM RECHNUNGEN WHERE BELEG_NR='$belegnr' && AKTUELL='1' ORDER BY BELEG_NR DESC LIMIT 0,1" );
		$numrows = mysql_numrows ( $result );
		if ($numrows > 0) {
			$row = mysql_fetch_assoc ( $result );
			$this->belegnr = $row ['BELEG_NR'];
			/* Skontogesamtbetrag updaten */
			$rr = new rechnungen ();
			$rr->update_skontobetrag ( $belegnr );
			$rr->update_nettobetrag ( $belegnr );
			$rr->update_bruttobetrag ( $belegnr );
			$result = mysql_query ( "SELECT * FROM RECHNUNGEN WHERE BELEG_NR='$belegnr' && AKTUELL='1' ORDER BY BELEG_NR DESC LIMIT 0,1" );
			$row = mysql_fetch_assoc ( $result );
			
			$this->aussteller_ausgangs_rnr = $row ['AUSTELLER_AUSGANGS_RNR'];
			$this->empfaenger_eingangs_rnr = $row ['EMPFAENGER_EINGANGS_RNR'];
			$this->rechnungstyp = $row ['RECHNUNGSTYP'];
			$this->rechnungsdatum = date_mysql2german ( $row ['RECHNUNGSDATUM'] );
			$this->eingangsdatum = date_mysql2german ( $row ['EINGANGSDATUM'] );
			$this->faellig_am = date_mysql2german ( $row ['FAELLIG_AM'] );
			$this->rechnungsnummer = $row ['RECHNUNGSNUMMER'];
			$this->rechnungs_netto = $row ['NETTO'];
			
			$this->rechnungs_brutto = $row ['BRUTTO'];
			
			// die("RB $this->rechnungs_brutto - $this->rechnungs_netto");
			$this->summe_mwst = $this->rechnungs_brutto - $this->rechnungs_netto;
			$this->rechnungs_mwst = $this->summe_mwst;
			$this->rechnungs_skontobetrag = $row ['SKONTOBETRAG'];
			$this->rechnungs_skontoabzug = $this->rechnungs_brutto - $this->rechnungs_skontobetrag;
			$this->rechnungs_aussteller_typ = $row ['AUSSTELLER_TYP'];
			$this->rechnungs_aussteller_id = $row ['AUSSTELLER_ID'];
			$this->rechnungs_empfaenger_typ = $row ['EMPFAENGER_TYP'];
			$this->rechnungs_empfaenger_id = $row ['EMPFAENGER_ID'];
			
			$this->rechnungs_kuerzel = $this->rechnungs_kuerzel_ermitteln ( $this->rechnungs_aussteller_typ, $this->rechnungs_aussteller_id, $row ['RECHNUNGSDATUM'] );
			$this->rechnungsnummer_kuerzel = $this->rechnungs_kuerzel . $this->aussteller_ausgangs_rnr;
			/* Rechnungspartner finden und Rechnungstyp ändern falls Aussteller = Empfänger */
			$this->rechnungs_partner_ermitteln ();
			
			$this->status_erfasst = $row ['STATUS_ERFASST'];
			$this->status_vollstaendig = $row ['STATUS_VOLLSTAENDIG'];
			$this->status_zugewiesen = $row ['STATUS_ZUGEWIESEN'];
			$this->kurzbeschreibung = $row ['KURZBESCHREIBUNG'];
			$this->status_bezahlt = $row ['STATUS_BEZAHLT'];
			$this->status_zahlung_freigegeben = $row ['STATUS_ZAHLUNG_FREIGEGEBEN'];
			$this->status_bestaetigt = $row ['STATUS_BESTAETIGT'];
			$this->bezahlt_am = date_mysql2german ( $row ['BEZAHLT_AM'] );
			$this->empfangs_geld_konto = $row ['EMPFANGS_GELD_KONTO'];
		}
	} // end function
	function summe_skonto_positionen($beleg_nr) {
		// $result = mysql_query ("SELECT SUM((MENGE*PREIS/100)*(100+MWST_SATZ) / 100 * (100-SKONTO)) AS SKONTO_BETRAG FROM RECHNUNGEN_POSITIONEN WHERE `BELEG_NR`='$beleg_nr' && AKTUELL='1'");
		// $row = mysql_fetch_assoc($result);
		// return $row[SKONTO_BETRAG];
		$rr = new rechnungen ();
		return $rr->summe_skonto_positionen ( $beleg_nr );
	}
	function update_skontobetrag($beleg_nr) {
		$rr = new rechnungen ();
		$betrag = nummer_komma2punkt ( nummer_punkt2komma ( $rr->summe_skonto_positionen ( $beleg_nr ) ) );
		mysql_query ( "UPDATE RECHNUNGEN SET SKONTOBETRAG='$betrag' WHERE BELEG_NR='$beleg_nr' && AKTUELL='1'" );
	}
	function summe_netto_positionen($beleg_nr) {
		
		/*
		 * $result = mysql_query ("SELECT SUM((MENGE*PREIS/100)*(100-RABATT_SATZ) ) AS NETTO_BETRAG FROM RECHNUNGEN_POSITIONEN WHERE `BELEG_NR`='$beleg_nr' && AKTUELL='1'");
		 * $row = mysql_fetch_assoc($result);
		 * return $row[NETTO_BETRAG];
		 */
		$rr = new rechnungen ();
		return $rr->summe_netto_positionen ( $beleg_nr );
	}
	function update_nettobetrag($beleg_nr) {
		$betrag = nummer_komma2punkt ( nummer_punkt2komma ( $this->summe_netto_positionen ( $beleg_nr ) ) );
		// echo "BETRAG $betrag";
		mysql_query ( "UPDATE RECHNUNGEN SET NETTO='$betrag' WHERE BELEG_NR='$beleg_nr' && AKTUELL='1'" );
	}
	function summe_brutto_positionen($beleg_nr) {
		$result = mysql_query ( "SELECT SUM((MENGE*PREIS/100)*(100+MWST_SATZ) ) AS BRUTTO FROM RECHNUNGEN_POSITIONEN WHERE `BELEG_NR`='$beleg_nr' && AKTUELL='1'" );
		$row = mysql_fetch_assoc ( $result );
		
		// $brutto = nummer_punkt2komma( $row[BRUTTO]);
		// echo "<h1>$brutto</h1>";
		// $brutto = nummer_komma2punkt($brutto);
		// echo "<h1>$brutto</h1>";
		// return $brutto;
		return $row [BRUTTO];
	}
	function update_bruttobetrag($beleg_nr) {
		$rr = new rechnungen ();
		$rr->update_bruttobetrag ( $beleg_nr );
		// $betrag = nummer_komma2punkt(nummer_punkt2komma($this->summe_brutto_positionen($beleg_nr)));
		// mysql_query ("UPDATE RECHNUNGEN SET BRUTTO='$betrag' WHERE BELEG_NR='$beleg_nr' && AKTUELL='1'");
	}
	function footer_zeilen_anzeigen($belegnr) {
		$this->rechnung_grunddaten_holen ( $belegnr );
		echo "<div id=\"div_footer_zeile\">";
		
		$result = mysql_query ( "SELECT ZEILE1, ZEILE2 FROM FOOTER_ZEILE WHERE AKTUELL = '1' && FOOTER_TYP = '$this->rechnungs_aussteller_typ' && FOOTER_TYP_ID = '$this->rechnungs_aussteller_id' ORDER BY FOOTER_DAT DESC LIMIT 0,1" );
		$nums = mysql_num_rows ( $result );
		if ($nums) {
			$row = mysql_fetch_assoc ( $result ) or DIE ();
			echo "<p id=\"footer_zeilenx\"><hr><center>$row[ZEILE1]<br>$row[ZEILE2]</center></p>";
			// echo "<p id=\"$footer_zeile2\">$row[ZEILE2]</p>";
		}
		echo "</div>";
	}
	function footer_zahlungshinweis($belegnr) {
		$this->rechnung_grunddaten_holen ( $belegnr );
		echo "<div id=\"div_z_hinweis\">";
		
		$result = mysql_query ( "SELECT ZAHLUNGSHINWEIS FROM FOOTER_ZEILE WHERE AKTUELL = '1' && FOOTER_TYP = '$this->rechnungs_aussteller_typ' && FOOTER_TYP_ID = '$this->rechnungs_aussteller_id' ORDER BY FOOTER_DAT DESC LIMIT 0,1" );
		$nums = mysql_num_rows ( $result );
		if ($nums) {
			$row = mysql_fetch_assoc ( $result ) or DIE ();
			echo "<p id=\"pzahlungs_hinweis\">$row[ZAHLUNGSHINWEIS]</p>";
		}
		echo "</div>";
	}
	function rechnungs_kuerzel_ermitteln($austeller_typ, $aussteller_id, $datum) {
		$result = mysql_query ( "SELECT KUERZEL FROM RECHNUNG_KUERZEL WHERE AKTUELL = '1' && AUSSTELLER_TYP = '$austeller_typ' && AUSSTELLER_ID = '$aussteller_id' && ( VON <= '$datum' OR BIS >= '$datum' ) ORDER BY RK_DAT DESC LIMIT 0,1" );
		$row = mysql_fetch_assoc ( $result );
		return $row ['KUERZEL'];
	}
	function rechnungs_partner_ermitteln() {
		if ($this->rechnungs_aussteller_typ == 'Partner') {
			
			/* Partnernamen holen */
			$this->rechnungs_aussteller_name = $this->get_partner_name ( $this->rechnungs_aussteller_id );
			/* Anschriften holen */
			$this->get_aussteller_info ( $this->rechnungs_aussteller_id );
			$this->rechnung_aussteller_partner_id = $this->rechnungs_aussteller_id;
		}
		
		if ($this->rechnungs_empfaenger_typ == 'Partner') {
			$this->rechnungs_empfaenger_name = $this->get_partner_name ( $this->rechnungs_empfaenger_id );
			/* Anschriften holen */
			$this->get_empfaenger_info ( $this->rechnungs_empfaenger_id );
			/* Ende Partnernamen holen */
			$this->rechnung_empfaenger_partner_id = $this->rechnungs_empfaenger_id;
		}
		
		if ($this->rechnungs_empfaenger_typ == 'Eigentuemer') {
			$weg = new weg ();
			$weg->get_eigentumer_id_infos3 ( $this->rechnungs_empfaenger_id );
			$this->rechnungs_empfaenger_name = $weg->post_anschrift;
			/* Anschriften holen */
			// $this->get_empfaenger_info($this->rechnungs_empfaenger_id);
			/* Ende Partnernamen holen */
			// $this->rechnung_empfaenger_partner_id = $this->rechnungs_empfaenger_id;
		}
		
		if ($this->rechnungs_aussteller_typ == 'Kasse') {
			/* Kassennamen holen */
			$kassen_info = new kasse ();
			$kassen_info->get_kassen_info ( $this->rechnungs_aussteller_id );
			$this->rechnungs_aussteller_name = "" . $kassen_info->kassen_name . "<br><br>" . $kassen_info->kassen_verwalter . "";
			/* Kassen Partner finden */
			$this->rechnung_aussteller_partner_id = $kassen_info->kassen_partner_id;
		}
		
		if ($this->rechnungs_empfaenger_typ == 'Kasse') {
			/* Kassennamen holen */
			$kassen_info = new kasse ();
			$kassen_info->get_kassen_info ( $this->rechnungs_empfaenger_id );
			$this->rechnungs_empfaenger_name = "" . $kassen_info->kassen_name . "<br><br>" . $kassen_info->kassen_verwalter . "";
			/* Kassen Partner finden */
			$this->rechnung_empfaenger_partner_id = $kassen_info->kassen_partner_id;
		}
		
		if ($this->rechnungs_aussteller_typ == 'Lager') {
			$lager_info = new lager ();
			// $this->rechnungs_aussteller_name = $lager_info->lager_bezeichnung($this->rechnungs_aussteller_id);
			/*
			 * Liefert Lagernamen und Partner id
			 * $lager_info->lager_name
			 * $lager_info->lager_partner_id
			 */
			$lager_info->lager_name_partner ( $this->rechnungs_aussteller_id );
			/* Partnernamen holen */
			$this->rechnungs_aussteller_name = 'Lager ' . $this->get_partner_name ( $lager_info->lager_partner_id );
			/* Anschriften holen */
			$this->get_aussteller_info ( $lager_info->lager_partner_id );
			$this->rechnung_aussteller_partner_id = $lager_info->lager_partner_id;
		}
		
		if ($this->rechnungs_empfaenger_typ == 'Lager') {
			$lager_info1 = new lager ();
			// $this->rechnungs_empfaenger_name = $lager_info->lager_bezeichnung($this->rechnungs_empfaenger_id); //alt
			/*
			 * Liefert Lagernamen und Partner id
			 * $lager_info->lager_name
			 * $lager_info->lager_partner_id
			 */
			$lager_info1->lager_name_partner ( $this->rechnungs_empfaenger_id );
			/* Partnernamen finden */
			$this->rechnungs_empfaenger_name = 'Lager ' . $this->get_partner_name ( $lager_info1->lager_partner_id );
			/* Anschriften holen */
			$this->get_empfaenger_info ( $lager_info1->lager_partner_id );
			
			$this->rechnung_empfaenger_partner_id = $lager_info1->lager_partner_id;
		}
		
		if ($this->rechnung_empfaenger_partner_id === $this->rechnung_aussteller_partner_id) {
			$this->rechnungs_typ_druck = 'BUCHUNGSBELEG';
		} else {
			// $this->rechnungs_typ_druck = 'RECHNUNG';
			$this->rechnungs_typ_druck = $this->rechnungstyp;
		}
	}
	
	/* Rechnung mit Positionen anzeigen */
	function rechnung_inkl_positionen_anzeigen($belegnr) {
		/* Rechnungskopf mit Grunddaten */
		$this->rechnungs_kopf ( $belegnr );
		
		$rechnungs_positionen_arr = $this->rechnungs_positionen_arr ( $belegnr );
		/* Rechnungspositionen Überschrift */
		echo "<div id=\"div_positionen\">";
		echo "<table id=\"positionen_tab\">\n";
		echo "<thead>";
		echo "<tr>";
		echo "<th scopr=\"col\" id=\"aus\">Aus</th>";
		echo "<th scopr=\"col\" id=\"aus\">Nach</th>";
		echo "<th scopr=\"col\">Pos</th>";
		echo "<th scopr=\"col\">Art.</th>";
		echo "<th scopr=\"col\">Bezeichnung</th>";
		if ($this->rechnungstyp == 'Buchungsbeleg') {
			echo "<th scopr=\"col\">Kontierung</th>";
		}
		echo "<th scopr=\"col\">Menge</th>";
		echo "<th scopr=\"col\">EP</th>";
		echo "<th scopr=\"col\">Rab.</th>";
		echo "<th scopr=\"col\">MWSt</th>";
		echo "<th scopr=\"col\">Skonto</th>";
		echo "<th scopr=\"col\" align=right>Netto</th>";
		echo "<th scopr=\"col\" align=right>WB</th>";
		echo "</tr>";
		echo "</thead>";
		if (count ( $rechnungs_positionen_arr ) > 0) {
			/* Rechnungspositionen */
			for($a = 0; $a < count ( $rechnungs_positionen_arr ); $a ++) {
				
				$u_beleg_nr = $rechnungs_positionen_arr [$a] ['U_BELEG_NR'];
				$position = $rechnungs_positionen_arr [$a] ['POSITION'];
				$menge = $rechnungs_positionen_arr [$a] ['MENGE'];
				$einzel_preis = $rechnungs_positionen_arr [$a] ['PREIS'];
				$mwst_satz = $rechnungs_positionen_arr [$a] ['MWST_SATZ'];
				$rabatt = $rechnungs_positionen_arr [$a] ['RABATT_SATZ'];
				
				$gesamt_netto = $rechnungs_positionen_arr [$a] ['GESAMT_NETTO'];
				$gesamt_netto = nummer_punkt2komma ( $gesamt_netto );
				$art_lieferant = $rechnungs_positionen_arr [$a] ['ART_LIEFERANT'];
				$artikel_nr = $rechnungs_positionen_arr [$a] ['ARTIKEL_NR'];
				$pos_skonto = $rechnungs_positionen_arr [$a] ['SKONTO'];
				if ($rabatt == '99.99' or $rabatt == '9.99' or $rabatt == '999.99') {
					fehlermeldung_ausgeben ( "Rabatt 99.99% oder Skonti 9.99%, Rechnung korrigieren!!!<br><br>" );
					$link_autokorrektur_pos = "<a href=\"?daten=rechnungen&option=autokorrektur_pos&belegnr=$belegnr\">Autokorrektur vornehmen</a>";
					warnung_ausgeben ( $link_autokorrektur_pos );
					echo "<br>";
				}
				$pos_skonto = nummer_punkt2komma ( $pos_skonto );
				
				/* Infos aus Katalog zu Artikelnr */
				$artikel_info_arr = $this->artikel_info ( $art_lieferant, $rechnungs_positionen_arr [$a] ['ARTIKEL_NR'] );
				for($i = 0; $i < count ( $artikel_info_arr ); $i ++) {
					if (! empty ( $artikel_info_arr [$i] ['BEZEICHNUNG'] )) {
						$bezeichnung = $artikel_info_arr [$i] ['BEZEICHNUNG'];
					} else {
						$bezeichnung = 'Unbekannt';
						$listenpreis = '0,00';
					}
					
					$menge = nummer_punkt2komma ( $menge );
					$einzel_preis = sprintf ( "%01.3f", $einzel_preis );
					$einzel_preis = nummer_punkt2komma ( $einzel_preis );
					
					// $listenpreis = nummer_punkt2komma($listenpreis);
					// $rabatt = nummer_punkt2komma($rabatt);
					// $gesamt_preis = nummer_punkt2komma($gesamt_preis);
					
					$r2 = new rechnungen ();
					$u_rechnungsnummer = '';
					$u_rechnungsnummer = $r2->get_rechnungsnummer ( $u_beleg_nr );
					// $f_rechnungsnummer = '';
					// $f_rechnungsnummer = $r2->get_rechnungsnummer($belegnr);
					
					$u_link = "<a href=\"?daten=rechnungen&option=rechnungs_uebersicht&belegnr=$u_beleg_nr\">$u_rechnungsnummer</a>";
					$ae_link = "<a href=\"?daten=rechnungen&option=position_aendern&belegnr=$belegnr&pos=$position\">Ändern</a>";
					$f_link = $this->nach_link ( $belegnr, $artikel_nr, $art_lieferant );
					echo "<tr><td valign=top id=\"aus\">$ae_link $u_link</td><td valign=top id=\"aus\">$f_link</td><td valign=top>$position.</td><td valign=top>$artikel_nr&nbsp;</td><td valign=top>$bezeichnung</td>";
					
					if ($this->rechnungstyp == 'Buchungsbeleg') {
						echo "<td valign=top>";
						$this->position_kontierung_infos ( $belegnr, $position );
						/*
						 * $this->kontierungs_menge = $menge;
						 * $this->kontenrahmen_konto = $kontenrahmen_konto;
						 * $this->kostentraeger_typ = $kostentraeger_typ;
						 * $this->kostentraeger_bez = $kostentraeger;
						 */
						echo "<b>$this->k_kontenrahmen_konto $this->k_kostentraeger_bez</b>";
					}
					echo "</td>";
					$js_wb = "onclick=\"wb_hinzufuegen($belegnr, $position)\"";
					$wb = "<img src=\"grafiken/wb.png\" $js_wb>";
					echo "<td align=right valign=top>&nbsp;&nbsp;$menge&nbsp;</td><td align=right valign=top>$einzel_preis&nbsp;</td><td align=left valign=top>&nbsp;&nbsp;$rabatt%</td><td align=left valign=top>&nbsp;&nbsp;$mwst_satz%&nbsp;</td><td align=right valign=top>$pos_skonto%&nbsp;&nbsp;</td><td align=right valign=top>$gesamt_netto €</td><td>$wb</td></tr>\n\n";
				} // end for 2
			} // end for 1
			
			/* Tabelle geht weiter in footertabelle_anzeigen und DIV element endet auch dort */
		}  // ende if $this->anzahl_positionen >0 d.h. Rechnung wurde nur kurz erfasst, positionen fehlen
		/* Positionen erfassen */
		else {
			
			echo "<tr><td><a href=\"?daten=rechnungen&option=positionen_erfassen&belegnr=$belegnr\">Positioneneigabe hier</a></td><td></td></tr>\n\n";
		}
		
		/* Rechnungsfooter d.h. Netto Brutto usw. */
		$this->rechnung_footer_tabelle_anzeigen ();
		/* Zahlungshinweis */
		/*
		 * if($this->rechnungstyp == 'Buchungsbeleg'){
		 * #$this->empfangs_geld_konto
		 * $g = new geldkonto_info;
		 * $g->geld_konto_details($this->empfangs_geld_konto);
		 * echo "Den Buchungsbetrag betrag bitten wir auf folgendes Konto zu überweisen:<br><br>";
		 * echo "Empfänger: $g->konto_beguenstigter<br>";
		 * echo "Kontonr.: $g->kontonummer<br>";
		 * echo "BLZ: $g->blz<br>";
		 * echo "Kreditinstitut: $g->kredit_institut<br>";
		 * }
		 */
		$this->footer_zahlungshinweis ( $belegnr );
		/* Footerzeile */
		$this->footer_zeilen_anzeigen ( $belegnr );
	}
	function nach_link($u_beleg_nr, $art_nr, $partner_id) {
		// ini_set('display_errors','On');
		// error_reporting(E_ALL|E_STRICT);
		// echo "$u_beleg_nr, $art_nr, $partner_id";
		$arr = $this->nach_link_arr ( $u_beleg_nr, $art_nr, $partner_id );
		if (is_array ( $arr )) {
			// echo '<pre>';
			// print_r($arr);
			// echo "<hr>";
			$anz = count ( $arr );
			$link = '';
			for($a = 0; $a < $anz; $a ++) {
				$beleg_nr = $arr [$a] ['BELEG_NR'];
				$menge = $arr [$a] ['MENGE'];
				$g_netto = nummer_punkt2komma_t ( $arr [$a] ['GESAMT_NETTO'] );
				
				$rr = new rechnungen ();
				$rr->rechnung_grunddaten_holen ( $beleg_nr );
				// print_r($rr);
				// die();
				$rr->rechnungs_empfaenger_name = substr ( $rr->rechnungs_empfaenger_name, 0, 30 );
				$link .= "<a href=\"?daten=rechnungen&option=rechnungs_uebersicht&belegnr=$beleg_nr\"><b>$rr->rechnungstyp" . ":" . "$rr->rechnungsnummer</b></a><br>$rr->rechnungs_empfaenger_name<br>$menge = $g_netto €<hr>";
			}
			return $link;
		}
	}
	function nach_link_arr($u_beleg_nr, $art_nr, $partner_id) {
		// SELECT BELEG_NR, MENGE, GESAMT_NETTO FROM `RECHNUNGEN_POSITIONEN` WHERE `U_BELEG_NR` = '6218' AND `BELEG_NR` != '6218' && ARTIKEL_NR='030626038206' && `ART_LIEFERANT`='10' && AKTUELL='1' ORDER BY `RECHNUNGEN_POSITIONEN`.`POSITION` ASC
		$result = mysql_query ( "SELECT BELEG_NR, MENGE, GESAMT_NETTO  FROM `RECHNUNGEN_POSITIONEN` WHERE `U_BELEG_NR` = '$u_beleg_nr' AND `BELEG_NR` != '$u_beleg_nr' && ARTIKEL_NR='$art_nr' && `ART_LIEFERANT`='$partner_id' && AKTUELL='1' ORDER BY BELEG_NR ASC" );
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			while ( $row = mysql_fetch_assoc ( $result ) )
				$my_array [] = $row;
			return $my_array;
		}
	}
	function rechnungs_kopf($belegnr) {
		$this->rechnung_grunddaten_holen ( $belegnr );
		$we_nummer = $this->empfaenger_eingangs_rnr;
		$wa_nummer = $this->aussteller_ausgangs_rnr;
		
		echo "<table id=rechnung width=\"100%\">\n";
		// echo "<p id=\"rechnungs_optionen\">";
		if ($this->status_bezahlt == '1') {
			$status_gezahlt = 'JA';
			$link_zahlung_freigeben = "";
		} else {
			$status_gezahlt = 'NEIN';
			if ($this->status_zahlung_freigegeben == '0') {
				$link_zahlung_freigeben = "<a href=\"?daten=rechnungen&option=zahlung_freigeben&belegnr=$belegnr\"><b>Zur Zahlung freigeben</b></a>";
			} else {
				$link_zahlung_freigeben = "Zur Zahlung freigegeben";
			}
		}
		
		$link_grunddaten_aendern = "<a href=\"?daten=rechnungen&option=rechnungsgrunddaten_aendern&belegnr=$belegnr\"><b>Grunddaten ändern</b></a>";
		
		$status_kontierung = $this->rechnung_auf_kontierung_pruefen ( $belegnr );
		// vollständig, unvollständig oder falsch
		if ($status_kontierung == 'vollstaendig' && $this->status_zugewiesen == '0') {
			$this->rechnung_als_zugewiesen ( $belegnr );
		}
		if ($status_kontierung == 'vollstaendig' && $this->status_vollstaendig == '0') {
			$this->rechnung_als_vollstaendig ( $belegnr );
		}
		if ($status_kontierung == 'unvollstaendig' or $status_kontierung == 'unvollstaendig') {
			// $this->rechnung_als_unvollstaendig($belegnr); //unvollständig und nicht zugewiesen, 2xstatus aufgehoben hat auswirkungen auf buchung, da keine buchung eine unvollständigen rechnung möglich ist
		}
		
		$kontierungsstatus_link = "<a href=\"?daten=rechnungen&option=rechnung_kontieren&belegnr=$belegnr\" class=\"kontierungs_link\">Kontierung $status_kontierung</a>\n";
		$kontierung_aufheben_link = "<a href=\"?daten=rechnungen&option=rechnung_kontierung_aufheben&belegnr=$belegnr\" class=\"kontierungs_link\">Gesamte Kontierung aufheben</a>\n";
		
		if ($this->rechnungstyp == "Rechnung") {
			echo "<tr class=feldernamen><td>Rechnung von</td><td>Rechnung an</td><td colspan=2 class=\"zusatz_info\">Zusatzinfo</td></tr>\n";
		}
		if ($this->rechnungstyp == "Buchungsbeleg") {
			echo "<tr class=feldernamen><td>Buchungsbeleg von</td><td>Buchungsbeleg an</td><td colspan=2 class=\"zusatz_info\">Zusatzinfo</td></tr>\n";
		}
		if ($this->rechnungstyp == "Gutschrift") {
			echo "<tr class=feldernamen><td>Gutschrift von</td><td>Gutschrift an</td><td colspan=2 class=\"zusatz_info\">Zusatzinfo</td></tr>\n";
		}
		// if($this->rechnungstyp == "Gutschrift"){
		// echo "<tr class=feldernamen><td>Gutschrift von</td><td>Gutschrift an</td><td colspan=2>Zusatzinfo</td></tr>\n";
		// }
		echo "<tr><td valign=top >" . $this->rechnungs_aussteller_name . "<br>" . $this->rechnungs_aussteller_strasse . " " . $this->rechnungs_aussteller_hausnr . "<br><br>" . $this->rechnungs_aussteller_plz . " " . $this->rechnungs_aussteller_ort . " </td><td valign=top><b>" . $this->rechnungs_empfaenger_name . "</b><br>" . $this->rechnungs_empfaenger_strasse . " " . $this->rechnungs_empfaenger_hausnr . "<br><br>" . $this->rechnungs_empfaenger_plz . " " . $this->rechnungs_empfaenger_ort . "</td>";
		
		echo "<td valign=top><b>ERFASSUNGSNR:</b><br>Rechnungsnr:<br>Rechnungsdatum:<br>Eingangsdatum:<br>Fällig am:";
		$link_pdf = "<a href=\"?daten=rechnungen&option=anzeigen_pdf&belegnr=$belegnr\"><img src=\"css/pdf.png\"></a>";
		$link_pdf1 = "<a href=\"?daten=rechnungen&option=anzeigen_pdf&belegnr=$belegnr&no_logo\"><img src=\"css/pdf2.png\"></a>";
		if ($this->status_bezahlt == '1') {
			echo "<br><b>Bezahlt am:</b>";
		}
		
		echo "<hr>$link_pdf $link_pdf1<hr>";
		
		if ($this->status_bezahlt == '0') {
			$link_zahlung_buchen = "<a href=\"?daten=rechnungen&option=rechnung_zahlung_buchen&belegnr=$belegnr\"><b>Zahlung buchen</b></a>";
		} else {
			$link_zahlung_buchen = "";
		}
		if ($this->status_bestaetigt == '0') {
			$link_empfang_buchen = "<a href=\"?daten=rechnungen&option=rechnung_empfang_buchen&belegnr=$belegnr\"><b>Geldempfang buchen</b></a>";
		}
		
		echo "</td><td valign=top align=\"left\"><b>$this->belegnr</b><br> $this->rechnungsnummer<br>$this->rechnungsdatum<br>$this->eingangsdatum<br>$this->faellig_am<br>Gezahlt:  $status_gezahlt";
		if ($this->status_bezahlt == '1') {
			echo "<br><b>$this->bezahlt_am</b>";
		}
		
		$link_details_hinzu = "<a href=\"?daten=details&option=details_hinzu&detail_tabelle=RECHNUNGEN&detail_id=$belegnr\">Lieferschein hinzufügen</a>";
		
		if ($this->rechnungstyp == 'Schlussrechnung') {
			$link_teilrg_hinzu = "<a href=\"?daten=rechnungen&option=teil_rg_hinzu&beleg_id=$belegnr\">Teilrechnung hinzufügen</a>";
		} else {
			$link_teilrg_hinzu = '';
		}
		echo "<hr>$link_zahlung_freigeben<br>$link_grunddaten_aendern<br><hr>$link_zahlung_buchen<br>$link_empfang_buchen<br>WE-Nummer: $we_nummer<br>WA-Nummer: $wa_nummer<br>$link_details_hinzu<br><b>$link_teilrg_hinzu";
		
		echo "</div></td></tr>\n";
		
		echo "<tr><td colspan=4><div id=\"rechnung_beschreibung\">$this->kurzbeschreibung</div><b>$kontierungsstatus_link<hr>$kontierung_aufheben_link</b></td></tr>\n";
		echo "</table>\n";
		/* DRUCKEN ab hier */
		/* Logo zum Drucken hinzufügen */
		if (file_exists ( "print_css/" . $this->rechnungs_aussteller_typ . "/" . $this->rechnungs_aussteller_id . "_logo.png" )) {
			echo "<div id=\"div_logo\"><img src=\"print_css/" . $this->rechnungs_aussteller_typ . "/" . $this->rechnungs_aussteller_id . "_logo.png\"><hr></div>\n";
		} else {
			echo "<div id=\"div_logo\">KEIN LOGO<br>Folgende Datei erstellen: print_css/" . $this->rechnungs_aussteller_typ . "/" . $this->rechnungs_aussteller_id . "_logo.png<hr></div>";
		}
		/* Div Firma */
		$r_rechnungs_aussteller_name = preg_replace ( '/<br>/', ' ', $this->rechnungs_aussteller_name );
		echo "<div id=\"div_firma\">$r_rechnungs_aussteller_name $this->rechnungs_aussteller_strasse $this->rechnungs_aussteller_hausnr - $this->rechnungs_aussteller_plz $this->rechnungs_aussteller_ort</div>";
		/* Rechnungskopf mit Grunddaten */
		/* DIV Adressfeld */
		echo "<div id=\"div_adressfeld\">$this->rechnungs_empfaenger_name<br>$this->rechnungs_empfaenger_strasse $this->rechnungs_empfaenger_hausnr<br><br>$this->rechnungs_empfaenger_plz $this->rechnungs_empfaenger_ort</div>\n";
		/* Markierung für die Brieffaltung */
		echo "<div id=\"div_faltlinie\">______";
		echo "</div>\n";
		
		/* DIV ADRRESSFELD */
		echo "\n<div id=\"div_rechnungsdaten\">\n";
		/* Links Überschriften/Titel */
		echo "<div id=\"rechnungsdaten_links\">\n";
		echo "<p id=\"rechnungsnummer_u\">$this->rechnungs_typ_druck:<br>$this->rechnungsnummer</p>\n";
		echo "</div>\n";
		/* Rechts daten */
		echo "<div id=\"rechnungsdaten_rechts\">\n";
		echo "<p id=\"rechnungsdatum_u\">Datum: $this->rechnungsdatum</p>\n";
		echo "<p id=\"rechnungsfaellig_u\">Fällig: $this->faellig_am</p>\n";
		// echo "<p id=\"skonto_u\">Skonto: $this->skonto %</p>\n";
		echo "</div>\n";
		echo "</div>\n";
		echo "<div id=\"div_kurzbeschreibung\">\n";
		echo "<p id=\"beschreibung_u\">$this->kurzbeschreibung</p>\n";
		echo "</div>\n";
		/* weiter geht es in function rechnung_anzeigen inkl positionen */
	}
	function rechnungs_kopf_kontierung($belegnr, $kostentraeger_typ) {
		$this->rechnung_grunddaten_holen ( $belegnr );
		/* Partnernamen holen */
		$partner_info = new partner ();
		/* Anschriften holen */
		// $partner_info->get_aussteller_info($this->rechnungs_aussteller_id);
		$partner_info->get_empfaenger_info ( $this->rechnungs_empfaenger_id );
		/* Ende Partnernamen holen */
		
		/*
		 * Aussteller Empfänger neu Definieren - wegen der automatischen Erstellung von Rechnungen an Eigentümer nach Kontierung
		 * Frühere Empfänger wird zu Aussteller
		 * Empfänger wird aus $objekt - Eigentümer definiert
		 */
		$rechnungs_aussteller_id = $this->rechnungs_aussteller_id;
		$rechnungs_aussteller_name = $this->rechnungs_empfaenger_name;
		$rechnungs_aussteller_str = $partner_info->rechnungs_empfaenger_strasse;
		$rechnungs_aussteller_hausnr = $partner_info->rechnungs_empfaenger_hausnr;
		$rechnungs_aussteller_plz = $partner_info->rechnungs_empfaenger_plz;
		$rechnungs_aussteller_ort = $partner_info->rechnungs_empfaenger_ort;
		
		$rechnungs_empfaenger_name = '';
		
		/* Rechnungskopf mit Grunddaten */
		
		echo "<table class=rechnung>\n";
		
		echo "<tr class=feldernamen><td colspan=4><b>Kontierung des Beleges $belegnr</b></td></tr>\n";
		echo "<tr class=feldernamen><td>VON:</td><td>AN:</td><td colspan=2>Zusatzinfo</td></tr>\n";
		echo "<tr><td valign=top><b>$rechnungs_aussteller_name</b><br>$rechnungs_aussteller_str $rechnungs_aussteller_hausnr<br><br>$rechnungs_aussteller_plz $rechnungs_aussteller_ort </td><td valign=top>Noch nicht definiert<br>Kostenträger ist ein /-e $kostentraeger_typ</td><td valign=top><b>Ursprungsdaten<hr></b>Belegnr:<br>Rechnungsnr: <br>Rechnungsdatum: <br>Eingangsdatum: <br>Fällig am: </td><td valign=top><br><hr>$this->belegnr<br><b>$this->rechnung_id</b><br> $this->rechnungsdatum<br> $this->eingangsdatum<br> <b>$this->faellig_am</b></td></tr>\n";
		echo "<tr><td colspan=4><hr>$this->kurzbeschreibung<hr></td></tr>\n";
		echo "</table>\n";
	}
	
	// ######
	/* Rechnung zum Kontieren mit Positionen anzeigen */
	function rechnung_zum_kontieren_anzeigen($belegnr) {
		$this->rechnung_grunddaten_holen ( $belegnr );
		
		/* Partnernamen holen */
		$partner_info = new partner ();
		/* Anschriften holen */
		$partner_info->get_aussteller_info ( $this->rechnungs_aussteller_id );
		$partner_info->get_empfaenger_info ( $this->rechnungs_empfaenger_id );
		/* Ende Partnernamen holen */
		
		$this->rechnungs_kopf ( $belegnr );
		
		$rechnungs_positionen_arr = $this->rechnungs_positionen_arr ( $belegnr );
		/* Rechnungspositionen Überschrift */
		if ($this->anzahl_positionen > 0) {
			echo "<table class=positionen>\n";
			echo "<form method=\"post\" name=\"myform\">\n";
			echo "<tr><td colspan=9><b>Kostenträger wählen</b>\n";
			$this->dropdown_kostentreager_typen ();
			echo "</td></tr>\n";
			$kt = new kontenrahmen ();
			echo "<tr><td colspan=9><b>Kostenträger wählen</b>\n";
			$kt->dropdown_kontenrahmen ( 'Kontenrahmen wählen', 'kontenrahmen', 'kontenrahmen', '' );
			echo "</td></tr>\n";
			echo "<tr><td colspan=9><b>Für die Kontierung wählen Sie bitte alle zusammenhängenden Positionen aus!!!</b></td></tr>\n";
			echo "<tr class=feldernamen><td><input type=\"checkbox\" onClick=\"this.value=check(this.form.positionen_list)\"><b>Alle</b></td><td>Pos</td><td>Artikelnr</td><td>Bezeichnung</td><td>Menge</td><td>Restmenge</td><td width=80>LP</td><td width=80>EP</td><td>Rabatt</td><td>Skonto</td><td align=right>MWSt %</td><td width=80>Netto</td></tr>\n";
			
			/* Rechnungspositionen */
			for($a = 0; $a < count ( $rechnungs_positionen_arr ); $a ++) {
				$position = $rechnungs_positionen_arr [$a] ['POSITION'];
				$menge = $rechnungs_positionen_arr [$a] ['MENGE'];
				$einzel_preis = $rechnungs_positionen_arr [$a] ['PREIS'];
				$mwst_satz = $rechnungs_positionen_arr [$a] ['MWST_SATZ'];
				$rabatt_satz = $rechnungs_positionen_arr [$a] ['RABATT_SATZ'];
				$skonto = $rechnungs_positionen_arr [$a] ['SKONTO'];
				$gesamt_preis = $rechnungs_positionen_arr [$a] ['GESAMT_NETTO'];
				$artikel_nr = $rechnungs_positionen_arr [$a] ['ARTIKEL_NR'];
				$art_lieferant = $rechnungs_positionen_arr [$a] ['ART_LIEFERANT'];
				$kontierte_menge = $this->position_auf_kontierung_pruefen ( $belegnr, $position );
				$restmenge = $menge - $kontierte_menge;
				
				/* Infos aus Katalog zu Artikelnr */
				$artikel_info_arr = $this->artikel_info ( $art_lieferant, $rechnungs_positionen_arr [$a] ['ARTIKEL_NR'] );
				for($i = 0; $i < count ( $artikel_info_arr ); $i ++) {
					if (! empty ( $artikel_info_arr [$i] ['BEZEICHNUNG'] )) {
						$bezeichnung = $artikel_info_arr [$i] ['BEZEICHNUNG'];
						$listenpreis = $artikel_info_arr [$i] ['LISTENPREIS'];
					} else {
						$bezeichnung = 'Unbekannt';
						$listenpreis = '0,00';
						$rabatt_satz = '0';
					}
					$menge = nummer_punkt2komma ( $menge );
					
					$einzel_preis = nummer_punkt2komma ( $einzel_preis );
					$listenpreis = nummer_punkt2komma ( $listenpreis );
					$mwst_satz = nummer_punkt2komma ( $mwst_satz );
					$gesamt_preis = nummer_punkt2komma ( $gesamt_preis );
					echo "<tr border=1><td>\n";
					if ($restmenge > 0) {
						echo "<input type=\"checkbox\" id=\"positionen_list\" name=\"positionen_list[]\" value=\"$position\">\n";
						$send_button_anzeigen = true;
					}
					$restmenge = nummer_punkt2komma ( $restmenge );
					echo "</td><td valign=top><b>$position.</td><td valign=top>$artikel_nr</td><td valign=top>$bezeichnung</td><td align=right valign=top>$menge</td><td align=right valign=top>$restmenge</td><td align=right valign=top>$listenpreis €</td><td align=right valign=top>$einzel_preis €</td><td align=right valign=top>$rabatt_satz %</td><td align=right valign=top>$skonto %</td><td align=right valign=top>$mwst_satz</td><td width=90 align=right valign=top>$gesamt_preis €</td></tr>\n";
					if ($kontierte_menge > 0) {
						echo "<tr><td><b>K</b><td><td colspan=10>\n";
						$this->position_kontierung_anzeigen ( $belegnr, $position );
						echo "</td></tr>\n";
					}
				} // end for 2
			} // end for 1
			if (isset ( $send_button_anzeigen )) {
				echo "<input type=\"hidden\" name=\"beleg_nr\" value=\"$this->belegnr\">\n";
				echo "<tr><td><input type=\"submit\" value=\" KONTIEREN \"></td></tr>\n";
				echo "</form></table>\n";
			}
		}  // ende if $this->anzahl_positionen >0 d.h. Rechnung wurde nur kurz erfasst, positionen fehlen
		/* Positionen erfassen */
		else {
			echo "<table class=rechnung><tr><td>\n";
			$rechnung_info = new rechnung ();
			$rechnung_info->positionen_eingabe_form ( $belegnr );
			echo "</td></tr></table>";
		}
		/* Rechnungsfooter */
		$this->rechnung_footer_tabelle_anzeigen ();
	}
	function kontierungstabelle_anzeigen($beleg_nr, $positionen_arr, $kostentraeger_typ) {
		$this->rechnung_grunddaten_holen ( $beleg_nr );
		// print_r($this);
		$form = new mietkonto ();
		// nur für die formularerstellung
		$kontenrahmen = new kontenrahmen ();
		// nur kontoliste dropdown
		$rechnung = new rechnung ();
		// für rechnungsmethoden
		// $this->rechnung_grunddaten_holen($beleg_nr);
		// $this->rechnungs_kopf_kontierung($beleg_nr, $kostentraeger_typ);
		$this->rechnungs_kopf ( $beleg_nr, $kostentraeger_typ );
		$rechnungs_positionen_arr = $this->rechnungs_positionen_arr ( $beleg_nr );
		$kontierung = new kontenrahmen ();
		$anzahl_pos_beleg = count ( $rechnungs_positionen_arr );
		$anzahl_pos_zu_kontierung = count ( $positionen_arr );
		echo "<table>\n";
		echo "<tr class=feldernamen><td>Pos</td><td>Artikelnr</td><td>Bezeichnung</td><td>Menge</td><td>LP </td><td>EP</td><td align=right>Rabatt</td><td align=right>MWSt</td><td width=90>Gesamt</td><td>Konto</td><td>Kostenträger</td><td>Weiter verwenden</td><td>Verwendung im Jahr</td></tr>\n";
		
		echo "<tr class=feldernamen><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td align=right></td><td width=90></td><td><input type=\"button\" onclick=\"auswahl_alle(this.form.kontenrahmen_konto)\" value=\"Alle\">
	</td><td><input type=\"button\" onclick=\"auswahl_alle(this.form.kostentraeger)\" value=\"Alle\"></td><td><input type=\"button\" onclick=\"auswahl_alle(this.form.weiter_verwenden)\" value=\"Alle\"></td><td><input type=\"button\" onclick=\"auswahl_alle(this.form.verwendungs_jahr)\" value=\"Alle\">
	</td></tr>\n";
		
		// echo "<input type=\"checkbox\" id=\"positionen_list\" name=\"positionen_list[]\" value=\"$position\">\n";
		
		for($a = 0; $a < $anzahl_pos_zu_kontierung; $a ++) {
			$zeilennr = $a;
			$kontierungs_position = $positionen_arr [$a];
			
			for($i = 0; $i < $anzahl_pos_beleg; $i ++) {
				if ($kontierungs_position == $rechnungs_positionen_arr [$i] ['POSITION']) {
					// echo "PPPPPP $i<br>\n";
					
					$position = $rechnungs_positionen_arr [$i] ['POSITION'];
					$ursprungs_menge = $rechnungs_positionen_arr [$i] ['MENGE'];
					$kontierte_menge = $this->position_auf_kontierung_pruefen ( $beleg_nr, $position );
					$menge = $ursprungs_menge - $kontierte_menge;
					$menge = nummer_punkt2komma ( $menge );
					$einzel_preis = $rechnungs_positionen_arr [$i] ['PREIS'];
					$einzel_preis = nummer_punkt2komma ( $einzel_preis );
					$mwst_satz = $rechnungs_positionen_arr [$i] ['MWST_SATZ'];
					$rabatt_satz = $rechnungs_positionen_arr [$i] ['RABATT_SATZ'];
					$skonto = $rechnungs_positionen_arr [$i] ['SKONTO'];
					$skonto = nummer_punkt2komma ( $skonto );
					
					$gesamt_preis = $rechnungs_positionen_arr [$i] ['GESAMT_NETTO'];
					$gesamt_preis = nummer_punkt2komma ( $gesamt_preis );
					$artikel_nr = $rechnungs_positionen_arr [$i] ['ARTIKEL_NR'];
					
					/* Infos aus Katalog zu Artikelnr */
					$artikel_info_arr = $this->artikel_info ( $this->rechnungs_aussteller_id, $artikel_nr );
					// echo "<pre>\n";
					// print_r($artikel_info_arr);
					// echo "</pre>\n";
					if (isset ( $artikel_info_arr [0] ['BEZEICHNUNG'] )) {
						$bezeichnung = $artikel_info_arr [0] ['BEZEICHNUNG'];
						$listenpreis = $artikel_info_arr [0] ['LISTENPREIS'];
						$listenpreis = nummer_punkt2komma ( $listenpreis );
					} else {
						$bezeichnung = 'Unbekannt';
						$listenpreis = '0,00';
					}
					// echo "<tr class=feldernamen><td>Pos</td><td>Artikelnr</td><td>Bezeichnung</td><td>Menge</td><td>EP </td><td>LP</td><td align=right>MWSt</td><td width=90>Gesamt</td><td>Konto</td><td>Kostenst.</td></tr>\n";
					$neue_position = $a + 1;
					echo "<tr><td valign=top>$neue_position.$kontierungs_position</td><td valign=top>$artikel_nr</td><td valign=top>$bezeichnung</td><td align=right valign=top>\n";
					$form->text_feld ( "Menge ($menge)", "gesendet[$neue_position][KONTIERUNGS_MENGE]=>'$neue_position'", $menge, 5 );
					echo "</td><td align=right valign=top>$listenpreis €</td><td align=right valign=top>$einzel_preis €</td><td align=right valign=top>$rabatt_satz %</td><td align=right valign=top>$mwst_satz %</td><td width=90 align=right valign=top>$gesamt_preis €</td><td>\n";
					
					/* Wegen der Rechnungskontierung muss hier der Kontenrahmen für alle angezeigt werden */
					// $kontenrahmen->dropdown_kontorahmen_konten("gesendet[$neue_position][KONTENRAHMEN_KONTO]=>'$neue_position'", 'ALLE','0');
					$bu = new buchen ();
					$kontenrahmen_id = $_POST ['kontenrahmen'];
					if (! empty ( $kontenrahmen_id )) {
						// $bu->dropdown_kostenrahmen_nr('Kostenkonto', "kontenrahmen_konto", 'Partner', $this->rechnungs_empfaenger_id, '');
						$kt = new kontenrahmen ();
						$kt->dropdown_konten_vom_rahmen ( 'Kostenkonto', "gesendet[$neue_position][KONTENRAHMEN_KONTO]=>'$neue_position", "kontenrahmen_konto", '', $kontenrahmen_id );
					} else {
						$bu->dropdown_kostenrahmen_nr ( 'Kostenkonto', "gesendet[$neue_position][KONTENRAHMEN_KONTO]=>'$neue_position", '', '', '' );
					}
					echo "</td><td>\n";
					$rechnung->dropdown_kostentreager_liste ( $kostentraeger_typ, "gesendet[$neue_position][KOSTENTRAEGER_ID]=>'$neue_position'", $this->rechnungs_aussteller_id );
					$form->hidden_feld ( "gesendet[$neue_position][KOSTENTRAEGER_TYP]=>'$neue_position'", $kostentraeger_typ );
					$form->hidden_feld ( "gesendet[$neue_position][KONTIERUNGS_POSITION]=>'$neue_position'", $kontierungs_position );
					$form->hidden_feld ( "gesendet[$neue_position][URSPRUNG_MENGE]=>'$neue_position'", $menge );
					$form->hidden_feld ( "gesendet[$neue_position][MWST_SATZ]=>'$neue_position'", $mwst_satz );
					$form->hidden_feld ( "gesendet[$neue_position][RABATT_SATZ]=>'$neue_position'", $rabatt_satz );
					$form->hidden_feld ( "gesendet[$neue_position][SKONTO]=>'$neue_position'", $skonto );
					// $form->hidden_feld("gesendet[$neue_position][ARTIKEL_NR]=>'$neue_position'", $artikel_nr);
					$form->hidden_feld ( "gesendet[$neue_position][EINZEL_PREIS]=>'$neue_position'", $einzel_preis );
					$form->hidden_feld ( "gesendet[$neue_position][GESAMT_PREIS]=>'$neue_position'", $gesamt_preis );
					echo "</td><td>";
					$this->weiter_verwenden_dropdown ( "gesendet[$neue_position][WEITER_VERWENDEN]=>'$neue_position'" );
					// "<input type=\"checkbox\" id=\"positionen_list\" name=\"positionen_list[]\" value=\"$kontierungs_position\">";
					echo "</td><td>";
					$this->verwendungs_jahr_dropdown ( "gesendet[$neue_position][VERWENDUNGS_JAHR]=>'$neue_position'" );
					// verwendungs_jahr_dropdown
					echo "</td></tr>\n";
				} // end if
			} // end for $i
		} // end for $a
		
		echo "<tr><td>\n";
		$form->hidden_feld ( 'BELEG_NR', $beleg_nr );
		$form->hidden_feld ( 'option', 'KONTIERUNG_SENDEN' );
		$form->send_button ( '', 'SEND' );
		echo "</td></tr>\n";
		echo "</table>\n";
		echo "<table>\n";
		echo "<tr><td>Im Beleg $beleg_nr befinden sich $anzahl_pos_beleg Positionen.</td></tr>\n";
		echo "<tr><td>$anzahl_pos_zu_kontierung von $anzahl_pos_beleg Positionen aus Beleg $beleg_nr haben Sie ausgewählt.</td></tr>\n";
		echo "</table>\n";
	} // end function
	function weiter_verwenden_dropdown($name) {
		echo "<select name=\"$name\" size=\"1\" id=\"weiter_verwenden\">\n";
		
		echo "<option name=\"$name\" value=\"1\" selected>JA</OPTION>\n";
		echo "<option name=\"$name\" value=\"0\">NEIN</OPTION>\n";
		echo "</select><br>\n";
	}
	function verwendungs_jahr_dropdown($name) {
		echo "<select name=\"$name\" size=\"1\" id=\"verwendungs_jahr\">\n";
		$akt_jahr = date ( "Y" );
		$anfangs_jahr = $akt_jahr - 3;
		$end_jahr = $akt_jahr + 2;
		for($a = $anfangs_jahr; $a <= $end_jahr; $a ++) {
			if ($a == $akt_jahr) {
				echo "<option name=\"$name\" value=\"$a\" selected>$a</OPTION>\n";
			} else {
				echo "<option name=\"$name\" value=\"$a\">$a</OPTION>\n";
			}
		}
		echo "</select><br>\n";
	}
	function kontierung_pruefen() {
		for($a = 1; $a <= count ( $_POST [gesendet] ); $a ++) {
			$kontierungs_menge = nummer_komma2punkt ( $_POST [gesendet] [$a] [KONTIERUNGS_MENGE] );
			$ursprung_menge = nummer_komma2punkt ( $_POST [gesendet] [$a] [URSPRUNG_MENGE] );
			// echo $_POST[gesendet][$a][KONTIERUNGS_MENGE];
			// echo " $kontierungs_menge > $ursprung_menge";
			if ($kontierungs_menge > $ursprung_menge) {
				$error = true;
			} else {
				$error = false;
			}
		}
		return $error;
	}
	function kontierung_speichern() {
		// $kontierung_id = $this->get_last_kontierung_id();
		// $kontierung_id = $kontierung_id + 1;
		$datum = date ( "Y-m-d" );
		// echo "<pre>";
		// print_r($_POST);
		// echo "</pre>";
		
		for($a = 1; $a <= count ( $_POST [gesendet] ); $a ++) {
			$kontierung_id = $this->get_last_kontierung_id ();
			$kontierung_id = $kontierung_id + 1;
			
			$beleg_nr = $_POST [BELEG_NR];
			$kontierungs_menge = $_POST [gesendet] [$a] [KONTIERUNGS_MENGE];
			$kontierungs_menge = nummer_komma2punkt ( $kontierungs_menge );
			$kontenrahmen_konto = $_POST [gesendet] [$a] [KONTENRAHMEN_KONTO];
			$kostentraeger_id = $_POST [gesendet] [$a] [KOSTENTRAEGER_ID];
			$kostentraeger_typ = $_POST [gesendet] [$a] [KOSTENTRAEGER_TYP];
			$kontierungs_pos = $_POST [gesendet] [$a] [KONTIERUNGS_POSITION];
			$einzel_preis = $_POST [gesendet] [$a] [EINZEL_PREIS];
			$einzel_preis = nummer_komma2punkt ( $einzel_preis );
			$gesamt_preis = $kontierungs_menge * $einzel_preis;
			// $gesamt_preis = nummer_komma2punkt($gesamt_preis);
			$verwendungs_jahr = $_POST [gesendet] [$a] [VERWENDUNGS_JAHR];
			$weiter_verwenden = $_POST [gesendet] [$a] [WEITER_VERWENDEN];
			$mwst_satz = $_POST [gesendet] [$a] [MWST_SATZ];
			$rabatt_satz = $_POST [gesendet] [$a] [RABATT_SATZ];
			$skonto = nummer_komma2punkt ( $_POST [gesendet] [$a] [SKONTO] );
			$db_abfrage = "INSERT INTO KONTIERUNG_POSITIONEN VALUES (NULL, '$kontierung_id','$beleg_nr', '$kontierungs_pos','$kontierungs_menge', '$einzel_preis', '$gesamt_preis', '$mwst_satz', '$skonto', '$rabatt_satz', '$kontenrahmen_konto', '$kostentraeger_typ', '$kostentraeger_id', '$datum', '$verwendungs_jahr', '$weiter_verwenden', '1')";
			$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
			/* Protokollieren */
			$last_dat = mysql_insert_id ();
			protokollieren ( 'KONTIERUNG_POSITIONEN', $last_dat, '0' );
		}
		$anzahl_positionen = count ( $_POST [gesendet] );
		hinweis_ausgeben ( "$anzahl_positionen Position (-en) wurde (-n) kontiert" );
		weiterleiten_in_sec ( "?daten=rechnungen&option=rechnung_kontieren&belegnr=$beleg_nr", 1 );
	}
	
	/* Ermitteln der letzten Kontierungs_id */
	function get_last_kontierung_id() {
		$result = mysql_query ( "SELECT KONTIERUNG_ID FROM KONTIERUNG_POSITIONEN WHERE  AKTUELL='1' ORDER BY KONTIERUNG_ID DESC LIMIT 0,1" );
		$row = mysql_fetch_assoc ( $result );
		return $row ['KONTIERUNG_ID'];
	}
	
	/* Ermitteln der letzten Kontierungs_position eines Beleges */
	function get_last_position_of_beleg($belegnr) {
		$result = mysql_query ( "SELECT POSITION FROM KONTIERUNG_POSITIONEN WHERE  BELEG_NR='$belegnr' && AKTUELL='1' ORDER BY KONTIERUNG_DAT DESC LIMIT 0,1" );
		$row = mysql_fetch_assoc ( $result );
		return $row [POSITION];
	}
	function position_auf_kontierung_pruefen($beleg_nr, $position) {
		$result = mysql_query ( "SELECT SUM( MENGE ) AS KONTIERTE_MENGE FROM `KONTIERUNG_POSITIONEN` WHERE BELEG_NR = '$beleg_nr' && POSITION = '$position' && AKTUELL='1'" );
		$row = mysql_fetch_assoc ( $result );
		$kontierte_menge = $row ['KONTIERTE_MENGE'];
		return $kontierte_menge;
	}
	function position_kontierung_anzeigen($beleg_nr, $position) {
		$result = mysql_query ( "SELECT KONTIERUNG_DAT, KONTIERUNG_ID, MENGE, EINZEL_PREIS, GESAMT_SUMME, KONTENRAHMEN_KONTO, KOSTENTRAEGER_TYP, KOSTENTRAEGER_ID FROM `KONTIERUNG_POSITIONEN` WHERE BELEG_NR = '$beleg_nr' && POSITION = '$position' && AKTUELL='1'" );
		$numrows = mysql_numrows ( $result );
		if ($numrows > 0) {
			while ( $row = mysql_fetch_assoc ( $result ) )
				$my_array [] = $row;
			echo "<hr>\n";
			for($a = 0; $a < $numrows; $a ++) {
				$dat = $my_array [$a] ['KONTIERUNG_DAT'];
				$id = $my_array [$a] ['KONTIERUNG_ID'];
				$menge = $my_array [$a] ['MENGE'];
				$einzel_preis = $my_array [$a] ['EINZEL_PREIS'];
				$gesamt_preis = $my_array [$a] ['EINZEL_PREIS'];
				$kontenrahmen_konto = $my_array [$a] ['KONTENRAHMEN_KONTO'];
				$kostentraeger_typ = $my_array [$a] ['KOSTENTRAEGER_TYP'];
				$kostentraeger_id = $my_array [$a] ['KOSTENTRAEGER_ID'];
				$kostentraeger = $this->kostentraeger_ermitteln ( $kostentraeger_typ, $kostentraeger_id );
				$menge = nummer_punkt2komma ( $menge );
				$link_aufhebung = "<a href=\"?daten=rechnungen&option=pos_kontierung_aufheben&belegnr=$beleg_nr&dat=$dat&id=$id\">Kontierung aufheben</a>";
				echo "<p id=\"pos_kontierung\">$menge $kontenrahmen_konto $kostentraeger_typ $kostentraeger $link_aufhebung</p>\n";
			}
			echo "<hr>\n";
		}
	}
	function position_kontierung_infos($beleg_nr, $position) {
		$result = mysql_query ( "SELECT KONTIERUNG_ID, MENGE, EINZEL_PREIS, GESAMT_SUMME, KONTENRAHMEN_KONTO, KOSTENTRAEGER_TYP, KOSTENTRAEGER_ID FROM `KONTIERUNG_POSITIONEN` WHERE BELEG_NR = '$beleg_nr' && POSITION = '$position' && AKTUELL='1'" );
		$numrows = mysql_numrows ( $result );
		unset ( $this->k_kontenrahmen_konto );
		unset ( $this->k_kostentraeger_bez );
		if ($numrows > 0) {
			
			$g = new geldkonto_info ();
			while ( $row = mysql_fetch_assoc ( $result ) )
				$my_array [] = $row;
				// for($a=0;$a<$numrows;$a++){
			for($a = 0; $a < 1; $a ++) {
				$menge = $my_array [$a] ['MENGE'];
				$einzel_preis = $my_array [$a] ['EINZEL_PREIS'];
				$gesamt_preis = $my_array [$a] ['EINZEL_PREIS'];
				$kontenrahmen_konto = $my_array [$a] ['KONTENRAHMEN_KONTO'];
				$kostentraeger_typ = $my_array [$a] ['KOSTENTRAEGER_TYP'];
				$kostentraeger_id = $my_array [$a] ['KOSTENTRAEGER_ID'];
				$kostentraeger = $this->kostentraeger_ermitteln ( $kostentraeger_typ, $kostentraeger_id );
				$menge = nummer_punkt2komma ( $menge );
				$this->k_kontierungs_menge = $menge;
				$this->k_kontenrahmen_konto = $kontenrahmen_konto;
				$this->k_kostentraeger_typ = $kostentraeger_typ;
				$this->k_kostentraeger_id = $kostentraeger_id;
				$this->k_kostentraeger_bez = $kostentraeger;
				
				$this->k_kostentraeger_anzahl_konten = $g->geldkonten_anzahl ( $kostentraeger_typ, $kostentraeger_id );
				if ($this->k_kostentraeger_anzahl_konten == '1') {
				}
			}
		}
	}
	function eigentuemer_ermitteln($kostentraeger_typ, $kostentraeger_id) {
		if ($kostentraeger_typ == 'Haus') {
			$haeuser = new haus ();
			$haeuser->get_haus_info ( $kostentraeger_id );
			$kostentraeger_id = $haeuser->objekt_id;
			$kostentraeger_typ = 'Objekt';
		}
		if ($kostentraeger_typ == 'Einheit') {
			$einheiten = new einheit ();
			$einheiten->get_einheit_info ( $kostentraeger_id );
			$kostentraeger_id = $einheiten->objekt_id;
			$kostentraeger_typ = 'Objekt';
		}
		if ($kostentraeger_typ == 'Objekt') {
			$o = new objekt ();
			$o->get_objekt_eigentuemer_partner ( $kostentraeger_id );
			return $o->objekt_eigentuemer_partner_id;
		}
	}
	function update_gsumme_positionen() {
		/*
		 * $abfrage = "UPDATE RECHNUNGEN_POSITIONEN AS t1
		 * JOIN RECHNUNGEN_POSITIONEN AS t2 ON t1.RECHNUNGEN_POS_ID = t2.RECHNUNGEN_POS_ID
		 * SET t1.GESAMT_NETTO = t2.MENGE*t2.PREIS";
		 */
	}
	function update_gsumme_kontierung() {
		/*
		 * $abfrage = "UPDATE KONTIERUNG_POSITIONEN AS t1
		 * JOIN KONTIERUNG_POSITIONEN AS t2 ON t1.KONTIERUNG_ID = t2.KONTIERUNG_ID
		 * SET t1.GESAMT_SUMME = t2.MENGE*t2.EINZEL_PREIS";
		 */
	}
	
	/*
	 * UPDATE RECHNUNGEN_POSITIONEN AS t1 JOIN RECHNUNGEN_POSITIONEN AS t2 ON t1.RECHNUNGEN_POS_ID = t2.RECHNUNGEN_POS_ID SET t1.GESAMT_NETTO = ( (
	 * t2.MENGE * t2.PREIS
	 * ) * ( 100 - t2.RABATT_SATZ ) /100 ) WHERE t1.BELEG_NR>='2550'
	 */
	/* Kostenträger ermitteln */
	function kostentraeger_ermitteln($kostentraeger_typ, $kostentraeger_id) {
		if ($kostentraeger_typ == 'Objekt') {
			$objekte = new objekt ();
			$objekt_name = $objekte->get_objekt_name ( $kostentraeger_id );
			$kostentraeger_string = "$objekt_name";
			// echo $kostentraeger_string;
			return $kostentraeger_string;
			// return $objekte->objekt_name;
		}
		if ($kostentraeger_typ == 'Haus') {
			$haeuser = new haus ();
			/*
			 * var $objekt_id;
			 * var $objekt_name;
			 * var $haus_strasse;
			 * var $haus_nummer;
			 * var $anzahl_haeuser;
			 * var $anzahl_einheiten;
			 * var $haus_plz;
			 * var $haus_stadt;
			 */
			$haeuser->get_haus_info ( $kostentraeger_id );
			$kostentraeger_string = "" . $haeuser->haus_strasse . " " . $haeuser->haus_nummer . "";
			return $kostentraeger_string;
		}
		if ($kostentraeger_typ == 'Einheit') {
			$einheiten = new einheit ();
			$einheiten->get_einheit_info ( $kostentraeger_id );
			// $kostentraeger_string = "<b>".$einheiten->einheit_kurzname."</b>&nbsp;".$einheiten->objekt_name."&nbsp;".$einheiten->haus_strasse."".$einheiten->haus_nummer."";
			$kostentraeger_string = "" . $einheiten->einheit_kurzname . "";
			return $kostentraeger_string;
		}
		
		if ($kostentraeger_typ == 'Partner') {
			$partner_info = new partner ();
			$partner_name = $partner_info->get_partner_name ( $kostentraeger_id );
			// $partner_name = substr($partner_name, 0, 20);
			return $partner_name;
		}
		if ($kostentraeger_typ == 'Lager') {
			$lager_info = new lager ();
			$lager_bezeichnung = $lager_info->lager_bezeichnung ( $kostentraeger_id );
			return $lager_bezeichnung;
		}
		
		if ($kostentraeger_typ == 'Mietvertrag') {
			$mv = new mietvertraege ();
			$mv->get_mietvertrag_infos_aktuell ( $kostentraeger_id );
			$kostentraeger_bez = $mv->personen_name_string_u;
			return $kostentraeger_bez;
		}
		
		if ($kostentraeger_typ == 'GELDKONTO') {
			$gk = new geldkonto_info ();
			$gk->geld_konto_details ( $kostentraeger_id );
			$kostentraeger_bez = $gk->geldkonto_bezeichnung_kurz;
			return $kostentraeger_bez;
		}
		
		if ($kostentraeger_typ == 'ALLE') {
			return 'ALLE';
		}
		
		if ($kostentraeger_typ == 'Wirtschaftseinheit') {
			$w = new wirt_e ();
			$w->get_wirt_e_infos ( $kostentraeger_id );
			return $w->w_name;
		}
		
		if ($kostentraeger_typ == 'Wirtschaftseinheit') {
			$w = new wirt_e ();
			$w->get_wirt_e_infos ( $kostentraeger_id );
			return $w->w_name;
		}
		
		if ($kostentraeger_typ == 'Baustelle_ext') {
			$s = new statistik ();
			$s->get_baustelle_ext_infos ( $kostentraeger_id );
			return 'BV*' . $s->bez;
		}
		
		if ($kostentraeger_typ == 'Eigentuemer') {
			$weg = new weg ();
			$bez = substr ( $weg->get_eigentumer_id_infos2 ( $kostentraeger_id ), 0, - 2 );
			return $bez;
		}
		
		if ($kostentraeger_typ == 'Benutzer') {
			$be = new benutzer ();
			$be->get_benutzer_infos ( $kostentraeger_id );
			return $be->benutzername;
		}
	}
	
	/* Rechnungsfooter */
	function rechnung_footer_tabelle_anzeigen() {
		$skonto_in_eur = $this->rechnungs_skontoabzug;
		
		$skontobetrag = $this->rechnungs_skontobetrag;
		$skontobetrag = sprintf ( "%01.2f", $skontobetrag );
		$skontobetrag = nummer_punkt2komma ( $skontobetrag );
		
		$rechnungs_netto = nummer_punkt2komma ( $this->rechnungs_netto );
		$rechnungs_mwst = $this->rechnungs_brutto - $rechnungs_netto;
		$rechnungs_mwst = nummer_punkt2komma ( $rechnungs_mwst );
		
		$rechnungs_brutto = nummer_punkt2komma ( $this->rechnungs_brutto );
		
		$skonto_in_eur = sprintf ( "%01.2f", $skonto_in_eur );
		$skonto_in_eur = nummer_punkt2komma ( $skonto_in_eur );
		
		/* rechnungsfooter */
		if ($this->rechnungstyp == "Rechnung" or $this->rechnungstyp == "Buchungsbeleg" or $this->rechnungstyp == "Gutschrift" or $this->rechnungstyp == "Stornorechnung") {
			$geld_konto_info = new geldkonto_info ();
			$geld_konto_info->geld_konto_details ( $this->empfangs_geld_konto );
			/* Falls rechnung bezahlt */
			if ($this->status_bezahlt == "1") {
				$msg = '';
				// $msg = "Rechnungsbetrag wurde am $this->bezahlt_am gezahlt.";
			} else {
				/* Falls rechnung unbezahlt */
				$msg = '';
				// $msg = "Bitte Rechnungbetrag auf folgendes Konto ".$geld_konto_info->kontonummer." bei ".$geld_konto_info->kredit_institut." BLZ: ".$geld_konto_info->blz." überwiesen.";
			}
			if ($this->rechnungstyp == "Rechnung" or $this->rechnungstyp == "Stornorechnung") {
				$msg = 'Den Rechnungsbetrag  bitten wir auf das unten genannte Konto zu überweisen.';
				$colspan = 12;
				$colspan1 = $colspan - 2;
			}
			if ($this->rechnungstyp == "Gutschrift") {
				$msg = 'Den Rechnungsbetrag werden wir auf Ihr Konto überweisen.';
				$colspan = 10;
				$colspan1 = $colspan - 2;
			}
			if ($this->rechnungstyp == "Buchungsbeleg") {
				$colspan = 12;
				$colspan1 = $colspan - 2;
				$msg = "Den Buchungsbetrag bitten wir auf folgendes Konto zu überweisen:<br><br>";
				$msg .= "Empfänger: $geld_konto_info->konto_beguenstigter<br>";
				$msg .= "Kontonr.: $geld_konto_info->kontonummer<br>";
				$msg .= "BLZ: $geld_konto_info->blz<br>";
				$msg .= "Kreditinstitut: $geld_konto_info->kredit_institut<br>";
			}
			
			echo "<tr><td colspan=$colspan><hr></td></tr>";
			echo "<tr><td colspan=$colspan1 align=right valign=top><b>Netto:</b></td><td colspan=2 align=right>$rechnungs_netto €</td></tr>";
			$this->summe_mwst_komma = nummer_punkt2komma ( $this->summe_mwst );
			echo "<tr><td colspan=$colspan1 align=right valign=top><b>MwSt:</b></td><td colspan=2 align=right>$this->summe_mwst_komma €</td></tr>";
			echo "<tr><td colspan=$colspan1 align=right valign=top><b>Brutto:</b></td><td colspan=2 align=right>$rechnungs_brutto €</td></tr>";
			echo "<tr><td colspan=$colspan1 align=right valign=top><b>Skonto:</b></td><td colspan=2 align=right>$skonto_in_eur €</td></tr>";
			echo "<tr><td colspan=$colspan1 align=right valign=top><b>Nach Abzug Skontobetrag:</b></td><td colspan=2 align=right>$skontobetrag €</td></tr>";
			
			echo "<tr><td  colspan=$colspan valign=top id=\"footer_msg\"><br>$msg</td></tr></table>";
		}
		echo "</div>";
		// ende div_positionen für druck
	} /* ende rechnungsfootoer */
	
	/* Rechnungsfooter bei Positionseingabe */
	function rechnung_footer_tabelle_anzeigen_pe() {
		$skonto_in_eur = $this->rechnungs_skontoabzug;
		
		$skontobetrag = $this->rechnungs_skontobetrag;
		$skontobetrag = sprintf ( "%01.2f", $skontobetrag );
		$skontobetrag = nummer_punkt2komma ( $skontobetrag );
		
		$rechnungs_netto = nummer_punkt2komma ( $this->rechnungs_netto );
		$rechnungs_mwst = $this->rechnungs_brutto - $rechnungs_netto;
		$rechnungs_mwst = nummer_punkt2komma ( $rechnungs_mwst );
		
		$rechnungs_brutto = nummer_punkt2komma ( $this->rechnungs_brutto );
		
		$skonto_in_eur = sprintf ( "%01.2f", $skonto_in_eur );
		$skonto_in_eur = nummer_punkt2komma ( $skonto_in_eur );
		
		/* rechnungsfooter */
		if ($this->rechnungstyp == "Rechnung" or $this->rechnungstyp == "Buchungsbeleg") {
			$geld_konto_info = new geldkonto_info ();
			$geld_konto_info->geld_konto_details ( $this->empfangs_geld_konto );
			/* Falls rechnung bezahlt */
			if ($this->status_bezahlt == "1") {
				$msg = "Rechnungsbetrag wurde am $this->bezahlt_am gezahlt.";
			} else {
				/* Falls rechnung unbezahlt */
				$msg = '';
				// $msg = "Bitte Rechnungbetrag auf folgendes Konto ".$geld_konto_info->kontonummer." bei ".$geld_konto_info->kredit_institut." BLZ: ".$geld_konto_info->blz." überwiesen.";
			}
			$msg = 'Den Rechnungsbetrag  bitten wir auf das unten genannte Konto zu überweisen.';
			echo "</table><table width=100% >";
			echo "<tr><td align=right valign=top><b>Netto:</b></td><td align=right valign=top>$rechnungs_netto €</td></tr>";
			$this->summe_mwst_komma = nummer_punkt2komma ( $this->summe_mwst );
			echo "<tr><td  align=right valign=top><b>MwSt:</b></td><td align=right valign=top>$this->summe_mwst_komma €</td></tr>";
			echo "<tr><td  align=right valign=top><b>Brutto:</b></td><td align=right valign=top>$rechnungs_brutto €</td></tr>";
			echo "<tr><td  align=right valign=top><b>Skonto:</b></td><td align=right valign=top>$skonto_in_eur €</td></tr>";
			echo "<tr><td  align=right valign=top><b>Nach Abzug Skontobetrag:</b></td><td valign=top align=right>$skontobetrag €</td></tr>";
			
			echo "<tr><td   valign=top id=\"footer_msg\"><br>$msg</td></tr></table>";
		}
	} /* ende rechnungsfootoer */
	
	/* Rechnungspositionen finden */
	function rechnungs_positionen_arr($belegnr) {
		$result = mysql_query ( "SELECT * FROM RECHNUNGEN_POSITIONEN WHERE BELEG_NR='$belegnr' && AKTUELL='1' ORDER BY POSITION ASC" );
		$numrows = mysql_numrows ( $result );
		if ($numrows < 1) {
			$this->anzahl_positionen = '0';
		} else {
			$this->anzahl_positionen = $numrows;
			while ( $row = mysql_fetch_assoc ( $result ) )
				$my_array [] = $row;
			return $my_array;
		}
	}
	
	/* Rechnung Position löschen bzw. deaktivieren */
	function position_deaktivieren($pos, $belegnr) {
		$result = mysql_query ( "UPDATE RECHNUNGEN_POSITIONEN SET AKTUELL='0' WHERE BELEG_NR='$belegnr' && AKTUELL='1' && POSITION='$pos'" );
	}
	
	/* Artikelinformationen aus dem Katalog holen */
	function artikel_info($partner_id, $artikel_nr) {
		$result = mysql_query ( "SELECT * FROM POSITIONEN_KATALOG WHERE ART_LIEFERANT='$partner_id' && ARTIKEL_NR = '$artikel_nr' && AKTUELL='1' ORDER BY KATALOG_DAT DESC LIMIT 0,1" );
		// echo "SELECT * FROM POSITIONEN_KATALOG WHERE ART_LIEFERANT='$partner_id' && ARTIKEL_NR = '$artikel_nr' && AKTUELL='1' ORDER BY KATALOG_DAT DESC LIMIT 0,1";
		$numrows = mysql_numrows ( $result );
		if (! $numrows) {
			return false;
		} else {
			while ( $row = mysql_fetch_assoc ( $result ) )
				$my_array [] = $row;
			return $my_array;
		}
	}
	
	/* Artikelnummern aus dem Katalog des Partner/Lieferanten holen */
	function artikel_leistungen_arr($partner_id) {
		$result = mysql_query ( "SELECT ARTIKEL_NR, BEZEICHNUNG, LISTENPREIS, RABATT_SATZ FROM POSITIONEN_KATALOG WHERE ART_LIEFERANT='$partner_id'  && AKTUELL='1' ORDER BY ARTIKEL_NR ASC" );
		$numrows = mysql_numrows ( $result );
		if ($numrows > 0) {
			while ( $row = mysql_fetch_assoc ( $result ) )
				$my_array [] = $row;
			return $my_array;
		}
	}
	
	/* Artikelnummer, Lieferant aus einem Beleg holen, darauf die Bezeichnung aus dem Katalog des Partner/Lieferanten holen */
	function kontierungsartikel_holen($beleg_nr, $position) {
		$result = mysql_query ( "SELECT ARTIKEL_NR, ART_LIEFERANT  FROM RECHNUNGEN_POSITIONEN WHERE BELEG_NR='$beleg_nr' && POSITION='$position' && AKTUELL='1' ORDER BY RECHNUNGEN_POS_DAT DESC LIMIT 0,1" );
		$numrows = mysql_numrows ( $result );
		if ($numrows > 0) {
			$row = mysql_fetch_assoc ( $result );
			$artikel_nr = $row ['ARTIKEL_NR'];
			$art_lieferant = $row ['ART_LIEFERANT'];
			/*
			 * $this->rechnung_grunddaten_holen($beleg_nr);
			 * $aussteller_id = $this->rechnungs_aussteller_id;
			 * #echo "$artikel_nr $aussteller_id<hr>";
			 */
			$artikel_info = $this->artikel_info ( $art_lieferant, $artikel_nr );
			// print_r($artikel_info);
			$this->artikel_nr = $row ['ARTIKEL_NR'];
			$this->art_lieferant = $row ['ART_LIEFERANT'];
			return $artikel_info [0] ['BEZEICHNUNG'];
		}
	}
	
	/* Ermitteln der letzten Artikel_nr/Leistungnr eines Lieferanten */
	function get_last_artikelnr($partner_id) {
		$result = mysql_query ( "SELECT ARTIKEL_NR FROM POSITIONEN_KATALOG WHERE ART_LIEFERANT='$partner_id' && AKTUELL='1' ORDER BY KATALOG_DAT DESC LIMIT 0,1" );
		$row = mysql_fetch_assoc ( $result );
		return $row ['ARTIKEL_NR'];
	}
	
	/* Ermitteln der letzten Artikel_nr/Leistungnr eines Lieferanten nach Bezeichnung */
	function get_last_artikelnr_nach_bezeichnung($partner_id, $bezeichnung) {
		$result = mysql_query ( "SELECT ARTIKEL_NR FROM POSITIONEN_KATALOG WHERE PARTNER_ID='$partner_id' && BEZEICHNUNG='$bezeichnung' && AKTUELL='1' ORDER BY ARTIKEL_NR DESC LIMIT 0,1" );
		$row = mysql_fetch_assoc ( $result );
		return $row ['ARTIKEL_NR'];
	}
	
	/* Ermitteln der letzten katalog_id */
	function get_last_katalog_id() {
		$result = mysql_query ( "SELECT KATALOG_ID FROM POSITIONEN_KATALOG WHERE AKTUELL='1' ORDER BY KATALOG_ID DESC LIMIT 0,1" );
		$row = mysql_fetch_assoc ( $result );
		return $row ['KATALOG_ID'];
	}
	
	/* Neuen Artikel/Leistung zum Lieferanten hinzufügen, wenn keine Artikelnummer eingegeben wurde, es wird eine neue vergeben */
	function artikel_leistung_speichern($partner_id, $bezeichnung, $listenpreis, $rabatt, $einheit, $mwst) {
		$letzte_kat_id = $this->get_last_katalog_id ();
		$letzte_kat_id = $letzte_kat_id + 1;
		$letzte_artikel_nr = $this->get_last_artikelnr ( $partner_id );
		$letzte_artikel_nr = $letzte_artikel_nr + 1;
		
		$db_abfrage = "INSERT INTO POSITIONEN_KATALOG VALUES (NULL, '$letzte_kat_id','$partner_id', '$letzte_artikel_nr','$bezeichnung', '$listenpreis', '$rabatt', '$einheit', '$mwst', '1')";
		$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		/* Protokollieren */
		$last_dat = mysql_insert_id ();
		// protokollieren('POSITIONEN_KATALOG', $last_dat, '0');
		return $this->get_last_artikelnr ( $partner_id );
	}
	
	/* Neuen Artikel/Leistung zum Lieferanten hinzufügen, wenn eine Artikelnummer eingegeben wurde, es wird mit der eingegebenen artikel_nr gespeichert */
	function artikel_leistung_mit_artikelnr_speichern($partner_id, $bezeichnung, $listenpreis, $artikel_nr, $rabatt, $einheit, $mwst, $pos_skonto) {
		$letzte_kat_id = $this->get_last_katalog_id ();
		$letzte_kat_id = $letzte_kat_id + 1;
		
		$bezeichnung = stripslashes ( $bezeichnung );
		$db_abfrage = "INSERT INTO POSITIONEN_KATALOG VALUES (NULL, '$letzte_kat_id','$partner_id', '$artikel_nr','$bezeichnung', '$listenpreis', '$rabatt', '$einheit', '$mwst', '$pos_skonto','1')";
		$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		/* Protokollieren */
		$last_dat = mysql_insert_id ();
		protokollieren ( 'POSITIONEN_KATALOG', $last_dat, '0' );
		return $this->get_last_artikelnr ( $partner_id );
	}
	
	/* Funktion zur Darstellung der Artikel bzw. (Leistungen) eines Lieferanten/Partners in einer Tabelle */
	function artikel_leistungen_block($partner_id) {
		$partner_info = new partner ();
		$partner_name = $partner_info->get_partner_name ( $partner_id );
		$katalog_arr = $this->artikel_leistungen_arr ( $partner_id );
		if (is_array ( $katalog_arr )) {
			echo "<div class=\"tabelle\">\n";
			echo $partner_name;
			// print_r($katalog_arr);
			echo "<table>\n";
			echo "<tr><td>ArtNr</td><td>Bezeichnung</td><td>LP</td><td>UP</td><td>Rabatt</td></tr>\n";
			for($a = 0; $a < count ( $katalog_arr ); $a ++) {
				$listenpreis = nummer_punkt2komma ( $katalog_arr [$a] [LISTENPREIS] );
				$rabatt_satz = $katalog_arr [$a] [RABATT_SATZ];
				$unser_preis = $listenpreis - (($listenpreis / 100) * $rabatt_satz);
				$javascript_link = "<a href=\"javascript:pos_fuellen('" . $katalog_arr [$a] [ARTIKEL_NR] . "','" . $katalog_arr [$a] ['BEZEICHNUNG'] . "', '" . $listenpreis . "');\">" . $katalog_arr [$a] [ARTIKEL_NR] . "</a>\n";
				echo "<tr><td>$javascript_link</td><td>" . $katalog_arr [$a] [BEZEICHNUNG] . "</td><td>$listenpreis €</td><td>$unser_preis</td><td><b>$rabatt_satz %</b></td></tr>\n";
			}
			// echo "<tr><td>".$katalog_arr[$a][ARTIKEL_NR]."</td><td>".$katalog_arr[$a][BEZEICHNUNG]."</td><td>".$katalog_arr[$a][LISTENPREIS]."</td></tr>\n";
			
			echo "</table>\n";
			echo "</div>\n";
		} else {
			echo "<div class=\"tabelle\">\n";
			echo "$partner_name <br>Keine Artikel / Leistungen vorhanden";
			echo "</div>\n";
		}
	}
	
	/* Maske zum Vervollständigen von Rechnungen d.h. Eingabe von Positionen */
	function positionen_eingabe_form($belegnr) {
		$this->rechnung_grunddaten_holen ( $belegnr );
		$form = new mietkonto ();
		// echo "$rechnung_id $partner";
		// echo "<table border=1><tr><td>\n";
		$form->erstelle_formular ( "Positionsanzahl eingeben", NULL );
		// echo "Geben Sie bitte die Anzahl der Positionen für die Rechnung $this->rechnungsnummer.<br>\n";
		$form->text_feld ( "Anzahl der Positionen:", "anzahl_positionen", "", "3" );
		$form->hidden_feld ( "option", "send_positionen" );
		$form->send_button ( "submit_position", "Senden" );
		$form->ende_formular ();
		// echo "</td></tr></table>\n";
	}
	
	/* Positionen einer Rechnung speichern */
	function positionen_speichern($belegnr) {
		$this->rechnung_grunddaten_holen ( $belegnr );
		$clean_arr = post_array_bereinigen ();
		$this->rechnung_grunddaten_holen ( $_REQUEST [rechnung_id] );
		if ($this->rechnungs_empfaenger_typ != 'Kasse') {
			$empfangs_geld_konto = $_POST [geld_konto];
		} else {
			$empfangs_geld_konto = '0';
			// NULL BEI KASSE
		}
		if (! isset ( $empfangs_geld_konto )) {
			echo "Kein Geldkonto ausgewählt";
		} else {
			
			/* Update der erfassten Rechung um die ausgewählte Kontonummer des rechnungaustellers mitzuteilen */
			if ($this->rechnungs_empfaenger_typ != 'Kasse') {
				$db_abfrage = "UPDATE RECHNUNGEN SET EMPFANGS_GELD_KONTO='$empfangs_geld_konto' WHERE BELEG_NR='$belegnr' && AKTUELL='1' ";
			} else {
				$zahlungs_datum = date_german2mysql ( $this->bezahlt_am );
				$db_abfrage = "UPDATE RECHNUNGEN SET EMPFANGS_GELD_KONTO='$empfangs_geld_konto', STATUS_ZAHLUNG_FREIGEGEBEN='1', STATUS_BEZAHLT='1', BEZAHLT_AM='$zahlungs_datum'  WHERE BELEG_NR='$belegnr' && AKTUELL='1' ";
			}
			$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
			/* Protokollieren von update */
			$last_dat = mysql_insert_id ();
			protokollieren ( 'RECHNUNGEN', $last_dat, $last_dat );
			echo "Dem Beleg $belegnr wurde die Kontonummer des Rechnungsausteller hinzugefügt<br>\n";
			
			/* Durchlauf von positionen */
			for($a = 1; $a <= count ( $_POST ['positionen'] ); $a ++) {
				$letzte_rech_pos_id = $this->get_last_rechnung_pos_id ();
				$letzte_rech_pos_id = $letzte_rech_pos_id + 1;
				
				/* Wenn Artikelnr eingegeben */
				if (! empty ( $_POST ['positionen'] [$a] ['artikel_nr'] )) {
					$pos_preis = nummer_komma2punkt ( $_POST ['positionen'] [$a] ['preis'] );
					$pos_menge = nummer_komma2punkt ( $_POST ['positionen'] [$a] ['menge'] );
					$pos_mwst_satz = nummer_komma2punkt ( $_POST ['positionen'] [$a] ['pos_mwst_satz'] );
					// $pos_rabatt = nummer_komma2punkt($_POST['positionen'][$a]['pos_rabatt']);
					$pos_rabatt = $_POST ['positionen'] [$a] ['pos_rabatt'];
					$pos_gesamt_netto = nummer_komma2punkt ( $_POST ['positionen'] [$a] ['gpreis'] );
					
					$db_abfrage = "INSERT INTO RECHNUNGEN_POSITIONEN VALUES (NULL, '$letzte_rech_pos_id', '$a', '$belegnr','$this->rechnungs_aussteller_id','" . $_POST ['positionen'] [$a] ['artikel_nr'] . "', '$pos_menge','$pos_preis','$pos_mwst_satz', '$pos_rabatt', '$pos_gesamt_netto','1')";
					
					$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
					/* Protokollieren */
					$last_dat = mysql_insert_id ();
					protokollieren ( 'RECHNUNGEN_POSITIONEN', $last_dat, '0' );
					echo "Position $a wurde gespeichert <br>\n";
				}  // end if
				
				/* Wenn keine Artikelnummer eingegeben ->Artikel anlegen */
				else {
					$pos_rabatt = nummer_komma2punkt ( $_POST ['positionen'] [$a] ['pos_rabatt'] );
					$this->artikel_leistung_speichern ( "" . $this->rechnungs_aussteller_id . "", "" . $_POST ['positionen'] [$a] ['bezeichnung'] . "", "" . $_POST ['positionen'] [$a] ['preis'] . ", $pos_rabatt" );
					$neue_artikel_nr = $this->get_last_artikelnr_nach_bezeichnung ( "" . $this->rechnungs_aussteller_id . "", "" . $_POST ['positionen'] [$a] ['bezeichnung'] . "" );
					
					$pos_preis = nummer_komma2punkt ( $_POST ['positionen'] [$a] ['preis'] );
					$pos_mwst_satz = nummer_komma2punkt ( $_POST ['positionen'] [$a] ['pos_mwst_satz'] );
					// $pos_rabatt = nummer_komma2punkt($_POST['positionen'][$a]['pos_rabatt']);
					$pos_rabatt = $_POST ['positionen'] [$a] ['pos_rabatt'];
					$pos_gesamt_netto = nummer_komma2punkt ( $_POST ['positionen'] [$a] ['gpreis'] );
					
					$db_abfrage = "INSERT INTO RECHNUNGEN_POSITIONEN VALUES (NULL, '$letzte_rech_pos_id', '$a', '$belegnr','$neue_artikel_nr', '" . $_POST ['positionen'] [$a] ['menge'] . "','$pos_preis','$pos_mwst_satz', '$pos_rabatt', '$pos_gesamt_netto','1')";
					
					$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
					/* Protokollieren */
					$last_dat = mysql_insert_id ();
					protokollieren ( 'RECHNUNGEN_POSITIONEN', $last_dat, '0' );
					echo "Position $a ($neue_artikel_nr) " . $_POST ['positionen'] [$a] ['bezeichnung'] . " wurde gespeichert<br>\n";
				}
			} // end for
			/* Rechnung als vollständig markieren */
			$this->rechnung_als_vollstaendig ( $belegnr );
			weiterleiten_in_sec ( "?daten=rechnungen&option=rechnungs_uebersicht&belegnr=$belegnr", 2 );
		} // end else kein konto
	}
	
	/* Positionen einer automatisch erstellten Rechnung speichern */
	function auto_positionen_speichern($belegnr, $positionen) {
		// echo "<pre>";
		// print_r($positionen);
		// echo "</pre>";
		$this->rechnung_grunddaten_holen ( $belegnr );
		
		for($a = 0; $a < count ( $positionen ); $a ++) {
			$letzte_rech_pos_id = $this->get_last_rechnung_pos_id ();
			$letzte_rech_pos_id = $letzte_rech_pos_id + 1;
			$zeile = $a + 1;
			
			$einzel_preis = $positionen [$a] ['preis'];
			$menge = $positionen [$a] ['menge'];
			$skonto = $positionen [$a] ['skonto'];
			
			$menge = nummer_komma2punkt ( $menge );
			$einzel_preis = nummer_komma2punkt ( $einzel_preis );
			$skonto = nummer_komma2punkt ( $skonto );
			
			$u_beleg_nr = $positionen [$a] ['beleg_nr'];
			$u_position = $positionen [$a] ['position'];
			$pos_rabatt_satz = $positionen [$a] ['rabatt_satz'];
			$pos_rabatt_satz = nummer_komma2punkt ( $pos_rabatt_satz );
			
			$gpreis = $einzel_preis * $menge;
			$gpreis = ($gpreis / 100) * (100 - $pos_rabatt_satz);
			$ursprungs_artikel_nr = $this->art_nr_from_beleg ( $u_beleg_nr, $u_position );
			$ursprungs_art_lieferant = $this->art_lieferant_from_beleg ( $u_beleg_nr, $u_position );
			$mwst_satz = $this->mwst_satz_der_position ( $u_beleg_nr, $u_position );
			
			$db_abfrage = "INSERT INTO RECHNUNGEN_POSITIONEN VALUES (NULL, '$letzte_rech_pos_id', '$zeile', '$belegnr', '$u_beleg_nr','$ursprungs_art_lieferant','$ursprungs_artikel_nr', '$menge','$einzel_preis','$mwst_satz', '$pos_rabatt_satz', '$skonto', '$gpreis','1')";
			$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
			/* Protokollieren */
			$last_dat = mysql_insert_id ();
			protokollieren ( 'RECHNUNGEN_POSITIONEN', $last_dat, '0' );
			// echo "Position $a ($neue_artikel_nr) ".$positionen[$a]['bezeichnung']." wurde gespeichert<br>\n";
			
			/* Autokontierung der Position */
			$position = $zeile;
			$u_position = $positionen [$a] ['position'];
			$u_belegnr = $positionen [$a] ['beleg_nr'];
			$dat = $positionen [$a] ['kontierung_dat'];
			$this->position_kontierung_infos_n ( $dat );
			/*
			 * echo '<pre>';
			 * print_r($this);
			 */
			echo "UBELEG $u_beleg_nr POS $u_position ";
			/* in rechnung gestellte menge */
			$kontierungs_menge = $positionen [$a] ['menge'];
			$kontierungs_menge = nummer_komma2punkt ( $kontierungs_menge );
			/* ursprüngliche kontierungsmenge */
			$u_kontierungs_menge = $this->kontierungs_menge_von_dat ( $dat );
			
			$kontenrahmen_konto = $this->kostenkonto;
			$kostentraeger_id = $this->kostentraeger_id;
			$kostentraeger_typ = $this->kostentraeger_typ;
			$kontierungs_pos = $positionen [$a] ['position'];
			$einzel_preis = $positionen [$a] ['preis'];
			$einzel_preis = nummer_komma2punkt ( $einzel_preis );
			
			$verwendungs_jahr = $this->verwendungs_jahr;
			$mwst_satz = $this->mwst_satz;
			$mwst_satz = nummer_komma2punkt ( $mwst_satz );
			
			$rabatt_satz = $positionen [$a] ['rabatt_satz'];
			$rabatt_satz = nummer_komma2punkt ( $rabatt_satz );
			
			$skonto = $positionen [$a] ['skonto'];
			$skonto = nummer_komma2punkt ( $skonto );
			
			if ($this->rechnungs_empfaenger_typ != 'Lager') {
				$this->automatisch_kontieren ( $belegnr, $kontierungs_menge, $kontenrahmen_konto, $kostentraeger_id, $kostentraeger_typ, $position, $einzel_preis, $mwst_satz, $rabatt_satz, $skonto, $verwendungs_jahr );
			}
			/* Wenn nicht die gesamte Menge in Rechnung gestellt wurde */
			if ($kontierungs_menge < $u_kontierungs_menge) {
				echo "KONTIERUNGSMENGE NICHT URSPRUNGSMENGE";
				$this->kontierungs_menge_anpassen_dat ( $dat, $kontierungs_menge );
				// menge die in Rechnung gestellt wurde
			}
			
			/* Deaktivieren der Position im Pool!!!! */
			if ($kontierungs_menge == $u_kontierungs_menge) {
				echo "KONTIERUNGSMENGE = URSPRUNGSMENGE";
				$this->kontierung_dat_deaktivieren ( $dat );
			}
		} // end for
		/* Rechnung als vollständig markieren */
		$this->rechnung_als_vollstaendig ( $belegnr );
		$this->rechnung_als_zugewiesen ( $belegnr );
		weiterleiten_in_sec ( "?daten=rechnungen&option=rechnungs_uebersicht&belegnr=$belegnr", 2 );
	}
	function position_kontierung_infos_alt($belegnr, $pos) {
		unset ( $this->kontierungs_menge );
		unset ( $this->kostenkonto );
		unset ( $this->kostentraeger_typ );
		unset ( $this->kostentraeger_id );
		unset ( $this->einzel_preis );
		unset ( $this->verwendungs_jahr );
		unset ( $this->mwst_satz );
		unset ( $this->rabatt_satz );
		$result = mysql_query ( "SELECT * FROM KONTIERUNG_POSITIONEN WHERE BELEG_NR='$belegnr' && POSITION='$pos' && AKTUELL='1' ORDER BY KONTIERUNG_DAT DESC LIMIT 0,1" );
		$row = mysql_fetch_assoc ( $result );
		$this->kontierungs_menge = $row ['MENGE'];
		$this->kostenkonto = $row ['KONTENRAHMEN_KONTO'];
		$this->kostentraeger_typ = $row ['KOSTENTRAEGER_TYP'];
		$this->kostentraeger_id = $row ['KOSTENTRAEGER_ID'];
		$this->einzel_preis = $row ['EINZEL_PREIS'];
		$this->verwendungs_jahr = $row ['VERWENDUNGS_JAHR'];
		$this->mwst_satz = $row ['MWST_SATZ'];
		$this->rabatt_satz = $row ['RABATT_SATZ'];
	}
	function position_kontierung_infos_n($dat) {
		unset ( $this->kontierungs_menge );
		unset ( $this->kostenkonto );
		unset ( $this->kostentraeger_typ );
		unset ( $this->kostentraeger_id );
		unset ( $this->einzel_preis );
		unset ( $this->verwendungs_jahr );
		unset ( $this->mwst_satz );
		unset ( $this->rabatt_satz );
		$result = mysql_query ( "SELECT * FROM KONTIERUNG_POSITIONEN WHERE KONTIERUNG_DAT='$dat' && AKTUELL='1' ORDER BY KONTIERUNG_DAT DESC LIMIT 0,1" );
		$row = mysql_fetch_assoc ( $result );
		$this->kontierungs_menge = $row ['MENGE'];
		$this->kostenkonto = $row ['KONTENRAHMEN_KONTO'];
		$this->kostentraeger_typ = $row ['KOSTENTRAEGER_TYP'];
		$this->kostentraeger_id = $row ['KOSTENTRAEGER_ID'];
		$this->einzel_preis = $row ['EINZEL_PREIS'];
		$this->verwendungs_jahr = $row ['VERWENDUNGS_JAHR'];
		$this->mwst_satz = $row ['MWST_SATZ'];
		$this->rabatt_satz = $row ['RABATT_SATZ'];
	}
	function art_nr_from_beleg($belegnr, $pos) {
		$result = mysql_query ( "SELECT ARTIKEL_NR FROM RECHNUNGEN_POSITIONEN WHERE BELEG_NR='$belegnr' && POSITION='$pos' && AKTUELL='1' ORDER BY RECHNUNGEN_POS_DAT DESC LIMIT 0,1" );
		$row = mysql_fetch_assoc ( $result );
		return $row ['ARTIKEL_NR'];
	}
	function art_lieferant_from_beleg($belegnr, $pos) {
		$result = mysql_query ( "SELECT ART_LIEFERANT FROM RECHNUNGEN_POSITIONEN WHERE BELEG_NR='$belegnr' && POSITION='$pos' && AKTUELL='1' ORDER BY RECHNUNGEN_POS_DAT DESC LIMIT 0,1" );
		$row = mysql_fetch_assoc ( $result );
		return $row ['ART_LIEFERANT'];
	}
	function rechnung_als_vollstaendig($belegnr) {
		$db_abfrage = "UPDATE RECHNUNGEN SET STATUS_VOLLSTAENDIG='1' WHERE BELEG_NR='$belegnr' && AKTUELL='1'";
		$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
	}
	function rechnung_als_unvollstaendig($belegnr) {
		$db_abfrage = "UPDATE RECHNUNGEN SET STATUS_VOLLSTAENDIG='0', STATUS_ZUGEWIESEN='0' WHERE BELEG_NR='$belegnr' && AKTUELL='1'";
		$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
	}
	function rechnung_als_zugewiesen($belegnr) {
		$db_abfrage = "UPDATE RECHNUNGEN SET STATUS_ZUGEWIESEN='1' WHERE BELEG_NR='$belegnr' && AKTUELL='1'";
		$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
	}
	function rechnung_als_freigegeben($belegnr) {
		$db_abfrage = "UPDATE RECHNUNGEN SET STATUS_ZAHLUNG_FREIGEGEBEN='1' WHERE BELEG_NR='$belegnr' && AKTUELL='1'";
		$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
	}
	function rechnung_als_gezahlt($belegnr, $datum) {
		$db_abfrage = "UPDATE RECHNUNGEN SET STATUS_BEZAHLT='1', BEZAHLT_AM='$datum' WHERE BELEG_NR='$belegnr' && AKTUELL='1'";
		$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
	}
	
	// function rechnung
	
	/* Prüfen ob ein Artikel nach Beschreibung exisitiert */
	function artikel_exists($partner_id, $artikel_bezeichnung) {
		$result = mysql_query ( "SELECT ARTIKEL_NR FROM POSITIONEN_KATALOG WHERE ART='$partner_id' && BEZEICHNUNG='$artikel_bezeichnung' && AKTUELL='1' ORDER BY KATALOG_DAT DESC LIMIT 0,1" );
		$row = mysql_fetch_assoc ( $result );
		return $row [ARTIKEL_NR];
	}
	
	/* Nach in Rechnungsstellung einer Konierungsposition, Tabelle WEITER_VERWENDEN auf 0 setzen */
	function kontierung_dat_id_deaktivieren($dat, $id) {
		$db_abfrage = "UPDATE KONTIERUNG_POSITIONEN SET WEITER_VERWENDEN='0' WHERE KONTIERUNG_DAT='$dat' && KONTIERUNG_ID='$id' && AKTUELL='1'";
		$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		/* Protokollieren */
		protokollieren ( 'RECHNUNGEN_POSITIONEN', $dat, $dat );
	}
	
	/* Nach in Rechnungsstellung einer Konierungsposition, Tabelle WEITER_VERWENDEN auf 0 setzen */
	function kontierung_dat_anpassen($dat, $neue_menge) {
		$db_abfrage = "UPDATE KONTIERUNG_POSITIONEN SET WEITER_VERWENDEN='0', MENGE='$neue_menge' WHERE KONTIERUNG_DAT='$dat'";
		$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		/* Protokollieren */
		protokollieren ( 'RECHNUNGEN_POSITIONEN', $dat, $dat );
	}
	
	/* Nach in Rechnungsstellung einer Konierungsposition, Tabelle WEITER_VERWENDEN auf 0 setzen */
	function kontierung_dat_deaktivieren($dat) {
		$db_abfrage = "UPDATE KONTIERUNG_POSITIONEN SET WEITER_VERWENDEN='0' WHERE KONTIERUNG_DAT='$dat' ";
		$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		/* Protokollieren */
		protokollieren ( 'KONTIERUNG_POSITIONEN', $dat, $dat );
	}
	
	/* Kontierung einer Position aufheben */
	function pos_kontierung_aufheben($dat, $id) {
		$db_abfrage = "UPDATE KONTIERUNG_POSITIONEN SET AKTUELL='0' WHERE KONTIERUNG_DAT='$dat' && KONTIERUNG_ID='$id' && AKTUELL='1'";
		$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		/* Protokollieren */
		protokollieren ( 'KONTIERUNG_POSITIONEN', $dat, $dat );
	}
	
	/* Kontierung einer Position aufheben */
	function pos_kontierung_aufheben_dat($dat) {
		$db_abfrage = "UPDATE KONTIERUNG_POSITIONEN SET AKTUELL='0' WHERE KONTIERUNG_DAT='$dat'";
		$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		/* Protokollieren */
		protokollieren ( 'KONTIERUNG_POSITIONEN', $dat, $dat );
	}
	
	/* Ermitteln der Menge einer Kontierungsposition */
	function kontierungs_menge($dat, $id) {
		$result = mysql_query ( "SELECT MENGE FROM KONTIERUNG_POSITIONEN WHERE KONTIERUNG_DAT='$dat' && KONTIERUNG_ID='$id' && AKTUELL='1'" );
		$row = mysql_fetch_assoc ( $result );
		return $row [MENGE];
	}
	
	/* Ermitteln der Gesamtmenge einer Kontierungsposition */
	function kontierungs_menge_gesamt($belegnr, $pos) {
		$result = mysql_query ( "SELECT SUM(MENGE) AS GESAMT_KONTIERT FROM KONTIERUNG_POSITIONEN WHERE BELEG_NR='$belegnr' && POSITION='$pos' && AKTUELL='1'" );
		$row = mysql_fetch_assoc ( $result );
		return $row [GESAMT_KONTIERT];
	}
	
	/* Ermitteln der Gesamtmenge einer Kontierungsposition */
	function kontierungs_menge_von_dat($dat) {
		$result = mysql_query ( "SELECT MENGE FROM KONTIERUNG_POSITIONEN WHERE KONTIERUNG_DAT='$dat' && AKTUELL='1'" );
		$row = mysql_fetch_assoc ( $result );
		return $row ['MENGE'];
	}
	
	/* Nach in Rechnungsstellung einer Konierungsposition mit veränderter Menge kontierungs_position anpassen um die Differenz */
	function kontierung_dat_id_andern($dat, $id, $aktuelle_menge) {
		$ursprungs_menge = $this->kontierungs_menge ( $dat, $id );
		if ($ursprungs_menge == $aktuelle_menge) {
			$this->kontierung_dat_id_deaktivieren ( $dat, $id );
		}
		if ($ursprungs_menge > $aktuelle_menge) {
			$result = mysql_query ( "SELECT MENGE FROM KONTIERUNG_POSITIONEN WHERE KONTIERUNG_DAT='$dat' && KONTIERUNG_ID='$id' && AKTUELL='1'" );
			$row = mysql_fetch_assoc ( $result );
			return $row [MENGE];
			
			$this->kontierung_dat_id_deaktivieren ( $dat, $id );
			$differenz_menge = $ursprungs_menge - $aktuelle_menge;
			
			$db_abfrage = "INSERT INTO KONTIERUNG_POSITIONEN VALUES (NULL, '$kontierung_id','$beleg_nr', '$kontierungs_pos','$kontierungs_menge', '$einzel_preis', '$gesamt_preis', '$kontenrahmen_konto', '$kostentraeger_typ', '$kostentraeger_id', '$datum', '$verwendungs_jahr', '$weiter_verwenden', '1')";
			$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		}
		
		if ($ursprungs_menge > $aktuelle_menge) {
			$this->kontierung_dat_id_deaktivieren ( $dat, $id );
		}
	}
	
	/* Menge einer Kontierungsposition ändern bzw anpassen */
	function kontierungs_menge_anpassen($dat, $id, $neue_menge) {
		$result = mysql_query ( "SELECT * FROM KONTIERUNG_POSITIONEN WHERE KONTIERUNG_DAT='$dat' && KONTIERUNG_ID='$id' && AKTUELL='1'" );
		$row = mysql_fetch_assoc ( $result );
		$beleg_nr = $row [BELEG_NR];
		$kontierungs_pos = $row [POSITION];
		$kontierungs_menge = $row [MENGE];
		$einzel_preis = $row [EINZEL_PREIS];
		$gesamt_preis = $row [GESAMT_SUMME];
		$kontenrahmen_konto = $row [KONTENRAHMEN_KONTO];
		$kostentraeger_typ = $row [KOSTENTRAEGER_TYP];
		$kostentraeger_id = $row [KOSTENTRAEGER_ID];
		$kontierungsdatum = $row [KONTIERUNGS_DATUM];
		$verwendungs_jahr = $row [VERWENDUNGS_JAHR];
		$weiter_verwenden = $row [WEITER_VERWENDEN];
		
		$this->kontierung_dat_id_deaktivieren ( $dat, $id );
		
		$db_abfrage = "INSERT INTO KONTIERUNG_POSITIONEN VALUES (NULL, '$kontierung_id','$beleg_nr', '$kontierungs_pos','$neue_menge', '$einzel_preis', '$gesamt_preis', '$kontenrahmen_konto', '$kostentraeger_typ', '$kostentraeger_id', '$datum', '$verwendungs_jahr', '$weiter_verwenden', '1')";
		$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
	}
	
	/* Menge einer Kontierungsposition ändern bzw anpassen */
	function kontierungs_menge_anpassen_dat($dat, $neue_menge) {
		$result = mysql_query ( "SELECT * FROM KONTIERUNG_POSITIONEN WHERE KONTIERUNG_DAT='$dat' && AKTUELL='1'" );
		$row = mysql_fetch_assoc ( $result );
		$beleg_nr = $row [BELEG_NR];
		$u_kontierung_id = $row [KONTIERUNG_ID];
		$kontierungs_pos = $row [POSITION];
		$kontierungs_menge = $row [MENGE];
		$einzel_preis = $row [EINZEL_PREIS];
		// $gesamt_preis = $row[GESAMT_SUMME];
		$mwst_satz = $row [MWST_SATZ];
		$rabatt_satz = $row [RABATT_SATZ];
		$kontenrahmen_konto = $row [KONTENRAHMEN_KONTO];
		$kostentraeger_typ = $row [KOSTENTRAEGER_TYP];
		$kostentraeger_id = $row [KOSTENTRAEGER_ID];
		$kontierungsdatum = $row [KONTIERUNGS_DATUM];
		$verwendungs_jahr = $row [VERWENDUNGS_JAHR];
		$weiter_verwenden = $row [WEITER_VERWENDEN];
		
		$diff_menge = $kontierungs_menge - $neue_menge;
		
		if ($diff_menge > 0) {
			
			/* Ursprungsmenge um Diffmenge Anpassen, dh. wenn vorher 3 und nur 2 in Rechnung dann 3 auf 2 setzen und rest als neue kontierungszeile einfügen, siehe drunter */
			$db_abfrage = "UPDATE KONTIERUNG_POSITIONEN SET MENGE='$neue_menge', WEITER_VERWENDEN='0' WHERE KONTIERUNG_DAT='$dat'";
			$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
			
			$datum = date ( "Y-m-d" );
			$kontierung_id = $this->get_last_kontierung_id ();
			$kontierung_id = $kontierung_id + 1;
			/* Differenzmenge / Restmenge für Pool für Weiterverwendung */
			$gesamt_preis = $diff_menge * $einzel_preis;
			$db_abfrage = "INSERT INTO KONTIERUNG_POSITIONEN VALUES (NULL, '$kontierung_id','$beleg_nr', '$kontierungs_pos','$diff_menge', '$einzel_preis', '$gesamt_preis', '$mwst_satz', '$rabatt_satz', '$kontenrahmen_konto', '$kostentraeger_typ', '$kostentraeger_id', '$datum', '$verwendungs_jahr', '1', '1')";
			$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		} else {
			echo "KEINE DIFFERENZMENGE ERSICHTLICH";
		}
	}
	
	/* Autokontierung einer Position */
	function automatisch_kontieren($beleg_nr, $kontierungs_menge, $kontenrahmen_konto, $kostentraeger_id, $kostentraeger_typ, $kontierungs_pos, $einzel_preis, $mwst_satz, $rabatt_satz, $skonto, $verwendungs_jahr) {
		$kontierung_id = $this->get_last_kontierung_id ();
		$kontierung_id = $kontierung_id + 1;
		
		$kontierungs_pos = $this->get_last_position_of_beleg ( $beleg_nr );
		$kontierungs_pos = $kontierungs_pos + 1;
		$gesamt_preis = $kontierungs_menge * $einzel_preis;
		$weiter_verwenden = '0';
		
		$datum = date ( "Y-m-d" );
		
		$db_abfrage = "INSERT INTO KONTIERUNG_POSITIONEN VALUES (NULL, '$kontierung_id','$beleg_nr', '$kontierungs_pos','$kontierungs_menge', '$einzel_preis', '$gesamt_preis', '$mwst_satz', '$skonto' , '$rabatt_satz', '$kontenrahmen_konto', '$kostentraeger_typ', '$kostentraeger_id', '$datum', '$verwendungs_jahr', '$weiter_verwenden', '1')";
		$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		/* Protokollieren */
		$last_dat = mysql_insert_id ();
		protokollieren ( 'KONTIERUNG_POSITIONEN', $last_dat, '0' );
	}
	
	/* Ermitteln der letzten RECHNUNGEN_POS_ID */
	function get_last_rechnung_pos_id() {
		$result = mysql_query ( "SELECT RECHNUNGEN_POS_ID FROM RECHNUNGEN_POSITIONEN ORDER BY RECHNUNGEN_POS_ID DESC LIMIT 0,1" );
		$row = mysql_fetch_assoc ( $result );
		return $row ['RECHNUNGEN_POS_ID'];
	}
	
	/* Funktion für's Suchen von rechnungen */
	function suche_rechnung_form() {
		$form = new mietkonto ();
		$partner = new partner ();
		
		$form->erstelle_formular ( "Rechnung finden", NULL );
		$datum_heute = date ( "d.m.Y" );
		echo "<table><tr><td>\n";
		$form->radio_button_checked ( "suchart", "lieferschein", 'Lieferschein' );
		echo "</td><td>\n";
		$form->text_feld ( "Lieferscheinnummer eingeben", "lieferschein_nr_txt", '', "10" );
		echo "</td></tr><tr><td>\n";
		$form->radio_button ( "suchart", "beleg_nr", 'Erfassungsnummer' );
		echo "</td><td>\n";
		$form->text_feld ( "Erfassungsnummer eingeben", "beleg_nr_txt", '', "10" );
		echo "</td></tr><tr><td>\n";
		$form->radio_button ( "suchart", "rechnungsnr", 'Rechnungsnummer' );
		echo "</td><td>\n";
		$form->text_feld ( "Rechnungsnummer eingeben", "rechnungsnr_txt", '', "10" );
		echo "</td></tr><tr><td>\n";
		$form->radio_button ( "suchart", "aussteller", 'Ausgestellt von' );
		echo "</td><td>\n";
		$partner_arr = $partner->partner_dropdown ( 'Aussteller wählen', 'aussteller', 'aussteller' );
		echo "</td></tr><tr><td>\n";
		$form->radio_button ( "suchart", "empfaenger", 'Ausgestellt an' );
		echo "</td><td>\n";
		$partner_arr = $partner->partner_dropdown ( 'Empfänger wählen', 'empfaenger', 'empfaenger' );
		echo "</td></tr><tr><td>\n";
		$form->radio_button ( "suchart", "partner_paar", 'Partnerpaar auswählen' );
		echo "</td><td>\n";
		$partner_arr = $partner->partner_dropdown ( 'Von', 'partner_paar1', 'partner_paar1' );
		// echo "</td><td>\n";
		echo "<br>";
		$partner_arr = $partner->partner_dropdown ( 'An', 'partner_paar2', 'partner_paar2' );
		echo "</td><td></td></tr><tr><td>\n";
		$form->send_button ( "submit_rechnungssuche", "Rechnung finden" );
		echo "</td></tr></table>\n";
		$form->hidden_feld ( "option", "rechnung_suchen1" );
		// $form->array_anzeigen($_POST);
		$form->ende_formular ();
	}
	function rechnung_finden_nach_beleg($beleg_nr) {
		$result = mysql_query ( "SELECT * FROM RECHNUNGEN WHERE BELEG_NR='$beleg_nr' && AKTUELL = '1' ORDER BY BELEG_NR DESC" );
		$numrows = mysql_numrows ( $result );
		if ($numrows > 0) {
			while ( $row = mysql_fetch_assoc ( $result ) )
				$my_array [] = $row;
		}
		return $my_array;
	}
	function rechnung_finden_nach_rnr($rnr) {
		$result = mysql_query ( "SELECT * FROM RECHNUNGEN WHERE LTRIM(RTRIM(RECHNUNGSNUMMER)) = '$rnr' && AKTUELL = '1' ORDER BY BELEG_NR DESC" );
		$numrows = mysql_numrows ( $result );
		if ($numrows > 0) {
			while ( $row = mysql_fetch_assoc ( $result ) )
				$my_array [] = $row;
			return $my_array;
		}
	}
	function rechnung_finden_nach_aussteller($aussteller) {
		$result = mysql_query ( "SELECT * FROM RECHNUNGEN WHERE AUSSTELLER_ID='$aussteller' && AUSSTELLER_TYP='Partner' && AKTUELL = '1' ORDER BY BELEG_NR DESC" );
		$numrows = mysql_numrows ( $result );
		if ($numrows > 0) {
			while ( $row = mysql_fetch_assoc ( $result ) )
				$my_array [] = $row;
		}
		return $my_array;
	}
	function rechnung_finden_nach_empfaenger($empfaengertyp, $id) {
		$result = mysql_query ( "SELECT * FROM RECHNUNGEN WHERE EMPFAENGER_TYP='$empfaengertyp' && EMPFAENGER_ID='$id' && AKTUELL = '1' ORDER BY EMPFAENGER_EINGANGS_RNR DESC" );
		$numrows = mysql_numrows ( $result );
		if ($numrows > 0) {
			while ( $row = mysql_fetch_assoc ( $result ) )
				$my_array [] = $row;
		}
		return $my_array;
	}
	function rechnung_finden_nach_paar($aussteller, $empfaenger) {
		$result = mysql_query ( "SELECT * FROM RECHNUNGEN WHERE EMPFAENGER_ID='$empfaenger' && EMPFAENGER_TYP='Partner' && AUSSTELLER_ID='$aussteller' && AUSSTELLER_TYP='Partner' && AKTUELL = '1' ORDER BY BELEG_NR DESC" );
		$numrows = mysql_numrows ( $result );
		if ($numrows > 0) {
			while ( $row = mysql_fetch_assoc ( $result ) )
				$my_array [] = $row;
		}
		return $my_array;
	}
	function rechnung_finden_nach_lieferschein($lieferschein) {
		include_once ('classes/class_details.php');
		$d = new detail ();
		$rechnungen_arr = $d->finde_detail_inhalt_arr ( 'RECHNUNGEN', 'Lieferschein', $lieferschein );
		if (is_array ( $rechnungen_arr )) {
			return $rechnungen_arr;
		} else {
			return false;
		}
	}
	function rechnungen_aus_arr_anzeigen($my_array) {
		echo "<table class=rechnungen>\n";
		echo "<tr class=feldernamen><td>ErfNr</td><td>RNr</td><td>TYP</td><td>R-Datum</td><td>Fällig</td><td>Von</td><td>An</td><td width=60>Netto</td><td width=60>Brutto</td></tr>\n";
		// print_r($my_array);
		for($a = 0; $a < count ( $my_array ); $a ++) {
			$belegnr = $my_array [$a] ['BELEG_NR'];
			$rechnungs_eingangs_nr = $my_array [$a] ['EMPFAENGER_EINGANGS_RNR'];
			$this->rechnung_grunddaten_holen ( $belegnr );
			
			$e_datum = date_mysql2german ( $my_array [$a] ['EINGANGSDATUM'] );
			$r_datum = date_mysql2german ( $my_array [$a] ['RECHNUNGSDATUM'] );
			$faellig_am = date_mysql2german ( $my_array [$a] ['FAELLIG_AM'] );
			$beleg_link = "<a href=\"?daten=rechnungen&option=rechnungs_uebersicht&belegnr=" . $my_array [$a] ['BELEG_NR'] . "\">Ansehen</a>\n";
			$netto = nummer_punkt2komma ( $my_array [$a] ['NETTO'] );
			// $mwst = nummer_punkt2komma($my_array[$a]['MWST']);
			$brutto = nummer_punkt2komma ( $my_array [$a] ['BRUTTO'] );
			$rechnungstyp = $my_array [$a] ['RECHNUNGSTYP'];
			$rechnungsnummer = $my_array [$a] ['RECHNUNGSNUMMER'];
			
			$link_pdf = "<a href=\"?daten=rechnungen&option=anzeigen_pdf&belegnr=$belegnr\"><img src=\"css/pdf.png\"></a>";
			$link_pdf1 = "<a href=\"?daten=rechnungen&option=anzeigen_pdf&belegnr=$belegnr&no_logo\"><img src=\"css/pdf2.png\"></a>";
			
			echo "<tr><td valign=\"top\">$beleg_link $link_pdf $link_pdf1</td><td valign=\"top\">$rechnungsnummer</td><td>$rechnungstyp</td><td valign=\"top\">$r_datum</td><td valign=\"top\"><b>$faellig_am</b></td><td valign=\"top\">$this->rechnungs_aussteller_name</td><td valign=\"top\">$this->rechnungs_empfaenger_name</td><td align=right valign=\"top\">$netto €</td><td align=right valign=\"top\">$brutto €</td></tr>\n";
		}
		
		echo "</table>\n";
	}
	
	/* Kostenträgerliste als dropdown */
	function dropdown_kostentreager_typen() {
		echo "<select name=\"kosten_traeger_typ\" size=1>\n";
		echo "<option value=\"Objekt\">Objekt</option>\n";
		echo "<option value=\"Haus\">Haus</option>\n";
		echo "<option value=\"Einheit\">Einheit</option>\n";
		echo "<option value=\"Partner\">Partner/Mieter</option>\n";
		echo "<option value=\"Lager\">Lager</option>\n";
		echo "</select>\n";
	}
	
	/* Kostenträgerliste als dropdown */
	function dropdown_kostentreager_liste($kostentraeger_typ, $name, $vorwahl_id = null) {
		if ($kostentraeger_typ == 'Objekt') {
			$objekte = new objekt ();
			$objekte->dropdown_objekte ( $name, 'kostentraeger' );
		}
		if ($kostentraeger_typ == 'Haus') {
			$haeuser = new haus ();
			$haeuser->dropdown_haeuser ( $name, 'kostentraeger' );
		}
		if ($kostentraeger_typ == 'Einheit') {
			$einheiten = new einheit ();
			$einheiten->dropdown_einheiten ( $name, 'kostentraeger' );
		}
		
		if ($kostentraeger_typ == 'Partner') {
			$partner_info = new partner ();
			$partner_info->partner_dropdown ( 'Kostenträger', $name, 'kostentraeger', $vorwahl_id );
		}
		if ($kostentraeger_typ == 'Lager') {
			$lager_info = new lager ();
			$lager_info->lager_dropdown ( "Lager", $name, 'kostentraeger' );
		}
	}
	function get_partner_name($partner_id) {
		$result = mysql_query ( "SELECT PARTNER_NAME FROM PARTNER_LIEFERANT WHERE PARTNER_ID='$partner_id' && AKTUELL = '1'" );
		$row = mysql_fetch_assoc ( $result );
		return $row ['PARTNER_NAME'];
	}
	function get_aussteller_info($partner_id) {
		$result = mysql_query ( "SELECT PARTNER_NAME, STRASSE, NUMMER, PLZ, ORT FROM PARTNER_LIEFERANT WHERE PARTNER_ID='$partner_id' && AKTUELL = '1'" );
		$row = mysql_fetch_assoc ( $result );
		// return
		$this->rechnungs_aussteller_name = $row ['PARTNER_NAME'];
		$this->rechnungs_aussteller_strasse = $row ['STRASSE'];
		$this->rechnungs_aussteller_hausnr = $row ['NUMMER'];
		$this->rechnungs_aussteller_plz = $row ['PLZ'];
		$this->rechnungs_aussteller_ort = $row ['ORT'];
	}
	function get_empfaenger_info($partner_id) {
		$result = mysql_query ( "SELECT PARTNER_NAME, STRASSE, NUMMER, PLZ, ORT FROM PARTNER_LIEFERANT WHERE PARTNER_ID='$partner_id' && AKTUELL = '1'" );
		$row = mysql_fetch_assoc ( $result );
		// return
		$this->rechnungs_empfaenger_name = $row ['PARTNER_NAME'];
		$this->rechnungs_empfaenger_strasse = $row ['STRASSE'];
		$this->rechnungs_empfaenger_hausnr = $row ['NUMMER'];
		$this->rechnungs_empfaenger_plz = $row ['PLZ'];
		$this->rechnungs_empfaenger_ort = $row ['ORT'];
	}
	function rechnungseingangsbuch($typ, $partner_id, $monat, $jahr, $rechnungstyp) {
		if (file_exists ( "classes/class_details.php" )) {
			include_once ("classes/class_details.php");
		}
		$monatname = monat2name ( $monat );
		$form = new formular ();
		$p = new partner ();
		if (! empty ( $_SESSION ['partner_id'] )) {
			$p->partner_grunddaten ( $_SESSION ['partner_id'] );
			$form->erstelle_formular ( "Ausgewählt: $p->partner_name", NULL );
			$form->erstelle_formular ( "Rechnungseingangsbuch $monatname $jahr - $p->partner_name", NULL );
		} else {
			$form->erstelle_formular ( "Ausgewählt: Lager", NULL );
			$form->erstelle_formular ( "Rechnungseingangsbuch $monatname $jahr - Lager", NULL );
		}
		echo "<table id=\"monate_links\"><tr><td>";
		$this->r_eingang_monate_links ( $monat, $jahr );
		echo "</td></tr>";
		$pdf_link = "<a href=\"?daten=rechnungen&option=rechnungsbuch_eingang&monat=$monat&jahr=$jahr&r_typ=Rechnung\">PDF-Ansicht</a>";
		echo "<tr><td>$pdf_link</td></tr>";
		echo "</table>";
		$rechnungen_arr = $this->eingangsrechnungen_arr ( $typ, $partner_id, $monat, $jahr, $rechnungstyp );
		/* Druck LOGO */
		
		$d = new detail ();
		$mandanten_nr = $d->finde_mandanten_nr ( $partner_id );
		
		if (file_exists ( "print_css/" . $typ . "/" . $partner_id . "_logo.png" )) {
			echo "<div id=\"div_logo\"><img src=\"print_css/" . $typ . "/" . $partner_id . "_logo.png\"><br>$p->partner_name Rechnungseingangsbuch $monatname $jahr Mandanten-Nr.: $mandanten_nr Blatt: $monat<hr></div>\n";
		} else {
			echo "<div id=\"div_logo\">KEIN LOGO<br>Folgende Datei erstellen: print_css/" . $typ . "/" . $partner_id . "_logo.png<hr></div>";
		}
		
		$this->rechnungsbuch_anzeigen_ein ( $rechnungen_arr );
		$form->ende_formular ();
		$form->ende_formular ();
	}
	function rechnungsausgangsbuch($typ, $partner_id, $monat, $jahr, $rechnungstyp) {
		if (file_exists ( "classes/class_details.php" )) {
			include_once ("classes/class_details.php");
		}
		$monatname = monat2name ( $monat );
		$form = new formular ();
		$p = new partner ();
		if (! empty ( $_SESSION ['partner_id'] )) {
			$p->partner_grunddaten ( $_SESSION ['partner_id'] );
			$form->erstelle_formular ( "Ausgewählt: $p->partner_name", NULL );
			$form->erstelle_formular ( "Rechnungsausgangsbuch $monatname $jahr - $p->partner_name", NULL );
		} else {
			$form->erstelle_formular ( "Ausgewählt: Lager", NULL );
			$form->erstelle_formular ( "Rechnungsausgangsbuch $monatname $jahr - Lager", NULL );
		}
		echo "<table id=\"monate_links\"><tr><td>";
		$this->r_ausgang_monate_links ( $monat, $jahr );
		echo "</td></tr>";
		$pdf_link = "<a href=\"?daten=rechnungen&option=rechnungsbuch_ausgang&monat=$monat&jahr=$jahr&r_typ=Rechnung\">PDF-Ansicht</a>";
		echo "<tr><td>$pdf_link</td></tr>";
		echo "</table>";
		$rechnungen_arr = $this->ausgangsrechnungen_arr ( $typ, $partner_id, $monat, $jahr, $rechnungstyp );
		/* Druck LOGO */
		$d = new detail ();
		$mandanten_nr = $d->finde_mandanten_nr ( $partner_id );
		
		if (file_exists ( "print_css/" . $typ . "/" . $partner_id . "_logo.png" )) {
			echo "<div id=\"div_logo\"><img src=\"print_css/" . $typ . "/" . $partner_id . "_logo.png\"><br>$p->partner_name Rechnungsausgangsbuch $monatname $jahr Mandanten-Nr.: $mandanten_nr Blatt: $monat<hr></div>\n";
		} else {
			echo "<div id=\"div_logo\">KEIN LOGO<br>Folgende Datei erstellen: print_css/" . $typ . "/" . $partner_id . "_logo.png<hr></div>";
		}
		$this->rechnungsbuch_anzeigen_aus ( $rechnungen_arr );
		$form->ende_formular ();
		$form->ende_formular ();
	}
	function rechnungsbuch_anzeigen_aus($arr) {
		if (isset ( $_REQUEST ['xls'] )) {
			ob_clean ();
			// ausgabepuffer leeren
			$fileName = 'rechnungsausgangsbuch' . date ( "d-m-Y" ) . '.xls';
			header ( "Content-type: application/vnd.ms-excel" );
			// header("Content-Disposition: attachment; filename=$fileName");
			header ( "Content-Disposition: inline; filename=$fileName" );
			$beleg_link = '';
		}
		
		echo "<table class=\"sortable\" id=\"positionen_tab\">\n";
		echo "<thead>";
		echo "<tr >";
		echo "<th scopr=\"col\" id=\"tr_ansehen\">Ansehen</th>";
		echo "<th >LFDNR</th>";
		echo "<th scopr=\"col\">Rechnungsempfänger</th>";
		echo "<th scopr=\"col\">Leistung/Ware</th>";
		echo "<th scopr=\"col\">Brutto</th>";
		// echo "<th scopr=\"col\">Skontobetrag</th>";
		echo "<th scopr=\"col\">Gutschriften und Returen</th>";
		echo "<th scopr=\"col\">R-Nr</th>";
		echo "<th scopr=\"col\">R-Datum</th>";
		echo "<th scopr=\"col\">Skonto</th>";
		echo "</tr>";
		
		echo "</thead>";
		
		$r = new rechnung ();
		
		$anzahl = count ( $arr );
		
		if ($anzahl) {
			$g_skonto = 0;
			for($a = 0; $a < $anzahl; $a ++) {
				
				$belegnr = $arr [$a] ['BELEG_NR'];
				if (! isset ( $fileName )) {
					$beleg_link = "<a href=\"?daten=rechnungen&option=rechnungs_uebersicht&belegnr=$belegnr\">Ansehen</a>";
					$pdf_link = "<a href=\"?daten=rechnungen&option=anzeigen_pdf&belegnr=$belegnr\"><img src=\"css/pdf.png\"></a>";
					$pdf_link1 = "<a href=\"?daten=rechnungen&option=anzeigen_pdf&belegnr=$belegnr&no_logo\"><img src=\"css/pdf2.png\"></a>";
				}
				$r->rechnung_grunddaten_holen ( $belegnr );
				$r->rechnungs_empfaenger_name = bereinige_string ( $r->rechnungs_empfaenger_name );
				$r->rechnungs_empfaenger_name = substr ( $r->rechnungs_empfaenger_name, 0, 48 );
				echo "<tr><td id=\"td_ansehen\">$beleg_link $pdf_link $pdf_link1</td><td valign=\"top\">$r->aussteller_ausgangs_rnr</td><td valign=\"top\">$r->rechnungs_empfaenger_name</td>";
				// $r->kurzbeschreibung =bereinige_string($r->kurzbeschreibung);
				echo "<td valign=\"top\">$r->kurzbeschreibung</td>";
				
				$r->rechnungs_brutto_ausgabe = nummer_punkt2komma ( $r->rechnungs_brutto );
				$r->rechnungs_skonto_ausgabe = nummer_punkt2komma ( $r->rechnungs_skontobetrag );
				
				if ($r->rechnungstyp == 'Rechnung' or $r->rechnungstyp == 'Teilrechnung') {
					// echo "<td align=\"right\">$r->rechnungs_brutto_ausgabe</td><td align=\"right\">$r->rechnungs_skonto_ausgabe</td><td></td>";
					echo "<td align=\"right\" valign=\"top\">$r->rechnungs_brutto_ausgabe</td><td></td>";
					$g_brutto_r = 0;
					$g_brutto_r = $g_brutto_r + $r->rechnungs_brutto;
					$g_brutto_r = sprintf ( "%01.2f", $g_brutto_r );
					$g_skonto_rg = 0;
					$g_skonto_rg = $g_skonto_rg + $r->rechnungs_skontobetrag;
					$g_skonto_rg = sprintf ( "%01.2f", $g_skonto_rg );
					
					$g_skonto = $g_skonto + $r->rechnungs_skontoabzug;
					$g_skonto = sprintf ( "%01.2f", $g_skonto );
					
					$g_netto = 0;
					$g_netto = $g_netto + $r->rechnungs_netto;
					$g_netto = sprintf ( "%01.2f", $g_netto );
					
					$g_mwst = 0;
					$g_mwst = $g_mwst + $r->rechnungs_mwst;
					$g_mwst = sprintf ( "%01.2f", $g_mwst );
					
					$g_brutto_g = 0;
					$g_brutto = $g_brutto_g + $r->rechnungs_brutto;
					$g_brutto = sprintf ( "%01.2f", $g_brutto );
				}
				
				if ($r->rechnungstyp == 'Schlussrechnung') {
					$rrr = new rechnungen ();
					$rrr->get_summen_schlussrechnung ( $belegnr );
					
					/* Sicherheitseinbehalt */
					$rrr->get_sicherheitseinbehalt ( $belegnr );
					if ($rrr->rg_betrag > '0.00') {
						// $this->rechnungs_brutto = ($row['BRUTTO'] - $rs->rg_betrag);
						// echo $this->rechnungs_brutto;
						$rrr->rechnungs_brutto_schluss = $rrr->rechnungs_brutto_schluss - $rrr->rg_betrag;
						$rrr->rechnungs_brutto_schluss_a = nummer_punkt2komma_t ( $rrr->rechnungs_brutto_schluss );
					}
					
					echo "<td align=\"right\" valign=\"top\">$rrr->rechnungs_brutto_schluss_a</td><td></td>";
					
					$g_brutto_r = $g_brutto_r + $rrr->rechnungs_brutto_schluss;
					$g_brutto_r = sprintf ( "%01.2f", $g_brutto_r );
					
					$g_skonto_rg = $g_skonto_rg + $r->rechnungs_skontobetrag;
					$g_skonto_rg = sprintf ( "%01.2f", $g_skonto_rg );
					
					$g_skonto = $g_skonto + $rrr->rechnungs_skontoabzug_schluss;
					$g_skonto = sprintf ( "%01.2f", $g_skonto );
					
					$g_netto = $g_netto + $rrr->rechnungs_netto_schluss;
					$g_netto = sprintf ( "%01.2f", $g_netto );
					
					$g_mwst = 0;
					$g_mwst = $g_mwst + $rrr->rechnungs_mwst_schluss;
					$g_mwst = sprintf ( "%01.2f", $g_mwst );
					
					$g_brutto_g = 0;
					$g_brutto = $g_brutto_g + $rrr->rechnungs_brutto_schluss;
					$g_brutto = sprintf ( "%01.2f", $g_brutto );
				}
				
				if ($r->rechnungstyp == 'Gutschrift' or $r->rechnungstyp == 'Stornorechnung') {
					// echo "<td></td><td></td><td align=\"right\">$r->rechnungs_skonto_ausgabe</td>";
					echo "<td></td><td align=\"right\" valign=\"top\">$r->rechnungs_brutto_ausgabe</td>";
					$g_brutto_g = 0;
					$g_brutto_g = $g_brutto_g + $r->rechnungs_brutto;
					$g_brutto_g = sprintf ( "%01.2f", $g_brutto_g );
				}
				$r->rechnungs_skontoabzug_a = nummer_punkt2komma ( $r->rechnungs_skontoabzug );
				echo "<td valign=\"top\"><b>$r->rechnungsnummer</b></td><td valign=\"top\">$r->rechnungsdatum</td><td align=\"right\" valign=\"top\">$r->rechnungs_skontoabzug_a</td></tr>";
			} // end for
			$g_brutto = nummer_punkt2komma ( $g_brutto );
			$g_brutto_g = nummer_punkt2komma ( $g_brutto_g );
			$g_skonto_rg = nummer_punkt2komma ( $g_skonto_rg );
			$g_skonto = nummer_punkt2komma ( $g_skonto );
			echo "<tfoot><tr><td colspan=\"9\"><hr></td></tr>";
			// echo "<tr><td></td><td></td><td></td><td></td><td align=\"right\"><b>$g_brutto_r</b></td><td align=\"right\"><b>$g_skonto_rg</b></td><td align=\"right\">$g_brutto_g</td><td></td><td></td></tr>";
			echo "<tr><td id=\"td_ansehen\"></td><td></td><td></td><td></td><td align=\"right\"><b>$g_brutto</b></td><td align=\"right\"><b>$g_brutto_g</b></td><td></td><td></td><td align=\"right\"><b>$g_skonto</b></td></tr></tfoot>";
		} else {
			echo "<tr><td colspan=10>Keine Rechnungen in diesem Monat</td></tr>";
		}
		echo "</table>";
	}
	function rechnungsbuch_anzeigen_ein($arr) {
		if (isset ( $_REQUEST ['xls'] )) {
			ob_clean ();
			// ausgabepuffer leeren
			$fileName = 'rechnungseingangsbuch' . date ( "d-m-Y" ) . '.xls';
			header ( "Content-type: application/vnd.ms-excel" );
			header ( "Content-Disposition: attachment; filename=$fileName" );
			$beleg_link = '';
		}
		echo "<table class=\"sortable\">";
		echo "<thead>";
		echo "<tr>";
		echo "<th id=\"tr_ansehen\">Ansehen</th>";
		echo "<th >LFDNR</th>";
		echo "<th >R-Datum</th>";
		echo "<th >Rechnungssteller</th>";
		echo "<th >RECHUNGSNR</th>";
		echo "<th >Leistung/Ware</th>";
		echo "<th >Brutto</th>";
		echo "<th >Skonto</th>";
		echo "<th >Gutschriften<br>Returen</th>";
		echo "<th >WEITERB.</th>";
		echo "<th >SALDO</th>";
		
		echo "</tr>";
		echo "</thead>";
		
		$r = new rechnung ();
		
		$anzahl = count ( $arr );
		$sum_weiterberechnet = 0;
		
		$g_brutto_r = 0;
		$g_brutto_g = 0;
		$g_netto = 0;
		$g_skonto = 0;
		$g_mwst = 0;
		
		if ($anzahl > 0) {
			for($a = 0; $a < $anzahl; $a ++) {
				
				$belegnr = $arr [$a] ['BELEG_NR'];
				/*
				 * if(!isset($fileName)){
				 * $beleg_link = "<a href=\"?daten=rechnungen&option=rechnungs_uebersicht&belegnr=$belegnr\">Ansehen</>\n";
				 * }
				 */
				
				if (! isset ( $fileName )) {
					$beleg_link = "<a href=\"?daten=rechnungen&option=rechnungs_uebersicht&belegnr=$belegnr\">Ansehen</a>";
					$pdf_link = "<a href=\"?daten=rechnungen&option=anzeigen_pdf&belegnr=$belegnr\"><img src=\"css/pdf.png\"></a>";
					$pdf_link1 = "<a href=\"?daten=rechnungen&option=anzeigen_pdf&belegnr=$belegnr&no_logo\"><img src=\"css/pdf2.png\"></a>";
				}
				
				$r->rechnung_grunddaten_holen ( $belegnr );
				$r->rechnungs_aussteller_name = substr ( $r->rechnungs_aussteller_name, 0, 48 );
				$status_kontierung = $r->rechnung_auf_kontierung_pruefen ( $belegnr );
				// echo $status_kontierung;
				
				if ($status_kontierung == 'unvollstaendig') {
					echo "<tr style=\"background-color:#ff778c\">";
				}
				
				if ($status_kontierung == 'vollstaendig') {
					echo "<tr style=\"background-color:#bcd59f\">";
				}
				
				echo "<td id=\"td_ansehen\">$beleg_link<br>$pdf_link $pdf_link1</td><td>$r->empfaenger_eingangs_rnr</td><td>$r->rechnungsdatum</td>";
				/* Prüfen ob die rechnung temporär zur Buchungszwecken an Rechnungsausstellr kontiert */
				if ($this->check_kontierung_rg ( $belegnr, $r->rechnungs_aussteller_typ, $r->rechnungs_aussteller_id ) == true) {
					echo "<td style=\"background-color:#f8ffbb\">$r->rechnungs_aussteller_name</td>";
				} else {
					echo "<td>$r->rechnungs_aussteller_name</td>";
				}
				
				echo "<td><b>$r->rechnungsnummer</b></td>";
				echo "<td>$r->kurzbeschreibung</td>";
				
				$r->rechnungs_skontoabzug_a = nummer_punkt2komma ( $r->rechnungs_skontoabzug );
				
				if ($r->rechnungstyp == 'Rechnung' or $r->rechnungstyp == 'Teilrechnung' or $r->rechnungstyp == 'Schlussrechnung') {
					$r->rechnungs_brutto_a = nummer_punkt2komma ( $r->rechnungs_brutto );
					echo "<td align=\"right\" valign=\"top\">$r->rechnungs_brutto_a </td><td align=\"right\" valign=\"top\">$r->rechnungs_skontoabzug_a</td><td></td>";
					$g_brutto_r += $r->rechnungs_brutto;
					// $g_brutto_r= sprintf("%01.2f", $g_brutto_r);
				}
				
				if ($r->rechnungstyp == 'Gutschrift' or $r->rechnungstyp == 'Stornorechnung') {
					$r->rechnungs_brutto_a = nummer_punkt2komma ( $r->rechnungs_brutto );
					echo "<td></td><td align=\"right\" valign=\"top\">$r->rechnungs_skontoabzug_a</td><td align=\"right\" valign=\"top\">$r->rechnungs_brutto_a </td>";
					$g_brutto_g += $r->rechnungs_brutto;
					// $g_brutto_g= sprintf("%01.2f", $g_brutto_g);
				}
				
				$summe_weiterbelastung_a = nummer_punkt2komma ( $this->get_weiterbelastung ( $belegnr ) );
				$summe_weiterbelastung = nummer_komma2punkt ( $summe_weiterbelastung_a );
				$sum_weiterberechnet += $summe_weiterbelastung;
				echo "<td>$summe_weiterbelastung_a</td>";
				$saldo_rg = $summe_weiterbelastung - $r->rechnungs_brutto;
				$saldo_rg_a = nummer_punkt2komma ( $saldo_rg );
				if ($saldo_rg >= 0) {
					echo "<td style=\"background-color:#bcd59f\">";
				} else {
					
					// braun ==c48b7c
					if ($this->check_kontierung_rg ( $belegnr, $r->rechnungs_empfaenger_typ, $r->rechnungs_empfaenger_id ) == true) {
						echo "<td style=\"background-color:#c48b7c\">";
					} else {
						echo "<td style=\"background-color:#ff778c\">";
					}
				}
				echo "$saldo_rg_a</td>";
				
				echo "</tr>";
				
				$g_netto += $r->rechnungs_netto;
				// $g_netto= sprintf("%01.2f", $g_netto);
				$g_mwst += $r->rechnungs_mwst;
				// $g_mwst= sprintf("%01.2f", $g_mwst);
				
				$g_skonto += $r->rechnungs_skontoabzug;
				// $g_skonto= sprintf("%01.2f", $g_skonto);
			}
			// echo "<tr><td colspan=\"9\"><hr></td></tr>";
			$g_brutto_r = nummer_punkt2komma_t ( $g_brutto_r );
			$g_brutto_g = nummer_punkt2komma_t ( $g_brutto_g );
			$g_skonto = nummer_punkt2komma_t ( $g_skonto );
			$sum_weiterberechnet_a = nummer_punkt2komma_t ( $sum_weiterberechnet );
			echo "<tfoot><tr><td id=\"td_ansehen\"></td><td></td><td></td><td></td><td></td><td></td><td align=\"right\"><b>$g_brutto_r</b></td><td align=\"right\"><b>$g_skonto</b></td><td><b>$g_brutto_g</b></td><td><b>$sum_weiterberechnet_a</b></td><td align=\"right\"></td></tr></tfoot>";
		} else {
			echo "<tr><td colspan=9>Keine Rechnungen in diesem Monat</td></tr>";
		}
		echo "</table>";
	}
	function check_kontierung_rg($beleg_nr, $kos_typ, $kos_id) {
		$result = mysql_query ( "SELECT * FROM `KONTIERUNG_POSITIONEN` WHERE BELEG_NR = '$beleg_nr' && KOSTENTRAEGER_TYP = '$kos_typ' && KOSTENTRAEGER_ID = '$kos_id' && AKTUELL='1'" );
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			return true;
		} else {
			return false;
		}
	}
	function get_weiterbelastung($belegnr) {
		$result = mysql_query ( "SELECT SUM((GESAMT_NETTO/100)*(100+MWST_SATZ)) AS SUMME FROM `RECHNUNGEN_POSITIONEN` WHERE `U_BELEG_NR`!=`BELEG_NR` && `U_BELEG_NR` = '$belegnr' AND `AKTUELL` = '1'" );
		$row = mysql_fetch_assoc ( $result );
		return $row ['SUMME'];
	}
	function rechnungsbuch_anzeigen_ein_ALTOK($arr) {
		if (isset ( $_REQUEST ['xls'] )) {
			ob_clean ();
			// ausgabepuffer leeren
			$fileName = 'rechnungseingangsbuch' . date ( "d-m-Y" ) . '.xls';
			header ( "Content-type: application/vnd.ms-excel" );
			header ( "Content-Disposition: attachment; filename=$fileName" );
			$beleg_link = '';
		}
		echo "<table class=\"sortable\">";
		echo "<thead>";
		echo "<tr>";
		echo "<th id=\"tr_ansehen\">Ansehen</th>";
		echo "<th >LFDNR</th>";
		echo "<th >Rechnungssteller</th>";
		echo "<th >Leistung/Ware</th>";
		echo "<th >Brutto</th>";
		echo "<th >Gutschriften und Returen</th>";
		echo "<th >RECHUNGSNR</th>";
		echo "<th >R-Datum</th>";
		echo "<th >Skonto</th>";
		echo "</tr>";
		echo "</thead>";
		
		$r = new rechnung ();
		
		$anzahl = count ( $arr );
		if ($anzahl > 0) {
			for($a = 0; $a < $anzahl; $a ++) {
				
				$belegnr = $arr [$a] ['BELEG_NR'];
				/*
				 * if(!isset($fileName)){
				 * $beleg_link = "<a href=\"?daten=rechnungen&option=rechnungs_uebersicht&belegnr=$belegnr\">Ansehen</>\n";
				 * }
				 */
				
				if (! isset ( $fileName )) {
					$beleg_link = "<a href=\"?daten=rechnungen&option=rechnungs_uebersicht&belegnr=$belegnr\">Ansehen</a>";
					$pdf_link = "<a href=\"?daten=rechnungen&option=anzeigen_pdf&belegnr=$belegnr\"><img src=\"css/pdf.png\"></a>";
					$pdf_link1 = "<a href=\"?daten=rechnungen&option=anzeigen_pdf&belegnr=$belegnr&no_logo\"><img src=\"css/pdf2.png\"></a>";
				}
				
				$r->rechnung_grunddaten_holen ( $belegnr );
				$r->rechnungs_aussteller_name = substr ( $r->rechnungs_aussteller_name, 0, 48 );
				echo "<tr><td id=\"td_ansehen\">$beleg_link $pdf_link $pdf_link1</td><td valign=\"top\">$r->empfaenger_eingangs_rnr</td><td valign=\"top\">$r->rechnungs_aussteller_name</td>";
				echo "<td valign=\"top\">$r->kurzbeschreibung</td>";
				if ($r->rechnungstyp == 'Rechnung' or $r->rechnungstyp == 'Teilrechnung' or $r->rechnungstyp == 'Schlussrechnung') {
					$r->rechnungs_brutto_a = nummer_punkt2komma ( $r->rechnungs_brutto );
					echo "<td align=\"right\" valign=\"top\">$r->rechnungs_brutto_a </td><td></td>";
					$g_brutto_r += $r->rechnungs_brutto;
					// $g_brutto_r= sprintf("%01.2f", $g_brutto_r);
				}
				if ($r->rechnungstyp == 'Gutschrift' or $r->rechnungstyp == 'Stornorechnung') {
					$r->rechnungs_brutto_a = nummer_punkt2komma ( $r->rechnungs_brutto );
					echo "<td></td><td align=\"right\" valign=\"top\">$r->rechnungs_brutto_a €</td>";
					$g_brutto_g = $g_brutto_g + $r->rechnungs_brutto;
					// $g_brutto_g= sprintf("%01.2f", $g_brutto_g);
				}
				
				$r->rechnungs_skontoabzug_a = nummer_punkt2komma ( $r->rechnungs_skontoabzug );
				echo "<td valign=\"top\"><b>$r->rechnungsnummer</b></td><td valign=\"top\">$r->rechnungsdatum</td><td align=\"right\" valign=\"top\">$r->rechnungs_skontoabzug_a</td></tr>";
				$g_netto += $r->rechnungs_netto;
				// $g_netto= sprintf("%01.2f", $g_netto);
				$g_mwst = $g_mwst + $r->rechnungs_mwst;
				// $g_mwst= sprintf("%01.2f", $g_mwst);
				
				$g_skonto = $g_skonto + $r->rechnungs_skontoabzug;
				// $g_skonto= sprintf("%01.2f", $g_skonto);
			}
			// echo "<tr><td colspan=\"9\"><hr></td></tr>";
			$g_brutto_r = nummer_punkt2komma ( $g_brutto_r );
			$g_brutto_g = nummer_punkt2komma ( $g_brutto_g );
			$g_skonto = nummer_punkt2komma ( $g_skonto );
			echo "<tfoot><tr><td id=\"td_ansehen\"></td><td></td><td></td><td></td><td align=\"right\"><b>$g_brutto_r</b></td><td align=\"right\"><b>$g_brutto_g</b></td><td><b></b></td><td></td><td align=\"right\"><b>$g_skonto</b></td></tr></tfoot>";
		} else {
			echo "<tr><td colspan=9>Keine Rechnungen in diesem Monat</td></tr>";
		}
		echo "</table>";
	}
	function eingangsrechnungen_arr($empfaenger_typ, $empfaenger_id, $monat, $jahr, $rechnungstyp) {
		// echo "<h1>$monat</h1>";
		if ($rechnungstyp == 'Rechnung') {
			$r_sql = "(RECHNUNGSTYP='$rechnungstyp' OR RECHNUNGSTYP='Stornorechnung' OR RECHNUNGSTYP='Gutschrift' OR RECHNUNGSTYP='Teilrechnung' OR RECHNUNGSTYP='Schlussrechnung')";
		} else {
			$r_sql = "RECHNUNGSTYP='$rechnungstyp'";
		}
		if ($monat == 'alle') {
			$result = mysql_query ( "SELECT * FROM RECHNUNGEN WHERE EMPFAENGER_ID='$empfaenger_id' && EMPFAENGER_TYP='$empfaenger_typ' && DATE_FORMAT( RECHNUNGSDATUM, '%Y' ) = '$jahr' && $r_sql && AKTUELL = '1' GROUP BY BELEG_NR ORDER BY EMPFAENGER_EINGANGS_RNR ASC" );
		} else {
			$result = mysql_query ( "SELECT * FROM RECHNUNGEN WHERE EMPFAENGER_ID='$empfaenger_id' && EMPFAENGER_TYP='$empfaenger_typ' && DATE_FORMAT( RECHNUNGSDATUM, '%Y-%m' ) = '$jahr-$monat' && $r_sql && AKTUELL = '1' GROUP BY BELEG_NR ORDER BY EMPFAENGER_EINGANGS_RNR ASC" );
		}
		// echo "SELECT * FROM RECHNUNGEN WHERE EMPFAENGER_ID='$empfaenger_id' && EMPFAENGER_TYP='$empfaenger_typ' && DATE_FORMAT( RECHNUNGSDATUM, '%Y' ) = '$jahr' && $r_sql && AKTUELL = '1' ORDER BY EMPFAENGER_EINGANGS_RNR DESC<hr>";
		
		$numrows = mysql_numrows ( $result );
		if ($numrows > 0) {
			while ( $row = mysql_fetch_assoc ( $result ) )
				$my_array [] = $row;
			return $my_array;
		}
	}
	function ausgangsrechnungen_arr($aussteller_typ, $aussteller_id, $monat, $jahr, $rechnungstyp) {
		// echo "<h1>$monat</h1>";
		if ($rechnungstyp == 'Rechnung') {
			$r_sql = "(RECHNUNGSTYP='$rechnungstyp' OR RECHNUNGSTYP='Stornorechnung' OR RECHNUNGSTYP='Gutschrift' OR RECHNUNGSTYP='Teilrechnung' OR RECHNUNGSTYP='Schlussrechnung')";
		} else {
			$r_sql = "RECHNUNGSTYP='$rechnungstyp'";
		}
		
		if ($monat == 'alle') {
			$result = mysql_query ( "SELECT * FROM RECHNUNGEN WHERE AUSSTELLER_ID='$aussteller_id' && AUSSTELLER_TYP='$aussteller_typ' && DATE_FORMAT( RECHNUNGSDATUM, '%Y' ) = '$jahr' && $r_sql && AKTUELL = '1' ORDER BY AUSTELLER_AUSGANGS_RNR ASC" );
		} else {
			$result = mysql_query ( "SELECT * FROM RECHNUNGEN WHERE AUSSTELLER_ID='$aussteller_id' && AUSSTELLER_TYP='$aussteller_typ' && DATE_FORMAT( RECHNUNGSDATUM, '%Y-%m' ) = '$jahr-$monat' && $r_sql && AKTUELL = '1' ORDER BY AUSTELLER_AUSGANGS_RNR DESC" );
		}
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			while ( $row = mysql_fetch_assoc ( $result ) )
				$my_array [] = $row;
			return $my_array;
		}
	}
	function r_eingang_monate_links($monat, $jahr) {
		// $monat = date("m");
		$link_p_wechseln = "<a href=\"?daten=rechnungen&option=eingangsbuch&partner_wechseln\">Partner wechseln</a>&nbsp;";
		
		echo $link_p_wechseln;
		$link_alle = "<a href=\"?daten=rechnungen&option=eingangsbuch&monat=alle&jahr=$jahr\">Alle von $jahr</a>&nbsp;";
		
		echo $link_alle;
		
		$bg = new berlussimo_global ();
		$link = "?daten=rechnungen&option=eingangsbuch";
		$bg->monate_jahres_links ( $jahr, $link );
		
		$self = $_SERVER ['QUERY_STRING'];
		echo "<a href=\"?$self&xls\">Als Excel</a>";
	}
	
	/*
	 * function r_eingang_monate_links($monat, $jahr){
	 * #$monat = date("m");
	 * $link_p_wechseln = "<a href=\"?daten=rechnungen&option=eingangsbuch&partner_wechseln\">Partner wechseln</a>&nbsp;";
	 * echo $link_p_wechseln;
	 * $link_alle = "<a href=\"?daten=rechnungen&option=eingangsbuch&monat=alle&jahr=$jahr\">Alle von $jahr</a>&nbsp;";
	 * echo $link_alle;
	 * for($a=1;$a<=$monat;$a++){
	 *
	 * $link = "<a href=\"?daten=rechnungen&option=eingangsbuch&monat=$a&jahr=$jahr\">$a/$jahr</a>&nbsp;";
	 * #echo "$a/$jahr<br>";
	 * echo "$link";
	 * }
	 * $self = $_SERVER['QUERY_STRING'];
	 * echo "<a href=\"?$self&xls\">Als Excel</a>";
	 * }
	 */
	function r_ausgang_monate_links($monat, $jahr) {
		$link_p_wechseln = "<a href=\"?daten=rechnungen&option=ausgangsbuch&partner_wechseln\">Partner wechseln</a>&nbsp;";
		echo $link_p_wechseln;
		$link_alle = "<a href=\"?daten=rechnungen&option=ausgangsbuch&monat=alle&jahr=$jahr\">Alle von $jahr</a>&nbsp;";
		echo $link_alle;
		$bg = new berlussimo_global ();
		$link = "?daten=rechnungen&option=ausgangsbuch";
		$bg->monate_jahres_links ( $jahr, $link );
		$self = $_SERVER ['QUERY_STRING'];
		echo "<a href=\"?$self&xls\">Als Excel</a>";
	}
	
	/*
	 * function r_ausgang_monate_links($monat, $jahr){
	 * $monat = date("m");
	 * $link_p_wechseln = "<a href=\"?daten=rechnungen&option=ausgangsbuch&partner_wechseln\">Partner wechseln</a>&nbsp;";
	 * echo $link_p_wechseln;
	 * $link_alle = "<a href=\"?daten=rechnungen&option=ausgangsbuch&monat=alle&jahr=$jahr\">Alle von $jahr</a>&nbsp;";
	 * echo $link_alle;
	 * for($a=1;$a<=$monat;$a++){
	 *
	 * $link = "<a href=\"?daten=rechnungen&option=ausgangsbuch&monat=$a&jahr=$jahr\">$a/$jahr</a>&nbsp;";
	 * #echo "$a/$jahr<br>";
	 * echo "$link";
	 * }
	 * $self = $_SERVER['QUERY_STRING'];
	 * echo "<a href=\"?$self&xls\">Als Excel</a>";
	 *
	 * }
	 */
} // Ende Klasse Rechnung

/* Klasse zum Blättern bzw mehrseitegen Darstellung von DB-Ergebnissen */
class blaettern {
	var $aktuelle_seite;
	// zeigt an wo man ist
	var $limit;
	// abfrageteil mit z.B. limit 0,1
	function blaettern($aktuelle_seite, $anzahl_zeilen_gesamt, $zeilen_pro_seite, $link) {
		$seiten_gesamt = intval ( $anzahl_zeilen_gesamt / $zeilen_pro_seite );
		// echo "<h3>$seiten_gesamt</h3>\n";
		$rest = $anzahl_zeilen_gesamt % $zeilen_pro_seite;
		if ($rest > 0) {
			$seiten_gesamt = $seiten_gesamt + 1;
		}
		// echo "<h3>$seiten_gesamt</h3>\n";
		/* Limit erstellung */
		if (isset ( $_REQUEST ['position'] )) {
			$this->limit = "LIMIT $_REQUEST[position],$zeilen_pro_seite";
			$aktuelle_seite = intval ( $_REQUEST ['position'] / $zeilen_pro_seite );
			$this->aktuelle_seite = $aktuelle_seite + 1;
		} else {
			$this->limit = "LIMIT 0,$zeilen_pro_seite";
			$this->aktuelle_seite = '1';
		}
		// echo "<h1>AKT $this->aktuelle_seite</h1>\n";
		
		/* Seitenlinks */
		echo "<b>Seite $this->aktuelle_seite von $seiten_gesamt</b>  -  ";
		for($i = 1; $i <= $seiten_gesamt; $i ++) {
			$position = ($i - 1) * $zeilen_pro_seite;
			if ($i == $this->aktuelle_seite) {
				echo "<a href=\"$link&position=$position\"><b>$i</b></a> ";
			} else {
				echo "<a href=\"$link&position=$position\">$i</a> ";
			}
		}
	} // end blaettern funct.
} // end class blaettern
class kontenrahmen {
	var $konten_dat;
	var $konten_id;
	var $konto;
	var $konto_bezeichnung;
	var $konto_gruppe_id;
	var $konto_gruppen_bezeichnung;
	var $konto_art_id;
	var $konto_art_bezeichnung;
	
	/* Holt Infos über ein Konto z.B. 5200 */
	function konto_informationen($konto) {
		$result = mysql_query ( "SELECT * FROM KONTENRAHMEN_KONTEN WHERE KONTO='$konto' && AKTUELL='1' ORDER BY KONTENRAHMEN_KONTEN_ID DESC LIMIT 0,1" );
		$row = mysql_fetch_assoc ( $result );
		$this->konten_dat = $row ['KONTENRAHMEN_KONTEN_DAT'];
		$this->konten_id = $row ['KONTENRAHMEN_KONTEN_ID'];
		$this->konto = $konto;
		$this->konto_bezeichnung = $row ['BEZEICHNUNG'];
		$this->gruppe_id = $row ['GRUPPE'];
		$this->konto_gruppen_bezeichnung = $this->gruppen_bezeichnung ( $this->gruppe_id );
		$this->konto_art_id = $row ['KONTO_ART'];
		$this->konto_art_bezeichnung = $this->kontoart ( $this->konto_art_id );
	}
	
	/* Holt Infos über ein Konto z.B. 5200 */
	function konto_informationen2($konto, $kontenrahmen_id) {
		$result = mysql_query ( "SELECT * FROM KONTENRAHMEN_KONTEN WHERE KONTO='$konto' && KONTENRAHMEN_ID='$kontenrahmen_id' && AKTUELL='1' ORDER BY KONTENRAHMEN_KONTEN_ID DESC LIMIT 0,1" );
		$row = mysql_fetch_assoc ( $result );
		$this->konten_dat = $row ['KONTENRAHMEN_KONTEN_DAT'];
		$this->konten_id = $row ['KONTENRAHMEN_KONTEN_ID'];
		$this->konto = $konto;
		$this->konto_bezeichnung = $row ['BEZEICHNUNG'];
		$this->gruppe_id = $row ['GRUPPE'];
		$this->konto_gruppen_bezeichnung = $this->gruppen_bezeichnung ( $this->gruppe_id );
		$this->konto_art_id = $row ['KONTO_ART'];
		$this->konto_art_bezeichnung = $this->kontoart ( $this->konto_art_id );
	}
	
	/* Holt Infos über eine Kontogruppe z.B. 1 - Reparaturen */
	function gruppen_bezeichnung($gruppen_id) {
		$result = mysql_query ( "SELECT BEZEICHNUNG FROM KONTENRAHMEN_GRUPPEN WHERE KONTENRAHMEN_GRUPPEN_ID='$gruppen_id' && AKTUELL='1' ORDER BY KONTENRAHMEN_GRUPPEN_ID DESC LIMIT 0,1" );
		$row = mysql_fetch_assoc ( $result );
		return $row ['BEZEICHNUNG'];
	}
	
	/* Holt Infos über eine Kontoart z.B. 1 - Kosten , 4 Einnahmen usw. */
	function kontoart($kontoart_id) {
		$result = mysql_query ( "SELECT KONTOART FROM KONTENRAHMEN_KONTOARTEN WHERE KONTENRAHMEN_KONTOART_ID='$kontoart_id' && AKTUELL='1' ORDER BY KONTENRAHMEN_KONTOART_ID DESC LIMIT 0,1" );
		$row = mysql_fetch_assoc ( $result );
		return $row ['KONTOART'];
	}
	function get_kontoart_id($kontoartbez) {
		$result = mysql_query ( "SELECT KONTENRAHMEN_KONTOART_ID FROM KONTENRAHMEN_KONTOARTEN WHERE KONTOART='$kontoartbez' && AKTUELL='1' ORDER BY KONTENRAHMEN_KONTOART_ID DESC LIMIT 0,1" );
		$row = mysql_fetch_assoc ( $result );
		return $row ['KONTENRAHMEN_KONTOART_ID'];
	}
	function get_gruppen_id($gruppenbez) {
		$result = mysql_query ( "SELECT KONTENRAHMEN_GRUPPEN_ID FROM KONTENRAHMEN_GRUPPEN WHERE BEZEICHNUNG='$gruppenbez' && AKTUELL='1' ORDER BY KONTENRAHMEN_GRUPPEN_DAT DESC LIMIT 0,1" );
		$row = mysql_fetch_assoc ( $result );
		return $row ['KONTENRAHMEN_GRUPPEN_ID'];
	}
	function get_konten_nach_art($kontoartbez, $k_id) {
		$kontoart_id = $this->get_kontoart_id ( $kontoartbez );
		if ($kontoart_id) {
			$result = mysql_query ( "SELECT * FROM `KONTENRAHMEN_KONTEN` WHERE `KONTO_ART` ='$kontoart_id' AND `KONTENRAHMEN_ID` ='$k_id' AND `AKTUELL` = '1' ORDER BY KONTO ASC" );
			$numrows = mysql_numrows ( $result );
			if ($numrows) {
				while ( $row = mysql_fetch_assoc ( $result ) ) {
					$my_array [] = $row;
				}
				return $my_array;
			}
		}
	}
	function get_konten_nach_art_gruppe($kontoartbez, $gruppenbez, $k_id) {
		$kontoart_id = $this->get_kontoart_id ( $kontoartbez );
		$gruppen_id = $this->get_gruppen_id ( $gruppenbez );
		if ($kontoart_id && $gruppen_id) {
			// echo "OK";
			// echo "SELECT * FROM `KONTENRAHMEN_KONTEN` WHERE `KONTO_ART` ='$kontoart_id' && GRUPPE='$gruppen_id' AND `KONTENRAHMEN_ID` ='$k_id' AND `AKTUELL` = '1' ORDER BY KONTO ASC";
			$result = mysql_query ( "SELECT * FROM `KONTENRAHMEN_KONTEN` WHERE `KONTO_ART` ='$kontoart_id' && GRUPPE='$gruppen_id' AND `KONTENRAHMEN_ID` ='$k_id' AND `AKTUELL` = '1' ORDER BY KONTO ASC" );
			$numrows = mysql_numrows ( $result );
			if ($numrows) {
				
				while ( $row = mysql_fetch_assoc ( $result ) ) {
					$my_array [] = $row;
				}
				return $my_array;
			}
		}
	}
	function kontenrahmen_uebersicht() {
		$konten_arr = $this->kontorahmen_konten_in_array ( '', '' );
		
		for($a = 0; $a < count ( $konten_arr ); $a ++) {
			$this->konto_informationen ( $konten_arr [$a] ['KONTO'] );
			
			$konten_arr [$a] [BEZEICHNUNG] = $this->konto_bezeichnung;
			$konten_arr [$a] [GRUPPE] = $this->konto_gruppen_bezeichnung;
			$konten_arr [$a] [KONTOART] = $this->konto_art_bezeichnung;
		}
		/* Feldernamen definieren - Überschrift Tabelle */
		$ueberschrift_felder_arr [0] = "Konto";
		$ueberschrift_felder_arr [1] = "Bezeichnung";
		$ueberschrift_felder_arr [2] = "Gruppe";
		$ueberschrift_felder_arr [3] = "Kontoart";
		array_als_tabelle_anzeigen ( $konten_arr, $ueberschrift_felder_arr );
	}
	
	/* Liste aller Kontorahmenkonten als array */
	function kontorahmen_konten_in_array($typ, $typ_id) {
		// echo "<h1>$typ $typ_id</h1>";
		$kontenrahmen_id = $this->get_kontenrahmen ( $typ, $typ_id );
		
		$result = mysql_query ( "SELECT KONTO, BEZEICHNUNG FROM KONTENRAHMEN_KONTEN WHERE KONTENRAHMEN_ID='$kontenrahmen_id' && AKTUELL='1' ORDER BY KONTO ASC" );
		
		$numrows = mysql_numrows ( $result );
		if ($numrows > 0) {
			while ( $row = mysql_fetch_assoc ( $result ) )
				$my_array [] = $row;
		}
		return $my_array;
	}
	
	/* Den dazugehörigen Kontenrahmen finden, egal ob Geldkonto, Partner usw. */
	function get_kontenrahmen($typ, $typ_id) {
		$result = mysql_query ( "SELECT KONTENRAHMEN_ID FROM `KONTENRAHMEN_ZUWEISUNG` WHERE TYP='$typ' && TYP_ID='$typ_id' && AKTUELL='1' ORDER BY DAT DESC LIMIT 0,1" );
		
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			$row = mysql_fetch_assoc ( $result );
			return $row ['KONTENRAHMEN_ID'];
		} else {
			/* Sonst den Kontenrahmen verwenden die keinen Kontenrahmen haben TYP='ALLE' */
			$result = mysql_query ( "SELECT KONTENRAHMEN_ID FROM `KONTENRAHMEN_ZUWEISUNG` WHERE TYP='ALLE' && AKTUELL='1' ORDER BY DAT DESC LIMIT 0,1" );
			$numrows = mysql_numrows ( $result );
			if ($numrows > 0) {
				$row = mysql_fetch_assoc ( $result );
				return $row ['KONTENRAHMEN_ID'];
			}
		}
	}
	
	/* Kontenliste als dropdown */
	function dropdown_kontorahmen_konten($name, $typ, $typ_id) {
		$konten_arr = $this->kontorahmen_konten_in_array ( $typ, $typ_id );
		echo "<select name=\"$name\" size=\"1\" id=\"kontenrahmen_konto\">\n";
		for($a = 0; $a < count ( $konten_arr ); $a ++) {
			$konto = $konten_arr [$a] ['KONTO'];
			$this->konto_informationen ( $konten_arr [$a] ['KONTO'] );
			
			// echo "<option value=\"$konto\">".$konto." - ".$this->konto_bezeichnung."</option>\n";
			echo "<option value=\"$konto\">$konto</option>\n";
		}
		echo "</select>\n";
	}
	
	/* Kontenrahmenliste als dropdown /kontierung */
	function dropdown_kontenrahmen($label, $name, $id, $js) {
		$kontenrahmen_arr = $this->kontenrahmen_in_arr ();
		echo "<label for=\"$name\" id=\"label_$name\">$label</label><select name=\"$name\" size=\"1\" id=\"$id\"  $js>\n";
		
		for($a = 0; $a < count ( $kontenrahmen_arr ); $a ++) {
			$kontenrahmen_id = $kontenrahmen_arr [$a] [KONTENRAHMEN_ID];
			$kontenrahmen_name = $kontenrahmen_arr [$a] [NAME];
			echo "<option value=\"$kontenrahmen_id\">$kontenrahmen_name</option>\n";
		}
		echo "</select>\n";
	}
	
	/* Kontenliste als dropdown /kontierung */
	function dropdown_konten_vom_rahmen($label, $name, $id, $js, $kontenrahmen_id) {
		
		// $kt->dropdown_konten_vom_rahmen('Kostenkonto', "kontenrahmen_konto", "kontenrahmen_konto", '', $kontenrahmen_id );
		$konten_arr = $this->konten_in_arr_rahmen ( $kontenrahmen_id );
		echo "<label for=\"$name\" id=\"$id\">$label</label><select name=\"$name\" size=\"1\" id=\"$id\"  $js>\n";
		
		for($a = 0; $a < count ( $konten_arr ); $a ++) {
			$konto = $konten_arr [$a] ['KONTO'];
			$this->konto_informationen2 ( $konten_arr [$a] ['KONTO'], $kontenrahmen_id );
			
			echo "<option value=\"$konto\">$konto $this->konto_bezeichnung</option>\n";
		}
		echo "</select>\n";
	}
	function kontenrahmen_in_arr() {
		$result = mysql_query ( "SELECT KONTENRAHMEN_ID, NAME FROM KONTENRAHMEN WHERE  AKTUELL='1' ORDER BY NAME ASC" );
		
		$numrows = mysql_numrows ( $result );
		if ($numrows > 0) {
			while ( $row = mysql_fetch_assoc ( $result ) )
				$my_array [] = $row;
		}
		return $my_array;
	}
	function konten_in_arr_rahmen($kontenrahmen_id) {
		$result = mysql_query ( "SELECT KONTO, BEZEICHNUNG FROM KONTENRAHMEN_KONTEN WHERE KONTENRAHMEN_ID='$kontenrahmen_id' && AKTUELL='1' ORDER BY KONTO ASC" );
		
		$numrows = mysql_numrows ( $result );
		if ($numrows > 0) {
			while ( $row = mysql_fetch_assoc ( $result ) )
				$my_array [] = $row;
		}
		return $my_array;
	}
	
	/* Kontenliste als dropdown mit Label, Id und Name */
	function dropdown_kontorahmenkonten($label, $id, $name, $typ, $typ_id, $js) {
		$konten_arr = $this->kontorahmen_konten_in_array ( $typ, $typ_id );
		echo "<label for=\"$name\" id=\"label_$name\">$label</label><select name=\"$name\" size=\"1\" id=\"$id\"  $js>\n";
		echo "<option value=\"\">Bitte wählen</option>\n";
		for($a = 0; $a < count ( $konten_arr ); $a ++) {
			$konto = $konten_arr [$a] ['KONTO'];
			$bez = $konten_arr [$a] ['BEZEICHNUNG'];
			$this->konto_informationen ( $konten_arr [$a] ['KONTO'] );
			
			// echo "<option value=\"$konto\">".$konto." - ".$this->konto_bezeichnung."</option>\n";
			echo "<option value=\"$konto\">$konto $bez</option>\n";
		}
		echo "</select>\n";
	}
	function dropdown_kontorahmenkonten_vorwahl($label, $id, $name, $typ, $typ_id, $js, $vorwahl_konto) {
		$konten_arr = $this->kontorahmen_konten_in_array ( $typ, $typ_id );
		// $js = "onchange=\"alert(this.form.name)\"";
		echo "<label for=\"$name\" id=\"label_$name\">$label</label><select name=\"$name\" size=\"1\" id=\"$id\"  $js>\n";
		echo "<option value=\"\">Bitte wählen</option>\n";
		// echo "<option value=\"0\">Konto 0</option>\n";
		for($a = 0; $a < count ( $konten_arr ); $a ++) {
			$konto = $konten_arr [$a] ['KONTO'];
			$bez = $konten_arr [$a] ['BEZEICHNUNG'];
			$this->konto_informationen ( $konten_arr [$a] ['KONTO'] );
			
			// echo "<option value=\"$konto\">".$konto." - ".$this->konto_bezeichnung."</option>\n";
			if ($vorwahl_konto == $konto) {
				echo "<option value=\"$konto\" selected>$konto $bez</option>\n";
			} else {
				echo "<option value=\"$konto\">$konto $bez</option>\n";
			}
		}
		echo "</select>\n";
	}
	
	/*
	 * SELECT *
	 * FROM GELD_KONTEN_ZUWEISUNG, GELD_KONTEN
	 * WHERE KOSTENTRAEGER_TYP = 'Objekt' && KOSTENTRAEGER_ID = '4' && GELD_KONTEN.KONTO_ID = GELD_KONTEN_ZUWEISUNG.KONTO_ID
	 * ORDER BY GELD_KONTEN.KONTO_ID ASC
	 * SELECT KONTO_ID,
	 * FROM GELD_KONTEN_ZUWEISUNG, GELD_KONTEN
	 * WHERE KOSTENTRAEGER_TYP = 'Objekt' && KOSTENTRAEGER_ID = '4' && GELD_KONTEN.KONTO_ID = GELD_KONTEN_ZUWEISUNG.KONTO_ID && GELD_KONTEN_ZUWEISUNG.AKTUELL='1' && GELD_KONTEN.AKTUELL='1'
	 * ORDER BY GELD_KONTEN.KONTO_ID ASC
	 */
} // ende class kontenrahmen
class geldkonto_info {
	/* Diese Vars werden von geld_konto_details($konto_id) gesetzt */
	var $konto_beguenstigster;
	var $kontonummer;
	var $blz;
	var $kredit_institut;
	
	/* Tabelle mit allen Geldkonten */
	function alle_geldkonten_tabelle_kontostand() {
		$result = mysql_query ( "SELECT GELD_KONTEN.KONTO_ID, GELD_KONTEN.BEZEICHNUNG, GELD_KONTEN.BEGUENSTIGTER, GELD_KONTEN.KONTONUMMER, GELD_KONTEN.BLZ, GELD_KONTEN.INSTITUT, IBAN, BIC FROM GELD_KONTEN WHERE  GELD_KONTEN.AKTUELL = '1' ORDER BY GELD_KONTEN.BEGUENSTIGTER ASC" );
		
		$numrows = mysql_numrows ( $result );
		if ($numrows > 0) {
			$zaehler = 0;
			while ( $row = mysql_fetch_assoc ( $result ) )
				$my_array [] = $row;
			
			echo "<table class=\"sortable\">";
			// echo "<tr class=\"feldernamen\"><td>KONTO ID</td><td>BEZEICHNUNG</td><td>KONTONUMMER</td><td align=right>KONTOSTAND</td></tr>";
			echo "<tr><th>KONTO</th><th>BEZEICHNUNG</th><th>BEGUENSTIGTER</th><th width=\"200\">IBAN</th><th>BIC</th><th>KONTOSTAND</th><th>OPTION</th><th>OPTION2</th></tr>";
			for($a = 0; $a < count ( $my_array ); $a ++) {
				$zaehler ++;
				$konto_id = $my_array [$a] ['KONTO_ID'];
				$konto_bezeichnung = $my_array [$a] ['BEZEICHNUNG'];
				$beguenstigter = $my_array [$a] ['BEGUENSTIGTER'];
				$kontonummer = $my_array [$a] ['KONTONUMMER'];
				$blz = $my_array [$a] ['BLZ'];
				$geld_institut = $my_array [$a] ['INSTITUT'];
				/*
				 * $sep = new sepa();
				 * $sep->get_iban_bic($kontonummer, $blz);
				 * $iban = $sep->IBAN1;
				 * $bic = $sep->BIC;
				 */
				$iban = chunk_split ( $my_array [$a] ['IBAN'], 4, ' ' );
				// $iban_1 = chunk_split($iban, 4, ' ');
				$bic = $my_array [$a] ['BIC'];
				$konto_stand_aktuell = nummer_punkt2komma_t ( $this->geld_konto_stand ( $konto_id ) );
				// $konto_stand_aktuell ='';
				$detail_link = "<a class=\"table_links\" href=\"?daten=details&option=details_anzeigen&detail_tabelle=GELD_KONTEN&detail_id=$konto_id\">Details</a>";
				$link_aendern = "<a class=\"table_links\" href=\"?daten=geldkonten&option=gk_aendern&gk_id=$konto_id\">GK ändern</a>";
				if ($zaehler == 1) {
					echo "<tr class=\"zeile1\"><td>$konto_id</td><td>$konto_bezeichnung</td><td>$beguenstigter</td><td>$iban</td><td>$bic</td><td align=right>$konto_stand_aktuell €</td><td>$detail_link</td><td>$link_aendern</td></tr>";
				}
				if ($zaehler == 2) {
					echo "<tr class=\"zeile2\"><td>$konto_id</td><td>$konto_bezeichnung</td><td>$beguenstigter</td><td>$iban</td><td>$bic</td><td align=right>$konto_stand_aktuell €</td><td>$detail_link</td><td>$link_aendern</td></tr>";
					$zaehler = 0;
				}
			}
			echo "<table>";
		} else {
			echo "<b>Keine Geldkonten vorhanden</b>";
			return FALSE;
		}
	}
	function kosten_monatlich($monat, $jahr, $geldkonto_id) {
		$letzter_tag = date ( "t", mktime ( 0, 0, 0, $monat, 1, $jahr ) );
		// echo $letzter_tag;
		$anfangsdatum = $jahr . '-' . $monat . '-1';
		$end_datum = $jahr . '-' . $monat . '-' . $letzter_tag;
		$result = mysql_query ( "SELECT SUM(GELD_KONTO_BUCHUNGEN.BETRAG) AS GESAMTKOSTEN_MONATLICH FROM GELD_KONTO_BUCHUNGEN WHERE DATUM BETWEEN '$anfangsdatum' AND '$end_datum' && GELD_KONTO_BUCHUNGEN.GELDKONTO_ID='$geldkonto_id' && AKTUELL='1' && KONTENRAHMEN_KONTO!='80001'" );
		$row = mysql_fetch_assoc ( $result );
		return $row ['GESAMTKOSTEN_MONATLICH'] . "</br>";
		// echo "$anfangsdatum bis $end_datum<br>";
	}
	function mieten_monatlich($monat, $jahr, $geldkonto_id) {
		$letzter_tag = date ( "t", mktime ( 0, 0, 0, $monat, 1, $jahr ) );
		// echo $letzter_tag;
		$anfangsdatum = $jahr . '-' . $monat . '-1';
		$end_datum = $jahr . '-' . $monat . '-' . $letzter_tag;
		$result = mysql_query ( "SELECT SUM(BETRAG) AS MIETEINNAHMEN_MONATLICH FROM GELD_KONTO_BUCHUNGEN WHERE DATUM BETWEEN '$anfangsdatum' AND '$end_datum' && GELDKONTO_ID='$geldkonto_id' && KONTENRAHMEN_KONTO='80001' && AKTUELL='1'" );
		
		$row = mysql_fetch_assoc ( $result );
		return $row ['MIETEINNAHMEN_MONATLICH'] . "</br>";
		// echo "$anfangsdatum bis $end_datum<br>";
	}
	function summe_kosten_objekt_zeitraum($geldkonto_id, $von_m, $von_j, $bis_m, $bis_j) {
		$zeit = new zeitraum ();
		$zeitraum_arr = $zeit->zeitraum_generieren ( $von_m, $von_j, $bis_m, $bis_j );
		// print_r($zeitraum_arr);
		$kosten_gesamt = '0.00';
		for($b = 0; $b < count ( $zeitraum_arr ); $b ++) {
			$monat = $zeitraum_arr [$b] [monat];
			$jahr = $zeitraum_arr [$b] [jahr];
			$kosten_gesamt = $kosten_gesamt + $this->kosten_monatlich ( $monat, $jahr, $geldkonto_id );
		}
		return $kosten_gesamt;
	}
	function summe_mieten_objekt_zeitraum($geldkonto_id, $von_m, $von_j, $bis_m, $bis_j) {
		$zeit = new zeitraum ();
		$zeitraum_arr = $zeit->zeitraum_generieren ( $von_m, $von_j, $bis_m, $bis_j );
		// print_r($zeitraum_arr);
		$kosten_gesamt = '0.00';
		for($b = 0; $b < count ( $zeitraum_arr ); $b ++) {
			$monat = $zeitraum_arr [$b] [monat];
			$jahr = $zeitraum_arr [$b] [jahr];
			$kosten_gesamt = $kosten_gesamt + $this->mieten_monatlich ( $monat, $jahr, $geldkonto_id );
		}
		return $kosten_gesamt;
	}
	
	/* Tabelle mit allen Geldkonten */
	function alle_geldkonten_tabelle() {
		$result = mysql_query ( "SELECT GELD_KONTEN.KONTO_ID, GELD_KONTEN.BEZEICHNUNG, GELD_KONTEN.BEGUENSTIGTER, GELD_KONTEN.KONTONUMMER, GELD_KONTEN.BLZ, GELD_KONTEN.INSTITUT FROM GELD_KONTEN WHERE  GELD_KONTEN.AKTUELL = '1' ORDER BY GELD_KONTEN.KONTO_ID ASC" );
		
		$numrows = mysql_numrows ( $result );
		if ($numrows > 0) {
			while ( $row = mysql_fetch_assoc ( $result ) )
				$my_array [] = $row;
			
			echo "<table class=\"sortable\">";
			// echo "<tr class=\"feldernamen\"><td>KONTO ID</td><td>BEZEICHNUNG</td><td>KONTONUMMER</td><td>MIETEINNAHMEN</td><td>KOSTEN</td><td>KONTOSTAND</td></tr>";
			echo "<tr><th>KONTO</th><th>BEZEICHNUNG</th><th>KONTONUMMER</th><th>KONTOSTAND</th></tr>";
			for($a = 0; $a < count ( $my_array ); $a ++) {
				$konto_id = $my_array [$a] ['KONTO_ID'];
				$konto_bezeichnung = $my_array [$a] ['BEZEICHNUNG'];
				$beguenstigter = $my_array [$a] ['BEGUENSTIGTER'];
				$kontonummer = $my_array [$a] ['KONTONUMMER'];
				$blz = $my_array [$a] ['BLZ'];
				$geld_institut = $my_array [$a] ['INSTITUT'];
				$summe_mieteinnahmen = $this->summe_mieteinnahmen ( $konto_id );
				// $summe_andere_buchungen = $this->summe_geld_konto_buchungen($konto_id);
				// $konto_stand_aktuell = $summe_mieteinnahmen + $summe_andere_buchungen;
				$kostengesamt = $this->summe_kosten_objekt_zeitraum ( $konto_id, '1', '2006', '4', '2009' );
				$mietengesamt = $this->summe_mieten_objekt_zeitraum ( $konto_id, '1', '2006', '4', '2009' );
				$konto_stand_monatsende = $mietengesamt + $kostengesamt;
				echo "<tr><td>$konto_id</td><td>$konto_bezeichnung</td><td>$kontonummer</td><td>$mietengesamt</td><td>$kostengesamt</td><td>$konto_stand_monatsende</td></tr>";
			}
			echo "<table>";
		} else {
			echo "<b>Keine Geldkonten vorhanden</b>";
			return FALSE;
		}
	}
	
	/* Funktion zur Erstellung eines Dropdowns für Empfangsgeldkonto */
	function dropdown_geldkonten($kostentraeger_typ, $kostentraeger_id) {
		// echo "<pre>";
		// print_r($_SESSION);
		$result = mysql_query ( "SELECT GELD_KONTEN.KONTO_ID, GELD_KONTEN.BEGUENSTIGTER, GELD_KONTEN.KONTONUMMER, GELD_KONTEN.BLZ, GELD_KONTEN.INSTITUT FROM GELD_KONTEN_ZUWEISUNG, GELD_KONTEN WHERE KOSTENTRAEGER_TYP = '$kostentraeger_typ' && KOSTENTRAEGER_ID = '$kostentraeger_id' && GELD_KONTEN.KONTO_ID = GELD_KONTEN_ZUWEISUNG.KONTO_ID && GELD_KONTEN_ZUWEISUNG.AKTUELL = '1' && GELD_KONTEN.AKTUELL = '1' ORDER BY GELD_KONTEN.KONTO_ID ASC" );
		
		$numrows = mysql_numrows ( $result );
		if ($numrows > 0) {
			while ( $row = mysql_fetch_assoc ( $result ) )
				$my_array [] = $row;
			
			echo "<label for=\"geld_konto_dropdown\">&nbsp;Bankverbindung - $kostentraeger_typ &nbsp;</label><select name=\"geld_konto\" id=\"geld_konto_dropdown\" size=\"1\" >";
			for($a = 0; $a < count ( $my_array ); $a ++) {
				$konto_id = $my_array [$a] ['KONTO_ID'];
				$beguenstigter = $my_array [$a] ['BEGUENSTIGTER'];
				$kontonummer = $my_array [$a] ['KONTONUMMER'];
				$blz = $my_array [$a] ['BLZ'];
				$geld_institut = $my_array [$a] ['INSTITUT'];
				if (isset ( $_SESSION ['geldkonto_id'] ) && $_SESSION ['geldkonto_id'] == $konto_id) {
					echo "<option value=\"$konto_id\" selected>$geld_institut - Knr:$kontonummer - Blz: $blz</option>\n";
				} else {
					echo "<option value=\"$konto_id\">$geld_institut - Knr:$kontonummer - Blz: $blz</option>\n";
				}
			}
			echo "</select>";
		} else {
			echo "<b>Kein Geldkonto hinterlegt bzw zugewiesen</b>";
			return FALSE;
		}
	}
	
	/* Funktion zur Erstellung eines Dropdowns für Empfangsgeldkonto */
	function dropdown_geldkonten_alle($label, $kostentraeger_typ, $kostentraeger_id) {
		$result = mysql_query ( "SELECT GELD_KONTEN.KONTO_ID, GELD_KONTEN.BEGUENSTIGTER, GELD_KONTEN.KONTONUMMER, GELD_KONTEN.BLZ, GELD_KONTEN.INSTITUT FROM GELD_KONTEN_ZUWEISUNG, GELD_KONTEN WHERE KOSTENTRAEGER_TYP = '$kostentraeger_typ' && KOSTENTRAEGER_ID = '$kostentraeger_id' && GELD_KONTEN.KONTO_ID = GELD_KONTEN_ZUWEISUNG.KONTO_ID && GELD_KONTEN_ZUWEISUNG.AKTUELL = '1' && GELD_KONTEN.AKTUELL = '1' ORDER BY GELD_KONTEN.KONTO_ID ASC" );
		
		$numrows = mysql_numrows ( $result );
		if ($numrows > 0) {
			while ( $row = mysql_fetch_assoc ( $result ) )
				$my_array [] = $row;
			
			echo "<label for=\"geld_konto_dropdown\">$label</label>\n<select name=\"geld_konto\" id=\"geld_konto_dropdown\" size=\"1\" >\n";
			for($a = 0; $a < count ( $my_array ); $a ++) {
				$konto_id = $my_array [$a] ['KONTO_ID'];
				$beguenstigter = $my_array [$a] ['BEGUENSTIGTER'];
				$kontonummer = $my_array [$a] ['KONTONUMMER'];
				$blz = $my_array [$a] ['BLZ'];
				$geld_institut = $my_array [$a] ['INSTITUT'];
				if (isset ( $_SESSION ['geldkonto_id'] ) && $_SESSION ['geldkonto_id'] == $konto_id) {
					echo "<option value=\"$konto_id\" selected>Knr:$kontonummer - Blz: $blz</option>\n";
				} else {
					echo "<option value=\"$konto_id\" >Knr:$kontonummer - Blz: $blz</option>\n";
				}
			} // end for
			echo "</select>\n";
		} else {
			echo "<b>Kein Geldkonto hinterlegt bzw zugewiesen</b>";
			return FALSE;
		}
	}
	
	/* Funktion zur Erstellung eines Dropdowns für Empfangsgeldkonto */
	function dropdown_geldkonten_k($label, $name, $id, $kostentraeger_typ, $kostentraeger_id) {
		$result = mysql_query ( "SELECT GELD_KONTEN.KONTO_ID, GELD_KONTEN.BEGUENSTIGTER, GELD_KONTEN.KONTONUMMER, GELD_KONTEN.BLZ, GELD_KONTEN.INSTITUT FROM GELD_KONTEN_ZUWEISUNG, GELD_KONTEN WHERE KOSTENTRAEGER_TYP = '$kostentraeger_typ' && KOSTENTRAEGER_ID = '$kostentraeger_id' && GELD_KONTEN.KONTO_ID = GELD_KONTEN_ZUWEISUNG.KONTO_ID && GELD_KONTEN_ZUWEISUNG.AKTUELL = '1' && GELD_KONTEN.AKTUELL = '1' ORDER BY GELD_KONTEN.KONTO_ID ASC" );
		
		$numrows = mysql_numrows ( $result );
		if ($numrows > 0) {
			while ( $row = mysql_fetch_assoc ( $result ) )
				$my_array [] = $row;
			
			echo "<label for=\"$id\">$label</label>\n<select name=\"$name\" id=\"$id\" size=\"1\" >\n";
			for($a = 0; $a < count ( $my_array ); $a ++) {
				$konto_id = $my_array [$a] ['KONTO_ID'];
				$beguenstigter = $my_array [$a] ['BEGUENSTIGTER'];
				$kontonummer = $my_array [$a] ['KONTONUMMER'];
				$blz = $my_array [$a] ['BLZ'];
				$geld_institut = $my_array [$a] ['INSTITUT'];
				if (isset ( $_SESSION ['geldkonto_id'] ) && $_SESSION ['geldkonto_id'] == $konto_id) {
					echo "<option value=\"$konto_id\" selected>Knr:$kontonummer - Blz: $blz</option>\n";
				} else {
					echo "<option value=\"$konto_id\" >Knr:$kontonummer - Blz: $blz</option>\n";
				}
			} // end for
			echo "</select>\n";
		} else {
			echo "<b>Kein Geldkonto hinterlegt bzw zugewiesen</b>";
			return FALSE;
		}
	}
	function geld_konto_details($konto_id) {
		$result = mysql_query ( "SELECT BEGUENSTIGTER, KONTONUMMER, BLZ, INSTITUT, BEZEICHNUNG, BIC, IBAN  FROM GELD_KONTEN WHERE KONTO_ID='$konto_id' && AKTUELL='1' ORDER BY KONTO_DAT DESC LIMIT 0,1" );
		$row = mysql_fetch_assoc ( $result );
		/*
		 * $this->konto_beguenstigter = $row['BEGUENSTIGTER'];
		 * $this->kontonummer = $row['KONTONUMMER'];
		 * $this->blz = $row['BLZ'];
		 * $this->kredit_institut = $row['INSTITUT'];
		 * $this->geldkonto_bezeichnung = $row['BEZEICHNUNG'];
		 * $this->geldkonto_bezeichnung_kurz = $this->geldkonto_bezeichnung;
		 */
		$this->IBAN = $row ['IBAN'];
		$this->IBAN1 = chunk_split ( $this->IBAN, 4, ' ' );
		$this->BIC = $row ['BIC'];
		
		$this->beguenstigter = $row ['BEGUENSTIGTER'];
		$this->konto_beguenstigter = $row ['BEGUENSTIGTER'];
		
		$this->kontonummer = $row ['KONTONUMMER'];
		$this->blz = $row ['BLZ'];
		
		$this->bankname = $row ['INSTITUT'];
		$this->institut = $row ['INSTITUT'];
		$this->kredit_institut = $row ['INSTITUT'];
		
		$this->geldkonto_bez = $row ['BEZEICHNUNG'];
		$this->geldkonto_bezeichnung = $row ['BEZEICHNUNG'];
		$this->geldkonto_bezeichnung_kurz = $this->geldkonto_bezeichnung;
	}
	
	/* Funktion zur Ermittlung der Anzahl der Geldkonten */
	function geldkonten_anzahl($kostentraeger_typ, $kostentraeger_id) {
		$result = mysql_query ( "SELECT GELD_KONTEN.KONTO_ID, GELD_KONTEN.BEGUENSTIGTER, GELD_KONTEN.KONTONUMMER, GELD_KONTEN.BLZ, GELD_KONTEN.INSTITUT FROM GELD_KONTEN_ZUWEISUNG, GELD_KONTEN WHERE KOSTENTRAEGER_TYP = '$kostentraeger_typ' && KOSTENTRAEGER_ID = '$kostentraeger_id' && GELD_KONTEN.KONTO_ID = GELD_KONTEN_ZUWEISUNG.KONTO_ID && GELD_KONTEN_ZUWEISUNG.AKTUELL = '1' && GELD_KONTEN.AKTUELL = '1' ORDER BY GELD_KONTEN.KONTO_ID ASC" );
		$numrows = mysql_numrows ( $result );
		return $numrows;
	}
	
	/* Funktion zur Ermittlung der Anzahl der Geldkonten */
	function geldkonten_arr($kostentraeger_typ, $kostentraeger_id) {
		$result = mysql_query ( "SELECT GELD_KONTEN.KONTO_ID, GELD_KONTEN.BEGUENSTIGTER, GELD_KONTEN.IBAN, GELD_KONTEN.BIC, GELD_KONTEN.KONTONUMMER, GELD_KONTEN.BLZ, GELD_KONTEN.INSTITUT  FROM GELD_KONTEN_ZUWEISUNG, GELD_KONTEN WHERE KOSTENTRAEGER_TYP = '$kostentraeger_typ' && KOSTENTRAEGER_ID = '$kostentraeger_id' && GELD_KONTEN.KONTO_ID = GELD_KONTEN_ZUWEISUNG.KONTO_ID && GELD_KONTEN_ZUWEISUNG.AKTUELL = '1' && GELD_KONTEN.AKTUELL = '1' ORDER BY GELD_KONTEN.KONTO_ID ASC" );
		$numrows = mysql_numrows ( $result );
		if ($numrows > 0) {
			while ( $row = mysql_fetch_assoc ( $result ) )
				$my_array [] = $row;
			return $my_array;
		} else {
			return FALSE;
		}
	}
	
	/* Diese Funktion ermittelt Geldkontonummern und zeigt sie im Dropdown */
	function geld_konten_ermitteln($kostentraeger_typ, $kostentraeger_id) {
		// echo "$kostentraeger_typ $kostentraeger_id<br>";
		$geldkonten_anzahl = $this->geldkonten_anzahl ( $kostentraeger_typ, $kostentraeger_id );
		if ($geldkonten_anzahl > 0) {
			$this->dropdown_geldkonten ( $kostentraeger_typ, $kostentraeger_id );
		} else {
			if ($kostentraeger_typ == 'Mietvertrag') {
				$mietvertrag_info = new mietvertrag ();
				$einheit_id = $mietvertrag_info->get_einheit_id_von_mietvertrag ( $kostentraeger_id );
				$this->geld_konten_ermitteln ( 'Einheit', $einheit_id );
				// echo "<h3>Mietvertrag $kostentraeger_id Einheit: $einheit_id </h3>";
			}
			
			if ($kostentraeger_typ == 'Einheit') {
				$einheit_info = new einheit ();
				$einheit_info->get_einheit_info ( $kostentraeger_id );
				$this->geld_konten_ermitteln ( 'Haus', $einheit_info->haus_id );
				// echo "<h3>Einheit $kostentraeger_id Haus: ".$einheit_info->haus_id." </h3>";
			}
			
			if ($kostentraeger_typ == 'Haus') {
				$haus_info = new haus ();
				$haus_info->get_haus_info ( $kostentraeger_id );
				$this->geld_konten_ermitteln ( 'Objekt', $haus_info->objekt_id );
				// echo "<h3>Haus $kostentraeger_id Objekt: ".$haus_info->objekt_id." fffff</h3>";
			}
			
			if ($kostentraeger_typ == 'Objekt') {
				// echo "BLAdfdfd $o->objekt_eigentuemer_id";
				$o = new objekt ();
				$o->get_objekt_infos ( $kostentraeger_id );
				// echo "BLA $o->objekt_eigentuemer_id";
				$this->geld_konten_ermitteln ( 'Partner', $o->objekt_eigentuemer_id );
				// $this->geld_konten_ermitteln('Objekt', $kostentraeger_id);
				// echo "<h3>Haus $kostentraeger_id Objekt: ".$haus_info->objekt_id." </h3>";
			}
		}
	}
	
	/* Diese Funktion ermittelt Geldkontonummern und seztzt die erste Kontonummer im object an */
	function geld_konto_ermitteln($kostentraeger_typ, $kostentraeger_id) {
		// echo "<h1>$kostentraeger_typ $kostentraeger_id<br>";
		$geldkonten_anzahl = $this->geldkonten_anzahl ( $kostentraeger_typ, $kostentraeger_id );
		if ($geldkonten_anzahl) {
			// $this->dropdown_geldkonten($kostentraeger_typ, $kostentraeger_id);
			$result = mysql_query ( "SELECT GELD_KONTEN.KONTO_ID, GELD_KONTEN.BEGUENSTIGTER, GELD_KONTEN.KONTONUMMER, GELD_KONTEN.BLZ, GELD_KONTEN.BEZEICHNUNG, GELD_KONTEN.INSTITUT, GELD_KONTEN.IBAN, GELD_KONTEN.BIC FROM GELD_KONTEN_ZUWEISUNG, GELD_KONTEN WHERE KOSTENTRAEGER_TYP = '$kostentraeger_typ' && KOSTENTRAEGER_ID = '$kostentraeger_id' && GELD_KONTEN.KONTO_ID = GELD_KONTEN_ZUWEISUNG.KONTO_ID && GELD_KONTEN_ZUWEISUNG.AKTUELL = '1' && GELD_KONTEN.AKTUELL = '1' ORDER BY GELD_KONTEN.KONTO_ID ASC LIMIT 0,1" );
			$numrows = mysql_numrows ( $result );
			if ($numrows) {
				$row = mysql_fetch_assoc ( $result );
				unset ( $this->geldkonto_id );
				$this->geldkonto_id = $row ['KONTO_ID'];
				$this->beguenstigter = umbruch_entfernen ( $row ['BEGUENSTIGTER'] );
				$this->kontonummer = $row ['KONTONUMMER'];
				$this->blz = $row ['BLZ'];
				$this->bez = $row ['BEZEICHNUNG'];
				$this->IBAN = $row ['IBAN'];
				$this->IBAN1 = chunk_split ( $this->IBAN, 4, ' ' );
				$this->BIC = $row ['BIC'];
				$this->geld_institut = $row ['INSTITUT'];
				/*
				 * $sep = new sepa();
				 * $sep->get_iban_bic($this->kontonummer, $this->blz);
				 * $this->BIC = $sep->BIC;
				 * $this->IBAN = $sep->IBAN;
				 * $this->IBAN1 = $sep->IBAN1; //4 stellig
				 */
			}
		} else {
			if ($kostentraeger_typ == 'Mietvertrag') {
				$mietvertrag_info = new mietvertrag ();
				$einheit_id = $mietvertrag_info->get_einheit_id_von_mietvertrag ( $kostentraeger_id );
				$this->geld_konten_ermitteln ( 'Einheit', $einheit_id );
				// echo "<h3>Mietvertrag $kostentraeger_id Einheit: $einheit_id </h3>";
			}
			
			if ($kostentraeger_typ == 'Einheit') {
				$einheit_info = new einheit ();
				$einheit_info->get_einheit_info ( $kostentraeger_id );
				$this->geld_konten_ermitteln ( 'Haus', $einheit_info->haus_id );
				// echo "<h3>Einheit $kostentraeger_id Haus: ".$einheit_info->haus_id." </h3>";
			}
			
			if ($kostentraeger_typ == 'Haus') {
				$haus_info = new haus ();
				$haus_info->get_haus_info ( $kostentraeger_id );
				$this->geld_konten_ermitteln ( 'Objekt', $haus_info->objekt_id );
				// echo "<h3>Haus $kostentraeger_id Objekt: ".$haus_info->objekt_id." </h3>";
			}
			
			if ($kostentraeger_typ == 'Objekt') {
				$o = new objekt ();
				$o->get_objekt_infos ( $kostentraeger_id );
				$this->geld_konten_ermitteln ( 'Partner', $o->objekt_eigentuemer_id );
				// echo "<h1>$kostentraeger_typ $kostentraeger_id";
			}
		}
	}
	
	/*
	 * var $objekt_id;
	 * var $objekt_name;
	 * var $haus_id;
	 * var $haus_strasse;
	 * var $haus_nummer;
	 * var $einheit_kurzname;
	 * var $einheit_qm;
	 * var $einheit_lage;
	 * var $anzahl_einheiten;
	 * var $haus_plz;
	 * var $haus_stadt;
	 * var $datum_heute;
	 * var $mietvertrag_id;
	 * function get_einheit_info($einheit_id){
	 */
	
	/* Funktionen bezogen auf Geldbewegungen auf dem Geldkonto */
	function summe_geld_konto_buchungen($geld_konto_id) {
		$result = mysql_query ( "SELECT sum( BETRAG ) AS KONTOSTAND_GELDBUCHUNGEN
FROM `GELD_KONTO_BUCHUNGEN` WHERE GELDKONTO_ID = '$geld_konto_id' && AKTUELL = '1'" );
		$numrows = mysql_numrows ( $result );
		if ($numrows > 0) {
			$row = mysql_fetch_assoc ( $result );
			return $row [KONTOSTAND_GELDBUCHUNGEN];
		} else {
			return FALSE;
		}
	}
	function summe_geld_konto_buchungen_kontiert($geld_konto_id, $kontenrahmen_konto) {
		$result = mysql_query ( "SELECT sum( BETRAG ) AS KONTOSTAND_GELDBUCHUNGEN_KONTIERT
FROM `GELD_KONTO_BUCHUNGEN` WHERE GELDKONTO_ID = '$geld_konto_id' && AKTUELL = '1' && KONTENRAHMEN_KONTO = '$kontenrahmen_konto'" );
		$numrows = mysql_numrows ( $result );
		if ($numrows > 0) {
			$row = mysql_fetch_assoc ( $result );
			return $row [KONTOSTAND_GELDBUCHUNGEN_KONTIERT];
		} else {
			return FALSE;
		}
	}
	function geld_konto_buchungen_zeitraum($geld_konto_id, $datum_von, $datum_bis) {
		$result = mysql_query ( "SELECT * AS KONTOSTAND_GELDBUCHUNGEN
FROM `GELD_KONTO_BUCHUNGEN` WHERE GELDKONTO_ID = '$geld_konto_id' && AKTUELL = '1' && DATUM BETWEEN '$datum_von' AND '$datum_bis'" );
		$numrows = mysql_numrows ( $result );
		if ($numrows > 0) {
			while ( $row = mysql_fetch_assoc ( $result ) )
				$my_array [] = $row;
			return $my_array;
		} else {
			return FALSE;
		}
	}
	
	/* Funktionen bezogen auf Mieteinnahmen auf dem Geldkonto */
	function summe_mieteinnahmen($geld_konto_id) {
		$result = mysql_query ( "SELECT sum( BETRAG ) AS SUMME_MIETEINNAHMEN
FROM `GELD_KONTO_BUCHUNGEN` WHERE GELDKONTO_ID = '$geld_konto_id' && KONTENRAHMEN_KONTO='80001' &&  AKTUELL = '1'" );
		$numrows = mysql_numrows ( $result );
		if ($numrows > 0) {
			$row = mysql_fetch_assoc ( $result );
			return $row [SUMME_MIETEINNAHMEN];
		} else {
			return FALSE;
		}
	}
	function summe_mieteinnahmen_zeitraum($geld_konto_id, $datum_von, $datum_bis) {
		$result = mysql_query ( "SELECT sum( BETRAG ) AS SUMME_MIETEINNAHMEN
FROM `GELD_KONTO_BUCHUNGEN` WHERE GELDKONTO_ID = '$geld_konto_id' && KONTENRAHMEN_KONTO='80001' && AKTUELL = '1' && DATUM BETWEEN '$datum_von' AND '$datum_bis'" );
		$numrows = mysql_numrows ( $result );
		if ($numrows > 0) {
			$row = mysql_fetch_assoc ( $result );
			return $row [SUMME_MIETEINNAHMEN];
		} else {
			return FALSE;
		}
	}
	function geld_konto_stand_ausgeben($geld_konto_id) {
		$result = mysql_query ( "SELECT sum( BETRAG ) AS KONTOSTAND
FROM `GELD_KONTO_BUCHUNGEN` WHERE GELDKONTO_ID = '$geld_konto_id' && AKTUELL = '1'" );
		$numrows = mysql_numrows ( $result );
		if ($numrows > 0) {
			$row = mysql_fetch_assoc ( $result );
			echo "<table><tr><td><h1>Kontostand: $row[KONTOSTAND] €</h1></td></tr></table>";
		} else {
			return FALSE;
		}
	}
	function geld_konto_stand($geld_konto_id) {
		$result = mysql_query ( "SELECT sum( BETRAG ) AS KONTOSTAND
FROM `GELD_KONTO_BUCHUNGEN` WHERE GELDKONTO_ID = '$geld_konto_id' && AKTUELL = '1'" );
		$numrows = mysql_numrows ( $result );
		if ($numrows > 0) {
			$row = mysql_fetch_assoc ( $result );
			return $row ['KONTOSTAND'];
		} else {
			return FALSE;
		}
	}
	function einnahmen_uebersicht_geldkonten() {
		/*
		 * SELECT GELDKONTO_ID, BEGUENSTIGTER, KONTONUMMER, BLZ, INSTITUT, SUM( BETRAG ) AS KONTOSTAND
		 * FROM `GELD_KONTO_BUCHUNGEN` JOIN ( GELD_KONTEN) ON (GELD_KONTO_BUCHUNGEN.GELDKONTO_ID = GELD_KONTEN.KONTO_ID)
		 * GROUP BY GELDKONTO_ID
		 * LIMIT 0 , 30
		 *
		 * SELECT KONTO AS GELDKONTO_ID, BEGUENSTIGTER, KONTONUMMER, BLZ, INSTITUT, SUM( BETRAG ) AS KONTOSTAND
		 * FROM `MIETE_ZAHLBETRAG`
		 * JOIN (
		 * GELD_KONTEN
		 * ) ON ( MIETE_ZAHLBETRAG.KONTO = GELD_KONTEN.KONTO_ID )
		 * GROUP BY GELDKONTO_ID
		 * LIMIT 0 , 30
		 */
	}
	function geld_konto_ein_ausgaben($geld_konto_id) {
		/*
		 * SELECT a.summe_mieteinnahmen, b.summe_anderezahlbetrage, a.summe_mieteinnahmen + b.summe_anderezahlbetrage AS KONTOSTAND
		 * FROM (
		 *
		 * SELECT sum( BETRAG ) AS summe_mieteinnahmen
		 * FROM MIETE_ZAHLBETRAG
		 * WHERE KONTO = '2'
		 * )a, (
		 *
		 * SELECT sum( BETRAG ) AS summe_anderezahlbetrage
		 * FROM GELD_KONTO_BUCHUNGEN
		 * WHERE GELDKONTO_ID = '2'
		 * )b
		 */
	}
} // ende class geldkontohttp://vwp0174.webpack.hosteurope.de/phpMyAdmin/sql.php?db=db1078767-berlus&table=GELD_KONTO_BUCHUNGEN&token=d228b9a8a8e6215d6a4953b26c215df3&goto=tbl_sql.php&back=tbl_sql.php&pos=0

/*
 * SELECT SUM( MIETE_ZAHLBETRAG.BETRAG ) AS SUMME_ALLER_MIETEN, GELD_KONTEN_ZUWEISUNG.KOSTENTRAEGER_ID
 * FROM `MIETE_ZAHLBETRAG` , GELD_KONTEN_ZUWEISUNG
 * WHERE MIETE_ZAHLBETRAG.KONTO = '7' && GELD_KONTEN_ZUWEISUNG.KONTO_ID = '7' && GELD_KONTEN_ZUWEISUNG.KOSTENTRAEGER_TYP = 'Partner'
 * GROUP BY GELD_KONTEN_ZUWEISUNG.KONTO_ID
 *
 * SELECT SUM( MIETE_ZAHLBETRAG.BETRAG ) AS SUMME_ALLER_MIETEN, SUM( RECHNUNGEN.SKONTOBETRAG ) AS SUMME_GEZAHLTER_RECHNUNGEN, GELD_KONTEN_ZUWEISUNG.KOSTENTRAEGER_ID
 * FROM `MIETE_ZAHLBETRAG` , GELD_KONTEN_ZUWEISUNG, RECHNUNGEN
 * WHERE MIETE_ZAHLBETRAG.KONTO = '7' && GELD_KONTEN_ZUWEISUNG.KONTO_ID = '7' && GELD_KONTEN_ZUWEISUNG.KOSTENTRAEGER_TYP = 'Partner' && GELD_KONTEN_ZUWEISUNG.KOSTENTRAEGER_ID = RECHNUNGEN.EMPFAENGER
 * GROUP BY GELD_KONTEN_ZUWEISUNG.KONTO_ID
 * LIMIT 0 , 30
 */
/*
 * class kasse extends rechnung{
 * var $kassen_name;
 * var $kassen_verwalter;
 * var $kassen_id;
 * var $kasse_in_rechnung_gestellt;
 * var $kasse_aus_rechnung_erhalten;
 * var $kasse_direkt_gezahlt;
 * var $kassen_stand;
 * var $kassen_forderung_offen;
 *
 * function dropdown_kassen($label, $name, $id){
 * $result = mysql_query ("SELECT KASSEN_ID, KASSEN_NAME, KASSEN_VERWALTER FROM `KASSEN` WHERE AKTUELL = '1'");
 * $numrows = mysql_numrows($result);
 * if($numrows){
 * echo "<input type=\"hidden\" name=\"empfaenger_typ\" value=\"Kasse\">";
 * echo "<label for=\"$id\">$label</label>";
 * echo "<select name=\"$name\" id=\"$id\">";
 * while($row = mysql_fetch_assoc($result)){
 * echo "<option value=\"$row[KASSEN_ID]\">$row[KASSEN_NAME] - $row[KASSEN_VERWALTER]</option>";
 * }
 * echo "</select>";
 * }else{
 * return FALSE;
 * }
 * }
 *
 * function get_kassen_info($kassen_id){
 * $result = mysql_query ("SELECT KASSEN_NAME, KASSEN_VERWALTER FROM `KASSEN` WHERE AKTUELL = '1' && KASSEN_ID='$kassen_id' ORDER BY KASSEN_DAT DESC LIMIT 0,1");
 * $numrows = mysql_numrows($result);
 * if($numrows){
 * $row = mysql_fetch_assoc($result);
 * $this->kassen_name = $row[KASSEN_NAME];
 * $this->kassen_verwalter = $row[KASSEN_VERWALTER];
 * $this->kassen_id = $row[KASSEN_ID];
 * }else{
 * return FALSE;
 * }
 * }
 * function kassen_stand($kassen_id){
 * /*Abfrage der von der Kasse gestellten gesammtsumme
 * $result = mysql_query ("SELECT SUM(SKONTOBETRAG) AS kasse_in_rechnung_gestellt FROM RECHNUNGEN WHERE AUSSTELLER_TYP='Kasse' && AUSSTELLER_ID='$kassen_id' && AKTUELL = '1'");
 * $numrows = mysql_numrows($result);
 * if($numrows){
 * $row = mysql_fetch_assoc($result);
 * $this->get_kassen_info($kassen_id);
 * $this->kasse_in_rechnung_gestellt = $row[kasse_in_rechnung_gestellt];
 * }else{
 * return FALSE;
 * }
 * /*Abfrage der an die Kasse gezahlten Gesammtsumme
 * $result = mysql_query ("SELECT SUM(SKONTOBETRAG) AS kasse_aus_rechnung_erhalten FROM RECHNUNGEN WHERE AUSSTELLER_TYP='Kasse' && AUSSTELLER_ID='$kassen_id' && STATUS_BEZAHLT='1' && AKTUELL = '1'");
 * $numrows = mysql_numrows($result);
 * if($numrows){
 * $row = mysql_fetch_assoc($result);
 * $this->get_kassen_info($kassen_id);
 * $this->kasse_aus_rechnung_erhalten = $row[kasse_aus_rechnung_erhalten];
 * }else{
 * return FALSE;
 * }
 * /*Abfrage der aus der Kasse gezahlten Gesammtsumme
 * $result = mysql_query ("SELECT SUM(SKONTOBETRAG) AS kasse_direkt_gezahlt FROM RECHNUNGEN WHERE EMPFAENGER_TYP='Kasse' && EMPFAENGER_ID='$kassen_id' && STATUS_BEZAHLT='1' && AKTUELL = '1'");
 * $numrows = mysql_numrows($result);
 * if($numrows){
 * $row = mysql_fetch_assoc($result);
 * $this->get_kassen_info($kassen_id);
 * $this->kasse_direkt_gezahlt = $row[kasse_direkt_gezahlt];
 * $this->kassen_stand = $this->kasse_aus_rechnung_erhalten - $this->kasse_direkt_gezahlt;
 * $this->kassen_forderung_offen = $this->kasse_in_rechnung_gestellt - $this->kasse_aus_rechnung_erhalten;
 * }else{
 * return FALSE;
 * }
 * }
 * function kassen_ueberblick(){
 * $result = mysql_query ("SELECT KASSEN_ID FROM `KASSEN` WHERE AKTUELL = '1'");
 * $numrows = mysql_numrows($result);
 * if($numrows){
 * echo "<table>";
 * echo "<tr><td>Kasse</td><td>Verwalter</td><td>Kassenstand</td><td>Gezahlt</td><td>Erhalten</td><td>I.R. gestellt</td></tr>";
 * while($row = mysql_fetch_assoc($result)){
 * $this->kassen_stand($row[KASSEN_ID]);
 * echo "<tr><td>$this->kassen_name</td><td>$this->kassen_verwalter</td><td>$this->kassen_stand</td><td>$this->kasse_direkt_gezahlt</td><td>$this->kasse_aus_rechnung_erhalten</td><td>$this->kasse_in_rechnung_gestellt</td></tr>";
 * }
 * echo "</table>";
 * }else{
 * return FALSE;
 * }
 * }
 *
 * }//end class kasse
 */
function join_alles() {
	/*
	 * export mv einheit_id einheit_name objektname objekt_id
	 * SELECT MIETVERTRAG.MIETVERTRAG_ID, MIETVERTRAG.EINHEIT_ID, EINHEIT.EINHEIT_KURZNAME, OBJEKT.OBJEKT_KURZNAME, OBJEKT.OBJEKT_ID
	 * FROM `MIETVERTRAG`
	 * RIGHT JOIN (
	 * EINHEIT, HAUS, OBJEKT
	 * ) ON ( MIETVERTRAG.EINHEIT_ID = EINHEIT.EINHEIT_ID && EINHEIT.HAUS_ID = HAUS.HAUS_ID && HAUS.OBJEKT_ID = OBJEKT.OBJEKT_ID )
	 * WHERE MIETVERTRAG_AKTUELL = '1' && ( MIETVERTRAG_BIS = '0000-00-00'
	 * OR MIETVERTRAG_BIS >= curdate( ) )
	 * GROUP BY MIETVERTRAG.EINHEIT_ID
	 * ORDER BY OBJEKT_KURZNAME, EINHEIT_KURZNAME ASC
	 * LIMIT 0 , 30
	 *
	 * /* SELECT MIETVERTRAG.MIETVERTRAG_BIS, MIETVERTRAG.MIETVERTRAG_ID, MIETVERTRAG.EINHEIT_ID, EINHEIT.EINHEIT_KURZNAME, EINHEIT.EINHEIT_LAGE, EINHEIT.EINHEIT_QM, OBJEKT.OBJEKT_KURZNAME, OBJEKT.OBJEKT_ID, HAUS.HAUS_STRASSE, HAUS.HAUS_NUMMER, count( MIETVERTRAG.MIETVERTRAG_ID ) AS MVS
	 * FROM `MIETVERTRAG`
	 * RIGHT JOIN (
	 * EINHEIT, HAUS, OBJEKT
	 * ) ON (
	 * MIETVERTRAG.EINHEIT_ID = EINHEIT.EINHEIT_ID && EINHEIT.HAUS_ID = HAUS.HAUS_ID && HAUS.OBJEKT_ID = OBJEKT.OBJEKT_ID
	 * )
	 * WHERE MIETVERTRAG_AKTUELL = '1' && ( MIETVERTRAG_BIS = '0000-00-00'
	 * OR MIETVERTRAG_BIS >= curdate( ) )
	 * GROUP BY EINHEIT.EINHEIT_ID
	 * ORDER BY OBJEKT_KURZNAME ASC
	 * LIMIT 30 , 30
	 */
	/* SELECT MIETVERTRAG.MIETVERTRAG_ID, MIETVERTRAG.EINHEIT_ID, EINHEIT.EINHEIT_KURZNAME, EINHEIT.EINHEIT_LAGE, EINHEIT.EINHEIT_QM, OBJEKT.OBJEKT_KURZNAME, HAUS.HAUS_STRASSE, HAUS.HAUS_NUMMER FROM `MIETVERTRAG` RIGHT JOIN (EINHEIT, HAUS, OBJEKT) ON (MIETVERTRAG.EINHEIT_ID=EINHEIT.EINHEIT_ID && EINHEIT.HAUS_ID=HAUS.HAUS_ID && HAUS.OBJEKT_ID=OBJEKT.OBJEKT_ID) WHERE MIETVERTRAG_AKTUELL='1' && (MIETVERTRAG_BIS='0000-00-00' OR MIETVERTRAG_BIS > '2008-10-31') */
	
	/*
	 * alle vermieteten einheiten
	 * SELECT MIETVERTRAG.MIETVERTRAG_ID, MIETVERTRAG.EINHEIT_ID, EINHEIT.EINHEIT_KURZNAME FROM `MIETVERTRAG` RIGHT JOIN (EINHEIT) ON (MIETVERTRAG.EINHEIT_ID=EINHEIT.EINHEIT_ID) WHERE MIETVERTRAG_AKTUELL='1' && (MIETVERTRAG_BIS='0000-00-00' OR MIETVERTRAG_BIS > '2008-10-31')
	 */
	
	/*
	 * Leerstände nach objekt beispiel für objekt 4
	 * SELECT OBJEKT_KURZNAME, EINHEIT_KURZNAME, HAUS_STRASSE, HAUS_NUMMER
	 * FROM `EINHEIT`
	 * RIGHT JOIN (
	 * HAUS, OBJEKT
	 * ) ON ( EINHEIT.HAUS_ID = HAUS.HAUS_ID && HAUS.OBJEKT_ID = OBJEKT.OBJEKT_ID )
	 * WHERE EINHEIT_ID NOT
	 * IN (
	 *
	 * SELECT EINHEIT_ID
	 * FROM MIETVERTRAG
	 * WHERE MIETVERTRAG_AKTUELL = '1' && MIETVERTRAG_BIS = '0000-00-00'
	 * )
	 * ORDER BY EINHEIT_KURZNAME ASC
	 * LIMIT 0 , 30
	 */
	
	/*
	 * Mietvertrag Einheit, einheitname, objektname und id
	 *
	 * SELECT MIETVERTRAG.MIETVERTRAG_ID, MIETVERTRAG.EINHEIT_ID, EINHEIT.EINHEIT_KURZNAME, OBJEKT.OBJEKT_KURZNAME, OBJEKT.OBJEKT_ID FROM `MIETVERTRAG` RIGHT JOIN (EINHEIT, HAUS, OBJEKT) ON (MIETVERTRAG.EINHEIT_ID=EINHEIT.EINHEIT_ID && EINHEIT.HAUS_ID=HAUS.HAUS_ID && HAUS.OBJEKT_ID=OBJEKT.OBJEKT_ID) WHERE MIETVERTRAG_AKTUELL='1' && EINHEIT_AKTUELL='1' && HAUS_AKTUELL='1' && OBJEKT_AKTUELL='1' && (MIETVERTRAG_BIS='0000-00-00' OR MIETVERTRAG_BIS > '2008-10-31')
	 *
	 * SELECT MIETVERTRAG.MIETVERTRAG_ID, MIETVERTRAG.EINHEIT_ID, EINHEIT.EINHEIT_KURZNAME, OBJEKT.OBJEKT_KURZNAME, OBJEKT.OBJEKT_ID
	 * FROM `MIETVERTRAG`
	 * RIGHT JOIN (
	 * EINHEIT, HAUS, OBJEKT
	 * ) ON ( MIETVERTRAG.EINHEIT_ID = EINHEIT.EINHEIT_ID && EINHEIT.HAUS_ID = HAUS.HAUS_ID && HAUS.OBJEKT_ID = OBJEKT.OBJEKT_ID )
	 * WHERE MIETVERTRAG_AKTUELL = '1' && EINHEIT_AKTUELL = '1' && HAUS_AKTUELL = '1' && OBJEKT_AKTUELL = '1' && ( MIETVERTRAG_BIS = '0000-00-00'
	 * OR MIETVERTRAG_BIS > '2008-10-31' )
	 */
	
	/*
	 * SELECT EINHEIT_ID, OBJEKT_KURZNAME, EINHEIT_KURZNAME
	 * FROM `EINHEIT`
	 * RIGHT JOIN (
	 * HAUS, OBJEKT
	 * ) ON ( EINHEIT.HAUS_ID = HAUS.HAUS_ID && HAUS.OBJEKT_ID = OBJEKT.OBJEKT_ID )
	 * WHERE EINHEIT_ID NOT
	 * IN (
	 *
	 * SELECT EINHEIT_ID
	 * FROM MIETVERTRAG
	 * WHERE MIETVERTRAG_AKTUELL = '1' && ( MIETVERTRAG_BIS = '0000-00-00'
	 * OR MIETVERTRAG_BIS > '2008-10-31' )
	 * ) && EINHEIT_AKTUELL = '1' && HAUS_AKTUELL = '1' && OBJEKT_AKTUELL = '1'
	 * ORDER BY EINHEIT_KURZNAME ASC
	 * LIMIT 0 , 30
	 */
	/*
	 * VERMIETETE EINHEITEN
	 * SELECT EINHEIT.EINHEIT_ID, EINHEIT.EINHEIT_KURZNAME, MIETVERTRAG_ID, COUNT(MIETVERTRAG_ID) AS MVS FROM EINHEIT right join (MIETVERTRAG) ON (EINHEIT.EINHEIT_ID=MIETVERTRAG.EINHEIT_ID)
	 * WHERE EINHEIT_AKTUELL='1' && MIETVERTRAG_AKTUELL='1' && (MIETVERTRAG_BIS = '0000-00-00' OR MIETVERTRAG_BIS > '2008-10-31' ) GROUP BY EINHEIT_ID ORDER BY `MVS` ASC
	 *
	 */
	
	/*
	 * SELECT MIETVERTRAG.MIETVERTRAG_BIS, MIETVERTRAG.MIETVERTRAG_ID, MIETVERTRAG.EINHEIT_ID, EINHEIT.EINHEIT_KURZNAME, PERSON_MIETVERTRAG.PERSON_MIETVERTRAG_PERSON_ID
	 * FROM `MIETVERTRAG`
	 * RIGHT JOIN (
	 * EINHEIT,PERSON_MIETVERTRAG) ON (
	 * MIETVERTRAG.EINHEIT_ID = EINHEIT.EINHEIT_ID && MIETVERTRAG.MIETVERTRAG_ID=PERSON_MIETVERTRAG.PERSON_MIETVERTRAG_MIETVERTRAG_ID
	 * )
	 * WHERE MIETVERTRAG_AKTUELL = '1' && ( MIETVERTRAG_BIS = '0000-00-00'
	 * OR MIETVERTRAG_BIS >= curdate( ) )
	 * GROUP BY EINHEIT.EINHEIT_KURZNAME
	 * ORDER BY `MIETVERTRAG`.`MIETVERTRAG_ID` ASC
	 */
	
	/*
	 * vergleichen des Zahlbetrages und des internverbuchten betrages
	 * select a.summe_gezahlt, b.summe_intern from
	 * ( select sum(BETRAG) as summe_gezahlt from MIETE_ZAHLBETRAG WHERE MIETVERTRAG_ID='2') a,
	 *
	 * ( select sum(BETRAG) as summe_intern from MIETBUCHUNGEN WHERE MIETVERTRAG_ID='2') b
	 *
	 */
	
	/*
	 *
	 * SELECT a.summe_mieteinnahmen, b.summe_anderezahlbetrage, a.summe_mieteinnahmen + b.summe_anderezahlbetrage AS KONTOSTAND
	 * FROM (
	 *
	 * SELECT sum( BETRAG ) AS summe_mieteinnahmen
	 * FROM MIETE_ZAHLBETRAG
	 * WHERE KONTO = '2'
	 * )a, (
	 *
	 * SELECT sum( BETRAG ) AS summe_anderezahlbetrage
	 * FROM GELD_KONTO_BUCHUNGEN
	 * WHERE GELDKONTO_ID = '2'
	 * )b
	 */
}
class lager {
	var $lagerbestand_arr;
	function lager_in_array() {
		$result = mysql_query ( "SELECT * FROM LAGER WHERE AKTUELL = '1' ORDER BY LAGER_NAME ASC" );
		$numrows = mysql_numrows ( $result );
		if ($numrows > 0) {
			while ( $row = mysql_fetch_assoc ( $result ) )
				$my_array [] = $row;
			return $my_array;
		} else {
			return false;
		}
	}
	function lager_dropdown($label, $name, $id) {
		$lager_arr = $this->lager_in_array ();
		echo "<label for=\"$id\">$label</label><select name=\"$name\" size=\"1\" id=\"$id\">\n";
		for($a = 0; $a < count ( $lager_arr ); $a ++) {
			$lager_id = $lager_arr [$a] [LAGER_ID];
			$lager_name = $lager_arr [$a] [LAGER_NAME];
			echo "<option value=\"$lager_id\">$lager_name</OPTION>\n";
		}
		echo "</select><br>\n";
	}
	function lager_bezeichnung($id) {
		$result = mysql_query ( "SELECT LAGER_NAME FROM LAGER WHERE AKTUELL = '1' && LAGER_ID='$id' ORDER BY LAGER_NAME ASC" );
		$numrows = mysql_numrows ( $result );
		if ($numrows > 0) {
			$row = mysql_fetch_assoc ( $result );
			return $row ['LAGER_NAME'];
		} else {
			return false;
		}
	}
	function get_lager_id($bez) {
		$result = mysql_query ( "SELECT LAGER_ID FROM LAGER WHERE AKTUELL = '1' && LAGER_NAME='$bez' ORDER BY LAGER_NAME ASC LIMIT 0,1" );
		$numrows = mysql_numrows ( $result );
		if ($numrows > 0) {
			$row = mysql_fetch_assoc ( $result );
			return $row ['LAGER_ID'];
		} else {
			return false;
		}
	}
	function lager_name_partner($id) {
		$result = mysql_query ( "SELECT LAGER_NAME, PARTNER_ID FROM LAGER RIGHT JOIN(LAGER_PARTNER) ON (LAGER.LAGER_ID = LAGER_PARTNER.LAGER_ID) WHERE LAGER.AKTUELL = '1' && LAGER_PARTNER.AKTUELL = '1' && LAGER.LAGER_ID='$id' ORDER BY LAGER_NAME ASC" );
		$numrows = mysql_numrows ( $result );
		if ($numrows > 0) {
			$row = mysql_fetch_assoc ( $result );
			$this->lager_name = $row ['LAGER_NAME'];
			$this->lager_partner_id = $row ['PARTNER_ID'];
		} else {
			return false;
		}
	}
	
	/*
	 * Funktion zur Erstellung des Lagerauswahlmenues
	 * $link="?daten=leerstand&option=objekt";
	 * lager_auswahl_liste($link);
	 */
	function lager_auswahl_liste($link) {
		if (isset ( $_REQUEST [lager_id] ) && ! empty ( $_REQUEST [lager_id] )) {
			$_SESSION [lager_id] = $_REQUEST [lager_id];
		}
		
		$mieten = new mietkonto ();
		$mieten->erstelle_formular ( "Lager auswählen...", NULL );
		if (isset ( $_SESSION [lager_id] )) {
			$lager_bezeichnung = $this->lager_bezeichnung ( $_SESSION [lager_id] );
			echo "<p>&nbsp;<b>Ausgewähltes Lager</b> -> $lager_bezeichnung";
		} else {
			echo "<p>&nbsp;<b>Lager auswählen</b>";
		}
		echo "<p class=\"objekt_auswahl\">";
		$lager_arr = $this->lager_in_array ();
		$anzahl_lager = count ( $lager_arr );
		
		for($i = 0; $i <= $anzahl_lager; $i ++) {
			echo "<a class=\"objekt_auswahl_buchung\" href=\"$link&lager_id=" . $lager_arr [$i] [LAGER_ID] . "\">" . $lager_arr [$i] [LAGER_NAME] . "</a>&nbsp;";
		}
		echo "</p>";
		$mieten->ende_formular ();
	}
	function artikel_suche_einkauf($artikel_nr, $empfaenger_typ, $empfaenger_id) {
		$r = new rechnung ();
		$bez = $r->kostentraeger_ermitteln ( $empfaenger_typ, $empfaenger_id );
		$result = mysql_query ( " SELECT RECHNUNGSNUMMER, RECHNUNGSDATUM, RECHNUNGEN_POSITIONEN.BELEG_NR, U_BELEG_NR, POSITION, ART_LIEFERANT, ARTIKEL_NR, MENGE, PREIS
FROM `RECHNUNGEN_POSITIONEN` , RECHNUNGEN
WHERE `ARTIKEL_NR` LIKE '%$artikel_nr%'
AND RECHNUNGEN.AKTUELL = '1'
AND RECHNUNGEN_POSITIONEN.AKTUELL = '1'
AND RECHNUNGEN.EMPFAENGER_TYP = '$empfaenger_typ' && RECHNUNGEN.EMPFAENGER_ID = '$empfaenger_id' && RECHNUNGEN_POSITIONEN.BELEG_NR = RECHNUNGEN.BELEG_NR" );
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			echo "<h3>Suchergebnis in Rechnungen von $bez  zu: $artikel_nr</h3>";
			echo "<table class=\"sortable\">";
			echo "<tr><th>LIEFERANT</th><th>ARTIKELNR</th><th>RDATUM</th><th>RNR</th><th>POSITION</th><th>BEZEICHNUNG</th><th>MENGE EINGANG</th><th>MENGE RAUS</th><th>RESTMENGE</th><th>PREIS</th></tr>";
			$g_menge = 0;
			$g_kontiert = 0;
			while ( $row = mysql_fetch_assoc ( $result ) ) {
				$p = new partners ();
				
				$r_nr = $row [RECHNUNGSNUMMER];
				$beleg_nr = $row [BELEG_NR];
				$u_beleg_nr = $row [U_BELEG_NR];
				$position = $row [POSITION];
				$art_lieferant = $row [ART_LIEFERANT];
				$p->get_partner_name ( $art_lieferant );
				$art_nr = $row [ARTIKEL_NR];
				$menge = $row [MENGE];
				$r = new rechnung ();
				$artikel_info_arr = $r->artikel_info ( $art_lieferant, $art_nr );
				$anz_bez = count ( $artikel_info_arr );
				$artikel_bez = $artikel_info_arr [0] ['BEZEICHNUNG'];
				// print_r($artikel_info_arr);
				$kontierte_menge = nummer_punkt2komma ( $r->position_auf_kontierung_pruefen ( $beleg_nr, $position ) );
				$g_kontiert += nummer_komma2punkt ( $kontierte_menge );
				$g_menge += $menge;
				$rest_menge_pos = nummer_punkt2komma ( $menge - nummer_komma2punkt ( $kontierte_menge ) );
				$rdatum = date_mysql2german ( $row [RECHNUNGSDATUM] );
				$preis = $row [PREIS];
				$r_link = "<a href=\"?daten=rechnungen&option=rechnungs_uebersicht&belegnr=$beleg_nr\">$r_nr</a>";
				echo "<tr><td>$p->partner_name</td><td>$art_nr</td><td>$rdatum</td><td>$r_link</td><td>$position</td><td>$artikel_bez</td><td>$menge</td><td>$kontierte_menge</td><td>$rest_menge_pos</td><td>$preis</td></tr>";
			}
			$g_rest = nummer_punkt2komma ( $g_menge - $g_kontiert );
			$g_menge = nummer_punkt2komma ( $g_menge );
			$g_kontiert = nummer_punkt2komma ( $g_kontiert );
			echo "<tfoot><tr ><td colspan=\"5\"><b>BESTAND</b></td><td><b>$g_menge</b></td><td><b>$g_kontiert</b></td><td><b>$g_rest</b></td><td></td></tr></tfoot>";
			echo "</table>";
		} else {
			echo "KEINE ARTIKEL GEFUNDEN $artikel_nr, $empfaenger_typ, $empfaenger_id";
		}
	}
	function artikel_suche_einkauf_form() {
		$f = new formular ();
		$f->erstelle_formular ( "Artikel im Lager suchen", NULL );
		$f->text_feld ( "Artikelnummer oder ein Teil davon eingeben", "artikel_nr", "", "35", 'artikel_nr', '' );
		$f->hidden_feld ( "option", "artikel_suche" );
		$f->send_button ( "submit", "Suchen" );
		$f->ende_formular ();
	}
	function artikel_suche($suchtext) {
		/*
		 * SELECT *
		 * FROM `RECHNUNGEN_POSITIONEN` , RECHNUNGEN
		 * WHERE `ARTIKEL_NR` LIKE '145980'
		 * AND RECHNUNGEN.AKTUELL = '1'
		 * AND RECHNUNGEN_POSITIONEN.AKTUELL = '1'
		 * AND RECHNUNGEN.EMPFAENGER_TYP = 'Lager' && RECHNUNGEN.EMPFAENGER_ID = '3' && RECHNUNGEN_POSITIONEN.BELEG_NR = RECHNUNGEN.BELEG_NR
		 * LIMIT 0 , 30
		 */
	}
	function lagerbestand_anzeigen() {
		if (! empty ( $_SESSION [lager_id] )) {
			$lager_id = $_SESSION ['lager_id'];
			mysql_query ( "SET SQL_BIG_SELECTS=1" );
			// $result = mysql_query ("SELECT RECHNUNGEN_POSITIONEN.BELEG_NR, POSITION, BEZEICHNUNG, RECHNUNGEN_POSITIONEN.ART_LIEFERANT, RECHNUNGEN_POSITIONEN.ARTIKEL_NR, COUNT( RECHNUNGEN_POSITIONEN.MENGE) AS GEKAUFTE_MENGE, RECHNUNGEN_POSITIONEN.PREIS, RECHNUNGEN_POSITIONEN.MWST_SATZ FROM RECHNUNGEN RIGHT JOIN (RECHNUNGEN_POSITIONEN, POSITIONEN_KATALOG) ON ( RECHNUNGEN.BELEG_NR = RECHNUNGEN_POSITIONEN.BELEG_NR && POSITIONEN_KATALOG.ART_LIEFERANT = RECHNUNGEN_POSITIONEN.ART_LIEFERANT && RECHNUNGEN_POSITIONEN.ARTIKEL_NR = POSITIONEN_KATALOG.ARTIKEL_NR ) WHERE EMPFAENGER_TYP = 'Lager' && EMPFAENGER_ID = '$lager_id' && RECHNUNGEN_POSITIONEN.AKTUELL='1' GROUP BY RECHNUNGEN_POSITIONEN.ARTIKEL_NR ORDER BY BEZEICHNUNG");
			
			$result = mysql_query ( "SELECT RECHNUNGEN.EINGANGSDATUM, RECHNUNGEN_POSITIONEN.BELEG_NR, POSITION, BEZEICHNUNG, RECHNUNGEN_POSITIONEN.ART_LIEFERANT, RECHNUNGEN_POSITIONEN.ARTIKEL_NR, RECHNUNGEN_POSITIONEN.MENGE AS GEKAUFTE_MENGE, RECHNUNGEN_POSITIONEN.PREIS, RECHNUNGEN_POSITIONEN.MWST_SATZ FROM RECHNUNGEN RIGHT JOIN (RECHNUNGEN_POSITIONEN, POSITIONEN_KATALOG) ON ( RECHNUNGEN.BELEG_NR = RECHNUNGEN_POSITIONEN.BELEG_NR && POSITIONEN_KATALOG.ART_LIEFERANT = RECHNUNGEN_POSITIONEN.ART_LIEFERANT && RECHNUNGEN_POSITIONEN.ARTIKEL_NR = POSITIONEN_KATALOG.ARTIKEL_NR  ) WHERE EMPFAENGER_TYP = 'Lager' && EMPFAENGER_ID = '$lager_id' && RECHNUNGEN_POSITIONEN.AKTUELL='1' && RECHNUNGEN.AKTUELL='1'  GROUP BY RECHNUNGEN_POSITIONEN.ARTIKEL_NR, BELEG_NR ORDER BY BEZEICHNUNG ASC" );
			// echo "SELECT RECHNUNGEN.EINGANGSDATUM, RECHNUNGEN_POSITIONEN.BELEG_NR, POSITION, BEZEICHNUNG, RECHNUNGEN_POSITIONEN.ART_LIEFERANT, RECHNUNGEN_POSITIONEN.ARTIKEL_NR, RECHNUNGEN_POSITIONEN.MENGE AS GEKAUFTE_MENGE, RECHNUNGEN_POSITIONEN.PREIS, RECHNUNGEN_POSITIONEN.MWST_SATZ FROM RECHNUNGEN RIGHT JOIN (RECHNUNGEN_POSITIONEN, POSITIONEN_KATALOG) ON ( RECHNUNGEN.BELEG_NR = RECHNUNGEN_POSITIONEN.BELEG_NR && POSITIONEN_KATALOG.ART_LIEFERANT = RECHNUNGEN_POSITIONEN.ART_LIEFERANT && RECHNUNGEN_POSITIONEN.ARTIKEL_NR = POSITIONEN_KATALOG.ARTIKEL_NR ) WHERE EMPFAENGER_TYP = 'Lager' && EMPFAENGER_ID = '$lager_id' && RECHNUNGEN_POSITIONEN.AKTUELL='1' && RECHNUNGEN.AKTUELL='1' GROUP BY RECHNUNGEN_POSITIONEN.ARTIKEL_NR, BELEG_NR ORDER BY BEZEICHNUNG ASC";
			
			$az = mysql_numrows ( $result );
			// az = anzahl zeilen
			if ($az) {
				while ( $row = mysql_fetch_assoc ( $result ) ) {
					$my_array [] = $row;
				}
				
				echo "<table class=\"sortable\">";
				// echo "<tr class=\"feldernamen\" align=\"right\"><td>Ansehen</td><td>Artikelnr.</td><td>Artikelbezeichnung</td><td>MENGE</td><td>RESTMENGE</td><td>PREIS</td><td>MWSt</td><td>RESTWERT</td></tr>";
				echo "<tr><th>Datum</th><th>LIEFERANT</th><th>Rechnung</th><th>Artikelnr.</th><th>Bezeichnung</th><th>Menge</th><th>rest</th><th>Preis</th><th>Mwst</th><th>Restwert</th></tr>";
				$gesamt_lager_wert = 0;
				$zaehler = 0;
				$rechnung_info = new rechnung ();
				for($a = 0; $a < count ( $my_array ); $a ++) {
					
					$datum = date_mysql2german ( $my_array [$a] [EINGANGSDATUM] );
					$beleg_nr = $my_array [$a] [BELEG_NR];
					$lieferant_id = $my_array [$a] [ART_LIEFERANT];
					$pp = new partners ();
					$pp->get_partner_name ( $lieferant_id );
					$position = $my_array [$a] [POSITION];
					$menge = $my_array [$a] [GEKAUFTE_MENGE];
					$preis = $my_array [$a] [PREIS];
					
					$kontierte_menge = $rechnung_info->position_auf_kontierung_pruefen ( $beleg_nr, $position );
					// $rechnung_info->rechnung_grunddaten_holen($beleg_nr);
					$rest_menge = $menge - $kontierte_menge;
					// $rest_menge = number_format($rest_menge,'',2,'.');
					// echo "$beleg_nr: $position. $menge - $kontierte_menge = $rest_menge<br>";
					$artikel_nr = $my_array [$a] [ARTIKEL_NR];
					$bezeichnung = $my_array [$a] [BEZEICHNUNG];
					$pos_mwst_satz = $my_array [$a] [MWST_SATZ];
					$waren_wert = ($rest_menge * $preis) / 100 * (100 + $pos_mwst_satz);
					
					$menge = nummer_punkt2komma ( $menge );
					$preis = nummer_punkt2komma ( $preis );
					$rest_menge = nummer_punkt2komma ( $rest_menge );
					$waren_wert_a = nummer_punkt2komma ( $waren_wert );
					
					$link_artikel_suche = "<a href=\"?daten=lager&option=artikel_suche&artikel_nr=$artikel_nr\">$artikel_nr</a>";
					$beleg_link = "<a href=\"?daten=rechnungen&option=rechnung_kontieren&belegnr=$beleg_nr\">Rechnung</a>";
					
					if ($rest_menge != '0,00') {
						$zaehler ++;
						$gesamt_lager_wert = $gesamt_lager_wert + $waren_wert;
						$beleg_link = "<a href=\"?daten=rechnungen&option=rechnung_kontieren&belegnr=$beleg_nr\">Rechnung</a>";
						
						if ($zaehler == '1') {
							$beleg_link = "<a href=\"?daten=rechnungen&option=rechnung_kontieren&belegnr=$beleg_nr\">Rechnung</a>";
							echo "<tr class=\"zeile1\" align=\"right\"><td>$datum</td><td>$pp->partner_name</td><td>$beleg_link</td><td>$link_artikel_suche</td><td>$bezeichnung</td><td>$menge</td><td>$rest_menge</td><td>$preis €</td><td>$pos_mwst_satz %</td><td>$waren_wert_a €</td></tr>";
						}
						
						if ($zaehler == '2') {
							$beleg_link = "<a href=\"?daten=rechnungen&option=rechnung_kontieren&belegnr=$beleg_nr\">Rechnung</a>";
							echo "<tr class=\"zeile2\" align=\"right\"><td>$datum</td><td>$pp->partner_name</td><td>$beleg_link</td><td>$link_artikel_suche</td><td>$bezeichnung</td><td>$menge</td><td>$rest_menge</td><td>$preis €</td><td>$pos_mwst_satz %</td><td>$waren_wert_a €</td></tr>";
						}
					}
					
					if ($zaehler == 2) {
						$zaehler = 0;
					}
				} // end for
				
				$gesamt_lager_wert_a = nummer_punkt2komma ( $gesamt_lager_wert );
				echo "<tr align=\"right\"><td colspan=9>Restwarenwert gesamt</td><td>$gesamt_lager_wert_a €</td></tr>";
				echo "</table>";
			} else {
				return false;
			}
		} else {
			warnung_ausgeben ( "Bitte Lager wählen" );
		}
	}
} // end class lager
?>