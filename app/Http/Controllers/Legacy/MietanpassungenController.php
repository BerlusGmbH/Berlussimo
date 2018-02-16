<?php

namespace App\Http\Controllers\Legacy;


use App\Http\Requests\Legacy\MietanpassungenRequest;

class MietanpassungenController extends LegacyController
{
    protected $submenu = 'legacy/options/links/links.mietkonten_blatt.php';
    protected $include = 'legacy/options/modules/mietanpassung.php';

    public function request(MietanpassungenRequest $request)
    {
        return $this->render();
    }
}