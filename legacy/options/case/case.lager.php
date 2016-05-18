<?php

if (request()->has('daten')) {
	$daten = request()->input('daten');
	switch ($daten) {
		
		case "lager" :
			include(base_path('legacy/options/links/links.lager.php'));
			echo "<div id='main'>";
			include(base_path('legacy/options/modules/lager.php'));
			echo "</echo>";
			break;
	}
}
