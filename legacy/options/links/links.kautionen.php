<h3>Kautionen</h3>
<div class="row">
    <?php
    echo "<div class='col-xs-4 col-md-3 col-lg-2'>";
    echo "<a href='" . route('web::kautionen::legacy', ['option' => 'kautionen_buchen']) . "'>Kautionen buchen</a>";
    echo "</div>";
    echo "<div class='col-xs-4 col-md-3 col-lg-2'>";
    echo "<a href='" . route('web::kautionen::legacy', ['option' => 'kontohochrechnung']) . "'>Kontoübersicht</a>";
    echo "</div>";
    echo "<div class='col-xs-4 col-md-3 col-lg-2'>";
    echo "<a href='" . route('web::kautionen::legacy', ['option' => 'mv_ohne_k']) . "'>Mieter ohne Kautionsbuchungen</a>";
    echo "</div>";
    echo "<div class='col-xs-4 col-md-3 col-lg-2'>";
    echo "<a href='" . route('web::kautionen::legacy', ['option' => 'kautionsuebersicht']) . "'>Kautionsübersicht</a>";
    echo "</div>";
    echo "<div class='col-xs-4 col-md-3 col-lg-2'>";
    echo "<a href='" . route('web::kautionen::legacy', ['option' => 'kautionsfelder']) . "'>Kautionsfelder</a>";
    echo "</div>";
    ?>
</div>
    