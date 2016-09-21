<h6>Mietspiegelverwaltung</h6>
<div class="row">
    <?php
    echo "<div class='col s4 m3 l2'>";
    echo "<a href='" . route('legacy::mietspiegel::index', ['option' => 'mietspiegelliste']) . "'>Mietspiegelliste</a>";
    echo "</div>";
    echo "<div class='col s4 m3 l2'>";
    echo "<a href='" . route('legacy::mietspiegel::index', ['option' => 'neuer_mietspiegel']) . "'>Neuer Mietspiegel</a>";
    echo "</div>";
    ?>
</div>