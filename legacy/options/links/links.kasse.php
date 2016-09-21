<h6>Kassen</h6>
<div class="row">
    <?php
    echo "<div class='col s4 m3 l2'>";
    echo "<a href='" . route('legacy::kassen::index', ['option' => 'kassenbuch']) . "'>Kassenbuch</a>";
    echo "</div>";
    echo "<div class='col s4 m3 l2'>";
    echo "<a href='" . route('legacy::kassen::index', ['option' => 'rechnung_an_kasse_erfassen']) . "'>Ausgaben erfassen</a>";
    echo "</div>";
    echo "<div class='col s4 m3 l2'>";
    echo "<a href='" . route('legacy::kassen::index', ['option' => 'buchungsmaske_kasse']) . "'>E/A Buchen</a>";
    echo "</div>";
    ?>
</div>
