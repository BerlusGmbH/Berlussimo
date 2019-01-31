<?php

if (request()->filled('daten')) {
	$daten = request()->input('daten');
	switch ($daten) {
		
		case "miete_buchen" :
			include(base_path('legacy/options/links/links.mietkonten_blatt.php'));
			echo "<div id='main'>";
			include(base_path('legacy/options/modules/buchungsmaske.php'));
			break;
	}
}
