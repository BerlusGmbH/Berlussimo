<?php

if (request()->input('daten')) {
	$daten = request()->input('daten');
	switch ($daten) {
		
		case "mietkonten_blatt" :
			include(base_path('legacy/options/links/links.mietkonten_blatt.php'));
			echo "<div id='main'>";
			include(base_path('legacy/options/modules/mietkonten_blatt.php'));
			break;
	}
}
