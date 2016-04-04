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
 * @filesource   $HeadURL: http://192.168.2.52/svn/berlussimo_1/tags/02.11.2010 - Downloadversion 0.27/classes/class_bpdf.php $
 * @version      $Revision: 15 $
 * @modifiedby   $LastChangedBy: sivac $
 * @lastmodified $Date: 2011-07-07 10:41:33 +0200 (Do, 07 Jul 2011) $
 * 
 */

/* Allgemeine Funktionsdatei laden */
if (file_exists ( "includes/allgemeine_funktionen.php" )) {
	include_once ("includes/allgemeine_funktionen.php");
}

if (file_exists ( "classes/class_sepa.php" )) {
	include_once ("classes/class_sepa.php");
}

// include_once ('classes/class_partners.php');

/* PDF KLASSE */
class b_pdf {
	function b_header(&$pdf, $partner_typ, $partner_id, $orientation = 'portrait', $font_file, $f_size, $logo_file = '') {
		// echo "$partner_typ $partner_id";
		// die('S');
		$all = $pdf->openObject ();
		$pdf->saveState ();
		$pdf->setStrokeColor ( 0, 0, 0, 1 );
		if (file_exists ( $font_file )) {
			$pdf->selectFont ( "$font_file" );
		} else {
			$pdf->selectFont ( BERLUS_PATH . "/$font_file" );
		}
		
		if ($orientation == 'portrait') {
			$pdf->ezSetMargins ( 135, 70, 50, 50 );
			if (! isset ( $_REQUEST ['no_logo'] )) {
				if ($logo_file == '') {
					$logo_file = "print_css/$partner_typ/$partner_id" . "_logo.png";
					$logo_file = BERLUS_PATH . "/print_css/$partner_typ/$partner_id" . "_logo.png";
				}
				if (file_exists ( "$logo_file" )) {
					$pdf->addPngFromFile ( "$logo_file", 200, 730, 200, 80 );
					$pdf->line ( 43, 725, 545, 725 );
					$pdf->line ( 42, 50, 550, 50 );
				} else {
					// $pdf->ezText("No Logo $logo_file");
				}
			} else {
				// $pdf->addText(43,760,$f_size,"Vorschau / Druckansicht ");
				$logo_file = "print_css/$partner_typ/$partner_id" . "_logo.png";
				$logo_file = BERLUS_PATH . "/print_css/$partner_typ/$partner_id" . "_logo.png";
			}
			$pdf->setLineStyle ( 0.5 );
			$this->footer_info ( $partner_typ, $partner_id );
			// $pdf->line(43,725,545,725);
			
			$pdf->addText ( 43, 718, $f_size, "$this->header_zeile" );
			
			$pdf->ezStartPageNumbers ( 545, 715, $f_size, '', 'Seite {PAGENUM} von {TOTALPAGENUM}', 1 );
			
			$pdf->setLineStyle ( 0.5 );
			// $pdf->line(42,50,550,50);
			
			if (! isset ( $_REQUEST ['no_logo'] )) {
				$pdf->addText ( 170, 42, $f_size, "$this->zeile1" );
				$pdf->addText ( 150, 35, $f_size, "$this->zeile2" );
			}
		} else {
			$pdf->ezSetMargins ( 120, 40, 30, 30 );
			$logo_file = "print_css/$partner_typ/$partner_id" . "_logo.png";
			$logo_file = BERLUS_PATH . "/print_css/$partner_typ/$partner_id" . "_logo.png";
			if (file_exists ( "$logo_file" )) {
				$pdf->addPngFromFile ( "$logo_file", 320, 505, 200, 80 );
			} else {
				$pdf->addText ( 370, 505, $f_size, "Vorschau / Druckansicht " );
			}
			$pdf->setLineStyle ( 0.5 );
			$this->footer_info ( $partner_typ, $partner_id );
			$pdf->line ( 43, 500, 785, 500 );
			$pdf->addText ( 43, 493, $f_size, "$this->header_zeile" );
			$pdf->ezStartPageNumbers ( 783, 493, $f_size, '', 'Seite {PAGENUM} von {TOTALPAGENUM}', 1 );
			$pdf->setLineStyle ( 0.5 );
			$pdf->line ( 42, 30, 785, 30 );
			
			$pdf->addText ( 275, 23, $f_size, "$this->zeile1" );
			$pdf->addText ( 255, 16, $f_size, "$this->zeile2" );
		}
		$pdf->restoreState ();
		$pdf->closeObject ();
		$pdf->addObject ( $all, 'all' );
	}
	function footer_info($typ, $id) {
		$result = mysql_query ( "SELECT * FROM FOOTER_ZEILE WHERE FOOTER_TYP='$typ' && FOOTER_TYP_ID='$id' && AKTUELL='1' ORDER BY  FOOTER_ID ASC LIMIT 0,1" );
		$row = mysql_fetch_assoc ( $result );
		$this->footer_typ = $row ['FOOTER_TYP'];
		$this->footer_typ_id = $row ['FOOTER_TYP_ID'];
		// $this->zahlungshinweis = strip_tags($row[ZAHLUNGSHINWEIS]);
		// $this->zahlungshinweis = strip_tags($row[ZAHLUNGSHINWEIS]);
		$this->zahlungshinweis = str_replace ( "<br>", "\n", $row ['ZAHLUNGSHINWEIS'] );
		$this->zahlungshinweis_org = $row ['ZAHLUNGSHINWEIS'];
		$this->zeile1 = $row ['ZEILE1'];
		$this->zeile2 = $row ['ZEILE2'];
		$this->header_zeile = $row ['HEADER'];
		$r = new rechnung ();
		$this->footer_partner = $r->kostentraeger_ermitteln ( $this->footer_typ, $this->footer_typ_id );
	}
	function mietentwicklung_aktuell($pdf, $mv_id) {
		$me = new mietentwicklung ();
		/* Aktuelle Miethöhe */
		$jahr = date ( "Y" );
		$monat = date ( "m" );
		$me->get_mietentwicklung_infos ( $mv_id, $jahr, $monat );
		/*
		 * $dat = $me->kostenkategorien;
		 * $pdf->ezTable($dat);
		 */
	}
	function erstelle_brief_vorlage($v_dat, $empf_typ, $empf_id_arr, $option = '0') {
		//if (file_exists ( 'pdfclass/class.ezpdf.php' )) {
		//	include_once ('pdfclass/class.ezpdf.php');
		//}
		if (file_exists ( 'classes/class_bpdf.php' )) {
			include_once ('classes/class_bpdf.php');
		}
		$anz_empf = count ( $empf_id_arr );
		if ($anz_empf > 0) {
			
			$pdf = new Cezpdf ( 'a4', 'portrait' );
			$bpdf = new b_pdf ();
			$bpdf->b_header ( $pdf, 'Partner', $_SESSION [partner_id], 'portrait', 'Helvetica.afm', 6 );
			$pdf->ezStopPageNumbers (); // seitennummerierung beenden
			
			for($index = 0; $index < sizeof ( $empf_id_arr ); $index ++) {
				
				$mv_id = $empf_id_arr [$index];
				$mv = new mietvertraege ();
				unset ( $mv->postanschrift );
				$mv->get_mietvertrag_infos_aktuell ( $mv_id );
				
				/*
				 * $me = new mietentwicklung();
				 * /*Aktuelle Miethöhe
				 */
				/*
				 * $jahr = date("Y");
				 * $monat = date("m");
				 * $me->get_mietentwicklung_infos($mv_id, $jahr, $monat);
				 * $dat = $me->kostenkategorien;
				 */
				$jahr = date ( "Y" );
				$monat = date ( "m" );
				$mkk = new mietkonto ();
				$this->aktuelle_g_miete = 0.00;
				$this->aktuelle_g_miete_arr = explode ( '|', $mkk->summe_forderung_monatlich ( $mv_id, $monat, $jahr ) );
				$this->aktuelle_g_miete = nummer_punkt2komma ( $this->aktuelle_g_miete_arr [0] );
				
				// $this->mietentwicklung_aktuell($pdf, $mv_id); //Tabelle
				$dets = new detail ();
				$mv_sepa = new sepa (); // SEPA LS Infos auf leer stellen
				                       // Infos nur von LS-teilnehmern
				if ($dets->finde_detail_inhalt ( 'MIETVERTRAG', $mv_id, 'Einzugsermächtigung' ) == 'JA') {
					$mv->ls_konto = $dets->finde_detail_inhalt ( 'MIETVERTRAG', $mv_id, 'Kontonummer-AutoEinzug' );
					$mv->ls_blz = $dets->finde_detail_inhalt ( 'MIETVERTRAG', $mv_id, 'BLZ-AutoEinzug' );
					$mv_sepa->get_iban_bic ( $mv->ls_konto, $mv->ls_blz );
				}
				
				$gk = new geldkonto_info ();
				$gk->geld_konto_ermitteln ( 'Objekt', $mv->objekt_id );
				
				$o = new objekt ();
				$o->get_objekt_infos ( $mv->objekt_id );
				// $o->objekt_eigentuemer_pdf
				
				// print_r($gk);
				// die();
				/* SEPA ERMITLUNG */
				$sepa = new sepa ();
				$sepa->get_iban_bic ( $gk->kontonummer, $gk->blz );
				$dets = new detail ();
				if (isset ( $sepa->GLAEUBIGER_ID )) {
					unset ( $sepa->GLAEUBIGER_ID );
				}
				$sepa->GLAEUBIGER_ID = $dets->finde_detail_inhalt ( 'GELD_KONTEN', $gk->geldkonto_id, 'GLAEUBIGER_ID' );
				if (! isset ( $sepa->GLAEUBIGER_ID )) {
					die ( "Bei $gk->kontonummer $mv->objekt_kurzname fehlt die Gläubiger ID" );
				}
				// echo '<pre>';
				// print_r($sepa);
				// $sepa->konto_info->IBAN1;
				// die();
				$this->get_texte ( $v_dat );
				
				// if($index>0){
				// $pdf->ezNewPage();
				// }
				
				/*
				 * echo '<pre>';
				 * print_r($mv);
				 * die();
				 */
				
				// ##############################################################
				/* Normale Mieter ohne Verzug und Zustell */
				$add = 0;
				$pa_arr = array ();
				if (count ( $mv->postanschrift ) < 1) {
					// $pdf->ezText("$mv->personen_name_string_u\n$mv->haus_strasse $mv->haus_nr\n\n$mv->haus_plz $mv->haus_stadt",12);
					// $pdf->ezText("$mv->namen\n$mv->haus_strasse $mv->haus_nr\n\n$mv->haus_plz $mv->haus_stadt",12);
					// $pdf_einzeln->ezText("$mv->namen\n$mv->haus_strasse $mv->haus_nr\n\n$mv->haus_plz $mv->haus_stadt",12);
					$pa_arr [$add] ['anschrift'] = "$mv->personen_name_string_u\n$mv->haus_strasse $mv->haus_nr\n<b>$mv->haus_plz $mv->haus_stadt</b>";
					$pa_arr [$add] ['mv_id'] = $mv_id;
					$add ++;
				}
				/* Mieter mit Verzug oder Zustell */
				if (count ( $mv->postanschrift ) == 1) {
					// OK$pa = $mv->postanschrift[0]['adresse'];
					$key_arr = array_keys ( $mv->postanschrift );
					$key = $key_arr [0];
					$pa = $mv->postanschrift [$key] ['adresse'];
					
					$pa_arr [$add] ['anschrift'] = $pa;
					$pa_arr [$add] ['mv_id'] = $mv_id;
					$add ++;
				}
				
				if (count ( $mv->postanschrift ) > 1) {
					// $pa = $mv->postanschrift[0]['adresse'];
					$anz_ad = count ( $mv->postanschrift );
					for($pp = 0; $pp < $anz_ad; $pp ++) {
						$pa_arr [$add] ['anschrift'] = $mv->postanschrift [$pp] ['adresse'];
						$pa_arr [$add] ['mv_id'] = $mv_id;
						$add ++;
					}
				}
				
				$anz_ppa = count ( $pa_arr );
				for($br = 0; $br < $anz_ppa; $br ++) {
					
					/* Kopf */
					
					$pdf_einzeln = new Cezpdf ( 'a4', 'portrait' );
					$bpdf->b_header ( $pdf_einzeln, 'Partner', $_SESSION [partner_id], 'portrait', 'Helvetica.afm', 6 );
					$pdf_einzeln->ezStopPageNumbers (); // seitennummerirung beenden
					
					/* Faltlinie */
					$pdf->setLineStyle ( 0.2 );
					$pdf_einzeln->setLineStyle ( 0.2 );
					$pdf->line ( 5, 542, 20, 542 );
					$pdf_einzeln->line ( 5, 542, 20, 542 );
					
					if (count ( $mv->postanschrift ) < 1) {
						// $pdf->addText(260,590,6,"$mv->einheit_lage",0);
						// $pdf_einzeln->addText(260,590,6,$mv->einheit_lage,0);
						// $pdf->ezText("$mv->einheit_lage",9);
						// $pdf_einzeln->ezText("$mv->einheit_lage",9);
					}
					
					$pa_1 = $pa_arr [$br] ['anschrift'];
					$mv_id_1 = $pa_arr [$br] ['mv_id'];
					$mv->get_mietvertrag_infos_aktuell ( $mv_id_1 );
					
					$pdf->addText ( 250, $pdf->y, 6, "$mv->einheit_lage", 0 );
					$pdf_einzeln->addText ( 250, $pdf->y, 6, $mv->einheit_lage, 0 );
					
					$pdf->ezText ( "$pa_1", 10 );
					$pdf_einzeln->ezText ( "$pa_1", 10 );
					
					// ##############################################################
					$pdf->ezSetDy ( - 80 );
					// $pdf->ezSetDy(-80);
					$pdf_einzeln->ezSetDy ( - 80 );
					if (! isset ( $_REQUEST ['datum'] )) {
						$datum_heute = date ( "d.m.Y" );
					} else {
						$datum_heute = $_REQUEST ['datum'];
					}
					$p = new partners ();
					$p->get_partner_info ( $_SESSION ['partner_id'] );
					
					$pdf->ezText ( "$p->partner_ort, $datum_heute", 9, array (
							'justification' => 'right' 
					) );
					$pdf->ezText ( "<b>Objekt: $mv->haus_strasse $mv->haus_nr, $mv->haus_plz $mv->haus_stadt</b>", 9 );
					if (! isset ( $mv->postanschrift )) {
						$pdf->ezText ( "<b>Einheit: $mv->einheit_kurzname</b>", 9 );
					} else {
						$pdf->ezText ( "<b>Einheit: $mv->einheit_kurzname (Mieter: $mv->personen_name_string)</b>", 9 );
					}
					$pdf->ezText ( "<b>$this->v_kurztext</b>", 9 );
					$pdf->ezSetDy ( - 30 );
					$pdf->ezText ( "$mv->mv_anrede", 9 );
					// $meine_var{$this->v_text_org} = $this->v_text;
					// echo $meine_var{$this->v_text_org};
					eval ( "\$this->v_text = \"$this->v_text\";" ); // Variable ausm Text füllen
					                                              
					// die($this->v_text);
					                                              
					// $pdf->ezText("$this->v_text", 12, array('justification'=>'full'));
					$pdf->ezText ( "$this->v_text", 9 );
					
					$pdf_einzeln->ezText ( "$p->partner_ort, $datum_heute", 11, array (
							'justification' => 'right' 
					) );
					$pdf_einzeln->ezText ( "<b>Objekt: $mv->haus_strasse $mv->haus_nr, $mv->haus_plz $mv->haus_stadt</b>", 12 );
					$pdf_einzeln->ezText ( "<b>Einheit: $mv->einheit_kurzname</b>", 11 );
					$pdf_einzeln->ezText ( "<b>$this->v_kurztext</b>", 11 );
					$pdf_einzeln->ezSetDy ( - 30 );
					$pdf_einzeln->ezText ( "$mv->mv_anrede", 11 );
					$txt_alt = $this->v_text;
					// $txt_neu = eval($txt_alt);
					$pdf_einzeln->ezText ( "$this->v_text", 11, array (
							'justification' => 'full' 
					) );
					// die();
					$this->pdf_speichern ( "SERIENBRIEFE/$_SESSION[username]", "$mv->einheit_kurzname - $this->v_kurztext vom $datum_heute" . '.pdf', $pdf_einzeln->output () );
					
					if ($index < sizeof ( $empf_id_arr ) - 1) {
						// $pdf->ezNewPage();
						// $pdf_einzeln->ezNewPage();
					}
					
					$pdf->ezNewPage ();
					$pdf_einzeln->ezNewPage ();
				}
			}
			
			/* erste packen und gz erstellen */
			$dir = 'SERIENBRIEFE';
			$tar_dir_name = "$dir/$_SESSION[username]";
			
			if (! file_exists ( $tar_dir_name )) {
				mkdir ( $tar_dir_name, 0777 );
			}
			
			exec ( "tar cfvz $tar_dir_name/Serienbrief.tar.gz $tar_dir_name/*.pdf" );
			exec ( "rm $tar_dir_name/*.pdf" );
			
			if (isset ( $_REQUEST ['emailsend'] )) {
				/* Als Email versenden */
				$from = 'serienbrief@berlus.de';
				$to = 'info@berlus.de';
				$DATEI = "$tar_dir_name/Serienbrief.tar.gz";
				$content_type = 'application/x-tar';
				$subject = "Serienbriefe $this->v_kurztext vom $datum_heute - $_SESSION[username]";
				
				// Do not change anything from here
				
				$random_hash = md5 ( date ( 'r', time () ) );
				
				$headers = "From: " . $from . "\r\nReply-To: " . $from;
				$headers .= "\r\nContent-Type: multipart/mixed; boundary=\"PHP-mixed-" . $random_hash . "\"";
				$attachment = chunk_split ( base64_encode ( file_get_contents ( $DATEI ) ) );
				
				$message = "--PHP-mixed-" . $random_hash . "\n" . "Content-Type: multipart/alternative; boundary=\"PHP-alt-" . $random_hash . "\"\n\n" . "--PHP-alt-" . $random_hash . "\n" . "Content-Type: text/plain; charset=\"UTF-8\"\n" . "Content-Transfer-Encoding: 7bit\n\n" . "Serienbriefe im Anhang.\n" . "\n\n" . "--PHP-alt-" . $random_hash . "--\n\n" . "--PHP-mixed-" . $random_hash . "\n" . "Content-Type: " . $content_type . "; name=\"$this->v_kurztext vom $datum_heute.tar.gz\"\n" . "Content-Transfer-Encoding: base64\n" . "Content-Disposition: attachment\n\n" . $attachment . "\n" . "--PHP-mixed-" . $random_hash . "--\n\n";
				
				/* Wenn Email versendet, dann PDF ANZEIGEN, sonst die(fehler)"; */
				/*
				 * if(@mail( $to, $subject, $message, $headers )){
				 * exec("rm $tar_dir_name/Serienbrief.tar.gz");
				 * /*Ausgabe
				 */
				// ob_clean(); //ausgabepuffer leeren
				// header("Content-type: application/pdf"); // wird von MSIE ignoriert
				// $pdf->ezStream();
				// }*/
				/*
				 * else{
				 * exec("rm $tar_dir_name/Serienbrief.tar.gz");
				 * die("Email konnte nicht versendet werden");
				 * }
				 */
				/* das Raus */
				ob_clean (); // ausgabepuffer leeren
				header ( "Content-type: application/pdf" ); // wird von MSIE ignoriert
				                                         // $pdf->ezStream();
				
				$dateiname = "\"$datum_heute - Serie - $this->v_kurztext.pdf\"";
				$pdf_opt ['Content-Disposition'] = $dateiname;
				$pdf->ezStream ( $pdf_opt );
			}  // emalsend
else {
				/* Kein Emailversand angefordert, nur ansehen */
				/* Ausgabe */
				ob_clean (); // ausgabepuffer leeren
				header ( "Content-type: application/pdf" ); // wird von MSIE ignoriert
				                                         // $pdf->ezStream();
				$dateiname = "\"$datum_heute - Serie - $this->v_kurztext.pdf\"";
				$pdf_opt ['Content-Disposition'] = $dateiname;
				$pdf->ezStream ( $pdf_opt );
			}
		} else {
			die ( 'Keine Empfänger gewählt' );
		}
	}
	function get_texte($v_dat) {
		$result = mysql_query ( "SELECT * FROM PDF_VORLAGEN WHERE DAT='$v_dat'" );
		$row = mysql_fetch_assoc ( $result );
		$this->v_kurztext = $row ['KURZTEXT'];
		$this->v_text = $row ['TEXT'];
		$this->v_kat = $row ['KAT'];
		$this->v_empf_typ = $row ['EMPF_TYP'];
	}
	function form_mieter2sess() {
		$f = new formular ();
		$f->erstelle_formular ( "Mieter wählen", NULL );
		$this->mieter_checkboxen ();
		$f->send_button ( "submit", "Hinzufügen" );
		$f->ende_formular ();
	}
	function mieter_checkboxen() {
		$f = new formular ();
		if (isset ( $_POST ['delete'] )) {
			unset ( $_SESSION ['serienbrief_mvs'] );
		}
		
		if (isset ( $_POST ['vorlage'] ) && is_array ( $_SESSION ['serienbrief_mvs'] )) {
			echo "Vorlage wählen";
			if (isset ( $_REQUEST ['kat'] )) {
				$this->vorlage_waehlen ( null, $_REQUEST ['kat'] );
			} else {
				$this->vorlage_waehlen ();
			}
		}
		
		if (isset ( $_POST ['mv_ids'] ) && is_array ( $_POST ['mv_ids'] )) {
			for($index = 0; $index < sizeof ( $_POST [mv_ids] ); $index ++) {
				$mv_id_add = $_POST [mv_ids] [$index];
				if (is_array ( $_SESSION [serienbrief_mvs] )) {
					if (! in_array ( $mv_id_add, $_SESSION [serienbrief_mvs] )) {
						$_SESSION [serienbrief_mvs] [] = $mv_id_add;
					}
				} else {
					$_SESSION [serienbrief_mvs] [] = $mv_id_add;
				}
			}
		}
		
		if (isset ( $_SESSION ['serienbrief_mvs'] ) && is_array ( $_SESSION ['serienbrief_mvs'] )) {
			echo "<table class=\"sortable\">";
			echo "<tr><th>Einheit</th><th>Mieter</th></tr>";
			for($a = 0; $a < count ( $_SESSION ['serienbrief_mvs'] ); $a ++) {
				$mv = new mietvertraege ();
				$mv_id = $_SESSION ['serienbrief_mvs'] [$a];
				$mv->get_mietvertrag_infos_aktuell ( $mv_id );
				echo "<tr><td>$mv->einheit_kurzname</td><td>$mv->personen_name_string</td></tr>";
				// echo "$mv->einheit_kurzname - $mv->personen_name_string".'<br>';
			}
			echo "</table>";
			$f->send_button ( "delete", "Alle Löschen" );
			$f->send_button ( "vorlage", "Vorlage Wählen" );
		}
		
		$f = new formular ();
		include_once ('classes/class_mahnungen.php');
		$m = new mahnungen ();
		$aktuelle_mvs = $m->finde_aktuelle_mvs ();
		// $aktuelle_mvs = $m->finde_alle_mvs();
		// onClick=\"this.value=check(this.form.positionen_list)\"
		if (is_array ( $aktuelle_mvs )) {
			$f->check_box_js_alle ( 'nn', 'nn', 'NN', 'Alle markieren', '', '', 'mv_ids' );
			for($index = 0; $index < sizeof ( $aktuelle_mvs ); $index ++) {
				$mv_id = $aktuelle_mvs [$index] ['MIETVERTRAG_ID'];
				$mv = new mietvertraege ();
				$mv->get_mietvertrag_infos_aktuell ( $mv_id );
				// echo "$mv->einheit_kurzname - $mv->personen_name_string".'<br>';
				if (isset ( $_SESSION ['serienbrief_mvs'] )) {
					if (! in_array ( $mv_id, $_SESSION ['serienbrief_mvs'] )) {
						$f->check_box_js1 ( 'mv_ids[]', 'mv_id_boxen', $mv_id, "$mv->einheit_kurzname - $mv->personen_name_string", '', '' );
					}
				} else {
					$f->check_box_js1 ( 'mv_ids[]', 'mv_id_boxen', $mv_id, "$mv->einheit_kurzname - $mv->personen_name_string", '', '' );
				}
			}
		} else {
			die ( "Keine Mieter" );
		}
	}
	function vorlage_waehlen($empf_typ = null, $kat = null) {
		if ($empf_typ == null && $kat == null) {
			$db_abfrage = "SELECT * FROM PDF_VORLAGEN ORDER BY KURZTEXT ASC";
		}
		
		if ($empf_typ && $kat == null) {
			$db_abfrage = "SELECT * FROM PDF_VORLAGEN WHERE EMPF_TYP='$empf_typ' ORDER BY KURZTEXT ASC";
		}
		
		if ($empf_typ == null && $kat) {
			$db_abfrage = "SELECT * FROM PDF_VORLAGEN WHERE KAT='$kat' ORDER BY KURZTEXT ASC";
		}
		
		if ($empf_typ != null && $kat != null) {
			$db_abfrage = "SELECT * FROM PDF_VORLAGEN WHERE KAT='$kat' && EMPF_TYP='$empf_typ' ORDER BY KURZTEXT ASC";
		}
		
		$result = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			start_table ();
			$link_kat = "<a href=\"?daten=bk&option=serienbrief\">Alle Kats anzeigen</a>";
			echo "<tr><th>Vorlage / Betreff</th><th>BEARBEITEN</th><th>KAT</th><th>ANSEHEN</th><th>ERSTELLEN</th></tr>";
			echo "<tr><td><b>$empf_typ<b></td><td>$link_kat</td><td></td><td></td><td></td></tr>";
			while ( $row = mysql_fetch_assoc ( $result ) ) {
				$dat = $row ['DAT'];
				$kurztext = $row ['KURZTEXT'];
				$text = $row ['TEXT'];
				$kat = $row ['KAT'];
				$link_erstellen = "<a href=\"?daten=bk&option=serienbrief_pdf&vorlagen_dat=$dat&emailsend\">Serienbrief erstellen (PDF & Email)</a>";
				$link_ansehen = "<a href=\"?daten=bk&option=serienbrief_pdf&vorlagen_dat=$dat\">Serienbrief ansehen</a>";
				$link_bearbeiten = "<a href=\"?daten=bk&option=vorlage_bearbeiten&vorlagen_dat=$dat\">Vorlage bearbeiten</a>";
				$link_kat = "<a href=\"?daten=bk&option=serienbrief&kat=$kat\">$kat</a>";
				
				echo "<tr><td>$kurztext</td><td>$link_kat</td><td>$link_bearbeiten</td><td>$link_ansehen</td><td>$link_erstellen</td></tr>";
				// echo "$link";
			}
			end_table ();
		} else {
			echo "Keine Vorlagen";
		}
	}
	function dropdown_kats($label, $name, $id, $js, $vorwahl = '') {
		$db_abfrage = "SELECT KAT FROM PDF_VORLAGEN GROUP BY KAT ORDER BY KAT ASC";
		$result = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			echo "<label for=\"$id\">$label</label>";
			echo "<select name=\"$name\" size=\"1\" id=\"$id\" $js>\n";
			echo "<option value=\"NEU\" selected>NEU</option>\n";
			
			while ( $row = mysql_fetch_assoc ( $result ) ) {
				$kat = $row ['KAT'];
				if ($vorwahl == $kat) {
					echo "<option value=\"$kat\" selected>$kat</option>\n";
				} else {
					echo "<option value=\"$kat\">$kat</option>\n";
				}
			}
			echo "</select>";
		} else {
			echo "Keine $label XX3";
		}
	}
	function dropdown_typ($label, $name, $id, $js, $vorwahl = '') {
		$db_abfrage = "SELECT EMPF_TYP AS KAT FROM PDF_VORLAGEN GROUP BY EMPF_TYP ORDER BY KAT ASC";
		$result = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			echo "<label for=\"$id\">$label</label>";
			echo "<select name=\"$name\" size=\"1\" id=\"$id\" $js>\n";
			
			while ( $row = mysql_fetch_assoc ( $result ) ) {
				$kat = $row ['KAT'];
				if ($vorwahl == $kat) {
					echo "<option value=\"$kat\" selected>$kat</option>\n";
				} else {
					echo "<option value=\"$kat\">$kat</option>\n";
				}
			}
			echo "</select>";
		} else {
			echo "Keine $label XX1";
		}
	}
	function form_vorlage_neu() {
		$f = new formular ();
		$f->erstelle_formular ( "Neue Serienbriefvorlage erfassen", NULL );
		$this->dropdown_kats ( 'Kategorie', 'kat', 'kat', '', '' );
		$f = new formular ();
		$f->text_feld ( 'Neue Kategorie', 'kat_man', null, 50, 'kat_man', '' );
		$this->dropdown_typ ( 'Empfängergruppe', 'empf_typ', 'empf_typ', '', '' );
		$f->text_feld ( 'Betreff', 'kurztext', '', 100, 'kurztext', '' );
		$f->text_bereich ( 'Text', 'text', '', 50, 50, 'text' );
		$f->hidden_feld ( "option", "serienbrief_vorlage_send" );
		$f->send_button ( "submit", "Speichern" );
		$f->ende_formular ();
	}
	function check_v_exists($kurztext, $text) {
		$result = mysql_query ( "SELECT *  FROM  PDF_VORLAGEN WHERE KURZTEXT='$kurztext' && TEXT='$text' " );
		$numrows = mysql_numrows ( $result );
		
		if ($numrows) {
			return true;
		}
	}
	function vorlage_speichern($kurztext, $text, $kat = 'Alle', $empf_typ = 'Mieter') {
		if (! $this->check_v_exists ( $kurztext, $text )) {
			$db_abfrage = "INSERT INTO PDF_VORLAGEN VALUES (NULL, '$kurztext', '$text', '$empf_typ', '$kat', '1')";
			$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
			/* Protokollieren */
			$last_dat = mysql_insert_id ();
			protokollieren ( 'PDF_VORLAGEN', $last_dat, '0' );
		} else {
			// echo "Vorlge exisitiert schon";
		}
	}
	function vorlage_update($dat, $kurztext, $text, $kat = 'Alle', $empf_typ = 'Mieter') {
		$db_abfrage = "UPDATE PDF_VORLAGEN SET KURZTEXT= '$kurztext', TEXT= '$text', KAT='$kat', EMPF_TYP='$empf_typ' WHERE DAT='$dat'";
		$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		/* Protokollieren */
		$last_dat = mysql_insert_id ();
		protokollieren ( 'PDF_VORLAGEN', $dat, $dat );
	}
	function pdf_speichern($dir, $dateiname, $pdfcode) {
		// $pdfcode = $pdf->output();
		// save the file
		if (! file_exists ( $dir )) {
			mkdir ( $dir, 0777 );
		}
		$fp = fopen ( $dir . '/' . $dateiname, 'w' );
		fwrite ( $fp, $pdfcode );
		fclose ( $fp );
	}
	function form_vorlage_edit($dat) {
		$this->get_texte ( $dat );
		$f = new formular ();
		$f->erstelle_formular ( "Serienbriefvorlage bearbeiten", NULL );
		$this->dropdown_kats ( 'Kategorie', 'kat', 'kat', '', $this->v_kat );
		$this->dropdown_typ ( 'Empfängergruppe', 'empf_typ', 'empf_typ', '', $this->v_empf_typ );
		$f->text_feld ( 'Betreff', 'kurztext', $this->v_kurztext, 100, 'kurztext', '' );
		$f->text_bereich ( 'Text', 'text', $this->v_text, 50, 50, 'text' );
		$f->hidden_feld ( "dat", "$dat" );
		$f->hidden_feld ( "option", "serienbrief_vorlage_send1" );
		$f->send_button ( "submit", "Speichern" );
		$f->ende_formular ();
	}
	function form_serienbrief_an($empfaenger) {
		$f = new formular ();
		$f->erstelle_formular ( "$empfaenger wählen", NULL );
		// $this->mieter_checkboxen();
		$this->checkboxen_auswahl ( $empfaenger );
		$f->hidden_feld ( "option", "empfaenger2sess" );
		$f->send_button ( "submit", "Hinzufügen" );
		$f->ende_formular ();
	}
	function checkboxen_auswahl($empfaenger) {
		$f = new formular ();
		if ($empfaenger == 'Partner') {
			$p = new partners ();
			$arr = $p->partner_in_array ();
			$anz = count ( $arr );
			if ($anz > 0) {
				
				for($a = 0; $a < $anz; $a ++) {
					$p1 = ( object ) $arr [$a];
					// echo '<pre>';
					// print_r($p1);
					// die();
					if (is_array ( $_SESSION [empfaenger_ids] )) {
						if (! in_array ( $p1->PARTNER_ID, $_SESSION [empfaenger_ids] )) {
							$f->check_box_js ( 'empf_ids[]', $p1->PARTNER_ID, "$p1->PARTNER_NAME $p1->STRASSE $p1->NUMMER, $p1->PLZ $p1->ORT", '', '' );
						}
					} else {
						$f->check_box_js ( 'empf_ids[]', $p1->PARTNER_ID, "$p1->PARTNER_NAME $p1->STRASSE $p1->NUMMER, $p1->PLZ $p1->ORT", '', '' );
					}
					/*
					 * if(!in_array($mv_id_add, $_SESSION[serienbrief_mvs])){
					 * $_SESSION[serienbrief_mvs][] = $mv_id_add;
					 * }
					 * }else{
					 * $_SESSION[serienbrief_mvs][] = $mv_id_add;
					 * }
					 *
					 */
				}
			} else {
				die ( 'Keine Partner im System' );
			}
		}
		if ($empfaenger == 'Objekt') {
		}
		if ($empfaenger == 'Haus') {
			$f->hidden_feld ( "empfaenger_typ", "$empfaenger" );
			$h = new haus ();
			$arr = $h->liste_aller_haeuser ();
			$anz = count ( $arr );
			if ($anz > 0) {
				for($a = 0; $a < $anz; $a ++) {
					$haus_str = $arr [$a] ['HAUS_STRASSE'];
					$haus_nr = $arr [$a] ['HAUS_NUMMER'];
					$haus_id = $arr [$a] ['HAUS_ID'];
					
					if (is_array ( $_SESSION [empfaenger_ids] )) {
						if (! in_array ( $haus_id, $_SESSION [empfaenger_ids] )) {
							$f->check_box_js ( 'empf_ids[]', $haus_id, "$haus_str $haus_nr", '', '' );
						}
					} else {
						$f->check_box_js ( 'empf_ids[]', $haus_id, "$haus_str $haus_nr", '', '' );
					}
				}
			}
		}
		if ($empfaenger == 'exMieter') {
		}
	}
	function pdf_heizungabnahmeprotokoll($pdf, $mv_id, $einzug = null) {
		$mv = new mietvertraege ();
		$mv->get_mietvertrag_infos_aktuell ( $mv_id );
		$pdf->ezText ( "<b>Wohnungs-Nr:</b> $mv->einheit_kurzname", 10, array (
				'justification' => 'right' 
		) );
		$pdf->ezText ( "<b>Mieter:</b> $mv->personen_name_string", 10 );
		$pdf->ezText ( "<b>Wohnung:</b> $mv->haus_strasse $mv->haus_nr, $mv->haus_plz $mv->haus_stadt  <b>Wohnlage:</b> $mv->einheit_lage", 10 );
		
		$pdf->ezSetDy ( - 15 ); // Abstand
		$tab [0] ['RAUM'] = 'Küche';
		$tab [1] ['RAUM'] = 'Bad';
		$tab [2] ['RAUM'] = '1. Zimmer';
		$tab [3] ['RAUM'] = '2. Zimmer';
		$tab [4] ['RAUM'] = '3. Zimmer';
		$tab [5] ['RAUM'] = '4. Zimmer';
		$tab [6] ['RAUM'] = '';
		$tab [7] ['RAUM'] = '';
		$tab [8] ['RAUM'] = '';
		$tab [9] ['RAUM'] = '';
		$tab [10] ['RAUM'] = '';
		$tab [11] ['RAUM'] = '';
		$tab [12] ['RAUM'] = '';
		
		$tabw [0] ['RAUM'] = 'Kaltwasser Bad';
		$tabw [1] ['RAUM'] = 'Warmwasser Bad';
		$tabw [2] ['RAUM'] = 'Kaltwasser Küche';
		$tabw [3] ['RAUM'] = 'Warmwasser Küche';
		$tabw [4] ['RAUM'] = '';
		$tabw [5] ['RAUM'] = '';
		$tabw [6] ['RAUM'] = '';
		$tabw [7] ['RAUM'] = '';
		$tabw [8] ['RAUM'] = '';
		
		$cols = array (
				'RAUM' => "Raum",
				'GERAET_NR' => "Geräte-Nr.",
				'ALT' => "M-WERT(alt)",
				'NEU' => "IST-WERT(neu)" 
		);
		
		if ($einzug == null) {
			$title = "Anlage zum Wohnungsabnahmeprotokoll | Ablesung der Heizung";
		} else {
			$title = "Anlage zum Wohnungsübergabeprotokoll | Ablesung der Heizung";
		}
		$pdf->ezTable ( $tab, $cols, "$title", array (
				'showHeadings' => 1,
				'shaded' => 1,
				'titleFontSize' => 10,
				'fontSize' => 9,
				'xPos' => 50,
				'xOrientation' => 'right',
				'width' => 500,
				'cols' => array (
						'DATUM' => array (
								'justification' => 'right',
								'width' => 50 
						),
						'G_BUCHUNGSNUMMER' => array (
								'justification' => 'right',
								'width' => 30 
						),
						'BETRAG' => array (
								'justification' => 'right',
								'width' => 50 
						),
						'KOSTENTRAEGER_BEZ' => array (
								'justification' => 'left',
								'width' => 75 
						),
						'KONTO' => array (
								'justification' => 'right',
								'width' => 30 
						),
						'AUSZUG' => array (
								'justification' => 'right',
								'width' => 35 
						),
						'PLATZ' => array (
								'justification' => 'left',
								'width' => 50 
						) 
				) 
		) );
		
		$pdf->ezSetDy ( - 40 ); // Abstand
		
		if ($einzug == null) {
			$title1 = "Anlage zum Wohnungsabnahmeprotokoll | Ablesung der Wasseruhren";
		} else {
			$title1 = "Anlage zum Wohnungsübergabeprotokoll | Ablesung der Wasseruhren";
		}
		
		$cols = array (
				'RAUM' => "Wasser",
				'GERAET_NR' => "Zähler-Nr.",
				'ALT' => "Stand",
				'NEU' => "Eichdatum !!!" 
		);
		$pdf->ezTable ( $tabw, $cols, "$title1", array (
				'showHeadings' => 1,
				'shaded' => 1,
				'titleFontSize' => 10,
				'fontSize' => 9,
				'xPos' => 50,
				'xOrientation' => 'right',
				'width' => 500,
				'cols' => array (
						'DATUM' => array (
								'justification' => 'right',
								'width' => 50 
						),
						'G_BUCHUNGSNUMMER' => array (
								'justification' => 'right',
								'width' => 30 
						),
						'BETRAG' => array (
								'justification' => 'right',
								'width' => 50 
						),
						'KOSTENTRAEGER_BEZ' => array (
								'justification' => 'left',
								'width' => 75 
						),
						'KONTO' => array (
								'justification' => 'right',
								'width' => 30 
						),
						'AUSZUG' => array (
								'justification' => 'right',
								'width' => 35 
						),
						'PLATZ' => array (
								'justification' => 'left',
								'width' => 50 
						) 
				) 
		) );
		
		/* Footer */
		$pdf->ezSetDy ( - 20 ); // Abstand
		$pdf->ezText ( "$mv->haus_stadt, __________________", 9, array (
				'justification' => 'left' 
		) );
		$pdf->ezSetDy ( - 8 ); // Abstand
		$pdf->addText ( 125, $pdf->y, 6, "Datum" );
		
		$pdf->ezSetDy ( - 20 ); // Abstand
		$pdf->ezText ( "____________________________________________      _____________________________________________", 9, array (
				'justification' => 'left' 
		) );
		$pdf->ezSetDy ( - 8 ); // Abstand
		$pdf->addText ( 150, $pdf->y, 6, "Mieter" );
		$pdf->addText ( 400, $pdf->y, 6, "Vermieter" );
	}
	function pdf_abnahmeprotokoll($pdf, $mv_id, $einzug = null) {
		$mv = new mietvertraege ();
		$mv->get_mietvertrag_infos_aktuell ( $mv_id );
		
		$pdf->ezText ( "<b>Mieter:</b> $mv->personen_name_string", 10 );
		
		$pdf->rectangle ( 530, $pdf->y, 10, 10 );
		/**
		 * Y bei Einzug
		 */
		$pdf->addText ( 441, $pdf->y + 2, 10, 'EINZUG' );
		if ($einzug != null) {
			
			$pdf->addText ( 531, $pdf->y + 2, 10, 'X' );
		}
		$pdf->ezSetDy ( - 13 ); // Abstand
		
		$pdf->rectangle ( 530, $pdf->y + 1, 10, 10 );
		$pdf->addText ( 440, $pdf->y + 2, 10, 'AUSZUG' );
		
		if ($einzug == null) {
			
			$pdf->addText ( 531, $pdf->y + 2, 10, 'X' );
		}
		
		$pdf->ezSetMargins ( 135, 70, 50, 50 );
		$pdf->ezText ( "<b>Wohnung:</b> $mv->haus_strasse $mv->haus_nr, $mv->haus_plz $mv->haus_stadt <b>Wohnlage:</b> $mv->einheit_lage", 10 );
		$pdf->ezSetDy ( 12 ); // Abstand zurück
		$pdf->ezText ( "<b>Wohnungs-Nr:</b> $mv->einheit_kurzname", 10, array (
				'justification' => 'right' 
		) );
		$pdf->ezSetDy ( - 5 ); // Abstand
		$pdf->ezText ( '_____ Zimmer           Küche/Kochnische           Wannenbad/Dusche           extra WC           Abstellraum', 10, array (
				'justification' => 'left' 
		) );
		$pdf->ezSetDy ( - 15 ); // Abstand
		$pdf->ezText ( '_____ Balkon/Loggia    _____ Keller-Nr: ___________________', 10, array (
				'justification' => 'left' 
		) );
		$pdf->ezSetDy ( - 15 ); // Abstand
		$pdf->ezText ( "<b>Beheizung:</b>", 10, array (
				'justification' => 'left' 
		) );
		
		$pdf->rectangle ( 120, $pdf->y - 2, 10, 10 );
		$pdf->addText ( 135, $pdf->y - 1, 10, 'Zentral' );
		
		$pdf->rectangle ( 185, $pdf->y - 2, 10, 10 );
		$pdf->addText ( 200, $pdf->y - 1, 10, 'Elt-Heizung' );
		
		$pdf->rectangle ( 285, $pdf->y - 2, 10, 10 );
		$pdf->addText ( 300, $pdf->y - 1, 10, 'Ofen' );
		
		$pdf->rectangle ( 350, $pdf->y - 2, 10, 10 );
		$pdf->addText ( 365, $pdf->y - 1, 10, 'Gasetagenheizung' );
		
		$pdf->ezSetDy ( - 15 ); // Abstand
		$pdf->ezText ( "<b>Warmwasser:</b>", 10, array (
				'justification' => 'left' 
		) );
		$pdf->rectangle ( 120, $pdf->y - 2, 10, 10 );
		$pdf->addText ( 135, $pdf->y - 1, 10, 'Elt     DE/Boiler' );
		$pdf->rectangle ( 120, $pdf->y - 15, 10, 10 );
		$pdf->addText ( 135, $pdf->y - 15, 10, 'Gas   DE/Boiler' );
		
		$pdf->rectangle ( 350, $pdf->y, 10, 10 );
		$pdf->addText ( 365, $pdf->y, 10, 'Zentral' );
		$pdf->rectangle ( 350, $pdf->y - 15, 10, 10 );
		$pdf->addText ( 365, $pdf->y - 15, 10, 'über Gasetagenheizung' );
		
		$pdf->ezSetDy ( - 15 ); // Abstand
		$y_e = $pdf->y;
		$pdf->ezText ( "<b>Elektrik-Zähler:</b>", 10, array (
				'justification' => 'left' 
		) );
		$pdf->ezSetDy ( - 5 ); // Abstand
		
		$pdf->ezText ( "<b>Zähler-Nr.:</b>", 10, array (
				'justification' => 'left' 
		) );
		$pdf->ezSetDy ( - 5 ); // Abstand
		$this->kasten ( $pdf, 10, 120, 15, 15 );
		
		$pdf->ezText ( "<b>Stand</b>", 10, array (
				'justification' => 'left' 
		) );
		$pdf->ezSetDy ( - 10 ); // Abstand
		$this->kasten ( $pdf, 6, 120, 15, 15 );
		$pdf->addText ( 215, $pdf->y, 15, "<b>,</b>" );
		$this->kasten ( $pdf, 3, 225, 15, 15 );
		
		$abstand = $pdf->y - $y_e;
		$pdf->ezSetDy ( - $abstand ); // Zurückhöhe Elektrozähler
		$pdf->ezSetMargins ( 135, 70, 280, 50 );
		$pdf->ezText ( "<b>Gas-Zähler:</b>", 10, array (
				'justification' => 'left' 
		) );
		$pdf->ezSetDy ( - 5 ); // Abstand
		
		$pdf->ezText ( "<b>Zähler-Nr.:</b>", 10, array (
				'justification' => 'left' 
		) );
		$pdf->ezSetDy ( - 5 ); // Abstand
		$this->kasten ( $pdf, 14, 340, 15, 15 );
		
		$pdf->ezText ( "<b>Stand</b>", 10, array (
				'justification' => 'left' 
		) );
		$pdf->ezSetDy ( - 10 ); // Abstand
		$this->kasten ( $pdf, 6, 340, 15, 15 );
		$pdf->addText ( 435, $pdf->y, 15, "<b>,</b>" );
		$this->kasten ( $pdf, 3, 445, 15, 15 );
		
		$pdf->ezSetMargins ( 135, 70, 50, 50 );
		$pdf->ezSetDy ( - 10 ); // Abstand
		$pdf->ezText ( "Der Mieter stimmt zu, dass der Vermieter die Zählerstände unter Angabe von Vor- und Zuname, sowie der Verzugsanschrift an den regionalen Versorger meldet.", 9, array (
				'justification' => 'left' 
		) );
		$pdf->ezSetDy ( - 10 ); // Abstand
		$pdf->ezText ( "<b>Bei der Wohnungsabnahme wurde festgestellt:</b>", 10, array (
				'justification' => 'left' 
		) );
		$pdf->ezSetMargins ( 135, 70, 330, 50 );
		$pdf->ezSetDy ( 12 ); // Zurück
		$pdf->ezText ( "<b>Beseitigung erfolgt durch:</b>", 10, array (
				'justification' => 'left' 
		) );
		
		$pdf->ezSetMargins ( 135, 70, 50, 50 );
		
		$pdf->ezSetDy ( - 5 ); // Abstand
		$pdf->ezText ( "<b>Wohnungsflur:</b>", 10, array (
				'justification' => 'left' 
		) );
		$pdf->ezSetDy ( - 20 ); // Abstand
		
		$this->kasten ( $pdf, 4, 50, 15, 15, 125 );
		$pdf->addText ( 70, $pdf->y + 3, 10, "keine Mängel" );
		$pdf->addText ( 210, $pdf->y + 3, 10, "folgende Mängel" );
		$pdf->addText ( 380, $pdf->y + 3, 10, "Mieter" );
		$pdf->addText ( 490, $pdf->y + 3, 10, "Vermieter" );
		$pdf->setLineStyle ( 1 );
		$pdf->ezSetDy ( - 20 ); // Abstand
		$pdf->line ( 42, $pdf->y, 550, $pdf->y );
		$pdf->ezSetDy ( - 20 ); // Abstand
		$pdf->line ( 42, $pdf->y, 550, $pdf->y );
		$pdf->ezSetDy ( - 20 ); // Abstand
		$pdf->line ( 42, $pdf->y, 550, $pdf->y );
		
		$pdf->ezText ( "<b>Küche:</b>", 10, array (
				'justification' => 'left' 
		) );
		$pdf->ezSetDy ( - 20 ); // Abstand
		
		$this->kasten ( $pdf, 4, 50, 15, 15, 125 );
		$pdf->addText ( 70, $pdf->y + 3, 10, "keine Mängel" );
		$pdf->addText ( 210, $pdf->y + 3, 10, "folgende Mängel" );
		$pdf->addText ( 350, $pdf->y + 3, 10, "Mieter" );
		$pdf->addText ( 490, $pdf->y + 3, 10, "Vermieter" );
		$pdf->setLineStyle ( 1 );
		$pdf->ezSetDy ( - 20 ); // Abstand
		$pdf->line ( 42, $pdf->y, 550, $pdf->y );
		$pdf->ezSetDy ( - 20 ); // Abstand
		$pdf->line ( 42, $pdf->y, 550, $pdf->y );
		$pdf->ezSetDy ( - 20 ); // Abstand
		$pdf->line ( 42, $pdf->y, 550, $pdf->y );
		
		$pdf->ezText ( "<b>Bad:</b>", 10, array (
				'justification' => 'left' 
		) );
		$pdf->ezSetDy ( - 20 ); // Abstand
		
		$this->kasten ( $pdf, 4, 50, 15, 15, 125 );
		$pdf->addText ( 70, $pdf->y + 3, 10, "keine Mängel" );
		$pdf->addText ( 210, $pdf->y + 3, 10, "folgende Mängel" );
		$pdf->addText ( 350, $pdf->y + 3, 10, "Mieter" );
		$pdf->addText ( 490, $pdf->y + 3, 10, "Vermieter" );
		$pdf->setLineStyle ( 1 );
		$pdf->ezSetDy ( - 20 ); // Abstand
		$pdf->line ( 42, $pdf->y, 550, $pdf->y );
		$pdf->ezSetDy ( - 20 ); // Abstand
		$pdf->line ( 42, $pdf->y, 550, $pdf->y );
		$pdf->ezSetDy ( - 20 ); // Abstand
		$pdf->line ( 42, $pdf->y, 550, $pdf->y );
		
		$pdf->ezText ( "<b>Wohnzimmer:</b>", 10, array (
				'justification' => 'left' 
		) );
		$pdf->ezSetDy ( - 20 ); // Abstand
		
		$this->kasten ( $pdf, 4, 50, 15, 15, 125 );
		$pdf->addText ( 70, $pdf->y + 3, 10, "keine Mängel" );
		$pdf->addText ( 210, $pdf->y + 3, 10, "folgende Mängel" );
		$pdf->addText ( 350, $pdf->y + 3, 10, "Mieter" );
		$pdf->addText ( 490, $pdf->y + 3, 10, "Vermieter" );
		$pdf->setLineStyle ( 1 );
		$pdf->ezSetDy ( - 20 ); // Abstand
		$pdf->line ( 42, $pdf->y, 550, $pdf->y );
		$pdf->ezSetDy ( - 20 ); // Abstand
		$pdf->line ( 42, $pdf->y, 550, $pdf->y );
		$pdf->ezSetDy ( - 20 ); // Abstand
		$pdf->line ( 42, $pdf->y, 550, $pdf->y );
		
		$pdf->ezNewPage ();
		$pdf->ezText ( "<b>Schlafzimmer:</b>", 10, array (
				'justification' => 'left' 
		) );
		$pdf->ezSetDy ( - 20 ); // Abstand
		
		$this->kasten ( $pdf, 4, 50, 15, 15, 125 );
		$pdf->addText ( 70, $pdf->y + 3, 10, "keine Mängel" );
		$pdf->addText ( 210, $pdf->y + 3, 10, "folgende Mängel" );
		$pdf->addText ( 350, $pdf->y + 3, 10, "Mieter" );
		$pdf->addText ( 490, $pdf->y + 3, 10, "Vermieter" );
		$pdf->setLineStyle ( 1 );
		$pdf->ezSetDy ( - 20 ); // Abstand
		$pdf->line ( 42, $pdf->y, 550, $pdf->y );
		$pdf->ezSetDy ( - 20 ); // Abstand
		$pdf->line ( 42, $pdf->y, 550, $pdf->y );
		$pdf->ezSetDy ( - 20 ); // Abstand
		$pdf->line ( 42, $pdf->y, 550, $pdf->y );
		
		$pdf->ezText ( "<b>1. Kinderzimmer:</b>", 10, array (
				'justification' => 'left' 
		) );
		$pdf->ezSetDy ( - 20 ); // Abstand
		
		$this->kasten ( $pdf, 4, 50, 15, 15, 125 );
		$pdf->addText ( 70, $pdf->y + 3, 10, "keine Mängel" );
		$pdf->addText ( 210, $pdf->y + 3, 10, "folgende Mängel" );
		$pdf->addText ( 350, $pdf->y + 3, 10, "Mieter" );
		$pdf->addText ( 490, $pdf->y + 3, 10, "Vermieter" );
		$pdf->setLineStyle ( 1 );
		$pdf->ezSetDy ( - 20 ); // Abstand
		$pdf->line ( 42, $pdf->y, 550, $pdf->y );
		$pdf->ezSetDy ( - 20 ); // Abstand
		$pdf->line ( 42, $pdf->y, 550, $pdf->y );
		$pdf->ezSetDy ( - 20 ); // Abstand
		$pdf->line ( 42, $pdf->y, 550, $pdf->y );
		
		$pdf->ezText ( "<b>2. Kinderzimmer:</b>", 10, array (
				'justification' => 'left' 
		) );
		$pdf->ezSetDy ( - 20 ); // Abstand
		
		$this->kasten ( $pdf, 4, 50, 15, 15, 125 );
		$pdf->addText ( 70, $pdf->y + 3, 10, "keine Mängel" );
		$pdf->addText ( 210, $pdf->y + 3, 10, "folgende Mängel" );
		$pdf->addText ( 350, $pdf->y + 3, 10, "Mieter" );
		$pdf->addText ( 490, $pdf->y + 3, 10, "Vermieter" );
		$pdf->setLineStyle ( 1 );
		$pdf->ezSetDy ( - 20 ); // Abstand
		$pdf->line ( 42, $pdf->y, 550, $pdf->y );
		$pdf->ezSetDy ( - 20 ); // Abstand
		$pdf->line ( 42, $pdf->y, 550, $pdf->y );
		$pdf->ezSetDy ( - 20 ); // Abstand
		$pdf->line ( 42, $pdf->y, 550, $pdf->y );
		
		$pdf->ezText ( "<b>3. Kinderzimmer:</b>", 10, array (
				'justification' => 'left' 
		) );
		$pdf->ezSetDy ( - 20 ); // Abstand
		
		$this->kasten ( $pdf, 4, 50, 15, 15, 125 );
		$pdf->addText ( 70, $pdf->y + 3, 10, "keine Mängel" );
		$pdf->addText ( 210, $pdf->y + 3, 10, "folgende Mängel" );
		$pdf->addText ( 350, $pdf->y + 3, 10, "Mieter" );
		$pdf->addText ( 490, $pdf->y + 3, 10, "Vermieter" );
		$pdf->setLineStyle ( 1 );
		$pdf->ezSetDy ( - 20 ); // Abstand
		$pdf->line ( 42, $pdf->y, 550, $pdf->y );
		$pdf->ezSetDy ( - 20 ); // Abstand
		$pdf->line ( 42, $pdf->y, 550, $pdf->y );
		$pdf->ezSetDy ( - 20 ); // Abstand
		$pdf->line ( 42, $pdf->y, 550, $pdf->y );
		
		$pdf->ezText ( "<b>Balkon/Loggia/Sonstiges:</b>", 10, array (
				'justification' => 'left' 
		) );
		$pdf->ezSetDy ( - 20 ); // Abstand
		
		$this->kasten ( $pdf, 4, 50, 15, 15, 125 );
		$pdf->addText ( 70, $pdf->y + 3, 10, "keine Mängel" );
		$pdf->addText ( 210, $pdf->y + 3, 10, "folgende Mängel" );
		$pdf->addText ( 350, $pdf->y + 3, 10, "Mieter" );
		$pdf->addText ( 490, $pdf->y + 3, 10, "Vermieter" );
		$pdf->setLineStyle ( 1 );
		$pdf->ezSetDy ( - 18 ); // Abstand
		$pdf->line ( 42, $pdf->y, 550, $pdf->y );
		$pdf->ezSetDy ( - 18 ); // Abstand
		$pdf->line ( 42, $pdf->y, 550, $pdf->y );
		$pdf->ezSetDy ( - 18 ); // Abstand
		$pdf->line ( 42, $pdf->y, 550, $pdf->y );
		$pdf->ezSetDy ( - 18 ); // Abstand
		$pdf->line ( 42, $pdf->y, 550, $pdf->y );
		
		$pdf->ezText ( "Folgende Schlüssel wurden übergeben:", 9, array (
				'justification' => 'left' 
		) );
		$pdf->ezSetMargins ( 135, 70, 250, 50 );
		$pdf->ezSetDy ( 10 ); // Zurück
		$pdf->ezText ( "______ Haustür-/ Zentralschlüssel", 9, array (
				'justification' => 'left' 
		) );
		$pdf->ezSetMargins ( 135, 70, 50, 50 );
		$pdf->ezText ( "______ Wohnungstür   ______ Briefkasten   ______ Keller   ______ Sonstige _____________________________________", 9, array (
				'justification' => 'left' 
		) );
		// $pdf->ezText("______ Briefkasten",9, array('justification'=>'left'));
		// $pdf->ezText("______ Keller",9, array('justification'=>'left'));
		// $pdf->ezText("______ Sonstige ________________________________",9, array('justification'=>'left'));
		$pdf->ezSetMargins ( 135, 70, 50, 50 );
		$pdf->ezSetDy ( - 5 ); // Abstand
		$pdf->ezText ( "<b>Die gezahlte Kaution kann ausgezahlt werden</b>", 9, array (
				'justification' => 'left' 
		) );
		$pdf->ezSetDy ( - 3 ); // Abstand
		$this->kasten ( $pdf, 2, 250, 10, 10, 50 );
		$pdf->addText ( 270, $pdf->y + 2, 9, "<b>JA</b>" );
		$pdf->addText ( 335, $pdf->y + 2, 9, "<b>NEIN</b>" );
		$pdf->ezSetDy ( - 5 ); // Abstand
		$pdf->ezText ( "IBAN:__________________________________  BIC:_________________  BANKNAME:___________________________", 9, array (
				'justification' => 'left' 
		) );
		$pdf->ezSetDy ( - 5 ); // Abstand
		$pdf->ezText ( "Verzugsanschrift: ____________________________________________________________________________________", 9, array (
				'justification' => 'left' 
		) );
		$pdf->ezSetDy ( - 5 ); // Abstand
		$pdf->ezText ( "Telefon und E-Mail: __________________________________________________________________________________", 9, array (
				'justification' => 'left' 
		) );
		$pdf->ezSetDy ( - 20 ); // Abstand
		$this->kasten ( $pdf, 1, 50, 10, 10, 50 );
		if ($einzug == 'einzug') {
			$pdf->addText ( 65, $pdf->y + 2, 9, "<b>Der Mieter hat die Einzugsbestätigung erhalten.</b>" );
		} else {
			$pdf->addText ( 65, $pdf->y + 2, 9, "<b>Der Mieter hat die Auszugsbestätigung erhalten.</b>" );
		}
		$pdf->ezSetDy ( - 10 ); // Abstand
		$pdf->ezText ( "$mv->haus_stadt, __________________", 9, array (
				'justification' => 'left' 
		) );
		$pdf->ezSetDy ( - 7 ); // Abstand
		$pdf->addText ( 125, $pdf->y, 6, "Datum" );
		
		$pdf->ezSetDy ( - 14 ); // Abstand
		$pdf->ezText ( "____________________________________________      _____________________________________________", 9, array (
				'justification' => 'left' 
		) );
		$pdf->ezSetDy ( - 7 ); // Abstand
		$pdf->addText ( 150, $pdf->y, 6, "Mieter" );
		$pdf->addText ( 400, $pdf->y, 6, "Vermieter" );
	}
	function pdf_einauszugsbestaetigung($pdf, $mv_id, $einzug = 0) {
		$pdf->ezSetMargins ( 135, 70, 50, 50 );
		$mv = new mietvertraege ();
		$mv->get_mietvertrag_infos_aktuell ( $mv_id );
		$oo = new objekt ();
		$oo->get_objekt_infos ( $mv->objekt_id );
		// echo '<pre>';
		// print_r($oo);
		// print_r($mv);
		// die();
		if ($mv->anzahl_personen > 1) {
			$ist_sind = 'sind';
		} else {
			$ist_sind = 'ist';
		}
		
		if ($einzug == '0') {
			$pdf->ezText ( "<b>Einzugsbestätigung</b>", 18, array (
					'justification' => 'left' 
			) );
			$pdf->ezText ( "$mv->einheit_kurzname", 10, array (
					'justification' => 'right' 
			) );
		} else {
			$pdf->ezText ( "<b>Auszugsbestätigung</b>", 18, array (
					'justification' => 'left' 
			) );
			$pdf->ezText ( "$mv->einheit_kurzname", 10, array (
					'justification' => 'right' 
			) );
		}
		$pdf->ezText ( "<b>Wohnungsgeberbescheinigung gemäß § 19 des Bundesmeldegesetzes (BMG)</b>", 11, array (
				'justification' => 'left' 
		) );
		$pdf->ezSetDy ( - 35 ); // Abstand
		$pdf->ezText ( "Hiermit bestätige(n) ich/wir als Wohnungsgeber/Vermieter, dass", 10 );
		$pdf->ezSetDy ( - 15 ); // Abstand
		$pdf->ezText ( "$mv->personen_name_string_u", 10 );
		
		$pdf->ezSetDy ( - 15 ); // Abstand
		if ($einzug == '0') {
			$pdf->ezText ( "in die von mir/uns vermietete Wohnung", 10 );
		} else {
			$pdf->ezText ( "aus der von mir/uns vermieteten Wohnung", 10 );
		}
		$pdf->ezSetDy ( - 15 ); // Abstand
		$pdf->ezText ( "unter der Anschrift: $mv->haus_strasse $mv->haus_nr, $mv->haus_plz $mv->haus_stadt  (Wohnlage:</b> $mv->einheit_lage)", 10 );
		$pdf->ezSetDy ( - 15 ); // Abstand
		if ($einzug == '0') {
			$pdf->ezText ( "am _______________________  eingezogen $ist_sind.", 10 );
		} else {
			$pdf->ezText ( "am _______________________  ausgezogen $ist_sind.", 10 );
		}
		
		$pdf->ezSetDy ( - 20 ); // Abstand
		                    
		// unset($oo->objekt_eigentuemer);
		
		if (empty ( $oo->objekt_eigentuemer )) {
			$pdf->ezSetDy ( - 30 ); // Abstand
			$this->kasten ( $pdf, 1, 50, 10, 10 );
			$pdf->addText ( 70, $pdf->y + 1, 10, 'Der Wohnungsgeber/Vermieter ist gleichzeitig <b>Eigentümer</b> der Wohnung oder' );
			
			$pdf->ezSetDy ( - 20 ); // Abstand
			
			$this->kasten ( $pdf, 1, 50, 10, 10 );
			
			$pdf->addText ( 70, $pdf->y + 1, 10, "Der Wohnungsgeber/Vermieter ist <b>nicht</b> Eigentümer der Wohnung" );
			$pdf->ezSetDy ( - 15 ); // Abstand
			
			$pdf->ezSetDy ( - 25 ); // Abstand
			$pdf->line ( 50, $pdf->y, 550, $pdf->y );
			$pdf->ezSetDy ( - 25 ); // Abstand
			$pdf->line ( 50, $pdf->y, 550, $pdf->y );
		} else {
			$this->kasten ( $pdf, 1, 50, 10, 10 );
			$pdf->addText ( 50, $pdf->y + 2, 10, 'X' );
			
			$pdf->addText ( 70, $pdf->y + 1, 10, "Der Wohnungsgeber ist <b>nicht</b> Eigentümer der Wohnung" );
			$pdf->ezSetDy ( - 15 ); // Abstand
			
			$pdf->eztext ( "Name und Anschrift des <b>Eigentümers</b> lauten:", 10 );
			
			$pdf->eztext ( "$oo->objekt_eigentuemer", 10 );
			$pp = new partners ();
			$pp->get_partner_info ( $oo->objekt_eigentuemer_id );
			$pdf->eztext ( "$pp->partner_strasse $pp->partner_hausnr, $pp->partner_plz $pp->partner_ort", 10 );
		}
		
		$pdf->ezSetDy ( - 25 ); // Abstand
		
		$pdf->ezText ( "Ich bestätige mit meiner Unterschrift den Ein- bzw. Auszug der oben genannten Person(en) in die näher bezeichnete Wohnung und dass ich als Wohnungsgeber oder als beauftragte Person diese Bescheinigung ausstellen darf. Ich habe davon Kenntnis genommen, da ich ordnungswidrig handele, wenn ich hierzu nicht berechtigt bin und dass es verboten ist, eine Wohnanschrift für eine Anmeldung eines Wohnsitzes einem Dritten anzubieten oder zur Verfügung zu stellen, obwohl ein tatsächlicher Bezug der Wohnung durch einen Dritten weder stattfindet noch beabsichtigt ist. Ein Verstoß gegen das Verbot stellt auch einen Ordnungswidrigkeit dar.", 8 );
		
		/* Footer */
		$pdf->ezSetDy ( - 25 ); // Abstand
		$pdf->ezText ( "$mv->haus_stadt, __________________", 9, array (
				'justification' => 'left' 
		) );
		$pdf->ezSetDy ( - 7 ); // Abstand
		$pdf->addText ( 125, $pdf->y, 6, "Datum" );
		
		$pdf->ezSetDy ( - 30 ); // Abstand
		$pdf->ezText ( "____________________________________________", 9, array (
				'justification' => 'left' 
		) );
		$pdf->ezSetDy ( - 8 ); // Abstand
		$pdf->addText ( 57, $pdf->y, 6, "Unterschrift des Wohnungsgebers/Vermieters oder der beauftragten Person" );
		
		$pdf->ezSetDy ( - 15 ); // Abstand
	}
	function kasten($pdf, $anz_felder, $startx, $h, $b, $abstand_zw = null) {
		for($a = 1; $a <= $anz_felder; $a ++) {
			
			if ($a == 1) {
				$pdf->rectangle ( $startx, $pdf->y, $b, $h );
			} else {
				if ($abstand_zw != null) {
					$startx += $abstand_zw;
				}
				
				$startx += $b;
				$pdf->rectangle ( $startx, $pdf->y, $b, $h );
			}
		}
	}
} // end class b_pdf

?>
