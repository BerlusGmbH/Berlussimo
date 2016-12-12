<h6>Administration</h6>
<div class="row">
<?php
echo "<div class='col s4 m3 l2'>";
echo "<a href='" . route('web::admin::legacy', ['admin_panel' => 'details_neue_kat']) . "'>Neue Hauptdetails</a>";
echo "</div>";
echo "<div class='col s4 m3 l2'>";
echo "<a href='" . route('web::admin::legacy', ['admin_panel' => 'details_neue_ukat']) . "'>Neue Detailoptionen</a>";
echo "</div>";
?>
</div>