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
    public $gruppe_id;
    public $konto_sonderumlage;

    /* Holt Infos über ein Konto z.B. 5200 */

	function get_konten_nach_art_gruppe($kontoartbez, $gruppenbez, $k_id) {
		$kontoart_id = $this->get_kontoart_id ( $kontoartbez );
		$gruppen_id = $this->get_gruppen_id ( $gruppenbez );
		if ($kontoart_id && $gruppen_id) {
			$result = DB::select( "SELECT * FROM `KONTENRAHMEN_KONTEN` WHERE `KONTO_ART` ='$kontoart_id' && GRUPPE='$gruppen_id' AND `KONTENRAHMEN_ID` ='$k_id' AND `AKTUELL` = '1' ORDER BY KONTO ASC" );
			if (!empty($result)) {
				return $result;
			}
		}
	}

    /* Holt Infos über ein Konto z.B. 5200 */

    function get_kontoart_id($kontoartbez)
    {
        $result = DB::select("SELECT KONTENRAHMEN_KONTOART_ID FROM KONTENRAHMEN_KONTOARTEN WHERE KONTOART='$kontoartbez' && AKTUELL='1' ORDER BY KONTENRAHMEN_KONTOART_ID DESC LIMIT 0,1");
        $row = $result[0];
        return $row ['KONTENRAHMEN_KONTOART_ID'];
    }

    /* Holt Infos über eine Kontogruppe z.B. 1 - Reparaturen */

    function get_gruppen_id($gruppenbez)
    {
        $result = DB::select("SELECT KONTENRAHMEN_GRUPPEN_ID FROM KONTENRAHMEN_GRUPPEN WHERE BEZEICHNUNG='$gruppenbez' && AKTUELL='1' ORDER BY KONTENRAHMEN_GRUPPEN_DAT DESC LIMIT 0,1");
        $row = $result[0];
        return $row ['KONTENRAHMEN_GRUPPEN_ID'];
	}

    /* Holt Infos über eine Kontoart z.B. 1 - Kosten , 4 Einnahmen usw. */

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

    function kontenrahmen_in_arr()
    {
        $result = DB::select("SELECT KONTENRAHMEN_ID, NAME FROM KONTENRAHMEN WHERE  AKTUELL='1' ORDER BY NAME ASC");
        return $result;
    }

	function dropdown_konten_vom_rahmen($label, $name, $id, $js, $kontenrahmen_id) {

		// $kt->dropdown_konten_vom_rahmen('Kostenkonto', "kontenrahmen_konto", "kontenrahmen_konto", '', $kontenrahmen_id );
		$konten_arr = $this->konten_in_arr_rahmen ( $kontenrahmen_id );
		echo "<div class='input-field'><select name=\"$name\" size=\"1\" id=\"$id\"  $js>\n";

		for($a = 0; $a < count ( $konten_arr ); $a ++) {
			$konto = $konten_arr [$a] ['KONTO'];
			$this->konto_informationen2 ( $konten_arr [$a] ['KONTO'], $kontenrahmen_id );

			echo "<option value=\"$konto\">$konto $this->konto_bezeichnung</option>\n";
		}
		echo "</select><label for=\"$id\">$label</label></div>\n";
	}

	function konten_in_arr_rahmen($kontenrahmen_id) {
		$result = DB::select( "SELECT KONTO, BEZEICHNUNG FROM KONTENRAHMEN_KONTEN WHERE KONTENRAHMEN_ID='$kontenrahmen_id' && AKTUELL='1' ORDER BY KONTO ASC" );
		return $result;
	}

    /* Liste aller Kontorahmenkonten als array */

    function konto_informationen2($konto, $kontenrahmen_id)
    {
        $result = DB::select("SELECT * FROM KONTENRAHMEN_KONTEN WHERE KONTO='$konto' && KONTENRAHMEN_ID='$kontenrahmen_id' && AKTUELL='1' ORDER BY KONTENRAHMEN_KONTEN_ID DESC LIMIT 0,1");
        $row = $result[0];
        $this->konten_dat = $row ['KONTENRAHMEN_KONTEN_DAT'];
        $this->konten_id = $row ['KONTENRAHMEN_KONTEN_ID'];
        $this->konto = $konto;
        $this->konto_bezeichnung = $row ['BEZEICHNUNG'];
        $this->gruppe_id = $row ['GRUPPE'];
        $this->konto_gruppen_bezeichnung = $this->gruppen_bezeichnung($this->gruppe_id);
        $this->konto_art_id = $row ['KONTO_ART'];
        $this->konto_art_bezeichnung = $this->kontoart($this->konto_art_id);
        $this->konto_sonderumlage = $row['SONDERUMLAGE'] == '1';
    }

    /* Den dazugehörigen Kontenrahmen finden, egal ob Geldkonto, Partner usw. */

    function gruppen_bezeichnung($gruppen_id)
    {
        $result = DB::select("SELECT BEZEICHNUNG FROM KONTENRAHMEN_GRUPPEN WHERE KONTENRAHMEN_GRUPPEN_ID='$gruppen_id' && AKTUELL='1' ORDER BY KONTENRAHMEN_GRUPPEN_ID DESC LIMIT 0,1");
        $row = $result[0];
        return $row ['BEZEICHNUNG'];
    }

    /* Kontenrahmenliste als dropdown /kontierung */

    function kontoart($kontoart_id)
    {
        $result = DB::select("SELECT KONTOART FROM KONTENRAHMEN_KONTOARTEN WHERE KONTENRAHMEN_KONTOART_ID='$kontoart_id' && AKTUELL='1' ORDER BY KONTENRAHMEN_KONTOART_ID DESC LIMIT 0,1");
        $row = $result[0];
        return $row ['KONTOART'];
    }

    /* Kontenliste als dropdown /kontierung */

	function dropdown_kontorahmenkonten($label, $id, $name, $typ, $typ_id, $js) {
		$konten_arr = $this->kontorahmen_konten_in_array ( $typ, $typ_id );
		echo "<div class='input-field'>";
		echo "<select name=\"$name\" size=\"1\" id=\"$id\"  $js>\n";
		echo "<option value=\"\">Bitte wählen</option>\n";
		for($a = 0; $a < count ( $konten_arr ); $a ++) {
			$konto = $konten_arr [$a] ['KONTO'];
			$bez = $konten_arr [$a] ['BEZEICHNUNG'];
			$this->konto_informationen ( $konten_arr [$a] ['KONTO'] );
			echo "<option value=\"$konto\">$konto $bez</option>\n";
		}
		echo "</select><label for=\"$id\" id=\"label_$name\">$label</label>\n";
        echo "</div>";
	}

    function kontorahmen_konten_in_array($typ, $typ_id)
    {
        $kontenrahmen_id = $this->get_kontenrahmen($typ, $typ_id);
        $result = DB::select("SELECT KONTO, BEZEICHNUNG FROM KONTENRAHMEN_KONTEN WHERE KONTENRAHMEN_ID='$kontenrahmen_id' && AKTUELL='1' ORDER BY KONTO ASC");
        return $result;
    }

    function get_kontenrahmen($typ, $typ_id)
    {
        $result = DB::select("SELECT KONTENRAHMEN_ID FROM `KONTENRAHMEN_ZUWEISUNG` WHERE TYP='$typ' && TYP_ID='$typ_id' && AKTUELL='1' ORDER BY DAT DESC LIMIT 0,1");
        if (!empty($result)) {
            $row = $result[0];
            return $row ['KONTENRAHMEN_ID'];
        } else {
            /* Sonst den Kontenrahmen verwenden die keinen Kontenrahmen haben TYP='ALLE' */
            $result = DB::select("SELECT KONTENRAHMEN_ID FROM `KONTENRAHMEN_ZUWEISUNG` WHERE TYP='ALLE' && AKTUELL='1' ORDER BY DAT DESC LIMIT 0,1");
            if (!empty($result)) {
                $row = $result[0];
                return $row ['KONTENRAHMEN_ID'];
            }
        }
    }

    /* Kontenliste als dropdown mit Label, Id und Name */

    function konto_informationen($konto)
    {
        $result = DB::select("SELECT * FROM KONTENRAHMEN_KONTEN WHERE KONTO='$konto' && AKTUELL='1' ORDER BY KONTENRAHMEN_KONTEN_ID DESC LIMIT 0,1");
        $row = $result[0];
        $this->konten_dat = $row ['KONTENRAHMEN_KONTEN_DAT'];
        $this->konten_id = $row ['KONTENRAHMEN_KONTEN_ID'];
        $this->konto = $konto;
        $this->konto_bezeichnung = $row ['BEZEICHNUNG'];
        $this->gruppe_id = $row ['GRUPPE'];
        $this->konto_gruppen_bezeichnung = $this->gruppen_bezeichnung($this->gruppe_id);
        $this->konto_art_id = $row ['KONTO_ART'];
        $this->konto_art_bezeichnung = $this->kontoart($this->konto_art_id);
    }

	function dropdown_kontorahmenkonten_vorwahl($label, $id, $name, $typ, $typ_id, $js, $vorwahl_konto, $form = null) {
		$konten_arr = $this->kontorahmen_konten_in_array ( $typ, $typ_id );
        echo "<div class='input-field'>";
		if(!is_null($form)) {
            echo "<select name=\"$name\" size=\"1\" id=\"$id\" $js form=\"$form\">\n";
        } else {
            echo "<select name=\"$name\" size=\"1\" id=\"$id\" $js>\n";
        }
		echo "<option value=\"\">Bitte wählen</option>\n";
		for($a = 0; $a < count ( $konten_arr ); $a ++) {
			$konto = $konten_arr [$a] ['KONTO'];
			$bez = $konten_arr [$a] ['BEZEICHNUNG'];
			$this->konto_informationen ( $konten_arr [$a] ['KONTO'] );

			if ($vorwahl_konto == $konto) {
				echo "<option value=\"$konto\" selected>$konto $bez</option>\n";
			} else {
				echo "<option value=\"$konto\">$konto $bez</option>\n";
			}
		}
		echo "</select><label for=\"$id\" id=\"label_$name\">$label</label>\n";
		echo "</div>";
	}
} // ende class kontenrahmen
