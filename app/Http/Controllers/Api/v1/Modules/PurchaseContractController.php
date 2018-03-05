<?php

namespace App\Http\Controllers\Api\v1\Modules;


use App\Http\Controllers\Controller;
use App\Http\Requests\Legacy\EinheitenRequest;
use App\Models\DetailCategory;
use App\Models\DetailSubcategory;
use App\Models\Kaufvertraege;
use Illuminate\Database\Eloquent\Relations\Relation;

class PurchaseContractController extends Controller
{

    public function detailsCategories(EinheitenRequest $request)
    {
        return response()->json(
            DetailCategory::where('DETAIL_KAT_KATEGORIE', array_search(Kaufvertraege::class, Relation::morphMap()))
                ->with('subcategories')->defaultOrder()->get()
        );
    }

    public function detailsSubcategories(EinheitenRequest $request, $category)
    {
        return response()->json(
            DetailSubcategory::whereHas('category', function ($query) use ($category) {
                $query->where(
                    'DETAIL_KAT_KATEGORIE',
                    array_search(Kaufvertraege::class, Relation::morphMap())
                )->where(
                    'DETAIL_KAT_NAME',
                    $category
                );
            })->defaultOrder()->get()
        );
    }
}