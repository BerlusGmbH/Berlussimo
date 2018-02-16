<?php

namespace App\Http\Controllers\Legacy;


use App\Http\Requests\Legacy\SepaRequest;

class SepaController extends LegacyController
{
    protected $submenu = 'legacy/options/links/links.sepa.php';
    protected $include = 'legacy/options/modules/sepa.php';

    public function request(SepaRequest $request)
    {
        return $this->render();
    }
}