<?php
$monat = sprintf('%02d', date("m"));
$jahr = date("Y");
?>
<div class='row'>
    <div class='col-xs-6 col-md-3'>
        <h3>Mietverträge</h3>
        <a href='/rentalcontracts'>Liste</a>
    </div>
    <div class='col-xs-6 col-md-3'>
        <h3>Ein- und Auszüge</h3>
        <div class="row">
            <div class='col-xs-12 col-sm-6'>
                <a href='<?php echo route('web::mietvertraege::legacy', ['mietvertrag_raus' => 'letzte_auszuege']) ?>'>Letzte
                    Auszüge</a>
            </div>
            <div class='col-xs-12 col-sm-6'>
                <a href='<?php echo route('web::mietvertraege::legacy', ['mietvertrag_raus' => 'letzte_einzuege']) ?>'>Letzte
                    Einzüge</a>
            </div>
            <div class='col-xs-12 col-sm-6'>
                <a href='<?php echo route('web::mietvertraege::legacy', ['mietvertrag_raus' => 'alle_letzten_auszuege', 'monat' => $monat, 'jahr' => $jahr]) ?>'>Alle
                    Auszüge</a>
            </div>
            <div class='col-xs-12 col-sm-6'>
                <a href='<?php echo route('web::mietvertraege::legacy', ['mietvertrag_raus' => 'alle_letzten_einzuege', 'monat' => $monat, 'jahr' => $jahr]) ?>'>Alle
                    Einzüge</a>
            </div>
        </div>
    </div>
    <div class='col-xs-6 col-md-3'>
        <h3>Mahnliste</h3>
        <div class="row">
            <div class='col-xs-12 col-sm-6'>
                <a href='<?php echo route('web::mietvertraege::legacy', ['mietvertrag_raus' => 'mahnliste_alle']) ?>'>Alle</a>
            </div>
            <div class='col-xs-12 col-sm-6'>
                <a href='<?php echo route('web::mietvertraege::legacy', ['mietvertrag_raus' => 'mahnliste']) ?>'>Aktuelle</a>
            </div>
            <div class='col-xs-12 col-sm-6'>
                <a href='<?php echo route('web::mietvertraege::legacy', ['mietvertrag_raus' => 'mahnliste_ausgezogene']) ?>'>Ehemalige</a>
            </div>
        </div>
    </div>
    <div class='col-xs-6 col-md-3'>
        <h3>Sonstige</h3>
        <div class="row">
            <div class='col-xs-12 col-sm-6'>
                <a href='<?php echo route('web::mietvertraege::legacy', ['mietvertrag_raus' => 'guthaben_liste']) ?>'>Guthaben</a>
            </div>
            <div class='col-xs-12 col-sm-6'>
                <a href='<?php echo route('web::mietvertraege::legacy', ['mietvertrag_raus' => 'saldenliste']) ?>'>Saldenlisten</a>
            </div>
            <div class='col-xs-12 col-sm-6'>
                <a href='<?php echo route('web::mietvertraege::legacy', ['mietvertrag_raus' => 'nebenkosten']) ?>'>Nebenkosten</a>
            </div>
            <?php $vorjahr = date("Y") - 1; ?>
            <div class='col-xs-12 col-sm-6'>
                <a href='<?php echo route('web::mietvertraege::legacy', ['mietvertrag_raus' => 'nebenkosten_pdf_zs', 'jahr' => $vorjahr]) ?>'>NK
                    PDF</a>
            </div>
            <div class='col-xs-12 col-sm-6'>
                <a href='<?php echo route('web::mietvertraege::legacy', ['mietvertrag_raus' => 'nebenkosten_pdf_zs', 'jahr' => $vorjahr, 'xls']) ?>'>NK
                    XLS</a>
            </div>
            <?php if (Auth::user()->can(\App\Libraries\Permission::PERMISSION_MODUL_EINHEIT)): ?>
                <div class='col-xs-12 col-sm-6'>
                    <a href='<?php echo route('web::einheiten::legacy', ['einheit_raus' => 'mieterliste_aktuell']) ?>'>Mieterliste</a>
                </div>
            <?php endif ?>
        </div>
    </div>
</div>
