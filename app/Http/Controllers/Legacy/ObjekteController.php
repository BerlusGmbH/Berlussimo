<?php

namespace App\Http\Controllers\Legacy;


use App\Http\Requests\Legacy\ObjekteRequest;

class ObjekteController extends LegacyController
{
    protected $submenu = 'legacy/options/links/links.form_objekte.php';
    protected $include = 'legacy/options/modules/objekte.php';

    public function request(ObjekteRequest $request)
    {
        return $this->render();
    }

    public function select($id)
    {
        session()->put('objekt_id', $id);

        return redirect()->intended(route('web::legacy', [], false));
    }
}