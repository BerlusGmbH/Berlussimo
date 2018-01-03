<h3>Zeiterfassung</h3>
<div class="row">
    <?php
    echo "<div class='col-xs-4 col-md-3 col-lg-2'>";
    echo "<a href='" . route('web::zeiterfassung::legacy', ['option' => 'eigene_zettel']) . "'>Eigene Zettel</a>";
    echo "</div>";
    echo "<div class='col-xs-4 col-md-3 col-lg-2'>";
    echo "<a href='" . route('web::zeiterfassung::legacy', ['option' => 'neuer_zettel']) . "'>Neuer Zettel</a>";
    echo "</div>";
    ?>
</div>
