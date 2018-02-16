<?php

namespace App\Http\Controllers\Legacy;


use App\Http\Requests\Legacy\ToDoRequest;
use App\Models\Auftraege;
use ListViews;

class ToDoController extends LegacyController
{
    protected $submenu = 'legacy/options/links/links.todo.php';
    protected $include = 'legacy/options/modules/todo.php';


    public function index(ToDoRequest $request)
    {
        $builder = Auftraege::query();

        list($columns, $auftraege, $index, $wantedRelations) = ListViews::calculateResponseData($request, $builder);

        return view('modules.auftraege.index', ['columns' => $columns, 'entities' => $auftraege, 'index' => $index, 'wantedRelations' => $wantedRelations]);
    }

    public function request(ToDoRequest $request)
    {
        return $this->render();
    }
}