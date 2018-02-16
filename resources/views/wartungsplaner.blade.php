<html>
<head>
    <title>Wartungskalender Berlussimo</title>
    <script type="text/javascript" src="{{mix('js/wartungsplaner.js')}}"></script>
    <link rel="stylesheet" type="text/css" href="{{mix('css/wartungsplaner.css')}}"/>
</head>

<body onload="start();">

<div id="navibox">
    <input type="button" value="Start" onclick="start();"/>
    <input type="button" value="Wochenkalender" onclick="wochenkalender();"/>
    <input type="button" value="Terminvorschläge" onclick="start_vorschlag();"/>
    <input type="button" value="Anstehende Wartungen" onclick="start_vorschlag_chrono();"/>
    <input type="button" value="Gerätelisten" onclick="geraete_listen();"/>
    <input type="button" value="Mitarbeiter" onclick="mitarbeiter();"/>
    <input type="button" value="Kundschaft" onclick="kundschaft();"/>
</div>


<div id="container">
    <div id="leftBox">
    </div>

    <div id="rightBox">
        Content in right item
    </div>
</div>


<div id="container1">
    <div id="leftBox1">
        Content in left item
    </div>

    <div id="rightBox1">
        Content in right item
    </div>
</div>
</body>
</html>