<div class="row">
    <div class='col-xs-6'>
        <h3>Rechnungen</h3>
        <div class="row">
            <div class='col-xs-12 col-md-4 col-lg-2'>
                <a href='{{route('web::rechnungen::legacy', ['option' => 'erfasste_rechnungen'])}}'>Alle</a>
            </div>
            <div class='col-xs-12 col-md-4 col-lg-2'>
                <a href='{{route('web::rechnungen::legacy', ['option' => 'rechnung_erfassen'])}}'>Erfassen</a>
            </div>
            <div class='col-xs-12 col-md-4 col-lg-2'>
                <a href='{{route('web::rechnungen::legacy', ['option' => 'gutschrift_erfassen'])}}'>Gutschrift</a>
            </div>
            <div class='col-xs-12 col-md-6 col-lg-2'>
                <a href='{{route('web::rechnungen::legacy', ['option' => 'eingangsbuch'])}}'>Eingangsbuch</a>
            </div>
            <div class='col-xs-12 col-md-6 col-lg-2'>
                <a href='{{route('web::rechnungen::legacy', ['option' => 'ausgangsbuch'])}}'>Ausgangsbuch</a>
            </div>
            <div class='col-xs-12 col-md-6 col-lg-4'>
                <a href='{{route('web::rechnungen::legacy', ['option' => 'rechnungsbuch_suche'])}}>'>Rechnungsbücher
                    PDF</a>
            </div>
            <div class='col-xs-12 col-md-6 col-lg-4'>
                <a href='{{route('web::rechnungen::legacy', ['option' => 'sepa_druckpool'])}}>'>SEPA aus
                    Rechnung</a>
            </div>
        </div>
    </div>
    <div class='col-xs-6'>
        <h3>Suchen & Filtern</h3>
        <div class="row">
            <div class='col-xs-12 col-md-6 col-lg-4'>
                <a href='{{route('web::rechnungen::legacy', ['option' => 'rechnung_suchen'])}}'>Rechnung
                    suchen</a>
            </div>
            <div class='col-xs-12 col-md-6 col-lg-4'>
                <a href='{{route('web::rechnungen::legacy', ['option' => 'kosten_einkauf'])}}'>Kosten
                    Einkauf</a>
            </div>
            <div class='col-xs-12 col-md-6 col-lg-4'>
                <a href='{{route('web::rechnungen::legacy', ['option' => 'vollstaendige_rechnungen'])}}'>Vollständige
                    Rechnungen</a>
            </div>
            <div class='col-xs-12 col-md-6 col-lg-4'>
                <a href='{{route('web::rechnungen::legacy', ['option' => 'unvollstaendige_rechnungen'])}}>'>Unvollständige
                    Rechnungen</a>
            </div>
            <div class='col-xs-12 col-md-6 col-lg-4'>
                <a href='{{route('web::rechnungen::legacy', ['option' => 'kontierte_rechnungen'])}}'>Kontierte
                    Rechnungen</a>
            </div>
            <div class='col-xs-12 col-md-6 col-lg-4'>
                <a href='{{route('web::rechnungen::legacy', ['option' => 'nicht_kontierte_rechnungen'])}}'>Nicht
                    kontierte Rechnungen</a>
            </div>
            <div class='col-xs-12 col-md-6 col-lg-4'>
                <a href='{{route('web::rechnungen::legacy', ['option' => 'verbindlichkeiten'])}}>'>Verbindlichkeiten</a>
            </div>
            <div class='col-xs-12 col-md-6 col-lg-4'>
                <a href='{{route('web::rechnungen::legacy', ['option' => 'forderungen'])}}'>Forderungen</a>
            </div>
            <div class='col-xs-12 col-md-6 col-lg-4'>
                <a href='{{route('web::rechnungen::legacy', ['option' => 'seb'])}}>'>SEB</a>
            </div>
        </div>
    </div>
    <div class='col-xs-2'>
        <h3>Import</h3>
        <div class="row">
            <div class='col-xs-12 col-md-6'>
                <a href='{{route('web::rechnungen::legacy', ['option' => 'form_ugl'])}}'>UGL</a>
            </div>
            <div class='col-xs-12 col-md-6'>
                <a href='{{route('web::rechnungen::legacy', ['option' => 'import_csv'])}}'>CSV</a>
            </div>
        </div>
    </div>
    <div class='col-xs-9'>
        <h3>Pool</h3>
        <div class="row">
            <div class='col-xs-12 col-md-6 col-lg-3'>
                <a href='{{route('web::rechnungen::legacy', ['option' => 'pool_rechnungen'])}}'>Rechnung aus
                    Pool
                    erstellen</a>
            </div>
            <div class='col-xs-12 col-md-6 col-lg-3'>
                <a href='{{route('web::rechnungen::legacy', ['option' => 'u_pool_liste'])}}'>Rechnungen im
                    Unterpool</a>
            </div>
            <div class='col-xs-12 col-md-6 col-lg-2'>
                <a href='{{route('web::rechnungen::legacy', ['option' => 'u_pool_erstellen'])}}'>Unterpool
                    erstellen</a>
            </div>
            <div class='col-xs-12 col-md-6 col-lg-2'>
                <a href='{{route('web::rechnungen::legacy', ['option' => 'pdf_druckpool', 'no_logo'])}}'>PDF-Druckpool</a>
            </div>
            <div class='col-xs-12 col-md-6 col-lg-2'>
                <a href='{{route('web::rechnungen::legacy', ['option' => 'sepa_druckpool'])}}'>SEPA-Druckpool</a>
            </div>
        </div>
    </div>
    <div class='col-xs-6 col-md-4'>
        <h3>Angebote</h3>
        <div class="row">
            <div class='col-xs-12 col-md-4'>
                <a href='{{route('web::rechnungen::legacy', ['option' => 'meine_angebote'])}}'>Alle</a>
            </div>
            <div class='col-xs-12 col-md-6'>
                <a href='{{route('web::rechnungen::legacy', ['option' => 'angebot_erfassen'])}}'>Erfassen</a>
            </div>
        </div>
    </div>
    <div class='col-xs-6 col-md-4'>
        <h3>Buchungsbelege</h3>
        <div class="row">
            <div class='col-xs-12 col-lg-6'>
                <a href='{{route('web::rechnungen::legacy', ['option' => 'buchungsbelege'])}}'>Buchungsbelege</a>
            </div>
            <div class='col-xs-12 col-lg-6'>
                <a href='{{route('web::rechnungen::legacy', ['option' => 'rg_aus_beleg'])}}'>Rechnung aus
                    Beleg</a>
            </div>
        </div>
    </div>
    <div class='col-xs-12 col-md-4'>
        <h3>Sonstige</h3>
        <div class="row">
            <div class='col-xs-12 col-lg-6'>
                <a href='{{route('web::zeiterfassung::legacy', ['option' => 'stundennachweise'])}}'>Stundennachweise</a>
            </div>
            <div class='col-xs-12 col-lg-6'>
                <a href='{{route('web::rechnungen::legacy', ['option' => 'vg_rechnungen'])}}'>Verwaltergebühren</a>
            </div>
        </div>
    </div>
</div>
