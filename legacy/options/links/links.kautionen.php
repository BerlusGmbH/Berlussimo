<h6>Kautionen</h6>
<div class="row">
    <?php
    echo "<div class='col s4 m3 l2'>";
    echo "<a href='" . route('legacy::kautionen::index', ['option' => 'kautionen_buchen']) . "'>Kautionen buchen</a>";
    echo "</div>";
    echo "<div class='col s4 m3 l2'>";
    echo "<a href='" . route('legacy::kautionen::index', ['option' => 'kontohochrechnung']) . "'>Kontoübersicht</a>";
    echo "</div>";
    echo "<div class='col s4 m3 l2'>";
    echo "<a href='" . route('legacy::kautionen::index', ['option' => 'mv_ohne_k']) . "'>Mieter ohne Kautionsbuchungen</a>";
    echo "</div>";
    echo "<div class='col s4 m3 l2'>";
    echo "<a href='" . route('legacy::kautionen::index', ['option' => 'kautionsuebersicht']) . "'><b>Kautionsübersicht</a>";
    echo "</div>";
    echo "<div class='col s4 m3 l2'>";
    echo "<a href='" . route('legacy::kautionen::index', ['option' => 'kautionsfelder']) . "'><b>Kautionsfelder</a>";
    echo "</div>";
    ?>
</div>
    