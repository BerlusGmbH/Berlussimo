<?php

require( __DIR__ . '/../SVGraph.php');
$leerstand = request()->input('leerstand');
$vermietet = request()->input('vermietet');
if(empty($leerstand)){
$leerstand=0.00;
}
if(empty($vermietet)){
$vermietet=0.00;
}
$objekt = request()->input('objekt');
$desc = "Statistik $objekt " . request()->input('jahr');

$graph = new PieGraph();

$graph->setEcmascriptUrl(asset('/js/PieGraph.js'));
$graph->setStylesheetUrl(asset('/css/PieGraph.css'));

$graph->setGraphTitle($desc);
$graph->setGraphDescription($desc);

$graph->addSegment($leerstand, 'Leerstand');
$graph->addSegment($vermietet, 'Vermietet');

$graph->output();