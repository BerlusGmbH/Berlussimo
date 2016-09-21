<h6>Artikel- und Leistungskatalog</h6>
<div class="row">
    <?php
    echo "<div class='col s4 m3 l2'>";
    echo "<a href='" . route('legacy::katalog::index', ['option' => 'katalog_anzeigen']) . "'>Artikel & Leistungen</a>";
    echo "</div>";
    echo "<div class='col s4 m3 l2'>";
    echo "<a href='" . route('legacy::katalog::index', ['option' => 'preisentwicklung']) . "'>Preisentwicklung</a>";
    echo "</div>";
    echo "<div class='col s4 m3 l2'>";
    echo "<a href='" . route('legacy::katalog::index', ['option' => 'artikelsuche']) . "'>Artikelsuche</a>";
    echo "</div>";
    echo "<div class='col s4 m3 l2'>";
    echo "<a href='" . route('legacy::katalog::index', ['option' => 'artikelsuche_freitext']) . "'>Artikelsuche Freitext</a>";
    echo "</div>";
    echo "<div class='col s4 m3 l2'>";
    echo "<a href='" . route('legacy::katalog::index', ['option' => 'meist_gekauft']) . "'>Meistgekauft</a>";
    echo "</div>";
    echo "<div class='col s4 m3 l2'>";
    echo "<a href='" . route('legacy::katalog::index', ['option' => 'zuletzt_gekauft']) . "'>Zuletzt gekauft</a>";
    echo "</div>";
    ?>
</div>
    