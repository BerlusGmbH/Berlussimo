<?php

namespace App\Http\Controllers\Api\v1\Modules;


use App\Http\Controllers\Controller;
use App\Http\Requests\Legacy\ToDoRequest;
use App\Models\Auftraege;
use App\Services\ListViewsService;
use Carbon\Carbon;

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
        $assignment->fillable([
            'TEXT', 'BENUTZER_TYP', 'BENUTZER_ID', 'VERFASSER_ID', 'AKUT', 'KOS_TYP', 'KOS_ID', 'ERLEDIGT'
        ])->update($request->only([
            'TEXT', 'BENUTZER_TYP', 'BENUTZER_ID', 'VERFASSER_ID', 'AKUT', 'KOS_TYP', 'KOS_ID', 'ERLEDIGT'
        ]));
        return response()->json($assignment);
    }

    public function store(ToDoRequest $request)
    {
        $params = $request->only(['TEXT', 'BENUTZER_TYP', 'BENUTZER_ID', 'VERFASSER_ID', 'AKUT', 'KOS_TYP', 'KOS_ID', 'ERLEDIGT']);
        $params = array_merge($params, [
            'AKTUELL' => '1',
            'T_ID' => Auftraege::max('T_ID') + 1,
            'ERSTELLT' => Carbon::now(),
            'ANZEIGEN_AB' => Carbon::today()
        ]);
        Auftraege::unguard();
        $object = Auftraege::create($params);
        Auftraege::reguard();
        return response()->json($object);
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
                $query->orderBy('ERSTELLT', 'desc');
            },
            'auftraege.von',
            'auftraege.an',
            'auftraege.kostentraeger'
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