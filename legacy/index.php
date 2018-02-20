<?php

define("BERLUS_PATH", __DIR__);

echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">';
echo "<head>";
////echo "<link href=\"css/lightbox.css\" rel=\"stylesheet\">";
// echo "<script type=\"text/javascript\" src=\"js/lightbox.js\"></script>";

////echo "<script type=\"text/javascript\" src=\"legacy/ajax/ajax.js\"></script>\n";
////echo "<script type=\"text/javascript\" src=\"legacy/ajax/dd_kostenkonto.js\"></script>\n";
////echo "<script type=\"text/javascript\" src=\"legacy/js/javascript.js\"></script>\n";
////echo "<script type=\"text/javascript\" src=\"legacy/js/sorttable.js\"></script>\n";
////echo "<link rel=\"stylesheet\" type=\"text/css\"  href=\"css/uebersicht.css\" media=\"screen\">\n";
////echo "<link rel=\"stylesheet\" type=\"text/css\"  href=\"css/berlussimo.css\"  media=\"screen\">\n";
////echo "<link href=\"css/demo.css\"       rel=\"stylesheet\" type=\"text/css\" /  media=\"screen\">";

////echo "<script src='https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js'></script>";
$legacyCss = mix('css/legacy.css');
echo "<link href='$legacyCss' rel='stylesheet' type='text/css'>\n";

echo "</head>";

echo "<body>\n";
$form = new mietkonto ();
$benutzer = Auth::user()->email;
erstelle_abschnitt("Benutzer: $benutzer");

if (request()->has('partner_id')) {
    session()->put('partner_id', request()->input('partner_id'));
}

if (check_user_links(Auth::user()->id, 'rechnungen')) {
    if (session()->has('partner_id')) {
        $p = new partners ();
        $p->get_partner_name(session()->get('partner_id'));
        $link_partner = "<a href='" . route('web::rechnungen::legacy', ['option' => 'partner_wechseln']) . "'>Partner wechseln: <b>$p->partner_name</b></a>&nbsp;&nbsp;";
    } else {
        $link_partner = "<a href='" . route('web::rechnungen::legacy', ['option' => 'partner_wechseln']) . "'>Partner wählen</b></a>&nbsp;&nbsp;";
    }
} else {
    $link_partner = '';
}

if (check_user_links(Auth::user()->id, 'buchen')) {
    if (session()->has('geldkonto_id')) {
        $g = new geldkonto_info ();
        $g->geld_konto_details(session()->get('geldkonto_id'));
        $link_geldkonto = "<a href='" . route('web::buchen::legacy', ['option' => 'geldkonto_aendern']) . "'>Geldkonto: $g->geldkonto_bezeichnung_kurz</a>&nbsp;&nbsp;";
    } else {
        $link_geldkonto = "<a href='" . route('web::buchen::legacy', ['option' => 'geldkonto_aendern']) . "'>Geldkonto wählen</a>&nbsp;&nbsp;";
    }
} else {
    $link_geldkonto = '';
}

$link_logout = '<a href="/logout">Abmelden</a>';

echo "<div style='text-align: center'>$link_partner &nbsp; $link_geldkonto<span style='float: right'>$link_logout</span></div>";
ende_abschnitt();

include("options/links/links.statisch.php");

include_options();
echo "<div  id=\"aus\"><center><b>Berlussimo</b> wurde von der <a target=\"_new\"  href=\"http://www.berlus.de\">Berlus GmbH</a> - Hausverwaltung zur Verfügung gestellt.</center></div>";

$mixJs = mix('js/legacy.js');
echo "<script type='text/javascript' src='$mixJs'></script>\n";
$mixJs = mix('js/lightbox-plus-jquery.js');
echo "<script type='text/javascript' src='$mixJs'></script>\n";

echo "</body></html>";
function include_options()
{
    $optdir = dir(base_path('legacy/options/case'));
    while ($func = $optdir->read()) {
        if (substr($func, 0, 5) == "case.") {
            include($optdir->path . "/" . $func);
        }
    }
    closedir($optdir->handle);
}
