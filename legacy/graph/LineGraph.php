<?php

class LineGraph {

	private $SVGraphRoot, $stylesheetUrl, $ecmascriptUrl;
	private $graphTitle, $graphDescription, $xAxisLabel, $yAxisLabel;
	private $xAxisValues, $dataRow, $dataRowNames;
	private $maxValue, $minValue, $stepValue, $nrOfSteps, $nullPoint;
	private $averages, $movingAverages, $hyperlinks;
	private $averagesNames, $movingAveragesNames;
	private $markedAreas;
	private $nrOfMeasuringPoints, $measuringPointWidth;
	private $nrOfDataRows, $rectWidth, $rectWidthRatio;
	private $outputTimestamp, $timestampText, $timestampFormat, $legendText;
	private $showValues;
	
	public function LineGraph() {
		$this->SVGraphRoot = getSVGraphRoot();
		$this->stylesheetUrl = $this->SVGraphRoot . 'css/LineGraph.css';
		$this->ecmascriptUrl = $this->SVGraphRoot . 'js/LineGraph.js';
		
		$this->graphTitle = false;
		$this->graphDescription = false;
		$this->xAxisLabel = false;
		$this->yAxisLabel = false;
		
		$this->xAxisValues = array();
		$this->dataRow = array();
		$this->dataRowNames = array();
		
		$this->nrOfSteps = 20;
		$this->nullPoint = 100;
		
		$this->averages = array();
		$this->averagesNames = array();
		$this->movingAverages = array();
		$this->movingAveragesNames = array();
		
		$this->markedAreas = array('horizontal' => array(), 'vertical' => array());
		
		$this->nrOfMeasuringPoints = 0;
		$this->measuringPointWidth = 0;
		
		$this->nrOfDataRows = 0;
		$this->rectWidth = 0;
		$this->rectWidthRatio = 0.5;
		
		$this->outputTimestamp = true;
		$this->timestampText = 'generated on';
		$this->timestampFormat = 'Y-m-d, H:i:s';
		$this->legendText = 'Key';
		
		$this->showValues = true;
	}
	
	public function setXAxis($values) {
		/**
		 *	Sets values of the X-Axis
		 *
		 *	@param $values: An array of values
		 *
		 *	@return: TRUE if setting the values suceeded, FALSE if not
		 */
		 
		 if(!is_array($values)) {
			throwError('First argument should be an array', __METHOD__);
			return false;
		}
		 
		 $this->xAxisValues = $values;
		 $this->nrOfMeasuringPoints = count($values);
		 $this->measuringPointWidth = 100 / $this->nrOfMeasuringPoints;
		 
		 return true;
	}

