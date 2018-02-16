<?php

if (request()->has('daten')) {
	$daten = request()->input('daten');
	switch ($daten) {

		case "katalog" :
			include(base_path('legacy/options/links/links.katalog.php'));
			echo "<div id='main'>";
			include(base_path('legacy/options/modules/katalog.php'));
			echo "</div>";
			break;
	}
}
