<h6>Häuser</h6>
<div class="row">
    <?php
    echo "<div class='col-xs-4 col-md-3 col-lg-1'>";
    echo "<a href='" . route('web::haeuser::legacy', ['haus_raus' => 'haus_kurz']) . "'>Alle</a>";
    echo "</div>";
    echo "<div class='col-xs-4 col-md-3 col-lg-1'>";
    echo "<a href='" . route('web::haeuserform::legacy', ['daten_rein' => 'haus_neu']) . "'>Neu</a>";
    echo "</div>";
    echo "<div class='col-xs-4 col-md-3 col-lg-1'>";
    echo "<a href='" . route('web::haeuser::legacy', ['haus_raus' => 'haus_aendern']) . "'>Ändern</a>";
    echo "</div>";
    ?>
</div>