	public function addDataRow($values, $name=false) {
		/**
		 *	Adds a data row to the existing graph
		 *
		 *	@param $values: An array of values
		 *	@param $name (optional): The name of the data row
		 *
		 *	@return: TRUE if adding the data row suceeded, FALSE if not
		 */
		 
		if(!is_array($values)) {
			throwError('First argument should be an array', __METHOD__);
			return false;
		}
		if($name!==false) $name = (string) $name;
		else $name = 'Data row ' . ($this->nrOfDataRows+1);
		
		$this->dataRow[] = $values;
		$this->dataRowNames[] = $name;
		$this->nrOfDataRows++;
		
		$max = max($values);
		$min = min($values);
		
		$this->calculateGraduation($min, $max);
		
		$this->averages[] = false;
		$this->averagesNames[] = false;
		$this->movingAverages[] = false;
		$this->movingAveragesNames[] = false;
		
		$this->hyperlinks[] = false;
		
		// re-calculate rect width
		$this->calculateRectSpecs();
		
		return true;
	}
	public function addAverage($dataRow=false, $name=false) {
		/**
		 *	Adds an average line (total or for a specific data row) to the existing graph.
		 *
		 *	@param $dataRow: The number of the data row, from which the average shall be displayed.
		 *					 If no parameter is given, the average from all data rows will be displayed.
		 *
		 *	@return: TRUE if adding the average line suceeded, FALSE if not
		 */
		 
		if($dataRow!==false && $dataRow!=='total') {
			/* average for a specific data row */
			$dataRow = (int) $dataRow;
			if($dataRow < 0 || $dataRow >= $this->nrOfDataRows) return false;
			
			// get name for average
			if($name!==false) $name = (string) $name;
			else $name = $this->dataRowNames[$dataRow] . ' average';
			$this->averagesNames[$dataRow] = $name;
			
			// calc avg
			$avg=0; $i=0;
			foreach($this->dataRow[$dataRow] as $value) {
				$avg += $value;
				$i++;
			}
			$i>0 ? $avg/=$i : $avg=false;
			$this->averages[$dataRow] = $avg;
		}
		else {
			/* total average */
			
			// get name for average
			if($name!==false) $name = (string) $name;
			else $name = 'Total average';
			$this->averagesNames['total'] = $name;
			
			// calc avg
			$avg=0; $i=0;
			foreach($this->dataRow as $array) {
				foreach($array as $value) {
					$avg += $value;
					$i++;
				}
			}
			$avg/=$i;
			$this->averages['total'] = $avg;
		}		 
		return true;
	}
	public function addMovingAverage($precision, $dataRow=false, $name=false) {
		/**
		 *	Adds a moving average line (total or for a specific data row) to the existing graph.
		 *
		 *	@param $precision: The number of data sets that will be used for calculation of the moving average.
		 *	@param $dataRow: The number of the data row, from which the moving average shall be displayed.
		 *					 If no parameter is given, the average from all data rows will be displayed.
		 *
		 *	@return: TRUE if adding the moving average line suceeded, FALSE if not
		 */
		 
		 $precision = (int) $precision;
			if($precision < 1 || $precision > $this->nrOfMeasuringPoints) return false;
			
		 if($dataRow!==false && $dataRow!=='total') {
			/* moving average for a specific data row */
			$dataRow = (int) $dataRow;
			if($dataRow < 0 || $dataRow >= $this->nrOfDataRows) return false;
			
			// get name for average
			if($name!==false) $name = (string) $name;
			else $name = $this->dataRowNames[$dataRow] . ' moving average';
			$this->movingAveragesNames[$dataRow] = $name;

			// calc avg
			$avg = array();
			for($i=0; $i<count($this->dataRow[$dataRow]); $i++) {
				if($i<$precision-1) {
					// not enough measuring points to build average
					$avg[] = false;
				}
				else {
					$tmp = 0; $count = 0;
					// calc avg from last {{$precision}} measuring points
					for($j=$i-($precision-1); $j<=$i; $j++) {
						$tmp += $this->dataRow[$dataRow][$j];
						$count++;
					}
					$count>0 ? $avg[] = $tmp/$count : $avg[] = false;
				}
			}
			$this->movingAverages[$dataRow] = $avg;
		 }
		 else {
			/* total moving average */
			
			// get name for average
			if($name!==false) $name = (string) $name;
			else $name = 'Total moving average';
			$this->movingAveragesNames['total'] = $name;
			
			// calc avg
			$avg = array();
			for($i=0; $i<$this->nrOfMeasuringPoints; $i++) {
				if($i<$precision-1) {
					// not enough measuring points to build average
					$avg[] = false;
				}
				else {
					$tmp = 0; $count = 0;
					for($j=$i-($precision-1); $j<=$i; $j++) {
						for($k=0; $k<$this->nrOfDataRows; $k++) {
							$tmp += $this->dataRow[$k][$j];
							$count++;
						}
					}
					$count>0 ? $avg[] = $tmp/$count : $avg[] = false;
				}
			}
			$this->movingAverages['total'] = $avg;
		 }
		 return true;
	}
	public function addHyperlink($url, $dataRow=false) {
		/**
		 *	Adds hyperlinks to graph data objects
		 *
		 *	@param $hyperlink: An array containing URLs or a single string containing an URL
		 *	@param $dataRow: The number of the data row, to which the hyperlink shall be added.
		 *					 If no parameter is given, the hyperlink will be set for all data rows.
		 *
		 *	@return: TRUE if adding the hyperlinks suceeded, FALSE if not
		 */
		 
		if($dataRow !== false) {
			$dataRow = (int) $dataRow;
			if($dataRow < 0 || $dataRow >= $this->nrOfDataRows) return false;
		}
		
		if(!is_array($url)) {
			// one hyperlink for all objects
			$url = (string) $url;
		}
		
		if($dataRow !== false) {
			// hyperlinks for a single data row
			$this->hyperlinks[$dataRow] = $url;
		}
		else {
			// hyperlinks for all data rows
			for($i=0; $i<$this->nrOfDataRows; $i++) {
				$this->hyperlinks[$i] = $url;
			}
		}
	}
	
