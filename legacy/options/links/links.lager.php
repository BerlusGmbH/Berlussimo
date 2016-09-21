<h6>Lager</h6>
<div class="row">
    <?php
    echo "<div class='col s4 m3 l2'>";
    echo "<a href='" . route('legacy::lager::index', ['option' => 'lagerbestand']) . "'>Lagerbestand</a>";
    echo "</div>";
    echo "<div class='col s4 m3 l2'>";
    echo "<a href='" . route('legacy::lager::index', ['option' => 'lagerbestand_bis_form']) . "'>Lagerbestand bis...</a>";
    echo "</div>";
    echo "<div class='col s4 m3 l2'>";
    echo "<a href='" . route('legacy::lager::index', ['option' => 're']) . "'>RE</a>";
    echo "</div>";
    echo "<div class='col s4 m3 l2'>";
    echo "<a href='" . route('legacy::lager::index', ['option' => 'ra']) . "'>RA</a>";
    echo "</div>";
    echo "<div class='col s4 m3 l2'>";
    echo "<a href='" . route('legacy::lager::index', ['option' => 'artikelsuche']) . "'>Artikelsuche</a>";
    echo "</div>";
    ?>
</div>
    