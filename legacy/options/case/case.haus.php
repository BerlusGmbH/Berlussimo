<?php

if (request()->filled('daten')) {
	switch (request()->input('daten')) {
		
		case "haus_raus" :
			include(base_path('legacy/options/links/links.form_haus.php'));
			echo "<div id='main'>";
			include(base_path('legacy/options/modules/haus.php'));
			echo "</div>";
			break;
	}
}