	public function markHorizontalArea($className, $from, $to=false) {
		/**
		 *	Marks a horizontal area as special area.
		 *
		 *	@param $from: The start value of the area
		 *	@param $to (optional): The end value of the area
		 *
		 *	@return: TRUE if marking the area suceeded, FALSE if not
		 */
		
		$from = (float) $from;
		if($to!==false) $to = (float) $to;
		else $to = $from;
		
		if($from > $this->maxValue) return false;
		else if($from > $to) return false;
		else if($to > $this->maxValue) $to = $this->maxValue;
		
		$this->markedAreas['horizontal'][] = array('className' => $className, 'from' => $from, 'to' => $to);
		
		return true;
	}
	public function markVerticalArea($className, $from, $to=false) {
		/**
		 *	Marks a vertical area as special area.
		 *
		 *	@param $from: The start value of the area
		 *	@param $to (optional): The end value of the area
		 *
		 *	@return: TRUE if marking the area suceeded, FALSE if not
		 */
		 
		$from = (float) $from;
		if($to!==false) $to = (float) $to;
		else $to = $from;
		
		if($from > $this->maxValue) return false;
		else if($from > $to) return false;
		else if($to > $this->maxValue) $to = $this->maxValue;
		
		$this->markedAreas['vertical'][] = array('className' => $className, 'from' => $from, 'to' => $to);
		
		return true;
	}
	
	public function output() {
		/**
		 *	Outputs the graph.
		 *
		 *	@return TRUE if output suceeded, FALSE if not
		 */
		
		outputSVGHeader();
		outputXMLVersion();
		outputCopyright();
		$this->outputStylesheetLink();
		outputDTD();
		
		$this->outputSVGStart();
			$this->outputECMAScriptLink();
			$this->outputGraphBackground();
			$this->outputGraphTitle();
			$this->outputCanvasStart();
				$this->outputCanvasBackground();
				$this->outputMarkedAreas();
				$this->outputCanvasGraduationLines();
				$this->outputAverages();
				$this->outputGraphObjects();
				$this->outputMovingAverages();
			$this->outputCanvasEnd();
			$this->outputAxes();
			$this->outputAxisLabels();
			$this->outputAxisGraduations();
			$this->outputTimestamp();
			$this->outputLegend();
		$this->outputSVGEnd();
		
		return true;
	}
	
	private function calculateRectSpecs() {
		if($this->nrOfDataRows > 0) {
			$this->rectWidth = $this->measuringPointWidth/$this->nrOfDataRows*$this->rectWidthRatio;
			$this->rectMargin = $this->measuringPointWidth - $this->nrOfDataRows*$this->rectWidth;
		}
		else {
			$this->rectWidth = 0;
			$this->rectMargin = 0;
		}
	}
	private function calculateGraduation($min, $max) {
		if(!isset($this->maxValue) || !isset($this->minValue) || $max > $this->maxValue || $min < $this->minValue) {
			
			if(isset($this->maxValue) && $max < $this->maxValue)
				$max = $this->maxValue;
			if(isset($this->minValue) && $min > $this->minValue)
				$min = $this->minValue;
				
			$grad = getGraduation($min, $max, $this->nrOfSteps);
				$this->minValue = $grad['min'];
				$this->maxValue = $grad['max'];
				$this->stepValue = $grad['step'];
			
			if($this->minValue < 0) {
				$this->nullPoint = $this->maxValue / ($this->maxValue - $this->minValue) * 100;
			}
			
			/*
			echo $this->nullPoint . '%<br />';
			echo $this->minValue . ' ' . $this->maxValue . ' ' . $this->stepValue . '<br />';
			exit();
			*/
		}
	}
	
