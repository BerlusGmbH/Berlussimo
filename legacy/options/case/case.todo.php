<?php

if (request()->input('daten')) {
	$daten = request()->input('daten');
	switch ($daten) {
		
		case "todo" :
			include(base_path('legacy/options/links/links.todo.php'));
			echo "<div id='main'>";
			include(base_path('legacy/options/modules/todo.php'));
			echo "</div>";
			break;
	}
}
