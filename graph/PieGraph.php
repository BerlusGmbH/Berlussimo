<?php

class PieGraph {

	private $SVGraphRoot, $stylesheetUrl, $ecmascriptUrl;
	private $graphTitle, $graphDescription;
	private $dataRow, $dataRowNames;
	private $hyperlinks;
	private $nrOfDataRows, $usedPercentage;
	private $outputTimestamp, $timestampText, $timestampFormat, $legendText;
	private $showValues;
	
	public function PieGraph() {
		$this->SVGraphRoot = getSVGraphRoot();
		$this->stylesheetUrl = $this->SVGraphRoot . 'css/PieGraph.css';
		$this->ecmascriptUrl = $this->SVGraphRoot . 'js/PieGraph.js';
		
		$this->graphTitle = false;
		$this->graphDescription = false;
		
		$this->dataRow = array();
		$this->dataRowNames = array();
		
		$this->nrOfDataRows = 0;
		$this->usedPercentage = 0;
		
		$this->outputTimestamp = true;
		$this->timestampText = 'generated on';
		$this->timestampFormat = 'Y-m-d, H:i:s';
		$this->legendText = 'Key';
		
		$this->showValues = true;
	}
	
	public function addSegment($value, $name=false) {
		/**
		 *	Adds a segment to the existing graph
		 *
		 *	@param $value: A percentage value
		 *	@param $name (optional): The name of the segment
		 *
		 *	@return: TRUE if adding the segment suceeded, FALSE if not
		 */
		
		$value = (float) $value;
			if($value <= 0 || $value > 100-$this->usedPercentage) return false;
			
		if($name!==false) $name = (string) $name;
		else $name = 'Data row ' . ($this->nrOfDataRows+1);
		
		$this->dataRow[] = $value;
		$this->dataRowNames[] = $name;
		$this->nrOfDataRows++;
		
		$this->hyperlinks[] = false;
		
		$this->usedPercentage += $value;
		
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
				$this->outputGraphObjects();
			$this->outputCanvasEnd();
			$this->outputTimestamp();
			$this->outputLegend();
		$this->outputSVGEnd();
		
		return true;
	}
	
	/* private output functions */
	private function outputStylesheetLink() {
		echo '<?xml-stylesheet type="text/css" href="'.$this->stylesheetUrl.'" ?>' . "\n";
	}
	
	private function outputSVGStart() {
		echo
			'<svg ' .
				'id="root" ' .
				'onload="init()" '.
				'onresize="fitsize()" '.
				'xmlns="http://www.w3.org/2000/svg" '.
				'xmlns:xlink="http://www.w3.org/1999/xlink"'.
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
			private function outputGraphObjects() {
			
				echo '<g id="segments">' . "\n\n";
				
				$i=0;
				foreach($this->dataRow as $value) {
					// add hyperlink
					if(isset($this->hyperlinks[$i])) {
						echo "\t" . '<a xlink:href="'.$this->hyperlinks[$i].'">' . "\n";
					}
					// output segment
					echo
						"\t\t" .
						'<path ' .
							'id="segment'.$i.'" ' .
							'd="M'.$value.',0" ' .
						'/>' . "\n"
					;
					if(isset($this->hyperlinks[$i])) {
						echo "\t" . '</a>' . "\n";
					}
					
					// output values
					if($this->showValues) {
						echo
							"\t" . '<a xlink:href="'.$this->hyperlinks[$i].'">' . "\n" .
							"\t\t" . '<text id="segment'.$i.'Text" text-anchor="middle">'.$value.'%</text>' . "\n" .
							"\t" . '</a>' . "\n"
						;
					}
					
					echo "\n";
					$i++;
				}
				
				echo "\n" . '<circle id="circle" r="0" />' . "\n";
				
				echo '</g>' . "\n";
			}
		private function outputCanvasEnd() {
			echo "\n" . '</svg>' . "\n\n";
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
					'xlink:href="'.$this->SVGraphRoot.'img/info.png" ' .
					'onclick="toggleLegend()" ' .
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
			for($i=0; $i<$this->nrOfDataRows; $i++) {
				// add hyperlink
				if(isset($this->hyperlinks[$i])) {
					echo "\t" . '<a xlink:href="'.$this->hyperlinks[$i].'">' . "\n";
				}
				
				echo "\t" . '<text id="legendDataRow'.$i.'Text" class="bold">'.$this->dataRowNames[$i].' ('.$this->dataRow[$i].'%)</text>' . "\n";
				echo "\t" . '<rect id="legendDataRow'.$i.'" class="segment'.$i.'" width="30" height="20" />' . "\n";
				
				if(isset($this->hyperlinks[$i])) {
					echo "\t" . '</a>' . "\n";
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
}

?>