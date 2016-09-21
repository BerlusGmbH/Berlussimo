<?php

include_once('funktionen.php');

echo "GOOG";
if (request()->has('b_id')) {
    $b_id = request()->input('b_id');
} else {
    $b_id = 34;
}
if (!request()->has('datum')) {
    $datum = date("Y-m-d");
} else {
    $datum = request()->input('datum');
}


if ($b_id && $datum) {
    $datum_d = date_mysql2german($datum);
    $arr = get_termine_tag_arr($b_id, $datum_d);
    echo "<pre>";
    print_r($arr);
} else {
    die('NIXNXINXINXINXIX');
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no"/>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8"/>
    <title>Google Maps JavaScript API v3 Example: Directions Waypoints</title>
    <link href="http://code.google.com/apis/maps/documentation/javascript/examples/default.css" rel="stylesheet"
          type="text/css"/>
    <script type="text/javascript" src="//maps.googleapis.com/maps/api/js?sensor=false"></script>
    <script type="text/javascript">
        var directionDisplay;
        var directionsService = new google.maps.DirectionsService();
        var map;

        function initialize() {
            directionsDisplay = new google.maps.DirectionsRenderer();
            var chicago = new google.maps.LatLng(41.850033, -87.6500523);
            var myOptions = {
                zoom: 6,
                mapTypeId: google.maps.MapTypeId.ROADMAP,
                center: chicago
            };
            map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);
            directionsDisplay.setMap(map);
        }

        function calcRoute() {
            var start = document.getElementById("start").value;
            var end = document.getElementById("end").value;
            var waypts = [];
            var checkboxArray = document.getElementById("waypoints");
            for (var i = 0; i < checkboxArray.length; i++) {
                if (checkboxArray.options[i].selected == true) {
                    waypts.push({
                        location: checkboxArray[i].value,
                        stopover: true
                    });
                }
            }

            var request = {
                origin: start,
                destination: end,
                waypoints: waypts,
                optimizeWaypoints: true,
                travelMode: google.maps.DirectionsTravelMode.DRIVING
            };
            directionsService.route(request, function (response, status) {
                if (status == google.maps.DirectionsStatus.OK) {
                    directionsDisplay.setDirections(response);
                    var route = response.routes[0];
                    var summaryPanel = document.getElementById("directions_panel");
                    summaryPanel.innerHTML = "";
                    // For each route, display summary information.
                    for (var i = 0; i < route.legs.length; i++) {
                        var routeSegment = i + 1;
                        summaryPanel.innerHTML += "<b>Route Segment: " + routeSegment + "</b><br />";
                        summaryPanel.innerHTML += route.legs[i].start_address + " to ";
                        summaryPanel.innerHTML += route.legs[i].end_address + "<br />";
                        summaryPanel.innerHTML += route.legs[i].distance.text + "<br /><br />";
                    }
                }
            });
        }
    </script>
</head>
<body onload="initialize()">
<div id="map_canvas" style="float:left;width:70%;height:100%;"></div>
<div id="control_panel" style="float:right;width:30%;text-align:left;padding-top:20px">
    <div style="margin:20px;border-width:2px;">

        <b>Start:</b>
        <select id="start">
            <option value="Halifax, NS">Halifax, NS</option>
            <option value="Boston, MA">Boston, MA</option>
            <option value="New York, NY">New York, NY</option>
            <option value="Miami, FL">Miami, FL</option>
        </select>
        <br/>

        <b>Waypoints:</b> <br/>
        <i>(Ctrl-Click for multiple selection)</i> <br/>
        <select multiple id="waypoints">
            <option value="montreal, quebec">Montreal, QBC</input>
            <option value="toronto, ont">Toronto, ONT</input>
            <option value="chicago, il">Chicago</input>
            <option value="winnipeg, mb">Winnipeg</input>

            <option value="fargo, nd">Fargo</input>
            <option value="calgary, ab">Calgary</input>
            <option value="spokane, wa">Spokane</input>
        </select>
        <br/>
        <b>End:</b>
        <select id="end">
            <option value="Vancouver, BC">Vancouver, BC</option>

            <option value="Seattle, WA">Seattle, WA</option>
            <option value="San Francisco, CA">San Francisco, CA</option>
            <option value="Los Angeles, CA">Los Angeles, CA</option>
        </select>
        <br/>
        <input type="submit" onclick="calcRoute();"/>
    </div>
    <div id="directions_panel" style="margin:20px;background-color:#FFEE77;"></div>
</div>
</body>

</html>

