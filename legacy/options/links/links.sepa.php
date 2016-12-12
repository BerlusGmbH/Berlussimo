<div class="row">
    <div class='col s5 m6'>
        <h6>Mieter-Mandate</h6>
        <div class='col s6 m6 l2'>
            <a href='<?php echo route('web::sepa::legacy', ['option' => 'mandat_mieter_neu']) ?>'>Neu</a>
        </div>
        <div class='col s6 m6 l2'>
            <a href='<?php echo route('web::sepa::legacy', ['option' => 'mandate_mieter_kurz']) ?>'>Alle</a>
        </div>
        <div class='col s12 m6 l2'>
            <a href='<?php echo route('web::sepa::legacy', ['option' => 'mandate_mieter']) ?>'>Einziehen</a>
        </div>
        <div class='col s12 m6 l2'>
            <a href='<?php echo route('web::sepa::legacy', ['option' => 'ls_auto_buchen']) ?>'>Buchen</a>
        </div>
    </div>
    <div class='col s5 m4'>
        <h6>Rechnungen-Mandate</h6>
        <div class='col s12 m4 l4'>
            <a href='<?php echo route('web::sepa::legacy', ['option' => 'mandate_rechnungen']) ?>'>Alle</a>
        </div>
        <div class='col s12 m4 l4'>
            <a href='<?php echo route('web::sepa::legacy', ['option' => 're_zahlen']) ?>'>RE zahlen</a>
        </div>
        <div class='col s12 m4 l4'>
            <a href='<?php echo route('web::sepa::legacy', ['option' => 'ra_zahlen']) ?>'>RA zahlen</a>
        </div>
    </div>
    <div class='col s2'>
        <h6>Hausgeld-Mandate</h6>
        <div class='col s4 m6 l4'>
            <a href='<?php echo route('web::sepa::legacy', ['option' => 'mandate_hausgeld']) ?>'>Alle</a>
        </div>
        <div class='col s4 m6 l4'>
            <a href='<?php echo route('web::sepa::legacy', ['option' => 'mandat_hausgeld_neu']) ?>'>Neu</a>
        </div>
    </div>
    <div class='col s6'>
        <h6>Manuelle Überweisung</h6>
        <div class='col s12 m12 l6'>
            <a href='<?php echo route('web::sepa::legacy', ['option' => 'sammel_ue']) ?>'>Sammelüberweisung</a>
        </div>
        <div class='col s12 m12 l6'>
            <a href='<?php echo route('web::sepa::legacy', ['option' => 'sammel_ue_IBAN']) ?>'>Sammelüberweisung
                IBAN</a>
        </div>
    </div>
    <div class='col s6'>
        <h6>Übersicht</h6>
        <div class='col s12 m4 l4'>
            <a href='<?php echo route('web::sepa::legacy', ['option' => 'sammler_anzeigen']) ?>'>Aktueller Sammler</a>
        </div>
        <div class='col s12 m5 l5'>
            <a href='<?php echo route('web::sepa::legacy', ['option' => 'sepa_files']) ?>'>Archiv (Aktuelles
                Konto)</a>
        </div>
        <div class='col s12 m2 l2'>
            <a href='<?php echo route('web::sepa::legacy', ['option' => 'sepa_files_fremd']) ?>'>Archiv</a>
        </div>
    </div>
</div>