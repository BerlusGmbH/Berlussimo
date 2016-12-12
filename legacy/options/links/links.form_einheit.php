<h6>Einheiten</h6>
<div class="row">
    <?php
    echo "<div class='col s4 m3 l1'>";
    echo "<a href='" . route('web::einheiten::legacy', ['einheit_raus' => 'einheit_kurz']) . "'>Alle</a>";
    echo "</div>";
    echo "<div class='col s4 m3 l1'>";
    echo "<a href='" . route('web::einheiten::legacy', ['einheit_raus' => 'einheit_neu']) . "'>Neu</a>";
    echo "</div>";
    ?>
</div>