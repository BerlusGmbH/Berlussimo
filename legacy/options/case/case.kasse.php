<?php

if (request()->has('daten' )) {
	$daten = request()->input('daten');
	switch ($daten) {
		
		case "kasse" :
			include(base_path('legacy/options/links/links.kasse.php'));
			echo "<div id='main'>";
			include(base_path('legacy/options/modules/kasse.php'));
			echo "</div>";
			break;
	}
}
