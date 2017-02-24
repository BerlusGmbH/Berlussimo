<h6>Geldkonten</h6>
<div class="row">
    <?php
    echo "<div class='col-xs-4 col-md-3 col-lg-2'>";
    echo "<a href='" . route('web::geldkonten::legacy') . "'>Kontostände</a>";
    echo "</div>";
    echo "<div class='col-xs-4 col-md-3 col-lg-2'>";
    echo "<a href='" . route('web::geldkonten::legacy', ['option' => 'uebersicht_ea']) . "'>Übersicht E/A</a>";
    echo "</div>";
    echo "<div class='col-xs-4 col-md-3 col-lg-2'>";
    echo "<a href='" . route('web::geldkonten::legacy', ['option' => 'gk_neu']) . "'>GK erstellen</a>";
    echo "</div>";
    echo "<div class='col-xs-4 col-md-3 col-lg-2'>";
    echo "<a href='" . route('web::geldkonten::legacy', ['option' => 'gk_zuweisen']) . "'>GK zuweisen</a>";
    echo "</div>";
    echo "<div class='col-xs-4 col-md-3 col-lg-2'>";
    echo "<a href='" . route('web::geldkonten::legacy', ['option' => 'uebersicht_zuweisung']) . "'>Übersicht Zuweisung</a>";
    echo "</div>";
    ?>
</div>
    