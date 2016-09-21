<?php

class mietspiegel {
	function get_ms_arr() {
		$result = mysql_query ( "SELECT JAHR, ORT FROM MIETSPIEGEL GROUP BY JAHR, ORT ORDER BY JAHR DESC, ORT ASC" );
		
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			while ( $row = mysql_fetch_assoc ( $result ) )
				$my_array [] = $row;
			return $my_array;
		} else {
			return FALSE;
		}
	}
	function liste_mietspiegel() {
		$arr = $this->get_ms_arr ();
		// echo '<pre>';
		// print_r($arr);
		if (! is_array ( $arr )) {
			echo "Keine Mietspiegeldaten in der Datenbank";
		} else {
			
			$anz = count ( $arr );
			for($a = 0; $a < $anz; $a ++) {
				$jahr = $arr [$a] ['JAHR'];
				$ort = $arr [$a] ['ORT'];
				if (empty ( $ort )) {
					$link_anzeigen = "<a href='" . route('legacy::mietspiegel::index', ['option' => 'mietspiegel_anzeigen', 'jahr' => $jahr]) . "'>$jahr - Ohne Ortsangabe</a><br>";
				} else {
					$link_anzeigen = "<a href='" . route('legacy::mietspiegel::index', ['option' => 'mietspiegel_anzeigen', 'jahr' => $jahr, 'ort' => $ort]) . "'>$jahr - $ort</a><br>";
				}
				echo $link_anzeigen;
			}
		}
	}
	function mietspiegel_werte_arr($jahr, $ort = null) {
		if ($ort == null) {
			$result = mysql_query ( "SELECT * FROM MIETSPIEGEL WHERE JAHR='$jahr' && ORT='' ORDER BY FELD ASC" );
		} else {
			$result = mysql_query ( "SELECT * FROM MIETSPIEGEL WHERE JAHR='$jahr' && ORT='$ort' ORDER BY FELD ASC" );
		}
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			while ( $row = mysql_fetch_assoc ( $result ) )
				$my_array [] = $row;
			return $my_array;
		} else {
			return FALSE;
		}
	}
	function mietspiegel_anzeigen($jahr, $ort = null) {
		$arr = $this->mietspiegel_werte_arr ( $jahr, $ort );
		if (! is_array ( $arr )) {
			fehlermeldung_ausgeben ( "Keine Daten im Mietspiegel $jahr" );
		} else {
			$this->form_neue_ms_werte ( $jahr, $ort );
			$this->form_neue_sonderabzuege ( $jahr, $ort );
			
			$anz = count ( $arr );
			echo "<table class='striped'>";
			echo "<thead><tr><th  colspan=\"5\">Mietspiegel $jahr</th></tr>";
			echo "<tr><th>Feld</th><th>Unterer Wert</th><th>Mittelwert</th><th>Oberwert</th><th>Option</th></tr></thead>";
			for($a = 0; $a < $anz; $a ++) {
				$feld = $arr [$a] ['FELD'];
				$dat = $arr [$a] ['DAT'];
				$u_wert = nummer_punkt2komma ( $arr [$a] ['U_WERT'] );
				$m_wert = nummer_punkt2komma ( $arr [$a] ['M_WERT'] );
				$o_wert = nummer_punkt2komma ( $arr [$a] ['O_WERT'] );
				$link_loeschen = "<a href='" . route('legacy::mietspiegel::index', ['option' => 'ms_wert_del', 'dat' => $dat]) . "'>Löschen</a>";
				echo "<tr><td><b>$feld</b></td><td>$u_wert</td><td><b>$m_wert</b></td><td>$o_wert</td><td>$link_loeschen</td></tr>";
			}
			echo "</table>";
		}
	}
	function mietspiegel_abzuege_arr($jahr, $ort = null) {
		if ($ort == null) {
			$result = mysql_query ( "SELECT * FROM `MS_SONDERMERKMALE` WHERE JAHR='$jahr' && ORT='' ORDER BY A_KLASSE ASC" );
		} else {
			$result = mysql_query ( "SELECT * FROM `MS_SONDERMERKMALE` WHERE JAHR='$jahr' && ORT='$ort' ORDER BY A_KLASSE ASC" );
		}
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			while ( $row = mysql_fetch_assoc ( $result ) )
				$my_array [] = $row;
			return $my_array;
		} else {
			return FALSE;
		}
	}
	function abzuege_anzeigen($jahr, $ort = null) {
		$arr = $this->mietspiegel_abzuege_arr ( $jahr, $ort );
		if (! is_array ( $arr )) {
			fehlermeldung_ausgeben ( "ABZÜGE NICHT EINGEPFLEGT" );
		} else {
			$anz = count ( $arr );
			echo "<table class='striped'>";
			echo "<thead><tr><th colspan=\"4\">Besondere Abzüge</th></tr>";
			echo "<tr><th>Ausstattungsklasse</th><th>Merkmal</th><th>Wert</th><th>Option</th></tr></thead>";
			for($a = 0; $a < $anz; $a ++) {
				$dat = $arr [$a] ['DAT'];
				$merkmal = $arr [$a] ['MERKMAL'];
				$wert = $arr [$a] ['WERT'];
				$ausstattungsklasse = $arr [$a] ['A_KLASSE'];
				$link_del = "<a href='" . route('legacy::mietspiegel::index', ['option' => 'del_sonderabzug', 'dat' => $dat]) . "'>Löschen</a>";
				echo "<tr><td><b>$ausstattungsklasse</b></td><td>$merkmal</td><td><b>$wert</b></td><td>$link_del</td></tr>";
			}
			echo "</table>";
		}
	}
	function form_neuer_mietspiegel() {
		$f = new formular ();
		$f->erstelle_formular ( 'Neuen Mietspiegel erfassen', null );
		$f->text_feld ( 'Jahr', 'jahr', '', 5, 'jahr', '' );
		$f->text_feld ( 'Ort', 'ort', '', 50, 'ort', '' );
		$f->hidden_feld ( 'option', 'ms_speichern' );
		$f->send_button ( 'BTN_MSS', 'Speichern' );
		$f->ende_formular ();
	}
	function form_neue_ms_werte($jahr, $ort = null) {
		$f = new formular ();
		$f->erstelle_formular ( 'Neue MS-Werte eingeben', null );
		$f->hidden_feld ( 'jahr', $jahr );
		
		if ($ort != null) {
			$f->hidden_feld ( 'ort', $ort );
		}
		
		$f->text_feld ( 'Feld (z.B. A1)', 'feld', '', 5, 'feld', '' );
		$f->text_feld ( 'Unterer Wert', 'u_wert', '', 10, 'u_wert', '' );
		$f->text_feld ( 'Mittlerer Wert', 'm_wert', '', 10, 'm_wert', '' );
		$f->text_feld ( 'Oberer Wert', 'o_wert', '', 10, 'o_wert', '' );
		$f->hidden_feld ( 'option', 'ms_wert_speichern' );
		$f->send_button ( 'BTN_MWS', 'Wert speichern' );
		$f->ende_formular ();
	}
	function form_neue_sonderabzuege($jahr, $ort = null) {
		$f = new formular ();
		$f->erstelle_formular ( 'Sonderabzüge eintragen / Ausstattugsklasse = Spaltennr in MS', null );
		$f->hidden_feld ( 'jahr', $jahr );
		
		if ($ort != null) {
			$f->hidden_feld ( 'ort', $ort );
		}
		
		$this->dropdown_merkmale_ms ( 'Merkmal wählen', 'merkmal', 'merkmal', '', '' );
		$f->text_feld ( 'Wertabzug (z.B. -1,86 MINUSBETRAG!!!', 'wert', '', 10, 'wert', '' );
		$this->dropdown_klassen ( 10, 'Ausstattungsklasse / Spaltennr aus MS', 'a_klasse', 'a_klasse' );
		$f->hidden_feld ( 'option', 'abzug_speichern' );
		$f->send_button ( 'BTN_MAS', 'Abzug speichern' );
		$f->ende_formular ();
	}
	function ms_speichern($jahr, $ort = null, $feld = 0, $u_wert = 0, $m_wert = 0, $o_wert = 0) {
		if ($feld != '0') {
			$db_abfrage = "DELETE FROM MIETSPIEGEL WHERE FELD='0' ";
			$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		}
		
		$u_wert = nummer_komma2punkt ( $u_wert );
		$m_wert = nummer_komma2punkt ( $m_wert );
		$o_wert = nummer_komma2punkt ( $o_wert );
		
		if ($ort == null) {
			$db_abfrage = "INSERT INTO MIETSPIEGEL VALUES (NULL, '$jahr', '', '$feld', '$u_wert', '$m_wert', '$o_wert')";
			$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		} else {
			$db_abfrage = "INSERT INTO MIETSPIEGEL VALUES (NULL, '$jahr', '$ort', '$feld', '$u_wert', '$m_wert', '$o_wert')";
			$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		}
	}
	function get_merkmale_ms_arr() {
		$result = mysql_query ( "SELECT * FROM  `MS_SONDERMERKMALE` GROUP BY MERKMAL ORDER BY MERKMAL ASC" );
		
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			while ( $row = mysql_fetch_assoc ( $result ) )
				$my_array [] = $row;
			return $my_array;
		} else {
			return FALSE;
		}
	}
	function sonderabzug_speichern($jahr, $merkmal, $betrag, $klasse, $ort) {
		if ($ort == null) {
			$db_abfrage = "INSERT INTO MS_SONDERMERKMALE VALUES (NULL, '$jahr', '', '$merkmal', '$betrag', '$klasse')";
			$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		} else {
			$db_abfrage = "INSERT INTO MS_SONDERMERKMALE VALUES (NULL, '$jahr', '$ort', '$merkmal', '$betrag', '$klasse')";
			$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		}
	}
	function dropdown_merkmale_ms($label, $name, $id, $vorwahl = '', $js = '') {
		$arr = $this->get_merkmale_ms_arr ();
		
		echo "<label for=\"$id\">$label</label><select name=\"$name\" id=\"$id\" size=1 $js>\n";
		if (is_array ( $arr )) {
			$anz = count ( $arr );
			for($a = 0; $a < $anz; $a ++) {
				$merkmal = $arr [$a] ['MERKMAL'];
				if ($merkmal == $vorwahl) {
					echo "<option value=\"$merkmal\" selected>$merkmal</option>";
				} else {
					echo "<option value=\"$merkmal\">$merkmal</option>";
				}
			}
		}
		echo "</select>";
	}
	function dropdown_klassen($anz, $label, $name, $id) {
		echo "<label for=\"$id\">$label</label><select name=\"$name\" id=\"$id\" size=1 >\n";
		for($a = 1; $a <= $anz; $a ++) {
			echo "<option value=\"$a\">$a</option>";
		}
		echo "</select>";
	}
	function ms_wert_loeschen($dat) {
		$db_abfrage = "DELETE FROM MIETSPIEGEL WHERE DAT='$dat'";
		$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
	}
	function ms_sonderabzug_loeschen($dat) {
		$db_abfrage = "DELETE FROM MS_SONDERMERKMALE WHERE DAT='$dat'";
		$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
	}
}//end class	