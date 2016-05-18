<?php

if (request()->has('daten')) {
	$daten = request()->input('daten');
	switch ($daten) {
		
		case "geldkonten" :
			include(base_path('legacy/options/links/links.geldkonten.php'));
			echo "<div id='main'>";
			include(base_path('legacy/options/modules/geldkonten.php'));
			echo "</div>";
			break;
	}
}
