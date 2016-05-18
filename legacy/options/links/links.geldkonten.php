<h6>Geldkonten</h6>
<div class="row">
    <?php
    echo "<div class='col s4 m3 l2'>";
    echo "<a href='" . route('legacy::geldkonten::index') . "'>Kontostände</a>";
    echo "</div>";
    echo "<div class='col s4 m3 l2'>";
    echo "<a href='" . route('legacy::geldkonten::index', ['option' => 'uebersicht_ea']) . "'>Übersicht E/A</a>";
    echo "</div>";
    echo "<div class='col s4 m3 l2'>";
    echo "<a href='" . route('legacy::geldkonten::index', ['option' => 'gk_neu']) . "'>GK erstellen</a>";
    echo "</div>";
    echo "<div class='col s4 m3 l2'>";
    echo "<a href='" . route('legacy::geldkonten::index', ['option' => 'gk_zuweisen']) . "'>GK zuweisen</a>";
    echo "</div>";
    echo "<div class='col s4 m3 l2'>";
    echo "<a href='" . route('legacy::geldkonten::index', ['option' => 'uebersicht_zuweisung']) . "'>Übersicht Zuweisung</a>";
    echo "</div>";
    ?>
</div>
    