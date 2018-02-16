<?php

namespace App\Http\Controllers\Legacy;


use App\Http\Requests\Legacy\KatalogRequest;

class KatalogController extends LegacyController
{
    protected $submenu = 'legacy/options/links/links.katalog.php';
    protected $include = 'legacy/options/modules/katalog.php';

    public function request(KatalogRequest $request)
    {
        return $this->render();
    }
}