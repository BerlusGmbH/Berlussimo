<h3>Lager</h3>
<div class="row">
    <?php
    echo "<div class='col-xs-4 col-md-3 col-lg-2'>";
    echo "<a href='" . route('web::lager::legacy', ['option' => 'lagerbestand']) . "'>Lagerbestand</a>";
    echo "</div>";
    echo "<div class='col-xs-4 col-md-3 col-lg-2'>";
    echo "<a href='" . route('web::lager::legacy', ['option' => 'lagerbestand_bis_form']) . "'>Lagerbestand bis...</a>";
    echo "</div>";
    echo "<div class='col-xs-4 col-md-3 col-lg-2'>";
    echo "<a href='" . route('web::lager::legacy', ['option' => 're']) . "'>RE</a>";
    echo "</div>";
    echo "<div class='col-xs-4 col-md-3 col-lg-2'>";
    echo "<a href='" . route('web::lager::legacy', ['option' => 'ra']) . "'>RA</a>";
    echo "</div>";
    echo "<div class='col-xs-4 col-md-3 col-lg-2'>";
    echo "<a href='" . route('web::lager::legacy', ['option' => 'artikelsuche']) . "'>Artikelsuche</a>";
    echo "</div>";
    ?>
</div>
    