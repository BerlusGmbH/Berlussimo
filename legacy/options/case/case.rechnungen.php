<?php

if (request()->has('daten')) {
	$daten = request()->input('daten');
	switch ($daten) {
		
		case "rechnungen" :
			include(base_path('legacy/options/links/links.rechnungen.php'));
			echo "<div id='main'>";
			include(base_path('legacy/options/modules/rechnungen.php'));
			echo "</div>";
			break;
	}
}