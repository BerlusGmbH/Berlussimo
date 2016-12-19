<h6>Objekte</h6>
<div class="row">
    <?php
    echo "<div class='col-xs-4 col-md-3 col-lg-1'>";
    echo "<a href='" . route('web::objekte::legacy', ['objekte_raus' => 'objekte_kurz']) . "'>Alle</a>";
    echo "</div>";
    echo "<div class='col-xs-4 col-md-3 col-lg-1'>";
    echo "<a href='" . route('web::objekte::legacy', ['objekte_raus' => 'objekt_anlegen']) . "'>Neu</a>";
    echo "</div>";
    echo "<div class='col-xs-4 col-md-3 col-lg-1'>";
    echo "<a href='" . route('web::objekte::legacy', ['objekte_raus' => 'objekt_kopieren']) . "'>Kopieren</a>";
    echo "</div>";
    ?>
</div>
    