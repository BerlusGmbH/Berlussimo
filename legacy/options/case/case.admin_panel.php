<?php

if (request()->has('optionen')) {
	$daten = request()->input('optionen');
	switch ($daten) {
		
		case "admin_panel" :
			include(base_path('legacy/options/links/links.admin_menu.php'));
            echo "<div id='main'>";
			include(base_path('legacy/options/modules/admin_panel.php'));
            echo "</div>";
			break;
	}
}
