<?php

namespace App\Http\Controllers\Api\v1\Modules;


use App\Http\Controllers\Controller;
use App\Http\Requests\Legacy\ToDoRequest;
use App\Models\Auftraege;
use App\Services\ListViewsService;

class AssignmentController extends Controller
{
    public function index(ToDoRequest $request, ListViewsService $listViewsService)
    {
        $builder = Auftraege::query();

        list($columns, $paginator, $index, $wantedRelations) = $listViewsService->calculateResponseData($request, $builder);

        return response()->json($listViewsService->response($columns, $index, $wantedRelations, $paginator, Auftraege::class));
    }

    public function update(ToDoRequest $request, Auftraege $assignment)
    {
        $assignment->fillable(['OBJEKT_KURZNAME', 'EIGENTUEMER_PARTNER'])->update($request->only(['OBJEKT_KURZNAME', 'EIGENTUEMER_PARTNER']));
        return response()->json($assignment);
    }

    public function show(ToDoRequest $request, Auftraege $assignment)
    {
        $assignment->append('wohnflaeche', 'gewerbeflaeche')->load([
            'hinweise',
            'commonDetails',
            'eigentuemer',
            'haeuser',
            'einheiten',
            'auftraege' => function ($query) {
                $query->with(['von', 'an'])->orderBy('ERSTELLT', 'desc');
            }
        ]);
        $array = $assignment->toArray();
        $array['mieter'] = $assignment->mieter()->get();
        $array['weg_eigentuemer'] = $assignment->WEGEigentuemer()->get();
        return $array;
    }

    public function parameters(ToDoRequest $request, ListViewsService $listViewsService)
    {
        return response()->json($listViewsService->getParameters(AssignmentController::class . '@index'));
    }
}