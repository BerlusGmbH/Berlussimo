<h6>Miete</h6>
<div class="row">
    <?php
    echo "<div class='col s6 m4 l2'>";
    echo "<a href='" . route('legacy::miete_definieren::index') . "'>Miethöhe definieren</a>";
    echo "</div>";
    echo "<div class='col s6 m4 l2'>";
    echo "<a href='" . route('legacy::miete_definieren::index', ['option' => 'mieterlisten_kostenkat', 'kostenkat' => 'MOD']) . "'>Mieterliste MOD</a>";
    echo "</div>";
    echo "<div class='col s6 m4 l2'>";
    echo "<a href='" . route('legacy::miete_definieren::index', ['option' => 'mieterlisten_kostenkat', 'kostenkat' => 'Untermieter Zuschlag']) . "'>Mieterliste Untermieterz.</a>";
    echo "</div>";
    if (check_user_links(Auth::user()->id, 'mietanpassung')) {
        echo "<div class='col s6 m4 l2'>";
        echo "<a href='" . route('legacy::mietanpassungen::index', ['option' => 'uebersicht']) . "'>Mietanpassungstabelle</a>";
        echo "</div>";
        echo "<div class='col s6 m4 l3'>";
        echo "<a href='" . route('legacy::mietanpassungen::index', ['option' => 'ak4']) . "'>Ausstattungsklasse 4-TEST</a>";
        echo "</div>";
    }
    ?>
</div>