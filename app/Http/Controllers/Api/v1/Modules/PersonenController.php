<?php

namespace App\Http\Controllers\Api\v1\Modules;


use App\Http\Controllers\Controller;
use App\Http\Requests\Modules\Persons\MergeRequest;
use App\Jobs\MergePersons;
use App\Models\Person;
use Auth;

class PersonenController extends Controller
{
    public function merge(MergeRequest $request, Person $left, Person $right)
    {
        $this->dispatch(new MergePersons($request->only(['name', 'first_name', 'birthday', 'sex']), $left, $right, Auth::user()));
        return response()->json(['status' => 'ok']);
    }
}