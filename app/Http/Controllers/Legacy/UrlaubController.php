<?php

namespace App\Http\Controllers\Legacy;


use App\Http\Requests\Legacy\UrlaubRequest;

class UrlaubController extends LegacyController
{
    protected $submenu = 'legacy/options/links/links.urlaub.php';
    protected $include = 'legacy/options/modules/urlaub.php';

    public function request(UrlaubRequest $request)
    {
        return $this->render();
    }
}