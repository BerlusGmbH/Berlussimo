<?php

require('../SVGraph.php');

$graph = new BarGraph();

	$graph->setGraphTitle('My bar graph');
	$graph->setGraphDescription('Just a demo');
	
	$graph->setXAxisLabel('My X-Axis');
	$graph->setYAxisLabel('My Y-Axis');
	
	$graph->setXAxis(array('put', 'some', 'text', 'or', 'values', 'like', 0, 'or', 0.75, 'here'));
	
	$graph->addDataRow(array(1,   1,   1,   1,   1,   1,   1,   10,   15,   20),    'My first data row');
	$graph->addDataRow(array(0.5, 1.5, 2.5, 3.5, 4.5, 5.5, 6.5, 7.5, 8.5, 9.5),  'My second data row');
	$graph->addDataRow(array(10,  9,   8,   7,   6,   5,   4,   3,   2,   1),    'My third data row');
	
	$graph->addAverage(0, 'Average for my first data row');
	$graph->addMovingAverage(2, 0, 'Moving average for my first data row');
	$graph->addAverage(); // Total average line
	
//print_r($graph);
	//$graph->output();

?>