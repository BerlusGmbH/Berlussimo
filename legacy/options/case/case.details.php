<?php

if (request()->has('daten')) {
    $daten = request()->input('daten');
    switch ($daten) {

        case "details" :
            include(base_path('legacy/options/links/links.details.php'));
            echo "<div id='main'>";
            include(base_path('legacy/options/modules/details.php'));
            echo "</div>";
            break;
    }
}