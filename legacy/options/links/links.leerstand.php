<div class="row">
    <div class="col s12 m6 l6">
        <h6>Interessenten</h6>
        <div class='col s6 m6 l2'>
            <a href='<?php echo route('web::leerstand::legacy', ['option' => 'form_interessenten']) ?>'>Erfassen</a>
        </div>
        <div class='col s6 m6 l2'>
            <a href='<?php echo route('web::leerstand::legacy', ['option' => 'interessentenliste']) ?>'>Alle</a>
        </div>
        <div class='col s6 m6 l2'>
            <a href='<?php echo route('web::leerstand::legacy', ['option' => 'termine']) ?>'>Termine</a>
        </div>
        <div class='col s6 m6 l6'>
            <a href='<?php echo route('web::leerstand::legacy', ['option' => 'termine', 'vergangen']) ?>'>vergangene
                Termine</a>
        </div>
    </div>
    <div class="col s12 m6 l2">
        <h6>Leerstände</h6>
        <div class='col s6 m4 l4'>
            <a href='<?php echo route('web::leerstand::legacy', ['option' => 'objekt']) ?>'>Alle</a>
        </div>
    </div>
    <div class="col s12 m6 l2">
        <h6>Vermietung</h6>
        <div class='col s6 m4 l4'>
            <a href='<?php echo route('web::leerstand::legacy', ['option' => 'vermietung']) ?>'>Alle</a>
        </div>
        <div class='col s6 m4 l4'>
            <a href='<?php echo route('web::leerstand::legacy', ['option' => 'vermietung_wedding']) ?>'>Favoriten</a>
        </div>
    </div>
    <div class="col s12 m6 l2">
        <h6>Sanierung</h6>
        <div class='col s6 m4 l4'>
            <a href='<?php echo route('web::leerstand::legacy', ['option' => 'sanierung']) ?>'>Alle</a>
        </div>
        <div class='col s6 m4 l4'>
            <a href='<?php echo route('web::leerstand::legacy', ['option' => 'sanierung_wedding']) ?>'>Favoriten</a>
        </div>
    </div>
</div>
