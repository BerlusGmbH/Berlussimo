<div class="row">
    <div class='col-xs-5'>
        <h3>Mieter-Mandate</h3>
        <div class="row">
            <div class='col-xs-6 col-md-6 col-lg-2'>
                <a href='<?php echo route('web::sepa::legacy', ['option' => 'mandat_mieter_neu']) ?>'>Neu</a>
            </div>
            <div class='col-xs-6 col-md-6 col-lg-2'>
                <a href='<?php echo route('web::sepa::legacy', ['option' => 'mandate_mieter_kurz']) ?>'>Alle</a>
            </div>
            <div class='col-xs-12 col-md-6 col-lg-2'>
                <a href='<?php echo route('web::sepa::legacy', ['option' => 'mandate_mieter']) ?>'>Einziehen</a>
            </div>
            <div class='col-xs-12 col-md-6 col-lg-2'>
                <a href='<?php echo route('web::sepa::legacy', ['option' => 'ls_auto_buchen']) ?>'>Buchen</a>
            </div>
        </div>
    </div>
    <div class='col-xs-5'>
        <h3>Rechnungen-Mandate</h3>
        <div class="row">
            <div class='col-xs-12 col-md-4'>
                <a href='<?php echo route('web::sepa::legacy', ['option' => 'mandate_rechnungen']) ?>'>Alle</a>
            </div>
            <div class='col-xs-12 col-md-4'>
                <a href='<?php echo route('web::sepa::legacy', ['option' => 're_zahlen']) ?>'>RE zahlen</a>
            </div>
            <div class='col-xs-12 col-md-4'>
                <a href='<?php echo route('web::sepa::legacy', ['option' => 'ra_zahlen']) ?>'>RA zahlen</a>
            </div>
        </div>
    </div>
    <div class='col-xs-2'>
        <h3>Hausgeld-Mandate</h3>
        <div class="row">
            <div class='col-xs-4'>
                <a href='<?php echo route('web::sepa::legacy', ['option' => 'mandate_hausgeld']) ?>'>Alle</a>
            </div>
            <div class='col-xs-4'>
                <a href='<?php echo route('web::sepa::legacy', ['option' => 'mandat_hausgeld_neu']) ?>'>Neu</a>
            </div>
        </div>
    </div>
    <div class='col-xs-6'>
        <h3>Manuelle Überweisung</h3>
        <div class="row">
            <div class='col-xs-12 col-md-6'>
                <a href='<?php echo route('web::sepa::legacy', ['option' => 'sammel_ue']) ?>'>Sammelüberweisung</a>
            </div>
            <div class='col-xs-12 col-md-6'>
                <a href='<?php echo route('web::sepa::legacy', ['option' => 'sammel_ue_IBAN']) ?>'>Sammelüberweisung
                    IBAN</a>
            </div>
        </div>
    </div>
    <div class='col-xs-6'>
        <h3>Übersicht</h3>
        <div class="row">
            <div class='col-xs-12 col-md-4'>
                <a href='<?php echo route('web::sepa::legacy', ['option' => 'sammler_anzeigen']) ?>'>Aktueller
                    Sammler</a>
            </div>
            <div class='col-xs-12 col-md-5'>
                <a href='<?php echo route('web::sepa::legacy', ['option' => 'sepa_files']) ?>'>Archiv (Aktuelles
                    Konto)</a>
            </div>
            <div class='col-xs-12 col-md-2'>
                <a href='<?php echo route('web::sepa::legacy', ['option' => 'sepa_files_fremd']) ?>'>Archiv</a>
            </div>
        </div>
    </div>
</div>