	/* private output functions */
	private function outputStylesheetLink() {
		echo '<?xml-stylesheet type="text/css" href="'.$this->stylesheetUrl.'" ?>' . "\n";
	}
	
	private function outputSVGStart() {
		echo
			'<svg ' .
				'xmlns="http://www.w3.org/2000/svg" '.
				'xmlns:xlink="http://www.w3.org/1999/xlink" '.
				'id="root" ' .
				'onload="fitsize()" '.
				'onresize="fitsize()" '.
			'>' . "\n\n"
		;
		echo "\t" . '<title>' . xmlEscape($this->graphTitle) . '</title>' . "\n";
		if($this->graphDescription)
			echo "\t" . '<desc>' . xmlEscape($this->graphDescription) . '</desc>' . "\n";
	}
		private function outputECMAScriptLink() {
			echo "\t" . '<script type="text/ecmascript" xlink:href="'.$this->ecmascriptUrl.'" />' . "\n\n";
		}
		private function outputGraphBackground() {
			echo "\t" . '<rect id="graphBackground" width="100%" height="100%" />' . "\n";
		}
		private function outputGraphTitle() {
			if($this->graphTitle)
				echo "\t" . '<text id="graphTitle" text-anchor="middle" x="50%" y="0">'.xmlEscape($this->graphTitle).'</text>' . "\n";
		}
		private function outputCanvasStart() {
			echo "\n" . '<svg id="canvas" x="0" y="0" width="0" height="0">' . "\n\n";
		}
			private function outputCanvasBackground() {
				echo '<rect id="canvasBackground" width="100%" height="100%" />' . "\n";	
			}
			private function outputMarkedAreas() {
				foreach($this->markedAreas['horizontal'] as $array) {
					$y = ($this->maxValue - $array['to']) / ($this->maxValue - $this->minValue) * 100;
					$height = ($array['to'] - $array['from']) / ($this->maxValue - $this->minValue) * 100;
					if($height < 0)
						$height *= -1;
					echo
						'<rect ' .
							'class="'.$array['className'].'" ' .
							'x="0" ' .
							'y="'.$y.'%" ' .
							'width="100%" ' .
							'height="'.$height.'%" ' .
						'/>' . "\n"
					;
				}
			}
			private function outputCanvasGraduationLines() {
				/* vertical lines */
				echo '<g id="graduationLinesVertical">' . "\n";
				for($i=0; $i<($this->nrOfMeasuringPoints-1); $i++) {
					echo
						"\t" .
						'<line ' .
							'x1="' . (($i+1)*$this->measuringPointWidth) . '%" ' .
							'y1="0" ' .
							'x2="' . (($i+1)*$this->measuringPointWidth) . '%" ' .
							'y2="100%" ' .
						'/>' . "\n"
					;
				}
				echo '</g>' . "\n";
				
				/* horizontal lines */
				// full
				echo '<g id="graduationLinesHorizontalFull">' . "\n";
				for($i=0; $i<=$this->nrOfSteps; $i+=2) {
					$y = $i / $this->nrOfSteps * 100;
					echo
						"\t" .
						'<line ' .
							'x1="0" ' .
							'y1="' . $y . '%" ' .
							'x2="100%" ' .
							'y2="' . $y . '%" ' .
						'/>' . "\n"
					;
				}
				if($this->nullPoint < 100) {
					echo "\t" . '<line x1="0" y1="99.99%" x2="100%" y2="99.99%" />' . "\n";
				}
				echo '</g>' . "\n";
				// half
				echo '<g id="graduationLinesHorizontalHalf">' . "\n";
				for($i=1; $i<=$this->nrOfSteps; $i+=2) {
					$y = $i / $this->nrOfSteps * 100;
					echo
						"\t" .
						'<line ' .
							'x1="0" ' .
							'y1="' . $y . '%" ' .
							'x2="100%" ' .
							'y2="' . $y . '%" ' .
						'/>' . "\n"
					;
				}
				echo '</g>' . "\n";
			}
			private function outputGraphObjects() {
				echo '<g id="lines">' . "\n";
				
				/* LOOP THROUGH ALL DATA ROWS */
				for($i=0; $i<$this->nrOfDataRows; $i++) {
				
					// new data row
					echo "\t" . '<g id="dataRow'.$i.'">' . "\n";
					
					/* LOOP THROUGH ALL MEASURING POINTS */
					for($j=0; $j<count($this->dataRow[$i]); $j++) {
					
						$value = $this->dataRow[$i][$j];
						
						// retrieve URL for this MP
						unset($url);
						if(is_string($this->hyperlinks[$i])) {
							$url = $this->hyperlinks[$i];
						}
						else if(is_array($this->hyperlinks[$i])) {
							if(isset($this->hyperlinks[$i][$j])) {
								if($this->hyperlinks[$i][$j])
									$url = $this->hyperlinks[$i][$j];
							}
						}
						if(isset($url)) {
							echo
								"\t\t" .
								'<a ' .
									'xlink:href="'.$url.'" ' .
								'>' . "\n";
						}
						
						// output line
						if(isset($this->dataRow[$i][$j]) && isset($this->dataRow[$i][$j+1])) {
							if($this->dataRow[$i][$j] !== false && $this->dataRow[$i][$j+1] !== false) {
								echo
									"\t\t\t" .
									'<line ' .
										'x1="'.($this->measuringPointWidth/2 + $this->measuringPointWidth*$j).'%" ' .
										'y1="'.(($this->maxValue - $this->dataRow[$i][$j]) / ($this->maxValue - $this->minValue) * 100).'%" ' .
										'x2="'.($this->measuringPointWidth/2 + $this->measuringPointWidth*($j+1)).'%" ' .
										'y2="'.(($this->maxValue - $this->dataRow[$i][$j+1]) / ($this->maxValue - $this->minValue) * 100).'%" ' .
									'/>' . "\n"
								;
							}
						}
						
						if(isset($url)) {
							echo "\t\t" . '</a>'. "\n";
						}
						
						// output text value
						if($this->showValues) {
							$textX = $this->measuringPointWidth/2 + $this->measuringPointWidth*$j;
							$textY = (($this->maxValue - $value) / ($this->maxValue - $this->minValue) * 100) - 2;
								if($textY < 3) $textY = 3;
								
							echo
								"\t\t\t" .
								'<text ' .
									'x="'.$textX.'%" ' .
									'y="'.$textY.'%" ' .
									'text-anchor="middle" ' .
								'>' .
									$value .
									//number_format($value, 4, ',', '.') .
								'</text>' . "\n"
							;
						}
					}
					echo "\t" . '</g>' . "\n";
				}
				echo '</g>' . "\n";
			}
			private function outputAverages() {
				if(isset($this->averages['total'])) {
					echo
						'<line ' .
							'id="totalAverageLine" ' .
							'x1="0" ' .
							'y1="'.(($this->maxValue - $this->averages['total']) / ($this->maxValue - $this->minValue) * 100).'%" ' .
							'x2="100%" ' .
							'y2="'.(($this->maxValue - $this->averages['total']) / ($this->maxValue - $this->minValue) * 100).'%" ' .
						'/>' . "\n"
					;
				}
				foreach($this->averages as $key => $value) {
					if($value !== false && $key !== 'total') {
						echo
							'<line ' .
								'class="averageLine'.$key.'" ' .
								'x1="0" ' .
								'y1="'.(($this->maxValue - $this->averages[$key]) / ($this->maxValue - $this->minValue) * 100).'%" ' .
								'x2="100%" ' .
								'y2="'.(($this->maxValue - $this->averages[$key]) / ($this->maxValue - $this->minValue) * 100).'%" ' .
							'/>' . "\n"
						;
					}
				}
			}
			private function outputMovingAverages() {
				if(isset($this->movingAverages['total'])) {
					/* output total moving average line */
					echo '<g id="totalMovingAverageLine">' . "\n";
					for($i=0; $i<$this->nrOfMeasuringPoints; $i++) {
						if(isset($this->movingAverages['total'][$i]) && isset($this->movingAverages['total'][$i+1])) {
							if($this->movingAverages['total'][$i] !== false && $this->movingAverages['total'][$i+1] !== false) {
								echo
									"\t" .
									'<line ' .
										'x1="'.($this->measuringPointWidth/2 + $this->measuringPointWidth*$i).'%" ' .
										'y1="'.(($this->maxValue - $this->movingAverages['total'][$i]) / ($this->maxValue - $this->minValue) * 100).'%" ' .
										'x2="'.($this->measuringPointWidth/2 + $this->measuringPointWidth*($i+1)).'%" ' .
										'y2="'.(($this->maxValue - $this->movingAverages['total'][$i+1]) / ($this->maxValue - $this->minValue) * 100).'%" ' .
									'/>' . "\n"
								;
							}
						}
					}
					echo '</g>' . "\n";
				}
				/* output moving averages for single data rows */
				foreach($this->movingAverages as $key => $value) {
					if(!empty($value) && $key !== 'total') {
						echo '<g id="movingAverageLine'.$key.'">' . "\n";
						for($i=0; $i<$this->nrOfMeasuringPoints; $i++) {
							if(isset($this->movingAverages[$key][$i]) && isset($this->movingAverages[$key][$i+1])) {
								if($this->movingAverages[$key][$i] !== false && $this->movingAverages[$key][$i+1] !== false) {
									echo
										"\t" .
										'<line ' .
											'x1="'.($this->measuringPointWidth/2 + $this->measuringPointWidth*$i).'%" ' .
											'y1="'.(($this->maxValue - $this->movingAverages[$key][$i]) / ($this->maxValue - $this->minValue) * 100).'%" ' .
											'x2="'.($this->measuringPointWidth/2 + $this->measuringPointWidth*($i+1)).'%" ' .
											'y2="'.(($this->maxValue - $this->movingAverages[$key][$i+1]) / ($this->maxValue - $this->minValue) * 100).'%" ' .
										'/>' . "\n"
									;
								}
							}
						}
						echo '</g>' . "\n";
					}
				}
			}
		private function outputCanvasEnd() {
			echo "\n" . '</svg>' . "\n\n";
		}
		private function outputAxes() {
			// X-Axis
			echo '<svg id="xAxisContainer">' . "\n";
			if($this->nullPoint < 100)
				echo '<line id="xAxis" x1="0" y1="'.$this->nullPoint.'%" x2="100%" y2="'.$this->nullPoint.'%" />' . "\n";
			else
				echo '<line id="xAxis" x1="0" y1="99.99%" x2="100%" y2="99.99%" />' . "\n";
			echo '</svg>' . "\n";
			// Y-Axis (left)
			echo '<line id="yAxis" x1="0" y1="0" x2="0" y2="0" />' . "\n";
			// Y-Axis (right)
			echo '<line id="yAxisRight" x1="0" y1="0" x2="0" y2="0" />' . "\n";
		}
		private function outputAxisGraduations() {
			// X-Axis
			echo '<svg id="xAxisGraduation" x="0" y="0" width="0" height="0">' . "\n";
			for($i=0; $i<$this->nrOfMeasuringPoints; $i++) {
				echo
					"\t" .
					'<text ' .
						'x="'.($this->measuringPointWidth*$i + $this->measuringPointWidth/2).'%" ' .
						'y="14" ' .
						'text-anchor="middle"' .
					'>' .
						$this->xAxisValues[$i] .
					'</text>' .
					"\n";
			}
			echo '</svg>' . "\n\n";
			// Y-Axis
			echo '<svg id="yAxisGraduation" x="0" y="0" width="0" height="0">' . "\n";
			for($i=0; $i<=$this->nrOfSteps; $i++) {
				$y = $i / $this->nrOfSteps * 100;
				$tmpValue = $this->maxValue - $i*$this->stepValue;
				echo "\t" . '<text x="100%" y="'.$y.'%" text-anchor="end">'.$tmpValue.'</text>' . "\n";
			}
			echo '</svg>' . "\n\n";
			echo '<svg id="yAxisRightGraduation" x="0" y="0" width="0" height="0">' . "\n";
			for($i=0; $i<=$this->nrOfSteps; $i++) {
				$y = $i / $this->nrOfSteps * 100;
				$tmpValue = $this->maxValue - $i*$this->stepValue;
				echo "\t" . '<text x="0" y="'.$y.'%">'.$tmpValue.'</text>' . "\n";
			}
			echo '</svg>' . "\n\n";
			
			echo '<text id="yAxisMaxLabel" text-anchor="end" x="0" y="0">'.$this->maxValue.'</text>' . "\n";
			echo '<text id="yAxisRightMaxLabel" text-anchor="start" x="0" y="0">'.$this->maxValue.'</text>' . "\n";
		}
		private function outputAxisLabels() {
			// X-Axis
			echo '<text id="xAxisLabel" text-anchor="middle" x="0" y="0">'.xmlEscape($this->xAxisLabel).'</text>' . "\n";
			// Y-Axis
			echo '<text id="yAxisLabel" text-anchor="middle" x="0" y="0">'.xmlEscape($this->yAxisLabel).'</text>' . "\n";
		}
		private function outputTimestamp() {
			if($this->outputTimestamp === true) {
				echo '<text id="timestamp" text-anchor="end">'.$this->timestampText.' '.date($this->timestampFormat).'</text>' . "\n";
			}
		}
		private function outputLegend() {
			echo
				"\n" . '<image ' .
					'id="legendInfoButton" ' .
					'xlink:href="'.$this->SVGraphRoot.'images/info.png" ' .
					'onmouseover="showLegend()" ' .
					'onmouseout="hideLegend()" ' .
					'width="32" ' .
					'height="32" ' .
					'style="cursor: pointer;" ' .
				'/>' . "\n"
			;
			echo
				'<svg id="legend">' . "\n" .
					// background
					"\t" . '<rect class="background" width="100%" height="100%" />' . "\n" .
					// heading
					"\t" . '<text ' .
						'id="legendHeading" ' .
						'class="heading" ' .
						'text-anchor="start" ' .
						'x="10" ' .
						'y="22" ' .
					'>' .
						$this->legendText .
					'</text>' . "\n"
			;
			// lines
			for($i=0; $i<$this->nrOfDataRows; $i++) {
				echo "\t" . '<text id="legendDataRow'.$i.'Text" class="bold">'.$this->dataRowNames[$i].'</text>' . "\n";
				echo "\t" . '<line id="legendDataRow'.$i.'" class="dataRow'.$i.'" />' . "\n";
			}
			// averages
			foreach($this->averages as $key => $value) {
				if($key === 'total') {
					echo "\t" . '<text id="legendTotalAverageLineText">'.$this->averagesNames['total'].' ('.number_format($this->averages['total'],1).')</text>' . "\n";
					echo "\t" . '<line id="legendTotalAverageLine" class="totalAverageLine" />' . "\n";
				}
				else if($value !== false) {
					echo "\t" . '<text id="legendAverageLine'.$key.'Text">'.$this->averagesNames[$key].' ('.number_format($this->averages[$key], 1).')</text>' . "\n";
					echo "\t" . '<line id="legendAverageLine'.$key.'" class="averageLine'.$key.'" />' . "\n";
				}
			}
			// moving averages
			foreach($this->movingAverages as $key => $value) {
				if($key === 'total') {
					echo "\t" . '<text id="legendTotalMovingAverageLineText">'.$this->movingAveragesNames['total'].'</text>' . "\n";
					echo "\t" . '<line id="legendTotalMovingAverageLine" class="totalMovingAverageLine" />' . "\n";
				}
				else if($value !== false) {
					echo "\t" . '<text id="legendMovingAverageLine'.$key.'Text">'.$this->movingAveragesNames[$key].'</text>' . "\n";
					echo "\t" . '<line id="legendMovingAverageLine'.$key.'" class="movingAverageLine'.$key.'" />' . "\n";
				}
			}
			echo
				'</svg>' . "\n"
			;
		}
	private function outputSVGEnd() {
		echo "\n" . '</svg>';
	}

