<?php

$daten = request()->input('daten');
if (request()->filled('einheit_raus')) {
    $einheit_raus = request()->input('einheit_raus');
} else {
    $einheit_raus = 'default';
}
if (request()->filled('haus_id')) {
    $haus_id = request()->input('haus_id');
} else {
    $haus_id = '';
}
if (request()->filled('objekt_id')) {
    $objekt_id = request()->input('objekt_id');
} else {
    $objekt_id = '';
}

switch ($einheit_raus) {

    default :
        break;

    case "mieterliste_aktuell" :
        $e = new einheit ();
        if (request()->filled('objekt_id') && !empty (request()->input('objekt_id'))) {
            $e->pdf_mieterliste(0, request()->input('objekt_id'));
        } else {
            $e->pdf_mieterliste(0);
        }
        break;

    case "mieteremail_aktuell" :
        $e = new einheit ();
        if (request()->input('objekt_id') && !empty (request()->input('objekt_id'))) {
            $o = new objekt ();
            $o->get_objekt_infos(request()->input('objekt_id'));
            echo "<h1>$o->objekt_kurzname</h1>";

            $emails_arr = $e->emails_mieter_arr(request()->input('objekt_id'));
            if (is_array($emails_arr)) {
                $emails_arr_u = array_values(array_unique($emails_arr));
                $anz = count($emails_arr_u);
                echo "<hr><a href=\"mailto:?bcc=";
                for ($a = 0; $a < $anz; $a++) {
                    $email = $emails_arr_u [$a];
                    echo "$email";
                    if ($a < $anz - 1) {
                        echo ",";
                    }
                }
                echo "\">Email an alle Mieter ($anz Emailadressen)</a>";
            } else {
                echo "Keine Emailadressen der Mieter";
            }
        } else {
            fehlermeldung_ausgeben("Objekt für Email wählen");
        }
        break;
}