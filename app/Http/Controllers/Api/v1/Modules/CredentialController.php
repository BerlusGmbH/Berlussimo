<?php

namespace App\Http\Controllers\Api\v1\Modules;

use App\Http\Controllers\Controller;
use App\Http\Requests\Modules\Persons\Credentials\UpdateRequest;
use App\Models\Credential;
use App\Models\Person;
use Hash;
use Illuminate\Http\Request;

class CredentialController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request, Person $person)
    {
        return response()->json($person->credential !== null);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdateRequest $request
     * @param  \App\Models\Person $person
     * @return \Illuminate\Http\Response
     */
    public function store(UpdateRequest $request, Person $person)
    {
        if ($request->has('password')) {
            if ($person->credential === null) {
                $c = new Credential();
            } else {
                $c = $person->credential;
            }
            $c->unguard();
            $c->fill(['password' => Hash::make($request->input('password'))]);
            $person->credential()->save($c);
        }
        if ($request->has('active')) {
            if ($request->input('active')) {
                $person->credential()->restore();
            } else {
                $person->credential()->delete();
            }
        }
        if ($request->has('roles')) {
            $person->syncRoles($request->input('roles', []));
        }
        return response()->json(['status' => 'ok']);
    }
}
