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
 * @filesource   $HeadURL: http://192.168.2.52/svn/berlussimo_1/tags/02.11.2010 - Downloadversion 0.27/classes/class_formular.php $
 * @version      $Revision: 15 $
 * @modifiedby   $LastChangedBy: sivac $
 * @lastmodified $Date: 2011-07-07 10:41:33 +0200 (Do, 07 Jul 2011) $
 * 
 */
class formular {
	function fieldset($legend_name, $id) {
		echo "<fieldset class=\"$id\" id=\"$id\" style=\"margin:0px;padding:0px;\">\n";
		echo "<legend>$legend_name</legend>\n";
	}
	function fieldset_ende() {
		echo "</fieldset>\n";
	}
	
	/* Formular erstellen bzw. anfangen, inkl legend, action */
	function erstelle_formular($name, $action, $legend = null) {

		if (! isset ( $action )) {
			echo "<form class='$name' name='$name' action='$self'  method='post' >\n";
		} else {
			echo "<form class='$name' name=\"$name\" action=\"$action\" method=\"post\">\n";
		}
		echo "\n";

		echo "<fieldset class=\"$name\" id=\"$name\">\n";
		echo "<legend>" . ((!is_null($legend)) ? $legend : $name) . "</legend>\n";
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
	}
	
	/* Formular abschliessen */
	function ende_formular() {
		echo "</fieldset></form>\n";
	}
	
	/* Button mit JS-Action */
	function button_js($name, $wert, $js) {
		echo "<input type=button name=\"$name\" value=\"$wert\" class=\"submit\" id=\"$name\" $js>";
	}
	
	/* Sendenbutton mit JS-Action */
	function send_button_js($name, $wert, $js) {
		echo "<input type=submit name=\"$name\" value=\"$wert\" class=\"submit\" id=\"$name\" $js>";
	}
	function send_button_disabled($name, $wert, $id) {
		echo "<input type=submit name=\"$name\" id=\"$id\" value=\"$wert\" class=\"submit\" id=\"$name\"  disabled>";
	}
	
	/* Sendenbutton normal */
	function send_button($name, $wert) {
		echo "<input type=submit name=\"$name\" value=\"$wert\" class=\"submit\" id=\"$name\">";
	}
	
	/* Radioauswahl normal */
	function radio_button($name, $wert, $label) {
		echo "<label for=\"$name\">$label</label>\n";
		echo "<input type=\"radio\" id=\"$name\" name=\"$name\" value=\"$wert\">\n";
	}
	
	/* Radioauswahl mit JS-Action */
	function radio_button_js($name, $wert, $label, $js, $checked) {
		echo "<label for=\"$name\">$label</label>\n";
		echo "<input type=\"radio\" id=\"$name\" name=\"$name\" value=\"$wert\" $js $checked>\n";
	}
	
	/* Checkboxauswahl mit JS-Action */
	function check_box_js($name, $wert, $label, $js, $checked) {
		echo "<label for=\"$name\">$label</label>\n";
		echo "<input type=\"checkbox\" id=\"$name\" name=\"$name\" value=\"$wert\" $js $checked>\n";
	}
	
	/* Checkboxauswahl mit JS-Action */
	function check_box_js1($name, $id, $wert, $label, $js, $checked) {
		echo "<label for=\"$name\">$label</label>\n";
		echo "<input type=\"checkbox\" id=\"$id\" name=\"$name\" value=\"$wert\" $js $checked>\n";
	}

	/* Checkboxauswahl mit JS-Action */
	function check_box_js1_label_last($name, $id, $wert, $label, $js, $checked) {
		echo "<input type=\"checkbox\" id=\"$id\" name=\"$name\" value=\"$wert\" $js $checked>\n";
		echo "<label for=\"$name\">$label</label>\n";
	}
	
	/* Checkboxauswahl für alle Boxen auf einmal mit JS-Action */
	function check_box_js_alle($name, $id, $wert, $label, $js, $checked, $feld) {
		echo "<label for=\"$name\">$label</label>\n";
		// echo "<input type=\"button\" name=\"button\" onclick='activate(this.form.elements[\"mv_ids[]\"]);' value=\"Alle wählen\">";
		$feld_arr = $feld . '[]';
		echo "<input type=\"checkbox\" id=\"$id\" name=\"$name\" value=\"$wert\" $js $checked onclick='activate(this.form.elements[\"$feld_arr\"]);'>\n";
	}
	
