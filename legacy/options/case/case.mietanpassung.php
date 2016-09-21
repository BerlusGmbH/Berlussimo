<?php

if (request()->has('daten')) {
	$daten = request()->input('daten');
	switch ($daten) {
		
		case "mietanpassung" :
			include(base_path('legacy/options/links/links.mietkonten_blatt.php'));
			echo "<div id='main'>";
			include(base_path('legacy/options/modules/mietanpassung.php'));
			echo "</div>";
			break;
	}
}