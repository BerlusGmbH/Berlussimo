<div class="row">
    <div class="col-xs-4">
        <h6>Baustellen</h6>
        <div class="row">
            <div class='col-xs-12 col-sm-6 col-md-3'>
                <a href='<?php echo route('web::construction::legacy', ['option' => 'baustellen_liste']) ?>'>Aktive</a>
            </div>
            <div class='col-xs-12 col-sm-6 col-md-3'>
                <a href='<?php echo route('web::construction::legacy', ['option' => 'baustellen_liste_inaktiv']) ?>'>Inaktive</a>
            </div>
            <div class='col-xs-12 col-sm-6 col-md-3'>
                <a href='<?php echo route('web::construction::legacy', ['option' => 'form_neue_baustelle']) ?>'>Neu</a>
            </div>
        </div>
    </div>
</div>