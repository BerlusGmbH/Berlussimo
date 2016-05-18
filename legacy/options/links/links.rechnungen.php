<div class="row">
    <div class='col s6'>
        <h6>Rechnungen</h6>
        <div class='col s12 m4 l2'>
            <a href='<?php echo route('legacy::rechnungen::index', ['option' => 'erfasste_rechnungen']) ?>'>Alle</a>
        </div>
        <div class='col s12 m4 l2'>
            <a href='<?php echo route('legacy::rechnungen::index', ['option' => 'rechnung_erfassen']) ?>'>Erfassen</a>
        </div>
        <div class='col s12 m4 l2'>
            <a href='<?php echo route('legacy::rechnungen::index', ['option' => 'gutschrift_erfassen']) ?>'>Gutschrift</a>
        </div>
        <div class='col s12 m6 l2'>
            <a href='<?php echo route('legacy::rechnungen::index', ['option' => 'eingangsbuch']) ?>'>Eingangsbuch</a>
        </div>
        <div class='col s12 m6 l2'>
            <a href='<?php echo route('legacy::rechnungen::index', ['option' => 'ausgangsbuch']) ?>'>Ausgangsbuch</a>
        </div>
        <div class='col s12 m8 l4'>
            <a href='<?php echo route('legacy::rechnungen::index', ['option' => 'rechnungsbuch_suche']) ?>'>Rechnungsbücher
                PDF</a>
        </div>
        <div class='col s12 m8 l4'>
            <a href='<?php echo route('legacy::rechnungen::index', ['option' => 'sepa_druckpool']) ?>'>SEPA aus Rechnung</a>
        </div>
    </div>
    <div class='col s6'>
        <h6>Suchen & Filtern</h6>
        <div class='col s12 m12 l4'>
            <a href='<?php echo route('legacy::rechnungen::index', ['option' => 'rechnung_suchen']) ?>'><b>Rechnung
                    suchen</b></a>
        </div>
        <div class='col s12 m12 l4'>
            <a href='<?php echo route('legacy::rechnungen::index', ['option' => 'kosten_einkauf']) ?>'>Kosten
                Einkauf</a>
        </div>
        <div class='col s12 m12 l4'>
            <a href='<?php echo route('legacy::rechnungen::index', ['option' => 'vollstaendige_rechnungen']) ?>'>Vollständige
                Rechnungen</a>
        </div>
        <div class='col s12 m12 l4'>
            <a href='<?php echo route('legacy::rechnungen::index', ['option' => 'unvollstaendige_rechnungen']) ?>'>Unvollständige
                Rechnungen</a>
        </div>
        <div class='col s12 m12 l4'>
            <a href='<?php echo route('legacy::rechnungen::index', ['option' => 'kontierte_rechnungen']) ?>'>Kontierte
                Rechnungen</a>
        </div>
        <div class='col s12 m12 l4'>
            <a href='<?php echo route('legacy::rechnungen::index', ['option' => 'nicht_kontierte_rechnungen']) ?>'>Nicht
                kontierte Rechnungen</a>
        </div>
        <div class='col s12 m12 l4'>
            <a href='<?php echo route('legacy::rechnungen::index', ['option' => 'verbindlichkeiten']) ?>'>Verbindlichkeiten</a>
        </div>
        <div class='col s12 m12 l4'>
            <a href='<?php echo route('legacy::rechnungen::index', ['option' => 'forderungen']) ?>'>Forderungen</a>
        </div>
        <div class='col s12 m12 l4'>
            <a href='<?php echo route('legacy::rechnungen::index', ['option' => 'seb']) ?>'>SEB</a>
        </div>
    </div>
    <div class='col s2'>
        <h6>Import</h6>
        <div class='col s12 m6 l6'>
            <a href='<?php echo route('legacy::rechnungen::index', ['option' => 'form_ugl']) ?>'>UGL</a>
        </div>
        <div class='col s12 m6 l6'>
            <a href='<?php echo route('legacy::rechnungen::index', ['option' => 'import_csv']) ?>'>CSV</a>
        </div>
    </div>
    <div class='col s9'>
        <h6>Pool</h6>
        <div class='col s12 m6 l3'>
            <a href='<?php echo route('legacy::rechnungen::index', ['option' => 'pool_rechnungen']) ?>'>Rechnung aus
                Pool
                erstellen</a>
        </div>
        <div class='col s12 m6 l3'>
            <a href='<?php echo route('legacy::rechnungen::index', ['option' => 'u_pool_liste']) ?>'>Rechnungen im
                Unterpool</a>
        </div>
        <div class='col s12 m6 l2'>
            <a href='<?php echo route('legacy::rechnungen::index', ['option' => 'u_pool_erstellen']) ?>'>Unterpool
                erstellen</a>
        </div>
        <div class='col s12 m6 l2'>
            <a href='<?php echo route('legacy::rechnungen::index', ['option' => 'pdf_druckpool', 'no_logo']) ?>'>PDF-Druckpool</a>
        </div>
    </div>
    <div class='col s6 m4'>
        <h6>Angebote</h6>
        <div class='col s12 m4 l6'>
            <a href='<?php echo route('legacy::rechnungen::index', ['option' => 'meine_angebote']) ?>'>Alle</a>
        </div>
        <div class='col s12 m6 l6'>
            <a href='<?php echo route('legacy::rechnungen::index', ['option' => 'angebot_erfassen']) ?>'>Erfassen</a>
        </div>
    </div>
    <div class='col s6 m4'>
        <h6>Buchungsbelege</h6>
        <div class='col s12 m12 l6'>
            <a href='<?php echo route('legacy::rechnungen::index', ['option' => 'buchungsbelege']) ?>'>Buchungsbelege</a>
        </div>
        <div class='col s12 m12 l6'>
            <a href='<?php echo route('legacy::rechnungen::index', ['option' => 'rg_aus_beleg']) ?>'>Rechnung aus Beleg</a>
        </div>
    </div>
    <div class='col s12 m4'>
        <h6>Sonstige</h6>
        <div class='col s12 m12 l6'>
            <a href='<?php echo route('legacy::zeiterfassung::index', ['option' => 'stundennachweise']) ?>'><b>Stundennachweise</b></a>
        </div>
        <div class='col s12 m12 l6'>
            <a href='<?php echo route('legacy::rechnungen::index', ['option' => 'vg_rechnungen']) ?>'>Verwaltergebühren</a>
        </div>
    </div>
</div>