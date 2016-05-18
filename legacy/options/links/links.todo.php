<div class="row">
    <div class="col s4">
        <h6>Aufgaben und Projekte</h6>
        <div class='col s4 m3 l4'>
            <a href='<?php echo route('legacy::todo::index', ['option' => 'neue_auftraege']) ?>'><b>Alle</b></a>
        </div>
        <div class='col s4 m3 l4'>
            <a href='<?php echo route('legacy::todo::index', ['option' => 'offene_auftraege']) ?>'><b>Offene</b></a>
        </div>
        <div class='col s4 m3 l4'>
            <a href='<?php echo route('legacy::todo::index', ['option' => 'erledigte_auftraege']) ?>'><b>Erledigte</b></a>
        </div>
    </div>
    <div class="col s4">
        <h6>Meine Aufgaben und Projekte</h6>
        <div class='col s4 m3 l3'>
            <a href='<?php echo route('legacy::todo::index') ?>'>Alle</a>
        </div>
        <div class='col s4 m3 l3'>
            <a href='<?php echo route('legacy::todo::index', ['option' => 'erledigte_projekte']) ?>'>Erledigte</a>
        </div>
        <div class='col s4 m3 l3'>
            <a href='<?php echo route('legacy::todo::index', ['option' => 'neues_projekt', 'typ' => 'Benutzer']) ?>'>Neu Intern</a>
        </div>
        <div class='col s4 m3 l3'>
            <a href='<?php echo route('legacy::todo::index', ['option' => 'neues_projekt', 'typ' => 'Partner']) ?>'>Neu Extern</a>
        </div>
    </div>
    <div class="col s4">
        <h6>Baustellen</h6>
        <div class='col s4 m3 l4'>
            <a href='<?php echo route('legacy::todo::index', ['option' => 'baustellen_liste']) ?>'>Aktive</a>
        </div>
        <div class='col s4 m3 l4'>
            <a href='<?php echo route('legacy::todo::index', ['option' => 'baustellen_liste_inaktiv']) ?>'>Inaktive</a>
        </div>
        <div class='col s4 m3 l4'>
            <a href='<?php echo route('legacy::todo::index', ['option' => 'form_neue_baustelle']) ?>'>Neu</a>
        </div>
    </div>
</div>
    