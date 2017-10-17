<?php

namespace App\Http\Controllers\Api\v1\Modules;


use App\Http\Controllers\Controller;
use App\Http\Requests\Legacy\EinheitenRequest;
use App\Models\DetailCategory;
use App\Models\DetailSubcategory;
use App\Models\Einheiten;
use App\Services\ListViewsService;
use Illuminate\Database\Eloquent\Relations\Relation;

class UnitController extends Controller
{
    public function index(EinheitenRequest $request, ListViewsService $listViewsService)
    {
        $builder = Einheiten::query();

        list($columns, $paginator, $index, $wantedRelations) = $listViewsService->calculateResponseData($request, $builder);

        return response()->json($listViewsService->response($columns, $index, $wantedRelations, $paginator, Einheiten::class));
    }

    public function update(EinheitenRequest $request, Einheiten $unit)
    {
        $unit->update($request->only(['EINHEIT_KURZNAME', 'EINHEIT_QM', 'EINHEIT_LAGE', 'TYP', 'HAUS_ID']));
        return response()->json($unit);
    }

    public function store(EinheitenRequest $request)
    {
        $params = $request->only(['EINHEIT_KURZNAME', 'EINHEIT_QM', 'EINHEIT_LAGE', 'TYP', 'HAUS_ID']);
        $params = array_merge($params, ['EINHEIT_AKTUELL' => '1', 'EINHEIT_ID' => Einheiten::max('EINHEIT_ID') + 1]);
        $unit = Einheiten::create($params);
        return response()->json($unit);
    }

    public function show(EinheitenRequest $request, Einheiten $unit)
    {
        $unit->load([
            'haus.objekt',
            'hinweise',
            'commonDetails',
            'mietvertraege',
            'kaufvertraege',
            'auftraege' => function ($query) {
                $query->with(['von', 'an'])->orderBy('ERSTELLT', 'desc');
            }
        ]);
        $array = $unit->toArray();
        $array['mieter'] = $unit->mieter()->get();
        $array['weg_eigentuemer'] = $unit->WEGEigentuemer()->get();
        return $array;
    }

    public function detailsCategories(EinheitenRequest $request)
    {
        return response()->json(
            DetailCategory::where('DETAIL_KAT_KATEGORIE', array_search(Einheiten::class, Relation::morphMap()))
                ->with('subcategories')->defaultOrder()->get()
        );
    }

    public function detailsSubcategories(EinheitenRequest $request, $category)
    {
        return response()->json(
            DetailSubcategory::whereHas('category', function ($query) use ($category) {
                $query->where(
                    'DETAIL_KAT_KATEGORIE',
                    array_search(Einheiten::class, Relation::morphMap())
                )->where(
                    'DETAIL_KAT_NAME',
                    $category
                );
            })->defaultOrder()->get()
        );
    }

    public function possibleUnitKinds(EinheitenRequest $request)
    {
        return Einheiten::getPossibleEnumValues('TYP');
    }

    public function parameters(EinheitenRequest $request, ListViewsService $listViewsService)
    {
        return response()->json($listViewsService->getParameters(UnitController::class . '@index'));
    }

    public function tenantsEMails(EinheitenRequest $request, Einheiten $unit)
    {
        $emails = collect();
        foreach ($unit->mieter()->with('emails')->get() as $mieter) {
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

    public function ownersEMails(EinheitenRequest $request, Einheiten $unit)
    {
        $emails = collect();
        foreach ($unit->WEGEigentuemer()->with('emails')->get() as $owner) {
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