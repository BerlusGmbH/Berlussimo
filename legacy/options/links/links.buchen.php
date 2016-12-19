<div class="row">
    <div class="col-xs-6">
        <h6>Buchen</h6>
        <div class="row">
            <div class='col-xs-12 col-sm-6 col-md-6 col-lg-2'>
                <a href='<?php echo route('web::miete_buchen::legacy') ?>'>Miete</a>
            </div>
            <div class='col-xs-12 col-sm-6 col-md-6 col-lg-2'>
                <a href='<?php echo route('web::buchen::legacy', ['option' => 'zahlbetrag_buchen']) ?>'>Kosten</a>
            </div>
            <div class='col-xs-12 col-sm-6 col-md-6 col-lg-4'>
                <a href='<?php echo route('web::buchen::legacy', ['option' => 'ausgangsbuch_kurz']) ?>'>Rechnungsausgang</a>
            </div>
            <div class='col-xs-12 col-sm-6 col-md-6 col-lg-4'>
                <a href='<?php echo route('web::buchen::legacy', ['option' => 'eingangsbuch_kurz', 'anzeige' => 'empfaenger_eingangs_rnr']) ?>'>Rechnungeseingang</a>
            </div>
        </div>
    </div>
    <div class="col-xs-6">
        <h6>Buchungsjournal</h6>
        <div class="row">
            <div class='col-xs-12 col-sm-6 col-md-3 col-lg-2'>
                <a href='<?php echo route('web::buchen::legacy', ['option' => 'buchungs_journal']) ?>'>Aktuell</a>
            </div>
            <?php $jahr = date("Y"); ?>
            <?php $vorjahr = date("Y") - 1; ?>
            <div class='col-xs-12 col-sm-6 col-md-3 col-lg-2'>
                <a href='<?php echo route('web::buchen::legacy', ['option' => 'buchungs_journal_jahr_pdf', 'jahr' => $jahr]) ?>'>PDF</a>
            </div>
            <div class='col-xs-12 col-sm-6 col-md-6 col-lg-3'>
                <a href='<?php echo route('web::buchen::legacy', ['option' => 'buchungs_journal_jahr_pdf', 'jahr' => $vorjahr, 'xls']) ?>'>Vorjahr
                    XLS</a>
            </div>
        </div>
    </div>
    <div class="col-xs-7">
        <h6>Suchen & Filtern</h6>
        <div class="row">
            <div class='col-xs-6 col-lg-3'>
                <a href='<?php echo route('web::buchen::legacy', ['option' => 'buchung_suchen']) ?>'>Buchung suchen</a>
            </div>
            <div class='col-xs-6 col-lg-4'>
                <a href='<?php echo route('web::buchen::legacy', ['option' => 'buchungen_zu_kostenkonto']) ?>'>Buchungen
                    zu
                    Kostenkonto</a>
            </div>
            <div class='col-xs-6 col-lg-3'>
                <a href='<?php echo route('web::buchen::legacy', ['option' => 'konten_uebersicht']) ?>'>Kontenübersicht</a>
            </div>
            <div class='col-xs-6 col-lg-2'>
                <a href='<?php echo route('web::buchen::legacy', ['option' => 'konto_uebersicht']) ?>'>Kontoübersicht</a>
            </div>
        </div>
    </div>
    <div class="col-xs-5">
        <h6>Sonstiges</h6>
        <div class="row">
            <div class='col-xs-12 col-lg-4'>
                <a href='<?php echo route('web::buchen::legacy', ['option' => 'kostenkonto_pdf', 'anzeige' => 'empfaenger_eingangs_rnr']) ?>'>Kostenkonto
                    PDF</a>
            </div>
            <div class='col-xs-12 col-lg-8'>
                <a href='<?php echo route('web::buchen::legacy', ['option' => 'buchungskonto_summiert_xls', 'jahr' => $vorjahr]) ?>'>Buchungskonten
                    summiert XLS</a>
            </div>
        </div>
    </div>
    <div class="col-xs-6">
        <h6>Commerzbank Kontoauszüge</h6>
        <div class="row">
            <div class='col-xs-12 col-md-4'>
                <a href='<?php echo route('web::buchen::legacy', ['option' => 'excel_buchen', 'upload']) ?>'>Hochladen</a>
            </div>
            <div class='col-xs-12 col-md-4'>
                <a href='<?php echo route('web::buchen::legacy', ['option' => 'excel_buchen_session']) ?>'>Verbuchen</a>
            </div>
            <div class='col-xs-12 col-md-4'>
                <a href='<?php echo route('web::buchen::legacy', ['option' => 'uebersicht_excel_konten']) ?>'>Übersicht</a>
            </div>
        </div>
    </div>
    <div class="col-xs-6">
        <h6>Berichte</h6>
        <div class="row">
            <div class='col-xs-12 col-md-6 col-lg-4'>
                <a href='<?php echo route('web::buchen::legacy', ['option' => 'monatsbericht_o_a']) ?>'>Monatsbericht o.
                    Auszug</a>
            </div>
            <div class='col-xs-12 col-md-6 col-lg-4'>
                <a href='<?php echo route('web::buchen::legacy', ['option' => 'monatsbericht_m_a']) ?>'>Monatsbericht m.
                    Auszug</a>
            </div>
            <div class='col-xs-12 col-md-6 col-lg-4'>
                <a href='<?php echo route('web::buchen::legacy', ['option' => 'kosten_einnahmen']) ?>'>Kosten &
                    Einnahmen</a>
            </div>
        </div>
    </div>
</div>