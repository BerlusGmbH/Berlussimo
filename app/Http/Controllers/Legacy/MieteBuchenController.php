<?php

namespace App\Http\Controllers\Legacy;


use App\Http\Requests\Legacy\MieteBuchenRequest;

class MieteBuchenController extends LegacyController
{
    protected $submenu = 'legacy/options/links/links.mietkonten_blatt.php';
    protected $include = 'legacy/options/modules/buchungsmaske.php';

    public function request(MieteBuchenRequest $request)
    {
        return $this->render();
    }
}