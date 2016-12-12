<h6>Zeiterfassung</h6>
<div class="row">
    <?php
    echo "<div class='col s4 m3 l2'>";
    echo "<a href='" . route('web::zeiterfassung::legacy', ['option' => 'eigene_zettel']) . "'>Eigene Zettel</a>";
    echo "</div>";
    echo "<div class='col s4 m3 l2'>";
    echo "<a href='" . route('web::zeiterfassung::legacy', ['option' => 'neuer_zettel']) . "'>Neuer Zettel</a>";
    echo "</div>";
    ?>
</div>
