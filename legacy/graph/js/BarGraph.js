/*
 *  BarGraph.js
 *
 *	This file is the default ECMAscript (JavaScript) for Bar Graphs. It is used if no other script is specified.
 *	To specify own scripts, use the setEcmascriptUrl() method
 */

window.onresize = fitsize;
 
/* global variables */ {
	var
		marginTop = 60,
		marginRight = 40,
		marginBottom = 60,
		marginLeft = 60
	;
}

/*
 *	fitsize() is called onload and onresize to re-calculate positions
 */
function fitsize() {
	/* get window size */ {
		width = window.innerWidth;
		height = window.innerHeight;
	}
	
	/* set graphTitle */ {
		graphTitle = document.getElementById('graphTitle');
		
		set(graphTitle, 'y', marginTop - 22);
	}
	/* set timestamp */ {
		timestamp = document.getElementById('timestamp');
		
		set(timestamp, 'x', width-marginRight);
		set(timestamp, 'y', height-6);
	}
	/* set Canvas */ {
		canvas = document.getElementById('canvas');
		
		set(canvas, 'width', width - marginLeft - marginRight);
		set(canvas, 'height', height - marginTop - marginBottom);
		set(canvas, 'x', marginLeft);
		set(canvas, 'y', marginTop);
	}
	
	/* set X-Axis */ {
		xAxisContainer = document.getElementById('xAxisContainer');
		xAxisLabel = document.getElementById('xAxisLabel');
		xAxisMaxLabel = document.getElementById('xAxisMaxLabel');
		xAxisGraduation = document.getElementById('xAxisGraduation');
		
		set(xAxisContainer, 'width', width - marginLeft - marginRight);
		set(xAxisContainer, 'height', height - marginTop - marginBottom + 1);
		set(xAxisContainer, 'x', marginLeft);
		set(xAxisContainer, 'y', marginTop);

		set(xAxisLabel, 'x', marginLeft + (width-marginLeft-marginRight) / 2);
		set(xAxisLabel, 'y', height - 10);
		set(xAxisMaxLabel, 'x', width - marginRight);
		set(xAxisMaxLabel, 'y', height - marginBottom + 12);
		set(xAxisGraduation, 'width', width-marginLeft-marginRight);
		set(xAxisGraduation, 'height', marginBottom);
		set(xAxisGraduation, 'x', marginLeft);
		set(xAxisGraduation, 'y', height-marginBottom);
	}
	/* set Y-Axis */ {
		yAxis = document.getElementById('yAxis');
		yAxisRight = document.getElementById('yAxisRight');
		yAxisLabel = document.getElementById('yAxisLabel');
		yAxisMaxLabel = document.getElementById('yAxisMaxLabel');
		yAxisRightMaxLabel = document.getElementById('yAxisRightMaxLabel');
		yAxisGraduation = document.getElementById('yAxisGraduation');
		yAxisRightGraduation = document.getElementById('yAxisRightGraduation');
		
		set(yAxis, 'x1', marginLeft);
		set(yAxis, 'y1', marginTop);
		set(yAxis, 'x2', marginLeft);
		set(yAxis, 'y2', height-marginBottom);
		set(yAxisRight, 'x1', width-marginRight);
		set(yAxisRight, 'y1', marginTop);
		set(yAxisRight, 'x2', width-marginRight);
		set(yAxisRight, 'y2', height-marginBottom);
		// label/title
		set(yAxisLabel, 'x', 20);
		set(yAxisLabel, 'y', marginTop + (height-marginTop-marginBottom) / 2);
		set(yAxisLabel, 'transform', 'rotate(270,20,'+(marginTop+(height-marginTop-marginBottom)/2)+')')
		// max label
		set(yAxisMaxLabel, 'x', marginLeft - 5);
		set(yAxisMaxLabel, 'y', marginTop);
		set(yAxisRightMaxLabel, 'x', width - marginRight + 5);
		set(yAxisRightMaxLabel, 'y', marginTop);
		// graduation
		set(yAxisGraduation, 'width', marginLeft - 5);
		set(yAxisGraduation, 'height', height-marginTop-marginBottom);
		set(yAxisGraduation, 'y', marginTop);
		set(yAxisRightGraduation, 'width', marginRight - 5);
		set(yAxisRightGraduation, 'height', height-marginTop-marginBottom);
		set(yAxisRightGraduation, 'x', width - marginRight + 5);
		set(yAxisRightGraduation, 'y', marginTop);
	}
	/* set legend */ {
		legendInfoButton = document.getElementById('legendInfoButton');
		legend = document.getElementById('legend'); // SVG element
		legendHeading = document.getElementById('legendHeading');
		legendTotalAverageLine = document.getElementById('legendTotalAverageLine');
		legendTotalMovingAverageLine = document.getElementById('legendTotalMovingAverageLine');
		
		set(legendInfoButton, 'x', 0);
		set(legendInfoButton, 'y', height-32);
		
		offset = 38;
		
		// data rows
		for(i=0; i<=20; i++) {
			legendDataRow = document.getElementById('legendDataRow' + i);
			if(legendDataRow) {
				text = document.getElementById('legendDataRow' + i + 'Text');
				set(legendDataRow, 'x', 10);
				set(legendDataRow, 'y', offset);
				set(text, 'x', 50);
				set(text, 'y', offset+14);
				offset += 26;
			}
		}
		offset += 12;
		
		// averages
		if(legendTotalAverageLine) {
			text = document.getElementById('legendTotalAverageLineText');
			set(legendTotalAverageLine, 'x1', 10);
			set(legendTotalAverageLine, 'y1', offset);
			set(legendTotalAverageLine, 'x2', 40);
			set(legendTotalAverageLine, 'y2', offset);
			set(text, 'x', 50);
			set(text, 'y', offset+4);
			offset += 18;
		}
		for(i=0; i<=20; i++) {
			legendAverageLine = document.getElementById('legendAverageLine' + i);
			if(legendAverageLine) {
				text = document.getElementById('legendAverageLine' + i + 'Text');
				set(legendAverageLine, 'x1', 10);
				set(legendAverageLine, 'y1', offset);
				set(legendAverageLine, 'x2', 40);
				set(legendAverageLine, 'y2', offset);
				set(text, 'x', 50);
				set(text, 'y', offset+4);
				offset += 20;
			}
		}
		// moving averages
		if(legendTotalMovingAverageLine) {
			text = document.getElementById('legendTotalMovingAverageLineText');
			set(legendTotalMovingAverageLine, 'x1', 10);
			set(legendTotalMovingAverageLine, 'y1', offset);
			set(legendTotalMovingAverageLine, 'x2', 40);
			set(legendTotalMovingAverageLine, 'y2', offset);
			set(text, 'x', 50);
			set(text, 'y', offset+4);
			offset += 20;
		}
		for(i=0; i<=20; i++) {
			legendMovingAverageLine = document.getElementById('legendMovingAverageLine' + i);
			if(legendMovingAverageLine) {
				text = document.getElementById('legendMovingAverageLine' + i + 'Text');
				set(legendMovingAverageLine, 'x1', 10);
				set(legendMovingAverageLine, 'y1', offset);
				set(legendMovingAverageLine, 'x2', 40);
				set(legendMovingAverageLine, 'y2', offset);
				set(text, 'x', 50);
				set(text, 'y', offset+4);
				offset += 20;
			}
		}
		
		legendHeight = offset;
		
		set(legend, 'x', marginLeft+1);
		set(legend, 'y', height-legendHeight-marginBottom);
		set(legend, 'width', width-marginLeft-marginRight-2);
		set(legend, 'height', legendHeight);
	}
	
	/* hide legend */ {
		hideLegend();
	}
}

function set(element, attribute, value) {
	if(element) {
		element.setAttributeNS(null, attribute, value);
	}
}

function showLegend() {
	legend = document.getElementById('legend');
	set(legend, 'visibility', 'visible');
}
function hideLegend() {
	legend = document.getElementById('legend');
	set(legend, 'visibility', 'hidden');
}
