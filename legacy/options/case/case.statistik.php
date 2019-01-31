<?php

if (request()->filled('daten')) {
	$daten = request()->input('daten');
	switch ($daten) {

		case "statistik" :
			include(base_path('legacy/options/links/links.statistik.php'));
			echo "<div id='main'>";
			include(base_path('legacy/options/modules/statistik.php'));
			echo "</div>";
			break;
	}
}