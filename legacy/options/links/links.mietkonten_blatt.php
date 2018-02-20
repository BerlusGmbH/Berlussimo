<div class="row">
    <div class='col-xs-12'>
        <h3>Miete</h3>
        <div class="row">
            <?php
            echo "<div class='col-xs-6 col-md-4 col-lg-2'>";
            echo "<a href='" . route('web::miete_definieren::legacy') . "'>Miethöhe definieren</a>";
            echo "</div>";
            echo "<div class='col-xs-6 col-md-4 col-lg-2'>";
            echo "<a href='" . route('web::mietanpassungen::legacy', ['option' => 'uebersicht']) . "'>Mietanpassungstabelle</a>";
            echo "</div>";
            echo "<div class='col-xs-6 col-md-4 col-lg-3'>";
            echo "<a href='" . route('web::mietanpassungen::legacy', ['option' => 'ak4']) . "'>Ausstattungsklasse 4 (Test)</a>";
            echo "</div>";
            ?>
        </div>
        <h3>Listen</h3>
        <div class="row">
            <?php
            echo "<div class='col-xs-6 col-md-4 col-lg-2'>";
            echo "<a href='" . route('web::miete_definieren::legacy', ['option' => 'mieterlisten_kostenkat', 'kostenkat' => 'MOD']) . "'>Mieterliste MOD</a>";
            echo "</div>";
            echo "<div class='col-xs-6 col-md-4 col-lg-2'>";
            echo "<a href='" . route('web::miete_definieren::legacy', ['option' => 'mieterlisten_kostenkat', 'kostenkat' => 'Untermieter Zuschlag']) . "'>Mieterliste Untermieterz.</a>";
            echo "</div>";
            ?>
        </div>
        <h3>Mietspiegel</h3>
        <div class="row">
            <?php
            echo "<div class='col-xs-4 col-md-3 col-lg-2'>";
            echo "<a href='" . route('web::mietspiegel::legacy', ['option' => 'mietspiegelliste']) . "'>Mietspiegelliste</a>";
            echo "</div>";
            echo "<div class='col-xs-4 col-md-3 col-lg-2'>";
            echo "<a href='" . route('web::mietspiegel::legacy', ['option' => 'neuer_mietspiegel']) . "'>Neuer Mietspiegel</a>";
            echo "</div>";
            ?>
        </div>
    </div>
</div>