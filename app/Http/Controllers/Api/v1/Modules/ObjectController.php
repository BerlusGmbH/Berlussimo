<?php

namespace App\Http\Controllers\Api\v1\Modules;


use App\Http\Controllers\Controller;
use App\Http\Requests\Legacy\ObjekteRequest;
use App\Models\DetailCategory;
use App\Models\DetailSubcategory;
use App\Models\Objekte;
use App\Services\ListViewsService;
use Illuminate\Database\Eloquent\Relations\Relation;

class ObjectController extends Controller
{
    public function select(ObjekteRequest $request, Objekte $object)
    {
        session()->put('objekt_id', $object->OBJEKT_ID);
        return ['status' => 'ok'];
    }

    public function unselect(ObjekteRequest $request)
    {
        session()->forget('objekt_id');
        return ['status' => 'ok'];
    }

    public function index(ObjekteRequest $request, ListViewsService $listViewsService)
    {
        $builder = Objekte::query();

        list($columns, $paginator, $index, $wantedRelations) = $listViewsService->calculateResponseData($request, $builder);

        return response()->json($listViewsService->response($columns, $index, $wantedRelations, $paginator, Objekte::class));
    }

    public function update(ObjekteRequest $request, Objekte $object)
    {
        $object->fillable(['OBJEKT_KURZNAME', 'EIGENTUEMER_PARTNER'])->update($request->only(['OBJEKT_KURZNAME', 'EIGENTUEMER_PARTNER']));
        return response()->json($object);
    }

    public function store(ObjekteRequest $request)
    {
        $params = $request->only(['OBJEKT_KURZNAME', 'EIGENTUEMER_PARTNER']);
        $params = array_merge($params, ['OBJEKT_AKTUELL' => '1', 'OBJEKT_ID' => Objekte::max('OBJEKT_ID') + 1]);
        Objekte::unguard();
        $object = Objekte::create($params);
        Objekte::reguard();
        return response()->json($object);
    }

    public function show(ObjekteRequest $request, Objekte $object)
    {
        $object->append('wohnflaeche', 'gewerbeflaeche')->load([
            'hinweise',
            'commonDetails',
            'eigentuemer',
            'haeuser',
            'einheiten',
            'auftraege' => function ($query) {
                $query->with(['von', 'an'])->orderBy('ERSTELLT', 'desc');
            }
        ]);
        $array = $object->toArray();
        $array['mieter'] = $object->mieter()->get();
        $array['weg_eigentuemer'] = $object->WEGEigentuemer()->get();
        return $array;
    }

    public function detailsCategories(ObjekteRequest $request)
    {
        return response()->json(
            DetailCategory::where('DETAIL_KAT_KATEGORIE', array_search(Objekte::class, Relation::morphMap()))
                ->with('subcategories')->defaultOrder()->get()
        );
    }

    public function detailsSubcategories(ObjekteRequest $request, $category)
    {
        return response()->json(
            DetailSubcategory::whereHas('category', function ($query) use ($category) {
                $query->where(
                    'DETAIL_KAT_KATEGORIE',
                    array_search(Objekte::class, Relation::morphMap())
                )->where(
                    'DETAIL_KAT_NAME',
                    $category
                );
            })->defaultOrder()->get()
        );
    }

    public function parameters(ObjekteRequest $request, ListViewsService $listViewsService)
    {
        return response()->json($listViewsService->getParameters(ObjectController::class . '@index'));
    }

    public function tenantsEMails(ObjekteRequest $request, Objekte $object)
    {
        $emails = collect();
        foreach ($object->mieter()->with('emails')->get() as $mieter) {
            if (!$mieter->emails->isEmpty()) {
                foreach ($mieter->emails as $email) {
                    if ($email->DETAIL_INHALT != '') {
                        $emails->push(trim($email->DETAIL_INHALT));
                    }
                }
            }
        }
        return $emails;
    }

    public function ownersEMails(ObjekteRequest $request, Objekte $object)
    {
        $emails = collect();
        foreach ($object->WEGEigentuemer()->with('emails')->get() as $owner) {
            if (!$owner->emails->isEmpty()) {
                foreach ($owner->emails as $email) {
                    if ($email->DETAIL_INHALT != '') {
                        $emails->push(trim($email->DETAIL_INHALT));
                    }
                }
            }
        }
        return $emails;
    }
}