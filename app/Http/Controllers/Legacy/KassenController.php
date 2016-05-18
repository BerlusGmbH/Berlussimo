<?php

namespace App\Http\Controllers\Legacy;


use App\Http\Requests\Legacy\KassenRequest;

class KassenController extends LegacyController
{
    protected $submenu = 'legacy/options/links/links.kasse.php';
    protected $include = 'legacy/options/modules/kasse.php';

    public function request(KassenRequest $request)
    {
        return $this->render();
    }
}