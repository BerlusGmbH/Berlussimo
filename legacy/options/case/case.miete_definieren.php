<?php

if (request()->has('daten')) {
	$daten = request()->input('daten');
	switch ($daten) {
		
		case "miete_definieren" :
			include(base_path('legacy/options/links/links.mietkonten_blatt.php'));
			echo "<div id='main'>";
			include(base_path('legacy/options/modules/miete_definieren.php'));
			echo "</div>";
			break;
	}
}
