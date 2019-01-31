<?php

if (request()->filled('daten')) {
	$daten = request()->input('daten');
	switch ($daten) {
		
		case "tickets" :
			include(base_path('legacy/options/links/links.tickets.php'));
			echo "<div id='main'>";
			include(base_path('legacy/options/modules/tickets.php'));
			echo "</div>";
			break;
	}
}