<h6>Urlaub</h6>
<div class="row">
    <?php
    echo "<div class='col s4 m3 l2'>";
    echo "<a href='" . route('legacy::urlaub::index', ['option' => 'uebersicht']) . "'>Übersicht</a>";
    echo "</div>";
    echo "<div class='col s4 m3 l2'>";
    echo "<a href='" . route('legacy::urlaub::index', ['option' => 'monatsansicht']) . "'>Monatsansicht</a>";
    echo "</div>";
    echo "<div class='col s4 m3 l2'>";
    echo "<a href='" . route('legacy::urlaub::index', ['option' => 'urlaubsplan_jahr']) . "'>Urlaubsplan PDF</a>";
    echo "</div>";
    ?>
</div>
    