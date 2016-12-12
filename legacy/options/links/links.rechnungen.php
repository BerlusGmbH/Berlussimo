<div class="row">
    <div class='col s6'>
        <h6>Rechnungen</h6>
        <div class='col s12 m4 l2'>
            <a href='<?php echo route('web::rechnungen::legacy', ['option' => 'erfasste_rechnungen']) ?>'>Alle</a>
        </div>
        <div class='col s12 m4 l2'>
            <a href='<?php echo route('web::rechnungen::legacy', ['option' => 'rechnung_erfassen']) ?>'>Erfassen</a>
        </div>
        <div class='col s12 m4 l2'>
            <a href='<?php echo route('web::rechnungen::legacy', ['option' => 'gutschrift_erfassen']) ?>'>Gutschrift</a>
        </div>
        <div class='col s12 m6 l2'>
            <a href='<?php echo route('web::rechnungen::legacy', ['option' => 'eingangsbuch']) ?>'>Eingangsbuch</a>
        </div>
        <div class='col s12 m6 l2'>
            <a href='<?php echo route('web::rechnungen::legacy', ['option' => 'ausgangsbuch']) ?>'>Ausgangsbuch</a>
        </div>
        <div class='col s12 m8 l4'>
            <a href='<?php echo route('web::rechnungen::legacy', ['option' => 'rechnungsbuch_suche']) ?>'>Rechnungsbücher
                PDF</a>
        </div>
        <div class='col s12 m8 l4'>
            <a href='<?php echo route('web::rechnungen::legacy', ['option' => 'sepa_druckpool']) ?>'>SEPA aus Rechnung</a>
        </div>
    </div>
    <div class='col s6'>
        <h6>Suchen & Filtern</h6>
        <div class='col s12 m12 l4'>
            <a href='<?php echo route('web::rechnungen::legacy', ['option' => 'rechnung_suchen']) ?>'>Rechnung
                    suchen</a>
        </div>
        <div class='col s12 m12 l4'>
            <a href='<?php echo route('web::rechnungen::legacy', ['option' => 'kosten_einkauf']) ?>'>Kosten
                Einkauf</a>
        </div>
        <div class='col s12 m12 l4'>
            <a href='<?php echo route('web::rechnungen::legacy', ['option' => 'vollstaendige_rechnungen']) ?>'>Vollständige
                Rechnungen</a>
        </div>
        <div class='col s12 m12 l4'>
            <a href='<?php echo route('web::rechnungen::legacy', ['option' => 'unvollstaendige_rechnungen']) ?>'>Unvollständige
                Rechnungen</a>
        </div>
        <div class='col s12 m12 l4'>
            <a href='<?php echo route('web::rechnungen::legacy', ['option' => 'kontierte_rechnungen']) ?>'>Kontierte
                Rechnungen</a>
        </div>
        <div class='col s12 m12 l4'>
            <a href='<?php echo route('web::rechnungen::legacy', ['option' => 'nicht_kontierte_rechnungen']) ?>'>Nicht
                kontierte Rechnungen</a>
        </div>
        <div class='col s12 m12 l4'>
            <a href='<?php echo route('web::rechnungen::legacy', ['option' => 'verbindlichkeiten']) ?>'>Verbindlichkeiten</a>
        </div>
        <div class='col s12 m12 l4'>
            <a href='<?php echo route('web::rechnungen::legacy', ['option' => 'forderungen']) ?>'>Forderungen</a>
        </div>
        <div class='col s12 m12 l4'>
            <a href='<?php echo route('web::rechnungen::legacy', ['option' => 'seb']) ?>'>SEB</a>
        </div>
    </div>
    <div class='col s2'>
        <h6>Import</h6>
        <div class='col s12 m6 l6'>
            <a href='<?php echo route('web::rechnungen::legacy', ['option' => 'form_ugl']) ?>'>UGL</a>
        </div>
        <div class='col s12 m6 l6'>
            <a href='<?php echo route('web::rechnungen::legacy', ['option' => 'import_csv']) ?>'>CSV</a>
        </div>
    </div>
    <div class='col s9'>
        <h6>Pool</h6>
        <div class='col s12 m6 l3'>
            <a href='<?php echo route('web::rechnungen::legacy', ['option' => 'pool_rechnungen']) ?>'>Rechnung aus
                Pool
                erstellen</a>
        </div>
        <div class='col s12 m6 l3'>
            <a href='<?php echo route('web::rechnungen::legacy', ['option' => 'u_pool_liste']) ?>'>Rechnungen im
                Unterpool</a>
        </div>
        <div class='col s12 m6 l2'>
            <a href='<?php echo route('web::rechnungen::legacy', ['option' => 'u_pool_erstellen']) ?>'>Unterpool
                erstellen</a>
        </div>
        <div class='col s12 m6 l2'>
            <a href='<?php echo route('web::rechnungen::legacy', ['option' => 'pdf_druckpool', 'no_logo']) ?>'>PDF-Druckpool</a>
        </div>
    </div>
    <div class='col s6 m4'>
        <h6>Angebote</h6>
        <div class='col s12 m4 l6'>
            <a href='<?php echo route('web::rechnungen::legacy', ['option' => 'meine_angebote']) ?>'>Alle</a>
        </div>
        <div class='col s12 m6 l6'>
            <a href='<?php echo route('web::rechnungen::legacy', ['option' => 'angebot_erfassen']) ?>'>Erfassen</a>
        </div>
    </div>
    <div class='col s6 m4'>
        <h6>Buchungsbelege</h6>
        <div class='col s12 m12 l6'>
            <a href='<?php echo route('web::rechnungen::legacy', ['option' => 'buchungsbelege']) ?>'>Buchungsbelege</a>
        </div>
        <div class='col s12 m12 l6'>
            <a href='<?php echo route('web::rechnungen::legacy', ['option' => 'rg_aus_beleg']) ?>'>Rechnung aus Beleg</a>
        </div>
    </div>
    <div class='col s12 m4'>
        <h6>Sonstige</h6>
        <div class='col s12 m12 l6'>
            <a href='<?php echo route('web::zeiterfassung::legacy', ['option' => 'stundennachweise']) ?>'>Stundennachweise</a>
        </div>
        <div class='col s12 m12 l6'>
            <a href='<?php echo route('web::rechnungen::legacy', ['option' => 'vg_rechnungen']) ?>'>Verwaltergebühren</a>
        </div>
    </div>
</div>