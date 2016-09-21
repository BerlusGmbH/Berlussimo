<?php

namespace App\Http\Controllers\Legacy;


use App\Http\Requests\Legacy\RechnungenRequest;

class RechnungenController extends LegacyController
{
    protected $submenu = 'legacy/options/links/links.rechnungen.php';
    protected $include = 'legacy/options/modules/rechnungen.php';

    public function request(RechnungenRequest $request)
    {
        return $this->render();
    }
}