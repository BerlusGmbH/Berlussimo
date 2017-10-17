<?php

namespace App\Http\Controllers\Legacy;


use App\Http\Requests\Legacy\HaeuserRequest;
use App\Models\Haeuser;

class HaeuserController extends LegacyController
{
    protected $submenu = 'legacy/options/links/links.form_haus.php';
    protected $include = 'legacy/options/modules/haus.php';

    public function request(HaeuserRequest $request)
    {
        return $this->render();
    }

    public function index(HaeuserRequest $request)
    {
        return view('modules.haeuser.index');
    }

    public function show(Haeuser $haeuser, HaeuserRequest $request)
    {
        return view('modules.haeuser.show', ['haus' => $haeuser]);
    }
}