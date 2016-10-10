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
} // end class
