<?php

namespace App\Http\Controllers\Legacy;


use App\Http\Requests\Legacy\BkRequest;

class BkController extends LegacyController
{
    protected $submenu = 'legacy/options/links/links.bk.php';
    protected $include = 'legacy/options/modules/bk.php';

    public function request(BkRequest $request)
    {
        return $this->render();
    }
}