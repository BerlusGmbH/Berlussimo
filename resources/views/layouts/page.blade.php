<!DOCTYPE html>
<html>
<head>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href='{{mix('css/vendor.css')}}' rel='stylesheet' type='text/css'>
    <link href='{{mix('css/berlussimo.css')}}' rel='stylesheet' type='text/css'>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
</head>

<body class="grey lighten-3">

@yield('page-content')

<script type='text/javascript' src={{mix('js/vendor.js')}}></script>
<script type='text/javascript' src={{mix('js/legacy.js')}}></script>
<script type='text/javascript' src={{mix('js/berlussimo.js')}}></script>
@stack('scripts')

</body>
</html>