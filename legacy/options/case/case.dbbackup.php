<?php

if (request()->has('daten')) {
    $daten = request()->input('daten');
    switch ($daten) {

        case "dbbackup" :
            include(base_path('legacy/options/links/links.dbbackup.php'));
            echo "<div id='main'>";
            include(base_path('legacy/options/modules/dbbackup.php'));
            echo "</div>";
            break;
    }
}