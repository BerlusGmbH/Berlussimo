<?php

class berlussimo_global
{
    public $vermietete_einheiten = [];
    public $unvermietete_einheiten = [];

    function berlussimo_global()
    {
        $this->datum_heute = date("Y-m-d");
    }

    function objekt_auswahl_liste()
    {
        session()->put('url.intended', URL::full());
        if (!session()->has('objekt_id')) {
            session()->flash(\App\Messages\InfoMessage::TYPE, ['Bitte wählen Sie ein Objekt.']);
        }
    }

    function monate_jahres_links($jahr, $link)
    {
        $f = new formular ();
        $f->fieldset("Monats- und Jahresauswahl", 'monate_jahre');
        $vorjahr = $jahr - 1;
        $nachjahr = $jahr + 1;
        $link_vorjahr = "&nbsp;<a href=\"$link&jahr=$vorjahr&monat=12\"><b>$vorjahr</b></a>&nbsp;";
        $link_nach = "&nbsp;<a href=\"$link&jahr=$nachjahr&monat=01\"><b>$nachjahr</b></a>&nbsp;";
        echo $link_vorjahr;
        $link_alle = "<a href=\"$link&jahr=$jahr\">Alle von $jahr</a>&nbsp;";
        echo $link_alle;
        for ($a = 1; $a <= 12; $a++) {
            $monat_zweistellig = sprintf('%02d', $a);
            $link_neu = "<a href=\"$link&monat=$monat_zweistellig&jahr=$jahr\">$a/$jahr</a>&nbsp;";
            // echo "$a/$jahr<br>";
            echo "$link_neu";
        }
        echo $link_nach;
        $f->fieldset_ende();
    }

    function jahres_links($jahr, $link)
    {
        $f = new formular ();
        $f->fieldset("Jahr wählen", 'monate_jahre');
        $vorjahr = $jahr - 1;
        $nachjahr = $jahr + 1;
        $link_vorjahr = "&nbsp;<a href=\"$link&jahr=$vorjahr\"><b>$vorjahr</b></a>&nbsp;";
        $link_nach = "&nbsp;<a href=\"$link&jahr=$nachjahr\"><b>$nachjahr</b></a>&nbsp;";
        echo $link_vorjahr;
        echo $link_nach;
        $f->fieldset_ende();
    }
} // ende class global