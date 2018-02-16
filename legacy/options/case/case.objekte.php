<?php

if (request()->has('daten')) {
	$daten = request()->input('daten');
	switch ($daten) {
		
		case "objekte_raus" :
			include(base_path('legacy/options/links/links.form_objekte.php'));
			echo "<div id='main'>";
			include(base_path('legacy/options/modules/objekte.php'));
			break;
	}
}
