<h6>Häuser</h6>
<div class="row">
    <?php
    echo "<div class='col s4 m3 l1'>";
    echo "<a href='" . route('legacy::haeuser::index', ['haus_raus' => 'haus_kurz']) . "'>Alle</a>";
    echo "</div>";
    echo "<div class='col s4 m3 l1'>";
    echo "<a href='" . route('legacy::haeuserform::index', ['daten_rein' => 'haus_neu']) . "'>Neu</a>";
    echo "</div>";
    echo "<div class='col s4 m3 l1'>";
    echo "<a href='" . route('legacy::haeuser::index', ['haus_raus' => 'haus_aendern']) . "'>Ändern</a>";
    echo "</div>";
    ?>
</div>
