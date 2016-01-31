<?php
	session_start();
	#echo "<pre>";
	#print_r($_SESSION[daten_arr]);

require('../SVGraph.php');

$desc = $_REQUEST['desc'];
$x_label = $_REQUEST['x_label'];
$y_label = $_REQUEST['y_label'];
$geldkonto_id =$_REQUEST['geldkonto_id'];

$graph = new LineGraph();


	$graph->setGraphTitle($desc);
	$graph->setGraphDescription($desc);
	
	$graph->setXAxisLabel($x_label);
	$graph->setYAxisLabel($y_label);
	
	$graph->setXAxis(array('Jan', 'Feb', 'März', 'April', 'Mai', 'Juni', 'Juli', 'August', 'Sept.', 'Oktober', 'Nov', 'Dez'));
	
	/**$graph->addDataRow(array(92,   91,   90,   90, 90, 91,91,91,90,88),    'Vermietet');
	$graph->addDataRow(array(20,21,22,22,22,21,21,21,22,24),  'Leer');
	$graph->addDataRow(array(1,3,5,7,9,11,13,15,25,45),  'test');
	$graph->addDataRow(array(20,21,22,22,22,21,21,21,22,24),  'Leer');
	**/
	$me_monat = $_SESSION['daten_arr'][$geldkonto_id]['me_monat'];
	$graph->addDataRow($me_monat, 'MIETEINNAHMEN');
	
	$kosten_monat = $_SESSION['daten_arr'][$geldkonto_id]['kosten_monat'];
	$graph->addDataRow($kosten_monat, 'KOSTEN');
	#$graph->addDataRow(array(10,  9,   8,   7,   6,   5,   4,   3,   2,   1),    'My third data row');
	
	$graph->addAverage(0, 'Average for my first data row');
	$graph->addMovingAverage(2, 0, 'Moving average for my first data row');
	$graph->addAverage(); // Total average line
	$graph->output();
?>