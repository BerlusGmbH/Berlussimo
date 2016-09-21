<?php

if (request()->has('formular')) {
	switch (request()->input('formular')) {
		
		case "haus" :
			include(base_path('legacy/options/links/links.form_haus.php'));
			echo "<div id='main'>";
			include(base_path('legacy/options/formulare/form_haus.php'));
			echo "</div>";
			break;
	}
}
