<?php

namespace App\Http\Controllers\Legacy;


class MietkontenblattController extends LegacyController
{
    protected $submenu = 'legacy/options/links/links.mietkonten_blatt.php';
    protected $include = 'legacy/options/modules/mietkonten_blatt.php';

    public function request(MietkontenblattController $request)
    {
        return $this->render();
    }
}