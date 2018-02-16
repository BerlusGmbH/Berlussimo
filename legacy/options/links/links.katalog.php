<h6>Artikel- und Leistungskatalog</h6>
<div class="row">
    <?php
    echo "<div class='col-xs-4 col-md-3 col-lg-2'>";
    echo "<a href='" . route('web::katalog::legacy', ['option' => 'katalog_anzeigen']) . "'>Artikel & Leistungen</a>";
    echo "</div>";
    echo "<div class='col-xs-4 col-md-3 col-lg-2'>";
    echo "<a href='" . route('web::katalog::legacy', ['option' => 'preisentwicklung']) . "'>Preisentwicklung</a>";
    echo "</div>";
    echo "<div class='col-xs-4 col-md-3 col-lg-2'>";
    echo "<a href='" . route('web::katalog::legacy', ['option' => 'artikelsuche']) . "'>Artikelsuche</a>";
    echo "</div>";
    echo "<div class='col-xs-4 col-md-3 col-lg-2'>";
    echo "<a href='" . route('web::katalog::legacy', ['option' => 'artikelsuche_freitext']) . "'>Artikelsuche Freitext</a>";
    echo "</div>";
    echo "<div class='col-xs-4 col-md-3 col-lg-2'>";
    echo "<a href='" . route('web::katalog::legacy', ['option' => 'meist_gekauft']) . "'>Meistgekauft</a>";
    echo "</div>";
    echo "<div class='col-xs-4 col-md-3 col-lg-2'>";
    echo "<a href='" . route('web::katalog::legacy', ['option' => 'zuletzt_gekauft']) . "'>Zuletzt gekauft</a>";
    echo "</div>";
    ?>
</div>
    