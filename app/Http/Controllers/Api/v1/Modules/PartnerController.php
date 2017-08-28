<?php

namespace App\Http\Controllers\Api\v1\Modules;


use App\Http\Controllers\Controller;
use App\Http\Requests\Legacy\PartnerRequest;
use App\Models\Partner;

class PartnerController extends Controller
{

    public function select(PartnerRequest $request, Partner $partner)
    {
        session()->put('partner_id', $partner->PARTNER_ID);
        return ['status' => 'ok'];
    }

    public function unselect(PartnerRequest $request)
    {
        session()->forget('partner_id');
        return ['status' => 'ok'];
    }
}