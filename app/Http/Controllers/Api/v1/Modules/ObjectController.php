<?php

namespace App\Http\Controllers\Api\v1\Modules;


use App\Http\Controllers\Controller;
use App\Http\Requests\Legacy\ObjekteRequest;
use App\Models\Objekte;

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
}