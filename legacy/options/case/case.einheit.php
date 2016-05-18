<?php

if (request()->has('daten')) {
    $daten = request()->input('daten');
    switch ($daten) {

        case "einheit_raus" :
            include(base_path('legacy/options/links/links.form_einheit.php'));
            echo "<div id='main'>";
            include(base_path('legacy/options/modules/einheit.php'));
            echo "</div>";
            break;
    }
}
