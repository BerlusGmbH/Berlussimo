<?php

namespace App\Http\Controllers\Legacy;


use App\Http\Requests\Legacy\KautionenRequest;

class KautionenController extends LegacyController
{
    protected $submenu = 'legacy/options/links/links.kautionen.php';
    protected $include = 'legacy/options/modules/kautionen.php';

    public function request(KautionenRequest $request)
    {
        return $this->render();
    }
}