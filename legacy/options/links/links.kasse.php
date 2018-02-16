<h6>Kassen</h6>
<div class="row">
    <?php
    echo "<div class='col-xs-4 col-md-3 col-lg-2'>";
    echo "<a href='" . route('web::kassen::legacy', ['option' => 'kassenbuch']) . "'>Kassenbuch</a>";
    echo "</div>";
    echo "<div class='col-xs-4 col-md-3 col-lg-2'>";
    echo "<a href='" . route('web::kassen::legacy', ['option' => 'rechnung_an_kasse_erfassen']) . "'>Ausgaben erfassen</a>";
    echo "</div>";
    echo "<div class='col-xs-4 col-md-3 col-lg-2'>";
    echo "<a href='" . route('web::kassen::legacy', ['option' => 'buchungsmaske_kasse']) . "'>E/A Buchen</a>";
    echo "</div>";
    ?>
</div>
