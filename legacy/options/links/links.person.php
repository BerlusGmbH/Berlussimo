<h3>Personen</h3>
<div class="row">
    <?php
    echo "<div class='col-xs-6 col-sm-4 col-md-3'>";
    echo "<a href='" . route('web::personen::legacy', ['anzeigen' => 'person_hinweis']) . "'>Personen mit Hinweisen</a>";
    echo "</div>";
    echo "<div class='col-xs-6 col-sm-4 col-md-3'>";
    echo "<a href='" . route('web::personen::legacy', ['anzeigen' => 'person_anschrift']) . "'>Zustell- und Verzugsanschriften</a>";
    echo "</div>";
    ?>
</div>
    