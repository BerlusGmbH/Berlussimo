<div class="row">
    <div class="col s6">
        <h6>Buchen</h6>
        <div class='col s12 m6 l2'>
            <a href='<?php echo route('legacy::miete_buchen::index') ?>'>Miete</a>
        </div>
        <div class='col s12 m6 l2'>
            <a href='<?php echo route('legacy::buchen::index', ['option' => 'zahlbetrag_buchen']) ?>'>Kosten</a>
        </div>
        <div class='col s12 m6 l4'>
            <a href='<?php echo route('legacy::buchen::index', ['option' => 'ausgangsbuch_kurz']) ?>'>Rechnungsausgang</a>
        </div>
        <div class='col s12 m6 l4'>
            <a href='<?php echo route('legacy::buchen::index', ['option' => 'eingangsbuch_kurz', 'anzeige' => 'empfaenger_eingangs_rnr']) ?>'>Rechnungeseingang</a>
        </div>
    </div>
    <div class="col s6">
        <h6>Buchungsjournal</h6>
        <div class='col s12 m3 l2'>
            <a href='<?php echo route('legacy::buchen::index', ['option' => 'buchungs_journal']) ?>'>Aktuell</a>
        </div>
        <?php $jahr = date("Y"); ?>
        <?php $vorjahr = date("Y") - 1; ?>
        <div class='col s12 m3 l2'>
            <a href='<?php echo route('legacy::buchen::index', ['option' => 'buchungs_journal_jahr_pdf', 'jahr' => $jahr]) ?>'>PDF</a>
        </div>
        <div class='col s12 m6 l3'>
            <a href='<?php echo route('legacy::buchen::index', ['option' => 'buchungs_journal_jahr_pdf', 'jahr' => $vorjahr, 'xls']) ?>'>Vorjahr
                XLS</a>
        </div>
    </div>
    <div class="col m7 l7">
        <h6>Suchen & Filtern</h6>
        <div class='col s6 m6 l3'>
            <a href='<?php echo route('legacy::buchen::index', ['option' => 'buchung_suchen']) ?>'>Buchung suchen</a>
        </div>
        <div class='col s12 m12 l4'>
            <a href='<?php echo route('legacy::buchen::index', ['option' => 'buchungen_zu_kostenkonto']) ?>'>Buchungen
                zu
                Kostenkonto</a>
        </div>
        <div class='col s6 m6 l3'>
            <a href='<?php echo route('legacy::buchen::index', ['option' => 'konten_uebersicht']) ?>'>Kontenübersicht</a>
        </div>
        <div class='col s6 m6 l2'>
            <a href='<?php echo route('legacy::buchen::index', ['option' => 'konto_uebersicht']) ?>'>Kontoübersicht</a>
        </div>
    </div>
    <div class="col m5 l5">
        <h6>Sonstiges</h6>
        <div class='col s12 m12 l4'>
            <a href='<?php echo route('legacy::buchen::index', ['option' => 'kostenkonto_pdf', 'anzeige' => 'empfaenger_eingangs_rnr']) ?>'>Kostenkonto
                PDF</a>
        </div>
        <div class='col s12 m12 l8'>
            <a href='<?php echo route('legacy::buchen::index', ['option' => 'buchungskonto_summiert_xls', 'jahr' => $vorjahr]) ?>'>Buchungskonten
                summiert XLS</a>
        </div>
    </div>
    <div class="col s6">
        <h6>Comerzbank Kontoauszüge</h6>
        <div class='col s12 m4'>
            <a href='<?php echo route('legacy::buchen::index', ['option' => 'excel_buchen', 'upload']) ?>'>Hochladen</a>
        </div>
        <div class='col s12 m4'>
            <a href='<?php echo route('legacy::buchen::index', ['option' => 'excel_buchen_session']) ?>'>Verbuchen</a>
        </div>
        <div class='col s12 m4'>
            <a href='<?php echo route('legacy::buchen::index', ['option' => 'uebersicht_excel_konten']) ?>'>Übersicht</a>
        </div>
    </div>
    <div class="col s6">
        <h6>Berichte</h6>
        <div class='col s12 m12 l4'>
            <a href='<?php echo route('legacy::buchen::index', ['option' => 'monatsbericht_o_a']) ?>'>Monatsbericht o.
                Auszug</a>
        </div>
        <div class='col s12 m12 l4'>
            <a href='<?php echo route('legacy::buchen::index', ['option' => 'monatsbericht_m_a']) ?>'>Monatsbericht m.
                Auszug</a>
        </div>
        <div class='col s12 m12 l4'>
            <a href='<?php echo route('legacy::buchen::index', ['option' => 'kosten_einnahmen']) ?>'>Kosten &
                Einnahmen</a>
        </div>
    </div>
</div>