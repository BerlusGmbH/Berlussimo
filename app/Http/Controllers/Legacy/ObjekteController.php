<?php

namespace App\Http\Controllers\Legacy;


use App\Http\Requests\Legacy\ObjekteRequest;
use App\Models\Objekte;

class ObjekteController extends LegacyController
{
    protected $submenu = 'legacy/options/links/links.form_objekte.php';
    protected $include = 'legacy/options/modules/objekte.php';


    public function request(ObjekteRequest $request)
    {
        return $this->render();
    }

    public function index(ObjekteRequest $request)
    {
        return view('modules.objekte.index');
    }

    public function show(Objekte $objekte, ObjekteRequest $request)
    {
        return view('modules.objekte.show', ['objekt' => $objekte]);
    }
}