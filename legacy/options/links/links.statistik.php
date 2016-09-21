<div class="row">
    <div class="col s6">
        <h6>Bau</h6>
        <div class='col s12 m6 l3'>
            <a href='<?php echo route('legacy::statistik::index', ['option' => 'bau_stat_menu']) ?>'>Einheit</a>
        </div>
        <div class='col s12 m6 l3'>
            <a href='<?php echo route('legacy::statistik::index', ['option' => 'baustelle']) ?>'>Baustellen</a>
        </div>
        <div class='col s12 m6 l3'>
            <a href='<?php echo route('legacy::zeiterfassung::index', ['option' => 'stunden']) ?>'>Stundenübersicht</a>
        </div>
        <div class='col s12 m6 l3'>
            <a href='<?php echo route('legacy::statistik::index', ['option' => 'fenster']) ?>'>Fensterübersicht</a>
        </div>
    </div>
    <div class="col s6">
        <h6>Vermietung</h6>
        <div class='col s12 m6 l3'>
            <a href='<?php echo route('legacy::statistik::index', ['option' => 'leer_vermietet_jahr']) ?>'>Leerstand</a>
        </div>
        <div class='col s12 m6 l3'>
            <a href='<?php echo route('legacy::statistik::index', ['option' => 'stellplaetze']) ?>'>Stellplätze (E)</a>
        </div>
        <div class='col s12 m6 l3'>
            <a href='<?php echo route('legacy::statistik::index', ['option' => 'garage']) ?>'>Garage (GBN)</a>
        </div>
    </div>
    <div class="col s12">
        <h6>Finanzen</h6>
        <div class='col s12 m6 l2'>
            <a href='<?php echo route('legacy::statistik::index', ['option' => 'me_k']) ?>'>E/A Diagramm</a>
        </div>
        <div class='col s12 m6 l3'>
            <a href='<?php echo route('legacy::statistik::index', ['option' => 'sollmieten_aktuell']) ?>'>Sollmieten
                aktuell
                inkl. Leerstand</a>
        </div>
        <div class='col s12 m6 l2'>
            <a href='<?php echo route('legacy::statistik::index', ['option' => 'sollmieten_haeuser', 'pdf']) ?>'>Sollmieten
                Häusergruppen</a>
        </div>
        <div class='col s12 m6 l2'>
            <a href='<?php echo route('legacy::statistik::index', ['option' => 'leer_haus_stat']) ?>'>Statistik im Haus
                5J</a>
        </div>
        <div class='col s12 m6 l2'>
            <a href='<?php echo route('legacy::leerstand::index', ['option' => 'kontrolle_preise']) ?>'>Vermietungspreise</a>
        </div>
    </div>
</div>