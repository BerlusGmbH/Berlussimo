<?php

require('../SVGraph.php');
$leerstand = $_REQUEST['leerstand'];
$vermietet = $_REQUEST['vermietet'];
if(empty($leerstand)){
$leerstand=0.00;
}
if(empty($vermietet)){
$vermietet=0.00;
}
$objekt = $_REQUEST['objekt'];
$desc = "Statistik $objekt $_REQUEST[jahr]";

$graph = new PieGraph();

	
	$graph->setGraphTitle($desc);
	$graph->setGraphDescription($desc);
	


	$graph->addSegment($leerstand, 'Leerstand');
	$graph->addSegment($vermietet, 'Vermietet');
	
	$graph->addHyperlink('http://slauth.de/projekte/SVGraph');
	
$graph->output();




?>