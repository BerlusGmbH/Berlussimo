<?php

namespace App\Http\Controllers\Legacy;


use App\Http\Requests\Legacy\HaeuserRequest;
use App\Models\Haeuser;
use ListViews;

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
        $builder = Haeuser::query();

        list($columns, $haeuser, $index, $wantedRelations) = ListViews::calculateResponseData($request, $builder);

        return view('modules.haeuser.index', ['columns' => $columns, 'entities' => $haeuser, 'index' => $index, 'wantedRelations' => $wantedRelations]);
    }

    public function show($id, HaeuserRequest $request)
    {
        $haus = Haeuser::find($id);
        return view('modules.haeuser.show', ['haus' => $haus]);
    }
}