<div class="row">
    <div class="col-xs-12">
        <?php
        if (!session()->has('objekt_id')) {
            echo "<h3>WEG</h3>";
        } else {
            $o = new objekt ();
            $o->get_objekt_infos(session()->get('objekt_id'));
            echo "<h3>WEG: $o->objekt_kurzname</h3>";
        }
        ?>
        <div class="row">
            <div class='col-xs-6 col-sm-3 col-md-3 col-lg-2'>
                <a class="WEG" href='<?php echo route('web::weg::legacy') ?>'>E-Mail</a>
            </div>
            <div class='col-xs-6 col-sm-3 col-md-3 col-lg-2'>
                <a href='<?php echo route('web::weg::legacy', ['option' => 'stammdaten_weg', 'lang' => 'en']) ?>'>Stammdaten</a>
            </div>
            <div class='col-xs-6 col-sm-3 col-md-3 col-lg-2'>
                <a href='<?php echo route('web::weg::legacy', ['option' => 'pdf_et_liste_alle_kurz']) ?>'>Eigentümerdaten</a>
            </div>
            <div class='col-xs-6 col-sm-3 col-md-3 col-lg-2'>
                <a href='<?php echo route('web::weg::legacy', ['option' => 'einheiten']) ?>'>Einheiten</a>
            </div>
            <div class='col-xs-6 col-sm-3 col-md-3 col-lg-2'>
                <a href='<?php echo route('web::weg::legacy', ['option' => 'eigentuemer_wechsel']) ?>'>Eigentümerwechsel</a>
            </div>
            <div class='col-xs-6 col-sm-3 col-md-3 col-lg-2'>
                <a href='<?php echo route('web::weg::legacy', ['option' => 'mahnliste']) ?>'>Mahnliste</a>
            </div>
            <div class='col-xs-6 col-sm-3 col-md-3 col-lg-2'>
                <a href='<?php echo route('web::weg::legacy', ['option' => 'serienbrief']) ?>'>Serienbrief</a>
            </div>
        </div>
    </div>
    <div class="col-xs-6">
        <h3>Buchen</h3>
        <div class="row">
            <div class='col-xs-6 col-sm-4 col-md-4 col-lg-3'>
                <a class="WEG"
                   href='<?php echo route('web::weg::legacy', ['option' => 'wohngeld_buchen_auswahl_e']) ?>'>Hausgeld</a>
            </div>
            <div class='col-xs-6 col-sm-4 col-md-4 col-lg-3'>
                <a class="WEG"
                   href='<?php echo route('web::buchen::legacy', ['option' => 'zahlbetrag_buchen']) ?>'>Kosten</a>
            </div>
            <div class='col-xs-6 col-sm-4 col-md-4 col-lg-4'>
                <a class="WEG" href='<?php echo route('web::weg::legacy', ['option' => 'kontostand_erfassen']) ?>'>Kontostand
                    erfassen</a>
            </div>
        </div>
    </div>
    <div class="col-xs-6 col-md-4 col-lg-2">
        <h3>Wirtschaftspläne</h3>
        <div class="row">
            <div class='col-xs-6'>
                <a class="WEG"
                   href='<?php echo route('web::weg::legacy', ['option' => 'wpliste']) ?>'>Alle</a>
            </div>
            <div class='col-xs-6'>
                <a class="WEG" href='<?php echo route('web::weg::legacy', ['option' => 'wp_neu']) ?>'>Neu</a>
            </div>
        </div>
    </div>
    <div class="col-xs-6 col-md-4 col-lg-2">
        <h3>IHR</h3>
        <div class="row">
            <div class='col-xs-4'>
                <a class="WEG" href='<?php echo route('web::weg::legacy', ['option' => 'ihr']) ?>'>IHR</a>
            </div>
            <div class='col-xs-8'>
                <a class="WEG" href='<?php echo route('web::weg::legacy', ['option' => 'pdf_ihr']) ?>'>PDF-IHR</a>
            </div>
        </div>
    </div>
    <?php
    $jahr = date("Y");
    $vorjahr = date("Y") - 1;
    ?>
    <div class="col-xs-6 col-md-4 col-lg-2">
        <h3>Kontenübersicht</h3>
        <div class="row">
            <div class='col-xs-6 col-md-4'>
                <a class="WEG"
                   href='<?php echo route('web::weg::legacy', ['option' => 'hausgeld_zahlungen', 'jahr' => $jahr]) ?>'><?php echo $jahr ?></a>
            </div>
            <div class='col-xs-6'>
                <a class="WEG"
                   href='<?php echo route('web::weg::legacy', ['option' => 'hausgeld_zahlungen_xls', 'jahr' =>
                       $vorjahr]) ?>'><?php echo $vorjahr ?> XLS</a>
            </div>
        </div>
    </div>
    <div class="col-xs-12">
        <h3>Hausgeldabrechnung</h3>
        <div class="row">
            <div class='col-xs-6 col-sm-4 col-lg-2'>
                <a class="WEG" href='<?php echo route('web::weg::legacy', ['option' => 'assistent']) ?>'>Assistent</a>
            </div>
            <div class='col-xs-6 col-sm-4 col-lg-2'>
                <a class="WEG" href='<?php echo route('web::weg::legacy', ['option' => 'hga_profile']) ?>'>Profile</a>
            </div>
            <div class='col-xs-6 col-sm-4 col-lg-2'>
                <a class="WEG"
                   href='<?php echo route('web::weg::legacy', ['option' => 'pdf_hausgelder']) ?>'>Hausgelder</a>
            </div>
            <div class='col-xs-6 col-sm-4 col-md-4 col-lg-2'>
                <a class="WEG"
                   href='<?php echo route('web::weg::legacy', ['option' => 'hk_verbrauch_tab']) ?>'>Heizkosten</a>
            </div>
            <div class='col-xs-6 col-sm-4 col-md-4 col-lg-2'>
                <a class="WEG" href='<?php echo route('web::weg::legacy', ['option' => 'hga_gesamt_pdf']) ?>'>Gesamtabrechnung</a>
            </div>
            <div class='col-xs-6 col-sm-4 col-md-4 col-lg-2'>
                <a class="WEG"
                   href='<?php echo route('web::weg::legacy', ['option' => 'hga_einzeln']) ?>'>Einzelabrechnung</a>
            </div>
        </div>
    </div>
</div>