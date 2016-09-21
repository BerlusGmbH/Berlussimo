<h6>Partner</h6>
<div class="row">
    <?php
    echo "<div class='col s4 m3 l1'>";
    echo "<a href='" . route('legacy::partner::index', ['option' => 'partner_liste']) . "'>Alle</a>";
    echo "</div>";
    echo "<div class='col s4 m3 l1'>";
    echo "<a href='" . route('legacy::partner::index', ['option' => 'partner_erfassen']) . "'>Neu</a>";
    echo "</div>";
    echo "<div class='col s4 m3 l1'>";
    echo "<a href='" . route('legacy::partner::index') . "'>Suchen</a>";
    echo "</div>";
    echo "<div class='col s4 m3 l1'>";
    echo "<a href='" . route('legacy::partner::index', ['option' => 'partner_umsatz']) . "'>Umsatz</a>";
    echo "</div>";
    echo "<div class='col s4 m3 l1'>";
    echo "<a href='" . route('legacy::partner::index', ['option' => 'serienbrief']) . "'>Serienbrief</a>";
    echo "</div>";
    ?>
</div>
    