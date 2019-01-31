<?php

if (request()->filled('formular')) {
    switch (request()->input('formular')) {

        case "einheit" :
            include(base_path('legacy/options/links/links.form_einheit.php'));
            echo "<div id='main'>";
            include(base_path('legacy/options/formulare/form_einheit.php'));
            echo "</div>";
            break;
    }
}
