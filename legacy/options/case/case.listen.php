<?php

if (request()->has('daten')) {
	$daten = request()->input('daten');
	switch ($daten) {
		
		case "listen" :
			include(base_path('legacy/options/links/links.listen.php'));
			echo "<div id='main'>";
			include(base_path('legacy/options/modules/listen.php'));
			echo "</div>";
			break;
	}
}