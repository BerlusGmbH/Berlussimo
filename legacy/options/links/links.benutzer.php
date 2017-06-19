<h6>Werkzeugverwaltung</h6>
<div class="row">
    <?php
    echo "<div class='col-xs-4 col-sm-4 col-md-3 col-lg-2'>";
    echo "<a href='" . route('web::benutzer::legacy', ['option' => 'werkzeuge']) . "'>Werkzeuge</a>";
    echo "</div>";
    echo "<div class='col-xs-4 col-sm-4 col-md-3 col-lg-2'>";
    echo "<a href='" . route('web::benutzer::legacy', ['option' => 'werkzeugliste_nach_mitarbeiter']) . "'>Werkzeuge nach Mitarbeiter</a>";
    echo "</div>";
    ?>
</div>
    