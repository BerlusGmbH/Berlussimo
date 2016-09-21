<?php

namespace App\Http\Controllers\Legacy;


use App\Http\Requests\Legacy\BuchenRequest;

class BuchenController extends LegacyController
{
    protected $submenu = 'legacy/options/links/links.buchen.php';
    protected $include = 'legacy/options/modules/buchen.php';

    public function request(BuchenRequest $request)
    {
        return $this->render();
    }
}