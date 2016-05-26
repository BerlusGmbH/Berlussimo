<?php
if (isset ( $_REQUEST ["daten"] )) {
	$daten = $_REQUEST ["daten"];
	switch ($daten) {
		
		case "mietspiegel" :
			include_once ("options/links/links.mietspiegel.php");
			echo "<div id='main'>";
			include ("options/modules/mietspiegel.php");
			echo "</div>";
			break;
	}
}

?>