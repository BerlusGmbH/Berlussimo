<div class="row">
    <div class="col s12">
        <?php
        if (!session()->has('objekt_id')) {
            echo "<h6>WEG</h6>";
        } else {
            $o = new objekt ();
            $o->get_objekt_infos(session()->get('objekt_id'));
            echo "<h6>WEG: $o->objekt_kurzname</h6>";
        }
        ?>
        <div class='col s6 m3 l2'>
            <a class="WEG" href='<?php echo route('legacy::weg::index') ?>'>E-Mail</a>
        </div>
        <div class='col s6 m3 l2'>
            <a class="WEG"
               href='<?php echo route('legacy::weg::index', ['option' => 'objekt_auswahl']) ?>'>WEG wechseln</a>
        </div>
        <div class='col s6 m3 l2'>
            <a class="WEG"
               href='<?php echo route('legacy::weg::index', ['option' => 'stammdaten_weg', 'lang' => 'en']) ?>'>Stammdaten</a>
        </div>
        <div class='col s6 m3 l2'>
            <a class="WEG" href='<?php echo route('legacy::weg::index', ['option' => 'pdf_et_liste_alle_kurz']) ?>'>Eigentümerdaten</a>
        </div>
        <div class='col s6 m3 l2'>
            <a class="WEG" href='<?php echo route('legacy::weg::index', ['option' => 'einheiten']) ?>'>Einheiten</a>
        </div>
        <div class='col s6 m3 l2'>
            <a class="WEG" href='<?php echo route('legacy::weg::index', ['option' => 'eigentuemer_wechsel']) ?>'>Eigentümerwechsel</a>
        </div>
        <div class='col s6 m3 l2'>
            <a class="WEG" href='<?php echo route('legacy::weg::index', ['option' => 'mahnliste']) ?>'>Mahnliste</a>
        </div>
        <div class='col s6 m3 l2'>
            <a class="WEG" href='<?php echo route('legacy::weg::index', ['option' => 'serienbrief']) ?>'>Serienbrief</a>
        </div>
    </div>
    <div class="col s6 m12 l6">
        <h6>Buchen</h6>
        <div class='col s12 m4 l3'>
            <a class="WEG" href='<?php echo route('legacy::weg::index', ['option' => 'wohngeld_buchen_auswahl_e']) ?>'>Hausgeld</a>
        </div>
        <div class='col s12 m4 l3'>
            <a class="WEG" href='<?php echo route('legacy::buchen::index', ['option' => 'zahlbetrag_buchen']) ?>'>Kosten</a>
        </div>
        <div class='col s12 m4 l6'>
            <a class="WEG" href='<?php echo route('legacy::weg::index', ['option' => 'kontostand_erfassen']) ?>'>Kontostand
                erfassen</a>
        </div>
    </div>
    <div class="col s6 m4 l2">
        <h6>Wirtschaftspläne</h6>
        <div class='col s6 m6 l6'>
            <a class="WEG"
               href='<?php echo route('legacy::weg::index', ['option' => 'wpliste']) ?>'>Alle</a>
        </div>
        <div class='col s6 m6 l6'>
            <a class="WEG" href='<?php echo route('legacy::weg::index', ['option' => 'wp_neu']) ?>'>Neu</a>
        </div>
    </div>
    <div class="col s6 m4 l2">
        <h6>IHR</h6>
        <div class='col s4 m6 l4'>
            <a class="WEG" href='<?php echo route('legacy::weg::index', ['option' => 'ihr']) ?>'>IHR</a>
        </div>
        <div class='col s8 m6 l8'>
            <a class="WEG" href='<?php echo route('legacy::weg::index', ['option' => 'pdf_ihr']) ?>'>PDF-IHR</a>
        </div>
    </div>
    <?php
    $jahr = date("Y");
    $vorjahr = date("Y") - 1;
    ?>
    <div class="col s12 m4 l2">
        <h6>Kontenübersicht</h6>
        <div class='col s6 m4 l4'>
            <a class="WEG"
               href='<?php echo route('legacy::weg::index', ['option' => 'hausgeld_zahlungen', 'jahr' => $jahr]) ?>'><?php echo $jahr ?></a>
        </div>
        <div class='col s6 m8 l8'>
            <a class="WEG" href='<?php echo route('legacy::weg::index', ['option' => 'hausgeld_zahlungen_xls', 'jahr' =>
                $vorjahr]) ?>'><?php echo $vorjahr ?> XLS</a>
        </div>
    </div>
    <div class="col s12 m12">
        <h6>Hausgeldabrechnung</h6>
        <div class='col s4 m4 l2'>
            <a class="WEG" href='<?php echo route('legacy::weg::index', ['option' => 'assistent']) ?>'>Assistent</a>
        </div>
        <div class='col s4 m4 l2'>
            <a class="WEG" href='<?php echo route('legacy::weg::index', ['option' => 'hga_profile']) ?>'>Profile</a>
        </div>
        <div class='col s4 m4 l2'>
            <a class="WEG"
               href='<?php echo route('legacy::weg::index', ['option' => 'pdf_hausgelder']) ?>'>Hausgelder</a>
        </div>
        <div class='col s6 m4 l2'>
            <a class="WEG"
               href='<?php echo route('legacy::weg::index', ['option' => 'hk_verbrauch_tab']) ?>'>Heizkosten</a>
        </div>
        <div class='col s6 m4 l2'>
            <a class="WEG" href='<?php echo route('legacy::weg::index', ['option' => 'hga_gesamt_pdf']) ?>'>Gesamtabrechnung</a>
        </div>
        <div class='col s6 m4 l2'>
            <a class="WEG" href='<?php echo route('legacy::weg::index', ['option' => 'hga_einzeln']) ?>'>Einzelabrechnung</a>
        </div>
    </div>
</div>