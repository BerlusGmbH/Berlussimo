<?php
$monat = sprintf('%02d', date("m"));
$jahr = date("Y");
?>
<div class='row'>
    <div class='col s6 m3'>
        <h6>Mietverträge</h6>
        <div class='col s12 l6'>
            <a href='<?php echo route('legacy::mietvertraege::index', ['mietvertrag_raus' => 'mietvertrag_kurz']) ?>'>Alle</a>
        </div>
        <div class='col s12 l6'>
            <a href='<?php echo route('legacy::mietvertraege::index', ['mietvertrag_raus' => 'mietvertrag_aktuelle']) ?>'>Aktuelle</a>
        </div>
        <div class='col s12 l6'>
            <a href='<?php echo route('legacy::mietvertraege::index', ['mietvertrag_raus' => 'mietvertrag_abgelaufen']) ?>'>Abgelaufene</a>
        </div>
        <div class='col s12 l6'>
            <a href='<?php echo route('legacy::mietvertraege::create') ?>'>Neu</a>
        </div>
    </div>
    <div class='col s6 m3'>
        <h6>Ein- und Auszüge</h6>
        <div class='col s12 l6'>
            <a href='<?php echo route('legacy::mietvertraege::index', ['mietvertrag_raus' => 'letzte_auszuege']) ?>'>Letzte
                Auszüge</a>
        </div>
        <div class='col s12 l6'>
            <a href='<?php echo route('legacy::mietvertraege::index', ['mietvertrag_raus' => 'letzte_einzuege']) ?>'>Letzte
                Einzüge</a>
        </div>
        <div class='col s12 l6'>
            <a href='<?php echo route('legacy::mietvertraege::index', ['mietvertrag_raus' => 'alle_letzten_auszuege', 'monat' => $monat, 'jahr' => $jahr]) ?>'>Alle
                Auszüge</a>
        </div>
        <div class='col s12 l6'>
            <a href='<?php echo route('legacy::mietvertraege::index', ['mietvertrag_raus' => 'alle_letzten_einzuege', 'monat' => $monat, 'jahr' => $jahr]) ?>'>Alle
                Einzüge</a>
        </div>
    </div>
    <div class='col s6 m3'>
        <h6>Mahnliste</h6>
        <div class='col s12 l6'>
            <a href='<?php echo route('legacy::mietvertraege::index', ['mietvertrag_raus' => 'mahnliste_alle']) ?>'>Alle</a>
        </div>
        <div class='col s12 l6'>
            <a href='<?php echo route('legacy::mietvertraege::index', ['mietvertrag_raus' => 'mahnliste']) ?>'>Aktuelle</a>
        </div>
        <div class='col s12 l6'>
            <a href='<?php echo route('legacy::mietvertraege::index', ['mietvertrag_raus' => 'mahnliste_ausgezogene']) ?>'>Ehemalige</a>
        </div>
    </div>
    <div class='col s6 m3'>
        <h6>Sonstige</h6>
        <div class='col s12 l6'>
            <a href='<?php echo route('legacy::mietvertraege::index', ['mietvertrag_raus' => 'guthaben_liste']) ?>'>Guthaben</a>
        </div>
        <div class='col s12 l6'>
            <a href='<?php echo route('legacy::mietvertraege::index', ['mietvertrag_raus' => 'saldenliste']) ?>'>Saldenlisten</a>
        </div>
        <div class='col s12 l6'>
            <a href='<?php echo route('legacy::mietvertraege::index', ['mietvertrag_raus' => 'nebenkosten']) ?>'>Nebenkosten</a>
        </div>
        <?php $vorjahr = date("Y") - 1; ?>
        <div class='col s12 l6'>
            <a href='<?php echo route('legacy::mietvertraege::index', ['mietvertrag_raus' => 'nebenkosten_pdf_zs', 'jahr' => $vorjahr]) ?>'>NK
                    PDF</a>
        </div>
        <div class='col s12 l6'>
            <a href='<?php echo route('legacy::mietvertraege::index', ['mietvertrag_raus' => 'nebenkosten_pdf_zs', 'jahr' => $vorjahr, 'xls']) ?>'>NK
                    XLS</a>
        </div>
        <?php if(check_user_mod(Auth::user()->id, 'einheit_raus')): ?>
        <div class='col s4 m3 l1'>
            <a href='<?php echo route('legacy::einheiten::index', ['einheit_raus' => 'mieterliste_aktuell']) ?>'>Mieterliste</a>
        </div>
        <?php endif ?>
    </div>
</div>