<h6>Kontenrahmen</h6>
<div class="row">
    <?php
    echo "<div class='col s6 m4 l1'>";
    echo "<a href='" . route('legacy::kontenrahmen::index', ['option' => 'kontenrahmen_uebersicht']) . "'>Alle</a>";
    echo "</div>";
    echo "<div class='col s6 m4 l1'>";
    echo "<a href='" . route('legacy::kontenrahmen::index', ['option' => 'kontenrahmen_neu']) . "'>Neu</a>";
    echo "</div>";
    echo "<div class='col s6 m4 l2'>";
    echo "<a href='" . route('legacy::kontenrahmen::index', ['option' => 'kostenkonto_neu']) . "'>Buchungskonto&nbsp;erstellen</a>";
    echo "</div>";
    echo "<div class='col s6 m4 l2'>";
    echo "<a href='" . route('legacy::kontenrahmen::index', ['option' => 'gruppen']) . "'>Gruppen anzeigen</a>";
    echo "</div>";
    echo "<div class='col s6 m4 l2'>";
    echo "<a href='" . route('legacy::kontenrahmen::index', ['option' => 'gruppe_neu']) . "'>Gruppe erstellen</a>";
    echo "</div>";
    echo "<div class='col s6 m4 l2'>";
    echo "<a href='" . route('legacy::kontenrahmen::index', ['option' => 'kontoarten']) . "'>Kontoarten anzeigen</a>";
    echo "</div>";
    echo "<div class='col s6 m4 l2'>";
    echo "<a href='" . route('legacy::kontenrahmen::index', ['option' => 'kontoart_neu']) . "'>Kontoart erstellen</a>";
    echo "</div>";
    echo "<div class='col s6 m4 l2'>";
    echo "<a href='" . route('legacy::kontenrahmen::index', ['option' => 'kontenrahmen_zuweisen']) . "'>Kontenrahmen&nbsp;zuweisen</a>";
    echo "</div>";
    ?>
</div>