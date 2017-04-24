<?php

namespace App\Http\Controllers\Modules\Persons;

use App\Http\Controllers\Controller;
use App\Http\Requests\Modules\Persons\Credentials\StoreRequest;
use App\Http\Requests\Modules\Persons\Credentials\UpdateRequest;
use App\Models\Person;
use Hash;

class CredentialController extends Controller
{

    /**
     * Store a newly created resource in storage.
     *
     * @param  StoreRequest $request
     * @param  Person $person
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRequest $request, Person $person)
    {
        $person->credential()->create(['password' => Hash::make(request()->input('password'))]);
        return redirect()->back();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdateRequest $request
     * @param  \App\Models\Person $person
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRequest $request, Person $person)
    {
        if (request()->has('password')) {
            $person->credential()->update(['password' => Hash::make(request()->input('password'))]);
        }
        if (request()->exists('inactive')) {
            $person->credential()->delete();
        } else {
            $person->credential()->restore();
        }
        $person->syncRoles(request()->input('roles', []));
        return redirect()->back();
    }
}
