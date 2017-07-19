<?php

namespace App\Http\Controllers\Api\v1\Modules;


use App\Http\Controllers\Controller;
use App\Http\Requests\Legacy\PersonenRequest;
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

    public function show(PersonenRequest $request, Person $person)
    {
        $person->load([
            'phones',
            'emails',
            'faxs',
            'adressen',
            'hinweise',
            'mietvertraege.einheit.haus.objekt',
            'kaufvertraege.einheit.haus.objekt',
            'commonDetails',
            'jobsAsEmployee' => function ($query) {
                $query->with(['title', 'employer']);
            },
            'roles',
            'audits' => function ($query) {
                $query->with('user');
            },
            'roles',
            'credential' => function ($query) {
                $query->withTrashed();
            }
        ]);
        return $person;
    }
}