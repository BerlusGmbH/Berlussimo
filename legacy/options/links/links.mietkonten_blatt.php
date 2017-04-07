<h6>Miete</h6>
<div class="row">
    <?php
    echo "<div class='col-xs-6 col-md-4 col-lg-2'>";
    echo "<a href='" . route('web::miete_definieren::legacy') . "'>Miethöhe definieren</a>";
    echo "</div>";
    echo "<div class='col-xs-6 col-md-4 col-lg-2'>";
    echo "<a href='" . route('web::miete_definieren::legacy', ['option' => 'mieterlisten_kostenkat', 'kostenkat' => 'MOD']) . "'>Mieterliste MOD</a>";
    echo "</div>";
    echo "<div class='col-xs-6 col-md-4 col-lg-2'>";
    echo "<a href='" . route('web::miete_definieren::legacy', ['option' => 'mieterlisten_kostenkat', 'kostenkat' => 'Untermieter Zuschlag']) . "'>Mieterliste Untermieterz.</a>";
    echo "</div>";
    echo "<div class='col-xs-6 col-md-4 col-lg-2'>";
    echo "<a href='" . route('web::mietanpassungen::legacy', ['option' => 'uebersicht']) . "'>Mietanpassungstabelle</a>";
    echo "</div>";
    echo "<div class='col-xs-6 col-md-4 col-lg-3'>";
    echo "<a href='" . route('web::mietanpassungen::legacy', ['option' => 'ak4']) . "'>Ausstattungsklasse 4-TEST</a>";
    echo "</div>";
    ?>
</div>