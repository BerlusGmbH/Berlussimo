<?php

namespace App\Http\Controllers\Legacy;


use App\Http\Requests\Legacy\MietvertraegeRequest;

class MietvertraegeController extends LegacyController
{
    protected $submenu = 'legacy/options/links/links.mietvertrag.php';
    protected $include = 'legacy/options/modules/mietvertrag.php';

    public function request(MietvertraegeRequest $request)
    {
        return $this->render();
    }
}
