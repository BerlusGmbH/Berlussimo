<?php

if (request()->filled('daten')) {
	$daten = request()->input('daten');
	switch ($daten) {
		
		case "benutzer" :
			include(base_path('legacy/options/links/links.benutzer.php'));
			echo "<div id='main'>";
			include(base_path('legacy/options/modules/benutzer.php'));
			echo "</div>";
			break;
	}
}
