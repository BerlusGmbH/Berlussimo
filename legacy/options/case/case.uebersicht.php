<?php

if (request()->filled('daten')) {
	$daten = request()->input('daten');
	switch ($daten) {
		
		case "uebersicht" :
			echo "<div id='main'>";
			include(base_path('legacy/options/modules/uebersicht.php'));
			echo "</div>";
			break;
	}
}
