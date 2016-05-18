<h6>Benutzerverwaltung</h6>
<div class="row">
    <?php
    echo "<div class='col s4 m3 l2'>";
    echo "<a href='" . route('legacy::benutzer::index') . "'>Alle Benutzer</a>";
    echo "</div>";
    echo "<div class='col s4 m3 l2'>";
    echo "<a href='" . route('legacy::benutzer::index', ['option' => 'neuer_benutzer']) . "'>Neuer Benutzer</a>";
    echo "</div>";
    echo "<div class='col s4 m3 l2'>";
    echo "<a href='" . route('legacy::benutzer::index', ['option' => 'werkzeuge']) . "'>Werkzeuge</a>";
    echo "</div>";
    ?>
</div>
    