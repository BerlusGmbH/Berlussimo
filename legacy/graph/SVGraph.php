<?php	
	// load files
	require_once('public.inc.php');
	require_once('functions.inc.php');
	
	// check PHP-Version
	$version = phpversion();
	$version = (float) $version;
	if($version < 5) {
		echo '<br />' . "\n" . '<b>Fatal error</b>: PHP ' . phpversion() . ' is installed on your system. <b>SVGraph</b> requires PHP 5 or higher.';
		exit();
	}
	
	// load classes
	require_once('BarGraph.php');
	require_once('LineGraph.php');
	require_once('PieGraph.php');