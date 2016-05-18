<h6>Zeiterfassung</h6>
<div class="row">
    <?php
    echo "<div class='col s4 m3 l2'>";
    echo "<a href='" . route('legacy::zeiterfassung::index', ['option' => 'eigene_zettel']) . "'>Eigene Zettel</a>";
    echo "</div>";
    echo "<div class='col s4 m3 l2'>";
    echo "<a href='" . route('legacy::zeiterfassung::index', ['option' => 'neuer_zettel']) . "'>Neuer Zettel</a>";
    echo "</div>";
    ?>
</div>
