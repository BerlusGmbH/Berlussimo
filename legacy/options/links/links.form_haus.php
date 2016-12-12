<h6>Häuser</h6>
<div class="row">
    <?php
    echo "<div class='col s4 m3 l1'>";
    echo "<a href='" . route('web::haeuser::legacy', ['haus_raus' => 'haus_kurz']) . "'>Alle</a>";
    echo "</div>";
    echo "<div class='col s4 m3 l1'>";
    echo "<a href='" . route('web::haeuserform::legacy', ['daten_rein' => 'haus_neu']) . "'>Neu</a>";
    echo "</div>";
    echo "<div class='col s4 m3 l1'>";
    echo "<a href='" . route('web::haeuser::legacy', ['haus_raus' => 'haus_aendern']) . "'>Ändern</a>";
    echo "</div>";
    ?>
</div>
