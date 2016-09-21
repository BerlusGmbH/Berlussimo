<?php

namespace App\Http\Controllers\Legacy;


use App\Http\Requests\Legacy\MietspiegelRequest;

class MietspiegelController extends LegacyController
{
    protected $submenu = 'legacy/options/links/links.mietspiegel.php';
    protected $include = 'legacy/options/modules/mietspiegel.php';

    public function request(MietspiegelRequest $request)
    {
        return $this->render();
    }
}