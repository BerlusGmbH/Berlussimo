<?php

namespace App\Http\Controllers\Legacy;


use App\Http\Requests\Legacy\EinheitenRequest;
use App\Models\Einheiten;

class EinheitenController extends LegacyController
{
    protected $submenu = 'legacy/options/links/links.form_einheit.php';
    protected $include = 'legacy/options/modules/einheit.php';

    public function request(EinheitenRequest $request)
    {
        return $this->render();
    }

    public function index(EinheitenRequest $request)
    {
        return view('modules.einheiten.index');
    }

    public function show(Einheiten $einheiten, EinheitenRequest $request)
    {
        return view('modules.einheiten.show', ['einheit' => $einheiten]);
    }
}