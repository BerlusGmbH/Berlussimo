<?php

function getGraduation($min, $max, $nrOfSteps) {

	$min = (float) $min;
	$max = (float) $max;
	$nrOfSteps = (int) $nrOfSteps;
		if($nrOfSteps <= 0) return false;
	
	$return = array();
	
	/* get min */		
	if($min < 0) {
		$tmpMin = floor(floatval($min)/10) * 10;
	}
	else
		$tmpMin = 0;
	if(!isset($tmpMin)) $tmpMin = 0;
	$return['min'] = $tmpMin;
	
	/* get max */
	if($max >= 50)
		$tmpMax = ceil(floatval($max)/10) * 10;
	else if($max >= 10)
		$tmpMax = ceil(floatval($max)/1) * 1;
	else {
		for($i=1; $i>0; $i/=10) {
			if($max >= $i) {
				$tmpMax = ceil(floatval($max)/$i) * $i;
				break;
			}
		}
	}
	if(!isset($tmpMax)) $tmpMax = 0;
	$return['max'] = $tmpMax;
		
	// get step
	$return['step'] = ($return['max'] - $return['min']) / $nrOfSteps;
	
	return $return;
}

function throwError($errMsg, $methodName) {
	echo "<br /><b>Warning</b>: $methodName(): $errMsg<br />";
}
function getSVGRaphRoot() {
	$return = dirname(__FILE__) . '/';
	$return = str_replace('\\', '/', $return);
	$return = str_replace($_SERVER['DOCUMENT_ROOT'], '', $return);
	if(substr($return, 0, 1) != '/') $return = '/' . $return;
	//return $return;
	return '/';
}
function xmlEscape($text) {
	$text = str_replace('&', '&amp;', $text);
	$text = str_replace('<', '&lt;', $text);
	$text = str_replace('>', '&gt;', $text);
	return $text;
}

function outputSVGHeader() {
	header('Content-Type: image/svg+xml');
}
function outputXMLVersion() {
	echo '<?xml version="1.0" encoding="ISO-8859-1" standalone="no" ?>' . "\n";
}
function outputCopyright() {
	echo
		'<!--' . "\n\n\n" .
			"\t" . 'This graph was created using SVGraph' . "\n" .
			"\t" . 'for more information on SVGraph please visit http://slauth.de/projekte/SVGraph' . "\n\n" .
		'-->' . "\n\n"
	;
}
function outputDTD() {
	echo '<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">' . "\n\n";
	//echo '<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 20010904//EN" "http://www.w3.org/TR/2001/REC-SVG-20010904/DTD/svg10.dtd">' . "\n\n";
}