<?php

namespace App\Http\Controllers\Legacy;


use App\Http\Requests\Legacy\ObjekteRequest;
use App\Models\Objekte;
use ListViews;

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
        $builder = Objekte::query();

        list($columns, $objekte, $index, $wantedRelations) = ListViews::calculateResponseData($request, $builder);

        return view('modules.objekte.index', ['columns' => $columns, 'entities' => $objekte, 'index' => $index, 'wantedRelations' => $wantedRelations]);
    }

    public function select($id)
    {
        session()->put('objekt_id', $id);

        return redirect()->intended(route('web::legacy', [], false));
    }

    public function show($id, ObjekteRequest $request)
    {
        $objekt = Objekte::with(['hinweise', 'commonDetails' => function ($query) {
            $query->defaultOrder();
        }, 'haeuser' => function ($query) {
            $query->defaultOrder();
        }, 'haeuser.hinweise', 'einheiten' => function ($query) {
            $query->defaultOrder();
        }, 'einheiten.hinweise', 'einheiten.mietvertraege'])->find($id);
        return view('modules.objekte.show', ['objekt' => $objekt]);
    }
}