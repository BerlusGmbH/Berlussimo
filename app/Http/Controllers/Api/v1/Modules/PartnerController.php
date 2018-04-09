<?php

namespace App\Http\Controllers\Api\v1\Modules;


use App\Http\Controllers\Controller;
use App\Http\Requests\AuthenticatedRequest;
use App\Http\Requests\Legacy\PartnerRequest;
use App\Models\Partner;

class PartnerController extends Controller
{

    public function select(AuthenticatedRequest $request, Partner $partner)
    {
        session()->put('partner_id', $partner->PARTNER_ID);
        return ['status' => 'ok'];
    }

    public function unselect(AuthenticatedRequest $request)
    {
        session()->forget('partner_id');
        return ['status' => 'ok'];
    }

    public function availableTitles(PartnerRequest $request, Partner $partner)
    {
        return response()->json($partner->availableJobTitles()->defaultOrder()->get());
    }
}