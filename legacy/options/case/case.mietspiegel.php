<?php

if (request()->filled('daten')) {
	$daten = request()->input('daten');
	switch ($daten) {
		
		case "mietspiegel" :
			include(base_path('legacy/options/links/links.mietspiegel.php'));
			echo "<div id='main'>";
			include(base_path('legacy/options/modules/mietspiegel.php'));
			echo "</div>";
			break;
	}
}