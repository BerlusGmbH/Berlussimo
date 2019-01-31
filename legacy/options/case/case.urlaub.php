<?php

if (request()->filled('daten')) {
	$daten = request()->input('daten');
	switch ($daten) {

		case "urlaub" :
			include(base_path('legacy/options/links/links.urlaub.php'));
			echo "<div id='main'>";
			include(base_path('legacy/options/modules/urlaub.php'));
			echo "</div>";
			break;
	}
}
