<?php

namespace App\Http\Controllers\Legacy;


use App\Http\Requests\Legacy\ZeiterfassungRequest;

class ZeiterfassungController extends LegacyController
{
    protected $submenu = 'legacy/options/links/links.zeiterfassung.php';
    protected $include = 'legacy/options/modules/zeiterfassung.php';

    public function request(ZeiterfassungRequest $request)
    {
        return $this->render();
    }
}