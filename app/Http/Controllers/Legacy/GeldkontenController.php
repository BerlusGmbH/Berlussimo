<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Requests\Legacy\GeldkontenRequest;
use gk;

class GeldkontenController extends LegacyController
{
    protected $submenu = 'legacy/options/links/links.geldkonten.php';
    protected $include = 'legacy/options/modules/geldkonten.php';

    public function request(GeldkontenRequest $request)
    {
        return $this->render();
    }

    public function select($id) {

        session()->put('geldkonto_id', $id);
        $gk = new gk();
        session()->put('objekt_id', $gk->get_objekt_id($id));

        return redirect()->intended(route('web::legacy', [], false));
    }
}