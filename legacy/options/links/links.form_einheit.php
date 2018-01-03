<h3>Einheiten</h3>
<div class="row">
    <?php
    echo "<div class='col-xs-4 col-md-3 col-lg-1'>";
    echo "<a href='" . route('web::einheiten::legacy', ['einheit_raus' => 'einheit_kurz']) . "'>Alle</a>";
    echo "</div>";
    echo "<div class='col-xs-4 col-md-3 col-lg-1'>";
    echo "<a href='" . route('web::einheiten::legacy', ['einheit_raus' => 'einheit_neu']) . "'>Neu</a>";
    echo "</div>";
    ?>
</div>