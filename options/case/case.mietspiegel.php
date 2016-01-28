<?php

if(isset($_REQUEST["daten"])){
	$daten = $_REQUEST["daten"];
	switch($daten) {

		case "mietspiegel":
			include("options/modules/mietspiegel.php");
			break;
	}





}

?>