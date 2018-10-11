<div class="row">
    <div class="col-xs-12 col-md-6 col-lg-2">
        <h3>Leerstände</h3>
        <div class="row">
            <div class='col-xs-6 col-md-4'>
                <a href='<?php echo route('web::leerstand::legacy', ['option' => 'objekt']) ?>'>Alle</a>
            </div>
        </div>
    </div>
    <div class="col-xs-12 col-md-6 col-lg-2">
        <h3>Vermietung</h3>
        <div class="row">
            <div class='col-xs-6 col-md-4'>
                <a href='<?php echo route('web::leerstand::legacy', ['option' => 'vermietung']) ?>'>Alle</a>
            </div>
            <div class='col-xs-6 col-md-4'>
                <a href='<?php echo route('web::leerstand::legacy', ['option' => 'vermietung_wedding']) ?>'>Favoriten</a>
            </div>
        </div>
    </div>
    <div class="col-xs-12 col-md-6 col-lg-2">
        <h3>Sanierung</h3>
        <div class="row">
            <div class='col-xs-6 col-md-4'>
                <a href='<?php echo route('web::leerstand::legacy', ['option' => 'sanierung']) ?>'>Alle</a>
            </div>
            <div class='col-xs-6 col-md-4'>
                <a href='<?php echo route('web::leerstand::legacy', ['option' => 'sanierung_wedding']) ?>'>Favoriten</a>
            </div>
        </div>
    </div>
</div>
