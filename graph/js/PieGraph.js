/*
 *  PieGraph.js
 *
 *	This file is the default ECMAscript (JavaScript) for Pie Graphs. It is used if no other script is specified.
 *	To specify own scripts, use the setEcmascriptUrl() method
 */

window.onresize = fitsize;

/* global variables */ {
	var
		marginTop = 60,
		marginRight = 20,
		marginBottom = 20,
		marginLeft = 20
	;
}

// array containing angle of each segment
var angles = new Array();

function init() {

	// get first segment
	i=0;
	segment = document.getElementById('segment' + i);
	
	// loop through all segments
	while(segment!=null) {
	
		// get segment angle (percentage information stored temporarily in 'd' as follows: 'M{percentage},0')
		tmpAngle = segment.getAttributeNS(null, 'd');
		tmpArray = tmpAngle.split(',');
		tmpAngle = tmpArray[0].substr(1);
		tmpAngle = tmpAngle / 100 * 360;
		angles[i] = tmpAngle;
		
		// get next segment
		i++;
		segment = document.getElementById('segment' + i);
	}
	
	fitsize();
}

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
		canvasWidth = width - marginLeft - marginRight;
		canvasHeight = height - marginTop - marginBottom;
		
		set(canvas, 'width', canvasWidth);
		set(canvas, 'height', canvasHeight);
		set(canvas, 'x', marginLeft);
		set(canvas, 'y', marginTop);
	}
	
	/* set legend */ {
		legendInfoButton = document.getElementById('legendInfoButton');
		legend = document.getElementById('legend'); // SVG element
		legendHeading = document.getElementById('legendHeading');
		
		set(legendInfoButton, 'x', 0);
		set(legendInfoButton, 'y', height-32);
		
		offset = 38;
		
		// segments
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
		
		legendHeight = offset;
		
		set(legend, 'x', marginLeft);
		set(legend, 'y', height - legendHeight - marginBottom);
		set(legend, 'width', width);
		set(legend, 'height', legendHeight);
	}
	
	/* set segments */ {
		usedAngle = 0;
		
		// calc radius, cx & cy
		cx = canvasWidth / 2;
		cy = canvasHeight / 2;	
		canvasWidth > canvasHeight
			? r = canvasHeight/2 - 3 // 3px for a circle-stroke
			: r = canvasWidth/2 - 3  // 3px for a circle-stroke
		;
		
		x1 = cx;	y1 = cy - r;
		x2 = 0;		y2 = 0;
		xText = 0;	yText = 0;
		
		// set circle
		circle = document.getElementById('circle');	
			set(circle, 'cx', cx);
			set(circle, 'cy', cy);
			set(circle, 'r', r);
		
		// get first segment
		i=0;
		segment = document.getElementById('segment' + i);
		segmentText = document.getElementById('segment' + i + 'Text');
		// loop through all segments
		while(segment!=null) {
		
			// get segment angle
			angle = angles[i]
				x2 = cx + r * Math.sin((angle + usedAngle) * Math.PI/180);
				y2 = cy - r * Math.cos((angle + usedAngle) * Math.PI/180);
				
				xText = cx + (r-30) * Math.sin((angle/2 + usedAngle) * Math.PI/180);
				yText = cy - (r-30) * Math.cos((angle/2 + usedAngle) * Math.PI/180);
			
			// set segment text
			if(segmentText) {
				set(segmentText, 'x', xText);
				set(segmentText, 'y', yText);
			}
			
			if(angle <= 180)
				angleConst = '0 0,1';
			else
				angleConst = '0 1,1';
			
			// set segment path
			d =
				'M' + cx + ',' + cy + ' ' +
				'L' + x1 + ',' + y1 + ' ' +
				'A' + r + ',' + r + ' ' +
				angleConst + ' ' +
				x2 + ',' + y2 + ' ' +
				'Z'
			;
			//alert(d);
			set(segment, 'd', d);
			
			// set starting point for next segment
			x1 = x2;
			y1 = y2;
			// add segment angle to usedAngle
			usedAngle += angle;
			
			// get next segment
			i++;
			segment = document.getElementById('segment' + i);
			segmentText = document.getElementById('segment' + i + 'Text');
		}
	}
}

function set(element, attribute, value) {
	if(element) {
		element.setAttributeNS(null, attribute, value);
	}
}

function toggleLegend() {
	legend = document.getElementById('legend');
	if(legend.getAttributeNS(null, 'visibility') == 'hidden')
		set(legend, 'visibility', 'visible');
	else
		set(legend, 'visibility', 'hidden');
}