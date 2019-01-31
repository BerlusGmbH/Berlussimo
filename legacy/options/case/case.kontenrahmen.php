<?php

if (request()->filled('daten')) {
	$daten = request()->input('daten');
	switch ($daten) {
		
		case "kontenrahmen" :
			include(base_path('legacy/options/links/links.kontenrahmen.php'));
			echo "<div id='main'>";
			include(base_path('legacy/options/modules/kontenrahmen.php'));
			echo "</div>";
			break;
	}
}
