<?php

if (request()->has('daten')) {
	$daten = request()->input('daten');
	switch ($daten) {
		
		case "bk" :
			include(base_path('legacy/options/links/links.bk.php'));
			echo "<div id='main'>";
			include(base_path('legacy/options/modules/bk.php'));
            echo "</div>";
			break;
	}
}
