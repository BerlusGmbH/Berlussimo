<?php

if (request()->has('daten')) {
	$daten = request()->input('daten');
	switch ($daten) {

		case "zeiterfassung" :
			include(base_path('legacy/options/links/links.zeiterfassung.php'));
			echo "<div id='main'>";
			include(base_path('legacy/options/modules/zeiterfassung.php'));
			echo "</div>";
			break;
	}
}
