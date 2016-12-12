<h6>Mietkontenübersicht -> Darstelltungsoptionen...</h6>
<div class="row">
    <?php
    echo "<div class='col s4 m3 l1'>";
    echo "<a href='" . route('web::mietkontenblatt::legacy', ['anzeigen' => 'mietkonto_uebersicht_detailiert', 'mietvertrag_id' => request()->input('mietvertrag_id')]) . "'>Seit Einzug</a>";
    echo "</div>";
    echo "<div class='col s4 m3 l1'>";
    echo "<a href=\"'" . route('web::mietkontenblatt::legacy', ['anzeigen' => 'mietkonto_detailiert_seit_1zahlung', 'mietvertrag_id' => request()->input('mietvertrag_id')]) . "'>Seit 1. Zahlung</a>";
    echo "</div>";
    echo "<div class='col s4 m3 l1'>";
    echo "<a href='" . route('web::miete_buchen::legacy') . "'>Zeitraum eingrenzen</a>";
    echo "</div>";
    ?>
</div>