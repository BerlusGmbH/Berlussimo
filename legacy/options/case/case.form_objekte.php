<?php

if (request()->filled('formular')) {
	$formular = request()->input('formular');
	switch ($formular) {
		
		case "objekte" :
			include(base_path('legacy/options/links/links.form_objekte.php'));
			echo "<div id='main'>";
			include(base_path('legacy/options/formulare/form_objekte.php'));
			echo "</div>";
			break;
	}
}
