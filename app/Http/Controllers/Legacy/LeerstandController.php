<?php

namespace App\Http\Controllers\Legacy;


use App\Http\Requests\Legacy\LeerstandRequest;

class LeerstandController extends LegacyController
{
    protected $submenu = 'legacy/options/links/links.leerstand.php';
    protected $include = 'legacy/options/modules/leerstand.php';

    public function request(LeerstandRequest $request)
    {
        return $this->render();
    }
}