<?php

if (request()->has('daten')) {
	$daten = request()->input('daten');
	switch ($daten) {
		
		case "person" :
			include(base_path('legacy/options/links/links.person.php'));
			echo "<div id='main'>";
			include(base_path('legacy/options/modules/person.php'));
			echo "</div>";
			break;
	}
}
