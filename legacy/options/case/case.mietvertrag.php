<?php

if (request()->has('daten')) {
	$daten = request()->input('daten');
	switch ($daten) {

		case "mietvertrag_raus" :
			include(base_path('legacy/options/links/links.mietvertrag.php'));
			echo "<div id='main'>";
			include(base_path('legacy/options/modules/mietvertrag.php'));
			echo "</div>";
			break;
	}
}
