<h6>Details</h6>
<div class="row">
<?php
echo "<div class='col-xs-4 col-sm-4 col-md-3 col-lg-2'>";
echo "<a href='" . route('web::details::legacy', ['option' => 'detail_suche']) . "'>Suche</a>";
echo "</div>";
echo "<div class='col-xs-4 col-sm-4 col-md-3 col-lg-2'>";
echo "<a href='" . route('web::admin::legacy', ['admin_panel' => 'details_neue_kat']) . "'>Neue Hauptdetails</a>";
echo "</div>";
echo "<div class='col-xs-4 col-sm-4 col-md-3 col-lg-2'>";
echo "<a href='" . route('web::admin::legacy', ['admin_panel' => 'details_neue_ukat']) . "'>Neue Detailoptionen</a>";
echo "</div>";
?>
</div>