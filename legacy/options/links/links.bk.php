<div class="row">
    <div class='col s12'>
        <h6>Betriebskosten & Nebenkostenabrechnung</h6>
        <div class='col s4 m3 l2'>
            <a href='<?php echo route('legacy::bk::index', ['option' => 'profile']) ?>'>Profile</a>
        </div>
        <div class='col s4 m3 l2'>
            <a href='<?php echo route('legacy::bk::index', ['option' => 'assistent']) ?>'>Assistent</a>
        </div>
        <div class='col s4 m3 l2'>
            <a href='<?php echo route('legacy::bk::index', ['option' => 'profil_reset']) ?>'>Profil reset</a>
        </div>
        <div class='col s4 m3 l2'>
            <a href='<?php echo route('legacy::bk::index', ['option' => 'zusammenfassung']) ?>'>Zusammenfassung</a>
        </div>
        <div class='col s4 m3 l2'>
            <a href='<?php echo route('legacy::bk::index', ['option' => 'pdf_ausgabe']) ?>'>PDF-Ausgabe</a>
        </div>
        <div class='col s4 m3 l2'>
            <a href='<?php echo route('legacy::bk::index', ['option' => 'anpassung_bk_hk']) ?>'>BK/HK Anpassung</a>
        </div>
        <div class='col s4 m3 l2'>
            <a href='<?php echo route('legacy::bk::index', ['option' => 'energie']) ?>'>Energiewerte</a>
        </div>
        <div class='col s4 m3 l2'>
            <a href='<?php echo route('legacy::bk::index', ['option' => 'anpassung_bk_nk']) ?>'>NK-BK
                    eingeben</a>
        </div>
        <div class='col s4 m3 l2'>
            <a href='<?php echo route('legacy::bk::index', ['option' => 'form_profil_kopieren']) ?>'>Profile
                kopieren</a>
        </div>
    </div>
    <div class='col s12 m6 l4'>
        <h6>Wirtschaftseinheiten</h6>
        <div class='col s6 l6'>
            <a href='<?php echo route('legacy::bk::index', ['option' => 'wirtschaftseinheiten']) ?>'>Alle</a>
        </div>
        <div class='col s6 l6'>
            <a href='<?php echo route('legacy::bk::index', ['option' => 'wirtschaftseinheit_neu']) ?>'>Neu</a>
        </div>
    </div>
    <div class='col s12 m6 l4'>
        <h6>Serienbriefe</h6>
        <div class='col s6 m6 l6'>
            <a href='<?php echo route('legacy::bk::index', ['option' => 'serienbrief']) ?>'>Vorlagen</a>
        </div>
        <div class='col s6 m6 l6'>
            <a href='<?php echo route('legacy::bk::index', ['option' => 'serienbrief_vorlage_neu']) ?>'>Neue
                Vorlage</a>
        </div>
    </div>
</div>
