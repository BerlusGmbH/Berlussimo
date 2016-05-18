<?php

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

			$konten_arr [$a] ['BEZEICHNUNG'] = $this->konto_bezeichnung;
			$konten_arr [$a] ['GRUPPE'] = $this->konto_gruppen_bezeichnung;
			$konten_arr [$a] ['KONTOART'] = $this->konto_art_bezeichnung;
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
			$kontenrahmen_id = $kontenrahmen_arr [$a] ['KONTENRAHMEN_ID'];
			$kontenrahmen_name = $kontenrahmen_arr [$a] ['NAME'];
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
		echo "<div class='input-field'>";
		echo "<select name=\"$name\" size=\"1\" id=\"$id\"  $js>\n";
		echo "<option value=\"\">Bitte wählen</option>\n";
		for($a = 0; $a < count ( $konten_arr ); $a ++) {
			$konto = $konten_arr [$a] ['KONTO'];
			$bez = $konten_arr [$a] ['BEZEICHNUNG'];
			$this->konto_informationen ( $konten_arr [$a] ['KONTO'] );

			// echo "<option value=\"$konto\">".$konto." - ".$this->konto_bezeichnung."</option>\n";
			echo "<option value=\"$konto\">$konto $bez</option>\n";
		}
		echo "</select><label for=\"$id\" id=\"label_$name\">$label</label>\n";
        echo "</div>";
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
