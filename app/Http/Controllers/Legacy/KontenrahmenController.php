<?php

namespace App\Http\Controllers\Legacy;


use App\Http\Requests\Legacy\KontenrahmenRequest;

class KontenrahmenController extends LegacyController
{
    protected $submenu = 'legacy/options/links/links.kontenrahmen.php';
    protected $include = 'legacy/options/modules/kontenrahmen.php';

    public function request(KontenrahmenRequest $request)
    {
        return $this->render();
    }
}