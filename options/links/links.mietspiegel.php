<?php
$f = new formular ();
echo "<div class=\"navi_leiste2\">";
$f->erstelle_formular ( "Hauptmen� -> Mietspiegelverwaltung...", NULL );
echo "<a href=\"?daten=mietspiegel&option=mietspiegelliste\">Mietspiegelliste</a>&nbsp;";
echo "<a href=\"?daten=mietspiegel&option=neuer_mietspiegel\">Neuer Mietspiegel</a>&nbsp;";
$f->ende_formular ();

echo "</div>";

?>