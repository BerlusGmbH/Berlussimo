<?php

namespace App\Http\Controllers\Legacy;


use App\Http\Requests\Legacy\LagerRequest;

class LagerController extends LegacyController
{
    protected $submenu = 'legacy/options/links/links.lager.php';
    protected $include = 'legacy/options/modules/lager.php';

    public function request(LagerRequest $request)
    {
        return $this->render();
    }
}