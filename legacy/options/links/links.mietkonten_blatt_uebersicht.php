<h3>Mietkontenübersicht -> Darstelltungsoptionen...</h3>
<div class="row">
    <?php
    echo "<div class='col-xs-4 col-md-3 col-lg-1'>";
    echo "<a href='" . route('web::mietkontenblatt::legacy', ['anzeigen' => 'mietkonto_uebersicht_detailiert', 'mietvertrag_id' => request()->input('mietvertrag_id')]) . "'>Seit Einzug</a>";
    echo "</div>";
    echo "<div class='col-xs-4 col-md-3 col-lg-1'>";
    echo "<a href=\"'" . route('web::mietkontenblatt::legacy', ['anzeigen' => 'mietkonto_detailiert_seit_1zahlung', 'mietvertrag_id' => request()->input('mietvertrag_id')]) . "'>Seit 1. Zahlung</a>";
    echo "</div>";
    echo "<div class='col-xs-4 col-md-3 col-lg-1'>";
    echo "<a href='" . route('web::miete_buchen::legacy') . "'>Zeitraum eingrenzen</a>";
    echo "</div>";
    ?>
</div>