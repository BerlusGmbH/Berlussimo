<?php

if (request()->has('daten')) {
	$daten = request()->input('daten');
	switch ($daten) {
		
		case "weg" :
			include(base_path('legacy/options/links/links.weg.php'));
			echo "<div id='main'>";
			include(base_path('legacy/options/modules/weg.php'));
			echo "</div>";
			break;
	}
}
