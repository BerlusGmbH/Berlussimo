<?php

namespace App\Http\Controllers\Api\v1\Modules;


use App\Http\Controllers\Controller;
use App\Http\Requests\Legacy\HaeuserRequest;
use App\Models\DetailCategory;
use App\Models\DetailSubcategory;
use App\Models\Haeuser;
use App\Services\ListViewsService;
use Illuminate\Database\Eloquent\Relations\Relation;

class HouseController extends Controller
{
    public function index(HaeuserRequest $request, ListViewsService $listViewsService)
    {
        $builder = Haeuser::query();

        list($columns, $paginator, $index, $wantedRelations) = $listViewsService->calculateResponseData($request, $builder);

        return response()->json($listViewsService->response($columns, $index, $wantedRelations, $paginator, Haeuser::class));
    }

    public function update(HaeuserRequest $request, Haeuser $house)
    {
        $house->update($request->only(['HAUS_STRASSE', 'HAUS_NUMMER', 'HAUS_STADT', 'HAUS_PLZ', 'OBJEKT_ID']));
        return response()->json($house);
    }

    public function store(HaeuserRequest $request)
    {
        $params = $request->only(['HAUS_STRASSE', 'HAUS_NUMMER', 'HAUS_STADT', 'HAUS_PLZ', 'OBJEKT_ID']);
        $params = array_merge($params, ['HAUS_AKTUELL' => '1', 'HAUS_ID' => Haeuser::max('HAUS_ID') + 1]);
        $house = Haeuser::create($params);
        return response()->json($house);
    }

    public function show(HaeuserRequest $request, Haeuser $house)
    {
        $house->append('wohnflaeche', 'gewerbeflaeche')->load([
            'objekt',
            'hinweise',
            'commonDetails',
            'einheiten',
            'auftraege' => function ($query) {
                $query->with(['von', 'an'])->orderBy('ERSTELLT', 'desc');
            }
        ]);
        $array = $house->toArray();
        $array['mieter'] = $house->mieter()->get();
        $array['weg_eigentuemer'] = $house->WEGEigentuemer()->get();
        return $array;
    }

    public function detailsCategories(HaeuserRequest $request)
    {
        return response()->json(
            DetailCategory::where('DETAIL_KAT_KATEGORIE', array_search(Haeuser::class, Relation::morphMap()))
                ->with('subcategories')->defaultOrder()->get()
        );
    }

    public function detailsSubcategories(HaeuserRequest $request, $category)
    {
        return response()->json(
            DetailSubcategory::whereHas('category', function ($query) use ($category) {
                $query->where(
                    'DETAIL_KAT_KATEGORIE',
                    array_search(Hauser::class, Relation::morphMap())
                )->where(
                    'DETAIL_KAT_NAME',
                    $category
                );
            })->defaultOrder()->get()
        );
    }

    public function parameters(HaeuserRequest $request, ListViewsService $listViewsService)
    {
        return response()->json($listViewsService->getParameters(HouseController::class . '@index'));
    }

    public function tenantsEMails(HaeuserRequest $request, Haeuser $house)
    {
        $emails = collect();
        foreach ($house->mieter()->with('emails')->get() as $mieter) {
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

    public function ownersEMails(HaeuserRequest $request, Haeuser $house)
    {
        $emails = collect();
        foreach ($house->WEGEigentuemer()->with('emails')->get() as $owner) {
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