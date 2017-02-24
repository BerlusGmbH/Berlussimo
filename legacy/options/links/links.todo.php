<div class="row">
    <div class="col-xs-4">
        <h6>Aufgaben und Projekte</h6>
        <div class="row">
            <div class='col-xs-12 col-sm-6 col-md-3'>
                <a href='<?php echo route('web::todo::legacy', ['option' => 'neue_auftraege']) ?>'>Alle</a>
            </div>
            <div class='col-xs-12 col-sm-6 col-md-3'>
                <a href='<?php echo route('web::todo::legacy', ['option' => 'offene_auftraege']) ?>'>Offene</a>
            </div>
            <div class='col-xs-12 col-sm-6 col-md-3'>
                <a href='<?php echo route('web::todo::legacy', ['option' => 'erledigte_auftraege']) ?>'>Erledigte</a>
            </div>
        </div>
    </div>
    <div class="col-xs-4">
        <h6>Meine Aufgaben und Projekte</h6>
        <div class="row">
            <div class='col-xs-12 col-sm-6 col-md-3'>
                <a href='<?php echo route('web::todo::legacy') ?>'>Alle</a>
            </div>
            <div class='col-xs-12 col-sm-6 col-md-3'>
                <a href='<?php echo route('web::todo::legacy', ['option' => 'erledigte_projekte']) ?>'>Erledigte</a>
            </div>
            <div class='col-xs-12 col-sm-6 col-md-3'>
                <a href='<?php echo route('web::todo::legacy', ['option' => 'neues_projekt', 'typ' => 'Benutzer']) ?>'>Neu
                    Intern</a>
            </div>
            <div class='col-xs-12 col-sm-6 col-md-3'>
                <a href='<?php echo route('web::todo::legacy', ['option' => 'neues_projekt', 'typ' => 'Partner']) ?>'>Neu
                    Extern</a>
            </div>
        </div>
    </div>
    <div class="col-xs-4">
        <h6>Baustellen</h6>
        <div class="row">
            <div class='col-xs-12 col-sm-6 col-md-3'>
                <a href='<?php echo route('web::todo::legacy', ['option' => 'baustellen_liste']) ?>'>Aktive</a>
            </div>
            <div class='col-xs-12 col-sm-6 col-md-3'>
                <a href='<?php echo route('web::todo::legacy', ['option' => 'baustellen_liste_inaktiv']) ?>'>Inaktive</a>
            </div>
            <div class='col-xs-12 col-sm-6 col-md-3'>
                <a href='<?php echo route('web::todo::legacy', ['option' => 'form_neue_baustelle']) ?>'>Neu</a>
            </div>
        </div>
    </div>
</div>