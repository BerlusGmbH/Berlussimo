<?php

class formular
{
    function fieldset($legend_name, $id)
    {
        echo "<fieldset class=\"$id\" id=\"$id\" style=\"margin:0;padding:0;\">\n";
        echo "<legend>$legend_name</legend>\n";
    }

    function fieldset_ende()
    {
        echo "</fieldset>\n";
    }

    /* Formular erstellen bzw. anfangen, inkl legend, action */
    function erstelle_formular($name, $action, $legend = null)
    {
        $scriptname = $_SERVER['REQUEST_URI'];

        if (!isset ($action)) {
            echo "<form class='$name' name='$name' action='$scriptname'  method='post' >\n";
        } else {
            echo "<form class='$name' name=\"$name\" action=\"$action\" method=\"post\">\n";
        }
        echo "\n";

        echo "<fieldset class=\"$name\" id=\"$name\">\n";
        echo "<legend>" . ((!is_null($legend)) ? $legend : $name) . "</legend>\n";
    }

    /* Formular abschliessen */
    function ende_formular()
    {
        echo "</fieldset></form>\n";
    }

    /* Button mit JS-Action */
    function button_js($name, $wert, $js)
    {
        echo "<a class=\"waves-effect waves-light btn\" name=\"$name\" id=\"$name\" $js>$wert</a>";
    }

    /* Sendenbutton mit JS-Action */
    function send_button_js($name, $wert, $js)
    {
        echo "<button type=\"submit\" name=\"$name\" value=\"$wert\" class=\"btn waves-effect waves-light\" id=\"$name\" $js><i class=\"mdi mdi-send right\"></i>$wert</button>";
    }

    function send_button_disabled($name, $wert, $id)
    {
        echo "<button type=\"submit\" name=\"$name\" value=\"$wert\" id=\"$id\" class=\"btn waves-effect waves-light\" disabled><i class=\"mdi mdi-send right\"></i>$wert</button>";
    }

    /* Sendenbutton normal */
    function send_button($name, $wert, $icon = 'send', $allignment = 'right')
    {
        echo "<button type=\"submit\" name=\"$name\" value=\"$wert\" class=\"btn waves-effect waves-light\" id=\"$name\"><i class=\"mdi mdi-$icon $allignment\"></i>$wert</button>";
    }

    /* Radioauswahl normal */
    function radio_button($name, $wert, $label)
    {
        echo "<input type=\"radio\" id=\"$name\" name=\"$name\" value=\"$wert\">\n";
        echo "<label for=\"$name\">$label</label>\n";
    }

    /* Radioauswahl mit JS-Action */
    function radio_button_js($name, $wert, $label, $js, $checked)
    {
        echo "<input type=\"radio\" id=\"$name\" name=\"$name\" value=\"$wert\" $js $checked>\n";
        echo "<label for=\"$name\">$label</label>\n";
    }

    /* Checkboxauswahl mit JS-Action */
    function check_box_js($name, $wert, $label, $js, $checked)
    {
        echo "<input type=\"checkbox\" class='filled-in' id=\"$wert\" name=\"$name\" value=\"$wert\" $js $checked>\n";
        echo "<label for=\"$wert\">$label</label>\n";
    }

    /* Checkboxauswahl mit JS-Action */
    function check_box_js1($name, $id, $wert, $label, $js, $checked)
    {
        echo "<input type=\"checkbox\" class='filled-in' id=\"$id\" name=\"$name\" value=\"$wert\" $js $checked>\n";
        echo "<label for=\"$id\">$label</label>\n";
    }

    /* Checkboxauswahl für alle Boxen auf einmal mit JS-Action */
    function check_box_js_alle($name, $id, $wert, $label, $js, $checked, $feld)
    {
        // echo "<input type=\"button\" name=\"button\" onclick='activate(this.form.elements[\"mv_ids[]\"]);' value=\"Alle wählen\">";
        $feld_arr = $feld . '[]';
        echo "<input type=\"checkbox\" class='filled-in' id=\"$id\" name=\"$name\" value=\"$wert\" $js $checked onclick='activate(this.form.elements[\"$feld_arr\"]);'>\n";
        echo "<label for=\"$id\">$label</label>\n";
    }

