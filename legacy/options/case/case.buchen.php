<?php

if (request()->filled('daten')) {
    $daten = request()->input("daten");
    switch ($daten) {

        case "buchen" :
            include(base_path('legacy/options/links/links.buchen.php'));
            echo "<div id='main'>";
            include(base_path('legacy/options/modules/buchen.php'));
            echo "</div>";
            break;
    }
}