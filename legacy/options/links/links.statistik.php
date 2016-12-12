<div class="row">
    <div class="col s6">
        <h6>Bau</h6>
        <div class='col s12 m6 l3'>
            <a href='<?php echo route('web::statistik::legacy', ['option' => 'bau_stat_menu']) ?>'>Einheit</a>
        </div>
        <div class='col s12 m6 l3'>
            <a href='<?php echo route('web::statistik::legacy', ['option' => 'baustelle']) ?>'>Baustellen</a>
        </div>
        <div class='col s12 m6 l3'>
            <a href='<?php echo route('web::zeiterfassung::legacy', ['option' => 'stunden']) ?>'>Stundenübersicht</a>
        </div>
        <div class='col s12 m6 l3'>
            <a href='<?php echo route('web::statistik::legacy', ['option' => 'fenster']) ?>'>Fensterübersicht</a>
        </div>
    </div>
    <div class="col s6">
        <h6>Vermietung</h6>
        <div class='col s12 m6 l3'>
            <a href='<?php echo route('web::statistik::legacy', ['option' => 'leer_vermietet_jahr']) ?>'>Leerstand</a>
        </div>
        <div class='col s12 m6 l3'>
            <a href='<?php echo route('web::statistik::legacy', ['option' => 'stellplaetze']) ?>'>Stellplätze (E)</a>
        </div>
        <div class='col s12 m6 l3'>
            <a href='<?php echo route('web::statistik::legacy', ['option' => 'garage']) ?>'>Garage (GBN)</a>
        </div>
    </div>
    <div class="col s12">
        <h6>Finanzen</h6>
        <div class='col s12 m6 l2'>
            <a href='<?php echo route('web::statistik::legacy', ['option' => 'me_k']) ?>'>E/A Diagramm</a>
        </div>
        <div class='col s12 m6 l3'>
            <a href='<?php echo route('web::statistik::legacy', ['option' => 'sollmieten_aktuell']) ?>'>Sollmieten
                aktuell
                inkl. Leerstand</a>
        </div>
        <div class='col s12 m6 l2'>
            <a href='<?php echo route('web::statistik::legacy', ['option' => 'sollmieten_haeuser', 'pdf']) ?>'>Sollmieten
                Häusergruppen</a>
        </div>
        <div class='col s12 m6 l2'>
            <a href='<?php echo route('web::statistik::legacy', ['option' => 'leer_haus_stat']) ?>'>Statistik im Haus
                5J</a>
        </div>
        <div class='col s12 m6 l2'>
            <a href='<?php echo route('web::leerstand::legacy', ['option' => 'kontrolle_preise']) ?>'>Vermietungspreise</a>
        </div>
    </div>
</div>