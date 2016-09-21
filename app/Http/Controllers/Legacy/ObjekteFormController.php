<?php

namespace App\Http\Controllers\Legacy;


use App\Http\Requests\Legacy\ObjekteRequest;

class ObjekteFormController extends LegacyController
{
    protected $submenu = 'legacy/options/links/links.form_objekte.php';
    protected $include = 'legacy/options/formulare/form_objekte.php';

    public function request(ObjekteRequest $request)
    {
        return $this->render();
    }
}