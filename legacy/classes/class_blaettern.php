<?php

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
        if (request()->filled('position')) {
            $this->limit = "LIMIT " . request()->input('position') . ",$zeilen_pro_seite";
            $aktuelle_seite = intval ( request()->input('position') / $zeilen_pro_seite );
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