<?php

namespace App\Http\Controllers\Legacy;


use App\Http\Requests\Legacy\UebersichtRequest;

class UebersichtController extends LegacyController
{
    protected $submenu = '';
    protected $include = 'legacy/options/modules/uebersicht.php';

    public function request(UebersichtRequest $request)
    {
        return $this->render('yellow-page');
    }
}