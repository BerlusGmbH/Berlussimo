<h3>Kontenrahmen</h3>
<div class="row">
    <?php
    echo "<div class='col-xs-6 col-md-4 col-lg-1'>";
    echo "<a href='" . route('web::kontenrahmen::legacy', ['option' => 'kontenrahmen_uebersicht']) . "'>Alle</a>";
    echo "</div>";
    echo "<div class='col-xs-6 col-md-4 col-lg-1'>";
    echo "<a href='" . route('web::kontenrahmen::legacy', ['option' => 'kontenrahmen_neu']) . "'>Neu</a>";
    echo "</div>";
    echo "<div class='col-xs-6 col-md-4 col-lg-2'>";
    echo "<a href='" . route('web::kontenrahmen::legacy', ['option' => 'kostenkonto_neu']) . "'>Buchungskonto&nbsp;erstellen</a>";
    echo "</div>";
    echo "<div class='col-xs-6 col-md-4 col-lg-2'>";
    echo "<a href='" . route('web::kontenrahmen::legacy', ['option' => 'gruppen']) . "'>Gruppen anzeigen</a>";
    echo "</div>";
    echo "<div class='col-xs-6 col-md-4 col-lg-2'>";
    echo "<a href='" . route('web::kontenrahmen::legacy', ['option' => 'gruppe_neu']) . "'>Gruppe erstellen</a>";
    echo "</div>";
    echo "<div class='col-xs-6 col-md-4 col-lg-2'>";
    echo "<a href='" . route('web::kontenrahmen::legacy', ['option' => 'kontoarten']) . "'>Kontoarten anzeigen</a>";
    echo "</div>";
    echo "<div class='col-xs-6 col-md-4 col-lg-2'>";
    echo "<a href='" . route('web::kontenrahmen::legacy', ['option' => 'kontoart_neu']) . "'>Kontoart erstellen</a>";
    echo "</div>";
    echo "<div class='col-xs-6 col-md-4 col-lg-2'>";
    echo "<a href='" . route('web::kontenrahmen::legacy', ['option' => 'kontenrahmen_zuweisen']) . "'>Kontenrahmen&nbsp;zuweisen</a>";
    echo "</div>";
    ?>
</div>