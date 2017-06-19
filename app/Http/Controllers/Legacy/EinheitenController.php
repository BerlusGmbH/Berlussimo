<?php

namespace App\Http\Controllers\Legacy;


use App\Http\Requests\Legacy\EinheitenRequest;
use App\Models\Einheiten;
use ListViews;

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
        $builder = Einheiten::query();

        list($columns, $einheiten, $index, $wantedRelations) = ListViews::calculateResponseData($request, $builder);

        return view('modules.einheiten.index', ['columns' => $columns, 'entities' => $einheiten, 'index' => $index, 'wantedRelations' => $wantedRelations]);
    }

    public function show($id, EinheitenRequest $request)
    {
        $einheit = Einheiten::find($id);
        return view('modules.einheiten.show', ['einheit' => $einheit]);
    }
}