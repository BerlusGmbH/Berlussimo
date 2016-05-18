<h6>Personen</h6>
<div class="row">
    <?php
    echo "<div class='col s4 m3 l1'>";
    echo "<a href='" . route('legacy::personen::index', ['anzeigen' => 'alle_personen']) . "'>Suche</a>";
    echo "</div>";
    echo "<div class='col s4 m3 l1'>";
    echo "<a href='" . route('legacy::personen::index', ['anzeigen' => 'person_erfassen']) . "'>Neu</a>";
    echo "</div>";
    echo "<div class='col s4 m6 l3'>";
    echo "<a href='" . route('legacy::personen::index', ['anzeigen' => 'person_hinweis']) . "'>Personen mit Hinweisen</a>";
    echo "</div>";
    echo "<div class='col s4 m6 l3'>";
    echo "<a href='" . route('legacy::personen::index', ['anzeigen' => 'person_anschrift']) . "'>Zustell- und Verzugsanschriften</a>";
    echo "</div>";
    ?>
</div>
    