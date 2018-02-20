<?php

require( __DIR__ . '/../SVGraph.php');

$desc = request()->input('desc');
$x_label = request()->input('x_label');
$y_label = request()->input('y_label');
$geldkonto_id = request()->input('geldkonto_id');

$graph = new LineGraph();

$graph->setEcmascriptUrl(mix('js/PieGraph.js'));
$graph->setStylesheetUrl(mix('css/PieGraph.css'));

$graph->setGraphTitle($desc);
$graph->setGraphDescription($desc);
	
$graph->setXAxisLabel($x_label);
$graph->setYAxisLabel($y_label);
	
$graph->setXAxis(array('Jan', 'Feb', 'März', 'April', 'Mai', 'Juni', 'Juli', 'August', 'Sept.', 'Oktober', 'Nov', 'Dez'));

$me_monat = session()->get('daten_arr')[$geldkonto_id]['me_monat'];
$graph->addDataRow($me_monat, 'MIETEINNAHMEN');
	
$kosten_monat = session()->get('daten_arr')[$geldkonto_id]['kosten_monat'];
$graph->addDataRow($kosten_monat, 'KOSTEN');
	
$graph->addAverage(0, 'Average for my first data row');
$graph->addMovingAverage(2, 0, 'Moving average for my first data row');
$graph->addAverage(); // Total average line
$graph->output();