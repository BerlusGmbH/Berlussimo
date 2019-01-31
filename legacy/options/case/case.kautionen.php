<?php

if (request()->filled('daten')) {
    $daten = request()->input('daten');
    switch ($daten) {

        case "kautionen" :
            include(base_path('legacy/options/links/links.kautionen.php'));
            echo "<div id='main'>";
            include(base_path('legacy/options/modules/kautionen.php'));
            echo "</div>";
            break;
    }
}

?>
