<?php

/* Modulabhängige Dateien d.h. Links und eigene Klasse */
class dtaus_berlus {
	function dtaus_datei_speichern($folder, $filename, $string) {
		$doc_root = $_SERVER ['DOCUMENT_ROOT'];
		$dtaus_ordner = $folder;
		
		$f_arr = explode ( "/", $folder );
		// echo "<pre>";
		// print_r($f_arr);
		$anzahl_ordner = count ( $f_arr );
		
		$ordner_temp = '';
		for($a = 0; $a < $anzahl_ordner; $a ++) {
			$o = $f_arr [$a]; // ordnername
			if ($a == 0) {
				if (! file_exists ( $o )) {
					echo " $o angelegt <br>";
					mkdir ( "$o", 0777 );
				}
				// rmdir($o);
				
				$ordner_temp .= $o;
				$ordner_temp .= '/';
			} else {
				
				$aktueller_ordner = $ordner_temp . $o;
				if (! file_exists ( $aktueller_ordner )) {
					echo "$a.  $aktueller_ordner angelegt <br>";
					mkdir ( "$aktueller_ordner", 0777 );
				}
				// rmdir($aktueller_ordner);
				$ordner_temp .= $o;
				$ordner_temp .= '/';
			}
		}
		
		$filename_neu = "$ordner_temp$filename";
		/* wenn datei existiert löschen */
		if (file_exists ( $filename_neu )) {
			unlink ( $filename_neu ); // Datei löschen
		}
		if (! file_exists ( $filename_neu )) {
			$fhandle = fopen ( $filename_neu, "w" );
			fwrite ( $fhandle, $string );
			fclose ( $fhandle );
			echo "<br>$filename_neu erstellt";
			chmod ( $filename_neu, 0644 );
			// system("rm $dir -R"); //ordner abrechnungsjahr löschen
			echo "<table  border=3>";
			echo "<tr class=\"feldernamen\"><td><a href=\"$_SERVER[SCRIPT_ROOT]$filename_neu\"><b>DOWNLOAD ALS DTA-DATEI</a></b></td></tr>";
			echo "<tr><td>$string</td></tr>";
			echo "</table>";
		}
		
		if (file_exists ( $filename_neu )) {
			return true;
		} else {
			return false;
		}
	} // end function
	function umbrueche_entfernen($text) {
		$text = preg_replace ( "/\r|\n/s", "", $text );
		return $text;
	}
	function pdf_dtaus_inhalt_obj($tab_arr) {
		$objekt_id = $tab_arr ['objekt_id'];
		$objekt_name = $tab_arr ['OBJEKT_KURZNAME'];
		unset ( $tab_arr ['objekt_id'] );
		unset ( $tab_arr ['OBJEKT_KURZNAME'] );
		
		$summe = 0;
		$anz = count ( $tab_arr );
		for($a = 0; $a < $anz; $a ++) {
			$summe += $tab_arr [$a] ['ZIEHEN'];
		}
		$tab_arr [$anz] ['BLZ'] = 'Summe';
		$tab_arr [$anz] ['BETRAG'] = nummer_punkt2komma_t ( $summe );
		
		$pdf = new Cezpdf ( 'a4', 'portrait' );
		$bpdf = new b_pdf ();
		$bpdf->b_header ( $pdf, 'Partner', session()->get('partner_id'), 'portrait', 'Helvetica.afm', 6 );
		$this->footer_zahlungshinweis = $bpdf->zahlungshinweis;
		
		$pdf->addInfo ( 'Author', Auth::user()->email);
		
		$cols = array (
				'EINHEIT_NAME' => "<b>EINHEIT</b>",
				'KONTOINHABER' => "<b>KONTOINHABER</b>",
				'KONTONUMMER' => "<b>KONTONR</b>",
				'BLZ' => "<b>BLZ</b>",
				'BETRAG' => "<b>BETRAG</b>",
				'vztext' => "<b>VZWECK\nBUCHUNGSTEXT</b>",
				'Autoeinzugsart' => "<b>EINZUGSART</b>" 
		);
		$pdf->ezTable ( $tab_arr, $cols, "DTAUS Protokoll $objekt_name", array (
				'showHeadings' => 1,
				'shaded' => 1,
				'titleFontSize' => 10,
				'fontSize' => 8,
				'xPos' => 50,
				'xOrientation' => 'right',
				'width' => 500,
				'rowGap' => 1,
				'cols' => array (
						'EINHEIT' => array (
								'justification' => 'left',
								'width' => 50 
						),
						'SALDO_VM' => array (
								'justification' => 'right',
								'width' => 60 
						),
						'UMLAGEN' => array (
								'justification' => 'right',
								'width' => 55 
						),
						'G_SOLL_AKT' => array (
								'justification' => 'right',
								'width' => 50 
						),
						'ZAHLUNGEN' => array (
								'justification' => 'right',
								'width' => 65 
						),
						'MWST' => array (
								'justification' => 'right' 
						),
						'ERG' => array (
								'justification' => 'right',
								'width' => 50 
						) 
				) 
		) );
		
		ob_end_clean (); // ausgabepuffer leeren
		$pdf->ezStream ();
	}
} // end class