	/* Textbereichsfeld erstellen */
	function text_bereich($beschreibung, $name, $wert, $cols, $rows, $id) {
		echo "<label for=\"$name\">$beschreibung</label>\n";
		echo "<textarea id=\"$id\" name=\"$name\"  cols=\"$cols\" rows=\"$rows\">$wert</textarea>\n";
	}
	
	/* Textbereichsfeld erstellen */
	function text_bereich_js($beschreibung, $name, $wert, $cols, $rows, $id, $js) {
		echo "<label for=\"$name\">$beschreibung</label>\n";
		echo "<textarea id=\"$id\" name=\"$name\"  cols=\"$cols\" rows=\"$rows\" $js>$wert</textarea>\n";
	}
	
	/* Textfeld inaktiv, ausgegraut, nicht veränderbar */
	function text_feld_inaktiv($beschreibung, $name, $wert, $size, $id) {
		echo "<label for=\"$name\">$beschreibung</label>\n";
		echo " <input type=\"text\" id=\"$id\" name=\"$beschreibung.$name\" value=\"$wert\" size=\"$size\" disabled>\n";
	}
	
	/* Textfeld inaktiv, ausgegraut, nicht veränderbar mit JS */
	function text_feld_inaktiv_js($beschreibung, $name, $wert, $size, $id, $js) {
		echo "<label for=\"$name\">$beschreibung</label>\n";
		echo " <input type=\"text\" id=\"$id\" name=\"$name\" $js value=\"$wert\" size=\"$size\" disabled >\n";
	}
	
	/* Textfeld mit ID, JS-Action und Label */
	function text_feld($beschreibung, $name, $wert, $size, $id, $js_action) {
		echo "<label for=\"$name\">$beschreibung</label>\n";
		echo "<input type=\"text\" id=\"$id\" name=\"$name\" value=\"$wert\" size=\"$size\" $js_action >\n";
	}
	
	/* Textfeld mit ID, JS-Action und Label */
	function iban_feld($beschreibung, $name, $wert, $size, $id, $js_action) {
		$js = " onkeyup=\"iban_format('$id')\"";
		$js_action .= $js;
		echo "<label for=\"$name\">$beschreibung</label>\n";
		echo "<input type=\"text\" id=\"$id\" name=\"$name\" value=\"$wert\" size=\"$size\" $js_action >\n";
	}
	function passwort_feld($beschreibung, $name, $wert, $size, $id, $js_action) {
		echo "<label for=\"$name\">$beschreibung</label>\n";
		echo "<input type=\"password\" id=\"$id\" name=\"$name\" value=\"$wert\" size=\"$size\" $js_action >\n";
	}
	
	/* Verstecktes Feld im Formular */
	function hidden_feld($name, $wert) {
		echo "<input type=\"hidden\" id=\"$name\" name=\"$name\" value=\"$wert\" >\n";
	}
	
	/* Datumsfeld mit ID, JS-Action und Label */
	function datum_feld($beschreibung, $name, $wert, $id) {
		$js_datum = "onchange=check_datum('$id')"; // check_datum holt sich den wert vom feld mit der id und prüft ihn!
		$this->text_feld ( "$beschreibung", "$name", "$wert", '10', $id, $js_datum );
	}
	function button_alle_waehlen($name, $wert, $id, $feld, $js) {
		echo "<input type=button name=\"$name\" value=\"$wert\" class=\"submit\" id=\"$name\" $js>";
	}
	
	/* AB HIER NUR ZUSATZFUNKTIONEN DIE AN FORMULARE ANLEHNEN */
	function post_array_bereinigen() {
		foreach ( $_POST as $key => $value ) {
			$clean_value = trim ( strip_tags ( $value ) );
			$clean_arr [$key] = "$clean_value";
		}
		return $clean_arr;
	}
	function post_unterarray_bereinigen($arrayname) {
		foreach ( $_POST [$arrayname] as $key => $value ) {
			$clean_value = trim ( strip_tags ( $value ) );
			$clean_arr [$key] = "$clean_value";
		}
		return $clean_arr;
	}
} // Ende der Klasse formular

?>
