<?php

if (request()->has('daten')) {
	$daten = request()->input('daten');
	switch ($daten) {
		
		case "personal" :
			include(base_path('legacy/options/links/links.personal.php'));
			echo "<div id='main'>";
			include(base_path('legacy/options/modules/personal.php'));
			echo "</div>";
			break;
	}
}
