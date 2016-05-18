<?php

if (request()->has('daten')) {
	$daten = request()->input('daten');
	switch ($daten) {
		
		case "leerstand" :
			include(base_path('legacy/options/links/links.leerstand.php'));
			echo "<div id='main'>";
			include(base_path('legacy/options/modules/leerstand.php'));
			echo "</div>";
			break;
	}
}
