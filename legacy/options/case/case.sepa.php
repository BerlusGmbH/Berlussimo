<?php

if (request()->has('daten')) {
	$daten = request()->input('daten');
	switch ($daten) {
		
		case "sepa" :
			include(base_path('legacy/options/links/links.sepa.php'));
			echo "<div id='main'>";
			include(base_path('legacy/options/modules/sepa.php'));
			echo "</div>";
			break;
	}
}