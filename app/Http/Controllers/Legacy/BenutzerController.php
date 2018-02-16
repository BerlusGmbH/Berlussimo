<?php

namespace App\Http\Controllers\Legacy;


use App\Http\Requests\Legacy\BenutzerRequest;

class BenutzerController extends LegacyController
{
    protected $submenu = 'legacy/options/links/links.benutzer.php';
    protected $include = 'legacy/options/modules/benutzer.php';

    public function request(BenutzerRequest $request)
    {
        return $this->render();
    }
}