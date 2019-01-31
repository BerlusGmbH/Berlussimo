<?php

if (request()->filled('daten')) {
	$daten = request()->input('daten');
	switch ($daten) {
		case "partner" :
			include(base_path('legacy/options/links/links.partner.php'));
			echo "<div id='main'>";
			include(base_path('legacy/options/modules/partner.php'));
			echo "</div>";
			break;
	}
}
