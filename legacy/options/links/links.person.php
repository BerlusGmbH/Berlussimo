<h6>Personen</h6>
<div class="row">
    <?php
    echo "<div class='col s4 m6 l3'>";
    echo "<a href='" . route('web::personen::legacy', ['anzeigen' => 'person_hinweis']) . "'>Personen mit Hinweisen</a>";
    echo "</div>";
    echo "<div class='col s4 m6 l3'>";
    echo "<a href='" . route('web::personen::legacy', ['anzeigen' => 'person_anschrift']) . "'>Zustell- und Verzugsanschriften</a>";
    echo "</div>";
    ?>
</div>
    