    /* Textbereichsfeld erstellen */
    function text_bereich($beschreibung, $name, $wert, $cols, $rows, $id)
    {
        echo "<div class=\"input-field\">";
        echo "<textarea id=\"$id\" name=\"$name\" class=\"materialize-textarea\" cols=\"$cols\" rows=\"$rows\">$wert</textarea>\n";
        echo "<label for=\"$id\">$beschreibung</label>\n";
        echo "</div>";
    }

    /* Textbereichsfeld erstellen */
    function text_bereich_js($beschreibung, $name, $wert, $cols, $rows, $id, $js)
    {
        echo "<div class=\"input-field\">";
        echo "<textarea id=\"$id\" name=\"$name\"  cols=\"$cols\" rows=\"$rows\" $js>$wert</textarea>\n";
        echo "<label for=\"$id\">$beschreibung</label>\n";
        echo "</div>";
    }

    /* Textfeld inaktiv, ausgegraut, nicht veränderbar */
    function text_feld_inaktiv($beschreibung, $name, $wert, $size, $id)
    {
        echo "<div class=\"input-field\">";
        echo "<input type=\"text\" id=\"$id\" name=\"$beschreibung.$name\" value=\"$wert\" size=\"$size\" disabled>\n";
        echo "<label for=\"$id\">$beschreibung</label>\n";
        echo "</div>";
    }

    /* Textfeld inaktiv, ausgegraut, nicht veränderbar mit JS */
    function text_feld_inaktiv_js($beschreibung, $name, $wert, $size, $id, $js)
    {
        echo "<div class=\"input-field\">";
        echo "<input type=\"text\" id=\"$id\" name=\"$name\" $js value=\"$wert\" size=\"$size\" disabled >\n";
        echo "<label for=\"$id\">$beschreibung</label>\n";
        echo "</div>";
    }

    /* Textfeld mit ID, JS-Action und Label */

    function iban_feld($beschreibung, $name, $wert, $size, $id, $js_action)
    {
        echo "<div class=\"input-field\">";
        $js = " onkeyup=\"iban_format('$id')\"";
        $js_action .= $js;
        echo "<input type=\"text\" id=\"$id\" name=\"$name\" value=\"$wert\" size=\"$size\" $js_action >\n";
        echo "<label for=\"$id\">$beschreibung</label>\n";
        echo "</div>";
    }

    /* Textfeld mit ID, JS-Action und Label */

    function passwort_feld($beschreibung, $name, $wert, $size, $id, $js_action)
    {
        echo "<div class=\"input-field\">";
        echo "<input type=\"password\" id=\"$id\" name=\"$name\" value=\"$wert\" size=\"$size\" $js_action >\n";
        echo "<label for=\"$id\">$beschreibung</label>\n";
        echo "</div>";
    }

    function hidden_feld($name, $wert)
    {
        echo "<input type=\"hidden\" id=\"$name\" name=\"$name\" value=\"$wert\" >\n";
    }

    /* Verstecktes Feld im Formular */

    function datum_feld($beschreibung, $name, $wert, $id)
    {
        $js_datum = "onchange=check_datum('$id')"; // check_datum holt sich den wert vom feld mit der id und prüft ihn!
        $this->text_feld("$beschreibung", "$name", "$wert", '10', $id, $js_datum);
    }

    /* Datumsfeld mit ID, JS-Action und Label */

    function text_feld($beschreibung, $name, $wert, $size, $id, $js_action)
    {
        echo "<div class=\"input-field\">";
        echo "<input type=\"text\" id=\"$id\" name=\"$name\" value=\"$wert\" size=\"$size\" $js_action >\n";
        echo "<label for=\"$id\">$beschreibung</label>\n";
        echo "</div>";
    }

    /* AB HIER NUR ZUSATZFUNKTIONEN DIE AN FORMULARE ANLEHNEN */
    function post_array_bereinigen()
    {
        foreach (request()->request->all() as $key => $value) {
            $clean_value = trim(strip_tags($value));
            $clean_arr [$key] = "$clean_value";
        }
        return $clean_arr;
    }
} // Ende der Klasse formular
