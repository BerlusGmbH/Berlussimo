<?php

namespace App\Http\Controllers\Legacy;


use App\Http\Requests\Legacy\MieteDefinierenRequest;

class MieteDefinierenController extends LegacyController
{
    protected $submenu = 'legacy/options/links/links.mietkonten_blatt.php';
    protected $include = 'legacy/options/modules/buchungsmaske.php';

    public function request(MieteDefinierenRequest $request)
    {
        return $this->render();
    }
}