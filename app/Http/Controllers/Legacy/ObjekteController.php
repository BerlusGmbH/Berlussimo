<?php

namespace App\Http\Controllers\Legacy;


use App\Http\Requests\Legacy\ObjekteRequest;

class ObjekteController extends LegacyController
{
    protected $submenu = '';
    protected $include = 'legacy/options/modules/objekte.php';


    public function request(ObjekteRequest $request)
    {
        return $this->render();
    }
}
