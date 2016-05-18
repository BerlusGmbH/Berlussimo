<?php

namespace App\Http\Controllers\Legacy;


use App\Http\Requests\Legacy\WEGRequest;

class WEGController extends LegacyController
{
    protected $submenu = 'legacy/options/links/links.weg.php';
    protected $include = 'legacy/options/modules/weg.php';

    public function request(WEGRequest $request)
    {
        return $this->render();
    }
}