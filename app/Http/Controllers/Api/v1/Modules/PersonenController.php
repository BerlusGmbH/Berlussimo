<?php

namespace App\Http\Controllers\Api\v1\Modules;


use App\Http\Controllers\Controller;
use App\Http\Requests\Modules\Persons\MergeRequest;
use App\Jobs\MergePersons;
use App\Models\Person;

class PersonenController extends Controller
{
    public function merge(MergeRequest $request, Person $left, Person $right)
    {
        $this->dispatch(new MergePersons($request->only(['name', 'first_name', 'birthday', 'sex']), $left, $right));
        return response()->json(['status' => 'ok']);
    }
}