	/* Getter and Setter */
	public function getStylesheetUrl() {
		return $this->stylesheetUrl;
	}
	public function setStylesheetUrl($url) {
		// delete query string
		$query = strpos($url, '?');
		if($query !== false) {
			$tmpUrl = substr($url, 0, strlen($url)-$query+1);
		}
		else
			$tmpUrl = $url;
		// check relative
		if(file_exists($tmpUrl)) {
			$this->stylesheetUrl = $url;
			return true;
		}
		else {
			// check SVGraph dir
			$tmpUrl = '/' . $tmpUrl;
			$file = $_SERVER['DOCUMENT_ROOT'] . $tmpUrl;
			// check URL
			if(file_exists($file)) {
				$this->stylesheetUrl = $url;
				return true;
			}
			else
				return false;
		}
	}
	public function getEcmascriptUrl() {
		return $this->ecmascriptUrl;
	}
	public function setEcmascriptUrl($url) {
		$url = '/' . $url;
		$file = $_SERVER['DOCUMENT_ROOT'] . $url;
		// check URL
		if(file_exists($file)) {
			$this->ecmascriptUrl = $url;
			return true;
		}
		else {
			return false;
		}
	}
	public function getGraphTitle() {
		return $this->graphTitle;
	}
	public function setGraphTitle($title) {
		$this->graphTitle = (string) $title;
		return true;
	}
	public function getGraphDescription() {
		return $this->graphDescription;
	}
	public function setGraphDescription($desc) {
		$this->graphDescription = (string) $desc;
		return true;
	}
	public function getXAxisLabel() {
		return $this->xAxisLabel;
	}
	public function setXAxisLabel($label) {
		$this->xAxisLabel = (string) $label;
		return true;
	}
	public function getYAxisLabel() {
		return $this->yAxisLabel;
	}
	public function setYAxisLabel($label) {
		$this->yAxisLabel = (string) $label;
		return true;
	}
	public function getOutputTimestamp() {
		return $this->outputTimestamp();
	}
	public function setOutputTimestamp($bool) {
		if(is_bool($bool)) {
			$this->outputTimestamp = $bool;
			return true;
		}
		else {
			return false;
		}
	}
	public function getTimestampText() {
		return $this->timestampText;
	}
	public function setTimestampText($text) {
		$this->timestampText = (string) $text;
		return true;
	}
	public function getTimestampFormat() {
		return $this->timestampFormat;
	}
	public function setTimestampFormat($format) {
		$this->timestampFormat = (string) $format;
		return true;
	}
	public function getLegendText() {
		return $this->legendText;
	}
	public function setLegendText($text) {
		$this->legendText = (string) $text;
	}
	/*
	public function getRectWidthPercentage() {
		return $this->rectWidthRatio * 100.0;
	}
	public function setRectWidthPercentage($ratio) {
		$ratio = ((double) $ratio) / 100.0;
		if($ratio <= 1.0 && $ratio > 0.0) {
			$this->rectWidthRatio = $ratio;
			$this->calculateRectSpecs();
			return true;
		}
		else {
			return false;
		}
	}
	*/
	public function getShowValues() {
		return $this->showValues;
	}
	public function setShowValues($bool) {
		if(is_bool($bool)) {
			$this->showValues = $bool;
			return true;
		}
		else {
			return false;
		}
	}
	public function getNrOfSteps() {
		return $this->nrOfSteps;
	}
	public function setNrOfSteps($steps) {
		$steps = (int) $steps;
		if($steps > 0 && $steps < 1000) {
			$this->nrOfSteps = $steps;
			return true;
		}
		else {
			return false;
		}
	}
	public function getMaximum() {
		return $this->maxValue;
	}
	public function setMaximum($max) {
		$this->calculateGraduation($this->minValue, $max);
		return true;
	}
	public function getMinimum() {
		return $this->minValue;
	}
	public function setMinimum($min) {
		$this->calculateGraduation($min, $this->maxValue);
		return true;
	}
}

?>